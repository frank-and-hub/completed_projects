<?php

namespace App\Http\Traits;

trait isLoanTrait
{
    public function getData($model,$member,$type=NULL)
    {
        // Fetch all the data according to model
        if($type == 0)
        {
            $data =  $model::where('applicant_id',$member)->where('status','4')->exists();
        }
        if($type == 1)
        {
            $data =  $model::where('member_id',$member)->where('status','4')->exists();
        }

        if($data )
        {
            $isLoan = 'Yes';
        }
        else{
            $isLoan = 'No';
        }

        return $isLoan;
    }

    public function getIsLoan($model,$customer_id)
    {
         
            $data =  $model::where('customer_id',$customer_id)->where('status','4')->exists();
          

        if($data )
        {
            $isLoan = 'Yes';
        }
        else{
            $isLoan = 'No';
        }

        return $isLoan;
    }


    public function investmentDeatil($model,$investmentID,$fieldCompair)
    {
        // Fetch all the data according to model
        $data =  $model::where($fieldCompair,$investmentID)->first();
        return $data;
    }

    public function daybookDepositeSum($model,$investmentID)
    {
        // Fetch all the data according to model
        $data =  $model::where('investment_id',$investmentID)->whereIn('transaction_type',[2,4])->sum('deposit');
        return $data;
    }
    public function getDatabyCustomer($model,$customer,$type=NULL)
    {
        // Fetch all the data according to model
        if($type == 0)
        {
            $data =  $model::where('customer_id',$customer)->where('status',4)->exists();
        }
        if($type == 1)
        {
            $data =  $model::where('customer_id',$customer)->where('status',4)->exists();
        }
        
        if($data)
        {
            $isLoan = 'Yes';
        }
        else{
            $isLoan = 'No';
        }

        return $isLoan;
    }
}
