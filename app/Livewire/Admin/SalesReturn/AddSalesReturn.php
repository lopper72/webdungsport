<?php

namespace App\Livewire\Admin\SalesReturn;

use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\SalesReturn;
use App\Models\SalesReturnDetail;
use App\Models\User;
use App\Services\DebtService;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;

class AddSalesReturn extends Component
{
    use WithPagination, WithoutUrlPagination;

    public $code = '';
    public $customer_id = '';
    public $product_id = '';
    public $order_code_filter = '';
    public $selected_order_id = '';
    public $return_date = '';
    public $note = '';
    public $rows = [];
    public $return_quantities = [];
    public $return_prices = [];
    public $return_notes = [];
    public $current_debt = 0;

    private const RETURNABLE_ORDER_STATUSES = ['delivered', 'completed'];

    public function mount()
    {
        $this->code = 'SRT' . time() . rand(100, 999);
        $this->return_date = now()->format('Y-m-d');
        $this->customer_id = request()->query('userid', '');

        if ($this->customer_id !== '') {
            $this->current_debt = $this->customerDebt();
        }
    }

    public function updatedCustomerId()
    {
        $this->product_id = '';
        $this->order_code_filter = '';
        $this->resetOrderSelection();
        $this->resetPage();
        $this->current_debt = $this->customerDebt();
    }

    public function setCustomerId($customer_id)
    {
        $this->customer_id = $customer_id;
        $this->product_id = '';
        $this->order_code_filter = '';
        $this->resetOrderSelection();
        $this->resetPage();
        $this->current_debt = $this->customerDebt();
    }

    public function updatedProductId()
    {
        $this->resetOrderSelection();
        $this->resetPage();
    }

    public function productChanged()
    {
        $this->resetOrderSelection();
        $this->resetPage();
    }

    public function updatedSelectedOrderId()
    {
        $this->loadReturnableItems();
    }

    public function updatedOrderCodeFilter()
    {
        $this->resetOrderSelection();
        $this->resetPage();
    }

    public function clearProductFilter()
    {
        $this->product_id = '';
        $this->resetOrderSelection();
        $this->resetPage();
    }

    public function selectOrder($orderId)
    {
        $this->selected_order_id = (string) $orderId;
        $this->loadReturnableItems();
    }

