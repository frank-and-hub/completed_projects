@extends('templates.admin.master')
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
<div class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header header-elements-inline">
                    <h6 class="card-title font-weight-semibold">Search Filter</h6>
                </div>
                <div class="card-body">
                    <form action="#" enctype="multipart/form-data" id="filter" name="filter">
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
                                        <input type="hidden" name="account_no" id="account_no" value="{{$investment->id}}">
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
                                    <label class="col-form-label col-lg-12">Associate Name </label>
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
                                        <input type="hidden" name="commission_export" id="commission_export" value="">
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
            <div class="card bg-white shadow">
                <div class="card-header bg-transparent header-elements-inline">
                    <h3 class="card-title font-weight-semibold"> Commission Listing - {{$investment->account_number}}</h3>
                    <div class="">
                        <button type="button" class="btn bg-dark legitRipple export ml-2" data-extension="0" style="float: right;">Export Excel</button>
                    </div>
                </div>
                <div class="table-responsive">
                    <table id="commission_listing" class="table table-flush">
                        <thead class="">
                            <tr>
                                <th>S/N</th>
                                <th>Month</th>
                                <th>Associate Id</th>
                                <th> Associate Name</th>
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
@stop
@section('script')
@include('templates.admin.investment_management.partials.commission_script')
@stop