<?php
namespace App\Http\Controllers\Admin\Report;

use App\Models\MemberIdProof;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use App\Models\Daybook;
use App\Models\Branch;
use App\Models\Transcation;
use App\Models\Memberinvestments;
use App\Models\Plans;
use App\Models\AccountHeads;
use App\Models\Companies;
use App\Http\Controllers\Admin\CommanController;
use App\Models\Member;
use App\Models\LoanDayBooks;
use Yajra\DataTables\DataTables;
use Carbon\Carbon;
use Session;
use Image;
use Redirect;
use URL;
use DB;
use App\Models\Loans;
use App\Services\Email;
use App\Services\Sms;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

/*
|---------------------------------------------------------------------------
| Admin Panel -- Report Management MotherBranchBusinessController
|--------------------------------------------------------------------------
|
| This controller handles admin_business report all functionlity.
*/
class NonPerformingAssetsReportController extends Controller
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
        if (check_my_permission(Auth::user()->id, "305") != "1") {
            return redirect()->route('admin.dashboard');
        }
        $data['title'] = 'Report | Non Performing Assets Report';
        $data['branch'] = Branch::where('status', 1)->get();
        $data['plans'] = Plans::where('status', 1)->get();
        $data['loan_plan_type'] = Loans::where('status', 1)->get();
        return view('templates.admin.report.npa.non_performing_assets', $data);
    }
    public function non_Performing_assets_listing(Request $request)
    {
        if ($request->ajax()) {
            $arrFormData = array(); {
                if (!empty($_POST['searchform'])) {
                    foreach ($_POST['searchform'] as $frm_data) {
                        $arrFormData[$frm_data['name']] = $frm_data['value'];
                    }
                }
                if (isset($arrFormData['is_search']) && $arrFormData['is_search'] == 'yes') {
                    $companyId = $arrFormData['company_id'];
                    $c_id = $arrFormData['customer_id'];
                    $member_id = $arrFormData['member_id'];
                    $account_no = $arrFormData['account_no'];
                    $branch_id = $arrFormData['branch_id'];
                    $loan_type_id = $arrFormData['loan_type_id'];
                    $companyId = $companyId > 0 ? $companyId : '' ;
                    $branch_id = $branch_id > 0 ? $branch_id : '' ;
                    if ($arrFormData['created_at'] != '') {
                        $last_date = date("Y-m-d", strtotime(convertDate($arrFormData['created_at'])));
                    } else {
                        $last_date = date("Y-m-d", strtotime(convertDate($request->globalDate)));
                    }
                    $last_date = date('Y-m-d', strtotime($last_date . ' - 1 days'));
                    if ($arrFormData['branch_id'] != '' && $arrFormData['branch_id'] > 0) {
                        $branch_id = (int) $arrFormData['branch_id'];
                    } else {
                        $branch_id = null;
                    }
                    $data = '';
                    $data = DB::select('call npa(?,?,?,?,?,?,?)', [$branch_id, $last_date, $loan_type_id,$companyId, $c_id,$member_id, $account_no]);
                    $cacheData = Cache::put('cacheData', $data);
                    $count = count($data);
                    $totalCount = $count;
                    $companycount = Companies::where('status',1)->count();
                    $record = array_slice($data, $_POST['start'], $_POST['length']);
                    $sno = $_POST['start'];
                    $rowReturn = [];
                    foreach ($record as $row) {
                        $first_emi_date = (($row->emi_option == 1) ? (date('Y-m-d', strtotime('+1 months', strtotime($row->approve_date)))) : (($row->emi_option == 2) ? (date('Y-m-d', strtotime('+7 Days', strtotime($row->approve_date)))) : date('Y-m-d', strtotime('+1 Days', strtotime($row->approve_date)))));
                        $data_diff = (strtotime($last_date) - strtotime(($row->last_recv) ?? ($first_emi_date ?? $last_date)));
                        $over_due_day = ($data_diff ? floor($data_diff / (60 * 60 * 24)) : 0) . ' Days';
                        $sno++;
                        $val['DT_RowIndex'] = $sno;
                        $val['company_name'] = $row->company_name;
                        $val['branch'] = $row->branch;
                        $val['customer_id'] = $row->customer_id;
                        $val['member_id'] = $row->member_id;
                        $val['member_name'] = $row->member_name;
                        $val['account_no'] = $row->account_number ?? 'N/A';
                        $val['loan_plan_name'] = $row->loan_plan_name;
                        $val['lone_sanction_date'] = isset($row->approve_date) ? date("d/m/Y", strtotime(convertDate($row->approve_date))) : 'N/A';
                        $val['lone_sanction_amt'] = $row->amount;
                        $val['emi'] = $row->emi_option == 1 ? 'Monthly' : ($row->emi_option == 2 ? 'Weekly' : 'Daily');
                        $val['emi_amt'] = $row->emi_amount ?? 'N/A';
                        $val['emi_period'] = $row->emi_period ?? 'N/A';
                        $val['closing_date'] = isset($row->closing_date) ? date("d/m/Y", strtotime(convertDate($row->closing_date))) : 'N/A';
                        $val['last_recovery_date'] = isset($row->last_recv) ? date("d/m/Y", strtotime(convertDate($row->last_recv))) : 'N/A';
                        $totalrecoveryamt = LoanDayBooks::whereIn('loan_sub_type', [0, 1])
                        ->where('account_number', $row->account_number)->where('is_deleted',0)
                        ->sum('deposit');
                        $val['total_recovery_amt'] = $totalrecoveryamt ?? 'N/A';
                        $val['over_due_day'] = $over_due_day;
                        $rowReturn[] = $val;
                    }
                    $token = session()->get('_token');
                    Cache::put('npa_list_Admin'.$token, $data);
                    Cache::put('npa_count_Admin'.$token, $count);
                    Cache::put('last_date_Admin'.$token, $last_date);
                    $output = array("draw" => $_POST['draw'], "recordsTotal" => $totalCount, "recordsFiltered" => $count, "data" => $rowReturn,);
                    return json_encode($output);
                } else {
                    $output = array("draw" => 0, "recordsTotal" => 0, "recordsFiltered" => 0, "data" => 0);
                    return json_encode($output);
                }
            }
        }
    }
    public function export(Request $request)
    {
        $token = session()->get('_token');
        $data = Cache::get('npa_list_Admin'.$token);
        $count = Cache::get('npa_count_Admin'.$token);
        $last_date = Cache::get('last_date_Admin'.$token);
        $input = $request->all();
        $start = $input["start"];
        $limit = $input["limit"];
        $companycount = Companies::where('status',1)->count();
        $returnURL = URL::to('/') . "/report/nonperformingassetsreport.csv";
		$fileName = env('APP_EXPORTURL') . "/report/nonperformingassetsreport.csv";
        global $wpdb;
        $postCols = array(
            'post_title',
            'post_content',
            'post_excerpt',
            'post_name',
        );
        header("Content-type: text/csv");
        $totalResults = $count;
        $result = 'next';
        if (($start + $limit) >= $totalResults) {
            $result = 'finished';
        }
        if ($start == 0) {
            $handle = fopen($fileName, 'w');
        } else {
            $handle = fopen($fileName, 'a');
        }
        if ($start == 0) {
            $headerDisplayed = false;
        } else {
            $headerDisplayed = true;
        }
        $sno = $_POST['start'];
        $rowReturn = [];
        $record = array_slice($data, $start, $limit);
        $totalCount = count($record);
        foreach ($record as $row) {
            $sno++;
            $val['S/N'] = $sno;
            $val['Compay Name'] = $row->company_name;
            $val['Branch Name'] = $row->branch;
            $val['Branch Code'] = $row->branch_code;
            $val['Branch Sector'] = $row->sector;
            $val['Branch Region'] = $row->regan;
            $val['Branch Zone'] = $row->zone;
            $val['Customer ID'] = $row->customer_id;
            $val['Member ID'] = $row->member_id;
            $val['Member Name'] = $row->member_name;
            $val['Account No'] = $row->account_number ?? 'N/A';
            $val['Loan plan Name'] = $row->loan_plan_name;
            $val['Loan Sanction Date'] = isset($row->approve_date) ? date("d/m/Y", strtotime(convertDate($row->approve_date))) : 'N/A';
            $val['Loan Amount'] = $row->amount ?? 'N/A';
            $val['EMI option'] = $row->emi_option == 1 ? 'Monthly' : ($row->emi_option == 2 ? 'Weekly' : 'Daily');
            $val['EMI Amount'] = $row->emi_amount ?? 'N/A';
            $val['EMI Period'] = $row->emi_period ?? 'N/A';
            $val['Closing Date'] = isset($row->closing_date) ? date("d/m/Y", strtotime(convertDate($row->closing_date))) : 'N/A';
            $val['Last recovery date'] = isset($row->last_recv) ? date("d/m/Y", strtotime(convertDate($row->last_recv))) : 'N/A';
            $totalrecoveryamt = LoanDayBooks::whereIn('loan_sub_type', [0, 1])
            ->where('account_number', $row->account_number)->where('is_deleted',0)
            ->sum('deposit');
            $val['Total recovery Amount'] = $totalrecoveryamt ?? 'N/A';

            $first_emi_date = (($row->emi_option == 1) ? (date('Y-m-d', strtotime('+1 months', strtotime($row->approve_date)))) : (($row->emi_option == 2) ? (date('Y-m-d', strtotime('+7 Days', strtotime($row->approve_date)))) : date('Y-m-d', strtotime('+1 Days', strtotime($row->approve_date)))));
            $data_diff = (strtotime($last_date) - strtotime(($row->last_recv) ?? ($first_emi_date ?? $last_date)));
            $over_due_day = ($data_diff ? floor($data_diff / (60 * 60 * 24)) : 0) . ' Days';

            $val['Over Due Days'] = $over_due_day;
            if (!$headerDisplayed) {
                fputcsv($handle, array_keys($val));
                $headerDisplayed = true;
            }
            fputcsv($handle, $val);
        }
        // Close the file
        fclose($handle);
        if ($totalResults == 0) {
            $percentage = 100;
        } else {
            $percentage = ($start + $limit) * 100 / $totalResults;
            $percentage = number_format((float) $percentage, 1, '.', '');
        }
        // Output some stuff for jquery to use
        $response = array(
            'result' => $result,
            'start' => $start,
            'limit' => $limit,
            'totalResults' => $totalResults,
            'fileName' => $returnURL,
            'percentage' => $percentage
        );
        echo json_encode($response);
    }
    public function allloanplans(Request $request)
    {
		if ($request['company_id'] == 0) {
			$plans = Loans::get(['name', 'id','loan_type']);
        $return_array = compact('plans');
        return json_encode($return_array);
		}
        $plans = Loans::where('company_id', $request['company_id'])->get(['name', 'id','loan_type']);
        $return_array = compact('plans');
        return json_encode($return_array);
    }
}