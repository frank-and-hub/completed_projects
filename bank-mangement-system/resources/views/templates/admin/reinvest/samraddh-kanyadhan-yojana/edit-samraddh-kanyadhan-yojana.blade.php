

<div class="form-group row">
  <label class="col-form-label col-lg-2">Relation with guardians</label>
  <div class="col-lg-4">
    <input type="text" name="guardian-ralationship" id="guardian-ralationship" class="form-control" value="{{ $investments->guardians_relation }}" @if($action != 'edit') readonly="" @endif>
  </div>
  <label class="col-form-label col-lg-2">Daughter name</label>
  <div class="col-lg-4">
    <input type="text" name="daughter-name" id="daughter-name" class="form-control" value="{{ $investments->daughter_name }}" @if($action != 'edit') readonly="" @endif>
  </div>
</div> 

<div class="form-group row">
  <label class="col-form-label col-lg-2">Date of Birth </label>
  <div class="col-lg-4">
    <div class="input-group">
      <span class="input-group-prepend">
        <span class="input-group-text"><i class="ni ni-calendar-grid-58"></i></span>
      </span>
       <input type="text" name="dob" id="dob" class="form-control kanyadhan-dob" data-val="age" value="{{ $investments->dob }}" @if($action != 'edit') readonly="" @endif>
    </div>
  </div>
  <label class="col-form-label col-lg-2">Age</label>
  <div class="col-lg-4">
    <input type="text" name="age" id="age" class="form-control" readonly="" value="{{ $investments->age }}"  @if($action != 'edit') readonly="" @endif>
  </div>
</div>

<div class="form-group row">
  <label class="col-form-label col-lg-2">Monthly Deposit Amount<sup>*</sup></label>
  <div class="col-lg-4">
    <div class="rupee-img">
    </div>
    <input type="text" name="amount" id="amount" class="form-control rupee-txt monthly-deposite-amount"  value="{{ $investments->deposite_amount }}" @if($action != 'edit') readonly="" @endif>
    <input type="hidden" class="sky-maturity-amount" name="maturity-amount" value="{{ $investments->maturity_amount }}">
    <input type="hidden" class="sky-interest-rate" name="interest-rate" value="" value="{{ $investments->interest_rate }}">
    <label id="balance-error" class="error"></label>
    <h4 class="maturity-amount">Maturity Amount :{{ $investments->maturity_amount }}</h4>
  </div>
  <label class="col-form-label col-lg-2">Tenure(years)<sup>*</sup></label>
  <div class="col-lg-4">
    <input type="text" name="tenure" id="tenure" class="form-control kanyadhan-yojna-tenure" value="{{ $investments->tenure }}" @if($action != 'edit') readonly="" @endif>
  </div>
</div>

<div class="form-group row">
  <label class="col-form-label col-lg-2">Phone Number</label>
  <div class="col-lg-4">
    <input type="text" name="phone-number" id="phone-number" class="form-control" value="{{ $investments->phone_number }}" @if($action != 'edit') readonly="" @endif>
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
@include('templates.admin.reinvest.partials.edit-payment-mode')
@include('templates.admin.reinvest.partials.edit-payment-mode')