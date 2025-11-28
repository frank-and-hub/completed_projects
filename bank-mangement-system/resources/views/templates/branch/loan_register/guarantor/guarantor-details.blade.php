<h3>Guarantor Details</h3>
<div class="form-group row">
  <label class="col-form-label col-lg-2">Customer ID</label>
  <div class="col-lg-4">
    <input type="text" data-val="guarantor" name="guarantor_auto_member_id" id="guarantor_auto_member_id" class="form-control">
    <input type="hidden" data-val="guarantor" name="guarantor_member_id" id="guarantor_member_id" class="form-control">
  </div>

  <label class="col-form-label col-lg-2 guarantor-name-section">Name<sup>*</sup></label>
  <div class="col-lg-4 guarantor-name-section">
    <input type="text" name="guarantor_name" id="guarantor_name" class="form-control" value="{{old('guarantor_name')}}">
  </div>
</div>

<div class="guarantor-member-detail" id="show_mwmber_detail">
</div>

<div class="form-group row guarantor-member-detail-box">
  <label class="col-form-label col-lg-2">Father Name<sup>*</sup></label>
  <div class="col-lg-4">
    <input type="text" name="guarantor_father_name" id="guarantor_father_name" class="form-control" value="{{old('guarantor_father_name')}}">
  </div>

  <label class="col-form-label col-lg-2">Date of Birth<sup>*</sup></label>
  <div class="col-lg-4">
    <input type="text" readonly name="guarantor_dob" id="guarantor_dob" class="form-control date_of_birth" value="{{old('guarantor_dob')}}">
  </div>
</div>

<div class="form-group row guarantor-member-detail-box">
  <label class="col-form-label col-lg-2">Marital Status<sup>*</sup></label>
  <div class="col-lg-4">
    <select name="guarantor_marital_status" id="guarantor_marital_status" class="form-control" title="Please select something!">
      <option value="">Select Type</option>
      <option value="0">Single</option>
      <option value="1">Married</option>
    </select>
  </div>

  <label class="col-form-label col-lg-2">Local Address<sup>*</sup></label>
  <div class="col-lg-4">
    <input type="text" name="local_address" id="local_address" class="form-control" value="{{old('local_address')}}">
  </div>
</div>

<div class="form-group row">
  <label class="col-form-label col-lg-2">Ownership<sup>*</sup></label>
  <div class="col-lg-4">
    <select name="guarantor_ownership" id="guarantor_ownership" class="form-control" title="Please select something!">
      <option value="">Select Type</option>
      <option value="0">Self</option>
      <option value="1">Perental</option>
      <option value="2">Rental</option>
    </select>
  </div>

  <label class="col-form-label col-lg-2">Temporary Address<sup>*</sup></label>
  <div class="col-lg-4">
    <select name="guarantor_temporary_address" id="guarantor_temporary_address" class="form-control" title="Please select something!">
      <option value="">Select ID</option>
      <option value="0">Self</option>
      <option value="1">Perental</option>
      <option value="2">Rental</option>
    </select>
  </div>
</div>

<div class="form-group row">
  <label class="col-form-label col-lg-2 guarantor-member-detail-box">Mobile Number<sup>*</sup></label>
  <div class="col-lg-4 guarantor-member-detail-box">
    <input type="text" name="guarantor_mobile_number" id="guarantor_mobile_number" class="form-control" title="Please enter minimum 10 or maximum 12 digit." value="{{old('guarantor_mobile_number')}}">
  </div>

  <label class="col-form-label col-lg-2">Educational Qualification<sup>*</sup></label>
  <div class="col-lg-4">
    <select name="guarantor_educational_qualification" id="guarantor_educational_qualification" class="form-control" title="Please select something!">
      <option value="">Select Type</option>
      <option value="1">Higher secondary</option>
      <option value="2">Junior High school</option>
      <option value="3">Graduation</option>
      <option value="4">Post Graduation</option>
    </select>
  </div>
</div>

<div class="form-group row">
  <label class="col-form-label col-lg-2">No. of Dependents <sup>*</sup></label>
  <div class="col-lg-4">
    <input type="text" name="guarantor_dependents_number" id="guarantor_dependents_number" class="form-control" autocomplete="off" value="{{old('guarantor_dependents_number')}}">
  </div>
</div>

