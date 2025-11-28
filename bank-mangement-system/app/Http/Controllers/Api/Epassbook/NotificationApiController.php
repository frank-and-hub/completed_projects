<?php

namespace App\Http\Controllers\Api\Epassbook;
//die('stop app');
use DB;
use URL;
use Session;

use Carbon;
use DateTime;
use Validator; 
use App\Services\Sms;
use App\Models\Files;
use App\Models\Loans;
use App\Models\Member;
use App\Models\Daybook;
use App\Models\Profits;
use App\Models\BranchCash;
use App\Models\Grouploans;
use App\Services\LoanEmiPaymentService;
use App\Http\Resources\EpassBookLoanResource;

use App\Models\LoanEmisNew;

use App\Models\Memberloans;

use App\Models\Transcation;

use App\Models\AccountHeads;

use App\Models\LoanDayBooks;

use App\Models\SamraddhBank;

use Illuminate\Http\Request;

use App\Models\BranchDaybook;

use App\Models\Loanotherdocs;

use App\Models\SavingAccount;

use App\Models\AllTransaction;

use App\Models\ReceivedCheque;

use App\Models\SamraddhCheque;

use App\Models\MemberTransaction;

use App\Models\AllHeadTransaction;

use App\Http\Traits\EmiDatesTraits;

use App\Models\AssociateCommission;

use App\Models\CommissionEntryLoan;

use App\Models\SamraddhBankAccount;

use App\Models\SamraddhBankClosing;

use App\Models\SamraddhBankDaybook;

use App\Models\Memberinvestments;

use App\Models\Notification;

use App\Models\SamraddhChequeIssue;

use App\Http\Controllers\Controller;

use App\Models\Loanapplicantdetails;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\Loaninvestmentmembers;
use App\Models\Loansguarantordetails;

use App\Models\ReceivedChequePayment;

use App\Models\TransactionReferences;

use Illuminate\Support\Facades\Schema;

use App\Models\Loanscoapplicantdetails;

use App\Models\SavingAccountTranscation;
use Illuminate\Support\Facades\Response;

use App\Http\Traits\Oustanding_amount_trait;
use App\Http\Controllers\Admin\CommanController;
use App\Http\Controllers\Api\CommanAppController;


class NotificationApiController extends Controller
{
    use EmiDatesTraits;
    public function __construct( LoanEmiPaymentService $loanPayment)
    {
        $this->loanPaymentService = $loanPayment;
    }

    /**
     * submit_loan_payment_emi.
     *
     * @param  \App\Request  $request
     * @return \Illuminate\Http\Response
     */

     public function get_notification(Request $request)
     {
         $input = $request->all();
         $response = [];
     
         if (isset($input["member_id"]) && $input["member_id"] != "") {
             $member = Member::select('id', 'member_id')->where('member_id', $input["member_id"])->first();
          
             if (isset($member->id)) {
                 $token = md5($member->member_id);
     
                 if ($token == $request->token) {
                     $notificationData = Notification::where("user_id", $member->id)
                         ->where('panel_type', 1)
                         ->orderBy('created_at_default', 'desc')
                         ->offset(0)
                         ->limit(50)
                         ->get();

                        //  dd($notificationData);
                    
                     $notifications = [];
                     foreach ($notificationData as $notification) {
                         switch ($notification->notification_type) {
                             case 1:
                                 $notification['notification_type'] = 'member_profile';
                                 break;
                             case 2:
                                 $notification['notification_type'] = 'investment_renewal';
                                 break;
                             case 3:
                                 $notification['notification_type'] = 'maturity';
                                 break;
                             case 4:
                                 $notification['notification_type'] = 'loan_due';
                                 break;
                            case 5:
                                $notification['notification_type'] = 'group_loan_due';
                                break;
                             default:
                                 $notification['notification_type'] = '';
                                 break;
                         }
                       
                         $notifications[] = $notification;
                     }
     
                     $response["status"] = "Success";
                     $response["code"] = 200;
                     $response["messages"] = "Data";
                     $response["data"] = $notifications;
                 } else {
                     $response["status"] = "Error";
                     $response["code"] = 201;
                     $response["messages"] = 'API token mismatch!';
                     $response["data"] = [];
                 }
             } else {
                 $response["status"] = "Error";
                 $response["code"] = 201;
                 $response["messages"] = "Enter Valid Member Id";
                 $response["data"] = [];
             }
         } else {
             $response["status"] = "Error";
             $response["code"] = 201;
             $response["messages"] = "Input parameter missing";
             $response["data"] = [];
         }
         
         return response()->json($response);
     }


    // public function get_notification(Request $request)
    // {
    //     $response = array();

    //     $input = $request->all();

    //     if(isset($input["member_id"]) && $input["member_id"]!=""){
            
    //         // Get Member ID
    //         $member = Member::select('id','member_id')->where('member_id',$input["member_id"])->first();
            

    //         if(isset($member->id)){
    //             $token = md5($member->member_id);
              
    //             if($token == $request->token){
    //                 $notificationData = Notification::where("user_id",$member->id)->where('panel_type','e-passbook')->orderBy('created_at_default', 'desc')->offset(0)->limit(50)->get();

    //                 $response["status"] = "Success";
    //                 $response["code"] = "200";
    //                 $response["messages"] = "Data";
    //                 $response["data"] = $notificationData;
    //             }
    //             else
    //             {
    //                 $response["status"] = "Error";
    //                 $response["code"] = 201;
    //                 $response["messages"] = 'API token mismatch!';
    //                 $response["data"] = array(); 
    //             }
                
    //         } else {
    //             $response["status"] = "Error";
    //             $response["code"] = 201;
    //             $response["messages"] = "Enter Valid Member Id";
    //             $response["data"] = array();
    //         }
    //     } else {
    //         $response["status"] = "Error";
    //         $response["code"] = "201";
    //         $response["messages"] = "Input parameter missing";
    //         $response["data"] = array();
    //     }
    //     return response()->json($response);
    // } 


    public function send_notification(Request $request)
    {

        $registrationIds = array("eB63ggcow6tOe2Pv0domqi:APA91bFzf1ncgXgK0eO7gbGlBNcTQD4jI6pr1kvR-0jWVgHfa1ooUY9piaSuZEUhUcQJ5KLS7zRjIDeVHdOM8BJg0ye0FMrSV7nERsNSV_2mIh8xNXTQ_VS-_eZLwBiQdDAYWQc055rS");
        
        $data = array('type' => 'user', 'title' => "test title", 'sound' => 'default', 'body' => "test Message");
        $fields = array('registration_ids' => $registrationIds, 'data' => $data);

        $data = json_encode($fields);
        //FCM API end-point
        $url = 'https://fcm.googleapis.com/fcm/send';
        //api_key in Firebase Console -> Project Settings -> CLOUD MESSAGING -> Server key
        $server_key = 'AAAAPIG7IEA:APA91bHUrepLK7rjqRwXF6OGdj8A94vJkGBAa_mpAgyNAhwdq_mQduhnH-cok4Hu8F_Mz1EpuK5eJxsjlqzKEz0S0S4tS8SnagSW915IKz9Olni8DSIeibRTZOjVfCbytLImLXKlLvun';
        //header with content_type api key
        $headers = array(
            'Content-Type:application/json',
            'Authorization:key='.$server_key
        );
        //CURL request to route notification to FCM connection server (provided by Google)
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $result = curl_exec($ch);
        print_r($result); die;
        curl_close($ch);
        
    } 


    public static function getRecords($dailyDepositeRecord,$currentDate)
    {
           //print_r($dailyDepositeRecord['plan_id']);die;
    
            switch ($dailyDepositeRecord['plan_id']) {
                case '7':
                    $pendingEmiAMount=0;
                    $pendingEmi=0;
                    $investCreatedDate =strtotime($dailyDepositeRecord['created_at']);
                   
                    $CURRENTDATE = strtotime($currentDate);

                    $totalBetweendays = abs($investCreatedDate-$CURRENTDATE);
                    $totalBetweendays = ceil(floatval($totalBetweendays/86400));
                    $totalAmount = ($totalBetweendays + 1) * $dailyDepositeRecord['deposite_amount'];
                    $getRenewalReceivedAmount= \App\Models\Daybook::whereIn('transaction_type',[2,4])->where('account_no',$dailyDepositeRecord['account_number'])->sum('deposit');
                   
                    if($getRenewalReceivedAmount != $totalAmount)
                    {
                        $pendingEmiAMount = $getRenewalReceivedAmount - $totalAmount ;
                        $pendingEmi =  $pendingEmiAMount/ $dailyDepositeRecord['deposite_amount'];
                    } 
                    else{
                        $pendingEmiAMount = 0;
                        $pendingEmi =  0;
                    }
                    return ['pendingEmiAMount'=>$pendingEmiAMount,'pendingEmi'=>$pendingEmi];
                    break;
                case '2':
                case '3':
                case '5':
                case '7':
                case '10':
                case '11':
                    $pendingEmiAMount=0;
                    $pendingEmi=0;
                    $investCreatedDate = Carbon\Carbon::parse($dailyDepositeRecord['created_at']);
                    $totalBetweenmonth = $investCreatedDate->diffInMonths($currentDate);
                    $totalAmount = ($totalBetweenmonth + 1) *  $dailyDepositeRecord['deposite_amount'];
                    $getRenewalReceivedAmount= \App\Models\Daybook::whereIn('transaction_type',[2,4])->where('account_no',$dailyDepositeRecord['account_number'])->sum('deposit');


                    if($getRenewalReceivedAmount != $totalAmount)
                    {
                        $pendingEmiAMount = $getRenewalReceivedAmount - $totalAmount ;

                        $pendingEmi =  $pendingEmiAMount/ $dailyDepositeRecord['deposite_amount'];
                    } 
                     else{
                        $pendingEmiAMount = 0;
                        $pendingEmi =  0;
                    }
                    return ['pendingEmiAMount'=>$pendingEmiAMount,'pendingEmi'=>$pendingEmi];
                
                    break;
            }
      
    }

    // public function getNotificationDetails(Request $request)
    // {
    //     $response = array();

    //     $input = $request->all();
    //     $current_balance=0.00;

    //     if(isset($input["member_id"]) && $input["member_id"]!="" && isset($input["type"]) && $input["type"]!="" && isset($input["loan_id"]) && $input["loan_id"]!=""){
            
    //         // Get Member ID
    //         //$member = Member::select('id','mobile_no')->where('member_id',$input["member_id"])->first();

    //         $member = Member::with(['savingAccount_Custom'=>function($q){
    //             $q->with('savingAccountBalance');
    //         }])->select('id','member_id','mobile_no')->where('member_id',$input["member_id"])->first();
    //         $balance =  $member['savingAccount_Custom']['savingAccountBalance']->sum('deposit') - $member['savingAccount_Custom']['savingAccountBalance']->sum('withdrawal');
    //         $current_balance  =  number_format((float) $balance, 2, '.', '');



    //         if(isset($member->id)){
    //             $token = md5($member->member_id);
    //             if($token == $request->token)
    //             {
                
    //                 $type = $input["type"];

    //                 $arr = array();
    //                 $arr["account_number"] ='';
    //                 $arr["deno_amount"] =0;
    //                 $arr["maturity_date"] ='';
    //                 $arr["deposit"] =0;
    //                 $arr["opening_date"] ='';
    //                 $arr["due_amount"] =0;
    //                 $arr["emi_amount"] =0;
    //                 $arr["plan_name"] ='';
    //                 $arr["loan_amount"] =0;
    //                 $arr["issue_date"] ='';
    //                 $arr["instalment"] ='';
    //                 $arr["mode"] ='';
    //                 $arr["emi_paid"] ='';


    //                 if($type == "loan"){
    //                     //$memberloandata = Memberloans::where("id",$input["loan_id"])->first();

    //                     $memberloandata = Memberloans::select('id','applicant_id','account_number','approve_date','emi_option','emi_period','deposite_amount','amount','status','loan_type','associate_member_id','branch_id','created_at','approved_date', 'emi_amount','transfer_amount')
    //                     ->with(['loan' => function($q){ $q->select('id','name','loan_type'); }])
    //                     ->where('id',$input["loan_id"]);
    //                     $memberloandata = $memberloandata->first() ;
                        
    //                     $arr["account_number"] =$memberloandata->account_number;
    //                 // $arr["deno_amount"] =$memberloandata->emi_amount;
    //                     //$arr["maturity_date"] =$memberloandata->emi_amount;
    //                     $arr["deposit"] =$memberloandata->emi_amount;
    //                 // $arr["opening_date"] =$memberloandata->emi_amount;
    //                 //  $arr["due_amount"] =$memberloandata->emi_amount;
    //                     $arr["emi_amount"] =number_format((float)$memberloandata->emi_amount, 2, '.', '');
    //                     $arr["plan_name"] =$memberloandata['loan']->name;
    //                     $arr["loan_amount"] =number_format((float)$memberloandata->transfer_amount, 2, '.', '');
    //                     $arr["issue_date"] =date("d-m-Y", strtotime($memberloandata->approve_date));
    //                     $arr["instalment"] =$memberloandata->emi_period;
    //                     $arr["emi_paid"] =0;
    //                     $arr["mode"] ='';
    //                     if($memberloandata->emi_option==1)
    //                     {
    //                         $arr["mode"] ='Monthly';
    //                     }
    //                     if($memberloandata->emi_option==2)
    //                     {
    //                         $arr["mode"] ='Weekly';
    //                     }
    //                     if($memberloandata->emi_option==3)
    //                     {
    //                         $arr["mode"] ='Daily';
    //                     }
                        
    //                 }
    //                 if($type == "group_loan"){                    
    //                     //$grouploan = Grouploans::where("id",$input["loan_id"])->first();
    //                     $memberloandata = Grouploans::select('id','applicant_id','account_number','approve_date','emi_option','emi_period','deposite_amount','amount','status','loan_type','associate_member_id','branch_id','created_at','approved_date', 'emi_amount','transfer_amount')
    //                     ->with(['loan' => function($q){ $q->select('id','name','loan_type'); }])
    //                     ->where('id',$input["loan_id"]);
    //                     $memberloandata = $memberloandata->first();
                        
    //                     $arr["account_number"] =$memberloandata->account_number;
    //                 // $arr["deno_amount"] =$memberloandata->emi_amount;
    //                     //$arr["maturity_date"] =$memberloandata->emi_amount;
    //                     $arr["deposit"] =$memberloandata->emi_amount;
    //                     // $arr["opening_date"] =$memberloandata->emi_amount;
    //                 //  $arr["due_amount"] =$memberloandata->emi_amount;
    //                     $arr["emi_amount"] =number_format((float)$memberloandata->emi_amount, 2, '.', '');
    //                     $arr["plan_name"] =$memberloandata['loan']->name;
    //                     $arr["loan_amount"] =number_format((float)$memberloandata->transfer_amount, 2, '.', '');
    //                     $arr["issue_date"] =date("d-m-Y", strtotime($memberloandata->approve_date));
    //                     $arr["instalment"] =$memberloandata->emi_period;
    //                     $arr["emi_paid"] =0;
    //                     $arr["mode"] ='';
    //                     if($memberloandata->emi_option==1)
    //                     {
    //                         $arr["mode"] ='Monthly';
    //                     }
    //                     if($memberloandata->emi_option==2)
    //                     {
    //                         $arr["mode"] ='Weekly';
    //                     }
    //                     if($memberloandata->emi_option==3)
    //                     {
    //                         $arr["mode"] ='Daily';
    //                     }         
    //                 }
    //                 if($type == "renewal"){ 
                        
    //                     $memberloandata = Memberinvestments::where("id",$input["loan_id"])->first(); 

    //                     $currentDate = Carbon\Carbon::now(); 
    //                     $record = $this->getRecords($memberloandata,$currentDate);
    //                     $pendingEmiAMount=0 ;
    //                     if(isset($record['pendingEmiAMount']))
    //                     {
    //                         $pendingEmiAMount=$record['pendingEmiAMount'] ;
    //                         if($record['pendingEmiAMount']>0)
    //                         {
    //                             $pendingEmiAMount=0 ;
    //                         }
    //                     } 
    //                     $pendingEmiAMount=str_replace('-', '', $pendingEmiAMount);

    //                     $deposit = \App\Models\Daybook::where('is_deleted',0)->where('investment_id',$memberloandata->id)->whereIn('transaction_type', [2,4,15,16,17,18,20,21,22,23,24,25,26,27,28,29,30])->sum('deposit');
    //                     $withdrawal = \App\Models\Daybook::where('is_deleted',0)->where('investment_id',$memberloandata->id)->whereIn('transaction_type', [2,4,15,16,17,18,20,21,22,23,24,25,26,27,28,29,30])->sum('withdrawal');
    //                     $totalAmount  = $deposit -  $withdrawal;
                    
    //                     $arr["account_number"] =$memberloandata->account_number; 
    //                     $arr["deposit"] =number_format((float)$totalAmount, 2, '.', '');
    //                     $arr["opening_date"] =date("d-m-Y", strtotime($memberloandata->created_at)); 
    //                     $arr["deno_amount"] =number_format((float)$memberloandata->deposite_amount, 2, '.', '');
    //                     $plans = DB::table("plans")->where("id",$memberloandata->plan_id)->first();
    //                             if(isset($plans->name)){
    //                                 $arr["plan_name"] = $plans->name;
    //                             } else {
    //                                 $arr["plan_name"] = "";
    //                             } 
    //                     $arr["maturity_date"] =date("d-m-Y", strtotime($memberloandata->maturity_date)); 
    //                     $arr["due_amount"] =number_format((float)$pendingEmiAMount, 2, '.', '');
                    


    //                 }
    //                 if($type == "maturity"){ 
    //                     $memberloandata = Memberinvestments::where("id",$input["loan_id"])->first(); 
    //                     $currentDate = Carbon\Carbon::now(); 
    //                     $record = $this->getRecords($memberloandata,$currentDate);
    //                     $pendingEmiAMount=0 ;
    //                     if(isset($record['pendingEmiAMount']))
    //                     {
    //                         $pendingEmiAMount=$record['pendingEmiAMount'] ;
    //                         if($record['pendingEmiAMount']>0)
    //                         {
    //                             $pendingEmiAMount=0 ;
    //                         }
    //                     } 
    //                     $pendingEmiAMount=str_replace('-', '', $pendingEmiAMount);
                    
    //                     $deposit = \App\Models\Daybook::where('is_deleted',0)->where('investment_id',$memberloandata->id)->whereIn('transaction_type', [2,4,15,16,17,18,20,21,22,23,24,25,26,27,28,29,30])->sum('deposit');
    //                     $withdrawal = \App\Models\Daybook::where('is_deleted',0)->where('investment_id',$memberloandata->id)->whereIn('transaction_type', [2,4,15,16,17,18,20,21,22,23,24,25,26,27,28,29,30])->sum('withdrawal');
    //                     $totalAmount  = $deposit -  $withdrawal;

    //                     $arr["account_number"] =$memberloandata->account_number; 
    //                     $arr["deposit"] =number_format((float)$totalAmount, 2, '.', '');
    //                     $arr["opening_date"] =date("d-m-Y", strtotime($memberloandata->created_at)); 
    //                     $arr["deno_amount"] =number_format((float)$memberloandata->deposite_amount, 2, '.', '');
    //                     $plans = DB::table("plans")->where("id",$memberloandata->plan_id)->first();
    //                             if(isset($plans->name)){
    //                                 $arr["plan_name"] = $plans->name;
    //                             } else {
    //                                 $arr["plan_name"] = "";
    //                             } 
    //                     $arr["maturity_date"] =date("d-m-Y", strtotime($memberloandata->maturity_date)); 
    //                     $arr["due_amount"] =number_format((float)$pendingEmiAMount, 2, '.', '');

    //                 }

                        
    //                 $response["status"] = "Success";
    //                 $response["code"] = 200;
    //                 $response["messages"] = "Notification Details";
    //                 $response["data"] = $arr;
    //                 $response["current_balance"] = $current_balance;
    //             }
    //             else
    //             {
    //                 $response["status"] = "Error";
    //                 $response["code"] = 201;
    //                 $response["messages"] = 'API token mismatch!';
    //                 $response["data"] = array(); 
    //                 $response["current_balance"] = $current_balance;
    //             }
                
    //         } else {
    //             $response["status"] = "Error";
    //             $response["code"] = 201;
    //             $response["messages"] = "Enter Valid Member Id";
    //             $response["data"] = array();
    //             $response["current_balance"] = $current_balance;
    //         }
    //     } else {
    //         $response["status"] = "Error";
    //         $response["code"] = "201";
    //         $response["messages"] = "Input parameter missing";
    //         $response["data"] = array();
    //         $response["current_balance"] = $current_balance;
    //     }
    //     return response()->json($response);
    // } 

