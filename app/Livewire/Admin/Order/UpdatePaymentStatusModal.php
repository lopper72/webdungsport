<?php

namespace App\Livewire\Admin\Order;

use LivewireUI\Modal\ModalComponent;
use App\Models\Order;
use App\Models\OrderStatus;
use Illuminate\Support\Facades\Auth;

class UpdatePaymentStatusModal extends ModalComponent
{
    public $title_status;
    public $note;
    public $order_id;
    public $payment_status = '';
    public $paid_amount = 0;
    public $subtotal_amount = 0;
    public $discount_amount = 0;
    public $discount_percent = 0;
    public $total_amount = 0;
    public $debt_amount = 0;
    public $previous_debt = 0;
    public $total_customer_debt = 0;
    public $original_payment_status = '';
    public $original_paid_amount = 0;

    public function mount($order_id, $payment_status)
    {
        $this->order_id = $order_id;
        $order = Order::findOrFail($order_id);
        $this->payment_status = $payment_status === 'pending' ? 'unpaid' : $payment_status;
        $this->subtotal_amount = $order->subtotal_amount;
        $this->discount_amount = $order->discount_amount;
        $this->discount_percent = $order->discount_percent;
        $this->total_amount = $order->total_amount;
        $this->original_payment_status = $this->payment_status;
        $this->original_paid_amount = $order->paid_amount ?? 0;
        $this->paid_amount = $this->original_paid_amount;
        $this->previous_debt = Order::where('user_id', $order->user_id)
            ->where('id', '<>', $order->id)
            ->where('created_at', '<', $order->created_at)
            ->whereIn('payment_status', ['unpaid', 'partial', 'pending'])
            ->where('status', '<>', 'rejected')
            ->get()
            ->sum(fn ($item) => max($item->total_amount - ($item->paid_amount ?? 0), 0));

        $this->syncPaymentAmount();
    }

    public function updateStatus(){
        $order = Order::findOrFail($this->order_id);
        $this->syncPaymentAmount();

        $rules = [
            'payment_status' => 'required|in:paid,partial,unpaid',
            'paid_amount' => 'required|numeric|min:0|lte:total_amount',
        ];

        if ($this->payment_status === 'partial') {
            $rules['paid_amount'] = 'required|numeric|min:1|lt:total_amount';
        }

        $this->validate($rules, [
            'payment_status.required' => 'Trường trạng thái thanh toán là bắt buộc.',
            'payment_status.in' => 'Trạng thái thanh toán không hợp lệ.',
            'paid_amount.required' => 'Vui lòng nhập số tiền đã thanh toán.',
            'paid_amount.numeric' => 'Số tiền đã thanh toán không hợp lệ.',
            'paid_amount.min' => 'Số tiền đã thanh toán phải lớn hơn 0.',
            'paid_amount.lt' => 'Thanh toán một phần phải nhỏ hơn tổng tiền đơn hàng.',
            'paid_amount.lte' => 'Số tiền đã thanh toán không được lớn hơn tổng tiền.',
        ]);

        $order->payment_status = $this->payment_status;
        $order->paid_amount = $this->paid_amount;
        $order->save();

        $order_status = new OrderStatus();
        $order_status->order_id = $this->order_id;
        $order_status->status = $this->payment_status;
        $order_status->note = $this->note;
        $order_status->action_by = Auth::user()->id;
        $order_status->save();

        $this->redirectRoute('admin.orders.view', ['id' => $this->order_id]);
    }

    public function updatedPaymentStatus()
    {
        $this->setPaymentStatus($this->payment_status);
    }

    public function setPaymentStatus($status)
    {
        $this->payment_status = $status === 'pending' ? 'unpaid' : $status;

        if ($this->payment_status === 'partial') {
            $this->paid_amount = $this->original_payment_status === 'partial'
                ? $this->original_paid_amount
                : 0;
        }

        $this->syncPaymentAmount();
    }

    public function updatedPaidAmount()
    {
        $this->syncPaymentAmount();
    }

    protected function syncPaymentAmount()
    {
        if ($this->payment_status === 'pending') {
            $this->payment_status = 'unpaid';
        }

        if ($this->payment_status === 'paid') {
            $this->paid_amount = $this->total_amount;
        } elseif ($this->payment_status === 'unpaid') {
            $this->paid_amount = 0;
        } else {
            $this->paid_amount = min((float) $this->paid_amount, (float) $this->total_amount);
            $this->paid_amount = max((float) $this->paid_amount, 0);
        }

        $this->debt_amount = max((float) $this->total_amount - (float) $this->paid_amount, 0);
        $this->total_customer_debt = (float) $this->previous_debt + (float) $this->debt_amount;
    }
    public function render()
    {
        return view('livewire.admin.order.update-payment-status-modal');
    }
}
