<?php

namespace App\Livewire\Admin\Order;

use Livewire\Component;
use LivewireUI\Modal\ModalComponent;
use App\Models\ProductDetail;
use App\Models\OrderDetail;
use App\Models\ProductSize;
use App\Models\Warehouse;
use App\Models\Product;
use App\Models\ImportProductDetail;
use App\Models\TransferWarehouseDetail;
use App\Models\ImportProduct;
use App\Models\TransferWarehouse;

class AddProductModal extends ModalComponent
{
    public $products;
    public $product_details;
    public $product_sizes;
    public $warehouses;
    public $product_id;
    public $product_detail_id;
    public $size_items = [];
    public $note = '';
    public $warehouse_id = 1;
    public $classRef;
    public $base_price = 0;

    public function mount($mode)
    {
        if($mode == 'New'){
            $this->classRef = AddOrder::class;
        }else{
            $this->classRef = EditOrder::class;
        }
        $this->order_product = new OrderDetail();
        $this->products = Product::all();
        $this->product_details = collect(new ProductDetail);
        $this->product_sizes = collect(new ProductSize);
        $this->warehouses = Warehouse::all();
    }

    public function loadProductAttributes()
    {
        if (!$this->product_id) {
            $this->product_details = collect();
            $this->base_price = 0;
            $this->product_detail_id = '';
            $this->size_items = [];
            return;
        }
        
        $this->product_details = ProductDetail::where('product_id', $this->product_id)->get();
        $product = Product::where('id', $this->product_id)->first();
        $this->base_price = $product ? $product->retail_price : 0;
        $this->product_detail_id = '';
        $this->size_items = [];
    }
    
    public function updatedProductDetailId()
    {
        $this->loadSizeInventory();
    }
    
    public function loadSizeInventory()
    {
        $this->size_items = [];
        $this->addError('size_items', '');
        
        if (!$this->product_id || !$this->product_detail_id) {
            return;
        }
        
        $sizes = ProductSize::where('product_id', $this->product_id)->orderBy('id')->get();
        $warehouse = Warehouse::find($this->warehouse_id);
        
        foreach ($sizes as $size) {
            $stock = $warehouse->totalProductAvailable($this->product_id, $this->product_detail_id, $size->id);
            
            // Calculate existing total for this product variant including current order
            $existingTotal = 0;
            foreach ($this->size_items as $existing) {
                if (is_string($existing)) {
                    $existing = json_decode($existing, true);
                }
                
                $existingProductId = is_object($existing) ? ($existing->product_id ?? 0) : ($existing['product_id'] ?? 0);
                $existingDetailId = is_object($existing) ? ($existing->product_detail_id ?? 0) : ($existing['product_detail_id'] ?? 0);
                $existingSizeId = is_object($existing) ? ($existing->size_id ?? 0) : ($existing['size_id'] ?? 0);
                $existingQuantity = is_object($existing) ? ($existing->quantity ?? 0) : ($existing['quantity'] ?? 0);
                
                if ($existingProductId == $this->product_id 
                    && $existingDetailId == $this->product_detail_id 
                    && $existingSizeId == $size->id) {
                    $existingTotal += $existingQuantity;
                }
            }
            
            $availableStock = $stock - $existingTotal;
            
            if ($availableStock > 0) {
                $this->size_items[] = [
                    'size_id' => $size->id,
                    'size_name' => $size->size,
                    'stock' => $availableStock,
                    'quantity' => 0,
                    'price' => $this->base_price,
                ];
            }
        }
    }
    
    public function incrementQuantity($index)
    {
        if ($this->size_items[$index]['quantity'] < $this->size_items[$index]['stock'] || $this->size_items[$index]['stock'] == 0) {
            $this->size_items[$index]['quantity']++;
        }
    }
    
    public function decrementQuantity($index)
    {
        if ($this->size_items[$index]['quantity'] > 0) {
            $this->size_items[$index]['quantity']--;
        }
    }
    
