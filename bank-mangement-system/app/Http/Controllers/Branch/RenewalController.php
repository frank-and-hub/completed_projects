<?php
namespace App\Http\Controllers\Branch;

use App\Http\Controllers\Branch\InvestmentReportController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Validator;
use Session;
use Redirect;
use URL;
use DB;
use App\Models\User;
use App\Models\Member;
use App\Models\Memberinvestments;
use App\Models\SavingAccount;
use App\Models\SavingAccountTranscation;
use App\Models\Investmentplantransactions;
use App\Models\Transcation;
use App\Models\TranscationLog;
use App\Models\ReceivedCheque;
use App\Models\Daybook;
use App\Http\Controllers\Branch\CommanTransactionsController;
use App\Services\Sms;
use App\Scopes\ActiveScope;

class RenewalController extends Controller
{
    /**
     * Instantiate a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }
    /**
     * Display renew form.
     *
     * @return \Illuminate\Http\Response
     */
    public function renew()
    {
        if (!in_array('Renewal Investment', auth()->user()->getPermissionNames()->toArray())) {
            return redirect()->route('branch.dashboard');
        }
        $data['title'] = "Renewal";
        return view('templates.branch.investment_management.renewal.renew', $data);
    }
    /**
     * Display renew form.
     *
     * @return \Illuminate\Http\Response
     */
    public function getCollectorAssociate(Request $request)
    {
        $code = $request->code;
        $collectorDetails = Member::with('savingAccount')->leftJoin('carders', 'members.current_carder_id', '=', 'carders.id')
            ->where('members.associate_no', $code)
            ->where('members.status', 1)
            ->where('members.is_deleted', 0)
            ->where('members.is_associate', 1)
            ->where('members.is_block', 0)
            ->where('members.associate_status', 1)
            ->select('carders.name as carders_name', 'members.first_name', 'members.last_name', 'members.id')
            ->first();
        if ($collectorDetails) {
            return Response::json(['msg_type' => 'success', 'collectorDetails' => $collectorDetails]);
        } else {
            return Response::json(['view' => 'No data found', 'msg_type' => 'error']);
        }
    }
    /**
     * Get investment details.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return JSON
     */
    public function getInvestmentDetails(Request $request)
    {
        $isMature = true;
        $type = '';
        $accountNumber = $request->account_number;
        $renewalDate = date('Y-m-d', strtotime(convertDate($request->renewalDate)));
        $planId = $request->renewPlanId;
        $getPlanDetails = \App\Models\Plans::where('id', $planId)->withoutGlobalScope(ActiveScope::class);
        $getPlanDetails = $getPlanDetails->first();
        $category = $getPlanDetails->plan_category_code;
        $allId = \App\Models\Plans::where('plan_category_code', $category)->withoutGlobalScope(ActiveScope::class)->pluck('id');
        // if ($planId == 7 || $planId == 1) {
        //     $investment = Memberinvestments::with('member', 'associateMember', 'ssb', 'demandadvice')->where('plan_id', $planId)->where('account_number', $accountNumber)->where('investment_correction_request', 0)->where('renewal_correction_request', 0)/*->where('company_id', $company_id)*/->where('is_mature', 1)->get();
        // } else {
        //     $investment = Memberinvestments::with('member', 'associateMember', 'ssb', 'demandadvice')->whereIn('plan_id', [2, 3, 5, 6, 10, 11])->where('account_number', $accountNumber)->where('investment_correction_request', 0)/*->where('company_id', $company_id)*/->where('renewal_correction_request', 0)->where('is_mature', 1)->get();
        // }
        $company_id = '';
        if (($getPlanDetails->plan_category_code == 'S' && $request->renewPlan == 2) || ($getPlanDetails->plan_category_code == 'D' && $request->renewPlan == 0) || ($getPlanDetails->plan_category_code == 'M' && $request->renewPlan == 1)) {
            $investment = Memberinvestments::with([
                'member',
                'associateMember',
                'ssb',
                'demandadvice' => function ($q) {
                    $q->where('is_deleted', 0);
                }
            ])->whereIn('plan_id', $allId)->where('account_number', $accountNumber)->where('investment_correction_request', 0)->where('renewal_correction_request', 0)->where('is_mature', 1)->get();
        }
        if (count($investment) > 0) {
            $company_id = $investment[0]->company_id;
            $aa = InvestmentReportController::getRecords($investment[0], $renewalDate);
            if (isset($aa['pendingEmiAMount'])) {
                $dueAmount = $aa['pendingEmiAMount'];
            } else {
                $dueAmount = 0;
            }
            $msg = true;
        } else {
            $aa = 0;
            $dueAmount = '';
            $msg = false;
        }
        $cheques = "";
        if (isset($request->chequee)) {
            $cheques = ReceivedCheque::where([
                ['company_id', $company_id],
                ['branch_id', Auth::user()->id],
                ['status', 2],
            ])->get(['id', 'cheque_no']);
        }
        if (count($investment) > 0 && !isset($investment[0]['demandadvice'])) {
            $maturityDate = $investment[0]->maturity_date;
            if ($maturityDate <= $renewalDate && $maturityDate != NULL && $investment[0]->plan_id != 1) {
                $msg = true;
                $amount = '';
                $minutes = 6;
                $isMature = true;
            } else {
                $msg = false;
                $isMature = true;
                $investmentLastDate = Investmentplantransactions::select('deposite_date', 'deposite_month')->where('investment_id', $investment[0]->id)->orderBy('id', 'desc')->first();
                if ($investmentLastDate && $investment[0]->plan_id == 7) {
                    $start_date = strtotime($investmentLastDate->deposite_date);
                    $end_date = strtotime(date('Y-m-d'));
                    $daysDiff = ($end_date - $start_date) / 60 / 60 / 24;
                    if ($daysDiff > 0) {
                        if ($investment[0]->due_amount >= 0) {
                            //$amount = ($investment[0]->deposite_amount*$daysDiff)/*+$investment[0]->due_amount*/;
                            $amount = ($investment[0]->deposite_amount * $daysDiff) + $investment[0]->due_amount;
                        } elseif ($investment[0]->due_amount < 0) {
                            $amount = $investment[0]->due_amount;
                        }
                    } else {
                        //$amount = 0;
                        $amount = $investment[0]->due_amount;
                        /*if($investment[0]->due_amount >= 0){
                        $amount = $investment[0]->due_amount+$investment[0]->deposite_amount;
                    }elseif($investment[0]->due_amount < 0){
                        $amount = $investment[0]->due_amount;
                    }*/
                    }
                } else if ($investmentLastDate && $investment[0]->plan_id == 1) {
                    $amount = $investment[0]->deposite_amount;
                } else if ($investmentLastDate) {
                    $lMonth = $investmentLastDate->deposite_month;
                    $cMonth = date('m');
                    $daysDiff = ($cMonth - $lMonth);
                    if ($daysDiff > 0) {
                        //$amount = ($investment[0]->deposite_amount*$daysDiff)-$investment[0]->current_balance;
                        if ($investment[0]->due_amount >= 0) {
                            //$amount = ($investment[0]->deposite_amount*$daysDiff)/*+$investment[0]->due_amount*/;
                            $amount = ($investment[0]->deposite_amount * $daysDiff) + $investment[0]->due_amount;
                        } elseif ($investment[0]->due_amount < 0) {
                            $amount = $investment[0]->due_amount;
                        }
                    } else {
                        //$amount = 0;
                        $amount = $investment[0]->due_amount;
                        /*if($investment[0]->due_amount >= 0){
                        $amount = $investment[0]->due_amount+$investment[0]->deposite_amount;
                    }elseif($investment[0]->due_amount < 0){
                        $amount = $investment[0]->due_amount;
                    }*/
                    }
                } else {
                    $amount = $investment[0]->deposite_amount;
                }
            }
            $currentDate = date('Y-m-d H:i:s');
            $getLastRecord = Daybook::where('investment_id', $investment[0]->id)->where('account_no', $accountNumber)->where('company_id', $company_id)->orderBy('created_at', 'desc')->first();
            if ($getLastRecord) {
                $dateTimeObject1 = date_create('' . $getLastRecord->updated_at . '');
                $dateTimeObject2 = date_create('' . $currentDate . '');
                $difference = date_diff($dateTimeObject1, $dateTimeObject2);
                $minutes = $difference->days * 24 * 60;
                $minutes += $difference->h * 60;
                $minutes += $difference->i;
                //$minutes = 6;
            } else {
                $minutes = 6;
            }
        } else if (!empty($investment[0]['demandadvice'])) {
            $type = 'demand-advice';
            $amount = '';
            $minutes = 6;
        } else {
            $amount = '';
            $minutes = 6;
            $msg = false;
            $isMature = false;
        }
        $resCount = count($investment);
        $return_array = compact('investment', 'resCount', 'amount', 'minutes', 'msg', 'dueAmount', 'isMature', 'type', 'cheques', 'company_id');
        return json_encode($return_array);
    }
    /*Code added by amar 7 Feb2022*/
    /**
     * Get investment details.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return JSON
     */
    public function storeAjax(Request $request)
    {
        if ($request->ajax()) {
            $baseurl = URL::to('/');
            $percentage = floor(($request['i'] / $request['no_of_acc']) * 100);
            /*$response = array(
            'status' => 'alert',
            'msg'    =>  'testing call',
            'percentage'=>$percentage,
             );
            return Response::json($response);*/
            $stateid = getBranchState(Auth::user()->username);
            $globaldate = checkMonthAvailability(date('d'), date('m'), date('Y'), $stateid);
            $getBranchId = getUserBranchId(Auth::user()->id);
            //$branch_id = (Auth::user()->id);
            $branch_id = $getBranchId->id;
            $getBranchCode = getBranchCode($getBranchId->id);
            $getBranchName = getBranchName(Auth::user()->id);
            $branchCode = $getBranchCode->branch_code;
            $branchName = getBranchName(Auth::user()->id)->name;
            $accountNumbers = $request['account_number'];
            $renewPlanId = $request['renewplan_id'];
            $sAccount = $this->getSavingAccountDetails($request['member_id']);
            $collectionSSBId = '';
            $request['renewal_date'] = $globaldate;
            DB::beginTransaction();
            try {
                if ($request['payment_mode'] == 1) {
                    $getChequeDetail = \App\Models\ReceivedCheque::where('id', $request['cheque_id'])->where('status', 3)->first(['id', 'amount', 'status']);
                    if (!empty($getChequeDetail)) {
                        $response = array(
                            'status' => 'alert',
                            'msg' => 'Cheque already used select another cheque',
                        );
                        return Response::json($response);
                        //return back()->with('alert', 'Cheque already used select another cheque');
                    } else {
                        $getamount = \App\Models\ReceivedCheque::where('id', $request['cheque_id'])->first(['id', 'amount']);
                        if ($getamount->amount != number_format((float) $request['total_amount'], 4, '.', '')) {
                            $response = array(
                                'status' => 'alert',
                                'msg' => 'Renew  amount is not equal to cheque amount',
                            );
                            return Response::json($response);
                            //return back()->with('alert', 'Renew  amount is not equal to cheque amount');
                        }
                    }
                }
                $received_cheque_id = $cheque_id = NULL;
                $cheque_deposit_bank_id = NULL;
                $cheque_deposit_bank_ac_id = NULL;
                $cheque_no = NULL;
                $cheque_date = $pdate = NULL;
                $online_deposit_bank_id = NULL;
                $online_deposit_bank_ac_id = NULL;
                $online_transction_no = NULL;
                $online_transction_date = NULL;
                if ($request['payment_mode'] == 1) {
                    $pmodeAll = 1;
                    $chequeDetail = \App\Models\ReceivedCheque::where('id', $request['cheque_id'])->first();
                    $received_cheque_id = $cheque_id = $request['cheque_id'];
                    $cheque_deposit_bank_id = $chequeDetail->deposit_bank_id;
                    $cheque_deposit_bank_ac_id = $chequeDetail->deposit_account_id;
                    $cheque_no = $request['cheque-number'];
                    $cheque_date = $pdate = date("Y-m-d", strtotime(str_replace('/', '-', $request['cheque-date'])));
                }
                if ($request['payment_mode'] == 0) {
                    $pmodeAll = 0;
                }
                if ($request['payment_mode'] == 4) {
                    $pmodeAll = 3;
                }
                if ($request['payment_mode'] == 3) {
                    $pmodeAll = 2;
                }
                Session::put('created_at', $globaldate);
                foreach ($accountNumbers as $key => $accountNumber) {
                    if ($accountNumber && $request['amount'][$key]) {
                        $amountArraySsb = array('1' => $request['amount'][$key]);
                        $currentDate = date('Y-m-d H:i:s');
                        $getLastRecord = Daybook::where('investment_id', $request['investment_id'][$key])->where('account_no', $accountNumber)->orderBy('created_at', 'desc')->first();
                        if ($getLastRecord) {
                            $dateTimeObject1 = date_create('' . $getLastRecord->updated_at . '');
                            $dateTimeObject2 = date_create('' . $currentDate . '');
                            $difference = date_diff($dateTimeObject1, $dateTimeObject2);
                            $minutes = $difference->days * 24 * 60;
                            $minutes += $difference->h * 60;
                            $minutes += $difference->i;
                            //$minutes = 6;
                        } else {
                            $minutes = 6;
                        }
                        $timeInvestmentArray = array();
                        if ($minutes > 5) {
                            $save = 0;
                            if ($renewPlanId == 2) {
                                $investmentDetail = $this->getInvestmentPlanDetail($request['investment_id'][$key]);
                                $sAccountId = $this->getSavingAccountId($request['investment_id'][$key]);
                                if ($sAccountId) {
                                    $ssb_id = $sAccountId->id;
                                    $ssbAccountNumber = $sAccountId->account_no;
                                } else {
                                    $ssb_id = NULL;
                                    $ssbAccountNumber = NULL;
                                }
                                $savingAccountDetail = $this->getSavingAccountDetails($request['investment_member_id'][$key]);
                                if ($savingAccountDetail) {
                                    $renewSavingOpeningBlanace = $savingAccountDetail->balance;
                                } else {
                                    $renewSavingOpeningBlanace = NULL;
                                }
                                if ($investmentDetail) {
                                    $sResult = Memberinvestments::find($request['investment_id'][$key]);
                                    $totalbalance = $investmentDetail->current_balance + $request['amount'][$key];
                                    $investData['current_balance'] = $totalbalance;
                                    $sResult->update($investData);
                                } else {
                                    $totalbalance = '';
                                }
                                if ($request['payment_mode'] == 0) {
                                    $mtssb = 'Cash deposit';
                                } else {
                                    $mtssb = 'Amount deposit';
                                }
                                $rno = $request['cheque-number'];
                                if ($request['payment_mode'] == 4) {
                                    $mtssb = 'Amount deposit by ' . $sAccount->account_no;
                                    $rno = $sAccount->account_no;
                                    $ssbAccountAmount = $sAccount->balance - $request['amount'][$key];
                                    $ssb_id = $collectionSSBId = $sAccount->id;
                                    $sResult = SavingAccount::find($ssb_id);
                                    $sData['balance'] = $ssbAccountAmount;
                                    $sResult->update($sData);
                                    $ssb['saving_account_id'] = $ssb_id;
                                    $ssb['account_no'] = $sAccount->account_no;
                                    $ssb['opening_balance'] = $ssbAccountAmount;
                                    $ssb['withdrawal'] = $request['amount'][$key];
                                    //$ssb['description'] = ''.$accountNumber.'/Auto debit - collection';     
                                    if ($branch_id != $investmentDetail->branch_id) {
                                        $branchName = getBranchName(Auth::user()->id);
                                        $ssb['description'] = 'Fund Trf. To ' . $savingAccountDetail->account_no . '- From ' . $branchName . '';
                                    } else {
                                        $ssb['description'] = 'Fund Trf. To ' . $savingAccountDetail->account_no;
                                    }
                                    $ssb['associate_id'] = $request['member_id'];
                                    $ssb['branch_id'] = $branch_id;
                                    $ssb['type'] = 6;
                                    $ssb['currency_code'] = 'INR';
                                    $ssb['payment_type'] = 'CR';
                                    $ssb['payment_mode'] = $request['payment_mode'];
                                    $ssb['deposit'] = NULL;
                                    $ssb['is_renewal'] = 0;
                                    $ssb['created_at'] = date("Y-m-d H:i:s", strtotime(str_replace('/', '-', $request['renewal_date'])));
                                    ;
                                    $ssbAccountTran = SavingAccountTranscation::create($ssb);
                                    $ssbFromId = $ssb_id;
                                    $ssbAccountTranFromId = $ssbAccountTran->id;
                                    $satRefId = CommanTransactionsController::createTransactionReferences($ssbAccountTran->id, $request['investment_id'][$key]);
                                    $createTransaction = CommanTransactionsController::createTransaction($satRefId, 5, 0, $request['investment_member_id'][$key], $branch_id, $branchCode, $amountArraySsb, $request['payment_mode'], $request['deposite_by_name'], $request['member_id'], $accountNumber, 0, NULL, NULL, date("Y-m-d", strtotime(str_replace('/', '-', $request['renewal_date']))), NULL, $online_payment_by = NULL, $ssb_id, 'DR');
                                    $transactionData['is_renewal'] = 0;
                                    $transactionData['created_at'] = date("Y-m-d H:i:s", strtotime(str_replace('/', '-', $request['renewal_date'])));
                                    $updateTransaction = Transcation::find($createTransaction);
                                    $updateTransaction->update($transactionData);
                                    TranscationLog::where('transaction_id', $transactionData)->update(['is_renewal' => 0, 'created_at' => date("Y-m-d H:i:s", strtotime(str_replace('/', '-', $request['renewal_date'])))]);
                                } else {
                                    $ssbFromId = NULL;
                                    $ssbAccountTranFromId = NULL;
                                }
                                $ssbAccountAmount = $renewSavingOpeningBlanace + $request['amount'][$key];
                                $ssb_id = $depositSSBId = $savingAccountDetail->id;
                                $sResult = SavingAccount::find($ssb_id);
                                $sData['balance'] = $ssbAccountAmount;
                                $sResult->update($sData);
                                $ssb['saving_account_id'] = $ssb_id;
                                $ssb['account_no'] = $accountNumber;
                                $ssb['opening_balance'] = $renewSavingOpeningBlanace + $request['amount'][$key];
                                $ssb['withdrawal'] = 0;
                                if ($branch_id != $investmentDetail->branch_id) {
                                    $branchName = getBranchNameByBrachAuto($branch_id);
                                    $ssb['description'] = $mtssb . ' - From ' . $branchName->name . '';
                                } else {
                                    $ssb['description'] = $mtssb;
                                }
                                $ssb['associate_id'] = $request['member_id'];
                                $ssb['branch_id'] = $branch_id;
                                $ssb['type'] = 2;
                                $ssb['currency_code'] = 'INR';
                                $ssb['payment_type'] = 'CR';
                                $ssb['payment_mode'] = $request['payment_mode'];
                                $ssb['reference_no'] = $rno;
                                $ssb['deposit'] = $request['amount'][$key];
                                $ssb['created_at'] = $request['renewal_date'];
                                $ssbAccountTran = SavingAccountTranscation::create($ssb);
                                $ssbToId = $savingAccountDetail->id;
                                $ssbAccountTranToId = $ssbAccountTran->id;
                                $satRefId = CommanTransactionsController::createTransactionReferences($ssbAccountTran->id, $request['investment_id'][$key]);
                                $createTransaction = CommanTransactionsController::createTransaction($satRefId, 5, 0, $request['investment_member_id'][$key], $branch_id, $branchCode, $amountArraySsb, $request['payment_mode'], $request['deposite_by_name'], $request['member_id'], $accountNumber, $request['cheque-number'], $request['bank-name'], $request['branch-name'], date("Y-m-d", strtotime(str_replace('/', '-', $request['renewal_date']))), NULL, $online_payment_by = NULL, $ssb_id, 'CR');
                                $transactionData['is_renewal'] = 0;
                                //$transactionData['created_at'] = $request['renewal_date'];
                                $updateTransaction = Transcation::find($createTransaction);
                                $updateTransaction->update($transactionData);
                                TranscationLog::where('transaction_id', $transactionData)->update(['is_renewal' => 0, 'created_at' => date("Y-m-d", strtotime(str_replace('/', '-', $request['renewal_date'])))]);
                                // $createDayBook = CommanTransactionsController::createDayBook($createTransaction,$satRefId,1,$request['investment_id'][$key],$request['member_id'],$request['investment_member_id'][$key],$totalbalance,$request['amount'][$key],$withdrawal=0,$ssb['description'],$ssbAccountNumber,$branch_id,$branchCode,$amountArraySsb,$request['payment_mode'],$request['deposite_by_name'],$request['member_id'],$accountNumber,$request['cheque-number'],$request['bank-name'],$request['branch-name'],date("Y-m-d", strtotime( str_replace('/','-',$request['renewal_date'] ) ) ),NULL,$online_payment_by=NULL,$ssb_id,'CR'); 
                                // ---------------------------  Day book modify --------------------------    
                                $lastAmount = Daybook::where('investment_id', $request['investment_id'][$key])->where('account_no', $accountNumber)->whereIN('transaction_type', [1])->whereDate('created_at', date("Y-m-d", strtotime(convertDate($request['renewal_date']))))->orderby('id', 'desc')->first();
                                if ($lastAmount) {
                                    $lastBalance = $lastAmount->opening_balance + $request['amount'][$key];
                                } else {
                                    $lastAmount = Daybook:: /*where('investment_id',$request['investment_id'][$key])->*/where('account_no', $accountNumber)->whereIN('transaction_type', [1])->whereDate('created_at', '<', date("Y-m-d", strtotime(convertDate($request['renewal_date']))))->orderby('id', 'desc')->first();
                                    if ($lastAmount) {
                                        $lastBalance = $lastAmount->opening_balance + $request['amount'][$key];
                                    } else {
                                        $lastOpeningAmount = Daybook::where('account_no', $accountNumber)->whereIN('transaction_type', [1])->orderby('id', 'desc')->first();
                                        $lastBalance = $lastOpeningAmount->opening_balance + $request['amount'][$key];
                                    }
                                }
                                if ($lastAmount) {
                                    $nextRenewal = $lastAmount = Daybook::where('investment_id', $request['investment_id'][$key])->whereIN('transaction_type', [1])->where('account_no', $accountNumber)->whereDate('created_at', '>', date("Y-m-d", strtotime(convertDate($lastAmount->created_at))))->get();
                                    foreach ($nextRenewal as $key1 => $value) {
                                        $daybookData['opening_balance'] = $value->opening_balance + $request['amount'][$key];
                                        $dayBook = Daybook::find($value->id);
                                        $dayBook->update($daybookData);
                                    }
                                }
                                $createDayBook = CommanTransactionsController::createDayBookNew($createTransaction, $satRefId, 2, $request['investment_id'][$key], $request['member_id'], $request['investment_member_id'][$key], $lastBalance, $request['amount'][$key], $withdrawal = 0, $ssb['description'], $ssbAccountNumber, $branch_id, $branchCode, $amountArraySsb, $request['payment_mode'], $request['deposite_by_name'], $request['member_id'], $accountNumber, $request['cheque-number'], $request['bank-name'], $request['branch-name'], $globaldate, NULL, $online_payment_by = NULL, $collectionSSBId, 'CR', $received_cheque_id, $cheque_deposit_bank_id, $cheque_deposit_bank_ac_id, $online_deposit_bank_id, $online_deposit_bank_ac_id);
                                // ---------------------------  HEAD IMPLEMENT --------------------------
                                $planId = $investmentDetail->plan_id;
                                $this->investHeadCreateSSB($request['amount'][$key], $globaldate, $request['investment_id'][$key], $planId, $cheque_no, $cheque_id, $cheque_date, $online_transction_no, $online_transction_date, $online_deposit_bank_id, $online_deposit_bank_ac_id, $branch_id, $request['member_id'], $request['investment_member_id'][$key], $collectionSSBId, $ssbAccountTran->id, $pmodeAll, $ssbAccountNumber, $ssbFromId, $ssbAccountTranFromId, $ssbToId, $ssbAccountTranToId);
                                //--------------------------------HEAD IMPLEMENT  ------------------------
                                $daybookData['is_renewal'] = 0;
                                //$daybookData['created_at'] = date("Y-m-d H:i:s", strtotime( str_replace('/','-',$request['renewal_date'] ) ) );
                                $dayBook = Daybook::find($createDayBook);
                                $dayBook->update($daybookData);
                                /*--------------------cheque assign -----------------------*/
                                if ($request['payment_mode'] == 1) {
                                    $receivedPayment['type'] = 3;
                                    $receivedPayment['branch_id'] = $branch_id;
                                    $receivedPayment['investment_id'] = $request['investment_id'][$key];
                                    $receivedPayment['day_book_id'] = $createDayBook;
                                    $receivedPayment['cheque_id'] = $request['cheque_id'];
                                    $receivedPayment['created_at'] = $globaldate;
                                    $receivedCreate = \App\Models\ReceivedChequePayment::create($receivedPayment);
                                    $dataRC['status'] = 3;
                                    $receivedcheque = \App\Models\ReceivedCheque::find($request['cheque_id']);
                                    $receivedcheque->update($dataRC);
                                }
                                /*-----------------------------------------------------------*/
                                $transaction = $this->transactionData($request->all(), $request['investment_id'][$key], $request['amount'][$key]);
                                $ipTransaction = Investmentplantransactions::create($transaction);
                                $ipTransactionData['is_renewal'] = 0;
                                $ipTransactionData['created_at'] = date("Y-m-d H:i:s", strtotime(str_replace('/', '-', $request['renewal_date'])));
                                $updateipTransaction = Investmentplantransactions::find($ipTransaction->id);
                                $updateipTransaction->update($ipTransactionData);
                                $investmentCurrentBalance = getSavingCurrentBalance($ssb_id, $accountNumber);
                                $save = 1;
                                $rAmount = $investmentCurrentBalance;
                            } else {
                                //Ivestment 
                                //$investmentAmount = Memberinvestments::select('due_amount')->where('id',$request['investment_id'][$key])->first();
                                $investmentDetail = $this->getInvestmentPlanDetail($request['investment_id'][$key]);
                                if ($request->renewplan_id == 3) {
                                    $data['due_amount'] = 0;
                                } else {
                                    $data['due_amount'] = $request['deo_amount'][$key];
                                }
                                if ($data['due_amount'] && $data['due_amount'] > 0) {
                                    $investment = Memberinvestments::find($request['investment_id'][$key]);
                                    $investment->update($data);
                                }
                                if ($request['payment_mode'] == 4) {
                                    $sAccount = $this->getSavingAccountDetails($request['member_id']);
                                    $ssbAccountAmount = $sAccount->balance - $request['amount'][$key];
                                    $ssb_id = $collectionSSBId = $sAccount->id;
                                    $sResult = SavingAccount::find($ssb_id);
                                    $sData['balance'] = $ssbAccountAmount;
                                    $sResult->update($sData);
                                    $ssb['saving_account_id'] = $ssb_id;
                                    $ssb['account_no'] = $sAccount->account_no;
                                    $ssb['opening_balance'] = $ssbAccountAmount;
                                    $ssb['deposit'] = NULL;
                                    $ssb['withdrawal'] = $request['amount'][$key];
                                    //$ssb['description']=''.$accountNumber.'/Auto debit';  
                                    /*if($branch_id != $investmentDetail->branch_id){
                                    $branchName = getBranchName(Auth::user()->id);
                                    $ssb['description'] = 'Cash withdrawal - From '.$branch.'';
                                }else{
                                    $ssb['description'] = 'Cash withdrawal';    
                                }*/
                                    $ssb['description'] = 'Fund Trf. To ' . $accountNumber . '';
                                    $ssb['branch_id'] = $branch_id;
                                    $ssb['type'] = 6;
                                    $ssb['associate_id'] = $request['member_id'];
                                    $ssb['currency_code'] = 'INR';
                                    $ssb['payment_type'] = 'CR';
                                    $ssb['payment_mode'] = $request['payment_mode'];
                                    $ssb['is_renewal'] = 0;
                                    $ssb['created_at'] = $request['renewal_date'];
                                    $ssbAccountTran = SavingAccountTranscation::create($ssb);
                                    $ssbFromId = $ssb_id;
                                    $ssbAccountTranFromId = $ssbAccountTran->id;
                                    $satRefId = CommanTransactionsController::createTransactionReferences($ssbAccountTran->id, $request['investment_id'][$key]);
                                } else {
                                    $satRefId = NULL;
                                    $ssbFromId = NULL;
                                    $ssbAccountTranFromId = NULL;
                                }
                                $ssbToId = NULL;
                                $ssbAccountTranToId = NULL;
                                $sAccount = $this->getSavingAccountDetails($request['investment_member_id'][$key]);
                                if ($sAccount) {
                                    $ssbAccountId = $sAccount->id;
                                } else {
                                    $ssbAccountId = 0;
                                }
                                /*$rplanId = $request['renew_investment_plan_id'];
                            if($rplanId==7){
                              $description = 'SDD Collection';  
                            }elseif($rplanId==10){
                              $description = 'SRD collection';  
                            }elseif($rplanId==5){
                              $description = 'FRD collection';  
                            }elseif($rplanId==3){
                              $description = 'SMB collection';  
                            }elseif($rplanId==2){
                              $description = 'SK collection';  
                            }elseif($rplanId==6){
                              $description = 'SJ collection';  
                            }*/
                                if ($investmentDetail) {
                                    $totalbalance = $investmentDetail->current_balance + $request['amount'][$key];
                                    $investData['current_balance'] = $totalbalance;
                                    $rplanId = $investmentDetail->plan_id;
                                    if ($request['payment_mode'] == 4) {
                                        $assAccount = $this->getSavingAccountDetails($request['member_id']);
                                        $description = 'Fund Rec. From ' . $assAccount->account_no . '';
                                    } else {
                                        if ($branch_id != $investmentDetail->branch_id) {
                                            $branchName = getBranchName(Auth::user()->id);
                                            if ($rplanId == 7) {
                                                $description = 'SDD Collection - From ' . $branchName->name . '';
                                            } elseif ($rplanId == 10) {
                                                $description = 'SRD collection - From ' . $branchName->name . '';
                                            } elseif ($rplanId == 5) {
                                                $description = 'FRD collection - From ' . $branchName->name . '';
                                            } elseif ($rplanId == 3) {
                                                $description = 'SMB collection - From ' . $branchName->name . '';
                                            } elseif ($rplanId == 2) {
                                                $description = 'SK collection - From ' . $branchName->name . '';
                                            } elseif ($rplanId == 6) {
                                                $description = 'SJ collection - From ' . $branchName->name . '';
                                            } elseif ($rplanId == 11) {
                                                $description = 'SB collection - From ' . $branchName->name . '';
                                            } elseif ($rplanId == 12) {
                                                $description = 'SSB Child collection- From ' . $branchName . '';
                                            }
                                        } else {
                                            if ($rplanId == 7) {
                                                $description = 'SDD Collection';
                                            } elseif ($rplanId == 10) {
                                                $description = 'SRD collection';
                                            } elseif ($rplanId == 5) {
                                                $description = 'FRD collection';
                                            } elseif ($rplanId == 3) {
                                                $description = 'SMB collection';
                                            } elseif ($rplanId == 2) {
                                                $description = 'SK collection';
                                            } elseif ($rplanId == 6) {
                                                $description = 'SJ collection';
                                            } elseif ($rplanId == 11) {
                                                $description = 'SB collection';
                                            } elseif ($rplanId == 12) {
                                                $description = 'SSB Child collection';
                                            }
                                        }
                                    }
                                } else {
                                    $totalbalance = '';
                                    $description = '';
                                }
                                $createTransaction = CommanTransactionsController::createTransaction($satRefId, 4, $request['investment_id'][$key], $request['investment_member_id'][$key], $branch_id, $branchCode, $amountArraySsb, $request['payment_mode'], $request['deposite_by_name'], $request['member_id'], $accountNumber, $request['cheque-number'], $request['bank-name'], $request['branch-name'], date("Y-m-d", strtotime(str_replace('/', '-', $request['renewal_date']))), NULL, $online_payment_by = NULL, $ssbAccountId, 'CR');
                                $transactionData['is_renewal'] = 0;
                                //$transactionData['created_at'] = date("Y-m-d H:i:s", strtotime( str_replace('/','-',$request['renewal_date'] ) ) );
                                $updateTransaction = Transcation::find($createTransaction);
                                $updateTransaction->update($transactionData);
                                TranscationLog::where('transaction_id', $transactionData)->update(['is_renewal' => 0, 'created_at' => date("Y-m-d", strtotime(str_replace('/', '-', $request['renewal_date'])))]);
                                // $createDayBook = CommanTransactionsController::createDayBook($createTransaction,$satRefId,4,$request['investment_id'][$key],$request['member_id'],$request['investment_member_id'][$key],$totalbalance,$request['amount'][$key],$withdrawal=0,$description,$ref=NULL,$branch_id,$branchCode,$amountArraySsb,$request['payment_mode'],$request['deposite_by_name'],$request['member_id'],$accountNumber,$request['cheque-number'],$request['bank-name'],$request['branch-name'],$globaldate,NULL,$online_payment_by=NULL,$ssbAccountId,'CR'); 
                                // ---------------------------  Day book modify --------------------------    
                                $lastBalance = getLastOpeingBalance($request['investment_id'][$key]) + $request['amount'][$key];
                                $createDayBook = CommanTransactionsController::createDayBookNew($createTransaction, $satRefId, 4, $request['investment_id'][$key], $request['member_id'], $request['investment_member_id'][$key], $lastBalance, $request['amount'][$key], $withdrawal = 0, $description, $ref = NULL, $branch_id, $branchCode, $amountArraySsb, $request['payment_mode'], $request['deposite_by_name'], $request['member_id'], $accountNumber, $request['cheque-number'], $request['bank-name'], $request['branch-name'], $globaldate, NULL, $online_payment_by = NULL, $collectionSSBId, 'CR', $received_cheque_id, $cheque_deposit_bank_id, $cheque_deposit_bank_ac_id, $online_deposit_bank_id, $online_deposit_bank_ac_id);
                                updateRenewalTransaction($accountNumber);
                                // ---------------------------  HEAD IMPLEMENT --------------------------
                                $planId = $investmentDetail->plan_id;
                                $this->investHeadCreate($request['amount'][$key], $globaldate, $request['investment_id'][$key], $planId, $cheque_no, $cheque_id, $cheque_date, $online_transction_no, $online_transction_date, $online_deposit_bank_id, $online_deposit_bank_ac_id, $branch_id, $request['member_id'], $request['investment_member_id'][$key], $collectionSSBId, $createDayBook, $pmodeAll, $investmentDetail->account_number, $ssbFromId, $ssbAccountTranFromId, $ssbToId, $ssbAccountTranToId);
                                //--------------------------------HEAD IMPLEMENT  ------------------------
                                /*-------------------------------  Commission  Section Start ------------------------------------*/
                                $entryTime = date("h:i:s");
                                $dateForRenew = date("Y-m-d", strtotime(convertDate($request['renewal_date'])));
                                $renewal_date_time = date("Y-m-d " . $entryTime . "", strtotime(str_replace('/', '-', $request['renewal_date'])));
                                $investment_id = $request['investment_id'][$key];
                                $tenureMonth = $investmentDetail->tenure * 12;
                                $amountkey = $request['amount'][$key];
                                $daybook_id = $createDayBook;
                                $plan_id = $investmentDetail->plan_id;
                                $investment_associte_id = $investmentDetail->associate_id;
                                $collector_id = $request['member_id'];
                                $comEntry['investment_id'] = $investment_id;
                                $comEntry['investment_plan_id'] = $plan_id;
                                $comEntry['investment_associte_id'] = $investment_associte_id;
                                $comEntry['collector_id'] = $collector_id;
                                $comEntry['branch_id'] = $branch_id;
                                $comEntry['amount'] = $amountkey;
                                $comEntry['renew_date'] = $dateForRenew;
                                $comEntry['daybook_id'] = $daybook_id;
                                $comEntry['tenure_month'] = $tenureMonth;
                                $comEntry['renewal_date_time'] = $renewal_date_time;
                                // $commissionEntry = \App\Models\CommissionEntryDetail::create($comEntry);
                                /*---
                             $dateForRenew=$request['renewal_date'];
                            $Commission=getMonthlyWiseRenewalNew($request['investment_id'][$key],$request['amount'][$key],$dateForRenew); 
                            foreach ($Commission as  $val) {
                                $tenureMonth=$investmentDetail->tenure*12;
                                 $commission =CommanTransactionsController:: commissionDistributeInvestmentRenew($investmentDetail->associate_id,$request['investment_id'][$key],3,$val['amount'],$val['month'],$investmentDetail->plan_id,$branch_id,$tenureMonth,$createDayBook,$val['type']);
                                 $commission_collection =CommanTransactionsController::commissionCollectionInvestmentRenew($request['member_id'],$request['investment_id'][$key],5,$val['amount'],$val['month'],$investmentDetail->plan_id,$branch_id,$tenureMonth,$createDayBook,$val['type']);
                                /*----- ------  credit business start ---- ---------------  
                                $creditBusiness =CommanTransactionsController::associateCreditBusiness($investmentDetail->associate_id,$request['investment_id'][$key],1,$val['amount'],$val['month'],$investmentDetail->plan_id,$tenureMonth,$createDayBook);
                                /*----- ------  credit business end ---- ---------------         
                            }
                            */
                                /*-----------------------------  Commission  Section End -------------------------------------*/
                                if ($investmentDetail) {
                                    $sResult = Memberinvestments::find($request['investment_id'][$key]);
                                    $investData['current_balance'] = $totalbalance;
                                    $sResult->update($investData);
                                } else {
                                    $totalbalance = '';
                                }
                                $daybookData['is_renewal'] = 0;
                                $daybookData['created_at'] = date("Y-m-d H:i:s", strtotime(str_replace('/', '-', $request['renewal_date'])));
                                $dayBook = Daybook::find($createDayBook);
                                $dayBook->update($daybookData);
                                /*--------------------cheque assign -----------------------*/
                                if ($request['payment_mode'] == 1) {
                                    $receivedPayment['type'] = 3;
                                    $receivedPayment['branch_id'] = $branch_id;
                                    $receivedPayment['investment_id'] = $request['investment_id'][$key];
                                    $receivedPayment['day_book_id'] = $createDayBook;
                                    $receivedPayment['cheque_id'] = $request['cheque_id'];
                                    $receivedPayment['created_at'] = $globaldate;
                                    $receivedCreate = \App\Models\ReceivedChequePayment::create($receivedPayment);
                                    $dataRC['status'] = 3;
                                    $receivedcheque = \App\Models\ReceivedCheque::find($request['cheque_id']);
                                    $receivedcheque->update($dataRC);
                                }
                                /*-----------------------------------------------------------*/
                                $transaction = $this->transactionData($request->all(), $request['investment_id'][$key], $request['amount'][$key]);
                                $ipTransaction = Investmentplantransactions::create($transaction);
                                $ipTransactionData['is_renewal'] = 0;
                                $updateipTransaction = Investmentplantransactions::find($ipTransaction->id);
                                $updateipTransaction->update($ipTransactionData);
                                $investmentCurrentBalance = getInvestmentCurrentBalance($investmentDetail->id, $investmentDetail->account_number);
                                $save = 1;
                                $rAmount = $investmentCurrentBalance;
                            }
                        } else {
                            $save = 0;
                            array_push($timeInvestmentArray, $accountNumber);
                        }
                    }
                    $contactNumber = array();
                    if ($key != 0 && $request['investment_member_phone_no'][$key]) {
                        $contactNumber[] = str_replace('"', '', $request['investment_member_phone_no'][$key]);
                        $text = 'Dear Member, Your A/C ' . $accountNumber . ' has been Credited on ' . date("d M Y", strtotime(str_replace('/', '-', $request['renewal_date']))) . ' With Rs. ' . round($request['amount'][$key], 2) . ' Cur Bal: ' . round($rAmount, 2) . '. Thanks Have a nice day';
                        $templateId = 1207161726461603982;
                        $sendToMember = new Sms();
                        $sendToMember->sendSms($contactNumber, $text, $templateId);
                    }
                }
                DB::commit();
            } catch (\Exception $ex) {
                DB::rollback();
                $response = array(
                    'status' => 'alert',
                    'msg' => $ex->getMessage(),
                );
                return Response::json($response);
                // return back()->with('alert', $ex->getMessage());
            }
            if ($save > 0) {
                //redirect()->route('investment/recipt/'.$insertedid);
                //return redirect('branch/renew/recipt/'.$insertedid);
                //return back()->with('success', 'Saved Successfully!');
                /*$data['title'] = "Renewal Recipt";
            $data['renewFields'] = $request->all();
            $data['branchCode'] = $branchCode;
            $data['branchName'] = $branchName;
            return view('templates.branch.investment_management.renewal.recipt', $data);*/
                //$branchArray  = array('branchName' => $branchName, 'timeInvestmentArray' => $timeInvestmentArray);
                $explodeArray = json_encode($request->all());
                $encodeRequests = base64_encode($explodeArray);
                $encodebranchCode = base64_encode($branchCode);
                $encodebranchName = base64_encode($branchName);
                $ssb = '';
                // if($renewPlanId==2)
                // {   
                //      $ssb = json_encode($ssbAccountTran->id); 
                //       return redirect('branch/renew/recipt/'.$encodeRequests.'/'.$encodebranchCode.'/'.$encodebranchName.'/'.$ssb.'');  
                // }
                // else{
                //      return redirect('branch/renew/recipt/'.$encodeRequests.'/'.$encodebranchCode.'/'.$encodebranchName.'');
                // }
                if ($renewPlanId == 2) {
                    $ssb = json_encode($ssbAccountTran->id);
                } else {
                    $ssb = $createDayBook;
                    // return redirect('branch/renew/recipt/'.$encodeRequests.'/'.$encodebranchCode.'/'.$encodebranchName.'');
                }
                $response = array(
                    'status' => 'success',
                    'msg' => 'Renewal form submitted successfully',
                    'percentage' => $percentage,
                    'redirect_url' => $baseurl . '/branch/renew/recipt/' . $encodeRequests . '/' . $encodebranchCode . '/' . $encodebranchName . '/' . $ssb . '',
                );
                return Response::json($response);
                // return redirect('branch/renew/recipt/'.$encodeRequests.'/'.$encodebranchCode.'/'.$encodebranchName.'/'.$ssb.'');
            } else {
                $response = array(
                    'status' => 'Failer',
                    'msg' => 'Error! Problem With Register New Plan',
                    'percentage' => $percentage,
                );
                return Response::json($response);
                // return back()->with('alert', 'Problem With Register New Plan');
            }
        } //end of ajax if
    }
    //End of code
    /**
     * Get investment details.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return JSON
     */
    public function store(Request $request)
    {
        $stateid = getBranchState(Auth::user()->username);
        $globaldate = checkMonthAvailability(date('d'), date('m'), date('Y'), $stateid);
        $getBranchId = getUserBranchId(Auth::user()->id);
        //$branch_id = (Auth::user()->id);
        $branch_id = $getBranchId->id;
        $getBranchCode = getBranchCode($getBranchId->id);
        $getBranchName = getBranchName(Auth::user()->id);
        $branchCode = $getBranchCode->branch_code;
        $branchName = getBranchName(Auth::user()->id)->name;
        $accountNumbers = $request['account_number'];
        $renewPlanId = $request['renewplan_id'];
        $sAccount = $this->getSavingAccountDetails($request['member_id']);
        $collectionSSBId = '';
        $request['renewal_date'] = $globaldate;
        DB::beginTransaction();
        try {
            if ($request['payment_mode'] == 1) {
                $getChequeDetail = \App\Models\ReceivedCheque::where('id', $request['cheque_id'])->where('status', 3)->first(['id', 'amount', 'status']);
                if (!empty($getChequeDetail)) {
                    return back()->with('alert', 'Cheque already used select another cheque');
                } else {
                    $getamount = \App\Models\ReceivedCheque::where('id', $request['cheque_id'])->first(['id', 'amount']);
                    if ($getamount->amount != number_format((float) $request['total_amount'], 4, '.', '')) {
                        return back()->with('alert', 'Renew  amount is not equal to cheque amount');
                    }
                }
            }
            $received_cheque_id = $cheque_id = NULL;
            $cheque_deposit_bank_id = NULL;
            $cheque_deposit_bank_ac_id = NULL;
            $cheque_no = NULL;
            $cheque_date = $pdate = NULL;
            $online_deposit_bank_id = NULL;
            $online_deposit_bank_ac_id = NULL;
            $online_transction_no = NULL;
            $online_transction_date = NULL;
            if ($request['payment_mode'] == 1) {
                $pmodeAll = 1;
                $chequeDetail = \App\Models\ReceivedCheque::where('id', $request['cheque_id'])->first();
                $received_cheque_id = $cheque_id = $request['cheque_id'];
                $cheque_deposit_bank_id = $chequeDetail->deposit_bank_id;
                $cheque_deposit_bank_ac_id = $chequeDetail->deposit_account_id;
                $cheque_no = $request['cheque-number'];
                $cheque_date = $pdate = date("Y-m-d", strtotime(str_replace('/', '-', $request['cheque-date'])));
            }
            if ($request['payment_mode'] == 0) {
                $pmodeAll = 0;
            }
            if ($request['payment_mode'] == 4) {
                $pmodeAll = 3;
            }
            if ($request['payment_mode'] == 3) {
                $pmodeAll = 2;
            }
            Session::put('created_at', $globaldate);
            foreach ($accountNumbers as $key => $accountNumber) {
                if ($accountNumber && $request['amount'][$key]) {
                    $amountArraySsb = array('1' => $request['amount'][$key]);
                    $currentDate = date('Y-m-d H:i:s');
                    $getLastRecord = Daybook::where('investment_id', $request['investment_id'][$key])->where('account_no', $accountNumber)->orderBy('created_at', 'desc')->first();
                    if ($getLastRecord) {
                        $dateTimeObject1 = date_create('' . $getLastRecord->updated_at . '');
                        $dateTimeObject2 = date_create('' . $currentDate . '');
                        $difference = date_diff($dateTimeObject1, $dateTimeObject2);
                        $minutes = $difference->days * 24 * 60;
                        $minutes += $difference->h * 60;
                        $minutes += $difference->i;
                        //$minutes = 6;
                    } else {
                        $minutes = 6;
                    }
                    $timeInvestmentArray = array();
                    if ($minutes > 5) {
                        $save = 0;
                        if ($renewPlanId == 2) {
                            $investmentDetail = $this->getInvestmentPlanDetail($request['investment_id'][$key]);
                            $sAccountId = $this->getSavingAccountId($request['investment_id'][$key]);
                            if ($sAccountId) {
                                $ssb_id = $sAccountId->id;
                                $ssbAccountNumber = $sAccountId->account_no;
                            } else {
                                $ssb_id = NULL;
                                $ssbAccountNumber = NULL;
                            }
                            $savingAccountDetail = $this->getSavingAccountDetails($request['investment_member_id'][$key]);
                            if ($savingAccountDetail) {
                                $renewSavingOpeningBlanace = $savingAccountDetail->balance;
                            } else {
                                $renewSavingOpeningBlanace = NULL;
                            }
                            if ($investmentDetail) {
                                $sResult = Memberinvestments::find($request['investment_id'][$key]);
                                $totalbalance = $investmentDetail->current_balance + $request['amount'][$key];
                                $investData['current_balance'] = $totalbalance;
                                $sResult->update($investData);
                            } else {
                                $totalbalance = '';
                            }
                            if ($request['payment_mode'] == 0) {
                                $mtssb = 'Cash deposit';
                            } else {
                                $mtssb = 'Amount deposit';
                            }
                            $rno = $request['cheque-number'];
                            if ($request['payment_mode'] == 4) {
                                $mtssb = 'Amount deposit by ' . $sAccount->account_no;
                                $rno = $sAccount->account_no;
                                $ssbAccountAmount = $sAccount->balance - $request['amount'][$key];
                                $ssb_id = $collectionSSBId = $sAccount->id;
                                $sResult = SavingAccount::find($ssb_id);
                                $sData['balance'] = $ssbAccountAmount;
                                $sResult->update($sData);
                                $ssb['saving_account_id'] = $ssb_id;
                                $ssb['account_no'] = $sAccount->account_no;
                                $ssb['opening_balance'] = $ssbAccountAmount;
                                $ssb['withdrawal'] = $request['amount'][$key];
                                //$ssb['description'] = ''.$accountNumber.'/Auto debit - collection';     
                                if ($branch_id != $investmentDetail->branch_id) {
                                    $branchName = getBranchName(Auth::user()->id);
                                    $ssb['description'] = 'Fund Trf. To ' . $savingAccountDetail->account_no . '- From ' . $branchName . '';
                                } else {
                                    $ssb['description'] = 'Fund Trf. To ' . $savingAccountDetail->account_no;
                                }
                                $ssb['associate_id'] = $request['member_id'];
                                $ssb['branch_id'] = $branch_id;
                                $ssb['type'] = 6;
                                $ssb['currency_code'] = 'INR';
                                $ssb['payment_type'] = 'CR';
                                $ssb['payment_mode'] = $request['payment_mode'];
                                $ssb['deposit'] = NULL;
                                $ssb['is_renewal'] = 0;
                                $ssb['created_at'] = date("Y-m-d H:i:s", strtotime(str_replace('/', '-', $request['renewal_date'])));
                                ;
                                $ssbAccountTran = SavingAccountTranscation::create($ssb);
                                $ssbFromId = $ssb_id;
                                $ssbAccountTranFromId = $ssbAccountTran->id;
                                $satRefId = CommanTransactionsController::createTransactionReferences($ssbAccountTran->id, $request['investment_id'][$key]);
                                $createTransaction = CommanTransactionsController::createTransaction($satRefId, 5, 0, $request['investment_member_id'][$key], $branch_id, $branchCode, $amountArraySsb, $request['payment_mode'], $request['deposite_by_name'], $request['member_id'], $accountNumber, 0, NULL, NULL, date("Y-m-d", strtotime(str_replace('/', '-', $request['renewal_date']))), NULL, $online_payment_by = NULL, $ssb_id, 'DR');
                                $transactionData['is_renewal'] = 0;
                                $transactionData['created_at'] = date("Y-m-d H:i:s", strtotime(str_replace('/', '-', $request['renewal_date'])));
                                $updateTransaction = Transcation::find($createTransaction);
                                $updateTransaction->update($transactionData);
                                TranscationLog::where('transaction_id', $transactionData)->update(['is_renewal' => 0, 'created_at' => date("Y-m-d H:i:s", strtotime(str_replace('/', '-', $request['renewal_date'])))]);
                            } else {
                                $ssbFromId = NULL;
                                $ssbAccountTranFromId = NULL;
                            }
                            $ssbAccountAmount = $renewSavingOpeningBlanace + $request['amount'][$key];
                            $ssb_id = $depositSSBId = $savingAccountDetail->id;
                            $sResult = SavingAccount::find($ssb_id);
                            $sData['balance'] = $ssbAccountAmount;
                            $sResult->update($sData);
                            $ssb['saving_account_id'] = $ssb_id;
                            $ssb['account_no'] = $accountNumber;
                            $ssb['opening_balance'] = $renewSavingOpeningBlanace + $request['amount'][$key];
                            $ssb['withdrawal'] = 0;
                            if ($branch_id != $investmentDetail->branch_id) {
                                $branchName = getBranchNameByBrachAuto($branch_id);
                                $ssb['description'] = $mtssb . ' - From ' . $branchName->name . '';
                            } else {
                                $ssb['description'] = $mtssb;
                            }
                            $ssb['associate_id'] = $request['member_id'];
                            $ssb['branch_id'] = $branch_id;
                            $ssb['type'] = 2;
                            $ssb['currency_code'] = 'INR';
                            $ssb['payment_type'] = 'CR';
                            $ssb['payment_mode'] = $request['payment_mode'];
                            $ssb['reference_no'] = $rno;
                            $ssb['deposit'] = $request['amount'][$key];
                            $ssb['created_at'] = $request['renewal_date'];
                            $ssbAccountTran = SavingAccountTranscation::create($ssb);
                            $ssbToId = $savingAccountDetail->id;
                            $ssbAccountTranToId = $ssbAccountTran->id;
                            $satRefId = CommanTransactionsController::createTransactionReferences($ssbAccountTran->id, $request['investment_id'][$key]);
                            $createTransaction = CommanTransactionsController::createTransaction($satRefId, 5, 0, $request['investment_member_id'][$key], $branch_id, $branchCode, $amountArraySsb, $request['payment_mode'], $request['deposite_by_name'], $request['member_id'], $accountNumber, $request['cheque-number'], $request['bank-name'], $request['branch-name'], date("Y-m-d", strtotime(str_replace('/', '-', $request['renewal_date']))), NULL, $online_payment_by = NULL, $ssb_id, 'CR');
                            $transactionData['is_renewal'] = 0;
                            //$transactionData['created_at'] = $request['renewal_date'];
                            $updateTransaction = Transcation::find($createTransaction);
                            $updateTransaction->update($transactionData);
                            TranscationLog::where('transaction_id', $transactionData)->update(['is_renewal' => 0, 'created_at' => date("Y-m-d", strtotime(str_replace('/', '-', $request['renewal_date'])))]);
                            // $createDayBook = CommanTransactionsController::createDayBook($createTransaction,$satRefId,1,$request['investment_id'][$key],$request['member_id'],$request['investment_member_id'][$key],$totalbalance,$request['amount'][$key],$withdrawal=0,$ssb['description'],$ssbAccountNumber,$branch_id,$branchCode,$amountArraySsb,$request['payment_mode'],$request['deposite_by_name'],$request['member_id'],$accountNumber,$request['cheque-number'],$request['bank-name'],$request['branch-name'],date("Y-m-d", strtotime( str_replace('/','-',$request['renewal_date'] ) ) ),NULL,$online_payment_by=NULL,$ssb_id,'CR'); 
                            // ---------------------------  Day book modify --------------------------    
                            $lastAmount = Daybook::where('investment_id', $request['investment_id'][$key])->where('account_no', $accountNumber)->whereIN('transaction_type', [1])->whereDate('created_at', date("Y-m-d", strtotime(convertDate($request['renewal_date']))))->orderby('id', 'desc')->first();
                            if ($lastAmount) {
                                $lastBalance = $lastAmount->opening_balance + $request['amount'][$key];
                            } else {
                                $lastAmount = Daybook:: /*where('investment_id',$request['investment_id'][$key])->*/where('account_no', $accountNumber)->whereIN('transaction_type', [1])->whereDate('created_at', '<', date("Y-m-d", strtotime(convertDate($request['renewal_date']))))->orderby('id', 'desc')->first();
                                if ($lastAmount) {
                                    $lastBalance = $lastAmount->opening_balance + $request['amount'][$key];
                                } else {
                                    $lastOpeningAmount = Daybook::where('account_no', $accountNumber)->whereIN('transaction_type', [1])->orderby('id', 'desc')->first();
                                    $lastBalance = $lastOpeningAmount->opening_balance + $request['amount'][$key];
                                }
                            }
                            if ($lastAmount) {
                                $nextRenewal = $lastAmount = Daybook::where('investment_id', $request['investment_id'][$key])->whereIN('transaction_type', [1])->where('account_no', $accountNumber)->whereDate('created_at', '>', date("Y-m-d", strtotime(convertDate($lastAmount->created_at))))->get();
                                foreach ($nextRenewal as $key1 => $value) {
                                    $daybookData['opening_balance'] = $value->opening_balance + $request['amount'][$key];
                                    $dayBook = Daybook::find($value->id);
                                    $dayBook->update($daybookData);
                                }
                            }
                            $createDayBook = CommanTransactionsController::createDayBookNew($createTransaction, $satRefId, 2, $request['investment_id'][$key], $request['member_id'], $request['investment_member_id'][$key], $lastBalance, $request['amount'][$key], $withdrawal = 0, $ssb['description'], $ssbAccountNumber, $branch_id, $branchCode, $amountArraySsb, $request['payment_mode'], $request['deposite_by_name'], $request['member_id'], $accountNumber, $request['cheque-number'], $request['bank-name'], $request['branch-name'], $globaldate, NULL, $online_payment_by = NULL, $collectionSSBId, 'CR', $received_cheque_id, $cheque_deposit_bank_id, $cheque_deposit_bank_ac_id, $online_deposit_bank_id, $online_deposit_bank_ac_id);
                            // ---------------------------  HEAD IMPLEMENT --------------------------
                            $planId = $investmentDetail->plan_id;
                            $this->investHeadCreateSSB($request['amount'][$key], $globaldate, $request['investment_id'][$key], $planId, $cheque_no, $cheque_id, $cheque_date, $online_transction_no, $online_transction_date, $online_deposit_bank_id, $online_deposit_bank_ac_id, $branch_id, $request['member_id'], $request['investment_member_id'][$key], $collectionSSBId, $ssbAccountTran->id, $pmodeAll, $ssbAccountNumber, $ssbFromId, $ssbAccountTranFromId, $ssbToId, $ssbAccountTranToId);
                            //--------------------------------HEAD IMPLEMENT  ------------------------
                            $daybookData['is_renewal'] = 0;
                            //$daybookData['created_at'] = date("Y-m-d H:i:s", strtotime( str_replace('/','-',$request['renewal_date'] ) ) );
                            $dayBook = Daybook::find($createDayBook);
                            $dayBook->update($daybookData);
                            /*--------------------cheque assign -----------------------*/
                            if ($request['payment_mode'] == 1) {
                                $receivedPayment['type'] = 3;
                                $receivedPayment['branch_id'] = $branch_id;
                                $receivedPayment['investment_id'] = $request['investment_id'][$key];
                                $receivedPayment['day_book_id'] = $createDayBook;
                                $receivedPayment['cheque_id'] = $request['cheque_id'];
                                $receivedPayment['created_at'] = $globaldate;
                                $receivedCreate = \App\Models\ReceivedChequePayment::create($receivedPayment);
                                $dataRC['status'] = 3;
                                $receivedcheque = \App\Models\ReceivedCheque::find($request['cheque_id']);
                                $receivedcheque->update($dataRC);
                            }
                            /*-----------------------------------------------------------*/
                            $transaction = $this->transactionData($request->all(), $request['investment_id'][$key], $request['amount'][$key]);
                            $ipTransaction = Investmentplantransactions::create($transaction);
                            $ipTransactionData['is_renewal'] = 0;
                            $ipTransactionData['created_at'] = date("Y-m-d H:i:s", strtotime(str_replace('/', '-', $request['renewal_date'])));
                            $updateipTransaction = Investmentplantransactions::find($ipTransaction->id);
                            $updateipTransaction->update($ipTransactionData);
                            $investmentCurrentBalance = getSavingCurrentBalance($ssb_id, $accountNumber);
                            $save = 1;
                            $rAmount = $investmentCurrentBalance;
                        } else {
                            //Ivestment 
                            //$investmentAmount = Memberinvestments::select('due_amount')->where('id',$request['investment_id'][$key])->first();
                            $investmentDetail = $this->getInvestmentPlanDetail($request['investment_id'][$key]);
                            if ($request->renewplan_id == 3) {
                                $data['due_amount'] = 0;
                            } else {
                                $data['due_amount'] = $request['deo_amount'][$key];
                            }
                            if ($data['due_amount'] && $data['due_amount'] > 0) {
                                $investment = Memberinvestments::find($request['investment_id'][$key]);
                                $investment->update($data);
                            }
                            if ($request['payment_mode'] == 4) {
                                $sAccount = $this->getSavingAccountDetails($request['member_id']);
                                $ssbAccountAmount = $sAccount->balance - $request['amount'][$key];
                                $ssb_id = $collectionSSBId = $sAccount->id;
                                $sResult = SavingAccount::find($ssb_id);
                                $sData['balance'] = $ssbAccountAmount;
                                $sResult->update($sData);
                                $ssb['saving_account_id'] = $ssb_id;
                                $ssb['account_no'] = $sAccount->account_no;
                                $ssb['opening_balance'] = $ssbAccountAmount;
                                $ssb['deposit'] = NULL;
                                $ssb['withdrawal'] = $request['amount'][$key];
                                //$ssb['description']=''.$accountNumber.'/Auto debit';  
                                /*if($branch_id != $investmentDetail->branch_id){
                                    $branchName = getBranchName(Auth::user()->id);
                                    $ssb['description'] = 'Cash withdrawal - From '.$branch.'';
                                }else{
                                    $ssb['description'] = 'Cash withdrawal';    
                                }*/
                                $ssb['description'] = 'Fund Trf. To ' . $accountNumber . '';
                                $ssb['branch_id'] = $branch_id;
                                $ssb['type'] = 6;
                                $ssb['associate_id'] = $request['member_id'];
                                $ssb['currency_code'] = 'INR';
                                $ssb['payment_type'] = 'CR';
                                $ssb['payment_mode'] = $request['payment_mode'];
                                $ssb['is_renewal'] = 0;
                                $ssb['created_at'] = $request['renewal_date'];
                                $ssbAccountTran = SavingAccountTranscation::create($ssb);
                                $ssbFromId = $ssb_id;
                                $ssbAccountTranFromId = $ssbAccountTran->id;
                                $satRefId = CommanTransactionsController::createTransactionReferences($ssbAccountTran->id, $request['investment_id'][$key]);
                            } else {
                                $satRefId = NULL;
                                $ssbFromId = NULL;
                                $ssbAccountTranFromId = NULL;
                            }
                            $ssbToId = NULL;
                            $ssbAccountTranToId = NULL;
                            $sAccount = $this->getSavingAccountDetails($request['investment_member_id'][$key]);
                            if ($sAccount) {
                                $ssbAccountId = $sAccount->id;
                            } else {
                                $ssbAccountId = 0;
                            }
                            /*$rplanId = $request['renew_investment_plan_id'];
                            if($rplanId==7){
                              $description = 'SDD Collection';  
                            }elseif($rplanId==10){
                              $description = 'SRD collection';  
                            }elseif($rplanId==5){
                              $description = 'FRD collection';  
                            }elseif($rplanId==3){
                              $description = 'SMB collection';  
                            }elseif($rplanId==2){
                              $description = 'SK collection';  
                            }elseif($rplanId==6){
                              $description = 'SJ collection';  
                            }*/
                            if ($investmentDetail) {
                                $totalbalance = $investmentDetail->current_balance + $request['amount'][$key];
                                $investData['current_balance'] = $totalbalance;
                                $rplanId = $investmentDetail->plan_id;
                                if ($request['payment_mode'] == 4) {
                                    $assAccount = $this->getSavingAccountDetails($request['member_id']);
                                    $description = 'Fund Rec. From ' . $assAccount->account_no . '';
                                } else {
                                    if ($branch_id != $investmentDetail->branch_id) {
                                        $branchName = getBranchName(Auth::user()->id);
                                        if ($rplanId == 7) {
                                            $description = 'SDD Collection - From ' . $branchName->name . '';
                                        } elseif ($rplanId == 10) {
                                            $description = 'SRD collection - From ' . $branchName->name . '';
                                        } elseif ($rplanId == 5) {
                                            $description = 'FRD collection - From ' . $branchName->name . '';
                                        } elseif ($rplanId == 3) {
                                            $description = 'SMB collection - From ' . $branchName->name . '';
                                        } elseif ($rplanId == 2) {
                                            $description = 'SK collection - From ' . $branchName->name . '';
                                        } elseif ($rplanId == 6) {
                                            $description = 'SJ collection - From ' . $branchName->name . '';
                                        } elseif ($rplanId == 11) {
                                            $description = 'SB collection - From ' . $branchName->name . '';
                                        } elseif ($rplanId == 12) {
                                            $description = 'SSB Child collection- From ' . $branchName . '';
                                        }
                                    } else {
                                        if ($rplanId == 7) {
                                            $description = 'SDD Collection';
                                        } elseif ($rplanId == 10) {
                                            $description = 'SRD collection';
                                        } elseif ($rplanId == 5) {
                                            $description = 'FRD collection';
                                        } elseif ($rplanId == 3) {
                                            $description = 'SMB collection';
                                        } elseif ($rplanId == 2) {
                                            $description = 'SK collection';
                                        } elseif ($rplanId == 6) {
                                            $description = 'SJ collection';
                                        } elseif ($rplanId == 11) {
                                            $description = 'SB collection';
                                        } elseif ($rplanId == 12) {
                                            $description = 'SSB Child collection';
                                        }
                                    }
                                }
                            } else {
                                $totalbalance = '';
                                $description = '';
                            }
                            $createTransaction = CommanTransactionsController::createTransaction($satRefId, 4, $request['investment_id'][$key], $request['investment_member_id'][$key], $branch_id, $branchCode, $amountArraySsb, $request['payment_mode'], $request['deposite_by_name'], $request['member_id'], $accountNumber, $request['cheque-number'], $request['bank-name'], $request['branch-name'], date("Y-m-d", strtotime(str_replace('/', '-', $request['renewal_date']))), NULL, $online_payment_by = NULL, $ssbAccountId, 'CR');
                            $transactionData['is_renewal'] = 0;
                            //$transactionData['created_at'] = date("Y-m-d H:i:s", strtotime( str_replace('/','-',$request['renewal_date'] ) ) );
                            $updateTransaction = Transcation::find($createTransaction);
                            $updateTransaction->update($transactionData);
                            TranscationLog::where('transaction_id', $transactionData)->update(['is_renewal' => 0, 'created_at' => date("Y-m-d", strtotime(str_replace('/', '-', $request['renewal_date'])))]);
                            // $createDayBook = CommanTransactionsController::createDayBook($createTransaction,$satRefId,4,$request['investment_id'][$key],$request['member_id'],$request['investment_member_id'][$key],$totalbalance,$request['amount'][$key],$withdrawal=0,$description,$ref=NULL,$branch_id,$branchCode,$amountArraySsb,$request['payment_mode'],$request['deposite_by_name'],$request['member_id'],$accountNumber,$request['cheque-number'],$request['bank-name'],$request['branch-name'],$globaldate,NULL,$online_payment_by=NULL,$ssbAccountId,'CR'); 
                            // ---------------------------  Day book modify --------------------------    
                            $lastBalance = getLastOpeingBalance($request['investment_id'][$key]) + $request['amount'][$key];
                            $createDayBook = CommanTransactionsController::createDayBookNew($createTransaction, $satRefId, 4, $request['investment_id'][$key], $request['member_id'], $request['investment_member_id'][$key], $lastBalance, $request['amount'][$key], $withdrawal = 0, $description, $ref = NULL, $branch_id, $branchCode, $amountArraySsb, $request['payment_mode'], $request['deposite_by_name'], $request['member_id'], $accountNumber, $request['cheque-number'], $request['bank-name'], $request['branch-name'], $globaldate, NULL, $online_payment_by = NULL, $collectionSSBId, 'CR', $received_cheque_id, $cheque_deposit_bank_id, $cheque_deposit_bank_ac_id, $online_deposit_bank_id, $online_deposit_bank_ac_id);
                            updateRenewalTransaction($accountNumber);
                            // ---------------------------  HEAD IMPLEMENT --------------------------
                            $planId = $investmentDetail->plan_id;
                            $this->investHeadCreate($request['amount'][$key], $globaldate, $request['investment_id'][$key], $planId, $cheque_no, $cheque_id, $cheque_date, $online_transction_no, $online_transction_date, $online_deposit_bank_id, $online_deposit_bank_ac_id, $branch_id, $request['member_id'], $request['investment_member_id'][$key], $collectionSSBId, $createDayBook, $pmodeAll, $investmentDetail->account_number, $ssbFromId, $ssbAccountTranFromId, $ssbToId, $ssbAccountTranToId);
                            //--------------------------------HEAD IMPLEMENT  ------------------------
                            /*-------------------------------  Commission  Section Start ------------------------------------*/
                            $entryTime = date("h:i:s");
                            $dateForRenew = date("Y-m-d", strtotime(convertDate($request['renewal_date'])));
                            $renewal_date_time = date("Y-m-d " . $entryTime . "", strtotime(str_replace('/', '-', $request['renewal_date'])));
                            $investment_id = $request['investment_id'][$key];
                            $tenureMonth = $investmentDetail->tenure * 12;
                            $amountkey = $request['amount'][$key];
                            $daybook_id = $createDayBook;
                            $plan_id = $investmentDetail->plan_id;
                            $investment_associte_id = $investmentDetail->associate_id;
                            $collector_id = $request['member_id'];
                            $comEntry['investment_id'] = $investment_id;
                            $comEntry['investment_plan_id'] = $plan_id;
                            $comEntry['investment_associte_id'] = $investment_associte_id;
                            $comEntry['collector_id'] = $collector_id;
                            $comEntry['branch_id'] = $branch_id;
                            $comEntry['amount'] = $amountkey;
                            $comEntry['renew_date'] = $dateForRenew;
                            $comEntry['daybook_id'] = $daybook_id;
                            $comEntry['tenure_month'] = $tenureMonth;
                            $comEntry['renewal_date_time'] = $renewal_date_time;
                            // $commissionEntry = \App\Models\CommissionEntryDetail::create($comEntry);
                            /*---
                             $dateForRenew=$request['renewal_date'];
                            $Commission=getMonthlyWiseRenewalNew($request['investment_id'][$key],$request['amount'][$key],$dateForRenew); 
                            foreach ($Commission as  $val) {
                                $tenureMonth=$investmentDetail->tenure*12;
                                 $commission =CommanTransactionsController:: commissionDistributeInvestmentRenew($investmentDetail->associate_id,$request['investment_id'][$key],3,$val['amount'],$val['month'],$investmentDetail->plan_id,$branch_id,$tenureMonth,$createDayBook,$val['type']);
                                 $commission_collection =CommanTransactionsController::commissionCollectionInvestmentRenew($request['member_id'],$request['investment_id'][$key],5,$val['amount'],$val['month'],$investmentDetail->plan_id,$branch_id,$tenureMonth,$createDayBook,$val['type']);
                                /*----- ------  credit business start ---- ---------------   
                                $creditBusiness =CommanTransactionsController::associateCreditBusiness($investmentDetail->associate_id,$request['investment_id'][$key],1,$val['amount'],$val['month'],$investmentDetail->plan_id,$tenureMonth,$createDayBook);
                                /*----- ------  credit business end ---- ---------------         
                            }
                            */
                            /*-----------------------------  Commission  Section End -------------------------------------*/
                            if ($investmentDetail) {
                                $sResult = Memberinvestments::find($request['investment_id'][$key]);
                                $investData['current_balance'] = $totalbalance;
                                $sResult->update($investData);
                            } else {
                                $totalbalance = '';
                            }
                            $daybookData['is_renewal'] = 0;
                            $daybookData['created_at'] = date("Y-m-d H:i:s", strtotime(str_replace('/', '-', $request['renewal_date'])));
                            $dayBook = Daybook::find($createDayBook);
                            $dayBook->update($daybookData);
                            /*--------------------cheque assign -----------------------*/
                            if ($request['payment_mode'] == 1) {
                                $receivedPayment['type'] = 3;
                                $receivedPayment['branch_id'] = $branch_id;
                                $receivedPayment['investment_id'] = $request['investment_id'][$key];
                                $receivedPayment['day_book_id'] = $createDayBook;
                                $receivedPayment['cheque_id'] = $request['cheque_id'];
                                $receivedPayment['created_at'] = $globaldate;
                                $receivedCreate = \App\Models\ReceivedChequePayment::create($receivedPayment);
                                $dataRC['status'] = 3;
                                $receivedcheque = \App\Models\ReceivedCheque::find($request['cheque_id']);
                                $receivedcheque->update($dataRC);
                            }
                            /*-----------------------------------------------------------*/
                            $transaction = $this->transactionData($request->all(), $request['investment_id'][$key], $request['amount'][$key]);
                            $ipTransaction = Investmentplantransactions::create($transaction);
                            $ipTransactionData['is_renewal'] = 0;
                            $updateipTransaction = Investmentplantransactions::find($ipTransaction->id);
                            $updateipTransaction->update($ipTransactionData);
                            $investmentCurrentBalance = getInvestmentCurrentBalance($investmentDetail->id, $investmentDetail->account_number);
                            $save = 1;
                            $rAmount = $investmentCurrentBalance;
                        }
                    } else {
                        $save = 0;
                        array_push($timeInvestmentArray, $accountNumber);
                    }
                }
                $contactNumber = array();
                if ($key != 0 && $request['investment_member_phone_no'][$key]) {
                    $contactNumber[] = str_replace('"', '', $request['investment_member_phone_no'][$key]);
                    $text = 'Dear Member, Your A/C ' . $accountNumber . ' has been Credited on ' . date("d M Y", strtotime(str_replace('/', '-', $request['renewal_date']))) . ' With Rs. ' . round($request['amount'][$key], 2) . ' Cur Bal: ' . round($rAmount, 2) . '. Thanks Have a nice day';
                    $templateId = 1207161726461603982;
                    $sendToMember = new Sms();
                    $sendToMember->sendSms($contactNumber, $text, $templateId);
                }
            }
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('alert', $ex->getMessage());
        }
        if ($save > 0) {
            //redirect()->route('investment/recipt/'.$insertedid);
            //return redirect('branch/renew/recipt/'.$insertedid);
            //return back()->with('success', 'Saved Successfully!');
            /*$data['title'] = "Renewal Recipt";
            $data['renewFields'] = $request->all();
            $data['branchCode'] = $branchCode;
            $data['branchName'] = $branchName;
            return view('templates.branch.investment_management.renewal.recipt', $data);*/
            //$branchArray  = array('branchName' => $branchName, 'timeInvestmentArray' => $timeInvestmentArray);
            $explodeArray = json_encode($request->all());
            $encodeRequests = base64_encode($explodeArray);
            $encodebranchCode = base64_encode($branchCode);
            $encodebranchName = base64_encode($branchName);
            $ssb = '';
            // if($renewPlanId==2)
            // {   
            //      $ssb = json_encode($ssbAccountTran->id); 
            //       return redirect('branch/renew/recipt/'.$encodeRequests.'/'.$encodebranchCode.'/'.$encodebranchName.'/'.$ssb.'');  
            // }
            // else{
            //      return redirect('branch/renew/recipt/'.$encodeRequests.'/'.$encodebranchCode.'/'.$encodebranchName.'');
            // }
            if ($renewPlanId == 2) {
                $ssb = json_encode($ssbAccountTran->id);
            } else {
                $ssb = $createDayBook;
                // return redirect('branch/renew/recipt/'.$encodeRequests.'/'.$encodebranchCode.'/'.$encodebranchName.'');
            }
            return redirect('branch/renew/recipt/' . $encodeRequests . '/' . $encodebranchCode . '/' . $encodebranchName . '/' . $ssb . '');
        } else {
            return back()->with('alert', 'Problem With Register New Plan');
        }
    }
    public function renewalDetails($url, $branchCode, $branchName, $ssb = NULL)
    {
        $decodedurl = base64_decode($url);
        $explodeArray = json_decode($decodedurl);
        $branchCode = base64_decode($branchCode);
        $branchName = base64_decode($branchName);
        $arrayVal = (array) $explodeArray;
        $data['renewFields'] = $arrayVal;
        if ($ssb != '') {
            if ($data['renewFields']['renewplan_id'] == 2) {
                $data['ssb_amount'] = SavingAccountTranscation::where('id', $ssb)->first();
            } else {
                $data['ssb_amount'] = Daybook::where('id', $ssb)->first();
            }
        }
        $data['title'] = "Renewal Recipt";
        $data['branchCode'] = $branchCode;
        $data['branchName'] = $branchName;
        //$data['timeInvestmentArray'] = $explodeBranchArray['timeInvestmentArray'];
        //$data['timeInvestmentString'] = implode(",",$explodeBranchArray['timeInvestmentArray']);
        /*Code added by Amar*/
        // return view('templates.branch.investment_management.renewal.recipt', $data);
        if (isset($data['renewFields']['form_data']) && $data['renewFields']['form_data'] != NULL) {
            parse_str($data['renewFields']['form_data'], $data_new);
            $arr_new['renewFields'] = array_merge($data, $data_new);
            $arr_new['title'] = "Renewal Recipt";
            $arr_new['branchCode'] = $branchCode;
            $arr_new['branchName'] = $branchName;
            // print_r($array);
            //dd($arr_new);
            if (isset($arr_new['renewFields']['renewplan_id']) && $arr_new['renewFields']['renewplan_id'] == 2) {
                $arr_new['ssb_amount'] = SavingAccountTranscation::where('id', $ssb)->first();
            } else {
                $arr_new['ssb_amount'] = Daybook::where('id', $ssb)->first();
            }
        } else {
            if (isset($data['renewFields']['renewplan_id']) && $data['renewFields']['renewplan_id'] == 2) {
                $data['ssb_amount'] = SavingAccountTranscation::where('id', $ssb)->first();
            } else {
                $data['ssb_amount'] = Daybook::where('id', $ssb)->first();
            }
        }
        //dd($arr_new);
        if (isset($arr_new) && $arr_new != NULL) {
            //echo "in if";exit;
            return view('templates.branch.investment_management.renewal.recipt', $arr_new);
        } else {
            //  echo "in else";exit;
            return view('templates.branch.investment_management.renewal.recipt', $data);
        }
        /*End of code*/
    }
    /**
     * Get saving account id.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getSavingAccountDetails($mId)
    {
        $getDetails = SavingAccount::where('member_id', $mId)->select('id', 'balance', 'account_no')->first();
        return $getDetails;
    }
    /**
     * Get saving account id.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getSavingAccountId($investmentId)
    {
        $getDetails = SavingAccount::where('member_investments_id', $investmentId)->select('id', 'balance', 'account_no')->first();
        return $getDetails;
    }
    /**
     * Get investment plan detail.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getInvestmentPlanDetail($id)
    {
        $getDetails = Memberinvestments::where('id', $id)->first();
        return $getDetails;
    }
    /**
     * Get investment plans transaction data to store.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function transactionData($request, $investmentId, $amount)
    {
        $getBranchId = getUserBranchId(Auth::user()->id);
        $branch_id = $getBranchId->id;
        $getBranchCode = getBranchCode($branch_id);
        $branchCode = $getBranchCode->branch_code;
        $sAccount = $this->getSavingAccountDetails($request['member_id']);
        $data['investment_id'] = $investmentId;
        $data['plan_id'] = $request['renew_investment_plan_id'];
        $data['member_id'] = $request['member_id'];
        $data['branch_id'] = $branch_id;
        $data['branch_code'] = $branchCode;
        $data['deposite_amount'] = $amount;
        $data['deposite_date'] = $request['renewal_date'];
        $data['deposite_month'] = date("m", strtotime(str_replace('/', '-', $request['renewal_date'])));
        $data['payment_mode'] = $request['payment_mode'];
        if ($sAccount->id) {
            $data['saving_account_id'] = $sAccount->id;
        } else {
            $data['saving_account_id'] = NULL;
        }
        return $data;
    }
    public function updateDate()
    {
        $nullInvestments = DB::table('investment_plan_transactions')->whereNull('deposite_date')->get();
        foreach ($nullInvestments as $key => $nullInvestment) {
            $date = $nullInvestment->created_at;
            $changeDate = date("Y-m-d", strtotime(convertDate($date)));
            $month = date("m", strtotime(convertDate($date)));
            $sResult = Investmentplantransactions::find($nullInvestment->id);
            $data['deposite_date'] = $changeDate;
            $data['deposite_month'] = $month;
            $sResult->update($data);
        }
    }
    public function investHeadCreate($amount, $globaldate, $investmentId, $planId, $cheque_no, $cheque_id, $cheque_date, $online_transction_no, $online_transction_date, $online_deposit_bank_id, $online_deposit_bank_ac_id, $branch_id, $associate_id, $memberId, $ssbId, $createDayBook, $payment_mode, $investmentAccountNoRd, $ssbFromId, $ssbAccountTranFromId, $ssbToId, $ssbAccountTranToId)
    {
        $amount = $amount;
        $daybookRefRD = CommanTransactionsController::createBranchDayBookReferenceNew($amount, $globaldate);
        $refIdRD = $daybookRefRD;
        $currency_code = 'INR';
        $headPaymentModeRD = 0;
        $payment_type_rd = 'CR';
        $type_id = $investmentId;
        $entry_date = date("Y-m-d", strtotime(convertDate($globaldate)));
        $entry_time = date("H:i:s", strtotime(convertDate($globaldate)));
        $created_by = 2;
        $created_by_id = Auth::user()->id;
        $created_at = date("Y-m-d H:i:s", strtotime(convertDate($globaldate)));
        $updated_at = date("Y-m-d H:i:s", strtotime(convertDate($globaldate)));
        $planDetail = getPlanDetail($planId);
        $type = 3;
        $sub_type = 32;
        $planCode = $planDetail->plan_code;
        ;
        if ($planCode == 703) {
            $head1Invest = 1;
            $head2Invest = 8;
            $head3Invest = 20;
            $head4Invest = 56;
            $head5Invest = NULL;
            $head_id = 56;
        }
        if ($planCode == 709) {
            $head1Invest = 1;
            $head2Invest = 8;
            $head3Invest = 20;
            $head4Invest = 59;
            $head5Invest = 80;
            $head_id = 80;
        }
        if ($planCode == 708) {
            $head1Invest = 1;
            $head2Invest = 8;
            $head3Invest = 20;
            $head4Invest = 59;
            $head5Invest = 85;
            $head_id = 85;
        }
        if ($planCode == 705) {
            $head1Invest = 1;
            $head2Invest = 8;
            $head3Invest = 20;
            $head4Invest = 57;
            $head5Invest = 79;
            $head_id = 79;
        }
        if ($planCode == 707) {
            $head1Invest = 1;
            $head2Invest = 8;
            $head3Invest = 20;
            $head4Invest = 59;
            $head5Invest = 81;
            $head_id = 81;
        }
        if ($planCode == 713) {
            $head1Invest = 1;
            $head2Invest = 8;
            $head3Invest = 20;
            $head4Invest = 59;
            $head5Invest = 84;
            $head_id = 84;
        }
        if ($planCode == 710) {
            $head1Invest = 1;
            $head2Invest = 8;
            $head3Invest = 20;
            $head4Invest = 58;
            $head5Invest = NULL;
            $head_id = 58;
        }
        if ($planCode == 712) {
            $head1Invest = 1;
            $head2Invest = 8;
            $head3Invest = 20;
            $head4Invest = 57;
            $head5Invest = 78;
            $head_id = 78;
        }
        if ($planCode == 706) {
            $head1Invest = 1;
            $head2Invest = 8;
            $head3Invest = 20;
            $head4Invest = 57;
            $head5Invest = 77;
            $head_id = 77;
        }
        if ($planCode == 704) {
            $head1Invest = 1;
            $head2Invest = 8;
            $head3Invest = 20;
            $head4Invest = 59;
            $head5Invest = 83;
            $head_id = 83;
        }
        if ($planCode == 718) {
            $head1Invest = 1;
            $head2Invest = 8;
            $head3Invest = 20;
            $head4Invest = 59;
            $head5Invest = 82;
            $head_id = 82;
        }
        if ($planCode == 721) {
            $head1Invest = 1;
            $head2Invest = 8;
            $head3Invest = 207;
            $head4Invest = 207;
            $head5Invest = 207;
            $head_id = 207;
        }
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
        if ($payment_mode == 1) {  // cheque moade 
            $headPaymentModeRD = 1;
            $chequeDetail = \App\Models\ReceivedCheque::where('id', $cheque_id)->first();
            $cheque_no = $chequeDetail->cheque_no;
            $cheque_date = $cheque_date;
            $cheque_bank_from = $chequeDetail->bank_name;
            $cheque_bank_ac_from = $chequeDetail->cheque_account_no;
            $cheque_bank_ifsc_from = NULL;
            $cheque_bank_branch_from = $chequeDetail->branch_name;
            $cheque_bank_to = $chequeDetail->deposit_bank_id;
            $cheque_bank_ac_to = $chequeDetail->deposit_account_id;
            $getBankHead = \App\Models\SamraddhBank::where('id', $cheque_bank_to)->first();
            $head11 = 2;
            $head21 = 10;
            $head31 = 27;
            $head41 = $getBankHead->account_head_id;
            $head51 = NULL;
            $rdDesDR = 'Bank A/c Dr ' . $amount . '/-';
            $rdDesCR = 'To ' . $planDetail->name . '(' . $investmentAccountNoRd . ')  A/c Cr ' . $amount . '/-';
            $rdDes = 'Amount received for ' . $planDetail->name . ' A/C Renewal (' . $investmentAccountNoRd . ') through cheque(' . $cheque_no . ')';
            $rdDesMem = $planDetail->name . ' A/C Renewal (' . $investmentAccountNoRd . ') through cheque(' . $cheque_no . ')';
            //bank head entry
            $allTranRDcheque = CommanTransactionsController::createAllHeadTransaction($refIdRD, $branch_id, $bank_id = NULL, $bank_ac_id = NULL, $head41, $type, $sub_type, $type_id, $createDayBook, $associate_id, $memberId, $branch_id_to = NULL, $branch_id_from = NULL, $amount, $amount, $amount, $rdDes, 'DR', $headPaymentModeRD, $currency_code, $amount_to_id = NULL, $amount_to_name = NULL, $amount_from_id = NULL, $amount_from_name = NULL, $jv_unique_id = NULL, $v_no, $v_date, $ssb_account_id_from, $ssb_account_id_to = NULL, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, 0, $cheque_id, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_from_id = NULL, $cheque_bank_ac_from_id = NULL, $cheque_bank_to, $cheque_bank_ac_to, getSamraddhBank($cheque_bank_to)->bank_name, $cheque_bank_to_branch = NULL, getSamraddhBankAccountId($cheque_bank_ac_to)->account_no, getSamraddhBankAccountId($cheque_bank_ac_to)->ifsc_code, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_from_id = NULL, $transction_bank_from_ac_id = NULL, $transction_bank_to, $transction_bank_ac_to, $transction_bank_to_name = NULL, $transction_bank_to_ac_no = NULL, $transction_bank_to_branch = NULL, $transction_bank_to_ifsc = NULL, $transction_date, $created_by, $created_by_id);
            /*$allTranRDcheque=CommanTransactionsController::createAllTransactionNew($refIdRD,$branch_id,$bank_id=NULL,$bank_ac_id=NULL,$head11,$head21,$head31,$head41,$head51,$type,$sub_type,$type_id,$associate_id,$memberId,$branch_id_to=NULL,$branch_id_from=NULL,$opening_balance=NULL,$amount,$closing_balance=NULL,$rdDes,'CR',$headPaymentModeRD,$currency_code,$amount_to_id=NULL,$amount_to_name=NULL,$amount_from_id=NULL,$amount_from_name=NULL,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at,$updated_at,$createDayBook);*/
            //bank entry
            $bankCheque = CommanTransactionsController::samraddhBankDaybookNew($refIdRD, $cheque_bank_to, $cheque_bank_ac_to, $type, $sub_type, $type_id, $createDayBook, $associate_id, $memberId, $branch_id, $amount, $amount, $amount, $rdDes, $rdDesDR, $rdDesCR, 'CR', $headPaymentModeRD, $currency_code, $amount_to_id = NULL, $amount_to_name = NULL, $amount_from_id = NULL, $amount_from_name = NULL, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_bank_to_name = NULL, $transction_bank_to_ac_no = NULL, $transction_bank_to_branch = NULL, $transction_bank_to_ifsc = NULL, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL);
            //bank balence
            $bankClosing = CommanTransactionsController::checkCreateBankClosing($cheque_bank_to, $cheque_bank_ac_to, $created_at, $amount, 0);
        } elseif ($payment_mode == 2) {  //online transaction
            $headPaymentModeRD = 2;
            $transction_no = $online_transction_no;
            $transction_bank_from = NULL;
            $transction_bank_ac_from = NULL;
            $transction_bank_ifsc_from = NULL;
            $transction_bank_branch_from = NULL;
            $transction_bank_to = $online_deposit_bank_id;
            $transction_bank_ac_to = $online_deposit_bank_ac_id;
            $transction_date = $online_transction_date;
            $getBHead = \App\Models\SamraddhBank::where('id', $transction_bank_to)->first();
            $head111 = 2;
            $head211 = 10;
            $head311 = 27;
            $head411 = $getBHead->account_head_id;
            $head511 = NULL;
            $rdDesDR = 'Bank A/c Dr ' . $amount . '/-';
            $rdDesCR = 'To ' . $planDetail->name . '(' . $investmentAccountNoRd . ')  A/c Cr ' . $amount . '/-';
            $rdDes = 'Amount received for ' . $planDetail->name . ' A/C Renewal (' . $investmentAccountNoRd . ') through online transaction(' . $transction_no . ')';
            $rdDesMem = $planDetail->name . ' A/C Renewal (' . $investmentAccountNoRd . ') through online transaction(' . $transction_no . ')';
            //bank head entry
            $allTranRDonline = CommanTransactionsController::createAllHeadTransaction($refIdRD, $branch_id, $bank_id = NULL, $bank_ac_id = NULL, $head411, $type, $sub_type, $type_id, $createDayBook, $associate_id, $memberId, $branch_id_to = NULL, $branch_id_from = NULL, $amount, $amount, $amount, $rdDes, 'DR', $headPaymentModeRD, $currency_code, $amount_to_id = NULL, $amount_to_name = NULL, $amount_from_id = NULL, $amount_from_name = NULL, $jv_unique_id = NULL, $v_no, $v_date, $ssb_account_id_from, $ssb_account_id_to = NULL, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $cheque_type = NULL, $cheque_id = NULL, $cheque_no = NULL, $cheque_date = NULL, $cheque_bank_from = NULL, $cheque_bank_ac_from = NULL, $cheque_bank_ifsc_from = NULL, $cheque_bank_branch_from = NULL, $cheque_bank_from_id = NULL, $cheque_bank_ac_from_id = NULL, $cheque_bank_to = NULL, $cheque_bank_ac_to = NULL, $cheque_bank_to_name = NULL, $cheque_bank_to_branch = NULL, $cheque_bank_to_ac_no = NULL, $cheque_bank_to_ifsc = NULL, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_from_id = NULL, $transction_bank_from_ac_id = NULL, $transction_bank_to, $transction_bank_ac_to, getSamraddhBank($online_deposit_bank_id)->bank_name, getSamraddhBankAccountId($online_deposit_bank_ac_id)->account_no, $transction_bank_to_branch = NULL, getSamraddhBankAccountId($online_deposit_bank_ac_id)->ifsc_code, $transction_date, $created_by, $created_by_id);
            /*$allTranRDonline=CommanTransactionsController::createAllTransactionNew($refIdRD,$branch_id,$bank_id=NULL,$bank_ac_id=NULL,$head111,$head211,$head311,$head411,$head511,$type,$sub_type,$type_id,$associate_id,$memberId,$branch_id_to=NULL,$branch_id_from=NULL,$opening_balance=NULL,$amount,$closing_balance=NULL,$rdDes,'CR',$headPaymentModeRD,$currency_code,$amount_to_id=NULL,$amount_to_name=NULL,$amount_from_id=NULL,$amount_from_name=NULL,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at,$updated_at,$createDayBook);*/
            //bank entry
            $bankonline = CommanTransactionsController::samraddhBankDaybookNew($refIdRD, $transction_bank_to, $transction_bank_ac_to, $type, $sub_type, $type_id, $createDayBook, $associate_id, $memberId, $branch_id, $opening_balance = NULL, $amount, $closing_balance = NULL, $rdDes, $rdDesDR, $rdDesCR, 'CR', $headPaymentModeRD, $currency_code, $amount_to_id = NULL, $amount_to_name = NULL, $amount_from_id = NULL, $amount_from_name = NULL, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, getSamraddhBank($online_deposit_bank_id)->bank_name, getSamraddhBankAccountId($online_deposit_bank_ac_id)->account_no, $transction_bank_to_branch = NULL, getSamraddhBankAccountId($online_deposit_bank_ac_id)->ifsc_code, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL);
            /*$bankonline=CommanTransactionsController::createSamraddhBankDaybookNew($refIdRD,$transction_bank_to,$transction_bank_ac_to,$type,$sub_type,$type_id,$associate_id,$memberId,$branch_id,$opening_balance=NULL,$amount,$closing_balance=NULL,$rdDes,$rdDesDR,$rdDesCR,'CR',$headPaymentModeRD,$currency_code,$amount_to_id=NULL,$amount_to_name=NULL,$amount_from_id=NULL,$amount_from_name=NULL,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at,$updated_at,$createDayBook,$jv_unique_id=NULL,$cheque_type=NULL,$cheque_id=NULL,$ssb_account_tran_id_to=NULL,$ssb_account_tran_id_from=NULL);*/
            //bank balence
            $bankClosing = CommanTransactionsController::checkCreateBankClosing($transction_bank_to, $transction_bank_ac_to, $created_at, $amount, 0);
        } elseif ($payment_mode == 3) { // ssb
            $headPaymentModeRD = 3;
            $v_no = mt_rand(0, 999999999999999);
            $v_date = $entry_date;
            $ssb_account_id_from = $ssbId;
            $SSBDescTran = 'Amount transfer to ' . $planDetail->name . '(' . $investmentAccountNoRd . ') ';
            $head1rdSSB = 1;
            $head2rdSSB = 8;
            $head3rdSSB = 20;
            $head4rdSSB = 56;
            $head5rdSSB = NULL;
            $ssbDetals = SavingAccount::where('id', $ssb_account_id_from)->first();
            $rdDesDR = $planDetail->name . '(' . $investmentAccountNoRd . ') A/c Dr ' . $amount . '/-';
            $rdDesCR = 'To SSB(' . $ssbDetals->account_no . ') A/c Cr ' . $amount . '/-';
            $rdDes = 'Amount received for ' . $planDetail->name . ' A/C Renewal (' . $investmentAccountNoRd . ') through SSB(' . $ssbDetals->account_no . ')';
            $rdDesMem = $planDetail->name . ' A/C Renewal (' . $investmentAccountNoRd . ') online through SSB(' . $ssbDetals->account_no . ')';
            // ssb  head entry -
            $allTranRDSSB = CommanTransactionsController::createAllHeadTransaction($refIdRD, $branch_id, $bank_id = NULL, $bank_ac_id = NULL, $head4rdSSB, $type, $sub_type, $type_id, $createDayBook, $associate_id, $memberId, $branch_id_to = NULL, $branch_id_from = NULL, $amount, $amount, $amount, $rdDes, 'DR', $headPaymentModeRD, $currency_code, $amount_to_id = NULL, $amount_to_name = NULL, $amount_from_id = NULL, $amount_from_name = NULL, $jv_unique_id = NULL, $v_no, $v_date, $ssb_account_id_from, $ssb_account_id_to = NULL, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $cheque_type = NULL, $cheque_id = NULL, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_from_id = NULL, $cheque_bank_ac_from_id = NULL, $cheque_bank_to, $cheque_bank_ac_to, $cheque_bank_to_name = NULL, $cheque_bank_to_branch = NULL, $cheque_bank_to_ac_no = NULL, $cheque_bank_to_ifsc = NULL, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_from_id = NULL, $transction_bank_from_ac_id = NULL, $transction_bank_to, $transction_bank_ac_to, $transction_bank_to_name = NULL, $transction_bank_to_ac_no = NULL, $transction_bank_to_branch = NULL, $transction_bank_to_ifsc = NULL, $transction_date, $created_by, $created_by_id);
            /*$allTranRDSSB=CommanTransactionsController::createAllTransactionNew($refIdRD,$branch_id,$bank_id=NULL,$bank_ac_id=NULL,$head1rdSSB,$head2rdSSB,$head3rdSSB,$head4rdSSB,$head5rdSSB,$type,$sub_type,$type_id,$associate_id,$memberId,$branch_id_to=NULL,$branch_id_from=NULL,$opening_balance=NULL,$amount,$closing_balance=NULL,$rdDes,'DR',$headPaymentModeRD,$currency_code,$amount_to_id=NULL,$amount_to_name=NULL,$amount_from_id=NULL,$amount_from_name=NULL,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at,$updated_at,$createDayBook);*/
            $branchClosingSSB = CommanTransactionsController::checkCreateBranchClosingDr($branch_id, $created_at, $amount, 0);
            $memberTranInvest77 = CommanTransactionsController::memberTransactionNew($refIdRD, '4', '47', $ssb_account_id_from, $createDayBook, $associate_id, $ssbDetals->member_id, $branch_id, $bank_id = NULL, $account_id = NULL, $amount, $SSBDescTran, 'DR', $headPaymentModeRD, $currency_code, $amount_to_id = $type_id, $amount_to_name = $planDetail->name, $amount_from_id = NULL, $amount_from_name = NULL, $v_no, $v_date, $ssb_account_id_from = NULL, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL);
            /*$memberTranInvest77 = CommanTransactionsController::createMemberTransactionNew($refIdRD,'4','47',$ssb_account_id_from,$associate_id,$ssbDetals->member_id,$branch_id,$bank_id=NULL,$account_id=NULL,$amount,$SSBDescTran,'DR',$headPaymentModeRD,$currency_code,$amount_to_id=$type_id,$amount_to_name=$planDetail->name,$amount_from_id=NULL,$amount_from_name=NULL,$v_no,$v_date,$ssb_account_id_from=NULL,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at,$updated_at,$createDayBook,$ssb_account_tran_id_to=NULL,$ssb_account_tran_id_from=NULL,$jv_unique_id=NULL,$cheque_type=NULL,$cheque_id=NULL);*/
        } else {
            $headPaymentModeRD = 0;
            $head1rdC = 2;
            $head2rdC = 10;
            $head3rdC = 28;
            $head4rdC = 71;
            $head5rdC = NULL;
            $rdDesDR = 'Cash A/c Dr ' . $amount . '/-';
            $rdDesCR = 'To ' . $planDetail->name . '(' . $investmentAccountNoRd . ')  A/c Cr ' . $amount . '/-';
            $rdDes = 'Amount received for ' . $planDetail->name . ' A/C Renewal (' . $investmentAccountNoRd . ') through cash(' . getBranchCode($branch_id)->branch_code . ')';
            $rdDesMem = $planDetail->name . ' A/C Renewal (' . $investmentAccountNoRd . ') through cash(' . getBranchCode($branch_id)->branch_code . ')';
            // branch cash  head entry +
            $allTranRDcash = CommanTransactionsController::createAllHeadTransaction($refIdRD, $branch_id, $bank_id = NULL, $bank_ac_id = NULL, $head3rdC, $type, $sub_type, $type_id, $createDayBook, $associate_id, $memberId, $branch_id_to = NULL, $branch_id_from = NULL, $amount, $amount, $amount, $rdDes, 'DR', $headPaymentModeRD, $currency_code, $amount_to_id = NULL, $amount_to_name = NULL, $amount_from_id = NULL, $amount_from_name = NULL, $jv_unique_id = NULL, $v_no, $v_date, $ssb_account_id_from, $ssb_account_id_to = NULL, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $cheque_type = NULL, $cheque_id = NULL, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_from_id = NULL, $cheque_bank_ac_from_id = NULL, $cheque_bank_to, $cheque_bank_ac_to, $cheque_bank_to_name = NULL, $cheque_bank_to_branch = NULL, $cheque_bank_to_ac_no = NULL, $cheque_bank_to_ifsc = NULL, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_from_id = NULL, $transction_bank_from_ac_id = NULL, $transction_bank_to, $transction_bank_ac_to, $transction_bank_to_name = NULL, $transction_bank_to_ac_no = NULL, $transction_bank_to_branch = NULL, $transction_bank_to_ifsc = NULL, $transction_date, $created_by, $created_by_id);
            /*$allTranRDcash=CommanTransactionsController::createAllTransactionNew($refIdRD,$branch_id,$bank_id=NULL,$bank_ac_id=NULL,$head1rdC,$head2rdC,$head3rdC,$head4rdC,$head5rdC,$type,$sub_type,$type_id,$associate_id,$memberId,$branch_id_to=NULL,$branch_id_from=NULL,$opening_balance=NULL,$amount,$closing_balance=NULL,$rdDes,$payment_type_rd,$headPaymentModeRD,$currency_code,$amount_to_id=NULL,$amount_to_name=NULL,$amount_from_id=NULL,$amount_from_name=NULL,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at,$updated_at,$createDayBook);*/
            //Balance   entry +
            $branchCash = CommanTransactionsController::checkCreateBranchCash($branch_id, $created_at, $amount, 0);
        }
        //branch day book entry +
        $daybookInvest = CommanTransactionsController::branchDayBookNew($refIdRD, $branch_id, $type, $sub_type, $type_id, $createDayBook, $associate_id, $memberId, $branch_id_to = NULL, $branch_id_from = NULL, $opening_balance = NULL, $amount, $closing_balance = NULL, $rdDes, $rdDesDR, $rdDesCR, $payment_type_rd, $headPaymentModeRD, $currency_code, $amount_to_id = NULL, $amount_to_name = NULL, $amount_from_id = NULL, $amount_from_name = NULL, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $is_contra = NULL, $contra_id = NULL, $created_at, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL);
        // Investment head entry +
        $allTranInvest = CommanTransactionsController::createAllHeadTransaction($refIdRD, $branch_id, $bank_id = NULL, $bank_ac_id = NULL, $head_id, $type, $sub_type, $type_id, $createDayBook, $associate_id, $memberId, $branch_id_to = NULL, $branch_id_from = NULL, $amount, $amount, $amount, $rdDes, $payment_type_rd, $headPaymentModeRD, $currency_code, $amount_to_id = NULL, $amount_to_name = NULL, $amount_from_id = NULL, $amount_from_name = NULL, $jv_unique_id = NULL, $v_no, $v_date, $ssb_account_id_from, $ssb_account_id_to = NULL, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $cheque_type = NULL, $cheque_id = NULL, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_from_id = NULL, $cheque_bank_ac_from_id = NULL, $cheque_bank_to, $cheque_bank_ac_to, $cheque_bank_to_name = NULL, $cheque_bank_to_branch = NULL, $cheque_bank_to_ac_no = NULL, $cheque_bank_to_ifsc = NULL, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_from_id = NULL, $transction_bank_from_ac_id = NULL, $transction_bank_to, $transction_bank_ac_to, $transction_bank_to_name = NULL, $transction_bank_to_ac_no = NULL, $transction_bank_to_branch = NULL, $transction_bank_to_ifsc = NULL, $transction_date, $created_by, $created_by_id);
        /*$allTranInvest = CommanTransactionsController::createAllTransactionNew($refIdRD,$branch_id,$bank_id=NULL,$bank_ac_id=NULL,$head1Invest,$head2Invest,$head3Invest,$head4Invest,$head5Invest,$type,$sub_type,$type_id,$associate_id,$memberId,$branch_id_to=NULL,$branch_id_from=NULL,$opening_balance=NULL,$amount,$closing_balance=NULL,$rdDes,$payment_type_rd,$headPaymentModeRD,$currency_code,$amount_to_id=NULL,$amount_to_name=NULL,$amount_from_id=NULL,$amount_from_name=NULL,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at,$updated_at,$createDayBook);*/
        // Member transaction  +
        $memberTranInvest = CommanTransactionsController::memberTransactionNew($refIdRD, $type, $sub_type, $type_id, $createDayBook, $associate_id, $memberId, $branch_id, $bank_id = NULL, $account_id = NULL, $amount, $rdDesMem, $payment_type_rd, $headPaymentModeRD, $currency_code, $amount_to_id = NULL, $amount_to_name = NULL, $amount_from_id = NULL, $amount_from_name = NULL, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL);
        /******** Balance   entry ***************/
        $branchClosing = CommanTransactionsController::checkCreateBranchClosing($branch_id, $created_at, $amount, 0);
    }
    public function investHeadCreateSSB($amount, $globaldate, $investmentId, $planId, $cheque_no, $cheque_id, $cheque_date, $online_transction_no, $online_transction_date, $online_deposit_bank_id, $online_deposit_bank_ac_id, $branch_id, $associate_id, $memberId, $ssbId, $createDayBook, $payment_mode, $investmentAccountNoRd, $ssbFromId, $ssbAccountTranFromId, $ssbToId, $ssbAccountTranToId)
    {
        $amount = $amount;
        $daybookRefRD = CommanTransactionsController::createBranchDayBookReferenceNew($amount, $globaldate);
        $refIdRD = $daybookRefRD;
        $currency_code = 'INR';
        $headPaymentModeRD = 0;
        $payment_type_rd = 'CR';
        $type_id = $investmentId;
        $entry_date = date("Y-m-d", strtotime(convertDate($globaldate)));
        $entry_time = date("H:i:s", strtotime(convertDate($globaldate)));
        $created_by = 2;
        $created_by_id = Auth::user()->id;
        $created_at = date("Y-m-d H:i:s", strtotime(convertDate($globaldate)));
        $updated_at = date("Y-m-d H:i:s", strtotime(convertDate($globaldate)));
        $planDetail = getPlanDetail($planId);
        $type = 4;
        $sub_type = 42;
        $planCode = $planDetail->plan_code;
        ;
        if ($planCode == 703) {
            $head1Invest = 1;
            $head2Invest = 8;
            $head3Invest = 20;
            $head4Invest = 56;
            $head5Invest = NULL;
        }
        $v_no = NULL;
        $v_date = NULL;
        $ssb_account_id_from = NULL;
        $cheque_type = NULL;
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
        if ($payment_mode == 1) {  // cheque moade 
            $headPaymentModeRD = 1;
            $chequeDetail = \App\Models\ReceivedCheque::where('id', $cheque_id)->first();
            $cheque_type = 1;
            $cheque_no = $chequeDetail->cheque_no;
            $cheque_date = $cheque_date;
            $cheque_bank_from = $chequeDetail->bank_name;
            $cheque_bank_ac_from = $chequeDetail->cheque_account_no;
            $cheque_bank_ifsc_from = NULL;
            $cheque_bank_branch_from = $chequeDetail->branch_name;
            $cheque_bank_to = $chequeDetail->deposit_bank_id;
            $cheque_bank_ac_to = $chequeDetail->deposit_account_id;
            $getBankHead = \App\Models\SamraddhBank::where('id', $cheque_bank_to)->first();
            $head11 = 2;
            $head21 = 10;
            $head31 = 27;
            $head41 = $getBankHead->account_head_id;
            $head51 = NULL;
            $rdDesDR = 'Bank A/c Dr ' . $amount . '/-';
            $rdDesCR = 'To ' . $planDetail->name . '(' . $investmentAccountNoRd . ')  A/c Cr ' . $amount . '/-';
            $rdDes = 'Amount received for SSB A/C Deposit (' . $investmentAccountNoRd . ') through cheque(' . $cheque_no . ')';
            $rdDesMem = 'SSB A/C Deposit (' . $investmentAccountNoRd . ') through cheque(' . $cheque_no . ')';
            //bank head entry
            $allHeadTranRDcheque = CommanTransactionsController::createAllHeadTransaction($refIdRD, $branch_id, $bank_id = NULL, $bank_ac_id = NULL, $head41, $type, $sub_type, $type_id, $createDayBook, $associate_id, $memberId, $branch_id_to = NULL, $branch_id_from = NULL, $amount, $amount, $amount, $rdDes, 'DR', $headPaymentModeRD, $currency_code, $amount_to_id = NULL, $amount_to_name = NULL, $amount_from_id = NULL, $amount_from_name = NULL, $jv_unique_id = NULL, $v_no, $v_date, $ssb_account_id_from, $ssb_account_id_to = NULL, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $cheque_type, $cheque_id, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_from_id = NULL, $cheque_bank_ac_from_id = NULL, $cheque_bank_to, $cheque_bank_ac_to, getSamraddhBank($cheque_bank_to)->bank_name, $cheque_bank_to_branch = NULL, getSamraddhBankAccountId($cheque_bank_ac_to)->account_no, getSamraddhBankAccountId($cheque_bank_ac_to)->ifsc_code, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_from_id = NULL, $transction_bank_from_ac_id = NULL, $transction_bank_to, $transction_bank_ac_to, $transction_bank_to_name = NULL, $transction_bank_to_ac_no = NULL, $transction_bank_to_branch = NULL, $transction_bank_to_ifsc = NULL, $transction_date, $created_by, $created_by_id);
            /*$allTranRDcheque=CommanTransactionsController::createAllTransactionNew($refIdRD,$branch_id,$bank_id=NULL,$bank_ac_id=NULL,$head11,$head21,$head31,$head41,$head51,$type,$sub_type,$type_id,$associate_id,$memberId,$branch_id_to=NULL,$branch_id_from=NULL,$opening_balance=NULL,$amount,$closing_balance=NULL,$rdDes,'CR',$headPaymentModeRD,$currency_code,$amount_to_id=NULL,$amount_to_name=NULL,$amount_from_id=NULL,$amount_from_name=NULL,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at,$updated_at,$createDayBook);*/
            //bank entry
            $bankCheque = CommanTransactionsController::samraddhBankDaybookNew($refIdRD, $cheque_bank_to, $cheque_bank_ac_to, $type, $sub_type, $type_id, $createDayBook, $associate_id, $memberId, $branch_id, $opening_balance = NULL, $amount, $closing_balance = NULL, $rdDes, $rdDesDR, $rdDesCR, 'CR', $headPaymentModeRD, $currency_code, $amount_to_id = NULL, $amount_to_name = NULL, $amount_from_id = NULL, $amount_from_name = NULL, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_bank_to_name = NULL, $transction_bank_to_ac_no = NULL, $transction_bank_to_branch = NULL, $transction_bank_to_ifsc = NULL, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL);
            //bank balence
            $bankClosing = CommanTransactionsController::checkCreateBankClosing($cheque_bank_to, $cheque_bank_ac_to, $created_at, $amount, 0);
        } elseif ($payment_mode == 2) {  //online transaction
            $cheque_type = NULL;
            $headPaymentModeRD = 2;
            $transction_no = $online_transction_no;
            $transction_bank_from = NULL;
            $transction_bank_ac_from = NULL;
            $transction_bank_ifsc_from = NULL;
            $transction_bank_branch_from = NULL;
            $transction_bank_to = $online_deposit_bank_id;
            $transction_bank_ac_to = $online_deposit_bank_ac_id;
            $transction_date = $online_transction_date;
            $getBHead = \App\Models\SamraddhBank::where('id', $transction_bank_to)->first();
            $head111 = 2;
            $head211 = 10;
            $head311 = 27;
            $head411 = $getBHead->account_head_id;
            $head511 = NULL;
            $rdDesDR = 'Bank A/c Dr ' . $amount . '/-';
            $rdDesCR = 'To ' . $planDetail->name . '(' . $investmentAccountNoRd . ')  A/c Cr ' . $amount . '/-';
            $rdDes = 'Amount received for SSB A/C Deposit (' . $investmentAccountNoRd . ') through online transaction(' . $transction_no . ')';
            $rdDesMem = 'SSB A/C Deposit (' . $investmentAccountNoRd . ') through online transaction(' . $transction_no . ')';
            //bank head entry
            $allTranRDonline = CommanTransactionsController::createAllHeadTransaction($refIdRD, $branch_id, $bank_id = NULL, $bank_ac_id = NULL, $head411, $type, $sub_type, $type_id, $createDayBook, $associate_id, $memberId, $branch_id_to = NULL, $branch_id_from = NULL, $amount, $amount, $amount, $rdDes, 'DR', $headPaymentModeRD, $currency_code, $amount_to_id = NULL, $amount_to_name = NULL, $amount_from_id = NULL, $amount_from_name = NULL, $jv_unique_id = NULL, $v_no, $v_date, $ssb_account_id_from, $ssb_account_id_to = NULL, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $cheque_type = NULL, $cheque_id = NULL, $cheque_no = NULL, $cheque_date = NULL, $cheque_bank_from = NULL, $cheque_bank_ac_from = NULL, $cheque_bank_ifsc_from = NULL, $cheque_bank_branch_from = NULL, $cheque_bank_from_id = NULL, $cheque_bank_ac_from_id = NULL, $cheque_bank_to = NULL, $cheque_bank_ac_to = NULL, $cheque_bank_to_name = NULL, $cheque_bank_to_branch = NULL, $cheque_bank_to_ac_no = NULL, $cheque_bank_to_ifsc = NULL, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_from_id = NULL, $transction_bank_from_ac_id = NULL, $transction_bank_to, $transction_bank_ac_to, getSamraddhBank($online_deposit_bank_id)->bank_name, getSamraddhBankAccountId($online_deposit_bank_ac_id)->account_no, $transction_bank_to_branch = NULL, getSamraddhBankAccountId($online_deposit_bank_ac_id)->ifsc_code, $transction_date, $created_by, $created_by_id);
            /*$allTranRDonline=CommanTransactionsController::createAllTransactionNew($refIdRD,$branch_id,$bank_id=NULL,$bank_ac_id=NULL,$head111,$head211,$head311,$head411,$head511,$type,$sub_type,$type_id,$associate_id,$memberId,$branch_id_to=NULL,$branch_id_from=NULL,$opening_balance=NULL,$amount,$closing_balance=NULL,$rdDes,'CR',$headPaymentModeRD,$currency_code,$amount_to_id=NULL,$amount_to_name=NULL,$amount_from_id=NULL,$amount_from_name=NULL,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at,$updated_at,$createDayBook);*/
            //bank entry
            $bankonline = CommanTransactionsController::samraddhBankDaybookNew($refIdRD, $transction_bank_to, $transction_bank_ac_to, $type, $sub_type, $type_id, $createDayBook, $associate_id, $memberId, $branch_id, $opening_balance = NULL, $amount, $closing_balance = NULL, $rdDes, $rdDesDR, $rdDesCR, 'CR', $headPaymentModeRD, $currency_code, $amount_to_id = NULL, $amount_to_name = NULL, $amount_from_id = NULL, $amount_from_name = NULL, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, getSamraddhBank($online_deposit_bank_id)->bank_name, getSamraddhBankAccountId($online_deposit_bank_ac_id)->account_no, $transction_bank_to_branch = NULL, getSamraddhBankAccountId($online_deposit_bank_ac_id)->ifsc_code, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL);
            //bank balence
            $bankClosing = CommanTransactionsController::checkCreateBankClosing($transction_bank_to, $transction_bank_ac_to, $created_at, $amount, 0);
        } elseif ($payment_mode == 3) { // ssb
            $headPaymentModeRD = 3;
            $v_no = mt_rand(0, 999999999999999);
            $v_date = $entry_date;
            $ssb_account_id_from = $ssbId;
            $SSBDescTran = 'Amount transfer to ' . $planDetail->name . '(' . $investmentAccountNoRd . ') ';
            $head1rdSSB = 1;
            $head2rdSSB = 8;
            $head3rdSSB = 20;
            $head4rdSSB = 56;
            $head5rdSSB = NULL;
            $ssbDetals = SavingAccount::where('id', $ssb_account_id_from)->first();
            $rdDesDR = $planDetail->name . '(' . $investmentAccountNoRd . ') A/c Dr ' . $amount . '/-';
            $rdDesCR = 'To SSB(' . $ssbDetals->account_no . ') A/c Cr ' . $amount . '/-';
            $rdDes = 'Amount received for SSB A/C Deposit (' . $investmentAccountNoRd . ') through SSB(' . $ssbDetals->account_no . ')';
            $rdDesMem = 'SSB A/C Deposit (' . $investmentAccountNoRd . ') online through SSB(' . $ssbDetals->account_no . ')';
            // ssb  head entry -
            $allTranRDSSB = CommanTransactionsController::createAllHeadTransaction($refIdRD, $branch_id, $bank_id = NULL, $bank_ac_id = NULL, $head4rdSSB, $type, $sub_type, $type_id, $createDayBook, $associate_id, $memberId, $branch_id_to = NULL, $branch_id_from = NULL, $amount, $amount, $amount, $rdDes, 'DR', $headPaymentModeRD, $currency_code, $amount_to_id = NULL, $amount_to_name = NULL, $amount_from_id = NULL, $amount_from_name = NULL, $jv_unique_id = NULL, $v_no, $v_date, $ssb_account_id_from, $ssb_account_id_to = NULL, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $cheque_type = NULL, $cheque_id = NULL, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_from_id = NULL, $cheque_bank_ac_from_id = NULL, $cheque_bank_to, $cheque_bank_ac_to, $cheque_bank_to_name = NULL, $cheque_bank_to_branch = NULL, $cheque_bank_to_ac_no = NULL, $cheque_bank_to_ifsc = NULL, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_from_id = NULL, $transction_bank_from_ac_id = NULL, $transction_bank_to, $transction_bank_ac_to, $transction_bank_to_name = NULL, $transction_bank_to_ac_no = NULL, $transction_bank_to_branch = NULL, $transction_bank_to_ifsc = NULL, $transction_date, $created_by, $created_by_id);
            /*$allTranRDSSB=CommanTransactionsController::createAllTransactionNew($refIdRD,$branch_id,$bank_id=NULL,$bank_ac_id=NULL,$head1rdSSB,$head2rdSSB,$head3rdSSB,$head4rdSSB,$head5rdSSB,$type,$sub_type,$type_id,$associate_id,$memberId,$branch_id_to=NULL,$branch_id_from=NULL,$opening_balance=NULL,$amount,$closing_balance=NULL,$rdDes,'DR',$headPaymentModeRD,$currency_code,$amount_to_id=NULL,$amount_to_name=NULL,$amount_from_id=NULL,$amount_from_name=NULL,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at,$updated_at,$createDayBook);*/
            $branchClosingSSB = CommanTransactionsController::checkCreateBranchClosingDr($branch_id, $created_at, $amount, 0);
            $memberTranInvest77 = CommanTransactionsController::memberTransactionNew($refIdRD, '4', '47', $ssb_account_id_from, $createDayBook, $associate_id, $ssbDetals->member_id, $branch_id, $bank_id = NULL, $account_id = NULL, $amount, $SSBDescTran, 'DR', $headPaymentModeRD, $currency_code, $amount_to_id = $type_id, $amount_to_name = $planDetail->name, $amount_from_id = NULL, $amount_from_name = NULL, $v_no, $v_date, $ssb_account_id_from = NULL, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL);
        } else {
            $headPaymentModeRD = 0;
            $head1rdC = 2;
            $head2rdC = 10;
            $head3rdC = 28;
            $head4rdC = 71;
            $head5rdC = NULL;
            $rdDesDR = 'Cash A/c Dr ' . $amount . '/-';
            $rdDesCR = 'To ' . $planDetail->name . '(' . $investmentAccountNoRd . ')  A/c Cr ' . $amount . '/-';
            $rdDes = 'Amount received for SSB A/C Deposit (' . $investmentAccountNoRd . ') through cash(' . getBranchCode($branch_id)->branch_code . ')';
            $rdDesMem = 'SSB A/C Deposit (' . $investmentAccountNoRd . ') through cash(' . getBranchCode($branch_id)->branch_code . ')';
            // branch cash  head entry +
            $allTranRDcash = CommanTransactionsController::createAllHeadTransaction($refIdRD, $branch_id, $bank_id = NULL, $bank_ac_id = NULL, $head3rdC, $type, $sub_type, $type_id, $createDayBook, $associate_id, $memberId, $branch_id_to = NULL, $branch_id_from = NULL, $amount, $amount, $amount, $rdDes, 'DR', $headPaymentModeRD, $currency_code, $amount_to_id = NULL, $amount_to_name = NULL, $amount_from_id = NULL, $amount_from_name = NULL, $jv_unique_id = NULL, $v_no, $v_date, $ssb_account_id_from, $ssb_account_id_to = NULL, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $cheque_type = NULL, $cheque_id = NULL, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_from_id = NULL, $cheque_bank_ac_from_id = NULL, $cheque_bank_to, $cheque_bank_ac_to, $cheque_bank_to_name = NULL, $cheque_bank_to_branch = NULL, $cheque_bank_to_ac_no = NULL, $cheque_bank_to_ifsc = NULL, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_from_id = NULL, $transction_bank_from_ac_id = NULL, $transction_bank_to, $transction_bank_ac_to, $transction_bank_to_name = NULL, $transction_bank_to_ac_no = NULL, $transction_bank_to_branch = NULL, $transction_bank_to_ifsc = NULL, $transction_date, $created_by, $created_by_id);
            /*$allTranRDcash=CommanTransactionsController::createAllTransactionNew($refIdRD,$branch_id,$bank_id=NULL,$bank_ac_id=NULL,$head1rdC,$head2rdC,$head3rdC,$head4rdC,$head5rdC,$type,$sub_type,$type_id,$associate_id,$memberId,$branch_id_to=NULL,$branch_id_from=NULL,$opening_balance=NULL,$amount,$closing_balance=NULL,$rdDes,$payment_type_rd,$headPaymentModeRD,$currency_code,$amount_to_id=NULL,$amount_to_name=NULL,$amount_from_id=NULL,$amount_from_name=NULL,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at,$updated_at,$createDayBook);*/
            //Balance   entry +
            $branchCash = CommanTransactionsController::checkCreateBranchCash($branch_id, $created_at, $amount, 0);
        }
        //branch day book entry +
        $daybookInvest = CommanTransactionsController::branchDayBookNew($refIdRD, $branch_id, $type, $sub_type, $type_id, $createDayBook, $associate_id, $memberId, $branch_id_to = NULL, $branch_id_from = NULL, $opening_balance = NULL, $amount, $closing_balance = NULL, $rdDes, $rdDesDR, $rdDesCR, $payment_type_rd, $headPaymentModeRD, $currency_code, $amount_to_id = NULL, $amount_to_name = NULL, $amount_from_id = NULL, $amount_from_name = NULL, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $is_contra = NULL, $contra_id = NULL, $created_at, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL);
        // Investment head entry +
        $allTranRDcash = CommanTransactionsController::createAllHeadTransaction($refIdRD, $branch_id, $bank_id = NULL, $bank_ac_id = NULL, $head4Invest, $type, $sub_type, $type_id, $createDayBook, $associate_id, $memberId, $branch_id_to = NULL, $branch_id_from = NULL, $amount, $amount, $amount, $rdDes, $payment_type_rd, $headPaymentModeRD, $currency_code, $amount_to_id = NULL, $amount_to_name = NULL, $amount_from_id = NULL, $amount_from_name = NULL, $jv_unique_id = NULL, $v_no, $v_date, $ssb_account_id_from, $ssb_account_id_to = NULL, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $cheque_type = NULL, $cheque_id = NULL, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_from_id = NULL, $cheque_bank_ac_from_id = NULL, $cheque_bank_to, $cheque_bank_ac_to, $cheque_bank_to_name = NULL, $cheque_bank_to_branch = NULL, $cheque_bank_to_ac_no = NULL, $cheque_bank_to_ifsc = NULL, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_from_id = NULL, $transction_bank_from_ac_id = NULL, $transction_bank_to, $transction_bank_ac_to, $transction_bank_to_name = NULL, $transction_bank_to_ac_no = NULL, $transction_bank_to_branch = NULL, $transction_bank_to_ifsc = NULL, $transction_date, $created_by, $created_by_id);
        /*$allTranInvest = CommanTransactionsController::createAllTransactionNew($refIdRD,$branch_id,$bank_id=NULL,$bank_ac_id=NULL,$head1Invest,$head2Invest,$head3Invest,$head4Invest,$head5Invest,$type,$sub_type,$type_id,$associate_id,$memberId,$branch_id_to=NULL,$branch_id_from=NULL,$opening_balance=NULL,$amount,$closing_balance=NULL,$rdDes,$payment_type_rd,$headPaymentModeRD,$currency_code,$amount_to_id=NULL,$amount_to_name=NULL,$amount_from_id=NULL,$amount_from_name=NULL,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at,$updated_at,$createDayBook);*/
        // Member transaction  +
        $memberTranInvest = CommanTransactionsController::memberTransactionNew($refIdRD, $type, $sub_type, $type_id, $createDayBook, $associate_id, $memberId, $branch_id, $bank_id = NULL, $account_id = NULL, $amount, $rdDesMem, $payment_type_rd, $headPaymentModeRD, $currency_code, $amount_to_id = NULL, $amount_to_name = NULL, $amount_from_id = NULL, $amount_from_name = NULL, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL);
        /******** Balance   entry ***************/
        $branchClosing = CommanTransactionsController::checkCreateBranchClosing($branch_id, $created_at, $amount, 0);
    }
    public static function createAllTransactionNew($daybook_ref_id, $branch_id, $bank_id, $bank_ac_id, $head1, $head2, $head3, $head4, $head5, $type, $sub_type, $type_id, $associate_id, $member_id, $branch_id_to, $branch_id_from, $opening_balance, $amount, $closing_balance, $description, $payment_type, $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $type_transaction_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from)
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
        $data['ssb_account_id_to'] = $ssb_account_id_to;
        $data['ssb_account_tran_id_to'] = $ssb_account_tran_id_to;
        $data['ssb_account_tran_id_from'] = $ssb_account_tran_id_from;
        $data['created_at'] = $created_at;
        $data['updated_at'] = $updated_at;
        $transcation = \App\Models\AllTransaction::create($data);
        return $transcation->id;
    }
    public static function createBranchDayBookNew($daybook_ref_id, $branch_id, $type, $sub_type, $type_id, $associate_id, $member_id, $branch_id_to, $branch_id_from, $opening_balance, $amount, $closing_balance, $description, $description_dr, $description_cr, $payment_type, $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $is_contra, $contra_id, $created_at, $updated_at, $type_transaction_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from)
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
        $data['ssb_account_id_to'] = $ssb_account_id_to;
        $data['ssb_account_tran_id_to'] = $ssb_account_tran_id_to;
        $data['ssb_account_tran_id_from'] = $ssb_account_tran_id_from;
        $data['updated_at'] = $updated_at;
        $transcation = \App\Models\BranchDaybook::create($data);
        return $transcation->id;
    }
    public static function createMemberTransactionNew($daybook_ref_id, $type, $sub_type, $type_id, $associate_id, $member_id, $branch_id, $bank_id, $account_id, $amount, $description, $payment_type, $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $type_transaction_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from)
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
        $data['ssb_account_id_to'] = $ssb_account_id_to;
        $data['ssb_account_tran_id_to'] = $ssb_account_tran_id_to;
        $data['ssb_account_tran_id_from'] = $ssb_account_tran_id_from;
        $data['created_at'] = $created_at;
        $data['updated_at'] = $updated_at;
        $transcation = \App\Models\MemberTransaction::create($data);
        return $transcation->id;
    }
    public function updateRenewal()
    {
        $data['title'] = "Update Renewal Transcation";
        return view('templates.branch.investment_management.renewal.updaterenewal', $data);
    }
    public function updateRenewalTransaction(Request $request)
    {
        $accountNumbers = $request['accountnumbers'][0];
        $explodeArray = explode(',', $accountNumbers);
        $investmentArray = array();
        $entryTime = date("H:i:s");
        foreach ($explodeArray as $value) {
            $dayBookRecords = Daybook::select('id', 'opening_balance', 'deposit', 'withdrawal', 'created_at')->where('account_no', $value)->whereIN('transaction_type', [2, 4, 16, 17, 18])->orderBy('created_at', 'asc')->get();
            $arraydayBookRecords = $dayBookRecords->toArray();
            foreach ($arraydayBookRecords as $key => $value1) {
                $addmiute = $key + 1;
                $lastRecord = Daybook::select('id', 'opening_balance', 'deposit')->where('account_no', $value)->whereIN('transaction_type', [2, 4, 16, 17, 18])->where('created_at', '<', $value1['created_at'])->orderBy('created_at', 'desc')->first();
                $endTime = strtotime("+" . $addmiute . " minutes", strtotime($entryTime));
                $newTime = date('H:i:s', $endTime);
                if ($lastRecord) {
                    $updateAssociateAmount = Daybook::where('id', $value1['id'])->update(array('opening_balance' => ($value1['deposit'] + $lastRecord->opening_balance), 'created_at' => date("Y-m-d " . $newTime . "", strtotime(convertDate($value1['created_at'])))));
                } else {
                    $updateAssociateAmount = Daybook::where('id', $value1['id'])->update(array('opening_balance' => $value1['deposit'], 'created_at' => date("Y-m-d " . $newTime . "", strtotime(convertDate($value1['created_at'])))));
                }
            }
        }
        foreach ($explodeArray as $value) {
            array_push($investmentArray, $value);
            $dayBookRecords = Daybook::select('id', 'opening_balance', 'deposit', 'withdrawal', 'created_at')->where('account_no', $value)->whereIN('transaction_type', [2, 4, 16, 17, 18])->orderBy('created_at', 'asc')->get();
            $arraydayBookRecords = $dayBookRecords->toArray();
            foreach ($arraydayBookRecords as $key => $value1) {
                $addmiute = $key + 1;
                $lastRecord = Daybook::select('id', 'opening_balance', 'deposit', 'withdrawal')->where('account_no', $value)->whereIN('transaction_type', [2, 4, 16, 17, 18])->where('created_at', '<', $value1['created_at'])->orderBy('created_at', 'desc')->first();
                $endTime = strtotime("+" . $addmiute . " minutes", strtotime($entryTime));
                $newTime = date('H:i:s', $endTime);
                if ($lastRecord) {
                    if ($value1['deposit'] > 0) {
                        $updateAssociateAmount = Daybook::where('id', $value1['id'])->update(array('opening_balance' => ($value1['deposit'] + $lastRecord->opening_balance)));
                    } elseif ($value1['withdrawal'] > 0) {
                        $updateAssociateAmount = Daybook::where('id', $value1['id'])->update(array('opening_balance' => ($lastRecord->opening_balance - $value1['withdrawal'])));
                    }
                } else {
                    $updateAssociateAmount = Daybook::where('id', $value1['id'])->update(array('opening_balance' => $value1['deposit']));
                }
            }
        }
        $investmentString = implode(",", $investmentArray);
        return back()->with('success', 'These Investment Account Number ' . $investmentString . ' renewal updated');
        //return view('templates.admin.investment_management.renewal.updaterenewal', $data);
    }
}