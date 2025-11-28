@extends('templates.admin.master')
@php
    $dropDown = $company;
    $filedTitle = 'Company';
    $name = 'company_id';
@endphp
@section('content')
    <div class="content">
        <div class="row">
            {{-- Server side validaiton --}}
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
            <form action="{!! route('admin.voucher.save') !!}" method="post" enctype="multipart/form-data" id="voucher_save"
                name="voucher_save">
                @csrf
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card bg-white">
                            <div class="card-body">
                                <h3 class="card-title mb-3">Receive Voucher Request</h3>
                                <div class="row">
                                    @include('templates.GlobalTempletes.new_role_type', [
                                        'dropDown' => $dropDown,
                                        'filedTitle' => $filedTitle,
                                        'name' => $name,
                                        'value' => '',
                                        'multiselect' => 'false',
                                        'design_type' => 4,
                                        'branchShow' => true,
                                        'branchName' => 'branch_id',
                                        'apply_col_md' => false,
                                        'multiselect' => false,
                                        'placeHolder1' => 'Please Select Company',
                                        'placeHolder2' => 'Please Select Branch',
                                    ])
                                    <div class="col-lg-4">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-4">Date<sup class="required">*</sup></label>
                                            <div class="col-lg-12 error-msg">
                                                <input type="text" id="date" name="date" class="form-control "
                                                    readonly="true" autocomplete="off">
                                                <input type="hidden" id="create_application_date"
                                                    name="create_application_date"
                                                    class="form-control create_application_date" readonly="true"
                                                    autocomplete="off">
                                            </div>
                                        </div>
                                    </div>

                                    <!-- <div class="col-lg-6">

                                       <div class="form-group row">

                                          <label class="col-form-label col-lg-3">Branch<sup class="required">*</sup></label>

                                          <div class="col-lg-9 error-msg">

                                             <select name="branch_id" id="branch_id" class="form-control" >

                                                        <option value="">----  Select Branch----</option>

                                                    </select>

                                             <input type="hidden" class="form-control created_at " name="created_at" id="created_at"  >

                                             <input type="hidden" class="form-control create_application_date " name="create_application_date" id="create_application_date"  >

                                          </div>

                                       </div>

                                    </div> -->
                                    <div class="col-lg-4">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-4">Branch Code<sup
                                                    class="required">*</sup></label>
                                            <div class="col-lg-12 error-msg">
                                                <input type="text" name="branch_code" id="branch_code"
                                                    class="form-control" placeholder="Branch Code" readonly="">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-4">Account Head<sup
                                                    class="required">*</sup></label>
                                            <div class="col-lg-12 error-msg">
                                                <select name="head" id="head" class="form-control" readonly>
                                                    <option value="">---- Select Head----</option>
                                                    <option value="32">Penal Interest</option>
                                                    <option value="96">Eli Loan</option>
                                                    <option value="122">Investment Plan Stationery Charge</option>
                                                    <option value="86">Indirect Expense</option>
                                                    <option value="87">Commission</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-4 indirect_sub_head" style="display: none;">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-6">Account Sub Head<sup
                                                    class="required">*</sup></label>
                                            <div class="col-lg-12 error-msg">
                                                <select name="sub_head" id="sub_head" class="form-control" readonly>
                                                    <option value="">---- Select Sub Head----</option>
                                                    @foreach ($sub_head as $item)
                                                    <option value="{{$item->head_id}}" data-company="{{$item->company_id}}" class="sub_headd" style="display: none;">{{$item->sub_head}}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-4 associate_code" style="display: none;">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-4">Associate Code<sup
                                                    class="required">*</sup></label>
                                            <div class="col-lg-12 error-msg">
                                                <input type="text" name="asso_code" id="asso_code"
                                                    class="form-control" required title="Please enter associate code">
                                                <input type="hidden" name="asso_id" id="asso_id">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-4 associate_name" style="display: none;">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-6">Associate Name<sup
                                                    class="required">*</sup></label>
                                            <div class="col-lg-12 error-msg">
                                                <input type="text" name="asso_name" id="asso_name"
                                                    class="form-control" readonly>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-4 director" id="director" style="display:none;">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-4">Director<sup
                                                    class="required">*</sup></label>
                                            <div class="col-lg-12 error-msg">
                                                <select name="director_id" id="director_id" class="form-control" readonly>
                                                    <option value="">---- Select Director----</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-4 director" id="director" style="display:none;">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-4">Director Created Date<sup
                                                    class="required">*</sup></label>
                                            <div class="col-lg-12 error-msg">
                                                <input type="text" name="register_date_director"
                                                    id="register_date_director" class="form-control" readonly>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-6 shareholder" id="shareholder" style="display:none;">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-3">Shareholder<sup
                                                    class="required">*</sup></label>
                                            <div class="col-lg-9 error-msg">
                                                <select name="shareholder_id" id="shareholder_id" class="form-control"
                                                    readonly>
                                                    <option value="">---- Select Shareholder----</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-4 shareholder" id="shareholder" style="display:none;">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-4">Shareholder Created Date<sup
                                                    class="required">*</sup></label>
                                            <div class="col-lg-12 error-msg">
                                                <input type="text" name="register_date_shareholder"
                                                    id="register_date_shareholder" class="form-control" readonly>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-4  bank" style="display:none;">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-4">Bank<sup
                                                    class="required">*</sup></label>
                                            <div class="col-lg-12 error-msg">
                                                <select name="bank_id" id="bank_id" class="form-control">
                                                    <option value="">---- Please Select----</option>
                                                    @foreach ($samraddh_bank as $bank)
                                                        <option value="{{ $bank->id }}">{{ $bank->bank_name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-4 bank" style="display:none;">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-4">Bank A/c<sup
                                                    class="required">*</sup></label>
                                            <div class="col-lg-12 error-msg">
                                                <select name="bank_account" id="bank_account" class="form-control">
                                                    <option value="">---- Please Select----</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-4  bank" style="display:none;">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-4">Bank Created Date<sup
                                                    class="required">*</sup></label>
                                            <div class="col-lg-12 error-msg">
                                                <input type="text" name="bank_register_date" id="bank_register_date"
                                                    class="form-control" readonly>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-4  bank" style="display:none;">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-4">Available Balance<sup
                                                    class="required">*</sup></label>
                                            <div class="col-lg-12 error-msg">
                                                <input type="text" name="bank_balance" id="bank_balance"
                                                    class="form-control" readonly>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-4 penal" style="display:none;">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-4">Employee Code<sup
                                                    class="required">*</sup></label>
                                            <div class="col-lg-12 error-msg">
                                                <input type="text" name="emp_code" id="emp_code"
                                                    class="form-control">
                                                <input type="hidden" name="emp_id" id="emp_id">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-4 penal" style="display:none;">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-4">Employee Created Date<sup
                                                    class="required">*</sup></label>
                                            <div class="col-lg-12 error-msg">
                                                <input type="text" name="emp_date" id="emp_date"
                                                    class="form-control" readonly>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-4 penal" style="display:none;">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-4">Employee Name<sup
                                                    class="required">*</sup></label>
                                            <div class="col-lg-12 error-msg">
                                                <input type="text" name="emp_name" id="emp_name"
                                                    class="form-control" readonly>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-4 eli_loan" id="eli_loan" style="display:none;">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-4">Eli Loan<sup
                                                    class="required">*</sup></label>
                                            <div class="col-lg-12 error-msg">
                                                <select name="eli_loan_id" id="eli_loan_id" class="form-control"
                                                    readonly>
                                                    <option value="">---- Select Eli Loan----</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-4 eli_loan" id="eli_loan_date1" style="display:none;">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-4">Eli Loan Created Date<sup
                                                    class="required">*</sup></label>
                                            <div class="col-lg-12 error-msg">
                                                <input type="text" name="eli_loan_date" id="eli_loan_date"
                                                    class="form-control" readonly>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-4 payment" >
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-4">Payment Mode<sup>*</sup></label>
                                            <div class="col-lg-12 error-msg">
                                                <select name="payment_mode" id="payment_mode" class="form-control">
                                                    <option value="">----Select Receive Mode ----</option>
                                                    <option value="0" class="payment-mode-cash">Cash</option>
                                                    <option value="1" class="payment-mode-type">Cheque</option>
                                                    <option value="2" class="payment-mode-type"> Online Transaction
                                                    </option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-4" style="">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-6">Receive Amount<sup>*</sup></label>
                                            <div class="col-lg-12 error-msg">
                                                <input type="text" name="amount" id="amount" class="form-control"
                                                    placholder="0.00">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-4 member-section" style="display: none">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-6">Member Id<sup>*</sup></label>
                                            <div class="col-lg-12 error-msg">
                                                <input type="text" name="member_id" id="member_id"
                                                    class="form-control" required="">
                                                <input type="hidden" name="member_auto_id" id="member_auto_id"
                                                    class="form-control">
                                                <input type="hidden" name="member_register_date"
                                                    id="member_register_date">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-4 member-section" style="display: none">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-6">Member Name<sup>*</sup></label>
                                            <div class="col-lg-12 error-msg">
                                                <input type="text" name="member_name" id="member_name"
                                                    class="form-control" readonly="">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-4">Particular<sup
                                                    class="required">*</sup></label>
                                            <div class="col-lg-12 error-msg">
                                                <textarea name="particular" id="particular" class="form-control" placeholder="particular"></textarea>
                                            </div>
                                        </div>
                                    </div>
                             
                                    <div class="col-lg-12 payment_mode_cash" style="display: none">
                                        <div class=" row">
                                            <div class="col-lg-12">
                                                <h4 class="mb-3">Cash</h4>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-4 payment_mode_cash" style="display: none">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-4">DayBook<sup>*</sup></label>
                                            <div class="col-lg-12 error-msg">
                                                <select name="daybook" id="daybook" class="form-control">
                                                    <option value="0"> Cash </option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-4 payment_mode_cash" style="display: none">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-4">Total Balance<sup>*</sup></label>
                                            <div class="col-lg-12 error-msg">
                                                <input type="text" name="branch_total_balance"
                                                    id="branch_total_balance" class="form-control" readonly
                                                    placholder="0.00">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-12 payment_mode_online" style="display: none">
                                        <div class=" row">
                                            <div class="col-lg-12">
                                                <h4 class="mb-3">Online Transaction</h4>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-6 payment_mode_online" style="display: none">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-3"> Receive Bank<sup>*</sup></label>
                                            <div class="col-lg-9 error-msg">
                                                <select name="online_bank" id="online_bank" class="form-control">
                                                    <option value="">---- Select Bank----</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-6 payment_mode_online" style="display: none">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-3"> Receive Bank A/c<sup>*</sup></label>
                                            <div class="col-lg-9 error-msg">
                                                <select name="online_bank_ac" id="online_bank_ac" class="form-control">
                                                    <option value="">---- Select Bank A/c----</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-6 payment_mode_online" style="display: none">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-3">UTR/Transaction Date<sup>*</sup></label>
                                            <div class="col-lg-9 error-msg">
                                                <input type="text" name="utr_date" id="utr_date"
                                                    class="form-control">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-6 payment_mode_online" style="display: none">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-3">UTR No./Transaction
                                                No.<sup>*</sup></label>
                                            <div class="col-lg-9 error-msg">
                                                <input type="text" name="utr_no" id="utr_no" class="form-control"
                                                    placeholder="UTR No./Transaction No. ">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-6 payment_mode_online" style="display: none">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-3">Transaction Bank
                                                Name<sup>*</sup></label>
                                            <div class="col-lg-9 error-msg">
                                                <input type="text" name="transaction_bank" id="transaction_bank"
                                                    class="form-control" placeholder="Transaction Bank ">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-6 payment_mode_online" style="display: none">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-3">Transaction Bank A/c<sup>*</sup></label>
                                            <div class="col-lg-9 error-msg">
                                                <input type="text" name="transaction_bank_ac" id="transaction_bank_ac"
                                                    class="form-control" placeholder="Transaction Bank A/c">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-6 payment_mode_online" style="display: none">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-3">Upload Transaction Slip</label>
                                            <div class="col-lg-9 error-msg">
                                                <input type="file" class="" name="bank_slip" id="bank_slip" />
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-12 payment_mode_cheque" style="display: none">
                                        <div class=" row">
                                            <div class="col-lg-12">
                                                <h4 class="mb-3">Cheque</h4>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-6 payment_mode_cheque" style="display: none">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-3"> Cheque No.<sup>*</sup></label>
                                            <div class="col-lg-9 error-msg">
                                                <select name="cheque_no" id="cheque_no" class="form-control">
                                                    <option value="">---- Select Cheque----</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-6 cheque" style="display: none">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-3">Cheque Number<sup>*</sup></label>
                                            <div class="col-lg-9 error-msg">
                                                <input type="text" name="cheque_number" id="cheque_number"
                                                    class="form-control" readonly>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-6 cheque" style="display: none">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-3">Cheque Amount<sup>*</sup></label>
                                            <div class="col-lg-9 error-msg">
                                                <input type="text" name="cheque_amount" id="cheque_amount"
                                                    class="form-control" readonly>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-6 cheque" style="display: none">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-3">Party Name<sup>*</sup></label>
                                            <div class="col-lg-9 error-msg">
                                                <input type="text" name="cheque_party_name" id="cheque_party_name"
                                                    class="form-control" readonly>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-6 cheque" style="display: none">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-3">Party Bank <sup>*</sup></label>
                                            <div class="col-lg-9 error-msg">
                                                <input type="text" name="cheque_party_bank" id="cheque_party_bank"
                                                    class="form-control" readonly>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-6 cheque" style="display: none">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-3">Party Bank A/c<sup>*</sup></label>
                                            <div class="col-lg-9 error-msg">
                                                <input type="text" name="cheque_party_bank_ac"
                                                    id="cheque_party_bank_ac" class="form-control" readonly>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-6 cheque" style="display: none">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-3">Receive Bank <sup>*</sup></label>
                                            <div class="col-lg-9 error-msg">
                                                <input type="text" name="cheque_deposit_bank" id="cheque_deposit_bank"
                                                    class="form-control" readonly>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-6 cheque" style="display: none">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-3">Receive Bank A/c <sup>*</sup></label>
                                            <div class="col-lg-9 error-msg">
                                                <input type="text" name="cheque_deposit_bank_ac"
                                                    id="cheque_deposit_bank_ac" class="form-control" readonly>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-6 cheque" style="display: none">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-3">Cheque Date<sup>*</sup></label>
                                            <div class="col-lg-9 error-msg">
                                                <input type="text" name="cheque_deposit_date" id="cheque_deposit_date"
                                                    class="form-control" readonly>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="gst_charge col-lg-4" style="display: none;">
                                        <div class="form-group row" style="">
                                            <label class="col-form-label col-lg-6">Cgst Stationary
                                                Charge<sup>*</sup></label>
                                            <div class="col-lg-12 error-msg">
                                                <input type="text" name="cgst_stationary_charge"
                                                    id="cgst_stationary_charge" class="form-control" readonly>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="gst_charge col-lg-4" style="display: none;">
                                        <div class="form-group row" style="">
                                            <label class="col-form-label col-lg-6">Sgst Stationary
                                                Charge<sup>*</sup></label>
                                            <div class="col-lg-12 error-msg">
                                                <input type="text" name="sgst_stationary_charge"
                                                    id="sgst_stationary_charge" class="form-control" readonly>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="igst_charge col-lg-4" style="display: none;">
                                        <div class="form-group row" style="">
                                            <label class="col-form-label col-lg-6">Igst Stationary
                                                Charge<sup>*</sup></label>
                                            <div class="col-lg-12 error-msg">
                                                <input type="text" name="igst_stationary_charge"
                                                    id="igst_stationary_charge" class="form-control" readonly>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-12">
                    <div class="card bg-white">
                        <div class="card-body">
                            <div class="text-center">
                                <button type="submit" class="btn btn-primary">Submit</button>
                            </div>
                        </div>
                    </div>
                </div>
        </div>
        </form>
    </div>
   </div>
@stop
@section('script')
    @include('templates.admin.voucher.partials.script')
@stop