<h5>Employment  Details</h5>
<div class="form-group row cheque-mode p-mode">
  <label class="col-form-label col-lg-2">Occupations<sup>*</sup></label>
  <div class="col-lg-4">
    <select name="guarantor_occupation_id" id="guarantor_occupation_id" class="form-control" title="Please select something!">
      <option value="">Select Type</option>
      <option value="1">Government Employee</option>                     
      <option value="2">Private Employee</option>                     
      <option value="3">Self Employees</option>
      <option value="4">Other</option>
    </select>
    <input type="hidden" name="guarantor_occupation" id="guarantor_occupation" class="form-control">
  </div>

  <label class="col-form-label col-lg-2 occupation-other-remark" style="display: none;">Remark</label>
  <div class="col-lg-4" style="display: none;">
    <input type="text" name="ocupation_remark" id="ocupation_remark" class="form-control" value="{{old('ocupation_remark')}}">
  </div>

  <label class="col-form-label col-lg-2 occupation-fields">Organization</label>
  <div class="col-lg-4">
    <input type="text" name="guarantor_organization" id="guarantor_organization" class="form-control" value="{{old('guarantor_organization')}}">
  </div>
</div>

<div class="form-group row cheque-mode p-mode  occupation-fields">
  <label class="col-form-label col-lg-2">Designation</label>
  <div class="col-lg-4">
    <input type="text" name="guarantor_designation" id="guarantor_designation" class="form-control" readonly="">
  </div>
  <label class="col-form-label col-lg-2">Monthly Income<sup>*</sup></label>
  <div class="col-lg-4">
    <input type="text" name="guarantor_monthly_income" id="guarantor_monthly_income" class="form-control" value="{{old('guarantor_year_from')}}">
  </div>
</div>

<div class="form-group row cheque-mode p-mode occupation-fields">
  <label class="col-form-label col-lg-2">Year from<sup>*</sup></label>
  <div class="col-lg-4">
    <input type="text" name="guarantor_year_from" id="guarantor_year_from" class="form-control" value="{{old('guarantor_year_from')}}">
  </div>
</div>

<h5>Bank details/ Security Cheque Bank details</h5>
<div class="form-group row cheque-mode p-mode">
  <label class="col-form-label col-lg-2">Bank Name</label>
  <div class="col-lg-4">
    <input type="text" name="guarantor_bank_name" id="guarantor_bank_name" class="form-control" value="{{old('guarantor_bank_name')}}">
  </div>
  <label class="col-form-label col-lg-2">Bank Account Number </label>
  <div class="col-lg-4">
    <input type="text" name="guarantor_bank_account_number" id="guarantor_bank_account_number" class="form-control" value="{{old('guarantor_bank_account_number')}}">
  </div>
</div>

<div class="form-group row cheque-mode p-mode">
  <label class="col-form-label col-lg-2">IFSC Code</label>
  <div class="col-lg-4">
    <input type="text" name="guarantor_ifsc_code" id="guarantor_ifsc_code" class="form-control" value="{{old('guarantor_ifsc_code')}}">
  </div>
  <label class="col-form-label col-lg-2">Cheque Number 1</label>
  <div class="col-lg-4">
    <input type="text" name="guarantor_cheque_number_1" id="guarantor_cheque_number_1" class="form-control" value="{{old('guarantor_cheque_number_1')}}">
  </div>
</div>

<div class="form-group row cheque-mode p-mode">
  <label class="col-form-label col-lg-2">Cheque Number 2</label>
  <div class="col-lg-4">
    <input type="text" name="guarantor_cheque_number_2" id="guarantor_cheque_number_2" class="form-control" value="{{old('guarantor_cheque_number_2')}}">
  </div>

  <label class="col-form-label col-lg-2">Security</label>
  <div class="col-lg-4">
    <select name="guarantor_security" id="guarantor_security" class="form-control" title="Please select something!">
      <option value="">Select Type</option>
      <option value="0">Cheuqe</option>
      <option value="1">Passbook</option>
      <option value="2">FD certificate</option>
    </select>
  </div>
</div>

<div class="form-group row">
  <label class="col-form-label col-lg-2">Security cheque/Stamp</label>
  <div class="col-lg-4">
    <input type="file" name="guarantor_income_file" id="guarantor_income_file" class="form-control">
  </div>
</div>

<h5>Documents</h5>
<div class="form-group row">
  <label class="col-form-label col-lg-2">ID  Proof<sup>*</sup></label>
  <div class="col-lg-12">
    <select name="guarantor_id_proof" id="guarantor_id_proof" class="form-control" title="Please select something!">
      <option data-val="guarantor" value="">Select ID</option>
      <option data-val="guarantor" value="1">Voter ID</option>
      <option data-val="guarantor" value="2">DL</option>
      <option data-val="guarantor" value="3">Aadhar card</option>
      <option data-val="guarantor" value="4">Passport</option>
      <option data-val="guarantor" value="5">Pan Card</option>
      <option data-val="guarantor" value="6">Other</option>
      <option data-val="guarantor" value="7">Electricity Bill</option>
    </select>
  </div>
