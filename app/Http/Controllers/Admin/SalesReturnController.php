<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class SalesReturnController extends Controller
{
    public function index()
    {
        return view('admin.dashboard.sales-return.index');
    }

    public function add()
    {
        return view('admin.dashboard.sales-return.add');
    }
}