    public function getNotificationDetails(Request $request)
    {
        $response = array();

        $input = $request->all();
        $current_balance = 0.00;

        if (isset($input["member_id"]) && $input["member_id"] != "" && isset($input["type"]) && $input["type"] != "" && isset($input["loan_id"]) && $input["loan_id"] != "") {

            // Get Member ID
            //$member = Member::select('id','mobile_no')->where('member_id',$input["member_id"])->first();

            // $member = Member::with(['savingAccount_Custom' => function ($q) {
            //     $q->with('savingAccountBalance');
            // }])->select('id', 'member_id', 'mobile_no')->where('member_id', $input["member_id"])->first();
           
            
            // $balance =  $member['savingAccount_Custom']['savingAccountBalance']->sum('deposit') - $member['savingAccount_Custom']['savingAccountBalance']->sum('withdrawal');




            $member = Member::with(['savingAccount_Custom' => function ($q) {
                $q->with('savingAccountBalance');
            }])->select('id', 'member_id', 'mobile_no')->where('member_id', $input["member_id"])->first();
            
            if (isset($member['savingAccount_Custom']) && isset($member['savingAccount_Custom']['savingAccountBalance'])) {
                $depositSum = $member['savingAccount_Custom']['savingAccountBalance']->sum('deposit');
                $withdrawalSum = $member['savingAccount_Custom']['savingAccountBalance']->sum('withdrawal');
            
                $balance = $depositSum - $withdrawalSum;
            } else {
               
                $balance = 0; 
            }
            
            
            $current_balance  =  number_format((float) $balance, 2, '.', '');

 

            if (isset($member->id)) {
                $token = md5($member->member_id);
                if ($token == $request->token) {

                    $type = $input["type"];

                    $arr = array();
                    $arr["account_number"] = '';
                    $arr["deno_amount"] = 0;
                    $arr["maturity_date"] = '';
                    $arr["deposit"] = 0;
                    $arr["opening_date"] = '';
                    $arr["due_amount"] = 0;
                    $arr["emi_amount"] = 0;
                    $arr["plan_name"] = '';
                    $arr["loan_amount"] = 0;
                    $arr["issue_date"] = '';
                    $arr["instalment"] = '';
                    $arr["mode"] = '';
                    $arr["emi_paid"] = '';

                 
                    if ($type == "loan") {
                        //$memberloandata = Memberloans::where("id",$input["loan_id"])->first();

                        $memberloandata = Memberloans::with(['loan' => function ($q) {
                                $q->select('id', 'name', 'loan_type');
                            }])
                            ->with('memberCompany')
                            ->with('company')
                            ->with('member')
                            ->where('id', $input["loan_id"]);
                        $memberloandata = $memberloandata->first();

                        // if($memberloandata['company']['status'] == 1 ){
                        //     $is_transaction_available  = 1;
                        // } else{
                        //     $is_transaction_available  = 0;
                            
                        // }

// dd($memberloandata->memberCompany->savingAccountNew->transaction_status);

                        if (
                            isset($memberloandata['company']['status']) &&
                            $memberloandata['company']['status'] === 1 &&
                            isset($memberloandata->memberCompany->savingAccountNew->transaction_status) &&
                            $memberloandata->memberCompany->savingAccountNew->transaction_status == 1
                        ){
                            $is_transaction_available  = 1;
                        } else{
                            $is_transaction_available  = 0;
                            
                        }
// dd($memberloandata['company']['status'],$memberloandata);
                      
                            

                            $applicationOriginalDate = $memberloandata->approve_date;
                            $applicationDateTime = DateTime::createFromFormat('d/m/Y', $applicationOriginalDate);
                            if ($applicationDateTime !== false) {
                                $formattedDate = $applicationDateTime->format('d-m-Y');
                                
                            } else {
                                $formattedDate = 'N/A';
                            }
                           
                                // Retrive outstanding Amount
                                $outstandingAmount = $memberloandata
                                    ->getOutstanding()
                                    ->latest("emi_date")
                                    ->first()
                                    ? $memberloandata
                                        ->getOutstanding()
                                        ->latest("emi_date")
                                        ->first()->out_standing_amount
                                    : $memberloandata->amount;
                                // Ensure Outstanding Amount is not negative
                                $outstandingAmount = isset($outstandingAmount)
                                    ? ($outstandingAmount > 0
                                        ? $outstandingAmount
                                        : $memberloandata->amount)
                                    : $memberloandata->amount;
                                // Retrive Emi details
                                $emiDetail = $this->loanPaymentService->emiDetails($memberloandata->id, $memberloandata->loan_type);
                                
                                
                    
                              
                              
                                // Calculate lastEmi date
                                $lastEmidate = isset($emiDetail->emi_date)
                                    ? date("d/m/Y", strtotime($emiDetail->emi_date))
                                    : date("d/m/Y", strtotime($memberloandata->approve_date));
                    
                                // Calculate Closure Amount
                                $closerAmount = calculateCloserAmount(
                                    $outstandingAmount,
                                    $lastEmidate,
                                    $memberloandata->ROI,
                                    $memberloandata["loanBranch"]->state_id
                                );
                      
                            // Add closer key
                            $arr["closer_amount"] = (string)$closerAmount;
                           
                            $arr['is_company_active'] = $is_transaction_available;
                        $arr["account_number"] = $memberloandata->account_number;
                        // $arr["deno_amount"] =$memberloandata->emi_amount;
                        //$arr["maturity_date"] =$memberloandata->emi_amount;
                        $arr["deposit"] = $memberloandata->emi_amount;
                        // $arr["opening_date"] =$memberloandata->emi_amount;
                        // dd($memberloandata->due_amount);
                         $arr["due_amount"] =$memberloandata->due_amount;
                        $arr["emi_amount"] = number_format((float)$memberloandata->emi_amount, 2, '.', '');
                        $arr["plan_name"] = $memberloandata['loan']->name;
                        $arr["loan_amount"] = number_format((float)$memberloandata->amount, 2, '.', '');
                        $arr["issue_date"] = (string)$formattedDate;
                        
                        $arr["instalment"] = $memberloandata->emi_period;
                        $arr["company_id"] = $memberloandata->company_id;
                        $arr["accountNumber"] = $memberloandata->memberCompany->savingAccountNew->account_no ?? 0;
                        
                        $arr["totalAmount"]   = $memberloandata->memberCompany->savingAccountNew->savingAccountTransactionView->opening_balance ?? 0;

                        $count = \App\Models\LoanDayBooks::where('account_number', $memberloandata["account_number"])->where('loan_sub_type', 0)->where('is_deleted', 0)->sum('deposit');
                            $arr["emi_paid"] = $count;
                        $arr["mode"] = '';
                        if ($memberloandata->emi_option == 1) {
                            $arr["mode"] = 'Monthly';
                        }
                        if ($memberloandata->emi_option == 2) {
                            $arr["mode"] = 'Weekly';
                        }
                        if ($memberloandata->emi_option == 3) {
                            $arr["mode"] = 'Daily';
                        }
                    }
                    if ($type == "group_loan") {
                        //$grouploan = Grouploans::where("id",$input["loan_id"])->first();
                        $memberloandata = Grouploans::
                            with(['loan' => function ($q) {
                                $q->select('id', 'name', 'loan_type');
                            }])
                            ->with('company')
                            ->with('loanSavingAccount2')
                            ->where('id', $input["loan_id"]);
                        $memberloandata = $memberloandata->first();
                        // dd($memberloandata['company']->plans);

                        // dd($memberloandata->loanSavingAccount2->transaction_status);
                        if($memberloandata['company']->status == 1 && isset($memberloandata->loanSavingAccount2->transaction_status) && $memberloandata->loanSavingAccount2->transaction_status == 1 ){
                            $is_transaction_available  = 1;
                        } else{
                            $is_transaction_available  = 0;
                            
                        }


                            // Retrive outstanding Amount
                          
                                // Retrive outstanding Amount
                                $outstandingAmount = $memberloandata
                                    ->getOutstanding()
                                    ->latest("emi_date")
                                    ->first()
                                    ? $memberloandata
                                        ->getOutstanding()
                                        ->latest("emi_date")
                                        ->first()->out_standing_amount
                                    : $memberloandata->amount;
                                // Ensure Outstanding Amount is not negative
                                $outstandingAmount = isset($outstandingAmount)
                                    ? ($outstandingAmount > 0
                                        ? $outstandingAmount
                                        : $memberloandata->amount)
                                    : $memberloandata->amount;
                                // Retrive Emi details
                                $emiDetail = $this->loanPaymentService->emiDetails($memberloandata->id, $memberloandata->loan_type);
                                
                                
                    
                              
                              
                                // Calculate lastEmi date
                                $lastEmidate = isset($emiDetail->emi_date)
                                    ? date("d/m/Y", strtotime($emiDetail->emi_date))
                                    : date("d/m/Y", strtotime(convertDate($memberloandata->approve_date)));
                    
                                // Calculate Closure Amount
                                $closerAmount = calculateCloserAmount(
                                    $outstandingAmount,
                                    $lastEmidate,
                                    $memberloandata->ROI,
                                    $memberloandata["loanBranch"]->state_id
                                );
                      
                            // Add closer key
                            $arr["closer_amount"] = (string)$closerAmount;





                        $arr["account_number"] = $memberloandata->account_number;
                        // $arr["deno_amount"] =$memberloandata->emi_amount;
                        //$arr["maturity_date"] =$memberloandata->emi_amount;
                        $arr["deposit"] = $memberloandata->emi_amount;
                        // $arr["opening_date"] =$memberloandata->emi_amount;
                        //  $arr["due_amount"] =$memberloandata->emi_amount;
                        $arr["emi_amount"] = number_format((float)$memberloandata->emi_amount, 2, '.', '');
                        $arr["plan_name"] = $memberloandata['loan']->name;
                        $arr["loan_amount"] = number_format((float)$memberloandata->amount, 2, '.', '');
                        $arr["issue_date"] = date("d-m-Y", strtotime(convertDate($memberloandata->approve_date)));
                        $arr["due_amount"] = (string)$memberloandata->due_amount;

                        $arr["instalment"] = $memberloandata->emi_period;
                        $count = \App\Models\LoanDayBooks::where('account_number', $memberloandata->account_number)->where('loan_sub_type', 0)->where('is_deleted', 0)->sum('deposit');
                        $arr["emi_paid"] = $count;                   
                        // dd($memberloandata->loanSavingAccount2->account_no);
                        // ['loan_saving_account2']
                        // dd( $memberloandata->memberCompany->savingAccountNew->savingAccountTransactionView);
                        $arr["company_id"] = $memberloandata->company_id;
                        $arr["accountNumber"] = $memberloandata->loanSavingAccount2->account_no ?? 0;
                        $arr["totalAmount"]   = $memberloandata->memberCompany->savingAccountNew->savingAccountTransactionView->opening_balance ?? 0;
                        $arr['is_company_active'] = $is_transaction_available;
                        
                        

                        $arr["mode"] = '';
                        if ($memberloandata->emi_option == 1) {
                            $arr["mode"] = 'Monthly';
                        }
                        if ($memberloandata->emi_option == 2) {
                            $arr["mode"] = 'Weekly';
                        }
                        if ($memberloandata->emi_option == 3) {
                            $arr["mode"] = 'Daily';
                        }
                    }
                    if ($type == "investment_renewal") {
                        $memberloandata = Memberinvestments::where("id", $input["loan_id"])->first();

                        $currentDate = Carbon\Carbon::now();
                        $record = $this->getRecords($memberloandata, $currentDate);
                        $pendingEmiAMount = 0;
                        if (isset($record['pendingEmiAMount'])) {
                            $pendingEmiAMount = $record['pendingEmiAMount'];
                            if ($record['pendingEmiAMount'] > 0) {
                                $pendingEmiAMount = 0;
                            }
                        }
                        $pendingEmiAMount = str_replace('-', '', $pendingEmiAMount);

                        $deposit = \App\Models\Daybook::where('is_deleted', 0)->where('investment_id', $memberloandata->id)->whereIn('transaction_type', [2, 4, 15, 16, 17, 18, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30])->sum('deposit');
                        $withdrawal = \App\Models\Daybook::where('is_deleted', 0)->where('investment_id', $memberloandata->id)->whereIn('transaction_type', [2, 4, 15, 16, 17, 18, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30])->sum('withdrawal');
                        $totalAmount  = $deposit -  $withdrawal;
                        $arr["account_number"] = $memberloandata->account_number;
                        $arr["deposit"] = number_format((float)$totalAmount, 2, '.', '');
                        $arr["opening_date"] = date("d-m-Y", strtotime($memberloandata->created_at));
                        $arr["deno_amount"] = number_format((float)$memberloandata->deposite_amount, 2, '.', '');
                        $plans = DB::table("plans")->where("id", $memberloandata->plan_id)->first();
                        if (isset($plans->name)) {
                            $arr["plan_name"] = $plans->name;
                        } else {
                            $arr["plan_name"] = "";
                        }
                        $arr["maturity_date"] = date("d-m-Y", strtotime($memberloandata->maturity_date));
                        $currentDate = Carbon\Carbon::now();
                        $record = $this->getRecords($memberloandata, $currentDate);

                            // $currentDate = Carbon\Carbon::now();
                            $pendingEmiAmount = 0;
                    
                            if (isset($record['pendingEmiAmount'])) {
                                $pendingEmiAmount = $record['pendingEmiAmount'];
                                if ($pendingEmiAmount > 0) {
                                    $pendingEmiAmount = 0;
                                }
                            }
                    
                            $pendingEmiAmount = str_replace('-', '', $pendingEmiAmount);
                            $arr["due_amount"] = number_format((float) $pendingEmiAmount, 2, '.', '');
                    }
                    if ($type == "maturity") {
                        $memberloandata = Memberinvestments::where("id", $input["loan_id"])->first();
                        $currentDate = Carbon\Carbon::now();
                        $record = $this->getRecords($memberloandata, $currentDate);
                        $pendingEmiAMount = 0;
                        if (isset($record['pendingEmiAMount'])) {
                            $pendingEmiAMount = $record['pendingEmiAMount'];
                            if ($record['pendingEmiAMount'] > 0) {
                                $pendingEmiAMount = 0;
                            }
                        }
                        $pendingEmiAMount = str_replace('-', '', $pendingEmiAMount);

                        $deposit = \App\Models\Daybook::where('is_deleted', 0)->where('investment_id', $memberloandata->id)->whereIn('transaction_type', [2, 4, 15, 16, 17, 18, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30])->sum('deposit');
                        $withdrawal = \App\Models\Daybook::where('is_deleted', 0)->where('investment_id', $memberloandata->id)->whereIn('transaction_type', [2, 4, 15, 16, 17, 18, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30])->sum('withdrawal');
                        $totalAmount  = $deposit -  $withdrawal;

                        $arr["account_number"] = $memberloandata->account_number;
                        $arr["deposit"] = number_format((float)$totalAmount, 2, '.', '');
                        $arr["opening_date"] = date("d-m-Y", strtotime($memberloandata->created_at));
                        $arr["deno_amount"] = number_format((float)$memberloandata->deposite_amount, 2, '.', '');
                        $plans = DB::table("plans")->where("id", $memberloandata->plan_id)->first();
                        if (isset($plans->name)) {
                            $arr["plan_name"] = $plans->name;
                        } else {
                            $arr["plan_name"] = "";
                        }
                        $arr["maturity_date"] = date("d-m-Y", strtotime($memberloandata->maturity_date));
                                                $arr["due_amount"] = number_format((float)$pendingEmiAMount, 2, '.', '');
                        $currentDate = Carbon\Carbon::now();
                        $currentDate = date("Y-m-d", strtotime(convertDate($currentDate)));
                            $record = $this->getRecords($memberloandata, $currentDate);
                            $pendingEmiAmount = 0;
                    
                            if (isset($record['pendingEmiAmount'])) {
                                $pendingEmiAmount = $record['pendingEmiAmount'];
                                if ($pendingEmiAmount > 0) {
                                    $pendingEmiAmount = 0;
                                }
                            }
                    
                            $pendingEmiAmount = str_replace('-', '', $pendingEmiAmount);
                            $arr["due_amount"] = number_format((float) $pendingEmiAmount, 2, '.', '');                        

                    }


                    $response["status"] = "Success";
                    $response["code"] = 200;
                    $response["messages"] = "Notification Details";
                    $response["data"] = $arr;
                    $response["minAmount"] = '500';

                    $response["current_balance"] = $current_balance;
                } else {
                    $response["status"] = "Error";
                    $response["code"] = 201;
                    $response["messages"] = 'API token mismatch!';
                    $response["data"] = array();
                    $response["current_balance"] = $current_balance;
                }
            } else {
                $response["status"] = "Error";
                $response["code"] = 201;
                $response["messages"] = "Enter Valid Member Id";
                $response["data"] = array();
                $response["current_balance"] = $current_balance;
            }
        } else {
            $response["status"] = "Error";
            $response["code"] = "201";
            $response["messages"] = "Input parameter missing";
            $response["data"] = array();
            $response["current_balance"] = $current_balance;
        }
        return response()->json($response);
    }

    


    // public static function getRecords($dailyDepositeRecord,$currentDate)
    // {
    //        //print_r($dailyDepositeRecord['plan_id']);die;
    
    //         switch ($dailyDepositeRecord['plan_id']) {
    //             case '7':
    //                 $pendingEmiAMount=0;
    //                 $pendingEmi=0;
    //                 $investCreatedDate =strtotime($dailyDepositeRecord['created_at']);
                   
    //                 $CURRENTDATE = strtotime($currentDate);

    //                 $totalBetweendays = abs($investCreatedDate-$CURRENTDATE);
    //                 $totalBetweendays = ceil(floatval($totalBetweendays/86400));
    //                 $totalAmount = ($totalBetweendays + 1) * $dailyDepositeRecord['deposite_amount'];
    //                 $getRenewalReceivedAmount= \App\Models\Daybook::whereIn('transaction_type',[2,4])->where('account_no',$dailyDepositeRecord['account_number'])->sum('deposit');
                   
    //                 if($getRenewalReceivedAmount != $totalAmount)
    //                 {
    //                     $pendingEmiAMount = $getRenewalReceivedAmount - $totalAmount ;
    //                     $pendingEmi =  $pendingEmiAMount/ $dailyDepositeRecord['deposite_amount'];
    //                 } 
    //                 else{
    //                     $pendingEmiAMount = 0;
    //                     $pendingEmi =  0;
    //                 }
    //                 return ['pendingEmiAMount'=>$pendingEmiAMount,'pendingEmi'=>$pendingEmi];
    //                 break;
    //             case '2':
    //             case '3':
    //             case '5':
    //             case '7':
    //             case '10':
    //             case '11':
    //                 $pendingEmiAMount=0;
    //                 $pendingEmi=0;
    //                 $investCreatedDate = Carbon\Carbon::parse($dailyDepositeRecord['created_at']);
    //                 $totalBetweenmonth = $investCreatedDate->diffInMonths($currentDate);
    //                 $totalAmount = ($totalBetweenmonth + 1) *  $dailyDepositeRecord['deposite_amount'];
    //                 $getRenewalReceivedAmount= \App\Models\Daybook::whereIn('transaction_type',[2,4])->where('account_no',$dailyDepositeRecord['account_number'])->sum('deposit');


    //                 if($getRenewalReceivedAmount != $totalAmount)
    //                 {
    //                     $pendingEmiAMount = $getRenewalReceivedAmount - $totalAmount ;

    //                     $pendingEmi =  $pendingEmiAMount/ $dailyDepositeRecord['deposite_amount'];
    //                 } 
    //                  else{
    //                     $pendingEmiAMount = 0;
    //                     $pendingEmi =  0;
    //                 }
    //                 return ['pendingEmiAMount'=>$pendingEmiAMount,'pendingEmi'=>$pendingEmi];
                
    //                 break;
    //         }
      
    // }
   
    

    // public function getAccountDetails(Request $request)
    // {
    //     $response = array();

    //     $input = $request->all();
    //     if(isset($input["member_id"]) && $input["member_id"]!="" && isset($input["type"]) && $input["type"]!=""){
            
    //         // Get Member ID
    //         $member = Member::with(['savingAccount_Custom'=>function($q){
    //             $q->with('savingAccountBalance');
    //         }])->select('id','member_id')->where('member_id',$input["member_id"])->first();
    //         $balance = 0;
    //         if(isset($member['savingAccount_Custom']['savingAccountBalance']))
    //         {
    //             $balance =  $member['savingAccount_Custom']['savingAccountBalance']->sum('deposit') - $member['savingAccount_Custom']['savingAccountBalance']->sum('withdrawal');
    //         }
            
    //         $balance  =  number_format((float) $balance, 2, '.', '');
    //         if(isset($member->id)){

    //             $type = $input["type"];

    //             if($type == "ssb"){

    //                 // Get SSB Account ID
    //                 $savingAccountDetails = SavingAccount::where('member_id',$member->id)->first();

    //                 if(isset($savingAccountDetails->id)){

    //                     $arr = array("account_number" => $savingAccountDetails->account_no,
    //                                  "plan" => "Saving Account"
    //                                 );

    //                     //total deposit
    //                     $deposit = SavingAccountTranscation::where("saving_account_id",$savingAccountDetails->id)->where("type","2")->sum("deposit");         
    //                     $arr["deposit"] = $balance;

    //                     // get Investment details
    //                     $memberInvestment = Memberinvestments::where("account_number",$savingAccountDetails->account_no)->first();
                        
    //                     if(isset($memberInvestment->deposite_amount)){
    //                         $arr["deno_amount"] = number_format((float)$memberInvestment->deposite_amount, 2, '.', '');
    //                     } else {
    //                         $arr["deno_amount"] = 0;
    //                     }

    //                     if(isset($memberInvestment->created_at)){
    //                         $arr["opening_date"] = $newDate = date("d-m-Y", strtotime($memberInvestment->created_at));
    //                     } else {
    //                         $arr["opening_date"] = "";
    //                     }

    //                     if(isset($memberInvestment->id)){
    //                         $arr["investment_id"] = (string)$memberInvestment->id;
    //                     } else {
    //                         $arr["investment_id"] = "";
    //                     }
                       
    //                     $arr["maturity_date"] = "";


    //                     $response["status"] = "Success";
    //                     $response["currentBalance"] = (string)$balance;
    //                     $response["code"] = 200;
    //                     $response["messages"] = "Data";
    //                     $response["data"] = array($arr);

    //                 }

    //             }

    //             if($type == "deposit"){
                    
    //                 $investmentArr = array();
    //                  // get Investment details
    //                 $memberInvestment = Memberinvestments::where("member_id",$member->id)->where("plan_id","!=",1)->where("is_mature",1)->get();

    //                 if(count($memberInvestment) > 0){

    //                     $memberInvestment = $memberInvestment->toArray();

    //                     for($w=0;$w<count($memberInvestment);$w++){
    //                         // $totalBalance = \App\Models\Daybook::where('is_deleted',0)->where('account_no',$memberInvestment[$w]["account_number"]);
    //                         // $totalAmount  = $totalBalance->sum('deposit') -  $totalBalance->sum('withdrawal');
    //                         $arr = array("investment_id" => (string)$memberInvestment[$w]["id"],
    //                                     "account_number" => $memberInvestment[$w]["account_number"],
    //                                     "deno_amount" => number_format((float)$memberInvestment[$w]["deposite_amount"], 2, '.', ''),
    //                                     "maturity_date" => $memberInvestment[$w]["maturity_date"],
    //                                     "deposit" => number_format((float)$memberInvestment[$w]["current_balance"], 2, '.', ''),
    //                                     );

    //                         if($memberInvestment[$w]["maturity_date"]!= null){
    //                             $arr["maturity_date"] = date("d-m-Y", strtotime($memberInvestment[$w]["maturity_date"]));
    //                         }
                            
    //                         if(isset($memberInvestment[$w]["created_at"])){
    //                             $arr["opening_date"] =  date("d-m-Y", strtotime($memberInvestment[$w]["created_at"]));
    //                         } else {
    //                             $arr["opening_date"] = "";
    //                         }
                            
