<div class="form-group row">
  <label class="col-form-label col-lg-2">Deposit Amount per month</label>
  <div class="col-lg-4">
    <div class="rupee-img">
    </div>
    <input type="text" name="amount" id="amount" class="form-control rupee-txt frd-amount" value="{{ $investments->deposite_amount }}"  @if($action != 'edit') readonly="" @endif>
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

<div class="form-group row">
@if($plans_tenure)
  <label class="col-form-label col-lg-2">Tenure<sup>*</sup></label>
  <div class="col-lg-4">
    <select name="tenure" id="tenure" class="form-control frd-tenure" title="Please select something!" @if($action != 'edit') disabled="" @endif>
      <option value="">Select Tenure</option>
	  @foreach($plans_tenure->PlanTenures as $key => $value)
	  <option value="{{$value->tenure}}" data-roi="{{$value->roi}}"  {{ ($investments->tenure == $key ) ? 'selected': '' }} >{{$value->tenure}}  Months</option>
	  @endforeach
    </select>
  </div>
@endif
<!--
  <label class="col-form-label col-lg-2">Tenure<sup>*</sup></label>
  <div class="col-lg-4">
    <select name="tenure" id="tenure" class="form-control frd-tenure" title="Please select something!" @if($action != 'edit') disabled="" @endif>
      <option value="">Select Tenure</option>
      <option @if($investments->tenure == 1) selected @endif value="12">12  Months</option>
      <option @if($investments->tenure == 2) selected @endif value="24">24  Months</option>
      <option @if($investments->tenure == 3) selected @endif value="36">36  Months</option>
      <option @if($investments->tenure == 4) selected @endif value="48">48  Months</option>
      <option @if($investments->tenure == 5) selected @endif value="60">60  Months</option>
    </select>
  </div>
-->
  <label class="col-form-label col-lg-2">Interest Rate (%)<sup></sup></label>
  <div class="col-lg-4">
    <input type="text" name="interest-rate" class="form-control rupee-txt frd-interest-rate" value="{{ $investments->interest_rate }}" readonly>
  </div>
</div> 

<input type="hidden" name="maturity-amount" class="form-control rupee-txt frd-maturity-amount-cal" value="{{ $investments->maturity_amount }}" readonly>
<h4 class="frd-maturity-amount">Maturity Amount :{{ $investments->maturity_amount }}</h4>
@include('templates.admin.investment_management.partials.edit-payment-mode')
@include('templates.admin.investment_management.partials.edit-nominees')