</div>

<div class="form-group row">
  <label class="col-form-label col-lg-2">Enter Id number<sup>*</sup></label>

  <div class="col-lg-4 ">
    <input type="text" name="guarantor_id_number" id="guarantor_id_number" class="form-control">
  </div>
  <div class="col-lg-1 ">
    <button type="button" id="guarantor_id_tooltip"  class="btn btn-sm btn-default" data-toggle="tooltip" data-placement="top" title="Enter Id proof number."><i class="fas fa-exclamation-circle text-secondary"></i>
    </button>
  </div>

  <label class="col-form-label col-lg-2">Upload File <sup class="required">*</sup></label>
  <div class="col-lg-3">
    <input type="file" name="guarantor_id_file" id="guarantor_id_file" class="form-control">
  </div>
</div>

<div class="form-group row">
  <label class="col-form-label col-lg-2">Address Proof<sup>*</sup></label>
  <div class="col-lg-4">
    <select name="guarantor_address_id_proof" id="guarantor_address_id_proof" class="form-control" title="Please select something!">
      <option data-val="guarantor_address" value="">Select ID</option>
      <option data-val="guarantor_address" value="1">Voter ID</option>
      <option data-val="guarantor_address" value="2">DL</option>
      <option data-val="guarantor_address" value="3">Aadhar card</option>
      <option data-val="guarantor_address" value="4">Passport</option>
      {{-- <option data-val="guarantor_address" value="5">Pan Card</option> --}}
      <option data-val="guarantor_address" value="6">Other</option>
      <option data-val="guarantor_address" value="7">Electricity Bill</option>
    </select>
  </div>

  <label class="col-form-label col-lg-2">Enter Id number<sup>*</sup></label>
  <div class="col-lg-3 ">
    <input type="text" name="guarantor_address_id_number" id="guarantor_address_id_number" class="form-control" value="{{old('guarantor_address_id_number')}}">
  </div>
  <div class="col-lg-1 ">
    <button type="button" id="guarantor_address_id_tooltip"  class="btn btn-sm btn-default" data-toggle="tooltip" data-placement="top" title="Enter Id proof number."><i class="fas fa-exclamation-circle text-secondary"></i>
    </button>
  </div>

</div>

<div class="form-group row">
  <label class="col-form-label col-lg-2">Upload File <sup class="required">*</sup></label>
  <div class="col-lg-4">
    <input type="file" name="guarantor_address_id_file" id="guarantor_address_id_file" class="form-control">
  </div>

  
  <label class="col-form-label col-lg-2">Income<sup>*</sup></label>
  <div class="col-lg-4">
    <select name="guarantor_income" id="guarantor_income" class="form-control" title="Please select something!">
      <option data-val="guarantor" value="">Select Type</option>
      <option data-val="guarantor" value="0">Salary Slip</option>
      <option data-val="guarantor" value="1">ITR</option>
      <option data-val="guarantor" value="2">Others</option>
    </select>
  </div>
  <!-- <label class="col-form-label col-lg-2">Under taking Doc</label>
  <div class="col-lg-4">
    <input type="file" name="guarantor_under_taking_doc" id="under_taking_doc" class="form-control">
  </div> -->
</div>

<div class="form-group row">
  <label class="col-form-label col-lg-2 guarantor-salary-remark" style="display: none;">Remark<sup>*</sup></label>
  <div class="col-lg-4 guarantor-salary-remark" style="display: none;">
    <input type="text" name="guarantor_income_remark" id="guarantor_income_remark" class="form-control" value="{{old('guarantor_income_remark')}}">
  </div>
</div>

<h5>Other Doc.</h3>
<div class="text-left">
  <input type="button" name="more-doc" id="more-doc-button" value="More Doc" class="btn btn-primary" data-val="0">
  <input type="hidden" name="hidden_more_doc" class="hidden_more_doc" value="0">
</div>  

<div class="form-group row more-doc px-3 mt-5" style="display: none;">
  <div class="form-group row flex-grow-1">
    <label class="col-form-label col-lg-2">Doc Title</label>
    <div class="col-lg-3">
      <input type="text" name="guarantor_more_doc_title[0]" id="guarantor_more_doc_title" class="form-control">
    </div>

    <label class="col-form-label col-lg-2">Upload File</label>
    <div class="col-lg-4">
      <input type="file" name="guarantor_more_upload_file[0]" id="guarantor_more_upload_file" class="form-control">
    </div>
  </div>
</div>