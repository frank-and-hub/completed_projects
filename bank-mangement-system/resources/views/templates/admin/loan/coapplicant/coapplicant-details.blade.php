<h3>Co-applicant Details </h5>
<div class="custom-control custom-checkbox mb-3 col-form-label">
<input type="checkbox" id="co_applicant_checkbox" name="co_applicant_checkbox" class="custom-control-input">
<label class="custom-control-label" for="co_applicant_checkbox">Yes</label>
<input type="hidden" id="co_applicant_checkbox_val" name="co_applicant_checkbox_val">
</div>

<div class="form-group row co-applicant-form" style="display: none;">
  <label class="col-form-label col-lg-2">Member ID<sup>*</sup></label>
  <div class="col-lg-10">
    <input type="text" data-val="co-applicant" name="co-applicant_auto_member_id" id="co-applicant_auto_member_id" class="form-control">
    <input type="hidden" data-val="co-applicant" name="co-applicant_member_id" id="co-applicant_member_id" class="form-control">
  </div>
</div>

<div class="co-applicant-member-detail co-applicant-form" id="show_mwmber_detail" style="display: none;">
</div>

<div class="form-group row co-applicant-form" style="display: none;">
  <label class="col-form-label col-lg-2">Address permanent<sup>*</sup></label>
  <div class="col-lg-4">
    <select name="co-applicant_address_permanent" id="co-applicant_address_permanent" class="form-control" title="Please select something!">
      <option value="">Select ID</option>
      <option value="0">Self</option>
      <option value="1">Perental</option>
      <option value="2">Rental</option>
    </select>
  </div>

  <label class="col-form-label col-lg-2">Temporary Address<sup>*</sup></label>
  <div class="col-lg-4">
    <select name="co-applicant_address_temporary" id="co-applicant_address_temporary" class="form-control" title="Please select something!">
      <option value="">Select ID</option>
      <option value="0">Self</option>
      <option value="1">Perental</option>
      <option value="2">Rental</option>
    </select>
  </div>
</div>

<h5 class="co-applicant-form" style="display: none;">Employment  Details</h5>
<div class="form-group row cheque-mode p-mode co-applicant-form" style="display: none;">
  <label class="col-form-label col-lg-2">Occupation</label>
  <div class="col-lg-4">
    <input type="text" name="co-applicant_occupation_name" id="co-applicant_occupation_name" class="form-control" readonly="">
    <input type="hidden" name="co-applicant_occupation" id="co-applicant_occupation" class="form-control">
  </div>
  <label class="col-form-label col-lg-2">Organization</label>
  <div class="col-lg-4">
    <input type="text" name="co-applicant_organization" id="co-applicant_organization" class="form-control">
  </div>
</div>

<div class="form-group row cheque-mode p-mode co-applicant-form" style="display: none;">
  <label class="col-form-label col-lg-2">Designation</label>
  <div class="col-lg-4">
    <input type="text" name="co-applicant_designation" id="co-applicant_designation" class="form-control">
  </div>
  <label class="col-form-label col-lg-2">Monthly Income<sup>*</sup></label>
  <div class="col-lg-4">
    <input type="text" name="co-applicant_monthly_income" id="co-applicant_monthly_income" class="form-control">
  </div>
</div>

<div class="form-group row cheque-mode p-mode co-applicant-form" style="display: none;">
  <label class="col-form-label col-lg-2">Year from<sup>*</sup></label>
  <div class="col-lg-4">
    <input type="text" name="co-applicant_year_from" id="co-applicant_year_from" class="form-control">
  </div>
</div>

<h5 class="co-applicant-form" style="display: none;">Bank details/ Security Cheque Bank details</h5>
<div class="form-group row cheque-mode p-mode co-applicant-form" style="display: none;">
  <label class="col-form-label col-lg-2">Bank Name </label>
  <div class="col-lg-4">
    <input type="text" name="co-applicant_bank_name" id="co-applicant_bank_name" class="form-control">
  </div>
  <label class="col-form-label col-lg-2">Bank Account Number <sup>*</sup></label>
  <div class="col-lg-4">
    <input type="text" name="co-applicant_bank_account_number" id="co-applicant_bank_account_number" class="form-control">
  </div>
</div>

<div class="form-group row cheque-mode p-mode co-applicant-form" style="display: none;">
  <label class="col-form-label col-lg-2">IFSC Code </label>
  <div class="col-lg-4">
    <input type="text" name="co-applicant_ifsc_code" id="co-applicant_ifsc_code" class="form-control">
  </div>
  <label class="col-form-label col-lg-2">Cheque Number 1</label>
  <div class="col-lg-4">
    <input type="text" name="co-applicant_cheque_number_1" id="co-applicant_cheque_number-1" class="form-control">
  </div>
</div>

<div class="form-group row cheque-mode p-mode co-applicant-form" style="display: none;">
  <label class="col-form-label col-lg-2">Cheque Number 2</label>
  <div class="col-lg-4">
    <input type="text" name="co-applicant_cheque_number_2" id="co-applicant_cheque_number_2" class="form-control">
  </div>
</div>

