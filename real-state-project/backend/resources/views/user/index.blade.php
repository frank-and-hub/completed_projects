@extends('layout.master')
@section('content')
@section('title', __('PocketProperty | Users'))
    <div class="content-wrapper">
        <div class="page-header">
            <h3 class="page-title">
                Users
            </h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#">{{ ucwords($title) }}</a></li>
                    <li class="breadcrumb-item active" aria-current="page">List</li>
                </ol>
            </nav>
        </div>
        <div class="card">
            <div class="card-body">
                <h4 class="card-title"></h4>
                <div class="row">
                    <div class="col-12">
                        <div class="table-responsive">
                            <table id="UserTable" class="table">
                                <thead>
                                    <tr>
                                        {{-- <th>Sr.No.</th> --}}
                                        <th>Created At</th>
                                        <th>Name</th>
                                        <th>Phone</th>
                                        <th>Status</th>
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
            var table = $('#UserTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('user_list') }}",
                columns: [
                    {
                        data: 'created_at',
                        name: 'created_at',
                        orderable: true,
                        render: function (data, type, row) {
                            return dateF2(data);
                        }
                    }, {
                        data: 'name',
                        name: 'name',
                        orderable: false
                    }, {
                        data: 'phonenumber',
                        name: 'phonenumber',
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
