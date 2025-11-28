@foreach($loanDetails['LoanGuarantor'] as $loanDetail)
<h3>Guarantor Details</h3>
<input type="hidden" name="guarantor_id" value="{{ $loanDetail->id }}">
<div class="form-group row">
  <label class="col-form-label col-lg-2">Member ID<sup>*</sup></label>
  <div class="col-lg-4">
    <input type="text" name="guarantor_auto_member_id" id="guarantor_auto_member_id" class="form-control" value="{{ getApplicantid($loanDetail->member_id) }}" readonly="">
    <input type="hidden" name="guarantor_member_id" id="guarantor_member_id" value="{{ $loanDetails->applicant_id }}">
  </div>

  <label class="col-form-label col-lg-2">Name<sup>*</sup></label>
  <div class="col-lg-4">
    <input type="text" name="guarantor_name" id="guarantor_name" class="form-control" value="{{ $loanDetail->name }}">
  </div>
</div>

<div class="form-group row">
  <label class="col-form-label col-lg-2">Father Name<sup>*</sup></label>
  <div class="col-lg-4">
    <input type="text" name="guarantor_father_name" id="guarantor_father_name" class="form-control" value="{{ $loanDetail->father_name }}">
  </div>

  <label class="col-form-label col-lg-2">Date of Birth<sup>*</sup></label>
  <div class="col-lg-4">
    <input type="text" readonly name="guarantor_dob" id="guarantor_dob" class="form-control date_of_birth" value="{{ date("d/m/Y", strtotime(convertDate($loanDetail->dob))) }}">
  </div>
</div>

<div class="form-group row">
  <label class="col-form-label col-lg-2">Marital Status<sup>*</sup></label>
  <div class="col-lg-4">
    <select name="guarantor_marital_status" id="guarantor_marital_status" class="form-control" title="Please select something!">
      <option value="">Select Type</option>
      <option @if($loanDetail->marital_status == 0) selected @endif value="0">Single</option>
      <option @if($loanDetail->marital_status == 1) selected @endif value="1">Married</option>
    </select>
  </div>

  <label class="col-form-label col-lg-2">Local Address<sup>*</sup></label>
  <div class="col-lg-4">
    <input type="text" name="local_address" id="local_address" class="form-control" value="{{ $loanDetail->local_address }}">
  </div>
</div>

<div class="form-group row">
  <label class="col-form-label col-lg-2">Ownership<sup>*</sup></label>
  <div class="col-lg-4">
    <select name="guarantor_ownership" id="guarantor_ownership" class="form-control" title="Please select something!">
      <option value="">Select Type</option>
      <option @if($loanDetail->ownership == 0) selected @endif value="0">Self</option>
      <option @if($loanDetail->ownership == 1) selected @endif value="1">Perental</option>
      <option @if($loanDetail->ownership == 2) selected @endif value="2">Rental</option>
    </select>
  </div>

  <label class="col-form-label col-lg-2">Temporary Address<sup>*</sup></label>
  <div class="col-lg-4">
    <select name="guarantor_temporary_address" id="guarantor_temporary_address" class="form-control" title="Please select something!">
      <option value="">Select ID</option>
      <option @if($loanDetail->temporary_permanent == 0) selected @endif value="0">Self</option>
      <option @if($loanDetail->temporary_permanent == 1) selected @endif value="1">Perental</option>
      <option @if($loanDetail->temporary_permanent == 2) selected @endif value="2">Rental</option>
    </select>
  </div>
</div>

<div class="form-group row">
  <label class="col-form-label col-lg-2">Mobile Number<sup>*</sup></label>
  <div class="col-lg-4">
    <input type="text" name="guarantor_mobile_number" id="guarantor_mobile_number" class="form-control" value="{{ $loanDetail->mobile_number }}" title="Please enter minimum 10 or maximum 12 digit.">
  </div>

  <label class="col-form-label col-lg-2">Educational Qualification<sup>*</sup></label>
  <div class="col-lg-4">
    <select name="guarantor_educational_qualification" id="guarantor_educational_qualification" class="form-control" title="Please select something!">
      <option value="">Select Type</option>
      <option @if($loanDetail->educational_qualification == 1) selected @endif value="1">Higher secondary</option>
      <option @if($loanDetail->educational_qualification == 2) selected @endif value="2">Junior High school</option>
      <option @if($loanDetail->educational_qualification == 3) selected @endif value="3">Graduation</option>
      <option @if($loanDetail->educational_qualification == 4) selected @endif value="4">Post Graduation</option>
    </select>
  </div>
