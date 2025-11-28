<?php
namespace App\Http\Controllers\Admin\Loan;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use URL;
use App\Models\{Loanscoapplicantdetails, LoanTenure, Member, MemberCompany, Memberloans, Loanapplicantdetails, Loansguarantordetails, SavingAccount, Memberinvestments, Grouploans, LoanAgainstDeposit, MemberIdProof, Loaninvestmentmembers, CollectorAccount};
use Validator;
use Carbon\Carbon;
use DB;
use App\Models\LoanDayBooks;
use App\Models\AllHeadTransaction;
use App\Models\BranchDaybook;
use App\Models\SavingAccountTranscation;
use App\Models\SamraddhBankDaybook;
use App\Models\Daybook;
use App\Models\LoanEmisNew;
use DateTime;



class EmiOutstandingUpdateController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
		if (check_my_permission(Auth::user()->id, "365") != "1" && check_my_permission(Auth::user()->id, "365") != "1") {
            return redirect()->route('admin.dashboard');
        }
        $data['title'] = 'Emi outstanding update';

        return view('templates.admin.loan.emi_update_cron.index', $data);
    }

    public function update(Request $request){
        try{
            
            $accountNo = $request['account_number'];
            $date = date("Y-m-d", strtotime(convertDate($request['date'])));
            $errDate = date("Y-m-d", strtotime(convertDate($request['errDate'])));
            if(isset($accountNo) && isset($date) && isset($errDate)){
                DB::select('call re_calculate_accrued_int(?,?,?,?)', [$date,  $accountNo, 0, $errDate]);
                return redirect()->route('admin.loan.emioutstanding.update')->with('success', 'Emi outstanding updated successfully!');
            }else{
                return redirect()->route('admin.loan.emioutstanding.update')->with('alert', 'An error occured!');
            }
        }catch(\Exception $ex){
            
            return back()->with('alert', $ex->getMessage());
        }
        

    }
    public function accountDetails(Request $request)
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
					return \Response::json(['view' => view('templates.admin.loan.emi_update_cron.accountdetail', ['loanData' => $data, 'groupList' => $groupList])->render(), 'msg_type' => 'success', 'id' => $id, 'ecs_type' => $data->ecs_type, 'ecs_ref_no' => ($ecs_ref_no ?? ''), 'loan_type' => $data->loans->loan_type, 'approve_date' =>  $data->approve_date ,'emi_due_date' =>  $data->emi_due_date,'emi_amount' =>  $data->emi_amount]);
				}
			} else {
				if ($data->status == 3) {
					return \Response::json(['view' => 'You can not change details of clear loan plans!', 'msg_type' => 'error_clear']);
				} else {
					$id = $data->id;
					return \Response::json(['view' => view('templates.admin.loan.emi_update_cron.accountdetail', ['loanData' => $data, 'groupList' => $groupList])->render(), 'msg_type' => 'success', 'id' => $id, 'ecs_type' => $data->ecs_type, 'ecs_ref_no' => ($ecs_ref_no ?? ''), 'loan_type' => $data->loans->loan_type, 'approve_date' =>  $data->approve_date ,'emi_due_date' =>  $data->emi_due_date,'emi_amount' =>  $data->emi_amount]);
				}
			}
		} else {
			return \Response::json(['view' => 'No data found', 'msg_type' => 'error']);
		}
	}
   

}
