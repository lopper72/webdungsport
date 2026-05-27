<?php

namespace App\Livewire\Admin\Report;

use Illuminate\Support\Facades\DB;
use Livewire\Component;

class RevenueBrandReport extends Component
{
    public $id = null;
    public $startdate = "";
    public $endate = "";
    public $brandId = "";

    public function mount($id = null)
    {
        $this->id = $id;
        $this->startdate = request()->startdate ?? "";
        $this->endate = request()->endate ?? "";
        $this->brandId = request()->brandId ?? "";
    }

    private function revenueByBrandQuery()
    {
        $query = DB::table('order_detail')
            ->join('orders', 'order_detail.order_id', '=', 'orders.id')
            ->join('products', 'order_detail.product_id', '=', 'products.id')
            ->leftJoin('brands', 'products.brand_id', '=', 'brands.id')
            ->select(
                'brands.id as brand_id',
                'brands.code as brand_code',
                DB::raw('COALESCE(brands.name, "Không có nhãn hàng") as brand_name'),
                DB::raw('COUNT(DISTINCT orders.id) as total_orders'),
                DB::raw('SUM(order_detail.quantity) as total_quantity'),
                DB::raw('SUM(order_detail.total_amount) as total_amount')
            )
            ->where('orders.status', '<>', 'rejected')
            ->groupBy('brands.id', 'brands.code', 'brands.name')
            ->orderByDesc('total_amount');

        if ($this->startdate !== "") {
            $query->whereDate('orders.order_date', '>=', $this->startdate);
        }

        if ($this->endate !== "") {
            $query->whereDate('orders.order_date', '<=', $this->endate);
        }

        if ($this->brandId !== "") {
            $query->where('products.brand_id', $this->brandId);
        }

        return $query;
    }

    public function render()
    {
        $results = $this->revenueByBrandQuery()->get();
        $totalAmount = $results->sum('total_amount');
        $totalQuantity = $results->sum('total_quantity');
        $totalOrders = $results->sum('total_orders');

        return view('livewire.admin.report.revenue-brand-report', [
            'results' => $results,
            'totalAmount' => $totalAmount,
            'totalQuantity' => $totalQuantity,
            'totalOrders' => $totalOrders,
        ]);
    }
}
