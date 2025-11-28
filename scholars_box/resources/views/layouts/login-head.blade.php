<!DOCTYPE html>
<html>

<head>
    @include('layouts.includes.head')
    @include('layouts.includes.style')
</head>

<body class="hold-transition sidebar-mini layout-fixed">
    
    <div id="loader" class="loader-wrapper">
        <div class="loader"></div>
    </div>


    <div class="wrapper">
        <!-- Content Wrapper. Contains page content -->
        <div>
            @yield('content')
        </div>
    </div>
</body>

@include('layouts.includes.script')

</html>
