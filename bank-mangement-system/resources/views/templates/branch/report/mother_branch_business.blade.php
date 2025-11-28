@extends('layouts/branch.dashboard')

@section('content')
<style type="text/css">
    #expense {
        margin: 4px, 4px;
        padding: 4px;

        height: 37rem;
        overflow-x: hidden;
        overflow-y: auto;
        text-align: justify;
    }

    .loader {
        position: fixed;
        left: 0px;
        top: 0px;
        width: 100%;
        height: 100%;
        z-index: 9999;
        background: url('{{url(' /')}}/asset/images/loader.gif') 50% 50% no-repeat rgb(249, 249, 249, 0);
    }
</style>
<?php

$getBranchId = getUserBranchId(Auth::user()->id);
$branch_id = $getBranchId->id;
?>
<div class="loader" style="display: none;"></div>

<div class="container-fluid mt--6">
    <div class="content-wrapper">
        <div class="row">

            <div class="col-lg-12">

                <div class="card bg-white">

                    <div class="card-body page-title">
                        <h3 class="">Mother Business Report Listing</h3>
                    </div>

                </div>

            </div>

        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card bg-white">
                    <div class="card-header header-elements-inline">
                        <h3 class="card-title font-weight-semibold">Search Filter</h3>
                    </div>
                    <div class="card-body">
                        <form action="#" method="post" enctype="multipart/form-data" id="filter" name="filter">
                            <input type="hidden" class="form-control create_application_date" name="default_date" id="default_date" autocomplete="off">
                            @csrf
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">From Date  <sup class="">*</sup></label>
                                        <div class="col-lg-12 error-msg">
                                            <div class="input-group">
                                                <input type="text" class="form-control" name="start_date" id="start_date" value=" " readonly required>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">To Date  <sup class="">*</sup></label>
                                        <div class="col-lg-12 error-msg">
                                            <div class="input-group">
                                                <input type="text" class="form-control" name="end_date" id="end_date" value=" " readonly required>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                @if(!empty($company))
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Company <sup class="">*</sup></label>
                                        <div class="col-lg-12 error-msg">
                                            <select class="form-control" name="company" id="company_id">
                                                <option value="">--Please Select Company -- </option>
                                                <option value="0">All Company</option>
                                                @foreach($company as $key=>$com)
                                                <option value="{{$key}}">{{$com}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                @endif

                                <input type="hidden" class="form-control  " name="branch_id" id="branch_id" value="{{$branch_id}}">
                                <div class="col-md-12">
                                    <div class="form-group row">
                                        <div class="col-lg-12 text-right">
                                            <input type="hidden" name="is_search" id="is_search" value="yes">
                                            <input type="hidden" name="export" id="export" value="">
                                            <button type="button" class=" btn btn-primary legitRipple" id="search_form" onClick="searchForm()">Submit</button>
                                            <button type="button" class="btn btn-secondary legitRipple" id="reset_form" onClick="resetForm()">Reset </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="row table-section d-none">
            <div class="col-lg-12">

                <div class="card bg-white shadow">
                    <div class="card-header bg-transparent">
                        <div class="row">
                            <div class="col-md-8">
                                <h3 class="mb-0 text-dark">Mother Branch Business Report</h3>
                            </div>
                            <div class="col-md-4">
                                <div class="text-right">
                                    <button type="button" class="btn btn-primary legitRipple export ml-2" data-extension="0" style="float: right;">Export Excel</button>

                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">

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
</div>


@stop
@section('script')
@include('templates.branch.report.partials.mother_branch_business_script')
@stop