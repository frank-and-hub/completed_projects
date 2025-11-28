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
                    <li class="breadcrumb-item"><a href="{{ route('user_list') }}">{{ ucwords($active_page) }} List</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Details</li>
                </ol>
            </nav>
        </div>
        <div class="row">
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-body">
                        {{-- <div class="d-flex flex-column align-items-center text-center">
                            <img src="{{ $sentInternalPropertyUser->user?->image ? Storage::url($sentInternalPropertyUser->user->image) : asset('assets/default_user.png') }}"
                                alt="" class="rounded-circle p-1 object-fit-cover" width="150" height="150">
                            <div class="mt-3 w-100">
                                <h4>{{ucwords($sentInternalPropertyUser->user->name)}}</h4>
                            </div>
                        </div> --}}
                        <ul class="list-group list-group-flush mt-3">
                            <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                                <h6 class="mb-0">Tenant name</h6>
                                <span><a
                                        href="{{ route('user_view', $sentInternalPropertyUser->user_id) }}">{{ ucwords($sentInternalPropertyUser->user->name) }}</a></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                                <h6 class="mb-0">{{ ucwords($sentInternalPropertyUser->admin->roles->first()->name) }}
                                    Name</h6>
                                <span><a
                                        href="{{ route('admin_user.role_type_view', $sentInternalPropertyUser->admin_id) }}">{{ ucwords($sentInternalPropertyUser->admin->name) }}</a></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                                <h6 class="mb-0">Property</h6>
                                <span><a
                                        href="{{ route('property.view', $sentInternalPropertyUser->internal_property_id) }}"><button
                                            class="btn btn-success btn-rounded">View</button></a></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                                <h6 class="mb-0">Property Needs</h6>
                                <span><a
                                        href="{{ route('submitted_property_view', $sentInternalPropertyUser->search_id) }}"><button
                                            class="btn btn-success btn-rounded">View</button></a></span>
                            </li>
                        </ul>
                    </div>
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
                                        <th>Agent/Landlord</th>
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
                var calendarTable = $('#calendarTable').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: "{{ route('calendar.index', ['sent_internal_property_user_id' => $sentInternalPropertyUser->id]) }}",
                    columns: [
                        {
                            data: 'created_at',
                            name: 'created_at',
                            render: function (data, type, row) {
                                return dateF2(data);
                            }
                        }, {
                            data: 'date_time',
                            name: 'date_time'
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
                            name: 'agent'
                        }, {
                            data: 'status',
                            name: 'status'
                        }
                    ],
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
