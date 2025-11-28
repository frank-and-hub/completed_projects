<?php

namespace App\Http\Controllers\CommonController\associate;

use App\Models\MemberIdProof;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Companies;
use Session;
use URL;
use DB;
use Illuminate\Support\Facades\Cache; /*
|---------------------------------------------------------------------------
| Admin Panel -- Member Management MemberController
|--------------------------------------------------------------------------
|
| This controller handles members all functionlity.
*/

class AssociateBusinessController extends Controller
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
    public function index()
    {
        if (Auth::user()->role_id != 3) {
            if (check_my_permission(Auth::user()->id, "121") != "1") {
                return redirect()->route('admin.dashboard');
            }
        } else {
            if (!in_array('Associate Business Report', auth()->user()->getPermissionNames()->toArray())) {
                return redirect()->route('branch.dashboard');
            }
        }
        $data['title'] = 'Report | Associate Business Report';
        return view('templates.CommonViews.Associate.associate_business', $data);
    }
    public function listing(Request $request)
    {
        if ($request->ajax()) {
            $arrFormData = array();
            if (!empty($_POST['searchform'])) {
                foreach ($_POST['searchform'] as $frm_data) {
                    $arrFormData[$frm_data['name']] = $frm_data['value'];
                }
            }
            if ($arrFormData['is_search'] == 'no') {
                $output = array("draw" => $_POST['draw'], "recordsTotal" => 0, "recordsFiltered" => 0, "data" => 0,);
                return json_encode($output);
            }
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
            if ((Auth::user()->role_id) == 3) {
                $getBranchId = getUserBranchId(Auth::user()->id);
                $arrFormData['branch_id'] = $getBranchId->id;
            }
            if ($arrFormData['associate'] == '') {
                $arrFormData['associate'] = 0;
            }
            $company = Companies::whereDelete('0')->pluck('name', 'id');
            $company[0] = 'All Company';
            $start = $_POST['start'];
            $length = $_POST['length'];
            $page_number = $start / $length;
            $page_number = $page_number + 1;
            if (Auth::user()->role_id != 3) {
                $data = DB::select('call associate_business_report(?,?,?,?,?,?,?)', [$startDate, $endDate, $arrFormData['associate'], $arrFormData['company_id'], $arrFormData['branch_id'], 1, 99999]);
            } else {
                $data = DB::select('call associate_business_report_branch_new(?,?,?,?,?,?,?)', [$startDate, $endDate, $arrFormData['associate'], $arrFormData['company_id'], $arrFormData['branch_id'], 1, 99999]);
            }
            $token = session()->get('_token');
            $totalCount = count($data);
            Cache::put('associate_business_report' . $token, $data);
            Cache::put('associate_business_report_COUNT' . $token, $totalCount);
            $record = array_slice($data, $start, $length);
            $count = $totalCount;
            $sno = $start;
            $rowReturn = [];
            foreach ($record as $row) {
                $sno++;
                $val['DT_RowIndex'] = $sno;
                $val['Company'] = $company[$arrFormData['company_id']];
                $val['AssociateCode'] = $row->associate_no;
                $val['AssociateName'] = $row->name;
                $val['AssociateBranch'] = $row->branch_name;
                $val['DailyNCC'] = $row->dnccamt;
                $val['DailyRenewal'] = $row->drenamt;
                $val['MonthlyNCC'] = $row->mnccamt;
                $val['MonthlyRenewal'] = $row->mrenamt;
                $val['FDNCC'] = $row->fnccamt;
                $val['NCC'] = $row->ncc_m;
                $val['TCC'] = $row->tcc_m;
                $val['SSBNCC'] = $row->snccamt;
                $val['T_NCC'] = $row->ncc_ssb;
                $val['T_TCC'] = $row->tcc_ssb;
                $val['SSBRenewal'] = $row->ssbren;
                $val['NewMembers'] = $row->new_m;
                $val['NewAssociates'] = $row->new_a;
                $val['NewLoansOTH'] = $row->loan_ac_no;
                $val['LoanAmount'] = $row->loan_amt;
                $val['LoanRecovery'] = $row->loan_recv_amt;
                $val['NewLoanLAD'] = $row->lad_transfer_ac_no;
                $val['LADAmount'] = $row->lad_transfer_amount;
                $val['LADRecovery'] = $row->lad_rec_amount;
                $val['MaturityPayment'] = $row->dem_amt;
                $val['Commission'] = 0.00;
                $rowReturn[] = $val;
            }
            $output = array("draw" => $_POST['draw'], "recordsTotal" => $totalCount, "recordsFiltered" => $count, "data" => $rowReturn,);
            return json_encode($output);
        }
    }
    public function compare()
    {
        if (Auth::user()->role_id != 3) {
            if (check_my_permission(Auth::user()->id, "123") != "1") {
                return redirect()->route('admin.dashboard');
            }
        } else {
            if (!in_array('Associate Business Compare Report', auth()->user()->getPermissionNames()->toArray())) {
                return redirect()->route('branch.dashboard');
            }
        }
        $data['title'] = 'Report | Associate Business Compare Report';
        return view('templates.CommonViews.AssociateCompare.associate_compare', $data);
    }
    public function comparelisting(Request $request)
    {
        if ($request->ajax()) {
            $arrFormData = array();
            if (!empty($_POST['searchform'])) {
                foreach ($_POST['searchform'] as $frm_data) {
                    $arrFormData[$frm_data['name']] = $frm_data['value'];
                }
            }
            if ($arrFormData['is_search'] == 'no') {
                $output = array("draw" => $_POST['draw'], "recordsTotal" => 0, "recordsFiltered" => 0, "data" => 0,);
                return json_encode($output);
            }
            if ($arrFormData['current_start_date'] != '') {
                $current_startDate = date("Y-m-d", strtotime(convertDate($arrFormData['current_start_date'])));
            } else {
                $current_startDate = '';
            }
            if ($arrFormData['current_end_date'] != '') {
                $current_endDate = date("Y-m-d ", strtotime(convertDate($arrFormData['current_end_date'])));
            } else {
                $current_endDate = '';
            }
            if ($arrFormData['compare_start_date'] != '') {
                $compare_startDate = date("Y-m-d", strtotime(convertDate($arrFormData['compare_start_date'])));
            } else {
                $compare_startDate = '';
            }
            if ($arrFormData['compare_end_date'] != '') {
                $compare_endDate = date("Y-m-d ", strtotime(convertDate($arrFormData['compare_end_date'])));
            } else {
                $compare_endDate = '';
            }
            if ($arrFormData['associate'] == '') {
                $arrFormData['associate'] = 0;
            }
            $company = Companies::whereDelete('0')->pluck('name', 'id');
            $company[0] = 'All Company';
            if ((Auth::user()->role_id) == 3) {
                $getBranchId = getUserBranchId(Auth::user()->id);
                $arrFormData['branch_id'] = $getBranchId->id;
            }
            $page_number = $_POST['start'] / $request->length;
            $page_number = $page_number + 1;
            $data = DB::select('call associate_business_compare_report(?,?,?,?,?,?,?,?,?)', [$current_startDate, $current_endDate, $compare_startDate, $compare_endDate, $arrFormData['company_id'], $arrFormData['branch_id'], $arrFormData['associate'], 1, 99999]);
            $token = session()->get('_token');
            $totalCount = count($data);
            Cache::put('associate_business_compare_report' . $token, $data);
            Cache::put('associate_business_report_compare_COUNT' . $token, $totalCount);
            $record = array_slice($data, $_POST['start'], $request->length);
            $count = $totalCount;
            $sno = $_POST['start'];
            $rowReturn = [];
            foreach ($record as $row) {
                $sno++;
                $val['DT_RowIndex'] = $sno;
                $val['Company'] = $company[$arrFormData['company_id']];
                $val['AssociateCode'] = $row->associate_no;
                $val['AssociateName'] = $row->name;
                $val['AssociateBranch'] = $row->br_name;
                $val['DailyNCC'] = $row->dnccamt;
                $val['DailyRenewal'] = $row->drenamt;
                $val['MonthlyNCC'] = $row->mnccamt;
                $val['MonthlyRenewal'] = $row->mrenamt;
                $val['FDNCC'] = $row->fnccamt;
                $val['NCC'] = $row->ncc_m;
                $val['TCC'] = $row->tcc_m;
                $val['SSBNCC'] = $row->snccamt;
                $val['T_NCC'] = $row->ncc_ssb;
                $val['T_TCC'] = $row->tcc_ssb;
                $val['SSBRenewal'] = $row->ssbren;
                $val['NewMembers'] = $row->new_m;
                $val['NewAssociates'] = $row->new_a;
                $val['NewLoansOTH'] = $row->loan_ac_no;
                $val['LoanAmount'] = $row->loan_amt;
                $val['LoanRecovery'] = $row->loan_recv_amt;
                $val['NewLoanLAD'] = $row->lad_transfer_ac_no;
                $val['LADAmount'] = $row->lad_transfer_amount;
                $val['LADRecovery'] = $row->lad_rec_amount;
                $val['c_DailyNCC'] = $row->c_dnccamt;
                $val['c_DailyRenewal'] = $row->c_drenamt;
                $val['c_MonthlyNCC'] = $row->c_mnccamt;
                $val['c_MonthlyRenewal'] = $row->c_mrenamt;
                $val['c_FDNCC'] = $row->c_fnccamt;
                $val['c_NCC'] = $row->c_ncc_m;
                $val['c_TCC'] = $row->c_tcc_m;
                $val['c_SSBNCC'] = $row->c_snccamt;
                $val['c_T_NCC'] = $row->c_ncc_ssb;
                $val['c_T_TCC'] = $row->c_tcc_ssb;
                $val['c_SSBRenewal'] = $row->c_ssbren;
                $val['c_NewMembers'] = $row->c_new_m;
                $val['c_NewAssociates'] = $row->c_new_a;
                $val['c_NewLoansOTH'] = $row->c_loan_ac_no;
                $val['c_LoanAmount'] = $row->c_loan_amt;
                $val['c_LoanRecovery'] = $row->c_loan_recv_amt;
                $val['c_NewLoanLAD'] = $row->c_lad_transfer_ac_no;
                $val['c_LADAmount'] = $row->c_lad_transfer_amount;
                $val['c_LADRecovery'] = $row->c_lad_rec_amount;
                $val['diff_DailyNCC'] = $row->diff_dnccamt;
                $val['diff_DailyRenewal'] = $row->diff_drenamt;
                $val['diff_MonthlyNCC'] = $row->diff_mnccamt;
                $val['diff_MonthlyRenewal'] = $row->diff_mrenamt;
                $val['diff_FDNCC'] = $row->diff_fnccamt;
                $val['diff_NCC'] = $row->diff_ncc_m;
                $val['diff_TCC'] = $row->diff_tcc_m;
                $val['diff_SSBNCC'] = $row->diff_snccamt;
                $val['diff_T_NCC'] = $row->diff_ncc_ssb;
                $val['diff_T_TCC'] = $row->diff_tcc_ssb;
                $val['diff_SSBRenewal'] = $row->diff_ssbren;
                $val['diff_NewMembers'] = $row->diff_new_m;
                $val['diff_NewAssociates'] = $row->diff_new_a;
                $val['diff_NewLoansOTH'] = $row->diff_loan_ac_no;
                $val['diff_LoanAmount'] = $row->diff_loan_amt;
                $val['diff_LoanRecovery'] = $row->diff_loan_recv_amt;
                $val['diff_NewLoanLAD'] = $row->diff_lad_transfer_ac_no;
                $val['diff_LADAmount'] = $row->diff_lad_transfer_amount;
                $val['diff_LADRecovery'] = $row->diff_lad_rec_amount;
                $rowReturn[] = $val;
            }
            $output = array("draw" => $_POST['draw'], "recordsTotal" => $totalCount, "recordsFiltered" => $count, "data" => $rowReturn);
            return json_encode($output);
        }
    }
    public function export(Request $request)
    {
        $token = session()->get('_token');
        $file = Session::get('_fileName');
        $data = Cache::get('associate_business_report' . $token);
        $count = Cache::get('associate_business_report_COUNT' . $token);
        $company = Companies::whereDelete('0')->pluck('name', 'id');
        $company[0] = 'All Company';
        $input = $request->all();
        $start = $input["start"];
        $limit = $input["limit"];
        $returnURL = URL::to('/') . "/asset/associatebusnissreport" . $file . ".csv";
        $fileName = env('APP_EXPORTURL') . "/asset/associatebusnissreport" . $file . ".csv";
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
        foreach ($data as $row) {
            $sno++;
            $val['S/N'] = $sno;
            $val['Company'] = $company[$request['company_id']];
            if (Auth::user()->role_id != 3) {
                $val['Sector'] = $row->sector;
                $val['Regan'] = $row->regan;
                $val['Zone'] = $row->zone;
                $val['Branch'] = $row->branch_name;
            }
            $val['Associate Code'] = $row->associate_no;
            $val['Associate Name'] = $row->name;
            // $val['DailyNI'] = $row->;
            $val['Daily NCC'] = $row->dnccamt;
            $val['Daily Renewal'] = $row->drenamt;
            // $val['MonthlyNI'] = $row->;
            $val['Monthly NCC'] = $row->mnccamt;
            $val['Monthly Renewal'] = $row->mrenamt;
            // $val['FDNI'] = $row->;
            $val['FD NCC'] = $row->fnccamt;
            $val['NCC'] = $row->ncc_m;
            $val['TCC'] = $row->tcc_m;
            $val['SSB NCC'] = $row->snccamt;
            $val['SSB Renewal'] = $row->ssbren;
            $val['NCC Total'] = $row->ncc_ssb;
            $val['TCC Total'] = $row->tcc_ssb;
            $val['New Loans (OTH)'] = $row->loan_ac_no;
            $val['Loan Amount'] = $row->loan_amt;
            $val['Loan Recovery'] = $row->loan_recv_amt;
            $val['New Loan (LAD)'] = $row->lad_transfer_ac_no;
            $val['LAD Amount'] = $row->lad_transfer_amount;
            $val['LAD Recovery'] = $row->lad_rec_amount;
            $val['Maturity Payment'] = $row->dem_amt;
            $val['New Members'] = $row->new_m;
            $val['New Associates'] = $row->new_a;
            // $val['Commission'] = 0.00;
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
    public function exportcompare(Request $request)
    {
        $token = session()->get('_token');
        $file = Session::get('_fileName');
        $data = Cache::get('associate_business_compare_report' . $token);
        $count = Cache::get('associate_business_report_compare_COUNT' . $token);
        $company = Companies::whereDelete('0')->pluck('name', 'id');
        $company[0] = 'All Company';
        $input = $request->all();
        $start = $input["start"];
        $limit = $input["limit"];
        $returnURL = URL::to('/') . "/asset/associatebusnisscomparereport" . $file . ".csv";
        $fileName = env('APP_EXPORTURL') . "/asset/associatebusnisscomparereport" . $file . ".csv";
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
        foreach ($data as $row) {
            $sno++;
            $val['S/N'] = $sno;
            $val['Company'] = $company[$request['company_id']];
            $val['Sector'] = $row->sector;
            $val['Regan'] = $row->regan;
            $val['Zone'] = $row->zone;
            $val['Branch'] = $row->br_name;
            $val['Associate Code'] = $row->associate_no;
            $val['Associate Name'] = $row->name;
            // $val['DailyNI'] = $row->;
            $val['Daily NCC'] = $row->dnccamt;
            $val['Daily Renewal'] = $row->drenamt;
            // $val['MonthlyNI'] = $row->;
            $val['Monthly NCC'] = $row->mnccamt;
            $val['Monthly Renewal'] = $row->mrenamt;
            // $val['FDNI'] = $row->;
            $val['FD NCC'] = $row->fnccamt;
            $val['NCC'] = $row->ncc_m;
            $val['TCC'] = $row->tcc_m;
            $val['SSB NCC'] = $row->snccamt;
            $val['SSB Renewal'] = $row->ssbren;
            $val['Total NCC'] = $row->ncc_ssb;
            $val['Total TCC'] = $row->tcc_ssb;
            $val['New Loans (OTH)'] = $row->loan_ac_no;
            $val['Loan Amount'] = $row->loan_amt;
            $val['Loan Recovery'] = $row->loan_recv_amt;
            $val['New Loan (LAD)'] = $row->lad_transfer_ac_no;
            $val['LAD Amount'] = $row->lad_transfer_amount;
            $val['LAD Recovery'] = $row->lad_rec_amount;
            $val['New Members'] = $row->new_m;
            $val['New Associates'] = $row->new_a;
            $val['Second Daily NCC'] = $row->c_dnccamt;
            $val['Second Daily Renewal'] = $row->c_drenamt;
            // $val['Second MonthlyNI'] = $row->c_;
            $val['Second Monthly NCC'] = $row->c_mnccamt;
            $val['Second Monthly Renewal'] = $row->c_mrenamt;
            // $val['Second FDNI'] = $row->c_;
            $val['Second FD NCC'] = $row->c_fnccamt;
            $val['Second NCC'] = $row->c_ncc_m;
            $val['Second TCC'] = $row->c_tcc_m;
            $val['Second SSB NCC'] = $row->c_snccamt;
            $val['Second SSB Renewal'] = $row->c_ssbren;
            $val['Second Total NCC'] = $row->c_ncc_ssb;
            $val['Second Total TCC'] = $row->c_tcc_ssb;
            $val['Second New Loans (OTH)'] = $row->c_loan_ac_no;
            $val['Second Loan Amount'] = $row->c_loan_amt;
            $val['Second Loan Recovery'] = $row->c_loan_recv_amt;
            $val['Second New Loan (LAD)'] = $row->c_lad_transfer_ac_no;
            $val['Second LAD Amount'] = $row->c_lad_transfer_amount;
            $val['Second LAD Recovery'] = $row->c_lad_rec_amount;
            $val['Second New Members'] = $row->c_new_m;
            $val['Second New Associates'] = $row->c_new_a;
            $val['Difference of Daily NCC'] = $row->diff_dnccamt;
            $val['Difference of Daily Renewal'] = $row->diff_drenamt;
            // $val['Difference of MonthlyNI'] = $row->diff_;
            $val['Difference of Monthly NCC'] = $row->diff_mnccamt;
            $val['Difference of Monthly Renewal'] = $row->diff_mrenamt;
            // $val['Difference of FDNI'] = $row->diff_;
            $val['Difference of FD NCC'] = $row->diff_fnccamt;
            $val['Difference of NCC'] = $row->diff_ncc_m;
            $val['Difference of TCC'] = $row->diff_tcc_m;
            $val['Difference of SSB NCC'] = $row->diff_snccamt;
            $val['Difference of SSB Renewal'] = $row->diff_ssbren;
            $val['Difference of Total NCC'] = $row->diff_ncc_ssb;
            $val['Difference of Total TCC'] = $row->diff_tcc_ssb;
            $val['Difference of New Loans (OTH)'] = $row->diff_loan_ac_no;
            $val['Difference of Loan Amount'] = $row->diff_loan_amt;
            $val['Difference of Loan Recovery'] = $row->diff_loan_recv_amt;
            $val['Difference of New Loan (LAD)'] = $row->diff_lad_transfer_ac_no;
            $val['Difference of LAD Amount'] = $row->diff_lad_transfer_amount;
            $val['Difference of LAD Recovery'] = $row->diff_lad_rec_amount;
            $val['Difference of New Members'] = $row->diff_new_m;
            $val['Difference of New Associates'] = $row->diff_new_a;
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
}
