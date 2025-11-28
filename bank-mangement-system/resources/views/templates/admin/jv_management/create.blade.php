@extends('templates.admin.master')

@section('content')

    <style>
        .search-table-outter {
            overflow-x: scroll;
        }

        .frm {
            min-width: 200px;
        }

        h5 {
            background-color: gray;
            margin: 0 -10px 0;
            padding: 4px 0 4px 10px;
        }
    </style>

    <div class="loader" style="display: none;"></div>

    <div class="content">

        <div class="row">

            <div class="col-md-12">

                <!-- Basic layout-->

                <div class="card">

                    <div class="">

                        <div class="card-body">

                            <form method="post" action="{!! route('admin.jv.save') !!}" id="jv-form">

                                <!-- <form method="post" action="javascript:void(0)" id="jv-form"> -->

                                @csrf
                                <div class="form-group row">
                                    <input type="hidden" name="type">
                                    @php
                                        $dropDown = $company;
                                        $filedTitle = 'Company';
                                        $name = 'company_id';
                                    @endphp

                                    @include('templates.GlobalTempletes.new_role_type', [
                                        'dropDown' => $dropDown,
                                        'filedTitle' => $filedTitle,
                                        'name' => $name,
                                        'value' => '',
                                        'multiselect' => 'false',
                                        'design_type' => 4,
                                        'branchShow' => true,
                                        'branchName' => 'branch',
                                        'apply_col_md' => false,
                                        'multiselect' => false,
                                        'placeHolder1' => 'Please Select Company',
                                        'placeHolder2' => 'Please Select Branch',
                                    ])
                                    <div class="col-md-4">

                                        <div class="form-group row">

                                            <label class="col-form-label col-lg-12">Date<sup>*</sup></label>

                                            <div class="col-lg-12 error-msg">

                                                <input type="text" name="cre_date" class="form-control cre_date"
                                                    autocomplete="off" readonly>

                                            </div>

                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group row">

                                            <label class="col-form-label col-lg-12">Journal<sup>*</sup></label>

                                            <div class="col-lg-12 error-msg">

                                                <input type="text" id="journal" name="journal" class="form-control"
                                                    value="{{ $jv_auto_id }}" readonly="">

                                            </div>

                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group row">

                                            <label class="col-form-label col-lg-12">Reference<sup>*</sup></label>

                                            <div class="col-lg-12 error-msg">

                                                <input type="text" name="reference" id="reference" class="form-control">

                                            </div>

                                        </div>
                                    </div>

                                </div>

                                <!-- <div class="form-group row">

                                                      <label class="col-form-label col-lg-2">Notes<sup>*</sup></label>

                                                        <div class="col-lg-4 error-msg">

                                                            <textarea id="notes" name="notes" class="form-control "></textarea>

                                                        </div>

                                                    </div> -->

                                {{-- <div class="form-group row">

                                    <label class="col-form-label col-lg-2">Branch <sup>*</sup></label>

                                    <div class="col-lg-4 error-msg">

                                        <select name="branch" id="branch" class="form-control">

                                            @foreach ($branches as $branch)
                                                <option value="{{ $branch->id }}">{{ $branch->name }}
                                                    ({{ $branch->branch_code }})</option>
                                            @endforeach

                                        </select>

                                    </div>

                                </div> --}}

                                <div class="search-table-outter wrapper mt-5">
                                    <table class="table table-flush" id="expense1">

                                        <thead>

                                            <tr>

                                                <th>ACCOUNT HEAD 1 </th>

                                                <th>ACCOUNT HEAD 2</th>

                                                <th>ACCOUNT HEAD 3</th>

                                                <th>ACCOUNT HEAD 4</th>

                                                <th>ACCOUNT HEAD 5</th>

                                                <th>CONTACT </th>

                                                <th>DESCRIPTION</th>

                                                <th>DEBITS </th>

                                                <th>CREDITS</th>

                                            </tr>

                                        </thead>

                                        <tbody id="expense">

                                            <tr>

                                                <td class="row-1-section">
                                                    <div class="error-msg frm">
                                                        <select name="account_head[1]" id="account_head"
                                                            class="form-control account-head-1 account_head_more account_head_dropdown frm"
                                                            data-row="1" data-value="1">
                                                            <option value="0">Select Account Head1</option>
                                                            @foreach ($account_heads as $heads)
                                                                @if ($heads->head_id == 1 || $heads->head_id == 4)
                                                                    <option data-parent-id="{{ $heads->parent_id }}"
                                                                        value="{{ $heads->head_id }}">{{ $heads->sub_head }}
                                                                    </option>
                                                                @endif
                                                            @endforeach

                                                        </select>
                                                    </div>
                                                    <input type="hidden" name="selecetd-head"
                                                        class="selecetd-head selecetd-head-1" data-row="1" data-value="1">
                                                    <input type="hidden" name="selecetd-head-p"
                                                        class="selecetd-head-p selecetd-head-p-1" data-row="1"
                                                        data-value="1">
                                                </td>

                                                <td class="row-1-section">
                                                    <div class="error-msg frm">
                                                        <select name="sub_head1[1]" id="sub_head1"
                                                            class="form-control sub-head1-1 1-sub_head1_more sub_head1_more account_head_dropdown frm"
                                                            data-row="1" data-value="1">
                                                            <option value="0">Select Account Head2</option>
                                                        </select>
                                                    </div>
                                                </td>

                                                <td class="row-1-section">
                                                    <div class=" error-msg frm">
                                                        <select name="sub_head2[1]" id="sub_head2"
                                                            class="form-control sub-head2-1 1-sub_head2_more sub_head2_more account_head_dropdown frm"
                                                            data-row="1" data-value="1">
                                                            <option value="0">Select Account Head3</option>
                                                        </select>
                                                    </div>
                                                </td>

                                                <td class="row-1-section">
                                                    <div class="error-msg frm">
                                                        <select name="sub_head3[1]" id="sub_head3"
                                                            class="form-control sub-head3-1 1-sub_head3_more sub_head3_more account_head_dropdown frm"
                                                            data-row="1" data-value="1">
                                                            <option value="0">Select Account Head4</option>
                                                        </select>
                                                    </div>
                                                </td>

                                                <td class="row-1-section">
                                                    <div class=" error-msg frm">
                                                        <select name="sub_head4[1]" id="sub_head4"
                                                            class="form-control sub-head4-1 1-sub_head4_more sub_head4_more account_head_dropdown frm"
                                                            data-row="1" data-value="1">
                                                            <option value="0">Select Account Head5</option>
                                                        </select>
                                                    </div>
                                                </td>

                                                <td class="row-1-section">
                                                    <div class=" error-msg frm">
                                                        <select name="contact[1]" id="contact"
                                                            class="form-control  head-contact contact-1 account_head_dropdown frm select2"
                                                            data-row="1" data-value="1">
                                                            <option value="">Select Contact Number</option>
                                                        </select>

                                                        <!--<input class="form-control  head-contact contact-1 frm" placeholder="Contact" type="text" name="contact[1]" id="contact" autocomplete="off" data-row="1" data-value="1">
                                                                            <div id="suggesstion-box-1"></div>-->
                                                    </div>
                                                    <input type="hidden" name="contact_account[1]"
                                                        class="contact-account-1">
                                                </td>

                                                <td class="row-1-section">
                                                    <div class=" error-msg frm">
                                                        <input type="text" name="description[1]" id="description"
                                                            class="form-control head-description description-1 frm"
                                                            data-row="1" data-value="1" required="" />
                                                    </div>
                                                </td>

                                                <td class="row-1-section">
                                                    <div class=" error-msg frm">
                                                        <input type="text" name="debit[1]" id="debit"
                                                            class="form-control debit-amount debit-1 frm" data-row="1"
                                                            data-value="1" />
                                                    </div>
                                                </td>

                                                <td class="row-1-section">
                                                    <div class=" error-msg frm">
                                                        <input type="text" name="credit[1]" id="credit"
                                                            class="form-control credit-amount credit-1 frm" data-row="1"
                                                            data-value="1" />
                                                    </div>
                                                </td>
                                            </tr>

                                        </tbody>

                                    </table>

                                </div>

                                <div class="d-flex justify-content-between mt-4">
                                    <div class="">
                                        <button type="button" class="btn btn-primary" id="add_row" data-row="1"><i
                                                class="icon-add"> ADD MORE</i></button>
                                    </div>
                                </div>

                                <div class="d-flex flex-wrap mt-4">

                                    <div class="t col-lg-3 rounded border ml-1 mt-1" id="Member"
                                        style="display:none;">
                                        <h5 class="text-white">Member Detail</h5>
                                        <div class="m-box">
                                        </div>
                                    </div>

                                    <div class="t col-lg-3 rounded border ml-1 mt-1" id="emp"
                                        style="display:none;">
                                        <h5 class="text-white">Employee Detail</h5>
                                        <div class="e-box ">
                                        </div>
                                    </div>

                                    <div class="t col-lg-3 rounded border ml-1 mt-1" id="rent"
                                        style="display:none;">
                                        <h5 class="text-white">Rent Detail</h5>
                                        <div class="r-box">
                                        </div>
                                    </div>


                                    <div class="t col-lg-3 rounded border ml-1 mt-1" id="associate"
                                        style="display:none;">
                                        <h5 class="text-white">Associate Detail</h5>
                                        <div class="a-box">
                                        </div>
                                    </div>

                                    <div class="t col-lg-3 rounded border ml-1 mt-1" id="director"
                                        style="display:none;">
                                        <h5 class="text-white">Director Detail</h5>
                                        <div class="d-box">
                                        </div>
                                    </div>

                                    <div class="t col-lg-3 rounded border ml-1 mt-1" id="shareholder"
                                        style="display:none;">
                                        <h5 class="text-white">Shareholder Detail</h5>
                                        <div class="s-box">
                                        </div>
                                    </div>


                                    <div class="t col-lg-3 rounded border ml-1 mt-1" id="investment"
                                        style="display:none;">
                                        <h5 class="text-white">Investment Detail</h5>
                                        <div class="re-box">
                                        </div>
                                    </div>

                                    <div class="t col-lg-3 rounded border ml-1 mt-1" id="load_account"
                                        style="display:none;">
                                        <h5 class="text-white">Loan Account Detail</h5>
                                        <div class="la-box">
                                        </div>
                                    </div>

                                    <div class="t col-lg-3 rounded border ml-1 mt-1" id="saving_account"
                                        style="display:none;">
                                        <h5 class="text-white">Saving Account Detail</h5>
                                        <div class="sa-box">
                                        </div>
                                    </div>

                                    <div class="t col-lg-3 rounded border ml-1 mt-1" id="bank_detail"
                                        style="display:none;">
                                        <h5 class="text-white">Bank Account Detail</h5>
                                        <div class="bank-box">
                                        </div>
                                    </div>
                                </div>

                                <div class="t col-lg-3 rounded border ml-1 mt-1" id="vendor_detail"
                                    style="display:none;">
                                    <h5 class="text-white">Vendor Detail</h5>
                                    <div class="vendor-box">
                                    </div>
                                </div>

                                <div class="t col-lg-3 rounded border ml-1 mt-1" id="customer_detail"
                                    style="display:none;">
                                    <h5 class="text-white">Customer Detail</h5>
                                    <div class="customer-box">
                                    </div>
                                </div>

                                <div class="t col-lg-3 rounded border ml-1 mt-1" id="creditCard_detail"
                                    style="display:none;">
                                    <h5 class="text-white">Credit card Detail</h5>
                                    <div class="creditCard-box">
                                    </div>
                                </div>

                                <div class="t col-lg-3 rounded border ml-1 mt-1" id="company_bond_detail"
                                    style="display:none;">
                                    <h5 class="text-white">Company Bond Detail</h5>
                                    <div class="company_bond-box">
                                    </div>
                                </div>
                      
                        <div class="d-flex justify-content-between mt-4">

                            <div class="t col-lg-7  rounded border">

                                <div class="d-flex justify-content-between my-2">

                                    <div class="total-label">Sub Total</div>

                                    <div class="total-amount debit-sub-total"> 0.00 </div>

                                    <div class="total-amount credit-sub-total"> 0.00 </div>

                                </div>

                                <div class="d-flex justify-content-between my-2">

                                    <div class="total-label">Total (Rs.)</div>

                                    <div class="total-amount debit-total"> 0.00 </div>

                                    <div class="total-amount credit-total"> 0.00 </div>

                                </div>

                                <!--  <div class="d-flex justify-content-between text-danger my-2">

                                                                <div class="total-label">Difference</div>

                                                                <div class="total-amount text-danger d-inline-block amount-diff"> 0.00 </div>

                                                            </div>
                                              -->
                            </div>

                        </div>

                        <div class="text-right mt-4">

                            <input type="submit" name="submitform" value="Submit" class="btn btn-primary submit">

                            <input type="reset" name="resetform" value="Reset"
                                class="btn btn-gray legitRipple reset">

                        </div>

                        </form>
                    </div>

                    </div>

                </div>

                <!-- /basic layout -->

            </div>

        </div>

    </div>

    </div>

@stop

@section('script')

    @include('templates.admin.jv_management.partials.create_script')

@stop
