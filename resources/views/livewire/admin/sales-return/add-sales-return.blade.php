<div>
    <form wire:submit="store">
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

            <div class="overflow-x-auto">
                <div class="mb-3 text-sm text-gray-600">
                    Chỉ hiển thị sản phẩm từ đơn hàng đã giao hoặc đã hoàn thành.
                </div>
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-200">
                        <tr>
                            <th scope="col" class="px-2 py-4 text-xs font-medium text-gray-700 uppercase tracking-wider w-12 text-center">STT</th>
                            <th scope="col" class="px-2 py-4 text-xs font-medium text-gray-700 uppercase tracking-wider w-36 text-left">Đơn hàng</th>
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
                        @if (empty($rows))
                            <tr>
                                <td class="px-2 py-2 whitespace-nowrap text-center" colspan="12">Không có sản phẩm còn được trả</td>
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
</div>
