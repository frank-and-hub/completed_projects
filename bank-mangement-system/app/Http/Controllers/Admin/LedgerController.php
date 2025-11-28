<?php
namespace App\Http\Controllers\Admin;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use App\Models\AccountHeads;
use App\Models\Employee;
use App\Models\Vendor;
use App\Models\Member;
use App\Models\RentLiability;
use App\Models\RentPayment;
use App\Models\Memberinvestments;
use App\Models\ShareHolder;
use App\Models\LoanDayBooks;
use App\Models\AllHeadTransaction;
use App\Models\AdvancedTransaction;
use App\Models\Branch;
use App\Models\Companies;
use App\Models\EmployeeSalary;
use Yajra\DataTables\DataTables;
use Carbon\Carbon;
use App\Models\CustomerTransaction;
use Session;
use Image;
use Redirect;
use URL;
use DB;
use App\Services\Email;
use App\Services\Sms;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Cache;
class LedgerController extends Controller
{
	public function __construct()
	{
		// check user login or not
		$this->middleware('auth');
	}
	public function index()
	{
		if (check_my_permission(Auth::user()->id, "201") != "1") {
			return redirect()->route('admin.dashboard');
		}
		//$x = Employee::where('status',1)->where('is_employee','1')->get();
		$data['title'] = 'Head Ledger Report';
		$data['branch'] = Branch::select('id', 'name')->where('status', 1)->pluck('name', 'id');
		$data['heads'] = AccountHeads::select('id', 'sub_head', 'parent_id')->where('labels', 1)->where('status', 0)->get();
		//$data['subHeads'] = AccountHeads::where('labels','>',1)->where('labels','<',5)->where('status',0)->get();
		$data['employee'] = Employee::has('company')->select('id', 'employee_name')->where('status', 1)->where('is_employee', '1')->get();
		//$data['member'] = Member::where('is_block','0')->where('status',1)->limit(20)->get();
		$data['associate'] = Member::has('memberCompany')->select('id', 'first_name', 'last_name')->where('is_block', '0')->where('status', 1)->where('is_associate', '1')->limit(20)->get();
		$data['rent_owner'] = RentLiability::has('company')->select('id', 'owner_name')->where('status', 0)->get();
		$data['director'] = ShareHolder::has('company')->select('id', 'name')->where('type', 19)->get();
		$data['share_holder'] = ShareHolder::has('company')->select('id', 'name')->where('type', 15)->get();
		$data['vendors'] = Vendor::has('company')->select('id', 'name')->where('type', '0')->get();
		$data['customers'] = Vendor::has('company')->select('id', 'name')->where('type', '1')->get();
		return view('templates.admin.ledger_listing.index', $data);
	}
	public function ledgerListing(Request $request)
	{
		$companydetails = Companies::pluck('name', 'id');
		$arrFormData = array();
		if (!empty($_POST['searchform'])) {
			foreach ($_POST['searchform'] as $frm_data) {
				$arrFormData[$frm_data['name']] = $frm_data['value'];
			}
		}
		$paymentModes = [
			0 => 'Cash',
			1 => 'Cheque',
			2 => 'Online Transfer',
			3 => 'SSB/GV Transfer',
			4 => 'Auto Transfer(ECS)',
			5 => 'By loan amount',
			6 => 'JV Module',
			7 => 'Credit Card',
			8 => 'Debit Card',
		];
		$token = session()->get('_token');
		$company_id = $arrFormData['company_id'];
		if ($arrFormData['start_date'] != '' && $arrFormData['end_date'] != '') {
			// code is commented and modify by sourab on 16-01-24
		/*
			if (isset($arrFormData['ledger_type']) && ($arrFormData['ledger_type'] > 0)) {
			} else {
				$sub_head_idDeposit = 0;
				if ($arrFormData['sub_head_id1'] != '') {
					$sub_head_idDeposit = $arrFormData['sub_head_id1'];
				}
				if ($arrFormData['sub_head_id2'] != '') {
					$sub_head_idDeposit = $arrFormData['sub_head_id2'];
				}
				if ($arrFormData['sub_head_id3'] != '') {
					$sub_head_idDeposit = $arrFormData['sub_head_id3'];
				}
				if ($arrFormData['sub_head_id4'] != '') {
					$sub_head_idDeposit = $arrFormData['sub_head_id4'];
				}

				if (in_array($arrFormData['sub_head_id3'], [56, 87]) && isset($arrFormData['account_number']) && ($arrFormData['account_number'] > 0)) {
					$sub_head_id = $arrFormData['sub_head_id3'];
					if ($sub_head_idDeposit == 87) {
						$account = \App\Models\SavingAccount::where('member_id', $arrFormData['account_number'])->first();
						$data = \App\Models\SavingAccountTranscation::where('account_no', $account->account_no)->where('type', 3);
					} else {
						$data = \App\Models\SavingAccountTranscation::select("saving_account_transctions.*", "saving_account_transctions.deposit as amount");
					}
					$data = $this->applyDateFilters($data, $arrFormData);
					$data = $this->applyBranchFilters($data, $arrFormData);
					$accounthead = getAccountHeadsDetails($sub_head_id, $company_id);
					Cache::put('account_Heads_' . $token, $accounthead);
					if ($sub_head_idDeposit != 87) {
						if (isset($arrFormData['account_number']) && ($arrFormData['account_number'] > 0)) {
							$data = $data->where("saving_account_id", $arrFormData['account_number']);
						}
					}
					$data1 = $data;
					$dataCR = $data;
					$count = $data1->count();
					$export = $data;
					$export = $export->orderby('created_at_default', 'asc')->get();
					Cache::put('data_head_ledger_listing' . $token, $export);
					$data = $data->orderby('created_at_default', 'asc')->offset($_POST['start'])->limit($_POST['length'])->get();
					// $totalCount = \App\Models\SavingAccountTranscation::count();
					$totalCount = $count;
					Cache::put('data_head_ledger_listing_count_' . $token, $totalCount);
					// Now For totals
					$totalAmountssssss = $this->condicationCheck($arrFormData, $dataCR, $sub_head_idDeposit, $company_id);
					Cache::put('totalAmountssssss_' . $token, $totalAmountssssss);
					$sno = $_POST['start'];
					$rowReturn = array();
					foreach ($data as $row) {
						$sno++;
						$val['DT_RowIndex'] = $sno;
						$val['company'] = $companydetails[$row->company_id];
						$val['id'] = $row->id;
						$val['description'] = str_replace('"}', "", str_replace('{"name":"', "", $row->description));
						$val['created_date'] = date("d/m/Y", strtotime(convertDate($row->created_at)));
						$val['branch_name'] = getBranchDetail($companydetails[$row->branch_id])->name;
						$val['head_name'] = getAcountHead(56);
						$val['payment_mode'] = $paymentModes[$row->payment_mode];
						$debit = 0;
						$val['debit'] = 0;
						if ($row->payment_type == 'CR') {
							$credit = $row->deposit;
							$val['credit'] = number_format((float) $row->deposit, 2, '.', '');
						} else {
							$credit = 0;
							$val['credit'] = 0;
						}
						if ($row->payment_type == 'DR') {
							$debit = $row->withdrawal;
							$val['debit'] = number_format((float) $row->withdrawal, 2, '.', '');
						} else {
							$debit = 0;
							$val['debit'] = 0;
						}
						$total = ($accounthead->cr_nature == 1) ? ((float) $credit - (float) $debit) : ((float) $debit - (float) $credit);
						$totalAmountssssss += $total;
						$val['balance'] = number_format((float) $totalAmountssssss, 2, '.', '');
						$rowReturn[] = $val;
					}
				} else if (in_array($sub_head_idDeposit, [58, 59, 77, 78, 79, 36]) && isset($arrFormData['account_number']) && ($arrFormData['account_number'] > 0)) {
					$sub_head_id = $sub_head_idDeposit;
					if ($sub_head_idDeposit == 36) {
						$data = \App\Models\Daybook::where('investment_id', '=', $arrFormData['account_number'])->select("day_books.*", "day_books.deposit as amount")->whereIn('transaction_type', [16]);
					} else {
						$data = \App\Models\Daybook::where('investment_id', '=', $arrFormData['account_number'])->select("day_books.*", "day_books.deposit as amount")->whereIn('transaction_type', [2, 4, 15, 16, 17]);
					}
					$data = $this->applyDateFilters($data, $arrFormData);
					$data = $this->applyBranchFilters($data, $arrFormData);
					$accounthead = getAccountHeadsDetails($sub_head_idDeposit, $company_id);
					Cache::put('account_Heads_' . $token, $accounthead);
					$data1 = $data;
					$dataCR = $data;
					$count = $data1->count();
					$export = $data;
					$export = $export->orderby('created_at_default', 'asc')->get();
					Cache::put('data_head_ledger_listing' . $token, $export);
					$data = $data->orderby('created_at_default', 'asc')->offset($_POST['start'])->limit($_POST['length'])->get();
					// $totalCount = AllHeadTransaction::count();
					$totalCount = $count;
					Cache::put('data_head_ledger_listing_count_' . $token, $totalCount);
					// Now For totals
					$totalAmountssssss = $this->condicationCheck($arrFormData, $dataCR, $sub_head_id, $company_id);
					Cache::put('totalAmountssssss_' . $token, $totalAmountssssss);
					$sno = $_POST['start'];
					$rowReturn = array();
					foreach ($data as $row) {
						$sno++;
						$val['DT_RowIndex'] = $sno;
						$val['company'] = $companydetails[$row->company_id];
						$val['id'] = $row->id;
						$val['description'] = str_replace('"}', "", str_replace('{"name":"', "", $row->description));
						$val['created_date'] = date("d/m/Y", strtotime(convertDate($row->created_at)));
						$val['branch_name'] = getBranchDetail($row->branch_id);
						$val['head_name'] = getAcountHead($sub_head_id);
						$val['payment_mode'] = $paymentModes[$row->payment_mode];
						$debit = 0;
						$val['debit'] = 0;
						if ($row->payment_type == 'CR') {
							$credit = $row->amount;
							$val['credit'] = number_format((float) $row->amount, 2, '.', '');
						} else {
							$credit = 0;
							$val['credit'] = 0;
						}
						if ($row->payment_type == 'DR') {
							$debit = $row->withdrawal;
							$val['debit'] = number_format((float) $row->withdrawal, 2, '.', '');
						} else {
							$debit = 0;
							$val['debit'] = 0;
						}
						$total = ($accounthead->cr_nature == 1) ? ((float) $credit - (float) $debit) : ((float) $debit - (float) $credit);
						$totalAmountssssss += $total;
						$val['balance'] = number_format((float) $totalAmountssssss, 2, '.', '');
						$rowReturn[] = $val;
					}
				} else if (in_array($sub_head_idDeposit, [31, 64, 65, 66, 67]) && isset($arrFormData['account_number']) && ($arrFormData['account_number'] > 0)) {
					$sub_head_id = $sub_head_idDeposit;
					if ($arrFormData['l_type'] != 3) {
						$data = LoanDayBooks::where('loan_type', $arrFormData['l_type'])->where('loan_id', $arrFormData['account_number']);
					} else {
						$data = LoanDayBooks::where('loan_type', $arrFormData['l_type'])->where('loan_id', $arrFormData['account_number'])->orWhere('group_loan_id', $arrFormData['account_number']);
					}
					$data = $this->applyDateFilters($data, $arrFormData);
					if (isset($arrFormData['branch_id']) && $arrFormData['branch_id'] != '') {
						$id = $arrFormData['branch_id'];
						$data->where('branch_id', $id);
					}
					$accounthead = getAccountHeadsDetails($sub_head_idDeposit, $company_id);
					Cache::put('account_Heads_' . $token, $accounthead);
					$data1 = $data;
					$dataCR = $data;
					$count = $data1->count();
					$export = $data;
					$export = $export->orderby('created_at', 'asc')->get();
					Cache::put('data_head_ledger_listing' . $token, $export);
					$data = $data->orderby('created_at', 'asc')->offset($_POST['start'])->limit($_POST['length'])->get();
					if ($sub_head_idDeposit == 64 || $sub_head_idDeposit == 65 || $sub_head_idDeposit == 66 || $sub_head_idDeposit == 67) {
						$r = AllHeadTransaction::where('head_id', $sub_head_idDeposit)->where('type', 5)->whereIn('sub_type', [51, 56])->where('type_id', $arrFormData['account_number'])->orderby('created_at', 'asc')->get();
						$data = $r->merge($data);
					}
					// $totalCount = AllHeadTransaction::count();
					$totalCount = $count;
					Cache::put('data_head_ledger_listing_count_' . $token, $totalCount);
					// Now For totals
					$totalAmountssssss = $this->condicationCheck($arrFormData, $dataCR, $sub_head_id, $company_id);
					Cache::put('totalAmountssssss_' . $token, $totalAmountssssss);
					$sno = $_POST['start'];
					$rowReturn = array();
					foreach ($data as $row) {
						$sno++;
						$val['DT_RowIndex'] = $sno;
						$val['company'] = $companydetails[$row->company_id];
						$val['id'] = $row->id;
						$val['description'] = str_replace('"}', "", str_replace('{"name":"', "", $row->description));
						$val['created_date'] = date("d/m/Y", strtotime(convertDate($row->created_at)));
						$val['branch_name'] = getBranchDetail($row->branch_id);
						$val['head_name'] = getAcountHead($sub_head_id);
						$val['payment_mode'] = $paymentModes[$row->payment_mode];
						$debit = 0;
						$val['debit'] = 0;
						if ($row->payment_type == 'CR') {
							if ($sub_head_idDeposit == 31) {
								$credit = $row->roi_amount;
								$val['credit'] = number_format((float) $row->roi_amount, 2, '.', '');
							} elseif (($sub_head_idDeposit == 64 || $sub_head_idDeposit == 65 || $sub_head_idDeposit == 66 || $sub_head_idDeposit == 67) && $row->type == 5) {
								$credit = $row->amount;
								$val['credit'] = number_format((float) $row->amount, 2, '.', '');
							} else {
								$credit = $row->deposit;
								$val['credit'] = number_format((float) $row->deposit, 2, '.', '');
							}
						} else {
							$credit = 0;
							$val['credit'] = 0;
						}
						if ($row->payment_type == 'DR') {
							if ($sub_head_idDeposit == 31) {
								$debit = $row->roi_amount;
								$val['debit'] = number_format((float) $row->roi_amount, 2, '.', '');
							} elseif (($sub_head_idDeposit == 64 || $sub_head_idDeposit == 65 || $sub_head_idDeposit == 66 || $sub_head_idDeposit == 67) && $row->type == 5) {
								$debit = $row->amount;
								$val['debit'] = number_format((float) $row->amount, 2, '.', '');
							} else {
								$debit = $row->deposit;
								$val['debit'] = number_format((float) $row->deposit, 2, '.', '');
							}
						} else {
							$debit = 0;
							$val['debit'] = 0;
						}
						$total = ($accounthead->cr_nature == 1) ? ((float) $credit - (float) $debit) : ((float) $debit - (float) $credit);
						$totalAmountssssss += $total;
						$val['balance'] = number_format((float) $totalAmountssssss, 2, '.', '');
						$rowReturn[] = $val;
					}
				} else if (in_array($sub_head_idDeposit, [142]) && isset($arrFormData['account_number']) && ($arrFormData['account_number'] > 0)) {
					$sub_head_id = $sub_head_idDeposit;
					$data = CustomerTransaction::where('customer_id', $arrFormData['account_number']);
					$data = $this->applyDateFilters($data, $arrFormData);
					if (isset($arrFormData['branch_id']) && $arrFormData['branch_id'] != '') {
						$id = $arrFormData['branch_id'];
						$data->where('branch_id', $id);
					}
					$accounthead = getAccountHeadsDetails($sub_head_idDeposit, $company_id);
					Cache::put('account_Heads_' . $token, $accounthead);
					$data1 = $data;
					$dataCR = $data;
					$count = $data1->count();
					$export = $data;
					$export = $export->orderby('created_at', 'asc')->get();
					Cache::put('data_head_ledger_listing' . $token, $export);
					$data = $data->orderby('created_at', 'asc')->offset($_POST['start'])->limit($_POST['length'])->get();
					// $totalCount = AllHeadTransaction::count();
					$totalCount = $count;
					Cache::put('data_head_ledger_listing_count_' . $token, $totalCount);
					// Now For totals
					$totalAmountssssss = $this->condicationCheck($arrFormData, $dataCR, $sub_head_id, $company_id);
					Cache::put('totalAmountssssss_' . $token, $totalAmountssssss);
					$sno = $_POST['start'];
					$rowReturn = array();
					foreach ($data as $row) {
						$sno++;
						$val['DT_RowIndex'] = $sno;
						$val['company'] = $companydetails[$row->company_id];
						$val['id'] = $row->id;
						$val['description'] = str_replace('"}', "", str_replace('{"name":"', "", $row->description));
						$val['created_date'] = date("d/m/Y", strtotime(convertDate($row->created_at)));
						$val['branch_name'] = getBranchDetail($row->branch_id);
						$val['head_name'] = getAcountHead($sub_head_id);
						$val['payment_mode'] = $paymentModes[$row->payment_mode];
						$debit = 0;
						$val['debit'] = 0;
						if ($row->payment_type == 'CR') {
							if ($sub_head_idDeposit == 31) {
								$credit = $row->roi_amount;
								$val['credit'] = number_format((float) $row->roi_amount, 2, '.', '');
							} else {
								$credit = $row->deposit;
								$val['credit'] = number_format((float) $row->deposit, 2, '.', '');
							}
						} else {
							$credit = 0;
							$val['credit'] = 0;
						}
						if ($row->payment_type == 'DR') {
							if ($sub_head_idDeposit == 31) {
								$debit = $row->roi_amount;
								$val['debit'] = number_format((float) $row->roi_amount, 2, '.', '');
							} else {
								$debit = $row->deposit;
								$val['debit'] = number_format((float) $row->deposit, 2, '.', '');
							}
						} else {
							$debit = 0;
							$val['debit'] = 0;
						}
						$total = ($accounthead->cr_nature == 1) ? ((float) $credit - (float) $debit) : ((float) $debit - (float) $credit);
						$totalAmountssssss += $total;
						$val['balance'] = number_format((float) $totalAmountssssss, 2, '.', '');
						$rowReturn[] = $val;
					}
				} else if (in_array($sub_head_idDeposit, [140, 176]) && isset($arrFormData['account_number']) && ($arrFormData['account_number'] > 0)) {
					$sub_head_id = $sub_head_idDeposit;
					$data = \App\Models\VendorTransaction::where('vendor_id', $arrFormData['account_number']);
					$data = $this->applyDateFilters($data, $arrFormData);
					if (isset($arrFormData['branch_id']) && $arrFormData['branch_id'] != '') {
						$id = $arrFormData['branch_id'];
						$data->where('branch_id', $id);
					}
					$accounthead = getAccountHeadsDetails($sub_head_idDeposit, $company_id);
					Cache::put('account_Heads_' . $token, $accounthead);
					$data1 = $data;
					$dataCR = $data;
					$count = $data1->count();
					$export = $data;
					$export = $export->orderby('created_at_deafult', 'asc')->get();
					Cache::put('data_head_ledger_listing' . $token, $export);
					$data = $data->orderby('created_at_deafult', 'asc')->offset($_POST['start'])->limit($_POST['length'])->get();
					// $totalCount = AllHeadTransaction::count();
					$totalCount = $count;
					Cache::put('data_head_ledger_listing_count_' . $token, $totalCount);
					// Now For totals
					$totalAmountssssss = $this->condicationCheck($arrFormData, $dataCR, $sub_head_id, $company_id);
					Cache::put('totalAmountssssss_' . $token, $totalAmountssssss);
					$sno = $_POST['start'];
					$rowReturn = array();
					foreach ($data as $row) {
						$sno++;
						$val['DT_RowIndex'] = $sno;
						$val['company'] = $companydetails[$row->company_id];
						$val['id'] = $row->id;
						$val['description'] = str_replace('"}', "", str_replace('{"name":"', "", $row->description));
						$val['created_date'] = date("d/m/Y", strtotime(convertDate($row->created_at)));
						$val['branch_name'] = getBranchDetail($row->branch_id);
						$val['head_name'] = getAcountHead($sub_head_id);
						$val['payment_mode'] = $paymentModes[$row->payment_mode];
						$debit = 0;
						$val['debit'] = 0;
						if ($row->payment_type == 'CR') {
							if ($sub_head_idDeposit == 31) {
								$credit = $row->roi_amount;
								$val['credit'] = number_format((float) $row->roi_amount, 2, '.', '');
							} else {
								$credit = $row->deposit;
								$val['credit'] = number_format((float) $row->deposit, 2, '.', '');
							}
						} else {
							$credit = 0;
							$val['credit'] = 0;
						}
						if ($row->payment_type == 'DR') {
							if ($sub_head_idDeposit == 31) {
								$debit = $row->roi_amount;
								$val['debit'] = number_format((float) $row->roi_amount, 2, '.', '');
							} else {
								$debit = $row->deposit;
								$val['debit'] = number_format((float) $row->deposit, 2, '.', '');
							}
						} else {
							$debit = 0;
							$val['debit'] = 0;
						}
						$total = ($accounthead->cr_nature == 1) ? ((float) $credit - (float) $debit) : ((float) $debit - (float) $credit);
						$totalAmountssssss += $total;
						$val['balance'] = number_format((float) $totalAmountssssss, 2, '.', '');
						$rowReturn[] = $val;
					}
				} else if (in_array($sub_head_idDeposit, [141]) && isset($arrFormData['account_number']) && ($arrFormData['account_number'] > 0)) {
					$sub_head_id = $sub_head_idDeposit;
					$data = \App\Models\AssociateTransaction::where('associate_id', $arrFormData['account_number']);
					$data = $this->applyDateFilters($data, $arrFormData);
					if (isset($arrFormData['branch_id']) && $arrFormData['branch_id'] != '') {
						$id = $arrFormData['branch_id'];
						$data->where('branch_id', $id);
					}
					$accounthead = getAccountHeadsDetails($sub_head_idDeposit, $company_id);
					Cache::put('account_Heads_' . $token, $accounthead);
					$data1 = $data;
					$dataCR = $data;
					$count = $data1->count();
					$export = $data;
					$export = $export->orderby('created_at', 'asc')->get();
					Cache::put('data_head_ledger_listing' . $token, $export);
					$data = $data->orderby('created_at', 'asc')->offset($_POST['start'])->limit($_POST['length'])->get();
					// $totalCount = AllHeadTransaction::count();
					$totalCount = $count;
					Cache::put('data_head_ledger_listing_count_' . $token, $totalCount);
					// Now For totals
					$totalAmountssssss = $this->condicationCheck($arrFormData, $dataCR, $sub_head_id, $company_id);
					Cache::put('totalAmountssssss_' . $token, $totalAmountssssss);
					$sno = $_POST['start'];
					$rowReturn = array();
					foreach ($data as $row) {
						$sno++;
						$val['DT_RowIndex'] = $sno;
						$val['company'] = $companydetails[$row->company_id];
						$val['id'] = $row->id;
						$val['description'] = str_replace('"}', "", str_replace('{"name":"', "", $row->description));
						$val['created_date'] = date("d/m/Y", strtotime(convertDate($row->created_at)));
						$val['branch_name'] = getBranchDetail($row->branch_id);
						$val['head_name'] = getAcountHead($sub_head_id);
						$val['payment_mode'] = $paymentModes[$row->payment_mode];
						$debit = 0;
						$val['debit'] = 0;
						if ($row->payment_type == 'CR') {
							if ($sub_head_idDeposit == 31) {
								$credit = $row->roi_amount;
								$val['credit'] = number_format((float) $row->roi_amount, 2, '.', '');
							} else {
								$credit = $row->deposit;
								$val['credit'] = number_format((float) $row->deposit, 2, '.', '');
							}
						} else {
							$credit = 0;
							$val['credit'] = 0;
						}
						if ($row->payment_type == 'DR') {
							if ($sub_head_idDeposit == 31) {
								$debit = $row->roi_amount;
								$val['debit'] = number_format((float) $row->roi_amount, 2, '.', '');
							} else {
								$debit = $row->deposit;
								$val['debit'] = number_format((float) $row->deposit, 2, '.', '');
							}
						} else {
							$debit = 0;
							$val['debit'] = 0;
						}
						$total = ($accounthead->cr_nature == 1) ? ((float) $credit - (float) $debit) : ((float) $debit - (float) $credit);
						$totalAmountssssss += $total;
						$val['balance'] = number_format((float) $totalAmountssssss, 2, '.', '');
						$rowReturn[] = $val;
					}
				} else if (in_array($sub_head_idDeposit, [61, 60]) && isset($arrFormData['account_number']) && ($arrFormData['account_number'] > 0)) {
					$sub_head_id = $sub_head_idDeposit;
					if ($sub_head_idDeposit == 60) {
						$data = \App\Models\RentLiabilityLedger::has('company')->where('rent_liability_id', $arrFormData['account_number']);
					} else {
						$data = \App\Models\EmployeeLedger::has('company')->where('employee_id', $arrFormData['account_number']);
					}
					$data = $this->applyDateFilters($data, $arrFormData);
					if (isset($arrFormData['branch_id']) && $arrFormData['branch_id'] != '') {
						$id = $arrFormData['branch_id'];
						$data->where('branch_id', $id);
					}
					$data1 = $data;
					$dataCR = $data;
					$count = $data1->count();
					$export = $data;
					$export = $export->orderby('created_at', 'asc')->get();
					Cache::put('data_head_ledger_listing' . $token, $export);
					$data = $data->orderby('created_at', 'asc')->offset($_POST['start'])->limit($_POST['length'])->get();
					// $totalCount = \App\Models\EmployeeLedger::where('employee_id', $arrFormData['account_number'])->count();
					$totalCount = $count;
					Cache::put('data_head_ledger_listing_count_' . $token, $totalCount);
					// Now For totals
					$totalAmountssssss = 0;
					if (isset($arrFormData['head_id']) && $arrFormData['head_id'] != "") {
						if ($_POST['pages'] == 1) {
							$totalAmount = 0;
						} else {
							$totalAmount = $_POST['total'];
						}
						if ($_POST['pages'] == "1") {
							$length = ($_POST['pages']) * $_POST['length'];
						} else {
							$length = ($_POST['pages'] - 1) * $_POST['length'];
						}
						$dataCR = $dataCR->offset(0)->limit($length)->get();
						$sub_head_id1 = "";
						if ($arrFormData['sub_head_id1'] != '') {
							$sub_head_id1 = $arrFormData['sub_head_id1'];
						}
						if ($arrFormData['sub_head_id2'] != '') {
							$sub_head_id1 = $arrFormData['sub_head_id2'];
						}
						if ($arrFormData['sub_head_id3'] != '') {
							$sub_head_id1 = $arrFormData['sub_head_id3'];
						}
						if ($arrFormData['sub_head_id4'] != '') {
							$sub_head_id1 = $arrFormData['sub_head_id4'];
						}
						if ($sub_head_id1 != "") {
							$accounthead = getAccountHeadsDetails($sub_head_id, $company_id);
							Cache::put('account_Heads_' . $token, $accounthead);
						}
						$totalDR = $dataCR->where('payment_type', 'DR')->sum('withdrawal');
						$totalCR = $dataCR->where('payment_type', 'CR')->sum('deposit');
						$totalAmountssssss = $totalCR - $totalDR;
						if ($_POST['pages'] == "1") {
							$totalAmountssssss = 0;
						}
					}
					Cache::put('totalAmountssssss_' . $token, $totalAmountssssss);
					$sno = $_POST['start'];
					$rowReturn = array();
					foreach ($data as $row) {
						$sno++;
						$val['DT_RowIndex'] = $sno;
						$val['company'] = $companydetails[$row->company_id];
						$val['id'] = $row->id;
						$val['description'] = str_replace('"}', "", str_replace('{"name":"', "", $row->description));
						;
						$val['created_date'] = date("d/m/Y", strtotime(convertDate($row->created_at)));
						$val['branch_name'] = getBranchDetail($row->branch_id);
						$val['head_name'] = getAcountHead($sub_head_id);
						$val['payment_mode'] = $paymentModes[$row->payment_mode];
						$debit = 0;
						$val['debit'] = 0;
						if ($row->payment_type == 'CR') {
							$credit = $row->deposit;
							$val['credit'] = number_format((float) $row->deposit, 2, '.', '');
						} else {
							$credit = 0;
							$val['credit'] = 0;
						}
						if ($row->payment_type == 'DR') {
							$debit = $row->withdrawal;
							$val['debit'] = number_format((float) $row->withdrawal, 2, '.', '');
						} else {
							$debit = 0;
							$val['debit'] = 0;
						}
						$total = ($row->payment_type == 'CR') ? ((float) $credit - (float) $debit) : ((float) $debit - (float) $credit);
						$totalAmountssssss += $total;
						$val['balance'] = number_format((float) $totalAmountssssss, 2, '.', '');
						$rowReturn[] = $val;
					}
				} else if (in_array($sub_head_idDeposit, [72]) && isset($arrFormData['account_number']) && ($arrFormData['account_number'] > 0)) {
					$sub_head_id = $sub_head_idDeposit;
					$data = AllHeadTransaction::has('company')->where('head_id', $sub_head_idDeposit)->where('member_id', $arrFormData['account_number']);
					$data = $this->applyDateFilters($data, $arrFormData);
					if (isset($arrFormData['branch_id']) && $arrFormData['branch_id'] != '') {
						$id = $arrFormData['branch_id'];
						$data->where('branch_id', $id);
					}
					$accounthead = getAccountHeadsDetails($sub_head_idDeposit, $company_id);
					Cache::put('account_Heads_' . $token, $accounthead);
					$data1 = $data;
					$dataCR = $data;
					$count = $data1->count();
					$export = $data;
					$export = $export->orderby('created_at', 'asc')->get();
					Cache::put('data_head_ledger_listing' . $token, $export);
					$data = $data->orderby('created_at', 'asc')->offset($_POST['start'])->limit($_POST['length'])->get();
					// $totalCount = AllHeadTransaction::count();
					$totalCount = $count;
					Cache::put('data_head_ledger_listing_count_' . $token, $totalCount);
					// Now For totals
					$totalAmountssssss = $this->condicationCheck($arrFormData, $dataCR, $sub_head_id, $company_id);
					Cache::put('totalAmountssssss_' . $token, $totalAmountssssss);
					$sno = $_POST['start'];
					$rowReturn = array();
					foreach ($data as $row) {
						$sno++;
						$val['DT_RowIndex'] = $sno;
						$val['company'] = $companydetails[$row->company_id];
						$val['id'] = $row->id;
						$val['description'] = str_replace('"}', "", str_replace('{"name":"', "", $row->description));
						$val['created_date'] = date("d/m/Y", strtotime(convertDate($row->created_at)));
						$val['branch_name'] = getBranchDetail($row->branch_id);
						$val['head_name'] = getAcountHead($sub_head_id);
						$val['payment_mode'] = $paymentModes[$row->payment_mode];
						$debit = 0;
						$val['debit'] = 0;
						if ($row->payment_type == 'CR') {
							$credit = $row->amount;
							$val['credit'] = number_format((float) $row->amount, 2, '.', '');
						} else {
							$credit = 0;
							$val['credit'] = 0;
						}
						if ($row->payment_type == 'DR') {
							$debit = $row->amount;
							$val['debit'] = number_format((float) $row->amount, 2, '.', '');
						} else {
							$debit = 0;
							$val['debit'] = 0;
						}
						$total = ($accounthead->cr_nature == 1) ? ((float) $credit - (float) $debit) : ((float) $debit - (float) $credit);
						$totalAmountssssss += $total;
						$val['balance'] = number_format((float) $totalAmountssssss, 2, '.', '');
						$rowReturn[] = $val;
					}
				} else if (in_array($sub_head_idDeposit, [73, 74, 179, 185, 186]) && isset($arrFormData['account_number']) && ($arrFormData['account_number'] > 0)) {
					$sub_head_id = $sub_head_idDeposit;
					$typeMapping = [
						12 => 4,
						10 => 3,
						2 => 5,
						26 => 2,
						27 => 1,
					];
					$type = $typeMapping[$arrFormData['type']] ?? null;
					$data = AdvancedTransaction::has('company')->where('type', $type)->where('type_id', $arrFormData['account_number']);
					$data = $this->applyDateFilters($data, $arrFormData);
					if (isset($arrFormData['branch_id']) && $arrFormData['branch_id'] != '') {
						$id = $arrFormData['branch_id'];
						$data->where('branch_id', $id);
					}
					$accounthead = getAccountHeadsDetails($sub_head_idDeposit, $company_id);
					Cache::put('account_Heads_' . $token, $accounthead);
					$data1 = $data;
					$dataCR = $data;
					$count = $data1->count();
					$export = $data;
					$export = $export->orderby('created_at', 'asc')->get();
					Cache::put('data_head_ledger_listing' . $token, $export);
					$data = $data->orderby('created_at', 'asc')->offset($_POST['start'])->limit($_POST['length'])->get();
					// $totalCount = AdvancedTransaction::count();
					$totalCount = $count;
					Cache::put('data_head_ledger_listing_count_' . $token, $totalCount);
					// Now For totals
					$totalAmountssssss = $this->condicationCheck($arrFormData, $dataCR, $sub_head_id, $company_id);
					Cache::put('totalAmountssssss_' . $token, $totalAmountssssss);
					$sno = $_POST['start'];
					$rowReturn = array();
					foreach ($data as $row) {
						$sno++;
						$val['DT_RowIndex'] = $sno;
						$val['company'] = $companydetails[$row->company_id];
						$val['id'] = $row->id;
						$val['description'] = str_replace('"}', "", str_replace('{"name":"', "", $row->description));
						$val['created_date'] = date("d/m/Y", strtotime(convertDate($row->created_at)));
						$val['branch_name'] = getBranchDetail($row->branch_id);
						$val['head_name'] = getAcountHead($sub_head_id);
						$val['payment_mode'] = $paymentModes[$row->payment_mode];
						$debit = 0;
						$val['debit'] = 0;
						if ($row->payment_type == 'CR') {
							$credit = $row->amount;
							$val['credit'] = number_format((float) $row->amount, 2, '.', '');
						} else {
							$credit = 0;
							$val['credit'] = 0;
						}
						if ($row->payment_type == 'DR') {
							$debit = $row->amount;
							$val['debit'] = number_format((float) $row->amount, 2, '.', '');
						} else {
							$debit = 0;
							$val['debit'] = 0;
						}
						$total = ($accounthead->cr_nature == 1) ? ((float) $credit - (float) $debit) : ((float) $debit - (float) $credit);
						$totalAmountssssss += $total;
						$val['balance'] = number_format((float) $totalAmountssssss, 2, '.', '');
						$rowReturn[] = $val;
					}
				} else {
					*/
					
					$data = AllHeadTransaction::has('company')->with('branch', 'member')->whereIsDeleted('0');
					if (isset($arrFormData['is_search']) && $arrFormData['is_search'] == 'yes') {
						if ($arrFormData['start_date'] != '') {
							$startDate = $arrFormData['start_date'];
							$endDate = $arrFormData['end_date'];
							$startDate = date("Y-m-d", strtotime(convertDate($startDate)));
							$endDate = date("Y-m-d", strtotime(convertDate($endDate)));
							$data = $data->whereBetween(\DB::raw('DATE(all_head_transaction.entry_date)'), [$startDate, $endDate]);
						}
						if (isset($arrFormData['branch_id']) && $arrFormData['branch_id'] != '') {
							$branch_id = $arrFormData['branch_id'];
							$data = $data->where('all_head_transaction.branch_id', $branch_id);
						}
						
						if (isset($arrFormData['company_id']) && $arrFormData['company_id'] != '') {
							$company_id = $arrFormData['company_id'];
							$data = $data->where('all_head_transaction.company_id', $company_id);
						}
						$sub_head_id = "";
						if ($arrFormData['sub_head_id1'] != '') {
							$sub_head_id = $arrFormData['sub_head_id1'];
						}
						if ($arrFormData['sub_head_id2'] != '') {
							$sub_head_id = $arrFormData['sub_head_id2'];
						}
						if ($arrFormData['sub_head_id3'] != '') {
							$sub_head_id = $arrFormData['sub_head_id3'];
						}
						if ($arrFormData['sub_head_id4'] != '') {
							$sub_head_id = $arrFormData['sub_head_id4'];
						}
						if ($sub_head_id != "") {
							if (($sub_head_id == 64 || $sub_head_id == 65 || $sub_head_id == 66 || $sub_head_id == 67 || $sub_head_id == 31 || $sub_head_id == 32 || $sub_head_id == 33 || $sub_head_id == 90) && ($arrFormData['account_number'] > 0)) {
								$arrsss = array(33, $sub_head_id);
								$data = $data->whereIn('all_head_transaction.head_id', $arrsss)->where('amount', '!=', 0);
							} else {
								$data = $data->where('all_head_transaction.head_id', $sub_head_id);
							}
							$accounthead = getAccountHeadsDetails($sub_head_id, $company_id);
							if($request->start=='0'){
								Cache::put('account_Heads_' . $token, $accounthead);
							}
						}
						if (isset($arrFormData['type']) && ($arrFormData['type'] > 0) && (isset($arrFormData['head_id']))) {
							if (isset($arrFormData['account_number']) && ($arrFormData['account_number'] > 0)) {
								if (isset($arrFormData['head_id']) && ($arrFormData['head_id'] > 0)) {
									if ($arrFormData['type'] == "4") {
										if ($sub_head_id == 56) {
											//$data=$data->where("saving_account_transctions.saving_account_id",$arrFormData['account_number']);
											$data = $data->leftJoin('saving_account_transctions', function ($join) {
												$join->on('saving_account_transctions.id', '=', 'all_head_transaction.type_transaction_id');
											})->where("saving_account_transctions.saving_account_id", $arrFormData['account_number']);
										}
									} else if ($arrFormData['type'] == "3") {
										if ($sub_head_id == 77 || $sub_head_id == 78 || $sub_head_id == 79 || $sub_head_id == 80 || $sub_head_id == 81 || $sub_head_id == 82 || $sub_head_id == 83 || $sub_head_id == 84 || $sub_head_id == 85 || $sub_head_id == 58 || $sub_head_id == 139) {
											if (isset($arrFormData['account_number']) && ($arrFormData['account_number'] > 0)) {
												$account_number = Memberinvestments::where('id', $arrFormData['account_number'])->first();
												if (isset($account_number->account_number)) {
													$data = $data->where("all_head_transaction.type", $arrFormData['type'])->where("all_head_transaction.type_id", $arrFormData['account_number'])->orWhere('all_head_transaction.description', 'LIKE', '%' . $account_number->account_number . '%');
												} else {
													$data = $data->where("all_head_transaction.type", $arrFormData['type'])->where("all_head_transaction.type_id", $arrFormData['account_number']);
												}
											} else {
												$data = $data->where("all_head_transaction.type", $arrFormData['type'])->where("all_head_transaction.type_id", $arrFormData['account_number']);
											}
										} elseif ($sub_head_id == 62) {
											$record = \App\Models\DemandAdvice::whereIn('payment_type', [1, 2, 4])->where('investment_id', $arrFormData['account_number'])->first();
											$data = $data->where("all_head_transaction.type_id", $record->id);
										} else if ($sub_head_id == 36) {
											$MenberInvestment = Memberinvestments::where("id", $arrFormData['account_number'])->first();
											if (isset($MenberInvestment->member_id)) {
												$data = $data->where("all_head_transaction.member_id", $MenberInvestment->member_id);
											} else {
												$data = $data->where("all_head_transaction.member_id", $arrFormData['account_number']);
											}
										} else {
											$data = $data->where("all_head_transaction.type", $arrFormData['type'])->where("all_head_transaction.type_id", $arrFormData['account_number']);
										}
									} else if ($arrFormData['type'] == "10") {
										if ($sub_head_id == 60 || $sub_head_id == 93 || $sub_head_id == 94 || $sub_head_id == 95) {
											$RentPayment = RentPayment::where("rent_liability_id", $arrFormData['account_number'])->pluck('id')->toArray();
											if ($RentPayment) {
												$data = $data->whereIn("all_head_transaction.type_transaction_id", $RentPayment);
											}
										}
									} else if ($arrFormData['type'] == "12") {
										if ($sub_head_id == 37) {
											$employeeSalary = EmployeeSalary::where("employee_id", $arrFormData['account_number'])->first();
											if (isset($employeeSalary->id)) {
												$data = $data->where("all_head_transaction.type", $arrFormData['type'])->where("all_head_transaction.type_transaction_id", $employeeSalary->id);
											}
										}
									} else if ($arrFormData['type'] == "2") {
										if ($sub_head_id == 63 || $sub_head_id == 87) {
											$account = getMemberSsbAccountDetail($arrFormData['account_number']);
											$data = $data->where("all_head_transaction.type", "9")->where("all_head_transaction.type_id", $account->id);
										}
										if ($sub_head_id == 88) {
											$data = $data->where("all_head_transaction.type", "4")->where("all_head_transaction.member_id", $arrFormData['account_number']);
										}
									} else if ($arrFormData['type'] == "5") {
										if ($sub_head_id == 64 || $sub_head_id == 65 || $sub_head_id == 66 || $sub_head_id == 67 || $sub_head_id == 32 || $sub_head_id == 33 || $sub_head_id == 90) {
											$data = $data->where("all_head_transaction.type", $arrFormData['type'])->where("all_head_transaction.type_id", $arrFormData['account_number']);
										} elseif ($sub_head_id == 31) {
											$data = $data->where('all_head_transaction.head_id', $sub_head_id)->where("all_head_transaction.type", $arrFormData['type'])->where("all_head_transaction.type_id", $arrFormData['account_number']);
										}
									} else {
										$data = $data->where("all_head_transaction.type", $arrFormData['type'])->where("all_head_transaction.type_id", $arrFormData['account_number']);
									}
								}
							}
						}
					}
					$dataCR = $data;
					$count = $data->count();
					$export = $data;
					$export = $export->orderby('all_head_transaction.id', 'desc')->get();
					if($request->start == '0'){
						Cache::put('data_head_ledger_listing' . $token, $export);
					}
					$data = $data->orderby('all_head_transaction.id', 'desc')->offset($_POST['start'])->limit($_POST['length'])->get();
					// $totalCount = AllHeadTransaction::count('id');
					$totalCount = $count;
					if($request->start == '0'){
						Cache::put('data_head_ledger_listing_count_' . $token, $totalCount);
					}
					// Now For totals
					$totalAmountssssss = $this->condicationCheck($arrFormData, $dataCR, $sub_head_id, $company_id);
					if($request->start == '0'){
						Cache::put('totalAmountssssss_' . $token, $totalAmountssssss);
					}
					$sno = $_POST['start'];
					$rowReturn = array();
					foreach ($data as $row) {
						$sno++;
						$val['DT_RowIndex'] = $sno;
						$val['company'] = $companydetails[$row->company_id];
						$val['id'] = $row->id;
						$val['description'] = str_replace('"}', "", str_replace('{"name":"', "", $row->description));
						$val['created_date'] = date("d/m/Y", strtotime(convertDate($row->created_at)));
						$val['branch_name'] = getBranchDetail([$row->branch_id])->name;
						$val['head_name'] = getAcountHead($row->head_id);
						$val['payment_mode'] = $paymentModes[$row->payment_mode];
						if (isset($arrFormData['head_id']) && $arrFormData['head_id'] != "") {							
							$val['debit'] = $debit = 0;
							$val['credit'] = $credit = 0;
							if ($row->payment_type == 'CR') {
								if (isset($row->deposit)) {
									$credit = $row->deposit;
									$val['credit'] = number_format((float) $row->deposit, 2, '.', '');
								} else {
									$credit = $row->amount;
									$val['credit'] = number_format((float) $row->amount, 2, '.', '');
								}
							}
							if ($row->payment_type == 'DR') {
								if (isset($row->withdrawal)) {
									$debit = $row->withdrawal;
									$val['debit'] = number_format((float) $row->withdrawal, 2, '.', '');
								} else {
									$debit = $row->amount;
									$val['debit'] = number_format((float) $row->amount, 2, '.', '');
								}
							}
							$total = ($accounthead->cr_nature == 1) ? ((float) $credit - (float) $debit) : ((float) $debit - (float) $credit);
							$totalAmountssssss += $total;
							$val['balance'] = number_format((float) $totalAmountssssss, 2, '.', '');
						} else {
							if ($row->payment_type == 'DR') {
								$val['debit'] = number_format((float) $row->amount, 2, '.', '');
								$val['credit'] = 0;
							} elseif ($row->payment_type == 'CR') {
								$val['debit'] = 0;
								$val['credit'] = number_format((float) $row->amount, 2, '.', '');
							}
							$val['balance'] = number_format((float) $row->amount, 2, '.', '');
						}
						$rowReturn[] = $val;
					}
				/*}*/
				$output = array("draw" => $_POST['draw'], "recordsTotal" => $totalCount, "recordsFiltered" => $count, "data" => $rowReturn, "total" => $totalAmountssssss);
				return json_encode($output);
			// }
		} else {
			$output = array("draw" => $_POST['draw'], "recordsTotal" => 0, "recordsFiltered" => 0, "data" => [], "total" => 0);
			return json_encode($output);
		}
		
	}



