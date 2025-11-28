@extends('templates.admin.master')
@section('content')
<?php
$finacialYear = getFinacialYear();
$startDatee = date("d/m/Y", strtotime($finacialYear['dateStart']));
$branchIddd = 33;
$globalDate1 = headerMonthAvailability(date('d'), date('m'), date('Y'), $branchIddd);
$endDatee = date("Y-m-d", strtotime(convertDate($globalDate1)));

?>
@section('css')
<style>
    .hideTableData {
        display: none;
    }
</style>
@endsection
<div class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header header-elements-inline">
                    <h6 class="card-title font-weight-semibold">Search Filter</h6>
                </div>
                <div class="card-body">
                    <form action="#" method="post" enctype="multipart/form-data" id="filter" name="filter">
                        <input type="hidden" class="form-control create_application_date" name="default_date"
                            id="default_date" autocomplete="off">
                        @csrf
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">From Date </label>
                                    <div class="col-lg-12 error-msg">
                                        <div class="input-group">
                                            <input type="text" class="form-control" name="start_date" id="start_date"
                                                value="" required>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">To Date </label>
                                    <div class="col-lg-12 error-msg">
                                        <div class="input-group">
                                            <input type="text" class="form-control" name="end_date" id="end_date"
                                                autocomplete="off" required>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @include('templates.GlobalTempletes.both_company_filter_new',['all'=>true])
                            <input type="hidden" name="sector" id="sector" value="" />
                            <input type="hidden" name="region" id="region" value="" />
                            <input type="hidden" name="zone" id="zone" value="" />
                            <div class="col-md-12">
                                <div class="form-group row">
                                    <div class="col-lg-12 text-right">
                                        <input type="hidden" name="is_search" id="is_search" value="no">
                                        <input type="hidden" name="export" id="export" value="">
                                        <input type="hidden" name="created_at" class="create_application_date"
                                            id="created_at">
                                        <button type="button" class=" btn bg-dark legitRipple"
                                            onClick="searchForm()">Submit</button>
                                        <button type="button" class="btn btn-gray legitRipple" id="reset_form"
                                            onClick="resetForm()">Reset </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-12 table-section hideTableData">
            <div class="card">
                <div class="card-header header-elements-inline">
                    <h6 class="card-title font-weight-semibold">Branch Business Report</h6>
                    <div class="">
                        <button type="button" class="btn bg-dark legitRipple export ml-2" data-extension="0"
                            style="float: right;">Export Excel</button>
                    </div>
                </div>
                <div class="">
                    <table id="admin_bussiness_listing" class="table datatable-show-all">
                        <thead>
                            <tr>
                                <th>S/N</th>
                                <th>Company Name</th>
                                <th>BR Name</th>
                                <th>BR Code</th>
                                <th>Daily NCC - No. A/C</th>
                                <th>Daily NCC - Amt</th>
                                <th>Daily Renew - No. A/C</th>
                                <th>Daily Renew - Amt</th>
                                <th>Monthly NCC - No. A/C</th>
                                <th>Monthly NCC - Amt</th>
                                <th>Monthly Renew- No. A/C</th>
                                <th>Monthly Renew- Amt</th>
                                <th>FD NCC - No. A/C</th>
                                <th>FD NCC - Amt</th>
                                <th>SSB NCC - No. A/C</th>
                                <th>SSB NCC - Amt</th>
                                <th>SSB Renew- No. A/C</th>
                                <th>SSB Renew- Amt</th>
                                <th>Other MI</th>
                                <th>Other STN</th>
                                <th>New MI Joining - No. A/C</th>
                                <th>New Associate Joining - No. A/C</th>
                                <th>Banking - No. A/C</th>
                                <th>Banking - Amt</th>
                                <th>Total Payment - Withdrawal</th>
                                <th>Total Payment - Payment</th>
                                <th>NCC</th>
                                <th>NCC SSB</th>
                                <th>TCC</th>
                                <th>TCC SSB</th>
                                <th>Loan - No. A/C</th>
                                <th>Loan - Amt</th>
                                <th>Loan Recovery - No. A/C</th>
                                <th>Loan Recovery - Amt</th>
                                <th>Loan Against Investment - No. A/C</th>
                                <th>Loan Against Investment - Amt</th>
                                <th>Loan Against Investment Recovery - No. A/C</th>
                                <th>Loan Against Investment Recovery - Amt</th>
                                <th>Cash in hand</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@include('templates.admin.report.partials.admin_business_script')
@stop