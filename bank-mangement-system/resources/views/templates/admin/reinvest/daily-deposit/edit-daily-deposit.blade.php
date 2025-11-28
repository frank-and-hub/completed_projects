<div class="form-group row">
  <label class="col-form-label col-lg-2">Daily Deposit Amount</label>
  <div class="col-lg-4">
    <div class="rupee-img">
    </div>
    <input type="text" name="amount" id="amount" class="form-control rupee-txt dd-amount" value="{{ $investments->deposite_amount }}"  @if($action != 'edit') readonly="" @endif>
    <label id="balance-error" class="error"></label>
  </div>

  <label class="col-form-label col-lg-2">Duration<sup>*</sup></label>
  <div class="col-lg-4">
    <select name="tenure" id="tenure" class="form-control dd-tenure" title="Please select something!" @if($action != 'edit') disabled="" @endif>
      <option value="">Select Tenure</option>
      <option @if($investments->tenure == 12) selected @endif value="12">12  Months</option>
      <option @if($investments->tenure == 24) selected @endif value="24">24  Months</option>
      <option @if($investments->tenure == 36) selected @endif value="36">36  Months</option>
      <option @if($investments->tenure == 60) selected @endif value="60">60  Months</option>
    </select>
  </div>
</div>

<div class="form-group row">
  <label class="col-form-label col-lg-2">Payment Mode<sup>*</sup></label>
  <div class="col-lg-4">
    <select name="payment-mode" id="payment-mode" class="form-control" title="Please select something!"  @if($action != 'edit') disabled="" @endif>
      <option value="">Select Mode</option>
      <option data-val="cash" value="0" @if($investments->payment_mode == 0) selected @endif >Cash</option>
      <option data-val="cheque-mode" value="1" @if($investments->payment_mode == 1) selected @endif >Cheque</option>
      <option data-val="online-transaction-mode" value="2" @if($investments->payment_mode == 2) selected @endif >Online transaction</option>
      <option data-val="ssb-account" value="3" @if($investments->payment_mode == 3) selected @endif >SSB account</option>
    </select>
  </div>
</div> 
<input type="hidden" name="interest-rate" class="form-control rupee-txt dd-interest-rate" value="{{ $investments->interest_rate }}">
<input type="hidden" name="maturity-amount" class="form-control rupee-txt dd-maturity-amount-val" value="{{ $investments->maturity_amount }}">
<h4 class="dd-maturity-amount">Maturity Amount :{{ $investments->maturity_amount }}</h4>

@include('templates.admin.reinvest.partials.edit-payment-mode')
@include('templates.admin.reinvest.partials.edit-nominees')