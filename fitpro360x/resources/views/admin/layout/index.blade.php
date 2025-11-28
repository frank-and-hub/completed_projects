<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin : @yield('admin-title')</title>
    <link href="{{ asset('assets/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/style.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/responsive.css') }}" rel="stylesheet">

    {{-- TOASTR --}}
    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet">


    <link href="{{ asset('assets/css/custom.css') }}" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/images/fav-icon.png') }}">
    @stack('styles')

</head>
@php $isCollapsed = session('menu_collapse', false); @endphp

<body class="innerbody {{ $isCollapsed ? 'menu-collapse' : '' }}">
    <aside class="sidebar">
        <div class="closemenu-btn"><img src="{{ asset('assets/images/Close_round_fill.png') }}" class="img-fluid"></div>
        <div class="text-center sildebarlogo">
            <a href="{{ route('admin.dashboard') }}">
                <img src="{{ asset('assets/images/logo-login.svg') }}" class="img-fluid logo-icon">
            </a>
        </div>
        <div class="menubar-holder">
            <ul class="menubar">
                <li class="{{ Request::routeIs('admin.dashboard') ? 'active' : '' }}">
                    <a href="{{ route('admin.dashboard') }}"><img
                            src="{{ asset('assets/images/dashboard-icon.svg') }}">
                        <span>Dashboard</span></a>
                </li>

                <li class="mt-2 {{ Request::routeIs('admin.userIndex') ? 'active' : '' }}">
                    <a href="{{ route('admin.userIndex') }}"><img src="{{ asset('assets/images/user-icon.svg') }}">
                        <span>Users</span></a>
                </li>
                <li
                    class="mt-2 {{ Request::routeIs('admin.workoutPlans*') ||
                    Request::is('admin/workoutPlans*') ||
                    Request::routeIs('admin.workoutPlansAdd*') ||
                    Request::is('admin/workout-plan/workout_settings*')||
                    //Request::routeIs('admin.meal*') ||
                    Request::is('admin/workout-plan/create_meal*') ||
                    Request::routeIs('admin.workoutSettingsEdit*') ||
                    Request::routeIs('admin.editWorkoutMeal*')
                                        
                        ? 'active'
                        : '' }}">
                    <a href="{{ route('admin.workoutPlansIndex') }}">
                        <img src="{{ asset('assets/images/Workout-plans.svg') }}">
                        <span>Workout Plans</span>
                    </a>
                </li>
                <li
                    class="mt-2 {{ Request::routeIs('admin.exercise*') || Request::is('admin/exercise*') ? 'active' : '' }}">
                    <a href="{{ route('admin.exerciseIndex') }}"><img
                            src="{{ asset('assets/images/Exercise.svg') }}"><span>Exercises</span></a>
                </li>
                <li
                    class="mt-2 {{ Request::routeIs('admin.fitnessChallenge*') || Request::is('admin/fitnessChallenge*') ? 'active' : '' }}">
                    <a href="{{ route('admin.fitnessChallengeIndex') }}"><img
                            src="{{ asset('assets/images/Fitness-challenge.svg') }}">
                        <span>Fitness
                            Challenges</span></a>
                </li>
                <li class="mt-2 {{ Request::routeIs('admin.mealsPlanIndex') ? 'active' : '' }}">
                    <a href="{{ route('admin.mealsPlanIndex') }}"><img
                            src="{{ asset('assets/images/Meal-plan.svg') }}">
                        <span>Meal
                            Plans</span></a>
                </li>
                <li class="mt-2 {{ Request::routeIs('admin.muscleIndex') ? 'active' : '' }}">
                    <a href="{{ route('admin.muscleIndex') }}"><img
                            src="{{ asset('assets/images/muscles_type_icon.svg') }}" class="ms-2">
                        <span class="ms-1">Muscles</span></a>
                </li>
                <li class="mt-2 {{ Request::routeIs('admin.bodyTypeIndex') ? 'active' : '' }}">
                    <a href="{{ route('admin.bodyTypeIndex') }}"><img
                            src="{{ asset('assets/images/body_type_icon.svg') }}" class="ms-2">
                        <span class="ms-1">Body Types</span></a>
                </li>
        </div>
    </aside>
    <!--header part-->
    <div class="header">
        <a href="javascript:void(0)" class="slidetoggle" id="sidebarToggle"><img
                src="{{ asset('assets/images/menu (2).svg') }}"> </a>
        <div class="user-set-menu dropdown">
            <a class="dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                <img src="{{ asset('assets/images/user-lg-pic.svg') }}" class="me-2">
                <div style="line-height: 1;" class="pe-2">
                    <h6>{{ ucfirst($currentUserInfo->fullname) }}</h6>
                </div>
            </a>
            <ul class="dropdown-menu">
                {{-- <li class="d-flex px-2"><img src="{{ asset('assets/images/lock.svg') }}" alt="">
                    <a class="dropdown-item ps-2" href="{{ route('admin.getProfile') }}">Profile</a>
                </li> --}}
                <li class="d-flex px-2"><img src="{{ asset('assets/images/lock.svg') }}" alt="">
                    <a class="dropdown-item ps-2" href="{{ route('admin.password.change') }}">Change Password</a>
                </li>
                <li class="d-flex px-2"><img src="{{ asset('assets/images/logout.svg') }}" alt="">
                    <a class="dropdown-item ps-2" href="{{ route('admin.logout') }}">Logout</a>
                </li>

            </ul>
        </div>
    </div>
    <!--header part-->
    <!--PAGE CONTENT-->
    @yield('content')
    <!--PAGE CONTENT-->
    <script>
        var csrf = "{{ csrf_token() }}";
        var baseUrl = "{{ url('/') }}";
    </script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js"></script>
    <script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}"></script>

    {{-- TOASTR --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    <script src="{{ asset('assets/js/custom.js') }}"></script>
    
    @stack('scripts')
</body>
<script>
    $('#sidebarToggle').on('click', function() {
        $.ajax({
            url: '{{ route('admin.toggle.sidebar') }}',
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            success: function(res) {
                if (res.collapsed) {
                    $('body').addClass('menu-collapse');
                } else {
                    $('body').removeClass('menu-collapse');
                }
            }
        });
    });
</script>
<script>
    @if (session('success'))
        toastr.success("{{ session('success') }}");
    @endif
    @if (session('error'))
        toastr.error("{{ session('error') }}");
    @endif
</script>
@stack('script')

</html>
