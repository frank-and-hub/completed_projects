<h4 class="card-title font-weight-semibold">Report Management | Daily Business  Report</h4>
<?php
  $tenure = array(1,3,5,7,10);
    $planDaily=getPlanID('710')->id;
    $planSSB=getPlanID('703')->id;
    $planKanyadhan=getPlanID('709')->id;
    $planMB=getPlanID('708')->id;
    $planFFD=getPlanID('705')->id;
    $planFRD=getPlanID('707')->id;
    $planJeevan=getPlanID('713')->id;
    $planMI=getPlanID('712')->id;
    $planFD=getPlanID('706')->id;
    $planRD=getPlanID('704')->id;
    $planBhavhishya=getPlanID('718')->id;
    $dailyId=array($planDaily);
    $monthlyId=array($planKanyadhan,$planMB,$planFRD,$planJeevan,$planRD,$planBhavhishya);
    $fdId=array($planMI,$planFFD,$planFD);
    $planids=array($planDaily,$planSSB,$planKanyadhan,$planMB,$planFFD,$planFRD,$planJeevan,$planMI,$planFD,$planRD,$planBhavhishya,);

    $current_daily_new_ac=branchBusinessInvestNewAcCount($startDate,$endDate,$branch_id,$planDaily);
    $current_daily_new_deno_ac=branchBusinessInvestNewDenoSum($startDate,$endDate,$branch_id,$planDaily);
  $current_daily_renew_ac=branchBusinessInvestRenewAc($startDate,$endDate,$dailyId,$branch_id);
  $current_daily_renew_amount_sum=branchBusinessInvestRenewAmountSum($startDate,$endDate,$dailyId,$branch_id);


    $current_monthly_new_ac=branchBusinessInvestNewAcCountType($startDate,$endDate,$monthlyId,$branch_id);
    $current_monthly_deno_sum=branchBusinessInvestNewDenoSumType($startDate,$endDate,$monthlyId,$branch_id);
   $current_monthly_renew_ac=branchBusinessInvestRenewAc($startDate,$endDate,$monthlyId,$branch_id);
   $current_monthly_renew_amount_sum=branchBusinessInvestRenewAmountSum($startDate,$endDate,$monthlyId,$branch_id);
    

   $current_fd_new_ac=branchBusinessInvestNewAcCountType($startDate,$endDate,$fdId,$branch_id);
   $current_fd_deno_sum=branchBusinessInvestNewDenoSumType($startDate,$endDate,$fdId,$branch_id);
   $current_fd_renew_ac=branchBusinessInvestRenewAc($startDate,$endDate,$fdId,$branch_id);
   $current_fd_renew=branchBusinessInvestRenewAmountSum($startDate,$endDate,$fdId,$branch_id);

   $current_daily_new_ac_tenure12 = branchBusinessTenureInvestNewAcCount($startDate,$endDate,$branch_id,$planDaily,'12');
   $current_daily_new_ac_tenure24 = branchBusinessTenureInvestNewAcCount($startDate,$endDate,$branch_id,$planDaily,'24');
   $current_daily_new_ac_tenure36 = branchBusinessTenureInvestNewAcCount($startDate,$endDate,$branch_id,$planDaily,'36');
    $current_daily_new_ac_tenure60 = branchBusinessTenureInvestNewAcCount($startDate,$endDate,$branch_id,$planDaily,'60');

   $monthly_new_ac_tenure12 = branchBusinessInvestTenureNewAcCountType($startDate,$endDate,$branch_id,$monthlyId,'12');
   $monthly_new_ac_tenure36 = branchBusinessInvestTenureNewAcCountType($startDate,$endDate,$branch_id,$monthlyId,'36');
   $monthly_new_ac_tenure60 = branchBusinessInvestTenureNewAcCountType($startDate,$endDate,$branch_id,$monthlyId,'60');
   $monthly_new_ac_tenure84 = branchBusinessInvestTenureNewAcCountType($startDate,$endDate,$branch_id,$monthlyId,'84');
   $monthly_new_ac_tenure120 = branchBusinessInvestTenureNewAcCountType($startDate,$endDate,$branch_id,$monthlyId,'120');
   $monthly_new_ac_tenurekanyadan = branchBusinessInvestTenureKanyadhan($startDate,$endDate,$branch_id,$monthlyId,$tenure);
   $monthly_new_ac_amt_sum_tenurekanyadan = branchBusinessInvestKanyadhanTenureNewDenoSumType($startDate,$endDate,$branch_id,$monthlyId,$tenure);

   $monthly_new_ac_amt_sum_tenure12 = branchBusinessInvestTenureNewDenoSumType($startDate,$endDate,$branch_id,$monthlyId,'12');
   $monthly_new_ac_amt_sum_tenure36 = branchBusinessInvestTenureNewDenoSumType($startDate,$endDate,$branch_id,$monthlyId,'36');
   $monthly_new_ac_amt_sum_tenure60 = branchBusinessInvestTenureNewDenoSumType($startDate,$endDate,$branch_id,$monthlyId,'60');
   $monthly_new_ac_amt_sum_tenure84 = branchBusinessInvestTenureNewDenoSumType($startDate,$endDate,$branch_id,$monthlyId,'84');
   $monthly_new_ac_amt_sum_tenure120 = branchBusinessInvestTenureNewDenoSumType($startDate,$endDate,$branch_id,$monthlyId,'120');

   $current_daily_new_ac_amt_sum_tenure12 = branchBusinessTenureInvestNewDenoSum($startDate,$endDate,$branch_id,$planDaily,'12');
   $current_daily_new_ac_amt_sum_tenure24 = branchBusinessTenureInvestNewDenoSum($startDate,$endDate,$branch_id,$planDaily,'24');
   $current_daily_new_ac_amt_sum_tenure36 = branchBusinessTenureInvestNewDenoSum($startDate,$endDate,$branch_id,$planDaily,'36');
   $current_daily_new_ac_amt_sum_tenure60 = branchBusinessTenureInvestNewDenoSum($startDate,$endDate,$branch_id,$planDaily,'60');

  $monthly_new_fd_ac_tenure12 = branchBusinessInvestTenureNewAcCountType($startDate,$endDate,$branch_id,$fdId,'12');
  $monthly_new_fd_ac_tenure18 = branchBusinessInvestTenureNewAcCountType($startDate,$endDate,$branch_id,$fdId,'18');
  $monthly_new_fd_ac_tenure24 = branchBusinessInvestTenureNewAcCountType($startDate,$endDate,$branch_id,$fdId,'24');
  $monthly_new_fd_ac_tenure36 = branchBusinessInvestTenureNewAcCountType($startDate,$endDate,$branch_id,$fdId,'36');
  $monthly_new_fd_ac_tenure48 = branchBusinessInvestTenureNewAcCountType($startDate,$endDate,$branch_id,$fdId,'48');
  $monthly_new_fd_ac_tenure60 = branchBusinessInvestTenureNewAcCountType($startDate,$endDate,$branch_id,$fdId,'60');
  $monthly_new_fd_ac_tenure72 = branchBusinessInvestTenureNewAcCountType($startDate,$endDate,$branch_id,$fdId,'72');
  $monthly_new_fd_ac_tenure96 = branchBusinessInvestTenureNewAcCountType($startDate,$endDate,$branch_id,$fdId,'96');
  $monthly_new_fd_ac_tenure84 = branchBusinessInvestTenureNewAcCountType($startDate,$endDate,$branch_id,$fdId,'84');
  $monthly_new_fd_ac_tenure120 = branchBusinessInvestTenureNewAcCountType($startDate,$endDate,$branch_id,$fdId,'120');
  
  $monthly_new_fd_sum_ac_tenure12 = branchBusinessInvestTenureNewDenoSumType($startDate,$endDate,$branch_id,$fdId,'12');
  $monthly_new_fd_sum_ac_tenure18 = branchBusinessInvestTenureNewDenoSumType($startDate,$endDate,$branch_id,$fdId,'18');
  $monthly_new_fd_sum_ac_tenure48 = branchBusinessInvestTenureNewDenoSumType($startDate,$endDate,$branch_id,$fdId,'48');
  $monthly_new_fd_sum_ac_tenure60 = branchBusinessInvestTenureNewDenoSumType($startDate,$endDate,$branch_id,$fdId,'60');
  $monthly_new_fd_sum_ac_tenure72 = branchBusinessInvestTenureNewDenoSumType($startDate,$endDate,$branch_id,$fdId,'72');
  $monthly_new_fd_sum_ac_tenure96 = branchBusinessInvestTenureNewDenoSumType($startDate,$endDate,$branch_id,$fdId,'96');
  $monthly_new_fd_sum_ac_tenure120 = branchBusinessInvestTenureNewDenoSumType($startDate,$endDate,$branch_id,$fdId,'120');


  $personal_loan_total_ac = gettotalloanAccountPlanwise($startDate,$endDate,$branch_id,'1');
  $grp_loan_total_ac = gettotalloanAccountPlanwise($startDate,$endDate,$branch_id,'3');
  $loan_against_investment_total_ac = gettotalloanAccountPlanwise($startDate,$endDate,$branch_id,'4');
  $staff_loan_total_ac = gettotalloanAccountPlanwise($startDate,$endDate,$branch_id,'2');

  $personal_loan_total_amt = gettotalloanAmountPlanwise($startDate,$endDate,$branch_id,'1');
  $grp_loan_total_amt = gettotalloanAmountPlanwise($startDate,$endDate,$branch_id,'3');
  $loan_against_investment_total_amt = gettotalloanAmountPlanwise($startDate,$endDate,$branch_id,'4');
  $staff_loan_total_amt = gettotalloanAmountPlanwise($startDate,$endDate,$branch_id,'2');
  
  
  $total_case_current_daily_new_ac = branchBusinessTotalCaseCollectionCount($startDate,$endDate,$branch_id,array($planDaily));
  $total_case_current_daily_new_deno_ac = branchBusinessTotalCaseCollectionAmountSum($startDate,$endDate,$branch_id,array($planDaily));
  $totalFWbranchwise = getTotalFWbranchWise($startDate,$endDate,$branch_id);
  $totalFW =getTotalFW();
  $total_final_payments =getTotalFinalPaymentBranchDaybook($startDate,$endDate,$branch_id);
  
  $total_case_current_monthly_new_ac =branchBusinessTotalCaseCollectionCount($startDate,$endDate,$branch_id,array($monthlyId));
  $total_case_current_monthly_deno_sum =branchBusinessTotalCaseCollectionAmountSum($startDate,$endDate,$branch_id,array($monthlyId));
  
  $total_case_current_fd_new_ac=branchBusinessTotalCaseCollectionCount($startDate,$endDate,$branch_id,array($fdId));
  $total_case_current_fd_deno_sum=branchBusinessTotalCaseCollectionAmountSum($startDate,$endDate,$branch_id,array($fdId));
  
  $total_loan_recovery_ac=App\Models\LoanDayBooks::where('branch_id',$branch_id)->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate])->count();
  $total_loan_recovery_amt=App\Models\LoanDayBooks::where('branch_id',$branch_id)->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate])->sum('deposit');
  $total_loan_recovery_amt=number_format((float)$total_loan_recovery_amt, 2, '.', '');
  $total_loan_recovery_ac_cashmode=loanRecoverAccountCashMode($startDate,$endDate,$branch_id);
  $total_loan_recovery_amt_cashmode=number_format((float)loanRecoverAmtCashMode($startDate,$endDate,$branch_id), 2, '.', '');
  
  $file_chrg_total_ac = file_chrg_total_ac($startDate,$endDate,$branch_id);
  $file_chrg_amount_total = file_chrg_amount_total($startDate,$endDate,$branch_id);
  $file_chrg_total_ac_case_mode =file_chrg_total_ac_case_mode($startDate,$endDate,$branch_id);
  $file_chrg_amount_total_cash_mode =file_chrg_amount_total_cash_mode($startDate,$endDate,$branch_id);

  $Other_ncc_total=totalOtherMiByTypeTotalCount($startDate,$endDate,$branch_id,1,12);+totalOtherMiByTypeTotalCount($startDate,$endDate,$branch_id,1,11);
  $Other_ncc_sum=totalOtherMiByType($startDate,$endDate,$branch_id,1,12);+totalOtherMiByType($startDate,$endDate,$branch_id,1,11);
 
  $closing_cash_in_hand_samraddh_micro = closing_cash_in_hand_samraddh_micro($endDate,$branch_id);
  $closing_cash_in_hand_samraddh_loan = closing_cash_in_hand_samraddh_loan($endDate,$branch_id);




            $fw_fd=branchBusinessInvestRenewAmountSumFW($startDate,$endDate,$fdId,$branch_id);
            $fw_month=branchBusinessInvestRenewAmountSumFW($startDate,$endDate,$monthlyId,$branch_id);
            $fw_daily=branchBusinessInvestRenewAmountSumFW($startDate,$endDate,$dailyId,$branch_id);
            

             $fw_fd_count=branchBusinessInvestRenewAmountcountFW($startDate,$endDate,$fdId,$branch_id);
            $fw_month_count=branchBusinessInvestRenewAmountcountFW($startDate,$endDate,$monthlyId,$branch_id);
            $fw_daily_count=branchBusinessInvestRenewAmountcountFW($startDate,$endDate,$dailyId,$branch_id);

            $fw_loan_recovery_count=fw_loan_recovery_count($startDate, $endDate,$branch_id);
            $fw_loan_recovery=fw_loan_recovery($startDate, $endDate,$branch_id);



            $fw_filecharge=fw_filecharge($startDate, $endDate,$branch_id);
            $fw_filecharge_sum=fw_filecharge_sum($startDate, $endDate,$branch_id);

            $fw_other=totalOtherMemberFW($startDate, $endDate,$branch_id);
            $fw_other_sum=totalOtherMemberFWSum($startDate, $endDate,$branch_id);

            $fw_total=$fw_other+$fw_filecharge+$fw_loan_recovery_count+$fw_fd_count+$fw_month_count+$fw_daily_count;
            
            $fw_total_sum=$fw_other_sum+$fw_filecharge_sum+$fw_loan_recovery+$fw_daily+$fw_month+$fw_fd;


