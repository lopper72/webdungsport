<?php

namespace App\Livewire\Admin\Inventory;

use App\Models\ImportProduct;
use App\Models\ImportProductDetail;
use App\Models\Product;
use App\Models\ProductDetail;
use App\Models\ProductSize;
use App\Models\Warehouse;
use Livewire\Component;

class AddImportProduct extends Component
{
    public $import_product_code = "";
    public $import_product_name = "";
    public $warehouse_id = "1";
    public $product_id = [];
    public $product_detail_id = [];
    public $size_id = [];
    public $import_product_detail_qnty = [];
    public $import_product_detail_count = 0;
    public $product_detail_list;
    public $product_size_list;
    public $disabled_select_yn = [];

    public function addImportProductDetail()
    {
        $this->import_product_detail_count++;
    }

    public function removeImportProductDetail($index)
    {
        if ($this->import_product_detail_count <= 0) {
            return;
        }

        array_splice($this->product_id, $index, 1);
        array_splice($this->product_detail_id, $index, 1);
        array_splice($this->size_id, $index, 1);
        array_splice($this->import_product_detail_qnty, $index, 1);
        array_splice($this->disabled_select_yn, $index, 1);

        if (is_array($this->product_detail_list)) {
            array_splice($this->product_detail_list, $index, 1);
        }

        if (is_array($this->product_size_list)) {
            array_splice($this->product_size_list, $index, 1);
        }

        $this->import_product_detail_count--;
    }

    public function copyImportProductDetail($index)
    {
        $this->import_product_detail_count++;

        if (!isset($this->product_id[$index])) {
            return;
        }

        $firstProductId = array_slice($this->product_id, 0, $index + 1);
        $secondProductId = array_slice($this->product_id, $index + 1);
        $this->product_id = array_merge($firstProductId, [$this->product_id[$index]], $secondProductId);

        $firstProductDetailList = array_slice($this->product_detail_list, 0, $index + 1);
        $secondProductDetailList = array_slice($this->product_detail_list, $index + 1);
        $this->product_detail_list = array_merge($firstProductDetailList, [$this->product_detail_list[$index]], $secondProductDetailList);

        $firstProductDetailId = array_slice($this->product_detail_id, 0, $index + 1);
        $secondProductDetailId = array_slice($this->product_detail_id, $index + 1);
        $this->product_detail_id = array_merge($firstProductDetailId, [$this->product_detail_id[$index]], $secondProductDetailId);

        $firstSizeList = array_slice($this->product_size_list, 0, $index + 1);
        $secondSizeList = array_slice($this->product_size_list, $index + 1);
        $this->product_size_list = array_merge($firstSizeList, [$this->product_size_list[$index]], $secondSizeList);

        $firstSizeId = array_slice($this->size_id, 0, $index + 1);
        $secondSizeId = array_slice($this->size_id, $index + 1);
        $this->size_id = array_merge($firstSizeId, [''], $secondSizeId);

        $firstQuantity = array_slice($this->import_product_detail_qnty, 0, $index + 1);
        $secondQuantity = array_slice($this->import_product_detail_qnty, $index + 1);
        $this->import_product_detail_qnty = array_merge($firstQuantity, [''], $secondQuantity);

        $productId = $this->product_id[$index];
        for ($i = $index; $i <= $this->import_product_detail_count; $i++) {
            if (isset($this->product_id[$i]) && $this->product_id[$i] == $productId) {
                $this->disabled_select_yn[$i] = "y";
            } else {
                $this->disabled_select_yn[$i - 1] = "n";
                $this->disabled_select_yn[$i] = "y";
                if (isset($this->product_id[$i])) {
                    $productId = $this->product_id[$i];
                }
            }
        }

        $this->disabled_select_yn[$this->import_product_detail_count] = "n";
    }

    public function pullDropdown($index)
    {
        $productId = $this->product_id[$index] ?? '';
        $this->product_detail_id[$index] = "";
        $this->size_id[$index] = "";
        $this->product_detail_list[$index] = $productId ? ProductDetail::where('product_id', $productId)->get() : collect();
        $this->product_size_list[$index] = $productId ? ProductSize::where('product_id', $productId)->get() : collect();
    }

