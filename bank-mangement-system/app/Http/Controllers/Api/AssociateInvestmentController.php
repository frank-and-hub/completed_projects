<?php
namespace App\Http\Controllers\Api;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Validator;
use App\Models\MemberIdProof;
use App\Models\Member;
use App\Models\Memberinvestments;
use App\Models\Daybook;
use App\Models\HeadSetting;
use App\Models\GstSetting;
use App\Models\CompanyBranch;
use App\Models\SavingAccountTranscation;
use App\Models\SavingAccount;
use App\Models\TranscationLog;
use App\Models\Transcation;
use App\Models\Investmentplantransactions;
use App\Models\Plans;
use App\Models\Relations;
use App\Models\Branch;
use App\Models\Memberinvestmentsnominees;
use App\Models\Investmentplanamounts;
use App\Models\Memberinvestmentspayments;
use Carbon\Carbon;
use Session;
use App\Services\Sms;
use App\Http\Controllers\Branch\CommanTransactionsController;

class AssociateInvestmentController extends Controller
{
    /**
     * Fetch member investments.
     *
     * @param  \App\Request  $request
     * @return \Illuminate\Http\Response
     */
    function getCategoryTree($parent_id, $tree_array = array())
    {
        $categories = associateTreeid($parent_id);
        foreach ($categories as $item) {
            $tree_array[] = ['member_id' => $item->member_id, 'status' => $item['member']->associate_status, 'is_block' => $item['member']->is_block];
            $tree_array = $this->getCategoryTree($item->member_id, $tree_array);
        }
        return $tree_array;
    }
    public function branchList(Request $request)
    {
        $associate_no = $request->associate_no;
        try {
            $member = Member::select('id', 'associate_app_status')->where('associate_no', $request->associate_no)->where('associate_status', 1)->where('is_block', 0)->first();
            if ($member) {
                $token = md5($request->associate_no);
                if ($token == $request->token) {
                    $data = array();
                    $branchData = Branch::where('status', 1)->get();
                    if (count($branchData)) {
                        foreach ($branchData as $key => $value) {
                            $data[$key]['id'] = $value->id;
                            $data[$key]['branch_code'] = $value->branch_code;
                            $data[$key]['name'] = $value->name;
                            $data[$key]['sector'] = $value->sector;
                            $data[$key]['name_code'] = $value->name . ' - ' . $value->branch_code;
                        }
                    }
                    $status = "Success";
                    $code = 200;
                    $messages = 'Branch listing!';
                    $result = ['branch' => $data, 'total_count' => count($data)];
                    $associate_status = $member->associate_app_status;
                    return response()->json(compact('status', 'code', 'messages', 'result', 'token', 'associate_status'), $code);
                } else {
                    $status = "Error";
                    $code = 201;
                    $messages = 'API token mismatch!';
                    $result = '';
                    $associate_status = $member->associate_app_status;
                    return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                }
            } else {
                $status = "Error";
                $code = 201;
                $messages = 'Something went wrong!';
                $result = '';
                $associate_status = 9;
                return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
            }
        } catch (\Exception $e) {
            $status = "Error";
            $code = 500;
            $messages = $e->getMessage();
            $result = '';
            $associate_status = 9;
            return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
        }
    }
    public function paymentModeRegister(Request $request)
    {
        $associate_no = $request->associate_no;
        try {
            $member = Member::select('id', 'associate_app_status')->where('associate_no', $request->associate_no)->where('associate_status', 1)->where('is_block', 0)->first();
            if ($member) {
                $token = md5($request->associate_no);
                if ($token == $request->token) {
                    $data = array();
                    $data[0]['value'] = 0;
                    $data[0]['name'] = 'Cash';
                    $data[1]['value'] = 1;
                    $data[1]['name'] = 'Cheque';
                    $data[2]['value'] = 2;
                    $data[2]['name'] = 'Online transaction';
                    $data[3]['value'] = 3;
                    $data[3]['name'] = 'SSB account';
                    $status = "Success";
                    $code = 200;
                    $messages = 'PaymentMode listing!';
                    $result = ['paymentMode' => $data, 'total_count' => count($data)];
                    $associate_status = $member->associate_app_status;
                    return response()->json(compact('status', 'code', 'messages', 'result', 'token', 'associate_status'), $code);
                } else {
                    $status = "Error";
                    $code = 201;
                    $messages = 'API token mismatch!';
                    $result = '';
                    $associate_status = $member->associate_app_status;
                    return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                }
            } else {
                $status = "Error";
                $code = 201;
                $messages = 'Something went wrong!';
                $result = '';
                $associate_status = 9;
                return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
            }
        } catch (\Exception $e) {
            $status = "Error";
            $code = 500;
            $messages = $e->getMessage();
            $result = '';
            $associate_status = 9;
            return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
        }
    }
    public function planList(Request $request)
    {
        $associate_no = $request->associate_no;
        try {
            $member = Member::select('id', 'associate_app_status')->where('associate_no', $request->associate_no)->where('associate_status', 1)->where('is_block', 0)->first();
            $customer_branch = Member::whereMember_id($request->customer_id)->value('branch_id');
            if ($member) {
                $token = md5($request->associate_no);
                if ($token == $request->token) {
                    $data = array();
                    $branchData = Plans::where('status', 1)->whereHas('company')->get();
                    if (count($branchData)) {
                        foreach ($branchData as $key => $value) {
                            $data[$key]['id'] = $value->id;
                            $data[$key]['name'] = $value->name;
                            $data[$key]['plan_code'] = $value->plan_code;
                            $data[$key]['company_id'] = $value->company_id;
                            $data[$key]['plan_category_code'] = $value->plan_category_code;
                            $data[$key]['plan_sub_category_code'] = $value->plan_sub_category_code;
                            $data[$key]['is_ssb_required'] = $value->is_ssb_required;
                            $data[$key]['deno_amount'] = $value->multiple_deposit;
                            $data[$key]['min_deposit'] = $value->min_deposit;
                            $detail = getBranchDetail($customer_branch)->state_id;
                            $getHeadSetting = HeadSetting::where('head_id', 122)->first();
                            $globaldate = checkMonthAvailability(date('d'), date('m'), date('Y'), $detail);
                            $getGstSetting = GstSetting::where('state_id', $detail)->where('applicable_date', '<=', $globaldate)->where('company_id', $value->company_id)->exists();
                            $gstAmount = 0;
                            $getGstSettingno = GstSetting::select('id', 'gst_no', 'state_id')->where('state_id', $detail)->where('applicable_date', '<=', $globaldate)->where('company_id', $value->company_id)->first();
                            $amount = 50;
                            $cgst = 0;
                            $sgst = 0;
                            $igst = 0;
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
                            if ($gstAmount > 0) {
                                if ($IntraState == true) {
                                    $cgst = $gstAmount;
                                    $sgst = $gstAmount;
                                } else if ($IntraState == false) {
                                    $igst = $gstAmount;
                                }
                            }
                            $data[$key]['gst_amount'] = $cgst + $sgst + $igst;
                        }
                    }
                    $status = "Success";
                    $code = 200;
                    $messages = 'Plan listing!';
                    $result = ['plan' => $data, 'total_count' => count($data)];
                    $associate_status = $member->associate_app_status;
                    return response()->json(compact('status', 'code', 'messages', 'result', 'token', 'associate_status'), $code);
                    /* `dd();` is a debugging function in Laravel that stands for "dump and die". It
                is used to dump the value of a variable or expression and immediately terminate the
                script execution. In this case, it is used to debug the `` variable inside the
                `transform` function. It will display the value of `` and stop the execution
                of the script. */ // Sourab Biswas ....
                } else {
                    $status = "Error";
                    $code = 201;
                    $messages = 'API token mismatch!';
                    $result = '';
                    $associate_status = $member->associate_app_status;
                    return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                }
            } else {
                $status = "Error";
                $code = 201;
                $messages = 'Something went wrong!';
                $result = '';
                $associate_status = 9;
                return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
            }
        } catch (\Exception $e) {
            $status = "Error";
            $code = 500;
            $messages = $e->getMessage();
            $result = '';
            $associate_status = 9;
            return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
        }
    }
    public function paymentModeRenew(Request $request)
    {
        $associate_no = $request->associate_no;
        try {
            $member = Member::select('id', 'associate_app_status')->where('associate_no', $request->associate_no)->where('associate_status', 1)->where('is_block', 0)->first();
            if ($member) {
                $token = md5($request->associate_no);
                if ($token == $request->token) {
                    $data = array();
                    $data[0]['value'] = 0;
                    $data[0]['name'] = 'Cash';
                    $data[1]['value'] = 1;
                    $data[1]['name'] = 'Cheque';
                    /*$data[2]['value'] = 2; 
                        $data[2]['name'] = 'DD';  
                        $data[2]['value'] = 3; 
                        $data[2]['name'] = 'Online transaction'; */
                    $data[2]['value'] = 4;
                    $data[2]['name'] = 'SSB account';
                    $status = "Success";
                    $code = 200;
                    $messages = ' Renew PaymentMode listing!';
                    $result = ['paymentMode' => $data, 'total_count' => count($data)];
                    $associate_status = $member->associate_app_status;
                    return response()->json(compact('status', 'code', 'messages', 'result', 'token', 'associate_status'), $code);
                } else {
                    $status = "Error";
                    $code = 201;
                    $messages = 'API token mismatch!';
                    $result = '';
                    $associate_status = $member->associate_app_status;
                    return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                }
            } else {
                $status = "Error";
                $code = 201;
                $messages = 'Something went wrong!';
                $result = '';
                $associate_status = 9;
                return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
            }
        } catch (\Exception $e) {
            $status = "Error";
            $code = 500;
            $messages = $e->getMessage();
            $result = '';
            $associate_status = 9;
            return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
        }
    }
    public function investmentList(Request $request)
    {
        $associate_no = $request->associate_no;
        try {
            $member = Member::with(['savingAccount_Custom' => function ($q) {
                $q->with('savingAccountBalance');
            }])->select('id', 'member_id', 'associate_app_status')->where('associate_no', $request->associate_no)->where('associate_status', 1)->where('is_block', 0)->first();
            $balance = 0;
            if (isset($member['savingAccount_Custom']['savingAccountBalance'])) {
                $balance = $member['savingAccount_Custom']['savingAccountBalance']->sum('deposit') - $member['savingAccount_Custom']['savingAccountBalance']->sum('withdrawal');
            }
            $balance = number_format((float) $balance, 2, '.', '');
            if ($member) {
                $token = md5($request->associate_no);
                if ($token == $request->token) {
                    if ($request->page > 0 && $request->length > 0) {
                        $invest_data = Memberinvestments::with([
                            'plan:id,name,plan_code,plan_category_code',
                            'member:id,member_id,first_name,last_name,village,city_id,pin_code,state_id,district_id,address,mobile_no',
                            'associateMember:id,associate_no,first_name,last_name',
                            'branch:id,name,branch_code,sector,regan,zone'
                        ])
                            ->with([
                                'memberCompany' => function ($q) {
                                    $q->select('id', 'member_id')
                                        ->with(['ssb_detail' => function ($q1) {
                                            $q1->select('id', 'account_no', 'member_id', 'customer_id')
                                                ->with([
                                                    'getSSBAccountBalance', 
                                                    'savingAccountTransactionViewOrderBy'
                                                ]);
                                        }]);
                                }
                            ])
                            ->where('company_id', $request->company_id)
                            ->whereHas('company')
                            ->where('associate_id', $member->id);
                        if ($request->from_date != '') {
                            $startDate = date("Y-m-d", strtotime(convertDate($request->from_date)));
                            if ($request->to_date != '') {
                                $endDate = date("Y-m-d ", strtotime(convertDate($request->to_date)));
                            } else {
                                $endDate = '';
                            }
                            $invest_data = $invest_data->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate]);
                        }
                        if ($request->plan_id != '') {
                            $plan_id = $request->plan_id;
                            $invest_data = $invest_data->where('plan_id', '=', $plan_id);
                        }
                        if ($request->associate_id != '') {
                            $associate_id = $request->associate_id;
                            $invest_data = $invest_data->where('associate_id', $associate_id);
                        }
                        $currentDate = Carbon::now();
                        $currentDate = $currentDate->format('Y-m-d');
                        if ($currentDate) {
                            $invest_data = $invest_data->where('maturity_date', '>=', $currentDate);
                        }
                        $invest_data1 = $invest_data->where('is_mature', 1)->orderby('id', 'DESC')->get();
                        $count = count($invest_data1);
                        if ($request->page == 1) {
                            $start = 0;
                        } else {
                            $start = ($request->page - 1) * $request->length;
                        }
                        $invest_data = $invest_data->orderby('id', 'DESC')->offset($start)->limit($request->length)->get();
                        if (count($invest_data) == 0) {
                            $status = "Error";
                            $code = 201;
                            $messages = 'No investment found of this company.';
                            $result = json_decode('{}');
                            $associate_status = 9;
                            return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                        }
                        foreach ($invest_data as $key => $row) {
                            $data[$key]['id'] = $row->id;
                            $data[$key]['plan'] = $row['plan']->name;
                            $data[$key]['form_number'] = $row->form_number;
                            $data[$key]['tenure'] = ($row->plan_category_code == 'S') ? 'N/A' : $row->tenure . ' Year';
                            if ($row->plan_category_code == 'S') {
                                // $ssb = $row['memberCompany']['ssb_detail']['getSSBAccountBalance']['totalBalance'] ?? 0;
                                $ssb = $row['memberCompany']['ssb_detail']['savingAccountTransactionViewOrderBy']['opening_balance'] ?? 0;
                                $current_balance = $ssb->balance;
                            } else {
                                $current_balance = $row->current_balance;
                            }
                            $data[$key]['current_balance'] = $current_balance;
                            $data[$key]['eli_amount'] = investmentEliAmount($row->id);
                            $data[$key]['deposite_amount'] = $row->deposite_amount;
                            $data[$key]['member'] = $row['member']->first_name . ' ' . $row['member']->last_name;
                            $data[$key]['customer_id'] = $row['member']->member_id;
                            $data[$key]['member_id'] = $row['memberCompany']->member_id;
                            $data[$key]['mobile_number'] = $row['member']->mobile_no ?? 'N/A';
                            $data[$key]['associate_code'] = $row['associateMember'] ? $row['associateMember']['associate_no'] : '';
                            $data[$key]['account_number'] = $row['account_number'];
                            $data[$key]['created_at'] = date("d/m/Y", strtotime($row['created_at']));
                            $data[$key]['account_number'] = $row->account_number;
                            $data[$key]['associate_name'] = $row['associateMember'] ? $row['associateMember']['first_name'] . ' ' . $row['associateMember']['last_name'] : '';
                            $data[$key]['branch'] = $row['branch']->name;
                            $data[$key]['branch_code'] = $row['branch']->branch_code;
                            $data[$key]['sector_name'] = $row['branch']->sector;
                            $data[$key]['region_name'] = $row['branch']->regan;
                            $data[$key]['zone_name'] = $row['branch']->zone;
                            $idProofDetail = MemberIdProof::where('member_id', $row['member']->id)->first();
                            $data[$key]['firstId'] = getIdProofName($idProofDetail->first_id_type_id) . ' - ' . $idProofDetail->first_id_no;
                            $data[$key]['secondId'] = getIdProofName($idProofDetail->second_id_type_id) . ' - ' . $idProofDetail->second_id_no;
                            $data[$key]['address'] = $row['member']->address;
                            $data[$key]['state'] = getStateName($row['member']->state_id);
                            $data[$key]['district'] = getDistrictName($row['member']->district_id);
                            $data[$key]['city'] = getCityName($row['member']->city_id);
                            $data[$key]['village'] = $row['member']->village;
                            $data[$key]['pin_code'] = $row['member']->pin_code;
                            // $data[$key]['ssb_balance']  =  $row['memberCompany']['ssb_detail']['getSSBAccountBalance']['totalBalance'] ?? 0;
                            $data[$key]['ssb_balance'] = $row['memberCompany']['ssb_detail']['savingAccountTransactionViewOrderBy']['opening_balance'] ?? 0;
                            $data[$key]['AccountNumber'] = $row['memberCompany']['ssb_detail']['getSSBAccountBalance']['account_no'] ?? 0;
                            $data[$key]['mId'] = $row->member_id;
                            $data[$key]['company_id'] = $row->company_id;
                            $data[$key]['is_show'] = in_array($row['plan']->plan_category_code, ['D', 'M']) ? 1 : 0;
                        }
                        $status = "Success";
                        $code = 200;
                        $messages = 'Investment listing!';
                        $page = $request->page;
                        $length = $request->length;
                        $result = ['member' => $data, 'total_count' => $count, 'page' => $page, 'length' => $length, 'record_count' => count($data)];
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'token', 'associate_status'), $code);
                    } elseif ($request->page == 0) {
                        $data = array();
                        $a = $this->getCategoryTree($parent_id = $member->id, $tree_array = array());
                        $b[] = array('member_id' => $member->id, 'status' => 1, 'is_block' => 0);
                        $tree_array1 = array();
                        $c = array_merge($a, $b);
                        foreach ($c as $v) {
                            $tree_array1[] = $v['member_id'];
                        }
                        $memberArray = Member::whereIn('associate_id', $tree_array1)->get('id');
                        $memberArray1 = array();
                        foreach ($memberArray as $v) {
                            $memberArray1[] = $v->id;
                        }
                        $invest_data = Memberinvestments::with('plan', 'member', 'associateMember', 'branch')->whereIn('member_id', $memberArray1);
                        if ($request->from_date != '') {
                            $startDate = date("Y-m-d", strtotime(convertDate($request->from_date)));
                            if ($request->to_date != '') {
                                $endDate = date("Y-m-d ", strtotime(convertDate($request->to_date)));
                            } else {
                                $endDate = '';
                            }
                            $invest_data = $invest_data->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate]);
                        }
                        if ($request->plan_id != '') {
                            $plan_id = $request->plan_id;
                            $invest_data = $invest_data->where('plan_id', '=', $plan_id);
                        }
                        if ($request->associate_id != '') {
                            $associate_id = $request->associate_id;
                            $invest_data = $invest_data->where('associate_id', $associate_id);
                        }
                        $currentDate = Carbon::now();
                        $currentDate = $currentDate->format('Y-m-d');
                        if ($currentDate) {
                            $invest_data = $invest_data->where('maturity_date', '>=', $currentDate);
                        }
                        $invest_data1 = $invest_data->where('is_mature', 1)->orderby('id', 'DESC')->get();
                        $count = count($invest_data1);
                        $invest_data = $invest_data->where('is_mature', 1)->orderby('id', 'DESC')->get();
                        foreach ($invest_data as $key => $row) {
                            $data[$key]['id'] = $row->id;
                            $data[$key]['plan'] = $row['plan']->name;
                            $data[$key]['form_number'] = $row->form_number;
                            if ($row->plan_id == 1) {
                                $tenure = 'N/A';
                            } else {
                                $tenure = $row->tenure . ' Year';
                            }
                            $data[$key]['tenure'] = $tenure;
                            if ($row->plan_id == 1) {
                                $ssb = getSsbAccountDetail($row->account_number);
                                $current_balance = $ssb->balance;
                            } else {
                                $current_balance = $row->current_balance;
                            }
                            $data[$key]['current_balance'] = $current_balance;
                            $data[$key]['eli_amount'] = investmentEliAmount($row->id);
                            $data[$key]['deposite_amount'] = $row->deposite_amount;
                            $data[$key]['member'] = $row['member']->first_name . ' ' . $row['member']->last_name;
                            $data[$key]['member_id'] = $row['member']->member_id;
                            $data[$key]['mobile_number'] = $row['member']->mobile_no;
                            $data[$key]['associate_code'] = $row['associateMember']['associate_no'];
                            $data[$key]['account_number'] = $row['account_number'];
                            $data[$key]['created_at'] = date("d/m/Y", strtotime($row['created_at']));
                            $data[$key]['account_number'] = $row->account_number;
                            $data[$key]['associate_name'] = $row['associateMember']['first_name'] . ' ' . $row['associateMember']['last_name'];
                            $data[$key]['branch'] = $row['branch']->name;
                            $data[$key]['branch_code'] = $row['branch']->branch_code;
                            $data[$key]['sector_name'] = $row['branch']->sector;
                            $data[$key]['region_name'] = $row['branch']->regan;
                            $data[$key]['zone_name'] = $row['branch']->zone;
                            $idProofDetail = MemberIdProof::where('member_id', $row['member']->id)->first();
                            $data[$key]['firstId'] = getIdProofName($idProofDetail->first_id_type_id) . ' - ' . $idProofDetail->first_id_no;
                            $data[$key]['secondId'] = getIdProofName($idProofDetail->second_id_type_id) . ' - ' . $idProofDetail->second_id_no;
                            $data[$key]['address'] = $row['member']->address;
                            $data[$key]['state'] = getStateName($row['member']->state_id);
                            $data[$key]['district'] = getDistrictName($row['member']->district_id);
                            $data[$key]['city'] = getCityName($row['member']->city_id);
                            $data[$key]['village'] = $row['member']->village;
                            $data[$key]['pin_code'] = $row['member']->pin_code;
                            $data[$key]['is_show'] = in_array($row['plan']->plan_category_code, ['D', 'M']) ? 1 : 0;
                        }
                        $status = "Success";
                        $code = 200;
                        $messages = 'Investment listing!';
                        $result = ['member' => $data, 'total_count' => $count, 'record_count' => count($data)];
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'token', 'associate_status'), $code);
                    }
                } else {
                    $status = "Error";
                    $code = 201;
                    $messages = 'API token mismatch!';
                    $result = '';
                    $associate_status = $member->associate_app_status;
                    return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                }
            } else {
                $status = "Error";
                $code = 201;
                $messages = 'Something went wrong!';
                $result = '';
                $associate_status = 9;
                return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
            }
        } catch (\Exception $e) {
            $status = "Error";
            $code = 500;
            $messages = $e->getMessage();
            $result = '';
            $associate_status = 9;
            return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
        }
    }
    public function commonListingInvestment(Request $request)
    {
        $associate_no = $request->associate_no;
        try {
            $member = Member::select('id', 'associate_app_status', 'first_name', 'last_name')->where('associate_no', $request->associate_no)->where('associate_status', 1)->where('is_block', 0)->first();
            if ($member) {
                $token = md5($request->associate_no);
                if ($token == $request->token) {
                    $data = array();
                    $plan = Plans::whereHas('company')->when($request->company_id != 0, function ($q) use ($request) {
                        $q->where('company_id', $request->company_id);
                    })->whereNotNull('id')->get();
                    if (count($plan)) {
                        foreach ($plan as $key => $value) {
                            $data[$key]['id'] = $value->id;
                            $data[$key]['name'] = $value->name;
                            $data[$key]['plan_code'] = $value->plan_code;
                        }
                    }
                    $dataBranch = array();
                    $branchData = CompanyBranch::When($request->company_id != 0, function ($q) use ($request) {
                        $q->where('company_id', $request->company_id);
                    })->where('status', 1)->get();
                    if (count($branchData)) {
                        foreach ($branchData as $key => $value) {
                            $dataBranch[$key]['id'] = $value->branch_id;
                            $dataBranch[$key]['branch_code'] = $value->branch->branch_code;
                            $dataBranch[$key]['name'] = $value->branch->name;
                            $dataBranch[$key]['sector'] = $value->branch->sector;
                            $dataBranch[$key]['name_code'] = $value->branch->name . ' - ' . $value->branch->branch_code;
                        }
                    }
                    $dataRelation = array();
                    $relationData = Relations::get();
                    if (count($relationData)) {
                        foreach ($relationData as $key => $value) {
                            $dataRelation[$key]['id'] = $value->id;
                            $dataRelation[$key]['name'] = $value->name;
                        }
                    }
                    $dataSSB = array();
                    $ssb = SavingAccount::whereHas('company')->where('customer_id', $member->id)->first();
                    if ($ssb) {
                        $dataSSB['id'] = $ssb->id;
                        $dataSSB['account_no'] = $ssb->account_no;
                        $dataSSB['balance'] = $ssb->balance;
                        $dataSSB['account_holder_name'] = $member->first_name . ' ' . $member->last_name;
                    }
                    $dataSSB1 = array();
                    $dataSSB1['amount'] = 100;
                    $status = "Success";
                    $code = 200;
                    $messages = 'Plan,Branch,Relations listing & Login user ssb detail- For investments!';
                    $result = ['branch' => $dataBranch, 'plan' => $data, 'relation' => $dataRelation, 'ssb_detail' => $dataSSB, 'ssb_fix_detail' => $dataSSB1];
                    $associate_status = $member->associate_app_status;
                    return response()->json(compact('status', 'code', 'messages', 'result', 'token', 'associate_status'), $code);
                } else {
                    $status = "Error";
                    $code = 201;
                    $messages = 'API token mismatch!';
                    $result = '';
                    $associate_status = $member->associate_app_status;
                    return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                }
            } else {
                $status = "Error";
                $code = 201;
                $messages = 'Something went wrong!';
                $result = '';
                $associate_status = 9;
                return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
            }
        } catch (\Exception $e) {
            $status = "Error";
            $code = 500;
            $messages = $e->getMessage();
            $result = '';
            $associate_status = 9;
            return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
        }
    }
    public function commonListingRenew(Request $request)
    {
        $associate_no = $request->associate_no;
        try {
            $member = Member::select('id', 'associate_app_status', 'first_name', 'last_name')->where('associate_no', $request->associate_no)->where('associate_status', 1)->where('is_block', 0)->first();
            if ($member) {
                $token = md5($request->associate_no);
                if ($token == $request->token) {
                    $data = array();
                    $data[0]['value'] = 0;
                    $data[0]['name'] = 'Daily Renewal';
                    $data[1]['value'] = 1;
                    $data[1]['name'] = 'RD/FRD Renewal';
                    $data[2]['value'] = 2;
                    $data[2]['name'] = 'Deposite Saving Account';
                    $dataBranch = array();
                    $branchData = Branch::where('status', 1)->get();
                    if (count($branchData)) {
                        foreach ($branchData as $key => $value) {
                            $dataBranch[$key]['id'] = $value->id;
                            $dataBranch[$key]['branch_code'] = $value->branch_code;
                            $dataBranch[$key]['name'] = $value->name;
                            $dataBranch[$key]['sector'] = $value->sector;
                            $dataBranch[$key]['name_code'] = $value->name . ' - ' . $value->branch_code;
                        }
                    }
                    $dataSSB = array();
                    $ssb = SavingAccount::whereHas('company')->where('member_id', $member->id)->first();
                    if ($ssb) {
                        $dataSSB['id'] = $ssb->id;
                        $dataSSB['account_no'] = $ssb->account_no;
                        $dataSSB['balance'] = $ssb->balance;
                        $dataSSB['account_holder_name'] = $member->first_name . ' ' . $member->last_name;
                    }
                    $status = "Success";
                    $code = 200;
                    $messages = 'Plan, Branch Listing & Login user ssb detail -- For Renew!';
                    $result = ['branch' => $dataBranch, 'plan' => $data, 'ssb_detail' => $dataSSB];
                    $associate_status = $member->associate_app_status;
                    return response()->json(compact('status', 'code', 'messages', 'result', 'token', 'associate_status'), $code);
                } else {
                    $status = "Error";
                    $code = 201;
                    $messages = 'API token mismatch!';
                    $result = '';
                    $associate_status = $member->associate_app_status;
                    return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                }
            } else {
                $status = "Error";
                $code = 201;
                $messages = 'Something went wrong!';
                $result = '';
                $associate_status = 9;
                return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
            }
        } catch (\Exception $e) {
            $status = "Error";
            $code = 500;
            $messages = $e->getMessage();
            $result = '';
            $associate_status = 9;
            return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
        }
    }
    public function investmentMemberDetail(Request $request)
    {
        $associate_no = $request->associate_no;
        $member_detail1 = array();
        $member_nominees1 = array();
        $dataSSB1 = array();
        $member_detail1['id'] = 0;
        $member_detail1['member_id'] = '';
        $member_detail1['name'] = '';
        $member_detail1['member_id'] = '';
        $member_detail1['address'] = '';
        $member_detail1['mobile_no'] = '';
        $member_detail1['member_id_proof'] = '';
        $member_detail1['category'] = '';
        $member_nominees1['name'] = '';
        $member_nominees1['relation'] = 0;
        $member_nominees1['gender'] = 0;
        $member_nominees1['gender_name'] = '';
        $member_nominees1['dob'] = '';
        $member_nominees1['age'] = 0;
        $dataSSB1['id'] = 0;
        $dataSSB1['account_no'] = '';
        $dataSSB1['balance'] = 0;
        $dataSSB1['account_holder_name'] = '';
        try {
            $member = Member::select('id', 'associate_app_status')->where('associate_no', $request->associate_no)->where('associate_status', 1)->where('is_block', 0)->first();
            if ($member) {
                $token = md5($request->associate_no);
                if ($token == $request->token) {
                    $member_detail = array();
                    $member_nominees = array();
                    if ($request->member_id != '') {
                        $a = $this->getCategoryTree($parent_id = $member->id, $tree_array = array());
                        $b[] = array('member_id' => $member->id, 'status' => 1, 'is_block' => 0);
                        $tree_array1 = array();
                        $c = array_merge($a, $b);
                        foreach ($c as $v) {
                            $tree_array1[] = $v['member_id'];
                        }
                        $db_member_data = Member::where('member_id', $request->member_id)->first();
                        if ($db_member_data) {
                            $member_data = Member::with('memberNominee')->leftJoin('special_categories', 'members.special_category_id', '=', 'special_categories.id')->leftJoin('member_id_proofs', 'members.id', '=', 'member_id_proofs.member_id')->where('members.member_id', $request->member_id)->whereIn('associate_id', $tree_array1)->select('members.id', 'members.member_id', 'members.first_name', 'members.last_name', 'members.mobile_no', 'members.address', 'special_categories.name as special_category', 'member_id_proofs.first_id_no', 'members.status', 'members.is_block')->first();
                            if ($member_data) {
                                // print_r($member_data);die;
                                //  $ssb = SavingAccount::where('member_id',$member->id)->first();
                                // $ssb_detail='';
                                $chk = 0;
                                if ($member_data->status == 0) {
                                    $chk++;
                                    $messages = 'Member Id inactive .Please contact administrator.';
                                }
                                if ($member_data->is_block == 1) {
                                    $chk++;
                                    $messages = 'Member Id  blocked.Please contact administrator.';
                                }
                                $member_detail['id'] = $member_data->id;
                                $member_detail['member_id'] = $member_data->member_id;
                                $member_detail['name'] = $member_data->first_name . ' ' . $member_data->last_name;
                                $member_detail['member_id'] = $member_data->member_id;
                                $member_detail['address'] = $member_data->address;
                                $member_detail['mobile_no'] = $member_data->mobile_no;
                                $member_detail['member_id_proof'] = $member_data->first_id_no;
                                if ($member_data->special_category) {
                                    $member_detail['category'] = $member_data->special_category;
                                } else {
                                    $member_detail['category'] = 'General Category';
                                }
                                $member_nominees['name'] = $member_data['memberNominee'][0]->name;
                                $member_nominees['relation'] = $member_data['memberNominee'][0]->relation;
                                $member_nominees['gender'] = $member_data['memberNominee'][0]->gender;
                                if ($member_data['memberNominee'][0]->gender == 1) {
                                    $g_name = 'Male';
                                } else {
                                    $g_name = 'Female';
                                }
                                $member_nominees['gender_name'] = $g_name;
                                $member_nominees['dob'] = date("d/m/Y", strtotime($member_data['memberNominee'][0]->dob));
                                $member_nominees['age'] = $member_data['memberNominee'][0]->age;
                                $dataSSB = array();
                                $ssb = SavingAccount::whereHas('company')->where('member_id', $member_data->id)->first();
                                if ($ssb) {
                                    $dataSSB['id'] = $ssb->id;
                                    $dataSSB['account_no'] = $ssb->account_no;
                                    $dataSSB['balance'] = $ssb->balance;
                                    $dataSSB['account_holder_name'] = $member_data->first_name . ' ' . $member_data->last_name;
                                } else {
                                    $dataSSB['id'] = 0;
                                    $dataSSB['account_no'] = '';
                                    $dataSSB['balance'] = 0;
                                    $dataSSB['account_holder_name'] = '';
                                }
                                if ($chk > 0) {
                                    $status = "Error";
                                    $code = 201;
                                    $result = ['member_detail' => $member_detail1, 'member_nominees' => $member_nominees1, 'member_ssb_detail' => $dataSSB1];
                                    $associate_status = $member->associate_app_status;
                                    return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                                } else {
                                    $status = "Success";
                                    $code = 200;
                                    $messages = 'Member detail !';
                                    $result = ['member_detail' => $member_detail, 'member_nominees' => $member_nominees, 'member_ssb_detail' => $dataSSB];
                                    $associate_status = $member->associate_app_status;
                                    return response()->json(compact('status', 'code', 'messages', 'result', 'token', 'associate_status'), $code);
                                }
                            } else {
                                $status = "Error";
                                $code = 201;
                                $messages = 'Member id not register by your team ';
                                $result = ['member_detail' => $member_detail1, 'member_nominees' => $member_nominees1, 'member_ssb_detail' => $dataSSB1];
                                $associate_status = $member->associate_app_status;
                                return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                            }
                        } else {
                            $status = "Error";
                            $code = 201;
                            $messages = 'Member id not exist.';
                            $result = ['member_detail' => $member_detail1, 'member_nominees' => $member_nominees1, 'member_ssb_detail' => $dataSSB1];
                            $associate_status = $member->associate_app_status;
                            return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                        }
                    } else {
                        $status = "Error";
                        $code = 201;
                        $messages = 'Please Enter Member Id';
                        $result = ['member_detail' => $member_detail1, 'member_nominees' => $member_nominees1, 'member_ssb_detail' => $dataSSB1];
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                } else {
                    $status = "Error";
                    $code = 201;
                    $messages = 'API token mismatch!';
                    $result = ['member_detail' => $member_detail1, 'member_nominees' => $member_nominees1, 'member_ssb_detail' => $dataSSB1];
                    $associate_status = $member->associate_app_status;
                    return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                }
            } else {
                $status = "Error";
                $code = 201;
                $messages = 'Something went wrong!';
                $result = ['member_detail' => $member_detail1, 'member_nominees' => $member_nominees1, 'member_ssb_detail' => $dataSSB1];
                $associate_status = 9;
                return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
            }
        } catch (\Exception $e) {
            $status = "Error";
            $code = 500;
            $messages = $e->getMessage();
            $result = ['member_detail' => $member_detail1, 'member_nominees' => $member_nominees1, 'member_ssb_detail' => $dataSSB1];
            $associate_status = 9;
            return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
        }
    }
    public function collectorDetail(Request $request)
    {
        $associate_no = $request->associate_no;
        $data1 = array();
        $data1['id'] = 0;
        $data1['associate_no'] = '';
        $data1['name'] = '';
        $data1['mobile_no'] = '';
        $data1['carder'] = '';
        try {
            $member = Member::select('id', 'associate_app_status')->where('associate_no', $request->associate_no)->where('associate_status', 1)->where('is_block', 0)->first();
            if ($member) {
                $token = md5($request->associate_no);
                if ($token == $request->token) {
                    $data = array();
                    if ($request->associate_code != '') {
                        $a = $this->getCategoryTree($parent_id = $member->id, $tree_array = array());
                        $b[] = array('member_id' => $member->id, 'status' => 1, 'is_block' => 0);
                        $tree_array1 = array();
                        $c = array_merge($a, $b);
                        foreach ($c as $v) {
                            $tree_array1[] = $v['member_id'];
                        }
                        $db_member_data = Member::where('associate_no', $request->associate_code)->first();
                        if ($db_member_data) {
                            if ($request->associate_no != $request->associate_code) {
                                $member_data = Member::where('associate_no', $request->associate_code)->whereIn('associate_senior_id', $tree_array1)->first();
                            } else {
                                $member_data = Member::where('associate_no', $request->associate_code)->first();
                            }
                            if ($member_data) {
                                // print_r($member_data);die;
                                //  $ssb = SavingAccount::where('member_id',$member->id)->first();
                                // $ssb_detail='';
                                $chk = 0;
                                if ($member_data->associate_status == 0) {
                                    $chk++;
                                }
                                if ($member_data->is_block == 1) {
                                    $chk++;
                                }
                                $data['id'] = $member_data->id;
                                $data['associate_no'] = $member_data->associate_no;
                                $data['name'] = $member_data->first_name . ' ' . $member_data->last_name;
                                $data['mobile_no'] = $member_data->mobile_no;
                                if ($member_data->associate_no == '9999999') {
                                    $data['carder'] = 'Company';
                                } else {
                                    $data['carder'] = getCarderNameFull($member_data->current_carder_id);
                                }
                                if ($chk > 0) {
                                    $status = "Error";
                                    $code = 201;
                                    $messages = 'Associate code inactive or blocked.Please contact administrator.';
                                    $result = ['associate_detail' => $data1];
                                    $associate_status = $member->associate_app_status;
                                    return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                                } else {
                                    $status = "Success";
                                    $code = 200;
                                    $messages = 'Associate detail !';
                                    $result = ['associate_detail' => $data];
                                    $associate_status = $member->associate_app_status;
                                    return response()->json(compact('status', 'code', 'messages', 'result', 'token', 'associate_status'), $code);
                                }
                            } else {
                                $status = "Error";
                                $code = 201;
                                $messages = 'Associate code not register by your team ';
                                $result = ['associate_detail' => $data1];
                                $associate_status = $member->associate_app_status;
                                return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                            }
                        } else {
                            $status = "Error";
                            $code = 201;
                            $messages = 'Associate code not exist.';
                            $result = ['associate_detail' => $data1];
                            $associate_status = $member->associate_app_status;
                            return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                        }
                    } else {
                        $status = "Error";
                        $code = 201;
                        $messages = 'Please Enter Associate Code';
                        $result = ['associate_detail' => $data1];
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                } else {
                    $status = "Error";
                    $code = 201;
                    $messages = 'API token mismatch!';
                    $result = ['associate_detail' => $data1];
                    $associate_status = $member->associate_app_status;
                    return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                }
            } else {
                $status = "Error";
                $code = 201;
                $messages = 'Something went wrong!';
                $result = ['associate_detail' => $data1];
                $associate_status = 9;
                return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
            }
        } catch (\Exception $e) {
            $status = "Error";
            $code = 500;
            $messages = $e->getMessage();
            $result = ['associate_detail' => $data1];
            $associate_status = 9;
            return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
        }
    }
    public function renewAccountDetail(Request $request)
    {
        $associate_no = $request->associate_no;
        $invest_detail1 = array();
        $invest_detail1['id'] = 0;
        $invest_detail1['account_number'] = '';
        $invest_detail1['member_auto_id'] = 0;
        $invest_detail1['member_name'] = '';
        $invest_detail1['associate_auto_id'] = 0;
        $invest_detail1['associate_name'] = '';
        $invest_detail1['amount'] = 0;
        $invest_detail1['due_amount'] = 0;
        $invest_detail1['deposite_amount'] = 0;
        try {
            $member = Member::select('id', 'associate_app_status')->where('associate_no', $request->associate_no)->where('associate_status', 1)->where('is_block', 0)->first();
            if ($member) {
                $token = md5($request->associate_no);
                if ($token == $request->token) {
                    $data = array();
                    if ($request->plan_id == '' && $request->account_no == '') {
                        $status = "Error";
                        $code = 201;
                        $messages = 'Please select plan or enter account number';
                        $result = ['account_detail' => $invest_detail1];
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    } elseif ($request->plan_id != '' && $request->account_no == '') {
                        $status = "Error";
                        $code = 201;
                        $messages = 'Please enter account number';
                        $result = ['account_detail' => $invest_detail1];
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    } elseif ($request->plan_id == '' && $request->account_no != '') {
                        $status = "Error";
                        $code = 201;
                        $messages = 'Please select plan';
                        $result = ['account_detail' => $invest_detail1];
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    } elseif ($request->plan_id != '' && $request->account_no != '') {
                        $invest_detail = array();
                        $a = $this->getCategoryTree($parent_id = $member->id, $tree_array = array());
                        $b[] = array('member_id' => $member->id, 'status' => 1, 'is_block' => 0);
                        $tree_array1 = array();
                        $c = array_merge($a, $b);
                        foreach ($c as $v) {
                            $tree_array1[] = $v['member_id'];
                        }
                        $db_member_data = Memberinvestments::whereHas('company')->where('account_number', $request->account_no)->first();
                        //$member_data = Member::with('branch')->whereIn('associate_id',$tree_array1)->get();
                        $memberArray = Member::whereIn('associate_id', $tree_array1)->get('id');
                        $memberArray1 = array();
                        foreach ($memberArray as $v) {
                            $memberArray1[] = $v->id;
                        }
                        //print_r($memberArray1);die;
                        $p = '';
                        if ($request->plan_id == 0) {
                            $p = 7;
                        }
                        if ($request->plan_id == 2) {
                            $p = 1;
                        }
                        $chk1 = 1;
                        if ($db_member_data) {
                            if ($db_member_data->plan_id == $p) {
                                $chk1 = 0;
                            } elseif ($db_member_data->plan_id == $p) {
                                $chk1 = 0;
                            } else {
                                if ($db_member_data->plan_id == 2 || $db_member_data->plan_id == 3 || $db_member_data->plan_id == 5 || $db_member_data->plan_id == 6 || $db_member_data->plan_id == 10 || $db_member_data->plan_id == 11) {
                                    $chk1 = 0;
                                } else {
                                    $chk1++;
                                }
                            }
                            if ($chk1 > 0) {
                                $status = "Error";
                                $code = 201;
                                $result = ['account_detail' => $invest_detail1];
                                $messages = 'Enter valid account number.';
                                $associate_status = $member->associate_app_status;
                                return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                            } else {
                                if ($request->plan_id == 0) {
                                    $invest_data = Memberinvestments::whereHas('company')->with('member', 'associateMember')->where('plan_id', 7)->where('account_number', $request->account_no)->whereIn('member_id', $memberArray1)->first();
                                } elseif ($request->plan_id == 2) {
                                    $invest_data = Memberinvestments::whereHas('company')->with('member', 'associateMember', 'ssb')->where('plan_id', 1)->where('account_number', $request->account_no)->whereIn('member_id', $memberArray1)->first();
                                } else {
                                    $invest_data = Memberinvestments::whereHas('company')->with('member', 'associateMember')->whereIn('plan_id', [2, 3, 5, 6, 10, 11])->where('account_number', $request->account_no)->whereIn('member_id', $memberArray1)->first();
                                }
                                if ($invest_data) {
                                    // print_r($member_data);die;
                                    //  $ssb = SavingAccount::where('member_id',$member->id)->first();
                                    // $ssb_detail='';
                                    $chk = 0;
                                    if ($invest_data->is_mature == 0) {
                                        $chk++;
                                        $messages = 'Account number already matured.';
                                    }
                                    if ($invest_data->investment_correction_request == 1) {
                                        $chk++;
                                        $messages = 'Account number temporarily not active contact to administrator.';
                                    }
                                    if ($invest_data->renewal_correction_request == 1) {
                                        $chk++;
                                        $messages = 'Account number temporarily not active contact to administrator.';
                                    }
                                    $investmentLastDate = Investmentplantransactions::select('deposite_date', 'deposite_month')->where('investment_id', $invest_data->id)->orderBy('id', 'desc')->first();
                                    if ($investmentLastDate && $invest_data->plan_id == 7) {
                                        $start_date = strtotime($investmentLastDate->deposite_date);
                                        $end_date = strtotime(date('Y-m-d'));
                                        $daysDiff = ($end_date - $start_date) / 60 / 60 / 24;
                                        if ($daysDiff > 0) {
                                            if ($invest_data->due_amount >= 0) {
                                                $amount = ($invest_data->deposite_amount * $daysDiff) + $invest_data->due_amount;
                                            } elseif ($invest_data->due_amount < 0) {
                                                $amount = $invest_data->due_amount;
                                            }
                                        } else {
                                            $amount = $invest_data->due_amount;
                                        }
                                    } else if ($investmentLastDate && $invest_data->plan_id == 1) {
                                        $amount = $invest_data->deposite_amount;
                                    } else if ($investmentLastDate) {
                                        $lMonth = $investmentLastDate->deposite_month;
                                        $cMonth = date('m');
                                        $daysDiff = ($cMonth - $lMonth);
                                        if ($daysDiff > 0) {
                                            if ($invest_data->due_amount >= 0) {
                                                $amount = ($invest_data->deposite_amount * $daysDiff) + $invest_data->due_amount;
                                            } elseif ($invest_data->due_amount < 0) {
                                                $amount = $invest_data->due_amount;
                                            }
                                        } else {
                                            $amount = $invest_data->due_amount;
                                        }
                                    } else {
                                        $amount = $invest_data->deposite_amount;
                                    }
                                    $invest_detail['id'] = $invest_data->id;
                                    if ($invest_data->plan_id == 1) {
                                        $invest_detail['account_number'] = $invest_data['ssb']->account_no;
                                    } else {
                                        $invest_detail['account_number'] = $invest_data->account_number;
                                    }
                                    $invest_detail['member_auto_id'] = $invest_data['member']->id;
                                    $invest_detail['member_name'] = $invest_data['member']->first_name . ' ' . $invest_data['member']->last_name;
                                    $invest_detail['associate_auto_id'] = $invest_data['associateMember']->id;
                                    $invest_detail['associate_name'] = $invest_data['associateMember']->first_name . ' ' . $invest_data['associateMember']->last_name;
                                    $invest_detail['amount'] = $amount;
                                    $invest_detail['due_amount'] = $invest_data->due_amount;
                                    $invest_detail['deposite_amount'] = $invest_data->deposite_amount;
                                    if ($chk > 0) {
                                        $status = "Error";
                                        $code = 201;
                                        $result = ['account_detail' => $invest_detail1];
                                        $associate_status = $member->associate_app_status;
                                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                                    } else {
                                        $status = "Success";
                                        $code = 200;
                                        $messages = 'Invesment detail !';
                                        $result = ['account_detail' => $invest_detail];
                                        $associate_status = $member->associate_app_status;
                                        return response()->json(compact('status', 'code', 'messages', 'result', 'token', 'associate_status'), $code);
                                    }
                                } else {
                                    $status = "Error";
                                    $code = 201;
                                    $messages = 'Account holder  not register by your team ';
                                    $result = ['account_detail' => $invest_detail1];
                                    $associate_status = $member->associate_app_status;
                                    return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                                }
                            }
                        } else {
                            $status = "Error";
                            $code = 201;
                            $messages = 'Account number not exist.';
                            $result = ['account_detail' => $invest_detail1];
                            $associate_status = $member->associate_app_status;
                            return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                        }
                    }
                } else {
                    $status = "Error";
                    $code = 201;
                    $messages = 'API token mismatch!';
                    $result = ['account_detail' => $invest_detail1];
                    $associate_status = $member->associate_app_status;
                    return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                }
            } else {
                $status = "Error";
                $code = 201;
                $messages = 'Something went wrong!';
                $result = ['account_detail' => $invest_detail1];
                $associate_status = 9;
                return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
            }
        } catch (\Exception $e) {
            $status = "Error";
            $code = 500;
            $messages = $e->getMessage();
            $result = ['account_detail' => $invest_detail1];
            $associate_status = 9;
            return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
        }
    }
    public function dailyDepositMaturity($tenure, $amount, $specialCategory)
    {
        $time = $tenure;
        if ($time >= 0 && $time <= 12) {
            $rate = 6;
        } else if ($time >= 13 && $time <= 24) {
            $rate = 6.50;
        } else if ($time >= 25 && $time <= 36) {
            $rate = 7;
        } else if ($time >= 37 && $time <= 60) {
            $rate = 7.25;
        }
        $principal = $amount;
        $ci = 12;
        $freq = 12;
        $irate = $rate / $ci;
        $year = $time / 12;
        $days = $time * 30;
        $monthlyPricipal = $principal * 30;
        $maturity = 0;
        for ($i = 1; $i <= $time; $i++) {
            $maturity += $monthlyPricipal * pow((1 + (($rate / 100) / $freq)), $freq * (($time - $i + 1) / 12));
        }
        $return_array = ['amount' => round($maturity), 'interest' => $rate];
        return $return_array;
    }
    public function ffdMaturity($tenure, $amount, $specialCategory)
    {
        $principal = $amount;
        $time = $tenure;
        if ($time >= 0 && $time <= 36) {
            $rate = 8;
        } else if ($time >= 37 && $time <= 48) {
            $rate = 8.25;
        } else if ($time >= 49 && $time <= 60) {
            $rate = 8.50;
        } else if ($time >= 61 && $time <= 72) {
            $rate = 8.75;
        } else if ($time >= 73 && $time <= 84) {
            $rate = 9;
        } else if ($time >= 85 && $time <= 96) {
            $rate = 9.50;
        } else if ($time >= 97 && $time <= 108) {
            $rate = 10;
        } else if ($time >= 109 && $time <= 120) {
            $rate = 11;
        }
        $ci = 1;
        $irate = $rate / $ci;
        $year = $time / 12;
        $result = ($principal * (pow((1 + $irate / 100), $year)));
        $return_array = ['amount' => round($result), 'interest' => $rate];
        return $return_array;
    }
    public function frdMaturity($tenure, $amount, $specialCategory)
    {
        $principal = $amount;
        $time = $tenure;
        if ($time >= 0 && $time <= 12) {
            $rate = 5;
        } else if ($time >= 13 && $time <= 24) {
            $rate = 6;
        } else if ($time >= 25 && $time <= 36) {
            $rate = 6.50;
        } else if ($time >= 37 && $time <= 48) {
            $rate = 7;
        } else if ($time >= 49 && $time <= 60) {
            $rate = 9;
        }
        $ci = 1;
        $irate = $rate / $ci;
        $year = $time / 12;
        //  $result = ( principal*(Math.pow((1 + irate / 100), year*ci) - 1) / (1 - Math.pow((1 + irate / 100), -ci / 12))).toFixed(2);
        $freq = 4;
        $maturity = 0;
        for ($i = 1; $i <= $time; $i++) {
            $maturity += $principal * pow((1 + (($rate / 100) / $freq)), $freq * (($time - $i + 1) / 12));
        }
        $result = $maturity;
        $return_array = ['amount' => round($result), 'interest' => $rate];
        return $return_array;
    }
    public function misMaturity($tenure, $amount, $specialCategory)
    {
        $time = $tenure;
        $principal = $amount;
        if ($time >= 0 && $time <= 60) {
            $rate = 10;
        } else if ($time >= 61 && $time <= 84) {
            $rate = 10.50;
        } else if ($time >= 85 && $time <= 120) {
            $rate = 11;
        }
        $ci = 1;
        $irate = $rate / $ci;
        $year = $time / 12;
        $result = ((($principal * $rate) / 12) / 100);
        $return_array = ['amount' => round($result), 'interest' => $rate];
        return $return_array;
    }
    public function fdMaturity($tenure, $amount, $specialCategory)
    {
        $time = $tenure;
        $principal = $amount;
        $specialCategory = $specialCategory;
        if ($time >= 0 && $time <= 18) {
            $rate = 9;
        } else if ($time >= 19 && $time <= 48) {
            if ($specialCategory == '') {
                $rate = 10;
            } else {
                $rate = 10.25;
            }
        } else if ($time >= 49 && $time <= 60) {
            if ($specialCategory == '') {
                $rate = 10.25;
            } else {
                $rate = 10.50;
            }
        } else if ($time >= 61 && $time <= 72) {
            if ($specialCategory == '') {
                $rate = 10.50;
            } else {
                $rate = 10.75;
            }
        } else if ($time >= 73 && $time <= 96) {
            if ($specialCategory == '') {
                $rate = 10.75;
            } else {
                $rate = 11;
            }
        } else if ($time >= 97 && $time <= 120) {
            if ($specialCategory == '') {
                $rate = 11;
            } else {
                $rate = 11.25;
            }
        }
        $ci = 1;
        $irate = $rate / $ci;
        $year = $time / 12;
        $result = ($principal * (pow((1 + $irate / 100), $year)));
        $return_array = ['amount' => round($result), 'interest' => $rate];
        return $return_array;
    }
    public function rdMaturity($tenure, $amount, $specialCategory)
    {
        $time = $tenure;
        $principal = $amount;
        $specialCategory = $specialCategory;
        if ($time >= 0 && $time <= 36) {
            if ($specialCategory == '') {
                $rate = 8;
            } else {
                $rate = 8.50;
            }
        } else if ($time >= 37 && $time <= 60) {
            if ($specialCategory == '') {
                $rate = 9;
            } else {
                $rate = 9.50;
            }
        } else if ($time >= 61 && $time <= 84) {
            if ($specialCategory == '') {
                $rate = 10;
            } else {
                $rate = 10.50;
            }
        }
        $ci = 1;
        $irate = $rate / $ci;
        $year = $time / 12;
        $freq = 4;
        $maturity = 0;
        for ($i = 1; $i <= $time; $i++) {
            $maturity += $principal * pow((1 + (($rate / 100) / $freq)), $freq * (($time - $i + 1) / 12));
        }
        $return_array = ['amount' => round($maturity), 'interest' => $rate];
        return $return_array;
    }
    public function tenurePlan(Request $request)
    {
        $associate_no = $request->associate_no;
        try {
            $member = Member::select('id', 'associate_app_status')->where('associate_no', $request->associate_no)->where('associate_status', 1)->where('is_block', 0)->first();
            if ($member) {
                $token = md5($request->associate_no);
                if ($token == $request->token) {
                    $data = array();
                    if ($request->plan_code == '') {
                        $status = "Error";
                        $code = 201;
                        $messages = 'Select plan';
                        $result = ['tenure' => $data, 'total_count' => count($data)];
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    } else {
                        $plan_code = $request->plan_code;
                        if ($plan_code == 704) {
                            $data[0]['value'] = 36;
                            $data[0]['name'] = '36  Months';
                            $data[1]['value'] = 60;
                            $data[1]['name'] = '60  Months';
                            $data[2]['value'] = 84;
                            $data[2]['name'] = '84  Months';
                        }
                        if ($plan_code == 706) {
                            $data[0]['value'] = 12;
                            $data[0]['name'] = '12  Months';
                            $data[1]['value'] = 24;
                            $data[1]['name'] = '24  Months';
                            $data[2]['value'] = 36;
                            $data[2]['name'] = '36  Months';
                            $data[3]['value'] = 48;
                            $data[3]['name'] = '48  Months';
                            $data[4]['value'] = 60;
                            $data[4]['name'] = '60  Months';
                            $data[5]['value'] = 72;
                            $data[5]['name'] = '72  Months';
                            $data[6]['value'] = 84;
                            $data[6]['name'] = '84  Months';
                            $data[7]['value'] = 96;
                            $data[7]['name'] = '96  Months';
                            $data[8]['value'] = 108;
                            $data[8]['name'] = '108  Months';
                            $data[9]['value'] = 120;
                            $data[9]['name'] = '120  Months';
                        }
                        if ($plan_code == 712) {
                            $data[0]['value'] = 60;
                            $data[0]['name'] = '60  Months';
                            $data[1]['value'] = 84;
                            $data[1]['name'] = '84  Months';
                            $data[2]['value'] = 120;
                            $data[2]['name'] = '120  Months';
                        }
                        if ($plan_code == 710) {
                            $data[0]['value'] = 12;
                            $data[0]['name'] = '12  Months';
                            $data[1]['value'] = 24;
                            $data[1]['name'] = '24  Months';
                            $data[2]['value'] = 36;
                            $data[2]['name'] = '36  Months';
                            $data[3]['value'] = 60;
                            $data[3]['name'] = '60  Months';
                        }
                        if ($plan_code == 707) {
                            $data[0]['value'] = 60;
                            $data[0]['name'] = '60  Months';
                        }
                        if ($plan_code == 705) {
                            $data[0]['value'] = 12;
                            $data[0]['name'] = '12  Months';
                            $data[1]['value'] = 24;
                            $data[1]['name'] = '24  Months';
                            $data[2]['value'] = 36;
                            $data[2]['name'] = '36  Months';
                            $data[3]['value'] = 48;
                            $data[3]['name'] = '48  Months';
                            $data[4]['value'] = 60;
                            $data[4]['name'] = '60  Months';
                            $data[5]['value'] = 72;
                            $data[5]['name'] = '72  Months';
                            $data[6]['value'] = 84;
                            $data[6]['name'] = '84  Months';
                            $data[7]['value'] = 96;
                            $data[7]['name'] = '96  Months';
                            $data[8]['value'] = 108;
                            $data[8]['name'] = '108  Months';
                            $data[9]['value'] = 120;
                            $data[9]['name'] = '120  Months';
                        }
                        $status = "Success";
                        $code = 200;
                        $messages = 'Tenure listing!';
                        $result = ['tenure' => $data, 'total_count' => count($data)];
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'token', 'associate_status'), $code);
                    }
                } else {
                    $status = "Error";
                    $code = 201;
                    $messages = 'API token mismatch!';
                    $result = '';
                    $associate_status = $member->associate_app_status;
                    return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                }
            } else {
                $status = "Error";
                $code = 201;
                $messages = 'Something went wrong!';
                $result = '';
                $associate_status = 9;
                return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
            }
        } catch (\Exception $e) {
            $status = "Error";
            $code = 500;
            $messages = $e->getMessage();
            $result = '';
            $associate_status = 9;
            return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
        }
    }
    public function planMaturity(Request $request)
    {
        $associate_no = $request->associate_no;
        $data = array();
        $data['maturity_amount'] = 0;
        $data['interest_rate'] = 0;
        try {
            $member = Member::select('id', 'associate_app_status')->where('associate_no', $request->associate_no)->where('associate_status', 1)->where('is_block', 0)->first();
            if ($member) {
                $token = md5($request->associate_no);
                if ($token == $request->token) {
                    if ($request->plan_code == '' && $request->tenure == '' && $request->amount == '') {
                        $status = "Error";
                        $code = 201;
                        $messages = 'Please select plan ,tenure and enter amount';
                        $result = ['maturity_detail' => $data];
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    } elseif ($request->plan_code != '' && $request->tenure != '' && $request->amount == '') {
                        $status = "Error";
                        $code = 201;
                        $messages = 'Please enter amount';
                        $result = ['maturity_detail' => $data];
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    } elseif ($request->plan_code == '' && $request->tenure != '' && $request->amount != '') {
                        $status = "Error";
                        $code = 201;
                        $messages = 'Please select  plan';
                        $result = ['maturity_detail' => $data];
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    } elseif ($request->plan_code != '' && $request->tenure == '' && $request->amount != '') {
                        $status = "Error";
                        $code = 201;
                        $messages = 'Please select  tenure';
                        $result = ['maturity_detail' => $data];
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    } else {
                        $plan_code = $request->plan_code;
                        $tenure = $request->tenure;
                        $category = $request->category;
                        $amount = $request->amount;
                        if ($plan_code == 704) {
                            $getData = $this->rdMaturity($tenure, $amount, $category);
                            // print_r($getData['amount'])  ;die;                             
                        } elseif ($plan_code == 706) {
                            $getData = $this->fdMaturity($tenure, $amount, $category);
                        } elseif ($plan_code == 712) {
                            $getData = $this->misMaturity($tenure, $amount, $category);
                        } elseif ($plan_code == 710) {
                            $getData = $this->dailyDepositMaturity($tenure, $amount, $category);
                        } elseif ($plan_code == 707) {
                            $getData = $this->frdMaturity($tenure, $amount, $category);
                        } elseif ($plan_code == 705) {
                            $getData = $this->ffdMaturity($tenure, $amount, $category);
                        } else {
                            $getData = '';
                        }
                        if ($getData) {
                            $data['maturity_amount'] = $getData['amount'];
                            $data['interest_rate'] = $getData['interest'];
                        }
                        $status = "Success";
                        $code = 200;
                        $messages = 'Maturity Detail!';
                        $result = ['maturity_detail' => $data];
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'token', 'associate_status'), $code);
                    }
                } else {
                    $status = "Error";
                    $code = 201;
                    $messages = 'API token mismatch!';
                    $result = ['maturity_detail' => $data];
                    $associate_status = $member->associate_app_status;
                    return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                }
            } else {
                $status = "Error";
                $code = 201;
                $messages = 'Something went wrong!';
                $result = ['maturity_detail' => $data];
                $associate_status = 9;
                return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
            }
        } catch (\Exception $e) {
            $status = "Error";
            $code = 500;
            $messages = $e->getMessage();
            $result = ['maturity_detail' => $data];
            $associate_status = 9;
            return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
        }
    }
    public function renewSubmit(Request $request)
    {
        $associate_no = $request->associate_no;
        $messages1 = array();
        $messages1['branch_id'] = '';
        $messages1['plan_id'] = '';
        $messages1['collector_associate_code'] = '';
        $messages1['login_ssb_account_no'] = '';
        $messages1['payment_mode'] = '';
        $messages1['account_info'] = '';
        try {
            $member = Member::select('id', 'associate_app_status', 'first_name', 'last_name')->where('associate_no', $request->associate_no)->where('associate_status', 1)->where('is_block', 0)->first();
            if ($member) {
                $token = md5($request->associate_no);
                if ($token == $request->token) {
                    $data = array();
                    $chk = 0;
                    $a = $this->getCategoryTree($parent_id = $member->id, $tree_array = array());
                    $b[] = array('member_id' => $member->id, 'status' => 1, 'is_block' => 0);
                    $tree_array1 = array();
                    $c = array_merge($a, $b);
                    foreach ($c as $v) {
                        $tree_array1[] = $v['member_id'];
                    }
                    $array_account = json_decode($request->account_info);
                    /* if($request->branch_id=='' && $request->branch_id>0)
                    {
                        $messages1['branch_id']= 'Please select branch.';
                        $chk++;
                    }*/
                    if ($request->plan_id == '') {
                        $messages1['plan_id'] = 'Please select plan.';
                        $chk++;
                    }
                    if ($request->collector_associate_code == '') {
                        $messages1['collector_associate_code'] = 'Please enter collector code.';
                        $chk++;
                    }
                    if ($request->login_ssb_account_no == '') {
                        $messages1['login_ssb_account_no'] = 'Please enter collector code.';
                        $chk++;
                    }
                    if ($request->payment_mode == '') {
                        $messages1['payment_mode'] = 'Please select payment_mode.';
                        $chk++;
                    } else {
                        if ($request->payment_mode != 4) {
                            $messages1['payment_mode'] = 'Only Ssb payment mode available.';
                            $chk++;
                        }
                    }
                    if (empty($array_account)) {
                        $messages1['account_info'] = 'Please enter account info detail.';
                        $chk++;
                    }
                    $db_member_data = Member::where('associate_no', $request->collector_associate_code)->first();
                    if ($db_member_data) {
                        if ($request->associate_no != $request->collector_associate_code) {
                            $member_data = Member::where('associate_no', $request->collector_associate_code)->whereIn('associate_senior_id', $tree_array1)->first();
                        } else {
                            $member_data = Member::where('associate_no', $request->collector_associate_code)->first();
                        }
                        if ($member_data) {
                            $collector_id = $member_data->id;
                        } else {
                            $messages1['collector_associate_code'] = 'Associate code not register by your team.';
                            $chk++;
                        }
                    } else {
                        $messages1['collector_associate_code'] = 'Associate code not exist.';
                        $chk++;
                    }
                    $member_ssb_id = '';
                    $member_ssb_balance = '';
                    $a = array();
                    $a = json_decode($request->account_info);
                    $a_total = 0;
                    if (count($a)) {
                        foreach ($a as $value) {
                            $a_total = $a_total + $value->amount;
                        }
                    }
                    $total_amount = $a_total;
                    $member_ssb = SavingAccount::where('member_id', $member->id)->first();
                    if ($member_ssb) {
                        $member_ssb_id = $member_ssb->id;
                        $member_ssb_balance = $member_ssb->balance;
                    } else {
                        $messages1['login_ssb_account_no'] = 'SSB account number not exist.';
                        $chk++;
                    }
                    if ($total_amount > $member_ssb_balance) {
                        $messages1['login_ssb_account_no'] = 'Sufficient amount not available in your SSB account';
                        $chk++;
                    }
                    if ($chk > 0) {
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $error_filed = $messages1;
                        $messages = 'All field required.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status', 'error_filed'), $code);
                    } else {
                        $invest_plan_id = $request->plan_id;
                        $invest_collector_associate_id = $collector_id;
                        $invest_ssb_id = $member_ssb_id;
                        $invest_paymentmode = 4; // ssb
                        $memberArray = Member::whereIn('associate_id', $tree_array1)->get('id');
                        $memberArray1 = array();
                        $chk_create = 0;
                        foreach ($memberArray as $v) {
                            $memberArray1[] = $v->id;
                        }
                        if ($invest_plan_id == 2) { //ssb renew
                            $received_cheque_id = $cheque_id = NULL;
                            $cheque_deposit_bank_id = NULL;
                            $cheque_deposit_bank_ac_id = NULL;
                            $cheque_no = NULL;
                            $cheque_date = $pdate = NULL;
                            $online_deposit_bank_id = NULL;
                            $online_deposit_bank_ac_id = NULL;
                            $online_transction_no = NULL;
                            $online_transction_date = NULL;
                            if (count($array_account) > 0) {
                                foreach ($array_account as $val_info) {
                                    $db_member_data = SavingAccount::where('account_no', $val_info->account)->first();
                                    if ($db_member_data) {
                                        $invest_data = $savingAccountDetail = SavingAccount::where('account_no', $val_info->account)->whereIn('member_id', $memberArray1)->first();
                                        if ($invest_data) {
                                            $invest_branch_id = $invest_data->branch_id;
                                            $getState = Branch::where('id', $invest_branch_id)->first();
                                            $stateid = $getState->state_id;
                                            $globaldate = checkMonthAvailability(date('d'), date('m'), date('Y'), $stateid);
                                            Session::put('created_at', $globaldate);
                                            $getState = Branch::where('id', $invest_branch_id)->first();
                                            $stateid = $getState->state_id;
                                            $globaldate = checkMonthAvailability(date('d'), date('m'), date('Y'), $stateid);
                                            Session::put('created_at', $globaldate);
                                            $branchCode = $getState->branch_code;
                                            $chk1 = 0;
                                            $accountNumber = $invest_data->account_no;
                                            $invest_data1 = Memberinvestments::whereIn('plan_id', [1])->where('account_number', $val_info->account)->first();
                                            if ($invest_data1->investment_correction_request == 1) {
                                                $chk1++;
                                                $messages1['account_no'] = 'Account number temporarily not active contact to administrator.';
                                            }
                                            if ($invest_data1->renewal_correction_request == 1) {
                                                $chk1++;
                                                $messages1['account_no'] = 'Account number temporarily not active contact to administrator.';
                                            }
                                            if ($chk1 == 0) {
                                                $amountArraySsb = array('1' => $val_info->amount);
                                                $renewSavingOpeningBlanace = $invest_data->balance;
                                                //---- ssb acount amount debit  --------
                                                $sAccount = SavingAccount::where('id', $member_ssb_id)->first();
                                                $mtssb = 'Amount deposit by ' . $sAccount->account_no;
                                                $rno = $sAccount->account_no;
                                                $ssbAccountAmount = $sAccount->balance - $val_info->amount;
                                                $ssb_id = $collectionSSBId = $sAccount->id;
                                                $sResult = SavingAccount::find($ssb_id);
                                                $sData['balance'] = $ssbAccountAmount;
                                                $sResult->update($sData);
                                                $ssb['saving_account_id'] = $ssb_id;
                                                $ssb['account_no'] = $sAccount->account_no;
                                                $ssb['opening_balance'] = $ssbAccountAmount;
                                                $ssb['withdrawal'] = $val_info->amount;
                                                if ($invest_branch_id != $invest_data->branch_id) {
                                                    $branchName = getBranchName($invest_branch_id);
                                                    $ssb['description'] = 'Fund Trf. To ' . $savingAccountDetail->account_no . '- From ' . $branchName . '';
                                                } else {
                                                    $ssb['description'] = 'Fund Trf. To ' . $savingAccountDetail->account_no;
                                                }
                                                $ssb['associate_id'] = $collector_id;
                                                $ssb['branch_id'] = $invest_branch_id;
                                                $ssb['type'] = 6;
                                                $ssb['currency_code'] = 'INR';
                                                $ssb['payment_type'] = 'DR';
                                                $ssb['payment_mode'] = 3;
                                                $ssb['deposit'] = NULL;
                                                $ssb['is_renewal'] = 0;
                                                $ssb['app_login_user_id'] = $member->id;
                                                $ssb['is_app'] = 1;
                                                $ssb['created_at'] = date("Y-m-d H:i:s", strtotime(str_replace('/', '-', $globaldate)));
                                                $ssbAccountTran1 = SavingAccountTranscation::create($ssb);
                                                $ssb_tran_id_from = $ssbAccountTran1->id;
                                                $satRefId = CommanTransactionsController::createTransactionReferences($ssbAccountTran1->id, $ssb_id);
                                                $createTransaction = CommanTransactionsController::createTransactionApp($satRefId, 5, 0, $collectionSSBId, $invest_branch_id, $branchCode, $amountArraySsb, 4, $member->first_name . ' ' . $member->last_name, $member->id, $sAccount->account_no, 0, NULL, NULL, date("Y-m-d", strtotime(str_replace('/', '-', $globaldate))), NULL, $online_payment_by = NULL, $ssb_id, 'DR', $member->id);
                                                $transactionData['is_renewal'] = 0;
                                                $transactionData['created_at'] = date("Y-m-d H:i:s", strtotime(str_replace('/', '-', $globaldate)));
                                                $updateTransaction = Transcation::find($createTransaction);
                                                $updateTransaction->update($transactionData);
                                                TranscationLog::where('transaction_id', $transactionData)->update(['is_renewal' => 0, 'created_at' => date("Y-m-d H:i:s", strtotime(str_replace('/', '-', $globaldate)))]);
                                                ///---------------------------------------------
                                                $ssbAccountAmount = $totalbalance = $renewSavingOpeningBlanace + $val_info->amount;
                                                $ssb_id = $depositSSBId = $invest_data->id;
                                                $sResult = SavingAccount::find($depositSSBId);
                                                $sData['balance'] = $ssbAccountAmount;
                                                $sResult->update($sData);
                                                $ssb1['saving_account_id'] = $depositSSBId;
                                                $ssb1['account_no'] = $accountNumber;
                                                $ssb1['opening_balance'] = $renewSavingOpeningBlanace + $val_info->amount;
                                                $ssb1['withdrawal'] = 0;
                                                if ($invest_branch_id != $invest_data->branch_id) {
                                                    $branchName = getBranchName($invest_branch_id);
                                                    $ssb1['description'] = $mtssb . ' - From ' . $branchName . '';
                                                } else {
                                                    $ssb1['description'] = $mtssb;
                                                }
                                                $ssb1['associate_id'] = $collector_id;
                                                $ssb1['branch_id'] = $invest_branch_id;
                                                $ssb1['type'] = 2;
                                                $ssb1['currency_code'] = 'INR';
                                                $ssb1['payment_type'] = 'CR';
                                                $ssb1['payment_mode'] = 4;
                                                $ssb1['reference_no'] = $rno;
                                                $ssb1['deposit'] = $val_info->amount;
                                                $ssb1['created_at'] = $globaldate;
                                                $ssb1['app_login_user_id'] = $member->id;
                                                $ssb1['is_app'] = 1;
                                                $ssbAccountTran = SavingAccountTranscation::create($ssb1);
                                                $satRefId = CommanTransactionsController::createTransactionReferences($ssbAccountTran->id, $invest_data1->id);
                                                $createTransaction = CommanTransactionsController::createTransactionApp($satRefId, 5, 0, $invest_data->id, $invest_branch_id, $branchCode, $amountArraySsb, 4, $member->first_name . ' ' . $member->last_name, $member->id, $accountNumber, NULL, NULL, $request['branch-name'], date("Y-m-d", strtotime(str_replace('/', '-', $globaldate))), NULL, $online_payment_by = NULL, $ssb_id, 'CR', $member->id);
                                                $transactionData['is_renewal'] = 0;
                                                $updateTransaction = Transcation::find($createTransaction);
                                                $updateTransaction->update($transactionData);
                                                TranscationLog::where('transaction_id', $transactionData)->update(['is_renewal' => 0, 'created_at' => date("Y-m-d", strtotime(str_replace('/', '-', $globaldate)))]);
                                                // ---------------------------  Day book modify --------------------------
                                                $createDayBook = CommanTransactionsController::createDayBookNewApp($createTransaction, $satRefId, 2, $invest_data->id, $collector_id, $invest_data->member_id, $totalbalance, $val_info->amount, $withdrawal = 0, $ssb1['description'], $accountNumber, $invest_branch_id, $branchCode, $amountArraySsb, 4, $member->first_name . ' ' . $member->last_name, $member->id, $accountNumber, NULL, NULL, NULL, $globaldate, NULL, $online_payment_by = NULL, $collectionSSBId, 'CR', $received_cheque_id, $cheque_deposit_bank_id, $cheque_deposit_bank_ac_id, $online_deposit_bank_id, $online_deposit_bank_ac_id, $member->id);
                                                // ---------------------------  HEAD IMPLEMENT --------------------------
                                                $planId = $invest_data1->plan_id;
                                                $pmodeAll = 3;
                                                $this->investHeadCreateSSB($val_info->amount, $globaldate, $invest_data->id, $planId, $cheque_no, $cheque_id, $cheque_date, $online_transction_no, $online_transction_date, $online_deposit_bank_id, $online_deposit_bank_ac_id, $invest_branch_id, $collector_id, $invest_data->member_id, $collectionSSBId, $ssbAccountTran->id, $pmodeAll, $invest_data->account_no, $member->id, $ssb_tran_id_from);
                                                //------------------HEAD IMPLEMENT ------- ---------  ------
                                                $daybookData['is_renewal'] = 0;
                                                $daybookData['app_login_user_id'] = $member->id;
                                                $daybookData['is_app'] = 1;
                                                $daybookData['created_at'] = date("Y-m-d H:i:s", strtotime(str_replace('/', '-', $globaldate)));
                                                $dayBook = Daybook::find($createDayBook);
                                                $dayBook->update($daybookData);
                                                $transaction['investment_id'] = $invest_data1->id;
                                                $transaction['plan_id'] = $invest_data1->plan_id;
                                                $transaction['member_id'] = $invest_data->member_id;
                                                $transaction['branch_id'] = $invest_branch_id;
                                                $transaction['branch_code'] = $branchCode;
                                                $transaction['deposite_amount'] = $val_info->amount;
                                                $transaction['deposite_date'] = $globaldate;
                                                $transaction['deposite_month'] = date("m", strtotime(str_replace('/', '-', $globaldate)));
                                                $transaction['payment_mode'] = 4;
                                                $transaction['saving_account_id'] = $ssb_id;
                                                $transaction['is_renewal'] = 0;
                                                $ipTransaction = Investmentplantransactions::create($transaction);
                                                /*$contactNumber = array();
                                                $member_p_no = Member::where('id',$invest_data->member_id)->first();
                                                if ( $key != 0 && $member_p_no->mobile_no ) {
                                                    $contactNumber[] = str_replace('"','',$member_p_no->mobile_no);
                                                    $text = 'Dear Member, Your A/C '. $accountNumber .' has been Credited on '. $ipTransaction->created_at->format('d M Y') .' With Rs. '. round
                                                        ($val_info->amount,2).' Cur Bal: '. round( $totalbalance, 2 ).'. Thanks Have a nice day';
                                                    $templateId = 1207161726461603982;
                                                    $sendToMember = new Sms();
                                                    $sendToMember->sendSms( $contactNumber, $text, $templateId);
                                                }*/
                                            } else {
                                                $status = "Error";
                                                $code = 201;
                                                $result = '';
                                                $error_filed = $messages1;
                                                $messages = 'field not valid';
                                                $associate_status = $member->associate_app_status;
                                                return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status', 'error_filed'), $code);
                                            }
                                        } else {
                                            $chk_create++;
                                            $messages = 'Account holder  not register by your team ';
                                        }
                                    } else {
                                        $chk_create++;
                                        $messages = 'Account number not exist.';
                                    }
                                }
                            } else {
                                $chk_create++;
                                $messages = 'Account infor not blank.';
                            }
                        } else { // investment renew
                            if (count($array_account) > 0) {
                                foreach ($array_account as $val_info) {
                                    $db_member_data = Memberinvestments::whereHas('company')->where('account_number', $val_info->account)->first();
                                    if ($db_member_data) {
                                        $invest_data = Memberinvestments::whereHas('company')->with('member', 'associateMember')->whereIn('plan_id', [2, 3, 5, 6, 10, 11, 7])->where('account_number', $val_info->account)->whereIn('member_id', $memberArray1)->first();
                                        if ($invest_data) {
                                            $invest_branch_id = $invest_data->branch_id;
                                            $getState = Branch::where('id', $invest_branch_id)->first();
                                            $stateid = $getState->state_id;
                                            $globaldate = checkMonthAvailability(date('d'), date('m'), date('Y'), $stateid);
                                            Session::put('created_at', $globaldate);
                                            $branchCode = $getState->branch_code;
                                            $chk1 = 0;
                                            $accountNumber = $invest_data->account_number;
                                            if ($invest_data->is_mature == 0) {
                                                $chk1++;
                                                $messages1['account_no'] = 'Account number already matured.';
                                            }
                                            if ($invest_data->investment_correction_request == 1) {
                                                $chk1++;
                                                $messages1['account_no'] = 'Account number temporarily not active contact to administrator.';
                                            }
                                            if ($invest_data->renewal_correction_request == 1) {
                                                $chk1++;
                                                $messages1['account_no'] = 'Account number temporarily not active contact to administrator.';
                                            }
                                            if ($invest_data->plan_id != 7) {
                                                $a = $val_info->amount;
                                                $b = $invest_data->deposite_amount;
                                                $c = $a % $b;
                                                if ($c != 0) {
                                                    $chk1++;
                                                    $messages1['amount'] = 'Amount should be multiply deno amount.';
                                                }
                                            }
                                            if ($chk1 == 0) {
                                                $amountArraySsb = array('1' => $val_info->amount);
                                                if ($invest_data->plan_id != 1) {
                                                    $investmentLastDate = Investmentplantransactions::select('deposite_date', 'deposite_month')->where('investment_id', $invest_data->id)->orderBy('id', 'desc')->first();
                                                    if ($investmentLastDate && $invest_data->plan_id == 7) {
                                                        $start_date = strtotime($investmentLastDate->deposite_date);
                                                        $end_date = strtotime(date('Y-m-d'));
                                                        $daysDiff = ($end_date - $start_date) / 60 / 60 / 24;
                                                        if ($daysDiff > 0) {
                                                            if ($invest_data->due_amount >= 0) {
                                                                $invest_due_amount = ($invest_data->deposite_amount * $daysDiff) + $invest_data->due_amount;
                                                            } elseif ($invest_data->due_amount < 0) {
                                                                $invest_due_amount = $invest_data->due_amount;
                                                            }
                                                        } else {
                                                            $invest_due_amount = $invest_data->due_amount;
                                                        }
                                                    } else if ($investmentLastDate && $invest_data->plan_id == 1) {
                                                        $invest_due_amount = $invest_data->deposite_amount;
                                                    } else if ($investmentLastDate) {
                                                        $lMonth = $investmentLastDate->deposite_month;
                                                        $cMonth = date('m');
                                                        $daysDiff = ($cMonth - $lMonth);
                                                        if ($daysDiff > 0) {
                                                            if ($invest_data->due_amount >= 0) {
                                                                $invest_due_amount = ($invest_data->deposite_amount * $daysDiff) + $invest_data->due_amount;
                                                            } elseif ($invest_data->due_amount < 0) {
                                                                $invest_due_amount = $invest_data->due_amount;
                                                            }
                                                        } else {
                                                            $invest_due_amount = $invest_data->due_amount;
                                                        }
                                                    } else {
                                                        $invest_due_amount = $invest_data->deposite_amount;
                                                    }
                                                }
                                                $dueAmount = $invest_due_amount;
                                                $cValue = $val_info->amount;
                                                if ($dueAmount < 0) {
                                                    $checkAmount = $dueAmount + $cValue;
                                                    if ($checkAmount > 0) {
                                                        $updateAmount = -$checkAmount;
                                                    } else if ($checkAmount <= 0) {
                                                        $updateAmount = $dueAmount + $cValue;
                                                    }
                                                } else {
                                                    $updateAmount = $dueAmount - $cValue;
                                                }
                                                $due_amount = $updateAmount;
                                                // --------- due update -----------------
                                                $data_due['due_amount'] = $due_amount;
                                                if ($data_due['due_amount'] && isset($data_due['due_amount'])) {
                                                    $investment = Memberinvestments::find($invest_data->id);
                                                    $investment->update($data_due);
                                                }
                                                //---- ssb acount amount debit  --------
                                                $sAccount = SavingAccount::whereHas('company')->where('id', $member_ssb_id)->first();
                                                $ssbAccountAmount = $sAccount->balance - $val_info->amount;
                                                $ssb_id = $sAccount->id;
                                                $sResult = SavingAccount::find($ssb_id);
                                                $sData['balance'] = $ssbAccountAmount;
                                                $sResult->update($sData);
                                                $ssb['saving_account_id'] = $ssb_id;
                                                $ssb['account_no'] = $sAccount->account_no;
                                                $ssb['opening_balance'] = $ssbAccountAmount;
                                                $ssb['deposit'] = NULL;
                                                $ssb['withdrawal'] = $val_info->amount;
                                                $ssb['description'] = 'Fund Trf. To ' . $invest_data->account_number . '';
                                                $ssb['branch_id'] = $invest_branch_id;
                                                $ssb['type'] = 6;
                                                $ssb['associate_id'] = $invest_collector_associate_id;
                                                $ssb['currency_code'] = 'INR';
                                                $ssb['payment_type'] = 'DR';
                                                $ssb['payment_mode'] = 3;
                                                $ssb['is_renewal'] = 0;
                                                $ssb['app_login_user_id'] = $member->id;
                                                $ssb['is_app'] = 1;
                                                $ssb['created_at'] = $globaldate;
                                                $ssbAccountTran = SavingAccountTranscation::create($ssb);
                                                $ssb_tran_id_from = $ssbAccountTran->id;
                                                $satRefId = CommanTransactionsController::createTransactionReferences($ssbAccountTran->id, $invest_data->id);
                                                $createTransaction1 = CommanTransactionsController::createTransactionApp($satRefId, 5, 0, $ssb_id, $invest_branch_id, $branchCode, $amountArraySsb, 4, $member->first_name . ' ' . $member->last_name, $member->id, $sAccount->account_no, 0, NULL, NULL, date("Y-m-d", strtotime(str_replace('/', '-', $globaldate))), NULL, $online_payment_by = NULL, $ssb_id, 'DR', $member->id);
                                                $transactionData1['is_renewal'] = 0;
                                                $transactionData1['created_at'] = date("Y-m-d H:i:s", strtotime(str_replace('/', '-', $globaldate)));
                                                $updateTransaction1 = Transcation::find($createTransaction1);
                                                $updateTransaction1->update($transactionData1);
                                                TranscationLog::where('transaction_id', $transactionData1)->update(['is_renewal' => 0, 'created_at' => date("Y-m-d H:i:s", strtotime(str_replace('/', '-', $globaldate)))]);
                                                $description = 'Fund Rec. From ' . $sAccount->account_no . '';
                                                //----------------------------------------------------------------------------
                                                $createTransaction = CommanTransactionsController::createTransactionApp($satRefId, 4, $invest_data->id, $invest_data->member_id, $invest_branch_id, $branchCode, $amountArraySsb, 4, $member->first_name . ' ' . $member->last_name, $member->id, $accountNumber, NULL, NULL, NULL, date("Y-m-d", strtotime(str_replace('/', '-', $globaldate))), NULL, $online_payment_by = NULL, $ssb_id, 'CR', $member->id);
                                                $transactionData['is_renewal'] = 0;
                                                $updateTransaction = Transcation::find($createTransaction);
                                                $updateTransaction->update($transactionData);
                                                TranscationLog::where('transaction_id', $transactionData)->update(['is_renewal' => 0, 'created_at' => date("Y-m-d", strtotime(str_replace('/', '-', $globaldate)))]);
                                                $totalbalance = $invest_data->current_balance + $val_info->amount;
                                                if ($invest_data) {
                                                    $s1Result = Memberinvestments::find($invest_data->id);
                                                    $investData['current_balance'] = $totalbalance;
                                                    $s1Result->update($investData);
                                                }
                                                // ---------------------------  Day book modify -------------------------- 
                                                $createDayBook = CommanTransactionsController::createDayBookNewApp($createTransaction, $satRefId, 4, $invest_data->id, $collector_id, $invest_data->member_id, $totalbalance, $val_info->amount, $withdrawal = 0, $description, $ref = NULL, $invest_branch_id, $branchCode, $amountArraySsb, 4, $member->first_name . ' ' . $member->last_name, $member->id, $accountNumber, NULL, NULL, NULL, $globaldate, NULL, $online_payment_by = NULL, $ssb_id, 'CR', NULL, NULL, NULL, NULL, NULL, $member->id);
                                                // ---------------------------  HEAD IMPLEMENT --------------------------
                                                $received_cheque_id = $cheque_id = NULL;
                                                $cheque_deposit_bank_id = NULL;
                                                $cheque_deposit_bank_ac_id = NULL;
                                                $cheque_no = NULL;
                                                $cheque_date = $pdate = NULL;
                                                $online_deposit_bank_id = NULL;
                                                $online_deposit_bank_ac_id = NULL;
                                                $online_transction_no = NULL;
                                                $online_transction_date = NULL;
                                                $planId = $invest_data->plan_id;
                                                $pmodeAll = 3;
                                                $this->investHeadCreate($val_info->amount, $globaldate, $invest_data->id, $planId, $cheque_no, $cheque_id, $cheque_date, $online_transction_no, $online_transction_date, $online_deposit_bank_id, $online_deposit_bank_ac_id, $invest_branch_id, $collector_id, $invest_data->member_id, $ssb_id, $createDayBook, $pmodeAll, $invest_data->account_number, $member->id, $ssb_tran_id_from);
                                                //--------------------------------HEAD IMPLEMENT  ------------------------
                                                /*-------------------------------  Commission  Section Start ------------------------------------*/
                                                $dateForRenew = $globaldate;
                                                $Commission = getMonthlyWiseRenewalNew($invest_data->id, $val_info->amount, $dateForRenew);
                                                foreach ($Commission as $val) {
                                                    $tenureMonth = $invest_data->tenure * 12;
                                                    $commission = CommanTransactionsController::commissionDistributeInvestmentRenew($invest_data->associate_id, $invest_data->id, 3, $val['amount'], $val['month'], $invest_data->plan_id, $invest_branch_id, $tenureMonth, $createDayBook, $val['type']);
                                                    $commission_collection = CommanTransactionsController::commissionCollectionInvestmentRenew($collector_id, $invest_data->id, 5, $val['amount'], $val['month'], $invest_data->plan_id, $invest_branch_id, $tenureMonth, $createDayBook, $val['type']);
                                                    /*----- ------  credit business start ---- ---------------*/
                                                    $creditBusiness = CommanTransactionsController::associateCreditBusiness($invest_data->associate_id, $invest_data->id, 1, $val['amount'], $val['month'], $invest_data->plan_id, $tenureMonth, $createDayBook);
                                                    /*----- ------  credit business end ---- ---------------*/
                                                }
                                                /*-----------------------------  Commission  Section End -------------------------------------*/
                                                $daybookData['is_renewal'] = 0;
                                                $daybookData['app_login_user_id'] = $member->id;
                                                $daybookData['is_app'] = 1;
                                                $daybookData['created_at'] = date("Y-m-d H:i:s", strtotime(str_replace('/', '-', $globaldate)));
                                                $dayBook = Daybook::find($createDayBook);
                                                $dayBook->update($daybookData);
                                                $transaction['investment_id'] = $invest_data->id;
                                                $transaction['plan_id'] = $invest_data->plan_id;
                                                $transaction['member_id'] = $invest_data->member_id;
                                                $transaction['branch_id'] = $invest_branch_id;
                                                $transaction['branch_code'] = $branchCode;
                                                $transaction['deposite_amount'] = $val_info->amount;
                                                $transaction['deposite_date'] = $globaldate;
                                                $transaction['deposite_month'] = date("m", strtotime(str_replace('/', '-', $globaldate)));
                                                $transaction['payment_mode'] = 4;
                                                $transaction['saving_account_id'] = $ssb_id;
                                                $transaction['is_renewal'] = 0;
                                                $ipTransaction = Investmentplantransactions::create($transaction);
                                                /*$contactNumber = array();
                                                $member_p_no = Member::where('id',$invest_data->member_id)->first();
                                                if ( $key != 0 && $member_p_no->mobile_no ) {
                                                    $contactNumber[] = str_replace('"','',$member_p_no->mobile_no);
                                                    $text = 'Dear Member, Your A/C '. $accountNumber .' has been Credited on '. $ipTransaction->created_at->format('d M Y') .' With Rs. '. round
                                                        ($val_info->amount,2).' Cur Bal: '. round( $totalbalance, 2 ).'. Thanks Have a nice day';
                                                    $templateId = 1207161726461603982;
                                                    $sendToMember = new Sms();
                                                    $sendToMember->sendSms( $contactNumber, $text, $templateId);
                                                }*/
                                            } else {
                                                $status = "Error";
                                                $code = 201;
                                                $result = '';
                                                $error_filed = $messages1;
                                                $messages = 'field not valid';
                                                $associate_status = $member->associate_app_status;
                                                return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status', 'error_filed'), $code);
                                            }
                                        } else {
                                            $chk_create++;
                                            $messages = 'Account holder  not register by your team ';
                                        }
                                    } else {
                                        $chk_create++;
                                        $messages = 'Account number not exist.';
                                    }
                                }
                            } else {
                                $chk_create++;
                                $messages = 'Account info not blank.';
                            }
                        }
                        if ($chk_create == 0) {
                            $status = "Success";
                            $code = 200;
                            $messages = 'Account Renewed Successfully.';
                            $result = '';
                            $associate_status = $member->associate_app_status;
                            return response()->json(compact('status', 'code', 'messages', 'result', 'token', 'associate_status', 'error_filed'), $code);
                        } else {
                            $status = "Error";
                            $code = 201;
                            $result = '';
                            $error_filed = $messages1;
                            $associate_status = $member->associate_app_status;
                            return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status', 'error_filed'), $code);
                        }
                    }
                } else {
                    $status = "Error";
                    $code = 201;
                    $messages = 'API token mismatch!';
                    $result = ['account_detail' => $invest_detail1];
                    $associate_status = $member->associate_app_status;
                    return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status', 'error_filed'), $code);
                }
            } else {
                $status = "Error";
                $code = 201;
                $messages = 'Something went wrong!';
                $result = ['account_detail' => $invest_detail1];
                $associate_status = 9;
                return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status', 'error_filed'), $code);
            }
        } catch (\Exception $e) {
            $status = "Error";
            $code = 500;
            $messages = $e->getMessage();
            $result = ['account_detail' => $invest_detail1];
            $associate_status = 9;
            return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status', 'error_filed'), $code);
        }
    }
    public function investHeadCreate($amount, $globaldate, $investmentId, $planId, $cheque_no, $cheque_id, $cheque_date, $online_transction_no, $online_transction_date, $online_deposit_bank_id, $online_deposit_bank_ac_id, $branch_id, $associate_id, $memberId, $ssbId, $createDayBook, $payment_mode, $investmentAccountNoRd, $login_user, $ssb_tran_id_from)
    {
        $ssb_tran_id_from = $ssb_tran_id_from;
        $ssb_tran_id_to = NULL;
        $amount = $amount;
        $daybookRefRD = CommanTransactionsController::createBranchDayBookReferenceNew($amount, $globaldate);
        $refIdRD = $daybookRefRD;
        $currency_code = 'INR';
        $headPaymentModeRD = 0;
        $payment_type_rd = 'CR';
        $type_id = $investmentId;
        $entry_date = date("Y-m-d", strtotime(convertDate($globaldate)));
        $entry_time = date("H:i:s", strtotime(convertDate($globaldate)));
        $created_by = 3;
        $created_by_id = $login_user;
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
        }
        if ($planCode == 709) {
            $head1Invest = 1;
            $head2Invest = 8;
            $head3Invest = 20;
            $head4Invest = 59;
            $head5Invest = 80;
        }
        if ($planCode == 708) {
            $head1Invest = 1;
            $head2Invest = 8;
            $head3Invest = 20;
            $head4Invest = 59;
            $head5Invest = 85;
        }
        if ($planCode == 705) {
            $head1Invest = 1;
            $head2Invest = 8;
            $head3Invest = 20;
            $head4Invest = 57;
            $head5Invest = 79;
        }
        if ($planCode == 707) {
            $head1Invest = 1;
            $head2Invest = 8;
            $head3Invest = 20;
            $head4Invest = 59;
            $head5Invest = 81;
        }
        if ($planCode == 713) {
            $head1Invest = 1;
            $head2Invest = 8;
            $head3Invest = 20;
            $head4Invest = 59;
            $head5Invest = 84;
        }
        if ($planCode == 710) {
            $head1Invest = 1;
            $head2Invest = 8;
            $head3Invest = 20;
            $head4Invest = 58;
            $head5Invest = NULL;
        }
        if ($planCode == 712) {
            $head1Invest = 1;
            $head2Invest = 8;
            $head3Invest = 20;
            $head4Invest = 57;
            $head5Invest = 78;
        }
        if ($planCode == 706) {
            $head1Invest = 1;
            $head2Invest = 8;
            $head3Invest = 20;
            $head4Invest = 57;
            $head5Invest = 77;
        }
        if ($planCode == 704) {
            $head1Invest = 1;
            $head2Invest = 8;
            $head3Invest = 20;
            $head4Invest = 59;
            $head5Invest = 83;
        }
        if ($planCode == 718) {
            $head1Invest = 1;
            $head2Invest = 8;
            $head3Invest = 20;
            $head4Invest = 59;
            $head5Invest = 82;
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
        // ssb
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
        $rdDesMem = $planDetail->name . ' A/C Renewal (' . $investmentAccountNoRd . ')  through SSB(' . $ssbDetals->account_no . ')';
        // ssb  head entry -
        $allTranRDSSB = CommanTransactionsController::createAllTransactionNewTran($refIdRD, $branch_id, $bank_id = NULL, $bank_ac_id = NULL, $head1rdSSB, $head2rdSSB, $head3rdSSB, $head4rdSSB, $head5rdSSB, 4, 47, $ssb_account_id_from, $associate_id, $memberId, $branch_id_to = NULL, $branch_id_from = NULL, $opening_balance = NULL, $amount, $closing_balance = NULL, $SSBDescTran, 'DR', $headPaymentModeRD, $currency_code, $amount_to_id = NULL, $amount_to_name = NULL, $amount_from_id = NULL, $amount_from_name = NULL, $v_no, $v_date, NULL, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $ssb_tran_id_from, NULL, NULL, NULL);
        $branchClosingSSB = CommanTransactionsController::checkCreateBranchClosingDr($branch_id, $created_at, $amount, 0);
        $memberTranInvest77 = CommanTransactionsController::createMemberTransactionNewTran($refIdRD, 4, 47, $ssb_account_id_from, $associate_id, $ssbDetals->member_id, $branch_id, $bank_id = NULL, $account_id = NULL, $amount, $SSBDescTran, 'DR', $headPaymentModeRD, $currency_code, $amount_to_id = NULL, $amount_to_name = NULL, $amount_from_id = NULL, $amount_from_name = NULL, $v_no, $v_date, NULL, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $ssb_tran_id_from, NULL, NULL, NULL);
        //branch day book entry +
        $daybookInvest = CommanTransactionsController::createBranchDayBookNewTran($refIdRD, $branch_id, $type, $sub_type, $type_id, $associate_id, $memberId, $branch_id_to = NULL, $branch_id_from = NULL, $opening_balance = NULL, $amount, $closing_balance = NULL, $rdDes, $rdDesDR, $rdDesCR, $payment_type_rd, $headPaymentModeRD, $currency_code, $amount_to_id = NULL, $amount_to_name = NULL, $amount_from_id = NULL, $amount_from_name = NULL, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $is_contra = NULL, $contra_id = NULL, $created_at, $updated_at, $createDayBook, $ssb_tran_id_from, NULL, NULL);
        // Investment head entry +
        $allTranInvest = CommanTransactionsController::createAllTransactionNewTran($refIdRD, $branch_id, $bank_id = NULL, $bank_ac_id = NULL, $head1Invest, $head2Invest, $head3Invest, $head4Invest, $head5Invest, $type, $sub_type, $type_id, $associate_id, $memberId, $branch_id_to = NULL, $branch_id_from = NULL, $opening_balance = NULL, $amount, $closing_balance = NULL, $rdDes, $payment_type_rd, $headPaymentModeRD, $currency_code, $amount_to_id = NULL, $amount_to_name = NULL, $amount_from_id = NULL, $amount_from_name = NULL, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $createDayBook, $ssb_tran_id_from, NULL, NULL);
        // Member transaction  +
        $memberTranInvest = CommanTransactionsController::createMemberTransactionNewTran($refIdRD, $type, $sub_type, $type_id, $associate_id, $memberId, $branch_id, $bank_id = NULL, $account_id = NULL, $amount, $rdDesMem, $payment_type_rd, $headPaymentModeRD, $currency_code, $amount_to_id = NULL, $amount_to_name = NULL, $amount_from_id = NULL, $amount_from_name = NULL, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $createDayBook, $ssb_tran_id_from, NULL, NULL);
        /******** Balance   entry ***************/
        $branchClosing = CommanTransactionsController::checkCreateBranchClosing($branch_id, $created_at, $amount, 0);
    }
    public function investHeadCreateSSB($amount, $globaldate, $investmentId, $planId, $cheque_no, $cheque_id, $cheque_date, $online_transction_no, $online_transction_date, $online_deposit_bank_id, $online_deposit_bank_ac_id, $branch_id, $associate_id, $memberId, $ssbId, $createDayBook, $payment_mode, $investmentAccountNoRd, $login_user, $ssb_tran_id_from)
    {
        $ssb_tran_id_from = $ssb_tran_id_from;
        $ssb_tran_id_to = NULL;
        $amount = $amount;
        $daybookRefRD = CommanTransactionsController::createBranchDayBookReferenceNew($amount, $globaldate);
        $refIdRD = $daybookRefRD;
        $currency_code = 'INR';
        $headPaymentModeRD = 0;
        $payment_type_rd = 'CR';
        $type_id = $investmentId;
        $entry_date = date("Y-m-d", strtotime(convertDate($globaldate)));
        $entry_time = date("H:i:s", strtotime(convertDate($globaldate)));
        $created_by = 3;
        $created_by_id = $login_user;
        $created_at = date("Y-m-d H:i:s", strtotime(convertDate($globaldate)));
        $updated_at = date("Y-m-d H:i:s", strtotime(convertDate($globaldate)));
        $planDetail = getPlanDetail($planId);
        $type = 4;
        $sub_type = 42;
        $planCode = $planDetail->plan_code;
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
        // ssb
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
        $allTranRDSSB = CommanTransactionsController::createAllTransactionNewTran($refIdRD, $branch_id, $bank_id = NULL, $bank_ac_id = NULL, $head1rdSSB, $head2rdSSB, $head3rdSSB, $head4rdSSB, $head5rdSSB, 4, 47, $ssb_account_id_from, $associate_id, $memberId, $branch_id_to = NULL, $branch_id_from = NULL, $opening_balance = NULL, $amount, $closing_balance = NULL, $rdDes, 'DR', $headPaymentModeRD, $currency_code, $amount_to_id = NULL, $amount_to_name = NULL, $amount_from_id = NULL, $amount_from_name = NULL, $v_no, $v_date, NULL, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $ssb_tran_id_from, NULL, $createDayBook, $type_id);
        $branchClosingSSB = CommanTransactionsController::checkCreateBranchClosingDr($branch_id, $created_at, $amount, 0);
        $memberTranInvest77 = CommanTransactionsController::createMemberTransactionNewTran($refIdRD, 4, 47, $ssb_account_id_from, $associate_id, $ssbDetals->member_id, $branch_id, $bank_id = NULL, $account_id = NULL, $amount, $SSBDescTran, 'DR', $headPaymentModeRD, $currency_code, $amount_to_id = $type_id, $amount_to_name = $planDetail->name, $amount_from_id = NULL, $amount_from_name = NULL, $v_no, $v_date, NULL, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $ssb_tran_id_from, NULL, $createDayBook, $type_id);
        //branch day book entry +
        $daybookInvest = CommanTransactionsController::createBranchDayBookNewTran($refIdRD, $branch_id, $type, $sub_type, $type_id, $associate_id, $memberId, $branch_id_to = NULL, $branch_id_from = NULL, $opening_balance = NULL, $amount, $closing_balance = NULL, $rdDes, $rdDesDR, $rdDesCR, $payment_type_rd, $headPaymentModeRD, $currency_code, $amount_to_id = NULL, $amount_to_name = NULL, $amount_from_id = NULL, $amount_from_name = NULL, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $is_contra = NULL, $contra_id = NULL, $created_at, $updated_at, $createDayBook, $ssb_tran_id_from, NULL, NULL);
        // Investment head entry +
        $allTranInvest = CommanTransactionsController::createAllTransactionNewTran($refIdRD, $branch_id, $bank_id = NULL, $bank_ac_id = NULL, $head1Invest, $head2Invest, $head3Invest, $head4Invest, $head5Invest, $type, $sub_type, $type_id, $associate_id, $memberId, $branch_id_to = NULL, $branch_id_from = NULL, $opening_balance = NULL, $amount, $closing_balance = NULL, $rdDes, $payment_type_rd, $headPaymentModeRD, $currency_code, $amount_to_id = NULL, $amount_to_name = NULL, $amount_from_id = NULL, $amount_from_name = NULL, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $createDayBook, $ssb_tran_id_from, NULL, NULL);
        // Member transaction  +
        $memberTranInvest = CommanTransactionsController::createMemberTransactionNewTran($refIdRD, $type, $sub_type, $type_id, $associate_id, $memberId, $branch_id, $bank_id = NULL, $account_id = NULL, $amount, $rdDesMem, $payment_type_rd, $headPaymentModeRD, $currency_code, $amount_to_id = NULL, $amount_to_name = NULL, $amount_from_id = NULL, $amount_from_name = NULL, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $createDayBook, $ssb_tran_id_from, NULL, NULL);
        /******** Balance   entry ***************/
        $branchClosing = CommanTransactionsController::checkCreateBranchClosing($branch_id, $created_at, $amount, 0);
    }
    public function mbMaturity(Request $request)
    {
        $associate_no = $request->associate_no;
        $data = array();
        $data['maturity_amount'] = 0;
        $data['interest_rate'] = 0;
        $data['tenure'] = 0;
        try {
            $member = Member::select('id', 'associate_app_status')->where('associate_no', $request->associate_no)->where('associate_status', 1)->where('is_block', 0)->first();
            if ($member) {
                $token = md5($request->associate_no);
                if ($token == $request->token) {
                    if ($request->plan_code == '' && $request->amount == '') {
                        $status = "Error";
                        $code = 201;
                        $messages = 'Please select plan and enter amount';
                        $result = ['maturity_detail' => $data];
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    } elseif ($request->plan_code != '' && $request->amount == '') {
                        $status = "Error";
                        $code = 201;
                        $messages = 'Please enter amount';
                        $result = ['maturity_detail' => $data];
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    } elseif ($request->plan_code == '' && $request->amount != '') {
                        $status = "Error";
                        $code = 201;
                        $messages = 'Please select  plan';
                        $result = ['maturity_detail' => $data];
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    } else {
                        $plan_code = $request->plan_code;
                        if ($plan_code == 708) {
                            $amount = $request->amount;
                            $principal = $amount;
                            $time = 12;
                            $tenure = 7;
                            $rate = 9;
                            $ci = 1;
                            $irate = 8 / $ci;
                            $year = $time / 12;
                            $freq = 4;
                            $perYearSixtyPecent = (($principal * 12) * 60 / 100);
                            $carryAmount = 0;
                            $carryForwardInterest = 0;
                            $oldMaturity = 0;
                            $maturity = 0;
                            for ($j = 1; $j <= $tenure; $j++) {
                                $perYearWithInterest = 0;
                                for ($i = 1; $i <= $time; $i++) {
                                    $perYearWithInterest = $perYearWithInterest + ($principal * pow((1 + (($rate / 100) / $freq)), $freq * (($time - $i + 1) / 12)));
                                }
                                if ($j > 1) {
                                    $carryForwardInterest = ($oldMaturity * (pow((1 + $rate / 100), 1)));
                                    $maturity = round($perYearWithInterest + $carryForwardInterest);
                                    $oldMaturity = round($maturity - $perYearSixtyPecent);
                                } else {
                                    $oldMaturity = round($perYearWithInterest - $perYearSixtyPecent);
                                    $maturity = $maturity + $oldMaturity;
                                }
                            }
                            $result = round($maturity);
                            if ($result) {
                                $data['maturity_amount'] = $result;
                                $data['interest_rate'] = $rate;
                                $data['tenure'] = $tenure;
                            }
                            $status = "Success";
                            $code = 200;
                            $messages = 'Maturity Detail!';
                            $result = ['maturity_detail' => $data];
                            $associate_status = $member->associate_app_status;
                            return response()->json(compact('status', 'code', 'messages', 'result', 'token', 'associate_status'), $code);
                        } else {
                            $status = "Error";
                            $code = 201;
                            $messages = 'Enter wrong plan code ';
                            $result = ['maturity_detail' => $data];
                            $associate_status = $member->associate_app_status;
                            return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                        }
                    }
                } else {
                    $status = "Error";
                    $code = 201;
                    $messages = 'API token mismatch!';
                    $result = ['maturity_detail' => $data];
                    $associate_status = $member->associate_app_status;
                    return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                }
            } else {
                $status = "Error";
                $code = 201;
                $messages = 'Something went wrong!';
                $result = ['maturity_detail' => $data];
                $associate_status = 9;
                return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
            }
        } catch (\Exception $e) {
            $status = "Error";
            $code = 500;
            $messages = $e->getMessage();
            $result = ['maturity_detail' => $data];
            $associate_status = 9;
            return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
        }
    }
    public function samraddhJeevanMaturity(Request $request)
    {
        $associate_no = $request->associate_no;
        $data = array();
        $data['maturity_amount'] = 0;
        $data['interest_rate'] = 0;
        $data['tenure'] = 0;
        try {
            $member = Member::select('id', 'associate_app_status')->where('associate_no', $request->associate_no)->where('associate_status', 1)->where('is_block', 0)->first();
            if ($member) {
                $token = md5($request->associate_no);
                if ($token == $request->token) {
                    if ($request->plan_code == '' && $request->amount == '') {
                        $status = "Error";
                        $code = 201;
                        $messages = 'Please select plan and enter amount';
                        $result = ['maturity_detail' => $data];
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    } elseif ($request->plan_code != '' && $request->amount == '') {
                        $status = "Error";
                        $code = 201;
                        $messages = 'Please enter amount';
                        $result = ['maturity_detail' => $data];
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    } elseif ($request->plan_code == '' && $request->amount != '') {
                        $status = "Error";
                        $code = 201;
                        $messages = 'Please select  plan';
                        $result = ['maturity_detail' => $data];
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    } else {
                        $plan_code = $request->plan_code;
                        if ($plan_code == 713) {
                            $amount = $request->amount;
                            $principal = $amount;
                            $time = $tenure = 84;
                            $rate = 10.50;
                            $ci = 1;
                            $irate = $rate / $ci;
                            $year = $time / 12;
                            $freq = 1;
                            $maturity = 0;
                            for ($i = 1; $i <= $time; $i++) {
                                $maturity += $principal * pow((1 + (($rate / 100) / $freq)), $freq * (($time - $i + 1) / 12));
                            }
                            $result = round($maturity);
                            if ($result) {
                                $data['maturity_amount'] = $result;
                                $data['interest_rate'] = $rate;
                                $data['tenure'] = $tenure;
                            }
                            $status = "Success";
                            $code = 200;
                            $messages = 'Maturity Detail!';
                            $result = ['maturity_detail' => $data];
                            $associate_status = $member->associate_app_status;
                            return response()->json(compact('status', 'code', 'messages', 'result', 'token', 'associate_status'), $code);
                        } else {
                            $status = "Error";
                            $code = 201;
                            $messages = 'Enter wrong plan code ';
                            $result = ['maturity_detail' => $data];
                            $associate_status = $member->associate_app_status;
                            return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                        }
                    }
                } else {
                    $status = "Error";
                    $code = 201;
                    $messages = 'API token mismatch!';
                    $result = ['maturity_detail' => $data];
                    $associate_status = $member->associate_app_status;
                    return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                }
            } else {
                $status = "Error";
                $code = 201;
                $messages = 'Something went wrong!';
                $result = ['maturity_detail' => $data];
                $associate_status = 9;
                return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
            }
        } catch (\Exception $e) {
            $status = "Error";
            $code = 500;
            $messages = $e->getMessage();
            $result = ['maturity_detail' => $data];
            $associate_status = 9;
            return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
        }
    }
    public function samraddhBhavhishyaMaturity(Request $request)
    {
        $associate_no = $request->associate_no;
        $data = array();
        $data['maturity_amount'] = 0;
        $data['interest_rate'] = 0;
        $data['tenure'] = 0;
        try {
            $member = Member::select('id', 'associate_app_status')->where('associate_no', $request->associate_no)->where('associate_status', 1)->where('is_block', 0)->first();
            if ($member) {
                $token = md5($request->associate_no);
                if ($token == $request->token) {
                    if ($request->plan_code == '' && $request->amount == '') {
                        $status = "Error";
                        $code = 201;
                        $messages = 'Please select plan and enter amount';
                        $result = ['maturity_detail' => $data];
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    } elseif ($request->plan_code != '' && $request->amount == '') {
                        $status = "Error";
                        $code = 201;
                        $messages = 'Please enter amount';
                        $result = ['maturity_detail' => $data];
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    } elseif ($request->plan_code == '' && $request->amount != '') {
                        $status = "Error";
                        $code = 201;
                        $messages = 'Please select  plan';
                        $result = ['maturity_detail' => $data];
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    } else {
                        $plan_code = $request->plan_code;
                        if ($plan_code == 718) {
                            $tenure = 120;
                            $principal = $request->amount;
                            $time = $tenure;
                            $rate = 11;
                            $ci = 1;
                            $irate = $rate / $ci;
                            $year = $time / 12;
                            $freq = 1;
                            $maturity = 0;
                            for ($i = 1; $i <= $time; $i++) {
                                $maturity = $maturity + $principal * pow((1 + (($rate / 100) / $freq)), $freq * (($time - $i + 1) / 12));
                            }
                            $result = round($maturity);
                            if ($result) {
                                $data['maturity_amount'] = $result;
                                $data['interest_rate'] = $rate;
                                $data['tenure'] = $tenure;
                            }
                            $status = "Success";
                            $code = 200;
                            $messages = 'Maturity Detail!';
                            $result = ['maturity_detail' => $data];
                            $associate_status = $member->associate_app_status;
                            return response()->json(compact('status', 'code', 'messages', 'result', 'token', 'associate_status'), $code);
                        } else {
                            $status = "Error";
                            $code = 201;
                            $messages = 'Enter wrong plan code ';
                            $result = ['maturity_detail' => $data];
                            $associate_status = $member->associate_app_status;
                            return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                        }
                    }
                } else {
                    $status = "Error";
                    $code = 201;
                    $messages = 'API token mismatch!';
                    $result = ['maturity_detail' => $data];
                    $associate_status = $member->associate_app_status;
                    return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                }
            } else {
                $status = "Error";
                $code = 201;
                $messages = 'Something went wrong!';
                $result = ['maturity_detail' => $data];
                $associate_status = 9;
                return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
            }
        } catch (\Exception $e) {
            $status = "Error";
            $code = 500;
            $messages = $e->getMessage();
            $result = ['maturity_detail' => $data];
            $associate_status = 9;
            return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
        }
    }
    public function samraddhKanyadhanYojana(Request $request)
    {
        $associate_no = $request->associate_no;
        $data = array();
        $data['maturity_amount'] = 0;
        $data['interest_rate'] = 0;
        $data['tenure'] = 0;
        $data['monthly_deposit'] = 0;
        try {
            $member = Member::select('id', 'associate_app_status')->where('associate_no', $request->associate_no)->where('associate_status', 1)->where('is_block', 0)->first();
            if ($member) {
                $token = md5($request->associate_no);
                if ($token == $request->token) {
                    if ($request->plan_code == '' && $request->age == '') {
                        $status = "Error";
                        $code = 201;
                        $messages = 'Please select plan and enter age';
                        $result = ['maturity_detail' => $data];
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    } elseif ($request->plan_code != '' && $request->age == '') {
                        $status = "Error";
                        $code = 201;
                        $messages = 'Please enter age';
                        $result = ['maturity_detail' => $data];
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    } elseif ($request->plan_code == '' && $request->age != '') {
                        $status = "Error";
                        $code = 201;
                        $messages = 'Please select  plan';
                        $result = ['maturity_detail' => $data];
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    } else {
                        $plan_code = $request->plan_code;
                        if ($plan_code == 709) {
                            $fa_code = $plan_code;
                            $tenure = 18 - $request->age;
                            $investmentAmount = Investmentplanamounts::where('plan_fa_code', $fa_code)->where('year', $tenure)->select('amount')->first();
                            $principal = $investmentAmount->amount;
                            if ($tenure >= 8 && $tenure <= 18) {
                                $rate = 11;
                            } else if ($tenure >= 6 && $tenure <= 7) {
                                $rate = 10.50;
                            } else if ($tenure < 6) {
                                $rate = 10;
                            }
                            $ci = 1;
                            $time = $tenure * 12;
                            $irate = $rate / $ci;
                            $year = $time / 12;
                            $result = ($principal * (pow((1 + $irate / 100), $year * $ci) - 1) / (1 - pow((1 + $irate / 100), -$ci / 12)));
                            if ($result) {
                                $data['maturity_amount'] = round($result);
                                $data['interest_rate'] = $rate;
                                $data['tenure'] = $tenure;
                                $data['monthly_deposit'] = $principal;
                            }
                            $status = "Success";
                            $code = 200;
                            $messages = 'Maturity Detail!';
                            $result = ['maturity_detail' => $data];
                            $associate_status = $member->associate_app_status;
                            return response()->json(compact('status', 'code', 'messages', 'result', 'token', 'associate_status'), $code);
                        } else {
                            $status = "Error";
                            $code = 201;
                            $messages = 'Enter wrong plan code ';
                            $result = ['maturity_detail' => $data];
                            $associate_status = $member->associate_app_status;
                            return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                        }
                    }
                } else {
                    $status = "Error";
                    $code = 201;
                    $messages = 'API token mismatch!';
                    $result = ['maturity_detail' => $data];
                    $associate_status = $member->associate_app_status;
                    return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                }
            } else {
                $status = "Error";
                $code = 201;
                $messages = 'Something went wrong!';
                $result = ['maturity_detail' => $data];
                $associate_status = 9;
                return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
            }
        } catch (\Exception $e) {
            $status = "Error";
            $code = 500;
            $messages = $e->getMessage();
            $result = ['maturity_detail' => $data];
            $associate_status = 9;
            return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
        }
    }
    public function associate_renew_report(Request $request)
    {
        $associate_no = $request->associate_no;
        try {
            $member = Member::select('id', 'associate_app_status')->where('associate_no', $request->associate_no)->where('associate_status', 1)->where('is_block', 0)->first();
            if ($member) {
                $token = md5($request->associate_no);
                if ($token == $request->token) {
                    if ($request->page > 0 && $request->length > 0) {
                        $data = array();
                        $pid = 1;
                        $data_re = Daybook::whereHas('company')->with(['dbranch', 'member', 'associateMember', 'investment' => function ($query) {
                            $query->select('id', 'plan_id', 'account_number', 'tenure');
                        }])
                            ->whereHas('investment', function ($query) use ($pid) {
                                $query->where('member_investments.plan_id', '!=', $pid);
                            })->where('transaction_type', 4)->where('app_login_user_id', $member->id)->where('is_app', 1);
                        $startDate = '';
                        $endDate = '';
                        if ($request->start_date != '') {
                            $startDate = $request->start_date;
                            if ($request->start_date != '') {
                                $endDate = $request->end_date;
                            }
                            $startDate = date("Y-m-d", strtotime(convertDate($startDate)));
                            $endDate = date("Y-m-d", strtotime(convertDate($endDate)));
                            $data_re = $data_re->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate]);
                        }
                        if ($request->plan_id != '') {
                            $planId = $request->plan_id;
                            $data_re = $data_re->whereHas('investment', function ($query) use ($planId) {
                                $query->where('member_investments.plan_id', '=', $planId);
                            });
                        }
                        $count = $data_re->orderby('id', 'DESC')->count();
                        if ($request->page == 1) {
                            $start = 0;
                        } else {
                            $start = ($request->page - 1) * $request->length;
                        }
                        $data_re = $data_re->orderby('id', 'DESC')->offset($start)->limit($request->length)->get();
                        foreach ($data_re as $key => $row) {
                            $data[$key]['id'] = $row['id'];
                            $data[$key]['created_at'] = date("d/m/Y", strtotime($row['created_at']));
                            $data[$key]['branch'] = $row['dbranch']['name'];
                            $data[$key]['branch_code'] = $row['dbranch']['branch_code'];
                            $data[$key]['sector_name'] = $row['dbranch']['sector'];
                            $data[$key]['region_name'] = $row['dbranch']['sector'];
                            $data[$key]['zone_name'] = $row['dbranch']['zone'];
                            $data[$key]['member_id'] = $row['member']['member_id'];
                            $data[$key]['account_number'] = $row['account_no'];
                            $data[$key]['member'] = $row['member']['first_name'] . ' ' . $row['member']['last_name'];
                            $planId = $row['investment']['plan_id'];
                            $planName = '';
                            if ($planId > 0) {
                                $PlanDetail = getPlanDetail($planId);
                                if (!empty($PlanDetail)) {
                                    $planName = $PlanDetail->toArray()['name'];
                                }
                            }
                            $data[$key]['plan'] = $planName;
                            $tenure = '';
                            if ($planId == 1) {
                                $tenure = 'N/A';
                            } else {
                                $tenure = $row['investment']['tenure'] . ' Year';
                            }
                            $data[$key]['tenure'] = $tenure;
                            $data[$key]['amount'] = $row['amount'];
                            $data[$key]['associate_code'] = $row['associateMember']['associate_no'];
                            $data[$key]['associate_name'] = $row['associateMember']['first_name'] . ' ' . $row['associateMember']['last_name'];
                            $mode = '';
                            if ($row->payment_mode == 0) {
                                $mode = "Cash";
                            } elseif ($row->payment_mode == 1) {
                                $mode = "Cheque";
                            } elseif ($row->payment_mode == 2) {
                                $mode = "DD";
                            } elseif ($row->payment_mode == 3) {
                                $mode = "Online";
                            } elseif ($row->payment_mode == 4) {
                                $mode = "By Saving Account";
                            } elseif ($row->payment_mode == 5) {
                                $mode = "From Loan Amount";
                            }
                            $data[$key]['payment_mode'] = $mode;
                        }
                        $status = "Success";
                        $code = 200;
                        $messages = 'Renew detail !';
                        $length = $request->length;
                        $page = $request->page;
                        $result = ['renew' => $data, 'total_count' => $count, 'page' => $page, 'length' => $length, 'record_count' => count($data)];
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    } else {
                        $status = "Error";
                        $code = 201;
                        $messages = 'Page no or length must be grater than 0!';
                        $result = '';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                } else {
                    $status = "Error";
                    $code = 201;
                    $messages = 'API token mismatch!';
                    $result = '';
                    $associate_status = $member->associate_app_status;
                    return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                }
            } else {
                $status = "Error";
                $code = 201;
                $messages = 'Something went wrong!';
                $result = '';
                $associate_status = 9;
                return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
            }
        } catch (\Exception $e) {
            $status = "Error";
            $code = 500;
            $messages = $e->getMessage();
            $result = '';
            $associate_status = 9;
            return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
        }
    }
    public function associate_renewSSB_report(Request $request)
    {
        $associate_no = $request->associate_no;
        try {
            $member = Member::select('id', 'associate_app_status')->where('associate_no', $request->associate_no)->where('associate_status', 1)->where('is_block', 0)->first();
            if ($member) {
                $token = md5($request->associate_no);
                if ($token == $request->token) {
                    if ($request->page > 0 && $request->length > 0) {
                        $data = array();
                        $pid = 1;
                        $data_re = SavingAccountTranscation::whereHas('company')->with(['savingAc', 'dbranch', 'associateMember'])->where('type', 2)->where('app_login_user_id', $member->id)->where('is_app', 1);
                        $startDate = '';
                        $endDate = '';
                        if ($request->start_date != '') {
                            $startDate = $request->start_date;
                            if ($request->start_date != '') {
                                $endDate = $request->end_date;
                            }
                            $startDate = date("Y-m-d", strtotime(convertDate($startDate)));
                            $endDate = date("Y-m-d", strtotime(convertDate($endDate)));
                            $data_re = $data_re->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate]);
                        }
                        $count = $data_re->orderby('id', 'DESC')->count();
                        if ($request->page == 1) {
                            $start = 0;
                        } else {
                            $start = ($request->page - 1) * $request->length;
                        }
                        $data_re = $data_re->orderby('id', 'DESC')->offset($start)->limit($request->length)->get();
                        foreach ($data_re as $key => $row) {
                            $data[$key]['id'] = $row['id'];
                            $data[$key]['created_at'] = date("d/m/Y", strtotime($row['created_at']));
                            $data[$key]['branch'] = $row['dbranch']['name'];
                            $data[$key]['branch_code'] = $row['dbranch']['branch_code'];
                            $data[$key]['sector_name'] = $row['dbranch']['sector'];
                            $data[$key]['region_name'] = $row['dbranch']['sector'];
                            $data[$key]['zone_name'] = $row['dbranch']['zone'];
                            $data[$key]['member_id'] = getApplicantid($row['savingAc']['member_id']);
                            $data[$key]['account_number'] = $row['account_no'];
                            $memberData = memberFieldDataStatus(array("id", "first_name", "last_name"), $row['savingAc']['member_id'], 'id');
                            $data[$key]['member'] = $memberData[0]['first_name'] . ' ' . $memberData[0]['last_name'];
                            if ($row['deposit'] > 0)
                                $data[$key]['amount'] = $row['deposit'];
                            else
                                $data[$key]['amount'] = 0.00;
                            $data[$key]['associate_code'] = $row['associateMember']['associate_no'];
                            $data[$key]['associate_name'] = $row['associateMember']['first_name'] . ' ' . $row['associateMember']['last_name'];
                            $mode = '';
                            if ($row->payment_mode == 0) {
                                $mode = "Cash";
                            } elseif ($row->payment_mode == 1) {
                                $mode = "Cheque";
                            } elseif ($row->payment_mode == 2) {
                                $mode = "DD";
                            } elseif ($row->payment_mode == 3) {
                                $mode = "Online";
                            } elseif ($row->payment_mode == 4) {
                                $mode = "By Saving Account";
                            } elseif ($row->payment_mode == 5) {
                                $mode = "From Loan Amount";
                            }
                            $data[$key]['payment_mode'] = $mode;
                        }
                        $status = "Success";
                        $code = 200;
                        $messages = 'Renew SSB detail !';
                        // $result   = ['renew' => $data,'total_count'=>$count];
                        $length = $request->length;
                        $page = $request->page;
                        $result = ['renew' => $data, 'total_count' => $count, 'page' => $page, 'length' => $length, 'record_count' => count($data)];
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    } else {
                        $status = "Error";
                        $code = 201;
                        $messages = 'Page no or length must be grater than 0!';
                        $result = '';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                } else {
                    $status = "Error";
                    $code = 201;
                    $messages = 'API token mismatch!';
                    $result = '';
                    $associate_status = $member->associate_app_status;
                    return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                }
            } else {
                $status = "Error";
                $code = 201;
                $messages = 'Something went wrong!';
                $result = '';
                $associate_status = 9;
                return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
            }
        } catch (\Exception $e) {
            $status = "Error";
            $code = 500;
            $messages = $e->getMessage();
            $result = '';
            $associate_status = 9;
            return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
        }
    }
    public function investHeadCreateRegister($amount, $globaldate, $investmentId, $planId, $cheque_no, $cheque_id, $cheque_date, $online_transction_no, $online_transction_date, $online_deposit_bank_id, $online_deposit_bank_ac_id, $branch_id, $associate_id, $memberId, $ssbId, $createDayBook, $payment_mode, $investmentAccountNoRd, $login_user, $ssb_tran_id_from)
    {
        $ssb_tran_id_from = $ssb_tran_id_from;
        $ssb_tran_id_to = NULL;
        $amount = $amount;
        $daybookRefRD = CommanTransactionsController::createBranchDayBookReferenceNew($amount, $globaldate);
        $refIdRD = $daybookRefRD;
        $currency_code = 'INR';
        $headPaymentModeRD = 0;
        $payment_type_rd = 'CR';
        $type_id = $investmentId;
        $entry_date = date("Y-m-d", strtotime(convertDate($globaldate)));
        $entry_time = date("H:i:s", strtotime(convertDate($globaldate)));
        $created_by = 3;
        $created_by_id = $login_user;
        $created_at = date("Y-m-d H:i:s", strtotime(convertDate($globaldate)));
        $updated_at = date("Y-m-d H:i:s", strtotime(convertDate($globaldate)));
        $planDetail = getPlanDetail($planId);
        $type = 3;
        $sub_type = 31;
        $planCode = $planDetail->plan_code;
        if ($planCode == 709) {
            $head1Invest = 1;
            $head2Invest = 8;
            $head3Invest = 20;
            $head4Invest = 59;
            $head5Invest = 80;
        }
        if ($planCode == 708) {
            $head1Invest = 1;
            $head2Invest = 8;
            $head3Invest = 20;
            $head4Invest = 59;
            $head5Invest = 85;
        }
        if ($planCode == 705) {
            $head1Invest = 1;
            $head2Invest = 8;
            $head3Invest = 20;
            $head4Invest = 57;
            $head5Invest = 79;
        }
        if ($planCode == 707) {
            $head1Invest = 1;
            $head2Invest = 8;
            $head3Invest = 20;
            $head4Invest = 59;
            $head5Invest = 81;
        }
        if ($planCode == 713) {
            $head1Invest = 1;
            $head2Invest = 8;
            $head3Invest = 20;
            $head4Invest = 59;
            $head5Invest = 84;
        }
        if ($planCode == 710) {
            $head1Invest = 1;
            $head2Invest = 8;
            $head3Invest = 20;
            $head4Invest = 58;
            $head5Invest = NULL;
        }
        if ($planCode == 712) {
            $head1Invest = 1;
            $head2Invest = 8;
            $head3Invest = 20;
            $head4Invest = 57;
            $head5Invest = 78;
        }
        if ($planCode == 706) {
            $head1Invest = 1;
            $head2Invest = 8;
            $head3Invest = 20;
            $head4Invest = 57;
            $head5Invest = 77;
        }
        if ($planCode == 704) {
            $head1Invest = 1;
            $head2Invest = 8;
            $head3Invest = 20;
            $head4Invest = 59;
            $head5Invest = 83;
        }
        if ($planCode == 718) {
            $head1Invest = 1;
            $head2Invest = 8;
            $head3Invest = 20;
            $head4Invest = 59;
            $head5Invest = 82;
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
        // -----  ssb---------------
        $headPaymentModeRD = 3;
        $v_no = mt_rand(0, 999999999999999);
        $v_date = $entry_date;
        $ssb_account_id_from = $ssbId;
        $SSBDescTran = 'Amount transferred to ' . $planDetail->name . '(' . $investmentAccountNoRd . ') ';
        $head1rdSSB = 1;
        $head2rdSSB = 8;
        $head3rdSSB = 20;
        $head4rdSSB = 56;
        $head5rdSSB = NULL;
        $ssbDetals = SavingAccount::where('id', $ssb_account_id_from)->first();
        $rdDesDR = $planDetail->name . '(' . $investmentAccountNoRd . ') A/c Dr ' . $amount . '/-';
        $rdDesCR = 'To SSB(' . $ssbDetals->account_no . ') A/c Cr ' . $amount . '/-';
        $rdDes = 'Amount received for Account opening ' . $planDetail->name . '(' . $investmentAccountNoRd . ') through SSB(' . $ssbDetals->account_no . ')';
        $rdDesMem = 'Account opening ' . $planDetail->name . '(' . $investmentAccountNoRd . ') through SSB(' . $ssbDetals->account_no . ')';
        // ssb  head entry -
        $allTranRDSSB = CommanTransactionsController::createAllTransactionNewTran($refIdRD, $branch_id, $bank_id = NULL, $bank_ac_id = NULL, $head1rdSSB, $head2rdSSB, $head3rdSSB, $head4rdSSB, $head5rdSSB, 4, 47, $ssb_account_id_from, $associate_id, $memberId, $branch_id_to = NULL, $branch_id_from = NULL, $opening_balance = NULL, $amount, $closing_balance = NULL, $rdDes, 'DR', $headPaymentModeRD, $currency_code, $amount_to_id = $type_id, $amount_to_name = $planDetail->name, $amount_from_id = NULL, $amount_from_name = NULL, $v_no, $v_date, NULL, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $ssb_tran_id_from, NULL, NULL, NULL);
        $branchClosingSSB = CommanTransactionsController::checkCreateBranchClosingDr($branch_id, $created_at, $amount, 0);
        // Member transaction  -
        $memberTranInvest77 = CommanTransactionsController::createMemberTransactionNewTran($refIdRD, 4, 47, $ssb_account_id_from, $associate_id, $ssbDetals->member_id, $branch_id, $bank_id = NULL, $account_id = NULL, $amount, $SSBDescTran, 'DR', $headPaymentModeRD, $currency_code, $amount_to_id = NULL, $amount_to_name = NULL, $amount_from_id = NULL, $amount_from_name = NULL, $v_no, $v_date, NULL, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $ssb_tran_id_from, NULL, NULL, NULL);
        //branch day book entry +
        $daybookInvest = CommanTransactionsController::createBranchDayBookNewTran($refIdRD, $branch_id, $type, $sub_type, $type_id, $associate_id, $memberId, $branch_id_to = NULL, $branch_id_from = NULL, $opening_balance = NULL, $amount, $closing_balance = NULL, $rdDes, $rdDesDR, $rdDesCR, $payment_type_rd, $headPaymentModeRD, $currency_code, $amount_to_id = NULL, $amount_to_name = NULL, $amount_from_id = NULL, $amount_from_name = NULL, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $is_contra = NULL, $contra_id = NULL, $created_at, $updated_at, $createDayBook, $ssb_tran_id_from, NULL, NULL);
        // Investment head entry +
        $allTranInvest = CommanTransactionsController::createAllTransactionNewTran($refIdRD, $branch_id, $bank_id = NULL, $bank_ac_id = NULL, $head1Invest, $head2Invest, $head3Invest, $head4Invest, $head5Invest, $type, $sub_type, $type_id, $associate_id, $memberId, $branch_id_to = NULL, $branch_id_from = NULL, $opening_balance = NULL, $amount, $closing_balance = NULL, $rdDes, $payment_type_rd, $headPaymentModeRD, $currency_code, $amount_to_id = NULL, $amount_to_name = NULL, $amount_from_id = NULL, $amount_from_name = NULL, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $createDayBook, $ssb_tran_id_from, NULL, NULL);
        // Member transaction  +
        $memberTranInvest = CommanTransactionsController::createMemberTransactionNewTran($refIdRD, $type, $sub_type, $type_id, $associate_id, $memberId, $branch_id, $bank_id = NULL, $account_id = NULL, $amount, $rdDesMem, $payment_type_rd, $headPaymentModeRD, $currency_code, $amount_to_id = NULL, $amount_to_name = NULL, $amount_from_id = NULL, $amount_from_name = NULL, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $createDayBook, $ssb_tran_id_from, NULL, NULL);
        /******** Balance   entry ***************/
        $branchClosing = CommanTransactionsController::checkCreateBranchClosing($branch_id, $created_at, $amount, 0);
    }
    public function investHeadCreateSSBRegister($amount, $globaldate, $investmentId, $planId, $cheque_no, $cheque_id, $cheque_date, $online_transction_no, $online_transction_date, $online_deposit_bank_id, $online_deposit_bank_ac_id, $branch_id, $associate_id, $memberId, $ssbId, $createDayBook, $payment_mode, $investmentAccountNoRd, $login_user, $ssb_tran_id_from)
    {
        $ssb_tran_id_from = $ssb_tran_id_from;
        $ssb_tran_id_to = NULL;
        $amount = $amount;
        $daybookRefRD = CommanTransactionsController::createBranchDayBookReferenceNew($amount, $globaldate);
        $refIdRD = $daybookRefRD;
        $currency_code = 'INR';
        $headPaymentModeRD = 0;
        $payment_type_rd = 'CR';
        $type_id = $investmentId;
        $entry_date = date("Y-m-d", strtotime(convertDate($globaldate)));
        $entry_time = date("H:i:s", strtotime(convertDate($globaldate)));
        $created_by = 3;
        $created_by_id = $login_user;
        $created_at = date("Y-m-d H:i:s", strtotime(convertDate($globaldate)));
        $updated_at = date("Y-m-d H:i:s", strtotime(convertDate($globaldate)));
        $planDetail = getPlanDetail($planId);
        $type = 4;
        $sub_type = 41;
        $planCode = $planDetail->plan_code;
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
        // ssb
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
        $rdDes = 'Amount received for Account opening ' . $planDetail->name . '(' . $investmentAccountNoRd . ') through  SSB (' . $ssbDetals->account_no . ')';
        $rdDesMem = 'SSB A/C opening (' . $investmentAccountNoRd . ')  through SSB (' . $ssbDetals->account_no . ')';
        // ssb  head entry -
        $allTranRDSSB = CommanTransactionsController::createAllTransactionNewTran($refIdRD, $branch_id, $bank_id = NULL, $bank_ac_id = NULL, $head1rdSSB, $head2rdSSB, $head3rdSSB, $head4rdSSB, $head5rdSSB, 4, 47, $ssb_account_id_from, $associate_id, $memberId, $branch_id_to = NULL, $branch_id_from = NULL, $opening_balance = NULL, $amount, $closing_balance = NULL, $rdDes, 'DR', $headPaymentModeRD, $currency_code, $amount_to_id = NULL, $amount_to_name = NULL, $amount_from_id = NULL, $amount_from_name = NULL, $v_no, $v_date, NULL, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $ssb_tran_id_from, NULL, NULL, NULL);
        $branchClosingSSB = CommanTransactionsController::checkCreateBranchClosingDr($branch_id, $created_at, $amount, 0);
        $memberTranInvest77 = CommanTransactionsController::createMemberTransactionNewTran($refIdRD, 4, 47, $ssb_account_id_from, $associate_id, $ssbDetals->member_id, $branch_id, $bank_id = NULL, $account_id = NULL, $amount, $SSBDescTran, 'DR', $headPaymentModeRD, $currency_code, $amount_to_id = NULL, $amount_to_name = NULL, $amount_from_id = NULL, $amount_from_name = NULL, $v_no, $v_date, NULL, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $ssb_tran_id_from, NULL, NULL, NULL);
        //branch day book entry +
        $daybookInvest = CommanTransactionsController::createBranchDayBookNewTran($refIdRD, $branch_id, $type, $sub_type, $type_id, $associate_id, $memberId, $branch_id_to = NULL, $branch_id_from = NULL, $opening_balance = NULL, $amount, $closing_balance = NULL, $rdDes, $rdDesDR, $rdDesCR, $payment_type_rd, $headPaymentModeRD, $currency_code, $amount_to_id = NULL, $amount_to_name = NULL, $amount_from_id = NULL, $amount_from_name = NULL, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $is_contra = NULL, $contra_id = NULL, $created_at, $updated_at, $createDayBook, $ssb_tran_id_from, NULL, NULL);
        // Investment head entry +
        $allTranInvest = CommanTransactionsController::createAllTransactionNewTran($refIdRD, $branch_id, $bank_id = NULL, $bank_ac_id = NULL, $head1Invest, $head2Invest, $head3Invest, $head4Invest, $head5Invest, $type, $sub_type, $type_id, $associate_id, $memberId, $branch_id_to = NULL, $branch_id_from = NULL, $opening_balance = NULL, $amount, $closing_balance = NULL, $rdDes, $payment_type_rd, $headPaymentModeRD, $currency_code, $amount_to_id = NULL, $amount_to_name = NULL, $amount_from_id = NULL, $amount_from_name = NULL, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $createDayBook, $ssb_tran_id_from, NULL, NULL);
        // Member transaction  +
        $memberTranInvest = CommanTransactionsController::createMemberTransactionNewTran($refIdRD, $type, $sub_type, $type_id, $associate_id, $memberId, $branch_id, $bank_id = NULL, $account_id = NULL, $amount, $rdDesMem, $payment_type_rd, $headPaymentModeRD, $currency_code, $amount_to_id = NULL, $amount_to_name = NULL, $amount_from_id = NULL, $amount_from_name = NULL, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $createDayBook, $ssb_tran_id_from, NULL, NULL);
        /******** Balance   entry ***************/
        $branchClosing = CommanTransactionsController::checkCreateBranchClosing($branch_id, $created_at, $amount, 0);
    }
    public function registerSSBInvestment(Request $request)
    {
        //print_r($_POST);die;
        $associate_no = $request->associate_no;
        $messages1 = array();
        try {
            $member = Member::select('id', 'associate_app_status', 'first_name', 'last_name')->where('associate_no', $request->associate_no)->where('associate_status', 1)->where('is_block', 0)->first();
            if ($member) {
                $token = md5($request->associate_no);
                if ($token == $request->token) {
                    $data = array();
                    $chk = 0;
                    $a = $this->getCategoryTree($parent_id = $member->id, $tree_array = array());
                    $b[] = array('member_id' => $member->id, 'status' => 1, 'is_block' => 0);
                    $tree_array1 = array();
                    $c = array_merge($a, $b);
                    foreach ($c as $v) {
                        $tree_array1[] = $v['member_id'];
                    }
                    if ($request->plan_id == '') {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Please select plan.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if (getPlanCode($request->plan_id) != 703) {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Please only saving account plan.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if ($request->agent_code == '') {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Please enter agent code.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if ($request->member_id == '') {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Please enter member id.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if ($request->branch_id == '') {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Please select branch';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if ($request->form_number == '') {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Please enter form no.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if ($request->amount == '') {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Please enter amount.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if ($request->first_nominee_name == '') {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Please enter first nominee name.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if ($request->first_nominee_relation == '') {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Please enter first nominee relation.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if ($request->first_nominee_dob == '') {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Please enter first nominee dob.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if ($request->first_nominee_age == '') {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Please enter first nominee age.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if ($request->first_nominee_gender == '') {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Please select gender.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if ($request->first_nominee_percentage == '') {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Please enter first nominee percentage.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if ($request->second_nominee_name != '' || $request->second_nominee_relation != '' || $request->second_nominee_dob != '' || $request->second_nominee_gender != '' || $request->second_nominee_percentage != '') {
                        if ($request->second_nominee_relation == '') {
                            $chk++;
                            $status = "Error";
                            $code = 201;
                            $result = '';
                            $messages = 'Please enter second nominee relation.';
                            $associate_status = $member->associate_app_status;
                            return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                        }
                        if ($request->second_nominee_dob == '') {
                            $chk++;
                            $status = "Error";
                            $code = 201;
                            $result = '';
                            $messages = 'Please enter second nominee dob.';
                            $associate_status = $member->associate_app_status;
                            return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                        }
                        if ($request->second_nominee_age == '') {
                            $chk++;
                            $status = "Error";
                            $code = 201;
                            $result = '';
                            $messages = 'Please enter second nominee age.';
                            $associate_status = $member->associate_app_status;
                            return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                        }
                        if ($request->second_nominee_gender == '') {
                            $chk++;
                            $status = "Error";
                            $code = 201;
                            $result = '';
                            $messages = 'Please select gender.';
                            $associate_status = $member->associate_app_status;
                            return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                        }
                        if ($request->second_nominee_percentage == '') {
                            $chk++;
                            $status = "Error";
                            $code = 201;
                            $result = '';
                            $messages = 'Please enter second nominee percentage.';
                            $associate_status = $member->associate_app_status;
                            return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                        }
                    }
                    if (($request->second_nominee_percentage + $request->first_nominee_percentage) != 100) {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Nominee percentage should be equal to 100.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    $memberIdAuto = '';
                    $agent_id_associate = '';
                    $db_member_data = Member::where('associate_no', $request->agent_code)->first();
                    if ($db_member_data) {
                        if ($request->associate_no != $request->agent_code) {
                            $member_data = Member::where('associate_no', $request->agent_code)->whereIn('associate_senior_id', $tree_array1)->first();
                        } else {
                            $member_data = Member::where('associate_no', $request->agent_code)->first();
                        }
                        if ($member_data) {
                            $agent_id_associate = $member_data->id;
                        } else {
                            $chk++;
                            $status = "Error";
                            $code = 201;
                            $result = '';
                            $messages = 'Agent code not register by your team.';
                            $associate_status = $member->associate_app_status;
                            return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                        }
                    } else {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Agent code not exist.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    $db_member_data1 = Member::where('member_id', $request->member_id)->first();
                    if ($db_member_data1) {
                        $member_data1 = Member::where('member_id', $request->member_id)->whereIn('associate_id', $tree_array1)->first();
                        if ($member_data1) {
                            $memberIdAuto = $member_data1->id;
                        } else {
                            $chk++;
                            $status = "Error";
                            $code = 201;
                            $result = '';
                            $messages = 'Member not register by your team.';
                            $associate_status = $member->associate_app_status;
                            return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                        }
                    } else {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Member code not exist.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    $member_ssb = SavingAccount::where('member_id', $member->id)->first();
                    if ($member_ssb) {
                        $member_ssb_id = $member_ssb->id;
                        $member_ssb_balance = $member_ssb->balance;
                    } else {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'SSB account number not exist.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if ($request->amount > $member_ssb_balance) {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Sufficient amount not available in your SSB account';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if (SavingAccount::where('member_id', $memberIdAuto)->count() > 0) {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Your saving account already created!';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if ($chk > 0) {
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'All field required.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    } else {
                        $branch_id = $request->branch_id;
                        $getState = Branch::where('id', $branch_id)->first();
                        $stateid = $getState->state_id;
                        $globaldate = checkMonthAvailability(date('d'), date('m'), date('Y'), $stateid);
                        Session::put('created_at', $globaldate);
                        $branchCode = $getState->branch_code;
                        $planId = $request->plan_id;
                        $faCode = getPlanCode($planId);
                        $investmentMiCode = getInvesmentMiCode($planId, $branch_id);
                        if (!empty($investmentMiCode)) {
                            $miCodeAdd = $investmentMiCode->mi_code + 1;
                        } else {
                            $miCodeAdd = 1;
                        }
                        $miCode = str_pad($miCodeAdd, 5, '0', STR_PAD_LEFT);
                        $miCodeBig = str_pad($miCodeAdd, 5, '0', STR_PAD_LEFT);
                        $passbook = '720' . $branchCode . $faCode . $miCodeBig;
                        $investmentAccount = $branchCode . $faCode . $miCode;
                        $is_primary = 0;
                        $dataInv['deposite_amount'] = $request->amount;
                        $dataInv['current_balance'] = $request->amount;
                        $dataInv['payment_mode'] = 4;
                        $dataInv['ssb_account_number'] = $investmentAccount;
                        $dataInv['mi_code'] = $miCode;
                        $dataInv['account_number'] = $investmentAccount;
                        $dataInv['plan_id'] = $planId;
                        $dataInv['form_number'] = $request->form_number;
                        $dataInv['member_id'] = $memberIdAuto;
                        $dataInv['associate_id'] = $agent_id_associate;
                        $dataInv['branch_id'] = $branch_id;
                        $dataInv['created_at'] = $globaldate;
                        $dataInv['app_login_user_id'] = $member->id;
                        $dataInv['is_app'] = 1;
                        $res = Memberinvestments::create($dataInv);
                        $insertedid = $investmentId = $res->id;
                        $description = 'SSB Account opening';
                        //-----create ssb account or transaction --------------------------- 
                        $ssbArray = array();
                        $miCodePassbook = str_pad($miCode, 5, '0', STR_PAD_LEFT);
                        // pass fa id 20 for passbook 
                        $getfaCodePassbook = getFaCode(20);
                        $faCodePassbook = $getfaCodePassbook->code;
                        $passbookNumber = $faCodePassbook . $branchCode . $faCode . $miCodePassbook;
                        // genarate  member saving account no        
                        $account_no = $investmentAccount;
                        $dataSSBCre['account_no'] = $account_no;
                        $dataSSBCre['member_investments_id'] = $investmentId;
                        $dataSSBCre['is_primary'] = $is_primary;
                        $dataSSBCre['passbook_no'] = $passbookNumber;
                        $dataSSBCre['mi_code'] = $miCode;
                        $dataSSBCre['fa_code'] = $faCode;
                        $dataSSBCre['member_id'] = $memberIdAuto;
                        $dataSSBCre['associate_id'] = $agent_id_associate;
                        $dataSSBCre['branch_id'] = $branch_id;
                        $dataSSBCre['branch_code'] = $branchCode;
                        $dataSSBCre['balance'] = 0;
                        $dataSSBCre['currency_code'] = 'INR';
                        $dataSSBCre['created_by_id'] = $member->id;
                        $dataSSBCre['created_by'] = 3;
                        $dataSSBCre['created_at'] = $globaldate;
                        $dataSSBCre['app_login_user_id'] = $member->id;
                        $dataSSBCre['is_app'] = 1;
                        $ssbAccount = SavingAccount::create($dataSSBCre);
                        $ssbArray['ssb_id'] = $ssbCreate_id = $ssbAccount->id;
                        // create saving account transcation
                        $dataTranCreate['saving_account_id'] = $ssbCreate_id;
                        $dataTranCreate['associate_id'] = $agent_id_associate;
                        $dataTranCreate['branch_id'] = $branch_id;
                        $dataTranCreate['account_no'] = $account_no;
                        $dataTranCreate['type'] = 1;
                        $dataTranCreate['opening_balance'] = $request->amount;
                        $dataTranCreate['deposit'] = $request->amount;
                        $dataTranCreate['withdrawal'] = 0;
                        $dataTranCreate['description'] = $description;
                        $dataTranCreate['currency_code'] = 'INR';
                        $dataTranCreate['payment_type'] = 'CR';
                        $dataTranCreate['payment_mode'] = 4;
                        $dataTranCreate['created_at'] = $globaldate;
                        $dataTranCreate['app_login_user_id'] = $member->id;
                        $dataTranCreate['is_app'] = 1;
                        $ssbAccountTran = SavingAccountTranscation::create($dataTranCreate);
                        $ssbArray['ssb_transaction_id'] = $ssb_Create_tran_id = $ssbAccountTran->id;
                        // update saving account current balance 
                        $balance_update = $request->amount;
                        $ssbBalance = SavingAccount::find($ssbCreate_id);
                        $ssbBalance->balance = $balance_update;
                        $ssbBalance->save();
                        //------------------------------------------------------------
                        $mRes = Member::find($memberIdAuto);
                        $mData['ssb_account'] = $investmentAccount;
                        $mRes->update($mData);
                        $satRefId = CommanTransactionsController::createTransactionReferences($ssb_Create_tran_id, $insertedid);
                        $ssbAccountId = $ssbCreate_id;
                        $amountArraySsb = array('1' => $request['amount']);
                        $amount_deposit_by_name = $member->first_name . ' ' . $member->last_name;
                        $ssbCreateTran = CommanTransactionsController::createTransactionApp($satRefId, 1, $ssbAccountId, $memberIdAuto, $branch_id, $branchCode, $amountArraySsb, 0, $amount_deposit_by_name, $member->id, $investmentAccount, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'CR', $member->id);
                        //---- ssb acount amount debit  --------
                        $sAccount = SavingAccount::where('id', $member_ssb_id)->first();
                        $ssbAccountAmount = $sAccount->balance - $request->amount;
                        $ssb_id = $ssb_pay_id = $sAccount->id;
                        $sResult = SavingAccount::find($ssb_pay_id);
                        $sData['balance'] = $ssbAccountAmount;
                        $sResult->update($sData);
                        $ssb['saving_account_id'] = $ssb_pay_id;
                        $ssb['account_no'] = $sAccount->account_no;
                        $ssb['opening_balance'] = $ssbAccountAmount;
                        $ssb['deposit'] = NULL;
                        $ssb['withdrawal'] = $request->amount;
                        $ssb['description'] = 'Fund Trf. To ' . $investmentAccount . '';
                        $ssb['branch_id'] = $branch_id;
                        $ssb['type'] = 6;
                        $ssb['associate_id'] = $agent_id_associate;
                        $ssb['currency_code'] = 'INR';
                        $ssb['payment_type'] = 'DR';
                        $ssb['payment_mode'] = 3;
                        $ssb['is_renewal'] = 0;
                        $ssb['app_login_user_id'] = $member->id;
                        $ssb['is_app'] = 1;
                        $ssb['created_at'] = $globaldate;
                        $ssbAccountTranLogin = SavingAccountTranscation::create($ssb);
                        $ssb_tran_id_from = $ssbAccountTranLogin->id;
                        $satRefIdLogin = CommanTransactionsController::createTransactionReferences($ssbAccountTranLogin->id, $investmentId);
                        $createTransaction1 = CommanTransactionsController::createTransactionApp($satRefIdLogin, 5, 0, $ssb_pay_id, $branch_id, $branchCode, $amountArraySsb, 4, $member->first_name . ' ' . $member->last_name, $member->id, $sAccount->account_no, 0, NULL, NULL, date("Y-m-d", strtotime(str_replace('/', '-', $globaldate))), NULL, $online_payment_by = NULL, $ssb_pay_id, 'DR', $member->id);
                        $transactionData1['is_renewal'] = 0;
                        $transactionData1['created_at'] = date("Y-m-d H:i:s", strtotime(str_replace('/', '-', $globaldate)));
                        $updateTransaction1 = Transcation::find($createTransaction1);
                        $updateTransaction1->update($transactionData1);
                        TranscationLog::where('transaction_id', $transactionData1)->update(['is_renewal' => 0, 'created_at' => date("Y-m-d H:i:s", strtotime(str_replace('/', '-', $globaldate)))]);
                        $createDayBook = CommanTransactionsController::createDayBookNewApp($ssbCreateTran, $satRefId, 1, $ssbAccountId, $agent_id_associate, $memberIdAuto, $request['amount'], $request['amount'], $withdrawal = 0, $description, $sAccount->account_no, $branch_id, $branchCode, $amountArraySsb, 4, $amount_deposit_by_name, $member->id, $investmentAccount, NULL, NULL, NULL, NULL, NULL, NULL, $ssb_pay_id, 'CR', NULL, NULL, NULL, NULL, NULL, $member->id);
                        $cheque_no = NULL;
                        $cheque_id = NULL;
                        $cheque_date = NULL;
                        $online_transction_no = NULL;
                        $online_transction_date = NULL;
                        $online_deposit_bank_id = NULL;
                        $online_deposit_bank_ac_id = NULL;
                        // -------------------- head implement start---------------
                        $this->investHeadCreateSSBRegister($request['amount'], $globaldate, $investmentId, $planId, $cheque_no, $cheque_id, $cheque_date, $online_transction_no, $online_transction_date, $online_deposit_bank_id, $online_deposit_bank_ac_id, $branch_id, $agent_id_associate, $memberIdAuto, $ssb_pay_id, $createDayBook, 3, $investmentAccount, $member->id, $ssb_tran_id_from);
                        // -------------------- head implement ---------------
                        $transaction['transaction_id'] = $ssbCreateTran;
                        $transaction['investment_id'] = $investmentId;
                        $transaction['transaction_ref_id'] = $satRefId;
                        $transaction['plan_id'] = $planId;
                        $transaction['member_id'] = $memberIdAuto;
                        $transaction['branch_id'] = $branch_id;
                        $transaction['branch_code'] = $branchCode;
                        $transaction['deposite_amount'] = $request['amount'];
                        $transaction['deposite_date'] = date('Y-m-d');
                        $transaction['deposite_month'] = date('m');
                        $transaction['payment_mode'] = 3;
                        $transaction['saving_account_id'] = $ssbCreate_id;
                        $transaction['created_at'] = $globaldate;
                        $Investmentplantransactions = Investmentplantransactions::create($transaction);
                        $fNominee = [
                            'investment_id' => $investmentId,
                            'nominee_type' => 0,
                            'name' => $request['first_nominee_name'],
                            //'second_name' => $request['fn_second_name'],
                            'relation' => $request['first_nominee_relation'],
                            'gender' => $request['first_nominee_gender'],
                            'dob' => date("Y-m-d", strtotime(str_replace('/', '-', $request['first_nominee_dob']))),
                            'age' => $request['first_nominee_age'],
                            'percentage' => $request['first_nominee_percentage'],
                            'created_at' => $globaldate,
                            //'phone_number' => $request['fn_mobile_number'],
                        ];
                        $memberFNomi = Memberinvestmentsnominees::create($fNominee);
                        if ($request['second_nominee_name'] != '' || $request['second_nominee_relation'] != '' || $request['second_nominee_gender'] != '' || $request['second_nominee_dob'] != '' || $request['second_nominee_age'] != '' || $request['second_nominee_percentage'] != '') {
                            $sNominee = [
                                'investment_id' => $investmentId,
                                'nominee_type' => 1,
                                'name' => $request['second_nominee_name'],
                                //'second_name' => $request['fn_second_name'],
                                'relation' => $request['second_nominee_relation'],
                                'gender' => $request['second_nominee_gender'],
                                'dob' => date("Y-m-d", strtotime(str_replace('/', '-', $request['second_nominee_dob']))),
                                'age' => $request['second_nominee_age'],
                                'percentage' => $request['second_nominee_percentage'],
                                'created_at' => $globaldate,
                                //'phone_number' => $request['fn_mobile_number'],
                            ];
                            $memberSNomi = Memberinvestmentsnominees::create($sNominee);
                        }
                        $savingAccountDetail = SavingAccount::where('id', $ssbCreate_id)->first();
                        /*  $text = 'Dear Member, Thank You for Choosing S.B. Micro Finance association Your saving A/C'. $savingAccountDetail->account_no.' is Created on '
                          .$savingAccountDetail->created_at->format('d M Y').' With Rs. '. round($request['amount'],2).' Cur Bal: '. round($savingAccountDetail->balance, 2).'. Thanks Have a good day';
                        $temaplteId = 1207161519023692218; 
                        $contactNumber = array();
                        $memberDetail = Member::find($memberIdAuto);
                        $contactNumber[] = $memberDetail->mobile_no;
                        $sendToMember = new Sms();
                        $sendToMember->sendSms( $contactNumber, $text ,$temaplteId);
                        */
                        $status = "Success";
                        $code = 200;
                        $messages = 'Saving Account Created Successfully.';
                        $result = '';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'token', 'associate_status'), $code);
                    }
                } else {
                    $status = "Error";
                    $code = 201;
                    $messages = 'API token mismatch!';
                    $result = '';
                    $associate_status = $member->associate_app_status;
                    return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                }
            } else {
                $status = "Error";
                $code = 201;
                $messages = 'Something went wrong!';
                $result = '';
                $associate_status = 9;
                return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
            }
        } catch (\Exception $e) {
            $status = "Error";
            $code = 500;
            $messages = $e->getMessage();
            $result = '';
            $associate_status = 9;
            return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
        }
    }
    public function registerKanyadhanInvestment(Request $request)
    {
        //print_r($_POST);die;
        $associate_no = $request->associate_no;
        $messages1 = array();
        try {
            $member = Member::select('id', 'associate_app_status', 'first_name', 'last_name')->where('associate_no', $request->associate_no)->where('associate_status', 1)->where('is_block', 0)->first();
            if ($member) {
                $token = md5($request->associate_no);
                if ($token == $request->token) {
                    $data = array();
                    $chk = 0;
                    $a = $this->getCategoryTree($parent_id = $member->id, $tree_array = array());
                    $b[] = array('member_id' => $member->id, 'status' => 1, 'is_block' => 0);
                    $tree_array1 = array();
                    $c = array_merge($a, $b);
                    foreach ($c as $v) {
                        $tree_array1[] = $v['member_id'];
                    }
                    if ($request->plan_id == '') {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Please select plan.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if (getPlanCode($request->plan_id) != 709) {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Please Select Samraddh Kanyadhan Yojana Plan Only .';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if ($request->agent_code == '') {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Please enter agent code.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if ($request->member_id == '') {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Please enter member id.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if ($request->branch_id == '') {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Please select branch.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if ($request->form_number == '') {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Please enter form no.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    /*if($request->monthly_deposit_amount=='')
                    {
                        $messages1['monthly_deposit_amount']= 'Please enter amount.';
                        $chk++;
                    }*/
                    if ($request->relation_with_guardians == '') {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Please enter relation with guardians.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if ($request->daughter_name == '') {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Please enter daughter name.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if ($request->dob == '') {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Please enter dob.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if ($request->age == '') {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Please enter age.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if ($request->phone_number == '') {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Please enter phone number.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    $memberIdAuto = '';
                    $agent_id_associate = '';
                    $db_member_data = Member::where('associate_no', $request->agent_code)->first();
                    if ($db_member_data) {
                        if ($request->associate_no != $request->agent_code) {
                            $member_data = Member::where('associate_no', $request->agent_code)->whereIn('associate_senior_id', $tree_array1)->first();
                        } else {
                            $member_data = Member::where('associate_no', $request->agent_code)->first();
                        }
                        if ($member_data) {
                            $agent_id_associate = $member_data->id;
                        } else {
                            $chk++;
                            $status = "Error";
                            $code = 201;
                            $result = '';
                            $messages = 'Agent code not register by your team.';
                            $associate_status = $member->associate_app_status;
                            return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                        }
                    } else {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Agent code not exist.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    $db_member_data1 = Member::where('member_id', $request->member_id)->first();
                    if ($db_member_data1) {
                        $member_data1 = Member::where('member_id', $request->member_id)->whereIn('associate_id', $tree_array1)->first();
                        if ($member_data1) {
                            $memberIdAuto = $member_data1->id;
                        } else {
                            $chk++;
                            $status = "Error";
                            $code = 201;
                            $result = '';
                            $messages = 'Member not register by your team.';
                            $associate_status = $member->associate_app_status;
                            return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                        }
                    } else {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Member code not exist.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    $fa_code = 709;
                    $tenure = 18 - $request->age;
                    $investmentAmountK = Investmentplanamounts::where('plan_fa_code', $fa_code)->where('year', $tenure)->select('amount')->first();
                    $principal = $investmentAmountK->amount;
                    $member_ssb = SavingAccount::where('member_id', $member->id)->first();
                    if ($member_ssb) {
                        $member_ssb_id = $member_ssb->id;
                        $member_ssb_balance = $member_ssb->balance;
                    } else {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'SSB account number not exist.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if ($principal > $member_ssb_balance) {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Sufficient amount not available in your SSB account';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if ($chk > 0) {
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'All field required.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    } else {
                        $branch_id = $request->branch_id;
                        $getState = Branch::where('id', $branch_id)->first();
                        $stateid = $getState->state_id;
                        $globaldate = checkMonthAvailability(date('d'), date('m'), date('Y'), $stateid);
                        Session::put('created_at', $globaldate);
                        $branchCode = $getState->branch_code;
                        $planId = $request->plan_id;
                        $faCode = getPlanCode($planId);
                        $investmentMiCode = getInvesmentMiCode($planId, $branch_id);
                        if (!empty($investmentMiCode)) {
                            $miCodeAdd = $investmentMiCode->mi_code + 1;
                        } else {
                            $miCodeAdd = 1;
                        }
                        $miCode = str_pad($miCodeAdd, 5, '0', STR_PAD_LEFT);
                        $miCodeBig = str_pad($miCodeAdd, 5, '0', STR_PAD_LEFT);
                        $passbook = '720' . $branchCode . $faCode . $miCodeBig;
                        $investmentAccount = $branchCode . $faCode . $miCode;
                        if ($tenure >= 8 && $tenure <= 18) {
                            $rate = 11;
                        } else if ($tenure >= 6 && $tenure <= 7) {
                            $rate = 10.50;
                        } else if ($tenure < 6) {
                            $rate = 10;
                        }
                        $ci = 1;
                        $time = $tenure * 12;
                        $irate = $rate / $ci;
                        $year = $time / 12;
                        $result = ($principal * (pow((1 + $irate / 100), $year * $ci) - 1) / (1 - pow((1 + $irate / 100), -$ci / 12)));
                        $amount = $principal;
                        $maturity_amount = round($result);
                        $interest_rate = $rate;
                        $dataInv['mi_code'] = $miCode;
                        $dataInv['account_number'] = $investmentAccount;
                        $dataInv['plan_id'] = $planId;
                        $dataInv['form_number'] = $request->form_number;
                        $dataInv['member_id'] = $memberIdAuto;
                        $dataInv['associate_id'] = $agent_id_associate;
                        $dataInv['branch_id'] = $branch_id;
                        $dataInv['created_at'] = $globaldate;
                        $dataInv['app_login_user_id'] = $member->id;
                        $dataInv['is_app'] = 1;
                        $dataInv['passbook_no'] = $passbook;
                        $dataInv['guardians_relation'] = $request['relation_with_guardians'];
                        $dataInv['daughter_name'] = $request['daughter_name'];
                        $dataInv['phone_number'] = $request['phone_number'];
                        $dataInv['dob'] = date("Y-m-d", strtotime(str_replace('/', '-', $request['dob'])));
                        $dataInv['age'] = $request['age'];
                        $dataInv['deposite_amount'] = $amount;
                        $dataInv['current_balance'] = $amount;
                        $dataInv['tenure'] = $tenure;
                        $dataInv['payment_mode'] = 3;
                        $dataInv['maturity_amount'] = $maturity_amount;
                        $dataInv['interest_rate'] = $interest_rate;
                        $dataInv['maturity_date'] = date("Y-m-d", strtotime('+ ' . ($tenure * 12) . 'months', strtotime(date("Y/m/d"))));
                        $res = Memberinvestments::create($dataInv);
                        $insertedid = $investmentId = $res->id;
                        $description = 'SK Account opening';
                        //------------------------------------------------------------
                        $amountArraySsb = array('1' => $amount);
                        $amount_deposit_by_name = $member->first_name . ' ' . $member->last_name;
                        //---- ssb acount amount debit  --------
                        $sAccount = SavingAccount::where('id', $member_ssb_id)->first();
                        $ssbAccountAmount = $sAccount->balance - $amount;
                        $ssb_id = $ssb_pay_id = $sAccount->id;
                        $sResult = SavingAccount::find($ssb_pay_id);
                        $sData['balance'] = $ssbAccountAmount;
                        $sResult->update($sData);
                        $ssb['saving_account_id'] = $ssb_pay_id;
                        $ssb['account_no'] = $sAccount->account_no;
                        $ssb['opening_balance'] = $ssbAccountAmount;
                        $ssb['deposit'] = NULL;
                        $ssb['withdrawal'] = $amount;
                        $ssb['description'] = 'Payment transfer to SK (' . $investmentAccount . ')';
                        $ssb['branch_id'] = $branch_id;
                        $ssb['type'] = 6;
                        $ssb['associate_id'] = $agent_id_associate;
                        $ssb['currency_code'] = 'INR';
                        $ssb['payment_type'] = 'DR';
                        $ssb['payment_mode'] = 3;
                        $ssb['is_renewal'] = 0;
                        $ssb['app_login_user_id'] = $member->id;
                        $ssb['is_app'] = 1;
                        $ssb['created_at'] = $globaldate;
                        $ssbAccountTranLogin = SavingAccountTranscation::create($ssb);
                        $ssb_tran_id_from = $ssbAccountTranLogin->id;
                        $satRefIdLogin = CommanTransactionsController::createTransactionReferences($ssbAccountTranLogin->id, $investmentId);
                        $createTransaction1 = CommanTransactionsController::createTransactionApp($satRefIdLogin, 5, 0, $ssb_pay_id, $branch_id, $branchCode, $amountArraySsb, 4, $member->first_name . ' ' . $member->last_name, $member->id, $sAccount->account_no, 0, NULL, NULL, date("Y-m-d", strtotime(str_replace('/', '-', $globaldate))), NULL, NULL, $ssb_pay_id, 'DR', $member->id);
                        $transactionData1['is_renewal'] = 0;
                        $transactionData1['created_at'] = date("Y-m-d H:i:s", strtotime(str_replace('/', '-', $globaldate)));
                        $updateTransaction1 = Transcation::find($createTransaction1);
                        $updateTransaction1->update($transactionData1);
                        TranscationLog::where('transaction_id', $transactionData1)->update(['is_renewal' => 0, 'created_at' => date("Y-m-d H:i:s", strtotime(str_replace('/', '-', $globaldate)))]);
                        $createDayBook = CommanTransactionsController::createDayBookNewApp($createTransaction1, $satRefIdLogin, 2, $investmentId, $agent_id_associate, $memberIdAuto, $amount, $amount, $withdrawal = 0, $description, $sAccount->account_no, $branch_id, $branchCode, $amountArraySsb, 4, $amount_deposit_by_name, $member->id, $investmentAccount, NULL, NULL, NULL, NULL, NULL, NULL, $ssb_pay_id, 'CR', NULL, NULL, NULL, NULL, NULL, $member->id);
                        $cheque_no = NULL;
                        $cheque_id = NULL;
                        $cheque_date = NULL;
                        $online_transction_no = NULL;
                        $online_transction_date = NULL;
                        $online_deposit_bank_id = NULL;
                        $online_deposit_bank_ac_id = NULL;
                        // -------------------- head implement start---------------
                        $this->investHeadCreateRegister($amount, $globaldate, $investmentId, $planId, $cheque_no, $cheque_id, $cheque_date, $online_transction_no, $online_transction_date, $online_deposit_bank_id, $online_deposit_bank_ac_id, $branch_id, $agent_id_associate, $memberIdAuto, $ssb_pay_id, $createDayBook, 3, $investmentAccount, $member->id, $ssb_tran_id_from);
                        // -------------------- head implement ---------------
                        /* ------------------ commission genarate-----------------*/
                        $commission = CommanTransactionsController::commissionDistributeInvestment($agent_id_associate, $investmentId, 3, $amount, 1, $planId, $branch_id, $tenure, $createDayBook);
                        $commission_collection = CommanTransactionsController::commissionCollectionInvestment($agent_id_associate, $investmentId, 5, $amount, 1, $planId, $branch_id, $tenure, $createDayBook);
                        /*----- ------  credit business start ---- ---------------*/
                        $creditBusiness = CommanTransactionsController::associateCreditBusiness($agent_id_associate, $investmentId, 1, $amount, 1, $planId, $tenure, $createDayBook);
                        /*----- ------  credit business end ---- ---------------*/
                        /* ------------------ commission genarate-----------------*/
                        $transaction['transaction_id'] = $ssb_tran_id_from;
                        $transaction['investment_id'] = $investmentId;
                        $transaction['transaction_ref_id'] = $satRefIdLogin;
                        $transaction['plan_id'] = $planId;
                        $transaction['member_id'] = $memberIdAuto;
                        $transaction['branch_id'] = $branch_id;
                        $transaction['branch_code'] = $branchCode;
                        $transaction['deposite_amount'] = $amount;
                        $transaction['deposite_date'] = date('Y-m-d');
                        $transaction['deposite_month'] = date('m');
                        $transaction['payment_mode'] = 3;
                        $transaction['saving_account_id'] = $ssb_pay_id;
                        $transaction['created_at'] = $globaldate;
                        $Investmentplantransactions = Investmentplantransactions::create($transaction);
                        $paymentData['ssb_amount'] = $amount;
                        $paymentData['ssb_account_id'] = $ssb_pay_id;
                        $paymentData['ssb_account_no'] = $sAccount->account_no;
                        $paymentData['investment_id'] = $investmentId;
                        $paymentData['created_at'] = $globaldate;
                        $respayments = Memberinvestmentspayments::create($paymentData);
                        $investmentDetail = Memberinvestments::find($investmentId);
                        /*    $text = 'Dear Member, Thank You for Choosing S.B. Micro Finance association Your SK A/c No.'. $investmentDetail->account_number.' is Credited on '.$investmentDetail->created_at->format('d M Y').' With Rs. '. round($investmentDetail->deposite_amount,2).'. Thanks Have a good day';
                            $temaplteId = 1207161519023692218;
                        $contactNumber = array();
                        $memberDetail = Member::find($memberIdAuto);
                        $contactNumber[] = $memberDetail->mobile_no;
                        $sendToMember = new Sms();
                        $sendToMember->sendSms( $contactNumber, $text ,$temaplteId);
                    */
                        $status = "Success";
                        $code = 200;
                        $messages = 'Samraddh Kanyadhan Yojana Account Created Successfully.';
                        $result = '';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'token', 'associate_status'), $code);
                    }
                } else {
                    $status = "Error";
                    $code = 201;
                    $messages = 'API token mismatch!';
                    $result = '';
                    $associate_status = $member->associate_app_status;
                    return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                }
            } else {
                $status = "Error";
                $code = 201;
                $messages = 'Something went wrong!';
                $result = '';
                $associate_status = 9;
                return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
            }
        } catch (\Exception $e) {
            $status = "Error";
            $code = 500;
            $messages = $e->getMessage();
            $result = '';
            $associate_status = 9;
            return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
        }
    }
    public function registerMbInvestment(Request $request)
    {
        //print_r($_POST);die;
        $associate_no = $request->associate_no;
        $messages1 = array();
        try {
            $member = Member::select('id', 'associate_app_status', 'first_name', 'last_name')->where('associate_no', $request->associate_no)->where('associate_status', 1)->where('is_block', 0)->first();
            if ($member) {
                $token = md5($request->associate_no);
                if ($token == $request->token) {
                    $data = array();
                    $chk = 0;
                    $a = $this->getCategoryTree($parent_id = $member->id, $tree_array = array());
                    $b[] = array('member_id' => $member->id, 'status' => 1, 'is_block' => 0);
                    $tree_array1 = array();
                    $c = array_merge($a, $b);
                    foreach ($c as $v) {
                        $tree_array1[] = $v['member_id'];
                    }
                    if ($request->plan_id == '') {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Please select plan.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if (getPlanCode($request->plan_id) != 708) {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Please Select Special Samraddh Money Back Plan Only .';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if ($request->agent_code == '') {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Please enter agent code.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if ($request->member_id == '') {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Please enter member id.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if ($request->branch_id == '') {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Please select branch.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if ($request->form_number == '') {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Please enter form no.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if ($request->amount == '') {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Please enter amount.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if ($request->first_nominee_name == '') {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Please enter first nominee name.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if ($request->first_nominee_relation == '') {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Please enter first nominee relation.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if ($request->first_nominee_dob == '') {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Please enter first nominee dob.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if ($request->first_nominee_age == '') {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Please enter first nominee age.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if ($request->first_nominee_gender == '') {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Please select gender.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if ($request->first_nominee_percentage == '') {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Please enter first nominee percentage.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if ($request->second_nominee_name != '' || $request->second_nominee_relation != '' || $request->second_nominee_dob != '' || $request->second_nominee_gender != '' || $request->second_nominee_percentage != '') {
                        if ($request->second_nominee_relation == '') {
                            $chk++;
                            $status = "Error";
                            $code = 201;
                            $result = '';
                            $messages = 'Please enter second nominee relation.';
                            $associate_status = $member->associate_app_status;
                            return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                        }
                        if ($request->second_nominee_dob == '') {
                            $chk++;
                            $status = "Error";
                            $code = 201;
                            $result = '';
                            $messages = 'Please enter second nominee dob.';
                            $associate_status = $member->associate_app_status;
                            return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                        }
                        if ($request->second_nominee_age == '') {
                            $chk++;
                            $status = "Error";
                            $code = 201;
                            $result = '';
                            $messages = 'Please enter second nominee age.';
                            $associate_status = $member->associate_app_status;
                            return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                        }
                        if ($request->second_nominee_gender == '') {
                            $chk++;
                            $status = "Error";
                            $code = 201;
                            $result = '';
                            $messages = 'Please select gender.';
                            $associate_status = $member->associate_app_status;
                            return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                        }
                        if ($request->second_nominee_percentage == '') {
                            $chk++;
                            $status = "Error";
                            $code = 201;
                            $result = '';
                            $messages = 'Please enter second nominee percentage.';
                            $associate_status = $member->associate_app_status;
                            return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                        }
                    }
                    if (($request->second_nominee_percentage + $request->first_nominee_percentage) != 100) {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Nominee percentage should be equal to 100.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if ($request->ssb_account_no == '') {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Please enter member ssb account no.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    $memberIdAuto = '';
                    $agent_id_associate = '';
                    $db_member_data = Member::where('associate_no', $request->agent_code)->first();
                    if ($db_member_data) {
                        if ($request->associate_no != $request->agent_code) {
                            $member_data = Member::where('associate_no', $request->agent_code)->whereIn('associate_senior_id', $tree_array1)->first();
                        } else {
                            $member_data = Member::where('associate_no', $request->agent_code)->first();
                        }
                        if ($member_data) {
                            $agent_id_associate = $member_data->id;
                        } else {
                            $chk++;
                            $status = "Error";
                            $code = 201;
                            $result = '';
                            $messages = 'Agent code not register by your team.';
                            $associate_status = $member->associate_app_status;
                            return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                        }
                    } else {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Agent code not exist.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    $db_member_data1 = Member::where('member_id', $request->member_id)->first();
                    if ($db_member_data1) {
                        $member_data1 = Member::where('member_id', $request->member_id)->whereIn('associate_id', $tree_array1)->first();
                        if ($member_data1) {
                            $memberIdAuto = $member_data1->id;
                        } else {
                            $chk++;
                            $status = "Error";
                            $code = 201;
                            $result = '';
                            $messages = 'Member not register by your team.';
                            $associate_status = $member->associate_app_status;
                            return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                        }
                    } else {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Member code not exist.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    $memberSsbExit = SavingAccount::where('account_no', $request->ssb_account_no)->where('member_id', $memberIdAuto)->first();
                    if ($memberSsbExit) {
                        $memberssbacountIdExit = $memberSsbExit->id;
                    } else {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'SSB Account not match with this member id.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    $member_ssb = SavingAccount::where('member_id', $member->id)->first();
                    if ($member_ssb) {
                        $member_ssb_id = $member_ssb->id;
                        $member_ssb_balance = $member_ssb->balance;
                    } else {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'SSB account number not exist.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if ($request->amount > $member_ssb_balance) {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Sufficient amount not available in your SSB account';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if ($chk > 0) {
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'All field required.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    } else {
                        $branch_id = $request->branch_id;
                        $getState = Branch::where('id', $branch_id)->first();
                        $stateid = $getState->state_id;
                        $globaldate = checkMonthAvailability(date('d'), date('m'), date('Y'), $stateid);
                        Session::put('created_at', $globaldate);
                        $branchCode = $getState->branch_code;
                        $planId = $request->plan_id;
                        $faCode = getPlanCode($planId);
                        $investmentMiCode = getInvesmentMiCode($planId, $branch_id);
                        if (!empty($investmentMiCode)) {
                            $miCodeAdd = $investmentMiCode->mi_code + 1;
                        } else {
                            $miCodeAdd = 1;
                        }
                        $miCode = str_pad($miCodeAdd, 5, '0', STR_PAD_LEFT);
                        $miCodeBig = str_pad($miCodeAdd, 5, '0', STR_PAD_LEFT);
                        $passbook = '720' . $branchCode . $faCode . $miCodeBig;
                        $investmentAccount = $branchCode . $faCode . $miCode;
                        $amount = $request->amount;
                        $principal = $amount;
                        $time = 12;
                        $tenure = 7;
                        $rate = 9;
                        $ci = 1;
                        $irate = 8 / $ci;
                        $year = $time / 12;
                        $freq = 4;
                        $perYearSixtyPecent = (($principal * 12) * 60 / 100);
                        $carryAmount = 0;
                        $carryForwardInterest = 0;
                        $oldMaturity = 0;
                        $maturity = 0;
                        for ($j = 1; $j <= $tenure; $j++) {
                            $perYearWithInterest = 0;
                            for ($i = 1; $i <= $time; $i++) {
                                $perYearWithInterest = $perYearWithInterest + ($principal * pow((1 + (($rate / 100) / $freq)), $freq * (($time - $i + 1) / 12)));
                            }
                            if ($j > 1) {
                                $carryForwardInterest = ($oldMaturity * (pow((1 + $rate / 100), 1)));
                                $maturity = round($perYearWithInterest + $carryForwardInterest);
                                $oldMaturity = round($maturity - $perYearSixtyPecent);
                            } else {
                                $oldMaturity = round($perYearWithInterest - $perYearSixtyPecent);
                                $maturity = $maturity + $oldMaturity;
                            }
                        }
                        $amount = $principal;
                        $maturity_amount = round($maturity);
                        $interest_rate = $rate;
                        $dataInv['mi_code'] = $miCode;
                        $dataInv['account_number'] = $investmentAccount;
                        $dataInv['plan_id'] = $planId;
                        $dataInv['form_number'] = $request->form_number;
                        $dataInv['member_id'] = $memberIdAuto;
                        $dataInv['associate_id'] = $agent_id_associate;
                        $dataInv['branch_id'] = $branch_id;
                        $dataInv['created_at'] = $globaldate;
                        $dataInv['app_login_user_id'] = $member->id;
                        $dataInv['is_app'] = 1;
                        $dataInv['passbook_no'] = $passbook;
                        $dataInv['ssb_account_number'] = $request['ssb_account_no'];
                        $dataInv['deposite_amount'] = $amount;
                        $dataInv['current_balance'] = $amount;
                        $dataInv['tenure'] = $tenure;
                        $dataInv['payment_mode'] = 3;
                        $dataInv['maturity_amount'] = $maturity_amount;
                        $dataInv['interest_rate'] = $interest_rate;
                        $dataInv['maturity_date'] = date("Y-m-d", strtotime('+ ' . ($tenure * 12) . 'months', strtotime(date("Y/m/d"))));
                        $res = Memberinvestments::create($dataInv);
                        $insertedid = $investmentId = $res->id;
                        $description = 'SMB Account opening';
                        //------------------------------------------------------------
                        $amountArraySsb = array('1' => $amount);
                        $amount_deposit_by_name = $member->first_name . ' ' . $member->last_name;
                        //---- ssb acount amount debit  --------
                        $sAccount = SavingAccount::where('id', $member_ssb_id)->first();
                        $ssbAccountAmount = $sAccount->balance - $amount;
                        $ssb_id = $ssb_pay_id = $sAccount->id;
                        $sResult = SavingAccount::find($ssb_pay_id);
                        $sData['balance'] = $ssbAccountAmount;
                        $sResult->update($sData);
                        $ssb['saving_account_id'] = $ssb_pay_id;
                        $ssb['account_no'] = $sAccount->account_no;
                        $ssb['opening_balance'] = $ssbAccountAmount;
                        $ssb['deposit'] = NULL;
                        $ssb['withdrawal'] = $amount;
                        $ssb['description'] = 'Payment transfer to SMB (' . $investmentAccount . ')';
                        $ssb['branch_id'] = $branch_id;
                        $ssb['type'] = 6;
                        $ssb['associate_id'] = $agent_id_associate;
                        $ssb['currency_code'] = 'INR';
                        $ssb['payment_type'] = 'DR';
                        $ssb['payment_mode'] = 3;
                        $ssb['is_renewal'] = 0;
                        $ssb['app_login_user_id'] = $member->id;
                        $ssb['is_app'] = 1;
                        $ssb['created_at'] = $globaldate;
                        $ssbAccountTranLogin = SavingAccountTranscation::create($ssb);
                        $ssb_tran_id_from = $ssbAccountTranLogin->id;
                        $satRefIdLogin = CommanTransactionsController::createTransactionReferences($ssbAccountTranLogin->id, $investmentId);
                        $createTransaction1 = CommanTransactionsController::createTransactionApp($satRefIdLogin, 5, 0, $ssb_pay_id, $branch_id, $branchCode, $amountArraySsb, 4, $member->first_name . ' ' . $member->last_name, $member->id, $sAccount->account_no, 0, NULL, NULL, date("Y-m-d", strtotime(str_replace('/', '-', $globaldate))), NULL, NULL, $ssb_pay_id, 'DR', $member->id);
                        $transactionData1['is_renewal'] = 0;
                        $transactionData1['created_at'] = date("Y-m-d H:i:s", strtotime(str_replace('/', '-', $globaldate)));
                        $updateTransaction1 = Transcation::find($createTransaction1);
                        $updateTransaction1->update($transactionData1);
                        TranscationLog::where('transaction_id', $transactionData1)->update(['is_renewal' => 0, 'created_at' => date("Y-m-d H:i:s", strtotime(str_replace('/', '-', $globaldate)))]);
                        $createDayBook = CommanTransactionsController::createDayBookNewApp($createTransaction1, $satRefIdLogin, 2, $investmentId, $agent_id_associate, $memberIdAuto, $amount, $amount, $withdrawal = 0, $description, $sAccount->account_no, $branch_id, $branchCode, $amountArraySsb, 4, $amount_deposit_by_name, $member->id, $investmentAccount, NULL, NULL, NULL, NULL, NULL, NULL, $ssb_pay_id, 'CR', NULL, NULL, NULL, NULL, NULL, $member->id);
                        $cheque_no = NULL;
                        $cheque_id = NULL;
                        $cheque_date = NULL;
                        $online_transction_no = NULL;
                        $online_transction_date = NULL;
                        $online_deposit_bank_id = NULL;
                        $online_deposit_bank_ac_id = NULL;
                        // -------------------- head implement start---------------
                        $this->investHeadCreateRegister($amount, $globaldate, $investmentId, $planId, $cheque_no, $cheque_id, $cheque_date, $online_transction_no, $online_transction_date, $online_deposit_bank_id, $online_deposit_bank_ac_id, $branch_id, $agent_id_associate, $memberIdAuto, $ssb_pay_id, $createDayBook, 3, $investmentAccount, $member->id, $ssb_tran_id_from);
                        // -------------------- head implement End ---------------
                        /* ------------------ commission genarate Start-----------------*/
                        $commission = CommanTransactionsController::commissionDistributeInvestment($agent_id_associate, $investmentId, 3, $amount, 1, $planId, $branch_id, $tenure, $createDayBook);
                        $commission_collection = CommanTransactionsController::commissionCollectionInvestment($agent_id_associate, $investmentId, 5, $amount, 1, $planId, $branch_id, $tenure, $createDayBook);
                        /*----- ------  credit business start ---- ---------------*/
                        $creditBusiness = CommanTransactionsController::associateCreditBusiness($agent_id_associate, $investmentId, 1, $amount, 1, $planId, $tenure, $createDayBook);
                        /*----- ------  credit business end ---- ---------------*/
                        /* ------------------ commission genarate End-----------------*/
                        $fNominee = [
                            'investment_id' => $investmentId,
                            'nominee_type' => 0,
                            'name' => $request['first_nominee_name'],
                            'relation' => $request['first_nominee_relation'],
                            'gender' => $request['first_nominee_gender'],
                            'dob' => date("Y-m-d", strtotime(str_replace('/', '-', $request['first_nominee_dob']))),
                            'age' => $request['first_nominee_age'],
                            'percentage' => $request['first_nominee_percentage'],
                            'created_at' => $globaldate,
                        ];
                        $memberFNomi = Memberinvestmentsnominees::create($fNominee);
                        if ($request['second_nominee_name'] != '' || $request['second_nominee_relation'] != '' || $request['second_nominee_gender'] != '' || $request['second_nominee_dob'] != '' || $request['second_nominee_age'] != '' || $request['second_nominee_percentage'] != '') {
                            $sNominee = [
                                'investment_id' => $investmentId,
                                'nominee_type' => 1,
                                'name' => $request['second_nominee_name'],
                                'relation' => $request['second_nominee_relation'],
                                'gender' => $request['second_nominee_gender'],
                                'dob' => date("Y-m-d", strtotime(str_replace('/', '-', $request['second_nominee_dob']))),
                                'age' => $request['second_nominee_age'],
                                'percentage' => $request['second_nominee_percentage'],
                                'created_at' => $globaldate,
                            ];
                            $memberSNomi = Memberinvestmentsnominees::create($sNominee);
                        }
                        $transaction['transaction_id'] = $ssb_tran_id_from;
                        $transaction['investment_id'] = $investmentId;
                        $transaction['transaction_ref_id'] = $satRefIdLogin;
                        $transaction['plan_id'] = $planId;
                        $transaction['member_id'] = $memberIdAuto;
                        $transaction['branch_id'] = $branch_id;
                        $transaction['branch_code'] = $branchCode;
                        $transaction['deposite_amount'] = $amount;
                        $transaction['deposite_date'] = date('Y-m-d');
                        $transaction['deposite_month'] = date('m');
                        $transaction['payment_mode'] = 3;
                        $transaction['saving_account_id'] = $ssb_pay_id;
                        $transaction['created_at'] = $globaldate;
                        $Investmentplantransactions = Investmentplantransactions::create($transaction);
                        $paymentData['ssb_amount'] = $amount;
                        $paymentData['ssb_account_id'] = $ssb_pay_id;
                        $paymentData['ssb_account_no'] = $sAccount->account_no;
                        $paymentData['investment_id'] = $investmentId;
                        $paymentData['created_at'] = $globaldate;
                        $respayments = Memberinvestmentspayments::create($paymentData);
                        $memberInvestData = Memberinvestments::with('ssb')->find($investmentId);
                        /* $text = 'Dear Member, Thank You for Choosing S.B. Micro Finance association Your Saving A/C '. $memberInvestData->ssb_account_number.' is Created on '.$memberInvestData->created_at->format('d M Y') . ' with Rs. 100.00 CR, Money Back A/c No. '.$memberInvestData->account_number.' is Created on '.$memberInvestData->created_at->format('d M Y').' with Rs. '.round($memberInvestData->deposite_amount,2).' CR. Have a good day';
                        $temaplteId = 1207161519138416891; 
                        $contactNumber = array();
                        $memberDetail = Member::find($memberIdAuto);
                        $contactNumber[] = $memberDetail->mobile_no;
                        $sendToMember = new Sms();
                        $sendToMember->sendSms( $contactNumber, $text ,$temaplteId);
                        */
                        $status = "Success";
                        $code = 200;
                        $messages = 'Special Samraddh Money Back Account Created Successfully.';
                        $result = '';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'token', 'associate_status'), $code);
                    }
                } else {
                    $status = "Error";
                    $code = 201;
                    $messages = 'API token mismatch!';
                    $result = '';
                    $associate_status = $member->associate_app_status;
                    return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                }
            } else {
                $status = "Error";
                $code = 201;
                $messages = 'Something went wrong!';
                $result = '';
                $associate_status = 9;
                return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
            }
        } catch (\Exception $e) {
            $status = "Error";
            $code = 500;
            $messages = $e->getMessage();
            $result = '';
            $associate_status = 9;
            return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
        }
    }
    public function registerFFDInvestment(Request $request)
    {
        //print_r($_POST);die;
        $associate_no = $request->associate_no;
        $messages1 = array();
        try {
            $member = Member::select('id', 'associate_app_status', 'first_name', 'last_name')->where('associate_no', $request->associate_no)->where('associate_status', 1)->where('is_block', 0)->first();
            if ($member) {
                $token = md5($request->associate_no);
                if ($token == $request->token) {
                    $data = array();
                    $chk = 0;
                    $a = $this->getCategoryTree($parent_id = $member->id, $tree_array = array());
                    $b[] = array('member_id' => $member->id, 'status' => 1, 'is_block' => 0);
                    $tree_array1 = array();
                    $c = array_merge($a, $b);
                    foreach ($c as $v) {
                        $tree_array1[] = $v['member_id'];
                    }
                    if ($request->plan_id == '') {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Please select plan.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if (getPlanCode($request->plan_id) != 705) {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Please Select Flexi Fixed Deposit Plan Only .';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if ($request->agent_code == '') {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Please enter agent code.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if ($request->member_id == '') {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Please enter member id.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if ($request->branch_id == '') {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Please select branch.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if ($request->form_number == '') {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Please enter form no.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if ($request->amount == '') {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Please enter amount.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if ($request->first_nominee_name == '') {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Please enter first nominee name.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if ($request->first_nominee_relation == '') {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Please enter first nominee relation.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if ($request->first_nominee_dob == '') {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Please enter first nominee dob.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if ($request->first_nominee_age == '') {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Please enter first nominee age.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if ($request->first_nominee_gender == '') {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Please select gender.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if ($request->first_nominee_percentage == '') {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Please enter first nominee percentage.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if ($request->second_nominee_name != '' || $request->second_nominee_relation != '' || $request->second_nominee_dob != '' || $request->second_nominee_gender != '' || $request->second_nominee_percentage != '') {
                        if ($request->second_nominee_relation == '') {
                            $chk++;
                            $status = "Error";
                            $code = 201;
                            $result = '';
                            $messages = 'Please enter second nominee relation.';
                            $associate_status = $member->associate_app_status;
                            return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                        }
                        if ($request->second_nominee_dob == '') {
                            $chk++;
                            $status = "Error";
                            $code = 201;
                            $result = '';
                            $messages = 'Please enter second nominee dob.';
                            $associate_status = $member->associate_app_status;
                            return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                        }
                        if ($request->second_nominee_age == '') {
                            $chk++;
                            $status = "Error";
                            $code = 201;
                            $result = '';
                            $messages = 'Please enter second nominee age.';
                            $associate_status = $member->associate_app_status;
                            return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                        }
                        if ($request->second_nominee_gender == '') {
                            $chk++;
                            $status = "Error";
                            $code = 201;
                            $result = '';
                            $messages = 'Please select gender.';
                            $associate_status = $member->associate_app_status;
                            return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                        }
                        if ($request->second_nominee_percentage == '') {
                            $chk++;
                            $status = "Error";
                            $code = 201;
                            $result = '';
                            $messages = 'Please enter second nominee percentage.';
                            $associate_status = $member->associate_app_status;
                            return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                        }
                    }
                    if (($request->second_nominee_percentage + $request->first_nominee_percentage) != 100) {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Nominee percentage should be equal to 100.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if ($request->tenure == '') {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Please enter tenure.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    $memberIdAuto = '';
                    $agent_id_associate = '';
                    $db_member_data = Member::where('associate_no', $request->agent_code)->first();
                    if ($db_member_data) {
                        if ($request->associate_no != $request->agent_code) {
                            $member_data = Member::where('associate_no', $request->agent_code)->whereIn('associate_senior_id', $tree_array1)->first();
                        } else {
                            $member_data = Member::where('associate_no', $request->agent_code)->first();
                        }
                        if ($member_data) {
                            $agent_id_associate = $member_data->id;
                        } else {
                            $chk++;
                            $status = "Error";
                            $code = 201;
                            $result = '';
                            $messages = 'Agent code not register by your team.';
                            $associate_status = $member->associate_app_status;
                            return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                        }
                    } else {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Agent code not exist.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    $db_member_data1 = Member::where('member_id', $request->member_id)->first();
                    if ($db_member_data1) {
                        $member_data1 = Member::where('member_id', $request->member_id)->whereIn('associate_id', $tree_array1)->first();
                        if ($member_data1) {
                            $memberIdAuto = $member_data1->id;
                        } else {
                            $chk++;
                            $status = "Error";
                            $code = 201;
                            $result = '';
                            $messages = 'Member not register by your team.';
                            $associate_status = $member->associate_app_status;
                            return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                        }
                    } else {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Member code not exist.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    $member_ssb = SavingAccount::where('member_id', $member->id)->first();
                    if ($member_ssb) {
                        $member_ssb_id = $member_ssb->id;
                        $member_ssb_balance = $member_ssb->balance;
                    } else {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'SSB account number not exist.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if ($request->amount > $member_ssb_balance) {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Sufficient amount not available in your SSB account';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if ($chk > 0) {
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'All field required.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    } else {
                        $branch_id = $request->branch_id;
                        $getState = Branch::where('id', $branch_id)->first();
                        $stateid = $getState->state_id;
                        $globaldate = checkMonthAvailability(date('d'), date('m'), date('Y'), $stateid);
                        Session::put('created_at', $globaldate);
                        $branchCode = $getState->branch_code;
                        $planId = $request->plan_id;
                        $faCode = getPlanCode($planId);
                        $investmentMiCode = getInvesmentMiCode($planId, $branch_id);
                        if (!empty($investmentMiCode)) {
                            $miCodeAdd = $investmentMiCode->mi_code + 1;
                        } else {
                            $miCodeAdd = 1;
                        }
                        $miCode = str_pad($miCodeAdd, 5, '0', STR_PAD_LEFT);
                        $miCodeBig = str_pad($miCodeAdd, 5, '0', STR_PAD_LEFT);
                        $passbook = '720' . $branchCode . $faCode . $miCodeBig;
                        $certificate = '719' . $branchCode . $faCode . $miCodeBig;
                        $investmentAccount = $branchCode . $faCode . $miCode;
                        $amount = $request->amount;
                        $tenure = $request->tenure;
                        $principal = $amount;
                        $time = $tenure;
                        if ($time >= 0 && $time <= 36) {
                            $rate = 8;
                        } else if ($time >= 37 && $time <= 48) {
                            $rate = 8.25;
                        } else if ($time >= 49 && $time <= 60) {
                            $rate = 8.50;
                        } else if ($time >= 61 && $time <= 72) {
                            $rate = 8.75;
                        } else if ($time >= 73 && $time <= 84) {
                            $rate = 9;
                        } else if ($time >= 85 && $time <= 96) {
                            $rate = 9.50;
                        } else if ($time >= 97 && $time <= 108) {
                            $rate = 10;
                        } else if ($time >= 109 && $time <= 120) {
                            $rate = 11;
                        }
                        $ci = 1;
                        $irate = $rate / $ci;
                        $year = $time / 12;
                        $result = ($principal * (pow((1 + $irate / 100), $year)));
                        $amount = $principal;
                        $maturity_amount = round($result);
                        $interest_rate = $rate;
                        $dataInv['mi_code'] = $miCode;
                        $dataInv['account_number'] = $investmentAccount;
                        $dataInv['plan_id'] = $planId;
                        $dataInv['form_number'] = $request->form_number;
                        $dataInv['member_id'] = $memberIdAuto;
                        $dataInv['associate_id'] = $agent_id_associate;
                        $dataInv['branch_id'] = $branch_id;
                        $dataInv['created_at'] = $globaldate;
                        $dataInv['app_login_user_id'] = $member->id;
                        $dataInv['is_app'] = 1;
                        $dataInv['certificate_no'] = $certificate;
                        $dataInv['deposite_amount'] = $amount;
                        $dataInv['current_balance'] = $amount;
                        $dataInv['tenure'] = $tenure / 12;
                        $dataInv['payment_mode'] = 3;
                        $dataInv['maturity_amount'] = $maturity_amount;
                        $dataInv['interest_rate'] = $interest_rate;
                        $dataInv['maturity_date'] = date("Y-m-d", strtotime('+ ' . ($tenure) . 'months', strtotime(date("Y/m/d"))));
                        if ($tenure == 12) {
                            $tenurefacode = $faCode . '001';
                        } elseif ($tenure == 24) {
                            $tenurefacode = $faCode . '002';
                        } elseif ($tenure == 36) {
                            $tenurefacode = $faCode . '003';
                        } elseif ($tenure == 48) {
                            $tenurefacode = $faCode . '004';
                        } elseif ($tenure == 60) {
                            $tenurefacode = $faCode . '005';
                        } elseif ($tenure == 72) {
                            $tenurefacode = $faCode . '006';
                        } elseif ($tenure == 84) {
                            $tenurefacode = $faCode . '007';
                        } elseif ($tenure == 96) {
                            $tenurefacode = $faCode . '008';
                        } elseif ($tenure == 108) {
                            $tenurefacode = $faCode . '009';
                        } elseif ($tenure == 120) {
                            $tenurefacode = $faCode . '010';
                        }
                        $dataInv['tenure_fa_code'] = $tenurefacode;
                        $res = Memberinvestments::create($dataInv);
                        $insertedid = $investmentId = $res->id;
                        $description = 'FFD Account opening';
                        //------------------------------------------------------------
                        $amountArraySsb = array('1' => $amount);
                        $amount_deposit_by_name = $member->first_name . ' ' . $member->last_name;
                        //---- ssb acount amount debit  --------
                        $sAccount = SavingAccount::where('id', $member_ssb_id)->first();
                        $ssbAccountAmount = $sAccount->balance - $amount;
                        $ssb_id = $ssb_pay_id = $sAccount->id;
                        $sResult = SavingAccount::find($ssb_pay_id);
                        $sData['balance'] = $ssbAccountAmount;
                        $sResult->update($sData);
                        $ssb['saving_account_id'] = $ssb_pay_id;
                        $ssb['account_no'] = $sAccount->account_no;
                        $ssb['opening_balance'] = $ssbAccountAmount;
                        $ssb['deposit'] = NULL;
                        $ssb['withdrawal'] = $amount;
                        $ssb['description'] = 'Payment transfer to FFD (' . $investmentAccount . ')';
                        $ssb['branch_id'] = $branch_id;
                        $ssb['type'] = 6;
                        $ssb['associate_id'] = $agent_id_associate;
                        $ssb['currency_code'] = 'INR';
                        $ssb['payment_type'] = 'DR';
                        $ssb['payment_mode'] = 3;
                        $ssb['is_renewal'] = 0;
                        $ssb['app_login_user_id'] = $member->id;
                        $ssb['is_app'] = 1;
                        $ssb['created_at'] = $globaldate;
                        $ssbAccountTranLogin = SavingAccountTranscation::create($ssb);
                        $ssb_tran_id_from = $ssbAccountTranLogin->id;
                        $satRefIdLogin = CommanTransactionsController::createTransactionReferences($ssbAccountTranLogin->id, $investmentId);
                        $createTransaction1 = CommanTransactionsController::createTransactionApp($satRefIdLogin, 5, 0, $ssb_pay_id, $branch_id, $branchCode, $amountArraySsb, 4, $member->first_name . ' ' . $member->last_name, $member->id, $sAccount->account_no, 0, NULL, NULL, date("Y-m-d", strtotime(str_replace('/', '-', $globaldate))), NULL, NULL, $ssb_pay_id, 'DR', $member->id);
                        $transactionData1['is_renewal'] = 0;
                        $transactionData1['created_at'] = date("Y-m-d H:i:s", strtotime(str_replace('/', '-', $globaldate)));
                        $updateTransaction1 = Transcation::find($createTransaction1);
                        $updateTransaction1->update($transactionData1);
                        TranscationLog::where('transaction_id', $transactionData1)->update(['is_renewal' => 0, 'created_at' => date("Y-m-d H:i:s", strtotime(str_replace('/', '-', $globaldate)))]);
                        $createDayBook = CommanTransactionsController::createDayBookNewApp($createTransaction1, $satRefIdLogin, 2, $investmentId, $agent_id_associate, $memberIdAuto, $amount, $amount, $withdrawal = 0, $description, $sAccount->account_no, $branch_id, $branchCode, $amountArraySsb, 4, $amount_deposit_by_name, $member->id, $investmentAccount, NULL, NULL, NULL, NULL, NULL, NULL, $ssb_pay_id, 'CR', NULL, NULL, NULL, NULL, NULL, $member->id);
                        $cheque_no = NULL;
                        $cheque_id = NULL;
                        $cheque_date = NULL;
                        $online_transction_no = NULL;
                        $online_transction_date = NULL;
                        $online_deposit_bank_id = NULL;
                        $online_deposit_bank_ac_id = NULL;
                        // -------------------- head implement start---------------
                        $this->investHeadCreateRegister($amount, $globaldate, $investmentId, $planId, $cheque_no, $cheque_id, $cheque_date, $online_transction_no, $online_transction_date, $online_deposit_bank_id, $online_deposit_bank_ac_id, $branch_id, $agent_id_associate, $memberIdAuto, $ssb_pay_id, $createDayBook, 3, $investmentAccount, $member->id, $ssb_tran_id_from);
                        // -------------------- head implement End ---------------
                        /* ------------------ commission genarate Start-----------------*/
                        $commission = CommanTransactionsController::commissionDistributeInvestment($agent_id_associate, $investmentId, 3, $amount, 1, $planId, $branch_id, $tenure, $createDayBook);
                        $commission_collection = CommanTransactionsController::commissionCollectionInvestment($agent_id_associate, $investmentId, 5, $amount, 1, $planId, $branch_id, $tenure, $createDayBook);
                        /*----- ------  credit business start ---- ---------------*/
                        $creditBusiness = CommanTransactionsController::associateCreditBusiness($agent_id_associate, $investmentId, 1, $amount, 1, $planId, $tenure, $createDayBook);
                        /*----- ------  credit business end ---- ---------------*/
                        /* ------------------ commission genarate End-----------------*/
                        $fNominee = [
                            'investment_id' => $investmentId,
                            'nominee_type' => 0,
                            'name' => $request['first_nominee_name'],
                            'relation' => $request['first_nominee_relation'],
                            'gender' => $request['first_nominee_gender'],
                            'dob' => date("Y-m-d", strtotime(str_replace('/', '-', $request['first_nominee_dob']))),
                            'age' => $request['first_nominee_age'],
                            'percentage' => $request['first_nominee_percentage'],
                            'created_at' => $globaldate,
                        ];
                        $memberFNomi = Memberinvestmentsnominees::create($fNominee);
                        if ($request['second_nominee_name'] != '' || $request['second_nominee_relation'] != '' || $request['second_nominee_gender'] != '' || $request['second_nominee_dob'] != '' || $request['second_nominee_age'] != '' || $request['second_nominee_percentage'] != '') {
                            $sNominee = [
                                'investment_id' => $investmentId,
                                'nominee_type' => 1,
                                'name' => $request['second_nominee_name'],
                                'relation' => $request['second_nominee_relation'],
                                'gender' => $request['second_nominee_gender'],
                                'dob' => date("Y-m-d", strtotime(str_replace('/', '-', $request['second_nominee_dob']))),
                                'age' => $request['second_nominee_age'],
                                'percentage' => $request['second_nominee_percentage'],
                                'created_at' => $globaldate,
                            ];
                            $memberSNomi = Memberinvestmentsnominees::create($sNominee);
                        }
                        $transaction['transaction_id'] = $ssb_tran_id_from;
                        $transaction['investment_id'] = $investmentId;
                        $transaction['transaction_ref_id'] = $satRefIdLogin;
                        $transaction['plan_id'] = $planId;
                        $transaction['member_id'] = $memberIdAuto;
                        $transaction['branch_id'] = $branch_id;
                        $transaction['branch_code'] = $branchCode;
                        $transaction['deposite_amount'] = $amount;
                        $transaction['deposite_date'] = date('Y-m-d');
                        $transaction['deposite_month'] = date('m');
                        $transaction['payment_mode'] = 3;
                        $transaction['saving_account_id'] = $ssb_pay_id;
                        $transaction['created_at'] = $globaldate;
                        $Investmentplantransactions = Investmentplantransactions::create($transaction);
                        $paymentData['ssb_amount'] = $amount;
                        $paymentData['ssb_account_id'] = $ssb_pay_id;
                        $paymentData['ssb_account_no'] = $sAccount->account_no;
                        $paymentData['investment_id'] = $investmentId;
                        $paymentData['created_at'] = $globaldate;
                        $respayments = Memberinvestmentspayments::create($paymentData);
                        $investmentDetail = Memberinvestments::find($investmentId);
                        /*    
                        $text = 'Dear Member, Thank You for Choosing S.B. Micro Finance association Your FFD A/c No.'. $investmentDetail->account_number.' is Credited on '.$investmentDetail->created_at->format('d M Y').' With Rs. '.round($investmentDetail->deposite_amount,2).'. Thanks Have a good day';
                        $temaplteId = 1207161519023692218; 
                        $contactNumber = array();
                        $memberDetail = Member::find($memberIdAuto);
                        $contactNumber[] = $memberDetail->mobile_no;
                        $sendToMember = new Sms();
                        $sendToMember->sendSms( $contactNumber, $text ,$temaplteId);
                        */
                        $status = "Success";
                        $code = 200;
                        $messages = 'Flexi Fixed Deposit Account Created Successfully.';
                        $result = '';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'token', 'associate_status'), $code);
                    }
                } else {
                    $status = "Error";
                    $code = 201;
                    $messages = 'API token mismatch!';
                    $result = '';
                    $associate_status = $member->associate_app_status;
                    return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                }
            } else {
                $status = "Error";
                $code = 201;
                $messages = 'Something went wrong!';
                $result = '';
                $associate_status = 9;
                return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
            }
        } catch (\Exception $e) {
            $status = "Error";
            $code = 500;
            $messages = $e->getMessage();
            $result = '';
            $associate_status = 9;
            return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
        }
    }
    public function registerFRDInvestment(Request $request)
    {
        $associate_no = $request->associate_no;
        $messages1 = array();
        try {
            $member = Member::select('id', 'associate_app_status', 'first_name', 'last_name')->where('associate_no', $request->associate_no)->where('associate_status', 1)->where('is_block', 0)->first();
            if ($member) {
                $token = md5($request->associate_no);
                if ($token == $request->token) {
                    $data = array();
                    $chk = 0;
                    $a = $this->getCategoryTree($parent_id = $member->id, $tree_array = array());
                    $b[] = array('member_id' => $member->id, 'status' => 1, 'is_block' => 0);
                    $tree_array1 = array();
                    $c = array_merge($a, $b);
                    foreach ($c as $v) {
                        $tree_array1[] = $v['member_id'];
                    }
                    if ($request->plan_id == '') {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Please select plan.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if (getPlanCode($request->plan_id) != 707) {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Please Select Flexi Recurring Deposit Plan Only .';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if ($request->agent_code == '') {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Please enter agent code.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if ($request->member_id == '') {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Please enter member id.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if ($request->branch_id == '') {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Please select branch.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if ($request->form_number == '') {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Please enter form no.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if ($request->amount == '') {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Please enter amount.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if ($request->first_nominee_name == '') {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Please enter first nominee name.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if ($request->first_nominee_relation == '') {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Please enter first nominee relation.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if ($request->first_nominee_dob == '') {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Please enter first nominee dob.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if ($request->first_nominee_age == '') {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Please enter first nominee age.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if ($request->first_nominee_gender == '') {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Please select gender.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if ($request->first_nominee_percentage == '') {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Please enter first nominee percentage.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if ($request->second_nominee_name != '' || $request->second_nominee_relation != '' || $request->second_nominee_dob != '' || $request->second_nominee_gender != '' || $request->second_nominee_percentage != '') {
                        if ($request->second_nominee_relation == '') {
                            $chk++;
                            $status = "Error";
                            $code = 201;
                            $result = '';
                            $messages = 'Please enter second nominee relation.';
                            $associate_status = $member->associate_app_status;
                            return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                        }
                        if ($request->second_nominee_dob == '') {
                            $chk++;
                            $status = "Error";
                            $code = 201;
                            $result = '';
                            $messages = 'Please enter second nominee dob.';
                            $associate_status = $member->associate_app_status;
                            return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                        }
                        if ($request->second_nominee_age == '') {
                            $chk++;
                            $status = "Error";
                            $code = 201;
                            $result = '';
                            $messages = 'Please enter second nominee age.';
                            $associate_status = $member->associate_app_status;
                            return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                        }
                        if ($request->second_nominee_gender == '') {
                            $chk++;
                            $status = "Error";
                            $code = 201;
                            $result = '';
                            $messages = 'Please select gender.';
                            $associate_status = $member->associate_app_status;
                            return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                        }
                        if ($request->second_nominee_percentage == '') {
                            $chk++;
                            $status = "Error";
                            $code = 201;
                            $result = '';
                            $messages = 'Please enter second nominee percentage.';
                            $associate_status = $member->associate_app_status;
                            return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                        }
                    }
                    if (($request->second_nominee_percentage + $request->first_nominee_percentage) != 100) {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Nominee percentage should be equal to 100.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if ($request->tenure == '') {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Please enter tenure.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    $memberIdAuto = '';
                    $agent_id_associate = '';
                    $db_member_data = Member::where('associate_no', $request->agent_code)->first();
                    if ($db_member_data) {
                        if ($request->associate_no != $request->agent_code) {
                            $member_data = Member::where('associate_no', $request->agent_code)->whereIn('associate_senior_id', $tree_array1)->first();
                        } else {
                            $member_data = Member::where('associate_no', $request->agent_code)->first();
                        }
                        if ($member_data) {
                            $agent_id_associate = $member_data->id;
                        } else {
                            $chk++;
                            $status = "Error";
                            $code = 201;
                            $result = '';
                            $messages = 'Agent code not register by your team.';
                            $associate_status = $member->associate_app_status;
                            return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                        }
                    } else {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Agent code not exist.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    $db_member_data1 = Member::where('member_id', $request->member_id)->first();
                    if ($db_member_data1) {
                        $member_data1 = Member::where('member_id', $request->member_id)->whereIn('associate_id', $tree_array1)->first();
                        if ($member_data1) {
                            $memberIdAuto = $member_data1->id;
                        } else {
                            $chk++;
                            $status = "Error";
                            $code = 201;
                            $result = '';
                            $messages = 'Member not register by your team.';
                            $associate_status = $member->associate_app_status;
                            return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                        }
                    } else {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Member code not exist.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    $member_ssb = SavingAccount::where('member_id', $member->id)->first();
                    if ($member_ssb) {
                        $member_ssb_id = $member_ssb->id;
                        $member_ssb_balance = $member_ssb->balance;
                    } else {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'SSB account number not exist.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if ($request->amount > $member_ssb_balance) {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Sufficient amount not available in your SSB account';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if ($chk > 0) {
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'All field required.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    } else {
                        $branch_id = $request->branch_id;
                        $getState = Branch::where('id', $branch_id)->first();
                        $stateid = $getState->state_id;
                        $globaldate = checkMonthAvailability(date('d'), date('m'), date('Y'), $stateid);
                        Session::put('created_at', $globaldate);
                        $branchCode = $getState->branch_code;
                        $planId = $request->plan_id;
                        $faCode = getPlanCode($planId);
                        $investmentMiCode = getInvesmentMiCode($planId, $branch_id);
                        if (!empty($investmentMiCode)) {
                            $miCodeAdd = $investmentMiCode->mi_code + 1;
                        } else {
                            $miCodeAdd = 1;
                        }
                        $miCode = str_pad($miCodeAdd, 5, '0', STR_PAD_LEFT);
                        $miCodeBig = str_pad($miCodeAdd, 5, '0', STR_PAD_LEFT);
                        $passbook = '720' . $branchCode . $faCode . $miCodeBig;
                        $certificate = '719' . $branchCode . $faCode . $miCodeBig;
                        $investmentAccount = $branchCode . $faCode . $miCode;
                        $amount = $request->amount;
                        $tenure = $request->tenure;
                        $principal = $amount;
                        $time = $tenure;
                        if ($time >= 0 && $time <= 12) {
                            $rate = 5;
                        } else if ($time >= 13 && $time <= 24) {
                            $rate = 6;
                        } else if ($time >= 25 && $time <= 36) {
                            $rate = 6.50;
                        } else if ($time >= 37 && $time <= 48) {
                            $rate = 7;
                        } else if ($time >= 49 && $time <= 60) {
                            $rate = 9;
                        }
                        $ci = 1;
                        $irate = $rate / $ci;
                        $year = $time / 12;
                        $freq = 4;
                        $maturity = 0;
                        for ($i = 1; $i <= $time; $i++) {
                            $maturity += $principal * pow((1 + (($rate / 100) / $freq)), $freq * (($time - $i + 1) / 12));
                        }
                        $result = $maturity;
                        $amount = $principal;
                        $maturity_amount = round($result);
                        $interest_rate = $rate;
                        $dataInv['mi_code'] = $miCode;
                        $dataInv['account_number'] = $investmentAccount;
                        $dataInv['plan_id'] = $planId;
                        $dataInv['form_number'] = $request->form_number;
                        $dataInv['member_id'] = $memberIdAuto;
                        $dataInv['associate_id'] = $agent_id_associate;
                        $dataInv['branch_id'] = $branch_id;
                        $dataInv['created_at'] = $globaldate;
                        $dataInv['app_login_user_id'] = $member->id;
                        $dataInv['is_app'] = 1;
                        $dataInv['passbook_no'] = $passbook;
                        $dataInv['deposite_amount'] = $amount;
                        $dataInv['current_balance'] = $amount;
                        $dataInv['tenure'] = $tenure / 12;
                        $dataInv['payment_mode'] = 3;
                        $dataInv['maturity_amount'] = $maturity_amount;
                        $dataInv['interest_rate'] = $interest_rate;
                        $dataInv['maturity_date'] = date("Y-m-d", strtotime('+ ' . ($tenure) . 'months', strtotime(date("Y/m/d"))));
                        $res = Memberinvestments::create($dataInv);
                        $insertedid = $investmentId = $res->id;
                        $description = 'FRD Account opening';
                        //------------------------------------------------------------
                        $amountArraySsb = array('1' => $amount);
                        $amount_deposit_by_name = $member->first_name . ' ' . $member->last_name;
                        //---- ssb acount amount debit  --------
                        $sAccount = SavingAccount::where('id', $member_ssb_id)->first();
                        $ssbAccountAmount = $sAccount->balance - $amount;
                        $ssb_id = $ssb_pay_id = $sAccount->id;
                        $sResult = SavingAccount::find($ssb_pay_id);
                        $sData['balance'] = $ssbAccountAmount;
                        $sResult->update($sData);
                        $ssb['saving_account_id'] = $ssb_pay_id;
                        $ssb['account_no'] = $sAccount->account_no;
                        $ssb['opening_balance'] = $ssbAccountAmount;
                        $ssb['deposit'] = NULL;
                        $ssb['withdrawal'] = $amount;
                        $ssb['description'] = 'Payment transfer to FRD (' . $investmentAccount . ')';
                        $ssb['branch_id'] = $branch_id;
                        $ssb['type'] = 6;
                        $ssb['associate_id'] = $agent_id_associate;
                        $ssb['currency_code'] = 'INR';
                        $ssb['payment_type'] = 'DR';
                        $ssb['payment_mode'] = 3;
                        $ssb['is_renewal'] = 0;
                        $ssb['app_login_user_id'] = $member->id;
                        $ssb['is_app'] = 1;
                        $ssb['created_at'] = $globaldate;
                        $ssbAccountTranLogin = SavingAccountTranscation::create($ssb);
                        $ssb_tran_id_from = $ssbAccountTranLogin->id;
                        $satRefIdLogin = CommanTransactionsController::createTransactionReferences($ssbAccountTranLogin->id, $investmentId);
                        $createTransaction1 = CommanTransactionsController::createTransactionApp($satRefIdLogin, 5, 0, $ssb_pay_id, $branch_id, $branchCode, $amountArraySsb, 4, $member->first_name . ' ' . $member->last_name, $member->id, $sAccount->account_no, 0, NULL, NULL, date("Y-m-d", strtotime(str_replace('/', '-', $globaldate))), NULL, NULL, $ssb_pay_id, 'DR', $member->id);
                        $transactionData1['is_renewal'] = 0;
                        $transactionData1['created_at'] = date("Y-m-d H:i:s", strtotime(str_replace('/', '-', $globaldate)));
                        $updateTransaction1 = Transcation::find($createTransaction1);
                        $updateTransaction1->update($transactionData1);
                        TranscationLog::where('transaction_id', $transactionData1)->update(['is_renewal' => 0, 'created_at' => date("Y-m-d H:i:s", strtotime(str_replace('/', '-', $globaldate)))]);
                        $createDayBook = CommanTransactionsController::createDayBookNewApp($createTransaction1, $satRefIdLogin, 2, $investmentId, $agent_id_associate, $memberIdAuto, $amount, $amount, $withdrawal = 0, $description, $sAccount->account_no, $branch_id, $branchCode, $amountArraySsb, 4, $amount_deposit_by_name, $member->id, $investmentAccount, NULL, NULL, NULL, NULL, NULL, NULL, $ssb_pay_id, 'CR', NULL, NULL, NULL, NULL, NULL, $member->id);
                        $cheque_no = NULL;
                        $cheque_id = NULL;
                        $cheque_date = NULL;
                        $online_transction_no = NULL;
                        $online_transction_date = NULL;
                        $online_deposit_bank_id = NULL;
                        $online_deposit_bank_ac_id = NULL;
                        // -------------------- head implement start---------------
                        $this->investHeadCreateRegister($amount, $globaldate, $investmentId, $planId, $cheque_no, $cheque_id, $cheque_date, $online_transction_no, $online_transction_date, $online_deposit_bank_id, $online_deposit_bank_ac_id, $branch_id, $agent_id_associate, $memberIdAuto, $ssb_pay_id, $createDayBook, 3, $investmentAccount, $member->id, $ssb_tran_id_from);
                        // -------------------- head implement End ---------------
                        /* ------------------ commission genarate Start-----------------*/
                        $commission = CommanTransactionsController::commissionDistributeInvestment($agent_id_associate, $investmentId, 3, $amount, 1, $planId, $branch_id, $tenure, $createDayBook);
                        $commission_collection = CommanTransactionsController::commissionCollectionInvestment($agent_id_associate, $investmentId, 5, $amount, 1, $planId, $branch_id, $tenure, $createDayBook);
                        /*----- ------  credit business start ---- ---------------*/
                        $creditBusiness = CommanTransactionsController::associateCreditBusiness($agent_id_associate, $investmentId, 1, $amount, 1, $planId, $tenure, $createDayBook);
                        /*----- ------  credit business end ---- ---------------*/
                        /* ------------------ commission genarate End-----------------*/
                        $fNominee = [
                            'investment_id' => $investmentId,
                            'nominee_type' => 0,
                            'name' => $request['first_nominee_name'],
                            'relation' => $request['first_nominee_relation'],
                            'gender' => $request['first_nominee_gender'],
                            'dob' => date("Y-m-d", strtotime(str_replace('/', '-', $request['first_nominee_dob']))),
                            'age' => $request['first_nominee_age'],
                            'percentage' => $request['first_nominee_percentage'],
                            'created_at' => $globaldate,
                        ];
                        $memberFNomi = Memberinvestmentsnominees::create($fNominee);
                        if ($request['second_nominee_name'] != '' || $request['second_nominee_relation'] != '' || $request['second_nominee_gender'] != '' || $request['second_nominee_dob'] != '' || $request['second_nominee_age'] != '' || $request['second_nominee_percentage'] != '') {
                            $sNominee = [
                                'investment_id' => $investmentId,
                                'nominee_type' => 1,
                                'name' => $request['second_nominee_name'],
                                'relation' => $request['second_nominee_relation'],
                                'gender' => $request['second_nominee_gender'],
                                'dob' => date("Y-m-d", strtotime(str_replace('/', '-', $request['second_nominee_dob']))),
                                'age' => $request['second_nominee_age'],
                                'percentage' => $request['second_nominee_percentage'],
                                'created_at' => $globaldate,
                            ];
                            $memberSNomi = Memberinvestmentsnominees::create($sNominee);
                        }
                        $transaction['transaction_id'] = $ssb_tran_id_from;
                        $transaction['investment_id'] = $investmentId;
                        $transaction['transaction_ref_id'] = $satRefIdLogin;
                        $transaction['plan_id'] = $planId;
                        $transaction['member_id'] = $memberIdAuto;
                        $transaction['branch_id'] = $branch_id;
                        $transaction['branch_code'] = $branchCode;
                        $transaction['deposite_amount'] = $amount;
                        $transaction['deposite_date'] = date('Y-m-d');
                        $transaction['deposite_month'] = date('m');
                        $transaction['payment_mode'] = 3;
                        $transaction['saving_account_id'] = $ssb_pay_id;
                        $transaction['created_at'] = $globaldate;
                        $Investmentplantransactions = Investmentplantransactions::create($transaction);
                        $paymentData['ssb_amount'] = $amount;
                        $paymentData['ssb_account_id'] = $ssb_pay_id;
                        $paymentData['ssb_account_no'] = $sAccount->account_no;
                        $paymentData['investment_id'] = $investmentId;
                        $paymentData['created_at'] = $globaldate;
                        $respayments = Memberinvestmentspayments::create($paymentData);
                        $investmentDetail = Memberinvestments::find($investmentId);
                        /*    
                        $text = 'Dear Member, Thank You for Choosing S.B. Micro Finance association Your FRD A/c No.'. $investmentDetail->account_number.' is Credited on '.$investmentDetail->created_at->format('d M Y').' With Rs. '.round($investmentDetail->deposite_amount,2).'CR. Thanks Have a good day';
                        $temaplteId = 1207161519023692218;
                        $contactNumber = array();
                        $memberDetail = Member::find($memberIdAuto);
                        $contactNumber[] = $memberDetail->mobile_no;
                        $sendToMember = new Sms();
                        $sendToMember->sendSms( $contactNumber, $text ,$temaplteId);
                        */
                        $status = "Success";
                        $code = 200;
                        $messages = 'Flexi Recurring Deposit Account Created Successfully.';
                        $result = '';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'token', 'associate_status'), $code);
                    }
                } else {
                    $status = "Error";
                    $code = 201;
                    $messages = 'API token mismatch!';
                    $result = '';
                    $associate_status = $member->associate_app_status;
                    return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                }
            } else {
                $status = "Error";
                $code = 201;
                $messages = 'Something went wrong!';
                $result = '';
                $associate_status = 9;
                return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
            }
        } catch (\Exception $e) {
            $status = "Error";
            $code = 500;
            $messages = $e->getMessage();
            $result = '';
            $associate_status = 9;
            return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
        }
    }
    public function registerJeevanInvestment(Request $request)
    {
        $associate_no = $request->associate_no;
        $messages1 = array();
        try {
            $member = Member::select('id', 'associate_app_status', 'first_name', 'last_name')->where('associate_no', $request->associate_no)->where('associate_status', 1)->where('is_block', 0)->first();
            if ($member) {
                $token = md5($request->associate_no);
                if ($token == $request->token) {
                    $data = array();
                    $chk = 0;
                    $a = $this->getCategoryTree($parent_id = $member->id, $tree_array = array());
                    $b[] = array('member_id' => $member->id, 'status' => 1, 'is_block' => 0);
                    $tree_array1 = array();
                    $c = array_merge($a, $b);
                    foreach ($c as $v) {
                        $tree_array1[] = $v['member_id'];
                    }
                    if ($request->plan_id == '') {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Please select plan.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if (getPlanCode($request->plan_id) != 713) {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Please Select Samraddh Jeevan Plan Only .';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if ($request->agent_code == '') {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Please enter agent code.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if ($request->member_id == '') {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Please enter member id.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if ($request->branch_id == '') {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Please select branch.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if ($request->form_number == '') {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Please enter form no.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if ($request->amount == '') {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Please enter amount.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if ($request->first_nominee_name == '') {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Please enter first nominee name.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if ($request->first_nominee_relation == '') {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Please enter first nominee relation.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if ($request->first_nominee_dob == '') {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Please enter first nominee dob.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if ($request->first_nominee_age == '') {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Please enter first nominee age.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if ($request->first_nominee_gender == '') {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Please select gender.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if ($request->first_nominee_percentage == '') {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Please enter first nominee percentage.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if ($request->second_nominee_name != '' || $request->second_nominee_relation != '' || $request->second_nominee_dob != '' || $request->second_nominee_gender != '' || $request->second_nominee_percentage != '') {
                        if ($request->second_nominee_relation == '') {
                            $chk++;
                            $status = "Error";
                            $code = 201;
                            $result = '';
                            $messages = 'Please enter second nominee relation.';
                            $associate_status = $member->associate_app_status;
                            return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                        }
                        if ($request->second_nominee_dob == '') {
                            $chk++;
                            $status = "Error";
                            $code = 201;
                            $result = '';
                            $messages = 'Please enter second nominee dob.';
                            $associate_status = $member->associate_app_status;
                            return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                        }
                        if ($request->second_nominee_age == '') {
                            $chk++;
                            $status = "Error";
                            $code = 201;
                            $result = '';
                            $messages = 'Please enter second nominee age.';
                            $associate_status = $member->associate_app_status;
                            return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                        }
                        if ($request->second_nominee_gender == '') {
                            $chk++;
                            $status = "Error";
                            $code = 201;
                            $result = '';
                            $messages = 'Please select gender.';
                            $associate_status = $member->associate_app_status;
                            return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                        }
                        if ($request->second_nominee_percentage == '') {
                            $chk++;
                            $status = "Error";
                            $code = 201;
                            $result = '';
                            $messages = 'Please enter second nominee percentage.';
                            $associate_status = $member->associate_app_status;
                            return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                        }
                    }
                    if (($request->second_nominee_percentage + $request->first_nominee_percentage) != 100) {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Nominee percentage should be equal to 100.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if ($request->ssb_account_no == '') {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Please enter member ssb account no.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    $memberIdAuto = '';
                    $agent_id_associate = '';
                    $db_member_data = Member::where('associate_no', $request->agent_code)->first();
                    if ($db_member_data) {
                        if ($request->associate_no != $request->agent_code) {
                            $member_data = Member::where('associate_no', $request->agent_code)->whereIn('associate_senior_id', $tree_array1)->first();
                        } else {
                            $member_data = Member::where('associate_no', $request->agent_code)->first();
                        }
                        if ($member_data) {
                            $agent_id_associate = $member_data->id;
                        } else {
                            $chk++;
                            $status = "Error";
                            $code = 201;
                            $result = '';
                            $messages = 'Agent code not register by your team.';
                            $associate_status = $member->associate_app_status;
                            return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                        }
                    } else {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Agent code not exist.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    $db_member_data1 = Member::where('member_id', $request->member_id)->first();
                    if ($db_member_data1) {
                        $member_data1 = Member::where('member_id', $request->member_id)->whereIn('associate_id', $tree_array1)->first();
                        if ($member_data1) {
                            $memberIdAuto = $member_data1->id;
                        } else {
                            $chk++;
                            $status = "Error";
                            $code = 201;
                            $result = '';
                            $messages = 'Member not register by your team.';
                            $associate_status = $member->associate_app_status;
                            return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                        }
                    } else {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Member code not exist.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    $memberSsbExit = SavingAccount::where('account_no', $request->ssb_account_no)->where('member_id', $memberIdAuto)->first();
                    if ($memberSsbExit) {
                        $memberssbacountIdExit = $memberSsbExit->id;
                    } else {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'SSB Account not match with this member id.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    $member_ssb = SavingAccount::where('member_id', $member->id)->first();
                    if ($member_ssb) {
                        $member_ssb_id = $member_ssb->id;
                        $member_ssb_balance = $member_ssb->balance;
                    } else {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'SSB account number not exist.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if ($request->amount > $member_ssb_balance) {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Sufficient amount not available in your SSB account';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if ($chk > 0) {
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'All field required.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    } else {
                        $branch_id = $request->branch_id;
                        $getState = Branch::where('id', $branch_id)->first();
                        $stateid = $getState->state_id;
                        $globaldate = checkMonthAvailability(date('d'), date('m'), date('Y'), $stateid);
                        Session::put('created_at', $globaldate);
                        $branchCode = $getState->branch_code;
                        $planId = $request->plan_id;
                        $faCode = getPlanCode($planId);
                        $investmentMiCode = getInvesmentMiCode($planId, $branch_id);
                        if (!empty($investmentMiCode)) {
                            $miCodeAdd = $investmentMiCode->mi_code + 1;
                        } else {
                            $miCodeAdd = 1;
                        }
                        $miCode = str_pad($miCodeAdd, 5, '0', STR_PAD_LEFT);
                        $miCodeBig = str_pad($miCodeAdd, 5, '0', STR_PAD_LEFT);
                        $passbook = '720' . $branchCode . $faCode . $miCodeBig;
                        $certificate = '719' . $branchCode . $faCode . $miCodeBig;
                        $investmentAccount = $branchCode . $faCode . $miCode;
                        $amount = $request->amount;
                        $principal = $amount;
                        $time = $tenure = 84;
                        $rate = 10.50;
                        $ci = 1;
                        $irate = $rate / $ci;
                        $year = $time / 12;
                        $freq = 1;
                        $maturity = 0;
                        for ($i = 1; $i <= $time; $i++) {
                            $maturity += $principal * pow((1 + (($rate / 100) / $freq)), $freq * (($time - $i + 1) / 12));
                        }
                        $result = round($maturity);
                        $amount = $principal;
                        $maturity_amount = round($result);
                        $interest_rate = $rate;
                        $dataInv['mi_code'] = $miCode;
                        $dataInv['account_number'] = $investmentAccount;
                        $dataInv['plan_id'] = $planId;
                        $dataInv['form_number'] = $request->form_number;
                        $dataInv['member_id'] = $memberIdAuto;
                        $dataInv['associate_id'] = $agent_id_associate;
                        $dataInv['branch_id'] = $branch_id;
                        $dataInv['created_at'] = $globaldate;
                        $dataInv['app_login_user_id'] = $member->id;
                        $dataInv['is_app'] = 1;
                        $dataInv['passbook_no'] = $passbook;
                        $dataInv['ssb_account_number'] = $request['ssb_account_no'];
                        $dataInv['deposite_amount'] = $amount;
                        $dataInv['current_balance'] = $amount;
                        $dataInv['tenure'] = $tenure / 12;
                        $dataInv['payment_mode'] = 3;
                        $dataInv['maturity_amount'] = $maturity_amount;
                        $dataInv['interest_rate'] = $interest_rate;
                        $dataInv['maturity_date'] = date("Y-m-d", strtotime('+ ' . ($tenure) . 'months', strtotime(date("Y/m/d"))));
                        $res = Memberinvestments::create($dataInv);
                        $insertedid = $investmentId = $res->id;
                        $description = 'SJ Account opening';
                        //------------------------------------------------------------
                        $amountArraySsb = array('1' => $amount);
                        $amount_deposit_by_name = $member->first_name . ' ' . $member->last_name;
                        //---- ssb acount amount debit  --------
                        $sAccount = SavingAccount::where('id', $member_ssb_id)->first();
                        $ssbAccountAmount = $sAccount->balance - $amount;
                        $ssb_id = $ssb_pay_id = $sAccount->id;
                        $sResult = SavingAccount::find($ssb_pay_id);
                        $sData['balance'] = $ssbAccountAmount;
                        $sResult->update($sData);
                        $ssb['saving_account_id'] = $ssb_pay_id;
                        $ssb['account_no'] = $sAccount->account_no;
                        $ssb['opening_balance'] = $ssbAccountAmount;
                        $ssb['deposit'] = NULL;
                        $ssb['withdrawal'] = $amount;
                        $ssb['description'] = 'Payment transfer to SJ (' . $investmentAccount . ')';
                        $ssb['branch_id'] = $branch_id;
                        $ssb['type'] = 6;
                        $ssb['associate_id'] = $agent_id_associate;
                        $ssb['currency_code'] = 'INR';
                        $ssb['payment_type'] = 'DR';
                        $ssb['payment_mode'] = 3;
                        $ssb['is_renewal'] = 0;
                        $ssb['app_login_user_id'] = $member->id;
                        $ssb['is_app'] = 1;
                        $ssb['created_at'] = $globaldate;
                        $ssbAccountTranLogin = SavingAccountTranscation::create($ssb);
                        $ssb_tran_id_from = $ssbAccountTranLogin->id;
                        $satRefIdLogin = CommanTransactionsController::createTransactionReferences($ssbAccountTranLogin->id, $investmentId);
                        $createTransaction1 = CommanTransactionsController::createTransactionApp($satRefIdLogin, 5, 0, $ssb_pay_id, $branch_id, $branchCode, $amountArraySsb, 4, $member->first_name . ' ' . $member->last_name, $member->id, $sAccount->account_no, 0, NULL, NULL, date("Y-m-d", strtotime(str_replace('/', '-', $globaldate))), NULL, NULL, $ssb_pay_id, 'DR', $member->id);
                        $transactionData1['is_renewal'] = 0;
                        $transactionData1['created_at'] = date("Y-m-d H:i:s", strtotime(str_replace('/', '-', $globaldate)));
                        $updateTransaction1 = Transcation::find($createTransaction1);
                        $updateTransaction1->update($transactionData1);
                        TranscationLog::where('transaction_id', $transactionData1)->update(['is_renewal' => 0, 'created_at' => date("Y-m-d H:i:s", strtotime(str_replace('/', '-', $globaldate)))]);
                        $createDayBook = CommanTransactionsController::createDayBookNewApp($createTransaction1, $satRefIdLogin, 2, $investmentId, $agent_id_associate, $memberIdAuto, $amount, $amount, $withdrawal = 0, $description, $sAccount->account_no, $branch_id, $branchCode, $amountArraySsb, 4, $amount_deposit_by_name, $member->id, $investmentAccount, NULL, NULL, NULL, NULL, NULL, NULL, $ssb_pay_id, 'CR', NULL, NULL, NULL, NULL, NULL, $member->id);
                        $cheque_no = NULL;
                        $cheque_id = NULL;
                        $cheque_date = NULL;
                        $online_transction_no = NULL;
                        $online_transction_date = NULL;
                        $online_deposit_bank_id = NULL;
                        $online_deposit_bank_ac_id = NULL;
                        // -------------------- head implement start---------------
                        $this->investHeadCreateRegister($amount, $globaldate, $investmentId, $planId, $cheque_no, $cheque_id, $cheque_date, $online_transction_no, $online_transction_date, $online_deposit_bank_id, $online_deposit_bank_ac_id, $branch_id, $agent_id_associate, $memberIdAuto, $ssb_pay_id, $createDayBook, 3, $investmentAccount, $member->id, $ssb_tran_id_from);
                        // -------------------- head implement End ---------------
                        /* ------------------ commission genarate Start-----------------*/
                        $commission = CommanTransactionsController::commissionDistributeInvestment($agent_id_associate, $investmentId, 3, $amount, 1, $planId, $branch_id, $tenure, $createDayBook);
                        $commission_collection = CommanTransactionsController::commissionCollectionInvestment($agent_id_associate, $investmentId, 5, $amount, 1, $planId, $branch_id, $tenure, $createDayBook);
                        /*----- ------  credit business start ---- ---------------*/
                        $creditBusiness = CommanTransactionsController::associateCreditBusiness($agent_id_associate, $investmentId, 1, $amount, 1, $planId, $tenure, $createDayBook);
                        /*----- ------  credit business end ---- ---------------*/
                        /* ------------------ commission genarate End-----------------*/
                        $fNominee = [
                            'investment_id' => $investmentId,
                            'nominee_type' => 0,
                            'name' => $request['first_nominee_name'],
                            'relation' => $request['first_nominee_relation'],
                            'gender' => $request['first_nominee_gender'],
                            'dob' => date("Y-m-d", strtotime(str_replace('/', '-', $request['first_nominee_dob']))),
                            'age' => $request['first_nominee_age'],
                            'percentage' => $request['first_nominee_percentage'],
                            'created_at' => $globaldate,
                        ];
                        $memberFNomi = Memberinvestmentsnominees::create($fNominee);
                        if ($request['second_nominee_name'] != '' || $request['second_nominee_relation'] != '' || $request['second_nominee_gender'] != '' || $request['second_nominee_dob'] != '' || $request['second_nominee_age'] != '' || $request['second_nominee_percentage'] != '') {
                            $sNominee = [
                                'investment_id' => $investmentId,
                                'nominee_type' => 1,
                                'name' => $request['second_nominee_name'],
                                'relation' => $request['second_nominee_relation'],
                                'gender' => $request['second_nominee_gender'],
                                'dob' => date("Y-m-d", strtotime(str_replace('/', '-', $request['second_nominee_dob']))),
                                'age' => $request['second_nominee_age'],
                                'percentage' => $request['second_nominee_percentage'],
                                'created_at' => $globaldate,
                            ];
                            $memberSNomi = Memberinvestmentsnominees::create($sNominee);
                        }
                        $transaction['transaction_id'] = $ssb_tran_id_from;
                        $transaction['investment_id'] = $investmentId;
                        $transaction['transaction_ref_id'] = $satRefIdLogin;
                        $transaction['plan_id'] = $planId;
                        $transaction['member_id'] = $memberIdAuto;
                        $transaction['branch_id'] = $branch_id;
                        $transaction['branch_code'] = $branchCode;
                        $transaction['deposite_amount'] = $amount;
                        $transaction['deposite_date'] = date('Y-m-d');
                        $transaction['deposite_month'] = date('m');
                        $transaction['payment_mode'] = 3;
                        $transaction['saving_account_id'] = $ssb_pay_id;
                        $transaction['created_at'] = $globaldate;
                        $Investmentplantransactions = Investmentplantransactions::create($transaction);
                        $paymentData['ssb_amount'] = $amount;
                        $paymentData['ssb_account_id'] = $ssb_pay_id;
                        $paymentData['ssb_account_no'] = $sAccount->account_no;
                        $paymentData['investment_id'] = $investmentId;
                        $paymentData['created_at'] = $globaldate;
                        $respayments = Memberinvestmentspayments::create($paymentData);
                        $memberInvestData = Memberinvestments::with('ssb')->find($investmentId);
                        /*    
                        $text = 'Dear Member, Thank You for Choosing S.B. Micro Finance association Your S. Jeevan A/c No.'.$memberInvestData->account_number.' is Credited on '.$memberInvestData->created_at->format('d M Y') . ' With Rs. '.round($memberInvestData->deposite_amount,2).'. Thanks Have a good day'; 
                        $temaplteId = 1207161519023692218;
                        $contactNumber = array();
                        $memberDetail = Member::find($memberIdAuto);
                        $contactNumber[] = $memberDetail->mobile_no;
                        $sendToMember = new Sms();
                        $sendToMember->sendSms( $contactNumber, $text ,$temaplteId);
                        */
                        $status = "Success";
                        $code = 200;
                        $messages = 'Samraddh Jeevan Account Created Successfully.';
                        $result = '';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'token', 'associate_status'), $code);
                    }
                } else {
                    $status = "Error";
                    $code = 201;
                    $messages = 'API token mismatch!';
                    $result = '';
                    $associate_status = $member->associate_app_status;
                    return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                }
            } else {
                $status = "Error";
                $code = 201;
                $messages = 'Something went wrong!';
                $result = '';
                $associate_status = 9;
                return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
            }
        } catch (\Exception $e) {
            $status = "Error";
            $code = 500;
            $messages = $e->getMessage();
            $result = '';
            $associate_status = 9;
            return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
        }
    }
    public function registerSddInvestment(Request $request)
    {
        // print_r($_POST);die;
        $associate_no = $request->associate_no;
        $messages1 = array();
        try {
            $member = Member::select('id', 'associate_app_status', 'first_name', 'last_name')->where('associate_no', $request->associate_no)->where('associate_status', 1)->where('is_block', 0)->first();
            if ($member) {
                $token = md5($request->associate_no);
                if ($token == $request->token) {
                    $data = array();
                    $chk = 0;
                    $a = $this->getCategoryTree($parent_id = $member->id, $tree_array = array());
                    $b[] = array('member_id' => $member->id, 'status' => 1, 'is_block' => 0);
                    $tree_array1 = array();
                    $c = array_merge($a, $b);
                    foreach ($c as $v) {
                        $tree_array1[] = $v['member_id'];
                    }
                    if ($request->plan_id == '') {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Please select plan.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if (getPlanCode($request->plan_id) != 710) {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Please Select Daily Deposit Plan Only .';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if ($request->agent_code == '') {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Please enter agent code.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if ($request->member_id == '') {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Please enter member id.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if ($request->branch_id == '') {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Please select branch.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if ($request->form_number == '') {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Please enter form no.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if ($request->amount == '') {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Please enter amount.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if ($request->first_nominee_name == '') {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Please enter first nominee name.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if ($request->first_nominee_relation == '') {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Please enter first nominee relation.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if ($request->first_nominee_dob == '') {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Please enter first nominee dob.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if ($request->first_nominee_age == '') {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Please enter first nominee age.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if ($request->first_nominee_gender == '') {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Please select gender.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if ($request->first_nominee_percentage == '') {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Please enter first nominee percentage.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if ($request->second_nominee_name != '' || $request->second_nominee_relation != '' || $request->second_nominee_dob != '' || $request->second_nominee_gender != '' || $request->second_nominee_percentage != '') {
                        if ($request->second_nominee_relation == '') {
                            $chk++;
                            $status = "Error";
                            $code = 201;
                            $result = '';
                            $messages = 'Please enter second nominee relation.';
                            $associate_status = $member->associate_app_status;
                            return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                        }
                        if ($request->second_nominee_dob == '') {
                            $chk++;
                            $status = "Error";
                            $code = 201;
                            $result = '';
                            $messages = 'Please enter second nominee dob.';
                            $associate_status = $member->associate_app_status;
                            return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                        }
                        if ($request->second_nominee_age == '') {
                            $chk++;
                            $status = "Error";
                            $code = 201;
                            $result = '';
                            $messages = 'Please enter second nominee age.';
                            $associate_status = $member->associate_app_status;
                            return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                        }
                        if ($request->second_nominee_gender == '') {
                            $chk++;
                            $status = "Error";
                            $code = 201;
                            $result = '';
                            $messages = 'Please select gender.';
                            $associate_status = $member->associate_app_status;
                            return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                        }
                        if ($request->second_nominee_percentage == '') {
                            $chk++;
                            $status = "Error";
                            $code = 201;
                            $result = '';
                            $messages = 'Please enter second nominee percentage.';
                            $associate_status = $member->associate_app_status;
                            return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                        }
                    }
                    if (($request->second_nominee_percentage + $request->first_nominee_percentage) != 100) {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Nominee percentage should be equal to 100.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if ($request->tenure == '') {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Please enter tenure.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    $memberIdAuto = '';
                    $agent_id_associate = '';
                    $db_member_data = Member::where('associate_no', $request->agent_code)->first();
                    if ($db_member_data) {
                        if ($request->associate_no != $request->agent_code) {
                            $member_data = Member::where('associate_no', $request->agent_code)->whereIn('associate_senior_id', $tree_array1)->first();
                        } else {
                            $member_data = Member::where('associate_no', $request->agent_code)->first();
                        }
                        if ($member_data) {
                            $agent_id_associate = $member_data->id;
                        } else {
                            $chk++;
                            $status = "Error";
                            $code = 201;
                            $result = '';
                            $messages = 'Agent code not register by your team.';
                            $associate_status = $member->associate_app_status;
                            return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                        }
                    } else {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Agent code not exist.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    $db_member_data1 = Member::where('member_id', $request->member_id)->first();
                    if ($db_member_data1) {
                        $member_data1 = Member::where('member_id', $request->member_id)->whereIn('associate_id', $tree_array1)->first();
                        if ($member_data1) {
                            $memberIdAuto = $member_data1->id;
                        } else {
                            $chk++;
                            $status = "Error";
                            $code = 201;
                            $result = '';
                            $messages = 'Member not register by your team.';
                            $associate_status = $member->associate_app_status;
                            return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                        }
                    } else {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Member code not exist.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    $member_ssb = SavingAccount::where('member_id', $member->id)->first();
                    if ($member_ssb) {
                        $member_ssb_id = $member_ssb->id;
                        $member_ssb_balance = $member_ssb->balance;
                    } else {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'SSB account number not exist.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if ($request->amount > $member_ssb_balance) {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Sufficient amount not available in your SSB account';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if ($chk > 0) {
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'All field required.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    } else {
                        $branch_id = $request->branch_id;
                        $getState = Branch::where('id', $branch_id)->first();
                        $stateid = $getState->state_id;
                        $globaldate = checkMonthAvailability(date('d'), date('m'), date('Y'), $stateid);
                        Session::put('created_at', $globaldate);
                        $branchCode = $getState->branch_code;
                        $planId = $request->plan_id;
                        $faCode = getPlanCode($planId);
                        $investmentMiCode = getInvesmentMiCode($planId, $branch_id);
                        if (!empty($investmentMiCode)) {
                            $miCodeAdd = $investmentMiCode->mi_code + 1;
                        } else {
                            $miCodeAdd = 1;
                        }
                        $miCode = str_pad($miCodeAdd, 5, '0', STR_PAD_LEFT);
                        $miCodeBig = str_pad($miCodeAdd, 5, '0', STR_PAD_LEFT);
                        $passbook = '720' . $branchCode . $faCode . $miCodeBig;
                        $certificate = '719' . $branchCode . $faCode . $miCodeBig;
                        $investmentAccount = $branchCode . $faCode . $miCode;
                        $amount = $request->amount;
                        $tenure = $request->tenure;
                        $principal = $amount;
                        $time = $tenure;
                        if ($time >= 0 && $time <= 12) {
                            $rate = 6;
                        } else if ($time >= 13 && $time <= 24) {
                            $rate = 6.50;
                        } else if ($time >= 25 && $time <= 36) {
                            $rate = 7;
                        } else if ($time >= 37 && $time <= 60) {
                            $rate = 7.25;
                        }
                        $ci = 12;
                        $freq = 12;
                        $irate = $rate / $ci;
                        $year = $time / 12;
                        $days = $time * 30;
                        $monthlyPricipal = $principal * 30;
                        $maturity = 0;
                        for ($i = 1; $i <= $time; $i++) {
                            $maturity += $monthlyPricipal * pow((1 + (($rate / 100) / $freq)), $freq * (($time - $i + 1) / 12));
                        }
                        $amount = $principal;
                        $maturity_amount = round($maturity);
                        $interest_rate = $rate;
                        $dataInv['mi_code'] = $miCode;
                        $dataInv['account_number'] = $investmentAccount;
                        $dataInv['plan_id'] = $planId;
                        $dataInv['form_number'] = $request->form_number;
                        $dataInv['member_id'] = $memberIdAuto;
                        $dataInv['associate_id'] = $agent_id_associate;
                        $dataInv['branch_id'] = $branch_id;
                        $dataInv['created_at'] = $globaldate;
                        $dataInv['app_login_user_id'] = $member->id;
                        $dataInv['is_app'] = 1;
                        $dataInv['passbook_no'] = $passbook;
                        $dataInv['deposite_amount'] = $amount;
                        $dataInv['current_balance'] = $amount;
                        $dataInv['tenure'] = $tenure / 12;
                        $dataInv['payment_mode'] = 3;
                        $dataInv['maturity_amount'] = $maturity_amount;
                        $dataInv['interest_rate'] = $interest_rate;
                        $dataInv['maturity_date'] = date("Y-m-d", strtotime('+ ' . ($tenure) . 'months', strtotime(date("Y/m/d"))));
                        if ($tenure == 12) {
                            $tenurefacode = $faCode . '001';
                        } elseif ($tenure == 24) {
                            $tenurefacode = $faCode . '002';
                        } elseif ($tenure == 36) {
                            $tenurefacode = $faCode . '003';
                        } elseif ($tenure == 48) {
                            $tenurefacode = $faCode . '004';
                        } elseif ($tenure == 60) {
                            $tenurefacode = $faCode . '005';
                        }
                        $dataInv['tenure_fa_code'] = $tenurefacode;
                        $res = Memberinvestments::create($dataInv);
                        $insertedid = $investmentId = $res->id;
                        $description = 'SDD Account opening';
                        //------------------------------------------------------------
                        $amountArraySsb = array('1' => $amount);
                        $amount_deposit_by_name = $member->first_name . ' ' . $member->last_name;
                        //---- ssb acount amount debit  --------
                        $sAccount = SavingAccount::where('id', $member_ssb_id)->first();
                        $ssbAccountAmount = $sAccount->balance - $amount;
                        $ssb_id = $ssb_pay_id = $sAccount->id;
                        $sResult = SavingAccount::find($ssb_pay_id);
                        $sData['balance'] = $ssbAccountAmount;
                        $sResult->update($sData);
                        $ssb['saving_account_id'] = $ssb_pay_id;
                        $ssb['account_no'] = $sAccount->account_no;
                        $ssb['opening_balance'] = $ssbAccountAmount;
                        $ssb['deposit'] = NULL;
                        $ssb['withdrawal'] = $amount;
                        $ssb['description'] = 'Payment transfer to SDD (' . $investmentAccount . ')';
                        $ssb['branch_id'] = $branch_id;
                        $ssb['type'] = 6;
                        $ssb['associate_id'] = $agent_id_associate;
                        $ssb['currency_code'] = 'INR';
                        $ssb['payment_type'] = 'DR';
                        $ssb['payment_mode'] = 3;
                        $ssb['is_renewal'] = 0;
                        $ssb['app_login_user_id'] = $member->id;
                        $ssb['is_app'] = 1;
                        $ssb['created_at'] = $globaldate;
                        $ssbAccountTranLogin = SavingAccountTranscation::create($ssb);
                        $ssb_tran_id_from = $ssbAccountTranLogin->id;
                        $satRefIdLogin = CommanTransactionsController::createTransactionReferences($ssbAccountTranLogin->id, $investmentId);
                        $createTransaction1 = CommanTransactionsController::createTransactionApp($satRefIdLogin, 5, 0, $ssb_pay_id, $branch_id, $branchCode, $amountArraySsb, 4, $member->first_name . ' ' . $member->last_name, $member->id, $sAccount->account_no, 0, NULL, NULL, date("Y-m-d", strtotime(str_replace('/', '-', $globaldate))), NULL, NULL, $ssb_pay_id, 'DR', $member->id);
                        $transactionData1['is_renewal'] = 0;
                        $transactionData1['created_at'] = date("Y-m-d H:i:s", strtotime(str_replace('/', '-', $globaldate)));
                        $updateTransaction1 = Transcation::find($createTransaction1);
                        $updateTransaction1->update($transactionData1);
                        TranscationLog::where('transaction_id', $transactionData1)->update(['is_renewal' => 0, 'created_at' => date("Y-m-d H:i:s", strtotime(str_replace('/', '-', $globaldate)))]);
                        $createDayBook = CommanTransactionsController::createDayBookNewApp($createTransaction1, $satRefIdLogin, 2, $investmentId, $agent_id_associate, $memberIdAuto, $amount, $amount, $withdrawal = 0, $description, $sAccount->account_no, $branch_id, $branchCode, $amountArraySsb, 4, $amount_deposit_by_name, $member->id, $investmentAccount, NULL, NULL, NULL, NULL, NULL, NULL, $ssb_pay_id, 'CR', NULL, NULL, NULL, NULL, NULL, $member->id);
                        $cheque_no = NULL;
                        $cheque_id = NULL;
                        $cheque_date = NULL;
                        $online_transction_no = NULL;
                        $online_transction_date = NULL;
                        $online_deposit_bank_id = NULL;
                        $online_deposit_bank_ac_id = NULL;
                        // -------------------- head implement start---------------
                        $this->investHeadCreateRegister($amount, $globaldate, $investmentId, $planId, $cheque_no, $cheque_id, $cheque_date, $online_transction_no, $online_transction_date, $online_deposit_bank_id, $online_deposit_bank_ac_id, $branch_id, $agent_id_associate, $memberIdAuto, $ssb_pay_id, $createDayBook, 3, $investmentAccount, $member->id, $ssb_tran_id_from);
                        // -------------------- head implement End ---------------
                        /* ------------------ commission genarate Start-----------------*/
                        $commission = CommanTransactionsController::commissionDistributeInvestment($agent_id_associate, $investmentId, 3, $amount, 1, $planId, $branch_id, $tenure, $createDayBook);
                        $commission_collection = CommanTransactionsController::commissionCollectionInvestment($agent_id_associate, $investmentId, 5, $amount, 1, $planId, $branch_id, $tenure, $createDayBook);
                        /*----- ------  credit business start ---- ---------------*/
                        $creditBusiness = CommanTransactionsController::associateCreditBusiness($agent_id_associate, $investmentId, 1, $amount, 1, $planId, $tenure, $createDayBook);
                        /*----- ------  credit business end ---- ---------------*/
                        /* ------------------ commission genarate End-----------------*/
                        $fNominee = [
                            'investment_id' => $investmentId,
                            'nominee_type' => 0,
                            'name' => $request['first_nominee_name'],
                            'relation' => $request['first_nominee_relation'],
                            'gender' => $request['first_nominee_gender'],
                            'dob' => date("Y-m-d", strtotime(str_replace('/', '-', $request['first_nominee_dob']))),
                            'age' => $request['first_nominee_age'],
                            'percentage' => $request['first_nominee_percentage'],
                            'created_at' => $globaldate,
                        ];
                        $memberFNomi = Memberinvestmentsnominees::create($fNominee);
                        if ($request['second_nominee_name'] != '' || $request['second_nominee_relation'] != '' || $request['second_nominee_gender'] != '' || $request['second_nominee_dob'] != '' || $request['second_nominee_age'] != '' || $request['second_nominee_percentage'] != '') {
                            $sNominee = [
                                'investment_id' => $investmentId,
                                'nominee_type' => 1,
                                'name' => $request['second_nominee_name'],
                                'relation' => $request['second_nominee_relation'],
                                'gender' => $request['second_nominee_gender'],
                                'dob' => date("Y-m-d", strtotime(str_replace('/', '-', $request['second_nominee_dob']))),
                                'age' => $request['second_nominee_age'],
                                'percentage' => $request['second_nominee_percentage'],
                                'created_at' => $globaldate,
                            ];
                            $memberSNomi = Memberinvestmentsnominees::create($sNominee);
                        }
                        $transaction['transaction_id'] = $ssb_tran_id_from;
                        $transaction['investment_id'] = $investmentId;
                        $transaction['transaction_ref_id'] = $satRefIdLogin;
                        $transaction['plan_id'] = $planId;
                        $transaction['member_id'] = $memberIdAuto;
                        $transaction['branch_id'] = $branch_id;
                        $transaction['branch_code'] = $branchCode;
                        $transaction['deposite_amount'] = $amount;
                        $transaction['deposite_date'] = date('Y-m-d');
                        $transaction['deposite_month'] = date('m');
                        $transaction['payment_mode'] = 3;
                        $transaction['saving_account_id'] = $ssb_pay_id;
                        $transaction['created_at'] = $globaldate;
                        $Investmentplantransactions = Investmentplantransactions::create($transaction);
                        $paymentData['ssb_amount'] = $amount;
                        $paymentData['ssb_account_id'] = $ssb_pay_id;
                        $paymentData['ssb_account_no'] = $sAccount->account_no;
                        $paymentData['investment_id'] = $investmentId;
                        $paymentData['created_at'] = $globaldate;
                        $respayments = Memberinvestmentspayments::create($paymentData);
                        $investmentDetail = Memberinvestments::find($investmentId);
                        /*    
                        $text = 'Dear Member, Thank You for Choosing S.B. Micro Finance association Your SDD A/c No.'. $investmentDetail->account_number.' is Credited on ' .$investmentDetail->created_at->format('d M Y').' With Rs. '.round($investmentDetail->deposite_amount,2).'CR. Thanks Have a good day';
                        $temaplteId = 1207161519023692218;
                        $contactNumber = array();
                        $memberDetail = Member::find($memberIdAuto);
                        $contactNumber[] = $memberDetail->mobile_no;
                        $sendToMember = new Sms();
                        $sendToMember->sendSms( $contactNumber, $text ,$temaplteId);
                        */
                        $status = "Success";
                        $code = 200;
                        $messages = 'Daily Deposit Account Created Successfully.';
                        $result = '';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'token', 'associate_status'), $code);
                    }
                } else {
                    $status = "Error";
                    $code = 201;
                    $messages = 'API token mismatch!';
                    $result = '';
                    $associate_status = $member->associate_app_status;
                    return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                }
            } else {
                $status = "Error";
                $code = 201;
                $messages = 'Something went wrong!';
                $result = '';
                $associate_status = 9;
                return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
            }
        } catch (\Exception $e) {
            $status = "Error";
            $code = 500;
            $messages = $e->getMessage();
            $result = '';
            $associate_status = 9;
            return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
        }
    }
    public function registerMISInvestment(Request $request)
    {
        $associate_no = $request->associate_no;
        $messages1 = array();
        try {
            $member = Member::select('id', 'associate_app_status', 'first_name', 'last_name')->where('associate_no', $request->associate_no)->where('associate_status', 1)->where('is_block', 0)->first();
            if ($member) {
                $token = md5($request->associate_no);
                if ($token == $request->token) {
                    $data = array();
                    $chk = 0;
                    $a = $this->getCategoryTree($parent_id = $member->id, $tree_array = array());
                    $b[] = array('member_id' => $member->id, 'status' => 1, 'is_block' => 0);
                    $tree_array1 = array();
                    $c = array_merge($a, $b);
                    foreach ($c as $v) {
                        $tree_array1[] = $v['member_id'];
                    }
                    if ($request->plan_id == '') {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Please select plan.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if (getPlanCode($request->plan_id) != 712) {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Please Select Monthly Income scheme Plan Only .';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if ($request->agent_code == '') {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Please enter agent code.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if ($request->member_id == '') {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Please enter member id.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if ($request->branch_id == '') {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Please select branch.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if ($request->form_number == '') {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Please enter form no.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if ($request->amount == '') {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Please enter amount.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if ($request->first_nominee_name == '') {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Please enter first nominee name.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if ($request->first_nominee_relation == '') {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Please enter first nominee relation.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if ($request->first_nominee_dob == '') {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Please enter first nominee dob.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if ($request->first_nominee_age == '') {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Please enter first nominee age.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if ($request->first_nominee_gender == '') {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Please select gender.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if ($request->first_nominee_percentage == '') {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Please enter first nominee percentage.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if ($request->second_nominee_name != '' || $request->second_nominee_relation != '' || $request->second_nominee_dob != '' || $request->second_nominee_gender != '' || $request->second_nominee_percentage != '') {
                        if ($request->second_nominee_relation == '') {
                            $chk++;
                            $status = "Error";
                            $code = 201;
                            $result = '';
                            $messages = 'Please enter second nominee relation.';
                            $associate_status = $member->associate_app_status;
                            return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                        }
                        if ($request->second_nominee_dob == '') {
                            $chk++;
                            $status = "Error";
                            $code = 201;
                            $result = '';
                            $messages = 'Please enter second nominee dob.';
                            $associate_status = $member->associate_app_status;
                            return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                        }
                        if ($request->second_nominee_age == '') {
                            $chk++;
                            $status = "Error";
                            $code = 201;
                            $result = '';
                            $messages = 'Please enter second nominee age.';
                            $associate_status = $member->associate_app_status;
                            return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                        }
                        if ($request->second_nominee_gender == '') {
                            $chk++;
                            $status = "Error";
                            $code = 201;
                            $result = '';
                            $messages = 'Please select gender.';
                            $associate_status = $member->associate_app_status;
                            return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                        }
                        if ($request->second_nominee_percentage == '') {
                            $chk++;
                            $status = "Error";
                            $code = 201;
                            $result = '';
                            $messages = 'Please enter second nominee percentage.';
                            $associate_status = $member->associate_app_status;
                            return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                        }
                    }
                    if (($request->second_nominee_percentage + $request->first_nominee_percentage) != 100) {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Nominee percentage should be equal to 100.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if ($request->tenure == '') {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Please enter tenure.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if ($request->ssb_account_no == '') {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Please enter member ssb account no.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    $memberIdAuto = '';
                    $agent_id_associate = '';
                    $db_member_data = Member::where('associate_no', $request->agent_code)->first();
                    if ($db_member_data) {
                        if ($request->associate_no != $request->agent_code) {
                            $member_data = Member::where('associate_no', $request->agent_code)->whereIn('associate_senior_id', $tree_array1)->first();
                        } else {
                            $member_data = Member::where('associate_no', $request->agent_code)->first();
                        }
                        if ($member_data) {
                            $agent_id_associate = $member_data->id;
                        } else {
                            $chk++;
                            $status = "Error";
                            $code = 201;
                            $result = '';
                            $messages = 'Agent code not register by your team.';
                            $associate_status = $member->associate_app_status;
                            return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                        }
                    } else {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Agent code not exist.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    $db_member_data1 = Member::where('member_id', $request->member_id)->first();
                    if ($db_member_data1) {
                        $member_data1 = Member::where('member_id', $request->member_id)->whereIn('associate_id', $tree_array1)->first();
                        if ($member_data1) {
                            $memberIdAuto = $member_data1->id;
                        } else {
                            $chk++;
                            $status = "Error";
                            $code = 201;
                            $result = '';
                            $messages = 'Member not register by your team.';
                            $associate_status = $member->associate_app_status;
                            return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                        }
                    } else {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Member code not exist.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    $memberSsbExit = SavingAccount::where('account_no', $request->ssb_account_no)->where('member_id', $memberIdAuto)->first();
                    if ($memberSsbExit) {
                        $memberssbacountIdExit = $memberSsbExit->id;
                    } else {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'SSB Account not match with this member id.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    $member_ssb = SavingAccount::where('member_id', $member->id)->first();
                    if ($member_ssb) {
                        $member_ssb_id = $member_ssb->id;
                        $member_ssb_balance = $member_ssb->balance;
                    } else {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'SSB account number not exist.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if ($request->amount > $member_ssb_balance) {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Sufficient amount not available in your SSB account';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if ($chk > 0) {
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'All field required.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    } else {
                        $branch_id = $request->branch_id;
                        $getState = Branch::where('id', $branch_id)->first();
                        $stateid = $getState->state_id;
                        $globaldate = checkMonthAvailability(date('d'), date('m'), date('Y'), $stateid);
                        Session::put('created_at', $globaldate);
                        $branchCode = $getState->branch_code;
                        $planId = $request->plan_id;
                        $faCode = getPlanCode($planId);
                        $investmentMiCode = getInvesmentMiCode($planId, $branch_id);
                        if (!empty($investmentMiCode)) {
                            $miCodeAdd = $investmentMiCode->mi_code + 1;
                        } else {
                            $miCodeAdd = 1;
                        }
                        $miCode = str_pad($miCodeAdd, 5, '0', STR_PAD_LEFT);
                        $miCodeBig = str_pad($miCodeAdd, 5, '0', STR_PAD_LEFT);
                        $passbook = '720' . $branchCode . $faCode . $miCodeBig;
                        $certificate = '719' . $branchCode . $faCode . $miCodeBig;
                        $investmentAccount = $branchCode . $faCode . $miCode;
                        $amount = $request->amount;
                        $tenure = $request->tenure;
                        $principal = $amount;
                        $time = $tenure;
                        if ($time >= 0 && $time <= 60) {
                            $rate = 10;
                        } else if ($time >= 61 && $time <= 84) {
                            $rate = 10.50;
                        } else if ($time >= 85 && $time <= 120) {
                            $rate = 11;
                        }
                        $ci = 1;
                        $irate = $rate / $ci;
                        $year = $time / 12;
                        $result = ((($principal * $rate) / 12) / 100);
                        $amount = $principal;
                        $maturity_amount = round($result);
                        $interest_rate = $rate;
                        $dataInv['mi_code'] = $miCode;
                        $dataInv['account_number'] = $investmentAccount;
                        $dataInv['plan_id'] = $planId;
                        $dataInv['form_number'] = $request->form_number;
                        $dataInv['member_id'] = $memberIdAuto;
                        $dataInv['associate_id'] = $agent_id_associate;
                        $dataInv['branch_id'] = $branch_id;
                        $dataInv['created_at'] = $globaldate;
                        $dataInv['app_login_user_id'] = $member->id;
                        $dataInv['is_app'] = 1;
                        $dataInv['certificate_no'] = $certificate;
                        $dataInv['deposite_amount'] = $amount;
                        $dataInv['current_balance'] = $amount;
                        $dataInv['tenure'] = $tenure / 12;
                        $dataInv['payment_mode'] = 3;
                        $dataInv['maturity_amount'] = $maturity_amount;
                        $dataInv['interest_rate'] = $interest_rate;
                        $dataInv['maturity_date'] = date("Y-m-d", strtotime('+ ' . ($tenure) . 'months', strtotime(date("Y/m/d"))));
                        $dataInv['ssb_account_number'] = $request['ssb_account_no'];
                        $res = Memberinvestments::create($dataInv);
                        $insertedid = $investmentId = $res->id;
                        $description = 'MIS Account opening';
                        //------------------------------------------------------------
                        $amountArraySsb = array('1' => $amount);
                        $amount_deposit_by_name = $member->first_name . ' ' . $member->last_name;
                        //---- ssb acount amount debit  --------
                        $sAccount = SavingAccount::where('id', $member_ssb_id)->first();
                        $ssbAccountAmount = $sAccount->balance - $amount;
                        $ssb_id = $ssb_pay_id = $sAccount->id;
                        $sResult = SavingAccount::find($ssb_pay_id);
                        $sData['balance'] = $ssbAccountAmount;
                        $sResult->update($sData);
                        $ssb['saving_account_id'] = $ssb_pay_id;
                        $ssb['account_no'] = $sAccount->account_no;
                        $ssb['opening_balance'] = $ssbAccountAmount;
                        $ssb['deposit'] = NULL;
                        $ssb['withdrawal'] = $amount;
                        $ssb['description'] = 'Payment transfer to MIS (' . $investmentAccount . ')';
                        $ssb['branch_id'] = $branch_id;
                        $ssb['type'] = 6;
                        $ssb['associate_id'] = $agent_id_associate;
                        $ssb['currency_code'] = 'INR';
                        $ssb['payment_type'] = 'DR';
                        $ssb['payment_mode'] = 3;
                        $ssb['is_renewal'] = 0;
                        $ssb['app_login_user_id'] = $member->id;
                        $ssb['is_app'] = 1;
                        $ssb['created_at'] = $globaldate;
                        $ssbAccountTranLogin = SavingAccountTranscation::create($ssb);
                        $ssb_tran_id_from = $ssbAccountTranLogin->id;
                        $satRefIdLogin = CommanTransactionsController::createTransactionReferences($ssbAccountTranLogin->id, $investmentId);
                        $createTransaction1 = CommanTransactionsController::createTransactionApp($satRefIdLogin, 5, 0, $ssb_pay_id, $branch_id, $branchCode, $amountArraySsb, 4, $member->first_name . ' ' . $member->last_name, $member->id, $sAccount->account_no, 0, NULL, NULL, date("Y-m-d", strtotime(str_replace('/', '-', $globaldate))), NULL, NULL, $ssb_pay_id, 'DR', $member->id);
                        $transactionData1['is_renewal'] = 0;
                        $transactionData1['created_at'] = date("Y-m-d H:i:s", strtotime(str_replace('/', '-', $globaldate)));
                        $updateTransaction1 = Transcation::find($createTransaction1);
                        $updateTransaction1->update($transactionData1);
                        TranscationLog::where('transaction_id', $transactionData1)->update(['is_renewal' => 0, 'created_at' => date("Y-m-d H:i:s", strtotime(str_replace('/', '-', $globaldate)))]);
                        $createDayBook = CommanTransactionsController::createDayBookNewApp($createTransaction1, $satRefIdLogin, 2, $investmentId, $agent_id_associate, $memberIdAuto, $amount, $amount, $withdrawal = 0, $description, $sAccount->account_no, $branch_id, $branchCode, $amountArraySsb, 4, $amount_deposit_by_name, $member->id, $investmentAccount, NULL, NULL, NULL, NULL, NULL, NULL, $ssb_pay_id, 'CR', NULL, NULL, NULL, NULL, NULL, $member->id);
                        $cheque_no = NULL;
                        $cheque_id = NULL;
                        $cheque_date = NULL;
                        $online_transction_no = NULL;
                        $online_transction_date = NULL;
                        $online_deposit_bank_id = NULL;
                        $online_deposit_bank_ac_id = NULL;
                        // -------------------- head implement start---------------
                        $this->investHeadCreateRegister($amount, $globaldate, $investmentId, $planId, $cheque_no, $cheque_id, $cheque_date, $online_transction_no, $online_transction_date, $online_deposit_bank_id, $online_deposit_bank_ac_id, $branch_id, $agent_id_associate, $memberIdAuto, $ssb_pay_id, $createDayBook, 3, $investmentAccount, $member->id, $ssb_tran_id_from);
                        // -------------------- head implement End ---------------
                        /* ------------------ commission genarate Start-----------------*/
                        $commission = CommanTransactionsController::commissionDistributeInvestment($agent_id_associate, $investmentId, 3, $amount, 1, $planId, $branch_id, $tenure, $createDayBook);
                        $commission_collection = CommanTransactionsController::commissionCollectionInvestment($agent_id_associate, $investmentId, 5, $amount, 1, $planId, $branch_id, $tenure, $createDayBook);
                        /*----- ------  credit business start ---- ---------------*/
                        $creditBusiness = CommanTransactionsController::associateCreditBusiness($agent_id_associate, $investmentId, 1, $amount, 1, $planId, $tenure, $createDayBook);
                        /*----- ------  credit business end ---- ---------------*/
                        /* ------------------ commission genarate End-----------------*/
                        $fNominee = [
                            'investment_id' => $investmentId,
                            'nominee_type' => 0,
                            'name' => $request['first_nominee_name'],
                            'relation' => $request['first_nominee_relation'],
                            'gender' => $request['first_nominee_gender'],
                            'dob' => date("Y-m-d", strtotime(str_replace('/', '-', $request['first_nominee_dob']))),
                            'age' => $request['first_nominee_age'],
                            'percentage' => $request['first_nominee_percentage'],
                            'created_at' => $globaldate,
                        ];
                        $memberFNomi = Memberinvestmentsnominees::create($fNominee);
                        if ($request['second_nominee_name'] != '' || $request['second_nominee_relation'] != '' || $request['second_nominee_gender'] != '' || $request['second_nominee_dob'] != '' || $request['second_nominee_age'] != '' || $request['second_nominee_percentage'] != '') {
                            $sNominee = [
                                'investment_id' => $investmentId,
                                'nominee_type' => 1,
                                'name' => $request['second_nominee_name'],
                                'relation' => $request['second_nominee_relation'],
                                'gender' => $request['second_nominee_gender'],
                                'dob' => date("Y-m-d", strtotime(str_replace('/', '-', $request['second_nominee_dob']))),
                                'age' => $request['second_nominee_age'],
                                'percentage' => $request['second_nominee_percentage'],
                                'created_at' => $globaldate,
                            ];
                            $memberSNomi = Memberinvestmentsnominees::create($sNominee);
                        }
                        $transaction['transaction_id'] = $ssb_tran_id_from;
                        $transaction['investment_id'] = $investmentId;
                        $transaction['transaction_ref_id'] = $satRefIdLogin;
                        $transaction['plan_id'] = $planId;
                        $transaction['member_id'] = $memberIdAuto;
                        $transaction['branch_id'] = $branch_id;
                        $transaction['branch_code'] = $branchCode;
                        $transaction['deposite_amount'] = $amount;
                        $transaction['deposite_date'] = date('Y-m-d');
                        $transaction['deposite_month'] = date('m');
                        $transaction['payment_mode'] = 3;
                        $transaction['saving_account_id'] = $ssb_pay_id;
                        $transaction['created_at'] = $globaldate;
                        $Investmentplantransactions = Investmentplantransactions::create($transaction);
                        $paymentData['ssb_amount'] = $amount;
                        $paymentData['ssb_account_id'] = $ssb_pay_id;
                        $paymentData['ssb_account_no'] = $sAccount->account_no;
                        $paymentData['investment_id'] = $investmentId;
                        $paymentData['created_at'] = $globaldate;
                        $respayments = Memberinvestmentspayments::create($paymentData);
                        $memberInvestData = Memberinvestments::with('ssb')->find($investmentId);
                        /*    
                        $text = 'Dear Member, Thank You for Choosing S.B. Micro Finance association Your MIS A/c No.'.$memberInvestData->account_number.' is Credited on '.$memberInvestData->created_at->format('d M Y').' With Rs. '.round($memberInvestData->deposite_amount,2).'. Thanks Have a good day';
                        $temaplteId = 1207161519023692218;
                        $contactNumber = array();
                        $memberDetail = Member::find($memberIdAuto);
                        $contactNumber[] = $memberDetail->mobile_no;
                        $sendToMember = new Sms();
                        $sendToMember->sendSms( $contactNumber, $text ,$temaplteId);
                        */
                        $status = "Success";
                        $code = 200;
                        $messages = 'Monthly Income scheme Account Created Successfully.';
                        $result = '';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'token', 'associate_status'), $code);
                    }
                } else {
                    $status = "Error";
                    $code = 201;
                    $messages = 'API token mismatch!';
                    $result = '';
                    $associate_status = $member->associate_app_status;
                    return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                }
            } else {
                $status = "Error";
                $code = 201;
                $messages = 'Something went wrong!';
                $result = '';
                $associate_status = 9;
                return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
            }
        } catch (\Exception $e) {
            $status = "Error";
            $code = 500;
            $messages = $e->getMessage();
            $result = '';
            $associate_status = 9;
            return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
        }
    }
    public function registerFDInvestment(Request $request)
    {
        $associate_no = $request->associate_no;
        $messages1 = array();
        try {
            $member = Member::select('id', 'associate_app_status', 'first_name', 'last_name')->where('associate_no', $request->associate_no)->where('associate_status', 1)->where('is_block', 0)->first();
            if ($member) {
                $token = md5($request->associate_no);
                if ($token == $request->token) {
                    $data = array();
                    $chk = 0;
                    $a = $this->getCategoryTree($parent_id = $member->id, $tree_array = array());
                    $b[] = array('member_id' => $member->id, 'status' => 1, 'is_block' => 0);
                    $tree_array1 = array();
                    $c = array_merge($a, $b);
                    foreach ($c as $v) {
                        $tree_array1[] = $v['member_id'];
                    }
                    if ($request->plan_id == '') {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Please select plan.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if (getPlanCode($request->plan_id) != 706) {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Please Select Fixed Deposit Plan Only .';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if ($request->agent_code == '') {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Please enter agent code.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if ($request->member_id == '') {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Please enter member id.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if ($request->branch_id == '') {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Please select branch.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if ($request->form_number == '') {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Please enter form no.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if ($request->amount == '') {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Please enter amount.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if ($request->first_nominee_name == '') {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Please enter first nominee name.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if ($request->first_nominee_relation == '') {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Please enter first nominee relation.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if ($request->first_nominee_dob == '') {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Please enter first nominee dob.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if ($request->first_nominee_age == '') {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Please enter first nominee age.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if ($request->first_nominee_gender == '') {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Please select gender.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if ($request->first_nominee_percentage == '') {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Please enter first nominee percentage.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if ($request->second_nominee_name != '' || $request->second_nominee_relation != '' || $request->second_nominee_dob != '' || $request->second_nominee_gender != '' || $request->second_nominee_percentage != '') {
                        if ($request->second_nominee_relation == '') {
                            $chk++;
                            $status = "Error";
                            $code = 201;
                            $result = '';
                            $messages = 'Please enter second nominee relation.';
                            $associate_status = $member->associate_app_status;
                            return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                        }
                        if ($request->second_nominee_dob == '') {
                            $chk++;
                            $status = "Error";
                            $code = 201;
                            $result = '';
                            $messages = 'Please enter second nominee dob.';
                            $associate_status = $member->associate_app_status;
                            return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                        }
                        if ($request->second_nominee_age == '') {
                            $chk++;
                            $status = "Error";
                            $code = 201;
                            $result = '';
                            $messages = 'Please enter second nominee age.';
                            $associate_status = $member->associate_app_status;
                            return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                        }
                        if ($request->second_nominee_gender == '') {
                            $chk++;
                            $status = "Error";
                            $code = 201;
                            $result = '';
                            $messages = 'Please select gender.';
                            $associate_status = $member->associate_app_status;
                            return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                        }
                        if ($request->second_nominee_percentage == '') {
                            $chk++;
                            $status = "Error";
                            $code = 201;
                            $result = '';
                            $messages = 'Please enter second nominee percentage.';
                            $associate_status = $member->associate_app_status;
                            return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                        }
                    }
                    if (($request->second_nominee_percentage + $request->first_nominee_percentage) != 100) {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Nominee percentage should be equal to 100.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if ($request->tenure == '') {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Please enter tenure.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    $memberIdAuto = '';
                    $agent_id_associate = '';
                    $db_member_data = Member::where('associate_no', $request->agent_code)->first();
                    if ($db_member_data) {
                        if ($request->associate_no != $request->agent_code) {
                            $member_data = Member::where('associate_no', $request->agent_code)->whereIn('associate_senior_id', $tree_array1)->first();
                        } else {
                            $member_data = Member::where('associate_no', $request->agent_code)->first();
                        }
                        if ($member_data) {
                            $agent_id_associate = $member_data->id;
                        } else {
                            $chk++;
                            $status = "Error";
                            $code = 201;
                            $result = '';
                            $messages = 'Agent code not register by your team.';
                            $associate_status = $member->associate_app_status;
                            return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                        }
                    } else {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Agent code not exist.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    $db_member_data1 = Member::where('member_id', $request->member_id)->first();
                    if ($db_member_data1) {
                        $member_data1 = Member::where('member_id', $request->member_id)->whereIn('associate_id', $tree_array1)->first();
                        if ($member_data1) {
                            $memberIdAuto = $member_data1->id;
                        } else {
                            $chk++;
                            $status = "Error";
                            $code = 201;
                            $result = '';
                            $messages = 'Member not register by your team.';
                            $associate_status = $member->associate_app_status;
                            return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                        }
                    } else {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Member code not exist.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    $member_ssb = SavingAccount::where('member_id', $member->id)->first();
                    if ($member_ssb) {
                        $member_ssb_id = $member_ssb->id;
                        $member_ssb_balance = $member_ssb->balance;
                    } else {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'SSB account number not exist.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if ($request->amount > $member_ssb_balance) {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Sufficient amount not available in your SSB account';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if ($chk > 0) {
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'All field required.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    } else {
                        $branch_id = $request->branch_id;
                        $getState = Branch::where('id', $branch_id)->first();
                        $stateid = $getState->state_id;
                        $globaldate = checkMonthAvailability(date('d'), date('m'), date('Y'), $stateid);
                        Session::put('created_at', $globaldate);
                        $branchCode = $getState->branch_code;
                        $planId = $request->plan_id;
                        $faCode = getPlanCode($planId);
                        $investmentMiCode = getInvesmentMiCode($planId, $branch_id);
                        if (!empty($investmentMiCode)) {
                            $miCodeAdd = $investmentMiCode->mi_code + 1;
                        } else {
                            $miCodeAdd = 1;
                        }
                        $miCode = str_pad($miCodeAdd, 5, '0', STR_PAD_LEFT);
                        $miCodeBig = str_pad($miCodeAdd, 5, '0', STR_PAD_LEFT);
                        $passbook = '720' . $branchCode . $faCode . $miCodeBig;
                        $certificate = '719' . $branchCode . $faCode . $miCodeBig;
                        $investmentAccount = $branchCode . $faCode . $miCode;
                        $amount = $request->amount;
                        $tenure = $request->tenure;
                        $principal = $amount;
                        $time = $tenure;
                        $specialCategory = '';
                        if ($member_data1->special_category_id > 0) {
                            $specialCategory = getSpecialCategoryName($member_data1->special_category_id);
                        }
                        $specialCategory = $specialCategory;
                        if ($time >= 0 && $time <= 18) {
                            $rate = 9;
                        } else if ($time >= 19 && $time <= 48) {
                            if ($specialCategory == '') {
                                $rate = 10;
                            } else {
                                $rate = 10.25;
                            }
                        } else if ($time >= 49 && $time <= 60) {
                            if ($specialCategory == '') {
                                $rate = 10.25;
                            } else {
                                $rate = 10.50;
                            }
                        } else if ($time >= 61 && $time <= 72) {
                            if ($specialCategory == '') {
                                $rate = 10.50;
                            } else {
                                $rate = 10.75;
                            }
                        } else if ($time >= 73 && $time <= 96) {
                            if ($specialCategory == '') {
                                $rate = 10.75;
                            } else {
                                $rate = 11;
                            }
                        } else if ($time >= 97 && $time <= 120) {
                            if ($specialCategory == '') {
                                $rate = 11;
                            } else {
                                $rate = 11.25;
                            }
                        }
                        $ci = 1;
                        $irate = $rate / $ci;
                        $year = $time / 12;
                        $result = ($principal * (pow((1 + $irate / 100), $year)));
                        $amount = $principal;
                        $maturity_amount = round($result);
                        $interest_rate = $rate;
                        $dataInv['mi_code'] = $miCode;
                        $dataInv['account_number'] = $investmentAccount;
                        $dataInv['plan_id'] = $planId;
                        $dataInv['form_number'] = $request->form_number;
                        $dataInv['member_id'] = $memberIdAuto;
                        $dataInv['associate_id'] = $agent_id_associate;
                        $dataInv['branch_id'] = $branch_id;
                        $dataInv['created_at'] = $globaldate;
                        $dataInv['app_login_user_id'] = $member->id;
                        $dataInv['is_app'] = 1;
                        $dataInv['certificate_no'] = $certificate;
                        $dataInv['deposite_amount'] = $amount;
                        $dataInv['current_balance'] = $amount;
                        $dataInv['tenure'] = $tenure / 12;
                        $dataInv['payment_mode'] = 3;
                        $dataInv['maturity_amount'] = $maturity_amount;
                        $dataInv['interest_rate'] = $interest_rate;
                        $dataInv['maturity_date'] = date("Y-m-d", strtotime('+ ' . ($tenure) . 'months', strtotime(date("Y/m/d"))));
                        $res = Memberinvestments::create($dataInv);
                        $insertedid = $investmentId = $res->id;
                        $description = 'SFD Account opening';
                        //------------------------------------------------------------
                        $amountArraySsb = array('1' => $amount);
                        $amount_deposit_by_name = $member->first_name . ' ' . $member->last_name;
                        //---- ssb acount amount debit  --------
                        $sAccount = SavingAccount::where('id', $member_ssb_id)->first();
                        $ssbAccountAmount = $sAccount->balance - $amount;
                        $ssb_id = $ssb_pay_id = $sAccount->id;
                        $sResult = SavingAccount::find($ssb_pay_id);
                        $sData['balance'] = $ssbAccountAmount;
                        $sResult->update($sData);
                        $ssb['saving_account_id'] = $ssb_pay_id;
                        $ssb['account_no'] = $sAccount->account_no;
                        $ssb['opening_balance'] = $ssbAccountAmount;
                        $ssb['deposit'] = NULL;
                        $ssb['withdrawal'] = $amount;
                        $ssb['description'] = 'Payment transfer to SFD (' . $investmentAccount . ')';
                        $ssb['branch_id'] = $branch_id;
                        $ssb['type'] = 6;
                        $ssb['associate_id'] = $agent_id_associate;
                        $ssb['currency_code'] = 'INR';
                        $ssb['payment_type'] = 'DR';
                        $ssb['payment_mode'] = 3;
                        $ssb['is_renewal'] = 0;
                        $ssb['app_login_user_id'] = $member->id;
                        $ssb['is_app'] = 1;
                        $ssb['created_at'] = $globaldate;
                        $ssbAccountTranLogin = SavingAccountTranscation::create($ssb);
                        $ssb_tran_id_from = $ssbAccountTranLogin->id;
                        $satRefIdLogin = CommanTransactionsController::createTransactionReferences($ssbAccountTranLogin->id, $investmentId);
                        $createTransaction1 = CommanTransactionsController::createTransactionApp($satRefIdLogin, 5, 0, $ssb_pay_id, $branch_id, $branchCode, $amountArraySsb, 4, $member->first_name . ' ' . $member->last_name, $member->id, $sAccount->account_no, 0, NULL, NULL, date("Y-m-d", strtotime(str_replace('/', '-', $globaldate))), NULL, NULL, $ssb_pay_id, 'DR', $member->id);
                        $transactionData1['is_renewal'] = 0;
                        $transactionData1['created_at'] = date("Y-m-d H:i:s", strtotime(str_replace('/', '-', $globaldate)));
                        $updateTransaction1 = Transcation::find($createTransaction1);
                        $updateTransaction1->update($transactionData1);
                        TranscationLog::where('transaction_id', $transactionData1)->update(['is_renewal' => 0, 'created_at' => date("Y-m-d H:i:s", strtotime(str_replace('/', '-', $globaldate)))]);
                        $createDayBook = CommanTransactionsController::createDayBookNewApp($createTransaction1, $satRefIdLogin, 2, $investmentId, $agent_id_associate, $memberIdAuto, $amount, $amount, $withdrawal = 0, $description, $sAccount->account_no, $branch_id, $branchCode, $amountArraySsb, 4, $amount_deposit_by_name, $member->id, $investmentAccount, NULL, NULL, NULL, NULL, NULL, NULL, $ssb_pay_id, 'CR', NULL, NULL, NULL, NULL, NULL, $member->id);
                        $cheque_no = NULL;
                        $cheque_id = NULL;
                        $cheque_date = NULL;
                        $online_transction_no = NULL;
                        $online_transction_date = NULL;
                        $online_deposit_bank_id = NULL;
                        $online_deposit_bank_ac_id = NULL;
                        // -------------------- head implement start---------------
                        $this->investHeadCreateRegister($amount, $globaldate, $investmentId, $planId, $cheque_no, $cheque_id, $cheque_date, $online_transction_no, $online_transction_date, $online_deposit_bank_id, $online_deposit_bank_ac_id, $branch_id, $agent_id_associate, $memberIdAuto, $ssb_pay_id, $createDayBook, 3, $investmentAccount, $member->id, $ssb_tran_id_from);
                        // -------------------- head implement End ---------------
                        /* ------------------ commission genarate Start-----------------*/
                        $commission = CommanTransactionsController::commissionDistributeInvestment($agent_id_associate, $investmentId, 3, $amount, 1, $planId, $branch_id, $tenure, $createDayBook);
                        $commission_collection = CommanTransactionsController::commissionCollectionInvestment($agent_id_associate, $investmentId, 5, $amount, 1, $planId, $branch_id, $tenure, $createDayBook);
                        /*----- ------  credit business start ---- ---------------*/
                        $creditBusiness = CommanTransactionsController::associateCreditBusiness($agent_id_associate, $investmentId, 1, $amount, 1, $planId, $tenure, $createDayBook);
                        /*----- ------  credit business end ---- ---------------*/
                        /* ------------------ commission genarate End-----------------*/
                        $fNominee = [
                            'investment_id' => $investmentId,
                            'nominee_type' => 0,
                            'name' => $request['first_nominee_name'],
                            'relation' => $request['first_nominee_relation'],
                            'gender' => $request['first_nominee_gender'],
                            'dob' => date("Y-m-d", strtotime(str_replace('/', '-', $request['first_nominee_dob']))),
                            'age' => $request['first_nominee_age'],
                            'percentage' => $request['first_nominee_percentage'],
                            'created_at' => $globaldate,
                        ];
                        $memberFNomi = Memberinvestmentsnominees::create($fNominee);
                        if ($request['second_nominee_name'] != '' || $request['second_nominee_relation'] != '' || $request['second_nominee_gender'] != '' || $request['second_nominee_dob'] != '' || $request['second_nominee_age'] != '' || $request['second_nominee_percentage'] != '') {
                            $sNominee = [
                                'investment_id' => $investmentId,
                                'nominee_type' => 1,
                                'name' => $request['second_nominee_name'],
                                'relation' => $request['second_nominee_relation'],
                                'gender' => $request['second_nominee_gender'],
                                'dob' => date("Y-m-d", strtotime(str_replace('/', '-', $request['second_nominee_dob']))),
                                'age' => $request['second_nominee_age'],
                                'percentage' => $request['second_nominee_percentage'],
                                'created_at' => $globaldate,
                            ];
                            $memberSNomi = Memberinvestmentsnominees::create($sNominee);
                        }
                        $transaction['transaction_id'] = $ssb_tran_id_from;
                        $transaction['investment_id'] = $investmentId;
                        $transaction['transaction_ref_id'] = $satRefIdLogin;
                        $transaction['plan_id'] = $planId;
                        $transaction['member_id'] = $memberIdAuto;
                        $transaction['branch_id'] = $branch_id;
                        $transaction['branch_code'] = $branchCode;
                        $transaction['deposite_amount'] = $amount;
                        $transaction['deposite_date'] = date('Y-m-d');
                        $transaction['deposite_month'] = date('m');
                        $transaction['payment_mode'] = 3;
                        $transaction['saving_account_id'] = $ssb_pay_id;
                        $transaction['created_at'] = $globaldate;
                        $Investmentplantransactions = Investmentplantransactions::create($transaction);
                        $paymentData['ssb_amount'] = $amount;
                        $paymentData['ssb_account_id'] = $ssb_pay_id;
                        $paymentData['ssb_account_no'] = $sAccount->account_no;
                        $paymentData['investment_id'] = $investmentId;
                        $paymentData['created_at'] = $globaldate;
                        $respayments = Memberinvestmentspayments::create($paymentData);
                        $investmentDetail = Memberinvestments::find($investmentId);
                        /*    
                        $text = 'Dear Member, Thank You for Choosing S.B. Micro Finance association Your SFD A/c No.'. $investmentDetail->account_number.' is Credited on '.$investmentDetail->created_at->format('d M Y').' With Rs. '.round($investmentDetail->deposite_amount,2).'CR. Thanks Have a good day';
                        $temaplteId = 1207161519023692218;
                        $contactNumber = array();
                        $memberDetail = Member::find($memberIdAuto);
                        $contactNumber[] = $memberDetail->mobile_no;
                        $sendToMember = new Sms();
                        $sendToMember->sendSms( $contactNumber, $text ,$temaplteId);
                        */
                        $status = "Success";
                        $code = 200;
                        $messages = 'Fixed Deposit Account Created Successfully.';
                        $result = '';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'token', 'associate_status'), $code);
                    }
                } else {
                    $status = "Error";
                    $code = 201;
                    $messages = 'API token mismatch!';
                    $result = '';
                    $associate_status = $member->associate_app_status;
                    return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                }
            } else {
                $status = "Error";
                $code = 201;
                $messages = 'Something went wrong!';
                $result = '';
                $associate_status = 9;
                return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
            }
        } catch (\Exception $e) {
            $status = "Error";
            $code = 500;
            $messages = $e->getMessage();
            $result = '';
            $associate_status = 9;
            return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
        }
    }
    public function registerRDInvestment(Request $request)
    {
        $associate_no = $request->associate_no;
        $messages1 = array();
        try {
            $member = Member::select('id', 'associate_app_status', 'first_name', 'last_name')->where('associate_no', $request->associate_no)->where('associate_status', 1)->where('is_block', 0)->first();
            if ($member) {
                $token = md5($request->associate_no);
                if ($token == $request->token) {
                    $data = array();
                    $chk = 0;
                    $a = $this->getCategoryTree($parent_id = $member->id, $tree_array = array());
                    $b[] = array('member_id' => $member->id, 'status' => 1, 'is_block' => 0);
                    $tree_array1 = array();
                    $c = array_merge($a, $b);
                    foreach ($c as $v) {
                        $tree_array1[] = $v['member_id'];
                    }
                    if ($request->plan_id == '') {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Please select plan.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if (getPlanCode($request->plan_id) != 704) {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Please Select Recurring Deposit Plan Only .';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if ($request->agent_code == '') {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Please enter agent code.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if ($request->member_id == '') {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Please enter member id.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if ($request->branch_id == '') {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Please select branch.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if ($request->form_number == '') {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Please enter form no.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if ($request->amount == '') {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Please enter amount.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if ($request->first_nominee_name == '') {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Please enter first nominee name.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if ($request->first_nominee_relation == '') {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Please enter first nominee relation.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if ($request->first_nominee_dob == '') {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Please enter first nominee dob.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if ($request->first_nominee_age == '') {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Please enter first nominee age.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if ($request->first_nominee_gender == '') {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Please select gender.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if ($request->first_nominee_percentage == '') {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Please enter first nominee percentage.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if ($request->second_nominee_name != '' || $request->second_nominee_relation != '' || $request->second_nominee_dob != '' || $request->second_nominee_gender != '' || $request->second_nominee_percentage != '') {
                        if ($request->second_nominee_relation == '') {
                            $chk++;
                            $status = "Error";
                            $code = 201;
                            $result = '';
                            $messages = 'Please enter second nominee relation.';
                            $associate_status = $member->associate_app_status;
                            return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                        }
                        if ($request->second_nominee_dob == '') {
                            $chk++;
                            $status = "Error";
                            $code = 201;
                            $result = '';
                            $messages = 'Please enter second nominee dob.';
                            $associate_status = $member->associate_app_status;
                            return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                        }
                        if ($request->second_nominee_age == '') {
                            $chk++;
                            $status = "Error";
                            $code = 201;
                            $result = '';
                            $messages = 'Please enter second nominee age.';
                            $associate_status = $member->associate_app_status;
                            return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                        }
                        if ($request->second_nominee_gender == '') {
                            $chk++;
                            $status = "Error";
                            $code = 201;
                            $result = '';
                            $messages = 'Please select gender.';
                            $associate_status = $member->associate_app_status;
                            return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                        }
                        if ($request->second_nominee_percentage == '') {
                            $chk++;
                            $status = "Error";
                            $code = 201;
                            $result = '';
                            $messages = 'Please enter second nominee percentage.';
                            $associate_status = $member->associate_app_status;
                            return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                        }
                    }
                    if (($request->second_nominee_percentage + $request->first_nominee_percentage) != 100) {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Nominee percentage should be equal to 100.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if ($request->tenure == '') {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Please enter tenure.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    $memberIdAuto = '';
                    $agent_id_associate = '';
                    $db_member_data = Member::where('associate_no', $request->agent_code)->first();
                    if ($db_member_data) {
                        if ($request->associate_no != $request->agent_code) {
                            $member_data = Member::where('associate_no', $request->agent_code)->whereIn('associate_senior_id', $tree_array1)->first();
                        } else {
                            $member_data = Member::where('associate_no', $request->agent_code)->first();
                        }
                        if ($member_data) {
                            $agent_id_associate = $member_data->id;
                        } else {
                            $chk++;
                            $status = "Error";
                            $code = 201;
                            $result = '';
                            $messages = 'Agent code not register by your team.';
                            $associate_status = $member->associate_app_status;
                            return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                        }
                    } else {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Agent code not exist.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    $db_member_data1 = Member::where('member_id', $request->member_id)->first();
                    if ($db_member_data1) {
                        $member_data1 = Member::where('member_id', $request->member_id)->whereIn('associate_id', $tree_array1)->first();
                        if ($member_data1) {
                            $memberIdAuto = $member_data1->id;
                        } else {
                            $chk++;
                            $status = "Error";
                            $code = 201;
                            $result = '';
                            $messages = 'Member not register by your team.';
                            $associate_status = $member->associate_app_status;
                            return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                        }
                    } else {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Member code not exist.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    $member_ssb = SavingAccount::where('member_id', $member->id)->first();
                    if ($member_ssb) {
                        $member_ssb_id = $member_ssb->id;
                        $member_ssb_balance = $member_ssb->balance;
                    } else {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'SSB account number not exist.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if ($request->amount > $member_ssb_balance) {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Sufficient amount not available in your SSB account';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if ($chk > 0) {
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'All field required.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    } else {
                        $branch_id = $request->branch_id;
                        $getState = Branch::where('id', $branch_id)->first();
                        $stateid = $getState->state_id;
                        $globaldate = checkMonthAvailability(date('d'), date('m'), date('Y'), $stateid);
                        Session::put('created_at', $globaldate);
                        $branchCode = $getState->branch_code;
                        $planId = $request->plan_id;
                        $faCode = getPlanCode($planId);
                        $investmentMiCode = getInvesmentMiCode($planId, $branch_id);
                        if (!empty($investmentMiCode)) {
                            $miCodeAdd = $investmentMiCode->mi_code + 1;
                        } else {
                            $miCodeAdd = 1;
                        }
                        $miCode = str_pad($miCodeAdd, 5, '0', STR_PAD_LEFT);
                        $miCodeBig = str_pad($miCodeAdd, 5, '0', STR_PAD_LEFT);
                        $passbook = '720' . $branchCode . $faCode . $miCodeBig;
                        $certificate = '719' . $branchCode . $faCode . $miCodeBig;
                        $investmentAccount = $branchCode . $faCode . $miCode;
                        $amount = $request->amount;
                        $tenure = $request->tenure;
                        $principal = $amount;
                        $time = $tenure;
                        $specialCategory = '';
                        if ($member_data1->special_category_id > 0) {
                            $specialCategory = getSpecialCategoryName($member_data1->special_category_id);
                        }
                        $specialCategory = $specialCategory;
                        if ($time >= 0 && $time <= 36) {
                            if ($specialCategory == '') {
                                $rate = 8;
                            } else {
                                $rate = 8.50;
                            }
                        } else if ($time >= 37 && $time <= 60) {
                            if ($specialCategory == '') {
                                $rate = 9;
                            } else {
                                $rate = 9.50;
                            }
                        } else if ($time >= 61 && $time <= 84) {
                            if ($specialCategory == '') {
                                $rate = 10;
                            } else {
                                $rate = 10.50;
                            }
                        }
                        $ci = 1;
                        $irate = $rate / $ci;
                        $year = $time / 12;
                        $freq = 4;
                        $maturity = 0;
                        for ($i = 1; $i <= $time; $i++) {
                            $maturity += $principal * pow((1 + (($rate / 100) / $freq)), $freq * (($time - $i + 1) / 12));
                        }
                        $amount = $principal;
                        $maturity_amount = round($maturity);
                        $interest_rate = $rate;
                        $dataInv['mi_code'] = $miCode;
                        $dataInv['account_number'] = $investmentAccount;
                        $dataInv['plan_id'] = $planId;
                        $dataInv['form_number'] = $request->form_number;
                        $dataInv['member_id'] = $memberIdAuto;
                        $dataInv['associate_id'] = $agent_id_associate;
                        $dataInv['branch_id'] = $branch_id;
                        $dataInv['created_at'] = $globaldate;
                        $dataInv['app_login_user_id'] = $member->id;
                        $dataInv['is_app'] = 1;
                        $dataInv['passbook_no'] = $passbook;
                        $dataInv['deposite_amount'] = $amount;
                        $dataInv['current_balance'] = $amount;
                        $dataInv['tenure'] = $tenure / 12;
                        $dataInv['payment_mode'] = 3;
                        $dataInv['maturity_amount'] = $maturity_amount;
                        $dataInv['interest_rate'] = $interest_rate;
                        $dataInv['maturity_date'] = date("Y-m-d", strtotime('+ ' . ($tenure) . 'months', strtotime(date("Y/m/d"))));
                        if ($tenure == 36) {
                            $tenurefacode = $faCode . '002';
                        } elseif ($tenure == 60) {
                            $tenurefacode = $faCode . '003';
                        } elseif ($tenure == 84) {
                            $tenurefacode = $faCode . '004';
                        } else {
                            $tenurefacode = $faCode . '001';
                        }
                        $dataInv['tenure_fa_code'] = $tenurefacode;
                        $res = Memberinvestments::create($dataInv);
                        $insertedid = $investmentId = $res->id;
                        $description = 'FRD Account opening';
                        //------------------------------------------------------------
                        $amountArraySsb = array('1' => $amount);
                        $amount_deposit_by_name = $member->first_name . ' ' . $member->last_name;
                        //---- ssb acount amount debit  --------
                        $sAccount = SavingAccount::where('id', $member_ssb_id)->first();
                        $ssbAccountAmount = $sAccount->balance - $amount;
                        $ssb_id = $ssb_pay_id = $sAccount->id;
                        $sResult = SavingAccount::find($ssb_pay_id);
                        $sData['balance'] = $ssbAccountAmount;
                        $sResult->update($sData);
                        $ssb['saving_account_id'] = $ssb_pay_id;
                        $ssb['account_no'] = $sAccount->account_no;
                        $ssb['opening_balance'] = $ssbAccountAmount;
                        $ssb['deposit'] = NULL;
                        $ssb['withdrawal'] = $amount;
                        $ssb['description'] = 'Payment transfer to FRD (' . $investmentAccount . ')';
                        $ssb['branch_id'] = $branch_id;
                        $ssb['type'] = 6;
                        $ssb['associate_id'] = $agent_id_associate;
                        $ssb['currency_code'] = 'INR';
                        $ssb['payment_type'] = 'DR';
                        $ssb['payment_mode'] = 3;
                        $ssb['is_renewal'] = 0;
                        $ssb['app_login_user_id'] = $member->id;
                        $ssb['is_app'] = 1;
                        $ssb['created_at'] = $globaldate;
                        $ssbAccountTranLogin = SavingAccountTranscation::create($ssb);
                        $ssb_tran_id_from = $ssbAccountTranLogin->id;
                        $satRefIdLogin = CommanTransactionsController::createTransactionReferences($ssbAccountTranLogin->id, $investmentId);
                        $createTransaction1 = CommanTransactionsController::createTransactionApp($satRefIdLogin, 5, 0, $ssb_pay_id, $branch_id, $branchCode, $amountArraySsb, 4, $member->first_name . ' ' . $member->last_name, $member->id, $sAccount->account_no, 0, NULL, NULL, date("Y-m-d", strtotime(str_replace('/', '-', $globaldate))), NULL, NULL, $ssb_pay_id, 'DR', $member->id);
                        $transactionData1['is_renewal'] = 0;
                        $transactionData1['created_at'] = date("Y-m-d H:i:s", strtotime(str_replace('/', '-', $globaldate)));
                        $updateTransaction1 = Transcation::find($createTransaction1);
                        $updateTransaction1->update($transactionData1);
                        TranscationLog::where('transaction_id', $transactionData1)->update(['is_renewal' => 0, 'created_at' => date("Y-m-d H:i:s", strtotime(str_replace('/', '-', $globaldate)))]);
                        $createDayBook = CommanTransactionsController::createDayBookNewApp($createTransaction1, $satRefIdLogin, 2, $investmentId, $agent_id_associate, $memberIdAuto, $amount, $amount, $withdrawal = 0, $description, $sAccount->account_no, $branch_id, $branchCode, $amountArraySsb, 4, $amount_deposit_by_name, $member->id, $investmentAccount, NULL, NULL, NULL, NULL, NULL, NULL, $ssb_pay_id, 'CR', NULL, NULL, NULL, NULL, NULL, $member->id);
                        $cheque_no = NULL;
                        $cheque_id = NULL;
                        $cheque_date = NULL;
                        $online_transction_no = NULL;
                        $online_transction_date = NULL;
                        $online_deposit_bank_id = NULL;
                        $online_deposit_bank_ac_id = NULL;
                        // -------------------- head implement start---------------
                        $this->investHeadCreateRegister($amount, $globaldate, $investmentId, $planId, $cheque_no, $cheque_id, $cheque_date, $online_transction_no, $online_transction_date, $online_deposit_bank_id, $online_deposit_bank_ac_id, $branch_id, $agent_id_associate, $memberIdAuto, $ssb_pay_id, $createDayBook, 3, $investmentAccount, $member->id, $ssb_tran_id_from);
                        // -------------------- head implement End ---------------
                        /* ------------------ commission genarate Start-----------------*/
                        $commission = CommanTransactionsController::commissionDistributeInvestment($agent_id_associate, $investmentId, 3, $amount, 1, $planId, $branch_id, $tenure, $createDayBook);
                        $commission_collection = CommanTransactionsController::commissionCollectionInvestment($agent_id_associate, $investmentId, 5, $amount, 1, $planId, $branch_id, $tenure, $createDayBook);
                        /*----- ------  credit business start ---- ---------------*/
                        $creditBusiness = CommanTransactionsController::associateCreditBusiness($agent_id_associate, $investmentId, 1, $amount, 1, $planId, $tenure, $createDayBook);
                        /*----- ------  credit business end ---- ---------------*/
                        /* ------------------ commission genarate End-----------------*/
                        $fNominee = [
                            'investment_id' => $investmentId,
                            'nominee_type' => 0,
                            'name' => $request['first_nominee_name'],
                            'relation' => $request['first_nominee_relation'],
                            'gender' => $request['first_nominee_gender'],
                            'dob' => date("Y-m-d", strtotime(str_replace('/', '-', $request['first_nominee_dob']))),
                            'age' => $request['first_nominee_age'],
                            'percentage' => $request['first_nominee_percentage'],
                            'created_at' => $globaldate,
                        ];
                        $memberFNomi = Memberinvestmentsnominees::create($fNominee);
                        if ($request['second_nominee_name'] != '' || $request['second_nominee_relation'] != '' || $request['second_nominee_gender'] != '' || $request['second_nominee_dob'] != '' || $request['second_nominee_age'] != '' || $request['second_nominee_percentage'] != '') {
                            $sNominee = [
                                'investment_id' => $investmentId,
                                'nominee_type' => 1,
                                'name' => $request['second_nominee_name'],
                                'relation' => $request['second_nominee_relation'],
                                'gender' => $request['second_nominee_gender'],
                                'dob' => date("Y-m-d", strtotime(str_replace('/', '-', $request['second_nominee_dob']))),
                                'age' => $request['second_nominee_age'],
                                'percentage' => $request['second_nominee_percentage'],
                                'created_at' => $globaldate,
                            ];
                            $memberSNomi = Memberinvestmentsnominees::create($sNominee);
                        }
                        $transaction['transaction_id'] = $ssb_tran_id_from;
                        $transaction['investment_id'] = $investmentId;
                        $transaction['transaction_ref_id'] = $satRefIdLogin;
                        $transaction['plan_id'] = $planId;
                        $transaction['member_id'] = $memberIdAuto;
                        $transaction['branch_id'] = $branch_id;
                        $transaction['branch_code'] = $branchCode;
                        $transaction['deposite_amount'] = $amount;
                        $transaction['deposite_date'] = date('Y-m-d');
                        $transaction['deposite_month'] = date('m');
                        $transaction['payment_mode'] = 3;
                        $transaction['saving_account_id'] = $ssb_pay_id;
                        $transaction['created_at'] = $globaldate;
                        $Investmentplantransactions = Investmentplantransactions::create($transaction);
                        $paymentData['ssb_amount'] = $amount;
                        $paymentData['ssb_account_id'] = $ssb_pay_id;
                        $paymentData['ssb_account_no'] = $sAccount->account_no;
                        $paymentData['investment_id'] = $investmentId;
                        $paymentData['created_at'] = $globaldate;
                        $respayments = Memberinvestmentspayments::create($paymentData);
                        $investmentDetail = Memberinvestments::find($investmentId);
                        /*    
                        $text = 'Dear Member, Thank You for Choosing S.B. Micro Finance association Your FRD A/c No.'. $investmentDetail->account_number.' is Credited on ' .$investmentDetail->created_at->format('d M Y').' With Rs. '.round($investmentDetail->deposite_amount,2).'CR. Thanks Have a good day';
                        $temaplteId = 1207161519023692218;
                        $contactNumber = array();
                        $memberDetail = Member::find($memberIdAuto);
                        $contactNumber[] = $memberDetail->mobile_no;
                        $sendToMember = new Sms();
                        $sendToMember->sendSms( $contactNumber, $text ,$temaplteId);
                        */
                        $status = "Success";
                        $code = 200;
                        $messages = 'Recurring Deposit Account Created Successfully.';
                        $result = '';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'token', 'associate_status'), $code);
                    }
                } else {
                    $status = "Error";
                    $code = 201;
                    $messages = 'API token mismatch!';
                    $result = '';
                    $associate_status = $member->associate_app_status;
                    return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                }
            } else {
                $status = "Error";
                $code = 201;
                $messages = 'Something went wrong!';
                $result = '';
                $associate_status = 9;
                return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
            }
        } catch (\Exception $e) {
            $status = "Error";
            $code = 500;
            $messages = $e->getMessage();
            $result = '';
            $associate_status = 9;
            return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
        }
    }
    public function registerSBInvestment(Request $request)
    {
        // print_r($_POST);die;
        $associate_no = $request->associate_no;
        $messages1 = array();
        try {
            $member = Member::select('id', 'associate_app_status', 'first_name', 'last_name')->where('associate_no', $request->associate_no)->where('associate_status', 1)->where('is_block', 0)->first();
            if ($member) {
                $token = md5($request->associate_no);
                if ($token == $request->token) {
                    $data = array();
                    $chk = 0;
                    $a = $this->getCategoryTree($parent_id = $member->id, $tree_array = array());
                    $b[] = array('member_id' => $member->id, 'status' => 1, 'is_block' => 0);
                    $tree_array1 = array();
                    $c = array_merge($a, $b);
                    foreach ($c as $v) {
                        $tree_array1[] = $v['member_id'];
                    }
                    if ($request->plan_id == '') {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Please select plan.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if (getPlanCode($request->plan_id) != 718) {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Please Select Samraddh Bhavhishyat Plan Only .';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if ($request->agent_code == '') {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Please enter agent code.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if ($request->member_id == '') {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Please enter member id.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if ($request->branch_id == '') {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Please select branch.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if ($request->form_number == '') {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Please enter form no.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if ($request->amount == '') {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Please enter amount.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if ($request->first_nominee_name == '') {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Please enter first nominee name.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if ($request->first_nominee_relation == '') {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Please enter first nominee relation.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if ($request->first_nominee_dob == '') {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Please enter first nominee dob.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if ($request->first_nominee_age == '') {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Please enter first nominee age.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if ($request->first_nominee_gender == '') {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Please select gender.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if ($request->first_nominee_percentage == '') {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Please enter first nominee percentage.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if ($request->second_nominee_name != '' || $request->second_nominee_relation != '' || $request->second_nominee_dob != '' || $request->second_nominee_gender != '' || $request->second_nominee_percentage != '') {
                        if ($request->second_nominee_relation == '') {
                            $chk++;
                            $status = "Error";
                            $code = 201;
                            $result = '';
                            $messages = 'Please enter second nominee relation.';
                            $associate_status = $member->associate_app_status;
                            return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                        }
                        if ($request->second_nominee_dob == '') {
                            $chk++;
                            $status = "Error";
                            $code = 201;
                            $result = '';
                            $messages = 'Please enter second nominee dob.';
                            $associate_status = $member->associate_app_status;
                            return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                        }
                        if ($request->second_nominee_age == '') {
                            $chk++;
                            $status = "Error";
                            $code = 201;
                            $result = '';
                            $messages = 'Please enter second nominee age.';
                            $associate_status = $member->associate_app_status;
                            return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                        }
                        if ($request->second_nominee_gender == '') {
                            $chk++;
                            $status = "Error";
                            $code = 201;
                            $result = '';
                            $messages = 'Please select gender.';
                            $associate_status = $member->associate_app_status;
                            return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                        }
                        if ($request->second_nominee_percentage == '') {
                            $chk++;
                            $status = "Error";
                            $code = 201;
                            $result = '';
                            $messages = 'Please enter second nominee percentage.';
                            $associate_status = $member->associate_app_status;
                            return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                        }
                    }
                    if (($request->second_nominee_percentage + $request->first_nominee_percentage) != 100) {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Nominee percentage should be equal to 100.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    $memberIdAuto = '';
                    $agent_id_associate = '';
                    $db_member_data = Member::where('associate_no', $request->agent_code)->first();
                    if ($db_member_data) {
                        if ($request->associate_no != $request->agent_code) {
                            $member_data = Member::where('associate_no', $request->agent_code)->whereIn('associate_senior_id', $tree_array1)->first();
                        } else {
                            $member_data = Member::where('associate_no', $request->agent_code)->first();
                        }
                        if ($member_data) {
                            $agent_id_associate = $member_data->id;
                        } else {
                            $chk++;
                            $status = "Error";
                            $code = 201;
                            $result = '';
                            $messages = 'Agent code not register by your team.';
                            $associate_status = $member->associate_app_status;
                            return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                        }
                    } else {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Agent code not exist.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    $db_member_data1 = Member::where('member_id', $request->member_id)->first();
                    if ($db_member_data1) {
                        $member_data1 = Member::where('member_id', $request->member_id)->whereIn('associate_id', $tree_array1)->first();
                        if ($member_data1) {
                            $memberIdAuto = $member_data1->id;
                        } else {
                            $chk++;
                            $status = "Error";
                            $code = 201;
                            $result = '';
                            $messages = 'Member not register by your team.';
                            $associate_status = $member->associate_app_status;
                            return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                        }
                    } else {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Member code not exist.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    $member_ssb = SavingAccount::where('member_id', $member->id)->first();
                    if ($member_ssb) {
                        $member_ssb_id = $member_ssb->id;
                        $member_ssb_balance = $member_ssb->balance;
                    } else {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'SSB account number not exist.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if ($request->amount > $member_ssb_balance) {
                        $chk++;
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'Sufficient amount not available in your SSB account';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    }
                    if ($chk > 0) {
                        $status = "Error";
                        $code = 201;
                        $result = '';
                        $messages = 'All field required.';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    } else {
                        $branch_id = $request->branch_id;
                        $getState = Branch::where('id', $branch_id)->first();
                        $stateid = $getState->state_id;
                        $globaldate = checkMonthAvailability(date('d'), date('m'), date('Y'), $stateid);
                        Session::put('created_at', $globaldate);
                        $branchCode = $getState->branch_code;
                        $planId = $request->plan_id;
                        $faCode = getPlanCode($planId);
                        $investmentMiCode = getInvesmentMiCode($planId, $branch_id);
                        if (!empty($investmentMiCode)) {
                            $miCodeAdd = $investmentMiCode->mi_code + 1;
                        } else {
                            $miCodeAdd = 1;
                        }
                        $miCode = str_pad($miCodeAdd, 5, '0', STR_PAD_LEFT);
                        $miCodeBig = str_pad($miCodeAdd, 5, '0', STR_PAD_LEFT);
                        $passbook = '720' . $branchCode . $faCode . $miCodeBig;
                        $certificate = '719' . $branchCode . $faCode . $miCodeBig;
                        $investmentAccount = $branchCode . $faCode . $miCode;
                        $amount = $request->amount;
                        $tenure = 120;
                        $principal = $amount;
                        $time = $tenure;
                        $rate = 11;
                        $ci = 1;
                        $irate = $rate / $ci;
                        $year = $time / 12;
                        $freq = 1;
                        $maturity = 0;
                        for ($i = 1; $i <= $time; $i++) {
                            $maturity = $maturity + $principal * pow((1 + (($rate / 100) / $freq)), $freq * (($time - $i + 1) / 12));
                        }
                        $result = round($maturity);
                        $amount = $principal;
                        $maturity_amount = round($maturity);
                        $interest_rate = $rate;
                        $dataInv['mi_code'] = $miCode;
                        $dataInv['account_number'] = $investmentAccount;
                        $dataInv['plan_id'] = $planId;
                        $dataInv['form_number'] = $request->form_number;
                        $dataInv['member_id'] = $memberIdAuto;
                        $dataInv['associate_id'] = $agent_id_associate;
                        $dataInv['branch_id'] = $branch_id;
                        $dataInv['created_at'] = $globaldate;
                        $dataInv['app_login_user_id'] = $member->id;
                        $dataInv['is_app'] = 1;
                        $dataInv['passbook_no'] = $passbook;
                        $dataInv['deposite_amount'] = $amount;
                        $dataInv['current_balance'] = $amount;
                        $dataInv['tenure'] = $tenure / 12;
                        $dataInv['payment_mode'] = 3;
                        $dataInv['maturity_amount'] = $maturity_amount;
                        $dataInv['interest_rate'] = $interest_rate;
                        $dataInv['maturity_date'] = date("Y-m-d", strtotime('+ ' . ($tenure) . 'months', strtotime(date("Y/m/d"))));
                        $res = Memberinvestments::create($dataInv);
                        $insertedid = $investmentId = $res->id;
                        $description = 'SB Account opening';
                        //------------------------------------------------------------
                        $amountArraySsb = array('1' => $amount);
                        $amount_deposit_by_name = $member->first_name . ' ' . $member->last_name;
                        //---- ssb acount amount debit  --------
                        $sAccount = SavingAccount::where('id', $member_ssb_id)->first();
                        $ssbAccountAmount = $sAccount->balance - $amount;
                        $ssb_id = $ssb_pay_id = $sAccount->id;
                        $sResult = SavingAccount::find($ssb_pay_id);
                        $sData['balance'] = $ssbAccountAmount;
                        $sResult->update($sData);
                        $ssb['saving_account_id'] = $ssb_pay_id;
                        $ssb['account_no'] = $sAccount->account_no;
                        $ssb['opening_balance'] = $ssbAccountAmount;
                        $ssb['deposit'] = NULL;
                        $ssb['withdrawal'] = $amount;
                        $ssb['description'] = 'Payment transfer to SB (' . $investmentAccount . ')';
                        $ssb['branch_id'] = $branch_id;
                        $ssb['type'] = 6;
                        $ssb['associate_id'] = $agent_id_associate;
                        $ssb['currency_code'] = 'INR';
                        $ssb['payment_type'] = 'DR';
                        $ssb['payment_mode'] = 3;
                        $ssb['is_renewal'] = 0;
                        $ssb['app_login_user_id'] = $member->id;
                        $ssb['is_app'] = 1;
                        $ssb['created_at'] = $globaldate;
                        $ssbAccountTranLogin = SavingAccountTranscation::create($ssb);
                        $ssb_tran_id_from = $ssbAccountTranLogin->id;
                        $satRefIdLogin = CommanTransactionsController::createTransactionReferences($ssbAccountTranLogin->id, $investmentId);
                        $createTransaction1 = CommanTransactionsController::createTransactionApp($satRefIdLogin, 5, 0, $ssb_pay_id, $branch_id, $branchCode, $amountArraySsb, 4, $member->first_name . ' ' . $member->last_name, $member->id, $sAccount->account_no, 0, NULL, NULL, date("Y-m-d", strtotime(str_replace('/', '-', $globaldate))), NULL, NULL, $ssb_pay_id, 'DR', $member->id);
                        $transactionData1['is_renewal'] = 0;
                        $transactionData1['created_at'] = date("Y-m-d H:i:s", strtotime(str_replace('/', '-', $globaldate)));
                        $updateTransaction1 = Transcation::find($createTransaction1);
                        $updateTransaction1->update($transactionData1);
                        TranscationLog::where('transaction_id', $transactionData1)->update(['is_renewal' => 0, 'created_at' => date("Y-m-d H:i:s", strtotime(str_replace('/', '-', $globaldate)))]);
                        $createDayBook = CommanTransactionsController::createDayBookNewApp($createTransaction1, $satRefIdLogin, 2, $investmentId, $agent_id_associate, $memberIdAuto, $amount, $amount, $withdrawal = 0, $description, $sAccount->account_no, $branch_id, $branchCode, $amountArraySsb, 4, $amount_deposit_by_name, $member->id, $investmentAccount, NULL, NULL, NULL, NULL, NULL, NULL, $ssb_pay_id, 'CR', NULL, NULL, NULL, NULL, NULL, $member->id);
                        $cheque_no = NULL;
                        $cheque_id = NULL;
                        $cheque_date = NULL;
                        $online_transction_no = NULL;
                        $online_transction_date = NULL;
                        $online_deposit_bank_id = NULL;
                        $online_deposit_bank_ac_id = NULL;
                        // -------------------- head implement start---------------
                        $this->investHeadCreateRegister($amount, $globaldate, $investmentId, $planId, $cheque_no, $cheque_id, $cheque_date, $online_transction_no, $online_transction_date, $online_deposit_bank_id, $online_deposit_bank_ac_id, $branch_id, $agent_id_associate, $memberIdAuto, $ssb_pay_id, $createDayBook, 3, $investmentAccount, $member->id, $ssb_tran_id_from);
                        // -------------------- head implement End ---------------
                        /* ------------------ commission genarate Start-----------------*/
                        $commission = CommanTransactionsController::commissionDistributeInvestment($agent_id_associate, $investmentId, 3, $amount, 1, $planId, $branch_id, $tenure, $createDayBook);
                        $commission_collection = CommanTransactionsController::commissionCollectionInvestment($agent_id_associate, $investmentId, 5, $amount, 1, $planId, $branch_id, $tenure, $createDayBook);
                        /*----- ------  credit business start ---- ---------------*/
                        $creditBusiness = CommanTransactionsController::associateCreditBusiness($agent_id_associate, $investmentId, 1, $amount, 1, $planId, $tenure, $createDayBook);
                        /*----- ------  credit business end ---- ---------------*/
                        /* ------------------ commission genarate End-----------------*/
                        $fNominee = [
                            'investment_id' => $investmentId,
                            'nominee_type' => 0,
                            'name' => $request['first_nominee_name'],
                            'relation' => $request['first_nominee_relation'],
                            'gender' => $request['first_nominee_gender'],
                            'dob' => date("Y-m-d", strtotime(str_replace('/', '-', $request['first_nominee_dob']))),
                            'age' => $request['first_nominee_age'],
                            'percentage' => $request['first_nominee_percentage'],
                            'created_at' => $globaldate,
                        ];
                        $memberFNomi = Memberinvestmentsnominees::create($fNominee);
                        if ($request['second_nominee_name'] != '' || $request['second_nominee_relation'] != '' || $request['second_nominee_gender'] != '' || $request['second_nominee_dob'] != '' || $request['second_nominee_age'] != '' || $request['second_nominee_percentage'] != '') {
                            $sNominee = [
                                'investment_id' => $investmentId,
                                'nominee_type' => 1,
                                'name' => $request['second_nominee_name'],
                                'relation' => $request['second_nominee_relation'],
                                'gender' => $request['second_nominee_gender'],
                                'dob' => date("Y-m-d", strtotime(str_replace('/', '-', $request['second_nominee_dob']))),
                                'age' => $request['second_nominee_age'],
                                'percentage' => $request['second_nominee_percentage'],
                                'created_at' => $globaldate,
                            ];
                            $memberSNomi = Memberinvestmentsnominees::create($sNominee);
                        }
                        $transaction['transaction_id'] = $ssb_tran_id_from;
                        $transaction['investment_id'] = $investmentId;
                        $transaction['transaction_ref_id'] = $satRefIdLogin;
                        $transaction['plan_id'] = $planId;
                        $transaction['member_id'] = $memberIdAuto;
                        $transaction['branch_id'] = $branch_id;
                        $transaction['branch_code'] = $branchCode;
                        $transaction['deposite_amount'] = $amount;
                        $transaction['deposite_date'] = date('Y-m-d');
                        $transaction['deposite_month'] = date('m');
                        $transaction['payment_mode'] = 3;
                        $transaction['saving_account_id'] = $ssb_pay_id;
                        $transaction['created_at'] = $globaldate;
                        $Investmentplantransactions = Investmentplantransactions::create($transaction);
                        $paymentData['ssb_amount'] = $amount;
                        $paymentData['ssb_account_id'] = $ssb_pay_id;
                        $paymentData['ssb_account_no'] = $sAccount->account_no;
                        $paymentData['investment_id'] = $investmentId;
                        $paymentData['created_at'] = $globaldate;
                        $respayments = Memberinvestmentspayments::create($paymentData);
                        $investmentDetail = Memberinvestments::find($investmentId);
                        /*    
                        $text = 'Dear Member, Thank You for Choosing S.B. Micro Finance association Your S. Bhavhishya A/c No.'. $investmentDetail->account_number.' is Credited on '  .$investmentDetail->created_at->format('d M Y').' With Rs. '.round($investmentDetail->deposite_amount,2).'CR. Thanks Have a good day';
                        $temaplteId = 1207161519023692218;
                        $contactNumber = array();
                        $memberDetail = Member::find($memberIdAuto);
                        $contactNumber[] = $memberDetail->mobile_no;
                        $sendToMember = new Sms();
                        $sendToMember->sendSms( $contactNumber, $text ,$temaplteId);
                        */
                        $status = "Success";
                        $code = 200;
                        $messages = 'Samraddh Bhavhishya Account Created Successfully.';
                        $result = '';
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'token', 'associate_status'), $code);
                    }
                } else {
                    $status = "Error";
                    $code = 201;
                    $messages = 'API token mismatch!';
                    $result = '';
                    $associate_status = $member->associate_app_status;
                    return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                }
            } else {
                $status = "Error";
                $code = 201;
                $messages = 'Something went wrong!';
                $result = '';
                $associate_status = 9;
                return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
            }
        } catch (\Exception $e) {
            $status = "Error";
            $code = 500;
            $messages = $e->getMessage();
            $result = '';
            $associate_status = 9;
            return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
        }
    }
    // GET iNVESTMENT DETAIL
    public function investment_detail(Request $request)
    {
        try {
            $member = Member::select('id', 'associate_app_status')->where('associate_no', $request->associate_no)->where('associate_status', 1)->where('is_block', 0)->first();
            if ($member) {
                $token = md5($request->associate_no);
                if ($token == $request->token) {
                    // if($request->page>0 && $request->length>0)
                    // {
                    $data = array();
                    $a = $this->getCategoryTree($parent_id = $member->id, $tree_array = array());
                    $b[] = array('member_id' => $member->id, 'status' => 1, 'is_block' => 0);
                    $tree_array1 = array();
                    $c = array_merge($a, $b);
                    foreach ($c as $v) {
                        $tree_array1[] = $v['member_id'];
                    }
                    $memberArray = Member::whereIn('associate_id', $tree_array1)->get('id');
                    $memberArray1 = array();
                    foreach ($memberArray as $v) {
                        $memberArray1[] = $v->id;
                    }
                    $v = '-R';
                    $invest_data = Memberinvestments::
                        with('plan', 'member', 'associateMember', 'branch', 'investmentNomiees')
                        ->where('id', $request->id)
                        ->where('account_number', 'not like', '%' . $v . '%')
                        ->where('is_mature', 1)
                        ->whereHas('company')
                        ->where('current_balance', '>', 0)
                        /*->whereIn('member_id',$memberArray1)*/
                    ;
                    $invest_data1 = $invest_data->get();
                    $count = count($invest_data1);
                    if ($request->page == 1) {
                        $start = 0;
                    } else {
                        $start = ($request->page - 1) * $request->length;
                    }
                    $invest_data = $invest_data->orderBy('id', 'asc')->get();
                    foreach ($invest_data as $key => $row) {
                        $data[$key]['id'] = $row->id;
                        $data[$key]['plan'] = $row['plan']->name;
                        $data[$key]['form_number'] = $row->form_number;
                        if ($row->plan_id == 1) {
                            $tenure = 'N/A';
                        } else {
                            if ($row->tenure == 1) {
                                $tenure = '12  Months';
                            } elseif ($row->tenure == 2) {
                                $tenure = '24  Months';
                            } elseif ($row->tenure == 3) {
                                $tenure = '26  Months';
                            } elseif ($row->tenure == 4) {
                                $tenure = '60  Months';
                            } else {
                                $tenure = $row->tenure . ' Year';
                            }
                        }
                        $data[$key]['tenure'] = $tenure;
                        if ($row->plan_id == 1) {
                            $ssb = getSsbAccountDetail($row->account_number);
                            $current_balance = $ssb->balance;
                        } else {
                            $current_balance = $row->current_balance;
                        }
                        $data[$key]['current_balance'] = $current_balance;
                        //$data[$key]['eli_amount']=investmentEliAmount($row->id);
                        $data[$key]['eli_amount'] = '';
                        $data[$key]['deposite_amount'] = $row->deposite_amount;
                        $data[$key]['member_first_name'] = $row['member']->first_name;
                        if (isset($row['member']->last_name)) {
                            $data[$key]['member_last_name'] = $row['member']->last_name;
                        } else {
                            $data[$key]['member_last_name'] = 'N/A';
                        }
                        $idProof = MemberIdProof::where('member_id', $row->member_id)->pluck('first_id_no')->first();
                        if ($idProof) {
                            $data[$key]['id_proof'] = $idProof;
                        } else {
                            $data[$key]['id_proof'] = 'N/A';
                        }
                        $data[$key]['member_id'] = $row['member']->member_id;
                        $data[$key]['mobile_number'] = $row['member']->mobile_no;
                        $data[$key]['associate_code'] = $row['associateMember']['associate_no'];
                        $data[$key]['associate_mobile_number'] = $row['associateMember']['mobile_no'];
                        if ($row->associate_no == '9999999') {
                            $carder = 'Company';
                        } else {
                            $carder = getCarderNameFull($row['associateMember']->current_carder_id);
                        }
                        $data[$key]['associate_carder'] = $carder;
                        $data[$key]['account_number'] = $row['account_number'];
                        $data[$key]['created_at'] = date("d/m/Y", strtotime($row['created_at']));
                        $data[$key]['account_number'] = $row->account_number;
                        $data[$key]['associate_name'] = $row['associateMember']['first_name'] . ' ' . $row['associateMember']['last_name'];
                        $data[$key]['branch'] = $row['branch']->name;
                        $data[$key]['branch_code'] = $row['branch']->branch_code;
                        $data[$key]['sector_name'] = $row['branch']->sector;
                        $data[$key]['region_name'] = $row['branch']->regan;
                        $data[$key]['zone_name'] = $row['branch']->zone;
                        $getSpecialCategory = '';
                        if ($row['member']->special_category_id > 0) {
                            $getSpecialCategory = getSpecialCategory($row['member']->special_category_id);
                        } else {
                            $getSpecialCategory = 'General Category';
                        }
                        $data[$key]['category'] = $getSpecialCategory;
                        $mode = '';
                        /*
                        if ($row->payment_mode == 0) {
                            $mode = "Cash";
                        } elseif ($row->payment_mode == 1) {
                            $mode = "Cheque";
                        } elseif ($row->payment_mode == 2) {
                            $mode = "DD";
                        } elseif ($row->payment_mode == 3) {
                            $mode = "Online";
                        } elseif ($row->payment_mode == 4) {
                            $mode = "By Saving Account";
                        } elseif ($row->payment_mode == 5) {
                            $mode = "From Loan Amount";
                        }
                        */
                        if ($row->payment_mode == 0) {
                            $mode = "Cash";
                        } elseif ($row->payment_mode == 1) {
                            $mode = "Cheque";
                        } elseif ($row->payment_mode == 2) {
                            $mode = "Online";
                        } elseif ($row->payment_mode == 3) {
                            $mode = "By Saving Account";
                        }
                        $data[$key]['payment_mode'] = $mode;
                        $data[$key]['maturity_amount'] = $row->maturity_amount;
                        $idProofDetail = MemberIdProof::where('member_id', $row['member']->id)->first();
                        $data[$key]['firstId'] = getIdProofName($idProofDetail->first_id_type_id) . ' - ' . $idProofDetail->first_id_no;
                        $data[$key]['secondId'] = getIdProofName($idProofDetail->second_id_type_id) . ' - ' . $idProofDetail->second_id_no;
                        $data[$key]['address'] = $row['member']->address;
                        $data[$key]['state'] = getStateName($row['member']->state_id);
                        $data[$key]['district'] = getDistrictName($row['member']->district_id);
                        $data[$key]['city'] = getCityName($row['member']->city_id);
                        $data[$key]['village'] = $row['member']->village;
                        $data[$key]['pin_code'] = $row['member']->pin_code;
                        if (count($row['investmentNomiees']) > 0) {
                            foreach ($row['investmentNomiees'] as $key => $nominee) {
                                $data[$key]['nominee_name'] = $nominee->name;
                                if (isset($nominee->relation)) {
                                    $data[$key]['relation'] = getRelationsName($nominee->relation);
                                } else {
                                    $data[$key]['relation'] = 'N/A';
                                }
                                if (isset($nominee->dob)) {
                                    $data[$key]['nominee_dob'] = date('d/m/Y', strtotime($nominee->dob));
                                } else {
                                    $data[$key]['nominee_dob'] = '';
                                }
                                if (isset($nominee->age)) {
                                    $data[$key]['nominee_age'] = (string) $nominee->age;
                                } else {
                                    $data[$key]['nominee_age'] = '';
                                }
                                if (isset($nominee->gender)) {
                                    switch ($nominee->gender) {
                                        case 0:
                                            $data[$key]['nominee_gender'] = 'Female';
                                            break;
                                        case 1:
                                            $data[$key]['nominee_gender'] = 'Male';
                                            break;
                                    }
                                    $data[$key]['nominee_percentage'] = (string) $nominee->percentage;
                                    // $data[$key]
                                    // ['nominee_gender']=$nominee->age;
                                }
                            }
                        } else {
                            $data[$key]['nominee_name'] = 'N/A';
                            $data[$key]['relation'] = 'N/A';
                            $data[$key]['nominee_dob'] = 'N/A';
                            $data[$key]['nominee_age'] = 'N/A';
                            $data[$key]['nominee_gender'] = 'N/A';
                            $data[$key]['nominee_percentage'] = '0';
                        }
                    }
                    $status = "Success";
                    $code = 200;
                    $messages = 'Investment listing!';
                    $page = $request->page;
                    $length = $request->length;
                    $result = ['member' => $data, 'total_count' => $count, 'page' => $page, 'length' => $length, 'record_count' => count($data)];
                    $associate_status = $member->associate_app_status;
                    return response()->json(compact('status', 'code', 'messages', 'result', 'token', 'associate_status'), $code);
                    // }
                    // else
                    // {
                    //     $status = "Error";
                    //     $code = 201;
                    //     $messages = 'Page no or length must be grater than 0!';
                    //     $result = '';
                    //     $associate_status=$member->associate_app_status;
                    //     return response()->json(compact('status', 'code', 'messages', 'result','associate_status'), $code);
                    // }
                } else {
                    $status = "Error";
                    $code = 201;
                    $messages = 'API token mismatch!';
                    $result = '';
                    $associate_status = $member->associate_app_status;
                    return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                }
            } else {
                $status = "Error";
                $code = 201;
                $messages = 'Something went wrong!';
                $result = '';
                $associate_status = 9;
                return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
            }
        } catch (\Exception $e) {
            $status = "Error";
            $code = 500;
            $messages = $e->getMessage();
            $result = '';
            $associate_status = 9;
            return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
        }
    }
}