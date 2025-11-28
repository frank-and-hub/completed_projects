<div class="form-group row">
  <label class="col-form-label col-lg-2">Monthly Deposit Amount</label>
  <div class="col-lg-4">
    <div class="rupee-img">
    </div>
    <input type="text" name="amount" id="amount" class="form-control rupee-txt sb-amount" value="{{ $investments->deposite_amount }}" @if($action != 'edit') readonly="" @endif>
    <label id="balance-error" class="error"></label>
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
<input type="hidden" name="maturity-amount" class="form-control rupee-txt sb-maturity-amount-val" value="{{ $investments->maturity_amount }}" readonly>
<h4 class="sb-maturity-amount"><h4 class="sb-maturity-amount"></h4></h4>

@include('templates.admin.reinvest.partials.edit-payment-mode')
@include('templates.admin.reinvest.partials.edit-nominees')