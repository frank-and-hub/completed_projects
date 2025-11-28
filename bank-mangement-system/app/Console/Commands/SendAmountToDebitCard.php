<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use DB;
use Carbon\Carbon;
class SendAmountToDebitCard extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sendamnounttodebitcard:send';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send Amount to debit Card from ssb';

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
       die('stop');
    DB::beginTransaction();
   
    try
    {
        $url_neft_uat  = "https://apibankingone.icicibank.com/api/v1/composite-payment";
        $debitCard = \App\Models\DebitCard::where('status',1)->where('is_block','!=',2)->where('running_transaction',0)->get();
       
        foreach ($debitCard as $ac)
                {
                   
                    $sumAmount = \App\Models\SavingAccountTranscation::with(['savingAc' => function ($q)
                    {
                        $q->select('id', 'member_id','account_no','member_id','associate_id','branch_id','balance');
                    }
                    ])
                        ->where('saving_account_id', $ac['ssb_id'])
                        ->orderBy('id', 'desc')
                        ->first();
                    $amount = 500;
                    $amountSend =number_format((float)FLOOR($sumAmount->opening_balance) - $amount, 0, '.', '') ;  
                    $checkAmount =number_format((float)FLOOR($sumAmount->opening_balance), 0, '.', '') ;  
                 
                        if ( $checkAmount  > $amount)
                        {
                            $memberId = getMemberData($sumAmount['savingAc']->member_id);
                                date_default_timezone_set("Asia/Calcutta");
                                $date = Carbon::now()->format('dmYhis');
                                $trsNo = $memberId->member_id . $date;
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
                    // print_r("<<========aresponse=========>><br />");
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
                            
                            // file_put_contents($file, $log, FILE_APPEND | LOCK_EX);
                        
                        
                            $output = json_decode($newsource);
                          
                            // print_r("<<========output=========>><br />");
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
        
                        }
                  
            }
        DB::commit();

        }
        catch(\Exception $ex)
        {

            DB::rollback();

            return back()->with('alert', $ex->getMessage());

        }
        \Log::info("Send Amount Request SuccessFully!");
    }
}
