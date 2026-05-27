<div class="rounded-sm border border-stroke bg-white shadow-default dark:border-strokedark dark:bg-boxdark">
    <div class="grid gap-4 border-b border-stroke px-4 py-4 md:grid-cols-2 md:px-6 xl:px-7.5">
        <div class="rounded-sm border border-stroke bg-gray-50 px-4 py-3">
            <p class="text-sm font-medium text-gray-500">Tổng stock tồn kho</p>
            <p class="mt-1 text-2xl font-bold text-gray-800">{{ number_format($totalStock, 0, ',', '.') }}</p>
        </div>
        <div class="rounded-sm border border-stroke bg-gray-50 px-4 py-3">
            <p class="text-sm font-medium text-gray-500">Tổng tiền hàng tồn theo giá sỉ</p>
            <p class="mt-1 text-2xl font-bold text-gray-800">{{ number_format($totalInventoryAmount, 0, ',', '.') }} VND</p>
        </div>
    </div>

    @if ($selectedProduct)
        <div class="border-b border-stroke px-4 py-5 md:px-6 xl:px-7.5">
            <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <h5 class="text-lg font-bold text-gray-800">
                        Lịch sử nhập xuất: {{ $selectedProduct->code }} - {{ $selectedProduct->name }}
                    </h5>
                    <p class="text-sm text-gray-500">
                        {{ optional($selectedProduct->productCategory)->name }} / {{ optional($selectedProduct->productBrand)->name }}
                    </p>
                </div>
                <button type="button" wire:click="closeHistory" class="inline-flex items-center justify-center rounded border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100">
                    Đóng
                </button>
            </div>

            <div class="overflow-x-auto rounded-sm border border-stroke">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-100">
                        <tr>
                            <th scope="col" class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-700">Ngày</th>
                            <th scope="col" class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-700">Loại</th>
                            <th scope="col" class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-700">Mã phiếu/đơn</th>
                            <th scope="col" class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-700">Tham chiếu</th>
                            <th scope="col" class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-700">Kho</th>
                            <th scope="col" class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-700">Size</th>
                            <th scope="col" class="px-3 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-700">Nhập</th>
                            <th scope="col" class="px-3 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-700">Xuất</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white text-sm">
                        @forelse ($inventoryHistory as $history)
                            <tr>
                                <td class="whitespace-nowrap px-3 py-2">{{ $history->date ? \Carbon\Carbon::parse($history->date)->format('d/m/Y H:i') : '-' }}</td>
                                <td class="whitespace-nowrap px-3 py-2">{{ $history->type }}</td>
                                <td class="whitespace-nowrap px-3 py-2">{{ $history->code }}</td>
                                <td class="px-3 py-2">{{ $history->reference_name ?? '-' }}</td>
                                <td class="whitespace-nowrap px-3 py-2">{{ $history->warehouse_name ?? '-' }}</td>
                                <td class="whitespace-nowrap px-3 py-2">{{ $history->size_name ?? '-' }}</td>
                                <td class="whitespace-nowrap px-3 py-2 text-right text-green-600">{{ $history->quantity_in > 0 ? number_format($history->quantity_in, 0, ',', '.') : '-' }}</td>
                                <td class="whitespace-nowrap px-3 py-2 text-right text-red-600">{{ $history->quantity_out > 0 ? number_format($history->quantity_out, 0, ',', '.') : '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td class="px-3 py-4 text-center text-gray-500" colspan="8">Không có lịch sử nhập xuất</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-200">
                <tr>
                    <th scope="col" class="px-2 py-4 text-sm font-medium text-gray-700 uppercase tracking-wider w-12 text-center">STT</th>
                    <th scope="col" class="px-2 py-4 text-sm font-medium text-gray-700 uppercase tracking-wider w-20 text-center"></th>
                    <th scope="col" class="px-2 py-4 text-sm font-medium text-gray-700 uppercase tracking-wider w-36 text-center">Mã sản phẩm</th>
                    <th scope="col" class="px-2 py-4 text-sm font-medium text-gray-700 uppercase tracking-wider text-left">Tên Sản phẩm</th>
                    <th scope="col" class="px-2 py-4 text-sm font-medium text-gray-700 uppercase tracking-wider text-left">Danh Mục</th>
                    <th scope="col" class="px-2 py-4 text-sm font-medium text-gray-700 uppercase tracking-wider text-left">Nhãn Hàng</th>
                    <th scope="col" class="px-2 py-4 text-sm font-medium text-gray-700 uppercase tracking-wider w-32 text-right">Giá lẽ (VND)</th>
                    <th scope="col" class="px-2 py-4 text-sm font-medium text-gray-700 uppercase tracking-wider w-32 text-right">Giá sỉ (VND)</th>
                    <th scope="col" class="px-2 py-4 text-sm font-medium text-gray-700 uppercase tracking-wider w-32 text-center">Tổng nhập kho</th>
                    <th scope="col" class="px-2 py-4 text-sm font-medium text-gray-700 uppercase tracking-wider w-32 text-center">Đã được đặt</th>
                    <th scope="col" class="px-2 py-4 text-sm font-medium text-gray-700 uppercase tracking-wider w-32 text-center">Tồn kho</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200 text-sm	">
                @if ($products->isEmpty())
                    <tr>
                        <td class="px-2 py-2 whitespace-nowrap text-center" colspan="8">Không có dữ liệu</td>
                    </tr>
                @endif
                @foreach ($products as $index => $product)
                        <tr>
                            <td class="px-2 py-2 whitespace-nowrap text-center">{{ $index + 1 }}</td>
                            <td class="px-2 py-2 whitespace-nowrap text-center">
                                <div class="flex justify-center">
                                    @if (count($product->productDetails) > 0 && $product->productDetails[0] && $product->productDetails[0]->image)
                                        @php
                                            $imageThumbnailCheck = json_decode($product->productDetails[0]->image);   
                                            $imageThumbnail = $imageThumbnailCheck ? $imageThumbnailCheck[0] : $product->productDetails[0]->image;
                                        @endphp
                                        <img src="{{ asset('storage/images/products/' . $imageThumbnail) }}" alt="Hình ảnh sản phẩm" class="w-15 h-15 shadow-md">
                                    @else
                                        <img src="{{ asset('library/images/image-not-found.jpg') }}" alt="Không có hình ảnh sản phẩm" class="w-15 h-15 shadow-md">
                                    @endif
                                </div>
                            </td>
                            <td class="px-2 py-2 whitespace-nowrap text-center">
                                <button type="button" wire:click="selectProduct({{ $product->id }})" class="font-medium text-blue-600 underline-offset-2 hover:text-blue-800 hover:underline">
                                    {{$product->code}}
                                </button>
                            </td>
                            <td class="px-2 py-2">{{$product->name}}</td>
                            <td class="px-2 py-2 whitespace-nowrap text-left">{{ optional($product->productCategory)->name }}</td>
                            <td class="px-2 py-2 whitespace-nowrap text-left">{{ optional($product->productBrand)->name }}</td>
                            <td class="px-2 py-2 whitespace-nowrap text-right">{{number_format($product->retail_price, 0, ',', '.')}}</td>
                            <td class="px-2 py-2 whitespace-nowrap text-right">{{number_format($product->wholesale_price, 0, ',', '.')}}</td>
                            <td class="px-2 py-2 whitespace-nowrap text-right">{{ number_format($product->total_imported, 0, ',', '.') }}</td>
                           
                            <td class="px-2 py-2 whitespace-nowrap text-right">{{ number_format($product->total_ordered, 0, ',', '.') }}</td>
                            <td class="px-2 py-2 whitespace-nowrap text-right">{{ number_format($product->total_imported - $product->total_ordered, 0, ',', '.') }}</td>
                        </tr>
                @endforeach
            </tbody>
        </table>
        <div class="px-4 py-6 md:px-6 xl:px-7.5">
            {{$products->links('livewire.custom-pagination')}}
        </div>
    </div>
</div>
