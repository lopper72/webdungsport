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
    public $total_amount = 0;

    public function mount($order_id, $payment_status)
    {
        $this->order_id = $order_id;
        $order = Order::findOrFail($order_id);
        $this->payment_status = $payment_status === 'pending' ? 'unpaid' : $payment_status;
        $this->paid_amount = $order->paid_amount ?? 0;
        $this->total_amount = $order->total_amount;
    }

    public function updateStatus(){
        $order = Order::findOrFail($this->order_id);
        $this->syncPaymentAmount();
        $this->validate([
            'payment_status' => 'required|in:paid,partial,unpaid',
            'paid_amount' => 'required|numeric|min:0|lte:total_amount',
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

        $this->redirectRoute('admin.orders');
    }

    public function updatedPaymentStatus()
    {
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
    }
    public function render()
    {
        return view('livewire.admin.order.update-payment-status-modal');
    }
}
