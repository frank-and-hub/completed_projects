<?php
namespace App\Http\Controllers\Admin\Report;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Branch;
use DateTime;
use Illuminate\Support\Facades\Cache;
use DB;
/*
    |---------------------------------------------------------------------------
    | Admin Panel -- Report Management AdminBusinessController
    |--------------------------------------------------------------------------
    |
    | This controller handles admin_business report all functionlity.
*/
class AdminBusinessController extends Controller
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
     * Route: /admin/report/admin_business
     * Method: get 
     * @return  array()  Response
     */
    //Admin Business Report (AMAN !! 17-05)
    public function index()
    {
        if (check_my_permission(Auth::user()->id, "127") != "1") {
            return redirect()->route('admin.dashboard');
        }
        $data['title'] = 'Report | Branch Business  Report';
        $data['branch'] = Branch::where('status', 1)->get();
        $data['zone'] = Branch::where('status', 1)->select('zone')->groupBy('zone')->get();
        return view('templates.admin.report.admin_business', $data);
    }
    public function admin_business_listing(Request $request)
    {
        $currentdate = date('Y-m-d');
        $companyname = '';
        if ($request->ajax()) {
            // fillter array 
            $arrFormData = array();
            $arrFormData['start_date'] = $request->start_date;
            $arrFormData['end_date'] = $request->end_date;
            $arrFormData['branch_id'] = $request->branch_id;
            $arrFormData['is_search'] = $request->is_search;
            $arrFormData['created_at'] = $request->created_at;
            $arrFormData['zone'] = $request->zone;
            $arrFormData['region'] = $request->region;
            $arrFormData['sector'] = $request->sector;
            $company = $arrFormData['company_id'] = $request->company_id;
            $customplan = getPlanIDCustom();
            $company_id = '';
            $companyname = '';
            if ($request['company_id'] != '' && $request['company_id'] != 0) {
                $company_id = $request['company_id'];
                $companyname = \App\Models\Companies::where('id', $company_id)->value('name');
            } else {
                $company_id = '';
                $companyname = 'All Company';
            }
            if (isset($arrFormData['is_search']) && $arrFormData['is_search'] == 'yes') {
                $branch_id = $arrFormData['branch_id'];
                $startDate = DateTime::createFromFormat("d/m/Y", $arrFormData['start_date']);
                $startDate = $startDate->format("Y-m-d");
                $endDate = DateTime::createFromFormat("d/m/Y", $arrFormData['end_date']);
                $endDate = $endDate->format("Y-m-d");
                $page_number = $_POST['start'] / $request->length;
                $page_number = $page_number + 1;
                $data = DB::select('call BranchBusinessReports(?,?,?,?,?,?)', [$startDate, $endDate, $company, $branch_id, $page_number, $request->length]);
                $datac = $data;
                $data1 = count($data);
                $count = $data1;
                $totalCount = $count;
                if ($branch_id == 0 || $branch_id == '') {
                    $totalCount = Branch::count('id');
                    $count = $totalCount;
                }
                $sno = $_POST['start'];
                $rowReturn = array();
                $limit = 10;
                $offset = 0;
                $token = session()->get('_token');
                $Cache = Cache::put('branch_business_reports' . $token, $datac);
                Cache::put('branch_business_reports_count' . $token, $count);
                $counter = 0;
                foreach ($data as $row) {
                    $sno++;
                    $val['DT_RowIndex'] = $sno;
                    $val['company_name'] = $companyname;
                    $val['branch'] = $row->name;
                    $val['branch_code'] = $row->branch_code;
                    $val['daily_new_ac'] = $row->dnccac;
                    $val['daily_deno_sum'] = "&#x20B9;" . $row->dnccamt;
                    $val['daily_renew_ac'] = $row->drenac;
                    $val['daily_renew'] = "&#x20B9;" . $row->drenamt;
                    $val['monthly_ncc_ac'] = $row->mnccac;
                    $val['monthly_ncc_amt'] = "&#x20B9;" . $row->mnccamt;
                    $val['monthly_renew_ac'] = $row->mrenac;
                    $val['monthly_renew_amt'] = "&#x20B9;" . $row->mrenamt;
                    $val['fd_new_ac'] = $row->fnccac;
                    $val['fd_deno_sum'] = "&#x20B9;" . $row->fnccamt;
                    $val['ssb_ncc_ac'] = $row->snccac;
                    $val['ssb_ncc_amt'] = $row->sncc;
                    $val['ssb_ren_ac'] = $row->ssbren_ac;
                    $val['ssb_ren_amt'] = "&#x20B9;" . number_format((float) $row->ssbren, 2, '.', '');
                    $val['other_mi'] = "&#x20B9;" . $row->MI;
                    $val['other_stn'] = "&#x20B9;" . $row->STN;
                    $val['new_mi_joining'] = $row->member_acn;
                    $val['new_associate_joining'] = $row->asso_ac;
                    $val['banking_ac'] = $row->sumbanking_ac;
                    $val['banking_amt'] = "&#x20B9;" . number_format((float) $row->sumbankingamt, 2, '.', '');
                    $val['total_withdrawal'] = "&#x20B9;" . number_format((float) $row->ssbw, 2, '.', '');
                    $val['total_payment'] = "&#x20B9;" . number_format((float) $row->MaturityPayment, 2, '.', '');
                    $val['ncc'] = $row->ncc;
                    $val['ncc_ssb'] = $row->ncc_ssb;
                    $val['tcc'] = $row->tcc;
                    $val['tcc_ssb'] = $row->tcc_ssb;
                    $val['loan_ac_no'] = $row->loan_ac_no;
                    $val['loan_amt'] = "&#x20B9;" . number_format((float) $row->loan_amt, 2, '.', '');
                    $val['loan_recv_ac_no'] = $row->loan_recv_ac_no;
                    $val['loan_recv_amt'] = "&#x20B9;" . number_format((float) $row->loan_recv_amt, 2, '.', '');
                    $val['loan_aginst_ac_no'] = $row->loan_aginst_ac_no;
                    $val['loan_aginst_amt'] = "&#x20B9;" . $row->loan_aginst_amt;
                    $val['loan_aginst_recv_ac_no'] = $row->loan_aginst_recv_ac_no;
                    $val['loan_aginst_recv_amt'] = "&#x20B9;" . number_format((float) $row->loan_aginst_recv_amt, 2, '.', '');
                    $val['cash_in_hand'] = $row->cash_in_hand;
                    $rowReturn[] = $val;
                }
                $output = array("draw" => $_POST['draw'], "recordsTotal" => $totalCount, "recordsFiltered" => $count, "data" => $rowReturn, );
                return json_encode($output);
            } else {
                $output = array("draw" => 0, "recordsTotal" => 0, "recordsFiltered" => 0, "data" => 0, );
                return json_encode($output);
            }
        }
    }
}