<?php
namespace App\Http\Controllers\Admin\Report;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use URL;
use Auth;
use DB;
use App\Models\Branch;
use App\Models\Daybook;
use App\Models\Plans;
use App\Models\Memberinvestments;
use Illuminate\Support\Facades\Cache;

class DepositAmountReportController extends Controller
{
	public function __construct()
	{
		// check user login or not
		$this->middleware('auth');
	}
	public function index()
	{
		if (check_my_permission(Auth::user()->id, "303") != "1") {
			return redirect()->route('admin.dashboard');
		}

		$data['title'] = 'Report | Deposit Amount Report';

		$datas = Branch::where('status', 1);
		if (Auth::user()->branch_id > 0) {
			$ids = $this->getDataRolewise(new Branch());
			$datas = $datas->whereIn('id', $ids);
		}
		$data['branch'] = $datas->get();
		$data['plans'] = Plans::has('company')->where('plan_category_code', '!=', 'S')->where('id', '!=', 1)->pluck('name', 'id');
		return view('templates.admin.report.depositamount.index', $data);
	}
	public function listing(Request $request)
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
					$branch_id = $arrFormData['branch_id'];
					$companyId = $companyId > 0 ? $companyId : '';
					$branch_id = $branch_id > 0 ? $branch_id : '';
					$arrFormData['start_date'] = $arrFormData['date_range'];
					$arrFormData['end_date'] = $arrFormData['end_date'];
					$plan_id = $arrFormData['plan_id'];
					$arrFormData['is_search'] = $request->is_search;
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
					$pageNo = 0;
					$perPageRecord = '';
					if ($_POST['length']) {
						$perPageRecord = $_POST['length'];
					}
					if ($_POST['start'] == 0) {
						$pageNo = 1;
					} else {
						$pageGet = $_POST['start'] / $_POST['length'];
						$pageNo = $pageGet + 1;
					}
					$data = DB::select('call deposit_account_report(?,?,?,?,?)', [$startDate, $branch_id, $plan_id, $endDate, $companyId]);
					$sno = $_POST['start'];
					$count = count($data);
					$rowReturn = [];
					$record = array_slice($data, $_POST['start'], $_POST['length']);
					$totalCount = count($record);
					foreach ($record as $row) {
						$sno++;
						$val['DT_RowIndex'] = $sno;
						$val['company_name'] = $row->company_name;
						$val['branch'] = $row->branch_name;
						$val['plan'] = $row->plan_name;
						$val['plan_tenure'] = round($row->plan_tenure);
						$val['demo_amount'] = number_format($row->demo_amount, 2);
						$val['renewal_amount'] = number_format($row->renewal_amount, 2);
						$val['total'] = number_format($row->total, 2);
						$val['maturity_deno'] = number_format($row->maturity_deno, 2);
						$val['maturity_total_amount'] = number_format($row->maturity_total_amount, 2);
						$rowReturn[] = $val;
					}
					$token = session()->get('_token');
					$Cache = Cache::put('depositamount_listAdmin' . $token, $data);
					Cache::put('depositamount_list_countAdmin' . $token, $count);
					$output = array("draw" => $_POST['draw'], "recordsTotal" => $totalCount, "recordsFiltered" => $count, "data" => $rowReturn, );
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
		$data = Cache::get('depositamount_listAdmin' . $token);
		$count = Cache::get('depositamount_list_countAdmin' . $token);
		$input = $request->all();
		$start = $input["start"];
		$limit = $input["limit"];
		$returnURL = URL::to('/') . "/asset/depositaccountreport.csv";
		$fileName = env('APP_EXPORTURL') . "asset/depositaccountreport.csv";
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
		$count = count($data);
		$rowReturn = [];
		$record = array_slice($data, $start, $limit);
		$totalCount = count($record);
		foreach ($record as $row) {
			$sno++;
			$val['S/N'] = $sno;
			$val['Company Name'] = $row->company_name;
			$val['Branch'] = $row->branch_name;
			$val['Branch Code'] = $row->branch_code;
			$val['Region'] = $row->regan;
			$val['Sector'] = $row->sector;
			$val['Zone'] = $row->zone;
			$val['Plan'] = $row->plan_name;
			$val['Plan Tenure (in month)'] = $row->plan_tenure;
			$val['Demo Amount'] = number_format($row->demo_amount, 2);
			$val['Renewal Amount'] = number_format($row->renewal_amount, 2);
			$val['Total'] = number_format($row->total, 2);
			$val['Maturity Deno'] = number_format($row->maturity_deno, 2);
			$val['Maturity Total Amount'] = number_format($row->maturity_total_amount, 2);
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

	public function allplans(Request $request)
	{
		$company_id = $request['company_id'];
		$plans = Plans::has('company')
			->where('plan_category_code', '!=', 'S')
			->when($company_id > 0, function ($q) use ($company_id) {
				$q->where('company_id', $company_id);
			})
			->get(['name', 'id', 'plan_category_code', 'plan_sub_category_code', 'company_id']);
		$return_array = compact('plans');
		return json_encode($return_array);
	}
}