@extends('templates.admin.master')



@section('content')
<?php
$dataid=$data->id;
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

                            <form method="post" action="{!! route('admin.holder.director.update') !!}" id="shareholder_form">

                                 @csrf

                                <input type="hidden" name="head_id" value={{$data->head_id}}>

                                 <input type="hidden" name="id" value={{$data->id}}>
                                 

                                <div class="form-group row">
                            


                              <label class="col-form-label col-lg-2">Select<sup>*</sup></label>
                            

                                    <div class="col-lg-4 error-msg">

                                        <input type="text" id="company" name="company" class="form-control " value="{{$data->company->name}}" readonly>
                                        <input type="hidden" id="company_id" name="company_id" class="form-control " value="{{$data->company->id}}">

                                    </div>



                                    <label class="col-form-label col-lg-2">Select<sup>*</sup></label>

                                    <div class="col-lg-4 error-msg">

                                       <select name = "type" class="form-control" id="type">

                                           <option value=' '>---Please Select---</option>

                                           <option value="15"

                                           @if($data->type ==15)

                                           selected

                                           @endif>

                                           Shareholder</option>

                                            <option value="19"  @if($data->type ==19)

                                           selected

                                           @endif>Director</option>

                                           </select>

                                    </div>

                                 

                                </div>
                                <div class="form-group row">

                                <label class="col-form-label col-lg-2">Member Id (CI) Verify</label>

                                <div class="col-lg-4  error-msg">
                                    

                                    <input type="text" id="member_id" name="member_id" class="form-control " value="{{$member->member_id ?? ''}}" readonly>
                                    <input type="hidden" id="memberid" name="memberid" class="form-control " value="">

                                </div>

                                <label class="col-form-label col-lg-2">SSB Account Verify</label>

                                <div class="col-lg-4  error-msg">

                                    <input type="text" id="ssb_account" name="ssb_account" class="form-control " value="{{$data->ssb_account}}" readonly>
                                    <input type="hidden" name="ssb_id" id="ssb_id" value="{{$data->ssb_account_id}}">

                                    </div>

                                </div>
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-2">Name Of Person<sup>*</sup></label>

                                    <div class="col-lg-4 error-msg">

                                        <input type="text" id="name" name="name" class="form-control " value="{{$data->name}}">

                                    </div>

                                   <label class="col-form-label col-lg-2">Father Name<sup>*</sup></label>

                                    <div class="col-lg-4  error-msg">

                                        <input type="text" id="father_name" name="father_name" class="form-control " value="{{$data->father_name}}">

                                    </div>

                                    

                                </div>
                                

                                <div class="form-group row">

                                <label class="col-form-label col-lg-2">Address<sup>*</sup></label>

                                     <div class="col-lg-4 error-msg">

                                        <input type="text" id="address" name="address" class="form-control " value="{{$data->address}}">

                                    </div>

                                    <label class="col-form-label col-lg-2">PAN Card<sup>*</sup></label>

                                    <div class="col-lg-4 error-msg">

                                        <input type="text" id="pan_no" name="pan_no" class="form-control "value="{{$data->pan_card}}" readonly>

                                    </div>

                                    
                                </div>

                                <div class="form-group row">

                                <label class="col-form-label col-lg-2">Aadhar Card<sup>*</sup></label>

                                     <div class="col-lg-4 error-msg">

                                        <input type="text" id="aadhar_no" name="aadhar_no" class="form-control " value="{{$data->aadhar_card}}" readonly>

                                    </div>


                                    <label class="col-form-label col-lg-2">Firm Name</label>

                                    <div class="col-lg-4">

                                        <input type="text" id="firm_name" name="firm_name" class="form-control " value="{{$data->firm_name}}" >

                                    </div>

                                    

                                </div>

                                <div class="form-group row">

                                <label class="col-form-label col-lg-2">Contact Number<sup>*</sup></label>

                                     <div class="col-lg-4 error-msg">

                                        <input type="text" id="contact_no" name="contact_no" class="form-control " value="{{$data->contact}}" >

                                    </div>

                                    <label class="col-form-label col-lg-2">Email Address</label>

                                    <div class="col-lg-4  error-msg">

                                        <input type="text" id="email" name="email" class="form-control " value="{{$data->email}}">

                                    </div>

                                    

                                </div>
                                <div class="form-group row">
                                <label class="col-form-label col-lg-2">Date<sup>*</sup></label>
                                    <div class="col-lg-4 error-msg">
                                        <input type="text" id="date1" name="date1" class="form-control " readonly value='{{date("d/m/Y", strtotime($data->created_at))}}'>
                                        <input type="hidden" class="form-control create_application_date " name="create_application_date" id="create_application_date"  >
                                    </div>
                                    </div>

                                
                                <h3 class="card-title">Bank Account Details</h3>

                                <div class="form-group row">

                                    <label class="col-form-label col-lg-2">Bank Name<sup>*</sup></label>

                                    <div class="col-lg-4 error-msg">

                                        <input type="text" id="bank_name" name="bank_name" class="form-control " value="{{$data->bank_name}}" >

                                    </div>

                                    <label class="col-form-label col-lg-2">Branch Name<sup>*</sup></label>

                                     <div class="col-lg-4 error-msg">

                                        <input type="text" id="branch_name" name="branch_name" class="form-control " value="{{$data->branch_name}}" >

                                    </div>

                                </div>

                                <div class="form-group row">

                                    <label class="col-form-label col-lg-2">Account Number<sup>*</sup></label>

                                    <div class="col-lg-4  error-msg">

                                        <input type="text" id="account_number" name="account_number" class="form-control " value="{{$data->account_number}}">

                                    </div>

                                    <label class="col-form-label col-lg-2">IFSC Code<sup>*</sup></label>

                                     <div class="col-lg-4  error-msg">

                                        <input type="text" id="ifsc_code" name="ifsc_code" class="form-control " value="{{$data->ifsc_code}}">

                                    </div>

                                </div>

                                

                                <div class="form-group row">

                                 <!--    <label class="col-form-label col-lg-2">Amount<sup>*</sup></label>

                                    <div class="col-lg-4  error-msg">

                                        <input type="text" id="amount" name="amount" class="form-control " value="{{number_format((float) $data->amount, 2, '.', '')}}" readonly>

                                    </div> -->

                                    <label class="col-form-label col-lg-2">Remark<sup>*</sup></label>

                                     <div class="col-lg-4  error-msg">

                                        <input type="text" id="remark" name="remark" class="form-control " value="{{$data->remark}}">

                                    </div>

                                </div>

                                 <div class="text-right">

                                    <input type="submit" name="submitform" value="Update" class="btn btn-primary ">

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

    @include('templates.admin.shareholder.partials.edit_script')

@stop