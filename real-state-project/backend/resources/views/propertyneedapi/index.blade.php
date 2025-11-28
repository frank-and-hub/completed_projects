@extends('layout.master')
@section('content')
@section('title', __('PocketProperty | ' . $active_page))
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
                    <div class="mb-3">
                        <h4 class="card-title"></h4>
                    </div>
                    <div class="text-right d-flex">
                        <div class="text-right">
                            <a href="{{ route('listings') }}" class="btn btn-secondary" target="blank">API Link</a>
                            <a href="{{ route('property-need-api-user.create') }}" class="btn  btn-danger  mr-2"><i
                                    class="fa fa-plus" data-toggle="tooltip" data-original-title="Add New">
                                </i>
                                Add New</a>
                        </div>
                    </div>
                </div>
                {{-- <h4 class="card-title">{{ ucwords($title) }} list</h4> --}}
                <div class="row">
                    <div class="col-12">
                        <div class="table-responsive">
                            <table id="ApiUserTable" class="table">
                                <thead>
                                    <tr>
                                        {{-- <th>Sr.No.</th> --}}
                                        <th>Created At</th>
                                        <th>Name</th>
                                        <th>Country</th>
                                        <th>Agency</th>
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
            const request_type = "{{ $active_page == 'agency' ? true : false }}";
            var table = $('#ApiUserTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: window.location.href,
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
                        orderable: false,
                    }, {
                        data: 'country',
                        name: 'country',
                        orderable: false,
                    }, {
                        data: 'agency',
                        name: 'agency',
                        orderable: false,
                    }, {
                        data: 'action',
                        name: 'action',
                        orderable: false
                    }
                ],
                drawCallback: function (settings, json) {
                    $('[data-toggle=tooltip]').tooltip();
                }
            });

            const STATUS_UPDATE_ROUTE = "{{ route('adminSubUser.agent.status') }}";

            $('.filter_agency_list').change(function () {
                val = $(this).val();
                const urlWithoutParams = window.location.origin + window.location.pathname;
                url = urlWithoutParams + '?requestType=' + val;
                window.location.replace(url);
            });
        </script>
    @endpush
@endsection
