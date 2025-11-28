@extends('templates.admin.master')

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
          <h4 class="card-title mb-3">Edit Cheque/UTR </h4>
        </div>
        <div class="card-body">
          <form action="{!! route('admin.received.cheque_update') !!}" method="post" enctype="multipart/form-data" id="cheque_add" name="cheque_add"  >
            @csrf
            <div class="row">
                <h6 class="card-title mb-3">Cheque/UTR  Details </h6> 
              <div class="col-lg-12"> 
                <div class="row"> 
                <div class="col-lg-6"> 
                    <div class="form-group row">
                      <label class="col-form-label col-lg-4">Branch<sup class="required">*</sup></label>
                      <div class="col-lg-8 error-msg">
                        <select   name="branch_id" id="branch_id" class="form-control" >  
                          <option value="">Select Branch</option>
                              @foreach ($branch as $val)
                                  <option value="{{ $val->id }}" @if($cheque->branch_id==$val->id ) selected @endif >{{ $val->name }}</option>
                               @endforeach
                        </select>
                      </div>
                    </div>
                  </div>

                  <div class="col-lg-6"> 
                    <div class="form-group row">
                      <label class="col-form-label col-lg-3">Bank Name<sup class="required">*</sup></label>
                      <div class="col-lg-9 error-msg">
                        <input type="text" name="bank_name" id="bank_name" class="form-control" value="{{ $cheque->bank_name}}" >
                        <input type="hidden" name="id" id="id" class="form-control" value="{{ $cheque->id}}" >
                      </div>
                    </div>
                  </div> 
                  <div class="col-lg-6"> 
                    <div class="form-group row">
                      <label class="col-form-label col-lg-3">Cheque/UTR  Number<sup class="required">*</sup></label>
                      <div class="col-lg-9 error-msg">
                        <input type="text" name="cheque_number" id="cheque_number" class="form-control"  value="{{ $cheque->cheque_no}}"  >
                      </div>
                    </div>
                  </div>
                  <div class="col-lg-6"> 
                    <div class="form-group row">
                      <label class="col-form-label col-lg-3">Account No<sup class="required">*</sup></label>
                      <div class="col-lg-9 error-msg">
                        <input type="text" name="account_no" id="account_no" class="form-control"   value="{{ $cheque->cheque_account_no}}" >
                      </div>
                    </div>
                  </div> 
                  <div class="col-lg-6"> 
                    <div class="form-group row">
                      <label class="col-form-label col-lg-3">Account Holder Name<sup class="required">*</sup></label>
                      <div class="col-lg-9 error-msg">
                        <input type="text" name="account_holder" id="account_holder" class="form-control"   value="{{ $cheque->account_holder_name}}" >
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
                            <?php 
                              $cheque_create_date=date("d/m/Y", strtotime($cheque->cheque_create_date));
                            ?>
                             <input type="text" class="form-control  " name="cheque_date" id="cheque_date"  value="{{ $cheque_create_date }}"  >
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="col-lg-6"> 
                    <div class="form-group row">
                      <label class="col-form-label col-lg-3">Amount<sup class="required">*</sup></label>
                      <div class="col-lg-9 error-msg">
                        <input type="text" name="amount" id="amount" class="form-control"   value="{{ round($cheque->amount)}}" >
                      </div>
                    </div>
                  </div>
                  <div class="col-lg-6"> 
                    <div class="form-group row">
                      <label class="col-form-label col-lg-3">Remark</label>
                      <div class="col-lg-9 error-msg"> 
                        <textarea name="remark" id="remark" class="form-control"  >{{ $cheque->remark}}</textarea>
                      </div>
                    </div>
                  </div> 

                </div>
              </div>

              <div class="col-lg-12">
              <h6 class="card-title mb-3">Deposit Bank Details </h6> 
                <div class="row">

                  <div class="col-lg-6"> 
                    <div class="form-group row">
                      <label class="col-form-label col-lg-4">Bank Name<sup class="required">*</sup></label>
                      <div class="col-lg-8 error-msg">
                        <select   name="bank_id" id="bank_id" class="form-control" >  <option value="">Select Bank Name</option>
                              @foreach ($bank as $val)
                                  <option value="{{ $val->id }}"  @if($cheque->deposit_bank_id== $val->id) selected @endif>{{ $val->bank_name }}</option>
                               @endforeach
                        </select>
                      </div>
                    </div>
                  </div> 
                  <div class="col-lg-6"> 
                    <div class="form-group row">
                      <label class="col-form-label col-lg-4">Account Number<sup class="required">*</sup></label>
                      <div class="col-lg-8 error-msg">
                        <select   name="account_id" id="account_id" class="form-control" >  <option value="">Select Account Number</option> 
                        </select>
                      </div>
                    </div>
                  </div>
                  <div class="col-lg-6"> 
                    <div class="form-group row">
                      <label class="col-form-label col-lg-4">Cheque/UTR  Deposit Date<sup class="required">*</sup></label>
                      <div class="col-lg-8 error-msg">
                        <div class="input-group">
                            <span class="input-group-prepend">
                              <span class="input-group-text"><i class="ni ni-calendar-grid-58"></i></span>
                            </span>
                            <?php 
                              $cheque_deposit_date=date("d/m/Y", strtotime($cheque->cheque_deposit_date));
                            ?>
                             <input type="text" class="form-control  " name="deposit_cheque_date" id="deposit_cheque_date"  value="{{ $cheque_deposit_date}}"  >
                        </div>
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
@include('templates.admin.cheque_management.partials.edit_recevied')
@stop