</div>

<div class="form-group row">
  <label class="col-form-label col-lg-2">No. of Dependents <sup>*</sup></label>
  <div class="col-lg-12">
    <input type="text" name="guarantor_dependents_number" id="guarantor_dependents_number" class="form-control" value="{{ $loanDetail->number_of_dependents }}">
  </div>
</div>

<h5>Employment  Details</h5>
<div class="form-group row cheque-mode p-mode">
  <label class="col-form-label col-lg-2">Occupation<sup>*</sup></label>
  <div class="col-lg-4">
    <input type="text" name="guarantor_occupation_name" id="guarantor_occupation_name" class="form-control" value="{{ getOccupationName($loanDetail->occupation) }}" readonly="">
    <input type="hidden" name="guarantor_occupation" id="guarantor_occupation" class="form-control" value="{{ $loanDetail->occupation }}">
  </div>
  <label class="col-form-label col-lg-2">Organization<sup>*</sup></label>
  <div class="col-lg-4">
    <input type="text" name="guarantor_organization" id="guarantor_organization" class="form-control" value="{{ $loanDetail->organization }}">
  </div>
</div>

<div class="form-group row cheque-mode p-mode">
  <label class="col-form-label col-lg-2">Designation</label>
  <div class="col-lg-4">
    <input type="text" name="guarantor_designation" id="guarantor_designation" class="form-control" value="{{ $loanDetail->designation }}">
  </div>
  <label class="col-form-label col-lg-2">Monthly Income<sup>*</sup></label>
  <div class="col-lg-4">
    <input type="text" name="guarantor_monthly_income" id="guarantor_monthly_income" class="form-control" value="{{ $loanDetail->monthly_income }}">
  </div>
</div>

<div class="form-group row cheque-mode p-mode">
  <label class="col-form-label col-lg-2">Year from<sup>*</sup></label>
  <div class="col-lg-4">
    <input type="text" name="guarantor_year_from" id="guarantor_year_from" class="form-control" value="{{ $loanDetail->year_from }}">
  </div>
</div>

<h5>Bank details/ Security Cheque Bank details</h5>
<div class="form-group row cheque-mode p-mode">
  <label class="col-form-label col-lg-2">Bank Name<sup>*</sup></label>
  <div class="col-lg-4">
    <input type="text" name="guarantor_bank_name" id="guarantor_bank_name" class="form-control" value="{{ $loanDetail->bank_name }}">
  </div>
  <label class="col-form-label col-lg-2">Bank Account Number </label>
  <div class="col-lg-4">
    <input type="text" name="guarantor_bank_account_number" id="guarantor_bank_account_number" class="form-control" value="{{ $loanDetail->bank_account_number }}">
  </div>
</div>

<div class="form-group row cheque-mode p-mode">
  <label class="col-form-label col-lg-2">IFSC Code<sup>*</sup></label>
  <div class="col-lg-4">
    <input type="text" name="guarantor_ifsc_code" id="guarantor_ifsc_code" class="form-control" value="{{ $loanDetail->ifsc_code }}">
  </div>
  <label class="col-form-label col-lg-2">Cheque Number 1<sup>*</sup></label>
  <div class="col-lg-4">
    <input type="text" name="guarantor_cheque_number_1" id="guarantor_cheque_number_1" class="form-control" value="{{ $loanDetail->cheque_number_1 }}">
  </div>
</div>

