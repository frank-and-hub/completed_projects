@extends('templates.admin.master')
@section('content')
<?php 
$amount_mode =0;
$total_transfer =number_format((float)$salary_list->actual_transfer_amount, 2, '.', '');
$due=$salary_list->actual_transfer_amount-$salary_list->transferred_salary;
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
                        <h6 class="card-title font-weight-semibold">Salary  Details</h6>
                    </div>
                    <form action="{!! route('admin.salary.part_payment_save') !!}" method="post" enctype="multipart/form-data"  name="transfer_save" id="transfer_save" class="transfer_save" >
                        @csrf 
                        <input type="hidden" name="created_at" class="created_at" id="created_at">
                        <input type="hidden" name="company_id"  id  = "company_id" class="company_id" value=" {{ $salary_list->company->id }}">
                        <input type="hidden" name="leaser_id" class="leaser_id" value="{{$leaser_id}}">
                        <input type="hidden" name="salaryID" class="salaryID" value=" {{ $salary_list->id }}">
                        <input type="hidden" name="create_application_date" class="create_application_date" id="create_application_date">

                    <div class="card-body">
                    <div class="row">
                           
                        <div class="col-md-12">
                            
                            <div class="row">
                                <div class="col-md-4 ">
                                        <div class="form-group row">
                                            <label class=" col-md-5">Company Name</label>
                                            <div class="col-lg-7 error-msg">
                                                {{ $salary_list->company->name }}
                                            </div>
                                        </div>
                                </div>
                                <div class="col-md-4 ">
                                        <div class="form-group row">
                                            <label class=" col-md-5">BR Name</label>
                                            <div class="col-lg-7 error-msg">
                                                {{ $salary_list->salary_branch->name }}
                                            </div>
                                        </div>
                                </div>
                                <div class="col-md-4 ">
                                        <div class="form-group row">
                                            <label class=" col-md-5">BR Code</label>
                                            <div class="col-lg-7 error-msg">
                                                {{ $salary_list->salary_branch->branch_code }}
                                            </div>
                                        </div>
                                </div>
                                <div class="col-md-4 ">
                                        <div class="form-group row">
                                            <label class=" col-md-5">SO Name</label>
                                            <div class="col-lg-7 error-msg">
                                                {{ $salary_list->salary_branch->sector }}
                                            </div>
                                        </div>
                                </div>
                                <div class="col-md-4 ">
                                        <div class="form-group row">
                                            <label class=" col-md-5">RO Name</label>
                                            <div class="col-lg-7 error-msg">
                                                {{ $salary_list['salary_branch']->regan }}
                                            </div>
                                        </div>
                                </div>
                                <div class="col-md-4 ">
                                        <div class="form-group row">
                                            <label class=" col-md-5">ZO Name</label>
                                            <div class="col-lg-7 error-msg">
                                                {{ $salary_list->salary_branch->zone }}
                                            </div>
                                        </div>
                                </div>
                                <div class="col-md-4 ">
                                        <div class="form-group row">
                                            <label class=" col-md-5">Employee Code</label>
                                            <div class="col-lg-7 error-msg">{{$salary_list->salary_employee->employee_code}}
                                            </div>
                                        </div>
                                </div>
                                <div class="col-md-4 ">
                                        <div class="form-group row">
                                            <label class=" col-md-5">Employee Name</label>
                                            <div class="col-lg-7 error-msg">
                                                
                                                {{$salary_list->salary_employee->employee_name}} 
                                            </div>
                                        </div>
                                </div>
                                <div class="col-md-4 ">
                                        <div class="form-group row">
                                            <label class=" col-md-5">Employee Designation</label>
                                            <div class="col-lg-7 error-msg">
                                                 {{ getDesignationData('designation_name',$salary_list->salary_employee->designation_id)->designation_name }}
                                            </div>
                                        </div>
                                </div>
                                <div class="col-md-4 ">
                                        <div class="form-group row">
                                            <label class=" col-md-5">Employee Mobile No.</label>
                                            <div class="col-lg-7 error-msg">
                                                 {{$salary_list->salary_employee->mobile_no}}
                                            </div>
                                        </div>
                                </div>
                                <div class="col-md-4 ">
                                        <div class="form-group row">
                                            <label class=" col-md-5"> SSB account</label>
                                            <div class="col-lg-7 error-msg">
                                                {{$salary_list->salary_employee->ssb_account}}
                                            </div>
                                        </div>
                                </div>
                                <div class="col-md-4 ">
                                        <div class="form-group row">
                                            <label class=" col-md-5"> Bank Name</label>
                                            <div class="col-lg-7 error-msg">
                                                {{$salary_list->salary_employee->bank_name}} 
                                            </div>
                                        </div>
                                </div>
                                <div class="col-md-4 ">
                                        <div class="form-group row">
                                            <label class=" col-md-5"> Bank A/c No.</label>
                                            <div class="col-lg-7 error-msg">
                                                {{$salary_list->salary_employee->bank_account_no}}
                                            </div>
                                        </div>
                                </div>
                                <div class="col-md-4 ">
                                        <div class="form-group row">
                                            <label class=" col-md-5">IFSC code </label>
                                            <div class="col-lg-7 error-msg">
                                               {{$salary_list->salary_employee->bank_ifsc_code}}
                                            </div>
                                        </div>
                                </div> 
                                
                                <div class="col-md-4 ">
                                        <div class="form-group row">
                                            <label class=" col-md-5">Gross Salary</label>
                                            <div class="col-lg-7 error-msg"> 
                                                <input type="text" name="gross_salary" id="actgross_salaryual_transfer" class="form-control gross_salary "   readonly="" value="{{  number_format((float)$salary_list->fix_salary, 2, '.', '') }}">
                                            </div>
                                        </div>
                                </div>
                                <div class="col-md-4 ">
                                        <div class="form-group row">
                                            <label class=" col-md-5">Leave</label>
                                            <div class="col-lg-7 error-msg"> 
                                                <input type="text" name="leave" id="leave" class="form-control leave "   readonly="" value="{{  number_format((float)$salary_list->leave, 2, '.', '') }}">
                                            </div>
                                        </div>
                                </div>
                                <div class="col-md-4 ">
                                        <div class="form-group row">
                                            <label class=" col-md-5">Tatal Salary</label>
                                            <div class="col-lg-7 error-msg"> 
                                                <input type="text" name="total_salary" id="total_salary" class="form-control total_salary "   readonly="" value="{{  number_format((float)$salary_list->total_salary, 2, '.', '') }}">
                                            </div>
                                        </div>
                                </div>

                                <div class="col-md-4 ">
                                        <div class="form-group row">
                                            <label class=" col-md-5">Deduction</label>
                                            <div class="col-lg-7 error-msg"> 
                                                <input type="text" name="deduction" id="deduction" class="form-control deduction "   readonly="" value="{{  number_format((float)$salary_list->deduction, 2, '.', '') }}">
                                            </div>
                                        </div>
                                </div>
                                <div class="col-md-4 ">
                                        <div class="form-group row">
                                            <label class=" col-md-5">Incentive / Bonus</label>
                                            <div class="col-lg-7 error-msg"> 
                                                <input type="text" name="incentive_bonus" id="incentive_bonus" class="form-control incentive_bonus "   readonly="" value="{{  number_format((float)$salary_list->incentive_bonus, 2, '.', '') }}">
                                            </div>
                                        </div>
                                </div>
                                <div class="col-md-4 ">
                                        <div class="form-group row">
                                            <label class=" col-md-5">Payable Amount</label>
                                            <div class="col-lg-7 error-msg"> 
                                                <input type="text" name="paybale_amount" id="paybale_amount" class="form-control paybale_amount "   readonly="" value="{{  number_format((float)$salary_list->paybale_amount, 2, '.', '') }}">
                                            </div>
                                        </div>
                                </div>
                                <div class="col-md-4 ">
                                        <div class="form-group row">
                                            <label class=" col-md-5">ESI Amount</label>
                                            <div class="col-lg-7 error-msg"> 
                                                <input type="text" name="esi_amount" id="esi_amount" class="form-control esi_amount "   readonly="" value="{{  number_format((float)$salary_list->esi_amount, 2, '.', '') }}">
                                            </div>
                                        </div>
                                </div>
                                <div class="col-md-4 ">
                                        <div class="form-group row">
                                            <label class=" col-md-5">PF Amount</label>
                                            <div class="col-lg-7 error-msg"> 
                                                <input type="text" name="pf_amount" id="total_salary" class="form-control pf_amount "   readonly="" value="{{  number_format((float)$salary_list->pf_amount, 2, '.', '') }}">
                                            </div>
                                        </div>
                                </div>
                                <div class="col-md-4 ">
                                        <div class="form-group row">
                                            <label class=" col-md-5">TDS Amount</label>
                                            <div class="col-lg-7 error-msg"> 
                                                <input type="text" name="tds_amount" id="tds_amount" class="form-control total_salary "   readonly="" value="{{  number_format((float)$salary_list->tds_amount, 2, '.', '') }}">
                                            </div>
                                        </div>
                                </div>
                                {{--<div class="col-md-4 ">
                                        <div class="form-group row">
                                            <label class=" col-md-5">Advance Payment Amount</label>
                                            <div class="col-lg-7 error-msg"> 
                                                <input type="text" name="advance_payment" id="advance_payment" class="form-control advance_payment "   value="{{  number_format((float)$salary_list->salary_employee->advance_payment, 2, '.', '') }}" readonly>
                                            </div>
                                        </div>
                                </div>--}}
                                
                                <div class="col-md-4 ">
                                        <div class="form-group row">
                                            <label class=" col-md-5">Tatal Paybale Amount</label>
                                            <div class="col-lg-7 error-msg"> 
                                                <input type="text" name="actual_transfer" id="actual_transfer" class="form-control actual_transfer "   readonly="" value="{{  number_format((float)$salary_list->actual_transfer_amount, 2, '.', '') }}">
                                            </div>
                                        </div>
                                </div>
                                <div class="col-md-4 ">
                                    <div class="form-group row">
                                        <label class=" col-md-5">Due Amount</label>
                                        <div class="col-lg-7 error-msg">
                                        <input type="text" name="due_amount" id="due_amount" class="form-control due_amount "   value="{{  number_format((float)$due, 2, '.', '') }}" readonly>
                                        </div>
                                    </div>
                                </div>
                               

                                 
                               
                                
                                <div class="col-md-4 ">
                                    <div class="form-group row">
                                        <label class=" col-md-5">Transfer Amount</label>
                                        <div class="col-lg-7 error-msg">
                                        <input type="text" name="transfer_amount" id="transfer_amount" class="form-control transfer_amount "   value="{{  number_format((float)$due, 2, '.', '') }}"  >
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
                                                    @if ($salary_list->salary_employee->ssb_account > 0)
                                                    <option value="1" @if($amount_mode==1) selected @endif>SSB</option>
                                                    @endif
                                                    <option value="2" @if($amount_mode==2) selected @endif>Bank</option> 
                                                    <option value="0" @if($amount_mode==0) selected @endif>Cash</option> 
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
                                <div class="row branch"  style="display: none;">
                                  <div class="col-md-6">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-4">Branch</label>
                                            <div class="col-lg-7 error-msg">
                                                <select class="form-control" id="payment_branch" name="payment_branch">
                                                  <option value="">Select Branch</option>  
                                                  
                                                      <option value="{{ $salary_list->salary_branch->id }}" data-value={{$salary_list->salary_branch->branch_code}}>{{ $salary_list->salary_branch->name }}</option>
                                                   
                                                </select>
                                            </div>
                                        </div>
                                  </div>
                                  <div class="col-md-6">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-4">Branch Current Balance</label>
                                            <div class="col-lg-7 error-msg">
                                                 <input class="form-control" id="branch_total_balance" name="branch_total_balance" readonly> 
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
                                                <input type="text" class="form-control" name="online_tran_amount" id="online_tran_amount" value="{{number_format((float)$due, 2, '.', '')}}" readonly>
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
                                                <input type="text" class="form-control" name="online_total_amount" id="online_total_amount" value="{{number_format((float)$due, 2, '.', '')}}" readonly> 
                                            </div>
                                        </div>
                                  </div>
                              </div>
                              <div class="row">
                                <div class="col-md-12" >
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-2"><strong>Total  Transfer Amount</strong></label>
                                            <div class="col-lg-8 error-msg">
                                                <input type="text" class="form-control" name="total_transfer_amount" id="total_transfer_amount" value="{{number_format((float)$due, 2, '.', '')}}" readonly> 
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
                                @if($salary_list)
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
@include('templates.admin.hr_management.salary.part_payment_script') 
@stop