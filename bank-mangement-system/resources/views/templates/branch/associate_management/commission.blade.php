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
return $months[$d-1];
}

@endphp
<div class="container-fluid mt--6">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-lg-12">
                <div class="card bg-white">
                <div class="card-body page-title"> 
                        <h3 class="">Associate Commission Listing</h3>
                    
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
                    <form action="#" method="post" enctype="multipart/form-data" id="commissionFilter" name="commissionFilter">
                        @csrf
                            <div class="row">
                                
                            @include('templates.GlobalTempletes.both_company_filter',['branchShow'=>true])
                            <div class="col-md-4">
								<div class="form-group row">
									<label class="col-form-label col-lg-12">Year<sup class="text-danger">*</sup></label>
									<div class="col-lg-12 error-msg">
										<select class="form-control" id="year" name="year">
											<option value="">Please select year</option>
											<option value="2020">2020</option>
											<option value="2021">2021</option>
											<option value="2022">2022</option>
											@foreach( $years as $year )
											<option value="{{ $year->year }}">{{ $year->year }}</option>
											@endforeach
										</select>
									</div>
								</div>
							</div>
							<div class="col-md-4">
								<div class="form-group row">
									<label class="col-form-label col-lg-12">Month<sup class="text-danger">*</sup></label>
									<div class="col-lg-12 error-msg">
										<select class="form-control" id="month" name="month">
											<option  value="">Please select month</option>
											<option class="myopt" data-year="[2021,2022]" value="1">January</option>
											<option class="myopt" data-year="[2021,2022]" value="2">February</option>
											<option class="myopt" data-year="[2021,2022]" value="3">March</option>
											<option class="myopt" data-year="[2021,2022]" value="4">April</option>
											<option class="myopt" data-year="[2021,2022]" value="5">May</option>
											<option class="myopt" data-year="[2020,2021,2022]" value="6">June</option>
											<option class="myopt" data-year="[2020,2021,2022]" value="7">July</option>
											<option class="myopt" data-year="[2020,2021,2022]" value="8">August</option>
											<option class="myopt" data-year="[2020,2021,2022]" value="9">September</option>
											<option class="myopt" data-year="[2020,2021,2022]" value="10">October</option>
											<option class="myopt" data-year="[2020,2021,2022]" value="11">November</option>
											<option class="myopt" data-year="[2020,2021,2022]" value="12">December</option>

											@foreach($dates as $date)
											<option class="myopt" data-year="[{{$date->year}}]" value="{{$date->month}}">{{getMonth($date->month)}}</option>
											@endforeach
										</select>
									</div>
								</div>
							</div>
                                <!--<div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Branch </label>
                                        <div class="col-lg-12 error-msg">
                                            <select class="form-control" id="branch_id" name="branch_id">
                                                <option value="">Select Branch</option>
                                                @foreach( $branch as $val )
                                                    <option value="{{ $val->id }}"  >{{ $val->name }}</option> 
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>-->

                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Associate Code </label>
                                        <div class="col-lg-12 error-msg">
                                            <input type="text" name="associate_code" id="associate_code" class="form-control"  > 
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Associate Name  </label>
                                        <div class="col-lg-12 error-msg">
                                            <input type="text" name="associate_name" id="associate_name" class="form-control"  > 
                                        </div>
                                    </div>
                                </div>
                            <div class="col-md-12">
                                    <div class="form-group text-right"> 
                                        <div class="col-lg-12 page">
                                            <input type="hidden" name="is_search" id="is_search" value="no">
                                            <input type="hidden" name="commission_export" id="commission_export" value="">
                                            <button type="button" class=" btn btn-primary legitRipple" onClick="searchCommissionForm()" >Submit</button>
                                            <button type="button" class="btn btn-gray legitRipple" id="reset_form" onClick="resetCommissionForm()" >Reset </button>
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
                                <h3 class="mb-0 text-dark">Associates</h3>
                            </div>
                        <div class="col-md-4 text-right">
                            <button type="button" class=" btn btn-primary legitRipple exportcommission ml-2" data-extension="0" style="float: right;">Export xslx</button>
                            <!-- <button type="button" class=" btn btn-primary legitRipple exportcommission" data-extension="1">Export PDF</button> -->
                        </div>
                            </div>
                        </div>
                    
                    <div class="table-responsive">
                        <table id="associate-commission-listing" class="table table-flush">
                            <thead class="">
                              <tr>
                                <th>S/N</th> 
                                <th>BR Name</th>                               
                                <th>Associate Name</th>
                                <th>Associate Code</th>
                                <th>Associate Carder</th>                         
                                <th>Total Commission Amount</th>
                                <th>Total Collection Amount</th>
                                <th>Total Collection Amount All</th>
                                <th>Senior Code</th>
                                <th>Senior Name</th>
                                <th>Senior Carder</th>
                                {{--Below code was hided by mahesh on 07-03-2024 on the saying of alpana mam
                                <th>Action</th>--}}
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