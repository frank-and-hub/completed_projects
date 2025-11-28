<?php
namespace App\Http\Controllers\Admin\PaymentManagement;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use App\Models\Branch;
use App\Models\SavingAccount;
use App\Models\SavingAccountTranscation;
use App\Models\AccountHeads;
use App\Models\SamraddhBank;
use App\Models\SamraddhCheque;
use App\Models\BranchCash;
use App\Services\ImageUpload;
use Spatie\Permission\Models\Permission;
use Carbon\Carbon;
use URL;
use DB;
use Session;
use App\Http\Controllers\Admin\CommanController;
use App\Services\Sms;
use App\Interfaces\RepositoryInterface;

class WithdrawalController extends Controller
{
    private $repository;
    /**
     * Instantiate a new controller instance.
     *
     * @return void
     */
    public function __construct(RepositoryInterface $repository)
    {
        $this->repository = $repository;
        $this->middleware('auth');
    }
    /**
     * Amount withdrawal view.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (check_my_permission(Auth::user()->id, "42") != "1") {
            return redirect()->route('admin.dashboard');
        }
        if (Auth::user()->branch_id > 0) {
            $id = Auth::user()->branch_id;
            $data['branches'] = Branch::select('id', 'name', 'branch_code')->where('id', '=', $id)->get();
        } else {
            $data['branches'] = Branch::select('id', 'name', 'branch_code')->get();
        }
        $data['title'] = 'Saving Account Withdrawal';
        //$data['bank'] = AccountHeads::select('id','title','account_number')->where('account_type',2)->get();
        //$data['cheques'] = SamraddhCheque::select('cheque_no')->get();
        $data['bank'] = SamraddhBank::with('bankAccount:id,bank_id,account_no,branch_name')->where("status", "1")->get();
        //$data['cheques'] = SamraddhCheque::select('cheque_no')->get();

        return view('templates.admin.payment-management.withdrawal.withdrawal_ssb_amount', $data);
    }
    /**
     * Get ssb account details.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return JSON
     */
    public function accountDetails(Request $request)
    {
        $account_number = $request->account_number;
        $branchId = $request->branchId;
        $companyId = $request->companyId;
        $cDate = date("Y-m-d");
        $date = date("Y-m-d", strtotime(convertDate($request->date)));
        $todayTransaction = SavingAccountTranscation::whereAccountNo($account_number)->whereDescription('Cash Withdrawal')->whereDate('created_at', $cDate)->count();
        $transactionBydate = \App\Models\SavingAccountTransactionView::whereAccountNo($account_number)
            ->whereDate('opening_date', '<=', $date)
            ->orderBy('opening_date', 'desc')
            ->first();
        $ssbAccountDetails = SavingAccount::with(['ssbcustomerDataGet', 'ssbMemberCustomer'])->whereAccountNo($account_number)->whereCompanyId($companyId);
        if (Auth::user()->branch_id > 0) {
            $ssbAccountDetails = $ssbAccountDetails->where('branch_id', Auth::user()->branch_id);
        }
        $ssbAccountDetails = $ssbAccountDetails->get();
        $mb = 0;
        $memberBank = 0;
        if ($todayTransaction) {
            $memberBank = \App\Models\MemberBankDetail::where('member_id', $ssbAccountDetails[0]['member_id'])->first();
            if ($memberBank) {
                $mb = 1;
            }
        }
        $resCount = count($ssbAccountDetails);
        // pd($ssbAccountDetails[0]['ssbcustomerDataGet']['signature']);
        $signature = !empty($ssbAccountDetails[0]) ? $ssbAccountDetails[0]['ssbcustomerDataGet'] ? ImageUpload::fileExists('profile/member_signature/' . $ssbAccountDetails[0]['ssbcustomerDataGet']['signature']) ? ImageUpload::generatePreSignedUrl('profile/member_signature/' . $ssbAccountDetails[0]['ssbcustomerDataGet']['signature']) : '' : '' : '';
        $photo = !empty($ssbAccountDetails[0]) ? $ssbAccountDetails[0]['ssbcustomerDataGet'] ? ImageUpload::fileExists('profile/member_avatar/' . $ssbAccountDetails[0]['ssbcustomerDataGet']['photo']) ? ImageUpload::generatePreSignedUrl('profile/member_avatar/' . $ssbAccountDetails[0]['ssbcustomerDataGet']['photo']) : '' : '' : '';
        $return_array = compact('ssbAccountDetails', 'resCount', 'todayTransaction', 'transactionBydate', 'mb', 'memberBank', 'signature', 'photo');
        return json_encode($return_array);
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
            $entryTime = date("H:i:s");
            $globaldate = date("Y-m-d", strtotime(convertDate($request->date)));
            Session::put('created_at', $globaldate);
            $currency_code = 'INR';
            $entry_date = date("Y-m-d", strtotime(convertDate($globaldate)));
            $entry_time = date("H:i:s", strtotime(convertDate($globaldate)));
            $comman_controller = new CommanController;
            // $ssbAccountDetails = SavingAccount::with(['ssbMemberCustomer2', 'company'])->where('account_no', $request['ssb_account_number'])->first();
            $ssbAccountDetails = SavingAccount::with([
                'ssbMemberCustomer.member:id,member_id,first_name,last_name,mobile_no',
                'ssbMemberCustomer2:id,member_id,first_name,last_name,mobile_no',
                'company'
            ])->where('account_no', $request['ssb_account_number'])->first();
            $amount = $request['amount'];
            $branchName = getBranchDetail($request['branch_id']);
            $companyName = getCompanyDetail($request['company_id']);
            $branchId = $request['branch_id'];
            $companyId = $request['company_id'];
            $bank_ac_id = $request->bank_account_number;
            $savingAccountHead = getPlanDetailByCompany($companyId);
            $dayBookRef = CommanController::createBranchDayBookReference($request['amount']);
            $getBranchAmount = \App\Models\Branch::whereId($branchId)->value('total_amount');
            $branch_total_amount = $companyId == 1 ? $getBranchAmount : 0;
            $startDate = ($companyId == 1) ? date("Y-m-d", strtotime(convertDate('2021-08-05'))) : '';

            if ($request['payment_mode'] == 0) {
                $cheque_dd_no = NULL;
                $online_payment_id = NULL;
                $online_payment_by = NULL;
                $paymentMode = 0;
                $ssbpaymentMode = 0;
                if ($branchId != $ssbAccountDetails->branch_id) {
                    $description = 'Cash Withdrawal - From ' . $branchName->name . '';
                } else {
                    $description = 'Cash Withdrawal';
                }
                $bankBla = \App\Models\BranchCurrentBalance::where('branch_id', $branchId)->where('company_id', $companyId)->when($startDate != '', function ($q) use ($startDate) {
                    $q->whereDate('entry_date', '>=', $startDate);
                })->where('entry_date', '<=', $entry_date);
                if ($companyId != '') {
                    $bankBla = $bankBla->where('company_id', $companyId);
                }
                $bankBla = $bankBla->sum('totalAmount');
                $balance = number_format((float) ($bankBla ?? 0.00), 2, '.', '');
                if (($balance + $branch_total_amount) < $amount) {
                    return back()->with('alert', 'Sufficient amount not available in branch cash!');
                }
            } elseif ($request['payment_mode'] == 1) {
                $bank_id = $request->bank;
                $bankDtail = getSamraddhBank($bank_id);
                $bankAcDetail = getSamraddhBankAccountId($bank_ac_id);
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
                    if (($amount > ($bankBla)) && $bank_ac_id != 2) {
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
                        $description = 'Cheque Withdrawal - From ' . $branchName->name . '';
                    } else {
                        $description = 'Cheque Withdrawal';
                    }
                } elseif ($request['bank_mode'] == 1) {
                    $cheque_dd_no = NULL;
                    $paymentMode = 3;
                    $ssbpaymentMode = 3;
                    $online_payment_id = $request['utr_no'];
                    $online_payment_by = $request['bank'];
                    if ($branchId != $ssbAccountDetails->branch_id) {
                        $description = 'Online Withdrawal - From ' . $branchName->name . '';
                    } else {
                        $description = 'Online Withdrawal';
                    }
                }
            }
            $ssbGet = SavingAccountTranscation::where(['saving_account_id' => $ssbAccountDetails->id, 'company_id' => $companyId])
                ->whereDate('created_at', '<=', date("Y-m-d", strtotime(convertDate($request->date))))
                ->orderby('created_at', 'DESC')
                ->orderby('id', 'DESC')
                ->first();
            $ssb['saving_account_id'] = $ssbAccountDetails->id;
            $ssb['account_no'] = $request['ssb_account_number'];
            if ($ssbGet) {
                $ssb['opening_balance'] = $ssbGet->opening_balance - $request['amount'];
                $ssb['amount'] = $ssbGet->opening_balance - $request['amount'];
                $entryDatessb = date("Y-m-d", strtotime(convertDate($request->date)));
                $entryTimessb = date("H:i:s", strtotime(convertDate($ssbGet->created_at)));
                $SSBDatenew = $entryDatessb . ' ' . $entryTimessb;
                $ssb['created_at'] = date("Y-m-d " . $entryTime . "", strtotime($SSBDatenew));
            } else {
                $SSBDatenew = $request->date;
                $ssb['opening_balance'] = $request['amount'];
                $ssb['amount'] = $request['amount'];
                $ssb['created_at'] = date("Y-m-d " . $entryTime . "", strtotime($request->date));
            }
            // $ssb['opening_balance'] = $ssbGet ? $ssbGet->opening_balance - $request['amount'] : $request['amount'];
            // $ssb['amount'] = $ssbGet ? $ssbGet->opening_balance - $request['amount'] : $request['amount'];
            // $ssb['created_at'] = $ssbGet ? date("Y-m-d " . $entryTime . "", strtotime(date("Y-m-d", strtotime(convertDate($request->date))) . ' ' . date("H:i:s", strtotime(convertDate($ssbGet->created_at))))) : date("Y-m-d " . $entryTime . "", strtotime($request->date));
            $ssb['branch_id'] = $request['branch_id'];
            $ssb['company_id'] = $request['company_id'];
            $ssb['type'] = 5;
            $ssb['deposit'] = 0;
            $ssb['withdrawal'] = $request['amount'];
            $ssb['description'] = $description;
            $ssb['currency_code'] = 'INR';
            $ssb['payment_type'] = 'DR';
            $ssb['payment_mode'] = $ssbpaymentMode;
            $ssb['daybook_ref_id'] = $dayBookRef;
            $currentDateRecord = SavingAccountTranscation::where(['company_id' => $companyId, 'saving_account_id' => $ssbAccountDetails->id])->whereDate('created_at', '<=', date("Y-m-d", strtotime(convertDate($request->date))))->orderby('id', 'DESC')->first();
            if ($currentDateRecord) {
                $data['opening_balance'] = $currentDateRecord->opening_balance - $request['amount'];
                $data['updated_at'] = date("Y-m-d", strtotime(convertDate($request->date)));
                $getNextBranchClosingRecord = SavingAccountTranscation::where(['company_id' => $companyId, 'account_no' => $request['ssb_account_number']])->whereDate('created_at', '>', date("Y-m-d", strtotime(convertDate($request->date))))->orderby('created_at', 'ASC')->get();
                if ($getNextBranchClosingRecord) {
                    foreach ($getNextBranchClosingRecord as $key => $value) {
                        $sResult = SavingAccountTranscation::find($value->id);
                        $sData['opening_balance'] = $value->opening_balance - $request['amount'];
                        $sData['updated_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($request->date)));
                        $sResult->update($sData);
                    }
                }
            }

            $ssb['reference_no'] = $dayBookRef;
            $ssb_transaction_id = $type_transaction_id = SavingAccountTranscation::insertGetId($ssb);
            // $ssb_transaction_id =   = $ssbAccountTran;     
            // update saving account current balance 
            $ssbBalance = SavingAccount::find($ssbAccountDetails->id);
            $ssbBalance->balance = $request['account_balance'] - $request['amount'];
            $ssbBalance->save();
            $ssbss = array("ssb" => $ssb, "ssbBalance" => $ssbBalance);
            $encodeDate = json_encode($ssbss);
            $arrs = [
                "saving_account_transaction_id" => $ssb_transaction_id,
                "type" => "9",
                "account_head_id" => 0,
                "user_id" => Auth::user()->id,
                "message" => "SSB_Withdraw",
                "data" => $encodeDate
            ];
            // DB::table('user_log')->insert($arrs);
            // $satRefId = $comman_controller->createTransactionReferences($ssb_transaction_id,$ssbAccountDetails->member_investments_id);
            $amountArraySsb = ['1' => $request['amount']];
            $amount_deposit_by_name = $ssbAccountDetails['ssbMemberCustomer']['member']->first_name . ' ' . $ssbAccountDetails['ssbMemberCustomer']['member']->last_name;
            // $ssbCreateTran = $comman_controller->createTransaction($satRefId,1,$ssbAccountDetails->id,$ssbAccountDetails->member_id,$request['branch_id'],$request['branch_code'],$amountArraySsb,$paymentMode,$amount_deposit_by_name,$ssbAccountDetails->id,$request['ssb_account_number'],$cheque_dd_no,$bank_name=NULL,$branch_name=NULL,date("Y-m-d", strtotime(convertDate($request->date))),$online_payment_id,$online_payment_by,$ssbAccountDetails->id,'DR');
            $totalbalance = $request['account_balance'] - $request['amount'];
            // $createDayBook = $comman_controller->createDayBook(NULL, NULL, 1, $ssbAccountDetails->member_investments_id, 0, $ssbAccountDetails->member_id, $totalbalance, 0, $request['amount'], $description, $request['ssb_account_number'], $request['branch_id'], $request['branch_code'], $amountArraySsb, $paymentMode, $amount_deposit_by_name, $ssbAccountDetails->member_investments_id, $request['ssb_account_number'], $cheque_dd_no, NULL, NULL, date("Y-m-d", strtotime(convertDate($request->date))), $online_payment_id, $online_payment_by, $ssbAccountDetails->id, 'DR',$companyId);
            /************* Head Implement ****************/

            if ($request['payment_mode'] == 0) {
                $amount = $request['amount'];
                $type = 4;
                $sub_type = 43;
                $daybook_ref_id = $dayBookRef;
                $created_by = 1;
                $created_by_id = \Auth::user()->id;
                $created_by_name = \Auth::user()->username;
                $created_at = date("Y-m-d H:i:s", strtotime(convertDate($globaldate)));
                $updated_at = date("Y-m-d H:i:s", strtotime(convertDate($globaldate)));
                $payment_type = 'DR';
                $branch_id = $request['branch_id'];
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
                $is_contra = NULL;
                $contra_id = NULL;
                $amount_to_id = NULL;
                $amount_to_name = NULL;
                $amount_from_id = NULL;
                $amount_from_name = NULL;
                $branchDetail = getBranchDetail($branch_id);
                $SSBId = $type_id = $ssbAccountDetails->id;
                $SSBAccountNo = $ssbAccountDetails->account_no;
                $description_cr = 'To cash A/c Cr ' . $amount . '/-';
                $description_dr = 'SSB(' . $SSBAccountNo . ') A/c Dr ' . $amount . '/-';
                $des = $description = 'SSB A/c (' . $SSBAccountNo . ') withdrawal payment through cash ' . $branchDetail->name . '(' . $branchDetail->branch_code . ')';

                /// ------------------- branch daybook -------------- 
                $brDaybook = CommanController::branchDayBookNew($daybook_ref_id, $branch_id, $type, $sub_type, $type_id, $type_transaction_id, $associate_id = NULL, $member_id, $branch_id_to = NULL, $branch_id_from = NULL, $amount, $description, $description_dr, $description_cr, 'DR', 0, $currency_code, $v_no = NULL, $ssb_account_id_from = NULL, $cheque_no = NULL, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL, $companyId);

                // branch cash head -mines ----------  
                $head1C = 2;
                $head2C = 10;
                $head3C = 28;
                $head4C = 71;
                $head5C = NULL;

                $brDaybook = CommanController::createAllHeadTransaction($daybook_ref_id, $branch_id, $bank_id = NULL, $bank_ac_id = NULL, $head3C, $type, $sub_type, $type_id, $type_transaction_id, $associate_id = NULL, $member_id, $branch_id_to = NULL, $branch_id_from = NULL, $amount, $description, 'CR', $payment_mode, $currency_code, $jv_unique_id = NULL, $v_no, $ssb_account_id_from, $ssb_account_id_to = NULL, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $cheque_type = NULL, $cheque_id = NULL, $cheque_no, $transction_no = NULL, $created_by, $created_by_id, $companyId);

                $allTranSSB = CommanController::createAllHeadTransaction($daybook_ref_id, $branch_id, $bank_id = NULL, $bank_ac_id = NULL, $savingAccountHead, $type, $sub_type, $type_id, $type_transaction_id, $associate_id = NULL, $member_id, $branch_id_to = NULL, $branch_id_from = NULL, $amount, $description, $payment_type, $payment_mode, $currency_code, $jv_unique_id = NULL, $v_no, $ssb_account_id_from, $ssb_account_id_to = NULL, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $cheque_type = NULL, $cheque_id = NULL, $cheque_no, $transction_no, $created_by, $created_by_id, $companyId);
                // both code's are commentedby saurab beacuse tabel are deleted from Database 
                // $branchClosing = $this->updateBranchCashDr($branch_id, $created_at, $amount, 0);

                // both code's are commentedby saurab beacuse tabel are deleted from Database 
                // $branchCash = $this->updateBranchClosingCashDr($branch_id, $created_at, $amount, 0);
            }
            //------------------------ bank mode withdrawal start -----------------------/
            if ($request['payment_mode'] == 1) {
                $amount = $request['amount'];
                $amount1 = $request['amount'] + $request['rtgs_neft_charge'];
                $nftAmount = $request['rtgs_neft_charge'];
                $type = 4;
                $sub_type = 43;
                $daybook_ref_id = $dayBookRef;
                $globaldate = date("Y-m-d", strtotime(convertDate($request->date)));
                Session::put('created_at', date("Y-m-d", strtotime(convertDate($request->date))));
                $currency_code = 'INR';
                $entry_date = date("Y-m-d", strtotime(convertDate($globaldate)));
                $entry_time = date("H:i:s", strtotime(convertDate($globaldate)));
                $created_by = 1;
                $created_by_id = \Auth::user()->id;
                $created_by_name = \Auth::user()->username;
                $created_at = date("Y-m-d H:i:s", strtotime(convertDate($globaldate)));
                $updated_at = date("Y-m-d H:i:s", strtotime(convertDate($globaldate)));
                $payment_type = 'DR';
                $bank_id = $request->bank;
                $bank_ac_id = $request->bank_account_number;
                $bankDtail = getSamraddhBank($bank_id);
                $bankAcDetail = getSamraddhBankAccountId($bank_ac_id);
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
                    if (($amount > $bankBla) && $bank_ac_id != 2) {
                        return back()->with('alert', 'Sufficient amount not available in bank account!');
                    }
                } else {
                    return back()->with('alert', 'Sufficient amount not available in bank account!');
                }
                $member_id = $ssbAccountDetails['ssbMemberCustomer']->id;
                $memberCode = $ssbAccountDetails['ssbMemberCustomer']['member']->member_id;
                $member_name = $ssbAccountDetails['ssbMemberCustomer']['member']->first_name . ' ' . $ssbAccountDetails['ssbMemberCustomer']['member']->last_name;
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
                    $desNft = $description = 'SSB A/c (' . $SSBAccountNo . ') withdrawal payment - NEFT Charges ' . $nftAmount;
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
                    $description_dr_nft = 'SSB(' . $SSBAccountNo . ') A/c Dr - NEFT Charges' . $nftAmount . '/-';
                    $description_cr_nft = 'To Bank A/c Cr - NEFT Charges' . $nftAmount . '/-';
                    // --------- NEFT charge------------------

                    $allTranNeftDR = CommanController::createAllHeadTransaction($daybook_ref_id, 29, $bank_id, $bank_ac_id, 92, $type, $sub_type, $type_id, $type_transaction_id, $associate_id = NULL, $member_id, $branch_id_to = NULL, $branch_id_from = NULL, $request->rtgs_neft_charge, $desNft, 'DR', $payment_mode, $currency_code, $jv_unique_id = NULL, $v_no, $ssb_account_id_from, $ssb_account_id_to, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $cheque_type, $cheque_id, $cheque_no, $transction_no, $created_by, $created_by_id, $companyId);

                    $allTranNeftCR = CommanController::createAllHeadTransaction($daybook_ref_id, 29, $bank_id, $bank_ac_id, 92, $type, $sub_type, $type_id, $type_transaction_id, $associate_id = NULL, $member_id, $branch_id_to = NULL, $branch_id_from = NULL, $request->rtgs_neft_charge, $desNft, 'CR', $payment_mode, $currency_code, $jv_unique_id = NULL, $v_no, $ssb_account_id_from, $ssb_account_id_to, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $cheque_type, $cheque_id, $cheque_no, $transction_no, $created_by, $created_by_id, $companyId);

                    //---------------     NFT update    ---------------

                    $nft = CommanController::samraddhBankDaybookNew($daybook_ref_id, $bank_id, $bank_ac_id, $type, $sub_type, $type_id, $type_transaction_id, $associate_id = NULL, $member_id, 29, $nftAmount, $nftAmount, $nftAmount, $desNft, $description_dr_nft, $description_cr_nft, 'DR', $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $jv_unique_id = NULL, $cheque_type, $cheque_id, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $companyId);

                    // NFT for Branch Day book -------------------

                    $brDaybook = CommanController::branchDayBookNew($daybook_ref_id, 29, $type, $sub_type, $type_id, $type_transaction_id, $associate_id = NULL, $member_id, $branch_id_to = NULL, $branch_id_from = NULL, $nftAmount, $description, $description_dr_nft, $description_cr_nft, $payment_type, $payment_mode, $currency_code, $v_no = NULL, $ssb_account_id_from = NULL, $cheque_no = NULL, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $jv_unique_id = NULL, $cheque_type, $cheque_id, $companyId);
                }

