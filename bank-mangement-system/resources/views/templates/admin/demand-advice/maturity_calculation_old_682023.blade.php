@php
    $keyVal = 0;
    $cInterest = 0;
    $regularInterest = 0;
    $total = 0;
    $collection = 0;
    $monthly = array(11);
    $rd = array(10);
    $daily = array(000);
    $preMaturity = array(4);
    $fixed = array(8,9);
    $samraddhJeevan = array(2,6);
    $moneyBack = array(3);
    $totalDeposit = 0;
    $totalInterestDeposit = 0;
    $currentInterst = 0;
    $rdPrematurity = array("D","M");
    $finacialYear=getFinacialYear();
    $investmentTds = 0;
    $tdsAmount = 0;
    $tdsPercentage = 0;
    $finacialYear = getFinacialYear();
    $fenddate    = date("Y", strtotime(convertDate($finacialYear['dateEnd'])));
    $fstrtdate    = date("Y", strtotime(convertDate($finacialYear['dateStart'])));

    $iss =0;
    
@endphp

@if(in_array($mInvestment->plan_id, $monthly))
    @if($investmentData)
        @php
            $investmentMonths = $mInvestment->tenure*12;
            $totalInvestmentAmount = App\Models\Daybook::select('investment_id','deposit','created_at')->where('investment_id',$mInvestment->id)->whereDate('created_at','>=',$mInvestment->created_at)->whereIn('transaction_type', [2,4])->sum('deposit');
            
        @endphp

        @for ($i=1; $i <= $investmentMonths ; $i++)
            @php
                $v = $i-1;
                $val = $mInvestment;
                $nDate =  date('Y-m-d', strtotime($val->created_at. ' + '.$v.' months'));
                $cMonth = date('m');
                $cYear = date('Y');
                $cuurentInterest = $mInvestment->interest_rate;
                $totalDeposit = $totalInvestmentAmount;

                $previousRecord = App\Models\Daybook::select('deposit','created_at')->whereIn('transaction_type', [2,4])->where('investment_id', $val->investment_id)->whereDate('created_at', '<', $nDate)->max('created_at');

                $sumPreviousRecordAmount = App\Models\Daybook::whereIn('transaction_type', [2,4])->where('investment_id', $val->investment_id)->whereDate('created_at', '<=', $nDate)->sum('deposit');

                $d1 = explode('-',$mInvestment->created_at);
                $d2 = explode('-',$nDate);

                $ts1 = strtotime($mInvestment->created_at);
                $ts2 = strtotime($nDate);

                $year1 = date('Y', $ts1);
                $year2 = date('Y', $ts2);

                $month1 = date('m', $ts1);
                $month2 = date('m', $ts2);

                $monthDiff = (($year2 - $year1) * 12) + ($month2 - $month1);

                if($cMonth > $d2[1] && $cYear > $d2[0]){

                    if($previousRecord){
                        $previousDate = explode('-',$previousRecord);
                        $previousMonth = $previousDate[1];
                        if(($secondMonth-$previousMonth) >= 3 && $sumPreviousRecordAmount < $mInvestment->deposite_amount*$monthDiff){
                            $defaulterInterest = 1.50;

                        }else{
                            $defaulterInterest = 0;
                        }
                    }else{
                        $defaulterInterest = 0;
                    }
                }else{
                    $defaulterInterest = 0;
                }

                $cfAmount = App\Models\Memberinvestments::where('id',$val->id)->first();
                
              
                if($val->deposite_amount*$monthDiff <= $totalDeposit){
                    $extraAmount = (int) $totalDeposit-(int) ($val->deposite_amount*$monthDiff);
                    $cfdAmount = (int) $extraAmount+(int) $cfAmount->carry_forward_amount;
                    App\Models\Memberinvestments::where('id', $val->id)->update(['carry_forward_amount'=>$cfdAmount]);

                    $aviAmount = $val->deposite_amount;
                    $total = $total+$val->deposite_amount;
                    if($monthDiff % 3 == 0 && $monthDiff != 0){
                        $total = $total+$regularInterest;
                        $cInterest = $regularInterest;
                    }else{
                        $total = $total;
                        $cInterest = 0;
                    }
                    $regularInterest = $regularInterest+(($cuurentInterest-$defaulterInterest)*$total/1200);
                    $addInterest = ($cuurentInterest-$defaulterInterest);
                    $a = -$aviAmount+$aviAmount*(pow((1+$addInterest/400), (4*$i/12)));
                    $interest = number_format((float)$a, 2, '.', '');

                    $totalInterestDeposit = $totalInterestDeposit+$interest;
                }elseif($val->deposite_amount*$monthDiff > $totalDeposit){
                    $pendingAmount =  ($val->deposite_amount*12)-$totalDeposit;
                    if((int) $cfAmount->carry_forward_amount >= (int) $pendingAmount){

                        $cfdAmount = (int) $cfAmount->carry_forward_amount-(int) $pendingAmount;
                        App\Models\Memberinvestments::where('id', $val->id)->update(['carry_forward_amount'=>$cfdAmount]);
                        $collection = (int) $totalDeposit+(int) $pendingAmount;

                    }elseif((int) $cfAmount->carry_forward_amount == (int) $pendingAmount){

                        App\Models\Memberinvestments::where('id', $val->id)->update(['carry_forward_amount'=>0]);
                        $collection = (int) $totalDeposit+(int) $cfAmount->carry_forward_amount;

                    }elseif((int) $pendingAmount > (int) $cfAmount->carry_forward_amount){

                        App\Models\Memberinvestments::where('id', $val->id)->update(['carry_forward_amount'=>0]);
                        $collection = (int) $totalDeposit+(int) $cfAmount->carry_forward_amount;
                    }

                    $checkAmount = $collection+($totalDeposit-$val->deposite_amount*($monthDiff-1));
                    if($checkAmount > 0){
                       $aviAmount = $checkAmount;

                       $total = $total+$checkAmount;
                        if($monthDiff % 3 == 0 && $monthDiff != 0){
                            $total = $total+$regularInterest;
                            $cInterest = $regularInterest;
                        }else{
                            $total = $total;
                            $cInterest = 0;
                        }
                        $regularInterest = $regularInterest+(($cuurentInterest-$defaulterInterest)*$total/1200);
                        $addInterest = ($cuurentInterest-$defaulterInterest);
                        $a = -$aviAmount+$aviAmount*(pow((1+$addInterest/400), (4*$i/12)));
                        $interest = number_format((float)$a, 2, '.', '');

                    }else{
                        $aviAmount = 0;
                        $total = 0;
                        $cuurentInterest = 0;
                        $interest = 0;
                        $addInterest = 0;
                    }
                    $totalInterestDeposit = $totalInterestDeposit+$interest;
                }

            @endphp
            <tr>
                <td>{{ $i }}</td>
                <td>{{ $aviAmount }}  &#8377</td>
                <td>{{ number_format($cInterest,2) }}</td>
                <td>{{ number_format($total,2) }}  &#8377</td>
                <td>{{ $cuurentInterest-$defaulterInterest }}</td>
                <td>{{ number_format($interest,2) }} &#8377</td>
                <td>{{ date("d/m/Y", strtotime(convertDate($nDate))) }}</td>
            </tr>
        @endfor

       
        /******** TDS Start***********/
        /******** TDS Start***********/
        @php
          $currentInterst = $totalInterestDeposit;
          tdsCalculate($currentInterst,$mInvestment,$demadAdvice);      
          $recordData = tdsCalculate($totalInterestDeposit,$mInvestment,$demadAdvice,Null,$fstrtdate,$fenddate); 
        @endphp        
        @if($paymentType == 2)
            <tr>
                <td colspan="7" class="dataTables_empty" valign="top"><b>Total Amount:</b> <span class="deposite-amount">{{ round($totalDeposit) }} &#8377</span><span class="interest-rate-amount" style="padding-left: 59%;">{{ floor($totalInterestDeposit) }} &#8377</span> <span class="total-amount" style="padding-left: 12%;">{{ round(($totalDeposit+$totalInterestDeposit)-(1.5*($totalDeposit+$totalInterestDeposit)/100)) }}</span>  &#8377</td>
            </tr>
        @else

            <tr>
                <td colspan="7" class="dataTables_empty" valign="top"><b>Total Amount:</b> <span class="deposite-amount">{{ round($totalDeposit) }} &#8377</span><span class="interest-rate-amount" style="padding-left: 59%;">{{ floor($totalInterestDeposit) }} &#8377</span> <span class="total-amount" style="padding-left: 12%;">{{ round($totalDeposit+$totalInterestDeposit) }}</span> &#8377</td>
            </tr>
        @endif

        <tr>
            <!-- <td colspan="7" class="dataTables_empty" valign="top"><b>TDS Amount:</b> <span class="tds-amount" style="padding-left: 31%;">{{ round($investmentTds) }}</span> &#8377<span class="investment-id" style="display:none;">{{ $mInvestment->id }}</span> <span class="tds-percentage" style="display:none;">{{ round($tdsPercentage) }}</span><span class="tds-percentage-on-amount" style="display:none;">{{ round($recordData['tdsAmount']) }}</span> </td> -->
            <td colspan="7" class="dataTables_empty" valign="top"><b>TDS Amount:</b> <span class="tds-amount" style="padding-left: 31%;">{{ round($recordData['tdsAmount']) }}</span> &#8377<span class="investment-id" style="display:none;">{{ $mInvestment->id }}</span> <span class="tds-percentage" style="display:none;">{{ round($recordData['tdsPercentage']) }}</span><span class="tds-percentage-on-amount" style="display:none;">{{ round($recordData['tdsAmount']) }}</span></td>
