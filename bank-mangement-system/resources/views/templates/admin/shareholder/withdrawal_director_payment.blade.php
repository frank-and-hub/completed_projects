@extends('templates.admin.master')



@section('content')
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

                            <form method="post" action="{!! route('admin.director_withdrawal_payment_transaction') !!}" id="shareholder_form">

                                 @csrf

                                <input type="hidden" name="type">

                                
								                <input type="hidden" class="form-control created_at " name="created_at" id="created_at"  >
                                <input type="hidden" class="form-control create_application_date " name="create_application_date" id="create_application_date"  >
								
								
                                
                                 

                                <div class="form-group row">
                                @include('templates.GlobalTempletes.role_type',['dropDown'=>$company,'filedTitle'=>'Company','name'=>'company_id','apply_col_md'=>true])

                                  <label class="col-form-label col-lg-2">Director<sup>*</sup></label>

                                  <div class="col-lg-4 error-msg">

                                     <select name = "name" class="form-control" id="head_type">

                                       <option value=''>---Please Select---</option>

                                       <input type="hidden" name= "company_id" id="company_id" >

                                      </select>

                                  </div>

                                  

                                </div>
                                

                                <div class="form-group row">
                                <label class="col-form-label col-lg-2">Father Name<sup>*</sup></label>

                                <div class="col-lg-4  error-msg">

                                    <input type="text" id="father_name" name="father_name" class="form-control " readonly="true">

                                </div>

                                  <label class="col-form-label col-lg-2">Address<sup>*</sup></label>

                                    <div class="col-lg-4 error-msg">

                                          <input type="text" id="address" name="address" class="form-control " readonly="true">

                                      </div>

                                  

                                </div>

                                <div class="form-group row">

                                    <label class="col-form-label col-lg-2">PAN Card<sup>*</sup></label>

                                    <div class="col-lg-4 error-msg">

                                        <input type="text" id="pan_no" name="pan_no" class="form-control " readonly="true">

                                    </div>

                                    <label class="col-form-label col-lg-2">Aadhar Card<sup>*</sup></label>

                                     <div class="col-lg-4 error-msg">

                                        <input type="text" id="aadhar_no" name="aadhar_no" class="form-control " readonly="true">

                                    </div>

                                     

                                </div>

                                <div class="form-group row">

                                  <label class="col-form-label col-lg-2">Amount<sup>*</sup></label>

                                    <div class="col-lg-4  error-msg">

                                        <input type="text" id="amount" name="amount" class="form-control " readonly="true">

                                    </div>

                                  <label class="col-form-label col-lg-2">Withdrawal Amount<sup>*</sup></label>

                                   <div class="col-lg-4 error-msg">

                                      <input type="text" id="withdrawal_amount" name="withdrawal_amount" class="form-control ">

                                  </div>


                                </div>
                                <div class="form-group row"> 

                                    <label class="col-form-label col-lg-2">Created Date<sup>*</sup></label>
                                    <div class="col-lg-4 error-msg">
                                        <input type="text" id="rgister_date" name="rgister_date" class="form-control " readonly> 
                                    </div>
                                    

                                    <label class="col-form-label col-lg-2">Payment Type<sup>*</sup></label>

                                    <div class="col-lg-4 error-msg">

                                      <select name="payment_type" id="payment_type" class="form-control">

                                          <option value="">Select Mode</option>

                                          <option data-val="cash" value="0">Cash</option>

                                          <option data-val="bank" value="1">Bank</option>

                                          <option data-val="ssb" value="2">SSB</option>

                                      </select>

                                    </div>
                                    
                                </div>

                                <div class="form-group row">
                                <label class="col-form-label col-lg-2">Payment Date<sup>*</sup></label>
                                    <div class="col-lg-4 error-msg">
                                        <input type="text" id="date" name="date" class="form-control " readonly> 
                                    </div>
                                </div>

                                <!-- on payment Mode Cash -->

                                <div class="col-lg-12 cash_mode"   style="display: none">

                                  <div class=" row"> <h4 >Cash Details</h4></div>

                                </div>

                                <div class="cash_mode"style="display: none;" >

                                  <div class="form-group row" >

                                    <label class="col-form-label col-lg-2">Branch<sup>*</sup></label>

                                     <div class="col-lg-4 error-msg">

                                      <select class="form-control" name="branch" id="branch">

                                        <option value=" ">---Please Select Branch---</option>

                                         

                                      </select>

                                    </div>

                                    <label class="col-form-label col-lg-2">Branch Code<sup>*</sup></label>

                                       <div class="col-lg-4 error-msg">

                                        <input type="text" id="branch_code" name="branch_code" class="form-control " readonly="true">

                                      </div>  

                                  </div>

                                </div>  

                                <div class="cash_mode"style="display: none;" >

                                   <div class="form-group row">

                                    <label class="col-form-label col-lg-2">DayBook<sup>*</sup></label>

                                    <div class="col-lg-4 error-msg">

                                        <select name="daybook" id="daybook" class="form-control" > 

                                          <option value="0"> Cash </option>

                                        </select>  

                                        </div>

                                      <label class="col-form-label col-lg-2">Total Balance<sup>*</sup></label>

                                  <div class="col-lg-4 error-msg">

                                      <input type="text" name="branch_total_balance" id="branch_total_balance" class="form-control" readonly placholder="0.00">

                                  </div>

                                </div>

                              </div>  



                                <!-- on payment Mode Bank -->

                                 <div class="col-lg-12 bank_mode"   style="display: none">

                                    <div class=" row"> <h4 >Bank Details</h4></div>

                                </div>

                                 <div class="form-group row bank_mode" id="bank_mode"style="display: none;">

                                    

                                    <label class="col-form-label col-lg-2">Bank Name<sup>*</sup></label>

                                     <div class="col-lg-4 error-msg">

                                      <select class="form-control" name="bank" id="bank">

                                        <option value="">---Please Select Bank ---</option>

                                        
                                      </select>

                                    </div>

                                     <label class="col-form-label col-lg-2">Bank Account<sup>*</sup></label>

                                     <div class="col-lg-4 error-msg">

                                      <select class="form-control" name="bank_account" id="bank_account">

                                         

                                      </select>

                                    </div>
                                
                                    

                            </div>
                            <div class="form-group row" id="transaction_mode"style="display: none;">

                                      

                                      <label class="col-form-label col-lg-2">Payment Mode<sup>*</sup></label>

                                        <div class="col-lg-4 error-msg">

                                              <select class="form-control" name="payment_mode" id="payment_mode">

                                                <option value="">---Please Select Payment Mode --</option>

                                                    <option value="0">Cheque</option>

                                                    <option value="1">Online Transaction</option>

                                                    

                                              </select>

                                        </div>
                                        

                                      </div>
                            <div class="form-group row" id="transaction_mode"style="display: none;">

                               

                                 <label class="col-form-label col-lg-2">Available Balance<sup>*</sup></label>

                                  <div class="col-lg-4 error-msg">

                                      <input type="text" name="bank_available_balance" id="bank_available_balance" class="form-control" readonly placholder="0.00">

                                  </div>


                                  

                            </div>

                          

                            <!-- Cheque -->

                            <div class="col-lg-12 cheque_mode"   style="display: none">

                             <div class=" row"> <h4 >Cheque</h4></div>

                          </div>

                            <div class="form-group row cheque_mode"  style="display: none">

                            

                                <label class="col-form-label col-lg-2">Cheque Number<sup>*</sup></label>

                                <div class="col-lg-4 error-msg">

                                      <select name="cheque_number" id="cheque_number" class="form-control">

                                                <option value="">----Please Select----</option>

                                               

                                            </select>

                                </div>

                                

                            </div>

                            <!-- Online -->

                          <div class="col-lg-12 utr_mode"   style="display: none">

                             <div class=" row"> <h4 >Online Transaction</h4></div>

                          </div>

                            <div class="form-group row utr_mode"  style="display: none">

                               <label class="col-form-label col-lg-2">UTR Number<sup>*</sup></label>

                                <div class="col-lg-4 error-msg">

                                      <input type="text" name="utr_number" class="form-control">

                                </div>

                             

                                 <label class="col-form-label col-lg-2">RTGS/NEFT Charge<sup>*</sup></label>

                                <div class="col-lg-4 error-msg">

                                      <input type="text" name="neft_charge" class="form-control">

                                </div>

                           </div>

                           <!-- SSB -->

                             <div class="col-lg-12 ssb_mode"   style="display: none">

                             <div class=" row"> <h4 >SSB Details </h4></div>

                          </div>

                            <div class="form-group row ssb_mode"  style="display: none">

                               <label class="col-form-label col-lg-2">SSB Account Number<sup>*</sup></label>

                                <div class="col-lg-4 error-msg">

                                  <input type="hidden" name="ssbid" id="ssbid" class="form-control" readonly="true">

                                      <input type="text" name="ssb_account_number" id="ssb_account_number" class="form-control" readonly="true">

                                </div>

                                <label class="col-form-label col-lg-2">Member Id (CI)<sup>*</sup></label>

                                <div class="col-lg-4 error-msg">

                                      <input type="text" name="member_id" id="member_id" class="form-control" readonly="true">

                                </div>


                               <label class="col-form-label col-lg-2">Account Holder Name

                                <sup>*</sup></label>
                                <div class="col-lg-4 error-msg">                                

                                      <input type="text" name="ssb_account_holder_name" id="ssb_account_holder_name" class="form-control" readonly="true">

                                </div>                              

                           
                               <label class="col-form-label col-lg-2">Account Balance

                                <sup>*</sup></label>
                                <div class="col-lg-4 error-msg">                                

                                      <input type="text" name="ssbbalance" id="ssbbalance" class="form-control" readonly="true">

                                </div>                              

                            

                            </div>

                            

                           <!-- End -->

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

    @include('templates.admin.shareholder.partials.director_withdrawal_script')

@stop