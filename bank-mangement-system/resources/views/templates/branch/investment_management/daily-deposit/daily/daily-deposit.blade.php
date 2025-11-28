<div class="form-group row">
  <label class="col-form-label col-lg-2">Daily Deposit Amount</label>
  <div class="col-lg-4">
    <div class="rupee-img">
    </div>
    <input type="text" name="amount" id="amount" class="form-control rupee-txt dd-amount">
    <label id="balance-error" class="error"></label>
  </div>

  <label class="col-form-label col-lg-2">Duration<sup>*</sup></label>
  <div class="col-lg-4">
    <select name="tenure" id="tenure" class="form-control dd-tenure" title="Please select something!">
      <option value="">Select Tenure</option>
      <option value="12" selected="">12  Months</option>
      <option value="24">24  Months</option>
      <option value="36">36  Months</option>
      <option value="60">60  Months</option>
    </select>
  </div>

</div>

<div class="form-group row">
  <label class="col-form-label col-lg-2">Payment Mode<sup>*</sup></label>
  <div class="col-lg-4">
    <select name="payment-mode" id="payment-mode" class="form-control" title="Please select something!">
      <option value="">Select Mode</option>
      <option data-val="cash" value="0">Cash</option>
      <option data-val="cheque-mode" value="1">Cheque</option>
      <!-- <option data-val="online-transaction-mode" value="2">Online transaction</option> -->
      <option data-val="ssb-account" value="3">SSB account</option>
    </select>
  </div>
</div>
<input type="hidden" name="interest-rate" class="form-control rupee-txt dd-interest-rate" readonly>
<input type="hidden" name="maturity-amount" class="form-control rupee-txt dd-maturity-amount-val" readonly>
<h4 class="dd-maturity-amount"></h4>

@include('templates.branch.investment_management.partials.payment-mode')
@include('templates.branch.investment_management.partials.nominees')