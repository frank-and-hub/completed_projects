@extends('layout.master')
@section('content')
@section('title', __('PocketProperty | ' . $active_page))
    <div class="content-wrapper">
        <div class="page-header">
            <h3 class="page-title">
                {{ ucwords($title) }}
            </h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#">{{ ucwords($title) }}</a></li>
                    <li class="breadcrumb-item active" aria-current="page">list</li>
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
                        @if ($active_page == 'agency')
                            <div class="text-right">
                                <a href="{{ route('admin_user.agency.create') }}" class="btn  btn-danger  mr-2"><i
                                        class="fa fa-plus">
                                    </i> Add New</a>
                            </div>
                        @endif
                        <!-- {{--<a href="{{ route('add_features') }}"  data-toggle="tooltip" data-original-title="Add New" class="btn  btn-danger  mr-2"><i class="fa fa-plus"> </i> Add New</a>--}} -->
                    </div>
                </div>
                <h4 class="card-title"></h4>
                <div class="row">
                    <div class="col-12">
                        <div class="table-responsive">
                            <table id="AdminTable" class="table">
                                <thead>
                                    <tr>
                                        <th>Created At</th>
                                        <th>Name</th>
                                        <th>Phone</th>
                                        <th>Status</th>
                                        <th>Agency</th>
                                        <th>Total Agent</th>
                                        <th>Total Property</th>
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


    <div class="modal fade" id="requestVerificationPopUp" tabindex="-1" role="dialog"
        aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-backdrop="static">
        <div class="modal-dialog modal-confirm">
            <div class="modal-content">
                <div class="modal-header justify-content-center">
                    <div class="icon-box">
                        <i class="material-icons">&#xE876;</i>
                    </div>
                </div>
                <div class="modal-body text-center">
                    <h4>Request Updated!</h4>
                    <p id="request-verification-message"></p>
                    <button class="btn btn-success" data-dismiss="modal"><span>Ok</span>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="requestTypeAgencies" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
        aria-hidden="true" data-backdrop="static">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form name="requestTypeAgencies-form" id="requestTypeAgencies-form">
                    <div class="modal-header plan_name">
                        <h5 class="modal-title text-center">Are you sure?</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <h6 class="text-danger">Request Verification</h6>
                        <input type="hidden" name="dataId" id="request_verification_dataId">
                        <div class="form-group">
                            <select name="verification_status" id="" class="form-control">
                                <option disabled selected>Select type</option>
                                <option value="accepted">Acccept</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                        <div class="alert alert-success success_msg" role="alert"></div>
                        <div class="alert alert-danger error_msg" role="alert"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn theme_btn_2" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn theme_btn_1">Ok</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('custom-script')
        <script type="text/javascript">
            const agency_of_agent = "{{ $active_page == 'agent' ? true : false }}";
            const agent_count = "{{ $active_page == 'agency' ? true : false }}";
            var table = $('#AdminTable').DataTable({
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
                        orderable: false
                    }, {
                        data: 'phonenumber',
                        name: 'phonenumber',
                        orderable: false,
                    }, {
                        data: 'status',
                        name: 'status',
                        orderable: false
                    }, {
                        data: 'agency',
                        name: 'agency',
                        orderable: false,
                        visible: agency_of_agent
                    }, {
                        data: 'agent_count',
                        name: 'agent_count',
                        orderable: false,
                        visible: agent_count
                    }, {
                        data: 'property_count',
                        name: 'property_count',
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

            const STATUS_UPDATE_ROUTE = "{{ route('admin_user.change_update', $active_page) }}";

            $('.filter_agency_list').change(function () {
                val = $(this).val();
                const urlWithoutParams = window.location.origin + window.location.pathname;
                url = urlWithoutParams + '?requestType=' + val;
                window.location.replace(url);
            });
        </script>
    @endpush
@endsection
