@extends('layouts/branch.dashboard')
@section('content')
<div class="container-fluid mt--6">
    <div class="row">
        <div class="col-lg-12">
            @if ($errors->any())
            <div class="col-md-12">
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
            @endif
            <form enctype="multipart/form-data" id="fillter" name="fillter">
                @csrf
                <input type="hidden" name="created_at" class="created_at">
                <input type="hidden" class="create_application_date" value="{{$data['date']}}" readonly name="create_application_date" id="create_application_date">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card bg-white">
                            <div class="card-body"> 
                               
                                <h3 class="card-title mb-3 maintital">
                                    @if($data['payment_type'] == 0) {{'Advance Rent'}}@endif
                                    @if($data['payment_type'] == 1) {{'Advance Salary'}}@endif
                                    @if($data['payment_type'] == 2) {{'TA Imprest Adance'}}@endif
                                </h3>
                                <div class="row">
                                    <div class="col-lg-6 form-group d-flex">
                                        <label class="col-form-label col-lg-4">Payment Type <sup class="required">*</sup></label>
                                        <div class="col-lg-6 error-msg">
                                            <select name="paymentType2" id="paymentType2" class="form-control" aria-invalid="false" disabled>
                                                <!-- <option value="">Please Select</option> -->
                                                <option data-val="0" {{($data['payment_type'] == 0)? 'selected':''}} value="0">Advance Rent</option>
                                                <option data-val="1" {{($data['payment_type'] == 1)? 'selected':''}} value="1">Advance Salary</option>
                                                <option data-val="2" {{($data['payment_type'] == 2)? 'selected':''}} value="2">TA advanced/Imprest</option>
                                            </select>
                                            <input type="hidden" name="paymentType" value="{{$data['payment_type']}}">
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-4">Date<sup class="required">*</sup></label>
                                            <div class="col-lg-8 error-msg">
                                                <input type="text" name="date" id="date" value="" class="form-control">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-4">Company<sup class="required">*</sup></label>
                                            <div class="col-lg-8 error-msg">
                                                <input type="text" name="company" value="{{$data['company']}}" readonly class="form-control">
                                            </div>
                                        </div>
                                    </div>
                                    <input type="hidden" name="company_id" id="company_id" value="{{$data['company_id']}}">
                                    <input type="hidden" name="u_id" value="{{$data['u_id']}}">
                                    <input type="hidden" name="u_paymenttype" value="{{$data['u_paymenttype']}}">
                                    <!-- Select Branch -->
                                    {{--@if(Auth::user()->branch_id>0) --}}
                                    <div class="col-lg-6">
                                        <div class="form-group d-flex">
                                            <label class="col-form-label col-lg-4">Select Branch <sup class="required">*</sup></label>
                                            <div class="col-lg-6 error-msg">
                                                <select name="branch"  disabled class="form-control">
                                                    <!-- <option value="">---Please Select Branch---</option> -->
                                                    @foreach( $data['branch'] as $k =>$val )
                                                    <option value="{{ $val['id'] }}" {{($val['id'] == $data['branch_id'])?'selected':''}}>{{ $val['name'] }}</option>
                                                    @endforeach
                                                </select>
                                                <input type="hidden" name="branch" id="branch" value="{{$data['branch_id']}}">
                                            </div>
                                        </div>
                                    </div>
                                    {{-- @endif --}}
                                </div>
                            </div>
                        </div>
                    </div>
                    <!----------------- TA Advance / Imprest ----------------->
                    <div class="col-lg-12 payment-type-box">
                        <div class="card taadvance bg-white" {{(isset($data['payment_type']) ? '' : 'style="display: none;"')}}>
                            <div class="card-body">
                                <h3 class="card-title mb-3 heading">Owner Details</h3>
                                <div class="row">
                                    @if($data['payment_type']==1 || $data['payment_type']==2)
                                    <div class="col-lg-6 employeecode">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-4">Employee Code<sup class="required">*</sup></label>
                                            <div class="col-lg-8 error-msg">
                                                <input type="text" name="ta_employee_code" disabled value="{{$data['employeecode']}}" id="ta_employee_code" class="form-control">
                                            </div>
                                        </div>
                                    </div>
                                    @endif
                                    @if($data['payment_type']==0)
                                    <div class="col-lg-6 ownerlist ">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-4">Owner name <sup class="required">*</sup></label>
                                            <div class="col-lg-8 error-msg">
                                                <select name="advanced_rent_party_name2" disabled value="" id="advanced_rent_party_name" class="form-control input" data-val="advanced_rent">
                                                    <option value="">Please Select</option>
                                                    @foreach($data['rentOwners'] as $value)
                                                    <option value="{{ $value['id'] }}" <?php if ($data['employeecode'] == $value['id']) {
                                                                                            echo 'selected';
                                                                                        } ?>>{{ $value['owner_name'] }}</option>
                                                    @endforeach
                                                </select>
                                                <input type="hidden" name="advanced_rent_party_name" value="{{ $data['employeecode'] }}">
                                            </div>
                                        </div>
                                    </div>
                                    @endif
                                    {{-- Geting created at --}}
                                    <input type="hidden" name="created_at" class="created_at" id="created_at">
                                    <input type="hidden" name="advanceTranserctionId" value="{{$data['advanceTranserctionId']}}" id="advanceTranserctionId">
                                    <div class="col-lg-6 employeename">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-4">Employee Name<sup class="required">*</sup></label>
                                            <div class="col-lg-8 error-msg">
                                                <input type="text" name="ename" readonly value="{{$data['employename']}}" id="ename" class="form-control">
                                            </div>
                                        </div>
                                    </div>
                                    {{--Get Employee Id--}}
                                    <input type="hidden" name="member_id" id="member_id" class="form-control">
                                    <input type="hidden" name="associate_code" id="associate_code" class="form-control">
                                    <input type="hidden" name="employee_id" id="employee_id" value="{{$data['employeid']}}" class="form-control">
                                    <input type="hidden" name="employee_name" id="employee_name" class="form-control">
                                    <input type="hidden" name="accountNumber" id="accountNumber" class="form-control">
                                    <div class="col-lg-6">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-4">Narration<sup class="required">*</sup></label>
                                            <div class="col-lg-8 error-msg">
                                                <input type="text" name="narration" value="{{$data['narration']}}" id="narration" class="form-control">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-4 amount">Amount<sup class="required">*</sup></label>
                                            <div class="col-lg-8 error-msg">
                                                <input type="text" name="aamount" value="{{$data['advancamount']}}" id="aamount" class="form-control">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-4">Mobile Number <sup class="required">*</sup></label>
                                            <div class="col-lg-8 error-msg">
                                                <input type="text" name="advanced_salary_mobile_number2" readonly value="{{$data['mobilenumber']}}" id="advanced_salary_mobile_number2" class="form-control input">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-4">Bank Name <sup class="required">*</sup></label>
                                            <div class="col-lg-8 error-msg">
                                                <input type="text" name="advanced_salary_bank_name2" value="{{$data['bankname']}}" readonly id="advanced_salary_bank_name2" class="form-control input" readonly="">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-4">Bank A/C No. <sup class="required">*</sup></label>
                                            <div class="col-lg-8 error-msg">
                                                <input type="text" name="advanced_salary_bank_account_number2" readonly id="advanced_salary_bank_account_number2" value="{{$data['bankacountnumber']}}" class="form-control input" readonly="">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-4">IFSC Code <sup class="required">*</sup></label>
                                            <div class="col-lg-8 error-msg">
                                                <input type="text" name="advanced_salary_ifsc_code2" readonly value="{{$data['bankifsc']}}" id="advanced_salary_ifsc_code2" class="form-control input" readonly="">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-6" id="ssb">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-4">SSB Account Number</label>
                                            <div class="col-lg-8 error-msg">
                                                <input type="text" name="ssbno" readonly value="{{$data['ssbaccountnumber']}}" id="ssbno" class="form-control">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-6" id="branchBalance2" style="display:none;">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-4">Branch Current Balance<sup class="required">*</sup></label>
                                            <div class="col-lg-8 error-msg">
                                                <input type="text" name="branchBalance" id="branchBalance" value="{{$data['BranchCurrentBalance']}}" readonly class="form-control">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-6 paymentmode">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-4">Payment Mode <sup class="required">*</sup></label>
                                            <div class="col-lg-8 error-msg">
                                                <select name="payment_mode" id="payment_mode" class="form-control input-type">
                                                    <option value="">Please Select</option>
                                                    <option value="SSB">SSB</option>
                                                    <option value="BANK">Bank</option>
                                                    <option value="CASH">CASH</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-6" id="tmode" style="display:none;">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-4">Transfer Mode <sup class="required">*</sup></label>
                                            <div class="col-lg-8 error-msg">
                                                <select name="transfer_mode" id="transfer_mode" class="form-control input-type">
                                                    <option value="">---- Please Select ----</option>
                                                    <option value="0">Cheque</option>
                                                    <option value="1">Online Transfer</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 " id="bankss" style="display:none;">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-4">Bank</label>
                                            <div class="col-lg-8 error-msg">
                                                <select class="form-control" id="bank_id" name="bank_id">
                                                    <option value="">Select Bank</option>
                                                    @foreach ($data['bank'] as $val)
                                                    <option value="{{ $val['id'] }}">{{ $val['bank_name'] }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 " id="accourid" style="display:none;">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-4">Account Number<sup class="required">*</sup></label>
                                            <div class="col-lg-8 error-msg">
                                                <select name="account_id" id="account_id" class="form-control" aria-invalid="false">
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-6 bankbalance " id="bankbalance" style="display:none;">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-4">Bank Balance<sup class="required">*</sup></label>
                                            <div class="col-lg-8 error-msg">
                                                <input type="text" class="form-control" name="bank_balance" id="bank_balance" readonly value="0.00">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 online utrnumber" style="display: none;">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-4"> UTR number / Transaction Number </label>
                                            <div class="col-lg-8 error-msg">
                                                <input type="text" class="form-control" name="utr_tran" id="utr_tran">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 online rtgsnumber" style="display: none;">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-4">RTGS/NEFT Charge </label>
                                            <div class="col-lg-8 error-msg">
                                                <input type="text" class="form-control" name="neft_charge" id="neft_charge">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-6 " id="cheque" style="display: none;">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-4">Cheque <sup class="required">*</sup></label>
                                            <div class="col-lg-8 error-msg">
                                                <select name="cheque_id" id="cheque_id" class="form-control">
                                                    <option value="">Select Cheque</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!----------------- End TA Advance / Imprest ----------------->
                                <button type="button" id="tasubmit" class="btn btn-primary text-right float-right">Submit</button>
                            </div>
                        </div>
                    </div>
            </form>
        </div>
    </div>
</div>
@stop

@section('script')
@include('templates.branch.AdvancePayment.partials.script')

@stop