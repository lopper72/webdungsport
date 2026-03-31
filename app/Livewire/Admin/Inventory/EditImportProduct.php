<?php

namespace App\Livewire\Admin\Inventory;

use Livewire\Component;
use App\Models\ImportProduct;
use App\Models\ImportProductDetail;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\ProductDetail;
use App\Models\ProductSize;

class EditImportProduct extends Component
{
    public $id;
    public $import_product_code = "";
    public $import_product_name = "";
    public $import_product_detail_qnty = [];
    public $import_product_detail_ids = [];
    public $product_id = [];
    public $product_detail_id = [];
    public $size_id = [];
    public $import_product_detail_count = 0;
    public $product_detail_list;
    public $product_size_list;
    public $disabled_select_yn = [];
    public $existing_detail_count = 0;
    public $deleted_detail_ids = [];
    public $isProcessing = false;

    public function mount($id)
    {
        $this->id = $id;
        $import_product = ImportProduct::find($id);
        $import_product_details = ImportProductDetail::where('import_product_id', $id)->get();
        
        $this->import_product_code = $import_product->code;
        $this->import_product_name = $import_product->name;
        $this->existing_detail_count = count($import_product_details);
        
        foreach ($import_product_details as $index => $detail) {
            $this->import_product_detail_ids[$index] = $detail->id;
            $this->import_product_detail_qnty[$index] = $detail->quantity;
            $this->product_id[$index] = $detail->product_id;
            $this->product_detail_id[$index] = $detail->product_detail_id;
            $this->size_id[$index] = $detail->size_id;
            $this->disabled_select_yn[$index] = "n";
            
            $this->product_detail_list[$index] = ProductDetail::where('product_id', $detail->product_id)->get();
            $this->product_size_list[$index] = ProductSize::where('product_id', $detail->product_id)->get();
        }
        
        $this->import_product_detail_count = count($import_product_details);
    }

    public function addImportProductDetail(){
        $new_index = $this->import_product_detail_count;
        $this->import_product_detail_count++;
        
        // Khởi tạo tất cả các trường là rỗng khi thêm mới
        $this->product_id[$new_index] = "";
        $this->product_detail_id[$new_index] = "";
        $this->size_id[$new_index] = "";
        $this->import_product_detail_qnty[$new_index] = "";
        $this->disabled_select_yn[$new_index] = "n";
        $this->import_product_detail_ids[$new_index] = null;
        $this->product_detail_list[$new_index] = [];
        $this->product_size_list[$new_index] = [];
    }

    public function removeImportProductDetail($index){
        // Chỉ cho phép xóa item mới (không phải item cũ)
        if($index >= $this->existing_detail_count && $this->import_product_detail_count > 0){
            // Remove item from arrays
            array_splice($this->product_id, $index, 1);
            array_splice($this->product_detail_id, $index, 1);
            array_splice($this->size_id, $index, 1);
            array_splice($this->import_product_detail_qnty, $index, 1);
            array_splice($this->disabled_select_yn, $index, 1);
            array_splice($this->import_product_detail_ids, $index, 1);
            
            // Remove from detail lists
            if(isset($this->product_detail_list) && is_array($this->product_detail_list)){
                array_splice($this->product_detail_list, $index, 1);
            }
            if(isset($this->product_size_list) && is_array($this->product_size_list)){
                array_splice($this->product_size_list, $index, 1);
            }
            
            $this->import_product_detail_count--;
        }
    }

    public function copyImportProductDetail($index){
        $this->import_product_detail_count++;
        if(isset($this->product_id[$index])){
            $firstProductId = array_slice($this->product_id, 0, $index+1);
            $secondProductId = array_slice($this->product_id, $index+1);
            $this->product_id = array_merge($firstProductId, [$this->product_id[$index]], $secondProductId);

            $firstProductDetailList = array_slice($this->product_detail_list, 0, $index+1);
            $secondProductDetailList = array_slice($this->product_detail_list, $index+1);
            $this->product_detail_list = array_merge($firstProductDetailList, [$this->product_detail_list[$index]], $secondProductDetailList);
            
            $firstProductDetailId = array_slice($this->product_detail_id, 0, $index+1);
            $secondProductDetailId = array_slice($this->product_detail_id, $index+1);
            $this->product_detail_id = array_merge($firstProductDetailId, [$this->product_detail_id[$index]], $secondProductDetailId);

            $firstSizeList = array_slice($this->product_size_list, 0, $index+1);
            $secondSizeList = array_slice($this->product_size_list, $index+1);
            $this->product_size_list = array_merge($firstSizeList, [$this->product_size_list[$index]], $secondSizeList);

            // Set size to empty when copying
            $firstSizeId = array_slice($this->size_id, 0, $index+1);
            $secondSizeId = array_slice($this->size_id, $index+1);
            $this->size_id = array_merge($firstSizeId, [""], $secondSizeId);

            // Set quantity to empty when copying
            $firstQnty = array_slice($this->import_product_detail_qnty, 0, $index+1);
            $secondQnty = array_slice($this->import_product_detail_qnty, $index+1);
            $this->import_product_detail_qnty = array_merge($firstQnty, [""], $secondQnty);

            $firstDisabled = array_slice($this->disabled_select_yn, 0, $index+1);
            $secondDisabled = array_slice($this->disabled_select_yn, $index+1);
            $this->disabled_select_yn = array_merge($firstDisabled, ["n"], $secondDisabled);

            $firstIds = array_slice($this->import_product_detail_ids, 0, $index+1);
            $secondIds = array_slice($this->import_product_detail_ids, $index+1);
            $this->import_product_detail_ids = array_merge($firstIds, [null], $secondIds);
        }
    }

