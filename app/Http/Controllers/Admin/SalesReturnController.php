<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SalesReturn;
use App\Models\System;
use PDF;

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

    public function view($id)
    {
        $salesReturn = SalesReturn::with([
            'customer',
            'cancelledBy',
            'details.order',
            'details.product',
            'details.productDetail',
            'details.productSize',
            'details.warehouse',
        ])->findOrFail($id);

        return view('admin.dashboard.sales-return.view', compact('salesReturn'));
    }


    public function pdf($id)
    {
        $salesReturn = SalesReturn::with([
            'customer',
            'details.order',
            'details.product',
            'details.productDetail',
            'details.productSize',
            'details.warehouse',
        ])->findOrFail($id);
        $system = System::first();

        $pdf = PDF::loadView('admin.dashboard.sales-return.pdf_view', [
            'salesReturn' => $salesReturn,
            'system' => $system,
            'totalQuantity' => $salesReturn->details->sum('quantity'),
        ]);

        $pdf->setPaper('A4', 'portrait');
        $pdf->set_option('defaultFont', 'DejaVuSans');

        return $pdf->download('Phieu tra hang ' . $salesReturn->code . '.pdf');
    }
}
