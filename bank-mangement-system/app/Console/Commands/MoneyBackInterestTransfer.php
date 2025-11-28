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

class MoneyBackInterestTransfer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'moneybackinteresttransfer:transfer';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Money Back Scheme Interest Transfer';
    /**
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
        $entryTime = date("h:i:s");

        $cDate = Carbon::now()->format('Y-m-d');
       //  comment for command run manullay 
		//$sjInvestment = Memberinvestments::where('plan_id',3)->where('is_mature',1)->where('account_number','not like','R-%')->get();
        // $sjInvestment =  $sjInvestment->get();
       //$sjInvestment =  DB::table('member_investments')->where('plan_id',3)->where('is_mature',1)->get();
        /**** uncomment for command run manullay****/

        $sjInvestment = Memberinvestments::whereIn('id',['4102'])->where('plan_id',3)->where('is_mature',1)->get();

        DB::beginTransaction();
        try {


            foreach($sjInvestment as $key => $val){
                //if (strpos($val->account_number, 'R-') === false) {

                    $maturity_date =  date('Y-m-d', strtotime($val->created_at. ' + '.($val->tenure).' year'))
                    ;
                    $tcountRefund = InvestmentMonthlyYearlyInterestDeposits::where('investment_id',$val->id)->where('plan_type_id',3)->count();
                            

                    if($tcountRefund < 7){

                        if($val->last_deposit_to_ssb_date == ''){
                            $diff = abs(strtotime($cDate) - strtotime($val->created_at));
                            $years = floor($diff / (365*60*60*24));

                            if($years > 0){
                                for ($j=1; $j <= $years; $j++) {
                                    $countRefund = InvestmentMonthlyYearlyInterestDeposits::where('investment_id',$val->id)->where('plan_type_id',3)->count();
                                    $createDate =  date('Y-m-d', strtotime($val->created_at. ' + '.$j.' years'));
                                    $depositInterest = InvestmentMonthlyYearlyInterestDeposits::where('investment_id',$val->id)->where('plan_type_id',3)->orderby('date', 'desc')->first();
                                    $depositDate = Memberinvestments::where('plan_id',3)->where('id',$val->id)->first('last_deposit_to_ssb_date');

                                    if($depositInterest){
                                        $collection = Daybook::where('investment_id',$val->id)->whereIn('transaction_type', [2,4])->whereBetween('created_at', [date("Y-m-d", strtotime(convertDate($depositDate->last_deposit_to_ssb_date))), date("Y-m-d", strtotime(convertDate($createDate)))])->sum('deposit');

                                        if((int) $collection > (int) ($val->deposite_amount*12)){
                                            $extraAmount = (int) $collection-(int) ($val->deposite_amount*12);
                                            $cfdAmount = (int) $extraAmount+(int) $val->carry_forward_amount;
                                            Memberinvestments::where('id', $val->id)->update(['carry_forward_amount'=>$cfdAmount]);
                                            $collection = $val->deposite_amount*12;
                                        }elseif(($val->deposite_amount*12) > $collection){
                                             $pendingAmount =  ($val->deposite_amount*12)-$collection;

                                             if((int) $val->carry_forward_amount >= (int) $pendingAmount){

                                                $cfdAmount = (int) $val->carry_forward_amount-(int) $pendingAmount;
                                                Memberinvestments::where('id', $val->id)->update(['carry_forward_amount'=>$cfdAmount]);
                                                $collection = (int) $collection+(int) $pendingAmount;

                                             }elseif((int) $val->carry_forward_amount == (int) $pendingAmount){

                                                Memberinvestments::where('id', $val->id)->update(['carry_forward_amount'=>0]);
                                                $collection = (int) $collection+(int) $val->carry_forward_amount;

                                             }elseif((int) $pendingAmount > (int) $val->carry_forward_amount){

                                                Memberinvestments::where('id', $val->id)->update(['carry_forward_amount'=>0]);
                                                $collection = (int) $collection+(int) $val->carry_forward_amount;
                                             }
                                        }

                                        $conditionalAmount = 9*$val->deposite_amount;

                                        if($collection < $conditionalAmount){
                                            $cInterest = 0;
                                            $regularInterest = 0;
                                            $total = 0;
                                            $totalDeposit = 0;
                                            $totalInterestDeposit = 0;

                                            $investmentMonths = $years*12;



                                            //$totalInvestmentAmount = Daybook::select('investment_id','deposit','created_at')->where('investment_id',$val->id)->whereIn('transaction_type', [2,4])->whereBetween('created_at', [date("Y-m-d", strtotime(convertDate($depositDate->last_deposit_to_ssb_date))), date("Y-m-d", strtotime(convertDate($createDate)))])->sum('deposit');

                                            $totalInvestmentAmount = $collection;

                                            for ($i=1; $i <= $investmentMonths ; $i++){

                                                $mInvestment = $val;
                                                $nDate =  date('Y-m-d', strtotime($val->created_at. ' + '.$i.' months'));
                                                $cMonth = date('m');
                                                $cYear = date('Y');
                                                $cuurentInterest = $mInvestment->interest_rate;
                                                $totalDeposit = $totalInvestmentAmount;
                                                $ts1 = strtotime($mInvestment->created_at);
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
                                            }

                                            $finalAmount = round($totalDeposit+$totalInterestDeposit);

                                            if($depositInterest){
                                                $availableAmountFd = ($depositInterest->available_amount*9/100)+$depositInterest->available_amount;
                                            }else{
                                                $availableAmountFd = 0;
                                            }


                                            if($countRefund < 6){
                                               Memberinvestments::where('id', $val->id)->update(['last_deposit_to_ssb_date'=>$createDate]);

                                                InvestmentMonthlyYearlyInterestDeposits::create([
                                                    'investment_id' => $val->id,
                                                    'plan_type_id' =>3,
                                                    'fd_amount' => $finalAmount,
                                                    'available_amount_fd' => $availableAmountFd,
                                                    'available_amount' => ($finalAmount+$availableAmountFd),
                                                    'date' =>$createDate,
                                                ]);
                                            }else{
                                                InvestmentMonthlyYearlyInterestDeposits::create([
                                                    'investment_id' => $val->id,
                                                    'plan_type_id' =>3,
                                                    'fd_amount' => $finalAmount,
                                                    'available_amount_fd' => $availableAmountFd,
                                                    'available_amount' => ($finalAmount+$availableAmountFd),
                                                    'date' =>$createDate,
                                                ]);
                                            }

                                        }elseif($collection >= $conditionalAmount){
                                            Session::put('created_at', date("Y-m-d ".$entryTime."", strtotime(convertDate($createDate))));

                                            $ssbAccountDetails = SavingAccount::with('ssbMember')->select('id','balance','branch_id','branch_code','member_id','account_no')->where('member_id',$val->member_id)->first();

                                            $yearlyAmount = 12*$val->deposite_amount;
                                            if($collection > $yearlyAmount){
                                                $cInterest = 0;
                                                $regularInterest = 0;
                                                $total = 0;
                                                $totalDeposit = 0;
                                                $totalInterestDeposit = 0;

                                                $investmentMonths = $years*12;


                                                //$totalInvestmentAmount = Daybook::select('investment_id','deposit','created_at')->where('investment_id',$val->id)->whereIn('transaction_type', [2,4])->whereBetween('created_at', [date("Y-m-d", strtotime(convertDate($depositDate->last_deposit_to_ssb_date))), date("Y-m-d", strtotime(convertDate($createDate)))])->sum('deposit');

                                                $totalInvestmentAmount = $collection;

                                                for ($i=1; $i <= $investmentMonths ; $i++){

                                                    $mInvestment = $val;
                                                    $nDate =  date('Y-m-d', strtotime($val->created_at. ' + '.$i.' months'));
                                                    $cMonth = date('m');
                                                    $cYear = date('Y');
                                                    $cuurentInterest = $mInvestment->interest_rate;
                                                    $totalDeposit = $totalInvestmentAmount;

                                                    $ts1 = strtotime($mInvestment->created_at);
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
                                                }

                                                $finalAmount = round($totalDeposit+$totalInterestDeposit);

                                                if($depositInterest){
                                                    $availableAmountFd = ($depositInterest->available_amount*9/100)+$depositInterest->available_amount;
                                                }else{
                                                    $availableAmountFd = 0;
                                                }

                                                if($countRefund < 6){
                                                   $cBalance = ($val->current_balance-($totalDeposit*60/100));
                                                   Memberinvestments::where('id', $val->id)->update([
                                                        'current_balance' => $cBalance,
                                                       'last_deposit_to_ssb_amount' => ($totalDeposit*60/100),'last_deposit_to_ssb_date'=>$createDate]);

                                                    $trdata['saving_account_transaction_id']=NULL;
                                                    $trdata['investment_id']=$val->id;
                                                    $trdata['created_at']=date("Y-m-d", strtotime(convertDate($createDate)));
                                                    $satRef = TransactionReferences::create($trdata);
                                                    $satRefId = $satRef->id;

                                                    $amountArraySsb=array('1'=>($totalDeposit*60/100));

                                                    $createTransaction = CommanController::createTransaction($satRefId,17,$val->id,$val->member_id,$val->branch_id,getBranchCode($val->branch_id)->branch_code,$amountArraySsb,4,getBranchName($val->branch_id),$val->member_id,$val->account_number,NULL,NULL,getBranchName($val->branch_id),$createDate,NULL,NULL,NULL,'DR');


                                                    $loanTypeArray = array(3,5,6,8,9,10,11,12);
                                                    $investmentTypeArray = array(3,5,6,8,9,10,11,12);
                                                    $data_log['transaction_type']=18;
                                                    $data_log['transaction_id']=$createTransaction;
                                                    $data_log['saving_account_transaction_reference_id']=$satRefId;
                                                    $data_log['investment_id']=$val->id;
                                                    $data_log['account_no']=$val->account_number;
                                                    $data_log['member_id']=$val->member_id;
                                                    $data_log['opening_balance']=$cBalance;
                                                    $data_log['withdrawal']=($totalDeposit*60/100);
                                                    $data_log['description']='Money Back amount transfer '.$ssbAccountDetails->account_no;
                                                    $data_log['branch_id']=$val->branch_id;
                                                    $data_log['branch_code']=getBranchCode($val->branch_id)->branch_code;
                                                    $data_log['amount']=($totalDeposit*60/100);
                                                    $data_log['currency_code']='INR';
                                                    $data_log['payment_mode']=4;
                                                    $data_log['payment_type']='DR';
                                                    $data_log['payment_date']=date("Y-m-d ".$entryTime."", strtotime(convertDate($createDate)));
                                                    $data_log['amount_deposit_by_name']=getBranchName($val->branch_id);
                                                    $data_log['amount_deposit_by_id']=$val->branch_id;
                                                    $data_log['created_by_id']=1;
                                                    $data_log['created_by']=2;
                                                    $data_log['created_at']=date("Y-m-d ".$entryTime."", strtotime(convertDate($createDate)));
                                                    $transcation = Daybook::create($data_log);

                                                    /*$nextDaybookRecord = Daybook::where('investment_id',$val->id)->whereDate('created_at','>',$createDate)->get();

                                                    foreach ($nextDaybookRecord as $key => $value) {
                                                        $balance_update = $value->opening_balance-($totalDeposit*60/100);
                                                        $daybookBalance = Daybook::find($value->id);
                                                        $daybookBalance->opening_balance=$balance_update;
                                                        $daybookBalance->save();
                                                    }*/

                                                    InvestmentMonthlyYearlyInterestDeposits::create([
                                                        'investment_id' => $val->id,
                                                        'plan_type_id' =>3,
                                                        'fd_amount' => $finalAmount,
                                                        'available_amount_fd' => $availableAmountFd,
                                                        'yearly_deposit_amount' => ($totalDeposit*60/100),
                                                        'available_amount' => ($finalAmount+$availableAmountFd)-($totalDeposit*60/100),
                                                        'date' =>$createDate,
                                                    ]);
                                                }else{
                                                    InvestmentMonthlyYearlyInterestDeposits::create([
                                                        'investment_id' => $val->id,
                                                        'plan_type_id' =>3,
                                                        'fd_amount' => $finalAmount,
                                                        'available_amount_fd' => $availableAmountFd,
                                                        'available_amount' => ($finalAmount+$availableAmountFd),
                                                        'date' =>$createDate,
                                                    ]);
                                                }

                                                /************* Head Implement************/
                                                $chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
                                                $vno = "";
                                                for ($k = 0; $k < 10; $k++) {
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
                                                $opening_balance = $totalDeposit*60/100;
                                                $amount = $totalDeposit*60/100;
                                                $closing_balance = $totalDeposit*60/100;

                                                $description = ($val->account_number).' Money Back amount '.number_format((float)$amount, 2, '.', '');
                                                $description_dr = getMemberData($val->member_id)->first_name.' '.getMemberData($val->member_id)->last_name.' Dr '.number_format((float)$amount, 2, '.', '');
                                                $description_cr = 'To Monthly Income scheme A/C Cr '.number_format((float)$amount, 2, '.', '');

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

                                                $record1=SavingAccountTranscation::where('account_no',$ssbAccountDetails->account_no)->whereDate('created_at','<',date("Y-m-d", strtotime(convertDate($createDate))))->first();

                                                $balance_update=($totalDeposit*60/100)+$ssbAccountDetails->balance;
                                                $ssbBalance = SavingAccount::find($ssbAccountDetails->id);
                                                $ssbBalance->balance=$balance_update;
                                                $ssbBalance->save();

                                                $ssb['saving_account_id']=$ssbAccountDetails->id;
                                                $ssb['account_no']=$ssbAccountDetails->account_no;
                                                if($record1->opening_balance){
                                                    $ssb['opening_balance']=($totalDeposit*60/100)+$record1->opening_balance;
                                                }else{
                                                    $ssb['opening_balance']=($totalDeposit*60/100);
                                                }
                                                $ssb['deposit']=($totalDeposit*60/100);
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
                                                    $sResult = SavingAccountTranscation::find($value->id);
                                                    $nsResult['opening_balance']=$value->opening_balance+($totalDeposit*60/100);
                                                    $nsResult['updated_at']=$createDate;
                                                    $sResult->update($nsResult);
                                                }

                                                $trdata['saving_account_transaction_id']=$saTranctionId;
                                                $trdata['investment_id']=$val->id;
                                                $trdata['created_at']=date("Y-m-d", strtotime(convertDate($createDate)));
                                                $satRef = TransactionReferences::create($trdata);
                                                $satRefId = $satRef->id;

                                                $paymentMode = 4;
                                                $amount_deposit_by_name = $ssbAccountDetails['ssbMember']->first_name.' '.$ssbAccountDetails['ssbMember']->last_name;

                                                $amountArraySsb = array('1'=>($totalDeposit*60/100));

                                                $ssbCreateTran = CommanController::createTransaction($satRefId,1,$val->id,$ssbAccountDetails->member_id,$ssbAccountDetails->branch_id,$ssbAccountDetails->branch_code,$amountArraySsb,$paymentMode,$amount_deposit_by_name=NULL,$ssbAccountDetails->member_id,$ssbAccountDetails->account_no,$cheque_dd_no=NULL,$bank_name=NULL,$branch_name=NULL,date("Y-m-d", strtotime(convertDate($createDate))),$online_payment_id=NULL,$online_payment_by=NULL,$ssbAccountDetails->account_no,'CR');

                                                $description = $description;
                                                $createDayBook = CommanController::createDayBook($ssbCreateTran,$satRefId,1,$ssbAccountDetails->id,NULL,$ssbAccountDetails->member_id,$amount+$ssbAccountDetails->balance,$amount,$withdrawal=0,$description,$ssbAccountDetails->account_no,$ssbAccountDetails->branch_id,$ssbAccountDetails->branch_code,$amountArraySsb,$paymentMode,$amount_deposit_by_name=NULL,$ssbAccountDetails->member_id,$ssbAccountDetails->account_no,0,NULL,NULL,date("Y-m-d", strtotime(convertDate($createDate))),NULL,$online_payment_by=NULL,$ssbAccountDetails->account_no,'CR');


                                                $dayBookRef = CommanController::createBranchDayBookReference($amount);

                                                $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,56,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$opening_balance,$amount,$closing_balance,$description,'CR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$jv_unique_id,$v_no,$v_date,$ssb_account_id_from,$ssb_account_id_to,$ssb_account_tran_id_to,$ssb_account_tran_id_from,$cheque_type,$cheque_id,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_from_id,$cheque_bank_ac_from_id,$cheque_bank_to,$cheque_bank_ac_to,$cheque_bank_to_name,$cheque_bank_to_branch,$cheque_bank_to_ac_no,$cheque_bank_to_ifsc,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_from_id,$transction_bank_from_ac_id,$transction_bank_to,$transction_bank_ac_to,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc,$transction_date,$created_by,$created_by_id);

                                                /*$allTransaction = CommanController::createAllTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,1,8,20,56,NULL,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$opening_balance,$amount,$closing_balance,$description,'CR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$createDate);*/

                                                $memberTransaction = CommanController::memberTransactionNew($dayBookRef,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id,$bank_id,$bank_ac_id,$amount,$description,$payment_type,$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$createDate,$ssb_account_tran_id_to,$ssb_account_tran_id_from,$jv_unique_id,$cheque_type,$cheque_id);


                                                $head1 = 1;
                                                $head2 = 8;
                                                $head3 = 20;
                                                $head4 = 59;
                                                $head5 = 85;

                                                $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,$head5,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$amount,$amount,$amount,$description,'DR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$jv_unique_id,$v_no,$v_date,$ssb_account_id_from,$ssb_account_id_to,$ssb_account_tran_id_to,$ssb_account_tran_id_from,$cheque_type,$cheque_id,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_from_id,$cheque_bank_ac_from_id,$cheque_bank_to,$cheque_bank_ac_to,$cheque_bank_to_name,$cheque_bank_to_branch,$cheque_bank_to_ac_no,$cheque_bank_to_ifsc,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_from_id,$transction_bank_from_ac_id,$transction_bank_to,$transction_bank_ac_to,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc,$transction_date,$created_by,$created_by_id);

                                                /*$allTransaction = CommanController::createAllTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,$head1,$head2,$head3,$head4,$head5,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$amount,$amount,$amount,$description,'DR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$createDate);*/
                                                /************* Head Implement************/
                                            }else{
                                                $cInterest = 0;
                                                $regularInterest = 0;
                                                $total = 0;
                                                $totalDeposit = 0;
                                                $totalInterestDeposit = 0;

                                                $investmentMonths = $years*12;

                                                //$totalInvestmentAmount = Daybook::select('investment_id','deposit','created_at')->where('investment_id',$val->id)->whereIn('transaction_type', [2,4])->whereBetween('created_at', [date("Y-m-d", strtotime(convertDate($depositDate->last_deposit_to_ssb_date))), date("Y-m-d", strtotime(convertDate($createDate)))])->sum('deposit');

                                                $totalInvestmentAmount = $collection;

                                                for ($i=1; $i <= $investmentMonths ; $i++){

                                                    $mInvestment = $val;
                                                    $nDate =  date('Y-m-d', strtotime($val->created_at. ' + '.$i.' months'));
                                                    $cMonth = date('m');
                                                    $cYear = date('Y');
                                                    $cuurentInterest = $mInvestment->interest_rate;
                                                    $totalDeposit = $totalInvestmentAmount;

                                                    $ts1 = strtotime($mInvestment->created_at);
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
                                                }

                                                $finalAmount = round($totalDeposit+$totalInterestDeposit);

                                                if($depositInterest){
                                                    $availableAmountFd = ($depositInterest->available_amount*9/100)+$depositInterest->available_amount;
                                                }else{
                                                    $availableAmountFd = 0;
                                                }

                                                if($countRefund < 6){
                                                    Session::put('created_at', date("Y-m-d ".$entryTime."", strtotime(convertDate($createDate))));
                                                    $ssbAccountDetails = SavingAccount::with('ssbMember')->select('id','balance','branch_id','branch_code','member_id','account_no')->where('member_id',$val->member_id)->first();

                                                    $cBalance = ($val->current_balance-($totalDeposit*60/100));
                                                    Memberinvestments::where('id', $val->id)->update([
                                                        'current_balance' => $cBalance,
                                                       'last_deposit_to_ssb_amount' => ($totalDeposit*60/100),'last_deposit_to_ssb_date'=>$createDate]);

                                                    $trdata['saving_account_transaction_id']=NULL;
                                                    $trdata['investment_id']=$val->id;
                                                    $trdata['created_at']=date("Y-m-d", strtotime(convertDate($createDate)));
                                                    $satRef = TransactionReferences::create($trdata);
                                                    $satRefId = $satRef->id;

                                                    $amountArraySsb=array('1'=>($totalDeposit*60/100));

                                                    $createTransaction = CommanController::createTransaction($satRefId,17,$val->id,$val->member_id,$val->branch_id,getBranchCode($val->branch_id)->branch_code,$amountArraySsb,4,getBranchName($val->branch_id),$val->member_id,$val->account_number,NULL,NULL,getBranchName($val->branch_id),$createDate,NULL,NULL,NULL,'DR');

                                                    $loanTypeArray = array(3,5,6,8,9,10,11,12);
                                                    $investmentTypeArray = array(3,5,6,8,9,10,11,12);
                                                    $data_log['transaction_type']=18;
                                                    $data_log['transaction_id']=$createTransaction;
                                                    $data_log['saving_account_transaction_reference_id']=$satRefId;
                                                    $data_log['investment_id']=$val->id;
                                                    $data_log['account_no']=$val->account_number;
                                                    $data_log['member_id']=$val->member_id;
                                                    $data_log['opening_balance']=$cBalance;
                                                    $data_log['withdrawal']=($totalDeposit*60/100);
                                                    $data_log['description']='Money Back amount transfer '.$ssbAccountDetails->account_no;
                                                    $data_log['branch_id']=$val->branch_id;
                                                    $data_log['branch_code']=getBranchCode($val->branch_id)->branch_code;
                                                    $data_log['amount']=($totalDeposit*60/100);
                                                    $data_log['currency_code']='INR';
                                                    $data_log['payment_mode']=4;
                                                    $data_log['payment_type']='DR';
                                                    $data_log['payment_date']=date("Y-m-d ".$entryTime."", strtotime(convertDate($createDate)));
                                                    $data_log['amount_deposit_by_name']=getBranchName($val->branch_id);
                                                    $data_log['amount_deposit_by_id']=$val->branch_id;
                                                    $data_log['created_by_id']=1;
                                                    $data_log['created_by']=2;
                                                    $data_log['created_at']=date("Y-m-d ".$entryTime."", strtotime(convertDate($createDate)));
                                                    $transcation = Daybook::create($data_log);

                                                    /*$nextDaybookRecord = Daybook::where('investment_id',$val->id)->whereDate('created_at','>',$createDate)->get();

                                                    foreach ($nextDaybookRecord as $key => $value) {
                                                        $balance_update = $value->opening_balance-($totalDeposit*60/100);
                                                        $daybookBalance = Daybook::find($value->id);
                                                        $daybookBalance->opening_balance=$balance_update;
                                                        $daybookBalance->save();
                                                    }*/

                                                    InvestmentMonthlyYearlyInterestDeposits::create([
                                                        'investment_id' => $val->id,
                                                        'plan_type_id' =>3,
                                                        'fd_amount' => $finalAmount,
                                                        'available_amount_fd' => $availableAmountFd,
                                                        'yearly_deposit_amount' => ($totalDeposit*60/100),
                                                        'available_amount' => ($finalAmount+$availableAmountFd)-($totalDeposit*60/100),
                                                        'date' =>$createDate,
                                                    ]);
                                                }else{
                                                    InvestmentMonthlyYearlyInterestDeposits::create([
                                                        'investment_id' => $val->id,
                                                        'plan_type_id' =>3,
                                                        'fd_amount' => $finalAmount,
                                                        'available_amount_fd' => $availableAmountFd,
                                                        'available_amount' => ($finalAmount+$availableAmountFd),
                                                        'date' =>$createDate,
                                                    ]);
                                                }

                                                /************* Head Implement************/

                                                $chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
                                                $vno = "";
                                                for ($k = 0; $k < 10; $k++) {
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
                                                $opening_balance = $totalDeposit*60/100;
                                                $amount = $totalDeposit*60/100;
                                                $closing_balance = $totalDeposit*60/100;

                                                $description = ($val->account_number).' Money Back amount '.number_format((float)$amount, 2, '.', '');
                                                $description_dr = getMemberData($val->member_id)->first_name.' '.getMemberData($val->member_id)->last_name.' Dr '.number_format((float)$amount, 2, '.', '');
                                                $description_cr = 'To Monthly Income scheme A/C Cr '.number_format((float)$amount, 2, '.', '');

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

                                                $record3=SavingAccountTranscation::where('account_no',$ssbAccountDetails->account_no)->whereDate('created_at','<',date("Y-m-d", strtotime(convertDate($createDate))))->first();

                                                $balance_update=($totalDeposit*60/100)+$ssbAccountDetails->balance;
                                                $ssbBalance = SavingAccount::find($ssbAccountDetails->id);
                                                $ssbBalance->balance=$balance_update;
                                                $ssbBalance->save();

                                                $ssb['saving_account_id']=$ssbAccountDetails->id;
                                                $ssb['account_no']=$ssbAccountDetails->account_no;
                                                if($record3){
                                                    $ssb['opening_balance']=($totalDeposit*60/100)+$record3->opening_balance;
                                                }else{
                                                    $ssb['opening_balance']=($totalDeposit*60/100);
                                                }
                                                $ssb['deposit']=($totalDeposit*60/100);
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

                                                $record4=SavingAccountTranscation::where('account_no',$ssbAccountDetails->account_no)->whereDate('created_at','>',date("Y-m-d", strtotime(convertDate($createDate))))->get();

                                                foreach ($record4 as $key => $value) {
                                                    $sResult = SavingAccountTranscation::find($value->id);
                                                    $nsResult['opening_balance']=$value->opening_balance+($totalDeposit*60/100);
                                                    $nsResult['updated_at']=$createDate;
                                                    $sResult->update($nsResult);
                                                }

                                                $paymentMode = 4;
                                                $amount_deposit_by_name = $ssbAccountDetails['ssbMember']->first_name.' '.$ssbAccountDetails['ssbMember']->last_name;

                                                $trdata['saving_account_transaction_id']=$saTranctionId;
                                                $trdata['investment_id']=$val->id;
                                                $trdata['created_at']=date("Y-m-d", strtotime(convertDate($createDate)));
                                                $satRef = TransactionReferences::create($trdata);
                                                $satRefId = $satRef->id;

                                                $amountArraySsb = array('1'=>($totalDeposit*60/100));

                                                $ssbCreateTran = CommanController::createTransaction($satRefId,1,$val->id,$ssbAccountDetails->member_id,$ssbAccountDetails->branch_id,$ssbAccountDetails->branch_code,$amountArraySsb,$paymentMode,$amount_deposit_by_name=NULL,$ssbAccountDetails->member_id,$ssbAccountDetails->account_no,$cheque_dd_no=NULL,$bank_name=NULL,$branch_name=NULL,date("Y-m-d", strtotime(convertDate($createDate))),$online_payment_id=NULL,$online_payment_by=NULL,$ssbAccountDetails->account_no,'CR');

                                                $description = $description;
                                                $createDayBook = CommanController::createDayBook($ssbCreateTran,$satRefId,1,$ssbAccountDetails->id,NULL,$ssbAccountDetails->member_id,$amount+$ssbAccountDetails->balance,$amount,$withdrawal=0,$description,$ssbAccountDetails->account_no,$ssbAccountDetails->branch_id,$ssbAccountDetails->branch_code,$amountArraySsb,$paymentMode,$amount_deposit_by_name=NULL,$ssbAccountDetails->member_id,$ssbAccountDetails->account_no,0,NULL,NULL,date("Y-m-d", strtotime(convertDate($createDate))),NULL,$online_payment_by=NULL,$ssbAccountDetails->account_no,'CR');


                                                $dayBookRef = CommanController::createBranchDayBookReference($amount);

                                                $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,56,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$opening_balance,$amount,$closing_balance,$description,'CR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$jv_unique_id,$v_no,$v_date,$ssb_account_id_from,$ssb_account_id_to,$ssb_account_tran_id_to,$ssb_account_tran_id_from,$cheque_type,$cheque_id,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_from_id,$cheque_bank_ac_from_id,$cheque_bank_to,$cheque_bank_ac_to,$cheque_bank_to_name,$cheque_bank_to_branch,$cheque_bank_to_ac_no,$cheque_bank_to_ifsc,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_from_id,$transction_bank_from_ac_id,$transction_bank_to,$transction_bank_ac_to,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc,$transction_date,$created_by,$created_by_id);

                                                /*$allTransaction = CommanController::createAllTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,1,8,20,56,NULL,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$opening_balance,$amount,$closing_balance,$description,'CR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$createDate);*/

                                                $memberTransaction = CommanController::memberTransactionNew($dayBookRef,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id,$bank_id,$bank_ac_id,$amount,$description,$payment_type,$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$createDate,$ssb_account_tran_id_to,$ssb_account_tran_id_from,$jv_unique_id,$cheque_type,$cheque_id);


                                                $head1 = 1;
                                                $head2 = 8;
                                                $head3 = 20;
                                                $head4 = 59;
                                                $head5 = 85;

                                                $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,$head5,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$amount,$amount,$amount,$description,'DR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$jv_unique_id,$v_no,$v_date,$ssb_account_id_from,$ssb_account_id_to,$ssb_account_tran_id_to,$ssb_account_tran_id_from,$cheque_type,$cheque_id,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_from_id,$cheque_bank_ac_from_id,$cheque_bank_to,$cheque_bank_ac_to,$cheque_bank_to_name,$cheque_bank_to_branch,$cheque_bank_to_ac_no,$cheque_bank_to_ifsc,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_from_id,$transction_bank_from_ac_id,$transction_bank_to,$transction_bank_ac_to,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc,$transction_date,$created_by,$created_by_id);

                                                /*$allTransaction = CommanController::createAllTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,$head1,$head2,$head3,$head4,$head5,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$amount,$amount,$amount,$description,'DR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$createDate);*/
                                                /************* Head Implement************/
                                            }
                                        }
                                    }else{

                                        $collection = Daybook::select('deposit')->where('investment_id',$val->id)->whereIn('transaction_type', [2,4])->whereBetween('created_at', [date("Y-m-d", strtotime(convertDate($val->created_at))), date("Y-m-d", strtotime(convertDate($createDate)))])->sum('deposit');

                                        if((int) $collection > (int) ($val->deposite_amount*12)){
                                            $extraAmount = (int) $collection-(int) ($val->deposite_amount*12);
                                            $cfdAmount = (int) $extraAmount+(int) $val->carry_forward_amount;
                                            Memberinvestments::where('id', $val->id)->update(['carry_forward_amount'=>$cfdAmount]);


                                            $collection = $val->deposite_amount*12;
                                        }elseif(($val->deposite_amount*12) > $collection){
                                             $pendingAmount =  ($val->deposite_amount*12)-$collection;

                                             if((int) $val->carry_forward_amount >= (int) $pendingAmount){

                                                $cfdAmount = (int) $val->carry_forward_amount-(int) $pendingAmount;
                                                Memberinvestments::where('id', $val->id)->update(['carry_forward_amount'=>$cfdAmount]);
                                                $collection = (int) $collection+(int) $pendingAmount;

                                             }elseif((int) $val->carry_forward_amount == (int) $pendingAmount){

                                                Memberinvestments::where('id', $val->id)->update(['carry_forward_amount'=>0]);
                                                $collection = (int) $collection+(int) $val->carry_forward_amount;

                                             }elseif((int) $pendingAmount > (int) $val->carry_forward_amount){

                                                Memberinvestments::where('id', $val->id)->update(['carry_forward_amount'=>0]);
                                                $collection = (int) $collection+(int) $val->carry_forward_amount;
                                             }
                                        }

                                        $conditionalAmount = 9*$val->deposite_amount;
                                        if($collection < $conditionalAmount){
                                            $cInterest = 0;
                                            $regularInterest = 0;
                                            $total = 0;
                                            $totalDeposit = 0;
                                            $totalInterestDeposit = 0;

                                            $investmentMonths = $years*12;

                                            //$totalInvestmentAmount = Daybook::select('investment_id','deposit','created_at')->where('investment_id',$val->id)->whereIn('transaction_type', [2,4])->whereBetween('created_at', [date("Y-m-d", strtotime(convertDate($val->created_at))), date("Y-m-d", strtotime(convertDate($createDate)))])->sum('deposit');

                                            $totalInvestmentAmount = $collection;

                                            for ($i=1; $i <= $investmentMonths ; $i++){

                                                $mInvestment = $val;
                                                $nDate =  date('Y-m-d', strtotime($val->created_at. ' + '.$i.' months'));
                                                $cMonth = date('m');
                                                $cYear = date('Y');
                                                $cuurentInterest = $mInvestment->interest_rate;
                                                $totalDeposit = $totalInvestmentAmount;

                                                $ts1 = strtotime($mInvestment->created_at);
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
                                            }

                                            $finalAmount = round($totalDeposit+$totalInterestDeposit);

                                            Memberinvestments::where('id', $val->id)->update(['last_deposit_to_ssb_date'=>$createDate]);

                                            InvestmentMonthlyYearlyInterestDeposits::create([
                                                'investment_id' => $val->id,
                                                'plan_type_id' =>3,
                                                'fd_amount' => $finalAmount,
                                                'available_amount' => $finalAmount,
                                                'date' =>$createDate,
                                            ]);

                                            return true;
                                        }elseif($collection >= $conditionalAmount){
                                            Session::put('created_at', date("Y-m-d ".$entryTime."", strtotime(convertDate($createDate))));
                                            $ssbAccountDetails = SavingAccount::with('ssbMember')->select('id','balance','branch_id','branch_code','member_id','account_no')->where('member_id',$val->member_id)->first();

                                            $yearlyAmount = 12*$val->deposite_amount;


                                            if($collection > $yearlyAmount){
                                                $cInterest = 0;
                                                $regularInterest = 0;
                                                $total = 0;
                                                $totalDeposit = 0;
                                                $totalInterestDeposit = 0;

                                                $investmentMonths = $years*12;

                                                //$totalInvestmentAmount = Daybook::select('investment_id','deposit','created_at')->where('investment_id',$val->id)->whereIn('transaction_type', [2,4])->whereBetween('created_at', [date("Y-m-d", strtotime(convertDate($val->created_at))), date("Y-m-d", strtotime(convertDate($createDate)))])->sum('deposit');

                                                $totalInvestmentAmount = $collection;

                                                for ($i=1; $i <= $investmentMonths ; $i++){

                                                    $mInvestment = $val;
                                                    $nDate =  date('Y-m-d', strtotime($val->created_at. ' + '.$i.' months'));
                                                    $cMonth = date('m');
                                                    $cYear = date('Y');
                                                    $cuurentInterest = $mInvestment->interest_rate;
                                                    $totalDeposit = $totalInvestmentAmount;

                                                    $ts1 = strtotime($mInvestment->created_at);
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
                                                }

                                                $finalAmount = round($totalDeposit+$totalInterestDeposit);

                                                $cBalance = ($val->current_balance-($totalDeposit*60/100));
                                                Memberinvestments::where('id', $val->id)->update([
                                                        'current_balance' => $cBalance,
                                                       'last_deposit_to_ssb_amount' => ($totalDeposit*60/100),'last_deposit_to_ssb_date'=>$createDate]);

                                                $trdata['saving_account_transaction_id']=NULL;
                                                $trdata['investment_id']=$val->id;
                                                $trdata['created_at']=date("Y-m-d", strtotime(convertDate($createDate)));
                                                $satRef = TransactionReferences::create($trdata);
                                                $satRefId = $satRef->id;

                                                $amountArraySsb=array('1'=>($totalDeposit*60/100));

                                                $createTransaction = CommanController::createTransaction($satRefId,17,$val->id,$val->member_id,$val->branch_id,getBranchCode($val->branch_id)->branch_code,$amountArraySsb,4,getBranchName($val->branch_id),$val->member_id,$val->account_number,NULL,NULL,getBranchName($val->branch_id),date("Y-m-d", strtotime( str_replace('/','-',$createDate))),NULL,NULL,NULL,'DR');

                                                $loanTypeArray = array(3,5,6,8,9,10,11,12);
                                                $investmentTypeArray = array(3,5,6,8,9,10,11,12);
                                                $data_log['transaction_type']=18;
                                                $data_log['transaction_id']=$createTransaction;
                                                $data_log['saving_account_transaction_reference_id']=$satRefId;
                                                $data_log['investment_id']=$val->id;
                                                $data_log['account_no']=$val->account_number;
                                                $data_log['member_id']=$val->member_id;
                                                $data_log['opening_balance']=$cBalance;
                                                $data_log['withdrawal']=($totalDeposit*60/100);
                                                $data_log['description']='Money Back amount transfer '.$ssbAccountDetails->account_no;
                                                $data_log['branch_id']=$val->branch_id;
                                                $data_log['branch_code']=getBranchCode($val->branch_id)->branch_code;
                                                $data_log['amount']=($totalDeposit*60/100);
                                                $data_log['currency_code']='INR';
                                                $data_log['payment_mode']=4;
                                                $data_log['payment_type']='DR';
                                                $data_log['payment_date']=date("Y-m-d ".$entryTime."", strtotime(convertDate($createDate)));
                                                $data_log['amount_deposit_by_name']=getBranchName($val->branch_id);
                                                $data_log['amount_deposit_by_id']=$val->branch_id;
                                                $data_log['created_by_id']=1;
                                                $data_log['created_by']=2;
                                                $data_log['created_at']=date("Y-m-d ".$entryTime."", strtotime(convertDate($createDate)));
                                                $transcation = Daybook::create($data_log);

                                                /*$nextDaybookRecord = Daybook::where('investment_id',$val->id)->whereDate('created_at','>',$createDate)->get();

                                                foreach ($nextDaybookRecord as $key => $value) {
                                                    $balance_update = $value->opening_balance-($totalDeposit*60/100);
                                                    $daybookBalance = Daybook::find($value->id);
                                                    $daybookBalance->opening_balance=$balance_update;
                                                    $daybookBalance->save();
                                                }*/

                                                InvestmentMonthlyYearlyInterestDeposits::create([
                                                    'investment_id' => $val->id,
                                                    'plan_type_id' =>3,
                                                    'fd_amount' => $finalAmount,
                                                    'yearly_deposit_amount' => ($totalDeposit*60/100),
                                                    'available_amount' => $finalAmount-($totalDeposit*60/100),
                                                    'date' =>$createDate,
                                                ]);

                                                /************* Head Implement************/

                                                $chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
                                                $vno = "";
                                                for ($k = 0; $k < 10; $k++) {
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
                                                $opening_balance = $totalDeposit*60/100;
                                                $amount = $totalDeposit*60/100;
                                                $closing_balance = $totalDeposit*60/100;

                                                $description = ($val->account_number).' Money Back amount '.number_format((float)$amount, 2, '.', '');
                                                $description_dr = getMemberData($val->member_id)->first_name.' '.getMemberData($val->member_id)->last_name.' Dr '.number_format((float)$amount, 2, '.', '');
                                                $description_cr = 'To Monthly Income scheme A/C Cr '.number_format((float)$amount, 2, '.', '');

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

                                                $record5=SavingAccountTranscation::where('account_no',$ssbAccountDetails->account_no)->whereDate('created_at','<',date("Y-m-d", strtotime(convertDate($createDate))))->first();

                                                $balance_update=($totalDeposit*60/100)+$ssbAccountDetails->balance;
                                                $ssbBalance = SavingAccount::find($ssbAccountDetails->id);
                                                $ssbBalance->balance=$balance_update;
                                                $ssbBalance->save();

                                                $ssb['saving_account_id']=$ssbAccountDetails->id;
                                                $ssb['account_no']=$ssbAccountDetails->account_no;
                                                if($record5->opening_balance){
                                                    $ssb['opening_balance']=($totalDeposit*60/100)+$record5->opening_balance;
                                                }else{
                                                    $ssb['opening_balance']=($totalDeposit*60/100);
                                                }
                                                $ssb['deposit']=($totalDeposit*60/100);
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

                                                $record6=SavingAccountTranscation::where('account_no',$ssbAccountDetails->account_no)->whereDate('created_at','>',date("Y-m-d", strtotime(convertDate($createDate))))->get();

                                                foreach ($record6 as $key => $value) {
                                                    $sResult = SavingAccountTranscation::find($value->id);
                                                    $nsResult['opening_balance']=$value->opening_balance+($totalDeposit*60/100);
                                                    $nsResult['updated_at']=$createDate;
                                                    $sResult->update($nsResult);
                                                }

                                                $paymentMode = 4;
                                                $amount_deposit_by_name = $ssbAccountDetails['ssbMember']->first_name.' '.$ssbAccountDetails['ssbMember']->last_name;

                                                $trdata['saving_account_transaction_id']=$saTranctionId;
                                                $trdata['investment_id']=$val->id;
                                                $trdata['created_at']=date("Y-m-d", strtotime(convertDate($createDate)));
                                                $satRef = TransactionReferences::create($trdata);
                                                $satRefId = $satRef->id;

                                                $amountArraySsb = array('1'=>($totalDeposit*60/100));

                                                $ssbCreateTran = CommanController::createTransaction($satRefId,1,$val->id,$ssbAccountDetails->member_id,$ssbAccountDetails->branch_id,$ssbAccountDetails->branch_code,$amountArraySsb,$paymentMode,$amount_deposit_by_name=NULL,$ssbAccountDetails->member_id,$ssbAccountDetails->account_no,$cheque_dd_no=NULL,$bank_name=NULL,$branch_name=NULL,date("Y-m-d", strtotime(convertDate($createDate))),$online_payment_id=NULL,$online_payment_by=NULL,$ssbAccountDetails->account_no,'CR');

                                                $description = $description;
                                                $createDayBook = CommanController::createDayBook($ssbCreateTran,$satRefId,1,$ssbAccountDetails->id,NULL,$ssbAccountDetails->member_id,$amount+$ssbAccountDetails->balance,$amount,$withdrawal=0,$description,$ssbAccountDetails->account_no,$ssbAccountDetails->branch_id,$ssbAccountDetails->branch_code,$amountArraySsb,$paymentMode,$amount_deposit_by_name=NULL,$ssbAccountDetails->member_id,$ssbAccountDetails->account_no,0,NULL,NULL,date("Y-m-d", strtotime(convertDate($createDate))),NULL,$online_payment_by=NULL,$ssbAccountDetails->account_no,'CR');


                                                $dayBookRef = CommanController::createBranchDayBookReference($amount);

                                                $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,56,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$opening_balance,$amount,$closing_balance,$description,'CR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$jv_unique_id,$v_no,$v_date,$ssb_account_id_from,$ssb_account_id_to,$ssb_account_tran_id_to,$ssb_account_tran_id_from,$cheque_type,$cheque_id,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_from_id,$cheque_bank_ac_from_id,$cheque_bank_to,$cheque_bank_ac_to,$cheque_bank_to_name,$cheque_bank_to_branch,$cheque_bank_to_ac_no,$cheque_bank_to_ifsc,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_from_id,$transction_bank_from_ac_id,$transction_bank_to,$transction_bank_ac_to,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc,$transction_date,$created_by,$created_by_id);

                                                /*$allTransaction = CommanController::createAllTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,1,8,20,56,NULL,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$opening_balance,$amount,$closing_balance,$description,'CR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$createDate);*/

                                                $memberTransaction = CommanController::memberTransactionNew($dayBookRef,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id,$bank_id,$bank_ac_id,$amount,$description,$payment_type,$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$createDate,$ssb_account_tran_id_to,$ssb_account_tran_id_from,$jv_unique_id,$cheque_type,$cheque_id);


                                                $head1 = 1;
                                                $head2 = 8;
                                                $head3 = 20;
                                                $head4 = 59;
                                                $head5 = 85;

                                                $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,$head5,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$amount,$amount,$amount,$description,'DR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$jv_unique_id,$v_no,$v_date,$ssb_account_id_from,$ssb_account_id_to,$ssb_account_tran_id_to,$ssb_account_tran_id_from,$cheque_type,$cheque_id,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_from_id,$cheque_bank_ac_from_id,$cheque_bank_to,$cheque_bank_ac_to,$cheque_bank_to_name,$cheque_bank_to_branch,$cheque_bank_to_ac_no,$cheque_bank_to_ifsc,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_from_id,$transction_bank_from_ac_id,$transction_bank_to,$transction_bank_ac_to,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc,$transction_date,$created_by,$created_by_id);

                                                /*$allTransaction = CommanController::createAllTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,$head1,$head2,$head3,$head4,$head5,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$amount,$amount,$amount,$description,'DR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$createDate);*/
                                                /************* Head Implement************/

                                                return true;
                                            }else{


                                                Session::put('created_at', date("Y-m-d ".$entryTime."", strtotime(convertDate($createDate))));

                                                $ssbAccountDetails = SavingAccount::with('ssbMember')->select('id','balance','branch_id','branch_code','member_id','account_no')->where('member_id',$val->member_id)->first();


                                                $cInterest = 0;
                                                $regularInterest = 0;
                                                $total = 0;
                                                $totalDeposit = 0;
                                                $totalInterestDeposit = 0;

                                                $investmentMonths = $years*12;


                                                //$totalInvestmentAmount = Daybook::select('investment_id','deposit','created_at')->where('investment_id',$val->id)->whereIn('transaction_type', [2,4])->whereBetween('created_at', [date("Y-m-d", strtotime(convertDate($val->created_at))), date("Y-m-d", strtotime(convertDate($createDate)))])->sum('deposit');

                                                $totalInvestmentAmount = $collection;



                                                for ($i=1; $i <= $investmentMonths ; $i++){

                                                    $mInvestment = $val;
                                                    $nDate =  date('Y-m-d', strtotime($val->created_at. ' + '.$i.' months'));
                                                    $cMonth = date('m');
                                                    $cYear = date('Y');
                                                    $cuurentInterest = $mInvestment->interest_rate;
                                                    $totalDeposit = $totalInvestmentAmount;

                                                    $ts1 = strtotime($mInvestment->created_at);
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
                                                }

                                                $finalAmount = round($totalDeposit+$totalInterestDeposit);

                                                $cBalance = ($val->current_balance-($totalDeposit*60/100));
                                                   Memberinvestments::where('id', $val->id)->update([
                                                        'current_balance' => $cBalance,
                                                       'last_deposit_to_ssb_amount' => ($totalDeposit*60/100),'last_deposit_to_ssb_date'=>$createDate]);

                                                $trdata['saving_account_transaction_id']=NULL;
                                                $trdata['investment_id']=$val->id;
                                                $trdata['created_at']=date("Y-m-d", strtotime(convertDate($createDate)));
                                                $satRef = TransactionReferences::create($trdata);
                                                $satRefId = $satRef->id;

                                                //echo ($totalDeposit*60/100); die;

                                                $amountArraySsb=array('1'=>($totalDeposit*60/100));

                                                $createTransaction = CommanController::createTransaction($satRefId,17,$val->id,$val->member_id,$val->branch_id,getBranchCode($val->branch_id)->branch_code,$amountArraySsb,4,getBranchName($val->branch_id),$val->member_id,$val->account_number,NULL,NULL,getBranchName($val->branch_id),date("Y-m-d", strtotime( str_replace('/','-',$createDate))),NULL,NULL,NULL,'DR');
                                                if(!isset($ssbAccountDetails->account_no))
                                                {
                                                    dd($val->id);
                                                }

                                                $loanTypeArray = array(3,5,6,8,9,10,11,12);
                                                $investmentTypeArray = array(3,5,6,8,9,10,11,12);
                                                $data_log['transaction_type']=18;
                                                $data_log['transaction_id']=$createTransaction;
                                                $data_log['saving_account_transaction_reference_id']=$satRefId;
                                                $data_log['investment_id']=$val->id;
                                                $data_log['account_no']=$val->account_number;
                                                $data_log['member_id']=$val->member_id;
                                                $data_log['opening_balance']=$cBalance;
                                                $data_log['withdrawal']=($totalDeposit*60/100);
                                                $data_log['description']='Money Back amount transfer '.$ssbAccountDetails->account_no;
                                                $data_log['branch_id']=$val->branch_id;
                                                $data_log['branch_code']=getBranchCode($val->branch_id)->branch_code;
                                                $data_log['amount']=($totalDeposit*60/100);
                                                $data_log['currency_code']='INR';
                                                $data_log['payment_mode']=4;
                                                $data_log['payment_type']='DR';
                                                $data_log['payment_date']=date("Y-m-d ".$entryTime."", strtotime(convertDate($createDate)));
                                                $data_log['amount_deposit_by_name']=getBranchName($val->branch_id);
                                                $data_log['amount_deposit_by_id']=$val->branch_id;
                                                $data_log['created_by_id']=1;
                                                $data_log['created_by']=2;
                                                $data_log['created_at']=date("Y-m-d ".$entryTime."", strtotime(convertDate($createDate)));
                                                $transcation = Daybook::create($data_log);

                                                /*$nextDaybookRecord = Daybook::where('investment_id',$val->id)->whereDate('created_at','>',$createDate)->get();

                                                foreach ($nextDaybookRecord as $key => $value) {
                                                    $balance_update = $value->opening_balance-($totalDeposit*60/100);
                                                    $daybookBalance = Daybook::find($value->id);
                                                    $daybookBalance->opening_balance=$balance_update;
                                                    $daybookBalance->save();
                                                }*/

                                                InvestmentMonthlyYearlyInterestDeposits::create([
                                                    'investment_id' => $val->id,
                                                    'plan_type_id' =>3,
                                                    'fd_amount' => $finalAmount,
                                                    'yearly_deposit_amount' => ($totalDeposit*60/100),
                                                    'available_amount' => $finalAmount-($totalDeposit*60/100),
                                                    'date' =>$createDate,
                                                ]);



                                                /************* Head Implement************/

                                                $chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
                                                $vno = "";
                                                for ($k = 0; $k < 10; $k++) {
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
                                                $opening_balance = $totalDeposit*60/100;
                                                $amount = $totalDeposit*60/100;
                                                $closing_balance = $totalDeposit*60/100;

                                                $description = ($val->account_number).' Money Back amount '.number_format((float)$amount, 2, '.', '');
                                                $description_dr = getMemberData($val->member_id)->first_name.' '.getMemberData($val->member_id)->last_name.' Dr '.number_format((float)$amount, 2, '.', '');
                                                $description_cr = 'To Monthly Income scheme A/C Cr '.number_format((float)$amount, 2, '.', '');

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

                                                $record7=SavingAccountTranscation::where('account_no',$ssbAccountDetails->account_no)->whereDate('created_at','<',date("Y-m-d", strtotime(convertDate($createDate))))->first();

                                                $balance_update=($totalDeposit*60/100)+$ssbAccountDetails->balance;
                                                $ssbBalance = SavingAccount::find($ssbAccountDetails->id);
                                                $ssbBalance->balance=$balance_update;
                                                $ssbBalance->save();

                                                $ssb['saving_account_id']=$ssbAccountDetails->id;
                                                $ssb['account_no']=$ssbAccountDetails->account_no;
                                                if($record7){
                                                    $ssb['opening_balance']=($totalDeposit*60/100)+$record7->opening_balance;
                                                }else{
                                                    $ssb['opening_balance']=($totalDeposit*60/100);
                                                }
                                                $ssb['deposit']=($totalDeposit*60/100);
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

                                                $record8=SavingAccountTranscation::where('account_no',$ssbAccountDetails->account_no)->whereDate('created_at','>',date("Y-m-d", strtotime(convertDate($createDate))))->get();

                                                foreach ($record8 as $key => $value) {
                                                    $sResult = SavingAccountTranscation::find($value->id);
                                                    $nsResult['opening_balance']=$value->opening_balance+($totalDeposit*60/100);
                                                    $nsResult['updated_at']=$createDate;
                                                    $sResult->update($nsResult);
                                                }

                                                $paymentMode = 4;
                                                $amount_deposit_by_name = $ssbAccountDetails['ssbMember']->first_name.' '.$ssbAccountDetails['ssbMember']->last_name;


                                                $trdata['saving_account_transaction_id']=$saTranctionId;
                                                $trdata['investment_id']=$val->id;
                                                $trdata['created_at']=date("Y-m-d", strtotime(convertDate($createDate)));
                                                $satRef = TransactionReferences::create($trdata);
                                                $satRefId = $satRef->id;

                                                $amountArraySsb = array('1'=>($totalDeposit*60/100));

                                                $ssbCreateTran = CommanController::createTransaction($satRefId,1,$val->id,$ssbAccountDetails->member_id,$ssbAccountDetails->branch_id,$ssbAccountDetails->branch_code,$amountArraySsb,$paymentMode,$amount_deposit_by_name=NULL,$ssbAccountDetails->member_id,$ssbAccountDetails->account_no,$cheque_dd_no=NULL,$bank_name=NULL,$branch_name=NULL,date("Y-m-d", strtotime(convertDate($createDate))),$online_payment_id=NULL,$online_payment_by=NULL,$ssbAccountDetails->account_no,'CR');

                                                $description = $description;
                                                $createDayBook = CommanController::createDayBook($ssbCreateTran,$satRefId,1,$ssbAccountDetails->id,NULL,$ssbAccountDetails->member_id,$amount+$ssbAccountDetails->balance,$amount,$withdrawal=0,$description,$ssbAccountDetails->account_no,$ssbAccountDetails->branch_id,$ssbAccountDetails->branch_code,$amountArraySsb,$paymentMode,$amount_deposit_by_name=NULL,$ssbAccountDetails->member_id,$ssbAccountDetails->account_no,0,NULL,NULL,date("Y-m-d", strtotime(convertDate($createDate))),NULL,$online_payment_by=NULL,$ssbAccountDetails->account_no,'CR');


                                                $dayBookRef = CommanController::createBranchDayBookReference($amount);

                                                $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,56,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$opening_balance,$amount,$closing_balance,$description,'CR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$jv_unique_id,$v_no,$v_date,$ssb_account_id_from,$ssb_account_id_to,$ssb_account_tran_id_to,$ssb_account_tran_id_from,$cheque_type,$cheque_id,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_from_id,$cheque_bank_ac_from_id,$cheque_bank_to,$cheque_bank_ac_to,$cheque_bank_to_name,$cheque_bank_to_branch,$cheque_bank_to_ac_no,$cheque_bank_to_ifsc,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_from_id,$transction_bank_from_ac_id,$transction_bank_to,$transction_bank_ac_to,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc,$transction_date,$created_by,$created_by_id);

                                                /*$allTransaction = CommanController::createAllTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,1,8,20,56,NULL,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$opening_balance,$amount,$closing_balance,$description,'CR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$createDate);*/

                                                $memberTransaction = CommanController::memberTransactionNew($dayBookRef,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id,$bank_id,$bank_ac_id,$amount,$description,$payment_type,$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$createDate,$ssb_account_tran_id_to,$ssb_account_tran_id_from,$jv_unique_id,$cheque_type,$cheque_id);


                                                $head1 = 1;
                                                $head2 = 8;
                                                $head3 = 20;
                                                $head4 = 59;
                                                $head5 = 85;

                                                $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,$head5,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$amount,$amount,$amount,$description,'DR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$jv_unique_id,$v_no,$v_date,$ssb_account_id_from,$ssb_account_id_to,$ssb_account_tran_id_to,$ssb_account_tran_id_from,$cheque_type,$cheque_id,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_from_id,$cheque_bank_ac_from_id,$cheque_bank_to,$cheque_bank_ac_to,$cheque_bank_to_name,$cheque_bank_to_branch,$cheque_bank_to_ac_no,$cheque_bank_to_ifsc,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_from_id,$transction_bank_from_ac_id,$transction_bank_to,$transction_bank_ac_to,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc,$transction_date,$created_by,$created_by_id);

                                                /*$allTransaction = CommanController::createAllTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,$head1,$head2,$head3,$head4,$head5,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$amount,$amount,$amount,$description,'DR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$createDate);*/

                                                /************* Head Implement************/
                                            }
                                        }
                                    }

                                }
                            }
                        }else{
                            $countRefund = InvestmentMonthlyYearlyInterestDeposits::where('investment_id',$val->id)->where('plan_type_id',3)->count();
                            

                            $diff = abs(strtotime($cDate) - strtotime($val->last_deposit_to_ssb_date));
                            $years = floor($diff / (365*60*60*24));
                            $entryDate =  date('Y-m-d', strtotime($val->last_deposit_to_ssb_date. ' + 1 year'));
                            if($years > 0){
                                $entryDate =  date('Y-m-d', strtotime($val->last_deposit_to_ssb_date. ' + 1 year'));
                                $createDate = $entryDate;
                                $cDate = $entryDate;
                                $depositInterest = InvestmentMonthlyYearlyInterestDeposits::where('investment_id',$val->id)->where('plan_type_id',3)->orderby('date', 'desc')->first();

                                $collection = Daybook::where('investment_id',$val->id)->whereIn('transaction_type', [2,4])->whereBetween('created_at', [date("Y-m-d", strtotime(convertDate($val->last_deposit_to_ssb_date))), date("Y-m-d", strtotime(convertDate($createDate)))])->sum('deposit');

                                if((int) $collection > (int) ($val->deposite_amount*12)){
                                    $extraAmount = (int) $collection-(int) ($val->deposite_amount*12);
                                    $cfdAmount = (int) $extraAmount+(int) $val->carry_forward_amount;
                                    Memberinvestments::where('id', $val->id)->update(['carry_forward_amount'=>$cfdAmount]);
                                    $collection = $val->deposite_amount*12;
                                }elseif(($val->deposite_amount*12) > $collection){
                                     $pendingAmount =  ($val->deposite_amount*12)-$collection;

                                     if((int) $val->carry_forward_amount >= (int) $pendingAmount){

                                        $cfdAmount = (int) $val->carry_forward_amount-(int) $pendingAmount;
                                        Memberinvestments::where('id', $val->id)->update(['carry_forward_amount'=>$cfdAmount]);
                                        $collection = (int) $collection+(int) $pendingAmount;

                                     }elseif((int) $val->carry_forward_amount == (int) $pendingAmount){

                                        Memberinvestments::where('id', $val->id)->update(['carry_forward_amount'=>0]);
                                        $collection = (int) $collection+(int) $val->carry_forward_amount;

                                     }elseif((int) $pendingAmount > (int) $val->carry_forward_amount){

                                        Memberinvestments::where('id', $val->id)->update(['carry_forward_amount'=>0]);
                                        $collection = (int) $collection+(int) $val->carry_forward_amount;
                                     }
                                }

                                $conditionalAmount = 9*$val->deposite_amount;

                                if($collection < $conditionalAmount){
                                    $cInterest = 0;
                                    $regularInterest = 0;
                                    $total = 0;
                                    $totalDeposit = 0;
                                    $totalInterestDeposit = 0;

                                    $investmentMonths = $years*12;

                                    //$totalInvestmentAmount = Daybook::select('investment_id','deposit','created_at')->where('investment_id',$val->id)->whereIn('transaction_type', [2,4])->whereBetween('created_at', [date("Y-m-d", strtotime(convertDate($val->last_deposit_to_ssb_date))), date("Y-m-d", strtotime(convertDate($createDate)))])->sum('deposit');

                                    $totalInvestmentAmount = $collection;

                                    for ($i=1; $i <= $investmentMonths ; $i++){

                                        $mInvestment = $val;
                                        $nDate =  date('Y-m-d', strtotime($val->created_at. ' + '.$i.' months'));
                                        $cMonth = date('m');
                                        $cYear = date('Y');
                                        $cuurentInterest = $mInvestment->interest_rate;
                                        $totalDeposit = $totalInvestmentAmount;

                                        /*$previousRecord = Daybook::select('deposit','created_at')->whereIn('transaction_type', [2,4])->where('investment_id', $val->investment_id)->whereDate('created_at', '<', $nDate)->max('created_at');

                                        $sumPreviousRecordAmount = Daybook::whereIn('transaction_type', [2,4])->where('investment_id', $val->investment_id)->whereDate('created_at', '<=', $nDate)->sum('deposit');

                                        $d1 = explode('-',$mInvestment->created_at);
                                        $d2 = explode('-',$nDate);*/

                                        $ts1 = strtotime($mInvestment->created_at);
                                        $ts2 = strtotime($nDate);

                                        $year1 = date('Y', $ts1);
                                        $year2 = date('Y', $ts2);

                                        $month1 = date('m', $ts1);
                                        $month2 = date('m', $ts2);

                                        $monthDiff = (($year2 - $year1) * 12) + ($month2 - $month1);

                                        /*if($cMonth > $d2[1] && $cYear > $d2[0]){

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
                                        }*/

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
                                    }

                                    $finalAmount = round($totalDeposit+$totalInterestDeposit);

                                    if($depositInterest){
                                        $availableAmountFd = ($depositInterest->available_amount*9/100)+$depositInterest->available_amount;
                                    }else{
                                        $availableAmountFd = 0;
                                    }

                                    if($countRefund < 6){
                                       Memberinvestments::where('id', $val->id)->update(['last_deposit_to_ssb_date'=>$createDate]);
                                        InvestmentMonthlyYearlyInterestDeposits::create([
                                            'investment_id' => $val->id,
                                            'plan_type_id' =>3,
                                            'fd_amount' => $finalAmount,
                                            'available_amount_fd' => $availableAmountFd,
                                            'available_amount' => ($finalAmount+$availableAmountFd),
                                            'date' =>$createDate,
                                        ]);
                                    }else{
                                        InvestmentMonthlyYearlyInterestDeposits::create([
                                            'investment_id' => $val->id,
                                            'plan_type_id' =>3,
                                            'fd_amount' => $finalAmount,
                                            'available_amount_fd' => $availableAmountFd,
                                            'available_amount' => ($finalAmount+$availableAmountFd),
                                            'date' =>$createDate,
                                        ]);
                                    }
                                }elseif($collection >= $conditionalAmount){


                                    $yearlyAmount = 12*$val->deposite_amount;
                                       

                                    if($collection > $yearlyAmount){
                                        $cInterest = 0;
                                        $regularInterest = 0;
                                        $total = 0;
                                        $totalDeposit = 0;
                                        $totalInterestDeposit = 0;

                                        $investmentMonths = $years*12;

                                        //$totalInvestmentAmount = Daybook::select('investment_id','deposit','created_at')->where('investment_id',$val->id)->whereIn('transaction_type', [2,4])->whereBetween('created_at', [date("Y-m-d", strtotime(convertDate($val->last_deposit_to_ssb_date))), date("Y-m-d", strtotime(convertDate($createDate)))])->sum('deposit');

                                        $totalInvestmentAmount = $collection;

                                        for ($i=1; $i <= $investmentMonths ; $i++){

                                            $mInvestment = $val;
                                            $nDate =  date('Y-m-d', strtotime($val->created_at. ' + '.$i.' months'));
                                            $cMonth = date('m');
                                            $cYear = date('Y');
                                            $cuurentInterest = $mInvestment->interest_rate;
                                            $totalDeposit = $totalInvestmentAmount;

                                            $ts1 = strtotime($mInvestment->created_at);
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
                                        }

                                        $finalAmount = round($totalDeposit+$totalInterestDeposit);

                                        if($depositInterest){
                                            $availableAmountFd = ($depositInterest->available_amount*9/100)+$depositInterest->available_amount;
                                        }else{
                                            $availableAmountFd = 0;
                                        }

                                        if($countRefund < 6){
                                            Session::put('created_at', date("Y-m-d ".$entryTime."", strtotime(convertDate($cDate))));
                                            $ssbAccountDetails = SavingAccount::with('ssbMember')->select('id','balance','branch_id','branch_code','member_id','account_no')->where('member_id',$val->member_id)->first();

                                            $cBalance = ($val->current_balance-($totalDeposit*60/100));
                                            Memberinvestments::where('id', $val->id)->update([
                                                'current_balance' => $cBalance,
                                               'last_deposit_to_ssb_amount' => ($totalDeposit*60/100),'last_deposit_to_ssb_date'=>$createDate]);

                                            $trdata['saving_account_transaction_id']=NULL;
                                            $trdata['investment_id']=$val->id;
                                            $trdata['created_at']=date("Y-m-d", strtotime(convertDate($createDate)));
                                            $satRef = TransactionReferences::create($trdata);
                                            $satRefId = $satRef->id;

                                            $amountArraySsb=array('1'=>($totalDeposit*60/100));

                                            $createTransaction = CommanController::createTransaction($satRefId,17,$val->id,$val->member_id,$val->branch_id,getBranchCode($val->branch_id)->branch_code,$amountArraySsb,4,getBranchName($val->branch_id),$val->member_id,$val->account_number,NULL,NULL,getBranchName($val->branch_id),date("Y-m-d", strtotime( str_replace('/','-',$createDate))),NULL,NULL,NULL,'DR');

                                            $loanTypeArray = array(3,5,6,8,9,10,11,12);
                                            $investmentTypeArray = array(3,5,6,8,9,10,11,12);
                                            $data_log['transaction_type']=18;
                                            $data_log['transaction_id']=$createTransaction;
                                            $data_log['saving_account_transaction_reference_id']=$satRefId;
                                            $data_log['investment_id']=$val->id;
                                            $data_log['account_no']=$val->account_number;
                                            $data_log['member_id']=$val->member_id;
                                            $data_log['opening_balance']=$cBalance;
                                            $data_log['withdrawal']=($totalDeposit*60/100);
                                            $data_log['description']='Money Back amount transfer '.$ssbAccountDetails->account_no;
                                            $data_log['branch_id']=$val->branch_id;
                                            $data_log['branch_code']=getBranchCode($val->branch_id)->branch_code;
                                            $data_log['amount']=($totalDeposit*60/100);
                                            $data_log['currency_code']='INR';
                                            $data_log['payment_mode']=4;
                                            $data_log['payment_type']='DR';
                                            $data_log['payment_date']=date("Y-m-d ".$entryTime."", strtotime(convertDate($createDate)));
                                            $data_log['amount_deposit_by_name']=getBranchName($val->branch_id);
                                            $data_log['amount_deposit_by_id']=$val->branch_id;
                                            $data_log['created_by_id']=1;
                                            $data_log['created_by']=2;
                                            $data_log['created_at']=date("Y-m-d ".$entryTime."", strtotime(convertDate($createDate)));
                                            $transcation = Daybook::create($data_log);

                                            /*$nextDaybookRecord = Daybook::where('investment_id',$val->id)->whereDate('created_at','>',$createDate)->get();

                                            foreach ($nextDaybookRecord as $key => $value) {
                                                $balance_update = $value->opening_balance-($totalDeposit*60/100);
                                                $daybookBalance = Daybook::find($value->id);
                                                $daybookBalance->opening_balance=$balance_update;
                                                $daybookBalance->save();
                                            }*/


                                            InvestmentMonthlyYearlyInterestDeposits::create([
                                                'investment_id' => $val->id,
                                                'plan_type_id' => 3,
                                                'fd_amount' => $finalAmount,
                                                'available_amount_fd' => $availableAmountFd,
                                                'yearly_deposit_amount' => ($totalDeposit*60/100),
                                                'available_amount' => ($finalAmount+$availableAmountFd)-($totalDeposit*60/100),
                                                'date' =>$createDate,
                                            ]);
                                        }else{
                                            InvestmentMonthlyYearlyInterestDeposits::create([
                                                'investment_id' => $val->id,
                                                'plan_type_id' =>3,
                                                'fd_amount' => $finalAmount,
                                                'available_amount_fd' => $availableAmountFd,
                                                'available_amount' => ($finalAmount+$availableAmountFd),
                                                'date' =>$createDate,
                                            ]);
                                        }

                                        /************* Head Implement************/

                                        $chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
                                        $vno = "";
                                        for ($k = 0; $k < 10; $k++) {
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
                                        $opening_balance = $totalDeposit*60/100;
                                        $amount = $totalDeposit*60/100;
                                        $closing_balance = $totalDeposit*60/100;

                                        $description = ($val->account_number).' Money Back amount '.number_format((float)$amount, 2, '.', '');
                                        $description_dr = getMemberData($val->member_id)->first_name.' '.getMemberData($val->member_id)->last_name.' Dr '.number_format((float)$amount, 2, '.', '');
                                        $description_cr = 'To Monthly Income scheme A/C Cr '.number_format((float)$amount, 2, '.', '');

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

                                        $record9=SavingAccountTranscation::where('account_no',$ssbAccountDetails->account_no)->whereDate('created_at','<',date("Y-m-d", strtotime(convertDate($createDate))))->first();

                                        $balance_update=($totalDeposit*60/100)+$ssbAccountDetails->balance;
                                        $ssbBalance = SavingAccount::find($ssbAccountDetails->id);
                                        $ssbBalance->balance=$balance_update;
                                        $ssbBalance->save();

                                        $ssb['saving_account_id']=$ssbAccountDetails->id;
                                        $ssb['account_no']=$ssbAccountDetails->account_no;
                                        if($record9){
                                            $ssb['opening_balance']=($totalDeposit*60/100)+$record9->opening_balance;
                                        }else{
                                            $ssb['opening_balance']=($totalDeposit*60/100);
                                        }

                                        $ssb['deposit']=($totalDeposit*60/100);
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

                                        $record10=SavingAccountTranscation::where('account_no',$ssbAccountDetails->account_no)->whereDate('created_at','>',date("Y-m-d", strtotime(convertDate($createDate))))->get();

                                        foreach ($record10 as $key => $value) {
                                            $sResult = SavingAccountTranscation::find($value->id);
                                            $nsResult['opening_balance']=$value->opening_balance+($totalDeposit*60/100);
                                            $nsResult['updated_at']=$createDate;
                                            $sResult->update($nsResult);
                                        }

                                        $paymentMode = 4;
                                        $amount_deposit_by_name = $ssbAccountDetails['ssbMember']->first_name.' '.$ssbAccountDetails['ssbMember']->last_name;

                                        $trdata['saving_account_transaction_id']=$saTranctionId;
                                        $trdata['investment_id']=$val->id;
                                        $trdata['created_at']=date("Y-m-d", strtotime(convertDate($cDate)));
                                        $satRef = TransactionReferences::create($trdata);
                                        $satRefId = $satRef->id;

                                        $amountArraySsb = array('1'=>($totalDeposit*60/100));

                                        $ssbCreateTran = CommanController::createTransaction($satRefId,1,$val->id,$ssbAccountDetails->member_id,$ssbAccountDetails->branch_id,$ssbAccountDetails->branch_code,$amountArraySsb,$paymentMode,$amount_deposit_by_name=NULL,$ssbAccountDetails->member_id,$ssbAccountDetails->account_no,$cheque_dd_no=NULL,$bank_name=NULL,$branch_name=NULL,date("Y-m-d", strtotime(convertDate($cDate))),$online_payment_id=NULL,$online_payment_by=NULL,$ssbAccountDetails->account_no,'CR');

                                        $description = $description;
                                        $createDayBook = CommanController::createDayBook($ssbCreateTran,$satRefId,1,$ssbAccountDetails->id,NULL,$ssbAccountDetails->member_id,$amount+$ssbAccountDetails->balance,$amount,$withdrawal=0,$description,$ssbAccountDetails->account_no,$ssbAccountDetails->branch_id,$ssbAccountDetails->branch_code,$amountArraySsb,$paymentMode,$amount_deposit_by_name=NULL,$ssbAccountDetails->member_id,$ssbAccountDetails->account_no,0,NULL,NULL,date("Y-m-d", strtotime(convertDate($cDate))),NULL,$online_payment_by=NULL,$ssbAccountDetails->account_no,'CR');


                                        $dayBookRef = CommanController::createBranchDayBookReference($amount);

                                        $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,56,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$opening_balance,$amount,$closing_balance,$description,'CR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$jv_unique_id,$v_no,$v_date,$ssb_account_id_from,$ssb_account_id_to,$ssb_account_tran_id_to,$ssb_account_tran_id_from,$cheque_type,$cheque_id,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_from_id,$cheque_bank_ac_from_id,$cheque_bank_to,$cheque_bank_ac_to,$cheque_bank_to_name,$cheque_bank_to_branch,$cheque_bank_to_ac_no,$cheque_bank_to_ifsc,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_from_id,$transction_bank_from_ac_id,$transction_bank_to,$transction_bank_ac_to,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc,$transction_date,$created_by,$created_by_id);

                                        /*$allTransaction = CommanController::createAllTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,1,8,20,56,NULL,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$opening_balance,$amount,$closing_balance,$description,'CR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$cDate);*/

                                        $memberTransaction = CommanController::memberTransactionNew($dayBookRef,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id,$bank_id,$bank_ac_id,$amount,$description,$payment_type,$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$cDate,$ssb_account_tran_id_to,$ssb_account_tran_id_from,$jv_unique_id,$cheque_type,$cheque_id);


                                        $head1 = 1;
                                        $head2 = 8;
                                        $head3 = 20;
                                        $head4 = 59;
                                        $head5 = 85;

                                        $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,$head5,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$amount,$amount,$amount,$description,'DR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$jv_unique_id,$v_no,$v_date,$ssb_account_id_from,$ssb_account_id_to,$ssb_account_tran_id_to,$ssb_account_tran_id_from,$cheque_type,$cheque_id,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_from_id,$cheque_bank_ac_from_id,$cheque_bank_to,$cheque_bank_ac_to,$cheque_bank_to_name,$cheque_bank_to_branch,$cheque_bank_to_ac_no,$cheque_bank_to_ifsc,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_from_id,$transction_bank_from_ac_id,$transction_bank_to,$transction_bank_ac_to,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc,$transction_date,$created_by,$created_by_id);

                                        /*$allTransaction = CommanController::createAllTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,$head1,$head2,$head3,$head4,$head5,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$amount,$amount,$amount,$description,'DR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$cDate);*/
                                        /************* Head Implement************/
                                    }else{
                                        $cInterest = 0;
                                        $regularInterest = 0;
                                        $total = 0;
                                        $totalDeposit = 0;
                                        $totalInterestDeposit = 0;

                                        $investmentMonths = $years*12;

                                        //$totalInvestmentAmount = Daybook::select('investment_id','deposit','created_at')->where('investment_id',$val->id)->whereIn('transaction_type', [2,4])->whereBetween('created_at', [date("Y-m-d", strtotime(convertDate($val->last_deposit_to_ssb_date))), date("Y-m-d", strtotime(convertDate($createDate)))])->sum('deposit');

                                        $totalInvestmentAmount = $collection;

                                        for ($i=1; $i <= $investmentMonths ; $i++){

                                            $mInvestment = $val;
                                            $nDate =  date('Y-m-d', strtotime($val->created_at. ' + '.$i.' months'));
                                            $cMonth = date('m');
                                            $cYear = date('Y');
                                            $cuurentInterest = $mInvestment->interest_rate;
                                            $totalDeposit = $totalInvestmentAmount;

                                            $ts1 = strtotime($mInvestment->created_at);
                                            $ts2 = strtotime($nDate);

                                            $year1 = date('Y', $ts1);
                                            $year2 = date('Y', $ts2);

                                            $month1 = date('m', $ts1);
                                            $month2 = date('m', $ts2);

                                            $defaulterInterest = 0;

                                            $monthDiff = (($year2 - $year1) * 12) + ($month2 - $month1);

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
                                        }

                                        $finalAmount = round($totalDeposit+$totalInterestDeposit);

                                        if($depositInterest){
                                            $availableAmountFd = ($depositInterest->available_amount*9/100)+$depositInterest->available_amount;
                                        }else{
                                            $availableAmountFd = 0;
                                        }

                                        if($countRefund < 6){
                                            
                                            Session::put('created_at', date("Y-m-d ".$entryTime."", strtotime(convertDate($cDate))));
                                            $ssbAccountDetails = SavingAccount::with('ssbMember')->select('id','balance','branch_id','branch_code','member_id','account_no')->where('member_id',$val->member_id)->first();

                                            $cBalance = ($val->current_balance-($totalDeposit*60/100));
                                            Memberinvestments::where('id', $val->id)->update([
                                                'current_balance' => $cBalance,
                                               'last_deposit_to_ssb_amount' => ($totalDeposit*60/100),'last_deposit_to_ssb_date'=>$createDate]);

                                            $trdata['saving_account_transaction_id']=NULL;
                                            $trdata['investment_id']=$val->id;
                                            $trdata['created_at']=date("Y-m-d", strtotime(convertDate($createDate)));
                                            $satRef = TransactionReferences::create($trdata);
                                            $satRefId = $satRef->id;

                                            $amountArraySsb=array('1'=>($totalDeposit*60/100));

                                            $createTransaction = CommanController::createTransaction($satRefId,17,$val->id,$val->member_id,$val->branch_id,getBranchCode($val->branch_id)->branch_code,$amountArraySsb,4,getBranchName($val->branch_id),$val->member_id,$val->account_number,NULL,NULL,getBranchName($val->branch_id),date("Y-m-d", strtotime( str_replace('/','-',$createDate))),NULL,NULL,NULL,'DR');

                                            $loanTypeArray = array(3,5,6,8,9,10,11,12);
                                            $investmentTypeArray = array(3,5,6,8,9,10,11,12);
                                            $data_log['transaction_type']=18;
                                            $data_log['transaction_id']=$createTransaction;
                                            $data_log['saving_account_transaction_reference_id']=$satRefId;
                                            $data_log['investment_id']=$val->id;
                                            $data_log['account_no']=$val->account_number;
                                            $data_log['member_id']=$val->member_id;
                                            $data_log['opening_balance']=$cBalance;
                                            $data_log['withdrawal']=($totalDeposit*60/100);
                                            $data_log['description']='Money Back amount transfer '.$ssbAccountDetails->account_no;
                                            $data_log['branch_id']=$val->branch_id;
                                            $data_log['branch_code']=getBranchCode($val->branch_id)->branch_code;
                                            $data_log['amount']=($totalDeposit*60/100);
                                            $data_log['currency_code']='INR';
                                            $data_log['payment_mode']=4;
                                            $data_log['payment_type']='DR';
                                            $data_log['payment_date']=date("Y-m-d ".$entryTime."", strtotime(convertDate($createDate)));
                                            $data_log['amount_deposit_by_name']=getBranchName($val->branch_id);
                                            $data_log['amount_deposit_by_id']=$val->branch_id;
                                            $data_log['created_by_id']=1;
                                            $data_log['created_by']=2;
                                            $data_log['created_at']=date("Y-m-d ".$entryTime."", strtotime(convertDate($createDate)));
                                            $transcation = Daybook::create($data_log);

                                            /*$nextDaybookRecord = Daybook::where('investment_id',$val->id)->whereDate('created_at','>',$createDate)->get();

                                            foreach ($nextDaybookRecord as $key => $value) {
                                                $balance_update = $value->opening_balance-($totalDeposit*60/100);
                                                $daybookBalance = Daybook::find($value->id);
                                                $daybookBalance->opening_balance=$balance_update;
                                                $daybookBalance->save();
                                            }*/

                                            InvestmentMonthlyYearlyInterestDeposits::create([
                                                'investment_id' => $val->id,
                                                'plan_type_id' =>3,
                                                'fd_amount' => $finalAmount,
                                                'available_amount_fd' => $availableAmountFd,
                                                'yearly_deposit_amount' => ($totalDeposit*60/100),
                                                'available_amount' => ($finalAmount+$availableAmountFd)-($totalDeposit*60/100),
                                                'date' =>$createDate,
                                            ]);
                                           

                                        }else{
                                            InvestmentMonthlyYearlyInterestDeposits::create([
                                                'investment_id' => $val->id,
                                                'plan_type_id' =>3,
                                                'fd_amount' => $finalAmount,
                                                'available_amount_fd' => $availableAmountFd,
                                                'available_amount' => ($finalAmount+$availableAmountFd),
                                                'date' =>$createDate,
                                            ]);
                                        }

                                        /************* Head Implement************/

                                        $chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
                                        $vno = "";
                                        for ($k = 0; $k < 10; $k++) {
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
                                        $opening_balance = $totalDeposit*60/100;
                                        $amount = $totalDeposit*60/100;
                                        $closing_balance = $totalDeposit*60/100;

                                        $description = ($val->account_number).' Money Back amount '.number_format((float)$amount, 2, '.', '');
                                        $description_dr = getMemberData($val->member_id)->first_name.' '.getMemberData($val->member_id)->last_name.' Dr '.number_format((float)$amount, 2, '.', '');
                                        $description_cr = 'To Monthly Income scheme A/C Cr '.number_format((float)$amount, 2, '.', '');

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

                                        $record11=SavingAccountTranscation::where('account_no',$ssbAccountDetails->account_no)->whereDate('created_at','<',date("Y-m-d", strtotime(convertDate($createDate))))->first();

                                        $balance_update=($totalDeposit*60/100)+$ssbAccountDetails->balance;
                                        $ssbBalance = SavingAccount::find($ssbAccountDetails->id);
                                        $ssbBalance->balance=$balance_update;
                                        $ssbBalance->save();

                                        $ssb['saving_account_id']=$ssbAccountDetails->id;
                                        $ssb['account_no']=$ssbAccountDetails->account_no;
                                        if($record11){
                                            $ssb['opening_balance']=($totalDeposit*60/100)+$record11->opening_balance;
                                        }else{
                                            $ssb['opening_balance']=($totalDeposit*60/100);
                                        }
                                        $ssb['deposit']=($totalDeposit*60/100);
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

                                        $record12=SavingAccountTranscation::where('account_no',$ssbAccountDetails->account_no)->whereDate('created_at','>',date("Y-m-d", strtotime(convertDate($createDate))))->get();

                                        foreach ($record12 as $key => $value) {
                                            $sResult = SavingAccountTranscation::find($value->id);
                                            $nsResult['opening_balance']=$value->opening_balance+($totalDeposit*60/100);
                                            $nsResult['updated_at']=$createDate;
                                            $sResult->update($nsResult);
                                        }

                                        $paymentMode = 4;
                                        $amount_deposit_by_name = $ssbAccountDetails['ssbMember']->first_name.' '.$ssbAccountDetails['ssbMember']->last_name;


                                        $trdata['saving_account_transaction_id']=$saTranctionId;
                                        $trdata['investment_id']=$val->id;
                                        $trdata['created_at']=date("Y-m-d", strtotime(convertDate($cDate)));
                                        $satRef = TransactionReferences::create($trdata);
                                        $satRefId = $satRef->id;

                                        $amountArraySsb = array('1'=>($totalDeposit*60/100));

                                        $ssbCreateTran = CommanController::createTransaction($satRefId,1,$val->id,$ssbAccountDetails->member_id,$ssbAccountDetails->branch_id,$ssbAccountDetails->branch_code,$amountArraySsb,$paymentMode,$amount_deposit_by_name=NULL,$ssbAccountDetails->member_id,$ssbAccountDetails->account_no,$cheque_dd_no=NULL,$bank_name=NULL,$branch_name=NULL,date("Y-m-d", strtotime(convertDate($cDate))),$online_payment_id=NULL,$online_payment_by=NULL,$ssbAccountDetails->account_no,'CR');

                                        $description = $description;
                                        $createDayBook = CommanController::createDayBook($ssbCreateTran,$satRefId,1,$ssbAccountDetails->id,NULL,$ssbAccountDetails->member_id,$amount+$ssbAccountDetails->balance,$amount,$withdrawal=0,$description,$ssbAccountDetails->account_no,$ssbAccountDetails->branch_id,$ssbAccountDetails->branch_code,$amountArraySsb,$paymentMode,$amount_deposit_by_name=NULL,$ssbAccountDetails->member_id,$ssbAccountDetails->account_no,0,NULL,NULL,date("Y-m-d", strtotime(convertDate($cDate))),NULL,$online_payment_by=NULL,$ssbAccountDetails->account_no,'CR');


                                        $dayBookRef = CommanController::createBranchDayBookReference($amount);

                                        $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,56,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$opening_balance,$amount,$closing_balance,$description,'CR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$jv_unique_id,$v_no,$v_date,$ssb_account_id_from,$ssb_account_id_to,$ssb_account_tran_id_to,$ssb_account_tran_id_from,$cheque_type,$cheque_id,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_from_id,$cheque_bank_ac_from_id,$cheque_bank_to,$cheque_bank_ac_to,$cheque_bank_to_name,$cheque_bank_to_branch,$cheque_bank_to_ac_no,$cheque_bank_to_ifsc,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_from_id,$transction_bank_from_ac_id,$transction_bank_to,$transction_bank_ac_to,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc,$transction_date,$created_by,$created_by_id);

                                        /*$allTransaction = CommanController::createAllTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,1,8,20,56,NULL,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$opening_balance,$amount,$closing_balance,$description,'CR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,date("Y-m-d", strtotime(convertDate($cDate))));*/

                                        $memberTransaction = CommanController::memberTransactionNew($dayBookRef,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id,$bank_id,$bank_ac_id,$amount,$description,$payment_type,$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,date("Y-m-d", strtotime(convertDate($cDate))),$ssb_account_tran_id_to,$ssb_account_tran_id_from,$jv_unique_id,$cheque_type,$cheque_id);


                                        $head1 = 1;
                                        $head2 = 8;
                                        $head3 = 20;
                                        $head4 = 59;
                                        $head5 = 85;
    
                                        $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,$head5,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$amount,$amount,$amount,$description,'DR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$jv_unique_id,$v_no,$v_date,$ssb_account_id_from,$ssb_account_id_to,$ssb_account_tran_id_to,$ssb_account_tran_id_from,$cheque_type,$cheque_id,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_from_id,$cheque_bank_ac_from_id,$cheque_bank_to,$cheque_bank_ac_to,$cheque_bank_to_name,$cheque_bank_to_branch,$cheque_bank_to_ac_no,$cheque_bank_to_ifsc,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_from_id,$transction_bank_from_ac_id,$transction_bank_to,$transction_bank_ac_to,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc,$transction_date,$created_by,$created_by_id);
                                        /*$allTransaction = CommanController::createAllTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,$head1,$head2,$head3,$head4,$head5,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$amount,$amount,$amount,$description,'DR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,date("Y-m-d", strtotime(convertDate($cDate))));*/
                                        /************* Head Implement************/
                                    }
                                }
                            }
                        }
                        \Log::info("Amount Deposite !".$val->id);
                    }
                //}
            }

        DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('alert', $ex->getMessage());
        }
        \Log::info("Amount Deposite Successfullyffffgbhgfnfg!");
    }
}
