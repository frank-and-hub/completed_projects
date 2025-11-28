<h4 class="card-title font-weight-semibold">Report Management | Duplicate Daybook Report</h4>
<?php
$f1 = 0;
$f2 = 0;
$tanuredata = getTanureDetail($company_id);

$start_date = $startDate;
$end_date = $endDate;

$file_chrg_total = App\Models\Daybook::whereIn('transaction_type', ['6,10'])->where('company_id', $company_id)->where('branch_id', $branch_id)->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate])->count();
$file_chrg_amount_total = App\Models\Daybook::whereIn('transaction_type', ['6,10'])->where('company_id', $company_id)->where('branch_id', $branch_id)->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate])->sum('amount');
$fund_transfer_total = App\Models\FundTransfer::where('transfer_type', 0)->where('company_id', $company_id)->where('branch_id', $branch_id)->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate])->count();
$fund_transfer_amount_total = App\Models\FundTransfer::where('transfer_type', 0)->where('company_id', $company_id)->where('branch_id', $branch_id)->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate])->sum('amount');
$mi_total = 0;
$mi_amount_total = 0;

$stn_total = 0;
$stn_amount_total = 0;
$other_total__income_account = getExpenseHeadaccountCount(3, 1, $startDate, $endDate, $branch_id);
$other_total__expense_account = getExpenseHeadaccountCount(4, 1, $startDate, $endDate, $branch_id);
$other_total__income_amount = headTotalNew(3, $startDate, $endDate, $branch_id,$company_id);
$other_total__expense_amount = headTotalNew(4, $startDate, $endDate, $branch_id,$company_id);
$investment_stationary_chrg_account = getInvestmentStationarychrgAccount($startDate, $endDate, $branch_id, $company_id);

$investment_stationary_chrg_amount = getInvestmentStationarychrgAmount($startDate, $endDate, $branch_id, $company_id);
$loan_total_account = App\Models\LoanDayBooks::where('loan_sub_type', 0)->where('branch_id', $branch_id)->where('company_id', $company_id)->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate])->where('is_deleted', 0)->count();
$loan_total_amount = App\Models\LoanDayBooks::where('loan_sub_type', 0)->where('branch_id', $branch_id)->where('company_id', $company_id)->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate])->where('is_deleted', 0)->sum('deposit');


$received_voucher_account = App\Models\ReceivedVoucher::where('branch_id', $branch_id)->where('company_id', $company_id)->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate])->count();;
$received_voucher_amount = App\Models\ReceivedVoucher::where('branch_id', $branch_id)->where('company_id', $company_id)->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate])->sum('amount');


$existsopening = \App\Models\BranchCurrentBalance::where('branch_id', $branch_id)->where('company_id', $company_id)->where('entry_date', $startDate)->exists();
if ($existsopening) {
    $cashInhandOpening =   \App\Models\BranchCurrentBalance::where('branch_id', $branch_id)->where('entry_date', $startDate)->where('company_id', $company_id)->orderBy('entry_date', 'DESC')->first();
    $cashInhandOpening = $cashInhandOpening->totalAmount;
} else {
    $cashInhandOpening =   \App\Models\BranchCurrentBalance::where('branch_id', $branch_id)->where('company_id', $company_id)->where('entry_date', '<=', $startDate)->orderBy('entry_date', 'DESC')->first();
    if (isset($cashInhandOpening->totalAmount)) {
        $cashInhandOpening = $cashInhandOpening->totalAmount;
    } else {
        $cashInhandOpening = 0;
    }
}
$cashInhandclosing = \App\Models\BranchCurrentBalance::where('branch_id', $branch_id)->where('company_id', $company_id)->where('entry_date', '<=', $endDate)->orderBy('entry_date', 'DESC')->first();

