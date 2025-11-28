@extends('layouts/branch.dashboard')

@section('content')
<style type="text/css">
  .datepicker.dropdown-menu {
    z-index: 9999999 !important;
}
</style>
<div class="container-fluid mt--6">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-lg-12">
                <div class="card bg-white">
                <div class="card-body">
                    <div class="">
                        <h3 class="">Loan Listing</h3>
                    </div>
                </div>
                </div>
            </div>
        </div>
        
     <div class="row">    
       
		 <div class="col-md-12">
            <div class="card">
                <div class="card-header header-elements-inline">
                    <h3 class="card-title font-weight-semibold">Search Filter</h3>
                </div>
                <div class="card-body">
                    <form action="#" method="post" enctype="multipart/form-data" id="filter" name="filter">
                    @csrf
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">Date From</label>
                                    <div class="col-lg-12 error-msg">
                                         <div class="input-group">
                                             <input type="text" class="form-control from_date" name="date_from"> 
                                           </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">Date To</label>
                                    <div class="col-lg-12 error-msg">
                                         <div class="input-group">
                                             <input type="text" class="form-control to_date" name="date_to"> 
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
                                    <label class="col-form-label col-lg-12">Associate code  </label>
                                    <div class="col-lg-12 error-msg">
                                        <input type="text" name="associate_code" id="associate_code" class="form-control"  > 
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">Loan Type </label>
                                    <div class="col-lg-12 error-msg">
                                        <div class="input-group">
                                            <select class="form-control" id="plan" name="plan">
                                                <option value=""  >----Select----</option> 
                                                @foreach( App\Models\Loans::pluck('name', 'id') as $key => $val )
                                                    @if($key != 3)
                                                        <option value="{{ $key }}"  >{{ $val }}</option> 
                                                    @endif
                                                @endforeach
                                            </select>
                                           </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group row">
                                    <label class="col-form-label col-lg-12">Status </label>
                                    <div class="col-lg-12 error-msg">
                                        <select class="form-control" id="status" name="status">
                                            <option value="">Select status</option>
                                            <!-- <option value="0">Pending</option> -->
                                            <option value="1">Approved</option>
											<option value="0">Pending</option>
                                            <option value="3" selected>Clear</option>
                                            <option value="4">Due</option>
                                            <option value="5">Rejected</option>
                                            <option value="6">Hold</option>
                                            <option value="7">Approved but hold</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group row"> 
                                    <div class="col-lg-12 text-right" >
                                        <input type="hidden" name="is_search" id="is_search" value="no">
                                      <input type="hidden" name="loan_recovery_export" id="loan_recovery_export" value="">
                                        <button type="button" class=" btn btn-primary legitRipple" onClick="searchForm()" >Submit</button>
                                        <button type="button" class="btn btn-gray legitRipple" id="reset_form" onClick="resetForm()" >Reset </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
            <div class="col-lg-12">                

                <div class="card bg-white shadow">
                    <div class="card-header bg-transparent">
                        <div class="row">
                            <div class="col-md-8">
                            <h3 class="mb-0 text-dark">Loans</h3>
                        </div>
                            <div class="col-md-4 text-right">
                             <button type="button" class="btn btn-primary legitRipple export ml-2" data-extension="0" style="float: right;">Export xslx</button>
							              <button type="button" class="btn  btn-primary legitRipple export" data-extension="1">Export PDF</button>
                        </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table id="loan-listing" class="table table-flush">
                            <thead class="">
                                <tr>
                                <th>S/N</th>
                                <th>Action</th>
                                 <th>BR Name</th>
                                    <!-- <th>BR Code</th>
                                    <th>SO Name</th>
                                    <th>RO Name</th>
                                    <th>ZO Name</th> -->
                                <th>A/C No.</th>
                                <th>Member Name</th>
                                <th>Member Id</th>
                                <th>Total Deposit</th>
                                <th>Loan Type</th>
                                <th>Tenure</th>
                                 <th>Emi Amount</th>
                                <th>Transfer Amount</th>
                                <th>Loan Amount</th>
                                <th>File Charges</th>
                                  <th>Insurance Charge</th>
                                <th>File Charge Payment Mode</th>
                                <th>Outstanding Amount</th>   
                                <th>Last Recovery Date</th>   
                                <th>Associate Code</th> 
                                <th>Associate Name</th>  
                                <th>Bank Name</th>
                                <th>Bank Account Number</th>
                                <th>IFSC Code</th> 
                                <th>Total Payment</th>  
                                <th>Approved Date</th> 
                                <th>Sanction Date</th> 
                                <th>Application Date</th>
                                <th>Collector Code</th>
                                <th>Collector Name</th>
                                <th>Rejected Reason</th>
                                <th>Status</th>
                                
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

     <div class="modal fade" id="rejection-view" tabindex="-1" role="dialog" aria-labelledby="modal-form" aria-hidden="true">
      <div class="modal-dialog modal- modal-dialog-centered modal-sm" role="document" style="max-width: 600px !important; ">
        <div class="modal-content">
          <div class="modal-body p-0">
            <div class="card bg-white border-0 mb-0">
              <div class="card-header bg-transparent pb-2ß">
                <div class="text-dark text-center mt-2 mb-3">View Admin  Remark</div>
              </div>
              <div class="card-body px-lg-5 py-lg-5">
                  <div class="form-group row">
                    <!-- <label class="col-form-label col-lg-2">Corrections</label> -->
                    <div class="col-lg-12 loan-rejected-description">              
                    </div>
                  </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="modal fade" id="pay-loan-emi" tabindex="-1" role="dialog" aria-labelledby="modal-form" aria-hidden="true">
      <div class="modal-dialog modal- modal-dialog-centered modal-sm" role="document" style="max-width: 800px !important; ">
        <div class="modal-content">
          <div class="modal-body p-0">
            <div class="card bg-white border-0 mb-0">
              <div class="card-header bg-transparent pb-2ß">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <div class="text-dark text-center mt-2 mb-3"><span>Pay Your EMI</div>
              </div>
              <div class="card-body px-lg-5 py-lg-5">
                  <form action="{{route('loan.depositeloanemi')}}" method="post" id="loan_emi" name="loan_emi">
                      @csrf
                      <input type="hidden" name="loan_id" id="loan_id">
                        @php
                          $stateid = getBranchState(Auth::user()->username);
                        @endphp
                        
                        <input type="hidden" name="created_at" id="created_at" value="{{ checkMonthAvailability(date('d'),date('m'),date('Y'),$stateid) }}">
                        <input type="hidden" name="created_date" id="created_date" value="">
                        <input type="hidden" name="ssb_id" id="ssb_id">
                        <input type="hidden" name="associate_member_id" id="associate_member_id">
                        <input type="hidden" name="branch" value="{{ $bId }}">
                        <input type="hidden" name="type" id="type" value="loan">
                        <div class="form-group row">
                          <label class="col-form-label col-lg-2">Deposit Date<sup>*</sup></label>
                          <div class="col-lg-4">
                            <input type="text" name="application_date" id="deposite_date"  class="form-control " autocomplete="off" value="{{ headerMonthAvailability(date('d'),date('m'),date('Y'),$stateid) }}" readonly>
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

                            <label class="col-form-label col-lg-2">Deposite Amount</label>
                            <div class="col-lg-4">
                              <input type="text" name="deposite_amount" id="deposite_amount" class="form-control">
                              <label class="error ssbamount-error"></label>
                            </div>
                        </div>

                        <div class="form-group row">
                          <label class="col-form-label col-lg-2">Closer Amount</label>
                          <div class="col-lg-4">
                            <input type="text" name="closing_amount" id="closing_amount" class="form-control" readonly="">
                          </div>

                          <label class="col-form-label col-lg-2">Penalty Amount</label>
                          <div class="col-lg-4">
                            <input type="text" name="penalty_amount" id="penalty_amount" class="form-control" value="0" readonly="">
                          </div>
                        </div>
                        <div class="form-group row gst1" style="display:none;">
                            <label class="col-form-label col-lg-2" id="label1"></label>
                            <div class="col-lg-4 gst1">
                              <input type="text" name="cgst_amount" id="cgst_amount" class="form-control" readonly="">
                            </div>

                            <label class="col-form-label col-lg-2" id="label2"></label>
                            <div class="col-lg-4 gst1">
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
                          <div class="form-group row gst2" style="display:none;">
                            <label class="col-form-label col-lg-2" id="label3"></label>
                            <div class="col-lg-4">
                              <input type="text" name="igst_amount" id="igst_amount" class="form-control" readonly="">
                            </div>
                        </div>

                        <div class="form-group row">
                            <!-- <label class="col-form-label col-lg-2">Select Branch</label>
                            <div class="col-lg-4">
                              <select class="form-control" name="branch" id="loan_branch">
                                    <option value="">----Select Branch----</option>
                                    @foreach( App\Models\Branch::pluck('name', 'id') as $key => $val )
                                    <option value="{{ $key }}"  >{{ $val }}</option> 
                                    @endforeach 
                              </select>
                            </div> -->

                            <label class="col-form-label col-lg-2">Payment Mode</label>
                            <div class="col-lg-4">
                              <select class="form-control" name="loan_emi_payment_mode" id="loan_emi_payment_mode">
                                <option value="">----Select----</option>
                                <option value="2">Cash</option>     
                                <option value="0">SSB Account</option>
                                <option value="1">Bank</option>   
                              </select>
                            </div>

                            <label class="col-form-label col-lg-2 ssb-account" style="display: none;">SSB Account Balance</label>
                            <div class="col-lg-4 ssb-account" style="display: none;">
                              <input type="text" name="ssb_account" id="ssb_account" class="form-control" readonly="">
                            </div>
                        </div>

                        <div class="form-group row ssb-account" style="display: none;">
                          <label class="col-form-label col-lg-2">SSB A/C Number</label>
                          <div class="col-lg-4 ssb-account">
                            <input type="text" name="ssb_account_number" id="ssb_account_number" class="form-control" readonly="">
                          </div>
                        </div>

                       <!--  <h5 class="card-title font-weight-semibold other-bank" style="display: none;">Customer Bank details</h5>
                        <div class="form-group row other-bank" style="display: none;">
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
                                   <!--  <option value="">----Please Select----</option>
                                    @foreach($cheques as $val)
                                        <option value="{{ $val->cheque_no }}" class="{{ $val->deposit_account_id }}-c-cheque c-cheque" >{{ $val->cheque_no }}</option>
                                    @endforeach -->
                                </select> 
