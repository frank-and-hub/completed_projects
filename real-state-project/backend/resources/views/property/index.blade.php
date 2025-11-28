@extends('layout.master')
@section('content')
@section('title', __('PocketProperty | Property'))
    <div class="content-wrapper">
        <div class="page-header">
            <h3 class="page-title">
                Property
            </h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#">{{ucwords($title)}}</a></li>
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
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="table-responsive">
                            <table id="adminproperty" class="table">
                                <thead>
                                    <tr>
                                        <th>Created At</th>
                                        <th>Title</th>
                                        <th>Agent/Private Landlord</th>
                                        <th>Country</th>
                                        <th>Province</th>
                                        <th>Town</th>
                                        <th>Suburb</th>
                                        <th>Property Type</th>
                                        <th>Property Status</th>
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
                var adminpropertyreque = $('#adminproperty').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: "{{ route('property.list') }}",
                    columns: [
                        {
                            data: 'created_at',
                            name: 'created_at',
                            render: function (data, type, row) {
                                return dateF2(data);
                            }
                        },{
                            data: 'title',
                            name: 'title',
                            searchable: true,
                            render: function (data, type, row, meta) {
                                return data.length > 30 ?
                                    `<span class="short-text">${data.substring(0, 30)}...</span>
                                     <span class="full-text" style="display:none; line-height:18px;">${data}</span>
                                     <span class="view-more" style="color: blue; cursor: pointer;">View More</span>` :
                                    data;
                            }
                        },{
                            data: 'agent',
                            name: 'agent'
                        },{
                            data: 'country',
                            name: 'country',
                            orderable: false,
                            searchable: true
                        }, {
                            data: 'province',
                            name: 'province',
                            orderable: false,
                            searchable: true
                        }, {
                            data: 'town',
                            name: 'town',
                            orderable: false,
                            searchable: true
                        }, {
                            data: 'suburb',
                            name: 'suburb',
                            orderable: false,
                            searchable: true
                        },{
                            data: 'property_type',
                            name: 'property_type'
                        },{
                            data: 'property_status',
                            name: 'property_status'
                        },{
                            data: 'action',
                            name: 'action'
                        }
                    ],
                    drawCallback: function (settings, json) {
                        $('[data-toggle=tooltip]').tooltip();
                    }
                });
            });
        </script>
    @endpush
@endsection
