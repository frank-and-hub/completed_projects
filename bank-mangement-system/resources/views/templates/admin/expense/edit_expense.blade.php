@extends('templates.admin.master')

@section('content')
    <style>
        .search-table-outter {
            overflow-x: scroll;
        }

        .frm {
            min-width: 90px;
        }

        #bill_date {
            min-width: 100px;
        }

        .required {
            color: red;
        }
    </style>
    <div class="loader" style="display: none;"></div>
    @php
        $dropDown = $company;
        $filedTitle = 'Company';
        $name = 'company_id';
        $selectedBranch = $billExpense->branch_id;
        $selectedCompany = $billExpense->company_id;
    @endphp
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
                    <form method="post" action="{!! route('admin.expense.update') !!}" id="expenses" name="expenses"
                        enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" class="form-control created_at " name="created_at" id="created_at">

                        <input type="hidden" class="form-control create_application_date " name="create_application_date"
                            id="create_application_date">
                        <input type="hidden" name="bill_no" value="{{ $billExpense->bill_no }}">
                        <div class="card-header header-elements-inline">
                            <div class="card-body">
                                <div class="form-group row">
                                    <label class="col-form-label  col-lg-2 ">Company<sup class="required">*</sup> </label>
                                    <div class=" col-lg-4  error-msg">

                                        <select class="form-control" name="company" disabled readonly>
                                            @foreach ($companyData as $c)
                                                <option value="{{ $c->id }}" selected="">{{ $c->name }}
                                                </option>
                                            @endforeach
                                        </select>

                                    </div>
                                    <label class="col-form-label col-lg-2">Branch </label>
                                    <div class="col-lg-4 error-msg">
                                        <select class="form-control valid" name="branch_id" id="branch"
                                            aria-invalid="false">
                                            <option value="">--Please Select Branch --</option>
                                            @foreach ($branches as $b)
                                                <option value="{{ $b->id }}" @if ($b->id == $selectedBranch) selected @endif>{{ $b->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <input type="hidden" name="company_id" id="company_id" value="{{ $selectedCompany }}">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-2">Date<sup class="text-danger">*</sup></label>
                                    <div class="col-lg-4">
                                        <input type="text" name="expensesDate" class="form-control expensesDate"
                                            id="expensesDate" readonly value="<?php if (isset($expenseData[0]->bill_date)) {
                                                echo date('d/m/Y', strtotime($expenseData[0]->bill_date));
                                            } ?>">
                                    </div>


                                </div>
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-2">Party Name<sup
                                            class="text-danger">*</sup></label>
                                    <div class="col-lg-4 error-msg">
                                        <input type="text" name="party_name" id="party_name" class="form-control"
                                            value="{{ $billExpense->party_name }}">
                                    </div>
                                    <label class="col-form-label col-lg-2">Payment Mode<sup
                                            class="text-danger">*</sup></label>
                                    <div class="col-lg-4 error-msg">
                                        <select name="payment_mode" id="payment_mode" class="form-control">
                                            <option value="">----Select Payment Mode ----</option>
                                            <option value="0" @if ($billExpense->payment_mode == 0) selected @endif>Cash
                                            </option>
                                            <option value="1" @if ($billExpense->payment_mode == 1) selected @endif>Cheque
                                            </option>
                                            <option value="2" @if ($billExpense->payment_mode == 2) selected @endif>Online
                                                Transaction</option>
                                        </select>
                                    </div>
                                </div>


                                <div class="form-group row cash_box" id="cash">
                                    <label class="col-form-label col-lg-2">Branch Balance</label>
                                    <div class="col-lg-4">
                                        <input type="text" name="branch_total_balance" id="branch_total_balance" readonly
                                            class="form-control" value="{{ $billExpense->branch_balance }}">
                                    </div>
                                </div><br />
                                <div id="bank_details" style="display: none;">
                                    <div class="form-group row">
                                        <div class="col-md-12 ">
                                            <h6 class="card-title">Company Bank Detail :</h6>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-2">Bank<sup class="text-danger">*</sup></label>
                                        <div class="col-lg-4 error-msg">
                                            <select class="form-control" id="bank_id" name="bank_id">
                                                <option value="">Select Bank</option>
                                                @foreach ($bank as $val)
                                                    <option value="{{ $val->id }}" <?php if($billExpense->bank_id == $val->id) { ?>
                                                        selected="selected" <?php } ?>>{{ $val->bank_name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <label class="col-form-label col-lg-2">
                                            Account Number<sup class="text-danger">*</sup>
                                        </label>
                                        <div class="col-lg-4 error-msg">
                                            <select name="account_id" id="account_id" class="form-control">
                                                <option value="">Select Account Number</option>
                                                @foreach ($bank_ac as $val)
                                                    <option value="{{ $val->id }}" <?php if($billExpense->account_id == $val->id) { ?>
                                                        selected="selected" <?php } ?>>{{ $val->account_no }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-2">Bank Balance</label>
                                        <div class="col-lg-10 error-msg">
                                            <input type="text" class="form-control" name="bank_balance"
                                                id="bank_balance" readonly value="{{ $billExpense->bank_balance }}">
                                        </div>
                                    </div>
                                    <div id="chq_details" style="display:none;">

                                        <div class="form-group row">
                                            <div class="col-md-12 ">
                                                <h6 class="card-title">Cheque Detail :</h6>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-2">Cheque<sup
                                                    class="text-danger">*</sup></label>
                                            <div class="col-lg-4 error-msg">
                                                <select name="cheque_id" id="cheque_id" class="form-control">
                                                    <option value="">Select Cheque</option>
                                                    @foreach ($cheques as $cheq)
                                                        <option value="{{ $cheq->id }}" <?php if($billExpense->cheque_id == $cheq->id) { ?>
                                                            selected="selected" <?php } ?>>{{ $cheq->cheque_no }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>

                                        </div>
                                    </div>
                                    <div id="online_details" style="display:none;">

                                        <div class="form-group row">
                                            <div class="col-md-12 ">
                                                <h6 class="card-title">Party Bank Detail :</h6>
                                            </div>
                                        </div>

                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-2">Party Bank Name<sup
                                                    class="text-danger">*</sup></label>
                                            <div class="col-lg-4 error-msg">
                                                <input type="text" class="form-control" name="party_bank_name"
                                                    id="party_bank_name" value="{{ $billExpense->party_name }}">
                                            </div>
                                            <label class="col-form-label col-lg-2">
                                                Party Bank A/C No<sup class="text-danger">*</sup>
                                            </label>
                                            <div class="col-lg-4 error-msg">
                                                <input type="text" class="form-control" name="party_bank_ac_no"
                                                    id="party_bank_ac_no" value="{{ $billExpense->party_bank_ac_no }}">
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-2">Party Bank Ifsc<sup
                                                    class="text-danger">*</sup></label>
                                            <div class="col-lg-4 error-msg">
                                                <input type="text" class="form-control" name="party_bank_ifsc"
                                                    id="party_bank_ifsc" value="{{ $billExpense->party_bank_ifsc }}">
                                            </div>
                                            <label class="col-form-label col-lg-2">UTR / Transaction No<sup
                                                    class="text-danger">*</sup></label>
                                            <div class="col-lg-4 error-msg">
                                                <input type="text" class="form-control" name="utr_no" id="utr_tran"
                                                    value="<?php if (isset($billExpense->utr_no)) {
                                                        echo $billExpense->utr_no;
                                                    } ?>" />
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-2">
                                                RTGS/NEFT Charge<sup class="text-danger">*</sup>
                                            </label>
                                            <div class="col-lg-4 error-msg">
                                                <input type="text" class="form-control" name="neft_charge"
                                                    id="neft_charge" value="<?php if (isset($billExpense->neft_charge)) {
                                                        echo $billExpense->neft_charge;
                                                    } ?>" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!--<div class="search-table-outter wrapper py-4">-->

                        <table class="table table-flush" id="expense1">
                            <thead>
                                <tr>
                                    <th>Particulars</th>
                                    <th>Account Head</th>
                                    <th>Account Sub Head1</th>
                                    <th>Account Sub Head2</th>
                                    <th>Amount</th>
                                    <th>Upload Receipt</th>
                                </tr>
                            </thead>
                            <tbody id="expense">
                                @foreach ($expenseData as $i => $value)
                                    <input type="hidden" name="expensesId[]" value="{{ $value->id }}">
                                    <tr>
                                        <td>
                                            <div class="col-lg-12 error-msg">
                                                <input type="text" name="particular[]" id="particular"
                                                    class="form-control frm particularRow" value="<?php if (isset($value->particular)) {
                                                        echo $value->particular;
                                                    } ?>"
                                                    data-row-id="{{ $i }}" />
                                            </div>
                                            <span id="msg-{{ $i }}" class="text-danger"></span>

                                        </td>
                                        <td>
                                            <div class="col-lg-12 error-msg">
                                                <select name="account_head[]" id="account_head"
                                                    class="form-control frm ac" data-row-id="{{ $i }}">
                                                    <option value="">Select Account Head</option>
                                                    @foreach ($account_heads as $heads)
                                                        @if (in_array($selectedCompany, json_decode($heads->company_id)))
                                                            <option value="{{ $heads->head_id }}"
                                                                @if ($value->account_head_id == $heads->head_id) selected @endif
                                                                data-companyId="{{ $heads->company_id }}"
                                                                class="head_option">
                                                                {{ $heads->sub_head }}</option>
                                                        @endif
                                                    @endforeach
                                                </select>
                                            </div>
                                            <span id="acmsg-{{ $i }}" class="text-danger"></span>

                                        </td>
                                        <?php
                                        $getSubHead = \App\Models\AccountHeads::where('parent_id', $value->account_head_id)->get();
                                        ?>
                                        <td>
                                            <div class="error-msg">
                                                <select name="sub_head1[]" id="sub_head1" class="form-control frm">
                                                    <option value="">Select Account Head1</option>
                                                    @foreach ($getSubHead as $heads)
                                                        <option value="{{ $heads->head_id }}" <?php if($value->sub_head1 == $heads->head_id) { ?>
                                                            selected="selected" <?php } ?>>
                                                            {{ $heads->sub_head }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </td>
                                        <?php
                                        $getSubHead2 = \App\Models\AccountHeads::where('parent_id', $value->sub_head1)->get();
                                        ?>
                                        <td>
                                            <div class=" error-msg">
                                                <select name="sub_head2[]" id="sub_head2" class="form-control frm">
                                                    <option value="">Select Account Head1</option>
                                                    @foreach ($getSubHead2 as $heads)
                                                        <option value="{{ $heads->head_id }}" <?php if($value->sub_head2 == $heads->head_id) { ?>
                                                            selected="selected" <?php } ?>>
                                                            {{ $heads->sub_head }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="col-lg-12 error-msg">
                                                <input autocomplete="off" type="text" name="amount[]" id="amount"
                                                    class="form-control t_amount" style="min-width:60px;"
                                                    value="<?php if (isset($value->amount)) {
                                                        echo $value->amount;
                                                    } ?>"data-row-id="{{ $i }}" />
                                            </div>
                                            <span id="amtmsg-{{ $i }}" class="text-danger"></span>
                                        </td>
                                        <?php
                                        if ($value->receipt) {
                                            // $url = URL::to('/core/storage/images/expense/' . $value->receipt . '');
                                            $url = ImageUpload::generatePreSignedUrl('expense/' . $value->receipt);
                                        }
                                        ?>
                                        <td>
                                            <div class="col-lg-12 error-msg">
                                                <input type="file" name="receipt[]" id="receipt" class="" value="{{ $value->receipt }}">
                                            </div>
                                            @if ($value->receipt)
                                                <a href="{{ $url }}" target="blank">{{ $value->receipt }}</a>
                                            @endif
                                        </td>
                                        @if ($i != 0)
                                            <td>
                                                <i class="fa fa-trash remCF"></i>
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <!--</div>-->
                        <hr />
                        <div class=" form-group row">
                            <div class=" col-lg-1"></div>
                            <div class=" col-lg-5">
                                <button type="button" class="btn btn-primary ml-2" id="add_row">
                                    <i class="icon-add ">ADD MORE</i>
                                </button>
                            </div>
                            <div class="  col-lg-6 row">
                                <label class="col-form-label col-lg-4">Total Amount<sup>*</sup></label>
                                <div class="col-lg-6">
                                    <?php
                                    $amount = $expenseData->sum('amount');
                                    if ($billExpense->neft_charge > 0) {
                                        $amount += $billExpense->neft_charge;
                                    }
                                    ?>
                                    <input type="text" name="total_amount" id="total_amount" class="form-control"
                                        value="{{ $amount }}" readonly />
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-12 mb-4">
                            <div class="text-center">
                                <button type="submit" class="btn btn-primary submit" id="mySubmitBtn">Submit</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @include('templates.admin.expense.partial.edit_script')
    <script>
        $(document).ready(function() {
            $(document).find('#company').prop('readonly', true);
        });
    </script>
@stop