    public function storeImportProduct()
    {
        $this->validate([
            'import_product_code' => 'required|unique:import_product,code',
            'import_product_name' => 'required',
        ], [
            'import_product_code.required' => 'Vui lòng nhập mã nhập hàng.',
            'import_product_code.unique' => 'Mã nhập hàng đã tồn tại.',
            'import_product_name.required' => 'Vui lòng nhập tiêu đề.',
        ]);

        if ($this->import_product_detail_count == 0) {
            $this->dispatchImportMessage('Thất bại', 'Bạn chưa chọn sản phẩm, vui lòng nhập lại.', 'error');
            return;
        }

        $errors = $this->validateImportDetails();
        if (!empty($errors)) {
            $this->dispatchImportMessage('Thất bại', implode('<br>', $errors), 'error');
            return;
        }

        $importProduct = new ImportProduct();
        $importProduct->code = $this->import_product_code;
        $importProduct->name = $this->import_product_name;
        $importProduct->warehouse_id = 1;
        $importProduct->save();

        for ($i = 0; $i < $this->import_product_detail_count; $i++) {
            $detail = new ImportProductDetail();
            $detail->import_product_id = $importProduct->id;
            $detail->product_id = $this->product_id[$i];
            $detail->product_detail_id = $this->product_detail_id[$i];
            $detail->size_id = $this->size_id[$i] ?? null;
            $detail->quantity = $this->import_product_detail_qnty[$i];
            $detail->save();
        }

        return redirect()->route('admin.import-product');
    }

    public function handleDetele($id)
    {
        return;
    }

    public function render()
    {
        $idLatest = ImportProduct::all();
        $warehouses = Warehouse::all();
        $products = Product::all();
        $this->import_product_code = 'IMP-' . str_pad(count($idLatest) + 1, 4, '0', STR_PAD_LEFT);

        return view('livewire.admin.inventory.add-import-product', [
            'warehouses' => $warehouses,
            'products' => $products,
            'product_detail_list' => $this->product_detail_list,
            'product_size_list' => $this->product_size_list,
            'disabled_select_yn' => $this->disabled_select_yn,
        ]);
    }

    private function validateImportDetails(): array
    {
        $errors = [];

        for ($i = 0; $i < $this->import_product_detail_count; $i++) {
            $row = $i + 1;
            $productId = $this->product_id[$i] ?? null;
            $productDetailId = $this->product_detail_id[$i] ?? null;
            $sizeId = $this->size_id[$i] ?? null;
            $quantity = $this->import_product_detail_qnty[$i] ?? null;

            if (!$productId || !Product::whereKey($productId)->exists()) {
                $errors[] = "Dòng {$row}: Vui lòng chọn sản phẩm hợp lệ.";
                continue;
            }

            if (!$productDetailId || !ProductDetail::where('id', $productDetailId)->where('product_id', $productId)->exists()) {
                $errors[] = "Dòng {$row}: Vui lòng chọn mẫu sản phẩm hợp lệ.";
            }

            if ($quantity === '' || $quantity === null) {
                $errors[] = "Dòng {$row}: Vui lòng nhập số lượng.";
            } elseif (!is_numeric($quantity)) {
                $errors[] = "Dòng {$row}: Số lượng phải là số.";
            } elseif ((float) $quantity < 0) {
                $errors[] = "Dòng {$row}: Số lượng không được nhỏ hơn 0.";
            }

            if (ProductSize::where('product_id', $productId)->exists()) {
                if (!$sizeId || !ProductSize::where('id', $sizeId)->where('product_id', $productId)->exists()) {
                    $errors[] = "Dòng {$row}: Vui lòng chọn size hợp lệ.";
                }
            }
        }

        return $errors;
    }

    private function dispatchImportMessage(string $title, string $message, string $type): void
    {
        $this->dispatch('successImportProduct', [
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'timeout' => 3000,
        ]);
    }
}
