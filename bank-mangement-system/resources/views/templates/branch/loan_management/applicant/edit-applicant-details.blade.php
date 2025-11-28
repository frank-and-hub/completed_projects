@foreach($loanDetails['LoanApplicants'] as $loanDetail)
<h3>Applicant Details </h3>
<input type="hidden" name="applicant_id" value="{{ $loanDetail->id }}">
<div class="form-group row">
  <label class="col-form-label col-lg-2">Applicant  Id<sup>*</sup></label>
  <div class="col-lg-4">
    <input type="text" data-val="applicant" name="auto_applicant_id" id="auto_applicant_id" class="form-control" value="{{ getmemberIdfromautoId($loanDetails->applicant_id)->member_id }}" autocomplete="off" readonly="">
    <input type="hidden" name="applicant_member_id" id="applicant_member_id" value="{{ $loanDetails->applicant_id }}">
  </div>
</div>

<div class="form-group row">
  {{-- <label class="col-form-label col-lg-2">Member ID<sup>*</sup></label>
  <div class="col-lg-4">
    <input type="text" name="applicant_member_id" id="applicant_member_id" class="form-control" value="{{ $loanDetail->member_id }}">
  </div> --}}

  <label class="col-form-label col-lg-2">Address permanent<sup>*</sup></label>
  <div class="col-lg-4">
    <select name="applicant_address_permanent" id="applicant_address_permanent" class="form-control" title="Please select something!">
      <option value="">Select ID</option>
      <option @if($loanDetail->address_permanent == 0) selected @endif value="0">Self</option>
      <option @if($loanDetail->address_permanent == 1) selected @endif value="1">Perental</option>
      <option @if($loanDetail->address_permanent == 2) selected @endif value="2">Rental</option>
    </select>
  </div>

  <label class="col-form-label col-lg-2">Temporary permanent<sup>*</sup></label>
  <div class="col-lg-4">
    <select name="applicant_address_temporary" id="applicant_address_temporary" class="form-control" title="Please select something!">
      <option value="">Select ID</option>
      <option @if($loanDetail->temporary_permanent == 0) selected @endif value="0">Self</option>
      <option @if($loanDetail->temporary_permanent == 1) selected @endif value="1">Perental</option>
      <option @if($loanDetail->temporary_permanent == 2) selected @endif value="2">Rental</option>
    </select>
  </div>
</div>

<h5>Employment  Details</h5>
<div class="form-group row cheque-mode p-mode">
  <label class="col-form-label col-lg-2">Occupation<sup>*</sup></label>
  <div class="col-lg-4">
    <input type="text" name="applicant_occupation_name" id="applicant_occupation_name" class="form-control" value="{{ getOccupationName($loanDetail->occupation) }}" readonly="">
    <input type="hidden" name="applicant_occupation" id="applicant_occupation" class="form-control group-loan-occupation" value="{{ $loanDetail->occupation }}">

  </div>
  <label class="col-form-label col-lg-2">Organization<sup>*</sup></label>
  <div class="col-lg-4">
    <input type="text" name="applicant_organization" id="applicant_organization" class="form-control" value="{{ $loanDetail->organization }}">
  </div>
</div>

<div class="form-group row cheque-mode p-mode">
  <label class="col-form-label col-lg-2">Designation<sup>*</sup></label>
  <div class="col-lg-4">
    <input type="text" name="applicant_designation" id="applicant_designation" class="form-control" value="{{ $loanDetail->designation }}" readonly="">
  </div>
  <label class="col-form-label col-lg-2">Monthly Income<sup>*</sup></label>
  <div class="col-lg-4">
    <input type="text" name="applicant_monthly_income" id="applicant_monthly_income" class="form-control" value="{{ $loanDetail->monthly_income }}">
  </div>
</div>

<div class="form-group row cheque-mode p-mode">
  <label class="col-form-label col-lg-2">Year from<sup>*</sup></label>
  <div class="col-lg-4">
    <input type="text" name="applicant_year_from" id="applicant_year_from" class="form-control year" value="{{ $loanDetail->year_from }}">
  </div>
</div>

<h5>Bank details/ Security Cheque Bank details</h5>
<div class="form-group row cheque-mode p-mode">
  <label class="col-form-label col-lg-2">Bank Name<sup>*</sup></label>
  <div class="col-lg-4">
    <input type="text" name="applicant_bank_name" id="applicant_bank_name" class="form-control" value="{{ $loanDetail->bank_name }}">
  </div>
  <label class="col-form-label col-lg-2">Bank Account Number </label>
  <div class="col-lg-4">
    <input type="text" name="applicant_bank_account_number" id="applicant_bank_account_number" class="form-control" value="{{ $loanDetail->bank_account_number }}">
  </div>
</div>

