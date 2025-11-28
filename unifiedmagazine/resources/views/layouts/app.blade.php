<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="base-url" content="{{ url('/') }}">
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Scripts -->
    @guest
    <script src="{{ asset('js/app.js') }}" defer></script>
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    @else
    @include('layouts.css')
        <script src="{{ asset('js/coustom.js') }}" defer></script>
        <style src="{{ asset('css/coustom.css') }}"></style>
    @endguest
    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">

</head>
<body>
    <div class="overlay" id="overlay"></div>
    <div class="loader" id="loader"></div>
        @guest
                @yield('content')
        @else
            <header id="header" class="header fixed-top d-flex align-items-center">
                <div class="d-flex align-items-center justify-content-between">
                    <a href="index.html" class="logo d-flex align-items-center">
                        <img src="{{asset('img/logo.png')}}" alt="">
                        <span class="d-none d-lg-block">{{ config('app.name', 'Laravel') }}</span>
                    </a>
                    <i class="bi bi-list toggle-sidebar-btn"></i>
                </div>
                @include('layouts.nav')
            </header>
            @include('layouts.sidebar')
            <main class="py-4 main" id="main">
                <div class="pagetitle">
                    @include('layouts.alert')
                    <nav>
                        <ol class="breadcrumb">
                            @php $newUrl = url()->current(); @endphp
                            @if(collect(explode('/', $newUrl))->last() != 'home')
                                <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                            @endif
                            <li class="breadcrumb-item active"><a href="{{ collect(explode('/', $newUrl))->last() }}">{{ ucwords(collect(explode('/', $newUrl))->last()) }}</a></li>
                        </ol>
                    </nav>
                </div>
                <section class="section dashboard">
                    @yield('content')
                </section>
            </main>
        @endguest
</body>
@guest
@else
@include('layouts.js')
@endguest
</html>
