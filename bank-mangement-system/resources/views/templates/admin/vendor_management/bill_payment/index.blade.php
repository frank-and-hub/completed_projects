@extends('templates.admin.master')

@section('content')

    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <form action="{!! route('admin.vendor_bill_payment.save') !!}" method="post" enctype="multipart/form-data" id="vendor_payment"
                            name="vendor_payment">
                            @csrf
                            <input type="hidden" name="create_application_date" id="create_application_date"
                                class=" form-control create_application_date">
                            <input type="hidden" class="form-control created_at " name="created_at" id="created_at">
                            <input type="hidden" class="form-control create_application_date "
                                name="create_application_date" id="create_application_date">
                                <input type="hidden" name="last_date" id="last_date" value="{{ date('d/m/Y', strtotime(convertDate($vendor->created_at)))}} ">
                            <div class="form-group row">
                                @include('templates.GlobalTempletes.new_role_type', [
                                    'dropDown' => $company,
                                    'filedTitle' => 'Company Name',
                                    'selectedCompany' => isset($vendor) ? $vendor->company_id : '',
                                    'name' => 'company_id',
                                    'value' => '',
                                    'multiselect' => 'false',
                                    'design_type' => 6,
                                    'branchShow' => false,
                                    'branchName' => 'branch_id',
                                    'apply_col_md' => true,
                                    'multiselect' => false,
                                    'placeHolder1' => 'Please Select Company',
                                    'placeHolder2' => 'Please Select Branch',
                                ])
                                <input type="hidden" value="{{ isset($vendor) ? $vendor->company_id : '' }}"
                                    name="company_id">
                                <div class="col-md-6">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-3">Vendor Name<sup class="required">*</sup>
                                        </label>
                                        <div class="col-lg-9 error-msg">
                                            <input type="text" name="v_name" id="v_name" class=" form-control"
                                                value="{{ $vendor->name }}" readonly>
                                            <input type="hidden" name="vid" id="vid" class=" form-control"
                                                value="{{ $vendor->id }}" readonly>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-3">Vendor Company Name<sup
                                                class="required">*</sup> </label>
                                        <div class="col-lg-9 error-msg">
                                            <input type="text" name="v_company_name" id="v_company_name"
                                                class=" form-control" value="{{ $vendor->company_name }}" readonly>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-3">Amount<sup class="required">*</sup> </label>
                                        <div class="col-lg-9 error-msg">
                                            <input type="text" name="amount" id="amount" class=" form-control"
                                                onkeypress="return isNumberKey(this)">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-3">Reference#</label>
                                        <div class="col-md-9 error-msg">
                                            <input type="text" name="ref_no" class="form-control" id="ref_no">
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-3">Payment Date<sup class="required">*</sup>
                                        </label>
                                        <div class="col-lg-9 error-msg">
                                            <input type="text" name="payment_date" id="payment_date"
                                                class=" form-control" readonly>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-3">Payment Mode<sup class="required">*</sup>
                                        </label>
                                        <div class="col-lg-9 error-msg">
                                            <select name="payment_mode" id="payment_mode" class="form-control  ">
                                                <option value="">Select Payment Mode</option>
                                                <option value="0">Cash</option>
                                                <option value="1">Cheque</option>
                                                <option value="2">Online Transaction </option>
                                                <option value="3">Eli Amount </option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row eli_amount" style="display: none;">
                                <div class="col-md-6">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-3">Eli Balance <sup class="required">*</sup>
                                        </label>
                                        <div class="col-lg-9 error-msg">
                                            <input type="text" name="eli_balance" id="eli_balance"
                                                class=" form-control" readonly>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group row cash" style="display: none;">
                                {{-- <label class="col-form-label col-lg-2">Branch<sup class="required">*</sup> </label>
							<div class="col-lg-4 error-msg">
								<select name="branch_id" id="branch_id" class="form-control  ">
									<option value="">Select Branch</option>
									@if (count($branch) > 0)
									@foreach ($branch as $index => $row)
									<option value="{{$row->id}}">{{$row->name}} - {{$row->branch_code}}</option>
							@endforeach
							@endif
							</select>
						</div> --}}
                                @include('templates.GlobalTempletes.branch_filter', [
                                    'branchName' => 'branch_id',
                                    'design_type' => 6,
                                    'placeHolder2' => 'Please Select Branch',
                                    'selectedBranch' => '',
                                ])
                                <label class="col-form-label col-lg-2">Branch Balance <sup class="required">*</sup>
                                </label>
                                <div class="col-lg-4 error-msg">
                                    <input type="text" name="branch_balance" id="branch_balance"
                                        class=" form-control" readonly>
                                </div>
                            </div>
                            <div class="form-group row bank" style="display: none;">
                                <div class="col-md-6">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-3">Bank<sup class="required">*</sup> </label>
                                        <div class="col-lg-9 error-msg">
                                            <select name="bank_id" id="bank_id" class="form-control  ">
                                                <option value="">Select Bank</option>
                                                @if (count($bank) > 0)
                                                    @foreach ($bank as $index => $row)
                                                        <option value="{{ $row->id }}"
                                                            data-companyId="{{ $row->company_id }}" style="display:none;">
                                                            {{ $row->bank_name }} </option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-3">Bank A/C<sup class="required">*</sup>
                                        </label>
                                        <div class="col-lg-9 error-msg">
                                            <select name="bank_ac" id="bank_ac" class="form-control  ">
                                                <option value="">Select account number</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-3">Bank Balance <sup class="required">*</sup>
                                        </label>
                                        <div class="col-lg-9 error-msg">
                                            <input type="text" name="bank_balance" id="bank_balance"
                                                class=" form-control" readonly>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row online" style="display: none;">
                                <div class="col-md-6">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-3">UTR Number<sup class="required">*</sup>
                                        </label>
                                        <div class="col-lg-9 error-msg">
                                            <input type="text" name="utr_no" id="utr_no" class=" form-control">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-3">RTGS/NEFT Charge <sup
                                                class="required">*</sup> </label>
                                        <div class="col-lg-9 error-msg">
                                            <input type="text" name="neft_charge" id="neft_charge"
                                                class=" form-control">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-md-6 cheque" style="display: none;">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-3">Cheque<sup class="required">*</sup>
                                        </label>
                                        <div class="col-lg-9 error-msg">
                                            <select name="cheque_id" id="cheque_id" class="form-control  ">
                                                <option value="">Select Cheque</option>
                                            </select>
                                            <input type="hidden" name="cheque_number" id="cheque_number"
                                                class=" form-control">
                                        </div>
                                    </div>
                                </div>
                            </div>
                    </div>
                    <!-- Table -->
                    <table class="table" id="pay_list">
                        <thead>
                            <tr>
                                <th>Branch</th>
                                <th>Date</th>
                                <th>Bill</th>
                                <th>Description</th>
                                <th>Bill Amount</th>
                                <th>Amount Due</th>
                                <th>Payment</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if (count($billDetail) > 0)
                                @foreach ($billDetail as $index => $row)
                                    <input type="hidden" name="bill_id[]" class="bill_id"
                                        id="bill_id{{ $row->id }}" value="{{ $row->id }}">
                                    <tr id="bill{{ $row->id }}" class="billDetailaget">
                                        <?php $bDetail = getBranchDetail($row->branch_id); ?>
                                        <td>{{ $bDetail->name }} - {{ $bDetail->branch_code }}</td>
                                        <td>{{ date('d/m/Y', strtotime(convertDate($row->bill_date))) }}
                                            <!--<br><small><span class="text-muted">Due Date: </span>22/07/2021</small>-->
                                        </td>
                                        <td>{{ $row->bill_number }}</td>
                                        <td>{{ $row->description }}</td>
                                        <td>{{ number_format((float) $row->payble_amount, 2, '.', '') }}</td>
                                        <td> <input type="text" name="bill_balance[]"
                                                class="form-control bill_balance" id="bill_balance{{ $row->id }}"
                                                onkeypress="return isNumberKey(this)" data-rowid="{{ $row->id }}"
                                                value="{{ number_format((float) $row->balance, 2, '.', '') }}"
                                                readonly=""></td>
                                        <td class="error-msg"><input type="text" name="pay_amount[]"
                                                class="form-control t_amount" id="pay_amount{{ $row->id }}"
                                                onkeypress="return isNumberKey(this)" data-rowid="{{ $row->id }}" @if($row->balance == 0) readonly @endif>
                                                <span id="msg3" class="text-danger"></span>
                                        </td>

                                        <input type="hidden" name="bill_branch_id[]" class="bill_branch_id"
                                            id="bill_branch_id{{ $row->id }}" value="{{ $row->branch_id }}">
                                        <input type="hidden" name="bill_date[]" class="bill_date"
                                            id="bill_date{{ $row->id }}"
                                            value="{{ date('d/m/Y', strtotime(convertDate($row->bill_date))) }}">
                                    </tr>
                                @endforeach
                            @endif
                        </tbody>
                        <tr>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td>Total:</td>
                            <td><input type="text" name="total_amount" class="form-control total_amount"
                                    id="total_amount" readonly></td>
                        </tr>
                    </table>
                    <div class="border-dashed alert alert-warning offset-lg-7">
                        <div class="row">
                            <p class="col-lg-8 text-right">Amount Paid:</p>
                            <p class="col-lg-4 text-right"><input type="text" name="amount_paid"
                                    class="form-control  amount_paid" id="amount_paid" readonly=""></p>
                        </div>
                        <div class="row">
                            <p class="col-lg-8 text-right">Amount used for Payments:</p>
                            <p class="col-lg-4 text-right"><input type="text" name="amount_used"
                                    class="form-control   amount_used" id="amount_used" readonly=""></p>
                        </div>
                        <div class="row">
                            <p class="col-lg-8 text-right"><i class="fas fa-exclamation-triangle mx-1"
                                    style="color:red;"></i> Amount in Excess:</p>
                            <p class="col-lg-4 text-right affected_amount" id="amount_excess_show"></p>
                            <input type="hidden" name="amount_excess" class="form-control  amount_excess"
                                id="amount_excess" readonly="">
                        </div>


                    </div>
                    <div class="col-lg-12">
                        <div class="form-group  row">
                            <div class="col-lg-12 text-center">
                                <button type="submit" class="btn btn-primary">Submit</button>
                            </div>
                        </div>
                    </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    </div>
    @include('templates.admin.vendor_management.bill_payment.partials.payment_script')
@stop
