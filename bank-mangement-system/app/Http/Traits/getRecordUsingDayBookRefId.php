<?php

namespace App\Http\Traits;

use Carbon\Carbon;
use DB;
use App\Models\BranchDaybook;
use App\Models\MemberTransaction;
trait getRecordUsingDayBookRefId
{
    /**
     * Summary of records
     * @param mixed $modelName
     * @param mixed $daybookRefId
     * @return mixed
     */
    public function records($modelName, $daybookRefId)
    {
        
        $record = $modelName::where('daybook_ref_id', $daybookRefId)->update(['is_deleted' => 1]);
        BranchDaybook::where('daybook_ref_id', $daybookRefId)->update(['is_deleted' => 1]);
        MemberTransaction::where('daybook_ref_id', $daybookRefId)->update(['is_deleted' => 1]);
       
       
    }
      /**
       * Summary of cron
       * @param mixed $loanDetails
       * @return void
       */

    public function cron($loanDetails)
    {
        $entryTime = date("H:i:s");
        $Approvedate = date('Y-m-d', strtotime($loanDetails->approve_date));
        $gdate = checkMonthAvailability(date('d'),date('m'),date('Y'),$loanDetails->loanBranch->state_id);
        $date =  date('Y-m-d', strtotime(convertDate($gdate)));
         $status = DB::select('call calculate_loan_interest(?,?)',[$date,$loanDetails->account_number] );

      
        
       
    }
    /**
     * Summary of outstandingAmount_updateDaily
     * @param mixed $loanDetails
     * @return bool
     */
    public function outstandingAmount_updateDaily($loanDetails)
    {

        $value = $loanDetails;
        // $dataddd = DB::select('CALL daily_outstanding_procedure('. $value->account_number.','.$value->amount.')' );
        // dd($dataddd);
        $emi_date = array();
        $initOut = $value->amount;
        
        $date = date('Y-m-d', strtotime($value->approve_date));
        $datessss = date('Y', strtotime($value->approve_date));
        $datesssss = date('m', strtotime($value->approve_date));
        $dd = date('Y-m-d');
        $tgtg = date('m');
        $emiAmount = $value->emi_amount;
        $diff = today()->diffInDays($date);
        for ($i = 0; $i <= $diff; $i++) {
            $rr = date('Y-m-d', strtotime($date . ' + 1 days'));
            $date = date('Y-m-d', strtotime($date . ' + 1 days'));
            $emi_date[] = $date;
        }
        $endRecord = end(($emi_date));
        $checkData = \App\Models\LoanDayBooks::where('loan_sub_type', 0)->where('account_number', $value->account_number)->where(\DB::raw('Date(payment_date)'), '>', $endRecord)->where('is_deleted', 0)->get();
        $newDate = array();
        if (count($checkData) > 0) {
            foreach ($checkData as $index => $v) {
                $newDate[] = date('Y-m-d', strtotime($v->created_at));
            }
        }
        $emi_date = array_merge($emi_date, $newDate);
        $a = ((($value->ROI) / 365) * $initOut) / 100;
        $endRecord = end(($emi_date));
        $ad = date('Y-m-d', strtotime($value->approve_date . ' + 1 days'));
     
        for ($i = 0; $i < count($emi_date); $i++) {
            $da = $ad;
            $damount = 0;
         
            $mout = 0;
            $penaltys = 0;
            if (strtotime($da) < strtotime($endRecord)) {
                if (strtotime($emi_date[$i]) <= strtotime(date('Y-m-d'))) {

                    $abbb = $emi_date[$i];

                    $exists = \App\Models\LoanDayBooks::where('loan_sub_type', 0)->where('account_number', $value->account_number)->where(\DB::raw('DATE(payment_date)'), '=', $abbb)->where('is_deleted', 0)->exists();
                    $Countexists = \App\Models\LoanDayBooks::where('account_number', $value->account_number)->where(\DB::raw('DATE(payment_date)'), '=', $abbb)->where('is_deleted', 0)->sum('deposit');
                    $penaltyExists = \App\Models\LoanDayBooks::where('loan_sub_type', 1)->where('account_number', $value->account_number)->where(\DB::raw('DATE(payment_date)'), '=', $abbb)->where('is_deleted', 0)->exists();
                    $penalty = \App\Models\LoanDayBooks::where('loan_sub_type', 1)->where('account_number', $value->account_number)->where(\DB::raw('DATE(payment_date)'), '=', $abbb)->where('is_deleted', 0)->first();
                    $emiDetail = \App\Models\LoanDayBooks::where('loan_sub_type', 0)->where('account_number', $value->account_number)->where(\DB::raw('DATE(payment_date)'), '=', $abbb)->where('is_deleted', 0)->first();



                    if ($exists == false) {
                        $EmiId = NULL;
                        $transDate = NULL;
                        $checkout =     \App\Models\LoanEmisNew::where('loan_id', $value->id)->where('loan_type', $value->loan_type)->where('is_deleted', 0)->orderBy('id', 'desc')->exists();

                        if ($checkout == false) {

                            $mout = ($initOut + $a);
                            $initOut = $mout;
                            $ammountArray[] = $initOut;
                            $interest = $a;
                            $principalAmount = 0 - $a;;
                        } else {
                            $checkout = \App\Models\LoanEmisNew::where('loan_id', $value->id)->where('loan_type', $value->loan_type)->where('is_deleted', 0)->orderBy('id', 'desc')->first();
                            if (isset($checkout->out_standing_amount)) {

                                $newint =  ((($value->ROI) / 365) * $checkout->out_standing_amount) / 100;
                                $interest = $newint;
                                $principalAmount = 0 - $interest;;
                                $mout = ($checkout->out_standing_amount + ($newint));
                            }
                        }
                    } else {
                        if ($Countexists > 0) {

                            $damount = $Countexists;
                        } else {
                            $damount = $emiDetail->deposit;
                        }
                        $abbb = date('Y-m-d', strtotime($emiDetail->created_at));


                        $transDate = $emiDetail->created_at;
                        $EmiId = $emiDetail->id;
                        $checkout = \App\Models\LoanEmisNew::where('loan_id', $value->id)->where('loan_type', $value->loan_type)->where('is_deleted', 0)->orderBy('id', 'desc')->first();
                        if (isset($checkout->out_standing_amount)) {

                            $newint =  ((($value->ROI) / 365) * $checkout->out_standing_amount) / 100;
                            $principalAmount = $damount - $newint;

                            $interest = $newint;


                            $mout = ($checkout->out_standing_amount - $principalAmount);
                        } else {
                            $interest = $a;
                            $principalAmount = $damount - $interest;
                            $mout = ($initOut - ($principalAmount));
                        }
                    }
                    $CHECKr = \App\Models\LoanEmisNew::WHERE('loan_id', $value->id)->where('emi_date', $abbb)->where('out_standing_amount', $mout)->where('roi_amount', $interest)->where('principal_amount', $principalAmount)->where('is_deleted', 0)->exists();
                    if ($mout > 0) {
                        $ddd = \App\Models\LoanEmisNew::WHERE('loan_id', $value->id)->where('loan_type', $value->loan_type)->where('emi_date', $abbb)->where('is_deleted', 0)->exists();
                        if ($ddd == true) {
                            $createRecord = \App\Models\LoanEmisNew::WHERE('loan_id', $value->id)->where('loan_type', $value->loan_type)->where('emi_date', $abbb)->update(['loan_id' => $value->id, 'out_standing_amount' => $mout, 'emi_date' => $abbb, 'roi_amount' => $interest, 'principal_amount' => $principalAmount, 'emi_received_date' => $transDate, 'emi_id' => $EmiId, 'penalty' => $penaltys, 'emi_option' => $value->emi_option, 'deposit' => $damount, 'loan_type' => $value->loan_type]);
                        } else {
                            $createRecord = \App\Models\LoanEmisNew::create(['loan_id' => $value->id, 'out_standing_amount' => $mout, 'emi_date' => $abbb, 'roi_amount' => $interest, 'principal_amount' => $principalAmount, 'emi_received_date' => $transDate, 'emi_id' => $EmiId, 'penalty' => $penaltys, 'emi_option' => $value->emi_option, 'deposit' => $damount, 'loan_type' => $value->loan_type]);
                        }
                        
                    }
                }
            }

            $ad = date('Y-m-d', strtotime($da . ' + 1 days'));
        }
        return true;
    }

