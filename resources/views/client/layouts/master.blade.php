<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
		<title>@yield('title')</title>
        <link href="https://fonts.googleapis.com/css?family=Work+Sans:200,400&display=swap" rel="stylesheet">
		<link rel="stylesheet" href="{{asset('library/css/style.css')}}">
		@vite(['resources/css/app.css','resources/js/app.js'])
		@livewireStyles
		<style>
            @import url('https://fonts.googleapis.com/css2?family=Oregano:ital@0;1&display=swap');
            .work-sans {
                font-family: 'Work Sans', sans-serif;
            }
                    
            #menu-toggle:checked + #menu {
                display: block;
            }
            
            .hover\:grow {
                transition: all 0.3s;
                transform: scale(1);
            }
            
            .hover\:grow:hover {
                transform: scale(1.02);
            }
            
            .carousel-open:checked + .carousel-item {
                position: static;
                opacity: 100;
            }
            
            .carousel-item {
                -webkit-transition: opacity 0.6s ease-out;
                transition: opacity 0.6s ease-out;
            }
            
            #carousel-1:checked ~ .control-1,
            #carousel-2:checked ~ .control-2,
            #carousel-3:checked ~ .control-3 {
                display: block;
            }
            
            .carousel-indicators {
                list-style: none;
                margin: 0;
                padding: 0;
                position: absolute;
                bottom: 2%;
                left: 0;
                right: 0;
                text-align: center;
                z-index: 10;
            }
            
            #carousel-1:checked ~ .control-1 ~ .carousel-indicators li:nth-child(1) .carousel-bullet,
            #carousel-2:checked ~ .control-2 ~ .carousel-indicators li:nth-child(2) .carousel-bullet,
            #carousel-3:checked ~ .control-3 ~ .carousel-indicators li:nth-child(3) .carousel-bullet {
                color: #000;
                /*Set to match the Tailwind colour you want the active one to be */
            }
            #scroll-to-top{
                position: fixed;
                bottom: 20px;
                right: 20px;
                background-color: #f91919;
                color: #fff;
                border-radius: 10px;
                display: none;
                cursor: pointer;
                padding: 10px;
                z-index: 99;
            }
            .my-slider{
                position: relative;
            }
            .my-slider .slick-arrow{
                position: absolute;
                padding: 10px;
                top: 50%;
                transform: translateY(-50%);
                text-align: center;
                color: #fff;
                background-color: #f91919;
                z-index: 2;
                cursor: pointer;
                border-radius: 50%;
            }
            .my-slider .slick-prev{
                left: 15px;
            }
            .my-slider .slick-next{
                right: 15px;
            }
        </style>
        <link rel="stylesheet" type="text/css" href="//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css"/>
	</head>
	<body class="bg-gray-300 font-sans leading-normal text-base tracking-normal flex flex-col min-h-screen">
        @include('client.layouts.menu')
        <div class="w-full mx-auto max-w-full md:max-w-7xl md:px-4 lg:px-8 flex flex-col flex-1">
            <main class="flex-1 bg-white">
                @yield('content')
            </main>
            @include('client.layouts.footer')
            @livewire('wire-elements-modal')
        </div>
        <div id="scroll-to-top">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 10.5 12 3m0 0 7.5 7.5M12 3v18" />
            </svg>
        </div>
    </body>
    @livewireScripts
    <script>
        function updateCartNumber(number){
            const cart_count = document.getElementById("cart-count")
            if(cart_count){
                cart_count.innerText = number
            }
        }
    </script>
	<script src="{{asset('library/js/app.js')}}"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script type="text/javascript" src="//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js"></script>
    <script>
        $(document).ready(function () {
            $(window).scroll(function () {
                if ($(this).scrollTop() > 130) {
                    $('#scroll-to-top').fadeIn();
                } else {
                    $('#scroll-to-top').fadeOut();
                }
            });

            $('#scroll-to-top').click(function () {
                $('html, body').animate({
                    scrollTop: 0
                }, 500);
                return false;
            });

             $('.my-slider').slick({
                slidesToShow: 1,
                slidesToScroll: 1,
                nextArrow:
                    '<div class="slick-arrow slick-next"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-3 lg:size-5"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" /></svg></div>',
                prevArrow:
                    '<div class="slick-arrow slick-prev"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-3 lg:size-5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" /></svg></div>',
                autoplay: true,
                arrows: true,
                autoplaySpeed: 3000
            });
        });
    </script>
</html>