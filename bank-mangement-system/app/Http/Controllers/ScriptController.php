<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use DB;
use App\Models\Event;
use App\Models\HolidaySettings;
use App\Models\States;
use App\Models\Memberinvestments;

use App\Models\Branch;
use Carbon\Carbon;
use App\Models\SamraddhBankClosing;
use App\Models\SamraddhBank;


class ScriptController extends Controller
{

    public function index(Request $request)
    {
      $data = SamraddhBank::get();
      // $eventHoliday = Event::get
    //  $startDate = new Carbon('2020-08-24');
    //  $date = date_format($startDate,'d/m/Y');
    //  $states = States::select('id')->get();
    //  $endDate = date('Y-m-d');
    //  $all_dates = array();
    //    foreach ($states as $key => $state) {
    //         $globalDate = headerMonthAvailability(date('d'),date('m'),date('Y'),$state->id);
           
    //        while($date <= $globalDate)
    //         {
    //           $all_dates = $date;
    //            dd($all_dates);
    //        die;
    //         }
          

    //    }

    // die;
      $startDate = new Carbon('2020-01-01');
      $date = date_format($startDate,'d/m/Y');
      $states = States::select('id')->get();
      $i=1;
      $all_dates = array();
      foreach ($states as $key => $state) 
      {
          $globalDate = headerMonthAvailability(date('d'),date('m'),date('Y'),$state->id);
          $gdate =Carbon::createFromFormat('d/m/Y', $globalDate)->subDays(1);
          
            while ($startDate->lte($gdate))
          {
              $all_dates[] =date('Y-m-d',strtotime($startDate));
              $startDate->addDay();
          }
       
      }
        foreach ($data as $key => $value) 
        {
          DB::beginTransaction();
          try{
            foreach ($all_dates as $key => $date) {
              foreach ($states as $key => $state) {
                  $checkHoliday = eventHoliday($date,$state->id);
              }
              if($checkHoliday == 0)
              {
                 $result = checkDataExist($date,$value->id);
                 
                if($result=='')
                {
                  
                  $dataRow = getDataRow($date,$value->id);
                  if($dataRow)
                 {
                    $record['bank_id'] =$dataRow->id;
                    $record['account_id'] =$dataRow->account_id;
                    $record['opening_balance'] =$dataRow->closing_balance;
                    $record['balance'] =$dataRow->closing_balance;
                    $record['closing_balance'] =$dataRow->closing_balance;
                    $record['loan_opening_balance'] =$dataRow->loan_closing_balance;
                    $record['loan_balance'] =$dataRow->loan_closing_balance;
                    $record['loan_closing_balance'] =$dataRow->loan_closing_balance;
                    $record['type'] =$dataRow->type;
                    $record['entry_date'] =$date;
                    $record['entry_time'] =$dataRow->entry_time;
                    $record['created_at'] =$date;
                    $record['updated_at'] =$date;
                   
                    $recordCreated = SamraddhBankClosing::create($record);
                  }
                }
                  else
                  {
                      if($result->closing_balance == 0 )
                      {
                          $result->closing_balance = $result->balance;
                          $result->save();
                      }
                      if($result->loan_closing_balance == 0)
                      {
                          $result->loan_closing_balance = $result->loan_balance;
                        $result->save();
                      }

                  }
              }       
            }
            DB::commit();
              }catch (\Exception $ex) {
               DB::rollback();
                echo $ex->getMessage();
              }   
          }
       echo 'done';
    }
	
