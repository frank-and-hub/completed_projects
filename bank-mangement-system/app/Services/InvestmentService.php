<?php

namespace App\Services;

use App\Http\Controllers\Admin\CommanController;
use App\Http\Resources\ErrorResponseResource;
use App\Models\PlanTenures;
use App\Models\{MemberCompany, Member, MemberNominee, Relations, PlanCategory, ReceivedCheque, SavingAccount, Memberinvestments, SavingAccountTranscation, Investmentplantransactions, Memberinvestmentsnominees, ReceivedChequePayment, HeadSetting, GstSetting, Plans, SamraddhBank};
use Auth;
use CommanTransactionFacade;
use Illuminate\Support\Facades\DB;
use Session;
use Carbon\Carbon;



class InvestmentService
{


    public function __construct()
    {
        $this->member = new Member();
    }
    public function getMember($customerId)
    {

        $columns = ['id', 'member_id'];
        $getMember = $this->member->whereMemberId($customerId)->whereStatus(1)->where('is_block', 0);

        return $getMember;
    }
    public function getMemberId($customerId)
    {
        $columns = ['id', 'member_id'];
        $getMember = $this->member->whereId($customerId)->whereStatus(1)->where('is_block', 0);
        return $getMember;
    }
    public function registerMember($memberDetails, $request, $transType = NULL)
    {
        // dd($memberDetails, $request, $transType);

        $type = 'create';

        $memberCode = generateCode($request, $type, config('constants.MEMBER'), 5, $request->company_id ?? $request['company_id']);

        DB::beginTransaction();
        try {

            $associateId = ($transType == 'Loan') ? $request['acc_member_id'] : $memberDetails->associate_id;

            $customerData = [
                'customer_id' => $memberDetails->id ?? '0',
                'company_id' => $request['company_id'] ?? '0',
                're_date' => isset($request['create_application_date']) ? date("Y-m-d", strtotime(str_replace('/', '-', $request['create_application_date']))) : date("Y-m-d", strtotime(convertDate($request->created_date))),
                'member_id' => $memberCode['memberCode'] ?? '0',
                'associate_code' => $memberDetails->associate_code ?? '0',
                'mi_code' => $memberCode['miCode'] ?? '0',
                'fa_code' => $memberCode['faCode'] ?? '0',
                'branch_id' => $request['branchid'] ?? '0',
                'branch_code' => $memberCode['branchCode'] ?? '0',
                'associate_id' => $associateId,
                'ssb_account' => $memberDetails->ssb_account ?? '0',
                'rd_account' => $memberDetails->rd_account ?? '0',
                'branch_mi' => $memberDetails->branch_mi ?? '0',
                'reinvest_old_account_number' => $memberDetails->reinvest_old_account_number ?? '0',
                'old_c_id' => $memberDetails->old_c_id ?? '0',
                'otp' => $memberDetails->otp ?? '0',
                'varifiy_time' => $memberDetails->varifiy_time ?? '0',
                'is_varified' => $memberDetails->is_varified ?? '0',
                'role_id' => 5,
                'upi' => $memberDetails->upi ?? '0',
                'token' => $memberDetails->token ?? '0',
                'created_at' => isset($request['create_application_date']) ? date("Y-m-d", strtotime(str_replace('/', '-', $request['create_application_date']))) : date("Y-m-d", strtotime(convertDate($request->created_date))),
            ];

            // dd(date("Y-m-d", strtotime(str_replace('/', '-', $request['create_application_date']))));

            $memberData = MemberCompany::create($customerData);

            $response = $memberData;
            DB::commit();
        } catch (\Exception $ex) {

            dd("gdgd: " . $ex->getMessage() . ' _ ' . $ex->getLine());
            DB::rollback();
            $response = [
                'insertedid' => '',
                'status' => false,
                'msg' => $ex->getMessage(),
                'line' => $ex->getLine(),
            ];
        }
        return $response;
    }
    public function storeInvestment($request, $repo)
    {
        try {
            DB::beginTransaction();
            $entryTime = date("H:i:s");
            if (isset($request['create_application_date'])) {
                Session::put('created_at', date("Y-m-d " . $entryTime . "", strtotime(convertDate($request['create_application_date']))));
                $request['create_application_date'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($request['create_application_date'])));
            } else {
                Session::put('created_at', date("Y-m-d " . $entryTime . "", strtotime(convertDate($request['created_date']))));
            }
            $type = 'create';
            $branch_id = $request['branchid'];
            if (isset($request->is_app) && $request->is_app) {
                $branch_id = Member::whereId($request->input('associatemid'))->value('branch_id');
                $request['branchid'] = $branch_id;
            }
            $getBranchCode = getBranchCode($branch_id);
            $planId = $request['investmentplan'];
            $faCode = getPlanCode($planId);
            $companyId = $request->company_id;
            $globaldate = $pdate = date("Y-m-d H:i:s", strtotime(str_replace('/', '-', $request['create_application_date'])));
            $investmentMiCode = getInvesmentMiCodeNew($planId, $branch_id);
            $branchCode = $getBranchCode->branch_code;
            $miCodeAdd = (!empty($investmentMiCode)) ? $investmentMiCode->mi_code + 1 : 1;
            $miCode = str_pad($miCodeAdd, 5, '0', STR_PAD_LEFT);
            $miCodeBig = str_pad($miCodeAdd, 5, '0', STR_PAD_LEFT);

            $codes = generateCode($request, $type, config('constants.PASSBOOK'), 5, $companyId);

            $passbook = $codes['passbookCode'] . $branchCode . $faCode . $miCodeBig;
            $certificate = $codes['certificateCode'] . $branchCode . $faCode . $miCodeBig;
            $investmentAccount = $branchCode . $faCode . $miCode;
            $data['certificate_no'] = '';
            $planCategory = $request->input('plan_type');
            $planName = $repo->getAllPlans()->whereId($planId)->first();
            $description = $planName->short_name . ' Account Opening';
            $shortName = $planName->short_name;
            $sAccount = $this->getMemberId($request->input('memberAutoId'))->with([
                'savingAccount' => function ($q) use ($companyId) {
                    $q->where('company_id', $companyId);
                }
            ])->first();
            $sAccount_associate = $this->getMemberId($request->input('associatemid'))->with([
                'savingAccount' => function ($q) use ($companyId) {
                    $q->where('company_id', $companyId);
                }
            ])->first();
            $checkMemberExistInAnyComapany = MemberCompany::where('customer_id', $request->input('memberAutoId'))->first();
            $sAccountDetail = $sAccount;
            $ssbAccountNumber = (isset($sAccount['savingAccount'][0])) ? $sAccount['savingAccount'][0]->account_no : '';
            $ssbId = (isset($sAccount['savingAccount'][0])) ? $sAccount['savingAccount'][0]->id : '';
            $ssbId_a = (isset($sAccount_associate['savingAccount'][0])) ? $sAccount_associate['savingAccount'][0]->id : '';
            $ssbBalance = (isset($sAccount['savingAccount'][0])) ? $sAccount['savingAccount'][0]->balance : '';
            $ssbBalance_a = (isset($sAccount_associate['savingAccount'][0])) ? $sAccount_associate['savingAccount'][0]->balance : '';
            $data = $this->getData($request->all(), $type, $miCode, $investmentAccount, $branch_id, $faCode);
            if ($planCategory == "F") {
                $data['certificate_no'] = $certificate;
            } else {
                $data['passbook_no'] = $passbook;
            }
            $received_cheque_id = $cheque_id =
                $cheque_deposit_bank_id =
                $cheque_deposit_bank_ac_id =
                $cheque_no =
                $cheque_date =
                $online_deposit_bank_id =
                $online_deposit_bank_ac_id =
                $online_transction_no =
                $online_transction_date = NULL;
            if ($request->input('payment-mode') == 1) {
                $chequeDetail = ReceivedCheque::whereId($request['cheque_id'])->first();
                $received_cheque_id = $cheque_id = $request['cheque_id'];
                $cheque_deposit_bank_id = $chequeDetail->deposit_bank_id;
                $cheque_deposit_bank_ac_id = $chequeDetail->deposit_account_id;
                $cheque_no = $request['cheque-number'];
                $cheque_date = $pdate = date("Y-m-d", strtotime(str_replace('/', '-', $request['cheque-date'])));
            }
            if ($request->input('payment-mode') == 2) {
                $online_deposit_bank_id = $request['rd_online_bank_id'];
                $online_deposit_bank_ac_id = $request['rd_online_bank_ac_id'];
                $online_transction_no = $request['transaction-id'];
                $online_transction_date = $pdate = date("Y-m-d", strtotime(str_replace('/', '-', $request['date'])));
            }
            $memberId = $request['memberAutoId'];

            if ($request['newUser'] == "false") {
                $memberDetail = $sAccount;
                $mData = $this->registerMember($memberDetail, $request);
                $data['mi_charge'] = empty($checkMemberExistInAnyComapany) ? 10 : 0;
                $data['stn_charge'] = empty($checkMemberExistInAnyComapany) ? 90 : 0;
                $mId = $mData['id'];
            } else {
                $mId = MemberCompany::where('customer_id', $request['memberAutoId'])->whereCompanyId($companyId)->first();
                $mId = $mId->id;
            }

            if ($planCategory == 'S') {
                if (SavingAccount::where('customer_id', $memberId)->whereCompanyId($companyId)->count() > 0) {
                    DB::rollback();
                    return $response = [
                        'insertId' => null,
                        'status' => '201',
                        'message' => 'Saving Account Already Exist'
                    ];
                }
            }

            if ($request->input('is_ssb_required') == 1) {
                $checkSSb = SavingAccount::where('customer_id', $memberId)->whereCompanyId($companyId)->count();
                if ($checkSSb == 0) {
                    $this->registerSSbRequiredData($request, $mId);
                }
            }
            $data['member_id'] = $mId;
            $data['created_at'] = isset($request['create_application_date']) ? date("Y-m-d H:i:s", strtotime(str_replace('/', '-', $request['create_application_date']))) : date("Y-m-d H:i:s", strtotime(convertDate($request->created_date)));
            if (isset($request->is_app) && $request->is_app) {
                $is_app = 1;
                $associate_app = 3;
                $data['is_app'] = 1;
                $savingTransaction['is_app'] = 1;
                $savingTransaction['app_login_user_id'] = $data['app_login_user_id'] = $request->input('associatemid');
            } else {
                $associate_app = null;
                $is_app = 0;
            }

            if ($request->plan_sub_category == 'K') {
                // $data['phone_number'] = $request['phone-number'];
                $data['age'] = $request['age'];
                $data['maturity_amount'] = $request['maturity-amount'];
                $data['tenure'] = $request['tenure'];
                $data['daughter_name'] = $request['daughter-name'];
                $data['dob'] = date('Y-m-d', strtotime(convertDate($request['dob'])));
                $data['guardians_relation'] = $request['guardian-ralationship'];
                $data['maturity_date'] = date("Y-m-d", strtotime('+ ' . ($request['tenure'] * 12) . 'months', strtotime($globaldate)));
            }
            $res = Memberinvestments::create($data);
            $memberInvestmentId = $res->id;
            $updatePlan = Plans::findorfail($planId);
            if ($updatePlan->is_editable == 1) {
                $updatePlan->update(['is_editable' => 0]);
            }
            $accountNumber = $res->account_number;
            $ppmode = 0;
            //cheque
            if ($request->input('payment-mode') == 1) {
                $ppmode = 1;
            }
            //online
            if ($request->input('payment-mode') == 2) {
                $ppmode = 3;
            }
            //ssb
            if ($request->input('payment-mode') == 3) {
                $ppmode = 4;
            }
            $daybookRefRD = CommanController::createBranchDayBookReferenceNew($request['amount'], $globaldate);
            switch ($planCategory) {
                case "S":
                    $is_primary = 0;
                    $insertedid = $res->id;
                    $encodeDate = json_encode($data);
                    $savingAccountId = $res->account_number;
                    $type = 0;
                    $createAccount = CommanController::createSavingAccountDescriptionModify($mId, $branch_id, $branchCode, $request['amount'], 0, $insertedid, $miCode, $investmentAccount, $is_primary, $faCode, $description, $request['associatemid'], $type, $daybookRefRD, $companyId, $request['memberAutoId'], $passbook, $associate_app);
                    $traType = 1;
                    $ssbId = $createAccount['ssb_id'];
                    $mData['ssb_account'] = $investmentAccount;
                    $mRes = MemberCompany::find($mId);
                    $sAccount = $mRes->update(['ssb_account' => $investmentAccount]);
                    $ssbAccountId = $createAccount['ssb_id'];
                    $amountArraySsb = array('1' => $request['amount']);
                    $amount_deposit_by_name = substr($request['member_name'], 0, strpos($request['member_name'], "-"));
                    // ---------------------------  Day book modify --------------------------
                    $createDayBook = $createAccount['ssb_transaction_id'] ?? null;
                    /**By Mahesh on 23-11-23 */
                    if ($associate_app != null) {
                        $savingTransaction = $this->savingAccountTransactionDataNew($request->all(), $insertedid, $type, $shortName, $investmentAccount, $sAccount_associate, $daybookRefRD, $s = 2);

                        $res = SavingAccountTranscation::create($savingTransaction);

                        $satRefId = NULL;
                        $sAccountId = $ssbId_a;
                        $sAccountAmount = $ssbBalance_a - $request->input('amount');
                        $ssbDetals = SavingAccount::find($sAccountId);
                        $sData['balance'] = $sAccountAmount;
                        $ssbDetals->update($sData);
                        $ssbCreateTran = NULL;
                        $sAccountNumber = $ssbAccountNumber;
                        $created_at = date("Y-m-d H:i:s", strtotime(convertDate($globaldate)));
                        $updated_at = date("Y-m-d H:i:s", strtotime(convertDate($globaldate)));
                        $entry_date = date("Y-m-d", strtotime(convertDate($globaldate)));
                        $entry_time = date("H:i:s");

                        if (isset($request->is_app) && $request->is_app) {
                            $created_by = 3;
                        } else {
                            $created_by = (Auth::user()->role_id == 3) ? 2 : 1;
                        }


                        $created_by_id = (isset($request->is_app) && $request->is_app) ? $request->input('associatemid') : Auth::user()->id;

                        $rdDesDR = $planName->name . '(' . $accountNumber . ') A/c Dr ' . $request['amount'] . '/-';
                        $rdDesCR = 'To SSB(' . $ssbDetals->account_no . ') A/c Cr ' . $request['amount'] . '/-';
                        $rdDes = 'Amount received for Account opening ' . $planName->name . '(' . $accountNumber . ') through SSB(' . $ssbDetals->account_no . ')';


                        $headPaymentModeRD = 3;
                        $transction_no = NULL;

                        if ($is_app != null) {
                            $allTranRDcheque = CommanTransactionFacade::createBranchDayBookNew($daybookRefRD, $branch_id, 4, 43, $ssbAccountId, $request['associatemid'], $mId, $branch_id_to = NULL, $branch_id_from = NULL, $request['amount'], $rdDes, $rdDesDR, $rdDesCR, 'DR', $headPaymentModeRD, 'INR', $v_no = NULL, $sAccountId, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $createDayBook = NULL, $companyId);
                        } else {
                            $allTranRDcheque = CommanTransactionFacade::createBranchDayBookNew($daybookRefRD, $branch_id, 4, 43, $sAccountId, $request['associatemid'], $mId, $branch_id_to = NULL, $branch_id_from = NULL, $request['amount'], $rdDes, $rdDesDR, $rdDesCR, 'DR', $headPaymentModeRD, 'INR', $v_no = NULL, $sAccountId, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $createDayBook = NULL, $companyId);
                        }
                    }
                    /**By Mahesh on 23-11-23 */
                    $text = 'Dear Member, Thank You for Choosing S.B. Micro Finance association Your ' . $shortName . ' A/c No.' . $savingAccountId . ' is Credited on ' . $res->created_at->format('d M Y') . ' With Rs. ' . round($request['amount'], 2) . '. Thanks Have a good day';
                    $temaplteId = 1207161519023692218;
                    break;
                default:
                    $ssbAccountId = $res->id;
                    $insertedid = $res->id;
                    $traType = 2;
                    $text = 'Dear Member, Thank You for Choosing S.B. Micro Finance association Your ' . $shortName . ' A/c No.' . $res->account_number . ' is Credited on ' . $res->created_at->format('d M Y') . ' With Rs. ' . round($res->deposite_amount, 2) . 'CR. Thanks Have a good day';
                    $temaplteId = 1207161519023692218;
                    if ($request->input('payment-mode') != 3) {
                        $paymentData = $this->getPaymentMethodData($request->all(), $insertedid, $type);
                    }

                    $amountArray = array('1' => $request->input('amount'));
                    $amount_deposit_by_name = $request->input('firstname') . ' ' . $request->input('lastname');
                    $amountArraySsb = array('1' => $request['amount']);
                    if ($request->input('payment-mode') == 3 && $is_app == 0) {
                        // $savingTransaction = $this->savingAccountTransactionData($request->all(),$insertedid,$type);

                        $savingTransaction = $this->savingAccountTransactionDataNew($request->all(), $insertedid, $type, $shortName, $investmentAccount, $sAccountDetail, $daybookRefRD);
                        $res = SavingAccountTranscation::create($savingTransaction);
                        $satRefId = NULL;
                        $sAccountId = $ssbId;
                        $sAccountAmount = $ssbBalance - $request->input('amount');
                        $ssbDetals = SavingAccount::find($sAccountId);
                        $sData['balance'] = $sAccountAmount;
                        $ssbDetals->update($sData);
                        $ssbCreateTran = NULL;
                        $sAccountNumber = $ssbAccountNumber;
                        $created_at = date("Y-m-d H:i:s", strtotime(convertDate($globaldate)));
                        $updated_at = date("Y-m-d H:i:s", strtotime(convertDate($globaldate)));
                        $entry_date = date("Y-m-d", strtotime(convertDate($globaldate)));
                        $entry_time = date("H:i:s");
                        $created_by = (Auth::user()->role_id == 3) ? 2 : 1;
                        $created_by_id = Auth::user()->id;
                        $rdDesDR = $planName->name . '(' . $accountNumber . ') A/c Dr ' . $request['amount'] . '/-';
                        $rdDesCR = 'To SSB(' . $ssbDetals->account_no . ') A/c Cr ' . $request['amount'] . '/-';
                        $rdDes = 'Amount received for Account opening ' . $planName->name . '(' . $accountNumber . ') through SSB(' . $ssbDetals->account_no . ')';
                        $headPaymentModeRD = 3;
                        $transction_no = NULL;

                        // $allTranRDcheque = CommanTransactionFacade::createBranchDayBookNew($daybookRefRD, $branch_id, 3, 31, $ssbAccountId, $request['associatemid'], $mId, $branch_id_to = NULL, $branch_id_from = NULL, $request['amount'], $rdDes, $rdDesDR, $rdDesCR, 'DR', $headPaymentModeRD, 'INR', $v_no = NULL, $sAccountId, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $createDayBook = NULL, $companyId);

                        if ($is_app != 0) {
                            $t = $ssbAccountId;
                        } else {
                            $t = $sAccountId;
                        }
                        $allTranRDcheque = CommanTransactionFacade::createBranchDayBookNew($daybookRefRD, $branch_id, 4, 43, $t, $request['associatemid'], $mId, $branch_id_to = NULL, $branch_id_from = NULL, $request['amount'], $rdDes, $rdDesDR, $rdDesCR, 'DR', $headPaymentModeRD, 'INR', $v_no = NULL, $sAccountId, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $createDayBook = NULL, $companyId);

                    } else if ($request->input('payment-mode') == 3 && $is_app == 1) {
                        $savingTransaction = $this->savingAccountTransactionDataNew($request->all(), $insertedid, $type, $shortName, $investmentAccount, $sAccount_associate, $daybookRefRD, $s = 2);

                        $res = SavingAccountTranscation::create($savingTransaction);

                        $satRefId = NULL;
                        $sAccountId = $ssbId_a;
                        $sAccountAmount = $ssbBalance_a - $request->input('amount');
                        $ssbDetals = SavingAccount::find($sAccountId);
                        $sData['balance'] = $sAccountAmount;
                        $ssbDetals->update($sData);
                        $ssbCreateTran = NULL;
                        $sAccountNumber = $ssbAccountNumber;
                        $created_at = date("Y-m-d H:i:s", strtotime(convertDate($globaldate)));
                        $updated_at = date("Y-m-d H:i:s", strtotime(convertDate($globaldate)));
                        $entry_date = date("Y-m-d", strtotime(convertDate($globaldate)));
                        $entry_time = date("H:i:s");

                        if (isset($request->is_app) && $request->is_app) {
                            $created_by = 3;
                            // $created_by_id =
                        } else {
                            $created_by = (Auth::user()->role_id == 3) ? 2 : 1;
                        }


                        $created_by_id = (isset($request->is_app) && $request->is_app) ? $request->input('associatemid') : Auth::user()->id;

                        $rdDesDR = $planName->name . '(' . $ssbDetals->account_no . ') A/c Dr ' . $request['amount'] . '/-';
                        $rdDesCR = 'To SSB(' . $accountNumber . ') A/c Cr ' . $request['amount'] . '/-';
                        $rdDes = 'Amount received for Account opening ' . $planName->name . '(' . $accountNumber . ') through SSB(' . $ssbDetals->account_no . ')';


                        $headPaymentModeRD = 3;
                        $transction_no = NULL;
                        $associatemid = $sAccount_associate->savingAccount[0]->member_id;

                        $allTranRDcheque = CommanTransactionFacade::createBranchDayBookNew($daybookRefRD, $branch_id, 4, 43, $ssbId_a, $request['associatemid'], $associatemid, $branch_id_to = NULL, $branch_id_from = NULL, $request['amount'], $rdDes, $rdDesDR, $rdDesCR, 'DR', $headPaymentModeRD, 'INR', $v_no = NULL, $sAccountId, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $createDayBook = NULL, $companyId);

                    } else {
                        $sAccountNumber = NULL;
                        $satRefId = NULL;
                        $ssbCreateTran = NULL;
                    }
            }
            $createDayBook = CommanController::createDayBookNew($daybookRefRD, $daybookRefRD, $traType, $ssbAccountId, $request['associatemid'], $mId, $request['amount'], $request['amount'], $withdrawal = 0, $description, $ssbAccountNumber, $branch_id, $branchCode, $amountArraySsb, $ppmode, $amount_deposit_by_name, $mId, $investmentAccount, $request['cheque-number'], $request['bank-name'], $request['branch-name'], $pdate, $online_transction_no, $online_payment_by = NULL, $ssbId_a, 'CR', $received_cheque_id, $cheque_deposit_bank_id, $cheque_deposit_bank_ac_id, $online_deposit_bank_id, $online_deposit_bank_ac_id, $companyId, $is_app);
            // ---------------------------  HEAD IMPLEMENT --------------------------
            ($planCategory == 'S')
                ?
                $this->investHeadCreateSSB($request['amount'], $globaldate, $ssbAccountId, $planId, $cheque_no, $cheque_id, $cheque_date, $online_transction_no, $online_transction_date, $online_deposit_bank_id, $online_deposit_bank_ac_id, $branch_id, $request['associatemid'], $mId, $sAccount_associate['savingAccount'][0]->account_no, $createDayBook, $request->input('payment-mode'), $investmentAccount, $companyId, $daybookRefRD, $request['is_app'])
                :
                $this->investHeadCreate($request['amount'], $globaldate, $insertedid, $planId, $cheque_no, $cheque_id, $cheque_date, $online_transction_no, $online_transction_date, $online_deposit_bank_id, $online_deposit_bank_ac_id, $branch_id, $request['associatemid'], $mId, $ssbId_a, $createDayBook, $request->input('payment-mode'), $accountNumber, $companyId, $daybookRefRD, $request['is_app']);
            if ($request['newUser'] == "false" && !$checkMemberExistInAnyComapany) {
                $this->memberCharges($request['amount'], $globaldate, $ssbAccountId, $planId, $cheque_no, $cheque_id, $cheque_date, $online_transction_no, $online_transction_date, $online_deposit_bank_id, $online_deposit_bank_ac_id, $branch_id, $request['associatemid'], $mData, $ssbId_a, $createDayBook, $request->input('payment-mode'), $investmentAccount, $companyId, $daybookRefRD, $request['is_app']);
            }
            $satRefId = NULL;
            $ssbCreateTran = NULL;
            //--------------------------------HEAD IMPLEMENT  -------------------------
            $transaction = $this->transactionData($satRefId, $request->all(), $insertedid, $type, $ssbCreateTran);
            $res = Investmentplantransactions::create($transaction);
            if ($request['fn_first_name']) {
                $fNominee = $this->getFirstNomineeData($request->all(), $insertedid, $type);
                $res = Memberinvestmentsnominees::create($fNominee);
            }
            if ($request['second_nominee_add'] == 1) {
                $sNominee = $this->getSecondNomineeData($request->all(), $insertedid, $type);
                $res = Memberinvestmentsnominees::create($sNominee);
            }
            if ($request->input('payment-mode') == 1) {
                $receivedPayment['type'] = 2;
                $receivedPayment['branch_id'] = $branch_id;
                $receivedPayment['investment_id'] = $insertedid;
                $receivedPayment['day_book_id'] = $createDayBook;
                $receivedPayment['cheque_id'] = $request['cheque_id'];
                // $receivedPayment['created_at'] = date("Y-m-d", strtotime(convertDate($request['created_at']))) ?? date("Y-m-d", strtotime(convertDate($request['create_application_date'])));
                $receivedPayment['created_at'] = $request['created_at'] ? date("Y-m-d", strtotime(convertDate($request['created_at']))) : ($request['create_application_date'] ? date("Y-m-d", strtotime(convertDate($request['create_application_date']))) : date('Y-m-d H:i:s'));
                $receivedCreate = ReceivedChequePayment::create($receivedPayment);
                $dataRC['status'] = 3;
                $receivedcheque = \App\Models\ReceivedCheque::find($request['cheque_id']);
                $receivedcheque->update($dataRC);
            }
            if ($request['stationary_charge'] > 0) {
                // $response = $this->stationaryCharges($insertedid, $companyId, $associate_app, $sAccount_associate);
                $response = $this->stationaryCharges($insertedid, $companyId, $associate_app, $sAccount_associate, $mId);

                if (!$response['status']) {
                    return $response;
                }
            }
            $contactNumber = array();
            $memberDetail = $this->getMemberId($request['memberAutoId'])->first();
            $contactNumber[] = $memberDetail->mobile_no;
            $sendToMember = new Sms();
            $sendToMember->sendSms($contactNumber, $text, $temaplteId);
            if ($res) {
                $plantypeid = $planCategory;
                if ($plantypeid == 'S') {
                    $collector_type = 'investmentsavingcollector';
                    $typeid = $res->id;
                } else {
                    $collector_type = 'investmentcollector';
                    $typeid = $res->investment_id;
                }
                $associateid = $request['associatemid'];
                $ff = CollectorAccountStoreLI($collector_type, $typeid, $associateid, $globaldate, $request['is_app']);

                $response = [
                    'insertedid' => $insertedid,
                    'status' => true,
                ];
            } else {
                $response = [
                    'insertedid' => '',
                    'status' => false,
                ];
            }
            DB::commit();
            return $response;
        } catch (\Exception $ex) {
            DB::rollback();
            dd("sfsfdg " . $ex->getLine() . '-' . $ex->getMessage());
            $response = [
                'insertedid' => $ex->getLine(),
                'status' => false,
                'msg' => $ex->getLine(),
            ];
            return $response;
        }
    }
    public function planForm($request, $repo)
    {
        $plan = $request->plan;
        $mId = $request->memberAutoId;
        $planCategory = $request->planCategory;
        $companyId = $request->companyId;
        $planId = $request->planId;
        $member = MemberNominee::where('member_id', $mId)->get();
        $plans_tenures = \App\Models\PlanTenures::where('plan_id', $planId)->whereStatus(1)->whereColumn('tenure', 'month_to')->get();
        $plans_tenure = $repo->getAllPlans()
            ->with('PlanTenures:roi,id,plan_id,tenure,month_from')
            ->has('PlanTenures')
            ->where('company_id', $request->companyId)
            ->where('status', '1')
            ->whereId($request->planId)
            ->first(['id', 'death_help', 'loan_against_deposit', 'prematurity']);
        $savingAccount = SavingAccount::where([
            ['customer_id', $mId],
            ['company_id', $companyId],
            ['status', 1],
            ['is_deleted', 0],
        ])->exists();
        $relations = Relations::all();
        $plan_amount = 0;
        $moduleName = ($request->sub_category == 'K') ? PlanCategory::whereCode($request->sub_category)->first('slug') : PlanCategory::whereCode($planCategory)->first('slug');
        ;
        $plan = $moduleName['slug'];
        $planArray = ['member' => $member, 'relations' => $relations, 'plans_tenure' => $plans_tenure, 'plan' => $plan, 'plans_tenures' => $plans_tenures, 'savingAccount' => $savingAccount];
        return $planArray;
    }
    /**
     * getData in array based on plan
     * @param $request, $type, $miCode, $investmentAccount, $branch_id, $faCode
     *
     */
    public function getData($request, $type, $miCode, $investmentAccount, $branch_id, $faCode)
    {
        $plantype = $request['plan_type'];
        $data['old_branch_id'] = $branch_id;
        $creatAt = Session::get('created_at');
        $globaldate = date('Y/m/d', strtotime($creatAt));
        switch ($plantype) {
            case "S":
                $data['deposite_amount'] = $request['amount'];
                $data['current_balance'] = $request['amount'];
                if ($plantype == "saving-account") {
                    $data['payment_mode'] = $request['payment-mode'];
                }
                break;
            case "D":
                $tenure = $request['tenure'];
                $data['tenure'] = $request['tenure'] / 12;
                $data['deposite_amount'] = $request['amount'];
                $data['payment_mode'] = $request['payment-mode'];
                $data['interest_rate'] = $request['interest-rate'];
                $data['maturity_amount'] = $request['maturity-amount'];
                $data['current_balance'] = $request['amount'];
                $data['maturity_date'] = date("Y-m-d", strtotime('+ ' . ($request['tenure']) . 'months', strtotime($globaldate)));
                break;
            case "F":
                $data['deposite_amount'] = $request['amount'];
                $data['payment_mode'] = $request['payment-mode'];
                $data['tenure'] = $request['tenure'] / 12;
                $data['maturity_amount'] = $request['maturity-amount'];
                $data['interest_rate'] = $request['interest-rate'];
                $data['current_balance'] = $request['amount'];
                $data['maturity_date'] = date("Y-m-d", strtotime('+ ' . ($request['tenure']) . 'months', strtotime($globaldate)));
                break;
            case "M":
                $tenure = $request['tenure'];
                $data['tenure'] = $request['tenure'] / 12;
                // $data['tenure_fa_code'] = $tenurefacode;
                $data['deposite_amount'] = $request['amount'];
                $data['payment_mode'] = $request['payment-mode'];
                $data['maturity_amount'] = $request['maturity-amount'];
                $data['interest_rate'] = $request['interest-rate'];
                $data['current_balance'] = $request['amount'];
                $data['maturity_date'] = date("Y-m-d", strtotime('+ ' . ($request['tenure']) . 'months', strtotime($globaldate)));
                break;
        }
        if ($type == 'create') {
            if ($plantype == 'S') {
                $data['ssb_account_number'] = $investmentAccount;
                $data['created_at'] = $creatAt;
            }
            $data['mi_code'] = $miCode;
            $data['account_number'] = $investmentAccount;
            $data['plan_id'] = $request['investmentplan'];
            $data['form_number'] = $request['form_number'];
            $data['customer_id'] = $request['memberAutoId'];
            $data['associate_id'] = $request['associatemid'];
            $data['branch_id'] = $branch_id;
            $data['company_id'] = $request['company_id'];
        }
        return $data;
    }
    /**
     * create transactionData for plan
     * @param  $satRefId, $request, $investmentId, $type, $transactionId, $sAccount
     *
     */
    public function transactionData($satRefId, $request, $investmentId, $type, $transactionId)
    {
        $getBranchId = (isset($request['is_app']) && $request['branchid']) ? getUserBranchId($request['branchid']) : getUserBranchId(Auth::user()->id);
        $branch_id = $getBranchId->id;
        $getBranchCode = getBranchCode($branch_id);
        $branchCode = $getBranchCode->branch_code;
        $creatAt = Session::get('created_at');
        $sAccount = $this->getMemberId($request['memberAutoId'])->with(['savingAccount:id,account_no,balance,member_id'])->first();
        $data['transaction_id'] = $transactionId;
        $data['transaction_ref_id'] = $satRefId;
        $data['investment_id'] = $investmentId;
        $data['plan_id'] = isset($request['investmentplan']) ? $request['investmentplan'] : 1;
        $data['member_id'] = $request['memberAutoId'];
        $data['branch_id'] = $branch_id;
        $data['branch_code'] = $branchCode;
        $data['deposite_amount'] = $request['amount'];
        $data['deposite_date'] = date('Y-m-d');
        $data['deposite_month'] = date('m');
        if ($request['plan_type'] == 'S') {
            $data['payment_mode'] = 0;
        } else {
            $data['payment_mode'] = $request['payment-mode'];
        }
        if (isset($sAccount['savingAccount'][0])) {
            $data['saving_account_id'] = $sAccount->id;
        } else {
            $data['saving_account_id'] = NULL;
        }
        $data['created_at'] = $creatAt;
        return $data;
    }
    /**
     * create stationaryCharges for the plan
     * @param $investmentId, $companyId
     *
     */
    public function memberCharges($amount, $globaldate, $investmentId, $planId, $cheque_no, $cheque_id, $cheque_date, $online_transction_no, $online_transction_date, $online_deposit_bank_id, $online_deposit_bank_ac_id, $branch_id, $associate_id, $memberId, $ssbId, $createDayBook, $payment_mode, $investmentAccountNoRd, $companyId, $daybookRefRD, $isApp = null)
    {
        DB::beginTransaction();
        try {
            $amount = $amount;
            $refIdRD = $daybookRefRD;
            $currency_code = 'INR';
            $headPaymentModeRD = 0;
            $type_id = $memberId->id;
            $entry_date = date("Y-m-d", strtotime(convertDate($globaldate)));
            $entry_time = date("H:i:s");
            $created_by = $isApp != null ? 3 : (Auth::user()->role_id == 3 ? 2 : 1);
            $created_by_id = ($isApp != Null) ? $associate_id : Auth::user()->id;
            $created_at = date('Y-m-d' . " " . date('H:i:s') . "", strtotime(convertDate($globaldate)));
            $updated_at = date('Y-m-d' . " " . date('H:i:s') . "", strtotime(convertDate($globaldate)));
            $v_no = NULL;
            $ssb_account_id_from = NULL;
            $cheque_no = NULL;
            $transction_no = NULL;
            $typeMI = 1;
            $sub_typeMI = 11;
            $head_idM1 = 55;
            $payment_type = 'CR';
            $sAccount_associate = $this->getMemberId($associate_id)->with([
                'savingAccount' => function ($q) use ($companyId) {
                    $q->where('company_id', $companyId);
                }
            ])->first();
            $head_idM2 = $head3STN2 = 28;
            $desMI = 'Cash received from member ' . $memberId->member->first_name . ' ' . $memberId->member->last_name . '(' . $memberId->member->member_id . ') through MI charge';
            $desMIDR = 'Cash A/c Dr 10/-';
            $desMICR = 'To ' . $memberId->member->first_name . ' ' . $memberId->member->last_name . '(' . $memberId->member->member_id . ') A/c Cr 10/-';
            $amountMi = 10;
            $amountSTN = 90;
            $typeSTN = 1;
            $sub_typeSTN = 12;
            $head3STN = 34;
            $desSTN = 'Cash received from member ' . $memberId->member->first_name . ' ' . $memberId->member->last_name . '(' . $memberId->member->member_id . ') through STN charge';
            $desSTNDR = 'Cash A/c Dr 90/-';
            $desSTNCR = 'To ' . $memberId->member->first_name . ' ' . $memberId->member->last_name . '(' . $memberId->member->member_id . ') A/c Cr 90/-';
            $memberId = $memberId->id;
            $companyId = (int) $companyId;

            $acc_no_for_desc = " for ($investmentAccountNoRd)";
            if ($isApp != NULL) {
                // STN charge memberInvestments
                $s_array = [
                    'associatemid' => $associate_id,
                    'branchid' => $branch_id,
                    'amount' => 90,
                    'create_application_date' => $globaldate,
                    'is_app' => 1,
                    'type' => 1,
                    'description' => "Stationary charge $acc_no_for_desc",
                    'company_id' => $companyId
                ];
                $type = null;
                $savingTransaction = $this->savingAccountTransactionDataNew($s_array, null, $type, null, null, $sAccount_associate, $daybookRefRD, $s = 3);
                $res = SavingAccountTranscation::create($savingTransaction);
                $ssbDetals = SavingAccount::find($sAccount_associate['savingAccount'][0]->id);
                $sData['balance'] = $ssbbal = (isset($sAccount_associate['savingAccount'][0])) ? $sAccount_associate['savingAccount'][0]->balance : '' - 90;
                $ssbDetals->update($sData);
                // MI Charge
                $s_array2 = [
                    'associatemid' => $associate_id,
                    'branchid' => $branch_id,
                    'amount' => 10,
                    'create_application_date' => $globaldate,
                    'is_app' => 1,
                    'type' => 1,
                    'description' => "MI charge $acc_no_for_desc",
                    'company_id' => $companyId
                ];

                $savingTransaction = $this->savingAccountTransactionDataNew($s_array2, null, $type, null, null, $sAccount_associate, $daybookRefRD, $s = 4);
                $res = SavingAccountTranscation::create($savingTransaction);
                $ssbDetals = SavingAccount::find($sAccount_associate['savingAccount'][0]->id);
                $sData['balance'] = $ssbbal - 10;
                $ssbDetals->update($sData);
                $headPaymentModeRD = 3;
                $planDetail = getPlanDetail($planId, $companyId);
                $head_idM2 = $head3STN2 = (($payment_type == 'CR') && ($isApp != null)) ? getplanheaddyanmic($sAccount_associate['savingAccount'][0]->id, $companyId) : $planDetail->deposit_head_id;
                $desSTN = 'Amount received from associate ' . $sAccount_associate->first_name . ' ' . $sAccount_associate->last_name . 'through ssb(' . $sAccount_associate['savingAccount'][0]->account_no . ') for STN charge';
                $desSTNDR = 'SSB A/c Dr 90/-';
                $desMI = 'Amount received from associate ' . $sAccount_associate->first_name . ' ' . $sAccount_associate->last_name . 'through ssb(' . $sAccount_associate['savingAccount'][0]->account_no . ') for MI charge';
                $desMIDR = 'SSB A/c Dr 10/-';
            }
            $allTranRDcheque = CommanTransactionFacade::headTransactionCreate($refIdRD, $branch_id, $bank_id = NULL, $bank_ac_id = NULL, $head_idM1, $typeMI, $sub_typeMI, $type_id, $associate_id, $memberId, $branch_id_to = NULL, $branch_id_from = NULL, $amountMi, $desMI, $payment_type, $headPaymentModeRD, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $createDayBook, NULL, $ssb_account_id_to = NULL, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, 0, $cheque_id, $companyId, $isApp);
            // dd('stop');
            $allTranRDcheque = CommanTransactionFacade::createBranchDayBookNew($refIdRD, $branch_id, $typeMI, $sub_typeMI, $type_id, $associate_id, $memberId, $branch_id_to = NULL, $branch_id_from = NULL, $amountMi, $desMI, $desMIDR, $desMICR, $payment_type, $headPaymentModeRD, $currency_code, $v_no, $ssb_account_id_from, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $createDayBook, $companyId);
            if ($isApp != NULL) {
                $ssbaccountdetails = SavingAccount::whereId($ssbId)->first()->member_id;
                $allTranRDcheque = CommanTransactionFacade::createBranchDayBookNew($refIdRD, $branch_id, 4, 43, $ssbId, $associate_id, $ssbaccountdetails, $branch_id_to = NULL, $branch_id_from = NULL, $amountMi, $desMI, $desMIDR, $desMICR, 'DR', $headPaymentModeRD, $currency_code, $v_no, $ssb_account_id_from, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $createDayBook, $companyId);
                $allTranRDcheque = CommanTransactionFacade::headTransactionCreate($refIdRD, $branch_id, $bank_id = NULL, $bank_ac_id = NULL, $head_idM2, 4, 43, $ssbId, $associate_id, $ssbaccountdetails, $branch_id_to = NULL, $branch_id_from = NULL, $amountMi, $desMI, 'DR', $headPaymentModeRD, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $createDayBook, NULL, $ssb_account_id_to = NULL, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, 0, $cheque_id, $companyId, $isApp);
            } else {
                $allTranRDcheque = CommanTransactionFacade::headTransactionCreate($refIdRD, $branch_id, $bank_id = NULL, $bank_ac_id = NULL, $head_idM2, $typeMI, $sub_typeMI, $type_id, $associate_id, $memberId, $branch_id_to = NULL, $branch_id_from = NULL, $amountMi, $desMI, 'DR', $headPaymentModeRD, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $createDayBook, NULL, $ssb_account_id_to = NULL, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, 0, $cheque_id, $companyId, $isApp);
            }
            $allTranRDcheque = CommanTransactionFacade::headTransactionCreate($refIdRD, $branch_id, $bank_id = NULL, $bank_ac_id = NULL, $head3STN, $typeSTN, $sub_typeSTN, $type_id, $associate_id, $memberId, $branch_id_to = NULL, $branch_id_from = NULL, $amountSTN, $desSTN, $payment_type, $headPaymentModeRD, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $createDayBook, NULL, $ssb_account_id_to = NULL, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, 0, $cheque_id, $companyId, $isApp);

            $allTranRDcheque = CommanTransactionFacade::createBranchDayBookNew($refIdRD, $branch_id, $typeSTN, $sub_typeSTN, $type_id, $associate_id, $memberId, $branch_id_to = NULL, $branch_id_from = NULL, $amountSTN, $desSTN, $desSTNDR, $desSTNCR, $payment_type, $headPaymentModeRD, $currency_code, $v_no, $ssb_account_id_from, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $createDayBook, $companyId);
            if ($isApp != NULL) {
                $ssbaccountdetails = SavingAccount::whereId($ssbId)->first()->member_id;
                $allTranRDcheque = CommanTransactionFacade::createBranchDayBookNew($refIdRD, $branch_id, 4, 43, $ssbId, $associate_id, $ssbaccountdetails, $branch_id_to = NULL, $branch_id_from = NULL, $amountSTN, $desSTN, $desSTNDR, $desSTNCR, 'DR', $headPaymentModeRD, $currency_code, $v_no, $ssb_account_id_from, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $createDayBook, $companyId);

                $allTranRDcheque = CommanTransactionFacade::headTransactionCreate($refIdRD, $branch_id, $bank_id = NULL, $bank_ac_id = NULL, $head3STN2, 4, 43, $ssbId, $associate_id, $ssbaccountdetails, $branch_id_to = NULL, $branch_id_from = NULL, $amountSTN, $desSTN, 'DR', $headPaymentModeRD, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $createDayBook, NULL, $ssb_account_id_to = NULL, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, 0, $cheque_id, $companyId, $isApp);
            } else {
                $allTranRDcheque = CommanTransactionFacade::headTransactionCreate($refIdRD, $branch_id, $bank_id = NULL, $bank_ac_id = NULL, $head3STN2, $typeSTN, $sub_typeSTN, $type_id, $associate_id, $memberId, $branch_id_to = NULL, $branch_id_from = NULL, $amountSTN, $desSTN, 'DR', $headPaymentModeRD, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $createDayBook, NULL, $ssb_account_id_to = NULL, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, 0, $cheque_id, $companyId, $isApp);
            }
            DB::commit();
        } catch (\Exception $ex) {
            dd("tgjgjg: " . $ex->getMessage(), $ex->getLine(), $ex->getFile());
            $response = [
                'status' => true,
                'msg' => $ex->getMessage(),
            ];
        }
        return true;
    }
    /*
    public function memberCharges($amount, $globaldate, $investmentId, $planId, $cheque_no, $cheque_id, $cheque_date, $online_transction_no, $online_transction_date, $online_deposit_bank_id, $online_deposit_bank_ac_id, $branch_id, $associate_id, $memberId, $ssbId, $createDayBook, $payment_mode, $investmentAccountNoRd, $companyId, $daybookRefRD, $isApp = Null)
    {
        DB::beginTransaction();
        try {
            $amount = $amount;
            $refIdRD = $daybookRefRD;
            $currency_code = 'INR';
            $headPaymentModeRD = 0;
            $type_id = $memberId->id;
            $entry_date = date("Y-m-d", strtotime(convertDate($globaldate)));
            $entry_time = date("H:i:s");
            $created_by = $isApp != null ? 3 : (Auth::user()->role_id == 3 ? 2 : 1);
            $created_by_id = ($isApp != Null) ? $associate_id : Auth::user()->id;
            $created_at = date("Y-m-d H:i:s", strtotime(convertDate($globaldate)));
            $updated_at = date("Y-m-d H:i:s", strtotime(convertDate($globaldate)));
            $v_no = NULL;
            $ssb_account_id_from = NULL;
            $cheque_no = NULL;
            $transction_no = NULL;
            $typeMI = 1;
            $sub_typeMI = 11;
            $head_idM1 = 55;
            $head_idM2 = 28;
            $desMI = 'Cash received from member ' . $memberId->member->first_name . ' ' . $memberId->member->last_name . '(' . $memberId->member->member_id . ') through MI charge';
            $desMIDR = 'Cash A/c Dr 10/-';
            $desMICR = 'To ' . $memberId->member->first_name . ' ' . $memberId->member->last_name . '(' . $memberId->member->member_id . ') A/c Cr 10/-';
            $amountMi = 10;
            $amountSTN = 90;
            $typeSTN = 1;
            $sub_typeSTN = 12;
            $head3STN = 34;
            $head3STN2 = 28;
            $desSTN = 'Cash received from member ' . $memberId->member->first_name . ' ' . $memberId->member->last_name . '(' . $memberId->member->member_id . ') through STN charge';
            $desSTNDR = 'Cash A/c Dr 90/-';
            $desSTNCR = 'To ' . $memberId->member->first_name . ' ' . $memberId->member->last_name . '(' . $memberId->member->member_id . ') A/c Cr 90/-';
            $memberId = $memberId->id;
            $companyId = (int) $companyId;
            $payment_type = 'CR';

            $acc_no_for_desc = " for ($investmentAccountNoRd)";
            if ($isApp != NULL) {
                $sAccount_associate = $this->getMemberId($associate_id)->with([
                    'savingAccount' => function ($q) use ($companyId) {
                        $q->where('company_id', $companyId);
                    }
                ])->first();
                // STN charge memberInvestments
                $s_array = [
                    'associatemid' => $associate_id,
                    'branchid' => $branch_id,
                    'amount' => 90,
                    'create_application_date' => $globaldate,
                    'is_app' => 1,
                    'type' => 1,
                    'description' => "Stationary charge $acc_no_for_desc",
                    'company_id' => $companyId
                ];
                $type = null;
                $savingTransaction = $this->savingAccountTransactionDataNew($s_array, null, $type, null, null, $sAccount_associate, $daybookRefRD, $s = 3);
                $res = SavingAccountTranscation::create($savingTransaction);
                $ssbDetals = SavingAccount::find($sAccount_associate['savingAccount'][0]->id);
                $sData['balance'] = $ssbbal = (isset($sAccount_associate['savingAccount'][0])) ? $sAccount_associate['savingAccount'][0]->balance : '' - 90;
                $ssbDetals->update($sData);
                // MI Charge
                $s_array2 = [
                    'associatemid' => $associate_id,
                    'branchid' => $branch_id,
                    'amount' => 10,
                    'create_application_date' => $globaldate,
                    'is_app' => 1,
                    'type' => 1,
                    'description' => "MI charge $acc_no_for_desc",
                    'company_id' => $companyId
                ];
                $sAccount_associate = $this->getMemberId($associate_id)->with([
                    'savingAccount' => function ($q) use ($companyId) {
                        $q->where('company_id', $companyId);
                    }
                ])->first();
                $savingTransaction = $this->savingAccountTransactionDataNew($s_array2, null, $type, null, null, $sAccount_associate, $daybookRefRD, $s = 4);
                $res = SavingAccountTranscation::create($savingTransaction);
                $ssbDetals = SavingAccount::find($sAccount_associate['savingAccount'][0]->id);
                $sData['balance'] = $ssbbal - 10;
                $ssbDetals->update($sData);
                $headPaymentModeRD = 3;
                $planDetail = getPlanDetail($planId, $companyId);
                $head_idM2 = $head3STN2 = $planDetail->deposit_head_id;
                $desSTN = 'Amount received from associate ' . $sAccount_associate->first_name . ' ' . $sAccount_associate->last_name . 'through ssb(' . $sAccount_associate['savingAccount'][0]->account_no . ') for STN charge';
                $desSTNDR = 'SSB A/c Dr 90/-';

                $desMI = 'Amount received from associate ' . $sAccount_associate->first_name . ' ' . $sAccount_associate->last_name . 'through ssb(' . $sAccount_associate['savingAccount'][0]->account_no . ') for MI charge';
                $desMIDR = 'SSB A/c Dr 10/-';
            }

            $allTranRDcheque = CommanTransactionFacade::headTransactionCreate($refIdRD, $branch_id, $bank_id = NULL, $bank_ac_id = NULL, $head_idM1, $typeMI, $sub_typeMI, $type_id, $associate_id, $memberId, $branch_id_to = NULL, $branch_id_from = NULL, $amountMi, $desMI, $payment_type, $headPaymentModeRD, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $createDayBook, NULL, $ssb_account_id_to = NULL, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, 0, $cheque_id, $companyId, $isApp);

            $allTranRDcheque = CommanTransactionFacade::createBranchDayBookNew($refIdRD, $branch_id, $typeMI, $sub_typeMI, $type_id, $associate_id, $memberId, $branch_id_to = NULL, $branch_id_from = NULL, $amountMi, $desMI, $desMIDR, $desMICR, $payment_type, $headPaymentModeRD, $currency_code, $v_no, $ssb_account_id_from, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $createDayBook, $companyId);
            $allTranRDcheque = CommanTransactionFacade::headTransactionCreate($refIdRD, $branch_id, $bank_id = NULL, $bank_ac_id = NULL, $head_idM2, $typeMI, $sub_typeMI, $type_id, $associate_id, $memberId, $branch_id_to = NULL, $branch_id_from = NULL, $amountMi, $desMI, 'DR', $headPaymentModeRD, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $createDayBook, NULL, $ssb_account_id_to = NULL, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, 0, $cheque_id, $companyId, $isApp);
            //STN
            $allTranRDcheque = CommanTransactionFacade::headTransactionCreate($refIdRD, $branch_id, $bank_id = NULL, $bank_ac_id = NULL, $head3STN, $typeSTN, $sub_typeSTN, $type_id, $associate_id, $memberId, $branch_id_to = NULL, $branch_id_from = NULL, $amountSTN, $desSTN, $payment_type, $headPaymentModeRD, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $createDayBook, NULL, $ssb_account_id_to = NULL, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, 0, $cheque_id, $companyId, $isApp);
            $allTranRDcheque = CommanTransactionFacade::createBranchDayBookNew($refIdRD, $branch_id, $typeSTN, $sub_typeSTN, $type_id, $associate_id, $memberId, $branch_id_to = NULL, $branch_id_from = NULL, $amountSTN, $desSTN, $desSTNDR, $desSTNCR, $payment_type, $headPaymentModeRD, $currency_code, $v_no, $ssb_account_id_from, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $createDayBook, $companyId);
            $allTranRDcheque = CommanTransactionFacade::headTransactionCreate($refIdRD, $branch_id, $bank_id = NULL, $bank_ac_id = NULL, $head3STN2, $typeSTN, $sub_typeSTN, $type_id, $associate_id, $memberId, $branch_id_to = NULL, $branch_id_from = NULL, $amountSTN, $desSTN, 'DR', $headPaymentModeRD, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $createDayBook, NULL, $ssb_account_id_to = NULL, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, 0, $cheque_id, $companyId, $isApp);
            DB::commit();
        } catch (\Exception $ex) {

            dd("tgjgjg: " . $ex->getMessage(), $ex->getLine());
            $response = [
                'status' => true,
                'msg' => $ex->getMessage(),
            ];
        }
        return true;
    }
     */
    /*
    public function stationaryCharges($investmentId, $companyId, $isApp = Null, $sAccount_associate = null)
    {

        DB::beginTransaction();
        try {

            $memberInvestments = Memberinvestments::with(['branch', 'member'])->whereId( $investmentId)->first();
            $entryTime = date("H:i:s");
            Session::put('created_at', date("Y-m-d " . $entryTime . "", strtotime(convertDate($memberInvestments->created_at))));
            $vno = NULL;
            $branch_id = $memberInvestments->branch_id;
            $type = 3;
            $sub_type = 35;
            $type_id = $memberInvestments->id;
            $associate_id = $memberInvestments->associate_id;
            $member_id = $memberInvestments->member_id;
            $amount = 50;
            $description = 'Stationary charges on investment plan registration ' . $amount . ' for (' . $memberInvestments->account_number . ')';
            $description_dr = 'Stationary charges on investment plan registration ' . $amount;
            $description_cr = 'Stationary charges on investment plan registration ' . $amount;
            $payment_mode = 0;
            $currency_code = 'INR';
            $sub_type_cgst = 321;
            $sub_type_sgst = 322;
            $sub_type_igst = 323;
            $v_no = $vno;
            $ssb_account_id_from = NULL;
            $cheque_no = NULL;
            $transction_no = NULL;
            $entry_date = $memberInvestments->created_at;
            $entry_time = date("H:i:s");

            $created_by = (($isApp != null) ? 3 : (Auth::user()->role_id == 3)) ? 2 : 1;

            if ($isApp == 3) {
                $created_by_id = $associate_id;
                $payment_mode = 3;
            } else {
                $created_by_id = ($isApp != Null) ? $isApp : Auth::user()->id;
            }
            $created_at = $memberInvestments->created_at;
            $dayBookRef = CommanController::createBranchDayBookReference($amount);
            $detail = getBranchDetail($memberInvestments['branch']->id)->state_id;
            $globaldate = checkMonthAvailability(date('d'), date('m'), date('Y'), $detail);
            $getHeadSetting = HeadSetting::where('head_id', 122)->first();
            $getGstSetting = GstSetting::where('state_id', $memberInvestments['branch']->state_id)->where('applicable_date', '<=', $globaldate)->where('company_id', $companyId)->exists();
            $gstAmount = 0;
            $getGstSettingno = GstSetting::select('id', 'gst_no', 'state_id')->where('state_id', $memberInvestments['branch']->state_id)->where('applicable_date', '<=', $globaldate)->where('company_id', $companyId)->first();
            if (isset($getHeadSetting->gst_percentage) && $getGstSetting) {
                if ($memberInvestments['branch']->state_id == $getGstSettingno->state_id) {
                    $gstAmount = ceil(($amount * $getHeadSetting->gst_percentage) / 100) / 2;
                    $cgstHead = 171;
                    $sgstHead = 172;
                    $IntraState = true;
                } else {
                    $gstAmount = ceil($amount * $getHeadSetting->gst_percentage) / 100;
                    $cgstHead = 170;
                    $IntraState = false;
                }
                $msg = true;
            } else {
                $IntraState = false;
                $msg = false;
            }
            $data['investment_id'] = $memberInvestments->id;
            $data['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($memberInvestments->created_at)));
            //   $satRef = TransactionReferences::create($data);
            $satRefId = NULL;
            $amountArraySsb = array('1' => (50));
            if ($isApp != null) {
                $created_by = 3;
            } else {
                $created_by = (Auth::user()->role_id == 3) ? 2 : 1;
            }
            if ($payment_mode == 3) {
                $day_book_payment_mode = 4;
            } else {
                $day_book_payment_mode = $payment_mode;
            }

            $createDayBook = CommanController::createDayBook($dayBookRef, $dayBookRef, 19, $memberInvestments->id, $associate_id, $memberInvestments->customer_id, 50, 50, $withdrawal = 0, $description, $memberInvestments->account_number, $memberInvestments->branch_id, getBranchCode($memberInvestments->branch_id)->branch_code, $amountArraySsb, $day_book_payment_mode, NULL, $memberInvestments->customer_id, $memberInvestments->account_number, 50, NULL, NULL, $created_at, NULL, NULL, NULL, 'CR', $companyId, $isApp);

            if ($isApp != NULL) {
                $sAccount_associate = $this->getMemberId($associate_id)->with([
                    'savingAccount' => function ($q) use ($companyId) {
                        $q->where('company_id', $companyId);
                    }
                ])->first();
                $s_array = [
                    'associatemid' => $associate_id,
                    'branchid' => $branch_id,
                    'amount' => 50,
                    'create_application_date' => $memberInvestments->created_at,
                    'is_app' => 1,
                    'type' => 1,
                    'description' => $description,
                    'company_id' => $companyId
                ];
                $savingTransaction = $this->savingAccountTransactionDataNew($s_array, null, $type, null, null, $sAccount_associate, $dayBookRef, $s = 3);
                $res = SavingAccountTranscation::create($savingTransaction);
                $ssbDetals = SavingAccount::find($sAccount_associate['savingAccount'][0]->id);
                $sData['balance'] = $ssbbal = (isset($sAccount_associate['savingAccount'][0])) ? $sAccount_associate['savingAccount'][0]->balance : '' - 50;
                $ssbDetals->update($sData);

                $planDetail = getPlanDetail($memberInvestments->plan_id, $companyId);
                $head4Invest = $planDetail->deposit_head_id;
            }

            $allTranRDcheque = CommanTransactionFacade::headTransactionCreate($dayBookRef, $branch_id, $bank_id = NULL, $bank_ac_id = NULL, 122, $type, $sub_type, $type_id, $associate_id, $member_id, $branch_id_to = NULL, $branch_id_from = NULL, $amount, $description, 'CR', $payment_mode, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $created_at, $createDayBook, NULL, $ssb_account_id_to = NULL, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, 0, $cheque_id = NULL, $companyId, $isApp);

            $allTranRDcheque = CommanTransactionFacade::headTransactionCreate($dayBookRef, $branch_id, $bank_id = NULL, $bank_ac_id = NULL, $head4Invest ?? 28, $type, $sub_type, $type_id, $associate_id, $member_id, $branch_id_to = NULL, $branch_id_from = NULL, $amount, $description, 'DR', $payment_mode, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $created_at, $createDayBook, NULL, $ssb_account_id_to = NULL, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, 0, $cheque_id = NULL, $companyId, $isApp);

            $allTranRDcheque = CommanTransactionFacade::createBranchDayBookNew($dayBookRef, $branch_id, $type, $sub_type, $type_id, $associate_id, $member_id, $branch_id_to = NULL, $branch_id_from = NULL, $amount, $description, $description_dr, $description_cr, 'CR', $payment_mode, $currency_code, $v_no, $ssb_account_id_from, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $created_at, $createDayBook, $companyId);

            if ($gstAmount > 0) {
                $createdGstTransaction = CommanController::gstTransaction($dayBookId = $dayBookRef, $getGstSettingno->gst_no, (!isset($memberInvestments['memberCompany']->member->gst_no)) ? NULL : $memberInvestments['memberCompany']->member->gst_no, 50, $getHeadSetting->gst_percentage, ($IntraState == false ? $gstAmount : 0), ($IntraState == true ? $gstAmount : 0), ($IntraState == true ? $gstAmount : 0), ($IntraState == true) ? $gstAmount + $gstAmount : $gstAmount, 122, $entry_date, 'IPC122', $memberInvestments->memberCompany->id, $branch_id, $companyId);




                if ($IntraState) {
                    $description = 'Stationary  Cgst Charge (' . $memberInvestments->account_number . ')';
                    $descriptionB = 'Stationary Sgst Charge (' . $memberInvestments->account_number . ')';
                    $amountArraySsb = array('1' => ($gstAmount));
                    $createDayBook = CommanController::createDayBook($dayBookRef, $satRefId, 26, $memberInvestments->id, $associate_id, $memberInvestments->customer_id, $gstAmount, $gstAmount, $withdrawal = 0, ($getHeadSetting->gst_percentage / 2) . 'CGST Charge on Stationary Charge', $memberInvestments->account_number, $memberInvestments->branch_id, getBranchCode($memberInvestments->branch_id)->branch_code, $amountArraySsb, $day_book_payment_mode, NULL, $memberInvestments->customer_id, $memberInvestments->account_number, 50, NULL, NULL, date("Y-m-d", strtotime(convertDate($memberInvestments->created_at))), NULL, NULL, NULL, 'CR', $companyId, $isApp);

                    $createDayBook = CommanController::createDayBook($dayBookRef, $satRefId, 27, $memberInvestments->id, $associate_id, $memberInvestments->customer_id, $gstAmount, $gstAmount, $withdrawal = 0, ($getHeadSetting->gst_percentage / 2) . 'SGST Charge on Stationary Charge', $memberInvestments->account_number, $memberInvestments->branch_id, getBranchCode($memberInvestments->branch_id)->branch_code, $amountArraySsb, $day_book_payment_mode, NULL, $memberInvestments->customer_id, $memberInvestments->account_number, 50, NULL, NULL, date("Y-m-d", strtotime(convertDate($memberInvestments->created_at))), NULL, NULL, NULL, 'CR', $companyId, $isApp);

                    if ($isApp != NULL) {
                        $sAccount_associate_c = $this->getMemberId($associate_id)->with([
                            'savingAccount' => function ($q) use ($companyId) {
                                $q->where('company_id', $companyId);
                            }
                        ])->first();
                        $s_array = [
                            'associatemid' => $associate_id,
                            'branchid' => $branch_id,
                            'amount' => $gstAmount,
                            'create_application_date' => $memberInvestments->created_at,
                            'is_app' => 1,
                            'type' => 1,
                            'description' => $description,
                            'company_id' => $companyId
                        ];
                        $sAccount_associate_c['savingAccount'][0]->balance = $ssbbal ?? $sAccount_associate_c['savingAccount'][0]->balance;

                        $savingTransaction = $this->savingAccountTransactionDataNew($s_array, null, $type, null, null, $sAccount_associate_c, $dayBookRef, $s = 4);
                        $res = SavingAccountTranscation::create($savingTransaction);
                        $ssbDetals = SavingAccount::find($sAccount_associate_c['savingAccount'][0]->id);
                        $sData['balance'] = $ssbbal = (isset($sAccount_associate_c['savingAccount'][0])) ? $sAccount_associate_c['savingAccount'][0]->balance : '' - $gstAmount;
                        $ssbDetals->update($sData);
                    }
                    $allTranRDcheque = CommanTransactionFacade::headTransactionCreate($dayBookRef, $branch_id, $bank_id = NULL, $bank_ac_id = NULL, $cgstHead, $type, $sub_type_cgst, $type_id, $associate_id, $member_id, $branch_id_to = NULL, $branch_id_from = NULL, $gstAmount, $description, 'CR', $payment_mode, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $created_at, $createdGstTransaction, NULL, $ssb_account_id_to = NULL, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, 0, $cheque_id = NULL, $companyId, $isApp);

                    $allTranRDcheque = CommanTransactionFacade::headTransactionCreate($dayBookRef, $branch_id, $bank_id = NULL, $bank_ac_id = NULL, $head4Invest ?? 28, $type, $sub_type_cgst, $type_id, $associate_id, $member_id, $branch_id_to = NULL, $branch_id_from = NULL, $gstAmount, $description, 'DR', $payment_mode, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $created_at, $createdGstTransaction, NULL, $ssb_account_id_to = NULL, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, 0, $cheque_id = NULL, $companyId, $isApp);

                    $allTranRDcheque = CommanTransactionFacade::headTransactionCreate($dayBookRef, $branch_id, $bank_id = NULL, $bank_ac_id = NULL, $sgstHead, $type, $sub_type_sgst, $type_id, $associate_id, $member_id, $branch_id_to = NULL, $branch_id_from = NULL, $gstAmount, $descriptionB, 'CR', $payment_mode, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $created_at, $createdGstTransaction, NULL, $ssb_account_id_to = NULL, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, 0, $cheque_id = NULL, $companyId, $isApp);

                    $allTranRDcheque = CommanTransactionFacade::headTransactionCreate($dayBookRef, $branch_id, $bank_id = NULL, $bank_ac_id = NULL, $head4Invest ?? 28, $type, $sub_type_sgst, $type_id, $associate_id, $member_id, $branch_id_to = NULL, $branch_id_from = NULL, $gstAmount, $descriptionB, 'DR', $payment_mode, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $created_at, $createdGstTransaction, NULL, $ssb_account_id_to = NULL, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, 0, $cheque_id = NULL, $companyId, $isApp);

                    $allTranRDcheque = CommanTransactionFacade::createBranchDayBookNew($dayBookRef, $branch_id, $type, $sub_type_cgst, $type_id, $associate_id, $member_id, $branch_id_to = NULL, $branch_id_from = NULL, $gstAmount, $description, $description, $description, 'CR', $payment_mode, $currency_code, $v_no, $ssb_account_id_from, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $created_at, $createdGstTransaction, $companyId);

                    $allTranRDcheque = CommanTransactionFacade::createBranchDayBookNew($dayBookRef, $branch_id, $type, $sub_type_sgst, $type_id, $associate_id, $member_id, $branch_id_to = NULL, $branch_id_from = NULL, $gstAmount, $descriptionB, $descriptionB, $descriptionB, 'CR', $payment_mode, $currency_code, $v_no, $ssb_account_id_from, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $created_at, $createdGstTransaction, $companyId);


                    if ($isApp != NULL) {
                        $sAccount_associate_s = $this->getMemberId($associate_id)->with([
                            'savingAccount' => function ($q) use ($companyId) {
                                $q->where('company_id', $companyId);
                            }
                        ])->first();
                        $s_array = [
                            'associatemid' => $associate_id,
                            'branchid' => $branch_id,
                            'amount' => $gstAmount,
                            'create_application_date' => $memberInvestments->created_at,
                            'is_app' => 1,
                            'type' => 1,
                            'description' => $descriptionB,
                            'company_id' => $companyId
                        ];
                        $sAccount_associate_s['savingAccount'][0]->balance = $ssbbal ?? $sAccount_associate_s['savingAccount'][0]->balance;
                        $savingTransaction = $this->savingAccountTransactionDataNew($s_array, null, $type, null, null, $sAccount_associate_s, $dayBookRef, $s = 5);
                        $res = SavingAccountTranscation::create($savingTransaction);
                        $ssbDetals = SavingAccount::find($sAccount_associate_s['savingAccount'][0]->id);
                        $sData['balance'] = $ssbbal = (isset($sAccount_associate_s['savingAccount'][0])) ? $sAccount_associate_s['savingAccount'][0]->balance : '' - $gstAmount;
                        $ssbDetals->update($sData);
                    }

                    $rec = [
                        'cgst_stationary_chrg' => $gstAmount,
                        'sgst_stationary_chrg' => $gstAmount,
                        'invoice_id' => $createdGstTransaction,
                    ];
                } else {
                    $description = 'Stationary  Igst Charge (' . $memberInvestments->account_number . ')';
                    $amountArraySsb = array('1' => ($gstAmount));
                    $createDayBook = CommanController::createDayBook($dayBookRef, $satRefId, 28, $memberInvestments->id, NULL, $memberInvestments->customer_id, $gstAmount, $gstAmount, $withdrawal = 0, ($getHeadSetting->gst_percentage) . 'IGST Charge on Stationary Charge', $memberInvestments->account_number, $memberInvestments->branch_id, getBranchCode($memberInvestments->branch_id)->branch_code, $amountArraySsb, $day_book_payment_mode ?? 0, NULL, $memberInvestments->customer_id, $memberInvestments->account_number, $gstAmount, NULL, NULL, date("Y-m-d", strtotime(convertDate($memberInvestments->created_at))), NULL, NULL, NULL, 'CR', $companyId, $isApp);

                    $allTranRDcheque = CommanTransactionFacade::headTransactionCreate($dayBookRef, $branch_id, $bank_id = NULL, $bank_ac_id = NULL, $cgstHead, $type, $sub_type_igst, $type_id, $associate_id, $member_id, $branch_id_to = NULL, $branch_id_from = NULL, $gstAmount, $description, 'CR', $payment_mode, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $created_at, $createdGstTransaction, NULL, $ssb_account_id_to = NULL, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, 0, $cheque_id = NULL, $companyId, $isApp);

                    $allTranRDcheque = CommanTransactionFacade::headTransactionCreate($dayBookRef, $branch_id, $bank_id = NULL, $bank_ac_id = NULL, $head4Invest ?? 28, $type, $sub_type_igst, $type_id, $associate_id, $member_id, $branch_id_to = NULL, $branch_id_from = NULL, $gstAmount, $description, 'DR', $payment_mode, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $created_at, $createdGstTransaction, NULL, $ssb_account_id_to = NULL, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, 0, $cheque_id = NULL, $companyId, $isApp);

                    $allTranRDcheque = CommanTransactionFacade::createBranchDayBookNew($dayBookRef, $branch_id, $type, $sub_type_igst, $type_id, $associate_id, $member_id, $branch_id_to = NULL, $branch_id_from = NULL, $amount, $description, $description, $description, 'CR', $payment_mode, $currency_code, $v_no, $ssb_account_id_from, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $created_at, $createdGstTransaction, $companyId);

                    if ($isApp != NULL) {
                        $sAccount_associate_i = $this->getMemberId($associate_id)->with([
                            'savingAccount' => function ($q) use ($companyId) {
                                $q->where('company_id', $companyId);
                            }
                        ])->first();
                        $s_array = [
                            'associatemid' => $associate_id,
                            'branchid' => $branch_id,
                            'amount' => $gstAmount,
                            'create_application_date' => $memberInvestments->created_at,
                            'is_app' => 1,
                            'type' => 1,
                            'description' => $description,
                            'company_id' => $companyId
                        ];
                        $sAccount_associate_i['savingAccount'][0]->balance = $ssbbal ?? $sAccount_associate_i['savingAccount'][0]->balance;
                        $savingTransaction = $this->savingAccountTransactionDataNew($s_array, null, $type, null, null, $sAccount_associate_i, $dayBookRef, $s = 7);
                        $res = SavingAccountTranscation::create($savingTransaction);
                        $ssbDetals = SavingAccount::find($sAccount_associate_i['savingAccount'][0]->id);
                        $sData['balance'] = (isset($sAccount_associate_i['savingAccount'][0])) ? $sAccount_associate_i['savingAccount'][0]->balance : '' - $gstAmount;
                        $ssbDetals->update($sData);
                    }
                    $rec = [
                        'igst_stationary_chrg' => $gstAmount,
                        'invoice_id' => $createdGstTransaction,
                    ];
                }
                $memberInvestments->update($rec);
            }
            $response = [
                'status' => true,
            ];
            DB::commit();
        } catch (\Exception $ex) {
            dd("dfs: " . $ex->getLine());
            $response = [
                'status' => true,
                'msg' => $ex->getMessage(),
            ];
        }
        return $response;
    }
    */
    public function stationaryCharges($investmentId, $companyId, $isApp = Null, $sAccount_associate = null, $mId)
    {
        DB::beginTransaction();
        try {
            $memberInvestments = Memberinvestments::with(['branch', 'member'])->whereId($investmentId)->first();
            $entryTime = date("H:i:s");
            Session::put('created_at', date("Y-m-d " . $entryTime . "", strtotime(convertDate($memberInvestments->created_at))));
            $vno = NULL;
            $branch_id = $memberInvestments->branch_id;
            $type = 3;
            $sub_type = 35;
            $type_id = $memberInvestments->id;
            $associate_id = $memberInvestments->associate_id;
            $member_id = $memberInvestments->member_id;
            $amount = 50;
            $description = 'Stationary charges on investment plan registration ' . $amount . ' for (' . $memberInvestments->account_number . ')';
            $description_dr = 'Stationary charges on investment plan registration ' . $amount;
            $description_cr = 'Stationary charges on investment plan registration ' . $amount;
            $payment_mode = 0;
            $currency_code = 'INR';
            $sub_type_cgst = 321;
            $sub_type_sgst = 322;
            $sub_type_igst = 323;
            $v_no = $vno;
            $ssb_account_id_from = NULL;
            $cheque_no = NULL;
            $transction_no = NULL;
            $entry_date = $memberInvestments->created_at;
            $entry_time = date("H:i:s");
            $created_by = (($isApp != null) ? 3 : (Auth::user()->role_id == 3)) ? 2 : 1;
            if ($isApp == 3) {
                $created_by_id = $associate_id;
                $payment_mode = 3;
            } else {
                $created_by_id = ($isApp != Null) ? $isApp : Auth::user()->id;
            }
            $created_at = $memberInvestments->created_at;
            $dayBookRef = CommanController::createBranchDayBookReference($amount);
            // $ssbDetals = SavingAccount::find($sAccount_associate['savingAccount'][0]->id);
            $detail = getBranchDetail($memberInvestments['branch']->id)->state_id;
            $globaldate = checkMonthAvailability(date('d'), date('m'), date('Y'), $detail);
            $getHeadSetting = HeadSetting::where('head_id', 122)->first();
            $getGstSetting = GstSetting::where('state_id', $memberInvestments['branch']->state_id)->where('applicable_date', '<=', $globaldate)->where('company_id', $companyId)->exists();
            $gstAmount = 0;
            $getGstSettingno = GstSetting::select('id', 'gst_no', 'state_id')->where('state_id', $memberInvestments['branch']->state_id)->where('applicable_date', '<=', $globaldate)->where('company_id', $companyId)->first();
            if (isset($getHeadSetting->gst_percentage) && $getGstSetting) {
                if ($memberInvestments['branch']->state_id == $getGstSettingno->state_id) {
                    $gstAmount = ceil(($amount * $getHeadSetting->gst_percentage) / 100) / 2;
                    $cgstHead = 171;
                    $sgstHead = 172;
                    $IntraState = true;
                } else {
                    $gstAmount = ceil($amount * $getHeadSetting->gst_percentage) / 100;
                    $cgstHead = 170;
                    $IntraState = false;
                }
                $msg = true;
            } else {
                $IntraState = false;
                $msg = false;
            }
            $data['investment_id'] = $memberInvestments->id;
            $data['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($memberInvestments->created_at)));
            // $satRef = TransactionReferences::create($data);
            $satRefId = NULL;
            $amountArraySsb = array('1' => (50));
            if ($isApp != null) {
                $created_by = 3;
            } else {
                $created_by = (Auth::user()->role_id == 3) ? 2 : 1;
            }
            if ($payment_mode == 3) {
                $day_book_payment_mode = 4;
            } else {
                $day_book_payment_mode = $payment_mode;
            }
            $ssbDetals = SavingAccount::where('member_investments_id', $investmentId)->first();
            // dd($investmentId);
            $createDayBook = CommanController::createDayBook($dayBookRef, $dayBookRef, 19, $memberInvestments->id, $associate_id, $memberInvestments->customer_id, 50, 50, $withdrawal = 0, $description, $memberInvestments->account_number, $memberInvestments->branch_id, getBranchCode($memberInvestments->branch_id)->branch_code, $amountArraySsb, $day_book_payment_mode, NULL, $memberInvestments->customer_id, $memberInvestments->account_number, 50, NULL, NULL, $created_at, NULL, NULL, NULL, 'CR', $companyId, $isApp);
            if ($isApp != NULL) {
                // $ssbDetals = SavingAccount::where('member_investments_id', $investmentId)->first();
                $sAccount_associate = $this->getMemberId($associate_id)->with([
                    'savingAccount' => function ($q) use ($companyId) {
                        $q->where('company_id', $companyId);
                    }
                ])->first();
                $s_array = [
                    'associatemid' => $associate_id,
                    'branchid' => $branch_id,
                    'amount' => 50,
                    'create_application_date' => $memberInvestments->created_at,
                    'is_app' => 1,
                    'type' => 1,
                    'description' => $description,
                    'company_id' => $companyId
                ];
                $savingTransaction = $this->savingAccountTransactionDataNew($s_array, null, $type, null, null, $sAccount_associate, $dayBookRef, $s = 3);
                $res = SavingAccountTranscation::create($savingTransaction);
                $ssbDetals = SavingAccount::find($sAccount_associate['savingAccount'][0]->id);
                $sData['balance'] = $ssbbal = (isset($sAccount_associate['savingAccount'][0])) ? $sAccount_associate['savingAccount'][0]->balance : '' - 50;
                $ssbDetals->update($sData);
                $planDetail = getPlanDetail($memberInvestments->plan_id, $companyId);
                // $head4Invest = $planDetail->deposit_head_id;
                $head4Invest = ($payment_mode == 3) ? $head4Invest = getplanheaddyanmic($sAccount_associate['savingAccount'][0]->id, $companyId) : $planDetail->deposit_head_id;
            }

            $allTranRDcheque = CommanTransactionFacade::headTransactionCreate($dayBookRef, $branch_id, $bank_id = NULL, $bank_ac_id = NULL, 122, $type, $sub_type, $memberInvestments->id, $associate_id, $member_id, $branch_id_to = NULL, $branch_id_from = NULL, $amount, $description, 'CR', $payment_mode, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $created_at, $createDayBook, NULL, $ssb_account_id_to = NULL, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, 0, $cheque_id = NULL, $companyId, $isApp);

            if ($payment_mode == 3) {

                $allTranRDcheque = CommanTransactionFacade::headTransactionCreate($dayBookRef, $branch_id, $bank_id = NULL, $bank_ac_id = NULL, $head4Invest ?? 28, 4, 43, $ssbDetals->id, $associate_id, $ssbDetals->member_id, $branch_id_to = NULL, $branch_id_from = NULL, $amount, $description, 'DR', $payment_mode, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $created_at, $createDayBook, NULL, $ssb_account_id_to = NULL, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, 0, $cheque_id = NULL, $companyId, $isApp);

            } else {

                $allTranRDcheque = CommanTransactionFacade::headTransactionCreate($dayBookRef, $branch_id, $bank_id = NULL, $bank_ac_id = NULL, $head4Invest ?? 28, $type, $sub_type, $type_id, $associate_id, $member_id, $branch_id_to = NULL, $branch_id_from = NULL, $amount, $description, 'DR', $payment_mode, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $created_at, $createDayBook, NULL, $ssb_account_id_to = NULL, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, 0, $cheque_id = NULL, $companyId, $isApp);
            }

            // $allTranRDcheque = CommanTransactionFacade::createBranchDayBookNew($dayBookRef, $branch_id, $type, $sub_type, $type_id, $associate_id, $member_id, $branch_id_to = NULL, $branch_id_from = NULL, $amount, $description, $description_dr, $description_cr, 'CR', $payment_mode, $currency_code, $v_no, $ssb_account_id_from, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $created_at, $createDayBook, $companyId);

            if ($payment_mode == 3 && $isApp != NULL) {

                $allTranRDcheque = CommanTransactionFacade::createBranchDayBookNew($dayBookRef, $branch_id, 4, 43, $ssbDetals->id, $associate_id, $ssbDetals->member_id, $branch_id_to = NULL, $branch_id_from = NULL, $amount, $description, $description_dr, $description_cr, 'DR', $payment_mode, $currency_code, $v_no, $ssb_account_id_from, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $created_at, $createDayBook, $companyId);

                $allTranRDcheque = CommanTransactionFacade::createBranchDayBookNew($dayBookRef, $branch_id, $type, $sub_type, $type_id, $associate_id, $member_id, $branch_id_to = NULL, $branch_id_from = NULL, $amount, $description, $description_dr, $description_cr, 'CR', $payment_mode, $currency_code, $v_no, $ssb_account_id_from, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $created_at, $createDayBook, $companyId);

            } else {

                $allTranRDcheque = CommanTransactionFacade::createBranchDayBookNew($dayBookRef, $branch_id, $type, $sub_type, $type_id, $associate_id, $member_id, $branch_id_to = NULL, $branch_id_from = NULL, $amount, $description, $description_dr, $description_cr, 'CR', $payment_mode, $currency_code, $v_no, $ssb_account_id_from, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $created_at, $createDayBook, $companyId);

            }
            if ($gstAmount > 0) {
                $createdGstTransaction = CommanController::gstTransaction($dayBookId = $dayBookRef, $getGstSettingno->gst_no, (!isset($memberInvestments['memberCompany']->member->gst_no)) ? NULL : $memberInvestments['memberCompany']->member->gst_no, 50, $getHeadSetting->gst_percentage, ($IntraState == false ? $gstAmount : 0), ($IntraState == true ? $gstAmount : 0), ($IntraState == true ? $gstAmount : 0), ($IntraState == true) ? $gstAmount + $gstAmount : $gstAmount, 122, $entry_date, 'IPC122', $memberInvestments->memberCompany->id, $branch_id, $companyId);
                if ($IntraState) {
                    $description = 'Stationary  Cgst Charge (' . $memberInvestments->account_number . ')';
                    $descriptionB = 'Stationary Sgst Charge (' . $memberInvestments->account_number . ')';
                    $amountArraySsb = array('1' => ($gstAmount));
                    $createDayBook = CommanController::createDayBook($dayBookRef, $satRefId, 26, $memberInvestments->id, $associate_id, $memberInvestments->customer_id, $gstAmount, $gstAmount, $withdrawal = 0, ($getHeadSetting->gst_percentage / 2) . 'CGST Charge on Stationary Charge', $memberInvestments->account_number, $memberInvestments->branch_id, getBranchCode($memberInvestments->branch_id)->branch_code, $amountArraySsb, $day_book_payment_mode, NULL, $memberInvestments->customer_id, $memberInvestments->account_number, 50, NULL, NULL, date("Y-m-d", strtotime(convertDate($memberInvestments->created_at))), NULL, NULL, NULL, 'CR', $companyId, $isApp);
                    $createDayBook = CommanController::createDayBook($dayBookRef, $satRefId, 27, $memberInvestments->id, $associate_id, $memberInvestments->customer_id, $gstAmount, $gstAmount, $withdrawal = 0, ($getHeadSetting->gst_percentage / 2) . 'SGST Charge on Stationary Charge', $memberInvestments->account_number, $memberInvestments->branch_id, getBranchCode($memberInvestments->branch_id)->branch_code, $amountArraySsb, $day_book_payment_mode, NULL, $memberInvestments->customer_id, $memberInvestments->account_number, 50, NULL, NULL, date("Y-m-d", strtotime(convertDate($memberInvestments->created_at))), NULL, NULL, NULL, 'CR', $companyId, $isApp);
                    if ($isApp != NULL) {
                        $sAccount_associate_c = $this->getMemberId($associate_id)->with([
                            'savingAccount' => function ($q) use ($companyId) {
                                $q->where('company_id', $companyId);
                            }
                        ])->first();
                        $s_array = [
                            'associatemid' => $associate_id,
                            'branchid' => $branch_id,
                            'amount' => $gstAmount,
                            'create_application_date' => $memberInvestments->created_at,
                            'is_app' => 1,
                            'type' => 1,
                            'description' => $description,
                            'company_id' => $companyId
                        ];
                        $sAccount_associate_c['savingAccount'][0]->balance = $ssbbal ?? $sAccount_associate_c['savingAccount'][0]->balance;
                        $savingTransaction = $this->savingAccountTransactionDataNew($s_array, null, $type, null, null, $sAccount_associate_c, $dayBookRef, $s = 4);
                        $res = SavingAccountTranscation::create($savingTransaction);
                        $ssbDetals = SavingAccount::find($sAccount_associate_c['savingAccount'][0]->id);
                        $sData['balance'] = $ssbbal = (isset($sAccount_associate_c['savingAccount'][0])) ? $sAccount_associate_c['savingAccount'][0]->balance : '' - $gstAmount;
                        $ssbDetals->update($sData);
                    }

                    $allTranRDcheque = CommanTransactionFacade::headTransactionCreate($dayBookRef, $branch_id, $bank_id = NULL, $bank_ac_id = NULL, $cgstHead, $type, $sub_type_cgst, $type_id, $associate_id, $member_id, $branch_id_to = NULL, $branch_id_from = NULL, $gstAmount, $description, 'CR', $payment_mode, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $created_at, $createdGstTransaction, NULL, $ssb_account_id_to = NULL, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, 0, $cheque_id = NULL, $companyId, $isApp);

                    $allTranRDcheque = CommanTransactionFacade::headTransactionCreate($dayBookRef, $branch_id, $bank_id = NULL, $bank_ac_id = NULL, $sgstHead, $type, $sub_type_sgst, $type_id, $associate_id, $member_id, $branch_id_to = NULL, $branch_id_from = NULL, $gstAmount, $descriptionB, 'CR', $payment_mode, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $created_at, $createdGstTransaction, NULL, $ssb_account_id_to = NULL, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, 0, $cheque_id = NULL, $companyId, $isApp);

                    $allTranRDcheque = CommanTransactionFacade::createBranchDayBookNew($dayBookRef, $branch_id, $type, $sub_type_cgst, $type_id, $associate_id, $member_id, $branch_id_to = NULL, $branch_id_from = NULL, $gstAmount, $description, $description, $description, 'CR', $payment_mode, $currency_code, $v_no, $ssb_account_id_from, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $created_at, $createdGstTransaction, $companyId);

                    $allTranRDcheque = CommanTransactionFacade::createBranchDayBookNew($dayBookRef, $branch_id, $type, $sub_type_sgst, $type_id, $associate_id, $member_id, $branch_id_to = NULL, $branch_id_from = NULL, $gstAmount, $descriptionB, $descriptionB, $descriptionB, 'CR', $payment_mode, $currency_code, $v_no, $ssb_account_id_from, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $created_at, $createdGstTransaction, $companyId);

                    /** DR entry for App in BranchDayBook and AllheadTransaction */

                    if ($payment_mode == 3 && $isApp != NULL) {

                        $allTranRDcheque = CommanTransactionFacade::createBranchDayBookNew($dayBookRef, $branch_id, 4, 43, $ssbDetals->id, $associate_id, $ssbDetals->id, $branch_id_to = NULL, $branch_id_from = NULL, $gstAmount, $description, $description, $description, 'DR', $payment_mode, $currency_code, $v_no, $ssb_account_id_from, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $created_at, $createdGstTransaction, $companyId);

                        $allTranRDcheque = CommanTransactionFacade::createBranchDayBookNew($dayBookRef, $branch_id, 4, 43, $ssbDetals->id, $associate_id, $ssbDetals->id, $branch_id_to = NULL, $branch_id_from = NULL, $gstAmount, $descriptionB, $descriptionB, $descriptionB, 'DR', $payment_mode, $currency_code, $v_no, $ssb_account_id_from, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $created_at, $createdGstTransaction, $companyId);

                        $allTranRDcheque = CommanTransactionFacade::headTransactionCreate($dayBookRef, $branch_id, $bank_id = NULL, $bank_ac_id = NULL, $head4Invest ?? 28, 4, 43, $ssbDetals->id, $associate_id, $ssbDetals->member_id, $branch_id_to = NULL, $branch_id_from = NULL, $gstAmount, $descriptionB, 'DR', $payment_mode, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $created_at, $createdGstTransaction, NULL, $ssb_account_id_to = NULL, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, 0, $cheque_id = NULL, $companyId, $isApp);

                        $allTranRDcheque = CommanTransactionFacade::headTransactionCreate($dayBookRef, $branch_id, $bank_id = NULL, $bank_ac_id = NULL, $head4Invest ?? 28, 4, 43, $ssbDetals->id, $associate_id, $ssbDetals->member_id, $branch_id_to = NULL, $branch_id_from = NULL, $gstAmount, $description, 'DR', $payment_mode, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $created_at, $createdGstTransaction, NULL, $ssb_account_id_to = NULL, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, 0, $cheque_id = NULL, $companyId, $isApp);

                    } else {

                        $allTranRDcheque = CommanTransactionFacade::headTransactionCreate($dayBookRef, $branch_id, $bank_id = NULL, $bank_ac_id = NULL, $head4Invest ?? 28, $type, $sub_type_sgst, $type_id, $associate_id, $member_id, $branch_id_to = NULL, $branch_id_from = NULL, $gstAmount, $descriptionB, 'DR', $payment_mode, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $created_at, $createdGstTransaction, NULL, $ssb_account_id_to = NULL, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, 0, $cheque_id = NULL, $companyId, $isApp);

                        $allTranRDcheque = CommanTransactionFacade::headTransactionCreate($dayBookRef, $branch_id, $bank_id = NULL, $bank_ac_id = NULL, $head4Invest ?? 28, $type, $sub_type_cgst, $type_id, $associate_id, $member_id, $branch_id_to = NULL, $branch_id_from = NULL, $gstAmount, $description, 'DR', $payment_mode, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $created_at, $createdGstTransaction, NULL, $ssb_account_id_to = NULL, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, 0, $cheque_id = NULL, $companyId, $isApp);

                    }
                    if ($isApp != NULL) {
                        $sAccount_associate_s = $this->getMemberId($associate_id)->with([
                            'savingAccount' => function ($q) use ($companyId) {
                                $q->where('company_id', $companyId);
                            }
                        ])->first();
                        $s_array = [
                            'associatemid' => $associate_id,
                            'branchid' => $branch_id,
                            'amount' => $gstAmount,
                            'create_application_date' => $memberInvestments->created_at,
                            'is_app' => 1,
                            'type' => 1,
                            'description' => $descriptionB,
                            'company_id' => $companyId
                        ];
                        $sAccount_associate_s['savingAccount'][0]->balance = $ssbbal ?? $sAccount_associate_s['savingAccount'][0]->balance;
                        $savingTransaction = $this->savingAccountTransactionDataNew($s_array, null, $type, null, null, $sAccount_associate_s, $dayBookRef, $s = 5);
                        $res = SavingAccountTranscation::create($savingTransaction);
                        $ssbDetals = SavingAccount::find($sAccount_associate_s['savingAccount'][0]->id);
                        $sData['balance'] = $ssbbal = (isset($sAccount_associate_s['savingAccount'][0])) ? $sAccount_associate_s['savingAccount'][0]->balance : '' - $gstAmount;
                        $ssbDetals->update($sData);
                    }
                    $rec = [
                        'cgst_stationary_chrg' => $gstAmount,
                        'sgst_stationary_chrg' => $gstAmount,
                        'invoice_id' => $createdGstTransaction,
                    ];
                } else {
                    $description = 'Stationary  Igst Charge (' . $memberInvestments->account_number . ')';
                    $amountArraySsb = array('1' => ($gstAmount));

                    $createDayBook = CommanController::createDayBook($dayBookRef, $satRefId, 28, $memberInvestments->id, NULL, $memberInvestments->customer_id, $gstAmount, $gstAmount, $withdrawal = 0, ($getHeadSetting->gst_percentage) . 'IGST Charge on Stationary Charge', $memberInvestments->account_number, $memberInvestments->branch_id, getBranchCode($memberInvestments->branch_id)->branch_code, $amountArraySsb, $day_book_payment_mode ?? 0, NULL, $memberInvestments->customer_id, $memberInvestments->account_number, $gstAmount, NULL, NULL, date("Y-m-d", strtotime(convertDate($memberInvestments->created_at))), NULL, NULL, NULL, 'CR', $companyId, $isApp);

                    $allTranRDcheque = CommanTransactionFacade::headTransactionCreate($dayBookRef, $branch_id, $bank_id = NULL, $bank_ac_id = NULL, $cgstHead, $type, $sub_type_igst, $type_id, $associate_id, $member_id, $branch_id_to = NULL, $branch_id_from = NULL, $gstAmount, $description, 'CR', $payment_mode, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $created_at, $createdGstTransaction, NULL, $ssb_account_id_to = NULL, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, 0, $cheque_id = NULL, $companyId, $isApp);

                    $allTranRDcheque = CommanTransactionFacade::createBranchDayBookNew($dayBookRef, $branch_id, $type, $sub_type_igst, $type_id, $associate_id, $member_id, $branch_id_to = NULL, $branch_id_from = NULL, $amount, $description, $description, $description, 'CR', $payment_mode, $currency_code, $v_no, $ssb_account_id_from, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $created_at, $createdGstTransaction, $companyId);

                    if ($payment_mode == 3) {

                        $allTranRDcheque = CommanTransactionFacade::createBranchDayBookNew($dayBookRef, $branch_id, 4, 43, $type_id, $associate_id, $ssbDetalsA->id, $branch_id_to = NULL, $branch_id_from = NULL, $amount, $description, $description, $description, 'DR', $payment_mode, $currency_code, $v_no, $ssb_account_id_from, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $created_at, $createdGstTransaction, $companyId);

                        $allTranRDcheque = CommanTransactionFacade::headTransactionCreate($dayBookRef, $branch_id, $bank_id = NULL, $bank_ac_id = NULL, $head4Invest ?? 28, 4, 43, $ssbDetals->id, $associate_id, $ssbDetals->member_id, $branch_id_to = NULL, $branch_id_from = NULL, $gstAmount, $description, 'DR', $payment_mode, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $created_at, $createdGstTransaction, NULL, $ssb_account_id_to = NULL, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, 0, $cheque_id = NULL, $companyId, $isApp);

                    } else {

                        $allTranRDcheque = CommanTransactionFacade::headTransactionCreate($dayBookRef, $branch_id, $bank_id = NULL, $bank_ac_id = NULL, $head4Invest ?? 28, $type, $sub_type_igst, $type_id, $associate_id, $member_id, $branch_id_to = NULL, $branch_id_from = NULL, $gstAmount, $description, 'DR', $payment_mode, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $created_at, $createdGstTransaction, NULL, $ssb_account_id_to = NULL, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, 0, $cheque_id = NULL, $companyId, $isApp);

                    }
                    if ($isApp != NULL) {
                        $sAccount_associate_i = $this->getMemberId($associate_id)->with([
                            'savingAccount' => function ($q) use ($companyId) {
                                $q->where('company_id', $companyId);
                            }
                        ])->first();
                        $s_array = [
                            'associatemid' => $associate_id,
                            'branchid' => $branch_id,
                            'amount' => $gstAmount,
                            'create_application_date' => $memberInvestments->created_at,
                            'is_app' => 1,
                            'type' => 1,
                            'description' => $description,
                            'company_id' => $companyId
                        ];
                        $sAccount_associate_i['savingAccount'][0]->balance = $ssbbal ?? $sAccount_associate_i['savingAccount'][0]->balance;
                        $savingTransaction = $this->savingAccountTransactionDataNew($s_array, null, $type, null, null, $sAccount_associate_i, $dayBookRef, $s = 7);
                        $res = SavingAccountTranscation::create($savingTransaction);
                        $ssbDetals = SavingAccount::find($sAccount_associate_i['savingAccount'][0]->id);
                        $sData['balance'] = (isset($sAccount_associate_i['savingAccount'][0])) ? $sAccount_associate_i['savingAccount'][0]->balance : '' - $gstAmount;
                        $ssbDetals->update($sData);
                    }
                    $rec = [
                        'igst_stationary_chrg' => $gstAmount,
                        'invoice_id' => $createdGstTransaction,
                    ];
                }
                $memberInvestments->update($rec);
            }
            $response = [
                'status' => true,
            ];
            DB::commit();
        } catch (\Exception $ex) {
            dd("dfs: " . $ex->getLine(), $ex->getMessage());
            $response = [
                'status' => true,
                'msg' => $ex->getMessage()
            ];
        }
        return $response;
    }
    /**
     * create savingAccountTransactionDataNew for the plan
     * @param $request, $investmentId, $type, $plan_name, $account_no, $sAccount
     *
     */
    public function savingAccountTransactionDataNew($request, $investmentId, $type, $plan_name, $account_no, $sAccount, $daybookRefRD = NULL, $second = null)
    {
        $sAccount = $sAccount;
        $data['associate_id'] = $request['associatemid'];
        $data['branch_id'] = $request['branchid'];
        $data['type'] = (isset($request['type'])) ? $request['type'] : 6;
        $data['saving_account_id'] = $sAccount['savingAccount'][0]->id;
        $data['account_no'] = $sAccount['savingAccount'][0]->account_no;
        $data['opening_balance'] = $sAccount['savingAccount'][0]->balance - $request['amount'];
        $data['withdrawal'] = $request['amount'];
        $data['description'] = (isset($request['description'])) ? $request['description'] : 'Payment transferred to ' . $plan_name . '(' . $account_no . ')';
        $data['currency_code'] = 'INR';
        $data['payment_type'] = 'DR';
        $data['payment_mode'] = 4;
        if (isset($request['is_app'])) {
            $data['is_app'] = 1;
            $data['app_login_user_id'] = $request['associatemid'];
        }

        $data['company_id'] = $request['company_id'];
        $data['daybook_ref_id'] = $daybookRefRD;
        $data['reference_no'] = '';
        $data['status'] = 1;
        if ($second == null) {
            $data['created_at'] = date("Y-m-d H:i:s", strtotime(str_replace('/', '-', $request['create_application_date'])));
        } else {
            // Assuming $request['create_application_date'] contains the original date
            $originalDate = $request['create_application_date'];

            // Create a Carbon instance from the original date
            $carbonDate = Carbon::parse(str_replace('/', '-', $request['create_application_date']));

            // Set the custom second value (e.g., 30)
            $customSecond = $second;
            $carbonDate->second($customSecond);

            // Format the Carbon instance as a string in the desired format
            $formattedDate = $carbonDate->format('Y-m-d H:i:s');

            // Assign the formatted date to $data['created_at']
            $data['created_at'] = $formattedDate;
        }
        return $data;
    }
    /**
     * create head entry  for the plan  except ssb
     *
     */
    public function investHeadCreate($amount, $globaldate, $investmentId, $planId, $cheque_no, $cheque_id, $cheque_date, $online_transction_no, $online_transction_date, $online_deposit_bank_id, $online_deposit_bank_ac_id, $branch_id, $associate_id, $memberId, $ssbId, $createDayBook, $payment_mode, $investmentAccountNoRd, $companyId, $daybookRefRD, $isApp = Null)
    {
        DB::beginTransaction();
        try {
            $amount = $amount;
            // $daybookRefRD = CommanController::createBranchDayBookReferenceNew($amount, $globaldate);
            $refIdRD = $daybookRefRD;
            $currency_code = 'INR';
            $headPaymentModeRD = 0;
            $payment_type_rd = 'CR';
            $type_id = $investmentId;
            $entry_date = date("Y-m-d", strtotime(convertDate($globaldate)));
            $entry_time = date("H:i:s");
            $created_by = $isApp != null ? 3 : (Auth::user()->role_id == 3 ? 2 : 1);
            $created_by_id = ($isApp != Null) ? $isApp : Auth::user()->id;
            $created_at = date("Y-m-d H:i:s", strtotime(convertDate($globaldate)));
            $updated_at = date("Y-m-d H:i:s", strtotime(convertDate($globaldate)));
            $planDetail = getPlanDetail($planId, $companyId);
            $type = 3;
            $sub_type = 31;
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
            $bank_id = NULL;
            $bank_ac_id = NULL;

            if ($payment_mode == 1) { // cheque moade
                $headPaymentModeRD = 1;
                $chequeDetail = \App\Models\ReceivedCheque::whereId($cheque_id)->first();
                $cheque_no = $chequeDetail->cheque_no;
                $cheque_date = $cheque_date;
                $cheque_bank_from = $chequeDetail->bank_name;
                $cheque_bank_ac_from = $chequeDetail->cheque_account_no;
                $cheque_bank_ifsc_from = NULL;
                $cheque_bank_branch_from = $chequeDetail->branch_name;
                $cheque_bank_to = $bank_id = $chequeDetail->deposit_bank_id;
                $cheque_bank_ac_to = $bank_ac_id = $chequeDetail->deposit_account_id;
                $getBankHead = SamraddhBank::whereId($cheque_bank_to)->first();
                $head41 = $getBankHead->account_head_id;
                $rdDesDR = 'Bank A/c Dr ' . $amount . '/-';
                $rdDesCR = 'To ' . $planDetail->name . '(' . $investmentAccountNoRd . ')  A/c Cr ' . $amount . '/-';
                $rdDes = 'Amount received for Account opening ' . $planDetail->name . '(' . $investmentAccountNoRd . ') through cheque(' . $cheque_no . ')';
                $rdDesMem = 'Account opening ' . $planDetail->name . '(' . $investmentAccountNoRd . ') through cheque(' . $cheque_no . ')';
                //bank head entry
                $allTranRDcheque = CommanTransactionFacade::headTransactionCreate($refIdRD, $branch_id, $cheque_bank_to, $cheque_bank_ac_to, $head41, $type, $sub_type, $type_id, $associate_id, $memberId, $branch_id_to = NULL, $branch_id_from = NULL, $amount, $rdDes, 'DR', $headPaymentModeRD, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $createDayBook, NULL, $ssb_account_id_to = NULL, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, 0, $cheque_id, $companyId, $isApp);
                //bank entry
                $bankCheque = CommanController::samraddhBankDaybookNew($refIdRD, $cheque_bank_to, $cheque_bank_ac_to, $type, $sub_type, $type_id, $createDayBook, $associate_id, $memberId, $branch_id, $amount, $amount, $closing_balance = NULL, $rdDes, $rdDesDR, $rdDesCR, 'CR', $headPaymentModeRD, $currency_code, $amount_to_id = NULL, $amount_to_name = NULL, $amount_from_id = NULL, $amount_from_name = NULL, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, getSamraddhBank($cheque_bank_to)->bank_name, getSamraddhBankAccountId($cheque_bank_ac_to)->account_no, $transction_bank_to_branch = NULL, getSamraddhBankAccountId($cheque_bank_ac_to)->ifsc_code, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $jv_unique_id = NULL, 0, $cheque_id, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $companyId);
                //bank balence
            } elseif ($payment_mode == 2) { //online transaction
                $headPaymentModeRD = 2;
                $transction_no = $online_transction_no;
                $transction_bank_from = NULL;
                $transction_bank_ac_from = NULL;
                $transction_bank_ifsc_from = NULL;
                $transction_bank_branch_from = NULL;
                $transction_bank_to = $bank_id = $online_deposit_bank_id;
                $transction_bank_ac_to = $bank_ac_id = $online_deposit_bank_ac_id;
                $transction_date = $online_transction_date;
                $getBHead = SamraddhBank::whereId($transction_bank_to)->first();
                $head411 = $getBHead->account_head_id;
                $rdDesDR = 'Bank A/c Dr ' . $amount . '/-';
                $rdDesCR = 'To ' . $planDetail->name . '(' . $investmentAccountNoRd . ')  A/c Cr ' . $amount . '/-';
                $rdDes = 'Amount received for Account opening ' . $planDetail->name . '(' . $investmentAccountNoRd . ') through online transaction(' . $transction_no . ')';
                $rdDesMem = 'Account opening ' . $planDetail->name . '(' . $investmentAccountNoRd . ') through online transaction(' . $transction_no . ')';
                //bank head entry
                $allTranRDcheque = CommanTransactionFacade::headTransactionCreate($refIdRD, $branch_id, $transction_bank_to, $transction_bank_ac_to, $head411, $type, $sub_type, $type_id, $associate_id, $memberId, $branch_id_to = NULL, $branch_id_from = NULL, $amount, $rdDes, 'DR', $headPaymentModeRD, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $createDayBook, NULL, $ssb_account_id_to = NULL, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, 0, $cheque_id, $companyId, $isApp);
                //bank entry
                $bankonline = CommanController::samraddhBankDaybookNew($refIdRD, $transction_bank_to, $transction_bank_ac_to, $type, $sub_type, $type_id, $createDayBook, $associate_id, $memberId, $branch_id, $opening_balance = NULL, $amount, $closing_balance = NULL, $rdDes, $rdDesDR, $rdDesCR, 'CR', $headPaymentModeRD, $currency_code, $amount_to_id = NULL, $amount_to_name = NULL, $amount_from_id = NULL, $amount_from_name = NULL, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, getSamraddhBank($online_deposit_bank_id)->bank_name, getSamraddhBankAccountId($online_deposit_bank_ac_id)->account_no, $transction_bank_to_branch = NULL, getSamraddhBankAccountId($online_deposit_bank_ac_id)->ifsc_code, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $jv_unique_id = NULL, NULL, $cheque_id = NULL, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $companyId);
                //bank balence
            } elseif ($payment_mode == 3) { // ssb
                $headPaymentModeRD = 3;
                $v_no = mt_rand(0, 999999999999999);
                $v_date = $entry_date;
                $ssb_account_id_from = $ssbId;
                $SSBDescTran = 'Amount transferred to ' . $planDetail->name . '(' . $investmentAccountNoRd . ') ';
                $head1rdSSB = 1;
                $head2rdSSB = 8;
                $head3rdSSB = 20;
                $head4rdSSB = ($isApp != null) ? getplanheaddyanmic($ssbId, $companyId) : 56;
                $head5rdSSB = NULL;
                $ssbDetals = SavingAccount::whereId($ssb_account_id_from)->first();
                $rdDesDR = $planDetail->name . '(' . $investmentAccountNoRd . ') A/c Dr ' . $amount . '/-';
                $rdDesCR = 'To SSB(' . $ssbDetals->account_no . ') A/c Cr ' . $amount . '/-';
                $rdDes = 'Amount received for Account opening ' . $planDetail->name . '(' . $investmentAccountNoRd . ') through SSB(' . $ssbDetals->account_no . ')';
                $rdDesMem = 'Account opening ' . $planDetail->name . '(' . $investmentAccountNoRd . ') online through SSB(' . $ssbDetals->account_no . ')';
                // ssb  head entry -
                $ssbaccountdetails = SavingAccount::whereId($ssb_account_id_from)->first()->member_id;

                // $allTranRDcheque = CommanTransactionFacade::headTransactionCreate($refIdRD, $branch_id, $cheque_bank_to, $cheque_bank_ac_to, $head4rdSSB, 4, 43, $type_id = $ssb_account_id_from, $associate_id, $ssbaccountdetails, $branch_id_to = NULL, $branch_id_from = NULL, $amount, $rdDes, 'DR', $headPaymentModeRD, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $createDayBook, NULL, $ssb_account_id_to = NULL, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, 0, $cheque_id, $companyId, $isApp);

                if ($isApp != null) {
                    $typeid = $ssb_account_id_from;

                } else {
                    $ssbIdCustoomer = SavingAccount::where('member_id', $memberId)->first('id');
                    $typeid = $ssbIdCustoomer->id;
                }
                $allTranRDcheque = CommanTransactionFacade::headTransactionCreate($refIdRD, $branch_id, $cheque_bank_to, $cheque_bank_ac_to, $head4rdSSB, 4, 43, $type_id = $typeid, $associate_id, $memberId, $branch_id_to = NULL, $branch_id_from = NULL, $amount, $rdDes, 'DR', $headPaymentModeRD, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $createDayBook, NULL, $ssb_account_id_to = NULL, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, 0, $cheque_id, $companyId, $isApp);

            } else {
                $headPaymentModeRD = 0;
                // $head3rdC = 28;
                $head3rdC = ($isApp != null) ? getplanheaddyanmic($ssbId, $companyId) : 28;
                $rdDesDR = 'Cash A/c Dr ' . $amount . '/-';
                $rdDesCR = 'To ' . $planDetail->name . '(' . $investmentAccountNoRd . ')  A/c Cr ' . $amount . '/-';
                $rdDes = 'Amount received for Account opening ' . $planDetail->name . '(' . $investmentAccountNoRd . ') through cash(' . getBranchCode($branch_id)->branch_code . ')';
                $rdDesMem = 'Account opening ' . $planDetail->name . '(' . $investmentAccountNoRd . ') through cash(' . getBranchCode($branch_id)->branch_code . ')';
                // branch cash  head entry +

                $allTranRDcheque = CommanTransactionFacade::headTransactionCreate($refIdRD, $branch_id, $bank_id = NULL, $bank_ac_id = NULL, $head3rdC, $type, $sub_type, $type_id, $associate_id, $memberId, $branch_id_to = NULL, $branch_id_from = NULL, $amount, $rdDes, 'DR', $headPaymentModeRD, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $createDayBook, NULL, $ssb_account_id_to = NULL, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, 0, $cheque_id, $companyId, $isApp);

            }
            //branch day book entry +

            $allTranRDcheque = CommanTransactionFacade::createBranchDayBookNew($refIdRD, $branch_id, $type, $sub_type, $type_id, $associate_id, $memberId, $branch_id_to = NULL, $branch_id_from = NULL, $amount, $rdDes, $rdDesDR, $rdDesCR, 'CR', $headPaymentModeRD, $currency_code, $v_no, $ssb_account_id_from, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $createDayBook, $companyId, $cheque_id, $cheque_no);

            $allTranRDcheque = CommanTransactionFacade::headTransactionCreate($refIdRD, $branch_id, $bank_id, $bank_ac_id, $head_id, $type, $sub_type, $type_id, $associate_id, $memberId, $branch_id_to = NULL, $branch_id_from = NULL, $amount, $rdDes, $payment_type_rd, $headPaymentModeRD, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $createDayBook, NULL, $ssb_account_id_to = NULL, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, 0, $cheque_id, $companyId, $isApp);

            DB::commit();
        } catch (\Exception $ex) {

            dd("ghgh: " . $ex->getLine());
            $response = [
                'status' => true,
                'msg' => $ex->getMessage(),
            ];
        }
        return true;
    }
    /**
     * create head entry  for the plan   ssb
     *
     */
    public function investHeadCreateSSB($amount, $globaldate, $investmentId, $planId, $cheque_no, $cheque_id, $cheque_date, $online_transction_no, $online_transction_date, $online_deposit_bank_id, $online_deposit_bank_ac_id, $branch_id, $associate_id, $memberId, $ssbId, $createDayBook, $payment_mode, $investmentAccountNoRd, $companyId, $daybookRefRD, $isApp = Null)
    {
        DB::beginTransaction();
        try {
            $amount = $amount;
            $refIdRD = $daybookRefRD;
            $payment_type_rd = 'CR';
            $type_id = $investmentId;
            $entry_date = date("Y-m-d", strtotime(convertDate($globaldate)));
            $entry_time = date("H:i:s");
            $role_id = auth()->user() ? (auth()->user()->role_id == 3 ? 2 : 1) : Member::whereId($associate_id)->value('role_id');
            $created_by = $isApp != null ? 3 : $role_id;
            $auth_created_by_id = Auth::user() ? Auth::user()->id : $associate_id;
            $created_by_id = ($isApp != Null) ? $associate_id : $auth_created_by_id;
            $created_at = date("Y-m-d H:i:s", strtotime(convertDate($globaldate)));
            $updated_at = date("Y-m-d H:i:s", strtotime(convertDate($globaldate)));
            $planDetail = getPlanDetail($planId, $companyId);
            $type = 4;
            $sub_type = 41;
            $planCode = $planDetail->plan_code;
            $head4Invest = $planDetail->deposit_head_id;
            $v_no = NULL;
            $ssb_account_id_from = NULL;
            $cheque_no = NULL;
            $transction_no = NULL;
            $head3rdC = 28;
            $currency_code = 'INR';

            $ssbaccountdetails = \App\Models\SavingAccount::whereId($ssbId)->exists() ? \App\Models\SavingAccount::whereId($ssbId)->first()->member_id : \App\Models\SavingAccount::where('account_no', $ssbId)->first()->member_id;

            if ($isApp != null) {
                $headPaymentModeRD = 3;
                $rdDesDR = 'SSB A/c Dr ' . $amount . '/-';
                $rdDesCR = 'To ' . $planDetail->name . '(' . $investmentAccountNoRd . ')  A/c Cr ' . $amount . '/-';
                $rdDes = 'Amount received for Account opening ' . $planDetail->name . '(' . $investmentAccountNoRd . ') through SSB(' . $ssbId . ')';
                $ssb_account_id_from = SavingAccount::whereAccount_no($ssbId)->value('id');
                $ssb_account_id_to = SavingAccount::whereAccount_no($investmentAccountNoRd)->value('id');
                // $head3rdC = $planDetail->interest_head_id;
                $head3rdC = $headPaymentModeRD == 3 ? getplanheaddyanmic($ssbId, $companyId) : $planDetail->interest_head_id;

                $allTranRDcheque = CommanTransactionFacade::headTransactionCreate($refIdRD, $branch_id, $bank_id = NULL, $bank_ac_id = NULL, $head3rdC, 4, 43, $type_id, $associate_id, $ssbaccountdetails, $branch_id_to = NULL, $branch_id_from = NULL, $amount, $rdDes, 'DR', $headPaymentModeRD, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $createDayBook, NULL, $ssb_account_id_to, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, 0, $cheque_id, $companyId, $isApp);

                $allTranRDcheque = CommanTransactionFacade::createBranchDayBookNew($refIdRD, $branch_id, $type, $sub_type, $type_id, $associate_id, $memberId, $branch_id_to = NULL, $branch_id_from = NULL, $amount, $rdDes, $rdDesDR, $rdDesCR, 'CR', $headPaymentModeRD, $currency_code, $v_no, $ssb_account_id_from, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $createDayBook, $companyId);


            } else {
                $headPaymentModeRD = 0;
                $rdDesDR = 'Cash A/c Dr ' . $amount . '/-';
                $rdDesCR = 'To ' . $planDetail->name . '(' . $investmentAccountNoRd . ')  A/c Cr ' . $amount . '/-';
                $rdDes = 'Amount received for Account opening ' . $planDetail->name . '(' . $investmentAccountNoRd . ') through cash(' . getBranchCode($branch_id)->branch_code . ')';
                $ssb_account_id_to = NULL;

                $allTranRDcheque = CommanTransactionFacade::headTransactionCreate($refIdRD, $branch_id, $bank_id = NULL, $bank_ac_id = NULL, $head3rdC, 4, 43, $type_id, $associate_id, $ssbaccountdetails, $branch_id_to = NULL, $branch_id_from = NULL, $amount, $rdDes, 'DR', $headPaymentModeRD, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $createDayBook, NULL, $ssb_account_id_to, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, 0, $cheque_id, $companyId, $isApp);

                $type_id = $investmentId;

                $allTranRDcheque = CommanTransactionFacade::createBranchDayBookNew($refIdRD, $branch_id, $type, $sub_type, $type_id, $associate_id, $memberId, $branch_id_to = NULL, $branch_id_from = NULL, $amount, $rdDes, $rdDesDR, $rdDesCR, 'CR', $headPaymentModeRD, $currency_code, $v_no, $ssb_account_id_from, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $createDayBook, $companyId);


            }
            // branch cash  head entry +

            $allTranRDcheque = CommanTransactionFacade::headTransactionCreate($refIdRD, $branch_id, $bank_id = NULL, $bank_ac_id = NULL, $head4Invest, $type, $sub_type, $type_id, $associate_id, $memberId, $branch_id_to = NULL, $branch_id_from = NULL, $amount, $rdDes, 'CR', $headPaymentModeRD, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $createDayBook, NULL, $ssb_account_id_to, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, 0, $cheque_id, $companyId, $isApp);
            DB::commit();
        } catch (\Exception $ex) {

            dd("rbjghjhjh: " . $ex->getMessage(), $ex->getLine());
            $response = [
                'status' => true,
                'msg' => $ex->getMessage(),
            ];
        }
        return true;
    }
    /**
     * Get investment plans first nominee data to store.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getFirstNomineeData($request, $investmentId, $type)
    {
        $data = [
            'investment_id' => $investmentId,
            'nominee_type' => 0,
            'name' => $request['fn_first_name'],
            //'second_name' => $request['fn_second_name'],
            'relation' => $request['fn_relationship'],
            'gender' => $request['fn_gender'],
            'dob' => date("Y-m-d", strtotime(str_replace('/', '-', $request['fn_dob']))),
            'age' => $request['fn_age'],
            'percentage' => $request['fn_percentage'],
            'is_minor' => (isset($request['fn_as_minor']) ? $request['fn_as_minor'] : '0'),
            'parent_name' => (isset($request['fn_parent_nominee_name']) ? $request['fn_parent_nominee_name'] : ''),
            'parent_no' => (isset($request['fn_parent_nominee_mobile_no']) ? $request['fn_parent_nominee_mobile_no'] : '0'),
            //'phone_number' => $request['fn_mobile_number'],
        ];
        return $data;
    }

    /**
     * Get investment plans second nominee data to store.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getSecondNomineeData($request, $investmentId, $type)
    {
        $data = [
            'investment_id' => $investmentId,
            'nominee_type' => 1,
            'name' => $request['sn_first_name'],
            //'second_name' => $request['sn_second_name'],
            'relation' => $request['sn_relationship'],
            'gender' => $request['sn_gender'],
            'dob' => date("Y-m-d", strtotime(str_replace('/', '-', $request['sn_dob']))),
            'age' => $request['sn_age'],
            'percentage' => $request['sn_percentage'],
            'is_minor' => (isset($request['sn_as_minor']) ? $request['sn_as_minor'] : '0'),
            'parent_name' => null, // $request['sn_parent_nominee_name'],
            'parent_no' => 0, // $request['sn_parent_nominee_mobile_no'],
            //'phone_number' => $request['sn_mobile_number'],
        ];
        return $data;
    }

    /**
     * Get investment plans payment method data to store.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getPaymentMethodData($request, $investmentId, $type)
    {

        switch ($request['payment-mode']) {
            case "1":
                $data['cheque_number'] = $request['cheque-number'];
                $data['bank_name'] = $request['bank-name'];
                $data['branch_name'] = $request['branch-name'];
                $data['cheque_date'] = date("Y-m-d", strtotime(str_replace('/', '-', $request['cheque-date'])));
                break;
            default:
                $data['transaction_id'] = $request['transaction-id'];
                $data['transaction_date'] = date("Y-m-d", strtotime(str_replace('/', '-', $request['date'])));
        }
        $data['investment_id'] = $investmentId;

        return $data;
    }







    public function registerSSbRequiredData($request, $memberId, $transtype = NULL)
    {

        DB::beginTransaction();
        try {
            $ssbAmount = \App\Models\SsbAccountSetting::where('user_type', 1)->where('plan_type', 1)->whereStatus(1)->first();
            $checkSSbExistinAllCompany = SavingAccount::where('customer_id', $request['memberAutoId'])->count();
            $memberInvestment = Memberinvestments::where('customer_id', $request['memberAutoId'])->count();
            $branch_id = $request['branchid'];
            if (isset($request->is_app) && $request->is_app) {
                $branch_id = Member::whereId($request->input('associatemid'))->value('branch_id');
            }
            $getBranchCode = getBranchCode($branch_id);

            $branchCode = $getBranchCode->branch_code;
            $planDetails = \App\Models\Plans::select('id', 'plan_category_code', 'short_name')->where('plan_category_code', 'S')->whereCompanyId($request['company_id'])->whereStatus(1)->first();
            $faCode = getPlanCode($planDetails->id);

            $planId = $planDetails->id;
            $investmentMiCode = getInvesmentMiCodeNew($planId, $branch_id);

            $codes = generateCode($request, 'create', config('constants.PASSBOOK'), 5, $request['company_id']);

            if (!empty($investmentMiCode)) {
                $miCodeAdd = $investmentMiCode->mi_code + 1;
            } else {
                $miCodeAdd = 1;
            }

            $miCode = str_pad($miCodeAdd, 5, '0', STR_PAD_LEFT);
            $passbook = $codes['passbookCode'] . $branchCode . $faCode . $miCode;
            // Invesment Account no
            $investmentAccount = $branchCode . $faCode . $miCode;

            $globaldate = $pdate = isset($request['create_application_date']) ? date("Y-m-d H:i:s", strtotime(str_replace('/', '-', $request['create_application_date']))) : date("Y-m-d H:i:s", strtotime(str_replace('/', '-', $request['created_at'])));
            Session::put('created_at', $globaldate);
            $ssbAmountTrans = 0;

            if ($transtype == 'Loan') {
                if ($checkSSbExistinAllCompany == 0) {
                    $ssbAmountTrans = $ssbAmount->amount;
                }
            }
            $is_primary = 0;
            $ssbdata['mi_code'] = $miCode;
            $ssbdata['account_number'] = $investmentAccount;
            $ssbdata['ssb_account_number'] = $investmentAccount;
            $ssbdata['plan_id'] = $planId;
            // if($request['f_number'] == null){
            //     pd('stop');
            // }
            $ssbdata['form_number'] = $request['fnumber'] ?? 0;
            $ssbdata['member_id'] = $memberId;
            $ssbdata['customer_id'] = $request['memberAutoId'];
            $ssbdata['associate_id'] = $request['associatemid'];
            $ssbdata['branch_id'] = $branch_id;
            $ssbdata['old_branch_id'] = $branch_id;
            $ssbdata['deposite_amount'] = $ssbAmountTrans;
            $ssbdata['current_balance'] = $ssbAmountTrans;
            $ssbdata['created_at'] = $globaldate;
            $ssbdata['company_id'] = $request['company_id'];
            if (isset($request->is_app) && $request->is_app) {
                $ssbdata['is_app'] = 1;
                $ssbdata['payment_mode'] = 3;
                $ssbdata['app_login_user_id'] = $request['associatemid'];
                $createSavingAccountDescriptionModify = 3;
                $is_app = 1;
            } else {
                $createSavingAccountDescriptionModify = 1;
                $is_app = null;
            }

            $daybookRefRDrr = CommanController::createBranchDayBookReferenceNew($ssbAmountTrans, $globaldate);

            $res = Memberinvestments::create($ssbdata);
            $investmentId = $res->id;
            $savingAccountId = $res->account_number;

            $description = $planDetails->short_name . ' Account Opening';
            $companyId = $request['company_id'];


            $createAccount = CommanController::createSavingAccountDescriptionModify($memberId, $branch_id, $branchCode, $ssbAmountTrans, 0, $res->id, $miCode, $investmentAccount, $is_primary, $faCode, $description, $request['associatemid'], 0, $daybookRefRDrr, $request['company_id'], $request['memberAutoId'], $passbook, $createSavingAccountDescriptionModify);




            $createDayBook = $createAccount['ssb_transaction_id'] ?? null;
            $mRes = MemberCompany::find($memberId);
            $mData['ssb_account'] = $investmentAccount;
            $mRes->update($mData);
            $satRefId = NULL;
            $ssbAccountId = $createAccount['ssb_id'];
            $ssbAmountNew = $ssbAmountTrans;
            $amountArraySsb = array('1' => $ssbAmountNew);
            $amount_deposit_by_name = NULL;
            $ssbCreateTran = NULL;
            $sAccount = $createAccount;
            if (count($sAccount) > 0) {
                $ssbAccountNumber = $investmentAccount;
                $ssbId = $sAccount['ssb_id'];
            } else {
                $ssbAccountNumber = '';
                $ssbId = '';
            }
            if (isset($request->is_app) && $request->is_app > 0) {
                $is_app = 1;
                $createDayBookNew_payment_mode = 4;
            } else {
                $is_app = null;
                $createDayBookNew_payment_mode = 0;
            }


            // $createDayBook = CommanController::createDayBookNew($daybookRefRDrr, $daybookRefRDrr, 2, $ssbId, $request['associatemid'], $memberId, $ssbAmountTrans, $ssbAmountTrans, $withdrawal = 0, $description, $ssbAccountNumber, $branch_id, $branchCode, $amountArraySsb, $createDayBookNew_payment_mode, $amount_deposit_by_name, $memberId, $investmentAccount, NULL, NULL, NULL, $globaldate, NULL, NULL, $ssbId, 'CR', NULL, NULL, NULL, NULL, NULL, $companyId, $is_app);
            // ---------------------------  HEAD IMPLEMENT --------------------------
            if ($checkSSbExistinAllCompany == 0) {
                $this->investHeadCreateSSB($ssbAmountTrans, $globaldate, $ssbAccountId, $planId, NULL, NULL, NULL, NULL, NULL, NULL, NULL, $branch_id, $request['associatemid'], $memberId, $ssbId, $createDayBook, $request['payment-mode'], $investmentAccount, $companyId, $daybookRefRDrr, $is_app);
            }

            //--------------------------------HEAD IMPLEMENT  -------------------------
            $type = 'create';

            if (isset($request['fn_first_name'])) {
                $transaction = $this->transactionData($satRefId, $request->all(), $investmentId, $type, $ssbCreateTran);
                $res = Investmentplantransactions::create($transaction);
                $ssbfndata['investment_id'] = $investmentId;
                $ssbfndata['nominee_type'] = 0;
                $ssbfndata['name'] = $request['fn_first_name'];
                //$ssbfndata['second_name'] = $request['ssb_fn_second_name'];
                $ssbfndata['relation'] = $request['fn_relationship'];
                $ssbfndata['gender'] = $request['fn_gender'];
                $ssbfndata['dob'] = date("Y-m-d", strtotime(str_replace('/', '-', $request['fn_dob'])));
                $ssbfndata['age'] = $request['fn_age'];
                $ssbfndata['percentage'] = $request['fn_percentage'];
                $res = Memberinvestmentsnominees::create($ssbfndata);
                if ($request['sa_second_nominee_add'] == 1) {
                    $ssbsndata['investment_id'] = $investmentId;
                    $ssbsndata['nominee_type'] = 1;
                    $ssbsndata['name'] = $request['sn_first_name'];
                    //$ssbsndata['second_name'] = $request['ssb_sn_second_name'];
                    $ssbsndata['relation'] = $request['sn_relationship'];
                    $ssbsndata['gender'] = $request['sn_gender'];
                    $ssbsndata['dob'] = date("Y-m-d", strtotime(str_replace('/', '-', $request['sn_dob'])));
                    $ssbsndata['age'] = $request['sn_age'];
                    $ssbsndata['percentage'] = $request['sn_percentage'];
                    $msNominee = Memberinvestmentsnominees::create($ssbsndata);
                }
            }

            if ($transtype == 'Loan') {
                $memberInvestment = Memberinvestments::where('customer_id', $request['memberAutoId'])->count();

                if ($memberInvestment > 1 && $ssbAmountTrans > 0) {
                    $this->loanStationaryCharges((object) $request, $investmentId, $memberId, $investmentAccount);
                }
            }

            $savingAccountDetail = SavingAccount::where('account_no', $savingAccountId)->first();



            $response = [
                'status' => true,
                'data' => $savingAccountDetail,
                'is_stationary' => ($memberInvestment > 1 && $ssbAmountTrans > 0) ? true : false
            ];

            DB::commit();
        } catch (\Exception $ex) {

            DB::rollback();

            dd("dgfffffffffff: " . $ex->getLine());
            $response = [
                'status' => false,
                'msg' => $ex->getMessage(),
                'line' => $ex->getLine(),

            ];
        }
        // dd($response,$ssbdata,$res);
        return $response;
    }



    public function loanStationaryCharges($request, $investmentId, $memberId, $account_no)
    {

        DB::beginTransaction();
        try {

            $memberInvestments = Memberinvestments::with(['branch', 'member'])->whereId($investmentId)->first();
            $entryTime = date("H:i:s");
            Session::put('created_at', date("Y-m-d " . $entryTime . "", strtotime(convertDate($request->created_at))));

            $companyId = $request->company_id;

            $vno = NULL;
            $isApp = NULL;
            $branch_id = $request->branchid;
            $type = 5;
            $sub_type = 35;
            $type_id = $investmentId;
            $associate_id = $request->associatemid;
            $member_id = $memberId;
            $amount = 50;
            $description = 'Stationary charges on investment plan registration ';
            $description_dr = 'Stationary charges on investment plan registration ' . $amount;
            $description_cr = 'Stationary charges on investment plan registration ' . $amount;
            $payment_mode = 0;
            $currency_code = 'INR';
            $sub_type_cgst = 321;
            $sub_type_sgst = 322;
            $sub_type_igst = 323;
            $v_no = $vno;
            $ssb_account_id_from = NULL;
            $cheque_no = NULL;
            $transction_no = NULL;
            $entry_date = date('Y-m-d', strtotime(convertDate($request->created_at)));
            $entry_time = date("H:i:s");
            $created_by = (Auth::user()->role_id == 3) ? 2 : 1;
            $created_by_id = Auth::user()->id;
            $created_at = date('Y-m-d H:i:s', strtotime($request->created_at));
            $dayBookRef = CommanController::createBranchDayBookReference($amount);
            $detail = getBranchDetail($request->branchid)->state_id;
            $globaldate = checkMonthAvailability(date('d'), date('m'), date('Y'), $detail);
            $getHeadSetting = HeadSetting::where('head_id', 122)->first();
            $getGstSetting = GstSetting::where('state_id', $detail)->where('applicable_date', '<=', $globaldate)->where('company_id', $companyId)->exists();
            $gstAmount = 0;
            $getGstSettingno = GstSetting::select('id', 'gst_no', 'state_id')->where('state_id', $detail)->where('applicable_date', '<=', $globaldate)->where('company_id', $companyId)->first();
            if (isset($getHeadSetting->gst_percentage) && $getGstSetting) {
                if ($detail == $getGstSettingno->state_id) {
                    $gstAmount = ceil(($amount * $getHeadSetting->gst_percentage) / 100) / 2;
                    $cgstHead = 171;
                    $sgstHead = 172;
                    $IntraState = true;
                } else {
                    $gstAmount = ceil($amount * $getHeadSetting->gst_percentage) / 100;
                    $cgstHead = 170;
                    $IntraState = false;
                }
                $msg = true;
            } else {
                $IntraState = false;
                $msg = false;
            }


            $data['investment_id'] = $investmentId;
            $data['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($request->created_at)));
            //   $satRef = TransactionReferences::create($data);
            $satRefId = NULL;
            $amountArraySsb = array('1' => (50));
            $createDayBook = CommanController::createDayBook($dayBookRef, $dayBookRef, 19, $investmentId, $associate_id, $member_id, 50, 50, $withdrawal = 0, 'Stationary charges on investment plan registration', $account_no, $request->branchid, getBranchCode($request->branchid)->branch_code, $amountArraySsb, 0, NULL, $request->memberAutoId, $account_no, 50, NULL, NULL, $created_at, NULL, NULL, NULL, 'CR', $companyId, $isApp);


            $allTranRDcheque = CommanTransactionFacade::headTransactionCreate($dayBookRef, $branch_id, $bank_id = NULL, $bank_ac_id = NULL, 122, $type, $sub_type, $type_id, $associate_id, $member_id, $branch_id_to = NULL, $branch_id_from = NULL, $amount, $description, 'CR', $payment_mode, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $created_at, $createDayBook, NULL, $ssb_account_id_to = NULL, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, 0, $cheque_id = NULL, $companyId);

            $allTranRDcheque = CommanTransactionFacade::headTransactionCreate($dayBookRef, $branch_id, $bank_id = NULL, $bank_ac_id = NULL, 28, $type, $sub_type, $type_id, $associate_id, $member_id, $branch_id_to = NULL, $branch_id_from = NULL, $amount, $description, 'DR', $payment_mode, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $created_at, $createDayBook, NULL, $ssb_account_id_to = NULL, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, 0, $cheque_id = NULL, $companyId);


            //  dd($memberInvestments);


            $allTranRDcheque = CommanTransactionFacade::createBranchDayBookNew($dayBookRef, $branch_id, $type, $sub_type, $type_id, $associate_id, $member_id, $branch_id_to = NULL, $branch_id_from = NULL, $amount, $description, $description_dr, $description_cr, 'CR', $payment_mode, $currency_code, $v_no, $ssb_account_id_from, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $created_at, $createDayBook, $companyId);

            if ($gstAmount > 0) {
                $createdGstTransaction = CommanController::gstTransaction($dayBookId = $dayBookRef, $getGstSettingno->gst_no, (!isset($memberInvestments['memberCompany']->member->gst_no)) ? NULL : $memberInvestments['memberCompany']->member->gst_no, 50, $getHeadSetting->gst_percentage, ($IntraState == false ? $gstAmount : 0), ($IntraState == true ? $gstAmount : 0), ($IntraState == true ? $gstAmount : 0), ($IntraState == true) ? $gstAmount + $gstAmount : $gstAmount, 122, $entry_date, 'IPC122', $memberInvestments->memberCompany->id, $branch_id, $companyId);




                if ($IntraState) {
                    $description = 'Stationary  Cgst Charge (' . $memberInvestments->account_number . ')';
                    $descriptionB = 'Stationary Sgst Charge (' . $memberInvestments->account_number . ')';
                    $amountArraySsb = array('1' => ($gstAmount));
                    $createDayBook = CommanController::createDayBook(NULL, $satRefId, 26, $memberInvestments->id, $associate_id, $memberInvestments->customer_id, $gstAmount, $gstAmount, $withdrawal = 0, ($getHeadSetting->gst_percentage / 2) . 'CGST Charge on Stationary Charge', $memberInvestments->account_number, $memberInvestments->branch_id, getBranchCode($memberInvestments->branch_id)->branch_code, $amountArraySsb, 0, NULL, $memberInvestments->customer_id, $memberInvestments->account_number, 50, NULL, NULL, date("Y-m-d", strtotime(convertDate($memberInvestments->created_at))), NULL, NULL, NULL, 'CR', $companyId, $isApp);

                    $createDayBook = CommanController::createDayBook(NULL, $satRefId, 27, $memberInvestments->id, $associate_id, $memberInvestments->customer_id, $gstAmount, $gstAmount, $withdrawal = 0, ($getHeadSetting->gst_percentage / 2) . 'SGST Charge on Stationary Charge', $memberInvestments->account_number, $memberInvestments->branch_id, getBranchCode($memberInvestments->branch_id)->branch_code, $amountArraySsb, 0, NULL, $memberInvestments->customer_id, $memberInvestments->account_number, 50, NULL, NULL, date("Y-m-d", strtotime(convertDate($memberInvestments->created_at))), NULL, NULL, NULL, 'CR', $companyId, $isApp);

                    $allTranRDcheque = CommanTransactionFacade::headTransactionCreate($dayBookRef, $branch_id, $bank_id = NULL, $bank_ac_id = NULL, $cgstHead, $type, $sub_type_cgst, $type_id, $associate_id, $member_id, $branch_id_to = NULL, $branch_id_from = NULL, $gstAmount, $description, 'CR', $payment_mode, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $created_at, $createdGstTransaction, NULL, $ssb_account_id_to = NULL, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, 0, $cheque_id = NULL, $companyId, $isApp);

                    $allTranRDcheque = CommanTransactionFacade::headTransactionCreate($dayBookRef, $branch_id, $bank_id = NULL, $bank_ac_id = NULL, 28, $type, $sub_type_cgst, $type_id, $associate_id, $member_id, $branch_id_to = NULL, $branch_id_from = NULL, $gstAmount, $description, 'DR', $payment_mode, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $created_at, $createdGstTransaction, NULL, $ssb_account_id_to = NULL, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, 0, $cheque_id = NULL, $companyId, $isApp);

                    $allTranRDcheque = CommanTransactionFacade::headTransactionCreate($dayBookRef, $branch_id, $bank_id = NULL, $bank_ac_id = NULL, $sgstHead, $type, $sub_type_sgst, $type_id, $associate_id, $member_id, $branch_id_to = NULL, $branch_id_from = NULL, $gstAmount, $descriptionB, 'CR', $payment_mode, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $created_at, $createdGstTransaction, NULL, $ssb_account_id_to = NULL, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, 0, $cheque_id = NULL, $companyId, $isApp);

                    $allTranRDcheque = CommanTransactionFacade::headTransactionCreate($dayBookRef, $branch_id, $bank_id = NULL, $bank_ac_id = NULL, 28, $type, $sub_type_sgst, $type_id, $associate_id, $member_id, $branch_id_to = NULL, $branch_id_from = NULL, $gstAmount, $descriptionB, 'DR', $payment_mode, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $created_at, $createdGstTransaction, NULL, $ssb_account_id_to = NULL, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, 0, $cheque_id = NULL, $companyId, $isApp);

                    $allTranRDcheque = CommanTransactionFacade::createBranchDayBookNew($dayBookRef, $branch_id, $type, $sub_type_cgst, $type_id, $associate_id, $member_id, $branch_id_to = NULL, $branch_id_from = NULL, $gstAmount, $description, $description_dr, $description_cr, 'CR', $payment_mode, $currency_code, $v_no, $ssb_account_id_from, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $created_at, $createdGstTransaction, $companyId);

                    $allTranRDcheque = CommanTransactionFacade::createBranchDayBookNew($dayBookRef, $branch_id, $type, $sub_type_sgst, $type_id, $associate_id, $member_id, $branch_id_to = NULL, $branch_id_from = NULL, $gstAmount, $descriptionB, $descriptionB, $descriptionB, 'CR', $payment_mode, $currency_code, $v_no, $ssb_account_id_from, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $created_at, $createdGstTransaction, $companyId);
                    $rec = [
                        'cgst_stationary_chrg' => $gstAmount,
                        'sgst_stationary_chrg' => $gstAmount,
                        'invoice_id' => $createdGstTransaction,
                    ];
                } else {
                    $description = 'Stationary  Igst Charge' . $memberInvestments->account_number;
                    $amountArraySsb = array('1' => ($gstAmount));
                    $createDayBook = CommanController::createDayBook(NULL, $satRefId, 28, $memberInvestments->id, NULL, $memberInvestments->customer_id, $gstAmount, $gstAmount, $withdrawal = 0, ($getHeadSetting->gst_percentage) . 'IGST Charge on Stationary Charge', $memberInvestments->account_number, $memberInvestments->branch_id, getBranchCode($memberInvestments->branch_id)->branch_code, $amountArraySsb, 0, NULL, $memberInvestments->customer_id, $memberInvestments->account_number, $gstAmount, NULL, NULL, date("Y-m-d", strtotime(convertDate($memberInvestments->created_at))), NULL, NULL, NULL, 'CR', $companyId);

                    $allTranRDcheque = CommanTransactionFacade::headTransactionCreate($dayBookRef, $branch_id, $bank_id = NULL, $bank_ac_id = NULL, $cgstHead, $type, $sub_type_igst, $type_id, $associate_id, $member_id, $branch_id_to = NULL, $branch_id_from = NULL, $gstAmount, $description, 'CR', $payment_mode, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $created_at, $createdGstTransaction, NULL, $ssb_account_id_to = NULL, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, 0, $cheque_id = NULL, $companyId);

                    $allTranRDcheque = CommanTransactionFacade::headTransactionCreate($dayBookRef, $branch_id, $bank_id = NULL, $bank_ac_id = NULL, 28, $type, $sub_type_igst, $type_id, $associate_id, $member_id, $branch_id_to = NULL, $branch_id_from = NULL, $gstAmount, $description, 'DR', $payment_mode, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $created_at, $createdGstTransaction, NULL, $ssb_account_id_to = NULL, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, 0, $cheque_id = NULL, $companyId);

                    $allTranRDcheque = CommanTransactionFacade::createBranchDayBookNew($dayBookRef, $branch_id, $type, $sub_type_igst, $type_id, $associate_id, $member_id, $branch_id_to = NULL, $branch_id_from = NULL, $amount, $description, $description, $description, 'CR', $payment_mode, $currency_code, $v_no, $ssb_account_id_from, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $created_at, $createdGstTransaction, $companyId);


                    $rec = [
                        'igst_stationary_chrg' => $gstAmount,
                        'invoice_id' => $createdGstTransaction,
                    ];
                }

                Memberinvestments::whereId($investmentId)->update($rec);
            }
            $response = [
                'status' => true,
                'data' => '50'
            ];
            DB::commit();
        } catch (\Exception $ex) {

            // dd("dfs:sfretrtr" . $ex->getMessage());
            $response = [
                'status' => true,
                'msg' => $ex->getLine(),
            ];
        }
        return $response;
    }

    /**
     * Retrive the nominee details based on provided customer id
     * @package App\Models\Member
     * @param $customerId
     * @return JSONResponse
     */

    public function getNomineeDetails($customerId)
    {
        // Retrieves  nominee details for the specified customer ID from the MemberNominee table.
        $nomineeDetails = MemberNominee::whereMemberId($customerId)->first();


        return $nomineeDetails;
    }

    /**
     * Handle the Scenario when data not found
     *
     * @param $message message that we want to show in response
     * @return JSON Response
     */
    public function handleDataNotFound($message)
    {
        // Create a error response with the supplied message
        $errorResponse = ['message' => $message];

        // return the error response as a JSON object with 404 status code and Error  Response Resource
        return response()->json(new ErrorResponseResource($errorResponse), 200);
    }

    /**
     * Retrive all the active relation from the relations table
     * @package App\Models\Relation
     * @return mixed
     */

    public function getRelation()
    {
        // Return all the active relation
        return Relations::get();
    }

    /**
     * Validation using Form Request
     */

    public function validatePlanTenureRequest($request)
    {
        return $validatedData =
            $request->validate([
                'plan_id' => 'required',
                'company_id' => 'required',
                'customer_id' => 'required',

            ]);
    }

    /**
     * Retrive tenure based on provided plan_id
     * @param array requestedData
     * @method App\Models\PlanTenures
     * @return array
     */

    public function fetchPlanTenure(array $requestedData)
    {
        // Retrive plan tenure based on provided plan_id and status is active
        return PlanTenures::wherePlanId($requestedData['plan_id'])->whereStatus(1)->select('id', 'plan_id', 'tenure', 'roi', 'compounding');
    }

    /**
     * Retrive default charged based on provided data
     * @param array requestedData
     * @param array shortNames
     * @method App\Models\SystemDefaultSettings
     * @return array
     */

    public function fetchCharge($requestedData, array $shortNames)
    {
        // Retrive default setting based on provided short name
        return SystemDefaultSettings()->whereIn(
            'short_name',
            $shortNames
        )->select('amount', 'short_name')
            ->whereCompanyId($requestedData['company_id'])
            ->get()->keyBy('short_name');
    }
}
