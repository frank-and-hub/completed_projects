@extends('templates.admin.master')



@section('content')

<?php 

$amount_mode='';

$total_transfer =number_format((float)$rent_list->actual_transfer_amount, 2, '.', '');

?>

 

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

        <div class="col-md-12" >

                <div class="card">

                    <div class="card-header header-elements-inline">

                        <h6 class="card-title font-weight-semibold">Rent List</h6>

                    </div>

                    <form action="{!! route('admin.rent.rent_transfer_advance_save') !!}" method="post" enctype="multipart/form-data"  name="transfer_save" id="transfer_save" class="transfer_save" >

                        @csrf 

                        <input type="hidden" name="created_at" class="created_at" id="created_at">

                        

                        <input type="hidden" name="leaser_id" class="leaser_id" value="{{$leaser_id}}">

                        <input type="hidden" name="rentPaymentId" class="rentPaymentId" value=" {{ $rent_list->id }}">

                    <div class="card-body">

                    <div class="row">

                           

                        <div class="col-md-12">

                            <h6 class="card-title  ">Rent Detail </h6>

                            <div class="row">

                                <div class="col-md-4 ">

                                        <div class="form-group row">

                                            <label class=" col-md-5">BR Name</label>

                                            <div class="col-lg-7 error-msg">

                                                {{ $rent_list->rentBranch->name }}

                                            </div>

                                        </div>

                                </div>

                                <div class="col-md-4 ">

                                        <div class="form-group row">

                                            <label class=" col-md-5">BR Code</label>

                                            <div class="col-lg-7 error-msg">

                                                {{ $rent_list->rentBranch->branch_code }}

                                            </div>

                                        </div>

                                </div>

                                <div class="col-md-4 ">

                                        <div class="form-group row">

                                            <label class=" col-md-5">SO Name</label>

                                            <div class="col-lg-7 error-msg">

                                                {{ $rent_list->rentBranch->sector }}

                                            </div>

                                        </div>

                                </div>

                                <div class="col-md-4 ">

                                        <div class="form-group row">

                                            <label class=" col-md-5">RO Name</label>

                                            <div class="col-lg-7 error-msg">

                                                {{ $rent_list['rentBranch']->regan }}

                                            </div>

                                        </div>

                                </div>

                                <div class="col-md-4 ">

                                        <div class="form-group row">

                                            <label class=" col-md-5">ZO Name</label>

                                            <div class="col-lg-7 error-msg">

                                                {{ $rent_list->rentBranch->zone }}

                                            </div>

                                        </div>

                                </div>

                                <div class="col-md-4 ">

                                        <div class="form-group row">

                                            <label class=" col-md-5">Rent Type</label>

                                            <div class="col-lg-7 error-msg">

                                                {{ getAcountHead($rent_list->rentLib->rent_type) }}

                                            </div>

                                        </div>

                                </div>

                                <div class="col-md-4 ">

                                        <div class="form-group row">

                                            <label class=" col-md-5">Period From</label>

                                            <div class="col-lg-7 error-msg">

                                               {{date("d/m/Y", strtotime(convertDate($rent_list->rentLib->agreement_from)))}}

                                            </div>

                                        </div>

                                </div>

                                <div class="col-md-4 ">

                                        <div class="form-group row">

                                            <label class=" col-md-5">Period To</label>

                                            <div class="col-lg-7 error-msg">

                                                {{date("d/m/Y", strtotime(convertDate($rent_list->rentLib->agreement_to)))}}

                                            </div>

                                        </div>

                                </div>

                                <div class="col-md-4 ">

                                        <div class="form-group row">

                                            <label class=" col-md-5">Address</label>

                                            <div class="col-lg-7 error-msg">

                                                {{$rent_list->rentLib->place}}

                                            </div>

                                        </div>

                                </div>



                                <div class="col-md-4 ">

                                        <div class="form-group row">

                                            <label class=" col-md-5">Owner Name</label>

                                            <div class="col-lg-7 error-msg">

                                                {{$rent_list->rentLib->owner_name}}

                                            </div>

                                        </div>

                                </div>

                                <div class="col-md-4 ">

                                        <div class="form-group row">

                                            <label class=" col-md-5">Owner Mobile Number</label>

                                            <div class="col-lg-7 error-msg">

                                               {{$rent_list->rentLib->owner_mobile_number}}

                                            </div>

                                        </div>

                                </div>

                                <div class="col-md-4 ">

                                        <div class="form-group row">

                                            <label class=" col-md-5">Owner Pan Card</label>

                                            <div class="col-lg-7 error-msg">

                                                {{$rent_list->rentLib->owner_pen_number}}

                                            </div>

                                        </div>

                                </div>

                                <div class="col-md-4 ">

                                        <div class="form-group row">

                                            <label class=" col-md-5">Owner Aadhar Card </label>

                                            <div class="col-lg-7 error-msg"> {{$rent_list->rentLib->owner_aadhar_number}}</td>

                                                

                                            </div>

                                        </div>

                                </div>

                                <div class="col-md-4 ">

                                        <div class="form-group row">

                                            <label class=" col-md-5">Owner SSB account</label>

                                            <div class="col-lg-7 error-msg">
                                                @if($rent_list->rentSSB)
                                                    {{$rent_list->rentSSB->account_no}}
                                                @endif

                                            </div>

                                        </div>

                                </div>

                                <div class="col-md-4 ">

                                        <div class="form-group row">

                                            <label class=" col-md-5">Owner Bank Name</label>

                                            <div class="col-lg-7 error-msg">

                                                {{$rent_list->owner_bank_name}} 

                                            </div>

                                        </div>

                                </div>

                                <div class="col-md-4 ">

                                        <div class="form-group row">

                                            <label class=" col-md-5">Owner Bank A/c No.</label>

                                            <div class="col-lg-7 error-msg">

                                                {{$rent_list->owner_bank_account_number}}

                                            </div>

                                        </div>

                                </div>

                                <div class="col-md-4 ">

                                        <div class="form-group row">

                                            <label class=" col-md-5">Owner IFSC code </label>

                                            <div class="col-lg-7 error-msg">

                                               {{$rent_list->owner_bank_ifsc_code}}

                                            </div>

                                        </div>

                                </div>

                                <div class="col-md-4 ">

                                        <div class="form-group row">

                                            <label class=" col-md-5">Yearly Increment</label>

                                            <div class="col-lg-7 error-msg">

                                                {{number_format((float)$rent_list->yearly_increment, 2, '.', '')}}%

                                            </div>

                                        </div>

                                </div>

                                <div class="col-md-4 ">

                                        <div class="form-group row">

                                            <label class=" col-md-5">Office Square feet area</label>

                                            <div class="col-lg-7 error-msg">

                                                {{$rent_list->office_area}}

                                            </div>

                                        </div>

                                </div>

                                <div class="col-md-4 ">

                                        <div class="form-group row">

                                            <label class=" col-md-5">Advance Payment Amount</label>

                                            <div class="col-lg-7 error-msg"> 

                                                <input type="text" name="advance_payment" id="advance_payment" class="form-control advance_payment "   value="{{  number_format((float)$rent_list->rentLib->advance_payment, 2, '.', '') }}" readonly>

                                            </div>

                                        </div>

                                </div>

                                <div class="col-md-4 ">

                                        <div class="form-group row">

                                            <label class=" col-md-5">Security amount</label>

                                            <div class="col-lg-7 error-msg">

                                               

                                                <input type="text" name="security_amount" id="security_amount" class="form-control security_amount "   value="{{  number_format((float)$rent_list->security_amount, 2, '.', '') }}" readonly>



                                            </div>

                                        </div>

                                </div>

                                <div class="col-md-4 ">

                                        <div class="form-group row">

                                            <label class=" col-md-5">Actual Transfer Amount</label>

                                            <div class="col-lg-7 error-msg"> 

                                                <input type="text" name="actual_transfer" id="actual_transfer" class="form-control actual_transfer "   readonly="" value="{{  number_format((float)$rent_list->actual_transfer_amount, 2, '.', '') }}">

                                            </div>

                                        </div>

                                </div>

                                <div class="col-md-4 ">

