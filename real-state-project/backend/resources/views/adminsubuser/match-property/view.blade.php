@extends('layout.master')
@section('content')
@section('title', __('PocketProperty | ' . ucwords($active_page)))
    <div class="content-wrapper">
        <div class="page-header">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#">{{ ucwords($active_page) }}</a></li>
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
                            <img src="{{ $sentInternalPropertyUser->user->image ? Storage::url($sentInternalPropertyUser->user->image) : asset('assets/default_user.png') }}"
                                alt="Admin" class="rounded-circle p-1 object-fit-cover" width="150" height="150">
                            <div class="mt-3 w-100">
                                <h4>{{ $sentInternalPropertyUser->user->name }}</h4>
                            </div>
                        </div>
                        <ul class="list-group list-group-flush mt-3">
                            <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                                <h6 class="mb-0">Phone</h6>
                                <span>{{ $sentInternalPropertyUser->user->country_code . ' ' . $sentInternalPropertyUser->user->phone }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                                <h6 class="mb-0">Email</h6>
                                <span><a href="https://mail.google.com/mail/?view=cm&fs=1&to={{ $sentInternalPropertyUser->user->email }}"
                                        target="_blank">{{ $sentInternalPropertyUser->user->email }}</a></span>
                            </li>
                            {{-- <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                                <h6 class="mb-0">Registered Date </h6>
                                <span>{{ $sentInternalPropertyUser->user->created_at }}</span>
                            </li> --}}
                            <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                                <h6 class="mb-0">Country </h6>
                                <span>{{ ucwords($sentInternalPropertyUser->user->country) }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                                <h6 class="mb-0">Time Zone</h6>
                                <span>{{ ucwords($sentInternalPropertyUser->user->timeZone) }}</span>
                            </li>
                            {{-- <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                                <h6 class="mb-0">Status</h6>
                                <span>
                                    @if ($sentInternalPropertyUser->user->status == 1)
                                    <button class="btn btn-success btn-rounded">Active</button>
                                    @else
                                    <button class="btn  btn-rounded">Inactive</button>
                                    @endif
                                </span>

                            </li> --}}
                        </ul>
                    </div>
                    {{-- <div class="card-body">
                        <ul class="list-group list-group-flush mt-3">
                            <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                                <h6 class="mb-0">Tenant name</h6>
                                <span><a href="{{ route('user_view', $sentInternalPropertyUser->user_id) }}">{{
                                        ucwords($sentInternalPropertyUser->user->name) }}</a></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                                <h6 class="mb-0">{{ ucwords($sentInternalPropertyUser->admin->roles->first()->name) }}
                                    Name</h6>
                                <span><a
                                        href="{{ route('admin_user.role_type_view', $sentInternalPropertyUser->admin_id) }}">{{
                                        ucwords($sentInternalPropertyUser->admin->name) }}</a></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                                <h6 class="mb-0">Property</h6>
                                <span>
                                    <a
                                        href="{{ route('adminSubUser.property.view', $sentInternalPropertyUser->internal_property_id) }}"><button
                                            class="btn btn-success btn-rounded">View</button></a>
                                </span>
                            </li>
                        </ul>
                    </div> --}}
                </div>
            </div>
            <div class="col-lg-12 mt-3">
                <div class="card">
                    <div class="card-header">
                        <h5>Calendar</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="calendarTable" class="table">
                                <thead>
                                    <tr>
                                        {{-- <th>Sr.No.</th> --}}
                                        <th>Created At</th>
                                        <th>Date Time</th>
                                        <th>Title</th>
                                        <th>Description</th>
                                        <th>Tenant</th>
                                        <th>Agent</th>
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
    @push('custom-script')
        <script type="text/javascript">
            $(document).ready(function () {
                const role = '{{ $role }}';

                var table = $('#calendarTable').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: "{{ route('adminSubUser.calendar.index') }}",
                    columns: [
                        {
                            data: 'created_at',
                            name: 'created_at',
                            render: function (data, type, row) {
                                return dateF2(data);
                            }
                        }, {
                            data: 'event_datetime',
                            name: 'event_datetime',
                            render: function (data, type, row) {
                                return dateF2(data);
                            }
                        }, {
                            data: 'title',
                            name: 'title'
                        }, {
                            data: 'description',
                            name: 'description',
                            render: function (data, type, row, meta) {
                                return data ? (data.length > 30 ?
                                    `<span class="short-text">${data.substring(0, 30)}...</span>
                             <span class="full-text" style="display:none; line-height:18px;">${data}</span>
                             <span class="view-more" style="color: blue; cursor: pointer;">View More</span>` :
                                    data) : '';
                            }
                        }, {
                            data: 'tenant',
                            name: 'tenant'
                        }, {
                            data: 'agent',
                            name: 'agent',
                            visible: "{{ auth()->user()->getRoleNames()->first() == 'agency' }}"
                        }, {
                            data: 'status',
                            name: 'status'
                        }],
                    drawCallback: function (settings, json) {
                        $('#calendarTable').off('click', '.view-more').on('click', '.view-more',
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
@endsection
