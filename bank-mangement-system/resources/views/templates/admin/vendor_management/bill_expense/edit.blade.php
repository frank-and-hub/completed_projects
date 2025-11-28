@extends('templates.admin.master')
@section('content')

    <head>
        <style>
            .search-table-outter {
                overflow-x: scroll;
            }

            .bill_table th,
            .bill_table td {
                min-width: 200px;
            }
        </style>
    </head>
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <form action="{!! route('admin.bill.update') !!}" method="post" enctype="multipart/form-data" id="billPay"
                            name="billPay">
                            @csrf
                            <div class="row form-group">
                                @include('templates.GlobalTempletes.new_role_type', [
                                    'dropDown' => $company,
                                    'filedTitle' => 'Company',
                                    'selectedCompany' => $bill->company_id,
                                    'selectedBranch' => $bill->branch_id,
                                    'name' => 'company_id',
                                    'value' => '',
                                    'multiselect' => 'false',
                                    'design_type' => 6,
                                    'branchShow' => true,
                                    'branchName' => 'branch_id',
                                    'branchId' => 'branch_id',
                                    'apply_col_md' => true,
                                    'multiselect' => false,
                                    'placeHolder1' => 'Please Select Company',
                                    'placeHolder2' => 'Please Select Branch',
                                ])
                                <div class="col-md-6">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-4">Vendor Name</label>
                                        <div class="col-lg-8  error-msg">
                                            <div class="input-group  ">
                                                <input type="hidden" class="form-control   " name="vendor_id"
                                                    id="vendor_id" value="{{ $vid }}">
                                                <select class="form-control" id="vendor_id1" name="vendor_id1" disabled>
                                                    <option value="">Select vendor</option>
                                                    @foreach ($vendorList as $val)
                                                        <option value="{{ $val->id }}"
                                                            @if ($vid == $val->id) selected @endif>
                                                            {{ $val->name }}</option>
                                                    @endforeach
                                                </select>
                                                <input type="hidden" class="form-control" name="vendor_create_date" id="vendor_create_date" value="{{$bill->vendor->created_at->format('d-m-Y')}}" readonly>
                                                <input type="hidden" class="form-control created_at "
                                                    name="bill_created_at" id="bill_created_at">
                                                <input type="hidden" class="form-control create_application_date "
                                                    name="bill_reate_application_date" id="bill_reate_application_date">
                                                <input type="hidden" class="form-control bill_id " name="bill_id"
                                                    id="bill_id" value="{{ $bill->id }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-4">Bill</label>
                                        <div class="col-lg-8 error-msg">
                                            <div class="input-group">
                                                <input type="text" class="form-control  " name="bill" id="bill"
                                                    value="{{ $bill->bill_number }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-4">Bill Date </label>
                                        <div class="col-lg-8 error-msg">
                                            <div class="input-group">
                                                <input type="text" class="form-control  " name="bill_date" id="bill_date"
                                                    readonly
                                                    value="{{ date('d/m/Y', strtotime(convertDate($bill->bill_date))) }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row form-group">
                                <div class="col-md-12">
                                    <div class="form-group row">
                                        <h3>Multiple Item Add</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="row form-group">
                                <div class="col-md-6">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-4">Discount</label>
                                        <div class="col-lg-8">
                                            <div class="input-group">
                                                <select class="form-control" id="discount" name="discount">
                                                    <option value="2"
                                                        @if ($bill->discount_type == 2) selected @endif>At transaction
                                                        level</option>
                                                    <option value="1"
                                                        @if ($bill->discount_type == 1) selected @endif>At a line item
                                                        level</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row form-group">
                                <div class="col-md-12">
                                    <div class="search-table-outter wrapper">
                                        <table class="table bill_table">
                                            <thead>
                                                <tr>
                                                    <th>Item</th>
                                                    <th>Account Head</th>
                                                    <th>Sub Head1</th>
                                                    <th>Sub Head2</th>
                                                    <th>Sub Head3</th>
                                                    <th>HSN/SAC Code</th>
                                                    <th>Quantity</th>
                                                    <th>Rate</th>
                                                    <th class="discountField" style="display:none">Discount</th>
                                                    <th>Amount</th>
                                                    <th>Taxable Value</th>
                                                    <th>GST</th>
                                                    <th>CGST</th>
                                                    <th>SGST</th>
                                                    <th>IGST</th>
                                                    <th>Total</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody id="bill-expense-table">
                                                <?php
											$count1=0;
											$count2=0;
											if($billItem)
											{
												$count1=count($billItem)+1;
												$count2=count($billItem);
												
												foreach($billItem as $b=>$valb)
												{
											?>


                                                <tr id="trRow{{ $b }}" value="{{ $b }}">
                                                    <input type="hidden" class="form-control  itemIdrow" name="itemIdrow[]"
                                                        id="itemIdrow{{ $b }}" readonly
                                                        value="{{ $valb->id }}">
                                                    <td id="tdRow{{ $b }}" class=" error-msg">

                                                        <select class="form-control item_id"
                                                            id="item_id{{ $b }}" name="item_id[]"
                                                            data-row-id="{{ $b }}">

                                                            <option value="">Choose...</option>

                                                            @foreach ($expense_item as $k => $val1)
                                                                <option value="{{ $val1->id }}"
                                                                    @if ($valb->exp_item_id == $val1->id) selected @endif class="expenseOption" data-companyid="{{$val1->company_id}}" >
                                                                    {{ $val1->name }}</option>
                                                            @endforeach

                                                        </select>

                                                    </td>

                                                </tr>
                                                <?php
													}
												}
												?>

                                                <input type="hidden" name="itemCount" id="itemCount"
                                                    value="{{ $count1 }}" />
                                                <input type="hidden" name="itemCount_old" id="itemCount_old"
                                                    value="{{ $count2 }}" />

                                                <tr id="trRow{{ $count2 }}" value="{{ $count2 }}">

                                                    <td id="tdRow{{ $count2 }}" class=" error-msg">

                                                        <select class="form-control item_id"
                                                            id="item_id{{ $count2 }}" name="item_id[]"
                                                            data-row-id="{{ $count2 }}">

                                                            <option value="">Choose...</option>

                                                            @foreach ($expense_item as $k => $val1)
                                                                <option value="{{ $val1->id }}" class="expenseOption" data-companyid="{{$val1->company_id}}">{{ $val1->name }}
                                                                </option>
                                                            @endforeach

                                                        </select>

                                                    </td>

                                                </tr>

                                            </tbody>

                                        </table>

                                    </div>

                                </div>

                            </div>

                            <div class="row form-group">

                                <div class="col-md-12">

                                    <div class="form-group row">

                                        <div class="col-lg-2 text-left">

                                            <button type="button" class=" btn bg-dark addNewRow">Add New Row</button>

                                        </div>

                                        <div class="col-lg-2 text-left">

                                        </div>



                                        <div class="col-lg-8 ">
                                            <div class="col-lg-12">
                                                <div class="row form-group">
                                                    <div class="col-md-12">
                                                        <div class="form-group row">
                                                            <label class="col-form-label col-lg-4">Upload Bill</label>
                                                            <div class="col-lg-5">
                                                            </div>
                                                            <div class="col-lg-3 error-msg">
                                                                <div class="input-group">
                                                                    <input type="file" name="bill_upload" id="bill_upload" class="form-control bill_upload">
                                                                   @php
                                                                   $folderName = 'bill_expense/' . $bill->bill_upload;
                                                                    if (ImageUpload::fileExists($folderName) && $bill->bill_upload != '') {
                                                                        $photo_url = ImageUpload::generatePreSignedUrl($folderName);
                                                                    } else {
                                                                        $photo_url = url('/')."/asset/images/user.png";
                                                                    }                                                                    
                                                                    @endphp
                                                                </div>
                                                                <a href="{{$photo_url}}" target="_blank">Bill Link</a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row form-group">
                                                    <div class="col-md-12">
                                                        <div class="form-group row">
                                                            <label class="col-form-label col-lg-4"> Total Taxable
                                                                Value</label>
                                                            <div class="col-lg-5">
                                                            </div>
                                                            <div class="col-lg-3 error-msg">
                                                                <div class="input-group">
                                                                    <input type="text" class="form-control text-right "
                                                                        name="total_taxable_value"
                                                                        id="total_taxable_value" readonly value="0.00">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row form-group">
                                                    <div class="col-md-12">
                                                        <div class="form-group row">
                                                            <label class="col-form-label col-lg-4">Sub Total</label>
                                                            <div class="col-lg-5">
                                                            </div>
                                                            <div class="col-lg-3 error-msg">
                                                                <div class="input-group">
                                                                    <input type="text" class="form-control text-right "
                                                                        name="total_sub" id="total_sub" readonly
                                                                        value="{{ number_format((float) $bill->sub_amount, 2, '.', '') }}">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row form-group tran_div_discount"
                                                    @if ($bill->discount_type == 2) style="display:block;" @else style="display:none;" @endif>
                                                    <div class="col-md-12">
                                                        <div class="form-group row">
                                                            <label class="col-form-label col-lg-4">Discount</label>
                                                            <div class="col-lg-5 error-msg">
                                                                <div class="input-group">
                                                                    <?php
                                                                    $valDiscount = '';
                                                                    $totalAmountDiscount = '0.00';
                                                                    $typediscount = $bill->total_discount_type;
                                                                    if ($bill->discount_type == 2) {
                                                                        if ($typediscount == 1) {
                                                                            $valDiscount = $bill->total_discount_per;
                                                                            $totalAmountDiscount = number_format(((float) $bill->sub_amount * $valDiscount) / 100, 2, '.', '');
                                                                        } else {
                                                                            $valDiscount = $bill->total_discount_amount;
                                                                            $totalAmountDiscount = number_format((float) $valDiscount, 2, '.', '');
                                                                        }
                                                                    }
                                                                    ?>
                                                                    <div class="col-md-6">
                                                                        <input type="text"
                                                                            class="form-control text-right "
                                                                            name="total_discount_val"
                                                                            id="total_discount_val"
                                                                            value="{{ $valDiscount }}"
                                                                            onkeypress="return isNumberKey(event)">
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <select name="total_discount_type"
                                                                            id="total_discount_type" class="form-control">
                                                                            <option value="1"
                                                                                @if ($typediscount == 1) selected @endif>
                                                                                %</option>
                                                                            <option value="0"
                                                                                @if ($typediscount == 0) selected @endif>
                                                                                Rs.</option>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-lg-3 error-msg">
                                                                <div class="input-group">
                                                                    <input type="text" class="form-control text-right "
                                                                        name="total_dis_amt" id="total_dis_amt" readonly
                                                                        value="{{ $totalAmountDiscount }}">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row form-group ">
                                                    <div class="col-md-12">
                                                        <div class="form-group row">
                                                            <label class="col-form-label col-lg-4">Tds Head</label>
                                                            <div class="col-lg-5 error-msg">
                                                                <div class="input-group">
                                                                    <select name="tds_head" id="tds_head"
                                                                        class="form-control">
                                                                        <option value="">Select Head </option>
                                                                        @foreach ($account_heads as $k => $val)
                                                                            <option data-parentId="{{ $val->parent_id }}"
                                                                                value="{{ $val->head_id }}"
                                                                                @if ($bill->tds_head == $val->head_id) selected @endif>
                                                                                {{ $val->sub_head }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-12" id="tds_head_div"
                                                        @if ($bill->tds_head > 0) style="display:block"  @else style="display:none" @endif>
                                                        <div class="form-group row">
                                                            <label class="col-form-label col-lg-4">Tds %</label>
                                                            <div class="col-lg-5 error-msg">
                                                                <div class="input-group">
                                                                    <input type="text" class="form-control  text-right"
                                                                        name="tds_per" id="tds_per"
                                                                        value="{{ number_format((float) $bill->tds_per, 2, '.', '') }}"
                                                                        onkeypress="return isNumberKey(event)">
                                                                </div>
                                                            </div>
                                                            <div class="col-lg-3 error-msg">
                                                                <div class="input-group">
                                                                    <input type="text" class="form-control text-right "
                                                                        name="tds_amt" id="tds_amt" readonly
                                                                        value="-{{ number_format((float) $bill->tds_amount, 2, '.', '') }}"
                                                                        onkeypress="return isNumberKey(event)">
                                                                    <input type="hidden" class="form-control text-right "
                                                                        name="tds_amt_final" id="tds_amt_final" readonly
                                                                        value="{{ number_format((float) $bill->tds_amount, 2, '.', '') }}"
                                                                         >
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <div class="form-group row">
                                                            <label class="col-form-label col-lg-4">Adjustment</label>
                                                            <div class="col-lg-5 error-msg">
                                                                <div class="input-group">
                                                                    <input type="text" class="form-control text-right "
                                                                        name="adj_amount" id="adj_amount"
                                                                        value="{{ number_format((float) $bill->adj_amount, 2, '.', '') }}">
                                                                </div>
                                                            </div>
                                                            <div class="col-lg-3 error-msg">
                                                                <div class="input-group">
                                                                    <input type="text" class="form-control text-right "
                                                                        name="final_adj_amount" id="final_adj_amount"
                                                                        readonly
                                                                        value="{{ number_format((float) $bill->adj_amount, 2, '.', '') }}">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <div class="form-group row">
                                                            <label class="col-form-label col-lg-4">Total</label>
                                                            <div class="col-lg-5">
                                                            </div>
                                                            <div class="col-lg-3 error-msg">
                                                                <div class="input-group">
                                                                    <input type="text" class="form-control text-right "
                                                                        name="total_amountPay" id="total_amountPay"
                                                                        readonly
                                                                        value="{{ number_format((float) $bill->payble_amount, 2, '.', '') }}">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <div class="form-group row">
                                                            <label class="col-form-label col-lg-4">Description</label>
                                                            <div class="col-lg-8 error-msg">
                                                                <div class="input-group">
                                                                    <textarea class="form-control text-right " name="description" id="description">{{ $bill->description }}</textarea>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row form-group mx-auto ">
                                    <div class="col-md-12 mt-4">
                                        <input type="submit" class="btn bg-primary " name="save_bill" id="save_bill"
                                            value="Update">
                                    </div>
                                </div>
                        </form>
                        <div class="modal fade" id="modal-form" tabindex="-1" role="dialog"
                            aria-labelledby="modal-form" aria-hidden="true">
                            <div class="modal-dialog modal- modal-dialog-centered modal-lg" role="document">
                                <div class="modal-content">
                                    <div class="modal-body p-0">
                                        <div class="card bg-white border-0 mb-0">
                                            <div class="card-header bg-transparent pb-2ß">
                                                <div class="text-dark text-center mt-2 mb-3">Item Detail</div>
                                            </div>
                                            <div class="card-body px-lg-5 py-lg-5">
                                                <form enctype="multipart/form-data" method="post" id="item_add"
                                                    name="item_add">
                                                    @csrf
                                                    <div class="row">
                                                        <div class="col-lg-12">
                                                            <div class="row">
                                                                <input type="hidden" name="created_at"
                                                                    class="created_at" id="created_at_item">
                                                                <div class="col-lg-6">
                                                                    <div class="form-group row">
                                                                        <label class="col-form-label col-lg-4">Name<sup
                                                                                class="required">*</sup></label>
                                                                        <div class="col-lg-8 error-msg">
                                                                            <input type="text" name="name"
                                                                                id="name" class="form-control">
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="col-lg-6">
                                                                    <div class="form-group row">
                                                                        <label class="col-form-label col-lg-3">Type <sup
                                                                                class="required">*</sup> </label>
                                                                        <div class="col-lg-9 error-msg">
                                                                            <div class="row">
                                                                                <div class="col-lg-4">
                                                                                    <div
                                                                                        class="custom-control custom-radio mb-3 ">
                                                                                        <input type="radio"
                                                                                            id="type_goods" name="type"
                                                                                            class="custom-control-input"
                                                                                            value="1">
                                                                                        <label class="custom-control-label"
                                                                                            for="type_goods">Goods</label>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="col-lg-4">
                                                                                    <div
                                                                                        class="custom-control custom-radio mb-3  ">
                                                                                        <input type="radio"
                                                                                            id="type_service"
                                                                                            name="type"
                                                                                            class="custom-control-input"
                                                                                            value="2">
                                                                                        <label class="custom-control-label"
                                                                                            for="type_service">Services</label>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="col-lg-6">
                                                                    <div class="form-group row">
                                                                        <label class="col-form-label col-lg-4">HSN/SAC Code
                                                                            <sup class="required">*</sup></label>
                                                                        <div class="col-lg-8 error-msg">
                                                                            <input type="text" name="hsn_code"
                                                                                id="hsn_code" class="form-control">
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="col-lg-6">
                                                                    <div class="form-group row">
                                                                        <label class="col-form-label col-lg-4">Cost Price
                                                                            <sup class="required">*</sup></label>
                                                                        <div class="col-lg-8 error-msg">
                                                                            <input type="text" name="cost_pirce"
                                                                                id="cost_pirce" class="form-control"
                                                                                onkeypress="return isNumberKey(event)">
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="col-lg-6">
                                                                    <div class="form-group row">
                                                                        <label class="col-form-label col-lg-4">Account
                                                                            Head<sup class="required">*</sup></label>
                                                                        <div class="col-lg-8 error-msg">
                                                                            <select name="account_head" id="account_head"
                                                                                class="form-control">
                                                                                <option value="">Select Account Head
                                                                                </option>
                                                                                <option value="9">Fixed Assets
                                                                                </option>
                                                                                <option value="86">Indirect Expense
                                                                                </option>
                                                                            </select>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="col-lg-6">
                                                                    <div class="form-group row">
                                                                        <label class="col-form-label col-lg-4">Account
                                                                            Subhead1 </label>
                                                                        <div class="col-lg-8 error-msg">
                                                                            <select name="account_subhead1"
                                                                                id="account_subhead1"
                                                                                class="form-control">
                                                                                <option value="">Select Sub Head
                                                                                </option>
                                                                            </select>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="col-lg-6">
                                                                    <div class="form-group row">
                                                                        <label class="col-form-label col-lg-4">Account
                                                                            Subhead2 </label>
                                                                        <div class="col-lg-8 error-msg">
                                                                            <select name="account_subhead2"
                                                                                id="account_subhead2"
                                                                                class="form-control">
                                                                                <option value="">Select Sub Head
                                                                                </option>
                                                                            </select>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="col-lg-6">
                                                                    <div class="form-group row">
                                                                        <label class="col-form-label col-lg-4">Account
                                                                            Subhead3 </label>
                                                                        <div class="col-lg-8 error-msg">
                                                                            <select name="account_subhead3"
                                                                                id="account_subhead3"
                                                                                class="form-control">
                                                                                <option value="">Select Sub Head
                                                                                </option>
                                                                            </select>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="col-lg-6">
                                                                    <div class="form-group row">
                                                                        <label class="col-form-label col-lg-4">Description
                                                                            <sup class="required">*</sup></label>
                                                                        <div class="col-lg-8 error-msg">
                                                                            <input type="text" name="description"
                                                                                id="description" class="form-control">
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="col-lg-6">
                                                                    <div class="form-group row error-msg">
                                                                        <label class="col-form-label col-lg-4">GST </label>
                                                                        <div class="col-lg-8  input-group">
                                                                            <input type="text" name="gst"
                                                                                id="gst" class="form-control"
                                                                                onkeypress="return isNumberKey(event)">
                                                                            <select name="gst_type" id="gst_type"
                                                                                class="form-control">
                                                                                <option value="1">%</option>
                                                                                <option value="0">Rs.</option>
                                                                            </select>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="col-lg-6">
                                                                    <div class="form-group row">
                                                                        <label class="col-form-label col-lg-4">CGST
                                                                        </label>
                                                                        <div class="col-lg-8 error-msg">
                                                                            <input type="text" name="cgst"
                                                                                id="cgst" class="form-control"
                                                                                readonly>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="col-lg-6">
                                                                    <div class="form-group row error-msg">
                                                                        <label class="col-form-label col-lg-4">SGST</label>
                                                                        <div class="col-lg-8 ">
                                                                            <input type="text" name="sgst"
                                                                                id="sgst" class="form-control"
                                                                                readonly>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="col-lg-6">
                                                                    <div class="form-group error-msg  row">
                                                                        <label class="col-form-label col-lg-4">IGST</label>
                                                                        <div class="col-lg-8 input-group">
                                                                            <input type="text" name="igst"
                                                                                id="igst" class="form-control"
                                                                                onkeypress="return isNumberKey(event)">
                                                                            <select name="igst_type" id="igst_type"
                                                                                class="form-control">
                                                                                <option value="1">%</option>
                                                                                <option value="0">Rs.</option>
                                                                            </select>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="col-lg-12">
                                                                    <div class="form-group row">
                                                                        <div class="col-lg-12 text-center">
                                                                            <button type="submit" class="btn btn-primary"
                                                                                id="submit_item">Submit</button>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @include('templates.admin.vendor_management.bill_expense.partials.edit_script')
@stop
