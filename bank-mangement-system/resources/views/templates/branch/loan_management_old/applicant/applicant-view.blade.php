@foreach($loanDetails['LoanApplicants'] as $LoanApplicant)
<div class="row">
    <div class="col-lg-12 ">
        <h3 class="mb-0 text-dark mb-0 text-dark bg-md-dark px-3 py-2 text-dark my-3">Applicant Details</h3>

        @php
            $aDetails = getMemberData($loanDetails->applicant_id);
        @endphp

        <div class="row">
            <label class=" col-lg-4">Applicant Id</label> 
            <div class="col-lg-1">:</div>
            <div class="col-lg-7"> {{ $aDetails->member_id }}</div>
        </div>

        <div class="row">
            <label class=" col-lg-4">Name</label> 
            <div class="col-lg-1">:</div>
            <div class="col-lg-7  "> {{ $aDetails->first_name }} {{ $aDetails->last_name }}</div>
        </div>

        <div class="row">
            <label class=" col-lg-4">Father's Name</label> 
            <div class="col-lg-1">:</div>
            <div class="col-lg-7  "> {{ $aDetails->father_husband }} </div>
        </div>

        <div class="row">
            <label class=" col-lg-4">Email Id</label> 
            <div class="col-lg-1">:</div>
            <div class="col-lg-7  "> {{ $aDetails->email }} </div>
        </div>

        <div class="row">
            <label class=" col-lg-4">Mobile Number</label> 
            <div class="col-lg-1">:</div>
            <div class="col-lg-7  "> {{ $aDetails->mobile_no }} </div>
        </div>

        @if($loanDetails->loan_type == 3)
            <div class="row">
                <label class=" col-lg-4">Member ID</label> 
                <div class="col-lg-1">:</div>
                <div class="col-lg-7  "> {{ getApplicantid($loanDetails->group_member_id) }} </div>
            </div>
        @endif
        <div class="row">
            <label class=" col-lg-4">Address permanent</label>
            <div class="col-lg-1">:</div>
            <div class="col-lg-7  ">  
                @if($LoanApplicant->address_permanent==0)
                Self
                @elseif($LoanApplicant->address_permanent==1)
                Perental
                @elseif($LoanApplicant->address_permanent==2)
                Rental
                @endif
            </div>
        </div>
        <div class="row">
            <label class=" col-lg-4">Temporary Address</label> 
            <div class="col-lg-1">:</div>
            <div class="col-lg-7  "> 
                @if($LoanApplicant->temporary_permanent==0)
                Self
                @elseif($LoanApplicant->temporary_permanent==1)
                Perental
                @elseif($LoanApplicant->temporary_permanent==2)
                Rental
                @endif
            </div>
        </div>
        <h5>Employment Details</h5>
        <div class="row">
            <label class=" col-lg-4">Occupation</label> 
            <div class="col-lg-1">:</div>
            <div class="col-lg-7  "> {{ getOccupationName($LoanApplicant->occupation) }} </div>
        </div>
        <div class="row">
            <label class=" col-lg-4">Organization</label> 
            <div class="col-lg-1">:</div>
            <div class="col-lg-7  "> {{ $LoanApplicant->organization }} </div>
        </div>
        <div class="row">
            <label class=" col-lg-4">Designation</label> 
            <div class="col-lg-1">:</div>
            <div class="col-lg-7  "> {{ $LoanApplicant->designation }} </div>
        </div>
        <div class="row">
            <label class=" col-lg-4">Monthly Income</label> 
            <div class="col-lg-1">:</div>
            <div class="col-lg-7  "> {{ $LoanApplicant->monthly_income }} ₹</div>
        </div>
        <div class="row">
            <label class=" col-lg-4">Year From</label> 
            <div class="col-lg-1">:</div>
            <div class="col-lg-7  "> {{ $LoanApplicant->year_from }} </div>
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
            <div class="col-lg-7  "> {{ $LoanApplicant->bank_name }}  </div>
        </div>
        <div class="row">
            <label class=" col-lg-4">Bank Account Number </label> 
            <div class="col-lg-1">:</div>
            <div class="col-lg-7  "> {{ $LoanApplicant->bank_account_number }}</div>
        </div>
        <div class="row">
            <label class=" col-lg-4"> IFSC Code</label> 
            <div class="col-lg-1">:</div>
            <div class="col-lg-7  "> {{ $LoanApplicant->ifsc_code }}  </div>
        </div>
        <div class="row">
            <label class=" col-lg-4">Cheque Number 1</label> 
            <div class="col-lg-1">:</div>
            <div class="col-lg-7  "> {{ $LoanApplicant->cheque_number_1 }} </div>
        </div>
        <div class="row">
            <label class=" col-lg-4">Cheque Number 2</label> 
            <div class="col-lg-1">:</div>
            <div class="col-lg-7  "> {{ $LoanApplicant->cheque_number_2 }} </div>
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
            @if($LoanApplicant->id_proof_type==0)
                Pen Card
            @elseif($LoanApplicant->id_proof_type==1)
                Aadhar Card
            @elseif($LoanApplicant->id_proof_type==2)
                DL
            @elseif($LoanApplicant->id_proof_type==3)
                Voter Id
            @elseif($LoanApplicant->id_proof_type==4)
                Passport
            @else
                Identity Card   
            @endif
            </div>
        </div>
        <div class="row">
            <label class=" col-lg-4">Id number</label> 
            <div class="col-lg-1">:</div>
            <div class="col-lg-7  "> {{ $LoanApplicant->id_proof_number }} </div>
        </div>
        <div class="row">
            @php
              $applicantFiles = getFileData($LoanApplicant->id_proof_file_id);
            @endphp
            <label class=" col-lg-4">Upload File</label> 
            <div class="col-lg-1">:</div>
            @if($applicantFiles)
                @foreach($applicantFiles as $applicantFile)
                <div class="col-lg-7  "><a href="{{ URL('core/storage/images/loan/document/'.$loanDetails->id.'/applicant/id_proof/'.$applicantFile->file_name.'') }}" target="blank">{{ $applicantFile->file_name }}</a></div>
                @endforeach
            @endif
        </div>
        <div class="row">
            <label class=" col-lg-4">Address Proof</label> 
            <div class="col-lg-1">:</div>
            <div class="col-lg-7  "> 
            @if($LoanApplicant->address_proof_type==0)
                Aadhar Card
            @elseif($LoanApplicant->address_proof_type==1)
                DL
            @elseif($LoanApplicant->address_proof_type==2)
                Voter Id
            @elseif($LoanApplicant->address_proof_type==3)
                Passport
            @elseif($LoanApplicant->address_proof_type==4)
                Identity Card   
            @elseif($LoanApplicant->address_proof_type==5)
                Bank Passbook  
            @elseif($LoanApplicant->address_proof_type==6)
                Electricity Bill
            @elseif($LoanApplicant->address_proof_type==7)
                Telephone Bill
            @endif
            </div>
        </div>
        <div class="row">
            <label class=" col-lg-4">Id number</label> 
            <div class="col-lg-1">:</div>
            <div class="col-lg-7  "> {{ $LoanApplicant->address_proof_id_number }} </div>
        </div>
        <div class="row">
            <label class=" col-lg-4">Upload File</label> 
            <div class="col-lg-1">:</div>
            @php
              $applicantAddressFiles = getFileData($LoanApplicant->address_proof_file_id);
            @endphp
            @if($applicantAddressFiles)
                @foreach($applicantAddressFiles as $applicantAddressFile)
                <div class="col-lg-7"><a href="{{ URL('core/storage/images/loan/document/'.$loanDetails->id.'/applicant/address_proof/'.$applicantAddressFile->file_name.'') }}" target="blank">{{ $applicantAddressFile->file_name }}</a></div>
                @endforeach
            @endif
        </div>
        <div class="row">
            <label class=" col-lg-4">Income</label> 
            <div class="col-lg-1">:</div>
            <div class="col-lg-7  "> 
                @if($LoanApplicant->income_type==0)
                    Salary Slip 
                @elseif($LoanApplicant->income_type==1)
                    ITR
                @elseif($LoanApplicant->income_type==2)
                    Others
                @endif               
            </div>
        </div>
        @if($LoanApplicant->income_remark)
        <div class="row">
            <label class=" col-lg-4">Remark</label> 
            <div class="col-lg-1">:</div>
            <div class="col-lg-7  "> {{ $LoanApplicant->income_remark }} </div>
        </div>
        @endif
        <div class="row">
            <label class=" col-lg-4">Upload File</label>
            <div class="col-lg-1">:</div>
            @php
              $applicantIncomeFiles = getFileData($LoanApplicant->income_file_id);
            @endphp
            @if($applicantIncomeFiles)
                @foreach($applicantIncomeFiles as $applicantIncomeFile) 
                <div class="col-lg-7"><a href="{{ URL('core/storage/images/loan/document/'.$loanDetails->id.'/applicant/income_proof/'.$applicantIncomeFile->file_name.'') }}" target="blank">{{ $applicantIncomeFile->file_name }}</a></div>
                @endforeach
            @endif
        </div>
        <div class="row">
            <label class=" col-lg-4">Security</label> 
            <div class="col-lg-1">:</div>
            <div class="col-lg-7  "> 
                @if($LoanApplicant->security==0)
                    Cheuqe
                @elseif($LoanApplicant->security==1)
                    Passbook
                @elseif($LoanApplicant->security==2)
                    FD Certificate
                @endif 
            </div>
        </div>
    </div>
</div> 
@endforeach 
