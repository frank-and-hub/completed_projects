@extends('templates.admin.master')
@section('content')
@section('css')
<style>
    .datatable {
        display: none;
    };
</style>
@endsection
<?php
$startDatee = (checkMonthAvailability(date('d'), date('m'), date('Y'), 33));
$startDatee = $endDatee = date('d/m/Y', strtotime($startDatee));
?>
<div class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header header-elements-inline">
                    <h6 class="card-title font-weight-semibold">Search Filter</h6>
                </div>
                <div class="card-body">
                    <form action="#" method="post" enctype="multipart/form-data" id="filter" name="filter">
                        @csrf
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">From Date </label>
                                    <div class="col-lg-12 error-msg">
                                        <input type="hidden" class="form-control  " name="from_date" id="from_date">
                                        <input type="text" class="form-control  create_application_date" name="start_date" id="start_date" value="{{$startDatee}}">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">To Date </label>
                                    <div class="col-lg-12 error-msg">
                                        <input type="text" class="form-control  create_application_date" name="end_date" id="end_date" value="{{$endDatee}}">
                                    </div>
                                </div>
                            </div>
                            @include('templates.GlobalTempletes.both_company_filter_new',['all'=>true])
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">Plans </label>
                                    <div class="col-lg-12 error-msg">
                                        <select class="form-control" id="plan_id" name="plan_id">
                                            <option value="">Please Choose Company</option>
                                            @foreach($plans as $k => $v)
                                                <option value="{{$k}}">{{$v}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">Scheme Account Number </label>
                                    <div class="col-lg-12 error-msg">
                                        <input type="text" class="form-control  " name="scheme_account_number" id="scheme_account_number">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">Member Name </label>
                                    <div class="col-lg-12 error-msg">
                                        <input type="text" class="form-control  " name="member_name" id="member_name">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">Member ID </label>
                                    <div class="col-lg-12 error-msg">
                                        <input type="text" name="member_id" id="member_id" class="form-control">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">Associate code </label>
                                    <div class="col-lg-12 error-msg">
                                        <input type="text" name="associate_code" id="associate_code" class="form-control">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">Status </label>
                                    <div class="col-lg-12 error-msg">
                                        <select class="form-control" id="status" name="status">
                                            <option value="">Select status</option>
                                            <option value="0">Upcoming</option>
                                            <option value="1">Redemption</option>
                                            <option value="2">Over Due</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group row">
                                    <div class="col-lg-12 text-right">
                                        <input type="hidden" name="associate_report_currentdate" id="associate_report_currentdate" class="create_application_date" value="{{$startDatee}}">
                                        <input type="hidden" name="is_search" id="is_search" value="yes">
                                        <input type="hidden" name="export" id="export">
                                        <button type="button" class=" btn bg-dark legitRipple" onClick="searchForm()">Submit</button>
                                        <button type="button" class="btn btn-gray legitRipple" id="reset_form" onClick="resetForm()">Reset </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-12 table-section datatable">
            <div class="card">
                <div class="card-header header-elements-inline">
                    <h6 class="card-title font-weight-semibold">Maturity Details</h6>
                    <div class="">
                        <button type="button" class="btn bg-dark legitRipple export-maturity ml-2" data-extension="0" style="float: right;">Export Excel</button>
                    </div>
                </div>
                <div class="">
                    <table id="maturity_list" class="table datatable-show-all">
                        <thead>
                            <tr>
                                <th>S/N</th>
                                <th>Company Name</th>
                                <th>Branch Name</th>
                                <th>Account No.</th>
                                <th>Customer ID</th>
                                <th>Member ID</th>
                                <th>Member Name</th>
                                <th>Plan </th>
                                <th>Tenure</th>
                                <th>Deposit Amount</th>
                                <th>Deno</th>
                                <th>Maturity Type</th>
                                <th>Maturity Amount</th>
                                <th>Maturity Payable Amount</th>
                                <th>Maturity Date</th>
                                <th>Associate code</th>
                                <th>Associate Name</th>
                                <th>Opening Date</th>
                                <th>Due Amount</th>
                                <th>Interest</th>
                                <th>TDS Amount</th>
                                <th>Final Payable Amount</th>
                                <th>Payment Mode</th>
                                <th>Payment Date</th>
                                <th>Cheque No./RTGS No.</th>
                                <th>RTGS Charge</th>
                                <th>SSB Account No.</th>
                                <th>Bank Name</th>
                                <th>Bank Account Number</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@include('templates.admin.report.partials.new_maturity')
@stop