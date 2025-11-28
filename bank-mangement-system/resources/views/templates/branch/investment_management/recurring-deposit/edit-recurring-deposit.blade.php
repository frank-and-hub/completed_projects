<div class="form-group row">
  <label class="col-form-label col-lg-2">Deposit Amount</label>
  <div class="col-lg-4">
    <div class="rupee-img">
    </div>
    <input type="text" name="amount" id="amount" class="form-control rupee-txt" value="{{ $investments->deposite_amount }}" disabled="">
    <label id="balance-error" class="error"></label>
  </div>

  <label class="col-form-label col-lg-2">Payment Mode<sup>*</sup></label>
  <div class="col-lg-4">
    <select name="payment-mode" id="payment-mode" class="form-control" title="Please select something!" disabled="">
      <option value="">Select Mode</option>
      <option data-val="cash" value="0" @if($investments->payment_mode == 0) selected @endif >Cash</option>
      <option data-val="cheque-mode" value="1" @if($investments->payment_mode == 1) selected @endif >Cheque</option>
      <option data-val="online-transaction-mode" value="2" @if($investments->payment_mode == 2) selected @endif >Online transaction</option>
      <option data-val="ssb-account" value="3" @if($investments->payment_mode == 3) selected @endif >SSB account</option>
    </select>
  </div>
</div>
</div>  

<div class="form-group row">
  <label class="col-form-label col-lg-2">Tenure<sup>*</sup></label>
  <div class="col-lg-4">
    <select name="tenure" id="tenure" class="form-control" title="Please select something!" @if($action != 'edit') disabled="" @endif>
      <option value="">Select Tenure</option>
      <option value="36" @if($investments->tenure == 3) selected @endif >36  Months</option>
      <option value="60" @if($investments->tenure == 5) selected @endif >60  Months</option>
      <option value="84" @if($investments->tenure == 7) selected @endif >84  Months</option>
    </select>
  </div>
  <input type="hidden" class="rd-maturity-amount-val" name="maturity-amount" value="{{ $investments->interest_rate }}">
  <input type="hidden" class="rd-interest-rate" name="interest-rate" value="{{ $investments->maturity_amount }}">
</div>
<h4 class="rd-maturity-amount">Maturity Amount :{{ $investments->maturity_amount }}</h4>

@include('templates.branch.investment_management.partials.edit-payment-mode')
@include('templates.branch.investment_management.partials.edit-nominees')