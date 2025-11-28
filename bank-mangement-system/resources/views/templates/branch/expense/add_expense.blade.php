@extends('layouts/branch.dashboard')

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

    .required {
        color: red;
    }

    .myfileinput {
        font-size: .875rem;
        font-weight: 400;
        line-height: 1.5;
        display: block;
        height: calc(1.5em + 1.25rem + 2px);
        padding: .625rem .75rem;
        transition: all .15s cubic-bezier(.68, -.55, .265, 1.55);
        color: #8898aa;
        border: 1px solid #dee2e6;
        border-radius: .25rem;
        background-color: #fff;
        background-clip: padding-box;
        box-shadow: 0 3px 2px rgba(233, 236, 239, .05);
    }
</style>
<div class="loader" style="display: none;"></div>
@php
$dropDown = $company;
$filedTitle = 'Company';
$name = 'company';
@endphp

<div class="container-fluid mt--6">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-lg-12">
                <div class="card bg-white">
                    <div class="card-body page-title">
                        <h3 class="">{{ $title }}</h3>
                        <!--<a href="{!! route('branch.fundtransfer.createbanktobank') !!}" style="float:right" class="btn btn-secondary">Add</a>-->
                        <a href="{{ url()->previous() }}" style="float:right" class="btn btn-secondary">Back</a>

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
                    <form method="post" action="{!! route('branch.expense.save') !!}" id="expenses" name="expenses" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" class="form-control created_at " name="created_at" id="created_at">

                        <input type="hidden" class="form-control create_application_date " name="create_application_date" id="create_application_date">
                        <div class="card-header header-elements-inline">
                            <div class="card-body">
                                <div class="form-group row">
                                    @include('templates.GlobalTempletes.new_role_type', [
                                    'dropDown' => $dropDown,
                                    'filedTitle' => $filedTitle,
                                    'name' => $name,
                                    'value' => '',
                                    'multiselect' => 'false',
                                    'design_type' => 5,
                                    'branchShow' => false,
                                    'selectedCompany' => $selectedCompany,
                                    'branchName' => 'branch_id',
                                    'apply_col_md' => false,
                                    'multiselect' => false,
                                    'placeHolder1' => 'Please Select Company',
                                    'placeHolder2' => 'Please Select Branch',
                                    ])
                                    <input type="hidden" name="company_id" id="company_id">
                                    <label class="col-form-label col-lg-2">Branch</label>
                                    <div class="col-lg-4">
                                        <select name="branch_id" id="branch_id" class="form-control">
                                           <!--<option value="">Select Branch</option>-->
                                            <option value="{{ $branch->id }}" selected>{{ $branch->name }}</option>

                                        </select>
                                    </div>
                                </div>
                                <div class="form-group row">

                                    <label class="col-form-label col-lg-2">Date<sup class="text-danger">*</sup></label>
                                    <div class="col-lg-4">
                                        <input type="text" name="bill_date" class="form-control expensesDatee" id="expensesDatee" readonly="" value="{{ headerMonthAvailability(date('d'), date('m'), date('Y'), getBranchStateByManagerId(Auth::user()->id)) }}" >
                                    </div>
                                    <label class="col-form-label col-lg-2">Party Name<sup class="text-danger">*</sup></label>
                                    <div class="col-lg-4 error-msg">
                                        <input type="text" name="party_name" id="party_name" class="form-control">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-2">Payment Mode<sup class="text-danger">*</sup></label>
                                    <div class="col-lg-4 error-msg">
                                        <select name="payment_mode" id="payment_mode" class="form-control">
                                            <option value="">----Select Payment Mode ----</option>
                                            <option value="0">Cash</option>
                                            <!--<option value="1">Cheque</option>
                                            <option value="2">Online Transaction </option>-->
                                        </select>
                                    </div>

                                </div>

                                <div class="form-group row cash_box" style="display:none;">
                                    <label class="col-form-label col-lg-2">Branch Balance</label>
                                    <div class="col-lg-4">
                                        <input type="text" name="branch_total_balance" id="branch_total_balance" readonly class="form-control">
                                    </div>
                                </div><br />

                                <div id="bank_details">
                                    <div class="form-group row">
                                        <div class="col-md-12 ">
                                            <h5 class="card-title">Company Bank Detail :</h5>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-2">Bank<sup class="text-danger">*</sup></label>
                                        <div class="col-lg-4 error-msg">
                                            <select class="form-control" id="bank_id" name="bank_id">
                                                <option value="">Select Bank</option>
                                                @foreach ($bank as $val)
                                                <option value="{{ $val->id }}">{{ $val->bank_name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <label class="col-form-label col-lg-2">
                                            Account Number<sup class="text-danger">*</sup>
                                        </label>
                                        <div class="col-lg-4 error-msg">
                                            <select name="account_id" id="account_id" class="form-control">
                                                <option value="">Select Account Number</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="form-group row" style="display:none;">
                                        <label class="col-form-label col-lg-2">Bank Balance</label>
                                        <div class="col-lg-10 error-msg">
                                            <input type="text" class="form-control" name="bank_balance" id="bank_balance" readonly value="0.00">
                                        </div>
                                    </div>

                                    <div id="chq_details">
                                        <div class="form-group row">
                                            <div class="col-md-12 ">
                                                <h5 class="card-title">Cheque Detail :</h5>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-2">Cheque<sup class="text-danger">*</sup></label>
                                            <div class="col-lg-4 error-msg">
                                                <select name="cheque_id" id="cheque_id" class="form-control">
                                                    <option value="">Select Cheque</option>
                                                </select>
                                            </div>

                                        </div>
                                    </div>

                                    <div id="online_details">
                                        <div class="form-group row">
                                            <div class="col-md-12 ">
                                                <h5 class="card-title">Party Bank Detail :</h5>
                                            </div>
                                        </div>

                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-2">Party Bank Name<sup class="text-danger">*</sup></label>
                                            <div class="col-lg-4 error-msg">
                                                <input type="text" class="form-control" name="party_bank_name" id="party_bank_name">
                                            </div>
                                            <label class="col-form-label col-lg-2">
                                                Party Bank A/C No<sup class="text-danger">*</sup>
                                            </label>
                                            <div class="col-lg-4 error-msg">
                                                <input type="text" class="form-control" name="party_bank_ac_no" id="party_bank_ac_no">
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-2">Party Bank Ifsc<sup class="text-danger">*</sup></label>
                                            <div class="col-lg-4 error-msg">
                                                <input type="text" class="form-control" name="party_bank_ifsc" id="party_bank_ifsc">
                                            </div>
                                            <label class="col-form-label col-lg-2">UTR / Transaction No<sup class="text-danger">*</sup></label>
                                            <div class="col-lg-4 error-msg">
                                                <input type="text" class="form-control" name="utr_no" id="utr_tran">
                                            </div>
                                        </div>
                                        <div class="form-group row">

                                            <label class="col-form-label col-lg-2">
                                                RTGS/NEFT Charge<sup class="text-danger">*</sup>
                                            </label>
                                            <div class="col-lg-4 error-msg">
                                                <input type="text" class="form-control t_amount" name="neft_charge" id="neft_charge">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="search-table-outter wrapper py-4">

                            <table class="table table-flush" id="expense1">
                                <thead>
                                    <tr>
                                        <th>Particulars</th>
                                        <th>Account Head</th>
                                        <th>Account Sub Head1</th>
                                        <th>Account Sub Head2</th>
                                        <th>Amount</th>
                                        <th>Upload Receipt</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody id="expense">
                                    <tr>


                                        <td>
                                            <div class="col-lg-12 error-msg">
                                                <input type="text" name="particular" id="particular" class="form-control frm" />
                                            </div>
                                        </td>
                                        <td>
                                            <div class="col-lg-12 error-msg">

                                                <select name="account_head" id="account_head" class="form-control frm">
                                                    <option value="">Select Account Head</option>
                                                    @foreach ($account_heads as $heads)
                                                    @if(in_array($selectedCompany,json_decode($heads->company_id)))
                                                    <option value="{{ $heads->head_id }}">{{ $heads->sub_head }}
                                                    </option>
                                                    @endif
                                                    @endforeach
                                                </select>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="col-lg-12 error-msg">
                                                <select name="sub_head1" id="sub_head1" class="form-control frm">

                                                </select>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="col-lg-12 error-msg">
                                                <select name="sub_head2" id="sub_head2" class="form-control frm">

                                                </select>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="col-lg-12 error-msg">
                                                <input type="text" autocomplete="off" name="amount" id="amount" class="form-control t_amount frm" />
                                            </div>
                                        </td>
                                        <td>
                                            <div class="col-lg-12 error-msg">
                                                <input type="file" name="receipt" id="receipt" class="myfileinput" />
                                            </div>
                                        </td>
                                        <td></td>

                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <hr />
                        <div class=" form-group row">
                            <div class=" col-lg-1">
                            </div>
                            <div class=" col-lg-5">
                                <button type="button" class="btn btn-primary ml-2" id="add_row"><i class="icon-add ">ADD MORE</i></button>
                            </div>
                            <div class="  col-lg-6 row">
                                <label class="col-form-label col-lg-4">Total Amount<sup>*</sup></label>
                                <div class="col-lg-6">
                                    <input type="text" name="total_amount" id="total_amount" class="form-control" readonly />
                                </div>

                            </div>
                        </div>



                        <div class="col-lg-12 mb-4">
                            <div class="text-center">
                                <button type="submit" class="btn btn-primary" id="mySubmitBtn">Submit</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
</div>
@stop
@section('script')
@include('templates.branch.expense.partial.script')
<script>
        $('#company').attr('disabled', true);
        $('#company_id').val($('#company').val());
    </script>
@stop