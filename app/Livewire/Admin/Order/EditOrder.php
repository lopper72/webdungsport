<?php

namespace App\Livewire\Admin\Order;

use Livewire\Component;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\Warehouse;

class EditOrder extends Component
{
    public $customers;
    public $payment_methods;
    public $payment_method_id = '';
    public $payment_status = '';
    public $customer_id = '';
    public $order_date = '';
    public $order_status = '';
    public $order_note = '';
    public $order_code = '';
    public $order_phone = '';
    public $order_email = '';
    public $order_address = '';
    public $order_state = '';
    public $order_city = '';
    public $order_details = [];
    public $subtotal_amount = 0;
    public $discount_amount = 0;
    public $discount_percentage = 0;
    public $grandtotal_amount = 0;
    public $shipping_amount = 0;
    public $total_amount = 0;
    public $grandtotal_notpay = 0;
    public $grandtotal_all = 0;
    public $order;
    public $order_id;
    public $order_product_delete = [];
    public $action = '';

protected $listeners = ['updateOrderProduct', 'updateOrderProductEdit', 'updateCustomerId'];

    public function updateOrderProduct($order_product, $isMultiple = false)
    {
        if ($isMultiple) {
            foreach ($order_product as $item) {
                // Validate stock before adding
                $warehouse = Warehouse::find($this->warehouse_id ?? 1);
                $stock = $warehouse->totalProductAvailable(
                    $item['product_id'], 
                    $item['product_detail_id'], 
                    $item['size_id']
                );
                
                // Calculate existing total for this product variant including current order
                $existingTotal = 0;
                foreach ($this->order_details as $existing) {
                    if (is_string($existing)) {
                        $existing = json_decode($existing, true);
                    }
                    
                    $existingProductId = is_object($existing) ? ($existing->product_id ?? 0) : ($existing['product_id'] ?? 0);
                    $existingDetailId = is_object($existing) ? ($existing->product_detail_id ?? 0) : ($existing['product_detail_id'] ?? 0);
                    $existingSizeId = is_object($existing) ? ($existing->size_id ?? 0) : ($existing['size_id'] ?? 0);
                    $existingQuantity = is_object($existing) ? ($existing->quantity ?? 0) : ($existing['quantity'] ?? 0);
                    
                    if ($existingProductId == $item['product_id'] 
                        && $existingDetailId == $item['product_detail_id'] 
                        && $existingSizeId == $item['size_id']) {
                        $existingTotal += $existingQuantity;
                    }
                }
                
                // Calculate total requested including current item being added
                $totalRequested = $existingTotal + $item['quantity'];
                
                if ($totalRequested > $stock) {
                    $this->dispatch('successOrder', [
                        'title' => 'Thất bại',
                        'message' => "Số lượng size {$item['size_name']} vượt tồn kho. Đã có {$existingTotal} trong đơn, còn {$stock} trong kho.",
                        'type' => 'error',
                        'timeout' => 3000
                    ]);
                    return;
                }
                
                $this->order_details[] = $item;
            }
        } else {
            $this->order_details[] = $order_product;
        }
        
        $this->updateAmount();
        $this->calTotalAmount();
    }
    
public function updateOrderProductEdit($order_product, $index)
{
    // Always convert to array for consistent format
    if (is_object($order_product) && method_exists($order_product, 'toArray')) {
        $order_product = $order_product->toArray();
    }

    $this->order_details[$index] = $order_product;

    $this->updateAmount();
    $this->calTotalAmount();
}

public function updateCustomerId($customer_id)
{
    $this->customer_id = $customer_id;
    $this->recalculateGrandtotalNotpay();
}

    public function removeProduct($index)
    {
        // Only add to delete list if item has database id
        if (isset($this->order_details[$index]["id"]) && $this->order_details[$index]["id"]) {
            $this->order_product_delete[] = $this->order_details[$index];
        }
        
        unset($this->order_details[$index]);
        $this->order_details = array_values($this->order_details);
        $this->updateAmount();
        $this->calTotalAmount();
    }

