<?php

namespace App\Services;

use App\Models\Order;
use App\Models\SalesReturn;
use App\Models\SalesReturnDetail;


/**
 * DebtService
 *
 * Xử lý logic công nợ của khách hàng, đặc biệt là mối quan hệ giữa
 * tiền trả hàng (đã cấn trừ công nợ) và việc thay đổi trạng thái thanh toán.
 *
 * Nguyên tắc:
 * - Khi trả hàng, tiền trả hàng được cấn trừ vào công nợ của khách hàng
 *   (tăng paid_amount của các đơn nợ theo thứ tự cũ nhất trước).
 * - Tổng công nợ của khách hàng KHÔNG được vượt quá:
 *       Tổng tiền các đơn (không rejected) - Tổng tiền trả hàng đã cấn trừ công nợ
 * - Nếu người dùng giảm paid_amount (làm tăng công nợ) vượt quá giới hạn này,
 *   hệ thống phải cảnh báo và chặn để không làm mất khoản cấn trừ từ trả hàng.
 */
class DebtService
{
    /**
     * Tổng tiền trả hàng đã cấn trừ công nợ của khách hàng.
     *
     * Return Offset (theo đơn) = tổng total_amount của các phiếu trả hàng
     * (không canceled) thuộc đơn hàng đó. Tổng cấn trừ của khách hàng là
     * tổng Return Offset của tất cả các đơn hàng của khách hàng đó.
     *
     * Lưu ý: Không dùng debt_adjustment_amount vì giá trị này có thể bị giới
     * hạn theo công nợ hiện tại khi tạo phiếu trả, gây lệch với Return Offset
     * thực tế được dùng để tính Payable Amount (xem returnAdjustedByOrder()).
     */
    public static function totalReturnAdjusted(int $userId): float
    {
        return (float) SalesReturnDetail::whereHas('salesReturn', function ($q) use ($userId) {
            $q->where('user_id', $userId)
                ->where('status', '<>', 'canceled');
        })->sum('total_amount');
    }


    /**
     * Số tiền phải trả sau khi trừ tiền trả hàng đã cấn trừ công nợ.
     *
     * Payable Amount = Order Total - Return Offset
     *
     * @param float $totalAmount Tổng tiền của đơn hàng
     * @param int   $userId      ID khách hàng
     */
    public static function payableAmount(float $totalAmount, int $userId): float
    {
        return max($totalAmount - self::totalReturnAdjusted($userId), 0);
    }

    /**
     * Tiền trả hàng đã cấn trừ công nợ của riêng một đơn hàng.
     *
     * Return Offset (theo đơn) = tổng total_amount của các phiếu trả hàng
     * (không canceled) thuộc đơn hàng đó.
     *
     * @param int $orderId ID đơn hàng
     */
    public static function returnAdjustedByOrder(int $orderId): float
    {
        return (float) SalesReturnDetail::where('order_id', $orderId)
            ->whereHas('salesReturn', function ($q) {
                $q->where('status', '<>', 'canceled');
            })
            ->sum('total_amount');
    }


    /**
     * Trạng thái thanh toán chỉ phụ thuộc vào số tiền đã thanh toán thực tế.
     *
     * - Paid Amount == 0                       => UNPAID
     * - 0 < Paid Amount < Payable Amount       => PARTIALLY PAID
     * - Paid Amount >= Payable Amount          => PAID
     *
     * @param float $paidAmount    Số tiền đã thanh toán thực tế
     * @param float $payableAmount Số tiền phải trả (Order Total - Return Offset)
     * @return string 'unpaid' | 'partial' | 'paid'
     */
    public static function paymentStatus(float $paidAmount, float $payableAmount): string
    {
        if ($paidAmount <= 0) {
            return 'unpaid';
        }

        if ($paidAmount >= $payableAmount) {
            return 'paid';
        }

        return 'partial';
    }



    /**
     * Tổng tiền các đơn hàng (không rejected) của khách hàng.
     */
    public static function totalOrderAmount(int $userId): float
    {
        return (float) Order::where('user_id', $userId)
            ->where('status', '<>', 'rejected')
            ->sum('total_amount');
    }

