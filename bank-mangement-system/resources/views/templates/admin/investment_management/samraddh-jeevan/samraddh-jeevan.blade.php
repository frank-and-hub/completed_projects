<div class="form-group row">
  <label class="col-form-label col-lg-2">SSB Account Available or Not?<sup>*</sup></label>
  <div class="col-lg-4 error-msg">
    <div class="row">
      <div class="col-lg-4">
        <div class="custom-control custom-radio mb-3 ">
          <input type="radio" nominee-form-class="samraddh-jeevan-nominee-form" data-val="samraddh-jeevan" id="ssb-yes" name="ssb_account_availability" class="custom-control-input ssb-account-availability" value="0" checked>
          <label class="custom-control-label" for="ssb-yes">Yes</label>
        </div>
      </div>
      <div class="col-lg-4">
        <div class="custom-control custom-radio mb-3  ">
          <input type="radio" nominee-form-class="samraddh-jeevan-nominee-form" data-val="samraddh-jeevan" id="ssb-no" name="ssb_account_availability" class="custom-control-input ssb-account-availability" value="1">
          <label class="custom-control-label" for="ssb-no" data-toggle="modal" data-target="#saving-account-modal-form">No</label>
        </div>
      </div>
    </div>
  </div>

  <label class="col-form-label col-lg-2 samraddh-jeevan">Enter SSB Account Number<sup>*</sup></label>
  <div class="col-lg-4 samraddh-jeevan">
    <input type="text" nominee-form-class="samraddh-jeevan-nominee-form" name="ssbacount" id="ssbacount" class="form-control">
    <label id="ssbaccount-error" class="error"></label>
  </div>
</div>

<div class="samraddh-jeevan-nominee-form" style="display: none;">
<div class="form-group row">
  <label class="col-form-label col-lg-2">Deposit Amount</label>
  <div class="col-lg-4">
    <div class="rupee-img">
    </div>
    <input type="text" name="amount" id="amount" class="form-control rupee-txt sj-amount">
    <label id="balance-error" class="error"></label>
  </div>

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
  <div class="form-group row">
    <label class="col-form-label col-lg-2"> Tenure (Months) </label>
    <div class="col-lg-4">
      <input type="text" name="tenure" class="form-control rupee-txt sj-tenure" readonly>
    </div>
  </div>
  <input type="hidden" name="interest-rate" class="form-control rupee-txt sj-interest-rate" readonly>

  <input type="hidden" name="maturity-amount" class="form-control rupee-txt sj-maturity-amount-val" readonly>
  <h4 class="sj-maturity-amount"></h4>
@include('templates.admin.investment_management.partials.payment-mode')
@include('templates.admin.investment_management.partials.nominees')
</div>