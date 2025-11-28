<h4 class="card-title font-weight-semibold">Report Management | Loan Report</h4>
<?php
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
      $tenure = array(1,3,5,7,10);

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

            $monthly_new_ac_amt_sum_tenure12 = branchBusinessInvestTenureNewDenoSumType($startDate,$endDate,$branch_id,$monthlyId,'12');
            $monthly_new_ac_amt_sum_tenure36 = branchBusinessInvestTenureNewDenoSumType($startDate,$endDate,$branch_id,$monthlyId,'36');
            $monthly_new_ac_amt_sum_tenure60 = branchBusinessInvestTenureNewDenoSumType($startDate,$endDate,$branch_id,$monthlyId,'60');
            $monthly_new_ac_amt_sum_tenure84= branchBusinessInvestTenureNewDenoSumType($startDate,$endDate,$branch_id,$monthlyId,'84');
            $monthly_new_ac_amt_sum_tenure120 = branchBusinessInvestTenureNewDenoSumType($startDate,$endDate,$branch_id,$monthlyId,'120');
            $monthly_new_ac_amt_sum_tenurekanyadan = branchBusinessInvestKanyadhanTenureNewDenoSumType($startDate,$endDate,$branch_id,$monthlyId,$tenure);

            $current_daily_new_ac_amt_sum_tenure12 = branchBusinessTenureInvestNewDenoSum($startDate,$endDate,$branch_id,$planDaily,'12');
            $current_daily_new_ac_amt_sum_tenure24 = branchBusinessTenureInvestNewDenoSum($startDate,$endDate,$branch_id,$planDaily,'24');
            $current_daily_new_ac_amt_sum_tenure36 = branchBusinessTenureInvestNewDenoSum($startDate,$endDate,$branch_id,$planDaily,'36');
            $current_daily_new_ac_amt_sum_tenure60 = branchBusinessTenureInvestNewDenoSum($startDate,$endDate,$branch_id,$planDaily,'60');
            $monthly_new_fd_ac_tenure12 = branchBusinessInvestTenureNewAcCountType($startDate,$endDate,$branch_id,$fdId,'12');
            $monthly_new_fd_ac_tenure18 = branchBusinessInvestTenureNewAcCountType($startDate,$endDate,$branch_id,$fdId,'18');
            $monthly_new_fd_ac_tenure48 = branchBusinessInvestTenureNewAcCountType($startDate,$endDate,$branch_id,$fdId,'48');
            $monthly_new_fd_ac_tenure60 = branchBusinessInvestTenureNewAcCountType($startDate,$endDate,$branch_id,$fdId,'60');
            $monthly_new_fd_ac_tenure72 = branchBusinessInvestTenureNewAcCountType($startDate,$endDate,$branch_id,$fdId,'72');
            $monthly_new_fd_ac_tenure96 = branchBusinessInvestTenureNewAcCountType($startDate,$endDate,$branch_id,$fdId,'96');
            $monthly_new_fd_ac_tenure120 = branchBusinessInvestTenureNewAcCountType($startDate,$endDate,$branch_id,$fdId,'120');
            
            $monthly_new_fd_sum_ac_tenure12 = branchBusinessInvestTenureNewDenoSumType($startDate,$endDate,$branch_id,$fdId,'12');
            $monthly_new_fd_sum_ac_tenure18 = branchBusinessInvestTenureNewDenoSumType($startDate,$endDate,$branch_id,$fdId,'18');
            $monthly_new_fd_sum_ac_tenure48 = branchBusinessInvestTenureNewDenoSumType($startDate,$endDate,$branch_id,$fdId,'48');
            $monthly_new_fd_sum_ac_tenure60 = branchBusinessInvestTenureNewDenoSumType($startDate,$endDate,$branch_id,$fdId,'60');
            $monthly_new_fd_sum_ac_tenure72 = branchBusinessInvestTenureNewDenoSumType($startDate,$endDate,$branch_id,$fdId,'72');
            $monthly_new_fd_sum_ac_tenure96 = branchBusinessInvestTenureNewDenoSumType($startDate,$endDate,$branch_id,$fdId,'96');
            $monthly_new_fd_sum_ac_tenure120= branchBusinessInvestTenureNewDenoSumType($startDate,$endDate,$branch_id,$fdId,'120');

            $file_chrg_total = App\Models\Daybook::whereIn('transaction_type',['6,10'])->where('branch_id',$branch_id)->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate])->count();
            $file_chrg_amount_total = App\Models\Daybook::whereIn('transaction_type',['6,10'])->where('branch_id',$branch_id)->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate])->sum('amount');;


          //  $mi_total = App\Models\MemberTransaction::where('type',1)->where('sub_type',11)->where('branch_id',$branch_id)->whereBetween(\DB::raw('DATE(entry_date)'), [$startDate, $endDate])->count();
           // $mi_amount_total = App\Models\MemberTransaction::where('type',1)->where('sub_type',11)->where('branch_id',$branch_id)->whereBetween(\DB::raw('DATE(entry_date)'), [$startDate, $endDate])->sum('amount');;

           //$stn_total = App\Models\MemberTransaction::where(function ($q){
             //   $q->where('type',1)->where('sub_type',12)
               // ->orwhere('type',21);
            //})->where('branch_id',$branch_id)->whereBetween(\DB::raw('DATE(entry_date)'), [$startDate, $endDate])->count();

            // $stn_amount_total = App\Models\MemberTransaction::where(function ($q){
            //     $q->where('type',1)->where('sub_type',12)
            //     ->orwhere('type',21);
            // })->where('branch_id',$branch_id)->whereBetween(\DB::raw('DATE(entry_date)'), [$startDate, $endDate])->sum('amount');

             $other_total__income_account= getExpenseHeadaccountCount(3,1,$startDate,$endDate,$branch_id);
            $other_total__expense_account = getExpenseHeadaccountCount(4,1,$startDate,$endDate,$branch_id);
            $other_total__income_amount = headTotalNew(3,$startDate,$endDate,$branch_id);
            $other_total__expense_amount = headTotalNew(4,$startDate,$endDate,$branch_id);
             $loan_total_account = App\Models\LoanDayBooks::where('branch_id',$branch_id)->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate])->where('is_deleted',0)->count();
            $loan_total_amount = App\Models\LoanDayBooks::where('branch_id',$branch_id)->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate])->where('is_deleted',0)->sum('deposit');
           

           $received_voucher_account =App\Models\ReceivedVoucher::where('branch_id',$branch_id)->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate])->count(); ;
            $received_voucher_amount = App\Models\ReceivedVoucher::where('branch_id',$branch_id)->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate])->sum('amount');
           

           $renew_emi_recovery_12_Id = getmemberinvestementPlanwise($startDate,$endDate,$branch_id,$planDaily,'12');
            $renew_emi_recovery_24_Id = getmemberinvestementPlanwise($startDate,$endDate,$branch_id,$planDaily,'24');
            $renew_emi_recovery_36_Id  = getmemberinvestementPlanwise($startDate,$endDate,$branch_id,$planDaily,'36');
           $renew_emi_recovery_60_Id  = getmemberinvestementPlanwise($startDate,$endDate,$branch_id,$planDaily,'60'); 
           
          $current_renew_emi_recovery_12 = getrenewemirecovertotalAccount($startDate,$endDate,$branch_id,'12',$dailyId);
            $current_renew_emi_recovery_24 = getrenewemirecovertotalAccount($startDate,$endDate,$branch_id,'24',$dailyId);
            $current_renew_emi_recovery_36 = getrenewemirecovertotalAccount($startDate,$endDate,$branch_id,'36',$dailyId);
            $current_renew_emi_recovery_60 = getrenewemirecovertotalAccount($startDate,$endDate,$branch_id,'60',$dailyId);
             $current_renew_emi_recovery_amnt_12 = getrenewemirecovertotalAmount($startDate,$endDate,$branch_id,'12',$dailyId);
            $current_renew_emi_recovery_amnt_24 = getrenewemirecovertotalAmount($startDate,$endDate,$branch_id,'24',$dailyId);
            $current_renew_emi_recovery_amnt_36 = getrenewemirecovertotalAmount($startDate,$endDate,$branch_id,'36',$dailyId);
            $current_renew_emi_recovery_amnt_60 = getrenewemirecovertotalAmount($startDate,$endDate,$branch_id,'60',$dailyId);

            $renew_emi_monthly_recovery_12_Id = getmemberinvestementPlanwiseType($startDate,$endDate,$branch_id,$monthlyId,'12');
            $renew_emi_monthly_recovery_84_Id = getmemberinvestementPlanwiseType($startDate,$endDate,$branch_id,$monthlyId,'84');
            $renew_emi_monthly_recovery_36_Id  = getmemberinvestementPlanwiseType($startDate,$endDate,$branch_id,$monthlyId,'36');
            $renew_emi_monthly_recovery_60_Id  = getmemberinvestementPlanwiseType($startDate,$endDate,$branch_id,$monthlyId,'60');
            $renew_emi_monthly_recovery_120_Id  = getmemberinvestementPlanwiseType($startDate,$endDate,$branch_id,$monthlyId,'120');
             $renew_emi_monthly_recovery_kanyadhan  = getmemberinvestementKanyadhanId($startDate,$endDate,$branch_id,$monthlyId,$tenure);


          $monthly_renew_emi_recovery_12 = getrenewemirecovertotalAccount($startDate,$endDate,$branch_id,'12',$monthlyId);
            $monthly_renew_emi_recovery_84 = getrenewemirecovertotalAccount($startDate,$endDate,$branch_id,'84',$monthlyId);
            $monthly_renew_emi_recovery_36 = getrenewemirecovertotalAccount($startDate,$endDate,$branch_id,'36',$monthlyId);
            $monthly_renew_emi_recovery_60 = getrenewemirecovertotalAccount($startDate,$endDate,$branch_id,'60',$monthlyId);
             $monthly_renew_emi_recovery_120 = getrenewemirecovertotalAccount($startDate,$endDate,$branch_id,'120',$monthlyId);
              $monthly_renew_emi_recovery_acnt_kanyadhan = getmemberinvestement_emi_recoverKanyadhan($startDate,$endDate,$branch_id,$renew_emi_monthly_recovery_kanyadhan); 

             $monthly_renew_emi_recovery_amnt_12 = getrenewemirecovertotalAmount($startDate,$endDate,$branch_id,'12',$monthlyId);
            $monthly_renew_emi_recovery_amnt_84 = getrenewemirecovertotalAmount($startDate,$endDate,$branch_id,'84',$monthlyId);
            $monthly_renew_emi_recovery_amnt_36 = getrenewemirecovertotalAmount($startDate,$endDate,$branch_id,'36',$monthlyId);
            $monthly_renew_emi_recovery_amnt_60 = getrenewemirecovertotalAmount($startDate,$endDate,$branch_id,'60',$monthlyId);
             $monthly_renew_emi_recovery_amnt_120 = getrenewemirecovertotalAmount($startDate,$endDate,$branch_id,'120',$monthlyId); 
             $monthly_renew_emi_recovery_amnt_sum = getmemberinvestement_emi_recoverKanyadhan_sum($startDate,$endDate,$branch_id,$renew_emi_monthly_recovery_kanyadhan); 

        


            $renew_emi_fd_recovery_12_Id = getmemberinvestementPlanwiseType($startDate,$endDate,$branch_id,$fdId,'12');
            $renew_emi_fd_recovery_18_Id = getmemberinvestementPlanwiseType($startDate,$endDate,$branch_id,$fdId,'18');
            $renew_emi_fd_recovery_48_Id  = getmemberinvestementPlanwiseType($startDate,$endDate,$branch_id,$fdId,'48');
            $renew_emi_fd_recovery_60_Id  = getmemberinvestementPlanwiseType($startDate,$endDate,$branch_id,$fdId,'60');
            $renew_emi_fd_recovery_72_Id  = getmemberinvestementPlanwiseType($startDate,$endDate,$branch_id,$fdId,'72');
            $renew_emi_fd_recovery_96_Id  = getmemberinvestementPlanwiseType($startDate,$endDate,$branch_id,$fdId,'96');
            $renew_emi_fd_recovery_120_Id  = getmemberinvestementPlanwiseType($startDate,$endDate,$branch_id,$fdId,'120');
           
    $fd_renew_emi_recovery_12 = getrenewemirecovertotalAccount($startDate,$endDate,$branch_id,'12',$fdId);
            $fd_renew_emi_recovery_18 = getrenewemirecovertotalAccount($startDate,$endDate,$branch_id,'18',$fdId);
            $fd_renew_emi_recovery_48 = getrenewemirecovertotalAccount($startDate,$endDate,$branch_id,'48',$fdId);
            $fd_renew_emi_recovery_60= getrenewemirecovertotalAccount($startDate,$endDate,$branch_id,'60',$fdId);
            $fd_renew_emi_recovery_72 = getrenewemirecovertotalAccount($startDate,$endDate,$branch_id,'72',$fdId);
            $fd_renew_emi_recovery_96 = getrenewemirecovertotalAccount($startDate,$endDate,$branch_id,'96',$fdId);
             $fd_renew_emi_recovery_120 = getrenewemirecovertotalAccount($startDate,$endDate,$branch_id,'120',$fdId);
            

             $fd_renew_emi_recovery_amnt_12 = getrenewemirecovertotalAmount($startDate,$endDate,$branch_id,'12',$fdId);
            $fd_renew_emi_recovery_amnt_18= getrenewemirecovertotalAmount($startDate,$endDate,$branch_id,'18',$fdId);
            $fd_renew_emi_recovery_amnt_48 = getrenewemirecovertotalAmount($startDate,$endDate,$branch_id,'48',$fdId);
            $fd_renew_emi_recovery_amnt_60 = getrenewemirecovertotalAmount($startDate,$endDate,$branch_id,'60',$fdId);
             $fd_renew_emi_recovery_amnt_72 = getrenewemirecovertotalAmount($startDate,$endDate,$branch_id,'72',$fdId);
             $fd_renew_emi_recovery_amnt_96= getrenewemirecovertotalAmount($startDate,$endDate,$branch_id,'96',$fdId); 
        
             $fd_renew_emi_recovery_amnt_120 = getrenewemirecovertotalAmount($startDate,$endDate,$branch_id,'120',$fdId); 
             // $data['monthly_renew_emi_recovery_amnt_sum'] = getmemberinvestement_emi_recoverKanyadhan_sum($startDate,$endDate,$branch_id,$renew_emi_monthly_recovery_kanyadhan); 

             $current_mature_account_12  = totalmatureAccount($startDate,$endDate,$branch_id,$planDaily,'12');
             $current_mature_account_24  = totalmatureAccount($startDate,$endDate,$branch_id,$planDaily,'24');
             $current_mature_account_36  = totalmatureAccount($startDate,$endDate,$branch_id,$planDaily,'36');
             $current_mature_account_60  = totalmatureAccount($startDate,$endDate,$branch_id,$planDaily,'60');

              $current_mature_amnt_12 = totalmatureAmount($startDate,$endDate,$branch_id,$planDaily,'12');
             $current_mature_amnt_24  = totalmatureAmount($startDate,$endDate,$branch_id,$planDaily,'24');
             $current_mature_amnt_36= totalmatureAmount($startDate,$endDate,$branch_id,$planDaily,'36');
             $current_mature_amnt_60  = totalmatureAmount($startDate,$endDate,$branch_id,$planDaily,'60');

             // Monthly

            $monthly_mature_ac_tenure12 = matureInvestTenureNewAcCountType($startDate,$endDate,$branch_id,$monthlyId,'12');
            $monthly_mature_ac_tenure36 = matureInvestTenureNewAcCountType($startDate,$endDate,$branch_id,$monthlyId,'36');
            $monthly_mature_ac_tenure60 = matureInvestTenureNewAcCountType($startDate,$endDate,$branch_id,$monthlyId,'60');
            $monthly_mature_ac_tenure84 = matureInvestTenureNewAcCountType($startDate,$endDate,$branch_id,$monthlyId,'84');
            $monthly_mature_ac_tenure120 = matureInvestTenureNewAcCountType($startDate,$endDate,$branch_id,$monthlyId,'120');
            $monthly_mature_fd_ac_tenure_kanyadhan = matureInvestTenureKanyadhanNewAcCountType($startDate,$endDate,$branch_id,$fdId,$tenure);

            $monthly_mature_ac_amt_sum_tenure12 = matureInvestTenureNewDenoSumType($startDate,$endDate,$branch_id,$monthlyId,'12');
            $monthly_mature_ac_amt_sum_tenure36= matureInvestTenureNewDenoSumType($startDate,$endDate,$branch_id,$monthlyId,'36');
            $monthly_mature_ac_amt_sum_tenure60 = matureInvestTenureNewDenoSumType($startDate,$endDate,$branch_id,$monthlyId,'60');
            $monthly_mature_ac_amt_sum_tenure84= matureInvestTenureNewDenoSumType($startDate,$endDate,$branch_id,$monthlyId,'84');
            $monthly_mature_ac_amt_sum_tenure120 = matureInvestTenureNewDenoSumType($startDate,$endDate,$branch_id,$monthlyId,'120');
            $monthly_mature_fd_ac_tenure_kanyadhan_amnt = matureInvestTenureKanyadhanAmount($startDate,$endDate,$branch_id,$fdId,$tenure);


            // FD


            $monthly_mature_fd_ac_tenure12= matureInvestTenureNewAcCountType($startDate,$endDate,$branch_id,$fdId,'12');
            $monthly_mature_fd_ac_tenure18 = matureInvestTenureNewAcCountType($startDate,$endDate,$branch_id,$fdId,'18');
            $monthly_mature_fd_ac_tenure48 = matureInvestTenureNewAcCountType($startDate,$endDate,$branch_id,$fdId,'48');
            $monthly_mature_fd_ac_tenure60 = matureInvestTenureNewAcCountType($startDate,$endDate,$branch_id,$fdId,'60');
            $monthly_mature_fd_ac_tenure72 = matureInvestTenureNewAcCountType($startDate,$endDate,$branch_id,$fdId,'72');
            $monthly_mature_fd_ac_tenure96= matureInvestTenureNewAcCountType($startDate,$endDate,$branch_id,$fdId,'96');
            $monthly_mature_fd_ac_tenure120 = matureInvestTenureNewAcCountType($startDate,$endDate,$branch_id,$fdId,'120');
            $monthly_mature_fd_ac_tenure_kanyadhan = matureInvestTenureKanyadhanNewAcCountType($startDate,$endDate,$branch_id,$fdId,$tenure);
            
            $monthly_mature_fd_sum_ac_tenure12 = matureInvestTenureNewDenoSumType($startDate,$endDate,$branch_id,$fdId,'12');
            $monthly_mature_fd_sum_ac_tenure18 = matureInvestTenureNewDenoSumType($startDate,$endDate,$branch_id,$fdId,'18');
            $monthly_mature_fd_sum_ac_tenure48 = matureInvestTenureNewDenoSumType($startDate,$endDate,$branch_id,$fdId,'48');
            $monthly_mature_fd_sum_ac_tenure60 = matureInvestTenureNewDenoSumType($startDate,$endDate,$branch_id,$fdId,'60');
            $monthly_mature_fd_sum_ac_tenure72 = matureInvestTenureNewDenoSumType($startDate,$endDate,$branch_id,$fdId,'72');
            $monthly_mature_fd_sum_ac_tenure96 = matureInvestTenureNewDenoSumType($startDate,$endDate,$branch_id,$fdId,'96');
            $monthly_mature_fd_sum_ac_tenure120 = matureInvestTenureNewDenoSumType($startDate,$endDate,$branch_id,$fdId,'120');
            $monthly_mature_fd_ac_tenure_kanyadhan_amnt = matureInvestTenureKanyadhanAmount($startDate,$endDate,$branch_id,$fdId,$tenure);

             $existsopening = App\Models\BranchCurrentBalance::where('branch_id',$branch_id)->where('entry_date', $startDate)->exists();
            if($existsopening)
            {
              $cashInhandOpening =   App\Models\BranchCurrentBalance::where('branch_id',$branch_id)->where('entry_date', $startDate)->orderBy('entry_date','DESC')->first();
              $cashInhandOpening = $cashInhandOpening->totalAmount;

            }
            else{
               $cashInhandOpening =   App\Models\BranchCurrentBalance::where('branch_id',$branch_id)->where('entry_date','<=', $startDate)->orderBy('entry_date','DESC')->first();
              $cashInhandOpening = $cashInhandOpening->totalAmount;

            }
           
               $cashInhandclosing = App\Models\BranchCurrentBalance::where('branch_id',$branch_id)->where('entry_date','<=', $endDate)->orderBy('entry_date','DESC')->first();

               $cashInhandclosing = $cashInhandclosing->totalAmount;
			   // sourab code
				$aa = BranchDaybookAmount($startDate,$endDate,$branch_id);
				$cash_in_hand['DR']=0;
				$cash_in_hand['CR']=0;
				if(array_key_exists('0_CR', $aa))
				{
					$cash_in_hand['CR'] = $aa['0_CR'] ; 
				}
				if(array_key_exists('0_DR', $aa))
				{
				   $cash_in_hand['DR'] = $aa['0_DR'] ; 
				}
				$getBranchOpening_cash =getBranchOpeningDetail($branch_id);
				$balance_cash =0;
				$C_balance_cash =0;
				$currentdate = date('Y-m-d');
				if($getBranchOpening_cash->date==$startDate)
				{
				  $balance_cash =$getBranchOpening_cash ->total_amount;
				  if($endDate == '')
					  {
						$endDate=$currentdate;
					  }
				}
				if($getBranchOpening_cash->date<$startDate)
				{
					if($getBranchOpening_cash->date != '')
					{
						  $getBranchTotalBalance_cash=getBranchTotalBalanceAllTran($startDate,$getBranchOpening_cash->date,$getBranchOpening_cash->total_amount,$branch_id);
					}
					else{
						 $getBranchTotalBalance_cash=getBranchTotalBalanceAllTran($startDate,$currentdate,$getBranchOpening_cash->total_amount,$branch_id);
						
					}
				 
				  $balance_cash =$getBranchTotalBalance_cash;
				  if($endDate == '')
					  {
						$endDate=$currentdate;
					  }
				}
				$getTotal_DR=getBranchTotalBalanceAllTranDR($startDate,$endDate,$branch_id);
				$getTotal_CR=getBranchTotalBalanceAllTranCR($startDate,$endDate,$branch_id);
				$totalBalance=$getTotal_CR-$getTotal_DR;
				$C_balance_cash =$balance_cash+$totalBalance;
				//sourab code
        dd('asd');

    ?> 

