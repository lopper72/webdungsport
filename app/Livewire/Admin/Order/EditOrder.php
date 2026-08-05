<?php

namespace App\Livewire\Admin\Order;

use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\SalesReturnDetail;
use App\Models\Warehouse;
use App\Services\DebtService;
use Livewire\Component;


class EditOrder extends Component
{
    public $payment_method_id = '';
    public $payment_status = '';
    public $customer_id = '';
    public $order_date = '';
    public $order_status = '';
    public $order_note = '';
    public $order_code = '';
    public $order_phone = '';
    public $order_email = '';
    public $order_address = '';
    public $order_state = '';
    public $order_city = '';
    public $order_details = [];
    public $total_quantity = 0;

    public $subtotal_amount = 0;
    public $discount_amount = 0;
    public $discount_percentage = 0;
    public $grandtotal_amount = 0;
    public $shipping_amount = 0;
    public $total_amount = 0;
    public $paid_amount = 0;
    public $payable_amount = 0;
    public $debt_amount = 0;
    public $previous_debt = 0;
    public $total_customer_debt = 0;
    public $grandtotal_notpay = 0;
    public $grandtotal_all = 0;
    public $warehouse_id = 1;
    public $order_id;
    public $order_created_at = '';
    public $order_product_delete = [];
    public $action = '';
    public $can_cancel_order = false;
    public $returned_quantities = [];
    public $total_return_adjusted = 0;
    public $has_return_order = false;




    protected $listeners = ['updateOrderProduct', 'updateOrderProductEdit', 'updateCustomerId'];

    public function mount($id, $customers, $payment_methods)
    {
        $order = Order::findOrFail($id);
        $this->order_id = $id;
        $this->order_created_at = $order->created_at?->toDateTimeString();
        $this->payment_method_id = $order->payment_method_id;
        $this->payment_status = $this->normalizeStatus($order->payment_status);
        $this->customer_id = $order->user_id;
        $this->order_date = date('Y-m-d', strtotime($order->order_date));
        $this->order_status = $order->status;
        $this->order_note = $order->note;
        $this->order_code = $order->code;
        $this->order_phone = $order->shipping_phone;
        $this->order_email = $order->shipping_email;
        $this->order_address = $order->shipping_address;
        $this->order_state = $order->shipping_state;
        $this->order_city = $order->shipping_city;
        $this->subtotal_amount = $order->subtotal_amount;
        $this->discount_amount = $order->discount_amount;
        $this->discount_percentage = $order->discount_percent;
        $this->grandtotal_amount = $order->grandtotal_amount;
        $this->shipping_amount = $order->shipping_amount;
        $this->total_amount = $order->total_amount;
        $this->paid_amount = $order->paid_amount ?? 0;
        $this->order_details = $order->order_detail()
            ->with('product', 'product_size', 'warehouse', 'product_detail')
            ->get()
            ->toArray();
        $this->total_quantity = collect($this->order_details)->sum('quantity');
        $this->can_cancel_order = ! SalesReturnDetail::where('order_id', $this->order_id)->exists();
        $this->returned_quantities = DebtService::returnedQuantitiesByOrder($this->order_id);
        // Có phiếu trả hàng (không bị hủy) hay không — dùng để ẩn/hiện cột trả hàng.
        $this->has_return_order = SalesReturnDetail::where('order_id', $this->order_id)
            ->whereHas('salesReturn', function ($q) {
                $q->where('status', '<>', 'canceled');
            })
            ->exists();

        $this->recalculatePreviousDebt();


    }


    public function updateOrderProduct($order_product, $isMultiple = false)
    {
        if ($isMultiple) {
            foreach ($order_product as $item) {
                if (!$this->canAddProduct($item)) {
                    return;
                }

                $this->order_details[] = $item;
            }
        } else {
            $this->order_details[] = $order_product;
        }

        $this->updateAmount();
        $this->calTotalAmount();
    }

    public function updateOrderProductEdit($order_product, $index)
    {
        if (is_object($order_product) && method_exists($order_product, 'toArray')) {
            $order_product = $order_product->toArray();
        }

        $this->order_details[$index] = $order_product;

        $this->updateAmount();
        $this->calTotalAmount();
    }

