<h3>Applicant Details </h3>
<div class="form-group row group-information" style="display: none;">
  <label class="col-form-label col-lg-2">Member ID<sup>*</sup></label>
  <div class="col-lg-10">
    <input type="text" data-val="group-loan" name="group_auto_member_id" id="group_auto_member_id" class="form-control">
    <input type="hidden" name="group_member_id" id="group-loan_member_id">
  </div>
</div>

<div class="form-group row applicant-box">
  <label class="col-form-label col-lg-2">Applicant  Id<sup>*</sup></label>
  <div class="col-lg-10">
    <input type="text" data-val="applicant" name="applicant_id" id="applicant_id" class="form-control" autocomplete="on">
    <input type="hidden" name="applicant_member_id" id="applicant_member_id">
  </div>
</div>

<div class="group-loan-member-detail">
</div>

<div class="form-group row">
  <label class="col-form-label col-lg-2">Address permanent<sup>*</sup></label>
  <div class="col-lg-4">
    <select name="applicant_address_permanent" id="applicant_address_permanent" class="form-control" title="Please select something!">
      <option value="">Select ID</option>
      <option value="0">Self</option>
      <option value="1">Perental</option>
      <option value="2">Rental</option>
    </select>
  </div>

  <label class="col-form-label col-lg-2">Temporary Address<sup>*</sup></label>
  <div class="col-lg-4">
    <select name="applicant_address_temporary" id="applicant_address_temporary" class="form-control" title="Please select something!">
      <option value="">Select ID</option>
      <option value="0">Self</option>
      <option value="1">Perental</option>
      <option value="2">Rental</option>
    </select>
  </div>
</div>

<h5>Employment  Details</h5>
<div class="form-group row cheque-mode p-mode">
  <label class="col-form-label col-lg-2">Occupation</label>
  <div class="col-lg-4">
    <input type="text" name="applicant_occupation_name" id="applicant_occupation_name" class="form-control group-loan-occupation-name" readonly="">
    <input type="hidden" name="applicant_occupation" id="applicant_occupation" class="form-control group-loan-occupation">
  </div>
  <label class="col-form-label col-lg-2">Organization</label>
  <div class="col-lg-4">
    <input type="text" name="applicant_organization" id="applicant_organization" class="form-control">
  </div>
</div>

<div class="form-group row cheque-mode p-mode">
  <label class="col-form-label col-lg-2">Designation</label>
  <div class="col-lg-4">
    <input type="text" name="applicant_designation" id="applicant_designation" class="form-control">
  </div>
  <label class="col-form-label col-lg-2">Monthly Income<sup>*</sup></label>
  <div class="col-lg-4">
    <input type="text" name="applicant_monthly_income" id="applicant_monthly_income" class="form-control">
  </div>
</div>

<div class="form-group row cheque-mode p-mode">
  <label class="col-form-label col-lg-2">Year from<sup>*</sup></label>
  <div class="col-lg-4">
    <input type="text" name="applicant_year_from" id="applicant_year_from" class="form-control">
  </div>
</div>

<h5>Bank details/ Security Cheque Bank details</h5>
<div class="form-group row cheque-mode p-mode">
  <label class="col-form-label col-lg-2">Bank Name <sup>*</sup></label>
  <div class="col-lg-4">
    <input type="text" name="applicant_bank_name" id="applicant_bank_name" class="form-control">
  </div>
  <label class="col-form-label col-lg-2">Bank Account Number <sup>*</sup></label>
  <div class="col-lg-4">
    <input type="text" name="applicant_bank_account_number" id="applicant_bank_account_number" class="form-control">
  </div>
</div>

<div class="form-group row cheque-mode p-mode">
  <label class="col-form-label col-lg-2">IFSC Code <sup>*</sup></label>
  <div class="col-lg-4">
    <input type="text" name="applicant_ifsc_code" id="applicant_ifsc_code" class="form-control">
  </div>
  <label class="col-form-label col-lg-2 cheque-box">Cheque Number 1<sup>*</sup></label>
  <div class="col-lg-4 cheque-box">
    <input type="text" name="applicant_cheque_number_1" id="applicant_cheque_number_1" class="form-control">
  </div>
</div>

<div class="form-group row cheque-mode p-mode cheque-box">
  <label class="col-form-label col-lg-2">Cheque Number 2<sup>*</sup></label>
  <div class="col-lg-4">
    <input type="text" name="applicant_cheque_number_2" id="applicant_cheque_number_2" class="form-control">
  </div>
</div>

