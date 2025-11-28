@extends('layout.master')
@section('content')
@section('title', __('PocketProperty | Property'))
    <div class="content-wrapper">
        <div class="page-header">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#">Properties</a></li>
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
                    <div class="text-right">
{{--
                        @if ($user->selected_agency)
                            <a href="{{ $user->selected_agency_api_status != 1 ? route('adminSubUser.property.agency_status', ['id' => Auth::user()->id]) : 'javascript:void(0)' }}"
                                class="btn mr-2" style="background-color: #5294e2 !important; color: white !important;">
                                <i class="fa fa-map"> </i> Allowed Property
                            </a>
                        @endif
                         --}}
                        <a href="#" class="btn  btn-danger  mr-2 add_property" data-toggle="tooltip"
                            data-original-title="Add New"><i class="fa fa-plus"> </i> Add New</a>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="table-responsive">
                            <table id="adminsubuser-property" class="table">
                                <thead>
                                    <tr>
                                        {{-- <th>Sr.No.</th> --}}
                                        <th>Created At</th>
                                        <th>Title</th>
                                        <th>Country</th>
                                        <th>Province</th>
                                        <th>Town</th>
                                        <th>Suburb</th>
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
            $(document).on('click', '.add_property', function (e) {
                e.preventDefault(); // Prevent the default link action

                $.ajax({
                    url: "{{ route('adminSubUser.check_plan_is_exists') }}",
                    type: "get",
                    success: function (response) {
                        if (response.status === 'success') {
                            subscription = response.data.is_plan_running;
                            if (subscription == 1) {
                                window.location.href = "{{ route('adminSubUser.property.add') }}";
                            } else {
                                subscriptionAdminModel();
                            }
                        }
                    },
                    error: function (xhr, status, error) {
                        console.error("AJAX Error: ", status, error);
                    }
                });
            });

            STATUS_UPDATE_ROUTE = `{{ route('adminSubUser.property.status') }}`;
            STATUS_UPDATE_ROUTE_CONTRACT = `{{ route('adminSubUser.property.status_contract') }}`;

            var table = $('#adminsubuser-property').DataTable({
                processing: true,
                serverSide: true,
                ajax: window.location.href,
                columns: [{
                    data: 'created_at',
                    name: 'created_at',
                    orderable: true,
                    render: function (data, type, row) {
                        return dateF2(data);
                    }
                }, {
                    data: 'title',
                    name: 'title',
                    render: function (data, type, row, meta) {
                        return data.length > 30 ?
                            `<span class="short-text">${data.substring(0, 30)}...</span>
                            <span class="full-text" style="display:none; line-height:18px;">${data}</span>
                            <span class="view-more" style="color: blue; cursor: pointer;">View More</span>` :
                            data;
                    }
                }, {
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
                }, {
                    data: 'action',
                    name: 'action',
                    orderable: false
                }],
                order: [
                    [3, 'desc']
                ],
                drawCallback: function (settings, json) {
                    $('[data-toggle=tooltip]').tooltip();
                }
            });

            $(document).on('change', '.changeStatusProperty', function () {
                // alert(1);
                var $this = $(this);
                var previousStatus = $this.prop('checked');
                var dataStatus = ($this.is(':checked')) ? 'unblock' : 'block';
                var dataId = $this.data('id');
                var dataTable = $this.data('datatable');
                Swal.fire({
                    title: `Are you sure?`,
                    text: 'You want to ' + dataStatus + ' Property!',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    allowOutsideClick: false,
                    confirmButtonText: 'Yes, ' + dataStatus + ' it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        statusAjaxCall(STATUS_UPDATE_ROUTE, dataId, dataStatus, dataTable);
                    } else if (result.dismiss === Swal.DismissReason.cancel) {
                        $this.prop('checked', (previousStatus == false));
                    }
                });
            });

            $(document).on('change', '.changeContractProperty', function () {
                var $this = $(this);
                var previousStatus = $this.prop('checked');
                var dataStatus = ($this.is(':checked')) ? 'unblock' : 'block';
                var dataId = $this.data('id');
                var dataTable = $this.data('datatable');
                Swal.fire({
                    title: `Are you sure?`,
                    text: `You want to ${dataStatus} Property! this property has a contract attached to it.`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    allowOutsideClick: false,
                    confirmButtonText: 'Yes, ' + dataStatus + ' it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        statusAjaxCall(STATUS_UPDATE_ROUTE_CONTRACT, dataId, dataStatus, dataTable);
                    } else if (result.dismiss === Swal.DismissReason.cancel) {
                        $this.prop('checked', (previousStatus == false));
                    }
                });
            });

            function statusAjaxCall(url, dataId, dataStatus, dataTable) {
                $.post(url, {
                    'dataId': dataId,
                    'datastatus': dataStatus
                }, function (response) {
                    if (response.status) {
                        if (response.type == 1) {
                            Swal.fire(
                                'Unblock!', response.msg, 'success'
                            );
                        } else {
                            Swal.fire(
                                'Block', response.msg, 'success'
                            );
                        }
                    } else {
                        Swal.fire(
                            'Oops !', response.msg, 'error'
                        );
                        if (previousStatus == false) {
                            $this.prop('checked', true);
                        } else {
                            $this.prop('checked', false);
                        }
                    }
                    $('#' + dataTable).DataTable().ajax.reload();
                }, 'JSON').fail(function (xhr, status, error) {
                    Swal.fire(
                        'Error',
                        'Status process encountered an error. Your file is safe :)',
                        'error'
                    );
                });
            }
        </script>
    @endpush
@endsection
