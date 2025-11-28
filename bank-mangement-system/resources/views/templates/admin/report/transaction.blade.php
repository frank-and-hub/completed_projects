@extends('templates.admin.master')

@section('content')
@section('css')
<style>
    .datatable{
    display:none;
}
</style>
@endsection
<?php
$startDatee = (checkMonthAvailability(date('d'),date('m'),date('Y'),33));
$startDatee = $endDatee = date('d/m/Y',strtotime($startDatee));
?>
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
                                                 <input type="text" class="form-control  create_application_date" name="start_date" id="start_date"  value="{{$startDatee}}" > 
                                               </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">To Date </label>
                                        <div class="col-lg-12 error-msg">
                                            <div class="input-group">
                                                <input type="text" class="form-control  create_application_date" name="end_date" id="end_date" value="{{$endDatee}}" >

                                               
                                            </div>
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
                                                    <option value="{{ $val->id }}"  >{{ $val->name }}</option> 
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
                                        <label class="col-form-label col-lg-12">Payment Mode </label>
                                        <div class="col-lg-12 error-msg">
                                            <select class="form-control" id="payment_mode" name="payment_mode">
                                                <option value="">all</option>
                                                <option value="0" selected>Cash</option>
                                                <option value="1">Cheque</option>
                                                <option value="other">Other</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Payment Type </label>
                                        <div class="col-lg-12 error-msg">
                                            <select class="form-control" id="payment_type" name="payment_type">
                                                <option value="">all</option>
                                                <option value="CR" selected>CR</option>
                                                <option value="DR">DR</option> 
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <div class="form-group row"> 
                                        <div class="col-lg-12 text-right" >
                                            <input type="hidden" name="associate_report_currentdate" id="associate_report_currentdate" class="create_application_date" value="{{$startDatee}}">
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
            <div class="col-md-12 table-section datatable">
                <div class="card">
                    <div class="card-header header-elements-inline">
                        <h6 class="card-title font-weight-semibold">Investment </h6>
                        <div class="">
                            <button type="button" class="btn bg-dark legitRipple export_invest ml-2" data-extension="0" style="float: right;">Export xslx</button> 
                        </div>
                    </div>
                    <div class="">
                        <table id="investment_list" class="table datatable-show-all">
                            <thead>
                                <tr>
                                    <th>S/N</th>
                                    <th>BR Name</th>
                                    <th>BR Code</th>
                                    <th>SO Name</th>
                                    <th>RO Name</th>
                                    <th>ZO Name</th> 
                                    <th>Member Id </th>                                    
                                    <th>Member Name</th>
                                    <th>Account No </th>
                                    <th>Plan Name </th>
                                    <th>Tag </th>
                                    <th>Amount</th>
                                    <th>Payment Mode</th>
                                    <th>Payment Type</th> 
                                    <th>Is Eli</th> 
                                    <th>Created</th>
                                        
                                </tr>
                            </thead>                    
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-md-12 table-section datatable">
                <div class="card">
                    <div class="card-header header-elements-inline">
                        <h6 class="card-title font-weight-semibold">SSB Account </h6>
                        <div class="">
                            <button type="button" class="btn bg-dark legitRipple export_ssb ml-2" data-extension="0" style="float: right;">Export xslx</button> 
                        </div>
                    </div>
                    <div class="">
                        <table id="ssb_list" class="table datatable-show-all">
                            <thead>
                                <tr>
                                    <th>S/N</th>
                                    <th>BR Name</th>
                                    <th>BR Code</th>
                                    <th>SO Name</th>
                                    <th>RO Name</th>
                                    <th>ZO Name</th> 
                                    <th>Member Id </th>                                    
                                    <th>Member Name</th>
                                    <th>Account No </th>
                                    <th>Amount</th>
                                    <th>Payment Mode</th>
                                    <th>Payment Type</th>
                                    <th>Created</th>
                                        
                                </tr>
                            </thead>                    
                        </table>
                    </div>
                </div>
            </div>


            <!--<div class="col-md-12">
                <div class="card">
                    <div class="card-header header-elements-inline">
                        <h6 class="card-title font-weight-semibold">Loan </h6>
                        <div class="">
                            <button type="button" class="btn bg-dark legitRipple export3 ml-2" data-extension="0" style="float: right;">Export xslx</button> 
                        </div>
                    </div>
                    <div class="">
                        <table id="loan_list" class="table datatable-show-all">
                            <thead>
                                <tr>
                                    <th>Comming Soon</th>                                       
                                </tr>
                            </thead>                    
                        </table>
                    </div>
                </div>
            </div>-->



            <div class="col-md-12 table-section datatable">
                <div class="card">
                    <div class="card-header header-elements-inline">
                        <h6 class="card-title font-weight-semibold">Other </h6>
                        <div class="">
                            <button type="button" class="btn bg-dark legitRipple export_other ml-2" data-extension="0" style="float: right;">Export xslx</button> 
                        </div>
                    </div>
                    <div class="">
                        <table id="other_list" class="table datatable-show-all">
                            <thead>
                                <tr>
                                    <th>S/N</th>
                                    <th>BR Name</th>
                                    <th>BR Code</th>
                                    <th>SO Name</th>
                                    <th>RO Name</th>
                                    <th>ZO Name</th> 
                                    <th>Member Id </th>                                    
                                    <th>Member Name</th>
                                    <th>Amount Type </th>
                                    <th>Amount</th>
                                    <th>Payment Mode</th>
                                    <th>Payment Type</th> 
                                    <th>Created</th>                                       
                                </tr>
                            </thead>                    
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@include('templates.admin.report.partials.transaction')
@stop