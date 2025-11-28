<?php

namespace App\Http\Controllers\Admin\InvestmentCollector;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\MemberCompany;
use App\Models\SavingAccount;
use App\Models\SavingAccountTranscation;
use Auth;
use URL;
use Illuminate\Support\Facades\Cache;

class SSBAccountStatusController extends Controller
{
	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index()
	{

		if (check_my_permission(Auth::user()->id, "340") != "1") {
			return redirect()->route('admin.dashboard');
		}
		$data['saving_account'] = '';
		$data['title'] = 'SSB Account | Change Status';
		return view('templates.admin.investment_management.ssb_account_status.index', $data);
	}
	public function status_check(Request $request)
	{
		$data = SavingAccount::where('account_no', $request->account_number)
			->where('is_deleted', '0')
			->with([
				'savingBranch',
				'ssbMemberCustomer',
				'ssbMemberCustomer.member',
				'savingAccountBalance'
			]);
		$data = $data->first();
		$type = $request->type;
		if ($data) {
			$account_number = $data->account_no;
			$m_id = $data->ssbMemberCustomer ? $data->ssbMemberCustomer->id : 'N/A';
			$member_id = MemberCompany::has('member')->with('member')->where('id', $m_id)->first();
			if ($member_id == null) {
				$member_id = ' ';
			}
			$member_name = $data->ssbMemberCustomer ? (ucwords($data->ssbMemberCustomer->member->first_name) . ' ' . ucwords($data->ssbMemberCustomer->member->last_name)) : 'N/A';
			$current_balance = ($data->savingAccountBalance
				? ((($data->savingAccountBalance->where('payment_type', 'CR')->sum('deposit')) - ($data->savingAccountBalance->where('payment_type', 'DR')->sum('withdrawal')))
					?? 'N/A')
				: 'N/A');
			$transaction_status = $data->transaction_status == 1 ? 'Active' : 'Inactive';
			return \Response::json(['view' => view('templates.admin.investment_management.ssb_account_status.partials.account_detail', ['member_id' => $member_id->member->member_id ?? '', 'member_name' => $member_name, 'current_balance' => $current_balance, 'account_number' => $account_number, 'transaction_status' => $transaction_status])->render(), 'msg_type' => 'success', 'transaction_status' => $data->transaction_status]);
		} else {
			return \Response::json(['view' => 'No data found', 'msg_type' => 'error1']);
		}
	}
	public function show(Request $request)
	{
		$input['transaction_status'] = $request->transaction_status == 1 ? '0' : '1';
		$data = SavingAccount::where('is_deleted', '0')->where('account_no', $request->account_number)->update($input);
		if ($input) {

			return back()->with('success', 'Account Status Changed Successfully!');
		} else {
			return back()->with('error', 'Account Status Not Changed !');
		}
	}
	public function listing(Request $request)
	{
		if ($request->ajax()) {
			$data = SavingAccount::with('savingBranch', 'customerSSB', 'SavingAccountBalannce2')->where('is_deleted', '0')->where('transaction_status', '0');
			$arrFormData = array();
			$arrFormData['account_number'] = $request->account_number;
			if ($arrFormData['account_number'] != '') {
				$data = $data->where('account_no', 'like', '%' . $arrFormData['account_number'] . '%');
			}
			$data1 = $data->orderby('updated_at', 'DESC')->count('id');
			$count = $data1;
			$data = $data->orderby('updated_at', 'DESC')->offset($_POST['start'])->limit($_POST['length'])->get();
			$totalCount = $count;
			$sno = $_POST['start'];
			$rowReturn = array();

			foreach ($data as $row) {
				$current_balance = $row->SavingAccountBalannce2 ? $row->SavingAccountBalannce2->totalBalance : 0;
				$sno++;
				$val['DT_RowIndex'] = $sno;
				$val['account_no'] = $row->account_no;
				$val['branch'] = $row->savingBranch->name;
				$val['branch_code'] = $row->savingBranch->branch_code;
				$val['member_id'] = $row->customerSSB->member_id ?? '';
				$val['member_name'] = $row->customerSSB ? $row->customerSSB->first_name . ' ' . $row->customerSSB->last_name : ' ';
				$val['current_balance'] = number_format($current_balance, 2);
				$val['transaction_status'] = $row->transaction_status == 1 ? 'Actiove' : 'Inactive';
				$rowReturn[] = $val;
			}
			Cache::put('ssb_account_status_list', $data);
			Cache::put('ssb_account_status_count', $count);
			$output = array("draw" => $_POST['draw'], "recordsTotal" => $totalCount, "recordsFiltered" => $count, "data" => $rowReturn);
			return json_encode($output);
		}
	}
	public function export(Request $request)
	{
		$data = Cache::get('ssb_account_status_list');
		$count = Cache::get('ssb_account_status_count');
		$input = $request->all();
		$start = $input["start"];
		$limit = $input["limit"];
		$returnURL = URL::to('/') . "/asset/ssbinactiveaccountexport.csv";
		$fileName = env('APP_EXPORTURL') . "asset/ssbinactiveaccountexport.csv";
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
		$record = array_slice($data->toArray(), $start, $limit);
		foreach ($record as $row) {
			$CR = SavingAccountTranscation::where('saving_account_id', $row['id'])->where('is_deleted', '0')->where('payment_type', 'CR')->sum('deposit');
			$DR = SavingAccountTranscation::where('saving_account_id', $row['id'])->where('is_deleted', '0')->where('payment_type', 'DR')->sum('withdrawal');
			$current_balance = ($CR - $DR);
			$sno++;
			$val['S/N'] = $sno;
			$val['Account No'] = $row['account_no'];
			$val['Branch Name'] = $row['saving_branch']['name'];
			$val['Branch Code'] = $row['saving_branch']['branch_code'];
			$val['Member ID'] = $row['customer_s_s_b']['member_id'] ?? ' ';
			$val['Member Name'] = $row['customer_s_s_b']['first_name'] . ' ' . $row['customer_s_s_b']['last_name'];
			$val['Current balance'] = number_format($current_balance, 2);
			$val['Account Status'] = 'inactive';
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
