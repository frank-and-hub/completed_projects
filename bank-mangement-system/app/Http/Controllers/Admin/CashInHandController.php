<?php
namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Auth;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use App\Interfaces\RepositoryInterface;
use URL;

class CashInHandController extends Controller
{
	public function __construct(RepositoryInterface $repository)
	{
		$this->middleware('auth');
		$this->repository = $repository;
	}
	// Cash In Hand Table
	public function index()
	{
		if (check_my_permission(Auth::user()->id, "35") != "1") {
			return redirect()->route('admin.dashboard');
		}
		$data['title'] = "Cash In Hand Report";
		// $data['branches'] = $this->repository->getAllBranch()->get(['id', 'name']);
		$data['region'] = $this->repository->getAllBranch()->whereStatus('1')->groupBy('regan')->pluck('regan');
		$data['companys'] = $this->repository->getAllCompanies()->whereStatus('1')->pluck('name', 'id');
		return view('templates.admin.cash_in_hand.index', $data);
	}
	// Fetch Table Lsiting
	public function cashInHandListing(Request $request)
	{
		$arrFormData = array();
		if (!empty($_POST['searchform'])) {
			foreach ($_POST['searchform'] as $frm_data) {
				$arrFormData[$frm_data['name']] = $frm_data['value'];
			}
		}

		$company_id = $arrFormData['company_id'];
		$branch_id = $arrFormData['branch_id'];
		$region = $arrFormData['region'];
		$sector = $arrFormData['sector'];
		$start_date = $arrFormData['start_date'];
		$end_date = $arrFormData['end_date'];
		$is_search = $arrFormData['is_search'];

		if (
			$request->ajax() && 
			check_my_permission(Auth::user()->id, "35") == "1" && 
			$arrFormData['is_search'] == 'yes' && 
			$company_id != null
			) {
			$date = Carbon::today()->toDateString();
			$data = $this->repository->getAllBranchCurrentBalance()
				->has('company')
				->join('branch', 'branch_current_balance.branch_id', '=', 'branch.id')
				->join('companies', 'branch_current_balance.company_id', '=', 'companies.id')
				->with(['cashBranch:id,name,regan,sector', 'company:id,short_name,name']);
			if (isset($is_search) && $is_search == 'yes') {
				if (isset($region)) {
					if ($region) {
						$data = $data->whereHas('cashBranch', function ($q) use ($region) {
							$q->where('branch.regan', 'like', '%' . $region . '%');
						});
						if ($sector) {
							$data = $data->whereHas('cashBranch', function ($q) use ($sector) {
								$q->where('branch.sector', 'like', '%' . $sector . '%');
							});
							if (($branch_id) && ($branch_id != '0')) {
								$data = $data->where('branch_id', $branch_id);
							}
						}
					}
					// if ($request->search['value'] != null) {
					// 	$search = $request->search['value'];
					// 	$data = $data->whereHas('cashBranch', function ($q) use ($search) {
					// 		$q->where('branch.name', 'like', '%' . strtoupper($search) . '%');
					// 	});
					// }
					if (isset($company_id) && ($company_id != '0')) {
						$data = $data->where('company_id', $company_id);
					}
					if ($start_date != '') {
						$startDate = date("Y-m-d", strtotime(convertDate($start_date)));
						if ($end_date != '') {
							$endDate = date("Y-m-d ", strtotime(convertDate($end_date)));
						} else {
							$endDate = '';
						}
						$data = $data->whereBetween(\DB::raw('entry_date'), ["" . $startDate . "", "" . $endDate . ""]);
					}
				} else {
					$data = $data->whereDate('entry_date', $date);
				}
				if ($company_id == '0') {
					$data = $data->select([
						\DB::raw("sum(branch_current_balance.CR) as CR"),
						\DB::raw("sum(branch_current_balance.DR) as DR"),
						\DB::raw("sum(branch_current_balance.totalAmount) as totalAmount"),
						'branch_current_balance.branch_id',
						'branch_current_balance.entry_date'
					])
					->groupBy('branch_current_balance.entry_date', 'branch_current_balance.branch_id');
				}
				$c = $data;
				$export = $data;
				$count = count($c->get('entry_date')->toArray());
				$token = session()->get('_token');
				if ($_POST['start'] == 0) {
					Cache::put('cash_in_hand_list' . $token, $export->orderBy('entry_date')->orderBy('branch_id')->get());
					Cache::put('cash_in_hand_list_count' . $token, $count);
				}
				$data = $data->orderBy('entry_date')
					->orderBy('branch_id')
					->skip($_POST['start'])
					->take($_POST['length'])
					->get();
				$totalCount = $count;
				$sn = 0;
				$rowReturn = array();
				foreach ($data as $key) {
					$details = $this->getOpeningAndBanking($company_id, $key);
					$sn++;
					$val['DT_RowIndex'] = $sn;
					$val['company_name'] = (($company_id == 0) ? 'All Company' : ($key['company']->short_name));
					$val['date'] = date('d/m/Y', strtotime($key->entry_date));
					// $val['region'] = ($key['cashBranch'][0]['regan']) ?? "N/A";
					// $val['sector'] = ($key['cashBranch'][0]['sector']) ?? "N/A";
					$val['branch_name'] = ($key['cashBranch'][0]['name']) ?? "N/A";
					$val['opening'] = number_format($details['opening'], '2', '.', '');
					$val['collection'] = number_format($key->CR, '2', '.', '');
					$val['payment'] = number_format($key->DR, '2', '.', '');
					$val['closing'] = number_format($key->totalAmount + $details['opening'], '2', '.', '');
					$val['banking'] = number_format($details['banking'], '2', '.', '');
					$rowReturn[] = $val;
				}
				$output = array("draw" => $_POST['draw'], "recordsTotal" => $totalCount, "recordsFiltered" => $count, "data" => $rowReturn);
			}
		} else {
			$output = array("draw" => 0, "recordsTotal" => 0, "recordsFiltered" => 0, "data" => 0);
		}
		return json_encode($output);
	}
	public function branchcurrentbalance($key, $branch_id, $company_id, $globaldate, $startDate, $getBranchAmount)
	{
		$entry_date = date("Y-m-d", strtotime(convertDate($globaldate)));

		$amount = ($company_id == 1) ? $getBranchAmount : 0;

		$data = $this->repository->getAllBranchCurrentBalance()
			->select('id', 'entry_date', 'branch_id', 'company_id', 'totalAmount')
			->when(isset($branch_id) && ($branch_id > '0') && ($branch_id != ''), function ($q) use ($branch_id) {
				$q->where('branch_id', $branch_id);
			})
			->where('entry_date', '<', $entry_date)
			->when(isset($startDate), function ($q) use ($startDate) {
				$q->whereDate('entry_date', '>=', $startDate);
			})
			->when($company_id > 0, function ($q) use ($company_id) {
				$q->where('company_id', $company_id);
			})
			->sum('totalAmount');
		return $data + $amount;
	}
	public function branchbankingbalance($key, $company_id, $branch_id, $sector, $region)
	{
		$data = $this->repository->getAllFundTransfer()
			->select('funds_transfer.branch_id', 'funds_transfer.transfer_date_time', 'branch.id', 'branch.regan', 'branch.sector', 'funds_transfer.company_id', 'funds_transfer.transfer_type')
			->join('branch', 'funds_transfer.branch_id', '=', 'branch.id')
			->whereNotNull('funds_transfer.transfer_date_time')
			->whereDate('funds_transfer.transfer_date_time', '=', $key->entry_date)
			->where('funds_transfer.transfer_type', '0')
			->when(isset($region) && ($region != '0') && ($region != ''), function ($q) use ($region) {
				$q->where('branch.regan', 'like', '%' . $region . '%');
			})
			->when(isset($sector) && ($sector != '0') && ($region != ''), function ($q) use ($sector) {
				$q->where('branch.sector', 'like', '%' . $sector . '%');
			})
			->when(isset($branch_id) && ($branch_id != '0') && ($branch_id != ''), function ($q) use ($branch_id) {
				$q->where('funds_transfer.branch_id', $branch_id);
			})
			->when(isset($company_id) && ($company_id != '0'), function ($q) use ($company_id) {
				$q->where('funds_transfer.company_id', $company_id);
			})
			->sum('amount');
		return $data;
	}
	public function region_sector(Request $request)
	{
		$data = $this->repository->getAllBranch()->whereStatus('1')
			->where('regan', 'like', '%' . $request->region . '%')
			->groupBy('sector')
			->pluck('sector');
		return $data;
	}
	public function sector_branch(Request $request)
	{
		$data = $this->repository->getAllBranch()->whereStatus('1')
			->where('regan', 'like', '%' . $request->region . '%')
			->where('sector', 'like', '%' . $request->sector . '%')
			->pluck('name', 'id');
		return $data;
	}
	public function export(Request $request)
	{
		$token = session()->get('_token');
		$data = Cache::get('cash_in_hand_list' . $token);
		$count = Cache::get('cash_in_hand_list_count' . $token);
		$input = $request->all();
		$company_id = $input["company_id"];
		$start = $input["start"];
		$limit = $input["limit"];
		$returnURL = URL::to('/') . "/asset/cash_in_hand_list.csv";
		$fileName = env('APP_EXPORTURL') . "asset/cash_in_hand_list.csv";
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

		foreach ($data->slice($start,$limit) as $key) {
			$details = $this->getOpeningAndBanking($company_id, $key);
			$sno++;
			$val['S/N'] = $sno;
			$val['COMPANY NAME'] = (($company_id == 0) ? 'ALL COMPANY' : ($key['company']['short_name']));
			$val['DATE'] = date('d/m/Y', strtotime($key->entry_date));
			$val['BRANCH REGION'] = ($key['cashBranch'][0]['regan']) ?? "N/A";
			$val['BRANCH SECTOR'] = ($key['cashBranch'][0]['sector']) ?? "N/A";
			$val['BRANCH NAME'] = ($key['cashBranch'][0]['name']) ?? "N/A";
			$val['OPENING'] = number_format($details['opening'], '2', '.', '');
			$val['COLLECTION'] = number_format($key->CR, '2', '.', '');
			$val['PAYMENT'] = number_format($key->DR, '2', '.', '');
			$val['CLOSING'] = number_format($key->totalAmount + $details['opening'], '2', '.', '');
			$val['BANKING'] = number_format($details['banking'], '2', '.', '');
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
	public function getOpeningAndBanking($company_id, $key)
	{
		$entry_date = $key->entry_date;
		$branchId = $key['cashBranch'][0]['id'];
		$sector = $key['cashBranch'][0]['sector'];
		$region = $key['cashBranch'][0]['regan'];
		$startDate =  in_array($company_id,[1,0]) ? date("Y-m-d", strtotime(convertDate('2021-08-05'))) : date("Y-m-d", strtotime(convertDate('2023-04-01')));
		$getBranchAmount = $this->repository->getBranchById($branchId)->value('total_amount');
		$data['opening'] = 0;
		if ($company_id == 0) {
			foreach ($this->repository->getAllCompanies()->pluck('name', 'id') as $k => $v) {
				$data['opening'] += $this->branchcurrentbalance($key, $branchId, $k, $entry_date, $startDate, $getBranchAmount);
			}
		} else {
			$data['opening'] += $this->branchcurrentbalance($key, $branchId, $company_id, $entry_date, $startDate, $getBranchAmount);
		}
		$data['banking'] = $this->branchbankingbalance($key, $company_id, $branchId, $sector, $region);
		return $data;
	}
}
