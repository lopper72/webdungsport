<?php

namespace App\Livewire\Admin\Arap;

use Livewire\Component;
use App\Models\Order;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;
use App\Models\User;

class ViewArap extends Component
{
    use WithPagination, WithoutUrlPagination;

    public $search_input = '';
    public $list_order   = [];
    public $id           = '';
    public $year         = 'ALL';
    public $month        = 'ALL';
    public $payment_amount = '';

    // Preview modal
    public $allocation_preview = [];   // mảng các dòng phân bổ, mỗi dòng có 'applied_amount' có thể sửa
    public $show_preview        = false;
    public $preview_error       = '';  // lỗi validate trong modal
    public $confirm_message     = '';  // thông báo xác nhận hiển thị trong modal header
    public $total_debt          = 0;   // tổng công nợ hiện tại của user
    public $success_message     = '';  // thông báo sau khi lưu thành công

    /* ------------------------------------------------------------------ */
    /*  Filters / Search                                                    */
    /* ------------------------------------------------------------------ */
    public function search()        { $this->resetPage(); }
    public function filterByYear()  { $this->resetPage(); }
    public function filterByMonth() { $this->resetPage(); }

    /* ------------------------------------------------------------------ */
    /*  Bước 1 – Tính phân bổ tự động và mở modal                         */
    /* ------------------------------------------------------------------ */
    public function previewDebtAllocation()
    {
        $this->preview_error   = '';
        $this->confirm_message = '';

        $this->validate([
            'payment_amount' => 'required|numeric|min:1',
        ], [
            'payment_amount.required' => 'Vui lòng nhập số tiền thanh toán.',
            'payment_amount.numeric'  => 'Số tiền thanh toán không hợp lệ.',
            'payment_amount.min'      => 'Số tiền thanh toán phải lớn hơn 0.',
        ]);

        $inputAmount = (float) $this->payment_amount;
        $user        = User::find($this->id);

        $orders = Order::where('user_id', $this->id)
            ->whereIn('payment_status', ['unpaid', 'partial', 'pending'])
            ->where('status', '<>', 'rejected')
            ->orderBy('order_date')
            ->orderBy('id')
            ->get();

        // Tổng công nợ hiện tại
        $this->total_debt = $orders->sum(fn($o) => max((float)$o->total_amount - (float)($o->paid_amount ?? 0), 0));

        // Không cho phép nhập vượt quá tổng công nợ
        if ($inputAmount > $this->total_debt) {
            $this->addError('payment_amount',
                'Số tiền ' . number_format($inputAmount, 0, ',', '.') . ' đ vượt quá tổng công nợ hiện tại là '
                . number_format($this->total_debt, 0, ',', '.') . ' đ. Vui lòng nhập lại.'
            );
            return;
        }

        // Thông báo xác nhận hiển thị trong modal
        $this->confirm_message = 'Bạn muốn thanh toán ' . number_format($inputAmount, 0, ',', '.') . ' đ cho ' . $user->name . '.';

        $remaining = $inputAmount;

        $preview = [];

        foreach ($orders as $order) {
            $currentPaid   = (float) ($order->paid_amount ?? 0);
            $remainingDebt = max((float) $order->total_amount - $currentPaid, 0);

            if ($remainingDebt <= 0) continue;

            $applied = $remaining > 0 ? min($remaining, $remainingDebt) : 0;

            $preview[] = [
                'id'             => $order->id,
                'code'           => $order->code,
                'order_date'     => $order->order_date,
                'total_amount'   => (float) $order->total_amount,
                'before_paid'    => $currentPaid,
                'max_applicable' => $remainingDebt,        // trần tối đa có thể nhập
                'applied_amount' => $applied,              // người dùng có thể sửa field này
            ];

            $remaining -= $applied;
        }

        $this->allocation_preview = $preview;
        $this->show_preview       = true;
    }

    /* ------------------------------------------------------------------ */
    /*  Tính toán lại khi người dùng sửa applied_amount của 1 dòng        */
    /* ------------------------------------------------------------------ */
    public function updatedAllocationPreview()
    {
        // Livewire gọi hook này mỗi khi bất kỳ phần tử nào của mảng thay đổi.
        // Chỉ cần giữ giá trị hợp lệ (không âm, không vượt max_applicable).
        $this->preview_error = '';

        foreach ($this->allocation_preview as $i => &$item) {
            $val = (float) ($item['applied_amount'] ?? 0);
            $val = max(0, $val);
            $val = min($val, $item['max_applicable']);
            $item['applied_amount'] = $val;
        }
        unset($item);
    }

    /* ------------------------------------------------------------------ */
    /*  Bước 2 – Xác nhận lưu                                             */
    /* ------------------------------------------------------------------ */
    public function confirmDebtAllocation()
    {
        $this->preview_error = '';

        if (empty($this->allocation_preview)) {
            $this->preview_error = 'Không có đơn hàng nào để cập nhật.';
            return;
        }

        // Validate: tổng applied không được vượt quá số tiền đã nhập
        $totalApplied   = array_sum(array_column($this->allocation_preview, 'applied_amount'));
        $inputAmount    = (float) $this->payment_amount;

        if (round($totalApplied, 2) > round($inputAmount, 2)) {
            $this->preview_error = 'Tổng tiền phân bổ vượt quá số tiền thanh toán đã nhập.';
            return;
        }

        $count = 0;
        foreach ($this->allocation_preview as $item) {
            if ((float) $item['applied_amount'] <= 0) continue;

            $order = Order::find($item['id']);
            if (!$order) continue;

            $newPaid = (float) ($order->paid_amount ?? 0) + (float) $item['applied_amount'];
            $order->paid_amount    = $newPaid;
            $order->payment_status = ($newPaid >= (float) $order->total_amount) ? 'paid' : 'partial';
            $order->save();
            $count++;
        }

        $this->payment_amount     = '';
        $this->allocation_preview = [];
        $this->show_preview       = false;
        $this->confirm_message    = '';

        $this->success_message = "✓ Đã cập nhật công nợ thành công cho {$count} đơn hàng.";

        // Tự động ẩn sau 4 giây
        $this->dispatch('clear-success-message');
    }

    public function cancelPreview()
    {
        $this->allocation_preview = [];
        $this->show_preview       = false;
        $this->preview_error      = '';
        $this->confirm_message    = '';
        $this->success_message    = '';
    }

    /* ------------------------------------------------------------------ */
    /*  Lifecycle                                                           */
    /* ------------------------------------------------------------------ */
    public function mount($id)
    {
        $this->id = $id;
    }

    public function render()
    {
        $user = User::find($this->id);

        $orders = Order::where('user_id', $this->id)
            ->whereIn('payment_status', ['unpaid', 'partial', 'pending'])
            ->where('status', '!=', 'rejected')
            ->when($this->year  !== 'ALL', fn($q) => $q->whereYear('order_date',  $this->year))
            ->when($this->month !== 'ALL', fn($q) => $q->whereMonth('order_date', $this->month))
            ->when($this->search_input !== '', fn($q) => $q->where('code', 'like', '%' . $this->search_input . '%'))
            ->orderBy('order_date', 'desc')
            ->paginate(1000);

        $this->list_order = collect($orders->items())->map(fn($o) => ['id' => $o->id])->toArray();

        return view('livewire.admin.arap.view-arap', [
            'orders' => $orders,
            'user'   => $user,
        ]);
    }
}