	public function update_maturity_date()
	{
		$data = Memberinvestments::get();
		
		foreach($data as $row)
		{
		if($row->maturity_date == ' ' )
		{
			 dd($maturity_date);
			$maturity_date =  date('Y-m-d', strtotime($row->created_at. ' + '.($row->tenure).' year'));
           
			 $d = Memberinvestments::where('id',$row->id)->update(['maturity_date' => $maturity_date]);
		
			
		}
		}
		echo "done";
	}
	
	
	public function deposite_query(){


        $investmentDetails = Memberinvestments::with(['plan','ssb','eliaccount'])->whereHas('eliaccount',function($q){
            $q->where('is_eli', '=', 1); // '=' is optional
        })->where('account_number',"like", "%" .'R-'. "%")->get();
      
        foreach ($investmentDetails as $account_detail){
         
        $entryTime = date("h:i:s");
        
        //$ssbAccountDetails = SavingAccount::with('ssbMember')->where('member_id',$account_detail->member_id)->first();

        $chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $vno = "";
        for ($k = 0; $k < 10; $k++) {
            $vno .= $chars[mt_rand(0, strlen($chars)-1)];
        }        

        $branch_id = $account_detail->branch_id;
        $type = 3;
        $sub_type = 34;
        $type_id = $account_detail->id;
        $type_transaction_id = $account_detail->id;
        $associate_id = NULL;
        $member_id = $account_detail->member_id;
        $branch_id_to = NULL;
        $branch_id_from = NULL;
        $opening_balance = $account_detail['eliaccount']->deposit;
        $amount = $account_detail['eliaccount']->deposit;
        $closing_balance = $account_detail['eliaccount']->deposit;

        $description = getMemberData($account_detail->member_id)->first_name.' '.getMemberData($account_detail->member_id)->last_name.' Dr '.$amount.' To '.$account_detail['plan']->name.' A/C Cr '.$amount;
        $description_dr = getMemberData($account_detail->member_id)->first_name.' '.getMemberData($account_detail->member_id)->last_name.' Dr '.$amount;
        $description_cr = 'To '.$account_detail['plan']->name.' A/C Cr '.$amount;

        $payment_type = 'CR';
        $payment_mode = 3;
        $currency_code = 'INR';
        $amount_to_id = NULL;
        $amount_to_name = NULL;
        $amount_from_id = NULL;
        $amount_from_name = NULL;
        $v_no = $vno;
        $v_date = date("Y-m-d ".$entryTime."", strtotime(convertDate($account_detail['eliaccount']->created_at)));
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
        
        $dayBookRef = CommanController::createBranchDayBookReference($amount);

        if($account_detail->plan_id == 2){

            $head1 = 1;

            $head2 = 8;

            $head3 = 20;

            $head4 = 59;

            $head5 = 80;

        }elseif($account_detail->plan_id == 3){

            $head1 = 1;

            $head2 = 8;

            $head3 = 20;

            $head4 = 59;

            $head5 = 85;

        }elseif($account_detail->plan_id == 4){

            $head1 = 1;

            $head2 = 8;

            $head3 = 20;

            $head4 = 57;

            $head5 = 79;

        }elseif($account_detail->plan_id == 5){

            $head1 = 1;

            $head2 = 8;

            $head3 = 20;

            $head4 = 59;

            $head5 = 83;

        }elseif($account_detail->plan_id == 6){

            $head1 = 1;

            $head2 = 8;

            $head3 = 20;

            $head4 = 59;

            $head5 = 84;

        }elseif($account_detail->plan_id == 7){

            $head1 = 1;

            $head2 = 8;

            $head3 = 20;

            $head4 = 58;

            $head5 = NULL;

        }elseif($account_detail->plan_id == 8){

            $head1 = 1;

            $head2 = 8;

            $head3 = 20;

            $head4 = 57;

            $head5 = 78;

        }elseif($account_detail->plan_id == 9){

            $head1 = 1;

            $head2 = 8;

            $head3 = 20;

            $head4 = 57;

            $head5 = 77;

        }elseif($account_detail->plan_id == 10){

            $head1 = 1;

            $head2 = 8;

            $head3 = 20;

            $head4 = 59;

            $head5 = 83;

        }elseif($account_detail->plan_id == 11){

            $head1 = 1;

            $head2 = 8;

            $head3 = 20;

            $head4 = 59;

            $head5 = 82;

        }
        // dd($dayBookRef,$branch_id,$bank_id,$bank_ac_id,$head1,$head2,$head3,$head4,$head5,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$amount,$amount,$amount,$description,'CR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$account_detail['eliaccount']->created_at);

        //  die();
        $allTransaction = CommanController::createAllTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,$head1,$head2,$head3,$head4,$head5,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$amount,$amount,$amount,$description,'CR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$account_detail['eliaccount']->created_at);
    }
      die('done');
    }


