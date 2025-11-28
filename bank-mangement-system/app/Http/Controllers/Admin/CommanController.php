<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use Carbon\Carbon;
use Session;
use Image;
use Redirect;
use Mail;

/*
|---------------------------------------------------------------------------
| Admin Panel -- CommanController
|--------------------------------------------------------------------------
|
| This controller handles all functions which call multiple times .
*/

class CommanController extends Controller
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
    private static $commissionDistributeForMembers = '';
    private static $associateParent = '';
    private static $associateParentInvestment = '';
    private static $commissionDistributeForInvestment = '';
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
        $data['created_by'] = (isset(Auth::user()->role_id) && Auth::user()->role_id == 3) ? 2 : 1;
        $data_log['created_at'] = $globaldate;
        $transcation = \App\Models\Transcation::create($data);
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
        $data_log['created_by'] = (isset(Auth::user()->role_id) && Auth::user()->role_id == 3) ? 2 : 1;
        $data_log['created_at'] = $globaldate;
        $transcation_log = \App\Models\TranscationLog::create($data_log);
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
        $data['created_by'] = (isset(Auth::user()->role_id) && Auth::user()->role_id == 3) ? 2 : 1;
        $data['receipts_for'] = $receipts_for;
        $data['created_at'] = $globaldate;
        $recipt = \App\Models\Receipt::create($data);
        $recipt_id = $recipt->id;
        foreach ($amountArray as $key => $option) {
            $data_amount['receipt_id'] = $recipt_id;
            $data_amount['amount'] = $option;
            $data_amount['type_label'] = $typeArray[$key];
            $data_amount['currency_code'] = 'INR';
            $data_amount['created_at'] = $globaldate;
            $re = \App\Models\ReceiptAmount::create($data_amount);
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
            if ($transaction_type) {
                $data['transaction_type'] = $transaction_type;
            }
            if ($account_id) {
                $data['transaction_type_id'] = $account_id;
            }
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
                $data['amount_deposit_by_id'] = $deposit_by_id;
            }
            $data['created_by_id'] = 1;
            $data['created_by'] = (isset(Auth::user()->role_id) && Auth::user()->role_id == 3) ? 2 : 1;
            $data['created_at'] = $globaldate;
            $transcation = \App\Models\Transcation::create($data);
            $tran_id = $transcation->id;
            $data_log['saving_account_transaction_reference_id'] = $satRefId;
            $data_log['transaction_id'] = $tran_id;
            if ($transaction_type) {
                $data_log['transaction_type'] = $transaction_type;
            }
            if ($account_id) {
                $data_log['transaction_type_id'] = $account_id;
            }
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
            $data_log['created_by_id'] = 1;
            $data_log['created_by'] = (isset(Auth::user()->role_id) && Auth::user()->role_id == 3) ? 2 : 1;
            $data_log['created_at'] = $globaldate;
            $transcation_log = \App\Models\TranscationLog::create($data_log);
        }
        return $tran_id;
    }
    /**
     *  update Payment transaction
     *
     * @param  $transaction_type,$ssbAccountId,$memberId,$branch_id,
     *  $branchCode,$amount,$payment_mode,$deposit_by_name,$deposit_by_id
     * @return \Illuminate\Http\Response
     */
    public static function updateTransaction($investmentId, $branch_id, $branchCode, $amountArray, $payment_mode, $cheque_dd_no, $bank_name, $branch_name, $payment_date, $online_payment_id, $online_payment_by, $saving_account_id, $payment_type)
    {
        $globaldate = Session::get('created_at');
        $transcationId = \App\Models\Transcation::select('id')->where('transaction_type', 2)->where('transaction_type_id', $investmentId)->first();
        if ($transcationId && isset($transcationId)) {
            foreach ($amountArray as $key => $option) {
                $transcation = \App\Models\Transcation::find($transcationId['id']);
                $transcation->branch_id = $branch_id;
                $transcation->branch_code = $branchCode;
                $transcation->amount = $option;
                $transcation->payment_mode = $payment_mode;
                $transcation->payment_type = $payment_type;
                if ($payment_mode == 1 || $payment_mode == 2) {
                    $transcation->cheque_dd_no = $cheque_dd_no;
                    $transcation->bank_name = $bank_name;
                    $transcation->branch_name = $branch_name;
                }
                if ($payment_mode == 3) {
                    $transcation->online_payment_id = $online_payment_id;
                    $transcation->online_payment_by = $online_payment_by;
                }
                if ($payment_mode == 4) {
                    $transcation->saving_account_id = $saving_account_id;
                }
                $transcation->save();
                $transcationLogId = \App\Models\TranscationLog::select('id')->where('transaction_id', $transcationId['id'])->first();
                $transcation_log = \App\Models\TranscationLog::find($transcationLogId['id']);
                $transcation_log->branch_id = $branch_id;
                $transcation_log->branch_code = $branchCode;
                $transcation_log->amount = $option;
                $transcation_log->payment_mode = $payment_mode;
                $transcation_log->payment_type = $payment_type;
                if ($payment_mode == 1 || $payment_mode == 2) {
                    $transcation_log->cheque_dd_no = $cheque_dd_no;
                    $transcation_log->bank_name = $bank_name;
                    $transcation_log->branch_name = $branch_name;
                }
                if ($payment_mode == 3) {
                    $transcation_log->online_payment_id = $online_payment_id;
                    $transcation_log->online_payment_by = $online_payment_by;
                }
                if ($payment_mode == 4) {
                    $transcation_log->saving_account_id = $saving_account_id;
                }
                $transcation_log->save();
            }
            return $transcationId['id'];
        } else {
            return '';
        }
    }
    /**
     *  create saving account .
     *
     * @param  $memberId,$branch_id,$branchCode,$amount,$payment_mode
     * @return \Illuminate\Http\Response
     */
    public static function createSavingAccount($memberId, $branch_id, $branchCode, $amount, $payment_mode, $investmentId, $miCode, $investmentAccountNoSsb, $is_primary, $faCode)
    {
        $globaldate = Session::get('created_at');
        $miCodePassbook = str_pad($miCode, 5, '0', STR_PAD_LEFT);
        // pass fa id 20 for passbook
        $getfaCodePassbook = getFaCode(20);
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
        $data['created_by'] = (isset(Auth::user()->role_id) && Auth::user()->role_id == 3) ? 2 : 1;
        $data['created_at'] = $globaldate;
        $ssbAccount = \App\Models\SavingAccount::create($data);
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
        $ssbAccountTran = \App\Models\SavingAccountTranscation::create($ssb);
        $ssbArray['ssb_transaction_id'] = $ssbAccountTran->id;
        // update saving account current balance
        $balance_update = $amount;
        $ssbBalance = \App\Models\SavingAccount::find($ssbArray['ssb_id']);
        $ssbBalance->balance = $balance_update;
        $ssbBalance->save();
        return $ssbArray;
    }
    /**
     *  create day book transaction
     *
     * @param  $transaction_type,$ssbAccountId,$memberId,$branch_id,
     *  $branchCode,$amount,$payment_mode,$deposit_by_name,$deposit_by_id
     * @return \Illuminate\Http\Response
     */
    public static function createDayBook($transaction_id, $satRefId, $transaction_type, $account_id, $associateId, $memberId, $openingBalance, $deposite, $withdrawal, $description, $referenceno, $branch_id, $branchCode, $amountArray, $payment_mode, $deposit_by_name, $deposit_by_id, $account_no, $cheque_dd_no, $bank_name, $branch_name, $payment_date, $online_payment_id, $online_payment_by, $saving_account_id, $payment_type, $companyId, $isApp = null)
    {
        $entryTime = date("H:i:s");
        $globaldate = Session::get('created_at');
        foreach ($amountArray as $key => $option) {
            $loanTypeArray = array(3, 5, 6, 8, 9, 10, 11, 12, 24, 25, 30);
            $investmentTypeArray = array(3, 5, 6, 8, 9, 10, 11, 12);
            $data_log['transaction_type'] = $transaction_type;
            $data_log['transaction_id'] = $transaction_id;
            $data_log['saving_account_transaction_reference_id'] = $satRefId;
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
            $data_log['payment_date'] = date("Y-m-d H:i:s", strtotime($globaldate)) ?? '';



            if ($payment_mode == 1 || $payment_mode == 2) {
                $data_log['cheque_dd_no'] = $cheque_dd_no;
                $data_log['bank_name'] = $bank_name;
                $data_log['branch_name'] = $branch_name;
                // if ($payment_date != null || $payment_date != 'null') {
                //     $data_log['payment_date'] = date("Y-m-d H:i:s", strtotime($payment_date));
                // }
            }
            if ($payment_mode == 3) {
                $data_log['online_payment_id'] = $online_payment_id;
                $data_log['online_payment_by'] = $online_payment_by;
                // if ($payment_date != null || $payment_date != 'null') {
                //     $data_log['payment_date'] = date("Y-m-d H:i:s", strtotime($payment_date));
                // }
            }
            if ($payment_mode == 4) {
                $data_log['saving_account_id'] = $saving_account_id;
                // if ($payment_date != null || $payment_date != 'null') {
                //     $data_log['payment_date'] = date("Y-m-d H:i:s", strtotime($payment_date));
                // }
            }
            $data_log['amount_deposit_by_name'] = $deposit_by_name;
            $data_log['amount_deposit_by_id'] = $deposit_by_id;
            $data_log['created_by_id'] = Auth::user()->id ?? 1;
            $data_log['created_by'] = (isset(Auth::user()->role_id) && Auth::user()->role_id == 3) ? 2 : 1;
            $data_log['company_id'] = $companyId ?? NULL;
            //$data_log['created_at']=date("Y-m-d h:i:s", strtotime($payment_date));
            if ($transaction_type == 16) {
                $data_log['created_at'] = date("Y-m-d", strtotime(convertDate($globaldate)));
            } else {
                $data_log['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($globaldate)));
            }
            if ($isApp != null) {
                $data_log['created_by_id'] = $associateId;
                $data_log['app_login_user_id'] = $associateId;
                $data_log['amount_deposit_by_id'] = $associateId;
                $data_log['created_by'] = 3;
                $data_log['is_app'] = 1;
            }
            $transcation = \App\Models\Daybook::create($data_log);
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
    public static function createLoanDayBook($roidayBookRef, $daybookid, $loan_type, $loan_sub_type, $loan_id, $group_loan_id, $account_number, $applicant_id, $roi_amount, $principal_amount, $opening_balance, $deposit, $description, $branch_id, $branch_code, $payment_type, $currency_code, $payment_mode, $cheque_dd_no, $bank_name, $branch_name, $payment_date, $online_payment_id, $created_by, $status, $cheque_date, $bank_account_number, $online_payment_by, $amount_deposit_by_name, $associate_id, $amount_deposit_by_id, $totalDailyInterest = Null, $totalDayInterest = Null, $penalty = NUll, $companyId, $recoveryModule)
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
        $data['created_by'] = $created_by;
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
        $data['recovery_module'] = $recoveryModule;

        $loadDayBook = \App\Models\LoanDayBooks::create($data);
        $loaddaybook_id = $loadDayBook->id;
        return $loaddaybook_id;
    }
    /**
     *  create reinvest day book transaction
     *
     * @param  $transaction_type,$ssbAccountId,$memberId,$branch_id,
     *  $branchCode,$amount,$payment_mode,$deposit_by_name,$deposit_by_id
     * @return \Illuminate\Http\Response
     */
    public static function createReinvestDayBook($transaction_id, $satRefId, $transaction_type, $account_id, $associateId, $memberId, $openingBalance, $deposite, $withdrawal, $description, $referenceno, $branch_id, $branchCode, $amountArray, $payment_mode, $deposit_by_name, $deposit_by_id, $account_no, $cheque_dd_no, $bank_name, $branch_name, $payment_date, $online_payment_id, $online_payment_by, $saving_account_id, $payment_type, $elistatus)
    {
        $globaldate = Session::get('created_at');
        foreach ($amountArray as $key => $option) {
            $data_log['transaction_type'] = $transaction_type;
            $data_log['transaction_id'] = $transaction_id;
            $data_log['saving_account_transaction_reference_id'] = $satRefId;
            $data_log['investment_id'] = $account_id;
            $data_log['account_no'] = $account_no;
            $data_log['associate_id'] = $associateId;
            $data_log['member_id'] = $memberId;
            $data_log['opening_balance'] = $openingBalance;
            $data_log['deposit'] = $deposite;
            $data_log['withdrawal'] = $withdrawal;
            $data_log['is_eli'] = $elistatus;
            $data_log['description'] = $description;
            $data_log['reference_no'] = $referenceno;
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
            $data_log['amount_deposit_by_id'] = $deposit_by_id;
            $data_log['created_by_id'] = Auth::user()->id;
            $data_log['created_by'] = (isset(Auth::user()->role_id) && Auth::user()->role_id == 3) ? 2 : 1;
            $data_log['created_at'] = $globaldate;
            $transcation = \App\Models\Daybook::create($data_log);
            $tran_id = $transcation->id;
        }
        return $tran_id;
    }
    /**
     *  update day book transaction
     *
     * @param  $transaction_type,$ssbAccountId,$memberId,$branch_id,
     *  $branchCode,$amount,$payment_mode,$deposit_by_name,$deposit_by_id
     * @return \Illuminate\Http\Response
     */
    public static function updateDayBook($transcationId, $associateId, $openingBalance, $deposite, $withdrawal, $branch_id, $branchCode, $amountArray, $payment_mode, $cheque_dd_no, $bank_name, $branch_name, $online_payment_id, $online_payment_by, $saving_account_id, $payment_type)
    {
        $dayBookId = \App\Models\Daybook::select('id')->where('transaction_id', $transcationId)->first();
        $globaldate = Session::get('created_at');
        if ($dayBookId && isset($dayBookId)) {
            foreach ($amountArray as $key => $option) {
                $daybook = \App\Models\Daybook::find($dayBookId['id']);
                $daybook->associate_id = $associateId;
                $daybook->opening_balance = $openingBalance;
                $daybook->deposit = $deposite;
                $daybook->withdrawal = $withdrawal;
                $daybook->branch_id = $branch_id;
                $daybook->branch_code = $branchCode;
                $daybook->amount = $option;
                if ($payment_mode == 3) {
                    $daybook->payment_mode = 4;
                } else {
                    $daybook->payment_mode = $payment_mode;
                }
                $daybook->payment_type = $payment_type;
                if ($payment_mode == 1) {
                    $daybook->cheque_dd_no = $cheque_dd_no;
                    $daybook->bank_name = $bank_name;
                    $daybook->branch_name = $branch_name;
                }
                if ($payment_mode == 2) {
                    $daybook->online_payment_id = $online_payment_id;
                    $daybook->online_payment_by = $online_payment_by;
                }
                if ($payment_mode == 3) {
                    $daybook->saving_account_id = $saving_account_id;
                }
                $daybook->save();
            }
            return $dayBookId['id'];
        } else {
            return '';
        }
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
        if ($payment_type == 'DR') {
            $dataSsb['withdrawal'] = $amount;
            $ssbBalance = $balance - $amount;
        } else {
            $dataSsb['deposit'] = $amount;
            $ssbBalance = $balance + $amount;
        }
        $dataSsb['saving_account_id'] = $account_id;
        $dataSsb['account_no'] = $account_no;
        $dataSsb['opening_balance'] = $ssbBalance;
        $dataSsb['amount'] = $balance;
        $dataSsb['description'] = $description;
        $dataSsb['currency_code'] = $currency_code;
        $dataSsb['payment_type'] = $payment_type;
        $dataSsb['payment_mode'] = $payment_mode;
        $dataSsb['created_at'] = $globaldate;
        $resSsb = \App\Models\SavingAccountTranscation::create($dataSsb);
        // $ssbBalance = $balance-$amount;
        $sResult = \App\Models\SavingAccount::find($account_id);
        $sData['balance'] = $ssbBalance;
        $sResult->update($sData);
        return $resSsb->id;
    }
    public static function ssbTransaction_new($account_id, $account_no, $balance, $amount, $description, $currency_code, $payment_type, $payment_mode, $type, $branch_id)
    {
        $globaldate = (Session::has('created_at')) ? Session::get('created_at') : date("Y-m-d");
        /*if($payment_type=='DR'){
        $dataSsb['withdrawal'] = 0;
        $ssbBalance = $balance;
        if($payment_mode == 4){
        $dataSsb['withdrawal'] = $amount;
        $ssbBalance = $balance-$amount;
        }
        //$ssbBalance = ($payment_mode == 4) ? $balance-$amount : $balance;
        }
        else{
        $dataSsb['deposit'] = $amount;
        $ssbBalance = ($payment_mode == 4) ? $balance+$amount : $balance;
        }*/
        if ($payment_type == 'DR') {
            $dataSsb['withdrawal'] = $amount;
            $ssbBalance = $balance - $amount;
        } else {
            $dataSsb['deposit'] = $amount;
            $ssbBalance = $balance + $amount;
        }
        $dataSsb['type'] = $type;
        $dataSsb['saving_account_id'] = $account_id;
        $dataSsb['account_no'] = $account_no;
        $dataSsb['opening_balance'] = $ssbBalance;
        $dataSsb['branch_id'] = $branch_id;
        $dataSsb['amount'] = $balance;
        $dataSsb['description'] = $description;
        $dataSsb['currency_code'] = $currency_code;
        $dataSsb['payment_type'] = $payment_type;
        $dataSsb['payment_mode'] = $payment_mode;
        $dataSsb['created_at'] = $globaldate;
        $resSsb = \App\Models\SavingAccountTranscation::create($dataSsb);
        // $ssbBalance = $balance-$amount;
        if ($payment_mode == 4) {
            $sResult = \App\Models\SavingAccount::find($account_id);
            $sData['balance'] = $ssbBalance;
            $sResult->update($sData);
        }
        return $resSsb->id;
    }
    /**
     *  create saving account with msg .
     *
     * @param  $memberId,$branch_id,$branchCode,$amount,$payment_mode
     * @return \Illuminate\Http\Response
     */
    public static function createSavingAccountDescription($memberId, $branch_id, $branchCode, $amount, $payment_mode, $investmentId, $miCode, $investmentAccountNoSsb, $is_primary, $faCode, $description)
    {
        $ssbArray = array();
        $miCodePassbook = str_pad($miCode, 5, '0', STR_PAD_LEFT);
        // pass fa id 20 for passbook
        $globaldate = Session::get('created_at');
        $getfaCodePassbook = getFaCode(20);
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
        $data['created_by'] = (isset(Auth::user()->role_id) && Auth::user()->role_id == 3) ? 2 : 1;
        $data['created_at'] = $globaldate;
        $ssbAccount = \App\Models\SavingAccount::create($data);
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
        if (isset($globaldate) && $globaldate != '') {
            $ssb['created_at'] = $globaldate;
        }
        $ssbAccountTran = \App\Models\SavingAccountTranscation::create($ssb);
        $ssbArray['ssb_transaction_id'] = $ssbAccountTran->id;
        // update saving account current balance
        $balance_update = $amount;
        $ssbBalance = \App\Models\SavingAccount::find($ssbArray['ssb_id']);
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
        $satRefId = \App\Models\TransactionReferences::create($data);
        return $satRefId->id;
    }
    /**
     *  update saving account with msg .
     *
     * @param  $memberId,$branch_id,$branchCode,$amount,$payment_mode
     * @return \Illuminate\Http\Response
     */
    public static function updateSavingAccountDescription($ssb_id, $branch_id, $branchCode, $amount)
    {
        $ssbAccount = \App\Models\SavingAccount::find($ssb_id);
        $ssbAccount->branch_id = $branch_id;
        $ssbAccount->branch_code = $branchCode;
        $ssbAccount->balance = $amount;
        $ssbAccount->save();
        $ssbAccountTran = \App\Models\SavingAccountTranscation::where('saving_account_id', $ssb_id)->update(array('opening_balance' => $amount, 'deposit' => $amount));
        return $ssb_id;
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
        $globaldate = Session::get('created_at');
        $associateCommission['member_id'] = $associate_id;
        $associateCommission['branch_id'] = $branch_id;
        $associateCommission['type'] = $type;
        $associateCommission['type_id'] = $member_id;
        $associateCommission['commission_type'] = $commission_type;
        $associateCommission['pay_type'] = 0;
        $associateCommission['created_at'] = $globaldate;
        if ($type != 1) {
            $associateCommission['carder_id'] = $member_carder;
        }
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
        $globaldate = Session::get('created_at');
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
        $date = \App\Models\Daybook::where('id', $daybookId)->first();
        $associateCommission['created_at'] = $date->created_at;
        if ($type != 5) {
            $associateCommission['pay_type'] = 2;
            $associateCommission['carder_id'] = $carder;
        }
        $associateCommission['associate_exist'] = $associate_exist;
        //$associateCommission['created_at'] = $globaldate;
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
        $globaldate = Session::get('created_at');
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
        $date = \App\Models\Daybook::where('id', $daybookId)->first();
        $associateCommission['created_at'] = $date->created_at;
        // $associateCommission['created_at'] = '2022-05-31 23:59:59';
        if ($type != 5) {
            $associateCommission['pay_type'] = $isOverdue;
            $associateCommission['carder_id'] = $carder;
        }
        $associateCommission['associate_exist'] = $associate_exist;
        //$associateCommission['associate_exist'] = $globaldate;
        //  print_r($associateCommission);die;
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
            $date = \App\Models\Daybook::where('id', $daybookId)->first();
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
        $date = \App\Models\Daybook::where('id', $daybookId)->first();
        $kotaBusiness['created_at'] = $date->created_at;
        $associateKotaBusiness = \App\Models\AssociateKotaBusinessTeam::create($kotaBusiness);
        if ($parent->senior_id > 0) {
            static::kotaBusinessTeamParent($parent->senior_id, $id, $daybookId);
        }
    }
    /*------- ---------  kota Business Team end ---- ------------*/
    /**
     *  get delete commission
     * @param
     * @return \Illuminate\Http\Response
     */
    public static function commissionDelete($daybookId, $investment_id)
    {
        $deleteCommission = \App\Models\AssociateCommission::where('is_distribute', 0)->where('type_id', $investment_id)->where('day_book_id', $daybookId)->delete();
        $getKotaBusiness = \App\Models\AssociateKotaBusiness::where('type_id', $investment_id)->where('day_book_id', $daybookId)->first();
        if ($getKotaBusiness) {
            $deleteBusinessTeam = \App\Models\AssociateKotaBusinessTeam::where('associate_kota_business_id', $getKotaBusiness->id)->where('day_book_id', $daybookId)->delete();
        }
        $deleteBusiness = \App\Models\AssociateKotaBusiness::where('type_id', $investment_id)->where('day_book_id', $daybookId)->delete();
    }
    /**
     * Get received approved cheque list.
     * Route: ajax call from - /admin/registerplan
     * Method: Post
     * @param  \Illuminate\Http\Request  $request
     * @return JSON array
     */
    public function approveReceivedCheque(Request $request)
    {
        $cheque = \App\Models\ReceivedCheque::whereCompanyId($request->companyId)->whereBranchId($request->branch_id)->where('purpose_type', 0)->where('status', 2)->get(['id', 'cheque_no', 'amount']);
        $return_array = compact('cheque');
        return json_encode($return_array);
    }
    public function approveReceivedChequeNew(Request $request)
    {
        $branchId = (int) $request->branch_id;
        $companyId = (int) $request->companyId;
        $purposeType = $request->purpose_type ?? 0;
        $purposeTypeId = $request->purpose_type_id ?? 0;
        $purposeTypeAcNo = $request->purpose_type_ac_no ?? 0;
        $cheque = \App\Models\ReceivedCheque::query()
            ->when(($companyId > 0), function ($query) use ($companyId) {
                $query->whereCompanyId($companyId);
            })
            ->when($branchId > 0, function ($query) use ($branchId) {
                $query->whereBranchId((int) $branchId);
            })
            ->when(isset($purposeType) && ($purposeType != 0), function ($query) use ($purposeType, $purposeTypeId, $purposeTypeAcNo) {
                $query->wherePurposeType((int) $purposeType)
                    ->when($purposeTypeId != 0, function ($query) use ($purposeTypeId) {
                        $query->wherePurposeTypeId((int) $purposeTypeId);
                    })
                    ->when($purposeTypeAcNo != 0, function ($query) use ($purposeTypeAcNo) {
                        $query->wherePurposeTypeAcNo($purposeTypeAcNo);
                    });
            })
            ->when(isset($purposeType) && ($purposeType == 0), function ($query) use ($purposeType, $purposeTypeId, $purposeTypeAcNo) {
                $query->wherePurposeType((int) $purposeType);
            })
            ->whereStatus(2)
            ->get(['id', 'cheque_no', 'amount']);
        $return_array = compact('cheque');
        return json_encode($return_array);
    }
    /**
     * Get received approved cheque detail.
     * Route: ajax call from - /admin/registerplan
     * Method: Post
     * @param  \Illuminate\Http\Request  $request
     * @return JSON array
     */
    public function approveReceivedChequeDetail(Request $request)
    {
        $cheque = \App\Models\ReceivedCheque::where('id', $request->cheque_id)->first(['id', 'cheque_no', 'bank_name', 'branch_name', 'cheque_create_date', 'amount', 'deposit_bank_id', 'deposit_account_id', 'account_holder_name', 'cheque_account_no', 'cheque_deposit_date']);
        if($cheque){
            $bank_name = \App\Models\SamraddhBank::select('id', 'bank_name')->with([
                "bankAccount" => function ($q) use ($cheque) {
                    $q->select('id', 'account_no', 'ifsc_code', 'bank_id', 'branch_name')->where('bank_id', '=', $cheque->deposit_bank_id);
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
    /**
     * Get received cheque detail.
     * Route: ajax call from - /admin/registerplan
     * Method: Post
     * @param  \Illuminate\Http\Request  $request
     * @return JSON array
     */
    public function assignChequeDetail(Request $request)
    {
        $cheque = \App\Models\ReceivedCheque::where('cheque_no', $request->cheque_no)->first(['id', 'cheque_no', 'amount']);
        $return_array = compact('cheque');
        return json_encode($cheque);
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
        return true;
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
                if ($type == 0) {
                    $data['balance'] = $amount;
                } else {
                    $data['balance'] = 0;
                }
                if ($type == 1) {
                    $data['loan_balance'] = $amount;
                } else {
                    $data['loan_balance'] = 0;
                }
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
        return true;
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
            } */
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
    /**
     *  create branch day book refresh transaction
     *
     * @param  $amount,$entry_date,$entry_time,$created_at
     * @return \Illuminate\Http\Response
     */
    public static function createBranchDayBookReference($amount)
    {
        $t = date("H:i:s");
        $globaldate = Session::get('created_at');
        $data['amount'] = $amount;
        $data['entry_date'] = date("Y-m-d", strtotime(convertDate($globaldate)));
        $data['entry_time'] = date("H:i:s");
        $data['created_at'] = date("Y-m-d " . $t . "", strtotime(convertDate($globaldate)));
        $transcation = \App\Models\BranchDaybookReference::create($data);
        return $transcation->id;
    }
    /**
     *  create branch day book refresh transaction
     *
     * @param  $amount,$entry_date,$entry_time,$created_at
     * @return \Illuminate\Http\Response
     */
    public static function createBranchDayBook($daybook_ref_id, $branch_id, $type, $sub_type, $type_id, $type_transaction_id, $associate_id, $member_id, $branch_id_to, $branch_id_from, $opening_balance, $amount, $closing_balance, $description, $description_dr, $description_cr, $payment_type, $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $is_contra, $contra_id, $created_at)
    {
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
        $data['transction_date'] = date("Y-m-d", strtotime(convertDate($globaldate)));
        $data['entry_date'] = date("Y-m-d", strtotime(convertDate($globaldate)));
        $data['entry_time'] = $t;
        $data['created_by'] = $created_by;
        $data['created_by_id'] = $created_by_id;
        $data['is_contra'] = $is_contra;
        $data['contra_id'] = $contra_id;
        $data['created_at'] = date("Y-m-d " . $t . "", strtotime(convertDate($globaldate)));
        $transcation = \App\Models\BranchDaybook::create($data);
        return true;
    }
    public static function branchDayBookNew($daybook_ref_id, $branch_id, $type, $sub_type, $type_id, $type_transaction_id, $associate_id, $member_id, $branch_id_to, $branch_id_from, $amount, $description, $description_dr, $description_cr, $payment_type, $payment_mode, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $jv_unique_id, $cheque_type, $cheque_id, $companyId)
    {

        $globaldate = '';

        if (Session::get('created_atUpdate')) {
            $globaldate = Session::get('created_atUpdate');
        } else {
            $globaldate = Session::get('created_at');
        }
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
        $data['transction_no'] = $transction_no;
        $data['payment_mode'] = $payment_mode;
        $data['currency_code'] = $currency_code;
        $data['v_no'] = $v_no;
        $data['ssb_account_id_from'] = $ssb_account_id_from;
        $data['cheque_no'] = $cheque_no;
        $data['entry_date'] = date("Y-m-d", strtotime(convertDate($globaldate)));
        $data['entry_time'] = date("H:i:s");
        $data['created_by'] = $created_by;
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
        $globaldate = Session::get('created_at');
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
        $data['entry_date'] = date("Y-m-d", strtotime(convertDate($globaldate)));
        $data['entry_time'] = date("H:i:s");
        $data['created_by'] = $created_by;
        $data['created_by_id'] = $created_by_id;
        $data['created_at'] = date("Y-m-d " . $t . "", strtotime(convertDate($globaldate)));
        $transcation = \App\Models\AllTransaction::create($data);
        return true;
    }
    public static function createAllHeadTransaction($daybook_ref_id, $branch_id, $bank_id, $bank_ac_id, $head_id, $type, $sub_type, $type_id, $type_transaction_id, $associate_id, $member_id, $branch_id_to, $branch_id_from, $amount, $description, $payment_type, $payment_mode, $currency_code, $jv_unique_id, $v_no, $ssb_account_id_from, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $cheque_no, $transction_no, $created_by, $created_by_id, $companyId)
    {

        $globaldate = '';

        if (Session::get('created_atUpdate')) {
            $globaldate = Session::get('created_atUpdate');
        } else {
            $globaldate = Session::get('created_at');
        }
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
        $data['created_by'] = $created_by;
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
        $data['created_by'] = $created_by;
        $data['created_by_id'] = $created_by_id;
        $data['created_at'] = date("Y-m-d " . $t . "", strtotime(convertDate($globaldate)));
        $transcation = \App\Models\MemberTransaction::create($data);
        return true;
    }
    public static function memberTransactionNew($daybook_ref_id, $type, $sub_type, $type_id, $type_transaction_id, $associate_id, $member_id, $branch_id, $bank_id, $account_id, $amount, $description, $payment_type, $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $jv_unique_id, $cheque_type, $cheque_id)
    {
        if (Session::get('created_atUpdate')) {
            $globaldate = Session::get('created_atUpdate');
        } else {
            $globaldate = Session::get('created_at');
        }
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
        $data['created_by'] = $created_by;
        $data['created_by_id'] = $created_by_id;
        $data['ssb_account_tran_id_to'] = $ssb_account_tran_id_to;
        $data['created_at'] = date("Y-m-d " . $t . "", strtotime(convertDate($globaldate)));
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
    public static function createSamraddhBankDaybook($daybook_ref_id, $bank_id, $account_id, $type, $sub_type, $type_id, $type_transaction_id, $associate_id, $member_id, $branch_id, $opening_balance, $amount, $closing_balance, $description, $description_dr, $description_cr, $payment_type, $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at)
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
        $data['created_by'] = $created_by;
        $data['created_by_id'] = $created_by_id;
        $data['created_at'] = date("Y-m-d " . $t . "", strtotime(convertDate($globaldate)));
        $transcation = \App\Models\SamraddhBankDaybook::create($data);
        return $transcation->id;
    }
    public static function samraddhBankDaybookNew($daybook_ref_id, $bank_id, $account_id, $type, $sub_type, $type_id, $type_transaction_id, $associate_id, $member_id, $branch_id, $opening_balance, $amount, $closing_balance, $description, $description_dr, $description_cr, $payment_type, $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $jv_unique_id, $cheque_type, $cheque_id, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $companyId)
    {
        // dd($created_by);
        // die();
        if (Session::get('created_atUpdate')) {
            $globaldate = Session::get('created_atUpdate');
        } else {
            $globaldate = Session::get('created_at');
        }
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
        $data['transction_bank_ac_to'] = $transction_bank_ac_to ?? NULL;
        $data['transction_bank_to_name'] = $transction_bank_to_name;
        $data['transction_bank_to_ac_no'] = $transction_bank_to_ac_no;
        $data['transction_bank_to_branch'] = $transction_bank_to_branch;
        $data['transction_bank_to_ifsc'] = $transction_bank_to_ifsc;
        $data['transction_date'] = date("Y-m-d", strtotime(convertDate($globaldate)));
        $data['entry_date'] = date("Y-m-d", strtotime(convertDate($globaldate)));
        $data['entry_time'] = date("H:i:s");
        $data['created_by'] = $created_by;
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
    public static function updateBranchBankBalance($dayBookRef, $amount, $branch_id, $transfer_mode, $transfer_type, $bank_id, $from_bank_id, $to_bank_id)
    {
        $globaldate = Session::get('created_at');
        //$globaldate = '2021-04-08 12:36:04';
        $entryTime = date("H:i:s");
        $entryDate = date("Y-m-d", strtotime(convertDate($globaldate)));
        if ($transfer_type == 0) {
            $getBranchRecord = \App\Models\BranchCash::where('branch_id', $branch_id)->orderBy('entry_date', 'desc')->first();
            $bResult = \App\Models\BranchCash::find($getBranchRecord->id);
            /*if($transfer_mode == 0){
            $bData['loan_balance']=$getBranchRecord->loan_balance-$amount;
            }elseif($transfer_mode == 1){
            $bData['balance']=$getBranchRecord->balance-$amount;
            }*/
            $bData['balance'] = $getBranchRecord->balance - $amount;
            $bData['updated_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($globaldate)));
            $bResult->update($bData);
            $getBranchClosingRecord = \App\Models\BranchClosing::where('branch_id', $branch_id)->orderBy('entry_date', 'desc')->first();
            $bResult = \App\Models\BranchClosing::find($getBranchClosingRecord->id);
            /*if($transfer_mode == 0){
            $bData['loan_balance']=$getBranchClosingRecord->loan_balance-$amount;
            }elseif($transfer_mode == 1){
            $bData['balance']=$getBranchClosingRecord->balance-$amount;
            }*/
            $bData['balance'] = $getBranchClosingRecord->balance - $amount;
            $bData['updated_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($globaldate)));
            $bResult->update($bData);
            $getBankRecord = \App\Models\SamraddhBankClosing::where('bank_id', $bank_id)->whereDate('entry_date', $entryDate)->first();
            if ($getBankRecord) {
                $Result = \App\Models\SamraddhBankClosing::find($getBankRecord->id);
                /*if($transfer_mode == 0){
                $data['loan_balance']=$getBankRecord->loan_balance+$amount;
                }elseif($transfer_mode == 1){
                $data['balance']=$getBankRecord->balance+$amount;
                }*/
                $data['balance'] = $getBankRecord->balance + $amount;
                $data['updated_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($globaldate)));
                $Result->update($data);
                $insertid = $getBankRecord->id;
            } else {
                $oldDateRecord = \App\Models\SamraddhBankClosing::where('bank_id', $bank_id)->whereDate('entry_date', '<', $entryDate)->orderby('entry_date', 'DESC')->first();
                if ($oldDateRecord) {
                    $cResult = \App\Models\SamraddhBankClosing::find($oldDateRecord->id);
                    /*if($transfer_mode == 0){
                    $cdata['loan_closing_balance']=$oldDateRecord->loan_balance;
                    }elseif($transfer_mode == 1){
                    $cdata['closing_balance']=$oldDateRecord->balance;
                    }*/
                    $cdata['closing_balance'] = $oldDateRecord->balance;
                    $cdata['updated_at'] = $entryDate;
                    $cResult->update($cdata);
                    $data1['bank_id'] = $oldDateRecord->bank_id;
                    /*if($transfer_mode == 0){
                    $data1['loan_opening_balance']=$oldDateRecord->loan_balance;
                    $data1['loan_balance']=$oldDateRecord->loan_balance+$amount;;
                    $data1['loan_closing_balance']=0;
                    }else{
                    $data1['opening_balance']=$oldDateRecord->balance;
                    $data1['balance']=$oldDateRecord->balance+$amount;;
                    $data1['closing_balance']=0;
                    }*/
                    $data1['opening_balance'] = $oldDateRecord->balance;
                    $data1['balance'] = $oldDateRecord->balance + $amount;
                    ;
                    $data1['closing_balance'] = 0;
                    $data1['entry_date'] = $entryDate;
                    $data1['entry_time'] = $entryTime;
                    $data1['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($globaldate)));
                    $transcation = \App\Models\SamraddhBankClosing::create($data1);
                    $insertid = $transcation->id;
                } else {
                    $data2['bank_id'] = $bank_id;
                    /*if($transfer_mode == 0){
                    $data2['loan_opening_balance']=0;
                    $data2['loan_balance']=$amount;
                    $data2['loan_closing_balance']=0;
                    }else{
                    $data2['opening_balance']=0;
                    $data2['balance']=$amount;
                    $data2['closing_balance']=0;
                    }*/
                    $data2['opening_balance'] = 0;
                    $data2['balance'] = $amount;
                    $data2['closing_balance'] = 0;
                    $data2['entry_date'] = $entryDate;
                    $data2['entry_time'] = $entryTime;
                    $data2['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($globaldate)));
                    $transcation = \App\Models\SamraddhBankClosing::create($data2);
                    $insertid = $transcation->id;
                }
            }
        } elseif ($transfer_type == 1) {
            $getFromBankRecord = \App\Models\SamraddhBankClosing::where('bank_id', $from_bank_id)->whereDate('entry_date', $entryDate)->first();
            if ($getFromBankRecord) {
                $Result = \App\Models\SamraddhBankClosing::find($getFromBankRecord->id);
                $data['balance'] = $getFromBankRecord->balance - $amount;
                $data['updated_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($globaldate)));
                $Result->update($data);
                $insertid = $getFromBankRecord->id;
            } else {
                $oldFromDateRecord = \App\Models\SamraddhBankClosing::where('bank_id', $from_bank_id)->whereDate('entry_date', '<', $entryDate)->orderby('entry_date', 'DESC')->first();
                if ($oldFromDateRecord) {
                    $cResult = \App\Models\SamraddhBankClosing::find($oldFromDateRecord->id);
                    $cdata['closing_balance'] = $oldFromDateRecord->balance;
                    $cdata['updated_at'] = $entryDate;
                    $cResult->update($cdata);
                    $data3['bank_id'] = $oldFromDateRecord->bank_id;
                    $data3['opening_balance'] = $oldFromDateRecord->balance;
                    $data3['balance'] = $oldFromDateRecord->balance - $amount;
                    ;
                    $data3['closing_balance'] = 0;
                    $data3['entry_date'] = $entryDate;
                    $data3['entry_time'] = $entryTime;
                    $data3['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($globaldate)));
                    $transcation = \App\Models\SamraddhBankClosing::create($data3);
                    $insertid = $transcation->id;
                } else {
                    $data4['bank_id'] = $from_bank_id;
                    $data4['opening_balance'] = 0;
                    $data4['balance'] = $amount;
                    $data4['closing_balance'] = 0;
                    $data4['entry_date'] = $entryDate;
                    $data4['entry_time'] = $entryTime;
                    $data4['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($globaldate)));
                    $transcation = \App\Models\SamraddhBankClosing::create($data4);
                    $insertid = $transcation->id;
                }
            }
            $getTOBankRecord = \App\Models\SamraddhBankClosing::where('bank_id', $to_bank_id)->whereDate('entry_date', $entryDate)->first();
            if ($getTOBankRecord) {
                $Result = \App\Models\SamraddhBankClosing::find($getTOBankRecord->id);
                $data5['balance'] = $getTOBankRecord->balance + $amount;
                $data5['updated_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($globaldate)));
                $Result->update($data5);
                $insertid = $getTOBankRecord->id;
            } else {
                $oldToDateRecord = \App\Models\SamraddhBankClosing::where('bank_id', $to_bank_id)->whereDate('entry_date', '<', $entryDate)->orderby('entry_date', 'DESC')->first();
                if ($oldToDateRecord) {
                    $cResult = \App\Models\SamraddhBankClosing::find($oldToDateRecord->id);
                    $cdata['closing_balance'] = $oldToDateRecord->balance;
                    $cdata['updated_at'] = $entryDate;
                    $cResult->update($cdata);
                    $data6['bank_id'] = $oldToDateRecord->bank_id;
                    $data6['opening_balance'] = $oldToDateRecord->balance;
                    $data6['balance'] = $oldToDateRecord->balance - $amount;
                    ;
                    $data6['closing_balance'] = 0;
                    $data6['entry_date'] = $entryDate;
                    $data6['entry_time'] = $entryTime;
                    $data6['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($globaldate)));
                    $transcation = \App\Models\SamraddhBankClosing::create($data6);
                    $insertid = $transcation->id;
                } else {
                    $data7['bank_id'] = $to_bank_id;
                    $data7['opening_balance'] = 0;
                    $data7['balance'] = $amount;
                    $data7['closing_balance'] = 0;
                    $data7['entry_date'] = $entryDate;
                    $data7['entry_time'] = $entryTime;
                    $data7['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($globaldate)));
                    $transcation = \App\Models\SamraddhBankClosing::create($data7);
                    $insertid = $transcation->id;
                }
            }
        }
        return $amount;
    }
    /**
     *  create branch day book
     *
     * @param  $transaction_type,$ssbAccountId,$memberId,$branch_id,
     *  $branchCode,$amount,$payment_mode,$deposit_by_name,$deposit_by_id
     * @return \Illuminate\Http\Response
     */
    public static function updateBackDateBranchBankBalance($dayBookRef, $amount, $branch_id, $transfer_mode, $transfer_type, $bank_id, $account_id, $from_bank_id, $from_account_id, $to_bank_id, $to_account_id, $ftdate, $neftcharge)
    {
        $globaldate = $ftdate;
        //$globaldate = '2021-04-08 12:36:04';
        $entryTime = date("H:i:s");
        $entryDate = date("Y-m-d", strtotime(convertDate($globaldate)));
        if ($transfer_type == 0) {
            $getCurrentBranchRecord = \App\Models\BranchCash::where('branch_id', $branch_id)->whereDate('entry_date', $entryDate)->first();
            if ($getCurrentBranchRecord) {
                $bResult = \App\Models\BranchCash::find($getCurrentBranchRecord->id);
                /*if($transfer_mode == 0){
                $bData['loan_balance']=$getCurrentBranchRecord->loan_balance-$amount;
                if($getCurrentBranchRecord->loan_closing_balance > 0){
                $bData['loan_closing_balance']=$getCurrentBranchRecord->loan_closing_balance-$amount;
                }
                }elseif($transfer_mode == 1){
                $bData['balance']=$getCurrentBranchRecord->balance-$amount;
                if($getCurrentBranchRecord->closing_balance > 0){
                $bData['closing_balance']=$getCurrentBranchRecord->closing_balance-$amount;
                }
                }*/
                $bData['balance'] = $getCurrentBranchRecord->balance - $amount;
                if ($getCurrentBranchRecord->closing_balance > 0) {
                    $bData['closing_balance'] = $getCurrentBranchRecord->closing_balance - $amount;
                }
                $bData['updated_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($globaldate)));
                $bResult->update($bData);
                $getNextBranchRecord = \App\Models\BranchCash::where('branch_id', $branch_id)->whereDate('entry_date', '>', $entryDate)->orderby('entry_date', 'ASC')->get();
                if ($getNextBranchRecord) {
                    foreach ($getNextBranchRecord as $key => $value) {
                        $sResult = \App\Models\BranchCash::find($value->id);
                        /*if($transfer_mode == 0){
                        $sData['loan_opening_balance']=$value->loan_closing_balance;
                        $sData['loan_balance']=$value->loan_balance-$amount;
                        if($value->closing_balance > 0){
                        $sData['loan_closing_balance']=$value->loan_closing_balance-$amount;
                        }
                        }elseif($transfer_mode == 1){
                        $sData['opening_balance']=$value->closing_balance;
                        $sData['balance']=$value->balance-$amount;
                        if($value->closing_balance > 0){
                        $sData['closing_balance']=$value->closing_balance-$amount;
                        }
                        }*/
                        $sData['opening_balance'] = $value->closing_balance;
                        $sData['balance'] = $value->balance - $amount;
                        if ($value->closing_balance > 0) {
                            $sData['closing_balance'] = $value->closing_balance - $amount;
                        }
                        $sData['updated_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($globaldate)));
                        $sResult->update($sData);
                    }
                }
            } else {
                $oldDateRecord = \App\Models\BranchCash::where('branch_id', $branch_id)->whereDate('entry_date', '<', $entryDate)->orderby('entry_date', 'DESC')->first();
                if ($oldDateRecord) {
                    $Result1 = \App\Models\BranchCash::find($oldDateRecord->id);
                    $data1['closing_balance'] = $oldDateRecord->balance;
                    //$data1['loan_closing_balance']=$oldDateRecord->loan_balance;
                    $Result1->update($data1);
                    $insertid1 = $oldDateRecord->id;
                    $nextRecordExists = \App\Models\BranchCash::where('branch_id', $branch_id)->whereDate('entry_date', '>', $entryDate)->orderby('entry_date', 'ASC')->get();
                    /*if($transfer_mode == 0){
                    $data2['balance']=$oldDateRecord->balance-$amount;
                    }else{
                    $data2['balance']=$oldDateRecord->balance;
                    }
                    if($transfer_mode == 1){
                    $data2['loan_balance']=$oldDateRecord->loan_balance-$amount;
                    }
                    else{
                    $data2['loan_balance']=$oldDateRecord->loan_balance;
                    }*/
                    $data2['balance'] = $oldDateRecord->balance - $amount;
                    $data2['opening_balance'] = $oldDateRecord->balance;
                    //$data2['loan_opening_balance']=$oldDateRecord->loan_balance;
                    if ($nextRecordExists) {
                        /*if($transfer_mode == 0){
                        $data2['closing_balance']=$oldDateRecord->balance-$amount;
                        }else{
                        $data2['closing_balance']=$oldDateRecord->balance;
                        }
                        if($transfer_mode == 1){
                        $data2['loan_closing_balance']=$oldDateRecord->loan_balance-$amount;
                        }
                        else{
                        $data2['loan_closing_balance']=$oldDateRecord->loan_balance;
                        }*/
                        $data2['closing_balance'] = $oldDateRecord->balance - $amount;
                        foreach ($nextRecordExists as $key => $value) {
                            $sResult = \App\Models\BranchCash::find($value->id);
                            /*if($transfer_mode == 0){
                            $sData['loan_opening_balance']=$value->loan_closing_balance;
                            $sData['loan_balance']=$value->loan_balance-$amount;
                            if($value->closing_balance > 0){
                            $sData['loan_closing_balance']=$value->loan_closing_balance-$amount;
                            }
                            }elseif($transfer_mode == 1){
                            $sData['opening_balance']=$value->closing_balance;
                            $sData['balance']=$value->balance-$amount;
                            if($value->closing_balance > 0){
                            $sData['closing_balance']=$value->closing_balance-$amount;
                            }
                            }*/
                            $sData['opening_balance'] = $value->closing_balance;
                            $sData['balance'] = $value->balance - $amount;
                            if ($value->closing_balance > 0) {
                                $sData['closing_balance'] = $value->closing_balance - $amount;
                            }
                            $sData['updated_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($globaldate)));
                            $sResult->update($sData);
                        }
                    } else {
                        $data2['closing_balance'] = 0;
                        //$data2['loan_closing_balance']=0;
                    }
                    $data2['branch_id'] = $branch_id;
                    $data2['entry_date'] = $entryDate;
                    $data2['entry_time'] = $entryTime;
                    $data2['type'] = $transfer_mode;
                    $data2['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($globaldate)));
                    $transcation = \App\Models\BranchCash::create($data2);
                    $insertid = $transcation->id;
                } else {
                    /*if($transfer_mode == 0){
                    $data3['balance']=$amount;
                    }
                    else                {
                    $data3['balance']=0;
                    }
                    if($transfer_mode == 1){
                    $data3['loan_balance']=$amount;
                    }
                    else{
                    $data3['loan_balance']=0;
                    }*/
                    $data3['balance'] = $amount;
                    $data3['opening_balance'] = 0;
                    $data3['closing_balance'] = 0;
                    //$data3['loan_opening_balance']=0;
                    //$data3['loan_closing_balance']=0;
                    $data3['branch_id'] = $branch_id;
                    $data3['entry_date'] = $entryDate;
                    $data3['entry_time'] = $entryTime;
                    $data3['type'] = $transfer_mode;
                    $data3['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($globaldate)));
                    $transcation = \App\Models\BranchCash::create($data3);
                    $insertid = $transcation->id;
                    $getNextBranchRecord = \App\Models\BranchCash::where('branch_id', $branch_id)->whereDate('entry_date', '>', $entryDate)->orderby('entry_date', 'ASC')->get();
                    if ($getNextBranchRecord) {
                        foreach ($getNextBranchRecord as $key => $value) {
                            $sResult = \App\Models\BranchCash::find($value->id);
                            /*if($transfer_mode == 0){
                            $sData['loan_opening_balance']=$value->loan_closing_balance;
                            $sData['loan_balance']=$value->loan_balance-$amount;
                            if($value->closing_balance > 0){
                            $sData['loan_closing_balance']=$value->loan_closing_balance-$amount;
                            }
                            }elseif($transfer_mode == 1){
                            $sData['opening_balance']=$value->closing_balance;
                            $sData['balance']=$value->balance-$amount;
                            if($value->closing_balance > 0){
                            $sData['closing_balance']=$value->closing_balance-$amount;
                            }
                            }*/
                            $sData['opening_balance'] = $value->closing_balance;
                            $sData['balance'] = $value->balance - $amount;
                            if ($value->closing_balance > 0) {
                                $sData['closing_balance'] = $value->closing_balance - $amount;
                            }
                            $sData['updated_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($globaldate)));
                            $sResult->update($sData);
                        }
                    }
                }
            }
            $getCurrentBranchClosingRecord = \App\Models\BranchClosing::where('branch_id', $branch_id)->whereDate('entry_date', $entryDate)->first();
            if ($getCurrentBranchClosingRecord) {
                $bResult = \App\Models\BranchClosing::find($getCurrentBranchClosingRecord->id);
                /*if($transfer_mode == 0){
                $bData['loan_balance']=$getCurrentBranchClosingRecord->loan_balance-$amount;
                if($getCurrentBranchClosingRecord->loan_closing_balance > 0){
                $bData['loan_closing_balance']=$getCurrentBranchClosingRecord->loan_closing_balance-$amount;
                }
                }elseif($transfer_mode == 1){
                $bData['balance']=$getCurrentBranchClosingRecord->balance-$amount;
                if($getCurrentBranchClosingRecord->closing_balance > 0){
                $bData['closing_balance']=$getCurrentBranchClosingRecord->closing_balance-$amount;
                }
                }*/
                $bData['balance'] = $getCurrentBranchClosingRecord->balance - $amount;
                if ($getCurrentBranchClosingRecord->closing_balance > 0) {
                    $bData['closing_balance'] = $getCurrentBranchClosingRecord->closing_balance - $amount;
                }
                $bData['updated_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($globaldate)));
                $bResult->update($bData);
                $getNextBranchClosingRecord = \App\Models\BranchClosing::where('branch_id', $branch_id)->whereDate('entry_date', '>', $entryDate)->orderby('entry_date', 'ASC')->get();
                if ($getNextBranchClosingRecord) {
                    foreach ($getNextBranchClosingRecord as $key => $value) {
                        $sResult = \App\Models\BranchClosing::find($value->id);
                        /*if($transfer_mode == 0){
                        $sData['loan_opening_balance']=$value->loan_closing_balance;
                        $sData['loan_balance']=$value->loan_balance-$amount;
                        if($value->closing_balance > 0){
                        $sData['loan_closing_balance']=$value->loan_closing_balance-$amount;
                        }
                        }elseif($transfer_mode == 1){
                        $sData['opening_balance']=$value->closing_balance;
                        $sData['balance']=$value->balance-$amount;
                        if($value->closing_balance > 0){
                        $sData['closing_balance']=$value->closing_balance-$amount;
                        }
                        }*/
                        $sData['opening_balance'] = $value->closing_balance;
                        $sData['balance'] = $value->balance - $amount;
                        if ($value->closing_balance > 0) {
                            $sData['closing_balance'] = $value->closing_balance - $amount;
                        }
                        $sData['updated_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($globaldate)));
                        $sResult->update($sData);
                    }
                }
            } else {
                $oldDateRecord = \App\Models\BranchClosing::where('branch_id', $branch_id)->whereDate('entry_date', '<', $entryDate)->orderby('entry_date', 'DESC')->first();
                if ($oldDateRecord) {
                    $Result1 = \App\Models\BranchClosing::find($oldDateRecord->id);
                    $data4['closing_balance'] = $oldDateRecord->balance;
                    //$data4['loan_closing_balance']=$oldDateRecord->loan_balance;
                    $Result1->update($data4);
                    $insertid1 = $oldDateRecord->id;
                    $brNextRecordExists = \App\Models\BranchClosing::where('branch_id', $branch_id)->whereDate('entry_date', '>', $entryDate)->orderby('entry_date', 'ASC')->get();
                    /*if($transfer_mode == 0){
                    $data5['balance']=$oldDateRecord->balance-$amount;
                    }else{
                    $data5['balance']=$oldDateRecord->balance;
                    }
                    if($transfer_mode == 1){
                    $data5['loan_balance']=$oldDateRecord->loan_balance-$amount;
                    }
                    else{
                    $data5['loan_balance']=$oldDateRecord->loan_balance;
                    }*/
                    $data5['balance'] = $oldDateRecord->balance - $amount;
                    $data5['opening_balance'] = $oldDateRecord->balance;
                    //$data5['loan_opening_balance']=$oldDateRecord->loan_balance;
                    if ($brNextRecordExists) {
                        /*if($transfer_mode == 0){
                        $data5['closing_balance']=$oldDateRecord->balance-$amount;
                        }else{
                        $data5['closing_balance']=$oldDateRecord->balance;
                        }
                        if($transfer_mode == 1){
                        $data5['loan_closing_balance']=$oldDateRecord->loan_balance-$amount;
                        }
                        else{
                        $data5['loan_closing_balance']=$oldDateRecord->loan_balance;
                        }*/
                        $data5['closing_balance'] = $oldDateRecord->balance - $amount;
                        foreach ($brNextRecordExists as $key => $value) {
                            $sResult = \App\Models\BranchClosing::find($value->id);
                            /*if($transfer_mode == 0){
                            $sData['loan_opening_balance']=$value->loan_closing_balance;
                            $sData['loan_balance']=$value->loan_balance-$amount;
                            if($value->closing_balance > 0){
                            $sData['loan_closing_balance']=$value->loan_closing_balance-$amount;
                            }
                            }elseif($transfer_mode == 1){
                            $sData['opening_balance']=$value->closing_balance;
                            $sData['balance']=$value->balance-$amount;
                            if($value->closing_balance > 0){
                            $sData['closing_balance']=$value->closing_balance-$amount;
                            }
                            }*/
                            $sData['opening_balance'] = $value->closing_balance;
                            $sData['balance'] = $value->balance - $amount;
                            if ($value->closing_balance > 0) {
                                $sData['closing_balance'] = $value->closing_balance - $amount;
                            }
                            $sData['updated_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($globaldate)));
                            $sResult->update($sData);
                        }
                    } else {
                        $data5['closing_balance'] = 0;
                        //$data5['loan_closing_balance']=0;
                    }
                    $data5['branch_id'] = $branch_id;
                    $data5['entry_date'] = $entryDate;
                    $data5['entry_time'] = $entryTime;
                    $data5['type'] = $transfer_mode;
                    $data5['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($globaldate)));
                    $transcation = \App\Models\BranchClosing::create($data5);
                    $insertid = $transcation->id;
                } else {
                    /*if($transfer_mode == 0){
                    $data6['balance']=$amount;
                    }
                    else                {
                    $data6['balance']=0;
                    }
                    if($transfer_mode == 1){
                    $data6['loan_balance']=$amount;
                    }
                    else{
                    $data6['loan_balance']=0;
                    }*/
                    $data6['balance'] = $amount;
                    $data6['opening_balance'] = 0;
                    $data6['closing_balance'] = 0;
                    //$data6['loan_opening_balance']=0;
                    //$data6['loan_closing_balance']=0;
                    $data6['branch_id'] = $branch_id;
                    $data6['entry_date'] = $entryDate;
                    $data6['entry_time'] = $entryTime;
                    $data6['type'] = $transfer_mode;
                    $data6['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($globaldate)));
                    $transcation = \App\Models\BranchClosing::create($data6);
                    $insertid = $transcation->id;
                    $getNextBranchClosingRecord = \App\Models\BranchClosing::where('branch_id', $branch_id)->whereDate('entry_date', '>', $entryDate)->orderby('entry_date', 'ASC')->get();
                    if ($getNextBranchClosingRecord) {
                        foreach ($getNextBranchClosingRecord as $key => $value) {
                            $sResult = \App\Models\BranchClosing::find($value->id);
                            /*if($transfer_mode == 0){
                            $sData['loan_opening_balance']=$value->loan_closing_balance;
                            $sData['loan_balance']=$value->loan_balance-$amount;
                            if($value->closing_balance > 0){
                            $sData['loan_closing_balance']=$value->loan_closing_balance-$amount;
                            }
                            }elseif($transfer_mode == 1){
                            $sData['opening_balance']=$value->closing_balance;
                            $sData['balance']=$value->balance-$amount;
                            if($value->closing_balance > 0){
                            $sData['closing_balance']=$value->closing_balance-$amount;
                            }
                            }*/
                            $sData['opening_balance'] = $value->closing_balance;
                            $sData['balance'] = $value->balance - $amount;
                            if ($value->closing_balance > 0) {
                                $sData['closing_balance'] = $value->closing_balance - $amount;
                            }
                            $sData['updated_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($globaldate)));
                            $sResult->update($sData);
                        }
                    }
                }
            }
            $getCurrentBankRecord = \App\Models\SamraddhBankClosing::where('bank_id', $bank_id)->where('account_id', $account_id)->whereDate('entry_date', $entryDate)->first();
            if ($getCurrentBankRecord) {
                $Result = \App\Models\SamraddhBankClosing::find($getCurrentBankRecord->id);
                /*if($transfer_mode == 0){
                $data['loan_balance']=$getCurrentBankRecord->loan_balance+$amount;
                if($getCurrentBankRecord->loan_closing_balance > 0){
                $data['loan_closing_balance']=$getCurrentBankRecord->loan_closing_balance+$amount;
                }
                }elseif($transfer_mode == 1){
                $data['balance']=$getCurrentBankRecord->balance+$amount;
                if($getCurrentBankRecord->closing_balance > 0){
                $data['closing_balance']=$getCurrentBankRecord->closing_balance+$amount;
                }
                }*/
                $data['balance'] = $getCurrentBankRecord->balance + $amount;
                if ($getCurrentBankRecord->closing_balance > 0) {
                    $data['closing_balance'] = $getCurrentBankRecord->closing_balance + $amount;
                }
                $data['updated_at'] = $entryDate;
                $Result->update($data);
                $insertid = $getCurrentBankRecord->id;
                $getNextBankRecord = \App\Models\SamraddhBankClosing::where('bank_id', $bank_id)->where('account_id', $account_id)->whereDate('entry_date', '>', $entryDate)->orderby('entry_date', 'ASC')->get();
                foreach ($getNextBankRecord as $key => $value) {
                    $sResult = \App\Models\SamraddhBankClosing::find($value->id);
                    /*if($transfer_mode == 0){
                    $sData7['loan_opening_balance']=$value->loan_closing_balance;
                    $sData7['loan_balance']=$value->loan_balance+$amount;
                    if($value->loan_closing_balance > 0){
                    $sData7['loan_closing_balance']=$value->loan_closing_balance+$amount;
                    }
                    }elseif($transfer_mode == 1){
                    $sData7['opening_balance']=$value->closing_balance;
                    $sData7['balance']=$value->balance+$amount;
                    if($value->closing_balance > 0){
                    $sData7['closing_balance']=$value->closing_balance+$amount;
                    }
                    }*/
                    $sData7['opening_balance'] = $value->closing_balance;
                    $sData7['balance'] = $value->balance + $amount;
                    if ($value->closing_balance > 0) {
                        $sData7['closing_balance'] = $value->closing_balance + $amount;
                    }
                    $sData7['updated_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($globaldate)));
                    $sResult->update($sData7);
                    $insertid = $value->id;
                }
            } else {
                $oldDateRecord = \App\Models\SamraddhBankClosing::where('bank_id', $bank_id)->where('account_id', $account_id)->whereDate('entry_date', '<', $entryDate)->orderby('entry_date', 'DESC')->first();
                if ($oldDateRecord) {
                    $cResult = \App\Models\SamraddhBankClosing::find($oldDateRecord->id);
                    //$cdata['loan_closing_balance']=$oldDateRecord->loan_balance;
                    $cdata['closing_balance'] = $oldDateRecord->balance;
                    $cdata['updated_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($globaldate)));
                    $cResult->update($cdata);
                    $data8RecordExists = \App\Models\SamraddhBankClosing::where('bank_id', $bank_id)->where('account_id', $account_id)->whereDate('entry_date', '>', $entryDate)->orderby('entry_date', 'ASC')->get();
                    $data8['bank_id'] = $oldDateRecord->bank_id;
                    $data8['account_id'] = $oldDateRecord->account_id;
                    /*if($transfer_mode == 0){
                    $data8['balance']=$oldDateRecord->balance+$amount;
                    }else{
                    $data8['balance']=$oldDateRecord->balance;
                    }
                    if($transfer_mode == 1){
                    $data8['loan_balance']=$oldDateRecord->loan_balance+$amount;
                    }
                    else{
                    $data8['loan_balance']=$oldDateRecord->loan_balance;
                    }*/
                    $data8['balance'] = $oldDateRecord->balance + $amount;
                    $data8['opening_balance'] = $oldDateRecord->balance;
                    if ($data8RecordExists) {
                        $data8['closing_balance'] = $oldDateRecord->balance + $amount;
                        foreach ($data8RecordExists as $key => $value) {
                            $sResult = \App\Models\SamraddhBankClosing::find($value->id);
                            $sData7['opening_balance'] = $value->closing_balance;
                            $sData7['balance'] = $value->balance + $amount;
                            if ($value->closing_balance > 0) {
                                $sData7['closing_balance'] = $value->closing_balance + $amount;
                            }
                            $sData7['updated_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($globaldate)));
                            $sResult->update($sData7);
                            $insertid = $value->id;
                        }
                    } else {
                        $data8['closing_balance'] = 0;
                    }
                    //$data8['loan_opening_balance']=$oldDateRecord->loan_balance;
                    //$data8['loan_closing_balance']=0;
                    $data8['entry_date'] = $entryDate;
                    $data8['entry_time'] = $entryTime;
                    $data8['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($globaldate)));
                    $transcation = \App\Models\SamraddhBankClosing::create($data8);
                    $insertid = $transcation->id;
                } else {
                    $data9['bank_id'] = $bank_id;
                    $data9['account_id'] = $account_id;
                    /*if($transfer_mode == 0){
                    $data9['balance']=$amount;
                    }
                    else                {
                    $data9['balance']=0;
                    }
                    if($transfer_mode == 1){
                    $data9['loan_balance']=$amount;
                    }
                    else{
                    $data9['loan_balance']=0;
                    }*/
                    $data9['balance'] = $amount;
                    $data9['opening_balance'] = 0;
                    $data9['closing_balance'] = 0;
                    //$data9['loan_opening_balance']=0;
                    //$data9['loan_closing_balance']=0;
                    $data9['entry_date'] = $entryDate;
                    $data9['entry_time'] = $entryTime;
                    $data9['type'] = $transfer_mode;
                    $data9['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($globaldate)));
                    $transcation = \App\Models\SamraddhBankClosing::create($data9);
                    $insertid = $transcation->id;
                    $getNextBankRecord = \App\Models\SamraddhBankClosing::where('bank_id', $bank_id)->where('account_id', $account_id)->whereDate('entry_date', '>', $entryDate)->orderby('entry_date', 'ASC')->get();
                    foreach ($getNextBankRecord as $key => $value) {
                        $sResult = \App\Models\SamraddhBankClosing::find($value->id);
                        /*if($transfer_mode == 0){
                        $sData7['loan_opening_balance']=$value->loan_closing_balance;
                        $sData7['loan_balance']=$value->loan_balance+$amount;
                        if($value->loan_closing_balance > 0){
                        $sData7['loan_closing_balance']=$value->loan_closing_balance+$amount;
                        }
                        }elseif($transfer_mode == 1){
                        $sData7['opening_balance']=$value->closing_balance;
                        $sData7['balance']=$value->balance+$amount;
                        if($value->closing_balance > 0){
                        $sData7['closing_balance']=$value->closing_balance+$amount;
                        }
                        }*/
                        $sData7['opening_balance'] = $value->closing_balance;
                        $sData7['balance'] = $value->balance + $amount;
                        if ($value->closing_balance > 0) {
                            $sData7['closing_balance'] = $value->closing_balance + $amount;
                        }
                        $sData7['updated_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($globaldate)));
                        $sResult->update($sData7);
                        $insertid = $value->id;
                    }
                }
            }
        } elseif ($transfer_type == 1) {
            $getCurrentFromBankRecord = \App\Models\SamraddhBankClosing::where('bank_id', $from_bank_id)->where('account_id', $from_account_id)->whereDate('entry_date', $entryDate)->first();
            if ($getCurrentFromBankRecord) {
                $Result = \App\Models\SamraddhBankClosing::find($getCurrentFromBankRecord->id);
                $data['balance'] = $getCurrentFromBankRecord->balance - ($amount + $neftcharge);
                if ($getCurrentFromBankRecord->closing_balance > 0) {
                    $data['closing_balance'] = $getCurrentFromBankRecord->closing_balance - ($amount + $neftcharge);
                }
                $data['updated_at'] = $entryDate;
                $Result->update($data);
                $insertid = $getCurrentFromBankRecord->id;
                $getNextFromBankRecord = \App\Models\SamraddhBankClosing::where('bank_id', $from_bank_id)->where('account_id', $from_account_id)->whereDate('entry_date', '>', $entryDate)->orderby('entry_date', 'ASC')->get();
                if ($getNextFromBankRecord) {
                    foreach ($getNextFromBankRecord as $key => $value) {
                        $sResult = \App\Models\SamraddhBankClosing::find($value->id);
                        $sData['opening_balance'] = $value->closing_balance;
                        $sdata['balance'] = $value->balance - $amount;
                        if ($value->closing_balance > 0) {
                            $sdata['closing_balance'] = $value->closing_balance - ($amount + $neftcharge);
                        }
                        $sdata['updated_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($globaldate)));
                        $sResult->update($sdata);
                    }
                }
            } else {
                $oldCurrentFromDateRecord = \App\Models\SamraddhBankClosing::where('bank_id', $from_bank_id)->where('account_id', $from_account_id)->whereDate('entry_date', '<', $entryDate)->orderby('entry_date', 'DESC')->first();
                if ($oldCurrentFromDateRecord) {
                    $cResult = \App\Models\SamraddhBankClosing::find($oldCurrentFromDateRecord->id);
                    $cdata['closing_balance'] = $oldCurrentFromDateRecord->balance;
                    //$cdata['loan_closing_balance']=$oldCurrentFromDateRecord->loan_balance;
                    $cdata['updated_at'] = $entryDate;
                    $cResult->update($cdata);
                    $data10RecordExists = \App\Models\SamraddhBankClosing::where('bank_id', $bank_id)->where('account_id', $account_id)->whereDate('entry_date', '>', $entryDate)->orderby('entry_date', 'ASC')->get();
                    $data10['bank_id'] = $oldCurrentFromDateRecord->bank_id;
                    $data10['account_id'] = $oldCurrentFromDateRecord->account_id;
                    /******** Micro *************/
                    $data10['opening_balance'] = $oldCurrentFromDateRecord->balance;
                    $data10['balance'] = $oldCurrentFromDateRecord->balance - ($amount + $neftcharge);
                    if ($data10RecordExists) {
                        $data10['closing_balance'] = $oldCurrentFromDateRecord->balance - ($amount + $neftcharge);
                        foreach ($data10RecordExists as $key => $value) {
                            $sResult = \App\Models\SamraddhBankClosing::find($value->id);
                            $sData['opening_balance'] = $value->closing_balance;
                            $sdata['balance'] = $value->balance - $amount;
                            if ($value->closing_balance > 0) {
                                $sdata['closing_balance'] = $value->closing_balance - ($amount + $neftcharge);
                            }
                            $sdata['updated_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($globaldate)));
                            $sResult->update($sdata);
                        }
                    } else {
                        $data10['closing_balance'] = 0;
                    }
                    /******** Micro *************/
                    /******** Loan *************/
                    /*$data10['loan_balance']=$oldCurrentFromDateRecord->loan_balance;
                    $data10['loan_opening_balance']=$oldCurrentFromDateRecord->loan_balance;
                    $data10['loan_closing_balance']=0;*/
                    /******** Loan *************/
                    $data10['entry_date'] = $entryDate;
                    $data10['entry_time'] = $entryTime;
                    $data10['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($globaldate)));
                    $transcation = \App\Models\SamraddhBankClosing::create($data10);
                    $insertid = $transcation->id;
                } else {
                    $data11['bank_id'] = $from_bank_id;
                    $data11['account_id'] = $from_account_id;
                    /******** Micro *************/
                    $data11['opening_balance'] = 0;
                    $data11['balance'] = -($amount + $neftcharge);
                    $data11['closing_balance'] = 0;
                    /******** Micro *************/
                    /******** Loan *************/
                    /*$data11['loan_balance']=0;
                    $data11['loan_opening_balance']=0;
                    $data11['loan_closing_balance']=0;*/
                    /******** Loan *************/
                    $data11['entry_date'] = $entryDate;
                    $data11['entry_time'] = $entryTime;
                    $data11['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($globaldate)));
                    $transcation = \App\Models\SamraddhBankClosing::create($data11);
                    $insertid = $transcation->id;
                    $getNextFromBankRecord = \App\Models\SamraddhBankClosing::where('bank_id', $from_bank_id)->where('account_id', $from_account_id)->whereDate('entry_date', '>', $entryDate)->orderby('entry_date', 'ASC')->get();
                    if ($getNextFromBankRecord) {
                        foreach ($getNextFromBankRecord as $key => $value) {
                            $sResult = \App\Models\SamraddhBankClosing::find($value->id);
                            $sData['opening_balance'] = $value->closing_balance;
                            $sdata['balance'] = $value->balance - $amount;
                            if ($value->closing_balance > 0) {
                                $sdata['closing_balance'] = $value->closing_balance - ($amount + $neftcharge);
                            }
                            $sdata['updated_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($globaldate)));
                            $sResult->update($sdata);
                        }
                    }
                }
            }
            $getCurrentTOBankRecord = \App\Models\SamraddhBankClosing::where('bank_id', $to_bank_id)->where('account_id', $to_account_id)->whereDate('entry_date', $entryDate)->first();
            if ($getCurrentTOBankRecord) {
                $Result = \App\Models\SamraddhBankClosing::find($getCurrentTOBankRecord->id);
                $data['balance'] = $getCurrentTOBankRecord->balance + $amount;
                if ($getCurrentTOBankRecord->closing_balance > 0) {
                    $data['closing_balance'] = $getCurrentTOBankRecord->closing_balance + $amount;
                }
                $data['updated_at'] = $entryDate;
                $Result->update($data);
                $insertid = $getCurrentTOBankRecord->id;
                $getNextTOBankRecord = \App\Models\SamraddhBankClosing::where('bank_id', $to_bank_id)->where('account_id', $to_account_id)->whereDate('entry_date', '>', $entryDate)->orderby('entry_date', 'ASC')->get();
                if ($getNextTOBankRecord) {
                    foreach ($getNextTOBankRecord as $key => $value) {
                        $sResult = \App\Models\SamraddhBankClosing::find($value->id);
                        $sData['opening_balance'] = $value->closing_balance;
                        $sdata['balance'] = $value->balance + $amount;
                        if ($value->closing_balance > 0) {
                            $sdata['closing_balance'] = $value->closing_balance + $amount;
                        }
                        $sdata['updated_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($globaldate)));
                        $sResult->update($sdata);
                    }
                }
            } else {
                $oldToDateRecord = \App\Models\SamraddhBankClosing::where('bank_id', $to_bank_id)->where('account_id', $to_account_id)->whereDate('entry_date', '<', $entryDate)->orderby('entry_date', 'DESC')->first();
                if ($oldToDateRecord) {
                    $cResult = \App\Models\SamraddhBankClosing::find($oldToDateRecord->id);
                    $cdata['closing_balance'] = $oldToDateRecord->balance;
                    //$cdata['loan_closing_balance']=$oldToDateRecord->loan_balance;
                    $cdata['updated_at'] = $entryDate;
                    $cResult->update($cdata);
                    $data12RecordExists = \App\Models\SamraddhBankClosing::where('bank_id', $bank_id)->where('account_id', $account_id)->whereDate('entry_date', '>', $entryDate)->orderby('entry_date', 'ASC')->get();
                    $data12['bank_id'] = $oldToDateRecord->bank_id;
                    $data12['account_id'] = $oldToDateRecord->account_id;
                    /******** Micro *************/
                    $data12['opening_balance'] = $oldToDateRecord->balance;
                    $data12['balance'] = $oldToDateRecord->balance + $amount;
                    if ($data12RecordExists) {
                        $data12['closing_balance'] = $oldToDateRecord->balance + $amount;
                        foreach ($data12RecordExists as $key => $value) {
                            $sResult = \App\Models\SamraddhBankClosing::find($value->id);
                            $sData['opening_balance'] = $value->closing_balance;
                            $sdata['balance'] = $value->balance + $amount;
                            if ($value->closing_balance > 0) {
                                $sdata['closing_balance'] = $value->closing_balance + $amount;
                            }
                            $sdata['updated_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($globaldate)));
                            $sResult->update($sdata);
                        }
                    } else {
                        $data12['closing_balance'] = 0;
                    }
                    /******** Micro *************/
                    /******** Loan *************/
                    /*$data12['loan_opening_balance']=$oldToDateRecord->loan_balance;
                    $data12['loan_balance']=$oldToDateRecord->loan_balance;
                    $data12['loan_closing_balance']=0;*/
                    /******** Loan *************/
                    $data12['entry_date'] = $entryDate;
                    $data12['entry_time'] = $entryTime;
                    $data12['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($globaldate)));
                    $transcation = \App\Models\SamraddhBankClosing::create($data12);
                    $insertid = $transcation->id;
                } else {
                    $data13['bank_id'] = $to_bank_id;
                    $data13['account_id'] = $to_account_id;
                    /******** Micro *************/
                    $data13['opening_balance'] = 0;
                    $data13['balance'] = $amount;
                    $data13['closing_balance'] = 0;
                    /******** Micro *************/
                    /******** Loan *************/
                    /*$data13['loan_opening_balance']=0;
                    $data13['loan_balance']=0;
                    $data13['loan_closing_balance']=0;*/
                    /******** Loan *************/
                    $data13['entry_date'] = $entryDate;
                    $data13['entry_time'] = $entryTime;
                    $data13['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($globaldate)));
                    $transcation = \App\Models\SamraddhBankClosing::create($data13);
                    $insertid = $transcation->id;
                    $getNextTOBankRecord = \App\Models\SamraddhBankClosing::where('bank_id', $to_bank_id)->where('account_id', $to_account_id)->whereDate('entry_date', '>', $entryDate)->orderby('entry_date', 'ASC')->get();
                    if ($getNextTOBankRecord) {
                        foreach ($getNextTOBankRecord as $key => $value) {
                            $sResult = \App\Models\SamraddhBankClosing::find($value->id);
                            $sData['opening_balance'] = $value->closing_balance;
                            $sdata['balance'] = $value->balance + $amount;
                            if ($value->closing_balance > 0) {
                                $sdata['closing_balance'] = $value->closing_balance + $amount;
                            }
                            $sdata['updated_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($globaldate)));
                            $sResult->update($sdata);
                        }
                    }
                }
            }
        }
        return $insertid;
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
                //$cdata['loan_closing_balance']=$oldCurrentFromDateRecord->loan_balance;
                $cdata['closing_balance'] = $oldCurrentFromDateRecord->balance;
                $cdata['updated_at'] = $entryDate;
                $cResult->update($cdata);
                $nextRecordExists = \App\Models\SamraddhBankClosing::where('bank_id', $bank_id)->where('account_id', $account_id)->whereDate('entry_date', '>', $entryDate)->orderby('entry_date', 'ASC')->get();
                $data1['bank_id'] = $oldCurrentFromDateRecord->bank_id;
                $data1['account_id'] = $oldCurrentFromDateRecord->account_id;
                $data1['opening_balance'] = $oldCurrentFromDateRecord->balance;
                $data1['balance'] = $oldCurrentFromDateRecord->balance - ($amount + $neft);
                if ($nextRecordExists) {
                    $data1['closing_balance'] = $oldCurrentFromDateRecord->balance - ($amount + $neft);
                    foreach ($nextRecordExists as $key => $value) {
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
    /**
     *  create branch day book
     *
     * @param  $transaction_type,$ssbAccountId,$memberId,$branch_id,
     *  $branchCode,$amount,$payment_mode,$deposit_by_name,$deposit_by_id
     * @return \Illuminate\Http\Response
     */
    public static function updateBackDateloanChargeBalance($amount, $branch_id, $ltdate)
    {
        $globaldate = $ltdate;
        $entryTime = date("H:i:s");
        $entryDate = date("Y-m-d", strtotime(convertDate($globaldate)));
        /************* Only Cash ***************/
        $getCurrentBranchRecord = \App\Models\BranchCash::where('branch_id', $branch_id)->whereDate('entry_date', $entryDate)->first();
        if ($getCurrentBranchRecord) {
            $bResult = \App\Models\BranchCash::find($getCurrentBranchRecord->id);
            $bData['balance'] = $getCurrentBranchRecord->balance + $amount;
            if ($getCurrentBranchRecord->closing_balance > 0) {
                $bData['closing_balance'] = $getCurrentBranchRecord->closing_balance + $amount;
            }
            $bData['updated_at'] = $entryDate;
            $bResult->update($bData);
            $getNextBranchRecord = \App\Models\BranchCash::where('branch_id', $branch_id)->whereDate('entry_date', '>', $entryDate)->orderby('entry_date', 'ASC')->get();
            if ($getNextBranchRecord) {
                foreach ($getNextBranchRecord as $key => $value) {
                    $sResult = \App\Models\BranchCash::find($value->id);
                    $sData['opening_balance'] = $value->closing_balance;
                    $sData['balance'] = $value->balance + $amount;
                    if ($value->closing_balance > 0) {
                        $sData['closing_balance'] = $value->closing_balance + $amount;
                    }
                    $sData['updated_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($globaldate)));
                    $sResult->update($sData);
                }
            }
        } else {
            $oldCurrentFromDateRecord = \App\Models\BranchCash::where('branch_id', $branch_id)->whereDate('entry_date', '<', $entryDate)->orderby('entry_date', 'DESC')->first();
            if ($oldCurrentFromDateRecord) {
                $cResult = \App\Models\BranchCash::find($oldCurrentFromDateRecord->id);
                $cdata['closing_balance'] = $oldCurrentFromDateRecord->balance;
                //$cdata['loan_closing_balance']=$oldCurrentFromDateRecord->loan_balance;
                $cdata['updated_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($globaldate)));
                $cResult->update($cdata);
                $data1RecordExists = \App\Models\BranchCash::where('branch_id', $branch_id)->whereDate('entry_date', '>', $entryDate)->orderby('entry_date', 'ASC')->get();
                $data1['branch_id'] = $oldCurrentFromDateRecord->branch_id;
                $data1['opening_balance'] = $oldCurrentFromDateRecord->balance;
                $data1['balance'] = $oldCurrentFromDateRecord->balance - $amount;
                if ($data1RecordExists) {
                    $data1['closing_balance'] = $oldCurrentFromDateRecord->balance - $amount;
                    foreach ($data1RecordExists as $key => $value) {
                        $sResult = \App\Models\BranchCash::find($value->id);
                        $sData['opening_balance'] = $value->closing_balance;
                        $sData['balance'] = $value->balance + $amount;
                        if ($value->closing_balance > 0) {
                            $sData['closing_balance'] = $value->closing_balance + $amount;
                        }
                        $sData['updated_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($globaldate)));
                        $sResult->update($sData);
                    }
                } else {
                    $data1['closing_balance'] = 0;
                }
                $data1['opening_balance'] = $oldCurrentFromDateRecord->balance;
                $data1['balance'] = $oldCurrentFromDateRecord->balance;
                $data1['closing_balance'] = 0;
                $data1['entry_date'] = $entryDate;
                $data1['entry_time'] = $entryTime;
                $data1['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($globaldate)));
                $transcation = \App\Models\BranchCash::create($data1);
                $insertid = $transcation->id;
            } else {
                $data2['branch_id'] = $branch_id;
                $data2['opening_balance'] = 0;
                $data2['balance'] = $amount;
                $data2['closing_balance'] = 0;
                /*$data2['loan_opening_balance']=0;
                $data2['loan_balance']=0;
                $data2['loan_closing_balance']=0;*/
                $data2['entry_date'] = $entryDate;
                $data2['entry_time'] = $entryTime;
                $data2['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($globaldate)));
                $transcation = \App\Models\BranchCash::create($data2);
                $insertid = $transcation->id;
            }
        }
        /************* Only Cash ***************/
        /************* Cash Online Cheque ***************/
        $getCurrentBranchRecord = \App\Models\BranchClosing::where('branch_id', $branch_id)->whereDate('entry_date', $entryDate)->first();
        if ($getCurrentBranchRecord) {
            $bResult = \App\Models\BranchClosing::find($getCurrentBranchRecord->id);
            $bData['balance'] = $getCurrentBranchRecord->balance + $amount;
            if ($getCurrentBranchRecord->closing_balance > 0) {
                $bData['closing_balance'] = $getCurrentBranchRecord->closing_balance + $amount;
            }
            $bData['updated_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($globaldate)));
            $bResult->update($bData);
            $getNextBranchRecord = \App\Models\BranchClosing::where('branch_id', $branch_id)->whereDate('entry_date', '>', $entryDate)->orderby('entry_date', 'ASC')->get();
            if ($getNextBranchRecord) {
                foreach ($getNextBranchRecord as $key => $value) {
                    $sResult = \App\Models\BranchClosing::find($value->id);
                    $sData['opening_balance'] = $value->closing_balance;
                    $sData['balance'] = $value->balance + $amount;
                    if ($value->closing_balance > 0) {
                        $sData['closing_balance'] = $value->closing_balance + $amount;
                    }
                    $sData['updated_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($globaldate)));
                    $sResult->update($sData);
                }
            }
        } else {
            $oldCurrentFromDateRecord = \App\Models\BranchClosing::where('branch_id', $branch_id)->whereDate('entry_date', '<', $entryDate)->orderby('entry_date', 'DESC')->first();
            if ($oldCurrentFromDateRecord) {
                $cResult = \App\Models\BranchClosing::find($oldCurrentFromDateRecord->id);
                $cdata['closing_balance'] = $oldCurrentFromDateRecord->balance;
                //$cdata['loan_closing_balance']=$oldCurrentFromDateRecord->loan_balance;
                $cdata['updated_at'] = $entryDate;
                $cResult->update($cdata);
                $data3RecordExists = \App\Models\BranchClosing::where('branch_id', $branch_id)->whereDate('entry_date', '>', $entryDate)->orderby('entry_date', 'ASC')->get();
                $data3['branch_id'] = $oldCurrentFromDateRecord->branch_id;
                $data3['opening_balance'] = $oldCurrentFromDateRecord->balance;
                $data3['balance'] = $oldCurrentFromDateRecord->balance;
                if ($data3RecordExists) {
                    $data3['closing_balance'] = $oldCurrentFromDateRecord->balance;
                    foreach ($data3RecordExists as $key => $value) {
                        $sResult = \App\Models\BranchClosing::find($value->id);
                        $sData['opening_balance'] = $value->closing_balance;
                        $sData['balance'] = $value->balance + $amount;
                        if ($value->closing_balance > 0) {
                            $sData['closing_balance'] = $value->closing_balance + $amount;
                        }
                        $sData['updated_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($globaldate)));
                        $sResult->update($sData);
                    }
                } else {
                    $data3['closing_balance'] = 0;
                }
                $data3['opening_balance'] = $oldCurrentFromDateRecord->balance;
                $data3['balance'] = $oldCurrentFromDateRecord->balance + $amount;
                $data3['closing_balance'] = 0;
                $data3['entry_date'] = $entryDate;
                $data3['entry_time'] = $entryTime;
                $data3['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($globaldate)));
                $transcation = \App\Models\BranchClosing::create($data3);
                $insertid = $transcation->id;
            } else {
                $data4['branch_id'] = $branch_id;
                $data4['opening_balance'] = 0;
                $data4['balance'] = $amount;
                $data4['closing_balance'] = 0;
                /*$data4['loan_opening_balance']=0;
                $data4['loan_balance']=0;
                $data4['loan_closing_balance']=0;*/
                $data4['entry_date'] = $entryDate;
                $data4['entry_time'] = $entryTime;
                $data4['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($globaldate)));
                $transcation = \App\Models\BranchClosing::create($data4);
                $insertid = $transcation->id;
            }
        }
        /************* Cash Online Cheque ***************/
        return true;
    }
    /**  Call Only Cash Mode transction
     *  create bank cash (If the current date entry is not found, then create a current date entry with a closing balance of the old date entry. If there is an entry, then update the closing balance with the amount)
     *
     * @param  $branch_id,$date,$amount,$type
     * @return \Illuminate\Http\Response (row id return)
     */
    public static function updateBranchBalanceFromWithdrawal($branch_id, $date, $amount)
    {
        $globaldate = $date;
        $entryTime = date("H:i:s");
        $entryDate = date("Y-m-d", strtotime(convertDate($date)));
        $currentDateRecord = \App\Models\BranchCash::where('branch_id', $branch_id)->whereDate('entry_date', $entryDate)->first();
        if ($currentDateRecord) {
            $Result = \App\Models\BranchCash::find($currentDateRecord->id);
            $data['balance'] = $currentDateRecord->balance - $amount;
            if ($currentDateRecord->closing_balance > 0) {
                $data['closing_balance'] = $currentDateRecord->closing_balance - $amount;
            }
            $data['updated_at'] = $date;
            $Result->update($data);
            $insertid = $currentDateRecord->id;
            $getNextBranchRecord = \App\Models\BranchCash::where('branch_id', $branch_id)->whereDate('entry_date', '>', $entryDate)->orderby('entry_date', 'ASC')->get();
            if ($getNextBranchRecord) {
                foreach ($getNextBranchRecord as $key => $value) {
                    $sResult = \App\Models\BranchCash::find($value->id);
                    $sData['opening_balance'] = $value->closing_balance;
                    $sData['balance'] = $value->balance - $amount;
                    if ($value->closing_balance > 0) {
                        $sData['closing_balance'] = $value->closing_balance - $amount;
                    }
                    $sData['updated_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($globaldate)));
                    $sResult->update($sData);
                }
            }
        } else {
            $oldDateRecord = \App\Models\BranchCash::where('branch_id', $branch_id)->whereDate('entry_date', '<', $entryDate)->orderby('entry_date', 'DESC')->first();
            if ($oldDateRecord) {
                $Result1 = \App\Models\BranchClosing::find($oldDateRecord->id);
                $data1['closing_balance'] = $oldDateRecord->balance;
                //$data1['loan_closing_balance']=$oldDateRecord->loan_balance;
                $Result1->update($data1);
                $insertid1 = $oldDateRecord->id;
                $data1RecordExists = \App\Models\BranchCash::where('branch_id', $branch_id)->whereDate('entry_date', '>', $entryDate)->orderby('entry_date', 'ASC')->get();
                $data1['opening_balance'] = $oldDateRecord->balance;
                $data1['balance'] = $oldDateRecord->balance - $amount;
                if ($data1RecordExists) {
                    $data1['closing_balance'] = $oldDateRecord->balance - $amount;
                    foreach ($data1RecordExists as $key => $value) {
                        $sResult = \App\Models\BranchCash::find($value->id);
                        $sData['opening_balance'] = $value->closing_balance;
                        $sData['balance'] = $value->balance - $amount;
                        if ($value->closing_balance > 0) {
                            $sData['closing_balance'] = $value->closing_balance - $amount;
                        }
                        $sData['updated_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($globaldate)));
                        $sResult->update($sData);
                    }
                } else {
                    $data1['closing_balance'] = 0;
                }
                //$data1['loan_opening_balance']=$oldDateRecord->loan_balance;
                //$data1['loan_balance']=$oldDateRecord->loan_balance;
                //$data1['loan_closing_balance']=0;
                $data1['branch_id'] = $branch_id;
                $data1['entry_date'] = $entryDate;
                $data1['entry_time'] = $entryTime;
                $data1['type'] = 0;
                $data1['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($globaldate)));
                $transcation = \App\Models\BranchCash::create($data1);
                $insertid = $transcation->id;
            } else {
                $data2['opening_balance'] = 0;
                $data2['balance'] = $amount;
                $data2['closing_balance'] = 0;
                /*$data2['loan_opening_balance']=0;
                $data2['loan_balance']=0;
                $data2['loan_closing_balance']=0;*/
                $data2['branch_id'] = $branch_id;
                $data2['entry_date'] = $entryDate;
                $data2['entry_time'] = $entryTime;
                $data2['type'] = 0;
                $data2['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($globaldate)));
                $transcation = \App\Models\BranchCash::create($data2);
                $insertid = $transcation->id;
            }
        }
        $branchclosingDateRecord = \App\Models\BranchClosing::where('branch_id', $branch_id)->whereDate('entry_date', $entryDate)->first();
        if ($branchclosingDateRecord) {
            $Result = \App\Models\BranchClosing::find($branchclosingDateRecord->id);
            $data['balance'] = $branchclosingDateRecord->balance - $amount;
            if ($branchclosingDateRecord->closing_balance > 0) {
                $bData['closing_balance'] = $branchclosingDateRecord->balance - $amount;
            }
            $data['updated_at'] = $date;
            $Result->update($data);
            $insertid = $branchclosingDateRecord->id;
            $getNextBranchRecord = \App\Models\BranchClosing::where('branch_id', $branch_id)->whereDate('entry_date', '>', $entryDate)->orderby('entry_date', 'ASC')->get();
            if ($getNextBranchRecord) {
                foreach ($getNextBranchRecord as $key => $value) {
                    $sResult = \App\Models\BranchCash::find($value->id);
                    $sData['opening_balance'] = $value->closing_balance;
                    $sData['balance'] = $value->balance - $amount;
                    if ($value->closing_balance > 0) {
                        $sData['closing_balance'] = $value->closing_balance - $amount;
                    }
                    $sData['updated_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($globaldate)));
                    $sResult->update($sData);
                }
            }
        } else {
            $oldDateBranchRecord = \App\Models\BranchClosing::where('branch_id', $branch_id)->whereDate('entry_date', '<', $entryDate)->orderby('entry_date', 'DESC')->first();
            if ($oldDateBranchRecord) {
                $Result1 = \App\Models\BranchClosing::find($oldDateBranchRecord->id);
                $data1['closing_balance'] = $oldDateBranchRecord->balance;
                //$data1['loan_closing_balance']=$oldDateBranchRecord->loan_balance;
                $Result1->update($data1);
                $insertid1 = $oldDateBranchRecord->id;
                $data3RecordExists = \App\Models\BranchClosing::where('branch_id', $branch_id)->whereDate('entry_date', '>', $entryDate)->orderby('entry_date', 'ASC')->get();
                $data3['opening_balance'] = $oldDateBranchRecord->balance;
                $data3['balance'] = $oldDateBranchRecord->balance - $amount;
                if ($data3RecordExists) {
                    $data3['closing_balance'] = $oldDateBranchRecord->balance - $amount;
                    foreach ($data3RecordExists as $key => $value) {
                        $sResult = \App\Models\BranchCash::find($value->id);
                        $sData['opening_balance'] = $value->closing_balance;
                        $sData['balance'] = $value->balance - $amount;
                        if ($value->closing_balance > 0) {
                            $sData['closing_balance'] = $value->closing_balance - $amount;
                        }
                        $sData['updated_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($globaldate)));
                        $sResult->update($sData);
                    }
                } else {
                    $data3['closing_balance'] = 0;
                }
                /*$data3['loan_opening_balance']=$oldDateBranchRecord->loan_balance;
                $data3['loan_closing_balance']=0;
                $data3['loan_balance']=$oldDateBranchRecord->loan_balance;*/
                $data3['branch_id'] = $branch_id;
                $data3['entry_date'] = $entryDate;
                $data3['entry_time'] = $entryTime;
                $data3['type'] = 0;
                $data3['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($globaldate)));
                $transcation = \App\Models\BranchClosing::create($data3);
                $insertid = $transcation->id;
            } else {
                $data4['opening_balance'] = 0;
                $data4['closing_balance'] = 0;
                $data4['balance'] = $amount;
                /*$data4['loan_opening_balance']=0;
                $data4['loan_closing_balance']=0;
                $data4['loan_balance']=0;*/
                $data4['branch_id'] = $branch_id;
                $data4['entry_date'] = $entryDate;
                $data4['entry_time'] = $entryTime;
                $data4['type'] = 0;
                $data4['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($globaldate)));
                $transcation = \App\Models\BranchClosing::create($data4);
                $insertid = $transcation->id;
            }
        }
        return true;
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
    public static function createBranchDayBookNew($daybook_ref_id, $branch_id, $type, $sub_type, $type_id, $associate_id, $member_id, $branch_id_to, $branch_id_from, $opening_balance, $amount, $closing_balance, $description, $description_dr, $description_cr, $payment_type, $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $is_contra, $contra_id, $created_at, $updated_at, $type_transaction_id)
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
        $data['created_by'] = $created_by;
        if ($created_by == 3) {
            $data['app_login_user_id'] = $created_by_id;
            $data['is_app'] = 1;
        }
        $data['created_by_id'] = $created_by_id;
        $data['is_contra'] = $is_contra;
        $data['contra_id'] = $contra_id;
        $data['created_at'] = $created_at;
        $data['updated_at'] = $updated_at;
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
        $data['created_by'] = $created_by;
        $data['created_by_id'] = $created_by_id;
        $data['created_at'] = $created_at;
        $data['updated_at'] = $updated_at;
        $transcation = \App\Models\AllTransaction::create($data);
        return true;
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
        $data['created_by'] = $created_by;
        $data['created_by_id'] = $created_by_id;
        $data['created_at'] = $created_at;
        $data['updated_at'] = $updated_at;
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
        $data['created_by'] = $created_by;
        $data['created_by_id'] = $created_by_id;
        $data['created_at'] = $created_at;
        $data['updated_at'] = $updated_at;
        $transcation = \App\Models\SamraddhBankDaybook::create($data);
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
        $data['created_by'] = $created_by;
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
        $data['created_by'] = $created_by;
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
        $data['created_by'] = $created_by;
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
        $data['created_by'] = $created_by;
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
    public static function createSavingAccountDescriptionModify($memberId, $branch_id, $branchCode, $amount, $payment_mode, $investmentId, $miCode, $investmentAccountNoSsb, $is_primary, $faCode, $description, $associate_id, $type = NULL, $daybookRefRD = NULL, $companyId = NULL, $memberAutoId = NULL, $passbookNumber = NULL, $creatdBy = NULL)
    {
        $ssbArray = array();
        $miCodePassbook = str_pad($miCode, 5, '0', STR_PAD_LEFT);
        // pass fa id 20 for passbook
        $globaldate = Session::get('created_at');


        // genarate  member saving account no
        $account_no = $investmentAccountNoSsb;
        $data['account_no'] = $account_no;
        $data['member_investments_id'] = $investmentId;
        $data['customer_id'] = $memberAutoId;
        $data['is_primary'] = $is_primary;
        $data['passbook_no'] = $passbookNumber;
        $data['mi_code'] = $miCode;
        $data['fa_code'] = $faCode;
        $data['member_id'] = $memberId;
        $data['associate_id'] = $associate_id;
        $data['branch_id'] = $branch_id;
        $data['branch_code'] = $branchCode;
        $data['old_branch_id'] = $branch_id;
        $data['old_branch_code'] = $branchCode;
        $data['balance'] = 0;
        $data['currency_code'] = 'INR';
        $data['created_by_id'] = Auth::user()->id??$associate_id;
        $data['company_id'] = $companyId;
        $data['created_by'] = $creatdBy;
        $ssb['payment_mode'] = $payment_mode;
        // $data['created_at'] = $globaldate;
        /**  modified by Mahesh on 20-nov 2023 to make it possible to chk that entry was made by app here created by 3 is for associate application user*/
        if ($creatdBy == 3) {
            $data['is_app'] = 1;
            $ssb['is_app'] = 1;
            $ssb['app_login_user_id'] = $associate_id;
            $ssb['payment_mode'] = 4; //Associate app payment mode fix ssb only
            $data['balance'] = $amount;
            $data['created_by_id'] = $associate_id;
            $data['app_login_user_id'] = $associate_id;
        }
        $currentDateTime = Carbon::now();
        $his = "$currentDateTime->hour:$currentDateTime->minute:$currentDateTime->second";
        $fd = date("Y-m-d H:i:s", strtotime($globaldate));
        $data['created_at'] = $fd;
        $ssbAccount = \App\Models\SavingAccount::create($data);


        $ssbArray['ssb_id'] = $ssbAccount->id;
        // create saving account transcation
        $ssb['saving_account_id'] = $ssbArray['ssb_id'];
        $ssb['associate_id'] = $associate_id;
        $ssb['branch_id'] = $branch_id;
        $ssb['daybook_ref_id'] = $daybookRefRD;
        $ssb['account_no'] = $account_no;
        $ssb['type'] = 1;
        $ssb['opening_balance'] = $amount;
        $ssb['deposit'] = $amount;
        $ssb['withdrawal'] = 0;
        $ssb['description'] = $description;
        $ssb['currency_code'] = 'INR';
        $ssb['payment_type'] = 'CR';
        $ssb['company_id'] = $companyId;
        // $ssb['transaction_id'] = $daybookRefRD;
        if (isset($globaldate) && $globaldate != '') {
            $ssb['created_at'] = $fd;
        }
        if ($amount > 0) {
            $ssbAccountTran = \App\Models\SavingAccountTranscation::create($ssb);
            $ssbArray['ssb_transaction_id'] = $ssbAccountTran->id;
            // update saving account current balance
            $balance_update = $amount;
            $ssbBalance = \App\Models\SavingAccount::find($ssbArray['ssb_id']);
            $ssbBalance->balance = $balance_update;
            $ssbBalance->save();
        }else{
            $ssbArray['ssb_transaction_id'] = 0;
        }


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
        $resSsb = \App\Models\SavingAccountTranscation::create($dataSsb);
        // $ssbBalance = $balance-$amount;
        $sResult = \App\Models\SavingAccount::find($account_id);
        $sData['balance'] = $ssbBalance;
        $sResult->update($sData);
        //print_r($resSsb->id);die;
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
    public static function createDayBookNew($transaction_id, $satRefId, $transaction_type, $account_id, $associateId, $memberId, $openingBalance, $deposite, $withdrawal, $description, $referenceno, $branch_id, $branchCode, $amountArray, $payment_mode, $deposit_by_name, $deposit_by_id, $account_no, $cheque_dd_no, $bank_name, $branch_name, $payment_date, $online_payment_id, $online_payment_by, $saving_account_id, $payment_type, $received_cheque_id, $cheque_deposit_bank_id, $cheque_deposit_bank_ac_id, $online_deposit_bank_id, $online_deposit_bank_ac_id, $companyId, $is_app = Null)
    {

        $globaldate = Session::get('created_at');
        $entryTime = date("H:i:s");
        foreach ($amountArray as $key => $option) {
            $data_log['transaction_type'] = $transaction_type;
            $data_log['transaction_id'] = $transaction_id;
            $data_log['saving_account_transaction_reference_id'] = $satRefId;
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
            } else {
                $data_log['payment_date'] = date("Y-m-d H:i:s", strtotime($globaldate));
            }
            $data_log['amount_deposit_by_name'] = $deposit_by_name;
            if ($deposit_by_id) {
                $data_log['amount_deposit_by_id'] = $deposit_by_id;
            }
             /**  modified by Mahesh on 20-nov 2023 to make it possible to chk that entry was made by app here created by 3 is for associate application user*/
             if ($is_app == 1) {
                $data_log['created_by_id'] = $associateId;
                $data_log['app_login_user_id'] = $associateId;
                $data_log['created_by'] = 3;
                $data_log['is_app'] = 1;
                $data_log['amount_deposit_by_id'] = $associateId;
            } else {
                $data_log['created_by_id'] = isset(Auth::user()->id) ? Auth::user()->id : 1;
                $data_log['created_by'] = (isset(Auth::user()->role_id) && Auth::user()->role_id == 3) ? 2 : 1;
            }
            //$data_log['created_at']=date("Y-m-d h:i:s", strtotime($payment_date));
            //$data_log['created_at']=$globaldate;
            if ($transaction_type == 16) {
                $data_log['created_at'] = date("Y-m-d", strtotime(convertDate($globaldate)));
            } else {
                $data_log['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($globaldate)));
            }
            $data_log['created_at_default'] = Carbon::now();
            // $data_log['created_at_default'] = date('Y-m-d H:i:s');
            $data_log['company_id'] = $companyId ?? 0;


            $transcation = \App\Models\Daybook::create($data_log);
            $tran_id = $transcation->id;
        }
        return $tran_id;
    }
    // public function bankChkbalance(Request $request)
    // {
    //     $globaldate = $request->entrydate;//05/06/2023
    //     $entry_date = date("Y-m-d", strtotime(convertDate($globaldate)));
    //     $bank_id_from_c = $request->bank_id;
    //     $companyId = $request->companyId;
    //     $bank_ac_id_from_c = $request->account_id;
    //     $startDate =($companyId==1)? date("Y-m-d", strtotime(convertDate('2021-08-05'))) : '';
    //     // $bankBla = \App\Models\BranchCurrentBalance::where('branch_id', $branch_id)->whereBetween('entry_date', ['2021-08-05', $entry_date])->sum('totalAmount');
    //     $bankBla = \App\Models\BankBalance::select('id', 'balance', 'bank_id')->where('bank_id', $bank_id_from_c)->where('account_id', $bank_ac_id_from_c)
    // 		->when($startDate != '',function($q) use($startDate){
    // 				$q->whereDate('entry_date','>=',$startDate);
    // 		})->where('entry_date', '<=', $entry_date)
    // 		->orderby('entry_date', 'desc')
    // 		->sum('totalAmount');

    // 	$balance = '0.00';
    //     $create_date = '';
    //     if ($bankBla) {
    //         $balance = number_format((float) $bankBla, 2, '.', '');
    //     }
    //     $getRecord = \App\Models\SamraddhBank::select('id', 'bank_name', 'created_at', 'company_id')->where('id', $bank_id_from_c)->whereCompanyId($companyId)->first();
    //     if ($getRecord) {
    //         $create_date = date("d/m/Y", strtotime($getRecord->created_at));
    //     }
    //     $return_array = compact('balance', 'create_date');
    //     return json_encode($return_array);
    // }

    // public function bankChkbalance(Request $request)
    // {
    //     $globaldate = $request->entrydate;
    //     $entry_date = date("Y-m-d", strtotime(convertDate($globaldate)));
    //     $bank_id_from_c = $request->bank_id;
    //     $companyId = $request->companyId;
    //     $bank_ac_id_from_c = $request->account_id;
    //     $bankBla = \App\Models\BankBalance::select('id', 'balance', 'bank_id')->where('bank_id', $bank_id_from_c)->where('account_id', $bank_ac_id_from_c)->whereDate('entry_date', '<=', $entry_date)->orderby('entry_date', 'desc')->sum('totalAmount');
    //     $balance = '0.00';
    //     $create_date = '';
    //     if ($bankBla) {
    //         $balance = number_format((float) $bankBla, 2, '.', '');
    //     }
    //     $getRecord = \App\Models\SamraddhBank::select('id', 'bank_name', 'created_at', 'company_id')->where('id', $bank_id_from_c)->whereCompanyId($companyId)->first();
    //     if ($getRecord) {
    //         $create_date = date("d/m/Y", strtotime($getRecord->created_at));
    //     }
    //     $return_array = compact('balance', 'create_date');
    //     return json_encode($return_array);
    // }
    public function bankChkbalance(Request $request)
    {
        $globaldate = $request->entrydate;
        $entry_date = date("Y-m-d", strtotime(convertDate($globaldate)));
        $bank_id_from_c = $request->bank_id;
        $companyId = $request->companyId;
        $bank_ac_id_from_c = $request->account_id;
        $bankBla = \App\Models\BankBalance::where('bank_id', $bank_id_from_c)->where('account_id', $bank_ac_id_from_c)->whereDate('entry_date', '<=', $entry_date);
        if (isset($request->company_id)) {
            $bankBla = $bankBla->where('company_id', $request->company_id);
        }
        $bankBla = $bankBla->orderby('entry_date', 'desc')->sum('totalAmount');
        $balance = '0.00';
        $create_date = '';
        if ($bankBla) {
            $balance = number_format((float) $bankBla, 2, '.', '');
        }
        $getRecord = \App\Models\SamraddhBank::select('id', 'bank_name', 'created_at', 'company_id')->where('id', $bank_id_from_c)->whereCompanyId($companyId)->first();
        if ($getRecord) {
            $create_date = date("d/m/Y", strtotime($getRecord->created_at));
        }
        $return_array = compact('balance', 'create_date');
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
        $bankBla = \App\Models\BranchCash::select('id', 'balance', 'loan_balance')->where('branch_id', $branch_id)->whereDate('entry_date', '<=', $entry_date)->orderby('entry_date', 'desc')->first();
        $balance = '0.00';
        if ($bankBla) {
            if ($request->daybook == 0) {
                $balance = number_format((float) $bankBla->balance, 2, '.', '');
            } else {
                $balance = number_format((float) $bankBla->loan_balance, 2, '.', '');
            }
        }
        $return_array = compact('balance');
        return json_encode($return_array);
    }
    /*-------------------------------------------------------------------------*/
    public static function checkCreateBranchCashDR($branch_id, $date, $amount, $type)
    {
        $entryDate = date("Y-m-d", strtotime(convertDate($date)));
        $entryTime = date("H:i:s");
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
    //---------------------------------------- back date --------------
    public static function checkCreateBankClosingDRBackDate($bank_id, $account_id, $date, $amount, $type)
    {
        $entryDate = date("Y-m-d", strtotime(convertDate($date)));
        $entryTime = date("H:i:s");
        $currentDateRecord = \App\Models\SamraddhBankClosing::where('bank_id', $bank_id)->where('account_id', $account_id)->whereDate('entry_date', $entryDate)->first();
        if ($currentDateRecord) {
            //print_r($entryDate);
            $Result = \App\Models\SamraddhBankClosing::find($currentDateRecord->id);
            $data['balance'] = $currentDateRecord->balance - $amount;
            //print_r($entryDate);die;
            $getNextrecord = \App\Models\SamraddhBankClosing::where('bank_id', $bank_id)->where('account_id', $account_id)->whereDate('entry_date', '>', $entryDate)->orderby('entry_date', 'ASC')->get();
            if ($getNextrecord) {
                foreach ($getNextrecord as $v) {
                    $ResultNext = \App\Models\SamraddhBankClosing::find($v->id);
                    if ($v->closing_balance > 0) {
                        $nextData['opening_balance'] = $v->opening_balance - $amount;
                        $nextData['balance'] = $v->balance - $amount;
                        $nextData['closing_balance'] = $v->closing_balance - $amount;
                    } else {
                        $nextData['opening_balance'] = $v->opening_balance - $amount;
                        $nextData['balance'] = $v->balance - $amount;
                    }
                    // print_r($nextData);die;
                    $ResultNext->update($nextData);
                }
                $data['closing_balance'] = $currentDateRecord->closing_balance - $amount;
            }
            $Result->update($data);
            $insertid = $currentDateRecord->id;
        } else {
            $oldDateRecord = \App\Models\SamraddhBankClosing::where('bank_id', $bank_id)->where('account_id', $account_id)->whereDate('entry_date', '<', $entryDate)->orderby('entry_date', 'DESC')->first();
            if ($oldDateRecord) {
                $Result1 = \App\Models\SamraddhBankClosing::find($oldDateRecord->id);
                $data1['closing_balance'] = $oldDateRecord->balance;
                $Result1->update($data1);
                $insertid1 = $oldDateRecord->id;
                $data['balance'] = $oldDateRecord->balance - $amount;
                $data['opening_balance'] = $oldDateRecord->balance;
                $data['bank_id'] = $bank_id;
                $data['account_id'] = $account_id;
                $data['entry_date'] = $entryDate;
                $data['entry_time'] = $entryTime;
                $data['type'] = $type;
                $getNextrecord1 = \App\Models\SamraddhBankClosing::where('bank_id', $bank_id)->where('account_id', $account_id)->whereDate('entry_date', '>', $entryDate)->orderby('entry_date', 'ASC')->get();
                if ($getNextrecord1) {
                    foreach ($getNextrecord1 as $v1) {
                        $ResultNext1 = \App\Models\SamraddhBankClosing::find($v1->id);
                        if ($v1->closing_balance > 0) {
                            $nextData1['opening_balance'] = $v1->opening_balance - $amount;
                            $nextData1['balance'] = $v1->balance - $amount;
                            $nextData1['closing_balance'] = $v1->closing_balance - $amount;
                        } else {
                            $nextData1['opening_balance'] = $v1->opening_balance - $amount;
                            $nextData1['balance'] = $v1->balance - $amount;
                        }
                        ///print_r($nextData1);die;
                        $ResultNext1->update($nextData1);
                    }
                    $data['closing_balance'] = $oldDateRecord->balance - $amount;
                }
                $data['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($date)));
                $transcation = \App\Models\SamraddhBankClosing::create($data);
                $insertid = $transcation->id;
            } else {
                if ($type == 0) {
                    $data['balance'] = $amount;
                } else {
                    $data['balance'] = 0;
                }
                $data['opening_balance'] = 0;
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
    public static function rentLedgerBackDateCR($rent_liability_id, $date, $amount)
    {
        $entryDate = date("Y-m-d", strtotime(convertDate($date)));
        $entryTime = date("H:i:s");
        $getNextrecord = \App\Models\RentLiabilityLedger::where('rent_liability_id', $rent_liability_id)->whereDate('created_at', '>', $entryDate)->orderby('created_at', 'ASC')->get();
        if ($getNextrecord) {
            foreach ($getNextrecord as $v1) {
                $ResultNext1 = \App\Models\RentLiabilityLedger::find($v1->id);
                $nextData1['opening_balance'] = $v1->opening_balance + $amount;
                //$nextData1['deposit']=$v1->deposit+$amount;
                ///print_r($nextData1);die;
                $ResultNext1->update($nextData1);
                $insertid = $ResultNext1->id;
            }
        }
        return true;
    }
    public static function SSBBackDateCR($ssbId, $date, $amount)
    {
        $entryDate = date("Y-m-d", strtotime(convertDate($date)));
        $entryTime = date("H:i:s");
        $getNextrecord = \App\Models\SavingAccountTranscation::where('saving_account_id', $ssbId)->whereDate('created_at', '>', $entryDate)->orderby('created_at', 'ASC')->get();
        if ($getNextrecord) {
            foreach ($getNextrecord as $v1) {
                $ResultNext1 = \App\Models\SavingAccountTranscation::find($v1->id);
                $nextData1['opening_balance'] = $v1->opening_balance + $amount;
                //$nextData1['deposit']=$v1->deposit+$amount;
                ///print_r($nextData1);die;
                $ResultNext1->update($nextData1);
                $insertid = $ResultNext1->id;
            }
        }
        return true;
    }
    /**
     *  Amount  deposit or withdrow in ssb account
     *
     * @param  $account_id,$account_no,$balance,$amount, $description,$currency_code,$payment_type,$payment_mode
     * @return \Illuminate\Http\Response
     */
    public static function SSBDateCR($account_id, $account_no, $balance, $amount, $description, $currency_code, $payment_type, $payment_mode, $branch_id, $associate_id, $type, $date, $daybook_ref_id = NULL, $company_id = NULL)
    {
        $entryDate = date("Y-m-d", strtotime(convertDate($date)));
        $entryTime = date("H:i:s");
        $ssbGet = \App\Models\SavingAccountTranscation::where('saving_account_id', $account_id)->whereDate('created_at', '<=', $entryDate)->orderby('created_at', 'DESC')->first();
        $dataSsb['saving_account_id'] = $account_id;
        $dataSsb['associate_id'] = $associate_id;
        $dataSsb['branch_id'] = $branch_id;
        $dataSsb['type'] = $type;
        $dataSsb['daybook_ref_id'] = $daybook_ref_id;
        $dataSsb['account_no'] = $account_no;
        if ($ssbGet) {
            $dataSsb['opening_balance'] = $ssbGet->opening_balance + $amount;
            $dataSsb['amount'] = $ssbGet->opening_balance + $amount;
        } else {
            $dataSsb['opening_balance'] = $amount;
            $dataSsb['amount'] = $amount;
        }
        $dataSsb['deposit'] = $amount;
        $dataSsb['description'] = $description;
        $dataSsb['currency_code'] = $currency_code;
        $dataSsb['payment_type'] = $payment_type;
        $dataSsb['payment_mode'] = $payment_mode;
        $dataSsb['created_at'] = $date;
        $dataSsb['company_id'] = $company_id;
        $resSsb = \App\Models\SavingAccountTranscation::create($dataSsb);
        // $ssbBalance = $balance-$amount;
        $ssbBalance = $balance + $amount;
        $sResult = \App\Models\SavingAccount::find($account_id);
        $sData['balance'] = $ssbBalance;
        $sResult->update($sData);
        //print_r($resSsb->id);die;
        return $resSsb->id;
    }
    public static function checkCreateBranchClosingBackdate($branch_id, $date, $amount, $type)
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
            $getNextrecord = \App\Models\BranchClosing::where('branch_id', $branch_id)->whereDate('entry_date', '>', $entryDate)->orderby('entry_date', 'ASC')->get();
            if ($getNextrecord) {
                foreach ($getNextrecord as $v) {
                    $ResultNext = \App\Models\BranchClosing::find($v->id);
                    if ($v->closing_balance > 0) {
                        /*if($type == 0){
                        $nextData['opening_balance']=$v->opening_balance+$amount;
                        $nextData['balance']=$v->balance+$amount;
                        $nextData['closing_balance']=$v->closing_balance+$amount;
                        }
                        if($type == 1){
                        $nextData['loan_opening_balance']=$v->loan_opening_balance+$amount;
                        $nextData['loan_balance']=$v->loan_balance+$amount;
                        $nextData['loan_closing_balance']=$v->loan_closing_balance+$amount;
                        }*/
                        $nextData['opening_balance'] = $v->opening_balance + $amount;
                        $nextData['balance'] = $v->balance + $amount;
                        $nextData['closing_balance'] = $v->closing_balance + $amount;
                    } else {
                        /*if($type == 0){
                        $nextData['opening_balance']=$v->opening_balance+$amount;
                        $nextData['balance']=$v->balance+$amount;
                        }
                        if($type == 1){
                        $nextData['loan_opening_balance']=$v->loan_opening_balance+$amount;
                        $nextData['loan_balance']=$v->loan_balance+$amount;
                        }*/
                        $nextData['opening_balance'] = $v->opening_balance + $amount;
                        $nextData['balance'] = $v->balance + $amount;
                    }
                    // print_r($nextData);die;
                    $ResultNext->update($nextData);
                }
                /*if($type == 0){
                $data['closing_balance']=$currentDateRecord->balance+$amount;
                }elseif($type == 1){
                $data['loan_closing_balance']=$currentDateRecord->loan_balance+$amount;
                }*/
                $data['closing_balance'] = $currentDateRecord->balance + $amount;
            }
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
                }
                else
                {
                $data['balance']=0;
                }
                if($type == 1){
                $data['loan_balance']=$oldDateRecord->loan_balance+$amount;
                }
                else
                {
                $data['loan_balance']=0;
                }*/
                $data['balance'] = $oldDateRecord->balance + $amount;
                $data['opening_balance'] = $oldDateRecord->balance;
                //$data['loan_opening_balance']=$oldDateRecord->loan_balance;
                $data['branch_id'] = $branch_id;
                $data['entry_date'] = $entryDate;
                $data['entry_time'] = $entryTime;
                $data['type'] = $type;
                $getNextrecord1 = \App\Models\BranchClosing::where('branch_id', $branch_id)->whereDate('entry_date', '>', $entryDate)->orderby('entry_date', 'ASC')->get();
                if ($getNextrecord1) {
                    foreach ($getNextrecord1 as $v1) {
                        $ResultNext1 = \App\Models\BranchClosing::find($v1->id);
                        if ($v1->closing_balance > 0) {
                            /*if($type == 0){
                            $nextData1['opening_balance']=$v1->opening_balance+$amount;
                            $nextData1['balance']=$v1->balance+$amount;
                            $nextData1['closing_balance']=$v1->closing_balance+$amount;
                            }
                            if($type == 1){
                            $nextData1['loan_opening_balance']=$v1->loan_opening_balance+$amount;
                            $nextData1['loan_balance']=$v1->loan_balance+$amount;
                            $nextData1['loan_closing_balance']=$v1->loan_closing_balance+$amount;
                            }*/
                            $nextData1['opening_balance'] = $v1->opening_balance + $amount;
                            $nextData1['balance'] = $v1->balance + $amount;
                            $nextData1['closing_balance'] = $v1->closing_balance + $amount;
                        } else {
                            /*if($type == 0){
                            $nextData1['opening_balance']=$v1->opening_balance+$amount;
                            $nextData1['balance']=$v1->balance+$amount;
                            }
                            if($type == 1){
                            $nextData1['loan_opening_balance']=$v1->loan_opening_balance+$amount;
                            $nextData1['loan_balance']=$v1->loan_balance+$amount;
                            }*/
                            $nextData1['opening_balance'] = $v1->opening_balance + $amount;
                            $nextData1['balance'] = $v1->balance + $amount;
                        }
                        ///print_r($nextData1);die;
                        $ResultNext1->update($nextData1);
                    }
                    /*if($type == 0){
                    $data['closing_balance']=$oldDateRecord->balance+$amount;
                    }
                    if($type == 1){
                    $data['loan_closing_balance']=$oldDateRecord->loan_balance+$amount;
                    }*/
                    $data['closing_balance'] = $oldDateRecord->balance + $amount;
                }
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
                $data['loan_opening_balance'] = 0;
                $data['branch_id'] = $branch_id;
                $data['entry_date'] = $entryDate;
                $data['entry_time'] = $entryTime;
                $data['type'] = $type;
                $getNextrecord1 = \App\Models\BranchClosing::where('branch_id', $branch_id)->whereDate('entry_date', '>', $entryDate)->orderby('entry_date', 'ASC')->get();
                if ($getNextrecord1) {
                    foreach ($getNextrecord1 as $v1) {
                        $ResultNext1 = \App\Models\BranchClosing::find($v1->id);
                        if ($v1->closing_balance > 0) {
                            /*if($type == 0){
                            $nextData1['opening_balance']=$v1->opening_balance+$amount;
                            $nextData1['balance']=$v1->balance+$amount;
                            $nextData1['closing_balance']=$v1->closing_balance+$amount;
                            }
                            if($type == 1){
                            $nextData1['loan_opening_balance']=$v1->loan_opening_balance+$amount;
                            $nextData1['loan_balance']=$v1->loan_balance+$amount;
                            $nextData1['loan_closing_balance']=$v1->loan_closing_balance+$amount;
                            }*/
                            $nextData1['opening_balance'] = $v1->opening_balance + $amount;
                            $nextData1['balance'] = $v1->balance + $amount;
                            $nextData1['closing_balance'] = $v1->closing_balance + $amount;
                        } else {
                            /*if($type == 0){
                            $nextData1['opening_balance']=$v1->opening_balance+$amount;
                            $nextData1['balance']=$v1->balance+$amount;
                            }
                            if($type == 1){
                            $nextData1['loan_opening_balance']=$v1->loan_opening_balance+$amount;
                            $nextData1['loan_balance']=$v1->loan_balance+$amount;
                            }*/
                            $nextData1['opening_balance'] = $v1->opening_balance + $amount;
                            $nextData1['balance'] = $v1->balance + $amount;
                        }
                        ///print_r($nextData1);die;
                        $ResultNext1->update($nextData1);
                    }
                    /*if($type == 0){
                    $data['closing_balance']=$amount;
                    }
                    if($type == 1){
                    $data['loan_closing_balance']=$amount;
                    }*/
                    $data['closing_balance'] = $amount;
                }
                $data['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($date)));
                $transcation = \App\Models\BranchClosing::create($data);
                $insertid = $transcation->id;
            }
        }
        return true;
    }
    public static function employeeLedgerBackDateCR($employee_id, $date, $amount)
    {
        $entryDate = date("Y-m-d", strtotime(convertDate($date)));
        $entryTime = date("H:i:s");
        $getNextrecord = \App\Models\EmployeeLedger::where('employee_id', $employee_id)->whereDate('created_at', '>', $entryDate)->orderby('created_at', 'ASC')->get();
        if ($getNextrecord) {
            foreach ($getNextrecord as $v1) {
                $ResultNext1 = \App\Models\EmployeeLedger::find($v1->id);
                $nextData1['opening_balance'] = $v1->opening_balance + $amount;
                //$nextData1['deposit']=$v1->deposit+$amount;
                ///print_r($nextData1);die;
                $ResultNext1->update($nextData1);
                $insertid = $ResultNext1->id;
            }
        }
        return true;
    }
    public static function checkCreateBranchClosingBackdateDR($branch_id, $date, $amount, $type)
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
            $getNextrecord = \App\Models\BranchClosing::where('branch_id', $branch_id)->whereDate('entry_date', '>', $entryDate)->orderby('entry_date', 'ASC')->get();
            if ($getNextrecord) {
                foreach ($getNextrecord as $v) {
                    $ResultNext = \App\Models\BranchClosing::find($v->id);
                    if ($v->closing_balance > 0) {
                        /*if($type == 0){
                        $nextData['opening_balance']=$v->opening_balance-$amount;
                        $nextData['balance']=$v->balance-$amount;
                        $nextData['closing_balance']=$v->closing_balance-$amount;
                        }
                        if($type == 1){
                        $nextData['loan_opening_balance']=$v->loan_opening_balance-$amount;
                        $nextData['loan_balance']=$v->loan_balance-$amount;
                        $nextData['loan_closing_balance']=$v->loan_closing_balance-$amount;
                        }*/
                        $nextData['opening_balance'] = $v->opening_balance - $amount;
                        $nextData['balance'] = $v->balance - $amount;
                        $nextData['closing_balance'] = $v->closing_balance - $amount;
                    } else {
                        /*if($type == 0){
                        $nextData['opening_balance']=$v->opening_balance-$amount;
                        $nextData['balance']=$v->balance-$amount;
                        }
                        if($type == 1){
                        $nextData['loan_opening_balance']=$v->loan_opening_balance-$amount;
                        $nextData['loan_balance']=$v->loan_balance-$amount;
                        }*/
                        $nextData['opening_balance'] = $v->opening_balance - $amount;
                        $nextData['balance'] = $v->balance - $amount;
                    }
                    // print_r($nextData);die;
                    $ResultNext->update($nextData);
                }
                /*if($type == 0){
                $data['closing_balance']=$currentDateRecord->balance-$amount;
                }elseif($type == 1){
                $data['loan_closing_balance']=$currentDateRecord->loan_balance-$amount;
                }*/
                $data['closing_balance'] = $currentDateRecord->balance - $amount;
            }
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
                }
                else
                {
                $data['balance']=0;
                }*/
                $data['balance'] = $oldDateRecord->balance - $amount;
                /*if($type == 1){
                $data['loan_balance']=$oldDateRecord->loan_balance-$amount;
                }
                else
                {
                $data['loan_balance']=0;
                }*/
                $data['opening_balance'] = $oldDateRecord->balance;
                //$data['loan_opening_balance']=$oldDateRecord->loan_balance;
                $data['branch_id'] = $branch_id;
                $data['entry_date'] = $entryDate;
                $data['entry_time'] = $entryTime;
                $data['type'] = $type;
                $getNextrecord1 = \App\Models\BranchClosing::where('branch_id', $branch_id)->whereDate('entry_date', '>', $entryDate)->orderby('entry_date', 'ASC')->get();
                if ($getNextrecord1) {
                    foreach ($getNextrecord1 as $v1) {
                        $ResultNext1 = \App\Models\BranchClosing::find($v1->id);
                        if ($v1->closing_balance > 0) {
                            /*if($type == 0){
                            $nextData1['opening_balance']=$v1->opening_balance-$amount;
                            $nextData1['balance']=$v1->balance-$amount;
                            $nextData1['closing_balance']=$v1->closing_balance-$amount;
                            }
                            if($type == 1){
                            $nextData1['loan_opening_balance']=$v1->loan_opening_balance-$amount;
                            $nextData1['loan_balance']=$v1->loan_balance-$amount;
                            $nextData1['loan_closing_balance']=$v1->loan_closing_balance-$amount;
                            }*/
                            $nextData1['opening_balance'] = $v1->opening_balance - $amount;
                            $nextData1['balance'] = $v1->balance - $amount;
                            $nextData1['closing_balance'] = $v1->closing_balance - $amount;
                        } else {
                            /*if($type == 0){
                            $nextData1['opening_balance']=$v1->opening_balance-$amount;
                            $nextData1['balance']=$v1->balance-$amount;
                            }
                            if($type == 1){
                            $nextData1['loan_opening_balance']=$v1->loan_opening_balance-$amount;
                            $nextData1['loan_balance']=$v1->loan_balance-$amount;
                            }*/
                            $nextData1['opening_balance'] = $v1->opening_balance - $amount;
                            $nextData1['balance'] = $v1->balance - $amount;
                        }
                        ///print_r($nextData1);die;
                        $ResultNext1->update($nextData1);
                    }
                    /*if($type == 0){
                    $data['closing_balance']=$oldDateRecord->balance-$amount;
                    }
                    if($type == 1){
                    $data['loan_closing_balance']=$oldDateRecord->loan_balance-$amount;
                    }*/
                    $data['closing_balance'] = $oldDateRecord->balance - $amount;
                }
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
                //$data['loan_opening_balance']=0;
                $data['branch_id'] = $branch_id;
                $data['entry_date'] = $entryDate;
                $data['entry_time'] = $entryTime;
                $data['type'] = $type;
                $getNextrecord1 = \App\Models\BranchClosing::where('branch_id', $branch_id)->whereDate('entry_date', '>', $entryDate)->orderby('entry_date', 'ASC')->get();
                if ($getNextrecord1) {
                    foreach ($getNextrecord1 as $v1) {
                        $ResultNext1 = \App\Models\BranchClosing::find($v1->id);
                        if ($v1->closing_balance > 0) {
                            /*if($type == 0){
                            $nextData1['opening_balance']=$v1->opening_balance-$amount;
                            $nextData1['balance']=$v1->balance-$amount;
                            $nextData1['closing_balance']=$v1->closing_balance-$amount;
                            }
                            if($type == 1){
                            $nextData1['loan_opening_balance']=$v1->loan_opening_balance-$amount;
                            $nextData1['loan_balance']=$v1->loan_balance-$amount;
                            $nextData1['loan_closing_balance']=$v1->loan_closing_balance-$amount;
                            }*/
                            $nextData1['opening_balance'] = $v1->opening_balance - $amount;
                            $nextData1['balance'] = $v1->balance - $amount;
                            $nextData1['closing_balance'] = $v1->closing_balance - $amount;
                        } else {
                            /*if($type == 0){
                            $nextData1['opening_balance']=$v1->opening_balance-$amount;
                            $nextData1['balance']=$v1->balance-$amount;
                            }
                            if($type == 1){
                            $nextData1['loan_opening_balance']=$v1->loan_opening_balance-$amount;
                            $nextData1['loan_balance']=$v1->loan_balance-$amount;
                            }*/
                            $nextData1['opening_balance'] = $v1->opening_balance - $amount;
                            $nextData1['balance'] = $v1->balance - $amount;
                        }
                        ///print_r($nextData1);die;
                        $ResultNext1->update($nextData1);
                    }
                    /*if($type == 0){
                    $data['closing_balance']=$amount;
                    }
                    if($type == 1){
                    $data['loan_closing_balance']=$amount;
                    }*/
                    $data['closing_balance'] = $amount;
                }
                $data['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($date)));
                $transcation = \App\Models\BranchClosing::create($data);
                $insertid = $transcation->id;
            }
        }
        return true;
    }
    public static function createBaranchCashBackDateCR($branch_id, $date, $amount, $type)
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
            $getNextrecord = \App\Models\BranchCash::where('branch_id', $branch_id)->whereDate('entry_date', '>', $entryDate)->orderby('entry_date', 'ASC')->get();
            if ($getNextrecord) {
                foreach ($getNextrecord as $v) {
                    $ResultNext = \App\Models\BranchCash::find($v->id);
                    if ($v->closing_balance > 0) {
                        /*if($type == 0){
                        $nextData['opening_balance']=$v->opening_balance+$amount;
                        $nextData['balance']=$v->balance+$amount;
                        $nextData['closing_balance']=$v->closing_balance+$amount;
                        }
                        if($type == 1){
                        $nextData['loan_opening_balance']=$v->loan_opening_balance+$amount;
                        $nextData['loan_balance']=$v->loan_balance+$amount;
                        $nextData['loan_closing_balance']=$v->loan_closing_balance+$amount;
                        }*/
                        $nextData['opening_balance'] = $v->opening_balance + $amount;
                        $nextData['balance'] = $v->balance + $amount;
                        $nextData['closing_balance'] = $v->closing_balance + $amount;
                    } else {
                        /*if($type == 0){
                        $nextData['opening_balance']=$v->opening_balance+$amount;
                        $nextData['balance']=$v->balance+$amount;
                        }
                        if($type == 1){
                        $nextData['loan_opening_balance']=$v->loan_opening_balance+$amount;
                        $nextData['loan_balance']=$v->loan_balance+$amount;
                        }*/
                        $nextData['opening_balance'] = $v->opening_balance + $amount;
                        $nextData['balance'] = $v->balance + $amount;
                    }
                    // print_r($nextData);die;
                    $ResultNext->update($nextData);
                }
                /*if($type == 0){
                $data['closing_balance']=$currentDateRecord->balance+$amount;
                }elseif($type == 1){
                $data['loan_closing_balance']=$currentDateRecord->loan_balance+$amount;
                }*/
                $data['closing_balance'] = $currentDateRecord->balance + $amount;
            }
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
                }
                else
                {
                $data['balance']=0;
                }
                if($type == 1){
                $data['loan_balance']=$oldDateRecord->loan_balance+$amount;
                }
                else
                {
                $data['loan_balance']=0;
                }*/
                $data['balance'] = $oldDateRecord->balance + $amount;
                $data['opening_balance'] = $oldDateRecord->balance;
                //$data['loan_opening_balance']=$oldDateRecord->loan_balance;
                $data['branch_id'] = $branch_id;
                $data['entry_date'] = $entryDate;
                $data['entry_time'] = $entryTime;
                $data['type'] = $type;
                $getNextrecord1 = \App\Models\BranchCash::where('branch_id', $branch_id)->whereDate('entry_date', '>', $entryDate)->orderby('entry_date', 'ASC')->get();
                if ($getNextrecord1) {
                    foreach ($getNextrecord1 as $v1) {
                        $ResultNext1 = \App\Models\BranchCash::find($v1->id);
                        if ($v1->closing_balance > 0) {
                            /*if($type == 0){
                            $nextData1['opening_balance']=$v1->opening_balance+$amount;
                            $nextData1['balance']=$v1->balance+$amount;
                            $nextData1['closing_balance']=$v1->closing_balance+$amount;
                            }
                            if($type == 1){
                            $nextData1['loan_opening_balance']=$v1->loan_opening_balance+$amount;
                            $nextData1['loan_balance']=$v1->loan_balance+$amount;
                            $nextData1['loan_closing_balance']=$v1->loan_closing_balance+$amount;
                            }*/
                            $nextData1['opening_balance'] = $v1->opening_balance + $amount;
                            $nextData1['balance'] = $v1->balance + $amount;
                            $nextData1['closing_balance'] = $v1->closing_balance + $amount;
                        } else {
                            /*if($type == 0){
                            $nextData1['opening_balance']=$v1->opening_balance+$amount;
                            $nextData1['balance']=$v1->balance+$amount;
                            }
                            if($type == 1){
                            $nextData1['loan_opening_balance']=$v1->loan_opening_balance+$amount;
                            $nextData1['loan_balance']=$v1->loan_balance+$amount;
                            }*/
                            $nextData1['opening_balance'] = $v1->opening_balance + $amount;
                            $nextData1['balance'] = $v1->balance + $amount;
                        }
                        ///print_r($nextData1);die;
                        $ResultNext1->update($nextData1);
                    }
                    /*if($type == 0){
                    $data['closing_balance']=$oldDateRecord->balance+$amount;
                    }
                    if($type == 1){
                    $data['loan_closing_balance']=$oldDateRecord->loan_balance+$amount;
                    }*/
                    $data['closing_balance'] = $oldDateRecord->balance + $amount;
                }
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
                $data['loan_opening_balance'] = 0;
                $data['branch_id'] = $branch_id;
                $data['entry_date'] = $entryDate;
                $data['entry_time'] = $entryTime;
                $data['type'] = $type;
                $getNextrecord1 = \App\Models\BranchCash::where('branch_id', $branch_id)->whereDate('entry_date', '>', $entryDate)->orderby('entry_date', 'ASC')->get();
                if ($getNextrecord1) {
                    foreach ($getNextrecord1 as $v1) {
                        $ResultNext1 = \App\Models\BranchCash::find($v1->id);
                        if ($v1->closing_balance > 0) {
                            /*if($type == 0){
                            $nextData1['opening_balance']=$v1->opening_balance+$amount;
                            $nextData1['balance']=$v1->balance+$amount;
                            $nextData1['closing_balance']=$v1->closing_balance+$amount;
                            }
                            if($type == 1){
                            $nextData1['loan_opening_balance']=$v1->loan_opening_balance+$amount;
                            $nextData1['loan_balance']=$v1->loan_balance+$amount;
                            $nextData1['loan_closing_balance']=$v1->loan_closing_balance+$amount;
                            }*/
                            $nextData1['opening_balance'] = $v1->opening_balance + $amount;
                            $nextData1['balance'] = $v1->balance + $amount;
                            $nextData1['closing_balance'] = $v1->closing_balance + $amount;
                        } else {
                            /*if($type == 0){
                            $nextData1['opening_balance']=$v1->opening_balance+$amount;
                            $nextData1['balance']=$v1->balance+$amount;
                            }
                            if($type == 1){
                            $nextData1['loan_opening_balance']=$v1->loan_opening_balance+$amount;
                            $nextData1['loan_balance']=$v1->loan_balance+$amount;
                            }*/
                            $nextData1['opening_balance'] = $v1->opening_balance + $amount;
                            $nextData1['balance'] = $v1->balance + $amount;
                        }
                        ///print_r($nextData1);die;
                        $ResultNext1->update($nextData1);
                    }
                    /*if($type == 0){
                    $data['closing_balance']=$amount;
                    }
                    if($type == 1){
                    $data['loan_closing_balance']=$amount;
                    }*/
                    $data['closing_balance'] = $amount;
                }
                $data['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($date)));
                $transcation = \App\Models\BranchCash::create($data);
                $insertid = $transcation->id;
            }
        }
        return true;
    }
    public static function createBaranchCashBackDateDR($branch_id, $date, $amount, $type)
    {
        $entryDate = date("Y-m-d", strtotime(convertDate($date)));
        $entryTime = date("H:i:s");
        $currentDateRecord = \App\Models\BranchCash::where('branch_id', $branch_id)->whereDate('entry_date', $entryDate)->first();
        if ($currentDateRecord) {
            $Result = \App\Models\BranchCash::find($currentDateRecord->id);
            /*if($type == 0){
            $data['balance']=$currentDateRecord->balance-$amount;
            }elseif($type == 1){
            $data['loan_balance']=$currentDateRecord->loan_balance-$amount;
            } */
            $data['balance'] = $currentDateRecord->balance - $amount;
            $data['updated_at'] = $date;
            $getNextrecord = \App\Models\BranchCash::where('branch_id', $branch_id)->whereDate('entry_date', '>', $entryDate)->orderby('entry_date', 'ASC')->get();
            if ($getNextrecord) {
                foreach ($getNextrecord as $v) {
                    $ResultNext = \App\Models\BranchCash::find($v->id);
                    if ($v->closing_balance > 0) {
                        /*if($type == 0){
                        $nextData['opening_balance']=$v->opening_balance-$amount;
                        $nextData['balance']=$v->balance-$amount;
                        $nextData['closing_balance']=$v->closing_balance-$amount;
                        }
                        if($type == 1){
                        $nextData['loan_opening_balance']=$v->loan_opening_balance-$amount;
                        $nextData['loan_balance']=$v->loan_balance-$amount;
                        $nextData['loan_closing_balance']=$v->loan_closing_balance-$amount;
                        }*/
                        $nextData['opening_balance'] = $v->opening_balance - $amount;
                        $nextData['balance'] = $v->balance - $amount;
                        $nextData['closing_balance'] = $v->closing_balance - $amount;
                    } else {
                        /*if($type == 0){
                        $nextData['opening_balance']=$v->opening_balance-$amount;
                        $nextData['balance']=$v->balance-$amount;
                        }
                        if($type == 1){
                        $nextData['loan_opening_balance']=$v->loan_opening_balance-$amount;
                        $nextData['loan_balance']=$v->loan_balance-$amount;
                        }*/
                        $nextData['opening_balance'] = $v->opening_balance - $amount;
                        $nextData['balance'] = $v->balance - $amount;
                    }
                    // print_r($nextData);die;
                    $ResultNext->update($nextData);
                }
                /*if($type == 0){
                $data['closing_balance']=$currentDateRecord->balance-$amount;
                }elseif($type == 1){
                $data['loan_closing_balance']=$currentDateRecord->loan_balance-$amount;
                }*/
                $data['closing_balance'] = $currentDateRecord->balance - $amount;
            }
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
                }
                else
                {
                $data['balance']=0;
                }
                if($type == 1){
                $data['loan_balance']=$oldDateRecord->loan_balance-$amount;
                }
                else
                {
                $data['loan_balance']=0;
                }*/
                $data['balance'] = $oldDateRecord->balance - $amount;
                $data['opening_balance'] = $oldDateRecord->balance;
                //$data['loan_opening_balance']=$oldDateRecord->loan_balance;
                $data['branch_id'] = $branch_id;
                $data['entry_date'] = $entryDate;
                $data['entry_time'] = $entryTime;
                $data['type'] = $type;
                $getNextrecord1 = \App\Models\BranchCash::where('branch_id', $branch_id)->whereDate('entry_date', '>', $entryDate)->orderby('entry_date', 'ASC')->get();
                if ($getNextrecord1) {
                    foreach ($getNextrecord1 as $v1) {
                        $ResultNext1 = \App\Models\BranchCash::find($v1->id);
                        if ($v1->closing_balance > 0) {
                            /*if($type == 0){
                            $nextData1['opening_balance']=$v1->opening_balance-$amount;
                            $nextData1['balance']=$v1->balance-$amount;
                            $nextData1['closing_balance']=$v1->closing_balance-$amount;
                            }
                            if($type == 1){
                            $nextData1['loan_opening_balance']=$v1->loan_opening_balance-$amount;
                            $nextData1['loan_balance']=$v1->loan_balance-$amount;
                            $nextData1['loan_closing_balance']=$v1->loan_closing_balance-$amount;
                            }*/
                            $nextData1['opening_balance'] = $v1->opening_balance - $amount;
                            $nextData1['balance'] = $v1->balance - $amount;
                            $nextData1['closing_balance'] = $v1->closing_balance - $amount;
                        } else {
                            /*if($type == 0){
                            $nextData1['opening_balance']=$v1->opening_balance-$amount;
                            $nextData1['balance']=$v1->balance-$amount;
                            }
                            if($type == 1){
                            $nextData1['loan_opening_balance']=$v1->loan_opening_balance-$amount;
                            $nextData1['loan_balance']=$v1->loan_balance-$amount;
                            }*/
                            $nextData1['opening_balance'] = $v1->opening_balance - $amount;
                            $nextData1['balance'] = $v1->balance - $amount;
                        }
                        ///print_r($nextData1);die;
                        $ResultNext1->update($nextData1);
                    }
                    /*if($type == 0){
                    $data['closing_balance']=$oldDateRecord->balance-$amount;
                    }
                    if($type == 1){
                    $data['loan_closing_balance']=$oldDateRecord->loan_balance-$amount;
                    }*/
                    $data['closing_balance'] = $oldDateRecord->balance - $amount;
                }
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
                //$data['loan_opening_balance']=0;
                $data['branch_id'] = $branch_id;
                $data['entry_date'] = $entryDate;
                $data['entry_time'] = $entryTime;
                $data['type'] = $type;
                $getNextrecord1 = \App\Models\BranchCash::where('branch_id', $branch_id)->whereDate('entry_date', '>', $entryDate)->orderby('entry_date', 'ASC')->get();
                if ($getNextrecord1) {
                    foreach ($getNextrecord1 as $v1) {
                        $ResultNext1 = \App\Models\BranchCash::find($v1->id);
                        if ($v1->closing_balance > 0) {
                            /*if($type == 0){
                            $nextData1['opening_balance']=$v1->opening_balance-$amount;
                            $nextData1['balance']=$v1->balance-$amount;
                            $nextData1['closing_balance']=$v1->closing_balance-$amount;
                            }
                            if($type == 1){
                            $nextData1['loan_opening_balance']=$v1->loan_opening_balance-$amount;
                            $nextData1['loan_balance']=$v1->loan_balance-$amount;
                            $nextData1['loan_closing_balance']=$v1->loan_closing_balance-$amount;
                            }*/
                            $nextData1['opening_balance'] = $v1->opening_balance - $amount;
                            $nextData1['balance'] = $v1->balance - $amount;
                            $nextData1['closing_balance'] = $v1->closing_balance - $amount;
                        } else {
                            /*if($type == 0){
                            $nextData1['opening_balance']=$v1->opening_balance-$amount;
                            $nextData1['balance']=$v1->balance-$amount;
                            }
                            if($type == 1){
                            $nextData1['loan_opening_balance']=$v1->loan_opening_balance-$amount;
                            $nextData1['loan_balance']=$v1->loan_balance-$amount;
                            }*/
                            $nextData1['opening_balance'] = $v1->opening_balance - $amount;
                            $nextData1['balance'] = $v1->balance - $amount;
                        }
                        ///print_r($nextData1);die;
                        $ResultNext1->update($nextData1);
                    }
                    /*if($type == 0){
                    $data['closing_balance']=$amount;
                    }
                    if($type == 1){
                    $data['loan_closing_balance']=$amount;
                    }*/
                    $data['closing_balance'] = $amount;
                }
                $data['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($date)));
                $transcation = \App\Models\BranchCash::create($data);
                $insertid = $transcation->id;
            }
        }
        return true;
    }
    /**
     *  Amount  deposit or withdrow in ssb account
     *
     * @param  $account_id,$account_no,$balance,$amount, $description,$currency_code,$payment_type,$payment_mode
     * @return \Illuminate\Http\Response
     */
    public static function SSBDateDR($account_id, $account_no, $balance, $amount, $description, $currency_code, $payment_type, $payment_mode, $branch_id, $associate_id, $type, $date)
    {
        $entryDate = date("Y-m-d", strtotime(convertDate($date)));
        $entryTime = date("H:i:s");
        $ssbGet = \App\Models\SavingAccountTranscation::where('saving_account_id', $account_id)->whereDate('created_at', '<=', $entryDate)->orderby('created_at', 'DESC')->first();
        $dataSsb['saving_account_id'] = $account_id;
        $dataSsb['associate_id'] = $associate_id;
        $dataSsb['branch_id'] = $branch_id;
        $dataSsb['type'] = $type;
        $dataSsb['account_no'] = $account_no;
        if ($ssbGet) {
            $dataSsb['opening_balance'] = $ssbGet->opening_balance - $amount;
            $dataSsb['amount'] = $ssbGet->opening_balance - $amount;
        } else {
            $dataSsb['opening_balance'] = $amount;
            $dataSsb['amount'] = $amount;
        }
        $dataSsb['withdrawal'] = $amount;
        $dataSsb['description'] = $description;
        $dataSsb['currency_code'] = $currency_code;
        $dataSsb['payment_type'] = $payment_type;
        $dataSsb['payment_mode'] = $payment_mode;
        $dataSsb['created_at'] = $date;
        $resSsb = \App\Models\SavingAccountTranscation::create($dataSsb);
        // $ssbBalance = $balance-$amount;
        $ssbBalance = $balance - $amount;
        $sResult = \App\Models\SavingAccount::find($account_id);
        $sData['balance'] = $ssbBalance;
        $sResult->update($sData);
        //print_r($resSsb->id);die;
        return $resSsb->id;
    }
    public static function SSBBackDateDR($ssbId, $date, $amount)
    {
        $entryDate = date("Y-m-d", strtotime(convertDate($date)));
        $entryTime = date("H:i:s");
        $getNextrecord = \App\Models\SavingAccountTranscation::where('saving_account_id', $ssbId)->whereDate('created_at', '>', $entryDate)->orderby('created_at', 'ASC')->get();
        if ($getNextrecord) {
            foreach ($getNextrecord as $v1) {
                $ResultNext1 = \App\Models\SavingAccountTranscation::find($v1->id);
                $nextData1['opening_balance'] = $v1->opening_balance - $amount;
                $ResultNext1->update($nextData1);
                $insertid = $ResultNext1->id;
            }
        }
        return true;
    }
    public static function checkCreateBankClosingCRBackDate($bank_id, $account_id, $date, $amount, $type)
    {
        $entryDate = date("Y-m-d", strtotime(convertDate($date)));
        $entryTime = date("H:i:s");
        $currentDateRecord = \App\Models\SamraddhBankClosing::where('bank_id', $bank_id)->where('account_id', $account_id)->whereDate('entry_date', $entryDate)->first();
        if ($currentDateRecord) {
            //print_r($entryDate);
            $Result = \App\Models\SamraddhBankClosing::find($currentDateRecord->id);
            $data['balance'] = $currentDateRecord->balance + $amount;
            //print_r($entryDate);die;
            $getNextrecord = \App\Models\SamraddhBankClosing::where('bank_id', $bank_id)->where('account_id', $account_id)->whereDate('entry_date', '>', $entryDate)->orderby('entry_date', 'ASC')->get();
            if ($getNextrecord) {
                foreach ($getNextrecord as $v) {
                    $ResultNext = \App\Models\SamraddhBankClosing::find($v->id);
                    if ($v->closing_balance > 0) {
                        $nextData['opening_balance'] = $v->opening_balance + $amount;
                        $nextData['balance'] = $v->balance + $amount;
                        $nextData['closing_balance'] = $v->closing_balance + $amount;
                    } else {
                        $nextData['opening_balance'] = $v->opening_balance + $amount;
                        $nextData['balance'] = $v->balance + $amount;
                    }
                    // print_r($nextData);die;
                    $ResultNext->update($nextData);
                }
                $data['closing_balance'] = $currentDateRecord->closing_balance + $amount;
            }
            $Result->update($data);
            $insertid = $currentDateRecord->id;
        } else {
            $oldDateRecord = \App\Models\SamraddhBankClosing::where('bank_id', $bank_id)->where('account_id', $account_id)->whereDate('entry_date', '<', $entryDate)->orderby('entry_date', 'DESC')->first();
            if ($oldDateRecord) {
                $Result1 = \App\Models\SamraddhBankClosing::find($oldDateRecord->id);
                $data1['closing_balance'] = $oldDateRecord->balance;
                $Result1->update($data1);
                $insertid1 = $oldDateRecord->id;
                $data['balance'] = $oldDateRecord->balance + $amount;
                $data['opening_balance'] = $oldDateRecord->balance;
                $data['bank_id'] = $bank_id;
                $data['account_id'] = $account_id;
                $data['entry_date'] = $entryDate;
                $data['entry_time'] = $entryTime;
                $data['type'] = $type;
                $getNextrecord1 = \App\Models\SamraddhBankClosing::where('bank_id', $bank_id)->where('account_id', $account_id)->whereDate('entry_date', '>', $entryDate)->orderby('entry_date', 'ASC')->get();
                if ($getNextrecord1) {
                    foreach ($getNextrecord1 as $v1) {
                        $ResultNext1 = \App\Models\SamraddhBankClosing::find($v1->id);
                        if ($v1->closing_balance > 0) {
                            $nextData1['opening_balance'] = $v1->opening_balance + $amount;
                            $nextData1['balance'] = $v1->balance + $amount;
                            $nextData1['closing_balance'] = $v1->closing_balance + $amount;
                        } else {
                            $nextData1['opening_balance'] = $v1->opening_balance - $amount;
                            $nextData1['balance'] = $v1->balance + $amount;
                        }
                        ///print_r($nextData1);die;
                        $ResultNext1->update($nextData1);
                    }
                    $data['closing_balance'] = $oldDateRecord->balance + $amount;
                }
                $data['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($date)));
                $transcation = \App\Models\SamraddhBankClosing::create($data);
                $insertid = $transcation->id;
            } else {
                if ($type == 0) {
                    $data['balance'] = $amount;
                } else {
                    $data['balance'] = 0;
                }
                $data['opening_balance'] = 0;
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
    // --------------------------------- back date ----------------------------
    public function ssbDateBalanceChk(Request $request)
    {
        $globaldate = $request->entrydate;
        $entry_date = date("Y-m-d", strtotime(convertDate($globaldate)));
        $ssbid = $request->ssbid;
        $bankBlaCR = \App\Models\SavingAccountTranscation::where('saving_account_id', $ssbid)->where('payment_type', 'CR')->whereDate('created_at', '<=', $entry_date)->orderby('created_at', 'desc')->sum('deposit');
        $bankBlaDR = \App\Models\SavingAccountTranscation::where('saving_account_id', $ssbid)->where('payment_type', 'DR')->whereDate('created_at', '<=', $entry_date)->orderby('created_at', 'desc')->sum('withdrawal');
        $bankBla = $bankBlaCR - $bankBlaDR;
        $balance = '0.00';
        if ($bankBla > 0) {
            $balance = number_format((float) $bankBla, 2, '.', '');
        }
        $return_array = compact('balance');
        return json_encode($return_array);
    }
    public function directorBalanceDate(Request $request)
    {
        $globaldate = $request->entrydate;
        $entry_date = date("Y-m-d", strtotime(convertDate($globaldate)));
        $id = $request->id;
        $dataDirector = \App\Models\ShareHolder::where('id', $id)->first();
        $headGet = \App\Models\AccountHeads::where('head_id', $dataDirector->head_id)->first();
        $label = 'head' . $headGet->labels;
        $bankBlaCR = \App\Models\AllHeadTransaction::where('head_id', $dataDirector->head_id)->where('payment_type', 'CR')->whereDate('entry_date', '<=', $entry_date)->orderby('entry_date', 'desc')->sum('amount');
        $bankBlaDR = \App\Models\AllHeadTransaction::where('head_id', $dataDirector->head_id)->where('payment_type', 'DR')->whereDate('entry_date', '<=', $entry_date)->orderby('entry_date', 'desc')->sum('amount');
        $bankBla = $bankBlaCR - $bankBlaDR;
        $balance = '0.00';
        if ($bankBla > 0) {
            $balance = number_format((float) $bankBla, 2, '.', '');
        }
        $return_array = compact('balance');
        return json_encode($return_array);
    }
    /********************************New field add Start **************/
    /**
     *  create branch day book
     *
     * @param  $amount,$entry_date,$entry_time,$created_at
     * @return \Illuminate\Http\Response
     */
    public static function branchDaybookCreateType($daybook_ref_id, $branch_id, $type, $sub_type, $type_id, $associate_id, $member_id, $branch_id_to, $branch_id_from, $opening_balance, $amount, $closing_balance, $description, $description_dr, $description_cr, $payment_type, $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $is_contra, $contra_id, $created_at, $updated_at, $type_transaction_id, $ssb_account_id_to, $cheque_bank_from_id, $cheque_bank_ac_from_id, $cheque_bank_to_name, $cheque_bank_to_branch, $cheque_bank_to_ac_no, $cheque_bank_to_ifsc, $transction_bank_from_id, $transction_bank_from_ac_id, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc, $amount_type)
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
        $data['created_by'] = $created_by;
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
        $data['amount_type'] = $amount_type;
        $transcation = \App\Models\BranchDaybook::create($data);
        return $transcation->id;
    }
    /**
     *  Head New table entry
     *
     * @param
     * @return \Illuminate\Http\Response
     */
    // public static function headTransactionCreate($daybook_ref_id, $branch_id, $bank_id, $bank_ac_id, $head_id, $type, $sub_type, $type_id, $associate_id, $member_id, $branch_id_to, $branch_id_from, $opening_balance, $amount, $closing_balance, $description, $payment_type, $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $type_transaction_id, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $cheque_bank_to_name, $cheque_bank_to_branch, $cheque_bank_to_ac_no, $cheque_bank_to_ifsc, $transction_bank_from_id, $transction_bank_from_ac_id, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc, $cheque_bank_from_id, $cheque_bank_ac_from_id)
    // {
    //     $data['daybook_ref_id'] = $daybook_ref_id;
    //     $data['branch_id'] = $branch_id;
    //     $data['bank_id'] = $bank_id;
    //     $data['bank_ac_id'] = $bank_ac_id;
    //     $data['head_id'] = $head_id;
    //     $data['type'] = $type;
    //     $data['sub_type'] = $sub_type;
    //     $data['type_id'] = $type_id;
    //     $data['type_transaction_id'] = $type_transaction_id;
    //     $data['associate_id'] = $associate_id;
    //     $data['member_id'] = $member_id;
    //     $data['branch_id_to'] = $branch_id_to;
    //     $data['branch_id_from'] = $branch_id_from;
    //     $data['opening_balance'] = $opening_balance;
    //     $data['amount'] = $amount;
    //     $data['closing_balance'] = $closing_balance;
    //     $data['description'] = $description;
    //     $data['payment_type'] = $payment_type;
    //     $data['payment_mode'] = $payment_mode;
    //     $data['currency_code'] = $currency_code;
    //     $data['amount_to_id'] = $amount_to_id;
    //     $data['amount_to_name'] = $amount_to_name;
    //     $data['amount_from_id'] = $amount_from_id;
    //     $data['amount_from_name'] = $amount_from_name;
    //     $data['jv_unique_id'] = $jv_unique_id;
    //     $data['v_no'] = $v_no;
    //     $data['v_date'] = $v_date;
    //     $data['ssb_account_id_from'] = $ssb_account_id_from;
    //     $data['ssb_account_id_to'] = $ssb_account_id_to;
    //     $data['ssb_account_tran_id_to'] = $ssb_account_tran_id_to;
    //     $data['ssb_account_tran_id_from'] = $ssb_account_tran_id_from;
    //     $data['cheque_type'] = $cheque_type;
    //     $data['cheque_id'] = $cheque_id;
    //     $data['cheque_no'] = $cheque_no;
    //     $data['cheque_date'] = $cheque_date;
    //     $data['cheque_bank_from'] = $cheque_bank_from;
    //     $data['cheque_bank_ac_from'] = $cheque_bank_ac_from;
    //     $data['cheque_bank_ifsc_from'] = $cheque_bank_ifsc_from;
    //     $data['cheque_bank_branch_from'] = $cheque_bank_branch_from;
    //     $data['cheque_bank_from_id'] = $cheque_bank_from_id;
    //     $data['cheque_bank_ac_from_id'] = $cheque_bank_ac_from_id;
    //     $data['cheque_bank_to'] = $cheque_bank_to;
    //     $data['cheque_bank_ac_to'] = $cheque_bank_ac_to;
    //     $data['cheque_bank_to_name'] = $cheque_bank_to_name;
    //     $data['cheque_bank_to_branch'] = $cheque_bank_to_branch;
    //     $data['cheque_bank_to_ac_no'] = $cheque_bank_to_ac_no;
    //     $data['cheque_bank_to_ifsc'] = $cheque_bank_to_ifsc;
    //     $data['transction_no'] = $transction_no;
    //     $data['transction_bank_from'] = $transction_bank_from;
    //     $data['transction_bank_ac_from'] = $transction_bank_ac_from;
    //     $data['transction_bank_ifsc_from'] = $transction_bank_ifsc_from;
    //     $data['transction_bank_branch_from'] = $transction_bank_branch_from;
    //     $data['transction_bank_from_id'] = $transction_bank_from_id;
    //     $data['transction_bank_from_ac_id'] = $transction_bank_from_ac_id;
    //     $data['transction_bank_to'] = $transction_bank_to;
    //     $data['transction_bank_ac_to'] = $transction_bank_ac_to;
    //     $data['transction_bank_to_name'] = $transction_bank_to_name;
    //     $data['transction_bank_to_ac_no'] = $transction_bank_to_ac_no;
    //     $data['transction_bank_to_branch'] = $transction_bank_to_branch;
    //     $data['transction_bank_to_ifsc'] = $transction_bank_to_ifsc;
    //     $data['transction_date'] = $transction_date;
    //     $data['entry_date'] = $entry_date;
    //     $data['entry_time'] = $entry_time;
    //     $data['created_by'] = $created_by;
    //     $data['created_by_id'] = $created_by_id;
    //     $data['created_at'] = $created_at;
    //     $data['updated_at'] = $updated_at;
    //     $transcation = \App\Models\AllHeadTransaction::create($data);
    //     return true;
    // }

    public static function headTransactionCreate($daybook_ref_id, $branch_id, $bank_id, $bank_ac_id, $head_id, $type, $sub_type, $type_id, $associate_id, $member_id, $branch_id_to, $branch_id_from, $amount, $description, $payment_type, $payment_mode, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $type_transaction_id, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $companyId, $isApp = null)
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
        $data['created_by'] = $created_by;
        $data['created_by_id'] = $created_by_id;
        $data['created_at'] = $created_at;
        $data['updated_at'] = $updated_at;
        $data['company_id'] = $companyId;
        if ($isApp != null) {
            $data['app_login_user_id'] = $created_by_id;
            $data['is_app'] = 1;
        }
        $transcation = \App\Models\AllHeadTransaction::create($data);
        return true;
    }
    /**
     *  create branch day book  ------------ New Field Add
     *
     * @param  $amount,$entry_date,$entry_time,$created_at
     * @return \Illuminate\Http\Response
     */
    public static function NewFieldBranchDaybookCreate($daybook_ref_id, $branch_id, $type, $sub_type, $type_id, $associate_id, $member_id, $branch_id_to, $branch_id_from, $opening_balance, $amount, $closing_balance, $description, $description_dr, $description_cr, $payment_type, $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $is_contra, $contra_id, $created_at, $updated_at, $type_transaction_id, $ssb_account_id_to, $cheque_bank_from_id, $cheque_bank_ac_from_id, $cheque_bank_to_name, $cheque_bank_to_branch, $cheque_bank_to_ac_no, $cheque_bank_to_ifsc, $transction_bank_from_id, $transction_bank_from_ac_id, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc, $amount_type, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $jv_unique_id, $cheque_type, $cheque_id)
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
        $data['created_by'] = $created_by;
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
        $data['amount_type'] = $amount_type;
        $data['ssb_account_tran_id_to'] = $ssb_account_tran_id_to;
        $data['ssb_account_tran_id_from'] = $ssb_account_tran_id_from;
        $data['jv_unique_id'] = $jv_unique_id;
        $data['cheque_type'] = $cheque_type;
        $data['cheque_id'] = $cheque_id;
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
    public static function NewFieldAddSamraddhBankDaybookCreate($daybook_ref_id, $bank_id, $account_id, $type, $sub_type, $type_id, $associate_id, $member_id, $branch_id, $opening_balance, $amount, $closing_balance, $description, $description_dr, $description_cr, $payment_type, $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $type_transaction_id, $ssb_account_id_to, $cheque_bank_from_id, $cheque_bank_ac_from_id, $cheque_bank_to_name, $cheque_bank_to_branch, $cheque_bank_to_ac_no, $cheque_bank_to_ifsc, $transction_bank_from_id, $transction_bank_from_ac_id, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $jv_unique_id, $cheque_type, $cheque_id)
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
        $data['created_by'] = $created_by;
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
        $data['created_by'] = $created_by;
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
    /*
    GET ELI AMOUNT
    */
    // public function getEliAmount(Request $request)
    // {
    //     $globaldate = $request->entrydate;
    //     $entry_date = date("Y-m-d", strtotime(convertDate($globaldate)));
    //     $getDR = \App\Models\AllHeadTransaction::where('head_id', 89)->whereDate('entry_date', '<=', $entry_date)->where('payment_type', 'DR')->sum('amount');
    //     // ECHO $getDR;DIE;
    //     $getCR = \App\Models\AllHeadTransaction::where('head_id', 89)->whereDate('entry_date', '<=', $entry_date)->where('payment_type', 'CR')->sum('amount');
    //     $val = $getDR - $getCR;
    //     $balance = number_format((float) $val, 2, '.', '');
    //     // echo $balance;die;
    //     $return_array = compact('balance');
    //     return json_encode($return_array);
    // }
    public function getEliAmount(Request $request)
    {
        $globaldate = $request->entrydate;
        $entry_date = date("Y-m-d", strtotime(convertDate($globaldate)));
        $getDR = \App\Models\AllHeadTransaction::where('head_id', 89)->when(isset($request->company_id), function ($query) use ($request) {
            return $query->where('company_id', $request->company_id);
        })->whereDate('entry_date', '<=', $entry_date)->where('payment_type', 'DR')->sum('amount');
        $getCR = \App\Models\AllHeadTransaction::where('head_id', 89)->when(isset($request->company_id), function ($query) use ($request) {
            return $query->where('company_id', $request->company_id);
        })->whereDate('entry_date', '<=', $entry_date)->where('payment_type', 'CR')->sum('amount');
        $val = $getDR - $getCR;
        $balance = number_format((float) $val, 2, '.', '');
        $return_array = compact('balance');
        return json_encode($return_array);
    }
    public function vendorBillget(Request $request)
    {
        $bill = \App\Models\VendorBill::where('vendor_id', $request->vendor_id)->where('status', '!=', 2)->where('is_deleted', 0)->where('bill_type', 0)->get(['id', 'bill_number']);
        $return_array = compact('bill');
        return json_encode($return_array);
    }
    public function vendorBillDue(Request $request)
    {
        $bill = \App\Models\VendorBill::where('id', $request->vendor_bill_id)->first(['id', 'balance']);
        $return_array = 0.00;
        if ($bill) {
            $return_array = $bill->balance;
        }
        return json_encode($return_array);
    }
    /**
     *  get delete commission New functinality 25 may 2022
     * @param
     * @return \Illuminate\Http\Response
     */
    public static function commissionDeleteNew($daybookId, $investment_id)
    {
        // $deleteCommission = \App\Models\CommissionEntryDetail::where('investment_id', $investment_id)->where('daybook_id', $daybookId)->delete();
    }
    public static function commissionDeleteNewUpdate($daybookId, $investment_id)
    {
        // $deleteCommission = \App\Models\CommissionEntryDetail::where('investment_id', $investment_id)->where('daybook_id', $daybookId)->update(['is_deleted' => '1']);
    }
    public static function commissionDeleteUpdateNew($daybookId, $investment_id)
    {
        // $deleteCommission = \App\Models\AssociateCommission::where('is_distribute', 0)->where('type_id', $investment_id)->where('day_book_id', $daybookId)->update(['is_deleted' => '1']);
        $getKotaBusiness = \App\Models\AssociateKotaBusiness::where('type_id', $investment_id)->where('day_book_id', $daybookId)->first();
        if ($getKotaBusiness) {
            $deleteBusiness = \App\Models\AssociateKotaBusiness::where('type_id', $investment_id)->where('day_book_id', $daybookId)->update(['is_deleted' => '1']);
            $deleteBusinessTeam = \App\Models\AssociateKotaBusinessTeam::where('associate_kota_business_id', $getKotaBusiness->id)->where('day_book_id', $daybookId)->update(['is_deleted' => '1']);
        }
    }
    public static function commissionDeleteUpdateNewLoan($daybookId)
    {
        $deleteCommission = \App\Models\AssociateCommission::where('is_distribute', 0)->whereIn('type', ['4', '6',])->where('day_book_id', $daybookId)->update(['is_deleted' => '1']);
    }
    public static function commissionDeleteUpdateNewLoanGroup($daybookId)
    {
        $deleteCommission = \App\Models\AssociateCommission::where('is_distribute', 0)->whereIn('type', ['7', '8',])->where('day_book_id', $daybookId)->update(['is_deleted' => '1']);
    }
    /**
     * static function of gst transaction
     */
    public static function gstTransaction($dayBookId = NULL, $companyGstNo = NULL, $customerGstNo = NULL, $taxValue = NULL, $rateofTax = NULL, $amountIgstTax = NULL, $amountCgstTax = NULL, $amountSgstTax = NULL, $totalAmount = NULL, $headId = NULL, $entryDate, $flag, $type_id, $branchId, $companyId)
    {
        $globaldate = Session::get('created_at');
        $accountHead = \App\Models\AccountHeads::select('id', 'sub_head')->where('head_id', $headId)->first();
        $shortHead = explode(' ', $accountHead->sub_head);
        $branchState = getBranchDetail($branchId)->state_id;
        $gstNumber = \App\Models\GstSetting::where('state_id', $branchState)->where('applicable_date', '<=', $globaldate)->first('id');
        $result = '';
        $transYead = date('y', strtotime(convertDate($entryDate)));
        $checkUid = \App\Models\GstTransaction::select('uid')->where('type_flag', $flag)->orderBy('uid', 'desc')->first();
        if (isset($checkUid->uid)) {
            $uid = str_pad($checkUid->uid + 1, 5, "0", STR_PAD_LEFT);
        } else {
            $a = 1;
            $uid = str_pad(1, 5, "0", STR_PAD_LEFT);
        }
        foreach ($shortHead as $word) {
            $result .= $word[0];
        }
        $currFinace = \Carbon\Carbon::now()->format('y');
        $currPreFinance = (\Carbon\Carbon::now()->format('y')) - 1;
        $data['invoice_number'] = $result . $branchId . $currPreFinance . $currFinace . $totalAmount . $uid;
        $data['type_flag'] = $flag;
        $data['uid'] = $uid;
        $data['daybook_ref_id'] = $dayBookId;
        $data['company_gst_no'] = $companyGstNo;
        $data['customer_gst_no'] = $customerGstNo;
        $data['tax_value'] = $taxValue;
        $data['rate_of_tax'] = $rateofTax;
        $data['amount_of_tax_igst'] = $amountIgstTax;
        $data['amount_of_tax_cgst'] = $amountCgstTax;
        $data['amount_of_tax_sgst'] = $amountSgstTax;
        $data['total_amount'] = $totalAmount;
        $data['head_id'] = $headId;
        $data['type_id'] = $type_id;
        $data['branch_id'] = $branchId;
        $data['entry_date'] = $globaldate;
        $data['gst_setting_id'] = $gstNumber->id;
        $data['company_id'] = $companyId;
        $createTransaction = \App\Models\GstTransaction::create($data);
        return $createTransaction->id;
    }

    public static function getbranchbankbalanceamount(Request $request)
    {
        $globaldate = $request->entrydate;
        $entry_date = date("Y-m-d", strtotime(convertDate($globaldate)));
        $branch_id = $request->branch_id;
        $company_id = $request->company_id;
        $getBranchAmount = \App\Models\Branch::whereId($branch_id)->first();
        $Amount = $company_id == 1 ? $getBranchAmount->total_amount : 0;

        $startDate = ($company_id == 1) ? date("Y-m-d", strtotime(convertDate('2021-08-05'))) : '';
        $balance = \App\Models\BranchCurrentBalance::where('branch_id', $branch_id)->where('company_id', $company_id)->when($startDate != '', function ($q) use ($startDate) {
            $q->whereDate('entry_date', '>=', $startDate);
        })->where('entry_date', '<=', $entry_date);
        if ($company_id != '') {
            $balance = $balance->where('company_id', $company_id);
        }
        $balance = $balance->sum('totalAmount');

        // $getBranchAmount = \App\Models\Branch::findorfail($branch_id);
        $return_array = ['balance' => $balance + $Amount];
        return json_encode($return_array);


    }

    public static function createBranchDayBookModify($daybook_ref_id, $branch_id, $type, $sub_type, $type_id, $type_transaction_id, $associate_id, $member_id, $branch_id_to, $branch_id_from, $amount, $description, $description_dr, $description_cr, $payment_type, $payment_mode, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $companyId)
    {
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
        $data['created_by'] = $created_by;
        $data['created_by_id'] = $created_by_id;
        $data['company_id'] = $companyId;
        $data['created_at'] = date("Y-m-d " . $t . "", strtotime(convertDate($globaldate)));
        $transcation = \App\Models\BranchDaybook::create($data);
        return true;
    }
    public static function createAllHeadTransactionModify(
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
        if (Session::get('created_atUpdate')) {
            $globaldate = Session::get('created_atUpdate');
        } else {
            $globaldate = Session::get('created_at');
        }
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
        $data['created_by'] = $created_by;
        $data['created_by_id'] = $created_by_id;
        $data['company_id'] = $companyId;
        $data['created_at'] = date("Y-m-d " . $t . "", strtotime(convertDate($globaldate)));
        $transcation = \App\Models\AllHeadTransaction::create($data);
        return true;
    }
    public static function createSamraddhBankDaybookModify(
        $daybook_ref_id,
        $bank_id,
        $account_id,
        $type,
        $sub_type,
        $type_id,
        $type_transaction_id,
        $associate_id,
        $member_id,
        $branch_id,
        $opening_balance,
        $amount,
        $closing_balance,
        $description,
        $description_dr,
        $description_cr,
        $payment_type,
        $payment_mode,
        $currency_code,
        $amount_to_id,
        $amount_to_name,
        $amount_from_id,
        $amount_from_name,
        $v_no,
        $v_date,
        $ssb_account_id_from,
        $cheque_no,
        $cheque_date,
        $cheque_bank_from,
        $cheque_bank_ac_from,
        $cheque_bank_ifsc_from,
        $cheque_bank_branch_from,
        $cheque_bank_to,
        $cheque_bank_ac_to,
        $transction_no,
        $transction_bank_from,
        $transction_bank_ac_from,
        $transction_bank_ifsc_from,
        $transction_bank_branch_from,
        $transction_bank_to,
        $transction_bank_ac_to,
        $transction_bank_to_name,
        $transction_bank_to_ac_no,
        $transction_bank_to_branch,
        $transction_bank_to_ifsc,
        $transction_date,
        $entry_date,
        $entry_time,
        $created_by,
        $created_by_id,
        $created_at,
        $companyId
    ) {
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
        $data['transction_bank_from'] = null;
        $data['transction_bank_ac_from'] = null;
        $data['transction_bank_ifsc_from'] = $transction_bank_ifsc_from;
        $data['transction_bank_branch_from'] = $transction_bank_branch_from;
        $data['transction_bank_to'] = $transction_bank_to;
        $data['transction_bank_ac_to'] = $transction_bank_to_name;
        $data['transction_bank_to_name'] = $transction_bank_to_ac_no;
        $data['transction_bank_to_ac_no'] = $transction_bank_ac_from;
        $data['transction_bank_to_branch'] = $transction_bank_to_branch;
        $data['transction_bank_to_ifsc'] = $transction_bank_to_ifsc;
        $data['transction_date'] = date("Y-m-d", strtotime(convertDate($globaldate)));
        $data['entry_date'] = date("Y-m-d", strtotime(convertDate($globaldate)));
        $data['entry_time'] = date("H:i:s");
        $data['created_by'] = $created_by;
        $data['created_by_id'] = $created_by_id;
        $data['company_id'] = $companyId;
        $data['created_at'] = date("Y-m-d " . $t . "", strtotime(convertDate($globaldate)));
        $transcation = \App\Models\SamraddhBankDaybook::create($data);
        return $transcation->id;
    }
    public static function getBankBalance(Request $request)
    {
        $data = checkBankBalance($request);
        return $data;
    }
    //created by tansukh jangir
    public function getLoanPlanByType(Request $request)
    {
        $data = \App\Models\Loans::where([
            ['loan_type', $request->loan_type],
            ['company_id', $request->company_id],
            ['status', 1],
        ])->get(['id', 'code', 'name']);
        return json_encode($data);
    }

    /*** Expence Tanshuk  */

    public static function branchDaybookCreateModified($daybook_ref_id, $branch_id, $type, $sub_type, $type_id, $associate_id, $member_id, $branch_id_to, $branch_id_from, $amount, $description, $description_dr, $description_cr, $payment_type, $payment_mode, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $type_transaction_id, $ssb_account_id_to, $company_id)
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
        $data['created_by'] = $created_by;
        $data['created_by_id'] = $created_by_id;

        $data['created_at'] = $created_at;
        $data['updated_at'] = $updated_at;
        $data['company_id'] = $company_id;

        /*----------------*/
        $data['ssb_account_id_to'] = $ssb_account_id_to;
        $transcation = \App\Models\BranchDaybook::create($data);
        return $transcation->id;
    }
    public static function NewFieldAddSamraddhBankDaybookCreateModify($daybook_ref_id, $bank_id, $account_id, $type, $sub_type, $type_id, $associate_id, $member_id, $branch_id, $opening_balance, $amount, $closing_balance, $description, $description_dr, $description_cr, $payment_type, $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $type_transaction_id, $ssb_account_id_to, $cheque_bank_from_id, $cheque_bank_ac_from_id, $cheque_bank_to_name, $cheque_bank_to_branch, $cheque_bank_to_ac_no, $cheque_bank_to_ifsc, $transction_bank_from_id, $transction_bank_from_ac_id, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $jv_unique_id, $cheque_type, $cheque_id, $company_id)
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
        $data['created_by'] = $created_by;
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
        $data['company_id'] = $company_id;
        $transcation = \App\Models\SamraddhBankDaybook::create($data);
        return $transcation->id;
    }
    public static function newHeadTransactionCreate($daybook_ref_id, $branch_id, $bank_id, $bank_ac_id, $head_id, $type, $sub_type, $type_id, $associate_id, $member_id, $branch_id_to, $branch_id_from, $amount, $description, $payment_type, $payment_mode, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $type_transaction_id, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $company_id)
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
        $data['created_by'] = $created_by;
        $data['created_by_id'] = $created_by_id;
        $data['created_at'] = $created_at;
        $data['updated_at'] = $updated_at;
        $data['company_id'] = $company_id;
        $transcation = \App\Models\AllHeadTransaction::create($data);
        return true;
    }
    public static function renewlimit($request, $repository)
    {        
        $accountNumber = $request->account_number;
        $currentDate = $request->renewalDate ? date('Y-m-d', strtotime(convertDate($request->renewalDate))) : date('Y-m-d', strtotime(convertDate($request->renewal_date)));
        $currentMonth = $request->renewalDate ? date('m', strtotime(convertDate($request->renewalDate))) : date('Y-m-d', strtotime(convertDate($request->renewal_date)));
        $planId = $request->renewPlanId ?? $request->renew_investment_plan_id;
        $getPlanDetails = $repository->getPlansById($planId)->withoutGlobalScope(ActiveScope::class)->with('plantenure')->first();
        $investment = $repository->getAllMemberinvestments()
            ->where('account_number', $accountNumber)
            ->where('investment_correction_request', 0)
            ->where('renewal_correction_request', 0)
            ->where('is_mature', 1)
            ->first()
        ;
        $tenure = ($getPlanDetails->plan_sub_category_code == 'X' and $getPlanDetails->plan_category_code == 'M') ? 12 : ($investment->tenure * 12 /*$getPlanDetails->plantenure->tenure*/);
        $tenure_Month = $tenure - 3;
        $msg = null;
        if (isset($investment)) {
            $openingDate = $investment->created_at;
            $AftermonthsDate = date('Y-m-d', strtotime($openingDate . ' +' . $tenure_Month . ' months'));
            $CompleteopeningDate = date('Y-m-d', strtotime($openingDate . ' +' . $tenure . ' months'));
            $closingDate = date('Y-m-d', strtotime($CompleteopeningDate . ' -1 days'));
            $amount = (int) $request->amount; //entered Amount
            $denoAmount = $investment->deposite_amount; // Deno Amount
            $company_id = $investment->company_id;
            $maxPaidAmount = ($getPlanDetails->plan_category_code == 'D') ? ($denoAmount * 31) : $denoAmount;
            // datemiddlemonth is a helper function
            $date = datemiddlemonth($openingDate, $currentDate);
            $start = $date['start'];
            $end = $date['end'];
            $payedAmount = $repository->getAllDaybook()
                ->where('investment_id', $investment->id)
                ->where('transaction_type', 4)
                ->where('account_no', $accountNumber)
                ->where(function ($query) use ($start, $end) {
                    $query->whereDate('created_at', $start)
                        ->orwhereDate('created_at', '=', $end)
                        ->orwhereBetween('created_at', [$start, $end]);
                })
                ->where('company_id', $company_id)
                ->sum('deposit')
            ;
            $compairAmount = $maxPaidAmount - ($payedAmount ?? 0);
            if ($currentDate >= $AftermonthsDate && $closingDate >= $currentDate) {
                if (
                    ($getPlanDetails->plan_category_code == 'D') ||
                    ($getPlanDetails->plan_category_code == 'M' && $getPlanDetails->plan_sub_category_code != 'X') ||
                    ($getPlanDetails->plan_category_code == 'M' && $getPlanDetails->plan_sub_category_code == 'X')
                ) {
                    if ($compairAmount != 0) {
                        if ($amount > $compairAmount) {
                            $msg = 'Renewal amount extended as per monthly limit, You can pay only ' . $compairAmount . ' amount on selected date.';
                        } else {
                            $msg = null;
                        }
                    }
                    if ($compairAmount == 0) {
                        $msg = "No renewal amout due on selected date ";
                    }
                }
            }
        } else {
            $msg = null;
        }
        $response = compact('msg');
        return $response;
    }
    public static function gstTransactionNew($dayBookId = NULL, $companyGstNo = NULL, $customerGstNo = NULL, $taxValue = NULL, $rateofTax = NULL, $amountIgstTax = NULL, $amountCgstTax = NULL, $amountSgstTax = NULL, $totalAmount = NULL, $headId = NULL, $entryDate, $flag, $type_id, $branchId, $companyId, $receivedId)
    {
        $globaldate = Session::get('created_at');
        $accountHead = \App\Models\AccountHeads::select('id', 'sub_head')->where('head_id', $headId)->first();
        $shortHead = explode(' ', $accountHead->sub_head);
        $branchState = getBranchDetail($branchId)->state_id;
        $gstNumber = \App\Models\GstSetting::where('state_id', $branchState)->where('applicable_date', '<=', $globaldate)->first('id');
        $result = '';
        $transYead = date('y', strtotime(convertDate($entryDate)));
        $checkUid = \App\Models\GstTransaction::select('uid')->where('type_flag', $flag)->orderBy('uid', 'desc')->first();
        if (isset($checkUid->uid)) {
            $uid = str_pad($checkUid->uid + 1, 5, "0", STR_PAD_LEFT);
        } else {
            $a = 1;
            $uid = str_pad(1, 5, "0", STR_PAD_LEFT);
        }
        foreach ($shortHead as $word) {
            $result .= $word[0];
        }
        $currFinace = \Carbon\Carbon::now()->format('y');
        $currPreFinance = (\Carbon\Carbon::now()->format('y')) - 1;
        $data['invoice_number'] = $result . $branchId . $currPreFinance . $currFinace . $totalAmount . $uid;
        $data['type_flag'] = $flag;
        $data['uid'] = $uid;
        $data['daybook_ref_id'] = $dayBookId;
        $data['company_gst_no'] = $companyGstNo;
        $data['customer_gst_no'] = $customerGstNo;
        $data['tax_value'] = $taxValue;
        $data['rate_of_tax'] = $rateofTax;
        $data['amount_of_tax_igst'] = $amountIgstTax;
        $data['amount_of_tax_cgst'] = $amountCgstTax;
        $data['amount_of_tax_sgst'] = $amountSgstTax;
        $data['total_amount'] = $totalAmount;
        $data['head_id'] = $headId;
        $data['type_id'] = $type_id;
        $data['branch_id'] = $branchId;
        $data['entry_date'] = $globaldate;
        $data['gst_setting_id'] = $gstNumber->id;
        $data['company_id'] = $companyId;
        $data['received_id'] = $receivedId;
        $createTransaction = \App\Models\GstTransaction::create($data);
        return $createTransaction->id;
    }
    public function getActivePlanByType(Request $request)
    {
        $loanPlanList = \App\Models\Loans::where('loan_type', $request->loan_type)->where('status', 1)->get(['id', 'name', 'code']);
        $return_array = compact('loanPlanList');
        return json_encode($return_array);
    }
    public static function SSBDateCRNew($account_id, $account_no, $balance, $amount, $description, $currency_code, $payment_type, $payment_mode, $branch_id, $associate_id, $type, $date, $company_id, $daybook_ref_id = NULL)
    {

        $entryDate = date("Y-m-d", strtotime(convertDate($date)));
        $entryTime = date("H:i:s");

        $ssbGet = \App\Models\SavingAccountTranscation::where('saving_account_id', $account_id)->where('created_at', '<=', $date)->where('company_id', $company_id)->orderByDesc('id')->first();


        // Check if associate_id is null before assigning it to dataSsb
        if ($associate_id !== null) {

            $dataSsb['associate_id'] = $associate_id;
        }

        $dataSsb['saving_account_id'] = $account_id;
        $dataSsb['branch_id'] = $branch_id;
        $dataSsb['type'] = $type;
        $dataSsb['account_no'] = $account_no;
        if ($ssbGet) {
            $dataSsb['opening_balance'] = $ssbGet->opening_balance + $amount;
            $dataSsb['amount'] = $ssbGet->opening_balance + $amount;
        } else {
            $dataSsb['opening_balance'] = $amount;
            $dataSsb['amount'] = $amount;
        }

        $dataSsb['deposit'] = $amount;

        $dataSsb['description'] = $description;
        $dataSsb['currency_code'] = $currency_code;
        $dataSsb['payment_type'] = $payment_type;
        $dataSsb['payment_mode'] = $payment_mode;
        $dataSsb['created_at'] = $entryDate . ' ' . $entryTime;
        $dataSsb['company_id'] = $company_id;
        $dataSsb['daybook_ref_id'] = $daybook_ref_id;
        $resSsb = \App\Models\SavingAccountTranscation::insertGetId($dataSsb);


        // $ssbBalance = $balance-$amount;
        $ssbBalance = $balance + $amount;
        $sResult = \App\Models\SavingAccount::find($account_id);
        $sData['balance'] = $ssbBalance;
        $sResult->update($sData);
        //print_r($resSsb->id);die;


        return $resSsb;
    }
    public static function SSBBackDateCRNew($ssbId, $date, $amount)
    {
        $entryDate = date("Y-m-d", strtotime(convertDate($date)));
        $entryTime = date("H:i:s");

        $getNextrecord = \App\Models\SavingAccountTranscation::where('saving_account_id', $ssbId)->where('created_at', '>', $date)->orderby('created_at', 'ASC')->get();

        if ($getNextrecord) {
            foreach ($getNextrecord as $v1) {
                $ResultNext1 = \App\Models\SavingAccountTranscation::find($v1->id);

                $nextData1['opening_balance'] = $v1->opening_balance + $amount;
                //$nextData1['deposit']=$v1->deposit+$amount;

                ///print_r($nextData1);die;
                $ResultNext1->update($nextData1);
                $insertid = $ResultNext1->id;
            }
        }
        return true;
    }
    public function approveReceivedChequeCompany(Request $request)
    {
        // dd($request->all());
        $name = $request->name;
        $cheque = \App\Models\ReceivedCheque::whereCompanyId($request->companyId)
            ->where('status', 2)
            ->where('account_holder_name', 'like', '%' . $name . '%')
            ->where('amount', $request->amount)
            ->where('branch_id', 29)
            ->get(['id', 'cheque_no', 'amount']);
        $return_array = compact('cheque');
        return json_encode($return_array);
    }
    //Create By shahid
    public static function SSBDateDRNewUpdate($account_id, $account_no, $balance, $amount, $description, $currency_code, $payment_type, $payment_mode, $branch_id, $associate_id, $type, $date, $company_id)
    {
        $entryDate = date("Y-m-d", strtotime(convertDate($date)));
        $entryTime = date("H:i:s");

        $ssbGet = \App\Models\SavingAccountTranscation::where('saving_account_id', $account_id)->where('created_at', '<=', $date)->where('company_id', $company_id)->orderByDesc('id')->first();


        // Check if associate_id is null before assigning it to dataSsb
        if ($associate_id !== null) {

            $dataSsb['associate_id'] = $associate_id;
        }

        $dataSsb['saving_account_id'] = $account_id;
        $dataSsb['branch_id'] = $branch_id;
        $dataSsb['type'] = $type;
        $dataSsb['account_no'] = $account_no;
        if ($ssbGet) {
            $dataSsb['opening_balance'] = $ssbGet->opening_balance + $amount;
            $dataSsb['amount'] = $amount;
        } else {
            $dataSsb['opening_balance'] = $amount;
            $dataSsb['amount'] = $amount;
        }

        $dataSsb['deposit'] = $amount;

        $dataSsb['description'] = $description;
        $dataSsb['currency_code'] = $currency_code;
        $dataSsb['payment_type'] = $payment_type;
        $dataSsb['payment_mode'] = $payment_mode;
        $dataSsb['created_at'] = $entryDate . ' ' . $entryTime;
        $dataSsb['company_id'] = $company_id;

        $resSsb = \App\Models\SavingAccountTranscation::insertGetId($dataSsb);


        // $ssbBalance = $balance-$amount;
        $ssbBalance = $balance + $amount;
        $sResult = \App\Models\SavingAccount::find($account_id);
        $sData['balance'] = $ssbBalance;
        $sResult->update($sData);
        //print_r($resSsb->id);die;


        return $resSsb;
    }

    public static function SSBBackDateDRNewUpdate($ssbId, $date, $amount)
    {
        $entryDate = date("Y-m-d", strtotime(convertDate($date)));
        $entryTime = date("H:i:s");

        $getNextrecord = \App\Models\SavingAccountTranscation::where('saving_account_id', $ssbId)->where('created_at', '>', $date)->orderby('created_at', 'ASC')->get();

        if ($getNextrecord) {
            foreach ($getNextrecord as $v1) {
                $ResultNext1 = \App\Models\SavingAccountTranscation::find($v1->id);

                $nextData1['opening_balance'] = $v1->opening_balance + $amount;
                //$nextData1['deposit']=$v1->deposit+$amount;

                ///print_r($nextData1);die;
                $ResultNext1->update($nextData1);
                $insertid = $ResultNext1->id;
            }
        }
        return true;
    }

    // End create by shahid on 10/05/23
}
