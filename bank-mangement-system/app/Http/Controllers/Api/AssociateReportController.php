<?php
namespace App\Http\Controllers\Api;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Validator;
use App\Models\Member;
use App\Models\Memberloans;
use App\Models\Daybook;
use App\Models\SavingAccountTranscation;
use App\Models\SavingAccount;
use App\Models\DemandAdvice;
use Carbon\Carbon;
use App\Models\AllHeadTransaction;
use App\Models\Companies;
class AssociateReportController extends Controller
{
    /**
     * Fetch member investments.
     *
     * @param  \App\Request  $request
     * @return \Illuminate\Http\Response
     */
    function getCategoryTree($parent_id,  $tree_array = array())
    {
        $categories = associateTreeid($parent_id);
        foreach ($categories as $item) {
            $tree_array[] =   [
				'member_id' => $item->member_id, 
				'status' => $item['member']->associate_status, 
				'is_block' => $item['member']->is_block
				];
            // $tree_array = $this->getCategoryTree($item->member_id, $tree_array);
        }
        return $tree_array;
    }
    public function associate_business_report(Request $request)
    {
        $associate_no = $request->associate_no;
        try {
            $member = Member::select('id', 'associate_app_status', 'associate_branch_id')
				->where('associate_no', $request->associate_no)
				->where('associate_status', 1)
				->where('is_block', 0)
				->first();
            if ($member) {
                $token = md5($request->associate_no);
                if ($token == $request->token) {
                    if ($request->page > 0 && $request->length > 0) {
                        $data = array();
                        $a = $this->getCategoryTree($parent_id = $member->id, $tree_array = array());
                        $b[] = array('member_id' => $member->id, 'status' => 1, 'is_block' => 0);
                        $tree_array1 = array();
                        $c = array_merge($a, $b);
                        foreach ($c as $v) {
                            if ($v['status'] == 1 && $v['is_block'] == 0) {
                                $tree_array1[] =  $v['member_id'];
                            }
                        }
                        $member_data = Member::with('associate_branch')->whereIn('id', $tree_array1);
                        if ($request->start_date != 0) {
                            $startDate = date('Y-m-d', strtotime(convertDate($request->start_date)));
                        } else {
                            $startDate = '';
                        }
                        if ($request->end_date != 0) {
                            $endDate = date('Y-m-d', strtotime(convertDate($request->end_date)));
                        } else {
                            $endDate = '';
                        }
                        if (isset($request->branch_id) && $request->branch_id != 0) {
                            $branch_id = $request->branch_id;
                            $member_data = $member_data->where('associate_branch_id', $request->branch_id);
                        } else {
                            $branch_id = '';
                        }
                        if ($request->associate_id != 0) {
                            $member_data = $member_data->where('id', '=', $request->associate_id);
                        }
                        $member_data1 = $member_data->orderby('associate_join_date', 'DESC')->get();
                        $count = count($member_data1);
                        if ($request->page == 1) {
                            $start = 0;
                        } else {
                            $start = ($request->page - 1) * $request->length;
                        }
                        $member_data = $member_data->orderby('associate_join_date', 'DESC')->offset($start)->limit($request->length)->get();
                        foreach ($member_data as $key => $row) {
                            // $branch_id='';
                            $associate_id = $row->id;
                            $planDaily = getPlanID('710')->id;
                            $dailyId = array($planDaily);
                            $planSSB = getPlanID('703')->id;
                            $planKanyadhan = getPlanID('709')->id;
                            $planMB = getPlanID('708')->id;
                            $planFRD = getPlanID('707')->id;
                            $planJeevan = getPlanID('713')->id;
                            $planRD = getPlanID('704')->id;
                            $planBhavhishya = getPlanID('718')->id;
                            $monthlyId = array($planKanyadhan, $planMB, $planFRD, $planJeevan, $planRD, $planBhavhishya);
                            $planMI = getPlanID('712')->id;
                            $planFFD = getPlanID('705')->id;
                            $planFD = getPlanID('706')->id;
                            $fdId = array($planMI, $planFFD, $planFD);
                            $data[$key]['join_date'] = date("d/m/Y", strtotime($row->associate_join_date));
                            $data[$key]['branch'] = $row['associate_branch']->name;
                            $data[$key]['branch_code'] = $row['associate_branch']->branch_code;
                            $data[$key]['sector_name'] = $row['associate_branch']->sector;
                            $data[$key]['region_name'] = $row['associate_branch']->regan;
                            $data[$key]['zone_name'] = $row['associate_branch']->zone;
                            $data[$key]['member_id'] = $row->associate_no;
                            $data[$key]['name'] = $row->first_name . ' ' . $row->last_name;
                            $data[$key]['cadre'] = getCarderName($row->current_carder_id);
                            $data[$key]['daily_new_ac'] = investNewAcCount($associate_id, $startDate, $endDate, $planDaily, $branch_id);
                            $data[$key]['daily_deno_sum'] = investNewDenoSum($associate_id, $startDate, $endDate, $planDaily, $branch_id);
                            $data[$key]['daily_renew_ac'] = investRenewAc($associate_id, $startDate, $endDate, $dailyId, $branch_id);
                            $data[$key]['daily_renew'] = investRenewAmountSum($associate_id, $startDate, $endDate, $dailyId, $branch_id);
                            $data[$key]['monthly_new_ac'] = investNewAcCountType($associate_id, $startDate, $endDate, $monthlyId, $branch_id);
                            $data[$key]['monthly_deno_sum'] = investNewDenoSumType($associate_id, $startDate, $endDate, $monthlyId, $branch_id);
                            $data[$key]['monthly_renew_ac'] = investRenewAc($associate_id, $startDate, $endDate, $monthlyId, $branch_id);
                            $data[$key]['monthly_renew'] = investRenewAmountSum($associate_id, $startDate, $endDate, $monthlyId, $branch_id);
                            $data[$key]['fd_new_ac'] = investNewAcCountType($associate_id, $startDate, $endDate, $fdId, $branch_id);
                            $data[$key]['fd_deno_sum'] = investNewDenoSumType($associate_id, $startDate, $endDate, $fdId, $branch_id);
                            $data[$key]['ssb_new_ac'] = totalInvestSSbAcCountByType($associate_id, $startDate, $endDate, $branch_id, 1);
                            $data[$key]['ssb_deno_sum'] = totalInvestSSbAmtByType($associate_id, $startDate, $endDate, $branch_id, 1);
                            $data[$key]['ssb_renew_ac'] = totalInvestSSbAcCountByType($associate_id, $startDate, $endDate, $branch_id, 2);
                            $data[$key]['ssb_renew'] = totalInvestSSbAmtByType($associate_id, $startDate, $endDate, $branch_id, 2);
                            $sum_ni_ac = $data[$key]['daily_new_ac'] + $data[$key]['monthly_new_ac'] + $data[$key]['fd_new_ac'] + $data[$key]['ssb_new_ac'];
                            $sum_ni_amount = $data[$key]['daily_deno_sum'] + $data[$key]['monthly_deno_sum'] + $data[$key]['fd_deno_sum'] + $data[$key]['ssb_deno_sum'];
                            $data[$key]['total_ni_ac'] = $sum_ni_ac;
                            $data[$key]['total_ni_amount'] = number_format((float)$sum_ni_amount, 2, '.', '');
                            $sum_renew_ac = $data[$key]['daily_renew_ac'] + $data[$key]['monthly_renew_ac'] + $data[$key]['ssb_renew_ac'];
                            $sum_renew_amount = $data[$key]['daily_renew'] + $data[$key]['monthly_renew'] + $data[$key]['ssb_renew'];
                            $data[$key]['total_ac'] = $sum_renew_ac;
                            $data[$key]['total_amount'] = number_format((float)$sum_renew_amount, 2, '.', '');
                            $data[$key]['other_mt'] = investOtherMiByType($associate_id, $startDate, $endDate, $branch_id, 1, 11);
                            $data[$key]['other_stn'] = investOtherMiByType($associate_id, $startDate, $endDate, $branch_id, 1, 12);
                            $ni_m = $data[$key]['daily_deno_sum'] + $data[$key]['monthly_deno_sum'] + $data[$key]['fd_deno_sum'];
                            $tcc_m = $data[$key]['daily_deno_sum'] + $data[$key]['monthly_deno_sum'] + $data[$key]['fd_deno_sum'] + $data[$key]['daily_renew'] + $data[$key]['monthly_renew'];
                            $tcc = $data[$key]['daily_deno_sum'] + $data[$key]['monthly_deno_sum'] + $data[$key]['fd_deno_sum'] + $data[$key]['ssb_deno_sum'] + $data[$key]['daily_renew'] + $data[$key]['monthly_renew'] + $data[$key]['ssb_renew'];
                            $data[$key]['ni_m'] = number_format((float)$ni_m, 2, '.', '');
                            $data[$key]['ni'] = number_format((float)$sum_ni_amount, 2, '.', '');
                            $data[$key]['tcc_m'] = number_format((float)$tcc_m, 2, '.', '');
                            $data[$key]['tcc'] = number_format((float)$tcc, 2, '.', '');
                            $data[$key]['loan_ac'] = totalLoanAc($associate_id, $startDate, $endDate, $branch_id);
                            $data[$key]['loan_amount'] = totalLoanAmount($associate_id, $startDate, $endDate, $branch_id);
                            $data[$key]['loan_recovery_ac'] = totalRenewLoanAc($associate_id, $startDate, $endDate, $branch_id);
                            $data[$key]['loan_recovery_amount'] = totalRenewLoanAmount($associate_id, $startDate, $endDate, $branch_id);
                            $data[$key]['new_associate'] = memberCountByType($associate_id, $startDate, $endDate, $branch_id, 1, 0);
                            $data[$key]['total_associate'] = memberCountByType($associate_id, $startDate, $endDate, $branch_id, 1, 1);
                            $data[$key]['new_member'] = memberCountByType($associate_id, $startDate, $endDate, $branch_id, 0, 0);
                            $data[$key]['total_member'] = memberCountByType($associate_id, $startDate, $endDate, $branch_id, 0, 1);
                        }
                        $status   = "Success";
                        $code     = 200;
                        $messages = 'Associate Business Report !';
                        $page  = $request->page;
                        $length  = $request->length;
                        $result   = ['business_report' => $data, 'total_count' => $count, 'page' => $page, 'length' => $length, 'record_count' => count($member_data)];
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    } elseif (!isset($request->length)) {
                        $data = array();
                        $a = $this->getCategoryTree($parent_id = $member->id, $tree_array = array());
                        $b[] = array('member_id' => $member->id, 'status' => 1, 'is_block' => 0);
                        $tree_array1 = array();
                        $c = array_merge($a, $b);
                        foreach ($c as $v) {
                            if ($v['status'] == 1 && $v['is_block'] == 0) {
                                $tree_array1[] =  $v['member_id'];
                            }
                        }
                        $member_data = Member::with('associate_branch')->whereIn('id', $tree_array1);
                        if ($request->start_date != 0) {
                            $startDate = date('Y-m-d', strtotime(convertDate($request->start_date)));
                        } else {
                            $startDate = '';
                        }
                        if ($request->end_date != 0) {
                            $endDate = date('Y-m-d', strtotime(convertDate($request->end_date)));
                        } else {
                            $endDate = '';
                        }
                        if (isset($request->branch_id)) {
                            $branch_id = $request->branch_id;
                            $member_data = $member_data->where('associate_branch_id', $request->branch_id);
                        } else {
                            $branch_id = '';
                        }
                        if ($request->associate_id != 0) {
                            $member_data = $member_data->where('id', '=', $request->associate_id);
                        }
                        $member_data1 = $member_data->orderby('associate_join_date', 'DESC')->get();
                        $count = count($member_data1);
                        // if($request->page==1)
                        // {
                        //     $start=0;
                        // }
                        // else
                        // {
                        //     $start=($request->page-1)*$request->length;
                        // }
                        $member_data = $member_data->orderby('associate_join_date', 'DESC')->get();
                        foreach ($member_data as $key => $row) {
                            // $startDate='';$endDate='';$branch_id='';
                            $associate_id = $row->id;
                            $planDaily = getPlanID('710')->id;
                            $dailyId = array($planDaily);
                            $planSSB = getPlanID('703')->id;
                            $planKanyadhan = getPlanID('709')->id;
                            $planMB = getPlanID('708')->id;
                            $planFRD = getPlanID('707')->id;
                            $planJeevan = getPlanID('713')->id;
                            $planRD = getPlanID('704')->id;
                            $planBhavhishya = getPlanID('718')->id;
                            $monthlyId = array($planKanyadhan, $planMB, $planFRD, $planJeevan, $planRD, $planBhavhishya);
                            $planMI = getPlanID('712')->id;
                            $planFFD = getPlanID('705')->id;
                            $planFD = getPlanID('706')->id;
                            $fdId = array($planMI, $planFFD, $planFD);
                            $data[$key]['join_date'] = date("d/m/Y", strtotime($row->associate_join_date));
                            $data[$key]['branch'] = $row['associate_branch']->name;
                            $data[$key]['branch_code'] = $row['associate_branch']->branch_code;
                            $data[$key]['sector_name'] = $row['associate_branch']->sector;
                            $data[$key]['region_name'] = $row['associate_branch']->regan;
                            $data[$key]['zone_name'] = $row['associate_branch']->zone;
                            $data[$key]['member_id'] = $row->associate_no;
                            $data[$key]['name'] = $row->first_name . ' ' . $row->last_name;
                            $data[$key]['cadre'] = getCarderName($row->current_carder_id);
                            $data[$key]['daily_new_ac'] = investNewAcCount($associate_id, $startDate, $endDate, $planDaily, $branch_id);
                            $data[$key]['daily_deno_sum'] = investNewDenoSum($associate_id, $startDate, $endDate, $planDaily, $branch_id);
                            $data[$key]['daily_renew_ac'] = investRenewAc($associate_id, $startDate, $endDate, $dailyId, $branch_id);
                            $data[$key]['daily_renew'] = investRenewAmountSum($associate_id, $startDate, $endDate, $dailyId, $branch_id);
                            $data[$key]['monthly_new_ac'] = investNewAcCountType($associate_id, $startDate, $endDate, $monthlyId, $branch_id);
                            $data[$key]['monthly_deno_sum'] = investNewDenoSumType($associate_id, $startDate, $endDate, $monthlyId, $branch_id);
                            $data[$key]['monthly_renew_ac'] = investRenewAc($associate_id, $startDate, $endDate, $monthlyId, $branch_id);
                            $data[$key]['monthly_renew'] = investRenewAmountSum($associate_id, $startDate, $endDate, $monthlyId, $branch_id);
                            $data[$key]['fd_new_ac'] = investNewAcCountType($associate_id, $startDate, $endDate, $fdId, $branch_id);
                            $data[$key]['fd_deno_sum'] = investNewDenoSumType($associate_id, $startDate, $endDate, $fdId, $branch_id);
                            $data[$key]['ssb_new_ac'] = totalInvestSSbAcCountByType($associate_id, $startDate, $endDate, $branch_id, 1);
                            $data[$key]['ssb_deno_sum'] = totalInvestSSbAmtByType($associate_id, $startDate, $endDate, $branch_id, 1);
                            $data[$key]['ssb_renew_ac'] = totalInvestSSbAcCountByType($associate_id, $startDate, $endDate, $branch_id, 2);
                            $data[$key]['ssb_renew'] = totalInvestSSbAmtByType($associate_id, $startDate, $endDate, $branch_id, 2);
                            $sum_ni_ac = $data[$key]['daily_new_ac'] + $data[$key]['monthly_new_ac'] + $data[$key]['fd_new_ac'] + $data[$key]['ssb_new_ac'];
                            $sum_ni_amount = $data[$key]['daily_deno_sum'] + $data[$key]['monthly_deno_sum'] + $data[$key]['fd_deno_sum'] + $data[$key]['ssb_deno_sum'];
                            $data[$key]['total_ni_ac'] = $sum_ni_ac;
                            $data[$key]['total_ni_amount'] = number_format((float)$sum_ni_amount, 2, '.', '');
                            $sum_renew_ac = $data[$key]['daily_renew_ac'] + $data[$key]['monthly_renew_ac'] + $data[$key]['ssb_renew_ac'];
                            $sum_renew_amount = $data[$key]['daily_renew'] + $data[$key]['monthly_renew'] + $data[$key]['ssb_renew'];
                            $data[$key]['total_ac'] = $sum_renew_ac;
                            $data[$key]['total_amount'] = number_format((float)$sum_renew_amount, 2, '.', '');
                            $data[$key]['other_mt'] = investOtherMiByType($associate_id, $startDate, $endDate, $branch_id, 1, 11);
                            $data[$key]['other_stn'] = investOtherMiByType($associate_id, $startDate, $endDate, $branch_id, 1, 12);
                            $ni_m = $data[$key]['daily_deno_sum'] + $data[$key]['monthly_deno_sum'] + $data[$key]['fd_deno_sum'];
                            $tcc_m = $data[$key]['daily_deno_sum'] + $data[$key]['monthly_deno_sum'] + $data[$key]['fd_deno_sum'] + $data[$key]['daily_renew'] + $data[$key]['monthly_renew'];
                            $tcc = $data[$key]['daily_deno_sum'] + $data[$key]['monthly_deno_sum'] + $data[$key]['fd_deno_sum'] + $data[$key]['ssb_deno_sum'] + $data[$key]['daily_renew'] + $data[$key]['monthly_renew'] + $data[$key]['ssb_renew'];
                            $data[$key]['ni_m'] = number_format((float)$ni_m, 2, '.', '');
                            $data[$key]['ni'] = number_format((float)$sum_ni_amount, 2, '.', '');
                            $data[$key]['tcc_m'] = number_format((float)$tcc_m, 2, '.', '');
                            $data[$key]['tcc'] = number_format((float)$tcc, 2, '.', '');
                            $data[$key]['loan_ac'] = totalLoanAc($associate_id, $startDate, $endDate, $branch_id);
                            $data[$key]['loan_amount'] = totalLoanAmount($associate_id, $startDate, $endDate, $branch_id);
                            $data[$key]['loan_recovery_ac'] = totalRenewLoanAc($associate_id, $startDate, $endDate, $branch_id);
                            $data[$key]['loan_recovery_amount'] = totalRenewLoanAmount($associate_id, $startDate, $endDate, $branch_id);
                            $data[$key]['new_associate'] = memberCountByType($associate_id, $startDate, $endDate, $branch_id, 1, 0);
                            $data[$key]['total_associate'] = memberCountByType($associate_id, $startDate, $endDate, $branch_id, 1, 1);
                            $data[$key]['new_member'] = memberCountByType($associate_id, $startDate, $endDate, $branch_id, 0, 0);
                            $data[$key]['total_member'] = memberCountByType($associate_id, $startDate, $endDate, $branch_id, 0, 1);
                        }
                        $status   = "Success";
                        $code     = 200;
                        $messages = 'Associate Business Report !';
                        // $page  = $request->page;
                        // $length  = $request->length;
                        $result   = ['business_report' => $data, 'total_count' => $count, 'record_count' => count($data)];
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
        } catch (Exception $e) {
            $status = "Error";
            $code = 500;
            $messages = $e->getMessage();
            $result = '';
            $associate_status = 9;
            \Log::info($messages);
            return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
        }
    }
    public function associate_business_summary_report(Request $request)
    {
        $associate_no = $request->associate_no;
        try {
            $member = Member::select('id', 'associate_app_status')->where('associate_no', $request->associate_no)->where('associate_status', 1)->where('is_block', 0)->first();
            if ($member) {
                $token = md5($request->associate_no);
                if ($token == $request->token) {
                    if ($request->page > 0 && $request->length > 0) {
                        $data = array();
                        $a = $this->getCategoryTree($parent_id = $member->id, $tree_array = array());
                        $b[] = array('member_id' => $member->id, 'status' => 1, 'is_block' => 0);
                        $tree_array1 = array();
                        $c = array_merge($a, $b);
                        foreach ($c as $v) {
                            if ($v['status'] == 1 && $v['is_block'] == 0) {
                                $tree_array1[] =  $v['member_id'];
                            }
                        }
                        $member_data = Member::with('associate_branch')->whereIn('id', $tree_array1);
                        if ($request->from_date != 0) {
                            $startDate = date('Y-m-d', strtotime(convertDate($request->from_date)));
                        } else {
                            $startDate = '';
                        }
                        if ($request->to_date != 0) {
                            $endDate = date('Y-m-d', strtotime(convertDate($request->to_date)));
                        } else {
                            $endDate = '';
                        }
                        if (isset($request->branch_id)) {
                            $branch_id = $request->branch_id;
                            $member_data = $member_data->where('associate_branch_id', $request->branch_id);
                        } else {
                            $branch_id = '';
                        }
                        if ($request->associate_id != 0) {
                            $member_data = $member_data->where('id', '=', $request->associate_id);
                        }
                        $member_data1 = $member_data->orderby('associate_join_date', 'DESC')->get();
                        $count = count($member_data1);
                        if ($request->page == 1) {
                            $start = 0;
                        } else {
                            $start = ($request->page - 1) * $request->length;
                        }
                        $member_data = $member_data->orderby('associate_join_date', 'DESC')->offset($start)->limit($request->length)->get();
                        foreach ($member_data as $key => $row) {
                            //$startDate='';$endDate='';$branch_id='';
                            $associate_id = $row->id;
                            $planDaily = getPlanID('710')->id;
                            $planSSB = getPlanID('703')->id;
                            $planKanyadhan = getPlanID('709')->id;
                            $planMB = getPlanID('708')->id;
                            $planFFD = getPlanID('705')->id;
                            $planFRD = getPlanID('707')->id;
                            $planJeevan = getPlanID('713')->id;
                            $planMI = getPlanID('712')->id;
                            $planFD = getPlanID('706')->id;
                            $planRD = getPlanID('704')->id;
                            $planBhavhishya = getPlanID('718')->id;
                            $planids = array($planDaily, $planSSB, $planKanyadhan, $planMB, $planFFD, $planFRD, $planJeevan, $planMI, $planFD, $planRD, $planBhavhishya,);
                            $data[$key]['join_date'] = date("d/m/Y", strtotime($row->associate_join_date));
                            $data[$key]['branch'] = $row['associate_branch']->name;
                            $data[$key]['branch_code'] = $row['associate_branch']->branch_code;
                            $data[$key]['sector_name'] = $row['associate_branch']->sector;
                            $data[$key]['region_name'] = $row['associate_branch']->regan;
                            $data[$key]['zone_name'] = $row['associate_branch']->zone;
                            $data[$key]['member_id'] = $row->associate_no;
                            $data[$key]['name'] = $row->first_name . ' ' . $row->last_name;
                            $data[$key]['cadre'] = getCarderName($row->current_carder_id);
                            $data[$key]['daily_new_ac'] = investNewAcCount($associate_id, $startDate, $endDate, $planDaily, $branch_id);
                            $data[$key]['daily_deno_sum'] = investNewDenoSum($associate_id, $startDate, $endDate, $planDaily, $branch_id);
                            $data[$key]['daily_renew_ac'] = investRenewAcPlan($associate_id, $startDate, $endDate, $planDaily, $branch_id);
                            $data[$key]['daily_renew'] = investRenewAmountSumPlan($associate_id, $startDate, $endDate, $planDaily, $branch_id);
                            $data[$key]['ssb_new_ac'] = totalInvestSSbAcCountByType($associate_id, $startDate, $endDate, $branch_id, 1);
                            $data[$key]['ssb_deno_sum'] = totalInvestSSbAmtByType($associate_id, $startDate, $endDate, $branch_id, 1);
                            $data[$key]['ssb_renew_ac'] = totalInvestSSbAcCountByType($associate_id, $startDate, $endDate, $branch_id, 2);
                            $data[$key]['ssb_renew'] = totalInvestSSbAmtByType($associate_id, $startDate, $endDate, $branch_id, 2);
                            $data[$key]['kanyadhan_new_ac'] = investNewAcCount($associate_id, $startDate, $endDate, $planKanyadhan, $branch_id);
                            $data[$key]['kanyadhan_deno_sum'] = investNewDenoSum($associate_id, $startDate, $endDate, $planKanyadhan, $branch_id);
                            $data[$key]['kanyadhan_renew_ac'] = investRenewAcPlan($associate_id, $startDate, $endDate, $planKanyadhan, $branch_id);
                            $data[$key]['kanyadhan_renew'] = investRenewAmountSumPlan($associate_id, $startDate, $endDate, $planKanyadhan, $branch_id);
                            $data[$key]['mb_new_ac'] = investNewAcCount($associate_id, $startDate, $endDate, $planMB, $branch_id);
                            $data[$key]['mb_deno_sum'] = investNewDenoSum($associate_id, $startDate, $endDate, $planMB, $branch_id);
                            $data[$key]['mb_renew_ac'] = investRenewAcPlan($associate_id, $startDate, $endDate, $planMB, $branch_id);
                            $data[$key]['mb_renew'] = investRenewAmountSumPlan($associate_id, $startDate, $endDate, $planMB, $branch_id);
                            $data[$key]['ffd_new_ac'] = investNewAcCount($associate_id, $startDate, $endDate, $planFFD, $branch_id);
                            $data[$key]['ffd_deno_sum'] = investNewDenoSum($associate_id, $startDate, $endDate, $planFFD, $branch_id);
                            $data[$key]['frd_new_ac'] = investNewAcCount($associate_id, $startDate, $endDate, $planFRD, $branch_id);
                            $data[$key]['frd_deno_sum'] = investNewDenoSum($associate_id, $startDate, $endDate, $planFRD, $branch_id);
                            $data[$key]['frd_renew_ac'] = investRenewAcPlan($associate_id, $startDate, $endDate, $planFRD, $branch_id);
                            $data[$key]['frd_renew'] = investRenewAmountSumPlan($associate_id, $startDate, $endDate, $planFRD, $branch_id);
                            $data[$key]['jeevan_new_ac'] = investNewAcCount($associate_id, $startDate, $endDate, $planJeevan, $branch_id);
                            $data[$key]['jeevan_deno_sum'] = investNewDenoSum($associate_id, $startDate, $endDate, $planJeevan, $branch_id);
                            $data[$key]['jeevan_renew_ac'] = investRenewAcPlan($associate_id, $startDate, $endDate, $planJeevan, $branch_id);;
                            $data[$key]['jeevan_renew'] = investRenewAmountSumPlan($associate_id, $startDate, $endDate, $planJeevan, $branch_id);
                            $data[$key]['mi_new_ac'] = investNewAcCount($associate_id, $startDate, $endDate, $planMI, $branch_id);
                            $data[$key]['mi_deno_sum'] = investNewDenoSum($associate_id, $startDate, $endDate, $planMI, $branch_id);
                            $data[$key]['mi_renew_ac'] = investRenewAcPlan($associate_id, $startDate, $endDate, $planMI, $branch_id);;
                            $data[$key]['mi_renew'] = investRenewAmountSumPlan($associate_id, $startDate, $endDate, $planMI, $branch_id);
                            $data[$key]['fd_new_ac'] = investNewAcCount($associate_id, $startDate, $endDate, $planFD, $branch_id);
                            $data[$key]['fd_deno_sum'] = investNewDenoSum($associate_id, $startDate, $endDate, $planFD, $branch_id);
                            $data[$key]['rd_new_ac'] = investNewAcCount($associate_id, $startDate, $endDate, $planRD, $branch_id);
                            $data[$key]['rd_deno_sum'] = investNewDenoSum($associate_id, $startDate, $endDate, $planRD, $branch_id);
                            $data[$key]['rd_renew_ac'] = investRenewAcPlan($associate_id, $startDate, $endDate, $planRD, $branch_id);
                            $data[$key]['rd_renew'] = investRenewAmountSumPlan($associate_id, $startDate, $endDate, $planRD, $branch_id);
                            $data[$key]['bhavhishya_new_ac'] = investNewAcCount($associate_id, $startDate, $endDate, $planBhavhishya, $branch_id);
                            $data[$key]['bhavhishya_deno_sum'] = investNewDenoSum($associate_id, $startDate, $endDate, $planBhavhishya, $branch_id);
                            $data[$key]['bhavhishya_renew_ac'] = investRenewAcPlan($associate_id, $startDate, $endDate, $planBhavhishya, $branch_id);
                            $data[$key]['bhavhishya_renew'] = investRenewAmountSumPlan($associate_id, $startDate, $endDate, $planBhavhishya, $branch_id);
                            $sum_ni_ac = $data[$key]['daily_new_ac'] + $data[$key]['ssb_new_ac'] + $data[$key]['kanyadhan_new_ac'] + $data[$key]['mb_new_ac'] + $data[$key]['ffd_new_ac'] + $data[$key]['frd_new_ac'] + $data[$key]['jeevan_new_ac'] + $data[$key]['mi_new_ac'] + $data[$key]['fd_new_ac'] + $data[$key]['rd_new_ac'] + $data[$key]['bhavhishya_new_ac'];
                            $sum_ni_amount = $data[$key]['daily_deno_sum'] + $data[$key]['ssb_deno_sum'] + $data[$key]['kanyadhan_deno_sum'] + $data[$key]['mb_deno_sum'] + $data[$key]['ffd_deno_sum'] + $data[$key]['frd_deno_sum'] + $data[$key]['jeevan_deno_sum'] + $data[$key]['mi_deno_sum'] + $data[$key]['fd_deno_sum'] + $data[$key]['rd_deno_sum'] + $data[$key]['bhavhishya_deno_sum'];
                            $sum_renew_ac = investRenewAc($associate_id, $startDate, $endDate, $planids, $branch_id);
                            $sum_renew_amount = investRenewAmountSum($associate_id, $startDate, $planids, $planids, $branch_id);
                            $data[$key]['total_ni_ac'] = $sum_ni_ac;
                            $data[$key]['total_ni_amount'] = number_format((float)$sum_ni_amount, 2, '.', '');
                            $data[$key]['other_mt'] = investOtherMiByType($associate_id, $startDate, $endDate, $branch_id, 1, 11);
                            $data[$key]['other_stn'] = investOtherMiByType($associate_id, $startDate, $endDate, $branch_id, 1, 12);
                            $ni_m = $data[$key]['daily_deno_sum'] + $data[$key]['kanyadhan_deno_sum'] + $data[$key]['mb_deno_sum'] + $data[$key]['ffd_deno_sum'] + $data[$key]['frd_deno_sum'] + $data[$key]['jeevan_deno_sum'] + $data[$key]['mi_deno_sum'] + $data[$key]['fd_deno_sum'] + $data[$key]['rd_deno_sum'] + $data[$key]['bhavhishya_deno_sum'];
                            $tcc_m = $data[$key]['daily_deno_sum'] + $data[$key]['kanyadhan_deno_sum'] + $data[$key]['mb_deno_sum'] + $data[$key]['ffd_deno_sum'] + $data[$key]['frd_deno_sum'] + $data[$key]['jeevan_deno_sum'] + $data[$key]['mi_deno_sum'] + $data[$key]['fd_deno_sum'] + $data[$key]['rd_deno_sum'] + $data[$key]['bhavhishya_deno_sum'] + $data[$key]['bhavhishya_renew'] + $data[$key]['rd_renew'] + $data[$key]['mi_renew'] + $data[$key]['jeevan_renew'] + $data[$key]['frd_renew'] + $data[$key]['mb_renew'] + $data[$key]['kanyadhan_renew'] + $data[$key]['daily_renew'];
                            $tcc = $data[$key]['daily_deno_sum'] + $data[$key]['kanyadhan_deno_sum'] + $data[$key]['mb_deno_sum'] + $data[$key]['ffd_deno_sum'] + $data[$key]['frd_deno_sum'] + $data[$key]['jeevan_deno_sum'] + $data[$key]['mi_deno_sum'] + $data[$key]['fd_deno_sum'] + $data[$key]['rd_deno_sum'] + $data[$key]['bhavhishya_deno_sum'] + $data[$key]['ssb_deno_sum'] + $data[$key]['bhavhishya_renew'] + $data[$key]['rd_renew'] + $data[$key]['mi_renew'] + $data[$key]['jeevan_renew'] + $data[$key]['frd_renew'] + $data[$key]['mb_renew'] + $data[$key]['kanyadhan_renew'] + $data[$key]['ssb_renew'] + $data[$key]['daily_renew'];
                            $data[$key]['ni_m'] = number_format((float)$ni_m, 2, '.', '');
                            $data[$key]['ni'] = number_format((float)$sum_ni_amount, 2, '.', '');
                            $data[$key]['tcc_m'] = number_format((float)$tcc_m, 2, '.', '');
                            $data[$key]['tcc'] = number_format((float)$tcc, 2, '.', '');
                            $data[$key]['st_loan_ac'] = associateLoanTypeAC($associate_id, $startDate, $endDate, $branch_id, 2);
                            $data[$key]['st_loan_amount'] = associateLoanTypeAmount($associate_id, $startDate, $endDate, $branch_id, 2);
                            $data[$key]['pl_loan_ac'] = associateLoanTypeAC($associate_id, $startDate, $endDate, $branch_id, 1);
                            $data[$key]['pl_loan_amount'] = associateLoanTypeAmount($associate_id, $startDate, $endDate, $branch_id, 1);
                            $data[$key]['la_loan_ac'] = associateLoanTypeAC($associate_id, $startDate, $endDate, $branch_id, 4);
                            $data[$key]['la_loan_amount'] = associateLoanTypeAmount($associate_id, $startDate, $endDate, $branch_id, 4);
                            $data[$key]['gp_loan_ac'] = associateLoanTypeAC($associate_id, $startDate, $endDate, $branch_id, 3);
                            $data[$key]['gp_loan_amount'] = associateLoanTypeAmount($associate_id, $startDate, $endDate, $branch_id, 3);
                            $data[$key]['loan_ac'] = $data[$key]['st_loan_ac'] + $data[$key]['pl_loan_ac'] + $data[$key]['la_loan_ac'] + $data[$key]['gp_loan_ac'];
                            $data[$key]['loan_amount'] = $data[$key]['st_loan_amount'] + $data[$key]['pl_loan_amount'] + $data[$key]['la_loan_amount'] + $data[$key]['gp_loan_amount'];
                            $data[$key]['st_loan_recovery_ac'] = associateLoanTypeRecoverAc($associate_id, $startDate, $endDate, $branch_id, 2);
                            $data[$key]['st_loan_recovery_amount'] = associateLoanTypeRecoverAmount($associate_id, $startDate, $endDate, $branch_id, 2);
                            $data[$key]['pl_loan_recovery_ac'] = associateLoanTypeRecoverAc($associate_id, $startDate, $endDate, $branch_id, 1);
                            $data[$key]['pl_loan_recovery_amount'] = associateLoanTypeRecoverAmount($associate_id, $startDate, $endDate, $branch_id, 1);
                            $data[$key]['la_loan_recovery_ac'] = associateLoanTypeRecoverAc($associate_id, $startDate, $endDate, $branch_id, 4);
                            $data[$key]['la_loan_recovery_amount'] = associateLoanTypeRecoverAmount($associate_id, $startDate, $endDate, $branch_id, 4);
                            $data[$key]['gp_loan_recovery_ac'] = associateLoanTypeRecoverAc($associate_id, $startDate, $endDate, $branch_id, 3);
                            $data[$key]['gp_loan_recovery_amount'] = associateLoanTypeRecoverAmount($associate_id, $startDate, $endDate, $branch_id, 3);
                            $data[$key]['loan_recovery_ac'] = $data[$key]['st_loan_recovery_ac'] + $data[$key]['pl_loan_recovery_ac'] + $data[$key]['la_loan_recovery_ac'] + $data[$key]['gp_loan_recovery_ac'];
                            $data[$key]['loan_recovery_amount'] = $data[$key]['st_loan_recovery_amount'] + $data[$key]['pl_loan_recovery_amount'] + $data[$key]['la_loan_recovery_amount'] + $data[$key]['gp_loan_recovery_amount'];
                            $data[$key]['new_associate'] = memberCountByType($associate_id, $startDate, $endDate, $branch_id, 1, 0);
                            $data[$key]['total_associate'] = memberCountByType($associate_id, $startDate, $endDate, $branch_id, 1, 1);
                            $data[$key]['total_ac'] = $data[$key]['st_loan_ac'] + $data[$key]['pl_loan_ac'] + $data[$key]['la_loan_ac'] + $data[$key]['gp_loan_ac'];;
                            $data[$key]['total_amount'] = $data[$key]['st_loan_amount'] + $data[$key]['pl_loan_amount'] + $data[$key]['la_loan_amount'] + $data[$key]['gp_loan_amount'];
                            $data[$key]['new_member'] = memberCountByType($associate_id, $startDate, $endDate, $branch_id, 0, 0);
                            $data[$key]['total_member'] = memberCountByType($associate_id, $startDate, $endDate, $branch_id, 0, 1);
                        }
                        $status   = "Success";
                        $code     = 200;
                        $messages = 'Associate Business Summary  Report !';
                        $page  = $request->page;
                        $length  = $request->length;
                        $result   = ['business_summary_report' => $data, 'total_count' => $count, 'page' => $page, 'length' => $length, 'record_count' => count($data)];
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    } elseif (!isset($request->length)) {
                        $data = array();
                        $a = $this->getCategoryTree($parent_id = $member->id, $tree_array = array());
                        $b[] = array('member_id' => $member->id, 'status' => 1, 'is_block' => 0);
                        $tree_array1 = array();
                        $c = array_merge($a, $b);
                        foreach ($c as $v) {
                            if ($v['status'] == 1 && $v['is_block'] == 0) {
                                $tree_array1[] =  $v['member_id'];
                            }
                        }
                        $member_data = Member::with('associate_branch')->whereIn('id', $tree_array1);
                        if ($request->from_date != 0) {
                            $startDate = date('Y-m-d', strtotime(convertDate($request->from_date)));
                        } else {
                            $startDate = '';
                        }
                        if ($request->to_date != 0) {
                            $endDate = date('Y-m-d', strtotime(convertDate($request->to_date)));
                        } else {
                            $endDate = '';
                        }
                        if (isset($request->branch_id)) {
                            $branch_id = $request->branch_id;
                            $member_data = $member_data->where('associate_branch_id', $request->branch_id);
                        } else {
                            $branch_id = '';
                        }
                        if ($request->associate_id != 0) {
                            $member_data = $member_data->where('id', '=', $request->associate_id);
                        }
                        $member_data1 = $member_data->orderby('associate_join_date', 'DESC')->count();
                        $count = ($member_data1);
                        if ($request->page == 1) {
                            $start = 0;
                        } else {
                            $start = ($request->page - 1) * $request->length;
                        }
                        $member_data = $member_data->orderby('associate_join_date', 'DESC')->get();
                        foreach ($member_data as $key => $row) {
                            //$startDate='';$endDate='';$branch_id='';
                            $associate_id = $row->id;
                            $planDaily = getPlanID('710')->id;
                            $planSSB = getPlanID('703')->id;
                            $planKanyadhan = getPlanID('709')->id;
                            $planMB = getPlanID('708')->id;
                            $planFFD = getPlanID('705')->id;
                            $planFRD = getPlanID('707')->id;
                            $planJeevan = getPlanID('713')->id;
                            $planMI = getPlanID('712')->id;
                            $planFD = getPlanID('706')->id;
                            $planRD = getPlanID('704')->id;
                            $planBhavhishya = getPlanID('718')->id;
                            $planids = array($planDaily, $planSSB, $planKanyadhan, $planMB, $planFFD, $planFRD, $planJeevan, $planMI, $planFD, $planRD, $planBhavhishya,);
                            $data[$key]['join_date'] = date("d/m/Y", strtotime($row->associate_join_date));
                            $data[$key]['branch'] = $row['associate_branch']->name;
                            $data[$key]['branch_code'] = $row['associate_branch']->branch_code;
                            $data[$key]['sector_name'] = $row['associate_branch']->sector;
                            $data[$key]['region_name'] = $row['associate_branch']->regan;
                            $data[$key]['zone_name'] = $row['associate_branch']->zone;
                            $data[$key]['member_id'] = $row->associate_no;
                            $data[$key]['name'] = $row->first_name . ' ' . $row->last_name;
                            $data[$key]['cadre'] = getCarderName($row->current_carder_id);
                            $data[$key]['daily_new_ac'] = investNewAcCount($associate_id, $startDate, $endDate, $planDaily, $branch_id);
                            $data[$key]['daily_deno_sum'] = investNewDenoSum($associate_id, $startDate, $endDate, $planDaily, $branch_id);
                            $data[$key]['daily_renew_ac'] = investRenewAcPlan($associate_id, $startDate, $endDate, $planDaily, $branch_id);
                            $data[$key]['daily_renew'] = investRenewAmountSumPlan($associate_id, $startDate, $endDate, $planDaily, $branch_id);
                            $data[$key]['ssb_new_ac'] = totalInvestSSbAcCountByType($associate_id, $startDate, $endDate, $branch_id, 1);
                            $data[$key]['ssb_deno_sum'] = totalInvestSSbAmtByType($associate_id, $startDate, $endDate, $branch_id, 1);
                            $data[$key]['ssb_renew_ac'] = totalInvestSSbAcCountByType($associate_id, $startDate, $endDate, $branch_id, 2);
                            $data[$key]['ssb_renew'] = totalInvestSSbAmtByType($associate_id, $startDate, $endDate, $branch_id, 2);
                            $data[$key]['kanyadhan_new_ac'] = investNewAcCount($associate_id, $startDate, $endDate, $planKanyadhan, $branch_id);
                            $data[$key]['kanyadhan_deno_sum'] = investNewDenoSum($associate_id, $startDate, $endDate, $planKanyadhan, $branch_id);
                            $data[$key]['kanyadhan_renew_ac'] = investRenewAcPlan($associate_id, $startDate, $endDate, $planKanyadhan, $branch_id);
                            $data[$key]['kanyadhan_renew'] = investRenewAmountSumPlan($associate_id, $startDate, $endDate, $planKanyadhan, $branch_id);
                            $data[$key]['mb_new_ac'] = investNewAcCount($associate_id, $startDate, $endDate, $planMB, $branch_id);
                            $data[$key]['mb_deno_sum'] = investNewDenoSum($associate_id, $startDate, $endDate, $planMB, $branch_id);
                            $data[$key]['mb_renew_ac'] = investRenewAcPlan($associate_id, $startDate, $endDate, $planMB, $branch_id);
                            $data[$key]['mb_renew'] = investRenewAmountSumPlan($associate_id, $startDate, $endDate, $planMB, $branch_id);
                            $data[$key]['ffd_new_ac'] = investNewAcCount($associate_id, $startDate, $endDate, $planFFD, $branch_id);
                            $data[$key]['ffd_deno_sum'] = investNewDenoSum($associate_id, $startDate, $endDate, $planFFD, $branch_id);
                            $data[$key]['frd_new_ac'] = investNewAcCount($associate_id, $startDate, $endDate, $planFRD, $branch_id);
                            $data[$key]['frd_deno_sum'] = investNewDenoSum($associate_id, $startDate, $endDate, $planFRD, $branch_id);
                            $data[$key]['frd_renew_ac'] = investRenewAcPlan($associate_id, $startDate, $endDate, $planFRD, $branch_id);
                            $data[$key]['frd_renew'] = investRenewAmountSumPlan($associate_id, $startDate, $endDate, $planFRD, $branch_id);
                            $data[$key]['jeevan_new_ac'] = investNewAcCount($associate_id, $startDate, $endDate, $planJeevan, $branch_id);
                            $data[$key]['jeevan_deno_sum'] = investNewDenoSum($associate_id, $startDate, $endDate, $planJeevan, $branch_id);
                            $data[$key]['jeevan_renew_ac'] = investRenewAcPlan($associate_id, $startDate, $endDate, $planJeevan, $branch_id);;
                            $data[$key]['jeevan_renew'] = investRenewAmountSumPlan($associate_id, $startDate, $endDate, $planJeevan, $branch_id);
                            $data[$key]['mi_new_ac'] = investNewAcCount($associate_id, $startDate, $endDate, $planMI, $branch_id);
                            $data[$key]['mi_deno_sum'] = investNewDenoSum($associate_id, $startDate, $endDate, $planMI, $branch_id);
                            $data[$key]['mi_renew_ac'] = investRenewAcPlan($associate_id, $startDate, $endDate, $planMI, $branch_id);;
                            $data[$key]['mi_renew'] = investRenewAmountSumPlan($associate_id, $startDate, $endDate, $planMI, $branch_id);
                            $data[$key]['fd_new_ac'] = investNewAcCount($associate_id, $startDate, $endDate, $planFD, $branch_id);
                            $data[$key]['fd_deno_sum'] = investNewDenoSum($associate_id, $startDate, $endDate, $planFD, $branch_id);
                            $data[$key]['rd_new_ac'] = investNewAcCount($associate_id, $startDate, $endDate, $planRD, $branch_id);
                            $data[$key]['rd_deno_sum'] = investNewDenoSum($associate_id, $startDate, $endDate, $planRD, $branch_id);
                            $data[$key]['rd_renew_ac'] = investRenewAcPlan($associate_id, $startDate, $endDate, $planRD, $branch_id);
                            $data[$key]['rd_renew'] = investRenewAmountSumPlan($associate_id, $startDate, $endDate, $planRD, $branch_id);
                            $data[$key]['bhavhishya_new_ac'] = investNewAcCount($associate_id, $startDate, $endDate, $planBhavhishya, $branch_id);
                            $data[$key]['bhavhishya_deno_sum'] = investNewDenoSum($associate_id, $startDate, $endDate, $planBhavhishya, $branch_id);
                            $data[$key]['bhavhishya_renew_ac'] = investRenewAcPlan($associate_id, $startDate, $endDate, $planBhavhishya, $branch_id);
                            $data[$key]['bhavhishya_renew'] = investRenewAmountSumPlan($associate_id, $startDate, $endDate, $planBhavhishya, $branch_id);
                            $sum_ni_ac = $data[$key]['daily_new_ac'] + $data[$key]['ssb_new_ac'] + $data[$key]['kanyadhan_new_ac'] + $data[$key]['mb_new_ac'] + $data[$key]['ffd_new_ac'] + $data[$key]['frd_new_ac'] + $data[$key]['jeevan_new_ac'] + $data[$key]['mi_new_ac'] + $data[$key]['fd_new_ac'] + $data[$key]['rd_new_ac'] + $data[$key]['bhavhishya_new_ac'];
                            $sum_ni_amount = $data[$key]['daily_deno_sum'] + $data[$key]['ssb_deno_sum'] + $data[$key]['kanyadhan_deno_sum'] + $data[$key]['mb_deno_sum'] + $data[$key]['ffd_deno_sum'] + $data[$key]['frd_deno_sum'] + $data[$key]['jeevan_deno_sum'] + $data[$key]['mi_deno_sum'] + $data[$key]['fd_deno_sum'] + $data[$key]['rd_deno_sum'] + $data[$key]['bhavhishya_deno_sum'];
                            $sum_renew_ac = investRenewAc($associate_id, $startDate, $endDate, $planids, $branch_id);
                            $sum_renew_amount = investRenewAmountSum($associate_id, $startDate, $planids, $planids, $branch_id);
                            $data[$key]['total_ni_ac'] = $sum_ni_ac;
                            $data[$key]['total_ni_amount'] = number_format((float)$sum_ni_amount, 2, '.', '');
                            $data[$key]['other_mt'] = investOtherMiByType($associate_id, $startDate, $endDate, $branch_id, 1, 11);
                            $data[$key]['other_stn'] = investOtherMiByType($associate_id, $startDate, $endDate, $branch_id, 1, 12);
                            $ni_m = $data[$key]['daily_deno_sum'] + $data[$key]['kanyadhan_deno_sum'] + $data[$key]['mb_deno_sum'] + $data[$key]['ffd_deno_sum'] + $data[$key]['frd_deno_sum'] + $data[$key]['jeevan_deno_sum'] + $data[$key]['mi_deno_sum'] + $data[$key]['fd_deno_sum'] + $data[$key]['rd_deno_sum'] + $data[$key]['bhavhishya_deno_sum'];
                            $tcc_m = $data[$key]['daily_deno_sum'] + $data[$key]['kanyadhan_deno_sum'] + $data[$key]['mb_deno_sum'] + $data[$key]['ffd_deno_sum'] + $data[$key]['frd_deno_sum'] + $data[$key]['jeevan_deno_sum'] + $data[$key]['mi_deno_sum'] + $data[$key]['fd_deno_sum'] + $data[$key]['rd_deno_sum'] + $data[$key]['bhavhishya_deno_sum'] + $data[$key]['bhavhishya_renew'] + $data[$key]['rd_renew'] + $data[$key]['mi_renew'] + $data[$key]['jeevan_renew'] + $data[$key]['frd_renew'] + $data[$key]['mb_renew'] + $data[$key]['kanyadhan_renew'] + $data[$key]['daily_renew'];
                            $tcc = $data[$key]['daily_deno_sum'] + $data[$key]['kanyadhan_deno_sum'] + $data[$key]['mb_deno_sum'] + $data[$key]['ffd_deno_sum'] + $data[$key]['frd_deno_sum'] + $data[$key]['jeevan_deno_sum'] + $data[$key]['mi_deno_sum'] + $data[$key]['fd_deno_sum'] + $data[$key]['rd_deno_sum'] + $data[$key]['bhavhishya_deno_sum'] + $data[$key]['ssb_deno_sum'] + $data[$key]['bhavhishya_renew'] + $data[$key]['rd_renew'] + $data[$key]['mi_renew'] + $data[$key]['jeevan_renew'] + $data[$key]['frd_renew'] + $data[$key]['mb_renew'] + $data[$key]['kanyadhan_renew'] + $data[$key]['ssb_renew'] + $data[$key]['daily_renew'];
                            $data[$key]['ni_m'] = number_format((float)$ni_m, 2, '.', '');
                            $data[$key]['ni'] = number_format((float)$sum_ni_amount, 2, '.', '');
                            $data[$key]['tcc_m'] = number_format((float)$tcc_m, 2, '.', '');
                            $data[$key]['tcc'] = number_format((float)$tcc, 2, '.', '');
                            $data[$key]['st_loan_ac'] = associateLoanTypeAC($associate_id, $startDate, $endDate, $branch_id, 2);
                            $data[$key]['st_loan_amount'] = associateLoanTypeAmount($associate_id, $startDate, $endDate, $branch_id, 2);
                            $data[$key]['pl_loan_ac'] = associateLoanTypeAC($associate_id, $startDate, $endDate, $branch_id, 1);
                            $data[$key]['pl_loan_amount'] = associateLoanTypeAmount($associate_id, $startDate, $endDate, $branch_id, 1);
                            $data[$key]['la_loan_ac'] = associateLoanTypeAC($associate_id, $startDate, $endDate, $branch_id, 4);
                            $data[$key]['la_loan_amount'] = associateLoanTypeAmount($associate_id, $startDate, $endDate, $branch_id, 4);
                            $data[$key]['gp_loan_ac'] = associateLoanTypeAC($associate_id, $startDate, $endDate, $branch_id, 3);
                            $data[$key]['gp_loan_amount'] = associateLoanTypeAmount($associate_id, $startDate, $endDate, $branch_id, 3);
                            $data[$key]['loan_ac'] = $data[$key]['st_loan_ac'] + $data[$key]['pl_loan_ac'] + $data[$key]['la_loan_ac'] + $data[$key]['gp_loan_ac'];
                            $data[$key]['loan_amount'] = $data[$key]['st_loan_amount'] + $data[$key]['pl_loan_amount'] + $data[$key]['la_loan_amount'] + $data[$key]['gp_loan_amount'];
                            $data[$key]['st_loan_recovery_ac'] = associateLoanTypeRecoverAc($associate_id, $startDate, $endDate, $branch_id, 2);
                            $data[$key]['st_loan_recovery_amount'] = associateLoanTypeRecoverAmount($associate_id, $startDate, $endDate, $branch_id, 2);
                            $data[$key]['pl_loan_recovery_ac'] = associateLoanTypeRecoverAc($associate_id, $startDate, $endDate, $branch_id, 1);
                            $data[$key]['pl_loan_recovery_amount'] = associateLoanTypeRecoverAmount($associate_id, $startDate, $endDate, $branch_id, 1);
                            $data[$key]['la_loan_recovery_ac'] = associateLoanTypeRecoverAc($associate_id, $startDate, $endDate, $branch_id, 4);
                            $data[$key]['la_loan_recovery_amount'] = associateLoanTypeRecoverAmount($associate_id, $startDate, $endDate, $branch_id, 4);
                            $data[$key]['gp_loan_recovery_ac'] = associateLoanTypeRecoverAc($associate_id, $startDate, $endDate, $branch_id, 3);
                            $data[$key]['gp_loan_recovery_amount'] = associateLoanTypeRecoverAmount($associate_id, $startDate, $endDate, $branch_id, 3);
                            $data[$key]['loan_recovery_ac'] = $data[$key]['st_loan_recovery_ac'] + $data[$key]['pl_loan_recovery_ac'] + $data[$key]['la_loan_recovery_ac'] + $data[$key]['gp_loan_recovery_ac'];
                            $data[$key]['loan_recovery_amount'] = $data[$key]['st_loan_recovery_amount'] + $data[$key]['pl_loan_recovery_amount'] + $data[$key]['la_loan_recovery_amount'] + $data[$key]['gp_loan_recovery_amount'];
                            $data[$key]['new_associate'] = memberCountByType($associate_id, $startDate, $endDate, $branch_id, 1, 0);
                            $data[$key]['total_associate'] = memberCountByType($associate_id, $startDate, $endDate, $branch_id, 1, 1);
                            $data[$key]['total_ac'] = $data[$key]['st_loan_ac'] + $data[$key]['pl_loan_ac'] + $data[$key]['la_loan_ac'] + $data[$key]['gp_loan_ac'];;
                            $data[$key]['total_amount'] = $data[$key]['st_loan_amount'] + $data[$key]['pl_loan_amount'] + $data[$key]['la_loan_amount'] + $data[$key]['gp_loan_amount'];
                            $data[$key]['new_member'] = memberCountByType($associate_id, $startDate, $endDate, $branch_id, 0, 0);
                            $data[$key]['total_member'] = memberCountByType($associate_id, $startDate, $endDate, $branch_id, 0, 1);
                        }
                        $status   = "Success";
                        $code     = 200;
                        $messages = 'Associate Business Summary  Report !';
                        $result   = ['business_summary_report' => $data, 'total_count' => $count, 'record_count' => count($data)];
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
        } catch (Exception $e) {
            $status = "Error";
            $code = 500;
            $messages = $e->getMessage();
            $result = '';
            $associate_status = 9;
            return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
        }
    }
    //    public function associate_business_compare_report(Request $request)
    //     {   
    //         $associate_no = $request->associate_no;  
    //         try { 
    //         $member = Member::select('id','associate_app_status')->where('associate_no',$request->associate_no)->where('associate_status',1)->where('is_block',0)->first();
    //             if ($member) {
    //                 $token = md5($request->associate_no);
    //                 if($token == $request->token){ 
    //                     if($request->current_start_date !='' && $request->current_start_date != 0){
    //                     $current_start_date=date("Y-m-d", strtotime(convertDate($request->current_start_date)));
    //                     } 
    //                     elseif($request->current_start_date == '0'){
    //                         $current_start_date = date('d/m/Y',strtotime('first day of -1 months'));
    //                     }
    //                 if($request->current_end_date!='' && $request->current_end_date != 0){
    //                      $current_end_date=date("Y-m-d ", strtotime(convertDate($request->current_end_date)));
    //                 } 
    //                  elseif($request->current_end_date == 0){
    //                     $current_end_date=date('d/m/Y',strtotime('last day of -1 months'));
    //                 }
    //                 if($request->comp_start_date !=''  && $request->comp_start_date != 0){
    //                     $comp_start_date=date("Y-m-d", strtotime(convertDate($request->comp_start_date)));
    //                 } 
    //                 elseif($request->comp_start_date == 0){
    //                     $comp_start_date=date('d/m/Y',strtotime('first day of -2 months'));
    //                 }
    //                 if($request->comp_end_date !=''  && $request->comp_end_date != 0){
    //                      $comp_end_date=date("Y-m-d ", strtotime(convertDate($request->comp_end_date)));
    //                 }
    //                 else if($request->comp_end_date == 0){
    //                     $comp_end_date=date('d/m/Y',strtotime('last day of -2 months'));
    //                 }
    //                 if($current_start_date!='' && $current_end_date!='' && $comp_start_date!='' && $comp_end_date!='')
    //                 { 
    //                     if($request->page>0 && $request->length>0 )
    //                     { 
    //                         // if($request->current_start_date !=''){
    //                         //     $current_start_date=date("Y-m-d", strtotime(convertDate($request->current_start_date)));
    //                         // } 
    //                         // if($request->current_end_date!=''){
    //                         //      $current_end_date=date("Y-m-d ", strtotime(convertDate($request->current_end_date)));
    //                         // } 
    //                         // if($request->comp_start_date !=''){
    //                         //     $comp_start_date=date("Y-m-d", strtotime(convertDate($request->comp_start_date)));
    //                         // } 
    //                         // if($request->comp_end_date !=''){
    //                         //      $comp_end_date=date("Y-m-d ", strtotime(convertDate($request->comp_end_date)));
    //                         // }
    //                         $data = array();
    //                         $a=$this->getCategoryTree($parent_id = $member->id, $tree_array = array());
    //                          $b[]=array('member_id'=>$member->id,'status'=>1,'is_block'=>0); 
    //                          $tree_array1 = array();
    //                          $c=array_merge($a, $b);                         
    //                          foreach ($a as $v) {
    //                             if($v['status']==1 && $v['is_block']==0)
    //                             {
    //                               $tree_array1[] =  $v['member_id'];
    //                             }                           
    //                          } 
    //                         $member_data = Member::with('associate_branch')->whereIn('id',$tree_array1);
    //                         if(isset($request->branch_id))
    //                         {
    //                             $branch_id =$request->branch_id;
    //                              $member_data = $member_data->where('associate_branch_id',$request->branch_id);
    //                         } 
    //                         else{
    //                             $branch_id ='';
    //                         }  
    //                         $member_data1=$member_data->orderby('associate_join_date','DESC')->get();
    //                         $count=count($member_data1);
    //                         if($request->page==1)
    //                         {
    //                             $start=0;
    //                         }
    //                         else
    //                         {
    //                            $start=($request->page-1)*$request->length;
    //                         }
    //                         $member_data=$member_data->orderby('associate_join_date','DESC')->offset($start)->limit($request->length)->get();
    //                         foreach ($member_data as $key => $row) {
    //                             // $startDate='';$endDate='';$branch_id='';
    //                             $associate_id=$row->id;
    //                              $planDaily=getPlanID('710')->id;
    //                             $dailyId=array($planDaily);
    //                             $planSSB=getPlanID('703')->id;
    //                             $planKanyadhan=getPlanID('709')->id;
    //                             $planMB=getPlanID('708')->id;
    //                             $planFRD=getPlanID('707')->id;
    //                             $planJeevan=getPlanID('713')->id;  
    //                             $planRD=getPlanID('704')->id;
    //                             $planBhavhishya=getPlanID('718')->id;
    //                             $monthlyId=array($planKanyadhan,$planMB,$planFRD,$planJeevan,$planRD,$planBhavhishya);
    //                             $planMI=getPlanID('712')->id;
    //                             $planFFD=getPlanID('705')->id;
    //                             $planFD=getPlanID('706')->id;
    //                             $fdId=array($planMI,$planFFD,$planFD);
    //                             $data[$key]['join_date']=date("d/m/Y", strtotime($row->associate_join_date));
    //                             $data[$key]['branch']=$row['associate_branch']->name;
    //                             $data[$key]['branch_code']=$row['associate_branch']->branch_code;
    //                             $data[$key]['sector_name']=$row['associate_branch']->sector;
    //                             $data[$key]['region_name']=$row['associate_branch']->regan;
    //                             $data[$key]['zone_name']=$row['associate_branch']->zone;
    //                             $data[$key]['member_id']=$row->associate_no;
    //                             $data[$key]['name']=$row->first_name.' '.$row->last_name;
    //                             $data[$key]['cadre']=getCarderName($row->current_carder_id);
    //                             $data[$key]['current_daily_new_ac']=investNewAcCount($associate_id,$current_start_date,$current_end_date,$planDaily,$branch_id);
    //                             $data[$key]['current_daily_deno_sum']=investNewDenoSum($associate_id,$current_start_date,$current_end_date,$planDaily,$branch_id);
    //                             $data[$key]['current_daily_renew_ac']=investRenewAc($associate_id,$current_start_date,$current_end_date,$dailyId,$branch_id);
    //                             $data[$key]['current_daily_renew']=investRenewAmountSum($associate_id,$current_start_date,$current_end_date,$dailyId,$branch_id);
    //                             $data[$key]['current_monthly_deno_sum']=investNewDenoSumType($associate_id,$current_start_date,$current_end_date,$monthlyId,$branch_id);
    //                             $data[$key]['current_monthly_renew_ac']=investRenewAc($associate_id,$current_start_date,$current_end_date,$monthlyId,$branch_id);
    //                             $data[$key]['current_monthly_renew']=investRenewAmountSum($associate_id,$current_start_date,$current_end_date,$monthlyId,$branch_id);
    //                             $data[$key]['current_fd_deno_sum']=investNewDenoSumType($associate_id,$current_start_date,$current_end_date,$fdId,$branch_id);
    //                             $data[$key]['current_ssb_deno_sum']=totalInvestSSbAmtByType($associate_id,$current_start_date,$current_end_date,$branch_id,1);
    //                             $data[$key]['current_ssb_renew_ac']=totalInvestSSbAcCountByType($associate_id,$current_start_date,$current_end_date,$branch_id,2);
    //                             $data[$key]['current_ssb_renew']=totalInvestSSbAmtByType($associate_id,$current_start_date,$current_end_date,$branch_id,2);
    //                             $data[$key]['current_daily_new_ac']=investNewAcCount($associate_id,$current_start_date,$current_end_date,$planDaily,$branch_id);
    //                             $data[$key]['current_monthly_new_ac']=investNewAcCountType($associate_id,$current_start_date,$current_end_date,$monthlyId,$branch_id);
    //                             $data[$key]['current_fd_new_ac']=investNewAcCountType($associate_id,$current_start_date,$current_end_date,$fdId,$branch_id);
    //                             $data[$key]['current_ssb_new_ac']=totalInvestSSbAcCountByType($associate_id,$current_start_date,$current_end_date,$branch_id,1);
    //                             $current_sum_ni_ac=$data[$key]['current_daily_new_ac']+$data[$key]['current_monthly_new_ac']+$data[$key]['current_fd_new_ac']+$data[$key]['current_ssb_new_ac'];  
    //                             $current_sum_ni_amount=$data[$key]['current_daily_deno_sum']+$data[$key]['current_monthly_deno_sum']+$data[$key]['current_fd_deno_sum']+$data[$key]['current_ssb_deno_sum'];
    //                             $data[$key]['current_total_ni_ac']=$current_sum_ni_ac; 
    //                             $data[$key]['current_total_ni_amount']=number_format((float)$current_sum_ni_amount, 2, '.', '');
    //                             $current_sum_renew_ac=$data[$key]['current_daily_renew_ac']+$data[$key]['current_monthly_renew_ac'];  
    //                             $current_sum_renew_amount=$data[$key]['current_daily_renew']+$data[$key]['current_monthly_renew']; 
    //                             $data[$key]['current_total_ac']=$current_sum_renew_ac;
    //                             $data[$key]['current_total_amount']=number_format((float)$current_sum_renew_amount, 2, '.', '');
    //                             $data[$key]['current_other_mt']=investOtherMiByType($associate_id,$current_start_date,$current_end_date,$branch_id,1,11);
    //                             $data[$key]['current_other_stn']=investOtherMiByType($associate_id,$current_start_date,$current_end_date,$branch_id,1,12);              
    //                             // $data[$key]['current_total_ac']='';
    //                             // $data[$key]['current_total_amount']='';
    //                             // $data[$key]['current_other_mt']='';
    //                             // $data[$key]['current_other_stn']='';
    //                             // $data[$key]['current_total_ni_amount']='';
    //                             // $data[$key]['current_total_ni_ac']='';
    //                             $current_ni_m=$data[$key]['current_daily_deno_sum']+$data[$key]['current_monthly_deno_sum']+$data[$key]['current_fd_deno_sum'];              
    //                             $current_tcc_m=$data[$key]['current_daily_deno_sum']+$data[$key]['current_monthly_deno_sum']+$data[$key]['current_fd_deno_sum']+$data[$key]['current_daily_renew']+$data[$key]['current_monthly_renew'];               
    //                             $current_tcc=$data[$key]['current_daily_deno_sum']+$data[$key]['current_monthly_deno_sum']+$data[$key]['current_fd_deno_sum']+$data[$key]['current_ssb_deno_sum']+$data[$key]['current_daily_renew']+$data[$key]['current_monthly_renew']+$data[$key]['current_ssb_renew'];
    //                             $data[$key]['current_ni_m']=number_format((float)$current_ni_m, 2, '.', '');
    //                             $data[$key]['current_ni']=number_format((float)$current_sum_ni_amount, 2, '.', '');
    //                             $data[$key]['current_tcc_m']=number_format((float)$current_tcc_m, 2, '.', '');
    //                             $data[$key]['current_tcc']=number_format((float)$current_tcc, 2, '.', '');
    //                             $data[$key]['current_loan_ac']=totalLoanAc($associate_id,$current_start_date,$current_end_date,$branch_id);
    //                             $data[$key]['current_loan_amount']=totalLoanAmount($associate_id,$current_start_date,$current_end_date,$branch_id);
    //                             $data[$key]['current_loan_recovery_ac']=totalRenewLoanAc($associate_id,$current_start_date,$current_end_date,$branch_id);
    //                             $data[$key]['current_loan_recovery_amount']=totalRenewLoanAmount($associate_id,$current_start_date,$current_end_date,$branch_id); 
    //                             $data[$key]['current_new_associate']=memberCountByType($associate_id,$current_start_date,$current_end_date,$branch_id,1,0);
    //                             $data[$key]['current_total_associate']=memberCountByType($associate_id,$current_start_date,$current_end_date,$branch_id,1,1);
    //                             $data[$key]['current_new_member']=memberCountByType($associate_id,$current_start_date,$current_end_date,$branch_id,0,0);
    //                             $data[$key]['current_total_member']=memberCountByType($associate_id,$current_start_date,$current_end_date,$branch_id,0,1);                
    //                             // $data[$key]['current_new_member']='';
    //                             // $data[$key]['current_total_member']='';
    //                             // $data[$key]['current_new_associate']='';
    //                             // $data[$key]['current_loan_recovery_ac']='';
    //                             // $data[$key]['current_loan_ac']='';
    //                             $data[$key]['compare_daily_new_ac']=investNewAcCount($associate_id,$comp_start_date,$comp_end_date,$planDaily,$branch_id);
    //                             $data[$key]['compare_daily_deno_sum']=investNewDenoSum($associate_id,$comp_start_date,$comp_end_date,$planDaily,$branch_id);
    //                              $data[$key]['compare_daily_renew_ac']=investRenewAc($associate_id,$comp_start_date,$comp_end_date,$dailyId,$branch_id);
    //                             $data[$key]['compare_daily_renew']=investRenewAmountSum($associate_id,$comp_start_date,$comp_end_date,$dailyId,$branch_id);
    //                             $data[$key]['compare_monthly_new_ac']=investNewAcCountType($associate_id,$comp_start_date,$comp_end_date,$monthlyId,$branch_id);
    //                             $data[$key]['compare_monthly_deno_sum']=investNewDenoSumType($associate_id,$comp_start_date,$comp_end_date,$monthlyId,$branch_id);
    //                             $data[$key]['compare_monthly_renew_ac']=investRenewAc($associate_id,$comp_start_date,$comp_end_date,$monthlyId,$branch_id);
    //                             $data[$key]['compare_monthly_renew']=investRenewAmountSum($associate_id,$comp_start_date,$comp_end_date,$monthlyId,$branch_id);
    //                             $data[$key]['compare_fd_new_ac']=investNewAcCountType($associate_id,$comp_start_date,$comp_end_date,$fdId,$branch_id);
    //                             $data[$key]['compare_fd_deno_sum']=investNewDenoSumType($associate_id,$comp_start_date,$comp_end_date,$fdId,$branch_id);
    //                              $data[$key]['compare_ssb_new_ac']=totalInvestSSbAcCountByType($associate_id,$comp_start_date,$comp_end_date,$branch_id,1);
    //                             $data[$key]['compare_ssb_deno_sum']=totalInvestSSbAmtByType($associate_id,$comp_start_date,$comp_start_date,$branch_id,1);
    //                             $data[$key]['compare_ssb_renew_ac']=totalInvestSSbAcCountByType($associate_id,$comp_start_date,$comp_end_date,$branch_id,2);
    //                             $data[$key]['compare_ssb_renew']=totalInvestSSbAmtByType($associate_id,$comp_start_date,$comp_end_date,$branch_id,2);
    //                             $compare_sum_ni_ac=$data[$key]['compare_daily_new_ac']+$data[$key]['compare_monthly_new_ac']+$data[$key]['compare_fd_new_ac']+$data[$key]['compare_ssb_new_ac']; 
    //                             $compare_sum_ni_amount=$data[$key]['compare_daily_deno_sum']+$data[$key]['compare_monthly_deno_sum']+$data[$key]['compare_fd_deno_sum']+$data[$key]['compare_ssb_deno_sum'];
    //                             $data[$key]['compare_total_ni_ac']=$compare_sum_ni_ac; 
    //                             $data[$key]['compare_total_ni_amount']=number_format((float)$compare_sum_ni_amount, 2, '.', '');
    //                             $compare_sum_renew_ac=$data[$key]['compare_daily_renew_ac']+$data[$key]['compare_monthly_renew_ac'];  
    //                             $compare_sum_renew_amount=$data[$key]['compare_daily_renew']+$data[$key]['compare_monthly_renew']; 
    //                             $data[$key]['compare_total_ni_ac']='';
    //                             $data[$key]['compare_total_ni_amount']='';
    //                             $data[$key]['compare_total_ac']=$compare_sum_renew_ac;
    //                             $data[$key]['compare_total_amount']=number_format((float)$compare_sum_renew_amount, 2, '.', '');
    //                             $data[$key]['compare_other_mt']=investOtherMiByType($associate_id,$comp_start_date,$comp_end_date,$branch_id,1,11);
    //                             $data[$key]['compare_other_stn']=investOtherMiByType($associate_id,$comp_start_date,$comp_end_date,$branch_id,1,12);
    //                             // $data[$key]['compare_total_ac']='';
    //                             // $data[$key]['compare_total_amount']='';
    //                             // $data[$key]['compare_other_mt']='';
    //                             // $data[$key]['compare_other_stn']='';
    //                             $compare_ni_m=$data[$key]['compare_daily_deno_sum']+$data[$key]['compare_monthly_deno_sum']+$data[$key]['compare_fd_deno_sum'];              
    //                             $compare_tcc_m=$data[$key]['compare_daily_deno_sum']+$data[$key]['compare_monthly_deno_sum']+$data[$key]['compare_fd_deno_sum']+$data[$key]['compare_daily_renew']+$data[$key]['compare_monthly_renew'];            
    //                             $compare_tcc=$data[$key]['compare_daily_deno_sum']+$data[$key]['compare_monthly_deno_sum']+$data[$key]['compare_fd_deno_sum']+$data[$key]['compare_ssb_deno_sum']+$data[$key]['compare_daily_renew']+$data[$key]['compare_monthly_renew']+$data[$key]['compare_ssb_renew'];
    //                             $data[$key]['compare_ni_m']=number_format((float)$compare_ni_m, 2, '.', '');
    //                             $data[$key]['compare_ni']=number_format((float)$compare_sum_ni_amount, 2, '.', '');
    //                             $data[$key]['compare_tcc_m']=number_format((float)$compare_tcc_m, 2, '.', '');
    //                             $data[$key]['compare_tcc']=number_format((float)$compare_tcc, 2, '.', '');
    //                             $data[$key]['compare_loan_ac']=totalLoanAc($associate_id,$comp_start_date,$comp_end_date,$branch_id);
    //                             $data[$key]['compare_loan_amount']=totalLoanAmount($associate_id,$comp_start_date,$comp_end_date,$branch_id);
    //                             $data[$key]['compare_loan_recovery_ac']=totalRenewLoanAc($associate_id,$comp_start_date,$comp_end_date,$branch_id);
    //                             $data[$key]['compare_loan_recovery_amount']=totalRenewLoanAmount($associate_id,$comp_start_date,$comp_end_date,$branch_id);            
    //                             // $data[$key]['compare_loan_recovery_ac']='';
    //                             // $data[$key]['compare_loan_ac']='';
    //                             $data[$key]['compare_new_associate']=memberCountByType($associate_id,$comp_start_date,$comp_end_date,$branch_id,1,0);
    //                             // $data[$key]['compare_new_associate']='';
    //                             $data[$key]['compare_total_associate']=memberCountByType($associate_id,$comp_start_date,$comp_end_date,$branch_id,1,1);
    //                             $data[$key]['compare_new_member']=memberCountByType($associate_id,$comp_start_date,$comp_end_date,$branch_id,0,0);
    //                             $data[$key]['compare_total_member']=memberCountByType($associate_id,$comp_start_date,$comp_end_date,$branch_id,0,1);
    //                             // $data[$key]['compare_new_member']='';
    //                             $data[$key]['result_daily_new_ac']=$data[$key]['current_daily_new_ac']-$data[$key]['compare_daily_new_ac'];
    //                            $data[$key]['result_daily_deno_sum']=$data[$key]['current_daily_deno_sum']-$data[$key]['compare_daily_deno_sum'];
    //                             $data[$key]['result_daily_renew_ac']=$data[$key]['current_daily_new_ac']-$data[$key]['compare_daily_new_ac'];
    //                             $data[$key]['result_daily_renew']=$data[$key]['current_daily_renew']-$data[$key]['compare_daily_renew'];
    //                             $data[$key]['result_monthly_new_ac']=$data[$key]['current_daily_new_ac']-$data[$key]['compare_daily_new_ac'];
    //                             $data[$key]['result_monthly_deno_sum']=$data[$key]['current_monthly_deno_sum']-$data[$key]['compare_monthly_deno_sum'];
    //                             $data[$key]['result_monthly_renew_ac']=$data[$key]['current_monthly_renew_ac']-$data[$key]['compare_monthly_renew_ac'];
    //                             $data[$key]['result_monthly_renew']=$data[$key]['current_monthly_renew']-$data[$key]['compare_monthly_renew'];
    //                             $data[$key]['result_fd_new_ac']=$data[$key]['current_fd_new_ac']-$data[$key]['compare_fd_new_ac'];
    //                             $data[$key]['result_fd_deno_sum']=$data[$key]['current_fd_deno_sum']-$data[$key]['compare_fd_deno_sum'];
    //                             $data[$key]['result_ssb_new_ac']=$data[$key]['current_ssb_new_ac']-$data[$key]['compare_ssb_new_ac'];
    //                             $data[$key]['result_ssb_deno_sum']=$data[$key]['current_ssb_deno_sum']-$data[$key]['compare_ssb_deno_sum'];
    //                             $data[$key]['result_ssb_renew_ac']=$data[$key]['current_ssb_renew_ac']-$data[$key]['compare_ssb_renew'];
    //                             $data[$key]['result_ssb_renew']=$data[$key]['current_ssb_renew']-$data[$key]['compare_ssb_deno_sum'];
    //                             $result_sum_ni_ac=$current_sum_ni_ac-$compare_sum_ni_ac;   
    //                             $result_sum_ni_amount=$current_sum_ni_amount-$compare_sum_ni_amount;
    //                             $data[$key]['result_total_ni_ac']=$result_sum_ni_ac; 
    //                             $data[$key]['result_total_ni_amount']=number_format((float)$result_sum_ni_amount, 2, '.', '');
    //                             $result_sum_renew_ac=$current_sum_renew_ac-$compare_sum_renew_ac;   
    //                             $result_sum_renew_amount=$current_sum_renew_amount-$compare_sum_renew_amount; 
    //                             // $data[$key]['result_total_ni_amount']='';
    //                             // $data[$key]['result_total_ni_ac']='';
    //                             // $data[$key]['result_ssb_new_ac']='';
    //                             // $data[$key]['result_fd_new_ac']='';
    //                             // $data[$key]['result_monthly_new_ac']='';
    //                             // $data[$key]['result_daily_renew_ac']='';
    //                             // $data[$key]['result_daily_new_ac']='';
    //                             $data[$key]['result_total_ac']=$result_sum_renew_ac;
    //                             $data[$key]['result_total_amount']=number_format((float)$result_sum_renew_amount, 2, '.', '');
    //                             $data[$key]['result_other_mt']=$data[$key]['current_other_mt']-$data[$key]['compare_other_mt'];
    //                             $data[$key]['result_other_stn']=$data[$key]['current_other_stn']-$data[$key]['compare_other_stn'];
    //                             // $data[$key]['result_other_stn']='';
    //                             // $data[$key]['result_other_mt']='';
    //                             // $data[$key]['result_total_amount']='';
    //                             // $data[$key]['result_total_ac']='';
    //                             $data[$key]['result_ni_m']=$data[$key]['current_ni_m']-$data[$key]['compare_ni_m'];
    //                             $data[$key]['result_ni']=$data[$key]['current_ni']-$data[$key]['compare_ni'];
    //                             $data[$key]['result_tcc_m']=$data[$key]['current_tcc_m']-$data[$key]['compare_tcc_m'];
    //                             $data[$key]['result_tcc']=$data[$key]['current_tcc']-$data[$key]['compare_tcc'];
    //                             $data[$key]['result_loan_ac']=$data[$key]['current_loan_ac']-$data[$key]['compare_loan_ac'];
    //                             $data[$key]['result_loan_amount']=$data[$key]['current_loan_amount']-$data[$key]['compare_loan_amount'];
    //                             $data[$key]['result_loan_recovery_ac']=$data[$key]['current_loan_recovery_ac']-$data[$key]['compare_loan_recovery_ac'];
    //                             $data[$key]['result_loan_recovery_amount']=$data[$key]['current_loan_recovery_amount']-$data[$key]['compare_loan_recovery_amount'];
    //                             $data[$key]['result_new_associate']=$data[$key]['current_new_associate']-$data[$key]['compare_new_associate'];
    //                             $data[$key]['result_total_associate']=$data[$key]['current_total_associate']-$data[$key]['compare_total_associate'];
    //                             $data[$key]['result_new_member']=$data[$key]['current_new_member']-$data[$key]['compare_new_member'];
    //                             $data[$key]['result_total_member']=$data[$key]['current_total_member']-$data[$key]['compare_total_member']; 
    //                             $data[$key]['result_loan_ac']='';
    //                             $data[$key]['result_loan_recovery_ac']='';
    //                             $data[$key]['result_new_associate']='';
    //                             $data[$key]['result_new_member']='';
    //                             $data[$key]['result_total_member']='';
    //                         }
    //                         $status   = "Success";
    //                         $code     = 200;
    //                         $page  = $request->page;
    //                         $length  = $request->length;
    //                         $messages = 'Associate Business Summary  Report !'; 
    //                         $result   = ['business_summary_report' => $data,'total_count'=>$count,'page'=>$page,'length'=>$length,'record_count'=>count($data)];
    //                         $associate_status=$member->associate_app_status;
    //                         return response()->json(compact('status', 'code', 'messages', 'result','associate_status'), $code); 
    //                     }
    //                     elseif($request->page == 0)
    //                     {
    //                         $data = array();
    //                         $a=$this->getCategoryTree($parent_id = $member->id, $tree_array = array());
    //                          $b[]=array('member_id'=>$member->id,'status'=>1,'is_block'=>0); 
    //                          $tree_array1 = array();
    //                          $c=array_merge($a, $b);                         
    //                          foreach ($a as $v) {
    //                             if($v['status']==1 && $v['is_block']==0)
    //                             {
    //                               $tree_array1[] =  $v['member_id'];
    //                             }                           
    //                          } 
    //                         $member_data = Member::with('associate_branch')->whereIn('id',$tree_array1);
    //                         if(isset($request->branch_id))
    //                         {
    //                             $branch_id =$request->branch_id;
    //                              $member_data = $member_data->where('associate_branch_id',$request->branch_id);
    //                         } 
    //                         else{
    //                             $branch_id =$member->branch_id;
    //                         }  
    //                         $member_data1=$member_data->orderby('associate_join_date','DESC')->get();
    //                         $count=count($member_data1);
    //                         if($request->page==1)
    //                         {
    //                             $start=0;
    //                         }
    //                         else
    //                         {
    //                            $start=($request->page-1)*$request->length;
    //                         }
    //                         $member_data=$member_data->orderby('associate_join_date','DESC')->get();
    //                         foreach ($member_data as $key => $row) {
    //                             $startDate='';$endDate='';$branch_id='';
    //                             $associate_id=$row->id;
    //                             $planDaily=getPlanID('710')->id;
    //                             $dailyId=array($planDaily);
    //                             $planSSB=getPlanID('703')->id;
    //                             $planKanyadhan=getPlanID('709')->id;
    //                             $planMB=getPlanID('709')->id;
    //                             $planFRD=getPlanID('707')->id;
    //                             $planJeevan=getPlanID('713')->id; 
    //                             $planRD=getPlanID('704')->id;
    //                             $planBhavhishya=getPlanID('718')->id;
    //                             $monthlyId=array($planKanyadhan,$planMB,$planFRD,$planJeevan,$planRD,$planBhavhishya);
    //                             $planMI=getPlanID('712')->id;
    //                             $planFFD=getPlanID('705')->id;
    //                             $planFD=getPlanID('706')->id;
    //                             $fdId=array($planMI,$planFFD,$planFD);
    //                             $data[$key]['join_date']=date("d/m/Y", strtotime($row->associate_join_date));
    //                             $data[$key]['branch']=$row['associate_branch']->name;
    //                             $data[$key]['branch_code']=$row['associate_branch']->branch_code;
    //                             $data[$key]['sector_name']=$row['associate_branch']->sector;
    //                             $data[$key]['region_name']=$row['associate_branch']->regan;
    //                             $data[$key]['zone_name']=$row['associate_branch']->zone;
    //                             $data[$key]['member_id']=$row->associate_no;
    //                             $data[$key]['name']=$row->first_name.' '.$row->last_name;
    //                             $data[$key]['cadre']=getCarderName($row->current_carder_id);
    //                             $data[$key]['current_daily_new_ac']=investNewAcCount($associate_id,$current_start_date,$current_end_date,$planDaily,$branch_id);
    //                             $data[$key]['current_daily_deno_sum']=investNewDenoSum($associate_id,$current_start_date,$current_end_date,$planDaily,$branch_id);
    //                             $data[$key]['current_daily_renew_ac']=investRenewAc($associate_id,$current_start_date,$current_end_date,$dailyId,$branch_id);
    //                             $data[$key]['current_daily_renew']=investRenewAmountSum($associate_id,$current_start_date,$current_end_date,$dailyId,$branch_id);
    //                             $data[$key]['current_monthly_new_ac']=investNewAcCountType($associate_id,$current_start_date,$current_end_date,$monthlyId,$branch_id);
    //                             $data[$key]['current_monthly_deno_sum']=investNewDenoSumType($associate_id,$current_start_date,$current_end_date,$monthlyId,$branch_id);
    //                             $data[$key]['current_monthly_renew_ac']=investRenewAc($associate_id,$current_start_date,$current_end_date,$monthlyId,$branch_id);
    //                             $data[$key]['current_monthly_renew']=investRenewAmountSum($associate_id,$current_start_date,$current_end_date,$monthlyId,$branch_id);
    //                             $data[$key]['current_fd_new_ac']=investNewAcCountType($associate_id,$current_start_date,$current_end_date,$fdId,$branch_id);
    //                             $data[$key]['current_fd_deno_sum']=investNewDenoSumType($associate_id,$current_start_date,$current_end_date,$fdId,$branch_id);
    //                             $data[$key]['current_ssb_new_ac']=totalInvestSSbAcCountByType($associate_id,$current_start_date,$current_end_date,$branch_id,1);
    //                             $data[$key]['current_ssb_deno_sum']=totalInvestSSbAmtByType($associate_id,$current_start_date,$current_end_date,$branch_id,1);
    //                             $data[$key]['current_ssb_renew_ac']=totalInvestSSbAcCountByType($associate_id,$current_start_date,$current_end_date,$branch_id,2);
    //                             $data[$key]['current_ssb_renew']=totalInvestSSbAmtByType($associate_id,$current_start_date,$current_end_date,$branch_id,2);
    //                             $current_sum_ni_ac=$data[$key]['current_daily_new_ac']+$data[$key]['current_monthly_new_ac']+$data[$key]['current_fd_new_ac']+$data[$key]['current_ssb_new_ac'];  
    //                             $current_sum_ni_amount=$data[$key]['current_daily_deno_sum']+$data[$key]['current_monthly_deno_sum']+$data[$key]['current_fd_deno_sum']+$data[$key]['current_ssb_deno_sum'];
    //                             $data[$key]['current_total_ni_ac']=$current_sum_ni_ac; 
    //                             $data[$key]['current_total_ni_amount']=number_format((float)$current_sum_ni_amount, 2, '.', '');
    //                             $current_sum_renew_ac=$data[$key]['current_daily_renew_ac']+$data[$key]['current_monthly_renew_ac'];  
    //                             $current_sum_renew_amount=$data[$key]['current_daily_renew']+$data[$key]['current_monthly_renew']; 
    //                             $data[$key]['current_total_ac']=$current_sum_renew_ac;
    //                             $data[$key]['current_total_amount']=number_format((float)$current_sum_renew_amount, 2, '.', '');
    //                             $data[$key]['current_other_mt']=investOtherMiByType($associate_id,$current_start_date,$current_end_date,$branch_id,1,11);
    //                             $data[$key]['current_other_stn']=investOtherMiByType($associate_id,$current_start_date,$current_end_date,$branch_id,1,12);              
    //                             $current_ni_m=$data[$key]['current_daily_deno_sum']+$data[$key]['current_monthly_deno_sum']+$data[$key]['current_fd_deno_sum'];              
    //                             $current_tcc_m=$data[$key]['current_daily_deno_sum']+$data[$key]['current_monthly_deno_sum']+$data[$key]['current_fd_deno_sum']+$data[$key]['current_daily_renew']+$data[$key]['current_monthly_renew'];               
    //                             $current_tcc=$data[$key]['current_daily_deno_sum']+$data[$key]['current_monthly_deno_sum']+$data[$key]['current_fd_deno_sum']+$data[$key]['current_ssb_deno_sum']+$data[$key]['current_daily_renew']+$data[$key]['current_monthly_renew']+$data[$key]['current_ssb_renew'];
    //                             $data[$key]['current_ni_m']=number_format((float)$current_ni_m, 2, '.', '');
    //                             $data[$key]['current_ni']=number_format((float)$current_sum_ni_amount, 2, '.', '');
    //                             $data[$key]['current_tcc_m']=number_format((float)$current_tcc_m, 2, '.', '');
    //                             $data[$key]['current_tcc']=number_format((float)$current_tcc, 2, '.', '');
    //                             $data[$key]['current_loan_ac']=totalLoanAc($associate_id,$current_start_date,$current_end_date,$branch_id);
    //                             $data[$key]['current_loan_amount']=totalLoanAmount($associate_id,$current_start_date,$current_end_date,$branch_id);
    //                             $data[$key]['current_loan_recovery_ac']=totalRenewLoanAc($associate_id,$current_start_date,$current_end_date,$branch_id);
    //                             $data[$key]['current_loan_recovery_amount']=totalRenewLoanAmount($associate_id,$current_start_date,$current_end_date,$branch_id); 
    //                             $data[$key]['current_new_associate']=memberCountByType($associate_id,$current_start_date,$current_end_date,$branch_id,1,0);
    //                             $data[$key]['current_total_associate']=memberCountByType($associate_id,$current_start_date,$current_end_date,$branch_id,1,1);
    //                             $data[$key]['current_new_member']=memberCountByType($associate_id,$current_start_date,$current_end_date,$branch_id,0,0);
    //                             $data[$key]['current_total_member']=memberCountByType($associate_id,$current_start_date,$current_end_date,$branch_id,0,1);                
    //                             $data[$key]['compare_daily_new_ac']=investNewAcCount($associate_id,$comp_start_date,$comp_end_date,$planDaily,$branch_id);
    //                             $data[$key]['compare_daily_deno_sum']=investNewDenoSum($associate_id,$comp_start_date,$comp_end_date,$planDaily,$branch_id);
    //                             $data[$key]['compare_daily_renew_ac']=investRenewAc($associate_id,$comp_start_date,$comp_end_date,$dailyId,$branch_id);
    //                             $data[$key]['compare_daily_renew']=investRenewAmountSum($associate_id,$comp_start_date,$comp_end_date,$dailyId,$branch_id);
    //                             $data[$key]['compare_monthly_new_ac']=investNewAcCountType($associate_id,$comp_start_date,$comp_end_date,$monthlyId,$branch_id);
    //                             $data[$key]['compare_monthly_deno_sum']=investNewDenoSumType($associate_id,$comp_start_date,$comp_end_date,$monthlyId,$branch_id);
    //                             $data[$key]['compare_monthly_renew_ac']=investRenewAc($associate_id,$comp_start_date,$comp_end_date,$monthlyId,$branch_id);
    //                             $data[$key]['compare_monthly_renew']=investRenewAmountSum($associate_id,$comp_start_date,$comp_end_date,$monthlyId,$branch_id);
    //                             $data[$key]['compare_fd_new_ac']=investNewAcCountType($associate_id,$comp_start_date,$comp_end_date,$fdId,$branch_id);
    //                             $data[$key]['compare_fd_deno_sum']=investNewDenoSumType($associate_id,$comp_start_date,$comp_end_date,$fdId,$branch_id);
    //                             $data[$key]['compare_ssb_new_ac']=totalInvestSSbAcCountByType($associate_id,$comp_start_date,$comp_end_date,$branch_id,1);
    //                             $data[$key]['compare_ssb_deno_sum']=totalInvestSSbAmtByType($associate_id,$comp_start_date,$comp_start_date,$branch_id,1);
    //                             $data[$key]['compare_ssb_renew_ac']=totalInvestSSbAcCountByType($associate_id,$comp_start_date,$comp_end_date,$branch_id,2);
    //                             $data[$key]['compare_ssb_renew']=totalInvestSSbAmtByType($associate_id,$comp_start_date,$comp_end_date,$branch_id,2);
    //                             $compare_sum_ni_ac=$data[$key]['compare_daily_new_ac']+$data[$key]['compare_monthly_new_ac']+$data[$key]['compare_fd_new_ac']+$data[$key]['compare_ssb_new_ac']; 
    //                             $compare_sum_ni_amount=$data[$key]['compare_daily_deno_sum']+$data[$key]['compare_monthly_deno_sum']+$data[$key]['compare_fd_deno_sum']+$data[$key]['compare_ssb_deno_sum'];
    //                             $data[$key]['compare_total_ni_ac']=$compare_sum_ni_ac; 
    //                             $data[$key]['compare_total_ni_amount']=number_format((float)$compare_sum_ni_amount, 2, '.', '');
    //                             $compare_sum_renew_ac=$data[$key]['compare_daily_renew_ac']+$data[$key]['compare_monthly_renew_ac'];  
    //                             $compare_sum_renew_amount=$data[$key]['compare_daily_renew']+$data[$key]['compare_monthly_renew']; 
    //                             $data[$key]['compare_total_ac']=$compare_sum_renew_ac;
    //                             $data[$key]['compare_total_amount']=number_format((float)$compare_sum_renew_amount, 2, '.', '');
    //                             $data[$key]['compare_other_mt']=investOtherMiByType($associate_id,$comp_start_date,$comp_end_date,$branch_id,1,11);
    //                             $data[$key]['compare_other_stn']=investOtherMiByType($associate_id,$comp_start_date,$comp_end_date,$branch_id,1,12);
    //                             $compare_ni_m=$data[$key]['compare_daily_deno_sum']+$data[$key]['compare_monthly_deno_sum']+$data[$key]['compare_fd_deno_sum'];              
    //                             $compare_tcc_m=$data[$key]['compare_daily_deno_sum']+$data[$key]['compare_monthly_deno_sum']+$data[$key]['compare_fd_deno_sum']+$data[$key]['compare_daily_renew']+$data[$key]['compare_monthly_renew'];            
    //                             $compare_tcc=$data[$key]['compare_daily_deno_sum']+$data[$key]['compare_monthly_deno_sum']+$data[$key]['compare_fd_deno_sum']+$data[$key]['compare_ssb_deno_sum']+$data[$key]['compare_daily_renew']+$data[$key]['compare_monthly_renew']+$data[$key]['compare_ssb_renew'];
    //                             $data[$key]['compare_ni_m']=number_format((float)$compare_ni_m, 2, '.', '');
    //                             $data[$key]['compare_ni']=number_format((float)$compare_sum_ni_amount, 2, '.', '');
    //                             $data[$key]['compare_tcc_m']=number_format((float)$compare_tcc_m, 2, '.', '');
    //                             $data[$key]['compare_tcc']=number_format((float)$compare_tcc, 2, '.', '');
    //                             $data[$key]['compare_loan_ac']=totalLoanAc($associate_id,$comp_start_date,$comp_end_date,$branch_id);
    //                             $data[$key]['compare_loan_amount']=totalLoanAmount($associate_id,$comp_start_date,$comp_end_date,$branch_id);
    //                             $data[$key]['compare_loan_recovery_ac']=totalRenewLoanAc($associate_id,$comp_start_date,$comp_end_date,$branch_id);
    //                             $data[$key]['compare_loan_recovery_amount']=totalRenewLoanAmount($associate_id,$comp_start_date,$comp_end_date,$branch_id);            
    //                             $data[$key]['compare_new_associate']=memberCountByType($associate_id,$comp_start_date,$comp_end_date,$branch_id,1,0);
    //                             $data[$key]['compare_total_associate']=memberCountByType($associate_id,$comp_start_date,$comp_end_date,$branch_id,1,1);
    //                             $data[$key]['compare_new_member']=memberCountByType($associate_id,$comp_start_date,$comp_end_date,$branch_id,0,0);
    //                             $data[$key]['compare_total_member']=memberCountByType($associate_id,$comp_start_date,$comp_end_date,$branch_id,0,1);
    //                             $data[$key]['result_daily_new_ac']=$data[$key]['current_daily_new_ac']-$data[$key]['compare_daily_new_ac'];
    //                             $data[$key]['result_daily_deno_sum']=$data[$key]['current_daily_new_ac']-$data[$key]['compare_daily_new_ac'];
    //                             $data[$key]['result_daily_renew_ac']=$data[$key]['current_daily_new_ac']-$data[$key]['compare_daily_new_ac'];
    //                             $data[$key]['result_daily_renew']=$data[$key]['current_daily_new_ac']-$data[$key]['compare_daily_new_ac'];
    //                             $data[$key]['result_monthly_new_ac']=$data[$key]['current_daily_new_ac']-$data[$key]['compare_daily_new_ac'];
    //                             $data[$key]['result_monthly_deno_sum']=$data[$key]['current_monthly_deno_sum']-$data[$key]['compare_monthly_deno_sum'];
    //                             $data[$key]['result_monthly_renew_ac']=$data[$key]['current_monthly_renew_ac']-$data[$key]['compare_monthly_renew_ac'];
    //                             $data[$key]['result_monthly_renew']=$data[$key]['current_monthly_renew']-$data[$key]['compare_monthly_renew'];
    //                             $data[$key]['result_fd_new_ac']=$data[$key]['current_fd_new_ac']-$data[$key]['compare_fd_new_ac'];
    //                             $data[$key]['result_fd_deno_sum']=$data[$key]['current_fd_deno_sum']-$data[$key]['compare_fd_deno_sum'];
    //                             $data[$key]['result_ssb_new_ac']=$data[$key]['current_ssb_new_ac']-$data[$key]['compare_ssb_new_ac'];
    //                             $data[$key]['result_ssb_deno_sum']=$data[$key]['current_ssb_deno_sum']-$data[$key]['compare_ssb_deno_sum'];
    //                             $data[$key]['result_ssb_renew_ac']=$data[$key]['current_ssb_renew_ac']-$data[$key]['compare_ssb_renew'];
    //                             $data[$key]['result_ssb_renew']=$data[$key]['current_ssb_renew']-$data[$key]['compare_ssb_deno_sum'];
    //                             $result_sum_ni_ac=$current_sum_ni_ac-$compare_sum_ni_ac;   
    //                             $result_sum_ni_amount=$current_sum_ni_amount-$compare_sum_ni_amount;
    //                             $data[$key]['result_total_ni_ac']=$result_sum_ni_ac; 
    //                             $data[$key]['result_total_ni_amount']=number_format((float)$result_sum_ni_amount, 2, '.', '');
    //                             $result_sum_renew_ac=$current_sum_renew_ac-$compare_sum_renew_ac;   
    //                             $result_sum_renew_amount=$current_sum_renew_amount-$compare_sum_renew_amount; 
    //                             $data[$key]['result_total_ac']=$result_sum_renew_ac;
    //                             $data[$key]['result_total_amount']=number_format((float)$result_sum_renew_amount, 2, '.', '');
    //                             $data[$key]['result_other_mt']=$data[$key]['current_other_mt']-$data[$key]['compare_other_mt'];
    //                             $data[$key]['result_other_stn']=$data[$key]['current_other_stn']-$data[$key]['compare_other_stn'];
    //                             $data[$key]['result_ni_m']=$data[$key]['current_ni_m']-$data[$key]['compare_ni_m'];
    //                             $data[$key]['result_ni']=$data[$key]['current_ni']-$data[$key]['compare_ni'];
    //                             $data[$key]['result_tcc_m']=$data[$key]['current_tcc_m']-$data[$key]['compare_tcc_m'];
    //                             $data[$key]['result_tcc']=$data[$key]['current_tcc']-$data[$key]['compare_tcc'];
    //                             $data[$key]['result_loan_ac']=$data[$key]['current_loan_ac']-$data[$key]['compare_loan_ac'];
    //                             $data[$key]['result_loan_amount']=$data[$key]['current_loan_amount']-$data[$key]['compare_loan_amount'];
    //                             $data[$key]['result_loan_recovery_ac']=$data[$key]['current_loan_recovery_ac']-$data[$key]['compare_loan_recovery_ac'];
    //                             $data[$key]['result_loan_recovery_amount']=$data[$key]['current_loan_recovery_amount']-$data[$key]['compare_loan_recovery_amount'];
    //                             $data[$key]['result_new_associate']=$data[$key]['current_new_associate']-$data[$key]['compare_new_associate'];
    //                             $data[$key]['result_total_associate']=$data[$key]['current_total_associate']-$data[$key]['compare_total_associate'];
    //                             $data[$key]['result_new_member']=$data[$key]['current_new_member']-$data[$key]['compare_new_member'];
    //                             $data[$key]['result_total_member']=$data[$key]['current_total_member']-$data[$key]['compare_total_member']; 
    //                         }
    //                         $status   = "Success";
    //                         $code     = 200;
    //                         $messages = 'Associate Business Summary  Report !'; 
    //                         $result   = ['business_summary_report' => $data,'total_count'=>$count,'record_count'=>count($data)];
    //                         $associate_status=$member->associate_app_status;
    //                         return response()->json(compact('status', 'code', 'messages', 'result','associate_status'), $code);
    //                     }
    //                     else
    //                     {
    //                         $status = "Error";
    //                         $code = 201;
    //                         $messages = 'Page no or length must be grater than 0!';
    //                         $result = '';
    //                         $associate_status=$member->associate_app_status;
    //                         return response()->json(compact('status', 'code', 'messages', 'result','associate_status'), $code);
    //                     }
    //                     }  
    //                     // else
    //                     // {
    //                     //     $status = "Error";
    //                     //     $code = 201;
    //                     //     $messages = 'Please select all date. ';
    //                     //     $result = '';
    //                     //     $associate_status=$member->associate_app_status;
    //                     //     return response()->json(compact('status', 'code', 'messages', 'result','associate_status'), $code);
    //                     // }                 
    //                 }else{
    //                     $status = "Error";
    //                     $code = 201;
    //                     $messages = 'API token mismatch!';
    //                     $result = '';
    //                     $associate_status=$member->associate_app_status;
    //                     return response()->json(compact('status', 'code', 'messages', 'result','associate_status'), $code);
    //                 }
    //             }else{
    //                 $status = "Error";
    //                 $code = 201;
    //                 $messages = 'Something went wrong!';
    //                 $result = '';
    //                 $associate_status=9;
    //                 return response()->json(compact('status', 'code', 'messages', 'result','associate_status'), $code);
    //             } 
    //         } catch (Exception $e) {
    //             $status = "Error";
    //             $code = 500;
    //             $messages = $e->getMessage();
    //             $result = '';
    //             $associate_status=9;
    //             return response()->json(compact('status', 'code', 'messages', 'result','associate_status'), $code);
    //         }
    //     } 
    public function associate_business_compare_report(Request $request)
    {
        $associate_no = $request->associate_no;
        try {
            $member = Member::select('id', 'associate_app_status')->where('associate_no', $request->associate_no)->where('associate_status', 1)->where('is_block', 0)->first();
            if ($member) {
                $token = md5($request->associate_no);
                if ($token == $request->token) {
                    if ($request->current_start_date != '' && $request->current_start_date != 0) {
                        $current_start_date = date("Y-m-d", strtotime(convertDate($request->current_start_date)));
                    } elseif ($request->current_start_date == '0') {
                        $current_start_date = date('d/m/Y', strtotime('first day of -1 months'));
                    }
                    if ($request->current_end_date != '' && $request->current_end_date != 0) {
                        $current_end_date = date("Y-m-d ", strtotime(convertDate($request->current_end_date)));
                    } elseif ($request->current_end_date == 0) {
                        $current_end_date = date('d/m/Y', strtotime('last day of -1 months'));
                    }
                    if ($request->comp_start_date != ''  && $request->comp_start_date != 0) {
                        $comp_start_date = date("Y-m-d", strtotime(convertDate($request->comp_start_date)));
                    } elseif ($request->comp_start_date == 0) {
                        $comp_start_date = date('d/m/Y', strtotime('first day of -2 months'));
                    }
                    if ($request->comp_end_date != ''  && $request->comp_end_date != 0) {
                        $comp_end_date = date("Y-m-d ", strtotime(convertDate($request->comp_end_date)));
                    } else if ($request->comp_end_date == 0) {
                        $comp_end_date = date('d/m/Y', strtotime('last day of -2 months'));
                    }
                    if ($current_start_date != '' && $current_end_date != '' && $comp_start_date != '' && $comp_end_date != '') {
                        if ($request->page > 0) {
                            // if($request->current_start_date !=''){
                            //     $current_start_date=date("Y-m-d", strtotime(convertDate($request->current_start_date)));
                            // } 
                            // if($request->current_end_date!=''){
                            //      $current_end_date=date("Y-m-d ", strtotime(convertDate($request->current_end_date)));
                            // } 
                            // if($request->comp_start_date !=''){
                            //     $comp_start_date=date("Y-m-d", strtotime(convertDate($request->comp_start_date)));
                            // } 
                            // if($request->comp_end_date !=''){
                            //      $comp_end_date=date("Y-m-d ", strtotime(convertDate($request->comp_end_date)));
                            // }
                            $data = array();
                            $a = $this->getCategoryTree($parent_id = $member->id, $tree_array = array());
                            $b[] = array('member_id' => $member->id, 'status' => 1, 'is_block' => 0);
                            $tree_array1 = array();
                            $c = array_merge($a, $b);
                            foreach ($c as $v) {
                                if ($v['status'] == 1 && $v['is_block'] == 0) {
                                    $tree_array1[] =  $v['member_id'];
                                }
                            }
                            $member_data = Member::with('associate_branch')->whereIn('id', $tree_array1);
                            if (isset($request->branch_id)) {
                                $branch_id = $request->branch_id;
                                $member_data = $member_data->where('associate_branch_id', $request->branch_id);
                            } else {
                                $branch_id = '';
                            }
                            $member_data1 = $member_data->orderby('associate_join_date', 'DESC')->get();
                            $count = count($member_data1);
                            if ($request->page == 1) {
                                $start = 0;
                            } else {
                                if (isset($request->length)) {
                                    $start = ($request->page - 1) * $request->length;
                                }
                            }
                            if (isset($request->length)) {
                                $member_data = $member_data->orderby('associate_join_date', 'DESC')->offset($start)->limit($request->length)->get();
                            } else {
                                $member_data = $member_data->orderby('associate_join_date', 'DESC')->get();
                            }
                            foreach ($member_data as $key => $row) {
                                // $startDate='';$endDate='';$branch_id='';
                                $associate_id = $row->id;
                                $planDaily = getPlanID('710')->id;
                                $dailyId = array($planDaily);
                                $planSSB = getPlanID('703')->id;
                                $planKanyadhan = getPlanID('709')->id;
                                $planMB = getPlanID('708')->id;
                                $planFRD = getPlanID('707')->id;
                                $planJeevan = getPlanID('713')->id;
                                $planRD = getPlanID('704')->id;
                                $planBhavhishya = getPlanID('718')->id;
                                $monthlyId = array($planKanyadhan, $planMB, $planFRD, $planJeevan, $planRD, $planBhavhishya);
                                $planMI = getPlanID('712')->id;
                                $planFFD = getPlanID('705')->id;
                                $planFD = getPlanID('706')->id;
                                $fdId = array($planMI, $planFFD, $planFD);
                                $data[$key]['join_date'] = date("d/m/Y", strtotime($row->associate_join_date));
                                $data[$key]['branch'] = $row['associate_branch']->name;
                                $data[$key]['branch_code'] = $row['associate_branch']->branch_code;
                                $data[$key]['sector_name'] = $row['associate_branch']->sector;
                                $data[$key]['region_name'] = $row['associate_branch']->regan;
                                $data[$key]['zone_name'] = $row['associate_branch']->zone;
                                $data[$key]['member_id'] = $row->associate_no;
                                $data[$key]['name'] = $row->first_name . ' ' . $row->last_name;
                                $data[$key]['cadre'] = getCarderName($row->current_carder_id);
                                $data[$key]['current_daily_new_ac'] = investNewAcCount($associate_id, $current_start_date, $current_end_date, $planDaily, $branch_id);
                                $data[$key]['current_daily_deno_sum'] = investNewDenoSum($associate_id, $current_start_date, $current_end_date, $planDaily, $branch_id);
                                $data[$key]['current_daily_renew_ac'] = investRenewAc($associate_id, $current_start_date, $current_end_date, $dailyId, $branch_id);
                                $data[$key]['current_daily_renew'] = investRenewAmountSum($associate_id, $current_start_date, $current_end_date, $dailyId, $branch_id);
                                $data[$key]['current_monthly_new_ac'] = investNewAcCountType($associate_id, $current_start_date, $current_end_date, $monthlyId, $branch_id);
                                $data[$key]['current_monthly_deno_sum'] = investNewDenoSumType($associate_id, $current_start_date, $current_end_date, $monthlyId, $branch_id);
                                $data[$key]['current_monthly_renew_ac'] = investRenewAc($associate_id, $current_start_date, $current_end_date, $monthlyId, $branch_id);
                                $data[$key]['current_monthly_renew'] = investRenewAmountSum($associate_id, $current_start_date, $current_end_date, $monthlyId, $branch_id);
                                $data[$key]['current_fd_new_ac'] = investNewAcCountType($associate_id, $current_start_date, $current_end_date, $fdId, $branch_id);
                                $data[$key]['current_fd_deno_sum'] = investNewDenoSumType($associate_id, $current_start_date, $current_end_date, $fdId, $branch_id);
                                $data[$key]['current_ssb_new_ac'] = totalInvestSSbAcCountByType($associate_id, $current_start_date, $current_end_date, $branch_id, 1);
                                $data[$key]['current_ssb_deno_sum'] = totalInvestSSbAmtByType($associate_id, $current_start_date, $current_end_date, $branch_id, 1);
                                $data[$key]['current_ssb_renew_ac'] = totalInvestSSbAcCountByType($associate_id, $current_start_date, $current_end_date, $branch_id, 2);
                                $data[$key]['current_ssb_renew'] = totalInvestSSbAmtByType($associate_id, $current_start_date, $current_end_date, $branch_id, 2);
                                $current_sum_ni_ac = $data[$key]['current_daily_new_ac'] + $data[$key]['current_monthly_new_ac'] + $data[$key]['current_fd_new_ac'] + $data[$key]['current_ssb_new_ac'];
                                $current_sum_ni_amount = $data[$key]['current_daily_deno_sum'] + $data[$key]['current_monthly_deno_sum'] + $data[$key]['current_fd_deno_sum'] + $data[$key]['current_ssb_deno_sum'];
                                $data[$key]['current_total_ni_ac'] = $current_sum_ni_ac;
                                $data[$key]['current_total_ni_amount'] = number_format((float)$current_sum_ni_amount, 2, '.', '');
                                $current_sum_renew_ac = $data[$key]['current_daily_renew_ac'] + $data[$key]['current_monthly_renew_ac'];
                                $current_sum_renew_amount = $data[$key]['current_daily_renew'] + $data[$key]['current_monthly_renew'];
                                $data[$key]['current_total_ac'] = $current_sum_renew_ac;
                                $data[$key]['current_total_amount'] = number_format((float)$current_sum_renew_amount, 2, '.', '');
                                $data[$key]['current_other_mt'] = investOtherMiByType($associate_id, $current_start_date, $current_end_date, $branch_id, 1, 11);
                                $data[$key]['current_other_stn'] = investOtherMiByType($associate_id, $current_start_date, $current_end_date, $branch_id, 1, 12);
                                $current_ni_m = $data[$key]['current_daily_deno_sum'] + $data[$key]['current_monthly_deno_sum'] + $data[$key]['current_fd_deno_sum'];
                                $current_tcc_m = $data[$key]['current_daily_deno_sum'] + $data[$key]['current_monthly_deno_sum'] + $data[$key]['current_fd_deno_sum'] + $data[$key]['current_daily_renew'] + $data[$key]['current_monthly_renew'];
                                $current_tcc = $data[$key]['current_daily_deno_sum'] + $data[$key]['current_monthly_deno_sum'] + $data[$key]['current_fd_deno_sum'] + $data[$key]['current_ssb_deno_sum'] + $data[$key]['current_daily_renew'] + $data[$key]['current_monthly_renew'] + $data[$key]['current_ssb_renew'];
                                $data[$key]['current_ni_m'] = number_format((float)$current_ni_m, 2, '.', '');
                                $data[$key]['current_ni'] = number_format((float)$current_sum_ni_amount, 2, '.', '');
                                $data[$key]['current_tcc_m'] = number_format((float)$current_tcc_m, 2, '.', '');
                                $data[$key]['current_tcc'] = number_format((float)$current_tcc, 2, '.', '');
                                $data[$key]['current_loan_ac'] = totalLoanAc($associate_id, $current_start_date, $current_end_date, $branch_id);
                                $data[$key]['current_loan_amount'] = totalLoanAmount($associate_id, $current_start_date, $current_end_date, $branch_id);
                                $data[$key]['current_loan_recovery_ac'] = totalRenewLoanAc($associate_id, $current_start_date, $current_end_date, $branch_id);
                                $data[$key]['current_loan_recovery_amount'] = totalRenewLoanAmount($associate_id, $current_start_date, $current_end_date, $branch_id);
                                $data[$key]['current_new_associate'] = memberCountByType($associate_id, $current_start_date, $current_end_date, $branch_id, 1, 0);
                                $data[$key]['current_total_associate'] = memberCountByType($associate_id, $current_start_date, $current_end_date, $branch_id, 1, 0);
                                $data[$key]['current_new_member'] = memberCountByType($associate_id, $current_start_date, $current_end_date, $branch_id, 0, 0);
                                $data[$key]['current_total_member'] = memberCountByType($associate_id, $current_start_date, $current_end_date, $branch_id, 0, 1);
                                $data[$key]['compare_daily_new_ac'] = investNewAcCount($associate_id, $comp_start_date, $comp_end_date, $planDaily, $branch_id);
                                $data[$key]['compare_daily_deno_sum'] = investNewDenoSum($associate_id, $comp_start_date, $comp_end_date, $planDaily, $branch_id);
                                $data[$key]['compare_daily_renew_ac'] = investRenewAc($associate_id, $comp_start_date, $comp_end_date, $dailyId, $branch_id);
                                $data[$key]['compare_daily_renew'] = investRenewAmountSum($associate_id, $comp_start_date, $comp_end_date, $dailyId, $branch_id);
                                $data[$key]['compare_monthly_new_ac'] = investNewAcCountType($associate_id, $comp_start_date, $comp_end_date, $monthlyId, $branch_id);
                                $data[$key]['compare_monthly_deno_sum'] = investNewDenoSumType($associate_id, $comp_start_date, $comp_end_date, $monthlyId, $branch_id);
                                $data[$key]['compare_monthly_renew_ac'] = investRenewAc($associate_id, $comp_start_date, $comp_end_date, $monthlyId, $branch_id);
                                $data[$key]['compare_monthly_renew'] = investRenewAmountSum($associate_id, $comp_start_date, $comp_end_date, $monthlyId, $branch_id);
                                $data[$key]['compare_fd_new_ac'] = investNewAcCountType($associate_id, $comp_start_date, $comp_end_date, $fdId, $branch_id);
                                $data[$key]['compare_fd_deno_sum'] = investNewDenoSumType($associate_id, $comp_start_date, $comp_end_date, $fdId, $branch_id);
                                $data[$key]['compare_ssb_new_ac'] = totalInvestSSbAcCountByType($associate_id, $comp_start_date, $comp_end_date, $branch_id, 1);
                                $data[$key]['compare_ssb_deno_sum'] = totalInvestSSbAmtByType($associate_id, $comp_start_date, $comp_start_date, $branch_id, 1);
                                $data[$key]['compare_ssb_renew_ac'] = totalInvestSSbAcCountByType($associate_id, $comp_start_date, $comp_end_date, $branch_id, 2);
                                $data[$key]['compare_ssb_renew'] = totalInvestSSbAmtByType($associate_id, $comp_start_date, $comp_end_date, $branch_id, 2);
                                $compare_sum_ni_ac = $data[$key]['compare_daily_new_ac'] + $data[$key]['compare_monthly_new_ac'] + $data[$key]['compare_fd_new_ac'] + $data[$key]['compare_ssb_new_ac'];
                                $compare_sum_ni_amount = $data[$key]['compare_daily_deno_sum'] + $data[$key]['compare_monthly_deno_sum'] + $data[$key]['compare_fd_deno_sum'] + $data[$key]['compare_ssb_deno_sum'];
                                $data[$key]['compare_total_ni_ac'] = $compare_sum_ni_ac;
                                $data[$key]['compare_total_ni_amount'] = number_format((float)$compare_sum_ni_amount, 2, '.', '');
                                $compare_sum_renew_ac = $data[$key]['compare_daily_renew_ac'] + $data[$key]['compare_monthly_renew_ac'];
                                $compare_sum_renew_amount = $data[$key]['compare_daily_renew'] + $data[$key]['compare_monthly_renew'];
                                $data[$key]['compare_total_ac'] = $compare_sum_renew_ac;
                                $data[$key]['compare_total_amount'] = number_format((float)$compare_sum_renew_amount, 2, '.', '');
                                $data[$key]['compare_other_mt'] = investOtherMiByType($associate_id, $comp_start_date, $comp_end_date, $branch_id, 1, 11);
                                $data[$key]['compare_other_stn'] = investOtherMiByType($associate_id, $comp_start_date, $comp_end_date, $branch_id, 1, 12);
                                $compare_ni_m = $data[$key]['compare_daily_deno_sum'] + $data[$key]['compare_monthly_deno_sum'] + $data[$key]['compare_fd_deno_sum'];
                                $compare_tcc_m = $data[$key]['compare_daily_deno_sum'] + $data[$key]['compare_monthly_deno_sum'] + $data[$key]['compare_fd_deno_sum'] + $data[$key]['compare_daily_renew'] + $data[$key]['compare_monthly_renew'];
                                $compare_tcc = $data[$key]['compare_daily_deno_sum'] + $data[$key]['compare_monthly_deno_sum'] + $data[$key]['compare_fd_deno_sum'] + $data[$key]['compare_ssb_deno_sum'] + $data[$key]['compare_daily_renew'] + $data[$key]['compare_monthly_renew'] + $data[$key]['compare_ssb_renew'];
                                $data[$key]['compare_ni_m'] = number_format((float)$compare_ni_m, 2, '.', '');
                                $data[$key]['compare_ni'] = number_format((float)$compare_sum_ni_amount, 2, '.', '');
                                $data[$key]['compare_tcc_m'] = number_format((float)$compare_tcc_m, 2, '.', '');
                                $data[$key]['compare_tcc'] = number_format((float)$compare_tcc, 2, '.', '');
                                $data[$key]['compare_loan_ac'] = totalLoanAc($associate_id, $comp_start_date, $comp_end_date, $branch_id);
                                $data[$key]['compare_loan_amount'] = totalLoanAmount($associate_id, $comp_start_date, $comp_end_date, $branch_id);
                                $data[$key]['compare_loan_recovery_ac'] = totalRenewLoanAc($associate_id, $comp_start_date, $comp_end_date, $branch_id);
                                $data[$key]['compare_loan_recovery_amount'] = totalRenewLoanAmount($associate_id, $comp_start_date, $comp_end_date, $branch_id);
                                $data[$key]['compare_new_associate'] = memberCountByType($associate_id, $comp_start_date, $comp_end_date, $branch_id, 1, 0);
                                $data[$key]['compare_total_associate'] = memberCountByType($associate_id, $comp_start_date, $comp_end_date, $branch_id, 1, 0);
                                $data[$key]['compare_new_member'] = memberCountByType($associate_id, $comp_start_date, $comp_end_date, $branch_id, 0, 0);
                                $data[$key]['compare_total_member'] = memberCountByType($associate_id, $comp_start_date, $comp_end_date, $branch_id, 0, 1);
                                $data[$key]['result_daily_new_ac'] = $data[$key]['current_daily_new_ac'] - $data[$key]['compare_daily_new_ac'];
                                $data[$key]['result_daily_deno_sum'] = $data[$key]['current_daily_deno_sum'] - $data[$key]['compare_daily_deno_sum'];
                                $data[$key]['result_daily_renew_ac'] = $data[$key]['current_daily_new_ac'] - $data[$key]['compare_daily_new_ac'];
                                $data[$key]['result_daily_renew'] = $data[$key]['current_daily_renew'] - $data[$key]['compare_daily_renew'];
                                $data[$key]['result_monthly_new_ac'] = $data[$key]['current_daily_new_ac'] - $data[$key]['compare_daily_new_ac'];
                                $data[$key]['result_monthly_deno_sum'] = $data[$key]['current_monthly_deno_sum'] - $data[$key]['compare_monthly_deno_sum'];
                                $data[$key]['result_monthly_renew_ac'] = $data[$key]['current_monthly_renew_ac'] - $data[$key]['compare_monthly_renew_ac'];
                                $data[$key]['result_monthly_renew'] = $data[$key]['current_monthly_renew'] - $data[$key]['compare_monthly_renew'];
                                $data[$key]['result_fd_new_ac'] = $data[$key]['current_fd_new_ac'] - $data[$key]['compare_fd_new_ac'];
                                $data[$key]['result_fd_deno_sum'] = $data[$key]['current_fd_deno_sum'] - $data[$key]['compare_fd_deno_sum'];
                                $data[$key]['result_ssb_new_ac'] = $data[$key]['current_ssb_new_ac'] - $data[$key]['compare_ssb_new_ac'];
                                $data[$key]['result_ssb_deno_sum'] = $data[$key]['current_ssb_deno_sum'] - $data[$key]['compare_ssb_deno_sum'];
                                $data[$key]['result_ssb_renew_ac'] = $data[$key]['current_ssb_renew_ac'] - $data[$key]['compare_ssb_renew'];
                                $data[$key]['result_ssb_renew'] = $data[$key]['current_ssb_renew'] - $data[$key]['compare_ssb_deno_sum'];
                                $result_sum_ni_ac = $current_sum_ni_ac - $compare_sum_ni_ac;
                                $result_sum_ni_amount = $current_sum_ni_amount - $compare_sum_ni_amount;
                                $data[$key]['result_total_ni_ac'] = $result_sum_ni_ac;
                                $data[$key]['result_total_ni_amount'] = number_format((float)$result_sum_ni_amount, 2, '.', '');
                                $result_sum_renew_ac = $current_sum_renew_ac - $compare_sum_renew_ac;
                                $result_sum_renew_amount = $current_sum_renew_amount - $compare_sum_renew_amount;
                                $data[$key]['result_total_ac'] = $result_sum_renew_ac;
                                $data[$key]['result_total_amount'] = number_format((float)$result_sum_renew_amount, 2, '.', '');
                                $data[$key]['result_other_mt'] = $data[$key]['current_other_mt'] - $data[$key]['compare_other_mt'];
                                $data[$key]['result_other_stn'] = $data[$key]['current_other_stn'] - $data[$key]['compare_other_stn'];
                                $data[$key]['result_ni_m'] = $data[$key]['current_ni_m'] - $data[$key]['compare_ni_m'];
                                $data[$key]['result_ni'] = $data[$key]['current_ni'] - $data[$key]['compare_ni'];
                                $data[$key]['result_tcc_m'] = $data[$key]['current_tcc_m'] - $data[$key]['compare_tcc_m'];
                                $data[$key]['result_tcc'] = $data[$key]['current_tcc'] - $data[$key]['compare_tcc'];
                                $data[$key]['result_loan_ac'] = $data[$key]['current_loan_ac'] - $data[$key]['compare_loan_ac'];
                                $data[$key]['result_loan_amount'] = $data[$key]['current_loan_amount'] - $data[$key]['compare_loan_amount'];
                                $data[$key]['result_loan_recovery_ac'] = $data[$key]['current_loan_recovery_ac'] - $data[$key]['compare_loan_recovery_ac'];
                                $data[$key]['result_loan_recovery_amount'] = $data[$key]['current_loan_recovery_amount'] - $data[$key]['compare_loan_recovery_amount'];
                                $data[$key]['result_new_associate'] = $data[$key]['current_new_associate'] - $data[$key]['compare_new_associate'];
                                $data[$key]['result_total_associate'] = $data[$key]['current_total_associate'] - $data[$key]['compare_total_associate'];
                                $data[$key]['result_new_member'] = $data[$key]['current_new_member'] - $data[$key]['compare_new_member'];
                                $data[$key]['result_total_member'] = $data[$key]['current_total_member'] - $data[$key]['compare_total_member'];
                            }
                            $status   = "Success";
                            $code     = 200;
                            $page  = $request->page;
                            $length  = $request->length;
                            $messages = 'Associate Business Summary  Report !';
                            $result   = ['business_summary_report' => $data, 'total_count' => $count, 'page' => $page, 'length' => $length, 'record_count' => count($data)];
                            $associate_status = $member->associate_app_status;
                            return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                        } elseif ($request->page == 0) {
                            $data = array();
                            $a = $this->getCategoryTree($parent_id = $member->id, $tree_array = array());
                            $b[] = array('member_id' => $member->id, 'status' => 1, 'is_block' => 0);
                            $tree_array1 = array();
                            $c = array_merge($a, $b);
                            foreach ($a as $v) {
                                if ($v['status'] == 1 && $v['is_block'] == 0) {
                                    $tree_array1[] =  $v['member_id'];
                                }
                            }
                            $member_data = Member::with('associate_branch')->whereIn('id', $tree_array1);
                            if (isset($request->branch_id)) {
                                $branch_id = $request->branch_id;
                                $member_data = $member_data->where('associate_branch_id', $request->branch_id);
                            } else {
                                $branch_id = $member->branch_id;
                            }
                            $member_data1 = $member_data->orderby('associate_join_date', 'DESC')->get();
                            $count = count($member_data1);
                            if ($request->page == 1) {
                                $start = 0;
                            } else {
                                $start = ($request->page - 1) * $request->length;
                            }
                            $member_data = $member_data->orderby('associate_join_date', 'DESC')->get();
                            foreach ($member_data as $key => $row) {
                                $startDate = '';
                                $endDate = '';
                                $branch_id = '';
                                $associate_id = $row->id;
                                $planDaily = getPlanID('710')->id;
                                $dailyId = array($planDaily);
                                $planSSB = getPlanID('703')->id;
                                $planKanyadhan = getPlanID('709')->id;
                                $planMB = getPlanID('709')->id;
                                $planFRD = getPlanID('707')->id;
                                $planJeevan = getPlanID('713')->id;
                                $planRD = getPlanID('704')->id;
                                $planBhavhishya = getPlanID('718')->id;
                                $monthlyId = array($planKanyadhan, $planMB, $planFRD, $planJeevan, $planRD, $planBhavhishya);
                                $planMI = getPlanID('712')->id;
                                $planFFD = getPlanID('705')->id;
                                $planFD = getPlanID('706')->id;
                                $fdId = array($planMI, $planFFD, $planFD);
                                $data[$key]['join_date'] = date("d/m/Y", strtotime($row->associate_join_date));
                                $data[$key]['branch'] = $row['associate_branch']->name;
                                $data[$key]['branch_code'] = $row['associate_branch']->branch_code;
                                $data[$key]['sector_name'] = $row['associate_branch']->sector;
                                $data[$key]['region_name'] = $row['associate_branch']->regan;
                                $data[$key]['zone_name'] = $row['associate_branch']->zone;
                                $data[$key]['member_id'] = $row->associate_no;
                                $data[$key]['name'] = $row->first_name . ' ' . $row->last_name;
                                $data[$key]['cadre'] = getCarderName($row->current_carder_id);
                                $data[$key]['current_daily_new_ac'] = investNewAcCount($associate_id, $current_start_date, $current_end_date, $planDaily, $branch_id);
                                $data[$key]['current_daily_deno_sum'] = investNewDenoSum($associate_id, $current_start_date, $current_end_date, $planDaily, $branch_id);
                                $data[$key]['current_daily_renew_ac'] = investRenewAc($associate_id, $current_start_date, $current_end_date, $dailyId, $branch_id);
                                $data[$key]['current_daily_renew'] = investRenewAmountSum($associate_id, $current_start_date, $current_end_date, $dailyId, $branch_id);
                                $data[$key]['current_monthly_new_ac'] = investNewAcCountType($associate_id, $current_start_date, $current_end_date, $monthlyId, $branch_id);
                                $data[$key]['current_monthly_deno_sum'] = investNewDenoSumType($associate_id, $current_start_date, $current_end_date, $monthlyId, $branch_id);
                                $data[$key]['current_monthly_renew_ac'] = investRenewAc($associate_id, $current_start_date, $current_end_date, $monthlyId, $branch_id);
                                $data[$key]['current_monthly_renew'] = investRenewAmountSum($associate_id, $current_start_date, $current_end_date, $monthlyId, $branch_id);
                                $data[$key]['current_fd_new_ac'] = investNewAcCountType($associate_id, $current_start_date, $current_end_date, $fdId, $branch_id);
                                $data[$key]['current_fd_deno_sum'] = investNewDenoSumType($associate_id, $current_start_date, $current_end_date, $fdId, $branch_id);
                                $data[$key]['current_ssb_new_ac'] = totalInvestSSbAcCountByType($associate_id, $current_start_date, $current_end_date, $branch_id, 1);
                                $data[$key]['current_ssb_deno_sum'] = totalInvestSSbAmtByType($associate_id, $current_start_date, $current_end_date, $branch_id, 1);
                                $data[$key]['current_ssb_renew_ac'] = totalInvestSSbAcCountByType($associate_id, $current_start_date, $current_end_date, $branch_id, 2);
                                $data[$key]['current_ssb_renew'] = totalInvestSSbAmtByType($associate_id, $current_start_date, $current_end_date, $branch_id, 2);
                                $current_sum_ni_ac = $data[$key]['current_daily_new_ac'] + $data[$key]['current_monthly_new_ac'] + $data[$key]['current_fd_new_ac'] + $data[$key]['current_ssb_new_ac'];
                                $current_sum_ni_amount = $data[$key]['current_daily_deno_sum'] + $data[$key]['current_monthly_deno_sum'] + $data[$key]['current_fd_deno_sum'] + $data[$key]['current_ssb_deno_sum'];
                                $data[$key]['current_total_ni_ac'] = $current_sum_ni_ac;
                                $data[$key]['current_total_ni_amount'] = number_format((float)$current_sum_ni_amount, 2, '.', '');
                                $current_sum_renew_ac = $data[$key]['current_daily_renew_ac'] + $data[$key]['current_monthly_renew_ac'];
                                $current_sum_renew_amount = $data[$key]['current_daily_renew'] + $data[$key]['current_monthly_renew'];
                                $data[$key]['current_total_ac'] = $current_sum_renew_ac;
                                $data[$key]['current_total_amount'] = number_format((float)$current_sum_renew_amount, 2, '.', '');
                                $data[$key]['current_other_mt'] = investOtherMiByType($associate_id, $current_start_date, $current_end_date, $branch_id, 1, 11);
                                $data[$key]['current_other_stn'] = investOtherMiByType($associate_id, $current_start_date, $current_end_date, $branch_id, 1, 12);
                                $current_ni_m = $data[$key]['current_daily_deno_sum'] + $data[$key]['current_monthly_deno_sum'] + $data[$key]['current_fd_deno_sum'];
                                $current_tcc_m = $data[$key]['current_daily_deno_sum'] + $data[$key]['current_monthly_deno_sum'] + $data[$key]['current_fd_deno_sum'] + $data[$key]['current_daily_renew'] + $data[$key]['current_monthly_renew'];
                                $current_tcc = $data[$key]['current_daily_deno_sum'] + $data[$key]['current_monthly_deno_sum'] + $data[$key]['current_fd_deno_sum'] + $data[$key]['current_ssb_deno_sum'] + $data[$key]['current_daily_renew'] + $data[$key]['current_monthly_renew'] + $data[$key]['current_ssb_renew'];
                                $data[$key]['current_ni_m'] = number_format((float)$current_ni_m, 2, '.', '');
                                $data[$key]['current_ni'] = number_format((float)$current_sum_ni_amount, 2, '.', '');
                                $data[$key]['current_tcc_m'] = number_format((float)$current_tcc_m, 2, '.', '');
                                $data[$key]['current_tcc'] = number_format((float)$current_tcc, 2, '.', '');
                                $data[$key]['current_loan_ac'] = totalLoanAc($associate_id, $current_start_date, $current_end_date, $branch_id);
                                $data[$key]['current_loan_amount'] = totalLoanAmount($associate_id, $current_start_date, $current_end_date, $branch_id);
                                $data[$key]['current_loan_recovery_ac'] = totalRenewLoanAc($associate_id, $current_start_date, $current_end_date, $branch_id);
                                $data[$key]['current_loan_recovery_amount'] = totalRenewLoanAmount($associate_id, $current_start_date, $current_end_date, $branch_id);
                                $data[$key]['current_new_associate'] = memberCountByType($associate_id, $current_start_date, $current_end_date, $branch_id, 1, 0);
                                $data[$key]['current_total_associate'] = memberCountByType($associate_id, $current_start_date, $current_end_date, $branch_id, 1, 1);
                                $data[$key]['current_new_member'] = memberCountByType($associate_id, $current_start_date, $current_end_date, $branch_id, 0, 0);
                                $data[$key]['current_total_member'] = memberCountByType($associate_id, $current_start_date, $current_end_date, $branch_id, 0, 1);
                                $data[$key]['compare_daily_new_ac'] = investNewAcCount($associate_id, $comp_start_date, $comp_end_date, $planDaily, $branch_id);
                                $data[$key]['compare_daily_deno_sum'] = investNewDenoSum($associate_id, $comp_start_date, $comp_end_date, $planDaily, $branch_id);
                                $data[$key]['compare_daily_renew_ac'] = investRenewAc($associate_id, $comp_start_date, $comp_end_date, $dailyId, $branch_id);
                                $data[$key]['compare_daily_renew'] = investRenewAmountSum($associate_id, $comp_start_date, $comp_end_date, $dailyId, $branch_id);
                                $data[$key]['compare_monthly_new_ac'] = investNewAcCountType($associate_id, $comp_start_date, $comp_end_date, $monthlyId, $branch_id);
                                $data[$key]['compare_monthly_deno_sum'] = investNewDenoSumType($associate_id, $comp_start_date, $comp_end_date, $monthlyId, $branch_id);
                                $data[$key]['compare_monthly_renew_ac'] = investRenewAc($associate_id, $comp_start_date, $comp_end_date, $monthlyId, $branch_id);
                                $data[$key]['compare_monthly_renew'] = investRenewAmountSum($associate_id, $comp_start_date, $comp_end_date, $monthlyId, $branch_id);
                                $data[$key]['compare_fd_new_ac'] = investNewAcCountType($associate_id, $comp_start_date, $comp_end_date, $fdId, $branch_id);
                                $data[$key]['compare_fd_deno_sum'] = investNewDenoSumType($associate_id, $comp_start_date, $comp_end_date, $fdId, $branch_id);
                                $data[$key]['compare_ssb_new_ac'] = totalInvestSSbAcCountByType($associate_id, $comp_start_date, $comp_end_date, $branch_id, 1);
                                $data[$key]['compare_ssb_deno_sum'] = totalInvestSSbAmtByType($associate_id, $comp_start_date, $comp_start_date, $branch_id, 1);
                                $data[$key]['compare_ssb_renew_ac'] = totalInvestSSbAcCountByType($associate_id, $comp_start_date, $comp_end_date, $branch_id, 2);
                                $data[$key]['compare_ssb_renew'] = totalInvestSSbAmtByType($associate_id, $comp_start_date, $comp_end_date, $branch_id, 2);
                                $compare_sum_ni_ac = $data[$key]['compare_daily_new_ac'] + $data[$key]['compare_monthly_new_ac'] + $data[$key]['compare_fd_new_ac'] + $data[$key]['compare_ssb_new_ac'];
                                $compare_sum_ni_amount = $data[$key]['compare_daily_deno_sum'] + $data[$key]['compare_monthly_deno_sum'] + $data[$key]['compare_fd_deno_sum'] + $data[$key]['compare_ssb_deno_sum'];
                                $data[$key]['compare_total_ni_ac'] = $compare_sum_ni_ac;
                                $data[$key]['compare_total_ni_amount'] = number_format((float)$compare_sum_ni_amount, 2, '.', '');
                                $compare_sum_renew_ac = $data[$key]['compare_daily_renew_ac'] + $data[$key]['compare_monthly_renew_ac'];
                                $compare_sum_renew_amount = $data[$key]['compare_daily_renew'] + $data[$key]['compare_monthly_renew'];
                                $data[$key]['compare_total_ac'] = $compare_sum_renew_ac;
                                $data[$key]['compare_total_amount'] = number_format((float)$compare_sum_renew_amount, 2, '.', '');
                                $data[$key]['compare_other_mt'] = investOtherMiByType($associate_id, $comp_start_date, $comp_end_date, $branch_id, 1, 11);
                                $data[$key]['compare_other_stn'] = investOtherMiByType($associate_id, $comp_start_date, $comp_end_date, $branch_id, 1, 12);
                                $compare_ni_m = $data[$key]['compare_daily_deno_sum'] + $data[$key]['compare_monthly_deno_sum'] + $data[$key]['compare_fd_deno_sum'];
                                $compare_tcc_m = $data[$key]['compare_daily_deno_sum'] + $data[$key]['compare_monthly_deno_sum'] + $data[$key]['compare_fd_deno_sum'] + $data[$key]['compare_daily_renew'] + $data[$key]['compare_monthly_renew'];
                                $compare_tcc = $data[$key]['compare_daily_deno_sum'] + $data[$key]['compare_monthly_deno_sum'] + $data[$key]['compare_fd_deno_sum'] + $data[$key]['compare_ssb_deno_sum'] + $data[$key]['compare_daily_renew'] + $data[$key]['compare_monthly_renew'] + $data[$key]['compare_ssb_renew'];
                                $data[$key]['compare_ni_m'] = number_format((float)$compare_ni_m, 2, '.', '');
                                $data[$key]['compare_ni'] = number_format((float)$compare_sum_ni_amount, 2, '.', '');
                                $data[$key]['compare_tcc_m'] = number_format((float)$compare_tcc_m, 2, '.', '');
                                $data[$key]['compare_tcc'] = number_format((float)$compare_tcc, 2, '.', '');
                                $data[$key]['compare_loan_ac'] = totalLoanAc($associate_id, $comp_start_date, $comp_end_date, $branch_id);
                                $data[$key]['compare_loan_amount'] = totalLoanAmount($associate_id, $comp_start_date, $comp_end_date, $branch_id);
                                $data[$key]['compare_loan_recovery_ac'] = totalRenewLoanAc($associate_id, $comp_start_date, $comp_end_date, $branch_id);
                                $data[$key]['compare_loan_recovery_amount'] = totalRenewLoanAmount($associate_id, $comp_start_date, $comp_end_date, $branch_id);
                                $data[$key]['compare_new_associate'] = memberCountByType($associate_id, $comp_start_date, $comp_end_date, $branch_id, 1, 0);
                                $data[$key]['compare_total_associate'] = memberCountByType($associate_id, $comp_start_date, $comp_end_date, $branch_id, 1, 1);
                                $data[$key]['compare_new_member'] = memberCountByType($associate_id, $comp_start_date, $comp_end_date, $branch_id, 0, 0);
                                $data[$key]['compare_total_member'] = memberCountByType($associate_id, $comp_start_date, $comp_end_date, $branch_id, 0, 1);
                                $data[$key]['result_daily_new_ac'] = $data[$key]['current_daily_new_ac'] - $data[$key]['compare_daily_new_ac'];
                                $data[$key]['result_daily_deno_sum'] = $data[$key]['current_daily_new_ac'] - $data[$key]['compare_daily_new_ac'];
                                $data[$key]['result_daily_renew_ac'] = $data[$key]['current_daily_new_ac'] - $data[$key]['compare_daily_new_ac'];
                                $data[$key]['result_daily_renew'] = $data[$key]['current_daily_new_ac'] - $data[$key]['compare_daily_new_ac'];
                                $data[$key]['result_monthly_new_ac'] = $data[$key]['current_daily_new_ac'] - $data[$key]['compare_daily_new_ac'];
                                $data[$key]['result_monthly_deno_sum'] = $data[$key]['current_monthly_deno_sum'] - $data[$key]['compare_monthly_deno_sum'];
                                $data[$key]['result_monthly_renew_ac'] = $data[$key]['current_monthly_renew_ac'] - $data[$key]['compare_monthly_renew_ac'];
                                $data[$key]['result_monthly_renew'] = $data[$key]['current_monthly_renew'] - $data[$key]['compare_monthly_renew'];
                                $data[$key]['result_fd_new_ac'] = $data[$key]['current_fd_new_ac'] - $data[$key]['compare_fd_new_ac'];
                                $data[$key]['result_fd_deno_sum'] = $data[$key]['current_fd_deno_sum'] - $data[$key]['compare_fd_deno_sum'];
                                $data[$key]['result_ssb_new_ac'] = $data[$key]['current_ssb_new_ac'] - $data[$key]['compare_ssb_new_ac'];
                                $data[$key]['result_ssb_deno_sum'] = $data[$key]['current_ssb_deno_sum'] - $data[$key]['compare_ssb_deno_sum'];
                                $data[$key]['result_ssb_renew_ac'] = $data[$key]['current_ssb_renew_ac'] - $data[$key]['compare_ssb_renew'];
                                $data[$key]['result_ssb_renew'] = $data[$key]['current_ssb_renew'] - $data[$key]['compare_ssb_deno_sum'];
                                $result_sum_ni_ac = $current_sum_ni_ac - $compare_sum_ni_ac;
                                $result_sum_ni_amount = $current_sum_ni_amount - $compare_sum_ni_amount;
                                $data[$key]['result_total_ni_ac'] = $result_sum_ni_ac;
                                $data[$key]['result_total_ni_amount'] = number_format((float)$result_sum_ni_amount, 2, '.', '');
                                $result_sum_renew_ac = $current_sum_renew_ac - $compare_sum_renew_ac;
                                $result_sum_renew_amount = $current_sum_renew_amount - $compare_sum_renew_amount;
                                $data[$key]['result_total_ac'] = $result_sum_renew_ac;
                                $data[$key]['result_total_amount'] = number_format((float)$result_sum_renew_amount, 2, '.', '');
                                $data[$key]['result_other_mt'] = $data[$key]['current_other_mt'] - $data[$key]['compare_other_mt'];
                                $data[$key]['result_other_stn'] = $data[$key]['current_other_stn'] - $data[$key]['compare_other_stn'];
                                $data[$key]['result_ni_m'] = $data[$key]['current_ni_m'] - $data[$key]['compare_ni_m'];
                                $data[$key]['result_ni'] = $data[$key]['current_ni'] - $data[$key]['compare_ni'];
                                $data[$key]['result_tcc_m'] = $data[$key]['current_tcc_m'] - $data[$key]['compare_tcc_m'];
                                $data[$key]['result_tcc'] = $data[$key]['current_tcc'] - $data[$key]['compare_tcc'];
                                $data[$key]['result_loan_ac'] = $data[$key]['current_loan_ac'] - $data[$key]['compare_loan_ac'];
                                $data[$key]['result_loan_amount'] = $data[$key]['current_loan_amount'] - $data[$key]['compare_loan_amount'];
                                $data[$key]['result_loan_recovery_ac'] = $data[$key]['current_loan_recovery_ac'] - $data[$key]['compare_loan_recovery_ac'];
                                $data[$key]['result_loan_recovery_amount'] = $data[$key]['current_loan_recovery_amount'] - $data[$key]['compare_loan_recovery_amount'];
                                $data[$key]['result_new_associate'] = $data[$key]['current_new_associate'] - $data[$key]['compare_new_associate'];
                                $data[$key]['result_total_associate'] = $data[$key]['current_total_associate'] - $data[$key]['compare_total_associate'];
                                $data[$key]['result_new_member'] = $data[$key]['current_new_member'] - $data[$key]['compare_new_member'];
                                $data[$key]['result_total_member'] = $data[$key]['current_total_member'] - $data[$key]['compare_total_member'];
                            }
                            $status   = "Success";
                            $code     = 200;
                            $messages = 'Associate Business Summary  Report !';
                            $result   = ['business_summary_report' => $data, 'total_count' => $count, 'record_count' => count($data)];
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
                    }
                    // else
                    // {
                    //     $status = "Error";
                    //     $code = 201;
                    //     $messages = 'Please select all date. ';
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
        } catch (Exception $e) {
            $status = "Error";
            $code = 500;
            $messages = $e->getMessage();
            $result = '';
            $associate_status = 9;
            return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
        }
    }
    //Live
    /*
     public function associate_maturity_report(Request $request)
    {   
        $associate_no = $request->associate_no;  
        try { 
        $member = Member::select('id','associate_app_status')->where('associate_no',$request->associate_no)->where('associate_status',1)->where('is_block',0)->first();
            if ($member) {
                $token = md5($request->associate_no);
                if($token == $request->token){ 
                    $current_start_date='';
                        $current_end_date='';
                        $comp_start_date='';
                        $comp_end_date='';
                    if($request->page>0 && $request->length>0 )
                    { 
                        $data = array();
                        $a=$this->getCategoryTree($parent_id = $member->id,  $tree_array = array());
                         $b[]=array('member_id'=>$member->id,'status'=>1,'is_block'=>0);
                         $tree_array1 = array();
                         $c=array_merge($a, $b);
                         foreach ($c as $v) {                            
                              $tree_array1[] =  $v['member_id'];
                         }
                         $memberArray = Member::whereIn('associate_id',$tree_array1)->get('id');
                        $memberArray1=array();
                        foreach ($memberArray as $v) {
                              $memberArray1[] =  $v->id;                          
                         }
                             $invest_data = DemandAdvice::with('branch','investment')->where('is_mature',0);
                        $invest_data=$invest_data->whereHas('investment', function ($query) use ($memberArray1) { $query->whereIn('member_investments.member_id',$memberArray1);  });
                        if($request->type !=''){
                            $type=$request->type;
                            if($type == 0)
                            {
                                 $invest_data=$invest_data;
                            }
                            else{
                                if($type == 1)
                                {
                                 $status_type = 0;      
                                }
                                else{
                                     $status_type = 1;   
                                }
                                $invest_data=$invest_data->where('status',$status_type);
                            }
                        }
                        $invest_data1=$invest_data->orderby('id','DESC')->get();
                        $count=count($invest_data1);
                        if($request->page==1)
                        {
                            $start=0;
                        }
                        else
                        {
                            $start=($request->page-1)*$request->length;
                        }
                        $invest_data=$invest_data->orderby('id','DESC')->offset($start)->limit($request->length)->get();
                        foreach ($invest_data as $key => $row) 
                        {
                            if($row->investment_id){
                                $investmentAmount = Daybook::where('investment_id',$row->investment_id)->whereIn('transaction_type',[2,4])->sum('deposit');
                                $current_balance = $investmentAmount;
                            }else{
                                 $current_balance = 0;
                            }
                            $data[$key]['id']=$row->id;
                            $data[$key]['branch_code']=$row['branch']->branch_code;
                            $data[$key]['branch_name']=$row['branch']->name;
                            $data[$key]['account_no']=$row['investment']->account_number;
                            $data[$key]['member_name']=getSeniorData($row['investment']->member_id,'first_name').' '.getSeniorData($row['investment']->member_id,'last_name');
                            $data[$key]['member_id']=getSeniorData($row['investment']->member_id,'member_id');
                            $data[$key]['plan_name']=getPlanDetail($row['investment']->plan_id)->name; 
                            $data[$key]['deno'] = number_format((float)$row['investment']->deposite_amount, 2, '.', '');
                            $data[$key]['maturity_amount'] = number_format((float)$row['investment']->maturity_amount, 2, '.', '');   
                            $data[$key]['associate_code'] =  getSeniorData($row['investment']->associate_id,'associate_no');
                            $data[$key]['associate_name'] = getSeniorData($row['investment']->associate_id,'first_name').' '.getSeniorData($row['investment']->associate_id,'last_name');
                            $data[$key]['opening_date'] =  date("d/m/Y", strtotime($row['investment']->created_at));
                            $data[$key]['due_amount'] =  number_format((float)$row['investment']->due_amount, 2, '.', '');
                            $data[$key]['total_amount'] = $row['investment']->current_balance;
                            $data[$key]['maturity_payable_amount'] = number_format((float)$row->maturity_amount_payable, 2, '.', '');
                            if(isset($row['investment']->tds_deduct_amount))
                            {
                                $data[$key]['tds_amount'] = number_format((float)$row['investment']->tds_deduct_amount, 2, '.', '');
                            }
                            else{
                                $data[$key]['tds_amount']='N/A';
                            }
                            // if($row['investment']->id){
                            //     $investmentAmount = Daybook::where('investment_id',$row->id)->whereIn('transaction_type',[2,4])->sum('deposit');
                            //     $current_balance = $investmentAmount;
                            // }else{
                            //      $current_balance = 0;
                            // }
                             if(isset($current_balance))
                             {
                                $data[$key]['deposit_amount'] = $current_balance ;
                             }
                             else{
                                 $data[$key]['deposit_amount']='N/A';
                             }
                             if(isset($row->id))
                            {
                                $interest = AllHeadTransaction::where('head_id',36)->where('type',13)->whereIn('sub_type',[137,136,133,134])->where('type_transaction_id',$row->id)->first(); 
                            }
                             if(isset($interest))
                            {
                                 $iamnount  =  number_format((float)$interest->amount, 2, '.', '');
                            }
                            else{
                                 $iamnount  = 'N/A';
                            }
                            $data[$key]['interest'] = $iamnount ;
                            if(isset($row->final_amount))       
                             {
                                 $data[$key]['final_amount'] =number_format((float)$row->final_amount, 2, '.', '');;
                             }
                             else{
                                 $data[$key]['final_amount'] = 'N/A';
                             }
                            $type='N/A';
                            if($row->payment_type==0)
                            {
                                $data[$key]['maturity_type']='Expense';
                            } 
                            elseif($row->payment_type ==1)
                            {
                                $data[$key]['maturity_type']='Maturity';
                            }
                            elseif($row->payment_type ==2)
                            {
                                $data[$key]['maturity_type']='PreMaturity';
                            } 
                            elseif($row->payment_type ==3)
                            {
                                $data[$key]['maturity_type']='Death Help';
                            }   
                            elseif($row->payment_type ==4)
                            {
                                $data[$key]['maturity_type']='Emergancy Maturity';
                            }   
                            else{
                                $data[$key]['maturity_type']='N/A';
                            }
                            if($row['investment'])
                            {
                                $data[$key]['maturity_date'] =  date('d/m/Y', strtotime($row['investment']->created_at. ' + '.($row['investment']->tenure).' year'));
                            }
                            else{
                                  $data[$key]['maturity_date'] = "N/A";
                            }
                            if( $row['investment']->tenure)
                            {
                                $data[$key]['tenure'] = $row['investment']->tenure;
                            }
                            else{
                                $data[$key]['tenure'] = "N/A";
                            }
                            if( $row->status==0)
                            {
                                $data[$key]['status'] = 'Pending';
                            }
                            else{
                                $data[$key]['status'] = "Paid";
                            }
                             if($row)
                            {
                                if($row->payment_mode == 0)
                                {
                                    $data[$key]['payment_mode'] = "Cash";
                                }
                                if($row->payment_mode == 1)
                                {
                                    $data[$key]['payment_mode'] = "Cheque";
                                }
                                if($row->payment_mode == 2)
                                {
                                    $data[$key]['payment_mode'] = "Online Transfer";
                                }
                                if($row->payment_mode == 3)
                                {
                                    $data[$key]['payment_mode'] = "SSB Transfer";
                                }
                            }
                            else{
                                $data[$key]['payment_mode'] = "N/A";
                            }
                            if($row->payment_mode == 1)
                            {
                                 $transaction = getMaturityTransactionRecord(13,133,$row->id);
                                 if($transaction)
                                 {
                                     $data[$key]['cheque_no'] =$transaction->cheque_no;
                                 }
                                 else{
                                     $data[$key]['cheque_no'] ='N/A';
                                 }
                            }
                            elseif($row->payment_mode == 2)
                            {
                                $transaction =getDemandTransactionDetails(13,$row->id);
                                if($transaction)
                                 {
                                     $data[$key]['cheque_no'] =$transaction->transction_no;
                                 }
                                 else{
                                     $data[$key]['cheque_no'] ='N/A';
                                 }
                            }
                            else{
                                 $data[$key]['cheque_no'] ='N/A';
                            }
                            if(isset($row->date))
            				{
            				$data[$key]['payment_date'] =  date('d/m/Y', strtotime($row->date));
            				}
            				else{
            				$data[$key]['payment_date'] = "N/A";
            				}
                            //ssb payment
                            if($row['member'])
                            {
                                $ac = SavingAccount::where('member_id',$row['member']->id)->first();
                                if($row->payment_mode == 3)
                                {
                                $ac = SavingAccount::where('member_id',$row['member']->id)->first();
                                if($ac ){
                                    $data[$key]['ssb_ac'] =$ac->account_no;
                                }
                                else{
                                    $data[$key]['ssb_ac']  =$row['demandadvice']->ssb_account;
                                }
                            }
                            elseif(isset($ac->account_no))
                            {
                                $data[$key]['ssb_ac'] =$ac->account_no;
                            }
                            else{
                                $data[$key]['ssb_ac'] ='N/A';  
                            }
                            }
                            else{
                                $data[$key]['ssb_ac'] ='N/A';  
                            }
                            if($row)
                            {
                                if($row->payment_mode == 1 || $row->payment_mode == 2)
                            {
                                // $transaction = getMaturityTransactionRecord(13,133,$row['demandadvice']->id);
                                 if(isset($row->bank_name))
                                 {
                                     $data[$key]['bank_name'] =$row->bank_name;
                                 }
                                 else{
                                     $data[$key]['bank_name'] ='N/A';
                                 }      
                            }
                            else{
                                $data[$key]['bank_name'] ='N/A';   
                            }
                            }
                            else{
                                $data[$key]['bank_name'] ='N/A';   
                            }
                            if($row)
                            {
                                if($row->payment_mode == 1 || $row->payment_mode == 2)
                                {
                                    // $transaction = getMaturityTransactionRecord(13,133,$row['demandadvice']->id);
                                     if(isset($row->bank_account_number))
                                     {
                                         $data[$key]['bank_ac'] =$row->bank_account_number;
                                     }
                                     else{
                                         $data[$key]['bank_ac'] ='N/A';
                                     }  
                                    }
                                    else{
                                        $data[$key]['bank_ac'] ='N/A'; 
                                    }
                            }
                            else{
                                $data[$key]['bank_ac'] ='N/A'; 
                            }
                            // rtgs charge
                            if($row)
                            {
                                if($row->payment_mode == 2 )
                                {
                                    $transaction = AllHeadTransaction::where('head_id',92)->where('type_id',$row->id)->first();;
                                    if($transaction)
                                    {
                                        $data[$key]['rtgs_chrg'] =number_format((float)$transaction->amount, 2, '.', '');
                                    }
                                     else{
                                         $$data[$key]['rtgs_chrg'] ='N/A';
                                    }
                                    }
                                    else{
                                        $data[$key]['rtgs_chrg'] ='N/A';   
                                    }
                            }
                            else{
                                $data[$key]['rtgs_chrg'] ='N/A';   
                            }
                        }
                        $status   = "Success";
                        $code     = 200;
                        $messages = 'Maturity Report !'; 
                        $page  = $request->page;
                        $length  = $request->length;
                        $result   = ['maturity' => $data,'total_count'=>$count,'page'=>$page,'length'=>$length,'record_count'=>count($data)];
                        $associate_status=$member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result','associate_status'), $code); 
                    }
                    else
                    {
                        $status = "Error";
                        $code = 201;
                        $messages = 'Page no or length must be grater than 0!';
                        $result = '';
                        $associate_status=$member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result','associate_status'), $code);
                    }                  
                }else{
                    $status = "Error";
                    $code = 201;
                    $messages = 'API token mismatch!';
                    $result = '';
                    $associate_status=$member->associate_app_status;
                    return response()->json(compact('status', 'code', 'messages', 'result','associate_status'), $code);
                }
            }else{
                $status = "Error";
                $code = 201;
                $messages = 'Something went wrong!';
                $result = '';
                $associate_status=9;
                return response()->json(compact('status', 'code', 'messages', 'result','associate_status'), $code);
            } 
        } catch (Exception $e) {
            $status = "Error";
            $code = 500;
            $messages = $e->getMessage();
            $result = '';
            $associate_status=9;
            return response()->json(compact('status', 'code', 'messages', 'result','associate_status'), $code);
        }
    }
    */
    public function associate_maturity_report(Request $request)
    {
        $associate_no = $request->associate_no;
        $length = $request->length;
        $page = $request->page;
        try {
            $member = Member::select('id', 'associate_app_status')->where('associate_no', $request->associate_no)->where('associate_status', 1)->where('is_block', 0)->first();
            if ($member) {
                $token = md5($associate_no);
                if ($token == $request->token) {
                    if ($page > 0) {
                        $data = [];
                        $tree_array1 = [];
                        $currentDate = date('Y-m-d');
                        $previousthreeMonthDate = date('Y-m-d', strtotime('-2 months'));
                        $previousOneMonth = date('Y-m-d', strtotime('-1 months'));

                        $a = $this->getCategoryTree($member->id, []);
                        $b = ['member_id' => $member->id, 'status' => 1, 'is_block' => 0];
                        $c = array_merge($a, [$b]);

                        foreach ($c as $v) {
                            $tree_array1[] = $v['member_id'];
                        }

                        $memberArray1 = Member::whereIn('associate_id', $tree_array1)->pluck('id');

                        $invest_data = DemandAdvice::whereHas('investment', function ($query) use ($memberArray1) {
                                $query->whereIn('member_investments.member_id', $memberArray1)
                                      ->with([
                                        'member:id,first_name,last_name,member_id',
                                        'associateMember:associate_no,id,first_name,last_name',
                                        'plan:id,name',
                                    ]);
                            })
                            ->where('is_mature', 0)
                            ->with([
                                'branch:id,name,branch_code',
                                'demandAmountHead:cheque_no,id,type_id,head_id',
                                'demandTransactionAmount:transction_no,id,type,type_id',
                                'sumdeposite2:investment_id,transaction_type,deposit'
                            ])
                            ->where(function ($query)use ($previousOneMonth, $currentDate) {
                                $query->where('status', 0)
                                      ->orWhere(function ($query) use ($previousOneMonth, $currentDate) {
                                          $query->where('status', '!=', 0)
                                                ->whereBetween('payment_date', [$previousOneMonth, $currentDate]);
                                      });
                            });
                            // ->whereBetween('date', [$previousthreeMonthDate, $currentDate])
                            // ;
                        
                        $invest_data = ($request->type != '') ? ($request->type == 0 ? $invest_data : ($request->type == 1 ? $invest_data->whereStatus(0) : $invest_data->whereStatus(1)) ) : $invest_data ;

                        $count = $invest_data->count('id');

                        $start = ($page == 1 ? 0 : (isset($length) ? ($page - 1) * $length : 0 ));
                        
                        $invest_data = isset($length) ? $invest_data->orderby('id', 'DESC')->offset($start)->limit($length)->get() : $invest_data->orderby('id', 'DESC')->get();
                                                
                        $maturity_type = [
                            0 => 'Expense',
                            1 => 'Maturity',
                            2 => 'PreMaturity',
                            3 => 'Death Help',
                            4 => 'Emergency Maturity',
                        ];
                        $payment_mode = [
                            '0' => 'Cash',
                            '1' => 'Cheque',
                            '2' => 'Online Transfer',
                            '3' => 'SSB Transfer',
                        ];
                        // foreach ($invest_data as $key => $row) {  
                        for ($key = 0; $key < count($invest_data); $key++) {
                            $row = $invest_data[$key];
                            // $current_balance = 0;   
                            $current_balance = isset($row->investment_id) ? $row->getCurrentBalanceAttribute() : 0;

                            $transaction = null;
                            $cheque_no = 'N/A';

                            if ($row->payment_mode == 1 && isset($row->demandAmount)) {
                                $transaction = $row->demandAmount;
                                $cheque_no = $transaction->cheque_no ?? $cheque_no;
                            } elseif ($row->payment_mode == 2 && isset($row->demandTransactionAmount)) {
                                $transaction = $row->demandTransactionAmount;
                                $cheque_no = $transaction->transction_no ?? $cheque_no;
                            }              

                           

                            $data[$key] = [
                                'id' => $row->id, 
                                'branch_code' => $row['branch']->branch_code, 
                                'branch_name' => $row['branch']->name, 
                                'account_no' => $row['investment']->account_number, 
                                'member_name' => $row->investment->member->first_name . ' ' . $row->investment->member->last_name, 
                                'member_id' => $row->investment->member->member_id, 
                                'plan_name' => $row->investment->plan->name, 
                                'deno' => number_format((float)$row['investment']->deposite_amount, 2, '.', ''), 
                                'maturity_amount' => number_format((float)$row['investment']->maturity_amount, 2, '.', ''), 
                                'associate_code' => $row->investment->associateMember->associate_no, 
                                'associate_name' => $row->investment->associateMember->first_name . ' ' . $row->investment->associateMember->last_name, 
                                'opening_date' =>  date("d/m/Y", strtotime($row['investment']->created_at)), 
                                'total_amount' => $row['investment']->current_balance, 
                                'maturity_payable_amount' => number_format((float)$row->maturity_amount_payable, 2, '.', ''), 
                                'due_amount' => '', 
                                // 'tds_amount' => isset($row['investment']->tds_deduct_amount) ? number_format((float)$row['investment']->tds_deduct_amount, 2, '.', '') : 'N/A', 
                                'tds_amount' => isset($row->tds_amount) ? number_format((float)$row->tds_amount, 2, '.', '') : 'N/A', 
                                'deposit_amount' => isset($current_balance) ? $current_balance : 'N/A', 
                                'interest' => isset($row->investment_id) ? round($row->final_amount - $row->maturity_prematurity_amount + ($row->tds_amount??0)) : 'N/A', 
                                'maturity_type' => $maturity_type[$row->payment_type] ?? 'N/A', 
                                'maturity_date' =>  $row['investment'] ? date('d/m/Y', strtotime($row['investment']->created_at . ' + ' . ($row['investment']->tenure) . ' year')) : 'N/A', 
                                'tenure' => $row['investment']->tenure??'N/A', 
                                'status' => ($row->status == 0) ? 'Pending' : 'Paid', 
                                'payment_mode' => $payment_mode[$row->payment_type] ?? 'N/A', 
                                'final_amount' => isset($row->final_amount) ? $row->final_amount : '', 
                                'cheque_no' => $cheque_no,
                                'payment_date' => isset($row->payment_date) ? date('d/m/Y', strtotime($row->payment_date)) : 'N/A',
                                'ssb_ac' => ($row['investment']['member']) ? (($row->payment_mode == 3) ? (($ac = getMemberSsbAccountDetail($row['investment']['member']->id)) ? $ac->account_no : ($row['demandadvice'] ? $row['demandadvice']->ssb_account : 'N/A')) : (isset($ac->account_no) ? $ac->account_no : 'N/A')) : 'N/A',
                                'bank_name' => ($row && ($row->payment_mode == 1 || $row->payment_mode == 2)) ? (isset($row->bank_name) ? $row->bank_name : 'N/A') : 'N/A',
                                'bank_ac' => ($row && ($row->payment_mode == 1 || $row->payment_mode == 2)) ? (isset($row->bank_account_number) ? $row->bank_account_number : 'N/A') : 'N/A',
                                'rtgs_chrg' => ($row && $row->payment_mode == 2) ? (isset($row->demandAmountHead) ? number_format((float)$row->demandAmountHead->amount, 2, '.', '') : 'N/A') : 'N/A',
                            ];
                        }
                        $status   = "Success";
                        $code     = 200;
                        $messages = 'Maturity Report !';
                        $page  = $request->page;
                        $length  = $request->length;
                        $result   = ['maturity' => $data, 'total_count' => $count, 'page' => $page, 'length' => $length, 'record_count' => count($data)];
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
        } catch (Exception $e) {
            $status = "Error";
            $code = 500;
            $messages = $e->getMessage();
            $result = '';
            $associate_status = 9;
            return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
        }
    }
    
    ///-------------------- Associate Collection Report -------   
    public function associateCollectionReport(Request $request)
    {
        $associate_no = $request->associate_no;
        if ($request->start_date != 0) {
            $startDate = date('Y-m-d', strtotime(convertDate($request->start_date)));;
        } elseif ($request->start_date == 0) {
            $startDate = date('Y-m-d', strtotime('first day of -1 months'));
        }
        if ($request->end_date != 0) {
            $endDate = date('Y-m-d', strtotime(convertDate($request->end_date)));;
        } elseif ($request->end_date == 0) {
            $endDate = date('Y-m-d', strtotime('last day of -1 months'));
        }
        $status = '';
        try {
            $member = Member::select('id', 'associate_app_status', 'associate_branch_id')->where('associate_no', $request->associate_no)->where('associate_status', 1)->where('is_block', 0)->first();
            if ($member) {
                $token = md5($request->associate_no);
                if ($token == $request->token) {
                    if ($request->page > 0) {
                        $data = array();
                        $a = $this->getCategoryTree($parent_id = $member->id, $tree_array = array());
                        $b[] = array('member_id' => $member->id, 'status' => 1, 'is_block' => 0);
                        $tree_array1 = array();
                        $c = array_merge($a, $b);
                        foreach ($a as $v) {
                            if ($v['status'] == 1 && $v['is_block'] == 0) {
                                $tree_array1[] =  $v['member_id'];
                            }
                        }
                        $member_data = Member::with('associate_branch')->whereIn('id', $tree_array1);
                        if ($request->branch_id != 0) {
                            $branch_id = $request->branch_id;
                            // $member_data = $member_data->where('associate_branch_id',$request->branch_id);
                        } elseif ($request->branch_id == 0) {
                            $branch_id = '';
                        }
                        $member_data1 = $member_data->orderby('associate_join_date', 'DESC')->get();
                        $count = count($member_data1);
                        if ($request->page == 1) {
                            $start = 0;
                        } else {
                            if (isset($request->length)) {
                                $start = ($request->page - 1) * $request->length;
                            }
                        }
                        if (isset($request->length)) {
                            $member_data = $member_data->orderby('associate_join_date', 'DESC')->offset($start)->limit($request->length)->get();
                        } else {
                            $member_data = $member_data->orderby('associate_join_date', 'DESC')->get();
                        }
                        foreach ($member_data as $key => $row) {
                            //$startDate='';$endDate='';$branch_id='';
                            $associate_id = $row->id;
                            $planDaily = getPlanID('710')->id;
                            $dailyId = array($planDaily);
                            $planSSB = getPlanID('703')->id;
                            $planKanyadhan = getPlanID('709')->id;
                            $planMB = getPlanID('709')->id;
                            $planFRD = getPlanID('707')->id;
                            $planJeevan = getPlanID('713')->id;
                            $planRD = getPlanID('704')->id;
                            $planBhavhishya = getPlanID('718')->id;
                            $monthlyId = array($planKanyadhan, $planMB, $planFRD, $planJeevan, $planRD, $planBhavhishya);
                            $planMI = getPlanID('712')->id;
                            $planFFD = getPlanID('705')->id;
                            $planFD = getPlanID('706')->id;
                            $fdId = array($planMI, $planFFD, $planFD);
                            $data[$key]['branch'] = $row['associate_branch']->name;
                            $data[$key]['branch_code'] = $row['associate_branch']->branch_code;
                            $data[$key]['associate_code'] = $row->associate_no;
                            $data[$key]['associate_name'] = $row->first_name . ' ' . $row->last_name;
                            // $data[$key]['cadre']=getCarderName($row->current_carder_id); 
                            $data[$key]['daily_deno_sum'] = investNewDenoSum($associate_id, $startDate, $endDate, $planDaily, $branch_id);
                            // dd($associate_id,$startDate,$endDate,$planDaily,$branch_id);
                            $daily_renew = investRenewAmountSum($associate_id, $startDate, $endDate, $dailyId, $branch_id);
                            $data[$key]['monthly_deno_sum'] = investNewDenoSumType($associate_id, $startDate, $endDate, $monthlyId, $branch_id);
                            $mon_renew = investRenewAmountSum($associate_id, $startDate, $endDate, $monthlyId, $branch_id);
                            $data[$key]['fd_deno_sum'] = investNewDenoSumType($associate_id, $startDate, $endDate, $fdId, $branch_id);
                            $ssb_deno_sum = totalInvestSSbAmtByType($associate_id, $startDate, $endDate, $branch_id, 1);
                            $ssb = totalInvestSSbAmtByType($associate_id, $startDate, $endDate, $branch_id, 2);
                            // $sum_ni_amount=$data[$key]['daily_deno_sum']+$data[$key]['monthly_deno_sum']+$data[$key]['fd_deno_sum']+$data[$key]['ssb_deno_sum'];
                            // $data[$key]['total_ni_amount']=number_format((float)$sum_ni_amount, 2, '.', '');
                            $sum_renew_amount = $daily_renew + $mon_renew + $ssb;
                            // $data[$key]['total_amount']=number_format((float)$sum_renew_amount, 2, '.', ''); 
                            // $data[$key]['other_mt']=investOtherMiByType($associate_id,$startDate,$endDate,$branch_id,1,11);
                            // $data[$key]['other_stn']=investOtherMiByType($associate_id,$startDate,$endDate,$branch_id,1,12);
                            // $ni_m=$data[$key]['daily_deno_sum']+$data[$key]['monthly_deno_sum']+$data[$key]['fd_deno_sum'];
                            // $tcc_m=$data[$key]['daily_deno_sum']+$data[$key]['monthly_deno_sum']+$data[$key]['fd_deno_sum']+$data[$key]['daily_renew']+$data[$key]['monthly_renew'];
                            $tcc = $data[$key]['daily_deno_sum'] + $data[$key]['monthly_deno_sum'] + $data[$key]['fd_deno_sum'] + $ssb_deno_sum + $daily_renew + $mon_renew + $sum_renew_amount;
                            // $data[$key]['ni_m']=number_format((float)$ni_m, 2, '.', '');
                            // $data[$key]['ni']=number_format((float)$sum_ni_amount, 2, '.', '');
                            // $data[$key]['tcc_m']=number_format((float)$tcc_m, 2, '.', '');
                            $data[$key]['tcc'] = number_format((float)$tcc, 2, '.', '');
                            // $data[$key]['loan_amount']=totalLoanAmount($associate_id,$startDate,$endDate,$branch_id);
                            $data[$key]['loan_recovery_amount'] = totalRenewLoanAmount($associate_id, $startDate, $endDate, $branch_id);
                            $data[$key]['collection_all'] = number_format((float)$sum_renew_amount, 2, '.', '');
                            $ncc = $data[$key]['daily_deno_sum'] + $data[$key]['monthly_deno_sum'] + $data[$key]['fd_deno_sum'];
                            $data[$key]['ncc'] = number_format((float)$ncc, 2, '.', '');
                        }
                        $status   = "Success";
                        $code     = 200;
                        $messages = 'Associate Collection Report  !';
                        $page  = $request->page;
                        $length  = $request->length;
                        $result   = ['business_report' => $data, 'total_count' => $count, 'page' => $page, 'length' => $length, 'record_count' => count($data)];
                        $associate_status = $member->associate_app_status;
                        return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                    } elseif ($request->page == 0) {
                        $data = array();
                        $a = $this->getCategoryTree($parent_id = $member->id, $tree_array = array());
                        $b[] = array('member_id' => $member->id, 'status' => 1, 'is_block' => 0);
                        $tree_array1 = array();
                        $c = array_merge($a, $b);
                        foreach ($a as $v) {
                            if ($v['status'] == 1 && $v['is_block'] == 0) {
                                $tree_array1[] =  $v['member_id'];
                            }
                        }
                        $member_data = Member::with('associate_branch')->whereIn('id', $tree_array1);
                        if (isset($request->branch_id)) {
                            $branch_id = $request->branch_id;
                            // $member_data = $member_data->where('associate_branch_id',$request->branch_id);
                        } else {
                            $branch_id = $member->branch_id;
                            // $member_data = $member_data->where('associate_branch_id',$member->associate_branch_id);
                        }
                        $member_data1 = $member_data->orderby('associate_join_date', 'DESC')->get();
                        $count = count($member_data1);
                        if ($request->page == 1) {
                            $start = 0;
                        } else {
                            $start = ($request->page - 1) * $request->length;
                        }
                        $member_data = $member_data->orderby('associate_join_date', 'DESC')->get();
                        foreach ($member_data as $key => $row) {
                            //$startDate='';$endDate='';$branch_id='';
                            $associate_id = $row->id;
                            $planDaily = getPlanID('710')->id;
                            $dailyId = array($planDaily);
                            $planSSB = getPlanID('703')->id;
                            $planKanyadhan = getPlanID('709')->id;
                            $planMB = getPlanID('709')->id;
                            $planFRD = getPlanID('707')->id;
                            $planJeevan = getPlanID('713')->id;
                            $planRD = getPlanID('704')->id;
                            $planBhavhishya = getPlanID('718')->id;
                            $monthlyId = array($planKanyadhan, $planMB, $planFRD, $planJeevan, $planRD, $planBhavhishya);
                            $planMI = getPlanID('712')->id;
                            $planFFD = getPlanID('705')->id;
                            $planFD = getPlanID('706')->id;
                            $fdId = array($planMI, $planFFD, $planFD);
                            $data[$key]['branch'] = $row['associate_branch']->name;
                            $data[$key]['branch_code'] = $row['associate_branch']->branch_code;
                            $data[$key]['associate_code'] = $row->associate_no;
                            $data[$key]['associate_name'] = $row->first_name . ' ' . $row->last_name;
                            // $data[$key]['cadre']=getCarderName($row->current_carder_id); 
                            $data[$key]['daily_deno_sum'] = investNewDenoSum($associate_id, $startDate, $endDate, $planDaily, $branch_id);
                            $daily_renew = investRenewAmountSum($associate_id, $startDate, $endDate, $dailyId, $branch_id);
                            $data[$key]['monthly_deno_sum'] = investNewDenoSumType($associate_id, $startDate, $endDate, $monthlyId, $branch_id);
                            $mon_renew = investRenewAmountSum($associate_id, $startDate, $endDate, $monthlyId, $branch_id);
                            $data[$key]['fd_deno_sum'] = investNewDenoSumType($associate_id, $startDate, $endDate, $fdId, $branch_id);
                            $ssb_deno_sum = totalInvestSSbAmtByType($associate_id, $startDate, $endDate, $branch_id, 1);
                            $ssb = totalInvestSSbAmtByType($associate_id, $startDate, $endDate, $branch_id, 2);
                            // $sum_ni_amount=$data[$key]['daily_deno_sum']+$data[$key]['monthly_deno_sum']+$data[$key]['fd_deno_sum']+$data[$key]['ssb_deno_sum'];
                            // $data[$key]['total_ni_amount']=number_format((float)$sum_ni_amount, 2, '.', '');
                            $sum_renew_amount = $daily_renew + $mon_renew + $ssb;
                            // $data[$key]['total_amount']=number_format((float)$sum_renew_amount, 2, '.', ''); 
                            // $data[$key]['other_mt']=investOtherMiByType($associate_id,$startDate,$endDate,$branch_id,1,11);
                            // $data[$key]['other_stn']=investOtherMiByType($associate_id,$startDate,$endDate,$branch_id,1,12);
                            // $ni_m=$data[$key]['daily_deno_sum']+$data[$key]['monthly_deno_sum']+$data[$key]['fd_deno_sum'];
                            // $tcc_m=$data[$key]['daily_deno_sum']+$data[$key]['monthly_deno_sum']+$data[$key]['fd_deno_sum']+$data[$key]['daily_renew']+$data[$key]['monthly_renew'];
                            $tcc = $data[$key]['daily_deno_sum'] + $data[$key]['monthly_deno_sum'] + $data[$key]['fd_deno_sum'] + $ssb_deno_sum + $daily_renew + $mon_renew + $sum_renew_amount;
                            // $data[$key]['ni_m']=number_format((float)$ni_m, 2, '.', '');
                            // $data[$key]['ni']=number_format((float)$sum_ni_amount, 2, '.', '');
                            // $data[$key]['tcc_m']=number_format((float)$tcc_m, 2, '.', '');
                            $data[$key]['tcc'] = number_format((float)$tcc, 2, '.', '');
                            // $data[$key]['loan_amount']=totalLoanAmount($associate_id,$startDate,$endDate,$branch_id);
                            $data[$key]['loan_recovery_amount'] = totalRenewLoanAmount($associate_id, $startDate, $endDate, $branch_id);
                            $data[$key]['collection_all'] = number_format((float)$sum_renew_amount, 2, '.', '');
                            $ncc = $data[$key]['daily_deno_sum'] + $data[$key]['monthly_deno_sum'] + $data[$key]['fd_deno_sum'];
                            $data[$key]['ncc'] = number_format((float)$ncc, 2, '.', '');
                        }
                        $status   = "Success";
                        $code     = 200;
                        $messages = 'Associate Collection Report  !';
                        $result   = ['business_report' => $data, 'total_count' => $count, 'record_count' => count($data)];
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
        } catch (Exception $e) {
            $status = "Error";
            $code = 500;
            $messages = $e->getMessage();
            $result = '';
            $associate_status = 9;
            return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
        }
    }
    //---------   Collection compare ----------------   
    public function associateCollectionCompareReport(Request $request)
    {
        $associate_no = $request->associate_no;
        try {
            $member = Member::select('id', 'associate_app_status', 'branch_id')->where('associate_no', $request->associate_no)->where('associate_status', 1)->where('is_block', 0)->first();
            if ($member) {
                $token = md5($request->associate_no);
                if ($token == $request->token) {
                    $current_start_date = '';
                    $current_end_date = '';
                    $comp_start_date = '';
                    $comp_end_date = '';
                    if ($request->current_start_date != 0) {
                        $current_start_date = date("Y-m-d", strtotime(convertDate($request->current_start_date)));
                    } elseif ($request->current_start_date == 0) {
                        $current_start_date = date('Y-m-d', strtotime('first day of -1 months'));
                    }
                    if ($request->current_end_date != 0) {
                        $current_end_date = date("Y-m-d ", strtotime(convertDate($request->current_end_date)));
                    } elseif ($request->current_end_date == 0) {
                        $current_end_date = date('Y-m-d', strtotime('last day of -1 months'));
                    }
                    if ($request->comp_start_date != 0) {
                        $comp_start_date = date("Y-m-d", strtotime(convertDate($request->comp_start_date)));
                    } elseif ($request->comp_start_date == 0) {
                        $comp_start_date = date('Y-m-d', strtotime('first day of -2 months'));
                    }
                    if ($request->comp_end_date != 0) {
                        $comp_end_date = date("Y-m-d ", strtotime(convertDate($request->comp_end_date)));
                    } elseif ($request->comp_end_date == 0) {
                        $comp_end_date = date('Y-m-d', strtotime('last day of -2 months'));
                    }
                    if ($current_start_date != '' && $current_end_date != '' && $comp_start_date != '' && $comp_end_date != '') {
                        // dd($current_start_date,$current_end_date,$comp_end_date,$comp_start_date);
                        if ($request->page > 0) {
                            $data = array();
                            $a = $this->getCategoryTree($parent_id = $member->id, $tree_array = array());
                            $b[] = array('member_id' => $member->id, 'status' => 1, 'is_block' => 0);
                            $tree_array1 = array();
                            $c = array_merge($a, $b);
                            foreach ($c as $v) {
                                if ($v['status'] == 1 && $v['is_block'] == 0) {
                                    $tree_array1[] =  $v['member_id'];
                                }
                            }
                            $member_data = Member::with('associate_branch')->whereIn('id', $tree_array1);
                            if ($request->branch_id != 0) {
                                $branch_id = $request->branch_id;
                                $member_data = $member_data->where('associate_branch_id', $request->branch_id);
                            } elseif ($request->branch_id == 0) {
                                $branch_id = '';
                                // $member_data = $member_data->where('associate_branch_id',$member->branch_id);
                            }
                            $member_data1 = $member_data->orderby('associate_join_date', 'DESC')->get();
                            $count = count($member_data1);
                            if ($request->page == 1) {
                                $start = 0;
                            } else {
                                if (isset($request->length)) {
                                    $start = ($request->page - 1) * $request->length;
                                }
                            }
                            if (isset($request->length)) {
                                $member_data = $member_data->orderby('associate_join_date', 'DESC')->offset($start)->limit($request->length)->get();
                            } else {
                                $member_data = $member_data->orderby('associate_join_date', 'DESC')->get();
                            }
                            foreach ($member_data as $key => $row) {
                                $startDate = '';
                                $endDate = '';
                                $branch_id = '';
                                $associate_id = $row->id;
                                $planDaily = getPlanID('710')->id;
                                $dailyId = array($planDaily);
                                $planSSB = getPlanID('703')->id;
                                $planKanyadhan = getPlanID('709')->id;
                                $planMB = getPlanID('709')->id;
                                $planFRD = getPlanID('707')->id;
                                $planJeevan = getPlanID('713')->id;
                                $planRD = getPlanID('704')->id;
                                $planBhavhishya = getPlanID('718')->id;
                                $monthlyId = array($planKanyadhan, $planMB, $planFRD, $planJeevan, $planRD, $planBhavhishya);
                                $planMI = getPlanID('712')->id;
                                $planFFD = getPlanID('705')->id;
                                $planFD = getPlanID('706')->id;
                                $fdId = array($planMI, $planFFD, $planFD);
                                //$branch_id = ($request->branch_id != '') ? $request->branch_id : $row->branch_id;
                                $data[$key]['branch'] = $row['associate_branch']->name;
                                $data[$key]['branch_code'] = $row['associate_branch']->branch_code;
                                // $data[$key]['sector_name']=$row['associate_branch']->sector;
                                // $data[$key]['region_name']=$row['associate_branch']->regan;
                                // $data[$key]['zone_name']=$row['associate_branch']->zone;
                                // $data[$key]['member_id']=$row->associate_no;
                                $data[$key]['name'] = $row->first_name . ' ' . $row->last_name;
                                $data[$key]['associate_code'] = $row->associate_no;
                                // $data[$key]['cadre']=getCarderName($row->current_carder_id);
                                // $data[$key]['current_daily_new_ac']=investNewAcCount($associate_id,$current_start_date,$current_end_date,$planDaily,$branch_id);
                                $data[$key]['current_daily_deno_sum'] = investNewDenoSum($associate_id, $current_start_date, $current_end_date, $planDaily, $branch_id);
                                // $data[$key]['current_daily_renew_ac']=investRenewAc($associate_id,$current_start_date,$current_end_date,$dailyId,$branch_id);
                                // $data[$key]['current_daily_renew']=investRenewAmountSum($associate_id,$current_start_date,$current_end_date,$dailyId,$branch_id);
                                $current_daily_renew = investRenewAmountSum($associate_id, $current_start_date, $current_end_date, $dailyId, $branch_id);
                                // $data[$key]['current_monthly_new_ac']=investNewAcCountType($associate_id,$current_start_date,$current_end_date,$monthlyId,$branch_id);
                                $data[$key]['current_monthly_deno_sum'] = investNewDenoSumType($associate_id, $current_start_date, $current_end_date, $monthlyId, $branch_id);
                                // $data[$key]['current_monthly_renew_ac']=investRenewAc($associate_id,$current_start_date,$current_end_date,$monthlyId,$branch_id);
                                // $data[$key]['current_monthly_renew']=investRenewAmountSum($associate_id,$current_start_date,$current_end_date,$monthlyId,$branch_id);
                                $current_monthly_renew = investRenewAmountSum($associate_id, $current_start_date, $current_end_date, $monthlyId, $branch_id);
                                // $data[$key]['current_fd_new_ac']=investNewAcCountType($associate_id,$current_start_date,$current_end_date,$fdId,$branch_id);
                                $data[$key]['current_fd_deno_sum'] = investNewDenoSumType($associate_id, $current_start_date, $current_end_date, $fdId, $branch_id);
                                // $data[$key]['current_ssb_new_ac']=totalInvestSSbAcCountByType($associate_id,$current_start_date,$current_end_date,$branch_id,1);
                                // $data[$key]['current_ssb_deno_sum']=totalInvestSSbAmtByType($associate_id,$current_start_date,$current_end_date,$branch_id,1);
                                $current_ssb_deno_sum = totalInvestSSbAmtByType($associate_id, $current_start_date, $current_end_date, $branch_id, 1);
                                // $data[$key]['current_ssb_renew_ac']=totalInvestSSbAcCountByType($associate_id,$current_start_date,$current_end_date,$branch_id,2);
                                // $data[$key]['current_ssb_renew']=totalInvestSSbAmtByType($associate_id,$current_start_date,$current_end_date,$branch_id,2);
                                $current_ssb_renew = totalInvestSSbAmtByType($associate_id, $current_start_date, $current_end_date, $branch_id, 2);
                                // $current_sum_ni_ac=$data[$key]['current_daily_new_ac']+$data[$key]['current_monthly_new_ac']+$data[$key]['current_fd_new_ac']+$data[$key]['current_ssb_new_ac'];  
                                // $current_sum_ni_amount=$data[$key]['current_daily_deno_sum']+$data[$key]['current_monthly_deno_sum']+$data[$key]['current_fd_deno_sum']+$data[$key]['current_ssb_deno_sum'];
                                // $data[$key]['current_total_ni_ac']=$current_sum_ni_ac; 
                                // $data[$key]['current_total_ni_amount']=number_format((float)$current_sum_ni_amount, 2, '.', '');
                                // $current_sum_renew_ac=$data[$key]['current_daily_renew_ac']+$data[$key]['current_monthly_renew_ac'];  
                                $current_sum_renew_amount = $current_daily_renew + $current_monthly_renew;
                                $data[$key]['current_all_collection'] = number_format((float)$current_sum_renew_amount, 2, '.', '');
                                // $data[$key]['current_total_ac']=$current_sum_renew_ac;
                                // $data[$key]['current_total_amount']=number_format((float)$current_sum_renew_amount, 2, '.', '');
                                // $data[$key]['current_other_mt']=investOtherMiByType($associate_id,$current_start_date,$current_end_date,$branch_id,1,11);
                                // $data[$key]['current_other_stn']=investOtherMiByType($associate_id,$current_start_date,$current_end_date,$branch_id,1,12);              
                                // $current_ni_m=$data[$key]['current_daily_deno_sum']+$data[$key]['current_monthly_deno_sum']+$data[$key]['current_fd_deno_sum'];              
                                // $current_tcc_m=$data[$key]['current_daily_deno_sum']+$data[$key]['current_monthly_deno_sum']+$data[$key]['current_fd_deno_sum']+$data[$key]['current_daily_renew']+$data[$key]['current_monthly_renew'];               
                                $current_tcc = $data[$key]['current_daily_deno_sum'] + $data[$key]['current_monthly_deno_sum'] + $data[$key]['current_fd_deno_sum'] + $current_ssb_deno_sum + $current_daily_renew + $current_monthly_renew + $current_ssb_renew;
                                // $data[$key]['current_ni_m']=number_format((float)$current_ni_m, 2, '.', '');
                                // $data[$key]['current_ni']=number_format((float)$current_sum_ni_amount, 2, '.', '');
                                // $data[$key]['current_tcc_m']=number_format((float)$current_tcc_m, 2, '.', '');
                                $data[$key]['current_tcc'] = number_format((float)$current_tcc, 2, '.', '');
                                $data[$key]['current_ncc'] = $data[$key]['current_daily_deno_sum'] + $data[$key]['current_monthly_deno_sum'] + $data[$key]['current_fd_deno_sum'];
                                // $data[$key]['current_loan_ac']=totalLoanAc($associate_id,$current_start_date,$current_end_date,$branch_id);
                                // $data[$key]['current_loan_amount']=totalLoanAmount($associate_id,$current_start_date,$current_end_date,$branch_id);
                                // $data[$key]['current_loan_recovery_ac']=totalRenewLoanAc($associate_id,$current_start_date,$current_end_date,$branch_id);
                                $data[$key]['current_loan_recovery_amount'] = totalRenewLoanAmount($associate_id, $current_start_date, $current_end_date, $branch_id);
                                // $data[$key]['current_new_associate']=memberCountByType($associate_id,$current_start_date,$current_end_date,$branch_id,1,0);
                                // $data[$key]['current_total_associate']=memberCountByType($associate_id,$current_start_date,$current_end_date,$branch_id,1,1);
                                // $data[$key]['current_new_member']=memberCountByType($associate_id,$current_start_date,$current_end_date,$branch_id,0,0);
                                // $data[$key]['current_total_member']=memberCountByType($associate_id,$current_start_date,$current_end_date,$branch_id,0,1);                
                                // $data[$key]['compare_daily_new_ac']=investNewAcCount($associate_id,$comp_start_date,$comp_end_date,$planDaily,$branch_id);
                                $data[$key]['compare_daily_deno_sum'] = investNewDenoSum($associate_id, $comp_start_date, $comp_end_date, $planDaily, $branch_id);
                                // $data[$key]['compare_daily_renew_ac']=investRenewAc($associate_id,$comp_start_date,$comp_end_date,$dailyId,$branch_id);
                                // $data[$key]['compare_daily_renew']=investRenewAmountSum($associate_id,$comp_start_date,$comp_end_date,$dailyId,$branch_id);
                                $compare_daily_renew = investRenewAmountSum($associate_id, $comp_start_date, $comp_end_date, $dailyId, $branch_id);
                                // $data[$key]['compare_monthly_new_ac']=investNewAcCountType($associate_id,$comp_start_date,$comp_end_date,$monthlyId,$branch_id);
                                $data[$key]['compare_monthly_deno_sum'] = investNewDenoSumType($associate_id, $comp_start_date, $comp_end_date, $monthlyId, $branch_id);
                                // $data[$key]['compare_monthly_renew_ac']=investRenewAc($associate_id,$comp_start_date,$comp_end_date,$monthlyId,$branch_id);
                                // $data[$key]['compare_monthly_renew']=investRenewAmountSum($associate_id,$comp_start_date,$comp_end_date,$monthlyId,$branch_id);
                                $compare_monthly_renew = investRenewAmountSum($associate_id, $comp_start_date, $comp_end_date, $monthlyId, $branch_id);
                                // $data[$key]['compare_fd_new_ac']=investNewAcCountType($associate_id,$comp_start_date,$comp_end_date,$fdId,$branch_id);
                                $data[$key]['compare_fd_deno_sum'] = investNewDenoSumType($associate_id, $comp_start_date, $comp_end_date, $fdId, $branch_id);
                                // $data[$key]['compare_ssb_new_ac']=totalInvestSSbAcCountByType($associate_id,$comp_start_date,$comp_end_date,$branch_id,1);
                                // $data[$key]['compare_ssb_deno_sum']=totalInvestSSbAmtByType($associate_id,$comp_start_date,$comp_start_date,$branch_id,1);
                                $compare_ssb_deno_sum = totalInvestSSbAmtByType($associate_id, $comp_start_date, $comp_start_date, $branch_id, 1);
                                // $data[$key]['compare_ssb_renew_ac']=totalInvestSSbAcCountByType($associate_id,$comp_start_date,$comp_end_date,$branch_id,2);
                                // $data[$key]['compare_ssb_renew']=totalInvestSSbAmtByType($associate_id,$comp_start_date,$comp_end_date,$branch_id,2);
                                $compare_ssb_renew = totalInvestSSbAmtByType($associate_id, $comp_start_date, $comp_end_date, $branch_id, 2);
                                // $compare_sum_ni_ac=$data[$key]['compare_daily_new_ac']+$data[$key]['compare_monthly_new_ac']+$data[$key]['compare_fd_new_ac']+$data[$key]['compare_ssb_new_ac']; 
                                // $compare_sum_ni_amount=$data[$key]['compare_daily_deno_sum']+$data[$key]['compare_monthly_deno_sum']+$data[$key]['compare_fd_deno_sum']+$data[$key]['compare_ssb_deno_sum'];
                                // $data[$key]['compare_total_ni_ac']=$compare_sum_ni_ac; 
                                // $data[$key]['compare_total_ni_amount']=number_format((float)$compare_sum_ni_amount, 2, '.', '');
                                // $compare_sum_renew_ac=$data[$key]['compare_daily_renew_ac']+$data[$key]['compare_monthly_renew_ac'];  
                                $compare_sum_renew_amount = $compare_daily_renew + $compare_monthly_renew;
                                // $data[$key]['compare_total_ac']=$compare_sum_renew_ac;
                                // $data[$key]['compare_total_amount']=number_format((float)$compare_sum_renew_amount, 2, '.', '');
                                $data[$key]['compare_all_collection'] = number_format((float)$compare_sum_renew_amount, 2, '.', '');
                                // $data[$key]['compare_other_mt']=investOtherMiByType($associate_id,$comp_start_date,$comp_end_date,$branch_id,1,11);
                                // $data[$key]['compare_other_stn']=investOtherMiByType($associate_id,$comp_start_date,$comp_end_date,$branch_id,1,12);
                                // $compare_ni_m=$data[$key]['compare_daily_deno_sum']+$data[$key]['compare_monthly_deno_sum']+$data[$key]['compare_fd_deno_sum'];              
                                // $compare_tcc_m=$data[$key]['compare_daily_deno_sum']+$data[$key]['compare_monthly_deno_sum']+$data[$key]['compare_fd_deno_sum']+$data[$key]['compare_daily_renew']+$data[$key]['compare_monthly_renew'];            
                                $compare_tcc = $data[$key]['compare_daily_deno_sum'] + $data[$key]['compare_monthly_deno_sum'] + $data[$key]['compare_fd_deno_sum'] + $compare_ssb_deno_sum + $compare_daily_renew + $compare_monthly_renew + $compare_ssb_renew;
                                // $data[$key]['compare_ni_m']=number_format((float)$compare_ni_m, 2, '.', '');
                                // $data[$key]['compare_ni']=number_format((float)$compare_sum_ni_amount, 2, '.', '');
                                // $data[$key]['compare_tcc_m']=number_format((float)$compare_tcc_m, 2, '.', '');
                                $data[$key]['compare_tcc'] = number_format((float)$compare_tcc, 2, '.', '');
                                // $data[$key]['compare_loan_ac']=totalLoanAc($associate_id,$comp_start_date,$comp_end_date,$branch_id);
                                // $data[$key]['compare_loan_amount']=totalLoanAmount($associate_id,$comp_start_date,$comp_end_date,$branch_id);
                                // $data[$key]['compare_loan_recovery_ac']=totalRenewLoanAc($associate_id,$comp_start_date,$comp_end_date,$branch_id);
                                $data[$key]['compare_loan_recovery_amount'] = totalRenewLoanAmount($associate_id, $comp_start_date, $comp_end_date, $branch_id);
                                // $data[$key]['compare_new_associate']=memberCountByType($associate_id,$comp_start_date,$comp_end_date,$branch_id,1,0);
                                // $data[$key]['compare_total_associate']=memberCountByType($associate_id,$comp_start_date,$comp_end_date,$branch_id,1,1);
                                // $data[$key]['compare_new_member']=memberCountByType($associate_id,$comp_start_date,$comp_end_date,$branch_id,0,0);
                                // $data[$key]['compare_total_member']=memberCountByType($associate_id,$comp_start_date,$comp_end_date,$branch_id,0,1);
                                // $data[$key]['result_daily_new_ac']=$data[$key]['current_daily_new_ac']-$data[$key]['compare_daily_new_ac'];
                                $data[$key]['result_daily_deno_sum'] = $data[$key]['current_daily_deno_sum'] - $data[$key]['compare_daily_deno_sum'];
                                $data[$key]['compare_ncc'] = $data[$key]['compare_daily_deno_sum'] + $data[$key]['compare_monthly_deno_sum'] + $data[$key]['compare_fd_deno_sum'];
                                // $data[$key]['result_daily_renew_ac']=$data[$key]['current_daily_new_ac']-$data[$key]['compare_daily_new_ac'];
                                // $data[$key]['result_daily_renew']=$data[$key]['current_daily_new_ac']-$data[$key]['compare_daily_new_ac'];
                                // $data[$key]['result_monthly_new_ac']=$data[$key]['current_daily_new_ac']-$data[$key]['compare_daily_new_ac'];
                                $data[$key]['result_monthly_deno_sum'] = $data[$key]['current_monthly_deno_sum'] - $data[$key]['compare_monthly_deno_sum'];
                                // $data[$key]['result_monthly_renew_ac']=$data[$key]['current_monthly_renew_ac']-$data[$key]['compare_monthly_renew_ac'];
                                // $data[$key]['result_monthly_renew']=$data[$key]['current_monthly_renew']-$data[$key]['compare_monthly_renew'];
                                // $data[$key]['result_fd_new_ac']=$data[$key]['current_fd_new_ac']-$data[$key]['compare_fd_new_ac'];
                                $data[$key]['result_fd_deno_sum'] = $data[$key]['current_fd_deno_sum'] - $data[$key]['compare_fd_deno_sum'];
                                // $data[$key]['result_ssb_new_ac']=$data[$key]['current_ssb_new_ac']-$data[$key]['compare_ssb_new_ac'];
                                // $data[$key]['result_ssb_deno_sum']=$data[$key]['current_ssb_deno_sum']-$data[$key]['compare_ssb_deno_sum'];
                                // $data[$key]['result_ssb_renew_ac']=$data[$key]['current_ssb_renew_ac']-$data[$key]['compare_ssb_renew'];
                                // $data[$key]['result_ssb_renew']=$data[$key]['current_ssb_renew']-$data[$key]['compare_ssb_deno_sum'];
                                // $result_sum_ni_ac=$current_sum_ni_ac-$compare_sum_ni_ac;   
                                // $result_sum_ni_amount=$current_sum_ni_amount-$compare_sum_ni_amount;
                                // $data[$key]['result_total_ni_ac']=$result_sum_ni_ac; 
                                // $data[$key]['result_total_ni_amount']=number_format((float)$result_sum_ni_amount, 2, '.', '');
                                // $result_sum_renew_ac=$current_sum_renew_ac-$compare_sum_renew_ac;   
                                $result_sum_renew_amount = $current_sum_renew_amount - $compare_sum_renew_amount;
                                // $data[$key]['result_total_ac']=$result_sum_renew_ac;
                                // $data[$key]['result_total_amount']=number_format((float)$result_sum_renew_amount, 2, '.', '');
                                // $data[$key]['result_other_mt']=$data[$key]['current_other_mt']-$data[$key]['compare_other_mt'];
                                // $data[$key]['result_other_stn']=$data[$key]['current_other_stn']-$data[$key]['compare_other_stn'];
                                // $data[$key]['result_ni_m']=$data[$key]['current_ni_m']-$data[$key]['compare_ni_m'];
                                // $data[$key]['result_ni']=$data[$key]['current_ni']-$data[$key]['compare_ni'];
                                // $data[$key]['result_tcc_m']=$data[$key]['current_tcc_m']-$data[$key]['compare_tcc_m'];
                                $data[$key]['result_tcc'] = $data[$key]['current_tcc'] - $data[$key]['compare_tcc'];
                                // $data[$key]['result_loan_ac']=$data[$key]['current_loan_ac']-$data[$key]['compare_loan_ac'];
                                // $data[$key]['result_loan_amount']=$data[$key]['current_loan_amount']-$data[$key]['compare_loan_amount'];
                                // $data[$key]['result_loan_recovery_ac']=$data[$key]['current_loan_recovery_ac']-$data[$key]['compare_loan_recovery_ac'];
                                $data[$key]['result_loan_recovery_amount'] = $data[$key]['current_loan_recovery_amount'] - $data[$key]['compare_loan_recovery_amount'];
                                // $data[$key]['result_new_associate']=$data[$key]['current_new_associate']-$data[$key]['compare_new_associate'];
                                // $data[$key]['result_total_associate']=$data[$key]['current_total_associate']-$data[$key]['compare_total_associate'];
                                // $data[$key]['result_new_member']=$data[$key]['current_new_member']-$data[$key]['compare_new_member'];
                                // $data[$key]['result_total_member']=$data[$key]['current_total_member']-$data[$key]['compare_total_member']; 
                                $data[$key]['result_collection_all'] = $result_sum_renew_amount;
                                $ncc = $data[$key]['result_daily_deno_sum'] - $data[$key]['result_monthly_deno_sum'] - $data[$key]['result_fd_deno_sum'];
                                $data[$key]['result_ncc'] = $ncc;
                            }
                            $status   = "Success";
                            $code     = 200;
                            $page  = $request->page;
                            $length  = $request->length;
                            $messages = 'Associate Collection Compare Report !';
                            $result   = ['business_summary_report' => $data, 'total_count' => $count, 'page' => $page, 'length' => $length, 'record_count' => count($data)];
                            $associate_status = $member->associate_app_status;
                            return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
                        } elseif ($request->page == 0) {
                            $data = array();
                            $a = $this->getCategoryTree($parent_id = $member->id, $tree_array = array());
                            $b[] = array('member_id' => $member->id, 'status' => 1, 'is_block' => 0);
                            $tree_array1 = array();
                            $c = array_merge($a, $b);
                            foreach ($c as $v) {
                                if ($v['status'] == 1 && $v['is_block'] == 0) {
                                    $tree_array1[] =  $v['member_id'];
                                }
                            }
                            $member_data = Member::with('associate_branch')->whereIn('id', $tree_array1);
                            if (isset($request->branch_id)) {
                                $branch_id = $request->branch_id;
                                // $member_data = $member_data->where('associate_branch_id',$request->branch_id);
                            } else {
                                $branch_id = $member->branch_id;
                                // $member_data = $member_data->where('associate_branch_id',$member->branch_id);
                            }
                            $member_data1 = $member_data->orderby('associate_join_date', 'DESC')->get();
                            $count = count($member_data1);
                            if ($request->page == 1) {
                                $start = 0;
                            } else {
                                $start = ($request->page - 1) * $request->length;
                            }
                            $member_data = $member_data->orderby('associate_join_date', 'DESC')->get();
                            foreach ($member_data as $key => $row) {
                                $startDate = '';
                                $endDate = '';
                                $branch_id = '';
                                $associate_id = $row->id;
                                $planDaily = getPlanID('710')->id;
                                $dailyId = array($planDaily);
                                $planSSB = getPlanID('703')->id;
                                $planKanyadhan = getPlanID('709')->id;
                                $planMB = getPlanID('708')->id;
                                $planFRD = getPlanID('707')->id;
                                $planJeevan = getPlanID('713')->id;
                                $planRD = getPlanID('704')->id;
                                $planBhavhishya = getPlanID('718')->id;
                                $monthlyId = array($planKanyadhan, $planMB, $planFRD, $planJeevan, $planRD, $planBhavhishya);
                                $planMI = getPlanID('712')->id;
                                $planFFD = getPlanID('705')->id;
                                $planFD = getPlanID('706')->id;
                                $fdId = array($planMI, $planFFD, $planFD);
                                //$branch_id = ($request->branch_id != '') ? $request->branch_id : $row->branch_id;
                                $data[$key]['branch'] = $row['associate_branch']->name;
                                $data[$key]['branch_code'] = $row['associate_branch']->branch_code;
                                // $data[$key]['sector_name']=$row['associate_branch']->sector;
                                // $data[$key]['region_name']=$row['associate_branch']->regan;
                                // $data[$key]['zone_name']=$row['associate_branch']->zone;
                                // $data[$key]['member_id']=$row->associate_no;
                                $data[$key]['name'] = $row->first_name . ' ' . $row->last_name;
                                $data[$key]['associate_code'] = $row->associate_no;
                                // $data[$key]['cadre']=getCarderName($row->current_carder_id);
                                // $data[$key]['current_daily_new_ac']=investNewAcCount($associate_id,$current_start_date,$current_end_date,$planDaily,$branch_id);
                                $data[$key]['current_daily_deno_sum'] = investNewDenoSum($associate_id, $current_start_date, $current_end_date, $planDaily, $branch_id);
                                // $data[$key]['current_daily_renew_ac']=investRenewAc($associate_id,$current_start_date,$current_end_date,$dailyId,$branch_id);
                                // $data[$key]['current_daily_renew']=investRenewAmountSum($associate_id,$current_start_date,$current_end_date,$dailyId,$branch_id);
                                $current_daily_renew = investRenewAmountSum($associate_id, $current_start_date, $current_end_date, $dailyId, $branch_id);
                                // $data[$key]['current_monthly_new_ac']=investNewAcCountType($associate_id,$current_start_date,$current_end_date,$monthlyId,$branch_id);
                                $data[$key]['current_monthly_deno_sum'] = investNewDenoSumType($associate_id, $current_start_date, $current_end_date, $monthlyId, $branch_id);
                                // $data[$key]['current_monthly_renew_ac']=investRenewAc($associate_id,$current_start_date,$current_end_date,$monthlyId,$branch_id);
                                // $data[$key]['current_monthly_renew']=investRenewAmountSum($associate_id,$current_start_date,$current_end_date,$monthlyId,$branch_id);
                                $current_monthly_renew = investRenewAmountSum($associate_id, $current_start_date, $current_end_date, $monthlyId, $branch_id);
                                // $data[$key]['current_fd_new_ac']=investNewAcCountType($associate_id,$current_start_date,$current_end_date,$fdId,$branch_id);
                                $data[$key]['current_fd_deno_sum'] = investNewDenoSumType($associate_id, $current_start_date, $current_end_date, $fdId, $branch_id);
                                // $data[$key]['current_ssb_new_ac']=totalInvestSSbAcCountByType($associate_id,$current_start_date,$current_end_date,$branch_id,1);
                                // $data[$key]['current_ssb_deno_sum']=totalInvestSSbAmtByType($associate_id,$current_start_date,$current_end_date,$branch_id,1);
                                $current_ssb_deno_sum = totalInvestSSbAmtByType($associate_id, $current_start_date, $current_end_date, $branch_id, 1);
                                // $data[$key]['current_ssb_renew_ac']=totalInvestSSbAcCountByType($associate_id,$current_start_date,$current_end_date,$branch_id,2);
                                // $data[$key]['current_ssb_renew']=totalInvestSSbAmtByType($associate_id,$current_start_date,$current_end_date,$branch_id,2);
                                $current_ssb_renew = totalInvestSSbAmtByType($associate_id, $current_start_date, $current_end_date, $branch_id, 2);
                                // $current_sum_ni_ac=$data[$key]['current_daily_new_ac']+$data[$key]['current_monthly_new_ac']+$data[$key]['current_fd_new_ac']+$data[$key]['current_ssb_new_ac'];  
                                // $current_sum_ni_amount=$data[$key]['current_daily_deno_sum']+$data[$key]['current_monthly_deno_sum']+$data[$key]['current_fd_deno_sum']+$data[$key]['current_ssb_deno_sum'];
                                // $data[$key]['current_total_ni_ac']=$current_sum_ni_ac; 
                                // $data[$key]['current_total_ni_amount']=number_format((float)$current_sum_ni_amount, 2, '.', '');
                                // $current_sum_renew_ac=$data[$key]['current_daily_renew_ac']+$data[$key]['current_monthly_renew_ac'];  
                                $current_sum_renew_amount = $current_daily_renew + $current_monthly_renew;
                                // $data[$key]['current_total_ac']=$current_sum_renew_ac;
                                // $data[$key]['current_total_amount']=number_format((float)$current_sum_renew_amount, 2, '.', '');
                                $data[$key]['current_all_collection'] = number_format((float)$current_sum_renew_amount, 2, '.', '');
                                // $data[$key]['current_other_mt']=investOtherMiByType($associate_id,$current_start_date,$current_end_date,$branch_id,1,11);
                                // $data[$key]['current_other_stn']=investOtherMiByType($associate_id,$current_start_date,$current_end_date,$branch_id,1,12);              
                                // $current_ni_m=$data[$key]['current_daily_deno_sum']+$data[$key]['current_monthly_deno_sum']+$data[$key]['current_fd_deno_sum'];              
                                // $current_tcc_m=$data[$key]['current_daily_deno_sum']+$data[$key]['current_monthly_deno_sum']+$data[$key]['current_fd_deno_sum']+$data[$key]['current_daily_renew']+$data[$key]['current_monthly_renew'];               
                                $current_tcc = $data[$key]['current_daily_deno_sum'] + $data[$key]['current_monthly_deno_sum'] + $data[$key]['current_fd_deno_sum'] + $current_ssb_deno_sum + $current_daily_renew + $current_monthly_renew + $current_ssb_renew;
                                // $data[$key]['current_ni_m']=number_format((float)$current_ni_m, 2, '.', '');
                                // $data[$key]['current_ni']=number_format((float)$current_sum_ni_amount, 2, '.', '');
                                // $data[$key]['current_tcc_m']=number_format((float)$current_tcc_m, 2, '.', '');
                                $data[$key]['current_tcc'] = number_format((float)$current_tcc, 2, '.', '');
                                $data[$key]['current_ncc'] = $data[$key]['current_daily_deno_sum'] + $data[$key]['current_monthly_deno_sum'] + $data[$key]['current_fd_deno_sum'];
                                // $data[$key]['current_loan_ac']=totalLoanAc($associate_id,$current_start_date,$current_end_date,$branch_id);
                                // $data[$key]['current_loan_amount']=totalLoanAmount($associate_id,$current_start_date,$current_end_date,$branch_id);
                                // $data[$key]['current_loan_recovery_ac']=totalRenewLoanAc($associate_id,$current_start_date,$current_end_date,$branch_id);
                                $data[$key]['current_loan_recovery_amount'] = totalRenewLoanAmount($associate_id, $current_start_date, $current_end_date, $branch_id);
                                // $data[$key]['current_new_associate']=memberCountByType($associate_id,$current_start_date,$current_end_date,$branch_id,1,0);
                                // $data[$key]['current_total_associate']=memberCountByType($associate_id,$current_start_date,$current_end_date,$branch_id,1,1);
                                // $data[$key]['current_new_member']=memberCountByType($associate_id,$current_start_date,$current_end_date,$branch_id,0,0);
                                // $data[$key]['current_total_member']=memberCountByType($associate_id,$current_start_date,$current_end_date,$branch_id,0,1);                
                                // $data[$key]['compare_daily_new_ac']=investNewAcCount($associate_id,$comp_start_date,$comp_end_date,$planDaily,$branch_id);
                                $data[$key]['compare_daily_deno_sum'] = investNewDenoSum($associate_id, $comp_start_date, $comp_end_date, $planDaily, $branch_id);
                                // $data[$key]['compare_daily_renew_ac']=investRenewAc($associate_id,$comp_start_date,$comp_end_date,$dailyId,$branch_id);
                                // $data[$key]['compare_daily_renew']=investRenewAmountSum($associate_id,$comp_start_date,$comp_end_date,$dailyId,$branch_id);
                                $compare_daily_renew = investRenewAmountSum($associate_id, $comp_start_date, $comp_end_date, $dailyId, $branch_id);
                                // $data[$key]['compare_monthly_new_ac']=investNewAcCountType($associate_id,$comp_start_date,$comp_end_date,$monthlyId,$branch_id);
                                $data[$key]['compare_monthly_deno_sum'] = investNewDenoSumType($associate_id, $comp_start_date, $comp_end_date, $monthlyId, $branch_id);
                                // $data[$key]['compare_monthly_renew_ac']=investRenewAc($associate_id,$comp_start_date,$comp_end_date,$monthlyId,$branch_id);
                                // $data[$key]['compare_monthly_renew']=investRenewAmountSum($associate_id,$comp_start_date,$comp_end_date,$monthlyId,$branch_id);
                                $compare_monthly_renew = investRenewAmountSum($associate_id, $comp_start_date, $comp_end_date, $monthlyId, $branch_id);
                                // $data[$key]['compare_fd_new_ac']=investNewAcCountType($associate_id,$comp_start_date,$comp_end_date,$fdId,$branch_id);
                                $data[$key]['compare_fd_deno_sum'] = investNewDenoSumType($associate_id, $comp_start_date, $comp_end_date, $fdId, $branch_id);
                                // $data[$key]['compare_ssb_new_ac']=totalInvestSSbAcCountByType($associate_id,$comp_start_date,$comp_end_date,$branch_id,1);
                                // $data[$key]['compare_ssb_deno_sum']=totalInvestSSbAmtByType($associate_id,$comp_start_date,$comp_start_date,$branch_id,1);
                                $compare_ssb_deno_sum = totalInvestSSbAmtByType($associate_id, $comp_start_date, $comp_start_date, $branch_id, 1);
                                // $data[$key]['compare_ssb_renew_ac']=totalInvestSSbAcCountByType($associate_id,$comp_start_date,$comp_end_date,$branch_id,2);
                                // $data[$key]['compare_ssb_renew']=totalInvestSSbAmtByType($associate_id,$comp_start_date,$comp_end_date,$branch_id,2);
                                $compare_ssb_renew = totalInvestSSbAmtByType($associate_id, $comp_start_date, $comp_end_date, $branch_id, 2);
                                // $compare_sum_ni_ac=$data[$key]['compare_daily_new_ac']+$data[$key]['compare_monthly_new_ac']+$data[$key]['compare_fd_new_ac']+$data[$key]['compare_ssb_new_ac']; 
                                // $compare_sum_ni_amount=$data[$key]['compare_daily_deno_sum']+$data[$key]['compare_monthly_deno_sum']+$data[$key]['compare_fd_deno_sum']+$data[$key]['compare_ssb_deno_sum'];
                                // $data[$key]['compare_total_ni_ac']=$compare_sum_ni_ac; 
                                // $data[$key]['compare_total_ni_amount']=number_format((float)$compare_sum_ni_amount, 2, '.', '');
                                // $compare_sum_renew_ac=$data[$key]['compare_daily_renew_ac']+$data[$key]['compare_monthly_renew_ac'];  
                                $compare_sum_renew_amount = $compare_daily_renew + $compare_monthly_renew;
                                // $data[$key]['compare_total_ac']=$compare_sum_renew_ac;
                                // $data[$key]['compare_total_amount']=number_format((float)$compare_sum_renew_amount, 2, '.', '');
                                $data[$key]['compare_all_collection'] = number_format((float)$compare_sum_renew_amount, 2, '.', '');
                                // $data[$key]['compare_other_mt']=investOtherMiByType($associate_id,$comp_start_date,$comp_end_date,$branch_id,1,11);
                                // $data[$key]['compare_other_stn']=investOtherMiByType($associate_id,$comp_start_date,$comp_end_date,$branch_id,1,12);
                                // $compare_ni_m=$data[$key]['compare_daily_deno_sum']+$data[$key]['compare_monthly_deno_sum']+$data[$key]['compare_fd_deno_sum'];              
                                // $compare_tcc_m=$data[$key]['compare_daily_deno_sum']+$data[$key]['compare_monthly_deno_sum']+$data[$key]['compare_fd_deno_sum']+$data[$key]['compare_daily_renew']+$data[$key]['compare_monthly_renew'];            
                                $compare_tcc = $data[$key]['compare_daily_deno_sum'] + $data[$key]['compare_monthly_deno_sum'] + $data[$key]['compare_fd_deno_sum'] + $compare_ssb_deno_sum + $compare_daily_renew + $compare_monthly_renew + $compare_ssb_renew;
                                // $data[$key]['compare_ni_m']=number_format((float)$compare_ni_m, 2, '.', '');
                                // $data[$key]['compare_ni']=number_format((float)$compare_sum_ni_amount, 2, '.', '');
                                // $data[$key]['compare_tcc_m']=number_format((float)$compare_tcc_m, 2, '.', '');
                                $data[$key]['compare_tcc'] = number_format((float)$compare_tcc, 2, '.', '');
                                // $data[$key]['compare_loan_ac']=totalLoanAc($associate_id,$comp_start_date,$comp_end_date,$branch_id);
                                // $data[$key]['compare_loan_amount']=totalLoanAmount($associate_id,$comp_start_date,$comp_end_date,$branch_id);
                                // $data[$key]['compare_loan_recovery_ac']=totalRenewLoanAc($associate_id,$comp_start_date,$comp_end_date,$branch_id);
                                $data[$key]['compare_loan_recovery_amount'] = totalRenewLoanAmount($associate_id, $comp_start_date, $comp_end_date, $branch_id);
                                // $data[$key]['compare_new_associate']=memberCountByType($associate_id,$comp_start_date,$comp_end_date,$branch_id,1,0);
                                // $data[$key]['compare_total_associate']=memberCountByType($associate_id,$comp_start_date,$comp_end_date,$branch_id,1,1);
                                // $data[$key]['compare_new_member']=memberCountByType($associate_id,$comp_start_date,$comp_end_date,$branch_id,0,0);
                                // $data[$key]['compare_total_member']=memberCountByType($associate_id,$comp_start_date,$comp_end_date,$branch_id,0,1);
                                // $data[$key]['result_daily_new_ac']=$data[$key]['current_daily_new_ac']-$data[$key]['compare_daily_new_ac'];
                                $data[$key]['result_daily_deno_sum'] = $data[$key]['current_daily_deno_sum'] - $data[$key]['compare_daily_deno_sum'];
                                $data[$key]['compare_ncc'] = $data[$key]['compare_daily_deno_sum'] + $data[$key]['compare_monthly_deno_sum'] + $data[$key]['compare_fd_deno_sum'];
                                // $data[$key]['result_daily_renew_ac']=$data[$key]['current_daily_new_ac']-$data[$key]['compare_daily_new_ac'];
                                // $data[$key]['result_daily_renew']=$data[$key]['current_daily_new_ac']-$data[$key]['compare_daily_new_ac'];
                                // $data[$key]['result_monthly_new_ac']=$data[$key]['current_daily_new_ac']-$data[$key]['compare_daily_new_ac'];
                                $data[$key]['result_monthly_deno_sum'] = $data[$key]['current_monthly_deno_sum'] - $data[$key]['compare_monthly_deno_sum'];
                                // $data[$key]['result_monthly_renew_ac']=$data[$key]['current_monthly_renew_ac']-$data[$key]['compare_monthly_renew_ac'];
                                // $data[$key]['result_monthly_renew']=$data[$key]['current_monthly_renew']-$data[$key]['compare_monthly_renew'];
                                // $data[$key]['result_fd_new_ac']=$data[$key]['current_fd_new_ac']-$data[$key]['compare_fd_new_ac'];
                                $data[$key]['result_fd_deno_sum'] = $data[$key]['current_fd_deno_sum'] - $data[$key]['compare_fd_deno_sum'];
                                // $data[$key]['result_ssb_new_ac']=$data[$key]['current_ssb_new_ac']-$data[$key]['compare_ssb_new_ac'];
                                // $data[$key]['result_ssb_deno_sum']=$data[$key]['current_ssb_deno_sum']-$data[$key]['compare_ssb_deno_sum'];
                                // $data[$key]['result_ssb_renew_ac']=$data[$key]['current_ssb_renew_ac']-$data[$key]['compare_ssb_renew'];
                                // $data[$key]['result_ssb_renew']=$data[$key]['current_ssb_renew']-$data[$key]['compare_ssb_deno_sum'];
                                // $result_sum_ni_ac=$current_sum_ni_ac-$compare_sum_ni_ac;   
                                // $result_sum_ni_amount=$current_sum_ni_amount-$compare_sum_ni_amount;
                                // $data[$key]['result_total_ni_ac']=$result_sum_ni_ac; 
                                // $data[$key]['result_total_ni_amount']=number_format((float)$result_sum_ni_amount, 2, '.', '');
                                // $result_sum_renew_ac=$current_sum_renew_ac-$compare_sum_renew_ac;   
                                $result_sum_renew_amount = $current_sum_renew_amount - $compare_sum_renew_amount;
                                // $data[$key]['result_total_ac']=$result_sum_renew_ac;
                                // $data[$key]['result_total_amount']=number_format((float)$result_sum_renew_amount, 2, '.', '');
                                // $data[$key]['result_other_mt']=$data[$key]['current_other_mt']-$data[$key]['compare_other_mt'];
                                // $data[$key]['result_other_stn']=$data[$key]['current_other_stn']-$data[$key]['compare_other_stn'];
                                // $data[$key]['result_ni_m']=$data[$key]['current_ni_m']-$data[$key]['compare_ni_m'];
                                // $data[$key]['result_ni']=$data[$key]['current_ni']-$data[$key]['compare_ni'];
                                // $data[$key]['result_tcc_m']=$data[$key]['current_tcc_m']-$data[$key]['compare_tcc_m'];
                                $data[$key]['result_tcc'] = $data[$key]['current_tcc'] - $data[$key]['compare_tcc'];
                                // $data[$key]['result_loan_ac']=$data[$key]['current_loan_ac']-$data[$key]['compare_loan_ac'];
                                // $data[$key]['result_loan_amount']=$data[$key]['current_loan_amount']-$data[$key]['compare_loan_amount'];
                                // $data[$key]['result_loan_recovery_ac']=$data[$key]['current_loan_recovery_ac']-$data[$key]['compare_loan_recovery_ac'];
                                $data[$key]['result_loan_recovery_amount'] = $data[$key]['current_loan_recovery_amount'] - $data[$key]['compare_loan_recovery_amount'];
                                // $data[$key]['result_new_associate']=$data[$key]['current_new_associate']-$data[$key]['compare_new_associate'];
                                // $data[$key]['result_total_associate']=$data[$key]['current_total_associate']-$data[$key]['compare_total_associate'];
                                // $data[$key]['result_new_member']=$data[$key]['current_new_member']-$data[$key]['compare_new_member'];
                                // $data[$key]['result_total_member']=$data[$key]['current_total_member']-$data[$key]['compare_total_member']; 
                                $data[$key]['result_collection_all'] = $result_sum_renew_amount;
                                $ncc = $data[$key]['result_daily_deno_sum'] - $data[$key]['result_monthly_deno_sum'] - $data[$key]['result_fd_deno_sum'];
                                $data[$key]['result_ncc'] = $ncc;
                            }
                            $status   = "Success";
                            $code     = 200;
                            $messages = 'Associate Collection Compare Report !';
                            $result   = ['business_summary_report' => $data, 'total_count' => $count, 'record_count' => count($data)];
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
                        $messages = 'Please select all date. ';
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
        } catch (Exception $e) {
            $status = "Error";
            $code = 500;
            $messages = $e->getMessage();
            $result = '';
            $associate_status = 9;
            return response()->json(compact('status', 'code', 'messages', 'result', 'associate_status'), $code);
        }
    }
    public function companyname()
    {
        try {
            $company =  Companies::select('id', 'name', 'short_name')->where('status', '1')->where('delete', '0')->get();
            $rowReturn = array();
            foreach ($company as $key) {
                $val['id'] = $key->id;
                $val['name'] = $key->name;
                $val['short_name'] = $key->short_name;
                $rowReturn[] = $val;
            }
            $status   = "Success";
            $code     = 200;
            $messages = 'Company Listing';
            $result   = $rowReturn;
            return response()->json(compact('status', 'code', 'messages', 'result'), $code);
        } catch (Exception $e) {
            $status = "Error";
            $code = 500;
            $messages = $e->getMessage();
            $result = '';
            \Log::info($messages);
            return response()->json(compact('status', 'code', 'messages', 'result'), $code);
        }
    }
}