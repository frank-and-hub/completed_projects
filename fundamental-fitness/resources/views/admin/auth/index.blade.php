<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }} @yield('admin-title')</title>
    <link href="{{ asset('assets/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/style.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/responsive.css') }}" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/images/fav-icon.png') }}">
    {{-- TOASTR --}}
    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet">
</head>

<body>
    @yield('content')

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js"></script>
    @stack('scripts')
    <script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}"></script>

    {{-- TOASTR --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    <script>
        @if (session('flash-error'))
            toastr.error("{{ session('flash-error') }}");
        @endif

        @if (session('flash-success'))
            toastr.success("{{ session('flash-success') }}");
        @endif

        @if (session('status'))
            toastr.success("{{ session('status') }}");
        @endif

        @if (session('message'))
            toastr.error("{{ session('message') }}");
        @endif


    </script>
</body>
</html>
