@extends('layout.master')
@section('content')
@section('title', __('PocketProperty | Users'))
<div class="content-wrapper">
    <div class="page-header">
        <h3 class="page-title">
            User Details
        </h3>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">{{ ucwords($title) }}</a></li>
                <li class="breadcrumb-item"><a href="{{ route('user_list') }}">List</a></li>
                <li class="breadcrumb-item active" aria-current="page">Details</li>
            </ol>
        </nav>
    </div>
    <div class="row">
        <div class="col-lg-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex flex-column align-items-center text-center">
                        <img src="{{ $user->image ? Storage::url($user->image) : asset('assets/default_user.png') }}"
                            alt="Admin" class="rounded-circle p-1 object-fit-cover" width="150" height="150">
                        <div class="mt-3 w-100">
                            <h4>{{ $user->name }}</h4>
                        </div>
                    </div>
                    <ul class="list-group list-group-flush mt-3">
                        <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                            <h6 class="mb-0">Phone</h6>
                            <span>{{ $user->country_code . ' ' . $user->phone }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                            <h6 class="mb-0">Email</h6>
                            <span><a href="https://mail.google.com/mail/?view=cm&fs=1&to={{ $user->email }}"
                                    target="_blank">{{ $user->email }}</a></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                            <h6 class="mb-0">Created At</h6>
                            <small>{{ $user->__created_at }}</small>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                            <h6 class="mb-0">Country </h6>
                            <span>{{ $user->country ? ucwords($user->country) : 'N/A' }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                            <h6 class="mb-0">Employment </h6>
                            <span>{{ $user?->user_employment ? ucwords($user?->user_employment?->emplyee_type) : 'N/A' }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                            <h6 class="mb-0">Live With </h6>
                            <span>{{ $user?->user_employment ? ucwords($user?->user_employment?->live_with) . ' Peoples': 'N/A' }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                            <h6 class="mb-0">Time Zone</h6>
                            <span>{{ ucwords($user->timeZone) }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                            <h6 class="mb-0">Status</h6>
                            <span>
                                @if ($user->status == 1)
                                    <button class="btn btn-success btn-rounded">Active</button>
                                @else
                                    <button class="btn  btn-rounded">Inactive</button>
                                @endif
                            </span>

                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5>Subscriptions</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="usersusbscription" class="table dataTable no-footer w-100 dt-responsive">
                            <thead>
                                <tr>
                                    {{-- <th>Sr.No.</th> --}}
                                    <th>Name</th>
                                    <th>Amount</th>
                                    <th>Start date</th>
                                    <th>Expire date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-12 mt-3">
            <div class="card">
                <div class="card-header">
                    <h5>Properties Request</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="userpropertyrequest" class="table" style="">
                            <thead>
                                <tr>
                                    {{-- <th>Sr.No.</th> --}}
                                    <th>Created At</th>
                                    <th>Country Name</th>
                                    <th>Province Name</th>
                                    <th>Suburb Name</th>
                                    <th>Property Type</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@push('custom-script')
    <script type="text/javascript">
        $(document).ready(function() {
            var usersusbscriptiontable = $('#usersusbscription').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('user_subscription', $user->id) }}",
                columns: [
                    {
                        data: 'subs_name',
                        name: 'subs_name'
                    }, {
                        data: 'amount',
                        name: 'amount'
                    }, {
                        data: 'started_at',
                        name: 'started_at'
                    }, {
                        data: 'expired_at',
                        name: 'expired_at'
                    }, {
                        data: 'status',
                        name: 'status'
                    }
                ],
                order: [
                    [3, 'asc']
                ],
                drawCallback: function(settings, json) {
                    $('[data-toggle=tooltip]').tooltip();
                }
            });
            var userpropertyrequesttable = $('#userpropertyrequest').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('user_property_request', $user->id) }}",
                columns: [
                {
                    data: 'created_at',
                    name: 'created_at',
                    render: function (data, type, row) {
                        return dateF2(data);
                    }
                },{
                    data: 'country',
                    name: 'country'
                }, {
                    data: 'province_name',
                    name: 'province_name'
                }, {
                    data: 'suburb_name',
                    name: 'suburb_name'
                }, {
                    data: 'property_type',
                    name: 'property_type'
                }, {
                    data: 'action',
                    name: 'action'
                }],
                drawCallback: function(settings, json) {
                    $('[data-toggle=tooltip]').tooltip();
                }
            });
        });
    </script>
@endpush
@endsection
