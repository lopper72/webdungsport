<div class="rounded-sm border border-stroke bg-white shadow-default dark:border-strokedark dark:bg-boxdark">
    <div class="grid gap-4 border-b border-stroke px-4 py-4 md:grid-cols-3 md:px-6 xl:px-7.5">
        <div class="rounded-sm border border-stroke bg-gray-50 px-4 py-3">
            <p class="text-sm font-medium text-gray-500">Doanh thu bán</p>
            <p class="mt-1 text-2xl font-bold text-gray-800">{{ number_format($salesAmount, 2, '.', ',') }} VND</p>
        </div>
        <div class="rounded-sm border border-stroke bg-gray-50 px-4 py-3">
            <p class="text-sm font-medium text-gray-500">Trả hàng</p>
            <p class="mt-1 text-2xl font-bold text-red-600">-{{ number_format($returnAmount, 2, '.', ',') }} VND</p>
        </div>
        <div class="rounded-sm border border-stroke bg-gray-50 px-4 py-3">
            <p class="text-sm font-medium text-gray-500">Doanh thu thực</p>
            <p class="mt-1 text-2xl font-bold text-gray-800">{{ number_format($totalAmount, 2, '.', ',') }} VND</p>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-200">
                <tr>
                    <th scope="col" class="px-2 py-4 text-sm font-medium text-gray-700 uppercase tracking-wider w-12 text-center">STT</th>
                    <th scope="col" class="px-2 py-4 text-sm font-medium text-gray-700 uppercase tracking-wider w-36 text-left">Ngày đặt hàng</th>
                    <th scope="col" class="px-2 py-4 text-sm font-medium text-gray-700 uppercase tracking-wider w-36 text-left">Khách hàng</th>
                    <th scope="col" class="px-2 py-4 text-sm font-medium text-gray-700 uppercase tracking-wider text-left">Mã đơn hàng</th>
                    <th scope="col" class="px-2 py-4 text-sm font-medium text-gray-700 uppercase tracking-wider w-32 text-right">Thành tiền</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200 text-sm">
                @if ($results->isEmpty())
                    <tr>
                        <td class="px-2 py-2 whitespace-nowrap text-center" colspan="5">Không có dữ liệu</td>
                    </tr>
                @endif

                @foreach ($results as $index => $result)
                    <tr>
                        <td class="px-2 py-2 whitespace-nowrap text-left">{{ $index + 1 }}</td>
                        <td class="px-2 py-2 whitespace-nowrap text-left">{{ date('d/m/Y', strtotime($result->order_date)) }}</td>
                        <td class="px-2 py-2">{{ $result->name }}</td>
                        <td class="px-2 py-2 whitespace-nowrap text-left">{{ $result->code }}</td>
                        <td class="px-2 py-2 whitespace-nowrap text-right">{{ number_format($result->total_amount, 2, '.', ',') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
