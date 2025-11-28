@extends('layout.master')
@section('content')
@section('title', __('PocketProperty | ' . ucwords($active_page)))
    <div class="content-wrapper">
        <div class="page-header">
            <h3 class="page-title">
                {{ ucwords($title) }} Details
            </h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#">{{ ucwords($active_page) }}</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('adminSubUser.agent.index') }}">list </a></li>
                    <li class="breadcrumb-item active" aria-current="page"> Details</li>
                </ol>
            </nav>
        </div>
        <div class="row">
            <div class="col-lg-5 m-auto">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex flex-column align-items-center text-center">
                            <img src="{{ $admin->image()->first()?->path ? Storage::url($admin->image()->first()->path) : asset('assets/default_user.png') }}"
                                alt="" class="rounded-circle p-1 object-fit-cover" width="150" height="150">
                            <div class="mt-3 w-100">
                                <h4>{{ ucwords($admin->name) }}</h4>
                            </div>
                        </div>
                        @if ($role == 'agency')
                            <ul class="list-group list-group-flush mt-3">
                                <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                                    <span class="mb-0">Business Name</span>
                                    <span>{{ $admin?->agencyRegister?->business_name }}</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                                    <span class="mb-0">Phone</span>
                                    <span>{{ $admin->country_code . ' ' . $admin->phone }}</span>
                                </li>

                                <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                                    <span class="mb-0">Email</span>
                                    <span><a href="https://mail.google.com/mail/?view=cm&fs=1&to={{ $admin->email }}"
                                            target="_blank">{{ $admin->email }}</a></span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                                    <span class="mb-0">Director / Owner ID number</span>
                                    <span>{{ $admin?->agencyRegister?->id_number }}</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                                    <span class="mb-0">Company registration number</span>
                                    <span>{{ $admin?->agencyRegister?->registration_number }}</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                                    <span class="mb-0">Company VAT number</span>
                                    <span>{{ $admin?->agencyRegister?->vat_number }}</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                                    <span class="mb-0">Address</span>
                                    <p>
                                        <span>{{ ucwords($admin?->agencyRegister?->street_address) }}</span>
                                        <br>
                                        <span>{{ ucwords($admin?->agencyRegister?->street_address_2) }}</span>
                                        <br>
                                        <span>{{ ucwords($admin->agencyRegister?->state_?->name ?: '') }}</span>
                                        <br>
                                        <span>{{ ucwords($admin->agencyRegister?->city_?->name ?: '') }}</span>
                                        <br>
                                        <span>{{ ucwords($admin?->agencyRegister?->postal_code) }}</span>
                                    </p>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                                    <span class="mb-0">Country</span>
                                    <span>{{ ucwords($admin->agencyRegister?->country_?->name) }}</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                                    <span class="mb-0">Time Zone</span>
                                    <span>{{ ucwords($admin->timeZone) }}</span>
                                </li>

                                <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                                    <span class="mb-0">Created At</span>
                                    <small>{{ $admin->__created_at }}</small>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                                    <span class="mb-0">Status</span>
                                    <span>
                                        @if ($admin->status == 1)
                                            <button class="btn btn-success btn-rounded">Active</button>
                                        @else
                                            <button class="btn  btn-rounded">Inactive</button>
                                        @endif
                                    </span>
                                </li>
                            </ul>
                        @elseif($role == 'privatelandlord')
                            <ul class="list-group list-group-flush mt-3">
                                {{-- <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                                    <span class="mb-0">Name</span>
                                    <span>{{$admin->name}}</span>
                                </li> --}}
                                <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                                    <span class="mb-0">Phone</span>
                                    <span>{{ $admin->dial_code . ' ' . $admin->phone }}</span>
                                </li>

                                <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                                    <span class="mb-0">Email</span>
                                    <span><a href="https://mail.google.com/mail/?view=cm&fs=1&to={{ $admin->email }}"
                                            target="_blank">{{ $admin->email }}</a></span>
                                </li>

                                <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                                    <span class="mb-0">Status</span>
                                    <span>
                                        @if ($admin->status == 1)
                                            <button class="btn btn-success btn-rounded">Active</button>
                                        @else
                                            <button class="btn  btn-rounded">Inactive</button>
                                        @endif
                                    </span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                                    <span class="mb-0">Country</span>
                                    <span>{{ ucwords($admin->country) }}</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                                    <span class="mb-0">Time Zone</span>
                                    <span>{{ ucwords($admin->timeZone) }}</span>
                                </li>
                            </ul>
                        @elseif($role == 'agent')
                            <ul class="list-group list-group-flush mt-3">
                                <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                                    <span class="mb-0">Name</span>
                                    <span>{{ $admin->name }}</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                                    <span class="mb-0">Agency Name</span>
                                    <span><a
                                            href="{{ route('admin_user.role_type_view', $admin->admin_id) }}">{{ ucwords($admin->agent_agency->name) }}</a></span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                                    <span class="mb-0">Phone</span>
                                    <span>{{ $admin->dial_code . ' ' . $admin->phone }}</span>
                                </li>

                                <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                                    <span class="mb-0">Email</span>
                                    <span><a href="https://mail.google.com/mail/?view=cm&fs=1&to={{ $admin->email }}"
                                            target="_blank">{{ $admin->email }}</a></span>
                                </li>

                                <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                                    <span class="mb-0">Status</span>
                                    <span>
                                        @if ($admin->status == 1)
                                            <button class="btn btn-success btn-rounded">Unblocked</button>
                                        @else
                                            <button class="btn  btn-rounded">Blocked</button>
                                        @endif
                                    </span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                                    <span class="mb-0">Country</span>
                                    <span>{{ ucwords($admin->country) }}</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                                    <span class="mb-0">Time Zone</span>
                                    <span>{{ ucwords($admin->timeZone) }}</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                                    <span class="mb-0">Created At</span>
                                    <span>{{ dateF2($admin->created_at) }}</span>
                                </li>
                            </ul>
                        @endif
                    </div>
                </div>
            </div>

            {{--
            <div class="col-lg-7">
                <div class="card">
                    <div class="card-header">
                        <h5>Subscriptions</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="usersusbscription" class="table dataTable no-footer w-100 dt-responsive">
                                <thead>
                                    <tr>
                                        <th>Plan Name</th>
                                        <th>Amount</th>
                                        <th>Expire At</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            --}}

            <div class="col-lg-12 mt-3">
                <div class="card">
                    <div class="card-header">
                        <h5>Properties</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="adminproperty" class="table">
                                <thead>
                                    <tr>
                                        <th>Created At</th>
                                        <th>Title</th>
                                        <th>Property Address</th>
                                        <th>No. of matched Tenant</th>
                                        <th>Active Requests</th>
                                        {{-- <th>Property Status</th> --}}
                                        <th>Action</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            {{-- <div class="col-lg-12 mt-3">
                <div class="card">
                    <div class="card-header">
                        <h5>Match Properties</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="adminmatchproperty" class="table">
                                <thead>
                                    <tr>
                                        <th>Created At</th>
                                        <th>Tenant</th>
                                        <th>Agent</th>
                                        <th>Title</th>
                                        <th>Property Type</th>
                                        <th>Property Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div> --}}

        </div>
    </div>
    @push('custom-script')
        <script type="text/javascript">
            var role_agency = ("{{ $role == 'agency' ? 1 : 0 }}" == 1) ? true : false;

            // $(document).ready(function () {
            //     const userid = `{{ $admin->id }}`;
            //     var usersusbscriptiontable = $('#usersusbscription').DataTable({
            //         processing: true,
            //         serverSide: true,
            //         ajax: {
            //             url: "{{ route('admin_user.subscribe_list') }}",
            //             data: function (d) {
            //                 if (typeof userid !== 'undefined') {
            //                     d.user_id = userid;
            //                 } else {
            //                     console.error("User ID is not defined.");
            //                 }
            //             }
            //         },
            //         columns: [
            //             {
            //                 data: 'plan_name',
            //                 name: 'plan_name'
            //             },{
            //                 data: 'amount',
            //                 name: 'amount'
            //             },{
            //                 data: 'expired_at',
            //                 name: 'expired_at',
            //                 orderable: true,
            //             },{
            //                 data: 'status',
            //                 name: 'status'
            //             },
            //         ],
            //         order: [
            //             [3, 'desc']
            //         ],
            //         drawCallback: function (settings, json) {
            //             $('[data-toggle=tooltip]').tooltip();
            //         }
            //     });

            var adminpropertyreque = $('#adminproperty').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('adminSubUser.agent.properties.list', $admin->id) }}",
                },
                columns: [
                    {
                        data: 'created_at',
                        name: 'created_at',
                        render: function (data, type, row) {
                            return dateF2(data);
                        }
                    }, {
                        data: 'title',
                        name: 'title'
                    }, {
                        data: 'property_address',
                        name: 'property_address',
                        // render: function (data, type, row, meta) {
                        //     return data.length > 30 ?
                        //     `<span class="short-text">${data.substring(0, 30)}...</span>
                        //         <span class="full-text text-lowercase" style="display:none; line-height:18px;">${data}</span>
                        //         <span class="view-more" style="color: blue; cursor: pointer;">View More</span>` : `<span class="text-lowercase">${data}</span>`;
                        // }
                    }, {
                        data: 'matched_tenant_count',
                        name: 'matched_tenant_count'
                    }, {
                        data: 'active_requests',
                        name: 'active_requests'
                    }, {
                        data: 'action',
                        name: 'action'
                    }
                ],
                drawCallback: function (settings, json) {
                    $('[data-toggle=tooltip]').tooltip();

                    // adminpropertyreque.column(2).visible(role_agency);
                }
            });

            //     var adminpropertyreque = $('#adminmatchproperty').DataTable({
            //         processing: true,
            //         serverSide: true,
            //         ajax: "{{ route('admin_user.match_property_list', $admin->id) }}",
            //         columns: [
            //             {
            //                 data: 'created_at',
            //                 name: 'created_at',
            //                 render: function (data, type, row) {
            //                     return dateF2(data);
            //                 }
            //             }, {
            //                 data: 'tenant',
            //                 name: 'tenant'
            //             },{
            //                 data: 'agent',
            //                 name: 'agent'
            //             },{
            //                 data: 'title',
            //                 name: 'title'
            //             },{
            //                 data: 'property_type',
            //                 name: 'property_type'
            //             },{
            //                 data: 'property_status',
            //                 name: 'property_status'
            //             }, {
            //                 data: 'action',
            //                 name: 'action'
            //             }
            //         ],
            //         drawCallback: function (settings, json) {
            //             $('[data-toggle=tooltip]').tooltip();

            //             adminpropertyreque.column(2).visible(role_agency);
            //         }
            //     });
            // });

        </script>
    @endpush
@endsection
