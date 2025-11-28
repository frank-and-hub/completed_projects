@php
    $auth = Auth::user();
@endphp
<nav class="navbar col-lg-12 col-12 p-0 fixed-top d-flex flex-row default-layout-navbar">
    <div class="text-center navbar-brand-wrapper d-flex align-items-center justify-content-center">
        <a class="navbar-brand brand-logo" href="{{ route('dashboard') }}"><img
                src="{{ asset('assets/admin/images/logo.png') }}" alt="logo" /></a>
        <a class="navbar-brand brand-logo-mini" href="{{ route('dashboard') }}"><img
                src="{{ asset('assets/admin/images/logo-mini.png') }}" alt="logo" /></a>
    </div>
    <div class="navbar-menu-wrapper d-flex align-items-stretch">
        <button class="navbar-toggler navbar-toggler align-self-center" type="button" data-toggle="minimize">
            <span class="fas fa-bars"></span><span class="ml-5"><b>{{ $title }}</b></span>
        </button>

        <ul class="navbar-nav navbar-nav-right">
            {{-- <li class="nav-item nav-profile dropdown d-flex align-items-center">
                <div class="stat-label timezone_time" style="overflow:hidden;" ></div>
            </li> --}}
            <li class="nav-item nav-profile dropdown d-flex align-items-center">
                <!-- Profile Image and Name -->
                <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" data-toggle="dropdown"
                    id="profileDropdown">
                    <!--{{ ($auth->image()->first()?->path) }} -->
                    <img src="{{ $auth->image()->first()?->path && Storage::exists($auth->image()->first()?->path)
                        ? Storage::url($auth->image()->first()?->path)
                        : asset('assets/default_user.png') }}"
                        alt="profile" class="rounded-circle mr-2"
                        style="width: 40px; height: 40px; object-fit: cover;">
                    <div class="d-inline-block text-left">
                        <h6 class="mb-0 p-0 font-weight-bold text-dark" style="margin-bottom: -10px !important;">{{ ucwords($auth->name) }}</h6>
                        <small class="text-muted" style="">{{ $auth->designation() }}</small>
                    </div>
                </a>

                <!-- Dropdown Menu -->
                <div class="dropdown-menu dropdown-menu-right navbar-dropdown" aria-labelledby="profileDropdown">
                    <div class="dropdown-divider"></div>
                    <a href="{{ route('logout') }}" class="dropdown-item">
                        <i class="fas fa-power-off text-primary mr-2"></i> Logout
                    </a>
                </div>
            </li>
        </ul>

        <button class="navbar-toggler navbar-toggler-right d-lg-none align-self-center" type="button"
            data-toggle="offcanvas">
            <span class="fas fa-bars"></span>
        </button>
    </div>
</nav>
