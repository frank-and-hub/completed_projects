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

                        <form method="post" action="{!! route('admin.store_loan_emi') !!}" id="loan_emi">

                            @csrf

                            <input type="hidden" name="loan_from_bank_id" id="loan_from_bank_id">

                            <input type="hidden" name="account_head_id" id="account_head_id">

                            <div class="form-group row">

                                <label class="col-form-label col-lg-2">Loan Account Number<sup>*</sup></label>

                                <div class="col-lg-4 error-msg">

                                    <select class="form-control" name="loan_account_number" id="loan_account_number">

                                        <option value=''>--- Please Select Account Number ---</option>

                                        @foreach($account as $loanAccount)

                                        <option value="{{$loanAccount->id}}">{{$loanAccount->loan_account_number}}</option>

                                        @endforeach

                                    </select>

                                </div>



                                <label class="col-form-label col-lg-2">Bank Name<sup>*</sup></label>

                                <div class="col-lg-4 error-msg">

                                    <input type="text" name="bank_name" class="form-control" id="bank_name" readonly>

                                </div>



                            </div>
                            <div class="form-group row">

                                <label class="col-form-label col-lg-2">Loan Created Date <sup>*</sup></label>

                                <div class="col-lg-4  error-msg">

                                    <input type="text" id="loan_date" name="loan_date" class="form-control   " readonly>

                                </div>
                                <label class="col-form-label col-lg-2">Branch Name<sup>*</sup></label>

                                <div class="col-lg-4 error-msg">
                                  
                                    <input type="text" id="branch_name" name="branch_name" class="form-control   " readonly>
                                </div>


                            </div>


                            <div class="form-group row">
                                <label class="col-form-label col-lg-2">Company Name<sup>*</sup></label>
                                <div class="col-lg-4 error-msg">
                                <select class="form-control" name="company_id" id="company_id" >
                                    <option value=""></option>
                                </select>

                                </div>


                                <label class="col-form-label col-lg-2">Loan Amount<sup>*</sup></label>

                                <div class="col-lg-4 error-msg">

                                    <input type="text" id="loan_amount" name="loan_amount" class="form-control " readonly="true">

                                </div>

                            </div>



                            <div class="form-group row">

                                <label class="col-form-label col-lg-2"> Emi Number <sup>*</sup></label>

                                <div class="col-lg-4">

                                    <input type="text" id="emi_number" name="emi_number" class="form-control ">

                                </div>

                                <label class="col-form-label col-lg-2">Emi Principal Amount<sup>*</sup></label>

                                <div class="col-lg-4 error-msg">

                                    <input type="text" id="emi_principal_amount" name="emi_principal_amount" class="form-control ">

                                </div>

                            </div>

                            <div class="form-group row">

                                <label class="col-form-label col-lg-2"> Emi Interest Amount <sup>*</sup></label>

                                <div class="col-lg-4">

                                    <input type="text" id="emi_interest_rate" name="emi_interest_rate" class="form-control ">

                                </div>

                                <label class="col-form-label col-lg-2">Remaining Amount <sup>*</sup></label>

                                <div class="col-lg-4">

                                    <input type="text" id="current_loan_amount" name="current_loan_amount" class="form-control " readonly="true">

                                </div>



                            </div>

                            
                            <div class="form-group row">
                                <label class="col-form-label col-lg-2">Transaction Date <sup>*</sup></label>

                                <div class="col-lg-4  error-msg">

                                    <input type="text" id="date" name="date" class="form-control" readonly>
                                    <input type="hidden" id="created_at" name="created_at" class="form-control created_at ">
                                    <input type="hidden" id="create_application_date" name="create_application_date" class="form-control create_application_date ">

                                </div>

                            </div>



                            <h3 class="card-title">Paid From Bank</h3>

                            <div class="form-group row">

                                <label class="col-form-label col-lg-2">Bank Name<sup>*</sup></label>

                                <div class="col-lg-4 error-msg">

                                    <select class="form-control" name="received_bank_name" id="received_bank_name">

                                        <option value=''>--- Please Select Bank ---</option>


                                    </select>

                                </div>

                                <label class="col-form-label col-lg-2">Bank Account<sup>*</sup></label>

                                <div class="col-lg-4 error-msg">

                                    <select class="form-control" name="received_bank_account" id="received_bank_account">

                                        <option value=''>--- Please Select Bank Account ---</option>

                                    </select>

                                </div>
                                <label class="col-form-label col-lg-2">Bank Balance<sup>*</sup></label>

                                <div class="col-lg-4 error-msg">
                                    <input type="text" id="bank_balance" name="bank_balance" class="form-control " readonly="true">
                                </div>

                            </div>



                            <div class="text-right">

                                <input type="submit" name="submitform" value="Submit" id="submitm" class="btn btn-primary submit">

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

@include('templates.admin.loan_from_bank.partials.script_emi')

@stop