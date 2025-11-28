@extends('templates.admin.master')
@section('content')
<style type="text/css">
  .datepicker.dropdown-menu {
    z-index: 9999999 !important;
}
.hideTableData {
        display: none;
    }
</style>
<div class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header header-elements-inline">
                    <h6 class="card-title font-weight-semibold">Search Filter</h6>
                </div>
                <div class="card-body">
                    <form action="#" method="post" enctype="multipart/form-data" id="grouploanrecoveryfilter" name="grouploanrecoveryfilter">
                    @csrf
                        <div class="row">

                            @php
                            $dropDown = $company;
                            $filedTitle = 'Company';
                            $name = 'company_id';
                            @endphp

                           @include('templates.GlobalTempletes.new_role_type',['dropDown'=>$dropDown,'filedTitle'=>$filedTitle,'name'=>$name,'value'=>'','multiselect'=>'false','design_type'=>4,'branchShow'=>true,'branchName'=>'branch_id','apply_col_md'=>true,'multiselect'=>false,'placeHolder1'=>'Please Select Company','placeHolder2'=>'Please Select Branch'])
                           <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">Loan Type <span class="required">*</span></label>
                                    <div class="col-lg-12 error-msg">
                                         <!-- <div class="input-group"> -->
                                            <select class="form-control group_loan_recovery_type  loan_typee" id="group_loan_recovery_type" name="group_loan_recovery_type">
                                                    <option  value=""  >----Select Loan Type----</option>
                                                    <option value="G">Group Loan</option>
                                            </select>
                                        <!-- </div> -->
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">Loan Plan</label>
                                    <div class="col-lg-12 error-msg">
                                         <!-- <div class="input-group"> -->
                                            <select class="form-control group_loan_recovery_plan loan_plann" id="group_loan_recovery_plan" name="group_loan_recovery_plan">
                                                    <option value=""  >----Select Loan Plan----</option>
                                            </select>
                                        <!-- </div> -->
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">Date From</label>
                                    <div class="col-lg-12 error-msg">
                                         <div class="input-group">
                                             <input type="text" readonly class="form-control from_date" id="date_from" name="date_from" autocomplete="off">
                                           </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">Date To</label>
                                    <div class="col-lg-12 error-msg">
                                         <div class="input-group">
                                             <input type="text" readonly class="form-control to_date" name="date_to" id="date_to" autocomplete="off">
                                           </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">Loan Account Number </label>
                                    <div class="col-lg-12 error-msg">
                                        <input type="text" class="form-control  " name="loan_account_number" id="loan_account_number"  >
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">Member Name </label>
                                    <div class="col-lg-12 error-msg">
                                        <input type="text" class="form-control  " name="member_name" id="member_name"  >
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                              <div class="form-group row">
                                  <label class="col-form-label col-lg-12">Member ID </label>
                                  <div class="col-lg-12 error-msg">
                                      <input type="text" name="member_id" id="member_id" class="form-control"  >
                                  </div>
                              </div>
                          </div>
                          <div class="col-md-4">
                              <div class="form-group row">
                                  <label class="col-form-label col-lg-12">Customer ID </label>
                                  <div class="col-lg-12 error-msg">
                                      <input type="text" name="customer_id" id="customer_id" class="form-control"  >
                                  </div>
                              </div>
                          </div>
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">Associate code  </label>
                                    <div class="col-lg-12 error-msg">
                                        <input type="text" name="associate_code" id="associate_code" class="form-control"  >
                                    </div>
                                </div>
                            </div>
                            <!-- <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">Plan </label>
                                    <div class="col-lg-12 error-msg">
                                        <div class="input-group">
                                            <select class="form-control" id="plan" name="plan">
                                                <option value=""  >----Select----</option>
                                                @foreach( $loan_plan as $val )
                                                  <option value="{{ $val->id }}"  >{{ $val->name }}( {{ $val->code }})</option>
                                                @endforeach
                                            </select>
                                           </div>
                                    </div>
                                </div>
                            </div> -->
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">Status </label>
                                    <div class="col-lg-12 error-msg">
                                        <select class="form-control" id="status" name="status">
                                            <option value="">Select status</option>
                                            <!-- <option value="0">Pending</option> -->
                                            <!--<option value="1">Approved</option>-->
                                            <option value="3" selected>Clear</option>
                                            <option value="4">Due</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
							<div class="col-md-4 group_loan_common">
								<div class="form-group row">
									<label class="col-form-label col-lg-12">Group Leader ID  </label>
									<div class="col-lg-12 error-msg">
										<input type="text" name="group_loan_common_id" id="group_loan_common_id" class="form-control"  >
									</div>
								</div>
							</div>
                            <div class="col-md-12">
                                <div class="form-group row">
                                    <div class="col-lg-12 text-right" >
                                        <input type="hidden" name="is_search" id="is_search" value="no">
                                        <input type="hidden" name="group_loan_recovery_export" id="group_loan_recovery_export" value="">
                                        <button type="button" class=" btn bg-dark legitRipple" onClick="groupLoanRecoverySearchForm()" >Submit</button>
                                        <button type="button" class="btn btn-gray legitRipple" id="reset_form" onClick="groupLoanRecoveryResetForm()" >Reset </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-12 table-section hideTableData">
            <div class="card">
                <div class="card-header header-elements-inline">
                    <h6 class="card-title font-weight-semibold">Group Loan Recovery</h6>
                    <div class="">
                        <button type="button" class="btn bg-dark legitRipple export-group-loan ml-2" data-extension="0" style="float: right;">Export Excel</button>
                        {{-- <button type="button" class="btn bg-dark legitRipple export-group-loan" data-extension="1">Export PDF</button> --}}
                    </div>
                </div>
                <div class="">
                    <table id="group_loan_recovery_table" class="table datatable-show-all">
                        <thead>
                            <tr>
                                <th>S/N</th>
                                <th>Company Name</th>
                                <th>BR Name</th>
                                <!-- <th>BR Code</th> -->
                                <!--<th>SO Name</th>
                                <th>RO Name</th>
                                <th>ZO Name</th>-->
                                <th>Application Number</th>
                                <th>A/C Number</th>
                                <th>Member Name</th>
                                <th>Member ID</th>
                                <th>Customer ID</th>
                                <th>Plan</th>
                                <th>Tenure</th>
                                <th>Transfer Amount</th>
                                 <th>Loan Amount</th>
                                 <th>File Charge</th>
                                 <th>Igst File Charges</th>
                                <th>Cgst File Charges</th>
                                <th>Sgst File Charges</th>
                                <th>Insurance Charges</th>
                                <th>Igst Insurance Charges</th>
                                <th>Cgst Insurance Charges</th>
                                <th>Sgst Insurance Charges</th>
                                <th>File Charge Payment Mode</th>
                                <th>Outstanding Amount</th>
                                <th>Last Recovery Date</th>
                                <th>Associate Code</th>
                                <th>Associate Name</th>
                                <th>Total Payment</th>
                                <th>Approved Date</th>
                                <th>Sanction Date</th>
                                <th>Application Date</th>
                                <th>Collector Code</th>
                                <th>Collector Name</th>
                                <th>Status</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="pay-loan-emi" tabindex="-1" role="dialog" aria-labelledby="modal-form" aria-hidden="true">
  <div class="modal-dialog modal- modal-dialog-centered modal-sm" role="document" style="max-width: 800px;">
    <div class="modal-content">
      <div class="modal-body p-0">
        <div class="card bg-white border-0 mb-0">
          <div class="card-header bg-transparent pb-2ß">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <div class="text-dark text-center mt-2 mb-3"><span>Pay Your EMI</span></div>
          </div>
          <div class="card-body px-lg-5 py-lg-5">
            <form action="{{route('admin.grouploan.depositeloanemi')}}" method="post" id="loan_emi" name="loan_emi">
              @csrf
              <input type="hidden" name="loan_id" id="loan_id">
                                        <input type="hidden" id="create_application_date" name="create_application_date" class="form-control create_application_date" value="">

              <input type="hidden" name="created_at" class="created_at">
              <input type="hidden" name="created_date" id="created_date" value="">
              <input type="hidden" name="ssb_id" id="ssb_id">
              <input type="hidden" name="associate_member_id" id="associate_member_id">
              <input type="hidden" name="type" id="type" value="group">
              <input type="hidden" name="ssbaccount" id="ssbaccount">
              <input type="hidden" name="company_id" id="companyId">

              <div class="form-group row">
                <label class="col-form-label col-lg-2">Deposit Date<sup>*</sup></label>
                <div class="col-lg-4">
                  <input type="text" name="application_date" class="form-control application_date" autocomplete="off">
                </div>
                <label class="col-form-label col-lg-2">Due Amount<sup>*</sup></label>
                <div class="col-lg-4">
                  <input type="text" name="due_amount" id="due_amount" class="form-control" readonly="">
                </div>
              </div>
              <div class="form-group row">
                <label class="col-form-label col-lg-2">Associate Code<sup>*</sup></label>
                <div class="col-lg-4">
                  <input type="text" name="loan_associate_code" id="loan_associate_code" class="form-control">
                </div>
                <label class="col-form-label col-lg-2">Associate Name<sup>*</sup></label>
                <div class="col-lg-4">
                  <input type="text" name="loan_associate_name" id="loan_associate_name" class="form-control" readonly="">
                </div>
              </div>
              <div class="form-group row">
                <label class="col-form-label col-lg-2">EMI Amount</label>
                <div class="col-lg-4">
                  <input type="text" name="loan_emi_amount" id="loan_emi_amount" class="form-control" readonly="">
                </div>
                <label class="col-form-label col-lg-2">Deposite Amount<sup>*</sup></label>
                <div class="col-lg-4">
                  <input type="text" name="deposite_amount" id="deposite_amount" class="form-control">
                  <label class="error ssbamount-error"></label>
                </div>
              </div>
              <div class="form-group row">
                <label class="col-form-label col-lg-2">Closer Amount</label>
                <div class="col-lg-4">
                  <input type="text" name="outstanding_amount" id="outstanding_amount" class="form-control" readonly="">
                </div>
                {{-- <label class="col-form-label col-lg-2">Penalty Amount</label>
                <div class="col-lg-4">
                  <input type="text" name="penalty_amount" id="penalty_amount" class="form-control" readonly="">
                </div> --}}
                    <label class="col-form-label col-lg-2">ECS Type<sup>*</sup></label>
                    <div class="col-lg-4">
                        <input type="text" name="ecs_type" id="ecs_type" class="form-control" readonly="">
                        <label class="error ssbamount-error"></label>
                    </div>
              </div>

              <div class="form-group row gst1">
                <label class="col-form-label col-lg-2" id="label1"></label>
                <div class="col-lg-4">
                  <input type="text" name="cgst_amount" id="cgst_amount" class="form-control" readonly="">
                </div>
                <label class="col-form-label col-lg-2" id="label2"></label>
                <div class="col-lg-4">
                  <input type="text" name="sgst_amount" id="sgst_amount" class="form-control" readonly="">
                </div>
              </div>
              <div class="form-group row">
                <label class="col-form-label col-lg-2">Recovered Amount</label>
                <div class="col-lg-4">
                  <input type="text" name="recovered_amount" id="recovered_amount" class="form-control" readonly="">
                </div>
                <label class="col-form-label col-lg-2">Last Recovered Amount</label>
                <div class="col-lg-4">
                  <input type="text" name="last_recovered_amount" id="last_recovered_amount" class="form-control" readonly="">
                </div>
              </div>
              <div class="form-group row gst2">
                <label class="col-form-label col-lg-2" id="label3"></label>
                <div class="col-lg-4">
                  <input type="text" name="igst_amount" id="igst_amount" class="form-control" readonly="">
                </div>
              </div>
              <div class="form-group row">
                <label class="col-form-label col-lg-2">Payment Mode<sup>*</sup></label>
                <div class="col-lg-4">
                  <select class="form-control" name="loan_emi_payment_mode" id="loan_emi_payment_mode">
                        <option value="">----Select----</option>
                        <!-- <option value="2">Cash</option>      -->
                        <option value="0">SSB Account</option>
                        <option value="1">Bank</option>
                  </select>
                </div>
                <label class="col-form-label col-lg-2">Select Branch<sup>*</sup></label>
                <div class="col-lg-4">
                  <select class="form-control" name="branch" id="loan_branch" required="">
                        <option value="">----Select Branch----</option>
                        {{-- @foreach( App\Models\Branch::pluck('name', 'id') as $key => $val )
                        <option value="{{ $key }}"  >{{ $val }}</option>
                        @endforeach --}}
                  </select>
                </div>
              </div>
              <div class="form-group row ssb-account" style="display: none;">
                <label class="col-form-label col-lg-2">Available Balance</label>
                <div class="col-lg-4 ssb-account" style="display: none;">
                  <input type="text" name="ssb_account" id="ssb_account" class="form-control" readonly="">
                </div>
                <label class="col-form-label col-lg-2">SSB A/C Number</label>
                <div class="col-lg-4 ssb-account">
                  <input type="text" name="ssb_account_number" id="ssb_account_number" class="form-control" readonly="">
                </div>
              </div>
              <h6 class="card-title font-weight-semibold other-bank" style="display: none;">Cheque details</h4>
             <!--  <div class="form-group row other-bank" style="display: none;">
                  <label class="col-form-label col-lg-2">Bank Name<sup>*</sup></label>
                  <div class="col-lg-4">
                      <input type="text" name="customer_bank_name" class="form-control" id="customer_bank_name">
                  </div>
                  <label class="col-form-label col-lg-2">Bank A/c No.<sup>*</sup></label>
                  <div class="col-lg-4">
                      <input type="text" name="customer_bank_account_number" class="form-control" id="customer_bank_account_number">
                  </div>
              </div>
              <div class="form-group row other-bank" style="display: none;">
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
                      <select name="bank_transfer_mode" id="bank_transfer_mode" class="form-control">
                          <option value="">----Please Select----</option>
                          <option value="0">Cheque</option>
                          <option value="1">Online</option>
                      </select>
                  </div>
                  <label class="col-form-label col-lg-2 cheque-transaction">Cheque No.<sup>*</sup></label>
                  <div class="col-lg-4 cheque-transaction">
                       <select name="customer_cheque" id="customer_cheque" class="form-control">
                       <!-- <option value="">----Please Select----</option>
                          @foreach($cheques as $val)
                               <option value="{{ $val->id }}"  >{{ $val->cheque_no }}({{number_format((float)$val->amount, 2, '.', '')}})</option>
                          @endforeach -->
                      </select>