                $allTran2 = CommanController::createAllHeadTransaction($daybook_ref_id, $branch_id, $bank_id, $bank_ac_id, $bankDtail->account_head_id, $type, $sub_type, $type_id, $type_transaction_id, $associate_id = NULL, $member_id, $branch_id_to = NULL, $branch_id_from = NULL, $amount, $des, 'CR', $payment_mode, $currency_code, $jv_unique_id = NULL, $v_no, $ssb_account_id_from, $ssb_account_id_to, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $cheque_type, $cheque_id, $cheque_no, $transction_no, $created_by, $created_by_id, $companyId);

                // code commented by sourab for making changes in nft changers entry only 
                $smbdc = CommanController::samraddhBankDaybookNew($daybook_ref_id, $bank_id, $bank_ac_id, $type, $sub_type, $type_id, $type_transaction_id, $associate_id = NULL, $member_id, $branch_id, $amount, $amount, $amount, $des, $description_dr, $description_cr, 'DR', $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $jv_unique_id = NULL, $cheque_type, $cheque_id, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $companyId);


                //-----------   bank balence  ---------------------------

                // $bankClosing = $this->updateBankClosingDR($bank_id, $bank_ac_id, $created_at, $amount1, 0);

                $allTran1 = CommanController::createAllHeadTransaction($daybook_ref_id, $branch_id, $bank_id, $bank_ac_id, $savingAccountHead, $type, $sub_type, $type_id, $type_transaction_id, $associate_id = NULL, $member_id, $branch_id_to = NULL, $branch_id_from = NULL, $amount, $description, $payment_type, $payment_mode, $currency_code, $jv_unique_id = NULL, $v_no, $ssb_account_id_from, $ssb_account_id_to, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $cheque_type, $cheque_id, $cheque_no, $transction_no, $created_by, $created_by_id, $companyId);

