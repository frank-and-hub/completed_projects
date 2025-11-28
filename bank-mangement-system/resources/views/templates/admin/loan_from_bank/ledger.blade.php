
@extends('templates.admin.master')

@section('content')

<?php
$finacialYear=getFinacialYear();
$startDatee=date("d/m/Y", strtotime($finacialYear['dateStart']));  

?>
    <div class="content">
        <div class="row">
            <div class="col-md-12">
            <div class="card">
                <div class="card-header header-elements-inline">
                    <h6 class="card-title font-weight-semibold">Search Filter</h6>
                </div>
                <div class="card-body">
                    <form action="#" method="post" enctype="multipart/form-data" id="filter_ledger" name="filter_ledger">
                    @csrf
                      <div class="row">
                          <div class="col-md-4">
                              <div class="form-group row">
                                  <label class="col-form-label col-lg-12">From Date </label>
                                  <div class="col-lg-12 error-msg">
                                       <div class="input-group">
                                           <input type="text" class="form-control  " name="start_date1" id="start_date1"   value="{{$startDatee}}"> 
                                         </div>
                                  </div>
                              </div>
                          </div>
                          <div class="col-md-4">
                              <div class="form-group row">
                                  <label class="col-form-label col-lg-12">To Date </label>
                                  <div class="col-lg-12 error-msg">
                                       <div class="input-group">
                                           <input type="text" class="form-control create_application_date" name="end_date1" id="end_date1"  > 
                                         </div>
                                  </div>
                              </div>
                          </div>
                          <div class="col-md-12">
                                <div class="form-group row"> 
                                    <div class="col-lg-12 text-right" >
                                        <input type="hidden" name="is_search" id="is_search" value="yes">
                                        <input type="hidden" name="ledger_export" id="ledger_export"  >
                                        <button type="button" class=" btn bg-dark legitRipple submit" onClick="searchForm_ledger()" >Submit</button>
                                        <button type="button" class="btn btn-gray legitRipple" id="reset_form" onClick="resetForm_ledger()" >Reset </button>
                                    </div>
                                </div>
                            </div>
                        </div> 
                        <input type="hidden" name="head" id="head" value="{{$head}}">
                        <input type="hidden" name="loan_id" id="loan_id" value="{{$detail->id}}"> 
                          <input type="hidden" name="label" id="label" value="{{$label}}"> 
                           <input type="hidden" name="create_application_date" id="create_application_date" class="create_application_date" > 
                    </form>
                </div>
                </div>
            </div>
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header header-elements-inline">
                        <h6 class="card-title font-weight-semibold">Loan From Bank Ledger List - {{$detail->bank_name}} ({{$detail->loan_account_number}}) </h6>
                        <div class="">
                             <button type="button" class="btn bg-dark legitRipple ledger_export ml-2" data-extension="0" style="float: right;">Export xslx</button>
                        <!-- <button type="button" class="btn bg-dark legitRipple ledger_export" data-extension="1">Export PDF</button> -->
                        </div>
                    </div>
                    <div class="">
                        <table id="loan_from_bank_ledger_listing" class="table datatable-show-all">
                            <thead>
                                <tr>
                                    <th >S.No</th>    
                                    <th >Company Name</th>    
                                    <th>Created Date</th>                                     
                                    <th >Type</th> 
                                    <th >Description</th>
                                    <th >Credit(CR)</th> 
                                    <th >Debit(DR)</th> 
                                    
                                    <th >Interest Amount</th> 
                                    <th >Balance</th> 
                                    <th >Payment Type</th>
                                    <th >Payment Mode</th>  
                                    <th> Bank</th>
                                    <th> Bank Account</th>
                                       
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
    @include('templates.admin.loan_from_bank.partials.script')
@stop