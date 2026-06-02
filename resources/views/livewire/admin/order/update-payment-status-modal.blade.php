<div>
    <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white uppercase">Trạng thái thanh toán đơn hàng</h3>
        <button type="button" wire:click="$dispatch('closeModal')" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white">
            <span class="sr-only">Close modal</span>
            <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
            </svg>
        </button>
    </div>

    <form class="p-4 md:p-5" onsubmit="return false"
        x-data='{
            status: @json($payment_status),
            paidAmount: @json((float) $paid_amount),
            totalAmount: @json((float) $total_amount),
            previousDebt: @json((float) $previous_debt),
            originalStatus: @json($original_payment_status),
            originalPaidAmount: @json((float) $original_paid_amount),
            format(value) { return new Intl.NumberFormat("en-US").format(Number(value) || 0) },
            debtAmount() { return Math.max(this.totalAmount - this.paidAmount, 0) },
            totalCustomerDebt() { return this.previousDebt + this.debtAmount() },
            syncStatus() {
                if (this.status === "paid") this.paidAmount = this.totalAmount
                else if (this.status === "unpaid") this.paidAmount = 0
                else if (this.originalStatus === "partial") this.paidAmount = this.originalPaidAmount
                else if (this.paidAmount <= 0 || this.paidAmount >= this.totalAmount) this.paidAmount = 0
            },
            syncPaidAmount() {
                this.paidAmount = Math.min(Math.max(Number(this.paidAmount) || 0, 0), this.totalAmount)
            }
        }'>
        <div class="grid gap-4 mb-4 grid-cols-2">
            <div class="col-span-2">
                <label for="payment_status" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Trạng thái thanh toán</label>
                <select x-model="status" x-on:change="syncStatus" wire:model.change="payment_status" id="payment_status" class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500">
                    <option value="paid">Đã thanh toán</option>
                    <option value="partial">Thanh toán một phần</option>
                    <option value="unpaid">Chưa thanh toán</option>
                </select>
            </div>
            <div class="col-span-2">
                <table class="w-full text-sm">
                    <tr>
                        <td class="py-2 font-bold uppercase tracking-wider text-gray-700">Tổng tiền hàng</td>
                        <td class="py-2 text-right font-bold text-gray-900">{{ number_format($subtotal_amount) }}</td>
                    </tr>
                    <tr>
                        <td class="py-2 font-bold uppercase tracking-wider text-gray-700">Giảm giá</td>
                        <td class="py-2 text-right">
                            <div class="flex items-center justify-end gap-3">
                                <input type="text" value="{{ number_format((float) $discount_percent, 2) }}" readonly class="w-24 rounded-md border border-gray-300 bg-gray-100 py-1.5 text-center text-gray-900">
                                <span>%</span>
                                <span class="w-24 font-bold text-gray-900">{{ number_format($discount_amount) }}</span>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td class="py-2 font-bold uppercase tracking-wider text-gray-700">Thành tiền</td>
                        <td class="py-2 text-right font-bold text-gray-900">{{ number_format($total_amount) }}</td>
                    </tr>
                    <tr>
                        <td class="py-2 font-bold uppercase tracking-wider text-gray-700">Đã thanh toán</td>
                        <td class="py-2 text-right">
                            <input x-show="status === 'partial'" x-model.number="paidAmount" x-on:input.debounce.300ms="syncPaidAmount" x-on:change="syncPaidAmount" wire:model.live="paid_amount" type="number" min="0" max="{{ $total_amount }}" id="paid_amount" class="w-36 rounded-md border border-gray-300 bg-gray-50 py-1.5 text-right text-gray-900 focus:border-blue-500 focus:ring-blue-500">
                            <span x-show="status !== 'partial'" x-text="format(paidAmount)" class="font-bold text-gray-900"></span>
                            @error('paid_amount') <div class="mt-1 text-sm text-red-600 normal-case">{{ $message }}</div> @enderror
                        </td>
                    </tr>
                    <tr x-show="status === 'paid'">
                        <td class="py-2 font-bold uppercase tracking-wider text-gray-700">Còn lại</td>
                        <td class="py-2 text-right font-bold text-gray-900">0</td>
                    </tr>
                    <tr x-show="status === 'partial' || status === 'unpaid'">
                        <td class="py-2 font-bold uppercase tracking-wider text-gray-700">Nợ phát sinh</td>
                        <td class="py-2 text-right font-bold text-gray-900" x-text="format(debtAmount())"></td>
                    </tr>
                    <tr x-show="status === 'partial' || status === 'unpaid'">
                        <td class="py-2 font-bold uppercase tracking-wider text-gray-700">Tổng nợ khách hàng</td>
                        <td class="py-2 text-right font-bold text-gray-900" x-text="format(totalCustomerDebt())"></td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="grid gap-4 mb-4 grid-cols-2">
            <div class="col-span-2">
                <label for="note" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Ghi chu</label>
                <textarea wire:model="note" id="note" rows="4" class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500" placeholder=""></textarea>
            </div>
        </div>

        <button type="button" wire:click="updateStatus" class="text-white inline-flex items-center bg-sky-700 hover:bg-sky-800 focus:ring-4 focus:outline-none focus:ring-sky-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center">
            Cập nhật trạng thái thanh toán
        </button>
    </form>
</div>

