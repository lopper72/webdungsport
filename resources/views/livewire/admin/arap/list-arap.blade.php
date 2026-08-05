<div class="rounded-sm border border-stroke bg-white shadow-default dark:border-strokedark dark:bg-boxdark">
    <div class="px-4 py-6 md:px-6 xl:px-7.5">
        <div class="flex justify-between items-center">
            <h4 class="text-xl font-bold text-black dark:text-white inline">DANH SÁCH KHÁCH HÀNG CÓ CÔNG NỢ</h4>
        </div>
    </div>

    <div class="px-4 py-1 mb-2 md:px-6 xl:px-7.5">
        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <input wire:model='search_input' wire:keydown='search' type="text" name="search" placeholder="Tìm kiếm..." class="px-2 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring focus:border-blue-300 md:px-3 md:py-2">
            <div class="flex items-center gap-2">
                <select wire:model="customer" id="customer" name="customer" class="block w-full pl-3 pr-10 mt-1 border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md" wire:change="filterByCustomer()">
                    <option value="ALL">Chọn khách hàng</option>
                    @foreach ($user_choose as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>
                <select wire:model="year" id="year" name="year" class="block w-full pl-3 pr-10 mt-1 border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md" wire:change="filterByYear()">
                    <option value="ALL">Chọn năm</option>
                    @for ($i = now()->year; $i >= 2010; $i--)
                        <option value="{{ $i }}">{{ $i }}</option>
                    @endfor
                </select>
            </div>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-200">
                <tr>
                    <th scope="col" class="px-2 py-4 text-sm font-medium text-gray-700 uppercase tracking-wider w-12 text-center">STT</th>
                    <th scope="col" class="px-2 py-4 text-sm font-medium text-gray-700 uppercase tracking-wider w-72 text-center">Mã khách hàng</th>
                    <th scope="col" class="px-2 py-4 text-sm font-medium text-gray-700 uppercase tracking-wider text-left">Tên khách hàng</th>
                    <th scope="col" class="px-2 py-4 text-sm font-medium text-gray-700 uppercase tracking-wider w-64 text-right">Tổng tiền hàng</th>
                    <th scope="col" class="px-2 py-4 text-sm font-medium text-gray-700 uppercase tracking-wider w-56 text-right">Đã thanh toán</th>
                    <th scope="col" class="px-2 py-4 text-sm font-medium text-gray-700 uppercase tracking-wider w-56 text-right">Công nợ</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200 text-sm">
                @if ($users->isEmpty())
                    <tr>
                        <td class="px-2 py-2 whitespace-nowrap text-center" colspan="6">Không có dữ liệu</td>
                    </tr>
                @endif

                @php
                    $totalPaid = 0;
                    $totalUnpaid = 0;
                    $totalAmount = 0;
                @endphp

                @foreach ($users as $user)
                    @php
                        $debtOrders = $user->orders()
                            ->whereIn('payment_status', ['unpaid', 'partial', 'pending'])
                            ->where('status', '<>', 'rejected')
                            ->when($year != "ALL", function ($query) use ($year) {
                                $query->whereYear('order_date', $year);
                            })
                            ->get();

                        $totalAmountUser = $debtOrders->sum('total_amount');
                        $totalPaidUser = $debtOrders->sum('paid_amount');
                        // Công nợ = tổng (Payable Amount - Paid Amount). Payable = Order Total - Return Offset (theo đơn).
                        $totalUnpaidUser = $debtOrders->sum(function ($order) {
                            $payable = max((float) $order->total_amount - \App\Services\DebtService::returnAdjustedByOrder((int) $order->id), 0);
                            return max($payable - (float) ($order->paid_amount ?? 0), 0);
                        });


                        $totalPaid += $totalPaidUser;
                        $totalUnpaid += $totalUnpaidUser;
                        $totalAmount += $totalAmountUser;
                    @endphp
                    <tr>
                        <td class="px-2 py-2 whitespace-nowrap text-center">{{ $users->perPage() * ($users->currentPage() - 1) + $loop->iteration }}</td>
                        <td class="px-2 py-2 whitespace-nowrap text-center">
                            <a href="{{ route('admin.araps.view', $user->id) }}" class="inline-flex items-center mr-2 text-indigo-600 hover:text-indigo-900">
                                {{ $user->code }}
                            </a>
                        </td>
                        <td class="px-2 py-2 whitespace-nowrap text-left">{{ $user->name }}</td>
                        <td class="px-2 py-2 whitespace-nowrap text-right">{{ number_format($totalAmountUser, 0, ',', '.') }}</td>
                        <td class="px-2 py-2 whitespace-nowrap text-right">{{ number_format($totalPaidUser, 0, ',', '.') }}</td>
                        <td class="px-2 py-2 whitespace-nowrap text-right">{{ number_format($totalUnpaidUser, 0, ',', '.') }}</td>
                    </tr>
                @endforeach

                <tr class="bg-gray-100">
                    <td class="px-2 py-2 whitespace-nowrap text-right" colspan="3">Tổng</td>
                    <td class="px-2 py-2 whitespace-nowrap text-right">{{ number_format($totalAmount, 0, ',', '.') }}</td>
                    <td class="px-2 py-2 whitespace-nowrap text-right">{{ number_format($totalPaid, 0, ',', '.') }}</td>
                    <td class="px-2 py-2 whitespace-nowrap text-right">{{ number_format($totalUnpaid, 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="px-4 py-6 md:px-6 xl:px-7.5">
        {{ $users->links('livewire.custom-pagination') }}
    </div>
</div>
