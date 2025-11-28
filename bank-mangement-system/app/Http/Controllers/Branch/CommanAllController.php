<?php 
namespace App\Http\Controllers\Branch; 

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use App\Models\SavingAccount; 
use App\Models\SavingAccountTranscation; 
use App\Models\Member; 
use App\Models\Transcation;
use App\Models\TranscationLog; 
use App\Models\Daybook; 
use App\Models\Receipt;
use App\Models\ReceiptAmount;
use App\Services\ImageUpload;
use Session;
use Image;
use Redirect;
use Mail;
/*
    |---------------------------------------------------------------------------
    | Branch Panel -- CommanController
    |--------------------------------------------------------------------------
    |
    | This controller handles all functions which call multiple times .
*/
class CommanAllController extends Controller
{
	/**
     * Create a new controller instance.
     * @return void
     */

	public function __construct()
    {
    	// check user login or not
    //	$this->middleware('auth');
    }

    
    /**
     *  Member image or signature update.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public static function updateFiles($request,$id)
    {
        $signature_filename='';
        $photo_filename='';       
        if ($request->hasFile('signature')) {
            $signature_image = $request->file('signature');
            $signature_filename = date('Y_m_d').'_'.time().$signature_image->getClientOriginalExtension();
            $signature_location = 'asset/profile/member_signature/' . $signature_filename;
            
            // $request->file('signature')->move($signature_image, $signature_filename);
            $mainFolderPhoto = '/profile/member_signature/';
            ImageUpload::upload($signature_image, $mainFolderPhoto, $signature_filename); 
            
        }
        if ($request->hasFile('photo')) {
            $photo_image = $request->file('photo');
            $photo_filename = date('Y_m_d').'_'.time().$photo_image->getClientOriginalExtension();
            $photo_location = 'asset/profile/member_avatar/' . $photo_filename;
            
            // Image::make($image)->resize(300,300)->save($photo_location);
            $mainFolderPhoto = '/profile/member_avatar/';
            ImageUpload::upload($photo_image, $mainFolderPhoto, $photo_filename);
        }
        $member = Member::find($memberId);
        $member->signature=$signature_filename;
        $member->photo=$photo_filename;
        return $member->save();
    }

    /**
     *  create saving account .
     *
     * @param  $memberId,$branch_id,$branchCode,$amount,$payment_mode
     * @return \Illuminate\Http\Response
     */
    public static function createSavingAccount($memberId,$branch_id,$branchCode,$amount,$payment_mode,$investmentId,$miCode,$investmentAccountNoSsb,$is_primary,$faCode)
    {
        
        
        
        $miCodePassbook=str_pad($miCode, 7, '0', STR_PAD_LEFT);
    // pass fa id 20 for passbook 
        $getfaCodePassbook=getFaCode(20);
        $faCodePassbook=$getfaCodePassbook->code;
        $passbookNumber=$faCodePassbook.$branchCode.$miCodePassbook;

    // genarate  member saving account no        
        $account_no=$investmentAccountNoSsb;
        $data['account_no']=$account_no;
        $data['member_investments_id']=$investmentId;
        $data['is_primary']=$is_primary;
        $data['passbook_no']=$passbookNumber;
        $data['mi_code']=$miCode;
        $data['fa_code']=$faCode;
        $data['member_id']=$memberId;
        $data['branch_id']=$branch_id;
        $data['branch_code']=$branchCode;
        $data['balance']=0;
        $data['currency_code']='INR';
        $data['created_by_id']=Auth::user()->id;
        $data['created_by']=2;
        $ssbAccount = SavingAccount::create($data);
        $ssb_id = $ssbAccount->id;
    // create saving account transcation
        $ssb['saving_account_id']=$ssb_id;
        $ssb['account_no']=$account_no;
        $ssb['opening_balance']=$amount;
        $ssb['deposit']=$amount;
        $ssb['withdrawal']=0;
        $ssb['description']='AGT Account opening';
        $ssb['currency_code']='INR';
        $ssb['payment_type']='CR';
        $ssb['payment_mode']=$payment_mode;
        $ssbAccountTran = SavingAccountTranscation::create($ssb);
        
    // update saving account current balance 
        $balance_update=$amount;

        $ssbBalance = SavingAccount::find($ssb_id);
        $ssbBalance->balance=$balance_update;
        $ssbBalance->save();
        return $ssb_id; 

    }
    /**
     *  create Payment transaction (only payment mode cash)
     *
     * @param  $transaction_type,$ssbAccountId,$memberId,$branch_id,
     *  $branchCode,$amount,$payment_mode,$deposit_by_name,$deposit_by_id
     * @return \Illuminate\Http\Response
     */
    public static function createPaymentTransaction($transaction_type,$ssbAccountId,$memberId,$branch_id,$branchCode,$amount,$payment_mode,$deposit_by_name,$deposit_by_id)
    { 
        $getSsbAcoountNo=getSsbAccountNumber($ssbAccountId);
        $data['transaction_type']=$transaction_type;
        $data['transaction_type_id']=$ssbAccountId;
        $data['account_no']=$getSsbAcoountNo->account_no;
        $data['member_id']=$memberId;
        $data['branch_id']=$branch_id;
        $data['branch_code']=$branchCode;
        $data['amount']=$amount;
        $data['currency_code']='INR';
        $data['payment_mode']=$payment_mode;

        $data['amount_deposit_by_name']=$deposit_by_name;
        $data['amount_deposit_by_id']=$deposit_by_id;
        $data['created_by_id']=Auth::user()->id;
        $data['created_by']=2;
        $transcation = Transcation::create($data);
        $tran_id = $transcation->id;


        $data_log['transaction_id']=$tran_id;
        $data_log['transaction_type']=$transaction_type;
        $data_log['transaction_type_id']=$ssbAccountId;
        $data_log['account_no']=$getSsbAcoountNo->account_no;
        $data_log['member_id']=$memberId;
        $data_log['branch_id']=$branch_id;
        $data_log['branch_code']=$branchCode;
        $data_log['amount']=$amount;
        $data_log['currency_code']='INR';
        $data_log['payment_mode']=$payment_mode;

        $data_log['amount_deposit_by_name']=$deposit_by_name;
        $data_log['amount_deposit_by_id']=$deposit_by_id;
        $data_log['created_by_id']=Auth::user()->id;
        $data_log['created_by']=2;
        $transcation_log = TranscationLog::create($data_log);
        return $tran_id;
    } 

