@extends('templates.admin.master')



@section('content')

    <div class="content">

        <div class="row">

            <div class="col-md-12">

                <div class="card">

                    <div class="card-header header-elements-inline">

                        <h6 class="card-title font-weight-semibold">Transfer Emergency Maturity</h6>

                    </div>

                    <div class="card-body">

                    {{Form::open(['url'=>route('admin.damandadvice.approvepayments'),'method'=>'post','name'=>'transferr_demand_advice_amount','id'=>'transferr_demand_advice_amount'])}}


                            <input type="hidden" name="created_at" class="created_at">
                            <input type="hidden" name="create_application_date" class="create_application_date">

                            <input type="hidden" name="demandAdviceIds" id="demandAdviceIds" value="{{ $selectedRecords }}">

                            <input type="hidden" name="company_id" id="company_id" value="{{ $demandAdvice[0]->company_id }}">



                            <input type="hidden" name="selected_fresh_expense_records" id="selected_fresh_expense_records">

                            <input type="hidden" name="pending_fresh_expense_records" id="pending_fresh_expense_records">



                            <input type="hidden" name="type" id="type" value="{{ $type }}">

                            <input type="hidden" name="subtype" id="subtype" value="{{ $subType }}">



                            @php



                                $dArray = array('');



                                foreach($demandAdvice as $key => $row){



                                    array_push($dArray,$row->date);



                                }



                            @endphp

                            

                            <input type="hidden" name="mdate" id="mdate" value="{{ max($dArray) }}">



                            <div class="row">

                                <div class="col-md-4">

                                    <div class="form-group row">

                                        <label class="col-form-label col-lg-12">Payment Date<sup>*</sup></label>

                                        <div class="col-lg-12 error-msg">

                                            

                                                <input class="form-control" type="text" name="payment_date" id="payment_date" autocomplete="off" readonly>

                                          

                                        </div>

                                    </div>

                                </div>



                                <div class="col-md-4">

                                    <div class="form-group row">

                                        <label class="col-form-label col-lg-12">Section Amount Mode<sup>*</sup></label>

                                        <div class="col-lg-12 error-msg">

                                            <div class="">

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

                                                @if(Auth::user()->branch_id>0) 

                                               <select class="form-control paymemt-input" id="branch_id" name="branch_id">

                                                    <option value="">----Please Select----</option>

                                                    @foreach( $branch as $key => $val )

<option value="{{ $val->branch->id}}"  >{{ $val->branch->name }}</option> 

