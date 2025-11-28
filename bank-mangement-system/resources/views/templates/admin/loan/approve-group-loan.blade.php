@extends('templates.admin.master')
@section('content')
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header header-elements-inline">
                        <h6 class="card-title font-weight-semibold">Transfer For  {{$ssbDetails->account_number}}</h6>
                    </div>
                    <div class="card-body">
                        <p class="text-danger"></p>
                        <form action="{{route('admin.grouploan.transferamount')}}" method="post" name="loan-transfer-form" id="loan-transfer-form">
                            @csrf
                            <input type="hidden" name="created_at" class="created_at">
                            <input type="hidden" name="loan_id" class="loan_id" value="{{ $load_id }}">
                            <input type="hidden" name="ssb_created_date" id="ssb_created_date" value="@if($ssbDetails['loanSavingAccount']){{ date("Y-m-d", strtotime(convertDate($ssbDetails['loanSavingAccount']->created_at))) }}@endif">
                            <input type="hidden" name="type" id="type" class="type" value="group">
                            <input type="hidden" name="companyId" class="companyId" id="companyId" value="{{$ssbDetails->company_id}}">
                            <input type="hidden" name="create_application_date" id="create_application_date" class="form-control  create_application_date" readonly >
                            <input type="hidden" name="created_at" class="created_at">
                            <div class="form-group row">
                                <label class="col-form-label col-lg-2">File Charges</label>
                                <div class="col-lg-4">
                                    <input type="text" name="file_charge" id="file_charge" class="form-control" value="{{ $ssbDetails->file_charges }}" readonly="">
                                </div>
                                <label class="col-form-label col-lg-2">Pay File Charge<sup>*</sup></label>
                                <div class="col-lg-4">
                                    <select class="form-control" name="pay_file_charge" id="pay_file_charge">
                                        <!-- <option value="">Select</option> -->
                                        <option value="0" >Loan Amount</option>
                                        <option value="1" selected>Cash</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-form-label col-lg-2">Select Date<sup>*</sup></label>
                                <div class="col-lg-4">
                                    <input type="text" name="date" id="date" class="form-control" readonly="">
                                </div>
                                <label class="col-form-label col-lg-2">Loan Amount</label>
                            <div class="col-lg-4">
                                <input type="text" name="loan_amount" id="loan_amount" class="form-control " style="pointer-events: none;" value="{{$ssbDetails->amount}}" readonly>
                            </div>
                             
                            </div>
                            <div class="form-group row">
                                <label class="col-form-label col-lg-2">Select Tansfer Type<sup>*</sup></label>
                                <div class="col-lg-4">
                                    <select class="form-control" name="payment_mode" id="payment_mode">
                                        <option value="">Select</option>
                                        <option value="0">SSB Account</option>
                                        <option value="1">Bank</option>
                                        <option value="2">Cash</option>
                                    </select>
                                </div>
                                <label class="col-form-label col-lg-2">Approve Date:</label>
                                <div class="col-lg-4">
                                    <input type="text" name="approve_date" id="approve_date" class="form-control " style="pointer-events: none;" value="{{date('d/m/Y',strtotime($ssbDetails->approved_date))}}" readonly>
                                </div>
                            </div>
						<div class="form-group row" 2>
                           <label class="col-form-label col-lg-2 ssb-transfer" style="display: none;">Enter SSB Account</label>
                            <div class="col-lg-4 ssb-transfer" style="display: none;">
                                <input type="hidden" name="ssbaccount" id="ssbaccount" value="@if($ssbDetails['loanSavingAccount2']){{ $ssbDetails['loanSavingAccount2']->account_no }}@endif">
                                <input type="text" name="ssb_account_number" id="ssb_account_number" class="form-control">
                            </div>
                        	<label class="col-form-label col-lg-2 insurance_amount" >Insurance Amount <sup>*</sup></label>
                            <div class="col-lg-4 insurance_amount">
								<input type="hidden" name="member_dob" id="member_dob" value="{{$dob->dob}}">
                                <input type="hidden" name="insurance_amount" id="insurance_amount">
                                <input type="text" name="insurance_amount1" id="insurance_amount1" class="form-control" value="@if(isset($ssbDetails->insurance_charge)){{$ssbDetails->insurance_charge}} @else 0 @endif"  readonly>
                            </div>
                        </div>
                            <!-- <label class="col-form-label col-lg-2 ssb-transfer" style="display: none;">Enter SSB Account</label>
                            <div class="col-lg-4 ssb-transfer" style="display: none;">
                                <input type="hidden" name="ssbaccount" id="ssbaccount" value="{{ $ssbDetails['loanMember']->ssb_account }}">
                                <input type="text" name="ssb_account_number" id="ssb_account_number" class="form-control">
                            </div> -->
                            <div class="form-group row" @if(isset($ssbDetails['loan']->loan_category))@if( $ssbDetails->ecs_type == 0 ) style="display:none;" @endif @endif>
                            <label class="col-form-label col-lg-2">ECS Ref No.</label>
                            <div class="col-lg-4">
                                <input type="text" name="ecs_ref_no" id="ecs_ref_no" class="form-control " style="pointer-events: none;" value="{{ $ecs_ref }}" readonly>
                            </div>
                        
                        
                            <label class="col-form-label col-lg-2">ECS Charge</label>
                            <div class="col-lg-4">
                                <input type="text" name="ecs_amount" id="ecs_amount" class="form-control " style="pointer-events: none;" value="{{ $ssbDetails->ecs_charges }}" readonly>
                            </div>
                        </div>
                        <h6 class="card-title font-weight-semibold other-bank" style="display: none;">Customer Bank details</h4>
                        <div class="form-group row other-bank" style="display: none;">
                            <label class="col-form-label col-lg-2">Bank Name<sup>*</sup></label>
                            <div class="col-lg-4">
                                <input type="text" name="customer_bank_name" class="form-control" id="customer_bank_name" value="{{$ssbDetails->LoanApplicants[0]->bank_name ?? ''}}" readonly>
                            </div>
                            <label class="col-form-label col-lg-2">Bank A/c<sup>*</sup></label>
                            <div class="col-lg-4">
                                <input type="text" name="customer_bank_account_number" class="form-control" id="customer_bank_account_number" value="{{$ssbDetails->LoanApplicants[0]->bank_account_number ??  ''}}" readonly="true">
                            </div>
                        </div>
                        <div class="form-group row other-bank" style="display: none;">
                            <label class="col-form-label col-lg-2">Branch Name<sup>*</sup></label>
                            <div class="col-lg-4">
                                <input type="text" name="customer_branch_name" class="form-control" id="customer_branch_name" value="" readonly>
                            </div>
                            <label class="col-form-label col-lg-2">IFSC code<sup>*</sup></label>
                            <div class="col-lg-4">
                                <input type="text" name="customer_ifsc_code" class="form-control" id="customer_ifsc_code" value="{{$ssbDetails->LoanApplicants[0]->ifsc_code ?? ''}}" readonly>
                            </div>
                        </div>
                        <h6 class="card-title font-weight-semibold other-bank" style="display: none;">Company Bank</h4>
                        <div class="form-group row other-bank" style="display: none;">
                            <label class="col-form-label col-lg-2">Select Bank<sup>*</sup></label>
                            <div class="col-lg-4">
                                <select name="company_bank" id="company_bank" class="form-control">
                                    <option value="">----Please Select----</option>
                                    @foreach( $cBanks as $key => $bank)
                                    @php
                                    // $balance = App\Models\SamraddhBankClosing::where('bank_id',$bank->id )->orderBy('id', 'desc')->first();
                                    $balance = '';
                                    @endphp
                                    @if($bank['bankAccount'])
                                        <option  value="{{ $bank->id }}" data-balance = "{{$balance ? $balance->balance:''}}" data-account="{{ $bank['bankAccount']->account_no }}">{{ $bank->bank_name }}</option>
                                    @endif
                                    @endforeach
                                </select>
                            </div>
                            <label class="col-form-label col-lg-2">Select Bank A/C<sup>*</sup></label>
                            <div class="col-lg-4">
                                <select name="company_bank_account_number" id="company_bank_account_number" class="form-control">
                                <option value="">----Please Select----</option>
                                @foreach($cBanks as $bank)
                                        @if($bank['allBankAccount'])
                                        @foreach($bank['allBankAccount'] as $bankAccount)
                                            <option class="{{ $bank->id }}-bank-account c-bank-account" value="{{ $bankAccount->account_no }}" data-account="{{$bankAccount->id}}"  style="display: none;">
                                        
                                            {{ $bankAccount->account_no }}</option>
                                        @endforeach   
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-group row other-bank" style="display: none;">
                            <label class="col-form-label col-lg-2">Bank Account Balance<sup>*</sup></label>
                            <div class="col-lg-4">
                                <input type="text" name="company_bank_account_balance" id="company_bank_account_balance" class="form-control" readonly="">
                            </div>
                            <label class="col-form-label col-lg-2">Select Mode</label>
                            <div class="col-lg-4">
                                <select name="bank_transfer_mode" id="bank_transfer_mode" class="form-control">
                                    <option value="">----Please Select----</option>
                                    <!-- <option value="0">Cheque</option> -->
                                    <option value="1">Online</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group row other-bank cheque-transaction" style="display: none;">
                            <label class="col-form-label col-lg-2">Cheque<sup>*</sup></label>
                            <div class="col-lg-4">
                                <select name="cheque_id" id="cheque_id" class="form-control">
                                    <option value="">----Please Select----</option>
                                
                                    @foreach($cBanks as $bank)
                                        @if($bank['samraddhBankCheque'])
                                        @foreach($bank['samraddhBankCheque'] as $cheque)
                                        <option value="{{ $cheque->cheque_no }}" class="{{ $cheque->account_id }}-c-cheque c-cheque" style="display: none;">{{ $cheque->cheque_no }}</option>
                                        
                                            
                                        @endforeach   
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                            <label class="col-form-label col-lg-2">Total Amount</label>
                            <div class="col-lg-4">
                                <input type="text" name="cheque_total_amount" id="cheque_total_amount" class="form-control" value="{{ $ssbDetails->amount }}" readonly="">
                            </div>
                        </div>
                        <div class="form-group row other-bank online-transaction" style="display: none;">
                            <label class="col-form-label col-lg-2">UTR number / Transaction Number<sup>*</sup></label>
                            <div class="col-lg-4">
                                <input type="text" name="utr_transaction_number" id="utr_transaction_number" class="form-control">
                            </div>
                            <label class="col-form-label col-lg-2">Amount</label>
                            <div class="col-lg-4">
                                <input type="text" name="online_total_amount" id="online_total_amount" value="{{ $ssbDetails->amount }}" class="form-control" readonly="">
                            </div>
                        </div>
                        <div class="form-group row other-bank online-transaction" style="display: none;">
                            <label class="col-form-label col-lg-2">RTGS/NEFT Charge<sup>*</sup></label>
                            <div class="col-lg-4">
                                <input type="text" name="rtgs_neft_charge" id="rtgs_neft_charge" class="form-control">
                            </div>
                            <label class="col-form-label col-lg-2">Total Online Amount</label>
                            <div class="col-lg-4">
                                <input type="text" name="total_online_amount" id="total_online_amount" class="form-control" readonly="">
                            </div>
                        </div>
                        <div class="col-md-12 d-flex justify-content-end">
                        <table>
                        <tbody>
                            @if($ssbDetails->loan_type != 4)
                            <input type="hidden" name="state_id_ac" id="state_id_ac" value="{{$ssbDetails['loanBranch']->state_id}}">
                             <tr >
                                 <th style="padding-right:15rem;padding-bottom:10px;">File Charge:</th>
                                 <td>{{$ssbDetails->file_charges}}</td>
                             </tr>
                             <tr >
                                 <th style="padding-right:15rem;padding-bottom:10px;" >Insurance Amount:</th>
                                 <td id="ins_amount">@if(isset($ssbDetails->insurance_charge)){{$ssbDetails->insurance_charge}} @else 0 @endif</td>
                             </tr>
                             <tr style="{{($ssbDetails->filecharge_cgst <=  0 ) ? 'display:none':''}}">
                                 <th style="padding-right:15rem;padding-bottom:10px;" >CGST on File Charge @if( isset($headSettingfileChrage->gst_percentage) ){{($headSettingfileChrage->gst_percentage)/2}}@else 0 @endif %:</th>
                                 <td id="cgst_file_charge_amount" data-amount="{{$ssbDetails->filecharge_cgst ?? 0}}">{{$ssbDetails->filecharge_cgst}}</td>
                             </tr>
                             <tr style="{{($ssbDetails->filecharge_sgst <=  0 ) ? 'display:none':''}}">
                                 <th style="padding-right:15rem;padding-bottom:10px;" >SGST on File Charge @if( isset($headSettingfileChrage->gst_percentage) ){{($headSettingfileChrage->gst_percentage)/2}}@else 0 @endif %:</th>
                                 <td id="sgst_file_charge_amount" data-amount="{{$ssbDetails->filecharge_sgst ?? 0}}">{{$ssbDetails->filecharge_sgst}}</td>
                             </tr>
                             <tr style="{{($ssbDetails->filecharge_igst <=  0 ) ? 'display:none':''}}">
                                 <th style="padding-right:15rem;padding-bottom:10px;" >IGST on File Charge @if( isset($headSettingfileChrage->gst_percentage) ){{($headSettingfileChrage->gst_percentage)/2}}@else 0 @endif %:</th>
                                 <td id="igst_file_charge_amount" data-amount="{{$ssbDetails->filecharge_igst ?? 0}}">{{$ssbDetails->filecharge_igst}}</td>
                             </tr>
                             <!-- Insurance Chasrge gst -->
                             <tr style="{{($ssbDetails->insurance_cgst <=  0 ) ? 'display:none':''}}">
                                 <th style="padding-right:15rem;padding-bottom:10px;" >CGST on Insurance @if( isset($headSetting->gst_percentage) ){{($headSetting->gst_percentage)/2}}@else 0 @endif %:</th>
                                 <td id="cgst_amount" data-amount="{{$ssbDetails->insurance_cgst ?? 0}}">{{$ssbDetails->insurance_cgst}}</td>
                             </tr>
                             <tr style="{{($ssbDetails->insurance_sgst <=  0 ) ? 'display:none':''}}">
                                 <th style="padding-right:15rem;padding-bottom:10px;" >SGST on Insurance @if( isset($headSetting->gst_percentage) ){{($headSetting->gst_percentage)/2}}@else 0 @endif %:</th>
                                 <td id="sgst_amount" data-amount="{{$ssbDetails->insurance_sgst ?? 0 }}">{{$ssbDetails->insurance_sgst}}</td>
                             </tr>
                             <tr style="{{($ssbDetails->insurance_charge_igst <=  0 ) ? 'display:none':''}}">
                                 <th style="padding-right:15rem;padding-bottom:10px;" >IGST on Insurance @if( isset($headSetting->gst_percentage) ){{($headSetting->gst_percentage)/2}}@else 0 @endif %:</th>
                                 <td id="igst_amount" data-amount="{{$ssbDetails->insurance_charge_igst ?? 0}}">{{$ssbDetails->insurance_charge_igst}}</td>
                             </tr>

                             <tr >
                                 <th style="padding-right:15rem;padding-bottom:10px;" >ECS Amount:</th>
                                 <td id="ecs_charge" data-amount="{{$ssbDetails->ecs_charges ?? 0}}">@if(isset($ssbDetails->ecs_charges)){{$ssbDetails->ecs_charges}} @else 0 @endif</td>
                             </tr>


                             <tr style="{{($ssbDetails->ecs_charge_igst <=  0 ) ? 'display:none':''}}">
                                 <th style="padding-right:15rem;padding-bottom:10px;" >IGST on ECS charge @if( isset($headSettingEcsChrage->gst_percentage) ){{($headSettingEcsChrage->gst_percentage)/2}}@else 0 @endif %:</th>
                                 <td id="ecs_charge_igst" data-amount="{{$ssbDetails->ecs_charge_igst ?? 0}}">{{$ssbDetails->ecs_charge_igst}}</td>
                             </tr>

                             <tr style="{{($ssbDetails->ecs_charge_sgst <=  0 ) ? 'display:none':''}}">
                                 <th style="padding-right:15rem;padding-bottom:10px;" >SGST on ECS charge @if( isset($headSettingEcsChrage->gst_percentage) ){{($headSettingEcsChrage->gst_percentage)/2}}@else 0 @endif %:</th>
                                 <td id="ecs_charge_sgst" data-amount="{{$ssbDetails->ecs_charge_sgst ?? 0}}">{{$ssbDetails->ecs_charge_sgst}}</td>
                             </tr>

                             <tr style="{{($ssbDetails->ecs_charge_cgst <=  0 ) ? 'display:none':''}}">
                                 <th style="padding-right:15rem;padding-bottom:10px;" >CGST on ECS charge @if( isset($headSettingEcsChrage->gst_percentage) ){{($headSettingEcsChrage->gst_percentage)/2}}@else 0 @endif %:</th>
                                 <td id="ecs_charge_cgst" data-amount="{{$ssbDetails->ecs_charge_cgst ?? 0}}">{{$ssbDetails->ecs_charge_cgst}}</td>
                             </tr>
                             @endif
                             <tr >
                                 <th style="padding-right:15rem;padding-bottom:10px;">Transfer Amount:</th>
                                 <td id="transfer_amount" data-amount = "{{$ssbDetails->amount}}">{{$ssbDetails->amount}}</td>
                             </tr>
                         </tbody>
                         </table>
                        </div>
                        <input type="hidden" id="hiddenTransferAmount" name="hiddenTransferAmount" value="">
                            <div class="text-right">
                                <button type="submit" class="btn bg-dark m-2" id="submit">Submit<i class="icon-paperplane ml-2"></i></button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@include('templates.admin.loan.partials.loanTransferScript')
@stop