<!--                                 //<input type="text" name="customer_cheque" id="customer_cheque" class="form-control">
 -->                            </div>

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
                            <!-- <label class="col-form-label col-lg-2 cheque-transaction" style="display: none">Amount</label>
                            <div class="col-lg-4 cheque-transaction" style="display: none">
                                <input type="text" name="cheque_total_amount" id="cheque_total_amount" class="form-control" value="" readonly="">
                            </div>
 -->
                            <label class="col-form-label col-lg-2 online-transaction" style="display: none">Amount</label>
                            <div class="col-lg-4 online-transaction" style="display: none">
                                <input type="text" name="total_online_amount" id="total_online_amount" value="" class="form-control" readonly="">
                            </div>
                        </div>
                           <div id="company_bank_detail">
                        <h5 class="card-title font-weight-semibold company_bank_detail" style="display: none;">Company Bank</h5>
                        <div class="form-group row other-bank" style="display: none;">
                            <label class="col-form-label col-lg-2">Select Bank<sup>*</sup></label>
                            <div class="col-lg-4">
                                <select name="company_bank" id="company_bank" class="form-control">
                                    <option value="">----Please Select----</option>
                                    @foreach( $cBanks as $key => $bank)
                                        @php
                                        $balance = $balance = App\Models\SamraddhBankClosing::where('bank_id',$bank->id )->orderBy('id', 'desc')->first();
                                        
                                    @endphp
                                        @if($bank['bankAccount'])
                                        
                                            <option  value="{{ $bank->id }}" data-balance = "{{$balance ? $balance->balance:''}}" data-account="{{ $bank['bankAccount']->account_no }}">{{ $bank->bank_name }}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>

                            <label class="col-form-label col-lg-2">Select Bank A/C</label>
                            <div class="col-lg-4">
                                <select name="bank_account_number" id="bank_account_number" class="form-control">
                                    <option value="">----Please Select----</option>
                                    @foreach($cBanks as $bank)
                                        @if($bank['bankAccount'])
                                            <option class="{{ $bank->id }}-bank-account c-bank-account" value="{{ $bank['bankAccount']->account_no }}" data-account="{{$bank['bankAccount']->id}}"  style="display: none;">
                                            {{ $bank['bankAccount']->account_no }}</option>
                                        @endif
                                    @endforeach
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
@include('templates.branch.loan_management.partials.script')
@stop
