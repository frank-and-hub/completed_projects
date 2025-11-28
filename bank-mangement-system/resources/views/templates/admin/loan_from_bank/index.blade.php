@extends('templates.admin.master')
@section('content')
<?php
$startDatee = '';
?>
<div class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header header-elements-inline">
                    <h6 class="card-title font-weight-semibold">Filter</h6>
                    <a class="font-weight-semibold" href="{!! route('admin.create.loan-from-bank') !!}"><i class="icon-file-plus mr-2"></i>Loan From Bank</a>
                </div>
                <div class="card-body">
                    <form action="#" method="post" enctype="multipart/form-data" id="filter" name="filter" novalidate="novalidate">
                        <div class="row">
                        @include('templates.GlobalTempletes.both_company_filter_new',['all'=>true])
                            <div class="col-md-12">
                                <div class="form-group text-right">
                                    <div class="col-lg-12 page">
                                        <button type="button" class="btn bg-dark legitRipple" onclick="searchLoanForm()">Submit</button>
                                        <input type="hidden" name="is_search" id="is_search" value="no">
                                        <input type="hidden" name="export" id="export">
                                        <button type="button" class="btn btn-gray legitRipple" id="reset_form" onclick="resetLoanForm()">Reset </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="row loan_list_report d-none">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header header-elements-inline">
                    <h6 class="card-title font-weight-semibold">Loan From Bank List</h6>
                    <div class="">
                        <button type="button" class="btn bg-dark legitRipple loan_export ml-2" data-extension="0" style="float: right;">Export xslx</button>
                    </div>
                </div>
                <div class="">
                    <table id="loan_from_bank_listing" class="table datatable-show-all hideTableData ">
                        <thead>
                            <tr>
                                <th>S/N</th>
                                <th>Company Name</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>Loan Type</th>
                                <th>Bank Name</th>
                                <th>Branch Name</th>
                                <th>Loan Amount</th>
                                <th>Outstanding Amount</th>
                                <th>Loan Account Number</th>
                                <th>Loan Interest Rate</th>
                                <th>Number Of Emi</th>
                                <th>Received Type</th>
                                <th>Received Bank</th>
                                <th>Received Bank Account Number</th>
                                <th>Vendor Name</th>
                                <th>Remarks</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@stop
@section('script')
@include('templates.admin.loan_from_bank.partials.script')
@stop