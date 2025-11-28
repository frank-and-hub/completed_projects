<?php

namespace App\Http\Traits;

use Carbon\Carbon;
use DB;

trait sumAmountTraits
{
    /**
     * Summary of cashAmountDetails
     * @param mixed $modelName
     * @param mixed $paymentType
     * @param mixed $paymentMode
     * @param mixed $startDate
     * @param mixed $endDate
     * @param mixed $column1
     * @param mixed $column2
     * @param mixed $column3
     * @param mixed $column4
     * @param mixed $column5
     * @param mixed $column6
     * @param mixed $event
     * @param mixed $perform
     * @param mixed $branch_id
     * @param mixed $type
     * @param mixed $subType
     * @return array
     */
    public function cashAmountDetails($modelName,$paymentType=NULL,$paymentMode,$startDate,$endDate,$column1,$column2,$column3,$column4,$column5,$column6,$event,$perform,$branch_id,$type=NULL,$subType=Null,$branchDate=NULL,$branchTotal=NULL,$company_id)
    {   
        DB::enableQueryLog();
        $balance_cash = 0;
        if(isset($type)){
            $data['approve'] = $modelName::where($column1, 'DR')->where($column5, $type)->where($column6, $subType)->when($branch_id > 0, function ($q) use($column3, $branch_id) {
                $q->where($column3, $branch_id);
            })->when($company_id > 0, function ($query)  use ($company_id){
                return $query->where('company_id', $company_id);
            })->whereBetween($column2, [$startDate, $endDate])->$event($perform);
        }

        if($branchDate==$startDate){
            $balance_cash =$branchTotal;
        }       

        if($branchDate<$startDate){
        $getBranchTotalBalance_cash=getBranchTotalBalanceAllTran($startDate,$branchDate,$branchTotal,$branch_id,$company_id);
        $balance_cash =$getBranchTotalBalance_cash;
        }
        $data['opening_balance'] = $balance_cash;   
        
       

        $data['CR'] = $modelName::where($column1, 'CR')->where($column4, $paymentMode)->when($branch_id > 0, function ($q) use($column3, $branch_id) {
            $q->where($column3, $branch_id);
        })->whereBetween($column2, [$startDate, $endDate])->where('is_deleted',0)->when($company_id > 0, function ($query)  use ($company_id){
            return $query->where('company_id', $company_id);
        })->$event($perform);

        $data['DR'] = $modelName::where($column1, 'DR')->when($company_id > 0, function ($query)  use ($company_id){
            return $query->where('company_id', $company_id);
        })->where($column4, $paymentMode)->when($branch_id > 0, function ($q) use($column3, $branch_id) {
            $q->where($column3, $branch_id);
        })->whereBetween($column2, [$startDate, $endDate])->where('is_deleted',0)->$event($perform);

        \DB::getQueryLog(); // Show results of log
        
       return $data;
    }

  
  
}
