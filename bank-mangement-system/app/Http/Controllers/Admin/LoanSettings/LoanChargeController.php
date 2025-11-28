<?php
namespace App\Http\Controllers\Admin\LoanSettings;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Loans;
use App\Models\Memberloans;
use App\Models\Grouploans;
use URL;
use App\Http\Traits\EmiDatesTraits;
use App\Http\Traits\Oustanding_amount_trait;
use App\Models\LoanCharge;
use Validator;
use Illuminate\Validation\Rule;

class LoanChargeController extends Controller
{
	use EmiDatesTraits, Oustanding_amount_trait;
	/**
	 * Instantiate a new controller instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->middleware('auth');
	}
	/**
	 * Display a listing of the loans.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function LoanCharges()
	{
		if (check_my_permission(Auth::user()->id, "301") != "1") {
			return redirect()->route('admin.dashboard');
		}
		$data['title'] = 'Loan Charge | Listing';
		return view('templates.admin.loan.loansettings.loancharges', $data);
	}
	public function LoanChargesCreate()
	{
		if (check_my_permission(Auth::user()->id, "302") != "1") {
			return redirect()->route('admin.dashboard');
		}
		$data['title'] = 'Loan Charge | Create';
		$data['loans'] = Loans::where('status', 1)->get();
		$data['type'] = "add";
		return view('templates.admin.loan.loansettings.createloancharges', $data);
	}
	public function planByLoanType(Request $request)
	{
		//dd($request->all());
		if ($request->loan_type == 1) {
			$data = \App\Models\Loans::where('loan_type', 'L')->where('status', 1)->get();
		} else {
			$data = \App\Models\Loans::where('loan_type', 'G')->where('status', 1)->get();
		}
		$return_array = compact('data');
		return json_encode($return_array);
	}
	public function tenureByPlanName(Request $request)
	{
		if ($request->plan_name == 3) {
			$data = \App\Models\LoanTenure::where('loan_id', $request->plan_name)->where('status', 1)->get();
		} else {
			$data = \App\Models\LoanTenure::where('loan_id', $request->plan_name)->where('status', 1)->get();
			//dd( $data);
		}
		$data = $data;
		//dd($data);	
		$return_array = compact('data');
		return json_encode($return_array);
	}
	public function loanChargeCheckExistingTenure(Request $request)
	{
		$exist = LoanCharge::where('type', $request->type)->where('loan_type', $request->loan_type)->where('plan_name', $request->plan_name)->where('tenure', $request->tenure)->first();
		if (isset($exist->id) && !empty($exist->id)) {
			return \Response::json(['view' => 'Already Exist', 'msg_type' => 'exist']);
		} else {
			return \Response::json(['view' => 'Not Found', 'msg_type' => 'not_exist']);
		}
	}
	public function LoanChargesStore(Request $request)
	{
		$rules = [
			'charges_emi_option' => 'required',
			'charges_tenure' => 'required',
			'charge_type' => 'required',
			'charge' => 'required',
			'min_amount' => 'required',
			'max_amount' => 'required',
			'effective_from' => 'required'
		];
		$customMessages = [
			'required' => 'The :attribute field is required.'
		];
		$this->validate($request, $rules, $customMessages);
		$checkRecordExist = LoanCharge::where(function ($q) use ($request) {
			$q->where('min_amount', '>=', $request->min_amount)->where('max_amount', '<=', $request->max_amount)->orWhere(function ($q) use ($request) {
				$q->where('min_amount', '>=', $request->min_amount)->where('max_amount', '>=', $request->max_amount);
			});
		})->where('loan_id', $request->loanId)->whereNull('effective_to')->where('type', $request->chargeMode)->exists();
		if ($checkRecordExist && !isset($request->id)) {
			$msg = ($request->chargeMode == 1) ? 'Loan Charge' : 'Insurance Charge';
			return redirect()->back()->with('alert', $msg . ' Already Created For this Loan');
		}
		$created_by_id = Auth::user()->id;
		if (isset($request->chargeMode) && $request->chargeMode == 1) {
			$data['charge_type'] = $request->charge_type;
			$data['type'] = $request->chargeMode;
			$data['charge'] = $request->charge;
			$data['min_amount'] = $request->min_amount;
			$data['max_amount'] = $request->max_amount;
			$data['effective_from'] = date("Y-m-d", strtotime(convertDate($request->effective_from)));
			$data['effective_to'] = isset($request->effective_to) ? date("Y-m-d", strtotime(convertDate($request->effective_to))) : NULL;
			$data['created_by'] = 1;
			$data['created_by_id'] = $created_by_id;
			$data['tenure'] = $request->charges_tenure;
			$data['emi_option'] = $request->charges_emi_option;
			// $data['plan_name'] = $request->loanId;
			$data['loan_id'] = $request->loanId;
		}
		if (isset($request->chargeMode) && $request->chargeMode == 2) {
			$data['charge_type'] = $request->charge_type;
			$data['type'] = $request->chargeMode;
			$data['charge'] = $request->charge;
			$data['min_amount'] = $request->min_amount;
			$data['max_amount'] = $request->max_amount;
			$data['effective_from'] = date("Y-m-d", strtotime(convertDate($request->effective_from)));
			$data['effective_to'] = isset($request->effective_to) ? date("Y-m-d", strtotime(convertDate($request->effective_to))) : NULL;
			$data['created_by'] = 1;
			$data['created_by_id'] = $created_by_id;
			$data['tenure'] = $request->charges_tenure;
			$data['emi_option'] = $request->charges_emi_option;
			// $data['plan_name'] = $request->loanId;
			$data['loan_id'] = $request->loanId;
		}
		if ($request['id'] == "") {
			$getLoanData = Loans::whereId($request->loanId)->first(['id', 'company_id']);
			$data['company_id'] = $getLoanData->company_id;
			$loanchargecreate = LoanCharge::create($data);
		} else {
			$loanchargecreate = LoanCharge::where("id", $request['id'])->update($data);
		}
		return redirect()->back()->with('success', 'Loan Charges Updated Successfully!');
	}
	public function LoanChargesList(Request $request)
	{
		/*This function has been used to change status of loanchargeID
			  If a user click on action->change status button then using ajax this function will call
			  */
		if (!empty($request->loanchargeid)) {
			$startDatee = (checkMonthAvailability(date('d'), date('m'), date('Y'), 33));
			$currentDatee = date('d/m/Y', strtotime($startDatee));
			$effective_to = $startDatee;
			$status = LoanCharge::where('id', $request->loanchargeid)->first('status');
			//dd($staus->status);
			if ($status->status == 1) {
				LoanCharge::where('id', $request->loanchargeid)->update(['status' => 0, 'effective_to' => $effective_to]);
				return response()->json(['status' => 'true']);
			}
			// if($staus->status==0){
			// 	LoanCharge::where('id',$request->loanchargeid)->update(['status'=>1,'effective_to'=>$effective_to]);
			// 	return response()->json(['status' => 'true']);
			// }else{
			// 	LoanCharge::where('id',$request->loanchargeid)->update(['status'=>0,'effective_to'=>$effective_to]);
			// 	return response()->json(['status' => 'true']);
			// }
		}
		$data = LoanCharge::select('id', 'type', 'loan_type', 'plan_name', 'tenure', 'min_amount', 'max_amount', 'charge', 'charge_type', 'status', 'effective_from', 'effective_to', 'created_by', 'created_by_id', 'created_at', 'updated_at')->with(['loans'])->with(['PlanName'])->with('PlanTenure');
		$data1 = $data->count('id');
		$count = $data1;
		$totalCount = $count;
		$data = $data->orderby('created_at', 'DESC')->offset($_POST['start'])->limit($_POST['length'])->get();
		$sno = $_POST['start'];
		$rowReturn = array();
		//dd($data);
		foreach ($data as $row) {
			$sno++;
			$val['DT_RowIndex'] = $sno;
			if ($row->type == 1) {
				$type = "File Charge";
			} else {
				$type = "Insurance Chage";
			}
			$val['type'] = $type;
			if ($row->loan_type == 1) {
				$val['loan_type'] = "Loan";
			} elseif ($row->loan_type == 2) {
				$val['loan_type'] = "Group Loan";
			}
			$val['plan_name'] = $row['PlanName']->name;
			$val['tenure'] = $row['PlanTenure']->name;
			$val['min_amount'] = number_format($row->min_amount, 2, '.', '');
			$val['max_amount'] = number_format($row->max_amount, 2, '.', '');
			if ($row->charge_type == 0) {
				$val['charge'] = number_format($row->charge, 2, '.', '') . '%';
			} else {
				$val['charge'] = number_format($row->charge, 2, '.', '');
			}
			if ($row->charge_type == 0) {
				$charge_type = "Percentage";
			} else {
				$charge_type = "Fixed";
			}
			$val['charge_type'] = $charge_type;
			$val['status'] = $row->status;
			if (isset($row->effective_from)) {
				$effective_from = date("d/m/Y", strtotime($row->effective_from));
			} else {
				$effective_from = 'N/A';
			}
			$val['effective_from'] = $effective_from;
			if (isset($row->effective_to)) {
				$effective_to = date("d/m/Y", strtotime($row->effective_to));
			} else {
				$effective_to = 'N/A';
			}
			$val['effective_to'] = $effective_to;
			$createdby = "";
			if ($row->created_by == 1) {
				$createdby = "Admin";
			} elseif ($row->created_by == 2) {
				$createdby = "Branch";
			} else {
				$createdby = "Sub Admin";
			}
			$val['created_by'] = $createdby;
			if ($row->created_by == 1) {
				$createdbyname = \App\Models\Admin::where('id', $row->created_by_id)->first('username');
				$createdbyname = $createdbyname->username;
			} else {
				$createdbyname = \App\Models\Branch::where('id', $row->created_by_id)->first('name');
				$createdbyname = $createdbyname->name;
			}
			$val['created_by_username'] = $createdbyname;
			$val['created_at'] = date("d/m/Y", strtotime($row->created_at));
			$val['updated_at'] = date("d/m/Y", strtotime($row->updated_at));
			$eurl = URL::to("admin/loan/loansettings/loancharges-edit/" . $row->id . "");
			if ($row->status == 1) {
				$btn = '<div class="list-icons"><div class="dropdown"><a href="#" class="list-icons-item" data-toggle="dropdown"><i class="icon-menu9 mr-2"></i></a><div class="dropdown-menu dropdown-menu-right">';
				$btn .= '<button class="dropdown-item" onclick="changeStatus(' . $row->id . ')" ><i class="fas fa-thumbs-up mr-2"></i>Change Status</button>';
				if ($row->plan_name == 3) {
					if ($row->type == 1) {
						$checkData = Grouploans::where('loan_type', $row->plan_name)->where('file_charges', $row->charge)->where('emi_period', $row['PlanTenure']->tenure)->where('emi_option', $row['PlanTenure']->emi_option)->count();
					} elseif ($row->type == 2) {
						$checkData = Grouploans::where('loan_type', $row->plan_name)->where('insurance_charge', $row->charge)->where('emi_period', $row['PlanTenure']->tenure)->where('emi_option', $row['PlanTenure']->emi_option)->count();
					}
				} else {
					if ($row->type == 1) {
						$checkData = Memberloans::where('loan_type', $row->plan_name)->where('file_charges', $row->charge)->where('emi_period', $row['PlanTenure']->tenure)->where('emi_option', $row['PlanTenure']->emi_option)->count();
					} elseif ($row->type == 2) {
						$checkData = Memberloans::where('loan_type', $row->plan_name)->where('insurance_charge', $row->charge)->where('emi_period', $row['PlanTenure']->tenure)->where('emi_option', $row['PlanTenure']->emi_option)->count();
					}
				}
				if ($checkData == 0) {
					$btn .= '<a class="dropdown-item" href="' . $eurl . '"><i class="icon-pencil7 mr-2"></i>Edit</a>';
				}
				$btn .= '</div></div></div>';
				$val['action'] = $btn;
			} else {
				$val['action'] = "N/A";
			}
			$rowReturn[] = $val;
		}
		$value = Cache::put('loan_charge_data', $data);
		Cache::put('loan_charge_data_count', $totalCount);
		$output = array("draw" => $_POST['draw'], "recordsFiltered" => $count, "recordsTotal" => $totalCount, "data" => $rowReturn, );
		return json_encode($output);
	}
	/**
	 * Export Loancharge in Excel
	 * using cache of LoanFileChargeListing
	 * @params cache key is loan_charge_data
	 */
	public function LoanChargelistExport(Request $request)
	{
		$data = Cache::get('loan_charge_data');
		$count = Cache::get('loan_charge_data_count');
		$input = $request->all();
		$start = $input["start"];
		$limit = $input["limit"];
		$returnURL = URL::to('/') . "/asset/loan_charge.csv";
		$fileName = env('APP_EXPORTURL') . "asset/loan_charge.csv";
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
		foreach ($results as $row) {
			$sno++;
			$val['S.No'] = $sno;
			if ($row->type == 1) {
				$type = "File Charge";
			} else {
				$type = "Insurance Chage";
			}
			$val['Type'] = $type;
			if ($row->loan_type == 1) {
				$loan_type = "Loan";
			} elseif ($row->loan_type == 2) {
				$loan_type = "Group Loan";
			}
			$val['Loan Type'] = $loan_type;
			$val['Plan Name'] = $row['PlanName']->name;
			$val['Tenure'] = $row['PlanTenure']->name;
			$val['Min Amount'] = number_format($row->min_amount, 2, '.', '');
			$val['Max Amount'] = number_format($row->max_amount, 2, '.', '');
			if ($row->charge_type == 0) {
				$val['Charge'] = number_format($row->charge, 2, '.', '');
			} else {
				$val['Charge'] = number_format($row->charge, 2, '.', '');
			}
			if ($row->charge_type == 0) {
				$charge_type = "Percentage";
			} elseif ($row->charge_type == 1) {
				$charge_type = "Fixed";
			}
			$val['Charge Type'] = $charge_type;
			if ($row->status == 0) {
				$status = "Inactive";
			} else {
				$status = "Active";
			}
			$val['Status'] = $status;
			if (isset($row->effective_from)) {
				$effective_from = date("d/m/Y", strtotime($row->effective_from));
			} else {
				$effective_from = 'N/A';
			}
			$val['Effective From'] = $effective_from;
			if (isset($row->effective_to)) {
				$effective_to = date("d/m/Y", strtotime($row->effective_to));
			} else {
				$effective_to = 'N/A';
			}
			$val['Effective To'] = $effective_to;
			$createdby = "";
			if ($row->created_by == 1) {
				$createdby = "Admin";
			} elseif ($row->created_by == 2) {
				$createdby = "Branch";
			} else {
				$createdby = "Sub Admin";
			}
			$val['Created By'] = $createdby;
			if ($row->created_by == 1) {
				$createdbyname = \App\Models\Admin::where('id', $row->created_by_id)->first('username');
				$createdbyname = $createdbyname->username;
			} else {
				$createdbyname = \App\Models\Branch::where('id', $row->created_by_id)->first('name');
				$createdbyname = $createdbyname->name;
			}
			$val['Created By Username'] = $createdbyname;
			$val['Created At'] = date("d/m/Y", strtotime($row->created_at));
			$val['Updated At'] = date("d/m/Y", strtotime($row->updated_at));
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
	public function LoanChargesEdit($id)
	{
		//dd($checkloantype->plan_name);
		$checkloantype = LoanCharge::where('id', $id)->first('plan_name');
		$data['title'] = "Loan Charge | Edit";
		if ($checkloantype->plan_name == 3) {
			$loans = Loans::where('loan_type', 'G')->get();
		} else {
			$loans = Loans::where('loan_type', 'L')->get();
		}
		// dd($loans);
		$data['loans'] = $loans;
		$data['loancharge'] = LoanCharge::findOrFail($id);
		$data['id'] = $id;
		$data['type'] = "edit";
		if ($checkloantype->plan_name == 3) {
			$tenureData = \App\Models\LoanTenure::where('loan_id', $checkloantype->plan_name)->where('status', 1)->get();
		} else {
			$tenureData = \App\Models\LoanTenure::where('loan_id', $checkloantype->plan_name)->where('status', 1)->get();
		}
		$tenuredata = $tenureData;
		//dd($tenuredata);
		$data['tenure'] = $tenuredata;
		return view('templates.admin.loan.loansettings.createloancharges', $data);
	}
}