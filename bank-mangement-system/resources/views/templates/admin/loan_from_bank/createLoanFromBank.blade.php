@extends('templates.admin.master')



@section('content')



<div class="loader" style="display: none;"></div>



<div class="content">

    <div class="row">

        <div class="col-md-12">

            <!-- Basic layout-->

            <div class="card">

                <div class="card-header header-elements-inline">

                    <div class="card-body">

                        <form method="post" action="{!! route('admin.save.loan-from-bank') !!}" id="loan_from_bank">

                            @csrf

                            <input type="hidden" name="type">
                            <input type="hidden" id="create_application_date" name="create_application_date" class="form-control create_application_date" readonly="true" autocomplete="off">




                            <div class="form-group row">

                                @php
                                $dropDown = $company;
                                $filedTitle = 'Company';
                                $name = 'company_id';
                                @endphp
                                @include('templates.GlobalTempletes.new_role_type',['dropDown'=>$dropDown,'filedTitle'=>$filedTitle,'name'=>$name,'value'=>'','multiselect'=>'false','design_type'=>4,'branchShow'=>true,'branchName'=>'branch_id','apply_col_md'=>true,'multiselect'=>false,'placeHolder1'=>'Please Select Company','placeHolder2'=>'Please Select Branch'])

                            </div>
                            <div class="form-group row">

                                <label class="col-form-label col-lg-2">Bank Name<sup>*</sup></label>

                                <div class="col-lg-4 error-msg">

                                    <input type="text" name="bank_name" class="form-control">

                                </div>
                                <label class="col-form-label col-lg-2">Address<sup>*</sup></label>

                                <div class="col-lg-4 error-msg">

                                    <input type="text" name="address" class="form-control">

                                </div>

                            </div>
                            <div class="form-group row">


                                <label class="col-form-label col-lg-2">Start Date<sup>*</sup></label>

                                <div class="col-lg-4 error-msg">

                                    <input type="text" id="start_date" name="start_date" class="form-control  " readonly>
                                    <input type="hidden" id="created_at" name="created_at" class="form-control created_at ">

                                </div>

                                <label class="col-form-label col-lg-2">End Date<sup>*</sup></label>

                                <div class="col-lg-4 error-msg">

                                    <input type="text" name="end_date" id="end_date" class="form-control" readonly>

                                </div>
                            </div>

                            <div class="form-group row">

                                <label class="col-form-label col-lg-2">Loan Amount</label>

                                <div class="col-lg-4  error-msg">

                                    <input type="text" id="loan_amount" name="loan_amount" class="form-control ">

                                </div>


                                <label class="col-form-label col-lg-2">Number of Emi</label>

                                <div class="col-lg-4">

                                    <input type="text" id="no_of_emi" name="no_of_emi" class="form-control ">

                                </div>

                            </div>

                            <div class="form-group row">

                                <label class="col-form-label col-lg-2">Emi Amount<sup>*</sup></label>

                                <div class="col-lg-4 error-msg">

                                    <input type="text" id="emi_amount" name="emi_amount" class="form-control ">

                                </div>

                                <label class="col-form-label col-lg-2">Remark<sup>*</sup></label>

                                <div class="col-lg-4 error-msg">

                                    <input type="text" id="remark" name="remark" class="form-control ">

                                </div>

                            </div>

                            <div class="form-group row">

                                <label class="col-form-label col-lg-2">Loan Account Number<sup>*</sup></label>

                                <div class="col-lg-4 error-msg">

                                    <input type="text" id="loan_account_no" name="loan_account_number" class="form-control">


                                </div>

                                <label class="col-form-label col-lg-2">Loan Interest Rate<sup>*</sup></label>

                                <div class="col-lg-4 error-msg">

                                    <input type="text" id="loan_interest_rate" name="loan_interest_rate" class="form-control ">

                                </div>

                            </div>

                            <div class="form-group row">
                                <label class="col-form-label col-lg-2">Select Type<sup>*</sup></label>
                                <div class="col-lg-4 error-msg">
                                    <select class="form-control" name="head_type" id="head_type">
                                        <option value=''>--- Please Select Head Type ---</option>
                                        <option value='230'> Secure Loan </option>
                                        <option value='231'>In-Secure Loan</option>
                                    </select>
                                </div>
                                <label class="col-form-label col-lg-2">Received Type<sup>*</sup></label>
                                <div class="col-lg-4 error-msg">
                                    <select class="form-control" name="received_type" id="received_type">
                                        <option value=''>--- Please Received Type ---</option>
                                        <option value='1'> Bank </option>
                                        <option value='2'> Vendor </option>
                                    </select>
                                </div>
                            </div>




                            <div class="form-group row bank_detailget" style="display: none;" id="bank_detailget">
                                <div class="col-lg-12">
                                    <h3 class="card-title">Received Bank</h3>
                                </div>
                                <label class="col-form-label col-lg-2">Bank Name<sup>*</sup></label>
                                <div class="col-lg-4 error-msg">
                                    <select class="form-control" name="received_bank_name" id="received_bank_name">
                                        <option value=''>--- Please Select Company ---</option>
                                        <!-- @foreach($banks as $bank)
                                        <option value="{{$bank->id}}">{{$bank->bank_name}}</option>
                                        @endforeach -->
                                    </select>
                                </div>
                                <label class="col-form-label col-lg-2">Bank Account<sup>*</sup></label>
                                <div class="col-lg-4 error-msg">
                                    <select class="form-control" name="received_bank_account" id="received_bank_account">
                                        <option value=''>--- Please Select Bank Account ---</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row vendor_detail" style="display: none;" id="vendor_detail">
                                <div class="col-lg-12">
                                    <h3 class="card-title">Received Vendor</h3>
                                </div>
                                <label class="col-form-label col-lg-2">Vendor Name<sup>*</sup></label>
                                <div class="col-lg-4 error-msg">
                                    <select class="form-control" name="vendor_id" id="vendor_id">
                                        <option value=''>--- Please Select Company ---</option>
                                        <!-- @foreach($vendor as $val)
                                        <option value="{{$val->id}}">{{$val->name}}</option>
                                        @endforeach -->
                                    </select>
                                </div>
                                <label class="col-form-label col-lg-2">Vendor Bill<sup>*</sup></label>
                                <div class="col-lg-4 error-msg">
                                    <select class="form-control" name="vendor_bill_id" id="vendor_bill_id">
                                        <option value=''>--- Please Select Bill ---</option>
                                    </select>
                                </div>

                                <label class="col-form-label col-lg-2">Bill Balance<sup>*</sup></label>
                                <div class="col-lg-4 error-msg">
                                    <input type="text" id="bill_amount_due" name="bill_amount_due" class="form-control " readonly value="0.00">
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

@include('templates.admin.loan_from_bank.partials.script_create_loan')

@stop