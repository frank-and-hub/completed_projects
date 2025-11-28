@extends('layout.master')
@section('content')
@section('title', __('PocketProperty | Submitted Property Requests'))
    <div class="content-wrapper">
        <div class="page-header">
            <h3 class="page-title">
                Property Needs
            </h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#">{{ ucwords($title) }}</a></li>
                    <li class="breadcrumb-item active" aria-current="page">List</li>
                </ol>
            </nav>
        </div>

        <div class="card mt-3">
            <div class="card-body">
                <h4 class="card-title"></h4>
                <div class="row">
                    <div class="col-12">
                        <div class="table-responsive">
                            <table id="propertyTable" class="table">
                                <thead>
                                    <tr>
                                        {{-- <th>Sr.No.</th> --}}
                                        <th>Created At</th>
                                        <th>User Name</th>
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
            $(document).ready(function () {
                var table = $('#propertyTable').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: "{{ route('submitted_property') }}",
                    columns: [
                        {
                            data: 'created_at',
                            name: 'created_at',
                            render: function (data, type, row) {
                                return dateF2(data);
                            }
                        }, {
                            data: 'user_name',
                            name: 'user_name'
                        }, {
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
                    drawCallback: function (settings, json) {
                        $('[data-toggle=tooltip]').tooltip();
                    }
                });
            });
        </script>
    @endpush
@endsection
