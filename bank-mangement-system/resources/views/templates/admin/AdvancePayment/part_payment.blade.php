@extends('templates.admin.master')

@section('content')
<style>
    .search-table-outter {
        overflow-x: scroll;
    }

    .frm {
        min-width: 200px;
    }

    #bill_date {
        min-width: 100px;
    }

    input[type=checkbox] {
        height: 0;
        width: 0;
        visibility: hidden;
    }

    #toggle {
        cursor: pointer;
        text-indent: -9999px;
        width: 50px;
        height: 25px;
        background: grey;
        display: block;
        border-radius: 25px;
        position: relative;
    }

    #toggle:after {
        content: '';
        position: absolute;
        top: 2px;
        left: 2px;
        width: 22px;
        height: 22px;
        background: #fff;
        border-radius: 90px;
        transition: 0.3s;

        box-shadow: 0px 50px 100px -20px rgba(50, 50, 93, 0.25), 0px 30px 60px -30px rgba(0, 0, 0, 0.3), inset 0px -2px 6px 0px rgba(10, 37, 64, 0.35);
    }

    input:checked+#toggle {
        background: #f71017;
    }

    input:checked+#toggle:after {
        left: calc(100% - 2px);
        transform: translateX(-100%);
    }

    #toggle:active:after {
        width: 30px;
    }
</style>
<div class="loader" style="display: none;"></div>