    //                         // Get Plan Name
    //                         $plans = DB::table("plans")->where("id",$memberInvestment[$w]["plan_id"])->first();
    //                         if(isset($plans->name)){
    //                             $arr["plan"] = $plans->name;
    //                         } else {
    //                             $arr["plan"] = "";
    //                         }
    //                         $currentDate = Carbon\Carbon::now(); 
    //                         $record = $this->getRecords($memberInvestment[$w],$currentDate);
    //                         $pendingEmiAMount=0 ;
    //                         if(isset($record['pendingEmiAMount']))
    //                         {
    //                             $pendingEmiAMount=$record['pendingEmiAMount'] ;
    //                             if($record['pendingEmiAMount']>0)
    //                             {
    //                                 $pendingEmiAMount=0 ;
    //                             }
    //                         } 
    //                         $pendingEmiAMount=str_replace('-', '', $pendingEmiAMount);
    //                         $arr["due_amount"] =number_format((float)$pendingEmiAMount, 2, '.', '');

    //                        array_push($investmentArr,$arr);
    //                     }
    //                 }

    //                 $response["status"] = "Success";
    //                 $response["currentBalance"] = (string)$balance;
    //                 $response["code"] = 200;
    //                 $response["messages"] = "Data";
    //                 $response["data"] = $investmentArr;
                    
    //             }


    //             if($type == "loan"){

    //                 $investmentArr = array();

    //                 $memberloan = Memberloans::where("applicant_id",$member->id)->whereIN('status',['0','1','4'])->get();

    //                 if(count($memberloan) > 0){
    //                     $memberloan = $memberloan->toArray();

    //                     for($w=0;$w<count($memberloan);$w++){
    //                         $arr = array("account_number" => $memberloan[$w]["account_number"],
    //                                     "emi_amount" => $memberloan[$w]["emi_amount"],
    //                                     "loan_id" => $memberloan[$w]["id"],
    //                                     "plan_name" => ""
    //                                     );

    //                         if($memberloan[$w]["loan_type"] == "1"){
    //                             $arr["plan_name"] = "Personal loan";
    //                         } else if($memberloan[$w]["loan_type"] == "2"){
    //                             $arr["plan_name"] = "Staff Loan";
    //                         } else if($memberloan[$w]["loan_type"] == "3"){
    //                             $arr["plan_name"] = "Group loan";
    //                         } else if($memberloan[$w]["loan_type"] == "4"){
    //                             $arr["plan_name"] = "Loan against Investment plan";
    //                         }          
    //                             $arr["loan_status"] =(string)$memberloan[$w]["status"];
    //                             $arr["investment_id"] =(string)$memberloan[$w]["id"];  
    //                             $arr["loan_amount"] =(string)number_format((float)$memberloan[$w]["transfer_amount"], 2, '.', '');
    //                             $arr["issue_date"] =(string)date("d-m-Y", strtotime($memberloan[$w]["approve_date"]));
    //                             $arr["instalment"] =(string)$memberloan[$w]["emi_period"];
    //                             $arr["mode"] ='';
    //                             if($memberloan[$w]["emi_option"]==1)
    //                             {
    //                                 $arr["mode"] ='Monthly';
    //                             }
    //                             if($memberloan[$w]["emi_option"]==2)
    //                             {
    //                                 $arr["mode"] ='Weekly';
    //                             }
    //                             if($memberloan[$w]["emi_option"]==3)
    //                             {
    //                                 $arr["mode"] ='Daily';
    //                             }
                                
    //                             $arr["emi_paid"] =0;
    //                             $arr["is_running_loan"] =0;
    //                             if($memberloan[$w]["status"]==4)
    //                             {
    //                                 $arr["is_running_loan"] =1;
                                    
    //                             }   
    //                             else{
    //                                 $arr["loan_amount"] =(string)number_format((float)$grouploan[$w]["amount"] , 2, '.', '');
    //                                 $arr["application_date"] =(string)date("d-m-Y", strtotime($grouploan[$w]["created_at"]));
    //                             }                            
                                
    //                             $arr["type"] ='loan';
                             

    //                         array_push($investmentArr,$arr);
    //                     }
    //                 }

    //                 $response["status"] = "Success";
    //                 $response["currentBalance"] = (string)$balance;
    //                 $response["code"] = 200;
    //                 $response["messages"] = "Data";
    //                 $response["data"] = $investmentArr;
    //             }


                
    //             if($type == "group_loan"){

    //                 $investmentArr = array();

    //                 $grouploan = Grouploans::where("member_id",$member->id)->whereIN('status',['0','1','4'])->get();

    //                 if(count($grouploan) > 0){
    //                     $grouploan = $grouploan->toArray();

    //                     for($w=0;$w<count($grouploan);$w++){
    //                         $arr = array("account_number" => $grouploan[$w]["account_number"],
    //                                     "emi_amount" => $grouploan[$w]["emi_amount"],
    //                                     "loan_id" => $grouploan[$w]["id"],
    //                                     "plan_name" => ""
    //                                     );
                                        
    //                         if($grouploan[$w]["loan_type"] == "1"){
    //                             $arr["plan_name"] = "Personal loan";
    //                         } else if($grouploan[$w]["loan_type"] == "2"){
    //                             $arr["plan_name"] = "Staff Loan";
    //                         } else if($grouploan[$w]["loan_type"] == "3"){
    //                             $arr["plan_name"] = "Group loan";
    //                         } else if($grouploan[$w]["loan_type"] == "4"){
    //                             $arr["plan_name"] = "Loan against Investment plan";
    //                         } 
    //                         $arr["loan_status"] =(string)$grouploan[$w]["status"]; 
    //                         $arr["investment_id"] =(string)$grouploan[$w]["id"];           

                             
    //                             $arr["loan_amount"] =(string)number_format((float)$grouploan[$w]["transfer_amount"], 2, '.', '');
    //                             $arr["issue_date"] =(string)date("d-m-Y", strtotime($grouploan[$w]["approve_date"]));
    //                             $arr["instalment"] =(string)$grouploan[$w]["emi_period"];
    //                             $arr["mode"] ='';
    //                             if($grouploan[$w]["emi_option"]==1)
    //                             {
    //                                 $arr["mode"] ='Monthly';
    //                             }
    //                             if($grouploan[$w]["emi_option"]==2)
    //                             {
    //                                 $arr["mode"] ='Weekly';
    //                             }
    //                             if($grouploan[$w]["emi_option"]==3)
    //                             {
    //                                 $arr["mode"] ='Daily';
    //                             }
                                
    //                             $arr["emi_paid"] =0;
    //                             $arr["is_running_loan"] =0;
    //                             if($grouploan[$w]["status"]==4)
    //                             {
    //                                 $arr["is_running_loan"] =1;
    //                             } 
    //                             else{
    //                                 $arr["loan_amount"] =(string)number_format((float)$grouploan[$w]["amount"], 2, '.', '');;
    //                                 $arr["application_date"] =(string)date("d-m-Y", strtotime($grouploan[$w]["created_at"]));
    //                             }                            
                                
    //                             $arr["type"] ='group_loan';

    //                         array_push($investmentArr,$arr);
    //                     }
    //                 }

    //                 $response["status"] = "Success";
    //                 $response["currentBalance"] = (string)$balance;
    //                 $response["code"] = 200;
    //                 $response["messages"] = "Data";
    //                 $response["data"] = $investmentArr;
    //             }

    //         } else {
    //             $response["status"] = "Error";
    //             $response["code"] = 201;
    //             $response["messages"] = "Enter Valid Member Id";
    //             $response["data"] = array();
    //         }

    //     } else {
    //         $response["status"] = "Error";
    //         $response["code"] = 201;
    //         $response["messages"] = "Input parameter missing";
    //         $response["data"] = array();
    //     }

    //     return response()->json($response);
    // }




    // public function sendLoanOtp(Request $request)
    // {
    //     $response = array();

    //     $input = $request->all();

    //     if(isset($input["member_id"]) && $input["member_id"]!="" && isset($input["type"]) && $input["type"]!=""){
            
    //         // Get Member ID
    //         $member = Member::select('id','mobile_no')->where('member_id',$input["member_id"])->first();

    //         if(isset($member->id)){
                
    //             $type = $input["type"];

    //             $contacts = $member->mobile_no;

    //             //$otp = rand(1000,9999);
    //             $otp = 1234;

    //             if($type == "loan"){
    //                 $sms_text = 'Your Loan OTP in '.urlencode($otp).' From Sammradh Bestwin Microfinance';
    //             } else {
    //                 $sms_text = 'Your Group Loan OTP in '.urlencode($otp).' From Sammradh Bestwin Microfinance';
    //             }
    //             /*
    //             $ch = curl_init();
    //             curl_setopt($ch, CURLOPT_URL, "http://sms.kutility.com/app/smsapi/index.php");
    //             curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    //             curl_setopt($ch, CURLOPT_POST, 1);
    //             curl_setopt($ch, CURLOPT_POSTFIELDS, "key=26059BB05DCA39&routeid=7&campaign=11207&type=text&contacts=" . $contacts . "&senderid=SBMFAP&msg=" . $sms_text."&template_id=1207165881681305418");
    //             $response = curl_exec($ch);
    //             curl_close($ch);
    //             */

    //             $response["status"] = "Success";
    //             $response["code"] = 200;
    //             $response["messages"] = "OTP send successfully!";
    //             $response["otp"] = $otp;

    //         } else {
    //             $response["status"] = "Error";
    //             $response["code"] = 201;
    //             $response["messages"] = "Enter Valid Member Id";
    //             $response["otp"] = "";
    //         }
    //     } else {
    //         $response["status"] = "Error";
    //         $response["code"] = "201";
    //         $response["messages"] = "Input parameter missing";
    //         $response["otp"] = "";
    //     }
    //     return response()->json($response);
    // } 








    // public function submitLoanPayment(Request $request)
    // {
    //     $response = array();

    //     $request = $request->all();
    //     $entryTime = date("H:i:s");
    //     if(isset($request["member_id"]) && $request["member_id"]!="" && isset($request["type"]) && $request["type"]!="" && isset($request["emi_amount"]) && $request["emi_amount"]!="" && isset($request["loan_id"]) && $request["loan_id"]!="" && isset($request["payment_mode"]) && $request["payment_mode"]!=""){
            
    //         // Get Member ID
    //         $member = Member::select('id','mobile_no','associate_code')->where('member_id',$request["member_id"])->first();

    //         if(isset($member->id)){

    //             $associate_no = $request["member_id"];
    //             $loan_id = $request["loan_id"];
    //             $loanId = $request["loan_id"];
    //             $emi_amount = $request["emi_amount"];
    //             $request['deposite_amount'] = $request["emi_amount"];
    //             $payment_modesss = $request["payment_mode"];
    //             $type = $request["type"];

    //             $request["loan_associate_code"] = $member->associate_code;
                
    //             $member = Member::select('id', 'associate_app_status', 'associate_id')->where('member_id', $request["member_id"])->where('associate_status', 1)->where('is_block', 0)->count();
    //             $member2 = Member::select('id', 'associate_app_status', 'associate_id', 'branch_id', 'first_name', 'last_name')->where('member_id', $request["member_id"])->where('associate_status', 1)->where('is_block', 0)->first();
    //             // dd($member);
    //             $userid = $member2->userid;
                
    //             $memberloan = Memberloans::with(['loanBranch','loan'])->where('id', $loanId)->first();
    //             $branchId = $memberloan->branch_id;
    //             $request['branch'] = $memberloan->branch_id;
    //             $request['associate_member_id'] = $memberloan->associate_member_id;
                

    //             $memberloanid = $memberloan->id;
    //             $member_loan_branch_id = $member2->branch_id;
    //             $membertype = $memberloan->loan_type;
    //             $mLoan = $memberloan;
    //             $stateid = getBranchState($mLoan['loanBranch']->name);
    //             $request['loan_associate_name'] = $member2->first_name . ' ' . $member2->last_name;

    //             $globaldate = checkMonthAvailability(date('d'), date('m'), date('Y'), $mLoan['loanBranch']->state_id);
    //             Session::put('created_at', date("Y-m-d " . $entryTime . "", strtotime(convertDate($globaldate))));

    //             //$globaldate = "12-11-2022";
    //                 $entryTime = date("H:i:s");
    //                 $ssbAccountDetails = SavingAccount::with('ssbMember')->where('member_id', $member2->id)->first();
    //                 $ssbAccountDetailsDeposit = SavingAccountTranscation::where('saving_account_id', $ssbAccountDetails->id)->sum('deposit');
    //                 $ssbAccountDetailsWithdrawal = SavingAccountTranscation::where('saving_account_id', $ssbAccountDetails->id)->sum('withdrawal');
    //                 $ssbAmont = $ssbAccountDetailsDeposit-$ssbAccountDetailsWithdrawal;

    //                 // check Saving Account is Exist or not
    //                     if ($ssbAmont < $request["emi_amount"]) {

    //                         $response["status"] = "Success";
    //                         $response["code"] = 201;
    //                         $response["messages"] = "Insufficient Balance!";
    //                         $response["data"] = array();
    //                         return response()->json($response);

    //                     } else {

    //                         $globaldate = date('Y-m-d', strtotime(convertDate($globaldate)));

    //                         $LoanCreatedDate = date('Y-m-d', strtotime($mLoan->approve_date));
    //                         $LoanCreatedYear = date('Y', strtotime($mLoan->approve_date));
    //                         $LoanCreatedMonth = date('m', strtotime($mLoan->approve_date));
    //                         $LoanCreateDate = date('d', strtotime($mLoan->approve_date));
    //                         $currentDate = date('Y-m-d');
    //                         $CurrentDate = date('d');
    //                         $CurrentDateYear = date('Y');
    //                         $CurrentDateMonth = date('m');


    //                         $applicationDate = date('Y-m-d', strtotime(convertDate($globaldate)));
    //                         $applicationCurrentDate = date('d', strtotime(convertDate($globaldate)));
    //                         $applicationCurrentDateYear = date('Y', strtotime(convertDate($globaldate)));
    //                         $applicationCurrentDateMonth = date('m', strtotime(convertDate($globaldate)));


    //                         if ($mLoan->emi_option == 1) { //Month

    //                             $daysDiff = (($CurrentDateYear - $LoanCreatedYear) * 12) + ($CurrentDateMonth - $LoanCreatedMonth);
    //                             $daysDiff2 = today()->diffInDays($LoanCreatedDate);
    //                             $nextEmiDates = $this->nextEmiDates($daysDiff, $LoanCreatedDate);
    //                             $nextEmiDates2 = $this->nextEmiDatesDays($daysDiff2, $LoanCreatedDate);
    //                             $nextEmiDates3 = $this->nextEmiDates($mLoan->emi_period, $LoanCreatedDate);
    //                         }
    //                         if ($mLoan->emi_option == 2) { //Week
    //                             $daysDiff = today()->diffInDays($LoanCreatedDate);
    //                             $daysDiff = $daysDiff / 7;
    //                             $nextEmiDates2 = $this->nextEmiDatesDays($daysDiff, $LoanCreatedDate);
    //                             $nextEmiDates = $this->nextEmiDatesWeek($daysDiff, $LoanCreatedDate);
    //                         }
    //                         if ($mLoan->emi_option == 3) {  //Days
    //                             $daysDiff = today()->diffInDays($LoanCreatedDate);
    //                             $nextEmiDates = $this->nextEmiDatesDays($daysDiff, $LoanCreatedDate);
    //                         }

    //                         $totalDayInterest = 0;
    //                         $totalDailyInterest = 0;
    //                         $newApplicationDate = explode('-', $applicationDate);
    //                         $StartnewDAte = $applicationCurrentDateYear . '-' . $applicationCurrentDateMonth . '-01';
    //                         $EndnewDAte = $applicationCurrentDateYear . '-' . $applicationCurrentDateMonth . '-31';
    //                         $dailyoutstandingAmount = 0;

    //                         $lastOutstanding = LoanEmisNew::where('loan_id', $mLoan->id)->where('loan_type', $mLoan->loan_type)->where('is_deleted', '0')->orderBy('id', 'desc')->first();

    //                         $lastOutstandingDate = LoanEmisNew::where('loan_id', $mLoan->id)->where('loan_type', $mLoan->loan_type)->where('is_deleted', '0')->pluck('emi_date')->toArray();
    //                         $newDate = array();
    //                         $deposit = $emi_amount;
    //                         if (isset($lastOutstanding->out_standing_amount)) {
    //                             $checkDateMonth = date('m', strtotime($lastOutstanding->emi_date));
    //                             $checkDateYear = date('Y', strtotime($lastOutstanding->emi_date));
    //                             $newstartDate = $checkDateYear . '-' .  $checkDateMonth . '-01';
    //                             $newEndDate = $checkDateYear . '-' .  $checkDateMonth . '-31';

    //                             $gapDayes = Carbon\Carbon::parse($lastOutstanding->emi_date)->diff( Carbon\Carbon::parse($applicationDate))->format('%a');


    //                             $lastOutstanding2 = LoanEmisNew::where('loan_id', $mLoan->id)->where('loan_type', $mLoan->loan_type)->whereBetween('emi_date', [$newstartDate, $newEndDate])->where('is_deleted', '0')->sum('out_standing_amount');
    //                             $roi = ((($mLoan->ROI) / 365) * $lastOutstanding->out_standing_amount) / 100;
    //                             if ($mLoan->emi_option == 1 || $mLoan->emi_option == 2) {
    //                                 $roi = $roi * $gapDayes;
    //                                 $principal_amount = $deposit - $roi;
    //                                 if (!array_key_exists($applicationCurrentDate . '_' . $applicationCurrentDateMonth . '_' . $applicationCurrentDateYear, $nextEmiDates)) {
    //                                     $outstandingAmount = ($lastOutstanding->out_standing_amount - $deposit);
    //                                 } else {
    //                                     $preDate = current($nextEmiDates);
    //                                     $oldDate = $nextEmiDates[$applicationCurrentDate . '_' . $applicationCurrentDateMonth . '_' . $applicationCurrentDateYear];
    //                                     if ($mLoan->emi_option == 1) {
    //                                         $previousDate =  Carbon\Carbon::parse($oldDate)->subMonth(1);
    //                                     }
    //                                     if ($mLoan->emi_option == 2) {
    //                                         $previousDate =  Carbon\Carbon::parse($oldDate)->subDays(7);
    //                                     }
    //                                     $pDate = date('Y-m-d', strtotime("+1 day", strtotime($previousDate)));
    //                                     if ($preDate == $applicationDate) {
    //                                         $aqmount = LoanEmisNew::where('loan_id', $mLoan->id)->whereBetween('emi_date', [$LoanCreatedDate, $applicationDate])->where('is_deleted', '0')->sum('roi_amount');
    //                                     } else {
    //                                         $aqmount = LoanEmisNew::where('loan_id', $mLoan->id)->whereBetween('emi_date', [$pDate, $applicationDate])->where('is_deleted', '0')->sum('roi_amount');
    //                                     }
    //                                     if ($aqmount > 0) {
    //                                         $outstandingAmount = ($lastOutstanding->out_standing_amount - $deposit + $roi   + $aqmount);
    //                                     } else {
    //                                         $outstandingAmount = ($lastOutstanding->out_standing_amount - $deposit + $roi);
    //                                     }
    //                                 }
    //                                 $dailyoutstandingAmount = $outstandingAmount + $roi;
    //                             } else {
    //                                 $principal_amount = $deposit - $roi;
    //                                 $outstandingAmount = ($lastOutstanding->out_standing_amount - $principal_amount);
    //                             }
    //                             $deposit =  $request['deposite_amount'];
    //                         } else {
    //                             $roi = ((($mLoan->ROI) / 365) * $mLoan->amount) / 100;
    //                             $gapDayes =  Carbon\Carbon::parse($mLoan->approve_date)->diff(Carbon\Carbon::parse($applicationDate))->format('%a');
    //                             if ($mLoan->emi_option == 1 || $mLoan->emi_option == 2) //Monthly
    //                             {
    //                                 $roi = $roi * $gapDayes;
    //                                 $principal_amount = $deposit - $roi;
    //                                 if (!array_key_exists($applicationCurrentDate . '_' . $applicationCurrentDateMonth . '_' . $applicationCurrentDateYear, $nextEmiDates)) {
    //                                     $outstandingAmount = ($mLoan->amount - $deposit);
    //                                 } else {
    //                                     $outstandingAmount = ($mLoan->amount - $deposit + $roi);
    //                                 }
    //                                 $dailyoutstandingAmount = $outstandingAmount + $roi;
    //                             } else {
    //                                 $principal_amount = $deposit - $roi;
    //                                 $outstandingAmount = ($mLoan->amount - $principal_amount);
    //                             }
    //                             $deposit =  $request['deposite_amount'];
    //                             $dailyoutstandingAmount = $mLoan->amount + $roi;
    //                         }


    //                         $amountArraySsb = array(
    //                             '1' => $request['deposite_amount']
    //                         );
    //                         if (isset(($ssbAccountDetails['ssbMember']))) {
    //                             $amount_deposit_by_name = $ssbAccountDetails['ssbMember']->first_name . ' ' . $ssbAccountDetails['ssbMember']->last_name;
    //                         } else {
    //                             $amount_deposit_by_name = NULL;
    //                         }

    //                         $dueAmount = $mLoan->due_amount - round($principal_amount);
    //                         $mlResult = Memberloans::find($request['loan_id']);
    //                         $lData['credit_amount'] = $mLoan->credit_amount + $principal_amount;
    //                         $lData['due_amount'] = $dueAmount;


    //                         $lData['received_emi_amount'] = $mLoan->received_emi_amount + $request['deposite_amount'];

    //                         $mlResult->update($lData);
    //                         // add log
    //                         $postData = $_POST;
    //                         $enData = array(
    //                             "post_data" => $postData,
    //                             "lData" => $lData
    //                         );

    //                         $encodeDate = json_encode($enData);
    //                         // $arrs = array(
    //                         //     "load_id" => $loan_id,
    //                         //     "type" => "7",
    //                         //     "account_head_id" => 0,
    //                         //     "user_id" => $member2->id,
    //                         //     "message" => "Loan Recovery   - Loan EMI payment",
    //                         //     "data" => $encodeDate
    //                         // );
    //                         // DB::table('user_log')->insert($arrs);

    //                         // end log

    //                             $cheque_dd_no = NULL;
    //                             $online_payment_id = NULL;
    //                             $online_payment_by = NULL;
    //                             $bank_name = NULL;
    //                             $cheque_date = NULL;
    //                             $account_number = NULL;
    //                             $paymentMode = 4;
    //                             $ssbpaymentMode = 3;
    //                             $paymentDate = date("Y-m-d " . $entryTime . "", strtotime(convertDate($globaldate)));
    //                             // $record1 = SavingAccountTranscation::where('account_no', $ssbAccountDetails->account_no)
    //                             //     ->whereDate('created_at', '<=', date("Y-m-d", strtotime(convertDate($globaldate))))->orderby('id', 'desc')
    //                             //     ->first();
                                