     public function registerCIB()
    {
        date_default_timezone_set("Asia/Calcutta"); 

        //Config UAT
        $url  = "https://apibankingone.icicibank.com/api/v1/composite-status";
        $url_cib_reg = "https://apibankingone.icicibank.com/api/Corporate/CIB/v1/Registration";


        $post_cib_reg = [
            "CORPID" => "SAM26840",
            "AGGRNAME"=>"SAMRADDHB",
            "USERID" => "AMRENDRA", 
            "AGGRID" => "CUST0675",
            "BANKID" => "ICI",
            "URN" => "SR209617949"
        ];



        $apostData = json_encode($post_cib_reg);
        // print_r("<<========apostData=========>><br />");
        // print_r($apostData);
        $sessionKey = 1234567890123456; //hash('MD5', time(), true); //16 byte session key
       // $encryptedKey = 1234567890123456;

        $fp= fopen("/home/mysamraddh/public_html/core/debit-card/LiveCert.txt","r");

        $pub_key_string=fread($fp,4096);


        //fclose($fp);

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
        // print_r("<<========request=========>><br />");
        // print_r($request);
       // dd($request);


        $apostData = json_encode($request);
        // print_r("<<========apostData=========>><br />");
        // print_r($apostData);
        $httpUrl = $url_cib_reg;
        // print_r("<<========httpUrl=========>><br />");
        // print_r($httpUrl);
        $headers = array(
            "cache-control: no-cache",
            "accept: application/json",
            "content-type: application/json",
            "apikey: D7KJ26CSZdIXqGJGtC1N5vatkGhV2xdP",
            "x-priority:0010"
        );
        // print_r("<<========headers=========>><br>");
        //  print_r($headers);


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

         dd("Header",$headers,'request',$request,'response',$aresponse,'endPoint','https://apibankingone.icicibank.com/api/Corporate/CIB/v1/Registration');
        

        print_r("<<========aresponse=========>><br />");
        $aerr = curl_error($acurl);
        $httpcode = curl_getinfo($acurl, CURLINFO_HTTP_CODE);
        print_r("<<========httpcode=========>><br />");
        print_r($httpcode);
        print_r("<<========curlresponse=========>><br />");
        print_r($aresponse);

        if ($aerr) {
            
            echo "cURL Error #:" . $aerr;
        } else {
            
            $fp= fopen("/home/mysamraddh/public_html/core/debit-card/uatprivatekey.pem","r");
            dd($fp);
            $priv_key=fread($fp,4096);
            fclose($fp);
            $res = openssl_get_privatekey($priv_key, "");
            $data = json_decode($aresponse);
            dd($data);
            openssl_private_decrypt(base64_decode($data->encryptedKey), $key, $priv_key);
            $encData = openssl_decrypt(base64_decode($data->encryptedData),"aes-128-cbc",$key,OPENSSL_PKCS1_PADDING);
            $newsource = substr($encData, 16);

            $log = "\n\n".'GUID - '."================================================================\n <br>";
            $log .= 'URL - '.$httpUrl."\n\n <br>";
            $log .= 'RESPONSE - '.json_encode($aresponse)."\n\n <br>";
            $log .= 'REQUEST ENCRYPTED - '.json_encode($newsource)."\n\n <br>";
            
            // file_put_contents($file, $log, FILE_APPEND | LOCK_EX);

            $output = json_decode($newsource);
            print_r("<<========output=========>><br />");
            print_r($output);
        }


    }