<table >

  <tr>
   
      <table>
        <tr>
          <th colspan="3"><strong >Cash Allocation1</strong></th>
        </tr>
        <tr>
          <td>Opening</td>
          <td></td>
          <td>{{number_format((float)$balance_cash, 2, '.', '')}}</td>
        </tr>
        <tr>
            <td></td>
            <td>Received</td>
            <td> Payment</td>
        </tr>
        <tr>
            <td>Cash</td>
           <td>{{number_format((float)$cash_in_hand['CR'], 2, '.', '')}}</td>
           <td>{{number_format((float)$cash_in_hand['DR'], 2, '.', '')}}</td> 
        </tr>
        <tr>
            <td>Closing</td>
            <td></td>
            <td>{{number_format((float)$C_balance_cash, 2, '.', '')}}</td> 
        </tr>

      </table>
   
      <table>
        <tr>
          <th colspan="3"><strong >Cheque</strong></th>
        </tr>
        <tr>
          <td>Opening</td>
          <td></td>
          <td>{{number_format((float)getchequeopeningBalance($startDate,$branch_id), 2, '.', '')}}</td> 
        </tr>
        <tr>
            <td></td>
            <td>Received</td>
            <td> Payment</td>
        </tr>
        <tr>
           <td >Cheque</td>
          <td>{{number_format((float)$cheque['CR'], 2, '.', '')}}</td>
            <td>{{number_format((float)$cheque['DR'], 2, '.', '')}}</td> 
           
        </tr>
        <tr>
            <td>Closing</td>
            <td></td>
            <td>{{number_format((float)getchequeclosingBalance($endDate,$branch_id), 2, '.', '')}}</td> 
        </tr>
    
      </table>
   
  
    
     
   
  </tr>