<div class="form-group row cheque-mode p-mode">
  <label class="col-form-label col-lg-2">IFSC Code<sup>*</sup></label>
  <div class="col-lg-4">
    <input type="text" name="applicant_ifsc_code" id="applicant_ifsc_code" class="form-control" value="{{ $loanDetail->ifsc_code }}">
  </div>
  <label class="col-form-label col-lg-2 cheque-box">Cheque Number 1<sup>*</sup></label>
  <div class="col-lg-4 cheque-box">
    <input type="text" name="applicant_cheque_number_1" id="applicant_cheque_number_1" class="form-control" value="{{ $loanDetail->cheque_number_1 }}">
  </div>
</div>

<div class="form-group row cheque-mode p-mode">
  <label class="col-form-label col-lg-2 cheque-box">Cheque Number 2<sup>*</sup></label>
  <div class="col-lg-4 cheque-box">
    <input type="text" name="applicant_cheque_number_2" id="applicant_cheque_number_2" class="form-control" value="{{ $loanDetail->cheque_number_2 }}">
  </div>

  <label class="col-form-label col-lg-2">Security<sup>*</sup></label>
  <div class="col-lg-4">
    <select name="applicant_security" id="applicant_security" class="form-control" title="Please select something!">
      <option value="">Select Type</option>
      <option @if($loanDetail->security == 0) selected @endif value="0">Cheuqe</option>
      <option @if($loanDetail->security == 1) selected @endif value="1">Passbook</option>
      <option @if($loanDetail->security == 2) selected @endif value="2">FD certificate</option>
    </select>
  </div>
</div>

<div class="form-group row">
  <label class="col-form-label col-lg-2">Upload File</label>
  <div class="col-lg-4">
    <input type="file" name="applicant_income_file" id="applicant_income_file" class="form-control">
    @php
      $applicantIncomeFiles = getFileData($loanDetail->income_file_id);
    @endphp

    @if(count($applicantIncomeFiles) > 0)
      @foreach($applicantIncomeFiles as $applicantIncomeFile)
            @php
              $foldeName = 'loan/document/'.$loanDetails->id.'/applicant/income_proof/'.$applicantIncomeFile->file_name.'';
              $url = ImageUpload::generatePreSignedUrl($foldeName);
            @endphp


      <span><a href="{{ $url }}" target="blank">{{ $applicantIncomeFile->file_name }}</a></span>
      <input type="hidden" name="hidden_applicant_income_file_id" id="hidden_applicant_income_file_id" value="{{ $applicantIncomeFile->id }}">
      @endforeach
    @else
      <input type="hidden" name="hidden_applicant_income_file_id" id="hidden_applicant_income_file_id" value="">
    @endif

  </div>
</div>
<? $address_proof_type = [
              0=>'pen card',
              1=>'dl',
              2=>'Voter ID',
              3=>'Passport',
              4=>'Identity card',
              5=>'Aadhar card',
              6=>'Bank passbook',
              7=>'Electricity Bill',
              8=>'Telephone bill',
          ];?>
<h5>Documents</h5>
<div class="form-group row">
  <label class="col-form-label col-lg-2">ID Proof<sup>*</sup></label>
  <div class="col-lg-12">
    <select name="applicant_id_proof" id="applicant_id_proof" class="form-control" title="Please select something!">
      <option value="">Select ID</option>
      @foreach($address_proof_type as $key => $val)
        <option data-val="applicant" @if($loanDetail->id_proof_type == $key) selected @endif value="{{$key}}">{{ucwords($val)}}</option>
      @endforeach
      {{--
      <option data-val="applicant" value="">Select ID</option>
      <option data-val="applicant" @if($loanDetail->id_proof_type == 1) selected @endif value="1">Voter ID</option>
      <option data-val="applicant" @if($loanDetail->id_proof_type == 2) selected @endif value="2">DL</option>
      <option data-val="applicant" @if($loanDetail->id_proof_type == 3) selected @endif value="3">Aadhar card</option>
      <option data-val="applicant" @if($loanDetail->id_proof_type == 4) selected @endif value="4">Passport</option>
      <option data-val="applicant" @if($loanDetail->id_proof_type == 5) selected @endif value="5">Pan Card</option>
      <option data-val="applicant" @if($loanDetail->id_proof_type == 6) selected @endif value="6">Other</option>
      <option data-val="applicant" @if($loanDetail->id_proof_type == 7) selected @endif value="7">Electricity Bill</option>
      --}}
    </select>
  </div>
</div>

<div class="form-group row">
  <label class="col-form-label col-lg-2">Enter Id number<sup>*</sup></label>
  <div class="col-lg-4">
    <input type="text" name="applicant_id_number" id="applicant_id_number" class="form-control" value="{{ $loanDetail->id_proof_number }}">
  </div>
  <div class="col-lg-1 ">
    <button type="button" id="applicant_id_tooltip"  class="btn btn-sm btn-default" data-toggle="tooltip" data-placement="top" title="Enter Id proof number."><i class="fas fa-exclamation-circle text-secondary"></i>
    </button>
  </div>

  <label class="col-form-label col-lg-2">Upload File</label>
  <div class="col-lg-3">
    <input type="file" name="applicant_id_file" id="applicant_id_file" class="form-control" value="{{ $loanDetail->id_proof_number }}">
    @php
      $applicantFiles = getFileData($loanDetail->id_proof_file_id);
    @endphp

    @if(count($applicantFiles) > 0)
      @foreach($applicantFiles as $applicantFile)

          @php
              $foldeName = 'loan/document/'.$loanDetails->id.'/applicant/id_proof/'.$applicantFile->file_name.'';
              $url = ImageUpload::generatePreSignedUrl($foldeName);
            @endphp

      <span><a href="{{ $url }}" target="blank">{{ $applicantFile->file_name }}</a></span>
      <input type="hidden" name="hidden_applicant_file_id" id="hidden_applicant_file_id" value="{{ $applicantFile->id }}">
      @endforeach
    @else
      <input type="hidden" name="hidden_applicant_file_id" id="hidden_applicant_file_id" value="">
    @endif
  </div>
