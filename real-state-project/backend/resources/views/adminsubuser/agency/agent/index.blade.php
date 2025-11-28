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
                            <a href="{{ route('adminSubUser.agent.create') }}" data-toggle="tooltip"
                                data-original-title="Add New" class="btn btn-danger mr-2 extra_btn"><i class="fa fa-plus">
                                </i>Add New</a>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="table-responsive">
                            <table id="AgentTable" class="table">
                                <thead>
                                    <tr>
                                        <th>Agent Name</th>
                                        <th>Managed Properties</th>
                                        <th>Number of tenants</th>
                                        <th>Active Requests</th>
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

    <div class="modal fade" id="tableModel" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
        aria-hidden="true" data-backdrop="static">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div id="">
                    <div class="modal-header plan_name">
                        <div class="row">
                            <h5 class="modal-title col-md-10" id="model_title"></h5>
                            <button type="button" class="close col-2" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    </div>
                    <div class="modal-body">
                        <div>
                        </div>
                        <div class="row" id=model_table>
                            <div class="col-12">
                                <div class="table-responsive">
                                    <table id="table_model" class="table">

                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('custom-script')
        <script type="text/javascript">
            const request_type = "{{ $active_page == 'agency' ? true : false }}";
            var table = $('#AgentTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: window.location.href,
                columns: [
                    {
                        data: 'name',
                        name: 'name',
                        orderable: false
                    }, {
                        data: 'properties_count',
                        name: 'properties_count',
                        orderable: false,
                    }, {
                        data: 'tenant_count',
                        name: 'tenant_count',
                        orderable: false,
                    }, {
                        data: 'active_requests',
                        name: 'active_requests',
                        orderable: false
                    },
                    {
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

            const modelUrl = "{{ route('adminSubUser.agent.datatable.model') }}";
            let dataTableInstance = null;
            $(document).on('click', '._model_table', function () {
                const type = $(this).data('type');
                const agentId = $(this).data('agent_id');
                const modelTitle = $(this).data('model_title');

                $('#model_title').text(modelTitle);
                // Dynamically build the table header based on type
                let tableHeader = '<thead><tr><th>Date</th><th>Name</th>';

                if (type === 'active_requests') {
                    tableHeader += '<th>Date/Time</th>';
                } else if (type === 'properties_count') {
                    tableHeader += '<th>Action</th>';
                } else if (type === 'tenant_count') {
                    tableHeader += '<th>Phone</th>';
                }

                tableHeader += '</tr></thead>';

                $('#model_table').html(`<table id="table_model" class="table" style="width: 100%;">${tableHeader}</table>`);

                if (dataTableInstance) {
                    dataTableInstance.destroy();
                }

                let columns = getColumnsByType(type);

                dataTableInstance = $('#table_model').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: modelUrl,
                        type: 'GET',
                        data: {
                            type: type,
                            agent_id: agentId
                        }
                    },
                    columns: columns,
                    order: [[0, 'desc']],
                    drawCallback: function () {
                        $('[data-toggle=tooltip]').tooltip();
                        // Optional: toggle full name
                        $('.view-more').on('click', function () {
                            const row = $(this).closest('td');
                            row.find('.short-text').hide();
                            row.find('.full-text').removeClass('d-none').show();
                            $(this).hide();
                        });
                    }
                });

                $('#tableModel').modal('show');
                $('#tableModel').on('shown.bs.modal', function () {
                    $(this).find('.modal-dialog').css({
                        'max-width': '50%',
                    });
                });
            });

            function getColumnsByType(type) {
                const baseColumns = [
                    {
                        data: 'created_at',
                        name: 'created_at',
                        width: '20%',
                        render: function (data) {
                            return dateF2(data);
                        }
                    },
                    {
                        data: 'name',
                        name: 'name',
                        width: '40%',
                        render: function (data) {
                            if (!data) return '-';
                            return data.length > 30
                                ? `<span class="short-text">${data.substring(0, 30)}...</span>
                                                <span class="full-text d-none">${data}</span>
                                                <span class="view-more text-primary" style="cursor: pointer;">View More</span>`
                                : data;
                        }
                    }
                ];

                const columnExtras = {
                    active_requests: [
                        {
                            data: 'event_datetime',
                            name: 'event_datetime',
                            orderable: false,
                            width: '40%',
                            render: function (data) {
                                return dateF2(data);
                            }
                        }
                    ],
                    properties_count: [
                        {
                            data: 'action',
                            name: 'action',
                            width: '40%',
                            orderable: false
                        }
                    ],
                    tenant_count: [
                        {
                            data: 'phone',
                            name: 'phone',
                            width: '40%',
                            orderable: true
                        }
                    ]
                };

                return [...baseColumns, ...(columnExtras[type] || [])];
            }


        </script>
    @endpush
@endsection
