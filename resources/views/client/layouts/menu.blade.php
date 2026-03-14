<header class="relative bg-white w-full shadow-md">
    <div class="bg-red-600 text-white text-center py-3 lg:hidden">
        <p class="text-base font-bold">Hotline: <a href="https://zalo.me/0965518457" target="_blank">0965518457</a> (Zalo)</p>
    </div>
    <div class="mx-auto max-w-full md:max-w-7xl px-2 lg:px-8">
        <div class="w-full flex flex-wrap lg:flex-nowrap justify-between items-center py-2">
            <a id="showMenu" href="javascript:void(0)" class="lg:hidden text-red-600">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                </svg>
            </a>
            <a href="{{ route('index') }}">
                <span class="sr-only">Your Company</span>
                @if ($system_info->logo)
                    <img src="{{ asset('storage/images/systems/' . $system_info->logo) }}"class="h-16 w-auto">
                @else
                    <img src="{{ asset('library/images/image-not-found.jpg') }}" class="h-16 w-auto">
                @endif
            </a>
            <form action="{{ route('spotlight.search') }}" class="w-full order-4 mt-2 lg:order-3 lg:w-auto lg:mt-0">
                @livewire('client.search')
            </form>
            <div class="flex items-center gap-4 order-3 lg:order-4">
                <a href="tel:0965518457" class="hidden lg:flex items-center gap-2 text-white text-sm rounded-lg px-3 h-12 bg-red-600 hover:bg-red-700 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z" />
                    </svg>
                    <p class="text-center">
                        Gọi cho chúng tôi <br>
                        0965518457
                    </p>
                </a>
                
                @if(Auth::check())
                    <div class="relative" x-data="{ isOpenProfile: false }">
                        <button @click="isOpenProfile = !isOpenProfile" aria-expanded="false" type="button" class="flex items-center gap-2 text-white text-sm rounded-lg px-3 h-12 bg-red-600 hover:bg-red-700 transition-colors" id="user-menu">
                            @if(Auth::user()->avatar_user)
                                <img class="h-8 w-8 rounded-full object-cover" src="{{ asset('storage/images/users/' . Auth::user()->avatar_user) }}" alt="{{Auth::user()->name}}">
                            @else
                                <img class="h-8 w-8 rounded-full" src="{{ asset('library/images/user/user-01.png') }}" alt="User Avatar">
                            @endif
                            <p class="text-center hidden md:block">
                                {{substr(Auth::user()->name, 0, 10)}}
                            </p>
                        </button>
                        <div x-show="isOpenProfile" aria-hidden="true" class="z-50 origin-top-right absolute right-0 mt-2 w-48 rounded-sm shadow-lg bg-white ring-1 ring-gray-300 ring-opacity-5" role="menu">
                            <div class="py-1" role="none">
                                <a href="{{ route('info_user') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900" role="menuitem">Thông tin tài khoản </a>
                                <a href="{{ route('order_summaries') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900" role="menuitem">Thông tin đơn hàng</a>
                                <a href="{{ route('change_password') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900" role="menuitem">Đổi mật khẩu</a>
                                <a href="{{ route('logout') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900" role="menuitem">Đăng xuất</a>
                            </div>
                        </div>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="flex items-center gap-2 text-white text-sm rounded-lg px-3 h-12 bg-red-600 hover:bg-red-700 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0 0 12 15.75a7.488 7.488 0 0 0-5.982 2.975m11.963 0a9 9 0 1 0-11.963 0m11.963 0A8.966 8.966 0 0 1 12 21a8.966 8.966 0 0 1-5.982-2.275M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                        </svg>
                        <p class="text-center hidden md:block">
                            Tài khoản
                        </p>
                    </a>
                @endif
            </div>
        </div>
    </div>
</header>
<div id="menuMobi" class="absolute top-0 left-0 w-4/5 bg-white shadow-lg z-40 h-full ease-in-out duration-300 -translate-x-full lg:hidden">
    <div class="flex justify-end w-full text-red-600 pr-2 pt-1">
        <svg id="closeMenu" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-8">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
        </svg>
    </div>
    <div class="pb-4 flex justify-center">
        <a href="{{ route('index') }}">
            @if ($system_info->logo)
                <img src="{{ asset('storage/images/systems/' . $system_info->logo) }}"class="h-16 w-auto">
            @else
                <img src="{{ asset('library/images/image-not-found.jpg') }}" class="h-16 w-auto">
            @endif
        </a>
    </div>
    <div class="px-4">
        @foreach($categories as $category)
            <a href="{{route('collection',['slug'=>$category->slug])}}" class="flex items-center gap-2 p-2 text-gray-900 border-b">
                <div class="size-8 rounded-full overflow-hidden">
                    @if ($category->image)
                        <img src="{{ asset('storage/images/categories/' . $category->image) }}" alt="{{$category->name}}" class="w-full h-full object-cover">
                    @else
                        <img src="{{ asset('library/images/image-not-found.jpg') }}" alt="Category Logo" class="w-full h-full object-cover">
                    @endif
                </div>
                <p class="text-xs uppercase">{{$category->name}}</p>
            </a>
        @endforeach
    </div>
</div>