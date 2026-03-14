<div class="w-full bg-white py-4 md:py-6 px-2 md:px-6">
    <h2 class="text-lg lg:text-2xl font-bold text-gray-800 mb-2">Sản phẩm mới</h2>
    <div class=" flex items-center flex-wrap">
        @foreach ($products as $product)
            <div class="w-1/2 lg:w-1/3 xl:w-1/4 p-2 md:p-4">
                <a href="{{route('product-detail', ['id' => $product->id, 'slug' => $product->slug])}}">
                    <div class="w-full aspect-w-1 aspect-h-1 overflow-hidden">
                        @if (count($product->productDetails) > 0 && $product->productDetails[0] && $product->productDetails[0]->image)
                            @php
                                $imageThumbnailCheck = json_decode($product->productDetails[0]->image);   
                                $imageThumbnail = $imageThumbnailCheck ? $imageThumbnailCheck[0] : $product->productDetails[0]->image;
                            @endphp
                            <img src="{{ asset('storage/images/products/' . $imageThumbnail) }}" alt="Hình ảnh sản phẩm" class="hover:grow hover:shadow-lg w-full h-full object-cover">
                        @else
                            <img src="{{ asset('library/images/image-not-found.jpg') }}" alt="Không có hình ảnh sản phẩm" class="hover:grow hover:shadow-lg  w-full h-full object-cover">
                        @endif
                    </div>
                    <p class="pt-3 text-gray-900 font-medium text-xs md:text-sm uppercase truncate" title="{{$product->name}}">
                        {{ $product->name ? $product->name : '-'}}
                    </p>
                    <p class="pt-1 text-gray-900 font-medium text-xs uppercase">{{$product->code}}</p>
                    <p class="text-gray-900 font-medium">
                        @if(Auth::check())
                            <span class="text-xs md:text-sm text-green-500">{{number_format($product->retail_price)}} VNĐ</span>
                        @else
                            <span class="text-xs text-red-500">Đăng nhập để xem giá</span>
                        @endif
                    </p>
                </a>
            </div>
        @endforeach
    </div>
    <div class="w-full bg-white">
        {{$products->links('livewire.custom-pagination')}}
    </div>
</div>