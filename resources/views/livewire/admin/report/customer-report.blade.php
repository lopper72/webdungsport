<div class="rounded-sm border border-stroke bg-white shadow-default dark:border-strokedark dark:bg-boxdark">
    <div class="px-4 py-4 sm:px-6">
        @if (!empty($startdate) || !empty($endate))
            <div class="text-sm text-gray-700 mb-2">
                <span class="font-semibold">Khoảng thời gian:</span>
                @if (!empty($startdate))
                    Từ {{ date('d/m/Y', strtotime($startdate)) }}
                @endif
                @if (!empty($startdate) && !empty($endate))
                    đến {{ date('d/m/Y', strtotime($endate)) }}
                @elseif (empty($startdate) && !empty($endate))
                    đến {{ date('d/m/Y', strtotime($endate)) }}
                @endif
            </div>
        @endif
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-200">
                <tr>
                    <th scope="col" class="px-2 py-4 text-sm font-medium text-gray-700 uppercase tracking-wider w-12 text-center">STT</th>
                    <th scope="col" class="px-2 py-4 text-sm font-medium text-gray-700 uppercase tracking-wider w-36 text-left">Khách Hàng</th>
                    <th scope="col" class="px-2 py-4 text-sm font-medium text-gray-700 uppercase tracking-wider w-32 text-right">Doanh Thu</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200 text-sm">
                @if (empty($results))
                    <tr>
                        <td class="px-2 py-2 whitespace-nowrap text-center" colspan="3">Không có dữ liệu</td>
                    </tr>
                @endif
                @foreach ($results as $index => $result)
                    <tr>
                        <td class="px-2 py-2 whitespace-nowrap text-left">{{$index + 1}}</td>
                        <td class="px-2 py-2">{{$result->name}}</td>
                        <td class="px-2 py-2 whitespace-nowrap text-right">{{number_format($result->total_amount, 2, '.', ',')}}</td>
                    </tr>
                @endforeach
                <tr>
                    <td class="px-2 py-2 whitespace-nowrap"></td>
                    <td class="px-2 py-2 whitespace-nowrap text-left font-semibold">Tổng doanh thu</td>
                    <td class="px-2 py-2 whitespace-nowrap text-right font-semibold">{{number_format(collect($results)->sum('total_amount'), 2, '.', ',')}}</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>