    /**
     * Tổng paid_amount của các đơn hàng (không rejected) của khách hàng.
     */
    public static function totalPaidAmount(int $userId): float
    {
        return (float) Order::where('user_id', $userId)
            ->where('status', '<>', 'rejected')
            ->sum('paid_amount');
    }

    /**
     * Công nợ tối đa mà khách hàng được phép nợ.
     * = Tổng tiền các đơn - Tổng tiền trả hàng đã cấn trừ công nợ.
     */
    public static function maxAllowedDebt(int $userId): float
    {
        return max(self::totalOrderAmount($userId) - self::totalReturnAdjusted($userId), 0);
    }

    /**
     * Công nợ hiện tại của khách hàng.
     * = tổng max(Payable Amount - Paid Amount, 0) của các đơn không rejected.
     * Payable Amount = Order Total - Return Offset (theo đơn).
     */
    public static function currentTotalDebt(int $userId): float
    {
        return (float) Order::where('user_id', $userId)
            ->where('status', '<>', 'rejected')
            ->get()
            ->sum(function ($order) {
                $payable = max((float) $order->total_amount - self::returnAdjustedByOrder((int) $order->id), 0);
                return max($payable - (float) ($order->paid_amount ?? 0), 0);
            });
    }


    /**
     * Số lượng đã trả cho từng order_detail của một đơn hàng.
     * Trả về map: order_detail_id => tổng quantity đã trả (chỉ tính phiếu trả không canceled).
     *
     * @param int $orderId ID đơn hàng
     * @return array<int, int>
     */
    public static function returnedQuantitiesByOrder(int $orderId): array
    {
        return SalesReturnDetail::where('order_id', $orderId)
            ->whereHas('salesReturn', function ($q) {
                $q->where('status', '<>', 'canceled');
            })
            ->get()
            ->groupBy('order_detail_id')
            ->map(fn ($items) => (int) $items->sum('quantity'))
            ->toArray();
    }


    /**
     * Kiểm tra xem một phiếu trả hàng có thể bị hủy hay không.
     *
     * Phiếu trả hàng là chứng từ tài chính, không được xóa vật lý mà chỉ
     * được hủy (soft delete) bằng cách đổi status thành 'canceled'.
     *
     * Điều kiện hủy:
     * - Phiếu chưa bị hủy trước đó.
     * - Phiếu chưa được tham chiếu bởi một chứng từ/giao dịch tài chính khác.
     *
     * Lưu ý: Mọi ảnh hưởng của phiếu trả (Return Offset, số lượng đã trả,
     * Payable Amount, Outstanding Debt, Payment Status) đều được tính động
     * từ các phiếu trả có status <> 'canceled'. Vì vậy khi hủy phiếu, các
     * giá trị này tự động được hoàn tác mà không cần thao tác thủ công.
     *
     * @param \App\Models\SalesReturn $salesReturn
     * @return array{allowed: bool, message: string}
     */
    public static function canCancelSalesReturn($salesReturn): array
    {
        if ($salesReturn->status === 'canceled') {
            return [
                'allowed' => false,
                'message' => 'Phiếu trả hàng này đã bị hủy trước đó, không thể hủy lại.',
            ];
        }

        // Kiểm tra xem phiếu trả có bị tham chiếu bởi chứng từ tài chính khác không.
        // Trong hệ thống hiện tại, phiếu trả chỉ được tham chiếu bởi chính các
        // dòng chi tiết của nó (sales_return_details). Nếu sau này có thêm chứng từ
        // khác tham chiếu (ví dụ: phiếu hoàn tiền, phiếu thu), cần bổ sung kiểm tra tại đây.
        $referenced = $salesReturn->details()->exists();

        if (!$referenced) {
            return [
                'allowed' => false,
                'message' => 'Phiếu trả hàng không có dòng chi tiết, không thể hủy.',
            ];
        }

        return [
            'allowed' => true,
            'message' => '',
        ];
    }



