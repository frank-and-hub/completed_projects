<div class="form-group row">
  <label class="col-form-label col-lg-2">Deposit Amount</label>
  <div class="col-lg-4">
    <div class="rupee-img">
    </div>
    <input type="text" name="amount" id="amount" class="form-control rupee-txt fd-amount" autocomplete="off">
    <label id="balance-error" class="error"></label>
  </div>
  @if($plans_tenures)
  <label class="col-form-label col-lg-2">Tenure<sup>*</sup></label>
  <div class="col-lg-4">
    <select name="tenure" id="tenure" class="form-control fd-tenure" title="Please select something!">
      <option value="">Select Tenure</option>
	  @foreach($plans_tenures as $key => $value)
      <option value="{{$value->tenure}}" data-roi="{{$value->roi}}"  data-spe-roi="{{$value->spl_roi}}">{{$value->tenure}}  Months</option>
	  @endforeach
    </select>
  </div>
  @endif
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
      <!-- <option data-val="ssb-account" value="3">SSB account</option> -->
    </select>
  </div>
  <input type="hidden" name="interest-rate" class="form-control rupee-txt fd-interest-rate" readonly>
  <input type="hidden" name="maturity-amount" class="form-control rupee-txt fd-maturity-amount-val" readonly>
</div>
<h4 class="fd-maturity-amount"></h4>
</div>
@include('templates.branch.investment_management.ssb-avaibility')

@include('templates.branch.investment_management.partials.payment-mode')
@include('templates.branch.investment_management.partials.nominees')