    //                             $transDate = date("Y-m-d " . $entryTime . "", strtotime(convertDate($globaldate)));
    //                             $record1 = SavingAccountTranscation::where('account_no', $ssbAccountDetails->account_no)
    //                                 ->whereDate('created_at', '<=',  $transDate)->orderby('id', 'desc')
    //                                 ->first();
    //                             $request['ssb_id'] = $ssbAccountDetails->id;
    //                             $ssb['saving_account_id'] = $ssbAccountDetails->id;

    //                             $ssb['account_no'] = $ssbAccountDetails->account_no;
    //                             $ssb['opening_balance'] = $record1->opening_balance - $request['emi_amount'];
    //                             $ssb['branch_id'] = $request['branch'];
    //                             $ssb['type'] = 9;
    //                             $ssb['deposit'] = 0;
    //                             $ssb['withdrawal'] = $request['deposite_amount'];
    //                             $ssb['description'] = 'Loan EMI Payment' . $mLoan->account_number;
    //                             $ssb['currency_code'] = 'INR';
    //                             $ssb['payment_type'] = 'DR';
    //                             $ssb['payment_mode'] = $ssbpaymentMode;
    //                             $ssb['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($globaldate)));
    //                             $ssb['is_app']=2;
    //                             $ssb['app_login_user_id']=$member2->id;
    //                             $ssbAccountTran = SavingAccountTranscation::create($ssb);
    //                             $ssb_transaction_id = $ssb_account_id_from = $ssbAccountTran->id;

    //                             // update saving account current balance
    //                             $ssbBalance = SavingAccount::find($ssbAccountDetails->id);
    //                             $ssbBalance->update(['balance' => ($ssbAccountDetails->balance - $request['emi_amount'])]);

    //                             $record2 = SavingAccountTranscation::where('account_no', $ssbAccountDetails->account_no)
    //                                 ->whereDate('created_at', '>', date("Y-m-d", strtotime(convertDate($globaldate))))->get();
    //                             foreach ($record2 as $key => $value) {
    //                                 $nsResult = SavingAccountTranscation::find($value->id);
    //                                 $nsResult['opening_balance'] = $value->opening_balance - $request['emi_amount'];
    //                                 $nsResult['updated_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($globaldate)));
    //                                 $nsResult->save();
    //                             }
    //                             $data['saving_account_transaction_id'] = $ssb_transaction_id;
    //                             $data['loan_id'] = $request['loan_id'];
    //                             $data['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($globaldate)));
    //                             $satRef = TransactionReferences::create($data);
    //                             $satRefId = $satRef->id;

    //                             $updateSsbDayBook = $this->updateSsbDayBookAmount($request['deposite_amount'], $ssbAccountDetails->account_no, date("Y-m-d " . $entryTime . "", strtotime(convertDate($globaldate))));
                               
    //                            // $ssbCreateTran = CommanAppController::createTransaction($satRefId, 1, $ssbAccountDetails->id, $ssbAccountDetails->member_id, $request['branch'], getBranchCode($request['branch'])->branch_code, $amountArraySsb, $paymentMode, $request['loan_associate_name'], $request['associate_member_id'], $ssbAccountDetails->account_no, $cheque_dd_no, $bank_name, $branch_name = NULL, $paymentDate, $online_payment_id, $online_payment_by, $ssbAccountDetails->id, 'DR');

    //                             //$totalbalance = $request['ssb_account'] - $request['deposite_amount'];


    //                             //$ssbCreateTran = CommanAppController::createTransaction($satRefId, 5, $request['loan_id'], $mLoan->applicant_id, $request['branch'], getBranchCode($request['branch'])->branch_code, $amountArraySsb, $paymentMode, $request['loan_associate_name'], $request['associate_member_id'], $mLoan->account_number, $cheque_dd_no, $bank_name, $branch_name = NULL, $paymentDate, $online_payment_id, $online_payment_by, NULL, 'CR');

    //                             /************* Head Implement ****************/


    //                             $chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    //                             $v_no = "";
    //                             for ($i = 0; $i < 10; $i++) {
    //                                 $v_no .= $chars[mt_rand(0, strlen($chars) - 1)];
    //                             }
    //                             $roidayBookRef = CommanAppController::createBranchDayBookReference($roi + $principal_amount);

    //                             $principalbranchDayBook = CommanAppController::branchDayBookNew($roidayBookRef, $branchId, 5, 52, $loanId, $ssb_transaction_id, $request['associate_member_id'], $member_id = NULL, $branch_id_to = NULL, $branch_id_from = NULL, $roi + $principal_amount, $roi + $principal_amount, $roi + $principal_amount, '' . $mLoan->account_number . ' EMI collection', 'SSB A/C Cr ' . ($roi + $principal_amount) . '', 'SSB A/C Cr ' . ($roi + $principal_amount) . '', 'CR', 3, 'INR', $branchId, getBranchDetail($branchId)->name, $amount_from_id = NULL, $amount_from_name = NULL, $v_no, date("Y-m-d", strtotime(convertDate($globaldate))), $ssbAccountDetails->id, $cheque_no = NULL, $cheque_date = NULL, $cheque_bank_from = NULL, $cheque_bank_ac_from = NULL, $cheque_bank_ifsc_from = NULL, $cheque_bank_branch_from = NULL, $cheque_bank_to = NULL, $cheque_bank_ac_to = NULL, $transction_no = NULL, $transction_bank_from = NULL, $transction_bank_ac_from = NULL, $transction_bank_ifsc_from = NULL, $transction_bank_branch_from = NULL, $transction_bank_to = NULL, $transction_bank_ac_to = NULL, date("Y-m-d", strtotime(convertDate($globaldate))), date("Y-m-d", strtotime(convertDate($globaldate))), date("H:i:s"), 3, $userid, $is_contra = NULL, $contra_id = NULL, date("Y-m-d", strtotime(convertDate($globaldate))), $ssb_account_tran_id_to = NULL, $ssb_account_id_from, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL,$member2->id,2);

    //                             $allHeadTransaction = $this->createAllHeadTransaction($roidayBookRef, $request['branch'], NULL, NULL, 31, 5, 523, $loanId, $ssb_transaction_id, $request['associate_member_id'], NULL, $mLoan->branch_id, $ssbAccountDetails->branch_id, $roi, $roi, $roi, '' . $mLoan->account_number . ' EMI collection', 'CR', 3, 'INR', $request['branch'], getBranchDetail($request['branch'])->name, $request['associate_member_id'], getMemberData($request['associate_member_id'])->first_name . ' ' . getMemberData($request['associate_member_id'])->last_name, $jv_unique_id = NULL, $v_no, date("Y-m-d", strtotime(convertDate($globaldate))), $request['ssb_id'], NULL, NULL, $ssb_transaction_id, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 3, $userid,$member2->id,2);


    //                             $allHeadTransaction = $this->createAllHeadTransaction($roidayBookRef, $request['branch'], NULL, NULL, 56, 5, 52, $loanId, $ssb_transaction_id, $request['associate_member_id'], NULL, $mLoan->branch_id, $ssbAccountDetails->branch_id, $roi, $roi, $roi, '' . $mLoan->account_number . ' EMI collection', 'DR', 3, 'INR', $request['branch'], getBranchDetail($request['branch'])->name, $request['associate_member_id'], getMemberData($request['associate_member_id'])->first_name . ' ' . getMemberData($request['associate_member_id'])->last_name, $jv_unique_id = NULL, $v_no, date("Y-m-d", strtotime(convertDate($globaldate))), $request['ssb_id'], NULL, NULL, $ssb_transaction_id, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 3, $userid,$member2->id,2);
    //                             // print_r($satRef->id);
    //                             // exit();
    //                             // if ($mLoan->loan_type == '1') {
    //                             //     $loan_head_id = 64;
    //                             // } elseif ($mLoan->loan_type == '2') {
    //                             //     $loan_head_id = 65;
    //                             // } elseif ($mLoan->loan_type == '3') {
    //                             //     $loan_head_id = 66;
    //                             // } elseif ($mLoan->loan_type == '4') {
    //                             //     $loan_head_id = 67;
    //                             // }
    //                             $loan_head_id = $mLoan['loan']->head_id;
    //                             $allHeadTransaction = $this->createAllHeadTransaction($roidayBookRef, $request['branch'], NULL, NULL, $loan_head_id, 5, 52, $loanId, $ssb_transaction_id, $request['associate_member_id'], NULL, $mLoan->branch_id, $ssbAccountDetails->branch_id, $principal_amount, $principal_amount, $principal_amount, '' . $mLoan->account_number . ' EMI collection', 'CR', 3, 'INR', $request['branch'], getBranchDetail($request['branch'])->name, $request['associate_member_id'], getMemberData($request['associate_member_id'])->first_name . ' ' . getMemberData($request['associate_member_id'])->last_name, $jv_unique_id = NULL, $v_no, date("Y-m-d", strtotime(convertDate($globaldate))), $request['ssb_id'], NULL, NULL, $ssb_transaction_id, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 3, $userid,$member2->id,2);

    //                             $allHeadTransaction = $this->createAllHeadTransaction($roidayBookRef, $request['branch'], NULL, NULL, 56, 5, 52, $loanId, $ssb_transaction_id, $request['associate_member_id'], NULL, $mLoan->branch_id, $ssbAccountDetails->branch_id, $principal_amount, $principal_amount, $principal_amount, '' . $mLoan->account_number . ' EMI collection', 'DR', 3, 'INR', $request['branch'], getBranchDetail($request['branch'])->name, $request['associate_member_id'], getMemberData($request['associate_member_id'])->first_name . ' ' . getMemberData($request['associate_member_id'])->last_name, $jv_unique_id = NULL, $v_no, date("Y-m-d", strtotime(convertDate($globaldate))), $request['ssb_id'], NULL, NULL, $ssb_transaction_id, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 3, $userid,$member2->id,2);

    //                             $roimemberTransaction = CommanAppController::memberTransactionNew($roidayBookRef, 5, 52, $loanId, $ssb_transaction_id, $request['associate_member_id'], $mLoan->applicant_id, $branchId, $bank_id = NULL, $account_id = NULL, $roi + $principal_amount, '' . $mLoan->account_number . ' EMI collection', 'DR', 3, 'INR', $branchId, getBranchDetail($branchId)->name, $amount_from_id = NULL, $amount_from_name = NULL, $v_no, date("Y-m-d", strtotime(convertDate($globaldate))), $ssbAccountDetails->id, $cheque_no = NULL, $cheque_date = NULL, $cheque_bank_from = NULL, $cheque_bank_ac_from = NULL, $cheque_bank_ifsc_from = NULL, $cheque_bank_branch_from = NULL, $cheque_bank_to = NULL, $cheque_bank_ac_to = NULL, $transction_no = NULL, $transction_bank_from = NULL, $transction_bank_ac_from = NULL, $transction_bank_ifsc_from = NULL, $transction_bank_branch_from = NULL, $transction_bank_to = NULL, $transction_bank_ac_to = NULL, $globaldate, $globaldate, date("H:i:s"), 3, $userid, $globaldate, $ssb_account_tran_id_to = NULL, $ssb_account_id_from, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL,$member2->id,2);

    //                             $createLoanDayBook = CommanAppController::createLoanDayBook($roidayBookRef, $roidayBookRef, $mLoan->loan_type, 0, $loanId, $lId = NULL, $mLoan->account_number, $mLoan->applicant_id, $roi, $principal_amount, $dueAmount, $request['deposite_amount'], 'Loan EMI deposit', $request['branch'], getBranchCode($request['branch'])->branch_code, 'CR', 'INR', $paymentMode, $cheque_dd_no, $bank_name, $branch_name = NULL, date("Y-m-d", strtotime(convertDate($globaldate))), $online_payment_id, 1, 1, $cheque_date, $account_number, NULL, $request['loan_associate_name'], $request['associate_member_id'], $request['branch'], $totalDailyInterest, $totalDayInterest, 0,$member2->id,2 );

    //                             $daybookId = $createLoanDayBook;
    //                             $total_amount = $request['deposite_amount'];
    //                             $percentage = 2;
    //                             $month = NULL;
    //                             $type_id = $request['loan_id'];
    //                             $type = 4;
    //                             $associate_id = $request['associate_member_id'];
    //                             $branch_id = $request['branch'];
    //                             $commission_type = 0;
    //                             $associateDetail = Member::where('id', $associate_id)->first();
    //                             $carder = $associateDetail->current_carder_id;
    //                             $associate_exist = 0;
    //                             $percentInDecimal = $percentage / 100;
    //                             $commission_amount = round($percentInDecimal * $total_amount, 4);
    //                             $loan_associate_code = $request["loan_associate_code"];
    //                             $associateCommission['member_id'] = $associate_id;
    //                             $associateCommission['branch_id'] = $branch_id;
    //                             $associateCommission['type'] = $type;
    //                             $associateCommission['type_id'] = $type_id;
    //                             $associateCommission['day_book_id'] = $daybookId;
    //                             $associateCommission['total_amount'] = $total_amount;
    //                             $associateCommission['month'] = $month;
    //                             $associateCommission['commission_amount'] = $commission_amount;
    //                             $associateCommission['percentage'] = $percentage;
    //                             $associateCommission['commission_type'] = $commission_type;
    //                             $date = \App\Models\Daybook::where('id', $daybookId)->first();
    //                             $associateCommission['created_at'] = $date->created_at;
    //                             $associateCommission['pay_type'] = 4;
    //                             $associateCommission['carder_id'] = $carder;
    //                             $associateCommission['associate_exist'] = $associate_exist;
    //                             if ($loan_associate_code != 9999999) {
    //                                 $associateCommissionInsert = \App\Models\CommissionEntryLoan::create($associateCommission);
    //                             }

    //                             /*---------- commission script  end  ---------*/

    //                             $totalDepsoit = LoanDaybooks::where('account_number', $mLoan->account_number)->where('loan_sub_type', 0)->where('is_deleted', 0)->sum('deposit');

    //                             $text = 'Dear Member,Received Rs.' .  $request['deposite_amount'] . ' as EMI of Loan A/C ' . $mLoan->account_number . ' on ' . date('d/m/Y', strtotime(convertDate($globaldate))) . ' Total recovery Rs. ' . $totalDepsoit . ' Thank You. Samraddh Bestwin Microfinance';;

    //                             $temaplteId = 1207166308935249821;
    //                             $contactNumber = array();
    //                             $memberDetail = Member::find($mLoan->applicant_id);
    //                             $contactNumber[] = $memberDetail->mobile_no;
    //                             $sendToMember = new Sms();
    //                             //$sendToMember->sendSms($contactNumber, $text, $temaplteId);

    //                             DB::commit();

    //                             $response["status"] = "Success";
    //                             $response["code"] = 200;
    //                             $response["messages"] = "Loan Emi Paid successful!";
    //                             $response["data"] = array();
    //                             return response()->json($response);

    //                         /************* Head Implement ****************/
    //                     }


    //         } else {
    //             $response["status"] = "Error";
    //             $response["code"] = 201;
    //             $response["messages"] = "Enter Valid Member Id";
    //             $response["otp"] = "";
    //         }
    //     } else {
    //         $response["status"] = "Error";
    //         $response["code"] = "201";
    //         $response["messages"] = "Input parameter missing";
    //         $response["otp"] = "";
    //     }
    //     return response()->json($response);
    // } 

