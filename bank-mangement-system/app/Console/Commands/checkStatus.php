<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Request as Request1;
use Validator;
use App\Models\Transcation; 
use App\Models\BranchDaybook;
use App\Models\Daybook;
use App\Models\SamraddhBankDaybook;  
use App\Models\AllTransaction;
use App\Models\AllHeadTransaction;

use App\Models\Memberloans;
use App\Models\Grouploans;
use App\Http\Controllers\Admin\CommanController;
use App\Http\Controllers\Admin\InvestmentplanController;
use App\Models\SavingAccountTranscation;
use App\Models\SavingAccount;
use App\Models\AccountHeads;
use App\Models\MemberTransaction;
use App\Models\Memberinvestments;
use App\Models\DemandAdvice;
use Yajra\DataTables\DataTables;
use App\Models\TransactionType;
use Carbon\Carbon;
use Session;
use Image;
use Redirect;
use URL;
use DB;
use App\Http\Controllers\Branch\CommanTransactionsController; 

class checkStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'checkStatus:debitCard';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check Status of Debit Card Transaction';

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
         date_default_timezone_set("Asia/Calcutta"); 
       
         DB::beginTransaction();
    try{
        $getData = \App\Models\DebitCardTransaction::where('status',0)->get();
        foreach($getData as $ac)
        {
            $ssb = \App\Models\DebitCard::with('savingAccount')->findorfail($ac->debit_card_id);
           $transactionLatest = SavingAccountTranscation::where('saving_account_id',$ssb->ssb_id)
                    ->orderBy('created_at', 'desc')
                    ->first();
        $uniqueId = $ac->transaction_id;
        $url_neft_uat  = "https://apibankingone.icicibank.com/api/v1/composite-status";
      

        $post_neft = [
            "URN" => "SR209617949",
            "AGGRID" => "CUST0675",
            "CORPID" => "SAMRADDH05102020",
            "USERID" => "AMRENDRA",
            "UNIQUEID" =>  $uniqueId ,
    
        ];
        $apostData = json_encode($post_neft);
        print_r("<<========apostData=========>><br />");
        print_r($apostData);
        $sessionKey = 1234567890123456; //hash('MD5', time(), true); //16 byte session key

         $fp = fopen("/home/mysamraddh/public_html/core/debit-card/LiveCert.txt", "r");
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
            "apikey: YqDT8sE0XnGTXvGAHslvTxv2lVzwIRPw",
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
            
             $fp = fopen("/home/mysamraddh/public_html/core/debit-card/uatprivatekey.pem", "r");
            $priv_key=fread($fp,8192);
            fclose($fp);
            $res = openssl_get_privatekey($priv_key, "");
            $data = json_decode($aresponse);
         
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

            if($output->STATUS =='SUCCESS' && $ac->status ==0)
            {
                $amountSend = $ac->amount;
                $entryDate =  Carbon::now()->format('Y-m-d');
                $entryTime = Carbon::now()->format('H:m:s');
                $daybookRefRD=CommanTransactionsController::createBranchDayBookReferenceNew($amountSend,$entryDate);
              
                
                $savingAccountId = $ssb['savingAccount']['id'];
                $savingAccountAccount =  $ssb['savingAccount']->account_no;
                $savingBranch =  $ssb['savingAccount']->branch_id;
                $savingAssociateId = $ssb['savingAccount']->associate_id;
                $savingMemberId = $ssb['savingAccount']->member_id;
                $type = 15;
               
                $openingBalance = $ssb->opening_balance - $ac->amount;
                $des = 'SSb Amount Transfer to Debit (CRON)';
                $paymentType = 'CR';
                $paymentMode = 6;
                $ssbData = [
                    'daybook_ref_id' =>$daybookRefRD,
                    'saving_account_id' => $savingAccountId,
                    'account_no' => $savingAccountAccount,
                    'branch_id' => $savingBranch,
                    'associate_id' => $savingAssociateId,
                    'type' => $type,
                    'opening_balance' => $transactionLatest->opening_balance - $ac->amount,
                    'deposit' =>0,
                    'withdrawal' => $ac->amount,
                    'description' => $des,
                    'currency_code' => 'INR',
                    'payment_type' => 'DR',
                    'payment_mode' => $paymentMode,
                ];
                $ssbTransaction = \App\Models\SavingAccountTranscation::create($ssbData);

                // Branch Daybook Transaction

                $branchDaybookData = [
                    'daybook_ref_id' =>$daybookRefRD,
                    'branch_id' => $savingBranch,
                    'associate_id' => $savingAssociateId,
                    'type' => 4,
                    'sub_type' => 43,
                    'type_id' =>$ac->id,
                    'type_transaction_id' => $ssb['savingAccount']->id,
                    'member_id' =>$savingMemberId,
                    'opening_balance'=>$ac->amount,
                    'amount' => $ac->amount,
                    'closing_balance' =>$ac->amount,
                    'description' =>$des,
                    'description_cr' => 'SSB To Debit Card'.$ac->card_no. 'Transfer(CRON)',
                    'payment_type' => 'DR',
                    'payment_mode' => 6,
                    'entry_date' => $entryDate,
                    'entry_time' =>  $entryTime,



                ];
                 $branchDaybookData2 = [
                    'daybook_ref_id' =>$daybookRefRD,
                    'branch_id' => $savingBranch,
                    'associate_id' => $savingAssociateId,
                    'type' => 29,
                    'sub_type' => 292,
                    'type_id' =>$ac->id,
                    'type_transaction_id' => $ac->debit_card_id,
                    'member_id' =>$savingMemberId,
                    'opening_balance'=>$ac->amount,
                    'amount' => $ac->amount,
                    'closing_balance' =>$ac->amount,
                    'description' =>$des,
                    'description_cr' => 'SSB To Debit Card '.$ssb->card_no. 'Transfer(CRON)',
                    'payment_type' => $paymentType,
                    'payment_mode' => 6,
                    'entry_date' => $entryDate,
                    'entry_time' =>  $entryTime,



                ];

                 $memberData = [
                    'daybook_ref_id' =>$daybookRefRD,
                    'branch_id' => $savingBranch,
                    'associate_id' => $savingAssociateId,
                    'type' => 29,
                    'sub_type' => 292,
                    'type_id' =>$ac->id,
                    'type_transaction_id' => $ac->debit_card_id,
                    'member_id' =>$savingMemberId,
                    'amount' => $ac->amount,
                    'description' =>$des,
                    'payment_type' => $paymentType,
                    'payment_mode' => 6,
                    'entry_date' => $entryDate,
                    'entry_time' =>  $entryTime,



                ];
                  $memberData2 = [
                    'daybook_ref_id' =>$daybookRefRD,
                    'branch_id' => $savingBranch,
                    'associate_id' => $savingAssociateId,
                    'type' => 4,
                    'sub_type' => 43,
                    'type_id' =>$ac->id,
                    'type_transaction_id' => $ssb['savingAccount']->id,
                    'member_id' =>$savingMemberId,
                    'amount' => $ac->amount,
                    'description' =>$des,
                    'payment_type' => 'DR',
                    'payment_mode' => 6,
                    'entry_date' => $entryDate,
                    'entry_time' =>  $entryTime,



                ];

                $brDaybook=\App\Models\BranchDaybook::create($branchDaybookData);
                $brDaybook2=\App\Models\BranchDaybook::create($branchDaybookData2);
                $head1 =56 ;
                $head2 =70; 
                $head3 =203; 
                 $AllheadTransactionData1 = [
                    'daybook_ref_id' =>$daybookRefRD,
                    'branch_id' => $savingBranch,
                    'associate_id' => $savingAssociateId,
                    'type' => 4,
                    'sub_type' => 43,
                    'type_id' =>$ac->id,
                    'type_transaction_id' =>$ssb['savingAccount']->id,
                    'member_id' =>$savingMemberId,
                    'opening_balance'=>$ac->amount,
                    'amount' => $ac->amount,
                    'closing_balance' =>$ac->amount,
                    'description' =>$des,
                    'payment_type' => 'DR',
                    'payment_mode' => 6,
                    'entry_date' => $entryDate,
                    'entry_time' =>  $entryTime,
                    'head_id' => $head1



                ];
                $AllheadTransactionData2 = [
                    'daybook_ref_id' =>$daybookRefRD,
                    'branch_id' => $savingBranch,
                    'associate_id' => $savingAssociateId,
                    'type' => 29,
                    'sub_type' => 292,
                    'type_id' =>$ac->id,
                    'type_transaction_id' =>$ac->debit_card_id,
                    'member_id' =>$savingMemberId,
                    'opening_balance'=>$ac->amount,
                    'amount' => $ac->amount,
                    'closing_balance' =>$ac->amount,
                    'description' =>$des,
                    'payment_type' => $paymentType,
                    'payment_mode' => 6,
                    'entry_date' => $entryDate,
                    'entry_time' =>  $entryTime,
                    'head_id' => $head2



                ];

                $bank = [
                    'daybook_ref_id' =>$daybookRefRD,
                    'branch_id' => $savingBranch,
                    'bank_id' => 2,
                    'account_id' => 2,
                    'associate_id' => $savingAssociateId,
                    'type' => 29,
                    'sub_type' => 292,
                    'type_id' =>$ac->id,
                    'type_transaction_id' =>$ac->debit_card_id,
                    'member_id' =>$savingMemberId,
                    'opening_balance'=>$ac->amount,
                    'amount' => $ac->amount,
                    'closing_balance' =>$ac->amount,
                    'description' =>$des,
                    'description_dr' =>$des,
                    'description_cr' =>$des,
                    'payment_type' => $paymentType,
                    'payment_mode' => 6,
                    'entry_date' => $entryDate,
                    'entry_time' =>  $entryTime,
                   



                ];

                $AllheadTransactionData3 = [
                    'daybook_ref_id' =>$daybookRefRD,
                    'branch_id' => $savingBranch,
                    'associate_id' => $savingAssociateId,
                    'type' => 29,
                    'sub_type' => 292,
                    'type_id' =>$ac->id,
                    'type_transaction_id' =>$ac->debit_card_id,
                    'member_id' =>$savingMemberId,
                    'opening_balance'=>$ac->amount,
                    'amount' => $ac->amount,
                    'closing_balance' =>$ac->amount,
                    'description' =>$des,
                    'payment_type' => $paymentType,
                    'payment_mode' => 6,
                    'entry_date' => $entryDate,
                    'entry_time' =>  $entryTime,
                    'head_id' => $head3



                ];
                $AllheadTransactionData4 = [
                    'daybook_ref_id' =>$daybookRefRD,
                    'branch_id' => $savingBranch,
                    'associate_id' => $savingAssociateId,
                    'type' => 29,
                    'sub_type' => 292,
                    'type_id' =>$ac->id,
                    'type_transaction_id' =>$ac->debit_card_id,
                    'member_id' =>$savingMemberId,
                    'opening_balance'=>$ac->amount,
                    'amount' => $ac->amount,
                    'closing_balance' =>$ac->amount,
                    'description' =>$des,
                    'payment_type' => 'DR',
                    'payment_mode' => 6,
                    'entry_date' => $entryDate,
                    'entry_time' =>  $entryTime,
                    'head_id' => $head3



                ];
              
                
               $allHead1 = \App\Models\AllHeadTransaction::create($AllheadTransactionData1);
               $allHead2 = \App\Models\AllHeadTransaction::create($AllheadTransactionData2);
               $allHead3 = \App\Models\AllHeadTransaction::create($AllheadTransactionData3);
               $allHead4 = \App\Models\AllHeadTransaction::create($AllheadTransactionData4);
               $memTran=   \App\Models\MemberTransaction::create($memberData);
               $memTran2=   \App\Models\MemberTransaction::create($memberData2);
               $bank1=   \App\Models\SamraddhBankDaybook::create($bank);
               $ac->update(['status'=>1]);
               $updateSSbAccountBalance = \App\Models\SavingAccount::where('id',$ssb->ssb_id)->update(['balance' => $ssb['savingAccount']->balance - $ac->amount]);
               $ssb->update(['running_transaction'=>0]);
            }
            if($output->STATUS =='REJECTED')
            {
                $ac->update(['status'=>2]);
                $ssb->update(['running_transaction'=>0]);

                // $transactionLatest->update(['debit_card_transaction_id'=>NULL]);
            }

            }
        }
        DB::commit();

        } catch (\Exception $ex) {

            DB::rollback(); 

            return back()->with('alert', $ex->getMessage());

        }
         \Log::info("Status Checked SuccessFully!");
    }
}
