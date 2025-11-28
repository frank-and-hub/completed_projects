<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use URL;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Session;
use App\Models\Memberloans;
use App\Models\LoanDayBooks;
use App\Models\Grouploans;
use DB;
use App\Http\Traits\BalanceSheetTrait;
use App\Http\Traits\IsLoanTrait;
class LoanApplicationExportController extends Controller
{
	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	use IsLoanTrait, BalanceSheetTrait;
	public function __construct()
	{
		$this->middleware('auth');
	}
	public function loanApplicationlistExport(Request $request)
	{
		$input = $request->all();
		$start = $input["start"];
		$limit = $input["limit"];
		$returnURL = URL::to('/') . "/asset/LOANAPPLICATION.csv";
		$fileName = env('APP_EXPORTURL') . "asset/LOANAPPLICATION.csv";
		header("Content-type: text/csv");
		//,'Loanotherdocs','GroupLoanMembers','loanInvestmentPlans'
		if ($request['loan_category'] == 'G') {
			$data = Grouploans::has('company')->select('customer_id', 'id', 'branch_id', 'created_at', 'loan_type', 'status', 'account_number', 'member_loan_id', 'applicant_id', 'member_id', 'group_loan_common_id', 'deposite_amount', 'amount', 'approve_date', 'emi_period', 'emi_option', 'due_amount', 'ROI', 'emi_amount', 'credit_amount', 'associate_member_id', 'approved_date', 'company_id')
				->with(['loan' => function ($q) {
					$q->has('company')->select('id', 'name', 'loan_type')->where('loan_type', '=', 'G');
				}])
				->with(['loanMember' => function ($q) {
					$q->select('id', 'member_id', 'first_name', 'last_name', 'mobile_no', 'address', 'associate_code');
				}])
				->with(['loanMemberAssociate' => function ($q) {
					$q->select('id', 'associate_no', 'first_name', 'last_name');
				}])
				->with(['company' => function ($q) {
					$q->select(['id', 'name']);
				}])
				->with(['loanBranch' => function ($q) {
					$q->select('id', 'name', 'zone', 'regan', 'sector', 'branch_code');
				}])
				->with(['loanMemberCompany' => function ($q) {
					$q->select(['id', 'member_id']);
				}])
				->with('LoanCoApplicantsOne:id,member_id,member_loan_id', 'LoanCoApplicantsOne.member:id,first_name,last_name,mobile_no')
				->with('LoanGuarantorOne:id,name,mobile_number')
				->whereIn('status', [0, 1, 2]);
		} else {
			$data = Memberloans::has('company')->select('customer_id', 'id', 'branch_id', 'associate_member_id', 'status', 'loan_type', 'applicant_id', 'group_loan_common_id', 'account_number', 'amount', 'deposite_amount', 'approve_date', 'approved_date', 'emi_amount', 'emi_period', 'emi_option', 'created_at', 'credit_amount', 'ROI', 'due_amount', 'company_id')
				->with(['loan' => function ($q) {
					$q->has('company')->select('id', 'name', 'loan_type')->where('loan_type', '=', 'L');
				}])
				->with(['loanMember' => function ($q) {
					$q->select('id', 'member_id', 'first_name', 'last_name', 'mobile_no', 'address', 'associate_code');
				}])
				->with(['loanMemberAssociate' => function ($q) {
					$q->select('id', 'associate_no', 'first_name', 'last_name');
				}])
				->with(['loanBranch' => function ($q) {
					$q->select('id', 'name', 'zone', 'regan', 'sector', 'branch_code');
				}])
				->with(['loanMemberCompany' => function ($q) {
					$q->select(['id', 'member_id']);
				}])
				->with('company:id,name')
				->with('LoanCoApplicantsOne:id,member_loan_id,member_id', 'LoanCoApplicantsOne.member:id,first_name,last_name,mobile_no')
				->with('LoanGuarantorOne:id,name,mobile_number,member_loan_id')
				->whereIn('status', [0, 1, 2]);
		}
		if (isset($request['is_search']) && $request['is_search'] == 'yes') {
			if ($request['application_start_date'] != '') {
				$startDate = date("Y-m-d", strtotime(convertDate($request['application_start_date'])));
				if ($request['application_end_date'] != '') {
					$endDate = date("Y-m-d ", strtotime(convertDate($request['application_end_date'])));
				} else {
					$endDate = '';
				}
				$data = $data->whereBetween(DB::Raw('date(created_at)'), [$startDate, $endDate]);
			}
			if ($request['plan'] != '') {
				$plan = $request['plan'];
				$data = $data->where('loan_type', '=', $plan);
			}
			if (isset($request['branch_id']) && $request['branch_id'] != '' && $request['branch_id'] > 0) {
				$branch_id = $request['branch_id'];
				$data = $data->where('branch_id', '=', $branch_id);
			}
			if ($request['status'] != '') {
				$status = $request['status'];
				$data = $data->where('status', '=', $status);
			}
			if ($request['member_name'] != '') {
				$member_name = $request['member_name'];
				$data = $data->whereHas('loanMember', function ($query) use ($member_name) {
					$query->where('members.first_name', 'LIKE', '%' . $member_name . '%')
						->orWhere('members.last_name', 'LIKE', '%' . $member_name . '%')
						->orWhere(DB::raw('concat(members.first_name," ",members.last_name)'), 'LIKE', "%$member_name%");
				});
			}
			// if($request['member_id'] !=''){
			//     $member_id=$request['member_id'];
			// 	$data=$data->whereHas('loanMember', function ($query) use ($member_id) {
			//       $query->where('members.member_id','LIKE','%'.$member_id.'%');
			//     }); 
			// }
			if ($request['member_id'] != '') {
				$member_id = $request['member_id'];
				$data = $data->whereHas('loanMemberCompany', function ($query) use ($member_id) {
					$query->where('member_companies.member_id', 'LIKE', '%' . $member_id . '%');
				});
			}
			if ($request['company_id'] != '' && $request['company_id'] > 0) {
				$company_id = $request['company_id'];
				$data = $data->where('company_id', $company_id);
			}
		} else {
			$startDate = date('Y-m-d', strtotime($request->adm_report_currentdate));
			$endDate = date('Y-m-d', strtotime($request->adm_report_currentdate));
		}
		$count = $data->where('is_deleted', 0)->orderby('id', 'DESC')->count();
		$DataArray = $data->where('is_deleted', 0)->orderby('id', 'DESC')->offset($start)->limit($limit)->get();
		$rowReturn = array();
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
		foreach ($DataArray as $row) {
			$sno++;
			$val['S/N'] = $sno;
			$val['Zone'] = $row['loanBranch']['zone'];
			$val['Region'] = $row['loanBranch']['regan'];
			$val['Sector'] = $row['loanBranch']['sector'];
			$val['Branch'] = $row['loanBranch']['name'];
			$val['Branch Code'] = $row['loanBranch']['branch_code'];
			$val['Compnay Name'] = $row['company'] ? $row['company']['name'] : 'N/A';
			$val['Customer ID'] = 'N/A';
			if ($row['loanMember']) {
				$val['Customer ID'] = $row['loanMember']->member_id;
			}
			$val['Member ID'] = 'N/A';
			if ($row['loanMemberCompany']) {
				$val['Member ID'] = $row['loanMemberCompany']->member_id;
			}
			$val['Member Name'] = 'N/A';
			if ($row['loanMember']) {
				$val['Member Name'] = $row['loanMember']->first_name . ' ' . $row['loanMember']->last_name;
			}
			$val['Application Date'] = date("d/m/Y", strtotime(convertDate($row['created_at'])));
			$val['Loan Type'] = $row['loan']['name'] ?? '';
			$val['No. of Installments'] = $row['emi_period'];
			if ($row['emi_option'] == 1) {
				$eType = 'Months';
			} elseif ($row['emi_option'] == 2) {
				$eType = 'Weeks';
			} elseif ($row['emi_option'] == 3) {
				$eType = 'Daily';
			}
			$val['Loan Mode'] = $eType;
			$val['Amount'] = $row['amount'];
			if ($row['status'] == 0) {
				$status = 'Pending';
			} elseif ($row['status'] == 1) {
				$status = 'Approved';
			} elseif ($row['status'] == 2) {
				$status = 'Rejected';
			}
			$val['Staus'] = $status;
			$val['Account Number'] = 'N/A';
			if ($row['status'] == 1) {
				$val['Account Number'] = $row['account_number'];
			}
			$val['Approval Date'] = 'N/A';
			if ($row['status'] == 1 && $row['approved_date'] != '') {
				$val['Approval Date'] = date("d/m/Y", strtotime(convertDate($row['approved_date'])));
			}
			$val['Associate Code'] = 'N/A';
			if (isset($row['loanMemberAssociate']->associate_no)) {
				$val['Associate Code'] = $row['loanMemberAssociate']->associate_no;
			}
			$val['Associate Name'] = 'N/A';
			$val['Associate Name'] = $row['loanMemberAssociate']->first_name . ' ' . $row['children']->last_name;
			$val['Member Mobile'] = $row['loanMember']->mobile_no;
			$coapplicantName = '';
			$coapplicantnumber = '';
			if ($row['LoanCoApplicantsOne']) {
				$coapplicantName = $row['LoanCoApplicantsOne']['member']->first_name . '' . ($row['LoanCoApplicantsOne']['member']->last_name) ?? "";
				$coapplicantnumber = $row['LoanCoApplicantsOne']['member']->mobile_no;
			}
			$val['Co-Applicant Name'] = $coapplicantName;
			$val['Co-Applicant Number'] = $coapplicantnumber;
			$gName = '';
			$gmNumber = '';
			if ($row['LoanGuarantorOne']) {
				$gName = $row['LoanGuarantorOne']->name;
				$gmNumber = $row['LoanGuarantorOne']->mobile_number;
			}
			$val['Guarantor Name'] = $gName;
			$val['Guarantor Number'] = $gmNumber;
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
		//}
	}
	public function loanissuelistExport(Request $request)
	{
		$input = $request->all();
		$start = $input["start"];
		$limit = $input["limit"];
		$_fileName = Session::get('_fileName');
		$returnURL = URL::to('/') . "/report/loanIssuedReport" . $_fileName . ".csv";
		$fileName = env('APP_EXPORTURL') . "report/loanIssuedReport" . $_fileName . ".csv";
		header("Content-type: text/csv");
		if ($request['loan_category'] === 'G') {
			$data = Grouploans::has('company')->select('company_id', 'customer_id', 'id', 'branch_id', 'created_at', 'loan_type', 'status', 'account_number', 'member_loan_id', 'applicant_id', 'member_id', 'group_loan_common_id', 'deposite_amount', 'transfer_amount', 'amount', 'approve_date', 'emi_period', 'emi_option', 'due_amount', 'ROI', 'emi_amount', 'credit_amount', 'associate_member_id', 'closing_date', 'clear_date')
				->with(['loan' => function ($q) {
					$q->has('company')->select('id', 'name', 'loan_type')->where('loan_type', '=', 'G');
				}])
				->with(['loanMember' => function ($q) {
					$q->select('id', 'member_id', 'first_name', 'last_name', 'mobile_no', 'address', 'associate_code');
				}])
				->with(['loanMemberAssociate' => function ($q) {
					$q->select('id', 'associate_no', 'first_name', 'last_name');
				}])
				->with(['loanBranch' => function ($q) {
					$q->select('id', 'name', 'sector');
				}])
				->with(['company' => function ($q) {
					$q->select(['id', 'name']);
				}])
				->with(['loanMemberCompany' => function ($q) {
					$q->select(['id', 'member_id']);
				}, 'getOutstanding' => function ($q) {
					$q->with(['loans' => function ($q) {
						$q->where('loan_type', '=', 'G');
					}]);
				}])->whereIn('status', [4, 3])->whereNotNull('approve_date')
				->where('is_deleted', 0);
		} else {
			$data = Memberloans::has('company')->select('company_id', 'customer_id', 'id', 'branch_id', 'associate_member_id', 'status', 'loan_type', 'applicant_id', 'group_loan_common_id', 'account_number', 'amount', 'deposite_amount', 'transfer_amount', 'approve_date', 'approved_date', 'emi_amount', 'emi_period', 'emi_option', 'created_at', 'credit_amount', 'ROI', 'due_amount', 'closing_date', 'clear_date')
				->with(['loan' => function ($q) {
					$q->has('company')->select('id', 'name', 'loan_type')->where('loan_type', '=', 'L');
				}])
				->with([
					'loanMember:id,member_id,first_name,last_name,mobile_no,address,associate_code',
					'loanMemberAssociate:id,associate_no,first_name,last_name',
					'loanBranch:id,name,sector',
					'loanMemberCompany:id,member_id',
					'company:id,name',
					'getOutstanding' => function ($q) {
						$q->with(['loans' => function ($q) {
							$q->where('loan_type', '!=', 'G');
						}]);
					}
				])
				->whereHas('loan', function ($query) {
					$query->where('loan_type', 'L');
				})
				->whereIn('status', [4, 3])
				->whereNotNull('approve_date')
				->where('is_deleted', 0);
		}
		if (isset($request['is_search']) && $request['is_search'] == 'yes') {
			//dd($request->all());
			if ($request['loanpayment_start_date'] != '') {
				$startDate = date("Y-m-d", strtotime(convertDate($request['loanpayment_start_date'])));
				if ($request['loanpayment_end_date'] != '') {
					$endDate = date("Y-m-d ", strtotime(convertDate($request['loanpayment_end_date'])));
				} else {
					$endDate = '';
				}
				$data = $data->whereBetween('approve_date', [$startDate, $endDate]);
			}
			if ($request['plan'] != '') {
				$plan = $request['plan'];
				$data = $data->where('loan_type', '=', $plan);
			}
			if ($request['loan_status'] != '') {
				$loan_status = $request['loan_status'];
				//dd($loan_status);
				$data = $data->where('status', '=', $loan_status);
			}
			if (isset($request['branch_id']) && $request['branch_id'] != '' && $request['branch_id'] > 0) {
				$branch_id = $request['branch_id'];
				$data = $data->where('branch_id', '=', $branch_id);
			}
			if ($request['member_name'] != '') {
				$member_name = $request['member_name'];
				$data = $data->whereHas('loanMember', function ($query) use ($member_name) {
					$query->where('members.first_name', 'LIKE', '%' . $member_name . '%')
						->orWhere('members.last_name', 'LIKE', '%' . $member_name . '%')
						->orWhere(DB::raw('concat(members.first_name," ",members.last_name)'), 'LIKE', "%$member_name%");
				});
			}
			if ($request['member_id'] != '') {
				$member_id = $request['member_id'];
				$data = $data->whereHas('loanMemberCompany', function ($query) use ($member_id) {
					$query->where('member_companies.member_id', 'LIKE', '%' . $member_id . '%');
				});
			}
			if ($request['account_number'] != '') {
				$account_number = $request['account_number'];
				$data = $data->where('account_number', '=', $account_number);
			}
			if ($request['company_id'] != '' && $request['company_id'] > 0) {
				$company_id = $request['company_id'];
				$data = $data->where('company_id', $company_id);
			}
		} else {
			$startDate = date('Y-m-d', strtotime($request->adm_report_currentdate));
			$endDate = date('Y-m-d', strtotime($request->adm_report_currentdate));
		}
		// $data->dd();
		$count = $data->orderby('id', 'DESC')->count();
		$DataArray = $data->orderby('id', 'DESC')->offset($_POST['start'])->limit($_POST['limit'])->get();
		$rowReturn = array();
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
		foreach ($DataArray as $row) {
			// p($row->toArray());
			// pd(($row->count('id')));
			$sno++;
			$val['S/N'] = $sno;
			$val['Zone'] = getBranchDetail($row['branch_id'])->zone;
			$val['Region'] = getBranchDetail($row['branch_id'])->regan;
			$val['Sector'] = getBranchDetail($row['branch_id'])->sector;
			$val['Branch'] = getBranchDetail($row['branch_id'])->name;
			$val['Branch Code'] = getBranchDetail($row['branch_id'])->branch_code;
			$val['Company'] = 'N/A';
			if ($row['company']) {
				$val['Company'] = $row['company']->name;
			}
			$val['Customer id'] = $row['loanMember']->member_id;
			$val['Member Id'] = $row['loanMemberCompany'] ? $row['loanMemberCompany']->member_id ?? 'N/A' : 'N/A';
			// if($row['loan_type'] == 3)
			// {
			//    $m_name =   $row['loanMemberAssociate']->first_name.' '. $row['loanMemberAssociate']->last_name;
			// }
			// else{
			//     $m_name =  $row['loanMember']->first_name.' '.$row['loanMember']->last_name;
			// }
			$m_name = 'N/A';
			if ($row['loanMember']) {
				$m_name = $row['loanMember']->first_name . ' ' . $row['loanMember']->last_name;
			}
			$val['Member Name'] = $m_name;
			$val['Account Number'] = $row['account_number'];
			$val['Application Date'] = date("d/m/Y", strtotime(convertDate($row['created_at'])));
			if ($row['approve_date'] && $row['approve_date'] != '') {
				$val['Issue Date'] = date("d/m/Y", strtotime(convertDate($row['approve_date'])));
			} else {
				$val['Issue Date'] = 'N/A';
			}
			$val['Plan'] = $row['loan'] ? $row['loan']['name'] : 'N/A';
			$val['Tenure'] = $row['emi_period'];
			if ($row['emi_option'] == 1) {
				$eType = 'Months';
			} elseif ($row['emi_option'] == 2) {
				$eType = 'Weeks';
			} elseif ($row['emi_option'] == 3) {
				$eType = 'Daily';
			}
			$val['Mode'] = $eType;
			$val['Loan Amount'] = $row['amount'];
			if (isset($row['transfer_amount'])) {
				$val['Transfer Amount'] = $row['transfer_amount'];
			} else {
				$val['Transfer Amount'] = 'N/A';
			}
			$amount = LoanDayBooks::where('loan_type', $row['loan_type'])->where('account_number', $row['account_number'])->where('loan_sub_type', '!=', 2)->where('is_deleted', 0)->sum('deposit');
			$val['Total Recovery'] = $amount;
			//used only to calculate ROI Amount
			if (isset($row['emi_option'])) {
				if ($row['emi_option'] == 1) {
					$closingAmountROI = $row['due_amount'] * $row['ROI'] / 1200;
				} elseif ($row['emi_option'] == 2) {
					$closingAmountROI = $row['due_amount'] * $row['ROI'] / 5200;
				} elseif ($row['emi_option'] == 3) {
					$closingAmountROI = $row['due_amount'] * $row['ROI'] / 36500;
				}
			} else {
				$closingAmountROI = 0;
			}
			//used only to calculate ROI Amount
			if (isset($row['due_amount'])) {
				$closingAmount = round($row['due_amount'] + $closingAmountROI);
			} else {
				$closingAmount = 0;
			}
			$outstandingAmount = isset($row['getOutstanding']->out_standing_amount)
				? ($row['getOutstanding']->out_standing_amount > 0 ? $row['getOutstanding']->out_standing_amount : 0)
				: $row->amount;
			$val['Outstanding'] = number_format((float) $outstandingAmount, 2, '.', '');
			//$val['Outstanding'] = '$closingAmount';
			if ($row['status'] == 0) {
				$val['status'] = 'Pending';
			} elseif ($row['status'] == 1) {
				$val['status'] = 'Approved';
			} elseif ($row['status'] == 3) {
				$val['status'] = 'Clear';
			} elseif ($row['status'] == 4) {
				$val['status'] = 'Due';
			}
			if (isset($row['clear_date']) && !empty($row['clear_date'])) {
				$val['Close date (if closed)'] = date("d/m/Y", strtotime($row['clear_date']));
			} else {
				$val['Close date (if closed)'] = 'N/A';
			}
			$payment_date = LoanDayBooks::where('loan_type', $row['loan_type'])->where('account_number', $row['account_number'])->where('is_deleted', 0)->orderBy('created_at', 'desc')->first();
			// if(isset($payment_date->id))
			// {
			//     dd($payment_date);
			// }
			if (isset($payment_date->id)) {
				$val['Last Date Of Emi'] = date("d/m/Y", strtotime($payment_date['payment_date']));
			} else {
				$val['Last Date Of Emi'] = 'N/A';
			}
			if (isset($row['loanMemberAssociate']->associate_no)) {
				$val['Associate Code'] = $row['loanMemberAssociate']->associate_no;
			} else {
				$val['Associate Code'] = 'N/A';
			}
			if ($row['loan_type'] == 3) {
				if (isset($row['associate_member_id'])) {
					$val['Associate Name'] = $row['children']->first_name . ' ' . $row['children']->last_name;
				} else {
					$val['Associate Name'] = 'N/A';
				}
			} else {
				if (isset($row['associate_member_id'])) {
					$val['Associate Name'] = $row['children']->first_name . ' ' . $row['children']->last_name;
				} else {
					$val['Associate Name'] = 'N/A';
				}
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
	public function loanissueClosedExport(Request $request)
	{
		$data = Cache::get('loanCloseReportList');
		$count = Cache::get('loanCloseReportListCount');
		$input = $request->all();
		$start = $input["start"];
		$limit = $input["limit"];
		$returnURL = URL::to('/') . "/report/loanClosedReport.csv";
		$fileName = env('APP_EXPORTURL') . "report/loanClosedReport.csv";
		global $wpdb;
		$postCols = array(
			'post_title',
			'post_content',
			'post_excerpt',
			'post_name',
		);
		header("Content-type: text/csv");
		$totalResults = $count;
		//dd($totalResults);
		$results = $data;
		//dd($results);
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
		foreach (array_slice($results, $start, $limit) as $row) {
			$sno++;
			$val['S/N'] = $sno;
			$val['Zone'] = getBranchDetail($row['branch_id'])->zone;
			$val['Region'] = getBranchDetail($row['branch_id'])->regan;
			$val['Sector'] = getBranchDetail($row['branch_id'])->sector;
			$val['Branch'] = getBranchDetail($row['branch_id'])->name;
			$val['Branch Code'] = getBranchDetail($row['branch_id'])->branch_code;
			$val['Company'] = 'N/A';
			if ($row['company']) {
				$val['Company'] = $row['company']['name'];
			}
			$val['Customer Id'] = $row['loan_member']['member_id'] ?? '';
			$val['Member Id'] = $row['loan_member_company'] ? $row['loan_member_company']['member_id'] : 'N/A';
			$val['Member Name'] = isset($row['loan_member']) ? $row['loan_member']['first_name'] . ' ' . $row['loan_member']['last_name'] : 'N/A';
			$val['Account Number'] = $row['account_number'];
			if (isset($row['approve_date'])) {
				$val['Issue Date'] = date("d/m/Y", strtotime(convertDate($row['approve_date'])));
			} else {
				$val['Issue Date'] = 'N/A';
			}
			$val['Close Date'] = date("d/m/Y", strtotime(convertDate($row['closing_date'])));
			$val['Plan'] = $row['loan']['name'];
			$val['Tenure'] = $row['emi_period'];
			if ($row['emi_option'] == 1) {
				$eType = 'Months';
			} elseif ($row['emi_option'] == 2) {
				$eType = 'Weeks';
			} elseif ($row['emi_option'] == 3) {
				$eType = 'Daily';
			}
			$val['Mode'] = $eType;
			$val['Loan Amount'] = $row['amount'];
			$amount = LoanDayBooks::where('loan_type', $row['loan_type'])
				->where('account_number', $row['account_number'])
				->where('is_deleted', 0)->sum('deposit');
			$val['Total Recovery'] = $amount;
			//used only to calculate ROI Amount
			if (isset($row['emi_option'])) {
				if ($row['emi_option'] == 1) {
					$closingAmountROI = $row['due_amount'] * $row['ROI'] / 1200;
				} elseif ($row['emi_option'] == 2) {
					$closingAmountROI = $row['due_amount'] * $row['ROI'] / 5200;
				} elseif ($row['emi_option'] == 3) {
					$closingAmountROI = $row['due_amount'] * $row['ROI'] / 36500;
				}
			} else {
				$closingAmountROI = 0;
			}
			//used only to calculate ROI Amount
			if (isset($row['due_amount'])) {
				$closingAmount = round($row['due_amount'] + $closingAmountROI);
			} else {
				$closingAmount = 0;
			}
			//$val['Balance'] = $closingAmount;
			$val['Balance'] = 'N/A';
			if (isset($row['loan_member_associate']['associate_no'])) {
				$val['associate_code'] = $row['loan_member_associate']['associate_no'];
			} else {
				$val['associate_code'] = 'N/A';
			}
			if ($row['loan_type'] == 3) {
				if (isset($row['associate_member_id'])) {
					$val['Associate Name'] = $row['children']['first_name'] . ' ' . $row['children']['last_name'];
				} else {
					$val['Associate Name'] = 'N/A';
				}
			} else {
				if (isset($row['associate_member_id'])) {
					$val['Associate Name'] = $row['children']['first_name'] . ' ' . $row['children']['last_name'];
				} else {
					$val['Associate Name'] = 'N/A';
				}
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
}