?>

      <table border = "1" class="table-flush" width = "100%" style="border-collapse: collapse;font-size:12px; margin-top: 2rem;">
        <thead>
           <tr>
             <th></th>
            <th colspan="2" scope="colgroup" style="font-weight: bold;">NCC(NEW BUSINESS)</th> 
            <th  colspan="2" scope="colgroup" style="font-weight: bold;">RENEWAL</th>
            <th  colspan="2" scope="colgroup" style="font-weight: bold;">TOTAL CASH COLLECTION</th>
            <th style="font-weight: bold;">NEW FW JOINING </th>
            <th  colspan="3" scope="colgroup" style="font-weight: bold;">NEW A/C THROUGH NEW WORKER</th>
            <th style="font-weight: bold;">TOTAL INVOLVEL FW</th>
            <th style="font-weight: bold;">TOTAL PAYMENTS</th> 
          </tr>
        </thead>
        <tbody>
            <tr>
               <td colspan="1" style="font-weight: bold;">MODE</td>
                <td style="font-weight: bold;">NO. OF A/C</td>
                <td style="font-weight: bold;">DENO.</td>
                <td style="font-weight: bold;">NO. OF A/C</td>
                <td style="font-weight: bold;">AMT.</td>
                <td style="font-weight: bold;">NO. OF A/C</td>
                <td style="font-weight: bold;">AMT.</td>
                <td style="font-weight: bold;"></td>
                <td style="font-weight: bold;">MODE</td>
                 <td style="font-weight: bold;">NO. OF A/C</td>
                <td style="font-weight: bold;">AMT.</td>
                <td style="font-weight: bold;"></td>
                <td style="font-weight: bold;"></td> 
            </tr>    
          <tr>
                    <td>DAILY</td>
                    <td>{{$current_daily_new_ac}}</td>
                    <td>{{$current_daily_new_deno_ac}}</td>
                    <td>{{$current_daily_renew_ac}}</td>
                    <td>{{$current_daily_renew_amount_sum}}</td>
                    <td>{{$total_case_current_daily_new_ac}}</td>
                    <td>{{number_format((float)$total_case_current_daily_new_deno_ac, 2, '.', '')}}</td>
                    <td>{{$totalFWbranchwise}}</td>
                    <td>DAILY</td>
                     <td>{{$fw_daily_count}}</td>
                    <td>{{$fw_daily}}</td>
                    <td>{{number_format((float)$totalFW, 2, '.', '')}}</td>
                    <td>{{$total_final_payments}}</td>
                </tr>
                <tr>    
                    <td>MONTHLY</td>
                   <td>{{$current_monthly_new_ac}}</td>
                    <td>{{$current_monthly_deno_sum}}</td>
                    <td>{{$current_monthly_renew_ac}}</td>
                    <td>{{$current_monthly_renew_amount_sum}}</td>
                    <td>{{$total_case_current_monthly_new_ac}}</td>
                    <td>{{number_format((float)$total_case_current_monthly_deno_sum, 2, '.', '')}}</td>
                    <td></td>
                    <td>MONTHLY</td>
                     <td>{{$fw_month_count}}</td>
                    <td>{{number_format((float)$fw_month, 2, '.', '')}}</td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>    
                    <td>FD</td>
                     <td>{{$current_fd_new_ac}}</td>
                    <td>{{$current_fd_deno_sum}}</td>
                    <td>{{$current_fd_renew_ac}}</td>
                    <td>{{$current_fd_renew}}</td>
                    <td>{{$total_case_current_fd_new_ac}}</td>
                    <td>{{number_format((float)$total_case_current_fd_deno_sum, 2, '.', '')}}</td>
                    <td></td>
                    <td>FD</td>
                     <td>{{$fw_fd_count}}</td>
                    <td>{{number_format((float)$fw_fd, 2, '.', '')}}</td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>    
                    <td>LOAN RECOVERY</td>
                    <td></td>
          <td></td>
          <td>{{$total_loan_recovery_ac}}</td>
          <td>{{$total_loan_recovery_amt}}</td>
          <td>{{$total_loan_recovery_ac_cashmode}}</td>
          <td>{{$total_loan_recovery_amt_cashmode}}</td>
                    <td></td>
                    <td>LOAN RECOVERY</td>
                     <td>{{$fw_loan_recovery_count}}</td>
                    <td>{{number_format((float)$fw_loan_recovery, 2, '.', '')}}</td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>    
                    <td>FILE CHARGE </td>
                    <td>{{$file_chrg_total_ac}}</td>
          <td>{{number_format((float)$file_chrg_amount_total, 2, '.', '')}}</td>
           <td></td>
          <td></td>
          <td>{{$file_chrg_total_ac_case_mode}}</td>
          <td>{{number_format((float)$file_chrg_amount_total_cash_mode, 2, '.', '')}}</td> 
                    <td></td>
                    <td>FILE CHARGE</td>
                    <td>{{$fw_filecharge}}</td>
                    <td>{{number_format((float)$fw_filecharge_sum, 2, '.', '')}}</td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>    
                    <td>OTHER</td>
                    <td>{{$Other_ncc_total}}</td>
          <td>{{number_format((float)$Other_ncc_sum, 2, '.', '')}}</td>
           <td></td>
          <td></td>
          <td>{{$Other_ncc_total}}</td>
          <td>{{number_format((float)$Other_ncc_sum, 2, '.', '')}}</td>
                    <td></td>
                    <td>OTHER</td>
                    <td>{{$fw_other}}</td>
                    <td>{{number_format((float)$fw_other_sum, 2, '.', '')}}</td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>    
                    <td>TOTAL</td>
                    <td>{{($current_daily_new_ac) + ($current_monthly_new_ac) + ($current_fd_new_ac) + ($file_chrg_total_ac) + ($Other_ncc_total) }}</td>
          <td>{{  $totalNEWBusinessAmount = number_format((float)($current_daily_new_deno_ac) + ($current_monthly_deno_sum) + ($current_fd_deno_sum) + ($file_chrg_amount_total) , 2, '.', '') + (number_format((float)$Other_ncc_sum, 2, '.', '')) }}</td>
           <td>{{($current_daily_renew_ac) + ($current_monthly_renew_ac) + ($current_fd_renew_ac) + ($total_loan_recovery_ac)  }}</td>
           <td>{{ $totalNewRenewalAmount = number_format((float)($current_daily_renew_amount_sum) + ($current_monthly_renew_amount_sum) + ($current_fd_renew) + ($total_loan_recovery_amt) , 2, '.', '') }}</td>
           <td>{{($total_case_current_daily_new_ac) + ($total_case_current_monthly_new_ac) + ($total_case_current_fd_new_ac) + ($total_loan_recovery_ac_cashmode) + ($file_chrg_total_ac_case_mode) + ($Other_ncc_total) }}</td>
          <td>{{ $totalCashInHandTotalAmount = (number_format((float)$total_case_current_daily_new_deno_ac, 2, '.', '')) + (number_format((float)$total_case_current_monthly_deno_sum, 2, '.', '')) + (number_format((float)$total_case_current_fd_deno_sum, 2, '.', '')) + (number_format((float)$total_loan_recovery_amt_cashmode, 2, '.', '')) + (number_format((float)$file_chrg_amount_total_cash_mode, 2, '.', '')) + (number_format((float)$Other_ncc_sum, 2, '.', '')) }}</td>
          <td></td>
                    <td>TOTAL</td>
                    <td>{{$fw_total}}</td>
                    <td>{{number_format((float)$fw_total_sum, 2, '.', '')}}</td>
                    <td></td>
                    <td></td>
                </tr>
           
        </tbody>
      </table>
      <h5 class="text-center">SCHEME WISE DETAIL OF OPENDED A\C AND DENOMINATION</h5>
      <table border = "1" class="table-flush" width = "100%" style="border-collapse: collapse;font-size:12px;margin-top: 1rem;">
        
        <thead>
          <tr>
           <th colspan="3" scope="colgroup" style="font-weight: bold;">DAILY/MONTHLY N.I.</th>
              <th colspan="3" scope="colgroup" style="font-weight: bold;">F.D. N.I.</th>
              <th colspan="3" scope="colgroup" style="font-weight: bold;">LOAN</th>
          </tr>
        </thead>  
        <tbody>
            <tr><td colspan="1" style="font-weight: bold;">PERIOD</td>
                <td style="font-weight: bold;">NO. A/C</td>
                <td style="font-weight: bold;">AMT.</td>
               <td colspan="1" style="font-weight: bold;">PERIOD</td>
                <td style="font-weight: bold;">NO. A/C</td>
                <td style="font-weight: bold;">AMT.</td>
                <td colspan="1" style="font-weight: bold;">PERIOD</td>
                <td style="font-weight: bold;">NO. A/C</td>
                <td style="font-weight: bold;">AMT.</td>
                
            </tr>    
    
      <tr>
        <td>12 DAILY</td>
        <td>{{$current_daily_new_ac_tenure12}}</td>
        <td>{{$current_daily_new_ac_amt_sum_tenure12}}</td>
        <td>12 Month</td> 
        <td>{{$monthly_new_fd_ac_tenure12}}</td>
        <td>{{number_format((float)$monthly_new_fd_sum_ac_tenure12, 2, '.', '')}}</td>
        <td>PERSONAL LOAN</td>
        <td>{{$personal_loan_total_ac}}</td>
        <td>{{number_format((float)$personal_loan_total_amt, 2, '.', '')}}</td>
      </tr>
      <tr>    
         <td>24 DAILY</td>
        

        <td>{{$current_daily_new_ac_tenure24}}</td>
        <td>{{$current_daily_new_ac_amt_sum_tenure24}}</td>
        <td>18 Month</td> 
        <td>{{$monthly_new_fd_ac_tenure18}}</td>
        <td>{{number_format((float)$monthly_new_fd_sum_ac_tenure18, 2, '.', '')}}</td>
        <td>GROUP LOAN</td>
        <td>{{$grp_loan_total_ac}}</td>
        <td>{{number_format((float)$grp_loan_total_amt, 2, '.', '')}}</td>
      </tr>
      <tr>    
        <td>36 DAILY </td>
        <td>{{$current_daily_new_ac_tenure36}}</td>
        <td>{{$current_daily_new_ac_amt_sum_tenure36}}</td>
        <td>48 Month</td> 
        <td>{{$monthly_new_fd_ac_tenure48}}</td>
        <td>{{number_format((float)$monthly_new_fd_sum_ac_tenure48, 2, '.', '')}}</td>
        <td>LOAN AGAINST INVESTMENT</td>
        <td>{{$loan_against_investment_total_ac}}</td>
        <td>{{number_format((float)$loan_against_investment_total_amt, 2, '.', '')}}</td>
      </tr>
      <tr>    
        <td>60 DAILY </td>
        <td>{{$current_daily_new_ac_tenure60}}</td>
        <td>{{$current_daily_new_ac_amt_sum_tenure60}}</td>
        <td>60 Month</td> 
        <td>{{$monthly_new_fd_ac_tenure60}}</td>
        <td>{{number_format((float)$monthly_new_fd_sum_ac_tenure60, 2, '.', '')}}</td>
        <td>STAFF LOAN</td>
        <td>{{$staff_loan_total_ac}}</td>
        <td>{{number_format((float)$staff_loan_total_amt, 2, '.', '')}}</td>
      </tr>
      <tr>    
        <td>12 Month </td>
        <td>{{$monthly_new_ac_tenure12}}</td>
        <td>{{$monthly_new_ac_amt_sum_tenure12}}</td>
        <td>72 Month</td> 
        <td>{{$monthly_new_fd_ac_tenure72}}</td>
        <td>{{number_format((float)$monthly_new_fd_sum_ac_tenure72, 2, '.', '')}}</td>
        <td></td>
        <td></td>
        <td></td>
      </tr>
      <tr>    
        <td>36 Month </td>
        <td>{{$monthly_new_ac_tenure36}}</td>
        <td>{{$monthly_new_ac_amt_sum_tenure36}}</td>
        <td>96 Month</td> 
        <td>{{$monthly_new_fd_ac_tenure96}}</td>
        <td>{{number_format((float)$monthly_new_fd_sum_ac_tenure96, 2, '.', '')}}</td>
        <td></td>
        <td></td>
        <td></td>
      </tr>
      <tr>    
        <td>60 Month </td>
        <td>{{$monthly_new_ac_tenure60}}</td>
        <td>{{$monthly_new_ac_amt_sum_tenure60}}</td>
        <td>120 Month</td> 
        <td>{{$monthly_new_fd_ac_tenure120}}</td>
        <td>{{number_format((float)$monthly_new_fd_sum_ac_tenure120, 2, '.', '')}}</td>
        <td></td>
        <td></td>
        <td></td>
      </tr>
      <tr>    
        <td>84 Month </td>
        <td>{{$monthly_new_ac_tenure84}}</td>
        <td>{{$monthly_new_ac_amt_sum_tenure84}}</td>
        <td></td> 
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
      </tr>
      <tr>    
        <td>120 Month</td>
        <td>{{$monthly_new_ac_tenure120}}</td>
        <td>{{$monthly_new_ac_amt_sum_tenure120}}</td>
        <td></td> 
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
      </tr>
      <tr>
        <td>Kanyadan</td>
        <td>{{$monthly_new_ac_tenurekanyadan}}</td>
        <td>{{$monthly_new_ac_amt_sum_tenurekanyadan}}</td>
        <td></td> 
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>   
      </tr>
        
        </tbody>
      </table>
      <h5 class="text-center">TOTAL EXPENSES REPORT</h5>
      <table border = "1" class="table-flush" width = "100%" style="border-collapse: collapse;font-size:12px;margin-top: 1rem;">
        
        <thead>
         
               <tr>
              <th style="font-weight: bold;">S.N.</th>
                <th style="font-weight: bold;">A/C Head.</th>
                <th style="font-weight: bold;">AMT.</th>
            </tr>
          
        </thead>
        <tbody>
            @foreach($account_head as $index => $head )
            
            <tr>    
            
                <td>{{$head->sub_head}}</td>
                <td>&#x20B9;{{number_format((float)headTotalNew($head->head_id,$startDate,$endDate,$branch_id), 2, '.', '')}}</td>
                
            </tr>
            <?php  $arrayCategories = array();
            $data=getFixedAsset($head->id); 
            ?>
            @if(count($data)>0) 

            @foreach($data as $ci =>$child_asset)

            <tr>
                
                <td>{{$child_asset->sub_head}}</td>
                <td>&#x20B9;{{number_format((float)headTotalNew($child_asset->head_id,$startDate,$endDate,$branch_id), 2, '.', '')}}</td>
            </tr>
            
            <?php
            $sub_child=getsubChildFixedAsset($child_asset->id);  
            ?>
            @if(count($sub_child) > 0)
                  @foreach($sub_child as $in=> $sub_child_asset)
                  <tr>

                

                <td>{{$sub_child_asset->sub_head}}</td>
                
                  <td>&#x20B9;{{number_format((float)headTotalNew($sub_child_asset->head_id,$startDate,$endDate,$branch_id), 2, '.', '')}}</td>
            </tr>
            <?php
            $sub_child_sub_asset=getsubChildsubAssetFixedAsset($sub_child_asset->id);  
            ?>
            @if(count($sub_child_sub_asset)>0)
            @foreach($sub_child_sub_asset as $i => $asset ) 
                <tr>
                
                <td>{{$asset->sub_head}}</td>
                <td>&#x20B9;{{number_format((float)headTotalNew($asset->head_id,$startDate,$endDate,$branch_id), 2, '.', '')}}</td>
            </tr>


            @endforeach
            @endif 
            @endforeach
            @endif
            @endforeach
            @endif
            @endforeach
        </tbody>  
          
      </table>
   
       <h5 class="text-center">FUND SEND H.Q. BY CASH (BANK DEPOSIT)</h5>
      <table border = "1" class="table-flush" width = "100%" style="border-collapse: collapse;font-size:12px;margin-top:1rem;">
       
        <tr>
            <td colspan="3">SAMRADDH(MICRO)</td>
            <td colspan="3">LOAN</td>
            <td ></td>
        </tr>
        <tr>    
           <td style="font-weight: bold;">DATE</td>
          <td style="font-weight: bold;">BANK NAME</td>
          <td style="font-weight: bold;">AMOUNT</td>
           <td style="font-weight: bold;">DATE</td>
          <td style="font-weight: bold;">BANK NAME</td>
          <td style="font-weight: bold;">AMOUNT</td>
          <td style="font-weight: bold;">TOTAL SENT</td>
      </tr>
      <?php if( (count($micros) > 0) || (count($loans) > 0) ) { 
      $microCount = count($micros);
      $loansCount = count($loans);
      $maxValue = max($microCount, $loansCount);
      
      for($i=0; $i<$maxValue; $i++){
    ?>
      <tr>
        <td> <?php if(isset($micros[$i])) { echo date("d/m/Y", strtotime(convertDate($micros[$i]->day))); } ?></td>
        <td> <?php if(isset($micros[$i])) { echo $micros[$i]->bank_name; } ?></td>
        <td> <?php if(isset($micros[$i])) { echo number_format((float)$micros[$i]->amount, 2, '.', ''); } ?></td>
        <td> <?php if(isset($loans[$i])) { echo date("d/m/Y", strtotime(convertDate($loans[$i]->day))); } ?></td>
        <td> <?php if(isset($loans[$i])) { echo $loans[$i]->bank_name; } ?></td>
        <td> <?php if(isset($loans[$i])) { echo number_format((float)$loans[$i]->amount, 2, '.', ''); } ?></td>
        <?php if($i == 0) {?>
          <td><?php echo $totalAmounts; ?></td>
        <?php } else { ?>
          <td></td>
        <?php } ?>
        </tr>
      
    <?php } } ?>
      
   </table>
   <h5 class="text-center">RECEIVED CHEQUES</h5>
    <table border = "1" class="table-flush" width = "100%" style="border-collapse: collapse;font-size:12px;margin-top:1rem;">
        <tr>
            <td colspan="3">SAMRADDH(MICRO)</td>
            <td colspan="3">LOAN</td>
            <td></td>
        </tr>
        <tr>    
            <td style="font-weight: bold;">DATE</td>
          <td style="font-weight: bold;">BANK NAME</td>
          <td style="font-weight: bold;">AMOUNT</td>
           <td style="font-weight: bold;">DATE</td>
          <td style="font-weight: bold;">BANK NAME</td>
          <td style="font-weight: bold;">AMOUNT</td>
          <td style="font-weight: bold;">TOTAL RECEIVED</td>
          
      </tr>
      <?php if( (count($receivedChequeMicro) > 0) || (count($receivedChequeLoan) > 0) ) { 
      $receivedChequemicroCount = count($receivedChequeMicro);
      $receivedChequeloansCount = count($receivedChequeLoan);
      $maxValue = max($receivedChequemicroCount, $receivedChequeloansCount);
      
      for($j=0; $j<$maxValue; $j++){
    ?>
      <tr>
        <td> <?php if(isset($receivedChequeMicro[$j])) { echo date("d/m/Y", strtotime(convertDate($receivedChequeMicro[$j]->day))); } ?></td>
        <td> <?php if(isset($receivedChequeMicro[$j])) { echo $receivedChequeMicro[$j]->bank_name; } ?></td>
        <td> <?php if(isset($receivedChequeMicro[$j])) { echo number_format((float)$receivedChequeMicro[$j]->amount, 2, '.', ''); } ?></td>
        <td> <?php if(isset($receivedChequeLoan[$j])) { echo date("d/m/Y", strtotime(convertDate($receivedChequeLoan[$j]->day))); } ?></td>
        <td> <?php if(isset($receivedChequeLoan[$j])) { echo $receivedChequeLoan[$j]->bank_name; } ?></td>
        <td> <?php if(isset($receivedChequeLoan[$j])) { echo number_format((float)$receivedChequeLoan[$j]->amount, 2, '.', ''); } ?></td>
        <?php if($j == 0) {?>
          <td><?php echo $total_received_cheque_amount; ?></td>
        <?php } else { ?>
          <td></td>
        <?php } ?>
      <tr/>
      
    <?php } } ?>
      
   </table>
   <h5 class="text-center">CASH INDEX</h5>
    <table border = "1" class="table-flush" width = "100%" style="border-collapse: collapse;font-size:12px;margin-top:1rem;">
        <thead>
            <tr>
                <th>OPENING CASH IN HAND RS.</th>
                <td>{{ $totalCashInHandTotalAmount }}</td>
            </tr>
             <tr>
                <th>(+) TOTAL RECEIVING RS.</th>
                <td>{{ $totalNEWBusinessAmount +  $totalNewRenewalAmount }}</td>
            </tr>
             <tr>
                <th>TOTAL RS.</th>
                <td>{{ $totalNEWBusinessAmount +  $totalNewRenewalAmount + $totalCashInHandTotalAmount }}</td>
            </tr>
             <tr>
                <th>(-) TOTAL PAYMENTS</th>
                <td>{{ $total_final_payments }}</td>
            </tr>
            <tr>
                <th>ACTUAL CASH IN HAND</th>
                <td>{{ $totalNEWBusinessAmount +  $totalNewRenewalAmount + $totalCashInHandTotalAmount - $total_final_payments }}</td>
            </tr>
        </thead>  
       
    </table>
     <table  border = "1" class="table-flush" width = "100%" style="border-collapse: collapse;font-size:12px;margin-top:1rem;">
        <thead>
            <tr>
        <th>CLOSING CASH IN HAND SAMRADDH</th>
        <td>{{number_format((float)$closing_cash_in_hand_samraddh_micro, 2, '.', '')}}</td>
      </tr>
       <tr>
        <th>CLOSING CASH IN HAND LOAN</th>
        <td>{{number_format((float)$closing_cash_in_hand_samraddh_loan, 2, '.', '')}}</td>
      </tr>
        </thead>  
    </table>

