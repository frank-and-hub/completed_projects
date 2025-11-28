<?php
namespace App\Http\Controllers\Api;

//die('stop app');
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Validator;
use App\Models\Member;
use App\Models\MemberCompany;
use App\Models\SavingAccountTransactionView;
use App\Models\Memberinvestments;
use App\Models\Daybook;
use App\Models\SavingAccountTranscation;
use App\Models\SavingAccount;
use Carbon;
use App\Models\LoanDayBooks;
use App\Models\Notification;
use App\Models\State;
use App\Models\City;
use App\Models\District;
use App\Services\ImageUpload;
use DB;

class MemberinvestmentController extends Controller
{
    /**
     * Fetch member investments.
     *
     * @param  \App\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function memberInvestments(Request $request)
    {
        $mid = $request->member_id;
        $aNumber = $request->account_number;
        $member = Member::where('member_id', $mid)->where('is_block', 0)->count();
        try {
            if ($member > 0) {
                $token = md5($request->member_id);
                if ($token == $request->token) {
                    $data = array();
                    $mInvestments = Memberinvestments::with('plan', 'member', 'associateMember');
                    $mInvestments = $mInvestments->whereHas('member', function ($query) use ($mid) {
                        $query->where('members.member_id', '' . $mid . '');
                    });
                    $mInvestments = $mInvestments->where('account_number', $aNumber);
                    $mInvestments = $mInvestments->where('is_mature', 1)->where('is_deleted', 0)->orderby('id', 'DESC')->get();
                    foreach ($mInvestments as $key => $value) {
                        $data[$key]['invetment_id'] = $value->id;
                        $data[$key]['account_number'] = $value->account_number;
                        $data[$key]['plan'] = $value['plan']->name;
                        $data[$key]['form_number'] = $value->form_number;
                        $data[$key]['created_at'] = date("d/m/Y", strtotime(str_replace('-', '/', $value->created_at)));
                    }
                    $status = "Success";
                    $code = 200;
                    $messages = 'Member investment listing!';
                    $result = ['investments' => $data];
                    return response()->json(compact('status', 'code', 'messages', 'result'), $code);
                } else {
                    $status = "Error";
                    $code = 201;
                    $messages = 'API token mismatch!';
                    $result = '';
                    return response()->json(compact('status', 'code', 'messages', 'result'), $code);
                }
            } else {
                $status = "Error";
                $code = 201;
                $messages = 'Something went wrong!';
                $result = '';
                return response()->json(compact('status', 'code', 'messages', 'result'), $code);
            }
        } catch (Exception $e) {
            $status = "Error";
            $code = 500;
            $messages = $e->getMessage();
            $result = '';
            return response()->json(compact('status', 'code', 'messages', 'result'), $code);
        }
    }
    /**
     * Fetch investments transactions.
     *
     * @param  \App\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function investmentTransactions(Request $request)
    {
        $mId = $request->member_id;
        $iId = $request->investment_id;
        $member = Member::where('member_id', $mId)->where('is_block', 0)->count();
        // dd($member);
        $eliOpeningAmount = 0.00;
        $accountsNumber = array('R-066523000256', 'R-084511001514', 'R-066523000257', 'R-066523000620', 'R-084523000800', 'R-066523000510', 'R-066523000258', 'R-084504006762', 'R-066504006287', 'R-066523000509', 'R-066523000588', 'R-084504007930');
        try {
            if ($member > 0) {
                $token = md5($mId);
                //dd($token);
                if ($token == $request->token) {
                    if ($request->type == "loan" || $request->type == "group_loan") {
                        if ($request->type == "group_loan") {
                            $investPlan = \App\Models\Grouploans::with(['loanBranch', 'loanMember'])->where('id', $iId)->first();
                        }
                        if ($request->type == "loan") {
                            $investPlan = \App\Models\Memberloans::with(['loanBranch', 'loanMember'])->where('id', $iId)->first();
                        }
                        $totalDeposit = 0;
                        $data = LoanDayBooks::where("account_number", $investPlan->account_number)->where('loan_sub_type', '!=', 2)->orderBy('id', 'desc')->get()->toArray();
                        $data = array_reverse($data);

                        // foreach ($data as $key => $value) {
                        //     dd($value['deposit']);
                        //     if ($value->deposit != '') {
                        //         $data[$key]["emi_deposit"] = (string)$value->deposit;
                        //     } else {
                        //         $data[$key]["emi_deposit"] = '0.00';
                        //     }
                        //     $totalDeposit += $value->deposit;
                        //     $data[$key]["total_deposit"] = (string)$totalDeposit;
                        // }           

                        foreach ($data as $key => $value) {
                            // dd($value['deposit']);
                            if ($value['deposit'] != '') {
                                $data[$key]["emi_deposit"] = (string) $value['deposit'];
                            } else {
                                $data[$key]["emi_deposit"] = '0.00';
                            }
                            $totalDeposit += $value['deposit'];
                            $data[$key]["total_deposit"] = (string) $totalDeposit;
                        }
                        $data = array_reverse($data);
                        $status = "Success";
                        $code = 200;
                        $messages = 'Investment transactions listing!';
                        $result = ['transactions' => $data];
                        return response()->json(compact('status', 'code', 'messages', 'result'), $code);
                    } else {
                        $investPlan = Memberinvestments::with('getPlanCustom')->select('plan_id', 'account_number', 'deposite_amount')->where('id', $iId)->first();
                        if ($investPlan['getPlanCustom']['plan_category_code'] == "S") {
                            // $data = SavingAccountTranscation::where('account_no', $investPlan->account_number)->where('is_deleted', 0)->orderBy(\DB::raw('date(created_at)'), 'DESC')->orderBy('id', 'desc')->get();
                            $data = SavingAccountTransactionView::where('account_no', $investPlan->account_number)->orderBy('opening_date', 'DESC')->orderBy('id', 'desc')->get();
                            foreach ($data as $key => $val) {
                                $data[$key]['id'] = $val->id;
                                $data[$key]['saving_account_id'] = $val->saving_account_id;
                                $data[$key]['branch_id'] = null;
                                $data[$key]['associate_id'] = null;
                                $data[$key]['account_no'] = $val->account_no;
                                $data[$key]['type'] = 5;
                                $data[$key]['opening_balance'] = $val->opening_balance;
                                $data[$key]['deposit'] = $val->deposit;
                                $data[$key]['withdrawal'] = $val->withdrawal;
                                $data[$key]['description'] = $val->description;
                                $data[$key]['reference_no'] = null;
                                $data[$key]['amount'] = $val->amount;
                                $data[$key]['currency_code'] = "INR";
                                $data[$key]['payment_type'] = null;
                                $data[$key]['payment_mode'] = null;
                                $data[$key]['is_renewal'] = null;
                                $data[$key]['status'] = null;
                                $data[$key]['is_deleted'] = 0;
                                $data[$key]['created_at'] = $val->opening_date;
                                $data[$key]['updated_at'] = $val->opening_date;
                                $data[$key]['app_login_user_id'] = null;
                                $data[$key]['is_app'] = null;
                                $data[$key]['jv_journal_id'] = null;
                                $data[$key]['created_at_default'] = $val->transaction_default_date;
                                $data[$key]['daybook_ref_id'] = null;
                                $data[$key]['debit_card_transaction_id'] = null;
                                $data[$key]['company_id'] = null;
                            }
                            foreach ($data as $key => $value) {
                                if ($value->amount == '') {
                                    $data[$key]['amount'] = '0.00';
                                }
                                if ($value->deposit == '') {
                                    $data[$key]['deposit'] = '0.00';
                                }
                                if ($value->withdrawal == '') {
                                    $data[$key]['withdrawal'] = '0.00';
                                }
                                //$data[$key]['opening_balance'] = (string)number_format($value->opening_balance, 2, '.', '');
                            }
                        } else {
                            $eliOpeningAmount = Daybook::where('investment_id', $iId)->whereIn('account_no', $accountsNumber)->where('is_eli', 1)->where(function ($q) {
                                $q->whereIN('transaction_type', [2, 4, 15, 16, 17, 18, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29]);
                            })->pluck('amount')->where('is_deleted', 0)->first();
                            $eliOpeningAmount = $eliOpeningAmount != null ? round($eliOpeningAmount, 2) : 0.00;
                            $data = Daybook::where('investment_id', $iId)->where(function ($q) {
                                $q->whereIN('transaction_type', [2, 4, 15, 16, 17, 18, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29]);
                            })->where('is_deleted', 0)->orderBy(\DB::raw('date(created_at)'), 'DESC')->orderBy('id', 'desc')->get();


                            foreach ($data as $key => $value) {
                                if ($value->is_eli == 1 && in_array($value->account_no, $accountsNumber)) {
                                    unset($data[$key]);
                                } else if (in_array($value->account_no, $accountsNumber)) {
                                    $data[$key]['opening_balance'] = $value->opening_balance - $eliOpeningAmount;
                                }
                            }
                        }

                        $status = "Success";
                        $code = 200;
                        $messages = 'Investment transactions listing!';
                        $result = ['transactions' => $data];
                        return response()->json(compact('status', 'code', 'messages', 'result'), $code);
                    }
                } else {
                    $status = "Error";
                    $code = 201;
                    $messages = 'API token mismatch!';
                    $result = '';
                    return response()->json(compact('status', 'code', 'messages', 'result'), $code);
                }
            } else {
                $status = "Error";
                $code = 201;
                $messages = 'Something went wrong!';
                $result = '';
                return response()->json(compact('status', 'code', 'messages', 'result'), $code);
            }
        } catch (Exception $e) {
            $status = "Error";
            $code = 500;
            $messages = $e->getMessage();
            $result = '';
            return response()->json(compact('status', 'code', 'messages', 'result'), $code);
        }
    }
    
    /**
     * View transaction details.
     *
     * @param  \App\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function viewTransactionDetails(Request $request)
    {
        $mId = $request->member_id;
        $iId = $request->investment_id;
        $tId = $request->transaction_id;
        $type = $request->type;
        $member = Member::where('member_id', $mId)->where('is_block', 0)->count();
        try {
            if ($member > 0) {
                $token = md5($mId);
                if ($token == $request->token) {
                    $data = array();
                    if ($type == 'deposit' || $type == 'ssb') {
                        $investPlan = Memberinvestments::with('plan')->select('plan_id', 'deposite_amount', 'due_amount', 'associate_id', 'branch_id', 'created_at', 'member_id', 'tenure', 'id', 'account_number')->with('branch')->where('id', $iId)->first();
                        if (isset($investPlan->id)) {
                            $globaldate = checkMonthAvailability(date('d'), date('m'), date('Y'), $investPlan['branch']->state_id);
                            $globaldate = date("Y-m-d", strtotime(convertDate($globaldate)));
                            if ($investPlan['plan']['plan_category_code'] == "S") {
                                $data['tDetails'] = SavingAccountTranscation::where('id', $tId)->where('is_deleted', 0)->first();
                                $mId = SavingAccount::select('member_investments_id', 'member_id')->where('account_no', $data['tDetails']->account_no)->first();
                                $aId = Memberinvestments::select('associate_id')->where('id', $mId->member_investments_id)->where('is_deleted', 0)->first();
                                $data['tDetails']['member_id'] = $mId->member_id;
                                $data['tDetails']['associate_id'] = $aId->associate_id;
                                if ($data['tDetails']->amount == '') {
                                    $data['tDetails']['amount'] = $data['tDetails']->deposit;
                                }
                            } else {
                                $data['tDetails'] = Daybook::where('id', $tId)->first();
                            }
                            $datane = MemberCompany::select('customer_id', 'associate_id')->find($data['tDetails']->member_id)->toArray();
                            $memberDetail = Member::find($datane['customer_id']);
                            // dd($memberDetail);
                            $associateDetail = Member::find($investPlan->associate_id);
                            $data['tDetails']['deo_amount'] = $investPlan->deposite_amount;
                            $data['tDetails']['user_name'] = $memberDetail->first_name . ' ' . $memberDetail->last_name;
                            $data['tDetails']['associate_name'] = $associateDetail->first_name . ' ' . $associateDetail->last_name;
                            $data['tDetails']['associate_code'] = $associateDetail->associate_no;
                            $data['tDetails']['plan_id'] = $investPlan->plan_id;
                            //  $dueAmount = CommanController::getRecords($investPlan,$globaldate);
                            $currentDate = Carbon\Carbon::now();
                            $record = $this->getRecords($investPlan, $currentDate);
                            $pendingEmiAMount = 0;
                            if (isset($record['pendingEmiAMount'])) {
                                $pendingEmiAMount = $record['pendingEmiAMount'];
                                if ($record['pendingEmiAMount'] > 0) {
                                    $pendingEmiAMount = 0;
                                }
                            }
                            $pendingEmiAMount = str_replace('-', '', $pendingEmiAMount);
                            $data['tDetails']['due_amount'] = number_format((float) $pendingEmiAMount, 2, '.', '');
                            $Status = 'Success';
                            $Code = 200;
                            $msg = 'Transaction details!';
                        } else {
                            $Status = 'Failure';
                            $Code = 201;
                            $msg = 'No Record Found';
                        }
                    }
                    if ($type == 'loan') {
                        $investPlan = \App\Models\Memberloans::with(['loanBranch', 'loanMember'])->where('id', $iId)->first();
                        if (isset($investPlan->id)) {
                            $globaldate = checkMonthAvailability(date('d'), date('m'), date('Y'), $investPlan['loanBranch']->state_id);
                            $globaldate = date("Y-m-d", strtotime(convertDate($globaldate)));
                            $data['tDetails'] = \App\Models\LoanDayBooks::select('id', 'loan_id', 'loan_type', 'account_number', 'branch_id', 'created_at', 'applicant_id', 'deposit', 'description', 'associate_id')->where('id', $tId)->where('is_deleted', 0)->first();
                            $data['tDetails']['member_id'] = $data['tDetails']->applicant_id;
                            $data['tDetails']['associate_id'] = $data['tDetails']->associate_id;
                            $data['tDetails']['amount'] = $data['tDetails']->deposit;
                            $memberDetail = Member::find($data['tDetails']->applicant_id);
                            $associateDetail = Member::find($data['tDetails']->associate_id);
                            $data['tDetails']['deo_amount'] = '';
                            $data['tDetails']['user_name'] = $memberDetail->first_name . ' ' . $memberDetail->last_name;
                            $data['tDetails']['associate_name'] = $associateDetail->first_name . ' ' . $associateDetail->last_name;
                            $data['tDetails']['associate_code'] = $associateDetail->associate_no;
                            $data['tDetails']['plan_id'] = $investPlan->loan_type;
                            $Status = 'Success';
                            $Code = 200;
                            $msg = 'Transaction details!';
                        } else {
                            $Status = 'Failure';
                            $Code = 201;
                            $msg = 'No Record Found';
                        }
                    }
                    if ($type == 'group_loan') {
                        $investPlan = \App\Models\Grouploans::with(['loanBranch', 'loanMember'])->where('id', $iId)->first();
                        if (isset($investPlan->id)) {
                            $globaldate = checkMonthAvailability(date('d'), date('m'), date('Y'), $investPlan['loanBranch']->state_id);
                            $globaldate = date("Y-m-d", strtotime(convertDate($globaldate)));
                            $data['tDetails'] = \App\Models\LoanDayBooks::select('id', 'loan_id', 'loan_type', 'account_number', 'branch_id', 'created_at', 'applicant_id', 'deposit', 'description', 'associate_id')->where('id', $tId)->where('is_deleted', 0)->first();
                            $data['tDetails']['member_id'] = $data['tDetails']->applicant_id;
                            $data['tDetails']['associate_id'] = $data['tDetails']->associate_id;
                            $data['tDetails']['amount'] = $data['tDetails']->deposit;
                            $memberDetail = Member::find($data['tDetails']->applicant_id);
                            $associateDetail = Member::find($data['tDetails']->associate_id);
                            $data['tDetails']['deo_amount'] = '';
                            $data['tDetails']['user_name'] = $memberDetail->first_name . ' ' . $memberDetail->last_name;
                            $data['tDetails']['associate_name'] = $associateDetail->first_name . ' ' . $associateDetail->last_name;
                            $data['tDetails']['associate_code'] = $associateDetail->associate_no;
                            $data['tDetails']['plan_id'] = $investPlan->loan_type;
                            $Status = 'Success';
                            $Code = 200;
                            $msg = 'Transaction details!';
                        } else {
                            $Status = 'Failure';
                            $Code = 201;
                            $msg = 'No Record Found';
                        }
                    }
                    $status = $Status;
                    $code = $Code;
                    $messages = $msg;
                    $result = ['transactionDetail' => $data];
                    return response()->json(compact('status', 'code', 'messages', 'result'), $code);
                } else {
                    $status = "Error";
                    $code = 201;
                    $messages = 'API token mismatch!';
                    $result = '';
                    return response()->json(compact('status', 'code', 'messages', 'result'), $code);
                }
            } else {
                $status = "Error";
                $code = 201;
                $messages = 'Something went wrong!';
                $result = '';
                return response()->json(compact('status', 'code', 'messages', 'result'), $code);
            }
        } catch (Exception $e) {
            $status = "Error";
            $code = 500;
            $messages = $e->getMessage();
            $result = '';
            return response()->json(compact('status', 'code', 'messages', 'result'), $code);
        }
    }
    /**
     * View member details.
     *
     * @param  \App\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function memberDetails(Request $request)
    {
        $mId = $request->member_id;
        $member = Member::where('member_id', $mId)->where('is_block', 0)->count();
        $mDetails = Member::select('id')->where('member_id', $mId)->where('is_block', 0)->first();
        $notificationCount = Notification::where('user_id', $mDetails->id)->where('is_read', 0)->count();
        $data['account_numbers'] = array();
        try {
            if ($member > 0) {
                $token = md5($mId);
                if ($token == $request->token) {
                    $mInvestments = Memberinvestments::select('account_number', 'id', 'plan_id')->with('plan')->whereCustomerId($mDetails->id)->where('is_mature', 1)->where('is_deleted', 0)->get();
                    $data['memberDetail'] = Member::where('id', $mDetails->id)->first();
                    // echo "<pre>"; print_r($data['memberDetail']); die;
                    $data['bankDetail'] = \App\Models\MemberBankDetail::where('member_id', $mDetails->id)->first();
                    $data['nomineeDetail'] = \App\Models\MemberNominee::where('member_id', $mDetails->id)->first();
                    $data['idProofDetail'] = \App\Models\MemberIdProof::where('member_id', $mDetails->id)->first();
                    $address = $data['memberDetail']->address;
                    $state = \App\Models\State::where('id', $data['memberDetail']['state_id'])->pluck('name');
                    $district_id = \App\Models\District::where('id', $data['memberDetail']['district_id'])->pluck('name');
                    $city_id = \App\Models\City::where('id', $data['memberDetail']['city_id'])->pluck('name');
                    $pin_code = $data['memberDetail']['pin_code'];
                    $address = $address . ', ' . $city_id . ', ' . $district_id . ', ' . $state . '-' . $pin_code;
                    $newAddress = str_replace(['[', ']', '"'], '', $address);
                    foreach ($mInvestments as $key => $mInvestment) {
                        $data['account_numbers'][$key] = $mInvestment->account_number . '-' . $mInvestment['plan']->name;
                    }
                    if ($data['memberDetail']->photo) {

                        $folderName = 'profile/member_avatar/' . $data['memberDetail']->photo;
                        $url = ImageUpload::fileExists($folderName) ? ImageUpload::generatePreSignedUrl($folderName) : '';

                        $data['memberDetail']['imageurl'] = $url ?? env('APP_URL') . '/asset/images/no-image.png';
                    } else {
                        $data['memberDetail']['imageurl'] = env('APP_URL') . '/asset/images/no-image.png';
                    }
                    if ($data['memberDetail']->signature) {

                        $folderName = 'profile/member_signature/' . $data['memberDetail']->signature;
                        $url = ImageUpload::fileExists($folderName) ? ImageUpload::generatePreSignedUrl($folderName) : '';

                        $data['memberDetail']['signatureurl'] = $url ?? env('APP_URL') . '/asset/images/no-image.png';
                    } else {
                        $data['memberDetail']['signatureurl'] = env('APP_URL') . '/asset/images/no-image.png';
                    }
                    if ($data['memberDetail']->gender == 0) {
                        $data['memberDetail']['gender'] = 'Female';
                    } elseif ($data['memberDetail']->gender == 1) {
                        $data['memberDetail']['gender'] = 'Male';
                    }
                    if ($data['memberDetail']->address != '') {
                        $data['memberDetail']['address'] = $newAddress;
                    } else {
                        $data['memberDetail']['address'] = '';
                    }
                    if ($data['memberDetail']->marital_status == 0) {
                        $data['memberDetail']['marital_status'] = 'Un Married';
                    } elseif ($data['memberDetail']->marital_status == 1) {
                        $data['memberDetail']['marital_status'] = 'Married';
                    }
                    $data['memberDetail']['occupation_id'] = getOccupationName($data['memberDetail']->occupation_id);
                    if ($data['memberDetail']->religion_id > 0) {
                        $data['memberDetail']['religion_id'] = getReligionName($data['memberDetail']->religion_id);
                    }
                    if ($data['memberDetail']->special_category_id > 0) {
                        $data['memberDetail']['special_category_id'] = getSpecialCategoryName($data['memberDetail']->special_category_id);
                    } else {
                        $data['memberDetail']['special_category_id'] = 'General Category';
                    }
                    if ($data['memberDetail']->status == 1)
                        $data['memberDetail']['status'] = 'Active';
                    else {
                        $data['memberDetail']['status'] = 'Inactive';
                    }
                    if (isset($data['nomineeDetail']->is_minor)) {
                        if ($data['nomineeDetail']->is_minor == 1)
                            $data['nomineeDetail']['is_minor'] = 'Yes';
                        else {
                            $data['nomineeDetail']['is_minor'] = 'No';
                        }
                    }
                    if (isset($data['nomineeDetail']->status)) {
                        if ($data['nomineeDetail']->status == 1)
                            $data['nomineeDetail']['status'] = 'Active';
                        else {
                            $data['nomineeDetail']['status'] = 'Inactive';
                        }
                    }
                    if (isset($data['nomineeDetail']->relation)) {
                        if ($data['nomineeDetail']->relation > 0) {
                            $data['nomineeDetail']['relation'] = getRelationsName($data['nomineeDetail']->relation);
                        }
                    }
                    if (isset($data['nomineeDetail']->gender)) {
                        if ($data['nomineeDetail']->gender == 0) {
                            $data['nomineeDetail']['gender'] = 'Female';
                        } elseif ($data['nomineeDetail']->gender == 1) {
                            $data['nomineeDetail']['gender'] = 'Male';
                        }
                    }
                    if (isset($data['nomineeDetail']->status)) {
                        if ($data['idProofDetail']->status == 1)
                            $data['idProofDetail']['status'] = 'Active';
                        else {
                            $data['idProofDetail']['status'] = 'Inactive';
                        }
                    }

                    $data['idProofDetail']['first_id_type_id'] = isset($data['idProofDetail']->first_id_type_id) ? getIdProofName($data['idProofDetail']->first_id_type_id) : '';
                    $data['idProofDetail']['second_id_type_id'] = isset($data['idProofDetail']->second_id_type_id) ? getIdProofName($data['idProofDetail']->second_id_type_id) : '';
                    $status = "Success";
                    $code = 200;
                    $messages = 'Member details!';
                    $notification_count = $notificationCount;
                    $result = $data;

                    return response()->json(compact('status', 'code', 'messages', 'notification_count', 'result'), $code);
                } else {
                    $status = "Error";
                    $code = 201;
                    $messages = 'API token mismatch!';
                    $notification_count = $notificationCount;
                    $result = '';
                    return response()->json(compact('status', 'code', 'notification_count', 'messages', 'result'), $code);
                }
            } else {
                $status = "Error";
                $code = 201;
                $messages = 'Something went wrong!';
                $notification_count = $notificationCount;
                $result = '';
                return response()->json(compact('status', 'code', 'notification_count', 'messages', 'result'), $code);
            }
        } catch (Exception $e) {
            $status = "Error";
            $code = 500;
            $messages = $e->getMessage();
            $result = '';
            return response()->json(compact('status', 'code', 'messages', 'result'), $code);
        }
    }
    public static function getRecords($dailyDepositeRecord, $currentDate)
    {
        //print_r($dailyDepositeRecord['plan_id']);die;
        switch ($dailyDepositeRecord['plan_id']) {
            case '7':
                $pendingEmiAMount = 0;
                $pendingEmi = 0;
                $investCreatedDate = strtotime($dailyDepositeRecord['created_at']);
                $CURRENTDATE = strtotime($currentDate);
                $totalBetweendays = abs($investCreatedDate - $CURRENTDATE);
                $totalBetweendays = ceil(floatval($totalBetweendays / 86400));
                $totalAmount = ($totalBetweendays + 1) * $dailyDepositeRecord['deposite_amount'];
                $getRenewalReceivedAmount = \App\Models\Daybook::whereIn('transaction_type', [2, 4])->where('account_no', $dailyDepositeRecord['account_number'])->sum('deposit');
                if ($getRenewalReceivedAmount != $totalAmount) {
                    $pendingEmiAMount = $getRenewalReceivedAmount - $totalAmount;
                    $pendingEmi = $pendingEmiAMount / $dailyDepositeRecord['deposite_amount'];
                } else {
                    $pendingEmiAMount = 0;
                    $pendingEmi = 0;
                }
                return ['pendingEmiAMount' => $pendingEmiAMount, 'pendingEmi' => $pendingEmi];
                break;
            case '2':
            case '3':
            case '5':
            case '7':
            case '10':
            case '11':
                $pendingEmiAMount = 0;
                $pendingEmi = 0;
                $investCreatedDate = Carbon\Carbon::parse($dailyDepositeRecord['created_at']);
                $totalBetweenmonth = $investCreatedDate->diffInMonths($currentDate);
                $totalAmount = ($totalBetweenmonth + 1) * $dailyDepositeRecord['deposite_amount'];
                $getRenewalReceivedAmount = \App\Models\Daybook::whereIn('transaction_type', [2, 4])->where('account_no', $dailyDepositeRecord['account_number'])->sum('deposit');
                if ($getRenewalReceivedAmount != $totalAmount) {
                    $pendingEmiAMount = $getRenewalReceivedAmount - $totalAmount;
                    $pendingEmi = $dailyDepositeRecord['deposite_amount'] > 0 ? ($pendingEmiAMount / $dailyDepositeRecord['deposite_amount']) : 0;
                } else {
                    $pendingEmiAMount = 0;
                    $pendingEmi = 0;
                }
                return ['pendingEmiAMount' => $pendingEmiAMount, 'pendingEmi' => $pendingEmi];
                break;
        }
    }
}