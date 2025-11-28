<h3>Co-applicant Details </h5>
<!-- <div class="custom-control custom-checkbox mb-3 col-form-label">
<input type="checkbox" id="co_applicant_checkbox" name="co_applicant_checkbox" class="custom-control-input">
<label class="custom-control-label" for="co_applicant_checkbox">Yes</label>
<input type="hidden" id="co_applicant_checkbox_val" name="co_applicant_checkbox_val">
</div> -->
  
@if(count($loanDetails['LoanCoApplicants']) > 0)
  @foreach($loanDetails['LoanCoApplicants'] as $loanDetail)
  <input type="hidden" name="coapplicant_id" value="{{ $loanDetail->id }}">
  <div class="form-group row co-applicant-form">
    <label class="col-form-label col-lg-2">Member ID<sup>*</sup></label>
    <div class="col-lg-10">
      <input type="text" name="co-applicant_auto_member_id" id="co-applicant_auto_member_id" class="form-control" value="{{ getApplicantid($loanDetail->member_id) }}" readonly="">
      <input type="hidden" name="co-applicant_member_id" id="co-applicant_member_id" class="form-control" value="{{ $loanDetail->member_id }}">
    </div>
  </div>

  <div class="form-group row co-applicant-form">
    <label class="col-form-label col-lg-2">Address permanent<sup>*</sup></label>
    <div class="col-lg-4">
      <select name="co-applicant_address_permanent" id="co-applicant_address_permanent" class="form-control" title="Please select something!">
        <option value="">Select ID</option>
          <option @if($loanDetail->address_permanent == 0) selected @endif value="0">Self</option>
          <option @if($loanDetail->address_permanent == 1) selected @endif value="1">Perental</option>
          <option @if($loanDetail->address_permanent == 2) selected @endif value="2">Rental</option>
      </select>
    </div>

    <label class="col-form-label col-lg-2">Temporary permanent<sup>*</sup></label>
    <div class="col-lg-4">
      <select name="co-applicant_address_temporary" id="co-applicant_address_temporary" class="form-control" title="Please select something!">
        <option value="">Select ID</option>
          <option @if($loanDetail->temporary_permanent == 0) selected @endif value="0">Self</option>
          <option @if($loanDetail->temporary_permanent == 1) selected @endif value="1">Perental</option>
          <option @if($loanDetail->temporary_permanent == 2) selected @endif value="2">Rental</option>
      </select>
    </div>
  </div>

  <h5 class="co-applicant-form">Employment  Details</h5>
  <div class="form-group row cheque-mode p-mode co-applicant-form">
    <label class="col-form-label col-lg-2">Occupation<sup>*</sup></label>
    <div class="col-lg-4">
      <input type="text" name="co-applicant_occupation_name" id="co-applicant_occupation_name" class="form-control" value="{{ getOccupationName($loanDetail->occupation) }}" readonly="">
      <input type="hidden" name="co-applicant_occupation" id="co-applicant_occupation" class="form-control" value="{{ $loanDetail->occupation }}">
    </div>
    <label class="col-form-label col-lg-2">Organization<sup>*</sup></label>
    <div class="col-lg-4">
      <input type="text" name="co-applicant_organization" id="co-applicant_organization" class="form-control" value="{{ $loanDetail->organization }}" readonly="">
    </div>
  </div>

  <div class="form-group row cheque-mode p-mode co-applicant-form">
    <label class="col-form-label col-lg-2">Designation<sup>*</sup></label>
    <div class="col-lg-4">
      <input type="text" name="co-applicant_designation" id="co-applicant_designation" class="form-control" value="{{ $loanDetail->designation }}" readonly="">
    </div>
    <label class="col-form-label col-lg-2">Monthly Income<sup>*</sup></label>
    <div class="col-lg-4">
      <input type="text" name="co-applicant_monthly_income" id="co-applicant_monthly_income" class="form-control" value="{{ $loanDetail->monthly_income }}">
    </div>
  </div>

  <div class="form-group row cheque-mode p-mode co-applicant-form">
    <label class="col-form-label col-lg-2">Year from<sup>*</sup></label>
    <div class="col-lg-4">
      <input type="text" name="co-applicant_year_from" id="co-applicant_year_from" class="form-control" value="{{ $loanDetail->year_from }}">
    </div>
  </div>

  <h5 class="co-applicant-form">Bank details/ Security Cheque Bank details</h5>
  <div class="form-group row cheque-mode p-mode co-applicant-form">
    <label class="col-form-label col-lg-2">Bank Name<sup>*</sup></label>
    <div class="col-lg-4">
      <input type="text" name="co-applicant_bank_name" id="co-applicant_bank_name" class="form-control" value="{{ $loanDetail->bank_name }}">
    </div>
    <label class="col-form-label col-lg-2">Bank Account Number </label>
    <div class="col-lg-4">
      <input type="text" name="co-applicant_bank_account_number" id="co-applicant_bank_account_number" class="form-control" value="{{ $loanDetail->bank_account_number }}">
    </div>
  </div>

  <div class="form-group row cheque-mode p-mode co-applicant-form">
    <label class="col-form-label col-lg-2">IFSC Code<sup>*</sup></label>
    <div class="col-lg-4">
      <input type="text" name="co-applicant_ifsc_code" id="co-applicant_ifsc_code" class="form-control" value="{{ $loanDetail->ifsc_code }}">
    </div>
    <label class="col-form-label col-lg-2">Cheque Number 1<sup>*</sup></label>
    <div class="col-lg-4">
      <input type="text" name="co-applicant_cheque_number_1" id="co-applicant_cheque_number-1" class="form-control" value="{{ $loanDetail->cheque_number_1 }}">
    </div>
  </div>

  <div class="form-group row cheque-mode p-mode co-applicant-form">
    <label class="col-form-label col-lg-2">Cheque Number 2<sup>*</sup></label>
    <div class="col-lg-4">
      <input type="text" name="co-applicant_cheque_number_2" id="co-applicant_cheque_number_2" class="form-control" value="{{ $loanDetail->cheque_number_2 }}">
    </div>

    <label class="col-form-label col-lg-2">Security<sup>*</sup></label>
    <div class="col-lg-4">
      <select name="co-applicant_security" id="co-applicant_security" class="form-control" title="Please select something!">
        <option value="">Select Type</option>
        <option @if($loanDetail->security == 0) selected @endif value="0">Cheuqe</option>
        <option @if($loanDetail->security == 1) selected @endif value="1">Passbook</option>
        <option @if($loanDetail->security == 2) selected @endif value="2">FD certificate</option>
      </select>
    </div>
  </div>

  <div class="form-group row co-applicant-form">
    <label class="col-form-label col-lg-2">Security cheque/Stamp</label>
    <div class="col-lg-4">
      <input type="file" name="co-applicant_income_file" id="co-applicant_income_file" class="form-control">
      @php
        $coapplicantIncomeFiles = getFileData($loanDetail->income_file_id);
      @endphp

      @if(count($coapplicantIncomeFiles) > 0)
        @foreach($coapplicantIncomeFiles as $coapplicantIncomeFile) 

        @php
          $foldeName = 'loan/document/'.$loanDetails->id.'/coapplicant/income_proof/'.$coapplicantIncomeFile->file_name.'';
          $url = ImageUpload::generatePreSignedUrl($foldeName);
        @endphp


          <span><a href="{{ $url }}" target="blank">{{ $coapplicantIncomeFile->file_name }}</a></span>
          <input type="hidden" name="hidden_coapplicant_income_file_id" id="hidden_coapplicant_income_file_id" value="{{ $coapplicantIncomeFile->id }}">
        @endforeach
      @else
        <input type="hidden" name="hidden_coapplicant_income_file_id" id="hidden_coapplicant_income_file_id" value="">
      @endif
    </div>
  </div>

  <h5 class="co-applicant-form">Documents</h5>
  <div class="form-group row co-applicant-form">
    <label class="col-form-label col-lg-2">ID  Proof<sup>*</sup></label>
    <div class="col-lg-12">
      <select name="co-applicant_id_proof" id="co-applicant_id_proof" class="form-control" title="Please select something!">
        <option data-val="applicant" value="">Select ID</option>
        <option data-val="applicant" @if($loanDetail->id_proof_type == 1) selected @endif value="1">Voter ID</option>
        <option data-val="applicant" @if($loanDetail->id_proof_type == 2) selected @endif value="2">DL</option>
        <option data-val="applicant" @if($loanDetail->id_proof_type == 3) selected @endif value="3">Aadhar card</option>
        <option data-val="applicant" @if($loanDetail->id_proof_type == 4) selected @endif value="4">Passport</option>
        <option data-val="applicant" @if($loanDetail->id_proof_type == 5) selected @endif value="5">Pan Card</option>
        <option data-val="applicant" @if($loanDetail->id_proof_type == 6) selected @endif value="6">Other</option>
        <option data-val="applicant" @if($loanDetail->id_proof_type == 7) selected @endif value="7">Electricity Bill</option>
      </select>
    </div>
  </div>

  <div class="form-group row co-applicant-form">
    <label class="col-form-label col-lg-2">Enter Id number<sup>*</sup></label>
    <div class="col-lg-4">
      <input type="text" name="co-applicant_id_number" id="co-applicant_id_number" class="form-control" value="{{ $loanDetail->id_proof_number }}">
    </div>
    <div class="col-lg-1 ">
      <button type="button" id="co-applicant_id_tooltip"  class="btn btn-sm btn-default" data-toggle="tooltip" data-placement="top" title="Enter Id proof number."><i class="fas fa-exclamation-circle text-secondary"></i>
      </button>
    </div>

    <label class="col-form-label col-lg-2">Upload File</label>
    <div class="col-lg-3">
      <input type="file" name="co-applicant_id_file" id="co-applicant_id_file" class="form-control">
        @php
          $coapplicantFiles = getFileData($loanDetail->id_proof_file_id);
        @endphp
        @if(count($coapplicantFiles) > 0)
          @foreach($coapplicantFiles as $coapplicantFile)

            @php
              $foldeName = 'loan/document/'.$loanDetails->id.'/coapplicant/id_proof/'.$coapplicantFile->file_name.'';
              $url = ImageUpload::generatePreSignedUrl($foldeName);
            @endphp
            <span><a href="{{ $url }}" target="blank">{{ $coapplicantFile->file_name }}</a></span>
            <input type="hidden" name="hidden_coapplicant_file_id" id="hidden_coapplicant_file_id" value="{{ $coapplicantFile->id }}">
          @endforeach
        @else
          <input type="hidden" name="hidden_coapplicant_file_id" id="hidden_coapplicant_file_id" value="">
        @endif
    </div>
  </div>

  <div class="form-group row co-applicant-form">
    <label class="col-form-label col-lg-2">Address Proof<sup>*</sup></label>
    <div class="col-lg-4">
      <select name="co-applicant_address_id_proof" id="co-applicant_address_id_proof" class="form-control" title="Please select something!">
          <option data-val="co-applicant_address" value="">Select ID</option>
          <option data-val="co-applicant_address" @if($loanDetail->address_proof_type == 1) selected @endif value="1">Voter ID</option>
          <option data-val="co-applicant_address" @if($loanDetail->address_proof_type == 2) selected @endif value="2">DL</option>
          <option data-val="co-applicant_address" @if($loanDetail->address_proof_type == 3) selected @endif value="3">Aadhar card</option>
          <option data-val="co-applicant_address" @if($loanDetail->address_proof_type == 4) selected @endif value="4">Passport</option>
          <option data-val="co-applicant_address" @if($loanDetail->address_proof_type == 5) selected @endif value="5">Pan Card</option>
          <option data-val="co-applicant_address" @if($loanDetail->address_proof_type == 6) selected @endif value="6">Other</option>
          <option data-val="co-applicant_address" @if($loanDetail->address_proof_type == 7) selected @endif value="7">Electricity Bill</option>
      </select>
    </div>

    <label class="col-form-label col-lg-2">Enter Id number<sup>*</sup></label>
    <div class="col-lg-3">
      <input type="text" name="co-applicant_address_id_number" id="co-applicant_address_id_number" class="form-control" value="{{ $loanDetail->address_proof_id_number }}">
    </div>
    <div class="col-lg-1">
      <button type="button" id="co-applicant_address_id_tooltip"  class="btn btn-sm btn-default" data-toggle="tooltip" data-placement="top" title="Enter Id proof number."><i class="fas fa-exclamation-circle text-secondary"></i>
      </button>
    </div>
  </div>

  <div class="form-group row co-applicant-form">
    <label class="col-form-label col-lg-2">Upload File</label>
    <div class="col-lg-4">
      <input type="file" name="co-applicant_address_id_file" id="co-applicant_address_id_file" class="form-control">
      @php
        $coapplicantAddressFiles = getFileData($loanDetail->address_proof_file_id);
      @endphp

      @if(count($coapplicantAddressFiles) > 0)
        @foreach($coapplicantAddressFiles as $coapplicantAddressFile)
             @php
              $foldeName = 'loan/document/'.$loanDetails->id.'/coapplicant/address_proof/'.$coapplicantAddressFile->file_name.'';
              $url = ImageUpload::generatePreSignedUrl($foldeName);
            @endphp
          <span><a href="{{$url }}" target="blank">{{ $coapplicantAddressFile->file_name }}</a></span>
          <input type="hidden" name="hidden_coapplicant_address_file_id" id="hidden_coapplicant_address_file_id" value="{{ $coapplicantAddressFile->id }}">
        @endforeach
      @else
        <input type="hidden" name="hidden_coapplicant_address_file_id" id="hidden_coapplicant_address_file_id" value="">
      @endif
    </div>

    <label class="col-form-label col-lg-2">Under taking Doc</label>
    <div class="col-lg-4">
      <input type="file" name="co-applicant_under_taking_doc" id="co-applicant_under_taking_doc" class="form-control">
      @php
          $coapplicantUnderTakingFiles = getFileData($loanDetail->under_taking_doc);
      @endphp
      
      @if(count($coapplicantUnderTakingFiles) > 0)
        @foreach($coapplicantUnderTakingFiles as $coapplicantUnderTakingFile)

          @php
              $foldeName = 'loan/document/'.$loanDetails->id.'/coapplicant/undertakingdoc/'.$coapplicantUnderTakingFile->file_name.'';
              $url = ImageUpload::generatePreSignedUrl($foldeName);
            @endphp
          <span><a href="{{ $url }}" target="blank">{{ $coapplicantUnderTakingFile->file_name }}</a></span>
          <input type="hidden" name="hidden_coapplicant_under_taking_file_id" id="hidden_coapplicant_under_taking_file_id" value="{{ $coapplicantUnderTakingFile->id }}">
        @endforeach
      @else
        <input type="hidden" name="hidden_coapplicant_under_taking_file_id" id="hidden_coapplicant_under_taking_file_id" value="">
      @endif
    </div>
  </div>

  <div class="form-group row co-applicant-form">
    <label class="col-form-label col-lg-2">Income<sup>*</sup></label>
    <div class="col-lg-4">
      <select name="co-applicant_income" id="co-applicant_income" class="form-control" title="Please select something!">
          <option data-val="co-applicant" value="">Select Type</option>
          <option data-val="co-applicant" @if($loanDetail->income_type == 0) selected @endif value="0">Salary Slip</option>
          <option data-val="co-applicant" @if($loanDetail->income_type == 1) selected @endif value="1">ITR</option>
          <option data-val="co-applicant" @if($loanDetail->income_type == 2) selected @endif value="2">Others</option>

      </select>
    </div>

    <label class="col-form-label col-lg-2 co-applicant-salary-remark" @if($loanDetail->income_type != 2) style="display: none;" @endif>Remark<sup>*</sup></label>
    <div class="col-lg-4 co-applicant-salary-remark" @if($loanDetail->income_type != 2)  style="display: none;" @endif>
      <input type="text" name="co_applicant_remark" id="co_applicant_remark" class="form-control" value="{{ $loanDetail->income_remark }}">
    </div>
  </div>

  @endforeach