    /**
     * Summary of outstandingAmount_updateWeekly
     * @param mixed $loanDetails
     * @return void
     */
    public function outstandingAmount_updateWeekly($loanDetails)
    {
        $value = $loanDetails;
        $emi_date = array();
        $initOut = $value->amount;
        $date = date('Y-m-d', strtotime($value->approve_date));
        $datessss = date('W', strtotime($value->approve_date));
        $datesssss = date('m', strtotime($value->approve_date));
        $dd = date('Y-m-d');
        $tgtg = date('m');
        $penaltys = 0;

        $emiAmount = $value->emi_amount;

        $diff = number_format((float)today()->diffInDays($date) / 7, 0, '.', '');
        for ($i = 0; $i <= $diff; $i++) {
            $rr = date('Y-m-d', strtotime($date . ' + 7 days'));
            $date = date('Y-m-d', strtotime($date . ' + 7 days'));
            $emi_date[] = $date;
        }

        $endRecord = end(($emi_date));
        $checkData = \App\Models\LoanDayBooks::where('loan_sub_type', 0)->where('account_number', $value->account_number)->where('is_deleted', 0)/*->where(\DB::raw('Date(created_at)'),'>',$endRecord)*/->get();
        $checkDatalats = \App\Models\LoanDayBooks::where('loan_sub_type', 0)->where('is_deleted', 0)->where('account_number', $value->account_number)->orderBy('id', 'desc')->first();

        $newDate = array();
        if (count($checkData) > 0) {
            foreach ($checkData as $index => $v) {
                $newDate[] = date('Y-m-d', strtotime($v->created_at));
            }
        }
        $emi_date = array_merge($emi_date, $newDate);
        $a = ((($value->ROI) / 52.14) * $initOut) / 100;
        $endRecord =  end(($emi_date));;
        $ad = date('Y-m-d', strtotime($value->approve_date . ' + 7 days'));

        for ($i = 0; $i < count($emi_date); $i++) {

            $da = $ad;
            $penaltys = 0;
            $monthCheck = date('m', strtotime($da));
            $yearCheck = date('Y', strtotime($da));
            $wmonthCheck = date('m', strtotime($da));
            $wyearCheck = date('Y', strtotime($da));
            $gdate = '';
            $exdate = '';
            $eDate = '';
            //dd($emi_date[$i],$emi_date[$i+1]);
            $mout = 0;
            if (strtotime($da) <= strtotime($endRecord)) {
                if (strtotime($da) <= strtotime(date('Y-m-d'))) {
                    $abbb = $da;
                    if (in_array($da, $emi_date)) {
                        if ($i > 0) {
                            $gdate = $emi_date[$i - 1];
                            $exdate =  $emi_date[$i - 1];
                        } else {

                            $gdate = $value->created_at;
                            $exdate = $value->created_at;
                        }
                        $exists = \App\Models\LoanDayBooks::where('loan_sub_type', 0)->where('is_deleted', 0)->where('account_number', $value->account_number)->where(\DB::raw('Date(payment_date)'), '>', $gdate)->where(\DB::raw('Date(payment_date)'), '<=', $abbb)/*->where(\DB::raw('Year(created_at)') ,'=', $yearCheck )*/->exists();

                        $emiDetail = \App\Models\LoanDayBooks::where('loan_sub_type', 0)->where('is_deleted', 0)->where('account_number', $value->account_number)->where(\DB::raw('Date(payment_date)'), '>', $gdate)->where(\DB::raw('Date(payment_date)'), '<=', $abbb)/*->where(\DB::raw('Year(created_at)') ,'=', $yearCheck )*/->get();
                        $dexists = \App\Models\LoanDayBooks::where('account_number', $value->account_number)->where(\DB::raw('Date(payment_date)'), '=', $abbb)->where('is_deleted', 0)->exists();

                        if ($exists == false) {
                            $deposit = 0;
                            $damount = 0;
                            $EmiId = NULL;
                            $transDate = NULL;
                            $checkout =     \App\Models\LoanEmisNew::where('loan_id', $value->id)->where('loan_type', $value->loan_type)->where('is_deleted', 0)->orderBy('id', 'desc')->exists();

                            if ($checkout == false) {

                                $mout = ($initOut + $a);
                                $initOut = $mout;
                                $ammountArray[] = $initOut;
                                $interest = $a;
                                $principalAmount = 0 - $a;;
                            } else {
                                $checkout = \App\Models\LoanEmisNew::where('loan_id', $value->id)->where('loan_type', $value->loan_type)->where('is_deleted', 0)->orderBy('id', 'desc')->first();
                                if (isset($checkout->out_standing_amount)) {

                                    $newint =  ((($value->ROI) / 52.14) * $checkout->out_standing_amount) / 100;
                                    $interest = $newint;
                                    $principalAmount = 0 - $interest;;
                                    $mout = ($checkout->out_standing_amount + ($newint));
                                }
                            }

                            if ($mout >  0) {
                                $ddd = \App\Models\LoanEmisNew::WHERE('loan_id', $value->id)->where('loan_type', $value->loan_type)->where('emi_date', $abbb)->where('is_deleted', 0)->exists();

                                if ($ddd == false) {

                                    $createRecord = \App\Models\LoanEmisNew::create(['loan_id' => $value->id, 'out_standing_amount' => $mout, 'emi_date' => $abbb, 'roi_amount' => $interest, 'principal_amount' => $principalAmount, 'emi_received_date' => $transDate, 'emi_id' => $EmiId, 'penalty' => $penaltys, 'emi_option' => $value->emi_option, 'deposit' => $deposit, 'loan_type' => $value->loan_type]);
                                }
                            }
                        } else {

                            foreach ($emiDetail as $key => $emiSecond) {
                                $penaltyExists = \App\Models\LoanDayBooks::where('loan_sub_type', 1)->where('account_number', $value->account_number)->where(\DB::raw('Date(payment_date)'), '=', date('Y-m-d', strtotime($emiSecond->created_at)))->where('is_deleted', 0)/*->where(\DB::raw('Year(created_at)') ,'=', $yearCheck )*/->exists();

                                $penalty = \App\Models\LoanDayBooks::where('loan_sub_type', 1)->where('account_number', $value->account_number)->where(\DB::raw('Date(payment_date)'), '=', date('Y-m-d', strtotime($emiSecond->created_at)))->where('is_deleted', 0)/*->where(\DB::raw('Year(created_at)') ,'=', $yearCheck )*/->first();

                                $damount = $emiSecond->principal_amount;
                                $deposit = $emiSecond->deposit;


                                $abbb = date('Y-m-d', strtotime($emiSecond->created_at));
                                if (isset($penalty->principal_amount)) {
                                    $penaltys =    $penalty->principal_amount;
                                } else {
                                    $penaltys = 0;
                                }

                                // echo($abbb)."<br/>";
                                $transDate = $emiSecond->created_at;
                                $EmiId = $emiSecond->id;
                                $checkout = \App\Models\LoanEmisNew::where('loan_id', $value->id)->where('loan_type', $value->loan_type)->where('is_deleted', 0)->orderBy('id', 'desc')->first();
                                if (isset($checkout->out_standing_amount)) {

                                    $newint =  ((($value->ROI) / 52.14) * $checkout->out_standing_amount) / 100;
                                    $principalAmount = $deposit - $newint;

                                    $interest = $newint;


                                    $mout = ($checkout->out_standing_amount - $principalAmount);
                                } else {
                                    $interest = $a;
                                    $principalAmount = $deposit - $interest;
                                    $mout = ($initOut - ($principalAmount));
                                }


                                $ddd = \App\Models\LoanEmisNew::WHERE('loan_id', $value->id)->WHERE('emi_id', $emiSecond->id)->where('loan_type', $value->loan_type)->where('emi_date', $abbb)->where('is_deleted', 0)->exists();


                                if ($ddd == false) {

                                    $createRecord = \App\Models\LoanEmisNew::create(['loan_id' => $value->id, 'out_standing_amount' => $mout, 'emi_date' => $abbb, 'roi_amount' => $interest, 'principal_amount' => $principalAmount, 'emi_received_date' => $transDate, 'emi_id' => $EmiId, 'penalty' => $penaltys, 'emi_option' => $value->emi_option, 'deposit' => $deposit, 'loan_type' => $value->loan_type]);
                                } else {
                                    $createRecord = \App\Models\LoanEmisNew::WHERE('loan_id', $value->id)->WHERE('emi_id', $emiSecond->id)->where('loan_type', $value->loan_type)->where('emi_date', $abbb)->update(['loan_id' => $value->id, 'out_standing_amount' => $mout, 'emi_date' => $abbb, 'roi_amount' => $interest, 'principal_amount' => $principalAmount, 'emi_received_date' => $transDate, 'emi_id' => $EmiId, 'penalty' => $penaltys, 'emi_option' => $value->emi_option, 'deposit' => $deposit, 'loan_type' => $value->loan_type]);
                                }
                            }
                        }
                    }
                }
            }


            $ad = date('Y-m-d', strtotime($da . ' + 7 days'));
        }
    }

