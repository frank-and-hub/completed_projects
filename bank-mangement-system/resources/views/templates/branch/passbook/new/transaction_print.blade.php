@extends('layouts/branch.dashboard')

@section('content')


<div class="container-fluid mt--6">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-lg-12">
                <div class="card bg-white">
                <div class="card-body page-title">
                   
                        <h3 class="">New Transaction For : 
                            @if($code=='S') {{ $accountDetail->account_no  }} @else {{ $accountDetail->account_number }} - {{ $accountDetail->plan->name}} @endif</h3>
                            @if($code=='S') 
                        <a href="{!! route('branch.passbook_transaction',['id'=>$accountDetail->member_investments_id,'code'=>$code]) !!}" style="float:right" class="btn btn-secondary">Back</a>
                        @else 
                            <a href="{!! route('branch.passbook_transaction',['id'=>$accountDetail->id,'code'=>$code]) !!}" style="float:right" class="btn btn-secondary">Back</a>
                        @endif
                    
                </div>
                </div>
            </div> 
        </div>
       

        <div class="row" > 
        
            <div class="col-lg-12"  >                

                <div class="card bg-white shadow">
                    <div class="card-body">
                        
                            <div class="table-responsive"  id="">
                                <table   class="table table-flush" style="width: 95%">
                                  <thead class=""> 
                                        <tr> 
                                            <th style="width: 13%;"> Date</th>
                                            <th style="width: 24%;">Particulars</th> 
                                            <th style="width: 10%;">Cheque No</th>
                                            <th style="width: 13%;">Withdrawal</th>
                                            <th style="width: 14%;">Amt. Deposited</th>
                                            <th style="width: 14%;">Balance</th>
                                            <th style="width: 11%;">Sign</th>
                                        </tr>
                                    </thead>
                                </table>
                                <table   class="table table-flush tran_print_tableNew" id="transaction_print" style="width: 95%">
                                   
                                     <tbody>
                                        @if(count($accountTranscation)>0)
                                            @foreach($accountTranscation as $val)

                                            @if ( $val->is_eli == 1 && in_array($val->account_no,$accountsNumber) )
                                                @else

                                                <tr>  
                                                    <td style="width: 13%;">
                                                        @if($code=='S')  {{ date("d/m/Y", strtotime($val->opening_date)) }} 
                                                        @else  {{ date("d/m/Y", strtotime($val->created_at)) }} 
                                                        @endif</td>
                                                    <td style="text-align: left;width: 24%">{{ str_replace('"}',"",str_replace('{"name":"',"",$val->description)) }}</td>
                                                    <td style="width: 10%;">
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
                                                    <td style="width: 13%; text-align: left;">@if($val->withdrawal>0){{ number_format($val->withdrawal,2) }}  @endif</td>
                                                    <td style="width: 14%;text-align: left;">@if($val->deposit>0){{ number_format($val->deposit,2) }}  @endif</td>
                                                    <td style="width: 14%;text-align: left;"> {{ number_format($val->opening_balance - $eliOpeningAmount,2) }}
                                                    </td>
                                                    <td style="width: 11%"> &nbsp;&nbsp;</td>
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

                 @if($code == 'S')
                    <div class="card bg-white shadow" id="printButton"> 
                        <div class="card-body">
                            <div class="col-lg-12 text-center ">
                                @if( in_array('New Passbook Transaction Print', auth()->user()->getPermissionNames()->toArray() ) )
                                <button type="submit" class="btn btn-primary" onclick="printDivTran('transaction_print','{{ $accountDetail->account_no  }}');" >Print</button>
                                @endif              
                            </div>
                        </div> 
                    </div>
                    
                
                @elseif($accountDetail->is_mature == 1)
                <div class="card bg-white shadow" id="printButton">  
                    <div class="card-body">
                        <div class="col-lg-12 text-center ">
                            @if( in_array('New Passbook Transaction Print', auth()->user()->getPermissionNames()->toArray() ) )
                            <button type="submit" class="btn btn-primary" onclick="printDivTran('transaction_print','{{ $accountDetail->account_no  }}');" >Print</button>
                            @endif              
                        </div>
                    </div> 
                </div>
                @endif

            </div> 

        </div>


    </div>
</div>
@stop

@section('script')
@include('templates.branch.passbook.new.script')
@stop