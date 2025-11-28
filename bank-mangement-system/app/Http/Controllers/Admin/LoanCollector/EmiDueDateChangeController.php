<?php
namespace App\Http\Controllers\Admin\LoanCollector;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use DB;
use App\Models\User;
use App\Models\Member;
use App\Models\LoanLog;
use App\Models\CollectorAccount;
use App\Models\Memberloans;
use App\Models\Grouploans;

class EmiDueDateChangeController extends Controller
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
	/**
	 * Display a listing of the loans.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function loanCollectorChange()
	{
		if (check_my_permission(Auth::user()->id, "302") != "1") {
			return redirect()->route('admin.dashboard');
		}
		$data['title'] = 'Loan Collector | Collector Change';
		return view('templates.admin.loan.emiDueDateChange.collectorchangeindex', $data);
	}
	public function index()
	{
		if (check_my_permission(Auth::user()->id, "357") != "1") {
			return redirect()->route('admin.dashboard');
		}
		$data['title'] = 'Loan Management | Emi Due Date & Emi Amount Correction';
		return view('templates.admin.loan.emiDueDateChange.index', $data);
	}
	public function loanCollectorDataGet(Request $request)
	{
		$groupList = '';
		$data = Memberloans::with([
			'member' => function ($q) {
				$q->select('id', 'member_id', 'first_name', 'last_name');
			},
			'loanMemberAssociate' => function ($q) {
				$q->select('id', 'associate_no', 'first_name', 'last_name');
			},
			'loanBranch' => function ($q) {
				$q->select('id', 'branch_code', 'name');
			}
		])->with([
					'CollectorAccount' => function ($q) {
						$q->with(['member_collector']);
					}
				])->with(['loans'])->with([
					'loanMemberCompany' => function ($q) {
						$q->select('id', 'member_id');
					}
				])->where('account_number', $request->code)->whereIn('status', [1, 3, 4])
			->first();
		if (empty($data)) {
			$data = Grouploans::
				with([
					'member' => function ($q) {
						$q->select('id', 'member_id', 'first_name', 'last_name');
					},
					'loanMemberAssociate' => function ($q) {
						$q->select('id', 'associate_no', 'first_name', 'last_name');
					},
					'loanBranch' => function ($q) {
						$q->select('id', 'branch_code', 'name');
					}
				])->with([
						'CollectorAccount' => function ($q) {
							$q->with(['member_collector']);
						}
					])
				->with(['loans'])
				->with([
					'loanMemberCompany' => function ($q) {
						$q->select('id', 'member_id');
					}
				])
				->where('account_number', $request->code)
				->whereIn('status', [1, 3, 4])
				->first();
			if ($data) {
				$groupList = Grouploans::with([
					'member' => function ($q) {
						$q->select('id', 'member_id', 'first_name', 'last_name');
					}
				])->with(['loans'])->with([
							'loanMemberCompany' => function ($q) {
								$q->select('id', 'member_id');
							}
						])->where('account_number', $data->account_number)->whereIn('status', [1, 3, 4])->get();
			}
		}
		$type = $request->type;
		if ($data) {
			$ecs_ref_no = (getMemberCompanySsbAccountDetail($data->customer_id, $data->company_id)) ? (getMemberCompanySsbAccountDetail($data->customer_id, $data->company_id)->account_no ?? '') : '';
			if (in_array($data->loan_type,getGroupLoanTypes())) {
				if ($data->status == 3) {
					return \Response::json(['view' => 'You can not change details of clear loan plans!', 'msg_type' => 'error_clear']);
				} else {
					$id = $data->id;
					return \Response::json(['view' => view('templates.admin.loan.emiDueDateChange.collector_detail', ['loanData' => $data, 'groupList' => $groupList])->render(), 'msg_type' => 'success', 'id' => $id, 'ecs_type' => $data->ecs_type, 'ecs_ref_no' => ($ecs_ref_no ?? ''), 'loan_type' => $data->loans->loan_type, 'approve_date' =>  $data->approve_date ,'emi_due_date' =>  $data->emi_due_date,'emi_amount' =>  $data->emi_amount]);
				}
			} else {
				if ($data->status == 3) {
					return \Response::json(['view' => 'You can not change details of clear loan plans!', 'msg_type' => 'error_clear']);
				} else {
					$id = $data->id;
					return \Response::json(['view' => view('templates.admin.loan.emiDueDateChange.collector_detail', ['loanData' => $data, 'groupList' => $groupList])->render(), 'msg_type' => 'success', 'id' => $id, 'ecs_type' => $data->ecs_type, 'ecs_ref_no' => ($ecs_ref_no ?? ''), 'loan_type' => $data->loans->loan_type, 'approve_date' =>  $data->approve_date ,'emi_due_date' =>  $data->emi_due_date,'emi_amount' =>  $data->emi_amount]);
				}
			}
		} else {
			return \Response::json(['view' => 'No data found', 'msg_type' => 'error']);
		}
	}
	
	public function emiDueDateChangeSave(Request $request)
	{
		// dd($request->all());
		DB::beginTransaction();
		try {
			$created_by_id = Auth::user()->id;
			$globaldate = $request['created_at'];
			$created_at = date("Y-m-d H:i:s", strtotime(convertDate($globaldate)));
			$record = $records = [];

			if (in_array($request->loan_type,getloanTypes())) {
				$record['type'] = 2;
				$updateLoan = Memberloans::whereId($request->loan_id);
			} elseif (in_array($request->loan_type,getgroupLoanTypes())){
				$record['type'] = 3;
				$updateLoan = Grouploans::whereId($request->loan_id);
			}
			$dueDate = date("Y-m-d", strtotime(convertDate($request->emiDueDate)));
			if ($request->change_type == 1 ) {
				$update = $updateLoan->whereLoanType($request->loan_type)->update([
					'emi_due_date' => ($dueDate == '' ? null : $dueDate),
				]);
				$status = 16;
				$title = "Emi Due Date Update";
				$old_val = $request->emi_due_date;
                $new_val = $request->emiDueDate;
			}else{
				$update = $updateLoan->whereLoanType($request->loan_type)->update([
					'new_emi_amount' => ($request->emi == '' ? null : $request->emi),
				]);
				$status = 17;
				$title = "emi amount update";
				$old_val = $request->emi_amount;
                $new_val = $request->emi;
			}
				
				
                
				
				$category = getLoanData($request->loan_type);
				$loanData = $updateLoan->get()->toArray();
				$records['loanId'] = $loanData[0]['id'];
				$records['loan_type'] = $loanData[0]['loan_type'];
				$records['loan_category'] = $category->loan_category;
				$records['loan_name'] = $category->name;
				$records['description'] = $request->remark; 
				$records['old_val'] = json_encode($old_val);
				$records['new_val'] = json_encode($new_val);
				$records['status'] = $status;
				$records['is_correction'] = 0;
				$records['status_changed_date'] = date("Y-m-d", strtotime(convertDate($request->create_application_date)));
				$records['created_by'] = (isset(auth()->user()->role_id) && auth()->user()->role_id == 3) ? 2 : 1;
				$records['created_by_name'] = 'Admin';
				$records['user_name'] = auth()->user()->username;
				$records['created_at'] = date("Y-m-d H:i:s", strtotime(convertDate($request->create_application_date)));
                $records['title'] =  $title;
				$recordInsert = LoanLog::create($records);
			
			DB::commit();
			if (!$recordInsert) {
				DB::rollback();
				return back()->with('alert', 'An error occured');
			} else {
				if ($request->change_type == 1) {
					return redirect('admin/loan/updates/emi_due_date/correction')->with('success', 'Emi due date update successfully!');
				} else {
					return redirect('admin/loan/updates/emi_due_date/correction')->with('success', 'Emi amount updated successfully!');
				}
			}
		} catch (\Exception $e) {
			DB::rollback();
			return back()->with('alert', $e->getMessage() . '-' . $e->getLine());
		}
	}

}