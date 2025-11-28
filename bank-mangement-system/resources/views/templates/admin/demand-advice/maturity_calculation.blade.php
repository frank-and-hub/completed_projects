@php

$keyVal = 0;
$cInterest = 0;
$regularInterest = 0;
$total = 0;
$collection = 0;
$preMaturity = array(1);
$finacialYear=getFinacialYear();
$investmentTds = 0;
$tdsAmount = 0;
$tdsPercentage = 0;
$finacialYear = getFinacialYear();
$fenddate    = date("Y", strtotime(convertDate($finacialYear['dateEnd'])));
$fstrtdate    = date("Y", strtotime(convertDate($finacialYear['dateStart'])));

$iss =0;
    

@endphp

@if((in_array($mInvestment->plan->prematurity, $preMaturity) && $demadAdvice->payment_type == 2) || (in_array($mInvestment->plan->death_help, $preMaturity) &&   $demadAdvice->sub_payment_type == 4) && ($mInvestment->plan->plan_sub_category_code!= 'I') || ($demadAdvice->payment_type == 1 && ($mInvestment->plan->plan_sub_category_code!= 'I')) || $demadAdvice->sub_payment_type == 5  )
   dd($investmentData);
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
    ?>

    /******** TDS Start***********/
    <tr>

    <td colspan="7" class="dataTables_empty" valign="top"><b>Total Amount:</b> <span class="deposite-amount" style="padding-left: 32%;">{{ round($totalInvestmentAmount) }} &#8377</span><span class="interest-rate-amount" style="padding-left: 27%;">{{ round($records->sum('interest_rate_amount')) }} &#8377</span> <span class="total-amount" style="padding-left: 13%;">{{ round($totalInvestmentAmount+$records->sum('interest_rate_amount')) }}</span> &#8377</td>

    </tr>

    <tr>
    <td colspan="7" class="dataTables_empty" valign="top"><b>TDS Amount:</b> <span class="tds-amount" style="padding-left: 31%;">{{ round($recordData['tdsAmount']) }}</span> &#8377<span class="investment-id" style="display:none;">{{ $mInvestment->id }}</span> <span class="tds-percentage" style="display:none;">{{ round($recordData['tdsPercentage']) }}</span><span class="tds-percentage-on-amount" style="display:none;">{{ round($recordData['tdsAmount']) }}</span></td>
    </tr>

    <tr>
    <td colspan="7" class="dataTables_empty" valign="top"><b>Final Amount:</b> <span class="final-amount" style="padding-left: 31%;">{{ round(($totalInvestmentAmount+$records->sum('interest_rate_amount'))-$recordData['tdsAmount']) }}</span> &#8377</td>
    </tr>

   

    <?php
        if($formGShow !=NULL){
            $formgID = $formGShow->id;
            $LatestformGYear = $formGShow->year;
            // p($LatestformGYear);
            // $formGYear = strtotime($LatestformGYear);
            // $FinalformGYear = date('Y', strtotime($LatestformGYear));
            $FinalformGYear = $LatestformGYear;
        }
        else{
            $formgID ='';
            $FinalformGYear ='';
        }
        
        $cDate = $globalDate;
        $currentFinancialDate = strtotime($cDate);
        $currentFinancialYear = date('Y', $currentFinancialDate);

    

    ?>
    @if(!empty($formgID)) 
        @if( $FinalformGYear == $currentFinancialYear || $currentFinancialYear == $formGShow->max_year) 
        <tr style="text-align:center">
            <td colspan="12">
                
            <a href="{{URL::to("admin/form_g/".$mInvestment->member_id."")}}" target="_blank">Check 15G/15H Status</a></td>
            
        </tr>

        @endif

    @endif


    @else
    <td colspan="3" class="dataTables_empty" valign="top">No data available in table</td>
    @endif
    @elseif(($mInvestment->plan->plan_sub_category_code== 'I'))
    @if($investmentData)
        @php
            $cDate = date('Y-m-d');
            $cYear = date('Y');
            foreach($interestData as $key => $iData)
            {
                    if($iData->tenure == ($mInvestment->tenure*12) )
                    {
                        $cuurentInterest = $mInvestment['member']->spl_roi != 0 ? $iData->spl_roi : $iData->roi;
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
            <td>{{ 0 }}</td>

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
        
        ?>
        /******** TDS Start***********/

        <tr>
            <td colspan="7" class="dataTables_empty" valign="top"><b>Total Amount:</b> <span class="deposite-amount" style="padding-left: 32%;">{{ round($mInvestment->deposite_amount) }} &#8377</span><span class="interest-rate-amount" style="padding-left: 27%;">{{ round($result) }} &#8377</span> <span class="total-amount" style="padding-left: 13%;">{{ round($mInvestment->deposite_amount+$result) }}</span> &#8377</td>
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
    @endif