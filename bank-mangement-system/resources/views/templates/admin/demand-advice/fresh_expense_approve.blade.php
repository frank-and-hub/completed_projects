@extends('templates.admin.master')



@section('content')
<style type="text/css">
    .is-assets{
        width: 100%;
    }

</style>
    <div class="content">

        <div class="row">

            <div class="col-md-12">

                <div class="card">

                    <div class="card-header header-elements-inline">

                        <h6 class="card-title font-weight-semibold">Approve Demand Advice</h6>

                    </div>

                    <div class="card-body">

                        <form action="{{route('admin.damandadvice.approvepayments')}}" method="post" id="transferr_demand_advice_amount" name="transferr_demand_advice_amount">

                            @csrf

                            <input type="hidden" name="created_at" class="created_at">

                            <input type="hidden" name="demandAdviceIds" id="demandAdviceIds" value="{{ $selectedRecords }}">



                            <input type="hidden" name="selected_fresh_expense_records" id="selected_fresh_expense_records">

                            <input type="hidden" name="pending_fresh_expense_records" id="pending_fresh_expense_records">

                            <input type="hidden" name="assestcategory" id="assestcategory">

                            <input type="hidden" name="assets_subcategory" id="assest_sub_category">

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

                                                <input class="form-control" type="text" name="payment_date" id="payment_date">

                                        </div>

                                    </div>

                                </div>



                               

									<!--
									<div class="col-md-4 is-assets" style="display: none;">
										<div class="form-group row">
											<label class="col-form-label col-lg-12">Asset Sub Categories<sup>*</sup></label>
											<div class="col-lg-12 error-msg">
												<select class="form-control" id="assets_subcategory" name="assets_subcategory">
													<option value="">---- Please Select ----</option>
													@foreach($assets_subcategory as $key => $val)
														<option class="{{ $val->parent_id }}-parent-id parent-id" value="{{ $val->head_id }}" style="display: none;">{{ $val->sub_head }}</option>
													@endforeach
												</select>
											</div>
										</div>
									</div> -->
									

                                <div class="col-md-4">

                                    <div class="form-group row">

                                        <label class="col-form-label col-lg-12">Section Amount Mode<sup>*</sup></label>

                                        <div class="col-lg-12 error-msg">

                                            

                                                <select class="form-control" id="amount_mode" name="amount_mode">

                                                    <option value="">---- Please Select ----</option>

                                                    <option value="0" data-val="cash-mode">Cash</option>

                                                    <!-- <option value="1" data-val="ssb-mode">SSB</option> -->

                                                    <option value="2" data-val="bank-mode">Bank</option> 

                                                </select>

                                          

                                        </div>

                                    </div>

                                </div>



                                <div class="col-md-4 cash-mode" style="display: none;">

                                    <div class="form-group row">

                                        <label class="col-form-label col-lg-12">Select Branch <sup>*</sup></label>

                                        <div class="col-lg-12 error-msg">

                                            

                                                @if(Auth::user()->branch_id>0) 

                                               <select class="form-control paymemt-input" id="branch_id" name="branch_id">

                                                    <option value="">----Please Select----</option>

                                                    @foreach( App\Models\Branch::where('id','=',Auth::user()->branch_id)->pluck('name', 'id') as $key => $val )

                                                    <option value="{{ $key }}"  >{{ $val }}</option> 

                                                    @endforeach 

                                                </select>

                                               

                                                 @else 

                                                 <select class="form-control paymemt-input" id="branch_id" name="branch_id">

                                                    <option value="">----Please Select----</option>

                                                    @foreach( App\Models\Branch::pluck('name', 'id') as $key => $val )

                                                    <option value="{{ $key }}"  >{{ $val }}</option> 

                                                    @endforeach 

                                                </select>

                                                 @endif 

                                            

                                        </div>

                                    </div>

                                </div>



                                <div class="col-md-4 cash-mode" style="display: none;">

                                    <div class="form-group row">

                                        <label class="col-form-label col-lg-12">Select Cash <sup>*</sup></label>

                                        <div class="col-lg-12 error-msg">

                                           

                                                <select class="form-control paymemt-input" id="cash_type" name="cash_type">

                                                    <option value="">---- Please Select ----</option>

                                                    <option value="0">Cash</option>

                                                    <!-- <option value="1">Loan</option> -->

                                                </select>

                                            

                                        </div>

                                    </div>

                                </div>



                                <div class="col-md-4 cash-mode" style="display: none;">

                                    <div class="form-group row">

                                        <label class="col-form-label col-lg-12">Cash In Hand Balance <sup>*</sup></label>

                                        <div class="col-lg-12 error-msg">

                                           

                                                <input type="text" name="cash_in_hand_balance" id="cash_in_hand_balance" class="form-control paymemt-input" readonly="">

                                             

                                        </div>

                                    </div>

                                </div>



                                <div class="col-md-4 bank-mode" style="display: none;">

                                    <div class="form-group row">

                                        <label class="col-form-label col-lg-12">Select Bank <sup>*</sup></label>

                                        <div class="col-lg-12 error-msg">

                                           

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



                                <div class="col-md-4 bank-mode" style="display: none;">

                                    <div class="form-group row">

                                        <label class="col-form-label col-lg-12">Bank Account Number<sup>*</sup></label>

                                        <div class="col-lg-12 error-msg">

                                           

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



                                <div class="col-md-4 bank-mode" style="display: none;">

                                    <div class="form-group row">

                                        <label class="col-form-label col-lg-12">Available Balance<sup>*</sup></label>

                                        <div class="col-lg-12 error-msg">

                                           

                                                <input type="text" name="available_balance" id="available_balance" class="form-control paymemt-input" readonly="">

                                           

                                        </div>

                                    </div>

                                </div>



                                <div class="col-md-4 bank-mode" style="display: none;">

                                    <div class="form-group row">

                                        <label class="col-form-label col-lg-12">Select Mode <sup>*</sup></label>

                                        <div class="col-lg-12 error-msg">

                                           

                                                <select class="form-control paymemt-input" id="mode" name="mode">

                                                    <option value=""  >----Select----</option> 

                                                    <option value="3"  >Cheque</option> 

                                                    <option value="4"  >Online</option> 

                                                </select>

                                             

                                        </div>

                                    </div>

                                </div>



                                <div class="col-md-4 cheque-mode" style="display: none;">

                                    <div class="form-group row">

                                        <label class="col-form-label col-lg-12">Cheque Number<sup>*</sup></label>

                                        <div class="col-lg-12 error-msg">

                                           

                                                <select class="form-control paymemt-input" id="cheque_number" name="cheque_number">

                                                    <option value="">----Please Select----</option>

                                                    @foreach($cheques as $val)

                                                        <option value="{{ $val->cheque_no }}" class="{{ $val->account_id }}-c-cheque c-cheque" style="display: none;">{{ $val->cheque_no }}</option>

                                                    @endforeach

                                                </select>

                                           

                                        </div>

                                    </div>

                                </div>



                                <div class="col-md-4 online-mode" style="display: none;">

                                    <div class="form-group row">

                                        <label class="col-form-label col-lg-12">UTR number / Transaction Number<sup>*</sup></label>

                                        <div class="col-lg-12 error-msg">

                                           

                                                <input type="text" name="utr_number" id="utr_number" class="form-control paymemt-input" >

                                           

                                        </div>

                                    </div>

                                </div>



                                <div class="col-md-4 common-mode" style="display: none;">

                                    <div class="form-group row">

                                        <label class="col-form-label col-lg-12">Amount<sup>*</sup></label>

                                        <div class="col-lg-12 error-msg">

                                           

                                                <input type="text" name="amount" id="amount" class="form-control" value="" readonly="">

                                           

                                        </div>

                                    </div>

                                </div>



                                <div class="col-md-4 online-mode" style="display: none;">

                                    <div class="form-group row">

                                        <label class="col-form-label col-lg-12">RTGS/NEFT Charge <sup>*</sup></label>

                                        <div class="col-lg-12 error-msg">

                                          

                                                <input type="text" name="neft_charge" id="neft_charge" class="form-control paymemt-input">

                                           

                                        </div>

                                    </div>

                                </div>



                                <div class="col-md-4 online-mode" style="display: none;">

                                    <div class="form-group row">

                                        <label class="col-form-label col-lg-12">Total amount  <sup>*</sup></label>

                                        <div class="col-lg-12 error-msg">

                                           

                                                <input type="text" name="total_amount" id="total_amount" class="form-control paymemt-input" readonly="">

                                           

                                        </div>

                                    </div>

                                </div>
                                <div class="col-md-4">

                                    <div class="form-group row">

                                        <label class="col-form-label col-lg-12">Assets<sup>*</sup></label>

                                        <div class="col-lg-12 error-msg">

                                                <select class="form-control" id="is_assests" name="is_assests">

                                                    <option value="">---- Please Select ----</option>

                                                    <option value="0">Yes</option>

                                                    <option value="1">No</option>

                                                </select>

                                        </div>

                                    </div>

                                </div>

                                    <div class="col-md-4 is-assets MainHead0">
                                        <div class="form-group row">
                                            <label class="col-form-label col-lg-12">Asset Categories<sup>*</sup></label>
                                            <div class="col-lg-12 error-msg">
                                                <select class="form-control assets_category" id="assets_category" name="assets_category" data-row-id="0">
                                                    <option value="">---- Please Select ----</option>
                                                    @foreach($assets_category as $key => $val)
                                                        <option value="{{ $val->head_id }}" >{{ $val->sub_head }}</option>
                                                    @endforeach
                                                </select>
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

                        </form>

                    </div>

                </div>

            </div>

            <div class="col-md-12">

                <div class="card">

                    <div class="card-header header-elements-inline">

                        <h6 class="card-title font-weight-semibold">Demand Advice Application</h6>

                    </div>

                    <table class="table datatable-show-all" id="fresh-expense-approve">

                        <thead>

                            <tr>

                                <th width="5%">S/N</th>

                                <th width="5%"><input type="checkbox" name="select_all_fresh_expense" id="select_all_fresh_expense"></th>

                                <th width="10%">BR Name</th>

                                <th width="10%">BR Code</th>

                                <th width="10%">SO Code</th>

                                <th width="10%">RO Code</th>

                                <th width="10%">ZO Code</th>

                                <th width="5%">Date</th>

                                <th width="5%">Voucher No</th>

                                <th width="5%">Advice Type</th>

								<th width="5%">Expense Type</th>

								<th width="5%">Total Amount</th>

                                <!-- <th width="5%">Advice No</th>

                                <th width="10%">Owner Name</th>

                                <th width="10%">Particular</th>

                                <th width="10%">Mobile</th> -->

                                <th width="10%">Status</th>

                            </tr>

                        </thead>

                        <tbody>

                            @foreach($demandAdvice as $key => $row)

                                @php

                                    $amount = App\Models\DemandAdviceExpense::where('demand_advice_id',$row->id)->sum('amount');

                                @endphp

                            <tr>

                                <td>{{ $key+1 }}</td> 

                                <td><input type="checkbox" name="fresh_expense_record" value="{{ $row->id }}" data-amount="{{ $amount }}" id="fresh_expense_record"></td>

                                <td>{{ $row['branch']->name }}</td> 

                                <td>{{ $row['branch']->branch_code }}</td> 

                                <td>{{ $row['branch']->sector }}</td> 

                                <td>{{ $row['branch']->regan }}</td> 

                                <td>{{ $row['branch']->zone }}</td> 

                                <td>{{ date("d/m/Y", strtotime( $row->date)) }}</td> 

                                <td>{{ $row->voucher_number }}</td>

                                <td>@if($row->payment_type == 0)

											Expenses

									@elseif($row->payment_type == 1)

										Maturity

									@elseif($row->payment_type == 2)

										Prematurity

									@elseif($row->payment_type == 3)

									@if($row->sub_payment_type == '4')

										Death Help

									@elseif($row->sub_payment_type == '5'){

									Death Claim

									@endif

									@endif

								</td>

                                 <td> @if($row->sub_payment_type == '0')

									 Fresh Exprense

									@elseif($row->sub_payment_type == '1')

											TA Advanced

									@elseif($row->sub_payment_type == '2')

										Advanced salary

									@elseif($row->sub_payment_type == '3')

											Advanced Rent

									@elseif($row->sub_payment_type == '4')

										N/A



									@elseif($row->sub_payment_type == '5')

										N/A

									@else

										N/A

									

									@endif

									</td> 

									<td>{{$row['expenses']->sum('amount')}}  &#8377;</td>

                                <!-- <td></td> 

                                <td>{{ $row->owner_name }}</td> 

                                <td>{{ $row->particular }}</td> 

                                <td>{{ $row->mobile_number }}</td>  -->

                                @if($row->status == 0)

                                    <td>Pending</td> 

                                @else

                                    <td>Approved</td>      

                                @endif

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

