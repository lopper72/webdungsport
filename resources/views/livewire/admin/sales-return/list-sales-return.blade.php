<div class="rounded-sm border border-stroke bg-white shadow-default dark:border-strokedark dark:bg-boxdark">
    <div class="px-4 py-6 md:px-6 xl:px-7.5">
        <div class="flex justify-between items-center">
            <h4 class="text-xl font-bold text-black dark:text-white inline">DANH SÁCH TRẢ HÀNG</h4>
            <a href="{{ route('admin.sales-returns.add') }}" class="inline-flex items-center px-4 py-2 bg-blue-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-600 active:bg-blue-700 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
                Thêm mới
            </a>
        </div>
    </div>

    @if (session()->has('message'))
        <div class="px-4 py-2 md:px-6 xl:px-7.5 text-sm text-green-600">{{ session('message') }}</div>
    @endif

    <div class="px-4 py-1 mb-2 md:px-6 xl:px-7.5">
        <input wire:model="search_input" wire:keydown="search" type="text" name="search" placeholder="Tìm kiếm..." class="px-2 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring focus:border-blue-300">
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-200">
                <tr>
                    <th scope="col" class="px-2 py-4 text-sm font-medium text-gray-700 uppercase tracking-wider w-12 text-center">STT</th>
                    <th scope="col" class="px-2 py-4 text-sm font-medium text-gray-700 uppercase tracking-wider w-48 text-left">Mã phiếu</th>
                    <th scope="col" class="px-2 py-4 text-sm font-medium text-gray-700 uppercase tracking-wider text-left">Khách hàng</th>
                    <th scope="col" class="px-2 py-4 text-sm font-medium text-gray-700 uppercase tracking-wider w-40 text-left">Ngày trả</th>
                    <th scope="col" class="px-2 py-4 text-sm font-medium text-gray-700 uppercase tracking-wider w-48 text-right">Tổng tiền</th>
                    <th scope="col" class="px-2 py-4 text-sm font-medium text-gray-700 uppercase tracking-wider w-48 text-right">Cấn nợ</th>
                    <th scope="col" class="px-2 py-4 text-sm font-medium text-gray-700 uppercase tracking-wider w-48 text-right">Hoàn tiền</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200 text-sm">
                @if ($salesReturns->isEmpty())
                    <tr>
                        <td class="px-2 py-2 whitespace-nowrap text-center" colspan="7">Không có dữ liệu</td>
                    </tr>
                @endif
                @foreach ($salesReturns as $salesReturn)
                    <tr>
                        <td class="px-2 py-2 whitespace-nowrap text-center">{{ $salesReturns->perPage() * ($salesReturns->currentPage() - 1) + $loop->iteration }}</td>
                        <td class="px-2 py-2 whitespace-nowrap text-left">{{ $salesReturn->code }}</td>
                        <td class="px-2 py-2 whitespace-nowrap text-left">{{ $salesReturn->customer?->name }}</td>
                        <td class="px-2 py-2 whitespace-nowrap text-left">{{ optional($salesReturn->return_date)->format('d/m/Y') }}</td>
                        <td class="px-2 py-2 whitespace-nowrap text-right">{{ number_format($salesReturn->total_amount, 0, ',', '.') }}</td>
                        <td class="px-2 py-2 whitespace-nowrap text-right">{{ number_format($salesReturn->debt_adjustment_amount, 0, ',', '.') }}</td>
                        <td class="px-2 py-2 whitespace-nowrap text-right">{{ number_format($salesReturn->refund_amount, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="px-4 py-6 md:px-6 xl:px-7.5">
        {{ $salesReturns->links('livewire.custom-pagination') }}
    </div>
</div>
