<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
    <div class="app-brand demo">
        <a href="{{ route('admin.dashboard') }}" class="app-brand-link">
            <span class="app-brand-logo demo">
                <img height="50px" width="50px" style="object-fit: contain;" src="{{ asset('images/logo1.png') }}">
            </span>
            <span class="app-brand-text demo menu-text fw-bolder ms-2 pt-3"> <img height="50px"
                    width="150px"src="{{ asset('images/park1.png') }}" style="object-fit: contain;"></span>
        </a>

        <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-block d-xl-none">
            Park Escape
        </a>
    </div>

    <div class="menu-inner-shadow"></div>

    <ul class="menu-inner py-1">
        <!-- Dashboard -->
        @can('dashboard-show')
            <li class="menu-item dashboard">
                <a href="{{ route('admin.dashboard') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-home-circle"></i>
                    <div data-i18n="Analytics">Dashboard</div>
                </a>
            </li>
        @endcan
        @can('users-show')
            <li class="menu-item user">
                <a href="{{ route('admin.user.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bxs-user"></i>
                    <div data-i18n="Analytics">Users</div>
                </a>
            </li>
        @endcan
        {{-- @can('users-show')
            <li class="menu-item delete_account">
                <a href="{{ route('admin.delete.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bxs-user"></i>
                    <div data-i18n="Analytics">Delete User Requests</div>
                </a>
            </li>
        @endcan --}}
        @can('show-sub-admins')
            <li class="menu-item subadmin">
                <a href="{{ route('admin.subadmin.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bxs-user-detail"></i>
                    <div data-i18n="Analytics">Admins</div>

                </a>
            </li>
        @endcan

        <li class="menu-item settings">
            <a href="{{ route('admin.settings') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-cog"></i>
                <div data-i18n="Analytics">Settings</div>
            </a>
        </li>


        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">Manage</span>
        </li>
        @can('park-show')
            <li class="menu-item park">
                <a href="{{ route('admin.park.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bxs-parking"></i>
                    <div data-i18n="Analytics">Parks</div>
                </a>
            </li>
        @endcan

        {{-- <li @class(['menu-item', 'category', 'open' => !empty($type)]) id="categories">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons  bx bx-category"></i>
                <div data-i18n="Invoice">Categories</div>
            </a>

            <ul class="menu-sub">
                <li @class([
                    'menu-item',
                    'active' => !empty($type) ? $type == 'all' : null,
                ])>

                    <a href="{{ route('admin.category.index', 'all') }}" class="menu-link">
                        <div data-i18n="All">All</div>
                    </a>
                </li>

                <li @class([
                    'menu-item',
                    'active' => !empty($type) ? $type == 'parent' : null,
                ])>
                    <a href="{{ route('admin.category.index', 'parent') }}" class="menu-link">
                        <div data-i18n="Preview">Parent</div>
                    </a>
                </li>


                <li @class([
                    'menu-item',
                    'active' => !empty($type) ? $type == 'no-child' : null,
                ])>
                    <a href="{{ route('admin.category.index', 'no-child') }}" class="menu-link">
                        <div data-i18n="Preview">Standalone</div>
                    </a>
                </li>

                <li @class([
                    'menu-item',
                    'active' => !empty($type)
                        ? $type == 'parent_special' || $type == 'standalone_special'|| $type == 'all_special'
                        : null,
                    'open' => !empty($type)
                        ? $type == 'parent_special' || $type == 'standalone_special'|| $type == 'all_special'
                        : null,
                ])>
                    <a href="javascript:void(0);" class="menu-link menu-toggle special-menu-item">
                        <i class="menu-icon tf-icons  bx bx-customize"></i>

                        <div data-i18n="Special Category">Special Category</div>
                    </a>
                    <ul class="menu-sub ml-3">
                        <li @class([
                            'menu-item',
                            'active' => !empty($type) ? $type == 'all_special' : null,
                        ])>
                            <a href="{{ route('admin.category.index', 'all_special') }}" class="menu-link">
                                <div data-i18n="Preview">All</div>
                            </a>
                        </li>

                        <li @class([
                            'menu-item',
                            'active' => !empty($type) ? $type == 'parent_special' : null,
                        ])>
                            <a href="{{ route('admin.category.index', 'parent_special') }}" class="menu-link">
                                <div data-i18n="Parent">Parent</div>
                            </a>
                        </li>

                        <li @class([
                            'menu-item',
                            'active' => !empty($type) ? $type == 'standalone_special' : null,
                        ])>
                            <a href="{{ route('admin.category.index', 'standalone_special') }}" class="menu-link">
                                <div data-i18n="Standalone">Standalone</div>
                            </a>
                        </li>
                    </ul>
                </li>


            </ul>

        </li> --}}

        @can('categories-show')
            <li class="menu-item category">
                <a href="{{ route('admin.category.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-category"></i>
                    <div data-i18n="Analytics">Categories</div>
                </a>
            </li>
        @endcan

        {{-- <li @class(['menu-item', 'feature_type', 'open' => !empty($feature_type)]) id="features">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons  bx bx-spreadsheet"></i>

                <div data-i18n="Featuers">Features</div>
            </a>

            <ul class="menu-sub">
                <li @class([
                    'menu-item',
                    'active' => !empty($feature_type) ? $feature_type == 'all' : null,
                ])>

                    <a href="{{ route('admin.feature_type.index', 'all') }}" class="menu-link">
                        <div data-i18n="All">All</div>
                    </a>
                </li>

                <li @class([
                    'menu-item',
                    'active' => !empty($feature_type) ? $feature_type == 'normal' : null,
                ])>

                    <a href="{{ route('admin.feature_type.index', 'normal') }}" class="menu-link">
                        <div data-i18n="Normal">Normal</div>
                    </a>
                </li>

                <li @class([
                    'menu-item',
                    'active' => !empty($feature_type) ? $feature_type == 'popular' : null,
                ])>

                    <a href="{{ route('admin.feature_type.index', 'popular') }}" class="menu-link">
                        <div data-i18n="popular">popular</div>
                    </a>
                </li>
            </ul>
        </li> --}}

        @can('features-show')
            <li class="menu-item feature_type">
                <a href="{{ route('admin.feature_type.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-spreadsheet"></i>
                    <div data-i18n="Analytics">Features</div>
                </a>
            </li>
            <li class="menu-item locations">
                <a href="{{ route('admin.locations.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-spreadsheet"></i>
                    <div data-i18n="Analytics">Locations</div>
                </a>
            </li>

        @endcan
        @can('features-show')
            {{-- <li class="menu-item seasons">
            <a href="{{route('admin.season')}}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-cloud-rain"></i>

                <div data-i18n="Analytics">Seasons</div>
            </a>
        </li> --}}
        @endcan





    </ul>
</aside>
@push('script')
    <script>
        $(document).ready(() => {
            $("#categories").click(() => {
                $("#features").removeClass('active');
            });
            $("#features").click(() => {
                $("#categories").removeClass('active');
            });
        })
    </script>
@endpush
