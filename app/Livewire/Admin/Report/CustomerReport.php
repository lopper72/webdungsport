<?php

namespace App\Livewire\Admin\Report;

use Livewire\Component;
use Illuminate\Support\Str;

use Spatie\LaravelImageOptimizer\Facades\ImageOptimizer;
use Illuminate\Support\Facades\Storage;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class CustomerReport extends Component
{

    public $startdate = "";
    public $endate = "";
    public $userid = "";

    protected $queryString = [
        'startdate',
        'endate',
        'userid',
    ];

    public function mount()
    {
        $this->startdate = request()->query('startdate', '');
        $this->endate = request()->query('endate', '');
        $this->userid = request()->query('userid', '');
    }

    public function render()
    {
        $startdate = $this->startdate;
        $endate = $this->endate;
        $userid = $this->userid;

        $salesQuery = DB::table('orders as od')
            ->select('od.user_id', DB::raw('SUM(od.total_amount) as sales_amount'))
            ->where('od.status', 'completed')
            ->groupBy('od.user_id');

        if ($startdate !== '') {
            $salesQuery->whereDate('od.order_date', '>=', $startdate);
        }

        if ($endate !== '') {
            $salesQuery->whereDate('od.order_date', '<=', $endate);
        }

        $returnsQuery = DB::table('sales_returns as sr')
            ->select('sr.user_id', DB::raw('SUM(sr.total_amount) as return_amount'))
            ->where('sr.status', '<>', 'canceled')
            ->groupBy('sr.user_id');

        if ($startdate !== '') {
            $returnsQuery->whereDate('sr.return_date', '>=', $startdate);
        }

        if ($endate !== '') {
            $returnsQuery->whereDate('sr.return_date', '<=', $endate);
        }

        $query = DB::table('users as user')
            ->joinSub($salesQuery, 'sales', function ($join) {
                $join->on('user.id', '=', 'sales.user_id');
            })
            ->leftJoinSub($returnsQuery, 'returns', function ($join) {
                $join->on('user.id', '=', 'returns.user_id');
            })
            ->select(
                'user.id as user_id',
                'user.name',
                DB::raw('(sales.sales_amount - COALESCE(returns.return_amount, 0)) as total_amount')
            )
            ->orderByDesc('total_amount');

        if ($userid !== '') {
            $query->where('user.id', (int) $userid);
        }

        $results = $query->get();

        return view('livewire.admin.report.customer-report', [
            'results' => $results,
            'startdate' => $startdate,
            'endate' => $endate,
        ]);
    }
}
