<div class="rounded-sm border border-stroke bg-white shadow-default dark:border-strokedark dark:bg-boxdark">
    <div class="px-4 py-6 md:px-6 xl:px-7.5">
        <div class="flex justify-between items-center">
            <h4 class="text-xl font-bold text-black dark:text-white inline">DANH SÁCH CÔNG NỢ CỦA: {{ $user->name }}</h4>
        </div>
    </div>

    <div class="px-4 py-1 mb-2 md:px-6 xl:px-7.5">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">

            {{-- Nhập tổng tiền --}}
            <div>
                <div class="flex flex-col gap-2 sm:flex-row sm:items-start">
                    <div>
                        <input wire:model="payment_amount" type="number" min="1" step="1000"
                            placeholder="Nhập số tiền thanh toán"
                            class="px-2 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring focus:border-blue-300 md:px-3 md:py-2 w-56">
                        @error('payment_amount')
                            <div class="mt-1 text-sm text-red-600">{{ $message }}</div>
                        @enderror
                    </div>
                    <button type="button" wire:click="previewDebtAllocation"
                        class="px-3 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 focus:outline-none focus:ring focus:ring-blue-300 whitespace-nowrap">
                        Xem phân bổ &amp; Cập nhật
                    </button>
                </div>
                @if (session()->has('success'))
                    <div class="mt-2 text-sm text-green-600 font-medium">✓ {{ session('success') }}</div>
                @endif
                @if ($success_message)
                    <div class="mt-2 text-sm text-green-600 font-medium bg-green-50 border border-green-200 rounded px-3 py-2" id="success-alert">
                        {{ $success_message }}
                    </div>
                @endif
            </div>

            {{-- Tìm kiếm & lọc --}}
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                <input wire:model='search_input' wire:keydown='search' type="text"
                    placeholder="Tìm kiếm mã đơn..."
                    class="px-2 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring focus:border-blue-300 md:px-3 md:py-2">
                <select wire:model="month" wire:change="filterByMonth()"
                    class="px-2 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring focus:border-blue-300 md:px-3 md:py-2">
                    <option value="ALL">Chọn tháng</option>
                    @for ($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}">Tháng {{ $m }}</option>
                    @endfor
                </select>
                <select wire:model="year" wire:change="filterByYear()"
                    class="px-2 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring focus:border-blue-300 md:px-3 md:py-2">
                    <option value="ALL">Chọn năm</option>
                    @for ($i = now()->year; $i >= 2010; $i--)
                        <option value="{{ $i }}">{{ $i }}</option>
                    @endfor
                </select>
            </div>
        </div>
    </div>

    {{-- ===================== MODAL PHÂN BỔ ===================== --}}
    @if ($show_preview)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 px-4">
            <div class="bg-white rounded-lg shadow-xl w-full max-w-4xl max-h-[90vh] flex flex-col">

                {{-- Header --}}
                <div class="px-6 py-4 border-b border-gray-200 flex items-start justify-between gap-4">
                    <div>
                        <h3 class="text-lg font-bold text-gray-800">Xác nhận phân bổ công nợ</h3>
                        <p class="text-sm text-gray-500 mt-0.5">
                            Bạn có thể <span class="font-medium text-gray-700">chỉnh sửa số tiền</span>
                            từng đơn bên dưới trước khi xác nhận.
                            Số tiền mỗi đơn không được vượt quá công nợ hiện tại của đơn đó.
                        </p>
                    </div>
                    <button wire:click="cancelPreview"
                        class="text-gray-400 hover:text-gray-600 text-2xl leading-none flex-shrink-0 mt-0.5">&times;</button>
                </div>

                {{-- Thông báo xác nhận --}}
                @if ($confirm_message)
                    <div class="px-6 py-3 bg-blue-500 text-white text-sm font-medium flex items-center gap-2">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M12 2a10 10 0 100 20A10 10 0 0012 2z"/></svg>
                        {{ $confirm_message }}
                        Vui lòng kiểm tra phân bổ bên dưới rồi bấm <strong class="underline">Xác nhận lưu</strong>.
                    </div>
                @endif

                {{-- Tổng kết nhanh ở trên --}}
                <div class="px-6 py-3 bg-blue-50 border-b border-blue-100 flex flex-wrap gap-6 text-sm">
                    <span>
                        Số tiền đã nhập:
                        <strong class="text-blue-700">{{ number_format((float)$payment_amount, 0, ',', '.') }} đ</strong>
                    </span>
                    <span>
                        Tổng đang phân bổ:
                        <strong class="text-blue-700" id="lw-total-applied">
                            {{ number_format(array_sum(array_column($allocation_preview, 'applied_amount')), 0, ',', '.') }} đ
                        </strong>
                    </span>
                    @php $remaining = (float)$payment_amount - array_sum(array_column($allocation_preview, 'applied_amount')); @endphp
                    <span>
                        Chưa phân bổ:
                        <strong class="{{ $remaining < 0 ? 'text-red-600' : 'text-gray-700' }}">
                            {{ number_format($remaining, 0, ',', '.') }} đ
                        </strong>
                    </span>
                </div>

                @if ($preview_error)
                    <div class="mx-6 mt-3 text-sm text-red-600 bg-red-50 border border-red-200 rounded px-3 py-2">
                        ⚠ {{ $preview_error }}
                    </div>
                @endif

                {{-- Bảng phân bổ --}}
                <div class="overflow-y-auto flex-1 px-6 py-4">
                    @if (count($allocation_preview) === 0)
                        <p class="text-center text-gray-400 py-10">Không có đơn hàng công nợ nào.</p>
                    @else
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="bg-gray-100 text-gray-600 text-xs uppercase">
                                    <th class="px-3 py-2 text-left">Mã đơn hàng</th>
                                    <th class="px-3 py-2 text-left">Ngày đặt</th>
                                    <th class="px-3 py-2 text-right">Tổng tiền</th>
                                    <th class="px-3 py-2 text-right">Đã TT trước</th>
                                    <th class="px-3 py-2 text-right text-red-600">Còn nợ</th>
                                    <th class="px-3 py-2 text-right text-blue-700 w-48">
                                        Phân bổ lần này
                                        <div class="text-gray-400 font-normal normal-case">(có thể sửa)</div>
                                    </th>
                                    <th class="px-3 py-2 text-right">Còn lại sau TT</th>
                                    <th class="px-3 py-2 text-center">Trạng thái mới</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach ($allocation_preview as $i => $item)
                                    @php
                                        $afterPaid    = $item['before_paid'] + (float)$item['applied_amount'];
                                        $afterDebt    = max($item['total_amount'] - $afterPaid, 0);
                                        $newStatus    = ($afterPaid >= $item['total_amount']) ? 'paid' : (($item['applied_amount'] > 0) ? 'partial' : 'unpaid');
                                    @endphp
                                    <tr class="hover:bg-gray-50 align-middle">
                                        <td class="px-3 py-2 font-medium text-indigo-600 whitespace-nowrap">
                                            {{ $item['code'] }}
                                        </td>
                                        <td class="px-3 py-2 text-gray-500 whitespace-nowrap">
                                            {{ $item['order_date'] ? date('d/m/Y', strtotime($item['order_date'])) : '—' }}
                                        </td>
                                        <td class="px-3 py-2 text-right whitespace-nowrap">
                                            {{ number_format($item['total_amount'], 0, ',', '.') }}
                                        </td>
                                        <td class="px-3 py-2 text-right whitespace-nowrap text-gray-500">
                                            {{ number_format($item['before_paid'], 0, ',', '.') }}
                                        </td>
                                        <td class="px-3 py-2 text-right whitespace-nowrap text-red-500 font-medium">
                                            {{ number_format($item['max_applicable'], 0, ',', '.') }}
                                        </td>

                                        {{-- Ô nhập có thể sửa --}}
                                        <td class="px-3 py-2 text-right whitespace-nowrap">
                                            <input
                                                wire:model.lazy="allocation_preview.{{ $i }}.applied_amount"
                                                id="applied-input-{{ $i }}"
                                                type="number"
                                                min="0"
                                                max="{{ $item['max_applicable'] }}"
                                                step="1000"
                                                class="w-40 text-right px-2 py-1 border border-blue-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-400 text-blue-700 font-semibold"
                                            >
                                        </td>

                                        <td class="px-3 py-2 text-right whitespace-nowrap {{ $afterDebt > 0 ? 'text-red-500' : 'text-green-600' }}">
                                            {{ number_format($afterDebt, 0, ',', '.') }}
                                        </td>
                                        <td class="px-3 py-2 text-center whitespace-nowrap">
                                            @if ($newStatus === 'paid')
                                                <span class="inline-block px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">Đã thanh toán</span>
                                            @elseif ($newStatus === 'partial')
                                                <span class="inline-block px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-700">Thanh toán 1 phần</span>
                                            @else
                                                <span class="inline-block px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-500">Không thay đổi</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="bg-gray-50 font-semibold text-sm border-t-2 border-gray-300">
                                    <td class="px-3 py-2 text-right" colspan="5">Tổng:</td>
                                    <td class="px-3 py-2 text-right text-blue-600">
                                        {{ number_format(array_sum(array_column($allocation_preview, 'applied_amount')), 0, ',', '.') }}
                                    </td>
                                    <td class="px-3 py-2 text-right text-red-500">
                                        {{ number_format(array_sum(array_column($allocation_preview, 'max_applicable')) - array_sum(array_column($allocation_preview, 'applied_amount')), 0, ',', '.') }}
                                    </td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    @endif
                </div>

                {{-- Footer --}}
                <div class="px-6 py-4 border-t border-gray-200 flex items-center justify-between gap-3">
                    <p class="text-xs text-gray-400">Tip: Nhập 0 để bỏ qua một đơn hàng</p>
                    <div class="flex gap-3">
                        <button wire:click="cancelPreview"
                            class="px-4 py-2 text-sm border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                            Huỷ
                        </button>
                        @if (count($allocation_preview) > 0)
                            <button
                                id="btn-confirm-debt"
                                type="button"
                                class="px-4 py-2 text-sm bg-blue-500 text-white rounded-md hover:bg-blue-600 font-medium">
                                Xác nhận lưu
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif
    {{-- ===================== END MODAL ===================== --}}

    {{-- Bảng danh sách đơn hàng --}}
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-200">
                <tr>
                    <th class="px-2 py-4 text-sm font-medium text-gray-700 uppercase tracking-wider w-12 text-center">STT</th>
                    <th class="px-2 py-4 text-sm font-medium text-gray-700 uppercase tracking-wider w-72 text-center">Mã đơn hàng</th>
                    <th class="px-2 py-4 text-sm font-medium text-gray-700 uppercase tracking-wider text-left">Ngày đặt</th>
                    <th class="px-2 py-4 text-sm font-medium text-gray-700 uppercase tracking-wider w-56 text-left">Trạng thái TT</th>
                    <th class="px-2 py-4 text-sm font-medium text-gray-700 uppercase tracking-wider w-64 text-right">Tổng tiền hàng</th>
                    <th class="px-2 py-4 text-sm font-medium text-gray-700 uppercase tracking-wider w-56 text-right">Đã thanh toán</th>
                    <th class="px-2 py-4 text-sm font-medium text-gray-700 uppercase tracking-wider w-56 text-right">Công nợ</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200 text-sm">
                @if ($orders->isEmpty())
                    <tr>
                        <td class="px-2 py-8 text-center text-gray-400" colspan="7">Không có dữ liệu</td>
                    </tr>
                @endif

                @foreach ($orders as $order)
                    @php
                        $paidAmount = ($order->payment_status === 'paid') ? $order->total_amount : ($order->paid_amount ?? 0);
                        $debtAmount = max($order->total_amount - $paidAmount, 0);
                    @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-2 py-2 whitespace-nowrap text-center">
                            {{ $orders->perPage() * ($orders->currentPage() - 1) + $loop->iteration }}
                        </td>
                        <td class="px-2 py-2 whitespace-nowrap text-center">
                            <a href="{{ route('admin.orders.view', $order->id) }}"
                                class="text-indigo-600 hover:text-indigo-900">{{ $order->code }}</a>
                        </td>
                        <td class="px-2 py-2 whitespace-nowrap text-left">
                            {{ $order->order_date ? date('d/m/Y', strtotime($order->order_date)) : $order->created_at->format('d/m/Y H:i') }}
                        </td>
                        <td class="px-2 py-2 whitespace-nowrap text-left">
                            @if ($order->payment_status === 'paid')
                                <span class="inline-block px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">Đã thanh toán</span>
                            @elseif ($order->payment_status === 'partial')
                                <span class="inline-block px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-700">Thanh toán 1 phần</span>
                            @else
                                <span class="inline-block px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-600">Chưa thanh toán</span>
                            @endif
                        </td>
                        <td class="px-2 py-2 whitespace-nowrap text-right">{{ number_format($order->total_amount, 0, ',', '.') }}</td>
                        <td class="px-2 py-2 whitespace-nowrap text-right">{{ number_format($paidAmount, 0, ',', '.') }}</td>
                        <td class="px-2 py-2 whitespace-nowrap text-right {{ $debtAmount > 0 ? 'text-red-500 font-medium' : '' }}">
                            {{ number_format($debtAmount, 0, ',', '.') }}
                        </td>
                    </tr>
                @endforeach

                @php
                    $totalAmount = $orders->sum('total_amount');
                    $totalPaid   = $orders->sum('paid_amount');
                    $totalUnpaid = $orders->sum(fn($o) => max($o->total_amount - ($o->paid_amount ?? 0), 0));
                @endphp
                <tr class="bg-gray-100 font-semibold">
                    <td class="px-2 py-2 text-right" colspan="4">Tổng</td>
                    <td class="px-2 py-2 text-right">{{ number_format($totalAmount, 0, ',', '.') }}</td>
                    <td class="px-2 py-2 text-right">{{ number_format($totalPaid, 0, ',', '.') }}</td>
                    <td class="px-2 py-2 text-right text-red-500">{{ number_format($totalUnpaid, 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="px-4 py-6 md:px-6 xl:px-7.5">
        {{ $orders->links('livewire.custom-pagination') }}
    </div>
</div>

<script>
    document.addEventListener('livewire:initialized', () => {
        // Xác nhận lưu
        document.addEventListener('click', function (e) {
            if (e.target && e.target.id === 'btn-confirm-debt') {
                const userName = @js($user->name ?? '');
                const totalApplied = Array.from(
                    document.querySelectorAll('[id^="applied-input-"]')
                ).reduce((sum, el) => sum + (parseFloat(el.value) || 0), 0);
                const formatted = new Intl.NumberFormat('vi-VN').format(totalApplied);
                if (confirm(`Xác nhận thanh toán ${formatted} đ cho ${userName}?`)) {
                    @this.confirmDebtAllocation();
                }
            }
        });

        // Tự ẩn success message sau 4 giây
        Livewire.on('clear-success-message', () => {
            setTimeout(() => {
                const el = document.getElementById('success-alert');
                if (el) {
                    el.style.transition = 'opacity 0.5s';
                    el.style.opacity = '0';
                }
                setTimeout(() => @this.set('success_message', ''), 500);
            }, 4000);
        });
    });
</script>
