@extends('layouts/branch.dashboard')

@section('content')
<div class="loader" style="display: none;"></div>
<div class="container-fluid mt--6">
    <div class="content-wrapper">
        <div class="row advice"> 
            <table width="100%" border="0" cellspacing="0" cellpadding="0">
              <tr>
                <td style="padding:10px;width: 60%;">
                  <div class="card bg-white" > 
                    <div class="card-body">
                      <h3 class="card-title mb-3 text-center" >Payment Voucher</h3>
                      <div class="row">
                        <table width="100%" border="0" cellspacing="0" cellpadding="0" align="center" style="margin: 20px">
                          <tr>
                            <td style="padding: 7px ;width:12%"> Advice Type : </td>
                            <td style="padding: 7px;width:20%">

                            @if($row->payment_type == 0)
                                Expenses
                            @elseif($row->payment_type == 1)
                                Maturity
                            @elseif($row->payment_type == 2)
                                Prematurity
                            @elseif($row->payment_type == 3)
                                Death Help
                            @endif
                            </td>
                            @if($row->payment_type == 0)
                                <td style="padding: 7px;width:12%"> Expense Type : </td>
                                <td style="padding: 7px;width:20%">
                                    @if($row->sub_payment_type == '0')
                                       Fresh Exprense
                                    @elseif($row->sub_payment_type == '1')
                                        TA Advanced
                                    @elseif($row->sub_payment_type == '2')
                                        Advanced salary
                                    @elseif($row->sub_payment_type == '3')
                                       Advanced Rent
                                    @elseif($row->sub_payment_type == '4')
                                        Death Help
                                    @elseif($row->sub_payment_type == '5')
                                        Death Claim
                                    @else
                                        N/A
                                    @endif
                                </td>
                            @endif
                            <td style="padding: 7px;width:12%"> Branch Name : </td>
                            <td style="padding: 7px;width:20%">{{ $row['branch']->name }}</td> 
                          </tr> 
                          <tr>
                            <td style="padding: 7px;width:12%"> Branch Code : </td>
                            <td style="padding: 7px;width:20%">{{ $row['branch']->branch_code }} </td>
                            <td style="padding: 7px;width:12%"> Voucher Number : </td>
                            <td style="padding: 7px;width:20%">{{ $row->voucher_number }} </td>
                          </tr>
                            @if($row->payment_type == 0 && $row->sub_payment_type == 1)
                                <tr>
                                    <td style="padding: 7px;width:12%"> Employee Code : </td>
                                    <td style="padding: 7px;width:20%">{{ $row->employee_code }} </td>
                                    <td style="padding: 7px;width:12%"> Employee Name : </td>
                                    <td style="padding: 7px;width:20%">{{ $row->employee_name }} </td>
                                </tr>
                                <tr>
                                    <td style="padding: 7px;width:12%"> Particular : </td>
                                    <td style="padding: 7px;width:20%">{{ $row->particular }} </td>
                                    <td style="padding: 7px;width:12%"> Advanced Amount : </td>
                                    <td style="padding: 7px;width:20%">{{ $row->advanced_amount }} &#8377</td>
                                </tr>
                            @endif

                            @if($row->payment_type == 0 && $row->sub_payment_type == 2)
                                <tr>
                                    <td style="padding: 7px;width:12%"> Employee Code : </td>
                                    <td style="padding: 7px;width:20%">{{ $row->employee_code }} </td>
                                    <td style="padding: 7px;width:12%"> Employee Name : </td>
                                    <td style="padding: 7px;width:20%">{{ $row->employee_name }} </td>
                                </tr>
                                <tr>
                                    <td style="padding: 7px;width:12%"> Mobile Number : </td>
                                    <td style="padding: 7px;width:20%">{{ $row->mobile_number }} </td>
                                    <td style="padding: 7px;width:12%"> Amount : </td>
                                    <td style="padding: 7px;width:20%">{{ $row->amount }} &#8377</td>
                                </tr>

                                <tr>
                                    <td style="padding: 7px;width:12%"> Narration : </td>
                                    <td style="padding: 7px;width:20%">{{ $row->narration }} </td>
                                    <td style="padding: 7px;width:12%"> SSB Account : </td>
                                    <td style="padding: 7px;width:20%">{{ $row->ssb_account }} </td>
                                </tr>

                                <tr>
                                    <td style="padding: 7px;width:12%"> Bank Name : </td>
                                    <td style="padding: 7px;width:20%">{{ $row->bank_name }} </td>
                                    <td style="padding: 7px;width:12%"> Bank A/C No : </td>
                                    <td style="padding: 7px;width:20%">{{ $row->bank_account_number }} </td>
                                </tr>
                                <tr>
                                    <td style="padding: 7px;width:12%"> IFSC Code : </td>
                                    <td style="padding: 7px;width:20%">{{ $row->bank_ifsc }} </td>
                                </tr>
                            @endif

                            @if($row->payment_type == 0 && $row->sub_payment_type == 2)
                                <tr>
                                    <td style="padding: 7px;width:12%"> Owner Name : </td>
                                    <td style="padding: 7px;width:20%">{{ $row->owner_name }} </td>
                                    <td style="padding: 7px;width:12%"> Mobile Number : </td>
                                    <td style="padding: 7px;width:20%">{{ $row->mobile_number }} </td>
                                </tr>
                                <tr>
                                    <td style="padding: 7px;width:12%"> Advanced Rent Amount : </td>
                                    <td style="padding: 7px;width:20%">{{ $row->amount }} &#8377</td>
                                    <td style="padding: 7px;width:12%"> Narration : </td>
                                    <td style="padding: 7px;width:20%">{{ $row->narration }} </td>
                                </tr>
                                <tr>
                                    <td style="padding: 7px;width:12%"> SSB Account : </td>
                                    <td style="padding: 7px;width:20%">{{ $row->ssb_account }} </td>
                                    <td style="padding: 7px;width:12%"> Bank Name : </td>
                                    <td style="padding: 7px;width:20%">{{ $row->bank_name }} </td>
                                </tr>
                                <tr>
                                    <td style="padding: 7px;width:12%"> Bank A/C No : </td>
                                    <td style="padding: 7px;width:20%">{{ $row->bank_account_number }} </td>
                                    <td style="padding: 7px;width:12%"> IFSC Code : </td>
                                    <td style="padding: 7px;width:20%">{{ $row->bank_ifsc }} </td>
                                </tr>
                            @endif

                            @if($row->payment_type == 1 || $row->payment_type == 2)
                                <tr>
                                    <td style="padding: 7px;width:12%"> Account Number : </td>
                                    <td style="padding: 7px;width:20%">{{ $row->account_number }} </td>
                                    <td style="padding: 7px;width:12%"> Opening Date : </td>
                                    <td style="padding: 7px;width:20%">{{ date("d/m/Y", strtotime(convertDate($row->opening_date))) }} </td>
                                </tr>
                                <tr>
                                    <td style="padding: 7px;width:12%"> Plan Name : </td>
                                    <td style="padding: 7px;width:20%">{{ $row->plan_name }} </td>
                                    <td style="padding: 7px;width:12%"> Tenure : </td>
                                    <td style="padding: 7px;width:20%">{{ $row->tenure }} </td>
                                </tr>
                                <tr>
                                    <td style="padding: 7px;width:12%"> Account Holder Name : </td>
                                    <td style="padding: 7px;width:20%">{{ $row->account_holder_name }} </td>
                                    <td style="padding: 7px;width:12%"> Category : </td>
                                    <td style="padding: 7px;width:20%">
                                    @if($row->maturity_prematurity_category == 0)
                                        Regular 
                                    @elseif($row->maturity_prematurity_category == 1)
                                        Defaulter
                                    @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 7px;width:12%"> Amount : </td>
                                    <td style="padding: 7px;width:20%">{{ $row->maturity_prematurity_amount }} &#8377</td>
                                    <td style="padding: 7px;width:12%"> Mobile Number : </td>
                                    <td style="padding: 7px;width:20%">{{ $row->mobile_number }} </td>
                                </tr>
                                <tr>
                                    <td style="padding: 7px;width:12%"> SSB Account Number : </td>
                                    <td style="padding: 7px;width:20%">{{ $row->ssb_account }} </td>
                                    <td style="padding: 7px;width:12%"> Bank A/C : </td>
                                    <td style="padding: 7px;width:20%">{{ $row->bank_account_number }} </td>
                                </tr>
                                <tr>
                                    <td style="padding: 7px;width:12%"> IFSC Code : </td>
                                    <td style="padding: 7px;width:20%">{{ $row->bank_ifsc }} </td>
                                </tr>
                            @endif

                            @if($row->payment_type == 3)
                                <tr>
                                    <td style="padding: 7px;width:12%"> Account Number : </td>
                                    <td style="padding: 7px;width:20%">{{ $row->account_number }} </td>
                                    <td style="padding: 7px;width:12%"> Opening Date : </td>
                                    <td style="padding: 7px;width:20%">{{ date("Y-m-d", strtotime(convertDate($row->opening_date))) }} </td>
                                </tr>
                                <tr>
                                    <td style="padding: 7px;width:12%"> Plan Name : </td>
                                    <td style="padding: 7px;width:20%">{{ $row->plan_name }} </td>
                                    <td style="padding: 7px;width:12%"> Tenure : </td>
                                    <td style="padding: 7px;width:20%">{{ $row->tenure }} </td>
                                </tr>
                                <tr>
                                    <td style="padding: 7px;width:12%"> Account Holder Name : </td>
                                    <td style="padding: 7px;width:20%">{{ $row->account_holder_name }} </td>
                                    <td style="padding: 7px;width:12%"> Deno : </td>
                                    <td style="padding: 7px;width:20%">{{ $row->deno }}</td>
                                </tr>
                                <tr>
                                    <td style="padding: 7px;width:12%"> Deposited Amount : </td>
                                    <td style="padding: 7px;width:20%">{{ $row->deposited_amount }} &#8377</td>
                                    <td style="padding: 7px;width:12%"> Death Help/Detah Claim Amount : </td>
                                    <td style="padding: 7px;width:20%">{{ $row->maturity_amount_payable }} </td>
                                </tr>
                                <tr>
                                    <td style="padding: 7px;width:12%"> Nominee Member Id : </td>
                                    <td style="padding: 7px;width:20%">{{ $row->naominee_member_id }} </td>
                                    <td style="padding: 7px;width:12%"> Nominee Name : </td>
                                    <td style="padding: 7px;width:20%">{{ $row->nominee_name }} </td>
                                </tr>
                                <tr>
                                    <td style="padding: 7px;width:12%"> Mobile Number : </td>
                                    <td style="padding: 7px;width:20%">{{ $row->mobile_number }} </td>
                                    <td style="padding: 7px;width:12%"> SSB Account : </td>
                                    <td style="padding: 7px;width:20%">{{ $row->mobile_number }} </td>
                                </tr>
                            @endif

                            <tr>
                                <td style="padding: 7px;width:12%"> Payment Mode : </td>
                                <td style="padding: 7px;width:20%">
                                    @if($row->payment_mode == '0')
                                        Cash
                                    @elseif($row->payment_mode == '1')
                                        Cheque
                                    @elseif($row->payment_mode == '2')
                                         Online Transfer
                                    @elseif($row->payment_mode == '3')
                                        SSB/GV
                                    @elseif($row->payment_mode == '4')
                                        Auto
                                    @else
                                        N/A
                                    @endif
                                </td>

                                @if($row->payment_mode == '1')
                                    <td style="padding: 7px;width:12%"> cheque No : </td>
                                    @php
                                      $transaction = getDemandTransactionDetails(13,$row->id)
                                    @endphp
                                    @if($transaction)
                                        <td style="padding: 7px;width:20%">{{ $transaction->cheque_no }} </td>
                                    @else
                                        <td style="padding: 7px;width:20%">N/A</td>
                                    @endif
                                @endif
                                
                                @if($row->payment_mode == '2')
                                    <td style="padding: 7px;width:12%"> Transaction No : </td>
                                    @php
                                      $transaction = getDemandTransactionDetails(13,$row->id)
                                    @endphp
                                    @if($transaction)
                                        <td style="padding: 7px;width:20%">{{ $transaction->transction_no }} </td>
                                    @else
                                        <td style="padding: 7px;width:20%">N/A</td>
                                    @endif
                                @endif
                            </tr>
                        </table>                  
                      </div> 
                    </div>

                    @if(count($row['expenses']) > 0)
                    <div class="card-header header-elements-inline">
                        <h3 class="card-title font-weight-semibold">
                        @if($row->sub_payment_type == '0')
                            Fresh Expenses
                        @elseif($row->sub_payment_type == '1')
                            TA Advanced
                        @endif</h3>
                    </div>
                    <table class="table datatable-show-all">
                        <thead>
                            <tr>
                                <th width="5%">S/N</th>
                                <th width="10%">Expense Type</th>
                                <th width="10%">Category</th>
                                <th width="10%">SubCategory</th>
                                @if($row->sub_payment_type == '0')
                                    <th width="10%">Party Name</th>
                                    <th width="10%">Particular</th>
                                    <th width="10%">Mobile Number</th>
                                @endif
                                <th width="10%">Amount</th>
                                <th width="5%">Bill Number</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($row['expenses'] as $key => $val)
                            <tr>
                                <td>{{ $key+1 }}</td>
                                @if($row->sub_payment_type == '0')
                                    <td>Fresh Expenses</td>
                                @elseif($row->sub_payment_type == '1')
                                    <td>TA Advanced</td>
                                @endif
                                @if(getAcountHead($val->category))
                                <td>{{ getAcountHead($val->category) }}</td>
                                @else
                                <td>N/A</td>
                                @endif
                                @if(getAcountHead($val->subcategory))
                                <td>{{ getAcountHead($val->subcategory) }}</td>
                                @else
                                <td>N/A</td>
                                @endif
                                @if($row->sub_payment_type == '0')
                                    <td>{{ $val->party_name }}</td>
                                    <td>{{ $val->particular }}</td>
                                    <td>{{ $val->mobile_number }}</td>
                                @endif
                                <td>{{ $val->amount }} &#8377</td>
                                <td>{{ $val->bill_number }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @endif

                  </div> 
                </td> 
              </tr>
            </table> 
        </div>

        <div class="row">
            <div class="col-lg-12">
                <div class="card bg-white" >            
                    <div class="card-body ">
                        <div class="row">
                            <div class="col-lg-12 text-center">
                              <button type="submit" class="btn btn-primary" target="_blank" onclick="printDiv('advice');"> Print<i class="icon-paperplane ml-2" ></i></button>
                            </div> 
                        </div>
                    </div>
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