    public function checkstatus()
    {
         date_default_timezone_set("Asia/Calcutta"); 



            $url_neft_uat  = "https://apibankingone.icicibank.com/api/Corporate/CIB/v1/RegistrationStatus";

            /*
            $post_neft = [
                "CORPID" => "PRACHICIB1",
                "AGGRNAME"=>"HARI445445",
                "USERID" => "USER3", 
                "AGGRID" => "HARI12444543",
                "BANKID" => "ICI",
                "URN" => "100214578449654123"
            ];


            $post_neft = [
                "URN" => "100214578449654123",
                "AGGRID" => "HARI12444543",
                "CORPID" => "PRACHICIB1",
                "USERID" => "USER3",
                "UTRNUMBER" => "455849"
            ];
            */

            $post_neft = [
            "AGGRNAME"=>"SAMRADDHB",
            "USERID" => "AMRENDRA", 
            "AGGRID" => "CUST0675",
            "BANKID" => "ICI",
            "URN" => "SR209617949"
            ];




            $apostData = json_encode($post_neft);
            print_r("<<========apostData=========>><br />");
            print_r($apostData);
            $sessionKey = 1234567890123456; //hash('MD5', time(), true); //16 byte session key

            $fp= fopen("/home/mysamraddh/public_html/core/debit-card/LiveCert.txt","r");
            $pub_key_string=fread($fp,4096);


            //fclose($fp);
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
            print_r("<<========request=========>><br />");
            print_r($request);



            $apostData = json_encode($request);
            print_r("<<========apostData=========>><br />");
            print_r($apostData);
            $httpUrl = $url_neft_uat;
            print_r("<<========httpUrl=========>><br />");
            print_r($httpUrl);
            $headers = array(
                "cache-control: no-cache",
                "accept: application/json",
                "content-type: application/json",
                "apikey: D7KJ26CSZdIXqGJGtC1N5vatkGhV2xdP",
                "x-priority:0010"
            );
            print_r("<<========headers=========>><br>");
             print_r($headers);


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
             print_r("<<========aresponse=========>><br />");
            $aerr = curl_error($acurl);
            $httpcode = curl_getinfo($acurl, CURLINFO_HTTP_CODE);
            print_r("<<========httpcode=========>><br />");
            print_r($httpcode);
            print_r("<<========curlresponse=========>><br />");
            print_r($aresponse);

            if ($aerr) {
                
                echo "cURL Error #:" . $aerr;
            } else {
                
                $fp= fopen("/home/mysamraddh/public_html/core/debit-card/uatprivatekey.pem","r");
                $priv_key=fread($fp,8192);
                fclose($fp);
                $res = openssl_get_privatekey($priv_key, "");
                $data = json_decode($aresponse);
                dd($data);
                openssl_private_decrypt(base64_decode($data->encryptedKey), $key, $priv_key);
                $encData = openssl_decrypt(base64_decode($data->encryptedData),"aes-128-cbc",$key,OPENSSL_PKCS1_PADDING);
                $newsource = substr($encData, 16);

                $log = "\n\n".'GUID - '."================================================================\n <br>";
                $log .= 'URL - '.$httpUrl."\n\n <br>";
                $log .= 'RESPONSE - '.json_encode($aresponse)."\n\n <br>";
                $log .= 'REQUEST ENCRYPTED - '.json_encode($newsource)."\n\n <br>";
                
                // file_put_contents($file, $log, FILE_APPEND | LOCK_EX);

                $output = json_decode($newsource);
                print_r("<<========output=========>><br />");
                print_r($output);
            }
    }