    public function createOrder(){
        $this->order_status = "pending";
        $this->storeOrder('create');
    }

    public function updateOrder(){
        $this->order_status = Order::find($this->order_id)->status;
        $this->storeOrder('update');
    }

    public function draftOrder(){
        $this->order_status = "draft";
        $this->storeOrder('draft');
    }

    public function storeOrder($action)
    {
        $this->validateOrder();

        if(empty($this->order_details)){
            $this->dispatch('successOrder', [
                'title' => 'Thất bại',
                'message' => 'Vui lòng chọn sản phẩm cho đơn hàng',
                'type' => 'error',
                'timeout' => 3000,
                'action' => $action
            ]);
            return;
        }

        $this->order->update([
            'code' => $this->order_code,
            'user_id' => $this->customer_id,
            'payment_method_id' => $this->payment_method_id,
            'payment_status' => $this->payment_status,
            'order_date' => $this->order_date,
            'status' => $this->order_status,
            'note' => $this->order_note,
            'shipping_phone' => $this->order_phone,
            'shipping_email' => $this->order_email,
            'shipping_address' => $this->order_address,
            'shipping_state' => $this->order_state,
            'shipping_city' => $this->order_city,
            'subtotal_amount' => $this->subtotal_amount,
            'discount_amount' => $this->discount_amount,
            'discount_percent' => $this->discount_percentage,
            'grandtotal_amount' => $this->grandtotal_amount,
            'shipping_amount' => $this->shipping_amount,
            'total_amount' => $this->total_amount,
        ]);

        foreach ($this->order_product_delete as $order_product) {
            if (isset($order_product["id"]) && $order_product["id"]) {
                OrderDetail::find($order_product["id"])->delete();
            }
        }
        foreach ($this->order_details as $order_product) {
            $attributes = [
                'order_id' => $this->order->id,
                'product_id' => $order_product["product_id"],
                'product_detail_id' => $order_product["product_detail_id"],
                'size_id' => $order_product["size_id"],
                'warehouse_id' => $order_product["warehouse_id"],
            ];
            
            $values = [
                'quantity' => $order_product["quantity"],
                'unit_price' => $order_product["unit_price"],
                'total_amount' => $order_product["total_amount"],
                'note' => $order_product["note"],
            ];
            
            // Always use updateOrCreate - this is 100% safe, no duplicate items
            OrderDetail::updateOrCreate($attributes, $values);
        }
        $this->dispatch('successOrder', [
            'title' => 'Thành công',
            'message' => '',
            'type' => 'success',
            'timeout' => 3000,
            'action' => $action
        ]);
    }

    protected function validateOrder()
    {
        $this->validate([
            'customer_id' => 'required',
            'payment_method_id' => 'required',
            'payment_status' => 'required',
            'order_date' => 'required',
            'order_status' => 'required',
            'order_code' => 'required',
            // 'order_phone' => 'required',
            // 'order_address' => 'required',
            // 'order_state' => 'required',
            // 'order_city' => 'required'
        ], [
            'customer_id.required' => 'Trường khách hàng là bắt buộc.',
            'payment_method_id.required' => 'Trường phương thức thanh toán là bắt buộc.',
            'payment_status.required' => 'Trường trạng thái thanh toán là bắt buộc.',
            'order_date.required' => 'Trường ngày đặt hàng là bắt buộc.',
            'order_status.required' => 'Trường trạng thái đơn hàng là bắt buộc.',
            'order_code.required' => 'Trường mã đơn hàng là bắt buộc.',
            // 'order_phone.required' => 'Trường số điện thoại đơn hàng là bắt buộc.',
            // 'order_address.required' => 'Trường địa chỉ đơn hàng là bắt buộc.',
            // 'order_state.required' => 'Trường tỉnh/thành phố đơn hàng là bắt buộc.',
            // 'order_city.required' => 'Trường quận/huyện đơn hàng là bắt buộc.'
        ]);
    }

