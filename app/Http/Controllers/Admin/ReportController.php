<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Brand;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index()
    {
        return view('admin.dashboard.report.list_report');
    }

    public function prelim($id)
    {
        return view('admin.dashboard.report.prelim_report', ['id' => $id]);
    }

    public function inventory()
    {
        return view('admin.dashboard.report.inventory_report');
    }

    public function inventoryHistory($id)
    {
        $product = Product::with(['productDetails', 'productCategory', 'productBrand'])
            ->findOrFail($id);

        $imports = DB::table('import_product_detail')
            ->join('import_product', 'import_product_detail.import_product_id', '=', 'import_product.id')
            ->leftJoin('warehouse', 'import_product.warehouse_id', '=', 'warehouse.id')
            ->leftJoin('product_size', 'import_product_detail.size_id', '=', 'product_size.id')
            ->leftJoin('product_detail', 'import_product_detail.product_detail_id', '=', 'product_detail.id')
            ->leftJoin('products', 'import_product_detail.product_id', '=', 'products.id')
            ->where('import_product_detail.product_id', $id)
            ->select(
                'import_product_detail.created_at as date',
                DB::raw("'Nhập kho' as type"),
                'import_product.code as code',
                'import_product.name as reference_name',
                'warehouse.name as warehouse_name',
                'product_size.size as size_name',
                'product_detail.title as product_model',
                'import_product_detail.quantity as quantity_in',
                DB::raw('0 as quantity_out')
            )
            ->get();

        $exports = DB::table('order_detail')
            ->join('orders', 'order_detail.order_id', '=', 'orders.id')
            ->leftJoin('warehouse', 'order_detail.warehouse_id', '=', 'warehouse.id')
            ->leftJoin('product_size', 'order_detail.size_id', '=', 'product_size.id')
            ->leftJoin('product_detail', 'order_detail.product_detail_id', '=', 'product_detail.id')
            ->leftJoin('products', 'order_detail.product_id', '=', 'products.id')
            ->leftJoin('users', 'orders.user_id', '=', 'users.id')
            ->where('order_detail.product_id', $id)
            ->where('orders.status', '<>', 'rejected')
            ->select(
                DB::raw('COALESCE(orders.order_date, order_detail.created_at) as date'),
                DB::raw("'Xuất bán' as type"),
                'orders.code as code',
                'users.name as reference_name',
                'warehouse.name as warehouse_name',
                'product_size.size as size_name',
                'product_detail.title as product_model',
                DB::raw('0 as quantity_in'),
                'order_detail.quantity as quantity_out'
            )
            ->get();

        $transfers = DB::table('transfer_product_detail')
            ->join('transfer_product', 'transfer_product_detail.transfer_product_id', '=', 'transfer_product.id')
            ->leftJoin('warehouse as from_warehouse', 'transfer_product.from_warehouse_id', '=', 'from_warehouse.id')
            ->leftJoin('warehouse as to_warehouse', 'transfer_product.to_warehouse_id', '=', 'to_warehouse.id')
            ->leftJoin('product_size', 'transfer_product_detail.size_id', '=', 'product_size.id')
            ->leftJoin('product_detail', 'transfer_product_detail.product_detail_id', '=', 'product_detail.id')
            ->leftJoin('products', 'transfer_product_detail.product_id', '=', 'products.id')
            ->where('transfer_product_detail.product_id', $id)
            ->select(
                'transfer_product_detail.created_at as date',
                'transfer_product.code as code',
                'transfer_product.name as reference_name',
                'from_warehouse.name as from_warehouse_name',
                'to_warehouse.name as to_warehouse_name',
                'product_size.size as size_name',
                'product_detail.title as product_model',
                'transfer_product_detail.quantity as quantity'
            )
            ->get()
            ->flatMap(function ($transfer) {
                return collect([
                    (object) [
                        'date'           => $transfer->date,
                        'type'           => 'Chuyển kho - xuất',
                        'code'           => $transfer->code,
                        'reference_name' => $transfer->reference_name,
                        'warehouse_name' => $transfer->from_warehouse_name,
                        'size_name'      => $transfer->size_name,
                        'product_model'  => $transfer->product_model,
                        'quantity_in'    => 0,
                        'quantity_out'   => $transfer->quantity,
                    ],
                    (object) [
                        'date'           => $transfer->date,
                        'type'           => 'Chuyển kho - nhập',
                        'code'           => $transfer->code,
                        'reference_name' => $transfer->reference_name,
                        'warehouse_name' => $transfer->to_warehouse_name,
                        'size_name'      => $transfer->size_name,
                        'product_model'  => $transfer->product_model,
                        'quantity_in'    => $transfer->quantity,
                        'quantity_out'   => 0,
                    ],
                ]);
            });

        $history = $imports
            ->concat($exports)
            ->concat($transfers)
            ->sortByDesc('date')
            ->values();

        // Tính tổng toàn bộ trước khi phân trang
        $totalIn  = $history->sum('quantity_in');
        $totalOut = $history->sum('quantity_out');

        return view('admin.dashboard.report.inventory_history_report', compact(
            'product', 'history', 'totalIn', 'totalOut'
        ));
    }

    public function revenue()
    {
        return view('admin.dashboard.report.revenue_report');
    }

    public function brand()
    {
        return view('admin.dashboard.report.brand_report');
    }

    public function revenueBrand()
    {
        return view('admin.dashboard.report.revenue_brand_report');
    }

    public function customer()
    {
        return view('admin.dashboard.report.customer_report');
    }
}
