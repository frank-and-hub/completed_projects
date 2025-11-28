<?php
namespace App\Http\Controllers\Api;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use DB;
use App\Http\Controllers\Api\Epassbook\CommanAppEpassbookController;
use App\Http\Controllers\Admin\CommanController;
use Session;
use App\Models\{Member,Transcation,Daybook,SavingAccount,SavingAccountTranscation,TransactionReferences,Investmentplantransactions,CorrectionLog,CorrectionRequests,Memberinvestments,SavingAccountBalannce};
use App\Services\Sms;
class RenewalAssociateController extends Controller
{
    public function __construct(Request $request)
    {
        $this->member = \App\Models\Member::with(['savingAccount_Customnew.savingAccountBalance','branch'])->select('id', 'associate_app_status', 'member_id', 'branch_id', 'associate_code')->where('associate_no', $request->associate_no)->where('associate_status', 1)->where('is_block', 0)->first();
        if (isset($this->member->savingAccount_Customnew[0]->savingAccountBalance) && $this->member->savingAccount_Customnew[0]->savingAccountBalance != null) {
            $this->ssbBalance = $this->member->savingAccount_Customnew[0]->savingAccountBalance->sum('deposit') - $this->member->savingAccount_Customnew[0]->savingAccountBalance->sum('withdrawal');
            $this->globaldate = checkMonthAvailability(date('d'), date('m'), date('Y'), $this->member->branch->state_id);
        }
        // dd($this->ssbBalance,$this->globaldate);
    }
    /**
     * Get Investment Plan Detail
     * Get Invetsment Details using Account Number
     * @param \App\Request  $request
     * @return \Illuminate\Http\Response
     */
    //code comented by Sourab on 08-06-2023
    // public function getInvetmentDetails(Request $request)
    // {
    //     $associate_no = $request->associate_no;
    //     $member = $this->member;
    //     $accountNumber = explode(",", $request->account_number);
    //     try {
    //         if ($member) {
    //             $token  = md5($associate_no);
    //             if ($token == $request->token) {
    //                 if (empty($request->company_id)) {
    //                     $status = 'Error';
    //                     $code   = 500;
    //                     $messages = 'Account Details';
    //                     $result = $data ?? 'Please Submit Company Id';
    //                     return response()->json(compact('status', 'code', 'messages', 'result'), $code);
    //                 }
    //                 $investmentDetails = Memberinvestments::with('member')->select('id', 'plan_id', 'account_number', 'is_mature', 'maturity_date', 'created_at', 'deposite_amount', 'member_id', 'customer_id')->where('associate_id', $member->id)->doesntHave('demandadvice')
    //                     ->whereIn('account_number', $accountNumber)
    //                     ->where('company_id', $request->company_id)
    //                     ->get()->keyby('account_number');
    //                 $data = array();
    //                 foreach ($accountNumber as $key =>  $value) {
    //                     if (isset($investmentDetails[$value]) && $investmentDetails[$value]->is_mature == 1) {
    //                         $data[$key] = [
    //                             'account_number' => $investmentDetails[$value]->account_number,
    //                             'account_holder_name' => $investmentDetails[$value]->member->first_name . ' ' . $investmentDetails[$value]->member->last_name ?? '',
    //                             'plan_name' => $investmentDetails[$value]->plan->name,
    //                             'deno_amount' => $investmentDetails[$value]->deposite_amount ?? 0,
    //                             'is_multiple' => 0,
    //                             'is_mature' => $investmentDetails[$value]->is_mature,
    //                             'maturity_date' => $investmentDetails[$value]->maturity_date,
    //                             'message' =>  $value . ' Exist',
    //                             'is_exist' => 1,
    //                         ];
    //                     } else {
    //                         $data[$key] = [
    //                             'account_number' => $value,
    //                             'account_holder_name' => '',
    //                             'plan_name' => '',
    //                             'deno_amount' => '',
    //                             'is_multiple' => '',
    //                             'is_mature' => '',
    //                             'maturity_date' => '',
    //                             'message' =>  'Please Check A/C No. Matured Or Not Found ',
    //                             'is_exist' => 0,
    //                         ];
    //                     }
    //                 }
    //                 $companyId = $request->company_id;
    //                 $associateNo = $request->associate_no;
    //                 // $newMember = \App\Models\Member::with(['savingAccount_Custom3'=>function($q) use($companyId){
    //                 //     $q->with('getSSBAccountBalance')->where('company_id',$companyId);
    //                 // },'savingAccount_Custom'=>function($q)use($companyId){
    //                 //     $q->with('getSSBAccountBalance')->where('company_id',$companyId);
    //                 // },'customerSSB'])                       
    //                 //     ->where('associate_no', $associateNo)
    //                 //     ->where('associate_status', 1)
    //                 //     ->where('is_block', 0)
    //                 //     ->first();
    //                     $newMember = \App\Models\Member::with('customerSSB')->                     
    //                     where('associate_no', $associateNo)
    //                     ->where('associate_status', 1)
    //                     ->where('is_block', 0)
    //                     ->first();
    //                   dd($newMember);
    //                 $cbalance = isset($newMember['savingAccount_Custom3']['getSSBAccountBalance']['totalBalance'])?$newMember['savingAccount_Custom3']['getSSBAccountBalance']['totalBalance'] : 0;
    //                 $currentBalance = isset($cbalance) ? number_format((float)$cbalance, 2, '.', '') : 0;
    //                 $is_ssb_available = isset($newMember['savingAccount_Custom']->id)? 1:0;
    //                 $status = 'Success';
    //                 $code   = 200;
    //                 $messages = 'Account Details';
    //                 $result = $data ?? 'No Record Found';
    //                 $associate_status = $member->associate_app_status;
    //             } else {
    //                 $currentBalance =  number_format((float)$this->ssbBalance, 2, '.', '');
    //                 $status = "Error";
    //                 $code = 419;
    //                 $messages = "Token Mismatch";
    //                 $result = '';
    //                 $associate_status = 9;
    //                 $is_ssb_available = '';
    //             }
    //         }
    //         DB::commit();
    //     } catch (Exception $error) {
    //         $status = "Error";
    //         $code = 500;
    //         $messages = $error->getMessage();
    //         $result = '';
    //         $associate_status = 9;
    //         $currentBalance = number_format((float)0, 2, '.', '');
    //     }
    //     return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status', 'currentBalance','is_ssb_available'), $code);
    // }
    public function getInvetmentDetails(Request $request)
    {
        $associateNo = $request->associate_no;
        $member = $this->member;
        $accountNumber = explode(",", $request->account_number);
		$companyId = $request->company_id;
        try {
            if ($member) {
                $token  = md5($associateNo);
                if ($token == $request->token) {
                    if (empty($companyId)) {
                        $status = 'Error';
                        $code   = 500;
                        $messages = 'Account Details';
                        $result = $data ?? 'Please Submit Company Id';
                        return response()->json(compact('status', 'code', 'messages', 'result'), $code);
                    }
                    $investmentDetails = Memberinvestments::with('member')
						->select('id', 'plan_id', 'account_number', 'is_mature', 'maturity_date', 'created_at', 'deposite_amount', 'member_id', 'customer_id')
						->where('associate_id', $member->id)
						->doesntHave('demandadvice')
                        ->whereIn('account_number', $accountNumber)
                        ->where('company_id', $companyId)
                        ->get()
						->keyby('account_number');
                    $data = array();
					$newMember = \App\Models\Member::with(['savingAccount_Custom3'=>function($q) use($companyId){
                        $q->with('getSSBAccountBalance')->where('company_id',$companyId);
                    },'savingAccount_Custom'=>function($q)use($companyId){
                        $q->with('getSSBAccountBalance')->where('company_id',$companyId);
                    }])                       
                        ->where('associate_no', $associateNo)
                        ->where('associate_status', 1)
                        ->where('is_block', 0)
                        ->first();
					$getSSb =  SavingAccount::where('customer_id',$newMember->id)->where('company_id',$companyId)->first(); 
                    foreach ($accountNumber as $key =>  $value) {
                        if (isset($investmentDetails[$value]) && $investmentDetails[$value]->is_mature == 1) {
                        $ssbBalance = SavingAccountBalannce::where('account_no',$getSSb->account_no)->where('saving_account_id',$getSSb->id)->first();
                            $data[$key] = [
                                'account_number' => $investmentDetails[$value]->account_number,
                                'account_holder_name' => $investmentDetails[$value]->member->first_name . ' ' . $investmentDetails[$value]->member->last_name ?? '',
                                'plan_name' => $investmentDetails[$value]->plan->name,
                                'deno_amount' => $investmentDetails[$value]->deposite_amount ?? 0,
                                'is_multiple' => 0,
                                'is_mature' => $investmentDetails[$value]->is_mature,
                                'maturity_date' => $investmentDetails[$value]->maturity_date,
                                'message' =>  $value . ' Exist',
                                'is_exist' => 1,
								'ssb_account_no' => $getSSb ? $getSSb->account_no : '0',
								'ssb_amount' => $ssbBalance ? $ssbBalance->totalBalance : '0',
                            ];
                        } else {
                            $data[$key] = [
                                'account_number' => $value,
                                'account_holder_name' => '',
                                'plan_name' => '',
                                'deno_amount' => '',
                                'is_multiple' => '',
                                'is_mature' => '',
                                'maturity_date' => '',
                                'message' => 'Please Check A/C No. Matured Or Not Found ',
                                'is_exist' => 0,
								'ssb_account_no' => '0',
								'ssb_amount' => '0',
                            ];
                        }
                    }
                    $cbalance = isset($newMember['savingAccount_Custom3']['getSSBAccountBalance']['totalBalance'])?$newMember['savingAccount_Custom3']['getSSBAccountBalance']['totalBalance'] : 0;
                    $currentBalance = isset($cbalance) ? number_format((float)$cbalance, 2, '.', '') : 0;
                    $is_ssb_available = isset($getSSb->id)? 1:0;
                    $status = 'Success';
                    $code   = 200;
                    $messages = 'Account Details';
                    $result = $data ?? 'No Record Found';
                    $associate_status = $member->associate_app_status;
                } else {
                    $currentBalance =  number_format((float)$this->ssbBalance, 2, '.', '');
                    $status = "Error";
                    $code = 419;
                    $messages = "Token Mismatch";
                    $result = '';
                    $associate_status = 9;
                    $is_ssb_available = '';
                }
            }
            DB::commit();
        } catch (Exception $error) {
            $status = "Error";
            $code = 500;
            $messages = $error->getMessage();
            $result = '';
            $associate_status = 9;
            $currentBalance = number_format((float)0, 2, '.', '');
        }
        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status', 'currentBalance','is_ssb_available'), $code);
    }
    /**
     * Submit Renewal
     * @param mixed $accountNumber,$paymentMode,$amount
     */
    // public function submitRenewals(Request $request)
    // {
    //     // dd($this->member);
    //     $member = \App\Models\Member::with('savingAccount_CustomAssociate.savingAccountBalance')->select('id', 'associate_app_status', 'branch_id','associate_code')->where('associate_no', $request->associate_no)->where('associate_status', 1)->where('is_block', 0)->first();
    //    dd($member);
    //     $ssbBalance = $member->savingAccount_Custom->savingAccountBalance->sum('deposit') - $member->savingAccount_Custom->savingAccountBalance->sum('withdrawal');
    //     $globaldate = checkMonthAvailability(date('d'), date('m'), date('Y'),  $member->branch->state_id);
    //     dd($ssbBalance);
    //     $associate_no = $request['associate_no'];
    //     $member = $member;
    //     $accountNumber = $request['data'];
    //     $branchDaybookTransactions = array();
    //     $allHeadTransactions = array();
    //     $memberTransactions = array();
    //     $ssbTransactions = array();
    //     $bankTransactions = array();
    //     $entryTime = date("H:i:s");
    //     $globaldate = $globaldate;
    //     $ssbBalance =  $ssbBalance;
    //     $company_id =  $request->company_id;
    //     $accountNumberDetails = json_decode($accountNumber);
    //     DB::beginTransaction();
    //     try {
    //         if ($member) {
    //             $token  = md5($associate_no);
    //             // dd($token);
    //             if ($token == $request->token) {
    //                 if (empty($request->company_id)) {
    //                     $status = 'Error';
    //                     $code   = 500;
    //                     $messages = 'Account Details';
    //                     $result = $data ?? 'Please Submit Company Id';
    //                     return response()->json(compact('status', 'code', 'messages', 'result'), $code);
    //                 }
    //                 foreach ($accountNumberDetails as $key => $account) {
    //                     $investmentRecord =  Memberinvestments::where('account_number', $account->account_number)->where('investment_correction_request', 0)->where('renewal_correction_request', 0)->where('company_id', $request->company_id)->where('is_mature', 1)->first();
    //                     $deno_amount = $investmentRecord['deposite_amount'];
    //                     $mobileNumber = $investmentRecord->member->mobile_no;
    //                     if (isset($investmentRecord->id)) {
    //                         $amountArraySsb = array('1' => $deno_amount);
    //                         if ($request['payment_mode'] == 0) {
    //                             $currBalance  = 0;
    //                             if ($ssbBalance < $deno_amount) {
    //                                 $status   = "Success";
    //                                 $code     = 201;
    //                                 $message = 'Insufficient Balance!';
    //                                 $result   = '';
    //                             } else {
    //                                 $min_balance = 500;
    //                                 $newDenoAmount = $deno_amount + $min_balance;
    //                                 // dd($newDenoAmount);
    //                                 if ($ssbBalance < $newDenoAmount) {
    //                                     $status   = "Success";
    //                                     $code     = 201;
    //                                     $message = 'Minimum Rs 500 should be in your SSB account!';
    //                                     $result   = '';
    //                                 } else {
    //                                     Session::put('created_at', date("Y-m-d " . $entryTime . "", strtotime(convertDate($globaldate))));
    //                                     if ($investmentRecord->plan_id == 1) {
    //                                         $sAccountId = $this->getSavingAccountDetails($investmentRecord->member_id);
    //                                         if ($sAccountId) {
    //                                             $ssb_id = $sAccountId->id;
    //                                             $ssbAccountNumber = $sAccountId->account_no;
    //                                         } else {
    //                                             $ssb_id = NULL;
    //                                             $ssbAccountNumber = NULL;
    //                                         }
    //                                         $savingAccountDetail = $sAccount = $this->getSavingAccountDetails($member->id);
    //                                         if ($savingAccountDetail) {
    //                                             $renewSavingOpeningBlanace = $savingAccountDetail->balance;
    //                                         } else {
    //                                             $renewSavingOpeningBlanace = NULL;
    //                                         }
    //                                         if ($investmentRecord) {
    //                                             $sResult = \App\Models\Memberinvestments::find($investmentRecord->id);
    //                                             $totalbalance = $investmentRecord->current_balance + $deno_amount;
    //                                             $investData['current_balance'] = $totalbalance;
    //                                             $sResult->update($investData);
    //                                         } else {
    //                                             $totalbalance = '';
    //                                         }
    //                                         $mtssb = 'Renewal received from SSB ' . $sAccount->account_no;
    //                                         $record1 = \App\Models\SavingAccountTranscation::where('account_no', $sAccount->account_no)->where('created_at', '<', $globaldate)->first();
    //                                         if (empty($record1)) {
    //                                             $response = array(
    //                                                 'status' => 'alert',
    //                                                 'msg'    =>  'Renew date should less than created date',
    //                                             );
    //                                             return Response::json($response);
    //                                         }
    //                                         $refTransactions['amount'] =  $deno_amount;
    //                                         $refTransactions['entry_date'] =  $globaldate;
    //                                         $refTransactions['entry_time'] =  $entryTime;
    //                                         $transcation = \App\Models\BranchDaybookReference::insertGetId($refTransactions);
    //                                         $rno = $sAccount->account_no;
    //                                         $ssbAccountAmount = $sAccount->balance - $deno_amount;
    //                                         $ssb_id = $collectionSSBId = $sAccount->id;
    //                                         $sResult = \App\Models\SavingAccount::find($ssb_id);
    //                                         $sData['balance'] = $ssbAccountAmount;
    //                                         $sResult->update($sData);
    //                                         $ssb['saving_account_id'] = $savingAccountDetail->id;
    //                                         $ssb['account_no'] = $sAccount->account_no;
    //                                         if ($record1) {
    //                                             $drSSbBalancd = $ssbAccountAmount;
    //                                         } else {
    //                                             $drSSbBalancd = $deno_amount;
    //                                         }
    //                                         $ssb['opening_balance'] = $drSSbBalancd;
    //                                         $ssb['withdrawal'] = $deno_amount;
    //                                         $ssb['description'] = 'SSb Withdrawal for renewal ' . $savingAccountDetail->account_no;
    //                                         $ssb['associate_id'] = $member->id;
    //                                         $ssb['branch_id'] = $investmentRecord->branch_id;
    //                                         $ssb['type'] = 6;
    //                                         $ssb['currency_code'] = 'INR';
    //                                         $ssb['payment_type'] = 'DR';
    //                                         $ssb['payment_mode'] = 4;
    //                                         $ssb['deposit'] = NULL;
    //                                         $ssb['is_renewal'] = 0;
    //                                         $ssb['is_app'] = 1;
    //                                         $ssb['created_at'] = $globaldate;;
    //                                         $ssb['daybook_ref_id'] = $transcation;
    //                                         $ssbAccountTran = \App\Models\SavingAccountTranscation::create($ssb);
    //                                         $ssbFromId = $ssb_id;
    //                                         $ssbAccountTranFromId = $ssbAccountTran->id;
    //                                         $encodeDate = json_encode($ssb);
    //                                         $record3 = \App\Models\SavingAccountTranscation::where('account_no', $sAccount->account_no)->where('created_at', '<', $globaldate)->first();
    //                                         if (empty($record3)) {
    //                                             $response = array(
    //                                                 'status' => 'alert',
    //                                                 'msg'    =>  'Renew date should less than created date',
    //                                             );
    //                                             return Response::json($response);
    //                                         }
    //                                         $ssbAccountAmount = $sAccountId->balance + $deno_amount;
    //                                         $ssb_id = $depositSSBId = $sAccountId->id;
    //                                         $sResult = \App\Models\SavingAccount::find($ssb_id);
    //                                         $sData['balance'] = $ssbAccountAmount;
    //                                         $sResult->update($sData);
    //                                         $ssb['saving_account_id'] = $sAccountId->id;
    //                                         $ssb['account_no'] = $account['account_number'];
    //                                         if ($record1) {
    //                                             $ssbAmountBalcne = $ssbAccountAmount;
    //                                         } else {
    //                                             $ssbAmountBalcne = $deno_amount;
    //                                         }
    //                                         $ssb['opening_balance'] = $ssbAmountBalcne;
    //                                         // $ssb['opening_balance'] = $sAccountId->opening_balance + $deno_amount;
    //                                         $ssb['withdrawal'] = 0;
    //                                         $ssb['description'] = $mtssb;
    //                                         $ssb['associate_id'] = $member->id;
    //                                         $ssb['branch_id'] = $investmentRecord->branch_id;
    //                                         $ssb['type'] = 2;
    //                                         $ssb['currency_code'] = 'INR';
    //                                         $ssb['payment_type'] = 'CR';
    //                                         $ssb['payment_mode'] = 4;
    //                                         $ssb['reference_no'] = $rno;
    //                                         $ssb['deposit'] = $deno_amount;
    //                                         $ssb['created_at'] = $globaldate;
    //                                         $ssb['is_app'] = 1;
    //                                         $ssb['daybook_ref_id'] = $transcation;
    //                                         $ssbAccountTran = \App\Models\SavingAccountTranscation::create($ssb);
    //                                         $ssbToId = $sAccountId->id;
    //                                         $ssbAccountTranToId = $ssbAccountTran->id;
    //                                         $encodeDate = json_encode($ssb);
    //                                         $lastAmount = Daybook::where('investment_id', $investmentRecord->id)->whereIN('transaction_type', [2, 4, 16, 17, 18])->where('account_no', $investmentRecord->account_number)->whereDate('created_at', '<=', $globaldate)->orderBy('id', 'desc')->first();
    //                                         if ($lastAmount) {
    //                                             $lastBalance = $lastAmount->opening_balance + $deno_amount;
    //                                         } else {
    //                                             $lastAmount = Daybook::/*where('investment_id',$request['investment_id'][$key])->*/where('account_no', $investmentRecord->account_number)->whereIN('transaction_type', [1])->whereDate('created_at', '<', $globaldate)->orderby('id', 'desc')->first();
    //                                             if ($lastAmount) {
    //                                                 $lastBalance = $lastAmount->opening_balance + $deno_amount;
    //                                             } else {
    //                                                 $lastOpeningAmount = Daybook::where('account_no', $investmentRecord->account_number)->whereIN('transaction_type', [1])->orderby('id', 'desc')->first();
    //                                                 //-----------update -----
    //                                                 $lastOpeningAmount = SavingAccountTranscation::where('account_no', $investmentRecord->account_number)->orderby('id', 'desc')->first();
    //                                                 $lastBalance = $lastOpeningAmount->opening_balance + $deno_amount;
    //                                             }
    //                                         }
    //                                         if ($lastAmount) {
    //                                             $nextRenewal = $lastAmount = Daybook::where('investment_id', $investmentRecord->id)->whereIN('transaction_type', [1])->where('account_no', $investmentRecord->account_number)->whereDate('created_at', '>', date("Y-m-d", strtotime(convertDate($lastAmount->created_at))))->get();
    //                                             foreach ($nextRenewal as $key1 => $value) {
    //                                                 $daybookData['opening_balance'] = $value->opening_balance + $deno_amount;
    //                                                 $dayBook = Daybook::find($value->id);
    //                                                 $dayBook->update($daybookData);
    //                                             }
    //                                         }
    //                                         $createDayBook = CommanAppEpassbookController::createDayBookNew($transcation, NULL, 2, $investmentRecord->id, $investmentRecord->associate_id, $investmentRecord->member_id, $lastBalance, $deno_amount, $withdrawal = 0, $ssb['description'], $ssbAccountNumber, $investmentRecord->branch_id, $investmentRecord->branch->branch_code, $amountArraySsb, 4, $member->first_name . ' ' . $member->last_name ?? '', $member->id, $investmentRecord->account_number, NULL, NULL, $investmentRecord->branch->name, $globaldate, NULL, $online_payment_by = NULL, $collectionSSBId, 'CR', NULL, NULL, NULL, NULL, NULL, $member->id, 1, $transcation, 3);
    //                                         $planId = $investmentRecord->plan_id;
    //                                         $this->investHeadCreateSSB($deno_amount, $globaldate, $investmentRecord->id, $planId, NULL, NULL, NULL, NULL, NULL, NULL, NULL, $investmentRecord->branch_id, $investmentRecord->associate_id, $investmentRecord->member_id, $savingAccountDetail->id, $ssbAccountTran->id, 4, $ssbAccountNumber, $ssbFromId, $ssbAccountTranFromId, $ssbToId, $ssbAccountTranToId, $transcation, $member->id, 1);
    //                                         //--------------------------------HEAD IMPLEMENT ------------------------
    //                                         $daybookData['is_renewal'] = 0;
    //                                         $dayBook = Daybook::find($createDayBook);
    //                                         //$dayBook->update($daybookData);
    //                                         /*--------------------cheque assign -----------------------*/
    //                                         $investmentCurrentBalance = getSavingCurrentBalance($ssb_id, $ssbAccountNumber);
    //                                         $save = 1;
    //                                         $rAmount = $investmentCurrentBalance;
    //                                         $updateInvestment = Memberinvestments::where('account_number', $account['account_number'])->update(['current_balance' => $ssbAmountBalcne]);
    //                                         $updateInvestment = Memberinvestments::where('account_number', $sAccount->account_no)->update(['current_balance' => $drSSbBalancd]);
    //                                     } else {
    //                                         dd("hi");   
    //                                         $record1 = SavingAccountTranscation::where('saving_account_id', $member->savingAccount_Custom->id)->wheredate('created_at', '<=', $globaldate)->orderBy('id', 'desc')->orderBy('created_at', 'desc')->first();
    //                                         $ssbAccountAmount = $member->savingAccount_Custom->balance - $deno_amount;
    //                                         $ssb_id = $depositSSBId = $member->savingAccount_Custom->id;
    //                                         $sResult = SavingAccount::find($ssb_id);
    //                                         $sData['balance'] = $ssbAccountAmount;
    //                                         $sResult->update($sData);
    //                                         $created_at1 = date("Y-m-d H:i:s", strtotime(convertDate($globaldate)));
    //                                         $daybookRefRD = CommanAppEpassbookController::createBranchDayBookReferenceNew($deno_amount, $created_at1);
    //                                         $ssb['saving_account_id'] = $ssb_id;
    //                                         $ssb['account_no'] = $member->savingAccount_Custom->account_no;
    //                                         $ssb['opening_balance'] = (double)$record1->opening_balance - (double)$deno_amount;
    //                                         $ssb['withdrawal'] = $deno_amount;
    //                                         $mtssb = 'SSb Withdrawal for renewal (' . $investmentRecord->account_number . ')';
    //                                         $ssb['description'] = $mtssb;
    //                                         $ssb['associate_id'] = $investmentRecord->associate_id;
    //                                         $ssb['branch_id'] = $investmentRecord->branch_id;
    //                                         $ssb['type'] = 6;
    //                                         $ssb['currency_code'] = 'INR';
    //                                         $ssb['payment_type'] = 'DR';
    //                                         $ssb['payment_mode'] = 4;
    //                                         $ssb['reference_no'] = $investmentRecord->account_number;
    //                                         $ssb['deposit'] = 0;
    //                                         $ssb['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($globaldate)));
    //                                         $ssb['is_app'] = 1;
    //                                         $ssb['daybook_ref_id'] = $daybookRefRD;
    //                                         $ssb['app_login_user_id'] = $member->id;
    //   $ssb['company_id'] = $company_id;
    //                                         $ssbAccountTran = SavingAccountTranscation::create($ssb);
    //                                         $ssbFromId = $member->savingAccount_Custom->id;
    //                                         $ssbAccountTranFromId = $ssbAccountTran->id;
    //                                         $ssb_id = $collectionSSBId = $member->savingAccount_Custom->id;
    //                                         $record2 = SavingAccountTranscation::where('account_no', $member->savingAccount_Custom->account_no)->wheredate('created_at', '>', $globaldate)->get();
    //                                         foreach ($record2 as $key1 => $value) {
    //                                             $nsResult = SavingAccountTranscation::find($value->id);
    //                                             $nsResult['opening_balance'] = (double)$value->opening_balance - (double)$deno_amount;
    //                                             $nsResult->update($nsResult->toArray());
    //                                         }
    //                                         $encodeDate = json_encode($ssb);
    //                                         $satRefId = NULL;
    //                                         $amountArraySsb = array('1' => $deno_amount);
    //                                         $createTransaction = NULL;
    //                                         $rplanId = $investmentRecord->plan_id;
    //                                         $descriptionRenewal = 'Renewal received from SSB ' . $member->savingAccount_Custom->account_no;
    //                                         $transactionData['is_renewal'] = 0;
    //                                         $transactionData['created_at'] = $globaldate;
    //                                         $currentDAte = date('Y-m-d H:i:s');
    //                                         $lastAmount = \App\Models\Daybook::where('investment_id', $investmentRecord->id)->whereIN('transaction_type', [2, 4, 15, 16, 17, 18, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30])->where('account_no', $investmentRecord->account_number)->whereDate('created_at', '<=', $globaldate)->orderBy('id', 'desc')->orderby('created_at', 'desc')->first();
    //                                         if ($lastAmount) {
    //                                             $lastBalance = (double)$lastAmount->opening_balance + (double)$deno_amount;
    //                                         } else {
    //                                             $lastAmount = Daybook::where('investment_id', $investmentRecord->id)->whereIN('transaction_type', [2, 4, 15, 16, 17, 18, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30])->where('account_no', $investmentRecord->account_number)->whereDate('created_at', '<', $globaldate)->orderby('id', 'desc')->orderby('created_at', 'desc')->first();
    //                                             if(isset($lastAmount->opening_balance)){
    //                                                 $lastBalance = (double)$lastAmount->opening_balance + (double)$deno_amount;
    //                                             }else{
    //                                                 $lastBalance = (double)$deno_amount;
    //                                             }
    //                                         }
    //                                         $createDayBook = CommanAppEpassbookController::createDayBookNew($createTransaction, $satRefId, 4, $investmentRecord->id, $investmentRecord->associate_id, $investmentRecord->member_id, $lastBalance, $deno_amount, $withdrawal = 0, $descriptionRenewal, $member->savingAccount_Custom->account_no, $investmentRecord->branch_id, $investmentRecord['branch']->branch_code, $amountArraySsb, 4, $member->first_name . ' ' . $member->last_name, $member->id, $investmentRecord->account_number, NULL, NULL, $investmentRecord['branch']->name, $globaldate, NULL, NULL, $collectionSSBId, 'CR', NULL, NULL, NULL, NULL, NULL, $member->id, 1, $daybookRefRD,$company_id);
    //                                         $this->investHeadCreate($deno_amount, $globaldate, $investmentRecord->id, $investmentRecord->plan_id, NULL, NULL, NULL, NULL, NULL, NULL, NULL, $investmentRecord->branch_id, $investmentRecord->associate_id, $investmentRecord->member_id, $collectionSSBId, $createDayBook, 3, 
    //                                         $investmentRecord->account_number, $collectionSSBId, $ssbAccountTranFromId, NULL, NULL, $member->id, $member->id, 1, $daybookRefRD,$company_id);
    //                                         $data = [
    //                                             'member_id' => $member->id,
    //                                             'branch_id' => $investmentRecord->branch_id,
    //                                             'renew_investment_plan_id' => $investmentRecord->plan_id,
    //                                             'payment_mode' => 3
    //                                         ];
    //                                         // $transaction = $this->transactionData($data, $investmentRecord->id, $deno_amount);
    //                                         $getBranchId = getBranchDetail( $data['branch_id']);
    //                                         $branch_id = $getBranchId->id;
    //                                         $branchCode = $getBranchId->branch_code;
    //                                         //$branchCode=$getBranchCode->branch_code;
    //                                         // dd($investmentRecord);
    //                                         $sAccount = $member->savingAccount_Custom;
    //                                         $data['investment_id'] = $investmentRecord->id;
    //                                         $data['plan_id'] = $data['renew_investment_plan_id'];
    //                                         $data['member_id'] = $data['member_id'];
    //                                         $data['branch_id'] = $branch_id;
    //                                         $data['branch_code'] = $branchCode;
    //                                         $data['deposite_amount'] = $deno_amount;
    //                                         $data['deposite_date'] = date("Y-m-d", strtotime(convertDate($globaldate)));
    //                                         $data['deposite_month'] = date("m", strtotime(str_replace('/', '-', $globaldate)));
    //                                         $data['payment_mode'] = $data['payment_mode'];
    //                                         if ($sAccount->id) {
    //                                             $data['saving_account_id'] = $sAccount->id;
    //                                         } else {
    //                                             $data['saving_account_id'] = NULL;
    //                                         }
    //                                         $transaction =  $data;
    //                                         // dd("hel");
    //                                         $transaction['is_renewal'] = 0;
    //                                         $transaction['created_at'] = date("Y-m-d H:i:s", strtotime(str_replace('/', '-', $globaldate)));
    //                                         // $updateInvestment = Memberinvestments::find($investmentRecord->id);
    //                                         // $updateInvestments = Memberinvestments::find($member->savingAccount_Custom->member_investments_id);
    //                                         // $updateInvestments->update(['current_balance' => $updateInvestments->current_balance - $deno_amount]);
    //                                         // $currAmount = $updateInvestment->current_balance + $deno_amount;
    //                                         // $updateInvestment->update(['current_balance' => $currAmount]);
    //                                         $updateInvestment = Memberinvestments::find($investmentRecord->id);
    //                                         $updateInvestments = Memberinvestments::find($member->savingAccount_Custom->member_investments_id);
    //                                         $updateInvestments->update(['current_balance' => (double)$updateInvestments->current_balance - (double)$deno_amount]);
    //                                         $currAmount = (double)$updateInvestment->current_balance + (double)$deno_amount;
    //                                         $updateInvestment->update(['current_balance' => $currAmount]);
    //                                         $rAmount = $currAmount;
    //                                     }
    //                                     $status = 'success';
    //                                     $code = 200;
    //                                     $message = 'Renewal Successfully';
    //                                     $result = '';
    //                                     $associate_status = 9;
    //                                     DB::commit();
    //                                 }
    //                             }
    //                         }
    //                         // if ($mobileNumber) { 
    //                         // $contactNumber[] = str_replace('"', '', $mobileNumber);
    //                         // $text = 'Dear Member, Your A/C ' . $account['account_number'] . ' has been Credited on ' . date("d/m/Y", strtotime($globaldate)) . ' With Rs. ' . round
    //                         // ($deno_amount, 2) . ' Cur Bal: ' . round($rAmount, 2) . '. Thanks Have a nice day';
    //                         // $templateId = 1207161726461603982;
    //                         // $sendToMember = new Sms();
    //                         // $sendToMember->sendSms($contactNumber, $text, $templateId);
    //                         // }						
    //                         // \App\Models\Memberinvestments::where('id',$investmentRecord->id)->update(['current_balance'=>$currAmount]);
    //                     } else {
    //                         $status   = "Failure";
    //                         $code     = 201;
    //                         $message = 'Record Not Found!';
    //                         $result   = '';
    //                         $associate_status = 9;
    //                     }
    //                 }
    //             } else {
    //                 $currentBalance =  number_format((float)$this->ssbBalance, 2, '.', '');
    //                 $status = "Error";
    //                 $code = 419;
    //                 $message = "Token Mismatch";
    //                 $result = '';
    //                 $associate_status = 9;
    //             }
    //         } else {
    //             $currentBalance =  0;
    //             $status = "Error";
    //             $code = 419;
    //             $message = "Invalid Associate Number";
    //             $result = '';
    //             $associate_status = 9;
    //         }
    //     } catch (Exception $err) {
    //         DB::rollback();
    //         $status = "Error";
    //         $code = 500;
    //         $message = $err->getMessage();
    //         $result = '';
    //     }
    //     return response()->json(compact('status', 'code', 'message', 'result'), $code);
    // }
// code commentwed by sourev on 08-06/2023
    // public function submitRenewals(Request $request)
    // {
    //     $associate_no = $request['associate_no'];
    //     $member = $this->member;
    //     $accountNumber = $request['data'];
    //     $branchDaybookTransactions = array();
    //     $allHeadTransactions = array();
    //     $memberTransactions = array();
    //     $ssbTransactions = array();
    //     $bankTransactions = array();
    //     $entryTime = date("H:i:s");
    //     $globaldate = $this->globaldate;
    //     $ssbBalance = $this->ssbBalance;
    //     // dd($ssbBalance);
    //     $accountNumberDetails = json_decode($accountNumber, true);
    //     $companyId = $request->company_id;
    //     DB::beginTransaction();
    //     try {
    //         if ($member) {
    //             $token = md5($associate_no);
    //             if ($token == $request->token) {
    //                 foreach ($accountNumberDetails as $key => $account) {
    //                     $investmentRecord = \App\Models\Memberinvestments::with(['plan','ssbAccount'=>function($q) use($companyId){
    //                         $q->whereCompanyId($companyId);
    //                     },'member'])->where('account_number',$account['account_number'])
    //                     ->where('investment_correction_request', 0)
    //                     ->where('renewal_correction_request', 0)
    //                     ->where('is_mature', 1)
    //                     ->where('company_id',$companyId)->first();
    //                     $companyId = $request->company_id;
    //                     $associateNo = $request->associate_no;
    //                     $newMember = \App\Models\Member::with(['savingAccount_Custom3'=>function($q) use($companyId){
    //                         $q->with('getSSBAccountBalance')->where('company_id',$companyId)->first();
    //                     }])                       
    //                         ->where('associate_no', $associateNo)
    //                         ->where('associate_status', 1)
    //                         ->where('is_block', 0)
    //                         ->get();
    //                     if(isset($newMember[0]['savingAccount_Custom3']['getSSBAccountBalance']['totalBalance']) != true){
    //                         $status = "Error";
    //                         $code = 201;
    //                         $message = 'SSB account Not Available!';
    //                         $result = '';
    //                         return response()->json(compact('status', 'code', 'message', 'result'), $code);
    //                     }
    //                     $deno_amount = $account['deposite_amount'];
    //                     $mobileNumber = $investmentRecord->member->mobile_no;
    //                     if (isset($investmentRecord->id)) {
    //                         $amountArraySsb = array('1' => $deno_amount);
    //                         if ($request['payment_mode'] == 0) {
    //                             $currBalance = 0;
    //                             if ($ssbBalance < $deno_amount) {
    //                                 $status = "Success";
    //                                 $code = 201;
    //                                 $message = 'Insufficient Balance!';
    //                                 $result = '';
    //                                 return response()->json(compact('status', 'code', 'message', 'result'), $code);
    //                             } else {
    //                                 $min_balance = 500;
    //                                 $newDenoAmount = $deno_amount + $min_balance;
    //                                 if ($ssbBalance < $newDenoAmount) {
    //                                     $status = "Success";
    //                                     $code = 201;
    //                                     $message = 'Minimum Rs 500 should be in your SSB account!';
    //                                     $result = '';
    //                                     return response()->json(compact('status', 'code', 'message', 'result'), $code);
    //                                 } else {
    //                                     Session::put('created_at', date("Y-m-d " . $entryTime . "", strtotime(convertDate($globaldate))));
    //                                     // dd($investmentRecord->plan->plan_category_code); 
    //                                     if ($investmentRecord->plan->plan_category_code == 'S') {
    //                                         $sAccountId = $this->getSavingAccountDetails($investmentRecord->member_id,$companyId);
    //                                         if ($sAccountId) {
    //                                             $ssb_id = $sAccountId->id;
    //                                             $ssbAccountNumber = $sAccountId->account_no;
    //                                         } else {
    //                                             $ssb_id = NULL;
    //                                             $ssbAccountNumber = NULL;
    //                                         }
    //                                         $savingAccountDetail = $sAccount = $this->getSavingAccountDetails($member->id,$companyId);
    //                                         if ($savingAccountDetail) {
    //                                             $renewSavingOpeningBlanace = $savingAccountDetail->balance;
    //                                         } else {
    //                                             $renewSavingOpeningBlanace = NULL;
    //                                         }
    //                                         if ($investmentRecord) {
    //                                             $sResult = \App\Models\Memberinvestments::find($investmentRecord->id);
    //                                             $totalbalance = $investmentRecord->current_balance + $deno_amount;
    //                                             $investData['current_balance'] = $totalbalance;
    //                                             $sResult->update($investData);
    //                                         } else {
    //                                             $totalbalance = '';
    //                                         }
    //                                         $mtssb = 'Renewal received from SSB ' . $sAccount->account_no;
    //                                         $record1 = \App\Models\SavingAccountTranscation::where('account_no', $sAccount->account_no)->where('created_at', '<', $globaldate)->first();
    //                                         if (empty($record1)) {
    //                                             $response = array(
    //                                                 'status' => 'alert',
    //                                                 'msg' => 'Renew date should less than created date',
    //                                             );
    //                                             return Response::json($response);
    //                                         }
    //                                         $refTransactions['amount'] = $deno_amount;
    //                                         $refTransactions['entry_date'] = $globaldate;
    //                                         $refTransactions['entry_time'] = $entryTime;
    //                                         $transcation = \App\Models\BranchDaybookReference::insertGetId($refTransactions);
    //                                         $rno = $sAccount->account_no;
    //                                         $ssbAccountAmount = $sAccount->balance - $deno_amount;
    //                                         $ssb_id = $collectionSSBId = $sAccount->id;
    //                                         $sResult = \App\Models\SavingAccount::find($ssb_id);
    //                                         $sData['balance'] = $ssbAccountAmount;
    //                                         $sResult->update($sData);
    //                                         $ssb['saving_account_id'] = $savingAccountDetail->id;
    //                                         $ssb['account_no'] = $sAccount->account_no;
    //                                         if ($record1) {
    //                                             $drSSbBalancd = $ssbAccountAmount;
    //                                         } else {
    //                                             $drSSbBalancd = $deno_amount;
    //                                         }
    //                                         $ssb['opening_balance'] = $drSSbBalancd;
    //                                         $ssb['withdrawal'] = $deno_amount;
    //                                         $ssb['description'] = 'SSb Withdrawal for renewal ' . $savingAccountDetail->account_no;
    //                                         $ssb['associate_id'] = $member->id;
    //                                         $ssb['branch_id'] = $investmentRecord->branch_id;
    //                                         $ssb['type'] = 6;
    //                                         $ssb['currency_code'] = 'INR';
    //                                         $ssb['payment_type'] = 'DR';
    //                                         $ssb['payment_mode'] = 4;
    //                                         $ssb['deposit'] = NULL;
    //                                         $ssb['is_renewal'] = 0;
    //                                         $ssb['is_app'] = 1;
    //                                         $ssb['created_at'] = $globaldate;;
    //                                         $ssb['daybook_ref_id'] = $transcation;
    //                                         $ssb['company_id'] = $companyId;
    //                                         $ssbAccountTran = \App\Models\SavingAccountTranscation::create($ssb);
    //                                         $ssbFromId = $ssb_id;
    //                                         $ssbAccountTranFromId = $ssbAccountTran->id;
    //                                         $encodeDate = json_encode($ssb);
    //                                         $record3 = \App\Models\SavingAccountTranscation::where('account_no', $sAccount->account_no)->where('created_at', '<', $globaldate)->first();
    //                                         if (empty($record3)) {
    //                                             $response = array(
    //                                                 'status' => 'alert',
    //                                                 'msg' => 'Renew date should less than created date',
    //                                             );
    //                                             return Response::json($response);
    //                                         }
    //                                         $ssbAccountAmount = $sAccountId->balance + $deno_amount;
    //                                         $ssb_id = $depositSSBId = $sAccountId->id;
    //                                         $sResult = \App\Models\SavingAccount::find($ssb_id);
    //                                         $sData['balance'] = $ssbAccountAmount;
    //                                         $sResult->update($sData);
    //                                         $ssb['saving_account_id'] = $sAccountId->id;
    //                                         $ssb['account_no'] = $account['account_number'];
    //                                         if ($record1) {
    //                                             $ssbAmountBalcne = $ssbAccountAmount;
    //                                         } else {
    //                                             $ssbAmountBalcne = $deno_amount;
    //                                         }
    //                                         $ssb['opening_balance'] = $ssbAmountBalcne;
    //                                         // $ssb['opening_balance'] = $sAccountId->opening_balance + $deno_amount;
    //                                         $ssb['withdrawal'] = 0;
    //                                         $ssb['description'] = $mtssb;
    //                                         $ssb['associate_id'] = $member->id;
    //                                         $ssb['branch_id'] = $investmentRecord->branch_id;
    //                                         $ssb['type'] = 2;
    //                                         $ssb['currency_code'] = 'INR';
    //                                         $ssb['payment_type'] = 'CR';
    //                                         $ssb['payment_mode'] = 4;
    //                                         $ssb['reference_no'] = $rno;
    //                                         $ssb['deposit'] = $deno_amount;
    //                                         $ssb['created_at'] = $globaldate;
    //                                         $ssb['is_app'] = 1;
    //                                         $ssb['daybook_ref_id'] = $transcation;
    //                                         $ssbAccountTran = \App\Models\SavingAccountTranscation::create($ssb);
    //                                         $ssbToId = $sAccountId->id;
    //                                         $ssbAccountTranToId = $ssbAccountTran->id;
    //                                         $encodeDate = json_encode($ssb);
    //                                         $lastAmount = Daybook::where('investment_id', $investmentRecord->id)->whereIN('transaction_type', [2, 4, 15, 16, 17, 18, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30])->where('account_no', $investmentRecord->account_number)->whereDate('created_at', '<=', $globaldate)->orderBy('id', 'desc')->first();
    //                                         if ($lastAmount) {
    //                                             $lastBalance = $lastAmount->opening_balance + $deno_amount;
    //                                         } else {
    //                                             $lastAmount = Daybook:: /*where('investment_id',$request['investment_id'][$key])->*/where('account_no', $investmentRecord->account_number)->whereIN('transaction_type', [1])->whereDate('created_at', '<', $globaldate)->orderby('id', 'desc')->first();
    //                                             if ($lastAmount) {
    //                                                 $lastBalance = $lastAmount->opening_balance + $deno_amount;
    //                                             } else {
    //                                                 $lastOpeningAmount = Daybook::where('account_no', $investmentRecord->account_number)->whereIN('transaction_type', [1])->orderby('id', 'desc')->first();
    //                                                 //-----------update -----
    //                                                 $lastOpeningAmount = SavingAccountTranscation::where('account_no', $investmentRecord->account_number)->orderby('id', 'desc')->first();
    //                                                 $lastBalance = $lastOpeningAmount->opening_balance + $deno_amount;
    //                                             }
    //                                         }
    //                                         if ($lastAmount) {
    //                                             $nextRenewal = $lastAmount = Daybook::where('investment_id', $investmentRecord->id)->whereIN('transaction_type', [1])->where('account_no', $investmentRecord->account_number)->whereDate('created_at', '>', date("Y-m-d", strtotime(convertDate($lastAmount->created_at))))->get();
    //                                             foreach ($nextRenewal as $key1 => $value) {
    //                                                 $daybookData['opening_balance'] = $value->opening_balance + $deno_amount;
    //                                                 $dayBook = Daybook::find($value->id);
    //                                                 $dayBook->update($daybookData);
    //                                             }
    //                                         }
    //                                         $createDayBook = CommanAppEpassbookController::createDayBookNew($transcation, NULL, 2, $investmentRecord->id, $investmentRecord->associate_id, $investmentRecord->member_id, $lastBalance, $deno_amount, $withdrawal = 0, $ssb['description'], $ssbAccountNumber, $investmentRecord->branch_id, $investmentRecord->branch->branch_code, $amountArraySsb, 4, $member->first_name . ' ' . $member->last_name ?? '', $member->id, $investmentRecord->account_number, NULL, NULL, $investmentRecord->branch->name, $globaldate, NULL, $online_payment_by = NULL, $collectionSSBId, 'CR', NULL, NULL, NULL, NULL, NULL, $member->id, 1, $transcation, $companyId);
    //                                         $planId = $investmentRecord->plan_id;
    //                                         $this->investHeadCreateSSB($deno_amount, $globaldate, $investmentRecord->id, $planId, NULL, NULL, NULL, NULL, NULL, NULL, NULL, $investmentRecord->branch_id, $investmentRecord->associate_id, $investmentRecord->member_id, $savingAccountDetail->id, $ssbAccountTran->id, 4, $ssbAccountNumber, $ssbFromId, $ssbAccountTranFromId, $ssbToId, $ssbAccountTranToId, $transcation, $member->id, 1, $companyId);
    //                                         //--------------------------------HEAD IMPLEMENT ------------------------
    //                                         $daybookData['is_renewal'] = 0;
    //                                         $dayBook = Daybook::find($createDayBook);
    //                                         //$dayBook->update($daybookData);
    //                                         /*--------------------cheque assign -----------------------*/
    //                                         $investmentCurrentBalance = getSavingCurrentBalance($ssb_id, $ssbAccountNumber);
    //                                         $save = 1;
    //                                         $rAmount = $investmentCurrentBalance;
    //                                         $updateInvestment = Memberinvestments::where('account_number', $account['account_number'])->update(['current_balance' => $ssbAmountBalcne]);
    //                                         $updateInvestment = Memberinvestments::where('account_number', $sAccount->account_no)->update(['current_balance' => $drSSbBalancd]);
    //                                     } else {
    //                                         $record1 = SavingAccountTranscation::where('saving_account_id', $investmentRecord['ssbAccount'][0]['id'])->wheredate('created_at', '<=', $globaldate)->orderBy('id', 'desc')->orderBy('created_at', 'desc')->first();
    //                                         $ssbAccountAmount =$investmentRecord['ssbAccount'][0]['balance'] - $deno_amount;
    //                                         $ssb_id = $depositSSBId = $investmentRecord['ssbAccount'][0]['id'];
    //                                         $sResult = SavingAccount::find($ssb_id);
    //                                         $sData['balance'] = $ssbAccountAmount;
    //                                         $sResult->update($sData);
    //                                         $created_at1 = date("Y-m-d H:i:s", strtotime(convertDate($globaldate)));
    //                                         $daybookRefRD = CommanAppEpassbookController::createBranchDayBookReferenceNew($deno_amount, $created_at1);
    //                                         $ssb['saving_account_id'] =$investmentRecord['ssbAccount'][0]['id'];
    //                                         $ssb['account_no'] = $investmentRecord['ssbAccount'][0]['account_no'];
    //                                         $ssb['opening_balance'] = $record1->opening_balance - $deno_amount;
    //                                         $ssb['withdrawal'] = $deno_amount;
    //                                         $mtssb = 'SSb Withdrawal for renewal (' . $investmentRecord->account_number . ')';
    //                                         $ssb['description'] = $mtssb;
    //                                         $ssb['associate_id'] = $investmentRecord->associate_id;
    //                                         $ssb['branch_id'] = $investmentRecord->branch_id;
    //                                         $ssb['type'] = 6;
    //                                         $ssb['currency_code'] = 'INR';
    //                                         $ssb['payment_type'] = 'DR';
    //                                         $ssb['payment_mode'] = 4;
    //                                         $ssb['reference_no'] = $investmentRecord->account_number;
    //                                         $ssb['deposit'] = 0;
    //                                         $ssb['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($globaldate)));
    //                                         $ssb['is_app'] = 1;
    //                                         $ssb['daybook_ref_id'] = $daybookRefRD;
    //                                         $ssb['company_id'] = $companyId;
    //                                         $ssb['app_login_user_id'] = $member->id;
    //                                         $ssbAccountTran = SavingAccountTranscation::create($ssb);
    //                                         $ssbFromId = $investmentRecord['ssbAccount'][0]['id'];
    //                                         $ssbAccountTranFromId = $ssbAccountTran->id;
    //                                         $ssb_id = $collectionSSBId = $investmentRecord['ssbAccount'][0]['id'];
    //                                         $record2 = SavingAccountTranscation::where('account_no', $investmentRecord['ssbAccount'][0]['account_no'])->wheredate('created_at', '>', $globaldate)->get();
    //                                         foreach ($record2 as $key1 => $value) {
    //                                             $nsResult = SavingAccountTranscation::find($value->id);
    //                                             $nsResult['opening_balance'] = $value->opening_balance - $deno_amount;
    //                                             $nsResult->update($nsResult->toArray());
    //                                         }
    //                                         $encodeDate = json_encode($ssb);
    //                                         $satRefId = NULL;
    //                                         $amountArraySsb = array('1' => $deno_amount);
    //                                         $createTransaction = NULL;
    //                                         $rplanId = $investmentRecord->plan_id;
    //                                         $descriptionRenewal = 'Renewal received from SSB ' . $investmentRecord['ssbAccount'][0]['account_no'];
    //                                         $transactionData['is_renewal'] = 0;
    //                                         $transactionData['created_at'] = $globaldate;
    //                                         $currentDAte = date('Y-m-d H:i:s');
    //                                         $lastAmount = \App\Models\Daybook::where('investment_id', $investmentRecord->id)->whereIN('transaction_type', [2, 4, 15, 16, 17, 18, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30])->where('account_no', $investmentRecord->account_number)->whereDate('created_at', '<=', $globaldate)->orderBy('id', 'desc')->orderby('created_at', 'desc')->first();
    //                                         if ($lastAmount) {
    //                                             $lastBalance = $lastAmount->opening_balance + $deno_amount;
    //                                         } else {
    //                                             $lastAmount = Daybook::where('investment_id', $investmentRecord->id)->whereIN('transaction_type', [2, 4, 15, 16, 17, 18, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30])->where('account_no', $investmentRecord->account_number)->whereDate('created_at', '<', $globaldate)->orderby('id', 'desc')->orderby('created_at', 'desc')->first();
    //                                             $lastBalance = $lastAmount->opening_balance + $deno_amount;
    //                                         }
    //                                         $createDayBook = CommanAppEpassbookController::createDayBookNew($createTransaction, $satRefId, 4, $investmentRecord->id, $investmentRecord->associate_id, $investmentRecord->member_id, $lastBalance, $deno_amount, $withdrawal = 0, $descriptionRenewal, $member->savingAccount_Custom->account_no, $investmentRecord->branch_id, $investmentRecord['branch']->branch_code, $amountArraySsb, 4, $member->first_name . ' ' . $member->last_name, $member->id, $investmentRecord->account_number, NULL, NULL, $investmentRecord['branch']->name, $globaldate, NULL, NULL, $collectionSSBId, 'CR', NULL, NULL, NULL, NULL, NULL, $member->id, 1, $daybookRefRD, $companyId);
    //                                     //  dd($investmentRecord->plan_id);
    //                                         $this->investHeadCreate($deno_amount, $globaldate, $investmentRecord->id, $investmentRecord->plan_id, NULL, NULL, NULL, NULL, NULL, NULL, NULL, $investmentRecord->branch_id, $investmentRecord->associate_id, $investmentRecord->member_id, $collectionSSBId, $createDayBook, 3, $investmentRecord->account_number, $collectionSSBId, $ssbAccountTranFromId, NULL, NULL, $member->id, $member->id, 1, $daybookRefRD, $companyId);
    //                                         $data = [
    //                                             'member_id' => $member->id,
    //                                             'branch_id' => $investmentRecord->branch_id,
    //                                             'renew_investment_plan_id' => $investmentRecord->plan_id,
    //                                             'payment_mode' => 3
    //                                         ];
    //                                         $transaction = $this->transactionData($data, $investmentRecord->id, $deno_amount);
    //                                         $transaction['is_renewal'] = 0;
    //                                         $transaction['created_at'] = date("Y-m-d H:i:s", strtotime(str_replace('/', '-', $globaldate)));
    //                                         // $updateInvestment = Memberinvestments::find($investmentRecord->id);
    //                                         // $updateInvestments = Memberinvestments::find($member->savingAccount_Custom->member_investments_id);
    //                                         // $updateInvestments->update(['current_balance' => $updateInvestments->current_balance - $deno_amount]);
    //                                         // $currAmount = $updateInvestment->current_balance + $deno_amount;
    //                                         // $updateInvestment->update(['current_balance' => $currAmount]);
    //                                         $updateInvestment = Memberinvestments::find($investmentRecord->id);
    //                                         $updateInvestments = Memberinvestments::find($member->savingAccount_Custom->member_investments_id);
    //                                         $updateInvestments->update(['current_balance' => $updateInvestments->current_balance - $deno_amount]);
    //                                         $currAmount = $updateInvestment->current_balance + $deno_amount;
    //                                         $updateInvestment->update(['current_balance' => $currAmount]);
    //                                         $rAmount = $currAmount;
    //                                     }
    //                                     $status = 'success';
    //                                     $code = 200;
    //                                     $message = 'Renewal Successfully';
    //                                     $result = '';
    //                                     $associate_status = 9;
    //                                     DB::commit();
    //                                 }
    //                                 $contactNumber = array();
    //                                 // dd( round($rAmount, 2));
    //                                 if ($mobileNumber) {
    //                                     // $contactNumber[] = str_replace('"', '', $mobileNumber);
    //                                     // $text = 'Dear Member, Your A/C ' . $account['account_number'] . ' has been Credited on ' . date("d/m/Y", strtotime($globaldate)) . ' With Rs. ' . round($deno_amount, 2) . ' Cur Bal: ' . round($rAmount, 2) . '. Thanks Have a nice day';
    //                                     // $templateId = 1207161726461603982;
    //                                     // $sendToMember = new Sms();
    //                                     // $sendToMember->sendSms($contactNumber, $text, $templateId);
    //                                 }
    //                             }
    //                         }
    //                         // \App\Models\Memberinvestments::where('id',$investmentRecord->id)->update(['current_balance'=>$currAmount]);
    //                     } else {
    //                         $status = "Failure";
    //                         $code = 201;
    //                         $message = 'Record Not Found!';
    //                         $result = '';
    //                         // $associate_status = 9;
    //                         return response()->json(compact('status', 'code', 'message', 'result'), $code);
    //                     }
    //                 }
    //             } else {
    //                 $currentBalance = number_format((float) $this->ssbBalance, 2, '.', '');
    //                 $status = "Error";
    //                 $code = 419;
    //                 $message = "Token Mismatch";
    //                 $result = '';
    //                 // $associate_status = 9;
    //                 return response()->json(compact('status', 'code', 'message', 'result'), $code);
    //             }
    //         } else {
    //             $currentBalance = 0;
    //             $status = "Error";
    //             $code = 419;
    //             $message = "Invalid Associate Number";
    //             $result = '';
    //             // $associate_status = 9;
    //             return response()->json(compact('status', 'code', 'message', 'result'), $code);
    //         }
    //     } catch (Exception $err) {
    //         DB::rollback();
    //         $status = "Error";
    //         $code = 500;
    //         $message = $err->getMessage();
    //         $result = '';
    //     }
    //     return response()->json(compact('status', 'code', 'message', 'result'), $code);
    // }
    public function submitRenewals(Request $request)
    {
        $associate_no = $request['associate_no'];
        $member = $this->member;
        $accountNumber = $request['data'];
        $branchDaybookTransactions = array();
        $allHeadTransactions = array();
        $memberTransactions = array();
        $ssbTransactions = array();
        $bankTransactions = array();
        $entryTime = date("H:i:s");
        $globaldate = $this->globaldate;
        // $ssbBalance = $this->ssbBalance;
        // dd($ssbBalance);
        $accountNumberDetails = json_decode($accountNumber, true);
        $companyId = $request->company_id;
        DB::beginTransaction();
         try {
            if ($member) {
                $token = md5($associate_no);
                if ($token == $request->token) {
                    $i = 0;
                    foreach ($accountNumberDetails as $key => $account) {
                        $investmentRecord = \App\Models\Memberinvestments::with(['plan','assoCiateSSbAccount'=>function($q) use($companyId){
                            $q->whereCompanyId($companyId);
                        },'member'])->where('account_number',$account['account_number'])
                        ->where('investment_correction_request', 0)
                        ->where('renewal_correction_request', 0)
                        ->where('is_mature', 1)
                        ->where('company_id',$companyId)->first();
                        $companyId = $request->company_id;
                        $associateNo = $request->associate_no;
                      
                        // $newMember = \App\Models\Member::with(['savingAccount_Custom3'=>function($q) use($companyId){
                        //     $q->with('getSSBAccountBalance')->where('company_id',$companyId)->first();
                        // }])                       
                        //     ->where('associate_no', $associateNo)
                        //     ->where('associate_status', 1)
                        //     ->where('is_block', 0)
                        //     ->get();
                        $newMember = \App\Models\Member::with(['savingAccount_Custom3'=>function($q) use($companyId){
                            $q->with('getSSBAccountBalance')->where('company_id',$companyId);
                        },'savingAccount_Custom'=>function($q)use($companyId){
                            $q->with('getSSBAccountBalance')->where('company_id',$companyId);
                        }])                       
                            ->where('associate_no', $associateNo)
                            ->where('associate_status', 1)
                            ->where('is_block', 0)
                            ->first();
                        $getSSb =  SavingAccount::with('savingAccountBalance')->where('customer_id',$newMember->id)->where('company_id',$companyId)->first(); 
                        $ssbBalance = $getSSb['savingAccountBalance']->sum('deposit') - $getSSb['savingAccountBalance']->sum('withdrawal');
                        if(!isset($getSSb->id) ){
                            $status = "Error";
                            $code = 201;
                            $message = 'SSB account Not Available!';
                            $result = '';
                            return response()->json(compact('status', 'code', 'message', 'result'), $code);
                        }
                        $deno_amount = $account['deposite_amount'];
                      
                        $mobileNumber = $investmentRecord->member->mobile_no;
                        if (isset($investmentRecord->id)) {
                            $amountArraySsb = array('1' => $deno_amount);
                            if ($request['payment_mode'] == 0) {
                                $currBalance = 0;
                                if ($ssbBalance < $deno_amount) {
                                    $status = "Success";
                                    $code = 201;
                                    $message = 'Insufficient Balance!';
                                    $result = '';
                                    return response()->json(compact('status', 'code', 'message', 'result'), $code);
                                } else {
                                    $min_balance = 500;
                                    $newDenoAmount = $deno_amount + $min_balance;
                                    if ($ssbBalance < $newDenoAmount) {
                                        $status = "Success";
                                        $code = 201;
                                        $message = 'Minimum Rs 500 should be in your SSB account!';
                                        $result = '';
                                        return response()->json(compact('status', 'code', 'message', 'result'), $code);
                                    } else {
                                        Session::put('created_at', date("Y-m-d " . $entryTime . "", strtotime(convertDate($globaldate))));
                                        // dd($investmentRecord->plan->plan_category_code); 
                                        if ($investmentRecord->plan->plan_category_code == 'S') {
                                            $sAccountId = $this->getSavingAccountDetails($investmentRecord->member_id,$companyId);
                                            if ($sAccountId) {
                                                $ssb_id = $sAccountId->id;
                                                $ssbAccountNumber = $sAccountId->account_no;
                                            } else {
                                                $ssb_id = NULL;
                                                $ssbAccountNumber = NULL;
                                            }
                                            $savingAccountDetail = $sAccount = $this->getSavingAccountDetails($member->id,$companyId);
                                            if ($savingAccountDetail) {
                                                $renewSavingOpeningBlanace = $savingAccountDetail->balance;
                                            } else {
                                                $renewSavingOpeningBlanace = NULL;
                                            }
                                            if ($investmentRecord) {
                                                $sResult = \App\Models\Memberinvestments::find($investmentRecord->id);
                                                $totalbalance = $investmentRecord->current_balance + $deno_amount;
                                                $investData['current_balance'] = $totalbalance;
                                                $sResult->update($investData);
                                            } else {
                                                $totalbalance = '';
                                            }
                                            $mtssb = 'Renewal received from SSB ' . $sAccount->account_no;
                                            $record1 = \App\Models\SavingAccountTranscation::where('account_no', $sAccount->account_no)->where('created_at', '<', $globaldate)->first();
                                            if (empty($record1)) {
                                                $response = array(
                                                    'status' => 'alert',
                                                    'msg' => 'Renew date should less than created date',
                                                );
                                                return Response::json($response);
                                            }
                                            $refTransactions['amount'] = $deno_amount;
                                            $refTransactions['entry_date'] = $globaldate;
                                            $refTransactions['entry_time'] = $entryTime;
                                            $transcation = \App\Models\BranchDaybookReference::insertGetId($refTransactions);
                                            $rno = $sAccount->account_no;
                                            $ssbAccountAmount = $sAccount->balance - $deno_amount;
                                            $ssb_id = $collectionSSBId = $sAccount->id;
                                            $sResult = \App\Models\SavingAccount::find($ssb_id);
                                            $sData['balance'] = $ssbAccountAmount;
                                            $sResult->update($sData);
                                            $ssb['saving_account_id'] = $savingAccountDetail->id;
                                            $ssb['account_no'] = $sAccount->account_no;
                                            if ($record1) {
                                                $drSSbBalancd = $ssbAccountAmount;
                                            } else {
                                                $drSSbBalancd = $deno_amount;
                                            }
                                            $ssb['opening_balance'] = $drSSbBalancd;
                                            $ssb['withdrawal'] = $deno_amount;
                                            $ssb['description'] = 'SSb Withdrawal for renewal ' . $savingAccountDetail->account_no;
                                            $ssb['associate_id'] = $member->id;
                                            $ssb['branch_id'] = $investmentRecord->branch_id;
                                            $ssb['type'] = 6;
                                            $ssb['currency_code'] = 'INR';
                                            $ssb['payment_type'] = 'DR';
                                            $ssb['payment_mode'] = 4;
                                            $ssb['deposit'] = NULL;
                                            $ssb['is_renewal'] = 0;
                                            $ssb['is_app'] = 1;
                                            $ssb['created_at'] = $globaldate;;
                                            $ssb['daybook_ref_id'] = $transcation;
                                            $ssb['company_id'] = $companyId;
                                            $ssbAccountTran = \App\Models\SavingAccountTranscation::create($ssb);
                                            $ssbFromId = $ssb_id;
                                            $ssbAccountTranFromId = $ssbAccountTran->id;
                                            $encodeDate = json_encode($ssb);
                                            $record3 = \App\Models\SavingAccountTranscation::where('account_no', $sAccount->account_no)->where('created_at', '<', $globaldate)->first();
                                            if (empty($record3)) {
                                                $response = array(
                                                    'status' => 'alert',
                                                    'msg' => 'Renew date should less than created date',
                                                );
                                                return Response::json($response);
                                            }
                                            $ssbAccountAmount = $sAccountId->balance + $deno_amount;
                                            $ssb_id = $depositSSBId = $sAccountId->id;
                                            $sResult = \App\Models\SavingAccount::find($ssb_id);
                                            $sData['balance'] = $ssbAccountAmount;
                                            $sResult->update($sData);
                                            $ssb['saving_account_id'] = $sAccountId->id;
                                            $ssb['account_no'] = $account['account_number'];
                                            if ($record1) {
                                                $ssbAmountBalcne = $ssbAccountAmount;
                                            } else {
                                                $ssbAmountBalcne = $deno_amount;
                                            }
                                            $ssb['opening_balance'] = $ssbAmountBalcne;
                                            // $ssb['opening_balance'] = $sAccountId->opening_balance + $deno_amount;
                                            $ssb['withdrawal'] = 0;
                                            $ssb['description'] = $mtssb;
                                            $ssb['associate_id'] = $member->id;
                                            $ssb['branch_id'] = $investmentRecord->branch_id;
                                            $ssb['type'] = 2;
                                            $ssb['currency_code'] = 'INR';
                                            $ssb['payment_type'] = 'CR';
                                            $ssb['payment_mode'] = 4;
                                            $ssb['reference_no'] = $rno;
                                            $ssb['deposit'] = $deno_amount;
                                            $addmiute = $i ;
                                            $endTime = strtotime("+" . $addmiute . " minutes", strtotime($entryTime));
                                            $newTime = date('H:i:s', $endTime);
                                            $ssb['created_at'] =  date("Y-m-d " . $newTime . "", strtotime(convertDate($globaldate)));
                                            // $ssb['created_at'] = $globaldate;
                                            $ssb['is_app'] = 1;
                                            $ssb['daybook_ref_id'] = $transcation;
                                            $ssbAccountTran = \App\Models\SavingAccountTranscation::create($ssb);
                                            $ssbToId = $sAccountId->id;
                                            $ssbAccountTranToId = $ssbAccountTran->id;
                                            $encodeDate = json_encode($ssb);
                                            $lastAmount = Daybook::where('investment_id', $investmentRecord->id)->whereIN('transaction_type', [2, 4, 15, 16, 17, 18, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30])->where('account_no', $investmentRecord->account_number)->whereDate('created_at', '<=', $globaldate)->orderBy('id', 'desc')->first();
                                            if ($lastAmount) {
                                                $lastBalance = $lastAmount->opening_balance + $deno_amount;
                                            } else {
                                                $lastAmount = Daybook:: /*where('investment_id',$request['investment_id'][$key])->*/where('account_no', $investmentRecord->account_number)->whereIN('transaction_type', [1])->whereDate('created_at', '<', $globaldate)->orderby('id', 'desc')->first();
                                                if ($lastAmount) {
                                                    $lastBalance = $lastAmount->opening_balance + $deno_amount;
                                                } else {
                                                    $lastOpeningAmount = Daybook::where('account_no', $investmentRecord->account_number)->whereIN('transaction_type', [1])->orderby('id', 'desc')->first();
                                                    //-----------update -----
                                                    $lastOpeningAmount = SavingAccountTranscation::where('account_no', $investmentRecord->account_number)->orderby('id', 'desc')->first();
                                                    $lastBalance = $lastOpeningAmount->opening_balance + $deno_amount;
                                                }
                                            }
                                            if ($lastAmount) {
                                                $nextRenewal = $lastAmount = Daybook::where('investment_id', $investmentRecord->id)->whereIN('transaction_type', [1])->where('account_no', $investmentRecord->account_number)->whereDate('created_at', '>', date("Y-m-d", strtotime(convertDate($lastAmount->created_at))))->get();
                                                foreach ($nextRenewal as $key1 => $value) {
                                                    $daybookData['opening_balance'] = $value->opening_balance + $deno_amount;
                                                    $dayBook = Daybook::find($value->id);
                                                    $dayBook->update($daybookData);
                                                }
                                            }
                                            $createDayBook = CommanAppEpassbookController::createDayBookNew($transcation, NULL, 2, $investmentRecord->id, $investmentRecord->associate_id, $investmentRecord->member_id, $lastBalance, $deno_amount, $withdrawal = 0, $ssb['description'], $ssbAccountNumber, $investmentRecord->branch_id, $investmentRecord->branch->branch_code, $amountArraySsb, 4, $member->first_name . ' ' . $member->last_name ?? '', $member->id, $investmentRecord->account_number, NULL, NULL, $investmentRecord->branch->name, $globaldate, NULL, $online_payment_by = NULL, $collectionSSBId, 'CR', NULL, NULL, NULL, NULL, NULL, $member->id, 1, $transcation, $companyId);
                                            $planId = $investmentRecord->plan_id;
                                            $this->investHeadCreateSSB($deno_amount, $globaldate, $investmentRecord->id, $planId, NULL, NULL, NULL, NULL, NULL, NULL, NULL, $investmentRecord->branch_id, $investmentRecord->associate_id, $investmentRecord->member_id, $savingAccountDetail->id, $ssbAccountTran->id, 4, $ssbAccountNumber, $ssbFromId, $ssbAccountTranFromId, $ssbToId, $ssbAccountTranToId, $transcation, $member->id, 1, $companyId);
                                            //--------------------------------HEAD IMPLEMENT ------------------------
                                            $daybookData['is_renewal'] = 0;
                                            $dayBook = Daybook::find($createDayBook);
                                            //$dayBook->update($daybookData);
                                            /*--------------------cheque assign -----------------------*/
                                            $investmentCurrentBalance = getSavingCurrentBalance($ssb_id, $ssbAccountNumber);
                                            $save = 1;
                                            $rAmount = $investmentCurrentBalance;
                                            $updateInvestment = Memberinvestments::where('account_number', $account['account_number'])->update(['current_balance' => $ssbAmountBalcne]);
                                            $updateInvestment = Memberinvestments::where('account_number', $sAccount->account_no)->update(['current_balance' => $drSSbBalancd]);
                                        } else {
                                            $record1 = SavingAccountTranscation::where('saving_account_id', $investmentRecord['assoCiateSSbAccount'][0]['id'])->wheredate('created_at', '<=', $globaldate)->orderBy('id', 'desc')->orderBy('created_at', 'desc')->first();
                                            $assoCiateSSbAccountAmount =$investmentRecord['assoCiateSSbAccount'][0]['balance'] - $deno_amount;
                                            $ssb_id = $depositSSBId = $investmentRecord['assoCiateSSbAccount'][0]['id'];
                                            $sResult = SavingAccount::find($ssb_id);
                                            $sData['balance'] = $assoCiateSSbAccountAmount;
                                            $sResult->update($sData);
                                            $created_at1 = date("Y-m-d H:i:s", strtotime(convertDate($globaldate)));
                                            $daybookRefRD = CommanAppEpassbookController::createBranchDayBookReferenceNew($deno_amount, $created_at1);
                                            $ssb['saving_account_id'] =$investmentRecord['assoCiateSSbAccount'][0]['id'];
                                            $ssb['account_no'] = $investmentRecord['assoCiateSSbAccount'][0]['account_no'];
                                            $ssb['opening_balance'] = $record1->opening_balance - $deno_amount;
                                            $ssb['withdrawal'] = $deno_amount;
                                            $mtssb = 'SSb Withdrawal for renewal (' . $investmentRecord->account_number . ')';
                                            $ssb['description'] = $mtssb;
                                            $ssb['associate_id'] = $investmentRecord->associate_id;
                                            $ssb['branch_id'] = $investmentRecord->branch_id;
                                            $ssb['type'] = 6;
                                            $ssb['currency_code'] = 'INR';
                                            $ssb['payment_type'] = 'DR';
                                            $ssb['payment_mode'] = 4;
                                            $ssb['reference_no'] = $investmentRecord->account_number;
                                            $ssb['deposit'] = 0;
                                            $addmiute = $i ;
                                            $endTime = strtotime("+" . $addmiute . " minutes", strtotime($entryTime));
                                            $newTime = date('H:i:s', $endTime);
                                            $ssb['created_at'] =  date("Y-m-d " . $newTime . "", strtotime(convertDate($globaldate)));
                                            // $ssb['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($globaldate)));
                                            $ssb['is_app'] = 1;
                                            $ssb['daybook_ref_id'] = $daybookRefRD;
                                            $ssb['company_id'] = $companyId;
                                            $ssb['app_login_user_id'] = $member->id;
                                            $ssbAccountTran = SavingAccountTranscation::create($ssb);
                                            $ssbFromId = $investmentRecord['assoCiateSSbAccount'][0]['id'];
                                            $ssbAccountTranFromId = $ssbAccountTran->id;
                                            $ssb_id = $collectionSSBId = $investmentRecord['assoCiateSSbAccount'][0]['id'];
                                            $record2 = SavingAccountTranscation::where('account_no', $investmentRecord['assoCiateSSbAccount'][0]['account_no'])->wheredate('created_at', '>', $globaldate)->get();
                                            foreach ($record2 as $key1 => $value) {
                                                $nsResult = SavingAccountTranscation::find($value->id);
                                                $nsResult['opening_balance'] = $value->opening_balance - $deno_amount;
                                                $nsResult->update($nsResult->toArray());
                                            }
                                            $encodeDate = json_encode($ssb);
                                            $satRefId = NULL;
                                            $amountArraySsb = array('1' => $deno_amount);
                                            $createTransaction = NULL;
                                            $rplanId = $investmentRecord->plan_id;
                                            $descriptionRenewal = 'Renewal received from SSB ' . $investmentRecord['assoCiateSSbAccount'][0]['account_no'];
                                            $transactionData['is_renewal'] = 0;
                                            $transactionData['created_at'] = $globaldate;
                                            $currentDAte = date('Y-m-d H:i:s');
                                            $lastAmount = \App\Models\Daybook::where('investment_id', $investmentRecord->id)->whereIN('transaction_type', [2, 4, 15, 16, 17, 18, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30])->where('account_no', $investmentRecord->account_number)->whereDate('created_at', '<=', $globaldate)->orderBy('id', 'desc')->orderby('created_at', 'desc')->first();
                                            if ($lastAmount) {
                                                $lastBalance = $lastAmount->opening_balance + $deno_amount;
                                            } else {
                                                $lastAmount = Daybook::where('investment_id', $investmentRecord->id)->whereIN('transaction_type', [2, 4, 15, 16, 17, 18, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30])->where('account_no', $investmentRecord->account_number)->whereDate('created_at', '<', $globaldate)->orderby('id', 'desc')->orderby('created_at', 'desc')->first();
                                                $lastBalance = $lastAmount->opening_balance + $deno_amount;
                                            }
                                            $createDayBook = CommanAppEpassbookController::createDayBookNew($createTransaction, $satRefId, 4, $investmentRecord->id, $investmentRecord->associate_id, $investmentRecord->member_id, $lastBalance, $deno_amount, $withdrawal = 0, $descriptionRenewal,$member->savingAccount_Customnew[0]['account_no'], $investmentRecord->branch_id, $investmentRecord['branch']->branch_code, $amountArraySsb, 4, $member->first_name . ' ' . $member->last_name, $member->id, $investmentRecord->account_number, NULL, NULL, $investmentRecord['branch']->name, $globaldate, NULL, NULL, $collectionSSBId, 'CR', NULL, NULL, NULL, NULL, NULL, $member->id, 1, $daybookRefRD, $companyId);
                                         
                                            $this->investHeadCreate($deno_amount, $globaldate, $investmentRecord->id, $investmentRecord->plan_id, NULL, NULL, NULL, NULL, NULL, NULL, NULL, $investmentRecord->branch_id, $investmentRecord->associate_id, $investmentRecord->member_id, $collectionSSBId, $createDayBook, 3, $investmentRecord->account_number, $collectionSSBId, $ssbAccountTranFromId, NULL, NULL, $member->id, $member->id, 1, $daybookRefRD, $companyId);
                                            $data = [
                                                'member_id' => $member->id,
                                                'branch_id' => $investmentRecord->branch_id,
                                                'renew_investment_plan_id' => $investmentRecord->plan_id,
                                                'payment_mode' => 3,
                                                'company_id' => $companyId
                                            ];
                                            $transaction = $this->transactionData($data, $investmentRecord->id, $deno_amount);
                                            $transaction['is_renewal'] = 0;
                                            $transaction['created_at'] = date("Y-m-d H:i:s", strtotime(str_replace('/', '-', $globaldate)));
                                            // $updateInvestment = Memberinvestments::find($investmentRecord->id);
                                            // $updateInvestments = Memberinvestments::find($member->savingAccount_Custom->member_investments_id);
                                            // $updateInvestments->update(['current_balance' => $updateInvestments->current_balance - $deno_amount]);
                                            // $currAmount = $updateInvestment->current_balance + $deno_amount;
                                            // $updateInvestment->update(['current_balance' => $currAmount]);
                                            $updateInvestment = Memberinvestments::find($investmentRecord->id);
                                            $updateInvestments = Memberinvestments::find($getSSb->member_investments_id);
                                            $updateInvestments->update(['current_balance' => $updateInvestments->current_balance - $deno_amount]);
                                            $currAmount = $updateInvestment->current_balance + $deno_amount;
                                            $updateInvestment->update(['current_balance' => $currAmount]);
                                            $rAmount = $currAmount;
                                        }
                                        $status = 'success';
                                        $code = 200;
                                        $message = 'Renewal Successfully';
                                        $result = '';
                                        $associate_status = 9;
                                        DB::commit();
                                    }
                                    $contactNumber = array();
                                    if ($mobileNumber) {
                                        $contactNumber[] = str_replace('"', '', $mobileNumber);
                                        $text = 'Dear Member, Your A/C ' . $account['account_number'] . ' has been Credited on ' . date("d/m/Y", strtotime($globaldate)) . ' With Rs. ' . round($deno_amount, 2) . ' Cur Bal: ' . round($rAmount, 2) . '. Thanks Have a nice day';
                                        $templateId = 1207161726461603982;
                                        $sendToMember = new Sms();
                                        $sendToMember->sendSms($contactNumber, $text, $templateId);
                                    }
                                }
                            }
                            // \App\Models\Memberinvestments::where('id',$investmentRecord->id)->update(['current_balance'=>$currAmount]);
                        } else {
                            $status = "Failure";
                            $code = 201;
                            $message = 'Record Not Found!';
                            $result = '';
                            // $associate_status = 9;
                            return response()->json(compact('status', 'code', 'message', 'result'), $code);
                        }
                    }
                } else {
                    $currentBalance = number_format((float) $this->ssbBalance, 2, '.', '');
                    $status = "Error";
                    $code = 419;
                    $message = "Token Mismatch";
                    $result = '';
                    // $associate_status = 9;
                    return response()->json(compact('status', 'code', 'message', 'result'), $code);
                }
            } else {
                $currentBalance = 0;
                $status = "Error";
                $code = 419;
                $message = "Invalid Associate Number";
                $result = '';
                // $associate_status = 9;
                return response()->json(compact('status', 'code', 'message', 'result'), $code);
            }
        } catch (Exception $err) {
           DB::rollback();
           $status = "Error";
           $code = 500;
           $message = $err->getLine().' '.$err->getMessage();
           $result = '';
        }
        return response()->json(compact('status', 'code', 'message', 'result'), $code);
    }
    public  function investHeadCreate($amount, $globaldate, $investmentId, $planId, $cheque_no, $cheque_id, $cheque_date, $online_transction_no, $online_transction_date, $online_deposit_bank_id, $online_deposit_bank_ac_id, $branch_id, $associate_id, $memberId, $ssbId, $createDayBook, $payment_mode, $investmentAccountNoRd, $ssbFromId, $ssbAccountTranFromId, $ssbToId, $ssbAccountTranToId, $authUser = NULL, $appuser, $isApp, $daybookRefRD, $company_id)
    {
        $amount = $amount;
        // $daybookRefRD=CommanAppEpassbookController::createBranchDayBookReferenceNew($amount,$globaldate);
        $refIdRD = $daybookRefRD;
        $currency_code = 'INR';
        $headPaymentModeRD = 0;
        $payment_type_rd = 'CR';
        $type_id = $ssbToId;
        $entry_date = date("Y-m-d", strtotime(convertDate($globaldate)));
        $entry_time = date("H:i:s", strtotime(convertDate($globaldate)));
        $created_by = 3;
        $created_by_id = $appuser;
        $created_at = date("Y-m-d H:i:s", strtotime(convertDate($globaldate)));
        $updated_at = date("Y-m-d H:i:s", strtotime(convertDate($globaldate)));
        $type = 4;
        $sub_type = 42;
       
        $planDetail = getPlanDetailCheck($planId,$company_id);
      
        // $planCode = $planDetail->plan_code;;
        $head_id = 80;
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
        if ($payment_mode == 3) {
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
            $ssbDetals = \App\Models\SavingAccount::where('id', $ssb_account_id_from)->first();
            $getPlanmHead = \App\Models\Plans::whereCompanyId($ssbDetals->company_id)->first();
            $head4rdSSB =$getPlanmHead->deposit_head_id;
            $rdDesDR = $planDetail->name . '(' . $investmentAccountNoRd . ') A/c Dr ' . $amount . '/-';
            $rdDesCR  = 'To SSB(' . $ssbDetals->account_no . ') A/c Cr ' . $amount . '/-';
            $rdDes = 'Amount received for ' . $planDetail->name . ' A/C Renewal (' . $investmentAccountNoRd . ') through SSB(' . $ssbDetals->account_no . ')';
            $rdDesMem = $planDetail->name . ' A/C Renewal (' . $investmentAccountNoRd . ')  through SSB(' . $ssbDetals->account_no . ')';
            // ssb  head entry -
            $allTranRDSSB = CommanAppEpassbookController::createAllHeadTransaction($refIdRD, $branch_id, $bank_id = NULL, $bank_ac_id = NULL, $head4rdSSB, 4, 47, $ssb_account_id_from, $ssbAccountTranFromId, $associate_id, $memberId, $branch_id_to = NULL, $branch_id_from = NULL, $amount, $SSBDescTran, 'DR', $headPaymentModeRD, $currency_code, $jv_unique_id = NULL, $v_no, $ssb_account_id_from, $ssb_account_id_to = NULL, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $cheque_type = NULL, $cheque_id = NULL, $cheque_no, $transction_no, $created_by, $created_by_id, $appuser, $isApp, $company_id);
            //  $branchClosingSSB = $this->updateBranchClosingCashDr($branch_id, $created_at, $amount, 0);
            //  $memberTranInvest77 = CommanAppEpassbookController::memberTransactionNew($refIdRD, '4', '42', $ssb_account_id_from, $ssbAccountTranFromId, $associate_id, $ssbDetals->member_id, $branch_id, $bank_id = NULL, $account_id = NULL, $amount, $SSBDescTran, 'DR', $headPaymentModeRD, $currency_code, $amount_to_id = $type_id, $amount_to_name = $planDetail->name, $amount_from_id = NULL, $amount_from_name = NULL, $v_no, $v_date, $ssb_account_id_from = NULL, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL, $appuser, $isApp);
        }
        //  $memberTranInvest77 = CommanAppEpassbookController::memberTransactionNew($refIdRD, '3', '32', $investmentId, $ssbAccountTranFromId, $associate_id, $memberId, $branch_id, $bank_id = NULL, $account_id = NULL, $amount, $SSBDescTran, 'CR', $headPaymentModeRD, $currency_code, $amount_to_id = $type_id, $amount_to_name = $planDetail->name, $amount_from_id = NULL, $amount_from_name = NULL, $v_no, $v_date, $ssb_account_id_from = NULL, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL, $appuser, $isApp);
        //branch day book entry +
        $daybookInvest = CommanAppEpassbookController::branchDayBookNew(
            $refIdRD,
            $branch_id,
            3,
            32,
            $investmentId,
            $ssbAccountTranFromId,
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
            $created_by,
            $created_by_id,
            $ssb_account_tran_id_to,
            $ssb_account_tran_id_from,
            $jv_unique_id,
            $cheque_type,
            $cheque_id,
            $appuser,
            $isApp,
            $company_id
        );
        //  dd($appuser);
        $daybookInvest = CommanAppEpassbookController::branchDayBookNew($refIdRD, $branch_id, $type, $sub_type, $ssbId, $ssbAccountTranFromId, $associate_id, $ssbDetals->member_id, $branch_id_to = NULL, $branch_id_from = NULL, $amount, $rdDes, $rdDesDR, $rdDesCR, 'DR', $headPaymentModeRD, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $created_by, $created_by_id, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $jv_unique_id, $cheque_type, $cheque_id, $appuser, $isApp, $company_id);
        // Investment head entry +
        $allTranInvest = CommanAppEpassbookController::createAllHeadTransaction(
            $refIdRD,
            $branch_id,
            $bank_id = NULL,
            $bank_ac_id = NULL,
            $head_id,
            $type,
            $sub_type,
            $ssb_account_id_from,
            $ssbAccountTranFromId,
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
            $appuser,
            $isApp,
            $company_id
        );
        // Member transaction  +
        // $memberTranInvest = CommanAppEpassbookController::memberTransactionNew($refIdRD,$type,$sub_type,$type_id,$createDayBook,$associate_id,$memberId,$branch_id,$bank_id=NULL,$account_id=NULL,$amount,$rdDesMem,$payment_type_rd,$headPaymentModeRD,$currency_code,$amount_to_id=NULL,$amount_to_name=NULL,$amount_from_id=NULL,$amount_from_name=NULL,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at,$ssb_account_tran_id_to=NULL,$ssb_account_tran_id_from=NULL,$jv_unique_id=NULL,$cheque_type=NULL,$cheque_id=NULL,$appuser,$isApp);
        /******** Balance   entry ***************/
        //  $branchClosing = $this->updateBranchClosingCashCr($branch_id, $created_at, $amount, 0);
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
    public function transactionData($request, $investmentId, $amount)
    {
        $globaldate = Session::get('created_at');
        $getBranchId = getBranchDetail($request['branch_id']);
        $branch_id = $getBranchId->id;
        $branchCode = $getBranchId->branch_code;
        //$branchCode=$getBranchCode->branch_code;
        $sAccount = SavingAccount::where('customer_id',$request['member_id'])->where('company_id',$request['company_id'])->first();
        // $sAccount = $this->member->savingAccount_Custom;
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
    public function getSavingAccountId($investmentId)
    {
        $getDetails = \App\Models\SavingAccount::where('member_investments_id', $investmentId)->select('id', 'balance', 'account_no')->first();
        return $getDetails;
    }
    public function getSavingAccountDetails($mId,$companyId)
    {
        $getDetails =  \App\Models\SavingAccount::where('member_id', $mId)->whereCompanyId($companyId)->select('id', 'balance', 'account_no')->first();
        return $getDetails;
    }
    /**
     * Collection Report of Associate
     * @param associate_no
     * table day_books 
     */
    public function collectionReport(Request $request)
    {
        $member = $this->member;
        $token  = md5($request->associate_no);
        $checkToken = $request->token;
        $dateTo = $request->date_to ?? 0;
        $dateFrom = $request->date_from ?? 0;
        $modeOfDeposit = $request->mode_of_deposit ?? '';
        $globaldate = $this->globaldate;
        $isSearch = $request->is_search;
        try {
            if ($token == $checkToken) {
                $transactionRecords = \App\Models\Daybook::select('id', 'created_at', 'deposit', 'account_no', 'member_id', 'is_app', 'transaction_type')->whereIn('transaction_type', [2, 4])->where('associate_id', $member->id)->where('is_deleted', 0);
                // date('Y-m-d',strtotime(convertDate($globaldate)))
                $startDate = ($isSearch == '0')
                    ? date('Y-m-d', strtotime(($globaldate)))
                    : (($dateFrom != 0) ?
                        date('Y-m-d', strtotime(convertDate($dateFrom))) : '');
                $endDate = ($isSearch == '0')
                    ? date('Y-m-d', strtotime(($globaldate)))
                    : (($dateTo != 0) ?
                        date('Y-m-d', strtotime(convertDate($dateTo))) :  date('Y-m-d', strtotime(($globaldate))));
                //If Deposite Mode  Filtered is Applied
                $transactionRecords = $transactionRecords->when($modeOfDeposit != '', function ($q) use ($modeOfDeposit) {
                    $q->where('is_app', $modeOfDeposit);
                });
                //If Date Filtered is Applied
                $transactionRecords = $transactionRecords->when($startDate != '0', function ($q) use ($startDate, $endDate) {
                    $q->whereBetween(\DB::raw('date(created_at)'), [$startDate, $endDate]);
                });
                $transactionRecords = $transactionRecords->get();
                $rowReturn = array();
                $mode = 'N/A';
                foreach ($transactionRecords as $key => $records) {
                    if ($records->is_app == 0) {
                        $mode = 'Software';
                    }
                    if ($records->is_app == 1) {
                        $mode = 'Associate';
                    }
                    if ($records->is_app == 2) {
                        $mode = 'E-Passbook';
                    }
                    $val['id'] = (string)($key + 1);
                    $val['account_number'] = $records->account_no;
                    $val['acc_holder_name'] = $records->member->first_name . ' ' . $records->member->last_name ?? '';
                    $val['amount'] = $records->deposit;
                    $val['mode_of_deposit'] = $mode;
                    $val['mode_of_deposit_id'] = (string)$records->is_app;
                    $rowReturn[] = $val;
                }
                $status = 'success';
                $code = 200;
                $messages = "Transaction Details";
                $result = $rowReturn;
                $associate_status = $member->associate_app_status;
            } else {
                $status = 'success';
                $code = 200;
                $messages = "Record not Found";
                $result = '';
                $associate_status = $member->associate_app_status;
            }
        } catch (Exception $ex) {
            $status = 'error';
            $code = 500;
            $messages = $ex->getMessage();
            $result = '';
            $associate_status = $member->associate_app_status;
        }
        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
    }
    public  function investHeadCreateSSB($amount, $globaldate, $investmentId, $planId, $cheque_no, $cheque_id, $cheque_date, $online_transction_no, $online_transction_date, $online_deposit_bank_id, $online_deposit_bank_ac_id, $branch_id, $associate_id, $memberId, $ssbId, $createDayBook, $payment_mode, $investmentAccountNoRd, $ssbFromId, $ssbAccountTranFromId, $ssbToId, $ssbAccountTranToId, $day_book_ref_id, $appuser, $isApp, $companyId)
    {
        $amount = $amount;
        $daybookRefRD = $day_book_ref_id;
        $refIdRD = $daybookRefRD;
        $currency_code = 'INR';
        $headPaymentModeRD = 0;
        $payment_type_rd = 'CR';
        $type_id = $investmentId;
        $entry_date = date("Y-m-d", strtotime(convertDate($globaldate)));
        $entry_time = date("H:i:s", strtotime(convertDate($globaldate)));
        $created_by = 3;
        $created_by_id = $this->member->id;
        $created_at = date("Y-m-d H:i:s", strtotime(convertDate($globaldate)));
        $updated_at = date("Y-m-d H:i:s", strtotime(convertDate($globaldate)));
        $planDetail = getPlanDetailCheck($planId,$companyId);
        $type = 4;
        $sub_type = 42;
        // $planCode = $planDetail->plan_code;;
        $head_id = $planDetail->deposit_head_id;
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
        $headPaymentModeRD = 3;
        $v_no = mt_rand(0, 999999999999999);
        $v_date = $entry_date;
        $ssb_account_id_from = $ssbId;
        $SSBDescTran = 'Amount transfer to ' . $planDetail->name . '(' . $investmentAccountNoRd . ') ';
        // $head1rdSSB = 1;
        // $head2rdSSB = 8;
        // $head3rdSSB = 20;
        // $head4rdSSB = 56;
        $head5rdSSB = NULL;
        $ssbDetals = SavingAccount::where('id', $ssb_account_id_from)->first();
        $getPlanmHead = \App\Models\Plans::whereCompanyId($ssbDetals->company_id)->first();
        $head4rdSSB =$getPlanmHead->deposit_head_id;
        $rdDesDR = $planDetail->name . '(' . $investmentAccountNoRd . ') A/c Dr ' . $amount . '/-';
        $rdDesCR  = 'To SSB(' . $ssbDetals->account_no . ') A/c Cr ' . $amount . '/-';
        $rdDes = 'Amount received for SSB A/C Deposit (' . $investmentAccountNoRd . ') through SSB(' . $ssbDetals->account_no . ')';
        $rdDesMem = 'SSB A/C Deposit (' . $investmentAccountNoRd . ') online through SSB(' . $ssbDetals->account_no . ')';
        // ssb  head entry -
        // $allTranRDSSB = CommanAppEpassbookController::createAllHeadTransaction($refIdRD, $branch_id, $bank_id = NULL, $bank_ac_id = NULL, $head4rdSSB, 4, 47, $ssb_account_id_from, $ssbAccountTranFromId, $associate_id, $memberId, $branch_id_to = NULL, $branch_id_from = NULL, $amount, $amount, $amount, $rdDes, 'DR', $headPaymentModeRD, $currency_code, $amount_to_id = NULL, $amount_to_name = NULL, $amount_from_id = NULL, $amount_from_name = NULL, $jv_unique_id = NULL, $v_no, $v_date, $ssb_account_id_from, $ssb_account_id_to = NULL, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $cheque_type = NULL, $cheque_id = NULL, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_from_id = NULL, $cheque_bank_ac_from_id = NULL, $cheque_bank_to, $cheque_bank_ac_to, $cheque_bank_to_name = NULL, $cheque_bank_to_branch = NULL, $cheque_bank_to_ac_no = NULL, $cheque_bank_to_ifsc = NULL, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_from_id = NULL, $transction_bank_from_ac_id = NULL, $transction_bank_to, $transction_bank_ac_to, $transction_bank_to_name = NULL, $transction_bank_to_ac_no = NULL, $transction_bank_to_branch = NULL, $transction_bank_to_ifsc = NULL, $transction_date, $created_by, $created_by_id, $appuser, $isApp);
        // $branchClosingSSB = $this->updateBranchClosingCashDr($branch_id, $created_at, $amount, 0);
        $allTranRDSSB = CommanAppEpassbookController::createAllHeadTransaction($refIdRD, $branch_id, $bank_id = NULL, $bank_ac_id = NULL, $head4rdSSB, 4, 47, $ssb_account_id_from, $ssbAccountTranFromId, $associate_id, $memberId, $branch_id_to = NULL, $branch_id_from = NULL, $amount, $SSBDescTran, 'DR', $headPaymentModeRD, $currency_code, $jv_unique_id = NULL, $v_no, $ssb_account_id_from, $ssb_account_id_to = NULL, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $cheque_type = NULL, $cheque_id = NULL, $cheque_no, $transction_no, $created_by, $created_by_id, $appuser, $isApp, $company_id);
        $daybookInvest = CommanAppEpassbookController::branchDayBookNew(
            $refIdRD,
            $branch_id,
            3,
            32,
            $investmentId,
            $ssbAccountTranFromId,
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
            $created_by,
            $created_by_id,
            $ssb_account_tran_id_to,
            $ssb_account_tran_id_from,
            $jv_unique_id,
            $cheque_type,
            $cheque_id,
            $appuser,
            $isApp,
            $company_id
        );
        $daybookInvest = CommanAppEpassbookController::branchDayBookNew($refIdRD, $branch_id, $type, $sub_type, $ssbId, $ssbAccountTranFromId, $associate_id, $ssbDetals->member_id, $branch_id_to = NULL, $branch_id_from = NULL, $amount, $rdDes, $rdDesDR, $rdDesCR, 'DR', $headPaymentModeRD, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $created_by, $created_by_id, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $jv_unique_id, $cheque_type, $cheque_id, $appuser, $isApp, $company_id);
        // Investment head entry +
        $allTranInvest = CommanAppEpassbookController::createAllHeadTransaction(
            $refIdRD,
            $branch_id,
            $bank_id = NULL,
            $bank_ac_id = NULL,
            $head_id,
            $type,
            $sub_type,
            $ssb_account_id_from,
            $ssbAccountTranFromId,
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
            $appuser,
            $isApp,
            $company_id
        );
        //branch day book entry +
        // $daybookInvest = CommanAppEpassbookController::branchDayBookNew($refIdRD, $branch_id, $type, $sub_type, $type_id, $ssbAccountTranFromId, $associate_id, $memberId, $branch_id_to = NULL, $branch_id_from = NULL, $amount, $amount, $amount, $rdDes, $rdDesDR, $rdDesCR, $payment_type_rd, $headPaymentModeRD, $currency_code, $amount_to_id = NULL, $amount_to_name = NULL, $amount_from_id = NULL, $amount_from_name = NULL, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $is_contra = NULL, $contra_id = NULL, $created_at, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL, $appuser, $isApp);
        // $memberTranInvest77 = CommanAppEpassbookController::branchDayBookNew($refIdRD, $branch_id, $type, $sub_type, $ssbFromId, $ssbAccountTranFromId, $associate_id, $ssbDetals->member_id, $branch_id_to = NULL, $branch_id_from = NULL, $amount, $amount, $amount, $rdDes, $rdDesDR, $rdDesCR, 'DR', $headPaymentModeRD, $currency_code, $amount_to_id = NULL, $amount_to_name = NULL, $amount_from_id = NULL, $amount_from_name = NULL, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $is_contra = NULL, $contra_id = NULL, $created_at, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL, $appuser, $isApp);
        // // Investment head entry +
        // $allTranInvest = CommanAppEpassbookController::createAllHeadTransaction($refIdRD, $branch_id, $bank_id = NULL, $bank_ac_id = NULL, $head4rdSSB, $type, $sub_type, $type_id, $createDayBook, $associate_id, $memberId, $branch_id_to = NULL, $branch_id_from = NULL, $amount, $amount, $amount, $rdDes, $payment_type_rd, $headPaymentModeRD, $currency_code, $amount_to_id = NULL, $amount_to_name = NULL, $amount_from_id = NULL, $amount_from_name = NULL, $jv_unique_id = NULL, $v_no, $v_date, $ssb_account_id_from, $ssb_account_id_to = NULL, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $cheque_type = NULL, $cheque_id = NULL, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_from_id = NULL, $cheque_bank_ac_from_id = NULL, $cheque_bank_to, $cheque_bank_ac_to, $cheque_bank_to_name = NULL, $cheque_bank_to_branch = NULL, $cheque_bank_to_ac_no = NULL, $cheque_bank_to_ifsc = NULL, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_from_id = NULL, $transction_bank_from_ac_id = NULL, $transction_bank_to, $transction_bank_ac_to, $transction_bank_to_name = NULL, $transction_bank_to_ac_no = NULL, $transction_bank_to_branch = NULL, $transction_bank_to_ifsc = NULL, $transction_date, $created_by, $created_by_id, $appuser, $isApp);
    }
}