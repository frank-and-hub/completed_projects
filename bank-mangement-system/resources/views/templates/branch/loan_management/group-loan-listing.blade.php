@extends('layouts/branch.dashboard')
@section('content')
<style type="text/css">
    .datepicker.dropdown-menu {
        z-index: 9999999 !important;
    }
</style>
<div class="container-fluid mt--6">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-lg-12">
                <div class="card bg-white">
                    <div class="card-body">
                        <div class="">
                            <h3 class="">Group Loan Listing</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header header-elements-inline">
                        <h3 class="card-title font-weight-semibold">Search Filter</h3>
                    </div>
                    <div class="card-body">
                        <form action="#" method="post" enctype="multipart/form-data" id="grouploanfilter"
                            name="grouploanfilter">
                            @csrf
                            <div class="row">
                                <style>
                                    .required {
                                        color: red;
                                    }
                                </style>
                                @include('templates.GlobalTempletes.role_type', [
                                'dropDown' => $branchCompany[Auth::user()->branches->id],
                                'name' => 'group_company_id',
                                'apply_col_md' => false,
                                'filedTitle' => 'Company',
                                ])
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Loan Plan <span
                                                class="required">*</span></label>
                                        <div class="col-lg-12 error-msg">
                                            <!-- <div class="input-group"> -->
                                            <select class="form-control" id="plan" name="plan">
                                                <option value="">----Select plan----</option>

                                            </select>
                                            <!-- </div> -->
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Customer ID</label>
                                        <div class="col-lg-12 error-msg">
                                            <!-- <div class="input-group"> -->
                                            <input type="text" class="form-control customer_id" name="customer_id">
                                            <!-- </div> -->
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Date From</label>
                                        <div class="col-lg-12 error-msg">
                                            <div class="input-group">
                                                <input type="text" class="form-control from_date" name="date_from"
                                                    id="date_from">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Date To</label>
                                        <div class="col-lg-12 error-msg">
                                            <div class="input-group">
                                                <input type="text" class="form-control to_date" name="date_to"
                                                    id="to_date">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Loan Account Number </label>
                                        <div class="col-lg-12 error-msg">
                                            <input type="text" class="form-control  " name="loan_account_number"
                                                id="loan_account_number">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Member Name </label>
                                        <div class="col-lg-12 error-msg">
                                            <input type="text" class="form-control  " name="member_name"
                                                id="member_name">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Member ID </label>
                                        <div class="col-lg-12 error-msg">
                                            <input type="text" name="member_id" id="member_id" class="form-control">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Associate code </label>
                                        <div class="col-lg-12 error-msg">
                                            <input type="text" name="associate_code" id="associate_code"
                                                class="form-control">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Group Leader ID </label>
                                        <div class="col-lg-12 error-msg">
                                            <input type="text" name="group_loan_common_id" id="group_loan_common_id"
                                                class="form-control">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Status </label>
                                        <div class="col-lg-12 error-msg">
                                            <select class="form-control" id="status" name="status">
                                                <option value="">Select status</option>
                                                <option value="1">Approved</option>
                                                <option value="0">Pending</option>
                                                <option value="3" selected>Clear</option>
                                                <option value="4">Due</option>
                                                <option value="5">Rejected</option>
                                                <option value="6">Hold</option>
                                                <option value="7">Approved but Hold</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group row">
                                        <div class="col-lg-12 text-right">
                                            <input type="hidden" name="is_search" id="is_search" value="no">
                                            <input type="hidden" name="group_loan_recovery_export"
                                                id="group_loan_recovery_export" value="">
                                            <button type="submit" class=" btn btn-primary legitRipple">Submit</button>
                                            <button type="button" class="btn btn-gray legitRipple" id="reset_form"
                                                onClick="resetGroupLoanForm()">Reset </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="row shadow d-none">
            <div class="col-lg-12">
                <div class="card bg-white shadow">
                    <div class="card bg-white shadow">
                        <div class="card-header bg-transparent">
                            <div class="row">
                                <div class="col-md-8">
                                    <h3 class="mb-0 text-dark">Loans</h3>
                                </div>
                                <div class="col-md-4 text-right">
                                    <button type="button" class="btn btn-primary legitRipple export-group-loan ml-2"
                                        data-extension="0" style="float: right;">Export xslx</button>
                                </div>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table id="group-loan-listing" class="table table-flush">
                                <thead class="">
                                    <tr>
                                        <th>S/N</th>
                                        <th>Action</th>
                                        <th>BR Name</th>
                                        <th>A/C No.</th>
                                        <th>Group Loan Common Id</th>
                                        <th>Member Name</th>
                                        <th>Customer Id </th>
                                        <th>Member Id </th>
                                        <th>Total Deposit</th>
                                        <th>Loan Type</th>
                                        <th>Tenure</th>
                                        <th>Emi Amount</th>
                                        <th>Transfer Amount</th>
                                        <th>Transfer Date</th>
                                        <th>Loan Amount</th>
                                        <th>File Charges</th>
                                        <th>Insurance Charge</th>
                                        <th>File Charge Payment Mode</th>
                                        <th>CGST INSURANCE CHARGE</th>
                                        <th>SGST INSURANCE CHARGE</th>
                                        <th>IGST INSURANCE CHARGE</th>
                                        <th>CGST FILE CHARGE</th>
                                        <th>SGST FILE CHARGE</th>
                                        <th>IGST FILE CHARGE</th>
                                        <th>CGST ECS CHARGE</th>
                                        <th>SGST ECS CHARGE</th>
                                        <th>IGST ECS CHARGE</th>
                                        <th>Outstanding Amount</th>
                                        <th>Last Recovery Date</th>
                                        <th>Associate Code</th>
                                        <th>Associate Name</th>
                                        <th>Bank Name</th>
                                        <th>Bank Account Number</th>
                                        <th>IFSC Code</th>
                                        <th>Total Payment</th>
                                        <th>Approved Date</th>
                                        <th>Sanction Date</th>
                                        <th>Application Date</th>
                                        <th>Collector Code</th>
                                        <th>Collector Name</th>
                                        <th>Reason</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal fade" id="rejection-view" tabindex="-1" role="dialog" aria-labelledby="modal-form"
            aria-hidden="true">
            <div class="modal-dialog modal- modal-dialog-centered modal-sm" role="document"
                style="max-width: 600px !important; ">
                <div class="modal-content">
                    <div class="modal-body p-0">
                        <div class="card bg-white border-0 mb-0">
                            <div class="card-header bg-transparent pb-2ß">
                                <div class="text-dark text-center mt-2 mb-3">View Admin Remark</div>
                            </div>
                            <div class="card-body px-lg-5 py-lg-5">
                                <div class="form-group row">
                                    <!-- <label class="col-form-label col-lg-2">Corrections</label> -->
                                    <div class="col-lg-12 loan-rejected-description">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal fade" id="pay-loan-emi" tabindex="-1" role="dialog" aria-labelledby="modal-form"
            aria-hidden="true">
            <div class="modal-dialog modal- modal-dialog-centered modal-sm" role="document"
                style="max-width: 800px !important; ">
                <div class="modal-content">
                    <div class="modal-body p-0">
                        <div class="card bg-white border-0 mb-0">
                            <div class="card-header bg-transparent pb-2ß">
                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                                <div class="text-dark text-center mt-2 mb-3"><span>Pay Your EMI</div>
                            </div>
                            <div class="card-body px-lg-5 py-lg-5">
                                <form action="{{ route('grouploan.depositeloanemi') }}" method="post" id="loan_emi"
                                    name="loan_emi">
                                    @csrf
                                    <input type="hidden" name="loan_id" id="loan_id">
                                    @php
                                    $stateid = getBranchState(Auth::user()->username);
                                    @endphp
                                    <input type="hidden" name="created_at" id="created_at"
                                        value="{{ checkMonthAvailability(date('d'), date('m'), date('Y'), $stateid) }}">
                                    <input type="hidden" name="created_date" id="created_date" value="">
                                    <input type="hidden" name="ssb_id" id="ssb_id">
                                    <input type="hidden" name="associate_member_id" id="associate_member_id">
                                    <input type="hidden" name="branch" value="{{ $bId }}">
                                    <input type="hidden" name="companyId" id="companyId">
                                    <input type="hidden" name="type" id="type" value="group">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-2">Deposit Date<sup>*</sup></label>
                                        <div class="col-lg-4">
                                            <input type="text" name="application_date" class="form-control "
                                                autocomplete="off"
                                                value="{{ headerMonthAvailability(date('d'), date('m'), date('Y'), $stateid) }}"
                                                readonly>
                                        </div>
                                        <label class="col-form-label col-lg-2">Due Amount<sup>*</sup></label>
                                        <div class="col-lg-4">
                                            <input type="text" name="due_amount" id="due_amount" class="form-control"
                                                readonly="">
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-2">Associate Code<sup>*</sup></label>
                                        <div class="col-lg-4">
                                            <input type="text" name="loan_associate_code" id="loan_associate_code"
                                                class="form-control" required="">
                                        </div>
                                        <label class="col-form-label col-lg-2">Associate Name<sup>*</sup></label>
                                        <div class="col-lg-4">
                                            <input type="text" name="loan_associate_name" id="loan_associate_name"
                                                class="form-control" readonly="">
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-2">EMI Amount</label>
                                        <div class="col-lg-4">
                                            <input type="text" name="loan_emi_amount" id="loan_emi_amount"
                                                class="form-control" readonly="">
                                        </div>
                                        <label class="col-form-label col-lg-2">Deposite Amount</label>
                                        <div class="col-lg-4">
                                            <input type="text" name="deposite_amount" id="deposite_amount"
                                                class="form-control">
                                            <label class="error ssbamount-error"></label>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-2">Closer Amount</label>
                                        <div class="col-lg-4">
                                            <input type="text" name="outstanding_amount" id="outstanding_amount"
                                                class="form-control" readonly="">
                                        </div>
                                        {{-- <label class="col-form-label col-lg-2">Penalty Amount</label>
                                        <div class="col-lg-4">
                                            <input type="text" name="penalty_amount" id="penalty_amount" value="0"
                                                class="form-control" readonly="">
                                        </div> --}}
                                    </div>
                                    <div class="form-group row gst1" style="display:none;">
                                        <label class="col-form-label col-lg-2" id="label1"></label>
                                        <div class="col-lg-4 gst1">
                                            <input type="text" name="cgst_amount" id="cgst_amount" class="form-control"
                                                readonly="">
                                        </div>
                                        <label class="col-form-label col-lg-2" id="label2"></label>
                                        <div class="col-lg-4 gst1">
                                            <input type="text" name="sgst_amount" id="sgst_amount" class="form-control"
                                                readonly="">
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-2">Recovered Amount</label>
                                        <div class="col-lg-4">
                                            <input type="text" name="recovered_amount" id="recovered_amount"
                                                class="form-control" readonly="">
                                        </div>
                                        <label class="col-form-label col-lg-2">Last Recovered Amount</label>
                                        <div class="col-lg-4">
                                            <input type="text" name="last_recovered_amount" id="last_recovered_amount"
                                                class="form-control" readonly="">
                                        </div>
                                    </div>
                                    <div class="form-group row gst2" style="display:none;">
                                        <label class="col-form-label col-lg-2" id="label3"></label>
                                        <div class="col-lg-4">
                                            <input type="text" name="igst_amount" id="igst_amount" class="form-control"
                                                readonly="">
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <!-- <label class="col-form-label col-lg-2">Select Branch</label>
                                                                                <div class="col-lg-4">
                                                                                  <select class="form-control" name="branch" id="loan_branch">
                                                                                        <option value="">----Select Branch----</option>
                                                                                        @foreach (App\Models\Branch::pluck('name', 'id') as $key => $val)
    <option value="{{ $key }}"  >{{ $val }}</option>
    @endforeach
                                                                                  </select>
                                                                                </div> -->
                                        <label class="col-form-label col-lg-2">Payment Mode</label>
                                        <div class="col-lg-4">
                                            <select class="form-control" name="loan_emi_payment_mode"
                                                id="loan_emi_payment_mode">
                                                <option value="">----Select----</option>
                                                <option value="2">Cash</option>
                                                <option value="0">SSB Account</option>
                                                <option value="1">Bank</option>
                                            </select>
                                        </div>
                                        <label class="col-form-label col-lg-2 ssb-account" style="display: none;">SSB
                                            Amount</label>
                                        <div class="col-lg-4 ssb-account" style="display: none;">
                                            <input type="text" name="ssb_account" id="ssb_account" class="form-control"
                                                readonly="">
                                        </div>
                                    </div>
                                    <div class="form-group row ssb-account" style="display: none;">
                                        <label class="col-form-label col-lg-2">SSB Account Number</label>
                                        <div class="col-lg-4 ssb-account">
                                            <input type="text" name="ssb_account_number" id="ssb_account_number"
                                                class="form-control" readonly="">
                                        </div>
                                    </div>
                                    <!--  <h5 class="card-title font-weight-semibold other-bank" style="display: none;">Customer Bank details</h5>
                                                                            <div class="form-group row other-bank" style="display: none;">
                                                                                <label class="col-form-label col-lg-2">Bank Name<sup>*</sup></label>
                                                                                <div class="col-lg-4">
                                                                                    <input type="text" name="customer_bank_name" class="form-control" id="customer_bank_name">
                                                                                </div>
                                                                                <label class="col-form-label col-lg-2">Bank A/c No.<sup>*</sup></label>
                                                                                <div class="col-lg-4">
                                                                                    <input type="text" name="customer_bank_account_number" class="form-control" id="customer_bank_account_number">
                                                                                </div>
                                                                            </div>
                                                     -->
                                    <!--  <div class="form-group row other-bank" style="display: none;">
                                                                                <label class="col-form-label col-lg-2">Branch Name<sup>*</sup></label>
                                                                                <div class="col-lg-4">
                                                                                    <input type="text" name="customer_branch_name" class="form-control" id="customer_branch_name">
                                                                                </div>
                                                                                <label class="col-form-label col-lg-2">IFSC code<sup>*</sup></label>
                                                                                <div class="col-lg-4">
                                                                                    <input type="text" name="customer_ifsc_code" class="form-control" id="customer_ifsc_code">
                                                                                </div>
                                                                            </div> -->
                                    <div class="form-group row other-bank" style="display: none;">
                                        <label class="col-form-label col-lg-2">Select Mode<sup>*</sup></label>
                                        <div class="col-lg-4">
                                            <select name="bank_transfer_mode" id="bank_transfer_mode"
                                                class="form-control">
                                                <option value="">----Please Select----</option>
                                                <option value="0">Cheque</option>
                                                <option value="1">Online</option>
                                            </select>
                                        </div>
                                        <label class="col-form-label col-lg-2 cheque-transaction">Cheque
                                            No.<sup>*</sup></label>
                                        <div class="col-lg-4 cheque-transaction">
                                            <select name="customer_cheque" id="customer_cheque" class="form-control">
                                                <!--   <option value="">----Please Select----</option>
                                                                                        @foreach ($cheques as $val)
    <option value="{{ $val->cheque_no }}" class="{{ $val->deposit_account_id }}-c-cheque c-cheque" style="display: none;">{{ $val->cheque_no }}</option>
    @endforeach -->
                                            </select>
                                            <!--  <input type="text" name="customer_cheque" id="customer_cheque" class="form-control"> -->
                                        </div>
                                        <label class="col-form-label col-lg-2 online-transaction">UTR number /
                                            Transaction Number<sup>*</sup></label>
                                        <div class="col-lg-4 online-transaction">
                                            <input type="text" name="utr_transaction_number" id="utr_transaction_number"
                                                class="form-control">
                                        </div>
                                    </div>
                                    <div class="  " id="cheque-detail-show" style="display: none;">
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
                                                <input type="text" name="cheque-date" id="cheque-date"
                                                    class="form-control" readonly>
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
                                    <div class="form-group row other-bank">
                                        <!-- <label class="col-form-label col-lg-2 cheque-transaction" style="display: none">Amount</label>
                                                                                <div class="col-lg-4 cheque-transaction" style="display: none">
                                                                                    <input type="text" name="cheque_total_amount" id="cheque_total_amount" class="form-control" value="" readonly="">
                                                                                </div>
                                                     -->
                                        {{-- <label class="col-form-label col-lg-2 online-transaction"
                                            style="display: none">Amount</label>
                                        <div class="col-lg-4 online-transaction" style="display: none">
                                            <input type="text" name="total_online_amount" id="total_online_amount"
                                                value="" class="form-control" readonly="">
                                        </div> --}}
                                    </div>
                                    <div id="company_bank_detail">
                                        <h5 class="card-title font-weight-semibold other-bank" style="display: none;">
                                            Company Bank</h5>
                                        <div class="form-group row other-bank" style="display: none;">
                                            <label class="col-form-label col-lg-2">Select Bank<sup>*</sup></label>
                                            <div class="col-lg-4">
                                                <select name="company_bank" id="company_bank" class="form-control">
                                                    <option value="">----Please Select----</option>
                                                    @foreach ($cBanks as $key => $bank)
                                                    @php
                                                    $balance = App\Models\BankBalance::where('bank_id',
                                                    $bank->id)->sum('totalAmount');
                                                    @endphp
                                                    @if ($bank['bankAccount'])
                                                    <option value="{{ $bank->id }}" data-balance="{{ $balance ?? '' }}"
                                                        data-account="{{ $bank['bankAccount']->account_no }}">
                                                        {{ $bank->bank_name }}</option>
                                                    @endif
                                                    @endforeach
                                                </select>
                                            </div>
                                            <label class="col-form-label col-lg-2">Select Bank A/C</label>
                                            <div class="col-lg-4">
                                                <select name="bank_account_number" id="bank_account_number"
                                                    class="form-control">
                                                    <option value="">Select Bank</option>
                                                </select>

                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <!-- <button type="button" class="btn btn-primary" form="modal-details">Submit</button> -->
                                        <input type="submit" name="submitform" value="Submit"
                                            class="btn btn-primary payloan-emi">
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal fade" id="exampleModal" class="refModel" tabindex="-1" role="dialog"
            aria-labelledby="exampleModalLabel" aria-hidden="true">
            <form action="#" method="post" enctype="multipart/form-data" id="ecsRef" class="" name="ecsRef">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLabel">ECS Register</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">

                            <div class="form-group">
                                <label for="message-text" class="col-form-label">Enter Reference Code</label>
                                <input type="text" class="form-control" id="ref-text" name="ref-text">
                                <input type="hidden" class="form-control" id="ref_id" name="refId">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            <button type="button" class="btn btn-primary escRefsubmit">Submit</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        @include('templates.admin.modal.index')
        @stop
        @section('script')
        @include('templates.branch.loan_management.partials.script')

        <script>
            var grouploantable;
                $(document).ready(function() {
                    $(document).on('click', '.reject-demand-advice', function(e) {
                        const modalTitle = $(this).attr('modal-title');
                        const loanId = $(this).attr('demandId');
                        const loanType = $(this).attr('loantype');
                        const loanCategory = $(this).attr('loanCategory');
                        const status = $(this).attr('status');
                        const el = document.createElement("input");
                        const statusData = document.createElement("input");
                        console.log("status", status);
                        $('.dinput').remove();
                        $('#demandRejectReason').attr('action', "{!! route('loan.reject') !!}")
                        $('#exampleModalLongTitle').html(modalTitle);
                        $inputData =
                            '<input type="hidden" id="loanCategory" class="dinput" name="loanCategory" value = "' +
                            loanCategory +
                            '"><input type="hidden" id="loanType" class="dinput" name="loanType" value = "' +
                            loanType + '"><input type="hidden" class="dinput" id="status" name="status" value = "' +
                            status + '">'
                        $('#demandRejectReason').append($inputData);
                        $('#demandId').val(loanId);
                    });

                    $('#demandRejectReason').validate({
                        rules: {
                            'rejectreason': {
                                required: true,
                            }
                        },
                            messages: {
                                'rejectreason': {
                                    required: 'Reason is required',

                            }
                        }
                    });
                    $('#ecsRef').validate({
                        rules: {
                            'ref-text': {
                                required: true,
                                // checkFormatEcs : '#ref-text',
                                // refNoExist :true,
                                minlength: 20,
                                maxlength: 20,
                                pattern: /^[a-zA-Z]{4}\d{16}$/
                            }
                        },
                        messages: {
                            'ref-text': {
                                checkFormatEcs: 'Please enter a valid format like ABCD1234567891011',
                                minlength: 'The value must be 20 characters long.',
                                maxlength: 'The value must be 20 characters long.',
                                pattern: 'The value must start with 4 alphabets followed by 16 digits.'
                        }
                    }
                    });

                    $(document).on('change' , '#ref-text',function(){
                        var refNo = $('#ref-text').val();
                        var loanType = 'G';
                        $.ajax({
                            type: "POST",
                            url: "{{route('branch.ecs.refNo.exist')}}",
                            data: {
                                'refNo': refNo,'loanType':loanType,
                            },
                            async: false,

                            success: function (response) {
                                console.log(response);
                                if(response == 1){

                                    swal('Warning','Reference Number Already Exist!','warning');
                                    $('#ref-text').val('');
                                }
                            }
                        });
                    });

                    $.validator.addMethod("checkFormatCustom", function(value, element, p) {
                        console.log(value, element, p);
                        // Check if the corresponding checkbox is checked
                        if ($(p).val() == 1) {
                            // Check if the 'element' is defined and has a value
                            if (element && element.value !== undefined) {
                                // Validate the format "ABCD1234567891011" without enforcing uppercase
                                if (this.optional(element) || /^[a-zA-Z]{4}\d{16}$/g.test(value)) {
                                    return true;
                                } else {
                                    $.validator.messages.checkFormatCustom = "Please enter a valid format (4 alphabets followed by 16 digits)";
                                    return false;
                                }
                            } else {
                                // 'element' is undefined or has no value, consider it invalid
                                return false;
                            }
                        } else {
                            // If the checkbox is not checked, consider it valid
                            return true;
                        }
                    }, "Invalid format");

                    $(document).on('click','.ecsRef',function(){
                        $('#ref-text').val('');
                        var ecsRef = $(this).data('id');
                        var refvalue = $(this).data('value');
                        // console.log()
                        if(refvalue){
                            $('#ref-text').val(refvalue);
                        }
                        // if(z == 0){
                        //     z += 1;
                        //     console.log(ecsRef,"ecsRef");
                            $('#ref_id').val(ecsRef);
                        // }
                    });

                    $(document).on('click','.escRefsubmit' , function(){
                        const refText = $('#ref-text').val();
                        var refId = $('#ref_id').val();
                        var createdByName = "Branch";
                        console.log(refId);
                        var loanType ="G";
                        // console.log(refText,refId);
                        if($('#ecsRef').valid()){
                        $.ajax({
                            type:"POST",
                            url:"{{ route('branch.loan.refNoStore') }}",
                            data:{
                                'refText':refText,
                                'refId':refId,
                                'loanType':loanType,
                                "createdByName" :createdByName,
                            },
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function(res){
                                $('#exampleModal').modal('hide');

                                console.log(res);
                                if(res == "success"){
                                    swal('Success!','Reference Number Store Successfully','success');
                                }else{
                                    swal('Warning!', 'An error occured');
                                }
                                grouploantable.draw();
                            }
                        }).then((res)=>{
                            // if( z > 0){
                            //     z -= 1;
                            // }
                            $('#ref_id').val('');
                            $('#ref-text').val('');
                        });}
                    });

                    grouploantable = $('#group-loan-listing').DataTable({
                        processing: true,
                        serverSide: true,
                        pageLength: 20,
                        lengthMenu: [10, 20, 40, 50, 100],
                        "fnRowCallback": function(nRow, aData, iDisplayIndex) {
                            var oSettings = this.fnSettings();
                            $("td:nth-child(1)", nRow).html(oSettings._iDisplayStart + iDisplayIndex + 1);
                            return nRow;
                        },
                        ajax: {
                            "url": "{{ route('loan.group.listing') }}",
                            "type": "POST",
                            "data": function(d) {
                                d.searchGroupLoanForm = $('form#grouploanfilter').serializeArray()
                            },
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                        },
                        columns: [{
                                data: 'DT_RowIndex',
                                name: 'DT_RowIndex'
                            },
                            {
                                data: 'action',
                                name: 'action',
                                orderable: false,
                                searchable: false
                            },
                            {
                                data: 'branch',
                                name: 'branch'
                            },
                            {
                                data: 'account_number',
                                name: 'account_number'
                            },
                            {
                                data: 'group_loan_common_id',
                                name: 'group_loan_common_id'
                            },

                            {
                                data: 'member_name',
                                name: 'member_name'
                            },
                            {
                                data: 'customer_id',
                                name: 'customer_id'
                            },
                            {
                                data: 'member_id',
                                name: 'member_id'
                            },
                            {
                                data: 'total_deposit',
                                name: 'total_deposit'
                            },

                            // {data: 'branch_code', name: 'branch_code'},
                            // {data: 'sector_name', name: 'sector_name'},
                            // {data: 'region_name', name: 'region_name'},
                            // {data: 'zone_name', name: 'zone_name'},





                            {
                                data: 'plan_name',
                                name: 'plan_name'
                            },
                            {
                                data: 'tenure',
                                name: 'tenure'
                            },
                            {
                                data: 'emi_amount',
                                name: 'emi_amount'
                            },

                            {
                                data: 'amount',
                                name: 'amount'
                            },
                            {
                                data: 'transfer_date',
                                name: 'transfer_date'
                            },
                            {
                                data: 'loan_amount',
                                name: 'loan_amount'
                            },
                            {
                                data: 'file_charges',
                                name: 'file_charges'
                            },
                            {
                                data: 'insurance_charge',
                                name: 'insurance_charge'
                            },
                            {
                                data: 'file_charges_payment_mode',
                                name: 'file_charges_payment_mode'
                            },
                            {
                                data: 'outstanding_amount',
                                name: 'outstanding_amount'
                            },
                            {
                                data: 'last_recovery_date',
                                name: 'last_recovery_date'
                            },
                            {
                                data: 'associate_code',
                                name: 'associate_code'
                            },
                            {
                                data: 'associate_name',
                                name: 'associate_name'
                            },
                            {
                                data: 'bank_name',
                                name: 'bank_name'
                            },
                            {
                                data: 'bank_account_number',
                                name: 'bank_account_number'
                            },
                            {
                                data: 'ifsc_code',
                                name: 'ifsc_code'
                            },
                            {
                                data: 'total_payment',
                                name: 'total_payment'
                            },
                            {
                                data: 'approve_date',
                                name: 'approve_date'
                            },
                            {
                                data: 'sanction_date',
                                name: 'sanction_date'
                            },
                            {
                                data: 'application_date',
                                name: 'application_date'
                            },
                            {
                                data: 'collector_code',
                                name: 'collector_code'
                            },
                            {
                                data: 'collector_name',
                                name: 'collector_name'
                            },
                            {
                                data: 'reason',
                                name: 'reason'
                            },
                            {
                                data: 'status',
                                name: 'status'
                            }
                        ],
                        "bDestroy": true,
                    });
                    $('.payloan-emi').on('click',e=>{
                        $(this).prop('disabled', true);
                    });
                    $("#date_from").hover(function() {
                        $("#date_from").datepicker({
                            format: "dd/mm/yyyy",
                            autoclose: true,
                            todayHighlight: true,
                        }).on("changeDate", function(e) {
                            // $('#to_date').datepicker('setDate', e.date, 'format', "dd/mm/yyyy");
                            $('#to_date').datepicker('setStartDate', e.date, 'format',
                                "dd/mm/yyyy");
                        });
                        $("#to_date").datepicker({
                            format: "dd/mm/yyyy",
                            autoclose: true,
                            orientation: "bottom",
                        });
                    })
                    $('#to_date').hover(function() {
                        if ($('#editInput').val() != '') {
                            let date = $('#from_datee').attr('data-val');
                            $('#to_date').datepicker({
                                format: "dd/mm/yyyy",
                                autoclose: true,
                                todayHighlight: true,
                                startDate: date,
                            })
                        }
                    });



                    //Plans fetch as per compnay id for group loan search form
                    $(document).on('change', '#group_company_id', function() {
                        var company_id = $('#group_company_id').val();
                        $.ajax({
                            type: "POST",
                            url: '{{ route('group.loan.fetch') }}',
                            dataType: 'JSON',
                            data: {
                                'company_id': company_id
                            },
                            success: function(e) {
                                if (e.data != '') {
                                    $("#plan").html(e.data);
                                }

                            }
                        });

                    })

                    function showgrouplisting() {
                        grouploantable.draw();
                    }

                    function searchGroupLoanForm() {
                        if ($('#grouploanfilter').valid()) {
                            $('#is_search').val("yes");
                            $('.shadow').removeClass('d-none');
                            showgrouplisting();
                        }else{
                            $('.shadow').toggleClass('d-none');
                        }
                    }
                    $('#grouploanfilter').validate({
                        rules: {
                            group_company_id: {
                                required: true,
                            },
                            plan: {
                                required: true,
                            }
                        },
                        messages: {
                            group_company_id: {
                                required: "Please select company",
                            },
                            plan: {
                                required: "Please select plan first",
                            }
                        },
                        submitHandler: function() {
                            searchGroupLoanForm();
                        }
                    });
                    // A function to turn all form data into a jquery object
                    jQuery.fn.serializeObject = function() {
                        var o = {};
                        var a = this.serializeArray();
                        jQuery.each(a, function() {
                            if (o[this.name] !== undefined) {
                                if (!o[this.name].push) {
                                    o[this.name] = [o[this.name]];
                                }
                                o[this.name].push(this.value || '');
                            } else {
                                o[this.name] = this.value || '';
                            }
                        });
                        return o;
                    };

                    // $('.export-group-loan').on('click', function(e) {
                    //     e.preventDefault();
                    //     var extension = $(this).attr('data-extension');
                    //     $('#group_loan_recovery_export').val(extension);
                    //     var startdate = $(".from_date").val();
                    //     var enddate = $(".to_date").val();
                    //     if (extension == 0) {
                    //         if (startdate == '') {
                    //             swal("Error!", "Please select start date, you can export last three months data!",
                    //                 "error");
                    //             return false;
                    //         }
                    //         if (enddate == '') {
                    //             swal("Error!", "Please select end date, you can export last three months data!",
                    //                 "error");
                    //             return false;
                    //         }
                    //         var formData = jQuery('#grouploanfilter').serializeObject();
                    //         var chunkAndLimit = 50;
                    //         $(".spiners").css("display", "block");
                    //         $(".loaders").text("0%");
                    //         doChunkedExports(0, chunkAndLimit, formData, chunkAndLimit);
                    //         $("#cover").fadeIn(100);
                    //     } else {
                    //         $('#group_loan_recovery_export').val(extension);
                    //         $('form#grouploanfilter').attr('action', "{!! route('branch.group_loan_list_export') !!}");
                    //         $('form#grouploanfilter').submit();
                    //     }
                    // });

                    // function doChunkedExports(start, limit, formData, chunkSize) {
                    //     formData['start'] = start;
                    //     formData['limit'] = limit;
                    //     jQuery.ajax({
                    //         type: "post",
                    //         dataType: "json",
                    //         url: "{!! route('branch.group_loan_list_export') !!}",
                    //         headers: {
                    //             'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    //         },
                    //         data: formData,
                    //         success: function(response) {
                    //             console.log(response);
                    //             if (response.result == 'next') {
                    //                 start = start + chunkSize;
                    //                 doChunkedExports(start, limit, formData, chunkSize);
                    //                 $(".loaders").text(response.percentage + "%");
                    //             } else {
                    //                 var csv = response.fileName;
                    //                 console.log('DOWNLOAD');
                    //                 $(".spiners").css("display", "none");
                    //                 $("#cover").fadeOut(100);
                    //                 window.open(csv, '_blank');
                    //             }
                    //         }
                    //     });
                    // }


                });
            </script>
        @stop
