<style>
.col-form-label {
    font-weight: bold;
}
</style>
<div class="loader" style="display: none;"></div>
<div class="content">
    <div class="col-lg-12" style="display:none" id="cardB">
        <div class="card bg-white">
            <div class="card-body page-title">
                <h3 class="">EMI PAYMENT</h3>
            </div>
        </div>
    </div>
    <div class="row" id="rowR">
        <div class="col-md-12">
            <!-- Basic layout-->
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
            <div class="card">
                <div class="card-body mt-3">
                    <form
                        action="{{ Auth::user()->role_id != 3 ? route('admin.loan.depositeloanemi') : route('branch.loan.depositeloanemi') }}"
                        method="post" id="loan_emi" name="loan_emi">
                        @csrf
                        <!--Detail section  -->
                        <input type="hidden" name="company_id" id="company_id" class="form-control">
                        <input type="hidden" name="loan_id" id="loan_id" class="form-control">
                        <!-- <input type="hidden" name="created_at" id="created_at" class="form-control"> -->
                        <input type="hidden" name="created_date" id="created_date" class="form-control">
                        <input type="hidden" name="associate_member_id" id="associate_member_id" class="form-control">
                        <input type="hidden" name="cgst_amount" id="cgst_amount" class="form-control">
                        <input type="hidden" name="igst_amount" id="igst_amount" class="form-control">
                        <input type="hidden" name="sgst_amount" id="sgst_amount" class="form-control">
                        <input type="hidden" name="associate_id" id="associate_id" class="form-control">
                        <input type="hidden" name="branch" id="branch" class="form-control">
                        <input type="hidden" name="branch_name" id="branch_name" class="form-control">
                        <input type="hidden" name="approve_date" id="approve_date" class="form-control">
                        <input type="hidden" name="created_at" id="created_at"
                            class="form-control create_application_date">
                        <input type="hidden" name="recovery_module" id="recovery_module"
                            class="form-control recovery_module" value="1">
                        <input type="hidden" name="created_date" id="created_date"
                            class="form-control create_application_date">
                        <input type="hidden" name="title" id="title" value="Pay EMI">
                        <input type="hidden" name="type" id="type" value="loan">

                        <div class="form-group row ml-2">
                            <label for="loan_type" class="col-form-label col-md-2">Loan Type<sup
                                    class="required">*</sup></label>
                            <div class="col-md-4">
                                <select name="loan_type" class="form-control" id="loan_type">
                                    <option value="">Select Loan Type</option>
                                    <option value="L">Loan</option>
                                    <option value="G">Group Loan</option>
                                </select>
                            </div>
                            <label for="account_number" class="col-form-label col-md-2">Account Number<sup
                                    class="required">*</sup></label>
                            <div class="col-md-4">
                                <input type="text" name="account_number" id="account_number" class="form-control"
                                    placeholder="Enter Loan Account Number">
                            </div>
                        </div>
                        <!-- Detail section end -->
                        <!-- Loan detail Section -->
                        <div class="member_details" style="display:none">
                            <div class="card-header header-elements-inline">
                                <h3 class="ml-0">Loan Details</h3>
                                <div class="header-elements">
                                    <div class="list-icons">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row ml-2">
                                <label class="col-form-label col-lg-2">Plan Name</label>
                                <input type="text" name="plan" id="plan" class="form-control col-md-2" readonly>
                                <label class="col-form-label col-lg-2">Customer Id</label>
                                <input type="text" name="customer_id" id="customer_id" class="form-control col-md-2"
                                    readonly>
                                <label class="col-form-label col-lg-2">Name</label>
                                <input type="text" name="name" id="name" class="form-control col-md-2" readonly>
                            </div>
                            <div class="form-group row ml-2">
                                <label class="col-form-label col-lg-2">Sanction Amount</label>
                                <input type="text" name="sanction_amount" id="sanction_amount"
                                    class="form-control col-md-2" readonly>
                                <label class="col-form-label col-lg-2">Emi Amount</label>
                                <input type="text" name="loan_emi_amount" id="loan_emi_amount"
                                    class="form-control col-md-2" readonly>
                                <label class="col-form-label col-lg-2">Recoverd Amount</label>
                                <a href="#" class="form-control col-md-2 recovered-amount-list" target="blank" style="border:none" >
                                <input type="text" name="recovered_amount" id="recovered_amount"
                                     readonly class="form-control" style="color:blue;cursor:pointer;">
                                </a>
                            </div>
                            <div class="form-group row ml-2">
                                <label class="col-form-label col-lg-2">Last Recovery Amount</label>
                                <input type="text" name="last_recovered_amount" id="last_recovered_amount"
                                    class="form-control col-md-2" readonly>
                                <!-- <input type="text" name="last_recover_date" id="last_recover_date" class="form-control col-md-2" readonly> -->
                                <label class="col-form-label col-lg-2" style="color:red">Closure Amount</label>
                                <input type="text" name="closure_amount" id="closure_amount"
                                    class="form-control col-md-2" style="color:red" readonly>
                                <label class="col-form-label col-lg-2" style="color:red">Due Amount</label>
                                <input type="text" style="color:red" name="due_amount" id="due_amount"
                                    class="form-control col-md-2" readonly>
                            </div>

                            <div class="form-group row ml-2">
                            <label class="col-form-label col-lg-2">ECS Type</label>
                                <input type="text" name="ecs_type" id="ecs_type"
                                    class="form-control col-md-2" readonly>
                            </div>
                            <div class="card-header header-elements-inline">
                                <h3 class="ml-0">Recovery Details</h3>
                                <div class="header-elements">
                                    <div class="list-icons">
                                    </div>
                                </div>
                            </div>
                            <!-- Associate Detail -->
                            <div class="form-group row ml-2">
                                <label class="col-form-label col-lg-2" style="color:blue">Branch<sup
                                        class="text-danger">*</sup></label>
                                <div class="col-lg-4">
                                    <select name="loan_branch" class="form-control col-md-9" id="loan_branch" required
                                        style="color:blue">
                                        <option value="">Select Branch</option>
                                    </select>
                                </div>

                                <label for="" class="col-form-label col-lg-2" style="color:blue">Date<sup
                                        class="text-danger">*</sup></label>
                                <div class="col-lg-4">
                                    <input type="text" name="application_date" id="application_date"
                                        class="form-control col-md-9 application_date" placeholder="Deposite Date"
                                        style="color:blue" readonly>

                                </div>
                            </div>

                            <div class="form-group row ml-2">
                                <label class="col-form-label col-lg-2" style="color:blue">Associate Code<sup
                                        class="text-danger">*</sup></label>
                                <div class="col-lg-4">
                                    <input type="text" name="loan_associate_code" id="loan_associate_code"
                                        class="form-control col-md-9" style="color:blue"
                                        placeholder="Enter Associate Code">
                                    <input type="hidden" name="loan_associate_id" id="loan_associate_id"
                                        class="form-control ">
                                </div>
                                <label class="col-form-label col-lg-2" style="color:blue">Associate Name<sup
                                        class="text-danger">*</sup></label>
                                <div class="col-lg-4">
                                    <input type="text" name="loan_associate_name" id="loan_associate_name"
                                        class="form-control col-md-9" readonly style="color:blue"
                                        placeholder="Associate Name">
                                </div>
                            </div>
                            <div class="form-group row ml-2">
                                <label class="col-form-label col-lg-2" style="color:blue">Deposit Amount<sup
                                        class="text-danger">*</sup></label>
                                <div class="col-lg-4">
                                    <input type="text" name="deposite_amount" id="deposite_amount"
                                        class="form-control col-md-9" placeholder="Deposit Amount" style="color:blue">
                                </div>

                                <label class="col-form-label col-lg-2">Payment Mode<sup
                                        class="text-danger">*</sup></label>
                                <div class="col-lg-4">
                                    <select name="loan_emi_payment_mode" class="form-control col-md-9"
                                        id="loan_emi_payment_mode" required>
                                        <option value="">Select Payment Mode</option>
                                        <!-- <option value="2">Cash</option> -->
                                        <option value="0">SSB Account</option>
                                        <option value="1">Bank</option>
                                    </select>
                                </div>
                            </div>
                            <!-- Associate Detail End-->
                        </div>
                        <!-- Loan detail Section End -->

                        <!-- Bank Mode Start -->
                        <div class="bank-mode ml-3" style="display:none">
                            <div class="col-lg-12 bank_mode">
                                <div class=" row">
                                    <h4>Payment Details</h4>
                                </div>
                            </div>
                            <!-- <div class="form-group row bank_mode" id="bank_mode">
                                <label class="col-form-label col-lg-2">Bank Name<sup class="text-danger">*</sup></label>
                                <div class="col-lg-4">
                                    <select class="form-control" name="company_bank" id="company_bank">
                                        <option value="">---Please Select Bank ---</option>
                                    </select>
                                </div>
                                <label class="col-form-label col-lg-2">Bank Account<sup
                                        class="text-danger">*</sup></label>
                                <div class="col-lg-4">
                                    <select class="form-control" name="bank_account_number" id="bank_account_number">
                                        <option value="">---Please Select Bank Account --</option>
                                    </select>
                                </div>
                            </div> -->
                            <div class="form-group row" id="transaction_mode">
                                <label class="col-form-label col-lg-2">Payment Mode<sup
                                        class="text-danger">*</sup></label>
                                <div class="col-lg-4">
                                    <select class="form-control" name="bank_transfer_mode" id="bank_transfer_mode">
                                        <option value="">---Please Select Payment Mode --</option>
                                        <option value="0">Cheque</option>
                                        <!-- <option value="1">Online Transaction</option> -->
                                    </select>
                                </div>
                            </div>
                        </div>
                        <!-- Bank Mode End -->
                        <!-- Cheque Type Start -->
                        <div class="cheque-mode ml-3" style="display:none">
                            <div class="col-lg-12 cheque_mode">
                                <div class=" row">
                                    <h4>Cheque Details</h4>
                                </div>
                            </div>
                            <div class="form-group row cheque_mode">
                                <label class="col-form-label col-lg-2">Cheque Number<sup
                                        class="text-danger">*</sup></label>
                                <div class="col-lg-4">
                                    <select name="customer_cheque" id="customer_cheque" class="form-control">
                                        <option value="">----Please Select----</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <!-- Cheque Type End  -->

                        <!-- cheque Details start -->

                        <div class="cheque-detail-show ml-3" id="cheque-detail-show" style="display:none">

                            <div class="form-group row">
                                <label class="col-form-label col-lg-2">Bank Name</label>
                                <div class="col-lg-4">
                                    <input type="text" name="customer_bank_name" id="customer_bank_name"
                                        class="form-control" readonly>
                                </div>
                                <label class="col-form-label col-lg-2">Deposit bank Account</label>
                                <div class="col-lg-4">
                                    <input type="text" name="company_bank_account_number"
                                        id="company_bank_account_number" class="form-control" readonly>
                                </div>
                            </div>
                            <div class=" form-group row">
                                <label class="col-form-label col-lg-2">Branch Name</label>
                                <div class="col-lg-4">
                                    <input type="text" name="customer_branch_name" id="customer_branch_name"
                                        class="form-control" readonly>
                                </div>
                                <label class="col-form-label col-lg-2">Cheque Date</label>
                                <div class="col-lg-4">
                                    <input type="text" name="cheque-date" id="cheque-date" class="form-control"
                                        readonly>
                                </div>
                            </div>
                            <div class=" form-group row">
                                <label class="col-form-label col-lg-2">Cheque Amount</label>
                                <div class="col-lg-4">
                                    <div class="rupee-img"></div>
                                    <input type="text" name="cheque_total_amount" id="cheque-amount"
                                        class="form-control rupee-txt" readonly>
                                </div>
                                <label class="col-form-label col-lg-2">Deposit Bank</label>
                                <div class="col-lg-4">
                                    <input type="text" name="cheque_company_bank" id="cheque_company_bank"
                                        class="form-control" readonly>
                                </div>
                            </div>
                        </div>

                        
                        <!-- cheque Details End -->
                        <!-- SSB Mode -->
                        <div class="ssb-mode ml-3" style="display:none">
                            <div class="col-lg-12 ssb_mode">
                                <div class=" row">
                                    <h4>SSB Details </h4>
                                </div>
                            </div>
                            <div class="form-group row ssb_mode">
                                <label class="col-form-label col-lg-2">SSB Account Number<sup
                                        class="text-danger">*</sup></label>
                                <div class="col-lg-4">
                                    <input type="hidden" name="ssb_id" id="ssb_id" class="" readonly="true">
                                    <input type="text" name="ssb_account_number" id="ssb_account_number"
                                        class="form-control col-md-9" readonly="true">
                                </div>
                                <label class="col-form-label col-lg-2">Account Balance<sup
                                        class="text-danger">*</sup></label>
                                <div class="col-lg-4">
                                    <input type="text" name="ssb_account" id="ssb_account" class="form-control col-md-9"
                                        readonly="true">
                                </div>
                            </div>
                        </div>
                        <!-- SSB Mode End-->
                        <!-- submit button -->
                        <div class="container text-center">
                            <button type="submit" class="btn btn-primary newId submitmember mt-4 otp"
                                id="submitBtn">Submit</button>
                        </div>
                    </form>
                </div>
                <!-- /basic layout -->
            </div>
        </div>
    </div>
