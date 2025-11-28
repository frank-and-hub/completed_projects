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

class LoanCollectorChangeController extends Controller
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
		return view('templates.admin.loan.loancollector.collectorchangeindex', $data);
	}
	public function index()
	{
		if (check_my_permission(Auth::user()->id, "343") != "1") {
			return redirect()->route('admin.dashboard');
		}
		$data['title'] = 'Loan Update | Ecs Type Change';
		return view('templates.admin.loan.loancollector.index', $data);
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
			},
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
			->where('account_number', $request->code)->whereIn('status', [1, 3, 4])
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
					},
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
				])
					->with(['loans'])
					->with([
						'loanMemberCompany' => function ($q) {
							$q->select('id', 'member_id');
						}
					])
					->where('account_number', $data->account_number)->whereIn('status', [1, 3, 4])->get();
			}
		}
		$type = $request->type;
		if ($data) {
			$ecs_ref_no = (getMemberCompanySsbAccountDetail($data->customer_id, $data->company_id)) ? (getMemberCompanySsbAccountDetail($data->customer_id, $data->company_id)->account_no ?? '') : '';
			if (in_array($data->loan_type,getGroupLoanTypes())) {
				if ($data->status == 3) {
					return \Response::json(['view' => 'You can not change collector of clear loan Account !', 'msg_type' => 'error_clear']);
				} else {
					$id = $data->id;
					return \Response::json(['view' => view('templates.admin.loan.loancollector.collector_detail', ['loanData' => $data, 'groupList' => $groupList])->render(), 'msg_type' => 'success', 'id' => $id, 'ecs_type' => $data->ecs_type, 'ecs_ref_no' => ($ecs_ref_no ?? ''), 'loan_type' => $data->loans->loan_type]);
				}
			} else {
				if ($data->status == 3) {
					return \Response::json(['view' => 'You can not change collector of clear loan Account!', 'msg_type' => 'error_clear']);
				} else {
					$id = $data->id;
					return \Response::json(['view' => view('templates.admin.loan.loancollector.collector_detail', ['loanData' => $data, 'groupList' => $groupList])->render(), 'msg_type' => 'success', 'id' => $id, 'ecs_type' => $data->ecs_type, 'ecs_ref_no' => ($ecs_ref_no ?? ''), 'loan_type' => $data->loans->loan_type]);
				}
			}
		} else {
			return \Response::json(['view' => 'No data found', 'msg_type' => 'error']);
		}
	}
	// Get New Associate Details
	public function getnewAssociteData(Request $request)
	{
		$data = Member::where('associate_no', $request->code)->where('is_deleted', 0)->first();
		$type = $request->type;
		if ($data) {
			if ($data->is_block == 1) {
				return \Response::json(['view' => 'No data found', 'msg_type' => 'error2']);
			} else {
				if ($data->associate_status == 1) {
					$id = $data->id;
					$carder = $data->current_carder_id;
					if ($carder > $request->carder) {
						return \Response::json(['view' => view('templates.admin.loan.loancollector.newassociate_detail', ['memberData' => $data])->render(), 'msg_type' => 'success', 'id' => $id, 'carder' => $carder]);
					} else {
						return \Response::json(['view' => $request->carder . '==' . $carder, 'msg_type' => 'error3']);
					}
				} else {
					return \Response::json(['view' => 'No data found', 'msg_type' => 'error1']);
				}
			}
		} else {
			return \Response::json(['view' => 'No data found', 'msg_type' => 'error']);
		}
	}
	public function loanCollectorChangeSave(Request $request)
	{
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

			if ($request->ecs_type !== '') {
				$update = $updateLoan->whereLoanType($request->loan_type)->update([
					'ecs_type' => ($request->ecs_type == '' ? 0 : $request->ecs_type),
					'ecs_ref_no' => ($request->ecs_type == '' ? '' : ($request->ecs_ref_no ?? null))
				]);
			}

			$exist = CollectorAccount::where('type_id', $request->loan_id)->where('type', $record['type'])->first();
			if (isset($exist->status) && isset($request->new_collector_id)) {
				CollectorAccount::where('type_id', $request->loan_id)->where('type', $record['type'])->update(['status' => 0]);
			}
            $status = [
				2 => 'SSB',
				1 => 'Bank',
				0 => 'Ecs Unregister'
			];
			if ($request->new_collector_id) {
                $record['type_id'] = $request->loan_id;
				$record['associate_id'] = $request->new_collector_id;
				$record['status'] = 1;
				$record['created_id'] = $created_by_id;
				$record['created_by'] = 1;
				$record['created_at'] = $created_at;
				$record['updated_at'] = $created_at;
				$recordInsert = CollectorAccount::create($record);
			} else {
                $old_val = '[' . ($status[$request->etype]) . ']';
                $new_val = '[' . ($status[$request->ecs_type] ) . ']';
				$category = getLoanData($request->loan_type);
				$loanData = $updateLoan->get()->toArray();
				$records['loanId'] = $loanData[0]['id'];
				$records['loan_type'] = $loanData[0]['loan_type'];
				$records['loan_category'] = $category->loan_category;
				$records['loan_name'] = $category->name;
				$records['description'] = $request->ecs_remark; //'ECS Update for A/C '. $loanData[0]['account_number'];
				$records['old_val'] = json_encode($old_val);
				$records['new_val'] = json_encode($new_val);
				$records['status'] = 9;
				$records['is_correction'] = 0;
				$records['status_changed_date'] = date('Y-m-d');
				$records['created_by'] = (isset(auth()->user()->role_id) && auth()->user()->role_id == 3) ? 2 : 1;
				$records['created_by_name'] = 'Admin';
				$records['user_name'] = auth()->user()->username;
				$records['created_at'] = date('Y-m-d H:i:s');
                $records['title'] =  'ECS Type Change';
				$recordInsert = LoanLog::create($records);
			}
			DB::commit();
			if (!$recordInsert) {
				DB::rollback();
				return back()->with('alert', 'Problem With Changing Collector');
			} else {
				if ($request->new_collector_id) {
					return redirect('admin/loan/loancollector/collector-change')->with('success', 'Collector Changed Successfully!');
				} else {
					return redirect('admin/loan/ecs_change')->with('success', 'Loan ECS Type Changed Successfully!');
				}
			}
		} catch (\Exception $e) {
			DB::rollback();
			return back()->with('alert', $e->getMessage() . '-' . $e->getLine());
		}
	}

}
