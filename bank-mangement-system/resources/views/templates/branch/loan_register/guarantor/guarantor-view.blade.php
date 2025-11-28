@foreach($loanDetails['LoanGuarantor'] as $LoanGuarantor)
<div class="row">
    <div class="col-lg-12 ">
        <h3 class="mb-0 text-dark mb-0 text-dark bg-md-dark px-3 py-2 text-dark my-3">Guarantor Details</h3>

        @php
            $gDetails = getMemberData($LoanGuarantor->member_id);
        @endphp

        <div class="row">
            <label class=" col-lg-4">Guarantor Id</label> 
            <div class="col-lg-1">:</div>
            <div class="col-lg-7  "> 
                @if($gDetails)
                    {{ $gDetails->member_id }}
                @else
                    N/A
                @endif
            </div>
        </div>

        <div class="row">
            <label class=" col-lg-4">Name</label> 
            <div class="col-lg-1">:</div>
            <div class="col-lg-7  "> {{ $LoanGuarantor->name }}</div>
        </div>

        <div class="row">
            <label class=" col-lg-4">Father's Name</label> 
            <div class="col-lg-1">:</div>
            <div class="col-lg-7  "> {{ $LoanGuarantor->father_name }} </div>
        </div>

        <div class="row">
            <label class=" col-lg-4">Email Id</label> 
            <div class="col-lg-1">:</div>
            <div class="col-lg-7  "> 
                @if($gDetails)
                    {{ $gDetails->email }}
                @else
                    N/A
                @endif
            </div>
        </div>

        <div class="row">
            <label class=" col-lg-4">DOB</label> 
            <div class="col-lg-1">:</div>
            <div class="col-lg-7  "> {{ date("m/d/Y", strtotime(convertDate($LoanGuarantor->dob))) }} </div>
        </div>

        <div class="row">
            <label class=" col-lg-4">Address permanent</label> 
            <div class="col-lg-1">:</div>
            <div class="col-lg-7  "> 
                @if($LoanGuarantor->address_permanent==0)
                Self
                @elseif($LoanGuarantor->address_permanent==1)
                Perental
                @elseif($LoanGuarantor->address_permanent==2)
                Rental
                @endif 
            </div>
        </div>
        <div class="row">
            <label class=" col-lg-4">Temporary Address</label> 
            <div class="col-lg-1">:</div>
            <div class="col-lg-7  "> 
                @if($LoanGuarantor->temporary_permanent==0)
                Self
                @elseif($LoanGuarantor->temporary_permanent==1)
                Perental
                @elseif($LoanGuarantor->temporary_permanent==2)
                Rental
                @endif 
            </div>
        </div>
        <h5>Employment Details</h5>
        <div class="row">
            <label class=" col-lg-4">Occupation</label> 
            <div class="col-lg-1">:</div>
            <div class="col-lg-7  "> {{ getOccupationName($LoanGuarantor->occupation) }} </div>
        </div>
        <div class="row">
            <label class=" col-lg-4">Organization</label> 
            <div class="col-lg-1">:</div>
            <div class="col-lg-7  "> {{ $LoanGuarantor->organization }} </div>
        </div>
        <div class="row">
            <label class=" col-lg-4">Designation</label> 
            <div class="col-lg-1">:</div>
            <div class="col-lg-7  "> {{ $LoanGuarantor->designation }} </div>
        </div>
        <div class="row">
            <label class=" col-lg-4">Monthly Income</label> 
            <div class="col-lg-1">:</div>
            <div class="col-lg-7  "> {{ $LoanGuarantor->monthly_income }} ₹</div>
        </div>
        <div class="row">
            <label class=" col-lg-4">Year From</label> 
            <div class="col-lg-1">:</div>
            <div class="col-lg-7  "> {{ $LoanGuarantor->year_from }} </div>
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
            <div class="col-lg-7  "> {{ $LoanGuarantor->bank_name }}  </div>
        </div>
        <div class="row">
            <label class=" col-lg-4">Bank Account Number </label> 
            <div class="col-lg-1">:</div>
            <div class="col-lg-7  "> {{ $LoanGuarantor->bank_account_number }}</div>
        </div>
        <div class="row">
            <label class=" col-lg-4"> IFSC Code</label> 
            <div class="col-lg-1">:</div>
            <div class="col-lg-7  "> {{ $LoanGuarantor->ifsc_code }}  </div>
        </div>
        <div class="row">
            <label class=" col-lg-4">Cheque Number 1</label> 
            <div class="col-lg-1">:</div>
            <div class="col-lg-7  "> {{ $LoanGuarantor->cheque_number_1 }} </div>
        </div>
        <div class="row">
            <label class=" col-lg-4">Cheque Number 2</label> 
            <div class="col-lg-1">:</div>
            <div class="col-lg-7  "> {{ $LoanGuarantor->cheque_number_2 }} </div>
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
                @if($LoanGuarantor->id_proof_type==0)
                    Pen Card
                @elseif($LoanGuarantor->id_proof_type==1)
                    Aadhar Card
                @elseif($LoanGuarantor->id_proof_type==2)
                    DL
                @elseif($LoanGuarantor->id_proof_type==3)
                    Voter Id
                @elseif($LoanGuarantor->id_proof_type==4)
                    Passport
                @else
                    Identity Card   
                @endif 
            </div>
        </div>
        <div class="row">
            <label class=" col-lg-4">Id number</label> 
            <div class="col-lg-1">:</div>
            <div class="col-lg-7  "> {{ $LoanGuarantor->id_proof_number }} </div>
        </div>
        <div class="row">
            @php
            $applicantFiles = getFileData($LoanGuarantor->id_proof_file_id);
            @endphp
            <label class=" col-lg-4">Security cheque/Stamp</label> 
            <div class="col-lg-1">:</div>
            @if($applicantFiles)
                @foreach($applicantFiles as $applicantFile)
                <div class="col-lg-7  "> <a href="{{ URL('core/storage/images/loan/document/'.$loanDetails->id.'/guarantor/id_proof/'.$applicantFile->file_name.'') }}" target="blank">{{ $applicantFile->file_name }}</a> </div>
                @endforeach
            @endif
        </div>
        <div class="row">
            <label class=" col-lg-4">Address Proof</label> 
            <div class="col-lg-1">:</div>
            <div class="col-lg-7  "> 
                @if($LoanGuarantor->address_proof_type==0)
                    Aadhar Card
                @elseif($LoanGuarantor->address_proof_type==1)
                    DL
                @elseif($LoanGuarantor->address_proof_type==2)
                    Voter Id
                @elseif($LoanGuarantor->address_proof_type==3)
                    Passport
                @elseif($LoanGuarantor->address_proof_type==4)
                    Identity Card   
                @elseif($LoanGuarantor->address_proof_type==5)
                    Bank Passbook  
                @elseif($LoanGuarantor->address_proof_type==6)
                    Electricity Bill
                @elseif($LoanGuarantor->address_proof_type==7)
                    Telephone Bill
                @endif 
            </div>
        </div>
        <div class="row">
            <label class=" col-lg-4">Id number</label> 
            <div class="col-lg-1">:</div>
            <div class="col-lg-7  "> {{ $LoanGuarantor->address_proof_id_number }} </div>
        </div>
        <div class="row">
            <label class=" col-lg-4">Upload File</label> 
            <div class="col-lg-1">:</div>
            @php
            $applicantAddressFiles = getFileData($LoanGuarantor->address_proof_file_id);
            @endphp
            @if($applicantAddressFiles)
                @foreach($applicantAddressFiles as $applicantAddressFile)
                <div class="col-lg-7"><a href="{{ URL('core/storage/images/loan/document/'.$loanDetails->id.'/guarantor/address_proof/'.$applicantAddressFile->file_name.'') }}" target="blank">{{ $applicantAddressFile->file_name }}</a></div>
                @endforeach
            @endif
        </div>
        <div class="row">
            <label class=" col-lg-4">Under taking Doc</label> 
            <div class="col-lg-1">:</div>
            @php
            $gUnderDocFiles = getFileData($LoanGuarantor->under_taking_doc);
            @endphp
            @if($gUnderDocFiles)
                @foreach($gUnderDocFiles as $gUnderDocFile)
                <div class="col-lg-7"><a href="{{ URL('core/storage/images/loan/document/'.$loanDetails->id.'/guarantor/undertakingdoc/'.$gUnderDocFile->file_name.'') }}" target="blank">{{ $gUnderDocFile->file_name }}</a></div>
                @endforeach
            @endif
        </div>
        <div class="row">
            <label class=" col-lg-4">Income</label> 
            <div class="col-lg-1">:</div>
            <div class="col-lg-7  "> 
                @if($LoanGuarantor->income_type==0)
                    Salary Slip 
                @elseif($LoanGuarantor->income_type==1)
                    ITR
                @elseif($LoanGuarantor->income_type==2)
                    Others
                @endif
            </div>
        </div>
        @if($LoanGuarantor->income_remark)
        <div class="row">
            <label class=" col-lg-4">Remark</label> 
            <div class="col-lg-1">:</div>
            <div class="col-lg-7  "> {{ $LoanGuarantor->income_remark }} </div>
        </div>
        @endif
        <div class="row">
            <label class=" col-lg-4">Upload File</label>
            <div class="col-lg-1">:</div>
            @php
            $applicantIncomeFiles = getFileData($LoanGuarantor->income_file_id);
            @endphp
            @if($applicantIncomeFiles)
                @foreach($applicantIncomeFiles as $applicantIncomeFile) 
                <div class="col-lg-7"><a href="{{ URL('core/storage/images/loan/document/'.$loanDetails->id.'/guarantor/income_proof/'.$applicantIncomeFile->file_name.'') }}" target="blank">{{ $applicantIncomeFile->file_name }}</a></div>
                @endforeach
            @endif
        </div>
        <div class="row">
            <label class=" col-lg-4">Security</label> 
            <div class="col-lg-1">:</div>
            <div class="col-lg-7  "> 
                @if($LoanGuarantor->security==0)
                    Cheuqe
                @elseif($LoanGuarantor->security==1)
                    Passbook
                @elseif($LoanGuarantor->security==2)
                    FD Certificate
                @endif 
            </div>
        </div>
    </div>
    <div class="col-lg-2 ">
    </div>
    @if ( count( $loanDetails['Loanotherdocs'] ) > 0)
    <div class="col-lg-12 ">
        <h5>Other Documents</h3>
        @foreach($loanDetails['Loanotherdocs'] as $loanotherdocs)
            <div class="row">
                <label class=" col-lg-4">Title </label> 
                <div class="col-lg-1">:</div>
                <div class="col-lg-7  "> {{ $loanotherdocs['title'] }}  </div>
            </div> 
            <div class="row">
                <label class=" col-lg-4">Upload File</label> 
                <div class="col-lg-1">:</div>
                @php
                $files = getFileData($loanotherdocs['file_id']);
                @endphp
                @if($files)
                    @foreach($files as $file)
                        <div class="col-lg-7  "> <a href="{{ URL('core/storage/images/loan/document/'.$loanDetails->id.'/guarantor/moredocument/'.$file->file_name.'') }}" target="blank">{{ $file->file_name }}</a> </div>
                    @endforeach
                @endif
            </div>
        @endforeach
    </div>
    @endif
</div>
@endforeach  