</tr>
        </tr>

        @if($paymentType == 2)
            @php
                $fAmount = ($totalDeposit+$totalInterestDeposit)-(1.5*($totalDeposit+$totalInterestDeposit)/100);
            @endphp
        @else
            @php
                $fAmount = $totalDeposit+$totalInterestDeposit;
            @endphp
        @endif

        <tr>
            <td colspan="7" class="dataTables_empty" valign="top"><b>Final Amount:</b> <span class="final-amount" style="padding-left: 31%;">{{ round($fAmount-$investmentTds) }}</span> &#8377</td>
        </tr>

        {{--@if($idProofDetail->first_id_type_id==5 )--}}

            @php 
                if($formGShow !=NULL){
                    $formgID = $formGShow->id;
                    $LatestformGYear = $formGShow->year;
                    $formGYear = strtotime($LatestformGYear);
                    $FinalformGYear = date('Y', $formGYear);
                }
                else{
                    $formgID ='';
                    $FinalformGYear ='';
                }
                
                $cDate = $globalDate;
                $currentFinancialDate = strtotime($cDate);
                $currentFinancialYear = date('Y', $currentFinancialDate);

            

            @endphp 

            @if(!empty($formgID)) 

                @if( $FinalformGYear == $currentFinancialYear) 
                <tr style="text-align:center">
                    <td colspan="12">
                        
                    <a href="{{URL::to("admin/form_g/".$mInvestment->member_id."")}}" target="_blank">Check 15G/15H Status</a></td>
                    
                </tr>

                @endif

            @endif

        {{--@endif--}}

    @else
    <tr>
        <td colspan="7" class="dataTables_empty" valign="top">No data available in table</td>
    </tr>
    @endif
    

    @elseif(in_array($mInvestment->plan->plan_category_code, $rdPrematurity))
    @if($investmentData)
        @php
        $re = maturityCalculation($mInvestment,NULL,$investmentMonths,$ActualInterest);
        $records = \App\Models\MaturityCalculate::where('investment_id',$mInvestment->id)->get();
        $lastKey = $records->keys()->last();
        $totalInvestmentAmount = $records[$lastKey]->total_amount;
        @endphp
    @foreach($records as $i =>$reData)
    <tr>
        <td>{{ $i + 1 }}</td>
        <td>{{ $reData->deposite }}  &#8377</td>
        <td>{{ number_format($reData->compound_interest,2) }}</td>
        <td>{{ number_format($reData->total,2) }}  &#8377</td>
        <td>{{ $reData->interest_rate}}</td>
        <td>{{$reData->interest_rate_amount }} &#8377</td>
        <td>{{ date("d/m/Y", strtotime(convertDate($reData->deposite_date))) }}</td>
    </tr>
    

    @endforeach
    <?php
    $recordData = tdsCalculate($records->sum('interest_rate_amount'),$mInvestment,$demadAdvice,Null,$fstrtdate,$fenddate); 
    // $checkYear = date("Y", strtotime(convertDate($demadAdvice->date)));

    // $investmentTds = App\Models\MemberInvestmentInterestTds::where('investment_id',$mInvestment->id)->sum('tdsamount_on_interest');

    // $existsInterst = App\Models\MemberInvestmentInterest::where('investment_id',$mInvestment->id)->sum('interest_amount');

    // $formG = App\Models\Form15G::where('member_id',$mInvestment->member_id)->where('year',$checkYear)->whereNotNull('file')->first();

    // if($formG){
    //     $tdsAmount = 0;
    //     $tdsPercentage = 0;
    //     $investmentTds = 0;
    // }else{

    //     $memberData = getMemberData($mInvestment->member_id);

    //     $diff = abs(strtotime($demadAdvice->date) - strtotime($memberData['dob']));
    //     $years = floor($diff / (365*60*60*24));

    //     if($years >= 60){
    //     $tdsDetail = App\Models\TdsDeposit::where('type',2)->where('start_date','<',$demadAdvice->date)->first();
    //     }else{
    //         $penCard = get_member_id_proof($mInvestment->member_id,5);
    //         if($penCard){
    //             $tdsDetail = App\Models\TdsDeposit::where('type',1)->where('start_date','<',$demadAdvice->date)->first();
    //         }else{
    //             $tdsDetail = App\Models\TdsDeposit::where('type',5)->where('start_date','<',$demadAdvice->date)->first();

    //         }
    //     }

    //     if($tdsDetail){
    //         $tdsAmount = $tdsDetail->tds_amount;
    //         $tdsPercentage = $tdsDetail->tds_per;
    //         $currentInterst=   $totalInterestDeposit;
    //         //$currentInterst = $interest-$existsInterst;


    //         if($currentInterst > $tdsAmount){
    //             $tdsAmountonInterest = $tdsPercentage*$currentInterst/100;
    //             $investmentTds = $investmentTds+$tdsAmountonInterest;
    //         }else{
    //             $investmentTds = 0;
    //         }
    //     }else{
    //         $tdsAmount = 0;
    //         $tdsPercentage = 0;
    //         $investmentTds = 0;
    //     }
    // }
    ?>

    /******** TDS Start***********/
    <tr>

<td colspan="7" class="dataTables_empty" valign="top"><b>Total Amount:</b> <span class="deposite-amount" style="padding-left: 32%;">{{ floor($totalInvestmentAmount) }} &#8377</span><span class="interest-rate-amount" style="padding-left: 27%;">{{ floor($records->sum('interest_rate_amount')) }} &#8377</span> <span class="total-amount" style="padding-left: 13%;">{{ floor($totalInvestmentAmount+$records->sum('interest_rate_amount')) }}</span> &#8377</td>

</tr>

<tr>
<td colspan="7" class="dataTables_empty" valign="top"><b>TDS Amount:</b> <span class="tds-amount" style="padding-left: 31%;">{{ round($recordData['tdsAmount']) }}</span> &#8377<span class="investment-id" style="display:none;">{{ $mInvestment->id }}</span> <span class="tds-percentage" style="display:none;">{{ round($recordData['tdsPercentage']) }}</span><span class="tds-percentage-on-amount" style="display:none;">{{ round($recordData['tdsAmount']) }}</span></td>
</tr>

