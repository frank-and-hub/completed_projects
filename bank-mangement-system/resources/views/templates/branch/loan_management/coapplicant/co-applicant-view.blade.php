
@foreach($loanDetails['LoanCoApplicants'] as $LoanCoApplicant)
<div class="row">
<div class="col-lg-12 ">
<h3 class="mb-0 text-dark mb-0 text-dark bg-md-dark px-3 py-2 text-dark my-3">Co-applicant Details</h3>

		@php
            $caDetails = getMemberCompanyDataNew($LoanCoApplicant->member_id,getDefaultCompanyId()->id);
        @endphp

		<div class="row">
			<label class=" col-lg-4">Customer Id</label>
			<div class="col-lg-1">:</div> 
			<div class="col-lg-7  "> {{ $caDetails->member->member_id }}</div>
		</div>

        <div class="row">
            <label class=" col-lg-4">Applicant Id</label>
            <div class="col-lg-1">:</div> 
            <div class="col-lg-7  "> {{ $caDetails->member_id }}</div>
        </div>

        <div class="row">
            <label class=" col-lg-4">Name</label>
            <div class="col-lg-1">:</div>
            <div class="col-lg-7  "> {{ $caDetails->member->first_name }} {{ $caDetails->member->last_name }}</div>
        </div>

        <div class="row">
            <label class=" col-lg-4">Father's Name</label> 
            <div class="col-lg-1">:</div>
            <div class="col-lg-7  "> {{ $caDetails->member->father_husband }} </div>
        </div>

        <div class="row">
            <label class=" col-lg-4">Email Id</label> 
            <div class="col-lg-1">:</div>
            <div class="col-lg-7  "> {{ $caDetails->member->email }} </div>
        </div>

        <div class="row">
            <label class=" col-lg-4">Mobile Number</label> 
            <div class="col-lg-1">:</div>
            <div class="col-lg-7  "> {{ $caDetails->member->mobile_no }} </div>
        </div>

<div class="row">
<label class=" col-lg-4">Address permanent</label> 
<div class="col-lg-1">:</div>
<div class="col-lg-7  "> 
@if($LoanCoApplicant->address_permanent==0)
Self
@elseif($LoanCoApplicant->address_permanent==1)
Perental
@elseif($LoanCoApplicant->address_permanent==2)
Rental
@endif
</div>
</div>
<div class="row">
<label class=" col-lg-4">Temporary Address</label> 
<div class="col-lg-1">:</div>
<div class="col-lg-7  ">
@if($LoanCoApplicant->temporary_permanent==0)
Self
@elseif($LoanCoApplicant->temporary_permanent==1)
Perental
@elseif($LoanCoApplicant->temporary_permanent==2)
Rental
@endif  
</div>
</div>
<h5>Employment Details</h5>
<div class="row">
<label class=" col-lg-4">Occupation</label> 
<div class="col-lg-1">:</div>
<div class="col-lg-7  "> {{ getOccupationName($LoanCoApplicant->occupation) }} </div>
</div>
<div class="row">
<label class=" col-lg-4">Organization</label> 
<div class="col-lg-1">:</div>
<div class="col-lg-7  "> {{ $LoanCoApplicant->organization }} </div>
</div>
<div class="row">
<label class=" col-lg-4">Designation</label> 
<div class="col-lg-1">:</div>
<div class="col-lg-7  "> {{ $LoanCoApplicant->designation }} </div>
</div>
<div class="row">
<label class=" col-lg-4">Monthly Income</label> 
<div class="col-lg-1">:</div>
<div class="col-lg-7  "> {{ $LoanCoApplicant->monthly_income }} ₹</div>
</div>
<div class="row">
<label class=" col-lg-4">Year From</label> 
<div class="col-lg-1">:</div>
<div class="col-lg-7  "> {{ $LoanCoApplicant->year_from }} </div>
</div>
</div>
<div class="col-lg-2 ">
</div>
</div>

<div class="row">
	<div class="col-lg-12 ">
	<h5>Bank details</h3>
	<div class="row">
	<label class=" col-lg-4">Bank Name </label> 
	<div class="col-lg-1">:</div>
	<div class="col-lg-7  "> {{ $LoanCoApplicant->bank_name }}  </div>
	</div>
	<div class="row">
	<label class=" col-lg-4">Bank Account Number </label> 
	<div class="col-lg-1">:</div>
	<div class="col-lg-7  "> {{ $LoanCoApplicant->bank_account_number }}</div>
	</div>
	<div class="row">
	<label class=" col-lg-4"> IFSC Code</label> 
	<div class="col-lg-1">:</div>
	<div class="col-lg-7  "> {{ $LoanCoApplicant->ifsc_code }}  </div>
	</div>
	<div class="row">
	<label class=" col-lg-4">Cheque Number 1</label> 
	<div class="col-lg-1">:</div>
	<div class="col-lg-7  "> {{ $LoanCoApplicant->cheque_number_1 }} </div>
	</div>
	<div class="row">
	<label class=" col-lg-4">Cheque Number 2</label> 
	<div class="col-lg-1">:</div>
	<div class="col-lg-7  "> {{ $LoanCoApplicant->cheque_number_2 }} </div>
	</div> 
	</div>
</div>

<div class="row">
<div class="col-lg-12 ">
<h5>Documents</h3>
<div class="row">                                   
<label class=" col-lg-4">ID Proof</label> 
<div class="col-lg-1">:</div>
<div class="col-lg-7  "> 
	@if($LoanCoApplicant->id_proof_type==0)
        Pen Card
    @elseif($LoanCoApplicant->id_proof_type==1)
        Aadhar Card
    @elseif($LoanCoApplicant->id_proof_type==2)
        DL
    @elseif($LoanCoApplicant->id_proof_type==3)
        Voter Id
    @elseif($LoanCoApplicant->id_proof_type==4)
        Passport
    @else
        Identity Card   
    @endif 
