@extends('layouts/branch.dashboard')
@php
$dropDown = $company;
$filedTitle  = 'Company';
$name = 'company_id';
@endphp
@section('content')

<div class="container-fluid mt--6">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-lg-12">
                <div class="card bg-white">
                <div class="card-body page-title"> 
                        <h3 class="">Received Cheque Management</h3>                    
                </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12">
                <div class="card bg-white"> 
                <div class="card-body">
                        <div class="card-body">
          <form action="{!! route('branch.received.cheque_save') !!}" method="post" enctype="multipart/form-data" id="cheque_add" name="cheque_add"  >
            @csrf

            
            <div class="row">


            <div class="col-lg-12">
              <h4 class="mb-0 text-dark">Deposit Bank Details </h4> 
                <div class="row">
                @include('templates.GlobalTempletes.role_type',[
							'dropDown'=> $branchCompany[Auth::user()->branches->id],
							'name'=>'company_id',
							'apply_col_md'=>false,
                            'filedTitle' => 'Company'
							])                
                  <div class="col-lg-4"> 
                    <div class="form-group row">
                      <label class="col-form-label col-lg-12">Bank Name<sup class="required">*</sup></label>
                      <div class="col-lg-12 error-msg">
                        <select   name="bank_id" id="bank_id" class="form-control" >  <option value="">Select Bank Name</option>
                            
                        </select>
                      </div>
                    </div>
                  </div> 
                  <div class="col-lg-4"> 
                    <div class="form-group row">
                      <label class="col-form-label col-lg-12">Account Number<sup class="required">*</sup></label>
                      <div class="col-lg-12 error-msg">
                        <select   name="account_id" id="account_id" class="form-control" >  <option value="">Select Account Number</option> 
                        </select>
                      </div>
                    </div>
                  </div>
                  <div class="col-lg-4"> 
                    <div class="form-group row">
                      <label class="col-form-label col-lg-12">Cheque/UTR  Deposit Date<sup class="required">*</sup></label>
                      <div class="col-lg-12 error-msg">
                        <div class="input-group">
                            <span class="input-group-prepend">
                              <span class="input-group-text"><i class="ni ni-calendar-grid-58"></i></span>
                            </span>
                            @php
                      $stateid = getBranchStateByManagerId(Auth::user()->id);
                    @endphp
                     <input type="text" class="form-control" name="deposit_cheque_date" id="deposit_cheque_date"  readonly>
                     <input type="hidden" class="form-control" name="current_date" id="current_date"   value="{{ headerMonthAvailability(date('d'),date('m'),date('Y'),$stateid) }}  ">
                             <!-- <input type="text" class="form-control created_at" name="deposit_cheque_date" id="deposit_cheque_date" autocomplete="off" > -->
                        </div>
                      </div>
                    </div>
                  </div>
                 
                </div>
              </div>     



              
                <h4 class="mb-0 text-dark">Cheque/UTR  Details </h4> 
              <div class="col-lg-12"> 
                <div class="row">  
                  

                  <div class="col-lg-6"> 
                    <input type="hidden" name="branch_id" id="branch_id" class="form-control"  value="{{ customBranchName(Auth::user()->id)->id }}" >
                    <div class="form-group row">
                      <label class="col-form-label col-lg-4">Bank Name<sup class="required">*</sup></label>
                      <div class="col-lg-8 error-msg">
                        <input type="text" name="bank_name" id="bank_name" class="form-control"  >
                      </div>
                    </div>
                    <div class="form-group row">
                      <label class="col-form-label col-lg-4">Branch Name<sup class="required">*</sup></label>
                      <div class="col-lg-8 error-msg">
                        <input type="text" name="bank_branch_name" id="bank_branch_name" class="form-control"  >
                      </div>
                    </div>
                  </div> 
                  <div class="col-lg-6"> 
                    <div class="form-group row">
                      <label class="col-form-label col-lg-5">Cheque/UTR  Number<sup class="required">*</sup></label>
                      <div class="col-lg-7 error-msg">
                      
                        <input type="text" name="cheque_number" id="cheque_number" class="form-control"  >
                      </div>
                    </div>
                  </div>
                  <div class="col-lg-6"> 
                    <div class="form-group row">
                      <label class="col-form-label col-lg-4">Account No<sup class="required">*</sup></label>
                      <div class="col-lg-8 error-msg">
                        <input type="text" name="account_no" id="account_no" class="form-control"  >
                      </div>
                    </div>
                  </div> 
                  <div class="col-lg-6"> 
                    <div class="form-group row">
                      <label class="col-form-label col-lg-5">Account Holder Name<sup class="required">*</sup></label>
                      <div class="col-lg-7 error-msg">
                        <input type="text" name="account_holder" id="account_holder" class="form-control"  >
                      </div>
                    </div>
                  </div> 
                  <div class="col-lg-6"> 
                    <div class="form-group row">
                      <label class="col-form-label col-lg-4">Cheque/UTR  Date<sup class="required">*</sup></label>
                      <div class="col-lg-8 error-msg">
                        <div class="input-group">
                            <span class="input-group-prepend">
                              <span class="input-group-text"><i class="ni ni-calendar-grid-58"></i></span>
                            </span>
                          
                            <input type="text" class="form-control" name="cheque_date" id="cheque_date"  readonly >
                             <!-- <input type="text" class="form-control  " name="cheque_date" id="cheque_date" autocomplete="off" > -->
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="col-lg-6"> 
                    <div class="form-group row">
                      <label class="col-form-label col-lg-5">Amount<sup class="required">*</sup></label>
                      <div class="col-lg-7 error-msg">
                        <div class="rupee-img"></div>
                        <input type="text" name="amount" id="amount" class="form-control rupee-txt"  >
                      </div>
                    </div>
                  </div>
                  <div class="col-lg-12"> 
                    <div class="form-group row">
                      <label class="col-form-label col-lg-2">Remark</label>
                      <div class="col-lg-10 error-msg"> 
                        <textarea name="remark" id="remark" class="form-control"></textarea>
                      </div>
                    </div>
                  </div> 

                </div>
              </div>

             
              <div class="col-lg-12">
                <div class="form-group row text-center"> 
                  <div class="col-lg-12 ">
                    <button type="submit" class="btn btn-primary">Submit</button>
                  </div>
                </div>
              </div>
            </div>
          </form>
        </div>
                    </div>
            </div>
            </div>
        </div> 
</div>


@stop

@section('script')
@include('templates.branch.cheque_management.partials.script_received')
@stop