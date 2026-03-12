@extends('client.layouts.master')

@section('title', 'Trang chủ')

@section('content')
    @include('client.slider')
    @include('client.layouts.brand')
    <section class="bg-white">
        <div class="container mx-auto flex items-center flex-wrap pt-4">
            <div class="w-full flex flex-wrap items-center justify-between px-2 md:px-4 py-3 bg-red-600 text-sm text-white">
                <div class="flex flex-wrap items-center justify-start gap-2 md:text-lg">
                    <span>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4 md:size-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" />
                        </svg>
                    </span>
                    <h2 class="font-bold">SẢN PHẨM MỚI</h2>
                </div>
                <a href="" class="flex flex-wrap items-center justify-end hover:text-gray-300">
                    <span>Xem tất cả</span>
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m5.25 4.5 7.5 7.5-7.5 7.5m6-15 7.5 7.5-7.5 7.5" />
                    </svg>
                </a>
            </div>
            @foreach ($new_products as $product)
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
    </section>
    @if($best_seller_products->count() > 0)
        <section class="bg-white">
            <div class="container mx-auto flex items-center flex-wrap">
                <div class="w-full flex flex-wrap items-center justify-between px-2 md:px-4 py-3 bg-red-600 text-sm text-white">
                    <div class="flex flex-wrap items-center justify-start gap-2 md:text-lg">
                        <span>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4 md:size-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" />
                            </svg>
                        </span>
                        <h2 class="font-bold">SẢN PHẨM BÁN CHẠY</h2>
                    </div>
                    <a href="" class="flex flex-wrap items-center justify-end hover:text-gray-300">
                        <span>Xem tất cả</span>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m5.25 4.5 7.5 7.5-7.5 7.5m6-15 7.5 7.5-7.5 7.5" />
                        </svg>
                    </a>
                </div>
                @foreach ($best_seller_products as $product)
                    <div class="w-1/2 lg:w-1/3 xl:w-1/4 p-2 md:p-4">
                        <a href="{{route('product-detail', ['id' => $product->product->id, 'slug' => $product->product->slug])}}">
                            <div class="w-full aspect-w-1 aspect-h-1 overflow-hidden">
                                @if (count($product->product->productDetails) > 0 && $product->product->productDetails[0] && $product->product->productDetails[0]->image)
                                    @php
                                        $imageThumbnailCheck = json_decode($product->product->productDetails[0]->image);   
                                        $imageThumbnail = $imageThumbnailCheck ? $imageThumbnailCheck[0] : $product->product->productDetails[0]->image;
                                    @endphp
                                    <img src="{{ asset('storage/images/products/' . $imageThumbnail) }}" alt="Hình ảnh sản phẩm" class="hover:grow hover:shadow-lg w-full h-full object-cover">
                                @else
                                    <img src="{{ asset('library/images/image-not-found.jpg') }}" alt="Không có hình ảnh sản phẩm" class="hover:grow hover:shadow-lg  w-full h-full object-cover">
                                @endif
                            </div>
                            <p class="pt-3 text-gray-900 font-medium text-xs md:text-sm uppercase truncate" title="{{$product->product->name}}">
                                 {{ $product->product->name ? $product->product->name : '-'}}
                            </p>
                            <p class="pt-1 text-gray-900 font-medium text-xs uppercase">{{$product->product->code}}</p>
                            <p class="text-gray-900 font-medium">
                                @if(Auth::check())
                                    <span class="text-xs md:text-sm text-green-500">{{number_format($product->product->retail_price)}} VNĐ</span>
                                @else
                                    <span class="text-xs text-red-500">Đăng nhập để xem giá</span>
                                @endif
                            </p>
                        </a>
                    </div>
                @endforeach
            </div>
        </section>
    @endif
@endsection