    public function updateAmount()
    {
        $this->subtotal_amount = 0;
        foreach ($this->order_details as $key => $order_product) {
            if (is_string($order_product)) {
                $order_product = json_decode($order_product, true);
            }
            
            if (is_object($order_product)) {
                $this->subtotal_amount += $order_product->total_amount;
            } elseif (is_array($order_product) && isset($order_product["total_amount"])) {
                $this->subtotal_amount += $order_product["total_amount"];
            }
        }
    }

    public function calTotalAmountDiscount()
    {
        if ($this->discount_percentage < 1 || $this->discount_percentage > 100) {
            $this->discount_percentage = 0;
            $this->dispatch('successOrder', [
                'title' => 'Thất bại',
                'message' => 'Giảm giá % phải nằm trong khoảng từ 1 đến 100.',
                'type' => 'error',
                'timeout' => 3000
            ]);
            
            return;
        }
        $this->discount_amount = round($this->subtotal_amount * $this->discount_percentage / 100, 3);
        $this->grandtotal_amount = $this->subtotal_amount - $this->discount_amount;
        $this->total_amount = $this->grandtotal_amount + $this->shipping_amount;
        $this->grandtotal_all = $this->total_amount + $this->grandtotal_notpay;
    }
public function calTotalAmount()
{
    $this->discount_amount = round($this->subtotal_amount * $this->discount_percentage / 100, 3);
    $this->grandtotal_amount = $this->subtotal_amount - $this->discount_amount;
    $this->total_amount = $this->grandtotal_amount + $this->shipping_amount;
    $this->grandtotal_all = $this->total_amount + $this->grandtotal_notpay;
}

protected function recalculateGrandtotalNotpay()
{
    $grandtotal_notpay = Order::where('user_id', '=', $this->customer_id)
        ->where('id', '<>', $this->order_id)
        ->where('created_at', '<', $this->order->created_at)
        ->where('payment_status', '=', 'pending')
        ->whereDoesntHave('orderStatus', function($query) {
            $query->where('status', '=', 'rejected');
        })->get();
    $this->grandtotal_notpay = $grandtotal_notpay->sum('total_amount');
    $this->grandtotal_all = $this->total_amount + $this->grandtotal_notpay;
}

public function mount($id, $customers, $payment_methods)
{
    $this->order = Order::findOrFail($id);
    $this->order_id = $id;
    $this->payment_method_id = $this->order->payment_method_id;
    $this->payment_status = $this->order->payment_status;
    $this->customer_id = $this->order->user_id;
    $this->order_date = date('Y-m-d', strtotime($this->order->order_date));
    $this->order_status = $this->order->status;
    $this->order_note = $this->order->note;
    $this->order_code = $this->order->code;
    $this->order_phone = $this->order->shipping_phone;
    $this->order_email = $this->order->shipping_email;
    $this->order_address = $this->order->shipping_address;
    $this->order_state = $this->order->shipping_state;
    $this->order_city = $this->order->shipping_city;
    $this->subtotal_amount = $this->order->subtotal_amount;
    $this->discount_amount = $this->order->discount_amount;
    $this->discount_percentage = $this->order->discount_percent;
    $this->grandtotal_amount = $this->order->grandtotal_amount;
    $this->shipping_amount = $this->order->shipping_amount;
    $this->total_amount = $this->order->total_amount;
    $this->customers = $customers;
    $this->payment_methods = $payment_methods;
    $this->order_details = $this->order->order_detail()->with('product', 'product_size', 'warehouse', 'product_detail')->get()->toArray();

    $this->recalculateGrandtotalNotpay();
}

    public function render()
    {
        return view('livewire.admin.order.edit-order');
    }
}