    /**
     *  create Payment Recipt 
     *
     * @param  $transaction_type,$ssbAccountId,$memberId,$branch_id,
     *  $branchCode,$amount,$payment_mode,$deposit_by_name,$deposit_by_id,$typeArray
     * @return \Illuminate\Http\Response
     */
    public static function createPaymentRecipt($tranID,$ssbAccountId,$memberId,$branch_id,$branchCode,$amountArray,$typeArray,$receipts_for,$account_no)
    {

        
        $data['transaction_id']=$tranID;
        $data['receipt_by_id']=$ssbAccountId;
        $data['account_no']=$account_no;
        $data['member_id']=$memberId;
        $data['branch_id']=$branch_id;
        $data['branch_code']=$branchCode;
        $data['created_by_id']=Auth::user()->id;
        $data['created_by']=2;
        $data['receipts_for']=$receipts_for;
        $recipt = Receipt::create($data);
        $recipt_id = $recipt->id;

        foreach ($amountArray as $key=>$option) 
        {
            $data_amount['receipt_id']=$recipt_id;
            $data_amount['amount']=$option;            
            $data_amount['type_label']=$typeArray[$key];
            $data_amount['currency_code']='INR';
            $re = ReceiptAmount::create($data_amount);
        }
        return $recipt_id;
    }
    /**
     *  create Payment transaction 
     *
     * @param  $transaction_type,$ssbAccountId,$memberId,$branch_id,
     *  $branchCode,$amount,$payment_mode,$deposit_by_name,$deposit_by_id
     * @return \Illuminate\Http\Response
     */
    public static function createTransaction($transaction_type,$account_id,$memberId,$branch_id,$branchCode,$amountArray,$payment_mode,$deposit_by_name,$deposit_by_id,$account_no,$cheque_dd_no,$bank_name,$branch_name,$payment_date,$online_payment_id,$online_payment_by,$saving_account_id,$payment_type)
    { 
        
        foreach ($amountArray as $key=>$option) 
        {
            $data['transaction_type']=$transaction_type;
            $data['transaction_type_id']=$account_id;
            $data['account_no']=$account_no;
            $data['member_id']=$memberId;
            $data['branch_id']=$branch_id;
            $data['branch_code']=$branchCode;
            $data['amount']=$option;
            $data['currency_code']='INR';
            $data['payment_mode']=$payment_mode;
            $data['payment_type']=$payment_type;
            if($payment_mode==1 || $payment_mode==2)
            {
                $data['cheque_dd_no']=$cheque_dd_no;
                $data['bank_name']=$bank_name;
                $data['branch_name']=$branch_name;
                if($payment_date!=null || $payment_date!='null')
                {
                     $data['payment_date']=date("Y-m-d", strtotime($payment_date));
                }
            }
            if($payment_mode==3)
            {
                $data['online_payment_id']=$online_payment_id;
                $data['online_payment_by']=$online_payment_by; 
                if($payment_date!=null || $payment_date!='null')
                {
                     $data['payment_date']=date("Y-m-d", strtotime($payment_date));
                }
            }
            if($payment_mode==4)
            {
                $data['saving_account_id']=$saving_account_id;
                if($payment_date!=null || $payment_date!='null')
                {
                     $data['payment_date']=date("Y-m-d", strtotime($payment_date));
                }          
            } 
            $data['amount_deposit_by_name']=$deposit_by_name;
            $data['amount_deposit_by_id']=$deposit_by_id;
            $data['created_by_id']=Auth::user()->id;
            $data['created_by']=2;
            $transcation = Transcation::create($data);
            $tran_id = $transcation->id;

            $data_log['transaction_id']=$tran_id;
            $data_log['transaction_type']=$transaction_type;
            $data_log['transaction_type_id']=$account_id;
            $data_log['account_no']=$account_no;
            $data_log['member_id']=$memberId;
            $data_log['branch_id']=$branch_id;
            $data_log['branch_code']=$branchCode;
            $data_log['amount']=$option;
            $data_log['currency_code']='INR';
            $data_log['payment_mode']=$payment_mode;
            $data_log['payment_type']=$payment_type;
            if($payment_mode==1 || $payment_mode==2)
            {
                $data_log['cheque_dd_no']=$cheque_dd_no;
                $data_log['bank_name']=$bank_name;
                $data_log['branch_name']=$branch_name;
                if($payment_date!=null || $payment_date!='null')
                {
                     $data_log['payment_date']=date("Y-m-d", strtotime($payment_date));
                }
            }
            if($payment_mode==3)
            {
                $data_log['online_payment_id']=$online_payment_id;
                $data_log['online_payment_by']=$online_payment_by; 
                if($payment_date!=null || $payment_date!='null')
                {
                     $data_log['payment_date']=date("Y-m-d", strtotime($payment_date));
                }
            }
            if($payment_mode==4)
            {
                $data_log['saving_account_id']=$saving_account_id;
                if($payment_date!=null || $payment_date!='null')
                {
                     $data_log['payment_date']=date("Y-m-d", strtotime($payment_date));
                }          
            }
            $data_log['amount_deposit_by_name']=$deposit_by_name;
            $data_log['amount_deposit_by_id']=$deposit_by_id;
            $data_log['created_by_id']=Auth::user()->id;
            $data_log['created_by']=2;
            $transcation_log = TranscationLog::create($data_log);
            
        }return $tran_id;
    }

