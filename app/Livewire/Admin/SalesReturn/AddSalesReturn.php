<?php

namespace App\Livewire\Admin\SalesReturn;

use App\Models\OrderDetail;
use App\Models\Order;
use App\Models\SalesReturn;
use App\Models\SalesReturnDetail;
use App\Models\User;
use App\Services\DebtService;
use Illuminate\Support\Facades\DB;
use Livewire\Component;


class AddSalesReturn extends Component
{
    public $code = '';
    public $customer_id = '';
    public $return_date = '';
    public $note = '';
    public $rows = [];
    public $return_quantities = [];
    public $return_prices = [];
    public $return_notes = [];
    public $current_debt = 0;

    public function mount()
    {
        $this->code = 'SRT' . time() . rand(100, 999);
        $this->return_date = now()->format('Y-m-d');
        $this->customer_id = request()->query('userid', '');

        if ($this->customer_id !== '') {
            $this->loadReturnableItems();
        }
    }

    public function updatedCustomerId()
    {
        $this->loadReturnableItems();
    }

    public function setCustomerId($customer_id)
    {
        $this->customer_id = $customer_id;
        $this->loadReturnableItems();
    }

    public function loadReturnableItems()
    {
        $this->rows = [];
        $this->return_quantities = [];
        $this->return_prices = [];
        $this->return_notes = [];

        if ($this->customer_id === '') {
            return;
        }

        $details = OrderDetail::query()
            ->with(['order.customer', 'product', 'product_detail', 'product_size', 'warehouse'])
            ->whereHas('order', function ($query) {
                $query->where('user_id', $this->customer_id)
                    ->where('status', 'completed');
            })
            ->orderByDesc('id')
            ->get();

        $this->current_debt = $this->customerDebt();

        foreach ($details as $detail) {
            $returnedQuantity = SalesReturnDetail::query()
                ->join('sales_returns', 'sales_return_details.sales_return_id', '=', 'sales_returns.id')
                ->where('sales_return_details.order_detail_id', $detail->id)
                ->where('sales_returns.status', '<>', 'canceled')
                ->sum('sales_return_details.quantity');
            $remainingQuantity = (int) $detail->quantity - (int) $returnedQuantity;

            if ($remainingQuantity <= 0) {
                continue;
            }

            $this->rows[] = [
                'order_id' => $detail->order_id,
                'order_code' => $detail->order?->code,
                'order_detail_id' => $detail->id,
                'product_id' => $detail->product_id,
                'product_name' => $detail->product?->name,
                'product_detail_id' => $detail->product_detail_id,
                'product_detail_name' => $detail->product_detail?->title ?? $detail->product_detail?->color ?? '',
                'size_id' => $detail->size_id,
                'size_name' => $detail->product_size?->size ?? '',
                'warehouse_id' => $detail->warehouse_id,
                'warehouse_name' => $detail->warehouse?->name,
                'sold_quantity' => (int) $detail->quantity,
                'returned_quantity' => (int) $returnedQuantity,
                'remaining_quantity' => $remainingQuantity,
                'unit_price' => (float) $detail->unit_price,
            ];

            $this->return_quantities[$detail->id] = 0;
            $this->return_prices[$detail->id] = (float) $detail->unit_price;
            $this->return_notes[$detail->id] = '';
        }
    }

    public function getTotalAmountProperty()
    {
        return collect($this->rows)->sum(function ($row) {
            $detailId = $row['order_detail_id'];
            $quantity = (int) ($this->return_quantities[$detailId] ?? 0);
            $price = (float) ($this->return_prices[$detailId] ?? 0);

            return $quantity > 0 ? $quantity * $price : 0;
        });
    }

    public function getDebtAdjustmentAmountProperty()
    {
        return min((float) $this->totalAmount, (float) $this->current_debt);
    }

    public function getRefundAmountProperty()
    {
        return max((float) $this->totalAmount - (float) $this->debtAdjustmentAmount, 0);
    }

    /**
     * Bước 1: Kiểm tra dữ liệu và hiển thị hộp thoại xác nhận trước khi lưu.
     * Vì trả hàng/hoàn tiền là thao tác tài chính quan trọng, cần người dùng
     * xác nhận rõ ràng trước khi thực hiện.
     */
    public function confirmBeforeStore()
    {
        $this->validate([
            'code' => 'required|unique:sales_returns,code',
            'customer_id' => 'required|exists:users,id',
            'return_date' => 'required|date',
        ], [
            'customer_id.required' => 'Vui lòng chọn khách hàng.',
            'return_date.required' => 'Vui lòng chọn ngày trả hàng.',
            'code.unique' => 'Mã phiếu trả đã tồn tại.',
        ]);

        $selectedRows = [];

        foreach ($this->rows as $row) {
            $detailId = $row['order_detail_id'];
            $quantity = (int) ($this->return_quantities[$detailId] ?? 0);
            $price = (float) ($this->return_prices[$detailId] ?? 0);

            if ($quantity <= 0) {
                continue;
            }

            if ($quantity > $row['remaining_quantity']) {
                $this->addError("return_quantities.$detailId", 'Số lượng trả không được lớn hơn số lượng còn được trả.');
                return;
            }

            if ($price < 0) {
                $this->addError("return_prices.$detailId", 'Giá trả không được âm.');
                return;
            }

            $selectedRows[] = [$row, $quantity, $price];
        }

        if (empty($selectedRows)) {
            $this->addError('rows', 'Vui lòng nhập ít nhất một sản phẩm trả hàng.');
            return;
        }

        $this->dispatch('confirmSalesReturnSave', [
            'totalAmount' => number_format($this->totalAmount, 0, ',', '.'),
            'debtAdjustmentAmount' => number_format($this->debtAdjustmentAmount, 0, ',', '.'),
            'refundAmount' => number_format($this->refundAmount, 0, ',', '.'),
        ]);
    }

