<?php

namespace App\Livewire\Admin\Order;

use Livewire\Component;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Warehouse;
use Carbon\Carbon;

class AddOrder extends Component
{
    public $order_details      = [];
    public $order_code         = '';
    public $payment_method_id  = '1';
    public $payment_status     = '';
    public $customer_id        = '';
    public $order_date         = '';
    public $order_status       = '';
    public $order_note         = '';
    public $order_phone        = '';
    public $order_email        = '';
    public $order_address      = '';
    public $order_state        = '';
    public $order_city         = '';
    public $subtotal_amount    = 0;
    public $discount_amount    = 0;
    public $grandtotal_amount  = 0;
    public $shipping_amount    = 0;
    public $total_amount       = 0;
    public $grandtotal_notpay  = 0;
    public $grandtotal_all     = 0;
    public $discount_percentage = 0;
    public $paid_amount        = 0;
    public $debt_amount        = 0;
    public $previous_debt      = 0;
    public $total_customer_debt = 0;
    public $warehouse_id       = 1;

    protected $listeners = ['updateOrderProduct', 'updateOrderProductEdit'];

    // -------------------------------------------------------------------------
    // Lifecycle
    // -------------------------------------------------------------------------

    public function mount()
    {
        $this->order_code = 'ODR' . time() . rand(100, 999) . rand(100, 999);
        $this->order_date = now()->format('Y-m-d');
    }

    // -------------------------------------------------------------------------
    // Updated hooks
    // -------------------------------------------------------------------------

    public function updatedCustomerId()
    {
        $this->recalculatePreviousDebt();
    }

    public function setCustomerId($customer_id)
    {
        $this->customer_id = $customer_id;
        $this->recalculatePreviousDebt();
    }

    public function updatedPaymentStatus()
    {
        $this->payment_status = $this->normalizeStatus($this->payment_status);
        $this->syncPaymentAmounts();
    }