    /**
     *  create day book transaction 
     *
     * @param  $transaction_type,$ssbAccountId,$memberId,$branch_id,
     *  $branchCode,$amount,$payment_mode,$deposit_by_name,$deposit_by_id
     * @return \Illuminate\Http\Response
     */
    public static function createDayBook($transaction_id,$transaction_type,$account_id,$memberId,$branch_id,$branchCode,$amountArray,$payment_mode,$deposit_by_name,$deposit_by_id,$account_no,$cheque_dd_no,$bank_name,$branch_name,$payment_date,$online_payment_id,$online_payment_by,$saving_account_id,$payment_type)
    { 
        
        foreach ($amountArray as $key=>$option) 
        {
            $data_log['transaction_id']=$transaction_id;
            $data_log['investment_id']=$account_id;
            $data_log['account_no']=$account_no;
            $data_log['associate_id']=$memberId;
            $data_log['branch_id']=$branch_id;
            $data_log['branch_code']=$branchCode;
            $data_log['amount']=$option;
            $data_log['currency_code']='INR';
            $data_log['payment_mode']=$payment_mode;
            $data_log['payment_type']=$payment_type;
            if($payment_mode==1 || $payment_mode==2)
            {
                $data_log['cheque_dd_no']=$cheque_dd_no;
                $data_log['bank_name']=$bank_name;
                $data_log['branch_name']=$branch_name;
                if($payment_date!=null || $payment_date!='null')
                {
                     $data_log['payment_date']=date("Y-m-d", strtotime($payment_date));
                }
            }
            if($payment_mode==3)
            {
                $data_log['online_payment_id']=$online_payment_id;
                $data_log['online_payment_by']=$online_payment_by; 
                if($payment_date!=null || $payment_date!='null')
                {
                     $data_log['payment_date']=date("Y-m-d", strtotime($payment_date));
                }
            }
            if($payment_mode==4)
            {
                $data_log['saving_account_id']=$saving_account_id;
                if($payment_date!=null || $payment_date!='null')
                {
                     $data_log['payment_date']=date("Y-m-d", strtotime($payment_date));
                }          
            } 
            $data_log['amount_deposit_by_name']=$deposit_by_name;
            $data_log['amount_deposit_by_id']=$deposit_by_id;
            $data_log['created_by_id']=Auth::user()->id;
            $data_log['created_by']=2;
            $transcation = Daybook::create($data_log);
            $tran_id = $transcation->id;
        }return $tran_id;
    }

