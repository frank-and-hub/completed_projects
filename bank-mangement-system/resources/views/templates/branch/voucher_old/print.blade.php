@extends('layouts/branch.dashboard')

@section('content')

<div class="container-fluid mt--6">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-lg-12">
                <div class="card bg-white">
                <div class="card-body page-title"> 
                        <h3 class="">Received Voucher Print</h3> 
                    
                </div>
                </div>
            </div>
        </div>
        <div class="row" id="advice"> 
        <table width="100%" border="0" cellspacing="0" cellpadding="0">
          <tr>
            <td style="padding:10px;width: 60%;">
              <div class="card bg-white" > 
                <div class="card-body">
                  <h3 class="card-title mb-3 text-center" >Print Received </h3>
                  <div class="row">
                    <table width="100%" border="0" cellspacing="0" cellpadding="0" align="center" style="margin: 20px">
                      <tr>
                        <td style="padding: 7px ;width:12%"> BR Name : </td>
                        <td style="padding: 7px;width:20%">{{$row['rv_branch']->name}}</td>
                        <td style="padding: 7px;width:12%"> BR Code : </td>
                        <td style="padding: 7px;width:20%">{{$row['rv_branch']->branch_code}} </td>
                        <td style="padding: 7px;width:12%"> SO Name : </td>
                        <td style="padding: 7px;width:20%">{{$row['rv_branch']->sector}}</td> 
                      </tr> 
                      <tr>
                        <td style="padding: 7px;width:12%"> RO Name : </td>
                        <td style="padding: 7px;width:20%">{{$row['rv_branch']->regan}} </td>
                        <td style="padding: 7px;width:12%"> ZO Name : </td>
                        <td style="padding: 7px;width:20%"> {{$row['rv_branch']->zone}}</td>
                        <td style="padding: 7px;width:12%"> Account Head : </td>
                        <td style="padding: 7px;width:20%">  {{getAcountHeadNameHeadId($row->account_head_id)}} </td>
                        
                      </tr>
                     

                      <tr>
                        <td style="padding: 7px;width:12%">  
                          @if($row->type==1)
                          Director :
                          @elseif($row->type==2)
                          Shareholder:
                          @elseif($row->type==3)
                          Employee Name/Code
                          @elseif($row->type==4)
                          Bank Name/Account No.
                          @elseif($row->type==5)
                          Eli Loan
                          @endif
                        </td>
                        <td style="padding: 7px;width:20%"> 
                          @if($row->type==1)
                          {{getAcountHeadNameHeadId($row->director_id)}}
                          @elseif($row->type==2)
                          {{getAcountHeadNameHeadId($row->shareholder_id)}}
                          @elseif($row->type==3)
                          {{$row['rv_employee']->employee_name }} - {{$row['rv_employee']->employee_code }}
                          @elseif($row->type==4)
                          {{getSamraddhBank($row->bank_id)->bank_name}} - {{getSamraddhBankAccountId($row->bank_ac_id)->account_no}}
                          @elseif($row->type==5)
                          {{getAcountHeadNameHeadId($row->eli_loan_id)}}
                          @endif
                        </td>
                        <td style="padding: 7px;width:12%"> Particular : </td>
                        <td style="padding: 7px;width:20%"> {{$row->particular}}</td>
                        <td style="padding: 7px;width:12%"> Received Mode : </td>
                        <td style="padding: 7px;width:20%">@if($row->received_mode==1) Cheque @elseif($row->received_mode==2) Online @else Cash @endif</td>
                        
                      </tr>
                      @if($row->received_mode==1)
                      <tr>
                        <td style="padding: 7px;width:12%"> Cheque No. :  </td>
                        <td style="padding: 7px;width:20%"> {{$row['rvCheque']->cheque_no}}</td>
                        <td style="padding: 7px;width:12%"> Cheque Date :  </td>
                        <td style="padding: 7px;width:20%">{{ date("d/m/Y", strtotime($row->cheque_date)) }} </td>
                        <td style="padding: 7px;width:12%"> Party Name </td>
                        <td style="padding: 7px;width:20%"> {{$row['rvCheque']->account_holder_name}}</td>
                        
                      </tr>
                      <tr>
                        <td style="padding: 7px;width:12%"> Party Bank </td>
                        <td style="padding: 7px;width:20%" >{{$row['rvCheque']->bank_name}}</td>
                        <td style="padding: 7px;width:12%"> Party Bank A/c </td>
                        <td style="padding: 7px;width:20%">{{$row['rvCheque']->cheque_account_no}}</td>
                        <td style="padding: 7px;width:12%"> Receive Bank </td>
                        <td style="padding: 7px;width:20%"> {{getSamraddhBank($row['rvCheque']->deposit_bank_id)->bank_name}}</td>
                        
                      </tr>
                      <tr>
                        <td style="padding: 7px;width:12%"> Receive Bank A/c  </td>
                        <td style="padding: 7px;width:20%" >  {{getSamraddhBankAccountId($row['rvCheque']->deposit_account_id)->account_no}}</td>
                        <td style="padding: 7px;width:12%"> Received Amount </td>
                        <td style="padding: 7px;width:20%">{{number_format((float)$row->amount, 2, '.', '')}} &#x20B9;</td>
                        <td style="padding: 7px;width:12%">  </td>
                        <td style="padding: 7px;width:20%"> </td>
                        
                      </tr>

                      @elseif($row->received_mode==2) 
                        <tr>
                        <td style="padding: 7px;width:12%"> UTR/Transaction No. :  </td>
                        <td style="padding: 7px;width:20%"> {{$row->online_tran_no}}</td>
                        <td style="padding: 7px;width:12%"> UTR/Transaction Date:  </td>
                        <td style="padding: 7px;width:20%">{{ date("d/m/Y", strtotime($row->online_tran_date)) }} </td>
                        <td style="padding: 7px;width:12%"> Transaction Slip</td>
                        <td style="padding: 7px;width:20%">
                          @if($row->slip)
                            <a href="{{url('/')}}/asset/voucher/{{$row->slip}}" target="_blanck">{{$row->slip}}</a>
                            @endif
                          </td>
                        
                      </tr>
                      <tr>
                        <td style="padding: 7px;width:12%"> Transaction Bank Name </td>
                        <td style="padding: 7px;width:20%" >{{$row->online_tran_bank_name}}</td>
                        <td style="padding: 7px;width:12%"> Transaction Bank A/c </td>
                        <td style="padding: 7px;width:20%">{{$row->online_tran_bank_ac_no}}</td>
                        <td style="padding: 7px;width:12%"> Receive Bank </td>
                        <td style="padding: 7px;width:20%"> {{getSamraddhBank($row->receive_bank_id)->bank_name}}</td>
                        
                      </tr>
                      <tr>
                        <td style="padding: 7px;width:12%"> Received Bank A/c  </td>
                        <td style="padding: 7px;width:20%" >  {{getSamraddhBankAccountId($row->receive_bank_ac_id)->account_no}}</td>
                        <td style="padding: 7px;width:12%"> Received Amount </td>
                        <td style="padding: 7px;width:20%">{{number_format((float)$row->amount, 2, '.', '')}} &#x20B9;</td>
                        <td style="padding: 7px;width:12%">  </td>
                        <td style="padding: 7px;width:20%"> </td>
                        
                      </tr>
                      @else 

                      <tr>
                        
                      <!--   <td style="padding: 7px;width:12%"> Daybook : </td>
                        <td style="padding: 7px;width:20%">@if($row->received_mode==0)  @if($row->daybook_type==1) Loan @else Investment @endif @else N/A @endif</td> -->
                        <td style="padding: 7px;width:12%"> Received Branch Name :  </td>
                        <td style="padding: 7px;width:20%">{{$row['rv_branch']->name}} </td>
                        <td style="padding: 7px;width:12%"> Received Branch Code : </td>
                        <td style="padding: 7px;width:20%"> {{$row['rv_branch']->branch_code}}</td>
                        
                      </tr>


                      @endif 
                      
                    </table>                  
                    
                   
                  </div> 
                </div>
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
              <button type="submit" class="btn btn-primary" onclick="printDiv('advice');"> Print<i class="icon-paperplane ml-2" ></i></button>
            </div> 
            </div>
          </div>
        </div>

      </div>  
</div>
        
</div>


@stop

@section('script')
@include('templates.branch.voucher.partials.script_print')
@stop