<div class="form-group row cheque-mode p-mode">
  <label class="col-form-label col-lg-2">Cheque Number 2<sup>*</sup></label>
  <div class="col-lg-12">
    <input type="text" name="guarantor_cheque_number_2" id="guarantor_cheque_number_2" class="form-control" value="{{ $loanDetail->cheque_number_2 }}">
  </div>
</div>

<h5>Documents</h5>
<div class="form-group row">
  <label class="col-form-label col-lg-2">ID  Proof<sup>*</sup></label>
  <div class="col-lg-12">
    <select name="guarantor_id_proof" id="guarantor_id_proof" class="form-control" title="Please select something!">
      <option value="">Select ID</option>
      <option data-val="guarantor" @if($loanDetail->id_proof_type == 1) selected @endif value="1">Voter ID</option>
      <option data-val="guarantor" @if($loanDetail->id_proof_type == 2) selected @endif value="2">DL</option>
      <option data-val="guarantor" @if($loanDetail->id_proof_type == 3) selected @endif value="3">Aadhar card</option>
      <option data-val="guarantor" @if($loanDetail->id_proof_type == 4) selected @endif value="4">Passport</option>
      <option data-val="guarantor" @if($loanDetail->id_proof_type == 5) selected @endif value="5">Pan Card</option>
      <option data-val="guarantor" @if($loanDetail->id_proof_type == 6) selected @endif value="6">Other</option>
      <option data-val="guarantor" @if($loanDetail->id_proof_type == 7) selected @endif value="7">Electricity Bill</option>
    </select>
  </div>
</div>

<div class="form-group row">
  <label class="col-form-label col-lg-2">Enter Id number<sup>*</sup></label>
  <div class="col-lg-4">
    <input type="text" name="guarantor_id_number" id="guarantor_id_number" class="form-control" value="{{ $loanDetail->id_proof_number }}">
  </div>

  <label class="col-form-label col-lg-2">Security cheque/Stamp</label>
  <div class="col-lg-4">
    <input type="file" name="guarantor_id_file" id="guarantor_id_file" class="form-control">
    @php
      $guarantorFiles = getFileData($loanDetail->id_proof_file_id);
    @endphp
    
    @if(count($guarantorFiles) > 0)
      @foreach($guarantorFiles as $guarantorFile)
        <span><a href="{{ URL('core/storage/images/loan/document/'.$loanDetails->id.'/guarantor/id_proof/'.$guarantorFile->file_name.'') }}" target="blank">{{ $guarantorFile->file_name }}</a></span>
        <input type="hidden" name="hidden_guarantor_file_id" id="hidden_guarantor_file_id" value="{{ $guarantorFile->id }}">
      @endforeach
    @else
      <input type="hidden" name="hidden_guarantor_file_id" id="hidden_guarantor_file_id" value="">
    @endif
  </div>
</div>

<div class="form-group row">
  <label class="col-form-label col-lg-2">Address Proof<sup>*</sup></label>
  <div class="col-lg-4">
    <select name="guarantor_address_id_proof" id="guarantor_address_id_proof" class="form-control" title="Please select something!">
      <option value="">Select ID</option>
      <option data-val="guarantor_address" @if($loanDetail->address_proof_type == 1) selected @endif value="1">Voter ID</option>
      <option data-val="guarantor_address" @if($loanDetail->address_proof_type == 2) selected @endif value="2">DL</option>
      <option data-val="guarantor_address" @if($loanDetail->address_proof_type == 3) selected @endif value="3">Aadhar card</option>
      <option data-val="guarantor_address" @if($loanDetail->address_proof_type == 4) selected @endif value="4">Passport</option>
      <option data-val="guarantor_address" @if($loanDetail->address_proof_type == 5) selected @endif value="5">Pan Card</option>
      <option data-val="guarantor_address" @if($loanDetail->address_proof_type == 6) selected @endif value="6">Other</option>
      <option data-val="guarantor_address" @if($loanDetail->address_proof_type == 7) selected @endif value="7">Electricity Bill</option>
    </select>
  </div>

  <label class="col-form-label col-lg-2">Enter Id number<sup>*</sup></label>
  <div class="col-lg-4">
    <input type="text" name="guarantor_address_id_number" id="guarantor_address_id_number" class="form-control" value="{{ $loanDetail->address_proof_id_number }}">
  </div>
