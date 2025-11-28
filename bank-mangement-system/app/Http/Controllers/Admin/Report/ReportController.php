<?php
namespace App\Http\Controllers\Admin\Report;

use App\Models\MemberIdProof;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Daybook;
use App\Models\Branch;
use App\Models\Transcation;
use App\Models\Memberinvestments;
use App\Models\Plans;
use App\Models\SavingAccount;
use App\Models\Member;
use App\Models\Memberloans;
use App\Models\LoanDayBooks;
use App\Models\Grouploans;
use App\Models\LoanEmisNew;
use Yajra\DataTables\DataTables;
use Carbon\Carbon;
use DB;
use App\Models\Loans;
use App\Services\ImageUpload;
use Illuminate\Support\Facades\Cache;

/*
    |---------------------------------------------------------------------------
    | Admin Panel -- Member Management MemberController
    |--------------------------------------------------------------------------
    |
    | This controller handles members all functionlity.
*/
class ReportController extends Controller
{
    /**
     * Create a new controller instance.
     * @return void
     */
    public function __construct()
    {
        // check user login or not
        $this->middleware('auth');
    }
    /**
     * Show Daybook report.
     * Route: /admin/report/daybook
     * Method: get
     * @return  array()  Response
     */
    public function index()
    {
        $data['title'] = 'Report | Daybook Report';
        $data['branch'] = Branch::where('status', 1)->get();
        $data['zone'] = Branch::where('status', 1)->distinct('zone')->get();
        return view('templates.admin.report.index', $data);
    }
    /**
     *  Associate Business detail.
     * Route: /admin/report/associate_business
     * Method: get
     * @return  array()  Response
     */
    public function associateBusinessReport()
    {
        if (check_my_permission(Auth::user()->id, "121") != "1") {
            return redirect()->route('admin.dashboard');
        }
        $data['title'] = 'Report | Associate Business Report';
        $data['branch'] = Branch::select('id', 'branch_code', 'name')->where('status', 1)->get();
        $data['zone'] = Branch::where('status', 1)->select('zone')->groupBy('zone')->get();
        return view('templates.admin.report.associate_business', $data);
    }
    /**
     * GetAssociate Business list
     * Route: ajax call from - /admin/report/associate_business
     * Method: Post
     * @param  \Illuminate\Http\Request  $request
     * @return JSON array
     */
    public function associateBusinessList(Request $request)
    {
        if ($request->ajax()) {
            $arrFormData = array();
            $arrFormData['start_date'] = $request->start_date;
            $arrFormData['end_date'] = $request->end_date;
            $arrFormData['branch_id'] = $request->branch_id;
            $arrFormData['is_search'] = $request->is_search;
            $arrFormData['zone'] = $request->zone;
            $arrFormData['region'] = $request->region;
            $arrFormData['sector'] = $request->sector;
            $arrFormData['associate_code'] = $request->associate_code;
            if ($arrFormData['start_date'] != '') {
                $startDate = date("Y-m-d", strtotime(convertDate($arrFormData['start_date'])));
            } else {
                $startDate = '';
            }
            if ($arrFormData['end_date'] != '') {
                $endDate = date("Y-m-d ", strtotime(convertDate($arrFormData['end_date'])));
            } else {
                $endDate = '';
            }
            $data = Member::select('id', 'associate_join_date', 'associate_no', 'first_name', 'last_name', 'current_carder_id', 'branch_id', 'associate_branch_id', 'created_at')->with([
                'associate_branch' => function ($q) {
                    $q->select('id', 'name', 'branch_code', 'sector', 'regan', 'zone');
                }
            ])->with([
                        'getCarderNameCustom' => function ($q) {
                            $q->select('id', 'name', 'short_name');
                        }
                    ])->where('member_id', '!=', '9999999')->where('is_associate', 1);
            if (Auth::user()->branch_id > 0) {
                $id = Auth::user()->branch_id;
                $data = $data->where('associate_branch_id', '=', $id);
            }
            if (isset($arrFormData['is_search']) && $arrFormData['is_search'] == 'yes') {
                if (isset($arrFormData['branch_id']) && $arrFormData['branch_id'] != '') {
                    $branch_id = $arrFormData['branch_id'];
                    $data = $data->where('associate_branch_id', '=', $branch_id);
                }
                if ($arrFormData['zone'] != '') {
                    $zone = $arrFormData['zone'];
                    $data = $data->whereHas('associate_branch', function ($query) use ($zone) {
                        $query->where('branch.zone', $zone);
                    });
                }
                if ($arrFormData['region'] != '') {
                    $region = $arrFormData['region'];
                    $data = $data->whereHas('associate_branch', function ($query) use ($region) {
                        $query->where('branch.regan', $region);
                    });
                }
                if ($arrFormData['sector'] != '') {
                    $sector = $arrFormData['sector'];
                    $data = $data->whereHas('associate_branch', function ($query) use ($sector) {
                        $query->where('branch.sector', $sector);
                    });
                }
                if ($arrFormData['associate_code'] != '') {
                    $associate_code = $arrFormData['associate_code'];
                    $data = $data->where('associate_no', '=', $associate_code);
                }
                if ($arrFormData['start_date'] != '') {
                    $startDate = date("Y-m-d", strtotime(convertDate($arrFormData['start_date'])));
                    if ($arrFormData['end_date'] != '') {
                        $endDate = date("Y-m-d ", strtotime(convertDate($arrFormData['end_date'])));
                    } else {
                        $endDate = '';
                    }
                }
            } else {
                $startDate = date('Y-m-d', strtotime($request->associate_report_currentdate));
                $endDate = date('Y-m-d', strtotime($request->associate_report_currentdate));
            }
            //$data1=$data->orderby('associate_join_date','ASC')->get();
            //$count=count($data1);
            $count = $data->count('id');
            $totalCount = $count;
            $data = $data->orderby('associate_join_date', 'ASC')->offset($_POST['start'])->limit($_POST['length'])->get();
            //$dataCount = Member::where('member_id','!=','9999999')->where('is_associate',1);
            // if(Auth::user()->branch_id>0){
            //     $dataCount=$dataCount->where('associate_branch_id','=',Auth::user()->branch_id);
            //    }
            $sno = $_POST['start'];
            $rowReturn = array();
            //$branch_id = $id;
            //$customplan = getPlanIDCustom();
            foreach ($data as $row) {
                if (!empty($branch_id)) {
                    $bID = $branch_id;
                } else {
                    $bID = '';
                }
                $sno++;
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
                $val['DT_RowIndex'] = $sno;
                $val['join_date'] = date("d/m/Y", strtotime($row->associate_join_date));
                $val['branch'] = $row['associate_branch']->name;
                $val['branch_code'] = $row['associate_branch']->branch_code;
                $val['sector_name'] = $row['associate_branch']->sector;
                $val['region_name'] = $row['associate_branch']->regan;
                $val['zone_name'] = $row['associate_branch']->zone;
                $val['member_id'] = $row->associate_no;
                $val['name'] = $row->first_name . ' ' . $row->last_name;
                $val['cadre'] = $row['getCarderNameCustom']->name . '(' . $row['getCarderNameCustom']->short_name . ')'; //getCarderName($row->current_carder_id);
                $val['daily_new_ac'] = investNewAcCount($associate_id, $startDate, $endDate, $planDaily, $bID);
                $val['daily_deno_sum'] = investNewDenoSum($associate_id, $startDate, $endDate, $planDaily, $bID);
                $val['daily_renew_ac'] = investRenewAc($associate_id, $startDate, $endDate, $dailyId, $bID);
                $val['daily_renew'] = investRenewAmountSum($associate_id, $startDate, $endDate, $dailyId, $bID);
                $val['monthly_new_ac'] = investNewAcCountType($associate_id, $startDate, $endDate, $monthlyId, $bID);
                $val['monthly_deno_sum'] = investNewDenoSumType($associate_id, $startDate, $endDate, $monthlyId, $bID);
                $val['monthly_renew_ac'] = investRenewAc($associate_id, $startDate, $endDate, $monthlyId, $bID);
                $val['monthly_renew'] = investRenewAmountSum($associate_id, $startDate, $endDate, $monthlyId, $bID);
                $val['fd_new_ac'] = investNewAcCountType($associate_id, $startDate, $endDate, $fdId, $bID);
                $val['fd_deno_sum'] = investNewDenoSumType($associate_id, $startDate, $endDate, $fdId, $bID);
                // $val['fd_renew_ac']=investRenewAc($associate_id,$startDate,$endDate,$fdId,$branch_id);
                // $val['fd_renew']=investRenewAmountSum($associate_id,$startDate,$endDate,$fdId,$branch_id);
                $val['ssb_new_ac'] = totalInvestSSbAcCountByType($associate_id, $startDate, $endDate, $bID, 1);
                $val['ssb_deno_sum'] = totalInvestSSbAmtByType($associate_id, $startDate, $endDate, $bID, 1);
                $val['ssb_renew_ac'] = totalInvestSSbAcCountByType($associate_id, $startDate, $endDate, $bID, 2);
                $val['ssb_renew'] = totalInvestSSbAmtByType($associate_id, $startDate, $endDate, $bID, 2);
                $sum_ni_ac = $val['daily_new_ac'] + $val['monthly_new_ac'] + $val['fd_new_ac'] + $val['ssb_new_ac'];
                $sum_ni_amount = $val['daily_deno_sum'] + $val['monthly_deno_sum'] + $val['fd_deno_sum'] + $val['ssb_deno_sum'];
                $val['total_ni_ac'] = $sum_ni_ac;
                $val['total_ni_amount'] = number_format((float) $sum_ni_amount, 2, '.', '');
                $sum_renew_ac = $val['daily_renew_ac'] + $val['monthly_renew_ac'] + $val['ssb_renew_ac'];
                $sum_renew_amount = $val['daily_renew'] + $val['monthly_renew'] + $val['ssb_renew'];
                $val['total_ac'] = $sum_renew_ac;
                $val['total_amount'] = number_format((float) $sum_renew_amount, 2, '.', '');
                $val['other_mt'] = investOtherMiByType($associate_id, $startDate, $endDate, $bID, 1, 11);
                $val['other_stn'] = investOtherMiByType($associate_id, $startDate, $endDate, $bID, 1, 12);
                $ni_m = $val['daily_deno_sum'] + $val['monthly_deno_sum'] + $val['fd_deno_sum'];
                // dd($val['daily_deno_sum'],$val['monthly_deno_sum'],$val['fd_deno_sum'],$val['daily_renew'],$val['monthly_renew']);
                $tcc_m = $val['daily_deno_sum'] + $val['monthly_deno_sum'] + $val['fd_deno_sum'] + $val['daily_renew'] + $val['monthly_renew'];
                $tcc = $val['daily_deno_sum'] + $val['monthly_deno_sum'] + $val['fd_deno_sum'] + $val['ssb_deno_sum'] + $val['daily_renew'] + $val['monthly_renew'] + $val['ssb_renew'];
                $val['ni_m'] = number_format((float) $ni_m, 2, '.', '');
                $val['ni'] = number_format((float) $sum_ni_amount, 2, '.', '');
                $val['tcc_m'] = number_format((float) $tcc_m, 2, '.', '');
                $val['tcc'] = number_format((float) $tcc, 2, '.', '');
                $val['loan_ac'] = totalLoanAc($associate_id, $startDate, $endDate, $bID);
                $val['loan_amount'] = totalLoanAmount($associate_id, $startDate, $endDate, $bID);
                $val['loan_recovery_ac'] = totalRenewLoanAc($associate_id, $startDate, $endDate, $bID);
                $val['loan_recovery_amount'] = totalRenewLoanAmount($associate_id, $startDate, $endDate, $bID);
                $val['new_associate'] = memberCountByType($associate_id, $startDate, $endDate, $bID, 1, 0);
                $val['total_associate'] = memberCountByType($associate_id, $startDate, $endDate, $bID, 1, 1);
                $val['new_member'] = memberCountByType($associate_id, $startDate, $endDate, $bID, 0, 0);
                $val['total_member'] = memberCountByType($associate_id, $startDate, $endDate, $bID, 0, 1);
                $rowReturn[] = $val;
            }
            // print_r($rowReturn);die;
            $output = array("draw" => $_POST['draw'], "recordsTotal" => $totalCount, "recordsFiltered" => $count, "data" => $rowReturn, );
            return json_encode($output);
        }
    }
    /**
     * Associate Business  summary detail.
     * Route: /admin/report/associate_business_summary
     * Method: get
     * @return  array()  Response
     */
    public function associateBusinessSummaryReport()
    {
        if (check_my_permission(Auth::user()->id, "122") != "1") {
            return redirect()->route('admin.dashboard');
        }
        $data['title'] = 'Report | Associate Business Summary Report';
        $data['branch'] = Branch::select('id', 'branch_code', 'name')->where('status', 1)->get();
        $data['zone'] = Branch::select('id', 'zone')->where('status', 1)->select('zone')->groupBy('zone')->get();
        return view('templates.admin.report.associate_business_summary', $data);
    }
    /**
     * Associate Business summary  list
     * Route: ajax call from - /admin/report/associate_business_summary
     * Method: Post
     * @param  \Illuminate\Http\Request  $request
     * @return JSON array
     */
    public function associateBusinessSummaryList(Request $request)
    {
        if ($request->ajax()) {
            $arrFormData = array();
            $arrFormData['start_date'] = $request->start_date;
            $arrFormData['end_date'] = $request->end_date;
            $arrFormData['branch_id'] = $request->branch_id;
            $arrFormData['is_search'] = $request->is_search;
            $arrFormData['zone'] = $request->zone;
            $arrFormData['region'] = $request->region;
            $arrFormData['sector'] = $request->sector;
            $arrFormData['associate_code'] = $request->associate_code;
            if ($arrFormData['start_date'] != '') {
                $startDate = date("Y-m-d", strtotime(convertDate($arrFormData['start_date'])));
            } else {
                $startDate = '';
            }
            if ($arrFormData['end_date'] != '') {
                $endDate = date("Y-m-d ", strtotime(convertDate($arrFormData['end_date'])));
            } else {
                $endDate = '';
            }
            if ($arrFormData['branch_id'] != '') {
                $branch_id = $arrFormData['branch_id'];
            } else {
                $branch_id = '';
            }
            $data = Member::select('id', 'associate_join_date', 'associate_no', 'first_name', 'last_name', 'current_carder_id', 'associate_branch_id', 'created_at')
                ->with([
                    'associate_branch' => function ($q) {
                        $q->select('id', 'name', 'branch_code', 'sector', 'regan', 'zone');
                    }
                ])
                ->with([
                    'getCarderNameCustom' => function ($q) {
                        $q->select('id', 'name', 'short_name');
                    }
                ])
                ->where('member_id', '!=', '9999999')->where('is_associate', 1);
            if (Auth::user()->branch_id > 0) {
                $id = Auth::user()->branch_id;
                $data = $data->where('associate_branch_id', '=', $id);
            }
            if (isset($arrFormData['is_search']) && $arrFormData['is_search'] == 'yes') {
                if (isset($arrFormData['branch_id']) && $arrFormData['branch_id'] != '') {
                    $id = $arrFormData['branch_id'];
                    $data = $data->where('associate_branch_id', '=', $id);
                }
                if ($arrFormData['zone'] != '') {
                    $zone = $arrFormData['zone'];
                    $data = $data->whereHas('associate_branch', function ($query) use ($zone) {
                        $query->where('branch.zone', $zone);
                    });
                }
                if ($arrFormData['region'] != '') {
                    $region = $arrFormData['region'];
                    $data = $data->whereHas('associate_branch', function ($query) use ($region) {
                        $query->where('branch.regan', $region);
                    });
                }
                if ($arrFormData['sector'] != '') {
                    $sector = $arrFormData['sector'];
                    $data = $data->whereHas('associate_branch', function ($query) use ($sector) {
                        $query->where('branch.sector', $sector);
                    });
                }
                if ($arrFormData['associate_code'] != '') {
                    $associate_code = $arrFormData['associate_code'];
                    $data = $data->where('associate_no', '=', $associate_code);
                }
                if ($arrFormData['start_date'] != '') {
                    $startDate = date("Y-m-d", strtotime(convertDate($arrFormData['start_date'])));
                    if ($arrFormData['end_date'] != '') {
                        $endDate = date("Y-m-d ", strtotime(convertDate($arrFormData['end_date'])));
                    } else {
                        $endDate = '';
                    }
                }
            } else {
                $startDate = date('Y-m-d', strtotime($request->associate_report_currentdate));
                $endDate = date('Y-m-d', strtotime($request->associate_report_currentdate));
            }
            $count = $data->count('id');
            $data = $data->orderby('associate_join_date', 'ASC')->offset($_POST['start'])->limit($_POST['length'])->get();
            $dataCount = Member::where('member_id', '!=', '9999999')->where('is_associate', 1);
            if (Auth::user()->branch_id > 0) {
                $dataCount = $dataCount->where('associate_branch_id', '=', Auth::user()->branch_id);
            }
            $totalCount = $dataCount->count('id');
            $sno = $_POST['start'];
            $rowReturn = array();
            $AllplanCods = getPlanIDCustom();
            foreach ($data as $row) {
                $sno++;
                $associate_id = $row->id;
                $planDaily = $AllplanCods['710'];
                $planSSB = $AllplanCods['703'];
                $planKanyadhan = $AllplanCods['709'];
                $planMB = $AllplanCods['708'];
                $planFFD = $AllplanCods['705'];
                $planFRD = $AllplanCods['707'];
                $planJeevan = $AllplanCods['713'];
                $planMI = $AllplanCods['712'];
                $planFD = $AllplanCods['706'];
                $planRD = $AllplanCods['704'];
                $planBhavhishya = $AllplanCods['718'];
                $planids = array($planDaily, $planSSB, $planKanyadhan, $planMB, $planFFD, $planFRD, $planJeevan, $planMI, $planFD, $planRD, $planBhavhishya, );
                $val['DT_RowIndex'] = $sno;
                $val['join_date'] = date("d/m/Y", strtotime($row->associate_join_date));
                $val['branch'] = $row['associate_branch']->name;
                $val['branch_code'] = $row['associate_branch']->branch_code;
                $val['sector_name'] = $row['associate_branch']->sector;
                $val['region_name'] = $row['associate_branch']->regan;
                $val['zone_name'] = $row['associate_branch']->zone;
                $val['member_id'] = $row->associate_no;
                $val['name'] = $row->first_name . ' ' . $row->last_name;
                $val['cadre'] = $row['getCarderNameCustom']->name . '(' . $row['getCarderNameCustom']->short_name . ')';
                $val['daily_new_ac'] = investNewAcCount($associate_id, $startDate, $endDate, $planDaily, $branch_id);
                $val['daily_deno_sum'] = investNewDenoSum($associate_id, $startDate, $endDate, $planDaily, $branch_id);
                $val['daily_renew_ac'] = investRenewAcPlan($associate_id, $startDate, $endDate, $planDaily, $branch_id);
                $val['daily_renew'] = investRenewAmountSumPlan($associate_id, $startDate, $endDate, $planDaily, $branch_id);
                $val['ssb_new_ac'] = totalInvestSSbAcCountByType($associate_id, $startDate, $endDate, $branch_id, 1);
                $val['ssb_deno_sum'] = totalInvestSSbAmtByType($associate_id, $startDate, $endDate, $branch_id, 1);
                $val['ssb_renew_ac'] = totalInvestSSbAcCountByType($associate_id, $startDate, $endDate, $branch_id, 2);
                $val['ssb_renew'] = totalInvestSSbAmtByType($associate_id, $startDate, $endDate, $branch_id, 2);
                $val['kanyadhan_new_ac'] = investNewAcCount($associate_id, $startDate, $endDate, $planKanyadhan, $branch_id);
                $val['kanyadhan_deno_sum'] = investNewDenoSum($associate_id, $startDate, $endDate, $planKanyadhan, $branch_id);
                $val['kanyadhan_renew_ac'] = investRenewAcPlan($associate_id, $startDate, $endDate, $planKanyadhan, $branch_id);
                $val['kanyadhan_renew'] = investRenewAmountSumPlan($associate_id, $startDate, $endDate, $planKanyadhan, $branch_id);
                $val['mb_new_ac'] = investNewAcCount($associate_id, $startDate, $endDate, $planMB, $branch_id);
                $val['mb_deno_sum'] = investNewDenoSum($associate_id, $startDate, $endDate, $planMB, $branch_id);
                $val['mb_renew_ac'] = investRenewAcPlan($associate_id, $startDate, $endDate, $planMB, $branch_id);
                $val['mb_renew'] = investRenewAmountSumPlan($associate_id, $startDate, $endDate, $planMB, $branch_id);
                $val['ffd_new_ac'] = investNewAcCount($associate_id, $startDate, $endDate, $planFFD, $branch_id);
                $val['ffd_deno_sum'] = investNewDenoSum($associate_id, $startDate, $endDate, $planFFD, $branch_id);
                $val['frd_new_ac'] = investNewAcCount($associate_id, $startDate, $endDate, $planFRD, $branch_id);
                $val['frd_deno_sum'] = investNewDenoSum($associate_id, $startDate, $endDate, $planFRD, $branch_id);
                $val['frd_renew_ac'] = investRenewAcPlan($associate_id, $startDate, $endDate, $planFRD, $branch_id);
                $val['frd_renew'] = investRenewAmountSumPlan($associate_id, $startDate, $endDate, $planFRD, $branch_id);
                $val['jeevan_new_ac'] = investNewAcCount($associate_id, $startDate, $endDate, $planJeevan, $branch_id);
                $val['jeevan_deno_sum'] = investNewDenoSum($associate_id, $startDate, $endDate, $planJeevan, $branch_id);
                $val['jeevan_renew_ac'] = investRenewAcPlan($associate_id, $startDate, $endDate, $planJeevan, $branch_id);
                ;
                $val['jeevan_renew'] = investRenewAmountSumPlan($associate_id, $startDate, $endDate, $planJeevan, $branch_id);
                $val['mi_new_ac'] = investNewAcCount($associate_id, $startDate, $endDate, $planMI, $branch_id);
                $val['mi_deno_sum'] = investNewDenoSum($associate_id, $startDate, $endDate, $planMI, $branch_id);
                $val['mi_renew_ac'] = investRenewAcPlan($associate_id, $startDate, $endDate, $planMI, $branch_id);
                ;
                $val['mi_renew'] = investRenewAmountSumPlan($associate_id, $startDate, $endDate, $planMI, $branch_id);
                $val['fd_new_ac'] = investNewAcCount($associate_id, $startDate, $endDate, $planFD, $branch_id);
                $val['fd_deno_sum'] = investNewDenoSum($associate_id, $startDate, $endDate, $planFD, $branch_id);
                $val['rd_new_ac'] = investNewAcCount($associate_id, $startDate, $endDate, $planRD, $branch_id);
                $val['rd_deno_sum'] = investNewDenoSum($associate_id, $startDate, $endDate, $planRD, $branch_id);
                $val['rd_renew_ac'] = investRenewAcPlan($associate_id, $startDate, $endDate, $planRD, $branch_id);
                $val['rd_renew'] = investRenewAmountSumPlan($associate_id, $startDate, $endDate, $planRD, $branch_id);
                $val['bhavhishya_new_ac'] = investNewAcCount($associate_id, $startDate, $endDate, $planBhavhishya, $branch_id);
                $val['bhavhishya_deno_sum'] = investNewDenoSum($associate_id, $startDate, $endDate, $planBhavhishya, $branch_id);
                $val['bhavhishya_renew_ac'] = investRenewAcPlan($associate_id, $startDate, $endDate, $planBhavhishya, $branch_id);
                $val['bhavhishya_renew'] = investRenewAmountSumPlan($associate_id, $startDate, $endDate, $planBhavhishya, $branch_id);
                $sum_ni_ac = $val['daily_new_ac'] + $val['ssb_new_ac'] + $val['kanyadhan_new_ac'] + $val['mb_new_ac'] + $val['ffd_new_ac'] + $val['frd_new_ac'] + $val['jeevan_new_ac'] + $val['mi_new_ac'] + $val['fd_new_ac'] + $val['rd_new_ac'] + $val['bhavhishya_new_ac'];
                $sum_ni_amount = $val['daily_deno_sum'] + $val['ssb_deno_sum'] + $val['kanyadhan_deno_sum'] + $val['mb_deno_sum'] + $val['ffd_deno_sum'] + $val['frd_deno_sum'] + $val['jeevan_deno_sum'] + $val['mi_deno_sum'] + $val['fd_deno_sum'] + $val['rd_deno_sum'] + $val['bhavhishya_deno_sum'];
                $sum_renew_ac = investRenewAc($associate_id, $startDate, $endDate, $planids, $branch_id);
                $sum_renew_amount = investRenewAmountSum($associate_id, $startDate, $planids, $planids, $branch_id);
                $val['total_ni_ac'] = $sum_ni_ac;
                $val['total_ni_amount'] = number_format((float) $sum_ni_amount, 2, '.', '');
                $val['total_ac'] = $sum_renew_ac;
                $val['total_amount'] = number_format((float) $sum_renew_amount, 2, '.', '');
                $val['other_mt'] = investOtherMiByType($associate_id, $startDate, $endDate, $branch_id, 1, 11);
                $val['other_stn'] = investOtherMiByType($associate_id, $startDate, $endDate, $branch_id, 1, 12);
                $ni_m = $val['daily_deno_sum'] + $val['kanyadhan_deno_sum'] + $val['mb_deno_sum'] + $val['ffd_deno_sum'] + $val['frd_deno_sum'] + $val['jeevan_deno_sum'] + $val['mi_deno_sum'] + $val['fd_deno_sum'] + $val['rd_deno_sum'] + $val['bhavhishya_deno_sum'];
                $tcc_m = $val['daily_deno_sum'] + $val['kanyadhan_deno_sum'] + $val['mb_deno_sum'] + $val['ffd_deno_sum'] + $val['frd_deno_sum'] + $val['jeevan_deno_sum'] + $val['mi_deno_sum'] + $val['fd_deno_sum'] + $val['rd_deno_sum'] + $val['bhavhishya_deno_sum'] + $val['bhavhishya_renew'] + $val['rd_renew'] + $val['mi_renew'] + $val['jeevan_renew'] + $val['frd_renew'] + $val['mb_renew'] + $val['kanyadhan_renew'] + $val['daily_renew'];
                $tcc = $val['daily_deno_sum'] + $val['kanyadhan_deno_sum'] + $val['mb_deno_sum'] + $val['ffd_deno_sum'] + $val['frd_deno_sum'] + $val['jeevan_deno_sum'] + $val['mi_deno_sum'] + $val['fd_deno_sum'] + $val['rd_deno_sum'] + $val['bhavhishya_deno_sum'] + $val['ssb_deno_sum'] + $val['bhavhishya_renew'] + $val['rd_renew'] + $val['mi_renew'] + $val['jeevan_renew'] + $val['frd_renew'] + $val['mb_renew'] + $val['kanyadhan_renew'] + $val['ssb_renew'] + $val['daily_renew'];
                $val['ni_m'] = number_format((float) $ni_m, 2, '.', '');
                $val['ni'] = number_format((float) $sum_ni_amount, 2, '.', '');
                $val['tcc_m'] = number_format((float) $tcc_m, 2, '.', '');
                $val['tcc'] = number_format((float) $tcc, 2, '.', '');
                $val['st_loan_ac'] = associateLoanTypeAC($associate_id, $startDate, $endDate, $branch_id, 2);
                $val['st_loan_amount'] = associateLoanTypeAmount($associate_id, $startDate, $endDate, $branch_id, 2);
                $val['pl_loan_ac'] = associateLoanTypeAC($associate_id, $startDate, $endDate, $branch_id, 1);
                $val['pl_loan_amount'] = associateLoanTypeAmount($associate_id, $startDate, $endDate, $branch_id, 1);
                $val['la_loan_ac'] = associateLoanTypeAC($associate_id, $startDate, $endDate, $branch_id, 4);
                $val['la_loan_amount'] = associateLoanTypeAmount($associate_id, $startDate, $endDate, $branch_id, 4);
                $val['gp_loan_ac'] = associateLoanTypeAC($associate_id, $startDate, $endDate, $branch_id, 3);
                $val['gp_loan_amount'] = associateLoanTypeAmount($associate_id, $startDate, $endDate, $branch_id, 3);
                $val['loan_ac'] = $val['st_loan_ac'] + $val['pl_loan_ac'] + $val['la_loan_ac'] + $val['gp_loan_ac'];
                $val['loan_amount'] = $val['st_loan_amount'] + $val['pl_loan_amount'] + $val['la_loan_amount'] + $val['gp_loan_amount'];
                $val['st_loan_recovery_ac'] = associateLoanTypeRecoverAc($associate_id, $startDate, $endDate, $branch_id, 2);
                $val['st_loan_recovery_amount'] = associateLoanTypeRecoverAmount($associate_id, $startDate, $endDate, $branch_id, 2);
                $val['pl_loan_recovery_ac'] = associateLoanTypeRecoverAc($associate_id, $startDate, $endDate, $branch_id, 1);
                $val['pl_loan_recovery_amount'] = associateLoanTypeRecoverAmount($associate_id, $startDate, $endDate, $branch_id, 1);
                $val['la_loan_recovery_ac'] = associateLoanTypeRecoverAc($associate_id, $startDate, $endDate, $branch_id, 4);
                $val['la_loan_recovery_amount'] = associateLoanTypeRecoverAmount($associate_id, $startDate, $endDate, $branch_id, 4);
                $val['gp_loan_recovery_ac'] = associateLoanTypeRecoverAc($associate_id, $startDate, $endDate, $branch_id, 3);
                $val['gp_loan_recovery_amount'] = associateLoanTypeRecoverAmount($associate_id, $startDate, $endDate, $branch_id, 3);
                $val['loan_recovery_ac'] = $val['st_loan_recovery_ac'] + $val['pl_loan_recovery_ac'] + $val['la_loan_recovery_ac'] + $val['gp_loan_recovery_ac'];
                $val['loan_recovery_amount'] = $val['st_loan_recovery_amount'] + $val['pl_loan_recovery_amount'] + $val['la_loan_recovery_amount'] + $val['gp_loan_recovery_amount'];
                $val['new_associate'] = memberCountByType($associate_id, $startDate, $endDate, $branch_id, 1, 0);
                $val['total_associate'] = memberCountByType($associate_id, $startDate, $endDate, $branch_id, 1, 1);
                $val['new_member'] = memberCountByType($associate_id, $startDate, $endDate, $branch_id, 0, 0);
                $val['total_member'] = memberCountByType($associate_id, $startDate, $endDate, $branch_id, 0, 1);
                $rowReturn[] = $val;
            }
            $output = array("draw" => $_POST['draw'], "recordsTotal" => $totalCount, "recordsFiltered" => $count, "data" => $rowReturn, );
            return json_encode($output);
        }
    }
    /* public function associateBusinessSummaryReport()
    {
        if (check_my_permission(Auth::user()->id, "122") != "1") {
            return redirect()->route('admin.dashboard');
        }
        $data['title'] = 'Report | Associate Business Summary Report';
        $data['branch'] = Branch::select('id', 'branch_code', 'name')->where('status', 1)->get();
        $data['zone'] = Branch::select('id', 'zone')->where('status', 1)->select('zone')->groupBy('zone')->get();
        return view('templates.admin.report.associate_business_report', $data);
    }*/
    /**
     * Associate Business Compare detail.
     * Route: /admin/report/associate_business_compare
     * Method: get
     * @return  array()  Response
     */
    public function associateBusinessCompareReport()
    {
        /*
      if(check_my_permission( Auth::user()->id,"123") != "1"){
          return redirect()->route('admin.dashboard');
        }
 */
        //  echo date('d/m/Y',strtotime('first day of -1 months')) ;die;
        $data['title'] = 'Report | Associate Business Compare Report';
        $data['branch'] = Branch::select('id', 'branch_code', 'name')->where('status', 1)->get();
        $data['zone'] = Branch::where('status', 1)->select('zone')->groupBy('zone')->get();
        $data['current_from'] = date('d/m/Y', strtotime('first day of -1 months'));
        $data['current_to'] = date('d/m/Y', strtotime('last day of -1 months'));
        $data['comp_from'] = date('d/m/Y', strtotime('first day of -2 months'));
        $data['comp_to'] = date('d/m/Y', strtotime('last day of -2 months'));
        return view('templates.admin.report.associate_business_compare', $data);
    }
    /**
     * Associate Business Compare  list
     * Route: ajax call from - /admin/report/associate_business
     * Method: Post
     * @param  \Illuminate\Http\Request  $request
     * @return JSON array
     */
    public function associateBusinessCompareList(Request $request)
    {
        if ($request->ajax()) {
            // fillter array
            $arrFormData = array();
            //
            $arrFormData['current_start_date'] = $request->current_start_date;
            $arrFormData['current_end_date'] = $request->current_end_date;
            $arrFormData['comp_start_date'] = $request->comp_start_date;
            $arrFormData['comp_end_date'] = $request->comp_end_date;
            $arrFormData['branch_id'] = $request->branch_id;
            $arrFormData['is_search'] = $request->is_search;
            $arrFormData['zone'] = $request->zone;
            $arrFormData['region'] = $request->region;
            $arrFormData['sector'] = $request->sector;
            $arrFormData['associate_code'] = $request->associate_code;
            if ($arrFormData['current_start_date'] != '') {
                $current_start_date = date("Y-m-d", strtotime(convertDate($arrFormData['current_start_date'])));
            } else {
                $current_start_date = '';
            }
            if ($arrFormData['current_end_date'] != '') {
                $current_end_date = date("Y-m-d ", strtotime(convertDate($arrFormData['current_end_date'])));
            } else {
                $current_end_date = '';
            }
            if ($arrFormData['comp_start_date'] != '') {
                $comp_start_date = date("Y-m-d", strtotime(convertDate($arrFormData['comp_start_date'])));
            } else {
                $comp_start_date = '';
            }
            if ($arrFormData['comp_end_date'] != '') {
                $comp_end_date = date("Y-m-d ", strtotime(convertDate($arrFormData['comp_end_date'])));
            } else {
                $comp_end_date = '';
            }
            if ($arrFormData['branch_id'] != '') {
                $branch_id = $arrFormData['branch_id'];
            } else {
                $branch_id = '';
            }
            $data = Member::select('id', 'associate_branch_id', 'associate_join_date', 'associate_no', 'first_name', 'last_name', 'current_carder_id')
                ->with([
                    'associate_branch' => function ($q) {
                        $q->select('id', 'name', 'branch_code', 'sector', 'regan', 'zone');
                    }
                ])
                ->with([
                    'getCarderNameCustom' => function ($q) {
                        $q->select('id', 'name', 'short_name');
                    }
                ])
                ->where('member_id', '!=', '9999999')->where('is_associate', 1);
            if (Auth::user()->branch_id > 0) {
                $id = Auth::user()->branch_id;
                $data = $data->where('associate_branch_id', '=', $id);
            }
            /******* fillter query start ****/
            if (isset($arrFormData['is_search']) && $arrFormData['is_search'] == 'yes') {
                if (isset($arrFormData['branch_id']) && $arrFormData['branch_id'] != '') {
                    $id = $arrFormData['branch_id'];
                    $data = $data->where('associate_branch_id', '=', $id);
                }
                if ($arrFormData['zone'] != '') {
                    $zone = $arrFormData['zone'];
                    $data = $data->whereHas('associate_branch', function ($query) use ($zone) {
                        $query->where('branch.zone', $zone);
                    });
                }
                if ($arrFormData['region'] != '') {
                    $region = $arrFormData['region'];
                    $data = $data->whereHas('associate_branch', function ($query) use ($region) {
                        $query->where('branch.regan', $region);
                    });
                }
                if ($arrFormData['sector'] != '') {
                    $sector = $arrFormData['sector'];
                    $data = $data->whereHas('associate_branch', function ($query) use ($sector) {
                        $query->where('branch.sector', $sector);
                    });
                }
                if ($arrFormData['associate_code'] != '') {
                    $associate_code = $arrFormData['associate_code'];
                    $data = $data->where('associate_no', '=', $associate_code);
                }
                /*
                if($arrFormData['name'] !=''){
                    $name =$arrFormData['name'];
                 $data=$data->where(function ($query) use ($name) { $query->where('first_name','LIKE','%'.$name.'%')->orWhere('last_name','LIKE','%'.$name.'%')->orWhere(DB::raw('concat(first_name," ",last_name)') , 'LIKE' , "%$name%"); });
                }*/
            }
            /******* fillter query End ****/
            $count = $data->count('id');
            $data = $data->orderby('associate_join_date', 'ASC')->offset($_POST['start'])->limit($_POST['length'])->get();
            $dataCount = Member::where('member_id', '!=', '9999999')->where('is_associate', 1);
            if (Auth::user()->branch_id > 0) {
                $dataCount = $dataCount->where('associate_branch_id', '=', Auth::user()->branch_id);
            }
            $totalCount = $dataCount->count();
            $sno = $_POST['start'];
            $rowReturn = array();
            $customplan = getPlanIDCustom();
            foreach ($data as $row) {
                $sno++;
                $associate_id = $row->id;
                $planDaily = $customplan['710']; //getPlanID('710')->id;
                $dailyId = array($planDaily);
                $planSSB = $customplan['703']; //getPlanID('703')->id;
                $planKanyadhan = $customplan['709']; //getPlanID('709')->id;
                $planMB = $customplan['708']; //getPlanID('708')->id;
                $planFRD = $customplan['707']; //getPlanID('707')->id;
                $planJeevan = $customplan['713']; //getPlanID('713')->id;
                $planRD = $customplan['704']; //getPlanID('704')->id;
                $planBhavhishya = $customplan['718']; //getPlanID('718')->id;
                $monthlyId = array($planKanyadhan, $planMB, $planFRD, $planJeevan, $planRD, $planBhavhishya);
                $planMI = $customplan['712']; //getPlanID('712')->id;
                $planFFD = $customplan['705']; //getPlanID('705')->id;
                $planFD = $customplan['706']; //getPlanID('706')->id;
                $fdId = array($planMI, $planFFD, $planFD);
                $val['DT_RowIndex'] = $sno;
                $val['join_date'] = date("d/m/Y", strtotime($row->associate_join_date));
                $val['branch'] = $row['associate_branch']->name;
                $val['branch_code'] = $row['associate_branch']->branch_code;
                $val['sector_name'] = $row['associate_branch']->sector;
                $val['region_name'] = $row['associate_branch']->regan;
                $val['zone_name'] = $row['associate_branch']->zone;
                $val['member_id'] = $row->associate_no;
                $val['name'] = $row->first_name . ' ' . $row->last_name;
                $val['cadre'] = $row['getCarderNameCustom']->name . '(' . $row['getCarderNameCustom']->short_name . ')'; //getCarderName($row->current_carder_id);
                $val['current_daily_new_ac'] = investNewAcCount($associate_id, $current_start_date, $current_end_date, $planDaily, $branch_id);
                $val['current_daily_deno_sum'] = investNewDenoSum($associate_id, $current_start_date, $current_end_date, $planDaily, $branch_id);
                $val['current_daily_renew_ac'] = investRenewAc($associate_id, $current_start_date, $current_end_date, $dailyId, $branch_id);
                $val['current_daily_renew'] = investRenewAmountSum($associate_id, $current_start_date, $current_end_date, $dailyId, $branch_id);
                $val['current_monthly_new_ac'] = investNewAcCountType($associate_id, $current_start_date, $current_end_date, $monthlyId, $branch_id);
                $val['current_monthly_deno_sum'] = investNewDenoSumType($associate_id, $current_start_date, $current_end_date, $monthlyId, $branch_id);
                $val['current_monthly_renew_ac'] = investRenewAc($associate_id, $current_start_date, $current_end_date, $monthlyId, $branch_id);
                $val['current_monthly_renew'] = investRenewAmountSum($associate_id, $current_start_date, $current_end_date, $monthlyId, $branch_id);
                $val['current_fd_new_ac'] = investNewAcCountType($associate_id, $current_start_date, $current_end_date, $fdId, $branch_id);
                $val['current_fd_deno_sum'] = investNewDenoSumType($associate_id, $current_start_date, $current_end_date, $fdId, $branch_id);
                /*$val['current_fd_renew_ac']=investRenewAc($associate_id,$current_start_date,$current_end_date,$fdId,$branch_id);
                $val['current_fd_renew']=investRenewAmountSum($associate_id,$current_start_date,$current_end_date,$fdId,$branch_id);*/
                $val['current_ssb_new_ac'] = totalInvestSSbAcCountByType($associate_id, $current_start_date, $current_end_date, $branch_id, 1);
                $val['current_ssb_deno_sum'] = totalInvestSSbAmtByType($associate_id, $current_start_date, $current_end_date, $branch_id, 1);
                $val['current_ssb_renew_ac'] = totalInvestSSbAcCountByType($associate_id, $current_start_date, $current_end_date, $branch_id, 2);
                $val['current_ssb_renew'] = totalInvestSSbAmtByType($associate_id, $current_start_date, $current_end_date, $branch_id, 2);
                $current_sum_ni_ac = $val['current_daily_new_ac'] + $val['current_monthly_new_ac'] + $val['current_fd_new_ac'] + $val['current_ssb_new_ac'];
                $current_sum_ni_amount = $val['current_daily_deno_sum'] + $val['current_monthly_deno_sum'] + $val['current_fd_deno_sum'] + $val['current_ssb_deno_sum'];
                $val['current_total_ni_ac'] = $current_sum_ni_ac;
                $val['current_total_ni_amount'] = number_format((float) $current_sum_ni_amount, 2, '.', '');
                $current_sum_renew_ac = $val['current_daily_renew_ac'] + $val['current_monthly_renew_ac'];
                $current_sum_renew_amount = $val['current_daily_renew'] + $val['current_monthly_renew'];
                $val['current_total_ac'] = $current_sum_renew_ac;
                $val['current_total_amount'] = number_format((float) $current_sum_renew_amount, 2, '.', '');
                $val['current_other_mt'] = investOtherMiByType($associate_id, $current_start_date, $current_end_date, $branch_id, 1, 11);
                $val['current_other_stn'] = investOtherMiByType($associate_id, $current_start_date, $current_end_date, $branch_id, 1, 12);
                $current_ni_m = $val['current_daily_deno_sum'] + $val['current_monthly_deno_sum'] + $val['current_fd_deno_sum'];
                $current_tcc_m = $val['current_daily_deno_sum'] + $val['current_monthly_deno_sum'] + $val['current_fd_deno_sum'] + $val['current_daily_renew'] + $val['current_monthly_renew'];
                $current_tcc = $val['current_daily_deno_sum'] + $val['current_monthly_deno_sum'] + $val['current_fd_deno_sum'] + $val['current_ssb_deno_sum'] + $val['current_daily_renew'] + $val['current_monthly_renew'] + $val['current_ssb_renew'];
                $val['current_ni_m'] = number_format((float) $current_ni_m, 2, '.', '');
                $val['current_ni'] = number_format((float) $current_sum_ni_amount, 2, '.', '');
                $val['current_tcc_m'] = number_format((float) $current_tcc_m, 2, '.', '');
                $val['current_tcc'] = number_format((float) $current_tcc, 2, '.', '');
                $val['current_loan_ac'] = totalLoanAc($associate_id, $current_start_date, $current_end_date, $branch_id);
                $val['current_loan_amount'] = totalLoanAmount($associate_id, $current_start_date, $current_end_date, $branch_id);
                $val['current_loan_recovery_ac'] = totalRenewLoanAc($associate_id, $current_start_date, $current_end_date, $branch_id);
                $val['current_loan_recovery_amount'] = totalRenewLoanAmount($associate_id, $current_start_date, $current_end_date, $branch_id);
                $val['current_new_associate'] = memberCountByType($associate_id, $current_start_date, $current_end_date, $branch_id, 1, 0);
                $val['current_total_associate'] = memberCountByType($associate_id, $current_start_date, $current_end_date, $branch_id, 1, 1);
                $val['current_new_member'] = memberCountByType($associate_id, $current_start_date, $current_end_date, $branch_id, 0, 0);
                $val['current_total_member'] = memberCountByType($associate_id, $current_start_date, $current_end_date, $branch_id, 0, 1);
                $val['compare_daily_new_ac'] = investNewAcCount($associate_id, $comp_start_date, $comp_end_date, $planDaily, $branch_id);
                $val['compare_daily_deno_sum'] = investNewDenoSum($associate_id, $comp_start_date, $comp_end_date, $planDaily, $branch_id);
                $val['compare_daily_renew_ac'] = investRenewAc($associate_id, $comp_start_date, $comp_end_date, $dailyId, $branch_id);
                $val['compare_daily_renew'] = investRenewAmountSum($associate_id, $comp_start_date, $comp_end_date, $dailyId, $branch_id);
                $val['compare_monthly_new_ac'] = investNewAcCountType($associate_id, $comp_start_date, $comp_end_date, $monthlyId, $branch_id);
                $val['compare_monthly_deno_sum'] = investNewDenoSumType($associate_id, $comp_start_date, $comp_end_date, $monthlyId, $branch_id);
                $val['compare_monthly_renew_ac'] = investRenewAc($associate_id, $comp_start_date, $comp_end_date, $monthlyId, $branch_id);
                $val['compare_monthly_renew'] = investRenewAmountSum($associate_id, $comp_start_date, $comp_end_date, $monthlyId, $branch_id);
                $val['compare_fd_new_ac'] = investNewAcCountType($associate_id, $comp_start_date, $comp_end_date, $fdId, $branch_id);
                $val['compare_fd_deno_sum'] = investNewDenoSumType($associate_id, $comp_start_date, $comp_end_date, $fdId, $branch_id);
                /* $val['compare_fd_renew_ac']=investRenewAc($associate_id,$comp_start_date,$comp_end_date,$fdId,$branch_id);
                $val['compare_fd_renew']=investRenewAmountSum($associate_id,$comp_start_date,$comp_end_date,$fdId,$branch_id);*/
                $val['compare_ssb_new_ac'] = totalInvestSSbAcCountByType($associate_id, $comp_start_date, $comp_end_date, $branch_id, 1);
                $val['compare_ssb_deno_sum'] = totalInvestSSbAmtByType($associate_id, $comp_start_date, $comp_start_date, $branch_id, 1);
                $val['compare_ssb_renew_ac'] = totalInvestSSbAcCountByType($associate_id, $comp_start_date, $comp_end_date, $branch_id, 2);
                $val['compare_ssb_renew'] = totalInvestSSbAmtByType($associate_id, $comp_start_date, $comp_end_date, $branch_id, 2);
                $compare_sum_ni_ac = $val['compare_daily_new_ac'] + $val['compare_monthly_new_ac'] + $val['compare_fd_new_ac'] + $val['compare_ssb_new_ac'];
                $compare_sum_ni_amount = $val['compare_daily_deno_sum'] + $val['compare_monthly_deno_sum'] + $val['compare_fd_deno_sum'] + $val['compare_ssb_deno_sum'];
                $val['compare_total_ni_ac'] = $compare_sum_ni_ac;
                $val['compare_total_ni_amount'] = number_format((float) $compare_sum_ni_amount, 2, '.', '');
                $compare_sum_renew_ac = $val['compare_daily_renew_ac'] + $val['compare_monthly_renew_ac'];
                $compare_sum_renew_amount = $val['compare_daily_renew'] + $val['compare_monthly_renew'];
                $val['compare_total_ac'] = $compare_sum_renew_ac;
                $val['compare_total_amount'] = number_format((float) $compare_sum_renew_amount, 2, '.', '');
                $val['compare_other_mt'] = investOtherMiByType($associate_id, $comp_start_date, $comp_end_date, $branch_id, 1, 11);
                $val['compare_other_stn'] = investOtherMiByType($associate_id, $comp_start_date, $comp_end_date, $branch_id, 1, 12);
                $compare_ni_m = $val['compare_daily_deno_sum'] + $val['compare_monthly_deno_sum'] + $val['compare_fd_deno_sum'];
                $compare_tcc_m = $val['compare_daily_deno_sum'] + $val['compare_monthly_deno_sum'] + $val['compare_fd_deno_sum'] + $val['compare_daily_renew'] + $val['compare_monthly_renew'];
                $compare_tcc = $val['compare_daily_deno_sum'] + $val['compare_monthly_deno_sum'] + $val['compare_fd_deno_sum'] + $val['compare_ssb_deno_sum'] + $val['compare_daily_renew'] + $val['compare_monthly_renew'] + $val['compare_ssb_renew'];
                $val['compare_ni_m'] = number_format((float) $compare_ni_m, 2, '.', '');
                $val['compare_ni'] = number_format((float) $compare_sum_ni_amount, 2, '.', '');
                $val['compare_tcc_m'] = number_format((float) $compare_tcc_m, 2, '.', '');
                $val['compare_tcc'] = number_format((float) $compare_tcc, 2, '.', '');
                $val['compare_loan_ac'] = totalLoanAc($associate_id, $comp_start_date, $comp_end_date, $branch_id);
                $val['compare_loan_amount'] = totalLoanAmount($associate_id, $comp_start_date, $comp_end_date, $branch_id);
                $val['compare_loan_recovery_ac'] = totalRenewLoanAc($associate_id, $comp_start_date, $comp_end_date, $branch_id);
                $val['compare_loan_recovery_amount'] = totalRenewLoanAmount($associate_id, $comp_start_date, $comp_end_date, $branch_id);
                $val['compare_new_associate'] = memberCountByType($associate_id, $comp_start_date, $comp_end_date, $branch_id, 1, 0);
                $val['compare_total_associate'] = memberCountByType($associate_id, $comp_start_date, $comp_end_date, $branch_id, 1, 1);
                $val['compare_new_member'] = memberCountByType($associate_id, $comp_start_date, $comp_end_date, $branch_id, 0, 0);
                $val['compare_total_member'] = memberCountByType($associate_id, $comp_start_date, $comp_end_date, $branch_id, 0, 1);
                $val['result_daily_new_ac'] = $val['current_daily_new_ac'] - $val['compare_daily_new_ac'];
                $val['result_daily_deno_sum'] = $val['current_daily_deno_sum'] - $val['compare_daily_deno_sum'];
                $val['result_daily_renew_ac'] = $val['current_daily_renew_ac'] - $val['compare_daily_renew_ac'];
                $val['result_daily_renew'] = $val['current_daily_renew'] - $val['compare_daily_renew'];
                $val['result_monthly_new_ac'] = $val['current_monthly_new_ac'] - $val['compare_monthly_new_ac'];
                $val['result_monthly_deno_sum'] = $val['current_monthly_deno_sum'] - $val['compare_monthly_deno_sum'];
                $val['result_monthly_renew_ac'] = $val['current_monthly_renew_ac'] - $val['compare_monthly_renew_ac'];
                $val['result_monthly_renew'] = $val['current_monthly_renew'] - $val['compare_monthly_renew'];
                $val['result_fd_new_ac'] = $val['current_fd_new_ac'] - $val['compare_fd_new_ac'];
                $val['result_fd_deno_sum'] = $val['current_fd_deno_sum'] - $val['compare_fd_deno_sum'];
                /*$val['result_fd_renew_ac']=$val['current_fd_renew_ac']-$val['compare_fd_renew_ac'];
                $val['result_fd_renew']=$val['current_fd_renew']-$val['compare_fd_renew'];*/
                $val['result_ssb_new_ac'] = $val['current_ssb_new_ac'] - $val['compare_ssb_new_ac'];
                $val['result_ssb_deno_sum'] = $val['current_ssb_deno_sum'] - $val['compare_ssb_deno_sum'];
                $val['result_ssb_renew_ac'] = $val['current_ssb_renew_ac'] - $val['compare_ssb_renew'];
                $val['result_ssb_renew'] = $val['current_ssb_renew'] - $val['compare_ssb_deno_sum'];
                $result_sum_ni_ac = $current_sum_ni_ac - $compare_sum_ni_ac;
                $result_sum_ni_amount = $current_sum_ni_amount - $compare_sum_ni_amount;
                $val['result_total_ni_ac'] = $result_sum_ni_ac;
                $val['result_total_ni_amount'] = number_format((float) $result_sum_ni_amount, 2, '.', '');
                $result_sum_renew_ac = $current_sum_renew_ac - $compare_sum_renew_ac;
                $result_sum_renew_amount = $current_sum_renew_amount - $compare_sum_renew_amount;
                $val['result_total_ac'] = $result_sum_renew_ac;
                $val['result_total_amount'] = number_format((float) $result_sum_renew_amount, 2, '.', '');
                $val['result_other_mt'] = $val['current_other_mt'] - $val['compare_other_mt'];
                $val['result_other_stn'] = $val['current_other_stn'] - $val['compare_other_stn'];
                $val['result_ni_m'] = $val['current_ni_m'] - $val['compare_ni_m'];
                $val['result_ni'] = $val['current_ni'] - $val['compare_ni'];
                $val['result_tcc_m'] = $val['current_tcc_m'] - $val['compare_tcc_m'];
                $val['result_tcc'] = $val['current_tcc'] - $val['compare_tcc'];
                $val['result_loan_ac'] = $val['current_loan_ac'] - $val['compare_loan_ac'];
                $val['result_loan_amount'] = $val['current_loan_amount'] - $val['compare_loan_amount'];
                $val['result_loan_recovery_ac'] = $val['current_loan_recovery_ac'] - $val['compare_loan_recovery_ac'];
                $val['result_loan_recovery_amount'] = $val['current_loan_recovery_amount'] - $val['compare_loan_recovery_amount'];
                $val['result_new_associate'] = $val['current_new_associate'] - $val['compare_new_associate'];
                $val['result_total_associate'] = $val['current_total_associate'] - $val['compare_total_associate'];
                $val['result_new_member'] = $val['current_new_member'] - $val['compare_new_member'];
                $val['result_total_member'] = $val['current_total_member'] - $val['compare_total_member'];
                $rowReturn[] = $val;
            }
            //  print_r($rowReturn);die;
            $output = array("draw" => $_POST['draw'], "recordsTotal" => $totalCount, "recordsFiltered" => $count, "data" => $rowReturn, );
            return json_encode($output);
        }
    }
    /**
     * Cash in hand detail.
     * Route: /admin/report/associate_business
     * Method: get
     * @return  array()  Response
     */
    public function cashInHand()
    {
        $data['title'] = 'Report | Cash In Hand Report';
        $data['branch'] = Branch::where('status', 1)->get();
        return view('templates.admin.report.cash', $data);
    }
    /**
     * Cash in hand detail.
     * Route: /admin/report/associate_business
     * Method: get
     * @return  array()  Response
     */
    public function maturityReport()
    {
        $data['title'] = 'Report | Maturity Report';
        $data['branch'] = Branch::where('status', 1)->get();
        return view('templates.admin.report.maturity', $data);
    }
    /**
     * cash in branch detail
     * Route: ajax call from - /admin/report/associate_business
     * Method: Post
     * @param  \Illuminate\Http\Request  $request
     * @return JSON array
     */
    public function cashInHandDetail(Request $request)
    {
        if ($request->ajax()) {
            // fillter array
            $arrFormData = array();
            $arrFormData['start_date'] = $request->start_date;
            $arrFormData['end_date'] = $request->end_date;
            $arrFormData['branch_id'] = $request->branch_id;
            $arrFormData['is_search'] = $request->is_search;
            if ($arrFormData['start_date'] != '') {
                $startDate = date("Y-m-d", strtotime(convertDate($arrFormData['start_date'])));
            } else {
                $startDate = '';
            }
            if ($arrFormData['end_date'] != '') {
                $endDate = date("Y-m-d ", strtotime(convertDate($arrFormData['end_date'])));
            } else {
                $endDate = '';
            }
            if ($arrFormData['branch_id'] != '') {
                $branch_id = $arrFormData['branch_id'];
            } else {
                $branch_id = '';
            }
            $data = Branch::where('status', 1);
            /******* fillter query start ****/
            if (isset($arrFormData['is_search']) && $arrFormData['is_search'] == 'yes') {
                if ($arrFormData['branch_id'] != '') {
                    $id = $arrFormData['branch_id'];
                    $data = $data->where('id', '=', $id);
                }
            }
            /******* fillter query End ****/
            $data1 = $data->orderby('created_at', 'ASC')->get();
            $count = count($data1);
            $data = $data->orderby('created_at', 'ASC')->offset($_POST['start'])->limit($_POST['length'])->get();
            $totalCount = Branch::where('status', 1)->count();
            $sno = $_POST['start'];
            $rowReturn = array();
            foreach ($data as $row) {
                $sno++;
                $bid = $row->id;
                $val['DT_RowIndex'] = $sno;
                $val['branch'] = $row->name;
                $val['branch_code'] = $row->branch_code;
                $val['sector_name'] = $row->sector;
                $val['region_name'] = $row->regan;
                $val['zone_name'] = $row->zone;
                $ssb = $this->branchInvestmentAmountSSB($startDate, $endDate, $bid);
                $newInvest1 = $this->branchInvestmentAmount($startDate, $endDate, $bid);
                $newInvest = number_format((float) $ssb + $newInvest1, 2, '.', '');
                $val['investmentAmount'] = $newInvest;
                $val['loanEmi'] = $loanEmi = $this->loanEmiBranch($startDate, $endDate, $bid);
                $loanEmi = 0;
                $val['other'] = $otherAmount = $this->otherCompanyCredit($startDate, $endDate, $bid);
                $val['totalAmount'] = number_format((float) $newInvest + $loanEmi + $otherAmount, 2, '.', '');
                $rowReturn[] = $val;
            }
            //  print_r($rowReturn);die;
            $output = array("draw" => $_POST['draw'], "recordsTotal" => $totalCount, "recordsFiltered" => $count, "data" => $rowReturn, );
            return json_encode($output);
        }
    }
    /**
     * get sum of investment deno amount by plan id.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function branchInvestmentAmount($start, $end, $bid)
    {
        $pid = 1;
        $data = Daybook::with([
            'investment' => function ($query) {
                $query->select('id', 'plan_id', 'account_number');
            }
        ])->whereHas('investment', function ($query) use ($pid) {
            $query->where('member_investments.plan_id', '!=', $pid);
        })->where('is_eli', '!=', 1)->whereIn('transaction_type', array(1, 2, 4))->where('branch_id', '=', $bid)->where('payment_type', '!=', 'DR');
        if ($start != '') {
            $startDate = $start;
            if ($end != '') {
                $endDate = $end;
            } else {
                $endDate = '';
            }
            $data = $data->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate]);
        }
        $sum = $data->sum('deposit');
        return $sum;
    }
    /**
     * get sum of investment deno amount by plan id.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function branchInvestmentAmountSSB($start, $end, $bid)
    {
        $data = \App\Models\SavingAccountTranscation::with([
            'savingAc' => function ($query) {
                $query->select('id', 'saving_account_id', 'account_no', 'branch_id');
            }
        ])->whereHas('savingAc', function ($query) use ($bid) {
            $query->where('saving_accounts.branch_id', $bid);
        })->where('payment_type', '!=', 'DR');
        if ($start != '') {
            $startDate = $start;
            if ($end != '') {
                $endDate = $end;
            } else {
                $endDate = '';
            }
            $data = $data->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate]);
        }
        $sum = $data->sum('deposit');
        return $sum;
    }
    /**
     * get sum of loan emi  by plan id.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function loanEmiBranch($start, $end, $bid)
    {
        $sum = 'Coming Soon';
        return $sum;
    }
    /**
     * get other Company Credit by branch.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function otherCompanyCredit($start, $end, $bid)
    {
        $data = Transcation::whereIn('transaction_type', array(0, 7))->where('branch_id', '=', $bid);
        if ($start != '') {
            $startDate = $start;
            if ($end != '') {
                $endDate = $end;
            } else {
                $endDate = '';
            }
            $data = $data->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate]);
        }
        $sum = $data->sum('amount');
        return $sum;
    }
    /**
     * All transaction detail.
     * Route: /admin/report/associate_business
     * Method: get
     * @return  array()  Response
     */
    public function transaction()
    {
        if (check_my_permission(Auth::user()->id, "124") != "1") {
            return redirect()->route('admin.dashboard');
        }
        $data['title'] = 'Report | Transaction  Report';
        $data['branch'] = Branch::where('status', 1)->get();
        return view('templates.admin.report.transaction', $data);
    }
    /**
     * cash in branch detail
     * Route: ajax call from - /admin/report/associate_business
     * Method: Post
     * @param  \Illuminate\Http\Request  $request
     * @return JSON array
     */
    public function transactionDetail(Request $request)
    {
        if ($request->ajax()) {
            // fillter array
            $arrFormData = array();
            $arrFormData['start_date'] = $request->start_date;
            $arrFormData['end_date'] = $request->end_date;
            $arrFormData['branch_id'] = $request->branch_id;
            $arrFormData['payment_type'] = $request->payment_type;
            $arrFormData['payment_mode'] = $request->payment_mode;
            $arrFormData['is_search'] = $request->is_search;
            // print_r($_POST);die;
            $planSSB = getPlanID('703')->id;
            $data = Daybook::with([
                'dbranch' => function ($query) {
                    $query->select('id', 'name', 'branch_code', 'sector', 'regan', 'zone');
                }
            ])->with([
                        'investment' => function ($query) {
                            $query->select('id', 'plan_id', 'account_number', 'member_id');
                        }
                    ])->whereHas('investment', function ($query) use ($planSSB) {
                        $query->where('member_investments.plan_id', '!=', $planSSB);
                    })->Where('transaction_type', 4);
            if (Auth::user()->branch_id > 0) {
                $id = Auth::user()->branch_id;
                $data = $data->where('branch_id', '=', $id);
            }
            /******* fillter query start ****/
            if (isset($arrFormData['is_search']) && $arrFormData['is_search'] == 'yes') {
                if (isset($arrFormData['branch_id']) && $arrFormData['branch_id'] != '') {
                    $id = $arrFormData['branch_id'];
                    $data = $data->where('branch_id', $id);
                }
                if ($arrFormData['payment_mode'] != '') {
                    $payment_mode = $arrFormData['payment_mode'];
                    if ($payment_mode == 0) {
                        $data = $data->where('payment_mode', 0);
                    }
                    if ($payment_mode == 1) {
                        $data = $data->where('payment_mode', 1);
                    }
                    if ($payment_mode == 'other') {
                        $data = $data->where('payment_mode', '>', 1);
                    }
                }
                if ($arrFormData['payment_type'] != '') {
                    $payment_type = $arrFormData['payment_type'];
                    if ($payment_type == 'DR') {
                        $data = $data->where('payment_type', 'DR');
                    }
                    if ($payment_type == 'CR') {
                        $data = $data->where('payment_type', '!=', 'DR');
                    }
                }
                if ($arrFormData['start_date'] != '') {
                    $startDate = date("Y-m-d", strtotime(convertDate($arrFormData['start_date'])));
                    if ($arrFormData['end_date'] != '') {
                        $endDate = date("Y-m-d ", strtotime(convertDate($arrFormData['end_date'])));
                    } else {
                        $endDate = '';
                    }
                    $data = $data->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate]);
                }
            }
            /******* fillter query End ****/
            $count = $data->count();
            //$data= $data->orderby('created_at','DESC')->get();
            $data = $data->orderby('created_at', 'DESC')->offset($_POST['start'])->limit($_POST['length'])->get();
            // $dataCount = Daybook::with(['investment' => function($query){ $query->select('id', 'plan_id','account_number','member_id');}])->whereHas('investment', function ($query) use ($planSSB) {  $query->where('member_investments.plan_id','!=',$planSSB); })->where(function($q) { $q->where('transaction_type', 2)->orWhere('transaction_type', 4); });
            $dataCount = Daybook::with([
                'dbranch' => function ($query) {
                    $query->select('id', 'name', 'branch_code', 'sector', 'regan', 'zone');
                }
            ])->with([
                        'investment' => function ($query) {
                            $query->select('id', 'plan_id', 'account_number', 'member_id');
                        }
                    ])->whereHas('investment', function ($query) use ($planSSB) {
                        $query->where('member_investments.plan_id', '!=', $planSSB);
                    })->Where('transaction_type', 4);
            if (Auth::user()->branch_id > 0) {
                $dataCount = $dataCount->where('branch_id', '=', Auth::user()->branch_id);
            }
            $totalCount = $dataCount->count();
            $sno = $_POST['start'];
            $rowReturn = array();
            foreach ($data as $row) {
                $sno++;
                $bid = $row->id;
                $val['DT_RowIndex'] = $sno;
                $val['branch'] = $row['dbranch']->name;
                $val['branch_code'] = $row['dbranch']->branch_code;
                $val['sector_name'] = $row['dbranch']->sector;
                $val['region_name'] = $row['dbranch']->regan;
                $val['zone_name'] = $row['dbranch']->zone;
                $val['member_id'] = getSeniorData($row['investment']->member_id, 'member_id');
                $val['member_name'] = getSeniorData($row['investment']->member_id, 'first_name') . ' ' . getSeniorData($row['investment']->member_id, 'last_name');
                $val['account_number'] = $row['investment']->account_number;
                $val['plan'] = getPlanDetail($row['investment']->plan_id)->name;
                $account_number = $row['investment']->account_number;
                if (str_starts_with($account_number, 'R-')) {
                    $val['tag'] = 'R';
                } else {
                    $val['tag'] = 'N';
                }
                $val['amount'] = number_format((float) $row->deposit, 2, '.', '');
                $p_mode = 'Other';
                if ($row->payment_mode == 0) {
                    $p_mode = 'Cash';
                }
                if ($row->payment_mode == 1) {
                    $p_mode = 'Cheque';
                }
                $val['mode'] = $p_mode;
                $p_type = 'CR';
                if ($row->payment_type == 'DR') {
                    $p_type = 'DR';
                    $val['amount'] = number_format((float) $row->withdrawal, 2, '.', '');
                }
                $val['type'] = $p_type;
                $is_eli = 'No';
                if ($row->is_eli == 1) {
                    $p_mode = 'Yes';
                }
                $val['is_eli'] = $is_eli;
                $val['created_at'] = date("d/m/Y", strtotime($row->created_at));
                $rowReturn[] = $val;
            }
            //  print_r($rowReturn);die;
            $output = array("draw" => $_POST['draw'], "recordsTotal" => $totalCount, "recordsFiltered" => $count, "data" => $rowReturn, );
            return json_encode($output);
        }
    }
    /**
     *ssb transaction detail
     * Route: ajax call from - /admin/report/associate_business
     * Method: Post
     * @param  \Illuminate\Http\Request  $request
     * @return JSON array
     */
    public function transactionDetailSSB(Request $request)
    {
        if ($request->ajax()) {
            // fillter array
            $arrFormData = array();
            $arrFormData['start_date'] = $request->start_date;
            $arrFormData['end_date'] = $request->end_date;
            $arrFormData['branch_id'] = $request->branch_id;
            $arrFormData['payment_type'] = $request->payment_type;
            $arrFormData['payment_mode'] = $request->payment_mode;
            $arrFormData['is_search'] = $request->is_search;
            $data = \App\Models\SavingAccountTranscation::with([
                'savingAc' => function ($query) {
                    $query->select('id', 'account_no', 'branch_id', 'member_id');
                }
            ]);
            if (Auth::user()->branch_id > 0) {
                $bid = Auth::user()->branch_id;
                $data = $data->whereHas('savingAc', function ($query) use ($bid) {
                    $query->where('saving_accounts.branch_id', $bid);
                });
            }
            /******* fillter query start ****/
            if (isset($arrFormData['is_search']) && $arrFormData['is_search'] == 'yes') {
                if (isset($arrFormData['branch_id']) && $arrFormData['branch_id'] != '') {
                    $bid = $arrFormData['branch_id'];
                    $data = $data->whereHas('savingAc', function ($query) use ($bid) {
                        $query->where('saving_accounts.branch_id', $bid);
                    });
                }
                if ($arrFormData['payment_mode'] != '') {
                    $payment_mode = $arrFormData['payment_mode'];
                    if ($payment_mode == 0) {
                        $data = $data->where('payment_mode', 0);
                    }
                    if ($payment_mode == 1) {
                        $data = $data->where('payment_mode', 1);
                    }
                    if ($payment_mode == 'other') {
                        $data = $data->where('payment_mode', '>', 1);
                    }
                }
                if ($arrFormData['payment_type'] != '') {
                    $payment_type = $arrFormData['payment_type'];
                    if ($payment_type == 'DR') {
                        $data = $data->where('payment_type', 'DR');
                    }
                    if ($payment_type == 'CR') {
                        $data = $data->where('payment_type', '!=', 'DR');
                    }
                }
                if ($arrFormData['start_date'] != '') {
                    $startDate = date("Y-m-d", strtotime(convertDate($arrFormData['start_date'])));
                    if ($arrFormData['end_date'] != '') {
                        $endDate = date("Y-m-d ", strtotime(convertDate($arrFormData['end_date'])));
                    } else {
                        $endDate = '';
                    }
                    $data = $data->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate]);
                }
            }
            /******* fillter query End ****/
            $data1 = $data->orderby('created_at', 'DESC')->get();
            $count = count($data1);
            $data = $data->orderby('created_at', 'DESC')->offset($_POST['start'])->limit($_POST['length'])->get();
            if (Auth::user()->branch_id > 0) {
                $bid = Auth::user()->branch_id;
                $totalCount = \App\Models\SavingAccountTranscation::whereHas('savingAc', function ($query) use ($bid) {
                    $query->where('saving_accounts.branch_id', $bid);
                })->count();
            } else {
                $totalCount = \App\Models\SavingAccountTranscation::count();
            }
            $sno = $_POST['start'];
            $rowReturn = array();
            foreach ($data as $row) {
                $sno++;
                $val['DT_RowIndex'] = $sno;
                $val['branch'] = getBranchDetail($row['savingAc']->branch_id)->name;
                $val['branch_code'] = getBranchDetail($row['savingAc']->branch_id)->branch_code;
                $val['sector_name'] = getBranchDetail($row['savingAc']->branch_id)->sector;
                $val['region_name'] = getBranchDetail($row['savingAc']->branch_id)->regan;
                $val['zone_name'] = getBranchDetail($row['savingAc']->branch_id)->zone;
                $val['member_id'] = getSeniorData($row['savingAc']->member_id, 'member_id');
                $val['member_name'] = getSeniorData($row['savingAc']->member_id, 'first_name') . ' ' . getSeniorData($row['savingAc']->member_id, 'last_name');
                $val['account_number'] = $row['savingAc']->account_no;
                $val['amount'] = number_format((float) $row->deposit, 2, '.', '');
                $p_mode = 'Other';
                if ($row->payment_mode == 0) {
                    $p_mode = 'Cash';
                }
                if ($row->payment_mode == 1) {
                    $p_mode = 'Cheque';
                }
                $val['mode'] = $p_mode;
                $p_type = 'CR';
                if ($row->payment_type == 'DR') {
                    $p_type = 'DR';
                    $val['amount'] = number_format((float) $row->withdrawal, 2, '.', '');
                }
                $val['type'] = $p_type;
                $val['created_at'] = date("d/m/Y", strtotime($row->created_at));
                $rowReturn[] = $val;
            }
            //  print_r($rowReturn);die;
            $output = array("draw" => $_POST['draw'], "recordsTotal" => $totalCount, "recordsFiltered" => $count, "data" => $rowReturn, );
            return json_encode($output);
        }
    }
    /**
     *other transaction detail
     * Route: ajax call from - /admin/report/associate_business
     * Method: Post
     * @param  \Illuminate\Http\Request  $request
     * @return JSON array
     */
    public function transactionDetailOther(Request $request)
    {
        if ($request->ajax()) {
            // fillter array
            $arrFormData = array();
            $arrFormData['start_date'] = $request->start_date;
            $arrFormData['end_date'] = $request->end_date;
            $arrFormData['branch_id'] = $request->branch_id;
            $arrFormData['payment_type'] = $request->payment_type;
            $arrFormData['payment_mode'] = $request->payment_mode;
            $arrFormData['is_search'] = $request->is_search;
            $data = Transcation::whereIn('transaction_type', array(0, 7));
            if (Auth::user()->branch_id > 0) {
                $bid = Auth::user()->branch_id;
                $data = $data->where('branch_id', $bid);
            }
            /******* fillter query start ****/
            if (isset($arrFormData['is_search']) && $arrFormData['is_search'] == 'yes') {
                if (isset($arrFormData['branch_id']) && $arrFormData['branch_id'] != '') {
                    $bid = $arrFormData['branch_id'];
                    $data = $data->where('branch_id', $bid);
                }
                if ($arrFormData['payment_mode'] != '') {
                    $payment_mode = $arrFormData['payment_mode'];
                    if ($payment_mode == 0) {
                        $data = $data->where('payment_mode', 0);
                    }
                    if ($payment_mode == 1) {
                        $data = $data->where('payment_mode', 1);
                    }
                    if ($payment_mode == 'other') {
                        $data = $data->where('payment_mode', '>', 1);
                    }
                }
                if ($arrFormData['payment_type'] != '') {
                    $payment_type = $arrFormData['payment_type'];
                    if ($payment_type == 'DR') {
                        $data = $data->where('payment_type', 'DR');
                    }
                    if ($payment_type == 'CR') {
                        $data = $data->where('payment_type', '!=', 'DR');
                    }
                }
                if ($arrFormData['start_date'] != '') {
                    $startDate = date("Y-m-d", strtotime(convertDate($arrFormData['start_date'])));
                    if ($arrFormData['end_date'] != '') {
                        $endDate = date("Y-m-d ", strtotime(convertDate($arrFormData['end_date'])));
                    } else {
                        $endDate = '';
                    }
                    $data = $data->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate]);
                }
            }
            /******* fillter query End ****/
            $count = $data->count('id');
            $data = $data->orderby('created_at', 'DESC')->offset($_POST['start'])->limit($_POST['length'])->get();
            $dataCount = Transcation::whereIn('transaction_type', array(0, 7));
            if (Auth::user()->branch_id > 0) {
                $dataCount = $dataCount->where('branch_id', '=', Auth::user()->branch_id);
            }
            $totalCount = $dataCount->count();
            $sno = $_POST['start'];
            $rowReturn = array();
            foreach ($data as $row) {
                $sno++;
                $val['DT_RowIndex'] = $sno;
                $val['branch'] = getBranchDetail($row->branch_id)->name;
                $val['branch_code'] = getBranchDetail($row->branch_id)->branch_code;
                $val['sector_name'] = getBranchDetail($row->branch_id)->sector;
                $val['region_name'] = getBranchDetail($row->branch_id)->regan;
                $val['zone_name'] = getBranchDetail($row->branch_id)->zone;
                $val['member_id'] = getSeniorData($row->member_id, 'member_id');
                $val['member_name'] = getSeniorData($row->member_id, 'first_name') . ' ' . getSeniorData($row->member_id, 'last_name');
                $val['account_number'] = 'Passbook Print';
                if ($row->transaction_type == 0) {
                    $val['account_number'] = 'Member Register';
                    if ($row->amount == 90 || $row->amount == 90.00) {
                        $val['account_number'] = 'Stn Charge';
                    }
                }
                $val['amount'] = number_format((float) $row->amount, 2, '.', '');
                $p_mode = 'Other';
                if ($row->payment_mode == 0) {
                    $p_mode = 'Cash';
                }
                if ($row->payment_mode == 1) {
                    $p_mode = 'Cheque';
                }
                $val['mode'] = $p_mode;
                $p_type = 'CR';
                if ($row->payment_type == 'DR') {
                    $p_type = 'DR';
                    $val['amount'] = number_format((float) $row->withdrawal, 2, '.', '');
                }
                $val['type'] = $p_type;
                $val['created_at'] = date("d/m/Y", strtotime($row->created_at));
                $rowReturn[] = $val;
            }
            //  print_r($rowReturn);die;
            $output = array("draw" => $_POST['draw'], "recordsTotal" => $totalCount, "recordsFiltered" => $count, "data" => $rowReturn, );
            return json_encode($output);
        }
    }
    /**
     * get branch region by zone.
     * Route: /hr
     * Method: get
     * @return  view
     */
    public function branchRegionByZone(Request $request)
    {
        //echo $request->start_date;die;
        /*$data['branch'] = Branch::where('status',1)->get();
        $data['zone'] = Branch::where('status',1)->distinct('zone')->get();
        $data['sector'] = Branch::where('status',1)->get();
        $data['region'] = Branch::where('status',1)->get();
        */
        $data = Branch::where('status', 1)->where('zone', $request->zone)->distinct('regan')->get('regan');
        //print_r($data);die;
        $return_array = compact('data');
        return json_encode($return_array);
    }
    /**
     * get branch sector by region.
     * Route: /hr
     * Method: get
     * @return  view
     */
    public function branchSectorByRegion(Request $request)
    {
        //echo $request->start_date;die;
        /*$data['branch'] = Branch::where('status',1)->get();
        $data['zone'] = Branch::where('status',1)->distinct('zone')->get();
        $data['sector'] = Branch::where('status',1)->get();
        $data['region'] = Branch::where('status',1)->get();
        */
        $data = Branch::where('status', 1)->where('regan', $request->region)->distinct('sector')->get('sector');
        //print_r($data);die;
        $return_array = compact('data');
        return json_encode($return_array);
    }
    /**
     * get branch  by sector or without sector.
     * Route: /hr
     * Method: get
     * @return  view
     */
    public function branchBySector(Request $request)
    {
        //echo $request->start_date;die;
        if ($request->sector != '') {
            $data = Branch::where('status', 1)->where('sector', $request->sector)->get();
        } else {
            $data = Branch::where('status', 1)->get();
        }
        //print_r($data);die;
        $return_array = compact('data');
        return json_encode($return_array);
    }
    public function loan()
    {
        if (check_my_permission(Auth::user()->id, "125") != "1") {
            return redirect()->route('admin.dashboard');
        }
        $data['title'] = 'Report | Loan  Report';
        $data['branch'] = Branch::select('id', 'name')->where('status', 1)->get();
        $data['loan'] = Loans::select('name', 'id')->get();
        return view('templates.admin.report.loan', $data);
    }
    public function companyIdToLoan(Request $request)
    {
        $company_id = $request->company_id;
        $data['loan'] = Loans::has('company')->when($company_id > 0, function ($q) use ($company_id) {
            $q->where('company_id', $company_id);
        })->pluck('name', 'id');
        return json_encode($data);
    }
    /**
     * Display the specified resource.
     *
     * @param  \App\Reservation  $reservation
     * @return \Illuminate\Http\Response
     */
    public function loanListing(Request $request)
    {
        if ($request->ajax()) {
            $arrFormData = array(); {
                if (!empty($_POST['searchform'])) {
                    foreach ($_POST['searchform'] as $frm_data) {
                        $arrFormData[$frm_data['name']] = $frm_data['value'];
                    }
                }
                $loan = Memberloans::query()
                // ->select('id', 'customer_id', 'applicant_id', 'status', 'loan_type', 'applicant_id', 'group_loan_common_id', 'branch_id', 'account_number', 'amount', 'deposite_amount', 'approve_date', 'approved_date', 'emi_amount', 'emi_period', 'emi_option', 'created_at', 'credit_amount', 'ROI', 'due_amount', 'company_id', 'associate_member_id', 'customer_id')
                ->has('company')
                    ->with([
                        'loan:id,name,loan_type',
                        'company:id,name',
                        'member:id,member_id,first_name,last_name,mobile_no,address',
                        'loanMemberCompany:id,member_id,customer_id',
                        'loanMemberCompany.member:id,member_id,first_name,last_name',
                        'LoanApplicants:id,member_id,member_loan_id',
                        'LoanCoApplicants:id,member_id,member_loan_id',
                        'LoanCoApplicants.member:id,first_name,last_name,mobile_no,address',
                        'LoanGuarantor:id,member_id,member_loan_id,name,mobile_number',
                        'loanBranch:id,name,sector',
                        'Loanotherdocs:id',
                        'GroupLoanMembers:id',
                        'loanInvestmentPlans:id',
                        // 'get_outstanding' => function ($q) {
                        //     $q->has('loans')->with([
                        //         'loans' => function ($q) {
                        //             $q->where('loan_type', '!=', 'G');
                        //         }
                        //     ]);
                        // }
                    ])
                    ->whereHas('loan', function ($query) {
                        $query->where('loan_type', '!=', 'G');
                    })
                    ;

                $grpLoan = Grouploans::query()
                // select('id', 'branch_id', 'created_at', 'loan_type', 'status', 'account_number', 'member_loan_id', 'applicant_id', 'member_id', 'group_loan_common_id', 'deposite_amount', 'amount', 'approve_date', 'emi_period', 'emi_option', 'due_amount', 'ROI', 'emi_amount', 'credit_amount', 'company_id', 'associate_member_id', 'customer_id')
                ->has('company')
                    ->with([
                        'loan:id,name,loan_type',
                        'company:id,name',
                        'member:id,member_id,first_name,last_name,mobile_no,address',
                        'loanMemberCompany:id,member_id,customer_id',
                        'loanMemberCompanyid:id,member_id,customer_id',
                        'loanMemberCompany.member:id,member_id,first_name,last_name',
                        'LoanApplicants:id,member_id,member_loan_id',
                        'LoanCoApplicants:id,member_id,member_loan_id',
                        'LoanCoApplicants.member:id,first_name,last_name,mobile_no,address',
                        'LoanGuarantor:id,member_id,member_loan_id,name,mobile_number',
                        'loanBranch:id,name,sector',
                        // 'get_outstanding' => function ($q) {
                        //     $q->has('loans')->with([
                        //         'loans' => function ($q) {
                        //             $q->where('loan_type', 'G');
                        //         }
                        //     ]);
                        // }                        
                        ]);
                // ->where('status','!=',0);

                if (Auth::user()->branch_id > 0) {
                    $id = Auth::user()->branch_id;
                    $loan = $loan->where('branch_id', '=', $id);
                    $grpLoan = $grpLoan->where('branch_id', '=', $id);
                }
                if (isset($arrFormData['is_search']) && $arrFormData['is_search'] == 'yes') {
                    if ($arrFormData['start_date'] != '') {
                        $startDate = date("Y-m-d", strtotime(convertDate($arrFormData['start_date'])));
                        if ($arrFormData['end_date'] != '') {
                            $endDate = date("Y-m-d ", strtotime(convertDate($arrFormData['end_date'])));
                        } else {
                            $endDate = '';
                        }
                        $loan = $loan->whereBetween('approve_date', [$startDate, $endDate]);
                        $grpLoan = $grpLoan->whereBetween('approve_date', [$startDate, $endDate]);
                    }
                    if ($arrFormData['plan'] != '') {
                        $plan = $arrFormData['plan'];
                        $loan = $loan->where('loan_type', '=', $plan);
                        $grpLoan = $grpLoan->where('loan_type', '=', $plan);
                    }
                    if (isset($arrFormData['customer_id']) && ($arrFormData['customer_id'] != '')) {
                        $customer_id = $arrFormData['customer_id'];
                        $loan = $loan->whereHas('member', function ($query) use ($customer_id) {
                            $query->where('member_id', $customer_id);
                        });
                        $grpLoan = $grpLoan->whereHas('member', function ($query) use ($customer_id) {
                            $query->where('member_id', $customer_id);
                        });
                    }
                    if (isset($arrFormData['branch_id']) && $arrFormData['branch_id'] > 0) {
                        $branch_id = $arrFormData['branch_id'];
                        $loan = $loan->where('branch_id', '=', $branch_id);
                        $grpLoan = $grpLoan->where('branch_id', '=', $branch_id);
                    }
                    if ($arrFormData['status'] != '') {
                        $status = $arrFormData['status'];
                        $loan = $loan->where('status', '=', $status);
                        $grpLoan = $grpLoan->where('status', '=', $status);
                    }
                    if ($arrFormData['application_number'] != '') {
                        $application_number = $arrFormData['application_number'];
                        $loan = $loan->where('account_number', '=', $application_number);
                        $grpLoan = $grpLoan->where('account_number', '=', $application_number);
                    }
                    if ($arrFormData['member_id'] != '') {
                        $member_id = $arrFormData['member_id'];
                        $loan = $loan->whereHas('loanMemberCompany', function ($query) use ($member_id) {
                            $query->where('member_id', $member_id);
                        });
                        $grpLoan = $grpLoan->whereHas('loanMemberCompanyid', function ($query) use ($member_id) {
                            $query->where('member_id', $member_id);
                        });
                    }
                    if ($arrFormData['company_id'] > 0) {
                        $company_id = $arrFormData['company_id'];
                        $loan = $loan->where('company_id', $company_id);
                        $grpLoan = $grpLoan->where('company_id', $company_id);
                    }
                } else {
                    $startDate = date('Y-m-d', strtotime($request->associate_report_currentdate));
                    $endDate = date('Y-m-d', strtotime($request->associate_report_currentdate));
                }
                $loan = $loan->orderBy('id', 'DESC')->get();
                $grpLoan = $grpLoan->orderBy('id', 'DESC')->get();
                // if ($loan->count() > 0 && !empty($loan)) {
                //     $loannew = $loan;
                // } else if ($grpLoan->count() > 0 && !empty($grpLoan)) {
                //     $loannew = $grpLoan;
                // }
                $data = array_merge($loan->toArray(), $grpLoan->toArray());
                $loannew = $data;
                $count = count($data);
                $totalCount = $count;
                $page = !empty($_POST['draw']) ? (int) $_POST['draw'] : 1;
                $limit = 20;
                $sno = $_POST['start'];
                $rowReturn = array();
                if (($_POST['start'] + $limit) >= $totalCount) {
                    $limit = ($totalCount - $_POST['start']);
                }
                $DataArray = collect($data)->slice($_POST['start'], $limit);
                
                foreach ($DataArray as $row) {
                    // pd($row);
                    $sno++;
                    $val['DT_RowIndex'] = "<div title='(".$row['id'].")'>$sno</div>";
                    switch ($row['status']) {
                        case 0:
                            $val['status'] = 'Inactive';
                            break;
                        case 1:
                            $val['status'] = 'Approved';
                            break;
                        case 2:
                            $val['status'] = 'Rejected';
                            break;
                        case 3:
                            $val['status'] = 'Completed';
                            break;
                        case 4:
                            $val['status'] = 'ONGOING';
                            break;
                        default:
                            $val['status'] = 'N/A';
                            break;
                    }
                    if ($row['loan_type'] == 3) {
                        if (isset($row['customer_id'])) {
                            if (customGetMemberData($row['customer_id'])) {
                                $val['applicant_name'] = customGetMemberData($row['customer_id'])->first_name . ' ' . customGetMemberData($row['customer_id'])->last_name;
                            } else {
                                $val['applicant_name'] = 'N/A';
                            }
                        } else {
                            $val['applicant_name'] = 'N/A';
                        }
                    } else {
                        if (isset($row['customer_id'])) {
                            if (isset($row['loan_member_company']['member'])) {
                                $val['applicant_name'] = customGetMemberData($row['customer_id'])->first_name . ' ' . customGetMemberData($row['customer_id'])->last_name; //customGetMemberData($row->member_id)
                            } else {
                                $val['applicant_name'] = 'N/A';
                            }
                        } else {
                            $val['applicant_name'] = 'N/A';
                        }
                    }
                    if ($row['loan_type'] == 3) {
                        if (isset($row['group_loan_common_id'])) {
                            $val['applicant_id'] = $row['group_loan_common_id'];
                        } else {
                            $val['applicant_id'] = 'N/A';
                        }
                    } else {
                        if (isset($row['customer_id'])) {
                            $val['applicant_id'] = Member::select('id', 'member_id')->find($row['customer_id'])->member_id;
                        } else {
                            $val['applicant_id'] = 'N/A';
                        }
                    }
                    if ($row['loan_type'] == 3) {
                        if (isset($row['loan_member_companyid']['id'])) {
                            $val['applicant_phone_number'] = customGetMemberData($row['customer_id'])->mobile_no ?? 'N/A';

                        } else {
                            $val['applicant_phone_number'] = 'N/A';
                        }
                    } else {
                        if (isset($row['customer_id'])) {
                            $val['applicant_phone_number'] = customGetMemberData($row['customer_id'])->mobile_no ?? 'N/A';

                        } else {
                            $val['applicant_phone_number'] = 'N/A';
                        }
                    }
                    $val['membership_id'] = 'N/A';
                    $val['account_number'] = $row['account_number'];

                    if ($row['loan_type'] == 3) {
                        if (isset($row['loan_member_companyid']['id'])) {
                            $val['customer_id'] = customGetMemberData($row['customer_id'])->member_id ?? 'N/A';

                        } else {
                            $val['customer_id'] = 'N/A';
                        }
                    } else {
                        if (isset($row['customer_id'])) {
                            $val['customer_id'] = customGetMemberData($row['customer_id'])->member_id ?? 'N/A';

                        } else {
                            $val['customer_id'] = 'N/A';
                        }
                    }

                    $val['company'] = isset($row['company']['name']) ? $row['company']['name'] : 'N/A';
                    if (isset($row['branch_id'])) {
                        if (isset($row['loan_branch'])) {
                            $val['branch'] = $row['loan_branch']['name']; //customGetBranchDetail($row->branch_id)->name;
                        } else {
                            $val['branch'] = 'N/A';
                        }
                    } else {
                        $val['branch'] = 'N/A';
                    }
                    if (isset($row['loan_branch'])) {
                        $val['sector'] = $row['loan_branch']['sector']; //customGetBranchDetail($row->branch_id)->sector;
                    } else {
                        $val['sector'] = 'N/A';
                    }
                    if ($row['loan_type'] == 3) {
                        if (isset($row['member_id'])) {
                            $val['member_id'] = getMemberCustomData($row['member_id'])->member_id;
                        } else {
                            $val['member_id'] = 'N/A';
                        }
                    } else {
                        if (isset($row['applicant_id'])) {
                            $val['member_id'] = getMemberCustomData($row['applicant_id'])->member_id;
                        } else {
                            $val['member_id'] = 'N/A';
                        }
                    }
                    if (isset($row['amount'])) {
                        $val['sanctioned_amount'] = $row['amount'] . ' &#8377';
                    } else {
                        $val['sanctioned_amount'] = 'N/A';
                    }
                    $val['transfer_amount'] = isset($row['deposite_amount']) ? $row['deposite_amount'] : 'N/A';
                    if (isset($row['approve_date'])) {
                        $val['sanctioned_date'] = date("d/m/Y", strtotime(convertDate($row['approve_date'])));
                    } else {
                        $val['sanctioned_date'] = 'N/A';
                    }
                    if (isset($row['emi_amount'])) {
                        $val['emi_rate'] = $row['emi_amount'];
                    } else {
                        $val['emi_rate'] = 'N/A';
                    }
                    if (isset($row['emi_period'])) {
                        $val['no_of_installement'] = $row['emi_period'];
                    } else {
                        $val['no_of_installement'] = 'N/A';
                    }
                    if (isset($row['emi_option'])) {
                        if ($row['emi_option'] == 1) {
                            $val['loan_mode'] = 'Months';
                        } elseif ($row['emi_option'] == 2) {
                            $val['loan_mode'] = 'Weeks';
                        } elseif ($row['emi_option'] == 3) {
                            $val['loan_mode'] = 'Daily';
                        }
                    } else {
                        $val['loan_mode'] = 'N/A';
                    }
                    $val['loan_type'] = $row['loan']['name'];
                    $val['loan_issue_date'] = date("d/m/Y", strtotime(convertDate($row['created_at'])));
                    $mode = Daybook::whereIn('transaction_type', [3, 8])->where('loan_id', $row['id'])->first(['payment_mode', 'cheque_dd_no']);
                    if ($mode) {
                        switch ($mode->payment_mode) {
                            case 0:
                                $val['loan_issue_mode'] = 'Cash';
                                break;
                            case 1:
                                $val['loan_issue_mode'] = 'Cheque';
                                break;
                            case 2:
                                $val['loan_issue_mode'] = 'DD';
                                break;
                            case 3:
                                $val['loan_issue_mode'] = 'Online Transaction';
                                break;
                            case 4:
                                $val['loan_issue_mode'] = 'SSB';
                                break;
                            case 5:
                                $val['loan_issue_mode'] = 'From Loan Amount';
                                break;
                        }
                    } else {
                        $val['loan_issue_mode'] = 'N/A';
                    }
                    $val['cheque_no'] = $mode ? $mode->cheque_dd_no ?? 'N/A' : 'N/A';

                    $val['total_recovery_amount'] = LoanDayBooksAmount($row['loan_type'], $row['account_number']) . ' &#8377';
                    $lastEmi = LoanDayBooks::where('loan_type', $row['loan_type'])->where('loan_id', $row['id'])->where('loan_sub_type', '!=', '2')->where('is_deleted', 0)->orderby('created_at', 'desc')->first('created_at');
                    if (isset($lastEmi->created_at)) {
                        $val['total_recovery_emi_till_date'] = date('d/m/Y', strtotime($lastEmi->created_at));
                    } else {
                        $val['total_recovery_emi_till_date'] = 'N/A';
                    }
                    if (isset($row['emi_option'])) {
                        if ($row['emi_option'] == 1) {
                            $closingAmountROI = $row['due_amount'] * $row['ROI'] / 1200;
                        } elseif ($row['emi_option'] == 2) {
                            $closingAmountROI = $row['due_amount'] * $row['ROI'] / 5200;
                        } elseif ($row['emi_option'] == 3) {
                            $closingAmountROI = $row['due_amount'] * $row['ROI'] / 36500;
                        }
                    } else {
                        $closingAmountROI = 0;
                    }
                    if (isset($row['due_amount'])) {
                        $closingAmount = round($row['due_amount'] + $closingAmountROI);
                    } else {
                        $closingAmount = 0;
                    }
                    // $outstandingAmount = isset($row['get_outstanding']['out_standing_amount'])
                    //     ? ($row['get_outstanding']['out_standing_amount'] > 0 ? $row['get_outstanding']['out_standing_amount'] : 0)
                    //     : $row['amount'];
                    
                    $outstandingAmount = getClosingAmountByLoan($row['id'],($row['loan']['loan_type']=='G'?false:true))
                         ? ((getClosingAmountByLoan($row['id'],($row['loan']['loan_type']=='G'?false:true)) > 0 )
                            ? getClosingAmountByLoan($row['id'],($row['loan']['loan_type']=='G'?false:true)) 
                            : 0) 
                         : $row['amount'];

                    // $outstandingAmount = isset($row['getOutstanding']['out_standing_amount']) ? ($row['getOutstanding']['out_standing_amount'] > 0 ? $row['getOutstanding']['out_standing_amount'] : 0) : $row['amount'];
                   
                    $val['closing_amount'] = $outstandingAmount . ' &#8377';
                    $loanComplateDate = Carbon::createFromFormat('Y-m-d', date('Y-m-d'));

                    $dueStartDate = $row['approve_date'] ? Carbon::createFromFormat('Y-m-d', $row['approve_date']) : Carbon::createFromFormat('Y-m-d', date('Y-m-d'));

                    if ($row['emi_option'] == 1) {
                        $dueTime = $dueStartDate->floatDiffInMonths($loanComplateDate);
                        $firstEmiDate = $dueStartDate->addMonth(1);
                    }
                    if ($row['emi_option'] == 2) {
                        $dueTime = $dueStartDate->diffInWeeks($loanComplateDate);
                        $firstEmiDate = $dueStartDate->addWeek(1);
                    }
                    if ($row['emi_option'] == 3) {
                        $dueTime = $dueStartDate->diffInDays($loanComplateDate);
                        $firstEmiDate = $dueStartDate->addDays(1);

                    }

                    $cAmount = round($dueTime * $row['emi_amount']);
                    $ramount = LoanDayBooks::where('loan_type', $row['loan_type'])->where('loan_id', $row['id'])->where('loan_sub_type', '!=', 2)->where('is_deleted', 0)->sum('deposit');

                    if ($ramount < $cAmount) {
                        $val['balance_emi'] = 'Yes';
                    } else {
                        $val['balance_emi'] = 'No';
                    }

                    $val['emi_should_be_received_till_date'] = $cAmount . ' &#8377';

                    $val['future_emi_due_till_date'] = ($cAmount - $ramount < 0) ? 0 : $cAmount - $ramount . ' &#8377';
                    $val['date'] = date('d/m/Y');
                    if (isset($row['associate_member_id'])) {
                        if (isset($row['associate_member_id'])) {

                            $val['co_applicant_name'] = customGetMemberData($row['associate_member_id'])->first_name . ' ' . customGetMemberData($row['associate_member_id'])->last_name;
                        } else {
                            $val['co_applicant_name'] = 'N/A';
                        }
                    } else {
                        $val['co_applicant_name'] = 'N/A';
                    }
                    if (isset($row['associate_member_id'])) {
                        if (isset($row['associate_member_id'])) {
                            $val['co_applicant_number'] = customGetMemberData($row['associate_member_id'])->mobile_no;
                        } else {
                            $val['co_applicant_number'] = 'N/A';
                        }
                    } else {
                        $val['co_applicant_number'] = 'N/A';
                    }
                    if (isset($row['loan_guarantor'][0]['member_id'])) {
                        if ((customGetMemberData($row['loan_guarantor'][0]['member_id']))) {
                            $val['gname'] = customGetMemberData($row['loan_guarantor'][0]['member_id'])->first_name . ' ' . customGetMemberData($row['loan_guarantor'][0]['member_id'])->last_name;
                        } else {
                            $val['gname'] = 'N/A';
                        }
                    } elseif (isset($row['loan_guarantor'][0]['member_loan_id'])) {
                        $val['gname'] = $row['loan_guarantor'][0]['name'];
                    } else {
                        $val['gname'] = 'N/A';
                    }
                    if (isset($row['loan_guarantor'][0]['mobile_number'])) {
                        if (isset($row['loan_guarantor'][0]['mobile_number'])) {
                            $val['gnumber'] = ($row['loan_guarantor'][0]['mobile_number']);
                        } else {
                            $val['gnumber'] = 'N/A';
                        }
                    } else {
                        $val['gnumber'] = 'N/A';
                    }
                    if (isset($row['loan_applicants'])) {
                        if (count($row['loan_applicants']) > 0) {
                            if ((customGetMemberData($row['loan_applicants'][0]['member_id']))) {
                                $val['applicant_address'] = preg_replace("/\r|\n/", "", customGetMemberData($row['loan_applicants'][0]['member_id'])->address);
                            } else {
                                $val['applicant_address'] = isset($row['member']['address']) ? preg_replace("/\r|\n/", "", $row['member']['address']) : 'N/A';
                            }
                        } else {
                            $val['applicant_address'] = 'N/A';
                        }
                    } else {
                        $val['applicant_address'] = 'N/A';
                    }
                    // $record = LoanDayBooks::where('loan_type', $row['loan_type'])->where('loan_id', $row['id'])->where('loan_sub_type','')->where('is_deleted', 0)->orderby('created_at', 'asc')->first('created_at');
                    // if ($record && isset($record)) {
                    //     $val['first_emi_date'] = date("d/m/Y", strtotime(convertDate($record->created_at)));
                    // } else {
                    //     $val['first_emi_date'] = 'N/A';
                    // }
                    $val['first_emi_date'] = date('d/m/Y', strtotime($firstEmiDate));
                    if (isset($row['approve_date'])) {
                        if (isset($row['emi_option'])) {
                            if ($row['emi_option'] == 1) {
                                $last_recovery_date = date('d/m/Y', strtotime("+" . $row['emi_period'] . " months", strtotime($row['approve_date'])));
                            } elseif ($row['emi_option'] == 2) {
                                $days = $row['emi_period'] * 7;
                                $start_date = $row['approve_date'];
                                $date = strtotime($start_date);
                                $date = strtotime("+" . $days . " day", $date);
                                $last_recovery_date = date('d/m/Y', $date);
                            } elseif ($row['emi_option'] == 3) {
                                $days = $row['emi_period'];
                                $start_date = $row['approve_date'];
                                $date = strtotime($start_date);
                                $date = strtotime("+" . $days . " day", $date);
                                $last_recovery_date = date('d/m/Y', $date);
                            }
                        } else {
                            $last_recovery_date = 'N/A';
                        }
                    } else {
                        $last_recovery_date = 'N/A';
                    }
                    $val['loan_end_date'] = $last_recovery_date;
                    // $amount = LoanDayBooks::where('loan_type', $row['loan_type'])->where('loan_id', $row['id'])->where('is_deleted', 0)->sum('deposit');
                    // $val['total_deposit_till_date'] = $amount . ' &#8377';
                    $rowReturn[] = $val;
                }
                if (isset($loannew)) {
                    $token = session()->get('_token');
                    //Set value on caches 
                    Cache::put('loanReportListAdmin' . $token, $loannew);
                    Cache::put('loanReportListCountAdmin' . $token, count($loannew));
                    //End Set value on caches
                }
                $output = array("draw" => $_POST['draw'], "recordsTotal" => $totalCount, "recordsFiltered" => $count, "data" => $rowReturn);
                return json_encode($output);
            }
        }
    }
    public function groupLoan()
    {
        $data['title'] = 'Report | Group Loan  Report';
        $data['branch'] = Branch::where('status', 1)->get();
        return view('templates.admin.report.grouploan', $data);
    }
    public function groupLoanListing(Request $request)
    {
        if ($request->ajax()) {
            $arrFormData = array();
            if (!empty($_POST['searchform'])) {
                foreach ($_POST['searchform'] as $frm_data) {
                    $arrFormData[$frm_data['name']] = $frm_data['value'];
                }
            }
            $data = Grouploans::select('id', 'status', 'member_id', 'account_number', 'branch_id', 'applicant_id', 'deposite_amount', 'approve_date', 'emi_amount', 'emi_period', 'emi_option', 'loan_type', 'due_amount', 'ROI', 'created_at')
                ->with([
                    'LoanApplicants' => function ($q) {
                        $q->select('id', 'member_id');
                    }
                ])
                ->with([
                    'LoanCoApplicants' => function ($q) {
                        $q->select('id', 'member_id');
                    }
                ])
                ->with([
                    'LoanGuarantor' => function ($q) {
                        $q->select('id', 'member_id');
                    }
                ]);
            if (isset($arrFormData['is_search']) && $arrFormData['is_search'] == 'yes') {
                if ($arrFormData['start_date'] != '') {
                    $startDate = date("Y-m-d", strtotime(convertDate($arrFormData['start_date'])));
                    if ($arrFormData['end_date'] != '') {
                        $endDate = date("Y-m-d ", strtotime(convertDate($arrFormData['end_date'])));
                    } else {
                        $endDate = '';
                    }
                    $data = $data->whereBetween('created_at', [$startDate, $endDate]);
                }
                if ($arrFormData['branch_id'] != '') {
                    $branch_id = $arrFormData['branch_id'];
                    $data = $data->where('branch_id', '=', $branch_id);
                }
                if ($arrFormData['status'] != '') {
                    $status = $arrFormData['status'];
                    $data = $data->where('status', '=', $status);
                }
                if ($arrFormData['application_number'] != '') {
                    $application_number = $arrFormData['application_number'];
                    $data = $data->where('account_number', '=', $application_number);
                }
                if ($arrFormData['member_id'] != '') {
                    $member_id = $arrFormData['member_id'];
                    $data = $data->whereHas('loanMember', function ($query) use ($member_id) {
                        $query->where('members.member_id', 'LIKE', '%' . $member_id . '%');
                    });
                }
            }
            $data = $data->orderby('id', 'DESC')->get();
            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('status', function ($row) {
                    if ($row->status == 0) {
                        return 'Inactive';
                    } elseif ($row->status == 1) {
                        return 'Approved';
                    } elseif ($row->status == 2) {
                        return 'Rejected';
                    } elseif ($row->status == 3) {
                        return 'Completed';
                    } elseif ($row->status == 4) {
                        return 'ONGOING';
                    }
                })
                ->rawColumns(['status'])
                ->addColumn('applicant_name', function ($row) {
                    if (count($row['LoanApplicants']) > 0) {
                        if (customGetMemberData($row['LoanApplicants'][0]->member_id)) {
                            return customGetMemberData($row['LoanApplicants'][0]->member_id)->first_name . ' ' . customGetMemberData($row['LoanApplicants'][0]->member_id)->last_name;
                        } else {
                            return 'N/A';
                        }
                    } else {
                        return 'N/A';
                    }
                })
                ->rawColumns(['applicant_name'])
                ->addColumn('applicant_phone_number', function ($row) {
                    if (count($row['LoanApplicants']) > 0) {
                        if (customGetMemberData($row['LoanApplicants'][0]->member_id)) {
                            return customGetMemberData($row['LoanApplicants'][0]->member_id)->mobile_no;
                        } else {
                            return 'N/A';
                        }
                    } else {
                        return 'N/A';
                    }
                })
                ->rawColumns(['applicant_phone_number'])
                ->addColumn('membership_id', function ($row) {
                    return 'N/A';
                })
                ->rawColumns(['membership_id'])
                ->addColumn('account_number', function ($row) {
                    return $row->account_number;
                })
                ->rawColumns(['account_number'])
                ->addColumn('branch', function ($row) {
                    return customGetBranchDetail($row->branch_id)->name;
                })
                ->rawColumns(['branch'])
                ->addColumn('sector', function ($row) {
                    return customGetBranchDetail($row->branch_id)->sector;
                })
                ->rawColumns(['sector'])
                ->addColumn('member_id', function ($row) {
                    return customGetMemberData($row->applicant_id)->member_id;
                })
                ->rawColumns(['member_id'])
                ->addColumn('sanctioned_amount', function ($row) {
                    return $row->deposite_amount . ' &#8377';
                })
                ->escapeColumns(['co_applicant_number'])
                ->addColumn('sanctioned_date', function ($row) {
                    if ($row->approve_date) {
                        return date("d/m/Y", strtotime(convertDate($row->approve_date)));
                    } else {
                        return 'N/A';
                    }
                })
                ->rawColumns(['sanctioned_date'])
                ->addColumn('emi_rate', function ($row) {
                    return $row->emi_amount;
                })
                ->rawColumns(['emi_rate'])
                ->addColumn('no_of_installement', function ($row) {
                    return $row->emi_period;
                })
                ->rawColumns(['no_of_installement'])
                ->addColumn('loan_mode', function ($row) {
                    if ($row->emi_option == 1) {
                        return 'Months';
                    } elseif ($row->emi_option == 2) {
                        return 'Weeks';
                    } elseif ($row->emi_option == 3) {
                        return 'Daily';
                    }
                })
                ->rawColumns(['loan_mode'])
                ->addColumn('loan_type', function ($row) {
                    return $row['loan']->name;
                })
                ->rawColumns(['loan_type'])
                ->addColumn('loan_issue_date', function ($row) {
                    return date("d/m/Y", strtotime(convertDate($row->created_at)));
                })
                ->rawColumns(['loan_issue_date'])
                ->addColumn('loan_issue_mode', function ($row) {
                    $mode = Daybook::whereIn('transaction_type', [3, 8])->where('loan_id', $row->id)->orderby('id', 'ASC')->first('payment_mode');
                    if ($mode) {
                        if ($mode->payment_mode == 1) {
                            return 'Cash';
                        } elseif ($mode->payment_mode == 2) {
                            return 'Cheque';
                        } elseif ($mode->payment_mode == 3) {
                            return 'DD';
                        } elseif ($mode->payment_mode == 4) {
                            return 'Online Transaction';
                        } elseif ($mode->payment_mode == 5) {
                            return 'SSB';
                        }
                    } else {
                        return 'N/A';
                    }
                })
                ->rawColumns(['loan_issue_mode'])
                ->addColumn('cheque_no', function ($row) {
                    $mode = Daybook::whereIn('transaction_type', [3, 8])->where('loan_id', $row->id)->orderby('id', 'ASC')->first('cheque_dd_no');
                    if ($mode) {
                        return $mode->cheque_dd_no;
                    } else {
                        return 'N/A';
                    }
                })
                ->rawColumns(['cheque_no'])
                ->addColumn('total_recovery_amount', function ($row) {
                    $amount = LoanDayBooks::where('loan_type', $row->loan_type)->where('loan_id', $row->id)->sum('deposit');
                    return $amount . ' &#8377';
                })
                ->escapeColumns(['co_applicant_number'])
                ->addColumn('total_recovery_emi_till_date', function ($row) {
                    return $row->credit_amount . ' &#8377';
                })
                ->escapeColumns(['co_applicant_number'])
                ->addColumn('closing_amount', function ($row) {
                    if ($row->emi_option == 1) {
                        $closingAmountROI = $row->due_amount * $row->ROI / 1200;
                    } elseif ($row->emi_option == 2) {
                        $closingAmountROI = $row->due_amount * $row->ROI / 5200;
                    } elseif ($row->emi_option == 3) {
                        $closingAmountROI = $row->due_amount * $row->ROI / 36500;
                    }
                    $closingAmount = round($row->due_amount + $closingAmountROI);
                    return $closingAmount . ' &#8377';
                })
                ->escapeColumns(['co_applicant_number'])
                ->addColumn('balance_emi', function ($row) {
                    $d1 = strtotime( $row->created_at);
                    $d2 = strtotime(date("Y-m-d"));
                    $firstMonth = date("m", $d1);
                    $secondMonth = date("m", $d2);
                    $monthDiff = $secondMonth - $firstMonth;
                    $ramount = LoanDayBooks::where('loan_type', $row->loan_type)->where('loan_id', $row->id)->sum('deposit');
                    $camount = $monthDiff * $row->emi_amount;
                    if ($ramount < $camount) {
                        return 'Yes';
                    } else {
                        return 'No';
                    }
                })
                ->rawColumns(['balance_emi'])
                ->addColumn('emi_should_be_received_till_date', function ($row) {
                    $d1 = strtotime( $row->created_at);
                    $d2 = strtotime(date("Y-m-d"));
                    $firstMonth = date("m", $d1);
                    $secondMonth = date("m", $d2);
                    $monthDiff = $secondMonth - $firstMonth;
                    $camount = $monthDiff * $row->emi_amount;
                    return $camount . ' &#8377';
                })
                ->escapeColumns(['co_applicant_number'])
                ->addColumn('future_emi_due_till_date', function ($row) {
                    $d1 = strtotime( $row->created_at);
                    $d2 = strtotime(date("Y-m-d"));
                    $firstMonth = date("m", $d1);
                    $secondMonth = date("m", $d2);
                    $monthDiff = $secondMonth - $firstMonth;
                    $camount = $monthDiff * $row->emi_amount;
                    return $camount . ' &#8377';
                })
                ->escapeColumns(['co_applicant_number'])
                ->addColumn('date', function ($row) {
                    return date("d/m/Y");
                })
                ->rawColumns(['date'])
                ->addColumn('co_applicant_name', function ($row) {
                    if (count($row['LoanCoApplicants']) > 0) {
                        if (customGetMemberData($row['LoanCoApplicants'][0]->member_id)) {
                            return customGetMemberData($row['LoanCoApplicants'][0]->member_id)->first_name . ' ' . customGetMemberData($row['LoanCoApplicants'][0]->member_id)->last_name;
                        } else {
                            return 'N/A';
                        }
                    } else {
                        return 'N/A';
                    }
                })
                ->rawColumns(['co_applicant_name'])
                ->addColumn('co_applicant_number', function ($row) {
                    if (count($row['LoanCoApplicants']) > 0) {
                        if (customGetMemberData($row['LoanCoApplicants'][0]->member_id)) {
                            return customGetMemberData($row['LoanCoApplicants'][0]->member_id)->mobile_no;
                        } else {
                            return 'N/A';
                        }
                    } else {
                        return 'N/A';
                    }
                })
                ->rawColumns(['co_applicant_number'])
                ->addColumn('gurantor_name', function ($row) {
                    if (count($row['LoanGuarantor']) > 0) {
                        if (customGetMemberData($row['LoanGuarantor'][0]->member_id)) {
                            return customGetMemberData($row['LoanGuarantor'][0]->member_id)->first_name . ' ' . customGetMemberData($row['LoanGuarantor'][0]->member_id)->last_name;
                        } else {
                            return 'N/A';
                        }
                    } else {
                        return 'N/A';
                    }
                })
                ->rawColumns(['gurantor_name'])
                ->addColumn('gurantor_number', function ($row) {
                    if (count($row['LoanGuarantor']) > 0) {
                        if (customGetMemberData($row['LoanGuarantor'][0]->member_id)) {
                            return customGetMemberData($row['LoanGuarantor'][0]->member_id)->mobile_no;
                        } else {
                            return 'N/A';
                        }
                    } else {
                        return 'N/A';
                    }
                })
                ->rawColumns(['gurantor_number'])
                ->addColumn('applicant_address', function ($row) {
                    if (count($row['LoanApplicants']) > 0) {
                        if (customGetMemberData($row['LoanApplicants'][0]->member_id)) {
                            return preg_replace("/\r|\n/", "", customGetMemberData($row['LoanApplicants'][0]->member_id)->address);
                        } else {
                            return 'N/A';
                        }
                    } else {
                        return 'N/A';
                    }
                })
                ->rawColumns(['applicant_address'])
                ->addColumn('first_emi_date', function ($row) {
                    $record = LoanDayBooks::where('loan_type', $row->loan_type)->where('loan_id', $row->id)->orderby('created_at', 'asc')->first('created_at');
                    if ($record && isset($record)) {
                        return date("d/m/Y", strtotime(convertDate($record->created_at)));
                    } else {
                        return 'N/A';
                    }
                })
                ->rawColumns(['first_emi_date'])
                ->addColumn('loan_end_date', function ($row) {
                    if ($row->approve_date) {
                        if ($row->emi_option == 1) {
                            $last_recovery_date = date('d/m/Y', strtotime("+" . $row->emi_period . " months", strtotime($row->approve_date)));
                        } elseif ($row->emi_option == 2) {
                            $days = $row->emi_period * 7;
                            $start_date = $row->approve_date;
                            $date = strtotime($start_date);
                            $date = strtotime("+" . $days . " day", $date);
                            $last_recovery_date = date('d/m/Y', $date);
                        } elseif ($row->emi_option == 3) {
                            $days = $row->emi_period;
                            $start_date = $row->approve_date;
                            $date = strtotime($start_date);
                            $date = strtotime("+" . $days . " day", $date);
                            $last_recovery_date = date('d/m/Y', $date);
                        }
                    } else {
                        $last_recovery_date = 'N/A';
                    }
                    return $last_recovery_date;
                })
                ->rawColumns(['loan_end_date'])
                ->addColumn('total_deposit_till_date', function ($row) {
                    $amount = LoanDayBooks::where('loan_type', $row->loan_type)->where('loan_id', $row->id)->sum('deposit');
                    return $amount . ' &#8377';
                })
                ->escapeColumns(['co_applicant_number'])
                ->make(true);
        }
    }
    public function maturity()
    {
        if (check_my_permission(Auth::user()->id, "126") != "1") {
            return redirect()->route('admin.dashboard');
        }
        $data['title'] = 'Report | Maturity  Report';
        $data['branch'] = Branch::where('status', 1)->get(['id', 'name', 'branch_code']);
        $data['plans'] = Plans::has('company')->where('status', 1)->where('plan_code', '!=', 703)->pluck('name', 'id');
        return view('templates.admin.report.new_maturity', $data);
    }
    public function maturityplans(Request $request)
    {
        $company_id = $request['company_id'];
        $plans = Plans::has('company')
            ->where('plan_category_code', '!=', 'S')
            ->when($company_id > 0, function ($q) use ($company_id) {
                $q->where('company_id', $company_id);
            })
            ->get(['name', 'id', 'plan_category_code', 'plan_sub_category_code']);
        $return_array = compact('plans');
        return json_encode($return_array);
    }
    public function maturityReportListing(Request $request)
    {
        if ($request->ajax()) {
            $arrFormData = array();
            $arrFormData['start_date'] = $request->start_date;
            $arrFormData['end_date'] = $request->end_date;
            $arrFormData['branch_id'] = $request->branch_id;
            $arrFormData['payment_type'] = $request->payment_type;
            $arrFormData['payment_mode'] = $request->payment_mode;
            $arrFormData['is_search'] = $request->is_search;
            $data = Memberinvestments::has('company')->select('company_id', 'customer_id', 'id', 'member_id', 'plan_id', 'branch_id', 'account_number', 'created_at', 'maturity_date', 'is_mature', 'deposite_amount', 'tenure', 'associate_id', 'due_amount', 'company_id', 'customer_id')->with([
                'member' => function ($q) {
                    $q->select('id', 'member_id', 'first_name', 'last_name', 'created_at');
                }
            ])
                ->with([
                    'member' => function ($q) {
                        $q->select('id', 'member_id', 'first_name', 'last_name');
                    },
                    'company' => function ($q) {
                        $q->select('id', 'name');
                    },
                    'memberCompany' => function ($q) {
                        $q->select('id', 'member_id');
                    },
                    'associateMember' => function ($q) {
                        $q->select('id', 'associate_no', 'associate_code', 'first_name', 'last_name');
                    },
                    'demandadvice' => function ($q) {
                        $q->select('id', 'date', 'tds_amount', 'maturity_prematurity_amount', 'final_amount', 'payment_type', 'payment_mode', 'bank_name', 'investment_id', 'maturity_amount_payable', 'bank_account_number', 'bank_name')->with([
                            'demandAmountHead' => function ($q) {
                                $q->select('id', 'amount', 'head_id', 'type_id');
                            },
                            'demandAmount' => function ($q) {
                                $q->select('id', 'amount', 'head_id', 'type_id', 'type', 'sub_type', 'amount', 'cheque_no');
                            },
                            'demandTransactionAmount' => function ($q) {
                                $q->select('id', 'amount', 'head_id', 'type_id', 'type', 'sub_type', 'amount', 'transction_no');
                            }
                        ]);
                    },
                    'branch' => function ($q) {
                        $q->select('id', 'name', 'branch_code', 'zone');
                    },
                    'plan' => function ($q) {
                        $q->select('id', 'name');
                    },
                    'sumdeposite',
                    'TransactionTypeDate' => function ($q) {
                        $q->select('id', 'investment_id', 'created_at');
                    }
                ])->where('plan_id', '!=', 1);
            if (Auth::user()->branch_id > 0) {
                $id = Auth::user()->branch_id;
                $data = $data->where('branch_id', '=', $id);
            }
            /******* fillter query start ****/
            if (isset($request['is_search']) && $request['is_search'] == 'yes') {
                if (isset($request['branch_id']) && $request['branch_id'] > 0) {
                    $bid = $request['branch_id'];
                    $data = $data->where('branch_id', $bid);
                }
                if ($request['plan_id'] != '') {
                    $planId = $request['plan_id'];
                    $data = $data->where('plan_id', '=', $planId);
                }
                if ($request['company_id'] > 0 && $request['company_id']) {
                    $company_id = $request['company_id'];
                    $data = $data->where('company_id', $company_id);
                }
                if ($request['member_id'] != '') {
                    $meid = $request['member_id'];
                    $data = $data->whereHas('memberCompany', function ($query) use ($meid) {
                        $query->where('member_companies.member_id', 'LIKE', '%' . $meid . '%');
                    });
                }
                if ($request['associate_code']) {
                    $associate_code = $request['associate_code'];
                    $data = $data->whereHas('associateMember', function ($query) use ($associate_code) {
                        $query->where('members.associate_no', 'Like', '%' . $associate_code . '%');
                    });
                }
                if ($request['scheme_account_number']) {
                    $scheme_account_number = $request['scheme_account_number'];
                    $data = $data->where('account_number', 'Like', '%' . $scheme_account_number . '%');
                }
                if ($request['name'] != '') {
                    $name = $request['name'];
                    $data = $data->whereHas('member', function ($query) use ($name) {
                        $query->where('members.first_name', 'LIKE', '%' . $name . '%')->orWhere('members.last_name', 'LIKE', '%' . $name . '%')->orWhere(DB::raw('concat(members.first_name," ",members.last_name)'), 'LIKE', "%$name%");
                    });
                }
                if ($request['start_date'] != '' && $request['status'] == '') {
                    $startDate = date("Y-m-d", strtotime(convertDate($request['start_date'])));
                    if ($request['end_date'] != '') {
                        $endDate = date("Y-m-d ", strtotime(convertDate($request['end_date'])));
                    } else {
                        $endDate = '';
                    }
                    $data = $data->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate]);
                }
                if ($request['status'] != '' && ($request['start_date'] != '' || $request['end_date'] != '') || ($request['start_date'] == Null || $request['end_date'] == Null)) {
                    $status = $request['status'];
                    $Date = date('Y-m-d');
                    if ($request['status'] == 0) {
                        if ($request['start_date'] != '') {
                            $startDate = date("Y-m-d", strtotime(convertDate($request['start_date'])));
                            $startDateMonth = date("m", strtotime(convertDate($request['start_date'])));
                            $startDateYear = date("Y", strtotime(convertDate($request['start_date'])));
                            if ($arrFormData['end_date'] != '') {
                                $endDate = date("Y-m-d ", strtotime(convertDate($request['end_date'])));
                            } else {
                                $endDate = '';
                            }
                            $currentDate = date("Y-m-d", strtotime(convertDate($Date)));
                            $currentDateMonth = date("m", strtotime(convertDate($Date)));
                            $currentDateYear = date("Y", strtotime(convertDate($Date)));
                            if ($startDateMonth == $currentDateMonth && $currentDateYear == $startDateYear) {
                                $data = $data->whereDate('maturity_date', '>', $currentDate)->whereDate('maturity_date', '<=', $endDate);
                            } elseif ($startDateMonth > $currentDateMonth) {
                                $data = $data->whereBetween('maturity_date', [$startDate, $endDate]);
                            } else {
                                $data = $data->where('maturity_date', '');
                            }
                        }
                    } elseif ($request['status'] == 1) {
                        if ($request['start_date'] != '') {
                            $startDate = date("Y-m-d", strtotime(convertDate($request['start_date'])));
                            $startDateMonth = date("m", strtotime(convertDate($request['start_date'])));
                            $startDateYear = date("Y", strtotime(convertDate($request['start_date'])));
                            if ($request['end_date'] != '') {
                                $endDate = date("Y-m-d ", strtotime(convertDate($request['end_date'])));
                            } else {
                                $endDate = '';
                            }
                            $currentDate = date("Y-m-d", strtotime(convertDate($Date)));
                            $currentDateMonth = date("m", strtotime(convertDate($Date)));
                            $currentDateYear = date("Y", strtotime(convertDate($Date)));
                            if ($startDateMonth == $currentDateMonth && $currentDateYear == $startDateYear) {
                                $data->whereHas('demandadvice', function ($query) use ($startDate, $endDate, $currentDate) {
                                    $query->where('status', 1);
                                })->whereBetween(\DB::raw('DATE(maturity_date)'), [$startDate, $currentDate])->where('is_mature', 0);
                            } elseif ($startDateMonth < $currentDateMonth) {
                                $data->whereHas('demandadvice', function ($query) use ($startDate, $endDate, $currentDate) {
                                    $query->whereBetween(\DB::raw('DATE(demand_advices.date)'), [$startDate, $endDate])->where('status', 1);
                                })->where('is_mature', 0);
                            } else {
                                $data->whereHas('demandadvice', function ($query) {
                                    $query->where('status', 1);
                                })->where('maturity_date', '<', '');
                            }
                        }
                        if ($request['end_date'] != '' && $request['start_date'] == Null) {
                            $endDate = date("Y-m-d ", strtotime(convertDate($request['end_date'])));
                            $data->whereHas('demandadvice', function ($query) use ($endDate) {
                                $query->where('status', 1);
                            })->whereDate(\DB::raw('DATE(maturity_date)'), '<=', $endDate)->where('is_mature', 0);
                        }
                        if ($request['end_date'] == NUll && $request['start_date'] == Null) {
                            $currentDate = date("Y-m-d", strtotime(convertDate($Date)));
                            $endDate = date("Y-m-d ", strtotime(convertDate($request['end_date'])));
                            $data->whereHas('demandadvice', function ($query) use ($endDate) {
                                $query->where('status', 1);
                            })->whereDate(\DB::raw('DATE(maturity_date)'), '<=', $currentDate)->where('is_mature', 0);
                        }
                    } elseif ($request['status'] == 2) {
                        if ($request['start_date'] != '') {
                            $startDate = date("Y-m-d", strtotime(convertDate($request['start_date'])));
                            $startDateMonth = date("m", strtotime(convertDate($request['start_date'])));
                            $startDateYear = date("Y", strtotime(convertDate($request['start_date'])));
                            if ($arrFormData['end_date'] != '') {
                                $endDate = date("Y-m-d ", strtotime(convertDate($request['end_date'])));
                            } else {
                                $endDate = '';
                            }
                            $currentDate = date("Y-m-d", strtotime(convertDate($Date)));
                            $currentDateMonth = date("m", strtotime(convertDate($Date)));
                            $currentDateYear = date("Y", strtotime(convertDate($Date)));
                            if ($startDateMonth == $currentDateMonth && $currentDateYear == $startDateYear) {
                                $data->where('is_mature', 0)->whereBetween('maturity_date', [$startDate, $currentDate])->orWhere(function ($q) use ($startDate, $currentDate) {
                                    $q->whereHas('demandadvice', function ($query) use ($startDate, $currentDate) {
                                        $query->where('status', 0)->whereBetween(\DB::raw('DATE(maturity_date)'), [$startDate, $currentDate]);
                                    });
                                });
                            } elseif ($startDateMonth < $currentDateMonth) {
                                $data->where('is_mature', 0)->whereBetween('maturity_date', [$startDate, $endDate])->orWhere(function ($q) use ($startDate, $endDate) {
                                    $q->whereHas('demandadvice', function ($query) use ($startDate, $endDate) {
                                        $query->where('status', 0)->whereBetween(\DB::raw('DATE(member_investments.maturity_date)'), [$startDate, $endDate]);
                                    });
                                });
                            } else {
                                $data->whereHas('demandadvice', function ($query) {
                                    $query->where('status', 0);
                                })->where('maturity_date', '<', '');
                            }
                        }
                    }
                }
            }
            /******* fillter query End ****/
            $data1 = $data->count('id');
            $count = $data1;
            $data = $data->orderby('created_at', 'DESC')->offset($_POST['start'])->limit($_POST['length'])->get();
            $totalCount = $count;
            $sno = $_POST['start'];
            $rowReturn = array();
            foreach ($data as $row) {
                if ($row['sumdeposite']->sum('deposit')) {
                    $current_balance = $row['sumdeposite']->sum('deposit');
                } else {
                    $current_balance = 0;
                }
                $sno++;
                $val['DT_RowIndex'] = $sno;
                $val['branch'] = $row['branch']->name;
                $val['company_name'] = $row['company']->name;
                $val['customer_id'] = 'N/A';
                if (isset($row['member']['member_id'])) {
                    $val['customer_id'] = $row['member']['member_id'];
                }
                if (isset($row['memberCompany'])) {
                    $val['member_id'] = $row['memberCompany']['member_id']; //customGetBranchDetail($row->branch_id)->sector;
                } else {
                    $val['member_id'] = 'N/A';
                }
                $val['account_no'] = $row->account_number;
                if ($row['member']) {
                    $val['member_name'] = $row['member']->first_name . ' ' . $row['member']->last_name;
                } else {
                    $val['member_name'] = 'N/A';
                }
                if ($row['member']) {
                    $val['customer_id'] = $row['member']->member_id;
                } else {
                    $val['customer_id'] = 'N/A';
                }
                if ($row['memberCompany']) {
                    $val['member_id'] = $row['memberCompany']->member_id;
                } else {
                    $val['member_id'] = 'N/A';
                }
                $val['plan_name'] = $row['plan']->name;
                $val['deno'] = "&#8377;" . number_format((float) $row->deposite_amount, 2, '.', '');
                $val['maturity_amount'] = "&#8377;" . number_format((float) $row->maturity_amount, 2, '.', '');
                if (isset($row['demandadvice']->tds_amount)) {
                    $val['tds_amount'] = "&#8377;" . number_format((float) $row['demandadvice']->tds_amount, 2, '.', '');
                } else {
                    $val['tds_amount'] = 'N/A';
                }
                if (isset($current_balance)) {
                    $val['deposit_amount'] = $current_balance;
                } else {
                    $val['deposit_amount'] = 'N/A';
                }
                if ($row['associateMember']) {
                    if (isset($row['associateMember']->associate_no)) {
                        $val['associate_code'] = $row['associateMember']->associate_no;
                    } else {
                        $val['associate_code'] = 'N/A';
                    }
                } else {
                    $val['associate_code'] = 'N/A';
                }
                if ($row) {
                    if (isset($row['associateMember'])) {
                        if (isset($row['associateMember']->first_name) && isset($row['associateMember']->last_name)) {
                            $val['associate_name'] = $row['associateMember']->first_name . ' ' . $row['associateMember']->last_name;
                        } else {
                            $val['associate_name'] = $row['associateMember']->first_name;
                        }
                    } else {
                        $val['associate_name'] = 'N/A';
                    }
                } else {
                    $val['associate_name'] = 'N/A';
                }
                $val['opening_date'] = date("d/m/Y", strtotime($row->created_at));
                $val['due_amount'] = "&#8377;" . number_format((float) $row->due_amount, 2, '.', '');
                $amount = '';
                if ($row['demandadvice']) {
                    // $investmentAmount = Daybook::where('investment_id',$row->investment_id)->whereIn('transaction_type',[2,4])->sum('deposit');
                    // Sachin sir ne change karaya 14-03-2022
                    $tds = 0;
                    if (isset($row['demandadvice']->tds_amount)) {
                        $tds = $row['demandadvice']->tds_amount;
                    }
                    $amount = round($row['demandadvice']->final_amount - $row['demandadvice']->maturity_prematurity_amount + $tds) . ' &#8377';
                }
                $val['roi'] = $amount;
                if (isset($row['demandadvice']->final_amount)) {
                    $val['total_amount'] = "&#8377;" . number_format((float) $row['demandadvice']->final_amount, 2, '.', '');
                    ;
                } else {
                    $val['total_amount'] = 'N/A';
                }
                if ($row['demandadvice']) {
                    if ($row['demandadvice']->payment_type == 0) {
                        $val['maturity_type'] = 'Expense';
                    } elseif ($row['demandadvice']->payment_type == 1) {
                        $val['maturity_type'] = 'Maturity';
                    } elseif ($row['demandadvice']->payment_type == 2) {
                        $val['maturity_type'] = 'PreMaturity';
                    } elseif ($row['demandadvice']->payment_type == 3) {
                        $val['maturity_type'] = 'Death Help';
                    } elseif ($row['demandadvice']->payment_type == 4) {
                        $val['maturity_type'] = 'Emergancy Maturity';
                    } else {
                        $val['maturity_type'] = 'N/A';
                    }
                } else {
                    $val['maturity_type'] = 'N/A';
                }
                if ($row->maturity_date) {
                    $val['maturity_date'] = $val['maturity_date'] = date('d/m/Y', strtotime($row->created_at . ' + ' . ($row->tenure) . ' year'));
                } else {
                    $val['maturity_date'] = "N/A";
                }
                if ($row->tenure) {
                    $val['tenure'] = $row->tenure;
                } else {
                    $val['tenure'] = "N/A";
                }
                if ($row['demandadvice']) {
                    $val['maturity_payable_amount'] = $row['demandadvice']->maturity_amount_payable . ' &#8377';
                } else {
                    $val['maturity_payable_amount'] = 'N/A';
                }
                if ($row['demandadvice']) {
                    if ($row['demandadvice']->payment_mode == 0) {
                        $val['payment_mode'] = "Cash";
                    }
                    if ($row['demandadvice']->payment_mode == 1) {
                        $val['payment_mode'] = "Cheque";
                    }
                    if ($row['demandadvice']->payment_mode == 2) {
                        $val['payment_mode'] = "Online Transfer";
                    }
                    if ($row['demandadvice']->payment_mode == 3) {
                        $val['payment_mode'] = "SSB Transfer";
                    }
                } else {
                    $val['payment_mode'] = "N/A";
                }
                if ($row->is_mature == 0) {
                    $date = $row['TransactionTypeDate'];
                    // Sachin sir ne change karaya 14-03-2022
                    if (isset($date[0]['created_at'])) {
                        $val['payment_date'] = date('d/m/Y', strtotime($date[0]['created_at']));
                    }
                } else {
                    $val['payment_date'] = "N/A";
                }
                if ($row['demandadvice']) {
                    if ($row['demandadvice']->payment_mode == 1) {
                        $transaction = $row['demandadvice']['demandAmount'][0];       //(13,133,$row['demandadvice']->id);
                        if ($transaction) {
                            $val['cheque_no'] = $transaction->cheque_no;
                        } else {
                            $val['cheque_no'] = 'N/A';
                        }
                    } elseif ($row['demandadvice']->payment_mode == 2) {
                        $transaction = $row['demandadvice']['demandTransactionAmount'][0]; //(13,$row['demandadvice']->id);
                        if ($transaction) {
                            $val['cheque_no'] = $transaction->transction_no;
                        } else {
                            $val['cheque_no'] = 'N/A';
                        }
                    } else {
                        $val['cheque_no'] = 'N/A';
                    }
                } else {
                    $val['cheque_no'] = 'N/A';
                }
                //ssb payment
                if ($row['demandadvice']) {
                    if ($row['demandadvice']->payment_mode == 3) {
                        $ac = SavingAccount::select('id', 'account_no')->where('member_id', $row['member']->id)->first();
                        if ($ac) {
                            $val['ssb_ac'] = $ac->account_no;
                        } else {
                            $val['ssb_ac'] = $row['demandadvice']->ssb_account;
                        }
                    } elseif (isset($ac->account_no)) {
                        $val['ssb_ac'] = $ac->account_no;
                    } else {
                        $val['ssb_ac'] = 'N/A';
                    }
                } else {
                    $val['ssb_ac'] = 'N/A';
                }
                //Bank Payment
                if ($row['demandadvice']) {
                    if ($row['demandadvice']->payment_mode == 1 || $row['demandadvice']->payment_mode == 2) {
                        // $transaction = getMaturityTransactionRecord(13,133,$row['demandadvice']->id);
                        if (isset($row['demandadvice']->bank_name)) {
                            $val['bank_name'] = $row['demandadvice']->bank_name;
                        } else {
                            $val['bank_name'] = 'N/A';
                        }
                    } else {
                        $val['bank_name'] = 'N/A';
                    }
                } else {
                    $val['bank_name'] = 'N/A';
                }
                if ($row['demandadvice']) {
                    if ($row['demandadvice']->payment_mode == 1 || $row['demandadvice']->payment_mode == 2) {
                        // $transaction = getMaturityTransactionRecord(13,133,$row['demandadvice']->id);
                        if (isset($row['demandadvice']->bank_account_number)) {
                            $val['bank_ac'] = $row['demandadvice']->bank_account_number;
                        } else {
                            $val['bank_ac'] = 'N/A';
                        }
                    } else {
                        $val['bank_ac'] = 'N/A';
                    }
                } else {
                    $val['bank_ac'] = 'N/A';
                }
                // rtgs charge
                if ($row['demandadvice']) {
                    if ($row['demandadvice']->payment_mode == 2) {
                        $transaction = $row['demandadvice']['demandAmountHead'][0];
                        ;
                        if ($transaction) {
                            $val['rtgs_chrg'] = number_format((float) $transaction->amount, 2, '.', '');
                        } else {
                            $val['rtgs_chrg'] = 'N/A';
                        }
                    } else {
                        $val['rtgs_chrg'] = 'N/A';
                    }
                } else {
                    $val['rtgs_chrg'] = 'N/A';
                }
                $rowReturn[] = $val;
            }
            //  print_r($rowReturn);die;
            $output = array("draw" => $_POST['draw'], "recordsTotal" => $totalCount, "recordsFiltered" => $count, "data" => $rowReturn, );
            return json_encode($output);
        } else {
            return json_encode([]);
        }
    }    
}