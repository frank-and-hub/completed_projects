
<table border = "1" width = "100%" style="border-collapse: collapse;font-size:12px;">
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
$planids=array($planDaily,$planSSB,$planKanyadhan,$planMB,$planFFD,$planFRD,$planJeevan,$planMI,$planFD,$planRD,$planBhavhishya,);
$dailyId=array($planDaily);
$monthlyId=array($planKanyadhan,$planMB,$planFRD,$planJeevan,$planRD,$planBhavhishya);
$fdId=array($planMI,$planFFD,$planFD);

?>


<thead>
    <tr>
        <th style="font-weight: bold;">S/N</th>
		<th style="font-weight: bold;">BR Name</th>
		<th style="font-weight: bold;">BR Code</th>
		<th style="font-weight: bold;">SO Name</th>
		<th style="font-weight: bold;">RO Name</th>
		<th style="font-weight: bold;">ZO Name</th>
										
		<th style="font-weight: bold;">Daily NCC - No. A/C</th>
		<th style="font-weight: bold;">Daily NCC - Amt</th>
		<th style="font-weight: bold;">Daily Renew - No. A/C</th>
		<th style="font-weight: bold;">Daily Renew - Amt</th>

		<th style="font-weight: bold;">Monthly NCC - No. A/C</th>
		<th style="font-weight: bold;">Monthly NCC - Amt</th>
		<th style="font-weight: bold;">Monthly Renew- No. A/C</th>
		<th style="font-weight: bold;">Monthly Renew- Amt</th>

		<th style="font-weight: bold;">FD NCC -  No. A/C</th>
		<th style="font-weight: bold;">FD NCC - Amt</th>

		<th style="font-weight: bold;">SSB NCC -  No. A/C</th>
		<th style="font-weight: bold;">SSB NCC - Amt</th>
		<th style="font-weight: bold;">SSB Renew- No. A/C</th>
		<th style="font-weight: bold;">SSB Renew- Amt</th>

		<th style="font-weight: bold;">Other MI</th>
		<th style="font-weight: bold;">Other STN</th>

		<th style="font-weight: bold;">New MI Joining - No. A/C</th>
		 <th style="font-weight: bold;">New Associate Joining - No. A/C</th>

		<th style="font-weight: bold;">Banking - No. A/C</th>
		<th style="font-weight: bold;">Banking - Amt</th>

		<th style="font-weight: bold;">Total Payment - Payment</th>
		<th style="font-weight: bold;">Total Payment - Expense</th>
		<th style="font-weight: bold;">Total Payment - Withdrawal</th>

		<th style="font-weight: bold;">NCC_M</th>
		<th style="font-weight: bold;">NCC</th>
		<th style="font-weight: bold;">TCC_M</th>
		<th style="font-weight: bold;">TCC</th>

		<th style="font-weight: bold;">Loan - No. A/C</th>
		<th style="font-weight: bold;">Loan - Amt</th>
		<th style="font-weight: bold;">Loan Recovery - No. A/C</th>
		<th style="font-weight: bold;">Loan Recovery - Amt</th>

		<th style="font-weight: bold;">Loan Against Investment - No. A/C</th>

		<th style="font-weight: bold;">Loan Against Investment - Amt</th>

		<th style="font-weight: bold;">Loan Against Investment Recovery - No. A/C</th>

		<th style="font-weight: bold;">Loan Against Investment Recovery - Amt</th>

<!-- 
		<th style="font-weight: bold;">Cash In Hand Micro</th>
		<th style="font-weight: bold;">Cash In Hand Loan</th> -->
		<th style="font-weight: bold;">Cash Closing Balance</th>
    </tr>
</thead>


<tbody>

