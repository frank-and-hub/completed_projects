<?php

namespace App\Http\Controllers\Branch;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use App\Http\Controllers\Admin\InvestmentReportController;
use DB;
use Validator;
use Session;
use Redirect;
use App\Interfaces\RepositoryInterface;
use URL;
use App\Services\Sms;
use App\Models\User;
use App\Models\Member;
use App\Models\TranscationLog;
use App\Models\Transcation;
use App\Models\Daybook;
use App\Models\SavingAccount;
use App\Models\SavingAccountTranscation;
use App\Models\TransactionReferences;
use App\Models\Investmentplantransactions;
use App\Models\SavingAccountBalannce;
use App\Models\CorrectionRequests;
use App\Models\Memberinvestments;
use App\Http\Controllers\Branch\CommanTransactionsController;
use Illuminate\Support\Facades\Schema;
use App\Http\Controllers\Branch\CommanController;
use App\Http\Controllers\Admin\CommanController as ACommanController;
use App\Models\Branch;
use App\Http\Traits\BranchPermissionRoleWiseTrait;
use Carbon\Carbon;
use App\Jobs\InsertTransactions;
use App\Scopes\ActiveScope;
use App\Http\Requests\RenewalRequest;
use App\Models\InvestmentBalance;

class NewRenewalController extends Controller
{
    /**
     * Instantiate a new controller instance.
     *
     * @return void
     */
    use BranchPermissionRoleWiseTrait;
    private $repository;
    public function __construct(RepositoryInterface $repository)
    {
        $this->middleware('auth');
        $this->repository = $repository;
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
        return view('templates.branch.investment_management.renewalNew.renew', $data);
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
        $accountNumber = $request->account_number;
        $renewalDate = date('Y-m-d', strtotime(convertDate($request->renewalDate)));
        $planId = $request->renewPlanId;
        if ($planId == 7 || $planId == 1 || $planId == 12 || $planId == 13) {
            $investment = Memberinvestments::with(['member', 'associateMember', 'ssb', 'demandadvice'=>function($q){
                $q->where('is_deleted', 0);
            }])
                ->where('plan_id', $planId)->where('account_number', $accountNumber)
                ->where('investment_correction_request', 0)->where('renewal_correction_request', 0)->where('is_mature', 1);
        } else {
            $investment = Memberinvestments::with(['member', 'associateMember', 'ssb', 'demandadvice'=>function($q){
                $q->where('is_deleted', 0);
            }])->whereIn('plan_id', [2, 3, 5, 6, 10, 11])->where('account_number', $accountNumber)->where('investment_correction_request', 0)->where('renewal_correction_request', 0)->where('is_mature', 1);
        }
        if (Auth::user()->branch_id > 0) {
            $investment = $investment->where('branch_id', Auth::user()->branch_id);
        }
        $investment = $investment->get();
        // dd($investment);
        $msg = false;
        $type = '';
        if (count($investment) > 0) {
            $aa = InvestmentReportController::getRecords($investment[0], $renewalDate);
            if (isset($aa['pendingEmiAMount'])) {
                $dueAmount = $aa['pendingEmiAMount'];
            } else {
                $dueAmount = 0;
            }
        } else {
            $aa = 0;
            $dueAmount = '';
        }
        if (count($investment) > 0 && !isset($investment[0]['demandadvice'])) {
            $maturityDate = $investment[0]->maturity_date;
            if ($maturityDate < $renewalDate && $maturityDate != NULL && $investment[0]->plan_id != 1) {
                $msg = true;
                $amount = '';
                $minutes = 6;
            } else {
                $msg = false;
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
            $getLastRecord = Daybook::where('investment_id', $investment[0]->id)->where('account_no', $accountNumber)->orderBy('created_at', 'desc')->first();
            if ($getLastRecord) {
                $dateTimeObject1 = date_create('' . $getLastRecord->updated_at . '');
                $dateTimeObject2 = date_create('' . $currentDate . '');
                $difference = date_diff($dateTimeObject1, $dateTimeObject2);
                $minutes = $difference->days * 24 * 60;
                $minutes += $difference->h * 60;
                $minutes += $difference->i;
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
        }
        $resCount = count($investment);
        $return_array = compact('investment', 'resCount', 'amount', 'minutes', 'msg', 'dueAmount', 'type');
        return json_encode($return_array);
    }
    /**
     * Store investment details.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return JSON
     */
    // public function storeAjax(RenewalRequest $request)
    // {

    //     $validatedData = $request->validated();


    //     //$companyId = $request->company_id;
    //     $branchDaybookTransactions = array();
    //     $allHeadTransactions = array();
    //     $memberTransactions = array();
    //     $ssbTransactions = array();
    //     $bankTransactions = array();
    //     $baseurl = URL::to('/');

    //      DB::beginTransaction();
    //      try {
    //         $entryTime = date("H:i:s");
    //         $globaldate = date("Y-m-d", strtotime(convertDate($request['renewal_date'])));
    //         Session::put('created_at', date("Y-m-d", strtotime(convertDate($globaldate))));
    //         $getBranchId = getUserBranchId(Auth::user()->id);

    //         //$branch_id = (Auth::user()->id);

    //         $branch_idm = $getBranchId->id;
    //         $getBranchCode = getBranchDetail($branch_idm);

    //         $branch_id = $getBranchCode->id;
    //         $branchCode = $getBranchCode->branch_code;
    //         $branchName = $getBranchCode->name;
    //         $accountNumbers = $request['account_number'];
    //         $renewPlanId = $request['renewplan_id'];
    //         $sAccount = $this->getSavingAccountDetails($request['member_id']);
    //         $collectionSSBId = '';
    //         $planDetails = \App\Models\Plans::withoutGlobalScope(ActiveScope::class)->get()->keyBy('id');

    //         if ($request['payment_mode'] == 1) {
    //             $getChequeDetail = \App\Models\ReceivedCheque::where('id', $request['cheque_id'])->where('status', 3)->first(['id', 'amount', 'status']);
    //             if (!empty($getChequeDetail)) {
    //                 $response = array(
    //                     'status' => 'alert',
    //                     'msg' => 'Cheque already used select another cheque',
    //                 );
    //                 return Response::json($response);
    //                 // return back()->with('alert', 'Cheque already used select another cheque');
    //             } else {
    //                 $getamount = \App\Models\ReceivedCheque::where('id', $request['cheque_id'])->first(['id', 'amount']);
    //                 if ($getamount->amount != number_format((float) $request['total_amount'], 4, '.', '')) {
    //                     $response = array(
    //                         'status' => 'alert',
    //                         'msg' => 'Renew  amount is not equal to cheque amount',
    //                     );
    //                     return Response::json($response);
    //                     // return back()->with('alert', 'Renew  amount is not equal to cheque amount');
    //                 }
    //             }
    //             $receivedcheque = \App\Models\ReceivedCheque::find($request['cheque_id']);
    //             $receivedcheque->status = 3 ;
    //             $receivedcheque->save();
    //         }

    //         $received_cheque_id = $cheque_id = NULL;
    //         $cheque_deposit_bank_id = NULL;
    //         $cheque_deposit_bank_ac_id = NULL;
    //         $cheque_no = NULL;
    //         $cheque_date = $pdate = NULL;
    //         $online_deposit_bank_id = NULL;
    //         $online_deposit_bank_ac_id = NULL;
    //         $online_transction_no = NULL;
    //         $online_transction_date = NULL;
    //         if ($request['payment_mode'] == 1) {
    //             $pmodeAll = 1;
    //             $chequeDetail = \App\Models\ReceivedCheque::where('id', $request['cheque_id'])->first();
    //             $received_cheque_id = $cheque_id = $request['cheque_id'];
    //             $cheque_deposit_bank_id = $cheque_bank_to = $chequeDetail->deposit_bank_id;
    //             $getBankHead = \App\Models\SamraddhBank::where('id', $cheque_bank_to)->first();
    //             $cheque_deposit_bank_ac_id = $cheque_bank_ac_to = $chequeDetail->deposit_account_id;
    //             $cheque_no = $request['cheque-number'];
    //             $cheque_date = $pdate = date("Y-m-d", strtotime(str_replace('/', '-', $request['cheque-date'])));
    //             $cashHeadId = $getBankHead->account_head_id;
    //             $types = 'Bank';
    //             $subString = 'through Cheque(' . $cheque_no . ')';
    //             $cheque_no = $chequeDetail->cheque_no;
    //             $cheque_date = $cheque_date;
    //             $cheque_bank_from = $chequeDetail->bank_name;
    //             $cheque_bank_ac_from = $chequeDetail->cheque_account_no;
    //             $cheque_bank_ifsc_from = NULL;
    //             $cheque_bank_branch_from = $chequeDetail->branch_name;
    //         }

    //         if ($request['payment_mode'] == 0) { //cash
    //             $pmodeAll = 0;
    //             $cashHeadId = 28;
    //             $types = 'Cash';
    //             $subString = 'through cash(' . getBranchCode($branch_id)->branch_code . ')';
    //         }
    //         if ($request['payment_mode'] == 4) {
    //             $pmodeAll = 3;
    //             $cashHeadId = 56;
    //             $types = 'SSB';
    //             $subString = 'through ssb(' . $sAccount->account_no . ')';
    //         }
    //         if ($request['payment_mode'] == 3) {
    //             $pmodeAll = 2;
    //         }

    //         //Cheque
    //         Session::put('created_at', $globaldate);

    //         $ssbAccountTran = '';
    //         $allContactNumbers = array();
    //         $allAccountNumbers = array();
    //         $amounts = array();
    //         $rAmounts = array();
    //         $ren_dates = array();
    //         foreach ($accountNumbers as $key => $accountNumber) {

    //             if ($accountNumber && $request['amount'][$key]) {
    //                 $amountArraySsb = array('1' => $request['amount'][$key]);
    //                 $save = 0;
    //                 $investmentDetail = $this->getInvestmentPlanDetail($request['investment_id'][$key]);
    //                 $companyId =$investmentDetail->company_id;

    //                 $amount = $request['amount'][$key];


    //                 if ($renewPlanId == 2) {
    //                     $sAccountId = $this->getSavingAccountId($request['investment_id'][$key]);

    //                     if ($sAccountId) {
    //                         $ssb_id = $sAccountId->id;
    //                         $ssbAccountNumber = $sAccountId->account_no;
    //                     } else {
    //                         $ssb_id = NULL;
    //                         $ssbAccountNumber = NULL;
    //                     }
    //                     $savingAccountDetail = $this->getSavingAccountDetails($request['investment_member_id'][$key]);
    //                     if ($savingAccountDetail) {
    //                         $renewSavingOpeningBlanace = $savingAccountDetail->balance;
    //                     } else {
    //                         $renewSavingOpeningBlanace = NULL;
    //                     }
    //                     if ($investmentDetail) {
    //                         $sResult = Memberinvestments::find($request['investment_id'][$key]);
    //                         $totalbalance = $investmentDetail->current_balance + $request['amount'][$key];
    //                         $investData['current_balance'] = $totalbalance;
    //                         $investData['company_id'] = $companyId;
    //                         $sResult->update($investData);
    //                     } else {
    //                         $totalbalance = '';
    //                     }
    //                     if ($request['payment_mode'] == 0) {
    //                         $mtssb = 'Cash deposit';
    //                     } else {
    //                         $mtssb = 'Amount deposit';
    //                     }
    //                     $rno = $request['cheque-number'];
    //                     if ($request['payment_mode'] == 4) {
    //                         /************* UpdateSaving Account ***************/
    //                         $record1 = SavingAccountTranscation::where('account_no', $sAccount->account_no)->where('type','=',1)->where('created_at', '<=', date("Y-m-d", strtotime(convertDate($request['renewal_date']))))->first();
    //                         if (empty($record1)) {
    //                             // $response = array(
    //                             //     'status' => 'alert',
    //                             //     'msg' => 'Renew date should less than created date',
    //                             // );
    //                             // return Response::json($response);
    //                              return back()->with('alert', 'Renew date should less than created date');
    //                         }
    //                         $mtssb = 'Amount deposit by ' . $sAccount->account_no;
    //                         $rno = $sAccount->account_no;
    //                         $ssbAccountAmount = $sAccount->balance - $request['amount'][$key];
    //                         $ssb_id = $collectionSSBId = $sAccount->id;
    //                         $sResult = SavingAccount::find($ssb_id);
    //                         $sData['balance'] = $ssbAccountAmount;
    //                         $sResult->update($sData);
    //                         $ssb['saving_account_id'] = $ssb_id;
    //                         $ssb['account_no'] = $sAccount->account_no;
    //                         if ($record1) {
    //                             $ssb['opening_balance'] = $ssbAccountAmount;
    //                         } else {
    //                             $ssb['opening_balance'] = $request['amount'][$key];
    //                         }
    //                         $ssb['withdrawal'] = $request['amount'][$key];
    //                         if ($branch_id != $investmentDetail->branch_id) {

    //                             $ssb['description'] = 'Fund Trf. To ' . $savingAccountDetail->account_no . '- From ' . $branchName . '';
    //                         } else {
    //                             $ssb['description'] = 'Fund Trf. To ' . $savingAccountDetail->account_no;
    //                         }
    //                         $ssb['associate_id'] = $request['member_id'];
    //                         $ssb['branch_id'] = $branch_id;
    //                         $ssb['type'] = 6;
    //                         $ssb['currency_code'] = 'INR';
    //                         $ssb['payment_type'] = 'DR';
    //                         $ssb['payment_mode'] = $request['payment_mode'];
    //                         $ssb['deposit'] = NULL;
    //                         $ssb['is_renewal'] = 0;
    //                         $ssb['company_id'] = $companyId;
    //                         $ssb['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(str_replace('/', '-', $request['renewal_date'])));
    //                         ;
    //                         $ssbAccountTran = SavingAccountTranscation::create($ssb);
    //                         $ssbFromId = $ssb_id;
    //                         $ssbAccountTranFromId = $ssbAccountTran->id;

    //                         /************* UpdateSaving Account ***************/
    //                         $encodeDate = json_encode($ssb);
    //                     } else {
    //                         $ssbFromId = NULL;
    //                         $ssbAccountTranFromId = NULL;
    //                     }
    //                     $record3 = SavingAccountTranscation::where('account_no', $accountNumber)->where('created_at', '<', date("Y-m-d", strtotime(convertDate($request['renewal_date']))))->first();

    //                     $ssbAccountAmount = ($renewSavingOpeningBlanace + $request['amount'][$key]);
    //                     $ssb_id = $depositSSBId = $savingAccountDetail->id;
    //                     $sResult = SavingAccount::find($ssb_id);
    //                     $sData['balance'] = $ssbAccountAmount;
    //                     $sResult->update($sData);
    //                     $ssb['saving_account_id'] = $ssb_id;
    //                     $ssb['account_no'] = $accountNumber;
    //                     $ssb['opening_balance'] = ($renewSavingOpeningBlanace + $request['amount'][$key]);
    //                     $ssb['withdrawal'] = 0;
    //                     if ($branch_id != $investmentDetail->branch_id) {

    //                         $ssb['description'] = $mtssb . ' - From ' . $branchName . '';
    //                     } else {
    //                         $ssb['description'] = $mtssb;
    //                     }
    //                     $ssb['associate_id'] = $request['member_id'];
    //                     $ssb['branch_id'] = $branch_id;
    //                     $ssb['type'] = 2;
    //                     $ssb['currency_code'] = 'INR';
    //                     $ssb['payment_type'] = 'CR';
    //                     $ssb['payment_mode'] = $request['payment_mode'];
    //                     $ssb['reference_no'] = $rno;
    //                     $ssb['company_id'] = $companyId;
    //                     $ssb['deposit'] = $request['amount'][$key];
    //                     $ssb['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($request['renewal_date'])));
    //                     $ssbAccountTran = SavingAccountTranscation::create($ssb);
    //                     $ssbToId = $savingAccountDetail->id;
    //                     $ssbAccountTranToId = $ssbAccountTran->id;

    //                     $encodeDate = json_encode($ssb);

    //                     // ---------------------------  Day book modify --------------------------

    //                     $lastAmount = Daybook::where('investment_id', $request['investment_id'][$key])->whereIN('transaction_type', [2, 4, 16, 17, 18])->where('account_no', $accountNumber)->whereDate('created_at', '<=', date("Y-m-d", strtotime(convertDate($request['renewal_date']))))->orderBy('id', 'desc')->first();
    //                     if ($lastAmount) {
    //                         $lastBalance = (($lastAmount ? $lastAmount->opening_balance : 0 ) + $request['amount'][$key]);
    //                     } else {
    //                         $lastAmount = Daybook:: /*where('investment_id',$request['investment_id'][$key])->*/where('account_no', $accountNumber)->whereIN('transaction_type', [1])->whereDate('created_at', '<', date("Y-m-d", strtotime(convertDate($request['renewal_date']))))->orderby('id', 'desc')->first();
    //                         if ($lastAmount) {
    //                             $lastBalance = (($lastAmount ? $lastAmount->opening_balance : 0 ) + $request['amount'][$key]);
    //                         } else {
    //                             $lastOpeningAmount = Daybook::where('account_no', $accountNumber)->whereIN('transaction_type', [1])->orderby('id', 'desc')->first();
    //                             //-----------update -----
    //                             if ($renewPlanId == 2) {
    //                                 $lastOpeningAmount = SavingAccountTranscation::where('account_no', $accountNumber)->orderby('id', 'desc')->first();
    //                             }
    //                             $lastBalance = $lastOpeningAmount->opening_balance + $request['amount'][$key];
    //                         }
    //                     }
    //                     if ($lastAmount) {
    //                         $nextRenewal = $lastAmount = Daybook::where('investment_id', $request['investment_id'][$key])->whereIN('transaction_type', [1])->where('account_no', $accountNumber)->whereDate('created_at', '>', date("Y-m-d", strtotime(convertDate($lastAmount->created_at))))->get();
    //                         foreach ($nextRenewal as $key1 => $value) {
    //                             $daybookData['opening_balance'] = $value->opening_balance + $request['amount'][$key];
    //                             $daybookData['company_id'] = $companyId;
    //                             $dayBook = Daybook::find($value->id);
    //                             $dayBook->update($daybookData);
    //                         }
    //                     }
    //                     $refTransactions['amount'] = $request['amount'][$key];
    //                     $refTransactions['entry_date'] = $globaldate;
    //                     $refTransactions['entry_time'] = $entryTime;
    //                     $createTransaction = \App\Models\BranchDaybookReference::insertGetId($refTransactions);

    //                     $createDayBook = CommanTransactionsController::createDayBookNew($createTransaction, $ssbAccountTranToId, 2, $request['investment_id'][$key], $request['member_id'], $request['investment_member_id'][$key], $lastBalance, $request['amount'][$key], $withdrawal = 0, $ssb['description'], $ssbAccountNumber, $branch_id, $branchCode, $amountArraySsb, $request['payment_mode'], $request['deposite_by_name'], $request['member_id'], $accountNumber, $request['cheque-number'], $request['bank-name'], $request['branch-name'], $globaldate, NULL, $online_payment_by = NULL, $collectionSSBId, 'CR', $received_cheque_id, $cheque_deposit_bank_id, $cheque_deposit_bank_ac_id, $online_deposit_bank_id, $online_deposit_bank_ac_id, $companyId);
    //                     // ---------------------------  HEAD IMPLEMENT --------------------------
    //                     $planId = $investmentDetail->plan_id;

    //                     $this->investHeadCreateSSB($request['amount'][$key], $globaldate, $request['investment_id'][$key], $planId, $cheque_no, $cheque_id, $cheque_date, $online_transction_no, $online_transction_date, $online_deposit_bank_id, $online_deposit_bank_ac_id, $branch_id, $request['member_id'], $request['investment_member_id'][$key], $collectionSSBId, $ssbAccountTran->id, $pmodeAll, $ssbAccountNumber, $ssbFromId, $ssbAccountTranFromId, $ssbToId, $ssbAccountTranToId, $companyId);
    //                     //--------------------------------HEAD IMPLEMENT  ------------------------
    //                     $daybookData['is_renewal'] = 0;
    //                     $daybookData['company_id'] = $companyId;
    //                     $dayBook = Daybook::find($createDayBook);
    //                     $dayBook->update($daybookData);
    //                     /*--------------------cheque assign -----------------------*/
    //                     if ($request['payment_mode'] == 1) {
    //                         $receivedPayment['type'] = 3;
    //                         $receivedPayment['branch_id'] = $branch_id;
    //                         $receivedPayment['investment_id'] = $request['investment_id'][$key];
    //                         $receivedPayment['day_book_id'] = $createDayBook;
    //                         $receivedPayment['cheque_id'] = $request['cheque_id'];
    //                         $receivedPayment['created_at'] = $globaldate;
    //                         $receivedCreate = \App\Models\ReceivedChequePayment::create($receivedPayment);
    //                         // $dataRC['101171001058'] = 3;

    //                     }
    //                     /*-----------------------------------------------------------*/
    //                     $transaction = $this->transactionData($request->all(), $request['investment_id'][$key], $request['amount'][$key]);
    //                     $ipTransaction = Investmentplantransactions::create($transaction);
    //                     $ipTransactionData['is_renewal'] = 0;
    //                     $ipTransactionData['created_at'] = date("Y-m-d H:i:s", strtotime(str_replace('/', '-', $request['renewal_date'])));
    //                     $updateipTransaction = Investmentplantransactions::find($ipTransaction->id);
    //                     $updateipTransaction->update($ipTransactionData);
    //                     $investmentCurrentBalance = getSavingCurrentBalance($ssb_id, $accountNumber);
    //                     $save = 1;
    //                     $rAmount = $investmentCurrentBalance;
    //                 } else {

    //                     $cDate = date("Y-m-d " . $entryTime . "", strtotime(convertDate($request['renewal_date'])));
    //                     if ($request->renewplan_id == 3) {
    //                         $data['due_amount'] = 0;
    //                     } else {
    //                         $data['due_amount'] = $request['deo_amount'][$key];
    //                     }
    //                     if ($data['due_amount'] && $data['due_amount'] > 0) {
    //                         $investment = Memberinvestments::find($request['investment_id'][$key]);
    //                         $investment->update($data);
    //                     }
    //                     if ($request['payment_mode'] == 4) {
    //                         $sAccount = $this->getSavingAccountDetails($request['member_id']);

    //                         $ssbAccountAmount = $sAccount->balance - $request['amount'][$key];
    //                         $ssb_id = $collectionSSBId = $sAccount->id;
    //                         $sResult = SavingAccount::find($ssb_id);
    //                         $sData['balance'] = $ssbAccountAmount;
    //                         $sResult->update($sData);
    //                         $ssb['saving_account_id'] = $ssb_id;
    //                         $ssb['account_no'] = $sAccount->account_no;
    //                         $ssb['opening_balance'] = $ssbAccountAmount;
    //                         $ssb['deposit'] = NULL;
    //                         $ssb['withdrawal'] = $request['amount'][$key];
    //                         $ssb['description'] = 'Fund Trf. To ' . $accountNumber . '';
    //                         $ssb['currency_code'] = 'INR';
    //                         $ssb['payment_type'] = 'CR';
    //                         $ssb['payment_mode'] = $request['payment_mode'];
    //                         $ssb['is_renewal'] = 0;
    //                         $ssb['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($request['renewal_date'])));
    //                         $ssbTransactions[] = [
    //                             'saving_account_id' => $ssb_id,
    //                             'account_no' => $sAccount->account_no,
    //                             'opening_balance' => $ssbAccountAmount,
    //                             'deposit' => NULL,
    //                             'withdrawal' => $request['amount'][$key],
    //                             'description' => 'Fund Trf. To ' . $accountNumber . '',
    //                             'currency_code' => 'INR',
    //                             'payment_type' => 'CR',
    //                             'payment_mode' => $request['payment_mode'],
    //                             'is_renewal' => 0,
    //                             'company_id' => $companyId,
    //                             'created_at' => date("Y-m-d " . $entryTime . "", strtotime(convertDate($request['renewal_date']))),
    //                         ];
    //                         $satRefId = NULL;

    //                         // dd($ssb);
    //                         $ssbFromId = $ssb_id;
    //                         $encodeDate = json_encode($ssb);
    //                     } else {
    //                         $satRefId = NULL;
    //                         $ssbFromId = NULL;
    //                         $ssbAccountTranFromId = NULL;
    //                     }




    //                     $ssbToId = NULL;
    //                     $ssbAccountTranToId = NULL;
    //                     $sAccount = $this->getSavingAccountDetails($request['investment_member_id'][$key]);
    //                     if ($sAccount) {
    //                         $ssbAccountId = $sAccount->id;
    //                     } else {
    //                         $ssbAccountId = 0;
    //                     }
    //                     if ($investmentDetail) {
    //                         $totalbalance = $investmentDetail->current_balance + $request['amount'][$key];
    //                         $investData['current_balance'] = $totalbalance;
    //                         $rplanId = $investmentDetail->plan_id;
    //                         if ($request['payment_mode'] == 4) {
    //                             $assAccount = $this->getSavingAccountDetails($request['member_id']);
    //                             $description = 'Fund Rec. From ' . $assAccount->account_no . '';
    //                         } else {


    //                             if ($branch_id != $investmentDetail->branch_id) {

    //                                 $shortanem =  $planDetails[$investmentDetail->plan_id]->short_name;
    //                                 $description =  $shortanem.' Collection - From ' . $branchName . '';

    //                                     // $description = 'SDD Collection - From ' . $branchName . '';

    //                             } else {
    //                                 $shortanem =  $planDetails[$investmentDetail->plan_id]->short_name;
    //                                 $description =  $shortanem.' Collection ';

    //                             }
    //                         }
    //                     } else {
    //                         $totalbalance = '';
    //                         $description = '';
    //                     }

    //                     $currentDAte = date('Y-m-d H:i:s');
    //                     $lastAmount = Daybook::where('investment_id', $request['investment_id'][$key])->whereIN('transaction_type', [2, 4, 16, 17, 18])->where('account_no', $accountNumber)->whereDate('created_at', '<=', date("Y-m-d", strtotime(convertDate($request['renewal_date']))))->orderBy('id', 'desc')->first();

    //                     if ($lastAmount) {
    //                         $lastBalance = (($lastAmount ? $lastAmount->opening_balance : 0 ) + $request['amount'][$key]);
    //                     } else {
    //                         $lastAmount = Daybook::where('investment_id', $request['investment_id'][$key])->whereIN('transaction_type', [2, 4, 16, 17, 18])->where('account_no', $accountNumber)->whereDate('created_at', '<', date("Y-m-d", strtotime(convertDate($request['renewal_date']))))->orderby('id', 'desc')->first();
    //                         $lastBalance = (($lastAmount ? $lastAmount->opening_balance : 0 ) + $request['amount'][$key]);
    //                     }
    //                     $refTransactions['amount'] = $request['amount'][$key];
    //                     $refTransactions['entry_date'] = $globaldate;
    //                     $refTransactions['entry_time'] = $entryTime;


    //                     $transcation = \App\Models\BranchDaybookReference::insertGetId($refTransactions);
    //                     $t = date("H:i:s");
    //                     $type = 3;
    //                     $sub_type = 32;


    //                     $rdDesDR = $types . ' A/c Dr ' . $amount . '/-';
    //                     $rdDesCR = 'To ' . $planDetails[$investmentDetail->plan_id]->name . '(' . $investmentDetail->account_number . ')  A/c Cr ' . $amount . '/-';
    //                     $rdDes = 'Amount received for ' . $planDetails[$investmentDetail->plan_id]->name . ' A/C Renewal (' . $investmentDetail->account_number . ')' . $subString;
    //                     $rdDesMem = $planDetails[$investmentDetail->plan_id]->name . ' A/C Renewal (' . $investmentDetail->account_number . ')' . $subString;

    //                     $created_by = 2;
    //                     $created_by_id = $branch_id;
    //                     $dayBookTransactions['transaction_id'] = $transcation;
    //                     $dayBookTransactions['daybook_ref_id'] = $transcation;

    //                     $dayBookTransactions['transaction_type'] = 4;

    //                     $dayBookTransactions['saving_account_transaction_reference_id'] = $satRefId;
    //                     $dayBookTransactions['investment_id'] = $request['investment_id'][$key];
    //                     $dayBookTransactions['account_no'] = $accountNumber;
    //                     $dayBookTransactions['associate_id'] = $request['member_id'];
    //                     $dayBookTransactions['member_id'] = $request['investment_member_id'][$key];
    //                     $dayBookTransactions['opening_balance'] = $lastBalance;
    //                     $dayBookTransactions['deposit'] = $request['amount'][$key];
    //                     $dayBookTransactions['withdrawal'] = 0;
    //                     $dayBookTransactions['description'] = $description;
    //                     $dayBookTransactions['reference_no'] = NULL;
    //                     $dayBookTransactions['branch_id'] = $branch_id;
    //                     $dayBookTransactions['branch_code'] = $branchCode;
    //                     $dayBookTransactions['amount'] = $amount;
    //                     $dayBookTransactions['currency_code'] = 'INR';
    //                     $dayBookTransactions['payment_mode'] = $request['payment_mode'];
    //                     $dayBookTransactions['payment_type'] = 'CR';
    //                     $dayBookTransactions['amount_deposit_by_name'] = $request['deposite_by_name'];
    //                     $dayBookTransactions['amount_deposit_by_id'] = $request['member_id'];
    //                     $dayBookTransactions['created_by_id'] = $branch_id;
    //                     $dayBookTransactions['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($globaldate)));
    //                     $dayBookTransactions['created_at_default'] = Carbon::now();
    //                     $dayBookTransactions['created_by'] = 2;
    //                     $dayBookTransactions['company_id'] = $companyId;

    //                     if ($request['payment_mode'] == 1) {
    //                         $dayBookTransactions['cheque_dd_no'] = $cheque_no;
    //                         $dayBookTransactions['bank_name'] = getSamraddhBank($cheque_deposit_bank_ac_id)->bank_name;
    //                         $dayBookTransactions['branch_name'] = $branchName;
    //                         $dayBookTransactions['received_cheque_id'] = $received_cheque_id;
    //                         $dayBookTransactions['cheque_deposit_bank_id'] = $cheque_deposit_bank_id;
    //                         $dayBookTransactions['cheque_deposit_bank_ac_id'] = $cheque_deposit_bank_ac_id;

    //                     }

    //                     $transactionId = \App\Models\Daybook::insertGetId($dayBookTransactions);
    //                     $branchDaybookTransactions[] = [
    //                         'daybook_ref_id' => $transcation,
    //                         'branch_id' => $branch_id,
    //                         'type' => $type,
    //                         'sub_type' => $sub_type,
    //                         'type_id' => $request['investment_id'][$key],
    //                         'type_transaction_id' => $transactionId,
    //                         'associate_id' => $request['member_id'],
    //                         'member_id' => $request['investment_member_id'][$key],
    //                         'amount' => $amount,
    //                         'description' => $rdDes,
    //                         'description_dr' => $rdDesDR,
    //                         'description_cr' => $rdDesCR,
    //                         'payment_type' => 'CR',
    //                         'payment_mode' => $pmodeAll,
    //                         'currency_code' => 'INR',
    //                         'entry_date' => date("Y-m-d", strtotime(convertDate($globaldate))),
    //                         'entry_time' => date("H:i:s"),
    //                         'created_by' => $created_by,
    //                         'created_by_id' => $created_by_id,
    //                         'created_at' => date("Y-m-d " . $t . "", strtotime(convertDate($globaldate))),
    //                         'cheque_no' => $cheque_no ?? NULL,
    //                         'company_id' => $companyId ?? NULL,
    //                     ];

    //                     if ($request['payment_mode'] == 1) {
    //                         $bankTransactions[] = [
    //                             'daybook_ref_id' => $transcation,
    //                             'bank_id' => $chequeDetail->deposit_bank_id,
    //                             'account_id' => $chequeDetail->deposit_account_id,
    //                             'type' => $type,
    //                             'sub_type' => $sub_type,
    //                             'type_id' => $request['investment_id'][$key],
    //                             'type_transaction_id' => $transactionId,
    //                             'associate_id' => $request['member_id'],
    //                             'member_id' => $request['investment_member_id'][$key],
    //                             'branch_id' => $branch_id,
    //                             'opening_balance' => $amount,
    //                             'amount' => $amount,
    //                             'closing_balance' => $amount,
    //                             'description' => $rdDes,
    //                             'description_dr' => $rdDesDR,
    //                             'description_cr' => $rdDesCR,
    //                             'payment_type' => 'CR',
    //                             'payment_mode' => $pmodeAll,
    //                             'currency_code' => 'INR',
    //                             'transction_date' => date("Y-m-d", strtotime(convertDate($globaldate))),
    //                             'entry_date' => date("Y-m-d", strtotime(convertDate($globaldate))),
    //                             'entry_time' => date("H:i:s"),
    //                             'created_by' => $created_by,
    //                             'created_by_id' => $created_by_id,
    //                             'created_at' => date("Y-m-d " . $t . "", strtotime(convertDate($globaldate))),
    //                             'cheque_no' => $cheque_no ?? NULL,
    //                             'cheque_date' => $cheque_date ?? NULL,
    //                             'cheque_bank_from' => $cheque_bank_from ?? NULL,
    //                             'cheque_bank_ac_from' => $cheque_bank_ac_from ?? NULL,
    //                             'cheque_bank_branch_from' => $cheque_bank_branch_from ?? NULL,
    //                             'cheque_bank_to' => $cheque_bank_to ?? NULL,
    //                             'cheque_bank_ac_to' => $cheque_bank_ac_to ?? NULL,
    //                             'company_id' => $companyId ?? NULL,

    //                         ];
    //                     }

    //                     $cashAllHeadTransactions[] = [
    //                         'daybook_ref_id' => $transcation,
    //                         'branch_id' => $branch_id,
    //                         'type' => $type,
    //                         'head_id' => $cashHeadId,
    //                         'sub_type' => $sub_type,
    //                         'type_id' => $request['investment_id'][$key],
    //                         'type_transaction_id' => $transactionId,
    //                         'associate_id' => $request['member_id'],
    //                         'member_id' => $request['investment_member_id'][$key],
    //                         'amount' => $amount,
    //                         'description' => $rdDes,
    //                         'payment_type' => 'DR',
    //                         'payment_mode' => $pmodeAll,
    //                         'currency_code' => 'INR',
    //                         'entry_date' => date("Y-m-d", strtotime(convertDate($globaldate))),
    //                         'entry_time' => date("H:i:s"),
    //                         'created_by' => $created_by,
    //                         'created_by_id' => $created_by_id,
    //                         'created_at' => date("Y-m-d " . $t . "", strtotime(convertDate($globaldate))),
    //                         'cheque_no' => $cheque_no ?? NULL,
    //                         'company_id' => $companyId ?? NULL,
    //                         'bank_id' => $chequeDetail->deposit_bank_id ?? NULL,
    //                         'bank_ac_id' => $chequeDetail->deposit_account_id ?? NULL,
    //                         'cheque_id' => $chequeDetail->id ?? NULL,
    //                         'company_id' => $companyId ?? NULL,


    //                     ];

    //                     $allHeadTransactions[] = [
    //                         'daybook_ref_id' => $transcation,
    //                         'branch_id' => $branch_id,
    //                         'type' => $type,
    //                         'head_id' => $planDetails[$investmentDetail->plan_id]->deposit_head_id,
    //                         'sub_type' => $sub_type,
    //                         'type_id' => $request['investment_id'][$key],
    //                         'type_transaction_id' => $transactionId,
    //                         'associate_id' => $request['member_id'],
    //                         'member_id' => $request['investment_member_id'][$key],
    //                         'amount' => $amount,
    //                         'description' => $rdDes,
    //                         'payment_type' => 'CR',
    //                         'payment_mode' => $request['payment_mode'],
    //                         'currency_code' => 'INR',
    //                         'entry_date' => date("Y-m-d", strtotime(convertDate($globaldate))),
    //                         'entry_time' => date("H:i:s"),
    //                         'created_by' => $created_by,
    //                         'created_by_id' => $created_by_id,
    //                         'created_at' => date("Y-m-d " . $t . "", strtotime(convertDate($globaldate))),
    //                         'cheque_no' => $cheque_no ?? NULL,
    //                         'company_id' => $companyId ?? NULL,
    //                         'bank_id' => $chequeDetail->deposit_bank_id ?? NULL,
    //                         'bank_ac_id' => $chequeDetail->deposit_account_id ?? NULL,
    //                         'cheque_id' => $chequeDetail->id ?? NULL,
    //                         'company_id' => $companyId ?? NULL,
    //                     ];

    //                     $investmentCurrentBalance = getInvestmentCurrentBalance($investmentDetail->id, $investmentDetail->account_number);
    //                     $rAmount = $investmentCurrentBalance;
    //                     Memberinvestments::where('id', $investmentDetail->id)->update(['current_balance' => $rAmount]);
    //                 }
    //             }
    //             $contactNumber = array();


    //             if ($key != 0 && $request['investment_member_phone_no'][$key]) {
    //                 // $contactNumber[] = str_replace('"', '', $request['investment_member_phone_no'][$key]);
    //                 // $text = 'Dear Member, Your A/C ' . $accountNumber . ' has been Credited on ' . date("d M Y", strtotime(str_replace('/', '-', $request['renewal_date']))) . ' With Rs. ' . round($request['amount'][$key], 2) . ' Cur Bal: ' . round($rAmount, 2) . '. Thanks Have a nice day';
    //                 // $templateId = 1207161726461603982;
    //                 // $sendToMember = new Sms();
    //                 // $sendToMember->sendSms($contactNumber, $text, $templateId);

    //                 $allContactNumbers = $request['investment_member_phone_no'];
    //                 $allAccountNumbers = $request['account_number'];
    //                 $rAmounts = $request['investment_id'];
    //                 $amounts = $request['amount'];
    //                 $ren_dates = date("d M Y", strtotime(str_replace('/', '-', $request['renewal_date'])));

    //             }
    //         }


    //         if ($renewPlanId != 2) {
    //             if ($request['payment_mode'] == 4) {
    //                 $ssbAccountTran = SavingAccountTranscation::insert($ssbTransactions);
    //             }
    //             if ($request['payment_mode'] == 1) {
    //                 $ssbAccountTran = \App\Models\SamraddhBankDaybook::insert($bankTransactions);
    //             }
    //             $result = $this->createBulkArray($branchDaybookTransactions, $allHeadTransactions, $cashAllHeadTransactions);
    //         }

    //         DB::commit();
    //      } catch (\Exception $ex) {
    //          DB::rollback();
    //          return back()->with('alert', $ex->getMessage());
    //      }
    //     $explodeArray = json_encode($request->all());

    //     $ssb = '';
    //     $encodeRequests = base64_encode($explodeArray);
    //     $encodebranchCode = base64_encode($branchCode);
    //     $encodebranchName = base64_encode($branchName);
    //     $totalAmount = base64_encode($request->total_amount);
    //     if ($renewPlanId == 2) {
    //         $ssb = json_encode($ssbAccountTran->id);
    //     } else {
    //         $ssb = $transactionId;
    //     }
    //     $allContactNumbers = http_build_query($allContactNumbers);
    //     $amounts = http_build_query($amounts);
    //     $allAccountNumbers = http_build_query($allAccountNumbers);
    //     $rAmounts = http_build_query($rAmounts);
    //     if($ren_dates=='' || $amounts==''){
    //         $baseurl = URL::to('/');
    //         $redirect_url = $baseurl . '/branch/renew/recipt/new/' . $encodeRequests . '/' . $encodebranchCode . '/' . $encodebranchName . '/' . $ssb . '/' . $totalAmount . '';
    //         return redirect($redirect_url);
    //        }

    //     return redirect()->route('branch.renew.new.sendMessage', [
    //         'allContactNumbers' => $allContactNumbers,
    //         'allAccountNumbers' => $allAccountNumbers,
    //         'rAmounts' => $rAmounts,
    //         'encodeRequests' => $encodeRequests,
    //         'encodebranchCode' => $encodebranchCode,
    //         'encodebranchName' => $encodebranchName,
    //         'ssb' => $ssb,
    //         'totalAmount' => $totalAmount,
    //         'amount' => $amounts,
    //         'ren_dates' =>$ren_dates
    //     ]);

    //     // $redirect_url = $baseurl . '/branch/renew/recipt/new/' . $encodeRequests . '/' . $encodebranchCode . '/' . $encodebranchName . '/' . $ssb . '/' . $totalAmount . '';
    //     // return redirect($redirect_url);
    // }


    public function storeAjax(RenewalRequest $request)
    {
        $m = ACommanController::renewlimit($request, $this->repository);;
        if($m['msg']){
            return back()->with('alert', $m['msg']); 
        }
        $validatedData = $request->validated();


        //$companyId = $request->company_id;
        $branchDaybookTransactions = array();
        $allHeadTransactions = array();
        $memberTransactions = array();
        $ssbTransactions = array();
        $bankTransactions = array();
        $baseurl = URL::to('/');

         DB::beginTransaction();
         try {
            $entryTime = date("H:i:s");
            $globaldate = date("Y-m-d", strtotime(convertDate($request['renewal_date'])));
            Session::put('created_at', date("Y-m-d", strtotime(convertDate($globaldate))));
            $getBranchId = getUserBranchId(Auth::user()->id);

            //$branch_id = (Auth::user()->id);

            $branch_idm = $getBranchId->id;
            $getBranchCode = getBranchDetail($branch_idm);

            $branch_id = $getBranchCode->id;
            $branchCode = $getBranchCode->branch_code;
            $branchName = $getBranchCode->name;
            $accountNumbers = $request['account_number'];
            $renewPlanId = $request['renewplan_id'];
            $sAccount = $this->getSavingAccountDetails($request['member_id']);
            $collectionSSBId = '';
            $planDetails = \App\Models\Plans::withoutGlobalScope(ActiveScope::class)->get()->keyBy('id');

            if ($request['payment_mode'] == 1) {
                $getChequeDetail = \App\Models\ReceivedCheque::where('id', $request['cheque_id'])->where('status', 3)->first(['id', 'amount', 'status']);
                if (!empty($getChequeDetail)) {
                    $response = array(
                        'status' => 'alert',
                        'msg' => 'Cheque already used select another cheque',
                    );
                    return Response::json($response);
                    // return back()->with('alert', 'Cheque already used select another cheque');
                } else {
                    $getamount = \App\Models\ReceivedCheque::where('id', $request['cheque_id'])->first(['id', 'amount']);
                    if ($getamount->amount != number_format((float) $request['total_amount'], 4, '.', '')) {
                        $response = array(
                            'status' => 'alert',
                            'msg' => 'Renew  amount is not equal to cheque amount',
                        );
                        return Response::json($response);
                        // return back()->with('alert', 'Renew  amount is not equal to cheque amount');
                    }
                }
                $receivedcheque = \App\Models\ReceivedCheque::find($request['cheque_id']);
                $receivedcheque->status = 3 ;
                $receivedcheque->save();
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
                $cheque_deposit_bank_id = $cheque_bank_to = $chequeDetail->deposit_bank_id;
                $getBankHead = \App\Models\SamraddhBank::where('id', $cheque_bank_to)->first();
                $cheque_deposit_bank_ac_id = $cheque_bank_ac_to = $chequeDetail->deposit_account_id;
                $cheque_no = $request['cheque-number'];
                $cheque_date = $pdate = date("Y-m-d", strtotime(str_replace('/', '-', $request['cheque-date'])));
                $cashHeadId = $getBankHead->account_head_id;
                $types = 'Bank';
                $subString = 'through Cheque(' . $cheque_no . ')';
                $cheque_no = $chequeDetail->cheque_no;
                $cheque_date = $cheque_date;
                $cheque_bank_from = $chequeDetail->bank_name;
                $cheque_bank_ac_from = $chequeDetail->cheque_account_no;
                $cheque_bank_ifsc_from = NULL;
                $cheque_bank_branch_from = $chequeDetail->branch_name;
            }

            if ($request['payment_mode'] == 0) { //cash
                $pmodeAll = 0;
                $cashHeadId = 28;
                $types = 'Cash';
                $subString = 'through cash(' . getBranchCode($branch_id)->branch_code . ')';
            }
            if ($request['payment_mode'] == 4) {
                $pmodeAll = 3;
                $cashHeadId = 56;
                $types = 'SSB';
                $subString = 'through ssb(' . $sAccount->account_no . ')';
            }
            if ($request['payment_mode'] == 3) {
                $pmodeAll = 2;
            }

            //Cheque
            Session::put('created_at', $globaldate);

            $ssbAccountTran = '';
            foreach ($accountNumbers as $key => $accountNumber) {

                if ($accountNumber && $request['amount'][$key]) {
                    $amountArraySsb = array('1' => $request['amount'][$key]);
                    $save = 0;
                    $investmentDetail = $this->getInvestmentPlanDetail($request['investment_id'][$key]);
                    $companyId =$investmentDetail->company_id;

                    $amount = $request['amount'][$key];


                    if ($renewPlanId == 2) {
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
                            $investData['company_id'] = $companyId;
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
                            /************* UpdateSaving Account ***************/
                            $record1 = SavingAccountTranscation::where('account_no', $sAccount->account_no)->where('type','=',1)->where('created_at', '<=', date("Y-m-d", strtotime(convertDate($request['renewal_date']))))->first();
                            if (empty($record1)) {
                                // $response = array(
                                //     'status' => 'alert',
                                //     'msg' => 'Renew date should less than created date',
                                // );
                                // return Response::json($response);
                                 return back()->with('alert', 'Renew date should less than created date');
                            }
                            $mtssb = 'Amount deposit by ' . $sAccount->account_no;
                            $rno = $sAccount->account_no;
                            $ssbAccountAmount = $sAccount->balance - $request['amount'][$key];
                            $ssb_id = $collectionSSBId = $sAccount->id;
                            $sResult = SavingAccount::find($ssb_id);
                            $sData['balance'] = $ssbAccountAmount;
                            $sResult->update($sData);
                            $ssb['saving_account_id'] = $ssb_id;
                            $ssb['account_no'] = $sAccount->account_no;
                            if ($record1) {
                                $ssb['opening_balance'] = $ssbAccountAmount;
                            } else {
                                $ssb['opening_balance'] = $request['amount'][$key];
                            }
                            $ssb['withdrawal'] = $request['amount'][$key];
                            if ($branch_id != $investmentDetail->branch_id) {

                                $ssb['description'] = 'Fund Trf. To ' . $savingAccountDetail->account_no . '- From ' . $branchName . '';
                            } else {
                                $ssb['description'] = 'Fund Trf. To ' . $savingAccountDetail->account_no;
                            }
                            $ssb['associate_id'] = $request['member_id'];
                            $ssb['branch_id'] = $branch_id;
                            $ssb['type'] = 6;
                            $ssb['currency_code'] = 'INR';
                            $ssb['payment_type'] = 'DR';
                            $ssb['payment_mode'] = $request['payment_mode'];
                            $ssb['deposit'] = NULL;
                            $ssb['is_renewal'] = 0;
                            $ssb['company_id'] = $companyId;
                            $ssb['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(str_replace('/', '-', $request['renewal_date'])));
                            ;
                            $ssbAccountTran = SavingAccountTranscation::create($ssb);
                            $ssbFromId = $ssb_id;
                            $ssbAccountTranFromId = $ssbAccountTran->id;

                            /************* UpdateSaving Account ***************/
                            $encodeDate = json_encode($ssb);
                        } else {
                            $ssbFromId = NULL;
                            $ssbAccountTranFromId = NULL;
                        }
                        $record3 = SavingAccountTranscation::where('account_no', $accountNumber)->where('created_at', '<', date("Y-m-d", strtotime(convertDate($request['renewal_date']))))->first();

                        $ssbAccountAmount = ($renewSavingOpeningBlanace + $request['amount'][$key]);
                        $ssb_id = $depositSSBId = $savingAccountDetail->id;
                        $sResult = SavingAccount::find($ssb_id);
                        $sData['balance'] = $ssbAccountAmount;
                        $sResult->update($sData);
                        $ssb['saving_account_id'] = $ssb_id;
                        $ssb['account_no'] = $accountNumber;
                        $ssb['opening_balance'] = ($renewSavingOpeningBlanace + $request['amount'][$key]);
                        $ssb['withdrawal'] = 0;
                        if ($branch_id != $investmentDetail->branch_id) {

                            $ssb['description'] = $mtssb . ' - From ' . $branchName . '';
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
                        $ssb['company_id'] = $companyId;
                        $ssb['deposit'] = $request['amount'][$key];
                        $ssb['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($request['renewal_date'])));
                        $ssbAccountTran = SavingAccountTranscation::create($ssb);
                        $ssbToId = $savingAccountDetail->id;
                        $ssbAccountTranToId = $ssbAccountTran->id;

                        $encodeDate = json_encode($ssb);

                        // ---------------------------  Day book modify --------------------------

                        $lastAmount = Daybook::where('investment_id', $request['investment_id'][$key])->whereIN('transaction_type', [2, 4, 16, 17, 18])->where('account_no', $accountNumber)->whereDate('created_at', '<=', date("Y-m-d", strtotime(convertDate($request['renewal_date']))))->orderBy('id', 'desc')->first();
                        if ($lastAmount) {
                            $lastBalance = (($lastAmount ? $lastAmount->opening_balance : 0 ) + $request['amount'][$key]);
                        } else {
                            $lastAmount = Daybook:: /*where('investment_id',$request['investment_id'][$key])->*/where('account_no', $accountNumber)->whereIN('transaction_type', [1])->whereDate('created_at', '<', date("Y-m-d", strtotime(convertDate($request['renewal_date']))))->orderby('id', 'desc')->first();
                            if ($lastAmount) {
                                $lastBalance = (($lastAmount ? $lastAmount->opening_balance : 0 ) + $request['amount'][$key]);
                            } else {
                                $lastOpeningAmount = Daybook::where('account_no', $accountNumber)->whereIN('transaction_type', [1])->orderby('id', 'desc')->first();
                                //-----------update -----
                                if ($renewPlanId == 2) {
                                    $lastOpeningAmount = SavingAccountTranscation::where('account_no', $accountNumber)->orderby('id', 'desc')->first();
                                }
                                $lastBalance = $lastOpeningAmount->opening_balance + $request['amount'][$key];
                            }
                        }
                        if ($lastAmount) {
                            $nextRenewal = $lastAmount = Daybook::where('investment_id', $request['investment_id'][$key])->whereIN('transaction_type', [1])->where('account_no', $accountNumber)->whereDate('created_at', '>', date("Y-m-d", strtotime(convertDate($lastAmount->created_at))))->get();
                            foreach ($nextRenewal as $key1 => $value) {
                                $daybookData['opening_balance'] = $value->opening_balance + $request['amount'][$key];
                                $daybookData['company_id'] = $companyId;
                                $dayBook = Daybook::find($value->id);
                                $dayBook->update($daybookData);
                            }
                        }
                        $refTransactions['amount'] = $request['amount'][$key];
                        $refTransactions['entry_date'] = $globaldate;
                        $refTransactions['entry_time'] = $entryTime;
                        $createTransaction = \App\Models\BranchDaybookReference::insertGetId($refTransactions);

                        $createDayBook = CommanTransactionsController::createDayBookNew($createTransaction, $ssbAccountTranToId, 2, $request['investment_id'][$key], $request['member_id'], $request['investment_member_id'][$key], $lastBalance, $request['amount'][$key], $withdrawal = 0, $ssb['description'], $ssbAccountNumber, $branch_id, $branchCode, $amountArraySsb, $request['payment_mode'], $request['deposite_by_name'], $request['member_id'], $accountNumber, $request['cheque-number'], $request['bank-name'], $request['branch-name'], $globaldate, NULL, $online_payment_by = NULL, $collectionSSBId, 'CR', $received_cheque_id, $cheque_deposit_bank_id, $cheque_deposit_bank_ac_id, $online_deposit_bank_id, $online_deposit_bank_ac_id, $companyId);
                        // ---------------------------  HEAD IMPLEMENT --------------------------
                        $planId = $investmentDetail->plan_id;

                        $this->investHeadCreateSSB($request['amount'][$key], $globaldate, $request['investment_id'][$key], $planId, $cheque_no, $cheque_id, $cheque_date, $online_transction_no, $online_transction_date, $online_deposit_bank_id, $online_deposit_bank_ac_id, $branch_id, $request['member_id'], $request['investment_member_id'][$key], $collectionSSBId, $ssbAccountTran->id, $pmodeAll, $ssbAccountNumber, $ssbFromId, $ssbAccountTranFromId, $ssbToId, $ssbAccountTranToId, $companyId);
                        //--------------------------------HEAD IMPLEMENT  ------------------------
                        $daybookData['is_renewal'] = 0;
                        $daybookData['company_id'] = $companyId;
                        $dayBook = Daybook::find($createDayBook);
                        $dayBook->update($daybookData);
                        /*--------------------cheque assign -----------------------*/
                        if ($request['payment_mode'] == "1") {
                            $receivedPayment['type'] = 3;
                            $receivedPayment['branch_id'] = $branch_id;
                            $receivedPayment['investment_id'] = $request['investment_id'][$key];
                            $receivedPayment['day_book_id'] = $createDayBook;
                            $receivedPayment['cheque_id'] = $request['cheque_id'];
                            $receivedPayment['created_at'] = $globaldate;
                            $receivedCreate = \App\Models\ReceivedChequePayment::create($receivedPayment);
                            // $dataRC['101171001058'] = 3;

                        }
                        /*-----------------------------------------------------------*/
                        $transaction = $this->transactionData($request->all(), $request['investment_id'][$key], $request['amount'][$key]);
                        $ipTransaction = Investmentplantransactions::create($transaction);
                        $ipTransactionData['is_renewal'] = 0;
                        $ipTransactionData['created_at'] = date("Y-m-d H:i:s", strtotime(str_replace('/', '-', $request['renewal_date'])));
                        $updateipTransaction = Investmentplantransactions::find($ipTransaction->id);
                        $updateipTransaction->update($ipTransactionData);
                        // $investmentCurrentBalance = getSavingCurrentBalance($ssb_id, $accountNumber);
                        //on 8 jan 2024 mahesh updated this
                        $investmentCurrentBalance = SavingAccountBalannce::where('saving_account_id',$ssb_id)->where('account_no',$accountNumber)->value('totalBalance');
                        $save = 1;
                        $rAmount = $investmentCurrentBalance;
                    } else {

                        $cDate = date("Y-m-d " . $entryTime . "", strtotime(convertDate($request['renewal_date'])));
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
                            $ssb['description'] = 'Fund Trf. To ' . $accountNumber . '';
                            $ssb['currency_code'] = 'INR';
                            $ssb['payment_type'] = 'CR';
                            $ssb['payment_mode'] = $request['payment_mode'];
                            $ssb['is_renewal'] = 0;
                            $ssb['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($request['renewal_date'])));
                            $ssbTransactions[] = [
                                'saving_account_id' => $ssb_id,
                                'account_no' => $sAccount->account_no,
                                'opening_balance' => $ssbAccountAmount,
                                'deposit' => NULL,
                                'withdrawal' => $request['amount'][$key],
                                'description' => 'Fund Trf. To ' . $accountNumber . '',
                                'currency_code' => 'INR',
                                'payment_type' => 'CR',
                                'payment_mode' => $request['payment_mode'],
                                'is_renewal' => 0,
                                'company_id' => $companyId,
                                'created_at' => date("Y-m-d " . $entryTime . "", strtotime(convertDate($request['renewal_date']))),
                            ];
                            $satRefId = NULL;

                            // dd($ssb);
                            $ssbFromId = $ssb_id;
                            $encodeDate = json_encode($ssb);
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
                        if ($investmentDetail) {
                            $totalbalance = $investmentDetail->current_balance + $request['amount'][$key];
                            $investData['current_balance'] = $totalbalance;
                            $rplanId = $investmentDetail->plan_id;
                            if ($request['payment_mode'] == 4) {
                                $assAccount = $this->getSavingAccountDetails($request['member_id']);
                                $description = 'Fund Rec. From ' . $assAccount->account_no . '';
                            } else {


                                if ($branch_id != $investmentDetail->branch_id) {

                                    $shortanem =  $planDetails[$investmentDetail->plan_id]->short_name;
                                    $description =  $shortanem.' Collection - From ' . $branchName . '';

                                        // $description = 'SDD Collection - From ' . $branchName . '';

                                } else {
                                    $shortanem =  $planDetails[$investmentDetail->plan_id]->short_name;
                                    $description =  $shortanem.' Collection ';

                                }
                            }
                        } else {
                            $totalbalance = '';
                            $description = '';
                        }

                        $currentDAte = date('Y-m-d H:i:s');
                        $lastAmount = Daybook::where('investment_id', $request['investment_id'][$key])->whereIN('transaction_type', [2, 4, 16, 17, 18])->where('account_no', $accountNumber)->whereDate('created_at', '<=', date("Y-m-d", strtotime(convertDate($request['renewal_date']))))->orderBy('id', 'desc')->first();

                        if ($lastAmount) {
                            $lastBalance = (($lastAmount ? $lastAmount->opening_balance : 0 ) + $request['amount'][$key]);
                        } else {
                            $lastAmount = Daybook::where('investment_id', $request['investment_id'][$key])->whereIN('transaction_type', [2, 4, 16, 17, 18])->where('account_no', $accountNumber)->whereDate('created_at', '<', date("Y-m-d", strtotime(convertDate($request['renewal_date']))))->orderby('id', 'desc')->first();
                            $lastBalance = (($lastAmount ? $lastAmount->opening_balance : 0 ) + $request['amount'][$key]);
                        }
                        $refTransactions['amount'] = $request['amount'][$key];
                        $refTransactions['entry_date'] = $globaldate;
                        $refTransactions['entry_time'] = $entryTime;


                        $transcation = \App\Models\BranchDaybookReference::insertGetId($refTransactions);
                        $t = date("H:i:s");
                        $type = 3;
                        $sub_type = 32;


                        $rdDesDR = $types . ' A/c Dr ' . $amount . '/-';
                        $rdDesCR = 'To ' . $planDetails[$investmentDetail->plan_id]->name . '(' . $investmentDetail->account_number . ')  A/c Cr ' . $amount . '/-';
                        $rdDes = 'Amount received for ' . $planDetails[$investmentDetail->plan_id]->name . ' A/C Renewal (' . $investmentDetail->account_number . ')' . $subString;
                        $rdDesMem = $planDetails[$investmentDetail->plan_id]->name . ' A/C Renewal (' . $investmentDetail->account_number . ')' . $subString;

                        $created_by = 2;
                        $created_by_id = $branch_id;
                        $dayBookTransactions['transaction_id'] = $transcation;
                        $dayBookTransactions['daybook_ref_id'] = $transcation;

                        $dayBookTransactions['transaction_type'] = 4;

                        $dayBookTransactions['saving_account_transaction_reference_id'] = $satRefId;
                        $dayBookTransactions['investment_id'] = $request['investment_id'][$key];
                        $dayBookTransactions['account_no'] = $accountNumber;
                        $dayBookTransactions['associate_id'] = $request['member_id'];
                        $dayBookTransactions['member_id'] = $request['investment_member_id'][$key];
                        $dayBookTransactions['opening_balance'] = $lastBalance;
                        $dayBookTransactions['deposit'] = $request['amount'][$key];
                        $dayBookTransactions['withdrawal'] = 0;
                        $dayBookTransactions['description'] = $description;
                        $dayBookTransactions['reference_no'] = NULL;
                        $dayBookTransactions['branch_id'] = $branch_id;
                        $dayBookTransactions['branch_code'] = $branchCode;
                        $dayBookTransactions['amount'] = $amount;
                        $dayBookTransactions['currency_code'] = 'INR';
                        $dayBookTransactions['payment_mode'] = $request['payment_mode'];
                        $dayBookTransactions['payment_type'] = 'CR';
                        $dayBookTransactions['amount_deposit_by_name'] = $request['deposite_by_name'];
                        $dayBookTransactions['amount_deposit_by_id'] = $request['member_id'];
                        $dayBookTransactions['created_by_id'] = $branch_id;
                        $dayBookTransactions['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($globaldate)));
                        $dayBookTransactions['created_at_default'] = Carbon::now();
                        $dayBookTransactions['created_by'] = 2;
                        $dayBookTransactions['company_id'] = $companyId;

                        if ($request['payment_mode'] == 1) {
                            $dayBookTransactions['cheque_dd_no'] = $cheque_no;
                            $dayBookTransactions['bank_name'] = getSamraddhBank($cheque_deposit_bank_ac_id)->bank_name;
                            $dayBookTransactions['branch_name'] = $branchName;
                            $dayBookTransactions['received_cheque_id'] = $received_cheque_id;
                            $dayBookTransactions['cheque_deposit_bank_id'] = $cheque_deposit_bank_id;
                            $dayBookTransactions['cheque_deposit_bank_ac_id'] = $cheque_deposit_bank_ac_id;

                        }

                        $transactionId = \App\Models\Daybook::insertGetId($dayBookTransactions);
                        $branchDaybookTransactions[] = [
                            'daybook_ref_id' => $transcation,
                            'branch_id' => $branch_id,
                            'type' => $type,
                            'sub_type' => $sub_type,
                            'type_id' => $request['investment_id'][$key],
                            'type_transaction_id' => $transactionId,
                            'associate_id' => $request['member_id'],
                            'member_id' => $request['investment_member_id'][$key],
                            'amount' => $amount,
                            'description' => $rdDes,
                            'description_dr' => $rdDesDR,
                            'description_cr' => $rdDesCR,
                            'payment_type' => 'CR',
                            'payment_mode' => $pmodeAll,
                            'currency_code' => 'INR',
                            'entry_date' => date("Y-m-d", strtotime(convertDate($globaldate))),
                            'entry_time' => date("H:i:s"),
                            'created_by' => $created_by,
                            'created_by_id' => $created_by_id,
                            'created_at' => date("Y-m-d " . $t . "", strtotime(convertDate($globaldate))),
                            'cheque_no' => $cheque_no ?? NULL,
                            'company_id' => $companyId ?? NULL,
                        ];

                        if ($request['payment_mode'] == 1) {
                            $bankTransactions[] = [
                                'daybook_ref_id' => $transcation,
                                'bank_id' => $chequeDetail->deposit_bank_id,
                                'account_id' => $chequeDetail->deposit_account_id,
                                'type' => $type,
                                'sub_type' => $sub_type,
                                'type_id' => $request['investment_id'][$key],
                                'type_transaction_id' => $transactionId,
                                'associate_id' => $request['member_id'],
                                'member_id' => $request['investment_member_id'][$key],
                                'branch_id' => $branch_id,
                                'opening_balance' => $amount,
                                'amount' => $amount,
                                'closing_balance' => $amount,
                                'description' => $rdDes,
                                'description_dr' => $rdDesDR,
                                'description_cr' => $rdDesCR,
                                'payment_type' => 'CR',
                                'payment_mode' => $pmodeAll,
                                'currency_code' => 'INR',
                                'transction_date' => date("Y-m-d", strtotime(convertDate($globaldate))),
                                'entry_date' => date("Y-m-d", strtotime(convertDate($globaldate))),
                                'entry_time' => date("H:i:s"),
                                'created_by' => $created_by,
                                'created_by_id' => $created_by_id,
                                'created_at' => date("Y-m-d " . $t . "", strtotime(convertDate($globaldate))),
                                'cheque_no' => $cheque_no ?? NULL,
                                'cheque_date' => $cheque_date ?? NULL,
                                'cheque_bank_from' => $cheque_bank_from ?? NULL,
                                'cheque_bank_ac_from' => $cheque_bank_ac_from ?? NULL,
                                'cheque_bank_branch_from' => $cheque_bank_branch_from ?? NULL,
                                'cheque_bank_to' => $cheque_bank_to ?? NULL,
                                'cheque_bank_ac_to' => $cheque_bank_ac_to ?? NULL,
                                'company_id' => $companyId ?? NULL,

                            ];
                        }

                        $cashAllHeadTransactions[] = [
                            'daybook_ref_id' => $transcation,
                            'branch_id' => $branch_id,
                            'type' => $type,
                            'head_id' => $cashHeadId,
                            'sub_type' => $sub_type,
                            'type_id' => $request['investment_id'][$key],
                            'type_transaction_id' => $transactionId,
                            'associate_id' => $request['member_id'],
                            'member_id' => $request['investment_member_id'][$key],
                            'amount' => $amount,
                            'description' => $rdDes,
                            'payment_type' => 'DR',
                            'payment_mode' => $pmodeAll,
                            'currency_code' => 'INR',
                            'entry_date' => date("Y-m-d", strtotime(convertDate($globaldate))),
                            'entry_time' => date("H:i:s"),
                            'created_by' => $created_by,
                            'created_by_id' => $created_by_id,
                            'created_at' => date("Y-m-d " . $t . "", strtotime(convertDate($globaldate))),
                            'cheque_no' => $cheque_no ?? NULL,
                            'company_id' => $companyId ?? NULL,
                            'bank_id' => $chequeDetail->deposit_bank_id ?? NULL,
                            'bank_ac_id' => $chequeDetail->deposit_account_id ?? NULL,
                            'cheque_id' => $chequeDetail->id ?? NULL,
                            'company_id' => $companyId ?? NULL,


                        ];

                        $allHeadTransactions[] = [
                            'daybook_ref_id' => $transcation,
                            'branch_id' => $branch_id,
                            'type' => $type,
                            'head_id' => $planDetails[$investmentDetail->plan_id]->deposit_head_id,
                            'sub_type' => $sub_type,
                            'type_id' => $request['investment_id'][$key],
                            'type_transaction_id' => $transactionId,
                            'associate_id' => $request['member_id'],
                            'member_id' => $request['investment_member_id'][$key],
                            'amount' => $amount,
                            'description' => $rdDes,
                            'payment_type' => 'CR',
                            'payment_mode' => $request['payment_mode'],
                            'currency_code' => 'INR',
                            'entry_date' => date("Y-m-d", strtotime(convertDate($globaldate))),
                            'entry_time' => date("H:i:s"),
                            'created_by' => $created_by,
                            'created_by_id' => $created_by_id,
                            'created_at' => date("Y-m-d " . $t . "", strtotime(convertDate($globaldate))),
                            'cheque_no' => $cheque_no ?? NULL,
                            'company_id' => $companyId ?? NULL,
                            'bank_id' => $chequeDetail->deposit_bank_id ?? NULL,
                            'bank_ac_id' => $chequeDetail->deposit_account_id ?? NULL,
                            'cheque_id' => $chequeDetail->id ?? NULL,
                            'company_id' => $companyId ?? NULL,
                        ];

                        // $investmentCurrentBalance = getInvestmentCurrentBalance($investmentDetail->id, $investmentDetail->account_number);
                        //on 8 jan 2024 mahesh updated this
                        $rAmount =InvestmentBalance::where('investment_id', $investmentDetail->id)->where('account_number',$investmentDetail->account_number)->value('totalBalance');
                        Memberinvestments::where('id', $investmentDetail->id)->update(['current_balance' => $rAmount]);
                    }
                }
                $contactNumber = array();


                if ($request['investment_member_phone_no'][$key]) {
                    $contactNumber[] = str_replace('"', '', $request['investment_member_phone_no'][$key]);
                    $text = 'Dear Member, Your A/C ' . $accountNumber . ' has been Credited on ' . date("d M Y", strtotime(str_replace('/', '-', $request['renewal_date']))) . ' With Rs. ' . round($request['amount'][$key], 2) . ' Cur Bal: ' . round($rAmount, 2) . '. Thanks Have a nice day';
                    $templateId = 1207161726461603982;
                    $sendToMember = new Sms();
                    $sendToMember->sendSms($contactNumber, $text, $templateId);
                }
            }


            if ($renewPlanId != 2) {
                if ($request['payment_mode'] == 4) {
                    $ssbAccountTran = SavingAccountTranscation::insert($ssbTransactions);
                }
                if ($request['payment_mode'] == 1) {
                    $ssbAccountTran = \App\Models\SamraddhBankDaybook::insert($bankTransactions);
                }
                $result = $this->createBulkArray($branchDaybookTransactions, $allHeadTransactions, $cashAllHeadTransactions);
            }
            DB::commit();
         } catch (\Exception $ex) {
             DB::rollback();
              return back()->with('alert', $ex->getMessage());
            //return back()->with('alert', $ex->getLine());


         }
        $explodeArray = json_encode($request->all());

        $ssb = '';
        $encodeRequests = base64_encode($explodeArray);
        $encodebranchCode = base64_encode($branchCode);
        $encodebranchName = base64_encode($branchName);
        $totalAmount = base64_encode($request->total_amount);
        if ($renewPlanId == 2) {
            $ssb = json_encode($ssbAccountTran->id);
        } else {
            $ssb = $transactionId;
        }
        $redirect_url = $baseurl . '/branch/renew/recipt/new/' . $encodeRequests . '/' . $encodebranchCode . '/' . $encodebranchName . '/' . $ssb . '/' . $totalAmount . '';

        return redirect($redirect_url);
    }
    /**
     * Get investment details.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return JSON
     */
    public function store(Request $request)
    {
        $entryTime = date("h:i:s");
        $globaldate = date("Y-m-d", strtotime(convertDate($request['renewal_date'])));
        $branch_id = $request['branch_id'];
        $getBranchCode = getBranchCode($branch_id);
        $getBranchName = getBranchName($branch_id);
        $branchCode = $getBranchCode->branch_code;
        $branchName = $getBranchName->name;
        $accountNumbers = $request['account_number'];
        $renewPlanId = $request['renewplan_id'];
        $sAccount = $this->getSavingAccountDetails($request['member_id']);
        $collectionSSBId = '';
        $encodeDate = json_encode($_POST);
        $arrs = array("saving_account_transaction_id" => 0, "type" => "3", "account_head_id" => 0, "user_id" => Auth::user()->id, "message" => "Renewal_Investment_Creation", "data" => $encodeDate);
        DB::table('user_log')->insert($arrs);
        DB::beginTransaction();
        try {
            if ($request['payment_mode'] == 1) {
                $getChequeDetail = \App\Models\ReceivedCheque::where('id', $request['cheque_id'])->where('status', 3)->first(['id', 'amount', 'status']);
                if (!empty($getChequeDetail)) {
                    return back()->with('alert', 'Cheque already used select another cheque');
                } else {
                    $getamount = \App\Models\ReceivedCheque::where('id', $request['cheque_id'])->first(['id', 'amount']);
                    if ($getamount->amount != number_format((float) $request['total_amount'], 4, '.', '')) {
                        return back()->with('alert', 'Renew amount is not equal to cheque amount');
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
            Session::put('created_at', date("Y-m-d", strtotime(convertDate($globaldate))));
            foreach ($accountNumbers as $key => $accountNumber) {
                if ($accountNumber && $request['amount'][$key]) {
                    $amountArraySsb = array('1' => $request['amount'][$key]);
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
                            /************* UpdateSaving Account ***************/
                            $record1 = SavingAccountTranscation::where('account_no', $sAccount->account_no)->where('created_at', '<=', date("Y-m-d", strtotime(convertDate($request['renewal_date']))))->first();
                            // if (empty($record1)) {
                            //     return back()->with('alert', 'Renew date should less than created date');
                            // }
                            $mtssb = 'Amount deposit by ' . $sAccount->account_no;
                            $rno = $sAccount->account_no;
                            $ssbAccountAmount = $sAccount->balance - $request['amount'][$key];
                            $ssb_id = $collectionSSBId = $sAccount->id;
                            $sResult = SavingAccount::find($ssb_id);
                            $sData['balance'] = $ssbAccountAmount;
                            $sResult->update($sData);
                            $ssb['saving_account_id'] = $ssb_id;
                            $ssb['account_no'] = $sAccount->account_no;
                            if ($record1) {
                                $ssb['opening_balance'] = $record1->opening_balance - $request['amount'][$key];
                            } else {
                                $ssb['opening_balance'] = $request['amount'][$key];
                            }
                            $ssb['withdrawal'] = $request['amount'][$key];
                            if ($branch_id != $investmentDetail->branch_id) {
                                $branchName = getBranchName($branch_id);
                                $ssb['description'] = 'Fund Trf. To ' . $savingAccountDetail->account_no . '- From ' . $branchName . '';
                            } else {
                                $ssb['description'] = 'Fund Trf. To ' . $savingAccountDetail->account_no;
                            }
                            $ssb['associate_id'] = $request['member_id'];
                            $ssb['branch_id'] = $branch_id;
                            $ssb['type'] = 6;
                            $ssb['currency_code'] = 'INR';
                            $ssb['payment_type'] = 'DR';
                            $ssb['payment_mode'] = $request['payment_mode'];
                            $ssb['deposit'] = NULL;
                            $ssb['is_renewal'] = 0;
                            $ssb['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(str_replace('/', '-', $request['renewal_date'])));
                            ;
                            $ssbAccountTran = SavingAccountTranscation::create($ssb);
                            $ssbFromId = $ssb_id;
                            $ssbAccountTranFromId = $ssbAccountTran->id;
                            $record2 = SavingAccountTranscation::where('account_no', $sAccount->account_no)->where('created_at', '>', date("Y-m-d", strtotime(convertDate($request['renewal_date']))))->get();
                            foreach ($record2 as $key1 => $value) {
                                $nsResult = SavingAccountTranscation::find($value->id);
                                $nsResult['opening_balance'] = $value->opening_balance - $request['amount'][$key];
                                $nsResult['updated_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($request->date)));
                                $nsResult->update($nsResult->toArray());
                            }
                            /************* UpdateSaving Account ***************/
                            $encodeDate = json_encode($ssb);
                            $arrs = array("saving_account_transaction_id" => $ssbAccountTran->id, "type" => "3", "account_head_id" => 0, "user_id" => Auth::user()->id, "message" => "Renewal_Investment_Creation", "data" => $encodeDate);
                            DB::table('user_log')->insert($arrs);
                            $satRefId = CommanController::createTransactionReferences($ssbAccountTran->id, $request['investment_id'][$key]);
                            $createTransaction = CommanController::createTransaction($satRefId, 5, 0, $request['investment_member_id'][$key], $branch_id, $branchCode, $amountArraySsb, $request['payment_mode'], $request['deposite_by_name'], $request['member_id'], $accountNumber, 0, NULL, NULL, date("Y-m-d", strtotime(str_replace('/', '-', $request['renewal_date']))), NULL, $online_payment_by = NULL, $ssb_id, 'DR');
                            $transactionData['is_renewal'] = 0;
                            $transactionData['created_at'] = date("Y-m-d H:i:s", strtotime(str_replace('/', '-', $request['renewal_date'])));
                            $updateTransaction = Transcation::find($createTransaction);
                            $updateTransaction->update($transactionData);
                            TranscationLog::where('transaction_id', $transactionData)->update(['is_renewal' => 0, 'created_at' => date("Y-m-d H:i:s", strtotime(str_replace('/', '-', $request['renewal_date'])))]);
                        } else {
                            $ssbFromId = NULL;
                            $ssbAccountTranFromId = NULL;
                        }
                        $record3 = SavingAccountTranscation::where('account_no', $accountNumber)->where('type','!=',1)->where('created_at', '<=', date("Y-m-d", strtotime(convertDate($request['renewal_date']))))->first();
                        if (empty($record3)) {
                            return back()->with('alert', 'Renew date should less than created date');
                        }
                        $ssbAccountAmount = ($renewSavingOpeningBlanace + $request['amount'][$key]);
                        $ssb_id = $depositSSBId = $savingAccountDetail->id;
                        $sResult = SavingAccount::find($ssb_id);
                        $sData['balance'] = $ssbAccountAmount;
                        $sResult->update($sData);
                        $ssb['saving_account_id'] = $ssb_id;
                        $ssb['account_no'] = $accountNumber;
                        $ssb['opening_balance'] = ($renewSavingOpeningBlanace + $request['amount'][$key]);
                        $ssb['withdrawal'] = 0;
                        if ($branch_id != $investmentDetail->branch_id) {
                            $branchName = getBranchName($branch_id);
                            $ssb['description'] = $mtssb . ' - From ' . $branchName . '';
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
                        $ssb['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($request['renewal_date'])));
                        $ssbAccountTran = SavingAccountTranscation::create($ssb);
                        $ssbToId = $savingAccountDetail->id;
                        $ssbAccountTranToId = $ssbAccountTran->id;
                        // $record4=SavingAccountTranscation::where('account_no',$accountNumber)->where('created_at','>',date("Y-m-d", strtotime(convertDate($request['renewal_date']))))->get();
                        // foreach ($record4 as $key1 => $value) {
                        // $saResult = SavingAccountTranscation::find($value->id);
                        // $newArray['opening_balance']=$value->opening_balance+$request['amount'][$key];
                        // $newArray['updated_at']=date("Y-m-d ".$entryTime."", strtotime(convertDate($request->renewal_date)));
                        // $saResult->update($newArray);
                        // SavingAccountTranscation::where('id', $value->id)->update(['opening_balance' => ($value->opening_balance+$request['amount'][$key]),'updated_at' => date("Y-m-d ".$entryTime."", strtotime(convertDate($request->renewal_date)))]);
                        // }
                        $encodeDate = json_encode($ssb);
                        $arrs = array("saving_account_transaction_id" => $ssbAccountTran->id, "type" => "3", "account_head_id" => 0, "user_id" => Auth::user()->id, "message" => "Renewal_Investment_Creation", "data" => $encodeDate);
                        DB::table('user_log')->insert($arrs);
                        $satRefId = CommanController::createTransactionReferences($ssbAccountTran->id, $request['investment_id'][$key]);
                        $createTransaction = CommanController::createTransaction($satRefId, 5, 0, $request['investment_member_id'][$key], $branch_id, $branchCode, $amountArraySsb, $request['payment_mode'], $request['deposite_by_name'], $request['member_id'], $accountNumber, $request['cheque-number'], $request['bank-name'], $request['branch-name'], date("Y-m-d", strtotime(str_replace('/', '-', $request['renewal_date']))), NULL, $online_payment_by = NULL, $ssb_id, 'CR');
                        $transactionData['is_renewal'] = 0;
                        $updateTransaction = Transcation::find($createTransaction);
                        $updateTransaction->update($transactionData);
                        TranscationLog::where('transaction_id', $transactionData)->update(['is_renewal' => 0, 'created_at' => date("Y-m-d", strtotime(str_replace('/', '-', $request['renewal_date'])))]);
                        // --------------------------- Day book modify --------------------------
                        $lastAmount = Daybook::where('investment_id', $request['investment_id'][$key])->whereIN('transaction_type', [2, 4, 16, 17, 18])->where('account_no', $accountNumber)->whereDate('created_at', '<=', date("Y-m-d", strtotime(convertDate($request['renewal_date']))))->orderBy('id', 'desc')->first();
                        if ($lastAmount) {
                            $lastBalance = (($lastAmount ? $lastAmount->opening_balance : 0 ) + $request['amount'][$key]);
                        } else {
                            $lastAmount = Daybook:: /*where('investment_id',$request['investment_id'][$key])->*/where('account_no', $accountNumber)->whereIN('transaction_type', [1])->whereDate('created_at', '<', date("Y-m-d", strtotime(convertDate($request['renewal_date']))))->orderby('id', 'desc')->first();
                            if ($lastAmount) {
                                $lastBalance = (($lastAmount ? $lastAmount->opening_balance : 0 ) + $request['amount'][$key]);
                            } else {
                                $lastOpeningAmount = Daybook::where('account_no', $accountNumber)->whereIN('transaction_type', [1])->orderby('id', 'desc')->first();
                                //-----------update -----
                                if ($renewPlanId == 2) {
                                    $lastOpeningAmount = SavingAccountTranscation::where('account_no', $accountNumber)->orderby('id', 'desc')->first();
                                }
                                $lastBalance = ($lastOpeningAmount ? $lastOpeningAmount->opening_balance : 0 ) + $request['amount'][$key];
                            }
                        }
                        if ($lastAmount) {
                            $nextRenewal = $lastAmount = Daybook::where('investment_id', $request['investment_id'][$key])->whereIN('transaction_type', [1])->where('account_no', $accountNumber)->whereDate('created_at', '>', date("Y-m-d", strtotime(convertDate($lastAmount->created_at))))->get();
                            foreach ($nextRenewal as $key1 => $value) {
                                $daybookData['opening_balance'] = ($value ? $value->opening_balance : 0) + $request['amount'][$key];
                                $dayBook = Daybook::find($value->id);
                                $dayBook->update($daybookData);
                            }
                        }
                        $createDayBook = CommanController::createDayBookNew($createTransaction, $satRefId, 2, $request['investment_id'][$key], $request['member_id'], $request['investment_member_id'][$key], $lastBalance, $request['amount'][$key], $withdrawal = 0, $ssb['description'], $ssbAccountNumber, $branch_id, $branchCode, $amountArraySsb, $request['payment_mode'], $request['deposite_by_name'], $request['member_id'], $accountNumber, $request['cheque-number'], $request['bank-name'], $request['branch-name'], $globaldate, NULL, $online_payment_by = NULL, $collectionSSBId, 'CR', $received_cheque_id, $cheque_deposit_bank_id, $cheque_deposit_bank_ac_id, $online_deposit_bank_id, $online_deposit_bank_ac_id);
                        // --------------------------- HEAD IMPLEMENT --------------------------
                        $planId = $investmentDetail->plan_id;
                        $this->investHeadCreateSSB($request['amount'][$key], $globaldate, $request['investment_id'][$key], $planId, $cheque_no, $cheque_id, $cheque_date, $online_transction_no, $online_transction_date, $online_deposit_bank_id, $online_deposit_bank_ac_id, $branch_id, $request['member_id'], $request['investment_member_id'][$key], $collectionSSBId, $ssbAccountTran->id, $pmodeAll, $ssbAccountNumber, $ssbFromId, $ssbAccountTranFromId, $ssbToId, $ssbAccountTranToId);
                        //--------------------------------HEAD IMPLEMENT ------------------------
                        $daybookData['is_renewal'] = 0;
                        $dayBook = Daybook::find($createDayBook);
                        //$dayBook->update($daybookData);
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
                        $investmentDetail = $this->getInvestmentPlanDetail($request['investment_id'][$key]);

                        // $investmentCurrentBalance = getInvestmentCurrentBalance($investmentDetail->id, $investmentDetail->account_number);
                        //on 8 jan 2024 mahesh updated this
                        $rAmount =InvestmentBalance::where('investment_id', $investmentDetail->id)->where('account_number',$investmentDetail->account_number)->value('totalBalance');
                        // $investmentCurrentBalance = getInvestmentCurrentBalance($investmentDetail->id, $investmentDetail->account_number);
                        // $rAmount = $investmentCurrentBalance;
                        $dataddd = DB::select('CALL renewal_procedure(' . $investmentDetail->current_balance . ')');
                        // dd($dataddd);
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
                            $ssb['description'] = 'Fund Trf. To ' . $accountNumber . '';
                            $ssb['currency_code'] = 'INR';
                            $ssb['payment_type'] = 'CR';
                            $ssb['payment_mode'] = $request['payment_mode'];
                            $ssb['is_renewal'] = 0;
                            $ssb['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($request['renewal_date'])));
                            $ssbAccountTran = SavingAccountTranscation::create($ssb);
                            $ssbFromId = $ssb_id;
                            $ssbAccountTranFromId = $ssbAccountTran->id;
                            $encodeDate = json_encode($ssb);
                            $arrs = array("saving_account_transaction_id" => $ssbAccountTran->id, "type" => "3", "account_head_id" => 0, "user_id" => Auth::user()->id, "message" => "Renewal_Investment_Creation", "data" => $encodeDate);
                            DB::table('user_log')->insert($arrs);
                            $satRefId = CommanController::createTransactionReferences($ssbAccountTran->id, $request['investment_id'][$key]);
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
                        if ($investmentDetail) {
                            $totalbalance = $investmentDetail->current_balance + $request['amount'][$key];
                            $investData['current_balance'] = $totalbalance;
                            $rplanId = $investmentDetail->plan_id;
                            if ($request['payment_mode'] == 4) {
                                $assAccount = $this->getSavingAccountDetails($request['member_id']);
                                $description = 'Fund Rec. From ' . $assAccount->account_no . '';
                            } else {
                                if ($branch_id != $investmentDetail->branch_id) {
                                    $branchName = getBranchName($branch_id);
                                    if ($rplanId == 7) {
                                        $description = 'SDD Collection - From ' . $branchName . '';
                                    } elseif ($rplanId == 10) {
                                        $description = 'SRD collection - From ' . $branchName . '';
                                    } elseif ($rplanId == 5) {
                                        $description = 'FRD collection - From ' . $branchName . '';
                                    } elseif ($rplanId == 3) {
                                        $description = 'SMB collection - From ' . $branchName . '';
                                    } elseif ($rplanId == 2) {
                                        $description = 'SK collection - From ' . $branchName . '';
                                    } elseif ($rplanId == 6) {
                                        $description = 'SJ collection - From ' . $branchName . '';
                                    } elseif ($rplanId == 11) {
                                        $description = 'SB collection - From ' . $branchName . '';
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
                        $createTransaction = CommanController::createTransaction($satRefId, 4, $request['investment_id'][$key], $request['investment_member_id'][$key], $branch_id, $branchCode, $amountArraySsb, $request['payment_mode'], $request['deposite_by_name'], $request['member_id'], $accountNumber, $request['cheque-number'], $request['bank-name'], $request['branch-name'], date("Y-m-d", strtotime(str_replace('/', '-', $request['renewal_date']))), NULL, $online_payment_by = NULL, $ssbAccountId, 'CR');
                        $transactionData['is_renewal'] = 0;
                        $updateTransaction = Transcation::find($createTransaction);
                        $updateTransaction->update($transactionData);
                        TranscationLog::where('transaction_id', $transactionData)->update(['is_renewal' => 0, 'created_at' => date("Y-m-d", strtotime(str_replace('/', '-', $request['renewal_date'])))]);
                        // --------------------------- Day book modify --------------------------
                        $lastAmount = Daybook::where('investment_id', $request['investment_id'][$key])->whereIN('transaction_type', [2, 4, 16, 17, 18])->where('account_no', $accountNumber)->whereDate('created_at', '<=', date(" Y-m-d", strtotime(convertDate($request['renewal_date']))))->orderBy('id', 'desc')->first();
                        if ($lastAmount) {
                            $lastBalance = (($lastAmount ? $lastAmount->opening_balance : 0 ) + $request['amount'][$key]);
                        } else {
                            $lastAmount = Daybook::where('investment_id', $request['investment_id'][$key])->whereIN('transaction_type', [2, 4, 16, 17, 18])->where('account_no', $accountNumber)->whereDate('created_at', '<', date("Y-m-d", strtotime(convertDate($request['renewal_date']))))->orderby('id', 'desc')->first();
                            $lastBalance = (($lastAmount ? $lastAmount->opening_balance : 0 ) + $request['amount'][$key]);
                        }
                        $createDayBook = CommanController::createDayBookNew($createTransaction, $satRefId, 4, $request['investment_id'][$key], $request['member_id'], $request['investment_member_id'][$key], $lastBalance, $request['amount'][$key], $withdrawal = 0, $description, $ref = NULL, $branch_id, $branchCode, $amountArraySsb, $request['payment_mode'], $request['deposite_by_name'], $request['member_id'], $accountNumber, $request['cheque-number'], $request['bank-name'], $request['branch-name'], $globaldate, NULL, $online_payment_by = NULL, $collectionSSBId, 'CR', $received_cheque_id, $cheque_deposit_bank_id, $cheque_deposit_bank_ac_id, $online_deposit_bank_id, $online_deposit_bank_ac_id);
                        updateRenewalTransaction($accountNumber);
                        // --------------------------- HEAD IMPLEMENT --------------------------
                        $planId = $investmentDetail->plan_id;
                        $this->investHeadCreate($request['amount'][$key], $globaldate, $request['investment_id'][$key], $planId, $cheque_no, $cheque_id, $cheque_date, $online_transction_no, $online_transction_date, $online_deposit_bank_id, $online_deposit_bank_ac_id, $branch_id, $request['member_id'], $request['investment_member_id'][$key], $collectionSSBId, $createDayBook, $pmodeAll, $investmentDetail->account_number, $ssbFromId, $ssbAccountTranFromId, $ssbToId, $ssbAccountTranToId);
                        //--------------------------------HEAD IMPLEMENT ------------------------
                        /*------------------------------- Commission Section Start ------------------------------------*/
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
                        /*----------------------------- Commission Section End -------------------------------------*/
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
                        //$dayBook->update($daybookData);
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
                        //$ipTransactionData['created_at'] = date("Y-m-d H:i:s", strtotime( str_replace('/','-',$request['renewal_date'] ) ) );
                        $updateipTransaction = Investmentplantransactions::find($ipTransaction->id);
                        $updateipTransaction->update($ipTransactionData);

                        // $investmentCurrentBalance = getInvestmentCurrentBalance($investmentDetail->id, $investmentDetail->account_number);
                        //on 8 jan 2024 mahesh updated this
                        $rAmount =InvestmentBalance::where('investment_id', $investmentDetail->id)->where('account_number',$investmentDetail->account_number)->value('totalBalance');
                        // $investmentCurrentBalance = getInvestmentCurrentBalance($investmentDetail->id, $investmentDetail->account_number);
                        //echo $investmentDetail->id.'-'.$investmentDetail->account_number; die;
                        $save = 1;
                        // $rAmount = $investmentCurrentBalance;
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
        $explodeArray = json_encode($request->all());
        $ssb = '';
        $encodeRequests = base64_encode($explodeArray);
        $encodebranchCode = base64_encode($branchCode);
        $encodebranchName = base64_encode($branchName);
        if ($renewPlanId == 2) {
            $ssb = json_encode($ssbAccountTran->id);
        } else {
            $ssb = $createDayBook;
        }
        return redirect('admin/renew/recipt/' . $encodeRequests . '/' . $encodebranchCode . '/' . $encodebranchName . '/' . $ssb . '');
    }
    public function renewalDetails($url, $branchCode, $branchName, $ssb = NULL, $totalAmount = NULL)
    {

        $decodedurl = base64_decode($url);
        $explodeArray = json_decode($decodedurl);
        $branchCode = base64_decode($branchCode);
        $branchName = base64_decode($branchName);
        $totalAmount = base64_decode($totalAmount);

        $data['title'] = "Renewal Receipt";
        $data['renewFields'] = (array) $explodeArray;
        $data['branchCode'] = $branchCode;
        $data['branchName'] = $branchName;
        $data['totalAmount'] = $totalAmount;
        /*Code added by aman*//*Code changed  by tansukh*/
        if (isset($data['renewFields']['form_data']) && $data['renewFields']['form_data'] != NULL) {
            parse_str($data['renewFields']['form_data'], $data_new);
            $arr_new['renewFields'] = array_merge($data, $data_new);
            $arr_new['title'] = "Renewal Receipt";
            $arr_new['branchCode'] = $branchCode;
            $arr_new['branchName'] = $branchName;
            $arr_new['totalAmount'] = $totalAmount;
            // print_r($array);
            //dd($arr_new);
            if (isset($arr_new['renewFields']['renewplan_id']) && $arr_new['renewFields']['renewplan_id'] == 2) {
                $arr_new['ssb_amount'] = SavingAccountTranscation::with('company:id,name')->where('id', $ssb)->first();
                $arr_new['company_name'] = $data['ssb_amount']['company']['name'];
            } else {
                $arr_new['ssb_amount'] = Daybook::with('companyName:id,name')->where('id', $ssb)->first();
                $arr_new['company_name'] = $data['ssb_amount']['companyName']['name'];
            }
        } else {
            if (isset($data['renewFields']['renewplan_id']) && $data['renewFields']['renewplan_id'] == 2) {
                $data['ssb_amount'] = SavingAccountTranscation::with('company:id,name')->where('id', $ssb)->first();
                $data['company_name'] = $data['ssb_amount']['company']['name'];
            } else {
                $data['ssb_amount'] = Daybook::with('companyName:id,name')->where('id', $ssb)->first();
                $data['company_name'] = $data['ssb_amount']['companyName']['name'];
            }
        }

        if (isset($arr_new) && $arr_new != NULL) {
            return view('templates.branch.investment_management.renewalNew.recipt', $arr_new);
        } else {
            return view('templates.branch.investment_management.renewalNew.recipt', $data);
        }
        //End of code
        /*        if($data['renewFields']['renewplan_id'] == 2)
        {
        $data['ssb_amount'] = SavingAccountTranscation::where('id',$ssb)->first();
        }
        else
        {
        $data['ssb_amount'] = Daybook::where('id',$ssb)->first();
        }
        return view('templates.admin.investment_management.renewal.recipt', $data);*/
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
        $sAccount = SavingAccount::where('customer_id',$request['member_id'])->where('company_id',$request['company_id'])->first();
        $data['investment_id'] = $investmentId;
        $data['plan_id'] = $request['renew_investment_plan_id'];
        $data['member_id'] = $request['member_id'];
        $data['branch_id'] = $branch_id;
        $data['branch_code'] = $branchCode;
        $data['deposite_amount'] = $amount;
        $data['deposite_date'] = date("Y-m-d", strtotime(convertDate($request['renewal_date'])));
        $data['deposite_month'] = date("m", strtotime(str_replace('/', '-', $request['renewal_date'])));
        $data['payment_mode'] = $request['payment_mode'];
        if (isset($sAccount->id)) {
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
        $daybookRefRD = CommanController::createBranchDayBookReferenceNew($amount, $globaldate);
        $refIdRD = $daybookRefRD;
        $currency_code = 'INR';
        $headPaymentModeRD = 0;
        $payment_type_rd = 'CR';
        $type_id = $investmentId;
        $entry_date = date("Y-m-d", strtotime(convertDate($globaldate)));
        $entry_time = date("H:i:s", strtotime(convertDate($globaldate)));
        $created_by = 2;
        $created_by_id = $branch_id;
        $created_at = date("Y-m-d H:i:s", strtotime(convertDate($globaldate)));
        $updated_at = date("Y-m-d H:i:s", strtotime(convertDate($globaldate)));
        $planDetail = getPlanDetail($planId);
        $type = 3;
        $sub_type = 32;
        $planCode = $planDetail->plan_code;
        ;
        $head_id = $planDetail->deposit_head_id;

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
        if ($payment_mode == 1) { // cheque moade
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
            $allTranRDcheque = CommanController::createAllHeadTransaction($refIdRD, $branch_id, $bank_id = NULL, $bank_ac_id = NULL, $head41, $type, $sub_type, $type_id, $createDayBook, $associate_id, $memberId, $branch_id_to = NULL, $branch_id_from = NULL, $amount, $amount, $amount, $rdDes, 'DR', $headPaymentModeRD, $currency_code, $amount_to_id = NULL, $amount_to_name = NULL, $amount_from_id = NULL, $amount_from_name = NULL, $jv_unique_id = NULL, $v_no, $v_date, $ssb_account_id_from, $ssb_account_id_to = NULL, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, 0, $cheque_id, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_from_id = NULL, $cheque_bank_ac_from_id = NULL, $cheque_bank_to, $cheque_bank_ac_to, getSamraddhBank($cheque_bank_to)->bank_name, $cheque_bank_to_branch = NULL, getSamraddhBankAccountId($cheque_bank_ac_to)->account_no, getSamraddhBankAccountId($cheque_bank_ac_to)->ifsc_code, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_from_id = NULL, $transction_bank_from_ac_id = NULL, $transction_bank_to, $transction_bank_ac_to, $transction_bank_to_name = NULL, $transction_bank_to_ac_no = NULL, $transction_bank_to_branch = NULL, $transction_bank_to_ifsc = NULL, $transction_date, $created_by, $created_by_id);
            /*$allTranRDcheque=CommanController::createAllTransactionNew($refIdRD,$branch_id,$bank_id=NULL,$bank_ac_id=NULL,$head11,$head21,$head31,$head41,$head51,$type,$sub_type,$type_id,$associate_id,$memberId,$branch_id_to=NULL,$branch_id_from=NULL,$opening_balance=NULL,$amount,$closing_balance=NULL,$rdDes,'CR',$headPaymentModeRD,$currency_code,$amount_to_id=NULL,$amount_to_name=NULL,$amount_from_id=NULL,$amount_from_name=NULL,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at,$updated_at,$createDayBook);*/
            //bank entry
            $bankCheque = CommanController::samraddhBankDaybookNew($refIdRD, $cheque_bank_to, $cheque_bank_ac_to, $type, $sub_type, $type_id, $createDayBook, $associate_id, $memberId, $branch_id, $opening_balance = NULL, $amount, $closing_balance = NULL, $rdDes, $rdDesDR, $rdDesCR, 'CR', $headPaymentModeRD, $currency_code, $amount_to_id = NULL, $amount_to_name = NULL, $amount_from_id = NULL, $amount_from_name = NULL, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_bank_to_name = NULL, $transction_bank_to_ac_no = NULL, $transction_bank_to_branch = NULL, $transction_bank_to_ifsc = NULL, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL);
            /*$bankCheque=CommanController::createSamraddhBankDaybookNew($refIdRD,$cheque_bank_to,$cheque_bank_ac_to,$type,$sub_type,$type_id,$associate_id,$memberId,$branch_id,$opening_balance=NULL,$amount,$closing_balance=NULL,$rdDes,$rdDesDR,$rdDesCR,'CR',$headPaymentModeRD,$currency_code,$amount_to_id=NULL,$amount_to_name=NULL,$amount_from_id=NULL,$amount_from_name=NULL,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at,$updated_at,$createDayBook);*/
            //bank balence
            $bankClosing = $this->updateBackDateBankBalance($cheque_bank_to, $cheque_bank_ac_to, $created_at, $amount, 0);
        } elseif ($payment_mode == 2) { //online transaction
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
            $allTranRDonline = CommanController::createAllHeadTransaction($refIdRD, $branch_id, $bank_id = NULL, $bank_ac_id = NULL, $head411, $type, $sub_type, $type_id, $createDayBook, $associate_id, $memberId, $branch_id_to = NULL, $branch_id_from = NULL, $amount, $amount, $amount, $rdDes, 'DR', $headPaymentModeRD, $currency_code, $amount_to_id = NULL, $amount_to_name = NULL, $amount_from_id = NULL, $amount_from_name = NULL, $jv_unique_id = NULL, $v_no, $v_date, $ssb_account_id_from, $ssb_account_id_to = NULL, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $cheque_type = NULL, $cheque_id = NULL, $cheque_no = NULL, $cheque_date = NULL, $cheque_bank_from = NULL, $cheque_bank_ac_from = NULL, $cheque_bank_ifsc_from = NULL, $cheque_bank_branch_from = NULL, $cheque_bank_from_id = NULL, $cheque_bank_ac_from_id = NULL, $cheque_bank_to = NULL, $cheque_bank_ac_to = NULL, $cheque_bank_to_name = NULL, $cheque_bank_to_branch = NULL, $cheque_bank_to_ac_no = NULL, $cheque_bank_to_ifsc = NULL, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_from_id = NULL, $transction_bank_from_ac_id = NULL, $transction_bank_to, $transction_bank_ac_to, getSamraddhBank($online_deposit_bank_id)->bank_name, getSamraddhBankAccountId($online_deposit_bank_ac_id)->account_no, $transction_bank_to_branch = NULL, getSamraddhBankAccountId($online_deposit_bank_ac_id)->ifsc_code, $transction_date, $created_by, $created_by_id);
            /*$allTranRDonline=CommanController::createAllTransactionNew($refIdRD,$branch_id,$bank_id=NULL,$bank_ac_id=NULL,$head111,$head211,$head311,$head411,$head511,$type,$sub_type,$type_id,$associate_id,$memberId,$branch_id_to=NULL,$branch_id_from=NULL,$opening_balance=NULL,$amount,$closing_balance=NULL,$rdDes,'CR',$headPaymentModeRD,$currency_code,$amount_to_id=NULL,$amount_to_name=NULL,$amount_from_id=NULL,$amount_from_name=NULL,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at,$updated_at,$createDayBook);*/
            //bank entry
            $bankonline = CommanController::samraddhBankDaybookNew($refIdRD, $transction_bank_to, $transction_bank_ac_to, $type, $sub_type, $type_id, $createDayBook, $associate_id, $memberId, $branch_id, $opening_balance = NULL, $amount, $closing_balance = NULL, $rdDes, $rdDesDR, $rdDesCR, 'CR', $headPaymentModeRD, $currency_code, $amount_to_id = NULL, $amount_to_name = NULL, $amount_from_id = NULL, $amount_from_name = NULL, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, getSamraddhBank($online_deposit_bank_id)->bank_name, getSamraddhBankAccountId($online_deposit_bank_ac_id)->account_no, $transction_bank_to_branch = NULL, getSamraddhBankAccountId($online_deposit_bank_ac_id)->ifsc_code, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL);
            /*$bankonline=CommanController::createSamraddhBankDaybookNew($refIdRD,$transction_bank_to,$transction_bank_ac_to,$type,$sub_type,$type_id,$associate_id,$memberId,$branch_id,$opening_balance=NULL,$amount,$closing_balance=NULL,$rdDes,$rdDesDR,$rdDesCR,'CR',$headPaymentModeRD,$currency_code,$amount_to_id=NULL,$amount_to_name=NULL,$amount_from_id=NULL,$amount_from_name=NULL,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at,$updated_at,$createDayBook);*/
            //bank balence
            $bankClosing = $this->updateBackDateBankBalance($transction_bank_to, $transction_bank_ac_to, $created_at, $amount, 0);
        } elseif ($payment_mode == 3) {
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
            $allTranRDSSB = CommanController::createAllHeadTransaction($refIdRD, $branch_id, $bank_id = NULL, $bank_ac_id = NULL, $head4rdSSB, $type, $sub_type, $type_id, $createDayBook, $associate_id, $memberId, $branch_id_to = NULL, $branch_id_from = NULL, $amount, $amount, $amount, $rdDes, 'DR', $headPaymentModeRD, $currency_code, $amount_to_id = NULL, $amount_to_name = NULL, $amount_from_id = NULL, $amount_from_name = NULL, $jv_unique_id = NULL, $v_no, $v_date, $ssb_account_id_from, $ssb_account_id_to = NULL, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $cheque_type = NULL, $cheque_id = NULL, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_from_id = NULL, $cheque_bank_ac_from_id = NULL, $cheque_bank_to, $cheque_bank_ac_to, $cheque_bank_to_name = NULL, $cheque_bank_to_branch = NULL, $cheque_bank_to_ac_no = NULL, $cheque_bank_to_ifsc = NULL, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_from_id = NULL, $transction_bank_from_ac_id = NULL, $transction_bank_to, $transction_bank_ac_to, $transction_bank_to_name = NULL, $transction_bank_to_ac_no = NULL, $transction_bank_to_branch = NULL, $transction_bank_to_ifsc = NULL, $transction_date, $created_by, $created_by_id);
            $branchClosingSSB = $this->updateBranchClosingCashDr($branch_id, $created_at, $amount, 0);
            $memberTranInvest77 = CommanController::memberTransactionNew($refIdRD, '4', '47', $ssb_account_id_from, $createDayBook, $associate_id, $ssbDetals->member_id, $branch_id, $bank_id = NULL, $account_id = NULL, $amount, $SSBDescTran, 'DR', $headPaymentModeRD, $currency_code, $amount_to_id = $type_id, $amount_to_name = $planDetail->name, $amount_from_id = NULL, $amount_from_name = NULL, $v_no, $v_date, $ssb_account_id_from = NULL, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to /*,$transction_bank_to_name=NULL,$transction_bank_to_ac_no=NULL,$transction_bank_to_branch=NULL,$transction_bank_to_ifsc=NULL*/, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL);
        } else {
            $ssb_account_id_from = $ssbFromId;
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
            $allTranRDcash = CommanController::createAllHeadTransaction($refIdRD, $branch_id, $bank_id = NULL, $bank_ac_id = NULL, $head3rdC, $type, $sub_type, $type_id, $createDayBook, $associate_id, $memberId, $branch_id_to = NULL, $branch_id_from = NULL, $amount, $amount, $amount, $rdDes, 'DR', $headPaymentModeRD, $currency_code, $amount_to_id = NULL, $amount_to_name = NULL, $amount_from_id = NULL, $amount_from_name = NULL, $jv_unique_id = NULL, $v_no, $v_date, $ssb_account_id_from, $ssb_account_id_to = NULL, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $cheque_type = NULL, $cheque_id = NULL, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_from_id = NULL, $cheque_bank_ac_from_id = NULL, $cheque_bank_to, $cheque_bank_ac_to, $cheque_bank_to_name = NULL, $cheque_bank_to_branch = NULL, $cheque_bank_to_ac_no = NULL, $cheque_bank_to_ifsc = NULL, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_from_id = NULL, $transction_bank_from_ac_id = NULL, $transction_bank_to, $transction_bank_ac_to, $transction_bank_to_name = NULL, $transction_bank_to_ac_no = NULL, $transction_bank_to_branch = NULL, $transction_bank_to_ifsc = NULL, $transction_date, $created_by, $created_by_id);
            /*$allTranRDcash=$this->createAllTransactionNew($refIdRD,$branch_id,$bank_id=NULL,$bank_ac_id=NULL,$head1rdC,$head2rdC,$head3rdC,$head4rdC,$head5rdC,$type,$sub_type,$type_id,$associate_id,$memberId,$branch_id_to=NULL,$branch_id_from=NULL,$opening_balance=NULL,$amount,$closing_balance=NULL,$rdDes,$payment_type_rd,$headPaymentModeRD,$currency_code,$amount_to_id=NULL,$amount_to_name=NULL,$amount_from_id=NULL,$amount_from_name=NULL,$v_no,$v_date,$ssbFromId,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at,$updated_at,$createDayBook,$ssbToId,$ssbAccountTranToId,$ssbAccountTranFromId);*/
            //Balance   entry +
            $branchCash = $this->updateBranchCashCr($branch_id, $created_at, $amount, 0);
        }
        //branch day book entry +
        $daybookInvest = CommanController::branchDayBookNew($refIdRD, $branch_id, $type, $sub_type, $type_id, $createDayBook, $associate_id, $memberId, $branch_id_to = NULL, $branch_id_from = NULL, $opening_balance = NULL, $amount, $closing_balance = NULL, $rdDes, $rdDesDR, $rdDesCR, $payment_type_rd, $headPaymentModeRD, $currency_code, $amount_to_id = NULL, $amount_to_name = NULL, $amount_from_id = NULL, $amount_from_name = NULL, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $is_contra = NULL, $contra_id = NULL, $created_at, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL);
        /*$daybookInvest = $this->createBranchDayBookNew($refIdRD,$branch_id,$type,$sub_type,$type_id,$associate_id,$memberId,$branch_id_to=NULL,$branch_id_from=NULL,$opening_balance=NULL,$amount,$closing_balance=NULL,$rdDes,$rdDesDR,$rdDesCR,$payment_type_rd,$headPaymentModeRD,$currency_code,$amount_to_id=NULL,$amount_to_name=NULL,$amount_from_id=NULL,$amount_from_name=NULL,$v_no,$v_date,$ssbFromId,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$is_contra=NULL,$contra_id=NULL,$created_at,$updated_at,$createDayBook,$ssbToId,$ssbAccountTranToId,$ssbAccountTranFromId);*/
        // Investment head entry +
        $allTranInvest = CommanController::createAllHeadTransaction($refIdRD, $branch_id, $bank_id = NULL, $bank_ac_id = NULL, $head_id, $type, $sub_type, $type_id, $createDayBook, $associate_id, $memberId, $branch_id_to = NULL, $branch_id_from = NULL, $amount, $amount, $amount, $rdDes, $payment_type_rd, $headPaymentModeRD, $currency_code, $amount_to_id = NULL, $amount_to_name = NULL, $amount_from_id = NULL, $amount_from_name = NULL, $jv_unique_id = NULL, $v_no, $v_date, $ssb_account_id_from, $ssb_account_id_to = NULL, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $cheque_type = NULL, $cheque_id = NULL, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_from_id = NULL, $cheque_bank_ac_from_id = NULL, $cheque_bank_to, $cheque_bank_ac_to, $cheque_bank_to_name = NULL, $cheque_bank_to_branch = NULL, $cheque_bank_to_ac_no = NULL, $cheque_bank_to_ifsc = NULL, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_from_id = NULL, $transction_bank_from_ac_id = NULL, $transction_bank_to, $transction_bank_ac_to, $transction_bank_to_name = NULL, $transction_bank_to_ac_no = NULL, $transction_bank_to_branch = NULL, $transction_bank_to_ifsc = NULL, $transction_date, $created_by, $created_by_id);
        /*$allTranInvest = $this->createAllTransactionNew($refIdRD,$branch_id,$bank_id=NULL,$bank_ac_id=NULL,$head1Invest,$head2Invest,$head3Invest,$head4Invest,$head5Invest,$type,$sub_type,$type_id,$associate_id,$memberId,$branch_id_to=NULL,$branch_id_from=NULL,$opening_balance=NULL,$amount,$closing_balance=NULL,$rdDes,$payment_type_rd,$headPaymentModeRD,$currency_code,$amount_to_id=NULL,$amount_to_name=NULL,$amount_from_id=NULL,$amount_from_name=NULL,$v_no,$v_date,$ssbFromId,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at,$updated_at,$createDayBook,$ssbToId,$ssbAccountTranToId,$ssbAccountTranFromId);*/
        // Member transaction  +
        $memberTranInvest = CommanController::memberTransactionNew($refIdRD, $type, $sub_type, $type_id, $createDayBook, $associate_id, $memberId, $branch_id, $bank_id = NULL, $account_id = NULL, $amount, $rdDesMem, $payment_type_rd, $headPaymentModeRD, $currency_code, $amount_to_id = NULL, $amount_to_name = NULL, $amount_from_id = NULL, $amount_from_name = NULL, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL);
        /*$memberTranInvest = $this->createMemberTransactionNew($refIdRD,$type,$sub_type,$type_id,$associate_id,$memberId,$branch_id,$bank_id=NULL,$account_id=NULL,$amount,$rdDesMem,$payment_type_rd,$headPaymentModeRD,$currency_code,$amount_to_id=NULL,$amount_to_name=NULL,$amount_from_id=NULL,$amount_from_name=NULL,$v_no,$v_date,$ssbFromId,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at,$updated_at,$createDayBook,$ssbToId,$ssbAccountTranToId,$ssbAccountTranFromId);*/
        /******** Balance   entry ***************/
        $branchClosing = $this->updateBranchClosingCashCr($branch_id, $created_at, $amount, 0);
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
    public function investHeadCreateSSB($amount, $globaldate, $investmentId, $planId, $cheque_no, $cheque_id, $cheque_date, $online_transction_no, $online_transction_date, $online_deposit_bank_id, $online_deposit_bank_ac_id, $branch_id, $associate_id, $memberId, $ssbId, $createDayBook, $payment_mode, $investmentAccountNoRd, $ssbFromId, $ssbAccountTranFromId, $ssbToId, $ssbAccountTranToId, $companyId)
    {
        DB::beginTransaction();
        try {

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
        $head4Invest =$planDetail->deposit_head_id;

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

        if ($payment_mode == 1) { // cheque moade

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

            $allHeadTranRDcheque = CommanTransactionsController::createAllHeadTransaction(
                $refIdRD,
                $branch_id,
                $bank_id = $chequeDetail->deposit_bank_id ?? NULL,
                $bank_ac_id = $chequeDetail->deposit_account_id ?? NULL,
                $head41,
                $type,
                $sub_type,
                $type_id,
                $createDayBook,
                $associate_id,
                $memberId,
                $branch_id_to = NULL,
                $branch_id_from = NULL,
                $amount,
                $rdDes,
                'DR',
                $headPaymentModeRD,
                $currency_code,
                $jv_unique_id = NULL,
                $v_no,
                $ssb_account_id_from,
                $ssb_account_id_to = NULL,
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

            /*$allTranRDcheque=CommanTransactionsController::createAllTransactionNew($refIdRD,$branch_id,$bank_id=NULL,$bank_ac_id=NULL,$head11,$head21,$head31,$head41,$head51,$type,$sub_type,$type_id,$associate_id,$memberId,$branch_id_to=NULL,$branch_id_from=NULL,$opening_balance=NULL,$amount,$closing_balance=NULL,$rdDes,'CR',$headPaymentModeRD,$currency_code,$amount_to_id=NULL,$amount_to_name=NULL,$amount_from_id=NULL,$amount_from_name=NULL,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at,$updated_at,$createDayBook);*/

            //bank entry

            $bankCheque = CommanTransactionsController::samraddhBankDaybookNew($refIdRD, $cheque_bank_to, $cheque_bank_ac_to, $type, $sub_type, $type_id, $createDayBook, $associate_id, $memberId, $branch_id, $opening_balance = NULL, $amount, $closing_balance = NULL, $rdDes, $rdDesDR, $rdDesCR, 'CR', $headPaymentModeRD, $currency_code, $amount_to_id = NULL, $amount_to_name = NULL, $amount_from_id = NULL, $amount_from_name = NULL, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_bank_to_name = NULL, $transction_bank_to_ac_no = NULL, $transction_bank_to_branch = NULL, $transction_bank_to_ifsc = NULL, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $companyId);

            //bank balence

            // $bankClosing = CommanTransactionsController::checkCreateBankClosing($cheque_bank_to, $cheque_bank_ac_to, $created_at, $amount, 0);
        } elseif ($payment_mode == 2) { //online transaction

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

            $allTranRDonline = CommanTransactionsController::createAllHeadTransaction(
                $refIdRD,
                $branch_id,
                $bank_id = NULL,
                $bank_ac_id = NULL,
                $head411,
                $type,
                $sub_type,
                $type_id,
                $createDayBook,
                $associate_id,
                $memberId,
                $branch_id_to = NULL,
                $branch_id_from = NULL,
                $amount,
                $rdDes,
                'DR',
                $headPaymentModeRD,
                $currency_code,
                $jv_unique_id = NULL,
                $v_no,
                $ssb_account_id_from,
                $ssb_account_id_to = NULL,
                $ssb_account_tran_id_to = NULL,
                $ssb_account_tran_id_from = NULL,
                $cheque_type = NULL,
                $cheque_id = NULL,
                $cheque_no = NULL,
                $transction_no,
                $created_by,
                $created_by_id,
                $companyId
            );

            /*$allTranRDonline=CommanTransactionsController::createAllTransactionNew($refIdRD,$branch_id,$bank_id=NULL,$bank_ac_id=NULL,$head111,$head211,$head311,$head411,$head511,$type,$sub_type,$type_id,$associate_id,$memberId,$branch_id_to=NULL,$branch_id_from=NULL,$opening_balance=NULL,$amount,$closing_balance=NULL,$rdDes,'CR',$headPaymentModeRD,$currency_code,$amount_to_id=NULL,$amount_to_name=NULL,$amount_from_id=NULL,$amount_from_name=NULL,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at,$updated_at,$createDayBook);*/

            //bank entry

            $bankonline = CommanTransactionsController::samraddhBankDaybookNew($refIdRD, $transction_bank_to, $transction_bank_ac_to, $type, $sub_type, $type_id, $createDayBook, $associate_id, $memberId, $branch_id, $opening_balance = NULL, $amount, $closing_balance = NULL, $rdDes, $rdDesDR, $rdDesCR, 'CR', $headPaymentModeRD, $currency_code, $amount_to_id = NULL, $amount_to_name = NULL, $amount_from_id = NULL, $amount_from_name = NULL, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, getSamraddhBank($online_deposit_bank_id)->bank_name, getSamraddhBankAccountId($online_deposit_bank_ac_id)->account_no, $transction_bank_to_branch = NULL, getSamraddhBankAccountId($online_deposit_bank_ac_id)->ifsc_code, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $companyId);

            //bank balence

            // $bankClosing = CommanTransactionsController::checkCreateBankClosing($transction_bank_to, $transction_bank_ac_to, $created_at, $amount, 0);
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

            $allTranRDSSB = CommanTransactionsController::createAllHeadTransaction(
                $refIdRD,
                $branch_id,
                $bank_id = NULL,
                $bank_ac_id = NULL,
                $head4rdSSB,
                $type,
                $sub_type,
                $type_id,
                $createDayBook,
                $associate_id,
                $memberId,
                $branch_id_to = NULL,
                $branch_id_from = NULL,
                $amount,
                $rdDes,
                'DR',
                $headPaymentModeRD,
                $currency_code,
                $jv_unique_id = NULL,
                $v_no,
                $ssb_account_id_from,
                $ssb_account_id_to = NULL,
                $ssb_account_tran_id_to = NULL,
                $ssb_account_tran_id_from = NULL,
                $cheque_type = NULL,
                $cheque_id = NULL,
                $cheque_no,
                $transction_no,
                $created_by,
                $created_by_id,
                $companyId
            );

            /*$allTranRDSSB=CommanTransactionsController::createAllTransactionNew($refIdRD,$branch_id,$bank_id=NULL,$bank_ac_id=NULL,$head1rdSSB,$head2rdSSB,$head3rdSSB,$head4rdSSB,$head5rdSSB,$type,$sub_type,$type_id,$associate_id,$memberId,$branch_id_to=NULL,$branch_id_from=NULL,$opening_balance=NULL,$amount,$closing_balance=NULL,$rdDes,'DR',$headPaymentModeRD,$currency_code,$amount_to_id=NULL,$amount_to_name=NULL,$amount_from_id=NULL,$amount_from_name=NULL,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at,$updated_at,$createDayBook);*/

            // $branchClosingSSB = CommanTransactionsController::checkCreateBranchClosingDr($branch_id, $created_at, $amount, 0);

        } else {

            $headPaymentModeRD = 0;

            $head3rdC = 28;

            $rdDesDR = 'Cash A/c Dr ' . $amount . '/-';

            $rdDesCR = 'To ' . $planDetail->name . '(' . $investmentAccountNoRd . ')  A/c Cr ' . $amount . '/-';

            $rdDes = 'Amount received for SSB A/C Deposit (' . $investmentAccountNoRd . ') through cash(' . getBranchCode($branch_id)->branch_code . ')';

            $rdDesMem = 'SSB A/C Deposit (' . $investmentAccountNoRd . ') through cash(' . getBranchCode($branch_id)->branch_code . ')';

            // branch cash  head entry +

            $allTranRDcash = CommanTransactionsController::createAllHeadTransaction(
                $refIdRD,
                $branch_id,
                $bank_id = NULL,
                $bank_ac_id = NULL,
                $head3rdC,
                $type,
                $sub_type,
                $type_id,
                $createDayBook,
                $associate_id,
                $memberId,
                $branch_id_to = NULL,
                $branch_id_from = NULL,
                $amount,
                $rdDes,
                'DR',
                $headPaymentModeRD,
                $currency_code,
                $jv_unique_id = NULL,
                $v_no,
                $ssb_account_id_from,
                $ssb_account_id_to = NULL,
                $ssb_account_tran_id_to = NULL,
                $ssb_account_tran_id_from = NULL,
                $cheque_type = NULL,
                $cheque_id = NULL,
                $cheque_no,
                $transction_no,
                $created_by,
                $created_by_id,
                $companyId
            );

            /*$allTranRDcash=CommanTransactionsController::createAllTransactionNew($refIdRD,$branch_id,$bank_id=NULL,$bank_ac_id=NULL,$head1rdC,$head2rdC,$head3rdC,$head4rdC,$head5rdC,$type,$sub_type,$type_id,$associate_id,$memberId,$branch_id_to=NULL,$branch_id_from=NULL,$opening_balance=NULL,$amount,$closing_balance=NULL,$rdDes,$payment_type_rd,$headPaymentModeRD,$currency_code,$amount_to_id=NULL,$amount_to_name=NULL,$amount_from_id=NULL,$amount_from_name=NULL,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at,$updated_at,$createDayBook);*/

            //Balance   entry +

            // $branchCash = CommanTransactionsController::checkCreateBranchCash($branch_id, $created_at, $amount, 0);
        }

        //branch day book entry +

        $daybookInvest = CommanTransactionsController::branchDayBookNew(
            $refIdRD,
            $branch_id,
            $type,
            $sub_type,
            $type_id,
            $createDayBook,
            $associate_id,
            $memberId,
            $branch_id_to = NULL,
            $branch_id_from = NULL,
            $amount,
            $rdDes,
            $rdDesDR,
            $rdDesCR,
            $payment_type_rd,
            $headPaymentModeRD,
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

        // Investment head entry +

        $allTranRDcash = CommanTransactionsController::createAllHeadTransaction(
            $refIdRD,
            $branch_id,
            $bank_id = $chequeDetail->deposit_bank_id ?? NULL,
            $bank_ac_id = $chequeDetail->deposit_account_id ?? NULL,
            $head4Invest,
            $type,
            $sub_type,
            $type_id,
            $createDayBook,
            $associate_id,
            $memberId,
            $branch_id_to = NULL,
            $branch_id_from = NULL,
            $amount,
            $rdDes,
            $payment_type_rd,
            $headPaymentModeRD,
            $currency_code,
            $jv_unique_id = NULL,
            $v_no,
            $ssb_account_id_from,
            $ssb_account_id_to = NULL,
            $ssb_account_tran_id_to = NULL,
            $ssb_account_tran_id_from = NULL,
            $cheque_type = NULL,
            $cheque_id = NULL,
            $cheque_no,
            $transction_no,
            $created_by,
            $created_by_id,
            $companyId
        );


        DB::commit();
    } catch (\Exception $ex) {
        DB::rollback();
        return back()->with('alert', $ex->getMessage());
    }

        // Member transaction  +


        /******** Balance   entry ***************/
    }
    public static function updateBackDateBankBalance($bank_id, $account_id, $date, $amount, $type)
    {
        $globaldate = $date;
        $entryTime = date("h:i:s");
        $entryDate = date("Y-m-d", strtotime(convertDate($globaldate)));
        $getCurrentFromBankRecord = \App\Models\SamraddhBankClosing::where('bank_id', $bank_id)->where('account_id', $account_id)->whereDate('entry_date', $entryDate)->first();
        if ($getCurrentFromBankRecord) {
            $Result = \App\Models\SamraddhBankClosing::find($getCurrentFromBankRecord->id);
            $data['balance'] = $getCurrentFromBankRecord->balance + $amount;
            if ($getCurrentFromBankRecord->closing_balance > 0) {
                $data['closing_balance'] = $getCurrentFromBankRecord->closing_balance + $amount;
            }
            $data['updated_at'] = $entryDate;
            $Result->update($data);
            $insertid = $getCurrentFromBankRecord->id;
            $getNextFromBankRecord = \App\Models\SamraddhBankClosing::where('bank_id', $bank_id)->where('account_id', $account_id)->whereDate('entry_date', '>', $entryDate)->orderby('entry_date', 'ASC')->get();
            if ($getNextFromBankRecord) {
                foreach ($getNextFromBankRecord as $key => $value) {
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
            $oldCurrentFromDateRecord = \App\Models\SamraddhBankClosing::where('bank_id', $bank_id)->where('account_id', $account_id)->whereDate('entry_date', '<', $entryDate)->orderby('entry_date', 'DESC')->first();
            if ($oldCurrentFromDateRecord) {
                $cResult = \App\Models\SamraddhBankClosing::find($oldCurrentFromDateRecord->id);
                $cdata['closing_balance'] = $oldCurrentFromDateRecord->balance;
                $cdata['updated_at'] = $entryDate;
                $cResult->update($cdata);
                $nextRecordExists = \App\Models\SamraddhBankClosing::where('bank_id', $bank_id)->where('account_id', $account_id)->whereDate('entry_date', '>', $entryDate)->orderby('entry_date', 'ASC')->get();
                $data1['bank_id'] = $oldCurrentFromDateRecord->bank_id;
                $data1['account_id'] = $oldCurrentFromDateRecord->account_id;
                $data1['opening_balance'] = $oldCurrentFromDateRecord->balance;
                $data1['balance'] = $oldCurrentFromDateRecord->balance + $amount;
                if ($nextRecordExists) {
                    $data1['closing_balance'] = $oldCurrentFromDateRecord->balance + $amount;
                    foreach ($nextRecordExists as $key => $value) {
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
                    $data1['closing_balance'] = 0;
                }
                $data1['entry_date'] = $entryDate;
                $data1['entry_time'] = $entryTime;
                $data1['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($globaldate)));
                $transcation = \App\Models\SamraddhBankClosing::create($data1);
                $insertid = $transcation->id;
            } else {
                // $data2['bank_id'] = $from_bank_id;
                $data2['bank_id'] = 0;
                $data1['account_id'] = $account_id;
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
        return true;
    }
    public static function updateBranchCashDr($branch_id, $date, $amount, $type)
    {
        $entryDate = date("Y-m-d", strtotime(convertDate($date)));
        $entryTime = date("H:i:s", strtotime(convertDate($date)));
        $currentDateRecord = \App\Models\BranchCash::where('branch_id', $branch_id)->whereDate('entry_date', $entryDate)->first();
        if ($currentDateRecord) {
            $Result = \App\Models\BranchCash::find($currentDateRecord->id);
            if ($type == 0) {
                $data['balance'] = $currentDateRecord->balance - $amount;
            } elseif ($type == 1) {
                $data['loan_balance'] = $currentDateRecord->loan_balance - $amount;
            }
            $data['updated_at'] = $date;
            $Result->update($data);
            $getNextBranchRecord = \App\Models\BranchCash::where('branch_id', $branch_id)->whereDate('entry_date', '>', $entryDate)->orderby('entry_date', 'ASC')->get();
            if ($getNextBranchRecord) {
                foreach ($getNextBranchRecord as $key => $value) {
                    $sResult = \App\Models\BranchCash::find($value->id);
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
            $oldDateRecord = \App\Models\BranchCash::where('branch_id', $branch_id)->whereDate('entry_date', '<', $entryDate)->orderby('entry_date', 'DESC')->first();
            if ($oldDateRecord) {
                $Result1 = \App\Models\BranchCash::find($oldDateRecord->id);
                $data1['closing_balance'] = $oldDateRecord->balance;
                $data1['loan_closing_balance'] = $oldDateRecord->loan_balance;
                $Result1->update($data1);
                $insertid1 = $oldDateRecord->id;
                $data1RecordExists = \App\Models\BranchCash::where('branch_id', $branch_id)->whereDate('entry_date', '>', $entryDate)->orderby('entry_date', 'ASC')->get();
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
                        $sResult = \App\Models\BranchCash::find($value->id);
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
                $transcation = \App\Models\BranchCash::create($data);
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
    public static function updateBranchCashCr($branch_id, $date, $amount, $type)
    {
        $entryDate = date("Y-m-d", strtotime(convertDate($date)));
        $entryTime = date("H:i:s", strtotime(convertDate($date)));
        $currentDateRecord = \App\Models\BranchCash::where('branch_id', $branch_id)->whereDate('entry_date', $entryDate)->first();
        if ($currentDateRecord) {
            $Result = \App\Models\BranchCash::find($currentDateRecord->id);
            if ($type == 0) {
                $data['balance'] = $currentDateRecord->balance + $amount;
            } elseif ($type == 1) {
                $data['loan_balance'] = $currentDateRecord->loan_balance + $amount;
            }
            $data['updated_at'] = $date;
            $Result->update($data);
            $getNextBranchRecord = \App\Models\BranchCash::where('branch_id', $branch_id)->whereDate('entry_date', '>', $entryDate)->orderby('entry_date', 'ASC')->get();
            if ($getNextBranchRecord) {
                foreach ($getNextBranchRecord as $key => $value) {
                    $sResult = \App\Models\BranchCash::find($value->id);
                    if ($type == 1) {
                        $sData['loan_opening_balance'] = $value->loan_closing_balance;
                        $sData['loan_balance'] = $value->loan_balance + $amount;
                        if ($value->closing_balance > 0) {
                            $sData['loan_closing_balance'] = $value->loan_closing_balance + $amount;
                        }
                    } elseif ($type == 0) {
                        $sData['opening_balance'] = $value->closing_balance;
                        $sData['balance'] = $value->balance + $amount;
                        if ($value->closing_balance > 0) {
                            $sData['closing_balance'] = $value->closing_balance + $amount;
                        }
                    }
                    $sData['updated_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($entryDate)));
                    $sResult->update($sData);
                }
            }
        } else {
            $oldDateRecord = \App\Models\BranchCash::where('branch_id', $branch_id)->whereDate('entry_date', '<', $entryDate)->orderby('entry_date', 'DESC')->first();
            if ($oldDateRecord) {
                $Result1 = \App\Models\BranchCash::find($oldDateRecord->id);
                $data1['closing_balance'] = $oldDateRecord->balance;
                $data1['loan_closing_balance'] = $oldDateRecord->loan_balance;
                $Result1->update($data1);
                $insertid1 = $oldDateRecord->id;
                $data1RecordExists = \App\Models\BranchCash::where('branch_id', $branch_id)->whereDate('entry_date', '>', $entryDate)->orderby('entry_date', 'ASC')->get();
                if ($type == 0) {
                    $data['balance'] = $oldDateRecord->balance + $amount;
                } else {
                    $data['balance'] = $oldDateRecord->balance;
                }
                if ($type == 1) {
                    $data['loan_balance'] = $oldDateRecord->loan_balance + $amount;
                } else {
                    $data['loan_balance'] = $oldDateRecord->loan_balance;
                }
                $data['opening_balance'] = $oldDateRecord->balance;
                $data['loan_opening_balance'] = $oldDateRecord->loan_balance;
                if ($data1RecordExists) {
                    if ($type == 0) {
                        $data['closing_balance'] = $oldDateRecord->balance + $amount;
                    } else {
                        $data['closing_balance'] = $oldDateRecord->balance;
                    }
                    if ($type == 1) {
                        $data['loan_closing_balance'] = $oldDateRecord->loan_balance + $amount;
                    } else {
                        $data['loan_closing_balance'] = $oldDateRecord->loan_balance;
                    }
                    foreach ($data1RecordExists as $key => $value) {
                        $sResult = \App\Models\BranchCash::find($value->id);
                        if ($type == 1) {
                            $sData['loan_opening_balance'] = $value->loan_closing_balance;
                            $sData['loan_balance'] = $value->loan_balance + $amount;
                            if ($value->closing_balance > 0) {
                                $sData['loan_closing_balance'] = $value->loan_closing_balance + $amount;
                            }
                        } elseif ($type == 0) {
                            $sData['opening_balance'] = $value->closing_balance;
                            $sData['balance'] = $value->balance + $amount;
                            if ($value->closing_balance > 0) {
                                $sData['closing_balance'] = $value->closing_balance + $amount;
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
                $transcation = \App\Models\BranchCash::create($data);
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
                $transcation = \App\Models\BranchCash::create($data);
                $insertid = $transcation->id;
            }
        }
        return true;
    }
    public static function updateBranchClosingCashCr($branch_id, $date, $amount, $type)
    {
        $entryDate = date("Y-m-d", strtotime(convertDate($date)));
        $entryTime = date("H:i:s", strtotime(convertDate($date)));
        $currentDateRecord = \App\Models\BranchClosing::where('branch_id', $branch_id)->whereDate('entry_date', $entryDate)->first();
        if ($currentDateRecord) {
            $Result = \App\Models\BranchClosing::find($currentDateRecord->id);
            if ($type == 0) {
                $data['balance'] = $currentDateRecord->balance + $amount;
            } elseif ($type == 1) {
                $data['loan_balance'] = $currentDateRecord->loan_balance + $amount;
            }
            $data['updated_at'] = $date;
            $Result->update($data);
            $getNextBranchClosingRecord = \App\Models\BranchClosing::where('branch_id', $branch_id)->whereDate('entry_date', '>', $entryDate)->orderby('entry_date', 'ASC')->get();
            if ($getNextBranchClosingRecord) {
                foreach ($getNextBranchClosingRecord as $key => $value) {
                    $sResult = \App\Models\BranchClosing::find($value->id);
                    if ($type == 1) {
                        $sData['loan_opening_balance'] = $value->loan_closing_balance;
                        $sData['loan_balance'] = $value->loan_balance + $amount;
                        if ($value->closing_balance > 0) {
                            $sData['loan_closing_balance'] = $value->loan_closing_balance + $amount;
                        }
                    } elseif ($type == 0) {
                        $sData['opening_balance'] = $value->closing_balance;
                        $sData['balance'] = $value->balance + $amount;
                        if ($value->closing_balance > 0) {
                            $sData['closing_balance'] = $value->closing_balance + $amount;
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
                    $data['balance'] = $oldDateRecord->balance + $amount;
                } else {
                    $data['balance'] = $oldDateRecord->balance;
                }
                if ($type == 1) {
                    $data['loan_balance'] = $oldDateRecord->loan_balance + $amount;
                } else {
                    $data['loan_balance'] = $oldDateRecord->loan_balance;
                }
                $data['opening_balance'] = $oldDateRecord->balance;
                $data['loan_opening_balance'] = $oldDateRecord->loan_balance;
                if ($data1RecordExists) {
                    if ($type == 0) {
                        $data['closing_balance'] = $oldDateRecord->balance + $amount;
                    } else {
                        $data['closing_balance'] = $oldDateRecord->balance;
                    }
                    if ($type == 1) {
                        $data['loan_closing_balance'] = $oldDateRecord->loan_balance + $amount;
                    } else {
                        $data['loan_closing_balance'] = $oldDateRecord->loan_balance;
                    }
                    foreach ($data1RecordExists as $key => $value) {
                        $sResult = \App\Models\BranchClosing::find($value->id);
                        if ($type == 1) {
                            $sData['loan_opening_balance'] = $value->loan_closing_balance;
                            $sData['loan_balance'] = $value->loan_balance + $amount;
                            if ($value->closing_balance > 0) {
                                $sData['loan_closing_balance'] = $value->loan_closing_balance + $amount;
                            }
                        } elseif ($type == 0) {
                            $sData['opening_balance'] = $value->closing_balance;
                            $sData['balance'] = $value->balance + $amount;
                            if ($value->closing_balance > 0) {
                                $sData['closing_balance'] = $value->closing_balance + $amount;
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
    public function getSavingBalance(Request $request)
    {
        $mId = $request->member_id;
        $date = date("Y-m-d", strtotime(convertDate($request->date)));
        $getDetails = SavingAccount::where('member_id', $mId)->select('id', 'balance', 'account_no')->first();
        if ($getDetails) {
            $record = SavingAccountTranscation::select('opening_balance')->where('account_no', $getDetails->account_no)->whereDate('created_at', '<=', $date)->orderby('created_at', 'desc')->first();
        } else {
            $record = '';
        }
        if ($record) {
            $savingBalance = $record->opening_balance;
        } else {
            $savingBalance = 0;
        }
        $return_array = compact('savingBalance');
        return json_encode($return_array);
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
        $data['ssb_account_id_to'] = $ssb_account_id_to;
        $data['ssb_account_tran_id_to'] = $ssb_account_tran_id_to;
        $data['ssb_account_tran_id_from'] = $ssb_account_tran_id_from;
        $data['created_at'] = $created_at;
        $data['updated_at'] = $updated_at;
        $transcation = \App\Models\BranchDaybook::create($data);
        return true;
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
        return true;
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
        return true;
    }
    public function updateRenewal()
    {

        if (!in_array('Update Renewal Transaction', auth()->user()->getPermissionNames()->toArray())) {
            return redirect()->route('branch.dashboard');
        }
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
                    $updateAssociateAmount = Daybook::where('id', $value1['id'])->update(array('opening_balance' => ($value1['deposit'] + ($lastRecord ? $lastRecord->opening_balance:0)), 'created_at' => date("Y-m-d " . $newTime . "", strtotime(convertDate($value1['created_at'])))));
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
                        $updateAssociateAmount = Daybook::where('id', $value1['id'])->update(array('opening_balance' => ($value1['deposit'] + ($lastRecord ? $lastRecord->opening_balance : 0 ))));
                    } elseif ($value1['withdrawal'] > 0) {
                        $updateAssociateAmount = Daybook::where('id', $value1['id'])->update(array('opening_balance' => (($lastRecord ? $lastRecord->opening_balance : 0 ) - $value1['withdrawal'])));
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
    // public function updateSsb()
    // {
    //     $data['title'] = "Update SSB Transcation";
    //     return view('templates.admin.investment_management.renewal.updatessb', $data);
    // }
    // public function updateRenewalTransaction(Request $request)
    // {
    //     $accountNumbers = $request['accountnumbers'][0];
    //     $explodeArray = explode(',', $accountNumbers);
    //     $investmentArray = array();
    //     $entryTime = date("H:i:s");
    //     foreach ($explodeArray as $value) {
    //         $dayBookRecords = Daybook::select('id','opening_balance','deposit','withdrawal','created_at')->where('account_no',$value)->whereIN('transaction_type',[2,4,16,17,18])->orderBy('created_at','asc')->get();
    //         $arraydayBookRecords = $dayBookRecords->toArray();
    //         foreach ($arraydayBookRecords as $key => $value1) {
    //             $addmiute = $key+1;
    //             $lastRecord = Daybook::select('id','opening_balance','deposit')->where('account_no',$value)->whereIN('transaction_type',[2,4,16,17,18])->where('created_at','<',$value1['created_at'])->orderBy('created_at','desc')->first();
    //             $endTime = strtotime("+".$addmiute." minutes", strtotime($entryTime));
    //             $newTime = date('H:i:s', $endTime);
    //             if($lastRecord){
    //                 $updateAssociateAmount = Daybook::where('id',$value1['id'])->update(array('opening_balance' => ($value1['deposit']+($lastRecord ? $lastRecord->opening_balance : 0 )),'created_at' => date("Y-m-d ".$newTime."", strtotime(convertDate($value1['created_at'])))));
    //             }else{
    //                 $updateAssociateAmount = Daybook::where('id',$value1['id'])->update(array('opening_balance' => $value1['deposit'],'created_at' => date("Y-m-d ".$newTime."", strtotime(convertDate($value1['created_at'])))));
    //             }
    //         }
    //     }
    //     foreach ($explodeArray as $value) {
    //         array_push($investmentArray,$value);
    //         $dayBookRecords = Daybook::select('id','opening_balance','deposit','withdrawal','created_at')->where('account_no',$value)->whereIN('transaction_type',[2,4,16,17,18])->orderBy('created_at','asc')->get();
    //         $arraydayBookRecords = $dayBookRecords->toArray();
    //         foreach ($arraydayBookRecords as $key => $value1) {
    //             $addmiute = $key+1;
    //             $lastRecord = Daybook::select('id','opening_balance','deposit','withdrawal')->where('account_no',$value)->whereIN('transaction_type',[2,4,16,17,18])->where('created_at','<',$value1['created_at'])->orderBy('created_at','desc')->first();
    //             $endTime = strtotime("+".$addmiute." minutes", strtotime($entryTime));
    //             $newTime = date('H:i:s', $endTime);
    //             if($lastRecord){
    //                 if($value1['deposit'] > 0){
    //                     $updateAssociateAmount = Daybook::where('id',$value1['id'])->update(array('opening_balance' => ($value1['deposit']+($lastRecord ? $lastRecord->opening_balance : 0 ))));
    //                 }elseif($value1['withdrawal'] > 0){
    //                     $updateAssociateAmount = Daybook::where('id',$value1['id'])->update(array('opening_balance' => (($lastRecord ? $lastRecord->opening_balance : 0 )-$value1['withdrawal'])));
    //                 }
    //             }else{
    //                 $updateAssociateAmount = Daybook::where('id',$value1['id'])->update(array('opening_balance' => $value1['deposit']));
    //             }
    //         }
    //     }
    //     $investmentString = implode(",",$investmentArray);
    //     return back()->with('success','These Investment Account Number '.$investmentString.' renewal updated');
    //     //return view('templates.admin.investment_management.renewal.updaterenewal', $data);
    // }
    // public function updateRenewalTransaction(Request $request)
    // {
    //     $accountNumbers = $request['accountnumbers'][0];
    //     $explodeArray = explode(',', $accountNumbers);
    //     $investmentArray = array();
    //     $entryTime = date("H:i:s");
    //     foreach ($explodeArray as $value) {
    //         $dayBookRecords = Daybook::select('id', 'opening_balance', 'deposit', 'withdrawal', 'created_at')->where('account_no', $value)->whereIN('transaction_type', [2, 4, 16, 17, 18])->orderBy('created_at', 'asc')->get();
    //         $arraydayBookRecords = $dayBookRecords->toArray();
    //         foreach ($arraydayBookRecords as $key => $value1) {
    //             $addmiute = $key + 1;
    //             $lastRecord = Daybook::select('id', 'opening_balance', 'deposit')->where('account_no', $value)->whereIN('transaction_type', [2, 4, 16, 17, 18])->where('created_at', '<', $value1['created_at'])->orderBy('created_at', 'desc')->first();
    //             // $sumAmount = Daybook::select('id','opening_balance','deposit')->where('account_no',$value)->whereIN('transaction_type',[2,4,16,17,18])->where('created_at','<',$value1['created_at'])->orderBy('created_at','desc')->sum('deposit');
    //             $endTime = strtotime("+" . $addmiute . " minutes", strtotime($entryTime));
    //             $newTime = date('H:i:s', $endTime);
    //             if ($lastRecord) {
    //                 $updateAssociateAmount = Daybook::where('id', $value1['id'])->update(array('opening_balance' => ($value1['deposit'] + ($lastRecord ? $lastRecord->opening_balance : 0 )), 'created_at' => date("Y-m-d " . $newTime . "", strtotime(convertDate($value1['created_at'])))));
    //                 $updateInvestmentCurrentBalance = Memberinvestments::where('account_number', $value)->update(['current_balance' => $value1['deposit'] + ($lastRecord ? $lastRecord->opening_balance : 0 )]);
    //             } else {
    //                 $updateAssociateAmount = Daybook::where('id', $value1['id'])->update(array('opening_balance' => $value1['deposit'], 'created_at' => date("Y-m-d " . $newTime . "", strtotime(convertDate($value1['created_at'])))));
    //             }
    //         }
    //     }
    //     foreach ($explodeArray as $value) {
    //         array_push($investmentArray, $value);
    //         $dayBookRecords = Daybook::select('id', 'opening_balance', 'deposit', 'withdrawal', 'created_at')->where('account_no', $value)->whereIN('transaction_type', [2, 4, 16, 17, 18])->orderBy('created_at', 'asc')->get();
    //         $arraydayBookRecords = $dayBookRecords->toArray();
    //         foreach ($arraydayBookRecords as $key => $value1) {
    //             $addmiute = $key + 1;
    //             $lastRecord = Daybook::select('id', 'opening_balance', 'deposit', 'withdrawal')->where('account_no', $value)->whereIN('transaction_type', [2, 4, 16, 17, 18])->where('created_at', '<', $value1['created_at'])->orderBy('created_at', 'desc')->first();
    //             $endTime = strtotime("+" . $addmiute . " minutes", strtotime($entryTime));
    //             $newTime = date('H:i:s', $endTime);
    //             if ($lastRecord) {
    //                 if ($value1['deposit'] > 0) {
    //                     $updateAssociateAmount = Daybook::where('id', $value1['id'])->update(array('opening_balance' => ($value1['deposit'] + ($lastRecord ? $lastRecord->opening_balance : 0 ))));
    //                     $updateInvestmentCurrentBalance = Memberinvestments::where('account_number', $value)->update(['current_balance' => $value1['deposit'] + ($lastRecord ? $lastRecord->opening_balance : 0 )]);
    //                 } elseif ($value1['withdrawal'] > 0) {
    //                     $updateAssociateAmount = Daybook::where('id', $value1['id'])->update(array('opening_balance' => (($lastRecord ? $lastRecord->opening_balance : 0 ) - $value1['withdrawal'])));
    //                     $updateInvestmentCurrentBalance = Memberinvestments::where('account_number', $value)->update(['current_balance' => ($lastRecord ? $lastRecord->opening_balance : 0 ) - $value1['withdrawal']]);
    //                 }
    //             } else {
    //                 $updateAssociateAmount = Daybook::where('id', $value1['id'])->update(array('opening_balance' => $value1['deposit']));
    //             }
    //         }
    //     }
    //     $investmentString = implode(",", $investmentArray);
    //     return back()->with('success', 'These Investment Account Number ' . $investmentString . ' renewal updated');
    //     //return view('templates.admin.investment_management.renewal.updaterenewal', $data);
    // }
    public function updateSsbTransaction(Request $request)
    {
        $accountNumbers = $request['accountnumbers'][0];
        $explodeArray = explode(',', $accountNumbers);
        $investmentArray = array();
        $entryTime = date("H:i:s");
        foreach ($explodeArray as $value) {
            $dayBookRecords = \App\Models\SavingAccountTranscation::select('id', 'opening_balance', 'deposit', 'withdrawal', 'created_at') /*->where('saving_account_id',$savingAccountId)*/->where('account_no', $value)->orderBy('created_at', 'asc')->get();
            $arraydayBookRecords = $dayBookRecords->toArray();
            foreach ($arraydayBookRecords as $key => $value1) {
                $addmiute = $key + 1;
                $lastRecord = \App\Models\SavingAccountTranscation::select('id', 'opening_balance', 'deposit') /*->where('saving_account_id',$savingAccountId)*/->where('account_no', $value)->where('created_at', '<', $value1['created_at'])->orderBy('created_at', 'desc')->first();
                $endTime = strtotime("+" . $addmiute . " minutes", strtotime($entryTime));
                $newTime = date('H:i:s', $endTime);
                if ($lastRecord) {
                    $updateAssociateAmount = \App\Models\SavingAccountTranscation::where('id', $value1['id'])->update(array('created_at' => date("Y-m-d " . $newTime . "", strtotime(convertDate($value1['created_at'])))));
                } else {
                    $updateAssociateAmount = \App\Models\SavingAccountTranscation::where('id', $value1['id'])->update(array('created_at' => date("Y-m-d " . $newTime . "", strtotime(convertDate($value1['created_at'])))));
                }
            }
        }
        foreach ($explodeArray as $value) {
            array_push($investmentArray, $value);
            $dayBookRecords = \App\Models\SavingAccountTranscation::select('id', 'opening_balance', 'deposit', 'withdrawal', 'created_at') /*->where('saving_account_id',$savingAccountId)*/->where('account_no', $value)->orderBy('created_at', 'asc')->get();
            $arraydayBookRecords = $dayBookRecords->toArray();
            foreach ($arraydayBookRecords as $key => $value1) {
                $lastRecord = \App\Models\SavingAccountTranscation::select('id', 'opening_balance', 'deposit', 'withdrawal') /*->where('saving_account_id',$savingAccountId)*/->where('account_no', $value)->where('created_at', '<', $value1['created_at'])->orderBy('created_at', 'desc')->first();
                if ($lastRecord) {
                    if ($value1['deposit'] > 0) {
                        $updateAssociateAmount = \App\Models\SavingAccountTranscation::where('id', $value1['id'])->update(array('opening_balance' => ($value1['deposit'] + ($lastRecord ? $lastRecord->opening_balance : 0 ))));
                    } elseif ($value1['withdrawal'] > 0) {
                        $updateAssociateAmount = \App\Models\SavingAccountTranscation::where('id', $value1['id'])->update(array('opening_balance' => (($lastRecord ? $lastRecord->opening_balance : 0 ) - $value1['withdrawal'])));
                    }
                } else {
                    $updateAssociateAmount = \App\Models\SavingAccountTranscation::where('id', $value1['id'])->update(array('opening_balance' => $value1['deposit']));
                }
            }
        }
        $investmentString = implode(",", $investmentArray);
        return back()->with('success', 'These SSB Account Number ' . $investmentString . ' renewal updated');
        //return view('templates.admin.investment_management.renewal.updaterenewal', $data);
    }
    public function updateAllRenewalTransactionView()
    {
        $data['title'] = "Update Renewal Transcation";
        return view('templates.admin.investment_management.renewal.updaterenewalview', $data);
    }
    public function updateAllRenewalTransaction()
    {
        //$accountNumbers = $request['accountnumbers'][0];
        //$explodeArray = explode(',', $accountNumbers);
        $investmentArray = array();
        $entryTime = date("H:i:s");
        $getInvestments = Memberinvestments::select('account_number')->where('plan_id', '!=', 1) /*->whereIN('id',[39])*/->offset(2050)->limit(50)->get();
        foreach ($getInvestments as $value) {
            $dayBookRecords = Daybook::select('id', 'opening_balance', 'deposit', 'created_at')->where('account_no', $value->account_number)->whereIN('transaction_type', [2, 4, 16, 17, 18])->orderBy('created_at', 'asc')->get();
            $arraydayBookRecords = $dayBookRecords->toArray();
            foreach ($arraydayBookRecords as $key => $value1) {
                $addmiute = $key + 1;
                $lastRecord = Daybook::select('id', 'opening_balance', 'deposit')->where('account_no', $value->account_number)->whereIN('transaction_type', [2, 4, 16, 17, 18])->where('created_at', '<', $value1['created_at'])->orderBy('created_at', 'desc')->first();
                $endTime = strtotime("+" . $addmiute . " minutes", strtotime($entryTime));
                $newTime = date('H:i:s', $endTime);
                if ($lastRecord) {
                    $updateAssociateAmount = Daybook::where('id', $value1['id'])->update(array('opening_balance' => ($value1['deposit'] + ($lastRecord ? $lastRecord->opening_balance : 0 )), 'created_at' => date("Y-m-d " . $newTime . "", strtotime(convertDate($value1['created_at'])))));
                } else {
                    $updateAssociateAmount = Daybook::where('id', $value1['id'])->update(array('opening_balance' => $value1['deposit'], 'created_at' => date("Y-m-d " . $newTime . "", strtotime(convertDate($value1['created_at'])))));
                }
            }
        }
        foreach ($getInvestments as $value) {
            array_push($investmentArray, $value->account_number);
            $dayBookRecords = Daybook::select('id', 'opening_balance', 'deposit', 'withdrawal', 'created_at')->where('account_no', $value->account_number)->whereIN('transaction_type', [2, 4, 16, 17, 18])->orderBy('created_at', 'asc')->get();
            $arraydayBookRecords = $dayBookRecords->toArray();
            foreach ($arraydayBookRecords as $key => $value1) {
                $addmiute = $key + 1;
                $lastRecord = Daybook::select('id', 'opening_balance', 'withdrawal', 'deposit')->where('account_no', $value->account_number)->whereIN('transaction_type', [2, 4, 16, 17, 18])->where('created_at', '<', $value1['created_at'])->orderBy('created_at', 'desc')->first();
                $endTime = strtotime("+" . $addmiute . " minutes", strtotime($entryTime));
                $newTime = date('H:i:s', $endTime);
                if ($lastRecord) {
                    //$updateAssociateAmount = Daybook::where('id',$value1['id'])->update(array('opening_balance' => ($value1['deposit']+($lastRecord ? $lastRecord->opening_balance : 0 ))));
                    if ($value1['deposit'] > 0) {
                        $updateAssociateAmount = Daybook::where('id', $value1['id'])->update(array('opening_balance' => ($value1['deposit'] + ($lastRecord ? $lastRecord->opening_balance : 0 ))));
                    } elseif ($value1['withdrawal'] > 0) {
                        $updateAssociateAmount = Daybook::where('id', $value1['id'])->update(array('opening_balance' => (($lastRecord ? $lastRecord->opening_balance : 0 ) - $value1['withdrawal'])));
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
    public function updateAllSsbTransaction()
    {
        //$accountNumbers = $request['accountnumbers'][0];
        //$explodeArray = explode(',', $accountNumbers);
        $investmentArray = array();
        $entryTime = date("H:i:s");
        $getSavingAccounts = SavingAccount::select('id', 'account_no') /*->whereIN('id',[49])->offset(0)->limit(2)*/->get();
        foreach ($getSavingAccounts as $value) {
            $dayBookRecords = \App\Models\SavingAccountTranscation::select('id', 'opening_balance', 'deposit', 'withdrawal', 'created_at')->where('saving_account_id', $value->id)->where('is_deleted', 0)->where('account_no', $value->account_no)->orderBy('id', 'asc')->get();
            $arraydayBookRecords = $dayBookRecords->toArray();
            foreach ($arraydayBookRecords as $key => $value1) {
                //dd($value->id,$value1['id'],$value->account_no);
                $addmiute = $key + 1;
                $lastRecord = \App\Models\SavingAccountTranscation::select('id', 'opening_balance', 'deposit')->where('saving_account_id', $value->id)->where('account_no', $value->account_no)->where('id', '<', $value1['id'])->orderBy('id', 'desc')->first();
                $latestRecord = \App\Models\SavingAccountTranscation::select('id', 'opening_balance', 'deposit')->where('is_deleted', 0)->where('saving_account_id', $value->id)->orderBy('id', 'asc')->first();
                $endTime = strtotime("+" . $addmiute . " minutes", strtotime($entryTime));
                $newTime = date('H:i:s', $endTime);
                if ($lastRecord) {
                    if ($latestRecord->created_at < $lastRecord->created_at) {
                        if ($lastRecord) {
                            if ($value1['deposit'] > 0) {
                                $updateAssociateAmount = \App\Models\SavingAccountTranscation::where('id', $value1['id'])->update(array('opening_balance' => ($value1['deposit'] + ($lastRecord ? $lastRecord->opening_balance : 0 ))));
                            } elseif ($value1['withdrawal'] > 0) {
                                $updateAssociateAmount = \App\Models\SavingAccountTranscation::where('id', $value1['id'])->update(array('opening_balance' => (($lastRecord ? $lastRecord->opening_balance : 0 ) - $value1['withdrawal'])));
                            }
                        } else {
                            $updateAssociateAmount = \App\Models\SavingAccountTranscation::where('id', $value1['id'])->update(array('opening_balance' => $value1['deposit']));
                        }
                    }
                } else {
                    if ($lastRecord) {
                        $updateAssociateAmount = \App\Models\SavingAccountTranscation::where('id', $value1['id'])->update(array('created_at' => date("Y-m-d " . $newTime . "", strtotime(convertDate($value1['created_at'])))));
                        $updateAssociateAmount = \App\Models\SavingAccount::where('id', $value->id)->update(['balance' => $latestRecord->opening_balance]);
                    } else {
                        $updateAssociateAmount = \App\Models\SavingAccountTranscation::where('id', $value1['id'])->update(array('created_at' => date("Y-m-d " . $newTime . "", strtotime(convertDate($value1['created_at'])))));
                    }
                }
            }
        }
        foreach ($getSavingAccounts as $value) {
            array_push($investmentArray, $value->account_no);
            $dayBookRecords = \App\Models\SavingAccountTranscation::select('id', 'opening_balance', 'deposit', 'withdrawal', 'created_at')->where('saving_account_id', $value->id)->where('is_deleted', 0)->where('account_no', $value->account_no)->orderBy('id', 'asc')->get();
            $arraydayBookRecords = $dayBookRecords->toArray();
            foreach ($arraydayBookRecords as $key => $value1) {
                $lastRecord = \App\Models\SavingAccountTranscation::select('id', 'opening_balance', 'deposit', 'withdrawal')->where('is_deleted', 0)->where('saving_account_id', $value->id)->where('account_no', $value->account_no)->where('id', '<', $value1['id'])->orderBy('id', 'desc')->first();
                if ($lastRecord) {
                    if ($value1['deposit'] > 0) {
                        $updateAssociateAmount = \App\Models\SavingAccountTranscation::where('id', $value1['id'])->update(array('opening_balance' => ($value1['deposit'] + ($lastRecord ? $lastRecord->opening_balance : 0 ))));
                    } else {
                        $updateAssociateAmount = \App\Models\SavingAccountTranscation::where('id', $value1['id'])->update(array('opening_balance' => (($lastRecord ? $lastRecord->opening_balance : 0 ) - $value1['withdrawal'])));
                    }
                } else {
                    $updateAssociateAmount = \App\Models\SavingAccountTranscation::where('id', $value1['id'])->update(array('opening_balance' => $value1['deposit']));
                }
            }
        }
        $investmentString = implode(",", $investmentArray);
        return back()->with('success', 'These SSB Account Number ' . $investmentString . ' renewal updated');
        //return view('templates.admin.investment_management.renewal.updaterenewal', $data);
    }
    //  public function updateAllSsbTransaction()
    // {
    //     //$accountNumbers = $request['accountnumbers'][0];
    //     //$explodeArray = explode(',', $accountNumbers);
    //     $investmentArray = array();
    //     $entryTime = date("H:i:s");
    //     $getSavingAccounts = SavingAccount::select('id','account_no')->whereIN('id',[49])/*->offset(0)->limit(2)*/->get();
    //     foreach ($getSavingAccounts as $value) {
    //         $dayBookRecords = \App\Models\SavingAccountTranscation::select('id','opening_balance','deposit','withdrawal','created_at')->where('saving_account_id',$value->id)->where('is_deleted',0)->where('account_no',$value->account_no)->orderBy('created_at','asc')->get();
    //         $arraydayBookRecords = $dayBookRecords->toArray();
    //         foreach ($arraydayBookRecords as $key => $value1) {
    //             $addmiute = $key+1;
    //             $lastRecord = \App\Models\SavingAccountTranscation::select('id','opening_balance','deposit')->where('saving_account_id',$value->id)->where('account_no',$value->account_no)->where('created_at','<',$value1['created_at'])->orderBy('created_at','desc')->first();
    //             $latestRecord = \App\Models\SavingAccountTranscation::select('id','opening_balance','deposit')->where('is_deleted',0)->where('saving_account_id',$value->id)->orderBy('created_at','desc')->first();
    //             $endTime = strtotime("+".$addmiute." minutes", strtotime($entryTime));
    //             $newTime = date('H:i:s', $endTime);
    //             if($lastRecord){
    //                 $updateAssociateAmount = \App\Models\SavingAccountTranscation::where('id',$value1['id'])->update(array('created_at' => date("Y-m-d ".$newTime."", strtotime(convertDate($value1['created_at'])))));
    //                 $updateAssociateAmount = \App\Models\SavingAccount::where('id',$value->id)->update(['balance'=>$latestRecord->opening_balance]);
    //             }else{
    //                 $updateAssociateAmount = \App\Models\SavingAccountTranscation::where('id',$value1['id'])->update(array('created_at' => date("Y-m-d ".$newTime."", strtotime(convertDate($value1['created_at'])))));
    //             }
    //         }
    //     }
    //     foreach ($getSavingAccounts as $value) {
    //         array_push($investmentArray,$value->account_no);
    //         $dayBookRecords = \App\Models\SavingAccountTranscation::select('id','opening_balance','deposit','withdrawal','created_at')->where('saving_account_id',$value->id)->where('is_deleted',0)->where('account_no',$value->account_no)->orderBy('created_at','asc')->get();
    //         $arraydayBookRecords = $dayBookRecords->toArray();
    //         foreach ($arraydayBookRecords as $key => $value1) {
    //             $lastRecord = \App\Models\SavingAccountTranscation::select('id','opening_balance','deposit','withdrawal')->where('is_deleted',0)->where('saving_account_id',$value->id)->where('account_no',$value->account_no)->where('created_at','<',$value1['created_at'])->orderBy('created_at','desc')->first();
    //             if($lastRecord){
    //                 if($value1['deposit'] > 0){
    //                     $updateAssociateAmount = \App\Models\SavingAccountTranscation::where('id',$value1['id'])->update(array('opening_balance' => ($value1['deposit']+($lastRecord ? $lastRecord->opening_balance : 0 ))));
    //                 }elseif($value1['withdrawal'] > 0){
    //                     $updateAssociateAmount = \App\Models\SavingAccountTranscation::where('id',$value1['id'])->update(array('opening_balance' => (($lastRecord ? $lastRecord->opening_balance : 0 )-$value1['withdrawal'])));
    //                 }
    //             }else{
    //                 $updateAssociateAmount = \App\Models\SavingAccountTranscation::where('id',$value1['id'])->update(array('opening_balance' => $value1['deposit']));
    //             }
    //         }
    //     }
    //     $investmentString = implode(",",$investmentArray);
    //     return back()->with('success','These SSB Account Number '.$investmentString.' renewal updated');
    //     //return view('templates.admin.investment_management.renewal.updaterenewal', $data);
    // }
    public function viewssbTransactionreceipt($id)
    {
        $data['title'] = 'Transaction Detail';
        $data['data'] = SavingAccountTranscation::where('id', $id)->first();
        // print_r($data['tDetails'] );die;
        $mId = SavingAccount::select('member_investments_id', 'member_id', 'account_no')->where('id', $data['data']->saving_account_id)->first();
        $aId = Memberinvestments::select('associate_id')->where('id', $mId->member_investments_id)->first();
        $data['data']['member_id'] = $mId->member_id;
        if ($data['data']->associate_id > 0) {
            $data['data']['associate_id'] = $data['data']->associate_id;
        } else {
            $data['data']['associate_id'] = $aId->associate_id;
        }
        $data['data']['account_no'] = $mId->account_no;
        $data['data']['type'] = 0;
        return view('templates.admin.investment_management.receipt', $data);
    }
    public function renewal_receipt(Request $request, $id)
    {
        $data['title'] = "Transaction Receipt";
        $data['data'] = \App\Models\Daybook::with([
            'dbranch',
            'member',
            'associateMember',
            'investment' => function ($query) {
                $query->select('id', 'plan_id', 'account_number', 'tenure');
            }
        ])->where('id', $id)->where('payment_type', 'CR')->first();
        return view('templates.admin.investment_management.receipt', $data);
    }


    public function createBulkArray($branchDaybookTransactions, $allHeadTransactions, $cashAllHeadTransactions)
    {

        $branchDayBookData = \App\Models\BranchDaybook::insert($branchDaybookTransactions);
        $allHeadTransactions = \App\Models\AllHeadTransaction::insert($allHeadTransactions);
        $cashAllHeadTransactions = \App\Models\AllHeadTransaction::insert($cashAllHeadTransactions);

        if ($cashAllHeadTransactions && $allHeadTransactions && $cashAllHeadTransactions) {
            return true;
        } else {
            return 'Error';
        }
    }
    public function renewlimit(Request $request){
        $response = ACommanController::renewlimit($request,$this->repository);
        return response()->json($response);
    }

    public function send_message($allContactNumbers, $allAccountNumbers, $rAmounts, $encodeRequests, $encodebranchCode, $encodebranchName, $ssb, $totalAmount, $amount,$ren_dates)
    {

        $baseurl = URL::to('/');
        parse_str($allContactNumbers, $allContactNumbers);
        parse_str($amount, $amount);
        parse_str($allAccountNumbers, $allAccountNumbers);
        parse_str($rAmounts, $rAmounts);



        if (!empty($allAccountNumbers)) {

            foreach ($allAccountNumbers as $key => $allAccountNumber) {
                $currentBalances = InvestmentBalance::select('totalBalance')
                ->where('account_number', $allAccountNumber)
                ->first();
                $investmentDetail = $this->getInvestmentPlanDetail($rAmounts[$key]);

                $investmentCurrentBalance = getInvestmentCurrentBalance($investmentDetail->id, $investmentDetail->account_number);
                $rAmount = $investmentCurrentBalance;
                $contactNumber[] = str_replace('"', '', $allContactNumbers[$key]);

                $text = 'Dear Member, Your A/C ' . $allAccountNumber . ' has been Credited on ' . $ren_dates . ' With Rs. ' . round($amount[$key], 2) . ' Cur Bal: ' . round($currentBalances->totalBalance, 2) . '. Thanks Have a nice day';

                 // $text = 'Dear Member, Your A/C ' . $accountNumber . ' has been Credited on ' . date("d M Y", strtotime(str_replace('/', '-', $request['renewal_date']))) . ' With Rs. ' . round($request['amount'][$key], 2) . ' Cur Bal: ' . round($rAmount, 2) . '. Thanks Have a nice day';

                $templateId = 1207161726461603982;

                $sendToMember = new Sms();
                $response = $sendToMember->sendSms($contactNumber, $text, $templateId);

                if ($response != true) {
                    return Response::json(['view' => 'Something Went wrong', 'msg_type' => 'error']);
                }
            }
        }


        $redirect_url = $baseurl . '/branch/renew/recipt/new/' . $encodeRequests . '/' . $encodebranchCode . '/' . $encodebranchName . '/' . $ssb . '/' . $totalAmount . '';
        return redirect($redirect_url);





        return redirect($redirect_url);
    }
}
// branch
