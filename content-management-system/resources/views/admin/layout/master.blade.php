@extends('admin.layout.base')

@section('master')
    <!-- [ Header ] start -->

    <!-- [ navigation menu ] end -->

    <!-- [ Side Bar ] start -->
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            @include('admin.includes.sidebar')
            <div class="layout-page" style="background: #f2f2f2;">
                @include('admin.includes.header')
                <!-- [ Side Bar ] end -->

                <!-- [ Main Content ] start -->
                <div class="content-wrapper">
                    <div class="container-l flex-grow-1 container-p-y mx-5">

                        @yield('content')

                        <!-- [ Footer ] start -->
                        @include('admin.includes.footer')
                    </div>
                </div>
            </div>

        </div>

    </div>

    <!-- [ Footer ] end -->
    <!-- [ Main Content ] end -->
    {{-- @include('admin.profile.change-password') --}}
@endsection
