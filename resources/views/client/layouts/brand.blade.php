@if(count($brands) > 0)
    <div class="w-full flex flex-wrap items-center justify-between px-2 md:px-4 py-3 bg-red-600 text-sm text-white">
        <div class="flex flex-wrap items-center justify-start gap-2 md:text-lg">
            <span>
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4 md:size-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" />
                </svg>
            </span>
            <h2 class="font-bold">NHÃN HÀNG</h2>
        </div>
    </div>
    <div class="my-brand py-4 md:py-6 px-2 md:px-4 relative">
        @foreach($brands as $brand)
            <div class="item-brand text-center">
                <div class="mb-2 size-12 lg:size-16 mx-auto overflow-hidden">
                    <a href="{{route('brand',['slug'=>$brand->slug])}}">
                        @if ($brand->logo)
                            <img src="{{ asset('storage/images/brands/' . $brand->logo) }}" alt="Brand Logo" class="w-full h-full object-cover lazyload">
                        @else
                            <img src="{{ asset('library/images/image-not-found.jpg') }}" alt="Brand Logo" class="w-full h-full lazyload">
                        @endif
                    </a>
                </div>
                <p class="text-gray-900 font-medium text-xs md:text-sm uppercase"><a href="{{route('brand',['slug'=>$brand->slug])}}">{{$brand->name}}</a></p>
            </div>
        @endforeach
    </div>
@endif