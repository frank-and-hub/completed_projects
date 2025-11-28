@extends('layouts/branch.dashboard')



@section('content')



<div class="container-fluid mt--6">

    <div class="content-wrapper">

        <div class="row">

            <div class="col-lg-12">

                <div class="card bg-white">

                <div class="card-body page-title"> 

                        <h3 class="">Associate Business Summary Listing</h3> 

                    

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

                               <!--  <div class="col-md-4">

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

                               <div class="col-md-4">

                                    <div class="form-group row">

                                        <label class="col-form-label col-lg-12">Branch </label>

                                        <div class="col-lg-12 error-msg">

                                            <select class="form-control" id="branch_id" name="branch_id">

                                                <option value="">Select Branch</option>

                                                @foreach( $branch as $val )

                                                    <option value="{{ $val->id }}"  >{{ $val->name }}  ({{$val->branch_code}})</option> 

                                                @endforeach

                                            </select>

                                        </div>

                                    </div>

                                </div>-->

                                 <div class="col-md-4">

                                    <div class="form-group row">

                                        <label class="col-form-label col-lg-12">Associate Code</label>

                                        <div class="col-lg-12 error-msg">

                                            <input type="text" name="associate_code" id="associate_code" class="form-control">

                                            <input type="hidden" name="branch_id" id="branch_id" value="{{getUserBranchId(Auth::user()->id)->id}}">

                                        </div>

                                    </div>

                                </div>

                                <div class="col-md-12">

                                    <div class="form-group row"> 

                                        <label class="col-form-label col-lg-12">  </label>

                                        <div class="col-lg-12 text-right" >



                                            <input type="hidden" name="is_search" id="is_search" value="yes">

                                            <input type="hidden" name="export" id="export" value="">

                                            <button type="button" class=" btn btn-primary legitRipple" onClick="searchForm()" >Submit</button>

                                            <button type="button" class="btn btn-secondary legitRipple" id="reset_form" onClick="resetForm()" >Reset </button>





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

                                <h3 class="mb-0 text-dark">Associate Business Summary Report</h3>

                            </div>

                            <div class="col-md-4 text-right">



                                <button type="button" class="btn btn-primary legitRipple export ml-2" data-extension="0" style="float: right;">Export xslx</button>

                            </div>

                            </div>

                        </div>

                    

                    <div class="table-responsive">

                        <table id="associate_bussiness_listing" class="table table-flush">

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

                                    <th>RD N.I. - No. A/C</th>

                                    <th>RD N.I. - Total Deno</th>

                                    <th>RD Renew - No. A/C</th>

                                    <th>RD Renew - Total Amt</th>  

                                    <th>FRD N.I. - No. A/C</th>

                                    <th>FRD N.I. - Total Deno</th>

                                    <th>FRD Renew - No. A/C</th>

                                    <th>FRD Renew - Total Amt</th> 

                                    <th>FD N.I. - No. A/C</th>

                                    <th>FD N.I. - Total Deno</th>

                                <!--  <th>FD Renew - No. A/C</th>

                                    <th>FD Renew - Total Amt</th> -->

                                    <th>FFD N.I. - No. A/C</th>

                                    <th>FFD N.I. - Total Deno</th>

                                <!--     <th>FFD Renew - No. A/C</th>

                                    <th>FFD Renew - Total Amt</th>  -->

                                    <th>Smaraddh Kanyadhan N.I. - No. A/C</th>

                                    <th>Smaraddh Kanyadhan N.I. - Total Deno</th>

                                    <th>Smaraddh Kanyadhan Renew - No. A/C</th>

                                    <th>Smaraddh Kanyadhan Renew - Total Amt</th>

                                    <th>Smaraddh Bhavishya N.I. - No. A/C</th>

                                    <th>Smaraddh Bhavishya N.I. - Total Deno</th>

                                    <th>Smaraddh Bhavishya Renew - No. A/C</th>

                                    <th>Smaraddh Bhavishya Renew - Total Amt</th>

                                    <th>Smaraddh Jeevan N.I. - No. A/C</th>

                                    <th>Smaraddh Jeevan N.I. - Total Deno</th>

                                    <th>Smaraddh Jeevan Renew - No. A/C</th>

                                    <th>Smaraddh Jeevan Renew - Total Amt</th>

                                    <th>SSB N.I. - No. A/C</th>

                                    <th>SSB N.I. - Total Deno</th>

                                    <th>SSB Deposit - No. A/C</th>

                                    <th>SSB Deposit - Total Amt</th> 

                                    <th>MIS N.I. - No. A/C</th>

                                    <th>MIS N.I. - Total Deno</th>

                                    <th>MIS Renew - No. A/C</th>

                                    <th>MIS Renew - Total Amt</th>  

                                    <th>MB N.I. - No. A/C</th>

                                    <th>MB N.I. - Total Deno</th>

                                    <th>MB Renew - No. A/C</th>

                                    <th>MB Renew - Total Amt</th>                                  

                                    <!--<th>Total Business N.I. - No. A/C</th>

                                    <th>Total Business N.I.- Total Amt.</th>

                                    <th>Total Business Renew - No. A/C</th>

                                    <th>Total Business Renew- Total Amt.</th>-->



                                    <th>Other MI</th>

                                    <th>Other STN</th>

                                    <th>NCC_M</th>

                                    <th>NCC</th>

                                    <th>TCC_M</th>

                                    <th>TCC</th>



                                    <th>Staff Loan - No. A/C</th>

                                    <th>Staff Loan - Total Amt</th>

                                    <th>Pl Loan - No. A/C</th>

                                    <th>Pl Loan - Total Amt</th>

                                    <th>Loan against Investment - No. A/C</th>

                                    <th>Loan against Investment - Total Amt</th>

                                    <th>Group Loan - No. A/C</th>                                    

                                    <th>Group Loan - Total Amt</th>



                                    <th>Total Loan - No. A/C</th>

                                    <th>Total Loan - Total Amt.</th>



                                    <th>Staff Loan EMI - No. A/C</th>

                                    <th>Staff Loan EMI- Total Amt</th>

                                    <th>Pl Loan EMI - No. A/C</th>

                                    <th>Pl Loan EMI - Total Amt</th>

                                    <th>Loan against Investment EMI - No. A/C</th>

                                    <th>Loan against Investment EMI - Total Amt</th>

                                    <th>Group Loan EMI - No. A/C</th>

                                    <th>Group Loan EMI - Total Amt</th>



                                    <th>Total Loan EMI- No. A/C</th>

                                    <th>Total Loan EMI - Total Amt.</th>



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





@stop



@section('script')

@include('templates.branch.report.partials.associate_business_summary')

@stop