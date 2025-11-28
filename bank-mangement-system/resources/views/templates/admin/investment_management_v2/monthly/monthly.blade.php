<div class="form-group row">
  <label class="col-form-label col-lg-2">Deposit Amount</label>
  <div class="col-lg-4">
    <div class="rupee-img">
    </div>
    <input type="text" name="amount" id="amount" class="form-control rupee-txt rd-amount" autocomplete="off">
    <label id="balance-error" class="error"></label>
  </div>

  <label class="col-form-label col-lg-2">Payment Mode<sup>*</sup></label>
  <div class="col-lg-4">
    <select name="payment-mode" id="payment-mode" class="form-control" title="Please select something!">
      <option value="">Select Mode</option>
      <option data-val="cash" value="0">Cash</option>
      <option data-val="cheque-mode" value="1">Cheque</option>
      <!-- <option data-val="online-transaction-mode" value="2">Online transaction</option> -->
      @if ($savingAccount)
        <option data-val="ssb-account" value="3">SSB account</option>
      @endif    
    </select>
  </div>
</div>
</div>  

<div class="form-group row">
@if($plans_tenures)
  <label class="col-form-label col-lg-2">Tenure<sup>*</sup></label>
  <div class="col-lg-4">
    <select name="tenure" id="tenure" class="form-control rd-tenure" title="Please select something!">
      <option value="">Select Tenure</option>
	  @foreach($plans_tenures as $key => $value)
      <option value="{{$value->tenure}}" data-roi="{{$value->roi}}"  data-spe-roi="{{$value->spl_roi}}" >{{$value->tenure}}  Months</option>
	  @endforeach
    </select>
  </div>
  @endif
  <!--
  <label class="col-form-label col-lg-2">Tenure<sup>*</sup></label>
  <div class="col-lg-4">
    <select name="tenure" id="tenure" class="form-control rd-tenure" title="Please select something!">
      <option value="">Select Tenure</option>
      <option value="36">36  Months</option>
      <option value="60">60  Months</option>
      <option value="84">84  Months</option>
    </select>
  </div>
  -->
  <input type="hidden" class="rd-maturity-amount-val" name="maturity-amount" >
  <input type="hidden" class="rd-interest-rate" name="interest-rate" >
</div>
<h4 class="rd-maturity-amount"></h4>
</div>
@include('templates.admin.investment_management_v2.ssb-avaibility')
@include('templates.admin.investment_management.partials.payment-mode')
@include('templates.admin.investment_management.partials.nominees')