    /**
     * Bước 2: Người dùng đã xác nhận → thực hiện lưu phiếu trả hàng.
     */
    public function confirmStore()
    {
        $this->validate([
            'code' => 'required|unique:sales_returns,code',
            'customer_id' => 'required|exists:users,id',
            'return_date' => 'required|date',
        ], [
            'customer_id.required' => 'Vui lòng chọn khách hàng.',
            'return_date.required' => 'Vui lòng chọn ngày trả hàng.',
            'code.unique' => 'Mã phiếu trả đã tồn tại.',
        ]);

        $selectedRows = [];

        foreach ($this->rows as $row) {
            $detailId = $row['order_detail_id'];
            $quantity = (int) ($this->return_quantities[$detailId] ?? 0);
            $price = (float) ($this->return_prices[$detailId] ?? 0);

            if ($quantity <= 0) {
                continue;
            }

            if ($quantity > $row['remaining_quantity']) {
                $this->addError("return_quantities.$detailId", 'Số lượng trả không được lớn hơn số lượng còn được trả.');
                return;
            }

            if ($price < 0) {
                $this->addError("return_prices.$detailId", 'Giá trả không được âm.');
                return;
            }

            $selectedRows[] = [$row, $quantity, $price];
        }

        if (empty($selectedRows)) {
            $this->addError('rows', 'Vui lòng nhập ít nhất một sản phẩm trả hàng.');
            return;
        }

        DB::transaction(function () use ($selectedRows) {
            $debtAdjustmentAmount = min((float) $this->totalAmount, (float) $this->customerDebt());
            $refundAmount = max((float) $this->totalAmount - $debtAdjustmentAmount, 0);

            $salesReturn = SalesReturn::create([
                'code' => $this->code,
                'user_id' => $this->customer_id,
                'return_date' => $this->return_date,
                'total_amount' => $this->totalAmount,
                'debt_adjustment_amount' => $debtAdjustmentAmount,
                'refund_amount' => $refundAmount,
                'status' => 'completed',
                'note' => $this->note,
            ]);

            foreach ($selectedRows as [$row, $quantity, $price]) {
                SalesReturnDetail::create([
                    'sales_return_id' => $salesReturn->id,
                    'order_id' => $row['order_id'],
                    'order_detail_id' => $row['order_detail_id'],
                    'product_id' => $row['product_id'],
                    'product_detail_id' => $row['product_detail_id'],
                    'size_id' => $row['size_id'],
                    'warehouse_id' => $row['warehouse_id'],
                    'quantity' => $quantity,
                    'unit_price' => $price,
                    'total_amount' => $quantity * $price,
                    'note' => $this->return_notes[$row['order_detail_id']] ?? null,
                ]);
            }

            // Cập nhật lại trạng thái thanh toán của các đơn hàng bị ảnh hưởng.
            // Trả hàng làm giảm Payable Amount (Return Offset), nên trạng thái
            // thanh toán phải được tính lại từ Paid Amount và Payable Amount mới.
            // Ví dụ: trả hết hàng → Payable = 0 → nếu khách đã trả tiền thì trạng thái
            // trở thành "Đã thanh toán", nếu chưa trả tiền thì "Chưa thanh toán"
            // (nhưng không còn công nợ vì Payable = 0).
            $affectedOrderIds = collect($selectedRows)->pluck('0.order_id')->unique();
            foreach ($affectedOrderIds as $affectedOrderId) {
                $order = Order::find($affectedOrderId);
                if (!$order) {
                    continue;
                }

                $payable = max((float) $order->total_amount - DebtService::returnAdjustedByOrder((int) $order->id), 0);
                $paid = (float) ($order->paid_amount ?? 0);
                $newStatus = DebtService::paymentStatus($paid, $payable);

                if ($order->payment_status !== $newStatus) {
                    $order->update(['payment_status' => $newStatus]);
                }
            }
        });


        session()->flash('message', 'Đã tạo phiếu trả hàng thành công.');

        return redirect()->route('admin.sales-returns');
    }

    public function render()
    {
        return view('livewire.admin.sales-return.add-sales-return', [
            'customers' => User::where('username', '!=', 'm8')->get(),
            'totalAmount' => $this->totalAmount,
            'debtAdjustmentAmount' => $this->debtAdjustmentAmount,
            'refundAmount' => $this->refundAmount,
        ]);
    }

    private function customerDebt()
    {
        if ($this->customer_id === '') {
            return 0;
        }

        return DebtService::currentTotalDebt((int) $this->customer_id);
    }

}


