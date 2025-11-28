<li class="nav-item {{ request()->routeIs('Admin.dashboard*') ? 'menu-open' : '' }}">
    <a class="nav-link {{ request()->routeIs('Admin.dashboard*') ? 'active' : '' }}"
        href="{{ route('Admin.dashboard') }}">
        <i class="fas fa-fw fa-tachometer-alt"></i>
        <p>Dashboard</p>
    </a>
</li>

<li class="nav-item {{ request()->routeIs('Admin.scholarship') ? 'menu-open' : '' }}">
    <a href="{{ route('Admin.scholarship.index') }}"
        class="nav-link {{ request()->routeIs('Admin.scholarship.index') ? 'active' : '' }}">
        <i class="fas fa-fw fa-clipboard"></i>
        <p>Scholarships</p>
    </a>
</li>

<li class="nav-item {{ request()->routeIs('Admin.scholarshipApplication*') ? 'menu-open' : '' }}">
    <a href="{{ route('Admin.scholarshipApplication.index') }}"
        class="nav-link {{ request()->routeIs('Admin.scholarshipApplication.index') ? 'active' : '' }}">
        <i class="fas fa-fw  fa-address-card"></i>
        <p>Student Application</p>
    </a>
</li>

<li class="nav-item">
    <a href="#" class="nav-link"
        onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
        <i class="fas fa-sign-out-alt"></i>
        <p>Logout</p>
    </a>
</li>
