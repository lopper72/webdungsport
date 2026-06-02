<?php

namespace App\Livewire\Admin\SalesReturn;

use App\Models\OrderDetail;
use App\Models\Order;
use App\Models\SalesReturn;
use App\Models\SalesReturnDetail;
use App\Models\User;
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
                    ->whereIn('status', ['delivered', 'completed']);
            })
            ->orderByDesc('id')
            ->get();

        $this->current_debt = $this->customerDebt();

        foreach ($details as $detail) {
            $returnedQuantity = SalesReturnDetail::where('order_detail_id', $detail->id)->sum('quantity');
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

    public function store()
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

            $this->applyDebtAdjustment($debtAdjustmentAmount);
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

        return Order::where('user_id', $this->customer_id)
            ->whereIn('payment_status', ['unpaid', 'partial', 'pending'])
            ->where('status', '<>', 'rejected')
            ->get()
            ->sum(fn ($order) => max((float) $order->total_amount - (float) ($order->paid_amount ?? 0), 0));
    }

    private function applyDebtAdjustment($amount)
    {
        $remaining = (float) $amount;

        if ($remaining <= 0) {
            return;
        }

        $orders = Order::where('user_id', $this->customer_id)
            ->whereIn('payment_status', ['unpaid', 'partial', 'pending'])
            ->where('status', '<>', 'rejected')
            ->orderBy('order_date')
            ->orderBy('id')
            ->get();

        foreach ($orders as $order) {
            if ($remaining <= 0) {
                break;
            }

            $currentPaid = (float) ($order->paid_amount ?? 0);
            $debt = max((float) $order->total_amount - $currentPaid, 0);

            if ($debt <= 0) {
                continue;
            }

            $applied = min($remaining, $debt);
            $newPaid = $currentPaid + $applied;

            $order->paid_amount = $newPaid;
            $order->payment_status = $newPaid >= (float) $order->total_amount ? 'paid' : 'partial';
            $order->save();

            $remaining -= $applied;
        }
    }
}
