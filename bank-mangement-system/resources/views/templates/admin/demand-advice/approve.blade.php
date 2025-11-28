@extends('templates.admin.master')


@section('content')
    <div class="content">

        <div class="row">

            <div class="col-md-12">

                <div class="card">

                    <div class="card-header header-elements-inline">

                        <h6 class="card-title font-weight-semibold">Approve Demand Advice</h6>
                        <input type="hidden" id="highestDate" value="{{$highestDate}}">
                    </div>

                    <div class="card-body">

                        <form action="{{route('admin.damandadvice.approvepayments')}}" method="post" id="transferr_demand_advice_amount" name="transferr_demand_advice_amount">

                            @csrf

                            <input type="hidden" name="created_at" class="created_at">

                            <input type="hidden" name="demandAdviceIds" id="demandAdviceIds" value="{{ $selectedRecords }}">

                            <input type="hidden" class="create_application_date" name="create_application_date" id="create_application_date">


                            <input type="hidden" name="company_id" id="company_id" value="{{$demandAdvice[0]->company_id}}">
                            
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

                                           

                                                <input class="form-control" type="text" name="payment_date" id="payment_date" autocomplete="off">

                                            

                                        </div>

                                    </div>

                                </div>



                                @if($type == 3)

                                    <div class="col-md-4">

                                        <div class="form-group row">

                                            <label class="col-form-label col-lg-12">Section Amount Mode<sup>*</sup></label>

                                            <div class="col-lg-12 error-msg">

                                                <div class="input-group">

                                                    <select class="form-control" id="amount_mode" name="amount_mode">

                                                        <option value="">---- Please Select ----</option>

                                                        <option value="0" data-val="cash-mode">Cash</option>

                                                        <option value="2" data-val="bank-mode">Bank</option> 

                                                        <option value="1" data-val="ssb-mode">SSB</option>

                                                    </select>

                                                </div>

                                            </div>

                                        </div>

                                    </div>

                                @else

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

                                @endif
                             

                                 <!---  SSb Start !------->
                                 @if($type == 3)
                                <div class="col-md-4 ssb-mode" style="display: none;">

                                    <div class="form-group row">

                                        <label class="col-form-label col-lg-12">SSB Account Number<sup>*</sup></label>

                                        <div class="col-lg-12 error-msg">

                                            <div class="input-group">
                                                <input type="text" class="form-control" name = "nominee_ac_number" id= "nominee_ac_number" value="{{$ssb}}" readonly>
                                            </div>

                                        </div>

                                    </div>

                                </div>
                                
                                @endif


                                <div class="col-md-4 cash-mode" style="display: none;">

                                    <div class="form-group row">

                                        <label class="col-form-label col-lg-12">Select Branch <sup>*</sup></label>

                                        <div class="col-lg-12 error-msg">

                                            <div class="input-group">

                                             

                                               

                                               

                                                 <select class="form-control paymemt-input" id="branch_id" name="branch_id">

                                                    <option value="">----Please Select----</option>
                                                    @php    
                                                       $branches =\App\Models\CompanyBranch::where('company_id',$demandAdvice[0]['company_id'])->get();
                                                      
                                                    @endphp
                                                    @foreach($branches  as $key => $val )
                                                  
                                                    <option value="{{ $val->branch_id }}"  >{{ $val->branch->name }}</option> 

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

                                           

                                                <input type="text" name="utr_number" id="utr_number" class="form-control paymemt-input" >

                                         

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

                                <th>S/N</th>

                                <th class="d-none"><input type="checkbox" name="select_all_fresh_expense" id="select_all_fresh_expense"></th>

                                <th>BR Name</th>

                                <!-- <th>BR Code</th> -->

                                <!-- <th>SO Code</th> -->

                                <!-- <th>RO Code</th> -->

                                <!-- <th>ZO Code</th> -->

                                <th>Date</th>

                                <th>Voucher No</th>

                                <th>Account Number</th>

								<th>Name</th>

								<th>Total Amount</th>

                                <th>TDS Amount</th> 
                                
                                <th>Interest</th>

								<th>Payable Amount</th>

                                <th>Final Amount</th>

                                <!-- <th>Advice No</th>

                                <th>Owner Name</th>

                                <th>Particular</th>

                                <th>Mobile</th> -->

                                <th>Status</th>

                            </tr>

                        </thead>

                        <tbody>

                            @foreach($demandAdvice as $key => $row)

                            <?php
                                //print_r($row);die;
                            ?>

                            <tr>

                                <td>{{ $key+1 }}</td> 

                                <td class="d-none" ><input type="checkbox" name="fresh_expense_record" value="{{ $row->id }}" @if($row->final_amount) @if($row->tds_amount >0)data-amount="{{ $row->final_amount }}" @else data-amount="{{ $row->final_amount}}" @endif @elseif($row->maturity_amount_payable) data-amount="{{ $row->maturity_amount_payable }}" @elseif($row->advanced_amount) data-amount="{{ $row->advanced_amount }}" @elseif($row->amount) data-amount="{{ $row->amount }}" @endif data-branch = "{{$row['branch']->id}}"  id="fresh_expense_record"></td>

                                <td>{{ $row['branch']->name }}</td> 

                                <!-- <td>{{ $row['branch']->branch_code }}</td>  -->

                                <!-- <td>{{ $row['branch']->sector }}</td>  -->

                                <!-- <td>{{ $row['branch']->regan }}</td>  -->

                                <!-- <td>{{ $row['branch']->zone }}</td>  -->

                                <td>{{ date("d/m/Y", strtotime( $row->created_at)) }}</td> 

                                <td>{{ $row->voucher_number }}</td>

                                <td>{{ $row->account_number }}</td>

                                <td>{{ getMemberNameByMemberInvestmentAutoId($row->investment_id) }}</td>

								<td>@if($row->payment_type==0)  {{ number_format((float)(($row->amount)), 2, '.', '') }} @else {{ number_format((float)(($row->maturity_prematurity_amount)), 2, '.', '') }}  @endif &#8377</td>

                                @if($row->tds_amount)
                                    <td>{{  number_format((float)(($row->tds_amount)), 2, '.', '') }}  &#8377</td>
                                @else
                                    <td>N/A</td>
                                @endif

                                <td>{{ getInterestAmountByInvestmentId($row->investment_id)}} &#8377</td>

								<td>{{  number_format((float)(($row->maturity_amount_payable+$row->tds_amount)), 2, '.', '') }}  &#8377</td>

                                @if($row->final_amount)
                                    <td>{{  number_format((float)(($row->final_amount)), 2, '.', '') }}  &#8377</td>
                                @elseif($row->maturity_amount_payable)
                                    <td>{{  number_format((float)(($row->maturity_amount_payable-$row->tds_amount)), 2, '.', '') }}  &#8377</td>
                                @elseif($row->advanced_amount)
                                    <td>{{  number_format((float)(($row->advanced_amount)), 2, '.', '') }}  &#8377</td>    
                                @else
                                    <td>N/A</td>
                                @endif

                                

                                <!-- <td></td> 

                                <td>{{ $row->owner_name }}</td> 

                                <td>{{ $row->particular }}</td> 

                                <td>{{ $row->mobile_number }}</td> --> 

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

