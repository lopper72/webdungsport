@extends('admin.layouts.master')

@section('title', 'Xem phiếu trả hàng')
@section('menu', 'sales_returns')

@section('content')
    <div class="container mx-auto px-2 py-8 sm:px-6 md:px-8">
        <h3 class="text-2xl text-gray-700 font-bold">XEM PHIẾU TRẢ HÀNG</h3>
        <nav class="text-sm font-medium text-gray-500 py-4" aria-label="breadcrumb">
            <ol class="list-none p-0 inline-flex">
                <li class="flex items-center">
                    <a href="{{ route('admin') }}" class="text-blue-500 hover:text-blue-700">Bảng điều khiển</a>
                    &nbsp;/&nbsp;
                </li>
                <li class="flex items-center">
                    <a href="{{ route('admin.sales-returns') }}" class="text-blue-500 hover:text-blue-700">Trả hàng</a>
                    &nbsp;/&nbsp;
                </li>
                <li class="flex items-center">
                    <span class="text-gray-700">{{ $salesReturn->code }}</span>
                </li>
            </ol>
        </nav>

        <div class="rounded-sm border border-stroke bg-white shadow-default dark:border-strokedark dark:bg-boxdark">
            <div class="px-4 py-6 md:px-6 xl:px-7.5">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <h4 class="text-xl font-bold text-black dark:text-white">
                        <span class="uppercase font-bold text-sky-500">{{ $salesReturn->code }}</span>
                    </h4>
                    <a href="{{ route('admin.sales-returns.pdf', ['id' => $salesReturn->id]) }}" class="inline-flex items-center justify-center px-4 py-2 bg-blue-500 border border-transparent rounded-md font-semibold text-xs text-white tracking-widest hover:bg-blue-600 active:bg-blue-700 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
                        Xuất PDF
                    </a>
                </div>
            </div>

            <div class="overflow-x-auto px-4 py-6 md:px-6 xl:px-7.5">
                <div class="grid gap-x-6 gap-y-6 grid-cols-1 sm:grid-cols-2 md:grid-cols-3">
                    <div>
                        <div class="block text-sm font-medium leading-6 text-gray-900">Khách hàng</div>
                        <div class="mt-2 text-sm text-gray-700">{{ $salesReturn->customer?->name }}</div>
                    </div>
                    <div>
                        <div class="block text-sm font-medium leading-6 text-gray-900">Ngày trả</div>
                        <div class="mt-2 text-sm text-gray-700">{{ optional($salesReturn->return_date)->format('d/m/Y') }}</div>
                    </div>
                    <div>
                        <div class="block text-sm font-medium leading-6 text-gray-900">Trạng thái</div>
                        <div class="mt-2 text-sm text-gray-700">{{ $salesReturn->status }}</div>
                    </div>
                    <div class="sm:col-span-2 md:col-span-3">
                        <div class="block text-sm font-medium leading-6 text-gray-900">Ghi chú</div>
                        <div class="mt-2 text-sm text-gray-700">{{ $salesReturn->note ?: '-' }}</div>
                    </div>
                </div>

                <div class="mt-8">
                    <h4 class="text-xl font-bold text-black dark:text-white inline">DANH SÁCH SẢN PHẨM TRẢ</h4>
                </div>

                <div class="overflow-x-auto mt-3">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-200">
                            <tr>
                                <th scope="col" class="px-2 py-4 text-xs font-medium text-gray-700 uppercase tracking-wider w-12 text-center">STT</th>
                                <th scope="col" class="px-2 py-4 text-xs font-medium text-gray-700 uppercase tracking-wider w-36 text-left">Đơn gốc</th>
                                <th scope="col" class="px-2 py-4 text-xs font-medium text-gray-700 uppercase tracking-wider text-left">Sản phẩm</th>
                                <th scope="col" class="px-2 py-4 text-xs font-medium text-gray-700 uppercase tracking-wider w-36 text-left">Mẫu</th>
                                <th scope="col" class="px-2 py-4 text-xs font-medium text-gray-700 uppercase tracking-wider w-20 text-left">Size</th>
                                <th scope="col" class="px-2 py-4 text-xs font-medium text-gray-700 uppercase tracking-wider w-32 text-left">Kho</th>
                                <th scope="col" class="px-2 py-4 text-xs font-medium text-gray-700 uppercase tracking-wider w-24 text-right">SL trả</th>
                                <th scope="col" class="px-2 py-4 text-xs font-medium text-gray-700 uppercase tracking-wider w-32 text-right">Giá trả</th>
                                <th scope="col" class="px-2 py-4 text-xs font-medium text-gray-700 uppercase tracking-wider w-32 text-right">Thành tiền</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200 text-sm">
                            @forelse ($salesReturn->details as $detail)
                                <tr>
                                    <td class="px-2 py-2 whitespace-nowrap text-center">{{ $loop->iteration }}</td>
                                    <td class="px-2 py-2 whitespace-nowrap text-left">
                                        @if ($detail->order)
                                            <a href="{{ route('admin.orders.view', ['id' => $detail->order_id]) }}" class="text-blue-600 hover:text-blue-800">
                                                {{ $detail->order->code }}
                                            </a>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="px-2 py-2 text-left">{{ $detail->product?->name }}</td>
                                    <td class="px-2 py-2 whitespace-nowrap text-left">{{ $detail->productDetail?->title ?? $detail->productDetail?->color ?? '-' }}</td>
                                    <td class="px-2 py-2 whitespace-nowrap text-left">{{ $detail->productSize?->size ?? '-' }}</td>
                                    <td class="px-2 py-2 whitespace-nowrap text-left">{{ $detail->warehouse?->name }}</td>
                                    <td class="px-2 py-2 whitespace-nowrap text-right">{{ number_format($detail->quantity, 0, ',', '.') }}</td>
                                    <td class="px-2 py-2 whitespace-nowrap text-right">{{ number_format($detail->unit_price, 0, ',', '.') }}</td>
                                    <td class="px-2 py-2 whitespace-nowrap text-right">{{ number_format($detail->total_amount, 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td class="px-2 py-2 whitespace-nowrap text-center" colspan="9">Không có dữ liệu</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-6 flex justify-end">
                    <div class="w-full max-w-md space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Tổng tiền trả</span>
                            <span class="font-semibold">{{ number_format($salesReturn->total_amount, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Cấn trừ công nợ</span>
                            <span class="font-semibold text-blue-600">{{ number_format($salesReturn->debt_adjustment_amount, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between border-t pt-2">
                            <span class="text-gray-900 font-semibold">Cần hoàn tiền</span>
                            <span class="text-xl font-bold text-red-600">{{ number_format($salesReturn->refund_amount, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex sm:flex-row items-center justify-end gap-x-6">
                    <a href="{{ route('admin.sales-returns') }}" class="text-sm font-semibold leading-6 text-gray-900">Quay lại</a>
                </div>
            </div>
        </div>
    </div>
@endsection
