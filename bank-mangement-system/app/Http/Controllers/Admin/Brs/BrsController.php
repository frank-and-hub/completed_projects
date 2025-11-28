<?php
namespace App\Http\Controllers\Admin\Brs;
use Illuminate\Http\Request;
use Auth;
use App\Models\Settings;
use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\SamraddhBankDaybook;
use App\Models\SamraddhBank;
use App\Models\AccountHeads;
use App\Models\BrsSavedData;
use Yajra\DataTables\DataTables;
use Carbon\Carbon;
use DB;
use URL;
use Session;
use App\Models\SamraddhBankAccount;
class BrsController extends Controller
{
	public function __construct()
	{
		$this->middleware('auth');
	}
	// BankAccount  Report 
	public function index()
	{
		if (check_my_permission(Auth::user()->id, "39") != "1") {
			return redirect()->route('admin.dashboard');
		}
		$data['title'] = "BRS";
		$data['banks'] = SamraddhBank::select('id', 'bank_name')->where("status", "1")->get();
		return view('templates.admin.brs.index', $data);
	}
	public function bank_charge()
	{
		if (check_my_permission(Auth::user()->id, "40") != "1") {
			return redirect()->route('admin.dashboard');
		}
		$data['title'] = "Brs | Bank Charge Management";
		$data['banks'] = SamraddhBank::select('id', 'bank_name')->where("status", "1")->get();
		return view('templates.admin.brs.bank_charge', $data);
	}
	public function get_brs_report_closing_balance()
	{
		$company_id = $_POST['company_id'];
		$bank_id = $_POST['bank_id'];
		$bank_account = $_POST['bank_account'];
		$year = $_POST['year'];
		$month = $_POST['month'];
		$date = $year . "-" . $month . "-01";
		$startDate = date('Y-m-01', strtotime($date));
		$endDate = date('Y-m-t', strtotime($date));
		$getBrsRecordSavedData = BrsSavedData::where('account_id', $bank_account)->where('bank_id', $bank_id)->where('year', $year)->where('month', $month)->orderBy('id', 'DESC')->get();
		//dd($getBrsRecordSavedData);
		if (count($getBrsRecordSavedData) > 0) {
			$openingbalance = $getBrsRecordSavedData[0]->opening_balance;
		} else {
			$openingbalance = '';
		}
		//$SamraddhBankClosingData = DB::table('samraddh_bank_closing')->where('account_id',$bank_account)->where('bank_id',$bank_id)->whereDate('entry_date', '<=', $endDate)->orderBy('entry_date','DESC')->select('*')->get();
		$SamraddhBankClosingData = \App\Models\BankBalance::whereBankId($bank_id)->whereAccountId($bank_account)->where('company_id', $company_id)->Where('entry_date', '<=', $endDate)->orderBy('entry_date', 'DESC')->get();
		$SamraddhBankClosingbalance = \App\Models\BankBalance::whereBankId($bank_id)->whereAccountId($bank_account)->where('company_id', $company_id)->Where('entry_date', '<=', $endDate)->orderBy('entry_date', 'DESC');
		if (count($SamraddhBankClosingData) > 0) {
			//dd($SamraddhBankClosingData[0]->totalAmount, $SamraddhBankClosingData[0]->entry_date);
			//$balance = $SamraddhBankClosingData[0]->totalAmount;
			$balance = $SamraddhBankClosingbalance->sum('totalAmount');
			$endDates = $SamraddhBankClosingData[0]->entry_date;
		} else {
			$balance = 0;
			$endDates = $endDate;
		}
		$openingbalance = number_format((float) $openingbalance, 2, '.', '');
		$balance = number_format((float) $balance, 2, '.', '');
		$endDates = date("d/m/Y", strtotime($endDates));
		$arrs = array("balance" => $balance, "endDates" => $endDates, "openingbalance" => $openingbalance);
		echo json_encode($arrs);
	}
	public function getBRSDATA(Request $request)
	{
		$start = 0;
		$bank_id = $_POST['bank'];
		$bank_account = $_POST['bank_account'];
		$company_id = $_POST['company_id'];
		$date = $_POST['year'] . "-" . $_POST['month'] . "-01";
		$startDate = date('Y-m-01', strtotime($date));
		$endDate = date('Y-m-t', strtotime($date));
		if ($request->session()->has('totalBalance')) {
			$request->session()->forget('totalBalance');
		}
		// Opening Balace of start
		$balancesssss = 0;
		//$SamraddhBankOpeningData = DB::table('samraddh_bank_closing')->where('entry_date',$startDate)->where('bank_id',$bank_id)->orderBy('id','DESC')->select('*')->get();
		$SamraddhBankOpeningData = \App\Models\BankBalance::where('company_id', $company_id)->whereBankId($bank_id)->Where('entry_date', $startDate)->orderBy('entry_date', 'DESC')->get();
		$SamraddhBankOpeningBalance = \App\Models\BankBalance::where('company_id', $company_id)->whereBankId($bank_id)->Where('entry_date', $startDate)->orderBy('entry_date', 'DESC');
		if (count($SamraddhBankOpeningData) > 0) {
			//$balancesssss = number_format((float)$SamraddhBankOpeningData[0]->totalAmount, 2, '.', '');
			$balancesssss = number_format((float) $SamraddhBankOpeningBalance->sum('totalAmount'), 2, '.', '');
		} else {
			//$SamraddhBankOpeningData = DB::table('samraddh_bank_closing')->where('entry_date','<',$startDate)->where('bank_id',$bank_id)->orderBy('id','DESC')->get();
			$SamraddhBankOpeningData = \App\Models\BankBalance::where('company_id', $company_id)->whereBankId($bank_id)->Where('entry_date', '<', $startDate)->orderBy('entry_date', 'DESC')->get();
			$SamraddhBankOpeningBalance = \App\Models\BankBalance::where('company_id', $company_id)->whereBankId($bank_id)->Where('entry_date', '<', $startDate)->orderBy('entry_date', 'DESC');
			if (count($SamraddhBankOpeningData) > 0) {
				$balancesssss = number_format((float) $SamraddhBankOpeningBalance->sum('totalAmount'), 2, '.', '');
			} else {
				$balancesssss = "0";
			}
		}
		// get data query
		$data = SamraddhBankDaybook::with(['Branch' => function ($query) {
			$query->select('id', 'name', 'branch_code', 'sector', 'regan', 'zone'); }])->where("is_saved", "0")->where('is_deleted', 0);
		if ($_POST['bank'] != '') {
			$data = $data->where("bank_id", $_POST['bank']);
		}
		if ($_POST['bank_account'] != '') {
			$data = $data->where("account_id", $_POST['bank_account']);
		}
		if ($_POST['year'] != '' && $_POST['month'] != '') {
			//$data=$data->whereBetween(\DB::raw('entry_date'), ["".$startDate."", "".$endDate.""]); 
			$data = $data->whereDate('entry_date', '<=', $endDate);
		}
		$data = $data->orderBy('entry_date', 'asc')->get();
		$rowReturn = array();
		foreach ($data as $row) {
			$val = array();
			$start++;
			$val["Sr_no"] = $start;
			$val['DT_RowIndex'] = $start;
			$val['id'] = $row->id;
			$val['daybook_ref_id'] = $row->daybook_ref_id;
			$val['bank_id'] = $row->bank_id;
			$val['account_id'] = $row->account_id;
			$val['amount'] = $row->amount;
			$val['type'] = $row->type;
			$val['sub_type'] = $row->sub_type;
			$val['type_id'] = $row->type_id;
			$val['type_transaction_id'] = $row->type_transaction_id;
			$val['associate_id'] = $row->associate_id;
			$val['member_id'] = $row->member_id;
			$val['branch_id'] = $row->branch_id;
			$val['opening_balance'] = $row->opening_balance;
			$val['closing_balance'] = $row->closing_balance;
			$val['description'] = $row->description;
			$val['description_dr'] = $row->description_dr;
			$val['description_cr'] = $row->description_cr;
			$val['payment_type'] = $row->payment_type;
			$val['payment_mode'] = $row->payment_mode;
			$val['currency_code'] = $row->currency_code;
			$val['amount_to_id'] = $row->amount_to_id;
			$val['amount_to_name'] = $row->amount_to_name;
			$val['amount_from_id'] = $row->amount_from_id;
			$val['amount_from_name'] = $row->amount_from_name;
			$val['v_no'] = $row->v_no;
			$val['v_date'] = $row->v_date;
			$val['ssb_account_id_from'] = $row->ssb_account_id_from;
			$val['ssb_account_id_to'] = $row->ssb_account_id_to;
			$val['cheque_no'] = $row->cheque_no;
			$val['cheque_date'] = $row->cheque_date;
			$val['cheque_bank_from'] = $row->cheque_bank_from;
			$val['cheque_bank_ac_from'] = $row->cheque_bank_ac_from;
			$val['cheque_bank_ifsc_from'] = $row->cheque_bank_ifsc_from;
			$val['cheque_bank_branch_from'] = $row->cheque_bank_branch_from;
			$val['cheque_bank_from_id'] = $row->cheque_bank_from_id;
			$val['cheque_bank_ac_from_id'] = $row->cheque_bank_ac_from_id;
			$val['cheque_bank_to'] = $row->cheque_bank_to;
			$val['cheque_bank_ac_to'] = $row->cheque_bank_ac_to;
			$val['cheque_bank_to_name'] = $row->cheque_bank_to_name;
			$val['cheque_bank_to_branch'] = $row->cheque_bank_to_branch;
			$val['cheque_bank_to_ac_no'] = $row->cheque_bank_to_ac_no;
			$val['cheque_bank_to_ifsc'] = $row->cheque_bank_to_ifsc;
			$val['transction_no'] = $row->transction_no;
			$val['transction_bank_from'] = $row->transction_bank_from;
			$val['transction_bank_ac_from'] = $row->transction_bank_ac_from;
			$val['transction_bank_ifsc_from'] = $row->transction_bank_ifsc_from;
			$val['transction_bank_branch_from'] = $row->transction_bank_branch_from;
			$val['transction_bank_from_id'] = $row->transction_bank_from_id;
			$val['transction_bank_from_ac_id'] = $row->transction_bank_from_ac_id;
			$val['transction_bank_to'] = $row->transction_bank_to;
			$val['transction_bank_ac_to'] = $row->transction_bank_ac_to;
			$val['transction_bank_to_name'] = $row->transction_bank_to_name;
			$val['transction_bank_to_ac_no'] = $row->transction_bank_to_ac_no;
			$val['transction_bank_to_branch'] = $row->transction_bank_to_branch;
			$val['transction_bank_to_ifsc'] = $row->transction_bank_to_ifsc;
			$val['transction_date'] = $row->transction_date;
			$val['is_saved'] = $row->is_saved;
			$val['entry_date'] = $row->entry_date;
			$val['entry_time'] = $row->entry_time;
			$val['created_by'] = $row->created_by;
			$val['created_by_id'] = $row->created_by_id;
			$val['created_at'] = $row->created_at;
			$val['updated_at'] = $row->updated_at;
			if ($row->Branch) {
				$val['branch_code'] = $row->Branch->branch_code;
			} else {
				$val['branch_code'] = "N/A";
			}
			if ($row->Branch) {
				$val['branch_name'] = $row->Branch->name;
			} else {
				$val['branch_name'] = "N/A";
			}
			$transaction_date = date("d/m/Y", strtotime(convertDate($row->entry_date)));
			$val['date'] = $transaction_date;
			if ($row->amount_from_id || $row->amount_to_id && $bank_id != '') {
				if ($bank_id != $row->amount_from_id) {
					$bank_name = getSamraddhBank($row->amount_to_id);
					if ($bank_name) {
						$account = SamraddhBankAccount::where('id', $bank_name->id)->first();
						$val['account_number'] = $account->account_no;
					}
				} elseif ($bank_id == $row->amount_from_id) {
					$bank_name = getSamraddhBank($row->amount_from_id);
					if ($bank_name) {
						$account = SamraddhBankAccount::where('id', $bank_name->id)->first();
						$val['account_number'] = $account->account_no;
					}
				}
			} else {
				$bank_name = getSamraddhBank($row->bank_id);
				if ($bank_name) {
					$account = SamraddhBankAccount::where('id', $bank_name->id)->first();
					$val['account_number'] = $account->account_no;
				}
			}
			$bank_name = getSamraddhBank($row->bank_id);
			if ($bank_name) {
				$account = SamraddhBankAccount::where('id', $bank_name->id)->first();
				$ac_no = $account->account_no;
			}
			if ($row->type == 7 || $row->type == 8 && $bank_id != '') {
				if ($bank_id == $row->amount_from_id) {
					$description = $row->description_dr;
					$val['particular'] = $description . ' ' . ($ac_no);
				} else if ($bank_id != $row->amount_from_id) {
					$description = $row->description_cr;
					$val['particular'] = $description . ' ' . ($ac_no);
				}
			} else if ($row->type == 2) {
				$val['particular'] = "Associate registration (RD Account)";
			} else if ($row->type == 3) {
				if ($row->sub_type == 30) {
					$val['particular'] = "R-Investment Register";
				}
				if ($row->sub_type == 31) {
					$val['particular'] = "Account opening";
				}
				if ($row->sub_type == 32) {
					$val['particular'] = "Renew";
				}
				if ($row->sub_type == 33) {
					$val['particular'] = "Passbook Print";
				}
				$val['particular'] = "Investment";
			} else if ($row->type == 4) {
				if ($row->sub_type == 41) {
					$val['particular'] = "Account opening";
				}
				if ($row->sub_type == 42) {
					$val['particular'] = "Deposit";
				}
				if ($row->sub_type == 43) {
					$val['particular'] = "Withdraw";
				}
				if ($row->sub_type == 44) {
					$val['particular'] = "Passbook Print";
				}
				if ($row->sub_type == 45) {
					$val['particular'] = "Commission";
				}
				if ($row->sub_type == 46) {
					$val['particular'] = "Fuel Charge";
				}
				$val['particular'] = "Saving Account";
			} else if ($row->type == 5) {
				if ($row->sub_type == 51 || $row->sub_type == 52 || $row->sub_type == 53 || $row->sub_type == 57) {
					$val['particular'] = "Loan";
				}
				if ($row->sub_type == 54 || $row->sub_type == 55 || $row->sub_type == 56 || $row->sub_type == 58) {
					$val['particular'] = "Group Loan";
				}
			} else if ($row->type == 10) {
				$val['particular'] = "Rent payable";
			} else if ($row->type == 12) {
				$val['particular'] = "Salary payable";
			} else {
				$val['particular'] = "N/A";
			}
			if ($row->amount_from_id && $row->amount_to_id && $bank_id) {
				if ($bank_id == $row->amount_from_id) {
					$account_head_id = SamraddhBank::where('id', $row->amount_from_id)->first('account_head_id');
					if ($account_head_id) {
						$account_head_no = AccountHeads::where('head_id', $account_head_id->account_head_id)->first(['id']);
						$val['account_head_no'] = $account_head_no->id;
					} else {
						$val['account_head_no'] = "N/A";
					}
				} elseif ($bank_id != $row->amount_from_id) {
					$account_head_id = SamraddhBank::where('id', $row->amount_to_id)->first('account_head_id');
					if ($account_head_id) {
						$account_head_no = AccountHeads::where('head_id', $account_head_id->account_head_id)->first(['id']);
						$val['account_head_no'] = $account_head_no->id;
					} else {
						$val['account_head_no'] = "N/A";
					}
				}
			} else if ($row->amount_from_id == '' && $row->amount_to_id == '' && $row->bank_id == $bank_id) {
				$account_head_id = SamraddhBank::where('id', $row->bank_id)->first('account_head_id');
				if ($account_head_id) {
					$account_head_no = AccountHeads::where('head_id', $account_head_id->account_head_id)->first(['id']);
					$val['account_head_no'] = $account_head_no->id;
				}
			} else if ($row->amount_from_id == '' && $row->amount_to_id == '' && $row->bank_id != $bank_id) {
				$account_head_id = SamraddhBank::where('id', $row->bank_id)->first('account_head_id');
				if ($account_head_id) {
					$account_head_no = AccountHeads::where('head_id', $account_head_id->account_head_id)->first(['id']);
					$val['account_head_no'] = $account_head_no->id;
				}
			} else {
				$val['account_head_no'] = '';
			}
			//account_head
			if ($row->type == 8 && $row->amount_from_id == $bank_id) {
				$account_head_id = SamraddhBank::where('id', $row->amount_from_id)->first('account_head_id');
				if ($account_head_id) {
					$account_head_no = AccountHeads::where('head_id', $account_head_id->account_head_id)->first(['sub_head']);
					$val['account_head_name'] = $account_head_no->sub_head;
				}
			}
			if ($row->type == 8 && $row->amount_from_id != $bank_id) {
				$account_head_id = SamraddhBank::where('id', $row->amount_to_id)->first('account_head_id');
				if ($account_head_id) {
					$account_head_no = AccountHeads::where('head_id', $account_head_id->account_head_id)->first(['sub_head']);
					$val['account_head_name'] = $account_head_no->sub_head;
				}
			}
			if ($row->type == 7 || $row->type == 5) {
				$account_head_id = SamraddhBank::where('id', $row->amount_to_id)->first('account_head_id');
				if ($account_head_id) {
					$account_head_no = AccountHeads::where('head_id', $account_head_id->account_head_id)->first(['sub_head']);
					$val['account_head_name'] = $account_head_no->sub_head;
				}
			}
			if ($row->amount_from_id == '' && $row->amount_to_id == '' && $row->bank_id == $bank_id) {
				$account_head_id = SamraddhBank::where('id', $row->bank_id)->first('account_head_id');
				if ($account_head_id) {
					$account_head_no = AccountHeads::where('head_id', $account_head_id->account_head_id)->first(['sub_head']);
					$val['account_head_name'] = $account_head_no->sub_head;
				}
			}
			if ($row->amount_from_id == '' && $row->amount_to_id == '' && $row->bank_id != $bank_id) {
				$account_head_id = SamraddhBank::where('id', $row->bank_id)->first('account_head_id');
				if ($account_head_id) {
					$account_head_no = AccountHeads::where('head_id', $account_head_id->account_head_id)->first(['sub_head']);
					$val['account_head_name'] = $account_head_no->sub_head;
				}
			}
			//Cheque Number
			if ($row->cheque_no) {
				$val['cheque_no'] = $row->cheque_no;
			} else {
				$val['cheque_no'] = 'N/A';
			}
			$amount = '';
			if ($row->amount_to_id && $row->amount_from_id) {
				if ($bank_id != $row->amount_from_id) {
					$amount = number_format((float) $row->amount, 2, '.', '');
					$val['credit'] = "CR-" . $amount;
				} else {
					$val['credit'] = "N/A";
				}
			} else if ($row->type == 10 && $row->bank_id == $bank_id) {
				$val['credit'] = "N/A";
			} else {
				$val['credit'] = "CR-" . number_format((float) $row->amount, 2, '.', '');
			}
			if ($row->amount_to_id && $row->amount_from_id) {
				if ($bank_id == $row->amount_from_id) {
					$val['debit'] = number_format((float) $row->amount, 2, '.', '');
				} else {
					$val['debit'] = "N/A";
				}
			} else if ($row->type == 10 && $row->bank_id == $bank_id) {
				$val['debit'] = $val['debit'] = number_format((float) $row->amount, 2, '.', '');
			} else {
				$val['debit'] = "N/A";
			}
			$totalBalance = '';
			$sessionValue = Session::get('totalBalance');
			if ($row->amount_from_id && $row->amount_to_id) {
				if ($row->amount_from_id != $bank_id) {
					if ($row) {
						$totalBalance = $row->amount + $sessionValue;
					} else {
						if ($row) {
							$totalBalance = $row->amount + $sessionValue;
						} else {
							$totalBalance = $row->amount;
						}
					}
				} elseif ($row->amount_from_id == $bank_id) {
					if ($row->amount) {
						$totalBalance = $sessionValue - $row->amount;
					} else {
						if ($row) {
							$totalBalance = $sessionValue - $row->amount;
						} else {
							$totalBalance = $row->amount;
						}
					}
				}
			} elseif (($row->type == 10) && $row->bank_id == $bank_id) {
				if ($row->amount) {
					$totalBalance = $sessionValue - $row->amount;
				} else {
					if ($row) {
						$totalBalance = $sessionValue - $row->amount;
					} else {
						$totalBalance = $row->amount;
					}
				}
			} else {
				$totalBalance = $sessionValue + $row->amount;
			}
			Session::put('totalBalance', $totalBalance);
			$val['balance'] = "&#x20B9;" . number_format((float) $totalBalance, 2, '.', '');
			//Balance
			if ($row->payment_type == "CR") {
				$val['credit'] = "CR-" . number_format((float) $row->amount, 2, '.', '');
			} else {
				$val['credit'] = "N/A";
			}
			if ($row->payment_type == "DR") {
				$val['debit'] = "DR-" . number_format((float) $row->amount, 2, '.', '');
			} else {
				$val['debit'] = "N/A";
			}
			if ($row->payment_type == "CR") {
				$balancesssss = $balancesssss + $row->amount;
			} elseif ($row->payment_type == "DR") {
				if ($balancesssss > $row->amount) {
					$balancesssss = $balancesssss - $row->amount;
				} else {
					$balancesssss = $row->amount - $balancesssss;
				}
			}
			$val['balance'] = "&#x20B9;" . number_format((float) $balancesssss, 2, '.', '');
			$rowReturn[] = $val;
		}
		$html = "";
		$html1 = "";
		if (!empty($rowReturn)) {
			for ($a = 0; $a < count($rowReturn); $a++) {
				$srN0 = $rowReturn[$a]["Sr_no"];
				$entry_date = $rowReturn[$a]["entry_date"];
				$entry_date_show = date("d/m/Y", strtotime($entry_date));
				$entry_date_edit = date("d-m-Y", strtotime($entry_date));
				$particular = $rowReturn[$a]["particular"];
				if (isset($rowReturn[$a]["account_head_name"])) {
					$account_head_name = $rowReturn[$a]["account_head_name"];
				} else {
					$account_head_name = "NA";
				}
				if ($rowReturn[$a]["payment_mode"] == 2) {
					$cheque_no = $rowReturn[$a]["transction_no"];
				} else {
					$cheque_no = $rowReturn[$a]["cheque_no"];
				}
				$amount = "&#x20B9;" . number_format((float) $rowReturn[$a]["amount"], 2, '.', '');
				$credit = $rowReturn[$a]["credit"];
				$debit = $rowReturn[$a]["debit"];
				$balance = $rowReturn[$a]["balance"];
				$branch_name = $rowReturn[$a]["branch_name"];
				$memberAccountNO = '';
				if ($rowReturn[$a]["type"] == 3 || $rowReturn[$a]["type"] == 5 || $rowReturn[$a]["type"] == 13) {
					$memberID = $rowReturn[$a]["member_id"];
					// $memberDetails = \App\Models\Member::where('id',$memberID)->first();
					// if (empty($memberDetails)) {
					$memberID = \App\Models\MemberCompany::where('id', $memberID)->value('customer_id');
					$memberDetails = \App\Models\Member::where('id', $memberID)->first();
					// }
					$firstname = "";
					$lastname = "";
					if (isset($memberDetails->first_name)) {
						$firstname = $memberDetails->first_name;
					} else {
						$firstname = " ";
					}
					if (isset($memberDetails->last_name)) {
						$lastname = $memberDetails->last_name;
					} else {
						$lastname = " ";
					}
					$memberName = $firstname . ' ' . $lastname;
					$typeID = $rowReturn[$a]["type_id"];
					//dd($memberDetails);
					if ($rowReturn[$a]["type"] == 3) {
						$memberaccountDetails = \App\Models\Memberinvestments::where('id', $typeID)->first();
						if (isset($memberaccountDetails)) {
							$memberAccountNO = $memberaccountDetails->account_number;
						} else {
							$memberAccountNO = " ";
						}
					}
					if ($rowReturn[$a]["type"] == 5) {
						if ($rowReturn[$a]["sub_type"] == 51) {
							$memberaccountDetails = \App\Models\Memberloans::where('id', $typeID)->where('loan_type', '!=', 3)->first();
							$branch_name2 = getBranchName($memberaccountDetails->branch_id);
							$branch_name = $branch_name2->name ?? $branch_name;
							if (isset($memberaccountDetails)) {
								$memberAccountNO = $memberaccountDetails->account_number;
							} else {
								$memberAccountNO = " ";
							}
						} elseif ($rowReturn[$a]["sub_type"] == 54) {
							$memberaccountDetails = \App\Models\Grouploans::where('id', $typeID)->first();
							//dd($memberaccountDetails->loanMember);
							if (isset($memberaccountDetails)) {
								if (isset($memberaccountDetails->loanMember)) {
									$memberName = $memberaccountDetails->loanMember->first_name . ' ' . $memberaccountDetails->loanMember->last_name;
								} else {
									$memberName = " ";
								}
								$memberAccountNO = $memberaccountDetails->account_number;
							} else {
								$memberAccountNO = " ";
							}
						}
					}
					if ($rowReturn[$a]["type"] == 13) {
						$memberaccountDetails = \App\Models\DemandAdvice::where('id', $typeID)->first();
						//dd($memberaccountDetails->bank_account_number);
						if (isset($memberaccountDetails)) {
							$memberAccountNO = $memberaccountDetails->account_number;
						} else {
							$memberAccountNO = " ";
						}
					}
					$particularss = $particular . ', Member Name-' . $memberName . ', A/C No.- ' . $memberAccountNO;
				} else {
					$particularss = "";
				}
				$particular = $rowReturn[$a]["description"] . ' ' . $particularss;
				//dd($particular);
				// Set balance on two digit
				//$amount = number_format((float)$amount, 2, '.', '');
				//$credit = number_format((float)$credit, 2, '.', '');
				//$debit = number_format((float)$debit, 2, '.', '');
				//$balance = number_format((float)$balance, 2, '.', '');
				if ($cheque_no == "" || $cheque_no == "N/A") {
					$cheque_no = 0;
				}
				$checkBrsRecordsCountGet = DB::table('brs_record')->where("samraddh_bank_daybook_id", $rowReturn[$a]["id"])->get();
				$checkBrsRecordsCount = count($checkBrsRecordsCountGet);
				if ($rowReturn[$a]["is_saved"] == "0") {
					if ($checkBrsRecordsCount > 0) {
						// $amount
						// $balance
						$html .= '<tr style="height:80px;" data-row-id = "' . $rowReturn[$a]["id"] . '">
									<td>' . $srN0 . '</td>
									<td>' . $entry_date_show . '</td>
									<td>' . $particular . '</td>
									<td>' . $branch_name . '</td>
									<td>' . $cheque_no . '</td>
									<td>' . $credit . '</td>
									<td>' . $debit . '</td>
								</tr>';
						$updateDate = $checkBrsRecordsCountGet[0]->change_date;
						$html1 .= '<tr style="height:80px;" data-row-id = "' . $rowReturn[$a]["id"] . '">
									<td><input type="text" name="entryDate[' . $rowReturn[$a]["id"] . '][]" value=' . $updateDate . '></td>
									<td>' . $particular . '</td>
									<td>' . $branch_name . '</td>
									<td>' . $cheque_no . '</td>
									<td>' . $credit . '</td>
									<td>' . $debit . '</td>
								</tr>';
					} else {
						$html .= '<tr class = "notSave datePikars" data-row-id = "' . $rowReturn[$a]["id"] . '" data-row-entry_date = "' . $entry_date_edit . '" data-row-particular = "' . $particular . '" data-row-account_head_name = "' . $branch_name . '" data-row-cheque_no = "' . $cheque_no . '"  data-row-credit = "' . $credit . '" data-row-debit = "' . $debit . '"  style="height:80px;">
									<td>' . $srN0 . '</td>
									<td>' . $entry_date_show . '</td>
									<td>' . $particular . '</td>
									<td>' . $branch_name . '</td>
									<td>' . $cheque_no . '</td>
									<td>' . $credit . '</td>
									<td>' . $debit . '</td>
								</tr>';
						$html1 .= '<tr data-row-id = "' . $rowReturn[$a]["id"] . '" style="height:80px;" id = "' . $rowReturn[$a]["id"] . '">
									</tr>';
					}
				} else {
					$html .= '<tr data-row-id = "' . $rowReturn[$a]["id"] . '" style="height:80px;">
								<td>' . $srN0 . '</td>
								<td>' . $entry_date_show . '</td>
								<td>' . $particular . '</td>
								<td>' . $branch_name . '</td>
								<td>' . $cheque_no . '</td>
								<td>' . $credit . '</td>
								<td>' . $debit . '</td>
							</tr>';
					$html1 .= '<tr data-row-id = "' . $rowReturn[$a]["id"] . '" style="height:80px;" id = "' . $rowReturn[$a]["id"] . '">
							</tr>';
				}
			}
		}
		$ResponseData["data"] = $html;
		$ResponseData["data1"] = $html1;
		return $ResponseData;
	}
	public function brs_reporting_listing(Request $request)
	{
		$pageStart = $_POST['start'];
		$start = $_POST['start'];
		$startAt = $_POST['draw'];
		$length = $_POST['length'];
		$search = $_POST['search']['value'];
		$from_start = $start;
		$bank_id = 1;
		$startDate = '';
		$endDate = '';
		$bankAccount_id = '';
		// if($request->session()->has('totalBalance'))
		// {
		//  $request->session()->forget('totalBalance');
		// }
		if ($request->ajax()) {
			$arrFormData = array();
			if (!empty($_POST['searchform'])) {
				foreach ($_POST['searchform'] as $frm_data) {
					$arrFormData[$frm_data['name']] = $frm_data['value'];
				}
			}
			$date = Carbon::today()->toDateString();
			$data = SamraddhBankDaybook::with(['Branch' => function ($query) {
				$query->select('id', 'name', 'branch_code', 'sector', 'regan', 'zone'); }])->where('is_deleted', 0)->offset($start)->limit($length);
			if (isset($arrFormData['is_search']) && $arrFormData['is_search'] == 'yes') {
				if ($_POST['start'] == 0) {
					if ($request->session()->has('totalBalance')) {
						$request->session()->forget('totalBalance');
					}
				}
				if ($arrFormData['bank'] != '' && $arrFormData['bank_account'] != '') {
					$bank_id = $arrFormData['bank'];
					$bankAccount_id = $arrFormData['bank_account'];
					$data = $data->where(function ($q) use ($bank_id, $bankAccount_id) {
						$q->orwhere('account_id', '=', $bankAccount_id)->orwhere('amount_from_id', '=', $bank_id)->orwhere('amount_to_id', $bank_id);
					});
				}
				if ($arrFormData['year'] != '' && $arrFormData['month'] != '') {
					$date = $arrFormData['year'] . "-" . $arrFormData['month'] . "-01";
					$startDate = date('Y-m-01', strtotime($date));
					$endDate = date('Y-m-t', strtotime($date));
					$data = $data->whereBetween(\DB::raw('entry_date'), ["" . $startDate . "", "" . $endDate . ""]);
				}
			}
			$whereCond = '((account_id = "' . $bank_id . '") )';
			$data = $data->whereRaw($whereCond);
			$data = $data->orderBy('entry_date', 'asc')->get();
			// 		
			// Now Count row start................................................................../
			$data1 = SamraddhBankDaybook::with(['Branch' => function ($query) {
				$query->select('id', 'name', 'branch_code', 'sector', 'regan', 'zone'); }])->where('is_deleted', 0);
			if (isset($arrFormData['is_search']) && $arrFormData['is_search'] == 'yes') {
				if ($arrFormData['bank'] != '' && $arrFormData['bank_account'] != '') {
					$bank_id = $arrFormData['bank'];
					$bankAccount_id = $arrFormData['bank_account'];
					$data = $data->where(function ($q) use ($bank_id, $bankAccount_id) {
						$q->orwhere('account_id', '=', $bankAccount_id)->orwhere('amount_from_id', '=', $bank_id)->orwhere('amount_to_id', $bank_id);
					});
				}
				if ($arrFormData['year'] != '' && $arrFormData['month'] != '') {
					$date = $arrFormData['year'] . "-" . $arrFormData['month'] . "-01";
					$startDate = date('Y-m-01', strtotime($date));
					$endDate = date('Y-m-t', strtotime($date));
					$data = $data->whereBetween(\DB::raw('entry_date'), ["" . $startDate . "", "" . $endDate . ""]);
				}
			}
			$whereCond = '((account_id = "' . $bank_id . '") )';
			$data1 = $data1->whereRaw($whereCond);
			$totalCount = $data1->orderBy('entry_date', 'asc')->count();
			// Now count row end .................................................................../
			$rowReturn = array();
			foreach ($data as $row) {
				$val = array();
				$start++;
				$val["Sr_no"] = $start;
				$val['DT_RowIndex'] = $start;
				$val['id'] = $row->id;
				$val['daybook_ref_id'] = $row->daybook_ref_id;
				$val['bank_id'] = $row->bank_id;
				$val['account_id'] = $row->account_id;
				$val['amount'] = $row->amount;
				$val['type'] = $row->type;
				$val['sub_type'] = $row->sub_type;
				$val['type_id'] = $row->type_id;
				$val['type_transaction_id'] = $row->type_transaction_id;
				$val['associate_id'] = $row->associate_id;
				$val['member_id'] = $row->member_id;
				$val['branch_id'] = $row->branch_id;
				$val['opening_balance'] = $row->opening_balance;
				$val['closing_balance'] = $row->closing_balance;
				$val['description'] = $row->description;
				$val['description_dr'] = $row->description_dr;
				$val['description_cr'] = $row->description_cr;
				$val['payment_type'] = $row->payment_type;
				$val['payment_mode'] = $row->payment_mode;
				$val['currency_code'] = $row->currency_code;
				$val['amount_to_id'] = $row->amount_to_id;
				$val['amount_to_name'] = $row->amount_to_name;
				$val['amount_from_id'] = $row->amount_from_id;
				$val['amount_from_name'] = $row->amount_from_name;
				$val['v_no'] = $row->v_no;
				$val['v_date'] = $row->v_date;
				$val['ssb_account_id_from'] = $row->ssb_account_id_from;
				$val['ssb_account_id_to'] = $row->ssb_account_id_to;
				$val['cheque_no'] = $row->cheque_no;
				$val['cheque_date'] = $row->cheque_date;
				$val['cheque_bank_from'] = $row->cheque_bank_from;
				$val['cheque_bank_ac_from'] = $row->cheque_bank_ac_from;
				$val['cheque_bank_ifsc_from'] = $row->cheque_bank_ifsc_from;
				$val['cheque_bank_branch_from'] = $row->cheque_bank_branch_from;
				$val['cheque_bank_from_id'] = $row->cheque_bank_from_id;
				$val['cheque_bank_ac_from_id'] = $row->cheque_bank_ac_from_id;
				$val['cheque_bank_to'] = $row->cheque_bank_to;
				$val['cheque_bank_ac_to'] = $row->cheque_bank_ac_to;
				$val['cheque_bank_to_name'] = $row->cheque_bank_to_name;
				$val['cheque_bank_to_branch'] = $row->cheque_bank_to_branch;
				$val['cheque_bank_to_ac_no'] = $row->cheque_bank_to_ac_no;
				$val['cheque_bank_to_ifsc'] = $row->cheque_bank_to_ifsc;
				$val['transction_no'] = $row->transction_no;
				$val['transction_bank_from'] = $row->transction_bank_from;
				$val['transction_bank_ac_from'] = $row->transction_bank_ac_from;
				$val['transction_bank_ifsc_from'] = $row->transction_bank_ifsc_from;
				$val['transction_bank_branch_from'] = $row->transction_bank_branch_from;
				$val['transction_bank_from_id'] = $row->transction_bank_from_id;
				$val['transction_bank_from_ac_id'] = $row->transction_bank_from_ac_id;
				$val['transction_bank_to'] = $row->transction_bank_to;
				$val['transction_bank_ac_to'] = $row->transction_bank_ac_to;
				$val['transction_bank_to_name'] = $row->transction_bank_to_name;
				$val['transction_bank_to_ac_no'] = $row->transction_bank_to_ac_no;
				$val['transction_bank_to_branch'] = $row->transction_bank_to_branch;
				$val['transction_bank_to_ifsc'] = $row->transction_bank_to_ifsc;
				$val['transction_date'] = $row->transction_date;
				if ($row->is_saved == "0") {
					$val['entry_date'] = '<span style="color:red;">' . $row->entry_date . '</span>';
				} else {
					$val['entry_date'] = $row->entry_date;
				}
				$val['entry_time'] = $row->entry_time;
				$val['created_by'] = $row->created_by;
				$val['created_by_id'] = $row->created_by_id;
				$val['created_at'] = $row->created_at;
				$val['updated_at'] = $row->updated_at;
				if ($row->Branch) {
					$val['branch_code'] = $row->Branch->branch_code;
				} else {
					$val['branch_code'] = "N/A";
				}
				if ($row->Branch) {
					$val['branch_name'] = $row->Branch->name;
				} else {
					$val['branch_name'] = "N/A";
				}
				$transaction_date = date("d/m/Y", strtotime(convertDate($row->entry_date)));
				$val['date'] = $transaction_date;
				if ($row->amount_from_id || $row->amount_to_id && $bank_id != '') {
					if ($bank_id != $row->amount_from_id) {
						$bank_name = getSamraddhBank($row->amount_to_id);
						if ($bank_name) {
							$account = SamraddhBankAccount::where('id', $bank_name->id)->first();
							$val['account_number'] = $account->account_no;
						}
					} elseif ($bank_id == $row->amount_from_id) {
						$bank_name = getSamraddhBank($row->amount_from_id);
						if ($bank_name) {
							$account = SamraddhBankAccount::where('id', $bank_name->id)->first();
							$val['account_number'] = $account->account_no;
						}
					}
				} else {
					$bank_name = getSamraddhBank($row->bank_id);
					if ($bank_name) {
						$account = SamraddhBankAccount::where('id', $bank_name->id)->first();
						$val['account_number'] = $account->account_no;
					}
				}
				$bank_name = getSamraddhBank($row->bank_id);
				if ($bank_name) {
					$account = SamraddhBankAccount::where('id', $bank_name->id)->first();
					$ac_no = $account->account_no;
				}
				if ($row->type == 7 || $row->type == 8 && $bank_id != '') {
					if ($bank_id == $row->amount_from_id) {
						$description = $row->description_dr;
						$val['particular'] = $description . ' ' . ($ac_no);
					} else if ($bank_id != $row->amount_from_id) {
						$description = $row->description_cr;
						$val['particular'] = $description . ' ' . ($ac_no);
					}
				} else if ($row->type == 2) {
					$val['particular'] = "Associate registration (RD Account)";
				} else if ($row->type == 3) {
					if ($row->sub_type == 30) {
						$val['particular'] = "R-Investment Register";
					}
					if ($row->sub_type == 31) {
						$val['particular'] = "Account opening";
					}
					if ($row->sub_type == 32) {
						$val['particular'] = "Renew";
					}
					if ($row->sub_type == 33) {
						$val['particular'] = "Passbook Print";
					}
					$val['particular'] = "Investment";
				} else if ($row->type == 4) {
					if ($row->sub_type == 41) {
						$val['particular'] = "Account opening";
					}
					if ($row->sub_type == 42) {
						$val['particular'] = "Deposit";
					}
					if ($row->sub_type == 43) {
						$val['particular'] = "Withdraw";
					}
					if ($row->sub_type == 44) {
						$val['particular'] = "Passbook Print";
					}
					if ($row->sub_type == 45) {
						$val['particular'] = "Commission";
					}
					if ($row->sub_type == 46) {
						$val['particular'] = "Fuel Charge";
					}
					$val['particular'] = "Saving Account";
				} else if ($row->type == 5) {
					if ($row->sub_type == 51 || $row->sub_type == 52 || $row->sub_type == 53 || $row->sub_type == 57) {
						$val['particular'] = "Loan";
					}
					if ($row->sub_type == 54 || $row->sub_type == 55 || $row->sub_type == 56 || $row->sub_type == 58) {
						$val['particular'] = "Group Loan";
					}
				} else if ($row->type == 10) {
					$val['particular'] = "Rent payable";
				} else if ($row->type == 12) {
					$val['particular'] = "Salary payable";
				} else {
					$val['particular'] = "N/A";
				}
				if ($row->amount_from_id && $row->amount_to_id && $bank_id) {
					if ($bank_id == $row->amount_from_id) {
						$account_head_id = SamraddhBank::where('id', $row->amount_from_id)->first('account_head_id');
						if ($account_head_id) {
							$account_head_no = AccountHeads::where('head_id', $account_head_id->account_head_id)->first(['id']);
							$val['account_head_no'] = $account_head_no->id;
						} else {
							$val['account_head_no'] = "N/A";
						}
					} elseif ($bank_id != $row->amount_from_id) {
						$account_head_id = SamraddhBank::where('id', $row->amount_to_id)->first('account_head_id');
						if ($account_head_id) {
							$account_head_no = AccountHeads::where('head_id', $account_head_id->account_head_id)->first(['id']);
							$val['account_head_no'] = $account_head_no->id;
						} else {
							$val['account_head_no'] = "N/A";
						}
					}
				} else if ($row->amount_from_id == '' && $row->amount_to_id == '' && $row->bank_id == $bank_id) {
					$account_head_id = SamraddhBank::where('id', $row->bank_id)->first('account_head_id');
					if ($account_head_id) {
						$account_head_no = AccountHeads::where('head_id', $account_head_id->account_head_id)->first(['id']);
						$val['account_head_no'] = $account_head_no->id;
					}
				} else if ($row->amount_from_id == '' && $row->amount_to_id == '' && $row->bank_id != $bank_id) {
					$account_head_id = SamraddhBank::where('id', $row->bank_id)->first('account_head_id');
					if ($account_head_id) {
						$account_head_no = AccountHeads::where('head_id', $account_head_id->account_head_id)->first(['id']);
						$val['account_head_no'] = $account_head_no->id;
					}
				} else {
					$val['account_head_no'] = '';
				}
				//account_head
				if ($row->type == 8 && $row->amount_from_id == $bank_id) {
					$account_head_id = SamraddhBank::where('id', $row->amount_from_id)->first('account_head_id');
					if ($account_head_id) {
						$account_head_no = AccountHeads::where('head_id', $account_head_id->account_head_id)->first(['sub_head']);
						$val['account_head_name'] = $account_head_no->sub_head;
					}
				}
				if ($row->type == 8 && $row->amount_from_id != $bank_id) {
					$account_head_id = SamraddhBank::where('id', $row->amount_to_id)->first('account_head_id');
					if ($account_head_id) {
						$account_head_no = AccountHeads::where('head_id', $account_head_id->account_head_id)->first(['sub_head']);
						$val['account_head_name'] = $account_head_no->sub_head;
					}
				}
				if ($row->type == 7 || $row->type == 5) {
					$account_head_id = SamraddhBank::where('id', $row->amount_to_id)->first('account_head_id');
					if ($account_head_id) {
						$account_head_no = AccountHeads::where('head_id', $account_head_id->account_head_id)->first(['sub_head']);
						$val['account_head_name'] = $account_head_no->sub_head;
					}
				}
				if ($row->amount_from_id == '' && $row->amount_to_id == '' && $row->bank_id == $bank_id) {
					$account_head_id = SamraddhBank::where('id', $row->bank_id)->first('account_head_id');
					if ($account_head_id) {
						$account_head_no = AccountHeads::where('head_id', $account_head_id->account_head_id)->first(['sub_head']);
						$val['account_head_name'] = $account_head_no->sub_head;
					}
				}
				if ($row->amount_from_id == '' && $row->amount_to_id == '' && $row->bank_id != $bank_id) {
					$account_head_id = SamraddhBank::where('id', $row->bank_id)->first('account_head_id');
					if ($account_head_id) {
						$account_head_no = AccountHeads::where('head_id', $account_head_id->account_head_id)->first(['sub_head']);
						$val['account_head_name'] = $account_head_no->sub_head;
					}
				}
				//Cheque Number
				if ($row->cheque_no) {
					$val['cheque_no'] = $row->cheque_no;
				} else {
					$val['cheque_no'] = 'N/A';
				}
				$amount = '';
				if ($row->amount_to_id && $row->amount_from_id) {
					if ($bank_id != $row->amount_from_id) {
						$amount = number_format((float) $row->amount, 2, '.', '');
						$val['credit'] = "&#x20B9;" . $amount;
					} else {
						$val['credit'] = "N/A";
					}
				} else if ($row->type == 10 && $row->bank_id == $bank_id) {
					$val['credit'] = "N/A";
				} else {
					$val['credit'] = "&#x20B9;" . number_format((float) $row->amount, 2, '.', '');
				}
				if ($row->amount_to_id && $row->amount_from_id) {
					if ($bank_id == $row->amount_from_id) {
						$val['debit'] = number_format((float) $row->amount, 2, '.', '');
					} else {
						$val['debit'] = "N/A";
					}
				} else if ($row->type == 10 && $row->bank_id == $bank_id) {
					$val['debit'] = $val['debit'] = number_format((float) $row->amount, 2, '.', '');
				} else {
					$val['debit'] = "N/A";
				}
				$totalBalance = '';
				$sessionValue = Session::get('totalBalance');
				if ($row->amount_from_id && $row->amount_to_id) {
					if ($row->amount_from_id != $bank_id) {
						if ($row) {
							$totalBalance = $row->amount + $sessionValue;
						} else {
							if ($row) {
								$totalBalance = $row->amount + $sessionValue;
							} else {
								$totalBalance = $row->amount;
							}
						}
					} elseif ($row->amount_from_id == $bank_id) {
						if ($row->amount) {
							$totalBalance = $sessionValue - $row->amount;
						} else {
							if ($row) {
								$totalBalance = $sessionValue - $row->amount;
							} else {
								$totalBalance = $row->amount;
							}
						}
					}
				} elseif (($row->type == 10) && $row->bank_id == $bank_id) {
					if ($row->amount) {
						$totalBalance = $sessionValue - $row->amount;
					} else {
						if ($row) {
							$totalBalance = $sessionValue - $row->amount;
						} else {
							$totalBalance = $row->amount;
						}
					}
				} else {
					$totalBalance = $sessionValue + $row->amount;
				}
				Session::put('totalBalance', $totalBalance);
				$val['balance'] = "&#x20B9;" . number_format((float) $totalBalance, 2, '.', '');
				$rowReturn[] = $val;
			}
			$output = array("draw" => $_POST['draw'], "recordsTotal" => $totalCount, "recordsFiltered" => $totalCount, "data" => $rowReturn, "page" => $pageStart, 'start' => $start);
			return json_encode($output);
		}
	}
	public function save_brs_report_data(Request $request)
	{
		// dd($request->all());
		$entryTime = date("H:i:s");
		// Insert Data in Brs Save Data Table with client required datagrid
		$created_by_id = Auth::user()->id;
		$data['company_id'] = $request['company_id'];
		$data['bank_id'] = $request['bank_id'];
		$data['account_id'] = $request['bank_account'];
		$data['year'] = $request['year'];
		$data['month'] = $request['month'];
		$data['opening_balance'] = $request['statementClosingBalance'];
		$data['closing_balance'] = $request['finalClosingBalance'];
		$data['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($request['created_at'])));
		$data['created_by'] = 1;
		$data['created_by_id'] = $created_by_id;
		if (isset($_POST["entryDate"])) {
			$entryDate = $_POST["entryDate"];
		} else {
			$entryDate = '';
		}
		$insert_date = date("Y-m-d");
		if (isset($entryDate) && !empty($entryDate)) {
			foreach ($entryDate as $key => $value) {
				$newDate = date("Y-m-d", strtotime($value[0]));
				//dd($newDate);
				$array = array("samraddh_bank_daybook_id" => $key, "change_date" => $newDate, "insert_date" => $insert_date);
				// Check first that data insert or not
				$checkBrsRecordsCount = DB::table('brs_record')->where("samraddh_bank_daybook_id", $key)->count();
				if ($checkBrsRecordsCount > 0) {
					DB::table('brs_record')->where("samraddh_bank_daybook_id", $key)->update($array);
				} else {
					DB::table('brs_record')->insert($array);
				}
			}
		}
		$brssave = \App\Models\BrsSavedData::create($data);
	}
	public function clear_brs_report_data()
	{
		$entryDate = $_POST["entryDate"];
		if (!empty($entryDate)) {
			foreach ($entryDate as $key => $value) {
				DB::table('brs_record')->where('samraddh_bank_daybook_id', $key)->delete();
			}
		}
	}
	public function print_brs_report_data()
	{
		if (isset($_POST["entryDate"])) {
			$entryDate = $_POST["entryDate"];
			if (!empty($entryDate)) {
				foreach ($entryDate as $key => $value) {
					$array = array("is_saved" => "1");
					DB::table('samraddh_bank_daybook')->where("id", $key)->update($array);
				}
			}
		}
	}
	public function print_brs(Request $request)
	{
		$start = 0;
		$bank_id = $_GET['bank'];
		$bankAccount_id = $_GET['bank_account'];
		$date = $_GET['year'] . "-" . $_GET['month'] . "-01";
		$startDate = date('Y-m-01', strtotime($date));
		$endDate = date('Y-m-t', strtotime($date));
		$bankAccount_id = $_GET['bank_account'];
		$closing_balance = $_GET['closing_balance'];
		$parsedDate = Carbon::parse($date)->subMonth();
		$newParsedYear = date("Y", strtotime(convertDate($parsedDate)));
		$newParsedmonth = date("m", strtotime(convertDate($parsedDate)));
		$selectedYear = $_GET['year'];
		$selectedmonth = $_GET['month'];
		$selectedMonthName = date('F', mktime(0, 0, 0, $selectedmonth, 10)); // March
		// Get Previous Year & Month record
		$prevYearMonthOpeningBankBalanceData = \App\Models\BrsSavedData::where('bank_id', $bank_id)->where('account_id', $bankAccount_id)->where('year', $newParsedYear)->where('month', $newParsedmonth)->orderby('id', 'DESC')->first();
		if ($prevYearMonthOpeningBankBalanceData != NULL) {
			$previousOpeningBalanceStatement = $prevYearMonthOpeningBankBalanceData->opening_balance;
			$previousOpeningDaybookBalance = $prevYearMonthOpeningBankBalanceData->closing_balance;
		} else {
			$previousOpeningBalanceStatement = 0;
			$previousOpeningDaybookBalance = 0;
		}
		// Get Current Year & Month record
		$CurrentYearMonthOpeningBankBalanceData = \App\Models\BrsSavedData::where('bank_id', $bank_id)->where('account_id', $bankAccount_id)->where('year', $selectedYear)->where('month', $selectedmonth)->orderby('id', 'DESC')->first();
		if ($CurrentYearMonthOpeningBankBalanceData != NULL) {
			$Currentclosingbankstatement = $CurrentYearMonthOpeningBankBalanceData->closing_balance;
		} else {
			$Currentclosingbankstatement = 0;
		}
		// Get Account Number Details
		$account_details = SamraddhBankAccount::with(['samraddhbank', 'getCompanyDetail'])->where("id", $bankAccount_id)->get();
		if ($request->session()->has('totalBalance')) {
			$request->session()->forget('totalBalance');
		}
		$data = SamraddhBankDaybook::with(['Branch' => function ($query) {
			$query->select('id', 'name', 'branch_code', 'sector', 'regan', 'zone'); }])->where("is_saved", "0")->where('is_deleted', 0);
		if ($bank_id != '') {
			$data = $data->where("bank_id", $bank_id);
		}
		if ($bankAccount_id != '') {
			$data = $data->where("account_id", $bankAccount_id);
		}
		if ($_GET['year'] != '' && $_GET['month'] != '') {
			$data = $data->whereDate('entry_date', '<=', $endDate);
		}
		$data = $data->orderBy('entry_date', 'asc')->get();
		$rowReturn = array();
		$Debited_in_Daybook_but_Not_Credited_by = array();
		$Debited_in_Daybook_but_Not_Credited_by_total = 0;
		$Credited_in_Daybook_but_Not_Debited_by = array();
		$Credited_in_Daybook_but_Not_Debited_by_total = 0;
		$Bank_chagres = array();
		$Bank_chagres_total = 0;
		$rest_all = array();
		$rest_all_total = 0;
		foreach ($data as $row) {
			$empArr = array();
			$ac_no = "";
			$bank_name = getSamraddhBank($row->bank_id);
			if ($bank_name) {
				$account = SamraddhBankAccount::where('id', $bank_name->id)->first();
				$ac_no = $account->account_no;
			}
			$particular = $row->description;
			// Now cheque that cheque clear or not
			if (($row->payment_type == "DR") && ($row->payment_mode == "1")) {
				$chequeClear = DB::table('samraddh_cheques')->where("status", "3")->where("bank_id", $row->bank_id)->where("account_id", $row->account_id)->where("cheque_no", $row->cheque_no)->count();
			} else {
				$chequeClear = 0;
			}
			if (($row->payment_type == "CR") && ($row->payment_mode == "1")) {
				$chequeClearCR = DB::table('received_cheques')->where("status", "3")->where("deposit_bank_id", $row->bank_id)->where("deposit_account_id", $row->account_id)->where("cheque_no", $row->cheque_no)->count();
			} else {
				$chequeClearCR = 0;
			}
			if ($row->type == "18") {
				$empArr['id'] = $row->id;
				$empArr['daybook_ref_id'] = $row->daybook_ref_id;
				$empArr['cheque_no'] = $row->cheque_no;
				$empArr['cheque_date'] = $row->cheque_date;
				$balance = number_format((float) $row->amount, 2, '.', '');
				$empArr['amount'] = $balance;
				$empArr['transction_no'] = $row->transction_no;
				$entry_date = date("d/m/Y", strtotime($row->entry_date));
				$empArr['entry_date'] = $entry_date;
				$empArr['particular'] = $particular;
				array_push($Bank_chagres, $empArr);
				$Bank_chagres_total = $Bank_chagres_total + $row->amount;
			} else if ($row->payment_type == "DR" && $row->payment_mode == "1" && $chequeClear > 0) {
				$empArr['id'] = $row->id;
				$empArr['daybook_ref_id'] = $row->daybook_ref_id;
				$empArr['cheque_no'] = $row->cheque_no;
				$empArr['cheque_date'] = $row->cheque_date;
				$balance = number_format((float) $row->amount, 2, '.', '');
				$empArr['amount'] = $balance;
				$empArr['transction_no'] = $row->transction_no;
				$entry_date = date("d/m/Y", strtotime($row->entry_date));
				$empArr['entry_date'] = $entry_date;
				$empArr['particular'] = $particular;
				array_push($Credited_in_Daybook_but_Not_Debited_by, $empArr);
				$Credited_in_Daybook_but_Not_Debited_by_total = $Credited_in_Daybook_but_Not_Debited_by_total + $row->amount;
			} else if ($row->payment_type == "CR" && $row->payment_mode == "1" && $chequeClearCR > 0) {
				$empArr['id'] = $row->id;
				$empArr['daybook_ref_id'] = $row->daybook_ref_id;
				$empArr['cheque_no'] = $row->cheque_no;
				$empArr['cheque_date'] = $row->cheque_date;
				$balance = number_format((float) $row->amount, 2, '.', '');
				$empArr['amount'] = $balance;
				$empArr['transction_no'] = $row->transction_no;
				$entry_date = date("d/m/Y", strtotime($row->entry_date));
				$empArr['entry_date'] = $entry_date;
				$empArr['particular'] = $particular;
				array_push($Debited_in_Daybook_but_Not_Credited_by, $empArr);
				$Debited_in_Daybook_but_Not_Credited_by_total = $Debited_in_Daybook_but_Not_Credited_by_total + $row->amount;
			} else {
				$empArr['id'] = $row->id;
				$empArr['daybook_ref_id'] = $row->daybook_ref_id;
				$empArr['cheque_no'] = $row->cheque_no;
				$empArr['cheque_date'] = $row->cheque_date;
				$balance = number_format((float) $row->amount, 2, '.', '');
				$empArr['amount'] = $balance;
				$empArr['transction_no'] = $row->transction_no;
				$entry_date = date("d/m/Y", strtotime($row->entry_date));
				$empArr['entry_date'] = $entry_date;
				$empArr['particular'] = $particular;
				array_push($rest_all, $empArr);
				$rest_all_total = $rest_all_total + $row->amount;
			}
		}
		$finalArr = [
			"rest_all_total" => $rest_all_total,
			"rest_all" => $rest_all,
			"Debited_in_Daybook_but_Not_Credited_by_total" => $Debited_in_Daybook_but_Not_Credited_by_total,
			"Debited_in_Daybook_but_Not_Credited_by" => $Debited_in_Daybook_but_Not_Credited_by,
			"Credited_in_Daybook_but_Not_Debited_by_total" => $Credited_in_Daybook_but_Not_Debited_by_total,
			"Credited_in_Daybook_but_Not_Debited_by" => $Credited_in_Daybook_but_Not_Debited_by,
			"Bank_chagres_total" => $Bank_chagres_total,
			"Bank_chagres" => $Bank_chagres,
			"account_details" => $account_details,
			"closing_balance" => $closing_balance,
			"selectedYear" => $selectedYear,
			"selectedMonthName" => $selectedMonthName,
			"previousOpeningBalanceStatement" => $previousOpeningBalanceStatement,
			"previousOpeningDaybookBalance" => $previousOpeningDaybookBalance,
			"Currentclosingbankstatement" => $Currentclosingbankstatement
		];
		// dd($finalArr);
		return view('templates.admin.brs.brs_report', $finalArr);
	}
}