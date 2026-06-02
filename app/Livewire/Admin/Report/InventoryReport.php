<?php

namespace App\Livewire\Admin\Report;

use Livewire\Component;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;


class InventoryReport extends Component
{
    use WithPagination, WithoutUrlPagination;

    public $selectedProductId = null;
    public $id = null;
    public $brandId = null;
    public $categoryID = null;

    public function mount($id = null)
    {
        $this->id = $id;
        $this->brandId = request()->brandId;
        $this->categoryID = request()->categoryID;
    }

    public function selectProduct($productId)
    {
        $this->selectedProductId = $productId;
    }

    public function closeHistory()
    {
        $this->selectedProductId = null;
    }

    private function productQuery()
    {
        $importedQuantity = DB::table('import_product_detail')
            ->select('product_id', DB::raw('SUM(quantity) as total_imported'))
            ->groupBy('product_id');

        $orderedQuantity = DB::table('order_detail')
            ->join('orders', 'order_detail.order_id', '=', 'orders.id')
            ->select('order_detail.product_id', DB::raw('SUM(order_detail.quantity) as total_ordered'))
            ->where('orders.status', '<>', 'rejected')
            ->groupBy('order_detail.product_id');

        $returnedQuantity = DB::table('sales_return_details')
            ->join('sales_returns', 'sales_return_details.sales_return_id', '=', 'sales_returns.id')
            ->select('sales_return_details.product_id', DB::raw('SUM(sales_return_details.quantity) as total_returned'))
            ->where('sales_returns.status', '<>', 'canceled')
            ->groupBy('sales_return_details.product_id');

        $query = Product::query()
            ->select(
                'products.id',
                'products.code',
                'products.name',
                'products.category_id',
                'products.brand_id',
                'products.retail_price',
                'products.wholesale_price',
                DB::raw('COALESCE(imported_products.total_imported, 0) as total_imported'),
                DB::raw('COALESCE(ordered_products.total_ordered, 0) as total_ordered'),
                DB::raw('COALESCE(returned_products.total_returned, 0) as total_returned')
            )
            ->joinSub($importedQuantity, 'imported_products', function ($join) {
                $join->on('products.id', '=', 'imported_products.product_id');
            })
            ->leftJoinSub($orderedQuantity, 'ordered_products', function ($join) {
                $join->on('products.id', '=', 'ordered_products.product_id');
            })
            ->leftJoinSub($returnedQuantity, 'returned_products', function ($join) {
                $join->on('products.id', '=', 'returned_products.product_id');
            })
            ->with(['productDetails', 'productCategory', 'productBrand'])
            ->orderBy('products.code');

        if ($this->brandId !== null && $this->brandId !== '') {
            $query->where('products.brand_id', $this->brandId);
        }

        if ($this->categoryID !== null && $this->categoryID !== '') {
            $query->where('products.category_id', $this->categoryID);
        }

        return $query;
    }

    private function getSelectedProduct()
    {
        if (!$this->selectedProductId) {
            return null;
        }

        return Product::with(['productCategory', 'productBrand'])->find($this->selectedProductId);
    }