    /**
     *  Amount  deposit or withdrow in ssb account 
     *
     * @param  $account_id,$account_no,$balance,$amount, $description,$currency_code,$payment_type,$payment_mode
     * @return \Illuminate\Http\Response
     */
    public static function ssbTransaction($account_id,$account_no,$balance,$amount,$description,$currency_code,$payment_type,$payment_mode)
    {
                    $ssbBalance = $balance-$amount;
                    $dataSsb['saving_account_id'] = $account_id;
                    $dataSsb['account_no'] = $account_no;
                    $dataSsb['opening_balance'] = $ssbBalance;
                    if($payment_type=='DR')
                    {
                        $dataSsb['withdrawal'] = $amount;
                    }
                    else
                    {
                       $dataSsb['deposit'] = $amount; 
                    }
                    $dataSsb['amount'] = $balance; 
                    $dataSsb['description'] = $description;
                    $dataSsb['currency_code'] = $currency_code;
                    $dataSsb['payment_type'] = $payment_type;
                    $dataSsb['payment_mode'] = $payment_mode; 
                    $resSsb = SavingAccountTranscation::create($dataSsb);

                    $ssbBalance = $balance-$amount;
                    $sResult = SavingAccount::find($account_id);
                    $sData['balance'] = $ssbBalance;
                    $sResult->update($sData); 
                     return  $resSsb->id;                   

    }



}
