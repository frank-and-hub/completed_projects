@extends('layouts/branch.dashboard')
@section('content')
@php
function getMonth($d){
$months = [
"January",
"February",
"March",
"April",
"May",
"June",
"July",
"August",
"September",
"October",
"November",
"December"
];
$index = $d-1;
if (array_key_exists($index, $months)) {
return $months[$index];
} else {
return "Invalid month number";
}
}
@endphp
<div class="container-fluid mt--6">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-lg-12">
                <div class="card bg-white">
                    <div class="card-body">
                        <div class="">
                            <h3 class="">Loan Commission Listing </h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
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
                                        <label class="col-form-label col-lg-12">Year</label>
                                        <div class="col-lg-12 error-msg">
                                            <select class="form-control" id="year" name="year">
                                                <option value="">Please select year</option>
                                                <option value="2022">2022</option>
                                                @foreach( $year as $year )
                                                <option value="{{ $year->year }}">{{ $year->year }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Month</label>
                                        <div class="col-lg-12 error-msg">
                                            <select class="form-control" id="month" name="month">
                                                <option value="">Please select month</option>
                                                <option class="myopt" data-year="[2022]" value="12">December</option>
                                                @foreach($month as $date)
                                                <option class="myopt" data-year="[{{$date->year}}]" value="{{$date->month}}">{{getMonth($date->month)}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Associate Name</label>
                                        <div class="col-lg-12 error-msg">
                                            <input type="text" name="associate_name" id="associate_name" class="form-control">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Associate Id </label>
                                        <div class="col-lg-12 error-msg">
                                            <input type="text" name="associate_code" id="associate_code" class="form-control">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group text-right">
                                        <div class="col-lg-12 page">
                                            <input type="hidden" name="is_search" id="is_search" value="yes">
                                            <input type="hidden" name="loan_id" id="loan_id" value="{{$loan->id}}">
                                            <input type="hidden" name="commission_export" id="commission_export" value="">
                                            <button type="button" class=" btn btn-primary legitRipple investment_filters" onClick="searchForm()">Submit</button>
                                            <button type="button" class="btn btn-gray legitRipple" id="reset_form" onClick="resetForm()">Reset </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="row table_hidden">
            <div class="col-lg-12">
                <div class="card bg-white shadow">
                    <div class="card-header bg-transparent">
                        <div class="row justify-content-between">
                            <div class="col-md-6">
                                <?php

                                if ($loan->loan_type == 1) {
                                    $plan_name = 'Personal Loan';
                                } elseif ($loan->loan_type == 2) {
                                    $plan_name = 'Staff Loan(SL)';
                                } elseif ($loan->loan_type == 3) {
                                    $plan_name = 'Group Loan';
                                } elseif ($loan->loan_type == 4) {
                                    $plan_name = 'Loan against Investment plan (DL) ';
                                }   ?>
                                <?php
                                if ($loan->account_number != null) {
                                    echo '<h3 class="mb-0 text-dark">Loans - ' . $loan->account_number . '-(' . $plan_name . ')</h3>';
                                }
                                ?>
                            </div>
                            <div class="col-md-6">
                                <button type="button" class="btn btn-primary legitRipple  export ml-2" data-extension="0" style="float: right;">Export xslx</button>
                            </div>
                        </div>
                    </div>                
                    <div class="table-responsive">
                        <table id="commission_listing" class="table table-flush">
                            <thead class="">
                                <tr>
                                    <th>S.No</th>
                                    <th>Month</th>
                                    <th>Associate ID</th>
                                    <th>Associate Name</th>
                                    <th>Carder Name</th>
                                    <th>Total Amount</th>
                                    <th>Qualifying Amount</th>
                                    <th>Commission Amount</th>
                                    <th>Percentage</th>
                                    <th>Carder From </th>
                                    <th>Carder To</th>
                                    <th>Commission Type</th>
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
@include('templates.branch.loan_management.partials.listing_loan_script')
@stop