    private function getInventoryHistory()
    {
        if (!$this->selectedProductId) {
            return collect();
        }

        $imports = DB::table('import_product_detail')
            ->join('import_product', 'import_product_detail.import_product_id', '=', 'import_product.id')
            ->leftJoin('warehouse', 'import_product.warehouse_id', '=', 'warehouse.id')
            ->leftJoin('product_size', 'import_product_detail.size_id', '=', 'product_size.id')
            ->where('import_product_detail.product_id', $this->selectedProductId)
            ->select(
                'import_product_detail.created_at as date',
                DB::raw("'Nhập kho' as type"),
                'import_product.code as code',
                'import_product.name as reference_name',
                'warehouse.name as warehouse_name',
                'product_size.size as size_name',
                'import_product_detail.quantity as quantity_in',
                DB::raw('0 as quantity_out')
            )
            ->get();

        $returns = DB::table('sales_return_details')
            ->join('sales_returns', 'sales_return_details.sales_return_id', '=', 'sales_returns.id')
            ->leftJoin('warehouse', 'sales_return_details.warehouse_id', '=', 'warehouse.id')
            ->leftJoin('product_size', 'sales_return_details.size_id', '=', 'product_size.id')
            ->leftJoin('users', 'sales_returns.user_id', '=', 'users.id')
            ->where('sales_return_details.product_id', $this->selectedProductId)
            ->where('sales_returns.status', '<>', 'canceled')
            ->select(
                DB::raw('COALESCE(sales_returns.return_date, sales_return_details.created_at) as date'),
                DB::raw("'Trả hàng' as type"),
                'sales_returns.code as code',
                'users.name as reference_name',
                'warehouse.name as warehouse_name',
                'product_size.size as size_name',
                'sales_return_details.quantity as quantity_in',
                DB::raw('0 as quantity_out')
            )
            ->get();

        $exports = DB::table('order_detail')
            ->join('orders', 'order_detail.order_id', '=', 'orders.id')
            ->leftJoin('warehouse', 'order_detail.warehouse_id', '=', 'warehouse.id')
            ->leftJoin('product_size', 'order_detail.size_id', '=', 'product_size.id')
            ->leftJoin('users', 'orders.user_id', '=', 'users.id')
            ->where('order_detail.product_id', $this->selectedProductId)
            ->where('orders.status', '<>', 'rejected')
            ->select(
                DB::raw('COALESCE(orders.order_date, order_detail.created_at) as date'),
                DB::raw("'Xuất bán' as type"),
                'orders.code as code',
                'users.name as reference_name',
                'warehouse.name as warehouse_name',
                'product_size.size as size_name',
                DB::raw('0 as quantity_in'),
                'order_detail.quantity as quantity_out'
            )
            ->get();

        $transfers = DB::table('transfer_product_detail')
            ->join('transfer_product', 'transfer_product_detail.transfer_product_id', '=', 'transfer_product.id')
            ->leftJoin('warehouse as from_warehouse', 'transfer_product.from_warehouse_id', '=', 'from_warehouse.id')
            ->leftJoin('warehouse as to_warehouse', 'transfer_product.to_warehouse_id', '=', 'to_warehouse.id')
            ->leftJoin('product_size', 'transfer_product_detail.size_id', '=', 'product_size.id')
            ->where('transfer_product_detail.product_id', $this->selectedProductId)
            ->select(
                'transfer_product_detail.created_at as date',
                'transfer_product.code as code',
                'transfer_product.name as reference_name',
                'from_warehouse.name as from_warehouse_name',
                'to_warehouse.name as to_warehouse_name',
                'product_size.size as size_name',
                'transfer_product_detail.quantity as quantity'
            )
            ->get()
            ->flatMap(function ($transfer) {
                return collect([
                    (object) [
                        'date' => $transfer->date,
                        'type' => 'Chuyển kho - xuất',
                        'code' => $transfer->code,
                        'reference_name' => $transfer->reference_name,
                        'warehouse_name' => $transfer->from_warehouse_name,
                        'size_name' => $transfer->size_name,
                        'quantity_in' => 0,
                        'quantity_out' => $transfer->quantity,
                    ],
                    (object) [
                        'date' => $transfer->date,
                        'type' => 'Chuyển kho - nhập',
                        'code' => $transfer->code,
                        'reference_name' => $transfer->reference_name,
                        'warehouse_name' => $transfer->to_warehouse_name,
                        'size_name' => $transfer->size_name,
                        'quantity_in' => $transfer->quantity,
                        'quantity_out' => 0,
                    ],
                ]);
            });

        return $imports
            ->concat($exports)
            ->concat($returns)
            ->concat($transfers)
            ->sortByDesc('date')
            ->values();
    }

    public function render()
    {
        $allProducts = $this->productQuery()->get();
        $products = $this->productQuery()->paginate(20);

        $totalStock = $allProducts->sum(fn ($product) => $product->total_imported - $product->total_ordered + $product->total_returned);
        $totalInventoryAmount = $allProducts->sum(fn ($product) => ($product->total_imported - $product->total_ordered + $product->total_returned) * $product->wholesale_price);

        return view('livewire.admin.report.inventory-report', [
            'products' => $products,
            'totalStock' => $totalStock,
            'totalInventoryAmount' => $totalInventoryAmount,
            'selectedProduct' => $this->getSelectedProduct(),
            'inventoryHistory' => $this->getInventoryHistory(),
        ]);
    }
}