<tr>
<td colspan="7" class="dataTables_empty" valign="top"><b>Final Amount:</b> <span class="final-amount" style="padding-left: 31%;">{{ floor(($totalInvestmentAmount+$records->sum('interest_rate_amount'))-$recordData['tdsAmount']) }}</span> &#8377</td>
</tr>

    <!-- <tr>
    <td colspan="7" class="dataTables_empty" valign="top"><b>Total Amount:</b> <span class="deposite-amount" style="padding-left: 31%;">{{ round($totalDeposit) }} &#8377</span><span class="interest-rate-amount" style="padding-left: 26%;">{{ round($totalInterestDeposit) }} &#8377</span> <span class="total-amount" style="padding-left: 12%;">{{ round($totalDeposit+$totalInterestDeposit) }}</span> &#8377</td>
    </tr>

    <tr>
    <td colspan="7" class="dataTables_empty" valign="top"><b>TDS Amount:</b> <span class="tds-amount" style="padding-left: 31%;">{{ round($investmentTds) }}</span> &#8377<span class="investment-id" style="display:none;">{{ $mInvestment->id }}</span></td>
    </tr>

    <tr>
    <td colspan="7" class="dataTables_empty" valign="top"><b>Final Amount:</b> <span class="final-amount" style="padding-left: 31%;">{{ round(($totalDeposit+$totalInterestDeposit)-$investmentTds) }}</span> &#8377<span class="investment-id" style="display:none;">{{ $mInvestment->id }}</span> <span class="tds-percentage" style="display:none;">{{ round($tdsPercentage) }}</span><span class="tds-percentage-on-amount" style="display:none;">{{ round($tdsAmount) }}</span></td>
    </tr> -->
        {{--@if($idProofDetail->first_id_type_id==5 )--}}

            @php 
                if($formGShow !=NULL){
                    $formgID = $formGShow->id;
                    $LatestformGYear = $formGShow->year;
                    $formGYear = strtotime($LatestformGYear);
                    $FinalformGYear = date('Y', $formGYear);
                }
                else{
                    $formgID ='';
                    $FinalformGYear ='';
                }
                
                $cDate = $globalDate;
                $currentFinancialDate = strtotime($cDate);
                $currentFinancialYear = date('Y', $currentFinancialDate);

            

            @endphp 

            @if(!empty($formgID)) 

                @if( $FinalformGYear == $currentFinancialYear) 
                <tr style="text-align:center">
                    <td colspan="12">
                        
                    <a href="{{URL::to("admin/form_g/".$mInvestment->member_id."")}}" target="_blank">Check 15G/15H Status</a></td>
                    
                </tr>

                @endif

            @endif

        {{--@endif--}}

    @else
    <td colspan="3" class="dataTables_empty" valign="top">No data available in table</td>
    @endif
    @elseif(in_array($mInvestment->plan_id, $daily))
    @if($investmentData)
        @php
      
        /* $records = \App\Models\MaturityCalculate::where('investment_id',$mInvestment->id)->get();
        $lastKey = $records->keys()->last();
        $totalInvestmentAmount = $records[$lastKey]->total_amount; */
            $investmentTds=0;
            $tdsPercentage = 0;
            $tdsAmount  = 0;
            $cMonth = date('m');
            $cYear = date('Y');
           /* $cuurentInterest = $mInvestment->interest_rate;*/
           
            $deathClaim =($subPaymentType == 5 ) ?  1 : 0;
            $tenureMonths = $mInvestment->tenure*12;    
            $re = maturityCalculation($mInvestment,NULL,$investmentMonths,$ActualInterest);

            $records = \App\Models\MaturityCalculate::where('investment_id',$mInvestment->id)->get();
        $lastKey = $records->keys()->last();
        $totalInvestmentAmount = $records[$lastKey]->total_amount;   
          dd($records);
            foreach($interestData[$deathClaim] as $key => $iData)
            {
               
                if($iData->tenure == ($tenureMonths) )
                {
                    $cuurentInterest = $mInvestment['member']->special_category_id != 0 ? $iData->special_interest : $iData->interest;
                }
                   
                 
            }
          
            $curDate = date("Y-m-d", strtotime($mInvestment->created_at));
           
            $i = 0;

            $startdate = date("Y-m-d", strtotime($mInvestment->created_at));
            $totalCommuAmount = 0;
            for ($i = 0; $i <= $tenureMonths; $i++){
                /*$integer = $i+1;
                $createdMonth = date("m", strtotime($mInvestment->created_at));
                $createdYear = date("Y", strtotime($mInvestment->created_at));
                if($createdMonth > $integer){
                    $month = $createdMonth+$i;
                    $year = $createdYear;
                }elseif($integer == $createdMonth){
                    $month = 1;
                    $year = $createdYear+1;
                }elseif(($i+1) > $createdMonth){
                    $month = ($integer-$createdMonth)+1;
                    $year = $createdYear+1;
                }*/


                $m = $i+1;
            
                $newdate = date("Y-m-d",  strtotime("".$m." month", strtotime($curDate)));


                if($startdate==date("Y-m-t", strtotime($newdate)))
                {
                    $newdate = date("Y-m-t", strtotime($newdate));
                }
                else{
                    $newdate = date("Y-m-d",  strtotime("-1 day", strtotime($newdate)));;

                }


                $enddate = $newdate;

                $implodeArray = explode('-',$newdate);
                
                $year = $implodeArray[0];

                $cdate = $mInvestment->created_at;
                $cexplodedate = explode('-',$mInvestment->created_at);
                if(($cexplodedate[1]+$i) > 12){
                    $month = ($cexplodedate[1]+$i)-12;
                }else{
                    $month = $cexplodedate[1]+$i;
                }


           

             

                if(($i+1) == 12){
                   /* $fRecord = App\Models\Daybook::where('investment_id', $mInvestment->id)
                    ->whereMonth('created_at', $month)->whereYear('created_at', $year)->first();

                    if($fRecord){
                        $total = App\Models\Daybook::where('account_no', $mInvestment->account_number)->whereIn('transaction_type', [2,4])->where('id','>=',$fRecord->id)->sum('deposit');
                    }else{
                       $total = App\Models\Daybook::where('investment_id', $mInvestment->id)
                    ->whereMonth('created_at', $month)->whereYear('created_at', $year)->sum('deposit');
                    }*/
                    $enddate = $enddate;

                }   
                if($i == 0)
                {
                    $total = App\Models\Daybook::where('account_no', $mInvestment->account_number)->whereIn('transaction_type', [2,4])->where(\DB::raw('DATE(created_at)'),'>=',$startdate)->where(\DB::raw('DATE(created_at)'),'<=',$enddate)->where('is_deleted','0')->sum('deposit');
                }       
                else{
                    $total = App\Models\Daybook::where('account_no', $mInvestment->account_number)->whereIn('transaction_type', [2,4])->where(\DB::raw('DATE(created_at)'),'>',$startdate)->where(\DB::raw('DATE(created_at)'),'<=',$enddate)->where('is_deleted','0')->sum('deposit');
                }
               
                   


                $totalCommuAmount = $totalCommuAmount + $mInvestment->deposite_amount*25;

                $totalDeposit = $totalDeposit+$total;
                $startdate = $newdate;
                $countDays = App\Models\Daybook::where('investment_id', $mInvestment->id)->whereIn('transaction_type', [2,4])->whereMonth('created_at', $month)->whereYear('created_at', $year)->count();

                if($totalDeposit < $totalCommuAmount){
                    $defaulterInterest = 1.50;
                }else{
                    $defaulterInterest = 0;
                }



                if($total > 0){
                    $aviAmount = $mInvestment->deposite_amount;
                }else{
                    $aviAmount = 0;
                }

                if(($tenureMonths-$i) == 0){
                    $aviAmount = 0;
                    $interestRate = 0;
                }else{
                    $interestRate = $cuurentInterest-$defaulterInterest;
                }
                $interest = ((($cuurentInterest-$defaulterInterest)*$totalDeposit)/1200);
                    /*if($tenureMonths == 12){
                        $interest = ((($cuurentInterest-$defaulterInterest)*$totalDeposit)/1200);

                    }elseif($tenureMonths == 24){
                        $interest = ((($cuurentInterest-$defaulterInterest)*$totalDeposit)/1200);
                    }elseif($tenureMonths == 36){
                        $interest = ((($cuurentInterest-$defaulterInterest)*$totalDeposit)/1200);
                    }elseif($tenureMonths == 60){
                        $interest = ((($cuurentInterest-$defaulterInterest)*$totalDeposit)/1200);
                    }*/

                    if(($tenureMonths-$i) == 0){
                        $interest = 0;
                    }
                    $totalInterestDeposit = $totalInterestDeposit + $interest;



                @endphp
                <tr>
                    <td>{{ $i+1 }}</td>
                    <td>{{ $aviAmount }} &#8377</td>
                    <td>{{ 0 }}</td>
                    <td>{{ $total }} &#8377</td>
                    <td>{{ $interestRate }}</td>


                    <td>{{ number_format($interest,2) }} &#8377</td>
                    <td>{{ date("d/m/Y", strtotime($newdate)) }}</td>
                </tr>


        /******** TDS Start***********/
        <?php
         $recordData = tdsCalculate($totalInterestDeposit,$mInvestment,$demadAdvice,Null,$fstrtdate,$fenddate); 
            // $checkYear = date("Y", strtotime(convertDate($demadAdvice->date)));
            // $fenddate    = date("Y", strtotime(convertDate($finacialYear['dateEnd'])));      
            // $investmentTds = App\Models\MemberInvestmentInterestTds::where('investment_id',$mInvestment->id)->sum('tdsamount_on_interest');

            // $existsInterst = App\Models\MemberInvestmentInterest::where('investment_id',$mInvestment->id)->sum('interest_amount');

            // $formG = App\Models\Form15G::where('member_id',$mInvestment->member_id)->where('year',$checkYear)->orwhere('max_year',$checkYear)->whereNotNull('file')->first();

            // if($formG){
            //     $tdsAmount = 0;
            //     $tdsPercentage = 0;
            //     $investmentTds = 0;
            // }else{

            //     $memberData = getMemberData($mInvestment->member_id);

            //     $diff = abs(strtotime($demadAdvice->date) - strtotime($memberData['dob']));
            //     $years = floor($diff / (365*60*60*24));

            //     if($years >= 60){
            //        $tdsDetail = App\Models\TdsDeposit::where('type',2)->where('start_date','<',$demadAdvice->date)->first();
            //     }else{
            //         $penCard = get_member_id_proof($mInvestment->member_id,5);
            //         if($penCard){
            //             $tdsDetail = App\Models\TdsDeposit::where('type',1)->where('start_date','<',$demadAdvice->date)->first();
            //         }else{
            //             $tdsDetail = App\Models\TdsDeposit::where('type',5)->where('start_date','<',$demadAdvice->date)->first();

            //         }
            //     }

            //     if($tdsDetail){
            //         $tdsAmount = $tdsDetail->tds_amount;
            //         $tdsPercentage = $tdsDetail->tds_per;
            //          $currentInterst=   $totalInterestDeposit;
            //         //$currentInterst = $interest-$existsInterst;


            //         if($currentInterst > $tdsAmount){
            //             $tdsAmountonInterest = $tdsPercentage*$currentInterst/100;
            //             $investmentTds = $investmentTds+$tdsAmountonInterest;
            //         }else{
            //             $investmentTds = 0;
            //         }
            //     }else{
            //         $tdsAmount = 0;
            //         $tdsPercentage = 0;
            //         $investmentTds = 0;
            //     }
            // }
        ?>
         @php

            }
        @endphp
        /******** TDS Start***********/

        <tr>
            <td colspan="7" class="dataTables_empty" valign="top"><b>Total Amount:</b> <span class="deposite-amount" style="padding-left: 31%;">{{ round($totalDeposit) }} &#8377</span><span class="interest-rate-amount" style="padding-left: 26%;">{{ floor($totalInterestDeposit) }} &#8377</span> <span class="total-amount" style="padding-left: 12%;">{{ round($totalDeposit+$totalInterestDeposit) }}</span> &#8377</td>
        </tr>

        <tr>
            <!-- <td colspan="7" class="dataTables_empty" valign="top"><b>TDS Amount:</b> <span class="tds-amount" style="padding-left: 31%;">{{ round($investmentTds) }}</span> &#8377<span class="investment-id" style="display:none;">{{ $mInvestment->id }}</span></td> -->
            <td colspan="7" class="dataTables_empty" valign="top"><b>TDS Amount:</b> <span class="tds-amount" style="padding-left: 31%;">{{ round($recordData['tdsAmount']) }}</span> &#8377<span class="investment-id" style="display:none;">{{ $mInvestment->id }}</span> <span class="tds-percentage" style="display:none;">{{ round($recordData['tdsPercentage']) }}</span><span class="tds-percentage-on-amount" style="display:none;">{{ round($recordData['tdsAmount']) }}</span></td>
        </tr>

        <tr>
            <td colspan="7" class="dataTables_empty" valign="top"><b>Final Amount:</b> <span class="final-amount" style="padding-left: 31%;">{{ round(($totalDeposit+$totalInterestDeposit)-$recordData['tdsAmount']) }}</span> &#8377<span class="investment-id" style="display:none;">{{ $mInvestment->id }}</span> <span class="tds-percentage" style="display:none;">{{ round($recordData['tdsPercentage']) }}</span><span class="tds-percentage-on-amount" style="display:none;">{{ round($recordData['tdsAmount']) }}</span></td>
        </tr>


        {{--@if($idProofDetail->first_id_type_id==5 )--}}

            @php 
                if($formGShow !=NULL){
                    $formgID = $formGShow->id;
                    $LatestformGYear = $formGShow->year;
                    $formGYear = strtotime($LatestformGYear);
                    $FinalformGYear = date('Y', $formGYear);
                }
                else{
                    $formgID ='';
                    $FinalformGYear ='';
                }
                
                $cDate = $globalDate;
                $currentFinancialDate = strtotime($cDate);
                $currentFinancialYear = date('Y', $currentFinancialDate);

            

            @endphp 

            @if(!empty($formgID)) 

                @if( $FinalformGYear == $currentFinancialYear) 
                <tr style="text-align:center">
                    <td colspan="12">
                        
                    <a href="{{URL::to("admin/form_g/".$mInvestment->member_id."")}}" target="_blank">Check 15G/15H Status</a></td>
                    
                </tr>

                @endif

            @endif

        {{--@endif--}}

    @else
        <td colspan="3" class="dataTables_empty" valign="top">No data available in table</td>
    @endif
    @elseif(in_array($mInvestment->plan_id, $preMaturity))
    @if($investmentData)

        @php
            $totalInvestmentAmount = App\Models\Daybook::select('investment_id','deposit','created_at')->where('investment_id',$mInvestment->id)->whereDate('created_at','>=',$mInvestment->created_at)->whereIn('transaction_type', [2,4])->sum('deposit');

            $cDate = date('Y-m-d');
            $ts1 = strtotime($mInvestment->created_at);
            $ts2 = strtotime($cDate);
            $year1 = date('Y', $ts1);
            $year2 = date('Y', $ts2);
            $month1 = date('m', $ts1);
            $month2 = date('m', $ts2);
            $monthDiff = (($year2 - $year1) * 12) + ($month2 - $month1);

           
            $deathClaim =($subPaymentType == 5 ) ?  1 : 0;
            $from = ($globalDate >=  $mInvestment->maturity_date) ? \Carbon\Carbon::parse($mInvestment->maturity_date ) : \Carbon\Carbon::parse($globalDate);
            $to = \Carbon\Carbon::parse($mInvestment->created_at);
    
            $investmentMonths =  $to->diffInMonths($from);
             $cuurentInterest = $ActualInterest;
           

            if($mInvestment->plan_id == 4){
                
                /* if($cDate < $maturity_date && $monthDiff != 120){
                    $defaulterInterest = 1.50;
                }else{
                    $defaulterInterest = 0;
                } */

                $defaulterInterest = 0;

                $irate = ($cuurentInterest-$defaulterInterest) / 1;

                $year = $investmentMonths / 12;
                $year = (float)$year;
                $rate = pow((1 + $irate / 100), $year);
                $Rate =substr($rate, 0,strpos($rate, '.') + 3);
                $result =  ( $mInvestment->deposite_amount*(pow((1 + $irate / 100), $year))-$mInvestment->deposite_amount);
                
               
            }else{

               /* if($cDate < $maturity_date && $monthDiff != 60){
                    $defaulterInterest = 1.50;
                }else{
                    $defaulterInterest = 0;
                }*/
                $defaulterInterest = 0;
                $irate = ($cuurentInterest-$defaulterInterest) / 1;

                $year = $monthDiff / 12;

                $maturity=0;
                $freq = 4;
                $year = ($mInvestment->tenure);

               

                $result =  ( $mInvestment->deposite_amount*(pow((1 + $irate / 100), $year))-$mInvestment->deposite_amount); 
                /* for($i=1; $i<=$monthDiff;$i++){
                    $rmaturity = ($mInvestment->deposite_amount*(pow((1+(($irate/100)/$freq)), $freq*(($monthDiff-$i+1)/12))));
                    $maturity = $maturity+$rmaturity;
                } */
                $result =  $maturity-$totalInvestmentAmount;
            }

        @endphp

        <tr>

            <td>{{ 1 }}</td>

            <td>{{ $totalInvestmentAmount }} &#8377</td>

            <td>{{ 0 }}</td>

            <td id="totalInvest" data-amount ="{{$totalInvestmentAmount}}">{{ $totalInvestmentAmount }} &#8377</td>

            <td>{{ $cuurentInterest-$defaulterInterest }}</td>

            <td contenteditable="true" id="int">{{ round($result) }} &#8377</td>

            <td>{{ date("d/m/Y", strtotime(convertDate($mInvestment->created_at))) }}</td>

        </tr>

        /******** TDS Start***********/
        <?php

            $recordData = tdsCalculate($result,$mInvestment,$demadAdvice,Null,$fstrtdate,$fenddate);  
          
            // $checkYear = date("Y", strtotime(convertDate($demadAdvice->date)));

            // $investmentTds = App\Models\MemberInvestmentInterestTds::where('investment_id',$mInvestment->id)->sum('tdsamount_on_interest');

            // $existsInterst = App\Models\MemberInvestmentInterest::where('investment_id',$mInvestment->id)->sum('interest_amount');

            // $formG = App\Models\Form15G::where('member_id',$mInvestment->member_id)->where('year',$checkYear)->orwhere('max_year',$checkYear)->whereNotNull('file')->first();
            // if($formG){
            //     $tdsAmount = 0;
            //     $tdsPercentage = 0;
            //     $investmentTds = 0;
            // }else{

            //     $memberData = getMemberData($mInvestment->member_id);
            //     $diff = abs(strtotime($demadAdvice->date) - strtotime($memberData['dob']));
            //     $years = floor($diff / (365*60*60*24));

            //     if($years >= 60){
            //        $tdsDetail = App\Models\TdsDeposit::where('type',2)->where('start_date','<',$demadAdvice->date)->first();
            //     }else{
            //         $penCard = get_member_id_proof($mInvestment->member_id,5);

            //         if($penCard){
            //             $tdsDetail = App\Models\TdsDeposit::where('type',1)->where('start_date','<',$demadAdvice->date)->first();
            //         }else{
            //             $tdsDetail = App\Models\TdsDeposit::where('type',5)->where('start_date','<',$demadAdvice->date)->first();
            //         }
            //     }

            //     if($tdsDetail){
            //         $tdsAmount = $tdsDetail->tds_amount;
            //         $tdsPercentage = $tdsDetail->tds_per;

            //         $currentInterst = $result-$existsInterst;

            //         if($currentInterst > $tdsAmount){
            //             $tdsAmountonInterest = $tdsPercentage*$currentInterst/100;
            //             $investmentTds = $investmentTds+$tdsAmountonInterest;
            //         }else{
            //             $investmentTds = 0;
            //         }
            //     }else{
            //         $tdsAmount = 0;
            //         $tdsPercentage = 0;
            //         $investmentTds = 0;
            //         $currentInterst = 0;
            //     }

            // }
        ?>
        /******** TDS Start***********/
        <script>
            $(document).ready(function(){
                var status = $('#int').attr('contenteditable');

                $('#int').on('keyup',function(){
                var totalAmount = ($('#totalInvest').attr('data-amount'));
                var number = ($(this).text()).replace('₹','');
                var tds = '<?php echo $investmentTds; ?>';
                var curInterest = '<?php echo $currentInterst; ?>';
                var tdsAmount = '<?php echo $tdsAmount; ?>';
                var tdsPercentage = '<?php echo $tdsPercentage; ?>';
                if(curInterest > tdsAmount)
                {
                       var tdsAmountonInterest =  tdsPercentage + curInteres/100;
                       var investmentTds = tds+tdsAmountonInterest;
                }
                else{
                    var investmentTds = 0;
                }


                var result = parseInt(totalAmount) + parseInt(number) - parseInt(investmentTds);

                $('.interest-rate-amount').html(number);
                $('.total-amount').html(result);
                $('.final-amount').html(result);
                $('.tds-amount').html(investmentTds);
                $('.tds-percentage').html(tdsPercentage);
                $('.tds-percentage-on-amount').html(tdsAmount);

                })

            })
    </script>

        <tr>

            <td colspan="7" class="dataTables_empty" valign="top"><b>Total Amount:</b> <span class="deposite-amount" style="padding-left: 32%;">{{ round($totalInvestmentAmount) }} &#8377</span><span class="interest-rate-amount" style="padding-left: 27%;">{{ floor($result) }} &#8377</span> <span class="total-amount" style="padding-left: 13%;">{{ round($totalInvestmentAmount+$result) }}</span> &#8377</td>

        </tr>

        <tr>
            <td colspan="7" class="dataTables_empty" valign="top"><b>TDS Amount:</b> <span class="tds-amount" style="padding-left: 31%;">{{ round($recordData['tdsAmount']) }}</span> &#8377<span class="investment-id" style="display:none;">{{ $mInvestment->id }}</span> <span class="tds-percentage" style="display:none;">{{ round($recordData['tdsPercentage']) }}</span><span class="tds-percentage-on-amount" style="display:none;">{{ round($recordData['tdsAmount']) }}</span></td>
        </tr>

        <tr>
            <td colspan="7" class="dataTables_empty" valign="top"><b>Final Amount:</b> <span class="final-amount" style="padding-left: 31%;">{{ round(($totalInvestmentAmount+$result)-$recordData['tdsAmount']) }}</span> &#8377</td>
        </tr>

        {{--@if($idProofDetail->first_id_type_id==5 )--}}

            @php 
                if($formGShow !=NULL){
                    $formgID = $formGShow->id;
                    $LatestformGYear = $formGShow->year;
                    $formGYear = strtotime($LatestformGYear);
                    $FinalformGYear = date('Y', $formGYear);
                }
                else{
                    $formgID ='';
                    $FinalformGYear ='';
                }
                
                $cDate = $globalDate;
                $currentFinancialDate = strtotime($cDate);
                $currentFinancialYear = date('Y', $currentFinancialDate);

            

            @endphp 

            @if(!empty($formgID)) 

                @if( $FinalformGYear == $currentFinancialYear) 
                <tr style="text-align:center">
                    <td colspan="12">
                        
                    <a href="{{URL::to("admin/form_g/".$mInvestment->member_id."")}}" target="_blank">Check 15G/15H Status</a></td>
                    
                </tr>

                @endif

            @endif

        {{--@endif--}}

    @else

        <td colspan="3" class="dataTables_empty" valign="top">No data available in table</td>

    @endif