                $brDaybook = CommanController::branchDayBookNew($daybook_ref_id, $branch_id, $type, $sub_type, $type_id, $type_transaction_id, $associate_id = NULL, $member_id, $branch_id_to = NULL, $branch_id_from = NULL, $amount, $description, $description_dr, $description_cr, $payment_type, $payment_mode, $currency_code, $v_no = NULL, $ssb_account_id_from = NULL, $cheque_no = NULL, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $jv_unique_id = NULL, $cheque_type, $cheque_id, $companyId);

                // both code's are commentedby saurab beacuse tabel are deleted from Database 
                // $branchClosing = $this->updateBranchClosingCashDr($branch_id, $created_at, $amount, 0);
            }
            /************* Head Implement ****************/
            $withdrawalDate = date("Y-m-d", strtotime(convertDate($request->date)));
            $remainingBalance = $request['account_balance'] - $request['amount'];
            $contactNumber = array();
            $contactNumber[] = $ssbAccountDetails['ssbMemberCustomer']['member']->mobile_no;
            // $text = "Dear " . $ssbAccountDetails['ssbMemberCustomer']['member']->first_name . ' ' . $ssbAccountDetails['ssbMemberCustomer']['member']->last_name . ', ';
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
            if ($branchName->first_login == '0') {
                $branchName->update(['first_login' => '1']);
            }
            DB::commit();
            branchbalancecrone($branchName->manager_id, Permission::all());
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('alert', $ex->getMessage());
            // return back()->with('alert', $ex->getLine());
        }
        return back()->with('success', 'Successfully withdrawal!');
    }
    public function getMicroDayBookAmount(Request $request)
    {
        $branchId = $request->branchId;
        $date = date("Y-m-d", strtotime(convertDate($request->date)));
        $microLoanRes = \App\Models\BranchCurrentBalance::where('branch_id', $branchId)->whereDate('entry_date', '<=', $date)->sum('totalAmount');
        // $microLoanRes = BranchCash::select('balance', 'loan_balance')->where('branch_id', $branchId)->whereDate('entry_date', '<=', $date)->orderby('entry_date', 'desc')->first();
        // $bankBla=\App\Models\BranchCash:: where('branch_id',$branchId)->whereDate('entry_date','<=',$entry_date)->orderby('entry_date','desc')->first();
        if ($microLoanRes) {
            $microAmount = (int) $microLoanRes;
        } else {
            $microAmount = 0;
        }
        $return_array = compact('microAmount');
        return json_encode($return_array);
    }
    public static function updateBranchCashDr($branch_id, $date, $amount, $type)
    {
        $entryDate = date("Y-m-d", strtotime(convertDate($date)));
        $entryTime = date("H:i:s", strtotime(convertDate($date)));
        // $currentDateRecord = \App\Models\BranchCash::where('branch_id', $branch_id)->whereDate('entry_date', $entryDate)->first();
        $currentDateRecord = \App\Models\BranchCurrentBalance::where('branch_id', $branch_id)->whereDate('entry_date', '<=', $entryDate)->sum('totalAmount');
        if ($currentDateRecord) {
            $Result = \App\Models\BranchCurrentBalance::find($currentDateRecord->id);
            if ($type == 0) {
                $data['balance'] = $currentDateRecord->balance - $amount;
            } elseif ($type == 1) {
                $data['loan_balance'] = $currentDateRecord->loan_balance - $amount;
            }
            $data['updated_at'] = $date;
            $Result->update($data);
            $insertid = $currentDateRecord->id;
            $getNextBranchRecord = \App\Models\BranchCurrentBalance::where('branch_id', $branch_id)->whereDate('entry_date', '<=', $entryDate)->sum('totalAmount');
            // $getNextBranchRecord = \App\Models\BranchCash::where('branch_id', $branch_id)->whereDate('entry_date', '>', $entryDate)->orderby('entry_date', 'ASC')->get();
            if ($getNextBranchRecord) {
                foreach ($getNextBranchRecord as $key => $value) {
                    $sResult = \App\Models\BranchCurrentBalance::find($value->id);
                    if ($type == 1) {
                        $sData['loan_opening_balance'] = $value->loan_closing_balance;
                        $sData['loan_balance'] = $value->loan_balance - $amount;
                        if ($value->closing_balance > 0) {
                            $sData['loan_closing_balance'] = $value->loan_closing_balance - $amount;
                        }
                    } elseif ($type == 0) {
                        $sData['opening_balance'] = $value->closing_balance;
                        $sData['balance'] = $value->balance - $amount;
                        if ($value->closing_balance > 0) {
                            $sData['closing_balance'] = $value->closing_balance - $amount;
                        }
                    }
                    $sData['updated_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($entryDate)));
                    $sResult->update($sData);
                }
            }
        } else {
            $oldDateRecord = \App\Models\BranchCurrentBalance::where('branch_id', $branch_id)->whereDate('entry_date', '<=', $entryDate)->sum('totalAmount');
            // $oldDateRecord = \App\Models\BranchCash::where('branch_id', $branch_id)->whereDate('entry_date', '<', $entryDate)->orderby('entry_date', 'DESC')->first();
            if ($oldDateRecord) {
                $Result1 = \App\Models\BranchCurrentBalance::find($oldDateRecord->id);
                $data1['closing_balance'] = $oldDateRecord->balance;
                $data1['loan_closing_balance'] = $oldDateRecord->loan_balance;
                $Result1->update($data1);
                $insertid1 = $oldDateRecord->id;
                $data1RecordExists = \App\Models\BranchCurrentBalance::where('branch_id', $branch_id)->whereDate('entry_date', '<=', $entryDate)->sum('totalAmount');
                // $data1RecordExists = \App\Models\BranchCash::where('branch_id', $branch_id)->whereDate('entry_date', '>', $entryDate)->orderby('entry_date', 'ASC')->get();
                if ($type == 0) {
                    $data['balance'] = $oldDateRecord->balance - $amount;
                } else {
                    $data['balance'] = $oldDateRecord->balance;
                }
                if ($type == 1) {
                    $data['loan_balance'] = $oldDateRecord->loan_balance - $amount;
                } else {
                    $data['loan_balance'] = $oldDateRecord->loan_balance;
                }
                if ($data1RecordExists) {
                    if ($type == 0) {
                        $data['closing_balance'] = $oldDateRecord->balance - $amount;
                    } else {
                        $data['closing_balance'] = $oldDateRecord->balance;
                    }
                    if ($type == 1) {
                        $data['loan_closing_balance'] = $oldDateRecord->loan_balance - $amount;
                    } else {
                        $data['loan_closing_balance'] = $oldDateRecord->loan_balance;
                    }
                    foreach ($data1RecordExists as $key => $value) {
                        $sResult = \App\Models\BranchCurrentBalance::find($value->id);
                        if ($type == 1) {
                            $sData['loan_opening_balance'] = $value->loan_closing_balance;
                            $sData['loan_balance'] = $value->loan_balance - $amount;
                            if ($value->closing_balance > 0) {
                                $sData['loan_closing_balance'] = $value->loan_closing_balance - $amount;
                            }
                        } elseif ($type == 0) {
                            $sData['opening_balance'] = $value->closing_balance;
                            $sData['balance'] = $value->balance - $amount;
                            if ($value->closing_balance > 0) {
                                $sData['closing_balance'] = $value->closing_balance - $amount;
                            }
                        }
                        $sData['updated_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($entryDate)));
                        $sResult->update($sData);
                    }
                } else {
                    $data['closing_balance'] = 0;
                    $data['loan_closing_balance'] = 0;
                }
                $data['opening_balance'] = $oldDateRecord->balance;
                $data['loan_opening_balance'] = $oldDateRecord->loan_balance;
                $data['branch_id'] = $branch_id;
                $data['entry_date'] = $entryDate;
                $data['entry_time'] = $entryTime;
                $data['type'] = $type;
                $data['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($date)));
                $transcation = \App\Models\BranchCurrentBalance::create($data);
                $insertid = $transcation->id;
            } else {
                if ($type == 0) {
                    $data['balance'] = 0 - $amount;
                } else {
                    $data['balance'] = 0;
                }
                if ($type == 1) {
                    $data['loan_balance'] = 0 - $amount;
                } else {
                    $data['loan_balance'] = 0;
                }
                $data['opening_balance'] = 0;
                $data['closing_balance'] = 0;
                $data['loan_opening_balance'] = 0;
                $data['loan_closing_balance'] = 0;
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
    public static function updateBranchClosingCashDr($branch_id, $date, $amount, $type)
    {
        $entryDate = date("Y-m-d", strtotime(convertDate($date)));
        $entryTime = date("H:i:s", strtotime(convertDate($date)));
        $currentDateRecord = \App\Models\BranchClosing::where('branch_id', $branch_id)->whereDate('entry_date', $entryDate)->first();
        if ($currentDateRecord) {
            $Result = \App\Models\BranchClosing::find($currentDateRecord->id);
            if ($type == 0) {
                $data['balance'] = $currentDateRecord->balance - $amount;
            } elseif ($type == 1) {
                $data['loan_balance'] = $currentDateRecord->loan_balance - $amount;
            }
            $data['updated_at'] = $date;
            $Result->update($data);
            $getNextBranchClosingRecord = \App\Models\BranchClosing::where('branch_id', $branch_id)->whereDate('entry_date', '>', $entryDate)->orderby('entry_date', 'ASC')->get();
            if ($getNextBranchClosingRecord) {
                foreach ($getNextBranchClosingRecord as $key => $value) {
                    $sResult = \App\Models\BranchClosing::find($value->id);
                    if ($type == 1) {
                        $sData['loan_opening_balance'] = $value->loan_closing_balance;
                        $sData['loan_balance'] = $value->loan_balance - $amount;
                        if ($value->closing_balance > 0) {
                            $sData['loan_closing_balance'] = $value->loan_closing_balance - $amount;
                        }
                    } elseif ($type == 0) {
                        $sData['opening_balance'] = $value->closing_balance;
                        $sData['balance'] = $value->balance - $amount;
                        if ($value->closing_balance > 0) {
                            $sData['closing_balance'] = $value->closing_balance - $amount;
                        }
                    }
                    $sData['updated_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($entryDate)));
                    $sResult->update($sData);
                }
            }
        } else {
            $oldDateRecord = \App\Models\BranchClosing::where('branch_id', $branch_id)->whereDate('entry_date', '<', $entryDate)->orderby('entry_date', 'DESC')->first();
            if ($oldDateRecord) {
                $Result1 = \App\Models\BranchClosing::find($oldDateRecord->id);
                $data1['closing_balance'] = $oldDateRecord->balance;
                $data1['loan_closing_balance'] = $oldDateRecord->loan_balance;
                $Result1->update($data1);
                $insertid1 = $oldDateRecord->id;
                $data1RecordExists = \App\Models\BranchClosing::where('branch_id', $branch_id)->whereDate('entry_date', '>', $entryDate)->orderby('entry_date', 'ASC')->get();
                if ($type == 0) {
                    $data['balance'] = $oldDateRecord->balance - $amount;
                } else {
                    $data['balance'] = $oldDateRecord->balance;
                }
                if ($type == 1) {
                    $data['loan_balance'] = $oldDateRecord->loan_balance - $amount;
                } else {
                    $data['loan_balance'] = $oldDateRecord->loan_balance;
                }
                $data['opening_balance'] = $oldDateRecord->balance;
                $data['loan_opening_balance'] = $oldDateRecord->loan_balance;
                if ($data1RecordExists) {
                    if ($type == 0) {
                        $data['closing_balance'] = $oldDateRecord->balance - $amount;
                    } else {
                        $data['closing_balance'] = $oldDateRecord->balance;
                    }
                    if ($type == 1) {
                        $data['loan_closing_balance'] = $oldDateRecord->loan_balance - $amount;
                    } else {
                        $data['loan_closing_balance'] = $oldDateRecord->loan_balance;
                    }
                    foreach ($data1RecordExists as $key => $value) {
                        $sResult = \App\Models\BranchClosing::find($value->id);
                        if ($type == 1) {
                            $sData['loan_opening_balance'] = $value->loan_closing_balance;
                            $sData['loan_balance'] = $value->loan_balance - $amount;
                            if ($value->closing_balance > 0) {
                                $sData['loan_closing_balance'] = $value->loan_closing_balance - $amount;
                            }
                        } elseif ($type == 0) {
                            $sData['opening_balance'] = $value->closing_balance;
                            $sData['balance'] = $value->balance - $amount;
                            if ($value->closing_balance > 0) {
                                $sData['closing_balance'] = $value->closing_balance - $amount;
                            }
                        }
                        $sData['updated_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($entryDate)));
                        $sResult->update($sData);
                    }
                } else {
                    $data['closing_balance'] = 0;
                    $data['loan_closing_balance'] = 0;
                }
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
                $data['loan_opening_balance'] = 0;
                $data['loan_closing_balance'] = 0;
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
    public static function updateBankClosingDR($bank_id, $account_id, $date, $amount, $type)
    {
        $entryDate = date("Y-m-d", strtotime(convertDate($date)));
        $entryTime = date("H:i:s");
        $currentDateRecord = \App\Models\SamraddhBankClosing::where('bank_id', $bank_id)->where('account_id', $account_id)->whereDate('entry_date', $entryDate)->first();
        if ($currentDateRecord) {
            $Result = \App\Models\SamraddhBankClosing::find($currentDateRecord->id);
            $data['balance'] = $currentDateRecord->balance - $amount;
            $data['updated_at'] = $date;
            $Result->update($data);
            $insertid = $currentDateRecord->id;
            $getNextBankClosingRecord = \App\Models\SamraddhBankClosing::where('bank_id', $bank_id)->where('account_id', $account_id)->whereDate('entry_date', '>', $entryDate)->orderby('entry_date', 'ASC')->get();
            if ($getNextBankClosingRecord) {
                foreach ($getNextBankClosingRecord as $key => $value) {
                    $sResult = \App\Models\SamraddhBankClosing::find($value->id);
                    $sData['opening_balance'] = $value->closing_balance;
                    $sData['balance'] = $value->balance - $amount;
                    if ($value->closing_balance > 0) {
                        $sData['closing_balance'] = $value->closing_balance - $amount;
                    }
                    $sData['updated_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($entryDate)));
                    $sResult->update($sData);
                }
            }
        } else {
            $oldDateRecord = \App\Models\SamraddhBankClosing::where('bank_id', $bank_id)->where('account_id', $account_id)->whereDate('entry_date', '<', $entryDate)->orderby('entry_date', 'DESC')->first();
            if ($oldDateRecord) {
                $Result1 = \App\Models\SamraddhBankClosing::find($oldDateRecord->id);
                $data1['closing_balance'] = $oldDateRecord->balance;
                $Result1->update($data1);
                $insertid1 = $oldDateRecord->id;
                $data1RecordExists = \App\Models\SamraddhBankClosing::where('bank_id', $bank_id)->where('account_id', $account_id)->whereDate('entry_date', '>', $entryDate)->orderby('entry_date', 'ASC')->get();
                if ($type == 0) {
                    $data['balance'] = $oldDateRecord->balance - $amount;
                } else {
                    $data['balance'] = $oldDateRecord->balance;
                }
                $data['opening_balance'] = $oldDateRecord->balance;
                if ($data1RecordExists) {
                    $data['closing_balance'] = $oldDateRecord->balance - $amount;
                    foreach ($data1RecordExists as $key => $value) {
                        $sResult = \App\Models\BranchClosing::find($value->id);
                        $sData['opening_balance'] = $value->closing_balance;
                        $sData['balance'] = $value->balance - $amount;
                        if ($value->closing_balance > 0) {
                            $sData['closing_balance'] = $value->closing_balance - $amount;
                        }
                        $sData['updated_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($entryDate)));
                        $sResult->update($sData);
                    }
                } else {
                    $data['closing_balance'] = 0;
                }
                $data['bank_id'] = $bank_id;
                $data['account_id'] = $account_id;
                $data['entry_date'] = $entryDate;
                $data['entry_time'] = $entryTime;
                $data['type'] = $type;
                $data['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($date)));
                $transcation = \App\Models\SamraddhBankClosing::create($data);
                $insertid = $transcation->id;
            } else {
                $data['balance'] = $amount;
                $data['opening_balance'] = 0;
                $data['closing_balance'] = 0;
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
    public function branchtocompany(Request $request)
    {
        $branchId = $request->branch;
        $company = $this->repository->getAllCompanies()->whereHas('branches', function ($que) use ($branchId) {
            $que->where('branch_id', $branchId);
        })->pluck('name', 'id');
        return json_encode($company);
        //  
    }
}