    public function updatedPaidAmount()
    {
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

    // -------------------------------------------------------------------------
    // Product list
    // -------------------------------------------------------------------------

    public function updateOrderProduct($order_product, $isMultiple = false)
    {
        if ($isMultiple) {
            foreach ($order_product as $item) {
                $existingTotal = 0;
                $warehouse = Warehouse::find($item['warehouse_id'] ?? $this->warehouse_id ?? 1);

                if (!$warehouse) {
                    $this->dispatch('successOrder', ['title' => 'Thất bại', 'message' => 'Không tìm thấy kho hàng.', 'type' => 'error', 'timeout' => 3000]);
                    return;
                }

                $stock = $warehouse->totalProductAvailable($item['product_id'], $item['product_detail_id'], $item['size_id']);

                foreach ($this->order_details as $existing) {
                    if (is_string($existing)) $existing = json_decode($existing, true);
                    $eProductId = is_object($existing) ? ($existing->product_id ?? 0)        : ($existing['product_id'] ?? 0);
                    $eDetailId  = is_object($existing) ? ($existing->product_detail_id ?? 0) : ($existing['product_detail_id'] ?? 0);
                    $eSizeId    = is_object($existing) ? ($existing->size_id ?? 0)            : ($existing['size_id'] ?? 0);
                    $eQty       = is_object($existing) ? ($existing->quantity ?? 0)           : ($existing['quantity'] ?? 0);
                    if ($eProductId == $item['product_id'] && $eDetailId == $item['product_detail_id'] && $eSizeId == $item['size_id']) {
                        $existingTotal += $eQty;
                    }
                }

                if ($existingTotal + $item['quantity'] > $stock) {
                    $this->dispatch('successOrder', [
                        'title'   => 'Thất bại',
                        'message' => "Số lượng size {$item['size_name']} vượt tồn kho. Đã có {$existingTotal} trong đơn, còn {$stock} trong kho.",
                        'type'    => 'error',
                        'timeout' => 3000,
                    ]);
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
        $this->order_details[$index] = $order_product;
        $this->updateAmount();
        $this->calTotalAmount();
    }

    public function removeProduct($index)
    {
        unset($this->order_details[$index]);
        $this->order_details = array_values($this->order_details);
        $this->updateAmount();
        $this->calTotalAmount();
    }

    // -------------------------------------------------------------------------
    // Order actions — giữ nguyên logic gốc: bấm nút → store thẳng
    // -------------------------------------------------------------------------

    public function createOrder()
    {
        $this->order_status = 'completed';
        $this->confirmBeforeStore('create');
    }

    public function draftOrder()
    {
        $this->order_status = 'draft';
        $this->confirmBeforeStore('draft');
    }

    public function confirmBeforeStore($action)
    {
        if (empty($this->order_details)) {
            $this->dispatch('successOrder', [
                'title'   => 'Thất bại',
                'message' => 'Vui lòng chọn sản phẩm cho đơn hàng',
                'type'    => 'error',
                'timeout' => 3000,
            ]);
            return;
        }

        $this->validate([
            'customer_id'       => 'required',
            'payment_method_id' => 'required',
            'payment_status'    => 'required|in:paid,partial,unpaid',
            'order_date'        => 'required',
            'order_code'        => 'required',
        ], [
            'customer_id.required'       => 'Trường khách hàng là bắt buộc.',
            'payment_method_id.required' => 'Trường phương thức thanh toán là bắt buộc.',
            'payment_status.required'    => 'Trường trạng thái thanh toán là bắt buộc.',
            'payment_status.in'          => 'Trạng thái thanh toán không hợp lệ.',
            'order_date.required'        => 'Trường ngày đặt hàng là bắt buộc.',
            'order_code.required'        => 'Trường mã đơn hàng là bắt buộc.',
        ]);

        $this->dispatch('confirmOrderSave', [
            'action'             => $action,
            'paymentStatus'      => $this->payment_status,
            'paymentStatusLabel' => $this->paymentStatusLabel(),
        ]);
    }

    public function confirmStoreOrder($action)
    {
        if ($action === 'create') {
            $this->order_status = 'completed';
        } elseif ($action === 'draft') {
            $this->order_status = 'draft';
        }
        $this->storeOrder($action);
    }

    public function storeOrder($action = 'create')
    {
        $this->validateOrder();

        if (empty($this->order_details)) {
            $this->dispatch('successOrder', [
                'title'   => 'Thất bại',
                'message' => 'Vui lòng chọn sản phẩm cho đơn hàng',
                'type'    => 'error',
                'timeout' => 3000,
            ]);
            return;
        }

        $order = Order::create([
            'code'              => $this->order_code,
            'user_id'           => $this->customer_id,
            'payment_method_id' => $this->payment_method_id,
            'payment_status'    => $this->payment_status,
            'order_date'        => $this->order_date,
            'status'            => $this->order_status,
            'note'              => $this->order_note,
            'shipping_phone'    => $this->order_phone,
            'shipping_email'    => $this->order_email,
            'shipping_address'  => $this->order_address,
            'shipping_state'    => $this->order_state,
            'shipping_city'     => $this->order_city,
            'subtotal_amount'   => $this->subtotal_amount,
            'discount_amount'   => $this->discount_amount,
            'grandtotal_amount' => $this->grandtotal_amount,
            'shipping_amount'   => $this->shipping_amount,
            'total_amount'      => $this->total_amount,
            'discount_percent'  => $this->discount_percentage,
            'paid_amount'       => $this->paid_amount,
            'has_debt'          => $this->debt_amount > 0,
        ]);

        foreach ($this->order_details as $order_product) {
            if (is_object($order_product)) {
                $order_product->order_id = $order->id;
                $order_product->save();
            } else {
                $detail = empty($order_product['id'])
                    ? new OrderDetail()
                    : OrderDetail::find($order_product['id']);
                $detail->fill([
                    'order_id'          => $order->id,
                    'product_id'        => $order_product['product_id'],
                    'product_detail_id' => $order_product['product_detail_id'],
                    'size_id'           => $order_product['size_id'],
                    'warehouse_id'      => $order_product['warehouse_id'],
                    'quantity'          => $order_product['quantity'],
                    'unit_price'        => $order_product['unit_price'],
                    'total_amount'      => $order_product['total_amount'],
                    'note'              => $order_product['note'],
                ])->save();
            }
        }

        $this->dispatch('successOrder', [
            'title'   => 'Thành công',
            'message' => '',
            'type'    => 'success',
            'timeout' => 3000,
        ]);

        $this->redirect(route('admin.orders'), navigate: true);
    }

    // -------------------------------------------------------------------------
    // Validation
    // -------------------------------------------------------------------------

    protected function validateOrder()
    {
        $this->syncPaymentAmounts();
        $this->syncOrderStatusForCounterSale();

        $this->validate([
            'customer_id'       => 'required',
            'payment_method_id' => 'required',
            'payment_status'    => 'required|in:paid,partial,unpaid',
            'paid_amount'       => 'required|numeric|min:0|lte:total_amount',
            'order_date'        => 'required',
            'order_status'      => 'required',
            'order_code'        => 'required',
        ], [
            'customer_id.required'       => 'Trường khách hàng là bắt buộc.',
            'payment_method_id.required' => 'Trường phương thức thanh toán là bắt buộc.',
            'payment_status.required'    => 'Trường trạng thái thanh toán là bắt buộc.',
            'payment_status.in'          => 'Trạng thái thanh toán không hợp lệ.',
            'paid_amount.lte'            => 'Số tiền đã thanh toán không được lớn hơn tổng tiền.',
            'order_date.required'        => 'Trường ngày đặt hàng là bắt buộc.',
            'order_status.required'      => 'Trường trạng thái đơn hàng là bắt buộc.',
            'order_code.required'        => 'Trường mã đơn hàng là bắt buộc.',
        ]);
    }

    // -------------------------------------------------------------------------
    // Amount calculations
    // -------------------------------------------------------------------------

    public function updateAmount()
    {
        $this->subtotal_amount = 0;
        foreach ($this->order_details as $order_product) {
            if (is_string($order_product)) {
                $order_product = json_decode($order_product, true);
            }
            if (is_object($order_product)) {
                $this->subtotal_amount += $order_product->total_amount;
            } elseif (is_array($order_product) && isset($order_product['total_amount'])) {
                $this->subtotal_amount += $order_product['total_amount'];
            }
        }
    }

    public function calTotalAmountDiscount()
    {
        if ($this->discount_percentage < 1 || $this->discount_percentage > 100) {
            $this->dispatch('successOrder', [
                'title'   => 'Thất bại',
                'message' => 'Giảm giá % phải nằm trong khoảng từ 1 đến 100.',
                'type'    => 'error',
                'timeout' => 3000,
            ]);
            $this->discount_percentage = 0;
            return;
        }
        $this->calTotalAmount();
    }

    public function calTotalAmount()
    {
        $this->discount_amount   = round($this->subtotal_amount * $this->discount_percentage / 100, 3);
        $this->grandtotal_amount = $this->subtotal_amount - $this->discount_amount;
        $this->total_amount      = $this->grandtotal_amount + $this->shipping_amount;
        $this->syncPaymentAmounts();
    }

    // -------------------------------------------------------------------------
    // Debt & payment — ported từ EditOrder
    // -------------------------------------------------------------------------

    protected function recalculatePreviousDebt()
    {
        if (!$this->customer_id) {
            $this->grandtotal_notpay = 0;
            $this->previous_debt     = 0;
            $this->syncPaymentAmounts();
            return;
        }

        $this->grandtotal_notpay = Order::where('user_id', $this->customer_id)
            ->whereIn('payment_status', ['unpaid', 'partial', 'pending'])
            ->where('status', '<>', 'rejected')
            ->get()
            ->sum(fn ($o) => max($o->total_amount - ($o->paid_amount ?? 0), 0));

        $this->previous_debt = $this->grandtotal_notpay;
        $this->syncPaymentAmounts();
    }

    protected function syncPaymentAmounts()
    {
        $this->payment_status = $this->normalizeStatus($this->payment_status);

        if ($this->payment_status === 'paid') {
            $this->paid_amount = $this->total_amount;
        } elseif ($this->payment_status === 'unpaid') {
            $this->paid_amount = 0;
        } else {
            $this->paid_amount = min((float) $this->paid_amount, (float) $this->total_amount);
            $this->paid_amount = max((float) $this->paid_amount, 0);
        }

        $this->debt_amount          = max((float) $this->total_amount - (float) $this->paid_amount, 0);
        $this->total_customer_debt  = (float) $this->previous_debt + (float) $this->debt_amount;
        $this->grandtotal_all       = $this->total_customer_debt;
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
            'paid'    => 'Đã thanh toán',
            'partial' => 'Thanh toán một phần',
            'unpaid'  => 'Chưa thanh toán',
            default   => 'Chưa chọn',
        };
    }

    // -------------------------------------------------------------------------
    // Render — pure
    // -------------------------------------------------------------------------

    public function render()
    {
        return view('livewire.admin.order.add-order', [
            'customers'       => \App\Models\User::where('username', '!=', 'm8')->get(),
            'payment_methods' => \App\Models\PaymentMethod::all(),
        ]);
    }
}
