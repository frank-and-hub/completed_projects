@extends('templates.admin.master')

@section('content')
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
                                             <div class="input-group">
                                                 <input type="text" class="form-control  " name="start_date" id="start_date"  > 
                                               </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">To Date </label>
                                        <div class="col-lg-12 error-msg">
                                            <div class="input-group">
                                                 <input type="text" class="form-control  " name="end_date" id="end_date"  >
                                               </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Zone </label>
                                        <div class="col-lg-12 error-msg">
                                            <select class="form-control" id="zone" name="zone">
                                                <option value="">Select Zone</option>
                                                @foreach( $zone as $val )
                                                    <option value="{{ $val->zone }}"  >{{ $val->zone }}</option> 
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Region </label>
                                        <div class="col-lg-12 error-msg">
                                            <select class="form-control" id="region" name="region">
                                                <option value="">Select Region</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Sector  </label>
                                        <div class="col-lg-12 error-msg">
                                            <select class="form-control" id="sector" name="sector">
                                                <option value="">Select Sector</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                @if(Auth::user()->branch_id<1)
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Branch </label>
                                        <div class="col-lg-12 error-msg">
                                            <select class="form-control" id="branch_id" name="branch_id">
                                                <option value="">Select Branch</option>
                                                @foreach( $branch as $val )
                                                    <option value="{{ $val->id }}"  >{{ $val->name }} ({{ $val->branch_code }})</option> 
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                @else
                                  <input type="hidden" name="branch_id" id="branch_id" value="{{Auth::user()->branch_id}}">                         
                                @endif
                                 <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Associate Code</label>
                                        <div class="col-lg-12 error-msg">
                                            <input type="text" name="associate_code" id="associate_code" class="form-control">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group row"> 
                                        <div class="col-lg-12 text-right" >
                                            <input type="hidden" name="is_search" id="is_search" value="no">
                                            <input type="hidden" name="export" id="export" value="">
                                            <button type="button" class=" btn bg-dark legitRipple" onClick="searchForm()" >Submit</button>
                                            <button type="button" class="btn btn-gray legitRipple" id="reset_form" onClick="resetForm()" >Reset </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header header-elements-inline">
                        <h6 class="card-title font-weight-semibold">Associate Business Report</h6>
                        <div class="">
                            <button type="button" class="btn bg-dark legitRipple export ml-2" data-extension="0" style="float: right;">Export xslx</button>
                           <!-- <button type="button" class="btn bg-dark legitRipple export" data-extension="1">Export PDF</button>-->
                        </div>
                    </div>
                    <div class="">
                        <table id="associate_bussiness_listing" class="table datatable-show-all">
                            <thead>
                                <tr>
                                    <th>S/N</th>
                                    <th>BR Name</th>
                                    <th>BR Code</th>
                                    <th>SO Name</th>
                                    <th>RO Name</th>
                                    <th>ZO Name</th>

                                    <th>Associate Code</th>  
                                    <th>Associate Name</th>  
                                     <th>Carder</th> 
                                                                      
                                    <th>Daily N.I. - No. A/C</th>
                                    <th>Daily N.I. - Total Deno</th>
                                    <th>Daily Renew - No. A/C</th>
                                    <th>Daily Renew - Total Amt</th>

                                    <th>Monthly N.I. - No. A/C</th>
                                    <th>Monthly N.I. - Total Deno</th>
                                    <th>Monthly Renew - No. A/C</th>
                                    <th>Monthly Renew - Total Amt</th>  

                                    <th>FD N.I. - No. A/C</th>
                                    <th>FD N.I. - Total Deno</th>
                                   <!-- <th>FD Renew - No. A/C</th>
                                    <th>FD Renew - Total Amt</th>  -->

                                    <th>SSB N.I. - No. A/C</th>
                                    <th>SSB N.I. - Total Deno</th>
                                    <th>SSB Deposit - No. A/C</th>
                                    <th>SSB Deposit - Total Amt</th> 

                                  <!--  <th>Total Business N.I. - No. A/C</th>
                                    <th>Total Business N.I.- Total Amt.</th>
                                    <th>Total Business Renew - No. A/C</th>
                                    <th>Total Business Renew- Total Amt.</th>-->

                                    <th>Other MI</th>
                                    <th>Other STN</th>

                                    <th>NCC_M</th>
                                    <th>NCC</th>
                                    <th>TCC_M</th>
                                    <th>TCC</th>

                                    <th>Loan - No. A/C</th>
                                    <th>Loan - Total Amt</th>


                                    <th>Loan Recovery - No. A/C</th>
                                    <th>Loan Recovery - Total Amt.</th>

                                    <th>New Associate Joining No.</th>
                                    <th>Total Associate Joining No.</th> 

                                    <th>New Member Joining No.</th>
                                    <th>Total Member Joining No.</th>    
                                </tr>
                            </thead>                    
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@include('templates.admin.report.partials.associate_business')
@stop