</div>
</div>
<div class="row">
<label class=" col-lg-4">Id number</label> 
<div class="col-lg-1">:</div>
<div class="col-lg-7  "> {{ $LoanCoApplicant->id_proof_number }} </div>
</div>
<div class="row">
@php
$applicantFiles = getFileData($LoanCoApplicant->id_proof_file_id);
@endphp
<label class=" col-lg-4">Security cheque/Stamp</label> 
<div class="col-lg-1">:</div>
@foreach($applicantFiles as $applicantFile)
	
			@php
				$foldeName = 'loan/document/'.$loanDetails->id.'/coapplicant/id_proof/'.$applicantFile->file_name.'';
				$url = ImageUpload::generatePreSignedUrl($foldeName);
			@endphp
<div class="col-lg-7  "> <a href="{{ $url }}" target="blank">{{ $applicantFile->file_name }}</a> </div>
@endforeach
</div>
<div class="row">
<label class=" col-lg-4">Address Proof</label> 
<div class="col-lg-1">:</div>
<div class="col-lg-7  "> 
	@if($LoanCoApplicant->address_proof_type==0)
	    Aadhar Card
	@elseif($LoanCoApplicant->address_proof_type==1)
	    DL
	@elseif($LoanCoApplicant->address_proof_type==2)
	    Voter Id
	@elseif($LoanCoApplicant->address_proof_type==3)
	    Passport
	@elseif($LoanCoApplicant->address_proof_type==4)
	    Identity Card   
	@elseif($LoanCoApplicant->address_proof_type==5)
	    Bank Passbook  
	@elseif($LoanCoApplicant->address_proof_type==6)
	    Electricity Bill
	@elseif($LoanCoApplicant->address_proof_type==7)
	    Telephone Bill
	@endif 
</div>
</div>
<div class="row">
<label class=" col-lg-4">Id number</label> 
<div class="col-lg-1">:</div>
<div class="col-lg-7  "> {{ $LoanCoApplicant->address_proof_id_number }} </div>
</div>
<div class="row">
<label class=" col-lg-4">Upload File</label> 
<div class="col-lg-1">:</div>
@php
$applicantAddressFiles = getFileData($LoanCoApplicant->address_proof_file_id);
@endphp
@if($applicantAddressFiles)
	@foreach($applicantAddressFiles as $applicantAddressFile)
	@php
		$foldeName = 'loan/document/'.$loanDetails->id.'/coapplicant/address_proof/'.$applicantAddressFile->file_name.'';
		$url = ImageUpload::generatePreSignedUrl($foldeName);
	@endphp
	<div class="col-lg-7"><a href="{{ $url }}" target="blank">{{ $applicantAddressFile->file_name }}</a></div>
	@endforeach
@endif
</div>

<div class="row">
	<label class=" col-lg-4">Under taking Doc</label> 
	<div class="col-lg-1">:</div>
	@php
	$coapplicantUnderDocFiles = getFileData($LoanCoApplicant->under_taking_doc);
	@endphp
	@if($coapplicantUnderDocFiles)
		@foreach($coapplicantUnderDocFiles as $coapplicantUnderDocFile)	
		@php
			$foldeName = 'loan/document/'.$loanDetails->id.'/coapplicant/undertakingdoc/'.$coapplicantUnderDocFile->file_name.'';
			$url = ImageUpload::generatePreSignedUrl($foldeName);
		@endphp


		<div class="col-lg-7"><a href="{{ $url }}" target="blank">{{ $coapplicantUnderDocFile->file_name }}</a></div>
		@endforeach
	@endif
</div>

<div class="row">
<label class=" col-lg-4">Income</label> 
<div class="col-lg-1">:</div>
<div class="col-lg-7  "> 
	@if($LoanCoApplicant->income_type==0)
        Salary Slip 
    @elseif($LoanCoApplicant->income_type==1)
        ITR
    @elseif($LoanCoApplicant->income_type==2)
        Others
    @endif
</div>
</div>
@if($LoanCoApplicant->income_remark)
<div class="row">
<label class=" col-lg-4">Remark</label> 
<div class="col-lg-1">:</div>
<div class="col-lg-7  "> {{ $LoanCoApplicant->income_remark }} </div>
</div>
@endif
<div class="row">
<label class=" col-lg-4">Upload File</label>
<div class="col-lg-1">:</div>
@php
$applicantIncomeFiles = getFileData($LoanCoApplicant->income_file_id);
@endphp
@if($applicantIncomeFiles)
	@foreach($applicantIncomeFiles as $applicantIncomeFile) 
		@php
			$foldeName = 'loan/document/'.$loanDetails->id.'/coapplicant/income_proof/'.$applicantIncomeFile->file_name.'';
			$url = ImageUpload::generatePreSignedUrl($foldeName);
		@endphp

	<div class="col-lg-7"><a href="{{ $url }}" target="blank">{{ $applicantIncomeFile->file_name }}</a></div>
	@endforeach
@endif
</div>
<div class="row">
<label class=" col-lg-4">Security</label> 
<div class="col-lg-1">:</div>
<div class="col-lg-7  "> 
	@if($LoanCoApplicant->security==0)
        Cheuqe
    @elseif($LoanCoApplicant->security==1)
        Passbook
    @elseif($LoanCoApplicant->security==2)
        FD Certificate
    @endif 
</div>
</div>
</div>
</div>  
@endforeach

                    