<?php

namespace App\Livewire\Admin\Order;

use LivewireUI\Modal\ModalComponent;
use App\Models\Order;
use App\Models\OrderStatus;
use App\Services\DebtService;
use Illuminate\Support\Facades\Auth;

class UpdatePaymentStatusModal extends ModalComponent
{
    public $title_status;
    public $note;
    public $order_id;
    public $payment_status = '';
    public $paid_amount = 0;
    public $total_amount = 0;
    public $payable_amount = 0;
    public $total_return_adjusted = 0;

    public function mount($order_id, $payment_status)
    {
        $this->order_id = $order_id;
        $order = Order::findOrFail($order_id);
        $this->payment_status = $payment_status === 'pending' ? 'unpaid' : $payment_status;
        $this->paid_amount = $order->paid_amount ?? 0;
        $this->total_amount = $order->total_amount;
        $this->total_return_adjusted = DebtService::returnAdjustedByOrder((int) $order->id);
        $this->payable_amount = max((float) $order->total_amount - (float) $this->total_return_adjusted, 0);
    }




    public function updateStatus(){
        $order = Order::findOrFail($this->order_id);
        $this->syncPaymentAmount();

        $this->validate([
            'payment_status' => 'required|in:paid,partial,unpaid',
            'paid_amount' => 'required|numeric|min:0|lte:payable_amount',
        ], [
            'paid_amount.lte' => 'Số tiền đã thanh toán không được lớn hơn số tiền phải trả ('
                . number_format($this->payable_amount, 0, ',', '.') . ' đ).',
        ]);

        // Trạng thái thanh toán phải khớp với số tiền đã thanh toán thực tế.
        $this->validatePaymentStatusAgainstPaidAmount();
        if ($this->getErrorBag()->has('paid_amount')) {
            return;
        }

        // Chặn việc giảm paid_amount làm công nợ vượt quá giới hạn


        // (bảo vệ khoản cấn trừ công nợ từ trả hàng).
        $debtCheck = DebtService::validateDebtReduction(
            $order->user_id,
            $order->id,
            (float) $this->paid_amount
        );

        if (!$debtCheck['allowed']) {
            $this->addError('paid_amount', $debtCheck['message']);
            return;
        }

        $order->payment_status = $this->payment_status;
        $order->paid_amount = $this->paid_amount;


        $shouldCompleteOrder = in_array($order->status, ['pending', 'confirmed', 'shipping', 'delivered'], true);

        if ($shouldCompleteOrder) {
            $order->status = 'completed';
        }

        $order->save();

        $order_status = new OrderStatus();
        $order_status->order_id = $this->order_id;
        $order_status->status = $this->payment_status;
        $order_status->note = $this->note;
        $order_status->action_by = Auth::user()->id;
        $order_status->save();

        if ($shouldCompleteOrder) {
            $completedStatus = new OrderStatus();
            $completedStatus->order_id = $this->order_id;
            $completedStatus->status = 'completed';
            $completedStatus->note = $this->note;
            $completedStatus->action_by = Auth::user()->id;
            $completedStatus->save();
        }

        $this->redirectRoute('admin.orders');
    }

    public function updatedPaymentStatus()
    {
        $this->applyPaymentStatusToPaidAmount();
        $this->syncPaymentAmount();
    }

    public function updatedPaidAmount()
    {
        $this->syncPaymentAmount();
    }

    /**
     * Khi người dùng đổi Payment Status, tự động cập nhật Paid Amount:
     * - Unpaid  => Paid Amount = 0
     * - Paid    => Paid Amount = Payable Amount
     * - Partial => giữ nguyên Paid Amount hiện tại (người dùng tự nhập)
     */
    protected function applyPaymentStatusToPaidAmount()
    {
        $payable = (float) $this->payable_amount;

        if ($this->payment_status === 'unpaid') {
            $this->paid_amount = 0;
        } elseif ($this->payment_status === 'paid') {
            $this->paid_amount = $payable;
        }
        // 'partial' => giữ nguyên paid_amount hiện tại.
    }


    protected function syncPaymentAmount()
    {
        // Payable Amount = Order Total - Return Offset.
        $this->paid_amount = min((float) $this->paid_amount, (float) $this->payable_amount);
        $this->paid_amount = max((float) $this->paid_amount, 0);

        // KHÔNG tự động đổi payment_status. Trạng thái do người dùng chọn,
        // sẽ được kiểm tra khớp với Paid Amount khi lưu (validatePaymentStatusAgainstPaidAmount).
    }



    protected function validatePaymentStatusAgainstPaidAmount()
    {
        $paid    = (float) $this->paid_amount;
        $payable = (float) $this->payable_amount;

        if ($this->payment_status === 'unpaid' && $paid != 0) {
            $this->addError('paid_amount', 'Khi trạng thái là Chưa thanh toán, số tiền đã thanh toán phải bằng 0.');
            return;
        }

        if ($this->payment_status === 'partial' && !($paid > 0 && $paid < $payable)) {
            $this->addError('paid_amount', 'Khi trạng thái là Thanh toán một phần, số tiền đã thanh toán phải lớn hơn 0 và nhỏ hơn số tiền phải trả ('
                . number_format($payable, 0, ',', '.') . ' đ).');
            return;
        }

        if ($this->payment_status === 'paid' && $paid != $payable) {
            $this->addError('paid_amount', 'Khi trạng thái là Đã thanh toán, số tiền đã thanh toán phải bằng số tiền phải trả ('
                . number_format($payable, 0, ',', '.') . ' đ).');
        }
    }



    public function render()

    {
        return view('livewire.admin.order.update-payment-status-modal');
    }
}
