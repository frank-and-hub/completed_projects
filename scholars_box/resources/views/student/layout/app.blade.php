<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Login - Scholarsbox')</title>
    <meta name="keywords" content="Scholarsbox">
    <meta name="description" content="Scholarsbox">
    <meta name="csrf-token" content="{{ csrf_token() }}" >
    <link rel="icon" href="https://scholarsbox.in/images/favicon.png" type="image/gif" sizes="16x16">
    <!--<script type="text/javascript" src="https://platform-api.sharethis.com/js/sharethis.js#property=65d56ec43625b4001a8bd15a&product=image-share-buttons&source=platform" async="async"></script>-->
    @include('student.includes.styles')
    
    
    <!-- Third-party Stylesheets -->
    @stack('third_party_stylesheets')

    <!-- Page-specific CSS -->
    @stack('page_css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

</head >

<body>
    <!-- Include header -->
    @include('student.includes.header')

    <div class="content-wrapper">
        <!-- Page content goes here -->
        @yield('content')
        
        
    </div>

    <!-- Third-party Scripts -->
    @stack('third_party_scripts')

    <!-- Page-specific Scripts -->
    @stack('page_scripts')
    
    <!-- Include footer -->
    @include('student.includes.footer')

    <!-- Include additional scripts -->
    @include('student.includes.scripts')
    
 

</body>

</html>