@else
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
      <input type="text" name="co-applicant_organization" id="co-applicant_organization" class="form-control" readonly="" value="Samradh bestwin">
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
    <label class="col-form-label col-lg-2">Bank Name<sup>*</sup></label>
    <div class="col-lg-4">
      <input type="text" name="co-applicant_bank_name" id="co-applicant_bank_name" class="form-control">
    </div>
    <label class="col-form-label col-lg-2">Bank Account Number </label>
    <div class="col-lg-4">
      <input type="text" name="co-applicant_bank_account_number" id="co-applicant_bank_account_number" class="form-control">
    </div>
  </div>

  <div class="form-group row cheque-mode p-mode co-applicant-form" style="display: none;">
    <label class="col-form-label col-lg-2">IFSC Code<sup>*</sup></label>
    <div class="col-lg-4">
      <input type="text" name="co-applicant_ifsc_code" id="co-applicant_ifsc_code" class="form-control">
    </div>
    <label class="col-form-label col-lg-2">Cheque Number 1<sup>*</sup></label>
    <div class="col-lg-4">
      <input type="text" name="co-applicant_cheque_number_1" id="co-applicant_cheque_number-1" class="form-control">
    </div>
  </div>

  <div class="form-group row cheque-mode p-mode co-applicant-form" style="display: none;">
    <label class="col-form-label col-lg-2">Cheque Number 2<sup>*</sup></label>
    <div class="col-lg-4">
      <input type="text" name="co-applicant_cheque_number_2" id="co-applicant_cheque_number_2" class="form-control">
    </div>

    <label class="col-form-label col-lg-2">Security<sup>*</sup></label>
    <div class="col-lg-4">
      <select name="co-applicant_security" id="co-applicant_security" class="form-control" title="Please select something!">
        <option value="">Select Type</option>
        <option value="0">Cheuqe</option>
        <option value="1">Passbook</option>
        <option value="2">FD certificate</option>
      </select>
    </div>
  </div>

  <div class="form-group row co-applicant-form" style="display: none;">
    <label class="col-form-label col-lg-2">Upload File</label>
    <div class="col-lg-4">
      <input type="file" name="co-applicant_income_file" id="co-applicant_income_file" class="form-control">
    </div>
  </div>

  <h5 class="co-applicant-form" style="display: none;">Documents</h5>
  <div class="form-group row co-applicant-form" style="display: none;">
    <label class="col-form-label col-lg-2">ID  Proof<sup>*</sup></label>
    <div class="col-lg-12">
      <select name="co-applicant_id_proof" id="co-applicant_id_proof" class="form-control" title="Please select something!">
        <option data-val="co-applicant" value="">Select ID</option>
        <option data-val="co-applicant" value="0">Pen card</option>
        <option data-val="co-applicant" value="1">DL</option>
        <option data-val="co-applicant" value="2">Voter ID</option>
        <option data-val="co-applicant" value="3">Passport</option>
        <option data-val="co-applicant" value="4">Identity card</option>
        <option data-val="co-applicant" value="5">Aadhar card</option>
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

    <label class="col-form-label col-lg-2">Upload File</label>
    <div class="col-lg-3">
      <input type="file" name="co-applicant_id_file" id="co-applicant_id_file" class="form-control">
    </div>
  </div>

  <div class="form-group row co-applicant-form" style="display: none;">
    <label class="col-form-label col-lg-2">Address Proof<sup>*</sup></label>
    <div class="col-lg-4">
      <select name="co-applicant_address_id_proof" id="co-applicant_address_id_proof" class="form-control" title="Please select something!">
        <option data-val="co-applicant_address" value="">Select ID</option>
        <option data-val="co-applicant_address" value="1">DL</option>
        <option data-val="co-applicant_address" value="2">Voter ID</option>
        <option data-val="co-applicant_address" value="3">Passport</option>
        <option data-val="co-applicant_address" value="4">Identity card</option>
        <option data-val="co-applicant_address" value="5">Aadhar card</option>
        <option data-val="co-applicant_address" value="6">Bank passbook</option>
        <option data-val="co-applicant_address" value="7">Electricity Bill</option>
        <option data-val="co-applicant_address" value="8">Telephone bill</option>
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
@endif