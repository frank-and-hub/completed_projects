@extends('templates.admin.master')

@section('content')
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <form action="#" method="post" enctype="multipart/form-data" id="filter_ta_advanced_report" name="filter_ta_advanced_report">
                        @csrf
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">From Date</label>
                                        <div class="col-lg-12 error-msg">
                                                <input type="text" name="date_from" id="date_from" class="form-control date-from" required="">
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">To Date</label>
                                        <div class="col-lg-12 error-msg">
                                                <input type="text" name="date_to" id="date_to" class="form-control date-to" required="">
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Employee Code</label>
                                        <div class="col-lg-12 error-msg">
                                          
                                                <input type="text" name="employee_code" id="ta_employee_code" class="form-control" data-val="ta_advanced" required="">
                                         
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Employee Name</label>
                                        <div class="col-lg-12 error-msg">
                                            
                                                <input type="text" name="ta_advanced_employee_name" id="ta_advanced_employee_name" class="form-control" readonly="" required="">
                                           
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <div class="form-group row"> 
                                        <div class="col-lg-12 text-right" >
                                          <input type="hidden" name="is_search" id="is_search" value="no">
                                          <button type="button" class=" btn bg-dark legitRipple" onClick="searchtaAdvancedReport()" >Submit</button>
                                          <button type="button" class="btn btn-gray legitRipple" id="reset_form" onClick="resettaAdvancedReport()" >Reset </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header header-elements-inline">
                        <h6 class="card-title font-weight-semibold">TA advance / Imprest</h6>
                    </div>
                    <table class="table datatable-show-all" id="ta-advanced-table">
                        <thead>
                            <tr>
                                <th width="5%">S/N</th>
                                <th width="10%">Payment Type</th>
                                <th width="10%">Sub Payment Type</th>
                                <th width="10%">Branch</th>
                                <th width="10%">Employee Code</th>
                                <th width="10%">Employee Name</th>
                                <th width="10%">Advanced Amount</th>
                                <th width="10%">Status</th>
                                <th width="5%">Created at</th>
                                <th width="10%">Action</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop

@section('script')
<script src="{{url('/')}}/asset/js/sweetalert.min.js"></script>
@include('templates.admin.demand-advice.partials.script')
@endsection