    public function updateCustomerId($customer_id)
    {
        $this->customer_id = $customer_id;
        $this->recalculatePreviousDebt();
    }

    public function setCustomerId($customer_id)
    {
        $this->customer_id = $customer_id;
        $this->recalculatePreviousDebt();
    }

    public function setPaymentMethodId($payment_method_id)
    {
        $this->payment_method_id = $payment_method_id;
    }

    public function removeProduct($index)
    {
        if (isset($this->order_details[$index]['id']) && $this->order_details[$index]['id']) {
            $this->order_product_delete[] = $this->order_details[$index];
        }

        unset($this->order_details[$index]);
        $this->order_details = array_values($this->order_details);
        $this->updateAmount();
        $this->calTotalAmount();
    }

    public function createOrder()
    {
        $this->order_status = 'completed';
        $this->confirmBeforeStore('create');
    }

    public function updateOrder()
    {
        $this->order_status = Order::findOrFail($this->order_id)->status;
        $this->confirmBeforeStore('update');
    }

    public function draftOrder()
    {
        $this->order_status = 'draft';
        $this->confirmBeforeStore('draft');
    }

    public function confirmBeforeStore($action)
    {
        $this->validateOrder();

        if (empty($this->order_details)) {
            $this->dispatchSuccessMessage('That bai', 'Vui long chon san pham cho don hang', 'error', $action);
            return;
        }

        $this->dispatch('confirmOrderSave', [
            'action' => $action,
            'paymentStatus' => $this->payment_status,
            'paymentStatusLabel' => $this->paymentStatusLabel(),
        ]);
    }

    public function confirmStoreOrder($action)
    {
        if ($action === 'create') {
            $this->order_status = 'completed';
        } elseif ($action === 'draft') {
            $this->order_status = 'draft';
        } elseif ($action === 'update') {
            $this->order_status = Order::findOrFail($this->order_id)->status;
        }

        $this->storeOrder($action);
    }

    public function storeOrder($action)
    {
        $this->validateOrder();

        if (empty($this->order_details)) {
            $this->dispatchSuccessMessage('That bai', 'Vui long chon san pham cho don hang', 'error', $action);
            return;
        }

        // Chặn việc giảm paid_amount làm công nợ vượt quá giới hạn
        // (bảo vệ khoản cấn trừ công nợ từ trả hàng).
        $debtCheck = DebtService::validateDebtReduction(
            (int) $this->customer_id,
            (int) $this->order_id,
            (float) $this->paid_amount,
            (float) $this->total_amount
        );


        if (!$debtCheck['allowed']) {
            $this->dispatchSuccessMessage('That bai', $debtCheck['message'], 'error', $action);
            return;
        }

        $order = Order::findOrFail($this->order_id);
        $order->update([

            'code' => $this->order_code,
            'user_id' => $this->customer_id,
            'payment_method_id' => $this->payment_method_id,
            'payment_status' => $this->payment_status,
            'order_date' => $this->order_date,
            'status' => $this->order_status,
            'note' => $this->order_note,
            'shipping_phone' => $this->order_phone,
            'shipping_email' => $this->order_email,
            'shipping_address' => $this->order_address,
            'shipping_state' => $this->order_state,
            'shipping_city' => $this->order_city,
            'subtotal_amount' => $this->subtotal_amount,
            'discount_amount' => $this->discount_amount,
            'discount_percent' => $this->discount_percentage,
            'grandtotal_amount' => $this->grandtotal_amount,
            'shipping_amount' => $this->shipping_amount,
            'total_amount' => $this->total_amount,
            'paid_amount' => $this->paid_amount,
        ]);

        foreach ($this->order_product_delete as $order_product) {
            if (isset($order_product['id']) && $order_product['id']) {
                OrderDetail::find($order_product['id'])?->delete();
            }
        }

        foreach ($this->order_details as $order_product) {
            OrderDetail::updateOrCreate([
                'order_id' => $order->id,
                'product_id' => $order_product['product_id'],
                'product_detail_id' => $order_product['product_detail_id'],
                'size_id' => $order_product['size_id'],
                'warehouse_id' => $order_product['warehouse_id'],
            ], [
                'quantity' => $order_product['quantity'],
                'unit_price' => $order_product['unit_price'],
                'total_amount' => $order_product['total_amount'],
                'note' => $order_product['note'],
            ]);
        }

        $this->dispatchSuccessMessage('Thanh cong', '', 'success', $action);
    }