    public function pullDropdown($index){
        $product_id = $this->product_id[$index];
        $this->product_detail_id[$index] = "";
        $this->size_id[$index] = "";
        $product_detail = ProductDetail::where('product_id',$product_id)->get();
        $product_size = ProductSize::where('product_id',$product_id)->get();
        $this->product_detail_list[$index] = $product_detail;
        $this->product_size_list[$index] = $product_size;
    }

    public function update2ImportProduct()
    {
        // DEBUG: Log để kiểm tra method被 gọi多少次
        \Log::info('update2ImportProduct called', [
            'isProcessing' => $this->isProcessing,
            'import_product_detail_count' => $this->import_product_detail_count,
            'timestamp' => now()->toDateTimeString()
        ]);
        
        // Ngăn chặn việc xử lý多次 nếu đang trong quá trình xử lý
        if ($this->isProcessing) {
            \Log::info('update2ImportProduct blocked - already processing');
            return;
        }
        $this->isProcessing = true;
        \Log::info('update2ImportProduct - set isProcessing to true');

        $this->validate([
            'import_product_name' => 'required',
        ], [
            'import_product_name.required' => 'Vui lòng nhập tiêu đề.',
        ]);

        if ($this->import_product_detail_count == 0) {
            $this->dispatch('successImportProduct', [
                'title' => 'Thất bại',
                'message' => 'Bạn chưa chọn sản phẩm, vui lòng nhập lại.',
                'type' => 'error',
                'timeout' => 3000
            ]);
            return;
        }

        // Validate tất cả các item (cả cũ và mới)
        $validation_errors = [];
        
        // Bước 1: Validate số lượng và sản phẩm cho từng dòng
        for ($i = 0; $i < $this->import_product_detail_count; $i++) {
            
            // Validate số lượng thủ công - cho phép số lượng bằng 0
            if (!isset($this->import_product_detail_qnty[$i]) || $this->import_product_detail_qnty[$i] === '' || $this->import_product_detail_qnty[$i] === null) {
                $validation_errors[] = 'Dòng '.($i+1).': Vui lòng nhập số lượng.';
            } elseif (!is_numeric($this->import_product_detail_qnty[$i])) {
                $validation_errors[] = 'Dòng '.($i+1).': Số lượng phải là số.';
            } elseif ($this->import_product_detail_qnty[$i] < 0) {
                $validation_errors[] = 'Dòng '.($i+1).': Số lượng không được nhỏ hơn 0.';
            }
            

            // Validate size cho tất cả các item
            if (!isset($this->size_id[$i]) || empty($this->size_id[$i])) {
                $validation_errors[] = 'Dòng '.($i+1).': Vui lòng chọn size.';
            }

            // Validate sản phẩm cho item mới
            if ($i >= $this->existing_detail_count) {
                if (!isset($this->product_id[$i]) || empty($this->product_id[$i])) {
                    $validation_errors[] = 'Dòng '.($i+1).': Vui lòng chọn sản phẩm.';
                }
                if (!isset($this->product_detail_id[$i]) || empty($this->product_detail_id[$i])) {
                    $validation_errors[] = 'Dòng '.($i+1).': Vui lòng chọn mẫu sản phẩm.';
                }
            }
        }

        // Bước 2: Tổng hợp số lượng nhập theo product/size TỪ TẤT CẢ IMPORT PRODUCTS
        $import_quantities_by_product = [];
        
        // Lấy tất cả import product details từ database (trừ các item đã bị xóa và trừ transaction hiện tại)
        $all_import_details = ImportProductDetail::whereNotIn('id', $this->deleted_detail_ids)
            ->where('import_product_id', '!=', $this->id)
            ->get();
        
        foreach ($all_import_details as $detail) {
            $key = $detail->product_id.'_'.$detail->product_detail_id.'_'.($detail->size_id ?? 'null');
            if (!isset($import_quantities_by_product[$key])) {
                $import_quantities_by_product[$key] = [
                    'product_id' => $detail->product_id,
                    'product_detail_id' => $detail->product_detail_id,
                    'size_id' => $detail->size_id,
                    'total_import_quantity' => 0,
                    'rows' => []
                ];
            }
            $import_quantities_by_product[$key]['total_import_quantity'] += $detail->quantity;
        }
        
        // Cộng thêm số lượng từ transaction hiện tại (từ form)
        for ($i = 0; $i < $this->import_product_detail_count; $i++) {
            $product_id = isset($this->product_id[$i]) ? $this->product_id[$i] : null;
            $product_detail_id = isset($this->product_detail_id[$i]) ? $this->product_detail_id[$i] : null;
            $size_id = isset($this->size_id[$i]) ? $this->size_id[$i] : null;
            $quantity = isset($this->import_product_detail_qnty[$i]) ? (int)$this->import_product_detail_qnty[$i] : 0;

            if ($product_id && $product_detail_id) {
                $key = $product_id.'_'.$product_detail_id.'_'.($size_id ?? 'null');
                if (!isset($import_quantities_by_product[$key])) {
                    $import_quantities_by_product[$key] = [
                        'product_id' => $product_id,
                        'product_detail_id' => $product_detail_id,
                        'size_id' => $size_id,
                        'total_import_quantity' => 0,
                        'rows' => []
                    ];
                }
                $import_quantities_by_product[$key]['total_import_quantity'] += $quantity;
                $import_quantities_by_product[$key]['rows'][] = $i + 1;
            }
        }

        // Bước 3: So sánh tổng số lượng nhập với số lượng đơn hàng đã đặt
        foreach ($import_quantities_by_product as $key => $import_data) {
            $ordered_quantity = OrderDetail::where('product_id', $import_data['product_id'])
                ->where('product_detail_id', $import_data['product_detail_id'])
                ->when($import_data['size_id'], function($query) use ($import_data) {
                    return $query->where('size_id', $import_data['size_id']);
                })
                ->sum('quantity');

            if ($import_data['total_import_quantity'] < $ordered_quantity) {
                $rows_text = !empty($import_data['rows']) ? 'Dòng '.implode(', ', $import_data['rows']).': ' : '';
                $validation_errors[] = $rows_text.'Tổng số lượng nhập ('.$import_data['total_import_quantity'].') không được nhỏ hơn số lượng đơn hàng đã đặt ('.$ordered_quantity.').';
            }
        }


        if (!empty($validation_errors)) {
            $this->dispatch('successImportProduct', [
                'title' => 'Thất bại',
                'message' => implode('<br>', $validation_errors),
                'type' => 'error',
                'timeout' => 3000
            ]);
            $this->isProcessing = false;
            return;
        }
        else {
             // Xóa các item đã đánh dấu xóa
            if(!empty($this->deleted_detail_ids)){
                ImportProductDetail::whereIn('id', $this->deleted_detail_ids)->delete();
            }

            // Cập nhật thông tin import product
            $import_product = ImportProduct::find($this->id);
            $import_product->name = $this->import_product_name;
            $import_product->save();

            // Cập nhật chi tiết hiện có (chỉ những item còn lại)
            $existing_details = ImportProductDetail::where('import_product_id', $this->id)->get();
            foreach ($existing_details as $detail) {
                // Tìm index của detail này trong mảng hiện tại
                $current_index = array_search($detail->id, $this->import_product_detail_ids);
                if($current_index !== false && isset($this->import_product_detail_qnty[$current_index])){
                    $detail->quantity = $this->import_product_detail_qnty[$current_index];
                    $detail->save();
                }
            }

            // Thêm mới các item
            for ($i = $this->existing_detail_count; $i < $this->import_product_detail_count; $i++) {
                $new_detail = new ImportProductDetail();
                $new_detail->import_product_id = $this->id;
                $new_detail->product_id = $this->product_id[$i];
                $new_detail->product_detail_id = $this->product_detail_id[$i];
                if(isset($this->size_id[$i])){
                    $new_detail->size_id = $this->size_id[$i];
                }
                $new_detail->quantity = $this->import_product_detail_qnty[$i];
                $new_detail->save();
            }

            $this->dispatch('successImportProduct', [
                'title' => 'Thành công',
                'message' => 'Cập nhật nhập hàng thành công.',
                'type' => 'success',
                'timeout' => 3000
            ]);
            $this->isProcessing = false;
            return redirect()->route('admin.import-product');
        }

       
    }

    public function render()
    {
        $import_product = ImportProduct::find($this->id);
        $import_product_details = ImportProductDetail::where('import_product_id', $this->id)->get();
        $products = Product::all();
        return view('livewire.admin.inventory.edit-import-product', [
            'import_product' => $import_product, 
            'import_product_details' => $import_product_details,
            'products' => $products,
            'product_detail_list' => $this->product_detail_list,
            'product_size_list' => $this->product_size_list,
            'disabled_select_yn' => $this->disabled_select_yn
        ]);
    }
}
