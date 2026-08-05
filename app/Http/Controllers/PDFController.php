<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PDF;
use App\Models\OrderDetail;
use App\Models\Order;
use App\Models\ProductDetail;
use App\Models\User;
use App\Models\System;
use App\Models\SalesReturnDetail;
use App\Services\DebtService;

class PDFController extends Controller
{
    public function index(){

    }
    public function generatePDF()
    {
        $orderId = request()->route('id');
        $order = Order::find($orderId);
        if (!$order) {
            abort(404);
        }
        $user = User::find($order->user_id);
        $username = $user->name;
        $orderDetails = OrderDetail::where('order_id', $orderId)->get()->map(function ($detail) {
            return [
                'id' => $detail->id,
                'name' => $detail->product_detail->product->name,
                'quantity' => $detail->quantity,
                'price' => $detail->unit_price,
                'total' => $detail->quantity * $detail->unit_price,
            ];
        });
        $total_quantity = $orderDetails->sum('quantity');   
        $total_price = $orderDetails->sum('total');
        $discount = $order->discount_amount;
        $paymentStatus = $order->payment_status === 'pending' ? 'unpaid' : $order->payment_status;
        // Payable Amount = Order Total - Return Offset (theo đơn).
        $returnAdjusted = DebtService::returnAdjustedByOrder((int) $order->id);
        $payableAmount = max((float) $order->total_amount - (float) $returnAdjusted, 0);
        $paidAmount = $paymentStatus === 'paid'
            ? $payableAmount
            : ($paymentStatus === 'unpaid' ? 0 : ($order->paid_amount ?? 0));
        // Outstanding Debt = Payable Amount - Paid Amount.
        $debt = max($payableAmount - $paidAmount, 0);
        // Có phiếu trả hàng (không bị hủy) hay không — dùng để ẩn/hiện dòng Return Offset & Payable Amount.
        $hasReturnOrder = SalesReturnDetail::where('order_id', (int) $order->id)
            ->whereHas('salesReturn', function ($q) {
                $q->where('status', '<>', 'canceled');
            })
            ->exists();
        // Số lượng đã trả theo từng order_detail (chỉ tính phiếu trả không canceled).
        $returnedQuantities = DebtService::returnedQuantitiesByOrder((int) $order->id);
        $totalReturnedQuantity = (int) array_sum($returnedQuantities);
        $totalRemainingQuantity = max((int) $total_quantity - $totalReturnedQuantity, 0);

        
        $date = date('d');
        $month = date('m');
        $year = date('Y');
        $date_now = 'Ngày ' . $date . ' tháng ' . $month . ' năm ' . $year;
        date_default_timezone_set('Asia/Ho_Chi_Minh'); // Set the timezone to your local timezone

        $orderDate = $order->order_date; // Get the order date of the current order
    

        $totalUnpaid_user = Order::where('user_id', '=', $order->user_id)
        ->where('id', '<>', $order->id)
        ->where('created_at', '<', $order->created_at)
        ->whereIn('payment_status', ['unpaid', 'partial', 'pending'])
        ->whereDoesntHave('orderStatus', function($query) {
            $query->where('status', '=', 'rejected');
        })->get()->sum(function ($order) {
            // Công nợ trước đó = tổng (Payable Amount - Paid Amount) của các đơn khác.
            $payable = max((float) $order->total_amount - DebtService::returnAdjustedByOrder((int) $order->id), 0);
            return max($payable - (float) ($order->paid_amount ?? 0), 0);
        });

      
        
        $time = date('H:i');
        $title = System::first()->website;
        $hotline = System::first()->phone;   
        $address = System::first()->address." - ".System::first()->city." - ".System::first()->state;
        $data = [
            'title' => $title,
            'hotline' => $hotline,
            'date_now' => $date_now,
            'time' => $time,
            'orderDetails' => $orderDetails,
            'total_quantity' => $total_quantity,
            'total_price' => $total_price,
            'discount' => $discount,
            'debt' => $debt,
            'username' => $username,
            'totalUnpaid_user' => $totalUnpaid_user,
            'paid_amount' => $paidAmount,
            'payment_status' => $paymentStatus,
            'debt_amount' => $debt,
            'total_customer_debt' => $totalUnpaid_user + $debt,
            'address' => $address,
            'discount_percent' => $order->discount_percent,
            // Đồng bộ với Order Detail page.
            'subtotal_amount' => (float) $order->subtotal_amount,
            'grandtotal_amount' => (float) $order->grandtotal_amount,
            'shipping_amount' => (float) $order->shipping_amount,
            'total_amount' => (float) $order->total_amount,
            'return_adjusted' => $returnAdjusted,
            'payable_amount' => $payableAmount,
            'has_return_order' => $hasReturnOrder,
            // Số lượng trả hàng.
            'returned_quantities' => $returnedQuantities,
            'total_returned_quantity' => $totalReturnedQuantity,
            'total_remaining_quantity' => $totalRemainingQuantity,
        ];

        $pdf = PDF::loadView('admin.dashboard.order.pdf_view', $data);
        $pdf->setPaper('A4', 'portrait');

        // Set font tiếng Việt
        $pdf->set_option('defaultFont', 'DejaVuSans');
        $filename = 'Hóa đơn '.$order->code.'.pdf';
        return $pdf->download($filename);
    }
}
