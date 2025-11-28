@extends('layout.master')
@section('content')
@section('title', __('PocketProperty | Property Api'))
    <div class="content-wrapper">
        <div class="page-header">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#">{{ ucwords($title) }}</a></li>
                    <li class="breadcrumb-item active" aria-current="page">List</li>
                </ol>
            </nav>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="row d-flex justify-content-between align-items-center">
                    <div class="row d-flex justify-content-between align-items-center">
                        <div class="mb-3">
                        </div>
                    </div>
                    <div class="text-right d-flex">
                        <div class="text-right">
                            <a href="{{ route('api_properties.properties.index') }}" data-toggle="tooltip"
                                data-original-title="Api Link" class="btn btn-secondary" target="blank">API Link</a>
                            <a href="{{ route('external_property_users.create') }}" data-toggle="tooltip"
                                data-original-title="Add New" class="btn  btn-danger  mr-2"><i class="fa fa-plus">
                                </i>
                                Add New</a>
                        </div>
                    </div>
                </div>
                <h4 class="card-title"></h4>
                <div class="row">
                    <div class="col-12">
                        <div class="table-responsive">
                            <table id="APITable" class="table">
                                <thead>
                                    <tr>
                                        {{-- <th>Sr.No.</th> --}}
                                        <th>Created At</th>
                                        <th>Agency</th>
                                        <th>Name</th>
                                        <th>Country</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                        {{-- <th>Phone</th> --}}
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
            var table = $('#APITable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('external_property_users.index') }}",
                columns: [
                    {
                        data: 'created_at',
                        name: 'created_at',
                        orderable: true,
                        render: function (data, type, row) {
                            return dateF2(data);
                        }
                    }, {
                        data: 'agency',
                        name: 'agency',
                        orderable: false
                    }, {
                        data: 'name',
                        name: 'name',
                        orderable: false
                    }, {
                        data: 'country',
                        name: 'country',
                        orderable: false
                    }, {
                        data: 'status',
                        name: 'status',
                        orderable: false
                    }, {
                        data: 'action',
                        name: 'action',
                        orderable: false
                    }],
                drawCallback: function (settings, json) {
                    $('[data-toggle=tooltip]').tooltip();
                }
            });
            var STATUS_UPDATE_ROUTE = "{{ route('change_update') }}"
        </script>
    @endpush
@endsection
