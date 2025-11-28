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
$selectedCompany = $billExpense->company_id ;
@endphp
<div class="container-fluid mt--6">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-lg-12">
                <div class="card bg-white">
                    <div class="card-body page-title">
                        <h3 class="">{{$title}}</h3>
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
                    <form method="post" action="{!! route('branch.expense.update') !!}" id="expenses" name="expenses" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" class="form-control created_at " name="created_at" id="created_at">

                        <input type="hidden" class="form-control create_application_date " name="create_application_date" id="create_application_date">
                        <input type="hidden" name="bill_no" value="{{$billExpense->bill_no}}">
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
                                    <label class="col-form-label col-lg-2">Branch<sup class="text-danger">*</sup></label>
                                    <div class="col-lg-4">
                                        <select name="branch_id" id="branch_id" class="form-control">
                                            <option value="{{ $branch->id}}" selected="selected">{{ $branch->name }}</option>

                                        </select>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-2">Date<sup class="text-danger">*</sup></label>
                                    <div class="col-lg-4">
                                        <input type="text" name="expensesDate" class="form-control expensesDate" id="expensesDate" readonly value="{{ isset($expenseData[0]->bill_date) ?  date('d/m/Y', strtotime($expenseData[0]->bill_date)) : '' }}">
                                    </div>
                                    <label class="col-form-label col-lg-2">Party Name<sup class="text-danger">*</sup></label>
                                    <div class="col-lg-4 error-msg">
                                        <input type="text" name="party_name" id="party_name" class="form-control" value="{{$billExpense->party_name}}">
                                    </div>

                                </div>
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-2">Payment Mode<sup class="text-danger">*</sup></label>
                                    <div class="col-lg-4 error-msg">
                                        <select name="payment_mode" id="payment_mode" class="form-control">
                                            <option value="">----Select Payment Mode ----</option>
                                            <option value="0" @if($billExpense->payment_mode == 0 ) selected @endif>Cash</option>
                                           <!-- <option value="1" @if($billExpense->payment_mode == 1 ) selected @endif>Cheque</option>
                                            <option value="2" @if($billExpense->payment_mode == 2 ) selected @endif>Online Transaction</option>-->
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group row cash_box" id="cash">
                                    <label class="col-form-label col-lg-2">Branch Balance</label>
                                    <div class="col-lg-4">
                                        <input type="text" name="branch_total_balance" id="branch_total_balance" readonly class="form-control" value="{{$billExpense->branch_balance}}">
                                    </div>
                                </div><br />
                                <div id="bank_details" style="display: none;">
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
                                                <option value="{{ $val->id }}" <?php if ($billExpense->bank_id == $val->id) { ?> selected="selected" <?php } ?>>{{ $val->bank_name }}</option>
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
                                                <option value="{{ $val->id }}" <?php if ($billExpense->account_id == $val->id) { ?> selected="selected" <?php } ?>>{{ $val->account_no }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="form-group row" style="display:none;">
                                        <label class="col-form-label col-lg-2">Bank Balance</label>
                                        <div class="col-lg-10 error-msg">
                                            <input type="text" class="form-control" name="bank_balance" id="bank_balance" readonly value="{{$billExpense->bank_balance}}">
                                        </div>
                                    </div>
                                    <div id="chq_details" style="display:none;">

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
                                                    @foreach ($cheques as $cheq)
                                                    <option value="{{ $cheq->id }}" <?php if ($billExpense->cheque_id == $cheq->id) { ?> selected="selected" <?php } ?>>{{ $cheq->cheque_no }}</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                        </div>
                                    </div>
                                    <div id="online_details" style="display:none;">

                                        <div class="form-group row">
                                            <div class="col-md-12 ">
                                                <h5 class="card-title">Party Bank Detail :</h5>
                                            </div>
                                        </div>

                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-2">Party Bank Name<sup class="text-danger">*</sup></label>
                                            <div class="col-lg-4 error-msg">
                                                <input type="text" class="form-control" name="party_bank_name" id="party_bank_name" value="{{$billExpense->party_name}}">
                                            </div>
                                            <label class="col-form-label col-lg-2">
                                                Party Bank A/C No<sup class="text-danger">*</sup>
                                            </label>
                                            <div class="col-lg-4 error-msg">
                                                <input type="text" class="form-control" name="party_bank_ac_no" id="party_bank_ac_no" value="{{$billExpense->party_bank_ac_no}}">
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-2">Party Bank Ifsc<sup class="text-danger">*</sup></label>
                                            <div class="col-lg-4 error-msg">
                                                <input type="text" class="form-control" name="party_bank_ifsc" id="party_bank_ifsc" value="{{$billExpense->party_bank_ifsc}}">
                                            </div>
                                            <label class="col-form-label col-lg-2">UTR / Transaction No<sup class="text-danger">*</sup></label>
                                            <div class="col-lg-4 error-msg">
                                                <input type="text" class="form-control" name="utr_no" id="utr_tran" value="<?php if (isset($billExpense->utr_no)) {
                                                                                                                                echo $billExpense->utr_no;
                                                                                                                            } ?>" />
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-2">
                                                RTGS/NEFT Charge<sup class="text-danger">*</sup>
                                            </label>
                                            <div class="col-lg-4 error-msg">
                                                <input type="text" class="form-control" name="neft_charge" id="neft_charge" value="<?php if (isset($billExpense->neft_charge)) {
                                                                                                                                        echo $billExpense->neft_charge;
                                                                                                                                    } ?>" />
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
                                    @foreach($expenseData as $i => $value)
                                    <input type="hidden" name="expensesId[]" value="{{$value->id}}">
                                    <tr>
                                        <td>
                                            <div class="col-lg-12 error-msg">
                                                <input type="text" name="particular[]" id="particular" class="form-control frm particularRow" value="<?php if (isset($value->particular)) {
                                                                                                                                                            echo $value->particular;
                                                                                                                                                        } ?>" data-row-id="{{$i}}" />
                                            </div>
                                            <span id="msg-{{$i}}" class="text-danger"></span>
                                        </td>
                                        <td>
                                            <div class="col-lg-12 error-msg">
                                                <select name="account_head[]" id="account_head" class="form-control frm ac" data-row-id="{{$i}}">
                                                    <option value="">Select Account Head</option>
                                                    @foreach( $account_heads as $heads)
                                                    @if(in_array($selectedCompany,json_decode($heads->company_id)))
                                                    <option value="{{ $heads->head_id }}" <?php if ($value->account_head_id == $heads->head_id) { ?> selected="selected" <?php } ?>>{{ $heads->sub_head }}</option>
                                                    @endif
                                                    @endforeach

                                                </select>
                                            </div>
                                            <span id="acmsg-{{$i}}" class="text-danger"></span>
                                        </td>
                                        <?php
                                        $getSubHead  = \App\Models\AccountHeads::where('parent_id', $value->account_head_id)->get();
                                        ?>
                                        <td>
                                            <div class="error-msg">
                                                <select name="sub_head1[]" id="sub_head1" class="form-control frm">
                                                    <option value="">Select Account Head1</option>
                                                    @foreach( $getSubHead as $heads)
                                                    <option value="{{ $heads->head_id }}" <?php if ($value->sub_head1 == $heads->head_id) { ?> selected="selected" <?php } ?>>{{ $heads->sub_head }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </td>
                                        <?php
                                        $getSubHead2  = \App\Models\AccountHeads::where('parent_id', $value->sub_head1)->get();
                                        ?>
                                        <td>
                                            <div class=" error-msg">
                                                <select name="sub_head2[]" id="sub_head2" class="form-control frm">
                                                    <option value="">Select Account Head1</option>
                                                    @foreach( $getSubHead2 as $heads)
                                                    <option value="{{ $heads->head_id }}" <?php if ($value->sub_head2 == $heads->head_id) { ?> selected="selected" <?php } ?>>{{ $heads->sub_head }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="col-lg-12 error-msg">
                                                <input autocomplete="off" type="text" name="amount[]" id="amount" class="form-control t_amount" style="min-width:60px;" value="<?php if (isset($value->amount)) {
                                                                                                                                                                                    echo $value->amount;
                                                                                                                                                                                } ?>" data-row-id="{{$i}}" />
                                            </div>
                                            <span id="amtmsg-{{$i}}" class="text-danger"></span>
                                        </td>
                                        <?php
                                        if ($value->receipt) {
                                            // $url = URL::to("/core/storage/images/expense/" . $value->receipt . "");
                                            $url = ImageUpload::generatePreSignedUrl('expense/' . $value->receipt);
                                        }
                                        ?>
                                        <td>
                                            <div class=" error-msg">
                                                <input type="file" name="receipt[]" id="receipt" class="myfileinput" value="{{$value->receipt}}">
                                            </div>
                                            @if($value->receipt)

                                            <a href="{{$url}}" target="blank">{{$value->receipt}}</a>
                                            @endif
                                        </td>

                                        @if($i != 0)
                                        <td>
                                            <i class="fa fa-trash remCF"></i>
                                        </td>
                                        @endif
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
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
                                    <input type="text" name="total_amount" id="total_amount" class="form-control" value="{{$amount}}" readonly />
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
    </div> @stop
    @section('script')
    @include('templates.branch.expense.partial.edit_script')
    <script>
        $('#company').attr('disabled', true);
        $('#company_id').val($('#company').val());
    </script>
    @stop