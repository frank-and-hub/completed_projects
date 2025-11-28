<?php
namespace App\Http\Controllers\Admin;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use URL;
use Illuminate\Http\Request;
use App\Models\DemandAdvice;
use App\Models\Branch;
use App\Models\Memberinvestments;
use DB;
use Validator;
use Carbon\Carbon;
use PDF;
use Log;
use Illuminate\Support\Facades\Cache;
use App\Http\Traits\IsLoanTrait;
class MaturityDemandExportController extends Controller
{
	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->middleware('auth');
	}
	public function maturitydemandlistExport(Request $request)
	{
		$data = Cache::get('maturity_demandlist');
		$count = Cache::get('maturity_demandlist_count');
		$input = $request->all();
		$start = $input["start"];
		$limit = $input["limit"];
		$returnURL = URL::to('/') . "/report/maturitydemand.csv";
		$fileName = env('APP_EXPORTURL') . "report/maturitydemand.csv";
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
		foreach ($results->slice($start, $limit) as $row) {
			$sno++;
			$val['S/N'] = $sno;
			if (isset($row['investment']['branch'])) {
				$val['branch'] = $row['investment']['branch']['name'];
			} else {
				$val['branch'] = 'N/A';
			}
			$val['customer_id'] = $row['investment']['member']['member_id'];
			$val['member_id'] = 'N/A';
			$val['member_name'] = 'N/A';
			//$val['branch'] = $sno;
			if (isset($row['investment_id']) && !empty($row['investment_id'])) {
				$val['member_id'] = $row['investment']['memberCompany']['member_id'];
				$val['member_name'] = $row['investment']['member']['first_name'] . ' ' . $row['investment']['member']['last_name'];
			} else {
				$val['Member ID'] = 'N/A';
				$val['Member Name'] = 'N/A';
			}
			if (isset($row['account_number'])) {
				$val['Account Number'] = $row['account_number'];
			} elseif (isset($row['investment_id'])) {
				$val['Account Number'] = $row['investment']['account_number'];
			} else {
				$val['Account Number'] = 'N/A';
			}
			if (isset($row['plan_name'])) {
				$val['Plan'] = $row->plan_name;
			} elseif (isset($row['investment_id'])) {
				$val['Plan'] = $row['investment']['plan']['name'];
			} else {
				$val['Plan'] = 'N/A';
			}
			if (isset($row['investment']['tenure'])) {
				$val['Tenure'] = $row['investment']['tenure'] . ' Year';
			} else {
				$val['Tenure'] = 'N/A';
			}
			if (isset($row['investment_id'])) {
				$val['Open Date'] = date("d/m/Y", strtotime(convertDate($row['investment']->created_at)));
			} else {
				$val['Open Date'] = 'N/A';
			}
			if (isset($row['investment']['maturity_date'])) {
				$val['Maturity Date'] = date("d/m/Y", strtotime(convertDate($row['investment']['maturity_date'])));
			} else {
				$val['Maturity Date'] = 'N/A';
			}
			if (isset($row->date)) {
				$val['demand_date'] = date("d/m/Y", strtotime(convertDate($row->date)));
			} else {
				$val['demand_date'] = 'N/A';
			}
			if (isset($row['sumdeposite'])) {
				$val['Total Deposite'] = number_format($row['sumdeposite']->sum('deposit'), 2, '.', '');
			}
			if (isset($row['sumdeposite2'])) {
				$val['Total Deposite'] = number_format($row->sumdeposite2->sum('deposit'), 2, '.', '');
			} else {
				$val['Total Deposite'] = 'N/A';
			}
			if ($row['status'] == 1 && $row['is_mature'] == 0) {
				if (isset($row->payment_date)) {
					$val['Payment Date'] = date('d/m/Y', strtotime(convertDate($row->payment_date)));
				} else {
					$val['Payment Date'] = 'N/A';
				}
			} else {
				$val['Payment Date'] = 'N/A';
			}
			if ($row['status'] == 1 && $row['is_mature'] == 0) {
				if (isset($row->payment_mode)) {
					if ($row->payment_mode == 0) {
						$val['Payment Mode'] = "Cash";
					} elseif ($row->payment_mode == 1) {
						$val['Payment Mode'] = "Cheque";
					} elseif ($row->payment_mode == 2) {
						$val['Payment Mode'] = "Online Transfer";
					} elseif ($row->payment_mode == 3) {
						$val['Payment Mode'] = "SSB GV Transfer";
					} elseif ($row->payment_mode == 4) {
						$val['Payment Mode'] = "Auto Transfer (ECS)";
					}
				} else {
					$val['Payment Mode'] = 'N/A';
				}
			} else {
				$val['Payment Mode'] = 'N/A';
			}
			if ($row['status'] == 0 && $row['is_mature'] == 1) {
				$val['status'] = 'Pending';
			} elseif ($row['status'] == 0 && $row['is_mature'] == 0) {
				$val['status'] = 'Processed';
			} elseif ($row['status'] == 1 && $row['is_mature'] == 0) {
				$val['status'] = 'Paid';
			}
			if (!empty($row['investment']['associateMember'])) {
				$val['Associate Code'] = $row['investment']['associateMember']->associate_no;
			} else {
				$val['Associate Code'] = 'N/A';
			}
			if (!empty($row['investment']['associateMember'])) {
				$val['Associate Name'] = $row['investment']['associateMember']->first_name . ' ' . $row['investment']['associateMember']->last_name; //customGetBranchDetail($row->branch_id)->sector;
			} else {
				$val['Associate Name'] = 'N/A';
			}
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
	public function maturitypaymentlistExport(Request $request)
	{
		$data = Cache::get('maturity_paymentlist');
		$count = Cache::get('maturity_paymentlist_count');
		$input = $request->all();
		$start = $input["start"];
		$limit = $input["limit"];
		$returnURL = URL::to('/') . "/report/maturitypayment.csv";
		$fileName = env('APP_EXPORTURL') . "report/maturitypayment.csv";
		global $wpdb;
		$postCols = array(
			'post_title',
			'post_content',
			'post_excerpt',
			'post_name',
		);
		header("Content-type: text/csv");
		if (isset($request['is_search']) && $request['is_search'] == 'yes') {
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
			foreach ($results->slice($start, $limit) as $row) {
				$sno++;
				$val['S/N'] = $sno;
				if (isset($row['investment']['branch'])) {
					$val['branch'] = $row['investment']['branch']['name'];
				} else {
					$val['branch'] = 'N/A';
				}
				$val['customer_id'] = 'N/A';
				if (isset($row['investment']['member'])) {
					$val['customer_id'] = $row['investment']['member']['member_id'];
				}
				if (isset($row['investment_id']) && !empty($row['investment_id'])) {
					$val['Member ID'] = $row['investment']['memberCompany']['member_id'];
					$val['Member Name'] = $row['investment']['member']['first_name'] . ' ' . $row['investment']['member']['last_name'];
				} elseif (isset($row['investment']) && !empty($row['investment'])) {
					$val['Member ID'] = $row['investment']['memberCompany']['member_id'];
					$val['Member Name'] = $row['investment']['member']['first_name'] . ' ' . $row['investment']['member']['last_name'];
				} else {
					$val['Member ID'] = 'N/A';
					$val['Member Name'] = 'N/A';
				}
				if (isset($row['account_number'])) {
					$val['Account number'] = $row['account_number'];
				} elseif (isset($row['investment'])) {
					$val['Account number'] = $row['investment']['account_number'];
				} else {
					$val['Account number'] = 'N/A';
				}
				if (isset($row['plan_name'])) {
					$val['Plan'] = $row['investment']['plan']['name'];
				} elseif (isset($row['investment'])) {
					$val['Plan'] = $row['investment']['plan']['name'];
				} else {
					$val['Plan'] = 'N/A';
				}
				if (isset($row['investment']['tenure'])) {
					$val['Tenure'] = $row['investment']['tenure'] . ' Year';
				} else {
					$val['Tenure'] = 'N/A';
				}
				if (isset($row['investment_id'])) {
					$val['Open Date'] = date("d/m/Y", strtotime(convertDate($row['investment']['created_at'])));
				} else {
					$val['Open Date'] = 'N/A';
				}
				if (isset($row['investment']['maturity_date'])) {
					$val['Maturity Date'] = date("d/m/Y", strtotime(convertDate($row['investment']['maturity_date'])));
				} else {
					$val['Maturity Date'] = 'N/A';
				}
				if (isset($row['payment_date'])) {
					$val['Payment Date'] = date("d/m/Y", strtotime(convertDate($row['payment_date'])));
				} else {
					$val['Payment Date'] = 'N/A';
				}
				if (isset($row['sumdeposite2'])) {
					$val['Total Deposit'] = number_format($row['sumdeposite2']->sum('deposit'), 2, '.', '');
				} else {
					$val['Total Deposit'] = 'N/A';
				}
				if (isset($row['final_amount'])) {
					$val['payment amount'] = $row['final_amount'];
				} else {
					$val['payment amount'] = 'N/A';
				}
				if (isset($row->payment_mode)) {
					if ($row->payment_mode == 0) {
						$val['Payment Mode'] = 'Cash';
					} elseif ($row->payment_mode == 1) {
						$val['Payment Mode'] = 'Cheque';
					} elseif ($row->payment_mode == 2) {
						$val['Payment Mode'] = 'Online Transfer';
					} elseif ($row->payment_mode == 3) {
						$val['Payment Mode'] = 'SSB/GV Transfer';
					} elseif ($row->payment_mode == 4) {
						$val['Payment Mode'] = 'Auto Transfer(ECS)';
					} else {
						$val['Payment Mode'] = 'N/A';
					}
				} else {
					$val['Payment Mode'] = 'N/A';
				}
				if (!empty($row['investment']['associateMember'])) {
					$val['associate code'] = $row['investment']['associateMember']->associate_no;
				} else {
					$val['associate code'] = 'N/A';
				}
				if (!empty($row['investment']['associateMember'])) {
					$val['associate name'] = $row['investment']['associateMember']->first_name . ' ' . $row['investment']['associateMember']->last_name; //customGetBranchDetail($row->branch_id)->sector;
				} else {
					$val['associate name'] = 'N/A';
				}
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
		} else {
			$response = array(
				'result' => 0,
				'start' => 0,
				'limit' => 0,
				'totalResults' => 0,
				'fileName' => 0,
				'percentage' => 0
			);
			echo json_encode($response);
		}
	}
	public function maturityoverdueExport(Request $request)
	{
		$data = Cache::get('maturity_overduelist');
		$count = Cache::get('maturity_overduelist_count');
		$input = $request->all();
		$start = $input["start"];
		$limit = $input["limit"];
		$returnURL = URL::to('/') . "/report/maturityoverdue.csv";
		$fileName = env('APP_EXPORTURL') . "report/maturityoverdue.csv";
		global $wpdb;
		$postCols = array(
			'post_title',
			'post_content',
			'post_excerpt',
			'post_name',
		);
		header("Content-type: text/csv");
		$sno = $_POST['start'];
		$totalResults = $count;
		$results = $data;
		$result = 'next';
		if (($start + $limit) >= $totalResults) {
			$result = 'finished';
		}
		// if its a fist run truncate the file. else append the file
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
		foreach ($results->slice($start, $limit) as $row) {
			$sno++;
			$val['S/N'] = $sno;
			if (isset($row['branch_id'])) {
				$val['Branch'] = $row['branch']['name']; //customGetBranchDetail($row->branch_id)->name;
			} else {
				$val['Branch'] = 'N/A';
			}
			if ($row['member']) {
				$val['customer_id'] = $row['member']->member_id;
			} else {
				$val['customer_id'] = 'N/A';
			}
			if (isset($row['member']['member_id'])) {
				$val['member_id'] = $row['memberCompany']['member_id']; //customGetBranchDetail($row->branch_id)->sector;
			} else {
				$val['member_id'] = 'N/A';
			}
			if (isset($row['member']['first_name'])) {
				$val['Member Name'] = $row['member']['first_name'] . ' ' . $row['member']['last_name']; //customGetBranchDetail($row->branch_id)->sector;
			} else {
				$val['Member Name'] = 'N/A';
			}
			if (isset($row['account_number'])) {
				$val['Account Number'] = $row['account_number'];
			} else {
				$val['Account Number'] = 'N/A';
			}
			if (isset($row['plan'])) {
				$val['Plan'] = $row['plan']->name;
			} else {
				$val['Plan'] = 'N/A';
			}
			if ($row['plan']->id == 4 || $row['plan']->id == 5) {
				$val['Tenure'] = 1 . ' Year';
			} else {
				$val['Tenure'] = $row->tenure . ' Year';
			}
			if (isset($row->created_at)) {
				$val['Open Date'] = date("d/m/Y", strtotime(convertDate($row->created_at)));
			} else {
				$val['Open Date'] = 'N/A';
			}
			if (isset($row->maturity_date)) {
				$val['Maturity Date'] = date("d/m/Y", strtotime(convertDate($row->maturity_date)));
			} else {
				$val['Maturity Date'] = 'N/A';
			}
			if (isset($row['current_balance'])) {
				$val['Total Deposit'] = $row['current_balance'];
			} else {
				$val['Total Deposit'] = 'N/A';
			}
			if (isset($row['maturity_date'])) {
				$startDatee = (checkMonthAvailability(date('d'), date('m'), date('Y'), ($row['branch'] ? $row['branch']['state_id'] : 33)));
				$currentDatee = Carbon::parse($startDatee);
				$maturitydate = Carbon::parse($row['maturity_date']);
				$planDetailMonthly = array('2', '3', '4', '5', '6', '9', '10', '11');
				$planDetailDaily = array('7');
				$periodMonthly = (in_array($row->plan_id, $planDetailMonthly) ? $currentDatee->diffInMonths($maturitydate) : '');
				$periodDaily = (in_array($row->plan_id, $planDetailDaily) ? $currentDatee->diffInDays($maturitydate) : '');
				//dd($periodMonthly,$periodDaily,$row->plan_id);
				if ($row->plan_id == 7) {
					$val['Overdue Period'] = $periodDaily . 'Days';
				} elseif ($row->plan_id == 2 || $row->plan_id == 3 || $row->plan_id == 4 || $row->plan_id == 5 || $row->plan_id == 6 || $row->plan_id == 9 || $row->plan_id == 10 || $row->plan_id == 11) {
					$val['Overdue Period'] = $periodMonthly . 'Month';
				} else {
					$val['Overdue Period'] = 'N/A';
				}
			} else {
				$val['Overdue Period'] = 'N/A';
			}
			if (!empty($row['associateMember'])) {
				$val['Associate Code'] = $row['associateMember']->associate_no;
			} else {
				$val['Associate Code'] = 'N/A';
			}
			if (!empty($row['associateMember'])) {
				$val['Associate Name'] = $row['associateMember']->first_name . ' ' . $row['associateMember']->last_name; //customGetBranchDetail($row->branch_id)->sector;
			} else {
				$val['Associate Name'] = 'N/A';
			}
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
	public function maturityUpcomingExport(Request $request)
	{
		$data = Cache::get('maturity_upcominglist');
		$count = Cache::get('maturity_upcominglist_count');
		$input = $request->all();
		$start = $input["start"];
		$limit = $input["limit"];
		$returnURL = URL::to('/') . "/report/maturityUpcoming.csv";
		$fileName = env('APP_EXPORTURL') . "report/maturityUpcoming.csv";
		global $wpdb;
		$postCols = array(
			'post_title',
			'post_content',
			'post_excerpt',
			'post_name',
		);
		header("Content-type: text/csv");
		$sno = $_POST['start'];
		$totalResults = $count;
		$results = $data;
		$result = 'next';
		if (($start + $limit) >= $totalResults) {
			$result = 'finished';
		}
		// if its a fist run truncate the file. else append the file
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
		foreach ($results->slice($start, $limit) as $row) {
			$sno++;
			$val['S/N'] = $sno;
			if (isset($row['branch_id'])) {
				$val['Branch'] = $row['branch']['name']; //customGetBranchDetail($row->branch_id)->name;
			} else {
				$val['Branch'] = 'N/A';
			}
			$val['customer_id'] = 'N/A';
			if (isset($row['member']['member_id'])) {
				$val['customer_id'] = $row['member']['member_id'];
			}
			$val['member_id'] = 'N/A';
			if (isset($row['memberCompany']['member_id'])) {
				$val['member_id'] = $row['memberCompany']['member_id'];
			}
			if (isset($row['member']['first_name'])) {
				$val['Member Name'] = $row['member']['first_name'] . ' ' . $row['member']['last_name']; //customGetBranchDetail($row->branch_id)->sector;
			} else {
				$val['Member Name'] = 'N/A';
			}
			if (isset($row['account_number'])) {
				$val['Account Number'] = $row['account_number'];
			} else {
				$val['Account Number'] = 'N/A';
			}
			if (isset($row['plan'])) {
				$val['Plan'] = $row['plan']->name;
			} else {
				$val['Plan'] = 'N/A';
			}
			if ($row['plan']->id == 4 || $row['plan']->id == 5) {
				$val['Tenure'] = 1 . ' Year';
			} else {
				$val['Tenure'] = $row->tenure . ' Year';
			}
			if (isset($row->created_at)) {
				$val['Open Date'] = date("d/m/Y", strtotime(convertDate($row->created_at)));
			} else {
				$val['Open Date'] = 'N/A';
			}
			if (isset($row->maturity_date)) {
				$val['Maturity Date'] = date("d/m/Y", strtotime(convertDate($row->maturity_date)));
			} else {
				$val['Maturity Date'] = 'N/A';
			}
			if (isset($row->deposite_amount)) {
				$val['Deno Amount'] = $row->deposite_amount;
			} else {
				$val['Deno Amount'] = "N/A";
			}
			if (isset($row['current_balance'])) {
				$val['Total Deposit'] = $row['current_balance'];
			} else {
				$val['Total Deposit'] = 'N/A';
			}
			if ($row->plan_id == 4 || $row->plan_id == 5) {
				$formatedcreated_at = Carbon::parse($row->created_at)->addYear();
				$newcreateddate = date("Y-m-d", strtotime(convertDate($formatedcreated_at)));
				if (isset($newcreateddate)) {
					$startDatee = (checkMonthAvailability(date('d'), date('m'), date('Y'), ($row['branch'] ? $row['branch']['state_id'] : 33)));
					$currentDatee = Carbon::parse($startDatee);
					$currentformatedDate = strtotime(convertDate($currentDatee));
					$maturitydate = Carbon::parse($newcreateddate);
					$maturityformatedDate = strtotime(convertDate($maturitydate));
					$remainingDate = $maturityformatedDate - $currentformatedDate;
					$remainingdays = round($remainingDate / (60 * 60 * 24));
					$remainingmonths = round($remainingdays / 30);
					//$remainingmonths = $currentDatee->diffInMonths($maturitydate);
					$planDetailMonthly = array('4', '5');
					$periodMonthly = (in_array($row->plan_id, $planDetailMonthly) ? $remainingmonths : '');
					$tag = ($periodMonthly > 1) ? ' Months' : ' Month';
					$val['Remaining Period'] = $periodMonthly . $tag;
					$expected_deposit = $periodMonthly * $row->deposite_amount;
					if ($row->plan_id == 4) {
						$val['Expected Deposit'] = 0;
					} else {
						$val['Expected Deposit'] = $expected_deposit;
					}
				} else {
					$val['Remaining Period'] = 'N/A';
					$val['Expected Deposit'] = 'N/A';
				}
			} elseif ($row->plan_id == 2 || $row->plan_id == 3 || $row->plan_id == 6 || $row->plan_id == 7 || $row->plan_id == 8 || $row->plan_id == 9 || $row->plan_id == 10 || $row->plan_id == 11) {
				if (isset($row['maturity_date'])) {
					$startDatee = (checkMonthAvailability(date('d'), date('m'), date('Y'), ($row['branch'] ? $row['branch']['state_id'] : 33)));
					$currentDatee = Carbon::parse($startDatee)->floorMonth();
					$maturitydate = Carbon::parse($row['maturity_date'])->floorMonth();
					$currentformatedDate = strtotime(convertDate($currentDatee));
					$maturityformatedDate = strtotime(convertDate($maturitydate));
					$remainingDate = $maturityformatedDate - $currentformatedDate;
					$remainingdays = round($remainingDate / (60 * 60 * 24));
					// $remainingmonths = round($remainingdays/30);
					$remainingmonths = $currentDatee->diffInMonths($maturitydate);
					$planDetailMonthly = array('2', '3', '6', '8', '9', '10', '11');
					$planDetailDaily = array('7');
					$periodMonthly = (in_array($row->plan_id, $planDetailMonthly) ? $remainingmonths : '');
					$periodMonthlyDiffdaily = (in_array($row->plan_id, $planDetailMonthly) ? $remainingdays : '');
					$periodDaily = (in_array($row->plan_id, $planDetailDaily) ? $remainingdays : '');
					// dd($periodMonthly,$periodDaily);
					if ($row->plan_id == 7) {
						$val['Remaining Period'] = $periodDaily . 'Days';
						$expected_deposit = $periodDaily * $row->deposite_amount;
						$val['Expected Deposit'] = $expected_deposit;
					} elseif ($row->plan_id == 2 || $row->plan_id == 3 || $row->plan_id == 6 || $row->plan_id == 8 || $row->plan_id == 9 || $row->plan_id == 10 || $row->plan_id == 11) {
						$tag = ($periodMonthly > 1) ? ' Months' : ' Month';
						$val['Remaining Period'] = $periodMonthly . $tag;
						if ($row->plan_id == 8 || $row->plan_id == 9) {
							$val['Expected Deposit'] = 0;
						} else {
							$expected_deposit = $periodMonthly * $row->deposite_amount;
							$val['Expected Deposit'] = $expected_deposit;
						}
					} else {
						$val['Remaining Period'] = 'N/A';
						$val['Expected Deposit'] = 'N/A';
					}
				} else {
					$val['Remaining Period'] = 'N/A';
					$val['Expected Deposit'] = 'N/A';
				}
			}
			if (!empty($row['associateMember'])) {
				$val['Associate Code'] = $row['associateMember']->associate_no;
			} else {
				$val['Associate Code'] = 'N/A';
			}
			if (!empty($row['associateMember'])) {
				$val['Associate Name'] = $row['associateMember']->first_name . ' ' . $row['associateMember']->last_name; //customGetBranchDetail($row->branch_id)->sector;
			} else {
				$val['Associate Name'] = 'N/A';
			}
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