<div class="form-group row">
  <label class="col-form-label col-lg-2">Enter SSB Account Number<sup>*</sup></label>
  <div class="col-lg-10">
    <input type="text" name="ssbacount" id="ssbacount" class="form-control" value="{{ $investments->ssb_account_number }}" @if($action != 'edit') readonly="" @endif>
    <label id="balance-error" class="error"></label>
  </div>
</div>

<div class="form-group row">
  <label class="col-form-label col-lg-2">Deposit Amount</label>
  <div class="col-lg-4">
    <div class="rupee-img">
    </div>
    <input type="text" name="amount" id="amount" class="form-control rupee-txt mis-amount" value="{{ $investments->deposite_amount }}" disabled="">
  </div>

  <label class="col-form-label col-lg-2">Payment Mode<sup>*</sup></label>
  <div class="col-lg-4">
    <select name="payment-mode" id="payment-mode" class="form-control" title="Please select something!" @if($action != 'edit') disabled="" @endif>
      <option value="">Select Mode</option>
      <option data-val="cash" value="0" @if($investments->payment_mode == 0) selected @endif >Cash</option>
      <option data-val="cheque-mode" value="1" @if($investments->payment_mode == 1) selected @endif >Cheque</option>
      <option data-val="online-transaction-mode" value="2" @if($investments->payment_mode == 2) selected @endif >Online transaction</option>
      <option data-val="ssb-account" value="3" @if($investments->payment_mode == 3) selected @endif >SSB account</option>
    </select>
  </div>
</div>

<div class="form-group row">
  <label class="col-form-label col-lg-2">Duration<sup>*</sup></label>
  <div class="col-lg-4">
    <select name="tenure" id="tenure" class="form-control mis-tenure" title="Please select something!" @if($action != 'edit') disabled="" @endif>
      <option value="">Select Tenure</option>
      <option @if($investments->tenure == 5) selected @endif  value="60">60  Months</option>
      <option @if($investments->tenure == 7) selected @endif  value="84">84  Months</option>
      <option @if($investments->tenure == 10) selected @endif  value="120">120  Months</option>
    </select>
  </div>

  <label class="col-form-label col-lg-2">Interest Rate (%)<sup></sup></label>
  <div class="col-lg-4">
    <input type="text" name="interest_rate" class="form-control rupee-txt mis-maturity-amount-cal" value="{{ $investments->interest_rate }}" readonly>
    <input type="hidden" name="maturity-amount" class="form-control rupee-txt mis-maturity-amount-val" value="{{ $investments->maturity_amount }}" readonly>
  </div>
</div>
<h4 class="mis-maturity-amount">Maturity Amount :{{ $investments->maturity_amount }}</h4>
@include('templates.admin.reinvest.partials.edit-payment-mode')
@include('templates.admin.reinvest.partials.edit-nominees')