@extends('templates.admin.master')

@section('content')

<div class="content">
    <div class="row">
        @if($errors->any())
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
            <form action="#" method="post" enctype="multipart/form-data" id="filter" name="filter">
                @csrf
                <input type="hidden" name="amount_mode_exp" class="amount_mode_exp" value="{{$amount_mode}}">
                <input type="hidden" name="selectedRent_exp" class="selectedRent_exp" value="{{$selectedRent}}">
            </form>
            <div class="card">
                <div class="card-header header-elements-inline">
                    <h6 class="card-title font-weight-semibold">Rent Transfer List</h6>
                    <div class="">

                        <button type="button" class="btn bg-dark legitRipple export ml-2" data-extension="0" style="float: right;">Export xslx</button>

                    </div>
                </div>
                <form action="{!! route('admin.rent.rent_transfer_save') !!}" method="post" enctype="multipart/form-data" name="transfer_save" id="transfer_save" class="transfer_save">
                    @csrf
                    <input type="hidden" name="created_at" class="created_at">
                    <input type="hidden" name="company_id" class="company_id" value="{{ $c_id}}">
                    <div class="card-body">
                        <div class="row">

                            <div class="col-md-12">
                                <div class="table-responsive py-4">
                                    <table class="table table-flush">
                                        <thead>
                                            <tr>
                                                <th style="border: 1px solid #ddd;">S.No</th>

                                                <th style="border: 1px solid #ddd;">Company Name</th>
                                                <th style="border: 1px solid #ddd;">BR Name</th>
                                                <th style="border: 1px solid #ddd;">BR Code</th>
                                                <th style="border: 1px solid #ddd;">SO Name</th>
                                                <th style="border: 1px solid #ddd;">RO Name</th>
                                                <th style="border: 1px solid #ddd;">ZO Name</th>
                                                <th style="border: 1px solid #ddd;">Rent Type</th>
                                                <th style="border: 1px solid #ddd;">Period From </th>
                                                <th style="border: 1px solid #ddd;">Period To</th>
                                                <th style="border: 1px solid #ddd;">Address</th>
                                                <th style="border: 1px solid #ddd;">Owner Name</th>
                                                <th style="border: 1px solid #ddd;">Owner Mobile Number</th>
                                                <th style="border: 1px solid #ddd;">Owner Pan Card</th>
                                                <th style="border: 1px solid #ddd;">Owner Aadhar Card </th>
                                                <th style="border: 1px solid #ddd;">Owner SSB account </th>

                                                <th style="border: 1px solid #ddd;"> Owner Bank Name</th>
                                                <th style="border: 1px solid #ddd;">Owner Bank A/c No.</th>
                                                <th style="border: 1px solid #ddd;">Owner IFSC code </th>


                                                <th style="border: 1px solid #ddd;">Yearly Increment</th>
                                                <th style="border: 1px solid #ddd;">Office Square feet area</th>
                                                <th style="border: 1px solid #ddd;">Advance Payment Amount</th>
                                                <th style="border: 1px solid #ddd;">Security amount</th>
                                                <th style="border: 1px solid #ddd;">Rent</th>
                                                <th style="border: 1px solid #ddd;">Tds Amount</th>
                                                <th style="border: 1px solid #ddd;">Actual Rent Amount</th>


                                                <th style="border: 1px solid #ddd;">Transfer Amount</th>

                                                <th style="border: 1px solid #ddd;">Employee Code</th>
                                                <th style="border: 1px solid #ddd;">Employee Name</th>
                                                <th style="border: 1px solid #ddd;">Employee Designation</th>
                                                <th style="border: 1px solid #ddd;">Employee Mobile No.</th>

                                            </tr>

                                        </thead>
                                        <tbody>
                                            <?php $ssb_chk = 0; ?>
                                            @if(count($rent_list)>0)
                                            <?php
                                            $total_transfer = 0;
                                            ?>
                                            @foreach($rent_list as $index => $row)
                                            <?php
                                            $total_transfer = $total_transfer + ($row->actual_transfer_amount - $row->transferred_amount);
                                            ?>

                                            <tr>
                                                <td style="border: 1px solid #ddd;">{{ $index+1 }}</td>


                                                <td style="border: 1px solid #ddd;">{{ $row['rentCompany']->name }}</td>
                                                <td style="border: 1px solid #ddd;">{{ $row['rentBranch']->name }}</td>
                                                <td style="border: 1px solid #ddd;">{{ $row['rentBranch']->branch_code }}</td>
                                                <td style="border: 1px solid #ddd;">{{ $row['rentBranch']->sector }}</td>
                                                <td style="border: 1px solid #ddd;">{{ $row['rentBranch']->regan }}</td>
                                                <td style="border: 1px solid #ddd;">{{ $row['rentBranch']->zone }} </td>

                                                <td style="border: 1px solid #ddd;">{{ getAcountHead($row['rentLib']->rent_type) }} </td>
                                                <td style="border: 1px solid #ddd;"> {{date("d/m/Y", strtotime(convertDate($row['rentLib']->agreement_from)))}} </td>
                                                <td style="border: 1px solid #ddd;">{{date("d/m/Y", strtotime(convertDate($row['rentLib']->agreement_to)))}} </td>
                                                <td style="border: 1px solid #ddd;"> {{$row['rentLib']->place}}</td>
                                                <td style="border: 1px solid #ddd;">{{$row['rentLib']->owner_name}} </td>
                                                <td style="border: 1px solid #ddd;">{{$row['rentLib']->owner_mobile_number}} </td>
                                                <td style="border: 1px solid #ddd;">{{$row['rentLib']->owner_pen_number}}</td>

                                                <td style="border: 1px solid #ddd;">{{$row['rentLib']->owner_aadhar_number}}</td>
                                                <td style="border: 1px solid #ddd;">


                                                    <?php
                                                    if ($row['rentSSB']) {
                                                        echo $row['rentSSB']->account_no;
                                                        $ssb_chk++;
                                                    } else {
                                                        $ssb_chk = 0;
                                                    }
                                                    ?>
                                                </td>
                                                <td style="border: 1px solid #ddd;">{{$row->owner_bank_name}} </td>
                                                <td style="border: 1px solid #ddd;"> {{$row->owner_bank_account_number}}</td>
                                                <td style="border: 1px solid #ddd;"> {{$row->owner_bank_ifsc_code}} </td>
                                                <td style="border: 1px solid #ddd;"> {{number_format((float)$row->yearly_increment, 2, '.', '')}}%</td>
                                                <td style="border: 1px solid #ddd;"> {{$row->office_area}}</td>
                                                <td style="border: 1px solid #ddd;"> {{number_format((float)$row['rentLib']->advance_payment, 2, '.', '')}}</td>

                                                <td style="border: 1px solid #ddd;">{{number_format((float)$row->security_amount, 2, '.', '')}} </td>
                                                <td style="border: 1px solid #ddd;"> {{number_format((float)$row->rent_amount, 2, '.', '')}}</td>
                                                <td style="border: 1px solid #ddd;"> {{number_format((float)$row->tds_amount, 2, '.', '')}}</td>
                                                <td style="border: 1px solid #ddd;"> {{number_format((float)$row->actual_transfer_amount, 2, '.', '')}}</td>


                                                <td style="border: 1px solid #ddd;">{{number_format((float)($row->actual_transfer_amount-$row->transferred_amount), 2, '.', '')}} </td>
                                                <td style="border: 1px solid #ddd;"> {{$row['rentEmp']->employee_code}} </td>
                                                <td style="border: 1px solid #ddd;"> {{$row['rentEmp']->employee_name}} </td>
                                                <td style="border: 1px solid #ddd;"> {{ getDesignationData('designation_name',$row['rentEmp']->designation_id)->designation_name }} </td>
                                                <td style="border: 1px solid #ddd;"> {{$row['rentEmp']->mobile_no}} </td>

                                                <input type="hidden" name="rent_id[]" value="{{ $row->id }}" class="id_get">




                                            </tr>
                                            @endforeach
                                            <input type="hidden" name="leaser_id" id="leaser_id" value="{{$leaser_id}}">


                                            <input type="hidden" class="form-control created_at " name="created_at" id="created_at">
                                            <input type="hidden" class="form-control create_application_date " name="create_application_date" id="create_application_date">
                                        </tbody>
                                        <tfoot>

                                            <tr>
                                                <td colspan="9" align="right" style="border: 1px solid #ddd;"><strong>Total Transfer Amount</strong> </td>
                                                <td colspan="20" align="left" style="border: 1px solid #ddd;"><span id='total_payble'><strong> {{number_format((float)$total_transfer, 2, '.', '')}} </strong> </span> </td>
                                            </tr>
                                        </tfoot>


                                        @else
                                        <tfoot>
                                            <tr>
                                                <td colspan="29" align="center" style="border: 1px solid #ddd;">No record </td>
                                            </tr>
                                        </tfoot>
                                        @endif

                                    </table>
                                </div>
                            </div>
                            <div class="col-md-12" style="padding-top: 30px">
                                <div class="row">
                                    <div class="col-md-12 cheque">
                                        <h6 class="card-title font-weight-semibold"> Payment Detail</h6>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-3">Amount Mode </label>
                                            <div class="col-lg-7 error-msg">
                                                <select class="form-control" id="amount_mode" name="amount_mode">
                                                    <option value="">Select Amount Mode</option>
                                                    @if($ssb_chk>0)<option value="1" @if($amount_mode==1) selected @endif>SSB</option> @endif
                                                    <option value="2" @if($amount_mode==2) selected @endif>Bank</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-3">Payment Date </label>
                                            <div class="col-lg-7 error-msg">
                                                <div class="input-group">
                                                    <input type="text" name="select_date" id="select_date" class="form-control  " readonly>

                                                    <input type="hidden" name="ledger_date" id="ledger_date" class="form-control  " readonly value="{{$ledger_date}}">

                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row bank" style="display: none;">
                                    <div class="col-md-12">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-2">Payment Mode </label>
                                            <div class="col-lg-7 error-msg">
                                                <select class="form-control" id="payment_mode" name="payment_mode">
                                                    <option value="">Select Payment Mode</option>
                                                    <option value="1">Cheque</option>
                                                    <option value="2">Online</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12 ">
                                        <h6 class="card-title "> Bank Detail</h6>
                                    </div>
                                    <div class="col-md-6 ">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-4">Bank</label>
                                            <div class="col-lg-8 error-msg">
                                                <select class="form-control" id="bank_id" name="bank_id">
                                                    <option value="">Select Bank</option>
                                                    @foreach ($bank as $val)
                                                    <option value="{{ $val->id }}">{{ $val->bank_name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-6 ">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-4">Account Number<sup class="required">*</sup></label>
                                            <div class="col-lg-8 error-msg">
                                                <select name="account_id" id="account_id" class="form-control">
                                                    <option value="">Select Account Number</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-6 ">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-4">Bank Balance<sup class="required">*</sup></label>
                                            <div class="col-lg-8 error-msg">
                                                <input type="text" class="form-control" name="bank_balance" id="bank_balance" readonly value="0.00">
                                            </div>
                                        </div>
                                    </div>


                                    <div class="col-md-12 cheque " style="display: none;">
                                        <h6 class="card-title  "> Cheque Detail</h6>
                                    </div>

                                    <div class="col-lg-6 cheque" style="display: none;">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-4">Cheque <sup class="required">*</sup></label>
                                            <div class="col-lg-8 error-msg">
                                                <select name="cheque_id" id="cheque_id" class="form-control">
                                                    <option value="">Select Cheque</option>
                                                </select>

                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-6 cheque_detail" style="display: none;">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-4">Cheque Number <sup class="required">*</sup></label>
                                            <div class="col-lg-8 error-msg">
                                                <input type="text" class="form-control" name="cheque_number" id="cheque_number" readonly>

                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-6 cheque_detail" style="display: none;">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-4">Cheque Amount <sup class="required">*</sup></label>
                                            <div class="col-lg-8 error-msg">
                                                <input type="text" class="form-control" name="cheque_amount" id="cheque_amount" readonly>

                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12 online" style="display: none;">
                                        <h6 class="card-title  ">Online Detail</h6>
                                    </div>
                                    <div class="col-md-6 online" style="display: none;">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-4"> UTR number / Transaction Number </label>
                                            <div class="col-lg-8 error-msg">
                                                <input type="text" class="form-control" name="utr_tran" id="utr_tran">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 online" style="display: none;">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-4"> Amount </label>
                                            <div class="col-lg-8 error-msg">
                                                <input type="text" class="form-control" name="online_tran_amount" id="online_tran_amount" value="{{number_format((float)$total_transfer, 2, '.', '')}}" readonly>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 online" style="display: none;">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-4">RTGS/NEFT Charge </label>
                                            <div class="col-lg-8 error-msg">
                                                <input type="text" class="form-control" name="neft_charge" id="neft_charge">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 online" style="display: none;">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-4">Total Amount</label>
                                            <div class="col-lg-8 error-msg">
                                                <input type="text" class="form-control" name="online_total_amount" id="online_total_amount" value="{{number_format((float)$total_transfer, 2, '.', '')}}" readonly>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-2"><strong>Total Transfer Amount</strong></label>
                                            <div class="col-lg-8 error-msg">
                                                <input type="text" class="form-control" name="total_transfer_amount" id="total_transfer_amount" value="{{number_format((float)$total_transfer, 2, '.', '')}}" readonly>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>

                        </div>


                    </div>

            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12 text-center">
                        @if(count($rent_list)>0)
                        <button type="submit" class=" btn bg-dark legitRipple" id="submit_transfer">Transfer</button>
                        @endif

                    </div>
                </div>
            </div>
            </form>
        </div>
    </div>

</div>
</div>
@stop

@section('script')
@include('templates.admin.rent-management.partials.transfer_save_script')
@stop