<div class="form-group row">

    <label class=" col-md-5">Tds Amount</label>

    <div class="col-lg-7 error-msg"> 

        <input type="text" name="tds_amount" id="tds_amount" class="form-control tds_amount "    value="{{  number_format((float)$rent_list->tds_amount, 2, '.', '') }}">

    </div>

</div>

</div>
                                <div class="col-md-4 ">

                                        <div class="form-group row">

                                            <label class=" col-md-5">Advance Payment Settle Amount</label>

                                            <div class="col-lg-7 error-msg">

                                                <input type="text" name="advance_settel" id="advance_settel" class="form-control advance_settel "   value="0.00" >

                                            </div>

                                        </div>

                                </div>

                                <div class="col-md-4 ">

                                        <div class="form-group row">

                                            <label class=" col-md-5">Security Settle Amount</label>

                                            <div class="col-lg-7 error-msg">

                                                <input type="text" name="security_settel" id="security_settel" class="form-control security_settel "   value="0.00" >

                                            </div>

                                        </div>

                                </div>

                                <div class="col-md-4 ">

                                        <div class="form-group row">

                                            <label class=" col-md-5">Transfer Amount</label>

                                            <div class="col-lg-7 error-msg">

                <input type="text" name="transfer_amount" id="transfer_amount" class="form-control transfer_amount "   value="{{  number_format((float)$rent_list->actual_transfer_amount, 2, '.', '') }}" readonly>

                                            </div>

                                        </div>

                                </div>

                                <div class="col-md-4 ">

                                        <div class="form-group row">

                                            <label class=" col-md-5">Employee Code</label>

                                            <div class="col-lg-7 error-msg">{{$rent_list->rentEmp->employee_code}}

                                            </div>

                                        </div>

                                </div>

                                <div class="col-md-4 ">

                                        <div class="form-group row">

                                            <label class=" col-md-5">Employee Name</label>

                                            <div class="col-lg-7 error-msg">

                                                

                                                {{$rent_list->rentEmp->employee_name}} 

                                            </div>

                                        </div>

                                </div>

                                <div class="col-md-4 ">

                                        <div class="form-group row">

                                            <label class=" col-md-5">Employee Designation</label>

                                            <div class="col-lg-7 error-msg">

                                                 {{ getDesignationData('designation_name',$rent_list->rentEmp->designation_id)->designation_name }}

                                            </div>

                                        </div>

                                </div>

                                <div class="col-md-4 ">

                                        <div class="form-group row">

                                            <label class=" col-md-5">Employee Mobile No.</label>

                                            <div class="col-lg-7 error-msg">

                                                 {{$rent_list->rentEmp->mobile_no}}

                                            </div>

                                        </div>

                                </div>

                                









                            </div>                                

                        </div>

                        <div class="col-md-12" style="padding-top: 30px">

                              <div class="row">

                           <div class="col-md-12 cheque" >

                                    <h6 class="card-title font-weight-semibold"> Payment Detail</h6>

                            </div>
                                  <div class="col-md-6">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-2">Amount Mode </label>
                                            <div class="col-lg-7 error-msg">
                                                <select class="form-control" id="amount_mode" name="amount_mode">
                                                    <option value="">Select Amount Mode</option> 
                                                    <option value="1" @if($amount_mode==1) selected @endif>SSB</option>
                                                    <option value="2" @if($amount_mode==2) selected @endif>Bank</option> 
                                                </select>
                                            </div>
                                        </div>
                                  </div>
                                  <div class="col-md-6">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-2">Payment Date</label>
                                            <div class="col-lg-7 error-msg">
                                                <div class="input-group">
                                                <input type="text" name="select_date" id="select_date" class="form-control  " readonly>

                                                <input type="hidden" name="ledger_date" id="ledger_date" class="form-control  " readonly value="{{$ledger_date}}"> 
                                                <input type="hidden" name="create_application_date" id="create_application_date" class="form-control create_application_date " readonly  > 

                                               </div>
                                            </div>
                                        </div>
                                  </div>
                                </div>

                                <div class="row bank"  style="display: none;">

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

                                  <div class="col-md-12 bank" style="display: none; ">

                                    <h6 class="card-title bank" style="display: none; "> Bank Detail</h6>

                                  </div>

                                  <div class="col-md-6 bank "  style="display: none;">

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



                                  <div class="col-lg-6 bank"  style="display: none;"> 

                                    <div class="form-group row">

                                      <label class="col-form-label col-lg-4">Account Number<sup class="required">*</sup></label>

                                      <div class="col-lg-8 error-msg">

                                        <select   name="account_id" id="account_id" class="form-control" >  <option value="">Select Account Number</option> 

                                        </select>

                                      </div>

                                    </div>

                                  </div>

                                  <div class="col-lg-6 bank"  style="display: none;">  

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

                                        <select   name="cheque_id" id="cheque_id" class="form-control" >  <option value="">Select Cheque</option> 

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

                                                <input type="text" class="form-control" name="utr_tran" id="utr_tran" >

                                            </div>

                                        </div>

                                  </div>

                                  <div class="col-md-6 online" style="display: none;">

                                        <div class="form-group row">

                                            <label class="col-form-label col-lg-4"> Amount  </label>

                                            <div class="col-lg-8 error-msg">

                                                <input type="text" class="form-control" name="online_tran_amount" id="online_tran_amount" value="{{number_format((float)$rent_list->actual_transfer_amount, 2, '.', '')}}" readonly>

                                            </div>

                                        </div>

                                  </div>

                                  <div class="col-md-6 online" style="display: none;">

                                        <div class="form-group row">

                                            <label class="col-form-label col-lg-4">RTGS/NEFT Charge   </label>

                                            <div class="col-lg-8 error-msg">

                                                <input type="text" class="form-control" name="neft_charge" id="neft_charge" >

                                            </div>

                                        </div>

                                  </div>

                                  <div class="col-md-6 online" style="display: none;">

                                        <div class="form-group row">

                                            <label class="col-form-label col-lg-4">Total Amount</label>

                                            <div class="col-lg-8 error-msg">

                                                <input type="text" class="form-control" name="online_total_amount" id="online_total_amount" value="{{number_format((float)$rent_list->actual_transfer_amount, 2, '.', '')}}" readonly> 

                                            </div>

                                        </div>

                                  </div>



                              </div>

                              <div class="row">

                                <div class="col-md-12" >

                                        <div class="form-group row">

                                            <label class="col-form-label col-lg-2"><strong>Total  Transfer Amount</strong></label>

                                            <div class="col-lg-8 error-msg">

                                                <input type="text" class="form-control" name="total_transfer_amount" id="total_transfer_amount" value="{{number_format((float)$rent_list->actual_transfer_amount, 2, '.', '')}}" readonly> 

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

                                @if($rent_list)

                                <button type="submit" class=" btn bg-dark legitRipple"  id="submit_transfer">Transfer</button>

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

@include('templates.admin.rent-management.partials.advance_script') 

@stop