</div>

<div class="form-group row">
  <label class="col-form-label col-lg-2">Upload File</label>
  <div class="col-lg-4">
    <input type="file" name="guarantor_address_id_file" id="guarantor_address_id_file" class="form-control">
    @php
      $guarantorAddressFiles = getFileData($loanDetail->address_proof_file_id);
    @endphp
    
    @if(count($guarantorAddressFiles) > 0)
      @foreach($guarantorAddressFiles as $guarantorAddressFile)
        <span><a href="{{ URL('core/storage/images/loan/document/'.$loanDetails->id.'/guarantor/address_proof/'.$guarantorAddressFile->file_name.'') }}" target="blank">{{ $guarantorAddressFile->file_name }}</a></span>
        <input type="hidden" name="hidden_guarantor_address_file_id" id="hidden_guarantor_address_file_id" value="{{ $guarantorAddressFile->id }}">
      @endforeach
    @else
      <input type="hidden" name="hidden_guarantor_address_file_id" id="hidden_guarantor_address_file_id" value="">
    @endif
  </div>

  <label class="col-form-label col-lg-2">Security<sup>*</sup></label>
  <div class="col-lg-4">
    <select name="guarantor_security" id="guarantor_security" class="form-control" title="Please select something!">
      <option value="">Select Type</option>
      <option @if($loanDetail->security == 0) selected @endif value="0">Cheuqe</option>
      <option @if($loanDetail->security == 1) selected @endif value="1">Passbook</option>
      <option @if($loanDetail->security == 2) selected @endif value="2">FD certificate</option>
    </select>
  </div>

  <!-- <label class="col-form-label col-lg-2">Under taking Doc</label>
  <div class="col-lg-4">
    <input type="file" name="guarantor_under_taking_doc" id="under_taking_doc" class="form-control">
    @php
        $guarantorUnderTakingFiles = getFileData($loanDetail->under_taking_doc);
    @endphp
    
    @if(count($guarantorUnderTakingFiles) > 0)
      @foreach($guarantorUnderTakingFiles as $guarantorUnderTakingFile)
        <span><a href="{{ URL('core/storage/images/loan/document/'.$loanDetails->id.'/guarantor/undertakingdoc/'.$guarantorUnderTakingFile->file_name.'') }}" target="blank">{{ $guarantorUnderTakingFile->file_name }}</a></span>
        <input type="hidden" name="hidden_guarantor_under_taking_file_id" id="hidden_guarantor_under_taking_file_id" value="{{ $guarantorUnderTakingFile->id }}">
      @endforeach
    @else
      <input type="hidden" name="hidden_guarantor_under_taking_file_id" id="hidden_guarantor_under_taking_file_id" value="">
    @endif
  </div> -->
</div>

<div class="form-group row">
  <label class="col-form-label col-lg-2">Upload File</label>
  <div class="col-lg-4">
    <input type="file" name="guarantor_income_file" id="guarantor_income_file" class="form-control">
    @php
      $guarantorIncomeFiles = getFileData($loanDetail->income_file_id);
    @endphp
    
    @if(count($guarantorIncomeFiles) > 0)
      @foreach($guarantorIncomeFiles as $guarantorIncomeFile)
        <span><a href="{{ URL('core/storage/images/loan/document/'.$loanDetails->id.'/guarantor/income_proof/'.$guarantorIncomeFile->file_name.'') }}" target="blank">{{ $guarantorIncomeFile->file_name }}</a></span>
        <input type="hidden" name="hidden_guarantor_income_file_id" id="hidden_guarantor_income_file_id" value="{{ $guarantorIncomeFile->id }}">
      @endforeach
    @else
      <input type="hidden" name="hidden_guarantor_income_file_id" id="hidden_guarantor_income_file_id" value="">
    @endif
  </div>

  <label class="col-form-label col-lg-2">Income<sup>*</sup></label>
  <div class="col-lg-4">
    <select name="guarantor_income" id="guarantor_income" class="form-control" title="Please select something!">
      <option value="">Select Type</option>
      <option data-val="guarantor" @if($loanDetail->income_type == 0) selected @endif value="0">Salary Slip</option>
      <option data-val="guarantor" @if($loanDetail->income_type == 1) selected @endif value="1">ITR</option>
      <option data-val="guarantor" @if($loanDetail->income_type == 2) selected @endif value="2">Others</option>
    </select>
  </div>