@endforeach 

                                                </select>

                                               

                                                 @else 

                                                 <select class="form-control paymemt-input" id="branch_id" name="branch_id">

                                                    <option value="">----Please Select----</option>

                                                    @foreach( $branch as $key => $val )

                                                    <option value="{{ $val->branch->id}}"  >{{ $val->branch->name }}</option> 

                                                    @endforeach 

                                                </select>

                                                 @endif 

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

                                                    <option value="0">Cash</option>

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

                                                        $balance = 0;

                                                        

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

                                            <button type="submit" class=" btn bg-dark legitRipple submit-transfer-button" >Submit</button>

                                        </div>

                                    </div>

                                </div>

                            </div>

                        {{Form::close()}}

                    </div>

                </div>

            </div>

            <div class="col-md-12">

                <div class="card">

                    <div class="card-header header-elements-inline">

                        <h6 class="card-title font-weight-semibold">Emergency Maturity Application</h6>

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

                                <th width="5%">TDS Amount</th>

                                <th width="5%">Maturity Amount Payable</th>

                                <th width="5%">Payable Amount</th>

                                <th width="5%">Mobile Number</th>

                                <th width="5%">SSB Account</th>

                                <th width="5%">Bank Name</th>

                                <th width="5%">Bank A/C No.</th>

                                <th width="5%">IFSC</th>

                                <th width="5%">Payment Date</th>

                            </tr>

                        </thead>

                        <tbody>

                            @foreach($demandAdvice as $key => $row)

                            <tr>

                                <td>{{ $key+1 }}</td> 

                                <td><input type="checkbox" name="fresh_expense_record" value="{{ $row->id }}" data-company-id = "{{$row->company_id}}"  data-amount="{{ $row->final_amount }}"  data-branch = "{{$row['branch']->id}}" id="fresh_expense_record"></td>

                                <td>{{ date("d/m/Y", strtotime($row['investment']->created_at)) }}</td> 

                                @php
                               
                                $pName = App\Models\Plans::where('id',$row['investment']->plan_id)->first('name');

                                $plan_name = $pName->name;

                                $account_holder_name =  $row['investment']['member']->first_name . ' ' . $row['investment']['member']->last_name;

                                @endphp

                                <td>{{ $pName->name }}</td> 

                                <td>{{ $row['investment']->tenure }}</td> 

                                <td>{{ $account_holder_name }}</td> 
                                <!-- <td>{{ $row['investment']->current_balance }} &#8377</td>  -->
                                <td>{{ $row['investment'] ? $row->balance : '0'}} &#8377</td>
                                <td>{{ number_format((float)(($row->maturity_amount_till_date)), 2, '.', '') }} &#8377</td> 

                                @if($row->tds_amount)
                                    <td>{{ number_format((float)(($row->tds_amount)), 2, '.', '') }} &#8377</td>
                                @else
                                    <td>N/A</td>
                                @endif

                                <td>{{ number_format((float)(($row->maturity_amount_payable+$row->tds_amount)), 2, '.', '') }} &#8377</td>

                                @if($row->final_amount)
                                    <td>{{  number_format((float)(($row->final_amount)), 2, '.', '') }} &#8377</td>
                                @elseif($row->maturity_amount_payable)
                                    <td>{{  number_format((float)(($row->maturity_amount_payable-$row->tds_amount)), 2, '.', '') }} &#8377</td>
                                @else
                                    <td>N/A</td>
                                @endif

                                @php

                                    if(isset($row['investment']->member)){

                                        $mNumber_record =  $row['investment']->member->mobile_no;

                                    }else{

                                        $mNumber_record = 'N/A';

                                    }


                                  
                                    if(isset( $row['investment']['ssb']->account_no)){

                                        $ssbaccount_no_record =  $row['investment']['ssb']->account_no;

                                    }else{

                                        $ssbaccount_no_record = 'N/A';

                                    }
                                  


                                    if(($row['investment']['member'])){

                                        $bank_name = $row['investment']['member'];

                                        if(count($bank_name['memberBankDetails']) > 0){

                                            $bank_name_record = $bank_name['memberBankDetails'][0]->bank_name;

                                        }else{

                                            $bank_name_record = 'N/A';

                                        }

                                    }else{

                                        $bank_name_record = 'N/A';

                                    }



                                    if(($row['investment']['member'])){

                                        $account_no = ($row['investment']['member']);

                                        if(count($account_no['memberBankDetails']) > 0){

                                            $account_no_record = $account_no['memberBankDetails'][0]->account_no;

                                        }else{

                                            $account_no_record = 'N/A';

                                        }

                                    }else{

                                        $account_no_record = 'N/A';

                                    }



                                    if(($row['investment']['member'])){

                                        $ifsc_code = ($row['investment']['member']);

                                        if(count($ifsc_code['memberBankDetails']) > 0){

                                            $ifsc_code_record = $ifsc_code['memberBankDetails'][0]->ifsc_code;

                                        }else{

                                            $ifsc_code_record = 'N/A';

                                        }

                                    }else{

                                        $ifsc_code_record = 'N/A';

                                    }

                                @endphp

                                <td>{{ $mNumber_record }}</td> 

                                <td>{{ $ssbaccount_no_record }}</td> 

                                <td>@if($bank_name_record != 'N/A'){{ $bank_name_record }}@else {{$row->bank_name}}@endif </td> 



                                <td>@if($account_no_record != 'N/A'){{ $account_no_record }}@else {{$row->bank_account_number}}@endif</td> 



                                <td>@if($ifsc_code_record != 'N/A'){{ $ifsc_code_record }}@else {{$row->bank_ifsc}}@endif </td>  

                                <td>{{ date("d/m/Y", strtotime( $row->date)) }}</td> 

                            </tr>

                            @endforeach

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

    </div>

@stop



@section('script')

<script src="{{url('/')}}/asset/js/sweetalert.min.js"></script>

@include('templates.admin.demand-advice.partials.script')

@endsection