$cashInhandclosing = !empty($cashInhandclosing->totalAmount) ? $cashInhandclosing->totalAmount : 'N/A';
//  $cashInhandclosing = App\Models\BranchCash::where('branch_id',$branch_id)->where('entry_date','<=', $endDate)->orderBy('entry_date','DESC')->first();
//  $cashInhandclosing = $cashInhandclosing->balance + $cashInhandclosing->loan_balance;
$ssbNiAc = totalSSbAccountByType($startDate, $endDate, $branch_id, 1, $company_id);
$ssbNiAmount = number_format((float) totalSSbAmtByType($startDate, $endDate, $branch_id, 1, $company_id), 2, '.', '');
$ssbRenewAc = totalSSbAccountByType($startDate, $endDate, $branch_id, 2, $company_id);
$ssbRenewAmount = number_format((float) totalSSbAmtByType($startDate, $endDate, $branch_id, 2, $company_id), 2, '.', '');
$ssbWAc = totalSSbAccountByType($startDate, $endDate, $branch_id, 5, $company_id);
$ssbWAmount = number_format((float) totalSSbAmtByType($startDate, $endDate, $branch_id, 5, $company_id), 2, '.', '');
?>
<table>
    <tr>
        <table>
            <tr>
                <th colspan="3"><strong>Cash Allocation1</strong></th>
            </tr>
            <?php
            $getBranchOpening_cash = getBranchOpeningDetail($branch_id);
            $balance_cash = 0;
            $C_balance_cash = 0;
            if ($getBranchOpening_cash->date == $start_date) {
                $balance_cash = $getBranchOpening_cash->total_amount;
            }
            if ($getBranchOpening_cash->date < $start_date) {
                $getBranchTotalBalance_cash = getBranchTotalBalanceAllTran($start_date, $getBranchOpening_cash->date, $getBranchOpening_cash->total_amount, $branch_id, $company_id);
                $balance_cash = $getBranchTotalBalance_cash;
            }
            $getTotal_DR = getBranchTotalBalanceAllTranDR($start_date, $end_date, $branch_id, $company_id);
            $getTotal_CR = getBranchTotalBalanceAllTranCR($start_date, $end_date, $branch_id, $company_id);
            $totalBalance = $getTotal_CR - $getTotal_DR;
            $C_balance_cash = $balance_cash + $totalBalance;
            ?>
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
                <th colspan="3"><strong>Cheque</strong></th>
            </tr>
            <tr>
                <td>Opening</td>
                <td></td>
                <td>{{number_format((float)getchequeopeningBalance($startDate,$branch_id,$company_id), 2, '.', '')}}</td>
            </tr>
            <tr>
                <td></td>
                <td>Received</td>
                <td> Payment</td>
            </tr>
            <tr>
                <td>Cheque</td>
                <td>{{number_format((float)$cheque['CR'], 2, '.', '')}}</td>
                <td>{{number_format((float)$cheque['DR'], 2, '.', '')}}</td>
            </tr>
            <tr>
                <td>Closing</td>
                <td></td>
                <td>{{getchequeclosingBalance($endDate,$branch_id,$company_id)}}</td>
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
                    <td>@if(!empty($value['bankAccount'])) {{$value['bankAccount']->account_no}} @else N/A @endif</td>
                    <td>0</td>
                    <td>0</td>
                    <td>0</td>
                    <td>0</td>
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
            <th style="font-weight: bold;">Tr.by</th>
            <th style="font-weight: bold;">Receipt.No</th>
            <th style="font-weight: bold;">Account Number</th>
            <th style="font-weight: bold;">Company Name</th>
            <th style="font-weight: bold;">Plan Name</th>
            <th style="font-weight: bold;">Associate</th>
            <th style="font-weight: bold;">Member/Employee/Owner/Plan/Loan</th>
            <th style="font-weight: bold;">Narration</th>
            <th style="font-weight: bold;">CR Narration</th>
            <th style="font-weight: bold;">DR Narration</th>
            <th style="font-weight: bold;">CR Amount</th>
            <th style="font-weight: bold;">DR Amount</th>
            <th style="font-weight: bold;">Balance</th>
            <th style="font-weight: bold;">Ref Id</th>
            <th style="font-weight: bold;">Payment Type</th>
            <th style="font-weight: bold;">Tag</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $getBranchOpening = getBranchOpeningDetail($branch_id);
        $balance = 0;
        $is_eli = 0;
        if ($getBranchOpening->date == $start_date) {
            $balance = $getBranchOpening->total_amount;
        }
        if ($getBranchOpening->date < $start_date) {
            $getBranchTotalBalance = getBranchTotalBalanceAllTran($start_date, $getBranchOpening->date, $getBranchOpening->total_amount, $branch_id, $company_id);
            $balance = $getBranchTotalBalance;
        }
        ?>
        <tr>
            <td class="child" colspan="12" style=" text-align: right; ">Opening Balance </td>
            <td class="child" colspan="4"> {{number_format((float)$balance, 2, '.', '')}}</td>
        </tr>
        @foreach($data as $index => $value)
        <tr>
            <td>{{$index+1}}</td>
            <td>{{$value['tr_date']}}</td>
            <td>{{$value['tran_by']}}</td>
            <td>{{$value['bt_id']}}</td>
            <td>{{$value['member_account']}} </td>
            <td>{{$value['company_name']}} </td>
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
        <?php
        $data = DB::table('branch_daybook')->select('branch_daybook.*', 'branch_daybook.created_at as record_created_date', 'branch_daybook.payment_mode as branch_payment_mode', 'branch_daybook.payment_type as branch_payment_type', 'branch_daybook.member_id as branch_member_id', 'branch_daybook.associate_id as branch_associate_id', 'member_investments.id', 'branch_daybook.id as btid', 'branch_daybook.company_id')->leftjoin('member_investments', 'member_investments.id', 'branch_daybook.type_id')->where('branch_daybook.branch_id', $branch_id)->whereBetween('branch_daybook.entry_date', [$startDate, $endDate])->where('branch_daybook.is_deleted', 0)->where('branch_daybook.company_id', $company_id)->orderBy('branch_daybook.entry_date', 'ASC')->get();
        ?>
        @foreach($data as $index => $value)
        <?php
        if ($value->type == 3 && $value->sub_type == 30) {
            $is_eli = \App\Models\Daybook::where('id', $value->type_transaction_id)->where('company_id', $company_id)->first();
            $is_eli = $is_eli->is_eli;
        }
        if ($value->branch_payment_type == 'CR') {
            if ($value->branch_payment_mode == 0 && $is_eli == 0) {
                $balance = $balance + $value->amount;
            }
        }
        if ($value->branch_payment_type == 'DR') {
            if ($value->branch_payment_mode == 0 && $is_eli == 0) {
                $balance = $balance - $value->amount;
            }
        }
        ?>
        @endforeach
        <tr>
            <td class="child" colspan="12" style=" text-align: right; ">Closing Balance </td>
            <td class="child" colspan="4"> {{number_format((float)$balance, 2, '.', '')}}</td>
        </tr>
    </tbody>
