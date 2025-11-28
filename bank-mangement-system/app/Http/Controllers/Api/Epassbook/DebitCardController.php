<?php

namespace App\Http\Controllers\Api\Epassbook;
use DB;
use URL;
use Session;
use DateTime;
use Validator;
use Carbon\Carbon;
use App\Services\Sms; 
use Illuminate\Http\Request; 
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Response;
use App\Models\Member; 
use App\Models\SavingAccount; 




class DebitCardController extends Controller
{
    public function __construct()
    {

    }

    /**
     * submit_loan_payment_emi.
     *
     * @param  \App\Request  $request
     * @return \Illuminate\Http\Response
     */

    /**
     * SSb Account Debit card amount get 
     *
     * @param  \App\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function checkSSBAccountBalance(Request $request)
    {
        $response = array();

        $input = $request->all();
        $notification_count=0;
        try{
            if(isset($input["member_id"]) && $input["member_id"]!=""){
            
                // Get Member ID
                // $member = Member::select('id')->where('member_id',$input["member_id"])->first();
                $member = Member::with(['savingAccount_Custom'=>function($q){
                    $q->with('savingAccountBalance');
                }])->select('id','member_id')->where('member_id',$input["member_id"])->first();
          
                $balance =  $member['savingAccount_Custom']['savingAccountBalance']->sum('deposit') - $member['savingAccount_Custom']['savingAccountBalance']->sum('withdrawal');
                $amounts  =  number_format((float) $balance, 2, '.', '');
                if(isset($member->id)){
    
                    // Get SSB Account ID
                    $savingAccountDetails = SavingAccount::select('id')->where('member_id',$member->id)->first();
                    $checkDebitCardExist = \App\Models\DebitCard::where('ssb_id',$savingAccountDetails->id)->where('is_block',1)->where('status',1)->exists();
                    $notification_count = \App\Models\Notification::where("user_id",$member->id)->where('is_read','0')->count('id');
    
                    if(isset($savingAccountDetails->id)){
                        // Get SSB Account Setting
                        $ssb_account_setting = DB::table("ssb_account_setting")->where("user_type","1")->where("plan_type","1")->first();
                        $ssb_setting_amount = 0;
                        if(isset($ssb_account_setting->amount)){
                            $ssb_setting_amount = $ssb_account_setting->amount;
                        }  
                        $getRequestLimit = \App\Models\DebitCardAmountSetting::where("duration","2")->where("status","1")->first();
                        $min_request_amount = 0;
                        $max_request_amount = 0;
                        if(isset($ssb_account_setting->amount) && isset($getRequestLimit->max_amount)){
                            $sumAmountDebitRequest = \App\Models\SavingAccountTranscation::where("saving_account_id",$savingAccountDetails->id)->where('is_deleted',0)->where('type',15)->where("payment_type","DR")->where(\DB::raw('MONTH(created_at)'),date('m'))->sum("withdrawal");
                            
                            $max_request_amount = $getRequestLimit->max_amount;
                            if($sumAmountDebitRequest>0)
                            {
                                $max_request_amount = $getRequestLimit->max_amount-$sumAmountDebitRequest;
                            }
                            $min_request_amount =  $getRequestLimit->min_amount;
                            
                        } 
    
                        $maxAmount = $amounts - $ssb_setting_amount;
    
                        if($maxAmount > 0){
                            $maxAmount = $maxAmount;
                            $is_button_show = "1";
                        } else {
                            $maxAmount = 0;
                            $is_button_show = "0";
                        }
    
                        $arr = array("amounts" => number_format((float)$amounts, 2, '.', ''),
                                    "max_amount" => number_format((float)$maxAmount, 2, '.', ''),
                                    "min_request_amount" => number_format((float)$min_request_amount, 2, '.', ''),
                                    "max_request_amount" => number_format((float)$max_request_amount, 2, '.', ''),
                                    "is_button_show" => $is_button_show,
                                    );
    
                        $response["status"] = "Success";
                        $response["is_card_available"] = $checkDebitCardExist;
                        $response["code"] = 200;
                        $response["messages"] = "Data";
                        $response["data"] = $arr;
                        $response["notification_count"] = $notification_count;
    
                    } else {
                        $response["status"] = "Error";
                        $response["code"] = 201;
                        $response["messages"] = "This user has no saving account";
                        $response["data"] = array();
                        $response["notification_count"] = $notification_count;
                    }
    
    
                } else {
                    $response["status"] = "Error";
                    $response["code"] = 201;
                    $response["messages"] = "Enter Valid Member Id";
                    $response["data"] = array();
                    $response["notification_count"] = $notification_count;
                }
    
            } else {
                $response["status"] = "Error";
                $response["code"] = 201;
                $response["messages"] = "Input parameter missing";
                $response["data"] = array();
                $response["notification_count"] = $notification_count;
            }
        }catch(\Exception $error)
        {
            $response["status"] = "Error";
            $response["code"] = 201;
            $response["messages"] = $error->getMessage();
            $response["data"] = array();
            $response["notification_count"] = $notification_count;
          
        }
       

        return response()->json($response);
    }


    
    public function sendDebitCardAmount(Request $request)
    {

        $response = array();

        $input = $request->all();

        if(isset($input["member_id"]) && $input["member_id"]!="" && isset($input["amount"]) && $input["amount"]!=""){
            
            // Get Member ID
            //$member = Member::select('id')->where('member_id',$input["member_id"])->first();
            $member = Member::with(['savingAccount_Custom'=>function($q){
                $q->with('savingAccountBalance');
            }])->select('id','member_id')->where('member_id',$input["member_id"])->first();

            $balance =  $member['savingAccount_Custom']['savingAccountBalance']->sum('deposit') - $member['savingAccount_Custom']['savingAccountBalance']->sum('withdrawal');
            $amounts_current  =  number_format((float) $balance, 2, '.', '');

            if(isset($member->id))
            {
                // Get SSB Account ID
                $savingAccountDetails = SavingAccount::where('member_id',$member->id)->first(['id', 'member_id','account_no','member_id','associate_id','branch_id','balance']);

                if(isset($savingAccountDetails->id))
                {
                    //echo '1';
                    
                    //$url_neft_uat  = "https://apibankingone.icicibank.com/api/v1/composite-payment";
                    $ac = \App\Models\DebitCard::where('status',1)->where('is_block','!=',2)->where('running_transaction',0)->where("ssb_id",$savingAccountDetails->id)->first();
                        if(isset($ac->id))
                        {
                          //  echo '2';
                            $ac = $ac->toArray();
                            $amount = $input["amount"];
                            $ssb_account_setting = DB::table("ssb_account_setting")->where("user_type","1")->where("plan_type","1")->first();
                            $ssb_setting_amount = 0;
                            if(isset($ssb_account_setting->amount)){
                                $ssb_setting_amount = $ssb_account_setting->amount;
                            }  
                            $chk=0;
                            $currentAvailable = $balance-$ssb_setting_amount;

                            if($currentAvailable  < $amount)
                            {
                               // echo '22';
                                $response["status"] = "Error";
                                $response["code"] = 201;
                                $response["message"] = "Your account has insufficient balance for this transaction. Current balace is ".$currentAvailable;
                                $response["data"] = array();
                                $chk++;
                            }
                                $getRequestLimit = \App\Models\DebitCardAmountSetting::where("duration","2")->where("status","1")->first();
                                $min_request_amount = 0;
                                $max_request_amount = 0;
                                if($currentAvailable>0)
                                {
                                    $sumAmountDebitRequest = \App\Models\SavingAccountTranscation::where("saving_account_id",$savingAccountDetails->id)->where('is_deleted',0)->where('type',15)->where("payment_type","DR")->where(\DB::raw('MONTH(created_at)'),date('m'))->sum("withdrawal");
                                    
                                    //echo $sumAmountDebitRequest;die;
                                    $max_request_amount = $getRequestLimit->max_amount;
                                    if($sumAmountDebitRequest>0)
                                    {
                                        $max_request_amount = $getRequestLimit->max_amount-$sumAmountDebitRequest;
                                    }
                                    $min_request_amount =  $getRequestLimit->min_amount;
                                    
                                }
                               // echo $amount.'<'.$min_request_amount;
                                if($min_request_amount>$amount)
                                {
                                   // echo '23';
                                    $response["status"] = "Error";
                                    $response["code"] = 201;
                                    $response["message"] = "Min Debit Amount is ".$min_request_amount;
                                    $response["data"] = array();
                                    $chk++;
                                }
                                if($amount>$max_request_amount)
                                {
                                   // echo '24';
                                    $response["status"] = "Error";
                                    $response["code"] = 201;
                                    $response["message"] = "you have exceeded the monthly limit. Limit is ".$getRequestLimit->max_amount;
                                    $response["data"] = array();
                                    $chk++;
                                }

                        

                            

                            /*no nneed  $sumAmount = \App\Models\SavingAccountTranscation::with(['savingAc' => function ($q)
                            {
                                $q->select('id', 'member_id','account_no','member_id','associate_id','branch_id','balance');
                            }
                            ])->where('saving_account_id', $savingAccountDetails->id)->orderBy('id', 'desc')->first();
                            */

                            