<?php 
$sno = 1;
foreach ($data as $row)
{
$branch_id = $row->id;
?>
	
	<tr>
	<td><?php echo $sno; ?></td>
	<td><?php echo $row->name; ?></td>
	<td><?php echo $row->branch_code; ?></td>
	<td><?php echo $row->sector; ?></td>
	<td><?php echo $row->regan; ?></td>
	<td><?php echo $row->zone; ?></td>
	
	<?php 
		$daily_new_ac = branchBusinessInvestNewAcCount($startDate,$endDate,$branch_id,$planDaily);
		$daily_deno_sum = branchBusinessInvestNewDenoSum($startDate,$endDate,$branch_id,$planDaily);
		$daily_renew_ac = branchBusinessInvestRenewAc($startDate,$endDate,$dailyId,$branch_id);
		$daily_renew = branchBusinessInvestRenewAmountSum($startDate,$endDate,$dailyId,$branch_id);
				
		$monthly_deno_sum =investNewDenoSumTypeBranch($startDate,$endDate,$monthlyId,$branch_id);
		$monthly_renew = investRenewAmountSumBranch($startDate,$endDate,$monthlyId,$branch_id);

		$rd_new_ac= branchBusinessInvestNewAcCountType($startDate,$endDate,$monthlyId,$branch_id);
		$rd_deno_sum = branchBusinessInvestNewDenoSumType($startDate,$endDate,$monthlyId,$branch_id);
		$rd_renew_ac = branchBusinessInvestRenewAc($startDate,$endDate,$monthlyId,$branch_id);
		$rd_renew = branchBusinessInvestRenewAmountSum($startDate,$endDate,$monthlyId,$branch_id);

		$fd_new_ac = branchBusinessInvestNewAcCountType($startDate,$endDate,$fdId,$branch_id);
		//$fd_deno_sum = number_format((float)branchBusinessInvestNewDenoSumType($startDate,$endDate,$fdId,$branch_id), 2, '.', '');;
		$fd_deno_sum = branchBusinessInvestNewDenoSumType($startDate,$endDate,$fdId,$branch_id);

		$ssb_new_ac = totalSSbAccountByType($startDate,$endDate,$branch_id,1);
		//$ssb_deno_sum = number_format((float)totalSSbAmtByType($startDate,$endDate,$branch_id,1), 2, '.', '');
		$ssb_deno_sum = totalSSbAmtByType($startDate,$endDate,$branch_id,1);
		$ssb_renew_ac = totalSSbAccountByType($startDate,$endDate,$branch_id,2);
		//$ssb_renew = number_format((float)totalSSbAmtByType($startDate,$endDate,$branch_id,2), 2, '.', '');
		$ssb_renew = totalSSbAmtByType($startDate,$endDate,$branch_id,2);

		$other_mi = totalOtherMiByType($startDate,$endDate,$branch_id,1,11);

		$stnAmount =totalOtherMiByType($startDate,$endDate,$branch_id,1,12);
        $stationaryAmount = totalOtherStatinarySTN($startDate,$endDate,$branch_id,21);
        $totalStnAmount = $stnAmount + $stationaryAmount;
		$other_stn = $totalStnAmount;

		$new_mi_joining = totalMijoining($startDate,$endDate,$branch_id);
        $new_associate_joining = getTotalFWbranchWise($startDate,$endDate,$branch_id);
		$total_ni_ac = totalNiACByCount($startDate,$endDate,$branch_id,7);
		$total_ni_amount = totalNiACByBranch($startDate,$endDate,$branch_id,7);

		//$total_payment = number_format((float)totalPaymentForMaturityAmount($startDate,$endDate,$branch_id,0), 2, '.', '');
		//$total_expense = number_format((float)totalPaymentExpense($startDate,$endDate,$branch_id,4), 2, '.', '');
		//$total_withdrawal = number_format((float)totalPaymentWithdrawal($startDate,$endDate,$branch_id,5), 2, '.', '');
		$total_payment = totalPaymentForMaturityAmount($startDate,$endDate,$branch_id,0);
		$total_expense = headTotalNew(4,$startDate,$endDate,$branch_id);
		$total_withdrawal = totalPaymentWithdrawal($startDate,$endDate,$branch_id,5);
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
         		$getBranchTotalBalance_cash=getBranchTotalBalanceAllTran($startDate, $currentdate,$getBranchOpening_cash->total_amount,$branch_id);
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
	?>
	
	<td><?php echo $daily_new_ac; ?></td>
	<td><?php echo $daily_deno_sum; ?></td>
	<td><?php echo $daily_renew_ac; ?></td>
	<td><?php echo $daily_renew; ?></td>

	<td><?php echo $rd_new_ac; ?></td>
	<td><?php echo $rd_deno_sum; ?></td>
	<td><?php echo $rd_renew_ac; ?></td>
	<td><?php echo $rd_renew; ?></td>
	<td><?php echo $fd_new_ac; ?></td>
	<td><?php echo $fd_deno_sum; ?></td>
	<td><?php echo $ssb_new_ac; ?></td>
	<td><?php echo $ssb_deno_sum; ?></td>
	<td><?php echo $ssb_renew_ac; ?></td>
	<td><?php echo $ssb_renew; ?></td>
	<td><?php echo $other_mi; ?></td>
	<td><?php echo $other_stn; ?></td>
	<td><?php echo $new_mi_joining; ?></td>
	<td><?php echo $new_associate_joining; ?></td>
	<td><?php echo $total_ni_ac; ?></td>
	<td><?php echo $total_ni_amount; ?></td>
	<td><?php echo $total_payment; ?></td>
	<td><?php echo $total_expense; ?></td>
	<td><?php echo $total_withdrawal; ?></td>
	<td><?php echo ((float)$daily_deno_sum + (float)$monthly_deno_sum + (float)$fd_deno_sum); ?></td>
	<td><?php echo ((float)$daily_deno_sum + (float)$monthly_deno_sum + (float)$fd_deno_sum + (float)$ssb_deno_sum); ?></td>
	<td><?php echo ((float)$daily_deno_sum + (float)$monthly_deno_sum + (float)$fd_deno_sum + (float)$daily_renew + (float)$monthly_renew); ?></td>
	<td><?php echo ((float)$daily_deno_sum + (float)$monthly_deno_sum + (float)$fd_deno_sum + (float)$ssb_deno_sum + (float)$daily_renew + (float)$monthly_renew + (float)$ssb_renew); ?></td>
	<td><?php echo loanSancationAccount($startDate,$endDate,$branch_id); ?></td>
	<td><?php echo number_format((float)loanSancationAmt($startDate,$endDate,$branch_id), 2, '.', ''); ?></td>
	<td><?php echo loanRecoverAccount($startDate,$endDate,$branch_id); ?></td>
	<td><?php echo number_format((float)loanRecoverAmt($startDate,$endDate,$branch_id), 2, '.', ''); ?></td>

	<td><?php echo loanAgainstSancationAccount($startDate,$endDate,$branch_id); ?></td>
	<td><?php echo number_format((float)loanAgainstSancationAmt($startDate,$endDate,$branch_id), 2, '.', ''); ?></td>
	<td><?php echo loanAgainstRecoverAccount($startDate,$endDate,$branch_id); ?></td>
	<td><?php echo number_format((float)loanAgainstRecoverAmt($startDate,$endDate,$branch_id), 2, '.', ''); ?></td>

	<!-- <td><?php echo getMicroEndDate($created_at,$endDate,$branch_id,0); ?></td>
	<td><?php echo getLoadEndDate($created_at,$endDate,$branch_id,1); ?></td> -->
	<td>{{ $C_balance_cash}}</td>
	<tr/>
<?php 
$sno = $sno + 1;	
} 
?>
</tbody>



</table>
