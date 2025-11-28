<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use URL;
use DB; 
use Session; 
use Illuminate\Support\Facades\Cache;
class LoanEcsBounceChargesController extends Controller
{
    /**
     * Instantiate a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    public function ecsBounceChargesStatus()
	{
		$data['title'] = "Loan Management | Bounce ECS Current Status";
		return view('templates.admin.loan.ecs_bounce_current_status.index', $data);
	}
	public function ecsBounceChargesStatusListing(Request $req)
	{
		if ($req->ajax()) {
			$arrFormData = [];
			if (!empty($_POST['searchform'])) {
				foreach ($_POST['searchform'] as $frm_data) {
					$arrFormData[$frm_data['name']] = $frm_data['value'];
				}
			}
			if ($arrFormData['company_id'] != '' && isset($arrFormData['is_search']) && $arrFormData['is_search'] == 'yes') {
				$branch_id = isset($arrFormData['branch_id']) && $arrFormData['branch_id'] > 0 ? $arrFormData['branch_id'] : NULL;
				$company_id = isset($arrFormData['company_id']) && $arrFormData['company_id'] > 0 ? (int)$arrFormData['company_id'] : NULL;
				$date = isset($arrFormData['date']) ? date('Y-m-d',strtotime(convertdate($arrFormData['date']))) : NULL;
				$data = DB::select('call bounce_ecs_current_status(?,?,?)', [$branch_id, $company_id,$date]);
				$count = $totalCount = count($data);
				$sno = $_POST['start'];
				$rowReturn = array();
				if ($sno == 0) {
					$token = Session::get('_token');
					Cache::put('bounce_ecs_current_status_listing_admin_' . $token, $data);
					Cache::put('bounce_ecs_current_status_listing_count_admin_' . $token, $count);
				}
				$result = array_slice($data, $_POST['start'], $_POST['length']);
				foreach ($result as $row) {
					$sno++;
					$val = [
						'DT_RowIndex' => $sno,
						'regan' => $row->regan ?? 'N/A',
						'branch' => $row->branch ?? 'N/A',
						'date' => $row->date ? (date('d/m/Y', strtotime(convertdate($row->date)))) : 'N/A',
						'account_number' => $row->account_number ?? 'N/A',
						'plan' => u($row->plan) ?? 'N/A',
						'customer' => $row->customer ?? 'N/A',
						'mobile_no' => $row->mobile_no ?? 'N/A',
						'associate_no' => $row->associate_no ?? 'N/A',
						'associate' => $row->associate ?? 'N/A',
						'emi_amount' => isset($row->emi_amount) ? number_format($row->emi_amount, 2) . ' &#8377;' : 'N/A',
						'mode' => $row->mode ?? 'N/A',
						'due_emi' => isset($row->due_emi) ? $row->due_emi . ' &#8377;' : 'N/A'
					];
					$rowReturn[] = $val;
				}
				$output = ["draw" => $_POST['draw'], "recordsTotal" => $totalCount, "recordsFiltered" => $count, "data" => $rowReturn];
			} else {
				$output = array(
					"draw" => 0,
					"recordsTotal" => 0,
					"recordsFiltered" => 0,
					"data" => 0,
				);
			}
			return json_encode($output);
		}
	}
	public function ecsBounceChargesStatusListingExport(Request $req)
	{
		$token = Session::get('_token');
		$data = Cache::get('bounce_ecs_current_status_listing_admin_' . $token);
		$count = Cache::get('bounce_ecs_current_status_listing_count_admin_' . $token);

		$input = $req->all();
		$start = $input["start"];
		$limit = $input["limit"];
		$returnURL = URL::to('/') . "/report/bounce_ecs_current_status_listing.csv";
		$fileName = env('APP_EXPORTURL') . "report/bounce_ecs_current_status_listing.csv";

		// header("Content-type: text/csv");
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
		$records = array_slice($data, $start, $limit);
		foreach ($records as $row) {
			$sno++;
			$val = [
				'S/No' => $sno,
                'REGION' => $row->regan ?? 'N/A',
                'BRANCH' => $row->branch ?? 'N/A',
                'DATE' => $row->date ? (date('d/m/Y', strtotime(convertdate($row->date)))) : 'N/A',
                'ACCOUNT NO' => $row->account_number ?? 'N/A',
                'PLAN' => u($row->plan) ?? 'N/A',
                'CUSTOMER NAME' => $row->customer ?? 'N/A',
                'MOBILE NO' => $row->mobile_no ?? 'N/A',
                'CODE' => $row->associate_no ?? 'N/A',
                'COLLECTOR NAME' => $row->associate ?? 'N/A',
                'EMI AMOUNT' => isset($row->emi_amount) ? $row->emi_amount : 'N/A',
                'PAYMENT MODE' => $row->mode ?? 'N/A',
                'DUE AMOUNT' => isset($row->due_emi) ? $row->due_emi : 'N/A'
			];
			if (!$headerDisplayed) {
				fputcsv($handle, array_keys($val));
				$headerDisplayed = true;
			}
			fputcsv($handle, $val);
		}
		fclose($handle);
		if ($totalResults == 0) {
			$percentage = 100;
		} else {
			$percentage = ($start + $limit) * 100 / $totalResults;
			$percentage = number_format((float) $percentage, 1, '.', '');
		}
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
