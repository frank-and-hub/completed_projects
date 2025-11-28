<?php
namespace App\Http\Controllers\Branch\PaymentManagement;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use App\Interfaces\RepositoryInterface;
use App\Models\{SamraddhCheque, SavingAccountTransactionView, MemberBankDetail, SamraddhBank, BranchCurrentBalance, Companies, Branch, SavingAccount, SavingAccountTranscation, DebitCard};
use App\Services\ImageUpload;
use Spatie\Permission\Models\Permission;
use Carbon\Carbon;
use URL;
use DB;
use Session;
use App\Http\Controllers\Branch\CommanTransactionsController;
use App\Services\Sms;

class WithdrawalController extends Controller
{
    /**
     * Instantiate a new controller instance.
     *
     * @return void
     */
    public function __construct(RepositoryInterface $repository)
    {
        $this->middleware('auth');
        $this->repository = $repository;
    }
    /**
     * Amount withdrawal view.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if (!in_array('SSB Withdraw', auth()->user()->getPermissionNames()->toArray())) {
            return redirect()->route('branch.dashboard');
        }
        $getBranchId = getUserBranchId(Auth::user()->id);
        $stateid = customBranchName()->state_id;
        $branchId = $getBranchId->id;
        $branchDetail = getBranchDetail($branchId);
        $date = checkMonthAvailability(date('d'), date('m'), date('Y'), $stateid);
        $data['branch_id'] = $branchId;
        $data['title'] = 'Saving Account Withdrawal';
        $data['branches'] = Branch::select('id', 'name', 'branch_code')->get();
        //$data['bank'] = AccountHeads::select('id','title','account_number')->where('account_type',2)->get();
        //$data['cheques'] = SamraddhCheque::select('cheque_no')->get();
        $data['bank'] = SamraddhBank::with('bankAccount:id,bank_id,account_no,branch_name')->where("status", "1")->get();
        $data['cheques'] = SamraddhCheque::select('cheque_no')->get();
        $data['microLoanRes'] = BranchCurrentBalance::select('totalAmount', 'company_id', 'entry_date', 'branch_id')->where('branch_id', $branchId)->whereDate('entry_date', '<=', $date)->orderBy('entry_date', 'desc')->first();
        $data['company_withdarl'] = Companies::whereHas('companybranchs', function ($q) use ($branchId) {
            $q->whereStatus('1')->whereBranchId($branchId)->with([
                'branch' => function ($q) use ($branchId) {
                    $q->whereId($branchId)->whereStatus('1');
                }
            ]);
        })->whereStatus('1')->pluck('name', 'id');
        $data['branch_balance_'] = \App\Models\BranchCurrentBalance::where('branch_id', $branchId)->whereBetween('entry_date', ['2021-08-05', $date])->sum('totalAmount');
        $data['branch_balance_'] = $data['branch_balance_'] + $branchDetail->total_amount;
        // echo "<pre>";print_r($data['bank']); die();
        return view('templates.branch.payment-management.withdrawal.withdrawal_ssb_amount', $data);
    }
    /**
     * @param  \Illuminate\Http\Request  $request
     * Get ssb account details.
     * modify by Sourab on 26-09-2023 as per client task bellow - 
     * There is a modification in above condition. above
     * check will be applied only for those customers who have running loan across the company. customer not having any loan in any company or has 
     * cleared all his loans can do saving withdrawal  from any branch.
     * and
     * Withdrawal from saving accounts can be done only at the branch where that saving account belongs not at any other branch.
     * If user tries to withdraw from other branch then show error message "Withdrawal of this account can be done from <mother branch name>"
     * Please make necessary . update in software (branch panel only).
     * @return JSON
     */
    public function accountDetails(Request $request)
    {
        /**
         * get account number from request from server
         */
        $account_number = $request->account_number;
        /**
         * get branch details by login branch id
         * as well get branch code.
         */
        $branchId = $request->branchId;
        $branchCode = getBranchDetail($branchId)->branch_code;

        $companyId = $request->companyId;
        $cDate = date("Y-m-d");
        // $transactionBydate = SavingAccountTranscation::select('opening_balance')->where('account_no', $account_number)->where('is_deleted', 0)->where(DB::Raw('Date(created_at)'), '<=', $cDate)->orderby('id', 'desc')->first();
        /**
         * for now all saving account transactions will show from saving_account_transaction_view table , so ablove code is commented.
         */
        $todayTransaction = SavingAccountTranscation::where('account_no', $account_number)->where('type', 5)->whereDate('created_at', $cDate)->count();
        $transactionBydate = SavingAccountTransactionView::select('opening_balance')->whereAccountNo($account_number)->where(DB::Raw('Date(opening_date)'), '<=', $cDate)->orderby('opening_date', 'desc')->first();

        $ssbAccountDetails = SavingAccount::with(['ssbMemberCustomer2', 'ssbMemberCustomer.member'])->where('account_no', $account_number)->whereCompanyId($companyId) /* ->where('branch_id',$branchId)*/->get();
        if(empty($ssbAccountDetails[0]) && !isset($ssbAccountDetails[0]))
        {
            return response()->json(['msg'=>'empty']);
        }

        if($ssbAccountDetails[0]->transaction_status == 0)
        {
            return response(['msg'=>'inactive']);
        }
        $mb = 0;
        $memberBank = 0;
        if ($todayTransaction) {
            $memberBank = MemberBankDetail::where('member_id', isset($ssbAccountDetails) ? isset($ssbAccountDetails[0]) ? $ssbAccountDetails[0]->member_id : '0' : '0')->first();
            if ($memberBank) {
                $mb = 1;
            }
        }
        $debitCardExist = DebitCard::where('ssb_id', isset($ssbAccountDetails) ? isset($ssbAccountDetails[0]) ? $ssbAccountDetails[0]->id : 0 : 0)->exists();
        if ($debitCardExist == false) {
            $resCount = count($ssbAccountDetails);
        } else {
            $resCount = count($ssbAccountDetails);
        }
        /**
         * saving account is exist on not if existing then only check
         * that saing account member have any runnig loan or not by using 
         * member table to member_loan table where 
         */
        $currectBranch = false;
        $branchname = false;
        // $getbranchCurrect = $this->getbranchCurrectData($ssbAccountDetails,$branchCode);
        // $currectBranch = $getbranchCurrect['currectBranch'];
        // $branchname = $getbranchCurrect['branchname'];
        /**
         * get member signature and photo from Amazon web services using $signature,$photo 
         * variable saperatly and send througt controller
         * by dynamic menber id.
         */


        // $signature = !empty($ssbAccountDetails[0]) ? $ssbAccountDetails[0]['ssbcustomerDataGet'] ? ImageUpload::fileExists('profile/member_signature/' . $ssbAccountDetails[0]['ssbcustomerDataGet']['signature']) ? ImageUpload::generatePreSignedUrl('profile/member_signature/' . $ssbAccountDetails[0]['ssbcustomerDataGet']['signature']) : '' : '' : '';
        // $photo = !empty($ssbAccountDetails[0]) ? $ssbAccountDetails[0]['ssbcustomerDataGet'] ? ImageUpload::fileExists('profile/member_avatar/' . $ssbAccountDetails[0]['ssbcustomerDataGet']['photo']) ? ImageUpload::generatePreSignedUrl('profile/member_avatar/' . $ssbAccountDetails[0]['ssbcustomerDataGet']['photo']) : '' : '' : '';

        $signature = !empty($ssbAccountDetails[0]) ? $ssbAccountDetails[0]['ssbcustomerDataGet'] ? $ssbAccountDetails[0]['ssbcustomerDataGet']['signature'] : '' : '' ;
        $photo = !empty($ssbAccountDetails[0]) ? $ssbAccountDetails[0]['ssbcustomerDataGet'] ? $ssbAccountDetails[0]['ssbcustomerDataGet']['photo'] : '' : '' ;

        if (!empty($signature)) {
            $signature = ImageUpload::fileExists('profile/member_signature/' . $signature) ? ImageUpload::generatePreSignedUrl('profile/member_signature/' . $signature) : '';
        }

        if (!empty($photo)) {
            $photo = ImageUpload::fileExists('profile/member_avatar/' . $photo) ? ImageUpload::generatePreSignedUrl('profile/member_avatar/' . $photo) : '';
        }

        if ((($signature == '') && ($photo != '')) || (($signature != '') && ($photo == '')) || (($signature == '') && ($photo == ''))) {
            return response(['msg'=>'withdrawal again']);
        }

        $return_array = compact('ssbAccountDetails', 'resCount', 'todayTransaction', 'mb', 'memberBank', 'transactionBydate', 'currectBranch', 'branchname', 'signature', 'photo');
        return json_encode($return_array);
    }
    /**
     * this getbranchCurrectData function create by Sourab on 26-09-2023 for getting current login branch name and verify
     * that the given account number for withdrawal having any current running loa/ group loan or not. based on saving account
     * member_id from member_loans and grou_loans table.
     */
    public function getbranchCurrectData($ssbAccountDetails, $branchCode)
    {
        /**
         * get all member_loas table data in array where member_loans applicant_id is a key and member_loans id is value 
         * only for current running loans where status are 4.
         * table are use by repository pattern.
         */
        $memberLoanData = $this->repository->getAllMemberloans()->where('is_deleted', 0)->whereIn('status', [4])->pluck('id', 'customer_id');
        /**
         * get all group_loans table data in array where group_loans member_id is a key and group_loans id is value 
         * only for current running loans where status are 4.
         * table are use by repository pattern.
         */
        $memberGroupLoanData = $this->repository->getAllGrouploans()->where('is_deleted', 0)->whereIn('status', [4])->pluck('id', 'customer_id');
        /**
         * check that saving account is exists or not for checking ferther for running loan.
         */
        $existSavingAccount = $ssbAccountDetails->first();
        if ($existSavingAccount != NULL) {
            /**
             * if saiving account member have a loan then check that the saving account branch and
             * current loagin branch are same or not 
             * same will check for group_loan if member have no loans 
             * and if member have no loans that means saving account branch is same as
             * login branch so no need to chack ferther
             */
            if (isset($memberLoanData[$ssbAccountDetails[0]->customer_id])) {
                $return = ($branchCode === $ssbAccountDetails[0]->branch_code) ? true : false;
            } elseif (isset($memberGroupLoanData[$ssbAccountDetails[0]->customer_id])) {
                $return = ($branchCode === $ssbAccountDetails[0]->branch_code) ? true : false;
            } else {
                $return = true;
            }
            /**
             * if saving acocunt is currect then get branch name of login branch.
             */
            $branch = getBranchNameByBrachCode($ssbAccountDetails[0]->branch_id)->name;
        } else {
            $return = true;
            $branch = NULL;
        }
        $response = [
            'currectBranch' => $return,
            'branchname' => $branch,
        ];
        return $response;
    }
    /**
     * Get ssb account details.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return JSON
     */
    public function saveWithdrawal(Request $request)
    {
        DB::beginTransaction();
        try {
            $logUserId = Auth::user()->id;
            $branchDetails = Branch::where('manager_id', $logUserId)->first();
            $branchCode = $branchDetails->branch_code;
            $branchId = $branchDetails->id;
            $globaldate = $request->created_at;
            Session::put('created_at', $request->created_at);
            $currency_code = 'INR';
            $entry_date = date("Y-m-d", strtotime(convertDate($globaldate)));
            $entry_time = date("H:i:s", strtotime(convertDate($globaldate)));
            $amount = $request['amount'];
            $comman_transaction_controller = new CommanTransactionsController;
            $ssbAccountDetails = SavingAccount::with([
                'ssbMemberCustomer.member:id,member_id,first_name,last_name,mobile_no',
                'ssbMemberCustomer2:id,member_id,first_name,last_name,mobile_no',
                'company'
            ])
                ->where('account_no', $request['ssb_account_number'])
                ->first();
            // $ssbAccountDetails = SavingAccount::with('ssbMember')->where('account_no', $request['ssb_account_number'])->first();
            $todayTransaction = SavingAccountTranscation::where('account_no', $request['ssb_account_number'])->where('type', 5)->whereDate('created_at', $entry_date)->count();
            if ($todayTransaction > 0) {
                return back()->with('alert', "You don't have permission more than one withdrawal in a day!");
            }
            $branchName = $branchDetails->name;
            $companyId = $request['company_id'];
            $bank_ac_id = $request->bank_account_number;
            $bank_id = $request->bank;
            $created_by = 2;
            $savingAccountHead = getPlanDetailByCompany($companyId);
            $dayBookRef = CommanTransactionsController::createBranchDayBookReference($request['amount'], $request['created_at']);
            if ($request['payment_mode'] == 0) {
                $cheque_dd_no = NULL;
                $online_payment_id = NULL;
                $online_payment_by = NULL;
                $paymentMode = 0;
                $ssbpaymentMode = 0;
                if ($branchId != $ssbAccountDetails->branch_id) {
                    $description = 'Cash Withdrawal - From ' . $branchName . '';
                } else {
                    $description = 'Cash Withdrawal';
                }
                //$bankBla=\App\Models\BranchCurrentBalance:: where('branch_id',$branchId)->whereDate('entry_date','<=',$entry_date)->orderby('entry_date','desc')->first();
                // $bankBla = BranchCurrentBalance::where('branch_id', $branchDetails->id)->whereDate('entry_date', '<=', $entry_date)->orderBy('entry_date', 'desc')->first();
                /*
                $bankBla = \App\Models\BranchCurrentBalance::where('branch_id', $branchDetails->id)->whereBetween('entry_date', ['2021-08-05', $entry_date])->sum('totalAmount');
                $bankBla = $bankBla + $branchDetails->total_amount;
                $balance = number_format((float) ($bankBla ?? 0.00), 2, '.', '');
                */
                //
                $getBranchAmount = \App\Models\Branch::whereId($branchId)->first();
                $Amount = $companyId == 1 ? $getBranchAmount->total_amount : 0;
                $startDate = ($companyId == 1) ? date("Y-m-d", strtotime(convertDate('2021-08-05'))) : '';
                $balance = \App\Models\BranchCurrentBalance::where('branch_id', $branchId)->where('company_id', $companyId)->when($startDate != '', function ($q) use ($startDate) {
                    $q->whereDate('entry_date', '>=', $startDate);
                })->where('entry_date', '<=', $entry_date);
                if ($companyId != '') {
                    $balance = $balance->where('company_id', $companyId);
                }
                $bankBla = $balance->sum('totalAmount');
                //
                if ($balance < $amount) {
                    return back()->with('alert', 'Sufficient amount not available in branch cash!');
                }

                $ssbBalance = SavingAccountTransactionView::where('account_no', $request->ssb_account_number)->orderBy('opening_date', 'DESC')->first();
                $ssbbalance = $ssbBalance->opening_balance;
                if ($ssbbalance < $amount) {
                    return back()->with('alert', 'Sufficient amount not available in Saving Account!');
                }

            } elseif ($request['payment_mode'] == 1) {

                $ssbBalance = SavingAccountTransactionView::where('account_no', $request->ssb_account_number)->orderBy('opening_date', 'DESC')->first();
                $ssbbalance = $ssbBalance->opening_balance;
                if ($ssbbalance < $amount) {
                    return back()->with('alert', 'Sufficient amount not available in Saving Account!');
                }

                $bankDtail = getSamraddhBank($bank_id);
                $bankAcDetail = getSamraddhBankAccountId($bank_ac_id);
                /*
                $bankBla = \App\Models\BranchCurrentBalance::where('branch_id', $branchId)->whereBetween('entry_date', ['2021-08-05', $entry_date])->sum('totalAmount');
                // $bankBla = \App\Models\SamraddhBankClosing::where('bank_id', $bank_id)->whereDate('entry_date', '<=', $entry_date)->orderby('entry_date', 'desc')->first();
               */
                //  
                $startDate = ($companyId == 1) ? date("Y-m-d", strtotime(convertDate('2021-08-05'))) : '';
                $bankBla = \App\Models\BankBalance::select('id', 'balance', 'bank_id')->where('bank_id', $bank_id)->where('account_id', $bank_ac_id)
                    ->when($startDate != '', function ($q) use ($startDate) {
                        $q->whereDate('entry_date', '>=', $startDate);
                    })->where('entry_date', '<=', $entry_date)
                    ->orderby('entry_date', 'desc')
                    ->sum('totalAmount');
                //  
                if ($bankBla) {
                    if ($amount > $bankBla) {
                        return back()->with('alert', 'Sufficient amount not available in bank account!');
                    }
                } else {
                    return back()->with('alert', 'Sufficient amount not available in bank account!');
                }
                if ($request['bank_mode'] == 0) {
                    $cheque_dd_no = $request['cheque_number'];
                    $online_payment_id = NULL;
                    $online_payment_by = NULL;
                    $paymentMode = 1;
                    $ssbpaymentMode = 1;
                    if ($branchId != $ssbAccountDetails->branch_id) {
                        $description = 'Cheque Withdrawal - From ' . $branchName . '';
                    } else {
                        $description = 'Cheque Withdrawal';
                    }
                } elseif ($request['bank_mode'] == 1) {
                    $cheque_dd_no = NULL;
                    $paymentMode = 3;
                    $ssbpaymentMode = 5;
                    $online_payment_id = $request['utr_no'];
                    $online_payment_by = $request['bank'];
                    if ($branchId != $ssbAccountDetails->branch_id) {
                        $description = 'Online Withdrawal - From ' . $branchName . '';
                    } else {
                        $description = 'Online Withdrawal';
                    }
                }
            }
            $ssb['saving_account_id'] = $ssbAccountDetails->id;
            $ssb['account_no'] = $request['ssb_account_number'];
            $ssb['opening_balance'] = $request['account_balance'] - $request['amount'];
            $ssb['branch_id'] = $branchId;
            $ssb['type'] = 5;
            $ssb['deposit'] = 0;
            $ssb['withdrawal'] = $request['amount'];
            $ssb['description'] = $description;
            $ssb['currency_code'] = 'INR';
            $ssb['payment_type'] = 'DR';
            $ssb['company_id'] = $companyId;
            $ssb['payment_mode'] = $ssbpaymentMode;
            $ssb['created_at'] = $request['created_at'];

            $ssb['reference_no'] = $dayBookRef;
            $ssb['daybook_ref_id'] = $dayBookRef;
            $ssb_transaction_id = $type_transaction_id = SavingAccountTranscation::insertGetId($ssb);
            // update saving account current balance
            $ssbBalance = SavingAccount::find($ssbAccountDetails->id);
            $ssbBalance->balance = $request['account_balance'] - $request['amount'];
            $ssbBalance->save();
            // $satRefId = $comman_transaction_controller->createTransactionReferences($ssb_transaction_id, $ssbAccountDetails->member_investments_id);
            $amountArraySsb = array('1' => $request['amount']);
            $amount_deposit_by_name = $ssbAccountDetails['ssbMemberCustomer2'] ? $ssbAccountDetails['ssbMemberCustomer2']->first_name . ' ' . $ssbAccountDetails['ssbMemberCustomer2']->last_name ?? '' : '';
            // $ssbCreateTran = $comman_transaction_controller->createTransaction($satRefId, 1, $ssbAccountDetails->id, $ssbAccountDetails->member_id, $branchId, $branchCode, $amountArraySsb, $paymentMode, $amount_deposit_by_name, $ssbAccountDetails->id, $request['ssb_account_number'], $cheque_dd_no, $bank_name = NULL, $branch_name = NULL, $request['created_at'], $online_payment_id, $online_payment_by, $ssbAccountDetails->id, 'DR');
            $totalbalance = $request['account_balance'] - $request['amount'];
            // $createDayBook = $comman_transaction_controller->createDayBook(NULL, NULL, 1, $ssbAccountDetails->member_investments_id, 0, $ssbAccountDetails->member_id, $totalbalance, 0, $request['amount'], $description, $request['ssb_account_number'], $branchId, $branchCode, $amountArraySsb, $paymentMode, $amount_deposit_by_name, $ssbAccountDetails->member_investments_id, $request['ssb_account_number'], $cheque_dd_no, NULL, NULL, $request['created_at'], $online_payment_by, $online_payment_by, $ssbAccountDetails->id, 'DR', $companyId);
            /************* Head Implement ****************/

            if ($request['payment_mode'] == 0) {
                $amount = $request['amount'];
                $type = 4;
                $sub_type = 43;
                $daybook_ref_id = $dayBookRef;

                $created_by_id = \Auth::user()->id;
                $created_by_name = \Auth::user()->username;
                $created_at = date("Y-m-d H:i:s", strtotime(convertDate($globaldate)));
                $updated_at = date("Y-m-d H:i:s", strtotime(convertDate($globaldate)));
                $payment_type = 'DR';
                $branch_id = $branchId;
                $member_id = $ssbAccountDetails->member_id;
                $payment_type = 'DR';
                $payment_mode = 0;
                $v_no = NULL;
                $v_date = NULL;
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
                $ssb_account_id_to = NULL;
                $cheque_bank_from_id = NULL;
                $cheque_bank_ac_from_id = NULL;
                $cheque_bank_to_name = NULL;
                $cheque_bank_to_branch = NULL;
                $cheque_bank_to_ac_no = NULL;
                $cheque_bank_to_ifsc = NULL;
                $transction_bank_from_id = NULL;
                $transction_bank_from_ac_id = NULL;
                $transction_bank_to_name = NULL;
                $transction_bank_to_ac_no = NULL;
                $transction_bank_to_branch = NULL;
                $transction_bank_to_ifsc = NULL;
                $amount_to_id = NULL;
                $amount_to_name = NULL;
                $amount_from_id = NULL;
                $amount_from_name = NULL;
                $branchDetail = getBranchDetail($branch_id);
                $SSBId = $type_id = $ssbAccountDetails->id;
                $SSBAccountNo = $ssbAccountDetails->account_no;
                $description_cr = 'To cash A/c Cr ' . $amount . '/-';
                $description_dr = 'SSB(' . $SSBAccountNo . ') A/c Dr ' . $amount . '/-';
                $des = $description = 'SSB A/c (' . $SSBAccountNo . ') withdrawal payment through cash ' . $branchDetail->name . '(' . $branchCode . ')';
                /// ------------------- branch daybook --------------
                $brDaybook = CommanTransactionsController::branchDayBookNew(
                    $daybook_ref_id,
                    $branch_id,
                    $type,
                    $sub_type,
                    $type_id,
                    $type_transaction_id,
                    $associate_id = NULL,
                    $member_id,
                    $branch_id_to = NULL,
                    $branch_id_from = NULL,
                    $amount,
                    $description,
                    $description_dr,
                    $description_cr,
                    'DR',
                    0,
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
                    $ssb_account_tran_id_to = NULL,
                    $ssb_account_tran_id_from = NULL,
                    $jv_unique_id = NULL,
                    $cheque_type = NULL,
                    $cheque_id = NULL,
                    $companyId
                );
                // branch cash head -mines ----------
                $head1C = 2;
                $head2C = 10;
                $head3C = 28;
                $head4C = 71;
                $head5C = NULL;
                $brDaybook = CommanTransactionsController::createAllHeadTransaction($daybook_ref_id, $branch_id, $bank_id = NULL, $bank_ac_id = NULL, $head3C, $type, $sub_type, $type_id, $type_transaction_id, $associate_id = NULL, $member_id, $branch_id_to = NULL, $branch_id_from = NULL, $amount, $description, 'CR', $payment_mode, $currency_code, $jv_unique_id = NULL, $v_no, $ssb_account_id_from, $ssb_account_id_to = NULL, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $cheque_type = NULL, $cheque_id = NULL, $cheque_no, $transction_no, $created_by, $created_by_id, $companyId);
                $allTranSSB = CommanTransactionsController::createAllHeadTransaction($daybook_ref_id, $branch_id, $bank_id = NULL, $bank_ac_id = NULL, $savingAccountHead, $type, $sub_type, $type_id, $type_transaction_id, $associate_id = NULL, $member_id, $branch_id_to = NULL, $branch_id_from = NULL, $amount, $description, $payment_type, $payment_mode, $currency_code, $jv_unique_id = NULL, $v_no, $ssb_account_id_from, $ssb_account_id_to = NULL, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $cheque_type = NULL, $cheque_id = NULL, $cheque_no, $transction_no, $created_by, $created_by_id, $companyId);
                // $branchClosing = CommanTransactionsController::checkCreateBranchClosingDr($branch_id, $created_at, $amount, 0);
                // $BranchCurrentBalance = CommanTransactionsController::checkCreateBranchCurrentBalanceDR($branch_id, $created_at, $amount, 0);
            }
            //------------------------ bank mode withdrawal start -----------------------/
            if ($request['payment_mode'] == 1) {
                $amount = $request['amount'];
                $amount1 = $request['amount'] + $request['rtgs_neft_charge'];

                $type = 4;
                $sub_type = 43;
                $daybook_ref_id = $dayBookRef;
                $globaldate = $request->created_at;
                Session::put('created_at', $request->created_at);
                $currency_code = 'INR';
                $entry_date = date("Y-m-d", strtotime(convertDate($globaldate)));
                $entry_time = date("H:i:s", strtotime(convertDate($globaldate)));
                $created_by = 2;
                $created_by_id = \Auth::user()->id;
                $created_by_name = \Auth::user()->username;
                $created_at = date("Y-m-d H:i:s", strtotime(convertDate($globaldate)));
                $updated_at = date("Y-m-d H:i:s", strtotime(convertDate($globaldate)));
                $payment_type = 'DR';
                $bank_id = $request->bank;
                $bank_ac_id = $request->bank_account_number;
                $bankDtail = getSamraddhBank($bank_id);
                $bankAcDetail = getSamraddhBankAccountId($bank_ac_id);
                // $bankBla = \App\Models\BranchCurrentBalance::where('branch_id', $branchId)->whereBetween('entry_date', ['2021-08-05', $entry_date])->sum('totalAmount');
                // $bankBla = \App\Models\SamraddhBankClosing::where('bank_id', $bank_id)->whereDate('entry_date', '<=', $entry_date)->orderby('entry_date', 'desc')->first();
                $startDate = ($companyId == 1) ? date("Y-m-d", strtotime(convertDate('2021-08-05'))) : '';
                $bankBla = \App\Models\BankBalance::where('bank_id', $request->bank)
                    ->where('account_id', $bank_ac_id)
                    ->when($startDate != '', function ($q) use ($startDate) {
                        $q->whereDate('entry_date', '>=', $startDate);
                    })
                    ->where('entry_date', '<=', $entry_date)
                    ->orderby('entry_date', 'desc')
                    ->sum('totalAmount');
                if ($bankBla) {
                    if ($amount > $bankBla) {
                        return back()->with('alert', 'Sufficient amount not available in bank account!');
                    }
                } else {
                    return back()->with('alert', 'Sufficient amount not available in bank account!');
                }
                $member_id = $ssbAccountDetails['ssbMemberCustomer']->id;
                $memberCode = $ssbAccountDetails['ssbMemberCustomer']['member']->member_id;
                $member_name = $ssbAccountDetails['ssbMemberCustomer']['member'] ? $ssbAccountDetails['ssbMemberCustomer']['member']->first_name . ' ' . $ssbAccountDetails['ssbMemberCustomer']['member']->last_name ?? '' : 'N/A';
                $SSBId = $type_id = $ssbAccountDetails->id;
                $SSBAccountNo = $ssbAccountDetails->account_no;
                $branch_id = $branchId;
                $amount_from_id = $bank_id;
                $amount_from_name = $bankDtail->bank_name . '(' . $bankAcDetail->account_no . ')';
                $amount_to_id = NULL;
                $amount_to_name = $member_name . '(' . $memberCode . ')';
                $opening_balance = NULL;
                $closing_balance = NULL;
                $v_no = NULL;
                $v_date = NULL;
                $ssb_account_id_from = NULL;
                $ssb_account_id_to = NULL;
                $cheque_type = NULL;
                $cheque_id = NULL;
                $cheque_no = NULL;
                $cheque_date = NULL;
                $cheque_bank_from = NULL;
                $cheque_bank_ac_from = NULL;
                $cheque_bank_ifsc_from = NULL;
                $cheque_bank_branch_from = NULL;
                $cheque_bank_to = NULL;
                $cheque_bank_ac_to = NULL;
                $cheque_bank_from_id = NULL;
                $cheque_bank_ac_from_id = NULL;
                $cheque_bank_to_name = NULL;
                $cheque_bank_to_branch = NULL;
                $cheque_bank_to_ac_no = NULL;
                $cheque_bank_to_ifsc = NULL;
                $transction_no = NULL;
                $transction_bank_from = NULL;
                $transction_bank_ac_from = NULL;
                $transction_bank_ifsc_from = NULL;
                $transction_bank_branch_from = NULL;
                $transction_bank_to = NULL;
                $transction_bank_ac_to = NULL;
                $transction_date = NULL;
                $transction_bank_from_id = NULL;
                $transction_bank_from_ac_id = NULL;
                $transction_bank_to_name = NULL;
                $transction_bank_to_ac_no = NULL;
                $transction_bank_to_branch = NULL;
                $transction_bank_to_ifsc = NULL;
                ;
                $is_contra = NULL;
                $contra_id = NULL;
                $description_cr = 'To Bank A/c Cr ' . $amount . '/-';
                $description_dr = 'SSB(' . $SSBAccountNo . ') A/c Dr ' . $amount . '/-';
                if ($request->bank_mode == 0) {
                    $cheque_type = 1;
                    $payment_mode = 1;
                    //-----------------------
                    $chequeIssue['cheque_id'] = $cheque_id = $request->cheque_number;
                    $chequeIssue['type'] = 8;
                    $chequeIssue['sub_type'] = 81;
                    $chequeIssue['type_id'] = $type_id;
                    $chequeIssue['cheque_issue_date'] = $entry_date;
                    $chequeIssue['created_at'] = $created_at;
                    $chequeIssue['updated_at'] = $updated_at;
                    $chequeIssueCreate = \App\Models\SamraddhChequeIssue::create($chequeIssue);
                    //------------------
                    $chequeUpdate['is_use'] = 1;
                    $chequeUpdate['status'] = 3;
                    $chequeUpdate['updated_at'] = $updated_at;
                    $chequeDataUpdate = \App\Models\SamraddhCheque::find($cheque_id);
                    $chequeDataUpdate->update($chequeUpdate);
                    $cheque_number = $chequeDataUpdate->cheque_no;
                    $des = $description = 'SSB A/c (' . $SSBAccountNo . ') withdrawal payment through cheque ' . $cheque_number;
                    $cheque_no = $cheque_number;
                    $cheque_date = $entry_date;
                    $cheque_bank_from = $bankDtail->bank_name;
                    $cheque_bank_ac_from = $bankAcDetail->account_no;
                    $cheque_bank_ifsc_from = $bankAcDetail->ifsc_code;
                    $cheque_bank_branch_from = $bankAcDetail->branch_name;
                    $cheque_bank_to = NULL;
                    $cheque_bank_ac_to = NULL;
                    $cheque_bank_from_id = $bank_id;
                    $cheque_bank_ac_from_id = $bank_ac_id;
                    $cheque_bank_to_name = NULL;
                    $cheque_bank_to_branch = NULL;
                    $cheque_bank_to_ac_no = NULL;
                    $cheque_bank_to_ifsc = NULL;
                }
                if ($request->bank_mode == 1) {
                    $cheque_type = NULL;
                    $cheque_id = NULL;
                    $payment_mode = 2;
                    $des = $description = 'SSB A/c (' . $SSBAccountNo . ') withdrawal payment through online ' . $request->utr_no;
                    $transction_no = $request->utr_no;
                    $transction_bank_from = $bankDtail->bank_name;
                    $transction_bank_ac_from = $bankAcDetail->account_no;
                    $transction_bank_ifsc_from = $bankAcDetail->ifsc_code;
                    $transction_bank_branch_from = $bankAcDetail->branch_name;
                    $transction_bank_to = NULL;
                    $transction_bank_ac_to = NULL;
                    $transction_date = $entry_date;
                    $transction_bank_from_id = $bank_id;
                    $transction_bank_from_ac_id = $bank_ac_id;
                    $transction_bank_to_name = $request->mbank;
                    $transction_bank_to_ac_no = $request->mbankac;
                    $transction_bank_to_branch = NULL;
                    $transction_bank_to_ifsc = $request->mbankifsc;
                    // --------- NEFT charge------------------
                    $allTranNeft = CommanTransactionsController::createAllHeadTransaction(
                        $daybook_ref_id,
                        $branch_id,
                        $bank_id,
                        $bank_ac_id,
                        92,
                        $type,
                        $sub_type,
                        $type_id,
                        $type_transaction_id,
                        $associate_id = NULL,
                        $member_id,
                        $branch_id_to = NULL,
                        $branch_id_from = NULL,
                        $request->rtgs_neft_charge,
                        $des,
                        'DR',
                        $payment_mode,
                        $currency_code,
                        $jv_unique_id = NULL,
                        $v_no,
                        $ssb_account_id_from,
                        $ssb_account_id_to,
                        $ssb_account_tran_id_to = NULL,
                        $ssb_account_tran_id_from = NULL,
                        $cheque_type,
                        $cheque_id,
                        $cheque_no,
                        $transction_no,
                        $created_by,
                        $created_by_id,
                        $companyId
                    );
                }
                $allTran2 = CommanTransactionsController::createAllHeadTransaction($daybook_ref_id, $branch_id, $bank_id, $bank_ac_id, $bankDtail->account_head_id, $type, $sub_type, $type_id, $type_transaction_id, $associate_id = NULL, $member_id, $branch_id_to = NULL, $branch_id_from = NULL, $amount1, $des, 'CR', $payment_mode, $currency_code, $jv_unique_id = NULL, $v_no, $ssb_account_id_from, $ssb_account_id_to, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $cheque_type, $cheque_id, $cheque_no, $transction_no, $created_by, $created_by_id, $companyId);
                $smbdc = CommanTransactionsController::samraddhBankDaybookNew($daybook_ref_id, $bank_id, $bank_ac_id, $type, $sub_type, $type_id, $type_transaction_id, $associate_id = NULL, $member_id, $branch_id, $amount1, $amount1, $amount1, $des, $description_dr, $description_cr, 'DR', $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $jv_unique_id = NULL, $cheque_type, $cheque_id, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $companyId);
                //-----------   bank balence  ---------------------------
                // $bankClosing = CommanTransactionsController::checkCreateBankClosingDR($bank_id, $bank_ac_id, $created_at, $amount1, 0);
                $allTran1 = CommanTransactionsController::createAllHeadTransaction($daybook_ref_id, $branch_id, $bank_id, $bank_ac_id, $savingAccountHead, $type, $sub_type, $type_id, $type_transaction_id, $associate_id = NULL, $member_id, $branch_id_to = NULL, $branch_id_from = NULL, $amount, $description, $payment_type, $payment_mode, $currency_code, $jv_unique_id = NULL, $v_no, $ssb_account_id_from, $ssb_account_id_to, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $cheque_type, $cheque_id, $cheque_no, $transction_no, $created_by, $created_by_id, $companyId);
                $brDaybook = CommanTransactionsController::branchDayBookNew(
                    $daybook_ref_id,
                    $branch_id,
                    $type,
                    $sub_type,
                    $type_id,
                    $type_transaction_id,
                    $associate_id = NULL,
                    $member_id,
                    $branch_id_to = NULL,
                    $branch_id_from = NULL,
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
                    $ssb_account_tran_id_to = NULL,
                    $ssb_account_tran_id_from = NULL,
                    $jv_unique_id = NULL,
                    $cheque_type,
                    $cheque_id,
                    $companyId
                );
            }
            /************* Head Implement ****************/
            SavingAccount::find($ssbAccountDetails->id)->update(['otp' => NULL, 'otp_valid_to' => NULL]);
            $branchDetail = \App\Models\Branch::where('id', $branchId)->first();
            $transferAmount = $branchDetail->transferrabel_amount;
            if (isset($branchDetail->first_login) && $branchDetail->first_login == 0) {
                if ($transferAmount == $request['amount']) {
                    $branchDetail->update(['first_login' => '1']);
                }
                if ($branchDetail->day_closing_amount < $branchDetail->cash_in_hand) {
                    $branchDetail->update(['day_closing_amount' => $branchDetail->day_closing_amount]);
                } else {
                    $branchDetail->update(['day_closing_amount' => $branchDetail->day_closing_amount - $request['amount'], 'transferrabel_amount' => $branchDetail->transferrabel_amount - $request['amount']]);
                }
            }
            $withdrawalDate = date("d M Y", strtotime(convertDate($request['created_at'])));
            $remainingBalance = $request['account_balance'] - $request['amount'];
            $contactNumber = array();
            $contactNumber[] = $ssbAccountDetails['ssbMemberCustomer2'] ? $ssbAccountDetails['ssbMemberCustomer2']->mobile_no : '';

            // $text = "Dear " . $ssbAccountDetails['ssbMemberCustomer2'] ? $ssbAccountDetails['ssbMemberCustomer2']->first_name . ' ' . $ssbAccountDetails['ssbMemberCustomer2']->last_name??'' :'' . ', ';
            // $text .= "Your A/c " . $ssbAccountDetails->account_no . " has been debited on " . $withdrawalDate . " with Rs. " . $request['amount'] . ". Cur Bal: " . $remainingBalance . ". Thanks";

            $nameSMS = $ssbAccountDetails['ssbMemberCustomer2'] ? $ssbAccountDetails['ssbMemberCustomer2']->first_name . ' ' . $ssbAccountDetails['ssbMemberCustomer2']->last_name ?? '' : '';
            $accountNoSMS = $ssbAccountDetails->account_no;
            $text = "Dear " . $nameSMS . ", Your A/c " . $accountNoSMS . " has been debited on " . $withdrawalDate . " with Rs. " . $request['amount'] . ". Cur Bal: " . $remainingBalance . ". Thanks";

            $numberWithMessage = array();
            $numberWithMessage['contactNumber'] = $contactNumber;
            $numberWithMessage['message'] = $text;
            $templateId = 1207161519057168367;
            $sendToMember = new Sms();
            $sendToMember->sendSms($contactNumber, $text, $templateId);
            if ($branchDetails->first_login == '0') {
                $branchDetails->update(['first_login' => '1']);
            }
            DB::commit();
            branchbalancecrone($branchDetails->manager_id, Permission::all());
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('alert', $ex->getMessage());
            // return back()->with('alert', $ex->getline());
        }
        return back()->with('success', 'Successfully withdrawal!');
    }
    /**
     * Send otp on Withdrawal
     */
    public function sendOtpToSSB(Request $request)
    {
        DB::beginTransaction();
        try {
            $accountNumber = $request->account_number;
            $otp = generateOtp();
            $numberWithMessage = array();
            $amount = $request->amount;
            $otpTime = Carbon::now()->addMinute()->toTimeString();
            $ssbAccount = \App\Models\SavingAccount::where('account_no', $accountNumber)->with('ssbmembersDataGet.member')->first();
            $updateSSbOtp = $ssbAccount->update(['otp' => $otp, 'otp_valid_to' => $otpTime]);
            $contactNumber = array();
            $contactNumber[] = $ssbAccount->ssbmembersDataGet ? $ssbAccount->ssbmembersDataGet->member->mobile_no : '';
            $newAccountNo = str_pad(substr($accountNumber, -4), strlen($accountNumber), 'X', STR_PAD_LEFT);
            $text = 'OTP for Withdrawal of Rs ' . $amount . ' from your Saving A/C ' . $newAccountNo . ' is ' . $otp . '. Samraddh Bestwin Microfinance';
            $numberWithMessage = array();
            $numberWithMessage['contactNumber'] = $contactNumber;
            $numberWithMessage['message'] = $text;
            $templateId = 1207167946798156050;
            $sendToMember = new Sms();
            $sendToMember->sendSms($contactNumber, $text, $templateId);
            $response = [
                'status' => 200,
                'message' => 'Otp Send SuccessFully',
            ];
            DB::commit();
        } catch (Exception $ex) {
            $response = [
                'status' => 500,
                'message' => $ex->getMessage()
            ];
        }
        return response()->json($response);
    }
    public function verifySSbOtp(Request $request)
    {
        $otp = $request->otp;
        $accountNumber = $request->accountNumber;
        $time = date(strtotime($request->currentTime));
        DB::beginTransaction();
        try {
            $getOtpDetails = SavingAccount::where('account_no', $accountNumber)->first(['otp', 'otp_valid_to']);
            $otpTime = date(strtotime($getOtpDetails->otp_valid_to));
            if ($otp == $getOtpDetails->otp) {
                $msg = 'OTP Verified Successfully';
                $status = 'VERIFIED';
                $code = 200;
            } elseif ($otp != $getOtpDetails->otp) {
                $msg = 'Otp is Incorrect';
                $status = 'INVALID';
                $code = 409;
            } elseif ($otpTime == NULL && $otp == NULL) {
                $msg = 'OTP Expired';
                $status = 'EXPIRED';
                $code = 410;
            } else {
                $msg = '';
                $status = '';
                $code = 0;
            }
            $response = [
                'msg' => $msg,
                'status' => $status,
                'code' => $code,
            ];
        } catch (Exception $ex) {
            $response = [
                'msg' => $ex->getMessage(),
                'status' => 'error',
                'code' => 500,
            ];
        }
        return response()->json($response);
    }
    public function updateSSbOtp(Request $request)
    {
        $accountNumber = $request->account_number;
        $updateSSB = SavingAccount::where('account_no', $accountNumber)->update(['otp' => NULL, 'otp_valid_to' => NULL]);
        return response()->json($updateSSB);
    }
}