    public function submitLoanPayment(Request $request)
    {
        
        $response = array();

        $request = $request->all();
        $entryTime = date("H:i:s");
      
        if (isset($request["member_id"]) && $request["member_id"] != "" && isset($request["company_id"]) && $request["company_id"] != "" && isset($request["emi_amount"]) && $request["emi_amount"] != "" && isset($request["loan_id"]) && $request["loan_id"] != "" && isset($request["payment_mode"]) && $request["payment_mode"] != "") {
            // Get Member ID
            $member = Member::select('id', 'mobile_no', 'associate_code', 'member_id','branch_id')
            ->with([
                "savingAccount_Custom3" => function ($q) use ($request) {
                    $q->when($request['company_id'], function ($query) use ($request) {
                        $query->with(['savingAccountTransactionView' => function ($q) {
                            $q->select('saving_account_id', 'opening_balance', 'id');
                        }])
                        ->whereCompanyId($request['company_id'])
                        ->where("transaction_status", "1");
                    });
                }
            ])
            ->where('member_id', $request["member_id"])
            ->first();
        
      
            $status = $this->loanPaymentService->checkSavingAccountStatus($member);
            if(method_exists($status,'getStatusCode') && $status->getStatusCode() === 404)
            {
                return new EpassBookLoanResource($status);

            }    
            $model = new Memberloans;
            $mLoan = $model::where('id',$request["loan_id"])->with('loanBranch')->where('status',4)->first();
            
            if(!isset($mLoan->account_number))
            {
                $response = [
                    'status' => 'error',
                     'code' => '201',
                     'data' => '',
                     'message' => 'Record Not Found'   
                ];
                return response()->json($response);

            }    
            $accountNumberDetails = [
                [
                    'account_number' => $mLoan->account_number,
                    'amount' => $request["emi_amount"]
                ]
            ];
            $stateid = getBranchState($mLoan['loanBranch']->name);
            $globaldate = checkMonthAvailability(date('d'), date('m'), date('Y'), $mLoan['loanBranch']->state_id);
            Session::put('created_at', date("Y-m-d " . $entryTime . "", strtotime(convertDate($globaldate))));


            if(isset($member->id))
            {
                $epassBook = 1;
                $data =  $this->loanPaymentService->depositeLoanEmi(
                    $model,
                    $accountNumberDetails,
                    $globaldate,
                    $member,
                    $epassBook,
                    $mLoan


    
                );
          
                return new EpassBookLoanResource($data);
            }
          
            // if (isset($member->id)) {

            //     $associate_no = $request["member_id"];
            //     $loan_id = $request["loan_id"];
            //     $loanId = $request["loan_id"];
            //     $emi_amount = $request["emi_amount"];
            //     $request['deposite_amount'] = $request["emi_amount"];
            //     $payment_modesss = $request["payment_mode"];
            //     $type = $request["type"];

            //     $request["loan_associate_code"] = $member->associate_code;

            //     $member = Member::select('id', 'associate_app_status', 'associate_id')->where('member_id', $request["member_id"])->where('associate_status', 1)->where('is_block', 0)->count();
            //     $member2 = Member::select('id', 'associate_app_status', 'associate_id', 'branch_id', 'first_name', 'last_name')->where('member_id', $request["member_id"])->where('associate_status', 1)->where('is_block', 0)->first();
            //     // dd($member);
            //     $userid = $member2->userid;

            //     $memberloan = Memberloans::with(['loanBranch', 'loan'])->where('id', $loanId)->first();
            //     $branchId = $memberloan->branch_id;
            //     $request['branch'] = $memberloan->branch_id;
            //     $request['associate_member_id'] = $memberloan->associate_member_id;


            //     $memberloanid = $memberloan->id;
            //     $member_loan_branch_id = $member2->branch_id;
            //     $membertype = $memberloan->loan_type;
            //     $mLoan = $memberloan;
            //     $stateid = getBranchState($mLoan['loanBranch']->name);
            //     $request['loan_associate_name'] = $member2->first_name . ' ' . $member2->last_name;

            //     $globaldate = checkMonthAvailability(date('d'), date('m'), date('Y'), $mLoan['loanBranch']->state_id);
            //     Session::put('created_at', date("Y-m-d " . $entryTime . "", strtotime(convertDate($globaldate))));

            //     //$globaldate = "12-11-2022";
            //     $entryTime = date("H:i:s");
            //     $ssbAccountDetails = SavingAccount::with('ssbMember')->where('member_id', $member2->id)->first();
            //     $ssbAccountDetailsDeposit = SavingAccountTranscation::where('saving_account_id', $ssbAccountDetails->id)->sum('deposit');
            //     $ssbAccountDetailsWithdrawal = SavingAccountTranscation::where('saving_account_id', $ssbAccountDetails->id)->sum('withdrawal');
            //     $ssbAmont = $ssbAccountDetailsDeposit - $ssbAccountDetailsWithdrawal;

            //     // check Saving Account is Exist or not
            //     if ($ssbAmont < $request["emi_amount"]) {

            //         $response["status"] = "Success";
            //         $response["code"] = 201;
            //         $response["messages"] = "Insufficient Balance!";
            //         $response["data"] = array();
            //         return response()->json($response);
            //     } else {

            //         $globaldate = date('Y-m-d', strtotime(convertDate($globaldate)));

            //         $LoanCreatedDate = date('Y-m-d', strtotime($mLoan->approve_date));
            //         $LoanCreatedYear = date('Y', strtotime($mLoan->approve_date));
            //         $LoanCreatedMonth = date('m', strtotime($mLoan->approve_date));
            //         $LoanCreateDate = date('d', strtotime($mLoan->approve_date));
            //         $currentDate = date('Y-m-d');
            //         $CurrentDate = date('d');
            //         $CurrentDateYear = date('Y');
            //         $CurrentDateMonth = date('m');


            //         $applicationDate = date('Y-m-d', strtotime(convertDate($globaldate)));
            //         $applicationCurrentDate = date('d', strtotime(convertDate($globaldate)));
            //         $applicationCurrentDateYear = date('Y', strtotime(convertDate($globaldate)));
            //         $applicationCurrentDateMonth = date('m', strtotime(convertDate($globaldate)));


            //         if ($mLoan->emi_option == 1) { //Month

            //             $daysDiff = (($CurrentDateYear - $LoanCreatedYear) * 12) + ($CurrentDateMonth - $LoanCreatedMonth);
            //             $daysDiff2 = today()->diffInDays($LoanCreatedDate);
            //             $nextEmiDates = $this->nextEmiDates($daysDiff, $LoanCreatedDate);
            //             $nextEmiDates2 = $this->nextEmiDatesDays($daysDiff2, $LoanCreatedDate);
            //             $nextEmiDates3 = $this->nextEmiDates($mLoan->emi_period, $LoanCreatedDate);
            //         }
            //         if ($mLoan->emi_option == 2) { //Week
            //             $daysDiff = today()->diffInDays($LoanCreatedDate);
            //             $daysDiff = $daysDiff / 7;
            //             $nextEmiDates2 = $this->nextEmiDatesDays($daysDiff, $LoanCreatedDate);
            //             $nextEmiDates = $this->nextEmiDatesWeek($daysDiff, $LoanCreatedDate);
            //         }
            //         if ($mLoan->emi_option == 3) {  //Days
            //             $daysDiff = today()->diffInDays($LoanCreatedDate);
            //             $nextEmiDates = $this->nextEmiDatesDays($daysDiff, $LoanCreatedDate);
            //         }

            //         $totalDayInterest = 0;
            //         $totalDailyInterest = 0;
            //         $newApplicationDate = explode('-', $applicationDate);
            //         $StartnewDAte = $applicationCurrentDateYear . '-' . $applicationCurrentDateMonth . '-01';
            //         $EndnewDAte = $applicationCurrentDateYear . '-' . $applicationCurrentDateMonth . '-31';
            //         $dailyoutstandingAmount = 0;

            //         $lastOutstanding = LoanEmisNew::where('loan_id', $mLoan->id)->where('loan_type', $mLoan->loan_type)->where('is_deleted', '0')->orderBy('id', 'desc')->first();

            //         $lastOutstandingDate = LoanEmisNew::where('loan_id', $mLoan->id)->where('loan_type', $mLoan->loan_type)->where('is_deleted', '0')->pluck('emi_date')->toArray();
            //         $newDate = array();
            //         $deposit = $emi_amount;
            //         if (isset($lastOutstanding->out_standing_amount)) {
            //             $checkDateMonth = date('m', strtotime($lastOutstanding->emi_date));
            //             $checkDateYear = date('Y', strtotime($lastOutstanding->emi_date));
            //             $newstartDate = $checkDateYear . '-' .  $checkDateMonth . '-01';
            //             $newEndDate = $checkDateYear . '-' .  $checkDateMonth . '-31';

            //             $gapDayes = Carbon\Carbon::parse($lastOutstanding->emi_date)->diff(Carbon\Carbon::parse($applicationDate))->format('%a');


            //             $lastOutstanding2 = LoanEmisNew::where('loan_id', $mLoan->id)->where('loan_type', $mLoan->loan_type)->whereBetween('emi_date', [$newstartDate, $newEndDate])->where('is_deleted', '0')->sum('out_standing_amount');
            //             $roi = ((($mLoan->ROI) / 365) * $lastOutstanding->out_standing_amount) / 100;
            //             if ($mLoan->emi_option == 1 || $mLoan->emi_option == 2) {
            //                 $roi = $roi * $gapDayes;
            //                 $principal_amount = $deposit - $roi;
            //                 if (!array_key_exists($applicationCurrentDate . '_' . $applicationCurrentDateMonth . '_' . $applicationCurrentDateYear, $nextEmiDates)) {
            //                     $outstandingAmount = ($lastOutstanding->out_standing_amount - $deposit);
            //                 } else {
            //                     $preDate = current($nextEmiDates);
            //                     $oldDate = $nextEmiDates[$applicationCurrentDate . '_' . $applicationCurrentDateMonth . '_' . $applicationCurrentDateYear];
            //                     if ($mLoan->emi_option == 1) {
            //                         $previousDate =  Carbon\Carbon::parse($oldDate)->subMonth(1);
            //                     }
            //                     if ($mLoan->emi_option == 2) {
            //                         $previousDate =  Carbon\Carbon::parse($oldDate)->subDays(7);
            //                     }
            //                     $pDate = date('Y-m-d', strtotime("+1 day", strtotime($previousDate)));
            //                     if ($preDate == $applicationDate) {
            //                         $aqmount = LoanEmisNew::where('loan_id', $mLoan->id)->whereBetween('emi_date', [$LoanCreatedDate, $applicationDate])->where('is_deleted', '0')->sum('roi_amount');
            //                     } else {
            //                         $aqmount = LoanEmisNew::where('loan_id', $mLoan->id)->whereBetween('emi_date', [$pDate, $applicationDate])->where('is_deleted', '0')->sum('roi_amount');
            //                     }
            //                     if ($aqmount > 0) {
            //                         $outstandingAmount = ($lastOutstanding->out_standing_amount - $deposit + $roi   + $aqmount);
            //                     } else {
            //                         $outstandingAmount = ($lastOutstanding->out_standing_amount - $deposit + $roi);
            //                     }
            //                 }
            //                 $dailyoutstandingAmount = $outstandingAmount + $roi;
            //             } else {
            //                 $principal_amount = $deposit - $roi;
            //                 $outstandingAmount = ($lastOutstanding->out_standing_amount - $principal_amount);
            //             }
            //             $deposit =  $request['deposite_amount'];
            //         } else {
            //             $roi = ((($mLoan->ROI) / 365) * $mLoan->amount) / 100;
            //             // dd($mLoan->approve_date,$applicationDate);
                        
            //             $approveDate = \Carbon\Carbon::createFromFormat('d/m/Y', $mLoan->approve_date);
            //             $applicationDate = \Carbon\Carbon::parse($applicationDate);
                        
            //             $gapDayes = $approveDate->diffInDays($applicationDate);
                        
            //             // $gapDayes =  Carbon\Carbon::parse($mLoan->approve_date)->diff(Carbon\Carbon::parse($applicationDate))->format('%a');
                     
            //             if ($mLoan->emi_option == 1 || $mLoan->emi_option == 2) //Monthly
            //             {
            //                 $roi = $roi * $gapDayes;
            //                 $principal_amount = $deposit - $roi;
            //                 if (!array_key_exists($applicationCurrentDate . '_' . $applicationCurrentDateMonth . '_' . $applicationCurrentDateYear, $nextEmiDates)) {
            //                     $outstandingAmount = ($mLoan->amount - $deposit);
            //                 } else {
            //                     $outstandingAmount = ($mLoan->amount - $deposit + $roi);
            //                 }
            //                 $dailyoutstandingAmount = $outstandingAmount + $roi;
            //             } else {
            //                 $principal_amount = $deposit - $roi;
            //                 $outstandingAmount = ($mLoan->amount - $principal_amount);
            //             }
            //             $deposit =  $request['deposite_amount'];
            //             $dailyoutstandingAmount = $mLoan->amount + $roi;
            //         }


            //         $amountArraySsb = array(
            //             '1' => $request['deposite_amount']
            //         );
            //         if (isset(($ssbAccountDetails['ssbMember']))) {
            //             $amount_deposit_by_name = $ssbAccountDetails['ssbMember']->first_name . ' ' . $ssbAccountDetails['ssbMember']->last_name;
            //         } else {
            //             $amount_deposit_by_name = NULL;
            //         }

            //         $dueAmount = $mLoan->due_amount - round($principal_amount);
            //         $mlResult = Memberloans::find($request['loan_id']);
            //         $lData['credit_amount'] = $mLoan->credit_amount + $principal_amount;
            //         $lData['due_amount'] = $dueAmount;


            //         $lData['received_emi_amount'] = $mLoan->received_emi_amount + $request['deposite_amount'];

            //         $mlResult->update($lData);
            //         // add log
            //         $postData = $_POST;
            //         $enData = array(
            //             "post_data" => $postData,
            //             "lData" => $lData
            //         );

            //         $encodeDate = json_encode($enData);
            //         // $arrs = array(
            //         //     "load_id" => $loan_id,
            //         //     "type" => "7",
            //         //     "account_head_id" => 0,
            //         //     "user_id" => $member2->id,
            //         //     "message" => "Loan Recovery   - Loan EMI payment",
            //         //     "data" => $encodeDate
            //         // );
            //         // DB::table('user_log')->insert($arrs);

            //         // end log

            //         $cheque_dd_no = NULL;
            //         $online_payment_id = NULL;
            //         $online_payment_by = NULL;
            //         $bank_name = NULL;
            //         $cheque_date = NULL;
            //         $account_number = NULL;
            //         $paymentMode = 4;
            //         $ssbpaymentMode = 3;
            //         $paymentDate = date("Y-m-d " . $entryTime . "", strtotime(convertDate($globaldate)));
            //         // $record1 = SavingAccountTranscation::where('account_no', $ssbAccountDetails->account_no)
            //         //     ->whereDate('created_at', '<=', date("Y-m-d", strtotime(convertDate($globaldate))))->orderby('id', 'desc')
            //         //     ->first();

            //         $transDate = date("Y-m-d " . $entryTime . "", strtotime(convertDate($globaldate)));

                    
            //         $record1 = SavingAccountTranscation::where('account_no', $ssbAccountDetails->account_no)
            //             ->whereDate('created_at', '<=',  $transDate)->orderby('id', 'desc')
            //             ->first();
            //         $request['ssb_id'] = $ssbAccountDetails->id;
            //         $ssb['saving_account_id'] = $ssbAccountDetails->id;

            //         $ssb['account_no'] = $ssbAccountDetails->account_no;
            //         $ssb['opening_balance'] = $record1->opening_balance - $request['emi_amount'];
            //         $ssb['branch_id'] = $request['branch'];
            //         $ssb['type'] = 9;
            //         $ssb['deposit'] = 0;
            //         $ssb['withdrawal'] = $request['deposite_amount'];
            //         $ssb['description'] = 'Loan EMI Payment' . $mLoan->account_number;
            //         $ssb['currency_code'] = 'INR';
            //         $ssb['payment_type'] = 'DR';
            //         $ssb['payment_mode'] = $ssbpaymentMode;
            //         $ssb['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($globaldate)));
            //         $ssb['is_app'] = 2;
            //         $ssb['app_login_user_id'] = $member2->id;
            //         $ssbAccountTran = SavingAccountTranscation::create($ssb);
            //         $ssb_transaction_id = $ssb_account_id_from = $ssbAccountTran->id;

            //         // update saving account current balance
            //         $ssbBalance = SavingAccount::find($ssbAccountDetails->id);
            //         $ssbBalance->update(['balance' => ($ssbAccountDetails->balance - $request['emi_amount'])]);
                
            //         $record2 = SavingAccountTranscation::where('account_no', $ssbAccountDetails->account_no)
            //             ->whereDate('created_at', '>', date("Y-m-d", strtotime(convertDate($globaldate))))->get();
            //         foreach ($record2 as $key => $value) {
            //             $nsResult = SavingAccountTranscation::find($value->id);
            //             $nsResult['opening_balance'] = $value->opening_balance - $request['emi_amount'];
            //             $nsResult['updated_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($globaldate)));
            //             $nsResult->save();
            //         }
            //         $data['saving_account_transaction_id'] = $ssb_transaction_id;
            //         $data['loan_id'] = $request['loan_id'];
            //         $data['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($globaldate)));
            //         // $satRef = TransactionReferences::create($data);
            //         // $satRefId = $satRef->id;
            //         // dd($satRefId);
            //         $updateSsbDayBook = $this->updateSsbDayBookAmount($request['deposite_amount'], $ssbAccountDetails->account_no, date("Y-m-d " . $entryTime . "", strtotime(convertDate($globaldate))));

            //         // $ssbCreateTran = CommanAppController::createTransaction($satRefId, 1, $ssbAccountDetails->id, $ssbAccountDetails->member_id, $request['branch'], getBranchCode($request['branch'])->branch_code, $amountArraySsb, $paymentMode, $request['loan_associate_name'], $request['associate_member_id'], $ssbAccountDetails->account_no, $cheque_dd_no, $bank_name, $branch_name = NULL, $paymentDate, $online_payment_id, $online_payment_by, $ssbAccountDetails->id, 'DR');

            //         //$totalbalance = $request['ssb_account'] - $request['deposite_amount'];


            //         //$ssbCreateTran = CommanAppController::createTransaction($satRefId, 5, $request['loan_id'], $mLoan->applicant_id, $request['branch'], getBranchCode($request['branch'])->branch_code, $amountArraySsb, $paymentMode, $request['loan_associate_name'], $request['associate_member_id'], $mLoan->account_number, $cheque_dd_no, $bank_name, $branch_name = NULL, $paymentDate, $online_payment_id, $online_payment_by, NULL, 'CR');

            //         /************* Head Implement ****************/


            //         $chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
            //         $v_no = "";
            //         for ($i = 0; $i < 10; $i++) {
            //             $v_no .= $chars[mt_rand(0, strlen($chars) - 1)];
            //         }
            //         $roidayBookRef = CommanAppEpassbookController::createBranchDayBookReference($roi + $principal_amount);

            //         $principalbranchDayBook = CommanAppEpassbookController::branchDayBookNew($roidayBookRef, $branchId, 5, 52, $loanId, $ssb_transaction_id, $request['associate_member_id'], $member_id = NULL, $branch_id_to = NULL, $branch_id_from = NULL, $roi + $principal_amount, $roi + $principal_amount, $roi + $principal_amount, '' . $mLoan->account_number . ' EMI collection', 'SSB A/C Cr ' . ($roi + $principal_amount) . '', 'SSB A/C Cr ' . ($roi + $principal_amount) . '', 'CR', 3, 'INR', $branchId, getBranchDetail($branchId)->name, $amount_from_id = NULL, $amount_from_name = NULL, $v_no, date("Y-m-d", strtotime(convertDate($globaldate))), $ssbAccountDetails->id, $cheque_no = NULL, $cheque_date = NULL, $cheque_bank_from = NULL, $cheque_bank_ac_from = NULL, $cheque_bank_ifsc_from = NULL, $cheque_bank_branch_from = NULL, $cheque_bank_to = NULL, $cheque_bank_ac_to = NULL, $transction_no = NULL, $transction_bank_from = NULL, $transction_bank_ac_from = NULL, $transction_bank_ifsc_from = NULL, $transction_bank_branch_from = NULL, $transction_bank_to = NULL, $transction_bank_ac_to = NULL, date("Y-m-d", strtotime(convertDate($globaldate))), date("Y-m-d", strtotime(convertDate($globaldate))), date("H:i:s"), 3, $userid, $is_contra = NULL, $contra_id = NULL, date("Y-m-d", strtotime(convertDate($globaldate))), $ssb_account_tran_id_to = NULL, $ssb_account_id_from, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL, $member2->id, 2);

            //         $allHeadTransaction = $this->createAllHeadTransaction($roidayBookRef, $request['branch'], NULL, NULL, 31, 5, 523, $loanId, $ssb_transaction_id, $request['associate_member_id'], NULL, $mLoan->branch_id, $ssbAccountDetails->branch_id, $roi, $roi, $roi, '' . $mLoan->account_number . ' EMI collection', 'CR', 3, 'INR', $request['branch'], getBranchDetail($request['branch'])->name, $request['associate_member_id'], getMemberData($request['associate_member_id'])->first_name . ' ' . getMemberData($request['associate_member_id'])->last_name, $jv_unique_id = NULL, $v_no, date("Y-m-d", strtotime(convertDate($globaldate))), $request['ssb_id'], NULL, NULL, $ssb_transaction_id, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 3, $userid, $member2->id, 2);


            //         $allHeadTransaction = $this->createAllHeadTransaction($roidayBookRef, $request['branch'], NULL, NULL, 56, 5, 52, $loanId, $ssb_transaction_id, $request['associate_member_id'], NULL, $mLoan->branch_id, $ssbAccountDetails->branch_id, $roi, $roi, $roi, '' . $mLoan->account_number . ' EMI collection', 'DR', 3, 'INR', $request['branch'], getBranchDetail($request['branch'])->name, $request['associate_member_id'], getMemberData($request['associate_member_id'])->first_name . ' ' . getMemberData($request['associate_member_id'])->last_name, $jv_unique_id = NULL, $v_no, date("Y-m-d", strtotime(convertDate($globaldate))), $request['ssb_id'], NULL, NULL, $ssb_transaction_id, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 3, $userid, $member2->id, 2);
            //         // print_r($satRef->id);
            //         // exit();
            //         // if ($mLoan->loan_type == '1') {
            //         //     $loan_head_id = 64;
            //         // } elseif ($mLoan->loan_type == '2') {
            //         //     $loan_head_id = 65;
            //         // } elseif ($mLoan->loan_type == '3') {
            //         //     $loan_head_id = 66;
            //         // } elseif ($mLoan->loan_type == '4') {
            //         //     $loan_head_id = 67;
            //         // }
            //         $loan_head_id = $mLoan['loan']->head_id;
            //         $allHeadTransaction = $this->createAllHeadTransaction($roidayBookRef, $request['branch'], NULL, NULL, $loan_head_id, 5, 52, $loanId, $ssb_transaction_id, $request['associate_member_id'], NULL, $mLoan->branch_id, $ssbAccountDetails->branch_id, $principal_amount, $principal_amount, $principal_amount, '' . $mLoan->account_number . ' EMI collection', 'CR', 3, 'INR', $request['branch'], getBranchDetail($request['branch'])->name, $request['associate_member_id'], getMemberData($request['associate_member_id'])->first_name . ' ' . getMemberData($request['associate_member_id'])->last_name, $jv_unique_id = NULL, $v_no, date("Y-m-d", strtotime(convertDate($globaldate))), $request['ssb_id'], NULL, NULL, $ssb_transaction_id, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 3, $userid, $member2->id, 2);

            //         $allHeadTransaction = $this->createAllHeadTransaction($roidayBookRef, $request['branch'], NULL, NULL, 56, 5, 52, $loanId, $ssb_transaction_id, $request['associate_member_id'], NULL, $mLoan->branch_id, $ssbAccountDetails->branch_id, $principal_amount, $principal_amount, $principal_amount, '' . $mLoan->account_number . ' EMI collection', 'DR', 3, 'INR', $request['branch'], getBranchDetail($request['branch'])->name, $request['associate_member_id'], getMemberData($request['associate_member_id'])->first_name . ' ' . getMemberData($request['associate_member_id'])->last_name, $jv_unique_id = NULL, $v_no, date("Y-m-d", strtotime(convertDate($globaldate))), $request['ssb_id'], NULL, NULL, $ssb_transaction_id, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 3, $userid, $member2->id, 2);

            //         $roimemberTransaction = CommanAppEpassbookController::memberTransactionNew($roidayBookRef, 5, 52, $loanId, $ssb_transaction_id, $request['associate_member_id'], $mLoan->applicant_id, $branchId, $bank_id = NULL, $account_id = NULL, $roi + $principal_amount, '' . $mLoan->account_number . ' EMI collection', 'DR', 3, 'INR', $branchId, getBranchDetail($branchId)->name, $amount_from_id = NULL, $amount_from_name = NULL, $v_no, date("Y-m-d", strtotime(convertDate($globaldate))), $ssbAccountDetails->id, $cheque_no = NULL, $cheque_date = NULL, $cheque_bank_from = NULL, $cheque_bank_ac_from = NULL, $cheque_bank_ifsc_from = NULL, $cheque_bank_branch_from = NULL, $cheque_bank_to = NULL, $cheque_bank_ac_to = NULL, $transction_no = NULL, $transction_bank_from = NULL, $transction_bank_ac_from = NULL, $transction_bank_ifsc_from = NULL, $transction_bank_branch_from = NULL, $transction_bank_to = NULL, $transction_bank_ac_to = NULL, $globaldate, $globaldate, date("H:i:s"), 3, $userid, $globaldate, $ssb_account_tran_id_to = NULL, $ssb_account_id_from, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL, $member2->id, 2);

            //         $createLoanDayBook = CommanAppController::createLoanDayBook($roidayBookRef, $roidayBookRef, $mLoan->loan_type, 0, $loanId, $lId = NULL, $mLoan->account_number, $mLoan->applicant_id, $roi, $principal_amount, $dueAmount, $request['deposite_amount'], 'Loan EMI deposit', $request['branch'], getBranchCode($request['branch'])->branch_code, 'CR', 'INR', $paymentMode, $cheque_dd_no, $bank_name, $branch_name = NULL, date("Y-m-d", strtotime(convertDate($globaldate))), $online_payment_id, 1, 1, $cheque_date, $account_number, NULL, $request['loan_associate_name'], $request['associate_member_id'], $request['branch'], $totalDailyInterest, $totalDayInterest, 0, $member2->id, 2);

            //         $daybookId = $createLoanDayBook;
            //         $total_amount = $request['deposite_amount'];
            //         $percentage = 2;
            //         $month = NULL;
            //         $type_id = $request['loan_id'];
            //         $type = 4;
            //         $associate_id = $request['associate_member_id'];
            //         $branch_id = $request['branch'];
            //         $commission_type = 0;
            //         $associateDetail = Member::where('id', $associate_id)->first();
            //         $carder = $associateDetail->current_carder_id;
            //         $associate_exist = 0;
            //         $percentInDecimal = $percentage / 100;
            //         $commission_amount = round($percentInDecimal * $total_amount, 4);
            //         $loan_associate_code = $request["loan_associate_code"];
            //         $associateCommission['member_id'] = $associate_id;
            //         $associateCommission['branch_id'] = $branch_id;
            //         $associateCommission['type'] = $type;
            //         $associateCommission['type_id'] = $type_id;
            //         $associateCommission['day_book_id'] = $daybookId;
            //         $associateCommission['total_amount'] = $total_amount;
            //         $associateCommission['month'] = $month;
            //         $associateCommission['commission_amount'] = $commission_amount;
            //         $associateCommission['percentage'] = $percentage;
            //         $associateCommission['commission_type'] = $commission_type;
            //         $date = \App\Models\Daybook::where('id', $daybookId)->first();
            //         $associateCommission['created_at'] = $date->created_at;
            //         $associateCommission['pay_type'] = 4;
            //         $associateCommission['carder_id'] = $carder;
            //         $associateCommission['associate_exist'] = $associate_exist;
            //         if ($loan_associate_code != 9999999) {
            //             $associateCommissionInsert = \App\Models\CommissionEntryLoan::create($associateCommission);
            //         }

            //         /*---------- commission script  end  ---------*/

            //         $totalDepsoit = LoanDaybooks::where('account_number', $mLoan->account_number)->where('loan_sub_type', 0)->where('is_deleted', 0)->sum('deposit');

            //         $text = 'Dear Member,Received Rs.' .  $request['deposite_amount'] . ' as EMI of Loan A/C ' . $mLoan->account_number . ' on ' . date('d/m/Y', strtotime(convertDate($globaldate))) . ' Total recovery Rs. ' . $totalDepsoit . ' Thank You. Samraddh Bestwin Microfinance';;

            //         $temaplteId = 1207166308935249821;
            //         $contactNumber = array();
            //         $memberDetail = Member::find($mLoan->applicant_id);
            //         $contactNumber[] = $memberDetail->mobile_no;
            //         $sendToMember = new Sms();
            //         //$sendToMember->sendSms($contactNumber, $text, $temaplteId);

            //         DB::commit();

            //         $response["status"] = "Success";
            //         $response["code"] = 200;
            //         $response["messages"] = "Loan Emi Paid successful!";
            //         $response["data"] = array();
            //         return response()->json($response);

            //         /************* Head Implement ****************/
            //     }
            // } else {
            //     $response["status"] = "Error";
            //     $response["code"] = 201;
            //     $response["messages"] = "Enter Valid Member Id";
            //     $response["otp"] = "";
            // }

            
        } else {
            $response["status"] = "Error";
            $response["code"] = "201";
            $response["messages"] = "Input parameter missing";
            $response["otp"] = "";
        }
        return response()->json($response);
    }



