<?php

namespace App\Console\Commands;

use App\Http\Controllers\Admin\CommanController;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;
use App\Models\Member;
use App\Models\MemberInvestmentInterest;
use App\Models\MemberInvestmentInterestTds;
use App\Models\Memberinvestments;
use App\Models\TdsDeposit;
use App\Models\Form15G;
use DB;
use Session;

class GenerateTdsForInterest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generateTdsForInterest:genrate';/**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate tds from member investment';/**
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
        die("stop");
        $entryTime = date("H:i:s");
        $date = Carbon::now()->format('Y-m-d H:i:s');
        $year = Carbon::now()->format('Y');
        //$marchDate = $year.'-03-31';
        $marchDate = '2021-03-31';
        $previousDate = date('Y-m-d',strtotime(''.$marchDate.' -1 year'));
            
        $memberInvestments = Member::with('associateInvestment');
        /*$memberInvestments = $memberInvestments->whereHas('associateInvestment', function (Builder $query) {
          $query->where('is_mature',1);
        });*/
        $memberInvestments = $memberInvestments->where('id',6156)->get();

        DB::beginTransaction();
        try {
            Session::put('created_at', date("Y-m-d ".$entryTime."", strtotime(convertDate($marchDate))));
            if($marchDate == $marchDate){

                if($memberInvestments){

                    $formG = Form15G::where('member_id',$memberInvestments[0]->id)->where('year','2022')->whereNotNull('file')->first();
                    
                    if($formG){

                            $tdsAmount = 0;
                            $tdsPercentage = 0;

                            foreach ($memberInvestments[0]['associateInvestment'] as $key => $value) {
                                $interestAmount = MemberInvestmentInterest::where('investment_id',$value->id)->whereBetween(\DB::raw('DATE(date)'), [$previousDate, $marchDate])->sum('interest_amount'); 
                                $tdsAmountonInterest = 0;

                                if($value->is_mature == 1){

                                    /***************** Head ****************/
                                    $chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
                                    $vno = "";
                                    for ($i = 0; $i < 10; $i++) {
                                        $vno .= $chars[mt_rand(0, strlen($chars)-1)];
                                    }        

                                    $branch_id = $value->branch_id;
                                    $type = 3;
                                    $sub_type = 34;
                                    $type_id = $value->id;
                                    $type_transaction_id = $value->id;
                                    $associate_id = NULL;
                                    $member_id = $value->member_id;
                                    $branch_id_to = NULL;
                                    $branch_id_from = NULL;
                                    $opening_balance = $interestAmount;
                                    $amount = $interestAmount;
                                    $closing_balance = $interestAmount;

                                    $description = ($value->account_number).' Interest amount '.number_format((float)$interestAmount, 2, '.', '');
                                    $description_dr = getMemberData($value->member_id)->first_name.' '.getMemberData($value->member_id)->last_name.' Dr '.number_format((float)$interestAmount, 2, '.', '');
                                    $description_cr = 'Interest amount A/C Cr '.number_format((float)$interestAmount, 2, '.', '');
                                    
                                    $payment_type = 'CR';
                                    $payment_mode = 3;
                                    $currency_code = 'INR';
                                    $amount_to_id =$value->member_id;
                                    $amount_to_name = getMemberData($value->member_id)->first_name.' '.getMemberData($value->member_id)->last_name;
                                    $amount_from_id = NULL;
                                    $amount_from_name = NULL;
                                    $v_no = $vno;
                                    $v_date = date("Y-m-d ".$entryTime."", strtotime(convertDate($marchDate)));
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

                                    $ssb_account_id_to = NULL;
                                    $ssb_account_tran_id_to = NULL;

                                    if($amount > 0){
                                        
                                        $dayBookRef = CommanController::createBranchDayBookReference($amount);

                                        $daybookInvest = CommanController::branchDayBookNew($dayBookRef,$branch_id,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$amount,$amount,$amount,$description,$description_dr,$description_cr,$payment_type,$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$is_contra,$contra_id,date("Y-m-d", strtotime(convertDate($marchDate))),$ssb_account_tran_id_to,$ssb_account_tran_id_from,$jv_unique_id,$cheque_type,$cheque_id);


                                        $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,36,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$opening_balance,$amount,$closing_balance,$description,'DR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$jv_unique_id,$v_no,$v_date,$ssb_account_id_from,$ssb_account_id_to,$ssb_account_tran_id_to,$ssb_account_tran_id_from,$cheque_type,$cheque_id,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_from_id,$cheque_bank_ac_from_id,$cheque_bank_to,$cheque_bank_ac_to,$cheque_bank_to_name,$cheque_bank_to_branch,$cheque_bank_to_ac_no,$cheque_bank_to_ifsc,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_from_id,$transction_bank_from_ac_id,$transction_bank_to,$transction_bank_ac_to,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc,date("Y-m-d", strtotime(convertDate($marchDate))),$created_by,$created_by_id);


                                        /*$allTransaction = CommanController::createAllTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,4,14,36,NULL,NULL,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$opening_balance,$amount,$closing_balance,$description,'CR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,date("Y-m-d", strtotime(convertDate($marchDate))));*/ 

                                        if($value->plan_id == 2){
                                            $head1 = 1;
                                            $head2 = 8;
                                            $head3 = 20;
                                            $head4 = 59;
                                            $head5 = 80;
                                            $head_id = 80;
                                        }elseif($value->plan_id == 3){
                                            $head1 = 1;
                                            $head2 = 8;
                                            $head3 = 20;
                                            $head4 = 59;
                                            $head5 = 85;
                                            $head_id = 85;
                                        }elseif($value->plan_id == 4){
                                            $head1 = 1;
                                            $head2 = 8;
                                            $head3 = 20;
                                            $head4 = 57;
                                            $head5 = 79;
                                            $head_id = 79;
                                        }elseif($value->plan_id == 5){
                                            $head1 = 1;
                                            $head2 = 8;
                                            $head3 = 20;
                                            $head4 = 59;
                                            $head5 = 83;
                                            $head_id = 83;
                                        }elseif($value->plan_id == 6){
                                            $head1 = 1;
                                            $head2 = 8;
                                            $head3 = 20;
                                            $head4 = 59;
                                            $head5 = 84;
                                            $head_id = 84;
                                        }elseif($value->plan_id == 7){
                                            $head1 = 1;
                                            $head2 = 8;
                                            $head3 = 20;
                                            $head4 = 58;
                                            $head5 = NULL;
                                            $head_id = 58;
                                        }elseif($value->plan_id == 8){
                                            $head1 = 1;
                                            $head2 = 8;
                                            $head3 = 20;
                                            $head4 = 57;
                                            $head5 = 78;
                                            $head_id = 78;
                                        }elseif($value->plan_id == 9){
                                            $head1 = 1;
                                            $head2 = 8;
                                            $head3 = 20;
                                            $head4 = 57;
                                            $head5 = 77;
                                            $head_id = 77;
                                        }elseif($value->plan_id == 10){
                                            $head1 = 1;
                                            $head2 = 8;
                                            $head3 = 20;
                                            $head4 = 59;
                                            $head5 = 83;
                                            $head_id = 83;
                                        }elseif($value->plan_id == 11){
                                            $head1 = 1;
                                            $head2 = 8;
                                            $head3 = 20;
                                            $head4 = 59;
                                            $head5 = 82;
                                            $head_id = 82;
                                        }

                                        $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,36,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$opening_balance,$amount,$closing_balance,$description,'CR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$jv_unique_id,$v_no,$v_date,$ssb_account_id_from,$ssb_account_id_to,$ssb_account_tran_id_to,$ssb_account_tran_id_from,$cheque_type,$cheque_id,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_from_id,$cheque_bank_ac_from_id,$cheque_bank_to,$cheque_bank_ac_to,$cheque_bank_to_name,$cheque_bank_to_branch,$cheque_bank_to_ac_no,$cheque_bank_to_ifsc,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_from_id,$transction_bank_from_ac_id,$transction_bank_to,$transction_bank_ac_to,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc,date("Y-m-d", strtotime(convertDate($marchDate))),$created_by,$created_by_id);

                                        /*$allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,36,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$opening_balance,$amount,$closing_balance,$description,'DR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$jv_unique_id,$v_no,$v_date,$ssb_account_id_from,$ssb_account_id_to,$ssb_account_tran_id_to,$ssb_account_tran_id_from,$cheque_type,$cheque_id,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_from_id,$cheque_bank_ac_from_id,$cheque_bank_to,$cheque_bank_ac_to,$cheque_bank_to_name,$cheque_bank_to_branch,$cheque_bank_to_ac_no,$cheque_bank_to_ifsc,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_from_id,$transction_bank_from_ac_id,$transction_bank_to,$transction_bank_ac_to,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc,date("Y-m-d", strtotime(convertDate($marchDate))),$created_by,$created_by_id);*/

                                        $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,$head_id,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$opening_balance,$amount,$closing_balance,$description,'CR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$jv_unique_id,$v_no,$v_date,$ssb_account_id_from,$ssb_account_id_to,$ssb_account_tran_id_to,$ssb_account_tran_id_from,$cheque_type,$cheque_id,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_from_id,$cheque_bank_ac_from_id,$cheque_bank_to,$cheque_bank_ac_to,$cheque_bank_to_name,$cheque_bank_to_branch,$cheque_bank_to_ac_no,$cheque_bank_to_ifsc,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_from_id,$transction_bank_from_ac_id,$transction_bank_to,$transction_bank_ac_to,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc,date("Y-m-d", strtotime(convertDate($marchDate))),$created_by,$created_by_id);

                                        /*$allTransaction = CommanController::createAllTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,$head1,$head2,$head3,$head4,$head5,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$opening_balance,$amount,$closing_balance,$description,'CR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,date("Y-m-d", strtotime(convertDate($marchDate))));*/

                                        /*$memberTransaction = CommanController::createMemberTransaction($dayBookRef,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id,$bank_id,$bank_ac_id,$amount,$description,$payment_type,$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,date("Y-m-d", strtotime(convertDate($marchDate))));*/
                                    }
                                    
                                    /***************** Head ****************/

                                    MemberInvestmentInterestTds::create([
                                        'member_id' => $value->member_id,
                                        'investment_id' => $value->id,
                                        'plan_type' => $value->plan_id,
                                        'branch_id' => $value->branch_id,
                                        'interest_amount' => $interestAmount,
                                        'date_from' => $previousDate,
                                        'date_to' => $marchDate,
                                        'tdsamount_on_interest' => $tdsAmountonInterest,
                                        'tds_amount' => $tdsAmount,
                                        'tds_percentage' => $tdsPercentage,
                                        'created_at' => date("Y-m-d ".$entryTime."", strtotime(convertDate($marchDate))),
                                    ]);

                                    Memberinvestments::where('id', $value->id)->update([
                                        'investment_interest_tds_date'=>$marchDate]);
                                }
                            }   
                        
                    }else{

                        $diff = abs(strtotime($marchDate) - strtotime($memberInvestments[0]->dob));
                        $years = floor($diff / (365*60*60*24));

                        if($years >= 60){
                           $tdsDetail = TdsDeposit::where('type',2)->where('start_date','<',$marchDate)->first();
                        }else{
                            $penCard = get_member_id_proof($memberInvestments[0]->id,5);
                            if($penCard){
                                $tdsDetail = TdsDeposit::where('type',1)->where('start_date','<',$marchDate)->first();
                            }else{
                                $tdsDetail = TdsDeposit::where('type',5)->where('start_date','<',$marchDate)->first();
                            }
                        }

                        if($tdsDetail){
                            $tdsAmount = $tdsDetail->tds_amount;
                            $tdsPercentage = $tdsDetail->tds_per;
                        }else{
                            $tdsAmount = 0;
                            $tdsPercentage = 0;
                        }

                        $totalBalance = 0;
                        foreach ($memberInvestments[0]['associateInvestment'] as $key => $value) {
                            $interestAmount = MemberInvestmentInterest::where('investment_id',$value->id)->whereBetween(\DB::raw('DATE(date)'), [$previousDate, $marchDate])->sum('interest_amount'); 
                            $totalBalance = $totalBalance+$interestAmount;
                        } 

                        if($totalBalance > $tdsAmount){
                            foreach ($memberInvestments[0]['associateInvestment'] as $key => $value) {

                                if($value->plan_id != 1){

                                    $interestAmount = MemberInvestmentInterest::where('investment_id',$value->id)->whereBetween(\DB::raw('DATE(date)'), [$previousDate, $marchDate])->sum('interest_amount'); 
                                    $tdsAmountonInterest = $tdsPercentage*$interestAmount/100;

                                    if($value->is_mature == 1){

                                        /***************** Head ****************/
                                        $chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
                                        $vno = "";
                                        for ($i = 0; $i < 10; $i++) {
                                            $vno .= $chars[mt_rand(0, strlen($chars)-1)];
                                        }        

                                        $branch_id = $value->branch_id;
                                        $type = 3;
                                        $sub_type = 34;
                                        $type_id = $value->id;
                                        $type_transaction_id = NULL;
                                        $associate_id = NULL;
                                        $member_id = $value->member_id;
                                        $branch_id_to = NULL;
                                        $branch_id_from = NULL;
                                        $opening_balance = $interestAmount;
                                        $amount = $interestAmount;
                                        $closing_balance = $interestAmount;

                                        $description = ($value->account_number).' Interest amount '.number_format((float)$interestAmount, 2, '.', '');
                                        $description_dr = getMemberData($value->member_id)->first_name.' '.getMemberData($value->member_id)->last_name.' Dr '.number_format((float)$interestAmount, 2, '.', '');
                                        $description_cr = 'Interest amount A/C Cr '.number_format((float)$interestAmount, 2, '.', '');
                                        
                                        $payment_type = 'CR';
                                        $payment_mode = 3;
                                        $currency_code = 'INR';
                                        $amount_to_id =$value->member_id;
                                        $amount_to_name = getMemberData($value->member_id)->first_name.' '.getMemberData($value->member_id)->last_name;
                                        $amount_from_id = NULL;
                                        $amount_from_name = NULL;
                                        $v_no = $vno;
                                        $v_date = date("Y-m-d ".$entryTime."", strtotime(convertDate($marchDate)));
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

                                        $ssb_account_id_to = NULL;
                                        $ssb_account_tran_id_to = NULL;

                                        if($amount > 0){
                                            
                                            $dayBookRef = CommanController::createBranchDayBookReference($amount);

                                            $daybookInvest = CommanController::branchDayBookNew($dayBookRef,$branch_id,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$amount,$amount,$amount,$description,$description_dr,$description_cr,$payment_type,$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$is_contra,$contra_id,$marchDate,$ssb_account_tran_id_to,$ssb_account_tran_id_from,$jv_unique_id,$cheque_type,$cheque_id);

                                            $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,36,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$opening_balance,$amount,$closing_balance,$description,'DR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$jv_unique_id,$v_no,$v_date,$ssb_account_id_from,$ssb_account_id_to,$ssb_account_tran_id_to,$ssb_account_tran_id_from,$cheque_type,$cheque_id,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_from_id,$cheque_bank_ac_from_id,$cheque_bank_to,$cheque_bank_ac_to,$cheque_bank_to_name,$cheque_bank_to_branch,$cheque_bank_to_ac_no,$cheque_bank_to_ifsc,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_from_id,$transction_bank_from_ac_id,$transction_bank_to,$transction_bank_ac_to,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc,$marchDate,$created_by,$created_by_id);

                                            /*$allTransaction = CommanController::createAllTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,4,14,36,NULL,NULL,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$opening_balance,$amount,$closing_balance,$description,'CR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$marchDate);*/ 

                                        }

                                        if($tdsAmountonInterest > 0){

                                            $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,62,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$tdsAmountonInterest,$tdsAmountonInterest,$tdsAmountonInterest,$description,'CR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$jv_unique_id,$v_no,$v_date,$ssb_account_id_from,$ssb_account_id_to,$ssb_account_tran_id_to,$ssb_account_tran_id_from,$cheque_type,$cheque_id,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_from_id,$cheque_bank_ac_from_id,$cheque_bank_to,$cheque_bank_ac_to,$cheque_bank_to_name,$cheque_bank_to_branch,$cheque_bank_to_ac_no,$cheque_bank_to_ifsc,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_from_id,$transction_bank_from_ac_id,$transction_bank_to,$transction_bank_ac_to,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc,$marchDate,$created_by,$created_by_id);

                                            /*$allTransaction = CommanController::createAllTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,1,8,22,62,NULL,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$tdsAmountonInterest,$tdsAmountonInterest,$tdsAmountonInterest,$description,'CR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$marchDate);*/
                                        }

                                        if(($opening_balance-$tdsAmountonInterest) > 0){

                                            if($value->plan_id == 2){
                                                $head1 = 1;
                                                $head2 = 8;
                                                $head3 = 20;
                                                $head4 = 59;
                                                $head5 = 80;
                                                $head_id = 80;
                                            }elseif($value->plan_id == 3){
                                                $head1 = 1;
                                                $head2 = 8;
                                                $head3 = 20;
                                                $head4 = 59;
                                                $head5 = 85;
                                                $head_id = 85;
                                            }elseif($value->plan_id == 4){
                                                $head1 = 1;
                                                $head2 = 8;
                                                $head3 = 20;
                                                $head4 = 57;
                                                $head5 = 79;
                                                $head_id = 79;
                                            }elseif($value->plan_id == 5){
                                                $head1 = 1;
                                                $head2 = 8;
                                                $head3 = 20;
                                                $head4 = 59;
                                                $head5 = 83;
                                                $head_id = 83;
                                            }elseif($value->plan_id == 6){
                                                $head1 = 1;
                                                $head2 = 8;
                                                $head3 = 20;
                                                $head4 = 59;
                                                $head5 = 84;
                                                $head_id = 84;
                                            }elseif($value->plan_id == 7){
                                                $head1 = 1;
                                                $head2 = 8;
                                                $head3 = 20;
                                                $head4 = 58;
                                                $head5 = NULL;
                                                $head_id = 58;
                                            }elseif($value->plan_id == 8){
                                                $head1 = 1;
                                                $head2 = 8;
                                                $head3 = 20;
                                                $head4 = 57;
                                                $head5 = 78;
                                                $head_id = 78;
                                            }elseif($value->plan_id == 9){
                                                $head1 = 1;
                                                $head2 = 8;
                                                $head3 = 20;
                                                $head4 = 57;
                                                $head5 = 77;
                                                $head_id = 77;
                                            }elseif($value->plan_id == 10){
                                                $head1 = 1;
                                                $head2 = 8;
                                                $head3 = 20;
                                                $head4 = 59;
                                                $head5 = 83;
                                                $head_id = 83;
                                            }elseif($value->plan_id == 11){
                                                $head1 = 1;
                                                $head2 = 8;
                                                $head3 = 20;
                                                $head4 = 59;
                                                $head5 = 82;
                                                $head_id = 82;
                                            }

                                            $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,$head_id,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,($opening_balance-$tdsAmountonInterest),($opening_balance-$tdsAmountonInterest),($opening_balance-$tdsAmountonInterest),$description,'CR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$jv_unique_id,$v_no,$v_date,$ssb_account_id_from,$ssb_account_id_to,$ssb_account_tran_id_to,$ssb_account_tran_id_from,$cheque_type,$cheque_id,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_from_id,$cheque_bank_ac_from_id,$cheque_bank_to,$cheque_bank_ac_to,$cheque_bank_to_name,$cheque_bank_to_branch,$cheque_bank_to_ac_no,$cheque_bank_to_ifsc,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_from_id,$transction_bank_from_ac_id,$transction_bank_to,$transction_bank_ac_to,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc,$marchDate,$created_by,$created_by_id);

                                            /*$allTransaction = CommanController::createAllTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,$head1,$head2,$head3,$head4,$head5,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,($opening_balance-$tdsAmountonInterest),($opening_balance-$tdsAmountonInterest),($opening_balance-$tdsAmountonInterest),$description,'CR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$marchDate);*/
                                        }

                                        /*if($amount > 0){
                                            $memberTransaction = CommanController::createMemberTransaction($dayBookRef,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id,$bank_id,$bank_ac_id,$amount,$description,$payment_type,$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$marchDate);
                                        }*/
                                        
                                        /***************** Head ****************/

                                        MemberInvestmentInterestTds::create([
                                            'member_id' => $value->member_id,
                                            'investment_id' => $value->id,
                                            'plan_type' => $value->plan_id,
                                            'branch_id' => $value->branch_id,
                                            'interest_amount' => $interestAmount,
                                            'date_from' => $previousDate,
                                            'date_to' => $marchDate,
                                            'tdsamount_on_interest' => $tdsAmountonInterest,
                                            'tds_amount' => $tdsAmount,
                                            'tds_percentage' => $tdsPercentage,
                                            'created_at' => date("Y-m-d ".$entryTime."", strtotime(convertDate($marchDate))),
                                        ]);

                                        Memberinvestments::where('id', $value->id)->update([
                                            'investment_interest_tds_date'=>$marchDate]);
                                    }
                                }
                            }
                        }else{

                            $tdsAmount = 0;
                            $tdsPercentage = 0;


                            foreach ($memberInvestments[0]['associateInvestment'] as $key => $value) {

                                if($value->plan_id != 1){

                                    $interestAmount = MemberInvestmentInterest::where('investment_id',$value->id)->whereBetween(\DB::raw('DATE(date)'), [$previousDate, $marchDate])->sum('interest_amount'); 
                                    $tdsAmountonInterest = 0;

                                    if($value->is_mature == 1){

                                        /***************** Head ****************/
                                        $chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
                                        $vno = "";
                                        for ($i = 0; $i < 10; $i++) {
                                            $vno .= $chars[mt_rand(0, strlen($chars)-1)];
                                        }        

                                        $branch_id = $value->branch_id;
                                        $type = 3;
                                        $sub_type = 34;
                                        $type_id = $value->id;
                                        $type_transaction_id = $value->id;
                                        $associate_id = NULL;
                                        $member_id = $value->member_id;
                                        $branch_id_to = NULL;
                                        $branch_id_from = NULL;
                                        $opening_balance = $interestAmount;
                                        $amount = $interestAmount;
                                        $closing_balance = $interestAmount;

                                        $description = ($value->account_number).' Interest amount '.number_format((float)$interestAmount, 2, '.', '');
                                        $description_dr = getMemberData($value->member_id)->first_name.' '.getMemberData($value->member_id)->last_name.' Dr '.number_format((float)$interestAmount, 2, '.', '');
                                        $description_cr = 'Interest amount A/C Cr '.number_format((float)$interestAmount, 2, '.', '');
                                        
                                        $payment_type = 'CR';
                                        $payment_mode = 3;
                                        $currency_code = 'INR';
                                        $amount_to_id =$value->member_id;
                                        $amount_to_name = getMemberData($value->member_id)->first_name.' '.getMemberData($value->member_id)->last_name;
                                        $amount_from_id = NULL;
                                        $amount_from_name = NULL;
                                        $v_no = $vno;
                                        $v_date = date("Y-m-d ".$entryTime."", strtotime(convertDate($marchDate)));
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

                                        $ssb_account_id_to = NULL;
                                        $ssb_account_tran_id_to = NULL;

                                        if($amount > 0){

                                            $dayBookRef = CommanController::createBranchDayBookReference($amount);

                                            $daybookInvest = CommanController::branchDayBookNew($dayBookRef,$branch_id,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$amount,$amount,$amount,$description,$description_dr,$description_cr,$payment_type,$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$is_contra,$contra_id,date("Y-m-d", strtotime(convertDate($marchDate))),$ssb_account_tran_id_to,$ssb_account_tran_id_from,$jv_unique_id,$cheque_type,$cheque_id);

                                            $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,36,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$opening_balance,$amount,$closing_balance,$description,'DR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$jv_unique_id,$v_no,$v_date,$ssb_account_id_from,$ssb_account_id_to,$ssb_account_tran_id_to,$ssb_account_tran_id_from,$cheque_type,$cheque_id,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_from_id,$cheque_bank_ac_from_id,$cheque_bank_to,$cheque_bank_ac_to,$cheque_bank_to_name,$cheque_bank_to_branch,$cheque_bank_to_ac_no,$cheque_bank_to_ifsc,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_from_id,$transction_bank_from_ac_id,$transction_bank_to,$transction_bank_ac_to,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc,date("Y-m-d", strtotime(convertDate($marchDate))),$created_by,$created_by_id);

                                            /*$allTransaction = CommanController::createAllTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,4,14,36,NULL,NULL,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$opening_balance,$amount,$closing_balance,$description,'CR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,date("Y-m-d", strtotime(convertDate($marchDate))));*/ 

                                            /*$memberTransaction = CommanController::createMemberTransaction($dayBookRef,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id,$bank_id,$bank_ac_id,$amount,$description,$payment_type,$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,date("Y-m-d", strtotime(convertDate($marchDate))));*/
                                        }

                                        if(($opening_balance-$tdsAmountonInterest) > 0){

                                            if($value->plan_id == 2){
                                                $head1 = 1;
                                                $head2 = 8;
                                                $head3 = 20;
                                                $head4 = 59;
                                                $head5 = 80;
                                                $head_id = 80;
                                            }elseif($value->plan_id == 3){
                                                $head1 = 1;
                                                $head2 = 8;
                                                $head3 = 20;
                                                $head4 = 59;
                                                $head5 = 85;
                                                $head_id = 85;
                                            }elseif($value->plan_id == 4){
                                                $head1 = 1;
                                                $head2 = 8;
                                                $head3 = 20;
                                                $head4 = 57;
                                                $head5 = 79;
                                                $head_id = 79;
                                            }elseif($value->plan_id == 5){
                                                $head1 = 1;
                                                $head2 = 8;
                                                $head3 = 20;
                                                $head4 = 59;
                                                $head5 = 83;
                                                $head_id = 83;
                                            }elseif($value->plan_id == 6){
                                                $head1 = 1;
                                                $head2 = 8;
                                                $head3 = 20;
                                                $head4 = 59;
                                                $head5 = 84;
                                                $head_id = 84;
                                            }elseif($value->plan_id == 7){
                                                $head1 = 1;
                                                $head2 = 8;
                                                $head3 = 20;
                                                $head4 = 58;
                                                $head5 = NULL;
                                                $head_id = 58;
                                            }elseif($value->plan_id == 8){
                                                $head1 = 1;
                                                $head2 = 8;
                                                $head3 = 20;
                                                $head4 = 57;
                                                $head5 = 78;
                                                $head_id = 78;
                                            }elseif($value->plan_id == 9){
                                                $head1 = 1;
                                                $head2 = 8;
                                                $head3 = 20;
                                                $head4 = 57;
                                                $head5 = 77;
                                                $head_id = 77;
                                            }elseif($value->plan_id == 10){
                                                $head1 = 1;
                                                $head2 = 8;
                                                $head3 = 20;
                                                $head4 = 59;
                                                $head5 = 83;
                                                $head_id = 83;
                                            }elseif($value->plan_id == 11){
                                                $head1 = 1;
                                                $head2 = 8;
                                                $head3 = 20;
                                                $head4 = 59;
                                                $head5 = 82;
                                                $head_id = 82;
                                            }

                                            $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,$head_id,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,($opening_balance-$tdsAmountonInterest),($opening_balance-$tdsAmountonInterest),($opening_balance-$tdsAmountonInterest),$description,'CR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$jv_unique_id,$v_no,$v_date,$ssb_account_id_from,$ssb_account_id_to,$ssb_account_tran_id_to,$ssb_account_tran_id_from,$cheque_type,$cheque_id,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_from_id,$cheque_bank_ac_from_id,$cheque_bank_to,$cheque_bank_ac_to,$cheque_bank_to_name,$cheque_bank_to_branch,$cheque_bank_to_ac_no,$cheque_bank_to_ifsc,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_from_id,$transction_bank_from_ac_id,$transction_bank_to,$transction_bank_ac_to,$transction_bank_to_name,$transction_bank_to_ac_no,$transction_bank_to_branch,$transction_bank_to_ifsc,date("Y-m-d", strtotime(convertDate($marchDate))),$created_by,$created_by_id);

                                            /*$allTransaction = CommanController::createAllTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,$head1,$head2,
                                                $head3,$head4,$head5,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,($opening_balance-$tdsAmountonInterest),($opening_balance-$tdsAmountonInterest),($opening_balance-$tdsAmountonInterest),$description,'CR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,date("Y-m-d", strtotime(convertDate($marchDate))));*/

                                        }
                                        
                                        /***************** Head ****************/

                                        MemberInvestmentInterestTds::create([
                                            'member_id' => $value->member_id,
                                            'investment_id' => $value->id,
                                            'plan_type' => $value->plan_id,
                                            'branch_id' => $value->branch_id,
                                            'interest_amount' => $interestAmount,
                                            'date_from' => $previousDate,
                                            'date_to' => $marchDate,
                                            'tdsamount_on_interest' => $tdsAmountonInterest,
                                            'tds_amount' => $tdsAmount,
                                            'tds_percentage' => $tdsPercentage,
                                            'created_at' => date("Y-m-d ".$entryTime."", strtotime(convertDate($marchDate))),
                                        ]);

                                        Memberinvestments::where('id', $value->id)->update([
                                            'investment_interest_tds_date'=>$marchDate]);
                                    }
                                }
                            }
                        }
                    }
                }
            }

        DB::commit();
        } catch (\Exception $ex) {
            DB::rollback(); 
            return back()->with('alert', $ex->getMessage());
        }
        
        \Log::info("Tds generate successfully!");
    }
}
