@php
    $path = auth()->guard('admin')->user()->image()->first()?->path ?: '';

    if (auth()->guard('admin')->user()->hasRole('admin')) {
        $siebar_profile_path = asset('assets/admin/images/logo-mini.png');
    } elseif ($path) {
        $siebar_profile_path = Storage::url($path);
    } else {
        $siebar_profile_path = asset('assets/default_user.png');
    }
@endphp
<nav class="sidebar sidebar-offcanvas" id="sidebar">
    <ul class="nav">
        @if (auth()->guard('admin')->user()->hasRole('admin'))
            <li class="nav-item dashboard">
                <a class="nav-link" href="{{ route('dashboard') }}">
                    <i class="fa fa-home menu-icon"></i>
                    <span class="menu-title">Dashboard</span>
                </a>
            </li>
            <li class="nav-item users">
                <a href="{{ route('user_list') }}" class="nav-link">
                    <i class="icon-sm fa fa-users menu-icon"></i>
                    <span class="menu-title">Tenants</span>
                </a>
            </li>
            <li class="nav-item agencies">
                <a href="{{ route('admin_user.role_type_admin_list', 'agency') }}" class="nav-link">
                    <i class="fa fa-user-group menu-icon"></i>
                    <span class="menu-title">Agencies</span>
                </a>
            </li>
            <li class="nav-item agent">
                <a href="{{ route('admin_user.role_type_admin_list', 'agent') }}" class="nav-link">
                    <i class="icon-sm fa fa-user-tie menu-icon"></i>
                    <span class="menu-title">Agents</span>
                </a>
            </li>
            <li class="nav-item privatelandlord">
                <a href="{{ route('admin_user.role_type_admin_list', 'privatelandlord') }}" class="nav-link">
                    <i class="icon-sm fa fa-user menu-icon"></i>
                    <span class="menu-title">Private Landlords</span>
                </a>
            </li>
            <li class="nav-item property">
                <a href="{{ route('property.list') }}" class="nav-link">
                    <i class="fa-solid fa-map-location-dot menu-icon"></i>
                    <span class="menu-title">Properties</span>
                </a>
            </li>
            <li class="nav-item property">
                <a href="{{ route('api_property.list') }}" class="nav-link">
                    <i class="icon-sm fa fa-street-view menu-icon"></i>
                    <span class="menu-title">Partner Properties</span>
                </a>
            </li>
            <li class="nav-item enquiry">
                <a href="{{ route('enquiry_list') }}" class="nav-link">
                    <i class="icon-sm fa fa-hand-point-up menu-icon"></i>
                    <span class="menu-title">Web Form Enquiries</span>
                </a>
            </li>
            <li class="nav-item Calendar">
                <a class="nav-link" href="{{ route('calendar.index') }}">
                    <i class="fa fa-calendar-plus menu-icon"></i>
                    <span class="menu-title">Calendar</span>
                </a>
            </li>
            {{-- <li class="nav-item features">
                <a href="{{ route('features_list') }}" class="nav-link">
                    <i class="icon-sm fa fa-align-center menu-icon"></i>
                    <span class="menu-title">Features</span>
                </a>
            </li> --}}
            <li class="nav-item Plans">
                <a href="{{ route('plan_list') }}" class="nav-link">
                    <i class="icon-sm fa fa-list-ol menu-icon"></i>
                    <span class="menu-title">Plans</span>
                </a>
            </li>
            <li class="nav-item Submitted Property Needs">
                <a href="{{ route('submitted_property') }}" class="nav-link">
                    <i class="icon-sm fa fa-hand menu-icon"></i>
                    <span class="menu-title">Submitted Property Needs</span>
                </a>
            </li>
            <li class="nav-item proprty_api">
                <a href="{{ route('external_property_users.index') }}" class="nav-link">
                    <i class="icon-sm fa fa-globe menu-icon"></i>
                    <span class="menu-title">Property API</span>
                </a>
            </li>
            <li class="nav-item property_need_api">
                <a href="{{ route('property-need-api-user.index') }}" class="nav-link">
                    <i class="icon-sm fa fa-code menu-icon"></i>
                    <span class="menu-title">Property Need API</span>
                </a>
            </li>
            <li class="nav-item contract_records @if (($active_page ?? 'active') === 'records') active @endif">
                <a href="{{ route('contract_records.index') }}" class="nav-link">
                    <i class="icon-sm fa fa-book menu-icon"></i>
                    <span class="menu-title">Contract Records</span>
                </a>
            </li>
            <li class="nav-item setting">
                <a href="{{ route('setting') }}" class="nav-link">
                    <i class="icon-sm fa fa-cogs menu-icon"></i>
                    <span class="menu-title">Settings</span>
                </a>
            </li>
        @else
            <li class="nav-item dashboard @if (($active_page ?? '') === 'dashboard') active @endif">
                <a class="nav-link" href="{{ route('adminSubUser.dashboard') }}">
                    {{-- <img src="{{ asset('assets/admin/images/dashboard.png') }}" class="sidebar-icon" /> --}}
                    <i class="fa fa-home menu-icon"></i>
                    <span class="menu-title">Dashboard</span>
                </a>
            </li>
            <li class="nav-item property @if (($active_page ?? '') === 'property') active @endif">
                <a class="nav-link" href="{{ route('adminSubUser.property.index') }}">
                    {{-- <img src="{{ asset('assets/admin/images/property.png') }}" class="sidebar-icon" /> --}}
                    <i class="fa fa-building menu-icon"></i>
                    <span class="menu-title">Properties</span>
                </a>
            </li>
            <li class="nav-item match-property @if (($active_page ?? '') === 'match-property') active @endif">
                <a class="nav-link" href="{{ route('adminSubUser.match-property.index') }}">
                    {{-- <img src="{{ asset('assets/admin/images/matchedproperty.png') }}" class="sidebar-icon" /> --}}
                    <i class="fa fa-users menu-icon"></i>
                    <span class="menu-title">Matched Tenants</span>
                </a>
            </li>
            <li class="nav-item {{ request()->routeIs('adminSubUser.calendar.pvr_index') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('adminSubUser.calendar.pvr_index') }}">

                    {{-- @if (request()->routeIs('adminSubUser.calendar.index'))
                        <img src="{{ asset('assets/admin/images/calander.png') }}" class="sidebar-icon" />
                    @else
                        <img src="{{ asset('assets/admin/images/calander1.png') }}" class="sidebar-icon" />
                    @endif --}}

                    <i class="fa fa-calendar-minus menu-icon"></i>
                    <span class="menu-title">Property Viewing Request</span>
                </a>
            </li>
            <li class="nav-item Calendar {{ request()->routeIs('adminSubUser.calendar.index') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('adminSubUser.calendar.index') }}">
                    {{-- @if (request()->routeIs('adminSubUser.calendar.index'))
                        <img src="{{ asset('assets/admin/images/calander.png') }}" class="sidebar-icon" />
                    @else
                        <img src="{{ asset('assets/admin/images/calander1.png') }}" class="sidebar-icon" />
                    @endif --}}
                    <i class="fa fa-calendar menu-icon"></i>
                    <span class="menu-title">Calendar</span>
                </a>
            </li>
            @if (auth()->guard('admin')->user()->hasRole('agency'))
                <li class="nav-item agency_agent @if (($active_page ?? '') === 'agency') active @endif">
                    <a class="nav-link" href="{{ route('adminSubUser.agent.index') }}">
                        {{-- <img src="{{ asset('assets/admin/images/agent.png') }}" class="sidebar-icon" /> --}}
                        <i class="fa fa-user-tie menu-icon"></i>
                        <span class="menu-title">Agents</span>
                    </a>
                </li>
            @endif
            <li class="nav-item contract_list @if (($active_page ?? '') === 'contract') active @endif ">
                <a href="{{ route('adminSubUser.contract.index') }}" class="nav-link">
                    {{-- <img src="{{ asset('assets/admin/images/contract1.png') }}" class="sidebar-icon" /> --}}
                    <i class="fa fa-file-pdf menu-icon"></i>
                    <span class="menu-title">Contracts </span>
                </a>
            </li>

            @if (auth()->guard('admin')->user()->hasRole('agency'))
                <li class="nav-item property_api @if (($active_page ?? '') === 'property-api') active @endif">
                    <a class="nav-link" href="{{ route('adminSubUser.external_property.index') }}">
                        <i class="icon-sm fa fa-globe menu-icon"></i>
                        <span class="menu-title">API Access</span>
                    </a>
                </li>
            @endif

            <li class="nav-item records_contracts @if (($active_page ?? '') === 'records') active @endif">
                <a href="{{ route('adminSubUser.contract_records.index') }}" class="nav-link">
                    {{-- <img src="{{ asset('assets/admin/images/reccords.png') }}" class="sidebar-icon" style="opacity: @if (($active_page ?? 'records') === 'records') 1 @else .4 @endif ; " /> --}}
                    <i class="icon-sm fa fa-book menu-icon"></i>
                    <span class="menu-title">Contract Records</span>
                </a>
            </li>
            @if (!auth()->guard('admin')->user()->hasRole('agent'))
                <li class="nav-item match-property @if (($active_page ?? '') === 'subscribe') active @endif">
                    <a class="nav-link" href="{{ route('adminSubUser.subscribe_list') }}">
                        {{-- <img src="{{ asset('assets/admin/images/subscription.png') }}" class="sidebar-icon" /> --}}
                        <i class="fa fa-list-ol menu-icon"></i>
                        <span class="menu-title">Subscription</span>
                    </a>
                </li>
            @endif
            <li class="nav-item setting @if (($active_page ?? '') === 'setting') active @endif">
                <a href="{{ route('adminSubUser.setting.index') }}" class="nav-link">
                    {{-- <img src="{{ asset('assets/admin/images/setting.png') }}" class="sidebar-icon" /> --}}
                    <i class="icon-sm fa fa-cogs menu-icon"></i>
                    <span class="menu-title">Settings</span>
                </a>
            </li>
        @endif
    </ul>
</nav>