	public function applyDateFilters($data, $arrFormData)
	{
		if ($arrFormData['start_date'] != '') {
			$startDate = $arrFormData['start_date'];
			$endDate = $arrFormData['end_date'];
			$startDate = date("Y-m-d", strtotime(convertDate($startDate)));
			$endDate = date("Y-m-d", strtotime(convertDate($endDate)));
			$data = $data->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate]);
		}
		return $data;
	}
	public function applyBranchFilters($data, $arrFormData)
	{
		if (isset($arrFormData['branch_id']) && $arrFormData['branch_id'] > 0) {
			$branch_id = $arrFormData['branch_id'];
			$data = $data->where('branch_id', $branch_id);
		}
		return $data;
	}
	public function applyCompanyFilters($data, $arrFormData)
	{
		if (isset($arrFormData['company_id']) && $arrFormData['company_id'] > 0) {
			$company_id = $arrFormData['company_id'];
			$data = $data->where('company_id', $company_id);
		}
		return $data;
	}
	public function condicationCheck($arrFormData, $dataCR, $sub_head_id, $company_id)
	{
		$total = 0;
		if (isset($arrFormData['head_id']) && $arrFormData['head_id'] != "") {
			$totalAmount = ($_POST['pages'] == 1) ? 0 : $_POST['total'];
			$length = ($_POST['pages'] == '1') ? ($_POST['pages'] * $_POST['length']) : (($_POST['pages'] - 1) * $_POST['length']);
			$dataCR = $dataCR->offset(0)->limit($length)->get();
			$sub_head_id1 = '';
			for ($i = 1; $i <= 4; $i++) {
				$key = 'sub_head_id' . $i;
				if (($arrFormData[$key]) != '') {
					$sub_head_id1 = $arrFormData[$key];
					break;
				}
			}
			if ($sub_head_id1 != "") {
				$accounthead = getAccountHeadsDetails($sub_head_id, $company_id);
			}
			$totalDR = $dataCR->where('payment_type', 'DR')->sum('amount');
			$totalCR = $dataCR->where('payment_type', 'CR')->sum('amount');
			$total = ($accounthead->cr_nature == 1) ? ($totalCR - $totalDR) : ($totalDR - $totalCR);
			if ($_POST['pages'] == "1") {
				$total = 0;
			}
		}
		return $total;
	}
	public function getHeadLedgerData(Request $request)
	{
		$head_id = $request->head_id;
		$company_id = $request->company_id;
		$company = Companies::whereId($request->company_id)->whereStatus('1')->exists();
		$companyList = $company ? $company_id : 0;
		$arrayCompanyList = explode(' ', $companyList);
		$companyList = array_map(function ($value) {
			return intval($value);
		}, $arrayCompanyList);
		$accountHead = AccountHeads::select(['id', 'head_id', 'parent_id', 'sub_head', 'company_id'])->where('parent_id', $head_id)
			->where('status', 0)
			->where('sub_head', '!=', 'RESERVES')
			->when($company_id, function ($q) use ($companyList) {
				$q->getCompanyRecords("CompanyId", $companyList);
			})
			->get();
		$head_ids = array($head_id);
		$subHeadsIDS = AccountHeads::select(['id', 'head_id', 'parent_id', 'sub_head', 'company_id'])->where('head_id', $head_id)
			->where('status', 0)
			->when($company_id, function ($q) use ($companyList) {
				$q->getCompanyRecords("CompanyId", $companyList);
			})
			->pluck('parent_id')
			->toArray();
		if (count($subHeadsIDS) > 0) {
			$head_ids = array_merge($head_ids, $subHeadsIDS);
			$record = get_account_head_ids($head_ids, $subHeadsIDS, true);
		}
		echo json_encode($accountHead);
	}
	public function getHeadLedgerUsersData(Request $request)
	{
		$name = $request->name;
		$ledger_type = $request->ledger_type;
		if ($ledger_type == "1") {
			$data = Member::where('is_block', '0')->where('status', 1)->where('member_id', 'LIKE', '%' . $name . '%')->get();
		}
		if ($ledger_type == "2") {
			$data = Employee::where('status', 1)->where('is_employee', '1')->where('employee_name', 'LIKE', '%' . $name . '%')->get();
		}
		if ($ledger_type == "3") {
			$data = Member::where('is_block', '0')->where('status', 1)->where('is_associate', '1')->where('member_id', 'LIKE', '%' . $name . '%')->get();
		}
		echo json_encode($data);
	}
	public function getMembersDatas(Request $request)
	{
		$input = $request->all();
		$branchId = $request->branchId;
		$result = Member::select('id', 'member_id', 'first_name', 'last_name')->where('member_id', 'LIKE', '%' . $input['query'] . '%')->get();
		/**********************/
		$members = [];
		if (count($result) > 0) {
			foreach ($result as $member) {
				if ($member->last_name != "" && $member->last_name != null) {
					$name = $member->first_name . " " . $member->last_name;
				} else {
					$name = $member->first_name;
				}
				$members[] = array(
					"id" => $member->id,
					"text" => $member->member_id,
					"attr1" => $name
				);
			}
		}
		return response()->json($members);
		/**********************/
	}
	public function getAssociateDatas(Request $request)
	{
		$input = $request->all();
		$result = Member::select('id', 'member_id', 'first_name', 'last_name')->where('is_associate', '1')->where('first_name', 'LIKE', '%' . $input['query'] . '%')->get();
		$members = [];
		if (count($result) > 0) {
			foreach ($result as $member) {
				if ($member->last_name != "" && $member->last_name != null) {
					$name = $member->first_name . " " . $member->last_name;
				} else {
					$name = $member->first_name;
				}
				$members[] = array(
					"id" => $member->id,
					"text" => $name
				);
			}
		}
		return response()->json($members);
	}
	public function getEmployeeDatas(Request $request)
	{
		$input = $request->all();
		$result = Employee::has('company')->where('status', 1)->where('is_employee', '1')->where('employee_name', 'LIKE', '%' . $input['query'] . '%')->get();
		$members = [];
		if (count($result) > 0) {
			foreach ($result as $member) {
				$members[] = array(
					"id" => $member->id,
					"text" => $member->employee_name
				);
			}
		}
		return response()->json($members);
	}
	public function getRentOwnerDatas(Request $request)
	{
		$input = $request->all();
		$result = RentLiability::has('company')->where('status', 0)->where('owner_name', 'LIKE', '%' . $input['query'] . '%')->get();
		$members = [];
		if (count($result) > 0) {
			foreach ($result as $member) {
				$members[] = array(
					"id" => $member->id,
					"text" => $member->owner_name
				);
			}
		}
		return response()->json($members);
	}
	public function getVendorDatas(Request $request)
	{
		$input = $request->all();
		$result = Vendor::has('company')->where('type', '0')->where('name', 'LIKE', '%' . $input['query'] . '%')->get();
		$members = [];
		if (count($result) > 0) {
			foreach ($result as $member) {
				$members[] = array(
					"id" => $member->id,
					"text" => $member->name
				);
			}
		}
		return response()->json($members);
	}
	public function getCustomerDatas(Request $request)
	{
		$input = $request->all();
		$companyId = $request->companyId;
		$result = Vendor::has('company')
			->where('type', '1')
			->when($companyId > 0, function ($q) use ($companyId) {
				$q->where('company_id', $companyId);
			})
			->where('name', 'LIKE', '%' . $input['query'] . '%')
			->get();
		$members = [];
		if (count($result) > 0) {
			foreach ($result as $member) {
				$members[] = array(
					"id" => $member->id,
					"text" => $member->name
				);
			}
		}
		return response()->json($members);
	}
	public function getDirectorDatas(Request $request)
	{
		$input = $request->all();
		$result = ShareHolder::has('company')->where('type', 19)->where('name', 'LIKE', '%' . $input['query'] . '%')->get();
		$members = [];
		if (count($result) > 0) {
			foreach ($result as $member) {
				$members[] = array(
					"id" => $member->id,
					"text" => $member->name
				);
			}
		}
		return response()->json($members);
	}
	public function getShareHolderDatas(Request $request)
	{
		// dd($request->all());
		$input = $request->all();
		$result = ShareHolder::has('company')->where('type', 15)->where('name', 'LIKE', '%' . $input['query'] . '%')->get();
		$members = [];
		if (count($result) > 0) {
			foreach ($result as $member) {
				$members[] = array(
					"id" => $member->id,
					"text" => $member->name
				);
			}
		}
		return response()->json($members);
	}
	public function ledgerRecord()
	{
		$x = Employee::has('company')->where('status', 1)->where('is_employee', '1')->get();
		$data['title'] = 'Ledger Listing';
		$data['branch'] = Branch::where('status', 1)->get();
		$data['heads'] = AccountHeads::where('labels', 1)->where('status', 0)->get();
		$data['subHeads'] = AccountHeads::where('labels', '>', 1)->where('labels', '<', 5)->where('status', 0)->get();
		$data['employee'] = Employee::has('company')->where('status', 1)->where('is_employee', '1')->get();
		$data['member'] = Member::where('is_block', '0')->where('status', 1)->limit(20)->get();
		$data['associate'] = Member::where('is_block', '0')->where('status', 1)->where('is_associate', '1')->limit(20)->get();
		$data['rent_owner'] = RentLiability::has('company')->where('status', 0)->get();
		$data['director'] = ShareHolder::has('company')->where('type', 19)->get();
		$data['share_holder'] = ShareHolder::has('company')->where('type', 15)->get();
		$data['vendors'] = Vendor::has('company')->where('type', '0')->get();
		$data['customers'] = Vendor::has('company')->where('type', '1')->get();
		return view('templates.admin.ledger_listing.ledger_record', $data);
	}
	
	public function head_ledger_listing_export(Request $request)
	{
		$token = session()->get('_token');
		$data = Cache::get('data_head_ledger_listing' . $token);
		$totalCount = Cache::get('data_head_ledger_listing_count_' . $token);
		$totalAmountssssss = Cache::get('totalAmountssssss_' . $token);
		// dd($totalCount,$totalAmountssssss);
		$accounthead = Cache::get('account_Heads_' . $token);
		$input = $request->all();
		$start = $input["start"];
		$limit = $input["limit"];
		$company_id = $input['company_id'];
		$companydetails = Companies::pluck('name', 'id');
		$returnURL = URL::to('/') . "/asset/" . ($input['company_id'] != 0 ? ($companydetails[$company_id]): 'all') . "_head_ledger_list.csv";
		$fileName = env('APP_EXPORTURL') . "asset/" . ($input['company_id'] != 0 ? ($companydetails[$company_id]): 'all') . "_head_ledger_list.csv";
		global $wpdb;
		$paymentModes = [
			0 => 'Cash',
			1 => 'Cheque',
			2 => 'Online Transfer',
			3 => 'SSB/GV Transfer',
			4 => 'Auto Transfer(ECS)',
			5 => 'By loan amount',
			6 => 'JV Module',
			7 => 'Credit Card',
			8 => 'Debit Card',
		];
	
		$getBranchDetail = Branch::pluck('name', 'id');
		$sno = $_POST['start'];
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
		$totalResults = $totalCount;
		$result = 'next';
		if (($start + $limit) >= $totalResults) {
			$result = 'finished';
		}
		// code is commented and modify by sourab on 16-01-24
		/*
		header("Content-type: text/csv");
		if (isset($input['ledger_type']) && ($input['ledger_type'] > 0)) {
		} else {
			// Filter According to head with account number
			$sub_head_idDeposit = 0;
			if ($input['sub_head_id1'] != '') {
				$sub_head_idDeposit = $input['sub_head_id1'];
			}
			if ($input['sub_head_id2'] != '') {
				$sub_head_idDeposit = $input['sub_head_id2'];
			}
			if ($input['sub_head_id3'] != '') {
				$sub_head_idDeposit = $input['sub_head_id3'];
			}
			if ($input['sub_head_id4'] != '') {
				$sub_head_idDeposit = $input['sub_head_id4'];
			}
			
			if (($input['sub_head_id3'] == 56 || $sub_head_idDeposit == 87) && isset($input['account_number']) && ($input['account_number'] > 0)) {
				$sub_head_id = $input['sub_head_id3'];
				foreach ($data as $row) {
					$sno++;
					$val['S/No'] = $sno;
					$val['Company'] = $companydetails[$row->company_id];
					$val['Description'] = str_replace('"}', "", str_replace('{"name":"', "", $row->description));
					$val['Created Date'] = date("d/m/Y", strtotime(convertDate($row->created_at)));
					$val['Branch Name'] = $getBranchDetail[$row->branch_id];
					$val['Head Name'] = getAcountHead(56);
					$val['Payment Mode'] = $paymentModes[$row->payment_mode];
					$debit = 0;
					$val['Debit'] = 0;
					if ($row->payment_type == 'CR') {
						$credit = $row->deposit;
						$val['Credit'] = number_format((float) $row->deposit, 2, '.', '');
					} else {
						$credit = 0;
						$val['Credit'] = 0;
					}
					if ($row->payment_type == 'DR') {
						$debit = $row->withdrawal;
						$val['Debit'] = number_format((float) $row->withdrawal, 2, '.', '');
					} else {
						$debit = 0;
						$val['Debit'] = 0;
					}
					$total = ($accounthead->cr_nature == 1) ? ((float) $credit - (float) $debit) : ((float) $debit - (float) $credit);
					$totalAmountssssss += $total;
					$val['Balance'] = number_format((float) $totalAmountssssss, 2, '.', '');
					if (!$headerDisplayed) {
						fputcsv($handle, array_keys($val));
						$headerDisplayed = true;
					}
					fputcsv($handle, $val);
				}				
			} else if (in_array($sub_head_idDeposit, [58, 59, 77, 78, 79, 36]) && isset($input['account_number']) && ($input['account_number'] > 0)) {
				$sub_head_id = $sub_head_idDeposit;
				foreach ($data as $row) {
					$sno++;
					$val['S/No'] = $sno;
					$val['Company'] = $companydetails[$row->company_id];
					$val['Description'] = str_replace('"}', "", str_replace('{"name":"', "", $row->description));
					$val['Created Date'] = date("d/m/Y", strtotime(convertDate($row->created_at)));
					$val['Branch Name'] = $getBranchDetail[$row->branch_id];
					$val['Head Name'] = getAcountHead($sub_head_id);
					$val['Payment Mode'] = $paymentModes[$row->payment_mode];
					$debit = 0;
					$val['Debit'] = 0;
					if ($row->payment_type == 'CR') {
						$credit = $row->amount;
						$val['Credit'] = number_format((float) $row->amount, 2, '.', '');
					} else {
						$credit = 0;
						$val['Credit'] = 0;
					}
					if ($row->payment_type == 'DR') {
						$debit = $row->withdrawal;
						$val['Debit'] = number_format((float) $row->withdrawal, 2, '.', '');
					} else {
						$debit = 0;
						$val['Debit'] = 0;
					}
					$total = ($accounthead->cr_nature == 1) ? ((float) $credit - (float) $debit) : ((float) $debit - (float) $credit);
					$totalAmountssssss += $total;
					$val['Balance'] = number_format((float) $totalAmountssssss, 2, '.', '');
					if (!$headerDisplayed) {
						fputcsv($handle, array_keys($val));
						$headerDisplayed = true;
					}
					fputcsv($handle, $val);
				}				
			} else if (in_array($sub_head_idDeposit, [31, 64, 65, 66, 67]) && isset($input['account_number']) && ($input['account_number'] > 0)) {
				$sub_head_id = $sub_head_idDeposit;
				foreach ($data as $row) {
					$sno++;
					$val['S/No'] = $sno;
					$val['Company'] = $companydetails[$row->company_id];
					$val['Description'] = str_replace('"}', "", str_replace('{"name":"', "", $row->description));
					$val['Created Date'] = date("d/m/Y", strtotime(convertDate($row->created_at)));
					$val['Branch Name'] = $getBranchDetail[$row->branch_id];
					$val['Head Name'] = getAcountHead($sub_head_id);
					$val['Payment Mode'] = $paymentModes[$row->payment_mode];
					$debit = 0;
					$val['Debit'] = 0;
					if ($row->payment_type == 'CR') {
						if ($sub_head_idDeposit == 31) {
							$credit = $row->roi_amount;
							$val['Credit'] = number_format((float) $row->roi_amount, 2, '.', '');
						} elseif (($sub_head_idDeposit == 64 || $sub_head_idDeposit == 65 || $sub_head_idDeposit == 66 || $sub_head_idDeposit == 67) && $row->type == 5) {
							$credit = $row->amount;
							$val['Credit'] = number_format((float) $row->amount, 2, '.', '');
						} else {
							$credit = $row->deposit;
							$val['Credit'] = number_format((float) $row->deposit, 2, '.', '');
						}
					} else {
						$credit = 0;
						$val['Credit'] = 0;
					}
					if ($row->payment_type == 'DR') {
						if ($sub_head_idDeposit == 31) {
							$debit = $row->roi_amount;
							$val['Debit'] = number_format((float) $row->roi_amount, 2, '.', '');
							;
						} elseif (($sub_head_idDeposit == 64 || $sub_head_idDeposit == 65 || $sub_head_idDeposit == 66 || $sub_head_idDeposit == 67) && $row->type == 5) {
							$debit = $row->amount;
							$val['Debit'] = number_format((float) $row->amount, 2, '.', '');
							;
						} else {
							$debit = $row->deposit;
							$val['Debit'] = number_format((float) $row->deposit, 2, '.', '');
							;
						}
					} else {
						$debit = 0;
						$val['Debit'] = 0;
					}
					$total = ($accounthead->cr_nature == 1) ? ((float) $credit - (float) $debit) : ((float) $debit - (float) $credit);
					$totalAmountssssss += $total;
					$val['Balance'] = number_format((float) $totalAmountssssss, 2, '.', '');
					if (!$headerDisplayed) {
						fputcsv($handle, array_keys($val));
						$headerDisplayed = true;
					}
					fputcsv($handle, $val);
				}				
			} else if (($sub_head_idDeposit == 142) && isset($input['account_number']) && ($input['account_number'] > 0)) {
				$sub_head_id = $sub_head_idDeposit;
				foreach ($data as $row) {
					$sno++;
					$val['S/No'] = $sno;
					$val['Company'] = $companydetails[$row->company_id];
					$val['Description'] = str_replace('"}', "", str_replace('{"name":"', "", $row->description));
					$val['Created Date'] = date("d/m/Y", strtotime(convertDate($row->created_at)));
					$val['Branch Name'] = $getBranchDetail[$row->branch_id];
					$val['Head Name'] = getAcountHead($sub_head_id);
					$val['Payment Mode'] = $paymentModes[$row->payment_mode];
					$debit = 0;
					$val['Debit'] = 0;
					if ($row->payment_type == 'CR') {
						if ($sub_head_idDeposit == 31) {
							$credit = $row->roi_amount;
							$val['Credit'] = number_format((float) $row->roi_amount, 2, '.', '');
						} else {
							$credit = $row->deposit;
							$val['Credit'] = number_format((float) $row->deposit, 2, '.', '');
						}
					} else {
						$credit = 0;
						$val['Credit'] = 0;
					}
					if ($row->payment_type == 'DR') {
						if ($sub_head_idDeposit == 31) {
							$debit = $row->roi_amount;
							$val['Debit'] = number_format((float) $row->roi_amount, 2, '.', '');
						} else {
							$debit = $row->deposit;
							$val['Debit'] = number_format((float) $row->deposit, 2, '.', '');
						}
					} else {
						$debit = 0;
						$val['Debit'] = 0;
					}
					$total = ($accounthead->cr_nature == 1) ? ((float) $credit - (float) $debit) : ((float) $debit - (float) $credit);
					$totalAmountssssss += $total;
					$val['Balance'] = number_format((float) $totalAmountssssss, 2, '.', '');
					if (!$headerDisplayed) {
						fputcsv($handle, array_keys($val));
						$headerDisplayed = true;
					}
					fputcsv($handle, $val);
				}				
			} else if (($sub_head_idDeposit == 140 || $sub_head_idDeposit == 176) && isset($input['account_number']) && ($input['account_number'] > 0)) {
				$sub_head_id = $sub_head_idDeposit;
				foreach ($data as $row) {
					$sno++;
					$val['S/No'] = $sno;
					$val['Company'] = $companydetails[$row->company_id];
					$val['Description'] = str_replace('"}', "", str_replace('{"name":"', "", $row->description));
					$val['Created Date'] = date("d/m/Y", strtotime(convertDate($row->created_at)));
					$val['Branch Name'] = $getBranchDetail[$row->branch_id];
					$val['Head Name'] = getAcountHead($sub_head_id);
					$val['Payment Mode'] = $paymentModes[$row->payment_mode];
					$debit = 0;
					$val['Debit'] = 0;
					if ($row->payment_type == 'CR') {
						if ($sub_head_idDeposit == 31) {
							$credit = $row->roi_amount;
							$val['Credit'] = number_format((float) $row->roi_amount, 2, '.', '');
							;
						} else {
							$credit = $row->deposit;
							$val['Credit'] = number_format((float) $row->deposit, 2, '.', '');
							;
						}
					} else {
						$credit = 0;
						$val['Credit'] = 0;
					}
					if ($row->payment_type == 'DR') {
						if ($sub_head_idDeposit == 31) {
							$debit = $row->roi_amount;
							$val['Debit'] = number_format((float) $row->roi_amount, 2, '.', '');
							;
						} else {
							$debit = $row->deposit;
							$val['Debit'] = number_format((float) $row->deposit, 2, '.', '');
							;
						}
					} else {
						$debit = 0;
						$val['Debit'] = 0;
					}
					$total = ($accounthead->cr_nature == 1) ? ((float) $credit - (float) $debit) : ((float) $debit - (float) $credit);
					$totalAmountssssss += $total;
					$val['Balance'] = number_format((float) $totalAmountssssss, 2, '.', '');
					if (!$headerDisplayed) {
						fputcsv($handle, array_keys($val));
						$headerDisplayed = true;
					}
					fputcsv($handle, $val);
				}				
			} else if (($sub_head_idDeposit == 141) && isset($input['account_number']) && ($input['account_number'] > 0)) {
				$sub_head_id = $sub_head_idDeposit;
				foreach ($data as $row) {
					$sno++;
					$val['S/No'] = $sno;
					$val['Company'] = $companydetails[$row->company_id];
					$val['Description'] = str_replace('"}', "", str_replace('{"name":"', "", $row->description));
					$val['Created Date'] = date("d/m/Y", strtotime(convertDate($row->created_at)));
					$val['Branch Name'] = $getBranchDetail[$row->branch_id];
					$val['Head Name'] = getAcountHead($sub_head_id);
					$val['Payment Mode'] = $paymentModes[$row->payment_mode];
					$debit = 0;
					$val['Debit'] = 0;
					if ($row->payment_type == 'CR') {
						if ($sub_head_idDeposit == 31) {
							$credit = $row->roi_amount;
							$val['Credit'] = number_format((float) $row->roi_amount, 2, '.', '');
							;
						} else {
							$credit = $row->deposit;
							$val['Credit'] = number_format((float) $row->deposit, 2, '.', '');
							;
						}
					} else {
						$credit = 0;
						$val['Credit'] = 0;
					}
					if ($row->payment_type == 'DR') {
						if ($sub_head_idDeposit == 31) {
							$debit = $row->roi_amount;
							$val['Debit'] = number_format((float) $row->roi_amount, 2, '.', '');
							;
						} else {
							$debit = $row->deposit;
							$val['Debit'] = number_format((float) $row->deposit, 2, '.', '');
							;
						}
					} else {
						$debit = 0;
						$val['Debit'] = 0;
					}
					$total = ($accounthead->cr_nature == 1) ? ((float) $credit - (float) $debit) : ((float) $debit - (float) $credit);
					$totalAmountssssss += $total;
					$val['Balance'] = number_format((float) $totalAmountssssss, 2, '.', '');
					if (!$headerDisplayed) {
						fputcsv($handle, array_keys($val));
						$headerDisplayed = true;
					}
					fputcsv($handle, $val);
				}				
			} else if (($sub_head_idDeposit == 61 || $sub_head_idDeposit == 60) && isset($input['account_number']) && ($input['account_number'] > 0)) {
				$sub_head_id = $sub_head_idDeposit;
				foreach ($data as $row) {
					$sno++;
					$val['S/No'] = $sno;
					$val['Company'] = $companydetails[$row->company_id];
					$val['Description'] = str_replace('"}', "", str_replace('{"name":"', "", $row->description));
					$val['Created Date'] = date("d/m/Y", strtotime(convertDate($row->created_at)));
					$val['Branch Name'] = $getBranchDetail[$row->branch_id];
					$val['Head Name'] = getAcountHead($sub_head_id);
					$val['Payment Mode'] = $paymentModes[$row->payment_mode];
					$debit = 0;
					$val['Debit'] = 0;
					if ($row->payment_type == 'CR') {
						$credit = $row->deposit;
						$val['Credit'] = number_format((float) $row->deposit, 2, '.', '');
					} else {
						$credit = 0;
						$val['Credit'] = 0;
					}
					if ($row->payment_type == 'DR') {
						$debit = $row->withdrawal;
						$val['Debit'] = number_format((float) $row->withdrawal, 2, '.', '');
					} else {
						$debit = 0;
						$val['Debit'] = 0;
					}
					$total = ($row->payment_type == 'CR') ? ((float) $credit - (float) $debit) : ((float) $debit - (float) $credit);
					$totalAmountssssss += $total;
					$val['Balance'] = number_format((float) $totalAmountssssss, 2, '.', '');
					if (!$headerDisplayed) {
						fputcsv($handle, array_keys($val));
						$headerDisplayed = true;
					}
					fputcsv($handle, $val);
				}				
			} else if (($sub_head_idDeposit == 72) && isset($input['account_number']) && ($input['account_number'] > 0)) {
				$sub_head_id = $sub_head_idDeposit;
				foreach ($data as $row) {
					$sno++;
					$val['S/No'] = $sno;
					$val['Company'] = $companydetails[$row->company_id];
					$val['Description'] = str_replace('"}', "", str_replace('{"name":"', "", $row->description));
					$val['Created Date'] = date("d/m/Y", strtotime(convertDate($row->created_at)));
					$val['Branch Name'] = $getBranchDetail[$row->branch_id];
					$val['Head Name'] = getAcountHead($sub_head_id);
					$val['Payment Mode'] = $paymentModes[$row->payment_mode];
					$debit = 0;
					$val['Debit'] = 0;
					if ($row->payment_type == 'CR') {
						$credit = $row->amount;
						$val['Credit'] = number_format((float) $row->amount, 2, '.', '');
					} else {
						$credit = 0;
						$val['Credit'] = 0;
					}
					if ($row->payment_type == 'DR') {
						$debit = $row->amount;
						$val['Debit'] = number_format((float) $row->amount, 2, '.', '');
					} else {
						$debit = 0;
						$val['Debit'] = 0;
					}
					$total = ($accounthead->cr_nature == 1) ? ((float) $credit - (float) $debit) : ((float) $debit - (float) $credit);
					$totalAmountssssss += $total;
					$val['Balance'] = number_format((float) $totalAmountssssss, 2, '.', '');
					if (!$headerDisplayed) {
						fputcsv($handle, array_keys($val));
						$headerDisplayed = true;
					}
					fputcsv($handle, $val);
				}				
			} else if (($sub_head_idDeposit == 73 || $sub_head_idDeposit == 74 || $sub_head_idDeposit == 179 || $sub_head_idDeposit == 185 || $sub_head_idDeposit == 186) && isset($input['account_number']) && ($input['account_number'] > 0)) {
				$sub_head_id = $sub_head_idDeposit;
				foreach ($data as $row) {
					$sno++;
					$val['S/No'] = $sno;
					$val['Company'] = $companydetails[$row->company_id];
					$val['Description'] = str_replace('"}', "", str_replace('{"name":"', "", $row->description));
					$val['Created Date'] = date("d/m/Y", strtotime(convertDate($row->created_at)));
					$val['Branch Name'] = $getBranchDetail[$row->branch_id];
					$val['Head Name'] = getAcountHead($sub_head_id);
					$val['Payment Mode'] = $paymentModes[$row->payment_mode];
					$debit = 0;
					$val['Debit'] = 0;
					if ($row->payment_type == 'CR') {
						$credit = $row->amount;
						$val['Credit'] = number_format((float) $row->amount, 2, '.', '');
					} else {
						$credit = 0;
						$val['Credit'] = 0;
					}
					if ($row->payment_type == 'DR') {
						$debit = $row->amount;
						$val['Debit'] = number_format((float) $row->amount, 2, '.', '');
					} else {
						$debit = 0;
						$val['Debit'] = 0;
					}
					$total = ($accounthead->cr_nature == 1) ? ((float) $credit - (float) $debit) : ((float) $debit - (float) $credit);
					$totalAmountssssss += $total;
					$val['Balance'] = number_format((float) $totalAmountssssss, 2, '.', '');
					if (!$headerDisplayed) {
						fputcsv($handle, array_keys($val));
						$headerDisplayed = true;
					}
					fputcsv($handle, $val);
				}				
			} else {
				*/

				foreach ($data as $row) {
					$sno++;
					// dd($row->toArray());
					$val['S/No'] = $sno;
					$val['Company'] = $companydetails[$row->company_id];
					// $val['Reference Id'] = $row->daybook_ref_id??'N/A';
					$val['Description'] = str_replace('"}', "", str_replace('{"name":"', "", $row->description));
					$val['Created Date'] = date("d/m/Y", strtotime(convertDate($row->created_at)));
					$val['Branch Name'] = $getBranchDetail[$row->branch_id];
					$val['Head Name'] = getAcountHead($row->head_id);
					$val['Payment Mode'] = $paymentModes[$row->payment_mode];
					if (isset($input['head_id']) && $input['head_id'] != "") {						
						$val['Debit'] = $debit = 0;
						$val['Credit'] = $credit = 0;
						if ($row->payment_type == 'CR') {
							if (isset($row->deposit)) {
								$credit = $row->deposit;
								$val['Credit'] = number_format((float) $row->deposit, 2, '.', '');
							} else {
								$credit = $row->amount;
								$val['Credit'] = number_format((float) $row->amount, 2, '.', '');
							}
						}
						if ($row->payment_type == 'DR') {
							if (isset($row->withdrawal)) {
								$withdrawal = $row->withdrawal;
								$debit = $withdrawal;
								$val['Debit'] = number_format((float) $row->withdrawal, 2, '.', '');
							} else {
								$debit = $row->amount;
								$val['Debit'] = number_format((float) $row->amount, 2, '.', '');
							}
						} 
						$total = ($accounthead->cr_nature == 1) ? ((float) $credit - (float) $debit) : ((float) $debit - (float) $credit);
						$totalAmountssssss += $total; 
						$val['Balance'] = number_format((float) $totalAmountssssss, 2, '.', '');
					} else {
						if ($row->payment_type == 'DR') {
							$val['Debit'] = number_format((float) $row->amount, 2, '.', '');
							$val['Credit'] = 0;
						} elseif ($row->payment_type == 'CR') {
							$val['Debit'] = 0;
							$val['Credit'] = number_format((float) $row->amount, 2, '.', '');
						}
						$val['Balance'] = number_format((float) $row->amount, 2, '.', '');
					}
					if (!$headerDisplayed) {
						fputcsv($handle, array_keys($val));
						$headerDisplayed = true;
					}
					fputcsv($handle, $val);
				}
			/*}*/
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
		// }
	}
}