<?php
namespace App\Http\Controllers\Admin;

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
use URL;
use App\Services\Sms;
use App\Interfaces\RepositoryInterface;
use App\Models\CommissionLeaserMonthly;
use App\Models\AllHeadTransaction;
use App\Models\BranchDaybook;
use App\Models\CorrectionRequests;
use App\Models\Member;
use App\Models\ReceivedCheque;
use App\Models\TranscationLog;
use App\Models\Transcation;
use App\Models\Daybook;
use App\Models\SavingAccount;
use App\Models\SavingAccountTranscation;
use App\Models\TransactionReferences;
use App\Models\Investmentplantransactions;
use App\Models\TransactionType;
use App\Models\Memberinvestments;
use App\Http\Controllers\Branch\CommanTransactionsController;
use Illuminate\Support\Facades\Schema;
use App\Http\Controllers\Admin\CommanController;
use App\Scopes\ActiveScope;

class RenewalController extends Controller
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
     * Display renew form.
     *
     * @return \Illuminate\Http\Response
     */
    /**
     * this delete fucntion is modify by Sourab on 13-10-2023
     * for make it work currectly for renwal transaction removing
     * from each and every condication.
     */
    public function delete($id, $correctionid, $code)
    {
        Schema::disableForeignKeyConstraints();
        DB::beginTransaction();
        try {
            $correctiondetails = $this->repository->getCorrectionRequestsById($correctionid)->first();
            /** get day book table details */
            if ($code == 'S') {
                $dayBook = $this->repository->getSavingAccountTranscationById($id)->first();
                $savingAccount = $this->repository->getAllSavingAccount()->where('account_no', $dayBook->account_no)->first(['member_investments_id', 'member_id']);
                $investmentId = $savingAccount->member_investments_id;
                $transactiontypeId = 21;
                $member_id = $savingAccount->member_id;
            } else {
                $dayBook = $this->repository->getDaybookById($id)->first();
                $investmentId = $dayBook->investment_id;
                $transactiontypeId = 10;
                $member_id = $dayBook->member_id;
            }
            $payment_mode = $dayBook->payment_mode;
            $dAmount = $dayBook->deposit;
            $account_no = $dayBook->account_no;
            $createdAt = $dayBook->created_at;
            $associateId = ($dayBook->associate_id != 1 && $dayBook->associate_id != 0) ? $dayBook->associate_id : $dayBook->member_id ?? 0;
            $deleted = ['is_deleted' => '1'];
            $dayBookRefId = ($dayBook->daybook_ref_id != null) ? $dayBook->daybook_ref_id : $dayBook->transaction_id ?? 0;
            /** for investment renewal only */
            $transactionType = $this->repository->getTransactionTypeById($transactiontypeId)->select('type', 'sub_type', 'id')->first()->toArray();
            $subType = $transactionType['sub_type'];
            $type = $transactionType['type'];
            /** get commission deatils for condication  that if ledger is already created for renewal or not. */
            $comMonth = date("m", strtotime($createdAt));
            $comYear = date("Y", strtotime($createdAt));
            $countCommLe = $this->repository->getAllCommissionLeaserMonthly()->where('year',$comYear)->where('month',$comMonth)->count();
            if ($countCommLe > 0) {
                $msg = 'Commission ledger created, so you can not delete entry';
                $msgtype = 'alert';
            } else {
                /** payament mode by saving Account then data delete from belllow tables - all_head_transaction,branch_daybook,saving_account_transaction. */
                $code == 'S' ? $this->deleteSavingAccountTransaction($code, $correctiondetails, $payment_mode, $associateId, $dAmount, $deleted) : null;
                $this->deleteSamraddhBankDayBookEntry($type, $subType, $investmentId, $id, $deleted, $dayBookRefId, $payment_mode);
                /**  check if transaction renewal from investment then below table row's will removed - all head transaction,branch day book,samraddh bank daybook */
                $dayBookRefId > 0 && $dayBookRefId !== null && $payment_mode == '4' ? AllHeadTransaction::whereDaybookRefId($dayBookRefId)->exists() ? AllHeadTransaction::whereDaybookRefId($dayBookRefId)->update($deleted) : null : null;

                $dayBookRefId > 0 && $dayBookRefId !== null && $payment_mode == '4' ? $this->repository->getAllSavingAccountTranscation()->whereDaybookRefId($dayBookRefId)->exists() ? $this->repository->getAllSavingAccountTranscation()->whereDaybookRefId($dayBookRefId)->update($deleted) : null : null;

                $this->deleteAllHeadTransactionEntry($type, $subType, $investmentId, $id, $dayBookRefId, $deleted);
                /** Delete Old Commission  start */
                // CommanController::commissionDeleteNewUpdate($id, $investmentId);
                CommanController::commissionDeleteUpdateNew($id, $investmentId);
                /** member investment balance update */
                $this->deleteMemberInvestmentEntry($investmentId, $dAmount);
                /** update daybook record */
                $code == 'S' ? $this->repository->getAllDaybook()->where('investment_id', $investmentId)->where('account_no', $account_no)->where('member_id', $member_id)->update(['is_deleted' => 1]) : $dayBook->update(['is_deleted' => 1]);
                $this->deleteCorrectionRequestEntry($id, $dayBook, $correctionid);
                /** commiting cade after running complete code currectly */
                $msg = 'Transaction deleted successfully!';
                $msgtype = 'success';
            }
            // dd("Transaction deleted successfully");
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            $msg = 'error - ' . $ex->getMessage() . ' at line - ' . $ex->getLine();
            $msgtype = 'alert';
        }
        return back()->with($msgtype, $msg);
    }
    public function deleteSavingAccountTransaction($code, $correctiondetails, $payment_mode, $associateId, $dAmount, $deleted)
    {
        if ($code == 'S') {
            $getAssociateAmount = $this->repository->getSavingAccountById($correctiondetails->account_id)->select('balance', 'id')->whereId($correctiondetails->account_id)->first();
            $updatedAmount = $getAssociateAmount->balance + $dAmount;
            $this->repository->getSavingAccountById($correctiondetails->account_id)->update(['balance' => $updatedAmount]);
        } else {
            $getAssociateAmount = $this->repository->getAllSavingAccount()->select('balance', 'id')->where('member_id', $associateId)->first();
            $updatedAmount = $getAssociateAmount->balance + $dAmount;
            $this->repository->getAllSavingAccount()->whereMemberId($associateId)->update(['balance' => $updatedAmount]);
        }
        /** check that transaction is from ssb or not if  transaction from ssb saving account transaction table data will be remove.*/
        $getTransactionData = $this->repository->getAllSavingAccountTranscation()->whereSavingAccountId($getAssociateAmount->id)->where('is_deleted', '0')->whereStatus('1') /*->whereAccountNo($dayBookreference_no)->whereDaybookRefId($dayBookRefId)*/->wherePaymentMode($payment_mode)->when($associateId > 1, function ($q) use ($associateId) {
            $q->whereAssociateId($associateId);
        })->latest()->first();
        // dd($getTransactionData);
        if ($getTransactionData) {
            $getTransactionData->update($deleted);
        }
    }
    public function deleteSamraddhBankDayBookEntry($type, $subType, $investmentId, $id, $deleted, $dayBookRefId, $payment_mode)
    {
        $bankDay_book = $this->repository->getAllSamraddhBankDaybook()->whereType($type)->whereSubType($subType)->whereTypeId($investmentId)->whereTypeTransactionId($id);
        if ($bankDay_book->exists()) {
            $bankDay_book->update($deleted);
        }
        if ($payment_mode == 1) {
            $cheque = $this->repository->getAllSamraddhBankDaybook()->whereType($type)->whereSubType($subType)->whereTypeId($investmentId)->whereTypeTransactionId($id) /*whereDaybookRefId($dayBookRefId)*/->value('cheque_no');
            if ($cheque) {
                $this->repository->getAllReceivedCheque()->whereChequeNo($cheque)->update(['status' => '2']);
            }
        }
    }
    public function deleteAllHeadTransactionEntry($type, $subType, $investmentId, $id, $dayBookRefId, $deleted)
    {
        $getAllHeadTransactionData = $this->repository->getAllAllHeadTransaction()/*->whereType($type)*/->whereSubType($subType)->whereTypeId($investmentId)->whereTypeTransactionId($id)->count('id');
        if ($getAllHeadTransactionData > 0) {
            $this->repository->getAllBranchDaybook()/*->whereType($type)*/->whereSubType($subType)->whereTypeId($investmentId)->whereTypeTransactionId($id)->when($dayBookRefId != 0, function ($q) use ($dayBookRefId) {
                $q->whereDaybookRefId($dayBookRefId);
            })->update($deleted);
            $this->repository->getAllAllHeadTransaction()/*->whereType($type)*/->whereSubType($subType)->whereTypeId($investmentId)->whereTypeTransactionId($id)->when($dayBookRefId != 0, function ($q) use ($dayBookRefId) {
                $q->whereDaybookRefId($dayBookRefId);
            })->update($deleted);
        }

    }
    public function deleteMemberInvestmentEntry($investmentId, $dAmount)
    {
        $getInvestAmount = $this->repository->getMemberinvestmentsById($investmentId)->first();
        if ($getInvestAmount) {
            $updatedInvestAmount = ($getInvestAmount->current_balance ?? 0) - $dAmount;
            $getInvestAmount->update([
                'renewal_correction_request' => '0',
                'current_balance' => $updatedInvestAmount,
            ]);
        }
    }
    public function deleteCorrectionRequestEntry($id, $dayBook, $correctionid)
    {
        $log['correction_type'] = 3;
        $log['correction_type_id'] = $id;
        $log['correction_log'] = json_encode($dayBook);
        $this->repository->createCorrectionLog($log);
        $this->repository->getCorrectionRequestsById($correctionid)->update(['status' => '1']);
    }
    /**
     * Display renew form.
     *
     * @return \Illuminate\Http\Response
     */
    public function renew()
    {
        if (check_my_permission(Auth::user()->id, "21") != "1") {
            return redirect()->route('admin.dashboard');
        }
        $data['title'] = "Renewal";
        return view('templates.admin.investment_management.renewal.renew', $data);
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
        $accountNumber = $request->account_number;
        $renewalDate = date('Y-m-d', strtotime(convertDate($request->renewalDate)));
        $planId = $request->renewPlanId;
        $plan_cat = array();
        $plan_cat = [
            0 =>'D',
            1 =>'M',
            2 =>'S'
        ];
        $getPlanDetails = \App\Models\Plans::where('id', $planId)->withoutGlobalScope(ActiveScope::class);
        $getPlanDetails = $getPlanDetails->first();
        $category = $getPlanDetails->plan_category_code;
        $allId = \App\Models\Plans::where('plan_category_code', $category)->withoutGlobalScope(ActiveScope::class)->pluck('id');
        $type = '';
        if ($getPlanDetails->plan_category_code == 'S' || $getPlanDetails->plan_category_code == 'D' || $getPlanDetails->plan_category_code == 'M') {
            // D stand's for Daily Renewal
            // M stand's for RD/FRD Renewal
            // S stand's for Deposite Saving Account
            $investment = Memberinvestments::with(['member', 'associateMember', 'ssb', 'demandadvice','plan:id,plan_category_code'])
                ->where([
                    'account_number' => $accountNumber,
                    'investment_correction_request' => 0,
                    'renewal_correction_request' => 0,
                    'is_mature' => 1,
                    'company_id' => (int) $request->company_id,
                    'branch_id' => (int) $request->branch_id
                ]);
        }
        $cheques = "";
        if ($request->chequee) {
            $cheques = ReceivedCheque::where([
                ['company_id', $request->company_id],
                ['branch_id', $request->branch_id],
                ['status', 2],
            ])->get(['id', 'cheque_no']);
        }
        if (Auth::user()->branch_id > 0) {
            $investment = $investment->where('branch_id', Auth::user()->branch_id);
        }
        $investment = $investment->get();
        if (count($investment) > 0) {
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
        if ((count($investment) > 0 ) && ($investment[0]['plan']['plan_category_code'] != $plan_cat[$request->renewPlan])) {
            $resCount = 0;
            $msg = false;
            $return_array = compact( 'resCount','msg');
            return json_encode($return_array);
        }
        // as per discussed with sachin sir demand advice entry's are deleted for day book ref id 2513665 on 14-03-24 and renewal payment is done
        if ((count($investment) > 0) && (!isset($investment[0]['demandadvice']) || ($investment[0]['demandadvice']['is_deleted'] == 1))) {
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
        // as per discussed with sachin sir demand advice entry's are deleted for day book ref id 2513665 on 14-03-24 and renewal payment is done
        } else if (!empty($investment[0]['demandadvice']) && ($investment[0]['demandadvice']['is_deleted'] == 0)) {
            $type = 'demand-advice';
            $amount = '';
            $minutes = 6;
        } else {
            $amount = '';
            $minutes = 6;
            $msg = false;
        }
        $resCount = count($investment);
        $return_array = compact('investment', 'resCount', 'amount', 'minutes', 'msg', 'dueAmount', 'isMature', 'type', 'cheques');
        return json_encode($return_array);
    }
    /**
     * Store investment details.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return JSON
     */
    public function storeAjax(Request $request)
    {
        if ($request->ajax()) {
            $baseurl = URL::to('/');
            if ($request['renewplan_id'] == 1) {
                $percentage = floor(($request['i'] / $request['rdfrd_no_of_accounts']) * 100);
            } else {
                $percentage = floor(($request['i'] / $request['daily_no_of_accounts']) * 100);
            }
            /* $response = array(
            'status' => 'alert',
            'msg'    =>  'testing call',
            'percentage'=>$percentage,
             );
            return Response::json($response);*/
            $entryTime = date("h:i:s");
            $globaldate = date("Y-m-d", strtotime(convertDate($request['renewal_date'])));
            $branch_id = $request['branch_id'];
            $getBranchCode = getBranchCode($branch_id);
            $getBranchName = getBranchDetail($branch_id);
            $branchCode = $getBranchCode->branch_code;
            $branchName = $getBranchName->name;
            $accountNumbers = $request['account_number'];
            $renewPlanId = $request['renewplan_id'];
            $sAccount = $this->getSavingAccountDetails($request['member_id']);
            $collectionSSBId = '';
            if ($request['i'] == 1) { //insert in user_log table only once during ajax call
                $encodeDate = json_encode($_POST);
                $arrs = array("saving_account_transaction_id" => 0, "type" => "3", "account_head_id" => 0, "user_id" => Auth::user()->id, "message" => "Renewal_Investment_Creation", "data" => $encodeDate);
                DB::table('user_log')->insert($arrs);
            }
            /* $encodeDate = json_encode($_POST);
        $arrs = array("saving_account_transaction_id" => 0, "type" => "3", "account_head_id" => 0, "user_id" => Auth::user()->id, "message" => "Renewal_Investment_Creation", "data" => $encodeDate);
        DB::table('user_log')->insert($arrs);
*/
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
                                if (empty($record1)) {
                                    $response = array(
                                        'status' => 'alert',
                                        'msg' => 'Renew date should less than created date',
                                    );
                                    return Response::json($response);
                                    // return back()->with('alert', 'Renew date should less than created date');
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
                                    $ssb['opening_balance'] = $record1->opening_balance - $request['amount'][$key];
                                } else {
                                    $ssb['opening_balance'] = $request['amount'][$key];
                                }
                                $ssb['withdrawal'] = $request['amount'][$key];
                                if ($branch_id != $investmentDetail->branch_id) {
                                    $branchName = getBranchDetail($branch_id)->name;
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
                            $record3 = SavingAccountTranscation::where('account_no', $accountNumber)->where('created_at', '<=', date("Y-m-d", strtotime(convertDate($request['renewal_date']))))->first();
                            if (empty($record3)) {
                                $response = array(
                                    'status' => 'alert',
                                    'msg' => 'Renew date should less than created date',
                                );
                                return Response::json($response);
                                //return back()->with('alert', 'Renew date should less than created date');
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
                                $branchName = getBranchNameByBrachAuto($branch_id)->name;
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
                            //     $saResult = SavingAccountTranscation::find($value->id);
                            //     $newArray['opening_balance']=$value->opening_balance+$request['amount'][$key];
                            //     $newArray['updated_at']=date("Y-m-d ".$entryTime."", strtotime(convertDate($request->renewal_date)));
                            //     $saResult->update($newArray);
                            //     SavingAccountTranscation::where('id', $value->id)->update(['opening_balance' => ($value->opening_balance+$request['amount'][$key]),'updated_at' => date("Y-m-d ".$entryTime."", strtotime(convertDate($request->renewal_date)))]);
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
                                    $dayBook = Daybook::find($value->id);
                                    $dayBook->update($daybookData);
                                }
                            }
                            $createDayBook = CommanController::createDayBookNew($createTransaction, $satRefId, 2, $request['investment_id'][$key], $request['member_id'], $request['investment_member_id'][$key], $lastBalance, $request['amount'][$key], $withdrawal = 0, $ssb['description'], $ssbAccountNumber, $branch_id, $branchCode, $amountArraySsb, $request['payment_mode'], $request['deposite_by_name'], $request['member_id'], $accountNumber, $request['cheque-number'], $request['bank-name'], $request['branch-name'], $globaldate, NULL, $online_payment_by = NULL, $collectionSSBId, 'CR', $received_cheque_id, $cheque_deposit_bank_id, $cheque_deposit_bank_ac_id, $online_deposit_bank_id, $online_deposit_bank_ac_id);
                            // ---------------------------  HEAD IMPLEMENT --------------------------
                            $planId = $investmentDetail->plan_id;
                            $this->investHeadCreateSSB($request['amount'][$key], $globaldate, $request['investment_id'][$key], $planId, $cheque_no, $cheque_id, $cheque_date, $online_transction_no, $online_transction_date, $online_deposit_bank_id, $online_deposit_bank_ac_id, $branch_id, $request['member_id'], $request['investment_member_id'][$key], $collectionSSBId, $ssbAccountTran->id, $pmodeAll, $ssbAccountNumber, $ssbFromId, $ssbAccountTranFromId, $ssbToId, $ssbAccountTranToId);
                            //--------------------------------HEAD IMPLEMENT  ------------------------
                            $daybookData['is_renewal'] = 0;
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
                                        $branchName = getBranchDetail($branch_id)->name;
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
                            // ---------------------------  Day book modify --------------------------
                            $lastAmount = Daybook::where('investment_id', $request['investment_id'][$key])->whereIN('transaction_type', [2, 4, 16, 17, 18])->where('account_no', $accountNumber)->whereDate('created_at', date("Y-m-d", strtotime(convertDate($request['renewal_date']))))->orderby('id', 'desc')->first();
                            if ($lastAmount) {
                                $lastBalance = $lastAmount->opening_balance + $request['amount'][$key];
                            } else {
                                $lastAmount = Daybook::where('investment_id', $request['investment_id'][$key])->whereIN('transaction_type', [2, 4, 16, 17, 18])->where('account_no', $accountNumber)->whereDate('created_at', '<', date("Y-m-d", strtotime(convertDate($request['renewal_date']))))->orderby('id', 'desc')->first();
                                $lastBalance = $lastAmount->opening_balance + $request['amount'][$key];
                            }
                            /*$nextRenewal = $lastAmount = Daybook::where('investment_id',$request['investment_id'][$key])->where('account_no',$accountNumber)->whereDate('created_at','>',date("Y-m-d", strtotime(convertDate($lastAmount->created_at))))->get();
                        foreach ($nextRenewal as $key1 => $value) {
                            $daybookData['opening_balance'] = $value->opening_balance+$request['amount'][$key];
                            $dayBook = Daybook::find($value->id);
                            $dayBook->update($daybookData);
                        }*/
                            $createDayBook = CommanController::createDayBookNew($createTransaction, $satRefId, 4, $request['investment_id'][$key], $request['member_id'], $request['investment_member_id'][$key], $lastBalance, $request['amount'][$key], $withdrawal = 0, $description, $ref = NULL, $branch_id, $branchCode, $amountArraySsb, $request['payment_mode'], $request['deposite_by_name'], $request['member_id'], $accountNumber, $request['cheque-number'], $request['bank-name'], $request['branch-name'], $globaldate, NULL, $online_payment_by = NULL, $collectionSSBId, 'CR', $received_cheque_id, $cheque_deposit_bank_id, $cheque_deposit_bank_ac_id, $online_deposit_bank_id, $online_deposit_bank_ac_id);
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
                            /* --------------
                        $dateForRenew=date("Y-m-d", strtotime(convertDate($request['renewal_date'])));
                        $Commission=getMonthlyWiseRenewalNew($request['investment_id'][$key],$request['amount'][$key],$dateForRenew);
                        foreach ($Commission as  $val) {
                            $tenureMonth=$investmentDetail->tenure*12;
                            $commission =CommanController:: commissionDistributeInvestmentRenew($investmentDetail->associate_id,$request['investment_id'][$key],3,$val['amount'],$val['month'],$investmentDetail->plan_id,$branch_id,$tenureMonth,$createDayBook,$val['type']);
                            $commission_collection =CommanController::commissionCollectionInvestmentRenew($request['member_id'],$request['investment_id'][$key],5,$val['amount'],$val['month'],$investmentDetail->plan_id,$branch_id,$tenureMonth,$createDayBook,$val['type']);
                            /*----- ------  credit business start ---- ---------------
                            $creditBusiness =CommanController::associateCreditBusiness($investmentDetail->associate_id,$request['investment_id'][$key],1,$val['amount'],$val['month'],$investmentDetail->plan_id,$tenureMonth,$createDayBook);
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
                            //$ipTransactionData['created_at'] = date("Y-m-d H:i:s", strtotime( str_replace('/','-',$request['renewal_date'] ) ) );
                            $updateipTransaction = Investmentplantransactions::find($ipTransaction->id);
                            $updateipTransaction->update($ipTransactionData);
                            $investmentCurrentBalance = getInvestmentCurrentBalance($investmentDetail->id, $investmentDetail->account_number);
                            //echo $investmentDetail->id.'-'.$investmentDetail->account_number; die;
                            $save = 1;
                            $rAmount = $investmentCurrentBalance;
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
                //  return back()->with('alert', $ex->getMessage());
            }
            /*$data['title'] = "Renewal Recipt";
        $data['renewFields'] = $request->all();
        $data['branchCode'] = $branchCode;
        $data['branchName'] = $branchName;
        return view('templates.admin.investment_management.renewal.recipt', $data);*/
            $explodeArray = json_encode($request->all());
            $ssb = '';
            // if($renewPlanId==2)
            // {
            //      $ssb = json_encode($ssbAccountTran->id);
            //       return redirect('admin/renew/recipt/'.$encodeRequests.'/'.$encodebranchCode.'/'.$encodebranchName.'/'.$ssb.'');
            // }
            // else{
            //          return redirect('admin/renew/recipt/'.$encodeRequests.'/'.$encodebranchCode.'/'.$encodebranchName.'');
            //     }
            $encodeRequests = base64_encode($explodeArray);
            $encodebranchCode = base64_encode($branchCode);
            $encodebranchName = base64_encode($branchName);
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
                'coutner' => $request['i'],
                'redirect_url' => $baseurl . '/admin/renew/recipt/' . $encodeRequests . '/' . $encodebranchCode . '/' . $encodebranchName . '/' . $ssb . '',
            );
            return Response::json($response);
            // return redirect('admin/renew/recipt/'.$encodeRequests.'/'.$encodebranchCode.'/'.$encodebranchName.'/'.$ssb.'');
            /*if ($createTransaction) {
            $data['title'] = "Renewal Recipt";
            $data['renewFields'] = $request->all();
            $data['branchCode'] = $branchCode;
            $data['branchName'] = $branchName;
            return view('templates.admin.investment_management.renewal.recipt', $data);
        } else {
            return back()->with('alert', 'Problem With Register New Plan');
        }*/
        }
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
        $getBranchName = getBranchDetail($branch_id);
        $branchCode = $getBranchCode->branch_code;
        $branchName = $getBranchName->name;
        $accountNumbers = $request['account_number'];
        $renewPlanId = $request['renewplan_id'];
        $sAccount = $this->getSavingAccountDetails($request['member_id']);
        $collectionSSBId = '';
        $encodeDate = json_encode($_POST);
        $createdBy = 1;
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
                            if (empty($record1)) {
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
                                $ssb['opening_balance'] = $record1->opening_balance - $request['amount'][$key];
                            } else {
                                $ssb['opening_balance'] = $request['amount'][$key];
                            }
                            $ssb['withdrawal'] = $request['amount'][$key];
                            if ($branch_id != $investmentDetail->branch_id) {
                                $branchName = getBranchNameByBrachAuto($branch_id);
                                $ssb['description'] = 'Fund Trf. To ' . $savingAccountDetail->account_no . '- From ' . $branchName->name . '';
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
                        $record3 = SavingAccountTranscation::where('account_no', $accountNumber)->where('created_at', '<=', date("Y-m-d", strtotime(convertDate($request['renewal_date']))))->first();
                        if (empty($record3)) {
                            return back()->with('alert', 'Renew date should less than created date');
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
                        $ssb['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($request['renewal_date'])));
                        $ssbAccountTran = SavingAccountTranscation::create($ssb);
                        $ssbToId = $savingAccountDetail->id;
                        $ssbAccountTranToId = $ssbAccountTran->id;
                        // $record4=SavingAccountTranscation::where('account_no',$accountNumber)->where('created_at','>',date("Y-m-d", strtotime(convertDate($request['renewal_date']))))->get();
                        // foreach ($record4 as $key1 => $value) {
                        //     $saResult = SavingAccountTranscation::find($value->id);
                        //     $newArray['opening_balance']=$value->opening_balance+$request['amount'][$key];
                        //     $newArray['updated_at']=date("Y-m-d ".$entryTime."", strtotime(convertDate($request->renewal_date)));
                        //     $saResult->update($newArray);
                        //     SavingAccountTranscation::where('id', $value->id)->update(['opening_balance' => ($value->opening_balance+$request['amount'][$key]),'updated_at' => date("Y-m-d ".$entryTime."", strtotime(convertDate($request->renewal_date)))]);
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
                                $dayBook = Daybook::find($value->id);
                                $dayBook->update($daybookData);
                            }
                        }
                        $createDayBook = CommanController::createDayBookNew($createTransaction, $satRefId, 2, $request['investment_id'][$key], $request['member_id'], $request['investment_member_id'][$key], $lastBalance, $request['amount'][$key], $withdrawal = 0, $ssb['description'], $ssbAccountNumber, $branch_id, $branchCode, $amountArraySsb, $request['payment_mode'], $request['deposite_by_name'], $request['member_id'], $accountNumber, $request['cheque-number'], $request['bank-name'], $request['branch-name'], $globaldate, NULL, $online_payment_by = NULL, $collectionSSBId, 'CR', $received_cheque_id, $cheque_deposit_bank_id, $cheque_deposit_bank_ac_id, $online_deposit_bank_id, $online_deposit_bank_ac_id);
                        // ---------------------------  HEAD IMPLEMENT --------------------------
                        $planId = $investmentDetail->plan_id;
                        $this->investHeadCreateSSB($request['amount'][$key], $globaldate, $request['investment_id'][$key], $planId, $cheque_no, $cheque_id, $cheque_date, $online_transction_no, $online_transction_date, $online_deposit_bank_id, $online_deposit_bank_ac_id, $branch_id, $request['member_id'], $request['investment_member_id'][$key], $collectionSSBId, $ssbAccountTran->id, $pmodeAll, $ssbAccountNumber, $ssbFromId, $ssbAccountTranFromId, $ssbToId, $ssbAccountTranToId);
                        //--------------------------------HEAD IMPLEMENT  ------------------------
                        $daybookData['is_renewal'] = 0;
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
                                    $branchName = getBranchNameByBrachAuto($branch_id);
                                    $branchName = $branchName->name;
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
                        // ---------------------------  Day book modify --------------------------
                        $lastAmount = Daybook::where('investment_id', $request['investment_id'][$key])->whereIN('transaction_type', [2, 4, 16, 17, 18])->where('account_no', $accountNumber)->whereDate('created_at', date("Y-m-d", strtotime(convertDate($request['renewal_date']))))->orderby('id', 'desc')->first();
                        if ($lastAmount) {
                            $lastBalance = $lastAmount->opening_balance + $request['amount'][$key];
                        } else {
                            $lastAmount = Daybook::where('investment_id', $request['investment_id'][$key])->whereIN('transaction_type', [2, 4, 16, 17, 18])->where('account_no', $accountNumber)->whereDate('created_at', '<', date("Y-m-d", strtotime(convertDate($request['renewal_date']))))->orderby('id', 'desc')->first();
                            $lastBalance = $lastAmount->opening_balance + $request['amount'][$key];
                        }
                        /*$nextRenewal = $lastAmount = Daybook::where('investment_id',$request['investment_id'][$key])->where('account_no',$accountNumber)->whereDate('created_at','>',date("Y-m-d", strtotime(convertDate($lastAmount->created_at))))->get();
                        foreach ($nextRenewal as $key1 => $value) {
                            $daybookData['opening_balance'] = $value->opening_balance+$request['amount'][$key];
                            $dayBook = Daybook::find($value->id);
                            $dayBook->update($daybookData);
                        }*/
                        $createDayBook = CommanController::createDayBookNew($createTransaction, $satRefId, 4, $request['investment_id'][$key], $request['member_id'], $request['investment_member_id'][$key], $lastBalance, $request['amount'][$key], $withdrawal = 0, $description, $ref = NULL, $branch_id, $branchCode, $amountArraySsb, $request['payment_mode'], $request['deposite_by_name'], $request['member_id'], $accountNumber, $request['cheque-number'], $request['bank-name'], $request['branch-name'], $globaldate, NULL, $online_payment_by = NULL, $collectionSSBId, 'CR', $received_cheque_id, $cheque_deposit_bank_id, $cheque_deposit_bank_ac_id, $online_deposit_bank_id, $online_deposit_bank_ac_id);
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
                        /* --------------
                        $dateForRenew=date("Y-m-d", strtotime(convertDate($request['renewal_date'])));
                        $Commission=getMonthlyWiseRenewalNew($request['investment_id'][$key],$request['amount'][$key],$dateForRenew);
                        foreach ($Commission as  $val) {
                            $tenureMonth=$investmentDetail->tenure*12;
                            $commission =CommanController:: commissionDistributeInvestmentRenew($investmentDetail->associate_id,$request['investment_id'][$key],3,$val['amount'],$val['month'],$investmentDetail->plan_id,$branch_id,$tenureMonth,$createDayBook,$val['type']);
                            $commission_collection =CommanController::commissionCollectionInvestmentRenew($request['member_id'],$request['investment_id'][$key],5,$val['amount'],$val['month'],$investmentDetail->plan_id,$branch_id,$tenureMonth,$createDayBook,$val['type']);
                            /*----- ------  credit business start ---- ---------------
                          $creditBusiness =CommanController::associateCreditBusiness($investmentDetail->associate_id,$request['investment_id'][$key],1,$val['amount'],$val['month'],$investmentDetail->plan_id,$tenureMonth,$createDayBook);
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
                        //$ipTransactionData['created_at'] = date("Y-m-d H:i:s", strtotime( str_replace('/','-',$request['renewal_date'] ) ) );
                        $updateipTransaction = Investmentplantransactions::find($ipTransaction->id);
                        $updateipTransaction->update($ipTransactionData);
                        $investmentCurrentBalance = getInvestmentCurrentBalance($investmentDetail->id, $investmentDetail->account_number);
                        //echo $investmentDetail->id.'-'.$investmentDetail->account_number; die;
                        $save = 1;
                        $rAmount = $investmentCurrentBalance;
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
        /*$data['title'] = "Renewal Recipt";
        $data['renewFields'] = $request->all();
        $data['branchCode'] = $branchCode;
        $data['branchName'] = $branchName;
        return view('templates.admin.investment_management.renewal.recipt', $data);*/
        $explodeArray = json_encode($request->all());
        $ssb = '';
        // if($renewPlanId==2)
        // {
        //      $ssb = json_encode($ssbAccountTran->id);
        //       return redirect('admin/renew/recipt/'.$encodeRequests.'/'.$encodebranchCode.'/'.$encodebranchName.'/'.$ssb.'');
        // }
        // else{
        //          return redirect('admin/renew/recipt/'.$encodeRequests.'/'.$encodebranchCode.'/'.$encodebranchName.'');
        //     }
        $encodeRequests = base64_encode($explodeArray);
        $encodebranchCode = base64_encode($branchCode);
        $encodebranchName = base64_encode($branchName);
        if ($renewPlanId == 2) {
            $ssb = json_encode($ssbAccountTran->id);
        } else {
            $ssb = $createDayBook;
            // return redirect('branch/renew/recipt/'.$encodeRequests.'/'.$encodebranchCode.'/'.$encodebranchName.'');
        }
        return redirect('admin/renew/recipt/' . $encodeRequests . '/' . $encodebranchCode . '/' . $encodebranchName . '/' . $ssb . '');
        /*if ($createTransaction) {
            $data['title'] = "Renewal Recipt";
            $data['renewFields'] = $request->all();
            $data['branchCode'] = $branchCode;
            $data['branchName'] = $branchName;
            return view('templates.admin.investment_management.renewal.recipt', $data);
        } else {
            return back()->with('alert', 'Problem With Register New Plan');
        }*/
    }
    public function renewalDetails($url, $branchCode, $branchName, $ssb = NULL)
    {
        $decodedurl = base64_decode($url);
        $explodeArray = json_decode($decodedurl);
        $branchCode = base64_decode($branchCode);
        $branchName = base64_decode($branchName);
        $data['title'] = "Renewal Recipt";
        $data['renewFields'] = (array) $explodeArray;
        $data['branchCode'] = $branchCode;
        $data['branchName'] = $branchName;
        /*Code added by amar*/
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
        if (isset($arr_new) && $arr_new != NULL) {
            return view('templates.admin.investment_management.renewal.recipt', $arr_new);
        } else {
            return view('templates.admin.investment_management.renewal.recipt', $data);
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
        $sAccount = $this->getSavingAccountDetails($request['member_id']);
        $data['investment_id'] = $investmentId;
        $data['plan_id'] = $request['renew_investment_plan_id'];
        $data['member_id'] = $request['member_id'];
        $data['branch_id'] = $branch_id;
        $data['branch_code'] = $branchCode;
        $data['deposite_amount'] = $amount;
        $data['deposite_date'] = date("Y-m-d", strtotime(convertDate($request['renewal_date'])));
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
        $daybookRefRD = CommanController::createBranchDayBookReferenceNew($amount, $globaldate);
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
            $allTranRDcheque = CommanController::createAllHeadTransaction($refIdRD, $branch_id, $bank_id = NULL, $bank_ac_id = NULL, $head41, $type, $sub_type, $type_id, $createDayBook, $associate_id, $memberId, $branch_id_to = NULL, $branch_id_from = NULL, $amount, $amount, $amount, $rdDes, 'DR', $headPaymentModeRD, $currency_code, $amount_to_id = NULL, $amount_to_name = NULL, $amount_from_id = NULL, $amount_from_name = NULL, $jv_unique_id = NULL, $v_no, $v_date, $ssb_account_id_from, $ssb_account_id_to = NULL, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, 0, $cheque_id, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_from_id = NULL, $cheque_bank_ac_from_id = NULL, $cheque_bank_to, $cheque_bank_ac_to, getSamraddhBank($cheque_bank_to)->bank_name, $cheque_bank_to_branch = NULL, getSamraddhBankAccountId($cheque_bank_ac_to)->account_no, getSamraddhBankAccountId($cheque_bank_ac_to)->ifsc_code, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_from_id = NULL, $transction_bank_from_ac_id = NULL, $transction_bank_to, $transction_bank_ac_to, $transction_bank_to_name = NULL, $transction_bank_to_ac_no = NULL, $transction_bank_to_branch = NULL, $transction_bank_to_ifsc = NULL, $transction_date, $created_by, $created_by_id);
            /*$allTranRDcheque=CommanController::createAllTransactionNew($refIdRD,$branch_id,$bank_id=NULL,$bank_ac_id=NULL,$head11,$head21,$head31,$head41,$head51,$type,$sub_type,$type_id,$associate_id,$memberId,$branch_id_to=NULL,$branch_id_from=NULL,$opening_balance=NULL,$amount,$closing_balance=NULL,$rdDes,'CR',$headPaymentModeRD,$currency_code,$amount_to_id=NULL,$amount_to_name=NULL,$amount_from_id=NULL,$amount_from_name=NULL,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at,$updated_at,$createDayBook);*/
            //bank entry
            $bankCheque = CommanController::samraddhBankDaybookNew($refIdRD, $cheque_bank_to, $cheque_bank_ac_to, $type, $sub_type, $type_id, $createDayBook, $associate_id, $memberId, $branch_id, $opening_balance = NULL, $amount, $closing_balance = NULL, $rdDes, $rdDesDR, $rdDesCR, 'CR', $headPaymentModeRD, $currency_code, $amount_to_id = NULL, $amount_to_name = NULL, $amount_from_id = NULL, $amount_from_name = NULL, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_bank_to_name = NULL, $transction_bank_to_ac_no = NULL, $transction_bank_to_branch = NULL, $transction_bank_to_ifsc = NULL, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL);
            /*$bankCheque=CommanController::createSamraddhBankDaybookNew($refIdRD,$cheque_bank_to,$cheque_bank_ac_to,$type,$sub_type,$type_id,$associate_id,$memberId,$branch_id,$opening_balance=NULL,$amount,$closing_balance=NULL,$rdDes,$rdDesDR,$rdDesCR,'CR',$headPaymentModeRD,$currency_code,$amount_to_id=NULL,$amount_to_name=NULL,$amount_from_id=NULL,$amount_from_name=NULL,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at,$updated_at,$createDayBook);*/
            //bank balence
            $bankClosing = $this->updateBackDateBankBalance($cheque_bank_to, $cheque_bank_ac_to, $created_at, $amount, 0);
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
    public function investHeadCreateSSB($amount, $globaldate, $investmentId, $planId, $cheque_no, $cheque_id, $cheque_date, $online_transction_no, $online_transction_date, $online_deposit_bank_id, $online_deposit_bank_ac_id, $branch_id, $associate_id, $memberId, $ssbId, $createDayBook, $payment_mode, $investmentAccountNoRd, $ssbFromId, $ssbAccountTranFromId, $ssbToId, $ssbAccountTranToId)
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
            $cheque_type = 1;
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
            $rdDes = 'Amount received for SSB A/C Deposit (' . $investmentAccountNoRd . ') through cheque(' . $cheque_no . ')';
            $rdDesMem = 'SSB A/C Deposit (' . $investmentAccountNoRd . ') through cheque(' . $cheque_no . ')';
            //bank head entry
            $allTranRDcheque = CommanController::createAllHeadTransaction($refIdRD, $branch_id, $bank_id = NULL, $bank_ac_id = NULL, $head41, $type, $sub_type, $type_id, $createDayBook, $associate_id, $memberId, $branch_id_to = NULL, $branch_id_from = NULL, $amount, $amount, $amount, $rdDes, 'DR', $headPaymentModeRD, $currency_code, $amount_to_id = NULL, $amount_to_name = NULL, $amount_from_id = NULL, $amount_from_name = NULL, $jv_unique_id = NULL, $v_no, $v_date, $ssb_account_id_from, $ssb_account_id_to = NULL, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $cheque_type, $cheque_id, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_from_id = NULL, $cheque_bank_ac_from_id = NULL, $cheque_bank_to, $cheque_bank_ac_to, getSamraddhBank($cheque_bank_to)->bank_name, $cheque_bank_to_branch = NULL, getSamraddhBankAccountId($cheque_bank_ac_to)->account_no, getSamraddhBankAccountId($cheque_bank_ac_to)->ifsc_code, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_from_id = NULL, $transction_bank_from_ac_id = NULL, $transction_bank_to, $transction_bank_ac_to, $transction_bank_to_name = NULL, $transction_bank_to_ac_no = NULL, $transction_bank_to_branch = NULL, $transction_bank_to_ifsc = NULL, $transction_date, $created_by, $created_by_id);
            /*$allTranRDcheque=CommanController::createAllTransactionNew($refIdRD,$branch_id,$bank_id=NULL,$bank_ac_id=NULL,$head11,$head21,$head31,$head41,$head51,$type,$sub_type,$type_id,$associate_id,$memberId,$branch_id_to=NULL,$branch_id_from=NULL,$opening_balance=NULL,$amount,$closing_balance=NULL,$rdDes,'CR',$headPaymentModeRD,$currency_code,$amount_to_id=NULL,$amount_to_name=NULL,$amount_from_id=NULL,$amount_from_name=NULL,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at,$updated_at,$createDayBook);*/
            //bank entry
            $bankCheque = CommanController::samraddhBankDaybookNew($refIdRD, $cheque_bank_to, $cheque_bank_ac_to, $type, $sub_type, $type_id, $createDayBook, $associate_id, $memberId, $branch_id, $opening_balance = NULL, $amount, $closing_balance = NULL, $rdDes, $rdDesDR, $rdDesCR, 'CR', $headPaymentModeRD, $currency_code, $amount_to_id = NULL, $amount_to_name = NULL, $amount_from_id = NULL, $amount_from_name = NULL, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_bank_to_name = NULL, $transction_bank_to_ac_no = NULL, $transction_bank_to_branch = NULL, $transction_bank_to_ifsc = NULL, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL);
            /*$bankCheque=CommanController::samraddhBankDaybookNew($refIdRD,$cheque_bank_to,$cheque_bank_ac_to,$type,$sub_type,$type_id,$associate_id,$memberId,$branch_id,$opening_balance=NULL,$amount,$closing_balance=NULL,$rdDes,$rdDesDR,$rdDesCR,'CR',$headPaymentModeRD,$currency_code,$amount_to_id=NULL,$amount_to_name=NULL,$amount_from_id=NULL,$amount_from_name=NULL,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at,$updated_at,$createDayBook,$jv_unique_id=NULL,$cheque_type=NULL,$cheque_id=NULL,$ssb_account_tran_id_to=NULL,$ssb_account_tran_id_from=NULL);*/
            //bank balence
            $bankClosing = $this->updateBackDateBankBalance($cheque_bank_to, $cheque_bank_ac_to, $created_at, $amount, 0);
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
            $allTranRDonline = CommanController::createAllHeadTransaction($refIdRD, $branch_id, $bank_id = NULL, $bank_ac_id = NULL, $head411, $type, $sub_type, $type_id, $createDayBook, $associate_id, $memberId, $branch_id_to = NULL, $branch_id_from = NULL, $amount, $amount, $amount, $rdDes, 'DR', $headPaymentModeRD, $currency_code, $amount_to_id = NULL, $amount_to_name = NULL, $amount_from_id = NULL, $amount_from_name = NULL, $jv_unique_id = NULL, $v_no, $v_date, $ssb_account_id_from, $ssb_account_id_to = NULL, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $cheque_type = NULL, $cheque_id = NULL, $cheque_no = NULL, $cheque_date = NULL, $cheque_bank_from = NULL, $cheque_bank_ac_from = NULL, $cheque_bank_ifsc_from = NULL, $cheque_bank_branch_from = NULL, $cheque_bank_from_id = NULL, $cheque_bank_ac_from_id = NULL, $cheque_bank_to = NULL, $cheque_bank_ac_to = NULL, $cheque_bank_to_name = NULL, $cheque_bank_to_branch = NULL, $cheque_bank_to_ac_no = NULL, $cheque_bank_to_ifsc = NULL, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_from_id = NULL, $transction_bank_from_ac_id = NULL, $transction_bank_to, $transction_bank_ac_to, getSamraddhBank($online_deposit_bank_id)->bank_name, getSamraddhBankAccountId($online_deposit_bank_ac_id)->account_no, $transction_bank_to_branch = NULL, getSamraddhBankAccountId($online_deposit_bank_ac_id)->ifsc_code, $transction_date, $created_by, $created_by_id);
            /*$allTranRDonline=CommanController::createAllTransactionNew($refIdRD,$branch_id,$bank_id=NULL,$bank_ac_id=NULL,$head111,$head211,$head311,$head411,$head511,$type,$sub_type,$type_id,$associate_id,$memberId,$branch_id_to=NULL,$branch_id_from=NULL,$opening_balance=NULL,$amount,$closing_balance=NULL,$rdDes,'CR',$headPaymentModeRD,$currency_code,$amount_to_id=NULL,$amount_to_name=NULL,$amount_from_id=NULL,$amount_from_name=NULL,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at,$updated_at,$createDayBook);*/
            //bank entry
            $bankonline = CommanController::samraddhBankDaybookNew($refIdRD, $transction_bank_to, $transction_bank_ac_to, $type, $sub_type, $type_id, $createDayBook, $associate_id, $memberId, $branch_id, $opening_balance = NULL, $amount, $closing_balance = NULL, $rdDes, $rdDesDR, $rdDesCR, 'CR', $headPaymentModeRD, $currency_code, $amount_to_id = NULL, $amount_to_name = NULL, $amount_from_id = NULL, $amount_from_name = NULL, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, getSamraddhBank($online_deposit_bank_id)->bank_name, getSamraddhBankAccountId($online_deposit_bank_ac_id)->account_no, $transction_bank_to_branch = NULL, getSamraddhBankAccountId($online_deposit_bank_ac_id)->ifsc_code, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL);
            /*$bankonline=CommanController::samraddhBankDaybookNew($refIdRD,$transction_bank_to,$transction_bank_ac_to,$type,$sub_type,$type_id,$associate_id,$memberId,$branch_id,$opening_balance=NULL,$amount,$closing_balance=NULL,$rdDes,$rdDesDR,$rdDesCR,'CR',$headPaymentModeRD,$currency_code,$amount_to_id=NULL,$amount_to_name=NULL,$amount_from_id=NULL,$amount_from_name=NULL,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at,$updated_at,$createDayBook,$jv_unique_id=NULL,$cheque_type=NULL,$cheque_id=NULL,$ssb_account_tran_id_to=NULL,$ssb_account_tran_id_from=NULL);*/
            //bank balence
            $bankClosing = $this->updateBackDateBankBalance($transction_bank_to, $transction_bank_ac_to, $created_at, $amount, 0);
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
            $allTranRDSSB = CommanController::createAllHeadTransaction($refIdRD, $branch_id, $bank_id = NULL, $bank_ac_id = NULL, $head4rdSSB, $type, $sub_type, $type_id, $createDayBook, $associate_id, $memberId, $branch_id_to = NULL, $branch_id_from = NULL, $amount, $amount, $amount, $rdDes, 'DR', $headPaymentModeRD, $currency_code, $amount_to_id = NULL, $amount_to_name = NULL, $amount_from_id = NULL, $amount_from_name = NULL, $jv_unique_id = NULL, $v_no, $v_date, $ssb_account_id_from, $ssb_account_id_to = NULL, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $cheque_type = NULL, $cheque_id = NULL, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_from_id = NULL, $cheque_bank_ac_from_id = NULL, $cheque_bank_to, $cheque_bank_ac_to, $cheque_bank_to_name = NULL, $cheque_bank_to_branch = NULL, $cheque_bank_to_ac_no = NULL, $cheque_bank_to_ifsc = NULL, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_from_id = NULL, $transction_bank_from_ac_id = NULL, $transction_bank_to, $transction_bank_ac_to, $transction_bank_to_name = NULL, $transction_bank_to_ac_no = NULL, $transction_bank_to_branch = NULL, $transction_bank_to_ifsc = NULL, $transction_date, $created_by, $created_by_id);
            /*$allTranRDSSB=CommanController::createAllTransactionNew($refIdRD,$branch_id,$bank_id=NULL,$bank_ac_id=NULL,$head1rdSSB,$head2rdSSB,$head3rdSSB,$head4rdSSB,$head5rdSSB,$type,$sub_type,$type_id,$associate_id,$memberId,$branch_id_to=NULL,$branch_id_from=NULL,$opening_balance=NULL,$amount,$closing_balance=NULL,$rdDes,'DR',$headPaymentModeRD,$currency_code,$amount_to_id=NULL,$amount_to_name=NULL,$amount_from_id=NULL,$amount_from_name=NULL,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at,$updated_at,$createDayBook);*/
            $branchClosingSSB = $this->updateBranchClosingCashDr($branch_id, $created_at, $amount, 0);
            $memberTranInvest77 = CommanController::memberTransactionNew($refIdRD, '4', '47', $ssb_account_id_from, $createDayBook, $associate_id, $ssbDetals->member_id, $branch_id, $bank_id = NULL, $account_id = NULL, $amount, $SSBDescTran, 'DR', $headPaymentModeRD, $currency_code, $amount_to_id = $type_id, $amount_to_name = $planDetail->name, $amount_from_id = NULL, $amount_from_name = NULL, $v_no, $v_date, $ssb_account_id_from = NULL, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL);
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
            $rdDes = 'Amount received for SSB A/C Deposit (' . $investmentAccountNoRd . ') through cash(' . getBranchCode($branch_id)->branch_code . ')';
            $rdDesMem = 'SSB A/C Deposit (' . $investmentAccountNoRd . ') through cash(' . getBranchCode($branch_id)->branch_code . ')';
            // branch cash  head entry +
            $allTranRDcash = CommanController::createAllHeadTransaction($refIdRD, $branch_id, $bank_id = NULL, $bank_ac_id = NULL, $head3rdC, $type, $sub_type, $type_id, $createDayBook, $associate_id, $memberId, $branch_id_to = NULL, $branch_id_from = NULL, $amount, $amount, $amount, $rdDes, 'DR', $headPaymentModeRD, $currency_code, $amount_to_id = NULL, $amount_to_name = NULL, $amount_from_id = NULL, $amount_from_name = NULL, $jv_unique_id = NULL, $v_no, $v_date, $ssb_account_id_from, $ssb_account_id_to = NULL, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $cheque_type = NULL, $cheque_id = NULL, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_from_id = NULL, $cheque_bank_ac_from_id = NULL, $cheque_bank_to, $cheque_bank_ac_to, $cheque_bank_to_name = NULL, $cheque_bank_to_branch = NULL, $cheque_bank_to_ac_no = NULL, $cheque_bank_to_ifsc = NULL, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_from_id = NULL, $transction_bank_from_ac_id = NULL, $transction_bank_to, $transction_bank_ac_to, $transction_bank_to_name = NULL, $transction_bank_to_ac_no = NULL, $transction_bank_to_branch = NULL, $transction_bank_to_ifsc = NULL, $transction_date, $created_by, $created_by_id);
            /*$allTranRDcash=$this->createAllTransactionNew($refIdRD,$branch_id,$bank_id=NULL,$bank_ac_id=NULL,$head1rdC,$head2rdC,$head3rdC,$head4rdC,$head5rdC,$type,$sub_type,$type_id,$associate_id,$memberId,$branch_id_to=NULL,$branch_id_from=NULL,$opening_balance=NULL,$amount,$closing_balance=NULL,$rdDes,$payment_type_rd,$headPaymentModeRD,$currency_code,$amount_to_id=NULL,$amount_to_name=NULL,$amount_from_id=NULL,$amount_from_name=NULL,$v_no,$v_date,$ssbFromId,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at,$updated_at,$createDayBook,$ssbToId,$ssbAccountTranToId,$ssbAccountTranFromId);*/
            //Balance   entry +
            $branchCash = $this->updateBranchCashCr($branch_id, $created_at, $amount, 0);
        }
        //branch day book entry +
        $daybookInvest = CommanController::branchDayBookNew($refIdRD, $branch_id, $type, $sub_type, $type_id, $createDayBook, $associate_id, $memberId, $branch_id_to = NULL, $branch_id_from = NULL, $opening_balance = NULL, $amount, $closing_balance = NULL, $rdDes, $rdDesDR, $rdDesCR, $payment_type_rd, $headPaymentModeRD, $currency_code, $amount_to_id = NULL, $amount_to_name = NULL, $amount_from_id = NULL, $amount_from_name = NULL, $v_no, $v_date, $ssbFromId, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $is_contra = NULL, $contra_id = NULL, $created_at, $ssbAccountTranToId, $ssbAccountTranFromId, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL);
        // Investment head entry +
        $allTranInvest = CommanController::createAllHeadTransaction($refIdRD, $branch_id, $bank_id = NULL, $bank_ac_id = NULL, $head4Invest, $type, $sub_type, $type_id, $createDayBook, $associate_id, $memberId, $branch_id_to = NULL, $branch_id_from = NULL, $amount, $amount, $amount, $rdDes, $payment_type_rd, $headPaymentModeRD, $currency_code, $amount_to_id = NULL, $amount_to_name = NULL, $amount_from_id = NULL, $amount_from_name = NULL, $jv_unique_id = NULL, $v_no, $v_date, $ssb_account_id_from, $ssbToId, $ssbAccountTranToId, $ssbAccountTranFromId, $cheque_type = NULL, $cheque_id = NULL, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_from_id = NULL, $cheque_bank_ac_from_id = NULL, $cheque_bank_to, $cheque_bank_ac_to, $cheque_bank_to_name = NULL, $cheque_bank_to_branch = NULL, $cheque_bank_to_ac_no = NULL, $cheque_bank_to_ifsc = NULL, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_from_id = NULL, $transction_bank_from_ac_id = NULL, $transction_bank_to, $transction_bank_ac_to, $transction_bank_to_name = NULL, $transction_bank_to_ac_no = NULL, $transction_bank_to_branch = NULL, $transction_bank_to_ifsc = NULL, $transction_date, $created_by, $created_by_id);
        /*$allTranInvest = $this->createAllTransactionNew($refIdRD,$branch_id,$bank_id=NULL,$bank_ac_id=NULL,$head1Invest,$head2Invest,$head3Invest,$head4Invest,$head5Invest,$type,$sub_type,$type_id,$associate_id,$memberId,$branch_id_to=NULL,$branch_id_from=NULL,$opening_balance=NULL,$amount,$closing_balance=NULL,$rdDes,$payment_type_rd,$headPaymentModeRD,$currency_code,$amount_to_id=NULL,$amount_to_name=NULL,$amount_from_id=NULL,$amount_from_name=NULL,$v_no,$v_date,$ssbFromId,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at,$updated_at,$createDayBook,$ssbToId,$ssbAccountTranToId,$ssbAccountTranFromId);*/
        // Member transaction  +
        $memberTranInvest = CommanController::memberTransactionNew($refIdRD, $type, $sub_type, $type_id, $createDayBook, $associate_id, $memberId, $branch_id, $bank_id = NULL, $account_id = NULL, $amount, $rdDesMem, $payment_type_rd, $headPaymentModeRD, $currency_code, $amount_to_id = NULL, $amount_to_name = NULL, $amount_from_id = NULL, $amount_from_name = NULL, $v_no, $v_date, $ssbFromId, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $ssbAccountTranToId, $ssbAccountTranFromId, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL);
        /******** Balance   entry ***************/
        $branchClosing = $this->updateBranchClosingCashCr($branch_id, $created_at, $amount, 0);
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
        $data['title'] = "Update Renewal Transcation";
        return view('templates.admin.investment_management.renewal.updaterenewal', $data);
    }
    public function updateSsb()
    {
        $data['title'] = "Update SSB Transcation";
        return view('templates.admin.investment_management.renewal.updatessb', $data);
    }
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
    //                 $updateAssociateAmount = Daybook::where('id',$value1['id'])->update(array('opening_balance' => ($value1['deposit']+$lastRecord->opening_balance),'created_at' => date("Y-m-d ".$newTime."", strtotime(convertDate($value1['created_at'])))));
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
    //                     $updateAssociateAmount = Daybook::where('id',$value1['id'])->update(array('opening_balance' => ($value1['deposit']+$lastRecord->opening_balance)));
    //                 }elseif($value1['withdrawal'] > 0){
    //                     $updateAssociateAmount = Daybook::where('id',$value1['id'])->update(array('opening_balance' => ($lastRecord->opening_balance-$value1['withdrawal'])));
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
                // $sumAmount = Daybook::select('id','opening_balance','deposit')->where('account_no',$value)->whereIN('transaction_type',[2,4,16,17,18])->where('created_at','<',$value1['created_at'])->orderBy('created_at','desc')->sum('deposit');
                $endTime = strtotime("+" . $addmiute . " minutes", strtotime($entryTime));
                $newTime = date('H:i:s', $endTime);
                if ($lastRecord) {
                    $updateAssociateAmount = Daybook::where('id', $value1['id'])->update(array('opening_balance' => ($value1['deposit'] + $lastRecord->opening_balance), 'created_at' => date("Y-m-d " . $newTime . "", strtotime(convertDate($value1['created_at'])))));
                    $updateInvestmentCurrentBalance = Memberinvestments::where('account_number', $value)->update(['current_balance' => $value1['deposit'] + $lastRecord->opening_balance]);
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
                        $updateInvestmentCurrentBalance = Memberinvestments::where('account_number', $value)->update(['current_balance' => $value1['deposit'] + $lastRecord->opening_balance]);
                    } elseif ($value1['withdrawal'] > 0) {
                        $updateAssociateAmount = Daybook::where('id', $value1['id'])->update(array('opening_balance' => ($lastRecord->opening_balance - $value1['withdrawal'])));
                        $updateInvestmentCurrentBalance = Memberinvestments::where('account_number', $value)->update(['current_balance' => $lastRecord->opening_balance - $value1['withdrawal']]);
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
                        $updateAssociateAmount = \App\Models\SavingAccountTranscation::where('id', $value1['id'])->update(array('opening_balance' => ($value1['deposit'] + $lastRecord->opening_balance)));
                    } elseif ($value1['withdrawal'] > 0) {
                        $updateAssociateAmount = \App\Models\SavingAccountTranscation::where('id', $value1['id'])->update(array('opening_balance' => ($lastRecord->opening_balance - $value1['withdrawal'])));
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
                    $updateAssociateAmount = Daybook::where('id', $value1['id'])->update(array('opening_balance' => ($value1['deposit'] + $lastRecord->opening_balance), 'created_at' => date("Y-m-d " . $newTime . "", strtotime(convertDate($value1['created_at'])))));
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
                    //$updateAssociateAmount = Daybook::where('id',$value1['id'])->update(array('opening_balance' => ($value1['deposit']+$lastRecord->opening_balance)));
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
                                $updateAssociateAmount = \App\Models\SavingAccountTranscation::where('id', $value1['id'])->update(array('opening_balance' => ($value1['deposit'] + $lastRecord->opening_balance)));
                            } elseif ($value1['withdrawal'] > 0) {
                                $updateAssociateAmount = \App\Models\SavingAccountTranscation::where('id', $value1['id'])->update(array('opening_balance' => ($lastRecord->opening_balance - $value1['withdrawal'])));
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
                        $updateAssociateAmount = \App\Models\SavingAccountTranscation::where('id', $value1['id'])->update(array('opening_balance' => ($value1['deposit'] + $lastRecord->opening_balance)));
                    } else {
                        $updateAssociateAmount = \App\Models\SavingAccountTranscation::where('id', $value1['id'])->update(array('opening_balance' => ($lastRecord->opening_balance - $value1['withdrawal'])));
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
    //                     $updateAssociateAmount = \App\Models\SavingAccountTranscation::where('id',$value1['id'])->update(array('opening_balance' => ($value1['deposit']+$lastRecord->opening_balance)));
    //                 }elseif($value1['withdrawal'] > 0){
    //                     $updateAssociateAmount = \App\Models\SavingAccountTranscation::where('id',$value1['id'])->update(array('opening_balance' => ($lastRecord->opening_balance-$value1['withdrawal'])));
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
        $data['mId'] = $mId = SavingAccount::select('member_investments_id', 'member_id', 'account_no', 'customer_id')->where('id', $data['data']->saving_account_id)->first();
        $data['aId'] = $aId = Memberinvestments::select('associate_id', 'plan_id')->where('id', $mId->member_investments_id)->first();
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
            'memberCompany:id,member_id,customer_id',
            'dbranch:id,name,branch_code',
            'member:id,member_id,first_name,last_name',
            'associateMember:id,member_id,first_name,last_name',
            'investment' => function ($query) {
                $query->with([
                    'plan' => function ($q) {
                        $q->withoutGlobalScope(ActiveScope::class);
                    }
                ])->select('id', 'plan_id', 'account_number', 'tenure', 'member_id', 'customer_id', 'associate_id');
            }
        ])->where('id', $id)->where('payment_type', 'CR')->first();
        return view('templates.admin.investment_management.receipt', $data);
    }
}