<!--                       <input type="text" name="customer_cheque" id="customer_cheque" class="form-control">
 -->                  </div>
                  <label class="col-form-label col-lg-2 online-transaction">UTR number / Transaction Number<sup>*</sup></label>
                  <div class="col-lg-4 online-transaction">
                      <input type="text" name="utr_transaction_number" id="utr_transaction_number" class="form-control">
                  </div>
              </div>
              <div class="  " id="cheque-detail-show" style="display: none;">
                  <div class="form-group row">
                    <label class="col-form-label col-lg-2">Bank Name</label>
                    <div class="col-lg-4">
                      <input type="text" name="customer_bank_name" id="customer_bank_name" class="form-control" readonly>
                    </div>
                    <label class="col-form-label col-lg-2">Deposit bank Account</label>
                    <div class="col-lg-4">
                        <input type="text" name="company_bank_account_number" id="company_bank_account_number" class="form-control"  readonly>
                    </div>
                  </div>
                  <div class=" form-group row">
                    <label class="col-form-label col-lg-2">Branch Name</label>
                    <div class="col-lg-4">
                      <input type="text" name="customer_branch_name" id="customer_branch_name" class="form-control" readonly>
                    </div>
                    <label class="col-form-label col-lg-2">Cheque Date</label>
                    <div class="col-lg-4">
                      <input type="text" name="cheque-date" id="cheque-date" class="form-control" readonly>
                    </div>
                  </div>
                  <div class=" form-group row">
                    <label class="col-form-label col-lg-2">Cheque Amount</label>
                    <div class="col-lg-4">
                      <div class="rupee-img"></div>
                      <input type="text" name="cheque_total_amount" id="cheque-amount" class="form-control rupee-txt" readonly>
                    </div>
                    <label class="col-form-label col-lg-2">Deposit Bank</label>
                    <div class="col-lg-4">
                        <input type="text" name="cheque_company_bank" id="cheque_company_bank" class="form-control"  readonly>
                    </div>
                  </div>
                </div>
              <div class="form-group row other-bank">
                 <!--  <label class="col-form-label col-lg-2 cheque-transaction" style="display: none">Amount</label>
                  <div class="col-lg-4 cheque-transaction" style="display: none">
                      <input type="text" name="cheque_total_amount" id="cheque_total_amount" class="form-control" value="" readonly="">
                  </div>
 -->
                  {{-- <label class="col-form-label col-lg-2 online-transaction" style="display: none">Amount</label>
                  <div class="col-lg-4 online-transaction" style="display: none">
                      <input type="text" name="total_online_amount" id="total_online_amount" value="" class="form-control" readonly="">
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
                                    $balance = App\Models\BankBalance::where('bank_id', $bank->id)->sum('totalAmount');
                                @endphp
                                @if ($bank['bankAccount'])
                                    <option value="{{ $bank->id }}"
                                        data-balance="{{ $balance ?? '' }}"
                                        data-account="{{ $bank['bankAccount']->account_no }}">
                                        {{ $bank->bank_name }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                    <label class="col-form-label col-lg-2">Select Bank A/C</label>
                    <div class="col-lg-4">
                    <select name="bank_account_number" id="bank_account_number" class="form-control" >
                              <option value="">Select Bank</option>
                    </select>

                    </div>
                </div>
            </div>
              <div class="text-right">
                <!-- <button type="button" class="btn btn-primary" form="modal-details">Submit</button> -->
                <input type="submit" name="submitform" value="Submit" class="btn btn-primary payloan-emi">
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@stop
@section('script')
<script src="{{url('/')}}/asset/js/sweetalert.min.js"></script>
@include('templates.admin.loan.partials.script')
@endsection
