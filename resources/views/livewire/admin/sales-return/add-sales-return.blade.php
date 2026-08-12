<div>
    <form wire:submit="confirmBeforeStore">
        <div class="space-y-8">
            <div class="grid gap-x-6 gap-y-4 grid-cols-1 sm:grid-cols-2 md:grid-cols-3">
                <div>
                    <label for="code" class="block text-sm font-medium leading-6 text-gray-900">Mã phiếu <span class="text-red-700">*</span></label>
                    <div class="mt-2">
                        <input wire:model="code" type="text" id="code" class="block w-full rounded-md border-0 px-2 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                    </div>
                    @error('code') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label for="customer_id" class="block text-sm font-medium leading-6 text-gray-900">Khách hàng <span class="text-red-700">*</span></label>
                    <div class="mt-2">
                        @include('livewire.admin.order.partials.customer-search-select', [
                            'customers' => $customers,
                            'selectedId' => $customer_id,
                            'selectId' => 'sales-return-customer',
                        ])
                    </div>
                    @error('customer_id') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label for="return_date" class="block text-sm font-medium leading-6 text-gray-900">Ngày trả <span class="text-red-700">*</span></label>
                    <div class="mt-2">
                        <input wire:model="return_date" type="date" id="return_date" class="block w-full rounded-md border-0 px-2 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                    </div>
                    @error('return_date') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                </div>
            </div>

            <div>
                <label for="note" class="block text-sm font-medium leading-6 text-gray-900">Ghi chú</label>
                <div class="mt-2">
                    <textarea wire:model="note" id="note" rows="2" class="block w-full rounded-md border-0 px-2 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"></textarea>
                </div>
            </div>

            <div class="grid gap-x-6 gap-y-4 grid-cols-1 md:grid-cols-3">
                <div>
                    <label for="sales-return-product" class="block text-sm font-medium leading-6 text-gray-900">Sản phẩm</label>
                    <div class="mt-2" wire:key="sales-return-product-filter-{{ $customer_id ?: 'none' }}-{{ $product_id ?: 'all' }}">
                        @include('livewire.admin.sales-return.partials.product-search-select', [
                            'products' => $products,
                            'selectedId' => $product_id,
                            'selectId' => 'sales-return-product',
                        ])
                    </div>
                    <p class="mt-1 text-xs text-gray-500">Có thể bỏ trống để xem tất cả đơn còn hàng được trả của khách.</p>
                </div>

                <div>
                    <label for="order_code_filter" class="block text-sm font-medium leading-6 text-gray-900">Mã đơn hàng</label>
                    <div class="mt-2">
                        <input
                            wire:model.live.debounce.400ms="order_code_filter"
                            type="text"
                            id="order_code_filter"
                            @disabled($customer_id === '')
                            placeholder="Lọc theo mã đơn"
                            class="block w-full rounded-md border-0 px-2 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 disabled:bg-gray-100 disabled:text-gray-500 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                        >
                    </div>
                </div>

                <div>
                    <div class="mt-8 text-sm text-gray-600">
                        Mỗi phiếu trả hàng chỉ chọn một đơn. Sau khi chọn khách hàng, danh sách đơn sẽ hiện mới nhất trước, 5 đơn mỗi trang.
                    </div>
                    @error('selected_order_id') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="overflow-x-auto" wire:key="sales-return-orders-{{ $customer_id ?: 'none' }}-{{ $product_id ?: 'all' }}-{{ $order_code_filter ?: 'all' }}-{{ $orders->currentPage() }}">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-200">
                        <tr>
                            <th scope="col" class="px-2 py-3 text-xs font-medium text-gray-700 uppercase tracking-wider w-12 text-center">Chọn</th>
                            <th scope="col" class="px-2 py-3 text-xs font-medium text-gray-700 uppercase tracking-wider w-40 text-left">Đơn hàng</th>
                            <th scope="col" class="px-2 py-3 text-xs font-medium text-gray-700 uppercase tracking-wider w-32 text-left">Ngày đơn</th>
                            <th scope="col" class="px-2 py-3 text-xs font-medium text-gray-700 uppercase tracking-wider w-40 text-left">Trạng thái thanh toán</th>
                            <th scope="col" class="px-2 py-3 text-xs font-medium text-gray-700 uppercase tracking-wider text-left">Sản phẩm có thể trả</th>
                            <th scope="col" class="px-2 py-3 text-xs font-medium text-gray-700 uppercase tracking-wider w-28 text-right">Còn trả</th>
                            <th scope="col" class="px-2 py-3 text-xs font-medium text-gray-700 uppercase tracking-wider w-36 text-right">Tổng đơn</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200 text-sm">
                        @if ($customer_id === '')
                            <tr>
                                <td class="px-2 py-3 text-center text-gray-500" colspan="7">Vui lòng chọn khách hàng</td>
                            </tr>
                        @elseif ($orders->isEmpty())
                            <tr>
                                <td class="px-2 py-3 text-center text-gray-500" colspan="7">Không có đơn còn hàng được trả</td>
                            </tr>
                        @endif

                        @foreach ($orders as $order)
                            <tr wire:key="sales-return-order-row-{{ $customer_id ?: 'none' }}-{{ $order['id'] }}" class="{{ (string) $selected_order_id === (string) $order['id'] ? 'bg-blue-50' : '' }}">
                                <td class="px-2 py-2 whitespace-nowrap text-center">
                                    <input
                                        type="radio"
                                        name="selected_order_{{ $customer_id ?: 'none' }}"
                                        value="{{ $order['id'] }}"
                                        wire:model.live="selected_order_id"
                                        class="h-4 w-4 border-gray-300 text-indigo-600 focus:ring-indigo-600"
                                    >
                                </td>
                                <td class="px-2 py-2 whitespace-nowrap text-left font-medium text-gray-900">
                                    <div class="flex items-center gap-2">
                                        <span>{{ $order['code'] }}</span>
                                        <button
                                            type="button"
                                            class="inline-flex h-7 w-7 items-center justify-center rounded-md text-indigo-600 hover:bg-indigo-50 hover:text-indigo-900"
                                            onclick="copySalesReturnOrderCode('{{ $order['code'] }}')"
                                            title="Copy mã đơn"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                                <path d="M6 11C6 8.17 6 6.76 6.88 5.88C7.76 5 9.17 5 12 5H15C17.83 5 19.24 5 20.12 5.88C21 6.76 21 8.17 21 11V16C21 18.83 21 20.24 20.12 21.12C19.24 22 17.83 22 15 22H12C9.17 22 7.76 22 6.88 21.12C6 20.24 6 18.83 6 16V11Z" stroke="currentColor" stroke-width="1.5"/>
                                                <path d="M6 19C4.34 19 3 17.66 3 16V10C3 6.23 3 4.34 4.17 3.17C5.34 2 7.23 2 11 2H15C16.66 2 18 3.34 18 5" stroke="currentColor" stroke-width="1.5"/>
                                            </svg>
                                            <span class="sr-only">Copy mã đơn</span>
                                        </button>
                                    </div>
                                </td>
                                <td class="px-2 py-2 whitespace-nowrap text-left">{{ $order['order_date'] }}</td>
                                <td class="px-2 py-2 whitespace-nowrap text-left">
                                    @switch($order['payment_status'])
                                        @case('paid')
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Đã thanh toán</span>
                                            @break
                                        @case('partial')
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Thanh toán một phần</span>
                                            @break
                                        @case('unpaid')
                                        @case('pending')
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Chưa thanh toán</span>
                                            @break
                                        @default
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-700">{{ $order['payment_status'] ?: '-' }}</span>
                                    @endswitch
                                </td>
                                <td class="px-2 py-2 text-left">{{ implode(', ', $order['matched_products']) }}</td>
                                <td class="px-2 py-2 whitespace-nowrap text-right">{{ number_format($order['remaining_quantity'], 0, ',', '.') }}</td>
                                <td class="px-2 py-2 whitespace-nowrap text-right">{{ number_format($order['total_amount'], 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                @if ($orders->hasPages())
                    <div class="mt-4">
                        {{ $orders->links('livewire.custom-pagination') }}
                    </div>
                @endif
            </div>

            <div class="overflow-x-auto">
                <div class="mb-3 text-sm text-gray-600">
                    Sản phẩm còn được trả trong đơn đã chọn.
                </div>
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-200">
                        <tr>
                            <th scope="col" class="px-2 py-4 text-xs font-medium text-gray-700 uppercase tracking-wider w-12 text-center">STT</th>
                            <th scope="col" class="px-2 py-4 text-xs font-medium text-gray-700 uppercase tracking-wider w-36 text-left">Đơn hàng</th>
                            <th scope="col" class="px-2 py-4 text-xs font-medium text-gray-700 uppercase tracking-wider w-32 text-left">Mã hàng</th>
                            <th scope="col" class="px-2 py-4 text-xs font-medium text-gray-700 uppercase tracking-wider text-left">Sản phẩm</th>
                            <th scope="col" class="px-2 py-4 text-xs font-medium text-gray-700 uppercase tracking-wider w-36 text-left">Mẫu</th>
                            <th scope="col" class="px-2 py-4 text-xs font-medium text-gray-700 uppercase tracking-wider w-20 text-left">Size</th>
                            <th scope="col" class="px-2 py-4 text-xs font-medium text-gray-700 uppercase tracking-wider w-32 text-left">Kho</th>
                            <th scope="col" class="px-2 py-4 text-xs font-medium text-gray-700 uppercase tracking-wider w-24 text-right">Đã mua</th>
                            <th scope="col" class="px-2 py-4 text-xs font-medium text-gray-700 uppercase tracking-wider w-24 text-right">Đã trả</th>
                            <th scope="col" class="px-2 py-4 text-xs font-medium text-gray-700 uppercase tracking-wider w-28 text-right">Còn trả</th>
                            <th scope="col" class="px-2 py-4 text-xs font-medium text-gray-700 uppercase tracking-wider w-28 text-right">SL trả</th>
                            <th scope="col" class="px-2 py-4 text-xs font-medium text-gray-700 uppercase tracking-wider w-32 text-right">Giá trả</th>
                            <th scope="col" class="px-2 py-4 text-xs font-medium text-gray-700 uppercase tracking-wider w-32 text-right">Thành tiền</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200 text-sm">
                        @if ($selected_order_id === '')
                            <tr>
                                <td class="px-2 py-2 whitespace-nowrap text-center text-gray-500" colspan="13">Chọn một đơn hàng để xem sản phẩm có thể trả</td>
                            </tr>
                        @elseif (empty($rows))
                            <tr>
                                <td class="px-2 py-2 whitespace-nowrap text-center text-gray-500" colspan="13">Không có sản phẩm còn được trả trong đơn đã chọn</td>
                            </tr>
                        @endif

                        @foreach ($rows as $index => $row)
                            @php
                                $detailId = $row['order_detail_id'];
                                $quantity = (int) ($return_quantities[$detailId] ?? 0);
                                $price = (float) ($return_prices[$detailId] ?? 0);
                            @endphp
                            <tr>
                                <td class="px-2 py-2 whitespace-nowrap text-center">{{ $index + 1 }}</td>
                                <td class="px-2 py-2 whitespace-nowrap text-left">{{ $row['order_code'] }}</td>
                                <td class="px-2 py-2 whitespace-nowrap text-left">{{ $row['product_code'] }}</td>
                                <td class="px-2 py-2 text-left">{{ $row['product_name'] }}</td>
                                <td class="px-2 py-2 whitespace-nowrap text-left">{{ $row['product_detail_name'] }}</td>
                                <td class="px-2 py-2 whitespace-nowrap text-left">{{ $row['size_name'] }}</td>
                                <td class="px-2 py-2 whitespace-nowrap text-left">{{ $row['warehouse_name'] }}</td>
                                <td class="px-2 py-2 whitespace-nowrap text-right">{{ $row['sold_quantity'] }}</td>
                                <td class="px-2 py-2 whitespace-nowrap text-right">{{ $row['returned_quantity'] }}</td>
                                <td class="px-2 py-2 whitespace-nowrap text-right">{{ $row['remaining_quantity'] }}</td>
                                <td class="px-2 py-2 whitespace-nowrap text-right">
                                    <input wire:model.live="return_quantities.{{ $detailId }}" type="number" min="0" max="{{ $row['remaining_quantity'] }}" class="block w-24 rounded-md border-0 px-2 py-1.5 text-right text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                                    @error("return_quantities.$detailId") <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                                </td>
                                <td class="px-2 py-2 whitespace-nowrap text-right">
                                    <input wire:model.live="return_prices.{{ $detailId }}" type="number" min="0" step="0.01" class="block w-28 rounded-md border-0 px-2 py-1.5 text-right text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                                    @error("return_prices.$detailId") <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                                </td>
                                <td class="px-2 py-2 whitespace-nowrap text-right">{{ number_format($quantity * $price, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                @error('rows') <div class="mt-2 text-sm text-red-600">{{ $message }}</div> @enderror
            </div>

            <div class="flex justify-end">
                <div class="w-full max-w-md space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Công nợ hiện tại</span>
                        <span class="font-semibold">{{ number_format($current_debt, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Tổng tiền trả</span>
                        <span class="font-semibold">{{ number_format($totalAmount, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Cấn trừ công nợ</span>
                        <span class="font-semibold text-blue-600">{{ number_format($debtAdjustmentAmount, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between border-t pt-2">
                        <span class="text-gray-900 font-semibold">Cần hoàn tiền</span>
                        <span class="text-xl font-bold text-red-600">{{ number_format($refundAmount, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-6 flex sm:flex-row items-center justify-end gap-x-6">
            <a href="{{ route('admin.sales-returns') }}" class="text-sm font-semibold leading-6 text-gray-900">Hủy</a>
            <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-500 border border-transparent rounded-md font-semibold text-xs text-white tracking-widest hover:bg-blue-600 active:bg-blue-700 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">Trả hàng</button>
        </div>
    </form>

    @script
    <script>
        window.copySalesReturnOrderCode = function (text) {
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(text)
                return
            }

            const el = document.createElement('textarea')
            el.value = text
            el.style.position = 'fixed'
            el.style.opacity = '0'
            document.body.appendChild(el)
            el.select()
            document.execCommand('copy')
            document.body.removeChild(el)
        }

        window.addEventListener('confirmSalesReturnSave', event => {
            const data = event.detail[0]
            const message = 'Bạn có chắc muốn trả hàng/hoàn tiền?\n\n'
                + 'Tổng tiền trả: ' + data.totalAmount + ' đ\n'
                + 'Cấn trừ công nợ: ' + data.debtAdjustmentAmount + ' đ\n'
                + 'Cần hoàn tiền: ' + data.refundAmount + ' đ'
            if (window.confirm(message)) {
                $wire.confirmStore()
            }
        })
    </script>
    @endscript
</div>