</table>
<div class="">
                 <h3 class="card-title font-weight-semibold">Bank</h3>
                <div class="">
                    <table border="1" width="100%" style="border-collapse: collapse;font-size:12px;">
                       <thead>
                                 <tr>
                                   <th style="font-weight: bold;">Bank Name</th>
                                   <th style="font-weight: bold;">Account Number</th>
                                   <th style="font-weight: bold;">Opening</th>
                                   <th style="font-weight: bold;">Receiving</th>
                                   <th style="font-weight: bold;">Payment</th>
                                   <th style="font-weight: bold;">Closing</th>
                                  
                                   <!-- <th>Account Head Code</th>
                                   <th>Account Head Name</th> -->
                                 </tr>
                               </thead>
                               <tbody>
                               @foreach($bank as $value)
                                 
                                 <tr>
                                  <td>{{$value->bank_name}}</td>
                                  <td>{{$value['bankAccount']->account_no}}</td>
                                   <td>{{number_format((float)getbankopeningBalance($startDate,$value->id), 2, '.', '')}}</td>
                                   <td>{{number_format((float)getbankreceivedBalance($startDate,$endDate,$branch_id,$value->id), 2, '.', '')}}</td>
                                    <td>{{number_format((float)getbankpaymentBalance($startDate,$endDate,$branch_id,$value->id), 2, '.', '')}}</td>
                                   <td>{{number_format((float)getbankclosingBalance($endDate,$value->id), 2, '.', '')}}</td>
                                 </tr>
                                 @endforeach
                               </tbody>
                    </table>     
                 </div>   
            </div>   

