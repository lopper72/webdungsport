<div class="rounded-sm border border-stroke bg-white shadow-default dark:border-strokedark dark:bg-boxdark">
    <div class="grid gap-4 border-b border-stroke px-4 py-4 md:grid-cols-3 md:px-6 xl:px-7.5">
        <div class="rounded-sm border border-stroke bg-gray-50 px-4 py-3">
            <p class="text-sm font-medium text-gray-500">Tổng doanh thu</p>
            <p class="mt-1 text-2xl font-bold text-gray-800">{{ number_format($totalAmount, 2, '.', ',') }} VND</p>
        </div>
        <div class="rounded-sm border border-stroke bg-gray-50 px-4 py-3">
            <p class="text-sm font-medium text-gray-500">Tổng số lượng bán</p>
            <p class="mt-1 text-2xl font-bold text-gray-800">{{ number_format($totalQuantity, 0, ',', '.') }}</p>
        </div>
        <div class="rounded-sm border border-stroke bg-gray-50 px-4 py-3">
            <p class="text-sm font-medium text-gray-500">Tổng đơn hàng</p>
            <p class="mt-1 text-2xl font-bold text-gray-800">{{ number_format($totalOrders, 0, ',', '.') }}</p>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-200">
                <tr>
                    <th scope="col" class="px-2 py-4 text-sm font-medium text-gray-700 uppercase tracking-wider w-12 text-center">STT</th>
                    <th scope="col" class="px-2 py-4 text-sm font-medium text-gray-700 uppercase tracking-wider w-36 text-left">Mã hãng</th>
                    <th scope="col" class="px-2 py-4 text-sm font-medium text-gray-700 uppercase tracking-wider text-left">Nhãn hàng</th>
                    <th scope="col" class="px-2 py-4 text-sm font-medium text-gray-700 uppercase tracking-wider w-36 text-right">Số đơn</th>
                    <th scope="col" class="px-2 py-4 text-sm font-medium text-gray-700 uppercase tracking-wider w-36 text-right">Số lượng bán</th>
                    <th scope="col" class="px-2 py-4 text-sm font-medium text-gray-700 uppercase tracking-wider w-40 text-right">Doanh thu</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200 text-sm">
                @if ($results->isEmpty())
                    <tr>
                        <td class="px-2 py-2 whitespace-nowrap text-center" colspan="6">Không có dữ liệu</td>
                    </tr>
                @endif

                @foreach ($results as $index => $result)
                    <tr>
                        <td class="px-2 py-2 whitespace-nowrap text-center">{{ $index + 1 }}</td>
                        <td class="px-2 py-2 whitespace-nowrap text-left">{{ $result->brand_code ?? '-' }}</td>
                        <td class="px-2 py-2 text-left">{{ $result->brand_name }}</td>
                        <td class="px-2 py-2 whitespace-nowrap text-right">{{ number_format($result->total_orders, 0, ',', '.') }}</td>
                        <td class="px-2 py-2 whitespace-nowrap text-right">{{ number_format($result->total_quantity, 0, ',', '.') }}</td>
                        <td class="px-2 py-2 whitespace-nowrap text-right">{{ number_format($result->total_amount, 2, '.', ',') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
