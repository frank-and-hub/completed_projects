@extends('templates.admin.master')

@section('content')

<style>

    .search-table-outter { overflow-x: scroll; }

    .frm{ min-width: 200px; }
    h5{
     background-color: gray;
     margin: 0 -10px 0;
     padding: 4px 0 4px 10px ;
    }

</style>

<div class="loader" style="display: none;"></div>

<div class="content">

    <div class="row">

        <div class="col-md-12">

            <!-- Basic layout-->

            <div class="card">

                <div class="">

                    <div class="card-body" >
                        
                        @if($bankingLedger->banking_type == 1)
                            <form method="post" action="{!! route('admin.banking.update') !!}" id="edit-banking-form"  enctype="multipart/form-data" data-type="1">
                                @csrf
                                <input type="hidden" name="type" value="1">
                                <input type="hidden" name="id" value="{{ $bankingLedger->id }}">
                                <input type="hidden" name="subtype" value="1">
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-2">Expense Account<sup>*</sup></label>
                                            <div class="col-lg-10 error-msg">
                                               <select class="form-control expence_head_id" name="expense_account" id="expense_account" data-row-id="1">
                                                    <option value="">Choose expence account...</option>
                                                    @foreach( $heads as $expence_head)
                                                    <option @if($bankingLedger->expense_account == $expence_head->head_id) selected @endif value="{{ $expence_head->head_id }}"  >{{ $expence_head->sub_head }}</option> 
                                                    @endforeach
                                               </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-2">Sub Head1<sup>*</sup></label>
                                            <div class="col-lg-10 error-msg">
                                               <select class="form-control expence_head_id" name="expense_account1" id="expense_account1" data-row-id="2">
                                                <option value=''>Choose Sub Head</option>
                                                @foreach( $subCategory2 as $expence_head)
                                                    @if($bankingLedger->expense_account1 == $expence_head->head_id)
                                                        <option selected value="{{ $expence_head->head_id }}"  >{{ $expence_head->sub_head }}</option>
                                                    @endif 
                                                @endforeach
                                               </select>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-lg-6">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-2">Sub Head2<sup>*</sup></label>
                                            <div class="col-lg-10 error-msg">
                                               <select class="form-control expence_head_id" name="expense_account2" id="expense_account2" data-row-id="3">
                                                <option value=''>Choose Sub Head</option>
                                                @foreach( $subCategory3 as $expence_head)
                                                    @if($bankingLedger->expense_account2 == $expence_head->head_id)
                                                        <option selected value="{{ $expence_head->head_id }}"  >{{ $expence_head->sub_head }}</option> 
                                                    @endif
                                                @endforeach
                                               </select>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-lg-6">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-2">Sub Head3<sup>*</sup></label>
                                            <div class="col-lg-10 error-msg">
                                               <select class="form-control expence_head_id" name="expense_account3" id="expense_account3" data-row-id="4">
                                                <option value=''>Choose Sub Head</option>
                                                @foreach( $subCategory4 as $expence_head)
                                                    @if($bankingLedger->expense_account3 == $expence_head->head_id)
                                                        <option selected value="{{ $expence_head->head_id }}"  >{{ $expence_head->sub_head }}</option> 
                                                    @endif
                                                @endforeach
                                               </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-6" @if($bankingLedger->payment_mode != 2) style="display:none" @endif>
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-2">Branch Name<sup>*</sup></label>
                                            <div class="col-lg-10 error-msg">
                                               <select class="form-control" name="expense_branch_id" id="expense_branch_id">
                                                    <option value="">Choose branch...</option>
                                                    @foreach( $branches as $branch)
                                                    <option @if($bankingLedger->branch_id == $branch->id) selected @endif value="{{ $branch->id }}"  >{{ $branch->name }} ({{$branch->branch_code}})</option> 
                                                    @endforeach
                                               </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-2">Date <sup>*</sup></label>
                                            <div class="col-lg-10 error-msg">
                                                <input class="form-control" type="text" name="expense_date" id="expense_date" value="{{ date("d/m/Y ", strtotime(convertDate($bankingLedger->date))) }}">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-2">Amount<sup>*</sup></label>
                                            <div class="col-lg-10 error-msg">
                                               <input class="form-control" type="text" name="expense_amount" id="expense_amount" value="{{ round($bankingLedger->amount) }}">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-2">Upload Receipt<sup>*</sup></label>
                                            <div class="col-lg-10 error-msg">
                                               <input class="form-control" type="file" name="expense_receipt" id="expense_receipt">
                                                @if($bankingLedger->file_id)
                                                <span>{{ getFileData($bankingLedger->file_id)[0]['file_name'] }}</span>
                                                @endif
                                               <input type="hidden" name="expense_file_id" value="{{ $bankingLedger->file_id }}">

                                            </div>
                                        </div>
                                    </div>  

                                    <div class="col-lg-6">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-2">Description<sup>*</sup></label>
                                            <div class="col-lg-10 error-msg">
                                               <input class="form-control" type="text" name="expense_description" id="expense_description" value="{{ $bankingLedger->description }}">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-2">Select Mode <sup>*</sup></label>
                                            <div class="col-lg-10 error-msg">
                                               <select class="form-control" name="expense_mode" id="expense_mode">
                                                    <option value="">Choose Mode...</option>
                                                    <option @if($bankingLedger->payment_mode == 1) selected @endif  value="1">Bank</option>
                                                    <option @if($bankingLedger->payment_mode == 2) selected @endif value="2">Cash</option>
                                               </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-6 bankDiv" @if($bankingLedger->payment_mode != 1) style="display:none" @endif>
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-2">Bank Name<sup>*</sup></label>
                                            <div class="col-lg-10 error-msg">
                                               <select class="form-control banks_id" name="expense_bank_id" id="expense_bank_id">
                                                    <option value="">Choose bank...</option>
                                                    @foreach( $banks as $bank)
                                                    <option @if($bankingLedger->bank_id == $bank->id) selected @endif value="{{ $bank->id }}"  >{{ $bank->bank_name }}</option> 
                                                    @endforeach
                                               </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-6 bankDiv" @if($bankingLedger->payment_mode != 1) style="display:none" @endif>
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-2">Account Number<sup>*</sup></label>
                                            <div class="col-lg-10 error-msg">
                                               <select class="form-control paid_via_account_number" name="expense_account_no" id="expense_account_no">
                                                <option value="">Please Select</option>
                                                @if($accounts)
                                                <option selected="" value="{{ $accounts->id }}">{{ $accounts->account_no }}</option>
                                               </select>
                                               @endif
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-6 bankDiv" @if($bankingLedger->payment_mode != 1) style="display:none" @endif>
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-2">Paid Via<sup>*</sup></label>
                                            <div class="col-lg-10 error-msg">
                                               <select class="form-control paid_via" name="expense_paid_via" id="expense_paid_via">
                                                    <option value="">Choose payment type...</option>
                                                    <option @if($bankingLedger->paid_via == 1) selected @endif value="1">Cheque</option>
                                                    <option @if($bankingLedger->paid_via == 2) selected @endif value="2">bank transfer</option>
                                               </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-6 bankneftutrDiv" @if($bankingLedger->payment_mode != 1 && $bankingLedger->paid_via != 2) style="display:none" @endif>
                                        <div class="form-group row bankutrDiv">
                                            <label class="col-form-label col-lg-2">NEFT/UTR No.<sup>*</sup></label>
                                            <div class="col-lg-10 error-msg">
                                               <input class="form-control" type="text" name="expense_utr" id="expense_utr" value="{{ $bankingLedger->neft_utr_no }}" required>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-6 bankutrDiv" @if($bankingLedger->payment_mode != 1 && $bankingLedger->paid_via != 2) style="display:none" @endif>
                                        <div class="form-group row bankutrDiv">
                                            <label class="col-form-label col-lg-2">NEFT Charge<sup>*</sup></label>
                                            <div class="col-lg-10 error-msg">
                                               <input class="form-control" type="text" name="expense_neft" id="expnese_neft" value="{{ round($bankingLedger->neft_charge) }}" required>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-6 bankDiv" @if($bankingLedger->payment_mode != 1 || $bankingLedger->paid_via != 1) style="display:none" @endif>
                                        <div class="form-group row chequeDiv">
                                            <label class="col-form-label col-lg-2">Cheque<sup>*</sup></label>
                                            <div class="col-lg-10 error-msg">
                                               <select class="form-control" name="expense_cheque_no" id="expense_cheque_no">
                                                <option value="">Please Select</option>
                                                @foreach($cheques as $cheque)
                                                <option @if($bankingLedger->cheque_no == $cheque->id) selected @endif value="{{ $cheque->id }}">{{ $cheque->cheque_no }}</option>
                                                @endforeach
                                               </select>
                                            </div>
                                        </div>
                                    </div>

                                </div>

                                <div class="text-right mt-10">
                                    <input type="submit" name="submitform" value="Submit" class="btn btn-primary submit">
                                </div>
                            </form>
                        @endif
                        
                        @if($bankingLedger->banking_type == 2)
                            <form method="post" action="{!! route('admin.banking.update') !!}" id="edit-banking-form" data-type="2">
                                @csrf
                                <input type="hidden" name="type" value="2">
                                <input type="hidden" name="id" value="{{ $bankingLedger->id }}">
                                <input type="hidden" name="subtype" value="2">
                                <div class="row">
                                    <div class="col-lg-12">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-1">Account<sup>*</sup></label>
                                            <div class="col-lg-11 error-msg">
                                               <select class="form-control" name="payment_account_payment_edit" id="payment_account_payment_edit" required="" disabled>
                                                    <option value="">Choose account type...</option>
                                                    <option @if($bankingLedger->account_type == 1) selected @endif value="1">Vendor</option>
                                                    <option @if($bankingLedger->account_type == 2) selected @endif value="2">Customer</option>
                                               </select>

                                                <input type="hidden" name="payment_account_payment" id="payment_account_payment" value="{{ $bankingLedger->account_type }}">

                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row payment_vendor_div" id="payment_vendor_div" @if($bankingLedger->account_type != 1) style="display:none" @endif>
                                    <div class="col-lg-6">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-2">Vendor Type<sup>*</sup></label>
                                            <div class="col-lg-10 error-msg">
                                               <select class="form-control" name="payemnt_vendor_type_edit" id="payemnt_vendor_type_edit" disabled>
                                                    <option value="">Choose vendor type...</option>
                                                    <option @if($bankingLedger->vendor_type == 0) selected @endif value="0">Rent</option>
                                                    <option @if($bankingLedger->vendor_type == 1) selected @endif value="1">Salary</option>
                                                    <option @if($bankingLedger->vendor_type == 2) selected @endif value="2">Associates</option>
                                                    <option @if($bankingLedger->vendor_type == 3) selected @endif value="3">Vendors</option>
                                               </select>

                                               <input type="hidden" name="payemnt_vendor_type" id="payemnt_vendor_type" value="{{ $bankingLedger->vendor_type }}">
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-lg-6">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-2">Branch Name<sup>*</sup></label>
                                            <div class="col-lg-10 error-msg">
                                               <select class="form-control" name="payment_branch_id" id="payment_branch_id">
                                                    <option value="">Choose branch...</option>
                                                    @foreach( $branches as $branch)
                                                    <option @if($bankingLedger->branch_id == $branch->id) selected @endif value="{{ $branch->id }}"  >{{ $branch->name }} ({{$branch->branch_code}})</option> 
                                                    @endforeach
                                               </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-2 vendor-associate-name">Vendor Name <sup>*</sup></label>
                                            <div class="col-lg-10 error-msg">
                                                <?php
                                                if($bankingLedger->vendor_type == 0){
                                                    $result = \App\Models\RentLiability::where('id',$bankingLedger->vendor_type_id)->first();
                                                    $redId = $result->id;
                                                    $resName = $result->owner_name;
                                                }elseif($bankingLedger->vendor_type == 1){
                                                    $result =  \App\Models\Employee::where('id',$bankingLedger->vendor_type_id)->first();
                                                    $redId = $result->id;
                                                    $resName = $result->employee_name;
                                                }elseif($bankingLedger->vendor_type == 2){
                                                    $result =  \App\Models\Member::where('id',$bankingLedger->vendor_type_id)->first();
                                                    $redId = $result->id;
                                                    $resName = $result->associate_no;
                                                }elseif($bankingLedger->vendor_type == 3){
                                                    $result =  \App\Models\Vendor::where('id',$bankingLedger->vendor_type_id)->where('type',0)->first();
                                                    $redId = $result->id;
                                                    $resName = $result->employee_name;
                                                }elseif($bankingLedger->vendor_type == 4 || $bankingLedger->vendor_type == 5){
                                                    $result =  \App\Models\Vendor::where('id',$bankingLedger->vendor_type_id)->where('type',1)->first();
                                                    $redId = $result->id;
                                                    $resName = $result->employee_name;
                                                }
                                                ?>
                                                <select name="payment_vendor_name_edit" id="payment_vendor_name_edit" class="form-control frm select2" data-row="1" data-value="1" disabled>
                                                      <option value="{{ $redId }}">{{ $resName }}</option>
                                                </select>

                                                <input type="hidden" name="payment_vendor_name" id="payment_vendor_name" value="{{ $redId }}">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-2">Amount<sup>*</sup></label>
                                            <div class="col-lg-10 error-msg">
                                                <?php $advancedStatus = getAdvancedEntry($bankingLedger->vendor_type,$bankingLedger->id,$bankingLedger->vendor_type_id); ?>
                                               <input class="form-control" type="text" name="vendor_payment_amount" id="vendor_payment_amount" value="{{ round($bankingLedger->amount) }}" @if($advancedStatus == 0) required="" @else readonly @endif>

                                               <input type="hidden" name="vendor_total_amount" id="vendor_total_amount" value="{{ round($relatedRecordAmount) }}">
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-lg-6">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-2">Date<sup>*</sup></label>
                                            <div class="col-lg-10 error-msg">
                                                <input class="form-control" type="text" name="payment_vendor_date" id="payment_vendor_date"  value="{{ date("d/m/Y ", strtotime(convertDate($bankingLedger->date))) }}" required="">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-2">Description<sup>*</sup></label>
                                            <div class="col-lg-10 error-msg">
                                                <input class="form-control" type="text" name="payment_vendor_description" id="payment_vendor_description" value="{{ $bankingLedger->description }}" required="">
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-lg-6">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-2">Select Mode <sup>*</sup></label>
                                            <div class="col-lg-10 error-msg">
                                               <select class="form-control" name="vendor_payment_mode" id="vendor_payment_mode" required="">
                                                    <option value="">Choose Mode...</option>
                                                    <option @if($bankingLedger->payment_mode == 1) selected @endif  value="1">Bank</option>
                                                    <option @if($bankingLedger->payment_mode == 2) selected @endif value="2">Cash</option>
                                               </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-6 PaymentVendorbankDiv" @if($bankingLedger->payment_mode != 1) style="display:none" @endif>
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-2">Bank Name<sup>*</sup></label>
                                            <div class="col-lg-10 error-msg">
                                               <select class="form-control banks_id" name="payment_vendor_bank_id" id="payment_vendor_bank_id" required="">
                                                    <option value="">Choose bank...</option>
                                                    @foreach( $banks as $bank)
                                                    <option @if($bankingLedger->bank_id == $bank->id) selected @endif value="{{ $bank->id }}"  >{{ $bank->bank_name }}</option> 
                                                    @endforeach
                                               </select>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-lg-6 PaymentVendorbankDiv" @if($bankingLedger->payment_mode != 1) style="display:none" @endif>
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-2">Account Number<sup>*</sup></label>
                                            <div class="col-lg-10 error-msg">
                                               <select class="form-control paid_via_account_number" name="payment_vendor_bank_account_number" id="payment_vendor_bank_account_number" required="">
                                                @if($accounts)
                                                <option selected="" value="{{ $accounts->id }}">{{ $accounts->account_no }}</option>
                                                @endif
                                               </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-6 PaymentVendorbankDiv" @if($bankingLedger->payment_mode != 1) style="display:none" @endif>
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-2">Paid Via<sup>*</sup></label>
                                            <div class="col-lg-10 error-msg">
                                               <select class="form-control paid_via" name="payment_vendor_paid_via" id="payment_vendor_paid_via" required="">
                                                    <option value="">Choose payment type...</option>
                                                    <option @if($bankingLedger->paid_via == 1) selected @endif value="1">Cheque</option>
                                                    <option @if($bankingLedger->paid_via == 2) selected @endif value="2">bank transfer</option>
                                               </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-6 PaymentVendorbankneftutrDiv" @if($bankingLedger->payment_mode != 1 && $bankingLedger->paid_via != 2) style="display:none" @endif>
                                        <div class="form-group row PaymentVendorbankneftutrDiv">
                                            <label class="col-form-label col-lg-2">NEFT/UTR No.<sup>*</sup></label>
                                            <div class="col-lg-10 error-msg">
                                               <input class="form-control" type="text" name="vendor_utr" id="vendor_utr" value="{{ $bankingLedger->neft_utr_no }}" required>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-6 PaymentVendorbankutrDiv" @if($bankingLedger->payment_mode != 1 && $bankingLedger->paid_via != 2) style="display:none" @endif>
                                        <div class="form-group row PaymentVendorbankutrDiv">
                                            <label class="col-form-label col-lg-2">NEFT Charge<sup>*</sup></label>
                                            <div class="col-lg-10 error-msg">
                                               <input class="form-control" type="text" name="vendor_neft" id="vendor_neft" value="{{ round($bankingLedger->neft_charge) }}" required="">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-6 PaymentVendorChequebankDiv" @if($bankingLedger->payment_mode != 1 || $bankingLedger->paid_via != 1) style="display:none" @endif>
                                        <div class="form-group row paymentchequeDiv">
                                            <label class="col-form-label col-lg-2">Cheque<sup>*</sup></label>
                                            <div class="col-lg-10 error-msg">
                                               <select class="form-control" name="payment_vendor_cheque_no" id="payment_vendor_cheque_no" required="">
                                                @foreach($cheques as $cheque)
                                                <option @if($bankingLedger->cheque_no == $cheque->id) selected @endif value="{{ $cheque->id }}">{{ $cheque->cheque_no }}</option>
                                                @endforeach
                                               </select>
                                            </div>
                                        </div>
                                    </div>
                                    
                                </div>
                                
                                <div class="row payment_customer_div" id="payment_customer_div" @if($bankingLedger->account_type != 2) style="display:none" @endif>
                                    <input type="hidden" name="cus_advance_type" id="cus_advance_type" value="4">
                                    <div class="col-lg-6">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-2">Branch Name<sup>*</sup></label>
                                            <div class="col-lg-10 error-msg">
                                               <select class="form-control" name="customer_branch_id" id="customer_branch_id" required>
                                                    <option value="">Choose branch...</option>
                                                    @foreach( $branches as $branch)
                                                    <option @if($bankingLedger->branch_id == $branch->id) selected @endif value="{{ $branch->id }}"  >{{ $branch->name }} ({{$branch->branch_code}})</option> 
                                                    @endforeach
                                               </select>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-lg-6">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-2">Customer Name <sup>*</sup></label>
                                            <div class="col-lg-10 error-msg">
                                                <?php
                                                if($bankingLedger->vendor_type == 0){
                                                    $result = \App\Models\RentLiability::where('id',$bankingLedger->vendor_type_id)->first();
                                                    $redId = $result->id;
                                                    $resName = $result->owner_name;
                                                }elseif($bankingLedger->vendor_type == 1){
                                                    $result =  \App\Models\Employee::where('id',$bankingLedger->vendor_type_id)->first();
                                                    $redId = $result->id;
                                                    $resName = $result->employee_name;
                                                }elseif($bankingLedger->vendor_type == 2){
                                                    $result =  \App\Models\Member::where('id',$bankingLedger->vendor_type_id)->first();
                                                    $redId = $result->id;
                                                    $resName = $result->associate_no;
                                                }elseif($bankingLedger->vendor_type == 3){
                                                    $result =  \App\Models\Vendor::where('id',$bankingLedger->vendor_type_id)->where('type',0)->first();
                                                    $redId = $result->id;
                                                    $resName = $result->employee_name;
                                                }elseif($bankingLedger->vendor_type == 4 || $bankingLedger->vendor_type == 5){
                                                    $result =  \App\Models\Vendor::where('id',$bankingLedger->vendor_type_id)->where('type',1)->first();
                                                    $redId = $result->id;
                                                    $resName = $result->employee_name;
                                                }
                                                ?>
                                                <select name="payment_customer_name_edit" id="payment_customer_name_edit" class="form-control frm select2" data-row="1" data-value="1" required disabled>
                                                      <option value="">Please Selct</option>
                                                      <option selected="" value="{{ $redId }}">{{ $resName }}</option>
                                                </select>

                                                <input type="hidden" name="payment_customer_name" id="payment_customer_name" value="{{ $redId }}">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-2">Date<sup>*</sup></label>
                                            <div class="col-lg-10 error-msg">
                                                <input class="form-control" type="text" name="payment_customer_date" id="payment_customer_date" value="{{ date("d/m/Y ", strtotime(convertDate($bankingLedger->date))) }}" required>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-2">Amount<sup>*</sup></label>
                                            <div class="col-lg-10 error-msg">
                                               <input class="form-control" type="text" name="payment_customer_amount" id="payment_customer_amount" value="{{ round($bankingLedger->amount) }}" required="">

                                               <input type="hidden" name="customer_total_amount" id="customer_total_amount">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-6 ">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-2">Description<sup>*</sup></label>
                                            <div class="col-lg-10 error-msg">
                                                <input class="form-control" type="text" name="payment_customer_description" id="payment_customer_description" value="{{ $bankingLedger->description }}" required>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-lg-6">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-2">Select Mode <sup>*</sup></label>
                                            <div class="col-lg-10 error-msg">
                                               <select class="form-control" name="customer_payment_mode" id="customer_payment_mode" required>
                                                    <option value="">Choose Mode...</option>
                                                    <option @if($bankingLedger->payment_mode == 1) selected @endif  value="1">Bank</option>
                                                    <option @if($bankingLedger->payment_mode == 2) selected @endif value="2">Cash</option>
                                               </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-6 PaymentCustomerbankDiv" @if($bankingLedger->payment_mode != 1) style="display:none" @endif>
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-2">Bank Name<sup>*</sup></label>
                                            <div class="col-lg-10 error-msg">
                                               <select class="form-control banks_id" name="payment_customer_bank_id" id="payment_customer_bank_id" required>
                                                    <option value="">Choose bank...</option>
                                                    @foreach( $banks as $bank)
                                                    <option @if($bankingLedger->bank_id == $bank->id) selected @endif value="{{ $bank->id }}"  >{{ $bank->bank_name }}</option> 
                                                    @endforeach
                                               </select>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-lg-6 PaymentCustomerbankDiv" @if($bankingLedger->payment_mode != 1) style="display:none" @endif>
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-2">Account Number<sup>*</sup></label>
                                            <div class="col-lg-10 error-msg">
                                               <select class="form-control paid_via_account_number" name="payment_customer_bank_account_number" id="payment_customer_bank_account_number" required>
                                                @if($accounts)
                                                <option selected="" value="{{ $accounts->id }}">{{ $accounts->account_no }}</option>
                                               </select>
                                               @endif
                                               </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-6 PaymentCustomerbankDiv" @if($bankingLedger->payment_mode != 1) style="display:none" @endif>
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-2">Paid Via<sup>*</sup></label>
                                            <div class="col-lg-10 error-msg">
                                               <select class="form-control paid_via" name="payment_customer_paid_via" id="payment_customer_paid_via" required>
                                                    <option value="">Choose payment type...</option>
                                                    <option @if($bankingLedger->paid_via == 1) selected @endif value="1">Cheque</option>
                                                    <option @if($bankingLedger->paid_via == 2) selected @endif value="2">bank transfer</option>
                                               </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-6 PaymentCustomerbankneftutrDiv" @if($bankingLedger->payment_mode != 1 && $bankingLedger->paid_via != 2) style="display:none" @endif>
                                        <div class="form-group row PaymentCustomerbankneftutrDiv">
                                            <label class="col-form-label col-lg-2">NEFT/UTR No.<sup>*</sup></label>
                                            <div class="col-lg-10 error-msg">
                                               <input class="form-control" type="text" name="customer_utr" id="customer_utr" value="{{ $bankingLedger->neft_utr_no }}" required>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-6 PaymentCustomerbankutrDiv" @if($bankingLedger->payment_mode != 1 && $bankingLedger->paid_via != 2) style="display:none" @endif>
                                        <div class="form-group row PaymentCustomerbankutrDiv">
                                            <label class="col-form-label col-lg-2">NEFT Charge<sup>*</sup></label>
                                            <div class="col-lg-10 error-msg">
                                               <input class="form-control" type="text" name="customer_neft" id="customer_neft" value="{{ round($bankingLedger->neft_charge) }}" required>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-6 PaymentCustomerChequebankDiv" @if($bankingLedger->payment_mode != 1 || $bankingLedger->paid_via != 1) style="display:none" @endif>
                                        <div class="form-group row paymentchequeDiv">
                                            <label class="col-form-label col-lg-2">Cheque<sup>*</sup></label>
                                            <div class="col-lg-10 error-msg">
                                               <select class="form-control" name="payment_customer_cheque_no" id="payment_customer_cheque_no" required>
                                                @foreach($cheques as $cheque)
                                                <option @if($bankingLedger->cheque_no == $cheque->id) selected @endif value="{{ $cheque->id }}">{{ $cheque->cheque_no }}</option>
                                                @endforeach
                                               </select>
                                            </div>
                                        </div>
                                    </div>                                 
                                </div>

                                <div class="rent_transaction_table transaction_table" @if($bankingLedger->vendor_type != 0 || count($bankingLedger['relatedRecord']) == 0) style="display:none" @endif>

                                    <div class="card-header header-elements-inline">
                                        <h6 class="card-title font-weight-semibold">Transaction List</h6>
                                    </div>
                                    <input type="hidden" name="rent_pending_bills" id="rent_pending_bills" value="0">
                                    <table id="rent_transaction_list" class="table datatable-show-all" >

                                        <thead>

                                            <tr>

                                                <th>Transaction ID.</th>

                                                <th>Month</th>

                                                <th>Year</th>    

                                                <th>Due Amount</th>

                                                <th>Payment(INR)</th>

                                            </tr>

                                        </thead> 

                                        <tbody class="transaction_table_list rent_transaction_table_list">
                                            @if($bankingLedger->vendor_type == 0)
                                                @foreach($bankingLedger['relatedRecord'] as $val => $relatedRecord)
                                                    <?php
                                                    $rentPaymentResult = \App\Models\RentPayment::where('id', $relatedRecord->type_id)->first();

                                                    ?>
                                                    <tr>
                                                        <td>{{ $rentPaymentResult->id }}</td>
                                                        <td>{{ $rentPaymentResult->month_name }}</td>
                                                        <td>{{ $rentPaymentResult->year }}</td>
                                                        <td>{{ round($rentPaymentResult->rent_amount) }}</td>


                                                        <?php 
                                                        $advancedStatus = getAdvancedEntry($bankingLedger->vendor_type,$bankingLedger->id,$bankingLedger->vendor_type_id); 

                                                        ?>
                                                        @if($advancedStatus == 0)
                                                        <td><input type="text" name="rent_payment_amount[{{ $rentPaymentResult->id }}]" class='rent_payment_amount_{{ $val }} rent_payment_amount form-control' style='width:100px;' data-pending-rent="{{ $rentPaymentResult->rent_amount }}" value="{{ round($relatedRecord->pay_amount) }}" required></td>
                                                        @else
                                                        <td><input type="text" name="rent_payment_amount[{{ $rentPaymentResult->id }}]" class='rent_payment_amount_{{ $val }} rent_payment_amount form-control' style='width:100px;' data-pending-rent="{{ $rentPaymentResult->rent_amount }}" value="{{ round($relatedRecord->pay_amount) }}" readonly></td>
                                                        @endif
                                                    </tr>
                                                @endforeach
                                            @endif
                                        </tbody>

                                    </table>

                                </div>

                                <div class="salary_transaction_table transaction_table" @if($bankingLedger->vendor_type != 1 || count($bankingLedger['relatedRecord']) == 0 ) style="display:none" @endif>

                                    <div class="card-header header-elements-inline">

                                        <h6 class="card-title font-weight-semibold">Transaction List</h6>

                                    </div>

                                    <table id="salary_transaction_list" class="table datatable-show-all" >

                                        <thead>

                                            <tr>

                                                <th>Transaction ID.</th>

                                                <th>Month</th>

                                                <th>Year</th>    

                                                <th>Due Amount</th>

                                                <th>Payment(INR)</th>

                                            </tr>

                                        </thead> 

                                        <tbody class="transaction_table_list salary_transaction_table_list">
                                            @if($bankingLedger->vendor_type == 1)
                                                @foreach($bankingLedger['relatedRecord'] as $val => $relatedRecord)
                                                    <?php
                                                    $employeePaymentResult = \App\Models\EmployeeSalary::where('id', $relatedRecord->type_id)->first();
                                                    ?>
                                                    <tr>
                                                        <td>{{ $employeePaymentResult->id }}</td>
                                                        <td>{{ $employeePaymentResult->month_name }}</td>
                                                        <td>{{ $employeePaymentResult->year }}</td>
                                                        <td>{{ round($employeePaymentResult->total_salary) }}</td>
                                                        <td><input type="text" name="salary_payment_amount[{{ $employeePaymentResult->id }}]" class='salary_payment_amount_{{ $val }} salary_payment_amount form-control' style='width:100px;' data-pending-salary="{{ $employeePaymentResult->total_salary }}" value="{{ round($relatedRecord->pay_amount) }}" required></td>
                                                    </tr>
                                                @endforeach
                                            @endif
                                        </tbody>

                                    </table>

                                </div>

                                <div class="associate_transaction_table transaction_table" @if($bankingLedger->vendor_type != 2 || count($bankingLedger['relatedRecord']) == 0) style="display:none" @endif>

                                    <div class="card-header header-elements-inline">

                                        <h6 class="card-title font-weight-semibold">Customer Advanced Payment List</h6>

                                    </div>

                                    <table id="associate_transaction_list" class="table datatable-show-all" >

                                        <thead>

                                            <tr>

                                                <th>Transaction ID.</th>  

                                                <th>Commission Amount</th>

                                                <th>Fuel Amount</th>

                                                <th>Commission Payment(INR)</th>

                                                <th>Fuel Payment(INR)</th>

                                            </tr>

                                        </thead> 

                                        <tbody class="transaction_table_list associate_transaction_table_list">
                                            
                                        </tbody>

                                    </table>

                                </div>

                                <div class="vendor_transaction_table transaction_table" @if($bankingLedger->vendor_type != 3 ||  count($bankingLedger['relatedRecord']) == 0) style="display:none" @endif>

                                    <div class="card-header header-elements-inline">

                                        <h6 class="card-title font-weight-semibold">Customer Advanced Payment List</h6>

                                    </div>

                                    <table id="vendor_transaction_list" class="table datatable-show-all" >

                                        <thead>

                                            <tr>

                                                <th>Transaction ID.</th>  

                                                <th>Bill Number</th>

                                                <th>Pending Amount</th>

                                                <th>Payment(INR)</th>

                                            </tr>

                                        </thead> 

                                        <tbody class="transaction_table_list vendor_transaction_table_list">
                                        @if($bankingLedger->vendor_type == 2)
                                            @foreach($bankingLedger['relatedRecord'] as $val => $relatedRecord)
                                                <?php
                                                $vendorResult = \App\Models\VendorBill::where('id', $relatedRecord->type_id)->first();
                                                ?>
                                                <tr>
                                                    <td>{{ $vendorResult->id }}</td>
                                                    <td>{{ $vendorResult->month_name }}</td>
                                                    <td>{{ $vendorResult->year }}</td>
                                                    <td>{{ round($vendorResult->payble_amount) }}</td>
                                                    <td><input type="text" name="vendor_pending_payment_amount[{{ $vendorResult->id }}]" class='vendor_pending_payment_amount{{ $val }} vendor_pending_payment_amount form-control' style='width:100px;' data-pending-vendor="{{ $vendorResult->payble_amount }}" value="{{ round($relatedRecord->pay_amount) }}" required></td>
                                                </tr>
                                            @endforeach
                                        @endif
                                        </tbody>

                                    </table>

                                </div>

                                <div class="customer_transaction_table transaction_table" @if($bankingLedger->vendor_type != 4 ||  count($bankingLedger['relatedRecord']) == 0) style="display:none" @endif>

                                    <div class="card-header header-elements-inline">

                                        <h6 class="card-title font-weight-semibold">Customer Advanced Payment List</h6>

                                    </div>

                                    <table id="customer_transaction_list" class="table datatable-show-all" >

                                        <thead>

                                            <tr>

                                                <th>Transaction ID.</th>  

                                                <th>Advanced Amount</th>

                                                <th>Payment(INR)</th>

                                            </tr>

                                        </thead> 

                                        <tbody class="transaction_table_list customer_transaction_table_list">

                                        </tbody>

                                    </table>

                                </div>


                                <div class="text-right mt-10">
                                    <input type="submit" name="submitform" value="Submit" class="btn btn-primary submit">
                                </div>
                            </form>
                        @endif  
                        
                        @if($bankingLedger->banking_type == 3)
                            <form method="post" action="{!! route('admin.banking.update') !!}" id="edit-banking-form"  data-type="3">
                                @csrf
                                <input type="hidden" name="type" value="3">
                                <input type="hidden" name="subtype" value="2">
                                <input type="hidden" name="id" value="{{ $bankingLedger->id }}">
                                @csrf
                                <div class="row">
                                    
                                    <div class="col-lg-6">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-2">Card<sup>*</sup></label>
                                            <div class="col-lg-10 error-msg">
                                               <select class="form-control" name="credit_card_id" id="credit_card_id" required>
                                                    <option value="">Choose credit card...</option>
                                                    @foreach( $credit_cards as $credit_card)
                                                    <option @if($bankingLedger->credit_card_id == $credit_card->id) selected @endif  value="{{ $credit_card->id }}"  >{{ $credit_card->credit_card_number }}</option> 
                                                    @endforeach
                                               </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-2">Branch Name<sup>*</sup></label>
                                            <div class="col-lg-10 error-msg">
                                               <select class="form-control" name="credit_card_branch_id" id="credit_card_branch_id">
                                                    <!-- <option value="">Choose branch...</option> -->
                                                    @foreach( $branches as $branch)
                                                    <option @if($bankingLedger->branch_id == $branch->id) selected @endif value="{{ $branch->id }}"  >{{ $branch->name }} ({{$branch->branch_code}})</option> 
                                                    @endforeach
                                               </select>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-lg-6">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-2">Date<sup>*</sup></label>
                                            <div class="col-lg-10 error-msg">
                                                <input class="form-control" type="text" name="credit_card_payment_date" id="credit_card_payment_date" value="{{ date("d/m/Y ", strtotime(convertDate($bankingLedger->date))) }}" required>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-2">Amount<sup>*</sup></label>
                                            <div class="col-lg-10 error-msg">
                                                <input class="form-control" type="text" name="credit_card_amount" id="credit_card_amount" value="{{ round($bankingLedger->amount) }}" readonly required>
                                                <input type="hidden" name="credit_card_total_amount" id="credit_card_total_amount" value="{{ $relatedRecordAmount }}">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-2">Description<sup>*</sup></label>
                                            <div class="col-lg-10 error-msg">
                                                <input class="form-control" type="text" name="credit_card_description" id="credit_card_description" value="{{ round($bankingLedger->amount) }}" value="{{ $bankingLedger->description }}" required>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-lg-6">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-2">Select Mode <sup>*</sup></label>
                                            <div class="col-lg-10 error-msg">
                                               <select class="form-control" name="credit_card_mode" id="credit_card_mode" required>
                                                    <option value="">Choose Mode...</option>
                                                    <option @if($bankingLedger->payment_mode == 1) selected @endif  value="1">Bank</option>
                                               </select>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-lg-6 CreditCardbankDiv" @if($bankingLedger->payment_mode != 1) style="display:none" @endif>
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-2">Bank Name<sup>*</sup></label>
                                            <div class="col-lg-10 error-msg">
                                               <select class="form-control banks_id" name="credit_card_bank_id" id="credit_card_bank_id" required>
                                                    <option value="">Choose bank...</option>
                                                    @foreach( $banks as $bank)
                                                    <option @if($bankingLedger->bank_id == $bank->id) selected @endif value="{{ $bank->id }}"  >{{ $bank->bank_name }}</option> 
                                                    @endforeach
                                               </select>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-lg-6 CreditCardbankDiv" @if($bankingLedger->payment_mode != 1) style="display:none" @endif>
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-2">Account Number<sup>*</sup></label>
                                            <div class="col-lg-10 error-msg" required>
                                               <select class="form-control paid_via_account_number" name="credit_card_account_number" id="credit_card_account_number" required>
                                                @if($accounts)
                                                <option selected="" value="{{ $accounts->id }}">{{ $accounts->account_no }}</option>
                                                @endif
                                               </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-6 CreditCardbankDiv" @if($bankingLedger->payment_mode != 1) style="display:none" @endif>
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-2">Paid Via<sup>*</sup></label>
                                            <div class="col-lg-10 error-msg">
                                               <select class="form-control paid_via" name="credit_card_customer_paid_via" id="credit_card_customer_paid_via" required>
                                                    <option value="">Choose payment type...</option>
                                                    <option @if($bankingLedger->paid_via == 1) selected @endif value="1">Cheque</option>
                                                    <option @if($bankingLedger->paid_via == 2) selected @endif value="2">bank transfer</option>
                                               </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-6 CreditCardbankneftutrDiv" @if($bankingLedger->payment_mode != 1 || $bankingLedger->paid_via != 2) style="display:none" @endif>
                                        <div class="form-group row CreditCardbankneftutrDiv">
                                            <label class="col-form-label col-lg-2">NEFT/UTR No.<sup>*</sup></label>
                                            <div class="col-lg-10 error-msg">
                                               <input class="form-control" type="text" name="credit_card_utr" id="credit_card_utr" value="{{ $bankingLedger->neft_utr_no }}" required>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-6 CreditCardbankutrDiv" @if($bankingLedger->payment_mode != 1 || $bankingLedger->paid_via != 2) style="display:none" @endif>
                                        <div class="form-group row CreditCardbankutrDiv">
                                            <label class="col-form-label col-lg-2">NEFT Charges<sup>*</sup></label>
                                            <div class="col-lg-10 error-msg">
                                               <input class="form-control" type="text" name="credit_card_neft" id="credit_card_neft" value="{{ round($bankingLedger->neft_charge) }}" required>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-6 CreditCardCustomerChequebankDiv" @if($bankingLedger->payment_mode != 1 || $bankingLedger->paid_via != 1) style="display:none" @endif>
                                        <div class="form-group row paymentchequeDiv">
                                            <label class="col-form-label col-lg-2">Cheque<sup>*</sup></label>
                                            <div class="col-lg-10 error-msg">
                                               <select class="form-control" name="credit_card_customer_cheque_no" id="credit_card_customer_cheque_no" required>
                                                <option value="">Please Select</option>
                                                @foreach($cheques as $cheque)
                                                <option @if($bankingLedger->cheque_no == $cheque->id) selected @endif value="{{ $cheque->id }}">{{ $cheque->cheque_no }}</option>
                                                @endforeach
                                               </select>
                                            </div>
                                        </div>
                                    </div>
                                    
                                </div>

                                <div class="credit_card_transaction_table transaction_table" @if($bankingLedger->banking_type != 3 || count($bankingLedger['relatedRecord']) == 0) style="display:none" @endif>

                                    <div class="card-header header-elements-inline">

                                        <h6 class="card-title font-weight-semibold">Transaction List</h6>

                                    </div>

                                    <table id="credit_card_transaction_list" class="table datatable-show-all" >

                                        <thead>

                                            <tr>

                                                <th>Transaction ID.</th>  

                                                <th>Bill Number</th>

                                                <th>Amount</th>

                                                <th>Due Amount</th>

                                                <th>Payment(INR)</th>

                                            </tr>

                                        </thead> 

                                        <tbody class="transaction_table_list credit_card_transaction_table_list">
                                            @foreach($bankingLedger['relatedRecord'] as $val => $relatedRecord)
                                                <?php
                                                $cCardPaymentResult = \App\Models\CreditCradTransaction::where('id', $relatedRecord->type_id)->first();

                                                ?>
                                                <tr>
                                                    <td>{{ $cCardPaymentResult->id }}</td>
                                                    @if($cCardPaymentResult->bill_id)
                                                        <td>{{ $cCardPaymentResult->bill_id }}</td>
                                                    @else
                                                        <td>N/A</td>
                                                    @endif
                                                    <td>{{ $cCardPaymentResult->total_amount }}</td>
                                                    <td>{{ round($cCardPaymentResult->total_amount-$cCardPaymentResult->used_amount) }}</td>


                                                    <?php 
                                                    $advancedStatus = getAdvancedEntry($bankingLedger->vendor_type,$bankingLedger->id,$bankingLedger->vendor_type_id); 

                                                    ?>
                                                    @if($advancedStatus == 0)
                                                    <td><input type="text" name="credit_card_payment_amount[{{ $cCardPaymentResult->id }}]" class='credit_card_payment_amount{{ $val }} credit_card_payment_amount form-control' style='width:100px;' data-credit-card="{{ $cCardPaymentResult->total_amount-$cCardPaymentResult->used_amount }}" value="{{ round($relatedRecord->pay_amount) }}" required></td>
                                                    @else
                                                    <td><input type="text" name="credit_card_payment_amount[{{ $cCardPaymentResult->id }}]" class='credit_card_payment_amount{{ $val }} credit_card_payment_amount form-control' style='width:100px;' data-credit-card="{{ $cCardPaymentResult->total_amount-$cCardPaymentResult->used_amount }}" value="{{ round($relatedRecord->pay_amount) }}" readonly></td>
                                                    @endif
                                                </tr>
                                            @endforeach
                                        </tbody>

                                    </table>

                                </div>  
                                
                                <div class="row">
                                    <div class="col-lg-12">
                                        <div class="text-right mt-10">
                                            <input type="submit" name="submitform" value="Submit" class="btn btn-primary submit">
                                        </div>
                                    </div>
                                </div>
                            </form>
                        @endif  
                        
                        @if($bankingLedger->banking_type == 4)
                            <form method="post" action="{!! route('admin.banking.update') !!}" id="edit-banking-form" data-type="4">
                                @csrf
                                <input type="hidden" name="type" value="4">
                                <input type="hidden" name="subtype" value="1">
                                <input type="hidden" name="id" value="{{ $bankingLedger->id }}">
                                <div class="row">
                                    <div class="col-lg-12">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-1">Account<sup>*</sup></label>
                                            <div class="col-lg-11 error-msg">
                                               <select class="form-control" name="receive_payment_account_type_edit" id="receive_payment_account_type_edit" required="" disabled>
                                                    <option value="">Choose account type...</option>
                                                    <option @if($bankingLedger->account_type == 1) selected @endif value="1">Vendor</option>
                                                    <option @if($bankingLedger->account_type == 2) selected @endif value="2">Customer</option>
                                               </select>

                                               <input type="hidden" name="receive_payment_account_type" id="receive_payment_account_type" value="{{ $bankingLedger->account_type }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row received_payment_vendor_div" id="received_payment_vendor_div" @if($bankingLedger->account_type != 1) style="display:none" @endif>
                                    <div class="col-lg-6">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-2">Vendor Type<sup>*</sup></label>
                                            <div class="col-lg-10 error-msg">
                                               <select class="form-control" name="received_payment_vendor_type_edit" id="received_payment_vendor_type_edit" disabled>
                                                    <option value="">Choose vendor type...</option>
                                                    <option @if($bankingLedger->vendor_type == 0) selected @endif value="0">Rent</option>
                                                    <option @if($bankingLedger->vendor_type == 1) selected @endif value="1">Salary</option>
                                                    <option @if($bankingLedger->vendor_type == 2) selected @endif value="2">Associates</option>
                                                    <option @if($bankingLedger->vendor_type == 3) selected @endif value="3">Vendors</option>
                                               </select>

                                               <input type="hidden" name="received_payment_vendor_type" id="received_payment_vendor_type" value="{{ $bankingLedger->vendor_type }}">
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-lg-6">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-2">Branch Name<sup>*</sup></label>
                                            <div class="col-lg-10 error-msg">
                                               <select class="form-control" name="received_payment_branch_id" id="received_payment_branch_id">
                                                    <!-- <option value="">Choose branch...</option> -->
                                                    @foreach( $branches as $branch)
                                                    <option @if($bankingLedger->branch_id == $branch->id) selected @endif value="{{ $branch->id }}"  >{{ $branch->name }} ({{$branch->branch_code}})</option> 
                                                    @endforeach
                                               </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-2 received-vendor-associate-name">Vendor Name <sup>*</sup></label>
                                            <div class="col-lg-10 error-msg">

                                                <?php
                                                if($bankingLedger->vendor_type == 0){
                                                    $result = \App\Models\RentLiability::where('id',$bankingLedger->vendor_type_id)->first();
                                                    $redId = $result->id;
                                                    $resName = $result->owner_name;
                                                }elseif($bankingLedger->vendor_type == 1){
                                                    $result =  \App\Models\Employee::where('id',$bankingLedger->vendor_type_id)->first();
                                                    $redId = $result->id;
                                                    $resName = $result->employee_name;
                                                }elseif($bankingLedger->vendor_type == 2){
                                                    $result =  \App\Models\Member::where('id',$bankingLedger->vendor_type_id)->first();
                                                    $redId = $result->id;
                                                    $resName = $result->associate_no;
                                                }elseif($bankingLedger->vendor_type == 3){
                                                    $result =  \App\Models\Vendor::where('id',$bankingLedger->vendor_type_id)->where('type',0)->first();
                                                    $redId = $result->id;
                                                    $resName = $result->employee_name;
                                                }elseif($bankingLedger->vendor_type == 4 || $bankingLedger->vendor_type == 5){
                                                    $result =  \App\Models\Vendor::where('id',$bankingLedger->vendor_type_id)->where('type',1)->first();
                                                    $redId = $result->id;
                                                    $resName = $result->employee_name;
                                                }
                                                ?>

                                                <select name="received_payment_vendor_name_edit" id="received_payment_vendor_name_edit" class="form-control frm select2" data-row="1" data-value="1" disabled>
                                                      <option value="">Please Selct</option>
                                                      <option selected="" value="{{ $redId }}">{{ $resName }}</option>
                                                </select>

                                                <input type="hidden" name="received_payment_vendor_name" id="received_payment_vendor_name" value="{{ $redId }}">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-2">Amount<sup>*</sup></label>
                                            <div class="col-lg-10 error-msg">
                                               <input class="form-control" type="text" name="vendor_received_payment_amount" id="vendor_received_payment_amount" value="{{ round($bankingLedger->amount) }}">
                                               <input type="hidden" name="vendor_received_total_amount" id="vendor_received_total_amount" value={{ round($relatedRecordAdvancedAmount) }}>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-lg-6">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-2">Date<sup>*</sup></label>
                                            <div class="col-lg-10 error-msg">
                                                <input class="form-control" type="text" name="received_payment_vendor_date" id="received_payment_vendor_date"  value="{{ date("d/m/Y ", strtotime(convertDate($bankingLedger->date))) }}">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-2">Description<sup>*</sup></label>
                                            <div class="col-lg-10 error-msg">
                                                <input class="form-control" type="text" name="received_payment_vendor_description" id="received_payment_vendor_description" value="{{ $bankingLedger->description }}">
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-lg-6">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-2">Select Mode <sup>*</sup></label>
                                            <div class="col-lg-10 error-msg">
                                               <select class="form-control" name="vendor_received_payment_mode" id="vendor_received_payment_mode">
                                                    <option value="">Choose Mode...</option>
                                                    <option @if($bankingLedger->payment_mode == 1) selected @endif  value="1">Bank</option>
                                                    <option @if($bankingLedger->payment_mode == 2) selected @endif value="2">Cash</option>
                                               </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-6 ReceivedPaymentVendorbankDiv" style="display:none">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-2">Bank Name<sup>*</sup></label>
                                            <div class="col-lg-10 error-msg">
                                               <select class="form-control banks_id" name="received_payment_vendor_bank_id" id="received_payment_vendor_bank_id">
                                                    <option value="">Choose bank...</option>
                                                    @foreach( $banks as $bank)
                                                    <option @if($bankingLedger->bank_id == $bank->id) selected @endif value="{{ $bank->id }}"  >{{ $bank->bank_name }}</option> 
                                                    @endforeach
                                               </select>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-lg-6 ReceivedPaymentVendorbankDiv" style="display:none">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-2">Account Number<sup>*</sup></label>
                                            <div class="col-lg-10 error-msg">
                                               <select class="form-control paid_via_account_number" name="received_payment_vendor_bank_account_number" id="received_payment_vendor_bank_account_number">
                                                @if($accounts)
                                                <option selected="" value="{{ $accounts->id }}">{{ $accounts->account_no }}</option>
                                                @endif
                                               </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-6 ReceivedPaymentVendorbankDiv" style="display:none">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-2">Paid Via<sup>*</sup></label>
                                            <div class="col-lg-10 error-msg">
                                               <select class="form-control paid_via" name="received_payment_vendor_paid_via" id="received_payment_vendor_paid_via">
                                                    <option value="">Choose payment type...</option>
                                                    <option @if($bankingLedger->paid_via == 1) selected @endif value="1">Cheque</option>
                                                    <option @if($bankingLedger->paid_via == 2) selected @endif value="2">bank transfer</option>
                                               </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-6 ReceivedPaymentVendorbankneftutrDiv" style="display:none">
                                        <div class="form-group row ReceivedPaymentVendorbankneftutrDiv">
                                            <label class="col-form-label col-lg-2">NEFT/UTR No.<sup>*</sup></label>
                                            <div class="col-lg-10 error-msg">
                                               <input class="form-control" type="text" name="received_payment_vendor_utr" id="received_payment_vendor_utr" value="{{ $bankingLedger->neft_utr_no }}" required>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- <div class="col-lg-6 ReceivedPaymentVendorbankutrDiv" style="display:none">
                                        <div class="form-group row ReceivedPaymentVendorbankutrDiv">
                                            <label class="col-form-label col-lg-2">NEFT Charge<sup>*</sup></label>
                                            <div class="col-lg-10 error-msg">
                                               <input class="form-control" type="text" name="received_payment_vendor_neft" id="received_payment_vendor_neft" required>
                                            </div>
                                        </div>
                                    </div> -->
                                    <input type="hidden" name="received_payment_vendor_neft" id="received_payment_vendor_neft" value="0">

                                    <div class="col-lg-6 ReceivedPaymentVendorChequebankDiv" style="display:none">
                                        <div class="form-group row paymentchequeDiv">
                                            <label class="col-form-label col-lg-2">Cheque<sup>*</sup></label>
                                            <div class="col-lg-10 error-msg">
                                               <select class="form-control" name="received_payment_vendor_cheque_no" id="received_payment_vendor_cheque_no">
                                                @foreach($cheques as $cheque)
                                                <option @if($bankingLedger->cheque_no == $cheque->id) selected @endif value="{{ $cheque->id }}">{{ $cheque->cheque_no }}</option>
                                                @endforeach
                                               </select>
                                            </div>
                                        </div>
                                    </div>

                                    

                                    <!-- <div class="col-lg-6 ReceivedPaymentVendorCashDiv" style="display:none">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-2">Branch Name<sup>*</sup></label>
                                            <div class="col-lg-10 error-msg">
                                               <select class="form-control" name="received_payment_vendor_branch_id" id="received_payment_vendor_branch_id">
                                                    <option value="">Choose branch...</option>
                                                    @foreach( $branches as $branch)
                                                    <option value="{{ $branch->id }}"  >{{ $branch->name }} ({{$branch->branch_code}})</option> 
                                                    @endforeach
                                               </select>
                                            </div>
                                        </div>
                                    </div> -->
                                    
                                    <!-- <div class="col-lg-6 ReceivedPaymentVendorCashDiv" style="display:none">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-2">Cash<sup>*</sup></label>
                                            <div class="col-lg-10 error-msg">
                                               <select class="form-control" name="received_payment_vendor_cash_type" id="received_payment_vendor_cash_type">
                                                    <option value="">Select Cash...</option>
                                                    <option value="1">Micro Cash</option>
                                               </select>
                                            </div>
                                        </div>
                                    </div> -->

                                </div>
                                
                                <div class="row received_payment_customer_div" id="received_payment_customer_div" @if($bankingLedger->account_type != 2) style="display:none" @endif>
                                    <input type="hidden" name="received_cus_advance_type" id="received_cus_advance_type" value="5">
                                    <div class="col-lg-6">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-2">Branch Name<sup>*</sup></label>
                                            <div class="col-lg-10 error-msg">
                                               <select class="form-control" name="received_payment_customer_branch_id" id="received_payment_customer_branch_id" required>
                                                    <!-- <option value="">Choose branch...</option> -->
                                                    @foreach( $branches as $branch)
                                                    <option @if($bankingLedger->branch_id == $branch->id) selected @endif value="{{ $branch->id }}"  >{{ $branch->name }} ({{$branch->branch_code}})</option> 
                                                    @endforeach
                                               </select>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-lg-6">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-2">Customer Name <sup>*</sup></label>
                                            <div class="col-lg-10 error-msg">
                                                <?php
                                                if($bankingLedger->vendor_type == 0){
                                                    $result = \App\Models\RentLiability::where('id',$bankingLedger->vendor_type_id)->first();
                                                    $redId = $result->id;
                                                    $resName = $result->owner_name;
                                                }elseif($bankingLedger->vendor_type == 1){
                                                    $result =  \App\Models\Employee::where('id',$bankingLedger->vendor_type_id)->first();
                                                    $redId = $result->id;
                                                    $resName = $result->employee_name;
                                                }elseif($bankingLedger->vendor_type == 2){
                                                    $result =  \App\Models\Member::where('id',$bankingLedger->vendor_type_id)->first();
                                                    $redId = $result->id;
                                                    $resName = $result->associate_no;
                                                }elseif($bankingLedger->vendor_type == 3){
                                                    $result =  \App\Models\Vendor::where('id',$bankingLedger->vendor_type_id)->where('type',0)->first();
                                                    $redId = $result->id;
                                                    $resName = $result->employee_name;
                                                }elseif($bankingLedger->vendor_type == 4 || $bankingLedger->vendor_type == 5){
                                                    $result =  \App\Models\Vendor::where('id',$bankingLedger->vendor_type_id)->where('type',1)->first();
                                                    $redId = $result->id;
                                                    $resName = $result->employee_name;
                                                }
                                                ?>
                                                <select name="received_payment_customer_name_edit" id="received_payment_customer_name_edit" class="form-control frm select2" data-row="1" data-value="1" required disabled>
                                                      <option value="">Please Selct</option>
                                                      <option selected="" value="{{ $redId }}">{{ $resName }}</option>
                                                </select>

                                                <input type="hidden" name="received_payment_customer_name" id="received_payment_customer_name" value="{{ $redId }}">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-2">Date<sup>*</sup></label>
                                            <div class="col-lg-10 error-msg">
                                                <input class="form-control" type="text" name="received_payment_customer_date" id="received_payment_customer_date" value="{{ date("d/m/Y ", strtotime(convertDate($bankingLedger->date))) }}" required>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-2">Amount<sup>*</sup></label>
                                            <div class="col-lg-10 error-msg">
                                               <input class="form-control" type="text" name="received_customer_payment_amount" id="received_customer_payment_amount" value="{{ round($bankingLedger->amount) }}" required>
                                               <input type="hidden" name="received_customer_total_amount" id="received_customer_total_amount" required>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-2">Description<sup>*</sup></label>
                                            <div class="col-lg-10 error-msg">
                                                <input class="form-control" type="text" name="received_payment_customer_description" id="received_payment_customer_description" value="{{ $bankingLedger->description }}" required>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!--
                                    <div class="col-lg-6">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-2">Date<sup>*</sup></label>
                                            <div class="col-lg-10 error-msg">
                                                <input class="form-control" type="text" name="received_payment_customer_date" id="received_payment_customer_date">
                                            </div>
                                        </div>
                                    </div> -->
                                    
                                    <div class="col-lg-6">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-2">Select Mode <sup>*</sup></label>
                                            <div class="col-lg-10 error-msg">
                                               <select class="form-control" name="received_payment_customer_mode" id="received_payment_customer_mode" required>
                                                    <option value="">Choose Mode...</option>
                                                    <option @if($bankingLedger->payment_mode == 1) selected @endif  value="1">Bank</option>
                                                    <option @if($bankingLedger->payment_mode == 2) selected @endif value="2">Cash</option>
                                               </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-6 ReceivedPaymentCustomerbankDiv" style="display:none">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-2">Bank Name<sup>*</sup></label>
                                            <div class="col-lg-10 error-msg">
                                               <select class="form-control banks_id" name="received_payment_customer_bank_id" id="received_payment_customer_bank_id" required>
                                                    <option value="">Choose bank...</option>
                                                    @foreach( $banks as $bank)
                                                    <option @if($bankingLedger->bank_id == $bank->id) selected @endif value="{{ $bank->id }}"  >{{ $bank->bank_name }}</option> 
                                                    @endforeach
                                               </select>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-lg-6 ReceivedPaymentCustomerbankDiv" style="display:none">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-2">Account Number<sup>*</sup></label>
                                            <div class="col-lg-10 error-msg" required>
                                               <select class="form-control paid_via_account_number" name="received_payment_customer_bank_account_number" id="received_payment_customer_bank_account_number">
                                                @if($accounts)
                                                <option selected="" value="{{ $accounts->id }}">{{ $accounts->account_no }}</option>
                                               </select>
                                               @endif
                                               </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-6 ReceivedPaymentCustomerbankDiv" style="display:none">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-2">Paid Via<sup>*</sup></label>
                                            <div class="col-lg-10 error-msg">
                                               <select class="form-control paid_via" name="received_payment_customer_paid_via" id="received_payment_customer_paid_via" required>
                                                    <option value="">Choose payment type...</option>
                                                    <option @if($bankingLedger->paid_via == 1) selected @endif value="1">Cheque</option>
                                                    <option @if($bankingLedger->paid_via == 2) selected @endif value="2">bank transfer</option>
                                               </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-6 ReceivedPaymentCustomerbankneftutrDiv" style="display:none">
                                        <div class="form-group row ReceivedPaymentCustomerbankneftutrDiv">
                                            <label class="col-form-label col-lg-2">NEFT/UTR No.<sup>*</sup></label>
                                            <div class="col-lg-10 error-msg">
                                               <input class="form-control" type="text" name="received_payment_customer_utr" value="{{ $bankingLedger->neft_utr_no }}"  id="received_payment_customer_utr" required>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- <div class="col-lg-6 ReceivedPaymentCustomerbankutrDiv" style="display:none">
                                        <div class="form-group row ReceivedPaymentCustomerbankutrDiv">
                                            <label class="col-form-label col-lg-2">NEFT Charge<sup>*</sup></label>
                                            <div class="col-lg-10 error-msg">
                                               <input class="form-control" type="text" name="received_payment_customer_neft" id="received_payment_customer_neft" required>
                                            </div>
                                        </div>
                                    </div> -->
                                    <input type="hidden" name="received_payment_customer_neft" id="received_payment_customer_neft" value="0">

                                    <div class="col-lg-6 ReceivedPaymentCustomerChequebankDiv" style="display:none">
                                        <div class="form-group row receivedPaymentchequeDiv">
                                            <label class="col-form-label col-lg-2">Cheque<sup>*</sup></label>
                                            <div class="col-lg-10 error-msg">
                                               <select class="form-control" name="received_payment_customer_cheque_no" id="received_payment_customer_cheque_no" required>
                                                @foreach($cheques as $cheque)
                                                <option @if($bankingLedger->cheque_no == $cheque->id) selected @endif value="{{ $cheque->id }}">{{ $cheque->cheque_no }}</option>
                                                @endforeach
                                               </select>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- <div class="col-lg-6 ReceivedPaymentCustomerCashDiv" style="display:none">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-2">Branch Name<sup>*</sup></label>
                                            <div class="col-lg-10 error-msg">
                                               <select class="form-control" name="received_payment_customer_branch_id" id="received_payment_customer_branch_id">
                                                    <option value="">Choose branch...</option>
                                                    @foreach( $branches as $branch)
                                                    <option value="{{ $branch->id }}"  >{{ $branch->name }} ({{$branch->branch_code}})</option> 
                                                    @endforeach
                                               </select>
                                            </div>
                                        </div>
                                    </div> -->
                                    
                                    <!-- <div class="col-lg-6 ReceivedPaymentCustomerCashDiv" style="display:none">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-2">Cash<sup>*</sup></label>
                                            <div class="col-lg-10 error-msg">
                                               <select class="form-control" name="received_payment_customer_cash_type" id="received_payment_customer_cash_type" required>
                                                    <option value="">Select Cash...</option>
                                                    <option value="1">Micro Cash</option>
                                               </select>
                                            </div>
                                        </div>
                                    </div> -->
                                    
                                </div>

                                <div class="rent_advanced_transaction_table transaction_table" @if($bankingLedger->vendor_type != 0 || count($bankingLedger['relatedAdvancedRecord']) == 0) style="display:none" @endif>

                                    <div class="card-header header-elements-inline">

                                        <h6 class="card-title font-weight-semibold">Transaction List</h6>

                                    </div>

                                    <table id="rent_advanced_transaction_list" class="table datatable-show-all" >

                                        <thead>

                                            <tr>

                                                <th>Transaction ID.</th>  

                                                <th>Total Amount</th>

                                                <th>Advanced Amount</th>

                                                <th>Payment(INR)</th>

                                            </tr>

                                        </thead> 

                                        <tbody class="transaction_table_list rent_advanced_transaction_table_list">
                                            @foreach($bankingLedger['relatedAdvancedRecord'] as $val => $relatedRecord)
                                            <?php
                                            $rentPaymentResult = \App\Models\BankingLedger::where('id', $relatedRecord->banking_transaction_id)->first();
                                            ?>
                                            <tr>
                                                <td>{{ $rentPaymentResult->id }}</td>
                                                <td>{{ round($rentPaymentResult->amount) }}</td>
                                                <td>{{ round($relatedRecord->amount) }}</td>

                                                <?php 
                                                $advancedStatus = getNextAdvancedEntry($bankingLedger->vendor_type,$bankingLedger->id,$bankingLedger->vendor_type_id); 
                                                ?>
                                                <td><input type="text" name="rent_advanced_payment_amount[{{ $rentPaymentResult->id }}]" class='rent_advanced_payment_amount_{{ $val }} rent_advanced_payment_amount form-control' style='width:100px;' data-advanced-rent="{{ $relatedRecord->amount }}" value="{{ round($relatedRecord->pay_amount) }}"></td>
                                            </tr>
                                            @endforeach
                                        </tbody>

                                    </table>
                                </div>

                                <div class="salary_advanced_transaction_table transaction_table" @if($bankingLedger->vendor_type != 1 || count($bankingLedger['relatedAdvancedRecord']) == 0) style="display:none" @endif>

                                    <div class="card-header header-elements-inline">

                                        <h6 class="card-title font-weight-semibold">Transaction List</h6>

                                    </div>

                                    <table id="salary_advanced_transaction_list" class="table datatable-show-all" >

                                        <thead>

                                            <tr>

                                                <th>Transaction ID.</th>  

                                                <th>Total Amount</th>

                                                <th>Advance Amount</th>

                                                <th>Payment(INR)</th>

                                            </tr>

                                        </thead> 

                                        <tbody class="transaction_table_list salary_advanced_transaction_table_list">
                                            @foreach($bankingLedger['relatedAdvancedRecord'] as $val => $relatedRecord)
                                            <?php
                                            $salaryResult = \App\Models\BankingLedger::where('id', $relatedRecord->banking_transaction_id)->first();
                                            ?>
                                            <tr>
                                                <td>{{ $salaryResult->id }}</td>
                                                <td>{{ round($salaryResult->amount) }}</td>
                                                <td>{{ round($relatedRecord->amount) }}</td>
                                                <td><input type="text" name="salary_advanced_payment_amount[{{ $salaryResult->id }}]" class='salary_advanced_payment_amount_{{ $val }} salary_advanced_payment_amount form-control' style='width:100px;' data-advanced-salary="{{ $relatedRecord->amount }}" value="{{ round($relatedRecord->pay_amount) }}" required></td>
                                            </tr>
                                            @endforeach
                                        </tbody>

                                    </table>
                                </div>

                                <div class="associate_advanced_transaction_table transaction_table" @if($bankingLedger->vendor_type != 2 || count($bankingLedger['relatedAdvancedRecord']) == 0) style="display:none" @endif>

                                    <div class="card-header header-elements-inline">

                                        <h6 class="card-title font-weight-semibold">Transaction List</h6>

                                    </div>

                                    <table id="associate_advanced_transaction_list" class="table datatable-show-all" >

                                        <thead>

                                            <tr>

                                                <th>Transaction ID.</th>  

                                                <th>Total Amount</th>

                                                <th>Advanced Amount</th>

                                                <th>Payment(INR)</th>

                                            </tr>

                                        </thead> 

                                        <tbody class="transaction_table_list associate_advanced_transaction_table_list">
                                            @foreach($bankingLedger['relatedAdvancedRecord'] as $val => $relatedRecord)
                                            <?php
                                            $associateResult = \App\Models\BankingLedger::where('id', $relatedRecord->banking_transaction_id)->first();
                                            ?>
                                            <tr>
                                                <td>{{ $associateResult->id }}</td>
                                                <td>{{ round($associateResult->amount) }}</td>
                                                <td>{{ round($relatedRecord->amount) }}</td>
                                                <td><input type="text" name="associate_advanced_payment_amount[{{ $associateResult->id }}]" class='associate_advanced_payment_amount_{{ $val }} associate_advanced_payment_amount form-control' style='width:100px;' data-advanced-associate="{{ $relatedRecord->amount }}" value="{{ round($relatedRecord->pay_amount) }}" required></td>
                                            </tr>
                                            @endforeach
                                        </tbody>

                                    </table>
                                </div>

                                <div class="vendor_advanced_transaction_table transaction_table" @if($bankingLedger->vendor_type != 3 || count($bankingLedger['relatedAdvancedRecord']) == 0) style="display:none" @endif>

                                    <div class="card-header header-elements-inline">

                                        <h6 class="card-title font-weight-semibold">Transaction List</h6>

                                    </div>

                                    <table id="vendor_advanced_transaction_list" class="table datatable-show-all" >

                                        <thead>

                                            <tr>

                                                <th>Transaction ID.</th>  

                                                <th>Total Amount</th>

                                                <th>Advanced Amount</th>

                                                <th>Payment(INR)</th>

                                            </tr>

                                        </thead> 

                                        <tbody class="transaction_table_list vendor_advanced_transaction_table_list">
                                            @foreach($bankingLedger['relatedAdvancedRecord'] as $val => $relatedRecord)
                                            <?php
                                            $associateResult = \App\Models\BankingLedger::where('id', $relatedRecord->banking_transaction_id)->first();
                                            ?>
                                            <tr>
                                                <td>{{ $associateResult->id }}</td>
                                                <td>{{ round($associateResult->amount) }}</td>
                                                <td>{{ round($relatedRecord->amount) }}</td>
                                                <td><input type="text" name="vendor_advanced_payment_amount[{{ $associateResult->id }}]" class='associate_advanced_payment_amount_{{ $val }} associate_advanced_payment_amount form-control' style='width:100px;' data-advanced-associate="{{ $relatedRecord->amount }}" value="{{ round($relatedRecord->pay_amount) }}" required></td>
                                            </tr>
                                            @endforeach
                                        </tbody>

                                    </table>
                                </div>

                                <div class="received_customer_transaction_table transaction_table" @if($bankingLedger->vendor_type != 4 || count($bankingLedger['relatedAdvancedRecord']) == 0) style="display:none" @endif>

                                    <div class="card-header header-elements-inline">

                                        <h6 class="card-title font-weight-semibold">Customer Advanced Payment List</h6>

                                    </div>

                                    <table id="received_customer_transaction_list" class="table datatable-show-all" >

                                        <thead>

                                            <tr>

                                                <th>Transaction ID.</th>  

                                                <th>Advanced Amount</th>

                                                <th>Payment(INR)</th>

                                            </tr>

                                        </thead> 

                                        <tbody class="transaction_table_list received_customer_transaction_table_list">

                                        </tbody>

                                    </table>

                                </div>

                                <div class="text-right mt-10">
                                    <input type="submit" name="submitform" value="Submit" class="btn btn-primary submit">
                                </div>
                            </form>
                        @endif
                        
                        @if($bankingLedger->banking_type == 5)
                            <form method="post" action="{!! route('admin.banking.update') !!}" id="edit-banking-form"  data-type="5">
                                @csrf
                                <input type="hidden" name="type" value="5">
                                <input type="hidden" name="id" value="{{ $bankingLedger->id }}">
                                <input type="hidden" name="subtype" value="1">
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-2">Indirect Income<sup>*</sup></label>
                                            <div class="col-lg-10 error-msg">
                                               <select class="form-control income_head_id" name="income_head_id" id="income_head_id" data-row-id="1">
                                                    <option value="">Choose indirect income account...</option>
                                                    <!-- <option @if($bankingLedger->expense_account == 12) selected @endif value="12">Direct Income</option>  -->
                                                    <option @if($bankingLedger->expense_account == 13) selected @endif value="13">Indirect Income</option> 
                                               </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-2">Sub Head1<sup>*</sup></label>
                                            <div class="col-lg-10 error-msg">
                                               <select class="form-control income_head_id" name="income_head_id1" id="income_head_id1" data-row-id="2">
                                                <option value="">Please Select</option>
                                                @foreach( $heads as $expence_head)
                                                    @if($bankingLedger->expense_account1 == $expence_head->head_id)
                                                        <option selected value="{{ $expence_head->head_id }}"  >{{ $expence_head->sub_head }}</option>
                                                    @endif 
                                                @endforeach
                                               </select>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-lg-6">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-2">Sub Head2<sup>*</sup></label>
                                            <div class="col-lg-10 error-msg">
                                               <select class="form-control income_head_id" name="income_head_id2" id="income_head_id2" data-row-id="3">
                                                <option value="">Please Select</option>
                                                @foreach( $subCategory2 as $expence_head)
                                                    @if($bankingLedger->expense_account2 == $expence_head->head_id)
                                                        <option selected value="{{ $expence_head->head_id }}"  >{{ $expence_head->sub_head }}</option> 
                                                    @endif
                                                @endforeach
                                               </select>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-lg-6">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-2">Sub Head3<sup>*</sup></label>
                                            <div class="col-lg-10 error-msg">
                                               <select class="form-control income_head_id" name="income_head_id3" id="income_head_id3" data-row-id="4">
                                                <option value="">Please Select</option>
                                                @foreach( $subCategory3 as $expence_head)
                                                    @if($bankingLedger->expense_account3 == $expence_head->head_id)
                                                        <option selected value="{{ $expence_head->head_id }}"  >{{ $expence_head->sub_head }}</option> 
                                                    @endif
                                                @endforeach
                                               </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-6" @if($bankingLedger->payment_mode != 2) style="display:none" @endif>
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-2">Branch Name<sup>*</sup></label>
                                            <div class="col-lg-10 error-msg">
                                               <select class="form-control" name="indirect_income_branch_id" id="indirect_income_branch_id">
                                                    <option value="">Choose branch...</option>
                                                    @foreach( $branches as $branch)
                                                    <option @if($bankingLedger->branch_id == $branch->id) selected @endif value="{{ $branch->id }}"  >{{ $branch->name }} ({{$branch->branch_code}})</option> 
                                                    @endforeach
                                               </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-2">Date <sup>*</sup></label>
                                            <div class="col-lg-10 error-msg">
                                                <input class="form-control" type="text" name="indirect_income_date" id="indirect_income_date" value="{{ date("d/m/Y ", strtotime(convertDate($bankingLedger->date))) }}">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-2">Amount<sup>*</sup></label>
                                            <div class="col-lg-10 error-msg">
                                               <input class="form-control" type="text" name="indirect_income_amount" id="indirect_income_amount" value="{{ round($bankingLedger->amount) }}">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-2">Description<sup>*</sup></label>
                                            <div class="col-lg-10 error-msg">
                                               <input class="form-control" type="text" name="indirect_income_description" id="indirect_income_description" value="{{ $bankingLedger->description }}">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-2">Select Mode <sup>*</sup></label>
                                            <div class="col-lg-10 error-msg">
                                               <select class="form-control" name="indirect_income_mode" id="indirect_income_mode">
                                                    <option value="">Choose Mode...</option>
                                                    <option @if($bankingLedger->payment_mode == 1) selected @endif value="1">Bank</option>
                                                    <option @if($bankingLedger->payment_mode == 2) selected @endif value="2">Cash</option>
                                               </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-6 IndirectIncomebankDiv" @if($bankingLedger->payment_mode != 1) style="display:none" @endif>
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-2">Bank Name<sup>*</sup></label>
                                            <div class="col-lg-10 error-msg">
                                               <select class="form-control banks_id" name="indirect_income_bank_id" id="indirect_income_bank_id">
                                                    <option value="">Choose bank...</option>
                                                    @foreach( $banks as $bank)
                                                    <option @if($bankingLedger->bank_id == $bank->id) selected @endif value="{{ $bank->id }}"  >{{ $bank->bank_name }}</option> 
                                                    @endforeach
                                               </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-6 IndirectIncomebankDiv" @if($bankingLedger->payment_mode != 1) style="display:none" @endif>
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-2">Account Number<sup>*</sup></label>
                                            <div class="col-lg-10 error-msg">
                                               <select class="form-control paid_via_account_number" name="indirect_income_account_no" id="indirect_income_account_no">
                                                <option value="">Please Select</option>
                                                @if($accounts)
                                                <option selected="" value="{{ $accounts->id }}">{{ $accounts->account_no }}</option>
                                                @endif
                                               </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-6 IndirectIncomebankDiv" @if($bankingLedger->payment_mode != 1) style="display:none" @endif>
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-2">Paid Via<sup>*</sup></label>
                                            <div class="col-lg-10 error-msg">
                                               <select class="form-control paid_via" name="indirect_income_paid_via" id="indirect_income_paid_via">
                                                    <option value="">Choose payment type...</option>
                                                    <option @if($bankingLedger->paid_via == 1) selected @endif value="1">Cheque</option>
                                                    <option @if($bankingLedger->paid_via == 2) selected @endif value="2">bank transfer</option>
                                               </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-6 IndirectIncomebankneftutrDiv" @if($bankingLedger->payment_mode != 1 && $bankingLedger->paid_via != 2) style="display:none" @endif>
                                        <div class="form-group row IndirectIncomebankneftutrDiv">
                                            <label class="col-form-label col-lg-2">NEFT/UTR No.<sup>*</sup></label>
                                            <div class="col-lg-10 error-msg">
                                               <input class="form-control" type="text" name="indirect_income_utr" id="indirect_income_utr" value="{{ $bankingLedger->neft_utr_no }}" required>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- <div class="col-lg-6 IndirectIncomebankutrDiv" @if($bankingLedger->payment_mode != 1 && $bankingLedger->paid_via != 2) style="display:none" @endif>
                                        <div class="form-group row IndirectIncomebankutrDiv">
                                            <label class="col-form-label col-lg-2">NEFT Charge<sup>*</sup></label>
                                            <div class="col-lg-10 error-msg">
                                               <input class="form-control" type="text" name="indirect_income_neft" id="indirect_income_neft" value="{{ round($bankingLedger->neft_charge) }}">
                                            </div>
                                        </div>
                                    </div> -->
                                    <input type="hidden" name="indirect_income_neft" id="indirect_income_neft" value="0">

                                    <div class="col-lg-6 IndirectIncomebankDiv" @if($bankingLedger->payment_mode != 1 || $bankingLedger->paid_via != 1) style="display:none" @endif>
                                        <div class="form-group row IncomdchequeDiv">
                                            <label class="col-form-label col-lg-2">Cheque<sup>*</sup></label>
                                            <div class="col-lg-10 error-msg">
                                               <select class="form-control" name="indirect_income_cheque_no" id="indirect_income_cheque_no">
                                                <option value="">Please Select</option>
                                                @foreach($cheques as $cheque)
                                                <option @if($bankingLedger->cheque_no == $cheque->id) selected @endif value="{{ $cheque->id }}">{{ $cheque->cheque_no }}</option>
                                                @endforeach
                                               </select>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- <div class="col-lg-6 IndirectIncomeCashDiv" style="display:none">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-2">Cash<sup>*</sup></label>
                                            <div class="col-lg-10 error-msg">
                                               <select class="form-control" name="indirect_income_cash_type" id="indirect_income_cash_type">
                                                    <option value="">Select Cash...</option>
                                                    <option value="1">Micro Cash</option>
                                               </select>
                                            </div>
                                        </div>
                                    </div> -->
                                </div>

                                <div class="text-right mt-10">
                                    <input type="submit" name="submitform" value="Submit" class="btn btn-primary submit">
                                </div>
                            </form>
                        @endif  
                        
                    </div>

                </div>

                <!-- /basic layout -->

            </div>

        </div>

    </div>

</div>

@stop

@section('script')

<script src="{{url('/')}}/asset/js/sweetalert.min.js"></script>

@include('templates.admin.banking_management.partials.create_script')

@stop