@elseif(in_array($mInvestment->plan_id, $fixed))
    @if($investmentData)
        @php
            $cDate = date('Y-m-d');
            $cYear = date('Y');
            /*$cuurentInterest = $mInvestment->interest_rate;*/
            $deathClaim =($subPaymentType == 5 ) ?  1 : 0;
            foreach($interestData[$deathClaim] as $key => $iData)
            {
                    if($iData->tenure == ($mInvestment->tenure*12) )
                    {
                        $cuurentInterest = $mInvestment['member']->special_category_id != 0 ? $iData->special_interest : $iData->interest;
                    }
            }
           

            if($cDate < $maturity_date){
                $defaulterInterest = 1.50;
            }else{
                $defaulterInterest = 0;
            }
        @endphp
        <tr>
            <td>{{ 1 }}</td>
            <td>{{ $mInvestment->deposite_amount }} &#8377</td>
            <td>{{ 0 }}</td>
            <td>{{ $mInvestment->deposite_amount }} &#8377</td>
            <td>{{ $cuurentInterest-$defaulterInterest }}</td>

            @php
                if($mInvestment->plan_id == 8){
                    $result =  0;
                }else{
                    $irate = ($cuurentInterest-$defaulterInterest) / 1;

                    $year = $mInvestment->tenure;

                    $result =  ( $mInvestment->deposite_amount*(pow((1 + $irate / 100), $year))-$mInvestment->deposite_amount);
                    $result =  ( $mInvestment->deposite_amount*(pow((1 + $irate / 100), $year))-$mInvestment->deposite_amount);
                }
            @endphp

            <td >{{ round($result) }} &#8377</td>
            <td>{{ date("d/m/Y", strtotime(convertDate($mInvestment->created_at))) }}</td>
        </tr>

        /******** TDS Start***********/
        <?php
         $recordData = tdsCalculate($result,$mInvestment,$demadAdvice,Null,$fstrtdate,$fenddate); 
            // $checkYear = date("Y", strtotime(convertDate($demadAdvice->date)));

            // $investmentTds = App\Models\MemberInvestmentInterestTds::where('investment_id',$mInvestment->id)->sum('tdsamount_on_interest');

            // $existsInterst = App\Models\MemberInvestmentInterest::where('investment_id',$mInvestment->id)->sum('interest_amount');

            // $formG = App\Models\Form15G::where('member_id',$mInvestment->member_id)->where('year',$checkYear)->orwhere('max_year',$checkYear)->whereNotNull('file')->first();
            // if($formG){
            //     $tdsAmount = 0;
            //     $tdsPercentage = 0;
            //     $investmentTds = 0;
            // }else{

            //     $memberData = getMemberData($mInvestment->member_id);
            //     $diff = abs(strtotime($demadAdvice->date) - strtotime($memberData['dob']));
            //     $years = floor($diff / (365*60*60*24));

            //     if($years >= 60){
            //        $tdsDetail = App\Models\TdsDeposit::where('type',2)->where('start_date','<',$demadAdvice->date)->first();
            //     }else{
            //         $penCard = get_member_id_proof($mInvestment->member_id,5);
            //         if($penCard){
            //             $tdsDetail = App\Models\TdsDeposit::where('type',1)->where('start_date','<',$demadAdvice->date)->first();
            //         }else{
            //             $tdsDetail = App\Models\TdsDeposit::where('type',5)->where('start_date','<',$demadAdvice->date)->first();
            //         }
            //     }

            //     if($tdsDetail){
            //         $tdsAmount = $tdsDetail->tds_amount;
            //         $tdsPercentage = $tdsDetail->tds_per;



            //         if($mInvestment->plan_id == 9)
            //         {
            //             $currentInterst = $result;
            //             if($currentInterst > $tdsAmount){
            //                 $amount = $currentInterst ;
            //                 $tdsAmountonInterest = $tdsPercentage*$amount/100;
            //                 $investmentTds = $investmentTds+$tdsAmountonInterest;
            //             }else{
            //                 $investmentTds = 0;
            //             }
            //         }
            //         else{
            //             $currentInterst = $result-$existsInterst;
            //             if($currentInterst > $tdsAmount){
            //                 $tdsAmountonInterest = $tdsPercentage*$currentInterst/100;
            //                 $investmentTds = $investmentTds+$tdsAmountonInterest;
            //             }else{
            //                 $investmentTds = 0;
            //             }
            //         }
            //     }else{
            //         $tdsAmount = 0;
            //         $tdsPercentage = 0;
            //         $investmentTds = 0;
            //     }
            // }
        ?>
        /******** TDS Start***********/

        <tr>
            <td colspan="7" class="dataTables_empty" valign="top"><b>Total Amount:</b> <span class="deposite-amount" style="padding-left: 32%;">{{ round($mInvestment->deposite_amount) }} &#8377</span><span class="interest-rate-amount" style="padding-left: 27%;">{{ floor($result) }} &#8377</span> <span class="total-amount" style="padding-left: 13%;">{{ round($mInvestment->deposite_amount+$result) }}</span> &#8377</td>
        </tr>

        <tr>
            <!-- <td colspan="7" class="dataTables_empty" valign="top"><b>TDS Amount:</b> <span class="tds-amount" style="padding-left: 31%;">{{ round($investmentTds) }}</span> &#8377<span class="investment-id" style="display:none;">{{ $mInvestment->id }}</span> <span class="tds-percentage" style="display:none;">{{ round($tdsPercentage) }}</span><span class="tds-percentage-on-amount" style="display:none;">{{ round($tdsAmount) }}</span></td> -->
            <td colspan="7" class="dataTables_empty" valign="top"><b>TDS Amount:</b> <span class="tds-amount" style="padding-left: 31%;">{{ round($recordData['tdsAmount']) }}</span> &#8377<span class="investment-id" style="display:none;">{{ $mInvestment->id }}</span> <span class="tds-percentage" style="display:none;">{{ round($recordData['tdsPercentage']) }}</span><span class="tds-percentage-on-amount" style="display:none;">{{ round($recordData['tdsAmount']) }}</span></td>
        </tr>

        <tr>
            <td colspan="7" class="dataTables_empty" valign="top"><b>Final Amount:</b> <span class="final-amount" style="padding-left: 31%;">{{ round(($mInvestment->deposite_amount+$result)-$recordData['tdsAmount']) }}</span> &#8377</td>
        </tr>


        {{--@if($idProofDetail->first_id_type_id==5 )--}}

            @php 
                if($formGShow !=NULL){
                    $formgID = $formGShow->id;
                    $LatestformGYear = $formGShow->year;
                    $formGYear = strtotime($LatestformGYear);
                    $FinalformGYear = date('Y', $formGYear);
                }
                else{
                    $formgID ='';
                    $FinalformGYear ='';
                }
                
                $cDate = $globalDate;
                $currentFinancialDate = strtotime($cDate);
                $currentFinancialYear = date('Y', $currentFinancialDate);



            @endphp 

            @if(!empty($formgID)) 

                @if( $FinalformGYear == $currentFinancialYear) 
                <tr style="text-align:center">
                    <td colspan="12">
                        
                    <a href="{{URL::to("admin/form_g/".$mInvestment->member_id."")}}" target="_blank">Check 15G/15H Status</a></td>
                    
                </tr>

                @endif

            @endif

        {{--@endif--}}

    @else
        <td colspan="3" class="dataTables_empty" valign="top">No data available in table</td>
    @endif
