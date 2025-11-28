@extends('layouts/branch.dashboard')
@section('content')
@php

$search='yes';
if(isset($_GET['month']))
{
$month=$_GET['month'];
$monthG=$_GET['month'];
}
if(isset($_GET['year']))
{
$year=$_GET['year'];
$yearG=$_GET['year'];
}
@endphp
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
                    <div class="card-body page-title">
                        <h3 class="">Associate Commission Details</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12">
                <div class="card bg-white">
                    <div class="card-header header-elements-inline">
                        <h3 class="card-title font-weight-semibold">Search Filter</h3>
                    </div>
                    <div class="card-body">

                        <form action="#" method="post" enctype="multipart/form-data" id="commissionFilterDetail" name="commissionFilterDetail">
                            @csrf
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Year </label>
                                        <div class="col-lg-12 error-msg">
                                            <select class="form-control" id="year" name="year">
                                            <option value="">Please select year</option>
                                                 <option value="2022"@if ($year == 2022) selected @endif>2022</option>
                                            @foreach( $years as $year )
                                            <option value="{{ $year->year }}"  @if ($year->year == $yearG) selected @endif>{{ $year->year }}</option>
                                            @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group row">
                                    <input type="hidden" id="month_set" value="{{$monthG}}"> 
                                        <label class="col-form-label col-lg-12">Month</label>
                                        <div class="col-lg-12 error-msg">
                                            <select name="month" id="month" class="form-control">
                                            <option value="">Select Month</option>
                                                <option class="myopt" data-year="[2022]" value="12"@if ($yearG == 2022) selected @endif>December</option>
                                                @foreach( $months as $date ) 
                                                <option class="myopt" data-year="[{{$date->year}}]" value="{{$date->month}}"
                                                @if ($date->month == $monthG && $yearG != 2022 ) selected @endif>{{getMonth($date->month)}}</option>
                                                @endforeach 
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Select Investment Plans </label>
                                        <div class="col-lg-12 error-msg">
                                            <select class="form-control" id="plan_id" name="plan_id">
                                                <option value="">Select Plan</option>
                                                @foreach( $plan as $val )
                                                <option value="{{ $val->id }}">{{ $val->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-12 text-right">
                                    <div class="form-group text-right">
                                        <div class="col-lg-12 page">
                                            <input type="hidden" name="is_search" id="is_search" value="yes">
                                            <input type="hidden" name="commission_export" id="commission_export" value="">
                                            <input type="hidden" name="id" id="id" value="{{$member->id}}">
                                            <button type="button" class=" btn btn-primary legitRipple" onClick="searchCommissionDetailForm()">Submit</button>
                                            <button type="button" class="btn btn-gray legitRipple" id="reset_form" onClick="resetCommissionDetailForm()">Reset </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12">

                <div class="card bg-white shadow">
                    <div class="card-header bg-transparent">
                        <div class="row">
                            <div class="col-md-8">
                                <h3 class="mb-0 text-dark">Associates - {{ $member->associate_no}}({{$member->first_name}} {{$member->last_name}}) - {{ getCarderName($member->current_carder_id) }}</h3>
                            </div>
                            <div class="col-md-4 text-right">
                                <button type="button" class=" btn btn-primary legitRipple exportcommissionDetail ml-2" data-extension="0" style="float: right;">Export xslx</button>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table id="associate-commission-detail" class="table table-flush">
                            <thead class="">
                                <tr>
                                    <th>S/N</th>
                                    <th>Month</th>
                                    <th>Account No.</th>
                                    <th>Plan Name</th>
                                    <th>Total Amount</th>
                                    <th>Qualifying Amount</th>
                                    <th>Commission Amount</th>
                                    <th>Percentage</th>
                                    <th>Carder From</th>
                                    <th>Carder To</th>
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
@include('templates.branch.associate_management.partials.listing_script')
@stop