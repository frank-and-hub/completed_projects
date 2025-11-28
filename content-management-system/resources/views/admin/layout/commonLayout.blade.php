@extends('admin/layout/commanLayoutMaster')

@php
    /* Display elements */
    $contentNavbar = true;
    $containerNav = $containerNav ?? 'container-xxl';
    $isNavbar = $isNavbar ?? true;
    $isMenu = $isMenu ?? true;
    $isFlex = $isFlex ?? false;
    $isFooter = $isFooter ?? true;
    $customizerHidden = $customizerHidden ?? '';
    $pricingModal = $pricingModal ?? false;

    /* HTML Classes */
    $navbarDetached = 'navbar-detached';

    /* Content classes */
    $container = $container ?? 'container-xxl';

@endphp

@section('layoutContent')
    <div class="layout-wrapper layout-content-navbar {{ $isMenu ? '' : 'layout-without-menu' }}">
        <div class="layout-container">

            {{-- @if ($isMenu)
    @include('layouts/sections/menu/verticalMenu')
    @endif --}}


            <!-- Layout page -->
            <div class="layout-page">
                <!-- BEGIN: Navbar-->
                {{-- @if ($isNavbar)
      @include('layouts/sections/navbar/navbar')
      @endif --}}
                <!-- END: Navbar-->


                <!-- Content wrapper -->
                <div class="content-wrapper">

                    <!-- Content -->
                    @if ($isFlex)
                        <div class="{{ $container }} d-flex align-items-stretch flex-grow-1 p-0">
                        @else
                            <div class="{{ $container }} flex-grow-1 container-p-y">
                    @endif
                    <div class="bs-toast toast toast-placement-ex m-2" role="alert" aria-live="assertive" aria-atomic="true"
                    style="position: fixed; top: 0; right: 0;"
                        data-delay="2000">
                        <div class="toast-header">
                            <i class='bx bx-bell me-2'></i>
                            <div class="me-auto fw-semibold" id="header-toast">fdgdfh</div>
                            <small></small>
                            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                        </div>
                        <div class="toast-body" id="toast-body">
                            Hello
                        </div>
                    </div>
                    @yield('content')

                    <!-- pricingModal -->
                    {{-- @if ($pricingModal)
            @include('_partials/_modals/modal-pricing')
            @endif --}}
                    <!--/ pricingModal -->

                </div>
                <!-- / Content -->

                <!-- Footer -->
                {{-- @if ($isFooter)
          @include('admin/ins/footer')
          @endif --}}
                <!-- / Footer -->
                <div class="content-backdrop fade"></div>
            </div>
            <!--/ Content wrapper -->
        </div>
        <!-- / Layout page -->
    </div>

    @if ($isMenu)
        <!-- Overlay -->
        <div class="layout-overlay layout-menu-toggle"></div>
    @endif
    <!-- Drag Target Area To SlideIn Menu On Small Screens -->
    <div class="drag-target"></div>
    </div>
    <!-- / Layout wrapper -->
@endsection