    /**
     * LƯU Ý VỀ HOÀN TÁC KHI HỦY PHIẾU TRẢ HÀNG:
     *
     * Khi tạo phiếu trả, hệ thống KHÔNG tăng paid_amount của các đơn hàng.
     * Tiền trả hàng được ghi nhận qua debt_adjustment_amount (Return Offset)
     * và được cấn trừ vào công nợ bằng cách giảm Payable Amount
     * (Payable Amount = Order Total - Return Offset).
     *
     * Vì Return Offset, số lượng đã trả, Payable Amount, Outstanding Debt và
     * Payment Status đều được TÍNH ĐỘNG từ các phiếu trả có status <> 'canceled'
     * (xem returnAdjustedByOrder(), returnedQuantitiesByOrder(), paymentStatus()),
     * nên khi hủy một phiếu trả (đổi status thành 'canceled'), mọi ảnh hưởng
     * này tự động được hoàn tác mà KHÔNG cần thao tác thủ công.
     *
     * Do đó KHÔNG có phương thức reverseDebtAdjustment() ở đây — nếu gọi nó
     * sẽ làm giảm paid_amount của các đơn một cách sai lệch.
     */

    /**
     * Kiểm tra xem việc thay đổi paid_amount của một đơn hàng có làm

     * tổng công nợ vượt quá giới hạn cho phép hay không.

     *
     * @param int   $userId        ID khách hàng
     * @param int   $orderId       ID đơn hàng đang được thay đổi
     * @param float $newPaidAmount paid_amount mới của đơn hàng
     * @param float|null $newTotalAmount total_amount mới của đơn hàng (nếu có thay đổi)
     *
     * @return array{allowed: bool, message: string, currentDebt: float, maxDebt: float}
     */
    public static function validateDebtReduction(int $userId, int $orderId, float $newPaidAmount, ?float $newTotalAmount = null): array
    {
        $orders = Order::where('user_id', $userId)
            ->where('status', '<>', 'rejected')
            ->get();

        // Tính tổng tiền các đơn, có tính đến total_amount mới của đơn đang sửa.
        $totalOrderAmount = 0.0;
        foreach ($orders as $order) {
            $total = $order->id === $orderId && $newTotalAmount !== null
                ? (float) $newTotalAmount
                : (float) $order->total_amount;
            $totalOrderAmount += $total;
        }

        $maxDebt = max($totalOrderAmount - self::totalReturnAdjusted($userId), 0);

        // Tính công nợ mới nếu áp dụng paid_amount mới cho đơn hàng này.
        // Công nợ mỗi đơn = max(Payable Amount - Paid Amount, 0).
        // Payable Amount = Order Total - Return Offset (theo đơn).
        $newTotalDebt = 0.0;
        foreach ($orders as $order) {
            $paid = $order->id === $orderId
                ? $newPaidAmount
                : (float) ($order->paid_amount ?? 0);

            $total = $order->id === $orderId && $newTotalAmount !== null
                ? (float) $newTotalAmount
                : (float) $order->total_amount;

            $payable = max($total - self::returnAdjustedByOrder((int) $order->id), 0);

            $newTotalDebt += max($payable - $paid, 0);
        }


        $allowed = $newTotalDebt <= $maxDebt + 0.001; // dung sai nhỏ do làm tròn

        $message = '';
        if (!$allowed) {
            $message = 'Không thể giảm số tiền đã thanh toán vì sẽ làm công nợ của khách hàng vượt quá giới hạn cho phép. '
                . 'Công nợ tối đa là ' . number_format($maxDebt, 0, ',', '.') . ' đ '
                . '(đã trừ ' . number_format(self::totalReturnAdjusted($userId), 0, ',', '.') . ' đ tiền trả hàng đã cấn trừ công nợ).';
        }

        return [
            'allowed' => $allowed,
            'message' => $message,
            'currentDebt' => $newTotalDebt,
            'maxDebt' => $maxDebt,
        ];
    }


}
