<?php

namespace App\Http\Traits;
use App\Http\Traits\EmiDatesTraits;


trait Oustanding_amount_trait
{
    use EmiDatesTraits;
    public function getoutstandingAmountData($model,$loanAccount,$emiOption,$startDate,$endDate,$approveDate,$ROI)
    {
        // Fetch all the data according to model
        if($startDate == '')
        {
            $data =  $model::where('loan_id',$loanAccount)->where('is_deleted','0')->orderBy('id','desc')->first('out_standing_amount');
            if(isset($data->out_standing_amount))
            {
                  $data = $data->out_standing_amount;   
            }
            else{
                 $data = '0';       
            }
            
           
        }
        else{
            $diffdays = today()->diffInMonths($approveDate);
         
            $nextEmiDates = $this->nextEmiDates($diffdays,$approveDate);
           if($startDate != '' && $endDate != '')
           {
                $date= date('Y-m-d',strtotime(convertDate($startDate)));   
                $applicationCurrentDate= date('d',strtotime(convertDate($startDate)));
                $applicationCurrentDateYear =date('Y',strtotime(convertDate($startDate)));
                $applicationCurrentDateMonth =date('m',strtotime(convertDate($startDate)));
                $data = $model::where('loan_id',$loanAccount)->where('emi_date',$date)->where('is_deleted','0')->orderBy('id','desc')->first(['out_standing_amount','roi_amount']);
               
                if(!empty($data))
                {
                   
                    $data = $data->out_standing_amount + $data->roi_amount;
                }
                else{
                    
                    //    if(!array_key_exists($applicationCurrentDate.'_'.$applicationCurrentDateMonth.'_'.$applicationCurrentDateYear,$nextEmiDates))
                    //    {

                       
                            $data = $model::where('loan_id',$loanAccount)->where('emi_date','<=',$date)->orderBy('id','desc')->first(['emi_date','out_standing_amount','roi_amount']);
                            if(isset($data))
                            {
                                 $to = \Carbon\Carbon::createFromFormat('Y-m-d', $startDate);
                                $from = \Carbon\Carbon::createFromFormat('Y-m-d', $data->emi_date);
                                $fromDate= date('Y-m-d',strtotime(convertDate($from)));
                                $diff_in_days = $to->diffInDays($from);
                                $roiAmount =  ((($ROI) / 365) * $data->out_standing_amount) / 100;
                                if(in_array($fromDate,$nextEmiDates))
                                {
                                    $amount = $roiAmount*$diff_in_days ;
                                }
                                else{
                                    $amount = $roiAmount*$diff_in_days + $data->roi_amount;
                                }
                                $data = $amount + $data->out_standing_amount;
                            }
                            else{
                                   $data = '0';  
                            }
                           
            
                        // }
                        // else{
                            
                        // }
                      
                    
                }
          
            }
        }
        return number_format((float)$data,2, '.', '');;
    }



    
}