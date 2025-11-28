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
                            <form method="post" action="{!! route('admin.store_director_deposit_payment') !!}" id="shareholder_form">
                                 @csrf
                                <input type="hidden" name="type">
                                <!-- <input type="hidden" name="company_id" id="company_id"> -->
                                <input type="hidden" class="form-control created_at " name="created_at" id="created_at"  >
                                <input type="hidden" class="form-control create_application_date " name="create_application_date" id="create_application_date"  >
                                
                                <div class="form-group row">
                                @include('templates.GlobalTempletes.role_type',['dropDown'=>$company,'filedTitle'=>'Company','name'=>'company_id','apply_col_md'=>true])

                                    <label class="col-form-label col-lg-2">Shareholder<sup>*</sup></label>
                                    <div class="col-lg-4 error-msg">
                                       <select name = "name" class="form-control" id="head_type">
                                           <option value=''>---Please Select Shareholder---</option>
                                           </select>
                                    </div>

                                    <!-- <label class="col-form-label col-lg-2">Company Name <sup>*</sup></label>

                                    <div class="col-lg-4  error-msg">

                                    <input type="text" id="company" name="company" class="form-control " value="" readonly>

                                    </div> -->
                                
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

                                    <label class="col-form-label col-lg-2">Deposit Amount<sup>*</sup></label>
                                     <div class="col-lg-4 error-msg">
                                        <input type="text" id="deposit_amount" name="deposit_amount" class="form-control ">
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
                                  <div class=" row"> <h4 >Cash</h4></div>
                            </div>
                                <div class="cash_mode"style="display: none;" >
                                <div class="form-group row" >
                                    
                                    <label class="col-form-label col-lg-2">Branch<sup>*</sup></label>
                                     <div class="col-lg-4 error-msg">
                                      <select class="form-control" name="branch" id="branch">
                                        <option value="">---Please Select Branch---</option>
                                          
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
                                    
                                </div>
                                 <div class="form-group row bank_mode" id="bank_mode"style="display: none;">
                                    
                                   
                                     
                                    
                            </div>
                            <div class="form-group row" id="transaction_mode"style="display: none;">
                                 <div class="col-lg-12"><h4 >Bank</h4></div>
                                <label class="col-form-label col-lg-2">Payment Mode<sup>*</sup></label>
                                <div class="col-lg-4 error-msg">
                                      <select class="form-control" name="payment_mode" id="payment_mode">
                                        <option value="">---Please Select Payment Mode --</option>
                                            <option value="1">Cheque</option>
                                            <option value="2">Online Transaction</option>
                                      </select>
                                </div>
                            </div>
                            <!-- Cheque -->
                             <div class="col-lg-12 payment_mode_cheque"   style="display: none">
                             <div class=" row"> <h4 >Cheque</h4></div>
                         </div>
                            <div class="payment_mode_cheque"style="display: none;" >
                                <div class="form-group row" >
                                    <label class="col-form-label col-lg-2"> Cheque No.<sup>*</sup></label>
                                <div class="col-lg-4 error-msg">
                                   <select name="cheque_no" id="cheque_no" class="form-control" >
                                    <option value="">----  Select Cheque----</option>
                                    
                                   </select> 
                                </div>
                              
                            </div>
                            </div>  
                            <!-- Cheque Detail -->
                                <div class="col-lg-12 cheque"   style="display: none">
                             <div class=" row"> <h4 >Cheque Details</h4></div>
                         </div>
                              <div class="cheque"style="display: none;" >
                                <div class="form-group row" >
                                     <label class="col-form-label col-lg-2">Cheque Number<sup>*</sup></label>
                                <div class="col-lg-4 error-msg">
                                   <input type="text" name="cheque_number" id="cheque_number" class="form-control" readonly>
                                </div>
                               <label class="col-form-label col-lg-2">Cheque Amount<sup>*</sup></label>
                                <div class="col-lg-4 error-msg">
                                   <input type="text" name="cheque_amount" id="cheque_amount" class="form-control" readonly>
                                </div>
                            </div>
                            </div>  
                            <div class="cheque"style="display: none;" >
                                <div class="form-group row" >
                                    <label class="col-form-label col-lg-2">Party Name<sup>*</sup></label>
                                <div class="col-lg-4 error-msg">
                                   <input type="text" name="cheque_party_name" id="cheque_party_name" class="form-control" readonly>
                                </div>
                         
                              <label class="col-form-label col-lg-2">Party Bank <sup>*</sup></label>
                                <div class="col-lg-4 error-msg">
                                   <input type="text" name="cheque_party_bank" id="cheque_party_bank" class="form-control" readonly>
                                </div>
                            </div>
                            </div>  
                            <div class="cheque"style="display: none;" >
                                 <div class="form-group row">
                                <label class="col-form-label col-lg-2">Party Bank A/c<sup>*</sup></label>
                                <div class="col-lg-4 error-msg">
                                   <input type="text" name="cheque_party_bank_ac" id="cheque_party_bank_ac" class="form-control" readonly>
                                </div>
                               <label class="col-form-label col-lg-2">Receive Bank <sup>*</sup></label>
                                <div class="col-lg-4 error-msg">
                                   <input type="text" name="cheque_deposit_bank" id="cheque_deposit_bank" class="form-control" readonly>
                                </div>
                              </div>
                            </div>  
                            <div class="cheque"style="display: none;" >
                                 <div class="form-group row">
                                <label class="col-form-label col-lg-2">Receive Bank A/c <sup>*</sup></label>
                                <div class="col-lg-4 error-msg">
                                   <input type="text" name="cheque_deposit_bank_ac" id="cheque_deposit_bank_ac" class="form-control" readonly>
                                </div>
                              <label class="col-form-label col-lg-2">Cheque Date<sup>*</sup></label>
                                <div class="col-lg-4 error-msg">
                                   <input type="text" name="cheque_deposit_date" id="cheque_deposit_date" class="form-control" readonly>
                                </div>
                              </div>
                            </div>  
                            <!-- Online -->
                            <div class="col-lg-12 payment_mode_online"   style="display: none">
                             <div class=" row"> <h4 >Online Transaction</h4></div>
                         </div>
                              <div class="payment_mode_online"style="display: none;" >
                                <div class="form-group row" >
                                   <label class="col-form-label col-lg-2"> Receive Bank<sup>*</sup></label>
                                <div class="col-lg-4 error-msg">
                                   <select name="online_bank" id="online_bank" class="form-control" >
                                    <option value="">----  Select Bank----</option>
                                    
                                   </select> 
                                </div>
                               <label class="col-form-label col-lg-2"> Receive Bank A/c<sup>*</sup></label>
                                <div class="col-lg-4 error-msg">
                                   <select name="online_bank_ac" id="online_bank_ac" class="form-control" >
                                    <option value="">----  Select Bank A/c----</option> 
                                   </select> 
                                </div>
                            </div>
                            </div>  
                            <div class="payment_mode_online"style="display: none;" >
                                <div class="form-group row" >
                                    <label class="col-form-label col-lg-2">UTR/Transaction Date<sup>*</sup></label>
                              <div class="col-lg-4 error-msg">
                                  <input type="text" name="utr_date" id="utr_date" class="form-control" readonly>
                              </div>
                         
                              <label class="col-form-label col-lg-2">UTR No./Transaction No.<sup>*</sup></label>
                              <div class="col-lg-4 error-msg">
                                  <input type="text" name="utr_no" id="utr_no" class="form-control" placeholder="UTR No./Transaction No. ">
                              </div>
                            </div>
                            </div>  
                            <div class="payment_mode_online"style="display: none;" >
                                 <div class="form-group row">
                                 <label class="col-form-label col-lg-2">Transaction Bank Name<sup>*</sup></label>
                              <div class="col-lg-4 error-msg">
                                  <input type="text" name="transaction_bank" id="transaction_bank" class="form-control" placeholder="Transaction Bank ">
                               </div>   
                              <label class="col-form-label col-lg-2">Transaction Bank A/c<sup>*</sup></label>
                              <div class="col-lg-4 error-msg">
                                  <input type="text" name="transaction_bank_ac" id="transaction_bank_ac" class="form-control" placeholder="Transaction Bank A/c">
                              </div>
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
    @include('templates.admin.shareholder.partials.script_director_payment_share')
@stop