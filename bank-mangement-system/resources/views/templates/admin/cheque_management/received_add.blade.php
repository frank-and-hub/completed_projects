@extends('templates.admin.master')
@php
$dropDown = $company;
$filedTitle  = 'Company';
$name = 'company_id';
@endphp
@section('content')

<div class="content"> 
  <div class="row"> 
        @if ($errors->any())
            <div class="col-md-12">
                  <div class="alert alert-danger">
                      <ul>
                          @foreach ($errors->all() as $error)
                              <li>{{ $error }}</li>
                          @endforeach
                      </ul>
                  </div>
            </div>
        @endif

    <div class="col-md-12">
      <div class="card">
        <div class="card-header header-elements-inline">
          <h4 class="card-title mb-3">Add Cheque/UTR </h4>
        </div>
        <div class="card-body">
          <form action="{!! route('admin.received.cheque_save') !!}" method="post" enctype="multipart/form-data" id="cheque_add" name="cheque_add"  >
            @csrf
            <input type="hidden" class="form-control create_application_date " name="created_at" id="created_at"  >

            <div class="row">

            <div class="col-lg-12">
              <h6 class="card-title mb-3">Deposit Bank Details </h6> 
                <div class="row">
                @include('templates.GlobalTempletes.new_role_type',['dropDown'=>$dropDown,'filedTitle'=>$filedTitle,'name'=>$name,'value'=>'','multiselect'=>'false','design_type'=>4,'branchShow'=>true,'branchName'=>'branch_id','apply_col_md'=>false,'multiselect'=>false,'placeHolder1'=>'Please Select Company','placeHolder2'=>'Please Select Branch'])
                  <div class="col-lg-4"> 
                    <div class="form-group row lg-12 ">
                      <label class="col-form-label col-lg-12">Bank Name<sup class="required">*</sup></label>
                      <div class="col-lg-12 error-msg">
                        <select   name="bank_id" id="bank_id" class="form-control" >  <option value="">Select Bank Name</option>
                              {{-- @foreach ($bank as $val)
                                  <option value="{{ $val->id }}">{{ $val->bank_name }}</option>
                               @endforeach --}}
                        </select>
                      </div>
                    </div>
                  </div>
                  <div class="col-lg-4"> 
                    <div class="form-group row">
                      <label class="col-form-label col-lg-12">Account Number<sup class="required">*</sup></label>
                      <div class="col-lg-12 error-msg">
                        <select name="account_id" id="account_id" class="form-control" >  <option value="">Select Account Number</option> 
                        </select>
                      </div>
                    </div>
                  </div>   
                  <div class="col-lg-4 "> 
                    <div class="form-group row">
                      <label class="col-form-label col-lg-12">Cheque Deposit Date<sup class="required">*</sup></label>
                      <div class="col-lg-12 error-msg">
                        <div class="input-group">
                            <span class="input-group-prepend mr-0">
                              <span class="input-group-text"><i class="ni ni-calendar-grid-58"></i></span>
                            </span>
                             <input type="text" class="form-control create_application_date" name="deposit_cheque_date" id="deposit_cheque_date" readonly >
                             
                        </div>
                      </div>
                    </div>
                  </div>
                   
                </div>
              </div>
              

              

              <div class="col-lg-12"> 
                <div class="row">  
                <h6 class="card-title mb-3">Cheque/UTR  Details </h6> 
              <div class="col-lg-12"> 
                <div class="row"> 

                  <div class="col-lg-6"> 
                    <div class="form-group row">
                      <label class="col-form-label col-lg-3">Bank Name<sup class="required">*</sup></label>
                      <div class="col-lg-9 error-msg">
                        <input type="text" name="bank_name" id="bank_name" class="form-control"  >
                      </div>
                    </div>
                  </div> 
                  <div class="col-lg-6"> 
                    <div class="form-group row">
                      <label class="col-form-label col-lg-3">Branch Name<sup class="required">*</sup></label>
                      <div class="col-lg-9 error-msg">
                        <input type="text" name="bank_branch_name" id="bank_branch_name" class="form-control"  >
                      </div>
                    </div>
                  </div>
                  <div class="col-lg-6"> 
                    <div class="form-group row">
                      <label class="col-form-label col-lg-3">Cheque/UTR  Number<sup class="required">*</sup></label>
                      <div class="col-lg-9 error-msg">
                        <input type="text" name="cheque_number" id="cheque_number" class="form-control"  >
                      </div>
                    </div>
                  </div>
                  <div class="col-lg-6"> 
                    <div class="form-group row">
                      <label class="col-form-label col-lg-3">Account No<sup class="required">*</sup></label>
                      <div class="col-lg-9 error-msg">
                        <input type="text" name="account_no" id="account_no" class="form-control"  >
                      </div>
                    </div>
                  </div> 
                  <div class="col-lg-6"> 
                    <div class="form-group row">
                      <label class="col-form-label col-lg-3">Account Holder Name<sup class="required">*</sup></label>
                      <div class="col-lg-9 error-msg">
                        <input type="text" name="account_holder" id="account_holder" class="form-control"  >
                      </div>
                    </div>
                  </div> 
                  <div class="col-lg-6"> 
                    <div class="form-group row">
                      <label class="col-form-label col-lg-3">Cheque/UTR  Date<sup class="required">*</sup></label>
                      <div class="col-lg-9 error-msg">
                        <div class="input-group">
                            <span class="input-group-prepend mr-0">
                              <span class="input-group-text"><i class="ni ni-calendar-grid-58"></i></span>
                            </span>
                             <input type="text" class="form-control create_application_date" name="cheque_date" id="cheque_date" autocomplete="off" >
                             <!-- <input type="hidden" class="form-control created_at " name="created_at" id="created_at"  > -->
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="col-lg-6"> 
                    <div class="form-group row">
                      <label class="col-form-label col-lg-3">Amount<sup class="required">*</sup></label>
                      <div class="col-lg-9 error-msg">
                        <input type="text" name="amount" id="amount" class="form-control"  >
                      </div>
                    </div>
                  </div>
                  <div class="col-lg-6"> 
                    <div class="form-group row">
                      <label class="col-form-label col-lg-3">Remark</label>
                      <div class="col-lg-9 error-msg"> 
                        <textarea name="remark" id="remark" class="form-control"></textarea>
                      </div>
                    </div>
                  </div> 
                </div>
              </div>
              <div class="col-lg-12">
                <div class="form-group row text-center"> 
                  <div class="col-lg-12 ">
                    <button type="submit" class="btn btn-primary" name="dBank">Submit</button>
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
@include('templates.admin.cheque_management.partials.script_received')
@stop