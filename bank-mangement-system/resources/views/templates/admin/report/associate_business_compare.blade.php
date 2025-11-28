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

                                

                                <div class="col-md-6">

                                    <div class="form-group row">

                                        <label class="col-form-label col-lg-12">Current From Date </label>

                                        <div class="col-lg-12 error-msg">

                                             <div class="input-group">

                                                 <input type="text" class="form-control  " name="current_start_date" id="current_start_date"  value="{{$current_from}}" > 

                                               </div>

                                        </div>

                                    </div>

                                </div>

                                <div class="col-md-6">

                                    <div class="form-group row">

                                        <label class="col-form-label col-lg-12">Current To Date </label>

                                        <div class="col-lg-12 error-msg">

                                            <div class="input-group">

                                                 <input type="text" class="form-control  " name="current_end_date" id="current_end_date"   value="{{$current_to}}">

                                               </div>

                                        </div>

                                    </div>

                                </div>

                                <div class="col-md-6">

                                    <div class="form-group row">

                                        <label class="col-form-label col-lg-12">Compare From Date </label>

                                        <div class="col-lg-12 error-msg">

                                             <div class="input-group">

                                                 <input type="text" class="form-control  " name="comp_start_date" id="comp_start_date"  value="{{$comp_from}}"> 

                                               </div>

                                        </div>

                                    </div>

                                </div>

                                <div class="col-md-6">

                                    <div class="form-group row">

                                        <label class="col-form-label col-lg-12">Compare To Date </label>

                                        <div class="col-lg-12 error-msg">

                                            <div class="input-group">

                                                 <input type="text" class="form-control  " name="comp_end_date" id="comp_end_date"  value="{{$comp_to}}" >

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

                                                    <option value="{{ $val->id }}"  >{{ $val->name }} ({{$val->branch_code}})</option> 

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

                                            <input type="hidden" name="is_search" id="is_search" value="yes">

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

                        <h6 class="card-title font-weight-semibold">Associate Business Compare Report</h6>

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

                                                                      

                                    <th>Current Daily N.I. - No. A/C</th>

                                    <th>Current Daily N.I. - Total Deno</th>

                                    <th>Current Daily Renew - No. A/C</th>

                                    <th>Current Daily Renew - Total Amt</th>



                                    <th>Current Monthly N.I. - No. A/C</th>

                                    <th>Current Monthly N.I. - Total Deno</th>

                                    <th>Current Monthly Renew - No. A/C</th>

                                    <th>Current Monthly Renew - Total Amt</th>  



                                    <th>Current FD N.I. - No. A/C</th>

                                    <th>Current FD N.I. - Total Deno</th>

                                   <!-- <th>Current FD Renew - No. A/C</th>

                                    <th>Current FD Renew - Total Amt</th>-->  



                                    <th>Current SSB N.I. - No. A/C</th>

                                    <th>Current SSB N.I. - Total Deno</th>

                                    <th>Current SSB Deposit - No. A/C</th>

                                    <th>Current SSB Deposit - Total Amt</th> 



                                    <!--<th>Current Total Business N.I. - No. A/C</th>

                                    <th>Current Total Business N.I.- Total Amt.</th>

                                    <th>Current Total Business Renew - No. A/C</th>

                                    <th>Current Total Business Renew- Total Amt.</th>-->



                                    <th>Current Other MI</th>

                                    <th>Current Other STN</th>



                                    <th>Current NCC_M</th>

                                    <th>Current NCC</th>

                                    <th>Current TCC_M</th>

                                    <th>Current TCC</th>



                                    <th>Current Loan - No. A/C</th>

                                    <th>Current Loan - Total Amt</th>





                                    <th>Current Loan Recovery - No. A/C</th>

                                    <th>Current Loan Recovery - Total Amt.</th>



                                    <th>Current New Associate Joining No.</th>

                                    <th>Current Total Associate Joining No.</th> 



                                    <th>Current New Member Joining No.</th>

                                    <th>Current Total Member Joining No.</th>   





                                    <th>Compare  Daily N.I. - No. A/C</th>

                                    <th>Compare  Daily N.I. - Total Deno</th>

                                    <th>Compare  Daily Renew - No. A/C</th>

                                    <th>Compare  Daily Renew - Total Amt</th>



                                    <th>Compare  Monthly N.I. - No. A/C</th>

                                    <th>Compare  Monthly N.I. - Total Deno</th>

                                    <th>Compare  Monthly Renew - No. A/C</th>

                                    <th>Current Monthly Renew - Total Amt</th>  



                                    <th>Compare  FD N.I. - No. A/C</th>

                                    <th>Compare  FD N.I. - Total Deno</th>

                                <!--    <th>Compare  FD Renew - No. A/C</th>

                                    <th>Compare  FD Renew - Total Amt</th>  -->



                                    <th>Compare  rent SSB N.I. - No. A/C</th>

                                    <th>Compare  SSB N.I. - Total Deno</th>

                                    <th>Compare  SSB Deposit - No. A/C</th>

                                    <th>Compare  SSB Deposit - Total Amt</th> 



                                    <!--<th>Compare  Total Business N.I. - No. A/C</th>

                                    <th>Compare  Total Business N.I.- Total Amt.</th>

                                    <th>Compare  Total Business Renew - No. A/C</th>

                                    <th>Compare  Total Business Renew- Total Amt.</th>-->



                                    <th>Compare  Other MI</th>

                                    <th>Compare  Other STN</th>



                                    <th>Compare  NCC_M</th>

                                    <th>Compare  NCC</th>

                                    <th>Compare  TCC_M</th>

                                    <th>Compare  TCC</th>



                                    <th>Compare  Loan - No. A/C</th>

                                    <th>Compare  Loan - Total Amt</th>





                                    <th>Compare  Loan Recovery - No. A/C</th>

                                    <th>Compare  Loan Recovery - Total Amt.</th>



                                    <th>Compare  New Associate Joining No.</th>

                                    <th>Compare  Total Associate Joining No.</th> 



                                    <th>Compare  New Member Joining No.</th>

                                    <th>Compare  Total Member Joining No.</th> 























                                    <th>Result  Daily N.I. - No. A/C</th>

                                    <th>Result  Daily N.I. - Total Deno</th>

                                    <th>Result  Daily Renew - No. A/C</th>

                                    <th>Result  Daily Renew - Total Amt</th>



                                    <th>Result  Monthly N.I. - No. A/C</th>

                                    <th>Result  Monthly N.I. - Total Deno</th>

                                    <th>Result  Monthly Renew - No. A/C</th>

                                    <th>Result Monthly Renew - Total Amt</th>  



                                    <th>Result  FD N.I. - No. A/C</th>

                                    <th>Result  FD N.I. - Total Deno</th>

                                   <!-- <th>Result  FD Renew - No. A/C</th>

                                    <th>Result  FD Renew - Total Amt</th>  -->



                                    <th>Result  rent SSB N.I. - No. A/C</th>

                                    <th>Result  SSB N.I. - Total Deno</th>

                                    <th>Result  SSB Deposit - No. A/C</th>

                                    <th>Result  SSB Deposit - Total Amt</th> 



                                    <!--<th>Result  Total Business N.I. - No. A/C</th>

                                    <th>Result  Total Business N.I.- Total Amt.</th>

                                    <th>Result  Total Business Renew - No. A/C</th>

                                    <th>Result  Total Business Renew- Total Amt.</th>-->



                                    <th>Result  Other MI</th>

                                    <th>Result  Other STN</th>



                                    <th>Result  NCC_M</th>

                                    <th>Result  NCC</th>

                                    <th>Result  TCC_M</th>

                                    <th>Result  TCC</th>



                                    <th>Result  Loan - No. A/C</th>

                                    <th>Result  Loan - Total Amt</th>





                                    <th>Result  Loan Recovery - No. A/C</th>

                                    <th>Result  Loan Recovery - Total Amt.</th>



                                    <th>Result  New Associate Joining No.</th>

                                    <th>Result  Total Associate Joining No.</th> 



                                    <th>Result  New Member Joining No.</th>

                                    <th>Result  Total Member Joining No.</th> 

                                </tr> 

                            </thead>                    

                        </table>

                    </div>

                </div>

            </div>

        </div>

    </div>

@include('templates.admin.report.partials.associate_business_compare')

@stop