<h5>Documents</h5>
<div class="form-group row">
  <label class="col-form-label col-lg-2">ID  Proof<sup>*</sup></label>
  <div class="col-lg-12">
    <select name="applicant_id_proof" id="applicant_id_proof" class="form-control" title="Please select something!">
      <option data-val="applicant" value="">Select ID</option>
      <option data-val="applicant" data-proof-val="" value="1">Voter ID</option>
      <option data-val="applicant" data-proof-val="" value="2">DL</option>
      <option data-val="applicant" data-proof-val="" value="3">Aadhar card</option>
      <option data-val="applicant" data-proof-val="" value="4">Passport</option>
      <option data-val="applicant" data-proof-val="" value="5">Pan Card</option>
      <option data-val="applicant" data-proof-val="" value="6">Other</option>
      <option data-val="applicant" data-proof-val="" value="7">Electricity Bill</option>
    </select>
  </div>
</div>

<div class="form-group row">
  <label class="col-form-label col-lg-2">Enter Id number<sup>*</sup></label>
  <!-- <div class="col-lg-5  error-msg">
    <div class="row">         -->          
    <div class="col-lg-4 ">
      <input type="text" name="applicant_id_number" id="applicant_id_number" class="form-control">
    </div>
    <div class="col-lg-1 ">
      <button type="button" id="applicant_id_tooltip"  class="btn btn-sm btn-default" data-toggle="tooltip" data-placement="top" title="Enter Id proof number."><i class="fas fa-exclamation-circle text-secondary"></i>
      </button>
    </div>
    <!-- </div>
  </div> -->

  <label class="col-form-label col-lg-2">Upload File</label>
  <div class="col-lg-3">
    <input type="file" name="applicant_id_file" id="applicant_id_file" class="form-control">
  </div>
</div>

<div class="form-group row">
  <label class="col-form-label col-lg-2">Address Proof<sup>*</sup></label>
  <div class="col-lg-12">
    <select name="applicant_address_id_proof" id="applicant_address_id_proof" class="form-control" title="Please select something!">
      <option data-val="applicant_address" data-proof-val="" value="1">Voter ID</option>
      <option data-val="applicant_address" data-proof-val="" value="2">DL</option>
      <option data-val="applicant_address" data-proof-val="" value="3">Aadhar card</option>
      <option data-val="applicant_address" data-proof-val="" value="4">Passport</option>
      <option data-val="applicant_address" data-proof-val="" value="5">Pan Card</option>
      <option data-val="applicant_address" data-proof-val="" value="6">Other</option>
      <option data-val="applicant_address" data-proof-val="" value="7">Electricity Bill</option>
    </select>
  </div>
</div>

<div class="form-group row">
  <label class="col-form-label col-lg-2">Enter Id number<sup>*</sup></label>
  <div class="col-lg-4 ">
    <input type="text" name="applicant_address_id_number" id="applicant_address_id_number" class="form-control">
  </div>
  <div class="col-lg-1 ">
    <button type="button" id="applicant_address_id_tooltip"  class="btn btn-sm btn-default" data-toggle="tooltip" data-placement="top" title="Enter Id proof number."><i class="fas fa-exclamation-circle text-secondary"></i>
    </button>
  </div>

  <label class="col-form-label col-lg-2">Upload File</label>
  <div class="col-lg-3">
    <input type="file" name="applicant_address_id_file" id="applicant_address_id_file" class="form-control">
  </div>
</div>

<div class="form-group row">
  <label class="col-form-label col-lg-2">Income<sup>*</sup></label>
  <div class="col-lg-4">
    <select name="applicant_income" id="applicant_income" class="form-control" title="Please select something!">
      <option data-val="applicant" value="">Select Type</option>
      <option data-val="applicant" value="0">Salary Slip</option>
      <option data-val="applicant" value="1">ITR</option>
      <option data-val="applicant" value="2">Others</option>
    </select>
  </div>

  <label class="col-form-label col-lg-2 applicant-salary-remark" style="display: none;">Remark<sup>*</sup></label>
  <div class="col-lg-4 applicant-salary-remark" style="display: none;">
    <input type="text" name="applicant_remark" id="applicant_remark" class="form-control">
  </div>
</div>

<div class="form-group row">
  <label class="col-form-label col-lg-2">Security<sup>*</sup></label>
  <div class="col-lg-4">
    <select name="applicant_security" id="applicant_security" class="form-control" title="Please select something!">
      <option value="">Select Type</option>
      <option value="0">Cheuqe</option>
      <option value="1">Passbook</option>
      <option value="2">FD certificate</option>
    </select>
  </div>

  <label class="col-form-label col-lg-2">Upload File</label>
  <div class="col-lg-4">
    <input type="file" name="applicant_income_file" id="applicant_income_file" class="form-control">
  </div>
</div>