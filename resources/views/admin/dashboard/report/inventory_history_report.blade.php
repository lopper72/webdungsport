@extends('admin.layouts.master')

@section('title', 'Báo cáo')
@section('menu', 'reports')

@section('content')
<div class="rounded-sm border border-stroke bg-white shadow-default dark:border-strokedark dark:bg-boxdark">

    {{-- Header --}}
    <div class="px-4 py-5 md:px-6 xl:px-7.5 border-b border-stroke">
        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
            <div>
                <a href="{{ route('admin.reports.inventory') }}"
                    class="text-sm text-blue-500 hover:underline mb-2 inline-block">← Quay lại báo cáo tồn kho</a>
                <h4 class="text-xl font-bold text-black dark:text-white">Lịch sử nhập xuất kho</h4>
                <p class="text-lg font-semibold text-blue-600 mt-0.5">
                    {{ $product->code }} — {{ $product->name }}
                </p>
                <p class="text-sm text-gray-500 mt-0.5">
                    {{ optional($product->productCategory)->name }}
                    @if(optional($product->productBrand)->name)
                        / {{ $product->productBrand->name }}
                    @endif
                </p>
            </div>
            @if(count($product->productDetails) > 0 && $product->productDetails[0]?->image)
                @php
                    $imgRaw = json_decode($product->productDetails[0]->image);
                    $imgSrc = $imgRaw ? $imgRaw[0] : $product->productDetails[0]->image;
                @endphp
                <img src="{{ asset('storage/images/products/' . $imgSrc) }}"
                    alt="{{ $product->name }}"
                    class="w-20 h-20 object-cover rounded shadow-md">
            @endif
        </div>
    </div>

    {{-- Thống kê nhanh — tính từ toàn bộ $history, không đổi theo trang --}}
    @php
        $stock = $totalIn - $totalOut;
        $perPage = 20;
        $currentPage = request()->get('page', 1);
        $pagedHistory = $history->forPage($currentPage, $perPage);
        $total = $history->count();
        $lastPage = ceil($total / $perPage);
        $firstItem = ($currentPage - 1) * $perPage + 1;
        $lastItem = min($currentPage * $perPage, $total);
    @endphp
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 px-4 py-5 md:px-6 xl:px-7.5 border-b border-stroke">
        <div class="rounded-sm border border-stroke bg-gray-50 px-4 py-3">
            <p class="text-sm font-medium text-gray-500">Tổng nhập</p>
            <p class="mt-1 text-2xl font-bold text-green-600">{{ number_format($totalIn, 0, ',', '.') }}</p>
        </div>
        <div class="rounded-sm border border-stroke bg-gray-50 px-4 py-3">
            <p class="text-sm font-medium text-gray-500">Tổng xuất</p>
            <p class="mt-1 text-2xl font-bold text-red-500">{{ number_format($totalOut, 0, ',', '.') }}</p>
        </div>
        <div class="rounded-sm border border-stroke bg-gray-50 px-4 py-3">
            <p class="text-sm font-medium text-gray-500">Tồn kho hiện tại</p>
            <p class="mt-1 text-2xl font-bold text-blue-600">{{ number_format($stock, 0, ',', '.') }}</p>
        </div>
        <div class="rounded-sm border border-stroke bg-gray-50 px-4 py-3">
            <p class="text-sm font-medium text-gray-500">Số giao dịch</p>
            <p class="mt-1 text-2xl font-bold text-gray-700">{{ $total }}</p>
        </div>
    </div>

    {{-- Bảng lịch sử --}}
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-200">
                <tr>
                    <th class="px-3 py-4 text-sm font-medium text-gray-700 uppercase tracking-wider w-12 text-center">STT</th>
                    <th class="px-3 py-4 text-sm font-medium text-gray-700 uppercase tracking-wider text-left">Tham chiếu</th>
                    <th class="px-3 py-4 text-sm font-medium text-gray-700 uppercase tracking-wider text-left">Mẫu sản phẩm</th>
                    <th class="px-3 py-4 text-sm font-medium text-gray-700 uppercase tracking-wider text-left">Size</th>
                    <th class="px-3 py-4 text-sm font-medium uppercase tracking-wider text-right text-green-600">Nhập</th>
                    <th class="px-3 py-4 text-sm font-medium uppercase tracking-wider text-right text-red-500">Xuất</th>
                    <th class="px-3 py-4 text-sm font-medium text-gray-700 uppercase tracking-wider text-left">Loại</th>
                    <th class="px-3 py-4 text-sm font-medium text-gray-700 uppercase tracking-wider text-left">Ngày</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200 text-sm">
                @forelse ($pagedHistory as $i => $row)
                    <tr class="hover:bg-gray-50">
                        <td class="px-3 py-2 whitespace-nowrap text-center text-gray-400">{{ $firstItem + $i }}</td>
                        <td class="px-3 py-2 whitespace-nowrap">
                            {{ $row->reference_name ?? '—' }}
                        </td>
                        <td class="px-3 py-2 whitespace-nowrap">
                            {{ $product->name }}{{ !empty($row->product_model) ? ' - ' . $row->product_model : '' }}
                        </td>
                        <td class="px-3 py-2 whitespace-nowrap">{{ $row->size_name ?? '—' }}</td>
                        <td class="px-3 py-2 whitespace-nowrap text-right font-medium text-green-600">
                            {{ $row->quantity_in > 0 ? number_format($row->quantity_in, 0, ',', '.') : '—' }}
                        </td>
                        <td class="px-3 py-2 whitespace-nowrap text-right font-medium text-red-500">
                            {{ $row->quantity_out > 0 ? number_format($row->quantity_out, 0, ',', '.') : '—' }}
                        </td>
                        <td class="px-3 py-2 whitespace-nowrap">
                            @if(str_contains(strtolower($row->type), 'nhập'))
                                <span class="inline-block px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">{{ $row->type }}</span>
                            @else
                                <span class="inline-block px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-600">{{ $row->type }}</span>
                            @endif
                        </td>
                        <td class="px-3 py-2 whitespace-nowrap">
                            {{ $row->date ? \Carbon\Carbon::parse($row->date)->format('d/m/Y H:i') : '—' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td class="px-3 py-8 text-center text-gray-400" colspan="8">Không có lịch sử nhập xuất</td>
                    </tr>
                @endforelse

                @if($total > 0)
                <tr class="bg-gray-100 font-semibold">
                    <td class="px-3 py-2 text-right" colspan="4">Tổng:</td>
                    <td class="px-3 py-2 text-right text-green-600">{{ number_format($totalIn, 0, ',', '.') }}</td>
                    <td class="px-3 py-2 text-right text-red-500">{{ number_format($totalOut, 0, ',', '.') }}</td>
                    <td class="px-3 py-2"></td>
                    <td class="px-3 py-2"></td>
                </tr>
                @endif
            </tbody>
        </table>
    </div>

    {{-- Phân trang thủ công --}}
    @if($lastPage > 1)
    <div class="px-4 py-4 md:px-6 xl:px-7.5 flex flex-col sm:flex-row justify-between items-center gap-2 border-t border-stroke">
        <p class="text-sm text-gray-500">
            Hiển thị {{ $firstItem }}–{{ $lastItem }} / {{ $total }} giao dịch
        </p>
        <div class="flex gap-1">
            @if($currentPage > 1)
                <a href="?page={{ $currentPage - 1 }}"
                   class="px-3 py-1.5 text-sm border border-stroke rounded hover:bg-gray-100">← Trước</a>
            @endif

            @for($p = max(1, $currentPage - 2); $p <= min($lastPage, $currentPage + 2); $p++)
                <a href="?page={{ $p }}"
                   class="px-3 py-1.5 text-sm border rounded {{ $p == $currentPage ? 'bg-sky-500 text-white border-sky-500' : 'border-stroke hover:bg-gray-100' }}">
                    {{ $p }}
                </a>
            @endfor

            @if($currentPage < $lastPage)
                <a href="?page={{ $currentPage + 1 }}"
                   class="px-3 py-1.5 text-sm border border-stroke rounded hover:bg-gray-100">Sau →</a>
            @endif
        </div>
    </div>
    @endif

    <div class="px-4 py-4 md:px-6 xl:px-7.5 text-xs text-gray-400 text-right">
        Xem lúc: {{ now()->format('d/m/Y H:i') }}
    </div>
</div>
@endsection
