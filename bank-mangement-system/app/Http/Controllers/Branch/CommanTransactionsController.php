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
use App\Models\BranchCurrentBalance;
use App\Models\Receipt;
use App\Models\ReceiptAmount;
use App\Models\TransactionReferences;
use App\Models\LoanDayBooks;
use Session;
use Image;
use Redirect;
use App\Models\SamraddhBank;
use Carbon\Carbon;
use Mail;
use App\Services\ImageUpload;

/*
|---------------------------------------------------------------------------
| Branch Panel -- CommanTransactionsController
|--------------------------------------------------------------------------
|
| This controller handles all functions which call multiple times .
*/

class CommanTransactionsController extends Controller
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
    public static function updateFiles($request, $id)
    {
        $signature_filename = '';
        $photo_filename = '';
        $memberId = $id;
        if ($request->hasFile('signature')) {
            $signature_image = $request->file('signature');
            $signature_filename = $memberId . '_' . time() . $signature_image->getClientOriginalExtension();
            $signature_location = 'asset/profile/member_signature/' . $signature_filename;
            $mainFolderSignature= '/profile/member_signature/';
            ImageUpload::upload($signature_image, $mainFolderSignature,$signature_filename);
            // $request->file('signature')->move($signature_image, $signature_filename);
        }
        if ($request->hasFile('photo')) {
            $photo_image = $request->file('photo');
            $photo_filename = $memberId . '_' . time() . $photo_image->getClientOriginalExtension();
            $photo_location = 'asset/profile/member_avatar/' . $photo_filename;
            $mainFolderPhoto = '/profile/member_avatar/';
            ImageUpload::upload($photo_image, $mainFolderPhoto,$photo_filename);
            // Image::make($image)->resize(300, 300)->save($photo_location);
        }
        $member = Member::find($memberId);
        $member->signature = $signature_filename;
        $member->photo = $photo_filename;
        return $member->save();
    }

    /**
     *  create saving account .
     *
     * @param  $memberId,$branch_id,$branchCode,$amount,$payment_mode
     * @return \Illuminate\Http\Response
     */
    public static function createSavingAccount($memberId, $branch_id, $branchCode, $amount, $payment_mode, $investmentId, $miCode, $investmentAccountNoSsb, $is_primary, $faCode, $companyId)
    {
        $globaldate = Session::get('created_at');
        $miCodePassbook = str_pad($miCode, 5, '0', STR_PAD_LEFT);
        // pass fa id 20 for passbook
        $getfaCodePassbook = getFaCode(20, $companyId);
        $faCodePassbook = $getfaCodePassbook->code;
        $passbookNumber = $faCodePassbook . $branchCode . $faCode . $miCodePassbook;

        // genarate  member saving account no
        $account_no = $investmentAccountNoSsb;
        $data['account_no'] = $account_no;
        $data['member_investments_id'] = $investmentId;
        $data['is_primary'] = $is_primary;
        $data['passbook_no'] = $passbookNumber;
        $data['mi_code'] = $miCode;
        $data['fa_code'] = $faCode;
        $data['member_id'] = $memberId;
        $data['branch_id'] = $branch_id;
        $data['branch_code'] = $branchCode;
        $data['old_branch_id'] = $branch_id;
        $data['old_branch_code'] = $branchCode;
        $data['balance'] = 0;
        $data['currency_code'] = 'INR';
        $data['created_by_id'] = Auth::user()->id;
        $data['created_by'] = (Auth::user()->role_id == '3') ? 2 : 1;
        $data['created_at'] = $globaldate;
        $data['company_id'] = $companyId;
        $ssbAccount = SavingAccount::create($data);
        $ssbArray['ssb_id'] = $ssbAccount->id;
        // create saving account transcation
        $ssb['saving_account_id'] = $ssbArray['ssb_id'];
        $ssb['account_no'] = $account_no;
        $ssb['opening_balance'] = $amount;
        $ssb['deposit'] = $amount;
        $ssb['withdrawal'] = 0;
        //$ssb['description']='AGT Account opening';
        $ssb['description'] = 'SSB Account opening';
        $ssb['currency_code'] = 'INR';
        $ssb['payment_type'] = 'CR';
        $ssb['payment_mode'] = $payment_mode;
        $ssb['created_at'] = $globaldate;
        $ssb['company_id'] = $companyId;
        $ssbAccountTran = SavingAccountTranscation::create($ssb);
        $ssbArray['ssb_transaction_id'] = $ssbAccountTran->id;
        // update saving account current balance
        $balance_update = $amount;

        $ssbBalance = SavingAccount::find($ssbArray['ssb_id']);
        $ssbBalance->balance = $balance_update;
        $ssbBalance->save();
        return $ssbArray;
    }
    /**
     *  create Payment transaction (only payment mode cash)
     *
     * @param  $transaction_type,$ssbAccountId,$memberId,$branch_id,
     *  $branchCode,$amount,$payment_mode,$deposit_by_name,$deposit_by_id
     * @return \Illuminate\Http\Response
     */
    public static function createPaymentTransaction($transaction_type, $ssbAccountId, $memberId, $branch_id, $branchCode, $amount, $payment_mode, $deposit_by_name, $deposit_by_id)
    {
        $globaldate = Session::get('created_at');
        $getSsbAcoountNo = getSsbAccountNumber($ssbAccountId);
        $data['transaction_type'] = $transaction_type;
        $data['transaction_type_id'] = $ssbAccountId;
        $data['account_no'] = $getSsbAcoountNo->account_no;
        $data['member_id'] = $memberId;
        $data['branch_id'] = $branch_id;
        $data['branch_code'] = $branchCode;
        $data['amount'] = $amount;
        $data['currency_code'] = 'INR';
        $data['payment_mode'] = $payment_mode;

        $data['amount_deposit_by_name'] = $deposit_by_name;
        $data['amount_deposit_by_id'] = $deposit_by_id;
        $data['created_by_id'] = Auth::user()->id;
        $data['created_by'] = (Auth::user()->role_id == '3') ? 2 : 1;
        $data['created_by'] = $globaldate;
        $transcation = Transcation::create($data);
        $tran_id = $transcation->id;


        $data_log['transaction_id'] = $tran_id;
        $data_log['transaction_type'] = $transaction_type;
        $data_log['transaction_type_id'] = $ssbAccountId;
        $data_log['account_no'] = $getSsbAcoountNo->account_no;
        $data_log['member_id'] = $memberId;
        $data_log['branch_id'] = $branch_id;
        $data_log['branch_code'] = $branchCode;
        $data_log['amount'] = $amount;
        $data_log['currency_code'] = 'INR';
        $data_log['payment_mode'] = $payment_mode;

        $data_log['amount_deposit_by_name'] = $deposit_by_name;
        $data_log['amount_deposit_by_id'] = $deposit_by_id;
        $data_log['created_by_id'] = Auth::user()->id;
        $data_log['created_by'] = (Auth::user()->role_id == '3') ? 2 : 1;;
        $data_log['created_by'] = $globaldate;
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
    public static function createPaymentRecipt($tranID, $ssbAccountId, $memberId, $branch_id, $branchCode, $amountArray, $typeArray, $receipts_for, $account_no)
    {

        $globaldate = Session::get('created_at');
        $data['transaction_id'] = $tranID;
        $data['receipt_by_id'] = $ssbAccountId;
        $data['account_no'] = $account_no;
        $data['member_id'] = $memberId;
        $data['branch_id'] = $branch_id;
        $data['branch_code'] = $branchCode;
        $data['created_by_id'] = Auth::user()->id;
        $data['created_by'] = (Auth::user()->role_id == '3') ? 2 : 1;
        $data['receipts_for'] = $receipts_for;
        $data['created_at'] = $globaldate;
        $recipt = Receipt::create($data);
        $recipt_id = $recipt->id;

        foreach ($amountArray as $key => $option) {
            $data_amount['receipt_id'] = $recipt_id;
            $data_amount['amount'] = $option;
            $data_amount['type_label'] = $typeArray[$key];
            $data_amount['currency_code'] = 'INR';
            $data['receipts_for'] = $receipts_for;
            $data['created_at'] = $globaldate;
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
    public static function createTransaction($satRefId, $transaction_type, $account_id, $memberId, $branch_id, $branchCode, $amountArray, $payment_mode, $deposit_by_name, $deposit_by_id, $account_no, $cheque_dd_no, $bank_name, $branch_name, $payment_date, $online_payment_id, $online_payment_by, $saving_account_id, $payment_type)
    {
        $globaldate = Session::get('created_at');
        foreach ($amountArray as $key => $option) {
            $data['saving_account_transaction_reference_id'] = $satRefId;
            $data['transaction_type'] = $transaction_type;
            $data['transaction_type_id'] = $account_id;
            $data['account_no'] = $account_no;
            $data['member_id'] = $memberId;
            $data['branch_id'] = $branch_id;
            $data['branch_code'] = $branchCode;
            $data['amount'] = $option;
            $data['currency_code'] = 'INR';
            $data['payment_mode'] = $payment_mode;
            $data['payment_type'] = $payment_type;
            if ($payment_mode == 1 || $payment_mode == 2) {
                $data['cheque_dd_no'] = $cheque_dd_no;
                $data['bank_name'] = $bank_name;
                $data['branch_name'] = $branch_name;
                if ($payment_date != null || $payment_date != 'null') {
                    $data['payment_date'] = date("Y-m-d", strtotime($payment_date));
                }
            }
            if ($payment_mode == 3) {
                $data['online_payment_id'] = $online_payment_id;
                $data['online_payment_by'] = $online_payment_by;
                if ($payment_date != null || $payment_date != 'null') {
                    $data['payment_date'] = date("Y-m-d", strtotime($payment_date));
                }
            }
            if ($payment_mode == 4) {
                $data['saving_account_id'] = $saving_account_id;
                if ($payment_date != null || $payment_date != 'null') {
                    $data['payment_date'] = date("Y-m-d", strtotime($payment_date));
                }
            }
            $data['amount_deposit_by_name'] = $deposit_by_name;
            if ($deposit_by_id) {
                $data_log['amount_deposit_by_id'] = $deposit_by_id;
            }
            $data['created_by_id'] = Auth::user()->id;
            $data['created_by'] = (Auth::user()->role_id == '3') ? 2 : 1;
            $data['created_at'] = $globaldate;
            $transcation = Transcation::create($data);
            $tran_id = $transcation->id;

            $data_log['saving_account_transaction_reference_id'] = $satRefId;
            $data_log['transaction_id'] = $tran_id;
            $data_log['transaction_type'] = $transaction_type;
            $data_log['transaction_type_id'] = $account_id;
            $data_log['account_no'] = $account_no;
            $data_log['member_id'] = $memberId;
            $data_log['branch_id'] = $branch_id;
            $data_log['branch_code'] = $branchCode;
            $data_log['amount'] = $option;
            $data_log['currency_code'] = 'INR';
            $data_log['payment_mode'] = $payment_mode;
            $data_log['payment_type'] = $payment_type;
            if ($payment_mode == 1 || $payment_mode == 2) {
                $data_log['cheque_dd_no'] = $cheque_dd_no;
                $data_log['bank_name'] = $bank_name;
                $data_log['branch_name'] = $branch_name;
                if ($payment_date != null || $payment_date != 'null') {
                    $data_log['payment_date'] = date("Y-m-d", strtotime($payment_date));
                }
            }
            if ($payment_mode == 3) {
                $data_log['online_payment_id'] = $online_payment_id;
                $data_log['online_payment_by'] = $online_payment_by;
                if ($payment_date != null || $payment_date != 'null') {
                    $data_log['payment_date'] = date("Y-m-d", strtotime($payment_date));
                }
            }
            if ($payment_mode == 4) {
                $data_log['saving_account_id'] = $saving_account_id;
                if ($payment_date != null || $payment_date != 'null') {
                    $data_log['payment_date'] = date("Y-m-d", strtotime($payment_date));
                }
            }
            $data_log['amount_deposit_by_name'] = $deposit_by_name;
            if ($deposit_by_id) {
                $data_log['amount_deposit_by_id'] = $deposit_by_id;
            }
            $data_log['created_by_id'] = Auth::user()->id;
            $data_log['created_by'] = (Auth::user()->role_id == '3') ? 2 : 1;;
            $data_log['created_at'] = $globaldate;
            $transcation_log = TranscationLog::create($data_log);
        }
        return $tran_id;
    }

    /**
     *  create day book transaction
     *
     * @param  $transaction_type,$ssbAccountId,$memberId,$branch_id,
     *  $branchCode,$amount,$payment_mode,$deposit_by_name,$deposit_by_id
     * @return \Illuminate\Http\Response
     */
    public static function createDayBook($transaction_id, $satRefId, $transaction_type, $account_id, $associateId, $memberId, $openingBalance, $deposite, $withdrawal, $description, $referenceno, $branch_id, $branchCode, $amountArray, $payment_mode, $deposit_by_name, $deposit_by_id, $account_no, $cheque_dd_no, $bank_name, $branch_name, $payment_date, $online_payment_id, $online_payment_by, $saving_account_id, $payment_type, $companyId)
    {
        $globaldate = Session::get('created_at');
        foreach ($amountArray as $key => $option) {
            $loanTypeArray = array(3, 5, 6, 8, 9, 10, 11, 12);
            $investmentTypeArray = array(3, 5, 6, 8, 9, 10, 11, 12);
            $data_log['transaction_type'] = $transaction_type;
            $data_log['saving_account_transaction_reference_id'] = $satRefId;
            $data_log['transaction_id'] = $transaction_id;
            if (in_array($transaction_type, $loanTypeArray)) {
                $data_log['loan_id'] = $account_id;
            } elseif ($transaction_type == 13) {
                $data_log['rent_id'] = $account_id;
            } else {
                $data_log['investment_id'] = $account_id;
            }
            $data_log['account_no'] = $account_no;
            $data_log['associate_id'] = $associateId;
            $data_log['member_id'] = $memberId;
            $data_log['opening_balance'] = $openingBalance;
            $data_log['deposit'] = $deposite;
            $data_log['withdrawal'] = $withdrawal;
            $data_log['description'] = $description;
            $data_log['reference_no'] = $referenceno;
            $data_log['branch_id'] = $branch_id;
            $data_log['branch_code'] = $branchCode;
            $data_log['amount'] = $option;
            $data_log['currency_code'] = 'INR';
            $data_log['payment_mode'] = $payment_mode;
            $data_log['payment_type'] = $payment_type;
            $data_log['company_id'] = $companyId;
            if ($payment_mode == 1 || $payment_mode == 2) {
                $data_log['cheque_dd_no'] = $cheque_dd_no;
                $data_log['bank_name'] = $bank_name;
                $data_log['branch_name'] = $branch_name;
                if ($payment_date != null || $payment_date != 'null') {
                    $data_log['payment_date'] = date("Y-m-d H:i:s", strtotime($payment_date));
                }
            }
            if ($payment_mode == 3) {
                $data_log['online_payment_id'] = $online_payment_id;
                $data_log['online_payment_by'] = $online_payment_by;
                if ($payment_date != null || $payment_date != 'null') {
                    $data_log['payment_date'] = date("Y-m-d H:i:s", strtotime($payment_date));
                }
            }
            if ($payment_mode == 4) {
                $data_log['saving_account_id'] = $saving_account_id;
                if ($payment_date != null || $payment_date != 'null') {
                    $data_log['payment_date'] = date("Y-m-d H:i:s", strtotime($payment_date));
                }
            }
            $data_log['amount_deposit_by_name'] = $deposit_by_name;
            $data_log['amount_deposit_by_id'] = $deposit_by_id;
            $data_log['created_by_id'] = Auth::user()->id;
            $data_log['created_by'] = (Auth::user()->role_id == '3') ? 2 : 1;;
            $data_log['created_at'] = date("Y-m-d H:i:s", strtotime($payment_date));
            $data_log['created_at'] = $globaldate;
            $transcation = Daybook::create($data_log);
            $tran_id = $transcation->id;
        }
        return $tran_id;
    }

    /**
     *  create loan day book transaction
     *
     * @param  $transaction_type,$ssbAccountId,$memberId,$branch_id,
     *  $branchCode,$amount,$payment_mode,$deposit_by_name,$deposit_by_id
     * @return \Illuminate\Http\Response
     */
    public static function createLoanDayBook($roidayBookRef, $daybookid, $loan_type, $loan_sub_type, $loan_id, $group_loan_id, $account_number, $applicant_id, $roi_amount, $principal_amount, $opening_balance, $deposit, $description, $branch_id, $branch_code, $payment_type, $currency_code, $payment_mode, $cheque_dd_no, $bank_name, $branch_name, $payment_date, $online_payment_id, $created_by, $status, $cheque_date, $bank_account_number, $online_payment_by, $amount_deposit_by_name, $associate_id, $amount_deposit_by_id, $totalDailyInterest = Null, $totalDayInterest = Null, $penalty = NUll,$companyId=NULL)
    {
        $globaldate = Session::get('created_at');
        $data['daybook_ref_id'] = $roidayBookRef;
        $data['day_book_id'] = $daybookid;
        $data['loan_type'] = $loan_type;
        $data['loan_sub_type'] = $loan_sub_type;
        $data['loan_id'] = $loan_id;
        $data['group_loan_id'] = $group_loan_id;
        $data['account_number'] = $account_number;
        $data['applicant_id'] = $applicant_id;
        $data['associate_id'] = $associate_id;
        $data['roi_amount'] = $roi_amount;
        $data['principal_amount'] = $principal_amount;
        $data['opening_balance'] = $opening_balance;
        $data['deposit'] = $deposit;
        $data['description'] = $description;
        $data['branch_id'] = $branch_id;
        $data['branch_code'] = $branch_code;
        $data['payment_type'] = $payment_type;
        $data['currency_code'] = $currency_code;
        $data['payment_mode'] = $payment_mode;
        $data['payment_date'] = date("Y-m-d", strtotime(convertDate($payment_date)));
        $data['created_by'] = (Auth::user()->role_id == '3') ? 2 : 1;
        $data['status'] = $status;
        $data['created_at'] = $globaldate;

        if ($payment_mode == 1 || $payment_mode == 2) {
            $data['cheque_dd_id'] = $cheque_dd_no;
            $data['cheque_date'] = date("Y-m-d", strtotime(convertDate($cheque_date)));
            $data['bank_id'] = $bank_name;
            $data['bank_account_number'] = $bank_account_number;
            $data['branch_name'] = $branch_name;
        }
        if ($payment_mode == 3) {
            $data['online_payment_id'] = $online_payment_id;
        }
        $data['online_payment_by'] = $online_payment_by;
        $data['amount_deposit_by_name'] = $amount_deposit_by_name;
        $data['amount_deposit_by_id'] = $amount_deposit_by_id;
        $data['emi_late_no_of_days'] = $totalDayInterest;
        $data['daily_wise_interest'] = $totalDailyInterest;
        $data['penalty'] = $penalty;
        $data['company_id'] = $companyId;

        $loadDayBook = LoanDayBooks::create($data);
        $loaddaybook_id = $loadDayBook->id;
        return $loaddaybook_id;
    }

    /**
     *  create branch day book refresh transaction
     *
     * @param  $amount,$entry_date,$entry_time,$created_at
     * @return \Illuminate\Http\Response
     */
    public static function createBranchDayBookReference($amount, $created_at)
    {
        $t = date("H:i:s");
        $data['amount'] = $amount;
        $data['entry_date'] = date("Y-m-d", strtotime(convertDate($created_at)));
        $data['entry_time'] = $t;
        $data['created_at'] = date("Y-m-d " . $t . "", strtotime(convertDate($created_at)));
        $transcation = \App\Models\BranchDaybookReference::create($data);
        return $transcation->id;
    }

    /**
     *  create branch day book
     *
     * @param  $transaction_type,$ssbAccountId,$memberId,$branch_id,
     *  $branchCode,$amount,$payment_mode,$deposit_by_name,$deposit_by_id
     * @return \Illuminate\Http\Response
     */
    public static function createBranchDayBook($daybook_ref_id, $branch_id, $type, $sub_type, $type_id, $type_transaction_id, $associate_id, $member_id, $branch_id_to, $branch_id_from, $opening_balance, $amount, $closing_balance, $description, $description_dr, $description_cr, $payment_type, $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $is_contra, $contra_id, $created_at)
    {
        $t = date("H:i:s");
        $data['daybook_ref_id'] = $daybook_ref_id;
        $data['branch_id'] = $branch_id;
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
        $data['description_dr'] = $description_dr;
        $data['description_cr'] = $description_cr;
        $data['payment_type'] = $payment_type;
        $data['payment_mode'] = $payment_mode;
        $data['currency_code'] = $currency_code;
        $data['amount_to_id'] = $amount_to_id;
        $data['amount_to_name'] = $amount_to_name;
        $data['amount_from_id'] = $amount_from_id;
        $data['amount_from_name'] = $amount_from_name;
        $data['v_no'] = $v_no;
        $data['v_date'] = $v_date;
        $data['ssb_account_id_from'] = $ssb_account_id_from;
        $data['cheque_no'] = $cheque_no;
        $data['cheque_date'] = $cheque_date;
        $data['cheque_bank_from'] = $cheque_bank_from;
        $data['cheque_bank_ac_from'] = $cheque_bank_ac_from;
        $data['cheque_bank_ifsc_from'] = $cheque_bank_ifsc_from;
        $data['cheque_bank_branch_from'] = $cheque_bank_branch_from;
        $data['cheque_bank_to'] = $cheque_bank_to;
        $data['cheque_bank_ac_to'] = $cheque_bank_to;
        $data['transction_no'] = $transction_no;
        $data['transction_bank_from'] = $transction_bank_from;
        $data['transction_bank_ac_from'] = $transction_bank_ac_from;
        $data['transction_bank_ifsc_from'] = $transction_bank_ifsc_from;
        $data['transction_bank_branch_from'] = $transction_bank_branch_from;
        $data['transction_bank_to'] = $transction_bank_to;
        $data['transction_bank_ac_to'] = $transction_bank_ac_to;
        $data['transction_date'] = date("Y-m-d", strtotime(convertDate($created_at)));
        $data['entry_date'] = date("Y-m-d", strtotime(convertDate($created_at)));
        $data['entry_time'] = date("H:i:s");
        $data['created_by'] = (Auth::user()->role_id == '3') ? 2 : 1;
        $data['created_by_id'] = $created_by_id;
        $data['is_contra'] = $is_contra;
        $data['contra_id'] = $contra_id;
        $data['created_at'] = date("Y-m-d " . $t . "", strtotime(convertDate($created_at)));
        $transcation = \App\Models\BranchDaybook::create($data);
        return $transcation->id;
    }

    public static function branchDayBookNew(
        $daybook_ref_id,
        $branch_id,
        $type,
        $sub_type,
        $type_id,
        $type_transaction_id,
        $associate_id,
        $member_id,
        $branch_id_to,
        $branch_id_from,
        $amount,
        $description,
        $description_dr,
        $description_cr,
        $payment_type,
        $payment_mode,
        $currency_code,
        $v_no,
        $ssb_account_id_from,
        $cheque_no,
        $transction_no,
        $entry_date,
        $entry_time,
        $created_by,
        $created_by_id,
        $created_at,
        $ssb_account_tran_id_to,
        $ssb_account_tran_id_from,
        $jv_unique_id,
        $cheque_type,
        $cheque_id,
        $companyId
    ) {
        $globaldate = Session::get('created_at');
        $t = date("H:i:s");
        $data['daybook_ref_id'] = $daybook_ref_id;
        $data['branch_id'] = $branch_id;
        $data['type'] = $type;
        $data['sub_type'] = $sub_type;
        $data['type_id'] = $type_id;
        $data['type_transaction_id'] = $type_transaction_id;
        $data['associate_id'] = $associate_id;
        $data['member_id'] = $member_id;
        $data['branch_id_to'] = $branch_id_to;
        $data['branch_id_from'] = $branch_id_from;
        $data['amount'] = $amount;
        $data['description'] = $description;
        $data['description_dr'] = $description_dr;
        $data['description_cr'] = $description_cr;
        $data['payment_type'] = $payment_type;
        $data['payment_mode'] = $payment_mode;
        $data['currency_code'] = $currency_code;
        $data['v_no'] = $v_no;
        $data['ssb_account_id_from'] = $ssb_account_id_from;
        $data['cheque_no'] = $cheque_no;
        $data['transction_no'] = $transction_no;
        $data['entry_date'] = date("Y-m-d", strtotime(convertDate($globaldate)));
        $data['entry_time'] = date("H:i:s");
        $data['created_by'] = (Auth::user()->role_id == '3') ? 2 : 1;
        $data['created_by_id'] = $created_by_id;
        $data['created_at'] = date("Y-m-d " . $t . "", strtotime(convertDate($globaldate)));
        $data['ssb_account_tran_id_to'] = $ssb_account_tran_id_to;
        $data['ssb_account_tran_id_from'] = $ssb_account_tran_id_from;
        $data['jv_unique_id'] = $jv_unique_id;
        $data['cheque_type'] = $cheque_type;
        $data['cheque_id'] = $cheque_id;
        $data['company_id'] = $companyId;
        $transcation = \App\Models\BranchDaybook::create($data);
        return true;
    }

    /**
     *  create all transaction
     *
     * @param  $transaction_type,$ssbAccountId,$memberId,$branch_id,
     *  $branchCode,$amount,$payment_mode,$deposit_by_name,$deposit_by_id
     * @return \Illuminate\Http\Response
     */
    public static function createAllTransaction($daybook_ref_id, $branch_id, $bank_id, $bank_ac_id, $head1, $head2, $head3, $head4, $head5, $type, $sub_type, $type_id, $type_transaction_id, $associate_id, $member_id, $branch_id_to, $branch_id_from, $opening_balance, $amount, $closing_balance, $description, $payment_type, $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at)
    {
        $t = date("H:i:s");
        $data['daybook_ref_id'] = $daybook_ref_id;
        $data['branch_id'] = $branch_id;
        $data['bank_id'] = $bank_id;
        $data['bank_ac_id'] = $bank_ac_id;
        $data['head1'] = $head1;
        $data['head2'] = $head2;
        $data['head3'] = $head3;
        $data['head4'] = $head4;
        $data['head5'] = $head5;
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
        $data['v_no'] = $v_no;
        $data['v_date'] = $v_date;
        $data['ssb_account_id_from'] = $ssb_account_id_from;
        $data['cheque_no'] = $cheque_no;
        $data['cheque_date'] = $cheque_date;
        $data['cheque_bank_from'] = $cheque_bank_from;
        $data['cheque_bank_ac_from'] = $cheque_bank_ac_from;
        $data['cheque_bank_ifsc_from'] = $cheque_bank_ifsc_from;
        $data['cheque_bank_branch_from'] = $cheque_bank_branch_from;
        $data['cheque_bank_to'] = $cheque_bank_to;
        $data['cheque_bank_ac_to'] = $cheque_bank_ac_to;
        $data['transction_no'] = $transction_no;
        $data['transction_bank_from'] = $transction_bank_from;
        $data['transction_bank_ac_from'] = $transction_bank_ac_from;
        $data['transction_bank_ifsc_from'] = $transction_bank_ifsc_from;
        $data['transction_bank_branch_from'] = $transction_bank_branch_from;
        $data['transction_bank_to'] = $transction_bank_to;
        $data['transction_bank_ac_to'] = $transction_bank_ac_to;
        $data['transction_date'] = $transction_date;
        $data['entry_date'] = date("Y-m-d", strtotime(convertDate($created_at)));
        $data['entry_time'] = date("H:i:s");
        $data['created_by'] = (Auth::user()->role_id == '3') ? 2 : 1;
        $data['created_by_id'] = $created_by_id;
        $data['created_at'] = date("Y-m-d " . $t . "", strtotime(convertDate($created_at)));
        $transcation = \App\Models\AllTransaction::create($data);
        return $transcation->id;
    }

    public static function createAllHeadTransaction(
        $daybook_ref_id,
        $branch_id,
        $bank_id,
        $bank_ac_id,
        $head_id,
        $type,
        $sub_type,
        $type_id,
        $type_transaction_id,
        $associate_id,
        $member_id,
        $branch_id_to,
        $branch_id_from,
        $amount,
        $description,
        $payment_type,
        $payment_mode,
        $currency_code,
        $jv_unique_id,
        $v_no,
        $ssb_account_id_from,
        $ssb_account_id_to,
        $ssb_account_tran_id_to,
        $ssb_account_tran_id_from,
        $cheque_type,
        $cheque_id,
        $cheque_no,
        $transction_no,
        $created_by,
        $created_by_id,
        $companyId
    ) {
        $globaldate = Session::get('created_at');
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
        $data['amount'] = $amount;
        $data['description'] = $description;
        $data['payment_type'] = $payment_type;
        $data['payment_mode'] = $payment_mode;
        $data['currency_code'] = $currency_code;
        $data['jv_unique_id'] = $jv_unique_id;
        $data['v_no'] = $v_no;
        $data['ssb_account_id_from'] = $ssb_account_id_from;
        $data['ssb_account_id_to'] = $ssb_account_id_to;
        $data['ssb_account_tran_id_to'] = $ssb_account_tran_id_to;
        $data['ssb_account_tran_id_from'] = $ssb_account_tran_id_from;
        $data['cheque_type'] = $cheque_type;
        $data['cheque_id'] = $cheque_id;
        $data['cheque_no'] = $cheque_no;
        $data['transction_no'] = $transction_no;
        $data['entry_date'] = date("Y-m-d", strtotime(convertDate($globaldate)));
        $data['entry_time'] = date("H:i:s");
        $data['created_by'] = (Auth::user()->role_id == '3') ? 2 : 1;
        $data['created_by_id'] = $created_by_id;
        $data['company_id'] = $companyId;
        $data['created_at'] = date("Y-m-d " . $t . "", strtotime(convertDate($globaldate)));
        $transcation = \App\Models\AllHeadTransaction::create($data);
        return true;
    }

    /**
     *  create branch day book
     *
     * @param  $transaction_type,$ssbAccountId,$memberId,$branch_id,
     *  $branchCode,$amount,$payment_mode,$deposit_by_name,$deposit_by_id
     * @return \Illuminate\Http\Response
     */
    public static function createMemberTransaction($daybook_ref_id, $type, $sub_type, $type_id, $type_transaction_id, $associate_id, $member_id, $branch_id, $bank_id, $account_id, $amount, $description, $payment_type, $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at)
    {
        $t = date("H:i:s");
        $data['daybook_ref_id'] = $daybook_ref_id;
        $data['type'] = $type;
        $data['sub_type'] = $sub_type;
        $data['type_id'] = $type_id;
        $data['type_transaction_id'] = $type_transaction_id;
        $data['associate_id'] = $associate_id;
        $data['member_id'] = $member_id;
        $data['branch_id'] = $branch_id;
        $data['bank_id'] = $bank_id;
        $data['account_id'] = $account_id;
        $data['amount'] = $amount;
        $data['description'] = $description;
        $data['payment_type'] = $payment_type;
        $data['payment_mode'] = $payment_mode;
        $data['currency_code'] = $currency_code;
        $data['amount_to_id'] = $amount_to_id;
        $data['amount_to_name'] = $amount_to_name;
        $data['amount_from_id'] = $amount_from_id;
        $data['amount_from_name'] = $amount_from_name;
        $data['v_no'] = $v_no;
        $data['v_date'] = $v_date;
        $data['ssb_account_id_from'] = $ssb_account_id_from;
        $data['cheque_no'] = $cheque_no;
        $data['cheque_date'] = $cheque_date;
        $data['cheque_bank_from'] = $cheque_bank_from;
        $data['cheque_bank_ac_from'] = $cheque_bank_ac_from;
        $data['cheque_bank_ifsc_from'] = $cheque_bank_ifsc_from;
        $data['cheque_bank_branch_from'] = $cheque_bank_branch_from;
        $data['cheque_bank_to'] = $cheque_bank_to;
        $data['cheque_bank_ac_to'] = $cheque_bank_ac_to;
        $data['transction_no'] = $transction_no;
        $data['transction_bank_from'] = $transction_bank_from;
        $data['transction_bank_ac_from'] = $transction_bank_ac_from;
        $data['transction_bank_ifsc_from'] = $transction_bank_ifsc_from;
        $data['transction_bank_branch_from'] = $transction_bank_branch_from;
        $data['transction_bank_to'] = $transction_bank_to;
        $data['transction_bank_ac_to'] = $transction_bank_ac_to;
        $data['transction_date'] = date("Y-m-d", strtotime(convertDate($created_at)));
        $data['entry_date'] = date("Y-m-d", strtotime(convertDate($created_at)));
        $data['entry_time'] = date("H:i:s");
        $data['created_by'] = (Auth::user()->role_id == '3') ? 2 : 1;
        $data['created_by_id'] = $created_by_id;
        $data['created_at'] = date("Y-m-d " . $t . "", strtotime(convertDate($created_at)));
        //echo "<pre>"; print_r($data); die;
        $transcation = \App\Models\MemberTransaction::create($data);
        return $transcation->id;
    }

    public static function memberTransactionNew($daybook_ref_id, $type, $sub_type, $type_id, $type_transaction_id, $associate_id, $member_id, $branch_id, $bank_id, $account_id, $amount, $description, $payment_type, $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $jv_unique_id, $cheque_type, $cheque_id)
    {
        $globaldate = Session::get('created_at');
        $t = date("H:i:s");
        $data['daybook_ref_id'] = $daybook_ref_id;
        $data['type'] = $type;
        $data['sub_type'] = $sub_type;
        $data['type_id'] = $type_id;
        $data['type_transaction_id'] = $type_transaction_id;
        $data['associate_id'] = $associate_id;
        $data['member_id'] = $member_id;
        $data['branch_id'] = $branch_id;
        $data['bank_id'] = $bank_id;
        $data['account_id'] = $account_id;
        $data['amount'] = $amount;
        $data['description'] = $description;
        $data['payment_type'] = $payment_type;
        $data['payment_mode'] = $payment_mode;
        $data['currency_code'] = $currency_code;
        $data['amount_to_id'] = $amount_to_id;
        $data['amount_to_name'] = $amount_to_name;
        $data['amount_from_id'] = $amount_from_id;
        $data['amount_from_name'] = $amount_from_name;
        $data['v_no'] = $v_no;
        $data['v_date'] = $v_date;
        $data['ssb_account_id_from'] = $ssb_account_id_from;
        $data['cheque_no'] = $cheque_no;
        $data['cheque_date'] = $cheque_date;
        $data['cheque_bank_from'] = $cheque_bank_from;
        $data['cheque_bank_ac_from'] = $cheque_bank_ac_from;
        $data['cheque_bank_ifsc_from'] = $cheque_bank_ifsc_from;
        $data['cheque_bank_branch_from'] = $cheque_bank_branch_from;
        $data['cheque_bank_to'] = $cheque_bank_to;
        $data['cheque_bank_ac_to'] = $cheque_bank_ac_to;
        $data['transction_no'] = $transction_no;
        $data['transction_bank_from'] = $transction_bank_from;
        $data['transction_bank_ac_from'] = $transction_bank_ac_from;
        $data['transction_bank_ifsc_from'] = $transction_bank_ifsc_from;
        $data['transction_bank_branch_from'] = $transction_bank_branch_from;
        $data['transction_bank_to'] = $transction_bank_to;
        $data['transction_bank_ac_to'] = $transction_bank_ac_to;
        $data['transction_date'] = date("Y-m-d", strtotime(convertDate($globaldate)));
        $data['entry_date'] = date("Y-m-d", strtotime(convertDate($globaldate)));
        $data['entry_time'] = date("H:i:s");
        $data['created_by'] = (Auth::user()->role_id == '3') ? 2 : 1;
        $data['created_by_id'] = $created_by_id;
        $data['created_at'] = date("Y-m-d " . $t . "", strtotime(convertDate($globaldate)));
        $data['ssb_account_tran_id_to'] = $ssb_account_tran_id_to;
        $data['ssb_account_tran_id_from'] = $ssb_account_tran_id_from;
        $data['jv_unique_id'] = $jv_unique_id;
        $data['cheque_type'] = $cheque_type;
        $data['cheque_id'] = $cheque_id;
        $transcation = \App\Models\MemberTransaction::create($data);
        return true;
    }

    /**
     *  create branch day book
     *
     * @param  $transaction_type,$ssbAccountId,$memberId,$branch_id,
     *  $branchCode,$amount,$payment_mode,$deposit_by_name,$deposit_by_id
     * @return \Illuminate\Http\Response
     */
    public static function createSamraddhBankDaybook($daybook_ref_id, $bank_id, $account_id, $type, $sub_type, $type_id, $type_transaction_id, $associate_id, $member_id, $branch_id, $opening_balance, $amount, $closing_balance, $description, $description_dr, $description_cr, $payment_type, $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at)
    {
        $t = date("H:i:s");
        $data['daybook_ref_id'] = $daybook_ref_id;
        $data['bank_id'] = $bank_id;
        $data['account_id'] = $account_id;
        $data['type'] = $type;
        $data['sub_type'] = $sub_type;
        $data['type_id'] = $type_id;
        $data['type_transaction_id'] = $type_transaction_id;
        $data['associate_id'] = $associate_id;
        $data['member_id'] = $member_id;
        $data['branch_id'] = $branch_id;
        $data['opening_balance'] = $opening_balance;
        $data['amount'] = $amount;
        $data['closing_balance'] = $closing_balance;
        $data['description'] = $description;
        $data['description_dr'] = $description_dr;
        $data['description_cr'] = $description_cr;
        $data['payment_type'] = $payment_type;
        $data['payment_mode'] = $payment_mode;
        $data['currency_code'] = $currency_code;
        $data['amount_to_id'] = $amount_to_id;
        $data['amount_to_name'] = $amount_to_name;
        $data['amount_from_id'] = $amount_from_id;
        $data['amount_from_name'] = $amount_from_name;
        $data['v_no'] = $v_no;
        $data['v_date'] = $v_date;
        $data['ssb_account_id_from'] = $ssb_account_id_from;
        $data['cheque_no'] = $cheque_no;
        $data['cheque_date'] = $cheque_date;
        $data['cheque_bank_from'] = $cheque_bank_from;
        $data['cheque_bank_ac_from'] = $cheque_bank_ac_from;
        $data['cheque_bank_ifsc_from'] = $cheque_bank_ifsc_from;
        $data['cheque_bank_branch_from'] = $cheque_bank_branch_from;
        $data['cheque_bank_to'] = $cheque_bank_to;
        $data['cheque_bank_ac_to'] = $cheque_bank_ac_to;
        $data['transction_no'] = $transction_no;
        $data['transction_bank_from'] = $transction_bank_from;
        $data['transction_bank_ac_from'] = $transction_bank_ac_from;
        $data['transction_bank_ifsc_from'] = $transction_bank_ifsc_from;
        $data['transction_bank_branch_from'] = $transction_bank_branch_from;
        $data['transction_bank_to'] = $transction_bank_to;
        $data['transction_bank_ac_to'] = $transction_bank_ac_to;
        $data['transction_date'] = date("Y-m-d", strtotime(convertDate($created_at)));
        $data['entry_date'] = date("Y-m-d", strtotime(convertDate($created_at)));
        $data['entry_time'] = date("H:i:s");
        $data['created_by'] = (Auth::user()->role_id == '3') ? 2 : 1;
        $data['created_by_id'] = $created_by_id;
        $data['created_at'] = date("Y-m-d " . $t . "", strtotime(convertDate($created_at)));
        $transcation = \App\Models\SamraddhBankDaybook::create($data);
        return $transcation->id;
    }

    /**
     *  create bank cash
     *
     * @param  $transaction_type,$ssbAccountId,$memberId,$branch_id,
     *  $branchCode,$amount,$payment_mode,$deposit_by_name,$deposit_by_id
     * @return \Illuminate\Http\Response
     */
    public static function createBankCash($branch_id, $date, $amount, $type)
    {
        $entryTime = date("H:i:s");
        $entryDate = date("Y-m-d", strtotime(convertDate($date)));
        $currentDateRecord = \App\Models\BranchCash::where('branch_id', $branch_id)->whereDate('entry_date', $entryDate)->first();

        if ($currentDateRecord) {
            $Result = \App\Models\BranchCash::find($currentDateRecord->id);
            /*if($type == 0){
            $data['balance']=$currentDateRecord->balance+$amount;
            }elseif($type == 1){
            $data['loan_balance']=$currentDateRecord->loan_balance+$amount;
            }*/

            $data['balance'] = $currentDateRecord->balance + $amount;
            $data['updated_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($date)));
            $Result->update($data);
            $insertid = $currentDateRecord->id;
        } else {
            $oldDateRecord = \App\Models\BranchCash::where('branch_id', $branch_id)->whereDate('entry_date', '<', $entryDate)->orderby('entry_date', 'DESC')->first();
            if ($oldDateRecord) {
                $Result1 = \App\Models\BranchCash::find($oldDateRecord->id);
                $data1['closing_balance'] = $oldDateRecord->balance;
                //$data1['loan_closing_balance']=$oldDateRecord->loan_balance;
                $Result1->update($data1);
                $insertid1 = $oldDateRecord->id;

                /*if($type == 0){
                $data['balance']=$oldDateRecord->balance+$amount;
                }else{
                $data['balance']=$oldDateRecord->balance;
                }
                if($type == 1){
                $data['loan_balance']=$oldDateRecord->loan_balance+$amount;
                }
                else{
                $data['loan_balance']=$oldDateRecord->loan_balance;
                }*/

                $data['balance'] = $oldDateRecord->balance + $amount;
                $data['opening_balance'] = $oldDateRecord->balance;
                $data['closing_balance'] = 0;
                //$data['loan_opening_balance']=$oldDateRecord->loan_balance;
                //$data['loan_closing_balance']=0;

                $data['branch_id'] = $branch_id;
                $data['entry_date'] = $entryDate;
                $data['entry_time'] = $entryTime;
                $data['type'] = $type;
                $data['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($date)));
                $transcation = \App\Models\BranchCash::create($data);
                $insertid = $transcation->id;
            } else {
                /*if($type == 0){
                $data['balance']=$amount;
                }
                else                {
                $data['balance']=0;
                }
                if($type == 1){
                $data['loan_balance']=$amount;
                }
                else{
                $data['loan_balance']=0;
                }*/

                $data['balance'] = $amount;
                $data['opening_balance'] = 0;
                $data['closing_balance'] = 0;
                //$data['loan_opening_balance']=0;
                //$data['loan_closing_balance']=0;

                $data['branch_id'] = $branch_id;
                $data['entry_date'] = $entryDate;
                $data['entry_time'] = $entryTime;
                $data['type'] = $type;
                $data['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($date)));
                $transcation = \App\Models\BranchCash::create($data);
                $insertid = $transcation->id;
            }
        }
    }

    /**
     *  create bank cash
     *
     * @param  $transaction_type,$ssbAccountId,$memberId,$branch_id,
     *  $branchCode,$amount,$payment_mode,$deposit_by_name,$deposit_by_id
     * @return \Illuminate\Http\Response
     */
    public static function createBankClosing($branch_id, $date, $amount, $type)
    {
        $entryTime = date("H:i:s");
        $entryDate = date("Y-m-d", strtotime(convertDate($date)));
        $currentDateRecord = \App\Models\BranchClosing::where('branch_id', $branch_id)->whereDate('entry_date', $entryDate)->first();

        if ($currentDateRecord) {
            $Result = \App\Models\BranchClosing::find($currentDateRecord->id);
            /*if($type == 0){
            $data['balance']=$currentDateRecord->balance+$amount;
            }elseif($type == 1){
            $data['loan_balance']=$currentDateRecord->loan_balance+$amount;
            }*/

            $data['balance'] = $currentDateRecord->balance + $amount;
            $data['updated_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($date)));
            $Result->update($data);
            $insertid = $currentDateRecord->id;
        } else {
            $oldDateRecord = \App\Models\BranchClosing::where('branch_id', $branch_id)->whereDate('entry_date', '<', $entryDate)->orderby('entry_date', 'DESC')->first();
            if ($oldDateRecord) {
                $Result1 = \App\Models\BranchClosing::find($oldDateRecord->id);
                $data1['closing_balance'] = $oldDateRecord->balance;
                //$data1['loan_closing_balance']=$oldDateRecord->loan_balance;
                $Result1->update($data1);
                $insertid1 = $oldDateRecord->id;

                /*if($type == 0){
                $data['balance']=$oldDateRecord->balance+$amount;
                }else{
                $data['balance']=$oldDateRecord->balance;
                }
                if($type == 1){
                $data['loan_balance']=$oldDateRecord->loan_balance+$amount;
                }
                else{
                $data['loan_balance']=$oldDateRecord->loan_balance;
                }*/

                $data['balance'] = $oldDateRecord->balance + $amount;
                $data['opening_balance'] = $oldDateRecord->balance;
                $data['closing_balance'] = 0;
                //$data['loan_opening_balance']=$oldDateRecord->loan_balance;
                //$data['loan_closing_balance']=0;
                $data['branch_id'] = $branch_id;
                $data['entry_date'] = $entryDate;
                $data['entry_time'] = $entryTime;
                $data['type'] = $type;
                $data['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($date)));
                $transcation = \App\Models\BranchClosing::create($data);
                $insertid = $transcation->id;
            } else {
                /*if($type == 0){
                $data['balance']=$amount;
                }
                else                {
                $data['balance']=0;
                }
                if($type == 1){
                $data['loan_balance']=$amount;
                }
                else{
                $data['loan_balance']=0;
                }*/

                $data['balance'] = $amount;
                $data['opening_balance'] = 0;
                $data['closing_balance'] = 0;
                //$data['loan_opening_balance']=0;
                //$data['loan_closing_balance']=0;

                $data['branch_id'] = $branch_id;
                $data['entry_date'] = $entryDate;
                $data['entry_time'] = $entryTime;
                $data['type'] = $type;
                $data['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($date)));
                $transcation = \App\Models\BranchClosing::create($data);
                $insertid = $transcation->id;
            }
        }
        return $insertid;
    }

    /**
     *  create bank cash
     *
     * @param  $transaction_type,$ssbAccountId,$memberId,$branch_id,
     *  $branchCode,$amount,$payment_mode,$deposit_by_name,$deposit_by_id
     * @return \Illuminate\Http\Response
     */
    public static function createSamraddhBankClosing($bank_id, $account_id, $date, $amount, $type)
    {
        $entryTime = date("H:i:s");
        $entryDate = date("Y-m-d", strtotime(convertDate($date)));
        $currentDateRecord = \App\Models\SamraddhBankClosing::where('bank_id', $bank_id)->where('account_id', $account_id)->whereDate('entry_date', $entryDate)->first();

        if ($currentDateRecord) {
            $Result = \App\Models\SamraddhBankClosing::find($currentDateRecord->id);
            /*if($type == 0){
            $data['balance']=$currentDateRecord->balance+$amount;
            }
            elseif($type == 1){
            $data['loan_closing_balance']=$oldDateRecord->loan_balance+$amount;
            }*/

            $data['balance'] = $currentDateRecord->balance + $amount;

            $data['updated_at'] = $date;
            $Result->update($data);
            $insertid = $currentDateRecord->id;
        } else {
            $oldDateRecord = \App\Models\SamraddhBankClosing::where('bank_id', $bank_id)->where('account_id', $account_id)->whereDate('entry_date', '<', $entryDate)->orderby('entry_date', 'DESC')->first();
            if ($oldDateRecord) {
                $Result1 = \App\Models\SamraddhBankClosing::find($oldDateRecord->id);
                $data1['closing_balance'] = $oldDateRecord->balance;
                //$data1['loan_closing_balance']=$oldDateRecord->loan_balance;
                $Result1->update($data1);
                $insertid1 = $oldDateRecord->id;

                /*if($type == 0){
                $data['balance']=$oldDateRecord->balance+$amount;
                }else{
                $data['balance']=$oldDateRecord->balance;
                }*/
                $data['balance'] = $oldDateRecord->balance + $amount;
                /*if($type == 1){
                $data['loan_balance']=$oldDateRecord->loan_balance+$amount;
                }
                else{
                $data['loan_balance']=$oldDateRecord->loan_balance;
                }*/
                $data['opening_balance'] = $oldDateRecord->balance;
                $data['closing_balance'] = 0;
                /*$data['loan_opening_balance']=$oldDateRecord->loan_balance;
                $data['loan_closing_balance']=0;*/

                $data['bank_id'] = $bank_id;
                $data['account_id'] = $account_id;
                $data['entry_date'] = $entryDate;
                $data['entry_time'] = $entryTime;
                $data['type'] = $type;
                $data['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($date)));
                $transcation = \App\Models\SamraddhBankClosing::create($data);
                $insertid = $transcation->id;
            } else {
                /*if($type == 0){
                $data['balance']=$amount;
                }
                else                {
                $data['balance']=0;
                }*/
                /*if($type == 1){
                $data['loan_balance']=$amount;
                }
                else{
                $data['loan_balance']=0;
                }*/

                $data['balance'] = $amount;
                $data['opening_balance'] = 0;
                $data['closing_balance'] = 0;
                /*$data['loan_opening_balance']=0;
                $data['loan_closing_balance']=0;*/

                $data['bank_id'] = $bank_id;
                $data['account_id'] = $account_id;
                $data['entry_date'] = $entryDate;
                $data['entry_time'] = $entryTime;
                $data['type'] = $type;
                $data['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($date)));
                $transcation = \App\Models\SamraddhBankClosing::create($data);
                $insertid = $transcation->id;
            }
        }
        return $insertid;
    }

    /**  Call all Mode transction
     *  create bank cash (If the current date entry is not found, then create a current date entry with a closing balance of the old date entry. If there is an entry, then update the closing balance with the amount)
     *
     * @param  $branch_id,$date,$amount,$type
     * @return \Illuminate\Http\Response (row id return)
     */
    public static function updateBranchBalanceFromWithdrawal($branch_id, $date, $amount)
    {
        $entryTime = date("H:i:s");
        $entryDate = date("Y-m-d", strtotime(convertDate($date)));
        $currentDateRecord = \App\Models\BranchCash::where('branch_id', $branch_id)->whereDate('entry_date', $entryDate)->first();

        if ($currentDateRecord) {
            $Result = \App\Models\BranchCash::find($currentDateRecord->id);
            $data['balance'] = $currentDateRecord->balance - $amount;
            $data['updated_at'] = $date;
            $Result->update($data);
            $insertid = $currentDateRecord->id;
        } else {
            $oldDateRecord = \App\Models\BranchCash::where('branch_id', $branch_id)->whereDate('entry_date', '<', $entryDate)->orderby('entry_date', 'DESC')->first();
            if ($oldDateRecord) {
                $Result1 = \App\Models\BranchClosing::find($oldDateRecord->id);
                $data1['closing_balance'] = $oldDateRecord->balance;

                $Result1->update($data1);
                $insertid1 = $oldDateRecord->id;
                $data['opening_balance'] = $oldDateRecord->balance;
                $data['balance'] = $oldDateRecord->balance - $amount;
                $data['closing_balance'] = 0;

                /* $data['loan_opening_balance']=$oldDateRecord->loan_closing_balance;
                $data['loan_balance']=$oldDateRecord->loan_balance;
                $data['loan_closing_balance']=0;*/

                $data['branch_id'] = $branch_id;
                $data['entry_date'] = $entryDate;
                $data['entry_time'] = $entryTime;
                $data['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($date)));
                $transcation = \App\Models\BranchCash::create($data);
                $insertid = $transcation->id;
            } else {
                $data['opening_balance'] = 0;
                $data['balance'] = $amount;
                $data['closing_balance'] = 0;

                /*$data['loan_opening_balance']=0;
                $data['loan_balance']=0;
                $data['loan_closing_balance']=0;*/

                $data['branch_id'] = $branch_id;
                $data['entry_date'] = $entryDate;
                $data['entry_time'] = $entryTime;
                $data['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($date)));
                $transcation = \App\Models\BranchCash::create($data);
                $insertid = $transcation->id;
            }
        }

        $branchclosingDateRecord = \App\Models\BranchClosing::where('branch_id', $branch_id)->whereDate('entry_date', $entryDate)->first();

        if ($branchclosingDateRecord) {
            $Result = \App\Models\BranchClosing::find($branchclosingDateRecord->id);
            $data['balance'] = $branchclosingDateRecord->balance - $amount;

            $data['updated_at'] = $date;
            $Result->update($data);
            $insertid = $branchclosingDateRecord->id;
        } else {
            $oldDateBranchRecord = \App\Models\BranchClosing::where('branch_id', $branch_id)->whereDate('entry_date', '<', $entryDate)->orderby('entry_date', 'DESC')->first();
            if ($oldDateBranchRecord) {

                $Result1 = \App\Models\BranchClosing::find($oldDateBranchRecord->id);
                $data1['closing_balance'] = $oldDateBranchRecord->balance;

                $Result1->update($data1);
                $insertid1 = $oldDateBranchRecord->id;
                $data['opening_balance'] = $oldDateBranchRecord->balance;
                $data['closing_balance'] = 0;
                $data['balance'] = $oldDateBranchRecord->balance - $amount;

                /*$data['loan_opening_balance']=$oldDateBranchRecord->loan_closing_balance;
                $data['loan_balance']=$oldDateBranchRecord->loan_balance;
                $data['loan_closing_balance']=0;*/

                $data['branch_id'] = $branch_id;
                $data['entry_date'] = $entryDate;
                $data['entry_time'] = $entryTime;
                $data['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($date)));
                $transcation = \App\Models\BranchClosing::create($data);
                $insertid = $transcation->id;
            } else {
                $data['opening_balance'] = 0;
                $data['closing_balance'] = 0;
                $data['balance'] = $amount;
                /*$data['loan_opening_balance']=0;
                $data['loan_balance']=0;
                $data['loan_closing_balance']=0;*/
                $data['branch_id'] = $branch_id;
                $data['entry_date'] = $entryDate;
                $data['entry_time'] = $entryTime;
                $data['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($date)));
                $transcation = \App\Models\BranchClosing::create($data);
                $insertid = $transcation->id;
            }
        }

        return $insertid;
    }

    /**
     *  Amount  deposit or withdrow in ssb account
     *
     * @param  $account_id,$account_no,$balance,$amount, $description,$currency_code,$payment_type,$payment_mode
     * @return \Illuminate\Http\Response
     */
    public static function ssbTransaction($account_id, $account_no, $balance, $amount, $description, $currency_code, $payment_type, $payment_mode)
    {
        $globaldate = Session::get('created_at');
        $ssbBalance = $balance - $amount;
        $dataSsb['saving_account_id'] = $account_id;
        $dataSsb['account_no'] = $account_no;
        $dataSsb['opening_balance'] = $ssbBalance;
        if ($payment_type == 'DR') {
            $dataSsb['withdrawal'] = $amount;
        } else {
            $dataSsb['deposit'] = $amount;
        }
        $dataSsb['amount'] = $balance;
        $dataSsb['description'] = $description;
        $dataSsb['currency_code'] = $currency_code;
        $dataSsb['payment_type'] = $payment_type;
        $dataSsb['payment_mode'] = $payment_mode;
        $dataSsb['created_at'] = $globaldate;
        $resSsb = SavingAccountTranscation::create($dataSsb);

        $ssbBalance = $balance - $amount;
        $sResult = SavingAccount::find($account_id);
        $sData['balance'] = $ssbBalance;
        $sResult->update($sData);
        return $resSsb->id;
    }
    /**
     *  create saving account with msg .
     *
     * @param  $memberId,$branch_id,$branchCode,$amount,$payment_mode
     * @return \Illuminate\Http\Response
     */
    public static function createSavingAccountDescription($memberId, $branch_id, $branchCode, $amount, $payment_mode, $investmentId, $miCode, $investmentAccountNoSsb, $is_primary, $faCode, $description, $companyId)
    {
        $globaldate = Session::get('created_at');
        $ssbArray = array();
        $miCodePassbook = str_pad($miCode, 5, '0', STR_PAD_LEFT);
        // pass fa id 20 for passbook
        $getfaCodePassbook = getFaCode(20, $companyId);
        $faCodePassbook = $getfaCodePassbook->code;
        $passbookNumber = $faCodePassbook . $branchCode . $faCode . $miCodePassbook;

        // genarate  member saving account no
        $account_no = $investmentAccountNoSsb;
        $data['account_no'] = $account_no;
        $data['member_investments_id'] = $investmentId;
        $data['is_primary'] = $is_primary;
        $data['passbook_no'] = $passbookNumber;
        $data['mi_code'] = $miCode;
        $data['fa_code'] = $faCode;
        $data['member_id'] = $memberId;
        $data['branch_id'] = $branch_id;
        $data['branch_code'] = $branchCode;
        $data['old_branch_id'] = $branch_id;
        $data['old_branch_code'] = $branchCode;
        $data['balance'] = 0;
        $data['currency_code'] = 'INR';
        $data['created_by_id'] = Auth::user()->id;
        $data['created_by'] = (Auth::user()->role_id == '3') ? 2 : 1;
        $data['created_at'] = $globaldate;
        $data['company_id'] = $companyId;
        $ssbAccount = SavingAccount::create($data);
        $ssbArray['ssb_id'] = $ssbAccount->id;
        // create saving account transcation
        $ssb['saving_account_id'] = $ssbArray['ssb_id'];
        $ssb['account_no'] = $account_no;
        $ssb['opening_balance'] = $amount;
        $ssb['deposit'] = $amount;
        $ssb['withdrawal'] = 0;
        $ssb['description'] = $description;
        $ssb['currency_code'] = 'INR';
        $ssb['payment_type'] = 'CR';
        $ssb['payment_mode'] = $payment_mode;
        $ssb['created_at'] = $globaldate;
        $ssb['company_id'] = $companyId;
        $ssbAccountTran = SavingAccountTranscation::create($ssb);
        $ssbArray['ssb_transaction_id'] = $ssbAccountTran->id;
        // update saving account current balance
        $balance_update = $amount;

        $ssbBalance = SavingAccount::find($ssbArray['ssb_id']);
        $ssbBalance->balance = $balance_update;
        $ssbBalance->save();
        return $ssbArray;
    }

    /**
     *  create saving account transaction reference .
     *
     * @param  $memberId,$branch_id,$branchCode,$amount,$payment_mode
     * @return \Illuminate\Http\Response
     */
    public static function createTransactionReferences($satRefId, $investmentId)
    {
        $globaldate = Session::get('created_at');
        $data['saving_account_transaction_id'] = $satRefId;
        $data['investment_id'] = $investmentId;
        $data['created_at'] = $globaldate;
        $satRefId = TransactionReferences::create($data);
        return $satRefId->id;
    }

    /*--------------------------------  Commisstion  Section Start ---------------------------------------*/

    /*------- --------- function call in member or associate crecte function ---- ------------*/
    /**
     *  assign  member to  conectect all parent.get associate detail
     *
     * @param
     * @return \Illuminate\Http\Response
     */
    public static function commissionDistributeMember($associate_id, $member_id, $type, $branch_id, $member_carder)
    {
        $firtsAssociate = associateTreeChain($associate_id);
        $commission_type = 0;
        static::commissionDistributeForMembers($associate_id, $member_id, $type, $commission_type, $branch_id, $member_carder);
        if ($firtsAssociate->carder < 16) {

            static::associateParent($firtsAssociate->senior_id, $firtsAssociate->carder, $member_id, $type, $branch_id, $member_carder);
        }
    }
    /**
     *  get associate parent list or assign  member to all
     *
     * @param
     * @return \Illuminate\Http\Response
     */
    public static function associateParent($member_id, $currentCarder, $type_id, $type, $branch_id, $member_carder, $c = '')
    {
        $parent = associateTreeChainActiveGet($member_id);
        $commission_type = 1;
        if ($parent->carder >= $currentCarder && $parent->carder < 16) {
            $c .= $parent->carder . ',';

            static::commissionDistributeForMembers($parent->member_id, $type_id, $type, $commission_type, $branch_id, $member_carder);
        }
        if ($parent->senior_id > 0) {
            static::associateParent($parent->senior_id, $currentCarder, $type_id, $type, $branch_id, $member_carder, $c);
        }
    }
    /**
     *  assign  member   or associate
     *
     * @param
     * @return \Illuminate\Http\Response
     */
    public static function commissionDistributeForMembers($associate_id, $member_id, $type, $commission_type, $branch_id, $member_carder)
    {
        $associateCommission['member_id'] = $associate_id;
        $associateCommission['branch_id'] = $branch_id;
        $associateCommission['type'] = $type;
        $associateCommission['type_id'] = $member_id;
        $associateCommission['commission_type'] = $commission_type;
        $associateCommission['pay_type'] = 0;
        if ($type != 1) {
            $associateCommission['carder_id'] = $member_carder;
        }
        $stateid = getBranchState(Auth::user()->username);
        $globaldate = checkMonthAvailability(date('d'), date('m'), date('Y'), $stateid);
        $associateCommission['created_at'] = $globaldate;

        $associateCommissionInsert = \App\Models\AssociateCommission::create($associateCommission);
    }

    /*----------------- Investment Register Commission Start -----------------------------*/

    /**
     *  associte detail get or distribute commission
     *
     * @param   (associate_id,type_id,type,total_amount,month,plan_id,branch_id)
     * @return \Illuminate\Http\Response
     */
    public static function commissionDistributeInvestment($associate_id, $type_id, $type, $total_amount, $month, $plan_id, $branch_id, $tenure, $daybookId)
    {

        $firtsAssociate = associateTreeChain($associate_id);
        $commission_type = 0;
        $associate_carder = $firtsAssociate->carder;
        static::commissionDistributeForInvestment($associate_id, $type_id, $type, $total_amount, $month, $plan_id, $branch_id, $tenure, $associate_carder, 0, $daybookId, 0);
        if ($associate_carder > 1) {
            $x = $associate_carder - 1;
            for ($i = $x; $i >= 1; $i--) {
                static::commissionDistributeForInvestment($associate_id, $type_id, $type, $total_amount, $month, $plan_id, $branch_id, $tenure, $i, 0, $daybookId, 1);
            }
        }
        if ($associate_carder < 16) {
            static::associateParentInvestment($firtsAssociate->senior_id, $type_id, $type, $total_amount, $month, $plan_id, $branch_id, $tenure, $firtsAssociate->carder, $daybookId);
        }
    }
    /**
     *  get associate parent list or distribute  commission to all
     *
     * @param
     * @return \Illuminate\Http\Response
     */
    public static function associateParentInvestment($member_id, $type_id, $type, $total_amount, $month, $plan_id, $branch_id, $tenure, $carder, $daybookId, $c = '')
    {
        $parent = associateTreeChainActiveGet($member_id);
        $commission_type = 1;
        if ($parent->carder > $carder && $parent->carder < 16) {
            $c .= $parent->carder . ',';
            $aso_carder = $parent->carder;
            $x = explode(",", $c);
            $total_carder = count($x);
            $y = $x[$total_carder - 2] - $carder;
            static::commissionDistributeForInvestment($parent->member_id, $type_id, $type, $total_amount, $month, $plan_id, $branch_id, $tenure, $aso_carder, 1, $daybookId, 0);
            if ($y > 1) {
                for ($i = ($carder + 1); $i < $aso_carder; $i++) {
                    static::commissionDistributeForInvestment($parent->member_id, $type_id, $type, $total_amount, $month, $plan_id, $branch_id, $tenure, $i, 1, $daybookId, 1);
                }
            }
        }
        if ($parent->senior_id == 0) {
            $x = explode(",", $c);
            $total_carder = count($x);

            if ($total_carder == 1) {
                $y = 16 - $carder;
                $z = $carder + 1;
            } else {
                $y = 16 - $x[$total_carder - 2];
                $z = ($x[$total_carder - 2]) + 1;
            }
            if ($y > 1) {
                for ($i = $z; $i < 16; $i++) {
                    static::commissionDistributeForInvestment(1, $type_id, $type, $total_amount, $month, $plan_id, $branch_id, $tenure, $i, 1, $daybookId, 1);
                }
            }
            static::commissionDistributeForInvestment($parent->member_id, $type_id, $type, $total_amount, $month, $plan_id, $branch_id, $tenure, 16, 1, $daybookId, 0);
        }
        if ($parent->senior_id > 0) {
            static::associateParentInvestment($parent->senior_id, $type_id, $type, $total_amount, $month, $plan_id, $branch_id, $tenure, $parent->carder, $daybookId, $c);
        }
    }
    /**
     * create commission -- Investment register
     *
     * @param
     * @return \Illuminate\Http\Response
     */
    public static function commissionDistributeForInvestment($associate_id, $type_id, $type, $total_amount, $month, $plan_id, $branch_id, $tenure, $carder, $commission_type, $daybookId, $associate_exist)
    {
        $percentage = 0.5;
        if ($carder == 16) {
            $percentage = 0.5;
        }
        if ($plan_id == 7) {
            $convert = $month;
            $monthGet = ($convert % 365) / 30.5;
            if ($monthGet > 0 && $monthGet < 1) {
                $monthGet = 1;
            } else {
                $monthGet = ceil($monthGet);
            }
            $month1 = $monthGet;
        } else {
            $monthGet = ceil($month);
            $month1 = $monthGet;
        }
        if ($plan_id == 3 || $plan_id == 2) {
            $tenure = $tenure * 12;
        }
        if ($plan_id == 2) {
            $tenure = 216;
        }
        $carder1 = $carder;
        if ($type == 5) {
            if ($carder == 16) {
                $carder1 = 15;
            }
        }

        $data = commissionDetail($plan_id, $carder1, $month1, $tenure);
        if ($data) {
            $percentage = $data->associate_per;
            if ($type == 5) {
                $percentage = $data->collector_per;
            }
        }
        $percentInDecimal = $percentage / 100;
        $commission_amount = round($percentInDecimal * $total_amount, 4);
        $associateCommission['member_id'] = $associate_id;
        $associateCommission['branch_id'] = $branch_id;
        $associateCommission['type'] = $type;
        $associateCommission['type_id'] = $type_id;
        $associateCommission['day_book_id'] = $daybookId;
        $associateCommission['total_amount'] = $total_amount;
        $associateCommission['month'] = $month;
        $associateCommission['commission_amount'] = $commission_amount;
        $associateCommission['percentage'] = $percentage;
        $associateCommission['commission_type'] = $commission_type;
        $date = Daybook::where('id', $daybookId)->first();
        $associateCommission['created_at'] = $date->created_at;
        if ($type != 5) {
            $associateCommission['pay_type'] = 2;
            $associateCommission['carder_id'] = $carder;
        }
        $associateCommission['associate_exist'] = $associate_exist;
        $associateCommissionInsert = \App\Models\AssociateCommission::create($associateCommission);
    }

    public static function commissionCollectionInvestment($associate_id, $type_id, $type, $total_amount, $month, $plan_id, $branch_id, $tenure, $daybookId)
    {
        $firtsAssociate = associateTreeChain($associate_id);
        $associate_carder = $firtsAssociate->carder;

        static::commissionDistributeForInvestment($associate_id, $type_id, $type, $total_amount, $month, $plan_id, $branch_id, $tenure, $associate_carder, 0, $daybookId, 0);
    }
    /*----------------- Investment Register Commission End -----------------------------*/
    /*---------------- Investment Renew Commission Start----------------------------*/
    /**
     *  associte detail get or distribute commission
     *
     * @param   (associate_id,type_id,type,total_amount,month,plan_id,branch_id)
     * @return \Illuminate\Http\Response
     */
    public static function commissionDistributeInvestmentRenew($associate_id, $type_id, $type, $total_amount, $month, $plan_id, $branch_id, $tenure, $daybookId, $isOverdue)
    {
        $firtsAssociate = associateTreeChain($associate_id);
        $commission_type = 0;
        $associate_carder = $firtsAssociate->carder;
        static::commissionDistributeForInvestmentRenew($associate_id, $type_id, $type, $total_amount, $month, $plan_id, $branch_id, $tenure, $associate_carder, 0, $daybookId, 0, $isOverdue);
        if ($associate_carder > 1) {
            $x = $associate_carder - 1;
            for ($i = $x; $i >= 1; $i--) {
                static::commissionDistributeForInvestmentRenew($associate_id, $type_id, $type, $total_amount, $month, $plan_id, $branch_id, $tenure, $i, 0, $daybookId, 1, $isOverdue);
            }
        }
        if ($associate_carder < 16) {
            static::associateParentInvestmentRenew($firtsAssociate->senior_id, $type_id, $type, $total_amount, $month, $plan_id, $branch_id, $tenure, $firtsAssociate->carder, $daybookId, $isOverdue);
        }
    }
    /**
     *  get associate parent list or distribute  commission to all
     * @param
     * @return \Illuminate\Http\Response
     */
    public static function associateParentInvestmentRenew($member_id, $type_id, $type, $total_amount, $month, $plan_id, $branch_id, $tenure, $carder, $daybookId, $isOverdue, $c = '')
    {
        $parent = associateTreeChainActiveGet($member_id);
        $commission_type = 1;

        if ($parent->carder > $carder && $parent->carder < 16) {
            $c .= $parent->carder . ',';
            $aso_carder = $parent->carder;
            $x = explode(",", $c);
            $total_carder = count($x);
            $y = $x[$total_carder - 2] - $carder;
            static::commissionDistributeForInvestmentRenew($parent->member_id, $type_id, $type, $total_amount, $month, $plan_id, $branch_id, $tenure, $aso_carder, 1, $daybookId, 0, $isOverdue);
            if ($y > 1) {
                for ($i = ($carder + 1); $i < $aso_carder; $i++) {
                    static::commissionDistributeForInvestmentRenew($parent->member_id, $type_id, $type, $total_amount, $month, $plan_id, $branch_id, $tenure, $i, 1, $daybookId, 1, $isOverdue);
                }
            }
        }
        if ($parent->senior_id == 0) {
            $x = explode(",", $c);
            $total_carder = count($x);

            if ($total_carder == 1) {
                $y = 16 - $carder;
                $z = $carder + 1;
            } else {
                $y = 16 - $x[$total_carder - 2];
                $z = ($x[$total_carder - 2]) + 1;
            }
            if ($y > 1) {
                for ($i = $z; $i < 16; $i++) {
                    static::commissionDistributeForInvestmentRenew(1, $type_id, $type, $total_amount, $month, $plan_id, $branch_id, $tenure, $i, 1, $daybookId, 1, $isOverdue);
                }
            }
            static::commissionDistributeForInvestmentRenew($parent->member_id, $type_id, $type, $total_amount, $month, $plan_id, $branch_id, $tenure, 16, 1, $daybookId, 0, $isOverdue);
        }
        if ($parent->senior_id > 0) {
            static::associateParentInvestmentRenew($parent->senior_id, $type_id, $type, $total_amount, $month, $plan_id, $branch_id, $tenure, $parent->carder, $daybookId, $isOverdue, $c);
        }
    }
    /**
     * create commission
     * @param
     * @return \Illuminate\Http\Response
     */
    public static function commissionDistributeForInvestmentRenew($associate_id, $type_id, $type, $total_amount, $month, $plan_id, $branch_id, $tenure, $carder, $commission_type, $daybookId, $associate_exist, $isOverdue)
    {
        $percentage = 0.5;
        if ($carder == 16) {
            $percentage = 0.5;
        }
        if ($plan_id == 7) {
            $convert = $month;
            $monthGet = ($convert % 365) / 30.5;
            if ($monthGet > 0 && $monthGet < 1) {
                $monthGet = 1;
            } else {
                $monthGet = ceil($monthGet);
            }
            $month1 = $monthGet;
        } else {
            $monthGet = ceil($month);
            $month1 = $monthGet;
        }
        if ($plan_id == 2) {
            $tenure = $tenure * 12;
        }
        if ($plan_id == 2) {
            $tenure = 216;
        }

        $carder1 = $carder;
        if ($type == 5) {
            if ($carder == 16) {
                $carder1 = 15;
            }
        }

        $data = commissionDetail($plan_id, $carder1, $month1, $tenure);
        if ($data) {
            if ($type == 5) {
                $percentage = $data->collector_per;
            } else {
                $percentage = $data->associate_per;
                if ($isOverdue == 1) {
                    $percentage = 0;
                }
            }
        } else {
            if ($isOverdue == 1) {
                $percentage = 0;
            }
        }
        $percentInDecimal = $percentage / 100;
        $commission_amount = round($percentInDecimal * $total_amount, 4);
        $associateCommission['member_id'] = $associate_id;
        $associateCommission['branch_id'] = $branch_id;
        $associateCommission['type'] = $type;
        $associateCommission['type_id'] = $type_id;
        $associateCommission['day_book_id'] = $daybookId;
        $associateCommission['total_amount'] = $total_amount;
        $associateCommission['month'] = $month;
        $associateCommission['commission_amount'] = $commission_amount;
        $associateCommission['percentage'] = $percentage;
        $associateCommission['commission_type'] = $commission_type;
        $date = Daybook::where('id', $daybookId)->first();
        $associateCommission['created_at'] = $date->created_at;
        if ($type != 5) {
            $associateCommission['pay_type'] = $isOverdue;
            $associateCommission['carder_id'] = $carder;
        }
        $associateCommission['associate_exist'] = $associate_exist;
        $associateCommissionInsert = \App\Models\AssociateCommission::create($associateCommission);
    }
    /**
     * create commission  collection
     *
     */
    public static function commissionCollectionInvestmentRenew($associate_id, $type_id, $type, $total_amount, $month, $plan_id, $branch_id, $tenure, $daybookId)
    {
        $firtsAssociate = associateTreeChain($associate_id);
        $associate_carder = $firtsAssociate->carder;
        static::commissionDistributeForInvestmentRenew($associate_id, $type_id, $type, $total_amount, $month, $plan_id, $branch_id, $tenure, $associate_carder, 0, $daybookId, 0, 2);
    }
    /*--------------- Investment Renew Commission End---------------*/
    /*-------------------------  Commisstion  Section End ----------------------------*/
    /*-------------------------  Koya business calculation  Section start ----------------------------*/
    /**
     * associate kota business percentage create
     * @param
     * @return \Illuminate\Http\Response
     */
    public static function associateCreditBusiness($associate_id, $inv_id, $type, $total_amount, $month, $plan_id, $tenure, $daybookId)
    {
        $percentage = 0;
        if ($plan_id == 3 || $plan_id == 2) {
            $tenure = $tenure * 12;
        }

        if ($plan_id == 2) {
            $tenure = 216;
        }
        $data = \App\Models\KotaBusinessDetail::where('plan_id', $plan_id)->where('tenure', $tenure)->first();
        if ($data) {
            $percentage = $data->percentage;

            $percentInDecimal = $percentage / 100;
            $amount = round($percentInDecimal * $total_amount, 4);
            $kotaBusiness['member_id'] = $associate_id;
            $kotaBusiness['type'] = $type;
            $kotaBusiness['type_id'] = $inv_id;
            $kotaBusiness['total_amount'] = $total_amount;
            $kotaBusiness['month'] = $month;
            $kotaBusiness['business_amount'] = $amount;
            $kotaBusiness['percentage'] = $percentage;
            $kotaBusiness['day_book_id'] = $daybookId;
            $date = Daybook::where('id', $daybookId)->first();
            $kotaBusiness['created_at'] = $date->created_at;
            $monthGet = $month;
            if ($plan_id == 7) {
                $convert = $month;
                $monthGet = ($convert % 365) / 30.5;
                if ($monthGet > 0 && $monthGet < 1) {
                    $monthGet = 1;
                } else {
                    $monthGet = ceil($monthGet);
                }
            }
            if ($monthGet <= $data->till_month) {
                $associateKotaBusiness = \App\Models\AssociateKotaBusiness::create($kotaBusiness);
                $id = $associateKotaBusiness->id;
                static::kotaBusinessTeam($associate_id, $id, $daybookId);
            }
        }
    }
    /*-------------------------  Koya business calculation  Section End ----------------------------*/
    /*------- ---------  kota Business Team start ---- ------------*/
    /**
     *  assign  member to  conectect all parent.get associate detail
     *
     * @param
     * @return \Illuminate\Http\Response
     */
    public static function kotaBusinessTeam($associate_id, $id, $daybookId)
    {
        $firtsAssociate = associateTreeChain($associate_id);
        if ($firtsAssociate->carder < 16) {

            static::kotaBusinessTeamParent($firtsAssociate->senior_id, $id, $daybookId);
        }
    }
    /**
     *  get associate parent list or assign  member to all
     *
     * @param
     * @return \Illuminate\Http\Response
     */
    public static function kotaBusinessTeamParent($associate_id, $id, $daybookId)
    {
        $parent = associateTreeChainActiveGet($associate_id);


        $kotaBusiness['member_id'] = $parent->member_id;
        $kotaBusiness['associate_kota_business_id'] = $id;
        $kotaBusiness['day_book_id'] = $daybookId;
        $date = Daybook::where('id', $daybookId)->first();
        $kotaBusiness['created_at'] = $date->created_at;
        $associateKotaBusiness = \App\Models\AssociateKotaBusinessTeam::create($kotaBusiness);


        if ($parent->senior_id > 0) {
            static::kotaBusinessTeamParent($parent->senior_id, $id, $daybookId);
        }
    }

    /*------- ---------  kota Business Team end ---- ------------*/

    /**
     * Get received approved cheque list.
     * Route: ajax call from - /branch/registerplan
     * Method: Post
     * @param  \Illuminate\Http\Request  $request
     * @return JSON array
     */
    public function approveReceivedCheque(Request $request)
    {
        $getBranchId = getUserBranchId(Auth::user()->id)->id;
        $cheque = \App\Models\ReceivedCheque::where('status', 2)->where('branch_id', $getBranchId)->get(['id', 'cheque_no', 'amount']);
        $return_array = compact('cheque');
        return json_encode($return_array);
    }

    /**
     * Get received approved cheque detail.
     * Route: ajax call from - /branch/registerplan
     * Method: Post
     * @param  \Illuminate\Http\Request  $request
     * @return JSON array
     */
    public function approveReceivedChequeDetail(Request $request)
    {
        $cheque = \App\Models\ReceivedCheque::where('id', $request->cheque_id)->first(['id', 'cheque_no', 'bank_name', 'branch_name', 'cheque_create_date', 'amount', 'deposit_bank_id', 'deposit_account_id', 'account_holder_name', 'cheque_account_no', 'cheque_deposit_date']);
        if($cheque){
            $bank_name = \App\Models\SamraddhBank::with([
                "bankAccount" => function ($q) use ($cheque) {
                    $q->where('bank_id', '=', $cheque->deposit_bank_id);
                }
            ])->where('id', $cheque->deposit_bank_id)->first();
            $data['id'] = $cheque->id;
            $data['cheque_no'] = $cheque->cheque_no;
            $data['bank_name'] = $cheque->bank_name;
            $data['branch_name'] = $cheque->branch_name;
            $data['amount'] = $cheque->amount;
            $data['cheque_create_date'] = date("d/m/Y", strtotime($cheque->cheque_create_date));
            $data['deposit_bank_name'] = $bank_name->bank_name;
            $data['deposite_bank_acc'] = $bank_name['bankAccount']->account_no;
            $data['cheque_deposite_date'] = date("d/m/Y", strtotime($cheque->cheque_deposit_date));
            $data['user_name'] = $cheque->account_holder_name;
            $data['bank_ac'] = $cheque->cheque_account_no;
        }else{
            $data['amount'] = 0;
        }
        return json_encode($data);
    }


    /******************* Account head Implement  start **********************/

    /**  Call Only Cash Mode transction
     *  create bank cash (If the current date entry is not found, then create a current date entry with a closing balance of the old date entry. If there is an entry, then update the closing balance with the amount)
     *
     * @param  $branch_id,$date,$amount,$type
     * @return \Illuminate\Http\Response (row id return)
     */
    public static function checkCreateBranchCash($branch_id, $date, $amount, $type)
    {
        $entryDate = date("Y-m-d", strtotime(convertDate($date)));
        $entryTime = date("H:i:s");
        $currentDateRecord = \App\Models\BranchCash::where('branch_id', $branch_id)->whereDate('entry_date', $entryDate)->first();

        if ($currentDateRecord) {
            $Result = \App\Models\BranchCash::find($currentDateRecord->id);
            /*if($type == 0){
            $data['balance']=$currentDateRecord->balance+$amount;
            }elseif($type == 1){
            $data['loan_balance']=$currentDateRecord->loan_balance+$amount;
            }*/

            $data['balance'] = $currentDateRecord->balance + $amount;
            $data['updated_at'] = $date;
            $Result->update($data);
            $insertid = $currentDateRecord->id;
        } else {
            $oldDateRecord = \App\Models\BranchCash::where('branch_id', $branch_id)->whereDate('entry_date', '<', $entryDate)->orderby('entry_date', 'DESC')->first();
            if ($oldDateRecord) {
                $Result1 = \App\Models\BranchCash::find($oldDateRecord->id);
                $data1['closing_balance'] = $oldDateRecord->balance;
                //$data1['loan_closing_balance']=$oldDateRecord->loan_balance;
                $Result1->update($data1);
                $insertid1 = $oldDateRecord->id;

                /*if($type == 0){
                $data['balance']=$oldDateRecord->balance+$amount;
                }else{
                $data['balance']=$oldDateRecord->balance;
                }
                if($type == 1){
                $data['loan_balance']=$oldDateRecord->loan_balance+$amount;
                }
                else{
                $data['loan_balance']=$oldDateRecord->loan_balance;
                }*/

                $data['balance'] = $oldDateRecord->balance + $amount;
                $data['opening_balance'] = $oldDateRecord->balance;
                $data['closing_balance'] = 0;
                //$data['loan_opening_balance']=$oldDateRecord->loan_balance;
                //$data['loan_closing_balance']=0;

                $data['branch_id'] = $branch_id;
                $data['entry_date'] = $entryDate;
                $data['entry_time'] = $entryTime;
                $data['type'] = $type;
                $data['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($date)));
                $transcation = \App\Models\BranchCash::create($data);
                $insertid = $transcation->id;
            } else {
                /*if($type == 0){
                $data['balance']=$amount;
                }
                else                {
                $data['balance']=0;
                }
                if($type == 1){
                $data['loan_balance']=$amount;
                }
                else{
                $data['loan_balance']=0;
                }*/

                $data['balance'] = $amount;
                $data['opening_balance'] = 0;
                $data['closing_balance'] = 0;
                //$data['loan_opening_balance']=0;
                //$data['loan_closing_balance']=0;

                $data['branch_id'] = $branch_id;
                $data['entry_date'] = $entryDate;
                $data['entry_time'] = $entryTime;
                $data['type'] = $type;
                $data['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($date)));
                $transcation = \App\Models\BranchCash::create($data);
                $insertid = $transcation->id;
            }
        }
        return $insertid;
    }

    /**  Call All payment Mode transction
     *  create bank cash (If the current date entry is not found, then create a current date entry with a closing balance of the old date entry. If there is an entry, then update the closing balance with the amount)
     *
     * @param  $branch_id,$date,$amount,$type
     * @return \Illuminate\Http\Response (row id return)
     */
    public static function checkCreateBranchClosing($branch_id, $date, $amount, $type)
    {
        $entryDate = date("Y-m-d", strtotime(convertDate($date)));
        $entryTime = date("H:i:s");
        $currentDateRecord = \App\Models\BranchClosing::where('branch_id', $branch_id)->whereDate('entry_date', $entryDate)->first();

        if ($currentDateRecord) {
            $Result = \App\Models\BranchClosing::find($currentDateRecord->id);
            /*if($type == 0){
            $data['balance']=$currentDateRecord->balance+$amount;
            }elseif($type == 1){
            $data['loan_balance']=$currentDateRecord->loan_balance+$amount;
            }*/

            $data['balance'] = $currentDateRecord->balance + $amount;
            $data['updated_at'] = $date;
            $Result->update($data);
            $insertid = $currentDateRecord->id;
        } else {
            $oldDateRecord = \App\Models\BranchClosing::where('branch_id', $branch_id)->whereDate('entry_date', '<', $entryDate)->orderby('entry_date', 'DESC')->first();
            if ($oldDateRecord) {
                $Result1 = \App\Models\BranchClosing::find($oldDateRecord->id);
                $data1['closing_balance'] = $oldDateRecord->balance;
                //$data1['loan_closing_balance']=$oldDateRecord->loan_balance;
                $Result1->update($data1);
                $insertid1 = $oldDateRecord->id;

                /*if($type == 0){
                $data['balance']=$oldDateRecord->balance+$amount;
                }else{
                $data['balance']=$oldDateRecord->balance;
                }
                if($type == 1){
                $data['loan_balance']=$oldDateRecord->loan_balance+$amount;
                }
                else{
                $data['loan_balance']=$oldDateRecord->loan_balance;
                }*/

                $data['balance'] = $oldDateRecord->balance + $amount;
                $data['opening_balance'] = $oldDateRecord->balance;
                $data['closing_balance'] = 0;
                /*$data['loan_opening_balance']=$oldDateRecord->loan_balance;
                $data['loan_closing_balance']=0;*/
                $data['branch_id'] = $branch_id;
                $data['entry_date'] = $entryDate;
                $data['entry_time'] = $entryTime;
                $data['type'] = $type;
                $data['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($date)));
                $transcation = \App\Models\BranchClosing::create($data);
                $insertid = $transcation->id;
            } else {
                /*if($type == 0){
                $data['balance']=$amount;
                }
                else                {
                $data['balance']=0;
                }
                if($type == 1){
                $data['loan_balance']=$amount;
                }
                else{
                $data['loan_balance']=0;
                }
                */

                $data['balance'] = $amount;
                $data['opening_balance'] = 0;
                $data['closing_balance'] = 0;
                //$data['loan_opening_balance']=0;
                //$data['loan_closing_balance']=0;

                $data['branch_id'] = $branch_id;
                $data['entry_date'] = $entryDate;
                $data['entry_time'] = $entryTime;
                $data['type'] = $type;
                $data['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($date)));
                $transcation = \App\Models\BranchClosing::create($data);
                $insertid = $transcation->id;
            }
        }
        return $insertid;
    }

    public static function checkCreateBranchClosingDr($branch_id, $date, $amount, $type)
    {
        $entryDate = date("Y-m-d", strtotime(convertDate($date)));
        $entryTime = date("H:i:s");
        $currentDateRecord = \App\Models\BranchClosing::where('branch_id', $branch_id)->whereDate('entry_date', $entryDate)->first();

        if ($currentDateRecord) {
            $Result = \App\Models\BranchClosing::find($currentDateRecord->id);
            /*if($type == 0){
            $data['balance']=$currentDateRecord->balance-$amount;
            }elseif($type == 1){
            $data['loan_balance']=$currentDateRecord->loan_balance-$amount;
            }*/

            $data['balance'] = $currentDateRecord->balance - $amount;
            $data['updated_at'] = $date;
            $Result->update($data);
            $insertid = $currentDateRecord->id;
        } else {
            $oldDateRecord = \App\Models\BranchClosing::where('branch_id', $branch_id)->whereDate('entry_date', '<', $entryDate)->orderby('entry_date', 'DESC')->first();
            if ($oldDateRecord) {
                $Result1 = \App\Models\BranchClosing::find($oldDateRecord->id);
                $data1['closing_balance'] = $oldDateRecord->balance;
                //$data1['loan_closing_balance']=$oldDateRecord->loan_balance;
                $Result1->update($data1);
                $insertid1 = $oldDateRecord->id;

                /*if($type == 0){
                $data['balance']=$oldDateRecord->balance-$amount;
                }else{
                $data['balance']=$oldDateRecord->balance;
                }
                if($type == 1){
                $data['loan_balance']=$oldDateRecord->loan_balance-$amount;
                }
                else{
                $data['loan_balance']=$oldDateRecord->loan_balance;
                }*/

                $data['balance'] = $oldDateRecord->balance - $amount;
                $data['opening_balance'] = $oldDateRecord->balance;
                $data['closing_balance'] = 0;
                //$data['loan_opening_balance']=$oldDateRecord->loan_balance;
                //$data['loan_closing_balance']=0;
                $data['branch_id'] = $branch_id;
                $data['entry_date'] = $entryDate;
                $data['entry_time'] = $entryTime;
                $data['type'] = $type;
                $data['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($date)));
                $transcation = \App\Models\BranchClosing::create($data);
                $insertid = $transcation->id;
            } else {
                /*if($type == 0){
                $data['balance']=$amount;
                }
                else                {
                $data['balance']=0;
                }
                if($type == 1){
                $data['loan_balance']=$amount;
                }
                else{
                $data['loan_balance']=0;
                }*/

                $data['balance'] = $amount;
                $data['opening_balance'] = 0;
                $data['closing_balance'] = 0;
                //$data['loan_opening_balance']=0;
                //$data['loan_closing_balance']=0;

                $data['branch_id'] = $branch_id;
                $data['entry_date'] = $entryDate;
                $data['entry_time'] = $entryTime;
                $data['type'] = $type;
                $data['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($date)));
                $transcation = \App\Models\BranchClosing::create($data);
                $insertid = $transcation->id;
            }
        }
        return $insertid;
    }
    /**  Call cheque,online transaction , bank to bank
     *  create bank cash (If the current date entry is not found, then create a current date entry with a closing balance of the old date entry. If there is an entry, then update the closing balance with the amount)
     *
     * @param  $branch_id,$date,$amount,$type
     * @return \Illuminate\Http\Response (row id return)
     */
    public static function checkCreateBankClosing($bank_id, $account_id, $date, $amount, $type)
    {
        $entryDate = date("Y-m-d", strtotime(convertDate($date)));
        $entryTime = date("H:i:s");
        $currentDateRecord = \App\Models\SamraddhBankClosing::where('bank_id', $bank_id)->where('account_id', $account_id)->whereDate('entry_date', $entryDate)->first();

        if ($currentDateRecord) {
            $Result = \App\Models\SamraddhBankClosing::find($currentDateRecord->id);
            /*if($type == 0){
            $data['balance']=$currentDateRecord->balance+$amount;
            }
            elseif($type == 1){
            $data['loan_closing_balance']=$oldDateRecord->loan_balance+$amount;
            }  */

            $data['balance'] = $currentDateRecord->balance + $amount;

            $data['updated_at'] = $date;
            $Result->update($data);
            $insertid = $currentDateRecord->id;
        } else {
            $oldDateRecord = \App\Models\SamraddhBankClosing::where('bank_id', $bank_id)->where('account_id', $account_id)->whereDate('entry_date', '<', $entryDate)->orderby('entry_date', 'DESC')->first();
            if ($oldDateRecord) {
                $Result1 = \App\Models\SamraddhBankClosing::find($oldDateRecord->id);
                $data1['closing_balance'] = $oldDateRecord->balance;
                //$data1['loan_closing_balance']=$oldDateRecord->loan_balance;
                $Result1->update($data1);
                $insertid1 = $oldDateRecord->id;

                /*if($type == 0){
                $data['balance']=$oldDateRecord->balance+$amount;
                }else{
                $data['balance']=$oldDateRecord->balance;
                }
                if($type == 1){
                $data['loan_balance']=$oldDateRecord->loan_balance+$amount;
                }
                else{
                $data['loan_balance']=$oldDateRecord->loan_balance;
                }*/

                $data['balance'] = $oldDateRecord->balance + $amount;
                $data['opening_balance'] = $oldDateRecord->balance;
                $data['closing_balance'] = 0;
                /*$data['loan_opening_balance']=$oldDateRecord->loan_balance;
                $data['loan_closing_balance']=0;*/

                $data['bank_id'] = $bank_id;
                $data['account_id'] = $account_id;
                $data['entry_date'] = $entryDate;
                $data['entry_time'] = $entryTime;
                $data['type'] = $type;
                $data['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($date)));
                $transcation = \App\Models\SamraddhBankClosing::create($data);
                $insertid = $transcation->id;
            } else {
                /*if($type == 0){
                $data['balance']=$amount;
                }
                else                {
                $data['balance']=0;
                }
                if($type == 1){
                $data['loan_balance']=$amount;
                }
                else{
                $data['loan_balance']=0;
                }*/

                $data['balance'] = $amount;
                $data['opening_balance'] = 0;
                $data['closing_balance'] = 0;
                /*$data['loan_opening_balance']=0;
                $data['loan_closing_balance']=0;*/

                $data['bank_id'] = $bank_id;
                $data['account_id'] = $account_id;
                $data['entry_date'] = $entryDate;
                $data['entry_time'] = $entryTime;
                $data['type'] = $type;
                $data['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($date)));
                $transcation = \App\Models\SamraddhBankClosing::create($data);
                $insertid = $transcation->id;
            }
        }
        return $insertid;
    }




    public static function checkCreateBankClosingDR($bank_id, $account_id, $date, $amount, $type)
    {
        $entryDate = date("Y-m-d", strtotime(convertDate($date)));
        $entryTime = date("H:i:s");
        $currentDateRecord = \App\Models\SamraddhBankClosing::where('bank_id', $bank_id)->where('account_id', $account_id)->whereDate('entry_date', $entryDate)->first();

        if ($currentDateRecord) {
            $Result = \App\Models\SamraddhBankClosing::find($currentDateRecord->id);
            /*if($type == 0){
            $data['balance']=$currentDateRecord->balance-$amount;
            }
            elseif($type == 1){
            $data['loan_closing_balance']=$oldDateRecord->loan_balance-$amount;
            }*/

            $data['balance'] = $currentDateRecord->balance - $amount;
            $data['updated_at'] = $date;
            $Result->update($data);
            $insertid = $currentDateRecord->id;
        } else {
            $oldDateRecord = \App\Models\SamraddhBankClosing::where('bank_id', $bank_id)->where('account_id', $account_id)->whereDate('entry_date', '<', $entryDate)->orderby('entry_date', 'DESC')->first();
            if ($oldDateRecord) {
                $Result1 = \App\Models\SamraddhBankClosing::find($oldDateRecord->id);
                $data1['closing_balance'] = $oldDateRecord->balance;
                //$data1['loan_closing_balance']=$oldDateRecord->loan_balance;
                $Result1->update($data1);
                $insertid1 = $oldDateRecord->id;

                /*if($type == 0){
                $data['balance']=$oldDateRecord->balance-$amount;
                }else{
                $data['balance']=$oldDateRecord->balance;
                }
                if($type == 1){
                $data['loan_balance']=$oldDateRecord->loan_balance-$amount;
                }
                else{
                $data['loan_balance']=$oldDateRecord->loan_balance;
                }*/
                $data['balance'] = $oldDateRecord->balance - $amount;
                $data['opening_balance'] = $oldDateRecord->balance;
                $data['closing_balance'] = 0;
                /*$data['loan_opening_balance']=$oldDateRecord->loan_balance;
                $data['loan_closing_balance']=0;*/

                $data['bank_id'] = $bank_id;
                $data['account_id'] = $account_id;
                $data['entry_date'] = $entryDate;
                $data['entry_time'] = $entryTime;
                $data['type'] = $type;
                $data['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($date)));
                $transcation = \App\Models\SamraddhBankClosing::create($data);
                $insertid = $transcation->id;
            } else {
                /*if($type == 0){
                $data['balance']=$amount;
                }
                else                {
                $data['balance']=0;
                }
                if($type == 1){
                $data['loan_balance']=$amount;
                }
                else{
                $data['loan_balance']=0;
                }*/
                $data['balance'] = $amount;
                $data['opening_balance'] = 0;
                $data['closing_balance'] = 0;
                /*$data['loan_opening_balance']=0;
                $data['loan_closing_balance']=0;*/

                $data['bank_id'] = $bank_id;
                $data['account_id'] = $account_id;
                $data['entry_date'] = $entryDate;
                $data['entry_time'] = $entryTime;
                $data['type'] = $type;
                $data['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($date)));
                $transcation = \App\Models\SamraddhBankClosing::create($data);
                $insertid = $transcation->id;
            }
        }
        return $insertid;
    }

    /*********************** date pass through main controller  start **********************/
    /**
     *  create branch day book refresh transaction
     *
     * @param  $amount,$entry_date,$entry_time,$created_at
     * @return \Illuminate\Http\Response
     */
    public static function createBranchDayBookReferenceNew($amount, $created_at)
    {

        $data['amount'] = $amount;
        $data['entry_date'] = date("Y-m-d", strtotime(convertDate($created_at)));
        $data['entry_time'] = date("H:i:s", strtotime(convertDate($created_at)));
        $data['created_at'] = date("Y-m-d", strtotime(convertDate($created_at)));
        $data['updated_at'] = date("Y-m-d", strtotime(convertDate($created_at)));
        $transcation = \App\Models\BranchDaybookReference::create($data);
        return $transcation->id;
    }

    /**
     *  create branch day book refresh transaction
     *
     * @param  $amount,$entry_date,$entry_time,$created_at
     * @return \Illuminate\Http\Response
     */
    public static function createBranchDayBookNew(
        $daybook_ref_id,
        $branch_id,
        $type,
        $sub_type,
        $type_id,
        $associate_id,
        $member_id,
        $branch_id_to,
        $branch_id_from,
        $amount,
        $description,
        $description_dr,
        $description_cr,
        $payment_type,
        $payment_mode,
        $currency_code,
        $v_no,
        $ssb_account_id_from,
        $cheque_no,
        $transction_no,
        $entry_date,
        $entry_time,
        $created_by,
        $created_by_id,
        $created_at,
        $updated_at,
        $type_transaction_id,
        $companyId
    ) {

        $data['daybook_ref_id'] = $daybook_ref_id;
        $data['branch_id'] = $branch_id;
        $data['type'] = $type;
        $data['sub_type'] = $sub_type;
        $data['type_id'] = $type_id;
        $data['type_transaction_id'] = $type_transaction_id;
        $data['associate_id'] = $associate_id;
        $data['member_id'] = $member_id;
        $data['branch_id_to'] = $branch_id_to;
        $data['branch_id_from'] = $branch_id_from;
        $data['amount'] = $amount;
        $data['description'] = $description;
        $data['description_dr'] = $description_dr;
        $data['description_cr'] = $description_cr;
        $data['payment_type'] = $payment_type;
        $data['payment_mode'] = $payment_mode;
        $data['currency_code'] = $currency_code;
        $data['v_no'] = $v_no;
        $data['ssb_account_id_from'] = $ssb_account_id_from;
        $data['cheque_no'] = $cheque_no;
        $data['transction_no'] = $transction_no;
        $data['entry_date'] = $entry_date;
        $data['entry_time'] = $entry_time;
        $data['created_by'] = (Auth::user()->role_id == '3') ? 2 : 1;
        $data['created_by_id'] = $created_by_id;
        $data['created_at'] = $created_at;
        $data['updated_at'] = $updated_at;
        $data['company_id'] = $companyId;
        $transcation = \App\Models\BranchDaybook::create($data);
        return $transcation->id;
    }

    /**
     *  create all transaction
     *
     * @param  $transaction_type,$ssbAccountId,$memberId,$branch_id,
     *  $branchCode,$amount,$payment_mode,$deposit_by_name,$deposit_by_id
     * @return \Illuminate\Http\Response
     */
    public static function createAllTransactionNew($daybook_ref_id, $branch_id, $bank_id, $bank_ac_id, $head1, $head2, $head3, $head4, $head5, $type, $sub_type, $type_id, $associate_id, $member_id, $branch_id_to, $branch_id_from, $opening_balance, $amount, $closing_balance, $description, $payment_type, $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $type_transaction_id)
    {

        $data['daybook_ref_id'] = $daybook_ref_id;
        $data['branch_id'] = $branch_id;
        $data['bank_id'] = $bank_id;
        $data['bank_ac_id'] = $bank_ac_id;
        $data['head1'] = $head1;
        $data['head2'] = $head2;
        $data['head3'] = $head3;
        $data['head4'] = $head4;
        $data['head5'] = $head5;
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
        $data['v_no'] = $v_no;
        $data['v_date'] = $v_date;
        $data['ssb_account_id_from'] = $ssb_account_id_from;
        $data['cheque_no'] = $cheque_no;
        $data['cheque_date'] = $cheque_date;
        $data['cheque_bank_from'] = $cheque_bank_from;
        $data['cheque_bank_ac_from'] = $cheque_bank_ac_from;
        $data['cheque_bank_ifsc_from'] = $cheque_bank_ifsc_from;
        $data['cheque_bank_branch_from'] = $cheque_bank_branch_from;
        $data['cheque_bank_to'] = $cheque_bank_to;
        $data['cheque_bank_ac_to'] = $cheque_bank_ac_to;
        $data['transction_no'] = $transction_no;
        $data['transction_bank_from'] = $transction_bank_from;
        $data['transction_bank_ac_from'] = $transction_bank_ac_from;
        $data['transction_bank_ifsc_from'] = $transction_bank_ifsc_from;
        $data['transction_bank_branch_from'] = $transction_bank_branch_from;
        $data['transction_bank_to'] = $transction_bank_to;
        $data['transction_bank_ac_to'] = $transction_bank_ac_to;
        $data['transction_date'] = $transction_date;
        $data['entry_date'] = $entry_date;
        $data['entry_time'] = $entry_time;
        $data['created_by'] = (Auth::user()->role_id == '3') ? 2 : 1;
        $data['created_by_id'] = $created_by_id;
        $data['created_at'] = $created_at;
        $data['updated_at'] = $updated_at;
        $transcation = \App\Models\AllTransaction::create($data);
        return $transcation->id;
    }

    /**
     *  create branch day book
     *
     * @param  $transaction_type,$ssbAccountId,$memberId,$branch_id,
     *  $branchCode,$amount,$payment_mode,$deposit_by_name,$deposit_by_id
     * @return \Illuminate\Http\Response
     */
    public static function createMemberTransactionNew($daybook_ref_id, $type, $sub_type, $type_id, $associate_id, $member_id, $branch_id, $bank_id, $account_id, $amount, $description, $payment_type, $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $type_transaction_id)
    {

        $data['daybook_ref_id'] = $daybook_ref_id;
        $data['type'] = $type;
        $data['sub_type'] = $sub_type;
        $data['type_id'] = $type_id;
        $data['type_transaction_id'] = $type_transaction_id;
        $data['associate_id'] = $associate_id;
        $data['member_id'] = $member_id;
        $data['branch_id'] = $branch_id;
        $data['bank_id'] = $bank_id;
        $data['account_id'] = $account_id;
        $data['amount'] = $amount;
        $data['description'] = $description;
        $data['payment_type'] = $payment_type;
        $data['payment_mode'] = $payment_mode;
        $data['currency_code'] = $currency_code;
        $data['amount_to_id'] = $amount_to_id;
        $data['amount_to_name'] = $amount_to_name;
        $data['amount_from_id'] = $amount_from_id;
        $data['amount_from_name'] = $amount_from_name;
        $data['v_no'] = $v_no;
        $data['v_date'] = $v_date;
        $data['ssb_account_id_from'] = $ssb_account_id_from;
        $data['cheque_no'] = $cheque_no;
        $data['cheque_date'] = $cheque_date;
        $data['cheque_bank_from'] = $cheque_bank_from;
        $data['cheque_bank_ac_from'] = $cheque_bank_ac_from;
        $data['cheque_bank_ifsc_from'] = $cheque_bank_ifsc_from;
        $data['cheque_bank_branch_from'] = $cheque_bank_branch_from;
        $data['cheque_bank_to'] = $cheque_bank_to;
        $data['cheque_bank_ac_to'] = $cheque_bank_ac_to;
        $data['transction_no'] = $transction_no;
        $data['transction_bank_from'] = $transction_bank_from;
        $data['transction_bank_ac_from'] = $transction_bank_ac_from;
        $data['transction_bank_ifsc_from'] = $transction_bank_ifsc_from;
        $data['transction_bank_branch_from'] = $transction_bank_branch_from;
        $data['transction_bank_to'] = $transction_bank_to;
        $data['transction_bank_ac_to'] = $transction_bank_ac_to;
        $data['transction_date'] = $transction_date;
        $data['entry_date'] = $entry_date;
        $data['entry_time'] = $entry_time;
        $data['created_by'] = (Auth::user()->role_id == '3') ? 2 : 1;
        $data['created_by_id'] = $created_by_id;
        $data['created_at'] = $created_at;
        $data['updated_at'] = $updated_at;
        $transcation = \App\Models\MemberTransaction::create($data);
        return $transcation->id;
    }


    /**
     *  create branch day book
     *
     * @param  $transaction_type,$ssbAccountId,$memberId,$branch_id,
     *  $branchCode,$amount,$payment_mode,$deposit_by_name,$deposit_by_id
     * @return \Illuminate\Http\Response
     */
    public static function createSamraddhBankDaybookNew($daybook_ref_id, $bank_id, $account_id, $type, $sub_type, $type_id, $associate_id, $member_id, $branch_id, $opening_balance, $amount, $closing_balance, $description, $description_dr, $description_cr, $payment_type, $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $type_transaction_id)
    {
        $data['daybook_ref_id'] = $daybook_ref_id;
        $data['bank_id'] = $bank_id;
        $data['account_id'] = $account_id;
        $data['type'] = $type;
        $data['sub_type'] = $sub_type;
        $data['type_id'] = $type_id;
        $data['type_transaction_id'] = $type_transaction_id;
        $data['associate_id'] = $associate_id;
        $data['member_id'] = $member_id;
        $data['branch_id'] = $branch_id;
        $data['opening_balance'] = $opening_balance;
        $data['amount'] = $amount;
        $data['closing_balance'] = $closing_balance;
        $data['description'] = $description;
        $data['description_dr'] = $description_dr;
        $data['description_cr'] = $description_cr;
        $data['payment_type'] = $payment_type;
        $data['payment_mode'] = $payment_mode;
        $data['currency_code'] = $currency_code;
        $data['amount_to_id'] = $amount_to_id;
        $data['amount_to_name'] = $amount_to_name;
        $data['amount_from_id'] = $amount_from_id;
        $data['amount_from_name'] = $amount_from_name;
        $data['v_no'] = $v_no;
        $data['v_date'] = $v_date;
        $data['ssb_account_id_from'] = $ssb_account_id_from;
        $data['cheque_no'] = $cheque_no;
        $data['cheque_date'] = $cheque_date;
        $data['cheque_bank_from'] = $cheque_bank_from;
        $data['cheque_bank_ac_from'] = $cheque_bank_ac_from;
        $data['cheque_bank_ifsc_from'] = $cheque_bank_ifsc_from;
        $data['cheque_bank_branch_from'] = $cheque_bank_branch_from;
        $data['cheque_bank_to'] = $cheque_bank_to;
        $data['cheque_bank_ac_to'] = $cheque_bank_ac_to;
        $data['transction_no'] = $transction_no;
        $data['transction_bank_from'] = $transction_bank_from;
        $data['transction_bank_ac_from'] = $transction_bank_ac_from;
        $data['transction_bank_ifsc_from'] = $transction_bank_ifsc_from;
        $data['transction_bank_branch_from'] = $transction_bank_branch_from;
        $data['transction_bank_to'] = $transction_bank_to;
        $data['transction_bank_ac_to'] = $transction_bank_ac_to;
        $data['transction_date'] = $transction_date;
        $data['entry_date'] = $entry_date;
        $data['entry_time'] = $entry_time;
        $data['created_by'] = (Auth::user()->role_id == '3') ? 2 : 1;
        $data['created_by_id'] = $created_by_id;
        $data['created_at'] = $created_at;
        $data['updated_at'] = $updated_at;
        $transcation = \App\Models\SamraddhBankDaybook::create($data);
        return $transcation->id;
    }

    public static function samraddhBankDaybookNew($daybook_ref_id, $bank_id, $account_id, $type, $sub_type, $type_id, $type_transaction_id, $associate_id, $member_id, $branch_id, $opening_balance, $amount, $closing_balance, $description, $description_dr, $description_cr, $payment_type, $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $jv_unique_id, $cheque_type, $cheque_id, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $companyId)
    {
        $globaldate = Session::get('created_at');
        $t = date("H:i:s");
        $data['daybook_ref_id'] = $daybook_ref_id;
        $data['bank_id'] = $bank_id;
        $data['account_id'] = $account_id;
        $data['type'] = $type;
        $data['sub_type'] = $sub_type;
        $data['type_id'] = $type_id;
        $data['type_transaction_id'] = $type_transaction_id;
        $data['associate_id'] = $associate_id;
        $data['member_id'] = $member_id;
        $data['branch_id'] = $branch_id;
        $data['opening_balance'] = $opening_balance;
        $data['amount'] = $amount;
        $data['closing_balance'] = $closing_balance;
        $data['description'] = $description;
        $data['description_dr'] = $description_dr;
        $data['description_cr'] = $description_cr;
        $data['payment_type'] = $payment_type;
        $data['payment_mode'] = $payment_mode;
        $data['currency_code'] = $currency_code;
        $data['amount_to_id'] = $amount_to_id;
        $data['amount_to_name'] = $amount_to_name;
        $data['amount_from_id'] = $amount_from_id;
        $data['amount_from_name'] = $amount_from_name;
        $data['v_no'] = $v_no;
        $data['v_date'] = $v_date;
        $data['ssb_account_id_from'] = $ssb_account_id_from;
        $data['cheque_no'] = $cheque_no;
        $data['cheque_date'] = $cheque_date;
        $data['cheque_bank_from'] = $cheque_bank_from;
        $data['cheque_bank_ac_from'] = $cheque_bank_ac_from;
        $data['cheque_bank_ifsc_from'] = $cheque_bank_ifsc_from;
        $data['cheque_bank_branch_from'] = $cheque_bank_branch_from;
        $data['cheque_bank_to'] = $cheque_bank_to;
        $data['cheque_bank_ac_to'] = $cheque_bank_ac_to;
        $data['transction_no'] = $transction_no;
        $data['transction_bank_from'] = $transction_bank_from;
        $data['transction_bank_ac_from'] = $transction_bank_ac_from;
        $data['transction_bank_ifsc_from'] = $transction_bank_ifsc_from;
        $data['transction_bank_branch_from'] = $transction_bank_branch_from;
        $data['transction_bank_to'] = $transction_bank_to;
        $data['transction_bank_ac_to'] = $transction_bank_ac_to;
        $data['transction_bank_to_name'] = $transction_bank_to_name;
        $data['transction_bank_to_ac_no'] = $transction_bank_to_ac_no;
        $data['transction_bank_to_branch'] = $transction_bank_to_branch;
        $data['transction_bank_to_ifsc'] = $transction_bank_to_ifsc;
        $data['transction_date'] = date("Y-m-d", strtotime(convertDate($globaldate)));
        $data['entry_date'] = date("Y-m-d", strtotime(convertDate($globaldate)));
        $data['entry_time'] = date("H:i:s");
        $data['created_by'] = (Auth::user()->role_id == '3') ? 2 : 1;
        $data['created_by_id'] = $created_by_id;
        $data['created_at'] = date("Y-m-d " . $t . "", strtotime(convertDate($globaldate)));
        $data['jv_unique_id'] = $jv_unique_id;
        $data['cheque_type'] = $cheque_type;
        $data['cheque_id'] = $cheque_id;
        $data['ssb_account_tran_id_to'] = $ssb_account_tran_id_to;
        $data['ssb_account_tran_id_from'] = $ssb_account_tran_id_from;
        $data['company_id'] = $companyId;
        $transcation = \App\Models\SamraddhBankDaybook::create($data);
        return $transcation->id;
    }

    /**
     *  create branch day book
     *
     * @param  $transaction_type,$ssbAccountId,$memberId,$branch_id,
     *  $branchCode,$amount,$payment_mode,$deposit_by_name,$deposit_by_id
     * @return \Illuminate\Http\Response
     */
    public static function updateBackDateloanBankBalance($amount, $bank_id, $account_id, $ltdate, $neft)
    {
        $globaldate = $ltdate;
        $entryTime = date("H:i:s");
        $entryDate = date("Y-m-d", strtotime(convertDate($globaldate)));


        $getCurrentFromBankRecord = \App\Models\SamraddhBankClosing::where('bank_id', $bank_id)->where('account_id', $account_id)->whereDate('entry_date', $entryDate)->first();

        if ($getCurrentFromBankRecord) {
            $Result = \App\Models\SamraddhBankClosing::find($getCurrentFromBankRecord->id);

            $data['balance'] = $getCurrentFromBankRecord->balance - ($amount + $neft);

            if ($getCurrentFromBankRecord->closing_balance > 0) {
                $data['closing_balance'] = $getCurrentFromBankRecord->closing_balance - ($amount + $neft);
            }
            $data['updated_at'] = $entryDate;
            $Result->update($data);
            $insertid = $getCurrentFromBankRecord->id;

            $getNextFromBankRecord = \App\Models\SamraddhBankClosing::where('bank_id', $bank_id)->where('account_id', $account_id)->whereDate('entry_date', '>', $entryDate)->orderby('entry_date', 'ASC')->get();

            if ($getNextFromBankRecord) {
                foreach ($getNextFromBankRecord as $key => $value) {
                    $sResult = \App\Models\SamraddhBankClosing::find($value->id);
                    $sData['opening_balance'] = $value->closing_balance;
                    $sdata['balance'] = $value->balance - ($amount + $neft);
                    if ($value->closing_balance > 0) {
                        $sdata['closing_balance'] = $value->closing_balance - ($amount + $neft);
                    }

                    $sdata['updated_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($globaldate)));
                    $sResult->update($sdata);
                }
            }
        } else {

            $oldCurrentFromDateRecord = \App\Models\SamraddhBankClosing::where('bank_id', $bank_id)->where('account_id', $account_id)->whereDate('entry_date', '<', $entryDate)->orderby('entry_date', 'DESC')->first();

            if ($oldCurrentFromDateRecord) {
                $cResult = \App\Models\SamraddhBankClosing::find($oldCurrentFromDateRecord->id);
                $cdata['closing_balance'] = $oldCurrentFromDateRecord->balance;
                $cdata['updated_at'] = $entryDate;
                $cResult->update($cdata);

                $data1RecordExists = \App\Models\SamraddhBankClosing::where('bank_id', $bank_id)->where('account_id', $account_id)->whereDate('entry_date', '>', $entryDate)->orderby('entry_date', 'ASC')->get();

                $data1['bank_id'] = $oldCurrentFromDateRecord->bank_id;
                $data1['account_id'] = $oldCurrentFromDateRecord->account_id;
                $data1['opening_balance'] = $oldCurrentFromDateRecord->balance;
                $data1['balance'] = $oldCurrentFromDateRecord->balance - ($amount + $neft);

                if ($data1RecordExists) {
                    $data1['closing_balance'] = $oldCurrentFromDateRecord->balance - ($amount + $neft);
                    foreach ($data1RecordExists as $key => $value) {
                        $sResult = \App\Models\SamraddhBankClosing::find($value->id);
                        $sData['opening_balance'] = $value->closing_balance;
                        $sdata['balance'] = $value->balance - ($amount + $neft);
                        if ($value->closing_balance > 0) {
                            $sdata['closing_balance'] = $value->closing_balance - ($amount + $neft);
                        }

                        $sdata['updated_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($globaldate)));
                        $sResult->update($sdata);
                    }
                } else {
                    $data1['closing_balance'] = 0;
                }

                $data1['entry_date'] = $entryDate;
                $data1['entry_time'] = $entryTime;

                $data1['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($globaldate)));
                $transcation = \App\Models\SamraddhBankClosing::create($data1);
                $insertid = $transcation->id;
            } else {
                $data2['bank_id'] = 0;
                $data1['account_id'] = $account_id;
                $data2['opening_balance'] = 0;
                $data2['balance'] = -($amount + $neft);
                $data2['closing_balance'] = 0;

                $data2['entry_date'] = $entryDate;
                $data2['entry_time'] = $entryTime;
                $data2['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($globaldate)));
                $transcation = \App\Models\SamraddhBankClosing::create($data2);
                $insertid = $transcation->id;
            }
        }

        return true;
    }

    /*********************** date pass through main controller  end **********************/



    /********************************New field add Start **************/
    /**
     *  create branch day book
     *
     * @param  $amount,$entry_date,$entry_time,$created_at
     * @return \Illuminate\Http\Response
     */
    public static function branchDaybookCreate($daybook_ref_id, $branch_id, $type, $sub_type, $type_id, $associate_id, $member_id, $branch_id_to, $branch_id_from, $opening_balance, $amount, $closing_balance, $description, $description_dr, $description_cr, $payment_type, $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $is_contra, $contra_id, $created_at, $updated_at, $type_transaction_id, $ssb_account_id_to, $cheque_bank_from_id, $cheque_bank_ac_from_id, $cheque_bank_to_name, $cheque_bank_to_branch, $cheque_bank_to_ac_no, $cheque_bank_to_ifsc, $transction_bank_from_id, $transction_bank_from_ac_id, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc)
    {

        $data['daybook_ref_id'] = $daybook_ref_id;
        $data['branch_id'] = $branch_id;
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
        $data['description_dr'] = $description_dr;
        $data['description_cr'] = $description_cr;
        $data['payment_type'] = $payment_type;
        $data['payment_mode'] = $payment_mode;
        $data['currency_code'] = $currency_code;
        $data['amount_to_id'] = $amount_to_id;
        $data['amount_to_name'] = $amount_to_name;
        $data['amount_from_id'] = $amount_from_id;
        $data['amount_from_name'] = $amount_from_name;
        $data['v_no'] = $v_no;
        $data['v_date'] = $v_date;
        $data['ssb_account_id_from'] = $ssb_account_id_from;
        $data['cheque_no'] = $cheque_no;
        $data['cheque_date'] = $cheque_date;
        $data['cheque_bank_from'] = $cheque_bank_from;
        $data['cheque_bank_ac_from'] = $cheque_bank_ac_from;
        $data['cheque_bank_ifsc_from'] = $cheque_bank_ifsc_from;
        $data['cheque_bank_branch_from'] = $cheque_bank_branch_from;
        $data['cheque_bank_to'] = $cheque_bank_to;
        $data['cheque_bank_ac_to'] = $cheque_bank_to;
        $data['transction_no'] = $transction_no;
        $data['transction_bank_from'] = $transction_bank_from;
        $data['transction_bank_ac_from'] = $transction_bank_ac_from;
        $data['transction_bank_ifsc_from'] = $transction_bank_ifsc_from;
        $data['transction_bank_branch_from'] = $transction_bank_branch_from;
        $data['transction_bank_to'] = $transction_bank_to;
        $data['transction_bank_ac_to'] = $transction_bank_ac_to;
        $data['transction_date'] = $transction_date;
        $data['entry_date'] = $entry_date;
        $data['entry_time'] = $entry_time;
        $data['created_by'] = (Auth::user()->role_id == '3') ? 2 : 1;
        $data['created_by_id'] = $created_by_id;
        $data['is_contra'] = $is_contra;
        $data['contra_id'] = $contra_id;
        $data['created_at'] = $created_at;
        $data['updated_at'] = $updated_at;
        /*----------------*/
        $data['ssb_account_id_to'] = $ssb_account_id_to;
        $data['cheque_bank_from_id'] = $cheque_bank_from_id;
        $data['cheque_bank_ac_from_id'] = $cheque_bank_ac_from_id;
        $data['cheque_bank_to_name'] = $cheque_bank_to_name;
        $data['cheque_bank_to_branch'] = $cheque_bank_to_branch;
        $data['cheque_bank_to_ac_no'] = $cheque_bank_to_ac_no;
        $data['cheque_bank_to_ifsc'] = $cheque_bank_to_ifsc;
        $data['transction_bank_from_id'] = $transction_bank_from_id;
        $data['transction_bank_from_ac_id'] = $transction_bank_from_ac_id;
        $data['transction_bank_to_name'] = $transction_bank_to_name;
        $data['transction_bank_to_ac_no'] = $transction_bank_to_ac_no;
        $data['transction_bank_to_branch'] = $transction_bank_to_branch;
        $data['transction_bank_to_ifsc'] = $transction_bank_to_ifsc;

        $transcation = \App\Models\BranchDaybook::create($data);
        return $transcation->id;
    }

    /**
     *  create all transaction
     *
     * @param  $transaction_type,$ssbAccountId,$memberId,$branch_id,
     *  $branchCode,$amount,$payment_mode,$deposit_by_name,$deposit_by_id
     * @return \Illuminate\Http\Response
     */
    public static function allTransactionCreate($daybook_ref_id, $branch_id, $bank_id, $bank_ac_id, $head1, $head2, $head3, $head4, $head5, $type, $sub_type, $type_id, $associate_id, $member_id, $branch_id_to, $branch_id_from, $opening_balance, $amount, $closing_balance, $description, $payment_type, $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $type_transaction_id, $ssb_account_id_to, $cheque_bank_from_id, $cheque_bank_ac_from_id, $cheque_bank_to_name, $cheque_bank_to_branch, $cheque_bank_to_ac_no, $cheque_bank_to_ifsc, $transction_bank_from_id, $transction_bank_from_ac_id, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc)
    {

        $data['daybook_ref_id'] = $daybook_ref_id;
        $data['branch_id'] = $branch_id;
        $data['bank_id'] = $bank_id;
        $data['bank_ac_id'] = $bank_ac_id;
        $data['head1'] = $head1;
        $data['head2'] = $head2;
        $data['head3'] = $head3;
        $data['head4'] = $head4;
        $data['head5'] = $head5;
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
        $data['v_no'] = $v_no;
        $data['v_date'] = $v_date;
        $data['ssb_account_id_from'] = $ssb_account_id_from;
        $data['cheque_no'] = $cheque_no;
        $data['cheque_date'] = $cheque_date;
        $data['cheque_bank_from'] = $cheque_bank_from;
        $data['cheque_bank_ac_from'] = $cheque_bank_ac_from;
        $data['cheque_bank_ifsc_from'] = $cheque_bank_ifsc_from;
        $data['cheque_bank_branch_from'] = $cheque_bank_branch_from;
        $data['cheque_bank_to'] = $cheque_bank_to;
        $data['cheque_bank_ac_to'] = $cheque_bank_ac_to;
        $data['transction_no'] = $transction_no;
        $data['transction_bank_from'] = $transction_bank_from;
        $data['transction_bank_ac_from'] = $transction_bank_ac_from;
        $data['transction_bank_ifsc_from'] = $transction_bank_ifsc_from;
        $data['transction_bank_branch_from'] = $transction_bank_branch_from;
        $data['transction_bank_to'] = $transction_bank_to;
        $data['transction_bank_ac_to'] = $transction_bank_ac_to;
        $data['transction_date'] = $transction_date;
        $data['entry_date'] = $entry_date;
        $data['entry_time'] = $entry_time;
        $data['created_by'] = (Auth::user()->role_id == '3') ? 2 : 1;
        $data['created_by_id'] = $created_by_id;
        $data['created_at'] = $created_at;
        $data['updated_at'] = $updated_at;
        /*----------------*/
        $data['ssb_account_id_to'] = $ssb_account_id_to;
        $data['cheque_bank_from_id'] = $cheque_bank_from_id;
        $data['cheque_bank_ac_from_id'] = $cheque_bank_ac_from_id;
        $data['cheque_bank_to_name'] = $cheque_bank_to_name;
        $data['cheque_bank_to_branch'] = $cheque_bank_to_branch;
        $data['cheque_bank_to_ac_no'] = $cheque_bank_to_ac_no;
        $data['cheque_bank_to_ifsc'] = $cheque_bank_to_ifsc;
        $data['transction_bank_from_id'] = $transction_bank_from_id;
        $data['transction_bank_from_ac_id'] = $transction_bank_from_ac_id;
        $data['transction_bank_to_name'] = $transction_bank_to_name;
        $data['transction_bank_to_ac_no'] = $transction_bank_to_ac_no;
        $data['transction_bank_to_branch'] = $transction_bank_to_branch;
        $data['transction_bank_to_ifsc'] = $transction_bank_to_ifsc;
        $transcation = \App\Models\AllTransaction::create($data);
        return $transcation->id;
    }

    /**
     *  create MemberTransaction
     *
     * @param  $transaction_type,$ssbAccountId,$memberId,$branch_id,
     *  $branchCode,$amount,$payment_mode,$deposit_by_name,$deposit_by_id
     * @return \Illuminate\Http\Response
     */
    public static function memberTransactionCreate($daybook_ref_id, $type, $sub_type, $type_id, $associate_id, $member_id, $branch_id, $bank_id, $account_id, $amount, $description, $payment_type, $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $type_transaction_id, $ssb_account_id_to, $cheque_bank_from_id, $cheque_bank_ac_from_id, $cheque_bank_to_name, $cheque_bank_to_branch, $cheque_bank_to_ac_no, $cheque_bank_to_ifsc, $transction_bank_from_id, $transction_bank_from_ac_id, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc)
    {

        $data['daybook_ref_id'] = $daybook_ref_id;
        $data['type'] = $type;
        $data['sub_type'] = $sub_type;
        $data['type_id'] = $type_id;
        $data['type_transaction_id'] = $type_transaction_id;
        $data['associate_id'] = $associate_id;
        $data['member_id'] = $member_id;
        $data['branch_id'] = $branch_id;
        $data['bank_id'] = $bank_id;
        $data['account_id'] = $account_id;
        $data['amount'] = $amount;
        $data['description'] = $description;
        $data['payment_type'] = $payment_type;
        $data['payment_mode'] = $payment_mode;
        $data['currency_code'] = $currency_code;
        $data['amount_to_id'] = $amount_to_id;
        $data['amount_to_name'] = $amount_to_name;
        $data['amount_from_id'] = $amount_from_id;
        $data['amount_from_name'] = $amount_from_name;
        $data['v_no'] = $v_no;
        $data['v_date'] = $v_date;
        $data['ssb_account_id_from'] = $ssb_account_id_from;
        $data['cheque_no'] = $cheque_no;
        $data['cheque_date'] = $cheque_date;
        $data['cheque_bank_from'] = $cheque_bank_from;
        $data['cheque_bank_ac_from'] = $cheque_bank_ac_from;
        $data['cheque_bank_ifsc_from'] = $cheque_bank_ifsc_from;
        $data['cheque_bank_branch_from'] = $cheque_bank_branch_from;
        $data['cheque_bank_to'] = $cheque_bank_to;
        $data['cheque_bank_ac_to'] = $cheque_bank_ac_to;
        $data['transction_no'] = $transction_no;
        $data['transction_bank_from'] = $transction_bank_from;
        $data['transction_bank_ac_from'] = $transction_bank_ac_from;
        $data['transction_bank_ifsc_from'] = $transction_bank_ifsc_from;
        $data['transction_bank_branch_from'] = $transction_bank_branch_from;
        $data['transction_bank_to'] = $transction_bank_to;
        $data['transction_bank_ac_to'] = $transction_bank_ac_to;
        $data['transction_date'] = $transction_date;
        $data['entry_date'] = $entry_date;
        $data['entry_time'] = $entry_time;
        $data['created_by'] = (Auth::user()->role_id == '3') ? 2 : 1;
        $data['created_by_id'] = $created_by_id;
        $data['created_at'] = $created_at;
        $data['updated_at'] = $updated_at;
        /*----------------*/
        $data['ssb_account_id_to'] = $ssb_account_id_to;
        $data['cheque_bank_from_id'] = $cheque_bank_from_id;
        $data['cheque_bank_ac_from_id'] = $cheque_bank_ac_from_id;
        $data['cheque_bank_to_name'] = $cheque_bank_to_name;
        $data['cheque_bank_to_branch'] = $cheque_bank_to_branch;
        $data['cheque_bank_to_ac_no'] = $cheque_bank_to_ac_no;
        $data['cheque_bank_to_ifsc'] = $cheque_bank_to_ifsc;
        $data['transction_bank_from_id'] = $transction_bank_from_id;
        $data['transction_bank_from_ac_id'] = $transction_bank_from_ac_id;
        $data['transction_bank_to_name'] = $transction_bank_to_name;
        $data['transction_bank_to_ac_no'] = $transction_bank_to_ac_no;
        $data['transction_bank_to_branch'] = $transction_bank_to_branch;
        $data['transction_bank_to_ifsc'] = $transction_bank_to_ifsc;
        $transcation = \App\Models\MemberTransaction::create($data);
        return $transcation->id;
    }


    /**
     *  create SamraddhBankDaybook
     *
     * @param  $transaction_type,$ssbAccountId,$memberId,$branch_id,
     *  $branchCode,$amount,$payment_mode,$deposit_by_name,$deposit_by_id
     * @return \Illuminate\Http\Response
     */
    public static function samraddhBankDaybookCreate($daybook_ref_id, $bank_id, $account_id, $type, $sub_type, $type_id, $associate_id, $member_id, $branch_id, $opening_balance, $amount, $closing_balance, $description, $description_dr, $description_cr, $payment_type, $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $type_transaction_id, $ssb_account_id_to, $cheque_bank_from_id, $cheque_bank_ac_from_id, $cheque_bank_to_name, $cheque_bank_to_branch, $cheque_bank_to_ac_no, $cheque_bank_to_ifsc, $transction_bank_from_id, $transction_bank_from_ac_id, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc)
    {
        $data['daybook_ref_id'] = $daybook_ref_id;
        $data['bank_id'] = $bank_id;
        $data['account_id'] = $account_id;
        $data['type'] = $type;
        $data['sub_type'] = $sub_type;
        $data['type_id'] = $type_id;
        $data['type_transaction_id'] = $type_transaction_id;
        $data['associate_id'] = $associate_id;
        $data['member_id'] = $member_id;
        $data['branch_id'] = $branch_id;
        $data['opening_balance'] = $opening_balance;
        $data['amount'] = $amount;
        $data['closing_balance'] = $closing_balance;
        $data['description'] = $description;
        $data['description_dr'] = $description_dr;
        $data['description_cr'] = $description_cr;
        $data['payment_type'] = $payment_type;
        $data['payment_mode'] = $payment_mode;
        $data['currency_code'] = $currency_code;
        $data['amount_to_id'] = $amount_to_id;
        $data['amount_to_name'] = $amount_to_name;
        $data['amount_from_id'] = $amount_from_id;
        $data['amount_from_name'] = $amount_from_name;
        $data['v_no'] = $v_no;
        $data['v_date'] = $v_date;
        $data['ssb_account_id_from'] = $ssb_account_id_from;
        $data['cheque_no'] = $cheque_no;
        $data['cheque_date'] = $cheque_date;
        $data['cheque_bank_from'] = $cheque_bank_from;
        $data['cheque_bank_ac_from'] = $cheque_bank_ac_from;
        $data['cheque_bank_ifsc_from'] = $cheque_bank_ifsc_from;
        $data['cheque_bank_branch_from'] = $cheque_bank_branch_from;
        $data['cheque_bank_to'] = $cheque_bank_to;
        $data['cheque_bank_ac_to'] = $cheque_bank_ac_to;
        $data['transction_no'] = $transction_no;
        $data['transction_bank_from'] = $transction_bank_from;
        $data['transction_bank_ac_from'] = $transction_bank_ac_from;
        $data['transction_bank_ifsc_from'] = $transction_bank_ifsc_from;
        $data['transction_bank_branch_from'] = $transction_bank_branch_from;
        $data['transction_bank_to'] = $transction_bank_to;
        $data['transction_bank_ac_to'] = $transction_bank_ac_to;
        $data['transction_date'] = $transction_date;
        $data['entry_date'] = $entry_date;
        $data['entry_time'] = $entry_time;
        $data['created_by'] = (Auth::user()->role_id == '3') ? 2 : 1;
        $data['created_by_id'] = $created_by_id;
        $data['created_at'] = $created_at;
        $data['updated_at'] = $updated_at;
        /*----------------*/
        $data['ssb_account_id_to'] = $ssb_account_id_to;
        $data['cheque_bank_from_id'] = $cheque_bank_from_id;
        $data['cheque_bank_ac_from_id'] = $cheque_bank_ac_from_id;
        $data['cheque_bank_to_name'] = $cheque_bank_to_name;
        $data['cheque_bank_to_branch'] = $cheque_bank_to_branch;
        $data['cheque_bank_to_ac_no'] = $cheque_bank_to_ac_no;
        $data['cheque_bank_to_ifsc'] = $cheque_bank_to_ifsc;
        $data['transction_bank_from_id'] = $transction_bank_from_id;
        $data['transction_bank_from_ac_id'] = $transction_bank_from_ac_id;
        $data['transction_bank_to_name'] = $transction_bank_to_name;
        $data['transction_bank_to_ac_no'] = $transction_bank_to_ac_no;
        $data['transction_bank_to_branch'] = $transction_bank_to_branch;
        $data['transction_bank_to_ifsc'] = $transction_bank_to_ifsc;
        $transcation = \App\Models\SamraddhBankDaybook::create($data);
        return $transcation->id;
    }

    /********************************New field add End **************/


    /******************* Account head Implement  End **********************/

    /******************* SSB Function Update  start **********************/
    /**
     *  create saving account with msg .
     *
     * @param  $memberId,$branch_id,$branchCode,$amount,$payment_mode
     * @return \Illuminate\Http\Response
     */
    public static function createSavingAccountDescriptionModify($memberId, $branch_id, $branchCode, $amount, $payment_mode, $investmentId, $miCode, $investmentAccountNoSsb, $is_primary, $faCode, $description, $associate_id, $companyId, $globaldate, $customerId, $daybookRefssbId)
    {
        Session::put(['created_at' => $globaldate]);
        $ssbArray = array();
        $miCodePassbook = str_pad($miCode, 5, '0', STR_PAD_LEFT);
        // pass fa id 20 for passbook
        $getfaCodePassbook = generateCode(['branchid' => $branch_id], NULL, NULL, $miCode, $companyId);
        $faCodePassbook = $getfaCodePassbook['passbookCode'];
        $passbookNumber = $faCodePassbook . $branchCode . $faCode . $miCodePassbook;

        // genarate  member saving account no
        $account_no = $investmentAccountNoSsb;
        $data['account_no'] = $account_no;
        $data['member_investments_id'] = $investmentId;
        $data['is_primary'] = $is_primary;
        $data['passbook_no'] = $passbookNumber;
        $data['mi_code'] = $miCode;
        $data['fa_code'] = $faCode;
        $data['member_id'] = $memberId; // member Company table ID
        $data['customer_id'] = $customerId;
        $data['associate_id'] = $associate_id;
        $data['branch_id'] = $branch_id;
        $data['branch_code'] = $branchCode;
        $data['old_branch_id'] = $branch_id;
        $data['old_branch_code'] = $branchCode;
        $data['balance'] = 0;
        $data['currency_code'] = 'INR';
        $data['created_by_id'] = Auth::user()->id;
        $data['created_by'] = (Auth::user()->role_id == '3') ? 2 : 1;
        $data['created_at'] = $globaldate;
        $data['company_id'] = $companyId;
        $ssbAccount = SavingAccount::create($data);
        $ssbArray['ssb_id'] = $ssbAccount->id;
        // create saving account transcation
        $ssbArray['ssb_transaction_id'] = NULL;
        if($amount >  0){
            $ssb['saving_account_id'] = $ssbArray['ssb_id'];
            $ssb['associate_id'] = $associate_id;
            $ssb['branch_id'] = $branch_id;
            $ssb['account_no'] = $account_no;
            $ssb['type'] = 1;
            $ssb['opening_balance'] = $amount;
            $ssb['deposit'] = $amount;
            $ssb['withdrawal'] = 0;
            $ssb['description'] = $description;
            $ssb['currency_code'] = 'INR';
            $ssb['payment_type'] = 'CR';
            $ssb['payment_mode'] = $payment_mode;
            $ssb['created_at'] = $globaldate;
            $ssb['company_id'] = $companyId;
            $ssb['daybook_ref_id'] = $daybookRefssbId;
            $ssbAccountTran = SavingAccountTranscation::create($ssb);
            $ssbArray['ssb_transaction_id'] = $ssbAccountTran->id;
        }
        // update saving account current balance
        $balance_update = $amount;

        $ssbBalance = SavingAccount::find($ssbArray['ssb_id']);
        $ssbBalance->balance = $balance_update;
        $ssbBalance->save();
        return $ssbArray;
    }


    /**
     *  Amount  deposit or withdrow in ssb account
     *
     * @param  $account_id,$account_no,$balance,$amount, $description,$currency_code,$payment_type,$payment_mode
     * @return \Illuminate\Http\Response
     */
    public static function ssbTransactionModify($account_id, $account_no, $balance, $amount, $description, $currency_code, $payment_type, $payment_mode, $branch_id, $associate_id, $type)
    {
        $globaldate = Session::get('created_at');
        if ($payment_type == 'DR') {
            $dataSsb['withdrawal'] = $amount;
            $ssbBalance = $balance - $amount;
        } else {
            $dataSsb['deposit'] = $amount;
            $ssbBalance = $balance + $amount;
        }
        $dataSsb['saving_account_id'] = $account_id;

        $dataSsb['associate_id'] = $associate_id;
        $dataSsb['branch_id'] = $branch_id;
        $dataSsb['type'] = $type;

        $dataSsb['account_no'] = $account_no;
        $dataSsb['opening_balance'] = $ssbBalance;

        $dataSsb['amount'] = $balance;
        $dataSsb['description'] = $description;
        $dataSsb['currency_code'] = $currency_code;
        $dataSsb['payment_type'] = $payment_type;
        $dataSsb['payment_mode'] = $payment_mode;
        $dataSsb['created_at'] = $globaldate;
        $resSsb = SavingAccountTranscation::create($dataSsb);

        // $ssbBalance = $balance-$amount;
        $sResult = SavingAccount::find($account_id);
        $sData['balance'] = $ssbBalance;
        $sResult->update($sData);
        return $resSsb->id;
    }
    /******************* SSB Function Update  End **********************/



    /**
     *  create day book transaction
     *
     * @param  $transaction_type,$ssbAccountId,$memberId,$branch_id,
     *  $branchCode,$amount,$payment_mode,$deposit_by_name,$deposit_by_id
     * @return \Illuminate\Http\Response
     */
    public static function createDayBookNew($transaction_id, $satRefId, $transaction_type, $account_id, $associateId, $memberId, $openingBalance, $deposite, $withdrawal, $description, $referenceno, $branch_id, $branchCode, $amountArray, $payment_mode, $deposit_by_name, $deposit_by_id, $account_no, $cheque_dd_no, $bank_name, $branch_name, $payment_date, $online_payment_id, $online_payment_by, $saving_account_id, $payment_type, $received_cheque_id, $cheque_deposit_bank_id, $cheque_deposit_bank_ac_id, $online_deposit_bank_id, $online_deposit_bank_ac_id, $companyId)
    {
        $entryTime = date("H:i:s");
        $globaldate = Session::get('created_at');
        foreach ($amountArray as $key => $option) {
            $data_log['transaction_type'] = $transaction_type;
            $data_log['transaction_id'] = $transaction_id;
            $data_log['saving_account_transaction_reference_id'] = $satRefId;
            $data_log['daybook_ref_id'] = $satRefId;
            $data_log['investment_id'] = $account_id;
            $data_log['account_no'] = $account_no;
            $data_log['associate_id'] = $associateId;
            $data_log['member_id'] = $memberId;
            $data_log['opening_balance'] = $openingBalance;
            $data_log['deposit'] = $deposite;
            $data_log['withdrawal'] = $withdrawal;
            $data_log['description'] = $description;
            $data_log['reference_no'] = $referenceno;
            $data_log['branch_id'] = $branch_id;
            $data_log['branch_code'] = $branchCode;
            $data_log['amount'] = $option;
            $data_log['currency_code'] = 'INR';
            $data_log['payment_mode'] = $payment_mode;
            $data_log['payment_type'] = $payment_type;
            $data_log['company_id'] = $companyId;
            if ($payment_mode == 1 || $payment_mode == 2) {
                $data_log['cheque_dd_no'] = $cheque_dd_no;
                $data_log['bank_name'] = $bank_name;
                $data_log['branch_name'] = $branch_name;
                $data_log['received_cheque_id'] = $received_cheque_id;
                $data_log['cheque_deposit_bank_id'] = $cheque_deposit_bank_id;
                $data_log['cheque_deposit_bank_ac_id'] = $cheque_deposit_bank_ac_id;
                if ($payment_date != null || $payment_date != 'null') {
                    $data_log['payment_date'] = date("Y-m-d H:i:s", strtotime($payment_date));
                }
            }
            if ($payment_mode == 3) {
                $data_log['online_payment_id'] = $online_payment_id;
                $data_log['online_payment_by'] = $online_payment_by;
                if ($payment_date != null || $payment_date != 'null') {
                    $data_log['payment_date'] = date("Y-m-d H:i:s", strtotime($payment_date));
                }

                $data_log['online_deposit_bank_id'] = $online_deposit_bank_id;
                $data_log['online_deposit_bank_ac_id'] = $online_deposit_bank_ac_id;
            }
            if ($payment_mode == 4) {
                $data_log['saving_account_id'] = $saving_account_id;
                if ($payment_date != null || $payment_date != 'null') {
                    $data_log['payment_date'] = date("Y-m-d H:i:s", strtotime($payment_date));
                }
            }
            $data_log['amount_deposit_by_name'] = $deposit_by_name;
            $data_log['amount_deposit_by_id'] = $deposit_by_id;
            $data_log['created_by_id'] = Auth::user()->id;
            $data_log['created_by'] = (Auth::user()->role_id == '3') ? 2 : 1;;
            //$data_log['created_at']=date("Y-m-d h:i:s", strtotime($payment_date));
            $data_log['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($globaldate)));
            $data_log['created_at_default'] = Carbon::now();
            $transcation = Daybook::create($data_log);
            $tran_id = $transcation->id;
        }
        return $tran_id;
    }


    //----------------------------------------------------------------
    // public function bankChkbalance(Request $request)
    // {
    //     $globaldate = $request->entrydate;
    //     $entry_date = date("Y-m-d", strtotime(convertDate($globaldate)));
    //     $bank_id_from_c = $request->bank_id;
    //     $bank_ac_id_from_c = $request->account_id;

    //     $bankBla = \App\Models\SamraddhBankClosing::where('bank_id', $bank_id_from_c)->where('account_id', $bank_ac_id_from_c)->whereDate('entry_date', '<=', $entry_date)->orderby('entry_date', 'desc')->first();

    //     $balance = 0.00;
    //     if ($bankBla) {
    //         $balance = number_format((float) $bankBla->balance, 2, '.', '');
    //     }
    //     $return_array = compact('balance');
    //     return json_encode($return_array);
    // }

    public function bankChkbalance(Request $request)
    {
        $globaldate = $request->entrydate;
        $entry_date = date("Y-m-d", strtotime(convertDate($globaldate)));
        $bank_id_from_c = $request->bank_id;
        $bank_ac_id_from_c = $request->account_id;

        $bankBla = \App\Models\BankBalance::where('bank_id', $bank_id_from_c)
            ->where('account_id', $bank_ac_id_from_c)
            ->whereDate('entry_date', '<=', $entry_date)
            ->orderby('entry_date', 'desc')
            ->sum('totalAmount');

        $balance = 0.00;
        if ($bankBla) {
            $balance = number_format((float) $bankBla, 2, '.', '');
        }
        $return_array = compact('balance');
        return json_encode($return_array);
    }

    public function getSubHead(Request $request)
    {
        $head_id = $request->headId;


        $getRecord = \App\Models\AccountHeads::where('parent_id', $head_id)->where('status', 0)->get();

        $msg = 0;
        $data = '';
        if ($getRecord) {
            $data = $getRecord;
            $msg = 1;
        }
        $return_array = compact('data', 'msg');
        return json_encode($return_array);
    }

    public function branchChkbalance(Request $request)
    {
        $globaldate = $request->entrydate;
        $entry_date = date("Y-m-d", strtotime(convertDate($globaldate)));
        $branch_id = $request->branch_id;

        // $bankBla=\App\Models\BranchCash:: where('branch_id',$branch_id)->whereDate('entry_date','<=',$entry_date)->orderby('entry_date','desc')->first();
        $bankBla = \App\Models\BranchCurrentBalance::where('branch_id', $branch_id)->whereDate('entry_date', '<=', $entry_date)->sum('totalAmount');

        /*
        $balance=0.00;
        if($bankBla)
        {
        if($request->daybook==0)
        {
        $balance=number_format((float)$bankBla->balance, 2, '.', '');
        }
        else
        {
        $balance=number_format((float)$bankBla->loan_balance, 2, '.', '');
        }

        }
        */
        $balance = number_format((float) $bankBla, 2, '.', '');
        $return_array = compact('balance');
        return json_encode($return_array);
    }


    public function empCheck(Request $request)
    {
        $employee_code = $request->employee_code;
        $resCount = 0;
        $emp = '';
        $designation_name = '';

        if ($employee_code) {
            $emp = \App\Models\Employee::where('employee_code', $employee_code)->first(['id', 'designation_id', 'employee_code', 'employee_name', 'mobile_no', 'status', 'company_id','branch_id']);
            if ($emp) {
                if ($emp->status == 0) {
                    $resCount = 2;
                } else {
                    $resCount = 1;
                    $designation_name = getDesignationData('designation_name', $emp->designation_id)->designation_name;
                }
            }
        }
        $return_array = compact('emp', 'resCount', 'designation_name');
        return json_encode($return_array);
    }


    /*-------------------------------------------------------------------------*/

    public static function checkCreateBranchCashDR($branch_id, $date, $amount, $type)
    {
        $entryDate = date("Y-m-d", strtotime(convertDate($date)));
        $entryTime = date("H:i:s", strtotime(convertDate($date)));
        $currentDateRecord = \App\Models\BranchCash::where('branch_id', $branch_id)->whereDate('entry_date', $entryDate)->first();

        if ($currentDateRecord) {
            $Result = \App\Models\BranchCash::find($currentDateRecord->id);
            /*if($type == 0){
            $data['balance']=$currentDateRecord->balance-$amount;
            }elseif($type == 1){
            $data['loan_balance']=$currentDateRecord->loan_balance-$amount;
            }*/

            $data['balance'] = $currentDateRecord->balance - $amount;
            $data['updated_at'] = $date;
            $Result->update($data);
            $insertid = $currentDateRecord->id;
        } else {
            $oldDateRecord = \App\Models\BranchCash::where('branch_id', $branch_id)->whereDate('entry_date', '<', $entryDate)->orderby('entry_date', 'DESC')->first();

            if ($oldDateRecord) {
                $Result1 = \App\Models\BranchCash::find($oldDateRecord->id);
                $data1['closing_balance'] = $oldDateRecord->balance;
                //$data1['loan_closing_balance']=$oldDateRecord->loan_balance;
                $Result1->update($data1);
                $insertid1 = $oldDateRecord->id;

                /*if($type == 0){
                $data['balance']=$oldDateRecord->balance-$amount;
                }else{
                $data['balance']=$oldDateRecord->balance;
                }
                if($type == 1){
                $data['loan_balance']=$oldDateRecord->loan_balance-$amount;
                }
                else{
                $data['loan_balance']=$oldDateRecord->loan_balance;
                }*/

                $data['balance'] = $oldDateRecord->balance - $amount;
                $data['opening_balance'] = $oldDateRecord->balance;
                $data['closing_balance'] = 0;
                //$data['loan_opening_balance']=$oldDateRecord->loan_balance;
                //$data['loan_closing_balance']=0;

                $data['branch_id'] = $branch_id;
                $data['entry_date'] = $entryDate;
                $data['entry_time'] = $entryTime;
                $data['type'] = $type;
                $data['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($date)));

                $transcation = \App\Models\BranchCash::create($data);
                $insertid = $transcation->id;
            } else {
                /*if($type == 0){
                $data['balance']=0-$amount;
                }
                else                {
                $data['balance']=0;
                }
                if($type == 1){
                $data['loan_balance']=0-$amount;
                }
                else{
                $data['loan_balance']=0;
                }*/

                $data['balance'] = 0 - $amount;
                $data['opening_balance'] = 0;
                $data['closing_balance'] = 0;
                //$data['loan_opening_balance']=0;
                //$data['loan_closing_balance']=0;

                $data['branch_id'] = $branch_id;
                $data['entry_date'] = $entryDate;
                $data['entry_time'] = $entryTime;
                $data['type'] = $type;
                $data['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($date)));
                $transcation = \App\Models\BranchCash::create($data);
                $insertid = $transcation->id;
            }
        }
        return true;
    }











    /**
     *  Head New table entry
     *
     * @param
     * @return \Illuminate\Http\Response
     */

    public static function headTransactionCreate($daybook_ref_id, $branch_id, $bank_id, $bank_ac_id, $head_id, $type, $sub_type, $type_id, $associate_id, $member_id, $branch_id_to, $branch_id_from, $amount, $description, $payment_type, $payment_mode, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $type_transaction_id, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $companyId)
    {

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
        $data['amount'] = $amount;
        $data['description'] = $description;
        $data['payment_type'] = $payment_type;
        $data['payment_mode'] = $payment_mode;
        $data['currency_code'] = $currency_code;
        $data['jv_unique_id'] = $jv_unique_id;
        $data['v_no'] = $v_no;
        $data['ssb_account_id_from'] = $ssb_account_id_from;
        $data['ssb_account_id_to'] = $ssb_account_id_to;
        $data['ssb_account_tran_id_to'] = $ssb_account_tran_id_to;
        $data['ssb_account_tran_id_from'] = $ssb_account_tran_id_from;
        $data['cheque_type'] = $cheque_type;
        $data['cheque_id'] = $cheque_id;
        $data['cheque_no'] = $cheque_no;
        $data['transction_no'] = $transction_no;
        $data['entry_date'] = $entry_date;
        $data['entry_time'] = $entry_time;
        $data['created_by'] = (Auth::user()->role_id == '3') ? 2 : 1;
        $data['created_by_id'] = $created_by_id;
        $data['created_at'] = $created_at;
        $data['updated_at'] = $updated_at;
        $data['company_id'] = $companyId;


        $transcation = \App\Models\AllHeadTransaction::create($data);
        // dd($transcation);
        return true;
    }




    /**
     *  create branch day book  ------------ New Field Add
     *
     * @param  $amount,$entry_date,$entry_time,$created_at
     * @return \Illuminate\Http\Response
     */
    public static function NewFieldBranchDaybookCreate($daybook_ref_id, $branch_id, $type, $sub_type, $type_id, $associate_id, $member_id, $branch_id_to, $branch_id_from, $amount, $description, $description_dr, $description_cr, $payment_type, $payment_mode, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $jv_unique_id, $cheque_type, $cheque_id, $companyId)
    {

        $data['daybook_ref_id'] = $daybook_ref_id;
        $data['branch_id'] = $branch_id;
        $data['type'] = $type;
        $data['sub_type'] = $sub_type;
        $data['type_id'] = $type_id;
        $data['associate_id'] = $associate_id;
        $data['member_id'] = $member_id;
        $data['branch_id_to'] = $branch_id_to;
        $data['branch_id_from'] = $branch_id_from;
        $data['amount'] = $amount;
        $data['description'] = $description;
        $data['description_dr'] = $description_dr;
        $data['description_cr'] = $description_cr;
        $data['payment_type'] = $payment_type;
        $data['payment_mode'] = $payment_mode;
        $data['currency_code'] = $currency_code;
        $data['v_no'] = $v_no;
        $data['ssb_account_id_from'] = $ssb_account_id_from;
        $data['cheque_no'] = $cheque_no;
        $data['transction_no'] = $transction_no;
        $data['entry_date'] = $entry_date;
        $data['entry_time'] = $entry_time;
        $data['created_by'] = (Auth::user()->role_id == '3') ? 2 : 1;
        $data['created_by_id'] = $created_by_id;
        $data['created_at'] = $created_at;
        $data['updated_at'] = $updated_at;
        /*----------------*/
        $data['ssb_account_id_to'] = $ssb_account_id_to;
        $data['ssb_account_tran_id_to'] = $ssb_account_tran_id_to;
        $data['ssb_account_tran_id_from'] = $ssb_account_tran_id_from;
        $data['jv_unique_id'] = $jv_unique_id;
        $data['cheque_type'] = $cheque_type;
        $data['cheque_id'] = $cheque_id;
        $data['company_id'] = $companyId;
        $transcation = \App\Models\BranchDaybook::create($data);
        return $transcation->id;
    }



    /**
     *  create SamraddhBankDaybook  ---- New Field Add
     *
     * @param  $transaction_type,$ssbAccountId,$memberId,$branch_id,
     *  $branchCode,$amount,$payment_mode,$deposit_by_name,$deposit_by_id
     * @return \Illuminate\Http\Response
     */
    public static function NewFieldAddSamraddhBankDaybookCreate($daybook_ref_id, $bank_id, $account_id, $type, $sub_type, $type_id, $associate_id, $member_id, $branch_id, $opening_balance, $amount, $closing_balance, $description, $description_dr, $description_cr, $payment_type, $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $type_transaction_id, $ssb_account_id_to, $cheque_bank_from_id, $cheque_bank_ac_from_id, $cheque_bank_to_name, $cheque_bank_to_branch, $cheque_bank_to_ac_no, $cheque_bank_to_ifsc, $transction_bank_from_id, $transction_bank_from_ac_id, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $jv_unique_id, $cheque_type, $cheque_id, $companyId)
    {
        $data['daybook_ref_id'] = $daybook_ref_id;
        $data['bank_id'] = $bank_id;
        $data['account_id'] = $account_id;
        $data['type'] = $type;
        $data['sub_type'] = $sub_type;
        $data['type_id'] = $type_id;
        $data['type_transaction_id'] = $type_transaction_id;
        $data['associate_id'] = $associate_id;
        $data['member_id'] = $member_id;
        $data['branch_id'] = $branch_id;
        $data['opening_balance'] = $opening_balance;
        $data['amount'] = $amount;
        $data['closing_balance'] = $closing_balance;
        $data['description'] = $description;
        $data['description_dr'] = $description_dr;
        $data['description_cr'] = $description_cr;
        $data['payment_type'] = $payment_type;
        $data['payment_mode'] = $payment_mode;
        $data['currency_code'] = $currency_code;
        $data['amount_to_id'] = $amount_to_id;
        $data['amount_to_name'] = $amount_to_name;
        $data['amount_from_id'] = $amount_from_id;
        $data['amount_from_name'] = $amount_from_name;
        $data['v_no'] = $v_no;
        $data['v_date'] = $v_date;
        $data['ssb_account_id_from'] = $ssb_account_id_from;
        $data['cheque_no'] = $cheque_no;
        $data['cheque_date'] = $cheque_date;
        $data['cheque_bank_from'] = $cheque_bank_from;
        $data['cheque_bank_ac_from'] = $cheque_bank_ac_from;
        $data['cheque_bank_ifsc_from'] = $cheque_bank_ifsc_from;
        $data['cheque_bank_branch_from'] = $cheque_bank_branch_from;
        $data['cheque_bank_to'] = $cheque_bank_to;
        $data['cheque_bank_ac_to'] = $cheque_bank_ac_to;
        $data['transction_no'] = $transction_no;
        $data['transction_bank_from'] = $transction_bank_from;
        $data['transction_bank_ac_from'] = $transction_bank_ac_from;
        $data['transction_bank_ifsc_from'] = $transction_bank_ifsc_from;
        $data['transction_bank_branch_from'] = $transction_bank_branch_from;
        $data['transction_bank_to'] = $transction_bank_to;
        $data['transction_bank_ac_to'] = $transction_bank_ac_to;
        $data['transction_date'] = $transction_date;
        $data['entry_date'] = $entry_date;
        $data['entry_time'] = $entry_time;
        $data['created_by'] = (Auth::user()->role_id == '3') ? 2 : 1;
        $data['created_by_id'] = $created_by_id;
        $data['created_at'] = $created_at;
        $data['updated_at'] = $updated_at;
        /*----------------*/
        $data['ssb_account_id_to'] = $ssb_account_id_to;
        $data['cheque_bank_from_id'] = $cheque_bank_from_id;
        $data['cheque_bank_ac_from_id'] = $cheque_bank_ac_from_id;
        $data['cheque_bank_to_name'] = $cheque_bank_to_name;
        $data['cheque_bank_to_branch'] = $cheque_bank_to_branch;
        $data['cheque_bank_to_ac_no'] = $cheque_bank_to_ac_no;
        $data['cheque_bank_to_ifsc'] = $cheque_bank_to_ifsc;
        $data['transction_bank_from_id'] = $transction_bank_from_id;
        $data['transction_bank_from_ac_id'] = $transction_bank_from_ac_id;
        $data['transction_bank_to_name'] = $transction_bank_to_name;
        $data['transction_bank_to_ac_no'] = $transction_bank_to_ac_no;
        $data['transction_bank_to_branch'] = $transction_bank_to_branch;
        $data['transction_bank_to_ifsc'] = $transction_bank_to_ifsc;

        $data['ssb_account_tran_id_to'] = $ssb_account_tran_id_to;
        $data['ssb_account_tran_id_from'] = $ssb_account_tran_id_from;
        $data['jv_unique_id'] = $jv_unique_id;
        $data['cheque_type'] = $cheque_type;
        $data['cheque_id'] = $cheque_id;
        $data['company_id'] = $companyId;


        $transcation = \App\Models\SamraddhBankDaybook::create($data);
        return $transcation->id;
    }



    /**   ----------------------------------------------------------------------------
     *  create MemberTransaction   -------- New Field Add
     *
     * @param  $transaction_type,$ssbAccountId,$memberId,$branch_id,
     *  $branchCode,$amount,$payment_mode,$deposit_by_name,$deposit_by_id
     * @return \Illuminate\Http\Response
     */
    public static function NewFieldAddMemberTransactionCreate($daybook_ref_id, $type, $sub_type, $type_id, $associate_id, $member_id, $branch_id, $bank_id, $account_id, $amount, $description, $payment_type, $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $type_transaction_id, $ssb_account_id_to, $cheque_bank_from_id, $cheque_bank_ac_from_id, $cheque_bank_to_name, $cheque_bank_to_branch, $cheque_bank_to_ac_no, $cheque_bank_to_ifsc, $transction_bank_from_id, $transction_bank_from_ac_id, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $jv_unique_id, $cheque_type, $cheque_id)
    {

        $data['daybook_ref_id'] = $daybook_ref_id;
        $data['type'] = $type;
        $data['sub_type'] = $sub_type;
        $data['type_id'] = $type_id;
        $data['type_transaction_id'] = $type_transaction_id;
        $data['associate_id'] = $associate_id;
        $data['member_id'] = $member_id;
        $data['branch_id'] = $branch_id;
        $data['bank_id'] = $bank_id;
        $data['account_id'] = $account_id;
        $data['amount'] = $amount;
        $data['description'] = $description;
        $data['payment_type'] = $payment_type;
        $data['payment_mode'] = $payment_mode;
        $data['currency_code'] = $currency_code;
        $data['amount_to_id'] = $amount_to_id;
        $data['amount_to_name'] = $amount_to_name;
        $data['amount_from_id'] = $amount_from_id;
        $data['amount_from_name'] = $amount_from_name;
        $data['v_no'] = $v_no;
        $data['v_date'] = $v_date;
        $data['ssb_account_id_from'] = $ssb_account_id_from;
        $data['cheque_no'] = $cheque_no;
        $data['cheque_date'] = $cheque_date;
        $data['cheque_bank_from'] = $cheque_bank_from;
        $data['cheque_bank_ac_from'] = $cheque_bank_ac_from;
        $data['cheque_bank_ifsc_from'] = $cheque_bank_ifsc_from;
        $data['cheque_bank_branch_from'] = $cheque_bank_branch_from;
        $data['cheque_bank_to'] = $cheque_bank_to;
        $data['cheque_bank_ac_to'] = $cheque_bank_ac_to;
        $data['transction_no'] = $transction_no;
        $data['transction_bank_from'] = $transction_bank_from;
        $data['transction_bank_ac_from'] = $transction_bank_ac_from;
        $data['transction_bank_ifsc_from'] = $transction_bank_ifsc_from;
        $data['transction_bank_branch_from'] = $transction_bank_branch_from;
        $data['transction_bank_to'] = $transction_bank_to;
        $data['transction_bank_ac_to'] = $transction_bank_ac_to;
        $data['transction_date'] = $transction_date;
        $data['entry_date'] = $entry_date;
        $data['entry_time'] = $entry_time;
        $data['created_by'] = (Auth::user()->role_id == '3') ? 2 : 1;
        $data['created_by_id'] = $created_by_id;
        $data['created_at'] = $created_at;
        $data['updated_at'] = $updated_at;
        /*----------------*/
        $data['ssb_account_id_to'] = $ssb_account_id_to;
        $data['cheque_bank_from_id'] = $cheque_bank_from_id;
        $data['cheque_bank_ac_from_id'] = $cheque_bank_ac_from_id;
        $data['cheque_bank_to_name'] = $cheque_bank_to_name;
        $data['cheque_bank_to_branch'] = $cheque_bank_to_branch;
        $data['cheque_bank_to_ac_no'] = $cheque_bank_to_ac_no;
        $data['cheque_bank_to_ifsc'] = $cheque_bank_to_ifsc;
        $data['transction_bank_from_id'] = $transction_bank_from_id;
        $data['transction_bank_from_ac_id'] = $transction_bank_from_ac_id;
        $data['transction_bank_to_name'] = $transction_bank_to_name;
        $data['transction_bank_to_ac_no'] = $transction_bank_to_ac_no;
        $data['transction_bank_to_branch'] = $transction_bank_to_branch;
        $data['transction_bank_to_ifsc'] = $transction_bank_to_ifsc;

        $data['ssb_account_tran_id_to'] = $ssb_account_tran_id_to;
        $data['ssb_account_tran_id_from'] = $ssb_account_tran_id_from;
        $data['jv_unique_id'] = $jv_unique_id;
        $data['cheque_type'] = $cheque_type;
        $data['cheque_id'] = $cheque_id;

        $transcation = \App\Models\MemberTransaction::create($data);
        return $transcation->id;
    }
    public static function gstTransactionNew(
        $dayBookId = NULL,
        $companyGstNo = NULL,
        $customerGstNo = NULL,
        $taxValue = NULL,
        $rateofTax = NULL,
        $amountIgstTax = NULL,
        $amountCgstTax = NULL,
        $amountSgstTax = NULL,
        $totalAmount = NULL,
        $headId = NULL,
        $entryDate,
        $flag,
        $type_id,
        $branch_id,
        $companyId,
        $received_id
    ) {
        $globaldate = Session::get('created_at');

        $accountHead = \App\Models\AccountHeads::select('id', 'sub_head')->where('head_id', $headId)->first();
        $shortHead = explode(' ', $accountHead->sub_head);
        $branchState = getBranchDetail($branch_id)->state_id;
        $gstNumber = \App\Models\GstSetting::where('state_id', $branchState)->where('applicable_date', '<=', $globaldate)->first('id');
        $result = '';
        $transYead = date('y', strtotime(convertDate($entryDate)));
        $checkUid = \App\Models\GstTransaction::select('uid')->where('type_flag', $flag)->orderBy('uid', 'desc')->first();
        if (isset($checkUid->uid)) {
            $uid =   str_pad($checkUid->uid + 1, 5, "0", STR_PAD_LEFT);
        } else {
            $a = 1;
            $uid =  str_pad(1, 5, "0", STR_PAD_LEFT);
        }


        foreach ($shortHead as $word) {
            $result .= $word[0];
        }
        $currFinace = \Carbon\Carbon::now()->format('y');
        $currPreFinance = (\Carbon\Carbon::now()->format('y')) - 1;
        $data['invoice_number'] =  $result . $branch_id . $currPreFinance . $currFinace . $totalAmount . $uid;
        $data['type_flag'] =  $flag;
        $data['uid'] =  $uid;
        $data['daybook_ref_id'] = $dayBookId;
        $data['company_gst_no'] =  $companyGstNo;
        $data['customer_gst_no'] =  $customerGstNo;
        $data['tax_value'] =  $taxValue;
        $data['rate_of_tax'] =  $rateofTax;
        $data['amount_of_tax_igst'] =  $amountIgstTax;
        $data['amount_of_tax_cgst'] = $amountCgstTax;
        $data['amount_of_tax_sgst'] = $amountSgstTax;
        $data['total_amount'] =  $totalAmount;
        $data['head_id'] =  $headId;
        $data['type_id'] =  $type_id;
        $data['branch_id'] =  $branch_id;
        $data['entry_date'] = $globaldate;
        $data['gst_setting_id'] = $gstNumber->id;
        $data['company_id'] = $companyId;
        $data['received_id'] = $received_id;
        $createTransaction = \App\Models\GstTransaction::create($data);
        return $createTransaction->id;
    }
}
