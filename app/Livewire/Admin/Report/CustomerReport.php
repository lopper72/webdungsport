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

        $where = [];
        $bindings = [];

        if ($startdate != '' && $endate != '') {
            $where[] = 'od.order_date between ? and ?';
            $bindings[] = $startdate;
            $bindings[] = $endate;
        }
        if ($startdate != '' && $endate == '') {
            $where[] = 'od.order_date >= ?';
            $bindings[] = $startdate;
        }
        if ($startdate == '' && $endate != '') {
            $where[] = 'od.order_date <= ?';
            $bindings[] = $endate;
        }
        if ($userid != '') {
            $where[] = 'od.user_id = ?';
            $bindings[] = (int) $userid;
        }

        $whereSql = '';
        if (!empty($where)) {
            $whereSql = ' AND ' . implode(' AND ', $where);
        }

        $results = DB::select(
            'SELECT user.id as user_id, user.name, SUM(od.total_amount) as total_amount
            FROM orders as od
            INNER JOIN users user on user.id = od.user_id
            WHERE 1 = 1 ' . $whereSql . '
            GROUP BY user.id, user.name
            ORDER BY SUM(od.total_amount) DESC',
            $bindings
        );

        return view('livewire.admin.report.customer-report', [
            'results' => $results,
            'startdate' => $startdate,
            'endate' => $endate,
        ]);
    }
}
