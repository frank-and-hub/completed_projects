<?php
namespace App\Http\Controllers\Api\Epassbook;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Validator;
use App\Models\Member;
use App\Models\CollectorAccount;
use App\Models\Memberinvestments;
use App\Models\Daybook;
use App\Http\Controllers\Admin\CommanController;
use App\Models\SavingAccountTranscation;
use App\Models\SavingAccount;
use Carbon\Carbon;
use DB;
use App\Http\Controllers\Api\Epassbook\CommanAppEpassbookController;
use Session;
use DateTime;
use App\Models\LoanEmisNew;
use App\Http\Traits\Oustanding_amount_trait;
use App\Services\Sms;
use App\Interfaces\RepositoryInterface;

class RenewalController extends Controller
{
    use Oustanding_amount_trait;
    public function __construct(RepositoryInterface $repository)
    {
        $this->repository = $repository;
    }
    public function renewal_payment(Request $request)
    {
        DB::beginTransaction();
        $entryTime = date("H:i:s");
        $investmentId = $request->id;
        $company_id = $request->company_id;
        $deno_amount = $request->deposit_amount;
        $associate_no = $request->member_id;
        $payment_mode = $request->payment_mode;
        $checktoken = $request->token;
        $token = md5($associate_no);
        try {
            $memberCount = Member::select('id', 'status', 'member_id')
                ->where('member_id', $associate_no)
                ->where('status', 1)
                ->where('is_block', 0)
                ->count();
            if ($memberCount > 0) {
                if ($token == $checktoken) {
                    $memberData = Member::with([
                        'savingAccount_Custom3' => function ($q) use ($company_id) {
                            $q->when('savingAccountBalance', function ($q) use ($company_id) {
                                $q->where('company_id', $company_id);
                            })->where('company_id', $company_id);
                        },
                        'memberCompany' => function ($q) use ($company_id) {
                            $q->where('company_id', $company_id);
                        }
                    ])
                        ->select('id', 'status', 'member_id', 'branch_id', 'first_name', 'last_name', 'company_id', 'mobile_no', 'company_id')
                        ->where('member_id', $associate_no)
                        ->where('status', 1)
                        ->where('is_block', 0)
                        ->first();
                    $ssbBalance = (double) $memberData['savingAccount_Custom3']['savingAccountBalance']->sum('deposit') - (double) $memberData['savingAccount_Custom3']['savingAccountBalance']->sum('withdrawal');
                    $investmentRecord = Memberinvestments::with(['branch', 'plan'])
                        ->where('customer_id', $memberData->id)
                        ->where('id', $investmentId)
                        ->where('investment_correction_request', 0)
                        ->where('renewal_correction_request', 0)
                        ->where('is_mature', 1)
                        ->first();
                    // if($memberData['savingAccount_Custom3']['company_id'] != $investmentRecord['company_id']) {
                    //     $status   = "Error";
                    //     $code     = 201;
                    //     $messages = 'Company Id is required!';
                    //     $result   = '';
                    //     return response()->json(compact('status', 'code', 'messages', 'result'), $code);
                    // }
                    if (isset($investmentRecord['id'])) {
                        $globaldate = checkMonthAvailability(date('d'), date('m'), date('Y'), $investmentRecord['branch']->state_id);
                        if($investmentRecord['maturity_date'] < $globaldate){
                            $status = "Failure";
                            $code = 201;
                            $messages = 'Renewal not allowed. Maturity is due.!';
                            $result = '';
                        }else{
                            if ($request->payment_mode == 0) {
                                $currBalance = 0;
                                $req = [
                                    'account_number' => $investmentRecord->account_number,
                                    'amount' => $investmentRecord->deposite_amount,
                                    'renewPlanId' => $investmentRecord->plan_id,
                                    'renewalDate' => $globaldate,
                                    'payment_mode' => $request->payment_mode
                                ];
                                $reply = CommanController::renewlimit((object) $req, $this->repository);
                                if ($reply['msg'] != null) {
                                    $status = "Success";
                                    $code = 201;
                                    $message = $reply['msg'];
                                    $result = '';
                                    return response()->json(compact('status', 'code', 'message', 'result'), $code);
                                } else {
                                    if ($ssbBalance < $deno_amount) {
                                        $status = "Success";
                                        $code = 201;
                                        $messages = 'Insufficient Balance!';
                                        $result = '';
                                        return response()->json(compact('status', 'code', 'messages', 'result'), $code);
                                    } else {
                                        $min_balance = 500;
                                        $newDenoAmount = $deno_amount + $min_balance;
                                        if ($ssbBalance < $newDenoAmount) {
                                            $status = "Success";
                                            $code = 201;
                                            $messages = 'Minimum Rs 500 should be in your SSB account!';
                                            $result = '';
                                            return response()->json(compact('status', 'code', 'messages', 'result'), $code);
                                        } else {
                                            Session::put('created_at', date("Y-m-d " . $entryTime . "", strtotime(convertDate($globaldate))));
                                            $record1 = SavingAccountTranscation::where('saving_account_id', $memberData['savingAccount_Custom3']->id)->wheredate('created_at', '<=', date("Y-m-d", strtotime(convertDate($globaldate))))->orderBy('id', 'desc')->orderBy('created_at', 'desc')->first();
                                            $ssbAccountAmount = $memberData['savingAccount_Custom3']->balance - $deno_amount;
                                            $ssb_id = $depositSSBId = $memberData['savingAccount_Custom3']->id;
                                            $sResult = SavingAccount::find($ssb_id);
                                            $sData['balance'] = $ssbAccountAmount;
                                            $sResult->update($sData);
                                            $created_at1 = date("Y-m-d H:i:s", strtotime(convertDate($globaldate)));
                                            $daybookRefRD = CommanAppEpassbookController::createBranchDayBookReferenceNew($deno_amount, $created_at1);
                                            $ssb['saving_account_id'] = $ssb_id;
                                            $ssb['account_no'] = $memberData['savingAccount_Custom3']->account_no;
                                            $ssb['opening_balance'] = $record1->opening_balance - $deno_amount;
                                            $ssb['withdrawal'] = $deno_amount;
                                            $mtssb = 'Amt. Transfer for renewal (' . $investmentRecord->account_number . ')';
                                            $ssb['description'] = $mtssb;
                                            $ssb['associate_id'] = $investmentRecord->associate_id;
                                            $ssb['branch_id'] = $investmentRecord->branch_id;
                                            $ssb['type'] = 6;
                                            $ssb['currency_code'] = 'INR';
                                            $ssb['payment_type'] = 'DR';
                                            $ssb['payment_mode'] = 4;
                                            $ssb['reference_no'] = $investmentRecord->account_number;
                                            $ssb['deposit'] = 0;
                                            $ssb['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($globaldate)));
                                            $ssb['is_app'] = 2;
                                            $ssb['daybook_ref_id'] = $daybookRefRD;
                                            $ssb['app_login_user_id'] = $memberData->id;
                                            $ssb['company_id'] = $company_id;
                                            $ssbAccountTran = SavingAccountTranscation::create($ssb);
                                            $ssbFromId = $memberData['savingAccount_Custom3']->id;
                                            $ssbAccountTranFromId = $ssbAccountTran->id;
                                            $ssb_id = $collectionSSBId = $memberData['savingAccount_Custom3']->id;
                                            $record2 = SavingAccountTranscation::where('account_no', $memberData['savingAccount_Custom3']->account_no)->wheredate('created_at', '>', date("Y-m-d", strtotime(convertDate($globaldate))))->get();
                                            foreach ($record2 as $key1 => $value) {
                                                $nsResult = SavingAccountTranscation::find($value->id);
                                                $nsResult['opening_balance'] = $value->opening_balance - $deno_amount;
                                                $nsResult->update($nsResult->toArray());
                                            }
                                            $encodeDate = json_encode($ssb);
                                            $arrs = array("saving_account_transaction_id" => $ssbAccountTran->id, "type" => "3", "account_head_id" => 0, "user_id" => $investmentRecord->id, "message" => "Renewal_Investment_Creation", "data" => $encodeDate);
                                            // $satRefId = CommanAppEpassbookController::createTransactionReferences($ssbAccountTran->id, $investmentRecord->member_id);
                                            $amountArraySsb = array('1' => $deno_amount);
                                            $createTransaction = NULL;
                                            $rplanId = $investmentRecord->plan_id;
                                            $descriptionRenewal = 'Renewal received from SSB ' . $memberData['savingAccount_Custom3']->account_no;
                                            $transactionData['is_renewal'] = 0;
                                            $transactionData['created_at'] = date("Y-m-d H:i:s", strtotime(str_replace('/', '-', $request['renewal_date'])));
                                            $currentDAte = date('Y-m-d H:i:s');
                                            $lastAmount = \App\Models\Daybook::where('investment_id', $investmentRecord->id)->whereIN('transaction_type', [2, 4, 15, 16, 17, 18, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30])->where('account_no', $investmentRecord->account_number)->whereDate('created_at', '<=', date("Y-m-d", strtotime(convertDate($globaldate))))->orderBy('id', 'desc')->orderby('created_at', 'desc')->first();

                                            if ($lastAmount) {
                                                $lastBalance = $lastAmount->opening_balance + $deno_amount;
                                            } else {
                                                $lastAmount = Daybook::where('investment_id', $investmentRecord->id)->whereIN('transaction_type', [2, 4, 15, 16, 17, 18, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30])->where('account_no', $investmentRecord->account_number)->whereDate('created_at', '<', date("Y-m-d", strtotime(convertDate($globaldate))))->orderby('id', 'desc')->orderby('created_at', 'desc')->first();
                                                //    dd($lastAmount);
                                                $lastBalance = $lastAmount->opening_balance + $deno_amount;
                                            }

                                            $createDayBook = CommanAppEpassbookController::createDayBookNew($createTransaction, $satRefId = NULL, 4, $investmentRecord->id, $investmentRecord->associate_id, $investmentRecord->member_id, $lastBalance, $deno_amount, $withdrawal = 0, $descriptionRenewal, $memberData['savingAccount_Custom3']->account_no, $investmentRecord->branch_id, $investmentRecord['branch']->branch_code, $amountArraySsb, 4, $memberData->first_name . ' ' . $memberData->last_name, $memberData->id, $investmentRecord->account_number, NULL, NULL, $investmentRecord['branch']->name, $globaldate, NULL, NULL, $collectionSSBId, 'CR', NULL, NULL, NULL, NULL, NULL, $memberData->id, 2, $daybookRefRD, $company_id);

                                            // dd($deno_amount, $globaldate, $investmentRecord->id, $investmentRecord->plan_id, NULL, NULL, NULL, NULL, NULL, NULL, NULL, $investmentRecord->branch_id, $investmentRecord->associate_id, $investmentRecord->member_id, $collectionSSBId, $createDayBook, 3, $investmentRecord->account_number, $collectionSSBId, $ssbAccountTranFromId, NULL, NULL, $memberData->id, $memberData->id, 2, $daybookRefRD,$company_id);

                                            $this->investHeadCreate( $deno_amount, $globaldate, $investmentRecord->id, $investmentRecord->plan_id, NULL, NULL, NULL, NULL, NULL, NULL, NULL, $investmentRecord->branch_id, $investmentRecord->associate_id, $investmentRecord->member_id, $collectionSSBId, $createDayBook, 3, $investmentRecord->account_number, $collectionSSBId, $ssbAccountTranFromId, NULL, NULL, $memberData->id, $memberData->id, 2, $daybookRefRD, $company_id );

                                            // $this->investHeadCreateSSB($request['amount'][$key], $globaldate, $request['investment_id'][$key], $planId, $cheque_no, $cheque_id, $cheque_date, $online_transction_no, $online_transction_date, $online_deposit_bank_id, $online_deposit_bank_ac_id, $branch_id,

                                            // $request['member_id'], $request['investment_member_id'][$key], $collectionSSBId, $ssbAccountTran->id, $pmodeAll, $ssbAccountNumber, $ssbFromId, $ssbAccountTranFromId, $ssbToId, $ssbAccountTranToId, $companyId);

                                            $data = [
                                                'member_id' => $memberData->id,
                                                'branch_id' => $investmentRecord->branch_id,
                                                'renew_investment_plan_id' => $investmentRecord->plan_id,
                                                'payment_mode' => 3
                                            ];
                                            $transaction = $this->transactionData($data, $investmentRecord->id, $deno_amount);
                                            $transaction['is_renewal'] = 0;
                                            $transaction['created_at'] = date("Y-m-d H:i:s", strtotime(str_replace('/', '-', $globaldate)));
                                            $ipTransaction = \App\Models\Investmentplantransactions::create($transaction);
                                            $updateInvestment = Memberinvestments::find($investmentId);
                                            $currAmount = $updateInvestment->current_balance + $deno_amount;
                                            $updateInvestment->update(['current_balance' => $currAmount]);
                                            $contactNumber = array();

                                            if ($memberData->mobile_no) {
                                                $contactNumber[] = str_replace('"', '', $memberData->mobile_no);
                                                $text = 'Dear Member, Your A/C ' . $investmentRecord->account_number . ' has been Credited on ' . date("d/m/Y", strtotime($globaldate)) . ' With Rs. ' . round($deno_amount, 2) . ' Cur Bal: ' . round($currAmount, 2) . '. Thanks Have a nice day';
                                                $templateId = 1207161726461603982;
                                                $sendToMember = new Sms();
                                                $sendToMember->sendSms($contactNumber, $text, $templateId);
                                            }
                                            $save = 1;
                                            $status = "Success";
                                            $code = '200';
                                            $messages = 'Renewal Successfully!';
                                            $result = $createDayBook;
                                        }
                                    }
                                }
                            } else {
                                $status = "Failure";
                                $code = 201;
                                $messages = 'Payment Mode Not Found !.';
                                $result = '';
                            }
                        }
                    } else {
                        $status = "Failure";
                        $code = 201;
                        $messages = 'Record Not Found!';
                        $result = '';
                    }
                } else {
                    $status = "Error";
                    $code = 201;
                    $messages = 'Invalid Api Token!';
                    $result = '';
                }
            }
            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            $status = "Error";
            $code = 500;
            $messages = $e->getMessage();
            $result = '';
        }
        return response()->json(compact('status', 'code', 'messages', 'result'), $code);
        // }
    }
    public function investHeadCreate($amount, $globaldate, $investmentId, $planId, $cheque_no, $cheque_id, $cheque_date, $online_transction_no, $online_transction_date, $online_deposit_bank_id, $online_deposit_bank_ac_id, $branch_id, $associate_id, $memberId, $ssbId, $createDayBook, $payment_mode, $investmentAccountNoRd, $ssbFromId, $ssbAccountTranFromId, $ssbToId, $ssbAccountTranToId, $authUser = NULL, $appuser, $isApp, $daybookRefRD, $company_id)
    {
        $amount = $amount;
        // $daybookRefRD=CommanAppEpassbookController::createBranchDayBookReferenceNew($amount,$globaldate);
        $refIdRD = $daybookRefRD;
        $currency_code = 'INR';
        $headPaymentModeRD = 0;
        $payment_type_rd = 'CR';
        $type_id = $investmentId;
        $entry_date = date("Y-m-d", strtotime(convertDate($globaldate)));
        $entry_time = date("H:i:s", strtotime(convertDate($globaldate)));
        $created_by = 4;
        $created_by_id = $authUser;
        $created_at = date("Y-m-d H:i:s", strtotime(convertDate($globaldate)));
        $updated_at = date("Y-m-d H:i:s", strtotime(convertDate($globaldate)));
        $planDetail = getPlanDetail($planId);
        $type = 3;
        $sub_type = 32;
        $planCode = $planDetail->plan_code;
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
        if ($payment_mode == 3) { // ssb
            $headPaymentModeRD = 3;
            $v_no = mt_rand(0, 999999999999999);
            $v_date = $entry_date;
            $ssb_account_id_from = $ssbId;
            $SSBDescTran = 'Amount transfer to ' . $planDetail->name . '(' . $investmentAccountNoRd . ') ';
            // $head1rdSSB = 1;
            // $head2rdSSB = 8;
            // $head3rdSSB = 20;
            // $head4rdSSB = 56;
            // $head5rdSSB = NULL;
            $ssbPlan = getPlanDetail($planId);
            $ssbDetals = SavingAccount::where('id', $ssb_account_id_from)->first();
            $getPlanmHead = \App\Models\Plans::whereCompanyId($ssbDetals->company_id)->first();
            $head4rdSSB = $getPlanmHead->deposit_head_id;
            $rdDesDR = $planDetail->name . '(' . $investmentAccountNoRd . ') A/c Dr ' . $amount . '/-';
            $rdDesCR = 'To SSB(' . $ssbDetals->account_no . ') A/c Cr ' . $amount . '/-';
            $rdDes = 'Amount received for ' . $planDetail->name . ' A/C Renewal (' . $investmentAccountNoRd . ') through SSB(' . $ssbDetals->account_no . ')';
            $rdDesMem = $planDetail->name . ' A/C Renewal (' . $investmentAccountNoRd . ')  through SSB(' . $ssbDetals->account_no . ')';
            // ssb  head entry -
            $allTranRDSSB = CommanAppEpassbookController::createAllHeadTransaction(
                $refIdRD,
                $branch_id,
                $bank_id = NULL,
                $bank_ac_id = NULL,
                $head4rdSSB,
                4,
                47,
                $ssb_account_id_from,
                $ssbAccountTranFromId,
                $associate_id,
                $memberId,
                $branch_id_to = NULL,
                $branch_id_from = NULL,
                $amount,
                $SSBDescTran,
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
                $appuser,
                $isApp,
                $company_id
            );
            // $memberTranInvest77 = CommanAppEpassbookController::memberTransactionNew($refIdRD, '4', '47', $ssb_account_id_from, $ssbAccountTranFromId, $associate_id, $ssbDetals->member_id, $branch_id, $bank_id = NULL, $account_id = NULL, $amount, $SSBDescTran, 'DR', $headPaymentModeRD, $currency_code, $amount_to_id = $type_id, $amount_to_name = $planDetail->name, $amount_from_id = NULL, $amount_from_name = NULL, $v_no, $v_date, $ssb_account_id_from = NULL, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL, $appuser, $isApp);
        }
        // Investment head entry +
        $allTranInvest = CommanAppEpassbookController::createAllHeadTransaction($refIdRD, $branch_id, $bank_id = NULL, $bank_ac_id = NULL, $head_id, $type, $sub_type, $type_id, $createDayBook, $associate_id, $memberId, $branch_id_to = NULL, $branch_id_from = NULL, $amount, $rdDes, $payment_type_rd, $headPaymentModeRD, $currency_code, $jv_unique_id = NULL, $v_no, $ssb_account_id_from, $ssb_account_id_to = NULL, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $cheque_type = NULL, $cheque_id = NULL, $cheque_no, $transction_no, $created_by, $created_by_id, $appuser, $isApp, $company_id);
        //branch day book entry +
        $daybookInvest = CommanAppEpassbookController::branchDayBookNew($refIdRD, $branch_id, $type, $sub_type, $type_id, $createDayBook, $associate_id, $memberId, $branch_id_to = NULL, $branch_id_from = NULL, $amount, $rdDes, $rdDesDR, $rdDesCR, $payment_type_rd, $headPaymentModeRD, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $created_by, $created_by_id, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL, $appuser, $isApp, $company_id);
        // Member transaction  +
        // $memberTranInvest = CommanAppEpassbookController::memberTransactionNew($refIdRD,$type,$sub_type,$type_id,$createDayBook,$associate_id,$memberId,$branch_id,$bank_id=NULL,$account_id=NULL,$amount,$rdDesMem,$payment_type_rd,$headPaymentModeRD,$currency_code,$amount_to_id=NULL,$amount_to_name=NULL,$amount_from_id=NULL,$amount_from_name=NULL,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at,$ssb_account_tran_id_to=NULL,$ssb_account_tran_id_from=NULL,$jv_unique_id=NULL,$cheque_type=NULL,$cheque_id=NULL,$appuser,$isApp);
        /******** Balance   entry ***************/
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
    public function getSavingAccountDetails($mId)
    {
        $getDetails = SavingAccount::where('customer_id', $mId)->select('id', 'balance', 'account_no')->first();
        return $getDetails;
    }
    public function transactionData($request, $investmentId, $amount)
    {
        $globaldate = Session::get('created_at');
        $getBranchId = getBranchDetail($request['branch_id']);
        $branch_id = $getBranchId->id;
        $branchCode = $getBranchId->branch_code;
        $sAccount = $this->getSavingAccountDetails($request['member_id']);
        $data['investment_id'] = $investmentId;
        $data['plan_id'] = $request['renew_investment_plan_id'];
        $data['member_id'] = $request['member_id'];
        $data['branch_id'] = $branch_id;
        $data['branch_code'] = $branchCode;
        $data['deposite_amount'] = $amount;
        $data['deposite_date'] = date("Y-m-d", strtotime(convertDate($globaldate)));
        $data['deposite_month'] = date("m", strtotime(str_replace('/', '-', $globaldate)));
        $data['payment_mode'] = $request['payment_mode'];
        if ($sAccount->id) {
            $data['saving_account_id'] = $sAccount->id;
        } else {
            $data['saving_account_id'] = NULL;
        }
        return $data;
    }
}