@elseif(in_array($mInvestment->plan_id, $samraddhJeevan))
    @if($investmentData)
        @php
            $cDate = $demadAdvice->date;
            $investmentMonths = $mInvestment->tenure*12;
            $totalInvestmentAmount = App\Models\Daybook::select('investment_id','deposit','created_at')->where('investment_id',$mInvestment->id)->whereDate('created_at','>=',$mInvestment->created_at)->whereIn('transaction_type', [2,4])->sum('deposit');
        @endphp
        @if($cDate >= $maturity_date)
            @php
                $cuurentInterest = 6;
                $depositAmount = ($mInvestment->deposite_amount*12)*$mInvestment->tenure;
                $result = $cuurentInterest*($depositAmount) / 100;
            @endphp
            <tr>
                <td>{{ 1 }}</td>
                <td>{{ $depositAmount }} &#8377</td>
                <td>{{ 0 }}</td>
                <td>{{ $depositAmount }} &#8377</td>
                <td>{{ $cuurentInterest }}</td>
                <td>{{ round($result) }} &#8377</td>
                <td>{{ date("d/m/Y", strtotime(convertDate($mInvestment->created_at))) }}</td>
            </tr>

            /******** TDS Start***********/
            <?php
             $recordData = tdsCalculate($result,$mInvestment,$demadAdvice,Null,$fstrtdate,$fenddate); 
                // $checkYear = date("Y", strtotime(convertDate($demadAdvice->date)));

                // $investmentTds = App\Models\MemberInvestmentInterestTds::where('investment_id',$mInvestment->id)->sum('tdsamount_on_interest');

                // $existsInterst = App\Models\MemberInvestmentInterest::where('investment_id',$mInvestment->id)->sum('interest_amount');

                // $formG = App\Models\Form15G::where('member_id',$mInvestment->member_id)->where('year',$checkYear)->orwhere('max_year',$checkYear)->whereNotNull('file')->first();
                // if($formG){
                //     $tdsAmount = 0;
                //     $tdsPercentage = 0;
                //     $investmentTds = 0;
                // }else{

                //     $memberData = getMemberData($mInvestment->member_id);
                //     $diff = abs(strtotime($demadAdvice->date) - strtotime($memberData['dob']));
                //     $years = floor($diff / (365*60*60*24));

                //     if($years >= 60){
                //        $tdsDetail = App\Models\TdsDeposit::where('type',2)->where('start_date','<',$demadAdvice->date)->first();
                //     }else{
                //         $penCard = get_member_id_proof($mInvestment->member_id,5);
                //         if($penCard){
                //             $tdsDetail = App\Models\TdsDeposit::where('type',1)->where('start_date','<',$demadAdvice->date)->first();
                //         }else{
                //             $tdsDetail = App\Models\TdsDeposit::where('type',5)->where('start_date','<',$demadAdvice->date)->first();
                //         }
                //     }

                //     if($tdsDetail){
                //         $tdsAmount = $tdsDetail->tds_amount;
                //         $tdsPercentage = $tdsDetail->tds_per;

                //         $currentInterst = $result-$existsInterst;

                //         if($currentInterst > $tdsAmount){
                //             $tdsAmountonInterest = $tdsPercentage*$currentInterst/100;
                //             $investmentTds = $investmentTds+$tdsAmountonInterest;
                //         }else{
                //             $investmentTds = 0;
                //         }
                //     }else{
                //         $tdsAmount = 0;
                //         $tdsPercentage = 0;
                //         $investmentTds = 0;
                //     }
                // }
            ?>
            /******** TDS Start***********/

            <tr>
                <td colspan="7" class="dataTables_empty" valign="top"><b>Total Amount:</b> <span class="deposite-amount" style="padding-left: 32%;">{{ round($depositAmount) }} &#8377</span><span class="interest-rate-amount" style="padding-left: 27%;">{{ floor($result) }} &#8377</span> <span class="total-amount" style="padding-left: 13%;">{{ round($depositAmount+$result) }}</span> &#8377</td>
            </tr>

            <tr>
                <!-- <td colspan="7" class="dataTables_empty" valign="top"><b>TDS Amount:</b> <span class="tds-amount" style="padding-left: 31%;">{{ round($investmentTds) }}</span> &#8377<span class="investment-id" style="display:none;">{{ $mInvestment->id }}</span> <span class="tds-percentage" style="display:none;">{{ round($tdsPercentage) }}</span><span class="tds-percentage-on-amount" style="display:none;">{{ round($tdsAmount) }}</span></td> -->
                <td colspan="7" class="dataTables_empty" valign="top"><b>TDS Amount:</b> <span class="tds-amount" style="padding-left: 31%;">{{ round($recordData['tdsAmount']) }}</span> &#8377<span class="investment-id" style="display:none;">{{ $mInvestment->id }}</span> <span class="tds-percentage" style="display:none;">{{ round($recordData['tdsPercentage']) }}</span><span class="tds-percentage-on-amount" style="display:none;">{{ round($recordData['tdsAmount']) }}</span></td>
            </tr>

            <tr>
                <td colspan="7" class="dataTables_empty" valign="top"><b>Final Amount:</b> <span class="final-amount" style="padding-left: 31%;">{{ round(($depositAmount+$result)-$recordData['tdsAmount']) }}</span> &#8377</td>
            </tr>
            
            {{--@if($idProofDetail->first_id_type_id==5 )--}}

                @php 
                    if($formGShow !=NULL){
                        $formgID = $formGShow->id;
                        $LatestformGYear = $formGShow->year;
                        $formGYear = strtotime($LatestformGYear);
                        $FinalformGYear = date('Y', $formGYear);
                    }
                    else{
                        $formgID ='';
                        $FinalformGYear ='';
                    }
                    
                    $cDate = $globalDate;
                    $currentFinancialDate = strtotime($cDate);
                    $currentFinancialYear = date('Y', $currentFinancialDate);



                @endphp 

                @if(!empty($formgID)) 

                    @if( $FinalformGYear == $currentFinancialYear) 
                    <tr style="text-align:center">
                        <td colspan="12">
                            
                        <a href="{{URL::to("admin/form_g/".$mInvestment->member_id."")}}" target="_blank">Check 15G/15H Status</a></td>
                        
                    </tr>

                    @endif

                @endif

            {{--@endif--}}


        @elseif($cDate < $maturity_date)

            @for ($i=1; $i <= $investmentMonths ; $i++)
                @php
                    $val = $mInvestment;
                    $nDate =  date('Y-m-d', strtotime($val->created_at. ' + '.$i.' months'));
                    $cMonth = date('m');
                    $cYear = date('Y');
                    $cMonth = date('m');
                    $cYear = date('Y');
                    if($mInvestment->plan_id == 2){
                        $cuurentInterest = $val->interest_rate;
                    }elseif($mInvestment->plan_id == 6){
                        $cuurentInterest = 11;
                    }
                    $totalDeposit = $totalInvestmentAmount;

                    $d1 = explode('-',$val->created_at);
                    $d2 = explode('-',$nDate);

                    $ts1 = strtotime($val->created_at);
                    $ts2 = strtotime($nDate);

                    $year1 = date('Y', $ts1);
                    $year2 = date('Y', $ts2);

                    $month1 = date('m', $ts1);
                    $month2 = date('m', $ts2);

                    $monthDiff = (($year2 - $year1) * 12) + ($month2 - $month1);

                    $cfAmount = App\Models\Memberinvestments::where('id',$val->id)->first();

                    if($val->deposite_amount*$monthDiff <= $totalDeposit){
                        $extraAmount = (int) $totalDeposit-(int) ($val->deposite_amount*$monthDiff);
                        $cfdAmount = (int) $extraAmount+(int) $cfAmount->carry_forward_amount;
                        App\Models\Memberinvestments::where('id', $val->id)->update(['carry_forward_amount'=>$cfdAmount]);

                        $aviAmount = $val->deposite_amount;
                        $total = $total+$val->deposite_amount;
                        if($monthDiff % 12 == 0 && $monthDiff != 0){
                            $total = $total+$regularInterest;
                            $cInterest = $regularInterest;
                        }else{
                            $total = $total;
                            $cInterest = 0;
                        }
                        $regularInterest = $regularInterest+($cuurentInterest*$total/1200);
                        $a = -$aviAmount+$aviAmount*(pow((1+$cuurentInterest/100), (1*$i/12)));
                        $interest = number_format((float)$a, 2, '.', '');
                        $totalInterestDeposit = $totalInterestDeposit+($interest);

                    }elseif($val->deposite_amount*$monthDiff > $totalDeposit){
                        $pendingAmount =  ($val->deposite_amount*12)-$totalDeposit;
                        if((int) $cfAmount->carry_forward_amount >= (int) $pendingAmount){

                            $cfdAmount = (int) $cfAmount->carry_forward_amount-(int) $pendingAmount;
                            App\Models\Memberinvestments::where('id', $val->id)->update(['carry_forward_amount'=>$cfdAmount]);
                            $collection = (int) $totalDeposit+(int) $pendingAmount;

                        }elseif((int) $cfAmount->carry_forward_amount == (int) $pendingAmount){

                            App\Models\Memberinvestments::where('id', $val->id)->update(['carry_forward_amount'=>0]);
                            $collection = (int) $totalDeposit+(int) $cfAmount->carry_forward_amount;

                        }elseif((int) $pendingAmount > (int) $cfAmount->carry_forward_amount){

                            App\Models\Memberinvestments::where('id', $val->id)->update(['carry_forward_amount'=>0]);
                            $collection = (int) $totalDeposit+(int) $cfAmount->carry_forward_amount;
                        }

                        $checkAmount = $collection+($totalDeposit-$val->deposite_amount*($monthDiff-1));

                        if($checkAmount > 0){
                           $aviAmount = $checkAmount;

                           $total = $total+$checkAmount;
                            if($monthDiff % 12 == 0 && $monthDiff != 0){
                                $total = $total+$regularInterest;
                                $cInterest = $regularInterest;
                            }else{
                                $total = $total;
                                $cInterest = 0;
                            }
                            $regularInterest = $regularInterest+($cuurentInterest*$total/1200);
                            $a = -$aviAmount+$aviAmount*(pow((1+$cuurentInterest/100), (1*$i/12)));
                            $interest = number_format((float)$a, 2, '.', '');

                        }else{
                            $aviAmount = 0;
                            $total = 0;
                            $cuurentInterest = 0;
                            $interest = 0;
                            $a = 0;
                        }
                        $totalInterestDeposit = $totalInterestDeposit+($interest);
                    }
                @endphp
                <tr>
                    <td>{{ $i }}</td>
                    <td>{{ $aviAmount }}  &#8377</td>
                    <td>{{ number_format($cInterest,2) }}</td>
                    <td>{{ number_format($total,2) }}  &#8377</td>
                    <td>{{ $cuurentInterest }}</td>
                    <td>{{ $interest }} &#8377</td>
                    <td>{{ date("d/m/Y", strtotime(convertDate($nDate))) }}</td>
                </tr>
            @endfor

            /******** TDS Start***********/
            <?php
             $recordData = tdsCalculate($totalInterestDeposit,$mInvestment,$demadAdvice,Null,$fstrtdate,$fenddate); 
                // $checkYear = date("Y", strtotime(convertDate($demadAdvice->date)));

                // $investmentTds = App\Models\MemberInvestmentInterestTds::where('investment_id',$mInvestment->id)->sum('tdsamount_on_interest');

                // $existsInterst = App\Models\MemberInvestmentInterest::where('investment_id',$mInvestment->id)->sum('interest_amount');

                // $formG = App\Models\Form15G::where('member_id',$mInvestment->member_id)->where('year',$checkYear)->orwhere('max_year',$checkYear)->whereNotNull('file')->first();
                // if($formG){
                //     $tdsAmount = 0;
                //     $tdsPercentage = 0;
                //     $investmentTds = 0;
                // }else{

                //     $memberData = getMemberData($mInvestment->member_id);
                //     $diff = abs(strtotime($demadAdvice->date) - strtotime($memberData['dob']));
                //     $years = floor($diff / (365*60*60*24));

                //     if($years >= 60){
                //        $tdsDetail = App\Models\TdsDeposit::where('type',2)->where('start_date','<',$demadAdvice->date)->first();
                //     }else{
                //         $penCard = get_member_id_proof($mInvestment->member_id,5);
                //         if($penCard){
                //             $tdsDetail = App\Models\TdsDeposit::where('type',1)->where('start_date','<',$demadAdvice->date)->first();
                //         }else{
                //             $tdsDetail = App\Models\TdsDeposit::where('type',5)->where('start_date','<',$demadAdvice->date)->first();
                //         }
                //     }

                //     if($tdsDetail){
                //         $tdsAmount = $tdsDetail->tds_amount;
                //         $tdsPercentage = $tdsDetail->tds_per;

                //         $currentInterst = $interest-$existsInterst;

                //         if($currentInterst > $tdsAmount){
                //             $tdsAmountonInterest = $tdsPercentage*$currentInterst/100;
                //             $investmentTds = $investmentTds+$tdsAmountonInterest;
                //         }else{
                //             $investmentTds = 0;
                //         }
                //     }else{
                //         $tdsAmount = 0;
                //         $tdsPercentage = 0;
                //         $investmentTds = 0;
                //     }
                // }
            ?>
            /******** TDS Start***********/

            <tr>
                <td colspan="7" class="dataTables_empty" valign="top"><b>Total Amount:</b> <span class="deposite-amount">{{ round($totalDeposit) }} &#8377</span><span class="interest-rate-amount" style="padding-left: 59%;">{{ floor($totalInterestDeposit) }} &#8377</span> <span class="total-amount" style="padding-left: 12%;">{{ round($totalDeposit+$totalInterestDeposit) }}</span> &#8377
                </td>
            </tr>

            <tr>
                <!-- <td colspan="7" class="dataTables_empty" valign="top"><b>TDS Amount:</b> <span class="tds-amount" style="padding-left: 31%;">{{ round($investmentTds) }}</span> &#8377<span class="investment-id" style="display:none;">{{ $mInvestment->id }}</span> <span class="tds-percentage" style="display:none;">{{ round($tdsPercentage) }}</span><span class="tds-percentage-on-amount" style="display:none;">{{ round($tdsAmount) }}</span></td> -->
                <td colspan="7" class="dataTables_empty" valign="top"><b>TDS Amount:</b> <span class="tds-amount" style="padding-left: 31%;">{{ round($recordData['tdsAmount']) }}</span> &#8377<span class="investment-id" style="display:none;">{{ $mInvestment->id }}</span> <span class="tds-percentage" style="display:none;">{{ round($recordData['tdsPercentage']) }}</span><span class="tds-percentage-on-amount" style="display:none;">{{ round($recordData['tdsAmount']) }}</span></td>
            </tr>

            <tr>
                <td colspan="7" class="dataTables_empty" valign="top"><b>Final Amount:</b> <span class="final-amount" style="padding-left: 31%;">{{ round(($totalDeposit+$totalInterestDeposit)-$recordData['tdsAmount']) }}</span> &#8377</td>
            </tr>

            {{--@if($idProofDetail->first_id_type_id==5 )--}}

                @php 
                    if($formGShow !=NULL){
                        $formgID = $formGShow->id;
                        $LatestformGYear = $formGShow->year;
                        $formGYear = strtotime($LatestformGYear);
                        $FinalformGYear = date('Y', $formGYear);
                    }
                    else{
                        $formgID ='';
                        $FinalformGYear ='';
                    }
                    
                    $cDate = $globalDate;
                    $currentFinancialDate = strtotime($cDate);
                    $currentFinancialYear = date('Y', $currentFinancialDate);

                

                @endphp 

                @if(!empty($formgID)) 

                    @if( $FinalformGYear == $currentFinancialYear) 
                    <tr style="text-align:center">
                        <td colspan="12">
                            
                        <a href="{{URL::to("admin/form_g/".$mInvestment->member_id."")}}" target="_blank">Check 15G/15H Status</a></td>
                        
                    </tr>

                    @endif

                @endif

            {{--@endif--}}

        @endif
    @else
        <tr>
            <td colspan="7" class="dataTables_empty" valign="top">No data available in table</td>
        </tr>
    @endif
