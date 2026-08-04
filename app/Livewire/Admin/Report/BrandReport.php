<?php

namespace App\Livewire\Admin\Report;

use Livewire\Component;
use App\Models\Product;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;

class BrandReport extends Component
{ 
    use WithPagination, WithoutUrlPagination;
    public function render()
    {
        $importedQuantity = DB::table('import_product_detail')
            ->select('product_id', DB::raw('SUM(quantity) as total_imported'))
            ->groupBy('product_id');

        $orderedQuantity = DB::table('order_detail')
            ->join('orders', 'order_detail.order_id', '=', 'orders.id')
            ->select('order_detail.product_id', DB::raw('SUM(order_detail.quantity) as total_ordered'))
            ->where('orders.status', 'completed')
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
            ->leftJoinSub($importedQuantity, 'imported_products', function ($join) {
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

        if (request()->brandId != '') {
            $query->where('products.brand_id', request()->brandId);
        }

        if (request()->categoryID != '') {
            $query->where('products.category_id', request()->categoryID);
        }

        $products = $query->paginate(20);
        
        return view('livewire.admin.report.brand-report', ['products' => $products]);
    }
}
