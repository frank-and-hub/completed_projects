@extends('templates.admin.master')

@section('content')


@php
$year=date('Y');
$month=date('m');
$search='yes';
if(isset($_GET['month']))
{
    $month=$_GET['month'];
    $monthn=$_GET['month'];
    $search='yes';
} else{
    $monthn = "";
}
$month=ltrim($month,0);
if(isset($_GET['year']))
{
    $year=$_GET['year'];
    $yearn=$_GET['year'];
    $search='yes';
} else{
    $yearn = "";
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
<div class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header header-elements-inline">
                    <h6 class="card-title font-weight-semibold">Search Filter</h6>
                </div>
                <div class="card-body">
                    <form action="#" method="post" enctype="multipart/form-data" id="commissionFilterDetail" name="commissionFilterDetail">
                        @csrf
                        <div class="row">
                        <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Year</label>
                                        <div class="col-lg-12 error-msg">
                                             <div class="input-group">
                                                 <select name="year" id="year" class="form-control" >
                                                 <option value="">Please select year</option>
                                                 <option value="2022"@if ($year == 2022) selected @endif>2022</option>
                                            @foreach( $years as $year )
                                            <option value="{{ $year->year }}"  @if ($year->year == $yearn) selected @endif>{{ $year->year }}</option>
                                            @endforeach
                                                  </select>
                                               </div>
                                        </div>
                                    </div>
                                </div>   
                                <input type="hidden" id="month_set" value="{{$monthn}}"> 
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Month</label>
                                        <div class="col-lg-12 error-msg">
                                             <div class="input-group"> 
                                             <select name="month" id="month" class="form-control">
                                                <option value="">Select Month</option>
                                                <option class="myopt" data-year="[2022]" value="12"@if ($yearn == 2022) selected @endif>December</option>
                                                @foreach( $months as $date ) 
                                                <option class="myopt" data-year="[{{$date->year}}]" value="{{$date->month}}"
                                                @if ($date->month == $monthn && $yearn != 2022 ) selected @endif>{{getMonth($date->month)}}</option>
                                                @endforeach                                              
                                            </select>
                                        </div>
                                        </div>
                                    </div>
                                </div>
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">Select Investment Plans </label>
                                    <div class="col-lg-12 error-msg">
                                        <select class="form-control" id="plan_id" name="plan_id">
                                            <option value="">Select Plan</option>
                                            @foreach( $plans as $plan )
                                            <option value="{{ $plan->id }}">{{ $plan->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group text-right">
                                    <div class="col-lg-12 page">
                                        <input type="hidden" name="is_search" id="is_search" value="yes">
                                        <input type="hidden" name="commission_export" id="commission_export" value="no">
                                        <input type="hidden" name="id" id="id" value="{{$member->id}}">
                                        <button type="button" class=" btn bg-dark legitRipple" onClick="searchCommissionDetailForm()">Submit</button>
                                        <button type="button" class="btn btn-gray legitRipple" id="reset_form" onClick="resetCommissionDetailForm()">Reset </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-12">
            <div class="card bg-white shadow">
                <div class="card-header bg-transparent header-elements-inline">
                    <h5 class="mb-0 text-dark">Associate Commission - {{ $member->associate_no}}({{$member->first_name}} {{$member->last_name}}) - {{ getCarderName($member->current_carder_id) }}</h3>
                        <div class="">
                            <button type="button" class="btn bg-dark legitRipple exportcommissionDetail ml-2" data-extension="0" style="float: right;">Export xslx</button>
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
@stop

@section('script')
@include('templates.admin.associate.partials.listing_script')
@stop