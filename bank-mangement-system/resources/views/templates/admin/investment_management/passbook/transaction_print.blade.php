@extends('templates.admin.master')

@section('content')

<div class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header header-elements-inline">
                    <h6 class="card-title font-weight-semibold">Old Transaction For : 
                        @if($code=='S') {{ $accountDetail->account_no  }} @else {{ $accountDetail->account_number }} - {{ $accountDetail->plan->name}} @endif</h6>
                        {{-- @if($code=='S') 
                            <a href="{!! route('admin.passbook_transaction',['id'=>$accountDetail->member_investments_id,'code'=>$code]) !!}" style="float:right" class="btn btn-secondary">Back</a>
                        @else 
                            <a href="{!! route('admin.passbook_transaction',['id'=>$accountDetail->id,'code'=>$code]) !!}" style="float:right" class="btn btn-secondary">Back</a>
                        @endif --}}
                </div>
            </div>
        </div>
    </div>
   

    <div class="row" > 
    
        <div class="col-lg-12"  >                

            <div class="card bg-white shadow">
                <div class="card-body">
                    
                        <div class="table-responsive"  id="">
                            <table   class="table table-flush" style="width: 100%;text-align: center;" >
                              <thead class=""> 
                                    <tr>
                                        <th style="width: 13%"> Date</th>
                                        <th style="width: 23%">Description</th> 
                                        <th  style="width: 16%">Cheque No</th>
                                        <th  style="width: 16%">Withdrawal</th>
                                        <th  style="width: 16%">Deposit</th>
                                        <th  style="width: 11%">Balance</th>
                                         <th  style="width: 5%">Sign</th>
                                    </tr>
                                </thead>
                            </table>
                            <table   class="table table-flush tran_print_table" style="width: 100%; text-align: center;" id="transaction_print">
                               
                                 <tbody>
                                    @if(count($accountTranscation)>0)
                                        @foreach($accountTranscation as $val)
                                            @if ( $val->is_eli == 1 && in_array($val->account_no,$accountsNumber) )
                                            @else
                                                <tr>
                                                    <td style="width: 13%;">
                                                        @if($code=='S')  {{ date("d/m/Y", strtotime($val->opening_date)) }} 
                                                        @else  {{ date("d/m/Y", strtotime($val->created_at)) }} 
                                                        @endif
                                                    </td>
                                                    <td style="width: 20%;">{{ str_replace('"}',"",str_replace('{"name":"',"",$val->description)) }}</td>
                                                    <td style="width: 17%;">
                                                        @if($code=='S') 
                                                            {{ $val->reference_no }} 
                                                        @else  
                                                            @if($val->payment_mode==1)
                                                            {{$val->cheque_dd_no}}
                                                            @endif
                                                            @if($val->payment_mode==4 || $val->payment_mode==5 )
                                                              
                                                                {{$val->reference_no}}
                                                            @endif
                                                            @if($val->payment_mode==3)
                                                              
                                                                {{ $val->online_payment_id}}                                                          
                                                            @endif
                                                        @endif
                                                    </td>
                                                    <td style="width: 17%">@if($val->withdrawal>0){{ number_format($val->withdrawal,2) }}  @endif</td>
                                                    <td style="width: 15%">@if($val->deposit>0){{ number_format($val->deposit,2) }}  @endif</td>
                                                    <td style="width: 13%">{{ number_format($val->opening_balance - $eliOpeningAmount,2) }} </td>
                                                    <td style="width: 5%"> &nbsp;&nbsp;</td>
                                                </tr>
                                            @endif
                                        @endforeach
                                    @else
                                    <tr><td colspan="6" class="text-center"> No data found!</td></tr>
                                    @endif
                                </tbody>
                            </table>
                        </div> 
                                       
                </div>
            </div> 

           
            <div class="card bg-white shadow" id="printButton"> 
                <div class="card-body">
                    <div class="col-lg-12 text-center ">
                        <button type="submit" class="btn btn-primary" onclick="printDivTran('transaction_print','{{ $accountDetail->account_no  }}');" >Print</button>
                    </div>
                </div> 
            </div>
            

        </div> 

    </div>
</div>

@stop

@section('script')
@include('templates.admin.investment_management.passbook.partials.script')
@stop