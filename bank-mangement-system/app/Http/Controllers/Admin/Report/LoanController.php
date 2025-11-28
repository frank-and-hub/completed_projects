<?php
namespace App\Http\Controllers\Admin\Report;
use Illuminate\Support\Facades\Cache;
use App\Models\MemberIdProof;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use App\Models\Branch;
use Yajra\DataTables\DataTables;
use Carbon\Carbon;
use Session;
use Image;
use Redirect;
use App\Models\Memberloans;
use App\Models\LoanDayBooks;
use App\Models\Grouploans;
use App\Models\Loans;
use URL;
use DB;
use App\Services\Email;
use App\Services\Sms;
use Illuminate\Support\Facades\Schema;
use App\Http\Traits\EmiDatesTraits;
/*
|---------------------------------------------------------------------------
| Admin Panel -- Member Management MemberController
|--------------------------------------------------------------------------
|
| This controller handles members all functionlity.
*/
class LoanController extends Controller
{
    use EmiDatesTraits;
    /**
     * Create a new controller instance.
     * @return void
     */
    public function __construct()
    {
        // check user login or not
        $this->middleware('auth');
    }
    public function loanapplication()
    {
        if (check_my_permission(Auth::user()->id, "287") != "1") {
            return redirect()->route('admin.dashboard');
        }
        $data['title'] = 'Report | Loans Application Report';
        $data['branch'] = Branch::select('id', 'name')->where('status', 1)->get();
        return view('templates.admin.report.loan.loan_application', $data);
    }
    public function planByLoanCategory(Request $request)
    {
        $loan = \App\Models\Loans::has('company')->select('id', 'name');
        if ($request->category) {
            $loan = $loan->where('loan_type', $request->category);
        }
        if ($request->company_id) {
            $loan = $loan->where('company_id', $request->company_id);
        }
        $data = $loan->get();
        $return_array = compact('data');
        return json_encode($return_array);
    }
    public function loanApplicationList(Request $request)
    {
        if ($request->ajax()) {
            $arrFormData = array();
            if (!empty($_POST['searchform'])) {
                foreach ($_POST['searchform'] as $frm_data) {
                    $arrFormData[$frm_data['name']] = $frm_data['value'];
                }
            }
            // pd($arrFormData['is_search']);
            if (isset($arrFormData['is_search']) && $arrFormData['is_search'] == 'yes') {
                if ($arrFormData['loan_category'] == 'G') {
                    $data = Grouploans::has('company')->select('customer_id', 'id', 'branch_id', 'created_at', 'loan_type', 'status', 'account_number', 'applicant_id', 'member_id', 'group_loan_common_id', 'deposite_amount', 'amount', 'approve_date', 'emi_period', 'emi_option', 'due_amount', 'ROI', 'emi_amount', 'credit_amount', 'associate_member_id', 'approved_date', 'company_id', 'is_deleted')
                        ->with([
                            'loanMember:id,member_id,first_name,last_name,mobile_no,address,associate_code',
                            'loanMemberAssociate:id,associate_no,first_name,last_name',
                            'company:id,name', 'loanBranch:id,name,sector',
                            'loanMemberCompany:id,member_id'])
                        ->with(['loan' => function ($q) {
                            $q->select('id', 'name', 'loan_type')->where('loan_type', '=', 'G');
                        }])
                        ->whereIn('status', [0, 1, 2])
                    ;
                } else {
                    $data = Memberloans::has('company')->select('customer_id', 'id', 'branch_id', 'associate_member_id', 'status', 'loan_type', 'applicant_id', 'group_loan_common_id', 'account_number', 'amount', 'deposite_amount', 'approve_date', 'approved_date', 'emi_amount', 'emi_period', 'emi_option', 'created_at', 'credit_amount', 'ROI', 'due_amount', 'company_id', 'is_deleted')
                        ->with([
                            'loanMember:id,member_id,first_name,last_name,mobile_no,address,associate_code',
                            'loanMemberAssociate:id,associate_no,first_name,last_name',
                            'loanBranch:id,name,sector',
                            'loanMemberCompany:id,member_id',
                            'company:id,name'])
                        ->with(['loan' => function ($q) {
                            $q->select('id', 'name', 'loan_type')->where('loan_type', '=', 'L');
                        }])
                        ->whereIn('status', [0, 1, 2]);
                }
                if (Auth::user()->branch_id > 0) {
                    $id = Auth::user()->branch_id;
                    $data = $data->where('branch_id', '=', $id);
                }
                if ($arrFormData['application_start_date'] != '') {
                    $startDate = date("Y-m-d", strtotime(convertDate($arrFormData['application_start_date'])));
                    if ($arrFormData['application_end_date'] != '') {
                        $endDate = date("Y-m-d ", strtotime(convertDate($arrFormData['application_end_date'])));
                    } else {
                        $endDate = '';
                    }
                    $data = $data->whereBetween(DB::Raw('date(created_at)'), [$startDate, $endDate]);
                }
                if ($arrFormData['plan'] != '') {
                    $plan = $arrFormData['plan'];
                    $data = $data->where('loan_type', '=', $plan);
                    //$grpLoan=$grpLoan->where('loan_type','=',$plan);
                }
                if (isset($arrFormData['branch_id']) && $arrFormData['branch_id'] != '' && $arrFormData['branch_id'] > '0') {
                    $branch_id = $arrFormData['branch_id'];
                    $data = $data->where('branch_id', '=', $branch_id);
                    //$grpLoan=$grpLoan->where('branch_id','=',$branch_id);
                }
                if ($arrFormData['status'] != '') {
                    $status = $arrFormData['status'];
                    $data = $data->where('status', '=', $status);
                    //$grpLoan=$grpLoan->where('status','=',$status);
                }
                if ($arrFormData['company_id'] != '' && $arrFormData['company_id'] > '0') {
                    $company_id = $arrFormData['company_id'];
                    $data = $data->where('company_id', $company_id);
                }
                if ($arrFormData['member_name'] != '') {
                    $member_name = $arrFormData['member_name'];
                    $data = $data->whereHas('loanMember', function ($query) use ($member_name) {
                        $query->where('members.first_name', 'LIKE', '%' . $member_name . '%')
                            ->orWhere('members.last_name', 'LIKE', '%' . $member_name . '%')
                            ->orWhere(DB::raw('concat(members.first_name," ",members.last_name)'), 'LIKE', "%$member_name%");
                    });
                }
                if ($arrFormData['member_id'] != '') {
                    $member_id = $arrFormData['member_id'];
                    $data = $data->whereHas('loanMemberCompany', function ($query) use ($member_id) {
                        $query->where('member_companies.member_id', 'LIKE', '%' . $member_id . '%');
                    });
                }
                $count = $data->where('is_deleted', 0)->orderby('id', 'DESC')->count();
                $DataArrayExport = $data->where('is_deleted', 0)->orderby('id', 'DESC')->get()->toArray();
                $DataArray = $data->where('is_deleted', 0)->orderby('id', 'DESC')->offset($_POST['start'])->limit($_POST['length'])->get();
                $sno = $_POST['start'];
                $rowReturn = array();
                $totalCount = $count;
                foreach ($DataArray as $row) {
                    $sno++;
                    $val['DT_RowIndex'] = $sno;
                    $val['company_name'] = $row['company'] ? $row['company']['name'] : 'N/A';
                    $val['branch'] = $row['loanBranch'] ? $row['loanBranch']['name'] : 'N/A';
                    $val['customer_id'] = $row['loanMember'] ? $row['loanMember']->member_id : 'N/A';
                    $val['member_id'] = $row['loanMemberCompany'] ? $row['loanMemberCompany']->member_id : 'N/A';
                    $val['member_name'] = $row['loanMember'] ? $row['loanMember']->first_name . ' ' . $row['loanMember']->last_name : 'N/A';
                    $val['created_at'] = date("d/m/Y", strtotime(convertDate($row['created_at'])));
                    $val['loan_type'] = $row['loan']['name'] ?? '';
                    $val['no_of_installement'] = isset($row['emi_period']) ? $row['emi_period'] : 'N/A';
                    $val['loan_mode'] = 'N/A';
                    if (isset($row['emi_option'])) {
                        if ($row['emi_option'] == 1) {
                            $val['loan_mode'] = 'Months';
                        } elseif ($row['emi_option'] == 2) {
                            $val['loan_mode'] = 'Weeks';
                        } elseif ($row['emi_option'] == 3) {
                            $val['loan_mode'] = 'Daily';
                        }
                    }
                    $val['sanctioned_amount'] = isset($row['amount']) ? $row['amount'] . ' &#8377' : 'N/A';
                    if ($row['status'] == 0) {
                        $val['status'] = 'Pending';
                    } elseif ($row['status'] == 1) {
                        $val['status'] = 'Approved';
                    } elseif ($row['status'] == 2) {
                        $val['status'] = 'Rejected';
                    } else {
                        $val['status'] = 'N/A';
                    }
                    $val['account_number'] = 'N/A';
                    if ($row['status'] == 1) {
                        $val['account_number'] = $row['account_number'];
                    }
                    $val['approved_date'] = 'N/A';
                    if ($row['status'] == 1 && $row['approved_date'] != '') {
                        $val['approved_date'] = date("d/m/Y", strtotime(convertDate($row['approved_date'])));
                    }
                    $val['associate_code'] = 'N/A';
                    if ($row['loanMemberAssociate']) {
                        $val['associate_code'] = $row['loanMemberAssociate']->associate_no;
                    }
                    $val['associate_name'] = 'N/A';
                    if ($row['loanMemberAssociate']) {
                        $val['associate_name'] = $row['loanMemberAssociate']->first_name . ' ' . $row['children']->last_name;
                    }
                    $rowReturn[] = $val;
                }
                Cache::put('loanApplicanttList', $DataArrayExport);
                Cache::put('loanApplicanttListcount', $totalCount);
                $output = array("draw" => $_POST['draw'], "recordsTotal" => $totalCount, "recordsFiltered" => $count, "data" => $rowReturn);
            } else {
                $output = array(
                    "draw" => 0,
                    "recordsTotal" => 0,
                    "recordsFiltered" => 0,
                    "data" => 0,
                );
            }
            return json_encode($output);
        }
    }
    public function loanissued()
    {
        if (check_my_permission(Auth::user()->id, "288") != "1") {
            return redirect()->route('admin.dashboard');
        }
        $data['title'] = 'Report | Loan Issued Report';
        $data['branch'] = Branch::select('id', 'name')->where('status', 1)->get();
        return view('templates.admin.report.loan.loan_issued', $data);
    }
    public function loanIssueList(Request $request)
    {
        if ($request->ajax()) {
            $arrFormData = array(); {
                if (!empty($_POST['searchform'])) {
                    foreach ($_POST['searchform'] as $frm_data) {
                        $arrFormData[$frm_data['name']] = $frm_data['value'];
                    }
                }
                if ($arrFormData['loan_category'] === 'G') {
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
                            $q->select('id', 'name', 'sector', 'state_id');
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
                            'loanBranch:id,name,sector,state_id',
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
                if (Auth::user()->branch_id > 0) {
                    $id = Auth::user()->branch_id;
                    $loan = $data->where('branch_id', '=', $id);
                    $grpLoan = $data->where('branch_id', '=', $id);
                }
                if (isset($arrFormData['is_search']) && $arrFormData['is_search'] == 'yes') {
                    if ($arrFormData['loanpayment_start_date'] != '') {
                        $startDate = date("Y-m-d", strtotime(convertDate($arrFormData['loanpayment_start_date'])));
                        if ($arrFormData['loanpayment_end_date'] != '') {
                            $endDate = date("Y-m-d ", strtotime(convertDate($arrFormData['loanpayment_end_date'])));
                        } else {
                            $endDate = '';
                        }
                        $data = $data->whereBetween('approve_date', [$startDate, $endDate]);
                    }
                    if ($arrFormData['plan'] != '') {
                        $plan = $arrFormData['plan'];
                        $data = $data->where('loan_type', '=', $plan);
                    }
                    if ($arrFormData['loan_status'] != '') {
                        $loan_status = $arrFormData['loan_status'];
                        //dd($loan_status);
                        $data = $data->where('status', '=', $loan_status);
                    }
                    if (isset($arrFormData['branch_id']) && $arrFormData['branch_id'] != '' && $arrFormData['branch_id'] > 0) {
                        $branch_id = $arrFormData['branch_id'];
                        $data = $data->where('branch_id', '=', $branch_id);
                    }
                    if ($arrFormData['member_name'] != '') {
                        $member_name = $arrFormData['member_name'];
                        $data = $data->whereHas('loanMember', function ($query) use ($member_name) {
                            $query->where('members.first_name', 'LIKE', '%' . $member_name . '%')
                                ->orWhere('members.last_name', 'LIKE', '%' . $member_name . '%')
                                ->orWhere(DB::raw('concat(members.first_name," ",members.last_name)'), 'LIKE', "%$member_name%");
                        });
                    }
                    if ($arrFormData['member_id'] != '') {
                        $member_id = $arrFormData['member_id'];
                        $data = $data->whereHas('loanMemberCompany', function ($query) use ($member_id) {
                            $query->where('member_companies.member_id', 'LIKE', '%' . $member_id . '%');
                        });
                    }
                    if ($arrFormData['account_number'] != '') {
                        $account_number = $arrFormData['account_number'];
                        $data = $data->where('account_number', '=', $account_number);
                    }
                    if ($arrFormData['company_id'] != '' && $arrFormData['company_id'] > 0) {
                        $company_id = $arrFormData['company_id'];
                        $data = $data->where('company_id', $company_id);
                    }
                } else {
                    $startDate = date('Y-m-d', strtotime($request->adm_report_currentdate));
                    $endDate = date('Y-m-d', strtotime($request->adm_report_currentdate));
                }
                $count = $data->orderby('id', 'DESC')->count();
                $DataArray = $data->orderby('id', 'DESC')->offset($_POST['start'])->limit($_POST['length'])->get();
                $sno = $_POST['start'];
                $rowReturn = array();
                $totalCount = $count;
                foreach ($DataArray as $row) {
                    $outstandingAmount = isset($row['getOutstanding']->out_standing_amount)
                        ? ($row['getOutstanding']->out_standing_amount > 0 ? $row['getOutstanding']->out_standing_amount : 0)
                        : $row->amount;
                    $globaldate = checkMonthAvailability(date('d'), date('m'), date('Y'), (33));
                    $LoanCreatedDate = date('Y-m-d', strtotime($row->approve_date));
                    $LoanCreatedYear = date('Y', strtotime($row->approve_date));
                    $LoanCreatedMonth = date('m', strtotime($row->approve_date));
                    $LoanCreateDate = date('d', strtotime($row->approve_date));
                    $currentDate = date('Y-m-d', strtotime($globaldate));
                    $CurrentDate = date('d', strtotime($globaldate));
                    $CurrentDateYear = date('Y', strtotime($globaldate));
                    $CurrentDateMonth = date('m', strtotime($globaldate));
                    $daysDiff = today()->diffInDays($LoanCreatedDate);
                    $nextEmiDates = $this->nextEmiDatesDaily($daysDiff, $LoanCreatedDate);
                    if (array_key_exists($CurrentDate . '_' . $CurrentDateMonth . '_' . $CurrentDateYear, $nextEmiDates)) {
                        $roiAmount = 0;
                        $principalAmount = 0;
                        $deposit = 0;
                        $lastOutstanding = \App\Models\LoanEmisNew::where('loan_id', $row->id)->where('is_deleted', '0')->where('loan_type', $row->loan_type)->orderBy('id', 'desc')->first();
                        if (isset($lastOutstanding->out_standing_amount)) {
                            $roiAmount = ((($row->ROI) / 365) * $lastOutstanding->out_standing_amount) / 100;
                            $deposit = 0;
                            $principalAmount = $deposit - $roiAmount;
                        } else {
                            $roiAmount = ((($row->ROI) / 365) * $row->amount) / 100;
                            $principalAmount = $deposit - $roiAmount;
                        }
                    }
                    $sno++;
                    $val['DT_RowIndex'] = $sno;
                    $val['name'] = 'N/A';
                    if ($row['company']) {
                        $val['name'] = $row['company']->name;
                    }
                    $val['branch'] = 'N/A';
                    if (isset($row['loanBranch'])) {
                        $val['branch'] = $row['loanBranch']['name']; //customGetBranchDetail($row->branch_id)->name;
                    }
                    $val['customer_id'] = 'N/A';
                    if ($row['loanMember']) {
                        $val['customer_id'] = $row['loanMember']->member_id;
                    }
                    $val['member_id'] = 'N/A';
                    if ($row['loanMemberCompany']) {
                        $val['member_id'] = $row['loanMemberCompany']->member_id;
                    }
                    $val['member_name'] = 'N/A';
                    if ($row['loanMember']) {
                        $val['member_name'] = $row['loanMember']->first_name . ' ' . $row['loanMember']->last_name;
                    }
                    $val['account_number'] = $row['account_number'];
                    $val['application_date'] = date("d/m/Y", strtotime(convertDate($row['created_at'])));
                    if ($row['approve_date'] && $row['approve_date'] != '') {
                        $val['loan_issue_date'] = date("d/m/Y", strtotime(convertDate($row['approve_date'])));
                    } else {
                        $val['loan_issue_date'] = 'N/A';
                    }
                    if (isset($row['loan'])) {
                        $val['loan_type'] = $row['loan']['name']; //customGetBranchDetail($row->branch_id)->name;
                    } else {
                        $val['loan_type'] = 'N/A';
                    }
                    if (isset($row['emi_period'])) {
                        $val['tenure'] = $row['emi_period'];
                    } else {
                        $val['tenure'] = 'N/A';
                    }
                    if (isset($row['emi_option'])) {
                        if ($row['emi_option'] == 1) {
                            $val['loan_mode'] = 'Months';
                        } elseif ($row['emi_option'] == 2) {
                            $val['loan_mode'] = 'Weeks';
                        } elseif ($row['emi_option'] == 3) {
                            $val['loan_mode'] = 'Daily';
                        }
                    } else {
                        $val['loan_mode'] = 'N/A';
                    }
                    if (isset($row['amount'])) {
                        $val['loan_amount'] = $row['amount'] . ' &#8377';
                    } else {
                        $val['loan_amount'] = 'N/A';
                    }
                    if (isset($row['transfer_amount'])) {
                        $val['transfer_amount'] = $row['transfer_amount'] . ' &#8377';
                    } else {
                        $val['transfer_amount'] = 'N/A';
                    }
                    $amount = LoanDayBooks::where('loan_type', $row['loan_type'])->where('loan_sub_type', '!=', 2)->where('account_number', $row['account_number'])->where('is_deleted', 0)->sum('deposit');
                    $val['total_recovery_amount'] = $amount . ' &#8377';
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
                    // if (isset($row['due_amount'])) {
                    //     $closingAmount = round($row['due_amount'] + $closingAmountROI);
                    // } else {
                    //     $closingAmount = 0;
                    // }
                    $val['due_amount'] = number_format((float) $outstandingAmount, 2, '.', '') . ' &#8377';
                    // $val['due_amount'] =number_format((float)$outstandingAmount, 2, '.', '').' &#8377';
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
                        $val['closing_date'] = date("d/m/Y", strtotime($row['clear_date']));
                    } else {
                        $val['closing_date'] = 'N/A';
                    }
                    $payment_date = LoanDayBooks::where('loan_type', $row['loan_type'])->where('account_number', $row['account_number'])->where('is_deleted', 0)->orderBy('created_at', 'desc')->first();
                    // if(isset($payment_date->id))
                    // {
                    //     dd($payment_date);
                    // }
                    if (isset($payment_date->id)) {
                        $val['last_date_of_emi'] = date("d/m/Y", strtotime($payment_date['payment_date']));
                    } else {
                        $val['last_date_of_emi'] = 'N/A';
                    }
                    if (isset($row['loanMemberAssociate']->associate_no)) {
                        $val['associate_code'] = $row['loanMemberAssociate']->associate_no;
                    } else {
                        $val['associate_code'] = 'N/A';
                    }
                    if ($row['loan_type'] == 3) {
                        if (isset($row['associate_member_id'])) {
                            $val['associate_name'] = $row['children']->first_name . ' ' . $row['children']->last_name;
                        } else {
                            $val['associate_name'] = 'N/A';
                        }
                    } else {
                        if (isset($row['associate_member_id'])) {
                            $val['associate_name'] = $row['children']->first_name . ' ' . $row['children']->last_name;
                        } else {
                            $val['associate_name'] = 'N/A';
                        }
                    }
                    $rowReturn[] = $val;
                }
                $output = array("draw" => $_POST['draw'], "recordsTotal" => $totalCount, "recordsFiltered" => $count, "data" => $rowReturn);
                return json_encode($output);
            }
        }
    }
    public function loanclosed()
    {
        if (check_my_permission(Auth::user()->id, "289") != "1") {
            return redirect()->route('admin.dashboard');
        }
        $data['title'] = 'Report | Loan Closed Report';
        $data['branch'] = Branch::select('id', 'name')->where('status', 1)->get();
        return view('templates.admin.report.loan.loan_closed', $data);
    }
    public function loanClosedList(Request $request)
    {
        if ($request->ajax()) {
            $arrFormData = array(); {
                if (!empty($_POST['searchform'])) {
                    foreach ($_POST['searchform'] as $frm_data) {
                        $arrFormData[$frm_data['name']] = $frm_data['value'];
                    }
                }
                if ($arrFormData['loan_category'] == 'G') {
                    $data = Grouploans::has('company')->select('company_id', 'customer_id', 'id', 'branch_id', 'created_at', 'loan_type', 'status', 'account_number', 'member_loan_id', 'applicant_id', 'member_id', 'group_loan_common_id', 'deposite_amount', 'amount', 'approve_date', 'emi_period', 'emi_option', 'due_amount', 'ROI', 'emi_amount', 'credit_amount', 'associate_member_id', 'closing_date')
                    ->with(['loan' => function ($q) {
                        $q->has('company')->select('id', 'name', 'loan_type');
                    }])
                        ->with([
                            'loanMember:id,member_id,first_name,last_name,mobile_no,address,associate_code',
                            'loanMemberAssociate:id,associate_no,first_name,last_name',
                            'loanBranch:id,name,sector',
                            'company:id,name',
                            'member:id,member_id,first_name,last_name',
                            'loanMemberCompany:id,member_id',
                            'children'
                        ])
                        ->whereIn('status', [0, 1, 2])
                    ;
                } else {
                    $data = Memberloans::has('company')->select('company_id', 'customer_id', 'id', 'branch_id', 'associate_member_id', 'status', 'loan_type', 'applicant_id', 'group_loan_common_id', 'account_number', 'amount', 'deposite_amount', 'approve_date', 'approved_date', 'emi_amount', 'emi_period', 'emi_option', 'created_at', 'credit_amount', 'ROI', 'due_amount', 'closing_date')
                        ->with(['loan' => function ($q) {
                            $q->has('company')->select('id', 'name', 'loan_type')->where('loan_type', '!=', '3');
                        }])
                        ->with([
                            'loanMember:id,member_id,first_name,last_name,mobile_no,address,associate_code',
                            'loanMemberAssociate:id,associate_no,first_name,last_name',
                            'company:id,name',
                            'loanBranch:id,name,sector',
                            'member:id,member_id,first_name,last_name',
                            'loanMemberCompany:id,member_id',
                            'children:id,first_name,last_name'
                        ])
                        ->whereHas('loan', function ($query) {
                            $query->where('loan_type', 'L');
                        })
                        ->whereIn('status', [0, 1, 2])
                    ;
                }
                if (isset($arrFormData['is_search']) && $arrFormData['is_search'] == 'yes') {
                    //dd($request->all());
                    if ($arrFormData['closure_start_date'] != '') {
                        $startDate = date("Y-m-d", strtotime(convertDate($arrFormData['closure_start_date'])));
                        if ($arrFormData['closure_end_date'] != '') {
                            $endDate = date("Y-m-d ", strtotime(convertDate($arrFormData['closure_end_date'])));
                        } else {
                            $endDate = '';
                        }
                        $data = $data->whereBetween('clear_date', [$startDate, $endDate]);
                    }
                    // pd($endDate);
                    if ($arrFormData['company_id'] != '' && $arrFormData['company_id'] > 0 ) {
                        $company_id = $arrFormData['company_id'];
                        $data = $data->where('company_id', $company_id);
                    }
                    if ($arrFormData['plan'] != '') {
                        $plan = $arrFormData['plan'];
                        $data = $data->where('loan_type', '=', $plan);
                    }
                    if (isset($arrFormData['branch_id']) && $arrFormData['branch_id'] != '' && $arrFormData['branch_id'] > '0') {
                        $branch_id = $arrFormData['branch_id'];
                        $data = $data->where('branch_id', '=', $branch_id);
                    }
                    if ($arrFormData['member_name'] != '') {
                        $member_name = $arrFormData['member_name'];
                        $data = $data->whereHas('loanMember', function ($query) use ($member_name) {
                            $query->where('first_name', 'LIKE', '%' . $member_name . '%')
                                ->orWhere('last_name', 'LIKE', '%' . $member_name . '%')
                                ->orWhere(DB::raw('concat(first_name," ",last_name)'), 'LIKE', "%$member_name%");
                        });
                    }
                    if ($arrFormData['member_id'] != '') {
                        $member_id = $arrFormData['member_id'];
                        $data = $data->whereHas('loanMemberCompany', function ($query) use ($member_id) {
                            $query->where('member_id', 'LIKE', '%' . $member_id . '%');
                        });
                    }
                    if ($arrFormData['account_number'] != '') {
                        $account_number = $arrFormData['account_number'];
                        $data = $data->where('account_number', '=', $account_number);
                    }
                } else {
                    $startDate = date('Y-m-d', strtotime($request->adm_report_currentdate));
                    $endDate = date('Y-m-d', strtotime($request->adm_report_currentdate));
                }
                $count = $data->where('is_deleted', 0)->orderby('id', 'DESC')->count();
                $dataExportClone = $data->where('is_deleted', 0)->orderby('id', 'DESC')->get()->toArray();
                $DataArray = $data->where('is_deleted', 0)->orderby('id', 'DESC')->offset($_POST['start'])->limit($_POST['length'])->get();
                $sno = $_POST['start'];
                $rowReturn = array();
                $totalCount = $count;
                foreach ($DataArray as $row) {
                    $sno++;
                    $val['DT_RowIndex'] = $sno;
                    $val['company_name'] = $row['company'] ? $row['company']['name'] : 'N/A';
                    $val['branch'] = isset($row['loanBranch']) ? $row['loanBranch']['name'] : 'N/A'; //customGetBranchDetail($row->branch_id)->name;
                    $val['customer_id'] = isset($row['member']) ? $row['member']->member_id : 'N/A';
                    $val['member_id'] = $row['loanMemberCompany'] ? $row['loanMemberCompany']->member_id : 'N/A';
                    $val['member_name'] = $row['loanMember'] ? $row['loanMember']->first_name . ' ' . $row['loanMember']->last_name : 'N/A';
                    $val['account_number'] = $row['account_number'];
                    $val['loan_issue_date'] = 'N/A';
                    if (isset($row['approve_date'])) {
                        $val['loan_issue_date'] = date("d/m/Y", strtotime(convertDate($row->approve_date)));
                    }
                    $val['closing_date'] = 'N/A';
                    if (isset($row['closing_date'])) {
                        $val['closing_date'] = date("d/m/Y", strtotime($row->closing_date));
                    }
                    $val['loan_type'] = $row['loan']['name'];
                    $val['tenure'] = 'N/A';
                    if (isset($row['emi_period'])) {
                        $val['tenure'] = $row['emi_period'];
                    }
                    $val['loan_mode'] = 'N/A';
                    if (isset($row['emi_option'])) {
                        if ($row['emi_option'] == 1) {
                            $val['loan_mode'] = 'Months';
                        } elseif ($row['emi_option'] == 2) {
                            $val['loan_mode'] = 'Weeks';
                        } elseif ($row['emi_option'] == 3) {
                            $val['loan_mode'] = 'Daily';
                        }
                    }
                    $val['loan_amount'] = 'N/A';
                    if (isset($row['amount'])) {
                        $val['loan_amount'] = $row['amount'] . ' &#8377';
                    }
                    $amount = LoanDayBooks::where('loan_type', $row['loan_type'])->where('account_number', $row['account_number'])->where('is_deleted', 0)->sum('deposit');
                    $val['total_recovery_amount'] = $amount . ' &#8377';
                    //used only to calculate ROI Amount
                    $closingAmountROI = 0;
                    if (isset($row['emi_option'])) {
                        if ($row['emi_option'] == 1) {
                            $closingAmountROI = $row['due_amount'] * $row['ROI'] / 1200;
                        } elseif ($row['emi_option'] == 2) {
                            $closingAmountROI = $row['due_amount'] * $row['ROI'] / 5200;
                        } elseif ($row['emi_option'] == 3) {
                            $closingAmountROI = $row['due_amount'] * $row['ROI'] / 36500;
                        }
                    }
                    //used only to calculate ROI Amount
                    $closingAmount = 0;
                    if (isset($row['due_amount'])) {
                        $closingAmount = round($row['due_amount'] + $closingAmountROI);
                    }
                    //$val['balance'] = $closingAmount.' &#8377';
                    $val['balance'] = 'N/A';
                    if (isset($row['loanMemberAssociate']->associate_no)) {
                        $val['associate_code'] = $row['loanMemberAssociate']->associate_no;
                    } else {
                        $val['associate_code'] = 'N/A';
                    }
                    if ($row['loan_type'] == 3) {
                        if (isset($row['associate_member_id'])) {
                            $val['associate_name'] = $row['children']->first_name . ' ' . $row['children']->last_name;
                        } else {
                            $val['associate_name'] = 'N/A';
                        }
                    } else {
                        if (isset($row['associate_member_id'])) {
                            $val['associate_name'] = $row['children']->first_name . ' ' . $row['children']->last_name;
                        } else {
                            $val['associate_name'] = 'N/A';
                        }
                    }
                    $rowReturn[] = $val;
                }
                Cache::put('loanCloseReportList', $dataExportClone);
                Cache::put('loanCloseReportListCount', $totalCount);
                $output = array("draw" => $_POST['draw'], "recordsTotal" => $totalCount, "recordsFiltered" => $count, "data" => $rowReturn);
                return json_encode($output);
            }
        }
    }
}