                           /*no nneed  $amountSend =number_format((float)FLOOR($sumAmount->opening_balance) - $amount, 0, '.', '') ;  
                            $checkAmount =number_format((float)FLOOR($sumAmount->opening_balance), 0, '.', '') ; 
                            */
                          

                            if ($chk==0)
                            {
                               // echo '1';
                                /*
                                //$memberId = getMemberData($savingAccountDetails->member_id);
                                date_default_timezone_set("Asia/Calcutta");
                                $date = Carbon::now()->format('dmYhis');
                                $trsNo = $member->member_id . $date;

                                $post_neft = [
                                    "tranRefNo" => $trsNo,
                                    "amount" =>$amountSend,
                                    "senderAcctNo" => "675105601216",
                                    "beneAccNo" =>$ac['card_no'],
                                    "beneIFSC" => "ICIC0000106",
                                    "beneName" => "SHERABHA",
                                    "narration1" => "1235678",
                                    "crpId" => "SAMRADDH05102020",
                                    "crpUsr" => "AMRENDRA",
                                    "aggrName" => "SAMRADDHB",
                                    "aggrId" => "CUST0675",
                                    "urn" => "SR209617949",
                                    "txnType" => "RGS",
                                ];
                        
                                //dd($post_neft);
                                $apostData = json_encode($post_neft);
                                
                                $sessionKey = 1234567890123456; //hash('MD5', time(), true); //16 byte session key

                                $fp= fopen("/home/mysamraddh/public_html/core/debit-card/LiveCert.txt","r");
                                $pub_key_string=fread($fp,4096);


                                fclose($fp);
                                openssl_get_publickey($pub_key_string);
                                openssl_public_encrypt($sessionKey,$encryptedKey,$pub_key_string); // RSA

                                $iv = 1234567890123456; //str_repeat("\0", 16);

                                $encryptedData = openssl_encrypt($apostData, 'aes-128-cbc', $sessionKey, OPENSSL_RAW_DATA, $iv); // AES
                                
                                $request = [
                                    "requestId"=> "req_".time(),
                                    "encryptedKey"=> base64_encode($encryptedKey),
                                    "iv"=> base64_encode($iv),
                                    "encryptedData"=> base64_encode($encryptedData),
                                    "oaepHashingAlgorithm"=> "NONE",
                                    "service"=> "",
                                    "clientInfo"=> "",
                                    "optionalParam"=> ""
                                ];
                        

                                $apostData = json_encode($request);
                                
                                $httpUrl = $url_neft_uat;
                                
                                $headers = array(
                                    "cache-control: no-cache",
                                    "accept: application/json",
                                    "content-type: application/json",
                                    "apikey: YqDT8sE0XnGTXvGAHslvTxv2lVzwIRPw",
                                    "x-priority:0010"
                                );
                        


                                $acurl = curl_init();
                                curl_setopt_array($acurl, array(
                                    CURLOPT_URL => $httpUrl,
                                    CURLOPT_RETURNTRANSFER => true,
                                    CURLOPT_ENCODING => "",
                                    CURLOPT_MAXREDIRS => 10,
                                    CURLOPT_TIMEOUT => 300,
                                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                                    CURLOPT_CUSTOMREQUEST => "POST",
                                    CURLOPT_POSTFIELDS => $apostData,
                                    CURLOPT_HTTPHEADER => $headers,
                                ));
                                
                                $aresponse = curl_exec($acurl);
                                $aerr = curl_error($acurl);
                                $httpcode = curl_getinfo($acurl, CURLINFO_HTTP_CODE);

                                if ($aerr) {
                                    echo "cURL Error #:" . $aerr;
                                } else {
                                    
                                    $fp= fopen("/home/mysamraddh/public_html/core/debit-card/uatprivatekey.pem","r");
                                    $priv_key=fread($fp,8192);
                                    fclose($fp);
                                    $res = openssl_get_privatekey($priv_key, "");
                                    $data = json_decode($aresponse);
                                    openssl_private_decrypt(base64_decode($data->encryptedKey), $key, $priv_key);
                                    $encData = openssl_decrypt(base64_decode($data->encryptedData),"aes-128-cbc",$key,OPENSSL_PKCS1_PADDING);
                                    $newsource = substr($encData, 16);
                                    // dd(($data->encryptedKey), $key, $priv_key);

                                    $log = "\n\n".'GUID - '."================================================================\n <br>";
                                    $log .= 'URL - '.$httpUrl."\n\n <br>";
                                    $log .= 'RESPONSE - '.json_encode($aresponse)."\n\n <br>";
                                    $log .= 'REQUEST ENCRYPTED - '.json_encode($newsource)."\n\n <br>";

                                    $output = json_decode($newsource);
                                
                                    $entryDate =  Carbon::now()->format('Y-m-d');
                                    $entryTime = Carbon::now()->format('H:m:s');
                                    
                                    $Debitdata = [
                                        'debit_card_id' => $ac['id'],
                                        'transaction_id' => $output->UNIQUEID,
                                        'payment_type' =>  'CR',
                                        'amount' => $amountSend,
                                        'status' => '0',
                                        'entry_date' => $entryDate,
                                        'entry_time' =>  $entryTime,
                                        ] ;
                                        
                                    $createTransaction = \App\Models\DebitCardTransaction::create($Debitdata);
                                    
                                    $ac->update(['running_transaction'=>1]);
                                        
                                    \Log::info("Send Amount Request SuccessFully!". $createTransaction.''.$ac->running_transaction );
                                    
                                
                                }
                                */
                                $response["status"] = "Success";
                                $response["code"] = 200;
                                $response["message"] = "Request created, amount will transfer to your debit card after admin approval!.";
                                $response["data"] = array();
            
                            }
                        }
                        else 
                        {
                            $response["status"] = "Error";
                            $response["code"] = 201;
                            $response["message"] = "Previous Request Not Approved Yet Please Wait..!";
                            $response["data"] = array();
                        }
                       // echo '4';
                        return response()->json($response);

                }

            } else {
                $response["status"] = "Error";
                $response["code"] = 201;
                $response["message"] = "Enter Valid Member Id";
                $response["data"] = array();
            }
        } 
        else{
            $response["status"] = "Error";
            $response["code"] = "201";
            $response["message"] = "Input parameter missing";
            $response["data"] = array();
        }
        return response()->json($response);
    }
    
    
}