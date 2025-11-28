<!--<div class="col-lg-12 d-none">-->
    <div class="card bg-white d-none" id="rdAccountInvestment">
        <div class="card-body">
            <h3 class="card-title mb-3">RD Investment Form </h3>
            <div class="row">
                <div class="col-lg-3">
                    <div class="form-group row">
                        <label class="col-form-label col-lg-5">Amount<sup class="required">*</sup></label>
                        <div class="col-lg-7 error-msg">
                            <div class="rupee-img"></div>
                            <input type="text" name="rd_amount" id="rd_amount" class="form-control rupee-txt"
                                value="500" readonly="readonly">
                        </div>
                    </div>
                </div>
                <div class="col-lg-5">
                    <div class="form-group row">
                        <label class="col-form-label col-lg-5">Payment Mode<sup class="required">*</sup></label>
                        <div class="col-lg-7 error-msg">
                            <select name="payment_mode" id="payment_mode" class="form-control">
                                <option value="">Select Mode</option>
                                <option data-val="cash" value="0">Cash</option>
                                <!-- <option data-val="cheque-mode" value="1">Cheque</option> -->
                                <!-- <option data-val="online-transaction-mode" value="2">Online transaction</option> -->
                                <!-- <option data-val="ssb-account" value="3">SSB account</option> -->
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="form-group row">
                        <label class="col-form-label col-lg-4">Tenure<sup class="required">*</sup></label>
                        <div class="col-lg-7 error-msg">
                            <label class="col-form-label col-lg-10 tenuremonths"></label>
                            <input type="hidden" name="tenure" id="tenure" class="form-control" value="">
                        </div>
                    </div>
                </div>
                {!! Form::hidden('rdPlanId',null,['id'=>'rdPlanId']) !!}
                {!! Form::hidden('rdPlanCode',null,['id'=>'rdPlanCode']) !!}
                {{-- <div class="col-lg-12">--}}
                <div class="col-lg-3">
                    <span id="maturity" style="padding: 15px"></span>
                </div>

                <input type="hidden" name="rd_amount_maturity" id="rd_amount_maturity" class="form-control rupee-txt"
                    value="0" readonly="readonly">
                <input type="hidden" name="rd_rate" id="rd_rate" class="form-control rupee-txt" value="0"
                    readonly="readonly">
                <div class="col-lg-5">
                    <div class="form-group row">
                        <label class="col-form-label col-lg-5">Form No<sup class="required">*</sup></label>
                        <div class="col-lg-7 error-msg">
                            <input type="text" name="rd_form_no" id="rd_form_no" class="form-control">
                        </div>
                    </div>
                </div>
                {{-- </div>--}}
            </div>
            <!------- RD account Payment mode detail  start ------------->
            <!------- Payment Mode - Cheque Start  ------------->
            <div id="payment_mode_cheque" style="display: none">
                <h4 class="card-title mb-3">Cheque Detail</h4>
                <div class=" row">
                    <div class="col-lg-6">
                        <div class="form-group row">
                            <label class="col-form-label col-lg-4">Cheque Number<sup class="required">*</sup></label>
                            <div class="col-lg-8 error-msg">
                                <select name="cheque_id" id="cheque_id" class="form-control" title="Please select something!">
                                    <option value="">Select Cheque</option>
                                </select>

                            </div>
                        </div>
                    </div>
                </div>
                <div class=" row" style="display: none;" id="cheque_detail">
                    <div class="col-lg-6">
                        <div class="form-group row">
                            <label class="col-form-label col-lg-4">Cheque Number<sup class="required">*</sup></label>
                            <div class="col-lg-8 error-msg">
                                <input type="text" name="rd_cheque_no" id="rd_cheque_no" class="form-control" readonly>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-form-label col-lg-4">Branch Name<sup class="required">*</sup></label>
                            <div class="col-lg-8 error-msg">
                                <input type="text" name="rd_branch_name" id="rd_branch_name" class="form-control"
                                    readonly>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-form-label col-lg-4">Cheque Amount<sup class="required">*</sup></label>
                            <div class="col-lg-8 error-msg">
                                <div class="rupee-img"></div>
                                <input type="text" name="cheque-amt" id="cheque-amt" class="form-control rupee-txt"
                                    readonly>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-form-label col-lg-4">Deposit Bank Name<sup
                                    class="required">*</sup></label>
                            <div class="col-lg-8 error-msg">
                                <input type="text" name="deposit_bank_name" id="deposit_bank_name" class="form-control"
                                    readonly>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form-group row">
                            <label class="col-form-label col-lg-4">Bank Name<sup class="required">*</sup></label>
                            <div class="col-lg-8 error-msg">
                                <input type="text" name="rd_bank_name" id="rd_bank_name" class="form-control" readonly>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-form-label col-lg-4">Cheque Date<sup class="required">*</sup></label>
                            <div class="col-lg-8 error-msg">
                                <div class="input-group">
                                    <span class="input-group-prepend">
                                        <span class="input-group-text"><i class="ni ni-calendar-grid-58"></i></span>
                                    </span>
                                    <input type="text" class="form-control " name="rd_cheque_date" id="rd_cheque_date"
                                        readonly>
                                </div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-form-label col-lg-4">Deposit Bank Account <sup
                                    class="required">*</sup></label>
                            <div class="col-lg-8 error-msg">
                                <input type="text" name="deposit_bank_account" id="deposit_bank_account"
                                    class="form-control" readonly>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!------- Payment Mode - Cheque End  ------------->
            <!------- Payment Mode - online transaction Start  ------------->
            <div id="payment_mode_online" style="display: none">
                <h4 class="card-title mb-3">Online transaction</h4>
                <div class=" row">
                    <div class="col-lg-6">
                        <div class="form-group row">
                            <label class="col-form-label col-lg-3">Transaction Id<sup class="required">*</sup></label>
                            <div class="col-lg-8 error-msg">
                                <input type="text" name="rd_online_id" id="rd_online_id" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form-group row">
                            <label class="col-form-label col-lg-3">Date<sup class="required">*</sup></label>
                            <div class="col-lg-8 error-msg">
                                <div class="input-group">
                                    <span class="input-group-prepend">
                                        <span class="input-group-text"><i class="ni ni-calendar-grid-58"></i></span>
                                    </span>
                                    <input type="text" class="form-control " name="rd_online_date" id="rd_online_date">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form-group row">
                            <label class="col-form-label col-lg-3">Deposit Bank <sup class="required">*</sup></label>
                            <div class="col-lg-8 error-msg">
                                <select name="rd_online_bank_id" id="rd_online_bank_id" class="form-control">
                                    <option value=''>---Please Select---</option>
                                    @foreach($samraddhBanks as $bank)
                                    @if($bank['bankAccount'])
                                    <option value="{{ $bank->id }}"
                                        data-account="{{ $bank['bankAccount']->account_no }}">
                                        {{ $bank->bank_name }}</option>
                                    @endif
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form-group row">
                            <label class="col-form-label col-lg-3">Deposit Bank Account <sup
                                    class="required">*</sup></label>
                            <div class="col-lg-8 error-msg">
                                <select name="rd_online_bank_ac_id" id="rd_online_bank_ac_id" class="form-control">
                                    <option value=''>---Please Select---</option>
                                    @foreach($samraddhBanks as $bank)
                                    @if($bank['bankAccount'])
                                    <option style="display: none;" class="{{ $bank->id }}-bank-account bank-account"
                                        value="{{ $bank['bankAccount']->id }}" style="display: none;">
                                        {{ $bank['bankAccount']->account_no }}</option>
                                    @endif
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!------- Payment Mode -  online transaction End  ------------->
            <!------- Payment Mode - SSB Account  ------------->
            <div id="payment_mode_ssb" style="display: none">
                <h4 class="card-title mb-3">Account Detail</h4>
                <div class=" row">
                    <div class="col-lg-6">
                        <div class="form-group row">
                            <label class="col-form-label col-lg-4">Account Number</label>
                            <div class="col-lg-8 error-msg">
                                <input type="text" name="rd_ssb_account_number" id="rd_ssb_account_number"
                                    class="form-control" disabled>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form-group row">
                            <label class="col-form-label col-lg-4">Account Balance</label>
                            <div class="col-lg-8 error-msg">
                                <input type="text" name="rd_ssb_account_amount" id="rd_ssb_account_amount"
                                    class="form-control" disabled>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!------- Payment Mode -  SSB Account  End  ------------->

            <!------- RD account Payment mode detail  End ------------->
            <h4 class="card-title mb-3">First Nominee Detail</h4>
            <div class="row">
                <div class="col-lg-12">
                    <div class="form-group row">
                        <div class="col-lg-9 error-msg">
                            <div class="row">
                                <div class="col-lg-9">
                                    <div class="custom-control custom-checkbox mb-3 ">
                                        <input type="checkbox" id="old_rd_no_detail" name="old_rd_no_detail"
                                            class="custom-control-input" value="1">
                                        <label class="custom-control-label" for="old_rd_no_detail" value="1">Yes</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="form-group row">
                        <label class="col-form-label col-lg-4">Full Name<sup class="required">*</sup></label>
                        <div class="col-lg-8 error-msg">
                            <input type="text" name="rd_first_first_name" id="rd_first_first_name" class="form-control">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-form-label col-lg-4">Relationship<sup class="required">*</sup></label>
                        <div class="col-lg-8" class="form-control">
                            <select name="rd_first_relation" id="rd_first_relation" class="form-control">
                                <option value="">Select Relation</option>
                                @foreach ($relations as $val)
                                <option value="{{ $val->id }}">{{ $val->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-form-label col-lg-4">Date of Birth<sup class="required">*</sup>
                        </label>
                        <div class="col-lg-8 error-msg">
                            <div class="input-group">
                                <span class="input-group-prepend">
                                    <span class="input-group-text"><i class="ni ni-calendar-grid-58"></i></span>
                                </span>
                                <input type="text" class="form-control " name="rd_first_dob" id="rd_first_dob">
                            </div>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-form-label col-lg-4">Percentage</label>
                        <div class="col-lg-8 error-msg">
                            <input type="text" name="rd_first_percentage" id="rd_first_percentage" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="form-group row">
                        <label class="col-form-label col-lg-4">Gender<sup class="required">*</sup>
                        </label>
                        <div class="col-lg-8 error-msg">
                            <div class="row">
                                <div class="col-lg-4">
                                    <div class="custom-control custom-radio mb-3 ">
                                        <input type="radio" id="rd_first_gender_male" name="rd_first_gender"
                                            class="custom-control-input" value="1">
                                        <label class="custom-control-label" for="rd_first_gender_male">Male</label>
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="custom-control custom-radio mb-3  ">
                                        <input type="radio" id="rd_first_gender_female" name="rd_first_gender"
                                            class="custom-control-input" value="0">
                                        <label class="custom-control-label" for="rd_first_gender_female">Female</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-form-label col-lg-4">Age<sup class="required">*</sup></label>
                        <div class="col-lg-8 error-msg">
                            <input type="text" name="rd_first_age" id="rd_first_age" class="form-control"
                                readonly="readonly">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-form-label col-lg-4">Mobile No<sup class="required">*</sup></label>
                        <div class="col-lg-8 error-msg">
                            <input type="text" name="rd_first_mobile_no" id="rd_first_mobile_no" class="form-control">
                        </div>
                    </div>

                </div>
                <div class="col-lg-12">
                    <div class="form-group row">
                        <div class="col-lg-12">
                            <button type="button" class="btn btn-primary" id="second_nominee_rd">Add
                                Nominee </button>
                            <button type="button" class="btn btn-primary" id="second_nominee_rd_remove"
                                style="display: none">Remove Nominee
                                <!-- <i class="fas fa-trash"></i> -->
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <DIV id="rd_second_no_div" style="display: none">
                <h4 class="card-title mb-3">Second Nominee Detail</h4>
                <div class="row">
                    <div class="col-lg-6">
                        <div class="form-group row">
                            <label class="col-form-label col-lg-4">Full Name<sup class="required">*</sup></label>
                            <div class="col-lg-8 error-msg">
                                <input type="text" name="rd_second_first_name" id="rd_second_first_name"
                                    class="form-control">
                                <input type="hidden" name="rd_second_validate" id="rd_second_validate"
                                    class="form-control" value="0">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-form-label col-lg-4">Relationship<sup class="required">*</sup></label>
                            <div class="col-lg-8" class="form-control">


                                <select name="rd_second_relation" id="rd_second_relation" class="form-control">
                                    <option value="">Select Relation</option>
                                    @foreach ($relations as $val)
                                    <option value="{{ $val->id }}">{{ $val->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-form-label col-lg-4">Date of Birth<sup class="required">*</sup>
                            </label>
                            <div class="col-lg-8 error-msg">
                                <div class="input-group">
                                    <span class="input-group-prepend">
                                        <span class="input-group-text"><i class="ni ni-calendar-grid-58"></i></span>
                                    </span>
                                    <input type="text" class="form-control " name="rd_second_dob" id="rd_second_dob">
                                </div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-form-label col-lg-4">Percentage<sup class="required">*</sup></label>
                            <div class="col-lg-8 error-msg">
                                <input type="text" name="rd_second_percentage" id="rd_second_percentage"
                                    class="form-control">
                            </div>
                        </div>

                    </div>
                    <div class="col-lg-6">

                        <div class="form-group row">
                            <label class="col-form-label col-lg-4">Gender<sup class="required">*</sup>
                            </label>
                            <div class="col-lg-8 error-msg">
                                <div class="row">
                                    <div class="col-lg-4">
                                        <div class="custom-control custom-radio mb-3 ">
                                            <input type="radio" id="rd_second_gender_male" name="rd_second_gender"
                                                class="custom-control-input" value="1">
                                            <label class="custom-control-label" for="rd_second_gender_male">Male</label>
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="custom-control custom-radio mb-3  ">
                                            <input type="radio" id="rd_second_gender_female" name="rd_second_gender"
                                                class="custom-control-input" value="0">
                                            <label class="custom-control-label"
                                                for="rd_second_gender_female">Female</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-form-label col-lg-4">Age<sup class="required">*</sup></label>
                            <div class="col-lg-8 error-msg">
                                <input type="text" name="rd_second_age" id="rd_second_age" class="form-control"
                                    readonly="readonly">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-form-label col-lg-4">Mobile No<sup class="required">*</sup></label>
                            <div class="col-lg-8 error-msg">
                                <input type="text" name="rd_second_mobile_no" id="rd_second_mobile_no"
                                    class="form-control">
                            </div>
                        </div>

                    </div>
                </div>
            </DIV>
        </div>
    </div>
<!--</div>-->