    public function copyPriceToAll($index)
    {
        $price = $this->size_items[$index]['price'];
        foreach ($this->size_items as $key => $item) {
            $this->size_items[$key]['price'] = $price;
        }
    }
    
    public function getTotalQuantityProperty()
    {
        return collect($this->size_items)->sum(function($item) {
            return (int) $item['quantity'];
        });
    }
    
    public function getTotalAmountProperty()
    {
        return collect($this->size_items)->sum(function($item) {
            return (int) $item['quantity'] * (float) $item['price'];
        });
    }

    public function storeOrderProduct(){
        $this->validate([
            'product_id' => 'required',
            'product_detail_id' => 'required',
        ], [
            'product_id.required' => 'Vui lòng chọn sản phẩm.',
            'product_detail_id.required' => 'Vui lòng chọn màu/mẫu sản phẩm.',
        ]);
        
        if (empty($this->size_items)) {
            $this->addError('size_items', 'Không có size nào còn hàng cho sản phẩm này.');
            return; 
        }
        
        if ($this->total_quantity <= 0) {
            $this->addError('size_items', 'Vui lòng nhập số lượng cho ít nhất 1 size.');
            return;
        }
        
        // Get current order items from parent component using Livewire events
        // Since getParent() is not available in LivewireUI ModalComponent we skip this validation for now
        $existingItems = [];
        
        foreach ($this->size_items as $index => $item) {
            if ($item['quantity'] > 0 && $item['price'] < 0) {
                $this->addError("size_items.$index.price", "Giá size {$item['size_name']} không hợp lệ.");
                return;
            }
            
            // Calculate total existing quantity for this exact product variant
            $existingTotal = 0;
            foreach ($existingItems as $existing) {
                if (is_string($existing)) {
                    $existing = json_decode($existing, true);
                }
                
                $existingProductId = is_object($existing) ? ($existing->product_id ?? 0) : ($existing['product_id'] ?? 0);
                $existingDetailId = is_object($existing) ? ($existing->product_detail_id ?? 0) : ($existing['product_detail_id'] ?? 0);
                $existingSizeId = is_object($existing) ? ($existing->size_id ?? 0) : ($existing['size_id'] ?? 0);
                $existingQuantity = is_object($existing) ? ($existing->quantity ?? 0) : ($existing['quantity'] ?? 0);
                
                if ($existingProductId == $this->product_id 
                    && $existingDetailId == $this->product_detail_id 
                    && $existingSizeId == $item['size_id']) {
                    $existingTotal += $existingQuantity;
                }
            }
            
            $totalRequested = $existingTotal + $item['quantity'];
            
            if ($totalRequested > $item['stock']) {
                $this->addError("size_items.$index.quantity", "Số lượng size {$item['size_name']} vượt tồn kho. Đã có {$existingTotal} trong đơn, còn {$item['stock']} trong kho.");
                return;
            }
        }
        
        $product = Product::find($this->product_id);
        $productDetail = ProductDetail::find($this->product_detail_id);
        
        $orderItems = [];
        
        foreach ($this->size_items as $item) {
            if ($item['quantity'] > 0) {
                $orderItems[] = [
                    'product_id' => $this->product_id,
                    'product_detail_id' => $this->product_detail_id,
                    'product_name' => $product->name,
                    'product_detail_name' => $productDetail->title,
                    'size_id' => $item['size_id'],
                    'size_name' => $item['size_name'],
                    'warehouse_id' => $this->warehouse_id,
                    'warehouse_name' => "TK",
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['price'],
                    'total_amount' => (int) $item['quantity'] * (float) $item['price'],
                    'note' => $this->note,
                ];
            }
        }
        
        $this->closeModalWithEvents([
            $this->classRef => ['updateOrderProduct', [$orderItems, true]],
        ]);
    }

    public static function modalMaxWidth(): string
    {
        return '2xl';
    }
    public function render()
    {
        return view('livewire.admin.order.add-product-modal');
    }
}
