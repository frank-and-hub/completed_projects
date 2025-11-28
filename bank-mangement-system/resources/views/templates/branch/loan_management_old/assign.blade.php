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
            <h3 class="mb-0">Registration</h3>
                <div class="header-elements">
                  <div class="list-icons">
                </div>
              </div>
          </div>
          <div class="card-body">

            <div class="form-group row select-loan">
              <label class="col-form-label col-lg-2">Loans<sup>*</sup></label>
              <div class="col-lg-4">
                <select name="loan" id="loan" class="form-control" title="Please select something!">
                  <option value="">Select Loan</option>
                  @foreach($loans as $loan)
                    @if($loan->name=='Personal loan')
                      <option data-val="{{ $loan->slug }}" value="{{ $loan->id }}">{{ $loan->name }}(PL)</option>
                    @elseif($loan->name=='Staff Loan')
                      <option data-val="{{ $loan->slug }}" value="{{ $loan->id }}">{{ $loan->name }}(SL)</option>
                    @elseif($loan->name=='Group Loan')
                      <option data-val="{{ $loan->slug }}" value="{{ $loan->id }}">{{ $loan->name }}(GL)</option>
                    @elseif($loan->name=='Loan against Investment plan')
                      <option data-val="{{ $loan->slug }}" value="{{ $loan->id }}">{{ $loan->name }}(DL)</option>
                    @endif
                  @endforeach
                </select>
              </div>
              @php
                $stateid = getBranchState(Auth::user()->username);
              @endphp
              <input type="hidden" name="created_at" id="created_at" class="form-control  created_at" readonly >


              <label class="col-form-label col-lg-2">Application Date<sup>*</sup></label>
              <div class="col-lg-4">
                <input type="text" name="application_date" class="application_date form-control" readonly="" >
              </div>
            </div>

            <form action="{{route('loan.store')}}" method="post" id="register-plan" name="register-plan" enctype="multipart/form-data">
            @csrf



                <input type="hidden" name="file_charge" id="file_charge">
                <input type="hidden" name="loan_emi" id="loan_emi">
                <input type="hidden" name="loan_type" id="loan_type">
                <input type="hidden" name="loan" class="loanId">
                <input type="hidden" name="emi_option" class="emi_option">
                <input type="hidden" name="emi_period" class="emi_period">
                <input type="hidden" name="interest_rate" id="interest-rate">
                <input type="hidden" name="loan_amount" id="loan_amount">
                <input type="hidden" name="loan_purpose" id="loan_purpose">
                <input type="hidden" name="created_date" id="created_date">

                <!-- <div class="form-group row salary-section" style="display: none;">
                  <label class="col-form-label col-lg-2">Salary<sup>*</sup></label>
                  <div class="col-lg-4">
                    <input type="text" name="salary" id="salary" class="form-control">
                  </div>
                </div> -->

                <div class="form-group row staff-loan-section" style="display: none;">
                  <label class="col-form-label col-lg-2">Loan Amount<sup>*</sup></label>
                  <div class="col-lg-4">
                    <input type="text" name="amount" id="amount" class="form-control c-amount">
                    <label id="loan-amount-error"></label>
                    <h4 class="loan-emi-amount"></h4>
                  </div>

                  <label class="col-form-label col-lg-2">EMI Mode Option<sup>*</sup></label>
                  <div class="col-lg-4">
                    <select name="emi_mode_option" id="emi_mode_option" class="form-control" title="Please select something!">
                      <option value="">--Select--</option>
                      <option class="staff-emi-mode" data-val="months" value="10">10 Months</option>
                      <option class="personal-emi-mode investmentloan-emi-mode" data-val="months" value="12">12 Months</option>
                      <option class="group-emi-mode" data-val="weeks" value="12">12 Weeks</option>
                      <option class="personal-emi-mode group-emi-mode" data-val="weeks" value="24">24 Weeks</option>
                      <option class="personal-emi-mode group-emi-mode" data-val="weeks" value="26">26 Weeks</option>
                      <option class="personal-emi-mode group-emi-mode" data-val="weeks" value="52">52 Weeks</option>
                      <option class="personal-emi-mode group-emi-mode" data-val="days" value="100">100 Days</option>
                      <option class="personal-emi-mode group-emi-mode" data-val="days" value="200">200 Days</option>
                    </select>
                  </div>
                </div>

                <div class="form-group row personal-loan-section" style="display: none;">
                    <label class="col-form-label col-lg-2">Loan Amount<sup>*</sup></label>
                    <div class="col-lg-4">
                        <input type="text" name="amount" id="amount" class="form-control c-amount">
                        <label id="loan-amount-error"></label>
                    </div>

                    <label class="col-form-label col-lg-2">EMI Mode Option<sup>*</sup></label>
                    <div class="col-lg-4">
                        <select name="emi_mode_option" id="emi_mode_option" class="form-control" title="Please select something!">
                            <option value="">--Select--</option>
                            <option class="personal-emi-mode investmentloan-emi-mode" data-val="months" value="12">12 Months</option>
                            <option class="group-emi-mode" data-val="weeks" value="12">12 Weeks</option>
                            <option class="personal-emi-mode group-emi-mode" data-val="weeks" value="24">24 Weeks</option>
                            <option class="personal-emi-mode group-emi-mode" data-val="weeks" value="26">26 Weeks</option>
                            <option class="personal-emi-mode group-emi-mode" data-val="weeks" value="52">52 Weeks</option>
                            <option class="personal-emi-mode group-emi-mode" data-val="days" value="100">100 Days</option>
                            <option class="personal-emi-mode group-emi-mode" data-val="days" value="200">200 Days</option>
                        </select>
                    </div>
                </div>

                <div class="form-group row staff-loan-section" style="display: none;">
                  <label class="col-form-label col-lg-2">Purpose for loan<sup>*</sup></label>
                  <div class="col-lg-4">
                    <input type="text" name="purpose" id="purpose" class="form-control purpose-loan">
                  </div>

                  <label class="col-form-label col-lg-2">Associate Code<sup>*</sup></label>
                  <div class="col-lg-4">
                    <input type="text" data-val="associate" name="acc_auto_member_id" id="acc_auto_member_id" class="form-control" autocomplete="on">
                    <input type="hidden" data-val="associate" name="acc_member_id" id="acc_member_id" class="form-control ass-member-id">
                  </div>
                </div>

                <div class="form-group row other-loan-section">
                  <label class="col-form-label col-lg-2">Loan Amount<sup>*</sup></label>
                  <div class="col-lg-4">
                    <input type="text" name="amount" id="amount" class="form-control c-amount" readonly>
                  </div>

                  <label class="col-form-label col-lg-2">Purpose for loan<sup>*</sup></label>
                  <div class="col-lg-4">
                    <input type="text" name="purpose" id="purpose" class="form-control purpose-loan">
                  </div>
                </div>

                <div class="form-group row other-loan-section">
                  <label class="col-form-label col-lg-2">EMI Mode Option<sup>*</sup></label>
                  <div class="col-lg-4">
                    <select name="emi_mode_option" id="emi_mode_option" class="form-control" title="Please select something!">
                      <option value="">--Select--</option>
                      <option class="personal-emi-mode investmentloan-emi-mode" data-val="months" value="12">12 Months</option>
                      <option class="group-emi-mode" data-val="weeks" value="12">12 Weeks</option>
                      <option class="personal-emi-mode group-emi-mode" data-val="weeks" value="24">24 Weeks</option>
                      <option class="personal-emi-mode group-emi-mode" data-val="weeks" value="26">26 Weeks</option>
                      <option class="personal-emi-mode group-emi-mode" data-val="weeks" value="52">52 Weeks</option>
                      <option class="personal-emi-mode group-emi-mode" data-val="days" value="100">100 Days</option>
                      <option class="personal-emi-mode group-emi-mode" data-val="days" value="200">200 Days</option>
                    </select>
                  </div>
                  <label class="col-form-label col-lg-2">Associate Code<sup>*</sup></label>
                  <div class="col-lg-4">
                    <input type="text" data-val="associate" name="acc_auto_member_id" id="acc_auto_member_id" class="form-control" autocomplete="on">
                    <input type="hidden" data-val="associate" name="acc_member_id" id="acc_member_id" class="form-control ass-member-id">
                  </div>
                </div>

                <div class="form-group row associate-member-detail" style="display: none;">
                  <label class="col-form-label col-lg-2">Name<sup>*</sup></label>
                  <div class="col-lg-4">
                    <input type="text" name="acc_name" id="acc_name" class="form-control" readonly="">
                  </div>

                  <label class="col-form-label col-lg-2">Carder<sup>*</sup></label>
                  <div class="col-lg-4">
                    <input type="text" name="acc_carder" id="acc_carder" class="form-control" readonly="">
                  </div>
                </div>

                <div class="alert alert-danger alert-block associate-member-detail-not-found" style="display: none;">  <strong>Member not found</strong> </div>

                <div class="form-group row bank-details-section">
                  <label class="col-form-label col-lg-2">Bank Account </label>
                  <div class="col-lg-4">
                    <input type="text" name="bank_account" id="bank_account" class="form-control associate-bank-account" readonly="">
                  </div>
                  <label class="col-form-label col-lg-2">IFSC code </label>
                  <div class="col-lg-4">
                    <input type="text" name="ifsc_code" id="ifsc_code" class="form-control associate-ifsc-code" readonly="">
                  </div>
                </div>


                <!---------- Group Loan ------------------->
                <div class="form-group row group-information" style="display: none;">
                  <label class="col-form-label col-lg-2">Group Activity<sup>*</sup></label>
                  <div class="col-lg-4">
                    <input type="text" name="group_activity" id="group_activity" class="form-control">
                  </div>

                  <label class="col-form-label col-lg-2">Group leader Member ID<sup>*</sup></label>
                  <div class="col-lg-4">
                    <input type="text" name="group_leader_member_id" id="group_leader_member_id" class="form-control" readonly="">
                    <input type="hidden" name="group_leader_m_id" id="group_leader_m_id" class="form-control">
                  </div>
                </div>

                <div class="form-group row group-information">
                  <label class="col-form-label col-lg-2 group-member-detail" style="display: none;">Name<sup>*</sup></label>
                  <div class="col-lg-4 group-member-detail" style="display: none;">
                    <input type="text" name="group_lm_name" id="group_lm_name" class="form-control" readonly="">
                  </div>

                  <label class="col-form-label col-lg-2 group-information">EMI Mode Option<sup>*</sup></label>
                  <div class="col-lg-4">
                    <select name="emi_mode_option" id="emi_mode_option" class="form-control group-information" title="Please select something!">
                      <option value="">--Select--</option>
                      <option class="personal-emi-mode" data-val="months" value="12">12 Months</option>
                      <option class="group-emi-mode" data-val="weeks" value="12">12 Weeks</option>
                      <option class="personal-emi-mode group-emi-mode" data-val="weeks" value="24">24 Weeks</option>
                      <option class="personal-emi-mode group-emi-mode" data-val="weeks" value="26">26 Weeks</option>
                      <option class="personal-emi-mode group-emi-mode" data-val="weeks" value="52">52 Weeks</option>
                      <option class="personal-emi-mode  group-emi-mode" data-val="days" value="100">100 Days</option>
                      <option class="personal-emi-mode group-emi-mode" data-val="days" value="200">200 Days</option>
                    </select>
                  </div>
                </div>

                <div class="alert alert-danger alert-block group-member-detail-not-found" style="display: none;">  <strong>Member not found</strong> </div>

                <div class="form-group row group-information" style="display: none;">
                  <label class="col-form-label col-lg-2">Associate Code<sup>*</sup></label>
                  <div class="col-lg-4">
                    <input type="text" name="group_associate_id" id="group_associate_id" data-val="group-associate" class="form-control">
                    <input type="hidden" name="group_associate_id" class="form-control group-associate-id">
                  </div>

                  <label class="col-form-label col-lg-2 group-associate-member-detail" style="display: none;">Name<sup>*</sup></label>
                  <div class="col-lg-4 group-associate-member-detail" style="display: none;">
                    <input type="text" name="group_associate_name" id="group_associate_name" class="form-control group-associate-name" readonly="">
                  </div>
                </div>

                <div class="alert alert-danger alert-block group-associate-member-detail-not-found" style="display: none;">  <strong>Associate not found</strong> </div>

                <div class="form-group row group-information" style="display: none;">
                  <label class="col-form-label col-lg-2">Select the number of Member<sup>*</sup></label>
                  <div class="col-lg-4">
                    <select name="number_of_member" id="number_of_member" class="form-control" required="" title="Please select something!">
                      <option value="">Select</option>
                      @for ($i = 2; $i <= 10; $i++)
                        <option value="{{ $i }}">{{ $i }}</option>
                      @endfor
                    </select>

                  </div>

                  <label class="col-form-label col-lg-2">Loan Amount<sup>*</sup></label>
                  <div class="col-lg-4">
                    <input type="text" name="amount" id="amount" class="form-control group-loan-amount c-amount" readonly>
                  </div>
                </div>

                <h3 class="group-loan-member-table" style="display: none;">Group Members Detail</h3>
                <div class="group-loan-member-table" style="overflow: auto;display: none;">
                  <table class="table table-flush">
                      <thead class="">
                          <tr>
                            <th>Member Id</th>
                            <th>Member Name</th>
                            <th>Father Name</th>
                            <th>Amount</th>
                            <th>Total Deposit Amount</th>
                            <th>Signature</th>
                            <th>Bank Name</th>
                            <th>SSB Account</th>
                            <th>IFSC Code</th>
                            <th>Group Leader</th>
                          </tr>
                      </thead>
                      <tbody class="m-input-number">
                      </tbody>
                  </table>
                </div>
                <!---------- Group Loan ------------------->

                <div class="form-group row staff-loan-section">
                  <label class="col-form-label col-lg-2">Bank Name</label>
                  <div class="col-lg-4">
                    <input type="text" name="bank_name" id="bank_name" class="form-control associate-bank-name" readonly=""e>
                  </div>
                </div>

                <div class="applciant-deatils-box" style="display: none;">
                  @include('templates.branch.loan_management.applicant.applicant-details')
                </div>
                <div class="coapplciant-deatils-box" style="display: none;">
                  @include('templates.branch.loan_management.coapplicant.coapplicant-details')
                </div>
                <div class="guarantor-deatils-box" style="display: none;">
                  @include('templates.branch.loan_management.guarantor.guarantor-details')
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
@include('templates.branch.loan_management.partials.script')
@stop
