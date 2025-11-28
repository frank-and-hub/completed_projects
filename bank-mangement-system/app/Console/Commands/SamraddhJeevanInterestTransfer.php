<?php

namespace App\Console\Commands;

use App\Http\Controllers\Admin\CommanController;
use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Models\Memberinvestments;
use App\Models\Daybook;
use App\Models\SavingAccount;
use App\Models\SavingAccountTranscation;
use App\Models\TransactionReferences;
use App\Models\InvestmentMonthlyYearlyInterestDeposits;
use DB;
use Session;

class SamraddhJeevanInterestTransfer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'samraddhjeevaninterest:transfer';/**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Transfer Samraddh Jeevan monthly interest';/**
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
        die('stop');
        $entryTime = date("h:i:s");
        $cDate = Carbon::now()->format('Y-m-d');
        $sjInvestment = Memberinvestments::where('plan_id',6)->where('is_mature',1)->get();

        DB::beginTransaction();
        try {
            foreach($sjInvestment as $key => $val){
                if (strpos($val->account_number, 'R-') === false) {
                    $maturity_date =  date('Y-m-d', strtotime($val->created_at. ' + '.($val->tenure).' year'))
                    ;
                    
                    if($cDate >= $maturity_date && $val->last_deposit_to_ssb_date == ''){
                        $cInterest = 0;
                        $regularInterest = 0; 
                        $total = 0;
                        $totalDeposit = 0;
                        $totalInterestDeposit = 0;

                        $investmentMonths = $val->tenure*12;
                        $totalInvestmentAmount = Daybook::select('investment_id','deposit','created_at')->where('investment_id',$val->id)->whereIn('transaction_type', [2,4])->sum('deposit');

                        
                        for ($i=1; $i <= $investmentMonths ; $i++){
                            $nDate =  date('Y-m-d', strtotime($val->created_at. ' + '.$i.' months')); 
                            $cMonth = date('m');
                            $cYear = date('Y');
                            $cMonth = date('m');
                            $cYear = date('Y');
                            $cuurentInterest = $val->interest_rate;
                            $totalDeposit = $totalInvestmentAmount;

                            $d1 = explode('-',$val->created_at);
                            $d2 = explode('-',$nDate);

                            $ts1 = strtotime($val->created_at);
                            $ts2 = strtotime($nDate);

                            $year1 = date('Y', $ts1);
                            $year2 = date('Y', $ts2);

                            $month1 = date('m', $ts1);
                            $month2 = date('m', $ts2);

                            $monthDiff = (($year2 - $year1) * 12) + ($month2 - $month1);

                            $cfAmount = Memberinvestments::where('id',$val->id)->first();

                            if($val->deposite_amount*$monthDiff <= $totalDeposit){
                                $extraAmount = (int) $totalDeposit-(int) ($val->deposite_amount*$monthDiff);
                                $cfdAmount = (int) $extraAmount+(int) $cfAmount->carry_forward_amount;
                                Memberinvestments::where('id', $val->id)->update(['carry_forward_amount'=>$cfdAmount]);

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

                                $pendingAmount =  ($val->deposite_amount*12)-$totalDeposit;
                                if((int) $cfAmount->carry_forward_amount >= (int) $pendingAmount){
             
                                    $cfdAmount = (int) $cfAmount->carry_forward_amount-(int) $pendingAmount;
                                    Memberinvestments::where('id', $val->id)->update(['carry_forward_amount'=>$cfdAmount]);
                                    $collection = (int) $collection+(int) $pendingAmount;

                                }elseif((int) $cfAmount->carry_forward_amount == (int) $pendingAmount){

                                    Memberinvestments::where('id', $val->id)->update(['carry_forward_amount'=>0]);
                                    $collection = (int) $collection+(int) $cfAmount->carry_forward_amount;

                                }elseif((int) $pendingAmount > (int) $cfAmount->carry_forward_amount){

                                    Memberinvestments::where('id', $val->id)->update(['carry_forward_amount'=>0]);
                                    $collection = (int) $collection+(int) $cfAmount->carry_forward_amount;
                                }
                                
                                $checkAmount = $collection+($totalDeposit-$val->deposite_amount*($monthDiff-1));
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
                        }

                        $finalAmount = round($totalDeposit+$totalInterestDeposit);
                        $monthlyInterest = $finalAmount*10/1200;

                        $m1 = strtotime($maturity_date);
                        $m2 = strtotime($cDate);
                        $y1 = date('Y', $m1);
                        $y2 = date('Y', $m2);
                        $n1 = date('m', $m1);
                        $n2 = date('m', $m2);
                        $mDiff = (($y2 - $y1) * 12) + ($n2 - $n1);
                        $totalCalculate = $monthlyInterest;


                    
                        for ($i=1; $i <= $mDiff; $i++) { 
          
                            $createDate =  date('Y-m-d', strtotime($maturity_date. ' + '.$i.' months'));

                            
                            Memberinvestments::where('id', $val->id)->update(['last_deposit_to_ssb_amount' => $totalCalculate,'last_deposit_to_ssb_date'=>$createDate]);
                            InvestmentMonthlyYearlyInterestDeposits::create([
                                'investment_id' => $val->id,
                                'plan_type_id' =>6,
                                'monthly_deposit_amount' =>$totalCalculate,
                                'date' =>$createDate,
                            ]);

                            /************* Head Implement************/
                            Session::put('created_at', date("Y-m-d ".$entryTime."", strtotime(convertDate($createDate))));
                            $chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
                            $vno = "";
                            for ($i = 0; $i < 10; $i++) {
                                $vno .= $chars[mt_rand(0, strlen($chars)-1)];
                            }        

                            $branch_id = $val->branch_id;
                            $type = 3;
                            $sub_type = 34;
                            $type_id = $val->id;
                            $type_transaction_id = $val->id;
                            $associate_id = NULL;
                            $member_id = $val->member_id;
                            $branch_id_to = NULL;
                            $branch_id_from = NULL;
                            $opening_balance = $totalCalculate;
                            $amount = $totalCalculate;
                            $closing_balance = $totalCalculate;

                            $description = ($val->account_number).' Interest amount '.number_format((float)$totalCalculate, 2, '.', '');
                            $description_dr = getMemberData($val->member_id)->first_name.' '.getMemberData($val->member_id)->last_name.' Dr '.number_format((float)$totalCalculate, 2, '.', '');
                            $description_cr = 'To Samraddh JEEVAN A/C Cr '.number_format((float)$totalCalculate, 2, '.', '');
                            

                            $payment_type = 'CR';
                            $payment_mode = 3;
                            $currency_code = 'INR';
                            $amount_to_id =$val->member_id;
                            $amount_to_name = getMemberData($val->member_id)->first_name.' '.getMemberData($val->member_id)->last_name;
                            $amount_from_id = NULL;
                            $amount_from_name = NULL;
                            $v_no = $vno;
                            $v_date = date("Y-m-d ".$entryTime."", strtotime(convertDate($createDate)));
                            $ssb_account_id_from = NULL;
                            $cheque_no = NULL;
                            $cheque_date = NULL;
                            $cheque_bank_from = NULL;
                            $cheque_bank_ac_from = NULL;
                            $cheque_bank_ifsc_from = NULL;
                            $cheque_bank_branch_from = NULL;
                            $cheque_bank_to = NULL;
                            $cheque_bank_ac_to = NULL;
                            $transction_no = NULL;
                            $transction_bank_from = NULL;
                            $transction_bank_ac_from = NULL;
                            $transction_bank_ifsc_from = NULL;
                            $transction_bank_branch_from = NULL;
                            $transction_bank_to = NULL;
                            $transction_bank_ac_to = NULL;
                            $transction_date = NULL;
                            $entry_date = NULL;
                            $entry_time = NULL;
                            $created_by = 1;
                            $created_by_id = 1;
                            $is_contra = NULL;
                            $contra_id = NULL;
                            $created_at = NULL;
                            $bank_id = NULL;
                            $bank_ac_id = NULL;
                            $transction_bank_to_name = NULL;
                            $transction_bank_to_ac_no = NULL;
                            $transction_bank_to_branch = NULL;
                            $transction_bank_to_ifsc = NULL;

                            $jv_unique_id = NULL;
                            $ssb_account_tran_id_from = NULL;
                            $cheque_type = NULL;
                            $cheque_id = NULL;
                            $cheque_bank_from_id = NULL;
                            $cheque_bank_ac_from_id = NULL;
                            $cheque_bank_to_name = NULL;
                            $cheque_bank_to_branch = NULL;
                            $cheque_bank_to_ac_no = NULL;
                            $cheque_bank_to_ifsc = NULL;
                            $transction_bank_from_id = NULL;
                            $transction_bank_from_ac_id = NULL;

                            $ssbAccountDetails = SavingAccount::with('ssbMember')->select('id','balance','branch_id','branch_code','member_id','account_no')->where('member_id',$val->member_id)->first();

                            $record1=SavingAccountTranscation::where('account_no',$ssbAccountDetails->account_no)->whereDate('created_at','<',date("Y-m-d", strtotime(convertDate($createDate))))->first();

                            $balance_update=$totalCalculate+$ssbAccountDetails->balance;
                            $ssbBalance = SavingAccount::find($ssbAccountDetails->id);
                            $ssbBalance->balance=$balance_update;
                            $ssbBalance->save();

                            $ssb['saving_account_id']=$ssbAccountDetails->id;
                            $ssb['account_no']=$ssbAccountDetails->account_no;
                            if($record1){
                                $ssb['opening_balance']=$totalCalculate+$record1->opening_balance;
                            }else{
                                $ssb['opening_balance']=$totalCalculate;
                            }
                            $ssb['deposit']=$totalCalculate;
                            $ssb['branch_id']=$val->branch_id;
                            $ssb['type']=10;
                        
                            $ssb['withdrawal']=0;
                            $ssb['description']=$description;
                            $ssb['currency_code']='INR';
                            $ssb['payment_type']='CR';
                            $ssb['payment_mode']=3;
                            $ssb['created_at']=$createDate;
                            $ssbAccountTran = SavingAccountTranscation::create($ssb);
                            $saTranctionId = $ssbAccountTran->id;

                            $ssb_account_id_to = $ssbAccountDetails->id;
                            $ssb_account_tran_id_to = $ssbAccountTran->id;

                            $record2=SavingAccountTranscation::where('account_no',$ssbAccountDetails->account_no)->whereDate('created_at','>',date("Y-m-d", strtotime(convertDate($createDate))))->get();   
                            foreach ($record2 as $key => $value) {
                                $nsResult = SavingAccountTranscation::find($value->id);
                                $nsResult['opening_balance']=$value->opening_balance+$totalCalculate; 
                                $nsResult['updated_at']=$createDate;
                                $sResult->update($nsResult);
                            }
                            
                            $paymentMode = 4;
                            $amount_deposit_by_name = $ssbAccountDetails['ssbMember']->first_name.' '.$ssbAccountDetails['ssbMember']->last_name;
                            

                            $data['saving_account_transaction_id']=$saTranctionId;
                            $data['investment_id']=$val->id;
                            $data['created_at']=date("Y-m-d", strtotime(convertDate($createDate)));
                            $satRef = TransactionReferences::create($data);
                            $satRefId = $satRef->id;

                            $amountArraySsb = array('1'=>$totalCalculate);
                            
                            $ssbCreateTran = CommanController::createTransaction($satRefId,1,$val->id,$ssbAccountDetails->member_id,$ssbAccountDetails->branch_id,$ssbAccountDetails->branch_code,$amountArraySsb,$paymentMode,$amount_deposit_by_name=NULL,$ssbAccountDetails->member_id,$ssbAccountDetails->account_no,$cheque_dd_no=NULL,$bank_name=NULL,$branch_name=NULL,date("Y-m-d", strtotime(convertDate($createDate))),$online_payment_id=NULL,$online_payment_by=NULL,$ssbAccountDetails->account_no,'CR');

                            $description = $description;
                            $createDayBook = CommanController::createDayBook($ssbCreateTran,$satRefId,1,$ssbAccountDetails->id,NULL,$ssbAccountDetails->member_id,$totalCalculate+$ssbAccountDetails->balance,$totalCalculate,$withdrawal=0,$description,$ssbAccountDetails->account_no,$ssbAccountDetails->branch_id,$ssbAccountDetails->branch_code,$amountArraySsb,$paymentMode,$amount_deposit_by_name=NULL,$ssbAccountDetails->member_id,$ssbAccountDetails->account_no,0,NULL,NULL,date("Y-m-d", strtotime(convertDate($createDate))),NULL,$online_payment_by=NULL,$ssbAccountDetails->account_no,'CR');
                            

                            $dayBookRef = CommanController::createBranchDayBookReference($totalCalculate);

                            $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,56,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$opening_balance,$amount,$closing_balance,$description,'CR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$jv_unique_id,$v_no,$v_date,$ssb_account_id_from,$ssb_account_id_to,$ssb_account_tran_id_to,$ssb_account_tran_id_from,$cheque_type,$cheque_id,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_from_id,$cheque_bank_ac_from_id,$cheque_bank_to,$cheque_bank_ac_to,$cheque_bank_to_name,$cheque_bank_to_branch,$cheque_bank_to_ac_no,$cheque_bank_to_ifsc,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_from_id,$transction_bank_from_ac_id,$transction_bank_to,$transction_bank_ac_to,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc,$transction_date,$created_by,$created_by_id);

                            /*$allTransaction = CommanController::createAllTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,1,8,20,56,NULL,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$opening_balance,$amount,$closing_balance,$description,'CR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$createDate);*/ 

                            $memberTransaction = CommanController::memberTransactionNew($dayBookRef,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id,$bank_id,$bank_ac_id,$amount,$description,$payment_type,$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$createDate,$ssb_account_tran_id_to,$ssb_account_tran_id_from,$jv_unique_id,$cheque_type,$cheque_id);


                            $head1 = 4;
                            $head2 = 14;
                            $head3 = 36;
                            $head4 = NULL;
                            $head5 = NULL;

                            $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,$head3,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$totalCalculate,$totalCalculate,$totalCalculate,$description,'DR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$jv_unique_id,$v_no,$v_date,$ssb_account_id_from,$ssb_account_id_to,$ssb_account_tran_id_to,$ssb_account_tran_id_from,$cheque_type,$cheque_id,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_from_id,$cheque_bank_ac_from_id,$cheque_bank_to,$cheque_bank_ac_to,$cheque_bank_to_name,$cheque_bank_to_branch,$cheque_bank_to_ac_no,$cheque_bank_to_ifsc,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_from_id,$transction_bank_from_ac_id,$transction_bank_to,$transction_bank_ac_to,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc,$transction_date,$created_by,$created_by_id);

                            /*$allTransaction = CommanController::createAllTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,$head1,$head2,$head3,$head4,$head5,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$amount,$totalCalculate,$totalCalculate,$totalCalculate,'CR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$createDate);*/
                            /************* Head Implement************/
                        }
                    }elseif($val->last_deposit_to_ssb_date != ''){
       
                        $m1 = strtotime($val->last_deposit_to_ssb_date);
                        $m2 = strtotime($cDate);
                        $y1 = date('Y', $m1);
                        $y2 = date('Y', $m2);
                        $n1 = date('m', $m1);
                        $n2 = date('m', $m2);
                        $mDiff = (($y2 - $y1) * 12) + ($n2 - $n1);
                        if($mDiff > 0){
                            $cInterest = 0;
                            $regularInterest = 0; 
                            $total = 0;
                            $totalDeposit = 0;
                            $totalInterestDeposit = 0;

                            $investmentMonths = $val->tenure*12;
                            $totalInvestmentAmount = Daybook::select('investment_id','deposit','created_at')->where('investment_id',$val->id)->whereIn('transaction_type', [2,4])->sum('deposit');
                            
                            for ($i=1; $i <= $investmentMonths ; $i++){
                                $nDate =  date('Y-m-d', strtotime($val->created_at. ' + '.$i.' months')); 
                                $cMonth = date('m');
                                $cYear = date('Y');
                                $cMonth = date('m');
                                $cYear = date('Y');
                                $cuurentInterest = $val->interest_rate;
                                $totalDeposit = $totalInvestmentAmount;

                                $d1 = explode('-',$val->created_at);
                                $d2 = explode('-',$nDate);

                                $ts1 = strtotime($val->created_at);
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
                            }

                            $finalAmount = round($totalDeposit+$totalInterestDeposit);
                            $monthlyInterest = $finalAmount*10/1200;

                            $totalCalculate = $monthlyInterest;

                            Memberinvestments::where('id', $val->id)->update(['last_deposit_to_ssb_amount' => $totalCalculate,'last_deposit_to_ssb_date'=>$cDate]);
                            InvestmentMonthlyYearlyInterestDeposits::create([
                                'investment_id' => $val->id,
                                'plan_type_id' =>6,
                                'monthly_deposit_amount' =>$totalCalculate,
                                'date' =>$cDate,
                            ]);

                            /************* Head Implement************/
                            Session::put('created_at', date("Y-m-d ".$entryTime."", strtotime(convertDate($cDate))));
                            $chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
                            $vno = "";
                            for ($i = 0; $i < 10; $i++) {
                                $vno .= $chars[mt_rand(0, strlen($chars)-1)];
                            }        

                            $branch_id = $val->branch_id;
                            $type = 3;
                            $sub_type = 34;
                            $type_id = $val->id;
                            $type_transaction_id = $val->id;
                            $associate_id = NULL;
                            $member_id = $val->member_id;
                            $branch_id_to = NULL;
                            $branch_id_from = NULL;
                            $opening_balance = $totalCalculate;
                            $amount = $totalCalculate;
                            $closing_balance = $totalCalculate;

                            $description = ($val->account_number).' Interest amount '.number_format((float)$totalCalculate, 2, '.', '');
                            $description_dr = getMemberData($val->member_id)->first_name.' '.getMemberData($val->member_id)->last_name.' Dr '.number_format((float)$totalCalculate, 2, '.', '');
                            $description_cr = 'To Samraddh JEEVAN A/C Cr '.number_format((float)$totalCalculate, 2, '.', '');
                            

                            $payment_type = 'CR';
                            $payment_mode = 3;
                            $currency_code = 'INR';
                            $amount_to_id =$val->member_id;
                            $amount_to_name = getMemberData($val->member_id)->first_name.' '.getMemberData($val->member_id)->last_name;
                            $amount_from_id = NULL;
                            $amount_from_name = NULL;
                            $v_no = $vno;
                            $v_date = date("Y-m-d ".$entryTime."", strtotime(convertDate($cDate)));
                            $ssb_account_id_from = NULL;
                            $cheque_no = NULL;
                            $cheque_date = NULL;
                            $cheque_bank_from = NULL;
                            $cheque_bank_ac_from = NULL;
                            $cheque_bank_ifsc_from = NULL;
                            $cheque_bank_branch_from = NULL;
                            $cheque_bank_to = NULL;
                            $cheque_bank_ac_to = NULL;
                            $transction_no = NULL;
                            $transction_bank_from = NULL;
                            $transction_bank_ac_from = NULL;
                            $transction_bank_ifsc_from = NULL;
                            $transction_bank_branch_from = NULL;
                            $transction_bank_to = NULL;
                            $transction_bank_ac_to = NULL;
                            $transction_date = NULL;
                            $entry_date = NULL;
                            $entry_time = NULL;
                            $created_by = 1;
                            $created_by_id = 1;
                            $is_contra = NULL;
                            $contra_id = NULL;
                            $created_at = NULL;
                            $bank_id = NULL;
                            $bank_ac_id = NULL;
                            $transction_bank_to_name = NULL;
                            $transction_bank_to_ac_no = NULL;
                            $transction_bank_to_branch = NULL;
                            $transction_bank_to_ifsc = NULL;

                            $jv_unique_id = NULL;
                            $ssb_account_tran_id_from = NULL;
                            $cheque_type = NULL;
                            $cheque_id = NULL;
                            $cheque_bank_from_id = NULL;
                            $cheque_bank_ac_from_id = NULL;
                            $cheque_bank_to_name = NULL;
                            $cheque_bank_to_branch = NULL;
                            $cheque_bank_to_ac_no = NULL;
                            $cheque_bank_to_ifsc = NULL;
                            $transction_bank_from_id = NULL;
                            $transction_bank_from_ac_id = NULL;

                            $ssbAccountDetails = SavingAccount::with('ssbMember')->select('id','balance','branch_id','branch_code','member_id','account_no')->where('member_id',$val->member_id)->first();

                            $record3=SavingAccountTranscation::where('account_no',$ssbAccountDetails->account_no)->whereDate('created_at','<',date("Y-m-d", strtotime(convertDate($cDate))))->first();

                            $balance_update=$totalCalculate+$ssbAccountDetails->balance;
                            $ssbBalance = SavingAccount::find($ssbAccountDetails->id);
                            $ssbBalance->balance=$balance_update;
                            $ssbBalance->save();

                            $ssb['saving_account_id']=$ssbAccountDetails->id;
                            $ssb['account_no']=$ssbAccountDetails->account_no;
                            if($record3){
                                $ssb['opening_balance']=$totalCalculate+$record3->opening_balance;
                            }else{
                                $ssb['opening_balance']=$totalCalculate;
                            }
                            $ssb['deposit']=$totalCalculate;
                            $ssb['branch_id']=$val->branch_id;
                            $ssb['type']=10;
                            $ssb['withdrawal']=0;
                            $ssb['description']=$description;
                            $ssb['currency_code']='INR';
                            $ssb['payment_type']='CR';
                            $ssb['payment_mode']=3;
                            $ssb['created_at']=$cDate;
                            $ssbAccountTran = SavingAccountTranscation::create($ssb);
                            $saTranctionId = $ssbAccountTran->id;

                            $ssb_account_id_to = $ssbAccountDetails->id;
                            $ssb_account_tran_id_to = $ssbAccountTran->id;

                            $record4=SavingAccountTranscation::where('account_no',$ssbAccountDetails->account_no)->whereDate('created_at','>',date("Y-m-d", strtotime(convertDate($cDate))))->get();   
                            foreach ($record4 as $key => $value) {
                                $nsResult = SavingAccountTranscation::find($value->id);
                                $nsResult['opening_balance']=$value->opening_balance+$totalCalculate; 
                                $nsResult['updated_at']=$cDate;
                                $sResult->update($nsResult);
                            }

                            $paymentMode = 4;
                            $amount_deposit_by_name = $ssbAccountDetails['ssbMember']->first_name.' '.$ssbAccountDetails['ssbMember']->last_name;
                            
                            $data['saving_account_transaction_id']=$saTranctionId;
                            $data['investment_id']=$val->id;
                            $data['created_at']=date("Y-m-d", strtotime(convertDate($cDate)));
                            $satRef = TransactionReferences::create($data);
                            $satRefId = $satRef->id;

                            $amountArraySsb = array('1'=>$totalCalculate);
                            
                            $ssbCreateTran = CommanController::createTransaction($satRefId,1,$val->id,$ssbAccountDetails->member_id,$ssbAccountDetails->branch_id,$ssbAccountDetails->branch_code,$amountArraySsb,$paymentMode,$amount_deposit_by_name=NULL,$ssbAccountDetails->member_id,$ssbAccountDetails->account_no,$cheque_dd_no=NULL,$bank_name=NULL,$branch_name=NULL,date("Y-m-d", strtotime(convertDate($cDate))),$online_payment_id=NULL,$online_payment_by=NULL,$ssbAccountDetails->account_no,'CR');

                            $description = $description;
                            $createDayBook = CommanController::createDayBook($ssbCreateTran,$satRefId,1,$ssbAccountDetails->id,NULL,$ssbAccountDetails->member_id,$totalCalculate+$ssbAccountDetails->balance,$totalCalculate,$withdrawal=0,$description,$ssbAccountDetails->account_no,$ssbAccountDetails->branch_id,$ssbAccountDetails->branch_code,$amountArraySsb,$paymentMode,$amount_deposit_by_name=NULL,$ssbAccountDetails->member_id,$ssbAccountDetails->account_no,0,NULL,NULL,date("Y-m-d", strtotime(convertDate($cDate))),NULL,$online_payment_by=NULL,$ssbAccountDetails->account_no,'CR');
                            

                            $dayBookRef = CommanController::createBranchDayBookReference($totalCalculate);

                            $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,56,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$opening_balance,$amount,$closing_balance,$description,'CR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$jv_unique_id,$v_no,$v_date,$ssb_account_id_from,$ssb_account_id_to,$ssb_account_tran_id_to,$ssb_account_tran_id_from,$cheque_type,$cheque_id,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_from_id,$cheque_bank_ac_from_id,$cheque_bank_to,$cheque_bank_ac_to,$cheque_bank_to_name,$cheque_bank_to_branch,$cheque_bank_to_ac_no,$cheque_bank_to_ifsc,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_from_id,$transction_bank_from_ac_id,$transction_bank_to,$transction_bank_ac_to,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc,$transction_date,$created_by,$created_by_id);

                            /*$allTransaction = CommanController::createAllTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,1,8,20,56,NULL,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$opening_balance,$amount,$closing_balance,$description,'CR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$cDate);*/ 

                            $memberTransaction = CommanController::memberTransactionNew($dayBookRef,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id,$bank_id,$bank_ac_id,$amount,$description,$payment_type,$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$cDate,$ssb_account_tran_id_to,$ssb_account_tran_id_from,$jv_unique_id,$cheque_type,$cheque_id);


                            $head1 = 4;
                            $head2 = 14;
                            $head3 = 36;
                            $head4 = NULL;
                            $head5 = NULL;

                            $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,$head3,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$totalCalculate,$totalCalculate,$totalCalculate,$description,'DR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$jv_unique_id,$v_no,$v_date,$ssb_account_id_from,$ssb_account_id_to,$ssb_account_tran_id_to,$ssb_account_tran_id_from,$cheque_type,$cheque_id,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_from_id,$cheque_bank_ac_from_id,$cheque_bank_to,$cheque_bank_ac_to,$cheque_bank_to_name,$cheque_bank_to_branch,$cheque_bank_to_ac_no,$cheque_bank_to_ifsc,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_from_id,$transction_bank_from_ac_id,$transction_bank_to,$transction_bank_ac_to,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc,$transction_date,$created_by,$created_by_id);

                            /*$allTransaction = CommanController::createAllTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,$head1,$head2,$head3,$head4,$head5,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$amount,$totalCalculate,$totalCalculate,$totalCalculate,'CR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$cDate);*/
                            /************* Head Implement************/    
                        }
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
