<div class="form-group row">
  <label class="col-form-label col-lg-2">Daily Deposit Amount</label>
  <div class="col-lg-4">
    <div class="rupee-img">
    </div>
    <input type="text" name="amount" id="amount" class="form-control rupee-txt dd-amount">
    <label id="balance-error" class="error"></label>
  </div>

  <label class="col-form-label col-lg-2">Payment Mode<sup>*</sup></label>
  <div class="col-lg-4">
    <select name="payment-mode" id="payment-mode" class="form-control" title="Please select something!">
      <option value="">Select Mode</option>
      <option data-val="cash" value="0">Cash</option>
      <option data-val="cheque-mode" value="1">Cheque</option>
      <option data-val="online-transaction-mode" value="2">Online transaction</option>
      @if ($savingAccount)
                <option data-val="ssb-account" value="3">SSB account</option>
            @endif    </select>
  </div>
</div>
@if($plans_tenure)
<div class="form-group row">
  <label class="col-form-label col-lg-2">Duration<sup>*</sup></label>
  <div class="col-lg-4">
    <select name="tenure" id="tenure" class="form-control dd-tenure" title="Please select something!">
      <option value="">Select Tenure</option>
	  @foreach($plans_tenure->PlanTenures as $key=>$value)
	 <option value="{{$value->tenure}}" data-roi="{{$value->roi}}" data-spe-roi="{{$value->spl_roi}}" >{{$value->tenure}}  Months</option>
	  @endforeach
    </select>
  </div>
</div>
@endif
<input type="hidden" name="interest-rate" class="form-control rupee-txt dd-interest-rate" readonly>
<input type="hidden" name="maturity-amount" class="form-control rupee-txt dd-maturity-amount-val" readonly>
<h4 class="dd-maturity-amount"></h4>
@include('templates.admin.investment_management_v2.ssb-avaibility')
@include('templates.admin.investment_management.partials.payment-mode')
@include('templates.admin.investment_management.partials.nominees')