    public static function createAllHeadTransaction($daybook_ref_id, $branch_id, $bank_id, $bank_ac_id, $head_id, $type, $sub_type, $type_id, $type_transaction_id, $associate_id, $member_id, $branch_id_to, $branch_id_from, $opening_balance, $amount, $closing_balance, $description, $payment_type, $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $jv_unique_id, $v_no, $v_date, $ssb_account_id_from, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_from_id, $cheque_bank_ac_from_id, $cheque_bank_to, $cheque_bank_ac_to, $cheque_bank_to_name, $cheque_bank_to_branch, $cheque_bank_to_ac_no, $cheque_bank_to_ifsc, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_from_id, $transction_bank_from_ac_id, $transction_bank_to, $transction_bank_ac_to, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc, $transction_date, $created_by, $created_by_id,$Appuser,$isApp)
    {
        $globaldate = Session::get('created_at');;
        $t = date("H:i:s");
        $data['daybook_ref_id'] = $daybook_ref_id;
        $data['branch_id'] = $branch_id;
        $data['bank_id'] = $bank_id;
        $data['bank_ac_id'] = $bank_ac_id;
        $data['head_id'] = $head_id;
        $data['type'] = $type;
        $data['sub_type'] = $sub_type;
        $data['type_id'] = $type_id;
        $data['type_transaction_id'] = $type_transaction_id;
        $data['associate_id'] = $associate_id;
        $data['member_id'] = $member_id;
        $data['branch_id_to'] = $branch_id_to;
        $data['branch_id_from'] = $branch_id_from;
        $data['opening_balance'] = $opening_balance;
        $data['amount'] = $amount;
        $data['closing_balance'] = $closing_balance;
        $data['description'] = $description;
        $data['payment_type'] = $payment_type;
        $data['payment_mode'] = $payment_mode;
        $data['currency_code'] = $currency_code;
        $data['amount_to_id'] = $amount_to_id;
        $data['amount_to_name'] = $amount_to_name;
        $data['amount_from_id'] = $amount_from_id;
        $data['amount_from_name'] = $amount_from_name;
        $data['jv_unique_id'] = $jv_unique_id;
        $data['v_no'] = $v_no;
        $data['v_date'] = $v_date;
        $data['ssb_account_id_from'] = $ssb_account_id_from;
        $data['ssb_account_id_to'] = $ssb_account_id_to;
        $data['ssb_account_tran_id_to'] = $ssb_account_tran_id_to;
        $data['ssb_account_tran_id_from'] = $ssb_account_tran_id_from;
        $data['cheque_type'] = $cheque_type;
        $data['cheque_id'] = $cheque_id;
        $data['cheque_no'] = $cheque_no;
        $data['cheque_date'] = $cheque_date;
        $data['cheque_bank_from'] = $cheque_bank_from;
        $data['cheque_bank_ac_from'] = $cheque_bank_ac_from;
        $data['cheque_bank_ifsc_from'] = $cheque_bank_ifsc_from;
        $data['cheque_bank_branch_from'] = $cheque_bank_branch_from;
        $data['cheque_bank_from_id'] = $cheque_bank_from_id;
        $data['cheque_bank_ac_from_id'] = $cheque_bank_ac_from_id;
        $data['cheque_bank_to'] = $cheque_bank_to;
        $data['cheque_bank_ac_to'] = $cheque_bank_ac_to;
        $data['cheque_bank_to_name'] = $cheque_bank_to_name;
        $data['cheque_bank_to_branch'] = $cheque_bank_to_branch;
        $data['cheque_bank_to_ac_no'] = $cheque_bank_to_ac_no;
        $data['cheque_bank_to_ifsc'] = $cheque_bank_to_ifsc;
        $data['transction_no'] = $transction_no;
        $data['transction_bank_from'] = $transction_bank_from;
        $data['transction_bank_ac_from'] = $transction_bank_ac_from;
        $data['transction_bank_ifsc_from'] = $transction_bank_ifsc_from;
        $data['transction_bank_branch_from'] = $transction_bank_branch_from;
        $data['transction_bank_from_id'] = $transction_bank_from_id;
        $data['transction_bank_from_ac_id'] = $transction_bank_from_ac_id;
        $data['transction_bank_to'] = $transction_bank_to;
        $data['transction_bank_ac_to'] = $transction_bank_ac_to;
        $data['transction_bank_to_name'] = $transction_bank_to_name;
        $data['transction_bank_to_ac_no'] = $transction_bank_to_ac_no;
        $data['transction_bank_to_branch'] = $transction_bank_to_branch;
        $data['transction_bank_to_ifsc'] = $transction_bank_to_ifsc;
        $data['transction_date'] = $transction_date;
        $data['entry_date'] = date("Y-m-d", strtotime(convertDate($globaldate)));
        $data['entry_time'] = date("H:i:s");
        $data['created_by'] = $created_by;
        $data['created_by_id'] = $created_by_id;
        $data['created_at'] = date("Y-m-d " . $t . "", strtotime(convertDate($globaldate)));
        $data['app_login_user_id']=$Appuser;
        $data['is_app']=$isApp;
        $transcation = \App\Models\AllHeadTransaction::create($data);
        return true;
    }



