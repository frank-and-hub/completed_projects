@extends('layouts/branch.dashboard')
@section('content')
<div class="loader" style="display: none;"></div>
<div class="container-fluid mt--6">
  <div class="content-wrapper">
    <div class="row">
      <div class="col-lg-12">
        <div class="card bg-white">
          <div class="card-body page-title">
            <h3 class="">{{$title}}</h3>
            <a href="{!! route('investment.plans') !!}" style="float:right" class="btn btn-secondary">Back</a>
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
            <h3 class="mb-0">Account Details</h3>
            <div class="header-elements">
              <div class="list-icons">
              </div>
            </div>
          </div>
          <div class="card-body">
            <!-- <div class="col-lg-12">Withdrawal service temporarily unavailable. Please contact your administrator 
           action="{{route('branch.withdrawal.save')}}"
           </div>-->
            <form action="{{route('branch.withdrawal.save')}}" method="post" id="withdrawal-ssb" name="withdrawal-ssb">
              @csrf
              @php
              $stateid = getBranchStateByManagerId(Auth::user()->id);
              @endphp
              <input type="hidden" name="branch_id" id="branch_id" value="{{ $branch_id }}">
              <input type="hidden" name="time" id="time">
              <input type="hidden" name="created_at" id="created_at"
                value="{{ checkMonthAvailability(date('d'),date('m'),date('Y'),$stateid) }}" class="created_at">
              <div class="form-group row">
              <label class="col-form-label col-lg-2">Select Company<span>*</span></label>
                <div class="col-lg-4">
                  <select name="company_id" id="company_id" class="form-control" required>
                    <option value="">----Please Select Company----</option>
                    @foreach( $company_withdarl as $key => $val )
                    <option value="{{$key}}">{{$val}}</option>
                    @endforeach
                  </select>
                </div>
                <label class="col-form-label col-lg-2">Select Date<sup>*</sup></label>
                <div class="col-lg-4">
                  @php
                  $stateid = getBranchStateByManagerId(Auth::user()->id);
                  @endphp
                  <input type="text" name="date" class="form-control"
                    value="{{ headerMonthAvailability(date('d'),date('m'),date('Y'),$stateid) }}" readonly="">
                </div>
              </div>
              <div class="form-group row">
                <label class="col-form-label col-lg-2">SSB Account<sup>*</sup></label>
                <div class="col-lg-4">
                  <input type="text" name="ssb_account_number" id="ssb_account_number" class="form-control"
                    placeholder="SSB Account" required="" autocomplete="off">
                </div>
                <label class="col-form-label col-lg-2">Customer Id</label>
                <div class="col-lg-4">
                  <input type="text" name="member_id" id="member_id" class="form-control" placeholder="Customer Id"
                    readonly="">
                </div>
              </div>
              <div class="form-group row">
                <label class="col-form-label col-lg-2">Account Holder Name</label>
                <div class="col-lg-4">
                  <input type="text" name="account_holder_name" id="account_holder_name" class="form-control"
                    placeholder="Account Holder Name" readonly="">
                </div>
                <label class="col-form-label col-lg-2">Account Balance</label>
                <div class="col-lg-4">
                  <input type="text" name="account_balance" id="account_balance" class="form-control"
                    placeholder="Account Balance" readonly="">
                </div>
              </div>
              <div class="form-group row">
                <label class="col-form-label col-lg-2">Amount<span>*</span></label>
                <div class="col-lg-4">
                  <input type="text" name="amount" id="amount" class="form-control" placeholder="Enter Amount"
                    required="" autocomplete="off">
                </div>
                <label class="col-form-label col-lg-2">Signature</label>
                <div class="col-lg-4">
                  <span class="signature"></span>
                </div>
              </div>
              <div class="form-group row">
                <label class="col-form-label col-lg-2">Photo</label>
                <div class="col-lg-4">
                  <span class="photo"></span>
                </div>
                <label class="col-form-label col-lg-2">Payment Mode<span>*</span></label>
                <div class="col-lg-4">
                  <select name="payment_mode" id="payment_mode" class="form-control">
                    <option value="">----Please Select----</option>
                    <option value="0">Cash</option>
                    <!-- <option value="1">Bank</option>    -->
                  </select>
                </div>
              </div>
              <div class="form-group row">
              <label class="col-form-label col-lg-2 cash" style="display: none;">Available balance in branch</label>
                <div class="col-lg-4 cash" style="display: none;">
                  <input type="text" name="available_balance" id="available_balance" class="form-control"
                    placeholder="Available balance"
                    value="" readonly=""
                    autocomplete="off">
                </div>

                <label class="col-form-label col-lg-2 bank" style="display: none;">Select Bank Name</label>
                <div class="col-lg-4 bank" style="display: none;">
                  <select name="bank" id="bank" class="form-control">
                    <option value="">----Please Select----</option>
                    @foreach( $bank as $key => $val )
                    <option data-val="{{ $val['bankAccount']?$val['bankAccount']->account_no:''}}"
                      value="{{ $val->id }}">{{ $val->bank_name }}</option>
                    @endforeach
                  </select>
                </div>
              </div>
              <div class="form-group row bank" style="display: none;">
                <label class="col-form-label col-lg-2">Bank A/c</label>
                <div class="col-lg-4">
                  <select name="bank_account_number" id="bank_account_number" class="form-control">
                    <option value="">----Please Select ----</option>
                  </select>
                </div>
                <label class="col-form-label col-lg-2">Available balance in bank</label>
                <div class="col-lg-4">
                  <input type="text" name="bank_balance" id="bank_balance" class="form-control" placeholder="0.00"
                    readonly="">
                </div>
                <label class="col-form-label col-lg-2">Select Mode</label>
                <div class="col-lg-4">
                  <select name="bank_mode" id="bank_mode" class="form-control">
                    <option value="">----Please Select----</option>
                    <option value="0">Cheque</option>
                    <option value="1">Online</option>
                  </select>
                </div>
              </div>
              <div class="form-group row">
                <label class="col-form-label col-lg-2 cheque" style="display: none;">Cheque Number</label>
                <div class="col-lg-4 cheque" style="display: none;">
                  <select name="cheque_number" id="cheque_number" class="form-control">
                    <option value="">----Please Select----</option>
                  </select>
                </div>
              </div>
              <div class="form-group row">
                <label class="col-form-label col-lg-2 online" style="display: none;">No/UTR No</label>
                <div class="col-lg-4 online" style="display: none;">
                  <input type="text" name="utr_no" id="utr_no" class="form-control">
                </div>
                <label class="col-form-label col-lg-2 online" style="display: none;">RTGS/NEFT Charge</label>
                <div class="col-lg-4 online" style="display: none;">
                  <input type="text" name="rtgs_neft_charge" id="rtgs_neft_charge" class="form-control">
                </div>
              </div>
              <div class="form-group row">
                <label class="col-form-label col-lg-2 online" style="display: none;">Member Bank</label>
                <div class="col-lg-4 online" style="display: none;">
                  <input type="text" name="mbank" id="mbank" class="form-control">
                </div>
                <label class="col-form-label col-lg-2 online" style="display: none;">Member Bank A/c</label>
                <div class="col-lg-4 online" style="display: none;">
                  <input type="text" name="mbankac" id="mbankac" class="form-control">
                </div>
              </div>
              <div class="form-group row">
                <label class="col-form-label col-lg-2 online" style="display: none;">Member Bank IFSC</label>
                <div class="col-lg-4 online" style="display: none;">
                  <input type="text" name="mbankifsc" id="mbankifsc" class="form-control">
                </div>
              </div>
              <div class="text-right otpbtn">
                <label class="btn btn-primary mt-4" id="otp" style="cursor: pointer;">Send OTP</label>
              </div>
          </div>
          <div class="text-right  subButton m-4" style="display:none;">
            <input type="submit" name="submitform" value="Submit" class="btn btn-primary submit-investment submit">
          </div>
          </form>
        </div>
      </div>
      <!-- /basic layout -->
    </div>
  </div>
  <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="d-flex justify-content-center align-items-center container">
        <div class="card py-5 px-3 modal-content">
          <h3 class="m-0">OTP Verification</h3><span class="mobile-text">Enter the code we just send on your mobile
            phone <b class="text-dark form_input" id="mobile_no"></b></span>
          <form action="#" method="post" name="otp_form" id="otp_form">
            <div class="d-flex flex-row mt-5 otp">
              <input type="text" class="form-control otp_inputs" maxlength=1 name="otp1" autofocus="">
              <input type="text" class="form-control otp_inputs" maxlength=1 name="otp2">
              <input type="text" class="form-control otp_inputs" maxlength=1 name="otp3">
              <input type="text" class="form-control otp_inputs" maxlength=1 name="otp4">
            </div>
            <label class="error-message"></label>
            <!-- <button type="submit" class="btn btn-primary mt-4" id="verify"> Verity Otp </button> -->
            <div class="d-flex justify-content-center">
              <input type="submit" name="otpSubmit" value="Verify OTP" id="verify" class="btn btn-primary mt-4">
            </div>
            <span class="timer d-flex justify-content-center">
              <span id="counter">
                <i class="fa-regular fa-clock fa-beat"></i></span>
            </span>
          </form>
          <div class="text-center mt-5"><span class="d-block mobile-text">Don't receive the code?</span>
            <span class="font-weight-bold text-primary cursor" style="cursor: pointer;" id="resend">Resend</span>
          </div>
        </div>
      </div>
    </div>
  </div>
  @stop
  @section('script')
  @include('templates.branch.payment-management.withdrawal.partials.script')
  @stop