<h5 class="co-applicant-form" style="display: none;">Documents</h5>
<div class="form-group row co-applicant-form" style="display: none;">
  <label class="col-form-label col-lg-2">ID  Proof<sup>*</sup></label>
  <div class="col-lg-12">
    <select name="co-applicant_id_proof" id="co-applicant_id_proof" class="form-control" title="Please select something!">
      <option data-val="co-applicant" value="">Select ID</option>
      <option data-val="co-applicant" value="1">Voter ID</option>
      <option data-val="co-applicant" value="2">DL</option>
      <option data-val="co-applicant" value="3">Aadhar card</option>
      <option data-val="co-applicant" value="4">Passport</option>
      <option data-val="co-applicant" value="5">Pan Card</option>
      <option data-val="co-applicant" value="6">Other</option>
      <option data-val="co-applicant" value="7">Electricity Bill</option>
    </select>
  </div>
</div>

<div class="form-group row co-applicant-form" style="display: none;">
  <label class="col-form-label col-lg-2">Enter Id number<sup>*</sup></label>
  <div class="col-lg-4 ">
    <input type="text" name="co-applicant_id_number" id="co-applicant_id_number" class="form-control">
  </div>
  <div class="col-lg-1 ">
    <button type="button" id="co-applicant_id_tooltip"  class="btn btn-sm btn-default" data-toggle="tooltip" data-placement="top" title="Enter Id proof number."><i class="fas fa-exclamation-circle text-secondary"></i>
    </button>
  </div>

  <label class="col-form-label col-lg-2">Security cheque/Stamp</label>
  <div class="col-lg-3">
    <input type="file" name="co-applicant_id_file" id="co-applicant_id_file" class="form-control">
  </div>
</div>

<div class="form-group row co-applicant-form" style="display: none;">
  <label class="col-form-label col-lg-2">Address Proof<sup>*</sup></label>
  <div class="col-lg-4">
    <select name="co-applicant_address_id_proof" id="co-applicant_address_id_proof" class="form-control" title="Please select something!">
      <option data-val="co-applicant_address" value="">Select ID</option>
      <option data-val="co-applicant_address" value="1">Voter ID</option>
      <option data-val="co-applicant_address" value="2">DL</option>
      <option data-val="co-applicant_address" value="3">Aadhar card</option>
      <option data-val="co-applicant_address" value="4">Passport</option>
      <option data-val="co-applicant_address" value="5">Pan Card</option>
      <option data-val="co-applicant_address" value="6">Other</option>
      <option data-val="co-applicant_address" value="7">Electricity Bill</option>
    </select>
  </div>

  <label class="col-form-label col-lg-2">Enter Id number<sup>*</sup></label>
  <div class="col-lg-3">
    <input type="text" name="co-applicant_address_id_number" id="co-applicant_address_id_number" class="form-control">
  </div>
  <div class="col-lg-1">
    <button type="button" id="co-applicant_address_id_tooltip"  class="btn btn-sm btn-default" data-toggle="tooltip" data-placement="top" title="Enter Id proof number."><i class="fas fa-exclamation-circle text-secondary"></i>
    </button>
  </div>
</div>

<div class="form-group row co-applicant-form" style="display: none;">
  <label class="col-form-label col-lg-2">Upload File</label>
  <div class="col-lg-4">
    <input type="file" name="co-applicant_address_id_file" id="co-applicant_address_id_file" class="form-control">
  </div>

  <label class="col-form-label col-lg-2">Under taking Doc</label>
  <div class="col-lg-4">
    <input type="file" name="co-applicant_under_taking_doc" id="co-applicant_under_taking_doc" class="form-control">
  </div>
</div>

<div class="form-group row co-applicant-form" style="display: none;">
  <label class="col-form-label col-lg-2">Income<sup>*</sup></label>
  <div class="col-lg-4">
    <select name="co-applicant_income" id="co-applicant_income" class="form-control" title="Please select something!">
      <option data-val="co-applicant" value="">Select Type</option>
      <option data-val="co-applicant" value="0">Salary Slip</option>
      <option data-val="co-applicant" value="1">ITR</option>
      <option data-val="co-applicant" value="2">Others</option>
    </select>
  </div>

  <label class="col-form-label col-lg-2 co-applicant-salary-remark" style="display: none;">Remark<sup>*</sup></label>
  <div class="col-lg-4 co-applicant-salary-remark" style="display: none;">
    <input type="text" name="co_applicant_remark" id="co_applicant_remark" class="form-control">
  </div>

</div>

<div class="form-group row co-applicant-form" style="display: none;">
  <label class="col-form-label col-lg-2">Security<sup>*</sup></label>
  <div class="col-lg-4">
    <select name="co-applicant_security" id="co-applicant_security" class="form-control" title="Please select something!">
      <option value="">Select Type</option>
      <option value="0">Cheuqe</option>
      <option value="1">Passbook</option>
      <option value="2">FD certificate</option>
    </select>
  </div>

  <label class="col-form-label col-lg-2">Upload File</label>
  <div class="col-lg-4">
    <input type="file" name="co-applicant_income_file" id="co-applicant_income_file" class="form-control">
  </div>
</div>