</div>
</div>

<!-- OTP VARIFICATION -->
<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog" role="document">
        <div class="d-flex justify-content-center align-items-center container">
            <div class="card py-3 px-3 modal-content">
                <div class="">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <h3 class="m-0">OTP Verification</h3><span class="mobile-text">Enter the code we just send on your
                    mobile
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
                        <input type="submit" name="otpSubmit" value="Verify OTP" id="verify"
                            class="btn btn-primary mt-4">
                    </div>
                    <span class="timer d-flex justify-content-center">
                        <span id="counter">
                            <i class="fa-regular fa-clock fa-beat"></i></span>
                    </span>
                </form>
                <div class="text-center mt-5"><span class="d-block mobile-text">Don't receive the code?</span>
                    <span class="font-weight-bold text-primary cursor" style="cursor: pointer;"
                        id="resend">Resend</span>
                </div>
            </div>
        </div>
    </div>
</div>







<!-- Online mode -->
<!-- <div class="online-mode ml-3" style="display:none">
                            <div class="col-lg-12 utr_mode">
                                <div class=" row">
                                    <h4>Online Transaction</h4>
                                </div>
                            </div>
                            <div class="form-group row utr_mode">
                                <label class="col-form-label col-lg-2">UTR Number<sup
                                        class="text-danger">*</sup></label>
                                <div class="col-lg-4">
                                    <input type="text" name="utr_transaction_number" class="form-control">
                                </div>
                                <label class="col-form-label col-lg-2">RTGS/NEFT Charge<sup class="text-danger">*</sup></label>
                                <div class="col-lg-4">
                                    <input type="text" name="neft_charge" class="form-control">
                                </div>
                            </div>
                        </div> -->
<!-- Online mode End-->