    public function sendAmountSher()
    {
        $url_neft_uat ="https://apibankingone.icicibank.com/api/v1/composite-payment";
        // $date = Carbon\Carbon::now()->format('dmY');
        $trsNo = 89898989;
        $url = "https://apibankingone.icicibank.com/api/v1/composite-status";
        $amountSend = 10;
        $post_neft = ["tranRefNo" => $trsNo, "amount" => $amountSend, "senderAcctNo" => "000451000301", "beneAccNo" => "000405002777", "beneIFSC" => "ICIC0000011", "beneName" => "ICICI", "narration1" => "Amount Transfer", "crpId" => 'SAM26840', "crpUsr" => "AMRENDRA", "aggrName" => "SAMRADDHB", "aggrId" => "CUST0675", "urn" => $trsNo, "txnType" => "TPA", "WORKFLOW_REQD" => "N", "CREDITACC" => 4629525412777954,

        ];

        $apostData = json_encode($post_neft);
        // print_r("<<========apostData=========>><br />");
        // print_r($apostData);
        $sessionKey = 1234567890123456; //hash('MD5', time(), true); //16 byte session key
        $fp = fopen("/home/mysamraddh/public_html/core/debit-card/LiveCert.txt", "r");
        $pub_key_string = fread($fp, 4096);

        //fclose($fp);
        openssl_get_publickey($pub_key_string);
        openssl_public_encrypt($sessionKey, $encryptedKey, $pub_key_string); // RSA
        $iv = 1234567890123456; //str_repeat("\0", 16);
        $encryptedData = openssl_encrypt($apostData, 'aes-128-cbc', $sessionKey, OPENSSL_RAW_DATA, $iv); // AES
        $request = ["requestId" => "req_" . time() , "encryptedKey" => base64_encode($encryptedKey) , "iv" => base64_encode($iv) , "encryptedData" => base64_encode($encryptedData) , "oaepHashingAlgorithm" => "NONE", "service" => "", "clientInfo" => "", "optionalParam" => ""];
        // print_r("<<========request=========>><br />");
        //  print_r($request);
        

        $apostData = json_encode($request);
        //  print_r("<<========apostData=========>><br />");
        //  print_r($apostData);
        $httpUrl = $url_neft_uat;
        //  print_r("<<========httpUrl=========>><br />");
        //  print_r($httpUrl);
        $headers = array(
            "cache-control: no-cache",
            "accept: application/json",
            "content-type: application/json",
            "apikey: YqDT8sE0XnGTXvGAHslvTxv2lVzwIRPw",
            "x-priority:0010"
        );
        // print_r("<<========headers=========>><br>");
        // print_r($headers);
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
        // print_r("<<========aresponse=========>><br />");
        $aerr = curl_error($acurl);
        $httpcode = curl_getinfo($acurl, CURLINFO_HTTP_CODE);
        // print_r("<<========httpcode=========>><br />");
        // print_r($httpcode);
        //print_r("<<========curlresponse=========>><br />");
        //print_r($aresponse);
        if ($aerr)
        {

            echo "cURL Error #:" . $aerr;
        }
        else
        {
            $fp = fopen("/home/mysamraddh/public_html/core/debit-card/uatprivatekey.pem", "r");

            $priv_key = fread($fp, 8192);
            fclose($fp);
            $res = openssl_get_privatekey($priv_key, "");
            $data = json_decode($aresponse);
            dd("Header",$headers,'request',$request,'response',$aresponse,'endPoint',$url_neft_uat);
            openssl_private_decrypt(base64_decode($data->encryptedKey) , $key, $priv_key);
            // print_r("<<==================Check =========================>>");
            // print_r($data);
            $encData = openssl_decrypt(base64_decode($data->encryptedData) , "aes-128-cbc", $key, OPENSSL_PKCS1_PADDING);

            $newsource = substr($encData, 16);

            $log = "\n\n" . 'GUID - ' . "================================================================\n <br>";
            $log .= 'URL - ' . $httpUrl . "\n\n <br>";
            $log .= 'RESPONSE - ' . json_encode($aresponse) . "\n\n <br>";
            // print_r($newsource);die();
            $log .= 'REQUEST ENCRYPTED - ' . json_encode($newsource) . "\n\n <br>";

            // file_put_contents($file, $log, FILE_APPEND | LOCK_EX);
            $output = json_decode($newsource);
        }
        // print_r("<<========output=========>><br />");
        // print_r($output);
       
    }

}
