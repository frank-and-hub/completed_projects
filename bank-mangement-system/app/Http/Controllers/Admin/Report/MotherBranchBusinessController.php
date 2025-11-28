<?php
namespace App\Http\Controllers\Admin\Report;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Companies;
use Illuminate\Support\Facades\Cache;
use App\Models\Branch;
use Session;
use URL;
use DB;
use DateTime;

/*
    |---------------------------------------------------------------------------
    | Admin Panel -- Report Management MotherBranchBusinessController
    |--------------------------------------------------------------------------
    |
    | This controller handles admin_business report all functionlity.
*/
class MotherBranchBusinessController extends Controller
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
        // if (check_my_permission(Auth::user()->id, "306") != "1") {
        //     return redirect()->route('admin.dashboard');
        // }
        $data['title'] = 'Report | Mother Branch Business  Report';
        $data['branch'] = Branch::where('status', 1)->get();
        $data['zone'] = Branch::where('status', 1)->select('zone')->groupBy('zone')->get();
        return view('templates.admin.report.mother_branch_business', $data);
    }
    public function mother_branch_business_listing(Request $request)
    {
        $currentdate = date('Y-m-d');
        if ($request->ajax()) {

            $arrFormData = array();
            $arrFormData['start_date'] = $request->start_date;
            $arrFormData['end_date'] = $request->end_date;
            $arrFormData['branch_id'] = $request->branch_id;
            $arrFormData['is_search'] = $request->is_search;
            $arrFormData['created_at'] = $request->created_at;
            $companyId = $arrFormData['company_id'] = $request->company_id;
            $companyname = Companies::where('id', $companyId)->first('name');
            $branch_id = $arrFormData['branch_id'];
            if (isset($arrFormData['is_search']) && $arrFormData['is_search'] == 'yes' && $companyId != '') {
                $startDate = DateTime::createFromFormat("d/m/Y", $arrFormData['start_date']);
                $startDate = $startDate->format("Y-m-d");
                $endDate = DateTime::createFromFormat("d/m/Y", $arrFormData['end_date']);
                $endDate = $endDate->format("Y-m-d");
                $page_number = $_POST['start'] / $request->length;
                $page_number = $page_number + 1;
                $token = session()->get('_token');
                if ($page_number == 1) {
                    $data = DB::select('call motherBranchReports(?,?,?,?,?,?)', [$startDate, $endDate, $companyId, $branch_id, $page_number, ($request->length * 100)]);
                    $count = count($data);
                    Cache::put('motherBranchReports_list' . $token, $data);
                    Cache::put('motherBranchReports_count' . $token, $count);
                } else {
                    $data = Cache::get('motherBranchReports_list' . $token);
                    $count = Cache::get('motherBranchReports_count' . $token);
                }
                $totalCount = $count;
                if ($branch_id == 0 || $branch_id == '') {
                    $totalCount = Branch::count();
                    $count = $totalCount;
                }
                $sno = $_POST['start'];
                $rowReturn = [];
                $result =  array_slice($data, $_POST['start'], $request->length);;
                foreach ($result as $row) {
                    $sno++;
                    $val = [
                        'DT_RowIndex' => $sno,
                        'company_name' => $companyname->name ?? 'All Company',
                        'branch' => $row->name,
                        'branch_code' => $row->branch_code,
                        'daily_new_ac' => $row->dnccac,
                        'daily_deno_sum' => "&#x20B9;" . $row->dnccamt,
                        'daily_renew_ac' => $row->drenac,
                        'daily_renew' => "&#x20B9;" . $row->drenamt,
                        'monthly_ncc_ac' => $row->mnccac,
                        'monthly_ncc_amt' => "&#x20B9;" . $row->mnccamt,
                        'monthly_renew_ac' => $row->mrenac,
                        'monthly_renew_amt' => "&#x20B9;" . $row->mrenamt,
                        'fd_new_ac' => $row->fnccac,
                        'fd_deno_sum' => "&#x20B9;" . $row->fnccamt,
                        'ssb_ncc_ac' => $row->snccac,
                        'ssb_ncc_amt' => $row->sncc,
                        'ssb_ren_ac' => $row->ssbren_ac,
                        'ssb_ren_amt' => "&#x20B9;" . number_format((float) $row->ssbren, 2, '.', ''),
                        'other_mi' => "&#x20B9;" . $row->MI,
                        'other_stn' => "&#x20B9;" . $row->STN,
                        'new_mi_joining' => $row->member_acn,
                        'new_associate_joining' => $row->asso_ac,
                        'banking_ac' => $row->sumbanking_ac,
                        'banking_amt' => "&#x20B9;" . number_format((float) $row->sumbankingamt, 2, '.', ''),
                        'total_withdrawal' => "&#x20B9;" . number_format((float) $row->ssbw, 2, '.', ''),
                        'total_payment' => "&#x20B9;" . number_format((float) $row->MaturityPayment, 2, '.', ''),
                        'ncc' => $row->ncc,
                        'ncc_ssb' => $row->ncc_ssb,
                        'tcc' => $row->tcc,
                        'tcc_ssb' => $row->tcc_ssb,
                        'loan_ac_no' => $row->loan_ac_no,
                        'loan_amt' => "&#x20B9;" . number_format((float) $row->loan_amt, 2, '.', ''),
                        'loan_recv_ac_no' => $row->loan_recv_ac_no,
                        'loan_recv_amt' => "&#x20B9;" . number_format((float) $row->loan_recv_amt, 2, '.', ''),
                        'loan_aginst_ac_no' => $row->loan_aginst_ac_no,
                        'loan_aginst_amt' => "&#x20B9;" . $row->loan_aginst_amt,
                        'loan_aginst_recv_ac_no' => $row->loan_aginst_recv_ac_no,
                        'loan_aginst_recv_amt' => "&#x20B9;" . number_format((float) $row->loan_aginst_recv_amt, 2, '.', ''),
                        'cash_in_hand' => $row->cash_in_hand
                    ];
                    $rowReturn[] = $val;
                }
                //  print_r($rowReturn);die;
                $output = array("draw" => $_POST['draw'], "recordsTotal" => $totalCount, "recordsFiltered" => $count, "data" => $rowReturn, );
                return json_encode($output);
            } else {
                $output = array("draw" => 0, "recordsTotal" => 0, "recordsFiltered" => 0, "data" => 0, );
                return json_encode($output);
            }
        }
    }
    public function motherBranchBusinessReportExport(Request $request)
    {
        $_fileName = Session::get('_fileName');
        $input = $request->all();
        $companyId = $request['company_id'];
        $company = Companies::pluck('name', 'id');
        $start = $input["start"];
        $limit = $input["limit"] * 10;
        $returnURL = URL::to('/') . "/asset/MotherReport" . $_fileName . ".csv";
        $fileName = env('APP_EXPORTURL') . "/asset/MotherReport" . $_fileName . ".csv";
        global $wpdb;
        $postCols = array(
            'post_title',
            'post_content',
            'post_excerpt',
            'post_name',
        );
        header("Content-type: text/csv");
        // $branch_id = $request->branch_id;
        // $startDate = DateTime::createFromFormat("d/m/Y", $request->start_date);
        // $startDate = $startDate->format("Y-m-d");
        // $endDate = DateTime::createFromFormat("d/m/Y", $request->end_date);
        // $endDate = $endDate->format("Y-m-d");
        $token = session()->get('_token');
        $data = Cache::get('motherBranchReports_list' . $token);
        $count = Cache::get('motherBranchReports_count' . $token);
        // $data = DB::select('call motherBranchReports(?,?,?,?,?,?)', [$startDate, $endDate, $companyId, $branch_id, $page_number = 1, 2000]);
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
        // $rowReturn = [];
        // $record = array_slice($data, $start, $limit);
        // $totalCount = count($record);
        // $counter = 0;
        // $offset = 0;
        foreach ($data as $row) {
            $sno++;
            $val['S/N'] = $sno;
            $val['Company Name'] = $company[$companyId] ?? "All Company";
            $val['BR Name'] = $row->name;
            $val['BR Code'] = $row->branch_code;
            $val['Region'] = $row->regan;
            $val['Sector'] = $row->sector;
            $val['Zone'] = $row->zone;
            $val['Daily NCC - No. A/C'] = $row->dnccac;
            $val['Daily NCC - Amt'] = $row->dnccamt;
            $val['Daily Renew - No. A/C'] = $row->drenac;
            $val['Daily Renew - Amt'] = $row->drenamt;
            $val['Monthly NCC - No. A/C'] = $row->mnccac;
            $val['Monthly NCC - Amt'] = $row->mnccamt;
            $val['Monthly Renew- No. A/C'] = $row->mrenac;
            $val['Monthly Renew- Amt'] = $row->mrenamt;
            $val['FD NCC - No. A/C'] = $row->fnccac;
            $val['FD NCC - Amt'] = $row->fnccamt;
            $val['SSB NCC - No. A/C'] = $row->snccac;
            $val['SSB NCC - Amt'] = $row->sncc;
            $val['SSB Renew- No. A/C'] = $row->ssbren_ac;
            $val['SSB Renew- Amt'] = number_format((float) $row->ssbren, 2, '.', '');
            $val['Other MI'] = $row->MI;
            $val['Other STN'] = $row->STN;
            $val['New MI Joining - No. A/C'] = $row->member_acn;
            $val['New Associate Joining - No. A/C'] = $row->asso_ac;
            $val['Banking - No. A/C'] = $row->sumbanking_ac;
            $val['Banking - Amt'] = number_format((float) $row->sumbankingamt, 2, '.', '');
            $val['Total Payment - Withdrawal'] = number_format((float) $row->ssbw, 2, '.', '');
            $val['Total Payment - Payment'] = number_format((float) $row->MaturityPayment, 2, '.', '');
            $val['NCC'] = $row->ncc;
            $val['NCC SSB'] = $row->ncc_ssb;
            $val['TCC'] = $row->tcc;
            $val['TCC SSB'] = $row->tcc_ssb;
            $val['Loan - No. A/C'] = $row->loan_ac_no;
            $val['Loan - Amt'] = number_format((float) $row->loan_amt, 2, '.', '');
            $val['Loan Recovery - No. A/C'] = $row->loan_recv_ac_no;
            $val['Loan Recovery - Amt'] = number_format((float) $row->loan_recv_amt, 2, '.', '');
            $val['Loan Against Investment - No. A/C'] = $row->loan_aginst_ac_no;
            $val['Loan Against Investment - Amt'] = $row->loan_aginst_amt;
            $val['Loan Against Investment Recovery - No. A/C'] = $row->loan_aginst_recv_ac_no;
            $val['Loan Against Investment Recovery - Amt'] = number_format((float) $row->loan_aginst_recv_amt, 2, '.', '');
            $val['Cash in hand'] = $row->cash_in_hand;
            if (!$headerDisplayed) {
                // Use the keys from $data as the titles
                fputcsv($handle, array_keys($val));
                $headerDisplayed = true;
            }
            // Put the data into the stream
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