<?php

namespace App\Console\Commands;

use App\Http\Controllers\Admin\CommanController;
use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Models\Memberinvestments;
use App\Models\Daybook;
use App\Models\MemberInvestmentInterest;
use DB;
use Session;

class GenerateInterestforTds extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'genrateinterestfortds:genrate';/**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate investment interest fron tds calculation';/**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }
    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {  
        die("xdfgfvxg");
        $entryTime = date("H:i:s");
        $date = Carbon::now()->format('Y-m-d H:i:s');

        $keyVal = 0;
        $cInterest = 0;
        $regularInterest = 0; 
        $total = 0;
        $monthly = array(10,11);
        $daily = array(7);
        $preMaturity = array(4,5);
        $fixed = array(9);
        $samraddhJeevan = array(2,6);
        $moneyBack = array(3);
        $totalDeposit = 0;
        $totalInterestDeposit = 0;

        $Investments = Memberinvestments::where('member_id',6156)->where('is_mature',1)->get();

        DB::beginTransaction();
        try {
            foreach ($Investments as $key => $val) {
                $mInvestment = $val;
                if($val->maturity_date >= $date){
                    $cDate = $date;
                }elseif($val->maturity_date < $date){
                    $cDate = $val->maturity_date;
                }

                if(in_array($mInvestment->plan_id, $monthly)){

                    if($val->investment_interest_date == ''){
                        if($cDate >= $val->maturity_date){
                            $investmentMonths = $mInvestment->tenure*12;
                        }else{

                            $ts1 = strtotime($mInvestment->created_at);
                            $ts2 = strtotime($cDate);
                            $year1 = date('Y', $ts1);
                            $year2 = date('Y', $ts2);
                            $month1 = date('m', $ts1);
                            $month2 = date('m', $ts2);
                            $investmentMonths = (($year2 - $year1) * 12) + ($month2 - $month1);

                        }  
                        $createDate = $val->created_at;
                    }else{
                        $ts1 = strtotime($val->investment_interest_date);
                        $ts2 = strtotime($cDate);
                        $year1 = date('Y', $ts1);
                        $year2 = date('Y', $ts2);
                        $month1 = date('m', $ts1);
                        $month2 = date('m', $ts2);
                        $investmentMonths = (($year2 - $year1) * 12) + ($month2 - $month1);
                        $createDate = $val->investment_interest_date;
                    }

                    $totalInvestmentAmount = Daybook::select('investment_id','deposit','created_at')->where('investment_id',$mInvestment->id)->whereDate('created_at','>=',$createDate)->whereIn('transaction_type', [2,4])->sum('deposit');

                    for ($i=1; $i <= $investmentMonths ; $i++){
                        $nDate =  date('Y-m-d', strtotime($createDate. ' + '.$i.' months')); 
                        $cMonth = date('m');
                        $cYear = date('Y');
                        $cuurentInterest = $mInvestment->interest_rate;
                        $totalDeposit = $totalInvestmentAmount;

                        $previousRecord = Daybook::select('deposit','created_at')->whereIn('transaction_type', [2,4])->where('investment_id', $val->investment_id)->whereDate('created_at', '<', $nDate)->max('created_at');

                        $sumPreviousRecordAmount = Daybook::whereIn('transaction_type', [2,4])->where('investment_id', $val->investment_id)->whereDate('created_at', '<=', $nDate)->sum('deposit');

                        $d1 = explode('-',$createDate);
                        $d2 = explode('-',$nDate);

                        $ts1 = strtotime($createDate);
                        $ts2 = strtotime($nDate);

                        $year1 = date('Y', $ts1);
                        $year2 = date('Y', $ts2);

                        $month1 = date('m', $ts1);
                        $month2 = date('m', $ts2);

                        $monthDiff = (($year2 - $year1) * 12) + ($month2 - $month1);

                        if($cMonth > $d2[1] && $cYear > $d2[0]){

                            if($previousRecord){
                                $previousDate = explode('-',$previousRecord);
                                $previousMonth = $previousDate[1];
                                if(($secondMonth-$previousMonth) >= 3 && $sumPreviousRecordAmount < $mInvestment->deposite_amount*$monthDiff){
                                    $defaulterInterest = 1.50;
                                    
                                }else{
                                    $defaulterInterest = 0;
                                }
                            }else{
                                $defaulterInterest = 0;
                            }
                        }else{
                            $defaulterInterest = 0;
                        }

                        if($val->deposite_amount*$monthDiff <= $totalDeposit){
                            $aviAmount = $val->deposite_amount;
                            $total = $total+$val->deposite_amount;
                            if($monthDiff % 3 == 0 && $monthDiff != 0){
                                $total = $total+$regularInterest;
                                $cInterest = $regularInterest;
                            }else{
                                $total = $total;
                                $cInterest = 0;
                            }
                            $regularInterest = $regularInterest+(($cuurentInterest-$defaulterInterest)*$total/1200);
                            $addInterest = ($cuurentInterest-$defaulterInterest);
                            $a = -$aviAmount+$aviAmount*(pow((1+$addInterest/400), (4*$i/12)));
                            $interest = number_format((float)$a, 2, '.', '');

                            $totalInterestDeposit = $totalInterestDeposit+$interest;
                        }elseif($val->deposite_amount*$monthDiff > $totalDeposit){
                            $checkAmount = ($totalDeposit-$val->deposite_amount*($monthDiff-1));
                            if($checkAmount > 0){
                               $aviAmount = $checkAmount; 

                               $total = $total+$checkAmount;
                                if($monthDiff % 3 == 0 && $monthDiff != 0){
                                    $total = $total+$regularInterest;
                                    $cInterest = $regularInterest;
                                }else{
                                    $total = $total;
                                    $cInterest = 0;
                                }
                                $regularInterest = $regularInterest+(($cuurentInterest-$defaulterInterest)*$total/1200);
                                $addInterest = ($cuurentInterest-$defaulterInterest);
                                $a = -$aviAmount+$aviAmount*(pow((1+$addInterest/400), (4*$i/12)));
                                $interest = number_format((float)$a, 2, '.', '');

                            }else{
                                $aviAmount = 0;
                                $total = 0;
                                $cuurentInterest = 0;
                                $interest = 0; 
                                $addInterest = 0; 
                            }
                            $totalInterestDeposit = $totalInterestDeposit+$interest;
                        }

                        if(round($interest) > 0){
                            MemberInvestmentInterest::create([
                                'member_id' => $val->member_id,
                                'investment_id' => $val->id,
                                'plan_type' => $val->plan_id,
                                'branch_id' => $val->branch_id,
                                'deposite_amount' => $totalDeposit,
                                'interest_amount' => $interest,
                                'date' =>$nDate,
                                'time' =>$entryTime,
                                'created_at' => date("Y-m-d ".$entryTime."", strtotime(convertDate($nDate))),
                            ]); 

                        }

                        Memberinvestments::where('id', $val->id)->update(['investment_interest_date'=>$nDate]);
                    }
                }elseif(in_array($mInvestment->plan_id, $daily)){

                    $cMonth = date('m');
                    $cYear = date('Y');
                    $cuurentInterest = $mInvestment->interest_rate;
                    
                    if($val->investment_interest_date == ''){
                        if($cDate >= $val->maturity_date){
                            $tenureMonths = $mInvestment->tenure*12;
                        }else{

                            $ts1 = strtotime($mInvestment->created_at);
                            $ts2 = strtotime($cDate);
                            $year1 = date('Y', $ts1);
                            $year2 = date('Y', $ts2);
                            $month1 = date('m', $ts1);
                            $month2 = date('m', $ts2);
                            $tenureMonths = (($year2 - $year1) * 12) + ($month2 - $month1);

                        }  
                        $createDate = $val->created_at;
                    }else{
                        $ts1 = strtotime($val->investment_interest_date);
                        $ts2 = strtotime($cDate);
                        $year1 = date('Y', $ts1);
                        $year2 = date('Y', $ts2);
                        $month1 = date('m', $ts1);
                        $month2 = date('m', $ts2);
                        $tenureMonths = (($year2 - $year1) * 12) + ($month2 - $month1);
                        $createDate = $val->investment_interest_date;
                    }

                    $i = 0;

                    for ($i = 0; $i <= $tenureMonths; $i++){
                        $newdate = date("Y-m-d", strtotime("".$i." month", strtotime($createDate))); 
                        
                        $implodeArray = explode('-',$newdate);
                        $year = $implodeArray[0];

                        $cdate = $createDate;
                        $cexplodedate = explode('-',$createDate);
                        if(($cexplodedate[1]+$i) > 12){
                            $month = ($cexplodedate[1]+$i)-12;
                        }else{
                            $month = $cexplodedate[1]+$i;
                        }

                        if(($i+1) == 13){
                            $fRecord = Daybook::where('investment_id', $mInvestment->id)
                            ->whereMonth('created_at', $month)->whereYear('created_at', $year)->first();
                            
                            if($fRecord){
                                $total = Daybook::where('investment_id', $mInvestment->id)->whereIn('transaction_type', [2,4])->where('id','>=',$fRecord->id)->sum('deposit');
                            }else{
                               $total = Daybook::where('investment_id', $mInvestment->id)
                            ->whereMonth('created_at', $month)->whereYear('created_at', $year)->sum('deposit'); 
                            }

                        }else{
                            $total = Daybook::where('investment_id', $mInvestment->id)->whereIn('transaction_type', [2,4])->whereMonth('created_at', $month)->whereYear('created_at', $year)->sum('deposit');
                        }

                        $totalDeposit = $totalDeposit+$total;

                        $countDays = Daybook::where('investment_id', $mInvestment->id)->whereIn('transaction_type', [2,4])->whereMonth('created_at', $month)->whereYear('created_at', $year)->count();

                        if($total < $mInvestment->deposite_amount*25){
                            $defaulterInterest = 1.50;
                        }else{
                            $defaulterInterest = 0;
                        }

                        if($total > 0){
                            $aviAmount = $mInvestment->deposite_amount;
                        }else{
                            $aviAmount = 0;
                        }

                        if(($tenureMonths-$i) == 0){
                            $aviAmount = 0;
                            $interestRate = 0;
                        }else{
                            $interestRate = $cuurentInterest-$defaulterInterest;
                        }

                        if($tenureMonths >= 12 && $tenureMonths < 24){
                            $interest = (($cuurentInterest-$defaulterInterest)*$totalDeposit*100)/117800;
                        }elseif($tenureMonths >= 24 && $tenureMonths < 36){
                            $interest = (($cuurentInterest-$defaulterInterest)*$totalDeposit*100)/115000;
                        }elseif($tenureMonths >= 36 && $tenureMonths < 60){
                            $interest = (($cuurentInterest-$defaulterInterest)*$totalDeposit*100)/111900;
                        }elseif($tenureMonths >= 60){
                            $interest = (($cuurentInterest-$defaulterInterest)*$totalDeposit*100)/106100;
                        }
                        
                        if(($tenureMonths-$i) == 0){
                            $interest = 0;
                        }
                        $totalInterestDeposit = $totalInterestDeposit+$interest;

                        if(round($interest) > 0){
                            MemberInvestmentInterest::create([
                                'member_id' => $val->member_id,
                                'investment_id' => $val->id,
                                'plan_type' => $val->plan_id,
                                'branch_id' => $val->branch_id,
                                'deposite_amount' => $totalDeposit,
                                'interest_amount' => $interest,
                                'date' =>$newdate,
                                'time' =>$entryTime,
                                'created_at' =>date("Y-m-d ".$entryTime."", strtotime(convertDate($newdate))),
                            ]); 

                        }

                        Memberinvestments::where('id', $val->id)->update(['investment_interest_date'=>$newdate]);
                    }
                }elseif(in_array($mInvestment->plan_id, $preMaturity)){
                    
                    if($val->investment_interest_date == ''){
                        if($cDate >= $val->maturity_date){
                            $ts1 = strtotime($mInvestment->created_at);
                            $ts2 = strtotime($val->maturity_date);
                            $year1 = date('Y', $ts1);
                            $year2 = date('Y', $ts2);
                            $month1 = date('m', $ts1);
                            $month2 = date('m', $ts2);
                            $monthDiff = (($year2 - $year1) * 12) + ($month2 - $month1);
                        }else{

                            $ts1 = strtotime($mInvestment->created_at);
                            $ts2 = strtotime($cDate);
                            $year1 = date('Y', $ts1);
                            $year2 = date('Y', $ts2);
                            $month1 = date('m', $ts1);
                            $month2 = date('m', $ts2);
                            $monthDiff = (($year2 - $year1) * 12) + ($month2 - $month1);

                        }  
                        $createDate = $val->created_at;
                    }else{
                        $ts1 = strtotime($val->investment_interest_date);
                        $ts2 = strtotime($cDate);
                        $year1 = date('Y', $ts1);
                        $year2 = date('Y', $ts2);
                        $month1 = date('m', $ts1);
                        $month2 = date('m', $ts2);
                        $monthDiff = (($year2 - $year1) * 12) + ($month2 - $month1);
                        $createDate = $val->investment_interest_date;
                    }

                    $newdate = date("Y-m-d", strtotime("".$monthDiff." month", strtotime($createDate))); 
                    $totalInvestmentAmount = Daybook::select('investment_id','deposit','created_at')->where('investment_id',$mInvestment->id)->whereDate('created_at','>=',$createDate)->whereIn('transaction_type', [2,4])->sum('deposit');

                    

                    if($mInvestment->plan_id == 4){
                        if($monthDiff >= 0 && $monthDiff <= 36){
                            $cuurentInterest = 8;
                        }else if($monthDiff >= 37 && $monthDiff <= 48){
                            $cuurentInterest = 8.25;
                        }else if($monthDiff >= 49 && $monthDiff <= 60){
                            $cuurentInterest = 8.50;
                        }else if($monthDiff >= 61 && $monthDiff <= 72){
                            $cuurentInterest = 8.75;
                        }else if($monthDiff >= 73 && $monthDiff <= 84){
                            $cuurentInterest = 9;
                        }else if($monthDiff >= 85 && $monthDiff <= 96){
                            $cuurentInterest = 9.50;
                        }else if($monthDiff >= 97 && $monthDiff <= 108){
                            $cuurentInterest = 10;
                        }else if($monthDiff >= 109 && $monthDiff <= 120){
                            $cuurentInterest = 11;
                        }
                    }elseif($mInvestment->plan_id == 5){
                        if($monthDiff >= 0 && $monthDiff <= 12){
                            $cuurentInterest = 5;
                        }else if($monthDiff >= 12 && $monthDiff <= 24){
                            $cuurentInterest = 6;
                        }else if($monthDiff >= 24 && $monthDiff <= 36){
                            $cuurentInterest = 6.50;
                        }else if($monthDiff >= 36 && $monthDiff <= 48){
                            $cuurentInterest = 7;
                        }else if($monthDiff >= 48 && $monthDiff <= 60){
                            $cuurentInterest = 9;
                        }
                    }


                    if($mInvestment->plan_id == 4){

                        $defaulterInterest = 0;

                        $irate = ($cuurentInterest-$defaulterInterest) / 1;

                        $year = $monthDiff / 12;

                        $result =  ( $totalInvestmentAmount*(pow((1 + $irate / 100), $year)))-($totalInvestmentAmount);

                    }else{

                        if($cDate < $val->maturity_date && $monthDiff != 60){
                            $defaulterInterest = 1.50;
                        }else{
                            $defaulterInterest = 0;
                        }

                        $irate = ($cuurentInterest-$defaulterInterest) / 1;

                        $year = $monthDiff / 12;

                        $maturity=0;
                        $freq = 4;

                        if($totalInvestmentAmount < $mInvestment->deposite_amount*$monthDiff){
                            for($i=1; $i<=$monthDiff;$i++){
                                $rmaturity = ($mInvestment->deposite_amount*(pow((1+(($irate/100)/$freq)), $freq*(($monthDiff-$i+1)/12))));
                                $maturity = $maturity+$rmaturity;
                            }
                        }else{
                            for($i=1; $i<=$monthDiff;$i++){
                                $rmaturity = ($totalInvestmentAmount*(pow((1+(($irate/100)/$freq)), $freq*(($monthDiff-$i+1)/12))));
                                $maturity = $maturity+$rmaturity;
                            }
                        }

                        $result =  $maturity-$totalInvestmentAmount;
                    }

                    if(round($result) > 0){
                        MemberInvestmentInterest::create([
                            'member_id' => $val->member_id,
                            'investment_id' => $val->id,
                            'plan_type' => $val->plan_id,
                            'branch_id' => $val->branch_id,
                            'deposite_amount' => $totalInvestmentAmount,
                            'interest_amount' => $result,
                            'date' =>$newdate,
                            'time' =>$entryTime,
                            'created_at' =>date("Y-m-d ".$entryTime."", strtotime(convertDate($newdate))),
                        ]); 

                    }

                    Memberinvestments::where('id', $val->id)->update(['investment_interest_date'=>$newdate]);
                }elseif(in_array($mInvestment->plan_id, $fixed)){

                    if($val->investment_interest_date == ''){
                        if($cDate >= $val->maturity_date){
                            $ts1 = strtotime($mInvestment->created_at);
                            $ts2 = strtotime($val->maturity_date);
                            $year1 = date('Y', $ts1);
                            $year2 = date('Y', $ts2);
                            $month1 = date('m', $ts1);
                            $month2 = date('m', $ts2);
                            $monthDiff = (($year2 - $year1) * 12) + ($month2 - $month1);
                        }else{

                            $ts1 = strtotime($mInvestment->created_at);
                            $ts2 = strtotime($cDate);
                            $year1 = date('Y', $ts1);
                            $year2 = date('Y', $ts2);
                            $month1 = date('m', $ts1);
                            $month2 = date('m', $ts2);
                            $monthDiff = (($year2 - $year1) * 12) + ($month2 - $month1);

                        }  
                        $createDate = $val->created_at;
                    }else{
                        $ts1 = strtotime($val->investment_interest_date);
                        $ts2 = strtotime($cDate);
                        $year1 = date('Y', $ts1);
                        $year2 = date('Y', $ts2);
                        $month1 = date('m', $ts1);
                        $month2 = date('m', $ts2);
                        $monthDiff = (($year2 - $year1) * 12) + ($month2 - $month1);
                        $createDate = $val->investment_interest_date;
                    }

                    $newdate = date("Y-m-d", strtotime("".$monthDiff." month", strtotime($createDate))); 
                    $cDate = date('Y-m-d');
                    $cYear = date('Y');
                    $cuurentInterest = $mInvestment->interest_rate;

                    if($cDate < $val->maturity_date){
                        $defaulterInterest = 1.50;
                    }else{
                        $defaulterInterest = 0;
                    }

                    if($mInvestment->plan_id == 8){
                        $result =  0;
                    }else{
                        $irate = ($cuurentInterest-$defaulterInterest) / 1;
                        $year = $monthDiff / 12;
                        $result =  ( $mInvestment->deposite_amount*(pow((1 + $irate / 100), $year)))-($mInvestment->deposite_amount);
                    }

                    if(round($result) > 0){
                        MemberInvestmentInterest::create([
                            'member_id' => $val->member_id,
                            'investment_id' => $val->id,
                            'plan_type' => $val->plan_id,
                            'branch_id' => $val->branch_id,
                            'deposite_amount' => $val->deposite_amount,
                            'interest_amount' => $result,
                            'date' =>$newdate,
                            'time' =>$entryTime,
                            'created_at' =>date("Y-m-d ".$entryTime."", strtotime(convertDate($newdate))),
                        ]); 

                    }

                    Memberinvestments::where('id', $val->id)->update(['investment_interest_date'=>$newdate]);
                }elseif(in_array($mInvestment->plan_id, $samraddhJeevan)){
      
                    if($val->investment_interest_date == ''){
                        if($cDate >= $val->maturity_date){
                            $investmentMonths = $mInvestment->tenure*12;
                        }else{

                            $ts1 = strtotime($mInvestment->created_at);
                            $ts2 = strtotime($cDate);
                            $year1 = date('Y', $ts1);
                            $year2 = date('Y', $ts2);
                            $month1 = date('m', $ts1);
                            $month2 = date('m', $ts2);
                            $investmentMonths = (($year2 - $year1) * 12) + ($month2 - $month1);

                        }  
                        $createDate = $val->created_at;
                    }else{
                        $ts1 = strtotime($val->investment_interest_date);
                        $ts2 = strtotime($cDate);
                        $year1 = date('Y', $ts1);
                        $year2 = date('Y', $ts2);
                        $month1 = date('m', $ts1);
                        $month2 = date('m', $ts2);
                        $investmentMonths = (($year2 - $year1) * 12) + ($month2 - $month1);
                        $createDate = $val->investment_interest_date;
                    }

                    $totalInvestmentAmount = App\Models\Daybook::select('investment_id','deposit','created_at')->where('investment_id',$mInvestment->id)->whereDate('created_at','>=',$createDate)->whereIn('transaction_type', [2,4])->sum('deposit');
                        
                    for ($i=1; $i <= $investmentMonths ; $i++){
                        $val = $mInvestment;
                        $nDate =  date('Y-m-d', strtotime($createDate. ' + '.$i.' months')); 
                        $cMonth = date('m');
                        $cYear = date('Y');
                        $cMonth = date('m');
                        $cYear = date('Y');
                        if($mInvestment->plan_id == 2){
                            $cuurentInterest = $val->interest_rate;
                        }elseif($mInvestment->plan_id == 6){
                            $cuurentInterest = 11;
                        }
                        $totalDeposit = $totalInvestmentAmount;

                        $d1 = explode('-',$createDate);
                        $d2 = explode('-',$nDate);

                        $ts1 = strtotime($createDate);
                        $ts2 = strtotime($nDate);

                        $year1 = date('Y', $ts1);
                        $year2 = date('Y', $ts2);

                        $month1 = date('m', $ts1);
                        $month2 = date('m', $ts2);

                        $monthDiff = (($year2 - $year1) * 12) + ($month2 - $month1);

                        if($val->deposite_amount*$monthDiff <= $totalDeposit){
                            $aviAmount = $val->deposite_amount;
                            $total = $total+$val->deposite_amount;
                            if($monthDiff % 12 == 0 && $monthDiff != 0){
                                $total = $total+$regularInterest;
                                $cInterest = $regularInterest;
                            }else{
                                $total = $total;
                                $cInterest = 0;
                            }
                            $regularInterest = $regularInterest+($cuurentInterest*$total/1200);
                            $a = -$aviAmount+$aviAmount*(pow((1+$cuurentInterest/100), (1*$i/12)));
                            $interest = number_format((float)$a, 2, '.', '');
                            $totalInterestDeposit = $totalInterestDeposit+($interest);
                            
                        }elseif($val->deposite_amount*$monthDiff > $totalDeposit){
                            
                            $checkAmount = ($totalDeposit-$val->deposite_amount*($monthDiff-1));
                            if($checkAmount > 0){
                               $aviAmount = $checkAmount; 

                               $total = $total+$checkAmount;
                                if($monthDiff % 12 == 0 && $monthDiff != 0){
                                    $total = $total+$regularInterest;
                                    $cInterest = $regularInterest;
                                }else{
                                    $total = $total;
                                    $cInterest = 0;
                                }
                                $regularInterest = $regularInterest+($cuurentInterest*$total/1200);
                                $a = -$aviAmount+$aviAmount*(pow((1+$cuurentInterest/100), (1*$i/12)));
                                $interest = number_format((float)$a, 2, '.', '');

                            }else{
                                $aviAmount = 0;
                                $total = 0;
                                $cuurentInterest = 0;
                                $interest = 0; 
                                $a = 0;
                            }
                            $totalInterestDeposit = $totalInterestDeposit+($interest);
                        }

                        if(round($interest) > 0){
                            MemberInvestmentInterest::create([
                                'member_id' => $val->member_id,
                                'investment_id' => $val->id,
                                'plan_type' => $val->plan_id,
                                'branch_id' => $val->branch_id,
                                'deposite_amount' => $totalDeposit,
                                'interest_amount' => $interest,
                                'date' =>$nDate,
                                'time' =>$entryTime,
                                'created_at' =>date("Y-m-d ".$entryTime."", strtotime(convertDate($nDate))),
                            ]); 

                        }

                        Memberinvestments::where('id', $val->id)->update(['investment_interest_date'=>$nDate]);
                    }   
                }elseif(in_array($mInvestment->plan_id, $moneyBack)){

                    if($val->investment_interest_date == ''){
                        if($cDate >= $val->maturity_date){
                            $investmentMonths = $mInvestment->tenure*12;
                        }else{

                            $ts1 = strtotime($mInvestment->created_at);
                            $ts2 = strtotime($cDate);
                            $year1 = date('Y', $ts1);
                            $year2 = date('Y', $ts2);
                            $month1 = date('m', $ts1);
                            $month2 = date('m', $ts2);
                            $investmentMonths = (($year2 - $year1) * 12) + ($month2 - $month1);

                        }  
                        $createDate = $val->created_at;
                    }else{
                        $ts1 = strtotime($val->investment_interest_date);
                        $ts2 = strtotime($cDate);
                        $year1 = date('Y', $ts1);
                        $year2 = date('Y', $ts2);
                        $month1 = date('m', $ts1);
                        $month2 = date('m', $ts2);
                        $investmentMonths = (($year2 - $year1) * 12) + ($month2 - $month1);
                        $createDate = $val->investment_interest_date;
                    }

                    $totalInvestmentAmount = Daybook::select('investment_id','deposit','created_at')->where('investment_id',$mInvestment->id)->whereDate('created_at','>=',$createDate)->whereIn('transaction_type', [2,4])->sum('deposit');

                    for ($i=1; $i <= $investmentMonths ; $i++){
              
                        $val = $mInvestment;
                        $nDate =  date('Y-m-d', strtotime($createDate. ' + '.$i.' months')); 
                        $cMonth = date('m');
                        $cYear = date('Y');
                        $cuurentInterest = $mInvestment->interest_rate;
                        $totalDeposit = $totalInvestmentAmount;

                        $ts1 = strtotime($createDate);
                        $ts2 = strtotime($nDate);

                        $year1 = date('Y', $ts1);
                        $year2 = date('Y', $ts2);

                        $month1 = date('m', $ts1);
                        $month2 = date('m', $ts2);

                        $monthDiff = (($year2 - $year1) * 12) + ($month2 - $month1);
                        $defaulterInterest = 0;
                        if($val->deposite_amount*$monthDiff <= $totalDeposit){
                            $aviAmount = $val->deposite_amount;
                            $total = $total+$val->deposite_amount;
                            if($monthDiff % 3 == 0 && $monthDiff != 0){
                                $total = $total+$regularInterest;
                                $cInterest = $regularInterest;
                            }else{
                                $total = $total;
                                $cInterest = 0;
                            }
                            $regularInterest = $regularInterest+(($cuurentInterest-$defaulterInterest)*$total/1200);
                            $addInterest = ($cuurentInterest-$defaulterInterest);
                            $a = -$aviAmount+$aviAmount*(pow((1+$addInterest/400), (4*$i/12)));
                            $interest = number_format((float)$a, 2, '.', '');

                            $totalInterestDeposit = $totalInterestDeposit+$interest;
                        }elseif($val->deposite_amount*$monthDiff > $totalDeposit){
                            $checkAmount = ($totalDeposit-$val->deposite_amount*($monthDiff-1));
                            if($checkAmount > 0){
                               $aviAmount = $checkAmount; 

                               $total = $total+$checkAmount;
                                if($monthDiff % 3 == 0 && $monthDiff != 0){
                                    $total = $total+$regularInterest;
                                    $cInterest = $regularInterest;
                                }else{
                                    $total = $total;
                                    $cInterest = 0;
                                }
                                $regularInterest = $regularInterest+(($cuurentInterest-$defaulterInterest)*$total/1200);
                                $addInterest = ($cuurentInterest-$defaulterInterest);
                                $a = -$aviAmount+$aviAmount*(pow((1+$addInterest/400), (4*$i/12)));
                                $interest = number_format((float)$a, 2, '.', '');

                            }else{
                                $aviAmount = 0;
                                $total = 0;
                                $cuurentInterest = 0;
                                $interest = 0; 
                                $addInterest = 0; 
                            }
                            $totalInterestDeposit = $totalInterestDeposit+$interest;
                        }

                        if(round($totalInterestDeposit) > 0){
                            MemberInvestmentInterest::create([
                                'member_id' => $val->member_id,
                                'investment_id' => $val->id,
                                'plan_type' => $val->plan_id,
                                'branch_id' => $val->branch_id,
                                'deposite_amount' => $totalDeposit,
                                'interest_amount' => $totalInterestDeposit,
                                'date' =>$nDate,
                                'time' =>$entryTime,
                                'created_at' =>date("Y-m-d ".$entryTime."", strtotime(convertDate($nDate))),
                            ]); 

                        }

                        Memberinvestments::where('id', $val->id)->update(['investment_interest_date'=>$nDate]);
                    }
                }
            }
        DB::commit();
        } catch (\Exception $ex) {
            DB::rollback(); 
            return back()->with('alert', $ex->getMessage());
        }
        
        \Log::info("Amount Deposite Successfully!");
    }
}
