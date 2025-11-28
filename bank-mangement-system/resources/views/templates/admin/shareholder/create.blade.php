@extends('templates.admin.master')

@section('content')
<?php
$dataid='';
?>
<style>
    sup{
        color:red;
    }
</style>
    <div class="loader" style="display: none;"></div>

    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <!-- Basic layout-->
                <div class="card">
                    <div class="card-header header-elements-inline">
                        <div class="card-body" >
                            <form method="post" action="{!! route('admin.shareholder.save') !!}" id="shareholder_form">
                                 @csrf
                                <!-- <input type="hidden" name="type"> -->
                                <div class="form-group row">
                                @include('templates.GlobalTempletes.role_type',['dropDown'=>$company,'filedTitle'=>'Company','name'=>'company_id','apply_col_md'=>true])
                                    <label class="col-form-label col-lg-2">Type<sup>*</sup></label>
                                    <div class="col-lg-4 error-msg">
                                       <select name = "type" class="form-control" id="type" required >
                                           <option value=''>---Please Select---</option>
                                           <option value="15">Shareholder</option>
                                            <option value="19">Director</option>
                                           </select>
                                    </div>
                                 
                                </div>
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-2">Member Id (CI)</label>
                                    <div class="col-lg-4  error-msg">
                                        <input type="text" id="member_id" name="member_id" class="form-control " >
                                    </div>
                                    <label class="col-form-label col-lg-2">SSB Account</label>
                                     <div class="col-lg-4  error-msg">
                                        <input type="text" id="ssb_account" name="ssb_account" class="form-control " readonly>
                                        <input type="hidden" name="ssb_id" id="ssb_id">
                                    </div>
                                </div>
                                <div class="form-group row">
                                 <label class="col-form-label col-lg-2">Name Of Person<sup>*</sup></label>
                                    <div class="col-lg-4 error-msg">
                                        <input type="text" id="name" name="name" class="form-control " >
                                    </div>
                                   <label class="col-form-label col-lg-2">Father/Husband Name<sup>*</sup></label>
                                    <div class="col-lg-4  error-msg">
                                        <input type="text" id="father_name" name="father_name" class="form-control " >
                                    </div>
                                   
                                </div>
                                
                                <div class="form-group row">
                                <label class="col-form-label col-lg-2 ">Address<sup>*</sup></label>
                                     <div class="col-lg-4 error-msg">
                                        <input type="text" id="address" name="address" class="form-control " >
                                    </div>
                                <label class="col-form-label col-lg-2">Contact Number<sup>*</sup></label>
                                    <div class="col-lg-4 error-msg">
                                    <input type="text" id="contact_no" name="contact_no" class="form-control " >
                                </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-2">PAN Card<sup>*</sup></label>
                                    <div class="col-lg-4 error-msg">
                                        <input type="text" id="pan_no" name="pan_no" class="form-control " >
                                    </div>
                                    <label class="col-form-label col-lg-2">Aadhar Card<sup>*</sup></label>
                                     <div class="col-lg-4 error-msg">
                                        <input type="text" id="aadhar_no" name="aadhar_no" class="form-control " >
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-2">Firm Name</label>
                                    <div class="col-lg-4">
                                        <input type="text" id="firm_name" name="firm_name" class="form-control " >
                                    </div>
                                    <label class="col-form-label col-lg-2">Email Address</label>
                                    <div class="col-lg-4 error-msg">
                                        <input type="text" id="email" name="email" class="form-control " >
                                    </div>
                                    
                                </div>
                                
                                <h3 class="card-title">Bank Account Details</h3>
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-2">Bank Name<sup>*</sup></label>
                                    <div class="col-lg-4 error-msg">
                                        <input type="text" id="bank_name" name="bank_name" class="form-control " >
                                    </div>
                                    <label class="col-form-label col-lg-2">Branch Name<sup>*</sup></label>
                                     <div class="col-lg-4 error-msg">
                                        <input type="text" id="branch_name" name="branch_name" class="form-control " >
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-2">Account Number<sup>*</sup></label>
                                    <div class="col-lg-4  error-msg">
                                        <input type="text" id="account_number" name="account_number" class="form-control " >
                                        <input type="hidden" id="c_id" name="c_id" class="form-control " >
                                    </div>
                                    <label class="col-form-label col-lg-2">IFSC Code<sup>*</sup></label>
                                     <div class="col-lg-4  error-msg">
                                        <input type="text" id="ifsc_code" name="ifsc_code" class="form-control " >
                                    </div>
                                </div>
                                
                                <div class="form-group row">
                                   <!--  <label class="col-form-label col-lg-2">Amount<sup>*</sup></label>
                                    <div class="col-lg-4  error-msg">
                                        <input type="text" id="amount" name="amount" class="form-control " >
                                    </div> -->
                                    <label class="col-form-label col-lg-2">Remark<sup>*</sup></label>
                                     <div class="col-lg-4  error-msg">
                                        <input type="text" id="remark" name="remark" class="form-control " >
                                    </div>

                                    <label class="col-form-label col-lg-2">Date<sup>*</sup></label>
                                    <div class="col-lg-4 error-msg">
                                        <input type="text" id="date" name="date" class="form-control " readonly>
                                        <input type="hidden" class="form-control create_application_date " name="create_application_date" id="create_application_date"  >
                                        <input type="hidden" class="form-control created_at " name="created_at" id="created_at"  >
                                    </div>
                                </div>
                                 <div class="text-right">
                                    <input type="submit" name="submitform" value="Submit" class="btn btn-primary submit">
                                </div>
                            </form>
                        </div>
                    </div>
                    <!-- /basic layout -->
                </div>
            </div>
        </div>
    </div>
@stop

@section('script')
    @include('templates.admin.shareholder.partials.script')
@stop