    public function loadReturnableItems()
    {
        $this->rows = [];
        $this->return_quantities = [];
        $this->return_prices = [];
        $this->return_notes = [];

        if ($this->customer_id === '' || $this->selected_order_id === '') {
            return;
        }

        $details = OrderDetail::query()
            ->with(['order.customer', 'product', 'product_detail', 'product_size', 'warehouse'])
            ->whereHas('order', function ($query) {
                $query->where('user_id', $this->customer_id)
                    ->where('id', $this->selected_order_id)
                    ->whereIn('status', self::RETURNABLE_ORDER_STATUSES);
            })
            ->orderByDesc('id')
            ->get();

        $this->current_debt = $this->customerDebt();

        foreach ($details as $detail) {
            $returnedQuantity = $this->returnedQuantityForDetail((int) $detail->id);
            $remainingQuantity = (int) $detail->quantity - (int) $returnedQuantity;

            if ($remainingQuantity <= 0) {
                continue;
            }

            $this->rows[] = [
                'order_id' => $detail->order_id,
                'order_code' => $detail->order?->code,
                'order_detail_id' => $detail->id,
                'product_id' => $detail->product_id,
                'product_code' => $detail->product?->code,
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

    public function confirmBeforeStore()
    {
        $selectedRows = $this->validateSelectedRows();

        if ($selectedRows === null) {
            return;
        }

        $this->dispatch('confirmSalesReturnSave', [
            'totalAmount' => number_format($this->totalAmount, 0, ',', '.'),
            'debtAdjustmentAmount' => number_format($this->debtAdjustmentAmount, 0, ',', '.'),
            'refundAmount' => number_format($this->refundAmount, 0, ',', '.'),
        ]);
    }

    public function confirmStore()
    {
        $selectedRows = $this->validateSelectedRows();

        if ($selectedRows === null) {
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
            'customers' => User::where('username', '!=', 'm8')->orderBy('name')->get(),
            'products' => Product::orderBy('name')->get(),
            'orders' => $this->returnableOrders(),
            'totalAmount' => $this->totalAmount,
            'debtAdjustmentAmount' => $this->debtAdjustmentAmount,
            'refundAmount' => $this->refundAmount,
        ]);
    }

    private function returnableOrders()
    {
        if ($this->customer_id === '') {
            return new LengthAwarePaginator([], 0, 5, 1);
        }

        $details = OrderDetail::query()
            ->with(['order', 'product'])
            ->whereHas('order', function ($query) {
                $query->where('user_id', $this->customer_id)
                    ->whereIn('status', self::RETURNABLE_ORDER_STATUSES);
            })
            ->when(trim($this->order_code_filter) !== '', function ($query) {
                $keyword = trim($this->order_code_filter);

                $query->whereHas('order', function ($orderQuery) use ($keyword) {
                    $orderQuery->where('code', 'like', '%' . $keyword . '%');
                });
            })
            ->orderByDesc('order_id')
            ->orderByDesc('id')
            ->get();

        $orders = [];

        foreach ($details as $detail) {
            $remainingQuantity = (int) $detail->quantity - (int) $this->returnedQuantityForDetail((int) $detail->id);

            if ($remainingQuantity <= 0 || !$detail->order) {
                continue;
            }

            $orderId = (int) $detail->order_id;

            if (!isset($orders[$orderId])) {
                $orders[$orderId] = [
                    'id' => $orderId,
                    'code' => $detail->order->code,
                    'order_date' => $this->formatDate($detail->order->order_date),
                    'status' => $detail->order->status,
                    'payment_status' => $detail->order->payment_status,
                    'total_amount' => (float) $detail->order->total_amount,
                    'matched_products' => [],
                    'matches_product_filter' => $this->product_id === '',
                    'remaining_quantity' => 0,
                ];
            }

            $productLabel = trim($detail->product?->name ?? '');
            $orders[$orderId]['matched_products'][$detail->product_id] = $productLabel !== '' ? $productLabel : 'SP #' . $detail->product_id;
            $orders[$orderId]['remaining_quantity'] += $remainingQuantity;

            if ($this->product_id !== '' && (string) $detail->product_id === (string) $this->product_id) {
                $orders[$orderId]['matches_product_filter'] = true;
            }
        }

        $orders = collect($orders)
            ->filter(fn ($order) => $order['matches_product_filter'])
            ->sortByDesc('id')
            ->map(function ($order) {
                $order['matched_products'] = array_values($order['matched_products']);
                unset($order['matches_product_filter']);

                return $order;
            })
            ->values();

        $page = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 5;

        return new LengthAwarePaginator(
            $orders->forPage($page, $perPage)->values(),
            $orders->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'pageName' => 'page']
        );
    }

    private function validateSelectedRows()
    {
        $this->validate([
            'code' => 'required|unique:sales_returns,code',
            'customer_id' => 'required|exists:users,id',
            'selected_order_id' => 'required|exists:orders,id',
            'return_date' => 'required|date',
        ], [
            'customer_id.required' => 'Vui lòng chọn khách hàng.',
            'selected_order_id.required' => 'Vui lòng chọn một đơn hàng để trả.',
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

            if ((string) $row['order_id'] !== (string) $this->selected_order_id) {
                $this->addError('selected_order_id', 'Mỗi phiếu trả hàng chỉ được chọn một đơn hàng.');
                return null;
            }

            if ($quantity > $row['remaining_quantity']) {
                $this->addError("return_quantities.$detailId", 'Số lượng trả không được lớn hơn số lượng còn được trả.');
                return null;
            }

            if ($price < 0) {
                $this->addError("return_prices.$detailId", 'Giá trả không được âm.');
                return null;
            }

            $selectedRows[] = [$row, $quantity, $price];
        }

        if (empty($selectedRows)) {
            $this->addError('rows', 'Vui lòng nhập ít nhất một sản phẩm trả hàng.');
            return null;
        }

        return $selectedRows;
    }

    private function customerDebt()
    {
        if ($this->customer_id === '') {
            return 0;
        }

        return DebtService::currentTotalDebt((int) $this->customer_id);
    }

    private function resetOrderSelection()
    {
        $this->selected_order_id = '';
        $this->rows = [];
        $this->return_quantities = [];
        $this->return_prices = [];
        $this->return_notes = [];
    }

    private function returnedQuantityForDetail(int $orderDetailId)
    {
        return SalesReturnDetail::query()
            ->join('sales_returns', 'sales_return_details.sales_return_id', '=', 'sales_returns.id')
            ->where('sales_return_details.order_detail_id', $orderDetailId)
            ->where('sales_returns.status', '<>', 'canceled')
            ->sum('sales_return_details.quantity');
    }

    private function formatDate($date)
    {
        if (!$date) {
            return '';
        }

        if ($date instanceof \DateTimeInterface) {
            return $date->format('d/m/Y');
        }

        try {
            return \Carbon\Carbon::parse($date)->format('d/m/Y');
        } catch (\Throwable $exception) {
            return (string) $date;
        }
    }
}
