<?php

namespace App\Http\Traits;

trait MoneyBackCalculation
{
    public function calculate($accountNumber=NULL,$dateInfo)
    {

        $totalCollection = \App\Models\Daybook::where('account_no', $accountNumber->account_number)
                            ->whereIn('transaction_type', [2, 4])
                            ->whereBetween('created_at', [$dateInfo['startYearDate'],$dateInfo['endYearDate']])
                            ->where('is_deleted',0)
                            ->sum('deposit');

        $totalCollection = $totalCollection  + ($accountNumber->carry_forward_amount ?? 0)                      ;

        $openingBalance = \App\Models\Daybook::where('account_no', $accountNumber->account_number)

                            ->whereBetween('created_at', [$dateInfo['startYearDate'],$dateInfo['endYearDate']])
                            ->where('is_deleted',0)
                            ->latest()->first();


                          

        $totalOneYearDeposit = $accountNumber->deposite_amount * 12;
        $totalNineMonthDeposit = $accountNumber->deposite_amount * 9;
        $totalEightMonthDeposit = $accountNumber->deposite_amount * 8;
        
        $TransferedData  = $this->getMoneyBackAmount
            (
                $totalCollection,
                $totalOneYearDeposit,
                $totalNineMonthDeposit,
                $totalEightMonthDeposit,
                $openingBalance->opening_balance ?? 0
            );
      return $TransferedData;

    }

    public function getMoneyBackAmount($totalCollection,$totalOneYearAmount,$totalNineMonthDeposit,$totalEightMonthDeposit,$openingBalance)
    {
        $moneyBackAmount = 0;
        $carryForwardAmount = 0;
      

        $moneyBackAmount = ($totalCollection > $totalEightMonthDeposit)
                            ?
                                ($totalCollection >= $totalNineMonthDeposit && $totalCollection <= $totalOneYearAmount)
                                ?
                                 $totalCollection*60/100
                                :   $totalOneYearAmount*60/100
                            :0 ;

        $carryForwardAmount = ($totalCollection > $totalEightMonthDeposit)
                            ?
                                ($totalCollection >= $totalNineMonthDeposit && $totalCollection <= $totalOneYearAmount)
                                ?
                                0
                                :   $totalCollection-$totalOneYearAmount
                            : 0;


    return ['moneyBackAmount'=>$moneyBackAmount,'carryForwardAmount'=>$carryForwardAmount,'openingBalance'=>$openingBalance];

    }

}
