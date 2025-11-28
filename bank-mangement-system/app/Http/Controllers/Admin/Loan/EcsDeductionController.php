<?php

namespace App\Http\Controllers\Admin\Loan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use URL;

use Illuminate\Support\Facades\Auth;
use App\Models\{Memberloans, Grouploans};

class EcsDeductionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        if (check_my_permission(Auth::user()->id, "347") != "1") {
            return redirect()->route('admin.dashboard');
        }
        $data['title'] = 'ECS Deduction Listing';
        return view('templates.admin.loan.ecs_deduction.index', $data);
    }

    // Listing the data from the loan and group loan table
    public function listing(Request $request)
    {
        $arrSearch = [];
        foreach ($request->searchData as $value) {
            $arrSearch[$value['name']] = $value['value'];
        }
        if ($arrSearch['loan_type'] == "") {
            $output = array('draw' => $_POST['draw'], 'data' => null, 'recordsTotal' => 0, 'recordsFiltered' => 0);
            return json_encode($output);
        }
        if ($arrSearch['loan_type'] == 'L' || $arrSearch['loan_type'] == 'all') {
            $memberLoanData = Memberloans::with([
                'loan:id,name',
                'loanBranch:id,regan',
                'member:id,first_name,last_name,mobile_no',
                'loanBranch:id,name,regan,sector,branch_code',
                'collectorAssociate.member_collector:id,first_name,last_name,associate_no'
            ])
                ->select(['id', 'emi_option', 'approve_date', 'customer_id', 'amount', 'emi_amount', 'emi_due_date', 'loan_type', 'ecs_type', 'ecs_ref_no', 'account_number', 'branch_id','new_emi_amount','transfer_amount'])
                ->where('ecs_type', '>', 0)->where('status', 4);
            if ($arrSearch['ecs_type'] != '') {
                $memberLoanData = $memberLoanData->where('ecs_type', $arrSearch['ecs_type']);
            }
            if (!empty($arrSearch['company_id'])) {
                $memberLoanData = $memberLoanData->where('company_id', $arrSearch['company_id']);
            }
            if (!empty($arrSearch['emi_due_date'] && $arrSearch['emi_due_to_date'])) {
                $emi_due_date = date('Y-m-d', strtotime(convertDate($arrSearch['emi_due_date'])));
                $emi_due_to_date = date('Y-m-d', strtotime(convertDate($arrSearch['emi_due_to_date'])));
                $memberLoanData = $memberLoanData->whereBetween('emi_due_date', [$emi_due_date, $emi_due_to_date]);
            } elseif (!empty($arrSearch['emi_due_date'])) {
                $fromDate = date('Y-m-d', strtotime(convertDate($arrSearch['emi_due_date'])));
                $memberLoanData = $memberLoanData->where('emi_due_date', $fromDate);
            } elseif (!empty($arrSearch['emi_due_to_date'])) {
                $toDate = date('Y-m-d', strtotime(convertDate($arrSearch['emi_due_to_date'])));
                $memberLoanData = $memberLoanData->where('emi_due_date', $toDate);
            }
            $data = $memberLoanData;
        }
        if ($arrSearch['loan_type'] == 'G' ||  $arrSearch['loan_type'] == 'all') {
            $groupLoanData = Grouploans::with([
                'loan:id,name',
                'loanBranch:id,regan',
                'member:id,first_name,last_name,mobile_no',
                'loanBranch:id,name,regan,sector,branch_code',
                'collectorAssociate.member_collector:id,first_name,last_name,associate_no'
            ])
                ->select(['id', 'emi_option', 'approve_date', 'customer_id', 'amount', 'emi_amount', 'emi_due_date', 'loan_type', 'ecs_type', 'ecs_ref_no', 'account_number', 'branch_id','new_emi_amount','transfer_amount'])
                ->where('ecs_type', '>', 0)->where('status', 4);
            if ($arrSearch['ecs_type'] != '') {
                $groupLoanData = $groupLoanData->where('ecs_type', $arrSearch['ecs_type']);
            }
            if (!empty($arrSearch['company_id'])) {
                $groupLoanData = $groupLoanData->where('company_id', $arrSearch['company_id']);
            }
            if (!empty($arrSearch['emi_due_date'] && $arrSearch['emi_due_to_date'])) {
                $fromDate = date('Y-m-d', strtotime(convertDate($arrSearch['emi_due_date'])));
                $toDate = date('Y-m-d', strtotime(convertDate($arrSearch['emi_due_to_date'])));
                $groupLoanData = $groupLoanData->whereBetween('emi_due_date', [$fromDate, $toDate]);
            } elseif (!empty($arrSearch['emi_due_date'])) {
                $gfromDate = date('Y-m-d', strtotime(convertDate($arrSearch['emi_due_date'])));
                $groupLoanData = $groupLoanData->where('emi_due_date', $gfromDate);
            } elseif (!empty($arrSearch['emi_due_to_date'])) {
                $gtoDate = date('Y-m-d', strtotime(convertDate($arrSearch['emi_due_to_date'])));
                $groupLoanData = $groupLoanData->where('emi_due_date', $gtoDate);
            }
            $data = $groupLoanData;
        }
        if ($arrSearch['loan_type'] == 'all') {
            $data = $memberLoanData->union($groupLoanData);
        }
        $totalCount = $data->count();

        $export = $data->orderBy('id', 'desc')->get();

        $token = session()->get('_token');
        Cache::put('ecs_deduction_data' . $token, $export);
        Cache::put('ecs_deduction_count' . $token, $totalCount);

        $data = $data->orderBy('id', 'desc')->offset($_POST['start'])->limit($_POST['length'])->get();
        // pd($data->toArray());
        $sno = $_POST['start'];
        $rowReturn = [];
        foreach ($data as $value) {
            $val['s_no'] = ++$sno;
            $val['emi_due_date'] = ($value->emi_due_date == '') ? 'N/A' : date('d/m/Y', strtotime(convertDate($value->emi_due_date)));
            $val['amount'] = ($value->amount == '') ?'N/A' : $value->amount;
            $val['emi_amount'] = ($value->new_emi_amount == '') ? $value->emi_amount : $value->new_emi_amount;
            $val['account_number'] = ($value->account_number == '') ? 'N/A' : $value->account_number;
            $val['loan_type'] = ($value['loan']->name == '') ? 'N/A' : $value['loan']->name;
            $val['regan'] = ($value['loanBranch']->regan == '') ? 'N/A' : $value['loanBranch']->regan;
            if ($value->ecs_type == 1) {
                $ecs_type = 'Bank';
            } else if ($value->ecs_type == 2) {
                $ecs_type = 'SSB';
            } else {
                $ecs_type = 'Default';
            }
            $val['ecs_type'] = ($value->ecs_type == '') ? 'N/A' : $ecs_type;
            $val['transfer_date'] = ($value->approve_date == '') ? 'N/A' : $value->approve_date;
            if ($value->emi_option == 1) {
                $emi_mode = 'Monthly';
            } elseif ($value->emi_option == 2) {
                $emi_mode = 'Weekly';
            } elseif ($value->emi_option == 3) {
                $emi_mode = 'Daily';
            } else {
                $emi_mode = 'Default';
            }
            $val['emi_mode'] = ($value->emi_option == '') ? 'N/A' : $emi_mode;
            $val['ecs_ref_no'] = ($value->ecs_ref_no == '') ? 'N/A' : $value->ecs_ref_no;
            $val['branch_id'] = ($value['loanBranch'] == '') ? 'N/A' : $value['loanBranch']->name;
            $val['customer_first_last_name'] = ($value['member'] == '') ? 'N/A' : $value['member']->first_name . ' ' . $value['member']->last_name;
            $val['mobile_no'] = ($value['member'] == '') ? 'N/A' : $value['member']->mobile_no;
            $memberDetails = $value['collectorAssociate']['member_collector'] ?? null;
            $val['associate_first_last_name'] = ($memberDetails == '') ? 'N/A' : $memberDetails->first_name . ' ' . $memberDetails->last_name;
            $val['associate_code'] = ($memberDetails == '') ? 'N/A' : $memberDetails->associate_no;
            $rowReturn[] = $val;
        }
        $output = array('draw' => $_POST['draw'], 'data' => $rowReturn, 'recordsTotal' => $totalCount, 'recordsFiltered' => $totalCount);
        // dd($output);
        return json_encode($output);
    }

    public function bankExport(Request $request)
    {
        $arrSearch = [];
        foreach ($request->all() as $k => $value) {
            $arrSearch[$k] = $value;
        }
        $input = $request->all();
        $start = $input["start"];
        $limit = $input["limit"];
        $returnURL = URL::to('/') . "/report/bank_Export.csv";
        $fileName = env('APP_EXPORTURL') . "report/bank_Export.csv";
        header("Content-type: text/csv");
        if ($arrSearch['loan_type'] == 'L' || $arrSearch['loan_type'] == 'all') {
            $memberLoanData = Memberloans::with([
                'company:id,client_code,short_name',
                'loan:id,name'
            ])
                ->select(['id', 'company_id', 'emi_option', 'approve_date', 'customer_id', 'emi_amount', 'emi_due_date', 'loan_type', 'ecs_type', 'ecs_ref_no', 'account_number', 'branch_id',\DB::raw("CONCAT(account_number, LPAD(PERIOD_DIFF(DATE_FORMAT(emi_due_date,'%Y%m'), DATE_FORMAT(approve_date,'%Y%m')), 2, '0')) AS 'C_T_R_N'")])
                ->where('ecs_type', 1)->where('status', 4)->whereDoesntHave('loan', function ($query) {
                    $query->where('name', 'like', '%SPECIAL%');
                });
            if (!empty($arrSearch['company_id'])) {
                $memberLoanData = $memberLoanData->where('company_id', $arrSearch['company_id']);
            }
            if (!empty($arrSearch['emi_due_date'] && $arrSearch['emi_due_to_date'])) {
                $emi_due_date = date('Y-m-d', strtotime(convertDate($arrSearch['emi_due_date'])));
                $emi_due_to_date = date('Y-m-d', strtotime(convertDate($arrSearch['emi_due_to_date'])));
                $memberLoanData = $memberLoanData->whereBetween('emi_due_date', [$emi_due_date, $emi_due_to_date]);
            } elseif (!empty($arrSearch['emi_due_date'])) {
                $fromDate = date('Y-m-d', strtotime(convertDate($arrSearch['emi_due_date'])));
                $memberLoanData = $memberLoanData->where('emi_due_date', $fromDate);
            } elseif (!empty($arrSearch['emi_due_to_date'])) {
                $toDate = date('Y-m-d', strtotime(convertDate($arrSearch['emi_due_to_date'])));
                $memberLoanData = $memberLoanData->where('emi_due_date', $toDate);
            }
            $data = $memberLoanData;
        }
        if ($arrSearch['loan_type'] == 'G' ||  $arrSearch['loan_type'] == 'all') {
            $groupLoanData = Grouploans::with([
                'company:id,client_code,short_name',
                'loan:id,name'
            ])
                ->select(['id', 'company_id', 'emi_option', 'approve_date', 'customer_id', 'emi_amount', 'emi_due_date', 'loan_type', 'ecs_type', 'ecs_ref_no', 'account_number', 'branch_id',\DB::raw("CONCAT(account_number, LPAD(PERIOD_DIFF(DATE_FORMAT(emi_due_date,'%Y%m'), DATE_FORMAT(approve_date,'%Y%m')), 2, '0')) AS 'C_T_R_N'")])
                ->where('ecs_type', 1)->where('status', 4)->whereDoesntHave('loan', function ($query) {
                    $query->where('name', 'like', '%SPECIAL%');
                });
            if (!empty($arrSearch['company_id'])) {
                $groupLoanData = $groupLoanData->where('company_id', $arrSearch['company_id']);
            }
            if (!empty($arrSearch['emi_due_date'] && $arrSearch['emi_due_to_date'])) {
                $fromDate = date('Y-m-d', strtotime(convertDate($arrSearch['emi_due_date'])));
                $toDate = date('Y-m-d', strtotime(convertDate($arrSearch['emi_due_to_date'])));
                $groupLoanData = $groupLoanData->whereBetween('emi_due_date', [$fromDate, $toDate]);
            } elseif (!empty($arrSearch['emi_due_date'])) {
                $gfromDate = date('Y-m-d', strtotime(convertDate($arrSearch['emi_due_date'])));
                $groupLoanData = $groupLoanData->where('emi_due_date', $gfromDate);
            } elseif (!empty($arrSearch['emi_due_to_date'])) {
                $gtoDate = date('Y-m-d', strtotime(convertDate($arrSearch['emi_due_to_date'])));
                $groupLoanData = $groupLoanData->where('emi_due_date', $gtoDate);
            }
            $data = $groupLoanData;
        }

        if ($arrSearch['loan_type'] == 'all') {
            $data = $memberLoanData->union($groupLoanData);
        }
        $totalCount = $data->count();
        $data = $data->orderBy('id', 'desc')->offset($start)->limit($limit)->get();

        $totalResults = $totalCount;
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
        foreach ($data as $value) {
            // $val['s_no'] = ++$sno;
            $val['Client Code'] = ($value['company']->client_code) ? $value['company']->client_code : $value['company']->short_name;
            // $val['Batch Reference Number'] = ($value['loan'] == '') ? 'N/A' : $value['loan']->name;
            $val['Batch Reference Number'] = ($value['company'] == '') ? 'N/A' : $value['company']->client_code . '' . date('dmy', strtotime(convertdate($arrSearch['create_application_date']))) . '' . '001';
            $val['Settlement Date'] = ($value->emi_due_date == '') ? 'N/A' : date('d/m/Y', strtotime(convertDate($value->emi_due_date)));
            $val['Transaction Amount'] = ($value->new_emi_amount == '') ? $value->emi_amount : $value->new_emi_amount;
            $val['Customer Transaction ref Number'] = ($value->C_T_R_N == '') ? 'N/A' : $value->C_T_R_N;
            $val['UMRN '] = ($value->ecs_ref_no == '') ? 'N/A' : $value->ecs_ref_no;
            if (!$headerDisplayed) {
                // Use the keys from $data as the titles
                fputcsv($handle, array_keys($val));
                $headerDisplayed = true;
            }
            // Put the data into the stream
            fputcsv($handle, $val);
        }
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

    public function export(Request $request)
    {
        $token = session()->get('_token');
        $data = Cache::get('ecs_deduction_data' . $token);
        $count = Cache::get('ecs_deduction_count' . $token);

        $input = $request->all();

        $start = $input['start'];
        $limit = $input['limit'];

        $returnURL = URL::to('/') . "/asset/ecs_deduction.csv";

        $fileName = env('APP_EXPORTURL') . "/asset/ecs_deduction.csv";

        $totalResults = $count;
        $result = 'next';
        if (($start + $limit) >= $totalResults) {
            $result = 'finished';
        }

        if ($start == 0) {
            $handle = fopen($fileName, 'w');
            $headerDisplayed = false;
        } else {
            $handle = fopen($fileName, 'a');
            $headerDisplayed = true;
        }

        $sno = $_POST['start'];
        $records = $data->slice($start, $limit);
        foreach ($records as $row) {
            $sno++;
            $val['S/No'] = $sno;

            $val['Region'] = ($row['loanBranch'] == '') ? 'N/A' : $row['loanBranch']->regan;
            $val['Branch'] = ($row['loanBranch'] == '') ? 'N/A' : $row['loanBranch']->name;

            $val['Customer Name'] = ($row['member'] == '') ? 'N/A' : $row['member']->first_name . ' ' . $row['member']->last_name;

            $val['Account Number'] = ($row->account_number == '') ? 'N/A' : $row->account_number;

            $val['Plan'] = ($row['loan']->name == '') ? 'N/A' : $row['loan']->name;

            $val['Loan Amount'] = ($row->amount == '') ? 'N/A' : $row->amount;
            
            $val['Sanction Date'] = ($row->approve_date == '') ? 'N/A' : $row->approve_date;

            $val['EMI Amount'] = ($row->new_emi_amount == '') ? $row->emi_amount : $row->new_emi_amount;

            $val['Emi Date Due'] = ($row->emi_due_date == '') ? 'N/A' : date('d/m/Y', strtotime(convertDate($row->emi_due_date)));

            if ($row->emi_option == 1) {
                $emi_mode = 'Monthly';
            } else if ($row->emi_option == 2) {
                $emi_mode = 'Weekly';
            } else if ($row->emi_option == 3) {
                $emi_mode = 'Daily';
            } else {
                $emi_mode = 'Default';
            }
            $val['Emi Mode'] = $emi_mode;

            if ($row->ecs_type == 1) {
                $ecs_type = 'Bank';
            } else if ($row->ecs_type == 2) {
                $ecs_type = 'SSB';
            } else {
                $ecs_type = 'Default';
            }
            
            $val['Mobile'] = ($row['member'] == '') ? 'N/A' : $row['member']->mobile_no;
            
            $val['ECS Reference No'] = ($row->ecs_ref_no == '') ? 'N/A' : $row->ecs_ref_no;
            $val['ECS Type'] = $ecs_type;


            // $memberDetails = $row['collectorAssociate']['member_collector'] ?? null;

            // $val['Associate Code'] = ($memberDetails == '') ? 'N/A' : $memberDetails->associate_no;

            // $val['Associate Name'] = ($memberDetails == '') ? 'N/A' : $memberDetails->first_name . ' ' . $memberDetails->last_name;

            // $val['BR Code'] = ($row['loanBranch'] == '') ? 'N/A' : $row['loanBranch']->branch_code;

            // $val['BR Region'] = ($row['loanBranch'] == '') ? 'N/A' : $row['loanBranch']->regan;

            // $val['BR Sector'] = ($row['loanBranch'] == '') ? 'N/A' : $row['loanBranch']->sector;

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
        // Make sure nothing else is sent, our file is done
        exit;
    }
}
