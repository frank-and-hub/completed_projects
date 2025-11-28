<div class="form-group row">
  <label class="col-form-label col-lg-2">SSB Account Available or Not?<sup>*</sup></label>
  <div class="col-lg-4 error-msg">
    <div class="row">
      <div class="col-lg-4">
        <div class="custom-control custom-radio mb-3 ">
          <input type="radio" nominee-form-class="monthly-income-scheme-nominee-form" data-val="monthly-income-scheme" id="ssb-yes" name="ssb_account_availability" class="custom-control-input ssb-account-availability" value="0" checked="">
          <label class="custom-control-label" for="ssb-yes">Yes</label>
        </div>
      </div>
      <div class="col-lg-4">
        <div class="custom-control custom-radio mb-3  ">
          <input type="radio" nominee-form-class="monthly-income-scheme-nominee-form" data-val="monthly-income-scheme" id="ssb-no" name="ssb_account_availability" class="custom-control-input ssb-account-availability" value="1">
          <label class="custom-control-label" for="ssb-no" data-toggle="modal" data-target="#saving-account-modal-form">No</label>
        </div>
      </div>
    </div>
  </div>

  <label class="col-form-label col-lg-2 monthly-income-scheme">Enter SSB Account Number<sup>*</sup></label>
  <div class="col-lg-4 monthly-income-scheme">
    <input type="text" nominee-form-class="monthly-income-scheme-nominee-form" name="ssbacount" id="ssbacount" class="form-control">
    <label id="ssbaccount-error" class="error"></label>
  </div>
</div>


<div class="monthly-income-scheme-nominee-form" style="display: none;">
  <div class="form-group row">
    <label class="col-form-label col-lg-2">Deposit Amount</label>
    <div class="col-lg-4">
      <div class="rupee-img">
      </div>
      <input type="text" name="amount" id="amount" class="form-control rupee-txt mis-amount">
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
@if($plans_tenure)
    <label class="col-form-label col-lg-2">Duration<sup>*</sup></label>
    <div class="col-lg-4">
      <select name="tenure" id="tenure" class="form-control mis-tenure" title="Please select something!">
        <option value="">Select Tenure</option>
	    @foreach($plans_tenure->PlanTenures as $key=>$value)
        <option value="{{$value->tenure}}" data-roi="{{$value->roi}}" >{{$value->tenure}}  Months</option>
		@endforeach
      </select>
    </div>
@endif
<!--
    <label class="col-form-label col-lg-2">Duration<sup>*</sup></label>
    <div class="col-lg-4">
      <select name="tenure" id="tenure" class="form-control mis-tenure" title="Please select something!">
        <option value="">Select Tenure</option>
        <option value="60">60  Months</option>
        <option value="84">84  Months</option>
        <option value="120">120  Months</option>
      </select>
    </div>
-->
    <label class="col-form-label col-lg-2">Interest Rate (%)<sup></sup></label>
    <div class="col-lg-4">
      <input type="text" name="interest_rate" class="form-control rupee-txt mis-maturity-amount-cal" readonly>
      <input type="hidden" name="maturity-amount" class="form-control rupee-txt mis-maturity-amount-val" readonly>
    </div>
  </div>
  <h4 class="mis-maturity-amount"></h4>

  @include('templates.admin.investment_management.partials.payment-mode')
  @include('templates.admin.investment_management.partials.nominees')
</div>