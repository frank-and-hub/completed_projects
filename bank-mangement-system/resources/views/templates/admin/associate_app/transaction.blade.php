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
                                        <label class="col-form-label col-lg-12">Associate code  </label>
                                        <div class="col-lg-12 error-msg">
                                            <input type="text" name="associate_code" id="associate_code" class="form-control"  >
                                            <span class="error invalid-feedback" id="associate_msg"></span> 
                                        </div>
                                    </div>
                                </div> 
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Associate Name </label>
                                        <div class="col-lg-12 error-msg">
                                            <input type="text" name="associate_name" id="associate_name" class="form-control" readonly >
                                            <input type="hidden" name="associate_id" id="associate_id" class="form-control" readonly > 
                                        </div>
                                    </div>
                                </div> 
                                
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Transaction Type </label>
                                        <div class="col-lg-12  error-msg">
                                            <select class="form-control" id="type" name="type">
                                                <option value="">Select Transaction</option> 
                                                <option value="1">Investments Plans Registration</option> 
                                                <option value="2">SSB Registration</option>
                                                <option value="3">Investments Renewal </option>
                                                <option value="4">SSB Renewal </option> 
                                                <option value="5">Loan Recovery</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group row"> 
                                        <div class="col-lg-12 text-right" >
                                            <input type="hidden" name="is_search" id="is_search" value="yes">
                                            <input type="hidden" name="member_export" id="member_export" value="">
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
                        <h6 class="card-title font-weight-semibold">Transaction  List</h6>
                        <!--<div class="">
                            <button type="button" class="btn bg-dark legitRipple export ml-2" data-extension="0" style="float: right;">Export xslx</button>
                            <button type="button" class="btn bg-dark legitRipple export" data-extension="1">Export PDF</button>
                        </div>-->
                    </div>
                    <div class="">
                        <table id="transaction_listing" class="table datatable-show-all">
                            <thead>
                                <tr>
                                    <th>S/N</th>
                                    <th>Created Date</th>
                                    <th>BR Name</th>
                                    <th>BR Code</th>
                                    <th>SO Name</th>
                                    <th>RO Name</th>
                                    <th>ZO Name</th>
                                    <th>Member ID</th>
                                    <th>Account Number</th>
                                    <th>Member (Account Holder) Name</th>
                                    <th>Plan Name</th>
                                    <th>Amount</th>
                                    <th>Payment Mode</th> 
                                    <th>Associate Code</th>  
                                    <th>Associate Name</th>                              
                                        
                                </tr>
                            </thead>                    
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@include('templates.admin.associate_app.partials.listing_script')
@stop