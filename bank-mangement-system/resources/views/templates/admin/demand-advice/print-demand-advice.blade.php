@extends('templates.admin.master')
@section('content')
<style type="text/css">
    @media print{
        .headId,.auth,.des{
            display: none;
        }
        .box2,.c_logo,.c_name,.c_address,.date,thead{
            display: none;
        }
        label .v_no,.m_id,.name,.ac_no {
             display: none;
        }
        .auth_main{
            border: none;
            margin-top: 80px;
            text-align: center;
        }
    } 

</style>

<table width="90%" align="center" border="0" cellspacing="0" cellpadding="0" style="font-family:Arial, Helvetica, sans-serif;margin-top:10px;" id="advice">
  <tr>
    <td>
     <table width="100%" border="0" cellspacing="0" cellpadding="15" style="background:#fff; border:#000000 solid 2px;">
      <tr>
        <td colspan="2" style=" text-align:center; padding:20px 0px 40px 0px; font-size:20px; font-weight:600">Payment Voucher</td>
      </tr>
      <tr>
        <td width="70%" style="text-align:center;">
        <img  src="https://my.samraddhbestwin.com/asset/images/logo_1587603901.png" width="200" alt="" />
         <h3 style="margin:0px; padding:10px 0px;">Samraddh Bestwin Micro Finance Association </h3>
         <p >Corp. Office: 114-115, Pushp Enclave, Sector -5, Pratap Nagar, Tonk Road Jaipur- 302033 Info.: Phone No.: +91 9829861860 | Web: www.samraddhbestwin.com | Mail Us: info@samraddhbestwin.com</p>
         <div style="padding:15px; border:#000000 solid 1px;"><strong>Authorised Center Name And Code: JHALAWAR(1020)</strong></div>
        </td>
        <td>
         <div class="" style="padding:0 0 0 50px;">
          <p><label>V.No:</label> <span>{{ $row->voucher_number }}</span></p>
        <?php
            if($row->payment_type ==1 || $row->payment_type ==2 || $row->payment_type ==3 )
            {
                $record = \App\Models\BranchDaybook::where('type',13)->whereIn('sub_type',[133,134,136])->where('type_id',$row->id)->first();
                $date = $record->entry_date;
            }
            else{
                $date = $row->date;
            }    

        ?>
          <p><label>Date:</label> <span> {{ date("d/m/Y", strtotime(convertDate($date))) }}</span></p>
         </div>
         @php
                                $totalAmount = 0;
                                if($row->payment_type == 0 && $row->sub_payment_type == 0){
                                    $name = 'N/A';
                                    $memberId = 'N/A';
                                    $accountNumber = 'N/A';
                                }elseif($row->payment_type == 0 && $row->sub_payment_type == 1){
                                    $demandAdvice = App\Models\DemandAdvice::with('employee')->where('id',$row->id)->first(); 
                                    if($demandAdvice['employee']->ssb_account){
                                        $ssbAccountDetails = App\Models\SavingAccount::with('ssbMember')->select('member_id')->where('account_no',$demandAdvice['employee']->ssb_account)->first();
                                        $memberId = $ssbAccountDetails->member_id;
                                    }else{
                                        $memberId = 'N/A';
                                    }
                                    $accountNumber = 'N/A';
                                    $name = str_replace('null', '', $row->employee_name);
                                }
                                elseif(($row->payment_type == 0 && $row->sub_payment_type == 2 )||  ($row->payment_type == 0 && $row->sub_payment_type == 3 ))   
                                    {
                                        if($row->sub_payment_type == 2)
                                        {

                                            $name =  $name = str_replace('null', '', $row->employee_name);
                                            $demandAdvice = App\Models\DemandAdvice::with('employee')->where('id',$row->id)->first(); 

                                             if($demandAdvice['employee']->ssb_account){
                                                $ssbAccountDetails = App\Models\SavingAccount::with('ssbMember')->select('member_id')->where('account_no',$demandAdvice['employee']->ssb_account)->first();
                                                if($ssbAccountDetails){
                                                    $memberId = getMemberData($ssbAccountDetails->member_id)->member_id;
                                                }else{
                                                    $memberId = 'N/A';
                                                }
                                            }else{
                                                $memberId = 'N/A';
                                            }
                                        }
                                        elseif($row->sub_payment_type ==3)
                                        {
                                            $name = $name = str_replace('null', '', $row->owner_name);
                                            $demandAdvice = App\Models\DemandAdvice::with('owner')->where('id',$row->id)->first(); 

                                             if($demandAdvice['owner']->owner_ssb_number){
                                                $ssbAccountDetails = App\Models\SavingAccount::with('ssbMember')->select('member_id')->where('account_no',$demandAdvice['owner']->owner_ssb_number)->first();
                                                if($ssbAccountDetails){
                                                    $memberId = getMemberData($ssbAccountDetails->member_id)->member_id;
                                                }else{
                                                    $memberId = 'N/A';
                                                }
                                            }else{
                                                $memberId = 'N/A';
                                            }
                                        }
                                       
                                        $accountNumber = 'N/A';
                                    }
                                elseif($row->payment_type == 1 || $row->payment_type == 2 || $row->payment_type == 3 || $row->payment_type == 4){
                                    $demandAdvice = App\Models\DemandAdvice::with('investment')->where('id',$row->id)->first();  
                                    if($demandAdvice['investment']){
                                        $memberId = $demandAdvice['investment']->memberCompany->member_id;
                                        $accountNumber = $demandAdvice['investment']->account_number;
                                    }else{
                                        $memberId = 'N/A';
                                        $accountNumber = 'N/A';
                                    }

                                    if($demandAdvice['investment']->plan_id == 2){
                                        $headName = getAcountHeadData(80);
                                    }elseif($demandAdvice['investment']->plan_id == 3){
                                        $headName = getAcountHeadData(85);
                                    }elseif($demandAdvice['investment']->plan_id == 4){
                                        $headName = getAcountHeadData(79);
                                    }elseif($demandAdvice['investment']->plan_id == 5){
                                        $headName = getAcountHeadData(83);
                                    }elseif($demandAdvice['investment']->plan_id == 6){
                                        $headName = getAcountHeadData(84);
                                    }elseif($demandAdvice['investment']->plan_id == 7){
                                        $headName = getAcountHeadData(58);
                                    }elseif($demandAdvice['investment']->plan_id == 8){
                                        $headName = getAcountHeadData(78);
                                    }elseif($demandAdvice['investment']->plan_id == 9){
                                        $headName = getAcountHeadData(77);
                                    }elseif($demandAdvice['investment']->plan_id == 10){
                                        $headName = getAcountHeadData(83);
                                    }elseif($demandAdvice['investment']->plan_id == 11){
                                        $headName = getAcountHeadData(82);
                                    }
                                   if($row->payment_type == 4)
                                    {
                                        $demandAdvice = App\Models\DemandAdvice::with('investment')->where('id',$row->id)->first();  
                                        if($demandAdvice['investment']->member_id){
                                            $name = getMemberCustomData($demandAdvice['investment']->member_id)->first_name.' '.getMemberCustomData($demandAdvice['investment']->member_id)->last_name??'';
                                        }
                                    }
                                    else{$name = str_replace('null', '', $row->account_holder_name);}
                                }
                                @endphp
         <div style="padding:0 0 0 30px;"> 	
 	
         <table width="100%" border="1" cellspacing="0" cellpadding="10">
        
          <tr>
          @if($name == "N/A")   
            <td>Name:</td>
            <td></td>
          @else
            <td>Name:</td>
            <td>{{ $name }}</td>
            @endif
          </tr>
          <tr>
          @if($memberId == "N/A")
            <td>Member Id:</td>
            <td></td>
          @else
            <td  width="100%">Member Id:</td>
            <td  width="100%">{{ $memberId }}</td>
          @endif  
          </tr>
          <tr>
          @if($accountNumber == "N/A")
            <td>Account No:</td>
            <td></td>                   
          @else
            <td>Account No:</td>
            <td>{{ $accountNumber }}</td>
            @endif
          </tr>
        </table>
        </div>
        </td>
      </tr>
    </table>
        <table width="100%" border="1" cellspacing="0" cellpadding="10">
         <thead>
          <tr>
            <td>Amount</td> 	 	 	
            <td>Payment Mode</td>
            <td>Account Head</td>
            <td>Rupees In Words</td>
          </tr>
          </thead>
          <tbody>
          <tr>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
          </tr>
          @if(($row->payment_type == 0 && $row->sub_payment_type == 0) || ($row->payment_type == 0 && $row->sub_payment_type == 1 &&  $row->ta_advanced_adjustment == 0)) 
            <?php
                $count = count($row['expenses']);
            ?>
            @if($count > 0)  
            @foreach($row['expenses'] as $val) 
            @php 
                $totalAmount = $totalAmount+$val->amount;
            @endphp 
          <tr>
            <td>{{ number_format((float)$val->amount, 2, '.', '') }}</td>
            <td rowspan="3"@if($val->payment_mode == 0)
                CASH
                @elseif($val->payment_mode == 1) 
                            CHEQUE 
                @php
                    $transaction = getDemandTransactionDetails(13,$row->id)
                @endphp
                @if($transaction->cheque_no)({{$transaction->cheque_no}})@endif
                    @elseif($val->payment_mode == 2)
                @php
                    $transaction = getDemandTransactionDetails(13,$row->id)
                @endphp
                ONLINE TRANSFER @if($transaction->transction_bank_ac_from)({{$transaction->transction_bank_ac_from}}) @if($row->bank_account_number )to {{$row->bank_account_number}}  ( {{$row->bank_name}})@else  @endif @endif 
                @elseif($val->payment_mode == 3)
                Saving Account Transfer @if($row->ssb_account)({{$row->ssb_account}})@endif
                @elseif($val->payment_mode == 4)
                AUTO Transfer
                @endif 
            </td>
            <td rowspan="3">
                @if(isset($val->assets_subcategory))
                {{ getAcountHeadData($val->assets_subcategory) }}
                @elseif(isset($val->subcategory))
                {{ getAcountHeadData($val->subcategory) }}
                @elseif(isset($val->subcategory1))
                {{ getAcountHeadData($val->subcategory1) }}
                @elseif(isset($val->subcategory2))
                {{ getAcountHeadData($val->subcategory2) }}
                    @elseif(isset($val->subcategory3))
                {{ getAcountHeadData($val->subcategory3) }}
                @else
                N/A
                @endif
            </td>
            <td rowspan="3">{{ amountINWord($totalAmount) }} Only</td>
          </tr>
          @endforeach
          <tr>
              <td></td>
              <td>Total: {{ number_format((float)($totalAmount), 2, '.', '') }}</td>
              <td></td>
              <td></td>
          </tr>
          @else
          <tr>
            <td>{{number_format((float)($row->advanced_amount), 2, '.', '')  }}</td>
            <td>
              @if($row->payment_mode == 0)
                    CASH
              @elseif($row->payment_mode == 1) 
                    CHEQUE 
                @php
                  $transaction = getDemandTransactionDetails(13,$row->id)
                @endphp
              @if($transaction->cheque_no)({{$transaction->cheque_no}})@endif
              @elseif($row->payment_mode == 2)
                @php
                $transaction = getDemandTransactionDetails(13,$row->id)
                @endphp
                ONLINE TRANSFER @if($transaction->transction_bank_ac_from)({{$transaction->transction_bank_ac_from}}) @if($row->bank_account_number )to {{$row->bank_account_number}}  ( {{$row->bank_name}})@else  @endif @endif
             @elseif($row->payment_mode == 3)
                    Saving Account Transfer @if($row->ssb_account)({{$row->ssb_account}})@endif
              @elseif($row->payment_mode == 4)
                    AUTO Transfer
             @endif
          </td>
            <td>
              TA Advanced
            </td>
            <td>{{amountINWord($row->advanced_amount)  }} Only</td>
        </tr>
        <tr>
          <td></td>
          <td>Total:{{ number_format((float)($row->advanced_amount), 2, '.', '') }}</td>
          <td></td>
          <td></td>
        </tr>                     
          @endif
          @elseif($row->payment_type == 0 && $row->sub_payment_type == 1 &&  $row->ta_advanced_adjustment == 0)
        <tr>
            <td>{{ number_format((float)($val->advanced_amount), 2, '.', '') }}</td>
            <td>
              @if($row->payment_mode == 0)
                  CASH
              @elseif($row->payment_mode == 1) 
                  CHEQUE 
                @php
                  $transaction = getDemandTransactionDetails(13,$row->id)
                @endphp
              @if($transaction->cheque_no)({{$transaction->cheque_no}})@endif
              @elseif($row->payment_mode == 2)
              @php
              $transaction = getDemandTransactionDetails(13,$row->id)
              @endphp
                ONLINE TRANSFER @if($transaction->transction_bank_ac_from)({{$transaction->transction_bank_ac_from}}) @if($row->bank_account_number )to {{$row->bank_account_number}}  ( {{$row->bank_name}})@else  @endif @endif
              @elseif($row->payment_mode == 3)
                  Saving Account Transfer @if($row->ssb_account)({{$row->ssb_account}})@endif
              @elseif($row->payment_mode == 4)
                  AUTO Transfer
              @endif
              </td>
            <td>TA Advanced</td>
            <td>{{ amountINWord($row->advanced_amount) }} Only</td>
        </tr>

        <tr>
            <td></td>
            <td>Total: {{ number_format((float)($row->advanced_amount), 2, '.', '') }}</td>
            <td></td>
            <td></td>
        </tr>
          @elseif(($row->payment_type == 0 && $row->sub_payment_type == 2 )||  ($row->payment_type == 0 && $row->sub_payment_type == 3 ))
        <tr>
           
            <td>{{ number_format((float)($row->amount), 2, '.', '') }}</td>
            <td>@if($row->payment_mode == 0)
                        CASH
                @elseif($row->payment_mode == 1) 
                        CHEQUE 
                    @php
                      $transaction = getDemandTransactionDetails(13,$row->id)
                    @endphp
                @if($transaction->cheque_no)({{$transaction->cheque_no}})@endif
                @elseif($row->payment_mode == 2)
                  @php
                  $transaction = getDemandTransactionDetails(13,$row->id)
                  @endphp
                    ONLINE TRANSFER 
                @if($transaction->transction_bank_ac_from)({{$transaction->transction_bank_ac_from}}) @if($row->bank_account_number )to {{$row->bank_account_number}}  ( {{$row->bank_name}})@else  @endif @endif
                @elseif($row->payment_mode == 3)
                      Saving Account Transfer @if($row->ssb_account)({{$row->ssb_account}})@endif
                @elseif($row->payment_mode == 4)
                        AUTO Transfer
                @endif
              </td>
            <td>@if($row->sub_payment_type==2)
                Advance Salary
                @elseif($row->sub_payment_type==3)
                Advance Rent
                @endif

            </td>
            <td>{{ amountINWord($row->amount) }} Only</td>
        </tr>

        <tr>
            <td></td>
            <td>Total:{{ number_format((float)($row->amount), 2, '.', '') }}</td>
            <td></td>
            <td></td>
        </tr>
        @elseif($row->payment_type == 1 || $row->payment_type == 2 || $row->payment_type == 3 ||  $row->payment_type == 4 )
        <tr>
            <td>{{ number_format((float)(($demandAdvice['investment']->maturity_payable_amount-$demandAdvice['investment']->maturity_payable_interest)), 2, '.', '') }}</td>
            <td rowspan="3">
              @if($row->payment_mode == 0)
                  CASH
              @elseif($row->payment_mode == 1) 
                  CHEQUE 
                    @php
                      $transaction = getDemandTransactionDetails(13,$row->id)
                        @endphp
                        @if($transaction->cheque_no)({{$transaction->cheque_no}})@endif
              @elseif($row->payment_mode == 2)
              @php
              $transaction = getDemandTransactionDetails(13,$row->id)
              @endphp
                  ONLINE TRANSFER @if($transaction->transction_bank_ac_from)({{$transaction->transction_bank_ac_from}}) @if($row->bank_account_number )to {{$row->bank_account_number}} ( {{$row->bank_name}})@else  @endif @endif
              @elseif($row->payment_mode == 3)
                  Saving Account Transfer @if($row->ssb_account)({{$row->ssb_account}})@endif
              @elseif($row->payment_mode == 4)
                  AUTO Transfer
              @endif
              </td>
            <td rowspan="3">{{ $headName }}</td>
            <td rowspan="3">{{ amountINWord($demandAdvice['investment']->maturity_payable_amount) }} Only</td>
        </tr>

        <tr>
          <td>{{ number_format((float)($demandAdvice['investment']->maturity_payable_interest-$demandAdvice->tds_amount), 2, '.', '') }}</td>
        </tr>

        <tr>
          @if($demandAdvice->tds_amount)
                <td>{{ number_format((float)($demandAdvice->tds_amount), 2, '.', '') }}</td>
          @else
                <td>0</td>
          @endif
          
        </tr>
        
        <tr>
          <td>Total: {{ number_format((float)(($demandAdvice['investment']->maturity_payable_amount-$demandAdvice->tds_amount)), 2, '.', '') }}</td>
          <td></td>
          <td></td>
          <td></td>
        </tr>
    @endif
         </tbody> 
        </table>
    
        <table width="100%" border="0" cellspacing="0" cellpadding="0" style="padding:20px 0 0 0;">
          <tr>
            <td>
             <table width="100%" border="0" cellspacing="0" cellpadding="10" style="border:#000000 solid 1px;">
              <tr>
                <td style="border-bottom:#000000 solid 1px;">Full Signature Of Authorised Person:</td>
                <td style="border-bottom:#000000 solid 1px;">Full Signature Of Cashier:</td>
              </tr>
              <tr>
                <td>Name Of Code Of Authorised Person: </td>
                <td>Name Of Emp Code Of Cashier:</td>
              </tr>
            </table>
            </td>
            <td style="padding:0 0 0 20px;">
            <table width="100%" border="0" cellspacing="0" cellpadding="10" style="border:#000000 solid 1px;">
              <tr>
                <td style="border-bottom:#000000 solid 1px;">RevenueStamp:</td>
                <td style="border-bottom:#000000 solid 1px;"></td>
              </tr>
              <tr>
                <td>Full Signature Of Applicant:</td>
                <td></td>
              </tr>
            </table></td>
          </tr>

        </table>
        <p style="font-weight:bold;">All The Expenses Except Maturity, Pre Maturity,S/Loan All Type Of Commission And Collection Charge</p>

        </td>
                                
      </tr>

    </table>
    <div class="row print-section">
                <div class="col-lg-12">
                  <div class="card bg-white" >            
                    <div class="card-body ">
                        <div class="row">
                            <div class="col-lg-12 text-center">
                                <button type="submit" class="btn btn-primary print-advice" onclick="printDiv('advice');" data-id="{{ $row->id }}"> Print<i class="icon-paperplane ml-2" ></i></button>
                            </div> 
                        </div>
                    </div>
                </div>
            </div>
        </div>                           

        @include('templates.admin.demand-advice.partials.print_script')

@stop
