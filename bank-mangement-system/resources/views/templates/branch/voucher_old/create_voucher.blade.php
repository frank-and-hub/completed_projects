@extends('layouts/branch.dashboard')

@section('content')

<?php
$employee_code='';
$remark='';
if(isset($_GET['employee']))
{
  $employee_code=$_GET['employee'];
}

if(old('employee_code'))
{
  $employee_code=old('employee_code');
}
if(old('remark'))
{
  $remark=old('remark');
}

$stateid = getBranchState(Auth::user()->username);
            $globaldate1 = date("d/m/Y", strtotime(checkMonthAvailability(date('d'),date('m'),date('Y'),$stateid)));
          //  echo $globaldate;die;

?>
<div class="loader" style="display: none;"></div>
<div class="container-fluid mt--6">
  <div class="content-wrapper">
          <div class="row">
            <div class="col-lg-12">
                <div class="card bg-white">
                <div class="card-body page-title"> 
                        <h3 class="">Received Voucher Request</h3> 
                       
                         <a href="{{ redirect()->back()->getTargetUrl() }}" style="float:right" class="btn btn-secondary">Back</a>
                </div>
                </div>
            </div>
        </div>
@if ($errors->any())
    <div class="row">
      <div class="col-lg-12">
        <div class="card bg-white">
          <div class="card-body"> 
              
                  <div class="alert alert-danger">
                      <ul>
                          @foreach ($errors->all() as $error)
                              <li>{{ $error }}</li>
                          @endforeach
                      </ul>
                  </div>
              
          </div>
        </div>
      </div>
    </div>
    @endif
    <form action="{!! route('branch.voucher.save') !!}" method="post" enctype="multipart/form-data" id="voucher_save" name="voucher_save"  >
    @csrf
      <div class="row">
        <div class="col-lg-12">
          <div class="card bg-white" > 
            <div class="card-body">
              <h3 class="card-title mb-3">Received Voucher Request</h3>
              <div class="row">
                        <div class="col-lg-6">
                           <div class="form-group row">
                              <label class="col-form-label col-lg-3">Branch<sup class="required">*</sup></label>
                              <div class="col-lg-9 error-msg">

                              	<input type="text" name="branch_name" id="branch_name" class="form-control" placeholder="Branch Code" readonly="" value="{{$branch_name}}">
                                      <input type="hidden" name="branch_id" id="branch_id" class="form-control" placeholder="Branch Code" readonly="" value="{{$branch_id}}">
                                 <input type="hidden" class="form-control created_at " name="created_at" id="created_at"  >
                                 <input type="hidden" class="form-control create_application_date " name="create_application_date" id="create_application_date" value="{{$globaldate1}}">
                              </div>
                           </div>
                        </div>
                        <div class="col-lg-6">
                           <div class="form-group row">
                              <label class="col-form-label col-lg-3">Branch Code<sup class="required">*</sup></label>
                              <div class="col-lg-9 error-msg">
                                  <input type="text" name="branch_code" id="branch_code" class="form-control" placeholder="Branch Code" readonly="" value="{{$branch_code}}">
                              </div>
                           </div>
                        </div>
                        <div class="col-lg-6">
                           <div class="form-group row">
                              <label class="col-form-label col-lg-3">Date<sup class="required">*</sup></label>
                              <div class="col-lg-9 error-msg">
                                 <input type="text" id="date" name="date" class="form-control  "  readonly="true" autocomplete="off" value="{{$globaldate1}}">
                              </div>
                           </div>
                        </div>
                        <div class="col-lg-6">
                           <div class="form-group row">
                              <label class="col-form-label col-lg-3">Account  Head<sup class="required">*</sup></label>
                              <div class="col-lg-9 error-msg">
                                 <select name="head" id="head" class="form-control" >
                                            <option value="">----  Select Head----</option>
                                            <!--<option value="19">Director Capital</option>-->
                                                <!--<option value="15">Shareholder capital</option>-->
                                                <option value="32">Penal Interest</option>
                                                <!--<option value="27">Bank Account </option>-->
                                                <option value="96">Eli Loan</option>
                                           
                                        </select>
                              </div>
                           </div>
                        </div>

                        <div class="col-lg-6" id="director" style= "display:none;">
                           <div class="form-group row">
                              <label class="col-form-label col-lg-3">Director<sup class="required">*</sup></label>
                              <div class="col-lg-9 error-msg">
                                 <select name="director_id" id="director_id" class="form-control" >
                                            <option value="">----  Select Director----</option> 
                                           
                                        </select>
                              </div>
                           </div>
                        </div>


                        <div class="col-lg-6" id="shareholder" style= "display:none;">
                           <div class="form-group row">
                              <label class="col-form-label col-lg-3">Shareholder<sup class="required">*</sup></label>
                              <div class="col-lg-9 error-msg">
                                 <select name="shareholder_id" id="shareholder_id" class="form-control" >
                                            <option value="">----  Select Shareholder----</option> 
                                           
                                        </select>
                              </div>
                           </div>
                        </div>
                        <div class="col-lg-6  bank" style="display:none;">
                           <div class="form-group row">
                              <label class="col-form-label col-lg-3">Bank<sup class="required">*</sup></label>
                              <div class="col-lg-9 error-msg">
                                 <select name="bank_id" id="bank_id" class="form-control" >
                                            <option value="">---- Please Select----</option>
                                            @foreach( $samraddh_bank as $bank)
                                                <option value="{{ $bank->id }}" >{{ $bank->bank_name }}</option>
                                            @endforeach
                                        </select>
                              </div>
                           </div>
                        </div>
                        <div class="col-lg-6 bank" style="display:none;">
                           <div class="form-group row">
                              <label class="col-form-label col-lg-3">Bank A/c<sup class="required">*</sup></label>
                              <div class="col-lg-9 error-msg">
                                 <select name="bank_account" id="bank_account" class="form-control" >
                                            <option value="">---- Please Select----</option>
                                           
                                        </select>
                              </div>
                           </div>
                        </div>
                        <div class="col-lg-6  bank" style="display:none;">
                           <div class="form-group row">
                              <label class="col-form-label col-lg-3">Available Balance<sup class="required">*</sup></label>
                              <div class="col-lg-9 error-msg">
                                 <input type="text" name="bank_balance" id="bank_balance" class="form-control"  readonly>
                              </div>
                           </div>
                        </div>

                        <div class="col-lg-6 penal"  style= "display:none;">
                           <div class="form-group row">
                              <label class="col-form-label col-lg-3">Employee Code<sup class="required">*</sup></label>
                              <div class="col-lg-9 error-msg">
                                 <input type="text" name="emp_code" id="emp_code" class="form-control" >
                                 <input type="hidden" name="emp_id" id="emp_id">
                              </div>
                           </div>
                        </div>

                        <div class="col-lg-6 penal"  style= "display:none;">
                           <div class="form-group row">
                              <label class="col-form-label col-lg-3">Employee Name<sup class="required">*</sup></label>
                              <div class="col-lg-9 error-msg">
                                 <input type="text" name="emp_name" id="emp_name" class="form-control"  readonly>
                              </div>
                           </div>
                        </div>
                        <div class="col-lg-6" id="eli_loan" style= "display:none;">
                           <div class="form-group row">
                              <label class="col-form-label col-lg-3">Eli Loan<sup class="required">*</sup></label>
                              <div class="col-lg-9 error-msg">
                                 <select name="eli_loan_id" id="eli_loan_id" class="form-control" >
                                            <option value="">----  Select Eli Loan----</option> 
                                           
                                        </select>
                              </div>
                           </div>
                        </div>




                        <div class="col-lg-6"  >
                           <div class="form-group row">
                              <label class="col-form-label col-lg-3">Particular<sup class="required">*</sup></label>
                              <div class="col-lg-9 error-msg">
                                 <textarea name="particular" id="particular" class="form-control" placeholder="particular"></textarea>
                              </div>
                           </div>
                        </div>
                        <div class="col-lg-6"  >
                           <div class="form-group row">
                              <label class="col-form-label col-lg-3">Received Mode<sup>*</sup></label>
                              <div class="col-lg-9 error-msg">
                                 <select name="payment_mode" id="payment_mode" class="form-control">
                                            <option value="">----Select Received Mode ----</option>
                                            <option value="0">Cash</option>
                                            <option value="1">Cheque</option>
                                            <option value="2"> Online Transaction </option>
                                        </select>  
                              </div>
                           </div>
                        </div>

                        <div class="col-lg-12 payment_mode_cash"   style="display: none">
                             <div class=" row"> <div class="col-lg-12"><h4 class="mb-3">Cash</h4></div></div>
                         </div>
                        <div class="col-lg-6 payment_mode_cash"   style="display: none">
                             <div class="form-group row">
                                <label class="col-form-label col-lg-3">DayBook<sup>*</sup></label>
                                <div class="col-lg-9 error-msg">
                                   <select name="daybook" id="daybook" class="form-control" > 
                              <option value="0"> Cash </option>
                            </select>  
                                </div>
                             </div>
                        </div> 
                        <div class="col-lg-6 payment_mode_cash"   style="display: none">
                          <div class="form-group row">
                              <label class="col-form-label col-lg-3">Total Balance<sup>*</sup></label>
                              <div class="col-lg-9 error-msg">
                                  <input type="text" name="branch_total_balance" id="branch_total_balance" class="form-control" readonly placholder="0.00">
                              </div>
                          </div>
                        </div>


                        <div class="col-lg-12 payment_mode_online"   style="display: none">
                             <div class=" row"> <div class="col-lg-12"><h4 class="mb-3">Online Transaction</h4></div></div>
                         </div>
                        <div class="col-lg-6 payment_mode_online"   style="display: none">
                             <div class="form-group row">
                                <label class="col-form-label col-lg-3"> Receive Bank<sup>*</sup></label>
                                <div class="col-lg-9 error-msg">
                                   <select name="online_bank" id="online_bank" class="form-control" >
                                    <option value="">----  Select Bank----</option>
                                    @foreach( $samraddh_bank as $bank)
                                    <option value="{{ $bank->id }}" >{{ $bank->bank_name }}</option>
                                    @endforeach
                                   </select> 
                                </div>
                             </div>
                        </div>
                        <div class="col-lg-6 payment_mode_online"   style="display: none">
                             <div class="form-group row">
                                <label class="col-form-label col-lg-3"> Receive Bank A/c<sup>*</sup></label>
                                <div class="col-lg-9 error-msg">
                                   <select name="online_bank_ac" id="online_bank_ac" class="form-control" >
                                    <option value="">----  Select Bank A/c----</option> 
                                   </select> 
                                </div>
                             </div>
                        </div>
                        <div class="col-lg-6 payment_mode_online"   style="display: none">
                          <div class="form-group row">
                              <label class="col-form-label col-lg-3">UTR/Transaction Date<sup>*</sup></label>
                              <div class="col-lg-9 error-msg">
                                  <input type="text" name="utr_date" id="utr_date" class="form-control" >
                              </div>
                          </div>
                        </div>

                        <div class="col-lg-6 payment_mode_online"   style="display: none">
                          <div class="form-group row">
                              <label class="col-form-label col-lg-3">UTR No./Transaction No.<sup>*</sup></label>
                              <div class="col-lg-9 error-msg">
                                  <input type="text" name="utr_no" id="utr_no" class="form-control" placeholder="UTR No./Transaction No. ">
                              </div>
                          </div>
                        </div>
                        <div class="col-lg-6 payment_mode_online"   style="display: none">
                          <div class="form-group row">
                              <label class="col-form-label col-lg-3">Transaction Bank Name<sup>*</sup></label>
                              <div class="col-lg-9 error-msg">
                                  <input type="text" name="transaction_bank" id="transaction_bank" class="form-control" placeholder="Transaction Bank ">
                              </div>
                          </div>
                        </div>
                        <div class="col-lg-6 payment_mode_online"   style="display: none">
                          <div class="form-group row">
                              <label class="col-form-label col-lg-3">Transaction Bank A/c<sup>*</sup></label>
                              <div class="col-lg-9 error-msg">
                                  <input type="text" name="transaction_bank_ac" id="transaction_bank_ac" class="form-control" placeholder="Transaction Bank A/c">
                              </div>
                          </div>
                        </div>
                        <div class="col-lg-6 payment_mode_online"   style="display: none">
                          <div class="form-group row">
                              <label class="col-form-label col-lg-3">Upload Transaction Slip</label>
                              <div class="col-lg-9 error-msg">
                                  <input type="file" class="" name="bank_slip" id="bank_slip" />
                              </div>
                          </div>
                        </div>

                        <div class="col-lg-12 payment_mode_cheque"   style="display: none">
                             <div class=" row"><div class="col-lg-12"><h4 class="mb-3">Cheque</h4></div></div>
                         </div>
                        <div class="col-lg-6 payment_mode_cheque"   style="display: none">
                             <div class="form-group row">
                                <label class="col-form-label col-lg-3"> Cheque No.<sup>*</sup></label>
                                <div class="col-lg-9 error-msg">
                                   <select name="cheque_no" id="cheque_no" class="form-control" >
                                    <option value="">----  Select Cheque----</option>
                                    
                                   </select> 
                                </div>
                             </div>
                        </div>
                        <div class="col-lg-6 cheque"   style="display: none">
                             <div class="form-group row">
                                <label class="col-form-label col-lg-3">Cheque Number<sup>*</sup></label>
                                <div class="col-lg-9 error-msg">
                                   <input type="text" name="cheque_number" id="cheque_number" class="form-control" readonly>
                                </div>
                             </div>
                        </div>

                        <div class="col-lg-6 cheque"   style="display: none">
                             <div class="form-group row">
                                <label class="col-form-label col-lg-3">Cheque Amount<sup>*</sup></label>
                                <div class="col-lg-9 error-msg">
                                   <input type="text" name="cheque_amount" id="cheque_amount" class="form-control" readonly>
                                </div>
                             </div>
                        </div>
                        <div class="col-lg-6 cheque"   style="display: none">
                             <div class="form-group row">
                                <label class="col-form-label col-lg-3">Party Name<sup>*</sup></label>
                                <div class="col-lg-9 error-msg">
                                   <input type="text" name="cheque_party_name" id="cheque_party_name" class="form-control" readonly>
                                </div>
                             </div>
                        </div>
                        <div class="col-lg-6 cheque"   style="display: none">
                             <div class="form-group row">
                                <label class="col-form-label col-lg-3">Party Bank <sup>*</sup></label>
                                <div class="col-lg-9 error-msg">
                                   <input type="text" name="cheque_party_bank" id="cheque_party_bank" class="form-control" readonly>
                                </div>
                             </div>
                        </div>
                        <div class="col-lg-6 cheque"   style="display: none">
                             <div class="form-group row">
                                <label class="col-form-label col-lg-3">Party Bank A/c<sup>*</sup></label>
                                <div class="col-lg-9 error-msg">
                                   <input type="text" name="cheque_party_bank_ac" id="cheque_party_bank_ac" class="form-control" readonly>
                                </div>
                             </div>
                        </div>
                        <div class="col-lg-6 cheque"   style="display: none">
                             <div class="form-group row">
                                <label class="col-form-label col-lg-3">Receive Bank <sup>*</sup></label>
                                <div class="col-lg-9 error-msg">
                                   <input type="text" name="cheque_deposit_bank" id="cheque_deposit_bank" class="form-control" readonly>
                                </div>
                             </div>
                        </div>
                        <div class="col-lg-6 cheque"   style="display: none">
                             <div class="form-group row">
                                <label class="col-form-label col-lg-3">Receive Bank A/c <sup>*</sup></label>
                                <div class="col-lg-9 error-msg">
                                   <input type="text" name="cheque_deposit_bank_ac" id="cheque_deposit_bank_ac" class="form-control" readonly>
                                </div>
                             </div>
                        </div>
                        <div class="col-lg-6 cheque"   style="display: none">
                             <div class="form-group row">
                                <label class="col-form-label col-lg-3">Cheque Date<sup>*</sup></label>
                                <div class="col-lg-9 error-msg">
                                   <input type="text" name="cheque_deposit_date" id="cheque_deposit_date" class="form-control" readonly>
                                </div>
                             </div>
                        </div>



                        <div class="col-lg-6 "   style="">
                          <div class="form-group row">
                              <label class="col-form-label col-lg-3">Received Amount<sup>*</sup></label>
                              <div class="col-lg-9 error-msg">
                                  <input type="text" name="amount" id="amount" class="form-control"  placholder="0.00">
                              </div>
                          </div>
                        </div>
                          
                        






                     </div>
            </div>
          </div>
      
          

           

          
        </div>
        


        <div class="col-lg-12">
          <div class="card bg-white" >            
            <div class="card-body">
              <div class="text-center">
              <button type="submit" class="btn btn-primary">Submit</button>
            </div>
            </div>
          </div>
        </div>

      </div> 
    </form>

  </div>
</div>



     
@stop

@section('script')
 @include('templates.branch.voucher.partials.script')
@stop