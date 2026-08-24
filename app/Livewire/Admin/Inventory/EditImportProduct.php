<?php

namespace App\Livewire\Admin\Inventory;

use App\Models\ImportProduct;
use App\Models\ImportProductDetail;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\ProductDetail;
use App\Models\ProductSize;
use App\Models\SalesReturnDetail;
use Livewire\Component;

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
        $importProduct = ImportProduct::findOrFail($id);
        $details = ImportProductDetail::where('import_product_id', $id)->get();

        $this->import_product_code = $importProduct->code;
        $this->import_product_name = $importProduct->name;
        $this->existing_detail_count = count($details);

        foreach ($details as $index => $detail) {
            $this->import_product_detail_ids[$index] = $detail->id;
            $this->import_product_detail_qnty[$index] = $detail->quantity;
            $this->product_id[$index] = $detail->product_id;
            $this->product_detail_id[$index] = $detail->product_detail_id;
            $this->size_id[$index] = $detail->size_id;
            $this->disabled_select_yn[$index] = "n";
            $this->product_detail_list[$index] = ProductDetail::where('product_id', $detail->product_id)->get();
            $this->product_size_list[$index] = $this->loadSizeOptions($detail->product_id, $detail->size_id);
        }

        $this->import_product_detail_count = count($details);
    }

    public function addImportProductDetail()
    {
        $newIndex = $this->import_product_detail_count;
        $this->import_product_detail_count++;

        $this->product_id[$newIndex] = "";
        $this->product_detail_id[$newIndex] = "";
        $this->size_id[$newIndex] = "";
        $this->import_product_detail_qnty[$newIndex] = "";
        $this->disabled_select_yn[$newIndex] = "n";
        $this->import_product_detail_ids[$newIndex] = null;
        $this->product_detail_list[$newIndex] = [];
        $this->product_size_list[$newIndex] = [];
    }

    public function removeImportProductDetail($index)
    {
        if ($index < $this->existing_detail_count || $this->import_product_detail_count <= 0) {
            return;
        }

        array_splice($this->product_id, $index, 1);
        array_splice($this->product_detail_id, $index, 1);
        array_splice($this->size_id, $index, 1);
        array_splice($this->import_product_detail_qnty, $index, 1);
        array_splice($this->disabled_select_yn, $index, 1);
        array_splice($this->import_product_detail_ids, $index, 1);

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
        $newIndex = $this->import_product_detail_count;
        $this->import_product_detail_count++;

        $this->product_id[$newIndex] = $this->product_id[$index] ?? "";
        $this->product_detail_id[$newIndex] = $this->product_detail_id[$index] ?? "";
        $this->product_detail_list[$newIndex] = $this->product_detail_list[$index] ?? [];
        $this->product_size_list[$newIndex] = $this->product_size_list[$index] ?? [];
        $this->size_id[$newIndex] = "";
        $this->import_product_detail_qnty[$newIndex] = "";
        $this->disabled_select_yn[$newIndex] = "n";
        $this->import_product_detail_ids[$newIndex] = null;
    }

    public function pullDropdown($index)
    {
        $productId = $this->product_id[$index] ?? '';
        $this->product_detail_id[$index] = "";
        $this->size_id[$index] = "";
        $this->product_detail_list[$index] = $productId ? ProductDetail::where('product_id', $productId)->get() : collect();
        $this->product_size_list[$index] = $productId ? ProductSize::where('product_id', $productId)->get() : collect();
    }

    public function update2ImportProduct()
    {
        if ($this->isProcessing) {
            return;
        }

        $this->isProcessing = true;

        $this->validate([
            'import_product_name' => 'required',
        ], [
            'import_product_name.required' => 'Vui lòng nhập tiêu đề.',
        ]);

        if ($this->import_product_detail_count == 0) {
            $this->dispatchImportMessage('Thất bại', 'Bạn chưa chọn sản phẩm, vui lòng nhập lại.', 'error');
            $this->isProcessing = false;
            return;
        }

        $validationErrors = array_merge(
            $this->validateImportDetails(),
            $this->validateInventoryAfterUpdate()
        );

        if (!empty($validationErrors)) {
            $this->dispatchImportMessage('Thất bại', implode('<br>', $validationErrors), 'error');
            $this->isProcessing = false;
            return;
        }

        if (!empty($this->deleted_detail_ids)) {
            ImportProductDetail::whereIn('id', $this->deleted_detail_ids)->delete();
        }

        $importProduct = ImportProduct::findOrFail($this->id);
        $importProduct->name = $this->import_product_name;
        $importProduct->save();

        for ($i = 0; $i < $this->import_product_detail_count; $i++) {
            $detailId = $this->import_product_detail_ids[$i] ?? null;
            $detail = $detailId ? ImportProductDetail::find($detailId) : new ImportProductDetail();

            if (!$detail) {
                continue;
            }

            $detail->import_product_id = $this->id;
            $detail->product_id = $this->product_id[$i];
            $detail->product_detail_id = $this->product_detail_id[$i];
            $detail->size_id = $this->size_id[$i] ?? null;
            $detail->quantity = $this->import_product_detail_qnty[$i];
            $detail->save();
        }

        $this->dispatchImportMessage('Thành công', 'Cập nhật nhập hàng thành công.', 'success');
        $this->isProcessing = false;

        return redirect()->route('admin.import-product');
    }

    public function render()
    {
        return view('livewire.admin.inventory.edit-import-product', [
            'import_product' => ImportProduct::find($this->id),
            'import_product_details' => ImportProductDetail::where('import_product_id', $this->id)->get(),
            'products' => Product::all(),
            'product_detail_list' => $this->product_detail_list,
            'product_size_list' => $this->product_size_list,
            'disabled_select_yn' => $this->disabled_select_yn,
        ]);
    }

    /**
     * Load size options for a row. When the stored size_id references a
     * product_size that no longer belongs to the row's product (orphaned /
     * mismatched data), still prepend it so the dropdown keeps showing the
     * currently saved value instead of appearing empty.
     */
    private function loadSizeOptions($productId, $sizeId)
    {
        $sizes = ProductSize::where('product_id', $productId)->get();

        if ($sizeId !== null && $sizeId !== '' && !$sizes->contains('id', $sizeId)) {
            $orphan = ProductSize::find($sizeId);
            if ($orphan) {
                $sizes->prepend($orphan);
            }
        }

        return $sizes;
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
                $this->addError('import_product_detail_qnty.' . $i, 'Vui lòng nhập số lượng.');
            } elseif (!is_numeric($quantity)) {
                $errors[] = "Dòng {$row}: Số lượng phải là số.";
                $this->addError('import_product_detail_qnty.' . $i, 'Số lượng phải là số.');
            } elseif ((float) $quantity < 0) {
                $errors[] = "Dòng {$row}: Số lượng không được nhỏ hơn 0.";
                $this->addError('import_product_detail_qnty.' . $i, 'Số lượng không được nhỏ hơn 0.');
            }

            if (ProductSize::where('product_id', $productId)->exists()) {
                if (!$sizeId || !ProductSize::where('id', $sizeId)->where('product_id', $productId)->exists()) {
                    $errors[] = "Dòng {$row}: Vui lòng chọn size hợp lệ.";
                    $this->addError('size_id.' . $i, 'Vui lòng chọn size.');
                }
            }
        }

        return $errors;
    }

    private function validateInventoryAfterUpdate(): array
    {
        $errors = [];
        $importsByKey = [];
        $affectedKeys = [];

        $originalDetails = ImportProductDetail::where('import_product_id', $this->id)->get();
        foreach ($originalDetails as $detail) {
            $affectedKeys[$this->inventoryKey($detail->product_id, $detail->product_detail_id, $detail->size_id)] = [
                'product_id' => $detail->product_id,
                'product_detail_id' => $detail->product_detail_id,
                'size_id' => $detail->size_id,
                'rows' => [],
            ];
        }

        $otherImportDetails = ImportProductDetail::whereNotIn('id', $this->deleted_detail_ids)
            ->where('import_product_id', '!=', $this->id)
            ->get();

        foreach ($otherImportDetails as $detail) {
            $this->addImportQuantity($importsByKey, $detail->product_id, $detail->product_detail_id, $detail->size_id, (int) $detail->quantity);
        }

        for ($i = 0; $i < $this->import_product_detail_count; $i++) {
            $productId = $this->product_id[$i] ?? null;
            $productDetailId = $this->product_detail_id[$i] ?? null;
            $sizeId = $this->size_id[$i] ?? null;
            $quantity = $this->import_product_detail_qnty[$i] ?? 0;

            if (!$productId || !$productDetailId || !is_numeric($quantity)) {
                continue;
            }

            $key = $this->inventoryKey($productId, $productDetailId, $sizeId);
            $this->addImportQuantity($importsByKey, $productId, $productDetailId, $sizeId, (int) $quantity);

            if (!isset($affectedKeys[$key])) {
                $affectedKeys[$key] = [
                    'product_id' => $productId,
                    'product_detail_id' => $productDetailId,
                    'size_id' => $sizeId,
                    'rows' => [],
                ];
            }

            $affectedKeys[$key]['rows'][] = $i + 1;
        }

        foreach ($affectedKeys as $key => $item) {
            $totalImported = $importsByKey[$key]['quantity'] ?? 0;
            $totalSold = $this->soldQuantity($item['product_id'], $item['product_detail_id'], $item['size_id']);
            $totalReturned = $this->returnedQuantity($item['product_id'], $item['product_detail_id'], $item['size_id']);
            $availableAfter = $totalImported + $totalReturned - $totalSold;

            if ($availableAfter < 0) {
                $rowsText = !empty($item['rows']) ? 'Dòng ' . implode(', ', $item['rows']) . ': ' : '';
                $errors[] = $rowsText . 'Tồn kho sẽ bị âm sau khi sửa. Tổng nhập (' . $totalImported . ') + trả hàng (' . $totalReturned . ') không đủ cho số lượng đã bán (' . $totalSold . ').';
            }
        }

        return $errors;
    }

    private function addImportQuantity(array &$importsByKey, $productId, $productDetailId, $sizeId, int $quantity): void
    {
        $key = $this->inventoryKey($productId, $productDetailId, $sizeId);

        if (!isset($importsByKey[$key])) {
            $importsByKey[$key] = [
                'product_id' => $productId,
                'product_detail_id' => $productDetailId,
                'size_id' => $sizeId,
                'quantity' => 0,
            ];
        }

        $importsByKey[$key]['quantity'] += $quantity;
    }

    private function soldQuantity($productId, $productDetailId, $sizeId): int
    {
        $query = OrderDetail::join('orders', 'order_detail.order_id', '=', 'orders.id')
            ->where('orders.status', 'completed')
            ->where('order_detail.product_id', $productId)
            ->where('order_detail.product_detail_id', $productDetailId);

        $this->applySizeFilter($query, 'order_detail.size_id', $sizeId);

        return (int) $query->sum('order_detail.quantity');
    }

    private function returnedQuantity($productId, $productDetailId, $sizeId): int
    {
        $query = SalesReturnDetail::join('sales_returns', 'sales_return_details.sales_return_id', '=', 'sales_returns.id')
            ->where('sales_returns.status', '<>', 'canceled')
            ->where('sales_return_details.product_id', $productId)
            ->where('sales_return_details.product_detail_id', $productDetailId);

        $this->applySizeFilter($query, 'sales_return_details.size_id', $sizeId);

        return (int) $query->sum('sales_return_details.quantity');
    }

    private function applySizeFilter($query, string $column, $sizeId): void
    {
        if ($sizeId === null || $sizeId === '') {
            $query->whereNull($column);
            return;
        }

        $query->where($column, $sizeId);
    }

    private function inventoryKey($productId, $productDetailId, $sizeId): string
    {
        return $productId . '_' . $productDetailId . '_' . ($sizeId ?: 'null');
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
