@extends('templates.admin.master')

@section('content')
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header header-elements-inline">
                        <h6 class="card-title font-weight-semibold">Saving Account Withdrawal</h6>
                    </div>
                    <form action="{{url('admin/save-withdrawal')}}" method="post" id="withdrawal-ssb">
                        @csrf
                        <input type="hidden" name="created_at" class="created_at" id="created_at">
                        <input type="hidden" name="create_application_date" id="create_application_date" class="form-control  create_application_date" readonly >
                        <div class="modal-body">
                            <div class="form-group row">
                                <!-- <div class="col-lg-4">
                                    <label class="col-form-label col-lg-12">Select Branch </label>
                                    <div class="col-lg-12">
                                        <select name="branch" id="branch" class="form-control">
                                            <option value="">----Please Select----</option>
                                            @foreach( $branches as $key => $val )
                                            <option data-val="{{ $val->branch_code }}" value="{{ $val->id }}"  >{{ $val->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>-->
								@include('templates.GlobalTempletes.new_role_type',[
									'dropDown'=>$AllCompany,
									'filedTitle'=>'Company',
									'name'=>'company_id',
									'value'=>'',
									'multiselect'=>'false',
									'design_type'=>4,
									'branchShow'=>true,
									'branchName'=>'branch_id',
									'apply_col_md'=>false,
									'multiselect'=>false,
									'placeHolder1'=>'Please Select Company',
									'placeHolder2'=>'Please Select Branch'
								])
                                <div class="col-lg-4">
                                    <label class="col-form-label col-lg-12">Branch Code</label>
                                    <div class="col-lg-12">
                                        <input type="text" name="branch_code" id="branch_code" class="form-control" readonly="">
                                    </div>
                                </div>

                                <div class="col-lg-4">
                                    <label class="col-form-label col-lg-12">Select Date</label>
                                    <div class="col-lg-12">
                                        <input type="text" name="date" id="date" class="form-control withdrawal_date" readonly="">
                                    </div>
                                </div>

                                <div class="col-lg-4">
                                    <label class="col-form-label col-lg-12">SSB Account <span>*</span></label>
                                    <div class="col-lg-12">
                                        <input type="text" name="ssb_account_number" id="ssb_account_number" class="form-control">
                                    </div>
                                </div>

                                <div class="col-lg-4">
                                    <label class="col-form-label col-lg-12">Customer Id</label>
                                    <div class="col-lg-12">
                                        <input type="text" name="member_id" id="member_id" class="form-control" readonly="">
                                    </div>
                                </div>

                                <div class="col-lg-4">
                                    <label class="col-form-label col-lg-12">Account Holder Name</label>
                                    <div class="col-lg-12">
                                        <input type="text" name="account_holder_name" id="account_holder_name" class="form-control" readonly="">
                                    </div>
                                </div>

                                <div class="col-lg-4">
                                    <label class="col-form-label col-lg-12">Account Balance</label>
                                    <div class="col-lg-12">
                                        <input type="text" name="account_balance" id="account_balance" class="form-control" readonly="">
                                    </div>
                                </div>

                                <div class="col-lg-4">
                                    <label class="col-form-label col-lg-12">Amount <span>*</span></label>
                                    <div class="col-lg-12">
                                        <input type="text" name="amount" id="amount" class="form-control">
                                    </div>
                                </div>

                                <div class="col-lg-4">
                                    <label class="col-form-label col-lg-12">Signature</label>
                                    <div class="col-lg-12">
                                        <span class="signature"></span>
                                    </div>
                                </div>

                                <div class="col-lg-4">
                                    <label class="col-form-label col-lg-12">Photo</label>
                                    <div class="col-lg-12">
                                        <span class="photo"></span>
                                    </div>
                                </div>

                                <div class="col-lg-4">
                                    <label class="col-form-label col-lg-12">Payment Mode <span>*</span></label>
                                    <div class="col-lg-12">
                                        <select name="payment_mode" id="payment_mode" class="form-control">
                                            <option value="">----Please Select----</option>
                                            <option value="0">Cash</option>
                                            <option value="1">Bank</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-6 cash" style="display: none;">
                                    <label class="col-form-label col-lg-12">Available balance in branch</label>
                                    <div class="col-lg-12">
                                        <input type="text" name="available_balance" id="available_balance" class="form-control" readonly="">
                                    </div>
                                </div>
								<div class="col-lg-6 bank" style="display: none;">
                                    <label class="col-form-label col-lg-12">Select Bank Name</label>
                                    <div class="col-lg-12">
                                        <select name="bank" id="bank" class="form-control">
                                            <option value="">----Please Select----</option>
                                             @foreach( $bank as $key => $val )
                                            <option data-val="{{ $val['bankAccount']?$val['bankAccount']->account_no:''}}" class="{{$val->company_id}}-company-bank company-bank" value="{{ $val->id }}" style="display: none;" >{{ $val->bank_name }}</option>
                                            @endforeach

                                        </select>
                                    </div>
                                </div>

                                <div class="col-lg-6 bank" style="display: none;">
                                    <label class="col-form-label col-lg-12">Bank A/c</label>
                                    <div class="col-lg-12">

                                        <select name="bank_account_number" id="bank_account_number" class="form-control">
                                            <option value="">----Please Select ----</option>


                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-6 bank" style="display: none;">
                                    <label class="col-form-label col-lg-12">Available balance in bank</label>
                                    <div class="col-lg-12">
                                        <input type="text" name="bank_balance" id="bank_balance" class="form-control" readonly="">
                                    </div>
                                </div>

                                <div class="col-lg-6 bank" style="display: none;">
                                    <label class="col-form-label col-lg-12">Select Mode</label>
                                    <div class="col-lg-12">
                                        <select name="bank_mode" id="bank_mode" class="form-control">
                                            <option value="">----Please Select----</option>
                                            <option value="0">Cheque</option>
                                            <option value="1">Online</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-lg-6 cheque" style="display: none;">
                                    <label class="col-form-label col-lg-12">Cheque Number</label>
                                    <div class="col-lg-12">
                                        <select name="cheque_number" id="cheque_number" class="form-control">
                                            <option value="">----Please Select----</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-lg-6 online" style="display: none;">
                                    <label class="col-form-label col-lg-12">No/UTR No</label>
                                    <div class="col-lg-12">
                                        <input type="text" name="utr_no" id="utr_no" class="form-control">
                                    </div>
                                </div>

                                <div class="col-lg-6 online" style="display: none;">
                                    <label class="col-form-label col-lg-12">RTGS/NEFT Charge</label>
                                    <div class="col-lg-12">
                                        <input type="text" name="rtgs_neft_charge" id="rtgs_neft_charge" class="form-control">
                                    </div>
                                </div>
                                <div class="col-lg-6 online" style="display: none;">
                                    <label class="col-form-label col-lg-12">Member Bank</label>
                                    <div class="col-lg-12">
                                        <input type="text" name="mbank" id="mbank" class="form-control">
                                    </div>
                                </div>
                                <div class="col-lg-6 online" style="display: none;">
                                    <label class="col-form-label col-lg-12">Member Bank A/c</label>
                                    <div class="col-lg-12">
                                        <input type="text" name="mbankac" id="mbankac" class="form-control">
                                    </div>
                                </div>
                                <div class="col-lg-6 online" style="display: none;">
                                    <label class="col-form-label col-lg-12">Member Bank IFSC</label>
                                    <div class="col-lg-12">
                                        <input type="text" name="mbankifsc" id="mbankifsc" class="form-control">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <a href="{{ url()->previous() }}" type="button" class="btn btn-link" data-dismiss="modal">Back</a>
                            <button type="submit" class="btn bg-dark submit">Submit<i class="icon-paperplane ml-2"></i></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@include('templates.admin.payment-management.withdrawal.partials.script')
@stop