<div class="content">
    <div class="row">
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



        <div class="col-md-12">
            <!-- Basic layout-->
            <div class="card">

                <div class="card-body">

                    <h3 class="card-title mb-3 maintital">Adjustment Request - Part payment</h3>


                </div>


                <form id="form">
                    @csrf
                    <input type="hidden" class="form-control created_at " name="created_at" id="created_at">

                    <input type="hidden" class="form-control create_application_date " name="create_application_date" id="create_application_date">
                    <div class="card-header header-elements-inline">
                        <div class="container-fluid">
                            <div class=" form-group row">
                                <div class="col-lg-6">
                                    <label for="EmployeeName">Employee Name</label>
                                    <input type="text" name="employeename" readonly value="{{$data['employee']['employee_name']}}" class="form-control">
                                    <input type="hidden" name="employeeid" value="{{$data['employee']['id']}}" class="form-control">
                                    <input type="hidden" name="id" value="{{last(explode('/', url()->current()))}}" class="form-control">
                                </div>
                                <div class="col-lg-6">
                                    <label for="Employeecode">Employee Code</label>
                                    <input type="text" name="employeecode" readonly value="{{$data['employee']['employee_code']}}" class="form-control">
                                </div>
                            </div>
                            <div class=" form-group row">
                                <div class="col-lg-6">
                                    <label for="Amount">TA Amount</label>
                                    <input type="number" name="approveAmount" readonly value="{{$data['advancePayment']['amount'] - $data['advancePayment']['used_amount'] - $data['advancePayment']['withdraw']}}" class="form-control" id="amount">
                                </div>
                                <div class="col-lg-6">
                                    <label for="adjestmentdate">Advance Payment Date</label>
                                    <input type="text" name="adjestmentdate" id="date" readonly value="{{date('d/m/Y',strtotime($data['advancePayment']['status_date']))}}" class="form-control">
                                </div>
                            </div>
                            <div class=" form-group row">
                                <div class="col-lg-6">
                                    <label for="company_name">Company Name</label>
                                    <input type="text" name="company_name" readonly value="{{$data['advancePayment']['company']['name']}}" class="form-control">
                                    <input type="hidden" name="company_id" id="company_id" value="{{$data['advancePayment']['company_id']}}">
                                </div>
                                <div class="col-lg-6">
                                    <label for="branch_name">Branch Name</label>
                                    <input type="text" name="branch_name" readonly value="{{$data['branchName']}}" class="form-control">
                                    <input type="hidden" name="branch_id" id="branch_id" value="{{$data['advancePayment']['branch_id']}}">
                                </div>
                            </div>

                        </div>
                    </div>

                    <hr />
                    @php

                    $ssb = $data['employee']['getSsb'] ? $data['employee']['getSsb']['account_no'] : '';

                    @endphp
                    <div class="form-group row ml-2">
                        <!-- <div class=" col-lg-1">
                        </div> -->
                        <!-- {{--<div class=" col-lg-5">
                            <button type="button" class="btn btn-primary ml-2" id="add_row"><i class="icon-add ">ADD</i></button>
                        </div>--}} -->
                        <input type="hidden" name="member_id" id="member_id" class="form-control">
                        <input type="hidden" name="associate_code" id="associate_code" class="form-control">
                        <input type="hidden" name="employee_name" id="employee_name" class="form-control">
                        <input type="hidden" name="accountNumber" id="accountNumber" class="form-control">
                        <div class="  col-lg-6 row">
                            <label class="col-form-label col-lg-4">Amount Receiving<sup>*</sup></label>
                            <div class="col-lg-6">
                                <input type="text" name="total_amount" id="total_amount" class="form-control"  />
                            </div>
                        </div>
                        <div class="col-lg-6 row  pl-4">
                            <label class="col-form-label col-lg-4">Remaining Amount</label>
                            <div class="col-lg-6">
                                <input type="text" name="remaining_amoutn" value="{{$data['advancePayment']['amount'] - $data['advancePayment']['used_amount'] - $data['advancePayment']['withdraw']}}" id="remaining_amount" class="form-control" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="form-group row ml-2 " id="settle_amt">

                        <div class="col-lg-6 ">
                            <div class="form-group row">
                                <label class="col-form-label col-lg-4">Date<sup class="required">*</sup></label>
                                <div class="col-lg-6">
                                    <input type="text" name="adj_date" id="adj_date" class="form-control" readonly />
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6" id="branchBalance2" style="display:none;">
                            <div class="form-group row">
                                <label class="col-form-label col-lg-4">Branch Current Balance<sup class="required">*</sup></label>
                                <div class="col-lg-6">
                                    <input type="text" name="branchBalance" id="branchBalance" value="" readonly class="form-control">
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6 mt-2 paymentmode pl-2">
                            <div class="form-group row">
                                <label class="col-form-label col-lg-4" id="payment_method">Payment Mode <sup class="required">*</sup></label>
                                <div class="col-lg-6">
                                    <select name="payment_mode" id="payment_mode" class="form-control input-type">
                                        <option value="">Please Select</option>
                                        <option value="BANK">Bank</option>
                                        <option value="CASH">CASH</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6 mt-2" id="tmode" style="display:none;">
                            <div class="form-group row">
                                <label class="col-form-label col-lg-4">Transfer Mode <sup class="required">*</sup></label>
                                <div class="col-lg-6">
                                    <select name="transfer_mode" id="transfer_mode" class="form-control input-type">
                                        <option value="">---- Please Select ----</option>
                                        <option value="0">Cheque</option>
                                        <option value="1">Online Transfer</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6  mt-2" id="bankss" style="display:none;">
                            <div class="form-group row">
                                <label class="col-form-label col-lg-4">Bank <sup class="required">*</sup></label>
                                <div class="col-lg-6">
                                    <select class="form-control" id="bank_id" name="bank_id">
                                        <option value="">Select Bank</option>
                                        @foreach ($data['bank'] as $val)
                                        <option value="{{ $val['id'] }}">{{ $val['bank_name'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6  mt-2" id="accourid" style="display:none;">
                            <div class="form-group row">
                                <label class="col-form-label col-lg-4">Account Number<sup class="required">*</sup></label>
                                <div class="col-lg-6">
                                    <select name="account_id" id="account_id" class="form-control" aria-invalid="false">
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6 bankbalance  mt-2" id="bankbalance" style="display:none;">
                            <div class="form-group row">
                                <label class="col-form-label col-lg-4">Bank Balance<sup class="required">*</sup></label>
                                <div class="col-lg-6">
                                    <input type="text" class="form-control" name="bank_balance" id="bank_balance" readonly value="0.00">
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6 online utrnumber mt-2" style="display: none;">
                            <div class="form-group row">
                                <label class="col-form-label col-lg-4"> UTR number / Transaction Number <sup class="required">*</sup></label>
                                <div class="col-lg-6">
                                    <input type="text" class="form-control" name="utr_tran" id="utr_tran">
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6 online rtgsnumber mt-2" style="display: none;">
                            <div class="form-group row">
                                <label class="col-form-label col-lg-4">RTGS/NEFT Charge</label>
                                <div class="col-lg-6">
                                    <input type="text" class="form-control" name="neft_charge" id="neft_charge">
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6  mt-2" id="cheque" style="display: none;">
                            <div class="form-group row">
                                <label class="col-form-label col-lg-4">Cheque <sup class="required">*</sup></label>
                                <div class="col-lg-6">
                                    <select name="cheque_id" id="cheque_id" class="form-control">
                                        <option value="">Select Cheque</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row w-100 ml-1 mt-3" id="recived_bank" style="display:none;">
                            <h3 class="w-100 ml-2">Bank details from which payment is received</h3>
                            <div class="col-lg-6  mt-2">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-4">Account Number<sup class="required">*</sup></label>
                                    <div class="col-lg-6">
                                        <input type="text" class="form-control" name="r_account_id" id="r_account_id">
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6  mt-2">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-4">Bank Name<sup class="required">*</sup></label>
                                    <div class="col-lg-6">
                                        <input type="text" class="form-control" name="r_bank_name" id="r_bank_name">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row w-100 ml-1 mt-3 p-mode" id="" style="display:none;">
                            <h3 class="w-100 ml-2">Cheque Detail</h3>
                            <div class="col-lg-6  mt-2">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-4">Cheque Number<sup class="required">*</sup></label>
                                    <div class="col-lg-6">
                                        <select name="cheque_id_r" id="cheque_id_r" class="form-control" title="Please select something!">
                                            <option value="">Select Cheque</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div style="display: none;" id='cheque_detail' class="row w-100 ml-1 mt-3">

                            <div class="form-group row w-100 ml-1 mt-3">
                                <label class="col-form-label col-lg-2">Cheque Number</label>
                                <div class="col-lg-4">
                                    <input type="text" name="cheque-number" id="cheque-number" class="form-control" readonly>
                                </div>
                                <label class="col-form-label col-lg-2">Bank Name</label>
                                <div class="col-lg-4">
                                    <input type="text" name="bank-name" id="bank-name" class="form-control" readonly>
                                </div>
                            </div>

                            <div class="form-group row w-100 ml-1 mt-3">
                                <label class="col-form-label col-lg-2">Branch Name</label>
                                <div class="col-lg-4">
                                    <input type="text" name="branch-name" id="branch-name" class="form-control" readonly>
                                </div>
                                <label class="col-form-label col-lg-2">Cheque Date</label>
                                <div class="col-lg-4">
                                    <input type="text" name="cheque-date" id="cheque-date" class="form-control" readonly>
                                </div>
                            </div>

                            <div class=" form-group row w-100 ml-1 mt-3">
                                <label class="col-form-label col-lg-2">Cheque Amount</label>
                                <div class="col-lg-4">
                                    <div class="rupee-img"></div>
                                    <input type="text" name="cheque-amt" id="cheque-amt" class="form-control rupee-txt" readonly>
                                </div>
                                <label class="col-form-label col-lg-2">Deposit Bank</label>
                                <div class="col-lg-4">
                                    <input type="text" name="deposit_bank_name" id="deposit_bank_name" class="form-control" readonly>
                                </div>
                            </div>
                            <div class=" form-group row w-100 ml-1 mt-3">
                                <label class="col-form-label col-lg-2">Deposit bank Account</label>
                                <div class="col-lg-4">
                                    <input type="text" name="deposit_bank_account" id="deposit_bank_account" class="form-control" readonly>
                                </div>
                            </div>
                            <input type="hidden" name="bank_name_id" id="bank_name_id" value="">
                            <input type="hidden" name="bank_ac_id" id="bank_ac_id" value="">
                        </div>
                    </div>


                    <input type="hidden" name="is_od" id="is_od" class="is_od_value">
                    <div class="col-lg-12 mb-4">
                        <div class="text-center">
                            <button type="submit" id="submit" class="btn btn-primary">Submit</button>
                        </div>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>
@include('templates.admin.AdvancePayment.partials.part_script')
@stop