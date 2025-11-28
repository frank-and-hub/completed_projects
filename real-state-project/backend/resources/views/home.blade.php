@extends('layout.master')
@section('content')
@section('title', __('PocketProperty | Admin Dashboard'))
<div class="content-wrapper">
    <div class="page-header">
        <h3 class="page-title">
            Dashboard
        </h3>
    </div>
    @if (auth()->guard('admin')->user()->hasRole('admin'))
        <div class="row">
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card p-20">
                    <a href="{{ route('user_list') }}">
                        <div class="border-lg-right border-bottom border-xxl-bottom-0">
                            <h6 class="pl-2 mb-0 ml-3 font-regular text-muted font-weight-bold mt-3">Total Users</h6>
                            <div class="d-block d-sm-flex h-100 align-items-center card-dash">
                                <i class="fa fa-users ml-3 fa_dash" style="font-size:42px"></i>
                                <div class="mt-3 mt-sm-0 ml-sm-auto mr-3 text-center text-sm-right">
                                    <h3 class="mb-0">{{ $data['totaluser'] }}</h3>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card p-20">
                    <div class="border-lg-right border-bottom border-xxl-bottom-0">
                        <h6 class="pl-2 mb-0 ml-3 font-regular text-muted font-weight-bold mt-3">Property Needs Revenue</h6>
                        <div class="d-block d-sm-flex h-100 align-items-center card-dash">
                            <i class="fa fa-signal ml-3 fa_dash" style="font-size:42px"></i>
                            <div class="mt-3 mt-sm-0 ml-sm-auto mr-3 text-center text-sm-right">
                                <h3 class="mb-0">{{ $data['revenue']['total'] }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card p-20">
                    <div class="border-lg-right border-bottom border-xxl-bottom-0">
                        <h6 class="pl-2 mb-0 ml-3 font-regular text-muted font-weight-bold mt-3">Agency Revenue</h6>
                        <div class="d-block d-sm-flex h-100 align-items-center card-dash">
                            <i class="fa fa-signal ml-3 fa_dash" style="font-size:42px"></i>
                            <div class="mt-3 mt-sm-0 ml-sm-auto mr-3 text-center text-sm-right">
                                <h3 class="mb-0">{{ $data['revenue']['agency'] }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card p-20">
                    <div class="border-lg-right border-bottom border-xxl-bottom-0">
                        <h6 class="pl-2 mb-0 ml-3 font-regular text-muted font-weight-bold mt-3">LandLord
                            Revenue</h6>
                        <div class="d-block d-sm-flex h-100 align-items-center card-dash">
                            <i class="fa fa-signal ml-3 fa_dash" style="font-size:42px"></i>
                            <div class="mt-3 mt-sm-0 ml-sm-auto mr-3 text-center text-sm-right">
                                <h3 class="mb-0">{{ $data['revenue']['privateLandLord'] }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card p-20">
                    <a href="{{ route('enquiry_list') }}">
                        <div class="border-lg-right border-bottom border-xxl-bottom-0">
                            <h6 class="pl-2 mb-0 ml-3 font-regular text-muted font-weight-bold mt-3">Total Web Form
                                Enquiries</h6>
                            <div class="d-block d-sm-flex h-100 align-items-center card-dash">
                                <i class="fa fa-id-card ml-3 fa_dash" style="font-size:42px"></i>
                                <div class="mt-3 mt-sm-0 ml-sm-auto mr-3 text-center text-sm-right">
                                    <h3 class="mb-0">{{ $data['totalRequest'] }}</h3>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card p-20">
                    <div class="border-lg-right border-bottom border-xxl-bottom-0">
                        <h6 class="pl-2 mb-0 ml-3 font-regular text-muted font-weight-bold mt-3">Total Submitted
                            Property Needs </h6>
                        <div class="d-block d-sm-flex h-100 align-items-center card-dash">
                            <i class="fa fa-building ml-3 fa_dash" style="font-size:42px"></i>
                            <div class="mt-3 mt-sm-0 ml-sm-auto mr-3 text-center text-sm-right">
                                <h3 class="mb-0">{{ $data['totalsearchproperty'] }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card p-20">
                    <div class="border-lg-right border-bottom border-xxl-bottom-0">
                        <h6 class="pl-2 mb-0 ml-3 font-regular text-muted font-weight-bold mt-3">Total Basic Submitted
                            Property Needs</h6>
                        <div class="d-block d-sm-flex h-100 align-items-center card-dash">
                            <i class="fa fa-building ml-3 fa_dash" style="font-size:42px"></i>
                            <div class="mt-3 mt-sm-0 ml-sm-auto mr-3 text-center text-sm-right">
                                <h3 class="mb-0">{{ $data['basicPlanProperty'] }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card p-20">

                    <div class="border-lg-right border-bottom border-xxl-bottom-0">
                        <h6 class="pl-2 mb-0 ml-3 font-regular text-muted font-weight-bold mt-3">Total Professional
                            Property Needs</h6>
                        <div class="d-block d-sm-flex h-100 align-items-center card-dash">
                            <i class="fa fa-building ml-3 fa_dash" style="font-size:42px"></i>
                            <div class="mt-3 mt-sm-0 ml-sm-auto mr-3 text-center text-sm-right">
                                <h3 class="mb-0">{{ $data['professionalPlanProperty'] }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card p-20">
                    <a href="{{ route('property_data') }}">
                        <div class="border-lg-right border-bottom border-xxl-bottom-0">
                            <h6 class="pl-2 mb-0 ml-3 font-regular text-muted font-weight-bold mt-3">Total Property
                                Clients
                            </h6>
                            <div class="d-block d-sm-flex h-100 align-items-center card-dash">
                                <i class="fa fa-building ml-3 fa_dash" style="font-size:42px"></i>
                                <i class="fa fa-user fa_dash" style="font-size:42px"></i>
                                <div class="mt-3 mt-sm-0 ml-sm-auto mr-3 text-center text-sm-right">
                                    <h3 class="mb-0">{{ $data['totalPropertyClient'] }}</h3>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card p-20">
                    <a class="border-lg-right border-bottom border-xxl-bottom-0" href="{{ route('map') }}"
                        target="_black">
                        <h6 class="pl-2 mb-0 ml-3 font-regular text-muted font-weight-bold mt-3">Total Properties
                        </h6>
                        <div class="d-block d-sm-flex h-100 align-items-center card-dash">
                            <i class="fa fa-building ml-3 fa_dash" style="font-size:42px"></i>
                            <div class="mt-3 mt-sm-0 ml-sm-auto mr-3 text-center text-sm-right">
                                <h3 class="mb-0">{{ $data['totalProperty'] + $data['totalInternalProperty'] }}
                                </h3>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
        <div class="row">
            {{-- Revenue --}}
            <div class="col-lg-8 col-md-6 col-sm-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body d-flex flex-column">
                        <h4 class="card-title">
                            Revenue
                        </h4>
                        <div class="row year_month">
                            <div class="col-6">
                                <div class="form-group">
                                    <label for="year-select">Select Year:</label>
                                    <select id="year-select" class="form-control">
                                        @foreach ($data['years'] as $year)
                                            <option value="{{ $year }}">{{ $year }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label for="month-select">Select Month:</label>
                                    <select id="month-select" class="form-control">
                                        <option value="">All Months</option>
                                        @foreach ($data['months'] as $month)
                                            <option value="{{ $month['value'] }}">{{ $month['name'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="text-center">
                            <img src="{{ asset('assets/admin/images/noDataFound.gif') }}" style="display: none; "
                                id="bar_chart_nodata">
                        </div>
                        <div id="total_revenue_can chart-container"
                            style="position: relative; height: 100%; width: 100%;">
                            <canvas id="total_revenue"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            {{-- Total Revenue --}}
            <div class="col-lg-4 col-md-6 col-sm-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body d-flex flex-column">
                        <h4 class="card-title">Total Revenue</h4>
                        <div class="flex-grow-1 d-flex flex-column justify-content-between">
                            <canvas id="total-revenue-sale-chart" class="mt-3 mb-3 mb-md-0"></canvas>
                            {{-- <div id="total-revenue-sale-chart-legend" class="daily-sales-chart-legend pt-4 border-top"></div> --}}
                        </div>
                        <div class="text-center">
                            <img src="{{ asset('assets/admin/images/noDataFound.gif') }}" id="total-revenue-nodata"
                                style="display: none;">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            {{-- Total Property Needs --}}
            <div class="col-lg-4 col-md-6 col-sm-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body d-flex flex-column">
                        <h4 class="card-title">Total Property Needs</h4>
                        <div class="flex-grow-1 d-flex flex-column justify-content-between">
                            <canvas id="property-needs-chart" class="mt-3 mb-3 mb-md-0"></canvas>
                            {{-- <div id="property-needs-chart-legend" class="pt-4"></div> --}}
                        </div>
                        <div class="text-center">
                            <img src="{{ asset('assets/admin/images/noDataFound.gif') }}" id="property-nodata"
                                style="display: none;">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Property Needs --}}
            <div class="col-lg-8 col-md-6 col-sm-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body d-flex flex-column">
                        <h4 class="card-title">
                            Property Needs
                        </h4>
                        <div class="row year_month_property">
                            <div class="col-6">
                                <div class="form-group">
                                    <label for="year-select-property">Select Year:</label>
                                    <select id="year-select-property" class="form-control">
                                        @foreach ($data['years'] as $year)
                                            <option value="{{ $year }}">{{ $year }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label for="month-select-property">Select Month:</label>
                                    <select id="month-select-property" class="form-control">
                                        <option value="">All Months</option>
                                        @foreach ($data['months'] as $month)
                                            <option value="{{ $month['value'] }}">{{ $month['name'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="text-center">
                            <img src="{{ asset('assets/admin/images/noDataFound.gif') }}" style="display: none; "
                                id="total_property_nodata">
                        </div>
                        <div id="total_property_con chart-container"
                            style="position: relative; height: 100%; width: 100%;">
                            <canvas id="total_property"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{--  --}}
    {{-- <div class="modal fade" id="adminPropertyMap"  tabindex="-1" role="dialog"
        aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-backdrop="static">
        <div class="modal-dialog" role="document"> --}}

    <div class="modal fade bd-example-modal-lg" style="background:rgba(0,0,0,0.4)" id="adminPropertyMap"
        tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header plan_name">
                    <h5 class="modal-title text-center">All Property (Private Landlord, Agent, Agency)</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div id="mapContainer" class="allpropertyMap">
                    <div id="map"></div>
                </div>

            </div>
        </div>
    </div>
</div>
@push('custom-script')
    @if (auth()->guard('admin')->user()->hasRole('admin'))
        <script src="{{ asset('assets/admin/js/chart.js?' . config('constants.asert_version')) }}"></script>
        <script src="{{ asset('assets/admin/js/dashboard.js?' . config('constants.asert_version')) }}"></script>
        <script type="text/javascript">
            // Total Revenue
            $(function() {
                var professional = parseFloat("{{ $data['revenue']['professional'] }}");
                var basic = parseFloat("{{ $data['revenue']['basic'] }}");
                var agencies = parseFloat("{{ $data['revenue']['agency'] }}");
                var landLord = parseFloat("{{ $data['revenue']['privateLandLord'] }}");

                initializeChart(
                    'total-revenue-sale-chart', 'total-revenue-sale-chart-legend', 'total-revenue-nodata', [
                        professional, basic, agencies, landLord
                    ], [
                        'Professional',
                        'Basic',
                        'Agencies',
                        'LandLord'
                    ],
                    [
                        '#F9B432',
                        '#F30051',
                        '#A9B432',
                        '#A30051'
                    ]
                );
            });


            $(function() {
                var chart;

                function fetchChartData(year, month) {
                    $.ajax({
                        url: "{{ route('total_revenue') }}",
                        method: 'GET',
                        data: {
                            year: year,
                            month: month
                        },
                        success: function(data) {
                            if (data.status == 404) {
                                $('#bar_chart_nodata').show();
                                $('.year_month').hide();
                                $('#total_revenue_can').css('display', 'none');
                                if (chart) {
                                    chart.destroy();
                                }
                            } else if (data.message) {
                                $('#bar_chart_nodata').show();
                                $('#total_revenue_can').css('display', 'none');
                                if (chart) {
                                    chart.destroy();
                                }
                            } else {
                                $('#bar_chart_nodata').hide();
                                updateChart(data, month);
                                $('#total_revenue_can').css('display', 'block');
                            }
                        }
                    });
                }

                function updateChart(data, month) {
                    var ctx = document.getElementById('total_revenue').getContext('2d');

                    if (chart) {
                        chart.destroy();
                    }

                    var xLabel = month ? 'Weeks' : 'Months';

                    chart = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: data.map(item => item.y),
                            datasets: [
                                {
                                    label: 'Professional',
                                    data: data.map(item => item.a),
                                    backgroundColor: '#F9B432'
                                },
                                {
                                    label: 'Basic',
                                    data: data.map(item => item.b),
                                    backgroundColor: '#F30051'
                                },
                                {
                                    label: 'Agencies',
                                    data: data.map(item => item.c),
                                    backgroundColor: '#A9B432'
                                },
                                {
                                    label: 'LandLord',
                                    data: data.map(item => item.d),
                                    backgroundColor: '#A30051'
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                x: {
                                    title: {
                                        display: true,
                                        text: xLabel
                                    }
                                },
                                y: {
                                    title: {
                                        display: true,
                                        text: 'ZAR'
                                    },
                                    ticks: {
                                        callback: function(value, index, values) {
                                            return value.toLocaleString(); // Format with commas
                                        }
                                    }
                                }
                            },
                            plugins: {
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            return context.dataset.label + ': ' + context.raw
                                                .toLocaleString(); // Format with commas
                                        }
                                    }
                                }
                            }
                        }
                    });
                }

                $('#year-select').change(function() {
                    var year = $(this).val();
                    var month = $('#month-select').val();
                    fetchChartData(year, month);
                });

                $('#month-select').change(function() {
                    var year = $('#year-select').val();
                    var month = $(this).val();
                    fetchChartData(year, month);
                });

                // Initialize chart with default year (current year)
                var currentYear = new Date().getFullYear();
                $('#year-select').val(currentYear);
                fetchChartData(currentYear, '');
            });

            // Property Needs
            $(function() {
                var professionalProperty = parseFloat("{{ $data['professionalPlanProperty'] }}");
                var basicProperty = parseFloat("{{ $data['basicPlanProperty'] }}");

                initializeChart(
                    'property-needs-chart', 'property-needs-chart-legend', 'property-nodata', [professionalProperty,
                        basicProperty
                    ], ['Professional Property', 'Basic Property'], ['#F9B432', '#F30051']
                );
            });

            $(function() {
                var chart;

                function fetchChartData(year, month) {
                    $.ajax({
                        url: "{{ route('total_property') }}",
                        method: 'GET',
                        data: {
                            year: year,
                            month: month
                        },
                        success: function(data) {
                            if (data.status == 404) {
                                $('#total_property_nodata').show();
                                $('.year_month_property').hide();
                                $('#total_property_con').css('display', 'none');
                                if (chart) {
                                    chart.destroy();
                                }
                            } else if (data.message) {
                                $('#total_property_nodata').show();
                                $('#total_property_con').css('display', 'none');
                                if (chart) {
                                    chart.destroy();
                                }
                            } else {
                                $('#total_property_nodata').hide();
                                updateChart(data, month);
                                $('#total_property_con').css('display', 'block');
                            }
                        }
                    });
                }

                function updateChart(data, month) {
                    var ctx = document.getElementById('total_property').getContext('2d');

                    if (chart) {
                        chart.destroy();
                    }

                    var xLabel = month ? 'Weeks' : 'Months';

                    chart = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: data.map(item => item.y),
                            datasets: [{
                                    label: 'Professional Property Needs',
                                    data: data.map(item => item.a),
                                    backgroundColor: '#F9B432'
                                },
                                {
                                    label: 'Basic Property Needs',
                                    data: data.map(item => item.b),
                                    backgroundColor: '#F30051'
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                x: {
                                    title: {
                                        display: true,
                                        text: xLabel
                                    }
                                },
                                y: {
                                    title: {
                                        display: true,
                                        text: 'Count'
                                    },
                                    ticks: {
                                        callback: function(value, index, values) {
                                            return value.toLocaleString(); // Format with commas
                                        }
                                    }
                                }
                            },
                            plugins: {
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            return context.dataset.label + ': ' + context.raw
                                                .toLocaleString(); // Format with commas
                                        }
                                    }
                                }
                            }
                        }
                    });
                }

                $('#year-select-property').change(function() {
                    var year = $(this).val();
                    var month = $('#month-select-property').val();
                    fetchChartData(year, month);
                });

                $('#month-select-property').change(function() {
                    var year = $('#year-select-property').val();
                    var month = $(this).val();
                    fetchChartData(year, month);
                });

                // Initialize chart with default year (current year)
                var currentYear = new Date().getFullYear();
                $('#year-select-property').val(currentYear);
                fetchChartData(currentYear, '');
            });


            // function adminPropertyMap() {
            // $('#adminPropertyMap').modal('show');
            // }
        </script>
    @endif
@endpush
@endsection