@elseif(in_array($mInvestment->plan_id, $moneyBack))
    @if($investmentData)
        @php
            $cDate = $demadAdvice->date;
            $diff = abs(strtotime($cDate) - strtotime($mInvestment->last_deposit_to_ssb_date));
            $years = floor($diff / (365*60*60*24));
        @endphp
        @if($cDate >= $maturity_date)
            @php
                $totalInvestmentAmount = App\Models\Daybook::select('investment_id','deposit','created_at')->where('investment_id',$mInvestment->id)->whereDate('created_at','>=',$mInvestment->created_at)->whereIn('transaction_type', [2,4])->sum('deposit');
                $maturityAmount = getMoneyBackAmount($mInvestment->id);
            @endphp
            <tr>
                <td>{{ 1 }}</td>
                <td>{{ $totalInvestmentAmount }} &#8377</td>
                <td>{{ 0 }}</td>
                <td>{{ $totalInvestmentAmount }} &#8377</td>
                <td>{{ round($mInvestment->interest_rate) }} &#8377</td>
                <td>{{ number_format((float)($maturityAmount->available_amount), 2, '.', '') }}</td>
                <td>{{ date("d/m/Y", strtotime(convertDate($mInvestment->created_at))) }}</td>
            </tr>

            /******** TDS Start***********/
            <?php
             $recordData = tdsCalculate($maturityAmount->available_amount,$mInvestment,$demadAdvice,Null,$fstrtdate,$fenddate); 
                // $checkYear = date("Y", strtotime(convertDate($demadAdvice->date)));

                // $investmentTds = App\Models\MemberInvestmentInterestTds::where('investment_id',$mInvestment->id)->sum('tdsamount_on_interest');

                // $existsInterst = App\Models\MemberInvestmentInterest::where('investment_id',$mInvestment->id)->sum('interest_amount');

                // $formG = App\Models\Form15G::where('member_id',$mInvestment->member_id)->where('year',$checkYear)->orwhere('max_year',$checkYear)->whereNotNull('file')->first();
                // if($formG){
                //     $tdsAmount = 0;
                //     $tdsPercentage = 0;
                //     $investmentTds = 0;
                // }else{

                //     $memberData = getMemberData($mInvestment->member_id);
                //     $diff = abs(strtotime($demadAdvice->date) - strtotime($memberData['dob']));
                //     $years = floor($diff / (365*60*60*24));

                //     if($years >= 60){
                //        $tdsDetail = App\Models\TdsDeposit::where('type',2)->where('start_date','<',$demadAdvice->date)->first();
                //     }else{
                //         $penCard = get_member_id_proof($mInvestment->member_id,5);
                //         if($penCard){
                //             $tdsDetail = App\Models\TdsDeposit::where('type',1)->where('start_date','<',$demadAdvice->date)->first();
                //         }else{
                //             $tdsDetail = App\Models\TdsDeposit::where('type',5)->where('start_date','<',$demadAdvice->date)->first();
                //         }
                //     }

                //     if($tdsDetail){
                //         $tdsAmount = $tdsDetail->tds_amount;
                //         $tdsPercentage = $tdsDetail->tds_per;

                //         $currentInterst = $maturityAmount->available_amount-$existsInterst;

                //         if($currentInterst > $tdsAmount){
                //             $tdsAmountonInterest = $tdsPercentage*$currentInterst/100;
                //             $investmentTds = $investmentTds+$tdsAmountonInterest;
                //         }else{
                //             $investmentTds = 0;
                //         }
                //     }else{
                //         $tdsAmount = 0;
                //         $tdsPercentage = 0;
                //         $investmentTds = 0;
                //     }
                // }
            ?>
            /******** TDS Start***********/

            <tr>
                <td colspan="7" class="dataTables_empty" valign="top"><b>Total Amount:</b> <span class="deposite-amount" style="padding-left: 32%;">{{ number_format($totalInvestmentAmount,2) }} &#8377</span><span class="interest-rate-amount" style="padding-left: 27%;">{{ floor($maturityAmount->available_amount) }} &#8377</span> <span class="total-amount" style="padding-left: 13%;">{{ round($maturityAmount->available_amount) }}</span> &#8377</td>
            </tr>

            <tr>
                <!-- <td colspan="7" class="dataTables_empty" valign="top"><b>TDS Amount:</b> <span class="tds-amount" style="padding-left: 31%;">{{ round($investmentTds) }}</span> &#8377<span class="investment-id" style="display:none;">{{ $mInvestment->id }}</span> <span class="tds-percentage" style="display:none;">{{ round($tdsPercentage) }}</span><span class="tds-percentage-on-amount" style="display:none;">{{ round($tdsAmount) }}</span></td> -->
                <td colspan="7" class="dataTables_empty" valign="top"><b>TDS Amount:</b> <span class="tds-amount" style="padding-left: 31%;">{{ round($recordData['tdsAmount']) }}</span> &#8377<span class="investment-id" style="display:none;">{{ $mInvestment->id }}</span> <span class="tds-percentage" style="display:none;">{{ round($recordData['tdsPercentage']) }}</span><span class="tds-percentage-on-amount" style="display:none;">{{ round($recordData['tdsAmount']) }}</span></td>
            </tr>

            <tr>
                <td colspan="7" class="dataTables_empty" valign="top"><b>Final Amount:</b> <span class="final-amount" style="padding-left: 31%;">{{ round(($maturityAmount->available_amount)-$recordData['tdsAmount']) }}</span> &#8377</td>
            </tr>

            {{--@if($idProofDetail->first_id_type_id==5 )--}}

                @php 
                    if($formGShow !=NULL){
                        $formgID = $formGShow->id;
                        $LatestformGYear = $formGShow->year;
                        $formGYear = strtotime($LatestformGYear);
                        $FinalformGYear = date('Y', $formGYear);
                    }
                    else{
                        $formgID ='';
                        $FinalformGYear ='';
                    }
                    
                    $cDate = $globalDate;
                    $currentFinancialDate = strtotime($cDate);
                    $currentFinancialYear = date('Y', $currentFinancialDate);

                

                @endphp 

                @if(!empty($formgID)) 

                    @if( $FinalformGYear == $currentFinancialYear) 
                    <tr style="text-align:center">
                        <td colspan="12">
                            
                        <a href="{{URL::to("admin/form_g/".$mInvestment->member_id."")}}" target="_blank">Check 15G/15H Status</a></td>
                        
                    </tr>

                    @endif

                @endif

            {{--@endif--}}


        @elseif($cDate < $maturity_date)

            @php
                $totaldepositAmount = App\Models\Daybook::select('investment_id','deposit','created_at')->where('investment_id',$mInvestment->id)->whereIn('transaction_type', [2,4])->sum('deposit');

                $depositInterest = App\Models\InvestmentMonthlyYearlyInterestDeposits::where('investment_id',$mInvestment->id)->where('plan_type_id',3)->orderby('date', 'desc')->first();

                if($mInvestment->last_deposit_to_ssb_date){
                    $sDate = date("Y-m-d", strtotime(convertDate($mInvestment->last_deposit_to_ssb_date)));
                    $ts1 = strtotime($sDate);
                    $ts2 = strtotime($cDate);
                    $year1 = date('Y', $ts1);
                    $year2 = date('Y', $ts2);
                    $month1 = date('m', $ts1);
                    $month2 = date('m', $ts2);
                    $monthDiff = (($year2 - $year1) * 12) + ($month2 - $month1);
                    $investmentMonths = $monthDiff;

                    $totalInvestmentAmount = App\Models\Daybook::select('investment_id','deposit','created_at')->where('investment_id',$mInvestment->id)->whereIn('transaction_type', [2,4])->whereDate('created_at','>=',$mInvestment->last_deposit_to_ssb_date)->sum('deposit');
                }else{
                    $totalInvestmentAmount = Daybook::select('investment_id','deposit','created_at')->where('investment_id',$mInvestment->id)->whereDate('created_at','>=',$mInvestment->created_at)->whereIn('transaction_type', [2,4])->sum('deposit');

                    $sDate = date("Y-m-d", strtotime(convertDate($mInvestment->created_at)));
                    $ts1 = strtotime($sDate);
                    $ts2 = strtotime($cDate);
                    $year1 = date('Y', $ts1);
                    $year2 = date('Y', $ts2);
                    $month1 = date('m', $ts1);
                    $month2 = date('m', $ts2);
                    $monthDiff = (($year2 - $year1) * 12) + ($month2 - $month1);
                    $investmentMonths = $monthDiff;
                }

                for ($i=1; $i <= $investmentMonths ; $i++){

                    $val = $mInvestment;
                    $nDate =  date('Y-m-d', strtotime($val->created_at. ' + '.$i.' months'));
                    $cMonth = date('m');
                    $cYear = date('Y');
                    $cuurentInterest = $mInvestment->interest_rate;
                    $totalDeposit = $totalInvestmentAmount;

                    $ts1 = strtotime($mInvestment->created_at);
                    $ts2 = strtotime($nDate);

                    $year1 = date('Y', $ts1);
                    $year2 = date('Y', $ts2);

                    $month1 = date('m', $ts1);
                    $month2 = date('m', $ts2);

                    $monthDiff = (($year2 - $year1) * 12) + ($month2 - $month1);
                    $defaulterInterest = 0;
                    if($val->deposite_amount*$monthDiff <= $totalDeposit){
                        $aviAmount = $val->deposite_amount;
                        $total = $total+$val->deposite_amount;
                        if($monthDiff % 3 == 0 && $monthDiff != 0){
                            $total = $total+$regularInterest;
                            $cInterest = $regularInterest;
                        }else{
                            $total = $total;
                            $cInterest = 0;
                        }
                        $regularInterest = $regularInterest+(($cuurentInterest-$defaulterInterest)*$total/1200);
                        $addInterest = ($cuurentInterest-$defaulterInterest);
                        $a = -$aviAmount+$aviAmount*(pow((1+$addInterest/400), (4*$i/12)));
                        $interest = number_format((float)$a, 2, '.', '');

                        $totalInterestDeposit = $totalInterestDeposit+$interest;
                    }elseif($val->deposite_amount*$monthDiff > $totalDeposit){
                        $checkAmount = ($totalDeposit-$val->deposite_amount*($monthDiff-1));
                        if($checkAmount > 0){
                           $aviAmount = $checkAmount;

                           $total = $total+$checkAmount;
                            if($monthDiff % 3 == 0 && $monthDiff != 0){
                                $total = $total+$regularInterest;
                                $cInterest = $regularInterest;
                            }else{
                                $total = $total;
                                $cInterest = 0;
                            }
                            $regularInterest = $regularInterest+(($cuurentInterest-$defaulterInterest)*$total/1200);
                            $addInterest = ($cuurentInterest-$defaulterInterest);
                            $a = -$aviAmount+$aviAmount*(pow((1+$addInterest/400), (4*$i/12)));
                            $interest = number_format((float)$a, 2, '.', '');

                        }else{
                            $aviAmount = 0;
                            $total = 0;
                            $cuurentInterest = 0;
                            $interest = 0;
                            $addInterest = 0;
                        }
                        $totalInterestDeposit = $totalInterestDeposit+$interest;
                    }
                }

                $finalAmount = round($totalDeposit+$totalInterestDeposit);

                if($depositInterest){
                    $availableAmountFd = ($depositInterest->available_amount*9/100)+$depositInterest->available_amount;
                }else{
                    $availableAmountFd = 0;
                }
            @endphp
            <tr>
                <td>{{ 1 }}</td>
                <td>{{ $totaldepositAmount }} &#8377</td>
                <td>{{ 0 }}</td>
                <td>{{ $totaldepositAmount }} &#8377</td>
                <td>{{ round($mInvestment->interest_rate) }} &#8377</td>
                <td>{{ number_format((float)($finalAmount+$availableAmountFd), 2, '.', '') }}</td>
                <td>{{ date("d/m/Y", strtotime(convertDate($mInvestment->created_at))) }}</td>
            </tr>

            /******** TDS Start***********/
            <?php
                $recordData = tdsCalculate($finalAmount+$availableAmountFd,$mInvestment,$demadAdvice,Null,$fstrtdate,$fenddate); 
                // $checkYear = date("Y", strtotime(convertDate($demadAdvice->date)));

                // $investmentTds = App\Models\MemberInvestmentInterestTds::where('investment_id',$mInvestment->id)->sum('tdsamount_on_interest');

                // $existsInterst = App\Models\MemberInvestmentInterest::where('investment_id',$mInvestment->id)->sum('interest_amount');

                // $formG = App\Models\Form15G::where('member_id',$mInvestment->member_id)->where('year',$checkYear)->orwhere('max_year',$checkYear)->whereNotNull('file')->first();
                // if($formG){
                //     $tdsAmount = 0;
                //     $tdsPercentage = 0;
                //     $investmentTds = 0;
                // }else{

                //     $memberData = getMemberData($mInvestment->member_id);
                //     $diff = abs(strtotime($demadAdvice->date) - strtotime($memberData['dob']));
                //     $years = floor($diff / (365*60*60*24));

                //     if($years >= 60){
                //        $tdsDetail = App\Models\TdsDeposit::where('type',2)->where('start_date','<',$demadAdvice->date)->first();
                //     }else{
                //         $penCard = get_member_id_proof($mInvestment->member_id,5);
                //         if($penCard){
                //             $tdsDetail = App\Models\TdsDeposit::where('type',1)->where('start_date','<',$demadAdvice->date)->first();
                //         }else{
                //             $tdsDetail = App\Models\TdsDeposit::where('type',5)->where('start_date','<',$demadAdvice->date)->first();
                //         }
                //     }

                //     if($tdsDetail){
                //         $tdsAmount = $tdsDetail->tds_amount;
                //         $tdsPercentage = $tdsDetail->tds_per;

                //         $currentInterst = ($finalAmount+$availableAmountFd)-$existsInterst;

                //         if($currentInterst > $tdsAmount){
                //             $tdsAmountonInterest = $tdsPercentage*$currentInterst/100;
                //             $investmentTds = $investmentTds+$tdsAmountonInterest;
                //         }else{
                //             $investmentTds = 0;
                //         }
                //     }else{
                //         $tdsAmount = 0;
                //         $tdsPercentage = 0;
                //         $investmentTds = 0;
                //     }
                // }
            ?>
            /******** TDS Start***********/

            <tr>
                <td colspan="7" class="dataTables_empty" valign="top"><b>Total Amount:</b> <span class="deposite-amount">{{ round($totaldepositAmount) }} &#8377</span><span class="interest-rate-amount" style="padding-left: 59%;">{{ floor($finalAmount+$availableAmountFd) }} &#8377</span> <span class="total-amount" style="padding-left: 12%;">{{ round($finalAmount+$availableAmountFd) }}</span> &#8377
                </td>
            </tr>

            <tr>
                <!-- <td colspan="7" class="dataTables_empty" valign="top"><b>TDS Amount:</b> <span class="tds-amount" style="padding-left: 31%;">{{ round($investmentTds) }}</span> &#8377<span class="investment-id" style="display:none;">{{ $mInvestment->id }}</span> <span class="tds-percentage" style="display:none;">{{ round($tdsPercentage) }}</span><span class="tds-percentage-on-amount" style="display:none;">{{ round($tdsAmount) }}</span></td> -->

                <td colspan="7" class="dataTables_empty" valign="top"><b>TDS Amount:</b> <span class="tds-amount" style="padding-left: 31%;">{{ round($recordData['tdsAmount']) }}</span> &#8377<span class="investment-id" style="display:none;">{{ $mInvestment->id }}</span> <span class="tds-percentage" style="display:none;">{{ round($recordData['tdsPercentage']) }}</span><span class="tds-percentage-on-amount" style="display:none;">{{ round($recordData['tdsAmount']) }}</span></td>
            </tr>

            <tr>
                <td colspan="7" class="dataTables_empty" valign="top"><b>Final Amount:</b> <span class="final-amount" style="padding-left: 31%;">{{ round(($finalAmount+$availableAmountFd)-$recordData['tdsAmount']) }}</span> &#8377</td>
            </tr>


            {{--@if($idProofDetail->first_id_type_id==5 )--}}

                @php 
                    if($formGShow !=NULL){
                        $formgID = $formGShow->id;
                        $LatestformGYear = $formGShow->year;
                        $formGYear = strtotime($LatestformGYear);
                        $FinalformGYear = date('Y', $formGYear);
                    }
                    else{
                        $formgID ='';
                        $FinalformGYear ='';
                    }
                    
                    $cDate = $globalDate;
                    $currentFinancialDate = strtotime($cDate);
                    $currentFinancialYear = date('Y', $currentFinancialDate);



                @endphp 

                @if(!empty($formgID)) 

                    @if( $FinalformGYear == $currentFinancialYear) 
                    <tr style="text-align:center">
                        <td colspan="12">
                            
                        <a href="{{URL::to("admin/form_g/".$mInvestment->member_id."")}}" target="_blank">Check 15G/15H Status</a></td>
                        
                    </tr>

                    @endif

                @endif

            {{--@endif--}}
        @endif
    @else
        <tr>
            <td colspan="7" class="dataTables_empty" valign="top">No data available in table</td>
        </tr>
    @endif
@endif
