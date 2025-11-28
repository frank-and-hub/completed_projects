@extends('layouts/branch.dashboard')

@section('content')
@php
  $stateid = getBranchStateByManagerId(Auth::user()->id);
@endphp
<div class="loader" style="display: none;"></div>

<div class="container-fluid mt--6">
  <div class="content-wrapper">
    <div class="row">
      <div class="col-lg-12">
        <div class="card bg-white">
          <div class="card-body">
            <div class="">
              <h3 class="">{{$title}}</h3>
                <a href="{!! route('loan.store') !!}" style="float:right" class="btn btn-secondary">Back</a>
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
            <h3 class="mb-0">Registrations</h3>
                <div class="header-elements">
                  <div class="list-icons">
                </div>
              </div>
          </div>
          <div class="card-body">
            <div class="form-group row">

                <label class="col-form-label col-lg-2">Loan<sup>*</sup></label>
                <div class="col-lg-4">
                  <select name="loan" id="loan" class="form-control" title="Please select something!" value="{{old('loan')}}" required>
                      <option value="">Select Loan</option>
                      <!-- @foreach ($loans as $loan)
                        @if (!isset($currentLoanType) || $currentLoanType != $loan->loan_tenure_plan->loan_type)
                          @if (isset($currentLoanType))
                            </optgroup>
                          @endif
                          <optgroup label="{{ ($loan->loan_tenure_plan->loan_type=='L') ? 'Loan' : 'Group Loan'}}">
                          <?php $currentLoanType = $loan->loan_tenure_plan->loan_type ?>
                        @endif
                        <option value="{{ $loan->loan_id }}" data-loanType = "{{$loan->loan_tenure_plan->loan_type}}" data-val="{{$loan->loan_tenure_plan->slug}}" data-min="{{$loan->loan_tenure_plan->min_amount}}" data-max="{{$loan->loan_tenure_plan->max_amount}}"  data-category="{{$loan->loan_tenure_plan->loan_category}}"  data-emiOption="{{$loan->emi_option}}" data-tenure="{{$loan->tenure}}"  data-company_id="{{$loan->company_id}}" data-ROInterest = "{{$loan->ROI}}">{{ $loan->name }}</option>
                      @endforeach -->


                      <optgroup label=" Loan">
                      @foreach ($loans as $loan)
                          @if ($loan->loan_tenure_plan->loan_type == 'L')
                              <option value="{{ $loan->loan_id }}" data-loanType="{{$loan->loan_tenure_plan->loan_type}}" data-val="{{ $loan->slug }}" data-min="{{ $loan->loan_tenure_plan->min_amount }}" data-max="{{ $loan->loan_tenure_plan->max_amount }}" data-category="{{ $loan->loan_tenure_plan->loan_category }}" data-emiOption="{{ $loan->emi_option }}" data-tenure="{{ $loan->tenure }}" data-company_id="{{ $loan->company_id }}" data-ROInterest="{{ $loan->ROI }}" data-ecstype ="{{$loan->ecs_allow}}">{{ $loan->name }}</option>
                          @endif
                      @endforeach
                  </optgroup>

                  <optgroup label="Group Loan">
                      @foreach ($loans as $loan)
                          @if ($loan->loan_tenure_plan->loan_type == 'G')
                              <option value="{{ $loan->loan_id }}" data-loanType="{{$loan->loan_tenure_plan->loan_type}}" data-val="{{ $loan->slug }}" data-min="{{ $loan->loan_tenure_plan->min_amount }}" data-max="{{ $loan->loan_tenure_plan->max_amount }}" data-category="{{ $loan->loan_tenure_plan->loan_category }}" data-emiOption="{{ $loan->emi_option }}" data-tenure="{{ $loan->tenure }}" data-company_id="{{ $loan->company_id }}" data-ROInterest="{{ $loan->ROI }}" data-ecstype ="{{$loan->ecs_allow}}">{{ $loan->name }}</option>
                          @endif
                      @endforeach
                  </optgroup>
                   


                  </select>
              </div>
                <label class="col-form-label col-lg-2">Customer's Id<sup>*</sup></label>
              <div class="col-lg-4">
                  <input type="text" name="customer_id" id="customer_id" class="form-control" value="" id="customer_id" data-val="applicant" value="{{old('customer_id')}}" required>
              </div>
              
            </div>
  
            <div class="applicant-member-detail staff-loan-section" id="show_mwmber_detail">
            </div>
            <div class="form-group row">
        
               <label class="col-form-label col-lg-2">Application Date<sup>*</sup></label>
                  <div class="col-lg-4">
                  <input type="text" name="application_date" class="application_date form-control" readonly="" value="{{ headerMonthAvailability(date('d'), date('m'), date('Y'), $stateid) }}"> 
                </div>

              
            </div>

            <form action="{!! route('loan.store') !!}" method="post" id="register-plan" name="register-plan" enctype="multipart/form-data">
            @csrf


                <input type="hidden" name="insurance_charge" id="insurance_charge">
                <input type="hidden" name="file_charge" id="file_charge">
                <input type="hidden" name="loan_emi" id="loan_emi">
                <input type="hidden" name="loan_type" id="loan_type">
                <input type="hidden" name="loanId" class="loanId">
                <input type="hidden" name="emi_option" class="emi_option">
                <input type="hidden" name="emi_period" class="emi_period">
                <input type="hidden" name="interest_rate" id="interest-rate">
                <input type="hidden" name="loan_amount" id="loan_amount">
                <input type="hidden" name="loan_purpose" id="loan_purpose">
                <input type="hidden" name="created_date" id="created_date" value="{{ headerMonthAvailability(date('d'), date('m'), date('Y'), $stateid) }}">
                <input type="hidden" name="newUser" id="newUser">
                <input type="hidden" name="member_id" id="member_id"> 
                <input type="hidden" name="branchid" id="branchid" value="{{getBranchDetailManagerId(Auth::user()->id)->id}}"> 
                <input type="hidden" name="company_id" id="company_id">        
                <input type="hidden" name="gstAmount" id="gstAmount" placeholder="gstAmount">
                <input type="hidden" name="gstStatus" id="gstStatus" placeholder="gstStatus">
                <input type="hidden" name="gstFileAmount" id="gstFileAmount"  placeholder="gstFileAmount">
                <input type="hidden" name="gstFileStatus" id="gstFileStatus" placeholder="gstFileStatus">
                <input type="hidden" name="gstPercentage" id="gstPercentage" placeholder="gstPercentage">
                <input type="hidden" name="gstFilePercentage" id="gstFilePercentage" placeholder="gstFilePercentage">
                <input type="hidden" name="loan_category" id="loan_category" placeholder="loan_category">
                <input type="hidden" name="age" id="age" >
                
                <input type="hidden" name="customerId" id="customerId" placeholder="customerId">

                <!-- changes by shahid on ecs changes -->
                <input type="hidden" class="ecsCharge" name="ecsCharge" id="ecsCharge" placeholder="ecsCharge">
                <input type="hidden" class="ecs_charges" name="ecs_charges" id="ecs_charges">
                <input type="hidden" class="ecsStatus" name="ecsStatus" id="ecsStatus" >
                <input type="hidden" class="ecsFileamount" name="ecsFileamount" id="ecsFileamount">
                <input type="hidden" class="gstecsPercentage" name="gstecsPercentage" id="gstecsPercentage">
                <!-- Changes Edit Done -->
                <!-- <div class="form-group row salary-section" style="display: none;">
                  <label class="col-form-label col-lg-2">Salary<sup>*</sup></label>
                  <div class="col-lg-4">
                    <input type="text" name="salary" id="salary" class="form-control">
                  </div>
                </div> -->

                <div class="form-group row staff-loan-section" style="display: none;">
                  <label class="col-form-label col-lg-2">Loan Amount<sup>*</sup></label>
                  <div class="col-lg-4">
                    <input type="text" name="amount" id="amount" class="form-control c-amount" >
                    <label id="loan-amount-error"></label>
                    <h4 class="loan-emi-amount"></h4>
                  </div>

                  <label class="col-form-label col-lg-2">EMI Mode Option<sup>*</sup></label>
                  <div class="col-lg-4">
                    <!-- <select name="emi_mode_option" id="emi_mode_option" class="form-control" title="Please select something!">
                      <option value="">--Select--</option>
                      <option class="staff-emi-mode" data-val="months" value="10">10 Months</option>
                      <option class="personal-emi-mode investmentloan-emi-mode" data-val="months" value="12">12 Months</option>
                      <option class="group-emi-mode" data-val="weeks" value="12">12 Weeks</option>
                      <option class="personal-emi-mode group-emi-mode" data-val="weeks" value="24">24 Weeks</option>
                      <option class="personal-emi-mode group-emi-mode" data-val="weeks" value="26">26 Weeks</option>
                      <option class="personal-emi-mode group-emi-mode" data-val="weeks" value="52">52 Weeks</option>
                      <option class="personal-emi-mode group-emi-mode" data-val="days" value="100">100 Days</option>
                      <option class="personal-emi-mode group-emi-mode" data-val="days" value="200">200 Days</option>
                    </select> -->
                    <input type="text" name="emi_mode_option"  class="form-control " id="emi_mode_option" readonly>

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
                        <!-- <select name="emi_mode_option" id="emi_mode_option" class="form-control" title="Please select something!">
                            <option value="">--Select--</option>
                            <option class="personal-emi-mode investmentloan-emi-mode" data-val="months" value="12">12 Months</option>
                            <option class="group-emi-mode" data-val="weeks" value="12">12 Weeks</option>
                            <option class="personal-emi-mode group-emi-mode" data-val="weeks" value="24">24 Weeks</option>
                            <option class="personal-emi-mode group-emi-mode" data-val="weeks" value="26">26 Weeks</option>
                            <option class="personal-emi-mode group-emi-mode" data-val="weeks" value="52">52 Weeks</option>
                            <option class="personal-emi-mode group-emi-mode" data-val="days" value="100">100 Days</option>
                            <option class="personal-emi-mode group-emi-mode" data-val="days" value="200">200 Days</option>
                        </select> -->
                         <input type="text" name="emi_mode_option" class="form-control " readonly id="emi_mode_option">
                    </div>
                </div>
                <!-- Ecs Changes by shahid -->
                <div class="form-group row custom-radio esc" style="display: none;">
                  <label class="col-form-label col-lg-2">ECS<sup>*</sup></label>
                  <div class="custom-control custom-radio ml-3">
                      <input type="radio" id="bank" name="ecs_type" class="custom-control-input" value="1" >
                      <label class="custom-control-label" for="bank">Bank</label>
                  </div>
             
                  <div class="custom-control custom-radio ml-3">
                      <input type="radio" id="ssb" name="ecs_type" class="custom-control-input" value="2">
                      <label class="custom-control-label" for="ssb">ssb</label>
                  </div>
                </div>
                <!-- end ecs change by shahid -->
                <div class="form-group row staff-loan-section" style="display: none;">
                  <label class="col-form-label col-lg-2">Purpose for loan<sup>*</sup></label>
                  <div class="col-lg-4">
                    <input type="text" name="purpose" id="purpose" class="form-control purpose-loan" >
                  </div>

                  <label class="col-form-label col-lg-2">Associate Code<sup>*</sup></label>
                  <div class="col-lg-4">
                    <input type="text" data-val="associate" name="acc_auto_member_id" id="acc_auto_member_id" class="form-control" autocomplete="on"  value="{{old('acc_auto_member_id')}}">
                    <input type="hidden" data-val="associate" name="acc_member_id" id="acc_member_id" class="form-control ass-member-id">
                  </div>
                </div>

                <div class="form-group row other-loan-section" style="display: none;">
                  <label class="col-form-label col-lg-2">Loan Amount<sup>*</sup></label>
                  <div class="col-lg-4">
                    <input type="text" name="amount" id="amount" class="form-control c-amount" readonly>
                  </div>

                  <label class="col-form-label col-lg-2">Purpose for loan<sup>*</sup></label>
                  <div class="col-lg-4">
                    <input type="text" name="purpose" id="purpose" class="form-control purpose-loan" >
                  </div>
                </div>

                <div class="form-group row other-loan-section" style="display: none;">
                  <label class="col-form-label col-lg-2">EMI Mode Option<sup>*</sup></label>
                  <div class="col-lg-4">
                    {{-- <select name="emi_mode_option" id="emi_mode_option" class="form-control" title="Please select something!">
                      <option value="">--Select--</option>
                      <option class="personal-emi-mode investmentloan-emi-mode" data-val="months" value="12">12 Months</option>
                      <option class="group-emi-mode" data-val="weeks" value="12">12 Weeks</option>
                      <option class="personal-emi-mode group-emi-mode" data-val="weeks" value="24">24 Weeks</option>
                      <option class="personal-emi-mode group-emi-mode" data-val="weeks" value="26">26 Weeks</option>
                      <option class="personal-emi-mode group-emi-mode" data-val="weeks" value="52">52 Weeks</option>
                      <option class="personal-emi-mode group-emi-mode" data-val="days" value="100">100 Days</option>
                      <option class="personal-emi-mode group-emi-mode" data-val="days" value="200">200 Days</option>
                    </select> --}}
                                        <input type="text" name="emi_mode_option"  class="form-control " id="emi_mode_option" readonly>

                  </div>
                  <label class="col-form-label col-lg-2">Associate Code<sup>*</sup></label>
                  <div class="col-lg-4">
                    <input type="text" data-val="associate" name="acc_auto_member_id" id="acc_auto_member_id" class="form-control" autocomplete="on" value="{{old('acc_auto_member_id')}}">
                    <input type="hidden" data-val="associate" name="acc_member_id" id="acc_member_id" class="form-control ass-member-id">
                  </div>
                </div>
 
                <div class="form-group row associate-member-detail" style="display: none;">
                  <label class="col-form-label col-lg-2">Name<sup>*</sup></label>
                  <div class="col-lg-4">
                    <input type="text" name="acc_name" id="acc_name" class="form-control"  readonly="">
                  </div>

                  <label class="col-form-label col-lg-2">Carder<sup>*</sup></label>
                  <div class="col-lg-4">
                    <input type="text" name="acc_carder" id="acc_carder" class="form-control" readonly="">
                  </div>
                </div> 

                <div class="alert alert-danger alert-block associate-member-detail-not-found" style="display: none;">  <strong>Member not found</strong> </div>

                <!-- <div class="form-group row bank-details-section" style="display: none;">
                  <label class="col-form-label col-lg-2">Bank Account </label>
                  <div class="col-lg-4">
                    <input type="text" name="bank_account" id="bank_account" class="form-control associate-bank-account" readonly="">
                  </div>
                  <label class="col-form-label col-lg-2">IFSC code </label>
                  <div class="col-lg-4">
                    <input type="text" name="ifsc_code" id="ifsc_code" class="form-control associate-ifsc-code" readonly="">
                  </div>
                </div> -->


                <!---------- Group Loan ------------------->
                <div class="form-group row group-information" style="display: none;">
                  <label class="col-form-label col-lg-2">Group Activity<sup>*</sup></label>
                  <div class="col-lg-4">
                    <input type="text" name="group_activity" id="group_activity" class="form-control">
                  </div>

                  <label class="col-form-label col-lg-2">Group leader Customer ID<sup>*</sup></label>
                  <div class="col-lg-4">
                    <input type="text" name="group_leader_member_id" id="group_leader_member_id" class="form-control" readonly="">
                    <input type="hidden" name="group_leader_m_id" id="group_leader_m_id" class="form-control">
                  </div>
                </div>

                <div class="form-group row group-information"  style="display: none;">
                  <label class="col-form-label col-lg-2 group-member-detail" style="display: none;">Name<sup>*</sup></label>
                  <div class="col-lg-4 group-member-detail" style="display: none;">
                    <input type="text" name="group_lm_name" id="group_lm_name" class="form-control" readonly="">
                  </div>

                  <label class="col-form-label col-lg-2 group-information">EMI Mode Option<sup>*</sup></label>
                  <div class="col-lg-4">
               
                                        <input type="text" name="emi_mode_option"  class="form-control emi_mode_option" id="emi_mode_option" readonly>

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
                      @for ($i = 5; $i <= 10; $i++)
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
                            <th>Customer Id</th>
                            <th>Member Name</th>
                            <th>Father Name</th>
                            <th>Amount</th>
                            <th>Total Deposit Amount</th>
                            <th>Signature</th>
                            <th>Bank Name</th>
                            <!-- <th>SSB Account</th> -->
                            <th>Bank Account</th>
                            <th>IFSC Code</th>
                            <th>Group Leader</th>
                          </tr>
                      </thead>
                      <tbody class="m-input-number">
                      </tbody>
                  </table>
                </div>
               
                <!---------- Group Loan ------------------->
{{-- 
                <div class="form-group row staff-loan-section"  style="display: none;">
                  <label class="col-form-label col-lg-2">Bank Name</label>
                  <div class="col-lg-4">
                    <input type="text" name="bank_name" id="bank_name" class="form-control associate-bank-name" readonly=""e>
                  </div>
                </div> --}}

                <div class="applciant-deatils-box" style="display: none;">
                  @include('templates.branch.loan_register.applicant.applicant-details')
                </div>
                <div class="coapplciant-deatils-box" style="display: none;">
                  @include('templates.branch.loan_register.coapplicant.coapplicant-details')
                </div>
                <div class="guarantor-deatils-box" style="display: none;">
                  @include('templates.branch.loan_register.guarantor.guarantor-details')
                </div>

                <div class="text-right">
                  <input type="submit" name="submitform" value="Submit" class="btn btn-primary submit-loan-form" style="display:none;">
                </div>

            </form>  
            
          </div>
        </div>

        <!-- /basic layout -->
      </div>
    </div>

@stop

@section('script')
@include('templates.branch.loan_register.partials.register_script')
@stop