<h4 class="card-title font-weight-semibold">Details 2 </h4>

<table border="1" width="100%" style="border-collapse: collapse;font-size:12px;">
    <thead>
  <tr>
     <th style="font-weight: bold;">Tr.No</th>
   <th style="font-weight: bold;">Tr.Date</th>
   <th style="font-weight: bold;">Tr.By</th>
   <th style="font-weight: bold;">Receipt.No</th>
   <th style="font-weight: bold;">Account Number</th>
   <th style="font-weight: bold;">Plan Name</th>
   <th style="font-weight: bold;">Associate</th>
   <th style="font-weight: bold;">Member/Employee/Owner/Plan/Loan</th>
   
   <th style="font-weight: bold;">Narration</th>
   <th style="font-weight: bold;">CR Narration</th>
   <th style="font-weight: bold;">DR Narration</th>
   <th style="font-weight: bold;">CR Amount</th>
   <th style="font-weight: bold;">DR Amount</th>
    <th>Balance</th>
  <!--  <th>Account Head Code</th>
   <th>Account Head Name</th> -->
     <th style="font-weight: bold;">Ref Id</th>
      <th style="font-weight: bold;">Payment Type</th>
   <th style="font-weight: bold;">Tag</th>

</tr>
</thead>
<tbody>
   @foreach($data as $index => $value)
    <tr>

       <td>{{$index+1}}</td>
       <td>{{$value['tr_date']}}</td>
	   <td>{{$value['tran_by']}}</td>
       <td>{{$value['bt_id']}}</td>
       <td>{{$value['member_account']}} </td>
       <td>{{$value['plan_name']}} </td>
      <td>{{$value['a_name']}}</td>
      <td>{{$value['memberName']}} </td>
      
      
       <td>{{$value['type']}}</td>
       <td>{{$value['description_cr']}}</td>
       <td>{{$value['description_dr']}}</td>
       <td>{{$value['cr_amnt']}}</td>
       <td>{{$value['dr_amnt']}}</td>
       <td>{{$value['balance']}}</td>
       
        <td>{{$value['ref_no']}}</td>
       <td>{{$value['pay_mode']}} </td>
       <td>{{$value['tag']}}</td>
   </tr>
   @endforeach