    public function outstandingAmount_update($loanDetails)
    {
        $value = $loanDetails;
        $emi_date = array();
        $initOut =$value->amount;
        $date = date('Y-m-d',strtotime($value->approve_date));
        $datessss = date('Y',strtotime($value->approve_date));
        $datesssss = date('m',strtotime($value->approve_date));
        $dd = date('Y');
        $tgtg = date('m');
        $deposit =0;

        $emiAmount = $value->emi_amount;
        $diff = (($dd - $datessss) * 12) + ($tgtg - $datesssss);
        for($i=0; $i< $diff; $i++)
        {
            $rr = date('Y-m-d', strtotime($date. ' + 1 months'));
            $date = date('Y-m-d', strtotime($date. ' + 1 months'));
            $emi_date[] = $date;

        }

        $endRecord = end(($emi_date));
        $checkData = \App\Models\LoanDayBooks::where('loan_sub_type',0)->where('account_number',$value->account_number)->where('is_deleted',0)/*->where(\DB::raw('Date(created_at)'),'>',$endRecord)*/->get();
        $newDate = array();
        if(count($checkData)>0)
        {
            foreach($checkData as $index =>$v)
            {
                $newDate[] = date('Y-m-d',strtotime($v->created_at));
            }
        }

        $emi_date = array_merge($emi_date,$newDate);
        $narray = array();
        foreach($emi_date as $KEY => $edate){

            if(!in_array($edate ,$narray)){
                $narray[str_replace('-','',$edate)] = $edate;
            }

        }

    
        $a = ((($value->ROI)/12) * $initOut)/100;
      
        $emi_date = ($narray);
           asort($emi_date);
     
        $emi_date = array_values($emi_date);
        $endRecord = end(($emi_date));
        $ad = date('Y-m-d', strtotime($value->approve_date. ' + 1 months'));

            for($i = 0; $i< count($emi_date); $i++){

                $da =$emi_date[$i];
                $deposit =0;

                $monthCheck = date('m', strtotime($da));
                $yearCheck = date('Y', strtotime($da));
                $wmonthCheck = date('m', strtotime($da));
                $wyearCheck = date('Y', strtotime($da));
                $gdate = '';
                $exdate = '';
                $eDate = '';
             
                $damount =0;

                $mout = 0;
                $penaltys=0;

                if(strtotime($da) <= strtotime($endRecord)){

                    if(strtotime($emi_date[$i]) <= strtotime(date('Y-m-d'))){

                        $abbb = $emi_date[$i];

                        if(in_array($da, $emi_date)){
                            if($i > 0)
                            {
                                $gdate = $emi_date[$i-1];
                                $exdate =  $emi_date[$i-1];
                            }
                            else{
                                $gdate = $value->created_at;
                                $exdate = $value->created_at;
                            }



                            $exists = \App\Models\LoanDayBooks::where('account_number',$value->account_number)->where(\DB::raw('Date(created_at)') ,'>', $gdate )->where(\DB::raw('Date(payment_date)') ,'<=', $abbb )->where('is_deleted',0)->exists();
                            $Countexists = \App\Models\LoanDayBooks::where('account_number',$value->account_number)->where(\DB::raw('Date(created_at)') ,'>', $gdate )->where(\DB::raw('Date(payment_date)') ,'<=', $abbb )->where('is_deleted',0)->sum('deposit');

                            $emiDetail = \App\Models\LoanDayBooks::where('account_number',$value->account_number)->where(\DB::raw('Date(created_at)') ,'>', $gdate )->where(\DB::raw('Date(payment_date)') ,'<=', $abbb )->where('is_deleted',0)->get();




                            if($exists == false){
                                $EmiId = NULL;
                                $transDate = NULL;
                                $checkout =     \App\Models\LoanEmisNew::where('loan_id',$value->id)->where('loan_type',$value->loan_type)->where('is_deleted',0)->orderBy('id','desc')->exists();

                                if($checkout == false){

                                    $mout = ($initOut + $a);
                                    $initOut = $mout;
                                    $ammountArray[]= $initOut;
                                    $interest = $a;
                                    $principalAmount =0 - $a;;
                                }
                                else{
                                    $checkout = \App\Models\LoanEmisNew::where('loan_id',$value->id)->where('loan_type',$value->loan_type)->where('is_deleted',0)->orderBy('id','desc')->first();
                                        if(isset($checkout->out_standing_amount)){

                                            $newint =  ((($value->ROI)/12) * $checkout->out_standing_amount)/100;
                                            $interest = $newint;
                                            $principalAmount =0 - $interest;;
                                            $mout = ($checkout->out_standing_amount + ($newint));

                                        }

                                    }
                                    if($mout >  0 )
                                    {
                                        $ddd = \App\Models\LoanEmisNew::WHERE('loan_id',$value->id)->where('loan_type',$value->loan_type)->where('emi_date',$abbb)->exists();

                                        if($ddd == false)
                                        {

                                          $createRecord = \App\Models\LoanEmisNew::create(['loan_id'=>$value->id,'out_standing_amount'=>$mout,'emi_date'=>$abbb,'roi_amount'=>$interest,'principal_amount'=>$principalAmount,'emi_received_date'=>$transDate,'emi_id'=>$EmiId,'penalty'=>$penaltys,'emi_option'=>$value->emi_option,'deposit'=>$damount,'loan_type'=>$value->loan_type]);

                                        }
                                        else{
                                            $createRecord = \App\Models\LoanEmisNew::WHERE('loan_id',$value->id)->where('loan_type',$value->loan_type)->where('emi_date',$abbb)->update(['loan_id'=>$value->id,'out_standing_amount'=>$mout,'emi_date'=>$abbb,'roi_amount'=>$interest,'principal_amount'=>$principalAmount,'emi_received_date'=>$transDate,'emi_id'=>$EmiId,'penalty'=>$penaltys,'emi_option'=>$value->emi_option,'deposit'=>$damount,'loan_type'=>$value->loan_type]);

                                        }

                                    }

                            }
                            else{
                                   
                                    foreach ($emiDetail as $key => $emiSecond) {

                                            $damount =$emiSecond->principal_amount;
                                            $deposit = $emiSecond->deposit;

                                        $penaltyExists = \App\Models\LoanDayBooks::where('loan_sub_type',1)->where('account_number',$value->account_number)->where(\DB::raw('Date(payment_date)') ,'=', date('Y-m-d',strtotime($emiSecond->created_at)) )->where('is_deleted',0)->exists();
                                        $penalty = \App\Models\LoanDayBooks:: where('loan_sub_type',1)->where('account_number',$value->account_number)->where(\DB::raw('Date(payment_date)') ,'=',date('Y-m-d',strtotime($emiSecond->created_at)) )->where('is_deleted',0)->sum('principal_amount');
                                        $abbb =date('Y-m-d',strtotime($emiSecond->created_at));

                                        // echo($abbb)."<br/>";
                                        $transDate =$emiSecond->created_at;
                                        $EmiId = $emiSecond->id;
                                        $checkout = \App\Models\LoanEmisNew::where('loan_id',$value->id)->where('loan_type',$value->loan_type)->where('is_deleted',0)->orderBy('id','desc')->first();
                                        if(isset($checkout->out_standing_amount))
                                        {

                                            $newint =  ((($value->ROI)/12) * $checkout->out_standing_amount)/100;
                                            $principalAmount = $deposit - $newint;

                                            $interest = $newint;

                                            $mout = ($checkout->out_standing_amount - $principalAmount);
                                           

                                        }
                                        else{
                                            $interest = $a;
                                            $principalAmount = $deposit - $interest;
                                            $mout = ($initOut - ($principalAmount ));
                                        }
                                        if($mout >  0 )
                                    {
                                        $ddd = \App\Models\LoanEmisNew::WHERE('loan_id',$value->id)->WHERE('emi_id',$emiSecond->id)->where('loan_type',$value->loan_type)->where('emi_date',$abbb)->where('is_deleted',0)->exists();

                                        if($ddd == false)
                                        {

                                          $createRecord = \App\Models\LoanEmisNew::create(['loan_id'=>$value->id,'out_standing_amount'=>$mout,'emi_date'=>$abbb,'roi_amount'=>$interest,'principal_amount'=>$principalAmount,'emi_received_date'=>$transDate,'emi_id'=>$EmiId,'penalty'=>$penaltys,'emi_option'=>$value->emi_option,'deposit'=>$deposit,'loan_type'=>$value->loan_type]);

                                        }
                                        else{
                                            $createRecord = \App\Models\LoanEmisNew:: WHERE('loan_id',$value->id)->where('loan_type',$value->loan_type)->where('emi_date',$abbb)->update(['loan_id'=>$value->id,'out_standing_amount'=>$mout,'emi_date'=>$abbb,'roi_amount'=>$interest,'principal_amount'=>$principalAmount,'emi_received_date'=>$transDate,'emi_id'=>$EmiId,'penalty'=>$penaltys,'emi_option'=>$value->emi_option,'deposit'=>$deposit,'loan_type'=>$value->loan_type]);
                                           
                                        }

                                    }

                                    }


                            }




                        }
                    }
                }

                
                $ad = date('Y-m-d', strtotime($da. ' + 1 months'));
            }



          




     

    }
}
