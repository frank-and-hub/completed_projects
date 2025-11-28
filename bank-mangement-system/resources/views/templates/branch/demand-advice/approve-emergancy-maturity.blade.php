@extends('layouts/branch.dashboard')

@section('content')
<div class="loader" style="display: none;"></div>
<div class="container-fluid mt--6">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header header-elements-inline">
                        <h3 class="card-title font-weight-semibold">Approve Emeregancy Maturity</h3>
                    </div>
                    <div class="card-body">
                        <form action="{{route('branch.damandadvice.approvepayments')}}" method="post" id="transferr_demand_advice_amount" name="transferr_demand_advice_amount">
                            @csrf
                            <input type="hidden" name="created_at" class="created_at">
                            <input type="hidden" name="demandAdviceIds" id="demandAdviceIds" value="{{ $selectedRecords }}">

                            <input type="hidden" name="selected_fresh_expense_records" id="selected_fresh_expense_records">
                            <input type="hidden" name="pending_fresh_expense_records" id="pending_fresh_expense_records">

                            <input type="hidden" name="type" id="type" value="{{ $type }}">
                            <input type="hidden" name="subtype" id="subtype" value="{{ $subType }}">

                            <div class="row">
                               <div class="col-md-4">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Section Amount Mode<sup>*</sup></label>
                                        <div class="col-lg-12 error-msg">
                                            <div class="input-group">
                                                <select class="form-control" id="amount_mode" name="amount_mode">
                                                    <option value="">---- Please Select ----</option>
                                                    <option value="0" data-val="cash-mode">Cash</option>
                                                    <option value="1" data-val="ssb-mode">SSB</option>
                                                    <option value="2" data-val="bank-mode">Bank</option> 
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4 cash-mode" style="display: none;">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Select Branch <sup>*</sup></label>
                                        <div class="col-lg-12 error-msg">
                                            <div class="input-group">
                                                <select class="form-control paymemt-input" id="branch_id" name="branch_id">
                                                    <option value="">----Please Select----</option>
                                                    @foreach( App\Models\Branch::pluck('name', 'id') as $key => $val )
                                                    <option value="{{ $key }}"  >{{ $val }}</option> 
                                                    @endforeach 
                                                </select>
                                               </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4 cash-mode" style="display: none;">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Select Cash <sup>*</sup></label>
                                        <div class="col-lg-12 error-msg">
                                            <div class="input-group">
                                                <select class="form-control paymemt-input" id="cash_type" name="cash_type">
                                                    <option value="">---- Please Select ----</option>
                                                    <option value="0">Micro</option>
                                                    <!-- <option value="1">Loan</option> -->
                                                </select>
                                               </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4 cash-mode" style="display: none;">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Cash In Hand Balance <sup>*</sup></label>
                                        <div class="col-lg-12 error-msg">
                                            <div class="input-group">
                                                <input type="text" name="cash_in_hand_balance" id="cash_in_hand_balance" class="form-control paymemt-input" readonly="">
                                               </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4 bank-mode" style="display: none;">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Select Bank <sup>*</sup></label>
                                        <div class="col-lg-12 error-msg">
                                            <div class="input-group">
                                                <select class="form-control paymemt-input" id="bank" name="bank">
                                                    <option value="">----Please Select----</option>
                                                    @foreach( $cBanks as $key => $bank)
                                                        @php
                                                        $balance = App\Models\SamraddhBankClosing::where('bank_id',$bank->id )->orderBy('id', 'desc')->first();
                                                        
                                                    @endphp
                                                        @if($bank['bankAccount'])
                                                          
                                                            <option  value="{{ $bank->id }}" data-balance = "{{$balance ? $balance->balance:''}}" data-account="{{ $bank['bankAccount']->account_no }}">{{ $bank->bank_name }}</option>
                                                        @endif
                                                    @endforeach
                                                </select>
                                               </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4 bank-mode" style="display: none;">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Bank Account Number<sup>*</sup></label>
                                        <div class="col-lg-12 error-msg">
                                            <div class="input-group">
                                                <select class="form-control paymemt-input" id="bank_account_number" name="bank_account_number">
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
                                </div>

                                <div class="col-md-4 bank-mode" style="display: none;">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Available Balance<sup>*</sup></label>
                                        <div class="col-lg-12 error-msg">
                                            <div class="input-group">
                                                <input type="text" name="available_balance" id="available_balance" class="form-control paymemt-input" readonly="">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4 bank-mode" style="display: none;">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Select Mode <sup>*</sup></label>
                                        <div class="col-lg-12 error-msg">
                                            <div class="input-group">
                                                <select class="form-control paymemt-input" id="mode" name="mode">
                                                    <option value=""  >----Select----</option> 
                                                    <option value="3"  >Cheque</option> 
                                                    <option value="4"  >Online</option> 
                                                </select>
                                               </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4 cheque-mode" style="display: none;">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Cheque Number<sup>*</sup></label>
                                        <div class="col-lg-12 error-msg">
                                            <div class="input-group">
                                                <select class="form-control paymemt-input" id="cheque_number" name="cheque_number">
                                                    <option value="">----Please Select----</option>
                                                    @foreach($cheques as $val)
                                                        <option value="{{ $val->cheque_no }}" class="{{ $val->account_id }}-c-cheque c-cheque" style="display: none;">{{ $val->cheque_no }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4 online-mode" style="display: none;">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">UTR number / Transaction Number<sup>*</sup></label>
                                        <div class="col-lg-12 error-msg">
                                            <div class="input-group">
                                                <input type="text" name="utr_number" id="utr_number" class="form-control paymemt-input" >
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4 common-mode" style="display: none;">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Amount<sup>*</sup></label>
                                        <div class="col-lg-12 error-msg">
                                            <div class="input-group">
                                                <input type="text" name="amount" id="amount" class="form-control" value="" readonly="">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4 online-mode" style="display: none;">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">RTGS/NEFT Charge <sup>*</sup></label>
                                        <div class="col-lg-12 error-msg">
                                            <div class="input-group">
                                                <input type="text" name="neft_charge" id="neft_charge" class="form-control paymemt-input">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4 online-mode" style="display: none;">
                                    <div class="form-group row">
                                        <label class="col-form-label col-lg-12">Total amount  <sup>*</sup></label>
                                        <div class="col-lg-12 error-msg">
                                            <div class="input-group">
                                                <input type="text" name="total_amount" id="total_amount" class="form-control paymemt-input" readonly="">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <div class="form-group row"> 
                                        <div class="col-lg-12 text-right" >
                                            <button type="submit" class=" btn btn-primary legitRipple submit-transfer-button" >Submit</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header header-elements-inline">
                        <h3 class="card-title font-weight-semibold">Emeregancy Maturity Application</h3>
                    </div>
                    <table class="table datatable-show-all" id="fresh-expense-approve">
                        <thead>
                            <tr>
                                <th width="5%">S/N</th>
                                <th width="5%"><input type="checkbox" name="select_all_fresh_expense" id="select_all_fresh_expense"></th>
                                <th width="10%">Opening Date</th>
                                <th width="10%">Plan Name</th>
                                <th width="5%">Tenure</th>
                                <th width="5%">Account holder name</th>
                                <th width="5%">Deposit Amount</th>
                                <th width="5%">Maturity Amount Till Date</th>
                                <th width="5%">Maturity Amount Payable</th>
                                <th width="5%">Payment Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($demandAdvice as $key => $row)
                            <tr>
                                <td>{{ $key+1 }}</td> 
                                <td><input type="checkbox" name="fresh_expense_record" value="{{ $row->id }}" data-amount="{{ $row->maturity_amount_payable }}" id="fresh_expense_record"></td>
                                <td>{{ date("d/m/Y", strtotime($row['investment']->created_date)) }}</td> 
                                @php
                                $pName = App\Models\Plans::where('id',$row['investment']->plan_id)->first('name');
                                $plan_name = $pName->name;
                                $account_holder_name = getMemberData($row['investment']->member_id)->first_name.' '.getMemberData($row['investment']->member_id)->last_name;
                                @endphp
                                <td>{{ $pName->name }}</td> 
                                <td>{{ $row['investment']->tenure }}</td> 
                                <td>{{ $account_holder_name }}</td> 
                                <td>{{ $row['investment']->deposite_amount }}</td> 
                                <td>{{ $row->maturity_prematurity_amount }}</td> 
                                <td>{{ $row->maturity_amount_payable }}</td>
                                <td>{{ date("d/m/Y", strtotime( $row->date)) }}</td> 
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('script')
<script src="{{url('/')}}/asset/js/sweetalert.min.js"></script>
@include('templates.branch.demand-advice.partials.script')
@stop
