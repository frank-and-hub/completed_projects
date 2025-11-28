@extends('layouts/branch.dashboard')
@section('content')

@if(session('success'))
<div class="alert alert-success">
    {{ session('success') }}
</div>
@endif

@if(session('error'))
<div class="alert alert-danger">
    {{ session('error') }}
</div>
@endif
<div class="container-fluid mt--6">
    <div class="row">
        <div class="col-lg-12">
            @if ($errors->any())
            <div class="col-md-12">
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
            @endif
            <div class="row">
                <div class="col-lg-12">
                    <div class="card bg-white">
                        <div class="card-body">
                            <h3 class="card-title mb-3 maintital">Advance Request List</h3>
                        </div>
                    </div>
                </div>
                <!-- Fillters -->
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header header-elements-inline">
                            <h3 class="card-title font-weight-semibold">Search Filter</h3>
                        </div>
                        <div class="card-body">
                            <form action="#" method="post" enctype="multipart/form-data" id="filter" name="filter">
                                @csrf
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-12">Start Date </label>
                                            <div class="col-lg-12 error-msg">
                                                <div class="input-group">
                                                    <input type="text" class="form-control  " name="start_date" id="start_date" autocomplete="off">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-12">End Date </label>
                                            <div class="col-lg-12 error-msg">
                                                <div class="input-group">
                                                    <input type="text" class="form-control  " name="end_date" id="end_date" autocomplete="off">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-12">Company<sup class="required">*</sup></label>
                                            <div class="col-lg-12 error-msg">
                                                <select name="company_id" id="company_id" class="form-control" aria-invalid="false">
                                                    <option value="0">All Company</option>
                                                    @foreach($company as $c_name){
                                                    <option value="{{$c_name['get_company']['id']}}">{{$c_name['get_company']['name']}}</option>
                                                }
                                                    @endforeach
                                                </select>
                                                <div class="input-group">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-12">Advance type</label>
                                            <div class="col-lg-12 error-msg">
                                                <select name="paymentType" id="paymentType" class="form-control" aria-invalid="false">
                                                    <option value="">Please Select</option>
                                                    <option value="0">Advance Rent</option>
                                                    <option value="1">Advance Salary</option>
                                                    <option value="2">TA advanced/Imprest</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-12">Status</label>
                                            <div class="col-lg-12 error-msg">
                                                <select name="status" id="status" class="form-control">
                                                    <option value="">Please Select</option>
                                                    <option value="0">Pending</option>
                                                    <option value="1">Paid</option>
                                                    <option value="2">Rejected</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-12">Settlement Status</label>
                                            <div class="col-lg-12 error-msg">
                                                <select name="settlement" id="settlement" class="form-control">
                                                    <option value="">Please Select</option>
                                                    <option value="1">Fully Settled</option>
                                                    <option value="0">Pending</option>
                                                    <option value="2">Partially Settled</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group row">
                                            <div class="col-lg-12 text-right">
                                                <input type="hidden" name="is_search" id="is_search" value="yes">
                                                <input type="hidden" name="member_export" id="member_export" value="">
                                                <button type="button" class=" btn btn-primary legitRipple" onClick="searchForm()">Submit</button>
                                                <button type="button" class="btn btn-gray legitRipple" id="reset_form" onClick="resetForm()">Reset </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="card bg-white flisting" style="display:none;">
                    <div class="row">
                        <div class="col-md-12 table-section datatable">
                            <div class="card bg-white shadow">
                                <div class="card-header bg-transparent">
                                    <div class="row">
                                        <div class="col-md-8">
                                            <h3 class="mb-0 text-dark">Advance Request List</h3>
                                        </div>
                                        <div class="col-md-4 text-right">
                                            <div class="">
                                                <button type="button" class="btn btn-primary legitRipple export-req ml-2" data-extension="0" style="float: right;">Export xslx</button>
                                                <!-- <button type="button" class="btn bg-dark legitRipple export" data-extension="1">Export PDF</button>-->
                                            </div>
                                            <input type="hidden" class="form-control created_at " name="created_at" id="created_at">
                                            <input type="hidden" class="form-control create_application_date " name="create_application_date" id="create_application_date">
                                        </div>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table id="Advance_request" class="table datatable-show-all table table-flush dataTable no-footer">
                                        <thead>
                                            <tr>
                                                <th>S/N</th>
                                                <th>Branch Name</th>
                                                <th>Requested Date</th>
                                                <th>Type</th>
                                                <!-- <th>Sub Type</th> -->
                                                <th>Name</th>
                                                <th>Request Amount</th>
                                                <th>Description</th>
                                                <th>File</th>
                                                <th>Status</th>
                                                <th>Advance Date</th>
                                                <th>Advance Amount</th>
                                                <th>Settled Amount</th>
                                                <th>Return/Excess Amount</th>
                                                <th>Settlement Status</th>
                                                <!-- <th>Status Remark</th> -->
                                                <th>Company Name</th>
                                                <th>User</th>
                                                <!-- <th>Created By Id</th> -->
                                                <!-- <th>Updated At</th> -->
                                                <th class="text-center">Action</th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Reject Model -->
    <div class="modal fade" id="remark" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title float-start">Reject Request</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="remarkform" action="" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="container form-group mt-2">
                        <input class="form-control" type="text" name="remark" placeholder="Remark">
                        <input type="hidden" name="created_at" class="created_at" id="created_at">
                    </div>

                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Save changes</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script>
        // set the url value in remark form

        $(document).ready(function() {
            $(document).on("click", ".remark", function() {
                const url = $(this).data('url');
                const id = $(this).data('id');
                $('#remarkform').attr('action', url);
            });
        });
    </script>


    @stop

    @section('script')
    @include('templates.branch.AdvancePayment.partials.script_list')

    @stop