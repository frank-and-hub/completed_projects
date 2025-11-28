@extends('templates.admin.master')

@section('content')

<div class="content">
    @if($accountDetail)
        <div class="row">  
            <div class="col-lg-12">   

                <div class="card">
                    <div class="card-header header-elements-inline">
                        <h6 class="card-title font-weight-semibold">New Transaction For :@if($code=='S'){{ $accountDetail->account_no  }} @else {{ $accountDetail->account_number }} - {{ $accountDetail->plan->name}} @endif</h6>
                    </div>
                </div>             

                <div class="card bg-white shadow">
                    <div class="card-body">                      
                        <form   method="post" enctype="multipart/form-data" action="{!! route('admin.transaction_start_new') !!}" id="fillter" name="fillter">
                        @csrf
                         
                         
                                <h3 class="card-title mb-3">Print Fillter</h3>
                                <div class="row">
                                    <div class="col-lg-5">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-5">Transaction ID From<sup class="required">*</sup></label>
                                            <div class="col-lg-7 error-msg ">
                                                <input type="text" name="transaction_id_from" id="transaction_id_from"  class="form-control  ">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-5">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-5">Transaction ID To<sup class="required">*</sup></label>
                                            <div class="col-lg-7 error-msg">
                                                <input type="text" name="transaction_id_to" id="transaction_id_to"  class="form-control  ">
                                                <input type="hidden" name="id" id="id"  class="form-control  " value="{{$accountDetail->id}} ">
                                                <input type="hidden" name="code" id="code"  class="form-control  " value="{{$code}}">
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-lg-2 text-center">
                                        <div class=" " > 
                                            <button type="submit" class="btn btn-primary">Submit<i class="icon-paperplane ml-2"></i></button> 
                                             
                                        </div>
                                    </div>
                                </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="row" > 
        
            <div class="col-lg-12" id="print_passbook">                

                <div class="card bg-white shadow">
                    <div class="card-body">
                        

               
                        <div class="table-responsive">
                            <table   class="table table-flush" style="width: 100%" id="listtansaction">
                                <thead class=""> 
                                    <tr>
                                        <th style="width: 10%"> S.No</th>
                                        <th style="width: 10%"> Transaction ID</th>
										<th style="width: 10%"> Transaction By</th>
                                        <th style="width: 10%"> Date</th>
                                        <th style="width: 10%">TR Date</th>
                                        <th>Particular</th> 
                                        <th>Cheque/Reference No</th>
                                        <th>Withdrawal</th>
                                        <th>Amt.Deposited</th>
                                        <th>Balance</th>
                                         <th>Action</th>
                                    </tr>
                                </thead> 
                            </table>
                        </div> 
                                           
                    </div>
                </div> 
            </div> 
        </div>
    @else
        <div class="row">
            <div class="col-lg-12">
                <div class="card bg-white">
                <div class="card-body">
                    <div class="text-center">
                        <h3 class="">Transaction not found</h3>
                        <a href="{!! route('branch.passbook') !!}" style="float:right" class="btn btn-secondary">Back</a>
                    </div>
                </div>
                </div>
            </div> 
        </div>
    @endif
</div>

@stop

@section('script')
@include('templates.admin.investment_management.passbook.new.tran_listing_script')
@stop