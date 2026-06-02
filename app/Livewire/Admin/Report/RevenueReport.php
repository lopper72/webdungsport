<?php

namespace App\Livewire\Admin\Report;

use Livewire\Component;
use Illuminate\Support\Facades\DB;


class RevenueReport extends Component
{

    public $id = null;
    public $startdate = "";
    public $endate = "";

    public function mount($id = null)
    {
        $this->id = $id;
        $this->startdate = request()->startdate ?? "";
        $this->endate = request()->endate ?? "";
    }

    private function revenueQuery()
    {
        $query = DB::table('orders as od')
            ->join('users as user', 'user.id', '=', 'od.user_id')
            ->select('od.code', 'od.id', 'user.name', 'od.total_amount', 'od.order_date')
            ->orderBy('od.order_date');

        if ($this->startdate !== "") {
            $query->whereDate('od.order_date', '>=', $this->startdate);
        }

        if ($this->endate !== "") {
            $query->whereDate('od.order_date', '<=', $this->endate);
        }

        return $query;
    }

    private function salesReturnTotal()
    {
        $query = DB::table('sales_returns')
            ->where('status', '<>', 'canceled');

        if ($this->startdate !== "") {
            $query->whereDate('return_date', '>=', $this->startdate);
        }

        if ($this->endate !== "") {
            $query->whereDate('return_date', '<=', $this->endate);
        }

        return $query->sum('total_amount');
    }

    public function render()
    {
        $results = $this->revenueQuery()->get();
        $salesAmount = $results->sum('total_amount');
        $returnAmount = $this->salesReturnTotal();
        $totalAmount = $salesAmount - $returnAmount;

        return view('livewire.admin.report.revenue-report', [
            'results' => $results,
            'salesAmount' => $salesAmount,
            'returnAmount' => $returnAmount,
            'totalAmount' => $totalAmount,
        ]);
    }
}