    public function updateSsbDayBookAmount($amount, $account_number, $date)
    {
        $globaldate = $date;
        $entryTime = date("H:i:s");
        $entryDate = date("Y-m-d", strtotime(convertDate($date)));
        $getCurrentBranchRecord = SavingAccountTranscation::where('account_no', $account_number)->whereDate('created_at', $entryDate)->first();
        if (isset($getCurrentBranchRecord->id)) {
            $bResult = SavingAccountTranscation::find($getCurrentBranchRecord->id);
            $bData['opening_balance'] = $getCurrentBranchRecord->opening_balance - $amount;
            $bData['updated_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($globaldate)));
        }
        $getNextBranchRecord = SavingAccountTranscation::where('account_no', $account_number)->whereDate('created_at', '>', $entryDate)->orderby('created_at', 'ASC')
            ->get();
        if ($getNextBranchRecord) {
            foreach ($getNextBranchRecord as $key => $value) {
                $sResult = SavingAccountTranscation::find($value->id);
                $sData['opening_balance'] = $value->opening_balance - $amount;
                $sData['updated_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($globaldate)));
                $sResult->update($sData);
            }
        }
    }



    



    // public function submitGroupLoanPayment(Request $request)
    // {
    //     $response = array();

    //     $request = $request->all();

    //     if(isset($request["member_id"]) && $request["member_id"]!="" && isset($request["type"]) && $request["type"]!="" && isset($request["emi_amount"]) && $request["emi_amount"]!="" && isset($request["loan_id"]) && $request["loan_id"]!="" && isset($request["payment_mode"]) && $request["payment_mode"]!=""){
            
    //         // Get Member ID
    //         $member = Member::select('id','mobile_no','associate_code')->where('member_id',$request["member_id"])->first();

    //         if(isset($member->id)){

    //             $associate_no = $request["member_id"];
    //             $loan_id = $request["loan_id"];
    //             $loanId = $request["loan_id"];
    //             $emi_amount = $request["emi_amount"];
    //             $request['deposite_amount'] = $request["emi_amount"];
    //             $payment_mode = $request["payment_mode"];
    //             $type = $request["type"];
                
    //             $request["loan_associate_code"] = $member->associate_code;
                
    //             $member = Member::select('id', 'associate_app_status', 'associate_id')->where('member_id', $request["member_id"])->where('associate_status', 1)->where('is_block', 0)->count();
    //             $member2 = Member::select('id', 'associate_app_status', 'associate_id', 'branch_id', 'first_name', 'last_name')->where('member_id', $request["member_id"])->where('associate_status', 1)->where('is_block', 0)->first();
                
    //             $userid = $member2->id;
                
    //             $mLoan = Grouploans::with(['loanMember', 'loanBranch','loan'])->where('id', $loan_id)->first();

    //             $branchId = $mLoan->branch_id;
    //             $request['branch'] = $mLoan->branch_id;
    //             $request['associate_member_id'] = $mLoan->associate_member_id;
                
    //             $token = md5($request["member_id"]);
                
    //             if ($token == $request["token"]) {
                    
    //                 $request['loan_associate_name'] = $member2->first_name . ' ' . $member2->last_name;
    //                 $entryTime = date("H:i:s");


    //                $globaldate = checkMonthAvailability(date('d'), date('m'), date('Y'), $mLoan['loanBranch']->state_id);
    //                Session::put('created_at', date("Y-m-d " . $entryTime . "", strtotime(convertDate($globaldate))));

    //                //$globaldate = "12-11-2022";

    //                 Session::put('created_at', date("Y-m-d " . $entryTime . "", strtotime(convertDate($globaldate))));

    //                 $paymentDate = date("Y-m-d", strtotime(convertDate($globaldate)));
    //                 $ssbAccountDetails = SavingAccount::with(['ssbMember'])->where('member_id', $member2->id)->first();

    //                 $ssbAccountDetailsDeposit = SavingAccountTranscation::where('saving_account_id', $ssbAccountDetails->id)->sum('deposit');

    //                 $ssbAccountDetailsWithdrawal = SavingAccountTranscation::where('saving_account_id', $ssbAccountDetails->id)->sum('withdrawal');

    //                 $ssbAmont = $ssbAccountDetailsDeposit-$ssbAccountDetailsWithdrawal;

    //                 if ($ssbAmont < $emi_amount) {

    //                     $response["status"] = "Success";
    //                     $response["code"] = 201;
    //                     $response["messages"] = "Insufficient Balance!";
    //                     $response["data"] = array();

    //                     return response()->json($response);
    //                 } else {
                        
    //                         $loanId = $request['loan_id'];
    //                         $branchId = $branchId;

    //                         $stateid = getBranchState($mLoan['loanBranch']->name);
    //                         $globaldate = date('Y-m-d', strtotime(convertDate($globaldate)));
    //                         $getHeadSetting = \App\Models\HeadSetting::where('head_id', 33)->first();
    //                         $getGstSetting = \App\Models\GstSetting::where('state_id', $mLoan['loanBranch']->state_id)->where('applicable_date', '<=', $globaldate)->exists();
    //                         $getGstSettingno = \App\Models\GstSetting::select('id', 'gst_no')->where('state_id', $mLoan['loanBranch']->state_id)->where('applicable_date', '<=', $globaldate)->first();

    //                         $gstAmount = 0;

    //                         $LoanCreatedDate = date('Y-m-d', strtotime($mLoan->approve_date));
    //                         $LoanCreatedYear = date('Y', strtotime($mLoan->approve_date));
    //                         $LoanCreatedMonth = date('m', strtotime($mLoan->approve_date));
    //                         $LoanCreateDate = date('d', strtotime($mLoan->approve_date));
    //                         $currentDate = date('Y-m-d');
    //                         $CurrentDate = date('d');
    //                         $CurrentDateYear = date('Y');
    //                         $CurrentDateMonth = date('m');
    //                         $applicationDate = date('Y-m-d', strtotime(convertDate($globaldate)));
    //                         $applicationCurrentDate = date('d', strtotime(convertDate($globaldate)));
    //                         $applicationCurrentDateYear = date('Y', strtotime(convertDate($globaldate)));
    //                         $applicationCurrentDateMonth = date('m', strtotime(convertDate($globaldate)));
    //                         if ($mLoan->emi_option == 1) { //Month
    //                             $daysDiff = (($CurrentDateYear - $LoanCreatedYear) * 12) + ($CurrentDateMonth - $LoanCreatedMonth);
    //                             $daysDiff2 = today()->diffInDays($LoanCreatedDate);
    //                             $nextEmiDates = $this->nextEmiDates($daysDiff, $LoanCreatedDate);
    //                             $nextEmiDates2 = $this->nextEmiDatesDays($daysDiff2, $LoanCreatedDate);
    //                             $nextEmiDates3 = $this->nextEmiDates($mLoan->emi_period, $LoanCreatedDate);
    //                         }
    //                         if ($mLoan->emi_option == 2) { //Week
    //                             $daysDiff = today()->diffInDays($LoanCreatedDate);
    //                             $daysDiff = $daysDiff / 7;
    //                             $nextEmiDates2 = $this->nextEmiDatesDays($daysDiff, $LoanCreatedDate);
    //                             $nextEmiDates = $this->nextEmiDatesWeek($daysDiff, $LoanCreatedDate);
    //                         }
    //                         if ($mLoan->emi_option == 3) {  //Days
    //                             $daysDiff = today()->diffInDays($LoanCreatedDate);
    //                             $nextEmiDates = $this->nextEmiDatesDays($daysDiff, $LoanCreatedDate);
    //                         }
    //                         // $currentEmiDate = $nextEmiDates[$CurrentDate.'_'.$CurrentDateMonth.'_'.$CurrentDateYear];
    //                         $totalDayInterest = 0;
    //                         $totalDailyInterest = 0;
    //                         $newApplicationDate = explode('-', $applicationDate);
    //                         $StartnewDAte = $applicationCurrentDateYear . '-' . $applicationCurrentDateMonth . '-01';
    //                         $EndnewDAte = $applicationCurrentDateYear . '-' . $applicationCurrentDateMonth . '-31';
    //                         $dailyoutstandingAmount = 0;
    //                         $lastOutstanding = LoanEmisNew::where('loan_id', $mLoan->id)->where('loan_type', $mLoan->loan_type)->where('is_deleted', '0')->orderBy('id', 'desc')->first();
    //                         $lastOutstandingDate = LoanEmisNew::where('loan_id', $mLoan->id)->where('loan_type', $mLoan->loan_type)->where('is_deleted', '0')->pluck('emi_date')->toArray();
    //                         // $eniDateOutstandingArray = array_values($lastOutstandingDate);
    //                         $newDate = array();
    //                         //$checkDate = array_intersect($nextEmiDates,$lastOutstandingDate);
    //                         $deposit = $request['emi_amount'];
                            
    //                         if (isset($lastOutstanding->out_standing_amount)) {
    //                             $checkDateMonth = date('m', strtotime($lastOutstanding->emi_date));
    //                             $checkDateYear = date('Y', strtotime($lastOutstanding->emi_date));
    //                             $newstartDate = $checkDateYear . '-' .  $checkDateMonth . '-01';
    //                             $newEndDate = $checkDateYear . '-' .  $checkDateMonth . '-31';
    //                             $gapDayes =  Carbon\Carbon::parse($lastOutstanding->emi_date)->diff(Carbon\Carbon::parse($applicationDate))->format('%a');
    //                             $lastOutstanding2 = LoanEmisNew::where('loan_id', $mLoan->id)->where('loan_type', $mLoan->loan_type)->whereBetween('emi_date', [$newstartDate, $newEndDate])->where('is_deleted', '0')->sum('out_standing_amount');
    //                             $roi = ((($mLoan->ROI) / 365) * $lastOutstanding->out_standing_amount) / 100;
    //                             if ($mLoan->emi_option == 1 || $mLoan->emi_option == 2) {
    //                                 $roi = $roi * $gapDayes;
    //                                 $principal_amount = $deposit - $roi;
    //                                 if (!array_key_exists($applicationCurrentDate . '_' . $applicationCurrentDateMonth . '_' . $applicationCurrentDateYear, $nextEmiDates)) {
    //                                     $outstandingAmount = ($lastOutstanding->out_standing_amount - $deposit);
    //                                 } else {
    //                                     $preDate = current($nextEmiDates);
    //                                     $oldDate = $nextEmiDates[$applicationCurrentDate . '_' . $applicationCurrentDateMonth . '_' . $applicationCurrentDateYear];
    //                                     if ($mLoan->emi_option == 1) {
    //                                         $previousDate = Carbon\Carbon::parse($oldDate)->subMonth(1);
    //                                     }
    //                                     if ($mLoan->emi_option == 2) {
    //                                         $previousDate =  Carbon\Carbon::parse($oldDate)->subDays(7);
    //                                     }
    //                                     $pDate = date('Y-m-d', strtotime("+1 day", strtotime($previousDate)));
    //                                     if ($preDate == $applicationDate) {
    //                                         $aqmount = LoanEmisNew::where('loan_id', $mLoan->id)->whereBetween('emi_date', [$LoanCreatedDate, $applicationDate])->where('is_deleted', '0')->sum('roi_amount');
    //                                     } else {
    //                                         $aqmount = LoanEmisNew::where('loan_id', $mLoan->id)->whereBetween('emi_date', [$pDate, $applicationDate])->where('is_deleted', '0')->sum('roi_amount');
    //                                     }
    //                                     if ($aqmount > 0) {
    //                                         $outstandingAmount = ($lastOutstanding->out_standing_amount - $deposit + $roi   + $aqmount);
    //                                     } else {
    //                                         $outstandingAmount = ($lastOutstanding->out_standing_amount - $deposit + $roi);
    //                                     }
    //                                 }
    //                                 $dailyoutstandingAmount = $outstandingAmount + $roi;
    //                             } else {
    //                                 $principal_amount = $deposit - $roi;
    //                                 $outstandingAmount = ($lastOutstanding->out_standing_amount - $principal_amount);
    //                             }
    //                             $deposit =  $request['deposite_amount'];
    //                         } else {
    //                             $roi = ((($mLoan->ROI) / 365) * $mLoan->amount) / 100;
    //                             $gapDayes = Carbon\Carbon::parse($mLoan->approve_date)->diff( Carbon\Carbon::parse($applicationDate))->format('%a');
    //                             if ($mLoan->emi_option == 1 || $mLoan->emi_option == 2) //Monthly
    //                             {
    //                                 $roi = $roi * $gapDayes;
    //                                 $principal_amount = $deposit - $roi;
    //                                 if (!array_key_exists($applicationCurrentDate . '_' . $applicationCurrentDateMonth . '_' . $applicationCurrentDateYear, $nextEmiDates)) {
    //                                     $outstandingAmount = ($mLoan->amount - $deposit);
    //                                 } else {
    //                                     $outstandingAmount = ($mLoan->amount - $deposit + $roi);
    //                                 }
    //                                 $dailyoutstandingAmount = $outstandingAmount + $roi;
    //                             } else {
    //                                 $principal_amount = $deposit - $roi;
    //                                 $outstandingAmount = ($mLoan->amount - $principal_amount);
    //                             }
    //                             $deposit =  $request['deposite_amount'];
    //                             $dailyoutstandingAmount = $mLoan->amount + $roi;
    //                         }
    //                         $amountArraySsb = array(
    //                             '1' => $request['emi_amount']
    //                         );
    //                         if (isset(($ssbAccountDetails['ssbMember']))) {
    //                             $amount_deposit_by_name = $ssbAccountDetails['ssbMember']->first_name . ' ' . $ssbAccountDetails['ssbMember']->last_name;
    //                         } else {
    //                             $amount_deposit_by_name = NULL;
    //                         }
    //                         $dueAmount = $mLoan->due_amount - round($principal_amount);
    //                         $glResult = Grouploans::find($request['loan_id']);
    //                         $glData['credit_amount'] = $mLoan->credit_amount + $principal_amount;
    //                         $glData['due_amount'] = $dueAmount;
    //                         if ($dueAmount == 0) {
    //                             $glData['status'] = 3;
    //                         }
    //                         $glResult['received_emi_amount'] = $mLoan->received_emi_amount + $request['emi_amount'];
    //                         $glResult->update($glData);
    //                         $gmLoan = Memberloans::with('loanMember')->where('id', $mLoan->member_loan_id)
    //                             ->first();
    //                         $gmDueAmount = $gmLoan->due_amount - $principal_amount;
    //                         $mlResult = Memberloans::find($mLoan->member_loan_id);
    //                         $lData['credit_amount'] = $mLoan->credit_amount + $principal_amount;
    //                         $lData['due_amount'] = $gmDueAmount;

    //                         $mlResult->update($lData);
    //                         // add log
    //                         $postData = $_POST;
    //                         $enData = array(
    //                             "post_data" => $postData,
    //                             "lData" => $lData
    //                         );
    //                         $encodeDate = json_encode($enData);
    //                         // $arrs = array(
    //                         //     "load_id" => $loanId,
    //                         //     "type" => "7",
    //                         //     "account_head_id" => 0,
    //                         //     "user_id" => $userid,
    //                         //     "message" => "Group Loan Recovery - Loan EMI payment",
    //                         //     "data" => $encodeDate
    //                         // );
    //                         // DB::table('user_log')->insert($arrs);
    //                         // end log
    //                         if ($request["payment_mode"] == 0) {
    //                             $cheque_dd_no = NULL;
    //                             $online_payment_id = NULL;
    //                             $online_payment_by = NULL;
    //                             $bank_name = NULL;
    //                             $cheque_date = NULL;
    //                             $account_number = NULL;
    //                             $paymentMode = 4;
    //                             $ssbpaymentMode = 3;
    //                             $paymentDate = date("Y-m-d", strtotime(convertDate($globaldate)));;

    //                             $transDate = date("Y-m-d " . $entryTime . "", strtotime(convertDate($globaldate)));
    //                             $record1 = SavingAccountTranscation::where('account_no', $ssbAccountDetails->account_no)
    //                                 ->whereDate('created_at', '<=',  $transDate)->orderby('id', 'desc')
    //                                 ->first();
                                    
    //                             $ssb['saving_account_id'] = $ssbAccountDetails->id;
    //                             $ssb['account_no'] = $ssbAccountDetails->account_no;
    //                             $ssb['opening_balance'] = $record1->opening_balance - $request['emi_amount'];
    //                             $ssb['branch_id'] = $request['branch'];
    //                             $ssb['type'] = 9;
    //                             $ssb['deposit'] = 0;
    //                             $ssb['withdrawal'] = $request['deposite_amount'];
    //                             $ssb['description'] = 'Loan EMI Payment' . $mLoan->account_number;
    //                             $ssb['currency_code'] = 'INR';
    //                             $ssb['payment_type'] = 'DR';
    //                             $ssb['payment_mode'] = $ssbpaymentMode;
    //                             $ssb['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($globaldate)));
    //                             $ssb['is_app']=2;
    //                             $ssb['app_login_user_id']=$member2->id;
    //                             $ssbAccountTran = SavingAccountTranscation::create($ssb);
    //                             $ssb_transaction_id = $ssb_account_id_from = $ssbAccountTran->id;
    //                             // update saving account current balance
    //                             $ssbBalance = SavingAccount::find($ssbAccountDetails->id);
    //                             $ssbBalance->balance = $ssbAccountDetails->balance - $request['deposite_amount'];
    //                             $ssbBalance->save();
    //                             $request['ssb_id'] =$ssbAccountDetails->id;
    //                             $record2 = SavingAccountTranscation::where('account_no', $ssbAccountDetails->account_no)
    //                                 ->whereDate('created_at', '>', date("Y-m-d", strtotime(convertDate($globaldate))))->get();
    //                             foreach ($record2 as $key => $value) {
    //                                 $nsResult = SavingAccountTranscation::find($value->id);
    //                                 $nsResult['opening_balance'] = $value->opening_balance - $request['deposite_amount'];
    //                                 $nsResult['updated_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($globaldate)));
    //                                 $nsResult->update($nsResult);
    //                             }
    //                             $data['saving_account_transaction_id'] = $ssb_transaction_id;
    //                             $data['loan_id'] = $request['loan_id'];
    //                             $data['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($globaldate)));
    //                             $satRef = TransactionReferences::create($data);
    //                             $satRefId = $satRef->id;
    //                             $updateSsbDayBook = $this->updateSsbDayBookAmount($request['deposite_amount'], $ssbAccountDetails->account_no, date("Y-m-d " . $entryTime . "", strtotime(convertDate($globaldate))));
                                
    //                             //$ssbCreateTran = CommanAppController::createTransaction($satRefId, 1, $ssbAccountDetails->id, $ssbAccountDetails->member_id, $request['branch'], getBranchCode($request['branch'])->branch_code, $amountArraySsb, $paymentMode, $request['loan_associate_name'], $request['associate_member_id'], $ssbAccountDetails->account_no, $cheque_dd_no, $bank_name, $branch_name = NULL, $paymentDate, $online_payment_id, $online_payment_by, $ssbAccountDetails->id, 'DR');
    //                             //$totalbalance = $ssbAccountDetails->balance - $request['deposite_amount'];

    //                             // $createDayBook = CommanAppController::createDayBook($ssbCreateTran, $satRefId, 1, $ssbAccountDetails->member_investments_id, 0, $ssbAccountDetails->member_id, $totalbalance, 0, $request['deposite_amount'], 'Withdrawal from SSB', $request['account_number'], $request['branch'], getBranchCode($request['branch'])->branch_code, $amountArraySsb, $paymentMode, $request['loan_associate_name'], $request['associate_member_id'], $ssbAccountDetails->account_no, $cheque_dd_no, NULL, NULL, $paymentDate, $online_payment_by, $online_payment_by, $ssbAccountDetails->id, 'DR');

    //                             //$ssbCreateTran = CommanAppController::createTransaction($satRefId, 9, $request['loan_id'], $mLoan->applicant_id, $request['branch'], getBranchCode($request['branch'])->branch_code, $amountArraySsb, $paymentMode, $request['loan_associate_name'], $request['associate_member_id'], $mLoan->account_number, $cheque_dd_no, $bank_name, $branch_name = NULL, $paymentDate, $online_payment_id, $online_payment_by, NULL, 'CR');

    //                             // $createDayBook = CommanAppController::createDayBook($ssbCreateTran, $satRefId, 9, $request['loan_id'], 0, $mLoan->applicant_id, $dueAmount, $request['deposite_amount'], 0, 'Loan EMI deposite', $mLoan->account_number, $request['branch'], getBranchCode($request['branch'])->branch_code, $amountArraySsb, $paymentMode, $request['loan_associate_name'], $request['associate_member_id'], $mLoan->account_number, $cheque_dd_no, $bank_name, $branch_name = NULL, $paymentDate, $online_payment_by, $online_payment_by, NULL, 'CR');
    //                         }

                            

    //                         /*************** Head Implement ************/

    //                             $chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    //                             $v_no = "";
    //                             for ($i = 0; $i < 10; $i++) {
    //                                 $v_no .= $chars[mt_rand(0, strlen($chars) - 1)];
    //                             }
    //                             $roidayBookRef = CommanAppController::createBranchDayBookReference($roi + $principal_amount);
                                
    //                             $roibranchDayBook = CommanAppController::branchDayBookNew($roidayBookRef, $branchId, 5, 55, $loanId, $ssb_transaction_id, $request['associate_member_id'], $member_id = NULL, $branch_id_to = NULL, $branch_id_from = NULL, $roi + $principal_amount, $roi + $principal_amount, $roi + $principal_amount, '' . $mLoan->account_number . ' EMI collection', 'SSB A/C Cr ' . ($roi + $principal_amount) . '', 'SSB A/C Cr ' . ($roi + $principal_amount) . '', 'CR', 3, 'INR', $branchId, getBranchDetail($branchId)->name, $amount_from_id = NULL, $amount_from_name = NULL, $v_no, date("Y-m-d", strtotime(convertDate($globaldate))), $request['ssb_id'], $cheque_no = NULL, $cheque_date = NULL, $cheque_bank_from = NULL, $cheque_bank_ac_from = NULL, $cheque_bank_ifsc_from = NULL, $cheque_bank_branch_from = NULL, $cheque_bank_to = NULL, $cheque_bank_ac_to = NULL, $transction_no = NULL, $transction_bank_from = NULL, $transction_bank_ac_from = NULL, $transction_bank_ifsc_from = NULL, $transction_bank_branch_from = NULL, $transction_bank_to = NULL, $transction_bank_ac_to = NULL, $globaldate, $globaldate, $entry_time = NULL, 3, $userid, $is_contra = NULL, $contra_id = NULL, $globaldate, $ssb_account_tran_id_to = NULL, $ssb_account_id_from, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL,$member2->id,2);
                                
    //                             $allHeadTransaction = $this->createAllHeadTransaction($roidayBookRef, $branchId, NULL, NULL, 31, 5, 524, $loanId, $ssb_transaction_id, $member2->id, NULL, $branchId, $ssbAccountDetails->branch_id, $roi, $roi, $roi, '' . $mLoan->account_number . ' EMI collection', 'CR', 3, 'INR', $branchId, getBranchDetail($branchId)->name, $member2->id,  $member2->first_name . ' ' . $member2->last_name, $jv_unique_id = NULL, $v_no, date("Y-m-d", strtotime(convertDate($globaldate))), $ssbAccountDetails->id, NULL, NULL, $ssb_transaction_id, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 3, $userid,$member2->id,2);


    //                             $allHeadTransaction = $this->createAllHeadTransaction($roidayBookRef, $branchId, NULL, NULL, 56, 5, 55, $loanId, $ssb_transaction_id, $member2->id, NULL, $mLoan->branch_id, $ssbAccountDetails->branch_id, $roi, $roi, $roi, '' . $mLoan->account_number . ' EMI collection', 'DR', 3, 'INR', $branchId, getBranchDetail($branchId)->name, $member2->id, $member2->first_name . ' ' . $member2->last_name, $jv_unique_id = NULL, $v_no, date("Y-m-d", strtotime(convertDate($globaldate))), $ssbAccountDetails->id, NULL, NULL, $ssb_transaction_id, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 3, $userid,$member2->id,2);

    //                             $loan_head_id = $mLoan['loan']->head_id;

    //                             $allHeadTransaction = $this->createAllHeadTransaction($roidayBookRef, $branchId, NULL, NULL, $loan_head_id, 5, 55, $loanId, $ssb_transaction_id, $member2->id, NULL, $mLoan->branch_id, $ssbAccountDetails->branch_id, $principal_amount, $principal_amount, $principal_amount, '' . $mLoan->account_number . ' EMI collection', 'CR', 3, 'INR', $request['branch'], getBranchDetail($branchId)->name, $member2->id, $member2->first_name . ' ' . $member2->last_name, $jv_unique_id = NULL, $v_no, date("Y-m-d", strtotime(convertDate($globaldate))), $request['ssb_id'], NULL, NULL, $ssb_transaction_id, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 3, $userid,$member2->id,2);

    //                             $allHeadTransaction = $this->createAllHeadTransaction($roidayBookRef, $branchId, NULL, NULL, 56, 5, 55, $loanId, $ssb_transaction_id, $member2->id, NULL, $mLoan->branch_id, $ssbAccountDetails->branch_id, $principal_amount, $principal_amount, $principal_amount, '' . $mLoan->account_number . ' EMI collection', 'CR', 3, 'INR', $branchId, getBranchDetail($branchId)->name, $member2->id, $member2->first_name . ' ' . $member2->last_name, $jv_unique_id = NULL, $v_no, date("Y-m-d", strtotime(convertDate($globaldate))), $request['ssb_id'], NULL, NULL, $ssb_transaction_id, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 3, $userid,$member2->id,2);
                                
    //                             $principalmemberTransaction = CommanAppController::memberTransactionNew($roidayBookRef, 5, 55, $loanId, $ssb_transaction_id, $member2->id, $mLoan->applicant_id, $branchId, $bank_id = NULL, $account_id = NULL, $roi + $principal_amount, '' . $mLoan->account_number . ' EMI collection', 'DR', 3, 'INR', $branchId, getBranchDetail($branchId)->name, $amount_from_id = NULL, $amount_from_name = NULL, $v_no, date("Y-m-d", strtotime(convertDate($globaldate))), $request['ssb_id'], $cheque_no = NULL, $cheque_date = NULL, $cheque_bank_from = NULL, $cheque_bank_ac_from = NULL, $cheque_bank_ifsc_from = NULL, $cheque_bank_branch_from = NULL, $cheque_bank_to = NULL, $cheque_bank_ac_to = NULL, $transction_no = NULL, $transction_bank_from = NULL, $transction_bank_ac_from = NULL, $transction_bank_ifsc_from = NULL, $transction_bank_branch_from = NULL, $transction_bank_to = NULL, $transction_bank_ac_to = NULL, $globaldate, $globaldate, $entry_time = NULL, 3, $userid, $globaldate, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL,$member2->id,2);
                                
    //                             $createLoanDayBook = CommanAppController::createLoanDayBook($roidayBookRef, $roidayBookRef, 3, 0, $mLoan->id, $mLoan->id, $mLoan->account_number, $mLoan->applicant_id, $roi, $principal_amount, $outstandingAmount, $request['emi_amount'], 'Loan EMI deposite', $request['branch'], getBranchCode($branchId)->branch_code, 'CR', 'INR', $paymentMode, $cheque_dd_no, $bank_name, $branch_name = NULL, $paymentDate, $online_payment_id, NULL, 1, $cheque_date, $account_number, NULL, $member2->first_name, $member2->id, $branchId, $totalDailyInterest, $totalDayInterest, 0,$member2->id,2);

    //                             /*************** Head Implement ************/
    //                             /*---------- commission script  start  ---------*/
    //                             $daybookId = $createLoanDayBook;
    //                             $total_amount = $request['emi_amount'];
    //                             $percentage = 2;
    //                             $month = NULL;
    //                             $type_id = $request['loan_id'];
    //                             $type = 7;
    //                             $associate_id = $member2->id;
    //                             $branch_id = $branchId;
    //                             $commission_type = 0;
    //                             $associateDetail = Member::where('id', $associate_id)->first();
    //                             $carder = $associateDetail->current_carder_id;
    //                             $associate_exist = 0;
    //                             $percentInDecimal = $percentage / 100;
    //                             $commission_amount = round($percentInDecimal * $total_amount, 4);
    //                             $loan_associate_code = $request["loan_associate_code"];
    //                             $associateCommission['member_id'] = $associate_id;
    //                             $associateCommission['branch_id'] = $branch_id;
    //                             $associateCommission['type'] = $type;
    //                             $associateCommission['type_id'] = $type_id;
    //                             $associateCommission['day_book_id'] = $daybookId;
    //                             $associateCommission['total_amount'] = $total_amount;
    //                             $associateCommission['month'] = $month;
    //                             $associateCommission['commission_amount'] = $commission_amount;
    //                             $associateCommission['percentage'] = $percentage;
    //                             $associateCommission['commission_type'] = $commission_type;
    //                             $date = \App\Models\Daybook::where('id', $daybookId)->first();
    //                             $associateCommission['created_at'] = $date->created_at;
    //                             $associateCommission['pay_type'] = 4;
    //                             $associateCommission['carder_id'] = $carder;
    //                             $associateCommission['associate_exist'] = $associate_exist;
    //                             if ($loan_associate_code != 9999999) {
    //                                 $associateCommissionInsert = \App\Models\CommissionEntryLoan::create($associateCommission);
    //                             }

    //                             $response["status"] = "Success";
    //                             $response["code"] = 200;
    //                             $response["messages"] = "Group Loan Emi Paid successful!";
    //                             $response["data"] = array();
    //                             return response()->json($response);
    //                     }


    //             } else {

    //                 $response["status"] = "Error";
    //                 $response["code"] = 201;
    //                 $response["messages"] = "Token is misMatch!";
    //                 $response["data"] = array();
    //             }


    //         } else {
    //             $response["status"] = "Error";
    //             $response["code"] = 201;
    //             $response["messages"] = "Enter Valid Member Id";
    //             $response["data"] = array();
    //         }
    //     } else {
    //         $response["status"] = "Error";
    //         $response["code"] = "201";
    //         $response["messages"] = "Input parameter missing";
    //         $response["data"] = array();
    //     }
    //     return response()->json($response);
    // } 

    public function submitGroupLoanPayment(Request $request)
    {
     

        $response = array();
        $entryTime = date("H:i:s");
        $request = $request->all();

        if (isset($request["member_id"]) && $request["member_id"] != "" && isset($request["company_id"]) && $request["company_id"] != "" && isset($request["emi_amount"]) && $request["emi_amount"] != "" && isset($request["loan_id"]) && $request["loan_id"] != "" && isset($request["payment_mode"]) && $request["payment_mode"] != "") {

            // Get Member ID
            $member = Member::select('id', 'mobile_no', 'associate_code', 'member_id','branch_id')
            ->with([
                "savingAccount_Custom3" => function ($q) use ($request) {
                    $q->when($request['company_id'], function ($query) use ($request) {
                        $query->with(['savingAccountTransactionView' => function ($q) {
                            $q->select('saving_account_id', 'opening_balance', 'id');
                        }])
                        ->whereCompanyId($request['company_id'])
                        ->where("transaction_status", "1");
                    });
                }
            ])
          
            ->where('member_id', $request["member_id"])
            ->first();
            
               
            $status = $this->loanPaymentService->checkSavingAccountStatus($member);
         
            if(method_exists($status,'getStatusCode') && $status->getStatusCode() === 404)
            {
                
                return new EpassBookLoanResource($status);

            }    
            $model = new Grouploans;
            // dd($request["loan_id"]);
            $mLoan = $model::where('id',$request["loan_id"])->with('loanBranch')->where('status',4)->first();
         
            if(!isset($mLoan->account_number))
            {
                $response = [
                    'status' => 'error',
                    'code' => '201',
                     'data' => '',
                     'message' => 'Record Not Found'   
                ];
                return response()->json($response);

            }    
            $accountNumberDetails = [
                [
                    'account_number' => $mLoan->account_number,
                    'amount' => $request["emi_amount"]
                ]
            ];
            $stateid = getBranchState($mLoan['loanBranch']->name);
            $globaldate = checkMonthAvailability(date('d'), date('m'), date('Y'), $mLoan['loanBranch']->state_id);
            Session::put('created_at', date("Y-m-d " . $entryTime . "", strtotime(convertDate($globaldate))));


            if(isset($member->id))
            {   $epassBook = 1;
                $data =  $this->loanPaymentService->depositeLoanEmi(
                    $model,
                    $accountNumberDetails,
                    $globaldate,
                    $member,
                    $epassBook,
                    $mLoan
    
                );
          
                return new EpassBookLoanResource($data);
            }

            // if (isset($member->id)) {

            //     $associate_no = $request["member_id"];
            //     $loan_id = $request["loan_id"];
            //     $loanId = $request["loan_id"];
            //     $emi_amount = $request["emi_amount"];
            //     $request['deposite_amount'] = $request["emi_amount"];
            //     $payment_mode = $request["payment_mode"];
            //     $type = $request["type"];

            //     $request["loan_associate_code"] = $member->associate_code;

            //     $member = Member::select('id', 'associate_app_status', 'associate_id')->where('member_id', $request["member_id"])->where('associate_status', 1)->where('is_block', 0)->count();
            //     $member2 = Member::select('id', 'associate_app_status', 'associate_id', 'branch_id', 'first_name', 'last_name')->where('member_id', $request["member_id"])->where('associate_status', 1)->where('is_block', 0)->first();

            //     $userid = $member2->id;

            //     $mLoan = Grouploans::with(['loanMember', 'loanBranch', 'loan'])->where('id', $loan_id)->first();

            //     $branchId = $mLoan->branch_id;
            //     $request['branch'] = $mLoan->branch_id;
            //     $request['associate_member_id'] = $mLoan->associate_member_id;

            //     $token = md5($request["member_id"]);

            //     if ($token == $request["token"]) {

            //         $request['loan_associate_name'] = $member2->first_name . ' ' . $member2->last_name;
            //         $entryTime = date("H:i:s");


            //         $globaldate = checkMonthAvailability(date('d'), date('m'), date('Y'), $mLoan['loanBranch']->state_id);
            //         Session::put('created_at', date("Y-m-d " . $entryTime . "", strtotime(convertDate($globaldate))));

            //         //$globaldate = "12-11-2022";

            //         Session::put('created_at', date("Y-m-d " . $entryTime . "", strtotime(convertDate($globaldate))));

            //         $paymentDate = date("Y-m-d", strtotime(convertDate($globaldate)));
            //         $ssbAccountDetails = SavingAccount::with(['ssbMember'])->where('member_id', $member2->id)->first();

            //         $ssbAccountDetailsDeposit = SavingAccountTranscation::where('saving_account_id', $ssbAccountDetails->id)->sum('deposit');

            //         $ssbAccountDetailsWithdrawal = SavingAccountTranscation::where('saving_account_id', $ssbAccountDetails->id)->sum('withdrawal');

            //         $ssbAmont = $ssbAccountDetailsDeposit - $ssbAccountDetailsWithdrawal;

            //         if ($ssbAmont < $emi_amount) {

            //             $response["status"] = "Success";
            //             $response["code"] = 201;
            //             $response["messages"] = "Insufficient Balance!";
            //             $response["data"] = array();

            //             return response()->json($response);
            //         } else {

            //             $loanId = $request['loan_id'];
            //             $branchId = $branchId;

            //             $stateid = getBranchState($mLoan['loanBranch']->name);
            //             $globaldate = date('Y-m-d', strtotime(convertDate($globaldate)));
            //             $getHeadSetting = \App\Models\HeadSetting::where('head_id', 33)->first();
            //             $getGstSetting = \App\Models\GstSetting::where('state_id', $mLoan['loanBranch']->state_id)->where('applicable_date', '<=', $globaldate)->exists();
            //             $getGstSettingno = \App\Models\GstSetting::select('id', 'gst_no')->where('state_id', $mLoan['loanBranch']->state_id)->where('applicable_date', '<=', $globaldate)->first();

            //             $gstAmount = 0;

            //             $LoanCreatedDate = date('Y-m-d', strtotime($mLoan->approve_date));
            //             $LoanCreatedYear = date('Y', strtotime($mLoan->approve_date));
            //             $LoanCreatedMonth = date('m', strtotime($mLoan->approve_date));
            //             $LoanCreateDate = date('d', strtotime($mLoan->approve_date));
            //             $currentDate = date('Y-m-d');
            //             $CurrentDate = date('d');
            //             $CurrentDateYear = date('Y');
            //             $CurrentDateMonth = date('m');
            //             $applicationDate = date('Y-m-d', strtotime(convertDate($globaldate)));
            //             $applicationCurrentDate = date('d', strtotime(convertDate($globaldate)));
            //             $applicationCurrentDateYear = date('Y', strtotime(convertDate($globaldate)));
            //             $applicationCurrentDateMonth = date('m', strtotime(convertDate($globaldate)));
            //             if ($mLoan->emi_option == 1) { //Month
            //                 $daysDiff = (($CurrentDateYear - $LoanCreatedYear) * 12) + ($CurrentDateMonth - $LoanCreatedMonth);
            //                 $daysDiff2 = today()->diffInDays($LoanCreatedDate);
            //                 $nextEmiDates = $this->nextEmiDates($daysDiff, $LoanCreatedDate);
            //                 $nextEmiDates2 = $this->nextEmiDatesDays($daysDiff2, $LoanCreatedDate);
            //                 $nextEmiDates3 = $this->nextEmiDates($mLoan->emi_period, $LoanCreatedDate);
            //             }
            //             if ($mLoan->emi_option == 2) { //Week
            //                 $daysDiff = today()->diffInDays($LoanCreatedDate);
            //                 $daysDiff = $daysDiff / 7;
            //                 $nextEmiDates2 = $this->nextEmiDatesDays($daysDiff, $LoanCreatedDate);
            //                 $nextEmiDates = $this->nextEmiDatesWeek($daysDiff, $LoanCreatedDate);
            //             }
            //             if ($mLoan->emi_option == 3) {  //Days
            //                 $daysDiff = today()->diffInDays($LoanCreatedDate);
            //                 $nextEmiDates = $this->nextEmiDatesDays($daysDiff, $LoanCreatedDate);
            //             }
            //             // $currentEmiDate = $nextEmiDates[$CurrentDate.'_'.$CurrentDateMonth.'_'.$CurrentDateYear];
            //             $totalDayInterest = 0;
            //             $totalDailyInterest = 0;
            //             $newApplicationDate = explode('-', $applicationDate);
            //             $StartnewDAte = $applicationCurrentDateYear . '-' . $applicationCurrentDateMonth . '-01';
            //             $EndnewDAte = $applicationCurrentDateYear . '-' . $applicationCurrentDateMonth . '-31';
            //             $dailyoutstandingAmount = 0;
            //             $lastOutstanding = LoanEmisNew::where('loan_id', $mLoan->id)->where('loan_type', $mLoan->loan_type)->where('is_deleted', '0')->orderBy('id', 'desc')->first();
            //             $lastOutstandingDate = LoanEmisNew::where('loan_id', $mLoan->id)->where('loan_type', $mLoan->loan_type)->where('is_deleted', '0')->pluck('emi_date')->toArray();
            //             // $eniDateOutstandingArray = array_values($lastOutstandingDate);
            //             $newDate = array();
            //             //$checkDate = array_intersect($nextEmiDates,$lastOutstandingDate);
            //             $deposit = $request['emi_amount'];

            //             if (isset($lastOutstanding->out_standing_amount)) {
            //                 $checkDateMonth = date('m', strtotime($lastOutstanding->emi_date));
            //                 $checkDateYear = date('Y', strtotime($lastOutstanding->emi_date));
            //                 $newstartDate = $checkDateYear . '-' .  $checkDateMonth . '-01';
            //                 $newEndDate = $checkDateYear . '-' .  $checkDateMonth . '-31';
            //                 $gapDayes =  Carbon\Carbon::parse($lastOutstanding->emi_date)->diff(Carbon\Carbon::parse($applicationDate))->format('%a');
            //                 $lastOutstanding2 = LoanEmisNew::where('loan_id', $mLoan->id)->where('loan_type', $mLoan->loan_type)->whereBetween('emi_date', [$newstartDate, $newEndDate])->where('is_deleted', '0')->sum('out_standing_amount');
            //                 $roi = ((($mLoan->ROI) / 365) * $lastOutstanding->out_standing_amount) / 100;
            //                 if ($mLoan->emi_option == 1 || $mLoan->emi_option == 2) {
            //                     $roi = $roi * $gapDayes;
            //                     $principal_amount = $deposit - $roi;
            //                     if (!array_key_exists($applicationCurrentDate . '_' . $applicationCurrentDateMonth . '_' . $applicationCurrentDateYear, $nextEmiDates)) {
            //                         $outstandingAmount = ($lastOutstanding->out_standing_amount - $deposit);
            //                     } else {
            //                         $preDate = current($nextEmiDates);
            //                         $oldDate = $nextEmiDates[$applicationCurrentDate . '_' . $applicationCurrentDateMonth . '_' . $applicationCurrentDateYear];
            //                         if ($mLoan->emi_option == 1) {
            //                             $previousDate = Carbon\Carbon::parse($oldDate)->subMonth(1);
            //                         }
            //                         if ($mLoan->emi_option == 2) {
            //                             $previousDate =  Carbon\Carbon::parse($oldDate)->subDays(7);
            //                         }
            //                         $pDate = date('Y-m-d', strtotime("+1 day", strtotime($previousDate)));
            //                         if ($preDate == $applicationDate) {
            //                             $aqmount = LoanEmisNew::where('loan_id', $mLoan->id)->whereBetween('emi_date', [$LoanCreatedDate, $applicationDate])->where('is_deleted', '0')->sum('roi_amount');
            //                         } else {
            //                             $aqmount = LoanEmisNew::where('loan_id', $mLoan->id)->whereBetween('emi_date', [$pDate, $applicationDate])->where('is_deleted', '0')->sum('roi_amount');
            //                         }
            //                         if ($aqmount > 0) {
            //                             $outstandingAmount = ($lastOutstanding->out_standing_amount - $deposit + $roi   + $aqmount);
            //                         } else {
            //                             $outstandingAmount = ($lastOutstanding->out_standing_amount - $deposit + $roi);
            //                         }
            //                     }
            //                     $dailyoutstandingAmount = $outstandingAmount + $roi;
            //                 } else {
            //                     $principal_amount = $deposit - $roi;
            //                     $outstandingAmount = ($lastOutstanding->out_standing_amount - $principal_amount);
            //                 }
            //                 $deposit =  $request['deposite_amount'];
            //             } else {
            //                 $roi = ((($mLoan->ROI) / 365) * $mLoan->amount) / 100;
            //                 $gapDayes = Carbon\Carbon::parse($mLoan->approve_date)->diff(Carbon\Carbon::parse($applicationDate))->format('%a');
            //                 if ($mLoan->emi_option == 1 || $mLoan->emi_option == 2) //Monthly
            //                 {
            //                     $roi = $roi * $gapDayes;
            //                     $principal_amount = $deposit - $roi;
            //                     if (!array_key_exists($applicationCurrentDate . '_' . $applicationCurrentDateMonth . '_' . $applicationCurrentDateYear, $nextEmiDates)) {
            //                         $outstandingAmount = ($mLoan->amount - $deposit);
            //                     } else {
            //                         $outstandingAmount = ($mLoan->amount - $deposit + $roi);
            //                     }
            //                     $dailyoutstandingAmount = $outstandingAmount + $roi;
            //                 } else {
            //                     $principal_amount = $deposit - $roi;
            //                     $outstandingAmount = ($mLoan->amount - $principal_amount);
            //                 }
            //                 $deposit =  $request['deposite_amount'];
            //                 $dailyoutstandingAmount = $mLoan->amount + $roi;
            //             }
            //             $amountArraySsb = array(
            //                 '1' => $request['emi_amount']
            //             );
            //             if (isset(($ssbAccountDetails['ssbMember']))) {
            //                 $amount_deposit_by_name = $ssbAccountDetails['ssbMember']->first_name . ' ' . $ssbAccountDetails['ssbMember']->last_name;
            //             } else {
            //                 $amount_deposit_by_name = NULL;
            //             }
            //             $dueAmount = $mLoan->due_amount - round($principal_amount);
            //             $glResult = Grouploans::find($request['loan_id']);
            //             $glData['credit_amount'] = $mLoan->credit_amount + $principal_amount;
            //             $glData['due_amount'] = $dueAmount;
            //             if ($dueAmount == 0) {
            //                 $glData['status'] = 3;
            //             }
            //             $glResult['received_emi_amount'] = $mLoan->received_emi_amount + $request['emi_amount'];
            //             $glResult->update($glData);
            //             $gmLoan = Memberloans::with('loanMember')->where('id', $mLoan->member_loan_id)
            //                 ->first();
            //             $gmDueAmount = $gmLoan->due_amount - $principal_amount;
            //             $mlResult = Memberloans::find($mLoan->member_loan_id);
            //             $lData['credit_amount'] = $mLoan->credit_amount + $principal_amount;
            //             $lData['due_amount'] = $gmDueAmount;

            //             $mlResult->update($lData);
            //             // add log
            //             $postData = $_POST;
            //             $enData = array(
            //                 "post_data" => $postData,
            //                 "lData" => $lData
            //             );
            //             $encodeDate = json_encode($enData);
            //             // $arrs = array(
            //             //     "load_id" => $loanId,
            //             //     "type" => "7",
            //             //     "account_head_id" => 0,
            //             //     "user_id" => $userid,
            //             //     "message" => "Group Loan Recovery - Loan EMI payment",
            //             //     "data" => $encodeDate
            //             // );
            //             // DB::table('user_log')->insert($arrs);
            //             // end log
            //             if ($request["payment_mode"] == 0) {
            //                 $cheque_dd_no = NULL;
            //                 $online_payment_id = NULL;
            //                 $online_payment_by = NULL;
            //                 $bank_name = NULL;
            //                 $cheque_date = NULL;
            //                 $account_number = NULL;
            //                 $paymentMode = 4;
            //                 $ssbpaymentMode = 3;
            //                 $paymentDate = date("Y-m-d", strtotime(convertDate($globaldate)));;

            //                 $transDate = date("Y-m-d " . $entryTime . "", strtotime(convertDate($globaldate)));
            //                 $record1 = SavingAccountTranscation::where('account_no', $ssbAccountDetails->account_no)
            //                     ->whereDate('created_at', '<=',  $transDate)->orderby('id', 'desc')
            //                     ->first();

            //                 $ssb['saving_account_id'] = $ssbAccountDetails->id;
            //                 $ssb['account_no'] = $ssbAccountDetails->account_no;
            //                 $ssb['opening_balance'] = $record1->opening_balance - $request['emi_amount'];
            //                 $ssb['branch_id'] = $request['branch'];
            //                 $ssb['type'] = 9;
            //                 $ssb['deposit'] = 0;
            //                 $ssb['withdrawal'] = $request['deposite_amount'];
            //                 $ssb['description'] = 'Loan EMI Payment' . $mLoan->account_number;
            //                 $ssb['currency_code'] = 'INR';
            //                 $ssb['payment_type'] = 'DR';
            //                 $ssb['payment_mode'] = $ssbpaymentMode;
            //                 $ssb['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($globaldate)));
            //                 $ssb['is_app'] = 2;
            //                 $ssb['app_login_user_id'] = $member2->id;
            //                 $ssbAccountTran = SavingAccountTranscation::create($ssb);
            //                 $ssb_transaction_id = $ssb_account_id_from = $ssbAccountTran->id;
            //                 // update saving account current balance
            //                 $ssbBalance = SavingAccount::find($ssbAccountDetails->id);
            //                 $ssbBalance->balance = $ssbAccountDetails->balance - $request['deposite_amount'];
            //                 $ssbBalance->save();
            //                 $request['ssb_id'] = $ssbAccountDetails->id;
            //                 $record2 = SavingAccountTranscation::where('account_no', $ssbAccountDetails->account_no)
            //                     ->whereDate('created_at', '>', date("Y-m-d", strtotime(convertDate($globaldate))))->get();
            //                 foreach ($record2 as $key => $value) {
            //                     $nsResult = SavingAccountTranscation::find($value->id);
            //                     $nsResult['opening_balance'] = $value->opening_balance - $request['deposite_amount'];
            //                     $nsResult['updated_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($globaldate)));
            //                     $nsResult->update($nsResult);
            //                 }
            //                 $data['saving_account_transaction_id'] = $ssb_transaction_id;
            //                 $data['loan_id'] = $request['loan_id'];
            //                 $data['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($globaldate)));
            //                 $satRef = TransactionReferences::create($data);
            //                 $satRefId = $satRef->id;
            //                 $updateSsbDayBook = $this->updateSsbDayBookAmount($request['deposite_amount'], $ssbAccountDetails->account_no, date("Y-m-d " . $entryTime . "", strtotime(convertDate($globaldate))));

            //                 //$ssbCreateTran = CommanAppController::createTransaction($satRefId, 1, $ssbAccountDetails->id, $ssbAccountDetails->member_id, $request['branch'], getBranchCode($request['branch'])->branch_code, $amountArraySsb, $paymentMode, $request['loan_associate_name'], $request['associate_member_id'], $ssbAccountDetails->account_no, $cheque_dd_no, $bank_name, $branch_name = NULL, $paymentDate, $online_payment_id, $online_payment_by, $ssbAccountDetails->id, 'DR');
            //                 //$totalbalance = $ssbAccountDetails->balance - $request['deposite_amount'];

            //                 // $createDayBook = CommanAppController::createDayBook($ssbCreateTran, $satRefId, 1, $ssbAccountDetails->member_investments_id, 0, $ssbAccountDetails->member_id, $totalbalance, 0, $request['deposite_amount'], 'Withdrawal from SSB', $request['account_number'], $request['branch'], getBranchCode($request['branch'])->branch_code, $amountArraySsb, $paymentMode, $request['loan_associate_name'], $request['associate_member_id'], $ssbAccountDetails->account_no, $cheque_dd_no, NULL, NULL, $paymentDate, $online_payment_by, $online_payment_by, $ssbAccountDetails->id, 'DR');

            //                 //$ssbCreateTran = CommanAppController::createTransaction($satRefId, 9, $request['loan_id'], $mLoan->applicant_id, $request['branch'], getBranchCode($request['branch'])->branch_code, $amountArraySsb, $paymentMode, $request['loan_associate_name'], $request['associate_member_id'], $mLoan->account_number, $cheque_dd_no, $bank_name, $branch_name = NULL, $paymentDate, $online_payment_id, $online_payment_by, NULL, 'CR');

            //                 // $createDayBook = CommanAppController::createDayBook($ssbCreateTran, $satRefId, 9, $request['loan_id'], 0, $mLoan->applicant_id, $dueAmount, $request['deposite_amount'], 0, 'Loan EMI deposite', $mLoan->account_number, $request['branch'], getBranchCode($request['branch'])->branch_code, $amountArraySsb, $paymentMode, $request['loan_associate_name'], $request['associate_member_id'], $mLoan->account_number, $cheque_dd_no, $bank_name, $branch_name = NULL, $paymentDate, $online_payment_by, $online_payment_by, NULL, 'CR');
            //             }



            //             /*************** Head Implement ************/

            //             $chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
            //             $v_no = "";
            //             for ($i = 0; $i < 10; $i++) {
            //                 $v_no .= $chars[mt_rand(0, strlen($chars) - 1)];
            //             }
            //             $roidayBookRef = CommanAppController::createBranchDayBookReference($roi + $principal_amount);

            //             $roibranchDayBook = CommanAppController::branchDayBookNew($roidayBookRef, $branchId, 5, 55, $loanId, $ssb_transaction_id, $request['associate_member_id'], $member_id = NULL, $branch_id_to = NULL, $branch_id_from = NULL, $roi + $principal_amount, $roi + $principal_amount, $roi + $principal_amount, '' . $mLoan->account_number . ' EMI collection', 'SSB A/C Cr ' . ($roi + $principal_amount) . '', 'SSB A/C Cr ' . ($roi + $principal_amount) . '', 'CR', 3, 'INR', $branchId, getBranchDetail($branchId)->name, $amount_from_id = NULL, $amount_from_name = NULL, $v_no, date("Y-m-d", strtotime(convertDate($globaldate))), $request['ssb_id'], $cheque_no = NULL, $cheque_date = NULL, $cheque_bank_from = NULL, $cheque_bank_ac_from = NULL, $cheque_bank_ifsc_from = NULL, $cheque_bank_branch_from = NULL, $cheque_bank_to = NULL, $cheque_bank_ac_to = NULL, $transction_no = NULL, $transction_bank_from = NULL, $transction_bank_ac_from = NULL, $transction_bank_ifsc_from = NULL, $transction_bank_branch_from = NULL, $transction_bank_to = NULL, $transction_bank_ac_to = NULL, $globaldate, $globaldate, $entry_time = NULL, 3, $userid, $is_contra = NULL, $contra_id = NULL, $globaldate, $ssb_account_tran_id_to = NULL, $ssb_account_id_from, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL, $member2->id, 2);

            //             $allHeadTransaction = $this->createAllHeadTransaction($roidayBookRef, $branchId, NULL, NULL, 31, 5, 524, $loanId, $ssb_transaction_id, $member2->id, NULL, $branchId, $ssbAccountDetails->branch_id, $roi, $roi, $roi, '' . $mLoan->account_number . ' EMI collection', 'CR', 3, 'INR', $branchId, getBranchDetail($branchId)->name, $member2->id,  $member2->first_name . ' ' . $member2->last_name, $jv_unique_id = NULL, $v_no, date("Y-m-d", strtotime(convertDate($globaldate))), $ssbAccountDetails->id, NULL, NULL, $ssb_transaction_id, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 3, $userid, $member2->id, 2);


            //             $allHeadTransaction = $this->createAllHeadTransaction($roidayBookRef, $branchId, NULL, NULL, 56, 5, 55, $loanId, $ssb_transaction_id, $member2->id, NULL, $mLoan->branch_id, $ssbAccountDetails->branch_id, $roi, $roi, $roi, '' . $mLoan->account_number . ' EMI collection', 'DR', 3, 'INR', $branchId, getBranchDetail($branchId)->name, $member2->id, $member2->first_name . ' ' . $member2->last_name, $jv_unique_id = NULL, $v_no, date("Y-m-d", strtotime(convertDate($globaldate))), $ssbAccountDetails->id, NULL, NULL, $ssb_transaction_id, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 3, $userid, $member2->id, 2);

            //             $loan_head_id = $mLoan['loan']->head_id;

            //             $allHeadTransaction = $this->createAllHeadTransaction($roidayBookRef, $branchId, NULL, NULL, $loan_head_id, 5, 55, $loanId, $ssb_transaction_id, $member2->id, NULL, $mLoan->branch_id, $ssbAccountDetails->branch_id, $principal_amount, $principal_amount, $principal_amount, '' . $mLoan->account_number . ' EMI collection', 'CR', 3, 'INR', $request['branch'], getBranchDetail($branchId)->name, $member2->id, $member2->first_name . ' ' . $member2->last_name, $jv_unique_id = NULL, $v_no, date("Y-m-d", strtotime(convertDate($globaldate))), $request['ssb_id'], NULL, NULL, $ssb_transaction_id, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 3, $userid, $member2->id, 2);

            //             $allHeadTransaction = $this->createAllHeadTransaction($roidayBookRef, $branchId, NULL, NULL, 56, 5, 55, $loanId, $ssb_transaction_id, $member2->id, NULL, $mLoan->branch_id, $ssbAccountDetails->branch_id, $principal_amount, $principal_amount, $principal_amount, '' . $mLoan->account_number . ' EMI collection', 'CR', 3, 'INR', $branchId, getBranchDetail($branchId)->name, $member2->id, $member2->first_name . ' ' . $member2->last_name, $jv_unique_id = NULL, $v_no, date("Y-m-d", strtotime(convertDate($globaldate))), $request['ssb_id'], NULL, NULL, $ssb_transaction_id, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 3, $userid, $member2->id, 2);

            //             $principalmemberTransaction = CommanAppController::memberTransactionNew($roidayBookRef, 5, 55, $loanId, $ssb_transaction_id, $member2->id, $mLoan->applicant_id, $branchId, $bank_id = NULL, $account_id = NULL, $roi + $principal_amount, '' . $mLoan->account_number . ' EMI collection', 'DR', 3, 'INR', $branchId, getBranchDetail($branchId)->name, $amount_from_id = NULL, $amount_from_name = NULL, $v_no, date("Y-m-d", strtotime(convertDate($globaldate))), $request['ssb_id'], $cheque_no = NULL, $cheque_date = NULL, $cheque_bank_from = NULL, $cheque_bank_ac_from = NULL, $cheque_bank_ifsc_from = NULL, $cheque_bank_branch_from = NULL, $cheque_bank_to = NULL, $cheque_bank_ac_to = NULL, $transction_no = NULL, $transction_bank_from = NULL, $transction_bank_ac_from = NULL, $transction_bank_ifsc_from = NULL, $transction_bank_branch_from = NULL, $transction_bank_to = NULL, $transction_bank_ac_to = NULL, $globaldate, $globaldate, $entry_time = NULL, 3, $userid, $globaldate, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL, $member2->id, 2);

            //             $createLoanDayBook = CommanAppController::createLoanDayBook($roidayBookRef, $roidayBookRef, 3, 0, $mLoan->id, $mLoan->id, $mLoan->account_number, $mLoan->applicant_id, $roi, $principal_amount, $outstandingAmount, $request['emi_amount'], 'Loan EMI deposite', $request['branch'], getBranchCode($branchId)->branch_code, 'CR', 'INR', $paymentMode, $cheque_dd_no, $bank_name, $branch_name = NULL, $paymentDate, $online_payment_id, NULL, 1, $cheque_date, $account_number, NULL, $member2->first_name, $member2->id, $branchId, $totalDailyInterest, $totalDayInterest, 0, $member2->id, 2);

            //             /*************** Head Implement ************/
            //             /*---------- commission script  start  ---------*/
            //             $daybookId = $createLoanDayBook;
            //             $total_amount = $request['emi_amount'];
            //             $percentage = 2;
            //             $month = NULL;
            //             $type_id = $request['loan_id'];
            //             $type = 7;
            //             $associate_id = $member2->id;
            //             $branch_id = $branchId;
            //             $commission_type = 0;
            //             $associateDetail = Member::where('id', $associate_id)->first();
            //             $carder = $associateDetail->current_carder_id;
            //             $associate_exist = 0;
            //             $percentInDecimal = $percentage / 100;
            //             $commission_amount = round($percentInDecimal * $total_amount, 4);
            //             $loan_associate_code = $request["loan_associate_code"];
            //             $associateCommission['member_id'] = $associate_id;
            //             $associateCommission['branch_id'] = $branch_id;
            //             $associateCommission['type'] = $type;
            //             $associateCommission['type_id'] = $type_id;
            //             $associateCommission['day_book_id'] = $daybookId;
            //             $associateCommission['total_amount'] = $total_amount;
            //             $associateCommission['month'] = $month;
            //             $associateCommission['commission_amount'] = $commission_amount;
            //             $associateCommission['percentage'] = $percentage;
            //             $associateCommission['commission_type'] = $commission_type;
            //             $date = \App\Models\Daybook::where('id', $daybookId)->first();
            //             $associateCommission['created_at'] = $date->created_at;
            //             $associateCommission['pay_type'] = 4;
            //             $associateCommission['carder_id'] = $carder;
            //             $associateCommission['associate_exist'] = $associate_exist;
            //             if ($loan_associate_code != 9999999) {
            //                 $associateCommissionInsert = \App\Models\CommissionEntryLoan::create($associateCommission);
            //             }

            //             $response["status"] = "Success";
            //             $response["code"] = 200;
            //             $response["messages"] = "Group Loan Emi Paid successful!";
            //             $response["data"] = array();
            //             return response()->json($response);
            //         }
            //     } else {

            //         $response["status"] = "Error";
            //         $response["code"] = 201;
            //         $response["messages"] = "Token is misMatch!";
            //         $response["data"] = array();
            //     }
            // } else {
                $response["status"] = "Error";
                $response["code"] = 201;
                $response["messages"] = "Enter Valid Member Id";
                $response["data"] = array();
            }
        // } else {
        //     $response["status"] = "Error";
        //     $response["code"] = "201";
        //     $response["messages"] = "Input parameter missing";
        //     $response["data"] = array();
        // }
        return response()->json($response);
    }

    public function notificationReadUpdate(Request $request)
    {
        $response = array();

        $input = $request->all();
       

        if(isset($input["member_id"]) && $input["member_id"]!="" && isset($input["id"]) && $input["id"]!="" && isset($input["token"]) && $input["token"]!=""){
            
            // Get Member ID
            $member = Member::select('id','mobile_no','member_id')->where('member_id',$input["member_id"])->first();
            if(isset($member->id)){

                $token = md5($member->member_id);
                if($token == $request->token){

                    $notificationUpdate = Notification::where('id',$input["id"])->update([ 'is_read' => '1' ]);

                    $response["status"] = "Success";
                    $response["code"] = 200;
                    $response["messages"] = 'Notification Update Sucessfully!';
                    $response["data"] = array(); 
                }
                else{
                    
                    $response["status"] = "Error";
                    $response["code"] = 201;
                    $response["messages"] = 'API token mismatch!';
                    $response["data"] = array(); 
                }
                
            } else {
                $response["status"] = "Error";
                $response["code"] = 201;
                $response["messages"] = "Enter Valid Member Id";
                $response["data"] = array(); 
            }
        } else {
            $response["status"] = "Error";
            $response["code"] = "201";
            $response["messages"] = "Input parameter missing";
            $response["data"] = array(); 
        }
        return response()->json($response);
    } 


    public function lmbu(){

    }
    
}