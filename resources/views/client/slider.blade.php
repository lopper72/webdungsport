<div class="w-full bg-white py-4 md:py-6 px-2 md:px-4 flex justify-between gap-4">
    <div class="w-full lg:w-1/5 hidden lg:block">
        <div class="flex items-center gap-2 mb-4 text-red-500 border-b py-2">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
            </svg>
            <p class="text-sm font-bold">DANH MỤC SẢN PHẨM</p>
        </div>
        <div class="w-full">
            @foreach($categories as $category)
                <a href="{{route('collection',['slug'=>$category->slug])}}" class="flex items-center gap-2 p-1 hover:bg-gray-200 hover:text-red-600 text-gray-900">
                    <div class="size-8 rounded-full overflow-hidden">
                        @if ($category->image)
                            <img src="{{ asset('storage/images/categories/' . $category->image) }}" alt="{{$category->name}}" class="w-full h-full object-cover lazyload">
                        @else
                            <img src="{{ asset('library/images/image-not-found.jpg') }}" alt="Category Logo" class="w-full h-full object-cover lazyload">
                        @endif
                    </div>
                    <p class="text-xs uppercase">{{$category->name}}</p>
                </a>
            @endforeach
        </div>
    </div>
    <div class='relative w-full lg:w-4/5'>
        <div class="my-slider relative">
            @if (count($slides) == 0)
                <div class="item-slider">
                    <img src="{{ asset('library/images/slider.jpg') }}" alt="Slide Image" class="w-full h-[250px] md:h-[450px] object-cover lazyload">
                </div>
            @else
                @foreach($slides as $slide)
                    <div class="item-slider">
                        <img src="{{ asset('storage/images/slides/' . $slide->image) }}" alt="{{$slide->title}}" class="w-full h-[250px] md:h-[450px] object-cover lazyload">
                    </div>
                @endforeach
            @endif
        </div>
    </div>
</div>