</div>

<div class="form-group row">
  <label class="col-form-label col-lg-2 guarantor-salary-remark" @if($loanDetail->income_type != 2) style="display: none;" @endif>Remark<sup>*</sup></label>
  <div class="col-lg-4 guarantor-salary-remark" @if($loanDetail->income_type != 2) style="display: none;" @endif>
    <input type="text" name="guarantor_income_remark" id="guarantor_income_remark" class="form-control" value="{{ $loanDetail->income_remark }}">
  </div>
</div>

<h5>Other Doc.</h3>

@if ( count( $loanDetails['Loanotherdocs'] ) > 0)
  <input type="hidden" name="hidden_more_doc" class="hidden_more_doc" value="1">
  <input type="hidden" name="count_more_doc" class="count_more_doc" value="{{ count($loanDetails['Loanotherdocs'])-1 }}">
@else
  <input type="hidden" name="hidden_more_doc" class="hidden_more_doc" value="0">
  <input type="hidden" name="count_more_doc" class="count_more_doc" value="0">
@endif

<div class="text-left">
  <input type="button" name="more-doc" id="more-doc-button" value="More Doc" class="btn btn-primary">
</div>

<div class="form-group row more-doc px-3 mt-5" @if (count( $loanDetails['Loanotherdocs']) == '') style="display: none;"  @endif>
  @if ( count( $loanDetails['Loanotherdocs'] ) > 0)
    @foreach($loanDetails['Loanotherdocs'] as $key => $loanotherdocs)
    <div class="form-group row flex-grow-1">
        <label class="col-form-label col-lg-2">Doc Title<sup>*</sup></label>
        <div class="col-lg-4">
          <input type="text" name="guarantor_more_doc_title[{{ $key }}]" id="guarantor_more_doc_title" class="form-control" value="{{ $loanotherdocs['title'] }}">
        </div>

        <label class="col-form-label col-lg-2">Upload File</label>
        <div class="col-lg-4">
          <input type="file" name="guarantor_more_upload_file[{{ $key }}]" id="guarantor_more_upload_file" class="form-control">
          @php
            $files = getFileData($loanotherdocs['file_id']);
          @endphp

          @if($files)
              @foreach($files as $file)
                  <span><a href="{{ URL('core/storage/images/loan/document/'.$loanDetails->id.'/guarantor/moredocument/'.$file->file_name.'') }}" target="blank">{{ $file->file_name }}</a></span>
                  <input type="hidden" name="hidden_other_doc_file_id[{{ $key }}]" id="hidden_other_doc_file_id" value="{{ $file->id }}">
              @endforeach
          @else
            <input type="hidden" name="hidden_other_doc_file_id[{{ $key }}]" id="hidden_other_doc_file_id" value="">
          @endif
        </div>
    </div>
    @endforeach
  @else
    <div class="form-group row flex-grow-1">
      <label class="col-form-label col-lg-2">Doc Title</label>
      <div class="col-lg-3">
        <input type="text" name="guarantor_more_doc_title[0]" id="guarantor_more_doc_title" class="form-control">
      </div>

      <label class="col-form-label col-lg-2">Upload File</label>
      <div class="col-lg-4">
        <input type="file" name="guarantor_more_upload_file[0]" id="guarantor_more_upload_file" class="form-control">
        <input type="hidden" name="hidden_other_doc_file_id[0]" id="hidden_other_doc_file_id" value="">
      </div>
    </div>
  @endif
</div>
@endforeach