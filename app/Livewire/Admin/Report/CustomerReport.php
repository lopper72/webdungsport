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
        $where = 'where 1 = 1 ';
        $startdate = $this->startdate;
        $endate = $this->endate;
        $userid = $this->userid;
        if ($startdate != '' && $endate != '') {
            $where = "and od.order_date between '" . $startdate . "' and '" . $endate . "'";
        }
        if ($startdate != '' && $endate == '') {
            $where = "and od.order_date >= '" . $startdate . "'";
        }
        if ($startdate == '' && $endate != '') {
            $where = "and od.order_date <= '" . $endate . "'";
        }
        if ($userid != '') {
            $where .= " and od.user_id = " . (int) $userid;
        }

        $results = DB::select(
            'SELECT user.id as user_id, user.name, SUM(od.total_amount) as total_amount
            FROM orders as od
            INNER JOIN users user on user.id = od.user_id
            WHERE 1 = 1 ' . $where . '
            GROUP BY user.id, user.name'
        );

        return view('livewire.admin.report.customer-report', [
            'results' => $results,
            'startdate' => $startdate,
            'endate' => $endate,
        ]);
    }
}
