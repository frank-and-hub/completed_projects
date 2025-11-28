@extends('templates.admin.master')
@php
$dropDown = $company;
$filedTitle = 'Company';
$name = 'company_id';
@endphp
@section('content')
@section('css')
<style>
    .datatable {
        display: none;
    }
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
                                    <label class="col-form-label col-lg-12">From Sanction Date </label>
                                    <div class="col-lg-12 error-msg">
                                        <div class="input-group">
                                            <input type="text" class="form-control  create_application_date" name="start_date" id="start_date" value="{{$startDatee}}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">To Sanction Date </label>
                                    <div class="col-lg-12 error-msg">
                                        <div class="input-group">
                                            <input type="text" class="form-control  create_application_date" name="end_date" id="end_date" value="{{$endDatee}}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">Status </label>
                                    <div class="col-lg-12 error-msg">
                                        <select class="form-control" id="status" name="status">
                                            <option value="">Select status</option>
                                            <option value="0">Pending</option>
                                            <option value="1">Approved</option>
                                            <option value="3">Completed</option>
                                            <option value="4">ONGOING</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            @include('templates.GlobalTempletes.both_company_filter_new',['all'=>true])
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">Loan Type </label>
                                    <div class="col-lg-12 error-msg">
                                        <div class="input-group">
                                            <select class="form-control" id="plan" name="plan">
                                                <option value="">----Select Loan Type----</option>
                                                @foreach( $loan as $val )
                                                <option value="{{ $val->id }}">{{ $val->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">Account Number </label>
                                    <div class="col-lg-12 error-msg">
                                        <input type="text" class="form-control  " name="application_number" id="application_number">
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
                                    <label class="col-form-label col-lg-12">Customer ID </label>
                                    <div class="col-lg-12 error-msg">
                                        <input type="text" name="customer_id" id="customer_id" class="form-control">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group row">
                                    <div class="col-lg-12 text-right">
                                        <input type="hidden" name="associate_report_currentdate" id="associate_report_currentdate" class="create_application_date" value="{{$startDatee}}">
                                        <input type="hidden" name="is_search" id="is_search" value="yes">
                                        <input type="hidden" name="export" id="export" value="">
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
                    <h6 class="card-title font-weight-semibold">Loan Details</h6>
                    <div class="">
                        <button type="button" class="btn bg-dark legitRipple export-loan" data-extension="1">Export Excel</button>
                    </div>
                </div>
                <div class="">
                    <table id="loan_list" class="table datatable-show-all">
                        <thead>
                            <tr>
                                <th>S/N</th>
                                <th>Staus</th>
                                <th>Applicant Name</th>
                                <th>Customer Id</th>
                                <th>Company</th>  
                                <th>Applicant Phone Number</th>
                                <th>Account No.</th>
                                <th>Branch</th>
                                <th>Sector Branch</th>
                                <th>Member Id</th>
                                <th>Sanctioned Amount</th>
                                <th>Transfer Amount</th>
                                <th>Sanctioned Date</th>
                                <th>EMI Amount</th>
                                <th>No. of Installments</th>
                                <th>Loan Mode</th>
                                <th>Loan Type</th>
                                <th>Loan Issued Date</th>
                                <th>Loan Issued Mode</th>
                                <th>Cheque No.</th>
                                <th>Total Recovery Amt</th>
                                <th>Last Recovery Date</th>
                                <th>Closing Amount</th>
                                <th>Balance EMI</th>
                                <th>EMI Should be received till date</th>
                                <th>Due Emi</th>
                                <th>Date</th>
                                <th>Co-Applicant Name</th>
                                <th>Co-Applicant Number</th>
                                <th>Guarantor Name</th>
                                <th>Guarantor Number</th>
                                <th>Applicant Address</th>
                                <th>First EMI Date</th>
                                <th>Loan End Date</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@include('templates.admin.report.partials.loan')
@stop