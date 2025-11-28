<div class="form-group row">
  <label class="col-form-label col-lg-2">Deposit Amount per month</label>
  <div class="col-lg-4">
    <div class="rupee-img">
    </div>
    <input type="text" name="amount" id="amount" class="form-control rupee-txt frd-amount">
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
@if($plans_tenure)
<div class="form-group row">
  <label class="col-form-label col-lg-2">Tenure<sup>*</sup></label>
  <div class="col-lg-4">
    <select name="tenure" id="tenure" class="form-control frd-tenure" title="Please select something!" readonly>
      <option value="">Select Tenure</option>
	  @foreach($plans_tenure->PlanTenures as $key => $value)
	  <option value="{{$value->tenure}}" data-roi="{{$value->roi}}" >{{$value->tenure}}  Months</option>
	  @endforeach
    </select>
  </div>
@endif
<!--
<div class="form-group row">
  <label class="col-form-label col-lg-2">Tenure<sup>*</sup></label>
  <div class="col-lg-4">
    <select name="tenure" id="tenure" class="form-control frd-tenure" title="Please select something!" readonly>
      <option value="">Select Tenure</option>
      <option value="12">12  Months</option>
      <option value="24">24  Months</option>
      <option value="36">36  Months</option>
      <option value="48">48  Months</option>
      <option value="60" selected="">60  Months</option>
    </select>
  </div>
-->
  <label class="col-form-label col-lg-2">Interest Rate (%)<sup></sup></label>
  <div class="col-lg-4">
    <input type="text" name="interest-rate" class="form-control rupee-txt frd-interest-rate" readonly>
  </div>

</div>
<input type="hidden" name="maturity-amount" class="form-control rupee-txt frd-maturity-amount-cal" readonly>
<h4 class="frd-maturity-amount"></h4>


@include('templates.admin.investment_management.partials.payment-mode')
@include('templates.admin.investment_management.partials.nominees')