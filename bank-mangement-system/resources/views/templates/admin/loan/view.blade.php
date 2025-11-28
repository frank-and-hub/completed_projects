@extends('templates.admin.master')
@section('content')
<div class="content">
    <div class="row">
        <div class="col-lg-12">
            @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <span class="alert-text"><strong>Success!</strong> {{ session('success') }} </span>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            @endif
        </div>
        <div class="col-lg-12" id="print_recipt">
            <div class="card bg-white">
                <div class="card-body">
                    <h3 class="card-title mb-3 bg-md-dark px-3 py-2 text-dark my-3">{{$loanDetails['loan']->name}}</h3>
                    @if($loanDetails['loan']->loan_category == 3)
                    <div class="row">
                        <label class=" col-lg-4  ">Group Activity </label>
                        <div class="col-lg-1">:</div>
                        <div class="col-lg-4   ">
                            {{ getGroupLoanDetail($loanDetails->id)->group_activity }}
                        </div>
                    </div>
                    <div class="row">
                        <label class=" col-lg-4  ">Group leader Member ID </label>
                        <div class="col-lg-1">:</div>
                        <div class="col-lg-4   ">
                            {{-- {{p(getMemberDetails(getMemberDetails(getGroupLoanDetail($loanDetails->id)->groupleader_member_id)->customer_id)->member_id)}} --}}
                            {{ getMemberCompanyDataNew(getGroupLoanDetail($loanDetails->id)->groupleader_member_id) ? getMemberCompanyDataNew(getGroupLoanDetail($loanDetails->id)->groupleader_member_id)->member_id:''}}
                        </div>
                    </div>
                    <div class="row">
                        <label class=" col-lg-4  ">Select the number of Member </label>
                        <div class="col-lg-1">:</div>
                        <div class="col-lg-4   ">
                            {{ count($loanDetails['GroupLoanMembers']) }}
                        </div>
                    </div>
                    @endif
                    <div class="row">
                        <label class=" col-lg-4  ">Loan Amount </label>
                        <div class="col-lg-1">:</div>
                        <div class="col-lg-4   ">
                            {{ $loanDetails->amount }} ₹
                        </div>
                    </div>
                    <div class="row">
                        <label class=" col-lg-4  ">EMI Mode Option </label>
                        <div class="col-lg-1">:</div>
                        <?$emiOption = [
                                1=>'Months',
                                2=>'Weeks',
                                3=>'Days',
                            ];?>
                        <div class="col-lg-4   ">
                            {{--
                            @if($loanDetails->emi_option == 1)
                            {{ $loanDetails->emi_period }} Months
                            @elseif($loanDetails->emi_option == 2)
                            {{ $loanDetails->emi_period }} Weeks
                            @elseif($loanDetails->emi_option == 3)
                            {{ $loanDetails->emi_period }} Days
                            @endif
                            --}}
                            {{$emiOption[$loanDetails->emi_option] ? $loanDetails->emi_period . ' ' .ucwords($emiOption[$loanDetails->emi_option]):''}}
                        </div>
                    </div>
                    @if($loanDetails['loan']->loan_category != 3)
                    <div class="row">
                        <label class=" col-lg-4  ">Purpose for loan </label>
                        <div class="col-lg-1">:</div>
                        <div class="col-lg-7   ">
                            {{ $loanDetails->loan_purpose }}
                        </div>
                    </div>
                    @endif
                    <div class="row">
                        <label class=" col-lg-4  ">Associate Code </label>
                        <div class="col-lg-1">:</div>
                        <div class="col-lg-7   ">
                            {{ getAssociateNo($loanDetails->associate_member_id) }}
                        </div>
                    </div>
                    @if($loanDetails['loan']->loan_category != 3)
                    <!-- <div class="row">
                        <label class=" col-lg-4  ">Bank Account </label>
                        <div class="col-lg-1">:</div>
                        <div class="col-lg-7   ">
                            {{ $loanDetails->bank_account }}
                        </div>
                    </div>
                    <div class="row">
                        <label class=" col-lg-4  ">IFSC code </label>
                        <div class="col-lg-1">:</div>
                        <div class="col-lg-7   ">
                            {{ $loanDetails->ifsc_code }}
                        </div>
                    </div>
                    <div class="row">
                        <label class=" col-lg-4  ">Bank Name </label>
                        <div class="col-lg-1">:</div>
                        <div class="col-lg-7   ">
                            {{ $loanDetails->bank_name }}
                        </div>
                    </div> -->
                    <div class="row">
                        <label class=" col-lg-4  ">Customer Id </label>
                        <div class="col-lg-1">:</div>
                        <div class="col-lg-7">
                            {{$loanDetails->member->member_id}}
                        </div>
                    </div>
                    <div class="row">
                        <label class=" col-lg-4  ">Applicant Id </label>
                        <div class="col-lg-1">:</div>
                        <div class="col-lg-7   ">
                            {{$loanDetails->loanMemberCompany->member_id}}
                        </div>
                    </div>
                    @endif
                    <br>
                    @if($loanDetails['loan']->loan_category == 3)
                    <h3>Group Members Detail</h3>
                    <div class="table-responsive">
                        <table class="table table-flush" style="width: 100%">
                            <thead class="">
                                <tr>
                                    <th>Member ID</th>
                                    <th>Customer ID</th>
                                    <th>Member Name</th>
                                    <th>Father Name</th>
                                    <th>Amount</th>
                                    <th>Bank Name</th>
                                    <th>SSB Account</th>
                                    <th>IFSC Code</th>
                                </tr>
                            </thead>
                            @foreach($loanDetails['GroupLoanMembers'] as $groupLoanMember)
                            @php
                             $mDetails = getMemberCompanyDataNew($groupLoanMember->customer_id);
                             $url = URL::to('admin/member-detail/' . $groupLoanMember->customer_id . '?type=0');
                             @endphp
                            <tr>
                                <td>{{ $mDetails->member->memberCompany->member_id }}</td>
                                <!-- <td>{{ $mDetails->member->member_id }}</td>
                             -->
                             <td><a href="{{$url}}" target="_blank">{{ $mDetails->member->member_id }}</a></td>
                                <td>{{ $mDetails->member->first_name }} {{ $mDetails->member->last_name }}</td>
                                <td>{{ $mDetails->member->father_husband }}</td>
                                <td>{{ $groupLoanMember->amount }}</td>
                                @if(isset($mDetails->member['memberBankDetails']) && count($mDetails->member['memberBankDetails']) > 0)
                                <td>{{ $mDetails->member['memberBankDetails'][0]->bank_name }}</td>
                                <td>{{ $mDetails->member['memberBankDetails'][0]->account_no }}</td>
                                <td>{{ $mDetails->member['memberBankDetails'][0]->ifsc_code }}</td>
                                @else
                                <td></td>
                                <td></td>
                                <td></td>
                                @endif
                            </tr>
                            @endforeach
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                    @endif
                    @if($loanDetails['loan']->loan_category == 4)
                    <h3>Applicant's Deposite Detail</h3>
                    <div class="table-responsive">
                        <table class="table table-flush" style="width: 100%">
                            <thead class="">
                                <tr>
                                    <th>Scheme</th>
                                    <th>Account ID</th>
                                    <th>Open Date</th>
                                    <th>Maturity Date</th>
                                    <th>Deposit</th>
                                    <th>Tenure</th>
                                    <th>Loan Amount</th>
                                </tr>
                            </thead>
                            @foreach($loanDetails['loanInvestmentPlans'] as $loanInvestmentPlan)
                            @php
                            $investmentDetails = getMemberInvestmentDetailById($loanInvestmentPlan->plan_id)
                            @endphp
                            <tr>
                                <td>{{ $investmentDetails->name }}</td>
                                <td>{{ $investmentDetails->account_number }}</td>
                                <td>{{ date('d/m/Y', strtotime($investmentDetails->created_at)) }}</td>
                                @php
                                $dueDate = getDueDate($investmentDetails->created_at,$investmentDetails->tenure)
                                @endphp
                                <td>{{ $dueDate }}</td>
                                <td>{{ $investmentDetails->current_balance }}</td>
                                <td>{{ $investmentDetails->tenure*12 }}</td>
                                <td>{{ $loanInvestmentPlan->amount }}</td>
                            </tr>
                            @endforeach
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                    @endif
                    @include('templates.admin.loan.applicant.applicant-view')
                    @if($loanDetails->loan_type !=3 OR $loanDetails->loan_type !=4)
                    @include('templates.admin.loan.coapplicant.co-applicant-view')
                    @endif
                    @if($loanDetails->loan_type !=4)
                    @include('templates.admin.loan.guarantor.guarantor-view')
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@include('templates.admin.loan.partials.script')
@stop