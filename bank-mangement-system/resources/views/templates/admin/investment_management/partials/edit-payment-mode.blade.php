<div class="form-group row ssb-account p-mode" @if($investments->payment_mode != 3) style="display:none;" @endif>
  <label class="col-form-label col-lg-2">Account Number</label>
  <div class="col-lg-4">
    <div class="input-group">
      <input type="text" name="account_n" id="account_n" class="form-control" value="@if($investments['ssb']) {{ $investments['ssb']['account_no'] }} @endif" readonly="">
    </div>
  </div>
  <label class="col-form-label col-lg-2">Account Balance</label>
  <div class="col-lg-4">
    <div class="input-group">
      <div class="rupee-img">
      </div>
      <input type="text" name="account_b" id="account_b" class="form-control rupee-txt" value="@if($investments['ssb']) {{ $investments['ssb']['balance'] }} @endif" readonly="">
    </div>
  </div>
</div>

<h3 class="cheque-mode cheque-mode-1 p-mode " @if($investments->payment_mode != 1) style="display:none;" @endif >Cheque Detail</h3>

<div class="form-group row cheque-mode cheque-mode-1 p-mode" @if($investments->payment_mode != 1) style="display:none;" @endif>
    <div class="col-lg-12">
      <label class="col-form-label col-lg-2">Cheque Number</label>
      <div class="col-lg-4">
        <select name="cheque_id" id="cheque_id" class="form-control" title="Please select something!">
            <option value="">Select Cheque</option> 
          </select> 
          @if(count($investments['investmentPayment']) > 0)
            <input type="hidden" name="cheque_id_get" id="cheque_id_get" class="form-control" readonly value="{{$investments['investmentPayment'][0]->cheque_number}}">
          @else
            <input type="hidden" name="cheque_id_get" id="cheque_id_get" class="form-control" readonly value="">
          @endif
      </div>
    </div>
  <div @if($investments->payment_mode != 1) style="display:none;" @endif id='cheque_detail' class="col-lg-12 row">
    <label class="col-form-label col-lg-2">Cheque Number</label>
    <div class="col-lg-4">
      <input type="text" name="cheque-number" id="cheque-number" class="form-control" readonly>
    </div>
    <label class="col-form-label col-lg-2">Bank Name</label>
    <div class="col-lg-4">
      <input type="text" name="bank-name" id="bank-name" class="form-control" readonly>
    </div>
    <label class="col-form-label col-lg-2">Branch Name</label>
    <div class="col-lg-4">
      <input type="text" name="branch-name" id="branch-name" class="form-control" readonly>
    </div>
    <label class="col-form-label col-lg-2">Cheque Date</label>
    <div class="col-lg-4">
      <input type="text" name="cheque-date" id="cheque-date" class="form-control" readonly>
    </div>
    <label class="col-form-label col-lg-2">Cheque Amount</label>
    <div class="col-lg-4">
      <input type="text" name="cheque-amt" id="cheque-amt" class="form-control" readonly>
    </div>
  </div>

  
</div>

<h3 class="online-transaction-mode p-mode" @if($investments->payment_mode != 2) style="display:none;" @endif>Online transaction</h3>
<div class="form-group  online-transaction-mode p-mode" @if($investments->payment_mode != 2) style="display:none;" @endif>
  <div class="form-group row">
  <label class="col-form-label col-lg-2">Transaction Id</label>
  <div class="col-lg-4">
    @if(count($investments['investmentPayment']) > 0)
      <input type="text" name="transaction-id" id="transaction-id" class="form-control" value="{{ $investments['investmentPayment'][0]->transaction_id }}" @if($action != 'edit') readonly="" @endif>
    @else
      <input type="text" name="transaction-id" id="transaction-id" class="form-control" value="" @if($action != 'edit') readonly="" @endif>
    @endif
  </div>
  <label class="col-form-label col-lg-2">Date</label>
  <div class="col-lg-4">
    @if(count($investments['investmentPayment']) > 0)
      <input type="text" name="date" id="date" class="form-control calendardate" value="@if($investments['investmentPayment'][0]->transaction_date){{ date('d/m/Y',strtotime($investments['investmentPayment'][0]->transaction_date) )  }}@endif" @if($action != 'edit') readonly="" @endif>
    @else
      <input type="text" name="date" id="date" class="form-control calendardate" value="" @if($action != 'edit') readonly="" @endif>
    @endif
  </div>
  </div>
  
   <div class="form-group row">
    <label class="col-form-label col-lg-2">Deposit Bank</label>
    <div class="col-lg-4">
      <select name="rd_online_bank_id" id="rd_online_bank_id" class="form-control" >
          <option value="">Select Bank</option> 
      </select>
    </div>
     <label class="col-form-label col-lg-2">Deposit Bank Account</label>
    <div class="col-lg-4">
      <select name="rd_online_bank_ac_id" id="rd_online_bank_ac_id" class="form-control" >
          <option value="">Select Bank  Account</option> 
      </select>
    </div>
  </div>
</div>
