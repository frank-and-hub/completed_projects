<h4 class=" ssbRequired ssb-detail" style="display:none;">SSB Details</h4>

<div class="form-group row  ssbRequired" style="display:none;" >

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
    <input type="text" nominee-form-class="monthly-income-scheme-nominee-form" name="ssbacount" id="ssbacount" class="form-control" readonly>
    <label id="ssbaccount-error" class="error"></label>
  </div>
</div>
<div class="form-group row ssb-show" style="display:none;">
  <label class="col-form-label col-lg-2">Amount</label>
  <div class="col-lg-4">
  	<div class="rupee-img">
    </div>
    <input type="text" name="ssb_amount" id="amount" class="form-control rupee-txt" value="0" readonly="">
  </div>
  <label class="col-form-label col-lg-2">Form Number<sup>*</sup></label>
    <div class="col-lg-4">
    <input type="text" name="ssb_form_number" id="ssb_form_number" class="form-control">
    </div>
</div>  

<!-- <div class="row">
	<label class="col-form-label col-lg-2">Primary account</label>
	<div class="col-lg-4">
		<div class="custom-control custom-checkbox mb-3 col-form-label">
			<input type="checkbox" id="primary_account" name="primary_account" class="custom-control-input" value="1">
			<input type="hidden" id="hidden_primary_account" name="hidden_primary_account" class="custom-control-input" value="0">
			<label class="custom-control-label" for="primary_account">Yes</label>
		</div>
	</div>
</div> -->