    protected function validateOrder()
    {
        $this->syncPaymentAmounts();
        $this->syncOrderStatusForCounterSale();

        $this->validate([
            'customer_id' => 'required',
            'payment_method_id' => 'required',
            'payment_status' => 'required|in:paid,partial,unpaid',
            'paid_amount' => 'required|numeric|min:0|lte:payable_amount',
            'order_date' => 'required',
            'order_status' => 'required',
            'order_code' => 'required',
        ], [
            'paid_amount.lte' => 'Số tiền đã thanh toán không được lớn hơn số tiền phải trả ('
                . number_format($this->payable_amount, 0, ',', '.') . ' đ).',
        ]);

        // Trạng thái thanh toán phải khớp với số tiền đã thanh toán thực tế.
        $this->validatePaymentStatusAgainstPaidAmount();
        if ($this->getErrorBag()->has('paid_amount')) {
            throw \Illuminate\Validation\ValidationException::withMessages($this->getErrorBag()->toArray());
        }
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



    public function updateAmount()
    {
        $this->subtotal_amount = 0;
        $this->total_quantity  = 0;

        foreach ($this->order_details as $order_product) {
            if (is_string($order_product)) {
                $order_product = json_decode($order_product, true);
            }

            if (is_object($order_product)) {
                $this->subtotal_amount += $order_product->total_amount;
                $this->total_quantity  += $order_product->quantity;
            } elseif (is_array($order_product) && isset($order_product['total_amount'])) {
                $this->subtotal_amount += $order_product['total_amount'];
                $this->total_quantity  += $order_product['quantity'] ?? 0;
            }
        }
    }


    public function calTotalAmountDiscount()
    {
        if ($this->discount_percentage < 1 || $this->discount_percentage > 100) {
            $this->discount_percentage = 0;
            $this->dispatchSuccessMessage('That bai', 'Giam gia % phai nam trong khoang tu 1 den 100.', 'error');
            return;
        }

        $this->calTotalAmount();
    }

    public function calTotalAmount()
    {
        $this->discount_amount = round($this->subtotal_amount * $this->discount_percentage / 100, 3);
        $this->grandtotal_amount = $this->subtotal_amount - $this->discount_amount;
        $this->total_amount = $this->grandtotal_amount + $this->shipping_amount;
        $this->syncPaymentAmounts();
    }

    public function updatedDiscountPercentage()
    {
        $this->calTotalAmountDiscount();
    }

    public function updatedShippingAmount()
    {
        $this->calTotalAmount();
    }

    public function setPaymentStatus($status)
    {
        $this->payment_status = $this->normalizeStatus($status);
        $this->applyPaymentStatusToPaidAmount();
        $this->syncPaymentAmounts();
    }


    public function updatedCustomerId()
    {
        $this->recalculatePreviousDebt();
    }

    public function updatedPaymentStatus()
    {
        $this->payment_status = $this->normalizeStatus($this->payment_status);
        $this->applyPaymentStatusToPaidAmount();
        $this->syncPaymentAmounts();
    }


    public function updatedPaidAmount()
    {
        $this->syncPaymentAmounts();
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


    protected function recalculatePreviousDebt()
    {
        if (!$this->customer_id) {
            $this->grandtotal_notpay = 0;
            $this->previous_debt = 0;
            $this->total_return_adjusted = 0;
            $this->syncPaymentAmounts();
            return;
        }

        $this->total_return_adjusted = DebtService::returnAdjustedByOrder((int) $this->order_id);


        $query = Order::where('user_id', $this->customer_id)
            ->where('id', '<>', $this->order_id)
            ->whereIn('payment_status', ['unpaid', 'partial', 'pending'])
            ->where('status', '<>', 'rejected');

        if ($this->order_created_at) {
            $query->where('created_at', '<', $this->order_created_at);
        }

        // Công nợ trước đó = tổng (Payable Amount - Paid Amount) của các đơn khác.
        // Payable Amount = Order Total - Return Offset (theo đơn).
        $this->grandtotal_notpay = $query->get()
            ->sum(fn ($order) => max(
                max((float) $order->total_amount - DebtService::returnAdjustedByOrder((int) $order->id), 0)
                - (float) ($order->paid_amount ?? 0),
                0
            ));


        $this->previous_debt = $this->grandtotal_notpay;
        $this->syncPaymentAmounts();
    }


    protected function syncPaymentAmounts()
    {
        // Payable Amount = Order Total - Return Offset.
        $this->payable_amount = max((float) $this->total_amount - (float) $this->total_return_adjusted, 0);

        $this->paid_amount = min((float) $this->paid_amount, (float) $this->payable_amount);
        $this->paid_amount = max((float) $this->paid_amount, 0);

        // KHÔNG tự động đổi payment_status. Trạng thái do người dùng chọn,
        // sẽ được kiểm tra khớp với Paid Amount khi lưu (validatePaymentStatusAgainstPaidAmount).

        // Outstanding Debt = Payable Amount - Paid Amount.
        $this->debt_amount = max((float) $this->payable_amount - (float) $this->paid_amount, 0);
        $this->total_customer_debt = (float) $this->previous_debt + (float) $this->debt_amount;
        $this->grandtotal_all = $this->total_customer_debt;
    }






    protected function syncOrderStatusForCounterSale()
    {
        if (in_array($this->order_status, ['pending', 'confirmed', 'shipping', 'delivered'], true)) {
            $this->order_status = 'completed';
        }
    }

    protected function normalizeStatus($status)
    {
        return $status === 'pending' ? 'unpaid' : $status;
    }

    protected function paymentStatusLabel()
    {
        return match ($this->payment_status) {
            'paid' => 'Đã thanh toán',
            'partial' => 'Thanh toán một phần',
            'unpaid' => 'Chưa thanh toán',
            default => 'Chưa chọn',
        };
    }

    protected function canAddProduct($item)
    {
        $warehouseId = $item['warehouse_id'] ?? $this->warehouse_id ?? 1;
        $warehouse = Warehouse::find($warehouseId);

        if (!$warehouse) {
            $this->dispatchSuccessMessage('That bai', 'Không tìm thấy kho hàng.', 'error');
            return false;
        }

        $stock = $warehouse->totalProductAvailable(
            $item['product_id'],
            $item['product_detail_id'],
            $item['size_id']
        );

        $existingTotal = 0;
        foreach ($this->order_details as $existing) {
            if (is_string($existing)) {
                $existing = json_decode($existing, true);
            }

            $existingProductId = is_object($existing) ? ($existing->product_id ?? 0) : ($existing['product_id'] ?? 0);
            $existingDetailId = is_object($existing) ? ($existing->product_detail_id ?? 0) : ($existing['product_detail_id'] ?? 0);
            $existingSizeId = is_object($existing) ? ($existing->size_id ?? 0) : ($existing['size_id'] ?? 0);
            $existingQuantity = is_object($existing) ? ($existing->quantity ?? 0) : ($existing['quantity'] ?? 0);

            if ($existingProductId == $item['product_id']
                && $existingDetailId == $item['product_detail_id']
                && $existingSizeId == $item['size_id']) {
                $existingTotal += $existingQuantity;
            }
        }

        if ($existingTotal + $item['quantity'] <= $stock) {
            return true;
        }

        $this->dispatchSuccessMessage('That bai', "So luong size {$item['size_name']} vuot ton kho.", 'error');

        return false;
    }

    protected function dispatchSuccessMessage($title, $message, $type, $action = null)
    {
        $payload = [
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'timeout' => 3000,
        ];

        if ($action !== null) {
            $payload['action'] = $action;
        }

        $this->dispatch('successOrder', $payload);
    }

    public function render()
    {
        return view('livewire.admin.order.edit-order', [
            'customers' => \App\Models\User::where('username', '!=', 'm8')->get(),
            'payment_methods' => \App\Models\PaymentMethod::all(),
        ]);
    }
}
