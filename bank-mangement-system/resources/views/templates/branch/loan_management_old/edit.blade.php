@extends('layouts/branch.dashboard')

@section('content')

<div class="loader" style="display: none;"></div>

<div class="container-fluid mt--6">
  <div class="content-wrapper">
    <div class="row">
      <div class="col-lg-12">
        <div class="card bg-white">
          <div class="card-body">
            <div class="">
              <h3 class="">{{$title}}</h3>
                <a href="{!! route('loan.loans') !!}" style="float:right" class="btn btn-secondary">Back</a>
            </div>
            <!-- Validate error messages -->
              @if ($errors->any())
                  <div class="alert alert-danger">
                      <ul>
                          @foreach ($errors->all() as $error)
                              <li>{{ $error }}</li>
                          @endforeach
                      </ul>
                  </div>
              @endif
            <!-- Validate error messages -->  
          </div>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-md-12">
        <!-- Basic layout-->
        <div class="card">
          <div class="card-header header-elements-inline">
            <h3 class="mb-0">Edit</h3>
                <div class="header-elements">
                  <div class="list-icons">
                </div>
              </div>
          </div>

          <div class="card-body">

            <div class="form-group row select-loan">
              <label class="col-form-label col-lg-2">Loans<sup>*</sup></label>
              <div class="col-lg-12">
                <select name="loan" id="loan" class="form-control" title="Please select something!" disabled="">
                  <option value="">Select Loan</option>
                  @foreach($loans as $loan)
                    @if($loan->name=='Personal loan')
                      <option  @if($loanDetails['loan']->id == $loan->id) selected @endif data-val="{{ $loan->slug }}" value="{{ $loan->id }}">{{ $loan->name }}(PL)</option>
                    @elseif($loan->name=='Staff Loan')
                      <option  @if($loanDetails['loan']->id == $loan->id) selected @endif data-val="{{ $loan->slug }}" value="{{ $loan->id }}">{{ $loan->name }}(SL)</option>
                    @elseif($loan->name=='Group Loan')
                      <option  @if($loanDetails['loan']->id == $loan->id) selected @endif data-val="{{ $loan->slug }}" value="{{ $loan->id }}">{{ $loan->name }}(GL)</option>
                    @elseif($loan->name=='Loan against Investment plan')
                      <option  @if($loanDetails['loan']->id == $loan->id) selected @endif data-val="{{ $loan->slug }}" value="{{ $loan->id }}">{{ $loan->name }}(DL)</option>
                    @endif
                  @endforeach
                </select>
              </div>
            </div>

            <form action="{{route('loan.update')}}" method="post" id="register-plan" name="register-plan" enctype="multipart/form-data">
            @csrf

                <!-- <input type="hidden" name="loanId" id="loanId" value="{{ $loanDetails->id }}">
                <input type="hidden" name="loan" id="loan" value="{{ $loanDetails->loan_id }}"> -->
                <input type="hidden" name="edit_reject_request" class="edit_reject_request">
                <input type="hidden" name="loan_type_slug" id="loan_type_slug" value="{{ getLoanData($loanDetails->loan_type)->slug }}">
                <input type="hidden" name="loan_type" id="loan_type" value="{{ $loanDetails->loan_type }}">
                <input type="hidden" name="loan" id="loan" value="{{ $loanDetails->loan_type }}">
                <input type="hidden" name="loanId" class="loanId" value="{{ $loanDetails->id }}">

                <input type="hidden" name="created_date" class="created_date" value="{{ $loanDetails->created_at }}">

                <!-------------------- personal loan ------------------------>
                <div class="form-group row personal-loan-section">
                  <label class="col-form-label col-lg-2">Loan Amount<sup>*</sup></label>
                  <div class="col-lg-4">
                    <input type="text" name="amount" id="amount" class="form-control c-amount" value="{{ $loanDetails->amount }}" readonly="">
                    <label id="loan-amount-error"></label>
                  </div>

                  <label class="col-form-label col-lg-2">EMI Mode Option<sup>*</sup></label>
                  <div class="col-lg-4">
                    <select name="emoption" id="emoption" class="form-control" title="Please select something!" disabled="">
                      <option @if($loanDetails->emi_option == 1 && $loanDetails->emi_period == 10) selected @endif class="staff-emi-mode" data-val="months" value="10">10 Months</option>
                      <option class="personal-emi-mode investmentloan-emi-mode" data-val="months" value="12">12 Months</option>
                      <option @if($loanDetails->emi_option == 2 && $loanDetails->emi_period == 12) selected @endif class="group-emi-mode" data-val="weeks" value="12">12 Weeks</option>
                      <option @if($loanDetails->emi_option == 2 && $loanDetails->emi_period == 24) selected @endif class="personal-emi-mode group-emi-mode" data-val="weeks" value="24">24 Weeks</option>
                      <option @if($loanDetails->emi_option == 2 && $loanDetails->emi_period == 26) selected @endif class="personal-emi-mode group-emi-mode" data-val="weeks" value="26">26 Weeks</option>
                      <option @if($loanDetails->emi_option == 2 && $loanDetails->emi_period == 52) selected @endif class="personal-emi-mode group-emi-mode" data-val="weeks" value="52">52 Weeks</option>
                      <option @if($loanDetails->emi_option == 3 && $loanDetails->emi_period == 100) selected @endif class="personal-emi-mode group-emi-mode" data-val="days" value="100">100 Days</option>
                      <option @if($loanDetails->emi_option == 3 && $loanDetails->emi_period == 200) selected @endif class="personal-emi-mode group-emi-mode" data-val="days" value="200">200 Days</option>
                    </select>
                    <input type="hidden" name="emi_mode_option" value="{{ $loanDetails['loan']->emi_option }}">
                  </div>
                </div>

                <div class="form-group row personal-loan-section">
                  <label class="col-form-label col-lg-2">Purpose for loan<sup>*</sup></label>
                  <div class="col-lg-4">
                    <input type="text" name="purpose" id="purpose" class="form-control" value="{{ $loanDetails->loan_purpose }}" readonly="">
                  </div>

                  <label class="col-form-label col-lg-2">Associate Code<sup>*</sup></label>
                  <div class="col-lg-4">
                    <input type="text" data-val="associate" name="acc_auto_member_id" id="acc_auto_member_id" class="form-control" value="{{ getMemberData($loanDetails->associate_member_id)->associate_no }}" autocomplete="on" readonly="">
                    <input type="hidden" data-val="associate" name="acc_member_id" id="acc_member_id" value="{{ $loanDetails->associate_member_id }}" class="form-control">
                  </div>
                </div>

                <div class="form-group row personal-loan-section">                
                  <label class="col-form-label col-lg-2">Bank Account</label>
                  <div class="col-lg-4">
                    <input type="text" name="bank_account" id="bank_account" class="form-control associate-bank-account" readonly="" value="{{ $loanDetails->bank_account }}">
                  </div>
                  <label class="col-form-label col-lg-2">IFSC code </label>
                  <div class="col-lg-4">
                    <input type="text" name="ifsc_code" id="ifsc_code" value="{{ $loanDetails->ifsc_code }}" class="form-control associate-ifsc-code" readonly="">
                  </div>
                </div>

                <div class="form-group row personal-loan-section">
                  <label class="col-form-label col-lg-2">Bank Name </label>
                  <div class="col-lg-4">
                    <input type="text" name="bank_name" id="bank_name" class="form-control associate-bank-name" readonly="" value="{{ $loanDetails->bank_name }}">
                  </div>
                </div>
                <!-------------------- personal loan ------------------------>

                <!---------- Group Loan ------------------->
                @if(count($loanDetails['GroupLoanMembers']) > 0)
                <div class="form-group row group-information" style="display: none;">
                  <label class="col-form-label col-lg-2">Group Activity<sup>*</sup></label>
                  <div class="col-lg-4">
                    <input type="text" name="group_activity" id="group_activity" class="form-control" value="@if(count($loanDetails['GroupLoanMembers']) > 0) {{ $loanDetails['GroupLoanMembers'][0]->group_activity }} @endif" readonly="">
                  </div>

                  <label class="col-form-label col-lg-2">Group leader Member ID<sup>*</sup></label>
                  <div class="col-lg-4">
                    <input type="text" name="group_leader_member_id" id="group_leader_member_id" class="form-control" readonly="" value="@if(count($loanDetails['GroupLoanMembers']) > 0) {{ getApplicantid($loanDetails['GroupLoanMembers'][0]->groupleader_member_id) }} @endif" readonly="">
                    <input type="hidden" name="group_leader_m_id" id="group_leader_m_id" class="form-control"  value="@if(count($loanDetails['GroupLoanMembers']) > 0) {{ getApplicantid($loanDetails['GroupLoanMembers'][0]->groupleader_member_id) }} @endif" readonly="">
                  </div>
                </div>

                <div class="form-group row group-information" style="display: none;">
                  <label class="col-form-label col-lg-2 group-information">EMI Mode Option<sup>*</sup></label>
                  <div class="col-lg-4">
                    <select name="emi_mode_option" id="emi_mode_option" class="form-control group-information" title="Please select something!" disabled="">
                      <option @if($loanDetails->emi_option == 1 && $loanDetails->emi_period == 10) selected @endif class="staff-emi-mode" data-val="months" value="10">10 Months</option>
                      <option class="personal-emi-mode investmentloan-emi-mode" data-val="months" value="12">12 Months</option>
                      <option @if($loanDetails->emi_option == 2 && $loanDetails->emi_period == 12) selected @endif class="group-emi-mode" data-val="weeks" value="12">12 Weeks</option>
                      <option @if($loanDetails->emi_option == 2 && $loanDetails->emi_period == 24) selected @endif class="personal-emi-mode group-emi-mode" data-val="weeks" value="24">24 Weeks</option>
                      <option @if($loanDetails->emi_option == 2 && $loanDetails->emi_period == 26) selected @endif class="personal-emi-mode group-emi-mode" data-val="weeks" value="26">26 Weeks</option>
                      <option @if($loanDetails->emi_option == 2 && $loanDetails->emi_period == 52) selected @endif class="personal-emi-mode group-emi-mode" data-val="weeks" value="52">52 Weeks</option>
                      <option @if($loanDetails->emi_option == 3 && $loanDetails->emi_period == 100) selected @endif class="personal-emi-mode group-emi-mode" data-val="days" value="100">100 Days</option>
                      <option @if($loanDetails->emi_option == 3 && $loanDetails->emi_period == 200) selected @endif class="personal-emi-mode group-emi-mode" data-val="days" value="200">200 Days</option>
                    </select>
                  </div>
                </div>

                <div class="form-group row group-information" style="display: none;">                 
                  <label class="col-form-label col-lg-2">Associate Code<sup>*</sup></label>
                  <div class="col-lg-4">
                    <input type="text" name="group_associate_id" id="group_associate_id" data-val="group-associate" class="form-control" value="{{ getMemberData($loanDetails->associate_member_id)->associate_no }}" autocomplete="on" readonly="">
                    <input type="hidden" name="group_associate_id" class="form-control group-associate-id" value="{{ $loanDetails->associate_member_id }}" readonly="">
                  </div>
                </div>

                <div class="form-group row group-information" style="display: none;">
                  <label class="col-form-label col-lg-2">Select the number of Member<sup>*</sup></label>
                  <div class="col-lg-4">
                    <select name="number_of_member" id="number_of_member" class="form-control" required="" title="Please select something!" disabled="">
                      <option value="">Select</option>
                      @for ($i = 2; $i <= 10; $i++)
                        <option @if(count($loanDetails['GroupLoanMembers']) == $i) selected="" @endif value="{{ $i }}">{{ $i }}</option>
                      @endfor
                    </select>

                  </div>

                  <label class="col-form-label col-lg-2">Loan Amount<sup>*</sup></label>
                  <div class="col-lg-4">
                    <input type="text" name="amount" id="amount" class="form-control group-loan-amount c-amount" value="{{ $loanDetails->amount }}" readonly>
                  </div>
                </div>

                <h3 class="group-loan-member-table group-information" style="display: none;">Group Members Detail</h3>
                <div class="group-loan-member-table group-information" style="overflow: auto;display: none;">
                  <table class="table table-flush">
                      <thead class="">
                          <tr>
                            <th>Member Id</th>
                            <th>Member Name</th>
                            <th>Father Name</th>
                            <th>Amount</th>
                            <th>Bank Name</th>
                            <th>SSB Account</th>
                            <th>IFSC Code</th>
                          </tr>
                      </thead>
                      <tbody class="m-input-number">
                        @foreach($loanDetails['GroupLoanMembers'] as $key => $value)
                        <tr>
                          <td>{{ getMemberData($value->member_id)->member_id }}</td>
                          <td>{{ getMemberData($value->member_id)->first_name }} {{ getMemberData($value->member_id)->last_name }}</td>
                          <td>{{ getMemberData($value->member_id)->father_husband }}</td>
                          <td>{{ $value->amount }}</td>
                          @if(count(getMemberData($value->member_id)['memberBankDetails']) > 0)
                            <td>{{ getMemberData($value->member_id)['memberBankDetails'][0]->bank_name }}</td>
                          @else
                            <td></td>
                          @endif
                          <td>{{ getMemberData($value->member_id)->ssb_account }}</td>
                          @if(count(getMemberData($value->member_id)['memberBankDetails']) > 0)
                            <td>{{ getMemberData($value->member_id)['memberBankDetails'][0]->ifsc_code }}</td>
                          @else
                            <td></td>
                          @endif
                        </tr>
                        @endforeach
                      </tbody>
                  </table>
                </div>
                @endif
                <!---------- Group Loan ------------------->
                @if(count($loanDetails['loanInvestmentPlans']) > 0)
                <h3 class="loan-against-investment-table loan-against-investment-information" style="display: none;">Applicant's Deposit Details</h3>
                <div class="loan-against-investment-table loan-against-investment-information" style="overflow: auto;display: none;">
                  <table class="table table-flush">
                      <thead class="">
                          <tr>
                            <th>Scheme</th>
                            <th>Account ID</th>
                            <th>Open Date</th>
                            <th>Due Date</th>
                            <th>Deposit</th>
                            <th>Tenure</th>
                            <th>Loan Amount</th>
                          </tr>
                      </thead>
                      <tbody class="m-input-number">
                        @foreach($loanDetails['loanInvestmentPlans'] as $key => $value)
                        <tr>
                          <td>{{ getMemberInvestment($value->plan_id)->name }}</td>
                          <td>{{ getMemberInvestment($value->plan_id)->account_number }}</td>
                          <td>{{ date("d/m/Y", strtotime(convertDate(getMemberInvestment($value->plan_id)->created_at))) }}</td>
                          <td>{{ date("d/m/Y", strtotime(convertDate(getMemberInvestment($value->plan_id)->maturity_date))) }}</td>
                          <td>{{ getMemberInvestment($value->plan_id)->deposite_amount }}</td>
                          <td>{{ getMemberInvestment($value->plan_id)->tenure }}</td>
                          <td><input type="text" name="ipl_amount[{{ $key }}]" class="ipl_amount ipl_amount-{{ $key }} form-control" value="{{ $value->amount }}" style="width: 104px" readonly=""></td>
                        </tr>
                        @endforeach
                      </tbody>
                  </table>
                </div>
                @endif

                <div class="applciant-deatils-box" style="display: none;">
                  @include('templates.branch.loan_management.applicant.edit-applicant-details')
                </div>
                <div class="coapplciant-deatils-box" style="display: none;">
                  @include('templates.branch.loan_management.coapplicant.edit-coapplicant-details')
                </div>
                <div class="guarantor-deatils-box" style="display: none;">
                  @include('templates.branch.loan_management.guarantor.edit-guarantor-details')
                </div>

                <div class="text-right">
                  <input type="submit" name="submitform" value="Submit" class="btn btn-primary submit-loan-form">
                </div>  

            </form>
          </div>
        </div>
        <!-- /basic layout -->
      </div>
    </div>

@stop

@section('script')
@include('templates.branch.loan_management.partials.edit-script')
@stop
