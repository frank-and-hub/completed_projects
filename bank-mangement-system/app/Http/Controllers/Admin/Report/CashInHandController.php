<?php
namespace App\Http\Controllers\Admin\Report;
use App\Models\BranchDaybook;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Branch;
use App\Models\Companies;
use App\Models\FundTransfer;
use URL;
use App\Http\Traits\BranchPermissionRoleWiseTrait;
use App\Http\Traits\sumAmountTraits;
/*
|---------------------------------------------------------------------------
| Admin Panel -- Member Management MemberController
|--------------------------------------------------------------------------
|
| This controller handles members all functionlity.
*/
class CashInHandController extends Controller
{
    /**
     * Create a new controller instance.
     * @return void
     */
    use BranchPermissionRoleWiseTrait, sumAmountTraits;
    public function __construct()
    {
        // check user login or not
        $this->middleware('auth');
    }
    public function cashInHand()
    {
        if (check_my_permission(Auth::user()->id, "300") != "1") {
            return redirect()->route('admin.dashboard');
        }
        $data['title'] = 'Report | Branch Cash In hand Report';
        $datas = Branch::where('status', 1);
        if (Auth::user()->branch_id > 0) {
            $ids = $this->getDataRolewise(new Branch());
            $datas = $datas->whereIn('id', $ids);
        }
        $data['branch'] = $datas->get();
        return view('templates.admin.report.cashinhand.index', $data);
    }
    public function cash_in_hand_Listing(Request $request)
    {
        $arrFormData = array();
        $arrFormData['searchform'] = $request->searchform;
        $arrFormData['start_date'] = $request->start_date;
        $arrFormData['end_date'] = $request->end_date;
        $arrFormData['branch_id'] = $request->branch_id;
        $arrFormData['company_id'] = $request->company_id;
        $arrFormData['is_search'] = $request->is_search;
        if ($request->ajax() && $arrFormData['is_search'] == 'yes') {
            if (isset($arrFormData['is_search']) && $arrFormData['is_search'] == 'yes') {
                if ($arrFormData['start_date'] != '') {
                    $startDate = date("Y-m-d", strtotime(convertDate($arrFormData['start_date'])));
                } else {
                    $startDate = date("Y-m-d", strtotime(convertDate($request->globalDate)));
                }
                if ($arrFormData['end_date'] != '') {
                    $endDate = date("Y-m-d ", strtotime(convertDate($arrFormData['end_date'])));
                } else {
                    $endDate = date("Y-m-d", strtotime(convertDate($request->globalDate)));
                }
                $data = Branch::select('id', 'name', 'branch_code', 'date', 'total_amount', 'sector', 'regan', 'zone')
                    ->with('companybranchs')
                    ->with(['companybranchs' => function ($query) {
                        $query->select('id', 'company_id');
                    }])
                    ->where('status', 1);
                if ($arrFormData['start_date'] != '') {
                    $startDate = date("Y-m-d", strtotime(convertDate($arrFormData['start_date'])));
                    if ($arrFormData['end_date'] != '') {
                        $endDate = date("Y-m-d", strtotime(convertDate($arrFormData['end_date'])));
                    }
                }
                if ($arrFormData['branch_id'] > 0 && isset($arrFormData['branch_id'])) {
                    if ($arrFormData['branch_id'] > 0) {
                        $data = $data->where('id', $arrFormData['branch_id']);
                    }
                }
                if ($arrFormData['company_id'] != '' && $arrFormData['company_id'] > 0) {
                    $data = $data->whereHas('companybranchs', function ($query) use ($arrFormData) {
                        $query->where('company_branch.company_id', $arrFormData['company_id']);
                    });
                }
                $data2 = $data->get();
                $count = $data->count('id');
                $data = $data->offset($_POST['start'])->limit($_POST['length'])->get();
                $totalCount = $count;
                $sno = $_POST['start'];
                $rowReturn = array();
                $rowReturn2 = array();
                foreach ($data as $row) {
                    $details = $this->cashAmountDetails(new BranchDaybook(), NULL, '0', $startDate, $endDate, 'payment_type', 'entry_date', 'branch_id', 'payment_mode', 'type', 'sub_type', 'sum', 'amount', $row->id, 7, 70, $row->date, $row->total_amount, $arrFormData['company_id']);
                    $clos = $details['CR'] + $details['opening_balance'];
                    $close = $clos - $details['DR'];
                    $frecord = FundTransfer::select('amount', 'branch_id', 'company_id')->where('transfer_type', 0)->where('branch_id', $row->id)->when($request->company_id > 0, function ($q) use ($request) {
                        return $q->where('company_id', $request->company_id);
                    })->where('status', 0)->where(function ($query) use ($startDate, $endDate) {
                        $query->whereDate('created_at', $startDate)
                            ->orwhereDate('created_at', '=', $endDate)
                            ->orwhereBetween('created_at', [$startDate, $endDate]);
                    })->sum('amount');
                    $company = Companies::whereId($arrFormData['company_id'])->first(['id', 'name']);
                    $sno++;
                    $val['company_name'] = isset($company->name) ? $company->name : "All";
                    $val['DT_RowIndex'] = $sno;
                    $val['branch'] = isset($row->name) ? $row->name : "N/A";
                    $val['branch_code'] = $row->branch_code;
                    $val['opening_cashinhand'] = number_format($details['opening_balance'], 2);
                    $val['total_cash_receving'] = number_format($details['CR'], 2);
                    $val['total_cash_Payment'] = number_format($details['DR'], 2);
                    $val['approve_banking'] = number_format($details['approve'], 2);
                    $val['unapprove_banking'] = number_format($frecord, 2);
                    $val['closing_cashinhand'] = number_format($close, 2);
                    $val['zone'] = $row->zone;
                    $val['regan'] = $row->regan;
                    $val['sector'] = $row->sector;
                    $rowReturn[] = $val;
                }
                foreach ($data2 as $row) {
                    $details = $this->cashAmountDetails(new BranchDaybook(), NULL, '0', $startDate, $endDate, 'payment_type', 'entry_date', 'branch_id', 'payment_mode', 'type', 'sub_type', 'sum', 'amount', $row->id, 7, 70, $row->date, $row->total_amount, $arrFormData['company_id']);
                    $clos = $details['CR'] + $details['opening_balance'];
                    $close = $clos - $details['DR'];
                    $frecord = FundTransfer::select('amount', 'branch_id', 'company_id')->where('transfer_type', 0)->where('branch_id', $row->id)->when($request->company_id > 0, function ($q) use ($request) {
                        return $q->where('company_id', $request->company_id);
                    })->where('status', 0)->where(function ($query) use ($startDate, $endDate) {
                        $query->whereDate('created_at', $startDate)
                            ->orwhereDate('created_at', '=', $endDate)
                            ->orwhereBetween('created_at', [$startDate, $endDate]);
                    })->sum('amount');
                    $company = Companies::whereId($arrFormData['company_id'])->first(['id', 'name']);
                    $sno++;
                    $val['company_name'] = isset($company->name) ? $company->name : 'All';
                    $val['branch'] = isset($row->name) ? $row->name : 'N/A';
                    $val['branch_code'] = $row->branch_code;
                    $val['opening_cashinhand'] = number_format($details['opening_balance'], 2);
                    $val['total_cash_receving'] = $details['CR'];
                    $val['total_cash_Payment'] = $details['DR'];
                    $val['approve_banking'] = $details['approve'];
                    $val['unapprove_banking'] = number_format($frecord, 2);
                    $val['closing_cashinhand'] = number_format($close, 2);
                    $val['zone'] = $row->zone;
                    $val['regan'] = $row->regan;
                    $val['sector'] = $row->sector;
                    $rowReturn2[] = $val;
                }
                $token = session()->get('_token');
                $Cache = Cache::put('cashinhand_demandlist' . $token, $rowReturn2);
                Cache::put('cashinhand_demandlist_count' . $token, $totalCount);
                $output = array("draw" => $_POST['draw'], "recordsTotal" => $totalCount, "recordsFiltered" => $count, "data" => $rowReturn, );
            }
        } else {
            $output = array("draw" => 0, "recordsTotal" => 0, "recordsFiltered" => 0, "data" => 0);
        }
        return json_encode($output);
    }
    public function cashinhand_demandlistExport(Request $request)
    {
        $token = session()->get('_token');
        $data = Cache::get('cashinhand_demandlist' . $token);
        $count = Cache::get('cashinhand_demandlist_count' . $token);
        $input = $request->all();
        $start = $_POST["start"];
        $limit = $_POST["limit"];
        $returnURL = URL::to('/') . "/report/Cash_in_hand_report.csv";
        $fileName = env('APP_EXPORTURL') . "/report/Cash_in_hand_report.csv";
        global $wpdb;
        $postCols = array(
            'post_title',
            'post_content',
            'post_excerpt',
            'post_name',
        );
        header("Content-type: text/csv");
        $totalResults = $count;
        $results = $data;
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
        $results = array_slice($results, $start, $limit);
        foreach ($results as $row) {
            $sno++;
            $val['S/N'] = $sno;
            $val['company name'] = $row['company_name'];
            $val['Branch'] = $row['branch'];
            $val['Branch Code'] = $row['branch_code'];
            if (isset($row['sector'])) {
                $val['Sector'] = $row['sector'];
            } else {
                $val['Sector'] = "N/A";
            }
            if (isset($row['regan'])) {
                $val['Region'] = $row['regan'];
            } else {
                $val['Region'] = "N/A";
            }
            if (isset($row['zone'])) {
                $val['Zone'] = $row['zone'];
            } else {
                $val['Zone'] = "N/A";
            }
            $val['Opening Cash In Hand'] = $row['opening_cashinhand'];
            $val['Total Cash Receving'] = $row['total_cash_receving'];
            $val['Total Cash Payment'] = $row['total_cash_Payment'];
            $val['Approve Banking'] = $row['approve_banking'];
            $val['Unapprove Banking'] = $row['unapprove_banking'];
            $val['Closing Cash In Hand'] = $row['closing_cashinhand'];
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