</tbody>
</table>
<h4 class="card-title font-weight-semibold">Details 3 </h4>

<table class="my-4" border="1" width="100%" style="border-collapse: collapse;font-size:12px;"> 
    <thead>
        <tr>
            <th></th>
            <th colspan="2" style="font-weight: bold;">NI</th>
            <th colspan="2" style="font-weight: bold;">Renew/Emi Recovery  </th>
            <th colspan="2" style="font-weight: bold;">Payment</th>
        </tr>
        <tr>
            <!--<th style="font-weight: bold;">Plan</th>-->
            <th style="font-weight: bold;">Total No A/C</th>
            <th style="font-weight: bold;">Amount</th>
            <th style="font-weight: bold;">Total No A/C</th>
            <th style="font-weight: bold;">Amount</th>
            <th style="font-weight: bold;">Total No A/C</th>
            <th style="font-weight: bold;">Amount</th>
        </tr>
    </thead>
    <tbody>
            
              <tr>
                              <td>12 DAILY</td>
                              <td>{{$current_daily_new_ac_tenure12}}</td>
                              <td>{{$current_daily_new_ac_amt_sum_tenure12}}</td>
                              <td>{{$current_renew_emi_recovery_12}}</td>
                              <td>{{$current_renew_emi_recovery_amnt_12}}</td>
                              <td>{{$current_mature_account_12}}</td>
                              <td>{{$current_mature_amnt_12}}</td>
                            </tr>
                            <tr>
                              <td>24 DAILY</td>
                              <td>{{$current_daily_new_ac_tenure24}}</td>
                              <td>{{$current_daily_new_ac_amt_sum_tenure24}}</td>
                              <td>{{$current_renew_emi_recovery_24}}</td>
                              <td>{{$current_renew_emi_recovery_amnt_24}}</td>
                              <td>{{$current_mature_account_24}}</td>
                              <td>{{$current_mature_amnt_24}}</td>
                            </tr>
                            <tr>
                              <td>36 DAILY</td>
                              <td>{{$current_daily_new_ac_tenure36}}</td>
                              <td>{{$current_daily_new_ac_amt_sum_tenure36}}</td>
                             <td>{{$current_renew_emi_recovery_36}}</td>
                              <td>{{$current_renew_emi_recovery_amnt_36}}</td>
                              <td>{{$current_mature_account_36}}</td>
                              <td>{{$current_mature_amnt_36}}</td>
                            </tr>
                             <tr>
                              <td>60 DAILY</td>
                               <td>{{$current_daily_new_ac_tenure60}}</td>
                              <td>{{$current_daily_new_ac_amt_sum_tenure60}}</td>
                              <td>{{$current_renew_emi_recovery_60}}</td>
                              <td>{{$current_renew_emi_recovery_amnt_60}}</td>
                              <td>{{$current_mature_account_60}}</td>
                              <td>{{$current_mature_amnt_60}}</td>
                            </tr>
                            <tr>
                              <td>12 MONTH</td>
                              <td>{{$monthly_new_ac_tenure12}}</td>
                              <td>{{$monthly_new_ac_amt_sum_tenure12}}</td>
                              <td>{{$monthly_renew_emi_recovery_12}}</td>
                              <td>{{$monthly_renew_emi_recovery_amnt_12}}</td>
                              <td>{{$monthly_mature_ac_tenure12}}</td>
                              <td>{{$monthly_mature_ac_amt_sum_tenure12}}</td>
                            </tr>
                            <tr>
                              <td>36 MONTH</td>
                              <td>{{$monthly_new_ac_tenure36}}</td>
                              <td>{{$monthly_new_ac_amt_sum_tenure36}}</td>
                              <td>{{$monthly_renew_emi_recovery_36}}</td>
                              <td>{{$monthly_renew_emi_recovery_amnt_36}}</td>
                              <td>{{$monthly_mature_ac_tenure36}}</td>
                              <td>{{$monthly_mature_ac_amt_sum_tenure36}}</td>
                            </tr>
                            <tr>
                              <td>60 MONTH</td>
                             <td>{{$monthly_new_ac_tenure60}}</td>
                              <td>{{$monthly_new_ac_amt_sum_tenure60}}</td>
                              <td>{{$monthly_renew_emi_recovery_60}}</td>
                              <td>{{$monthly_renew_emi_recovery_amnt_60}}</td>
                              <td>{{$monthly_mature_ac_tenure60}}</td>
                              <td>{{$monthly_mature_ac_amt_sum_tenure60}}</td>
                            </tr>
                            <tr>
                              <td>84 MONTH</td>
                              <td>{{$monthly_new_ac_tenure84}}</td>
                              <td>{{$monthly_new_ac_amt_sum_tenure84}}</td>
                              <td>{{$monthly_renew_emi_recovery_84}}</td>
                              <td>{{$monthly_renew_emi_recovery_amnt_84}}</td>
                              <td>{{$monthly_mature_ac_tenure84}}</td>
                              <td>{{$monthly_mature_ac_amt_sum_tenure84}}</td>
                            </tr>
                            <tr>
                              <td>120 MONTH</td>
                               <td>{{$monthly_new_ac_tenure120}}</td>
                              <td>{{$monthly_new_ac_amt_sum_tenure120}}</td>
                              <td>{{$monthly_renew_emi_recovery_120}}</td>
                              <td>{{$monthly_renew_emi_recovery_amnt_120}}</td>
                              <td>{{$monthly_mature_ac_tenure120}}</td>
                              <td>{{$monthly_mature_ac_amt_sum_tenure120}}</td>
                            </tr>
                           <tr>
                            <td>Kanyadan</td>
                              <td>{{$monthly_new_ac_tenurekanyadan}}</td>
                              <td>{{$monthly_new_ac_amt_sum_tenurekanyadan}}</td>
                              <td>{{$monthly_renew_emi_recovery_acnt_kanyadhan}}</td> 
                               <td>{{$monthly_renew_emi_recovery_amnt_sum}}</td> 
                              <td>{{$monthly_mature_fd_ac_tenure_kanyadhan}}</td>
                              <td>{{$monthly_mature_fd_ac_tenure_kanyadhan_amnt}}</td>
                                
                                </tr>
                            <tr>
                              <td>FD.12 MONTH</td>
                              <td>{{$monthly_new_fd_ac_tenure12}}</td>
                              <td>{{$monthly_new_fd_sum_ac_tenure12}}</td>
                              <td>{{$fd_renew_emi_recovery_12}}</td>
                              <td>{{$fd_renew_emi_recovery_amnt_12}}</td>
                              <td>{{$monthly_mature_fd_ac_tenure12}}</td>
                              <td>{{$monthly_mature_fd_sum_ac_tenure12}}</td>
                            </tr>
                            <tr>
                              <td>FD.18 MONTH</td>
                              <td>{{$monthly_new_fd_ac_tenure18}}</td>
                              <td>{{$monthly_new_fd_sum_ac_tenure18}}</td>
                              <td>{{$fd_renew_emi_recovery_18}}</td>
                              <td>{{$fd_renew_emi_recovery_amnt_18}}</td>
                              <td>{{$monthly_mature_fd_ac_tenure18}}</td>
                              <td>{{$monthly_mature_fd_sum_ac_tenure18}}</td>
                            </tr>
                            <tr>
                              <td>FD.48 MONTH</td>
                              <td>{{$monthly_new_fd_ac_tenure48}}</td>
                              <td>{{$monthly_new_fd_sum_ac_tenure48}}</td>
                              <td>{{$fd_renew_emi_recovery_48}}</td>
                              <td>{{$fd_renew_emi_recovery_amnt_48}}</td>
                              <td>{{$monthly_mature_fd_ac_tenure48}}</td>
                              <td>{{$monthly_mature_fd_sum_ac_tenure48}}</td>
                            </tr>
                            <tr>
                              <td>FD.60 MONTH</td>
                              <td>{{$monthly_new_fd_ac_tenure60}}</td>
                               <td>{{$monthly_new_fd_sum_ac_tenure60}}</td>
                              <td>{{$fd_renew_emi_recovery_60}}</td>
                              <td>{{$fd_renew_emi_recovery_amnt_60}}</td>
                              <td>{{$monthly_mature_fd_ac_tenure60}}</td>
                              <td>{{$monthly_mature_fd_sum_ac_tenure60}}</td>
                            </tr>
                            <tr>
                              <td>FD.72 MONTH</td>
                              <td>{{$monthly_new_fd_ac_tenure72}}</td>
                               <td>{{$monthly_new_fd_sum_ac_tenure72}}</td>
                              <td>{{$fd_renew_emi_recovery_72}}</td>
                              <td>{{$fd_renew_emi_recovery_amnt_72}}</td>
                              <td>{{$monthly_mature_fd_ac_tenure72}}</td>
                              <td>{{$monthly_mature_fd_sum_ac_tenure72}}</td>
                            </tr>
                            <tr>
                              <td>FD.96 MONTH</td>
                              <td>{{$monthly_new_fd_ac_tenure96}}</td>
                               <td>{{$monthly_new_fd_sum_ac_tenure96}}</td>
                              <td>{{$fd_renew_emi_recovery_96}}</td>
                              <td>{{$fd_renew_emi_recovery_amnt_96}}</td>
                              <td>{{$monthly_mature_fd_ac_tenure96}}</td>
                              <td>{{$monthly_mature_fd_sum_ac_tenure96}}</td>
                            </tr>
                            <tr>
                              <td>FD.120 MONTH</td>
                              <td>{{$monthly_new_fd_ac_tenure120}}</td>
                               <td>{{$monthly_new_fd_sum_ac_tenure120}}</td>
                              <td>{{$fd_renew_emi_recovery_120}}</td>
                              <td>{{$fd_renew_emi_recovery_amnt_120}}</td>
                              <td>{{$monthly_mature_fd_ac_tenure120}}</td>
                              <td>{{$monthly_mature_fd_sum_ac_tenure120}}</td>
                            </tr>
                            <tr>
                              <td>File Charge</td>
                              <td>{{$file_chrg_total}}</td>
                              <td>{{$file_chrg_amount_total}}</td>
                              <td></td>
                              <td></td>
                              <td></td>
                              <td></td>
                            </tr>
                            <tr>
                              <td>MI</td>
                              <td>mi_total</td>
                              <td>mi_amount_total</td>
                              
                              <td></td>
                              <td></td>
                              <td></td>
                              <td></td>
                            </tr>
                            <tr>
                              <td>STN</td>
                              <td>stn_total</td>
                              <td>stn_amount_total</td>
                              <td></td>
                              <td></td>
                              <td></td>
                              <td></td>
                            </tr>
                            <tr>
                              <td>Other</td>
                             <td>{{$other_total__income_account}}</td>
                              <td>{{number_format((float)$other_total__income_amount, 2, '.', '')}}</td>
                              <td>{{$other_total__expense_account}}</td>
                              <td>{{number_format((float)$other_total__expense_amount, 2, '.', '')}}</td>
                              <td>N/A</td>
                              <td>N/A</td>
                            </tr>
                            <tr>
                              <td>LOAN</td>
                              <td></td>
                              <td></td>
                              <td>{{$loan_total_account}}</td>
                              <td>{{$loan_total_amount}}</td>
                              <td></td>
                              <td></td>
                            </tr>
                            <tr>
                              <td>RECEIVED VOUCHER</td>
                              <td>{{$received_voucher_account}}</td>
                              <td>{{$received_voucher_amount}}</td>
                              <td></td>
                              <td></td>
                              <td></td>
                              <td></td>
                            </tr>
    </tbody>
</table>