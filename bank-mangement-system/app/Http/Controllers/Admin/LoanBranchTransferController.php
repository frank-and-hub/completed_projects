<?php
namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Input;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Validator;
use App\Models\User;
use App\Models\Loans;
use App\Models\Memberloans;
use App\Models\MemberTransaction;
use App\Models\BranchDaybook;
use App\Models\AllTransaction;
use App\Models\Transcation;
use App\Models\Profits;
use App\Models\Member;
use App\Models\Loanapplicantdetails;
use App\Models\Loanscoapplicantdetails;
use App\Models\Loansguarantordetails;
use App\Models\Loanotherdocs;
use App\Models\Loaninvestmentmembers;
use App\Models\Files;
use App\Models\SavingAccount;
use App\Models\SavingAccountTranscation;
use App\Models\TransactionReferences;
use App\Models\Grouploans;
use App\Models\Companies;
use App\Models\LoanDayBooks;
use App\Models\SamraddhCheque;
use App\Models\AccountHeads;
use App\Models\ReceivedChequePayment;
use App\Models\ReceivedCheque;
use App\Models\SamraddhBank;
use App\Models\Daybook;
use App\Models\SamraddhChequeIssue;
use App\Models\SamraddhBankClosing;
use App\Models\AssociateCommission;
use App\Models\SamraddhBankAccount;
use App\Models\SamraddhBankDaybook;
use App\Models\BranchCash;
use App\Models\AllHeadTransaction;
use App\Models\AccountBranchTransfer;
use App\Http\Controllers\Admin\LoanSettings\LoanChargeController;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Response;
use App\Http\Controllers\Admin\CommanTransactionsController;
use URL;
use DB;
use Session;
use DateTime;
use App\Http\Traits\EmiDatesTraits;
use App\Models\LoanEmisNew;
use App\Models\CommissionEntryLoan;
use App\Http\Traits\Oustanding_amount_trait;
use App\Http\Traits\getRecordUsingDayBookRefId;
use App\Services\Sms;
use App\Models\LoanTenure;
use App\Models\CollectorAccount;
use Illuminate\Validation\Rule;
use App\Events\UserActivity;

