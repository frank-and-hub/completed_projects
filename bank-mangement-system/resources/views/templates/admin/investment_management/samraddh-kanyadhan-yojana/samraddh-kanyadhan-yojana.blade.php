

<div class="form-group row">
  <label class="col-form-label col-lg-2">Relation with guardians</label>
  <div class="col-lg-4">
    <input type="text" name="guardian-ralationship" id="guardian-ralationship" class="form-control">
  </div>
  <label class="col-form-label col-lg-2">Daughter name</label>
  <div class="col-lg-4">
    <input type="text" name="daughter-name" id="daughter-name" class="form-control">
  </div>
</div> 

<div class="form-group row">
  <label class="col-form-label col-lg-2">Date of Birth </label>
  <div class="col-lg-4">
    <div class="input-group">
      <span class="input-group-prepend">
        <span class="input-group-text"><i class="ni ni-calendar-grid-58"></i></span>
      </span>
       <input type="text" name="dob" id="dob" class="form-control rupee-txt kanyadhan-dob" data-val="age">
    </div>
  </div>
  <label class="col-form-label col-lg-2">Age</label>
  <div class="col-lg-4">
    <input type="text" name="age" id="age" class="form-control" readonly="">
  </div>
</div>

<div class="form-group row">
  <label class="col-form-label col-lg-2">Monthly Deposit Amount<sup>*</sup></label>
  <div class="col-lg-4">
    <div class="rupee-img">
    </div>
    <input type="text" name="amount" id="amount" class="form-control rupee-txt monthly-deposite-amount" readonly="">
    <input type="hidden" class="sky-maturity-amount" name="maturity-amount" value="">
    <input type="hidden" class="sky-interest-rate" name="interest-rate" value="">
    <label id="balance-error" class="error"></label>
    <h4 class="maturity-amount"></h4>
  </div>
  <label class="col-form-label col-lg-2">Tenure(years)<sup>*</sup></label>
  <div class="col-lg-4">
    <input type="text" name="tenure" id="tenure" class="form-control kanyadhan-yojna-tenure" readonly="">
    <label id="tenure-error" class="error"></label>
  </div>
</div>

<div class="form-group row">
  <label class="col-form-label col-lg-2">Phone Number</label>
  <div class="col-lg-4">
    <input type="text" name="phone-number" id="phone-number" class="form-control" title="Please enter minimum 10 or maximum 12 digit.">
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

@include('templates.admin.investment_management.partials.payment-mode')