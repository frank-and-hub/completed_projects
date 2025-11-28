@extends('admin.layout.master')
<!-- Content wrapper -->

<!-- Content -->
@section('content')
    @push('custom-style')
        <link rel="stylesheet" href="{{ asset('assets/daterangepicker-master/daterangepicker.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/vendor/css/dashboard.css') }}">
    @endpush
    <x-admin.dashboard.cards />

    <div class="row">
        <div class="col-md-6 mt-3 grid-margin grid-margin-md-0">
            <div class="card">
                <div class="card-header bg-lightblue">
                    <div class="d-row d-flex justify-content-between">

                        <h6 class="text-primary">Day Wise Parks and Users</h6>
                        <div id="date-range-filter-day-wise-users" class="date-range-filter-day-wise-users btn btn-primary"
                            style="padding:8px !important;">
                            <i class="fas fa-calendar"></i>&nbsp;
                            <span></span> <i class="fa fa-caret-down"></i>
                        </div>

                    </div>
                </div>

                <div class="card-body p-2 pb-4">
                    <div class="loader d-none" id="day_wise_users_parks_bar_chart_loader"
                        style="margin: auto;
                    position: absolute;
                    left: 50%;
                    top: 45%;">
                    </div>
                    <div class="chart">
                        <div class="chartjs-size-monitor">
                            <div class="chartjs-size-monitor-expand">
                                <div class=""></div>
                            </div>
                            <div class="chartjs-size-monitor-shrink">
                                <div class=""></div>
                            </div>
                        </div>
                        <div class="day-wise-bar-chart">
                            <canvas id="day-wise-parks-bar-chart" class="chart-canvas chartjs-render-monitor"
                                style="min-height: 260px; height: 290px; display: block; width: 580px;" width="725"
                                height="362"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if ($verified_ratings > 0)
            @can('users-show')
                <div class="col-md-6 mt-3 grid-margin grid-margin-md-0">
                    <div class="card">
                        <div class="card-header bg-lightblue">
                            <h6 class="text-primary">Top 5 Parks</h6>
                        </div>
                        <div class="card-body mt-2" style="height:320px;">
                            <div class="loader d-none" id="top_five_park_loader"
                                style="margin: auto;
                    position: absolute;
                    left: 50%;
                    top: 45%;">
                            </div>
                            <div class="p-0 m-0" id="top_parks">

                            </div>



                        </div>
                    </div>

                </div>
            @endcan
        @endif
    </div>

    @if($verified_ratings>0)
    @can('users-show')
        <div class="row">
            <div class="col-md-6 mt-3 grid-margin grid-margin-md-0">
                <div class="card" style="height:415px;">
                    <div class="card-header bg-lightblue">
                        <h6 class='text-primary'>Top 5 Users</h6>
                    </div>
                    <div class="card-body">
                        <div class="loader d-none" id="top_five_user_loader"
                            style="margin: auto;
                    position: absolute;
                    left: 50%;
                    top: 45%;">
                        </div>
                        <div class="p-0 m-0" id="top_users">

                        </div>
                    </div>
                </div>

            </div>
        </div>
    @endcan
    @endif

    @push('script')
        <script>
            var now = "{{ Carbon\Carbon::now()->setTimezone(Auth::user()->timezone) }}";
            var day_wise_parks_users_bar_chart_url = "{{ route('admin.dashboard.day_wise_parks_users_bar_chart') }}";
            var dashboard_countr_url = "{{ route('admin.dashboard.count') }}";
            var top_parks_url = "{{ route('admin.dashboard.top.five.parks') }}";
            var top_users_url = "{{ route('admin.dashoard.top.five.users') }}";
        </script>
        <script src="{{ asset('assets/js/charts/chart.js') }}"></script>
        <script src="{{ asset('assets/daterangepicker-master/moment.min.js') }}"></script>
        <script src="{{ asset('assets/daterangepicker-master/daterangepicker.js') }}"></script>
        <script src="{{ asset('assets/js/chart-daterange-picker.js') }}"></script>
        <script src="{{ asset('assets/js/charts/day-wise-users-parks-chart.js') }}"></script>
        <script src={{ asset('assets/js/dashboard/dashboard.js') }}></script>
        <script src="{{ asset('assets/js/dashboard/top_parks.js') }}"></script>
        <script src="{{ asset('assets/js/dashboard/top_users.js') }}"></script>
    @endpush
@endsection
