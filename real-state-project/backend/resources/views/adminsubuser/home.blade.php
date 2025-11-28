@extends('layout.master')
@section('content')
@section('title', __('PocketProperty | Admin Dashboard'))
    @php
        $auth = Auth::user();
    @endphp
    <style>
        th {
            font-size: 12px !important;
        }

        .heading {
            color: #A1A5B7 !important;
        }
    </style>
    <div class="content-wrapper">
        <div class="row">
            @if (auth()->guard('admin')->user()->hasRole('agency'))
                <div class="col-lg-4 col-md-6 mb-2">
                    <div class="card p-3 count-card">
                        <a class="d-flex align-items-center" href="{{route('adminSubUser.agent.index')}}">
                            <div class="rounded-circle icon-bg d-flex justify-content-center align-items-center">
                                <i class="fa fa-users text-white" style="font-size: 40px;"></i>
                            </div>
                            <div class="ml-5">
                                <h6 class="heading font-regular font-weight-bold mb-1">Total Agents</h6>
                                <h3 class="font-weight-bold mb-0">{{ $data['totalAgent'] }}</h3>
                            </div>
                        </a>
                    </div>
                </div>
            @endif
            <div class="col-lg-4 col-md-6 mb-2">
                <div class="card p-3 count-card">
                    <a class="d-flex align-items-center" href="{{route('adminSubUser.property.index')}}">
                        <div class="rounded-circle icon-bg d-flex justify-content-center align-items-center">
                            <i class="fa fa-building text-white" style="font-size: 40px;"></i>
                        </div>
                        <div class="ml-5">
                            <h6 class="heading font-regular font-weight-bold mb-1">Total Properties</h6>
                            <h3 class="font-weight-bold mb-0">{{ $data['totalProperty'] }}</h3>
                        </div>
                    </a>
                </div>
            </div>
            @if (auth()->guard('admin')->user()->hasRole(['agency', 'privatelandlord']))
                <div class="col-lg-4 col-md-6 mb-2">
                    <div class="card p-3 count-card">
                        <a class="d-flex align-items-center" href="{{route('adminSubUser.subscribe_list')}}">
                            <div class="rounded-circle icon-bg d-flex justify-content-center align-items-center">
                                {{-- <i class="fa fa-building text-white" style="font-size: 40px;"></i> --}}
                                <img src="{{ asset('assets/admin/images/planning.png') }}" width="59%" loading="lazy"/>
                            </div>
                            <div class="ml-5">
                                <h6 class="heading font-regular font-weight-bold mb-1">Total Plans</h6>
                                <h3 class="font-weight-bold mb-0">{{ $data['totalPlan'] ?? 0 }}</h3>
                            </div>
                        </a>
                    </div>
                </div>
            @endif
            <div class="col-lg-4 col-md-6 mb-2">
                <div class="card p-3 count-card">
                    <a class="d-flex align-items-center" href="{{route('adminSubUser.match-property.index')}}">
                        <div class="rounded-circle icon-bg d-flex justify-content-center align-items-center"
                            style="padding: 15px;">
                            <i class="fa fa-building text-white" style="font-size: 30px;"></i>
                            <i class="fa fa-user text-white" style="font-size: 30px;"></i>
                        </div>
                        <div class="ml-5">
                            <h6 class="heading font-regular font-weight-bold mb-1">Total Matching Properties</h6>
                            <h3 class="font-weight-bold mb-0">{{ $data['totalMatchProperties'] }}</h3>
                        </div>
                    </a>
                </div>
            </div>
            @if (!empty($data['adminPlan']))
                <div class="col-lg-4 col-md-6 mb-2">
                    <div class="card p-3 count-card">
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle icon-bg d-flex justify-content-center align-items-center">
                                <i class="fa fa-clock text-white" style="font-size: 40px;"></i>
                            </div>
                            <div class="ml-5">
                                <h6 class="heading font-regular font-weight-bold mb-1"> Plan valid upto </h6>
                                <h5 class="font-weight-bold mb-0 text-danger mt-2">{{ $data['adminPlan'] }}</h5>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
            @if (auth()->guard('admin')->user()->hasRole(['agent']))
                <div class="col-lg-4 col-md-6 mb-2">
                    <div class="card p-3 count-card">
                        <a class="d-flex align-items-center" href="#">
                            <div class="rounded-circle icon-bg d-flex justify-content-center align-items-center"
                                style="padding: 15px;">
                                <img src="{{ asset('assets/admin/images/calander2.png') }}" style="width:60px; height:60px;" />
                            </div>
                            <div class="ml-5">
                                <h6 class="heading font-regular font-weight-bold mb-1">Total Appointments</h6>
                                <h3 class="font-weight-bold mb-0">{{ $data['totalAppointments'] }}</h3>
                            </div>
                        </a>
                    </div>
                </div>
            @endif
            @if (auth()->guard('admin')->user()->hasRole(['agency', 'privatelandlord']))
                <div class="col-lg-4 col-md-6 mb-2">
                    <div class="card p-3 count-card">
                        <a class="d-flex align-items-center" href="{{route('adminSubUser.calendar.pvr_index')}}">
                            <div class="rounded-circle icon-bg d-flex justify-content-center align-items-center">
                                <img src="{{ asset('assets/admin/images/planning.png') }}" width="59%" />
                            </div>
                            <div class="ml-5 row justify-content-between w-75">
                                <div class="col-12">
                                    <h6 class="heading font-regular font-weight-bold mb-1">Property Viewing Request</h6>
                                </div>
                                <div class="col-6">
                                    <h3 class="font-weight-bold mb-0 text-success">{{ $data['pvr']['acceptedPVR'] ?? 0 }}</h3>
                                    <p class="heading font-regular mb-0 text-small">Approved Request</p>
                                </div>
                                <div class="col-6">
                                    <h3 class="font-weight-bold mb-0">{{ $data['pvr']['TotalPVR'] ?? 0 }}</h3>
                                    <p class="heading font-regular mb-0 text-small">Total Request</p>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
            @endif

            @if (auth()->guard('admin')->user()->hasRole('agency') && $data['admin']['selected_agency_api_status'] == 1)
                <div class="col-lg-4 col-md-6 mb-2 {{ $data['admin']['selected_agency_api_status'] }}">
                    <div class="card p-3 count-card">
                        <a class="d-flex align-items-center" href="#" type="button" class="btn btn-primary" data-toggle="modal" id="model_btn" data-target=".bd-example-modal-lg">
                            <div class="rounded-circle icon-bg d-flex justify-content-center align-items-center">
                                <i class="fa fa-terminal text-white" style="font-size: 40px;"></i>
                            </div>
                            <div class="ml-5">
                                <h6 class="heading font-regular font-weight-bold mb-1">Property API</h6>
                                <h3 class="font-weight-bold mb-0">Login credentials</h3>
                            </div>
                        </a>
                    </div>
                </div>
            @endif
        </div>
        @if (auth()->guard('admin')->user()->hasRole('agency') && $data['admin']['selected_agency_api_status'] == 1)
            <div class="modal fade bd-example-modal-lg modal-dialog-scrollable" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content" id="">
                        <div class="row grid-margin mb-0">
                            <div class="col-lg-12">
                                <div class="card">
                                    <div class="card-body">
                                        @php
                                            $external_property = $data['postmanAPI'] ?? null;
                                        @endphp
                                        <div class="row">
                                            <div class="form-group col-6">
                                                <label for="name">User Name </label>
                                                <input id="name" autocomplete="off" class="form-control" type="text"
                                                    rows="20" name="name" value="{{ $external_property?->name ?? '' }}"
                                                    placeholder="Enter name" />
                                            </div>

                                            <div class="form-group col-6">
                                                <label for="Password">Password</label>
                                                <input id="Password" autocomplete="off" class="form-control" type="text"
                                                    rows="20" name="password" placeholder="Enter Password"
                                                    value="{{ $external_property?->password_text}}" />
                                            </div>

                                            @if (isset($external_property))
                                                <div class="form-group col-12">
                                                    <label for="api_key">Api Key</label>
                                                    <textarea id="api_key" autocomplete="off" class="form-control" type="text" rows="10" name="api_key"
                                                        placeholder="Enter api key mumber">{{ $external_property?->api_key ?? '' }}</textarea>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <div class="card">
            <div class="card-body">
                <div class="row d-flex justify-content-between align-items-center">
                    <div class="mb-3">
                        <h6 class="cart-title title_for_table">Today’s Property Viewing Request</h6>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="table-responsive">
                            <table id="pvrTable" class="table">
                                <thead>
                                    <tr>
                                        <th>Property Name</th>
                                        <th>Property Type</th>
                                        <th>Tenant Name</th>
                                        <th>Request Time & Date</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('custom-script')
        <script type="text/javascript">
            document.addEventListener('DOMContentLoaded', function () {
                const ajax_url = `{{ route('adminSubUser.calendar.pvr_index') }}`;
                let AJAX_URL_DATA_TABLE = ``;
                const filter = '';

                var pvrTable = $('#pvrTable').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: ajax_url,
                        data: function (d) {
                            d.selectedDate = $('#selectedDate').val();
                        }
                    },
                    columns: [
                        {
                            data: 'property',
                            name: 'property',
                            searchable: true,
                        }, {
                            data: 'property_type',
                            name: 'property_type',
                            searchable: true
                        }, {
                            data: 'tenant',
                            name: 'tenant',
                            searchable: true,
                        }, {
                            data: 'event_datetime',
                            name: 'event_datetime',
                            render: function (data, type, row) {
                                return dateF2(data);
                            }
                        }, {
                            data: 'status',
                            name: 'status'
                        }
                    ],
                    drawCallback: function (settings, json) {

                        $('#pvrTable').off('click', '.view-more').on('click', '.view-more',
                            function () {
                                var $shortText = $(this).siblings('.short-text');
                                var $fullText = $(this).siblings('.full-text');
                                if ($shortText.is(':visible')) {
                                    $shortText.hide();
                                    $fullText.show();
                                    $(this).text('View Less');
                                } else {
                                    $shortText.show();
                                    $fullText.hide();
                                    $(this).text('View More');
                                }
                            });
                        $('[data-toggle=tooltip]').tooltip();
                    }
                });
            });
        </script>
    @endpush
