<header class="relative bg-white w-full shadow-md">
    <div class="bg-red-600 text-white text-center py-3 sm:hidden">
        <p class="text-base font-bold">Hotline: <a href="https://zalo.me/0965518457" target="_blank">0965518457</a> (Zalo)</p>
    </div>
    <div class="mx-auto max-w-full md:max-w-7xl px-2 lg:px-8">
        <div class="w-full flex flex-wrap lg:flex-nowrap justify-between items-center py-2">
            <!-- Logo -->
            <div class="flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6 md:hidden text-white">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                </svg>
                <a href="{{ route('index') }}">
                    <span class="sr-only">Your Company</span>
                    @if ($system_info->logo)
                        <img src="{{ asset('storage/images/systems/' . $system_info->logo) }}"class="h-16 w-auto">
                    @else
                        <img src="{{ asset('library/images/image-not-found.jpg') }}" class="h-16 w-auto">
                    @endif
                </a>
            </div>
            <form action="{{ route('spotlight.search') }}" class="w-full order-3 mt-2 lg:order-2 lg:w-auto lg:mt-0">
                @livewire('client.search')
            </form>
            <div class="flex items-center gap-4 order-2 lg:order-3">
                <a href="tel:0965518457" class="hidden md:flex items-center gap-2 text-white text-sm rounded-lg px-3 h-12 bg-red-600 hover:bg-red-700 transition-colors">
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
                            <p class="text-center">
                                {{Auth::user()->name}}
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
                        <p class="text-center">
                            Tài khoản
                        </p>
                    </a>
                @endif
            </div>
        </div>
    </div>
</header>