</div>

<div class="form-group row">
  <label class="col-form-label col-lg-2">Address Proof<sup>*</sup></label>
  <div class="col-lg-12">
    
    <select name="applicant_address_id_proof" id="applicant_address_id_proof" class="form-control" title="Please select something!">
      <option data-val="applicant_address" value="">Select ID</option>
      @foreach($address_proof_type as $key => $val)
        <option data-val="applicant_address" @if($loanDetail->address_proof_type == $key) selected @endif value="{{$key}}">{{ucwords($val)}}</option>
      @endforeach
      {{--
      <option data-val="applicant_address" @if($loanDetail->address_proof_type == 1) selected @endif value="1">Voter ID</option>
      <option data-val="applicant_address" @if($loanDetail->address_proof_type == 2) selected @endif value="2">DL</option>
      <option data-val="applicant_address" @if($loanDetail->address_proof_type == 3) selected @endif value="3">Aadhar card</option>
      <option data-val="applicant_address" @if($loanDetail->address_proof_type == 4) selected @endif value="4">Passport</option>
      <option data-val="applicant_address" @if($loanDetail->address_proof_type == 5) selected @endif value="5">Pan Card</option>
      <option data-val="applicant_address" @if($loanDetail->address_proof_type == 6) selected @endif value="6">Other</option>
      <option data-val="applicant_address" @if($loanDetail->address_proof_type == 7) selected @endif value="7">Electricity Bill</option>
      --}}
    </select>
  </div>
</div>

<div class="form-group row">
  <label class="col-form-label col-lg-2">Enter Id number<sup>*</sup></label>
  <div class="col-lg-4">
    <input type="text" name="applicant_address_id_number" id="applicant_address_id_number" class="form-control" value="{{ $loanDetail->address_proof_id_number }}">
  </div>
  <div class="col-lg-1 ">
    <button type="button" id="applicant_address_id_tooltip"  class="btn btn-sm btn-default" data-toggle="tooltip" data-placement="top" title="Enter Id proof number."><i class="fas fa-exclamation-circle text-secondary"></i>
    </button>
  </div>

  <label class="col-form-label col-lg-2">Upload File</label>
  <div class="col-lg-3">
    <input type="file" name="applicant_address_id_file" id="applicant_address_id_file" class="form-control">
    @php
      $applicantAddressFiles = getFileData($loanDetail->address_proof_file_id);
    @endphp

    @if(count($applicantAddressFiles) > 0)
      @foreach($applicantAddressFiles as $applicantAddressFile)
            @php
              $foldeName = 'loan/document/'.$loanDetails->id.'/applicant/address_proof/'.$applicantAddressFile->file_name.'';
              $url = ImageUpload::generatePreSignedUrl($foldeName);
            @endphp

      <span><a href="{{ $url }}" target="blank">{{ $applicantAddressFile->file_name }}</a></span>
      <input type="hidden" name="hidden_applicant_address_file_id" id="hidden_applicant_address_file_id" value="{{ $applicantAddressFile->id }}">
      @endforeach
    @else
      <input type="hidden" name="hidden_applicant_address_file_id" id="hidden_applicant_address_file_id" value="">
    @endif
  </div>
</div>

<div class="form-group row">
  <label class="col-form-label col-lg-2">Income<sup>*</sup></label>
  <div class="col-lg-4">
    <select name="applicant_income" id="applicant_income" class="form-control" title="Please select something!">
      <option value="">Select Type</option>
      <option @if($loanDetail->income_type == 0) selected @endif data-val="applicant" value="0">Salary Slip</option>
      <option @if($loanDetail->income_type == 1) selected @endif data-val="applicant" value="1">ITR</option>
      <option @if($loanDetail->income_type == 2) selected @endif data-val="applicant" value="2">Others</option>
    </select>
  </div>

  
  <label class="col-form-label col-lg-2 applicant-salary-remark" @if($loanDetail->income_type != 2) style="display: none;" @endif>Remark<sup>*</sup></label>
  <div class="col-lg-4 applicant-salary-remark" @if($loanDetail->income_type != 2)  style="display: none;" @endif>
    <input type="text" name="remark" id="remark" class="form-control" value="{{ $loanDetail->income_remark }}">
  </div>
</div>
@endforeach