class LoanBranchTransferController extends Controller
{
    use Oustanding_amount_trait, getRecordUsingDayBookRefId;
    /**
     * Instantiate a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }


    /*********************  loan Branch Transfer start ******************************/
    public function loanbranchtransfer()
    {
        if (check_my_permission(Auth::user()->id, "284") != "1") {
            return redirect()->route('admin.dashboard');
        }
        $data['title'] = 'Loan | Account Branch Transfer';
        return view('templates.admin.loan.loanbranchtransfer', $data);
    }
    public function getLoanbrtansferData(Request $request)
    {
        $branch = \App\Models\Branch::where('status', 1);
        if (Auth::user()->branch_id > 0) {
            $branch = $branch->where('id', Auth::user()->branch_id);
        }
        $branch = $branch->get();
        $groupList = '';
        $data = Memberloans::with([
            'loanMember' => function ($q) {
                $q->with('memberCompany')->select('id', 'member_id', 'first_name', 'last_name');
            },
            'loanMemberAssociate' => function ($q) {
                $q->select('id', 'associate_no', 'first_name', 'last_name');
            },
            'loanBranch' => function ($q) {
                $q->select('id', 'branch_code', 'name');
            }
        ])->with([
                    'loanMemberCompany' => function ($q) {
                        $q->select('id', 'member_id');
                    }
                ])->where('account_number', $request->code)
            ->first();

        if (empty($data)) {

            $data = Grouploans::with([
                'loanMember' => function ($q) {
                    $q->with('memberCompany')->select('id', 'member_id', 'first_name', 'last_name');
                },
                'loanMemberAssociate' => function ($q) {
                    $q->select('id', 'associate_no', 'first_name', 'last_name');
                },
                'loanBranch' => function ($q) {
                    $q->select('id', 'branch_code', 'name');
                }
            ])->with([
                        'loanMemberCompany' => function ($q) {
                            $q->select('id', 'member_id');
                        }
                    ])->where('account_number', $request->code)->first();

            // dd($data['loanMember']['memberCompany']['member_id']);
            if ($data) {
                $groupList = Grouploans::with([
                    'loanMember' => function ($q) {
                        $q->select('id', 'member_id', 'first_name', 'last_name');
                    }
                ])->with([
                            'loanMemberCompany' => function ($q) {
                                $q->select('id', 'member_id');
                            }
                        ])->where('group_loan_common_id', $data->group_loan_common_id)->where('id', '!=', $data->id)->get();
                $clearCount = Grouploans::where('group_loan_common_id', $data->group_loan_common_id)->where('status', 3)->count();
            }
        }
        $type = $request->type;
        if ($data) {
            if (in_array($data->loan_type, getGroupLoanTypes())) {
                if ($clearCount > 0) {
                    return \Response::json(['view' => 'No data found', 'msg_type' => 'error_cleargoup']);
                } else {
                    $id = $data->id;
                    return \Response::json(['view' => view('templates.admin.loan.partials.loanbranchtransfer_detail', ['loanData' => $data, 'branch' => $branch, 'groupList' => $groupList])->render(), 'msg_type' => 'success', 'id' => $id]);
                }
            } else {
                if ($data->status == 3) {
                    return \Response::json(['view' => 'No data found', 'msg_type' => 'error_clear']);
                } else {
                    $id = $data->id;
                    return \Response::json(['view' => view('templates.admin.loan.partials.loanbranchtransfer_detail', ['loanData' => $data, 'branch' => $branch, 'groupList' => $groupList])->render(), 'msg_type' => 'success', 'id' => $id]);
                }
            }
        } else {
            return \Response::json(['view' => 'No data found', 'msg_type' => 'error']);
        }
    }
    public function loanbranchtransfersave(Request $request)
    {
        if (empty($request->loan_id) || empty($request->branch_id)) {
            return back()->with('errors', 'Enter account number');
        }
        $id = $request->loan_id;
        $created_by_id = Auth::user()->id;
        $globaldate = $request['created_at'];
        $created_at = date("Y-m-d H:i:s", strtotime(convertDate($globaldate)));

        if (in_array($request->loan_type, getGroupLoanTypes())) {
            $loanDetail = Grouploans::where('id', $id)->first(['id', 'group_loan_common_id', 'member_loan_id']);
            $groupList = Grouploans::where('group_loan_common_id', $loanDetail->group_loan_common_id)->where('id', '!=', $loanDetail->id)->get();
            foreach ($groupList as $glist) {
                //print_r($glist->id);die;
                $LoanBranchTransferGroup = new AccountBranchTransfer;
                $LoanBranchTransferGroup->type = 3;
                $LoanBranchTransferGroup->new_branch_id = $request['branch_id'];
                $LoanBranchTransferGroup->type_id = $glist->id;
                $LoanBranchTransferGroup->old_branch_id = $glist->branch_id;
                $LoanBranchTransferGroup->created_by = 1;
                $LoanBranchTransferGroup->created_by_id = $created_by_id;
                $LoanBranchTransferGroup->created_at = $created_at;
                $LoanBranchTransferGroup->updated_at = $created_at;
                $LoanBranchTransferGroup->save();
                $groupLoan = \App\Models\Grouploans::where('id', $glist->id)->update(['branch_id' => $request->branch_id]);
            }
            $Membernew = \App\Models\Grouploans::where('id', $loanDetail->id)->update(['branch_id' => $request->branch_id]);
            $Membernewgroup = \App\Models\Memberloans::where('id', $loanDetail->member_loan_id)->update(['branch_id' => $request->branch_id]);
            $LoanBranchTransfer1 = new AccountBranchTransfer;
            $LoanBranchTransfer1->type = 3;
            $LoanBranchTransfer1->new_branch_id = $request['branch_id'];
            $LoanBranchTransfer1->type_id = $loanDetail->member_loan_id;
            $LoanBranchTransfer1->old_branch_id = $request['old_branch_id'];
            $LoanBranchTransfer1->created_by = 1;
            $LoanBranchTransfer1->created_by_id = $created_by_id;
            $LoanBranchTransfer1->created_at = $created_at;
            $LoanBranchTransfer1->updated_at = $created_at;
            $LoanBranchTransfer1->save();
        } else {
            $Membernew = \App\Models\Memberloans::where('id', $id)->update(['branch_id' => $request->branch_id]);
        }
        $LoanBranchTransfer = new AccountBranchTransfer;
        $LoanBranchTransfer->type = 3;
        $LoanBranchTransfer->new_branch_id = $request['branch_id'];
        $LoanBranchTransfer->type_id = $id;
        $LoanBranchTransfer->old_branch_id = $request['old_branch_id'];
        $LoanBranchTransfer->created_by = 1;
        $LoanBranchTransfer->created_by_id = $created_by_id;
        $LoanBranchTransfer->created_at = $created_at;
        $LoanBranchTransfer->updated_at = $created_at;
        $LoanBranchTransfer->save();
        return back()->with('success', 'Loan branch updated successfully');
        /*		DB::commit();
               } catch (\Exception $ex) {
                   DB::rollback();
                   return back()->with('errors', $ex->getMessage());
               }*/
    }
    public function loanbranchtransferlog()
    {
        if (check_my_permission(Auth::user()->id, "285") != "1") {
            return redirect()->route('admin.dashboard');
        }
        $data['title'] = 'Loan | Account Branch Transfer Log Detail';
        return view('templates.admin.log.loanlogbranchtransfer', $data);
    }
    public function getLoanbrtansferLogData(Request $request)
    {
        $accno = $request->code;
        //print_r($accno);die;
        $loanDetail = Memberloans::where('account_number', $accno)->first('id');
        if (empty($loanDetail)) {
            $loanDetail = Grouploans::where('account_number', $accno)->first('id');
        }
        if ($loanDetail) {
            $data = AccountBranchTransfer::where('type', 3)->where('type_id', $loanDetail['id'])->orderBy('id', 'DESC')->get();
            if ($data) {
                return \Response::json(['view' => view('templates.admin.log.partials.loanlogbranchtransfer_detail', ['loanData' => $data])->render(), 'msg_type' => 'success']);
            }
        } else {
            return \Response::json(['view' => 'No data found', 'msg_type' => 'error']);
        }
    }
    /*********************  loan Branchghjgh Transfer end ******************************/
    /**
     * Get Loan Category form loans table
     * @params loanType
     */
    public function getloanCategory(Request $request)
    {
        $loanCategory = Loans::where('loan_type', $request->loanType)->get();
        return response()->json(['loanCategory' => $loanCategory]);
    }



}
?>
