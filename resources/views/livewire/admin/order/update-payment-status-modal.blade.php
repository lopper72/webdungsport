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

    <form class="p-4 md:p-5" wire:submit.prevent="updateStatus">
        <div class="grid gap-4 mb-4 grid-cols-2">
            <div class="col-span-2">
                <label for="payment_status" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Trạng thái thanh toán</label>
                <select wire:model.live="payment_status" id="payment_status" class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500">
                    <option value="paid">Đã thanh toán</option>
                    <option value="partial">Thanh toán một phần</option>
                    <option value="unpaid">Chưa thanh toán</option>
                </select>
            </div>
            <div class="col-span-2">
                <label for="paid_amount" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Số tiền đã thanh toán</label>
                @if($payment_status === 'partial')
                    <input wire:model.live="paid_amount" type="number" min="0" max="{{ $total_amount }}" id="paid_amount" class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 text-right">
                    @error('paid_amount') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                @else
                    <div class="p-2.5 w-full text-sm text-gray-900 bg-gray-100 rounded-lg border border-gray-300 text-right">{{ number_format($paid_amount) }}</div>
                @endif
            </div>
        </div>

        <div class="grid gap-4 mb-4 grid-cols-2">
            <div class="col-span-2">
                <label for="note" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Ghi chú</label>

                <textarea wire:model="note" id="note" rows="4" class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500" placeholder=""></textarea>
            </div>
        </div>

        <button type="submit" class="text-white inline-flex items-center bg-sky-700 hover:bg-sky-800 focus:ring-4 focus:outline-none focus:ring-sky-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center">
            Cập nhật trạng thái thanh toán
        </button>
    </form>
</div>