</table>
<h4 class="card-title font-weight-semibold">Details 3 </h4>
<table class="my-4" border="1" width="100%" style="border-collapse: collapse;font-size:12px;">
    <thead>
        <tr>
            <th></th>
            <th colspan="2" style="font-weight: bold;">NI</th>
            <th colspan="2" style="font-weight: bold;">Renew/Emi Recovery </th>
            <th colspan="2" style="font-weight: bold;">Payment</th>
        </tr>
        <tr>
            <th style="font-weight: bold;">Plan</th>
            <th style="font-weight: bold;">Total No A/C</th>
            <th style="font-weight: bold;">Amount</th>
            <th style="font-weight: bold;">Total No A/C</th>
            <th style="font-weight: bold;">Amount</th>
            <th style="font-weight: bold;">Total No A/C</th>
            <th style="font-weight: bold;">Amount</th>
        </tr>
    </thead>
    <tbody>
        @php
        $planDaily=getPlanID('710')->id;
        $dailyId=array($planDaily);
        $planSSB=getPlanID('703')->id;
        $planKanyadhan=getPlanID('709')->id;
        $planMB=getPlanID('708')->id;
        $planFRD=getPlanID('707')->id;
        $planJeevan=getPlanID('713')->id;
        $planRD=getPlanID('704')->id;
        $planBhavhishya=getPlanID('718')->id;
        $planMI = getPlanID('712')->id;
        $planFFD = getPlanID('705')->id;
        $planFD = getPlanID('706')->id;
        $fdId=array($planMI,$planFFD,$planFD);
        $monthlyId=array($planKanyadhan,$planMB,$planFRD,$planJeevan,$planRD,$planBhavhishya);
        $totalmonthly_new_ac_tenure = 0;
        $totalmonthly_new_fd_ac_tenure = 0;
        $totalbranchBusinessTenureInvestNewAcCount = 0;
        $totalbranchBusinessTenureInvestNewDenoSum = 0;
        $totalgetrenewemirecovertotalAccount = 0;
        $totalgetrenewemirecovertotalAmount = 0;
        $totalmonthly_renew_emi_recovery_amnt = 0;
        $totalfd_renew_emi_recovery_amnt = 0;
        $gettotalmatureAccount = 0;
        $gettotalmatureAmount = 0;
        $totalbranchBusinessInvestTenureNewDenoSumType = 0;
        $totalfdbranchBusinessInvestTenureNewDenoSumType = 0;
        $matureInvestTenureNewAcCountType = 0;
        $monthly_mature_fd_ac_tenure = 0;
        $monthly_mature_ac_amt_sum_tenure = 0;
        $monthly_mature_fd_sum_ac_tenure = 0;
        $totalmonthlygetrenewemirecovertotalAccount = 0;
        $totalfddgetrenewemirecovertotalAccount = 0;
        $tenureids = [];
        @endphp
        @if(!empty($tanuredata))
        @foreach($tanuredata as $tenure)
        @php
        if($tenure->plan_category_code == 'D'){
        $plancategory = 'DAILY';
        }elseif($tenure->plan_category_code == 'M'){
        $plancategory = 'Monthly';
        }elseif($tenure->plan_category_code == 'S'){
        $plancategory = 'SAVING';
        }elseif($tenure->plan_category_code == 'F'){
        $plancategory = 'FIXED DEPOSIT';
        }

        $branchBusinessTenureInvestNewAcCount = branchBusinessTenureInvestNewAcCount($start_date, $end_date, $branch_id, $tenure->id, $tenure->tenure,$company_id);

        $branchBusinessTenureInvestNewDenoSum = branchBusinessTenureInvestNewDenoSum($start_date, $end_date, $branch_id, $tenure->id, $tenure->tenure,$company_id);
        $totalbranchBusinessTenureInvestNewDenoSum += $branchBusinessTenureInvestNewDenoSum;



        $getrenewemirecovertotalAccount = getrenewemirecovertotalAccount($start_date, $end_date, $branch_id, $tenure->tenure, $tenure->id,$company_id);
        $totalgetrenewemirecovertotalAccount += $getrenewemirecovertotalAccount;

        $getrenewemirecovertotalAmount = getrenewemirecovertotalAmount($start_date, $end_date, $branch_id, $tenure->tenure, $tenure->id,$company_id);
        $totalgetrenewemirecovertotalAmount += $getrenewemirecovertotalAmount;

        $totalmatureAccount = totalmatureAccount($start_date, $end_date, $branch_id, $tenure->id, $tenure->tenure,$company_id);
        $gettotalmatureAccount += $totalmatureAccount;

        $totalmatureAmount = totalmatureAmount($start_date, $end_date, $branch_id, $tenure->id, $tenure->tenure,$company_id);

        $gettotalmatureAmount += $totalmatureAmount;

        $totalbranchBusinessTenureInvestNewAcCount += branchBusinessTenureInvestNewAcCount($start_date, $end_date, $branch_id, $tenure->id, $tenure->tenure,$company_id);


        if($tenure->plan_category_code == 'M'){
        $totalmonthly_new_ac_tenure += branchBusinessInvestTenureNewAcCountType($start_date, $end_date, $branch_id, $tenure->id, $tenure->tenure,$company_id);
        $totalmonthly_renew_emi_recovery_amnt += getrenewemirecovertotalAmount($start_date, $end_date, $branch_id, $tenure->tenure, $tenure->id,$company_id);
        $matureInvestTenureNewAcCountType += matureInvestTenureNewAcCountType($start_date, $end_date, $branch_id, $tenure->tenure, $tenure->tenure,$company_id);
        $monthly_mature_ac_amt_sum_tenure += matureInvestTenureNewDenoSumType($start_date, $end_date, $branch_id, $tenure->id, $tenure->tenure,$company_id);
        $branchBusinessInvestTenureNewDenoSumType = branchBusinessInvestTenureNewDenoSumType($start_date,$end_date,$branch_id,$tenure->id,$tenure->tenure,$company_id=null);
        $totalbranchBusinessInvestTenureNewDenoSumType += $branchBusinessInvestTenureNewDenoSumType;
        $totalmonthlygetrenewemirecovertotalAccount += getrenewemirecovertotalAccount($start_date, $end_date, $branch_id, $tenure->tenure, $tenure->id,$company_id);
        }
        if($tenure->plan_category_code == 'F'){
        $totalfd_renew_emi_recovery_amnt += getrenewemirecovertotalAmount($start_date, $end_date, $branch_id, $tenure->tenure, $fdId,$company_id);
        $totalmonthly_new_fd_ac_tenure += branchBusinessInvestTenureNewAcCountType($start_date, $end_date, $branch_id, $tenure->id, $tenure->tenure ,$company_id);
        $monthly_mature_fd_ac_tenure += matureInvestTenureNewAcCountType($start_date, $end_date, $branch_id, $tenure->tenure, $tenure->tenure,$company_id);
        $monthly_mature_fd_sum_ac_tenure += matureInvestTenureNewDenoSumType($start_date, $end_date, $branch_id, $fdId, $tenure->tenure,$company_id);
        $totalfdbranchBusinessInvestTenureNewDenoSumType += branchBusinessInvestTenureNewDenoSumType($start_date,$end_date,$branch_id,$tenure->id,$tenure->tenure,$company_id=null);
        $totalfddgetrenewemirecovertotalAccount += getrenewemirecovertotalAccount($start_date, $end_date, $branch_id, $tenure->tenure, $tenure->id,$company_id);
        }
        $tenureids [] = $tenure->tenure;
        @endphp

        <tr>
            <td>{{$tenure->tenure}} {{$plancategory}}</td>
            <td>{{$branchBusinessTenureInvestNewAcCount}}</td>
            <td>{{$branchBusinessTenureInvestNewDenoSum}}</td>
            <td>{{$getrenewemirecovertotalAccount}}</td>
            <td>{{$getrenewemirecovertotalAmount}}</td>
            <td>{{$totalmatureAccount}}</td>
            <td>{{$totalmatureAmount}}</td>
        </tr>
        @endforeach
        @else
        <tr>
            <td colspan="7" class="text-center">No Record Found</td>
        </tr>
        @endif

        @php
        $renew_emi_monthly_recovery_kanyadhan = getmemberinvestementKanyadhanId($start_date, $end_date, $branch_id, $monthlyId, $tenureids,$company_id);
        @endphp
        @php
        $monthly_new_ac_tenurekanyadan = branchBusinessInvestTenureKanyadhan($start_date, $end_date, $branch_id, $monthlyId, $tenureids,$company_id);
        $branchBusinessInvestKanyadhanTenureNewDenoSumType = branchBusinessInvestKanyadhanTenureNewDenoSumType($start_date, $end_date, $branch_id, $monthlyId, $tenureids,$company_id);
        $getmemberinvestement_emi_recoverKanyadhan = getmemberinvestement_emi_recoverKanyadhan($start_date, $end_date, $branch_id, $renew_emi_monthly_recovery_kanyadhan,$company_id);
        $getmemberinvestement_emi_recoverKanyadhan_sum = getmemberinvestement_emi_recoverKanyadhan_sum($start_date, $end_date, $branch_id, $renew_emi_monthly_recovery_kanyadhan,$company_id);
        $matureInvestTenureKanyadhanNewAcCountType = matureInvestTenureKanyadhanNewAcCountType($start_date, $end_date, $branch_id, $fdId, $tenureids,$company_id);
        $matureInvestTenureKanyadhanAmount = matureInvestTenureKanyadhanAmount($start_date, $end_date, $branch_id, $fdId, $tenureids,$company_id);
        @endphp
        <tr>
            <td>Kanyadan</td>
            <td>{{$monthly_new_ac_tenurekanyadan}}</td>
            <td>{{$branchBusinessInvestKanyadhanTenureNewDenoSumType}}</td>
            <td>{{$getmemberinvestement_emi_recoverKanyadhan}}</td>
            <td>{{$getmemberinvestement_emi_recoverKanyadhan_sum}}</td>
            <td>{{$matureInvestTenureKanyadhanNewAcCountType}}</td>
            <td>{{$matureInvestTenureKanyadhanAmount}}</td>
        </tr>
        <tr>
            <td>SSB</td>
            <td>{{$ssbNiAc}}</td>
            <td>{{$ssbNiAmount}}</td>
            <td>{{$ssbRenewAc}}</td>
            <td>{{$ssbRenewAmount}}</td>
            <td>{{$ssbWAc}}</td>
            <td>{{$ssbWAmount}}</td>
        </tr>
        <tr>
            <td>Total</td>
            <?php
            $t1 = $totalbranchBusinessTenureInvestNewAcCount + $totalmonthly_new_ac_tenure + $monthly_new_ac_tenurekanyadan
                + $totalmonthly_new_fd_ac_tenure + $ssbNiAc;

            $t2 = $totalbranchBusinessTenureInvestNewDenoSum
                + $totalbranchBusinessInvestTenureNewDenoSumType
                + $totalfdbranchBusinessInvestTenureNewDenoSumType
                + $branchBusinessInvestKanyadhanTenureNewDenoSumType
                + $ssbNiAmount;


            $t3 = $totalgetrenewemirecovertotalAccount
                + $totalmonthlygetrenewemirecovertotalAccount
                + $totalfddgetrenewemirecovertotalAccount
                + $getmemberinvestement_emi_recoverKanyadhan
                + $ssbRenewAc;

            $t4 = $totalgetrenewemirecovertotalAmount
                + $totalmonthly_renew_emi_recovery_amnt
                + $totalfd_renew_emi_recovery_amnt
                + $ssbRenewAc;

            $t5 = $gettotalmatureAccount
                + $matureInvestTenureNewAcCountType
                + $matureInvestTenureKanyadhanNewAcCountType
                + $monthly_mature_fd_ac_tenure
                + $ssbWAc;


            $t6 = $gettotalmatureAmount
                + $monthly_mature_ac_amt_sum_tenure
                + $matureInvestTenureKanyadhanAmount
                + $monthly_mature_fd_sum_ac_tenure
                + $ssbWAmount;

            ?>
            <td>{{ $t1 }}</td>

            <td>{{number_format((float)$t2, 2, '.', '')}}</td>

            <td>{{$t3}}</td>

            <td>{{number_format((float)$t4, 2, '.', '')}}</td>

            <td>{{$t5}}</td>

            <td>{{number_format((float)$t6, 2, '.', '')}}</td>
        </tr>
        <tr>
            <td>Fund Transfer</td>
            <td> </td>
            <td> </td>
            <td> </td>
            <td> </td>
            <td>{{$fund_transfer_total}}</td>
            <td>{{number_format((float)$fund_transfer_amount_total, 2, '.', '')}}</td>
        </tr>
        <tr>
            <td>File Charge</td>
            <td>{{$file_chrg_total}}</td>
            <td>{{number_format((float)$file_chrg_amount_total, 2, '.', '')}}</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td>MI</td>
            <td>{{$mi_total}}</td>
            <td>{{number_format((float)$mi_amount_total, 2, '.', '')}}</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td>STN</td>
            <td>{{$stn_total}}</td>
            <td>{{number_format((float)$stn_amount_total, 2, '.', '')}}</td>
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
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td>Investment Stationery Charge</td>
            <td></td>
            <td></td>
            <td>{{$investment_stationary_chrg_account}}</td>
            <td>{{number_format((float)$investment_stationary_chrg_amount, 2, '.', '')}}</td>
            <td> </td>
            <td> </td>
        </tr>
        <tr>
            <td>LOAN</td>
            <td></td>
            <td></td>
            <td>{{$loan_total_account}}</td>
            <td>{{number_format((float)$loan_total_amount, 2, '.', '')}}</td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td>RECEIVED VOUCHER</td>
            <td>{{$received_voucher_account}}</td>
            <td>{{number_format((float)$received_voucher_amount, 2, '.', '')}}</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
    </tbody>
</table>
<? //die;
?>