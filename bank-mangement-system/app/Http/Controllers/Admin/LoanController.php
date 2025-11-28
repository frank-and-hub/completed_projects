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
use App\Models\PlanLogDetails;
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

class LoanController extends Controller
{
    use Oustanding_amount_trait, getRecordUsingDayBookRefId, EmiDatesTraits;
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
    public function index()
    {
        $data['title'] = 'Loans Plan | Listing';
        return view('templates.admin.loan.plan_list', $data);
    }
    /**
     * Display a listing of the loans.
     *
     * @return \Illuminate\Http\Response
     */
    public function Loans()
    {
        if (check_my_permission(Auth::user()->id, "25") != "1") {
            return redirect()
                ->route('admin.dashboard');
        }
        $data['title'] = 'Loans';
        return view('templates.admin.loan.loans', $data);
    }
    /*Loans Fetch for the loan search form*/
    public function fetch(Request $request)
    {
        $fetch = Companies::with('loans:id,loan_type,name,company_id')->where('id', $request->company_id)->get(['id']);
        $data = '';
        $cou = count($fetch[0]['loans']);
        $ddaa = $fetch->toArray();
        for ($i = 0; $i < $cou; $i++) {
            $data .= '<option value="' . $ddaa[0]['loans'][$i]['id'] . '">' . $ddaa[0]['loans'][$i]['name'] . '</option>';
        }
        return response()->json(['data' => ($data) ?? '']);
    }
    /**
     * Display the specified resource.
     *
     * @param  \App\Reservation  $reservation
     * @return \Illuminate\Http\Response
     */
    public function planListing(Request $request)
    {
        $company_name = Companies::where('id', $request->company_id)->first(['id', 'name']);
        if ($request->ajax()) {
            $data = Loans::where('company_id', $request->company_id)->orderby('id', 'DESC');
            $data1 = $data->count('id');
            $count = $data1; //count($data1);
            $data = $data->offset($_POST['start'])->limit($_POST['length'])->get();
            $dataCount = Loans::orderby('id', 'DESC');
            $totalCount = $dataCount->count('id');
            $sno = $_POST['start'];
            $rowReturn = array();
            foreach ($data as $row) {
                if ($row->loan_type == 'L') {
                    $val['type'] = 'Loan';
                    $count = Memberloans::where('loan_type', $row->id)->count('id');
                } else {
                    $val['type'] = 'Group Loan';
                    $count = Grouploans::where('loan_type', $row->id)->count('id');
                }
                $relationId = '';
                $sno++;
                $val['DT_RowIndex'] = $sno;
                $val['id'] = $row->id;
                $val['company_name'] = $company_name->name;
                $val['name'] = $row->name;
                if ($row->loan_type == 'L') {
                    $val['type'] = 'Loan';
                } else {
                    $val['type'] = 'Group Loan';
                }
                if ($row->loan_category == 1) {
                    $val['category'] = 'Personal Loan';
                } elseif ($row->loan_category == 2) {
                    $val['category'] = 'Staff Loan';
                } elseif ($row->loan_category == 3) {
                    $val['category'] = 'Group Loan';
                } else {
                    $val['category'] = 'Loan against Investment';
                }
                $val['code'] = $row->code;
                if ($row->effective_from != NULL) {
                    $val['effective_from'] = date("d/m/Y", strtotime($row->effective_from));
                } else {
                    $val['effective_from'] = 'N/A';
                }
                $val['effective_to'] = '';
                if ($row->effective_to != NULL) {
                    $val['effective_to'] = date("d/m/Y", strtotime($row->effective_to));
                }
                $val['created_by'] = (isset($row->created_by_id) && $row->created_by_id != 0) ? getAdminUsername($row->created_by_id) : '';
                // if($row->created_by==1)
                // {
                //     $val['created_by'] =  getAdminUsername($row->created_by_id);
                // }
                // else
                // {
                //     $val['created_by'] = getAdminUsername($row->created_by_id);
                // }
                $val['min_amount'] = number_format((float) $row->min_amount, 2, '.', '');
                $val['max_amount'] = number_format((float) $row->max_amount, 2, '.', '');
                $val['status'] = $row->status;
                $val['created_at'] = date("d/m/Y h:i:A", strtotime($row->created_at));
                $chk = 0;
                $url = URL::to("admin/loan/tenure/edit/" . $row->id . "");
                $detailurl = URL::to("admin/loan/plan/details/" . $row->id . "");
                $commissionurl = URL::to("admin/loan/commission/percentage/" . $row->id . "");
                $btn = "";
                $btn .= '<div class="list-icons"><div class="dropdown">
                    <a href="#" class="list-icons-item" data-toggle="dropdown"><i class="icon-menu9"></i></a>
                    <div class="dropdown-menu dropdown-menu-right">';
                $btn .= '<button class="dropdown-item" onclick="change_plan_status(' . $row->id . ')" ><i class="fas fa-thumbs-up mr-2"></i>Change Status</button>';
                if ($row->status == 1) {
                    $chk++;
                }
                if ($count == 0 && $row->status == 1) {
                    $btn .= ' <a class="dropdown-item" href="' . $url . '"><i class="icon-pencil7 mr-2"></i>Edit</a>';
                    $chk++;
                }
                $btn .= '<a class="dropdown-item" href="' . $detailurl . '"><i class="fa fa-file"></i>Loan Details</a>';

                // $btn .= '<a class="dropdown-item" href="'.$commissionurl.'"><i class="fa fa-percent"></i>Commission Per.</a>';
                $btn .= ' </div></div></div>';
                $val['action'] = $btn;
                $rowReturn[] = $val;
            }
            $output = array("draw" => $_POST['draw'], "recordsTotal" => $totalCount, "recordsFiltered" => $totalCount, "data" => $rowReturn, );
            return json_encode($output);
        }
    }
    /**
     * Change Loan plan Status
     */
    public function planstatusChange(Request $request)
    {
        /*
        $adminID = Auth::user()->id;
        $id = $request->id;
        $globaldate=$request->created_at;
        $created_at=date("Y-m-d H:i:s", strtotime(convertDate($globaldate)));
        $data = Loans::where('id',$id)->update(['status' =>0,'effective_to' =>$created_at]);
        $updateTenure = LoanTenure::where('loan_id',$id)->update(['status' =>0,'effective_to' =>$created_at]);
        $updateCharges = \App\Models\LoanCharge::where('loan_id',$id)->update(['status' =>0,'effective_to' =>$created_at]);
        return \Response::json(['msg' => 'Status Changed Successfully', 'msg_type' => 'sucess']);
        */
        /** this code is modify by Gaurav on 16-10-2023 for makeing chaneges in function  */
        $loan_data = Loans::where('id', $request->id)->first();

        if ($loan_data->status == 1) {
            $loan_data->effective_to = date('Y-m-d', strtotime($request->created_at));
            $loan_data->status = 0;
            $loan_data->save();

            $loan_tenure_data = LoanTenure::where('loan_id', $loan_data->id)->get();
            if ($loan_tenure_data != null) {
                foreach ($loan_tenure_data as $tenure) {
                    if ($tenure->status == 1) {
                        $tenure->effective_to = date('Y-m-d', strtotime($request->created_at));
                        $tenure->status = 0;
                        $tenure->save();

                        /**Insert the log in the table
                         * table name is plan_log_details
                         */
                        $log_insert = new PlanLogDetails();
                        $log_insert->type = 1;
                        $log_insert->type_id = $tenure->loan_id;
                        $log_insert->tenure_id = $tenure->id;
                        $log_insert->title = 'Loan Plan’s Tenure Status Change';
                        if ($tenure->emi_option == 1) {
                            $log_insert->description = $tenure->tenure . ' Months [' . $loan_data->name . ' - ' . $loan_data->code . '] was deactivated by ' . Auth::user()->username;
                        } else if ($tenure->emi_option == 2) {
                            $log_insert->description = $tenure->tenure . ' Weeks [' . $loan_data->name . ' - ' . $loan_data->code . '] was deactivated by ' . Auth::user()->username;
                        } else if ($tenure->emi_option == 3) {
                            $log_insert->description = $tenure->tenure . ' Days [' . $loan_data->name . ' - ' . $loan_data->code . '] was deactivated by ' . Auth::user()->username;
                        }
                        $log_insert->old_data = 'Active';
                        $log_insert->new_data = 'Inactive';
                        $log_insert->created_by = 1;
                        $log_insert->created_by_id = Auth::user()->id;
                        $log_insert->save();
                    } else {
                        $tenure->effective_to = null;
                        $tenure->status = 1;
                        $tenure->save();

                        /**Insert the log in the table
                         * table name is plan_log_details
                         */
                        $log_insert = new PlanLogDetails();
                        $log_insert->type = 1;
                        $log_insert->type_id = $tenure->loan_id;
                        $log_insert->tenure_id = $tenure->id;
                        $log_insert->title = 'Loan Plan’s Tenure Status Change';
                        if ($tenure->emi_option == 1) {
                            $log_insert->description = $tenure->tenure . ' Months [' . $loan_data->name . ' - ' . $loan_data->code . '] was activated by ' . Auth::user()->username;
                        } else if ($tenure->emi_option == 2) {
                            $log_insert->description = $tenure->tenure . ' Weeks [' . $loan_data->name . ' - ' . $loan_data->code . '] was activated by ' . Auth::user()->username;
                        } else if ($tenure->emi_option == 3) {
                            $log_insert->description = $tenure->tenure . ' Days [' . $loan_data->name . ' - ' . $loan_data->code . '] was activated by ' . Auth::user()->username;
                        }
                        $log_insert->old_data = 'Inactive';
                        $log_insert->new_data = 'Active';
                        $log_insert->created_by = 1;
                        $log_insert->created_by_id = Auth::user()->id;
                        $log_insert->save();
                    }
                }
            }
            // /**Insert the log in the table
            //  * table name is plan_log_details
            //  */
            $log_insert = new PlanLogDetails();
            $log_insert->type = 1;
            $log_insert->type_id = $loan_data->id;
            $log_insert->title = 'Loan Plan Status Change';
            $log_insert->description = $loan_data->name . ' - ' . $loan_data->code . ' was deactivated by ' . Auth::user()->username;
            $log_insert->old_data = 'Active';
            $log_insert->new_data = 'Inactive';
            $log_insert->created_by = 1;
            $log_insert->created_by_id = Auth::user()->id;
            $log_insert->save();
            return response(['response' => 1]);
        } else {
            $loan_data->effective_to = null;
            $loan_data->status = 1;
            $loan_data->save();

            $loan_tenure_data = LoanTenure::where('loan_id', $loan_data->id)->get();
            if ($loan_tenure_data != null) {
                foreach ($loan_tenure_data as $tenure) {
                    if ($tenure->status == 1) {
                        $date = str_replace('/', '-', $request->gdate);
                        $tenure->effective_to = date('Y-m-d', strtotime($date));
                        $tenure->status = 0;
                        $tenure->save();

                        /**Insert the log in the table
                         * table name is plan_log_details
                         */
                        $log_insert = new PlanLogDetails();
                        $log_insert->type = 1;
                        $log_insert->type_id = $tenure->loan_id;
                        $log_insert->tenure_id = $tenure->id;
                        $log_insert->title = 'Loan Plan’s Tenure Status Change';
                        if ($tenure->emi_option == 1) {
                            $log_insert->description = $tenure->tenure . ' Months [' . $loan_data->name . ' - ' . $loan_data->code . '] was deactivated by ' . Auth::user()->username;
                        } else if ($tenure->emi_option == 2) {
                            $log_insert->description = $tenure->tenure . ' Weeks [' . $loan_data->name . ' - ' . $loan_data->code . '] was deactivated by ' . Auth::user()->username;
                        } else if ($tenure->emi_option == 3) {
                            $log_insert->description = $tenure->tenure . ' Days [' . $loan_data->name . ' - ' . $loan_data->code . '] was deactivated by ' . Auth::user()->username;
                        }
                        $log_insert->old_data = 'Active';
                        $log_insert->new_data = 'Inactive';
                        $log_insert->created_by = 1;
                        $log_insert->created_by_id = Auth::user()->id;
                        $log_insert->save();
                    } else {
                        $tenure->effective_to = null;
                        $tenure->status = 1;
                        $tenure->save();

                        /**Insert the log in the table
                         * table name is plan_log_details
                         */
                        $log_insert = new PlanLogDetails();
                        $log_insert->type = 1;
                        $log_insert->type_id = $tenure->loan_id;
                        $log_insert->tenure_id = $tenure->id;
                        $log_insert->title = 'Loan Plan’s Tenure Status Change';
                        if ($tenure->emi_option == 1) {
                            $log_insert->description = $tenure->tenure . ' Months [' . $loan_data->name . ' - ' . $loan_data->code . '] was activated by ' . Auth::user()->username;
                        } else if ($tenure->emi_option == 2) {
                            $log_insert->description = $tenure->tenure . ' Weeks [' . $loan_data->name . ' - ' . $loan_data->code . '] was activated by ' . Auth::user()->username;
                        } else if ($tenure->emi_option == 3) {
                            $log_insert->description = $tenure->tenure . ' Days [' . $loan_data->name . ' - ' . $loan_data->code . '] was activated by ' . Auth::user()->username;
                        }
                        $log_insert->old_data = 'Inactive';
                        $log_insert->new_data = 'Active';
                        $log_insert->created_by = 1;
                        $log_insert->created_by_id = Auth::user()->id;
                        $log_insert->save();
                    }
                }
            }

            /**Insert the log in the table
             * table name is plan_log_details
             */
            $log_insert = new PlanLogDetails();
            $log_insert->type = 1;
            $log_insert->type_id = $loan_data->id;
            $log_insert->title = 'Loan Plan Status Change';
            $log_insert->description = $loan_data->name . ' - ' . $loan_data->code . ' was activated by ' . Auth::user()->username;
            $log_insert->old_data = 'Inactive';
            $log_insert->new_data = 'Active';
            $log_insert->created_by = 1;
            $log_insert->created_by_id = Auth::user()->id;
            $log_insert->save();

            return response(['response' => 1]);
        }
    }
    /**
     * create loan Plan
     */
    public function planCreate()
    {
        $data['title'] = 'Loans Plan - Create';
        $data['loans'] = Loans::where('status', 1)->whereIn('id', [1, 2, 3, 4])->get();
        return view('templates.admin.loan.plan_create', $data);
    }


    public function planStore(Request $request)
    {
        $rules = [
            'companyId' => 'required',
            'name' => 'required',
            // 'code' => 'required',
            'loan_type' => 'required',
            'loan_category' => 'required',
            'min_amount' => 'required',
            'max_amount' => 'required',
            'effective_from' => 'required'
        ];
        $customMessages = [
            'required' => 'The :attribute field is required.'
        ];
        $this->validate($request, $rules, $customMessages);
        //echo $request->effective_from;die;
        $count = Loans::where('code', $request->code)->count();
        if ($count > 0) {
            return back()->with('alert', 'Loan code already used!')->withInput();
        }
        $slug = str_replace(' ', '-', strtolower($request->name));
        $countname = Loans::where('name', $request->name)->where('slug', $slug)->count();
        if ($countname > 0) {
            return back()->with('alert', 'Name already exist!')->withInput();
        }
        DB::beginTransaction();
        try {
            $globaldate = $request->created_at;
            $getLastHeadId = AccountHeads::where('status', 0)->orderBy('head_id', 'desc')->first('head_id');
            $loanAsstes = AccountHeads::where('head_id', 25)->where('status', 0)->orderBy('head_id', 'desc')->first(['head_id', 'id', 'labels', 'cr_nature', 'dr_nature']);
            $created_at = date("Y-m-d H:i:s", strtotime(convertDate($globaldate)));
            $data['company_id'] = $request->companyId;
            $data['name'] = $request->name;
            $companiesDetails = Companies::where('id', $request->companyId)->first(['id', 'last_fa_code', 'count']);
            $code = $companiesDetails->last_fa_code + 1;
            $data['code'] = $code;
            $data['slug'] = $slug;
            $data['loan_type'] = $request->loan_type;
            $data['loan_category'] = $request->loan_category;
            $data['min_amount'] = $request->min_amount;
            $data['max_amount'] = $request->max_amount;
            $data['effective_from'] = date('Y-m-d', strtotime(convertDate($request->effective_from)));
            $data['created_by_id'] = auth()->user()->id;
            $data['created_at'] = $created_at;
            $data['status'] = 1;
            $headDetails = [
                'head_id' => $getLastHeadId->head_id + 1,
                'sub_head' => $request->name,
                'parent_id' => $loanAsstes->head_id,
                'parentId_auto_id' => $loanAsstes->id,
                'labels' => $loanAsstes->labels + 1,
                'status' => 0,
                'cr_nature' => $loanAsstes->cr_nature,
                'dr_nature' => $loanAsstes->dr_nature,
                'is_move' => 1,
                'company_id' => '[' . $request->companyId . ']',
            ];
            $createHead = AccountHeads::create($headDetails);
            $headDetails2 = [
                'head_id' => $getLastHeadId->head_id + 2,
                'sub_head' => $request->name . ' Accrued Interest',
                'parent_id' => $loanAsstes->head_id,
                'parentId_auto_id' => $loanAsstes->head_id,
                'labels' => $loanAsstes->labels + 1,
                'status' => 0,
                'cr_nature' => $loanAsstes->cr_nature,
                'dr_nature' => $loanAsstes->dr_nature,
                'is_move' => 1,
                'company_id' => '[' . $request->companyId . ']',
            ];
            $createHead2 = AccountHeads::create($headDetails2);
            $data['head_id'] = $createHead->head_id;
            $data['ac_head_id'] = $createHead2->head_id;
            $res = Loans::create($data);
            $this->Store($request, $res->id);
            if ($request->loan_category != 4) {
                $this->LoanChargesStore($request, $res->id);
            }
            $count_value = $companiesDetails->count + 1;
            Companies::where('id', $request->companyId)->update(['last_fa_code' => $code, 'count' => $count_value]);
            DB::commit();
            return redirect()->route('admin.loan.plan_listing')->with('success', 'Loan Plan Saved Successfully!');
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('alert', $ex->getMessage().$ex->getLine());

        }
    }
    /**
     * Loan Tenure list
     */
    public function LoansTenure()
    {
        if (check_my_permission(Auth::user()->id, "25") != "1") {
            return redirect()
                ->route('admin.dashboard');
        }
        $data['title'] = 'Loans Tenure - Listing';
        return view('templates.admin.loan.loans', $data);
    }
    /**
     * Display the specified resource.
     *
     * @param  \App\Reservation  $reservation
     * @return \Illuminate\Http\Response
     */
    public function loanListing(Request $request)
    {
        if ($request->ajax()) {
            $data = LoanTenure::with('loan_tenure_plan:id,name,slug,code,loan_type')
                ->with('member:id,member_id,company_id,status');
            $arrFormData = array();
            if (!empty($_POST['searchform'])) {
                foreach ($_POST['searchform'] as $frm_data) {
                    $arrFormData[$frm_data['name']] = $frm_data['value'];
                }
            }
            if ($arrFormData) {
                if ($arrFormData['is_search'] == 'Yes' || $arrFormData['is_search'] != 'No') {
                    if ($arrFormData['company_id'] != '') {
                        $data = $data->where('company_id', $arrFormData['company_id']);
                    }
                    // if($arrFormData['branch_id']!=''){
                    //     $data = $data->where('branch_id',$arrFormData['branch_id']);
                    // }
                    if ($arrFormData['plan'] != '') {
                        $data = $data->with([
                            'loan_tenure_plan' => function ($query) use ($arrFormData) {
                                $query->whereId($arrFormData['plan']);
                            }
                        ]);
                    }
                }
            }
            $count = $data->count('id');
            $data = $data->skip($_POST['start'])->take($_POST['length'])->get();
            $totalCount = $count;
            $sno = $_POST['start'];
            $rowReturn = array();
            foreach ($data as $row) {
                $relationId = '';
                $sno++;
                $val['DT_RowIndex'] = $sno;
                $val['id'] = $row->id ?? 'N/A';
                $val['name'] = $row->name ?? 'N/A';
                $count = 0;
                if (($row['loan_tenure_plan'] ? $row['loan_tenure_plan']->loan_type : '0') == 'L') {
                    $val['type'] = 'Loan';
                    $count = Memberloans::where('loan_type', $row['loan_tenure_plan']->id)->where('emi_period', $row->tenure)->where('emi_option', $row->emi_option)->count('id');
                } else {
                    $val['type'] = 'Group Loan';
                    $count = Grouploans::where('loan_type', $row['loan_tenure_plan']->id ?? '0')->where('emi_period', $row->tenure ?? '0')->where('emi_option', $row->emi_option ?? '0')->count('id');
                }
                $val['tenure'] = $row->tenure ?? 'N/A';
                if ($row->emi_option == 1) {
                    $val['emi_option'] = 'Monthly';
                } else if ($row->emi_option == 2) {
                    $val['emi_option'] = 'Weekly';
                } else {
                    $val['emi_option'] = 'Daily(Days)';
                }
                $val['roi'] = $row->ROI ?? 'N/A';
                $val['effective_from'] = date("d/m/Y", strtotime($row->effective_from)) ?? 'N/A';
                $val['effective_to'] = isset($row->effective_to) ? date("d/m/Y", strtotime($row->effective_to)) : 'N/A';
                $val['created_by'] = getAdminUsername($row->created_by_id) ?? 'N/A';
                $val['status'] = $row->status ?? 'N/A';
                $val['created_at'] = date("d/m/Y h:i:A", strtotime($row->created_at)) ?? 'N/A';
                $url = URL::to("admin/loan/tenure/edit/" . $row->id . "");
                $chk = 0;
                $btn = "";
                $btn .= '<div class="list-icons"><div class="dropdown">
                    <a href="#" class="list-icons-item" data-toggle="dropdown"><i class="icon-menu9"></i></a>
                    <div class="dropdown-menu dropdown-menu-right">';
                if ($count == 0 || $row->status == 1) {
                    $btn .= ' <a class="dropdown-item" href="' . $url . '"><i class="icon-pencil7 mr-2"></i>Edit</a>';
                    $chk++;
                }
                if ($row->status == 1) {
                    $btn .= '<button class="dropdown-item" onclick="change_status(' . $row->status . ',' . $row->id . ')" ><i class="fas fa-thumbs-up mr-2"></i>Change Status</button>';
                    $chk++;
                }
                if ($chk == 0) {
                    $btn .= ' N/A';
                }
                $btn .= ' </div></div></div>';
                $val['action'] = $btn ?? 'N/A';
                $rowReturn[] = $val;
            }
            $output = array("draw" => $_POST['draw'], "recordsTotal" => $totalCount, "recordsFiltered" => $totalCount, "data" => $rowReturn, );
            return json_encode($output);
        }
    }
    /**
     * Show the form for creating a new loan.
     *
     * @return \Illuminate\Http\Response
     */
    public function Create()
    {
        if (check_my_permission(Auth::user()->id, "303") != "1") {
            return redirect()->route('admin.dashboard');
        }
        $data['title'] = 'Loans Tenure - Create';
        return view('templates.admin.loan.create', $data);
    }
    /**
     * Change Loan Status
     */
    public function tenureStatusChange(Request $request)
    {
        $adminID = Auth::user()->id;
        $status = $request->status;
        $id = $request->id;
        $type = $request->type;
        $globaldate = $request->created_at;
        $created_at = date("Y-m-d H:i:s", strtotime(convertDate($globaldate)));
        if ($status == 1) {
            $data = LoanTenure::where('id', $id)->update(['status' => 0, 'effective_to' => $created_at]);
        }
        return \Response::json(['msg' => 'Status Changed Successfully', 'msg_type' => 'sucess']);
    }
    /**
     * Store a newly created loan in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function Store($request, $loanId)
    {
        if (isset($request->tenure_ids)) {
            $rules = [
                'tenure' => 'required',
                'emi_option' => 'required',
                'roi' => 'required',
                'effective_from' => 'required'
            ];
        } else {
            $rules = [
                'tenure' => 'required',
                'emi_option' => 'required',
                'roi' => 'required',
                'effective_from' => 'required'
            ];
        }
        $customMessages = [
            'required' => 'The :attribute field is required.'
        ];
        $this->validate($request, $rules, $customMessages);
        $roiIntertes = $request->roi;
        $effectiveDate = date('Y-m-d', strtotime(convertDate($request->tenure_effective_from)));
        $getdublicate = LoanTenure::where('loan_id', $loanId)->where('tenure', $request->tenure)->where('emi_option', $request->emi_option)->where('effective_from', '<', $effectiveDate)->orderBy('effective_from', 'DESC')->first(['id', 'name', 'effective_from']);
        if ($getdublicate) {
            $effective_to = date('Y-m-d', strtotime('-1 day', strtotime(convertDate($request->tenure_effective_from))));
            $update = LoanTenure::where('id', $getdublicate->id)->update(['effective_to' => $effective_to]);
        }
        $getdublicate2 = LoanTenure::where('loan_id', $loanId)->where('tenure', $request->tenure)->where('emi_option', $request->emi_option)->where('effective_from', '>', $effectiveDate)->orderBy('effective_from', 'ASC')->first(['id', 'name', 'effective_from', 'effective_to']);
        $getPlanName = Loans::where('id', $loanId)->first(['id', 'name', 'company_id']);
        if ($request->emi_option == 1) {
            $emi_option = 'Months';
        } else if ($request->emi_option == 2) {
            $emi_option = 'Weeks';
        } else {
            $emi_option = 'Days';
        }
        $name = $getPlanName->name . ' - ' . $request->tenure . ' ' . $emi_option;
        $data['name'] = $name;
        $data['loan_id'] = $loanId;
        $data['tenure'] = $request->tenure;
        $data['emi_option'] = $request->emi_option;
        $data['ROI'] = $request->roi;
        $data['effective_from'] = date('Y-m-d', strtotime(convertDate($request->effective_from)));
        $data['effective_to'] = (isset($request->tenure_effective_to)) ? date('Y-m-d', strtotime(convertDate($request->tenure_effective_to))) : NULL;
        $data['created_by_id'] = auth()->user()->id;
        $data['created_at'] = $request->created_at;
        $data['status'] = 1;
        $data['company_id'] = $getPlanName->company_id;
        $res = LoanTenure::create($data);
        if (isset($_POST['more_emi_option'])) {
            foreach (($_POST['more_emi_option']) as $key => $option) {
                if ($request->emi_option == 1) {
                    $emi_option = 'Months';
                } else if ($request->emi_option == 2) {
                    $emi_option = 'Weeks';
                } else {
                    $emi_option = 'Days';
                }
                $getdublicate2 = LoanTenure::where('loan_id', $loanId)->where('tenure', $_POST['more_tenure'][$key])->where('emi_option', $_POST['more_emi_option'][$key])->where('effective_from', '>', date('Y-m-d', strtotime(convertDate($_POST['more_tenure_effective_from'][$key]))))->orderBy('effective_from', 'ASC')->first(['id', 'name', 'effective_from', 'effective_to']);
                $name = $getPlanName->name . ' - ' . $request->tenure . ' ' . $emi_option;
                $dataMore['name'] = $name;
                $dataMore['loan_id'] = $loanId;
                $dataMore['tenure'] = $_POST['more_tenure'][$key];
                $dataMore['emi_option'] = $_POST['more_emi_option'][$key];
                $dataMore['ROI'] = $_POST['more_roi'][$key];
                $dataMore['effective_from'] = date('Y-m-d', strtotime(convertDate($_POST['more_tenure_effective_from'][$key])));
                if ($getdublicate2) {
                    $tenureData['effective_to'] = date('Y-m-d', strtotime(convertDate($getdublicate2->effective_to)));
                }
                $dataMore['created_by_id'] = auth()->user()->id;
                $dataMore['created_at'] = $request->created_at;
                $dataMore['status'] = 1;
                $dataMore['company_id'] = $getPlanName->company_id;
                if (count($request->tenure_ids) > 0) {
                    $res = LoanTenure::where('id', $_POST['tenure_ids'][$key])->update($dataMore);
                } else {
                    $res = LoanTenure::create($dataMore);
                }
            }
        }
        return $res;
    }

    public function refNoStore(Request $request)
    {
        // $oldVal = !empty($request['oldVal']) ? json_encode($request['oldVal']) : '';
        // dd([$oldVal, $request->all()]);

        try {
            if ($request->loanType == "L") {

                $loandata = Memberloans::find($request['refId']);

                if (!$loandata) {
                    return response()->json(['error' => 'Loan data not found'], 404);
                }
                $loanT = getLoanData($loandata->loan_type);

                $loanLog = [
                    "loanId" => $request['refId'] ?? '',
                    "loan_type" => $loandata->loan_type,
                    "loan_category" => $loanT->loan_category,
                    "loan_name" => $loanT->name,
                    "title" =>  ($loandata->ecs_ref_no == null) ? 'Ecs Register' : 'Ecs Update',
                    "description" => ($loandata->ecs_ref_no == null) ? 'Ecs Register' . '(' . $request['refText'] . ')' : 'Ecs Update' . '(' . $request['refText'] . ')',

                    "old_val" => !empty($request['oldVal']) ? json_encode($request['oldVal']) : null,
                    "new_val" => !empty($request['refText']) ? json_encode($request['refText']) : null,
                    "status" => 12,
                    "status_changed_date" => date("Y-m-d"),
                    "created_by" => Auth::user()->id,
                    "created_by_name" => $request['createdByName'],
                    "user_name" => Auth::user()->username,

                ];

                \App\Models\LoanLog::create($loanLog);
                $loandata->update(['ecs_ref_no' => $request['refText']]);
                return response()->json(['success']);
            } else {
                $loandata = Grouploans::find($request['refId']);

                if (!$loandata) {
                    return response()->json(['error' => 'Loan data not found'], 404);
                }

                $loanT = getLoanData($loandata->loan_type);
                $loanLog = [
                    "loanId" => $request['refId'] ?? '',
                    "loan_type" => $loandata->loan_type,
                    "loan_category" => $loanT->loan_category,
                    "loan_name" => $loanT->name,
                    "title" =>  ($loandata->ecs_ref_no == null) ? 'Ecs Register' : 'Ecs Update',
                    "description" => ($loandata->ecs_ref_no == null) ? 'Ecs Register' . '(' . $request['refText'] . ')' : 'Ecs Update' . '(' . $request['refText'] . ')',

                    "old_val" => !empty($request['oldVal']) ? json_encode($request['oldVal']) : null,
                    "new_val" => !empty($request['refText']) ? json_encode($request['refText']) : null,
                    "status" => 12,
                    "status_changed_date" => date("Y-d-m"),
                    "created_by" => Auth::user()->id,
                    "created_by_name" => $request['createdByName'],
                    "user_name" => Auth::user()->username,


                ];
                // dd($loanLog);
                \App\Models\LoanLog::create($loanLog);
                $loandata->update(['ecs_ref_no' => $request['refText']]);
                return response()->json(['success']);
            }
            // dd($loandata);
        } catch (\Exception $e) {
            // Handle the exception
            return response()->json(['error' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }
    // public function refNoStore(Request $request){
    //     // dd($request->all());

    //     try {
    //         if($request->loanType == "L"){

    //         $loandata = Memberloans::find($request['refId']);

    //         if (!$loandata) {
    //             return response()->json(['error' => 'Loan data not found'], 404);
    //         }
    //         $loanT = getLoanData($loandata->loan_type);

    //         $loanLog = [
    //             "loanId" => $request['refId'] ?? '',
    //             "loan_type" => $loandata->loan_type ,
    //             "loan_category" => $loanT->loan_category ,
    //             "loan_name" => $loanT->name,
    //             "description" => ($loandata->ecs_ref_no == null) ? 'Ecs Register' . '(' . $request['refText'] . ')' : 'Ecs Update' . '(' . $request['refText'] . ')',

    //             "status_changed_date" => date("Y-m-d"),
    //             "created_by" =>Auth::user()->id ,
    //             "created_by_name" => $request['createdByName'],
    //             "user_name" =>Auth::user()->username ,

    //         ];

    //         \App\Models\LoanLog::create($loanLog);
    //         $loandata->update(['ecs_ref_no' => $request['refText']]);
    //         return response()->json(['success']);
    //     }else{
    //         $loandata = Grouploans::find($request['refId']);

    //         if (!$loandata) {
    //             return response()->json(['error' => 'Loan data not found'], 404);
    //         }

    //         $loanT = getLoanData($loandata->loan_type);
    //         $loanLog = [
    //             "loanId" => $request['refId'] ?? '',
    //             "loan_type" => $loandata->loan_type ,
    //             "loan_category" => $loanT->loan_category ,
    //             "loan_name" => $loanT->name,
    //             "description" => ($loandata->ecs_ref_no == null) ? 'Ecs Register' . '(' . $request['refText'] . ')' : 'Ecs Update' . '(' . $request['refText'] . ')',

    //             "status_changed_date" => date("Y-d-m")  ,
    //             "created_by" =>Auth::user()->id ,
    //             "created_by_name" => $request['createdByName'],
    //             "user_name" =>Auth::user()->username ,


    //         ];
    //         // dd($loanLog);
    //         \App\Models\LoanLog::create($loanLog);
    //         $loandata->update(['ecs_ref_no' => $request['refText']]);
    //         return response()->json(['success']);
    //     }
    //         // dd($loandata);
    //     } catch (\Exception $e) {
    //         // Handle the exception
    //         return response()->json(['error' => 'An error occurred: ' . $e->getMessage()], 500);
    //     }
    // }
    /**
     * Store a newly created loan in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function editTenure($request, $loanId)
    {
        $rules = [
            'tenure' => 'required',
            'emi_option' => 'required',
            'roi' => 'required',
            'effective_from' => 'required'
        ];
        $customMessages = [
            'required' => 'The :attribute field is required.'
        ];
        $this->validate($request, $rules, $customMessages);
        $roiIntertes = $request->roi;
        $getPlanName = Loans::where('id', $loanId)->first(['id', 'name']);
        if (isset($_POST['more_emi_option'])) {
            foreach (($_POST['more_emi_option']) as $key => $option) {
                if ($_POST['more_emi_option'][$key] == 1) {
                    $emi_option = 'Months';
                } else if ($_POST['more_emi_option'][$key] == 2) {
                    $emi_option = 'Weeks';
                } else {
                    $emi_option = 'Days';
                }
                $name = $getPlanName->name . ' - ' . $_POST['more_tenure'][$key] . ' ' . $emi_option;
                $dataMore['name'] = $name;
                $dataMore['loan_id'] = $loanId;
                $dataMore['tenure'] = $_POST['more_tenure'][$key];
                $dataMore['emi_option'] = $_POST['more_emi_option'][$key];
                $dataMore['ROI'] = $_POST['more_roi'][$key];
                $dataMore['effective_from'] = date('Y-m-d', strtotime(convertDate($_POST['more_tenure_effective_from'][$key])));
                // if($getdublicate2)
                // {
                //     $tenureData['effective_to']=date('Y-m-d', strtotime(convertDate($getdublicate2->effective_to)));
                // }
                $dataMore['created_by_id'] = auth()->user()->id;
                $dataMore['created_at'] = $request->created_at;
                $dataMore['status'] = 1;
                $res = LoanTenure::create($dataMore);
            }
        }
        if (count($request->tenure_ids) > 0) {
            foreach (($request->tenure_ids) as $key => $option) {
                if ($_POST['emi_option'][$key] == 1) {
                    $emi_option = 'Months';
                } else if ($_POST['emi_option'][$key] == 2) {
                    $emi_option = 'Weeks';
                } else {
                    $emi_option = 'Days';
                }
                $name = $getPlanName->name . ' - ' . $_POST['tenure'][$key] . ' ' . $emi_option;
                $dataMore['name'] = $name;
                $dataMore['loan_id'] = $loanId;
                $dataMore['tenure'] = $_POST['tenure'][$key];
                $dataMore['emi_option'] = $_POST['emi_option'][$key];
                $dataMore['ROI'] = $_POST['roi'][$key];
                $dataMore['effective_from'] = date('Y-m-d', strtotime(convertDate($_POST['tenure_effective_from'][$key])));
                $dataMore['created_by_id'] = auth()->user()->id;
                $dataMore['created_at'] = $request->created_at;
                $dataMore['status'] = 1;
                $res = LoanTenure::where('id', $_POST['tenure_ids'][$key])->update($dataMore);
            }
        }
        return $res;
    }
    /**
     * Display created loan by id.
     *
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function Edit($id, Request $request)
    {
        $resource = request()->segment(3);
        $data['title'] = ($resource == 'tenure') ? 'Loans Plan | Edit' : 'Loans Plan | Details';
        $data['plan'] = Loans::where('id', $id)->first(['id', 'name', 'code', 'loan_category', 'min_amount', 'max_amount', 'effective_from', 'loan_type', 'company_id']);
        $data['company_name'] = Companies::where('id', $data['plan']->company_id)->first(['id', 'name']);
        $data['loans'] = Loans::where('status', 1)->whereIn('id', [1, 2, 3, 4])->get();
        $data['record'] = Memberloans::select('id', 'emi_period')->where('loan_type', $data['plan']->id)->get()->groupBy('emi_period');
        $data['recordFile'] = Memberloans::select('id', 'file_charges')->where('loan_type', $data['plan']->id)->get()->groupBy('file_charges');
        $data['recordIns'] = Memberloans::select('id', 'insurance_charge')->where('loan_type', $data['plan']->id)->get()->groupBy('insurance_charge');
        $data['loan'] = LoanTenure::where('loan_id', $id)->where('is_deleted', 0)->get();
        $data['fileCharge'] = \App\Models\LoanCharge::where('loan_id', $id)->where('type', 1)->where('status', 1)->where('is_deleted', 0)->get();
        $data['insCharge'] = \App\Models\LoanCharge::where('loan_id', $id)->where('type', 2)->where('status', 1)->where('is_deleted', 0)->get();
        $data['resourceType'] = $resource;
        return view('templates.admin.loan.edit', $data);
    }
    /**
     * get duplicate tenure
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getDublicateTenure(Request $request)
    {
        $createdAt = date('Y-m-d', strtotime(convertDate($request->effective_from)));
        $json_encode = 0;
        if ($request->id == 0) {
            $getdublicate = LoanTenure::where('loan_id', $request->loan_id)->where('tenure', $request->tenure)->where('emi_option', $request->emi_option)->orderBy('effective_from', 'DESC')->whereRaw('? between effective_from and effective_to', [$createdAt])->orderBy('effective_from', 'DESC')->count();
            $getdublicateDESC = LoanTenure::where('loan_id', $request->loan_id)->where('tenure', $request->tenure)->where('emi_option', $request->emi_option)->orderBy('effective_from', 'DESC')->first(['id', 'name', 'effective_from']);
        } else {
            $getdublicate = LoanTenure::where('id', '!=', $request->id)->where('loan_id', $request->loan_id)->where('tenure', $request->tenure)->where('emi_option', $request->emi_option)->whereRaw('? between effective_from and effective_to', [$createdAt])->orderBy('effective_from', 'DESC')->count();
            $getdublicateDESC = LoanTenure::where('id', '!=', $request->id)->where('loan_id', $request->loan_id)->where('tenure', $request->tenure)->where('emi_option', $request->emi_option)->orderBy('effective_from', 'DESC')->first(['id', 'name', 'effective_from']);
        }
        if ($getdublicate > 0) {
            $json_encode = 2;
        }
        if ($getdublicateDESC) {
            if ((date('Y-m-d', strtotime(convertDate($request->effective_from))) > $getdublicateDESC->effective_from) && $getdublicate == 0) {
                $json_encode = 1;
            }
        }
        return json_encode($json_encode);
    }
    /**
     * Update the specified loan.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function updateTenure(Request $request)
    {
        // $validator = Validator::make($request->all(), [
        //         'tenure' => 'required|numeric',
        //         'emi_option' =>  'required',
        //         'roi' => 'required|numeric',
        //         'tenure_effective_from' => 'required'
        // ]);
        // // $validator = $request->validate($rules);
        // if ($validator->fails()){
        //           return redirect()->back()
        //         ->withErrors($validator)
        //         ->withInput();
        // }
        $checkRecordExist = LoanTenure::where('loan_id', $request->loanId)->where('tenure', $request->tenure)->whereNull('effective_to')->exists();
        $checkRecordExistEmi = LoanTenure::where('loan_id', $request->loanId)->where('emi_option', $request->emi_option)->whereNull('effective_to')->exists();
        if ($checkRecordExist && $checkRecordExistEmi && !isset($request->id)) {
            return redirect()->back()->with(['alert' => 'Tenure Already Created For this Loan']);
        }
        $roiIntertes = $request->roi;
        $getPlanName = Loans::where('id', $request->loanId)->first(['id', 'name']);
        if ($request->emi_option == 1) {
            $emi_option = 'Months';
        } else if ($request->emi_option == 2) {
            $emi_option = 'Weeks';
        } else {
            $emi_option = 'Days';
        }
        if (!isset($request->id)) {
            $name = $getPlanName->name . ' - ' . $request->tenure . ' ' . $emi_option;
            $tenureData['name'] = $name;
            $tenureData['loan_id'] = $request->loanId;
        }
        $tenureData['roi'] = $request->roi;
        $tenureData['tenure'] = $request->tenure;
        $tenureData['emi_option'] = $request->emi_option;
        $tenureData['effective_from'] = date('Y-m-d', strtotime(convertDate($request->tenure_effective_from)));
        $tenureData['effective_to'] = (isset($request->tenure_effective_to)) ? date('Y-m-d', strtotime(convertDate($request->tenure_effective_to))) : NULL;
        $tenureData['created_by_id'] = auth()->user()->id;
        // if($tenureUpdate->effective_to < $tenureData['effective_from'] )
        // {
        //     $tenureData['effective_to']=NULL;
        // }
        if (isset($request->id)) {
            $tenureUpdate = LoanTenure::find($request->id);
            $res = $tenureUpdate->update($tenureData);
        } else {
            $res = LoanTenure::create($tenureData);
        }
        //dd($res);
        if ($res) {
            return redirect()->back()->with('success', 'Loan Tenure Updated Successfully!')->withInput();
        } else {
            return redirect()->route('admin.loan.loans')->with('alert', 'An error occured')->withInput();
        }
    }
    /**
     * Loan requests listing view.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function loanRequest(Request $request)
    {
        if (check_my_permission(Auth::user()->id, "26") != "1") {
            return redirect()
                ->route('admin.dashboard');
        }
        $data['title'] = 'Loan Registration Details';
        return view('templates.admin.loan.loan-requests', $data);
    }
    /**
     * Show loan requests listing.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function loanRequestAjax(Request $request)
    {
        if ($request->ajax()) {
            if (!empty($_POST['searchform'])) {
                foreach ($_POST['searchform'] as $frm_data) {
                    $arrFormData[$frm_data['name']] = $frm_data['value'];
                }
            }
            if (isset($arrFormData['is_search']) && $arrFormData['is_search'] == 'yes') {
                $applicationDate = date("Y-m-d ", strtotime(convertDate($arrFormData['create_application_date'])));
                if ($arrFormData['loan_type'] == 'L') {
                    $data = Memberloans::select('id', 'applicant_id', 'account_number', 'approve_date', 'emi_option', 'emi_period', 'deposite_amount', 'amount', 'file_charges', 'status', 'loan_type', 'associate_member_id', 'branch_id', 'created_at', 'approved_date', 'insurance_cgst', 'insurance_sgst', 'filecharge_sgst', 'filecharge_cgst', 'insurance_charge_igst', 'filecharge_igst', 'insurance_charge', 'rejection_description', 'customer_id', 'transfer_amount', 'company_id', 'application_no','ecs_charges','ecs_type','ecs_ref_no','ssb_id','ecs_charge_igst','ecs_charge_cgst','ecs_charge_sgst')
                        ->with([
                            'memberCompany:id,member_id,customer_id',
                            'loan:id,name,loan_type,loan_category',
                            'loanMember:id,member_id,first_name,last_name',
                            'loanMemberAssociate:id,associate_no,first_name,last_name',
                            'loanBranch:id,name,branch_code,sector,regan,zone',
                            'loanMemberBankDetails:id,member_id,bank_name,account_no,ifsc_code',
                            'company:id,name'
                        ])
                        ->whereIn('status', [0, 1, 3, 4, 5, 6, 7,8])
                        ->where('is_deleted', 0);
                } else {
                    $data = Grouploans::select('id', 'group_activity', 'groupleader_member_id', 'applicant_id', 'account_number', 'group_loan_common_id', 'approve_date', 'emi_option', 'emi_period', 'deposite_amount', 'transfer_amount', 'amount', 'file_charges', 'status', 'member_loan_id', 'member_id', 'associate_member_id', 'branch_id', 'created_at', 'approved_date', 'loan_type', 'insurance_cgst', 'insurance_sgst', 'filecharge_sgst', 'filecharge_cgst', 'insurance_charge_igst', 'filecharge_igst', 'insurance_charge', 'rejection_description', 'customer_id', 'company_id', 'application_no','ecs_charges','ecs_type','ecs_ref_no','ssb_id','ecs_charge_igst','ecs_charge_cgst','ecs_charge_sgst')
                        ->with([
                            'memberCompany:id,member_id,customer_id',
                            'loan:id,name,loan_type,loan_category',
                            'loanMember:id,member_id,first_name,last_name',
                            'loanMemberAssociate:id,member_id,first_name,last_name,associate_no',
                            'groupleaderMemberIDCustom:id,member_id,first_name,last_name,associate_no',
                            'MemberApplicantCustom:id,member_id,first_name,last_name,associate_no',
                            'company:id,name',
                            'gloanBranch:id,name,branch_code,sector,regan,zone,state_id',
                            // 'loanMemberBankDetails2:id,member_id,bank_name,account_no,ifsc_code',
                            //'loanMemberBankDetails:id,member_id,bank_name,account_no,ifsc_code'
                        ])->whereIn('status', [0, 1, 3, 4, 5, 6, 7,8]);
                }
                if ($arrFormData['group_loan_common_id'] != '') {
                    $group_loan_id = $arrFormData['group_loan_common_id'];
                    $data = $data->where('group_loan_common_id', $group_loan_id);
                }
                if (isset($arrFormData['company_id']) && $arrFormData['company_id'] != '') {
                    $company_id = $arrFormData['company_id'];
                    $data = $data->where('company_id', $company_id);
                }
                if ($arrFormData['date_from'] != '') {
                    $startDate = date("Y-m-d", strtotime(convertDate($arrFormData['date_from'])));
                    if ($arrFormData['date_to'] != '') {
                        $endDate = date("Y-m-d ", strtotime(convertDate($arrFormData['date_to'])));
                    } else {
                        $endDate = '';
                    }
                    $data = $data->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate]);
                }
                if ($arrFormData['loan_plan'] != '') {
                    $planId = $arrFormData['loan_plan'];
                    $data = $data->where('loan_type', $planId);
                }
                if (isset($arrFormData['branch_id']) && $arrFormData['branch_id'] != '') {
                    $branch_id = $arrFormData['branch_id'];
                    $data = $data->where('branch_id', $branch_id);
                }
                if ($arrFormData['application_number'] != '') {
                    $application_number = $arrFormData['application_number'];
                    $data = $data->where('account_number', $application_number);
                }
                if ($arrFormData['member_name'] != '') {
                    $name = $arrFormData['member_name'];
                    $data = $data->whereHas('loanMember', function ($query) use ($name) {
                        $query->where('members.first_name', 'LIKE', '%' . $name . '%')->orWhere('members.last_name', 'LIKE', '%' . $name . '%')->orWhere(DB::raw('concat(members.first_name," ",members.last_name)'), 'LIKE', "%$name%");
                    });
                }
                if ($arrFormData['member_id'] != '') {
                    $meid = $arrFormData['member_id'];
                    $data = $data->whereHas('memberCompany', function ($query) use ($meid) {
                        $query->where('member_companies.member_id', 'LIKE', '%' . $meid . '%');
                    });
                }
                if ($arrFormData['customer_id'] != '') {
                    $meid = $arrFormData['customer_id'];
                    $data = $data->whereHas('loanMember', function ($query) use ($meid) {
                        $query->where('members.member_id', 'LIKE', '%' . $meid . '%');
                    });
                }
                if ($arrFormData['associate_code'] != '') {
                    $associateCode = $arrFormData['associate_code'];
                    $data = $data->whereHas('loanMemberAssociate', function ($query) use ($associateCode) {
                        $query->where('members.associate_no', 'LIKE', '%' . $associateCode . '%');
                    });
                }
                if ($arrFormData['status'] != '') {
                    $status = $arrFormData['status'];
                    $data = $data->where('status', $status);
                }
                $loant = $arrFormData['loan_type'];
                $data = $data->whereHas('loan', function ($query) use ($loant) {
                    $query->where('loans.loan_type', $loant);
                });
                $totalCount = $data->count('id');
                $dataexport = $data->orderby('id', 'DESC')->get();
                $data = $data->orderby('id', 'DESC')->offset($_POST['start'])->limit($_POST['length'])->get();
                $sno = $_POST['start'];
                $rowReturn = array();
                $statusLabels = [
                    0 => 'Pending',
                    1 => 'Approved',
                    2 => 'Rejected',
                    3 => 'Clear',
                    4 => 'Due',
                    5 => 'Rejected',
                    6 => 'Hold',
                    7 => 'Approved Hold',
                    8 => 'Cancel',
                ];
                foreach ($data as $row) {
                    $sno++;
                    $val['DT_RowIndex'] = $sno;
                    if ($row['loan']->loan_type == 'G') {
                        $group_loan_id = $row->group_loan_common_id;
                        $val['branch'] = $row['gloanBranch']->name;
                        $val['branch_code'] = $row['gloanBranch']->branch_code;
                        $val['sector'] = $row['gloanBranch']->sector;
                        $val['region'] = $row['gloanBranch']->regan;
                        $val['zone'] = $row['gloanBranch']->zone;
                        $val['insurance_charge'] = (isset($row->insurance_charge)) ? $row->insurance_charge . ' <i class="fa fa-inr"></i>' : 'N/A';
                        $bankName = getMemberCompanyDataNew($row->customer_id) ? !empty(getMemberCompanyDataNew($row->customer_id)->member['memberBankDetails'][0]) ? getMemberCompanyDataNew($row->customer_id)->member['memberBankDetails'][0]['bank_name'] : 'N/A' : 'N/A';
                        $bankAccount = getMemberCompanyDataNew($row->customer_id) ? !empty(getMemberCompanyDataNew($row->customer_id)->member['memberBankDetails'][0]) ? getMemberCompanyDataNew($row->customer_id)->member['memberBankDetails'][0]['account_no'] : 'N/A' : 'N/A';
                        $ifscCode = getMemberCompanyDataNew($row->customer_id) ? !empty(getMemberCompanyDataNew($row->customer_id)->member['memberBankDetails'][0]) ? getMemberCompanyDataNew($row->customer_id)->member['memberBankDetails'][0]['ifsc_code'] : 'N/A' : 'N/A';
                        // as per new update changes are made by sourab on sachinn sir permmission
                        // $bankName = isset($row['loanMemberBankDetails2']->bank_name) ? $row['loanMemberBankDetails2']->bank_name : 'N/A';
                        // $bankAccount =isset($row['loanMemberBankDetails2']->account_no) ? $row['loanMemberBankDetails2']->account_no : 'N/A';
                        // $ifscCode =isset($row['loanMemberBankDetails2']->ifsc_code) ? $row['loanMemberBankDetails2']->ifsc_code : 'N/A';
                    } else {
                        $group_loan_id = '';
                        $val['branch'] = $row['loanBranch']->name . " (" . $row['loanBranch']->branch_code . ") ";
                        $val['sector'] = $row['loanBranch']->sector;
                        $val['region'] = $row['loanBranch']->regan;
                        $val['zone'] = $row['loanBranch']->zone;
                        $val['insurance_charge'] = (isset($row->insurance_charge)) ? $row->insurance_charge . ' <i class="fa fa-inr"></i>' : 'N/A';
                        ;
                        $bankName = isset($row['loanMemberBankDetails']->bank_name) ? $row['loanMemberBankDetails']->bank_name : 'N/A';
                        $bankAccount = isset($row['loanMemberBankDetails']->account_no) ? $row['loanMemberBankDetails']->account_no : 'N/A';
                        $ifscCode = isset($row['loanMemberBankDetails']->ifsc_code) ? $row['loanMemberBankDetails']->ifsc_code : 'N/A';
                    }
                    $val['emi_period'] = $row->emi_period;
                    $val['emi_option'] = ($row->emi_option == 1) ? 'Month' : (($row->emi_option == 2) ? 'Week' : 'Daily');
                    $val['applicant_id'] = $row->application_no ?? 'N/A';
                    $val['group_loan_id'] = $group_loan_id ?? 'N/A';
                    $val['application_number'] = $row->account_number ?? 'N/A';
                    $val['cgst_insurance_charge'] = $row->insurance_cgst . ' <i class="fa fa-inr"></i>' ?? 'N/A';
                    $val['sgst_insurance_charge'] = $row->insurance_sgst . ' <i class="fa fa-inr"></i>' ?? 'N/A';
                    $val['igst_insurance_charge'] = $row->insurance_charge_igst . ' <i class="fa fa-inr"></i>' ?? 'N/A';
                    $val['igst_file_charge'] = $row->filecharge_igst . ' <i class="fa fa-inr"></i>' ?? 'N/A';
                    $val['cgst_file_charge'] = $row->filecharge_cgst . ' <i class="fa fa-inr"></i>' ?? 'N/A';
                    $val['sgst_file_charge'] = $row->filecharge_sgst . ' <i class="fa fa-inr"></i>' ?? 'N/A';
                    $member_id = $row['memberCompany']->member_id ?? 'N/A';
                    $customer_id = $row['loanMember']->member_id ?? 'N/A';
                    $mid = $row['memberCompany']->id ?? 0;
                    $member_name = isset($row['loanMember']) ? $row['loanMember']->first_name . " " . $row['loanMember']->last_name : '';
                    $val['member_id'] = $member_id;
                    $val['customer_id'] = $customer_id;
                    $val['company'] = Companies::findorFail($row->company_id)->name;
                    // $val['totaldepositinv'] ='N/A';
                    // if($member_id>0)
                    // {
                    //     // $val['totaldepositinv'] = getAllDeposit($customer_id,$applicationDate);
                    //     $val['totaldepositinv'] = ($row->amount)??'0.00';
                    // }
                    // // $val['totaldepositinv'] = ($row->amount)??'0.00';
                    $val['totaldepositinv'] = getAllDeposit($row['loanMember']->id, $applicationDate) ?? '';
                    $val['member_name'] = $member_name;
                    if ($row->approve_date) {
                        switch ($row->emi_option) {
                            case 1:
                                $last_recovery_date = date('d/m/Y', strtotime("+" . $row->emi_period . " months", strtotime($row->approve_date)));
                                break;
                            case 2:
                                $days = $row->emi_period * 7;
                                $start_date = $row->approve_date;
                                $date = strtotime($start_date);
                                $date = strtotime("+" . $days . " day", $date);
                                $last_recovery_date = date('d/m/Y', $date);
                                break;
                            case 3:
                                $days = $row->emi_period;
                                $start_date = $row->approve_date;
                                $date = strtotime($start_date);
                                $date = strtotime("+" . $days . " day", $date);
                                $last_recovery_date = date('d/m/Y', $date);
                                break;
                            default:
                                $last_recovery_date = 'N/A';
                                break;
                        }
                    } else {
                        $last_recovery_date = 'N/A';
                    }
                    $val['last_recovery_date'] = $last_recovery_date;
                    $val['associate_code'] = isset($row['loanMemberAssociate']->associate_no) ? $row['loanMemberAssociate']->associate_no : 'N/A';
                    $member = $row['loanMemberAssociate'];
                    $val['associate_name'] = (isset($member->first_name) ? $member->first_name . ' ' . $member->last_name : 'N/A');
                    $val['loan'] = $row['loan']->name;
                    $val['amount'] = ($row->transfer_amount) ?? "0.00" . ' <i class="fa fa-inr"></i>';
                    $val['transfer_date'] = date("d/m/Y", strtotime(convertDate($row->approve_date))) ?? '';
                    $val['file_charge'] = $row->file_charges ?? 'N/A';
                    $val['ecs_charge'] = $row->ecs_charges ?? 'N/A';
                    $val['ecs_ref_no'] = $row->ecs_ref_no ?? 'N/A';
                    $val['igst_ecs_charge'] = $row->ecs_charge_igst??'N/A';
                    $val['cgst_ecs_charge'] = $row->ecs_charge_cgst??'N/A';
                    $val['sgst_ecs_charge'] = $row->ecs_charge_sgst??'N/A';
                    $val['loan_amount'] = $row->amount . ' <i class="fas fa-rupee-sign"></i>';
                    $val['file_charge'] = $row->file_charges . ' <i class="fas fa-rupee-sign"></i>';
                    if ($row['loan']->loan_type == 'G') {
                        $val['bank_name'] = $bankName ?? 'N/A';
                        $val['bank_account_number'] = $bankAccount ?? 'N/A';
                        $val['ifsc_code'] = $ifscCode ?? 'N/A';
                    } else {
                        $val['bank_name'] = loanApplicatBankDetail($row->id) ? loanApplicatBankDetail($row->id)->bank_name : 'N/A';
                        $val['bank_account_number'] = loanApplicatBankDetail($row->id) ? loanApplicatBankDetail($row->id)->bank_account_number : 'N/A';
                        $val['ifsc_code'] = loanApplicatBankDetail($row->id) ? loanApplicatBankDetail($row->id)->ifsc_code : 'N/A';
                    }
                    $val['reason'] = isset($row->rejection_description) ? $row->rejection_description : 'N/A';
                    $val['status'] = isset($row->status) ? ($statusLabels[$row->status] ?? 'N/A') : 'N/A';
                    $approve_date = isset($row['approved_date']) ? date("d/m/Y", strtotime($row['approved_date'])) : 'N/A';
                    $val['approve_date'] = $approve_date;
                    $val['created_at'] = date("d/m/Y", strtotime($row['created_at']));
                    $val['running_loan_account_number'] = getMemberCurrentRunningLoan($row['customer_id'],$arrFormData['loan_type'] =='L' ? true : false,$row->account_number);
					$val['running_loan_closing_amount'] = getMemberCurrentRunningClosingAmount($row['customer_id'],$arrFormData['loan_type'] =='L' ? true : false,$row->account_number);
                    if ($row['loan']->loan_type == 'G') {
                        $vurl = URL::to("admin/loan/view/" . $row->member_loan_id . "/3");
                        $eurl = URL::to("admin/loan/edit/" . $row->member_loan_id . "");
                        $aurl = URL::to("admin/loan/approve-group-loan/" . $row->member_loan_id . "");
                        $rurl = URL::to("admin/loan/loan-request-reject/" . $row->member_loan_id . "/3");
                        $taurl = URL::to("admin/loan/transfer-group-loan-amount/" . $row->id . "");
                        $turl = URL::to("admin/loan/emi-transactions/" . $row->id . "/G");
                        $purl = URL::to("admin/loan/print/" . $row->id . "/3");
                        $pdf = URL::to("admin/loan/download-recovery-clear/" . $row->id . "/3");
                        $print = URL::to("admin/loan/print-recovery-clear/" . $row->id . "/3");
                        $urlCom = URL::to("admin/loan/commission-group/" . $row->id . "");
                    } else {
                        $vurl = URL::to("admin/loan/view/" . $row->id . "/" . $row->loan_type . "");
                        $eurl = URL::to("admin/loan/edit/" . $row->id . "");
                        $aurl = URL::to("admin/loan/approve/" . $row->id . "");
                        $rurl = URL::to("admin/loan/loan-request-reject/" . $row->id . "/" . $row->loan_type . "");
                        $taurl = URL::to("admin/loan/transfer/" . $row->id . "");
                        $purl = URL::to("admin/loan/print/" . $row->id . "/" . $row->loan_type . "");
                        $turl = URL::to("admin/loan/emi-transactions/" . $row->id . "/" . "L" . "");
                        $urlCom = URL::to("admin/loan/commission/" . $row->id . "");
                        $pdf = URL::to("admin/loan/download-recovery-clear/" . $row->id . "/" . $row->loan_type . "");
                        $print = URL::to("admin/loan/print-recovery-clear/" . $row->id . "/" . $row->loan_type . "");
                    }
                    $loanLogs = URL::to("admin/loan/logs/" . $row->id . "/" . $row->loan_type . "");
                    $changestatus = 0;
                    $appstatus = 1;
                    $dateNew = encrypt($applicationDate);
                    $pendurl = URL::to("admin/loan/pending/" . $row->id . "/" . $row->loan_type . "/" . $changestatus . "/" . $dateNew . "");
                    $apphurl = URL::to("admin/loan/pending/" . $row->id . "/" . $row->loan_type . "/" . $appstatus . "/" . $dateNew . "");
                    // $cibilUrl = URL::to("admin/loan/cibil/" . $member_id );
                    $rowref = $row->ecs_ref_no ??null;
                    $btn = '<div class="list-icons"><div class="dropdown"><a href="#" class="list-icons-item" data-toggle="dropdown"><i class="icon-menu9 mr-2"></i></a><div class="dropdown-menu dropdown-menu-right">';
                    if ($row->status == 0) {
                        if (Auth::user()->id != "13") {
                            $btn .= '<a class="dropdown-item" href="' . $aurl . '"><i class="fas fa-thumbs-up mr-2"></i>Approve</a>';
                            $btn .= '<a class="dropdown-item reject-demand-advice" href="' . $taurl . '" data-toggle="modal" data-target="#formmodal" modal-title = "Cause of Rejection" demandId = "' . $row->id . '" status = "5" loanCategory = "' . $row['loan']->loan_category . '" loanType = "' . $row->loan_type . '" ><i class="fas fa-thumbs-down" ></i>Reject </a>';
                            $btn .= '<a class="dropdown-item reject-demand-advice" href="' . $taurl . '" data-toggle="modal" data-target="#formmodal" modal-title = "Cause of Hold" demandId = "' . $row->id . '" status = "6"  loanCategory = "' . $row['loan']->loan_category . '" loanType = "' . $row->loan_type . '" ><i class="icon-paperplane" ></i>Hold </a>';
                            // $btn .= '<a class="dropdown-item reject-loan" href="' . $rurl . '" data-toggle="modal" data-target="#loan-rejected" loan-id="' . $row->id . '"><i class="fas fa-thumbs-down mr-2"></i>Delete</a>';
                            // $btn .= '<a class="dropdown-item" href="' . $eurl . '"><i class="icon-pencil7 mr-2"></i>Edit</a>';
                        }
                    } elseif ($row->status == 1 && ($row->amount != $row->deposite_amount)) {
                        $ecsConditionTransferbtn = '<a class="dropdown-item" href="' . $taurl . '"><i class="fa fa-exchange mr-2"></i>Transfer Amount</a>';

                        if (Auth::user()->id != "13")
                        {
                                if($row->ecs_type == 0){
                                    $btn .= $ecsConditionTransferbtn;
                                }else{
                                    if($row->ecs_type == 1){
                                        if($row->ecs_ref_no!=null){
                                            $btn .= $ecsConditionTransferbtn;
                                        }else{
                                            $btn .= '';
                                        }
                                    }else{
                                        if(!empty($row->ssb_id)){
                                            $btn .= $ecsConditionTransferbtn;
                                        }else{
                                            $btn .= '';
                                        }
                                    }
                                }
                        }
                        $btn .= '<a class="dropdown-item reject-demand-advice" href="' . $taurl . '" data-toggle="modal" data-target="#formmodal" modal-title = "Cause of Hold" demandId = "' . $row->id . '" status = "7"  loanCategory = "' . $row['loan']->loan_category . '" loanType = "' . $row->loan_type . '" ><i class="icon-paperplane" ></i>Hold </a>';
                        if(check_my_permission(Auth::user()->id, "363") == "1"){
                            $btn .= '<a class="dropdown-item reject-demand-advice" href="' . $taurl . '" data-toggle="modal" data-target="#formmodal" modal-title = "Cause of Cancletion" demandId = "' . $row->id . '" status = "8" loanCategory = "' . $row['loan']->loan_category . '" loanType = "' . $row->loan_type . '" ><i class="fa fa-close mr-2"></i>Cancle </a>';
                        }

                        /** please uncomment below commented code for showing cancle option */
                        // $btn .= '<a class="dropdown-item reject-demand-advice" href="'.$taurl.'" data-toggle="modal" data-target="#formmodal" modal-title = "Cause of Cancletion" demandId = "'.$row->id.'" status = "8" loanCategory = "'.$row['loan']->loan_category.'" loanType = "'.$row->loan_type.'" ><i class="fa fa-close mr-2"></i>Cancle </a>';
                        if($row->ecs_type == 1){
                            $btn .= '<a class="dropdown-item ecsRef" data-toggle="modal" data-target="#exampleModal"  data-id="' . $row->id .'" data-value="'.$rowref.'" ><i class="fas fa-atlas mr-2 ecsRef" data-toggle="modal" data-target="#exampleModal" data-id="' . $row->id .'" id="refId" data-value="'.$rowref.'" ></i>Ecs Register</a>';
                        }
                    } elseif ($row->status == 6 || $row->status == 7) {
                        $btn .= '<a class="dropdown-item" href="' . $apphurl . '"><i class="fas fa-thumbs-up mr-2"></i>Approve</a>';
                        if($row->status !== 7){
                            $btn .= '<a class="dropdown-item" href="' . $pendurl . '"><i class="fas fa-thumbs-up mr-2"></i>Pending</a>';
                        }
                        if($row->ecs_type == 1){
                            $btn .= '<a class="dropdown-item ecsRef" data-toggle="modal" data-target="#exampleModal"  data-id="' . $row->id .'" data-value="'.$rowref.'" ><i class="fas fa-atlas mr-2 ecsRef" data-toggle="modal" data-target="#exampleModal" data-id="' . $row->id .'" id="refId" data-value="'.$rowref.'" ></i>Ecs Register</a>';
                        }
                        if($row->status == 7 && check_my_permission(Auth::user()->id, "363") == "1"){
                            $btn .= '<a class="dropdown-item reject-demand-advice" href="' . $taurl . '" data-toggle="modal" data-target="#formmodal" modal-title = "Cause of Cancletion" demandId = "' . $row->id . '" status = "8" loanCategory = "' . $row['loan']->loan_category . '" loanType = "' . $row->loan_type . '" ><i class="fa fa-close mr-2"></i>Cancle </a>';
                        }
                    }
                    $btn .= '<a class="dropdown-item" href="' . $vurl . '"><i class="fas fa-eye text-default mr-2"></i>View</a>';
                    // $btn .= '<a class="dropdown-item" href="' . $cibilUrl . '"><i class="fas fa-eye text-default mr-2"></i>Get Cibil Report</a>';
                    if (Auth::user()->id != "13") {
                        // $btn .= '<a class="dropdown-item" href="' . $purl . '" target="_blank"><i class="fa fa-print mr-2" aria-hidden="true"></i>Print</a>';
                    }
                    $btn .= '<a class="dropdown-item" href="' . $turl . '"><i class="fas fa-money-bill-wave-alt mr-2" aria-hidden="true"></i>Transactions</a>';
                     $btn .= '<a class="dropdown-item" href="' . $urlCom . '"><i class="fas fa-percent text-default mr-2"></i>Loan Commission</a>';
                    if ($row->status == 3) {
                        if (Auth::user()->id != "13") {
                            $btn .= '<a class="dropdown-item" href="' . $pdf . '"><i class="fas fa-download text-default mr-2"></i>Download No Dues</a>';
                            $btn .= '<a class="dropdown-item" href="' . $print . '" target="_blank"><i class="fas fa-print text-default mr-2" ></i>print No Dues</a>';
                        }
                    }
                    elseif($row->status == 4){
                        if($row->ecs_type == 1){
                            $btn .= '<a class="dropdown-item ecsRef" data-toggle="modal" data-target="#exampleModal" data-id="' . $row->id .'" data-value="'.$rowref.'" ><i class="fas fa-atlas mr-2 ecsRef" data-toggle="modal" data-target="#exampleModal" data-id="' . $row->id .'" id="refId"  data-value="'.$rowref.'" ></i>Ecs Register</a>';
                        }
                    }
                    $btn .= '<a class="dropdown-item" href="' . $loanLogs . '" target="_blank"><i class="fas fa-print text-default mr-2" ></i>Logs</a>';
                    $btn .= '</div></div></div>';
                    $val['action'] = $btn;
                    $rowReturn[] = $val;
                }
                $token = session()->get('_token');
                Cache::put('loan_report_list_exportAdmin' . $token, $dataexport->toArray());
                Cache::put('loan_report_list_export_countAdmin' . $token, $totalCount);
                $output = array(
                    "draw" => $_POST['draw'],
                    "recordsTotal" => $totalCount,
                    "recordsFiltered" => $totalCount,
                    "data" => $rowReturn,
                );
                return json_encode($output);
            } else {
                $output = array(
                    "draw" => 0,
                    "recordsTotal" => 0,
                    "recordsFiltered" => 0,
                    "data" => 0,
                );
                return json_encode($output);
            }
        }
    }
    /**
     * Group Loan requests listing view.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function groupLoanRequest(Request $request)
    {
        if (check_my_permission(Auth::user()->id, "27") != "1") {
            return redirect()
                ->route('admin.dashboard');
        }
        $data['title'] = 'Group Loan Registration Details';
        return view('templates.admin.loan.group-loan-requests', $data);
    }
    /**
     * Show group loan requests listing.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function groupLoanRequestAjax(Request $request)
    {
        if ($request->ajax() && check_my_permission(Auth::user()->id, "27") == "1") {
            $arrFormData = array();
            if (!empty($_POST['searchform'])) {
                foreach ($_POST['searchform'] as $frm_data) {
                    $arrFormData[$frm_data['name']] = $frm_data['value'];
                }
            }
            $data = Grouploans::select('id', 'group_activity', 'groupleader_member_id', 'applicant_id', 'account_number', 'group_loan_common_id', 'approve_date', 'emi_option', 'emi_period', 'deposite_amount', 'transfer_amount', 'amount', 'file_charges', 'status', 'member_loan_id', 'member_id', 'associate_member_id', 'branch_id', 'created_at', 'approved_date')
                ->with([
                    'loanMember' => function ($q) {
                        $q->select('id', 'member_id', 'first_name', 'last_name');
                    }
                ])->with([
                        'loanMemberAssociate' => function ($q) {
                            $q->select('id', 'member_id', 'first_name', 'last_name', 'associate_no');
                        }
                    ])->with([
                        'groupleaderMemberIDCustom' => function ($q) {
                            $q->select('id', 'member_id', 'first_name', 'last_name', 'associate_no');
                        }
                    ])->with([
                        'MemberApplicantCustom' => function ($q) {
                            $q->select('id', 'member_id', 'first_name', 'last_name', 'associate_no');
                        }
                    ])->with([
                        'gloanBranch' => function ($query) {
                            $query->select('id', 'name', 'branch_code', 'sector', 'regan', 'zone');
                        },
                        'loanMemberBankDetails2' => function ($q) {
                            $q->select('id', 'member_id', 'bank_name', 'account_no', 'ifsc_code');
                        }
                    ])->whereIn('status', [0, 1, 3])->where('loan_type', $arrFormData['group_loan_plan'])->where('company_id', $arrFormData['company_id']);

            if (Auth::user()->branch_id > 0) {
                $id = Auth::user()->branch_id;
                $data = $data->where('branch_id', '=', $id);
            }
            if (isset($arrFormData['is_search']) && $arrFormData['is_search'] == 'yes') {
                if ($arrFormData['date_from'] != '') {
                    $startDate = date("Y-m-d", strtotime(convertDate($arrFormData['date_from'])));
                    if ($arrFormData['date_to'] != '') {
                        $endDate = date("Y-m-d ", strtotime(convertDate($arrFormData['date_to'])));
                    } else {
                        $endDate = '';
                    }
                    $data = $data->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate]);
                }
                if (isset($arrFormData['branch_id']) && $arrFormData['branch_id'] != '') {
                    $branch_id = $arrFormData['branch_id'];
                    $data = $data->where('branch_id', '=', $branch_id);
                }
                if ($arrFormData['application_number'] != '') {
                    $application_number = $arrFormData['application_number'];
                    $data = $data->where('account_number', '=', $application_number);
                }
                if ($arrFormData['member_name'] != '') {
                    $name = $arrFormData['member_name'];
                    $data = $data->select('id', 'member_id', 'first_name', 'last_name')->whereHas('loanMember', function ($query) use ($name) {
                        $query->select('id', 'member_id', 'first_name', 'last_name')->where('members.first_name', 'LIKE', '%' . $name . '%')->orWhere('members.last_name', 'LIKE', '%' . $name . '%')->orWhere(DB::raw('concat(members.first_name," ",members.last_name)'), 'LIKE', "%$name%");
                    });
                }
                if ($arrFormData['member_id'] != '') {
                    $meid = $arrFormData['member_id'];
                    $data = $data->select('id', 'member_id', 'first_name', 'last_name')->whereHas('loanMember', function ($query) use ($meid) {
                        $query->select('id', 'member_id', 'first_name', 'last_name')->where('members.member_id', 'LIKE', '%' . $meid . '%');
                    });
                }
                if ($arrFormData['associate_code'] != '') {
                    $associateCode = $arrFormData['associate_code'];
                    $data = $data->select('id', 'associate_no', 'first_name', 'last_name')->whereHas('loanMemberAssociate', function ($query) use ($associateCode) {
                        $query->select('id', 'associate_no', 'first_name', 'last_name')->where('members.associate_no', 'LIKE', '%' . $associateCode . '%');
                    });
                }
                if ($arrFormData['status'] != '') {
                    $status = $arrFormData['status'];
                    $data = $data->where('status', '=', $status);
                }
            }
            $totalCount = $data->count('id');
            $data = $data->orderby('id', 'DESC')->offset($_POST['start'])->limit($_POST['length'])->get();
            //$count = count($data);
            // $totalCount = Grouploans::with('loanMember', 'loanMemberAssociate')->with(['gloanBranch' => function ($query){
            //     $query->select('id', 'name', 'branch_code', 'sector', 'regan', 'zone'); } ])->count("id");
            $sno = $_POST['start'];
            $rowReturn = array();
            foreach ($data as $row) {
                $sno++;
                $val['DT_RowIndex'] = $sno;
                $val['applicant_id'] = ($row->group_activity == 'Group loan application') ? ($row->groupleader_member_id ? $row['groupleaderMemberIDCustom']->member_id : 'N/A') : ($row->applicant_id ? $row['MemberApplicantCustom']->member_id : 'N/A');
                $val['group_loan_id'] = $row->group_loan_common_id ?? 'N/A';
                $val['application_number'] = $row->account_number ?? 'N/A';
                $val['branch'] = $row['gloanBranch']->name;
                $val['branch_code'] = $row['gloanBranch']->branch_code;
                $val['sector'] = $row['gloanBranch']->sector;
                $val['region'] = $row['gloanBranch']->regan;
                $val['zone'] = $row['gloanBranch']->zone;
                $val['member_id'] = $row['loanMember']->member_id;
                $val['member_name'] = $row['loanMember']->first_name . ' ' . $row['loanMember']->last_name;
                switch ($row->emi_option) {
                    case 1:
                        $last_recovery_date = date('d/m/Y', strtotime("+" . $row->emi_period . " months", strtotime($row->approve_date)));
                        break;
                    case 2:
                        $days = $row->emi_period * 7;
                        $start_date = $row->approve_date;
                        $date = strtotime($start_date);
                        $date = strtotime("+" . $days . " day", $date);
                        $last_recovery_date = date('d/m/Y', $date);
                        break;
                    case 3:
                        $days = $row->emi_period;
                        $start_date = $row->approve_date;
                        $date = strtotime($start_date);
                        $date = strtotime("+" . $days . " day", $date);
                        $last_recovery_date = date('d/m/Y', $date);
                        break;
                    default:
                        $last_recovery_date = 'N/A';
                        break;
                }
                $val['last_recovery_date'] = $last_recovery_date;
                $val['associate_code'] = $row['loanMemberAssociate']->associate_no; //getMemberData($row->associate_member_id)->associate_no;
                $member = $row['loanMemberAssociate']; //Member::where('id', $row->associate_member_id)->first(['id', 'first_name', 'last_name']);
                $val['associate_name'] = $member->first_name . ' ' . $member->last_name;
                $val['loan'] = 'Group Loan';
                $val['amount'] = $row->transfer_amount . ' <i class="fa fa-inr"></i>';
                $val['loan_amount'] = $row->amount . ' <i class="fas fa-rupee-sign"></i>';
                $val['file_charge'] = $row->file_charges . ' <i class="fas fa-rupee-sign"></i>';
                $val['bank_name'] = $row['loanMemberBankDetails2'] ? $row['loanMemberBankDetails2']->bank_name ?? 'N/A' : 'N/A';
                $val['bank_account_number'] = $row['loanMemberBankDetails2'] ? $row['loanMemberBankDetails2']->account_no ?? 'N/A' : 'N/A';
                $val['ifsc_code'] = $row['loanMemberBankDetails2'] ? $row['loanMemberBankDetails2']->ifsc_code ?? 'N/A' : 'N/A';
                $status = [
                    0 => 'Pending',
                    1 => 'Approved',
                    2 => 'Rejected',
                    3 => 'Clear',
                    4 => 'Due',
                    5 => 'Rejected',
                    6 => 'Hold',
                ];
                $val['status'] = $status[$row->status] ?? '';
                $approve_date = $row['approved_date'] ? date("d/m/Y", strtotime($row['approved_date'])) : 'N/A';
                $val['approve_date'] = $approve_date;
                $val['created_at'] = date("d/m/Y", strtotime($row['created_at']));
                $btn = '';
                $vurl = URL::to("admin/loan/view/" . $row->member_loan_id . "/3");
                $eurl = URL::to("admin/loan/edit/" . $row->member_loan_id . "");
                $aurl = URL::to("admin/loan/approve-group-loan/" . $row->member_loan_id . "");
                $rurl = URL::to("admin/loan/loan-request-reject/" . $row->member_loan_id . "/3");
                $taurl = URL::to("admin/loan/transfer-group-loan-amount/" . $row->id . "");
                $turl = URL::to("admin/loan/emi-transactions/" . $row->id . "/3");
                $purl = URL::to("admin/loan/print/" . $row->id . "/3");
                $pdf = URL::to("admin/loan/download-recovery-clear/" . $row->id . "/3");
                $print = URL::to("admin/loan/print-recovery-clear/" . $row->id . "/3");
                $urlCom = URL::to("admin/loan/commission-group/" . $row->id . "");
                $btn .= '<div class="list-icons"><div class="dropdown"><a href="#" class="list-icons-item" data-toggle="dropdown"><i class="icon-menu9"></i></a><div class="dropdown-menu dropdown-menu-right">';
                if ($row->status == 0) {
                    if (Auth::user()->id != "13") {
                        $btn .= '<a class="dropdown-item" href="' . $aurl . '"><i class="fas fa-thumbs-up"></i>Approve</a>';
                        $btn .= '<a class="dropdown-item reject-loan" href="' . $rurl . '" data-toggle="modal" data-target="#loan-rejected" loan-id="' . $row->id . '"><i class="fas fa-thumbs-down"></i>Delete</a>';
                        $btn .= '<a class="dropdown-item" href="' . $eurl . '"><i class="icon-pencil7 mr-2"></i>Edit</a>';
                    }
                } elseif ($row->status == 1 && ($row->amount != $row->deposite_amount)) {
                    if (Auth::user()->id != "13") {
                        $btn .= '<a class="dropdown-item" href="' . $taurl . '"><i class="fa fa-exchange"></i>Transfer Amount</a>';
                    }
                }
                $btn .= '<a class="dropdown-item" href="' . $vurl . '"><i class="fas fa-eye text-default mr-2"></i>View</a>';
                if (Auth::user()->id != "13") {
                    $btn .= '<a class="dropdown-item" href="' . $purl . '" target="_blank"><i class="fa fa-print mr-2" aria-hidden="true"></i>Print</a>';
                }
                $btn .= '<a class="dropdown-item" href="' . $turl . '"><i class="fas fa-money-bill-wave-alt mr-2" aria-hidden="true"></i>Transactions</a>';
                $btn .= '<a class="dropdown-item" href="' . $urlCom . '"><i class="fas fa-percent text-default mr-2"></i>Loan Commission</a>';
                if ($row->status == 3) {
                    if (Auth::user()->id != "13") {
                        $btn .= '<a class="dropdown-item" href="' . $pdf . '"><i class="fas fa-download text-default mr-2"></i>Download No Dues</a>';
                        $btn .= '<a class="dropdown-item" href="' . $print . '" target="_blank"><i class="fas fa-print text-default mr-2" ></i>print No Dues</a>';
                    }
                }
                $btn .= '</div></div></div>';
                $val['action'] = $btn;
                $rowReturn[] = $val;
            }
            $output = array(
                "draw" => $_POST['draw'],
                "recordsTotal" => $totalCount,
                "recordsFiltered" => $totalCount,
                "data" => $rowReturn,
            );
            return json_encode($output);
        }
    }
    /**
     * Display created member loan by id.
     *
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function editLoan($id)
    {
        $data['title'] = "Loan Edit";
        $data['loans'] = Loans::all();
        $data['loanDetails'] = Memberloans::with('loan', 'LoanApplicants', 'LoanCoApplicants', 'LoanGuarantor', 'Loanotherdocs', 'GroupLoanMembers', 'loanInvestmentPlans')->findOrFail($id);
        $data['id'] = $id;
        return view('templates.admin.loan.edit_member_loan', $data);
    }
    /**
     * Update the specified loan.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function updateLoan(Request $request)
    {
        $loantype = $request->input('loan_type');
        /*switch ($loantype) {
          case "personal-loan":
            $rules = [
                'loan' => 'required',
                'amount' => 'required',
                'days' => 'required',
                'months' => 'required',
                'acc_member_id' => 'required',
                //'applicant_id' => 'required',
                'applicant_member_id' => 'required',
                'applicant_address_permanent' => 'required',
                'applicant_address_temporary' => 'required',
                'applicant_occupation' => 'required',
                'applicant_organization' => 'required',
                'applicant_designation' => 'required',
                'applicant_monthly_income' => 'required|integer',
                'applicant_year_from' => 'required|integer',
                'applicant_bank_name' => 'required',
                'applicant_bank_account_number' => 'required|integer',
                'applicant_ifsc_code' => 'required|integer',
                'applicant_cheque_number_1' => 'required|integer',
                'applicant_cheque_number_2' => 'required|integer',
                'applicant_id_proof' => 'required',
                'applicant_id_number' => 'required',
                'applicant_address_id_proof' => 'required',
                'applicant_address_id_number' => 'required',
                'applicant_income' => 'required',
                'applicant_security' => 'required',
                'co-applicant_member_id' => 'required',
                'co-applicant_address_permanent' => 'required',
                'co-applicant_address_temporary' => 'required',
                'co-applicant_occupation' => 'required',
                'co-applicant_organization' => 'required',
                'co-applicant_designation' => 'required',
                'co-applicant_monthly_income' => 'required|integer',
                'co-applicant_year_from' => 'required|integer',
                'co-applicant_bank_name' => 'required',
                'co-applicant_bank_account_number' => 'required|integer',
                'co-applicant_ifsc_code' => 'required|integer',
                'co-applicant_cheque_number_1' => 'required|integer',
                'co-applicant_cheque_number_2' => 'required|integer',
                'co-applicant_id_proof' => 'required',
                'co-applicant_id_number' => 'required',
                'co-applicant_address_id_proof' => 'required',
                'co-applicant_address_id_number' => 'required',
                'co-applicant_income' => 'required',
                'co-applicant_security' => 'required',
                'guarantor_member_id' => 'required',
                'guarantor_name' => 'required',
                'guarantor_father_name' => 'required',
                'guarantor_dob' => 'required',
                'guarantor_marital_status' => 'required',
                'local_address' => 'required',
                'guarantor_ownership' => 'required',
                'guarantor_temporary_address' => 'required',
                'guarantor_mobile_number' => 'required|integer',
                'guarantor_educational_qualification' => 'required',
                'guarantor_dependents_number' => 'required',
                'guarantor_occupation' => 'required',
                'guarantor_organization' => 'required',
                'guarantor_designation' => 'required',
                'guarantor_monthly_income' => 'required|integer',
                'guarantor_year_from' => 'required|integer',
                'guarantor_bank_name' => 'required',
                'guarantor_bank_account_number' => 'required|integer',
                'guarantor_ifsc_code' => 'required|integer',
                'guarantor_cheque_number_1' => 'required|integer',
                'guarantor_cheque_number_2' => 'required|integer',
                'guarantor_id_proof' => 'required',
                'guarantor_id_number' => 'required',
                'guarantor_address_id_proof' => 'required',
                'guarantor_address_id_number' => 'required',
                'guarantor_income' => 'required',
                'guarantor_security' => 'required',
            ];
            break;
        }
        $customMessages = [
            'acc_member_id.required' => 'The accociate member id field is required.',
            'required' => 'The :attribute field is required.'
        ];
        $this->validate($request, $rules, $customMessages);*/
        $type = 'update';
        $loanId = $request->input('loanId');
        $data = $this->getData($request->all(), $type);
        $memberLoan = Memberloans::find($loanId);
        $data['edit_reject_request'] = $request->input('edit_reject_request');
        $memberLoan->update($data);
        DB::beginTransaction();
        try {
            switch ($loantype) {
                case "1":
                    $applicantIdFile = $request->file('applicant_id_file');
                    $applicantAddressFile = $request->file('applicant_address_id_file');
                    $applicantIncomeFile = $request->file('applicant_income_file');
                    $applicant = $this->applicantDetailData($request->all(), $applicantIdFile, $applicantAddressFile, $applicantIncomeFile, $loanId, $type);
                    $applicantId = $request->input('applicant_id');
                    $applicanRes = Loanapplicantdetails::find($applicantId);
                    $applicanRes->update($applicant);
                    $coapplicantIdFile = $request->file('co-applicant_id_file');
                    $coapplicantAddressFile = $request->file('co-applicant_address_id_file');
                    $coapplicantIncomeFile = $request->file('co-applicant_income_file');
                    $coapplicantUnderTakingDoc = $request->file('co-applicant_under_taking_doc');
                    //if($request['co_applicant_checkbox'] != ''){
                    $coApplicant = $this->coApplicantDetailData($request->all(), $coapplicantIdFile, $coapplicantAddressFile, $coapplicantIncomeFile, $coapplicantUnderTakingDoc, $loanId, $type);
                    $coapplicantId = $request->input('coapplicant_id');
                    $coapplicanRes = Loanscoapplicantdetails::find($coapplicantId);
                    $coapplicanRes->update($coApplicant);
                    //}
                    $guarantorIdFile = $request->file('guarantor_id_file');
                    $guarantorAddressFile = $request->file('guarantor_address_id_file');
                    $guarantorIncomeFile = $request->file('guarantor_income_file');
                    $guarantorOtherFile = $request->file('guarantor_more_upload_file');
                    //$guarantorUnderTakingDoc = $request->file('guarantor_under_taking_doc');
                    $guarantor = $this->guarantorDetailData($request->all(), $guarantorIdFile, $guarantorAddressFile, $guarantorIncomeFile, $guarantorOtherFile, $loanId, $type);
                    $guarantorId = $request->input('guarantor_id');
                    $guarantorRes = Loansguarantordetails::find($guarantorId);
                    $guarantorRes->update($guarantor);
                    if ($request['hidden_more_doc'] == 1 && !empty($request->file('guarantor_more_upload_file'))) {
                        $guarantorMoreDoc = $this->uploadGuarantorMoreDoc($request->all(), $request['guarantor_more_doc_title'], $request->file('guarantor_more_upload_file'), $loanId, 'guarantor', 'moredocument', $type);
                    }
                    break;
                case "2":
                    $applicantIdFile = $request->file('applicant_id_file');
                    $applicantAddressFile = $request->file('applicant_address_id_file');
                    $applicantIncomeFile = $request->file('applicant_income_file');
                    $applicant = $this->applicantDetailData($request->all(), $applicantIdFile, $applicantAddressFile, $applicantIncomeFile, $loanId, $type);
                    $applicantId = $request->input('applicant_id');
                    $applicanRes = Loanapplicantdetails::find($applicantId);
                    $applicanRes->update($applicant);
                    $coapplicantIdFile = $request->file('co-applicant_id_file');
                    $coapplicantAddressFile = $request->file('co-applicant_address_id_file');
                    $coapplicantIncomeFile = $request->file('co-applicant_income_file');
                    if ($request['co_applicant_checkbox'] != '') {
                        $coApplicant = $this->coApplicantDetailData($request->all(), $coapplicantIdFile, $coapplicantAddressFile, $coapplicantIncomeFile, $loanId, $type);
                        $coapplicantId = $request->input('coapplicant_id');
                        $coapplicanRes = Loanscoapplicantdetails::find($coapplicantId);
                        $coapplicanRes->update($coApplicant);
                    }
                    $guarantorIdFile = $request->file('guarantor_id_file');
                    $guarantorAddressFile = $request->file('guarantor_address_id_file');
                    $guarantorIncomeFile = $request->file('guarantor_income_file');
                    $guarantorOtherFile = $request->file('guarantor_more_upload_file');
                    //$guarantorUnderTakingDoc = $request->file('guarantor_under_taking_doc');
                    $guarantor = $this->guarantorDetailData($request->all(), $guarantorIdFile, $guarantorAddressFile, $guarantorIncomeFile, $guarantorOtherFile, $loanId, $type);
                    $guarantorId = $request->input('guarantor_id');
                    $guarantorRes = Loansguarantordetails::find($guarantorId);
                    $guarantorRes->update($guarantor);
                    if ($request['hidden_more_doc'] == 1 && !empty($request->file('guarantor_more_upload_file'))) {
                        $guarantorMoreDoc = $this->uploadGuarantorMoreDoc($request->all(), $request['guarantor_more_doc_title'], $request->file('guarantor_more_upload_file'), $loanId, 'guarantor', 'moredocument', $type);
                    }
                    break;
                case "3":
                    $applicantIdFile = $request->file('applicant_id_file');
                    $applicantAddressFile = $request->file('applicant_address_id_file');
                    $applicantIncomeFile = $request->file('applicant_income_file');
                    $applicant = $this->applicantDetailData($request->all(), $applicantIdFile, $applicantAddressFile, $applicantIncomeFile, $loanId, $type);
                    $applicantId = $request->input('applicant_id');
                    $applicanRes = Loanapplicantdetails::find($applicantId);
                    $applicanRes->update($applicant);
                    $guarantorIdFile = $request->file('guarantor_id_file');
                    $guarantorAddressFile = $request->file('guarantor_address_id_file');
                    $guarantorIncomeFile = $request->file('guarantor_income_file');
                    $guarantorOtherFile = $request->file('guarantor_more_upload_file');
                    //$guarantorUnderTakingDoc = $request->file('guarantor_under_taking_doc');
                    $guarantor = $this->guarantorDetailData($request->all(), $guarantorIdFile, $guarantorAddressFile, $guarantorIncomeFile, $guarantorOtherFile, $loanId, $type);
                    $guarantorId = $request->input('guarantor_id');
                    $guarantorRes = Loansguarantordetails::find($guarantorId);
                    $guarantorRes->update($guarantor);
                    if ($request['hidden_more_doc'] == 1 && !empty($request->file('guarantor_more_upload_file'))) {
                        $guarantorMoreDoc = $this->uploadGuarantorMoreDoc($request->all(), $request['guarantor_more_doc_title'], $request->file('guarantor_more_upload_file'), $loanId, 'guarantor', 'moredocument', $type);
                    }
                    break;
                case "4":
                    $applicantIdFile = $request->file('applicant_id_file');
                    $applicantAddressFile = $request->file('applicant_address_id_file');
                    $applicantIncomeFile = $request->file('applicant_income_file');
                    $guarantorOtherFile = $request->file('guarantor_more_upload_file');
                    $applicant = $this->applicantDetailData($request->all(), $applicantIdFile, $applicantAddressFile, $applicantIncomeFile, $guarantorOtherFile, $request->input('loanId'), $type);
                    $applicantId = $request->input('applicant_id');
                    $applicanRes = Loanapplicantdetails::find($applicantId);
                    $applicanRes->update($applicant);
                    break;
            }
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('alert', $ex->getMessage().$ex->getLine());
        }
        return back()
            ->with('success', 'Update was Successful!');
        /*if ($applicanRes) {
            return back()->with('success', 'Update was Successful!');
        } else {
            return back()->with('alert', 'An error occured');
        }*/
    }
    /**
     * upload more documents to store.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function uploadGuarantorMoreDoc($request, $moredoctitles, $moredocfiles, $loanId, $folder, $moredocfolder, $type)
    {
        $mainFolder = storage_path() . '/images/loan/document/' . $loanId;
        File::makeDirectory($mainFolder, $mode = 0777, true, true);
        $loanTypeFolder = $mainFolder . '/' . $folder;
        File::makeDirectory($loanTypeFolder, $mode = 0777, true, true);
        $loanTypeProffFolder = $loanTypeFolder . '/' . $moredocfolder . '/';
        //File::makeDirectory($loanTypeProffFolder, $mode = 0777, true, true);
        foreach ($moredoctitles as $key => $value) {
            if (array_key_exists($key, $moredocfiles) && $request['hidden_other_doc_file_id'][$key]) {
                $file = $moredocfiles[$key];
                $uploadFile = $file->getClientOriginalName();
                $filename = pathinfo($uploadFile, PATHINFO_FILENAME);
                $fname = $filename . '_' . time() . $key . '.' . $file->getClientOriginalExtension();
                $file->move($loanTypeProffFolder, $fname);
                $data = [
                    'file_name' => $fname,
                    'file_path' => $loanTypeProffFolder,
                    'file_extension' => $file->getClientOriginalExtension(),
                    'created_at' => $request['created_at'],
                ];
                $fileRes = Files::find($request['hidden_other_doc_file_id'][$key]);
                $fileRes->update($data);
                $filesId = $request['hidden_other_doc_file_id'][$key];
            } elseif (array_key_exists($key, $moredocfiles) && $request['hidden_other_doc_file_id'][$key] == '') {
                $file = $moredocfiles[$key];
                $uploadFile = $file->getClientOriginalName();
                $filename = pathinfo($uploadFile, PATHINFO_FILENAME);
                $fname = $filename . '_' . time() . $key . '.' . $file->getClientOriginalExtension();
                $file->move($loanTypeProffFolder, $fname);
                $data = [
                    'file_name' => $fname,
                    'file_path' => $loanTypeProffFolder,
                    'file_extension' => $file->getClientOriginalExtension(),
                    'created_at' => $request['created_at'],
                ];
                $res = Files::create($data);
                $fId = $res->id;
                $loanOtherData = [
                    'member_loan_id' => $loanId,
                    'title' => $value,
                    'file_id' => $fId,
                    'created_at' => $request['created_at'],
                ];
                $res = Loanotherdocs::create($loanOtherData);
            }
        }
    }
    /**
     * Get investment plans data to store.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getData($request, $type)
    {
        $loantype = $request['loan_type'];
        switch ($loantype) {
            case "personal-loan":
                break;
            case "staff-loan":
                /*$data['emi_mode_in_month'] = $request['staff_emi_mode'];
                $data['group_activity'] = $request['group_activity'];
                $data['group_member_id'] = $request['group_leader_member_id'];
                $data['number_of_member'] = $request['number_of_member'];*/
                break;
            case "group-loan":
                $data['group_activity'] = $request['group_activity'];
                $data['groupleader_member_id'] = $request['group_leader_m_id'];
                $data['group_associate_id'] = $request['group_associate_id'];
                $data['number_of_member'] = $request['number_of_member'];
                $data['group_member_id'] = $request['group_member_id'];
                break;
        }
        if ($type == 'create') {
            $data['branch_id'] = $request['branch_id'];
            $data['emi_option'] = $request['emi_option'];
            $data['emi_period'] = $request['emi_period'];
            $data['emi_amount'] = $request['loan_emi'];
            $data['file_charges'] = $request['file_charge'];
            $data['loan_purpose'] = $request['purpose'];
            $data['amount'] = $request['amount'];
            $data['loan_type'] = $request['loan'];
        }
        $data['associate_member_id'] = $request['acc_member_id'];
        $data['applicant_id'] = $request['applicant_member_id'];
        //$data['emi_mode'] = $request['emi_mode'];
        $data['bank_account'] = $request['bank_account'];
        $data['ifsc_code'] = $request['ifsc_code'];
        $data['bank_name'] = $request['bank_name'];
        $data['created_at'] = $request['created_at'];
        return $data;
    }
    /**
     * Get loan applicant data to store.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function applicantDetailData($request, $idFile, $addressFile, $incomeFile, $loanId, $type)
    {
        $folder = 'applicant';
        $data['member_loan_id'] = $request['loanId'];
        $data['member_id'] = $request['acc_member_id'];
        $data['address_permanent'] = $request['applicant_address_permanent'];
        $data['temporary_permanent'] = $request['applicant_address_temporary'];
        //$data['occupation'] = $request['applicant_occupation'];
        $data['organization'] = $request['applicant_organization'];
        $data['designation'] = $request['applicant_designation'];
        $data['monthly_income'] = $request['applicant_monthly_income'];
        $data['year_from'] = $request['applicant_year_from'];
        $data['bank_name'] = $request['applicant_bank_name'];
        $data['bank_account_number'] = $request['applicant_bank_account_number'];
        $data['ifsc_code'] = $request['applicant_ifsc_code'];
        $data['cheque_number_1'] = $request['applicant_cheque_number_1'];
        $data['cheque_number_2'] = $request['applicant_cheque_number_2'];
        if ($type == 'update' && $idFile) {
            $hiddenFileId = $request['hidden_applicant_file_id'];
            $fileId = $this->updateUploadStoreImage($idFile, $loanId, $folder, 'id_proof', $hiddenFileId, $request['created_at']);
        } elseif ($type == 'update' && $idFile == '') {
            $fileId = $request['hidden_applicant_file_id'];
        } elseif ($type == 'create' && $idFile) {
            $fileId = $this->uploadStoreImage($idFile, $loanId, $folder, 'id_proof', $request['created_at']);
        }
        if (empty($fileId)) {
            $fileId = null;
        }
        $data['id_proof_type'] = $request['applicant_id_proof'];
        $data['id_proof_number'] = $request['applicant_id_number'];
        $data['id_proof_file_id'] = $fileId;
        if ($type == 'update' && $addressFile) {
            $hiddenFileId = $request['hidden_applicant_address_file_id'];
            $addressFileId = $this->updateUploadStoreImage($addressFile, $loanId, $folder, 'address_proof', $hiddenFileId, $request['created_at']);
        } elseif ($type == 'update' && $addressFile == '') {
            $addressFileId = $request['hidden_applicant_address_file_id'];
        } elseif ($type == 'create' && $addressFile) {
            $addressFileId = $this->uploadStoreImage($addressFile, $loanId, $folder, 'address_proof', $request['created_at']);
        }
        if (empty($addressFileId)) {
            $addressFileId = null;
        }
        $data['address_proof_type'] = $request['applicant_address_id_proof'];
        $data['address_proof_id_number'] = $request['applicant_address_id_number'];
        $data['address_proof_file_id'] = $addressFileId;
        if ($type == 'update' && $incomeFile) {
            $hiddenFileId = $request['hidden_applicant_income_file_id'];
            $incomeFileId = $this->updateUploadStoreImage($incomeFile, $loanId, $folder, 'income_proof', $hiddenFileId, $request['created_at']);
        } elseif ($type == 'update' && $incomeFile == '') {
            $incomeFileId = $request['hidden_applicant_income_file_id'];
        } elseif ($type == 'create' && $incomeFile) {
            $incomeFileId = $this->uploadStoreImage($incomeFile, $loanId, $folder, 'income_proof', $request['created_at']);
        }
        if (empty($incomeFileId)) {
            $incomeFileId = null;
        }
        $data['income_type'] = $request['applicant_income'];
        if ($data['income_type'] == 2) {
            $data['income_remark'] = $request['applicant_remark'];
        }
        $data['income_file_id'] = $incomeFileId;
        $data['security'] = $request['applicant_security'];
        $data['created_at'] = $request['created_at'];
        return $data;
    }
    /**
     * Get loan co-applicant data to store.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function coApplicantDetailData($request, $idFile, $addressFile, $incomeFile, $undertakingFile, $loanId, $type)
    {
        $folder = 'coapplicant';
        $data['member_loan_id'] = $loanId;
        $data['member_id'] = $request['co-applicant_member_id'];
        $data['address_permanent'] = $request['co-applicant_address_permanent'];
        $data['temporary_permanent'] = $request['co-applicant_address_temporary'];
        //$data['occupation'] = $request['co-applicant_occupation'];
        $data['organization'] = $request['co-applicant_organization'];
        $data['designation'] = $request['co-applicant_designation'];
        $data['monthly_income'] = $request['co-applicant_monthly_income'];
        $data['year_from'] = $request['co-applicant_year_from'];
        $data['bank_name'] = $request['co-applicant_bank_name'];
        $data['bank_account_number'] = $request['co-applicant_bank_account_number'];
        $data['ifsc_code'] = $request['co-applicant_ifsc_code'];
        $data['cheque_number_1'] = $request['co-applicant_cheque_number_1'];
        $data['cheque_number_2'] = $request['co-applicant_cheque_number_2'];
        if ($type == 'update' && $idFile) {
            $hiddenFileId = $request['hidden_coapplicant_file_id'];
            $fileId = $this->updateUploadStoreImage($idFile, $loanId, $folder, 'id_proof', $hiddenFileId, $request['created_at']);
        } elseif ($type == 'update' && $idFile == '') {
            $fileId = $request['hidden_coapplicant_file_id'];
        } elseif ($type == 'create' && $idFile) {
            $fileId = $this->uploadStoreImage($idFile, $loanId, $folder, 'id_proof', $request['created_at']);
        }
        if (empty($fileId)) {
            $fileId = null;
        }
        $data['id_proof_type'] = $request['co-applicant_id_proof'];
        $data['id_proof_number'] = $request['co-applicant_id_number'];
        $data['id_proof_file_id'] = $fileId;
        if ($type == 'update' && $addressFile) {
            $hiddenFileId = $request['hidden_coapplicant_address_file_id'];
            $addressFileId = $this->updateUploadStoreImage($addressFile, $loanId, $folder, 'address_proof', $hiddenFileId, $request['created_at']);
        } elseif ($type == 'update' && $addressFile == '') {
            $addressFileId = $request['hidden_coapplicant_address_file_id'];
        } elseif ($type == 'create' && $addressFile) {
            $addressFileId = $this->uploadStoreImage($addressFile, $loanId, $folder, 'address_proof', $request['created_at']);
        }
        if (empty($addressFileId)) {
            $addressFileId = null;
        }
        $data['address_proof_type'] = $request['co-applicant_address_id_proof'];
        $data['address_proof_id_number'] = $request['co-applicant_address_id_number'];
        $data['address_proof_file_id'] = $addressFileId;
        if ($type == 'update' && $incomeFile) {
            $hiddenFileId = $request['hidden_coapplicant_income_file_id'];
            $incomeFileId = $this->updateUploadStoreImage($incomeFile, $loanId, $folder, 'income_proof', $hiddenFileId, $request['created_at']);
        } elseif ($type == 'update' && $incomeFile == '') {
            $incomeFileId = $request['hidden_coapplicant_income_file_id'];
        } elseif ($type == 'create' && $incomeFile) {
            $incomeFileId = $this->uploadStoreImage($incomeFile, $loanId, $folder, 'income_proof', $request['created_at']);
        }
        if (empty($incomeFileId)) {
            $incomeFileId = null;
        }
        $data['income_type'] = $request['co-applicant_income'];
        /*if($data['income_type']==2){
            $data['income_remark'] = $request['co_applicant_remark'];
        }*/
        $data['income_file_id'] = $incomeFileId;
        $data['security'] = $request['co-applicant_security'];
        /*$moreFileId = $this->uploadStoreImage($incomeFile,$loanId,$folder,'other');
        $data['more_doc_title'] = $request['fn_mobile_number'];
        $data['more_doc_file_id'] = $moreFileId;*/
        if ($type == 'update' && $undertakingFile) {
            $hiddenFileId = $request['hidden_guarantor_income_file_id'];
            $undertakingFileId = $this->updateUploadStoreImage($undertakingFile, $loanId, $folder, 'undertakingdoc', $hiddenFileId, $request['created_at']);
        } elseif ($type == 'update' && $undertakingFile == '') {
            $undertakingFileId = $request['hidden_guarantor_income_file_id'];
        } elseif ($type == 'create' && $undertakingFile) {
            $undertakingFileId = $this->uploadStoreImage($undertakingFile, $loanId, $folder, 'undertakingdoc', $request['created_at']);
        }
        if (empty($undertakingFileId)) {
            $undertakingFileId = null;
        }
        $data['under_taking_doc'] = $undertakingFileId;
        $data['created_at'] = $request['created_at'];
        return $data;
    }
    /**
     * Get loan guarantor data to store.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function guarantorDetailData($request, $idFile, $addressFile, $incomeFile, $guarantorOtherFile, $loanId, $type)
    {
        $folder = 'guarantor';
        $data['member_loan_id'] = $loanId;
        $data['member_id'] = $request['guarantor_member_id'];
        $data['name'] = $request['guarantor_name'];
        $data['father_name'] = $request['guarantor_father_name'];
        $data['dob'] = date("Y-m-d", strtotime(convertDate($request['guarantor_dob'])));
        $data['marital_status'] = $request['guarantor_marital_status'];
        $data['local_address'] = preg_replace("/\r|\n/", "", $request['local_address']);
        $data['ownership'] = $request['guarantor_ownership'];
        $data['temporary_permanent'] = preg_replace("/\r|\n/", "", $request['guarantor_temporary_address']);
        $data['mobile_number'] = $request['guarantor_mobile_number'];
        $data['educational_qualification'] = $request['guarantor_educational_qualification'];
        $data['number_of_dependents'] = $request['guarantor_dependents_number'];
        //$data['occupation'] = $request['guarantor_occupation'];
        $data['organization'] = $request['guarantor_organization'];
        $data['designation'] = $request['guarantor_designation'];
        $data['monthly_income'] = $request['guarantor_monthly_income'];
        $data['year_from'] = $request['guarantor_year_from'];
        $data['bank_name'] = $request['guarantor_bank_name'];
        $data['bank_account_number'] = $request['guarantor_bank_account_number'];
        $data['ifsc_code'] = $request['guarantor_ifsc_code'];
        $data['cheque_number_1'] = $request['guarantor_cheque_number_1'];
        $data['cheque_number_2'] = $request['guarantor_cheque_number_2'];
        if ($type == 'update' && $idFile) {
            $hiddenFileId = $request['hidden_guarantor_file_id'];
            $fileId = $this->updateUploadStoreImage($idFile, $loanId, $folder, 'id_proof', $hiddenFileId, $request['created_at']);
        } elseif ($type == 'update' && $idFile == '') {
            $fileId = $request['hidden_guarantor_file_id'];
        } elseif ($type == 'create' && $idFile) {
            $fileId = $this->uploadStoreImage($idFile, $loanId, $folder, 'id_proof', $request['created_at']);
        }
        if (empty($fileId)) {
            $fileId = null;
        }
        $data['id_proof_type'] = $request['guarantor_id_proof'];
        $data['id_proof_number'] = $request['guarantor_id_number'];
        $data['id_proof_file_id'] = $fileId;
        if ($type == 'update' && $addressFile) {
            $hiddenFileId = $request['hidden_guarantor_address_file_id'];
            $addressFileId = $this->updateUploadStoreImage($addressFile, $loanId, $folder, 'address_proof', $hiddenFileId, $request['created_at']);
        } elseif ($type == 'update' && $addressFile == '') {
            $addressFileId = $request['hidden_guarantor_address_file_id'];
        } elseif ($type == 'create' && $addressFile) {
            $addressFileId = $this->uploadStoreImage($addressFile, $loanId, $folder, 'address_proof', $request['created_at']);
        }
        if (empty($addressFileId)) {
            $addressFileId = null;
        }
        $data['address_proof_type'] = $request['guarantor_address_id_proof'];
        $data['address_proof_id_number'] = $request['guarantor_address_id_number'];
        $data['address_proof_file_id'] = $addressFileId;
        if ($type == 'update' && $incomeFile) {
            $hiddenFileId = $request['hidden_guarantor_income_file_id'];
            $incomeFileId = $this->updateUploadStoreImage($incomeFile, $loanId, $folder, 'income_proof', $hiddenFileId, $request['created_at']);
        } elseif ($type == 'update' && $incomeFile == '') {
            $incomeFileId = $request['hidden_guarantor_income_file_id'];
        } elseif ($type == 'create' && $incomeFile) {
            $incomeFileId = $this->uploadStoreImage($incomeFile, $loanId, $folder, 'income_proof', $request['created_at']);
        }
        if (empty($incomeFileId)) {
            $incomeFileId = null;
        }
        /*if($type=='update' && $undertakingFile){
            $hiddenFileId = $request['hidden_guarantor_under_taking_file_id'];
            $undertakingFileId = $this->updateUploadStoreImage($undertakingFile,$loanId,$folder,'undertakingdoc',$hiddenFileId,$request['created_at']);
        }elseif($type=='update' && $undertakingFile == ''){
            $undertakingFileId = $request['hidden_guarantor_under_taking_file_id'];
        }elseif ($type=='create' && $undertakingFile) {
            $undertakingFileId = $this->uploadStoreImage($undertakingFile,$loanId,$folder,'undertakingdoc',$request['created_at']);
        }
        if(empty($undertakingFileId)){
            $undertakingFileId = null;
        }
        $data['under_taking_doc'] = $undertakingFileId;*/
        $data['income_type'] = $request['guarantor_income'];
        /*if($data['income_type']==2){
            $data['income_remark'] = $request['guarantor_income_remark'];
        }*/
        $data['income_file_id'] = $incomeFileId;
        $data['security'] = $request['guarantor_security'];
        if ($type != 'update') {
            $data['created_at'] = $request['created_at'];
        }
        //echo "<pre>"; print_r($data); die;
        /*if($request['hidden_more_doc']==1){
            if($type=='update' && $guarantorOtherFile && $request['hidden_other_doc_file_id']){
                $hiddenFileId = $request['hidden_other_doc_file_id'];
                $moreFileId = $this->updateUploadStoreImage($guarantorOtherFile,$loanId,$folder,'other',$hiddenFileId);
            }elseif($type=='update' && $guarantorOtherFile == '' && $request['hidden_other_doc_file_id'] != ''){
                $moreFileId = $request['hidden_other_doc_file_id'];
                //$moreFileId = $this->uploadStoreImage($guarantorOtherFile,$loanId,$folder,'other');
            }elseif($type=='update' && $guarantorOtherFile && $request['hidden_other_doc_file_id'] == ''){
                $moreFileId = $this->uploadStoreImage($guarantorOtherFile,$loanId,$folder,'other');
                //$moreFileId = $this->uploadStoreImage($guarantorOtherFile,$loanId,$folder,'other');
            }elseif ($type=='create' && $guarantorOtherFile) {
                $moreFileId = $this->uploadStoreImage($guarantorOtherFile,$loanId,$folder,'other');
            }
            $data['more_doc_title'] = $request['guarantor_more_doc_title'];
            $data['more_doc_file_id'] = $moreFileId;
        }*/
        return $data;
    }
    /**
     * update upload loan proof documents to store.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function updateUploadStoreImage($file, $loanId, $folder, $prooffolder, $hiddenFileId, $created_at)
    {
        $mainFolder = storage_path() . '/images/loan/document/' . $loanId;
        File::makeDirectory($mainFolder, $mode = 0777, true, true);
        $loanTypeFolder = $mainFolder . '/' . $folder;
        File::makeDirectory($loanTypeFolder, $mode = 0777, true, true);
        $loanTypeProffFolder = $loanTypeFolder . '/' . $prooffolder . '/';
        //File::makeDirectory($loanTypeProffFolder, $mode = 0777, true, true);
        $proffFolder = glob($loanTypeFolder . '/' . $prooffolder . '/*');
        foreach ($proffFolder as $fileRes) {
            if (is_file($fileRes))
                unlink($fileRes);
        }
        $uploadFile = $file->getClientOriginalName();
        $filename = pathinfo($uploadFile, PATHINFO_FILENAME);
        $fname = $filename . '_' . time() . '.' . $file->getClientOriginalExtension();
        $file->move($loanTypeProffFolder, $fname);
        if ($hiddenFileId == '') {
            $data = [
                'file_name' => $fname,
                'file_path' => $loanTypeProffFolder,
                'file_extension' => $file->getClientOriginalExtension(),
                'created_at' => $created_at,
            ];
            $res = Files::create($data);
            $filesId = $res->id;
        } else {
            $data = [
                'file_name' => $fname,
                'file_path' => $loanTypeProffFolder,
                'file_extension' => $file->getClientOriginalExtension(),
            ];
            $fileRes = Files::find($hiddenFileId);
            $fileRes->update($data);
            $filesId = $hiddenFileId;
        }
        return $filesId;
    }
    /**
     * upload loan proof documents to store.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function uploadStoreImage($file, $loanId, $folder, $prooffolder, $created_at)
    {
        $mainFolder = storage_path() . '/images/loan/document/' . $loanId;
        File::makeDirectory($mainFolder, $mode = 0777, true, true);
        $loanTypeFolder = $mainFolder . '/' . $folder;
        File::makeDirectory($loanTypeFolder, $mode = 0777, true, true);
        $loanTypeProffFolder = $loanTypeFolder . '/' . $prooffolder . '/';
        //File::makeDirectory($loanTypeProffFolder, $mode = 0777, true, true);
        $uploadFile = $file->getClientOriginalName();
        $filename = pathinfo($uploadFile, PATHINFO_FILENAME);
        $fname = $filename . '_' . time() . '.' . $file->getClientOriginalExtension();
        $file->move($loanTypeProffFolder, $fname);
        $data = [
            'file_name' => $fname,
            'file_path' => $loanTypeProffFolder,
            'file_extension' => $file->getClientOriginalExtension(),
            'created_at' => $created_at,
        ];
        $res = Files::create($data);
        return $res->id;
    }
    /**
     * Update Loan requests status view.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function loanRequestApproval($id)
    {
        try {

            $mLoanDetails = Memberloans::select('branch_id', 'loan_type')->where('id', $id)->first();
            //$getBranchId=getUserBranchId($mLoanDetails->branch_id);
            $branch_id = $mLoanDetails->branch_id;
            $getBranch = getBranchDetail($branch_id);
            $branchCode = $getBranch->branch_code;
            $state_id = $getBranch->state_id;
            $faCode = getLoanCode($mLoanDetails->loan_type);
            $loanMiCode = getLoanMiCodeNew($mLoanDetails->loan_type, $branch_id);
            if (!empty($loanMiCode)) {
                $miCodeAdd = $loanMiCode->mi_code + 1;
            } else {
                $miCodeAdd = 1;
            }
            $miCode = str_pad($miCodeAdd, 5, '0', STR_PAD_LEFT);
            $loanAccount = $branchCode . $faCode . $miCode;

            $mLoan = Memberloans::find($id);
            $mLoanData['mi_code'] = $miCode;
            $mLoanData['account_number'] = $loanAccount;
            $mLoanData['status'] = 1;
            $mLoanData['rejection_description'] = '';
            $globaldate = checkMonthAvailability(date('d'), date('m'), date('Y'), $state_id);
            $mLoanData['approved_date'] = date("Y-m-d", strtotime($globaldate));
            $mLoanData['old_branch_id'] = $branch_id;
            // Register Collector On Approve Of Member Loan and Make an Entry in New Table Collector Account start
            $collector_type = 'loancollector';
            $typeid = $mLoan->id;
            $associateid = $mLoan->associate_member_id;
            CollectorAccountStoreLI($collector_type, $typeid, $associateid, $globaldate);
            // Register Collector On Approve Of Member Loan and Make an Entry in New Table Collector Account End
            $createdData = $mLoan->update($mLoanData);

            $t = getLoanData($mLoan->loan_type)->loan_category;
            $data = [
                'loanId' => $mLoan->id,
                'loan_type' => $mLoan->loan_type,
                'loan_name' => $mLoan->loan->name,
                'status' => 1,
                'title' => 'Loan Approved',
                'description' => 'Loan Status Approved',
                'status_changed_date' => $globaldate,
                'created_by' => Auth::user()->id,
                'user_name' => Auth::user()->username,
                'created_by_name'=> 'Admin',
                'loan_category' => $t,
            ];
            \App\Models\LoanLog::create($data);
            DB::commit();
            return back()->with('success', 'Loan request has been Approved!');
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('alert', $ex->getMessage().$ex->getLine());
        }

    }
    /**
     * Update Loan requests status view.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function groupLoanRequestApproval($id)
    {
        $mLoanDetails = Memberloans::select('branch_id', 'loan_type')->where('id', $id)->first();
        $gLoanDetails = Grouploans::select('id', 'company_id')->where('member_loan_id', $id)->get();
        foreach ($gLoanDetails as $key => $value) {
            //$getBranchId=getUserBranchId($mLoanDetails->branch_id);
            //$branch_id=$getBranchId->id;
            $branch_id = $mLoanDetails->branch_id;
            $getBranchCode = getBranchCode($branch_id);
            $branchCode = $getBranchCode->branch_code;
            $state_id = getBranchDetail($branch_id)->state_id;
            $faCode = getLoanCodeByCompany('G', $value->company_id);
            $loanMiCode = getGroupLoanMiCodeNew($branch_id);
            if (!empty($loanMiCode)) {
                $miCodeAdd = $loanMiCode->mi_code + 1;
            } else {
                $miCodeAdd = 1;
            }
            $miCode = str_pad($miCodeAdd, 5, '0', STR_PAD_LEFT);
            $loanAccount = $branchCode . $faCode . $miCode;
            $gLoan = Grouploans::find($value->id);
            $gLoanData['mi_code'] = $miCode;
            $gLoanData['account_number'] = $loanAccount;
            $gLoanData['status'] = 1;
            $globaldate = checkMonthAvailability(date('d'), date('m'), date('Y'), $state_id);
            $gLoanData['approved_date'] = date("Y-m-d", strtotime($globaldate));
            $gLoanData['old_branch_id'] = $branch_id;
            // Register Collector On Approve Of Group Loan and Make an Entry in New Table Collector Account start
            $collector_type = 'grouploancollector';
            $typeid = $gLoan->id;
            $associateid = $gLoan->associate_member_id;
            CollectorAccountStoreLI($collector_type, $typeid, $associateid, $globaldate);
            // Register Collector On Approve Of Group Loan and Make an Entry in New Table Collector Account End
            $gLoan->update($gLoanData);

            $t = getLoanData($mLoanDetails->loan_type)->loan_category;
            $data = [
                'loanId' => $value->id,
                'loan_type' => $mLoanDetails->loan_type,
                'loan_name' => $mLoanDetails->loan->name,
                'status' => 1,
                'title' => 'Group Loan Approved',
                'description' => ' Group Loan Status Approved',
                'status_changed_date' => $globaldate,
                'created_by' => Auth::user()->id,
                'created_by_name' => 'Admin',
                'user_name' => Auth::user()->username,
                'loan_category' => $t,
            ];
            \App\Models\LoanLog::create($data);
        }
        // event(new UserActivity($gLoan,'Group Loan Approve',$request));
        $mLoan = Memberloans::find($id);
        $mLoanData['status'] = 1;
        $mLoan->update($mLoanData);
        return back()->with('success', 'Loan request has been Approved!');
    }
    /**
     * Update Loan requests status view.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function loanRequestRejection($id, $type)
    {
        $loanId = $id;
        $mLoanDetails = Memberloans::select('branch_id', 'loan_type')->where('id', $loanId)->first();
        $maLoanDetails = Loanapplicantdetails::select('id_proof_file_id', 'address_proof_file_id', 'income_file_id')->where('member_loan_id', $loanId)->first();
        $mcoaLoanDetails = Loanscoapplicantdetails::select('id_proof_file_id', 'address_proof_file_id', 'income_file_id', 'more_doc_file_id')->where('member_loan_id', $loanId)->first();
        $mgLoanDetails = Loansguarantordetails::select('id_proof_file_id', 'address_proof_file_id', 'income_file_id', 'more_doc_file_id')->where('member_loan_id', $loanId)->first();
        $lothetDocs = Loanotherdocs::select('file_id')->where('member_loan_id', $loanId)->get();
        Schema::disableForeignKeyConstraints();
        DB::beginTransaction();
        try {
            if (isset($maLoanDetails->id_proof_file_id) && $maLoanDetails->id_proof_file_id) {
                $loDocumentFiles = Files::where('id', $maLoanDetails->id_proof_file_id)
                    ->delete();
            }
            if (isset($maLoanDetails->address_proof_file_id) && $maLoanDetails->address_proof_file_id) {
                $loDocumentFiles = Files::where('id', $maLoanDetails->address_proof_file_id)
                    ->delete();
            }
            if (isset($maLoanDetails->income_file_id) && $maLoanDetails->income_file_id) {
                $loDocumentFiles = Files::where('id', $maLoanDetails->income_file_id)
                    ->delete();
            }
            if (isset($mcoaLoanDetails->id_proof_file_id) && $mcoaLoanDetails->id_proof_file_id) {
                $loDocumentFiles = Files::where('id', $mcoaLoanDetails->id_proof_file_id)
                    ->delete();
            }
            if (isset($mcoaLoanDetails->address_proof_file_id) && $mcoaLoanDetails->address_proof_file_id) {
                $loDocumentFiles = Files::where('id', $mcoaLoanDetails->address_proof_file_id)
                    ->delete();
            }
            if (isset($mcoaLoanDetails->income_file_id) && $mcoaLoanDetails->income_file_id) {
                $loDocumentFiles = Files::where('id', $mcoaLoanDetails->income_file_id)
                    ->delete();
            }
            if (isset($mcoaLoanDetails->more_doc_file_id) && $mcoaLoanDetails->more_doc_file_id) {
                $loDocumentFiles = Files::where('id', $mcoaLoanDetails->more_doc_file_id)
                    ->delete();
            }
            if (isset($mgLoanDetails->id_proof_file_id) && $mgLoanDetails->id_proof_file_id) {
                $loDocumentFiles = Files::where('id', $mgLoanDetails->id_proof_file_id)
                    ->delete();
            }
            if (isset($mgLoanDetails->address_proof_file_id) && $mgLoanDetails->address_proof_file_id) {
                $loDocumentFiles = Files::where('id', $mgLoanDetails->address_proof_file_id)
                    ->delete();
            }
            if (isset($mgLoanDetails->income_file_id) && $mgLoanDetails->income_file_id) {
                $loDocumentFiles = Files::where('id', $mgLoanDetails->income_file_id)
                    ->delete();
            }
            if (isset($mgLoanDetails->more_doc_file_id) && $mgLoanDetails->more_doc_file_id) {
                $loDocumentFiles = Files::where('id', $mgLoanDetails->more_doc_file_id)
                    ->delete();
            }
            foreach ($lothetDocs as $key => $value) {
                if (isset($value->file_id) && $value->file_id) {
                    $loDocumentFiles = Files::where('id', $value->file_id)
                        ->delete();
                }
            }
            $applicatnt = Loanapplicantdetails::where('member_loan_id', $loanId)->delete();
            $coapplicatnt = Loanscoapplicantdetails::where('member_loan_id', $loanId)->delete();
            $gaurantor = Loansguarantordetails::where('member_loan_id', $loanId)->delete();
            $liMembers = Loaninvestmentmembers::where('member_loan_id', $loanId)->delete();
            $loDocuments = Loanotherdocs::where('member_loan_id', $loanId)->delete();
            $loan = Memberloans::where('id', $loanId)->delete();
            if ($type == 3) {
                $loan = Grouploans::where('member_loan_id', $loanId)->delete();
            }
            $mainFolder = storage_path() . '/images/loan/document/' . $loanId;
            $this->delete_directory($mainFolder);
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('alert', $ex->getMessage().$ex->getLine());
        }
        /*$mLoan = Memberloans::find($loanId);
        $mLoanData['edit_reject_request'] = 1;
        $mLoanData['rejection_description'] = $request->rejection;
        $mLoanData['status'] = 2;
        $mLoan->update($mLoanData);*/
        return back()
            ->with('success', 'Loan request has been deleted!');
    }
    function delete_directory($dir)
    {
        if (is_dir($dir)) {
            $dir_handle = opendir($dir);
            if ($dir_handle) {
                while ($file = readdir($dir_handle)) {
                    if ($file != "." && $file != "..") {
                        if (!is_dir($dir . "/" . $file)) {
                            unlink($dir . "/" . $file);
                        } else {
                            $this->delete_directory($dir . '/' . $file);
                        }
                    }
                }
                closedir($dir_handle);
            }
            rmdir($dir);
            return true;
        }
        return false;
    }

    public function transferAmountView($id)
    {
        // $currglobaldate = Session::get('created_at');
        // $globaldate =Carbon::parse($currglobaldate);
        $data['title'] = 'Transfer Loan Amount';
        $data['load_id'] = $id;
        //$amount = Memberloans::where('id', $id)->first();
        $data['ssbDetails'] = Memberloans::with(['loanMember', 'loanSavingAccount', 'loanMemberBankDetails', 'LoanApplicants','loanBranch'])->where('id', $id)->first();
        // dd($data['ssbDetails']->LoanApplicants[0]);
        $data['cBanks'] = SamraddhBank::where('company_id',$data['ssbDetails']->company_id)->with(['allBankAccount','samraddhBankCheque'=>function($q){
            $q->where('status', 1);
        }])->get();
        // pd($data['ssbDetails']->toArray());
        $data['cheques'] = SamraddhCheque::select('cheque_no', 'account_id')->where('status', 1)->get();
        $stateid = getBranchState($data['ssbDetails']['loanBranch']->name);
        $globaldate = checkMonthAvailability(date('d'),date('m'),date('Y'),$stateid);
        $gDate = checkMonthAvailability(date('d'),date('m'),date('Y'),33);
        $data['gstSetting'] = \App\Models\GstSetting::where('state_id', $data['ssbDetails']['loanBranch']->state_id)->where('applicable_date','<=',$globaldate)->exists();
         $data['headSetting'] = \App\Models\HeadSetting::where('head_id',294)->first();
        $data['headSettingfileChrage'] = \App\Models\HeadSetting::where('head_id',90)->first();
        $data['headSettingfileChrageAmount']    = ceil(number_format((float)($data['ssbDetails']->file_charges * $data['headSettingfileChrage']->gst_percentage )/100, 2, '.', ''));
        $data['headSettingEcsChrage'] = \App\Models\HeadSetting::where('head_id',434)->first();
        $data['headSettingEcsChrageAmount']    = ceil(number_format((float)($data['ssbDetails']->ecs_charges * $data['headSettingEcsChrage']->gst_percentage )/100, 2, '.', ''));
		$data['dob'] = Member::find($data['ssbDetails']->customer_id);
		$member_dob = date('Y-m-d' , strtotime($data['dob']->dob));
		$today_date = date('Y-m-d');
		$diff = abs(strtotime($today_date) - strtotime($member_dob));
		$years = floor($diff / (365*60*60*24));
		if($years > 60){
			$data['ssbDetails']['insurance_charge'] = 0;
		}
        $data['ecs_ref'] = '';
        if ($data['ssbDetails']->ecs_type != 0) {
            $data['ecs_ref'] = $data['ssbDetails']->ecs_type == 1 ? $data['ssbDetails']->ecs_ref_no : $data['ssbDetails']['loanSavingAccount']->account_no;
        }
        $data['ecs_ref'] = '';
        if ($data['ssbDetails']->ecs_type != 0) {
            $data['ecs_ref'] = $data['ssbDetails']->ecs_ref_no ?? '' ;
        }
        // pd($data);
        return view('templates.admin.loan.approve-loan', $data);
    }
    /**
     * Transfer group loan amount view.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function transferGroupLoanAmountView($id)
    {
        $data['title'] = 'Transfer Group Loan Amount';
        $data['load_id'] = $id;
        $data['ssbDetails'] = Grouploans::with('loanMember', 'loanSavingAccount2', 'loanMemberBankDetails','LoanApplicants')->where('id', $id)->first();
        // dd($data['ssbDetails']);
        $data['cheques'] = SamraddhCheque::select('cheque_no', 'account_id')->where('status', 1)
            ->get();
            $data['cBanks'] = SamraddhBank::where('company_id',$data['ssbDetails']->company_id)->with(['allBankAccount','samraddhBankCheque'=>function($q){
                $q->where('status', 1);
            }])->get();
        $stateid = getBranchState($data['ssbDetails']['loanBranch']->name);
        $globaldate = checkMonthAvailability(date('d'),date('m'),date('Y'),$stateid);
        $data['gstSetting'] = \App\Models\GstSetting::where('state_id', $data['ssbDetails']['loanBranch']->state_id)->where('applicable_date','<=',$globaldate)->exists();
        $data['headSetting'] = \App\Models\HeadSetting::where('head_id',294)->first();
        $data['headSettingfileChrage'] = \App\Models\HeadSetting::where('head_id',90)->first();
        $data['headSettingEcsChrage'] = \App\Models\HeadSetting::where('head_id',434)->first();
        $data['headSettingfileChrageAmount']    = ceil(number_format((float)($data['ssbDetails']->file_charges * $data['headSettingfileChrage']->gst_percentage )/100, 2, '.', ''));  ;
        // $data['insurance_charge'] =\App\Models\LoanCharge::where('min_amount','<=',$data['ssbDetails']->amount)->where('max_amount','>=', $data['ssbDetails']->amount)->where('loan_type',$data['ssbDetails']->loan_type)->where('type',1)->where('status',1)->where('effective_from','<=',$globaldate)->first();
		$data['dob'] = Member::find($data['ssbDetails']->customer_id);
		$member_dob = date('Y-m-d' , strtotime($data['dob']->dob));
		$today_date = date('Y-m-d');
		$diff = abs(strtotime($today_date) - strtotime($member_dob));
		$years = floor($diff / (365*60*60*24));
		if($years > 60){
			$data['ssbDetails']['insurance_charge'] = 0;
		}

        $data['ecs_ref'] = '';
        if ($data['ssbDetails']->ecs_type != 0) {
            $data['ecs_ref'] = $data['ssbDetails']->ecs_ref_no ?? '' ;
        }
        // dd( $data['ssbDetails'] );
        return view('templates.admin.loan.approve-group-loan', $data);
    }
    /**
     * Update Loan requests status view.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function transferAmount(Request $request)
    {
            // dd('gfhf');
            // dd($request->all());
        $date = DateTime::createFromFormat('d/m/Y',$request->date);
        $approve_date = DateTime::createFromFormat('d/m/Y',$request->approve_date);
        if($date < $approve_date)
        {
            return back()->with('alert','Selected date must be greater than approved date..!!');
        }

        $loanId = $request->loan_id;
        $payment_mode = $request->payment_mode;
        $loanDetails = Memberloans::with('loanMemberCustom', 'savingAccount','loanSavingAccount','loan')
        ->whereId($loanId)
        ->select('deposite_amount','transfer_amount', 'account_number', 'amount', 'associate_member_id', 'applicant_id', 'branch_id', 'loan_type','emi_option','emi_amount','insurance_charge','gsttype','filecharge_cgst','filecharge_sgst','filecharge_cgst','insurance_charge_igst','insurance_sgst','insurance_cgst','emi_period','company_id','customer_id','ecs_charge_cgst','ecs_charge_sgst','ecs_charge_igst')
        ->first();
        $stateid = getBranchState($loanDetails['loanBranch']->name);
        $globaldate = checkMonthAvailability(date('d'),date('m'),date('Y'),$stateid);
        $getHeadSetting = \App\Models\HeadSetting::where('head_id',294)->first();
        $getHeadSettingFileCHrage = \App\Models\HeadSetting::where('head_id',90)->first();
        $getEcsSetting = \App\Models\HeadSetting::where('head_id', 434)->first();
        $getGstSetting = \App\Models\GstSetting::where('state_id',$loanDetails['loanBranch']->state_id)->where('applicable_date','<=',$globaldate)->exists();
        $getGstSettingno = \App\Models\GstSetting::select('id','gst_no','state_id')->where('state_id',$loanDetails['loanBranch']->state_id)->where('applicable_date','<=',$globaldate)->first();
        $gstAmount = 0;
        $gstAmountFileChrage = 0;
        $insurance_amount = $loanDetails->insurance_charge;
        if(isset($getHeadSetting->gst_percentage) &&  $getGstSetting )
        {
            if($loanDetails['loanBranch']->state_id == $getGstSettingno->state_id)
            {
                $gstAmount =  ceil(($insurance_amount*$getHeadSetting->gst_percentage)/100)/2;
                $cgstHead = 171;
                $sgstHead = 172;
                $IntraState = true;
            }
            else{
                $gstAmount =  ceil($insurance_amount*$getHeadSetting->gst_percentage)/100;
                $cgstHead = 170;
                $IntraState = false;
            }
        }
        if(isset($getHeadSettingFileCHrage->gst_percentage) &&  $getGstSetting )
        {
            if($loanDetails['loanBranch']->state_id ==  $getGstSettingno->state_id)
            {
                $gstAmountFileChrage =  ceil(($request['file_charge']*$getHeadSettingFileCHrage->gst_percentage)/100)/2;
                $cgstHead = 171;
                $sgstHead = 172;
                $IntraStateFile = true;
            }
            else{
                $gstAmountFileChrage =  ceil($request['file_charge']*$getHeadSettingFileCHrage->gst_percentage)/100;
                $cgstHead = 170;
                $IntraStateFile = false;
            }
        }

        if (isset($getEcsSetting->gst_percentage) && $getGstSetting) {
            if ($loanDetails['loanBranch']->state_id == $getGstSettingno->state_id) {
                $gstEcsChrage = ceil(($request['insurance_amount'] * $getEcsSetting->gst_percentage) / 100) / 2;
                $cgstHead = 171;
                $sgstHead = 172;
                $IntraStateFile = true;
            } else {
                $gstEcsChrage = ceil($request['insurance_amount'] * $getEcsSetting->gst_percentage) / 100;
                $cgstHead = 170;
                $IntraStateFile = false;
            }
        }

        $insurance_amount = $loanDetails->insurance_charge;
        $gstAmount = ($loanDetails->insurance_igst > 0) ? $loanDetails->insurance_igst : ($loanDetails->insurance_cgst + $loanDetails->insurance_sgst)  ;

        $gstAmountFileChrage =  ($loanDetails->filecharge_igst > 0) ? $loanDetails->filecharge_igst : ($loanDetails->filecharge_cgst+$loanDetails->filecharge_sgst) ;

        $gstEcsChrage = ($loanDetails->ecs_charge_igst > 0) ? $loanDetails->ecs_charge_igst : ($loanDetails->ecs_charge_cgst + $loanDetails->ecs_charge_sgst);


        $globaldate = date('Y-m-d',strtotime(convertDate($request['created_at'])));
        if($loanDetails->emi_option == 1)
        {
            $Mode = 'Monthly';
        }
        elseif($loanDetails->emi_option == 2)
        {
            $Mode = 'Weekly';
        }
        elseif($loanDetails->emi_option == 3)
        {
            $Mode = 'Daily';
        }
        if ($request->ssbaccount) //SSB
        {
            $ssbAccountDetails = SavingAccount::with('ssbMemberCustomer')->where('account_no', $request->ssbaccount)
                ->first();
            $ssb_account_number = $request->ssbaccount;
        }
        else
        {
            $ssb_account_number = 0;
        }
        if ($payment_mode == 0)//SSb
        {
            if ($loanDetails['loanSavingAccount']->account_no != $ssb_account_number)
            {
                return back()->with('alert', 'Applicant SSB Account Number does not exists!');
            }
        }
        $countSsbResult = SavingAccount::where('account_no', $ssb_account_number)->count();
        if ($payment_mode == 0)
        {
            if ($countSsbResult == 0)
            {
                return back()->with('alert', 'SSB Account does not exists!');
            }
        }
        if ($payment_mode == 1)//Bank
        {
            $bank_id = $request['company_bank'];
            $bank_ac_id = getSamraddhBankAccount($request['company_bank_account_number'])->id;
            $bankBla = \App\Models\BankBalance::where('bank_id', $bank_id)->where('company_id', $loanDetails->company_id)->where('account_id', $bank_ac_id)->whereDate('entry_date', '<=', date("Y-m-d", strtotime(convertDate($request['date']))))->orderby('entry_date', 'desc')
                ->sum('totalAmount');
            if ($bankBla > 0)
            {
                // Changes By Anup SIr = 01-09-2022  && $bank_id != 2 (Aman jain )
            //https://pm.w3care.com/projects/1892/tasks/45618
                if ($request['total_online_amount'] > $bankBla && $bank_id != 2)
                {
                    return back()
                        ->with('alert', 'Sufficient amount not available in bank account!');
                }
            }
            else
            {
                return back()
                    ->with('alert', 'Sufficient amount not available in bank account!');
            }
        }
        if ($loanDetails->deposite_amount > 0)
        {
            return back()
                ->with('alert', 'Loan amount already transferred to user’s SSB account!');
        } /*if($countSsbResult > 0){*/
        DB::beginTransaction();
        try
        {
            $entryTime = date("H:i:s");
            Session::put('created_at', date("Y-m-d", strtotime(convertDate($request['date']))));
            $amountArraySsb = array(
                '1' => $loanDetails->amount
            );
            $dayBookRef = CommanController::createBranchDayBookReference($loanDetails->amount);
            if ($payment_mode == 0)//ssb
            {
                $paymentMode = 4;
                $transferMode = 'SSB';
                $amount_deposit_by_name = $ssbAccountDetails['ssbMemberCustomer']->first_name . ' ' . $ssbAccountDetails['ssbMemberCustomer']->last_name;
                $transDate = date("Y-m-d ".$entryTime."",strtotime(convertDate($request['date'])));
                $record1 = \App\Models\SavingAccountTransactionView::where('account_no', $ssb_account_number)->where('opening_date','<=',$transDate)->orderby('id','desc')->first();
                $ssb['saving_account_id'] = $ssbAccountDetails->id;
                $ssb['account_no'] = $ssb_account_number;
                $transferAccount = $ssb_account_number;
                if ($record1)
                {
                    if ($request['pay_file_charge'] == 0)
                    {
                        $ssb['opening_balance'] = ($loanDetails->amount - $request['file_charge'] - $request['ecs_amount'] - $insurance_amount - $gstAmount - $gstAmountFileChrage -$gstEcsChrage) + $record1->opening_balance;
                        $ssb['deposit'] = $loanDetails->amount - $request['file_charge'] - $insurance_amount - $request['ecs_amount'] - $gstAmount - $gstAmountFileChrage - $gstEcsChrage ;
                    }
                    else
                    {
                        $ssb['opening_balance'] = $loanDetails->amount + $record1->opening_balance;
                        $ssb['deposit'] = $loanDetails->amount;
                    }
                }
                else
                {
                    if ($request['pay_file_charge'] == 0)
                    {
                    $ssb['opening_balance'] = ($loanDetails->amount - $request['file_charge'] - $insurance_amount -$request['ecs_amount'] - $gstAmount - $gstAmountFileChrage - $gstEcsChrage);
                    $ssb['deposit'] = $loanDetails->amount - $request['file_charge'] - $insurance_amount -$gstAmount -$request['ecs_amount']-  $gstAmountFileChrage -$gstEcsChrage;
                    }
                    else
                    {
                        $ssb['opening_balance'] = $loanDetails->amount;
                        $ssb['deposit'] = $loanDetails->amount;
                    }
                }
                $ssb['branch_id'] = $loanDetails->branch_id;
                $ssb['type'] = 6;
                $ssb['withdrawal'] = 0;
                $ssb['description'] = 'Transferred loan amount to SSB'.$loanDetails->account_number;
                $ssb['currency_code'] = 'INR';
                $ssb['payment_type'] = 'CR';
                $ssb['payment_mode'] = 3;
                $ssb['company_id'] = $loanDetails->company_id;;
                $ssb['created_at'] = date("Y-m-d ".$entryTime."", strtotime(convertDate($request['date'])));
                $ssbAccountTran = SavingAccountTranscation::create($ssb);
                $saTranctionId = $ssbAccountTran->id;
                if ($request['pay_file_charge'] == 0) //From Loan Amount File Charge
                {
                    $balance_update = ($loanDetails->amount - $request['file_charge'] - $insurance_amount) + $ssbAccountDetails->balance /*-$gstAmount - $gstAmountFileChrage*/;
                }
                else
                {
                    $balance_update = $loanDetails->amount + $ssbAccountDetails->balance;
                }
                $ssbBalance = SavingAccount::find($ssbAccountDetails->id);
                $ssbBalance->balance = $balance_update;
                $ssbBalance->save();
                $record2 =\App\Models\SavingAccountTransactionView::where('account_no', $ssb_account_number)->whereDate('opening_date', '>', date("Y-m-d", strtotime(convertDate($request['date']))))->get();
                foreach ($record2 as $key => $value)
                {
                    $nsResult = SavingAccountTranscation::find($value->transaction_id);
                    if ($request['pay_file_charge'] == 0) //From Loan Amount File Charge
                    {
                        $nsResult['opening_balance'] = ($loanDetails->amount - $request['file_charge']-$request['ecs_amount'] -$insurance_amount -$gstEcsChrage /*-$gstAmount - $gstAmountFileChrage*/) + $value->opening_balance;
                    }
                    else
                    {
                        $nsResult['opening_balance'] = $loanDetails->amount + $value->opening_balance;
                    }
                    $nsResult['updated_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($request['date'])));
                    $nsResult->save();
                }
                $data['saving_account_transaction_id'] = $saTranctionId;
                $data['loan_id'] = $loanId;
                $data['created_at'] = date("Y-m-d", strtotime(convertDate($request['date'])));
                $loan_amount = $loanDetails->amount;
                //Create BranchDaybook Transaction and generate Daybook id
                $satRef = $dayBookRef;
                $satRefId = $dayBookRef;
                $description = 'Loan amount to ssb';
            }
            elseif ($payment_mode == 1) //Bank Payment mode
            {
                $satRefId = NULL;
                $transferMode = 'BANK';
                if ($request['bank_transfer_mode'] == 0 && $request['bank_transfer_mode'] != '')
                {
                    $paymentMode = 1;
                }
                elseif ($request['bank_transfer_mode'] == 1)
                {
                    $paymentMode = 3;
                }
                $saTranctionId = '';
            }
            elseif ($payment_mode == 2) // Cash payment Mode
            {
                $satRefId = NULL;
                $paymentMode = 2;
                $saTranctionId = '';
            }
            $amount_deposit_by_name = $loanDetails['loanMemberCustom']->first_name . ' ' . $loanDetails['loanMemberCustom']->last_name;
            /************* Head Implement ****************/
            // dd($payment_mode);
            if ($payment_mode == 0) //SSb
            {
                $chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
                $v_no = "";
                for ($i = 0;$i < 10;$i++)
                {
                    $v_no .= $chars[mt_rand(0, strlen($chars) - 1) ];
                }
                $loan_head_id = $loanDetails['loan']->head_id;
                $ssbHead = \App\Models\Plans::where('company_id',$loanDetails->company_id)->where('plan_category_code','S')->first();
                $ssbHead = $ssbHead->deposit_head_id;
                //............................. Optimization code...........................//
                if ($request['pay_file_charge'] == 0)
                {
                    $loan_amount = $loanDetails->amount - $request['file_charge'] - $insurance_amount-$request['ecs_amount'] - $gstAmount - $gstAmountFileChrage -$gstEcsChrage;
                    // $loan_amount = $loanDetails->amount - $request['file_charge']- $insurance_amount ;
                    $totalLoanAmount = $loanDetails->amount;
                    $tAmount = $loan_amount;
                }
                else
                {
                    $loan_amount = $loanDetails->amount;
                    $totalLoanAmount = $loanDetails->amount;
                    $tAmount = $loan_amount;
                }
                // $dayBookRef = CommanController::createBranchDayBookReference($loan_amount);
                //Create Loan Daybook Sanction Entry (New Change)
                $createLoanDayBook = CommanController::createLoanDayBook($dayBookRef, $dayBookRef, $loanDetails->loan_type, 2, $loanId, $lId = NULL, $loanDetails->account_number, $loanDetails->applicant_id, 0,0,0,$loanDetails->amount,'Loan Sanction', $loanDetails->branch_id, getBranchCode($loanDetails->branch_id)->branch_code, 'CR', 'INR', 4, NULL, NULL, $branch_name = NULL, date("Y-m-d", strtotime(convertDate($request['date']))), NULL, 1, 1, NULL,$loanDetails->account_number, NULL, NULL, NULL, $request['branch'],0,0,0,$loanDetails->company_id,null );
                $createDayBook = $createLoanDayBook;
                //............................. Optimization code...........................//
            //Create Branch Daybook Reference Transaction For SSb and generate Daybook Id
                //............................. Optimization code...........................//
                //Create BranchDaybook Transaction For SSB
                $branchDayBook = CommanController::branchDayBookNew($dayBookRef, $loanDetails->branch_id, 5, 51, $loanId, $saTranctionId,$loanDetails->associate_member_id, $loanDetails->applicant_id, $loanDetails->branch_id, $loanDetails->branch_id, $totalLoanAmount, 'Payment transfer for Loan Sanction', 'To ' . $loanDetails->account_number . ' A/C Dr ' . $totalLoanAmount . '', 'To Bank A/C Cr ' . $loanDetails->account_number . '', 'DR', 3, 'INR',$v_no, $ssb_account_id_from = NULL, $cheque_no = NULL,$transction_no = NULL,$entry_date = NULL,$entry_time = NULL,1,Auth::user()->id,$created_at = NULL,$saTranctionId, $ssb_account_tran_id_from = NULL,$jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL,$loanDetails->company_id);
                // dd($branchDayBook);
                //............................. Optimization code...........................//
                /*$allTransaction = $this->createAllTransaction($dayBookRef,$loanDetails->branch_id,$bank_id=NULL,$bank_ac_id=NULL,2,10,25,$loan_head_id,$head5=NULL,5,51,$loanId,$saTranctionId,$loanDetails->associate_member_id,$loanDetails->applicant_id,$branch_id_to=NULL,$loanDetails->branch_id,$loan_amount,$loan_amount,$loan_amount,'Payment transfer for Loan Sanction','CR',3,'INR',$loanDetails->applicant_id,getMemberData($loanDetails->applicant_id)->first_name.' '.getMemberData($loanDetails->applicant_id)->last_name,$amount_from_id=NULL,$amount_from_name=NULL,$v_no,date("Y-m-d", strtotime(convertDate($request['date']))),$ssb_account_id_from=NULL,$cheque_no=NULL,$cheque_date=NULL,$cheque_bank_from=NULL,$cheque_bank_ac_from=NULL,$cheque_bank_ifsc_from=NULL,$cheque_bank_branch_from=NULL,$cheque_bank_to=NULL,$cheque_bank_ac_to=NULL,$transction_no=NULL,$transction_bank_from=NULL,$transction_bank_ac_from=NULL,$transction_bank_ifsc_from=NULL,$transction_bank_branch_from=NULL,$transction_bank_to=NULL,$transction_bank_ac_to=NULL,$transction_date=NULL,$entry_date=NULL,$entry_time=NULL,1,Auth::user()->id,$request['date'],$ssbAccountDetails->id,$saTranctionId,$ssb_account_tran_id_from=NULL);*/
                //............................. Optimization code...........................//
                //Create AllHead Transaction For SSB in loan Head DR Type
                $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef, $loanDetails->branch_id, $bank_id = NULL, $bank_ac_id = NULL, $loan_head_id, 5, 51, $loanId, $saTranctionId,$loanDetails->associate_member_id, $loanDetails->applicant_id, $branch_id_to = NULL, $loanDetails->branch_id, $totalLoanAmount, 'Payment transfer for Loan Sanction', 'DR', 3, 'INR',$jv_unique_id = NULL, $v_no, $ssb_account_id_from = NULL, $ssbAccountDetails->id,$saTranctionId, $ssb_account_tran_id_from = NULL,$cheque_type = NULL, $cheque_id = NULL, $cheque_no = NULL,$transction_no = NULL,1, Auth::user()->id,$loanDetails->company_id );
                //............................. Optimization code...........................//
                //Create AllHead Transaction For SSB Head  CR Type
                $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef, $loanDetails->branch_id, $bank_id = NULL, $bank_ac_id = NULL, $ssbHead, 5, 51, $loanId, $saTranctionId,$loanDetails->associate_member_id, $loanDetails->applicant_id, $branch_id_to = NULL, $loanDetails->branch_id,$loan_amount, 'Payment transfer for Loan Sanction', 'CR', 3, 'INR', $jv_unique_id = NULL, $v_no, $ssb_account_id_from = NULL, $ssbAccountDetails->id,$saTranctionId, $ssb_account_tran_id_from = NULL,$cheque_type = NULL, $cheque_id = NULL, $cheque_no = NULL,$transction_no = NULL,1, Auth::user()->id,$loanDetails->company_id );
            }
            //Bank Transaction
            elseif ($payment_mode == 1) //Bank
            {
                $loan_head_id = $loanDetails['loan']->head_id;
                if ($request['pay_file_charge'] == 0) // loan
                {
                    $loan_amount = $loanDetails->amount - $request['file_charge']- $insurance_amount - $request['ecs_amount']-$gstAmount - $gstAmountFileChrage - $gstEcsChrage;
                    $totalLoanAmount = $loanDetails->amount;
                    $tAmount = $loan_amount;
                }
                else
                {
                    $loan_amount = $loanDetails->amount;
                    $totalLoanAmount = $loanDetails->amount;
                    $tAmount = $loan_amount;
                }
                if ($request['bank_transfer_mode'] == 0 && $request['bank_transfer_mode'] != '') //Cheque Mode
                {
                    $payment_type = 1;
                    $amount_from_id = $request['company_bank'];
                    $amount_from_name = getSamraddhBank($request['company_bank'])->bank_name;
                    $cheque_type = 1;
                    $cheque_id = getSamraddhChequeData($request['cheque_id'])->id;
                    $cheque_no = $request['cheque_id'];
                    $cheque_date = getSamraddhChequeData($request['cheque_id'])->cheque_create_date;
                    $cheque_bank_from = $request['company_bank'];
                    $cheque_bank_ac_from = $request['company_bank_account_number'];
                    $cheque_bank_ifsc_from = getSamraddhBankAccount($request['company_bank_account_number'])->ifsc_code;
                    $cheque_bank_ac_from_id = getSamraddhBankAccount($request['company_bank_account_number'])->id;
                    $cheque_bank_branch_from = NULL;
                    $cheque_bank_to = NULL;
                    $cheque_bank_ac_to = $request['customer_bank_account_number'];
                    $v_no = NULL;
                    $cheque_dd_no = $cheque_no;
                    $v_date = NULL;
                    $ssb_account_id_from = NULL;
                    $transction_no = NULL;
                    $transction_bank_from = $request['company_bank'];
                    $transction_bank_ac_from = $request['company_bank_account_number'];
                    $transction_bank_ifsc_from = getSamraddhBankAccount($request['company_bank_account_number'])->ifsc_code;
                    $transction_bank_from_ac_id = getSamraddhBankAccount($request['company_bank_account_number'])->id;
                    $transction_bank_branch_from = NULL;
                    $transction_bank_to = NULL;
                    $transction_bank_ac_to = $request['customer_bank_account_number'];
                    $transction_bank_to_name = $request['customer_bank_name'];
                    $transction_bank_to_ac_no = $request['customer_bank_account_number'];
                    $transction_bank_to_branch = $request['customer_branch_name'];
                    $transction_bank_to_ifsc = $request['customer_ifsc_code'];
                    $transferAccount = $request['customer_bank_account_number'];
                }
                elseif ($request['bank_transfer_mode'] == 1) // Online Mode
                {
                    $payment_type = 2;
                    $amount_from_id = $request['company_bank'];
                    $amount_from_name = getSamraddhBank($request['company_bank'])->bank_name;
                    $cheque_type = NULL;
                    $cheque_id = NULL;
                    $cheque_no = NULL;
                    $cheque_dd_no = $cheque_no;
                    $cheque_date = NULL;
                    $cheque_bank_from = NULL;
                    $cheque_bank_ac_from = NULL;
                    $cheque_bank_ac_from_id = NULL;
                    $cheque_bank_ifsc_from = NULL;
                    $cheque_bank_branch_from = NULL;
                    $cheque_bank_to = NULL;
                    $cheque_bank_ac_to = NULL;
                    $transction_no = $request['utr_transaction_number'];
                    $v_no = NULL;
                    $v_date = NULL;
                    $ssb_account_id_from = NULL;
                    $transction_bank_from = $request['company_bank'];
                    $transction_bank_ac_from = $request['company_bank_account_number'];
                    $transction_bank_ifsc_from = getSamraddhBankAccount($request['company_bank_account_number'])->ifsc_code;
                    $transction_bank_from_ac_id = getSamraddhBankAccount($request['company_bank_account_number'])->id;
                    $transction_bank_branch_from = NULL;
                    $transction_bank_to = NULL;
                    $transction_bank_ac_to = $request['customer_bank_account_number'];
                    $transction_bank_to_name = $request['customer_bank_name'];
                    $transction_bank_to_ac_no = $request['customer_bank_account_number'];
                    $transction_bank_to_branch = $request['customer_branch_name'];
                    $transction_bank_to_ifsc = $request['customer_ifsc_code'];
                    $transferAccount = $request['customer_bank_account_number'];
                }
                //Create BranchDaybook Transaction and generate Daybook id
                //Create Loan Daybook Sanction Entry (New Change)
            // pd($loan_amount);
                // $dayBookRef = CommanController::createBranchDayBookReference($loan_amount);
                $createLoanDayBook = CommanController::createLoanDayBook($dayBookRef, $dayBookRef, $loanDetails->loan_type, 2, $loanId, $lId = NULL, $loanDetails->account_number, $loanDetails->applicant_id, 0,0,0,$loanDetails->amount,'Loan Sanction', $loanDetails->branch_id, getBranchCode($loanDetails->branch_id)->branch_code, 'CR', 'INR', 3, NULL, NULL, $branch_name = NULL, date("Y-m-d", strtotime(convertDate($request['date']))), NULL, 1, 1, NULL,$loanDetails->account_number, NULL, NULL, NULL, $request['branch'],0,0,0,$loanDetails->company_id,null);
                    $createDayBook = $createLoanDayBook;
                //............................. Optimization code...........................//
                //Create Branch Daybook Transaction for bank mode
                $branchDayBook = CommanController::branchDayBookNew($dayBookRef, $loanDetails->branch_id, 5, 51, $loanId, $createDayBook,$loanDetails->associate_member_id, $loanDetails->applicant_id, $branch_id_to = NULL, $loanDetails->branch_id, $totalLoanAmount,'Payment transfer for Loan Sanction', 'To ' . $loanDetails->account_number . ' A/C Cr ' . $totalLoanAmount . '', 'To Bank A/C Dr ' . $loanDetails->account_number . '', 'DR', $payment_type, 'INR',$v_no,$ssb_account_id_from,$cheque_no,$transction_no,$entry_date = NULL,$entry_time = NULL,1,Auth::user()->id, date("Y-m-d", strtotime(convertDate($request['date']))),$ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL,$jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL,$loanDetails->company_id);
                //............................. Optimization code...........................//
                /*$allTransaction = CommanController::createAllTransaction($dayBookRef,$loanDetails->branch_id,$bank_id=NULL,$bank_ac_id=NULL,2,10,25,$loan_head_id,$head5=NULL,5,51,$loanId,$createDayBook,$loanDetails->associate_member_id,$loanDetails->applicant_id,$branch_id_to=NULL,$loanDetails->branch_id,$loan_amount,$loan_amount,$loan_amount,'Payment transfer for Loan Sanction','CR',$payment_type,'INR',$loanDetails->applicant_id,getMemberData($loanDetails->applicant_id)->first_name.' '.getMemberData($loanDetails->applicant_id)->last_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date=NULL,$entry_date=NULL,$entry_time=NULL,1,Auth::user()->id,$created_at=NULL);*/
                //............................. Optimization code...........................//
                //Create AllHead Transaction for bank mode and  Head
                $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef, $loanDetails->branch_id, $amount_from_id,getSamraddhBankAccount($request['company_bank_account_number'])->id, $loan_head_id, 5, 51, $loanId, $createDayBook,$loanDetails->associate_member_id, $loanDetails->applicant_id, $branch_id_to = NULL, $loanDetails->branch_id, $totalLoanAmount, 'Payment transfer for Loan Sanction', 'DR', $payment_type, 'INR', $jv_unique_id = NULL, $v_no, $ssb_account_id_from = NULL,NULL,NULL, $ssb_account_tran_id_from = NULL,$cheque_type = NULL, $cheque_id = NULL, $cheque_no = NULL,$transction_no = NULL,1, Auth::user()->id,$loanDetails->company_id );
                //............................. Optimization code...........................//
                //Create AllHead Transaction for bank mode and  Bank Head
                $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef, $loanDetails->branch_id, $amount_from_id, getSamraddhBankAccount($request['company_bank_account_number'])->id, getSamraddhBank($request['company_bank'])->account_head_id, 5, 51, $loanId, $createDayBook,$loanDetails->associate_member_id, $loanDetails->applicant_id, $branch_id_to = NULL, $loanDetails->branch_id, ($loan_amount + $request['rtgs_neft_charge']), 'Payment transfer for Loan Sanction', 'CR', $payment_type, 'INR',  $jv_unique_id = NULL, $v_no, $ssb_account_id_from = NULL, NULL,NULL, $ssb_account_tran_id_from = NULL,$cheque_type, $cheque_id, $cheque_no ,$transction_no = NULL,1, Auth::user()->id,$loanDetails->company_id );
                if ($request['bank_transfer_mode'] == 1) //Online
                {
                    if( $request['rtgs_neft_charge'] > 0) //Rtgs Charge
                    {
                        $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef, $loanDetails->branch_id, $bank_id = NULL, $bank_ac_id = NULL, 92, 5, 522, $loanId, $createDayBook,$loanDetails->associate_member_id, $loanDetails->applicant_id, $branch_id_to = NULL, $loanDetails->branch_id, $request['rtgs_neft_charge'], 'NEFT Charge A/c Dr ' . $request['rtgs_neft_charge'] . '', 'DR', $payment_type, 'INR', $jv_unique_id = NULL,NULL, $ssb_account_id_from = NULL,NULL,NULL, $ssb_account_tran_id_from = NULL,$cheque_type = NULL, $cheque_id = NULL, $cheque_no = NULL,$transction_no = NULL,1, Auth::user()->id,$loanDetails->company_id );
                        //............................. Optimization code...........................//
                        $branchDayBook = CommanController::branchDayBookNew($dayBookRef, $loanDetails->branch_id, 5, 51, $loanId, $createDayBook,$loanDetails->associate_member_id, $loanDetails->applicant_id, $branch_id_to = NULL, $loanDetails->branch_id, $request['rtgs_neft_charge'],'NEFT Charge', 'Bank Charge To ' . $loanDetails->account_number . ' A/C Dr ' . $totalLoanAmount . '', 'To Bank A/C Cr ' . $loanDetails->account_number . '', 'DR', $payment_type, 'INR',  NULL,NULL,$cheque_no,NULL,$entry_date = NULL,$entry_time = NULL,1,Auth::user()->id,date("Y-m-d", strtotime(convertDate($request['date']))),$ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL,$jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL,$loanDetails->company_id);
                    }
                }
                $samraddhBankDaybook = CommanController::samraddhBankDaybookNew($dayBookRef, $amount_from_id, getSamraddhBankAccount($request['company_bank_account_number'])->id, 5, 51, $loanId, $createDayBook,$loanDetails->associate_member_id, $loanDetails->applicant_id, $loanDetails->branch_id, $loan_amount + $request['rtgs_neft_charge'], $loan_amount + $request['rtgs_neft_charge'], $loan_amount + $request['rtgs_neft_charge'], 'Payment transfer for Loan Sanction', 'Cash A/c Dr ' . ($loan_amount + $request['rtgs_neft_charge']) . '', 'NEFT Charge A/c Dr ' . $request['rtgs_neft_charge'] . '', 'DR', $payment_type, 'INR', $loanDetails->applicant_id, getMemberCustom($loanDetails->customer_id)->first_name . ' ' . getMemberCustom($loanDetails->customer_id)->last_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $amount_from_name, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc, $transction_date = NULL, $entry_date = NULL, $entry_time = NULL, 1, Auth::user()->id, $created_at = NULL, $jv_unique_id = NULL, $cheque_type, $cheque_id, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL,$loanDetails->company_id);
                //Create Cheque
                if ($request['bank_transfer_mode'] == 0 && $request['bank_transfer_mode'] != '')
                {
                    SamraddhCheque::where('cheque_no', $request['cheque_id']);
                    SamraddhChequeIssue::create([
                    'cheque_id' => getSamraddhChequeData($request['cheque_id'])->id,
                    'type' => 3,
                    'sub_type' => 33,
                    'type_id' => $loanId,
                    'cheque_issue_date' => date("Y-m-d", strtotime(convertDate($request['date']))) ,
                    'status' => 1,
                    ]);
                }
            }
            //Cash Transaction
            elseif ($payment_mode == 2)
            {
                $v_no = NULL;
                $loan_head_id = $loanDetails['loan']->head_id;
                if ($request['pay_file_charge'] == 0)
                {
                    $loan_amount = $loanDetails->amount - $request['file_charge'] - $insurance_amount - $request['ecs_amount'] - $gstAmountFileChrage - $gstAmount - $gstEcsChrage;
                    //-$gstAmountFileChrage - $gstAmount;
                    $totalLoanAmount = $loanDetails->amount ;
                }
                else
                {
                    $loan_amount = $loanDetails->amount;
                    $totalLoanAmount = $loanDetails->amount;
                }
                // $dayBookRef = CommanController::createBranchDayBookReference($loan_amount);
                //Create Loan Daybook Sanction Entry (New Change)
                // $dayBookRef = CommanController::createBranchDayBookReference($loan_amount);
                $createLoanDayBook = CommanController::createLoanDayBook($dayBookRef, $dayBookRef, $loanDetails->loan_type, 2, $loanId, $lId = NULL, $loanDetails->account_number, $loanDetails->applicant_id, 0,0,0,$loanDetails->amount,'Loan Sanction', $loanDetails->branch_id, getBranchCode($loanDetails->branch_id)->branch_code, 'CR', 'INR', 0, NULL, NULL, $branch_name = NULL, date("Y-m-d", strtotime(convertDate($request['date']))), NULL, 1, 1, NULL,$loanDetails->account_number, NULL, NULL, NULL, $request['branch'],0,0,0,$loanDetails->company_id,null);
                $createDayBook = $createLoanDayBook;
                //............................. Optimization code...........................//
                $branchDayBook = CommanController::branchDayBookNew($dayBookRef, $loanDetails->branch_id, 5, 51, $loanId, $createDayBook, $loanDetails->associate_member_id, $loanDetails->applicant_id, $loanDetails->branch_id, $loanDetails->branch_id, $totalLoanAmount, 'Payment transfer for Loan Sanction', 'To ' . $loanDetails->account_number . ' A/C Dr ' . $totalLoanAmount . '', 'To Bank A/C Cr ' . $loanDetails->account_number . '', 'DR', 0, 'INR',NULL,NULL,NULL,NULL,date("Y-m-d", strtotime(convertDate($request['date']))), NULL,1,Auth::user()->id,date("Y-m-d", strtotime(convertDate($request['date']))),$ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL,$jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL,$loanDetails->company_id);
                //............................. Optimization code...........................//
                //............................. Optimization code...........................//
                $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef, $loanDetails->branch_id, $bank_id = NULL, $bank_ac_id = NULL, $loan_head_id, 5, 51, $loanId, $createDayBook,  $loanDetails->associate_member_id, $loanDetails->applicant_id, $branch_id_to = NULL, $loanDetails->branch_id, $totalLoanAmount, 'Payment transfer for Loan Sanction', 'DR', 0, 'INR', $jv_unique_id = NULL, NULL, $ssb_account_id_from = NULL,NULL,NULL, $ssb_account_tran_id_from = NULL,$cheque_type = NULL, $cheque_id = NULL, $cheque_no = NULL,$transction_no = NULL,1, Auth::user()->id,$loanDetails->company_id );
                //............................. Optimization code...........................//
                $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef, $loanDetails->branch_id, $bank_id = NULL, $bank_ac_id = NULL, 28, 5, 51, $loanId, $createDayBook,$loanDetails->associate_member_id, $loanDetails->applicant_id, $branch_id_to = NULL, $loanDetails->branch_id, $loan_amount, 'Payment transfer for Loan Sanction', 'CR', 0, 'INR', $jv_unique_id = NULL,NULL, $ssb_account_id_from = NULL, NULL,NULL, NULL,NULL,  NULL, NULL,NULL,1, Auth::user()->id,$loanDetails->company_id );
            }
            /************* Head Implement ****************/
            //File Charge Transaction loan
            if ($request['pay_file_charge'] != '' && $request['pay_file_charge'] == 0)
            {
                $mlResult = Memberloans::find($loanId);
                $lData['deposite_amount'] = $loanDetails->amount - $request['file_charge'];
                $lData['due_amount'] = $loanDetails->amount;
                $lData['file_charge_type'] = '1';
                $lData['transfer_amount'] = $loan_amount;
                $lData['approve_date'] = date("Y-m-d", strtotime(convertDate($request['date'])));
                if ($loanDetails->emi_option == 1)
                {
                    $lData['closing_date'] = date('Y-m-d', strtotime("+" . $loanDetails->emi_period . " months", strtotime($lData['approve_date'])));
                }
                elseif ($loanDetails->emi_option == 2)
                {
                    $days = $loanDetails->emi_period * 7;
                    $start_date = $lData['approve_date'];
                    $date = strtotime($start_date);
                    $date = strtotime("+" . $days . " day", $date);
                    $lData['closing_date'] = date('Y-m-d', $date);
                }
                elseif ($loanDetails->emi_option == 3)
                {
                    $days = $loanDetails->emi_period;
                    $start_date = $lData['approve_date'];
                    $date = strtotime($start_date);
                    $date = strtotime("+" . $days . " day", $date);
                    $lData['closing_date'] = date('Y-m-d', $date);
                }
                $lData['status'] = 4;
                $mlResult->update($lData);
                $fileamountArraySsb = array(
                    '1' => $request['file_charge']
                );
                /************* Head Implement ****************/
                $chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
                $v_no = "";
                for ($i = 0;$i < 10;$i++)
                {
                    $v_no .= $chars[mt_rand(0, strlen($chars) - 1) ];
                }
                /*if($request['pay_file_charge'] == 0){
                        $loan_amount=$loanDetails->amount-$request['file_charge'];
                    }else{
                        $loan_amount=$request['file_charge'];
                    }*/
                $loan_amount = $request['file_charge'];
                // $dayBookRef = CommanController::createBranchDayBookReference($loan_amount + $insurance_amount);
                $branchDayBook = CommanController::branchDayBookNew($dayBookRef, $loanDetails->branch_id, 5, 57, $loanId, $createDayBook, $loanDetails->associate_member_id, $loanDetails->applicant_id, $branch_id_to = NULL, $loanDetails->branch_id,$loan_amount, 'To ' . $loanDetails->account_number . ' A/C Dr ' . $loan_amount . '', 'To ' . $loanDetails->account_number . ' A/C Dr ' . $loan_amount . '', 'To Bank A/C Cr ' . $loanDetails->account_number . '', 'CR', 5, 'INR', NULL,NULL,NULL,NULL,$entry_date = NULL,$entry_time = NULL,1,Auth::user()->id,date("Y-m-d", strtotime(convertDate($request['date']))),$ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL,$jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL,$loanDetails->company_id);
                // $branchDayBook = CommanController::branchDayBookNew($dayBookRef, $loanDetails->branch_id, 5, 57, $loanId, $createDayBook, $loanDetails->associate_member_id, $loanDetails->applicant_id, $branch_id_to = NULL, $loanDetails->branch_id,$loan_amount, 'To ' . $loanDetails->account_number . ' A/C Dr ' . $loan_amount . '', 'To ' . $loanDetails->account_number . ' A/C Dr ' . $loan_amount . '', 'To Bank A/C Cr ' . $loanDetails->account_number . '', 'CR', 5, 'INR', NULL,NULL,NULL,NULL,$entry_date = NULL,$entry_time = NULL,1,Auth::user()->id,$created_at = NULL,$ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL,$jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL,$loanDetails->company_id);
                $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef, $loanDetails->branch_id, $bank_id = NULL, $bank_ac_id = NULL, 90, 5, 57, $loanId, $createDayBook, $loanDetails->associate_member_id, $loanDetails->applicant_id, $branch_id_to = NULL, $loanDetails->branch_id, $loan_amount, 'To ' . $loanDetails->account_number . ' A/C Dr ' . $loan_amount . '', 'CR', 5, 'INR', $jv_unique_id = NULL, $v_no, $ssb_account_id_from = NULL, NULL,NULL, $ssb_account_tran_id_from = NULL,$cheque_type = NULL, $cheque_id = NULL, $cheque_no = NULL,$transction_no = NULL,1, Auth::user()->id,$loanDetails->company_id );
                if($insurance_amount > 0)
                {
                    $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef, $loanDetails->branch_id, $bank_id = NULL, $bank_ac_id = NULL, 294, 5,525, $loanId, $createDayBook, $loanDetails->associate_member_id, $loanDetails->applicant_id, $branch_id_to = NULL, $loanDetails->branch_id, $insurance_amount, 'To ' . $loanDetails->account_number . ' A/C Dr ' . $insurance_amount . '', 'CR', 5, 'INR', $jv_unique_id = NULL, $v_no, $ssb_account_id_from = NULL, NULL,NULL, $ssb_account_tran_id_from = NULL,$cheque_type = NULL, $cheque_id = NULL, $cheque_no = NULL,$transction_no = NULL,1, Auth::user()->id,$loanDetails->company_id );
                    $branchDayBook = CommanController::branchDayBookNew($dayBookRef, $loanDetails->branch_id, 5, 525, $loanId, $createDayBook, $loanDetails->associate_member_id, $loanDetails->applicant_id, $branch_id_to = NULL, $loanDetails->branch_id, $insurance_amount, 'To ' . $loanDetails->account_number . ' A/C Dr ' . $loan_amount . '', 'To ' . $loanDetails->account_number . ' A/C Dr ' . $insurance_amount . '', 'To Bank A/C Cr ' . $loanDetails->account_number . '', 'CR', 5, 'INR', NULL,NULL,NULL,NULL,$entry_date = NULL,$entry_time = NULL,1,Auth::user()->id,date("Y-m-d", strtotime(convertDate($request['date']))),$ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL,$jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL,$loanDetails->company_id);
                }


                if($request['ecs_amount'] > 0)
                {
                    // dd($request['ecs_amount']);
                    $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef, $loanDetails->branch_id, $bank_id = NULL, $bank_ac_id = NULL, 434, 5,547, $loanId, $createDayBook, $loanDetails->associate_member_id, $loanDetails->applicant_id, $branch_id_to = NULL, $loanDetails->branch_id, $request['ecs_amount'], 'ECS To ' . $loanDetails->account_number . ' A/C Dr ' . $request['ecs_amount'] . '', 'CR', 5, 'INR', $jv_unique_id = NULL, $v_no, $ssb_account_id_from = NULL, NULL,NULL, $ssb_account_tran_id_from = NULL,$cheque_type = NULL, $cheque_id = NULL, $cheque_no = NULL,$transction_no = NULL,1, Auth::user()->id,$loanDetails->company_id );
                    $branchDayBook = CommanController::branchDayBookNew($dayBookRef, $loanDetails->branch_id, 5, 547, $loanId, $createDayBook, $loanDetails->associate_member_id, $loanDetails->applicant_id, $branch_id_to = NULL, $loanDetails->branch_id, $request['ecs_amount'], 'ECS To ' . $loanDetails->account_number . ' A/C Dr ' . $loan_amount . '', 'ECS To ' . $loanDetails->account_number . ' A/C Dr ' . $request['ecs_amount'] . '', 'ECS To Bank A/C Cr ' . $loanDetails->account_number . '', 'CR', 5, 'INR', NULL,NULL,NULL,NULL,$entry_date = NULL,$entry_time = NULL,1,Auth::user()->id,date("Y-m-d", strtotime(convertDate($request['date']))),$ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL,$jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL,$loanDetails->company_id);
                }
                //Gst Charge Foir Branch DayBook
                if($gstAmount > 0 )
                {
                    $mlResult = Memberloans::find($loanId);
                    if($loanDetails->insurance_cgst > 0)
                    {
                        $gstAmount = $gstAmount/2;
                        $branchDayBook = CommanController::branchDayBookNew($dayBookRef, $loanDetails->branch_id, 5, 528, $loanId, $createDayBook, $loanDetails->associate_member_id, $loanDetails->applicant_id, $branch_id_to = NULL, $loanDetails->branch_id, $gstAmount, 'CGST CHARGE ' . $loanDetails->account_number . '','CGST CHARGE ' . $loanDetails->account_number . '', 'CGST CHARGE ' . $loanDetails->account_number . '', 'CR', 5, 'INR',NULL,NULL,NULL,NULL,$entry_date = NULL,$entry_time = NULL,1,Auth::user()->id,$created_at = NULL,$ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL,$jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL,$loanDetails->company_id);
                        $branchDayBook = CommanController::branchDayBookNew($dayBookRef, $loanDetails->branch_id, 5, 529, $loanId, $createDayBook, $loanDetails->associate_member_id, $loanDetails->applicant_id, $branch_id_to = NULL, $loanDetails->branch_id, $gstAmount, 'SGST CHARGE ' . $loanDetails->account_number . '','SGST CHARGE ' . $loanDetails->account_number . '', 'SGST CHARGE ' . $loanDetails->account_number . '', 'CR', 5, 'INR', NULL,NULL,NULL,NULL,$entry_date = NULL,$entry_time = NULL,1,Auth::user()->id,$created_at = NULL,$ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL,$jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL,$loanDetails->company_id);
                        $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef, $loanDetails->branch_id, $bank_id = NULL, $bank_ac_id = NULL, $cgstHead,5, 528, $loanId, $createDayBook, $loanDetails->associate_member_id, $loanDetails->applicant_id, $branch_id_to = NULL, $loanDetails->branch_id, $gstAmount, '' . $loanDetails->account_number . ' CGST charge', 'CR', 5, 'INR', $jv_unique_id = NULL, $v_no, $ssb_account_id_from = NULL, NULL,NULL, $ssb_account_tran_id_from = NULL,$cheque_type = NULL, $cheque_id = NULL, $cheque_no = NULL,$transction_no = NULL,1, Auth::user()->id,$loanDetails->company_id );
                        $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef, $loanDetails->branch_id, $bank_id = NULL, $bank_ac_id = NULL, $sgstHead,5, 529, $loanId, $createDayBook, $loanDetails->associate_member_id, $loanDetails->applicant_id, $branch_id_to = NULL, $loanDetails->branch_id, $gstAmount, '' . $loanDetails->account_number . ' SGST charge', 'CR', 5, 'INR', $jv_unique_id = NULL, $v_no, $ssb_account_id_from = NULL, NULL,NULL, $ssb_account_tran_id_from = NULL,$cheque_type = NULL, $cheque_id = NULL, $cheque_no = NULL,$transction_no = NULL,1, Auth::user()->id,$loanDetails->company_id );
                        //   $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef, $loanDetails->branch_id, $bank_id = NULL, $bank_ac_id = NULL, 28, 5, 528, $loanId, $createDayBook, $loanDetails->associate_member_id, $loanDetails->applicant_id, $branch_id_to = NULL, $loanDetails->branch_id, $gstAmount,'' . $loanDetails->account_number . ' Cgst  charge', 'DR', 0, 'INR', $jv_unique_id = NULL, $v_no, $ssb_account_id_from = NULL, NULL,NULL, $ssb_account_tran_id_from = NULL,$cheque_type = NULL, $cheque_id = NULL, $cheque_no = NULL,$transction_no = NULL,1, Auth::user()->id,$loanDetails->company_id );
                        //  $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef, $loanDetails->branch_id, $bank_id = NULL, $bank_ac_id = NULL, 28, 5, 529, $loanId, $createDayBook, $loanDetails->associate_member_id, $loanDetails->applicant_id, $branch_id_to = NULL, $loanDetails->branch_id,$gstAmount, '' . $loanDetails->account_number . ' Sgst  charge', 'DR', 0, 'INR',  $jv_unique_id = NULL, $v_no, $ssb_account_id_from = NULL, NULL,NULL, $ssb_account_tran_id_from = NULL,$cheque_type = NULL, $cheque_id = NULL, $cheque_no = NULL,$transction_no = NULL,1, Auth::user()->id,$loanDetails->company_id );
                    }
                    else{
                        $branchDayBook = CommanController::branchDayBookNew($dayBookRef, $loanDetails->branch_id, 5, 527, $loanId, $createDayBook, $loanDetails->associate_member_id, $loanDetails->applicant_id, $branch_id_to = NULL, $loanDetails->branch_id, $gstAmount,'IGST CHARGE ' . $loanDetails->account_number . '','IGST CHARGE ' . $loanDetails->account_number . '', 'IGST CHARGE ' . $loanDetails->account_number . '', 'CR', 5, 'INR',NULL,NULL,NULL,NULL,$entry_date = NULL,$entry_time = NULL,1,Auth::user()->id,$created_at = NULL,$ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL,$jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL,$loanDetails->company_id);
                        $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef, $loanDetails->branch_id, $bank_id = NULL, $bank_ac_id = NULL, $cgstHead,5, 527, $loanId, $createDayBook, $loanDetails->associate_member_id, $loanDetails->applicant_id, $branch_id_to = NULL, $loanDetails->branch_id, $gstAmount, '' . $loanDetails->account_number . 'IGST charge', 'CR', 5, 'INR', $jv_unique_id = NULL, $v_no, $ssb_account_id_from = NULL, NULL,NULL, $ssb_account_tran_id_from = NULL,$cheque_type = NULL, $cheque_id = NULL, $cheque_no = NULL,$transction_no = NULL,1, Auth::user()->id,$loanDetails->company_id );
                    }
                        $createdGstTransaction = CommanController::gstTransaction($dayBookId = $dayBookRef,$getGstSettingno->gst_no,(!isset($loanDetails['loanMemberCustom']->gst_no)) ? NULL : $loanDetails['loanMemberCustom']->gst_no,$insurance_amount,$getHeadSetting->gst_percentage,($IntraState == false ? $gstAmount : 0 ) ,($IntraState == true ? $gstAmount : 0),($IntraState== true ? $gstAmount : 0),($IntraState == true) ? $insurance_amount + $gstAmount + $gstAmount :$insurance_amount + $gstAmount,294,$request['date'],'IC294',$loanDetails['loanMemberCustom']->id,$loanDetails->branch_id,$loanDetails->company_id);
                }
                if($gstAmountFileChrage > 0 )
                {
                    $mlResult = Memberloans::find($loanId);
                    if($loanDetails->filecharge_cgst > 0)
                    {
                        $gstAmountFileChrage = $gstAmountFileChrage/2;
                        $branchDayBook = CommanController::branchDayBookNew($dayBookRef, $loanDetails->branch_id, 5, 530, $loanId, $createDayBook, $loanDetails->associate_member_id, $loanDetails->applicant_id, $branch_id_to = NULL, $loanDetails->branch_id, $gstAmountFileChrage, 'File Charge CGST ' . $loanDetails->account_number . '','File Charge CGST  ' . $loanDetails->account_number . '', 'File Charge CGST ' . $loanDetails->account_number . '', 'CR', 5, 'INR', NULL,NULL,NULL,NULL,$entry_date = NULL,$entry_time = NULL,1,Auth::user()->id,$ssb_account_tran_id_to = NULL,$created_at = NULL,$ssb_account_tran_id_from = NULL,$jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL,$loanDetails->company_id);
                        $branchDayBook = CommanController::branchDayBookNew($dayBookRef, $loanDetails->branch_id, 5, 531, $loanId, $createDayBook, $loanDetails->associate_member_id, $loanDetails->applicant_id, $branch_id_to = NULL, $loanDetails->branch_id, $gstAmountFileChrage, 'File Charge SGST ' . $loanDetails->account_number . '','File Charge SGST ' . $loanDetails->account_number . '', 'File Charge SGST' . $loanDetails->account_number . '', 'CR', 5, 'INR',NULL,NULL,NULL,NULL,$entry_date = NULL,$entry_time = NULL,1,Auth::user()->id,$ssb_account_tran_id_to = NULL,$created_at = NULL,$ssb_account_tran_id_from = NULL,$jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL,$loanDetails->company_id);
                        $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef, $loanDetails->branch_id, $bank_id = NULL, $bank_ac_id = NULL, $cgstHead,5, 530, $loanId, $createDayBook, $loanDetails->associate_member_id, $loanDetails->applicant_id, $branch_id_to = NULL, $loanDetails->branch_id,$gstAmountFileChrage, '' . $loanDetails->account_number . 'File Charge CGST ', 'CR', 5, 'INR',  $jv_unique_id = NULL, $v_no, $ssb_account_id_from = NULL, NULL,NULL, $ssb_account_tran_id_from = NULL,$cheque_type = NULL, $cheque_id = NULL, $cheque_no = NULL,$transction_no = NULL,1, Auth::user()->id,$loanDetails->company_id );
                        $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef, $loanDetails->branch_id, $bank_id = NULL, $bank_ac_id = NULL, $sgstHead,5, 531, $loanId, $createDayBook, $loanDetails->associate_member_id, $loanDetails->applicant_id, $branch_id_to = NULL, $loanDetails->branch_id, $gstAmountFileChrage, '' . $loanDetails->account_number . 'File Charge SGST ', 'CR', 5, 'INR', $jv_unique_id = NULL, $v_no, $ssb_account_id_from = NULL, NULL,NULL, $ssb_account_tran_id_from = NULL,$cheque_type = NULL, $cheque_id = NULL, $cheque_no = NULL,$transction_no = NULL,1, Auth::user()->id,$loanDetails->company_id );
                        //  $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef, $loanDetails->branch_id, $bank_id = NULL, $bank_ac_id = NULL, 28, 5, 530, $loanId, $createDayBook, $loanDetails->associate_member_id, $loanDetails->applicant_id, $branch_id_to = NULL, $loanDetails->branch_id, $gstAmountFileChrage, '' . $loanDetails->account_number . 'Loan File Charge Cgst  charge', 'DR', 0, 'INR',  $jv_unique_id = NULL, $v_no, $ssb_account_id_from = NULL, NULL,NULL, $ssb_account_tran_id_from = NULL,$cheque_type = NULL, $cheque_id = NULL, $cheque_no = NULL,$transction_no = NULL,1, Auth::user()->id,$loanDetails->company_id );
                        //  $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef, $loanDetails->branch_id, $bank_id = NULL, $bank_ac_id = NULL, 28, 5, 531, $loanId, $createDayBook, $loanDetails->associate_member_id, $loanDetails->applicant_id, $branch_id_to = NULL, $loanDetails->branch_id, $gstAmountFileChrage, '' . $loanDetails->account_number . 'Loan File Charge Sgst  charge', 'DR', 0, 'INR',$jv_unique_id = NULL, $v_no, $ssb_account_id_from = NULL, NULL,NULL, $ssb_account_tran_id_from = NULL,$cheque_type = NULL, $cheque_id = NULL, $cheque_no = NULL,$transction_no = NULL,1, Auth::user()->id,$loanDetails->company_id );
                    }
                    else{
                        $branchDayBook = CommanController::branchDayBookNew($dayBookRef, $loanDetails->branch_id, 5, 532, $loanId, $createDayBook, $loanDetails->associate_member_id, $loanDetails->applicant_id, $branch_id_to = NULL, $loanDetails->branch_id, $gstAmountFileChrage, 'IGST CHARGE ' . $loanDetails->account_number . '','IGST CHARGE ' . $loanDetails->account_number . '', 'File Charge IGST CHARGE ' . $loanDetails->account_number . '', 'CR', 5, 'INR', NULL,NULL,NULL,NULL,$entry_date = NULL,$entry_time = NULL,1,Auth::user()->id,$ssb_account_tran_id_to = NULL,$created_at = NULL,$ssb_account_tran_id_from = NULL,$jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL,$loanDetails->company_id);
                        $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef, $loanDetails->branch_id, $bank_id = NULL, $bank_ac_id = NULL, $cgstHead,5, 532, $loanId, $createDayBook, $loanDetails->associate_member_id, $loanDetails->applicant_id, $branch_id_to = NULL, $loanDetails->branch_id, $gstAmount, '' . $loanDetails->account_number . 'IGST charge', 'CR', 5, 'INR', $jv_unique_id = NULL, $v_no, $ssb_account_id_from = NULL, NULL,NULL, $ssb_account_tran_id_from = NULL,$cheque_type = NULL, $cheque_id = NULL, $cheque_no = NULL,$transction_no = NULL,1, Auth::user()->id,$loanDetails->company_id );
                    }
                    $createdGstTransaction = CommanController::gstTransaction($dayBookId = $dayBookRef,$getGstSettingno->gst_no,(!isset($loanDetails['loanMemberCustom']->gst_no)) ? NULL : $loanDetails['loanMemberCustom']->gst_no,$request['file_charge'],$getHeadSettingFileCHrage->gst_percentage,($IntraStateFile == false ? $gstAmountFileChrage : 0 ) ,($IntraStateFile == true ? $gstAmountFileChrage : 0),($IntraStateFile == true ? $gstAmountFileChrage : 0),($IntraStateFile == true ? $gstAmountFileChrage+ $request['file_charge'] + $gstAmountFileChrage  : $request['file_charge'] + $gstAmountFileChrage),90,$request['date'],'FC90',$loanDetails['loanMemberCustom']->id,$loanDetails->branch_id,$loanDetails->company_id);
                }
                if ($gstEcsChrage > 0) {
                    if ($loanDetails->ecs_charge_cgst > 0) {
                        $gstEcsChrage = $gstEcsChrage / 2;
                        $branchDayBook = CommanController::branchDayBookNew($dayBookRef, $loanDetails->branch_id, 5, 548, $loanId, $createDayBook, $loanDetails->associate_member_id, $loanDetails->applicant_id, $branch_id_to = NULL, $loanDetails->branch_id, $gstEcsChrage, 'ecs Charge CGST ' . $loanDetails->account_number . '', 'ecs Charge CGST  ' . $loanDetails->account_number . '', 'ecs Charge CGST ' . $loanDetails->account_number . '', 'CR', 5, 'INR', NULL, NULL, NULL, NULL, $entry_date = NULL, $entry_time = NULL, 1, Auth::user()->id, $ssb_account_tran_id_to = NULL, $created_at = NULL, $ssb_account_tran_id_from = NULL, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL, $loanDetails->company_id);

                        $branchDayBook = CommanController::branchDayBookNew($dayBookRef, $loanDetails->branch_id, 5, 549, $loanId, $createDayBook, $loanDetails->associate_member_id, $loanDetails->applicant_id, $branch_id_to = NULL, $loanDetails->branch_id, $gstEcsChrage, 'ecs Charge SGST ' . $loanDetails->account_number . '', 'ecs Charge SGST ' . $loanDetails->account_number . '', 'ecs Charge SGST' . $loanDetails->account_number . '', 'CR', 5, 'INR', NULL, NULL, NULL, NULL, $entry_date = NULL, $entry_time = NULL, 1, Auth::user()->id, $ssb_account_tran_id_to = NULL, $created_at = NULL, $ssb_account_tran_id_from = NULL, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL, $loanDetails->company_id);

                        $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef, $loanDetails->branch_id, $bank_id = NULL, $bank_ac_id = NULL, $cgstHead, 5, 548, $loanId, $createDayBook, $loanDetails->associate_member_id, $loanDetails->applicant_id, $branch_id_to = NULL, $loanDetails->branch_id, $gstEcsChrage, '' . $loanDetails->account_number .' ' . 'ecs Charge CGST ', 'CR', 5, 'INR', $jv_unique_id = NULL, $v_no, $ssb_account_id_from = NULL, NULL, NULL, $ssb_account_tran_id_from = NULL, $cheque_type = NULL, $cheque_id = NULL, $cheque_no = NULL, $transction_no = NULL, 1, Auth::user()->id, $loanDetails->company_id);

                        $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef, $loanDetails->branch_id, $bank_id = NULL, $bank_ac_id = NULL, $sgstHead, 5, 549, $loanId, $createDayBook, $loanDetails->associate_member_id, $loanDetails->applicant_id, $branch_id_to = NULL, $loanDetails->branch_id, $gstEcsChrage, '' . $loanDetails->account_number .' ' .'ecs Charge SGST ', 'CR', 5, 'INR', $jv_unique_id = NULL, $v_no, $ssb_account_id_from = NULL, NULL, NULL, $ssb_account_tran_id_from = NULL, $cheque_type = NULL, $cheque_id = NULL, $cheque_no = NULL, $transction_no = NULL, 1, Auth::user()->id, $loanDetails->company_id);



                    } else {
                        $branchDayBook = CommanController::branchDayBookNew($dayBookRef, $loanDetails->branch_id, 5, 550, $loanId, $createDayBook, $loanDetails->associate_member_id, $loanDetails->applicant_id, $branch_id_to = NULL, $loanDetails->branch_id, $gstEcsChrage, 'IGST CHARGE ' . $loanDetails->account_number . '', 'IGST CHARGE ' . $loanDetails->account_number . '', 'ecs Charge IGST CHARGE ' . $loanDetails->account_number . '', 'CR', 5, 'INR', NULL, NULL, NULL, NULL, $entry_date = NULL, $entry_time = NULL, 1, Auth::user()->id, $ssb_account_tran_id_to = NULL, $created_at = NULL, $ssb_account_tran_id_from = NULL, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL, $loanDetails->company_id);


                        $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef, $loanDetails->branch_id, $bank_id = NULL, $bank_ac_id = NULL, $cgstHead, 5, 550, $loanId, $createDayBook, $loanDetails->associate_member_id, $loanDetails->applicant_id, $branch_id_to = NULL, $loanDetails->branch_id, $gstAmount, '' . $loanDetails->account_number . 'IGST charge', 'CR', 5, 'INR', $jv_unique_id = NULL, $v_no, $ssb_account_id_from = NULL, NULL, NULL, $ssb_account_tran_id_from = NULL, $cheque_type = NULL, $cheque_id = NULL, $cheque_no = NULL, $transction_no = NULL, 1, Auth::user()->id, $loanDetails->company_id);

                    }
                    // dd($gstEcsChrage);
                    // $createdGstTransaction = CommanController::gstTransaction($dayBookId = $dayBookRef, $getGstSettingno->gst_no, (!isset($loanDetails['loanMemberCustom']->gst_no)) ? NULL : $loanDetails['loanMemberCustom']->gst_no, $request['ecs_amount'], $getEcsSetting->gst_percentage, ($IntraStateFile == false ? $gstEcsChrage : 0), ($IntraStateFile == true ? $gstEcsChrage : 0), ($IntraStateFile == true ? $gstEcsChrage : 0), ($IntraStateFile == true ? $gstEcsChrage + $request['ecs_amount'] + $gstEcsChrage : $request['ecs_amount'] + $gstEcsChrage), 90, $request['date'], 'FC90', $loanDetails['loanMemberCustom']->id, $loanDetails->branch_id, $loanDetails->company_id);
                    $createdGstTransaction = CommanController::gstTransaction($dayBookId = $dayBookRef, $getGstSettingno->gst_no, (!isset ($loanDetails['loanMemberCustom']->gst_no)) ? NULL : $loanDetails['loanMemberCustom']->gst_no, $request['ecs_amount'], $getEcsSetting->gst_percentage, ($IntraStateFile == false ? $gstEcsChrage : 0), ($IntraStateFile == true ? $gstEcsChrage : 0), ($IntraStateFile == true ? $gstEcsChrage : 0), ($IntraStateFile == true ? $gstEcsChrage + $request['ecs_amount'] + $gstEcsChrage : $request['ecs_amount'] + $gstEcsChrage), 434, $request['date'], 'EC434', $loanDetails['loanMemberCustom']->id, $loanDetails->branch_id, $loanDetails->company_id);
                }
                /************* Head Implement ****************/
            }

            elseif ($request['pay_file_charge'] != '' && $request['pay_file_charge'] == 1)
            {
                $mlResult = Memberloans::find($loanId);
                $lData['deposite_amount'] = $loanDetails->amount;
                $lData['due_amount'] = $loanDetails->amount;
                $lData['file_charge_type'] = '0';
                $lData['transfer_amount'] = $loan_amount;
                $lData['approve_date'] = date("Y-m-d", strtotime(convertDate($request['date'])));
                if ($loanDetails->emi_option == 1)
                {
                    $lData['closing_date'] = date('Y-m-d', strtotime("+" . $loanDetails->emi_period . " months", strtotime($lData['approve_date'])));
                }
                elseif ($loanDetails->emi_option == 2)
                {
                    $days = $loanDetails->emi_period * 7;
                    $start_date = $lData['approve_date'];
                    $date = strtotime($start_date);
                    $date = strtotime("+" . $days . " day", $date);
                    $lData['closing_date'] = date('Y-m-d', $date);
                }
                elseif ($loanDetails->emi_option == 3)
                {
                    $days = $loanDetails->emi_period;
                    $start_date = $lData['approve_date'];
                    $date = strtotime($start_date);
                    $date = strtotime("+" . $days . " day", $date);
                    $lData['closing_date'] = date('Y-m-d', $date);
                }
                $lData['status'] = 4;
                $mlResult->update($lData);
                $fileamountArraySsb = array(
                    '1' => $request['file_charge']
                );
                $ssbCreateTran =NULL;
                if($gstAmount > 0)
                {
                    if($loanDetails->insurance_cgst > 0)
                    {
                        $gstAmount=$gstAmount/2;
                        //Alpana -- gstAmount chage divided by 2
                        $branchDayBook = CommanController::branchDayBookNew($dayBookRef, $loanDetails->branch_id, 0, 528, $loanId, $createDayBook,$loanDetails->associate_member_id, $loanDetails->applicant_id, $branch_id_to = NULL, $loanDetails->branch_id, $gstAmount, 'CGST INSURANCE CHARGE ' . $loanDetails->account_number . '','CGST INSURANCE CHARGE ' . $loanDetails->account_number . '', 'CGST INSURANCE CHARGE ' . $loanDetails->account_number . '', 'CR', 0, 'INR', NULL,NULL,NULL,NULL,$entry_date = NULL,$entry_time = NULL,1,Auth::user()->id,$ssb_account_tran_id_to = NULL,$created_at = NULL,$ssb_account_tran_id_from = NULL,$jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL,$loanDetails->company_id);
                        $branchDayBook = CommanController::branchDayBookNew($dayBookRef, $loanDetails->branch_id, 0, 529, $loanId, $createDayBook,$loanDetails->associate_member_id, $loanDetails->applicant_id, $branch_id_to = NULL, $loanDetails->branch_id, $gstAmount, 'SGST INSURANCE CHARGE ' . $loanDetails->account_number . '','SGST INSURANCE CHARGE ' . $loanDetails->account_number . '', 'SGST INSURANCE CHARGE ' . $loanDetails->account_number . '', 'CR', 0, 'INR', NULL,NULL,NULL,NULL,$entry_date = NULL,$entry_time = NULL,1,Auth::user()->id,$ssb_account_tran_id_to = NULL,$created_at = NULL,$ssb_account_tran_id_from = NULL,$jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL,$loanDetails->company_id);
                        $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef, $loanDetails->branch_id, $bank_id = NULL, $bank_ac_id = NULL, $sgstHead,5, 529, $loanId, $createDayBook,$loanDetails->associate_member_id, $loanDetails->applicant_id, $branch_id_to = NULL, $loanDetails->branch_id,$gstAmount, '' . $loanDetails->account_number . ' SGST INSURANCE charge', 'CR', 0, 'INR', $jv_unique_id = NULL, $v_no, $ssb_account_id_from = NULL, NULL,NULL, $ssb_account_tran_id_from = NULL,$cheque_type = NULL, $cheque_id = NULL, $cheque_no = NULL,$transction_no = NULL,1, Auth::user()->id,$loanDetails->company_id );
                        $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef, $loanDetails->branch_id, $bank_id = NULL, $bank_ac_id = NULL, $sgstHead,5, 528, $loanId, $createDayBook,$loanDetails->associate_member_id, $loanDetails->applicant_id, $branch_id_to = NULL, $loanDetails->branch_id,$gstAmount, '' . $loanDetails->account_number . ' CGST INSURANCE charge', 'CR', 0, 'INR', $jv_unique_id = NULL, $v_no, $ssb_account_id_from = NULL, NULL,NULL, $ssb_account_tran_id_from = NULL,$cheque_type = NULL, $cheque_id = NULL, $cheque_no = NULL,$transction_no = NULL,1, Auth::user()->id,$loanDetails->company_id );
                        $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef, $loanDetails->branch_id, $bank_id = NULL, $bank_ac_id = NULL, 28, 5, 528, $loanId, $createDayBook,$loanDetails->associate_member_id, $loanDetails->applicant_id, $branch_id_to = NULL, $loanDetails->branch_id, $gstAmount, '' . $loanDetails->account_number . ' Cgst INSURANCE  charge', 'DR', 0, 'INR', $jv_unique_id = NULL, $v_no, $ssb_account_id_from = NULL, NULL,NULL, $ssb_account_tran_id_from = NULL,$cheque_type = NULL, $cheque_id = NULL, $cheque_no = NULL,$transction_no = NULL,1, Auth::user()->id,$loanDetails->company_id );
                        $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef, $loanDetails->branch_id, $bank_id = NULL, $bank_ac_id = NULL, 28, 5, 529, $loanId, $createDayBook,$loanDetails->associate_member_id, $loanDetails->applicant_id, $branch_id_to = NULL, $loanDetails->branch_id,$gstAmount, '' . $loanDetails->account_number . ' Sgst INSURANCE charge', 'DR', 0, 'INR',  $jv_unique_id = NULL, $v_no, $ssb_account_id_from = NULL, NULL,NULL, $ssb_account_tran_id_from = NULL,$cheque_type = NULL, $cheque_id = NULL, $cheque_no = NULL,$transction_no = NULL,1, Auth::user()->id,$loanDetails->company_id );
                    }
                    else{
                        $branchDayBook = CommanController::branchDayBookNew($dayBookRef, $loanDetails->branch_id, 5, 527, $loanId, $createDayBook,$loanDetails->associate_member_id, $loanDetails->applicant_id, $branch_id_to = NULL, $loanDetails->branch_id, $gstAmount, 'IGST INSURANCE CHARGE ' . $loanDetails->account_number . '','IGST INSURANCE CHARGE ' . $loanDetails->account_number . '', 'IGST INSURANCE CHARGE ' . $loanDetails->account_number . '', 'CR', 0, 'INR',NULL,NULL,NULL,NULL,$entry_date = NULL,$entry_time = NULL,1,Auth::user()->id,$ssb_account_tran_id_to = NULL,$created_at = NULL,$ssb_account_tran_id_from = NULL,$jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL,$loanDetails->company_id);
                        $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef, $loanDetails->branch_id, $bank_id = NULL, $bank_ac_id = NULL, 28, 5, 529, $loanId, $createDayBook,$loanDetails->associate_member_id, $loanDetails->applicant_id, $branch_id_to = NULL, $loanDetails->branch_id,$gstAmount, '' . $loanDetails->account_number . ' Igst INSURANCE charge', 'CR', 0, 'INR',  $jv_unique_id = NULL, $v_no, $ssb_account_id_from = NULL, NULL,NULL, $ssb_account_tran_id_from = NULL,$cheque_type = NULL, $cheque_id = NULL, $cheque_no = NULL,$transction_no = NULL,1, Auth::user()->id,$loanDetails->company_id );
                        $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef, $loanDetails->branch_id, $bank_id = NULL, $bank_ac_id = NULL, 28, 5, 529, $loanId, $createDayBook,$loanDetails->associate_member_id, $loanDetails->applicant_id, $branch_id_to = NULL, $loanDetails->branch_id, $gstAmount, '' . $loanDetails->account_number . 'Igst INSURANCE charge', 'DR', 0, 'INR', $jv_unique_id = NULL, $v_no, $ssb_account_id_from = NULL, NULL,NULL, $ssb_account_tran_id_from = NULL,$cheque_type = NULL, $cheque_id = NULL, $cheque_no = NULL,$transction_no = NULL,1, Auth::user()->id,$loanDetails->company_id );
                    }
                    $createdGstTransaction = CommanController::gstTransaction($dayBookId = $dayBookRef,$getGstSettingno->gst_no,(!isset($loanDetails['loanMemberCustom']->gst_no)) ? NULL : $loanDetails['loanMemberCustom']->gst_no,$insurance_amount,$getHeadSetting->gst_percentage,($IntraState == false ? $gstAmount : 0 ) ,($IntraState == true ? $gstAmount : 0),($IntraState== true ? $gstAmount : 0),($IntraState == true) ? $insurance_amount + $gstAmount + $gstAmount :$insurance_amount + $gstAmount,294,$request['date'],'IC294',$loanDetails['loanMemberCustom']->id,$loanDetails->branch_id,$loanDetails->company_id);
                }
                //File Charge Gst Charge Transaction
                if($gstAmountFileChrage > 0 )
                {
                    if($loanDetails->filecharge_cgst > 0)
                    {
                        $gstAmountFileChrage=$gstAmountFileChrage/2;
                        //Alpana -- gstAmount chage divided by 2
                        $branchDayBook = CommanController::branchDayBookNew($dayBookRef, $loanDetails->branch_id, 5, 530, $loanId, $createDayBook,$loanDetails->associate_member_id, $loanDetails->applicant_id, $branch_id_to = NULL, $loanDetails->branch_id, $gstAmountFileChrage, 'File CGST CHARGE ' . $loanDetails->account_number . '','File CGST CHARGE ' . $loanDetails->account_number . '', 'File CGST CHARGE ' . $loanDetails->account_number . '', 'CR', 0, 'INR',NULL,NULL,NULL,NULL,$entry_date = NULL,$entry_time = NULL,1,Auth::user()->id,$ssb_account_tran_id_to = NULL,$created_at = NULL,$ssb_account_tran_id_from = NULL,$jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL,$loanDetails->company_id);
                        $branchDayBook = CommanController::branchDayBookNew($dayBookRef, $loanDetails->branch_id, 5, 531, $loanId, $createDayBook,$loanDetails->associate_member_id, $loanDetails->applicant_id, $branch_id_to = NULL, $loanDetails->branch_id, $gstAmountFileChrage, 'File SGST CHARGE ' . $loanDetails->account_number . '','File SGST CHARGE ' . $loanDetails->account_number . '', 'File sGST CHARGE ' . $loanDetails->account_number . '', 'CR', 0, 'INR',NULL,NULL,NULL,NULL,$entry_date = NULL,$entry_time = NULL,1,Auth::user()->id,$ssb_account_tran_id_to = NULL,$created_at = NULL,$ssb_account_tran_id_from = NULL,$jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL,$loanDetails->company_id);
                        $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef, $loanDetails->branch_id, $bank_id = NULL, $bank_ac_id = NULL, $cgstHead,5, 530, $loanId, $createDayBook,$loanDetails->associate_member_id, $loanDetails->applicant_id, $branch_id_to = NULL, $loanDetails->branch_id,$gstAmountFileChrage, '' . $loanDetails->account_number . 'File CGST charge', 'CR', 0, 'INR', $jv_unique_id = NULL, $v_no, $ssb_account_id_from = NULL, NULL,NULL, $ssb_account_tran_id_from = NULL,$cheque_type = NULL, $cheque_id = NULL, $cheque_no = NULL,$transction_no = NULL,1, Auth::user()->id,$loanDetails->company_id );
                        $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef, $loanDetails->branch_id, $bank_id = NULL, $bank_ac_id = NULL, $sgstHead,5, 531, $loanId, $createDayBook,$loanDetails->associate_member_id, $loanDetails->applicant_id, $branch_id_to = NULL, $loanDetails->branch_id,$gstAmountFileChrage, '' . $loanDetails->account_number . 'File SGST charge', 'CR', 0, 'INR', $jv_unique_id = NULL, $v_no, $ssb_account_id_from = NULL, NULL,NULL, $ssb_account_tran_id_from = NULL,$cheque_type = NULL, $cheque_id = NULL, $cheque_no = NULL,$transction_no = NULL,1, Auth::user()->id,$loanDetails->company_id );
                        $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef, $loanDetails->branch_id, $bank_id = NULL, $bank_ac_id = NULL, 28, 5, 530, $loanId, $createDayBook,$loanDetails->associate_member_id, $loanDetails->applicant_id, $branch_id_to = NULL, $loanDetails->branch_id, $gstAmountFileChrage, '' . $loanDetails->account_number . 'File Cgst  charge', 'DR', 0, 'INR', $jv_unique_id = NULL, $v_no, $ssb_account_id_from = NULL, NULL,NULL, $ssb_account_tran_id_from = NULL,$cheque_type = NULL, $cheque_id = NULL, $cheque_no = NULL,$transction_no = NULL,1, Auth::user()->id,$loanDetails->company_id );
                        $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef, $loanDetails->branch_id, $bank_id = NULL, $bank_ac_id = NULL, 28, 5, 531, $loanId, $createDayBook,$loanDetails->associate_member_id, $loanDetails->applicant_id, $branch_id_to = NULL, $loanDetails->branch_id, $gstAmountFileChrage, '' . $loanDetails->account_number . 'File Sgst  charge', 'DR', 0, 'INR', $jv_unique_id = NULL, $v_no, $ssb_account_id_from = NULL, NULL,NULL, $ssb_account_tran_id_from = NULL,$cheque_type = NULL, $cheque_id = NULL, $cheque_no = NULL,$transction_no = NULL,1, Auth::user()->id,$loanDetails->company_id );
                    }
                    else{
                        $branchDayBook = CommanController::branchDayBookNew($dayBookRef, $loanDetails->branch_id, 5, 532, $loanId, $createDayBook,$loanDetails->associate_member_id, $loanDetails->applicant_id, $branch_id_to = NULL, $loanDetails->branch_id, $gstAmountFileChrage, 'IGST File CHARGE ' . $loanDetails->account_number . '','IGST File CHARGE ' . $loanDetails->account_number . '', 'IGST File CHARGE ' . $loanDetails->account_number . '', 'CR', 0, 'INR',NULL,NULL,NULL,NULL,$entry_date = NULL,$entry_time = NULL,1,Auth::user()->id,$ssb_account_tran_id_to = NULL,$created_at = NULL,$ssb_account_tran_id_from = NULL,$jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL,$loanDetails->company_id);
                        $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef, $loanDetails->branch_id, $bank_id = NULL, $bank_ac_id = NULL, 28, 5, 532, $loanId, $createDayBook,$loanDetails->associate_member_id, $loanDetails->applicant_id, $branch_id_to = NULL, $loanDetails->branch_id,$gstAmountFileChrage, '' . $loanDetails->account_number . ' Igst File charge', 'CR', 0, 'INR',  $jv_unique_id = NULL, $v_no, $ssb_account_id_from = NULL, NULL,NULL, $ssb_account_tran_id_from = NULL,$cheque_type = NULL, $cheque_id = NULL, $cheque_no = NULL,$transction_no = NULL,1, Auth::user()->id,$loanDetails->company_id );
                        $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef, $loanDetails->branch_id, $bank_id = NULL, $bank_ac_id = NULL, 28, 5, 532, $loanId, $createDayBook,$loanDetails->associate_member_id, $loanDetails->applicant_id, $branch_id_to = NULL, $loanDetails->branch_id, $gstAmountFileChrage, '' . $loanDetails->account_number . 'File Igst  charge', 'DR', 0, 'INR', $jv_unique_id = NULL, $v_no, $ssb_account_id_from = NULL, NULL,NULL, $ssb_account_tran_id_from = NULL,$cheque_type = NULL, $cheque_id = NULL, $cheque_no = NULL,$transction_no = NULL,1, Auth::user()->id,$loanDetails->company_id );
                    }
                    $createdGstTransaction = CommanController::gstTransaction($dayBookId = $dayBookRef,$getGstSettingno->gst_no,(!isset($loanDetails['loanMemberCustom']->gst_no)) ? NULL : $loanDetails['loanMemberCustom']->gst_no,$request['file_charge'],$getHeadSettingFileCHrage->gst_percentage,($IntraStateFile == false ? $gstAmountFileChrage : 0 ) ,($IntraStateFile == true ? $gstAmountFileChrage : 0),($IntraStateFile == true ? $gstAmountFileChrage : 0),($IntraStateFile == true ? $gstAmountFileChrage+ $request['file_charge'] + $gstAmountFileChrage  : $request['file_charge'] + $gstAmountFileChrage),90,$request['date'],'FC90',$loanDetails['loanMemberCustom']->id,$loanDetails->branch_id,$loanDetails->company_id);
                    //DD( $createdGstTransaction);
                }

                if ($gstEcsChrage > 0) {
                    if ($loanDetails->ecs_charge_cgst > 0) {
                        $gstEcsChrage = $gstEcsChrage / 2;
                        $branchDayBook = CommanController::branchDayBookNew($dayBookRef, $loanDetails->branch_id, 5, 548, $loanId, $createDayBook, $loanDetails->associate_member_id, $loanDetails->applicant_id, $branch_id_to = NULL, $loanDetails->branch_id, $gstEcsChrage, 'ecs Charge CGST ' . $loanDetails->account_number . '', 'ecs Charge CGST  ' . $loanDetails->account_number . '', 'ecs Charge CGST ' . $loanDetails->account_number . '', 'CR', 0, 'INR', NULL, NULL, NULL, NULL, $entry_date = NULL, $entry_time = NULL, 1, Auth::user()->id, $ssb_account_tran_id_to = NULL, $created_at = NULL, $ssb_account_tran_id_from = NULL, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL, $loanDetails->company_id);

                        $branchDayBook = CommanController::branchDayBookNew($dayBookRef, $loanDetails->branch_id, 5, 549, $loanId, $createDayBook, $loanDetails->associate_member_id, $loanDetails->applicant_id, $branch_id_to = NULL, $loanDetails->branch_id, $gstEcsChrage, 'ecs Charge SGST ' . $loanDetails->account_number . '', 'ecs Charge SGST ' . $loanDetails->account_number . '', 'ecs Charge SGST' . $loanDetails->account_number . '', 'CR', 0, 'INR', NULL, NULL, NULL, NULL, $entry_date = NULL, $entry_time = NULL, 1, Auth::user()->id, $ssb_account_tran_id_to = NULL, $created_at = NULL, $ssb_account_tran_id_from = NULL, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL, $loanDetails->company_id);

                        $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef, $loanDetails->branch_id, $bank_id = NULL, $bank_ac_id = NULL, $cgstHead, 5, 548, $loanId, $createDayBook, $loanDetails->associate_member_id, $loanDetails->applicant_id, $branch_id_to = NULL, $loanDetails->branch_id, $gstEcsChrage, '' . $loanDetails->account_number .' ' . 'ecs Charge CGST ', 'CR', 0, 'INR', $jv_unique_id = NULL, $v_no, $ssb_account_id_from = NULL, NULL, NULL, $ssb_account_tran_id_from = NULL, $cheque_type = NULL, $cheque_id = NULL, $cheque_no = NULL, $transction_no = NULL, 1, Auth::user()->id, $loanDetails->company_id);

                        $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef, $loanDetails->branch_id, $bank_id = NULL, $bank_ac_id = NULL, 28, 5, 548, $loanId, $createDayBook,$loanDetails->associate_member_id, $loanDetails->applicant_id, $branch_id_to = NULL, $loanDetails->branch_id, $gstEcsChrage, '' . $loanDetails->account_number . 'ecs Charge CGST', 'DR', 0, 'INR', $jv_unique_id = NULL, $v_no, $ssb_account_id_from = NULL, NULL,NULL, $ssb_account_tran_id_from = NULL,$cheque_type = NULL, $cheque_id = NULL, $cheque_no = NULL,$transction_no = NULL,1, Auth::user()->id,$loanDetails->company_id );

                        $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef, $loanDetails->branch_id, $bank_id = NULL, $bank_ac_id = NULL, $sgstHead, 5, 549, $loanId, $createDayBook, $loanDetails->associate_member_id, $loanDetails->applicant_id, $branch_id_to = NULL, $loanDetails->branch_id, $gstEcsChrage, '' . $loanDetails->account_number .' ' .'ecs Charge SGST ', 'CR', 0, 'INR', $jv_unique_id = NULL, $v_no, $ssb_account_id_from = NULL, NULL, NULL, $ssb_account_tran_id_from = NULL, $cheque_type = NULL, $cheque_id = NULL, $cheque_no = NULL, $transction_no = NULL, 1, Auth::user()->id, $loanDetails->company_id);


                        $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef, $loanDetails->branch_id, $bank_id = NULL, $bank_ac_id = NULL, 28, 5, 549, $loanId, $createDayBook,$loanDetails->associate_member_id, $loanDetails->applicant_id, $branch_id_to = NULL, $loanDetails->branch_id, $gstEcsChrage, '' . $loanDetails->account_number . 'ecs Charge SGST', 'DR', 0, 'INR', $jv_unique_id = NULL, $v_no, $ssb_account_id_from = NULL, NULL,NULL, $ssb_account_tran_id_from = NULL,$cheque_type = NULL, $cheque_id = NULL, $cheque_no = NULL,$transction_no = NULL,1, Auth::user()->id,$loanDetails->company_id );



                    } else {
                        $branchDayBook = CommanController::branchDayBookNew($dayBookRef, $loanDetails->branch_id, 5, 550, $loanId, $createDayBook, $loanDetails->associate_member_id, $loanDetails->applicant_id, $branch_id_to = NULL, $loanDetails->branch_id, $gstEcsChrage, 'IGST CHARGE ' . $loanDetails->account_number . '', 'IGST CHARGE ' . $loanDetails->account_number . '', 'ecs Charge IGST CHARGE ' . $loanDetails->account_number . '', 'CR', 0, 'INR', NULL, NULL, NULL, NULL, $entry_date = NULL, $entry_time = NULL, 1, Auth::user()->id, $ssb_account_tran_id_to = NULL, $created_at = NULL, $ssb_account_tran_id_from = NULL, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL, $loanDetails->company_id);


                        $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef, $loanDetails->branch_id, $bank_id = NULL, $bank_ac_id = NULL, $cgstHead, 5, 550, $loanId, $createDayBook, $loanDetails->associate_member_id, $loanDetails->applicant_id, $branch_id_to = NULL, $loanDetails->branch_id, $gstAmount, '' . $loanDetails->account_number . 'IGST charge', 'CR', 0, 'INR', $jv_unique_id = NULL, $v_no, $ssb_account_id_from = NULL, NULL, NULL, $ssb_account_tran_id_from = NULL, $cheque_type = NULL, $cheque_id = NULL, $cheque_no = NULL, $transction_no = NULL, 1, Auth::user()->id, $loanDetails->company_id);

                    }
                    // dd($gstEcsChrage);
                    $createdGstTransaction = CommanController::gstTransaction($dayBookId = $dayBookRef, $getGstSettingno->gst_no, (!isset ($loanDetails['loanMemberCustom']->gst_no)) ? NULL : $loanDetails['loanMemberCustom']->gst_no, $request['ecs_amount'], $getEcsSetting->gst_percentage, ($IntraStateFile == false ? $gstEcsChrage : 0), ($IntraStateFile == true ? $gstEcsChrage : 0), ($IntraStateFile == true ? $gstEcsChrage : 0), ($IntraStateFile == true ? $gstEcsChrage + $request['ecs_amount'] + $gstEcsChrage : $request['ecs_amount'] + $gstEcsChrage), 434, $request['date'], 'EC434', $loanDetails['loanMemberCustom']->id, $loanDetails->branch_id, $loanDetails->company_id);
                }
                /************* Head Implement ****************/
                $chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
                $v_no = "";
                for ($i = 0;$i < 10;$i++)
                {
                    $v_no .= $chars[mt_rand(0, strlen($chars) - 1) ];
                }
                $loan_amount = $request['file_charge'];
                // $dayBookRef = CommanController::createBranchDayBookReference($loan_amount - $insurance_amount);
                $branchDayBook = CommanController::branchDayBookNew($dayBookRef, $loanDetails->branch_id, 5, 57, $loanId, $createDayBook,$loanDetails->associate_member_id, $loanDetails->applicant_id, $branch_id_to = NULL, $loanDetails->branch_id, $loan_amount, '' . $loanDetails->account_number . ' file charge', 'To ' . $loanDetails->account_number . ' A/C Dr ' . $loan_amount . '', 'To Bank A/C Cr ' . $loanDetails->account_number . '', 'CR', 0, 'INR',NULL,NULL,NULL,NULL,$entry_date = NULL,$entry_time = NULL,1,Auth::user()->id,$created_at = NULL,$ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL,$jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL,$loanDetails->company_id);
                if($insurance_amount)
                {
                    $branchDayBook = CommanController::branchDayBookNew($dayBookRef, $loanDetails->branch_id, 5, 525, $loanId, $createDayBook,$loanDetails->associate_member_id, $loanDetails->applicant_id, $branch_id_to = NULL, $loanDetails->branch_id, $insurance_amount,'' . $loanDetails->account_number . ' insurance charge', 'To ' . $loanDetails->account_number . ' A/C Dr ' . $loan_amount . '', 'To Bank A/C Cr ' . $loanDetails->account_number . '', 'CR', 0, 'INR',NULL,NULL,NULL,NULL,$entry_date = NULL,$entry_time = NULL,1,Auth::user()->id,date("Y-m-d", strtotime(convertDate($request['date']))),$ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL,$jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL,$loanDetails->company_id);
                    $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef, $loanDetails->branch_id, $bank_id = NULL, $bank_ac_id = NULL, 294,5, 525, $loanId, $createDayBook,$loanDetails->associate_member_id, $loanDetails->applicant_id, $branch_id_to = NULL, $loanDetails->branch_id, $insurance_amount, '' . $loanDetails->account_number . ' insurance charge', 'CR', 0, 'INR', $jv_unique_id = NULL, $v_no, $ssb_account_id_from = NULL, NULL,NULL, $ssb_account_tran_id_from = NULL,$cheque_type = NULL, $cheque_id = NULL, $cheque_no = NULL,$transction_no = NULL,1, Auth::user()->id,$loanDetails->company_id );
                    $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef, $loanDetails->branch_id, $bank_id = NULL, $bank_ac_id = NULL, 28, 5, 525, $loanId, $createDayBook,$loanDetails->associate_member_id, $loanDetails->applicant_id, $branch_id_to = NULL, $loanDetails->branch_id, $insurance_amount, '' . $loanDetails->account_number . ' insurance charge', 'DR', 0, 'INR',  $jv_unique_id = NULL, $v_no, $ssb_account_id_from = NULL, NULL,NULL, $ssb_account_tran_id_from = NULL,$cheque_type = NULL, $cheque_id = NULL, $cheque_no = NULL,$transction_no = NULL,1, Auth::user()->id,$loanDetails->company_id );
                }
                if($request['ecs_amount'])
                {
                    $branchDayBook = CommanController::branchDayBookNew($dayBookRef, $loanDetails->branch_id, 5, 525, $loanId, $createDayBook,$loanDetails->associate_member_id, $loanDetails->applicant_id, $branch_id_to = NULL, $loanDetails->branch_id, $request['ecs_amount'],'' . $loanDetails->account_number . ' ECS charge', 'To ' . $loanDetails->account_number . ' A/C Dr ' . $loan_amount . '', 'To Bank A/C Cr ' . $loanDetails->account_number . '', 'CR', 0, 'INR',NULL,NULL,NULL,NULL,$entry_date = NULL,$entry_time = NULL,1,Auth::user()->id,date("Y-m-d", strtotime(convertDate($request['date']))),$ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL,$jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL,$loanDetails->company_id);
                    $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef, $loanDetails->branch_id, $bank_id = NULL, $bank_ac_id = NULL, 434,5, 525, $loanId, $createDayBook,$loanDetails->associate_member_id, $loanDetails->applicant_id, $branch_id_to = NULL, $loanDetails->branch_id, $request['ecs_amount'], '' . $loanDetails->account_number . ' ECS charge', 'CR', 0, 'INR', $jv_unique_id = NULL, $v_no, $ssb_account_id_from = NULL, NULL,NULL, $ssb_account_tran_id_from = NULL,$cheque_type = NULL, $cheque_id = NULL, $cheque_no = NULL,$transction_no = NULL,1, Auth::user()->id,$loanDetails->company_id );
                    $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef, $loanDetails->branch_id, $bank_id = NULL, $bank_ac_id = NULL, 28, 5, 525, $loanId, $createDayBook,$loanDetails->associate_member_id, $loanDetails->applicant_id, $branch_id_to = NULL, $loanDetails->branch_id, $request['ecs_amount'], '' . $loanDetails->account_number . ' ECS charge', 'DR', 0, 'INR',  $jv_unique_id = NULL, $v_no, $ssb_account_id_from = NULL, NULL,NULL, $ssb_account_tran_id_from = NULL,$cheque_type = NULL, $cheque_id = NULL, $cheque_no = NULL,$transction_no = NULL,1, Auth::user()->id,$loanDetails->company_id );
                }
                $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef, $loanDetails->branch_id, $bank_id = NULL, $bank_ac_id = NULL, 90, 5, 57, $loanId, $createDayBook,$loanDetails->associate_member_id, $loanDetails->applicant_id, $branch_id_to = NULL, $loanDetails->branch_id, $loan_amount, '' . $loanDetails->account_number . ' file charge', 'CR', 0, 'INR', $jv_unique_id = NULL, $v_no, $ssb_account_id_from = NULL, NULL,NULL, $ssb_account_tran_id_from = NULL,$cheque_type = NULL, $cheque_id = NULL, $cheque_no = NULL,$transction_no = NULL,1, Auth::user()->id,$loanDetails->company_id );
                $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef, $loanDetails->branch_id, $bank_id = NULL, $bank_ac_id = NULL, 28, 5, 57, $loanId, $createDayBook,$loanDetails->associate_member_id, $loanDetails->applicant_id, $branch_id_to = NULL, $loanDetails->branch_id, $loan_amount, '' . $loanDetails->account_number . ' file charge', 'DR', 0, 'INR', $jv_unique_id = NULL, $v_no, $ssb_account_id_from = NULL, NULL,NULL, $ssb_account_tran_id_from = NULL,$cheque_type = NULL, $cheque_id = NULL, $cheque_no = NULL,$transction_no = NULL,1, Auth::user()->id,$loanDetails->company_id );
                $mlResult = Memberloans::find($loanId);
                $mlResult->update($lData);
                /************* Head Implement ****************/
            }
            else
            {
                $mlResult = Memberloans::find($loanId);



                $lData['deposite_amount'] = $loanDetails->amount;
                $lData['due_amount'] = $loanDetails->amount;
                $lData['transfer_amount'] = $loanDetails->amount;
                $lData['status'] = 4;
                $lData['approve_date'] = date("Y-m-d", strtotime(convertDate($request['date'])));
                $lData['emi_date'] = date("Y-m-d", strtotime(convertDate($request['date'])));
                $lData['emi_due_amount'] =$mlResult->emi_amount;
                $lData['is_due'] =1;
                $lData['emi_day'] = $mlResult->emi_option;
                $lData['emi_due_date'] = $emiDueDate;
                $mlResult->update($lData);
            }



            // ECS updation code
                $mlResult = Memberloans::find($loanId);
                $lData['emi_date'] = date("Y-m-d", strtotime(convertDate($request['date'])));
                $lData['emi_due_amount'] = $mlResult->emi_amount;
                $lData['is_due'] = 1;

                // Extract day from the provided date
                $day = date('j', strtotime(convertDate($request['date'])));

                $lData['emi_day'] = null;
                if($mlResult->emi_option == 1){
                    // Set emi_day based on conditions
                    if ($day >= 1 && $day <= 12) {
                        $lData['emi_day'] = 12;
                    } elseif ($day >= 13 && $day <= 22) {
                        $lData['emi_day'] = 22;
                    } else {
                        $lData['emi_day'] = 30;
                    }
                }elseif($mlResult->emi_option == 2){
                    $lData['emi_day'] = 7;
                }else{
                    $lData['emi_day'] = 1;
                }


                if ($mlResult['emi_option'] == 3) {
                    $emiDueDate = Carbon::createFromFormat('d/m/Y', $request['date'])->addDay()->format('Y-m-d');
                } elseif ($mlResult['emi_option'] == 2) {
                    $emiDueDate = Carbon::createFromFormat('d/m/Y', $request['date'])->addDays(7)->format('Y-m-d');
                } elseif ($mlResult['emi_option'] == 1) {
                    $date = Carbon::createFromFormat('d/m/Y', $request['date']);
                    $currentDay = $date->day;
                    $date->addMonth();

                    if ($date->day != $currentDay) {
                        $date->endOfMonth();
                    }

                    $emiDueDate = $date->format('Y-m-d');
                }

                $lData['emi_due_date'] = $emiDueDate;
                $mlResult->update($lData);


            // Ecs updated code end here

            //  Comment on 31/08/2024
            // event(new UserActivity($mlResult,'Transfer Loan Amount',$request));
            if($loanDetails->loan_type != 4 && ($request->payment_mode == 1 || $request->payment_mode == 0) ){
                // $text = 'Dear Member, Loan of Rs.'. $loanDetails->amount. ' is transferred to your '.$transferMode.' on '. date('d/m/Y',strtotime(convertDate($request['date']))).' Loan A/C No ' .$loanDetails->account_number.' EMI Rs. '. $loanDetails->emi_amount.' '.$Mode.' Samraddh Bestwin Microfinance';;
                // $temaplteId = 1207166308925961267;
                // $contactNumber = array();
                // $memberDetail = Member::find($loanDetails->customer_id);
                // $contactNumber[] = $memberDetail->mobile_no;
                // $sendToMember = new Sms();
                // $sendToMember->sendSms( $contactNumber, $text, $temaplteId);
            }
            if($loanDetails->loan_type == 4 && ($request->payment_mode == 1 || $request->payment_mode == 0) ){
                // $text = 'Dear Member, Loan of Rs.'. $loanDetails->amount. ' is transferred to your '.$transferMode.' on '. date('d/m/Y',strtotime(convertDate($request['date']))).' Loan A/C No ' .$loanDetails->account_number.' Samraddh Bestwin Microfinance';
                // $temaplteId = 1207166565670370650;
                // $contactNumber = array();
                // $memberDetail = Member::find($loanDetails->customer_id);
                // $contactNumber[] = $memberDetail->mobile_no;
                // $sendToMember = new Sms();
                // $sendToMember->sendSms( $contactNumber, $text, $temaplteId);
            }
            $transferDate = date("Y-m-d", strtotime(convertDate($request['date'])));
            $currentDate = date("Y-m-d", strtotime(convertDate($globaldate)));

            // dd($currentDate,$loanDetails->account_number);
            if($transferDate != $currentDate)
            {
                // calling proceger in ddatabase so please check that for ferther date
                DB::select('call calculate_loan_interest_update(?,?,?)',[$currentDate,$loanDetails->account_number,1] );
            }

            $t = getLoanData($loanDetails->loan_type)->loan_category;

                $logdata = [
                    'loanId' => $loanId,
                    'loan_type' => $loanDetails->loan_type,
                    'loan_category' => $t,
                    'loan_name' => $loanDetails['loan']->name,
                    'title' => 'Loan Transfer',
                    'status' => 4,
                    'description' => 'A loan amount of ' . $request['hiddenTransferAmount'] . ' has been transferred to the customer through ' .
                    (($request['payment_mode'] == 0) ? 'SSB' : (($request['payment_mode'] == 1) ? 'Bank Transfer' : 'Cash')) . ', incorporating a ' .
                    (($loanDetails['pay_file_charge'] == 1) ? 'cash' : (($loanDetails['pay_file_charge'] == 0) ? 'loan' : '')) . ' file charge of ' .
                    ($request['file_charge'] ?? 0) . ' and an insurance charge of ' . ($request['insurance_amount'] ?? 0) . ' is applicable.',
                    'status_changed_date' => date("Y-m-d", strtotime(convertDate($request['date']))),
                    'created_by' => Auth::user()->id,
                    'user_name' => Auth::user()->username,
                    'created_by_name' => 'Admin',
                ];


            \App\Models\LoanLog::create($logdata);


            DB::commit();
        }
        catch(\Exception $ex)
        {
            DB::rollback();
            dd($ex->getLine(),$ex->getMessage());
            return back()->with(['alert'=>$ex->getMessage(),'line'=>$ex->getLine()]);
        }
        return redirect()
            ->route('admin.loan.request')
            ->with('success', 'Loan amount has been successfully transferred!');
        /*}else{
            return back()->with('alert', 'SSB Account does not exists!');
        }*/
    }

    public function transferGroupLoanAmount(Request $request)
    {
        $loanId = $request->loan_id;
        $payment_mode = $request->payment_mode;
        $insurance_amount = $request->insurance_amount1;
        $loanDetails = Grouploans::with('loanMember', 'loanSavingAccount2', 'loanBranch', 'loan', 'loanMemberAssociate')->where('id', $loanId)->first();
        if ($loanDetails->emi_option == 1) {
            $Mode = 'Monthly';
        } elseif ($loanDetails->emi_option == 2) {
            $Mode = 'Weekly';
        } elseif ($loanDetails->emi_option == 3) {
            $Mode = 'Daily';
        }

        if ($request->ssbaccount) {
            $ssbAccountDetails = SavingAccount::select('id', 'balance', 'branch_id', 'branch_code', 'member_id')->with('ssbmembersDataGet')->where('account_no', $request->ssbaccount)
                ->first();
            $ssb_account_number = $request->ssbaccount;
        } else {
            $ssb_account_number = 0;
        }
        $stateid = getBranchState($loanDetails['loanBranch']->name);
        $globaldate = checkMonthAvailability(date('d'), date('m'), date('Y'), $stateid);
        $getHeadSetting = \App\Models\HeadSetting::where('head_id', 294)->first();
        $getHeadSettingFileCHrage = \App\Models\HeadSetting::where('head_id', 90)->first();
        $getEcsSetting = \App\Models\HeadSetting::where('head_id', 434)->first();
        $getGstSetting = \App\Models\GstSetting::where('state_id', $loanDetails['loanBranch']->state_id)->where('applicable_date', '<=', $globaldate)->exists();
        $getGstSettingno = \App\Models\GstSetting::select('id', 'gst_no')->where('state_id', $loanDetails['loanBranch']->state_id)->where('applicable_date', '<=', $globaldate)->first();
        $countSsbResult = SavingAccount::where('account_no', $ssb_account_number)->count();
        $gstAmount = 0;
        $gstAmountFileChrage = 0;
        $gstAmount = 0;
        $gstAmountFileChrage = 0;
        $tragFAmount = 0;
        $tragAmount = 0;
        $tragEcsAmount = 0;
        if ($loanDetails->filecharge_cgst > 0) {
            if ($loanDetails->filecharge_sgst > 0) {
                $filecharge_sgst = $loanDetails->filecharge_sgst;
            }
            $IntraStateFile = true;
            $gstAmountFileChrage = $loanDetails->filecharge_cgst;
            $cgstHead = 171;
            $sgstHead = 172;
            $gstAmountFileChrage = ceil($gstAmountFileChrage);
            $tragAmount = $loanDetails->filecharge_cgst + $loanDetails->filecharge_sgst;
        }
        if ($loanDetails->filecharge_igst > 0) {
            $gstAmountFileChrage = $loanDetails->filecharge_igst;
            $IntraStateFile = false;
            $cgstHead = 170;
            $gstAmountFileChrage = ceil($gstAmountFileChrage);
            $tragAmount = $loanDetails->filecharge_igst;
        }
        if ($loanDetails->insurance_cgst > 0) {

            $gstAmount = $loanDetails->insurance_sgst;
            $IntraState = true;
            $cgstHead = 171;
            $sgstHead = 172;
            $gstAmount = ceil($gstAmount);
            $tragFAmount = $loanDetails->insurance_sgst + $loanDetails->insurance_igst;

        }
        if ($loanDetails->insurance_charge_igst > 0) {
            $gstAmount = ($loanDetails->insurance_charge_igst);
            $IntraState = false;
            $cgstHead = 170;
            $gstAmount = ceil($gstAmount);
            $tragFAmount = $loanDetails->insurance_charge_igst;
        }
        // Check ecs charge sGst Igst
        if ($loanDetails->ecs_charge_cgst > 0) {
            if ($loanDetails->ecs_charge_sgst > 0) {
                $ecscharge_sgst = $loanDetails->ecs_charge_sgst;
            }
            $IntraStateFile = true;
            $gstAmountEcs = $loanDetails->ecs_charge_cgst;
            $cgstHead = 171;
            $sgstHead = 172;
            $gstAmountEcs = ceil($gstAmountEcs);
            $tragEcsAmount = $loanDetails->ecs_charge_cgst + $loanDetails->ecs_charge_sgst;
        }
        if ($loanDetails->ecs_charge_igst > 0) {
            $gstAmountEcs = $loanDetails->ecs_charge_igst;
            $IntraStateFile = false;
            $cgstHead = 170;
            $gstAmountEcs = ceil($gstAmountEcs);
            $tragEcsAmount = $loanDetails->ecs_charge_igst;
        }
        // End Ecs charge Sgst Igst
        if ($payment_mode == 0) {
            if ($loanDetails['loanSavingAccount2']->account_no != $ssb_account_number) {
                return back()->with('alert', 'Applicant SSB Account Number does not exists!');
            }
            if ($countSsbResult == 0) {
                return back()->with('alert', 'SSB Account does not exists!');
            }
        }
        if ($payment_mode == 1) {
            $bank_id = $request['company_bank'];
            $bank_ac_id = getSamraddhBankAccount($request['company_bank_account_number'])->id;


            $bankBla = \App\Models\BankBalance::where('bank_id', $bank_id)->where('company_id', $loanDetails->company_id)->where('account_id', $bank_ac_id)->whereDate('entry_date', '<=', date("Y-m-d", strtotime(convertDate($request['date']))))->orderby('entry_date', 'desc')->sum('totalAmount');
            // $bankBla = SamraddhBankClosing::where('bank_id', $bank_id)->where('account_id', $bank_ac_id)->whereDate('entry_date', '<=', date("Y-m-d", strtotime(convertDate($request['date']))))->orderby('entry_date', 'desc')->first();
            if ($bankBla > 0) {
                // Changes By Anup SIr = 01-09-2022  && $bank_id != 2 (Aman jain )
                // dd($request['total_online_amount'], $bankBla->totalAmount,$bank_id,$loanDetails->company_id,$bank_ac_id,$request['date']);
                if (($request['total_online_amount'] > $bankBla) && ($bank_id != 2)) {
                    return back()
                        ->with('alert', 'Sufficient amount not available in bank account!');
                }
            } else {
                return back()
                    ->with('alert', 'Sufficient amount not available in bank account!');
            }
        }
        if ($loanDetails->deposite_amount > 0) {
            return back()
                ->with('alert', 'Loan amount already transferred to user’s SSB account!');
        }
        /*if($countSsbResult > 0){*/
        DB::beginTransaction();
        try {
            $entryTime = date("H:i:s");
            Session::put('created_at', date("Y-m-d", strtotime(convertDate($request['date']))));
            $amountArraySsb = array(
                '1' => $loanDetails->amount
            );
            $loan_amount = $loanDetails->amount;

            $dayBookRef = CommanController::createBranchDayBookReference($loan_amount);

            if ($payment_mode == 0) {
                $paymentMode = 4;
                $amount_deposit_by_name = $ssbAccountDetails['ssbmembersDataGet']->member->first_name . ' ' . $ssbAccountDetails['ssbmembersDataGet']->member->last_name;
                $transDate = date("Y-m-d " . $entryTime . "", strtotime(convertDate($request['date'])));
                $ssbHead = \App\Models\Plans::where('company_id', $loanDetails->company_id)->where('plan_category_code', 'S')->first();
                $ssbHead = $ssbHead->deposit_head_id;
                $record1 = \App\Models\SavingAccountTransactionView::where('account_no', $ssb_account_number)->where('opening_date', '<=', $transDate)->orderby('id', 'desc')->first();

                $ssb['saving_account_id'] = $ssbAccountDetails->id;
                $ssb['account_no'] = $ssb_account_number;
                if ($record1) {
                    if ($request['pay_file_charge'] == 0) {
                        $ssb['opening_balance'] = ($loanDetails->amount - $request['file_charge'] - $insurance_amount - $request['ecs_amount'] - $tragAmount - $tragFAmount -$tragEcsAmount) + $record1->opening_balance;
                        $ssb['deposit'] = $loanDetails->amount - $request['file_charge'] - $insurance_amount - $request['ecs_amount'] - $tragAmount - $tragFAmount -$tragEcsAmount ;
                        //- $tragAmount - $tragFAmount;
                    } else {
                        $ssb['opening_balance'] = $loanDetails->amount + $record1->opening_balance;
                        $ssb['deposit'] = $loanDetails->amount;
                    }
                } else {
                    if ($request['pay_file_charge'] == 0) {
                        $ssb['opening_balance'] = ($loanDetails->amount - $request['file_charge'] - $insurance_amount - $request['ecs_amount'] - $tragAmount - $tragFAmount -$tragEcsAmount);
                        $ssb['deposit'] = $loanDetails->amount - $request['file_charge'] - $insurance_amount - $request['ecs_amount']- $tragAmount - $tragFAmount - $tragEcsAmount;
                    } else {
                        $ssb['opening_balance'] = $loanDetails->amount;
                        $ssb['deposit'] = $loanDetails->amount;
                    }
                }
                $ssb['branch_id'] = $loanDetails->branch_id;
                $ssb['type'] = 6;
                $ssb['withdrawal'] = 0;
                $ssb['description'] = 'Transferred loan amount to SSB' . $loanDetails->account_number;
                $ssb['currency_code'] = 'INR';
                $ssb['payment_type'] = 'CR';
                $ssb['payment_mode'] = 3;
                $ssb['created_at'] = date("Y-m-d" . $entryTime . "", strtotime(convertDate($request['date'])));
                $ssbAccountTran = SavingAccountTranscation::create($ssb);
                $saTranctionId = $ssbAccountTran->id;
                $transferMode = 'SSB';
                if ($request['pay_file_charge'] == 0) {
                    $balance_update = ($loanDetails->amount - $request['file_charge'] - $insurance_amount - $request['ecs_amount']  /*- $gstAmount - $gstAmountFileChrage*/) + $ssbAccountDetails->balance;
                } else {
                    $balance_update = $loanDetails->amount + $ssbAccountDetails->balance;
                }
                $ssbBalance = SavingAccount::find($ssbAccountDetails->id);
                $ssbBalance->balance = $balance_update;
                $ssbBalance->save();
                $record2 = \App\Models\SavingAccountTransactionView::where('account_no', $ssb_account_number)->whereDate('opening_date', '>', date("Y-m-d", strtotime(convertDate($request['date']))))->get();
                foreach ($record2 as $key => $value) {
                    $nsResult = SavingAccountTranscation::find($value->transaction_id);
                    if ($request['pay_file_charge'] == 0) {
                        $nsResult['opening_balance'] = ($loanDetails->amount - $request['file_charge'] - $insurance_amount - $request['ecs_amount'] - $gstAmount - $gstAmountFileChrage - $gstAmountEcs) + $value->opening_balance;
                    } else {
                        $nsResult['opening_balance'] = $loanDetails->amount + $value->opening_balance;
                    }
                    $nsResult['updated_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($request['date'])));
                    $nsResult->save();
                }
                $data['saving_account_transaction_id'] = $saTranctionId;
                $data['loan_id'] = $loanId;
                $data['created_at'] = date("Y-m-d", strtotime(convertDate($request['date'])));
                //Create BranchDaybook Transaction and generate Daybook id
                // $dayBookRef = CommanController::createBranchDayBookReference($loan_amount);
                $satRef = $dayBookRef;
                $satRefId = $dayBookRef;
                $description = 'Loan amount to ssb';

            } elseif ($payment_mode == 1) {
                $satRefId = NULL;
                $transferMode = 'BANK';
                if ($request['bank_transfer_mode'] == 0 && $request['bank_transfer_mode'] != '') {
                    $paymentMode = 1;
                } elseif ($request['bank_transfer_mode'] == 1) {
                    $paymentMode = 3;
                }
                $saTranctionId = '';
            } elseif ($payment_mode == 2) {
                $satRefId = NULL;
                $paymentMode = 2;
                $saTranctionId = '';
            }
            $amount_deposit_by_name = $loanDetails['loanMemberAssociate']->first_name . ' ' . $loanDetails['loanMemberAssociate']->last_name;

            if ($payment_mode == 0) {
                $loan_head_id = $loanDetails['loan']->head_id;

                /************* Head Implement ****************/
                $chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
                $v_no = "";
                for ($i = 0; $i < 10; $i++) {
                    $v_no .= $chars[mt_rand(0, strlen($chars) - 1)];
                }
                if ($request['pay_file_charge'] == 0) {
                    $loan_amount = $loanDetails->amount - $request['file_charge'] - $insurance_amount - $request['ecs_amount']- $tragAmount - $tragFAmount - $tragEcsAmount;
                    $totalLoanAmount = $loanDetails->amount;
                    $tAmount = $loan_amount;
                } else {
                    $loan_amount = $loanDetails->amount;
                    $totalLoanAmount = $loanDetails->amount;
                    $tAmount = $loan_amount;
                }
                // $dayBookRef = CommanController::createBranchDayBookReference($loan_amount);
                //Sanction Entry in  loan Daybook
                $createLoanDayBook = $createDayBook = CommanController::createLoanDayBook($dayBookRef, $dayBookRef, $loanDetails->loan_type, 2, $loanId, $lId = NULL, $loanDetails->account_number, $loanDetails->applicant_id, 0, 0, 0, $loanDetails->amount, 'Loan Sanction', $loanDetails->branch_id, getBranchCode($loanDetails->branch_id)->branch_code, 'CR', 'INR', 3, NULL, NULL, $branch_name = NULL, date("Y-m-d", strtotime(convertDate($request['date']))), NULL, 1, 1, NULL, $loanDetails->account_number, NULL, NULL, NULL, $request['branch'], 0, 0, 0, $loanDetails->company_id, null);


                $branchDayBook = CommanController::branchDayBookNew($dayBookRef, $loanDetails->branch_id, 5, 54, $loanId, $saTranctionId, $loanDetails->associate_member_id, $loanDetails->member_id, $loanDetails->branch_id, $loanDetails->branch_id, $totalLoanAmount, 'Payment transfer for Group Loan Sanction', 'To ' . $loanDetails->account_number . ' A/C Dr ' . $totalLoanAmount . '', 'To Bank A/C Cr ' . $loanDetails->account_number . '', 'DR', 3, 'INR', $v_no, $ssb_account_id_from = NULL, $cheque_no = NULL, $transction_no = NULL, $entry_date = NULL, $entry_time = NULL, 1, Auth::user()->id, $created_at = NULL, $saTranctionId, $ssb_account_tran_id_from = NULL, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL, $loanDetails->company_id);


                $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef, $loanDetails->branch_id, $bank_id = NULL, $bank_ac_id = NULL, $loan_head_id, 5, 54, $loanId, $saTranctionId, $loanDetails->associate_member_id, $loanDetails->member_id, $branch_id_to = NULL, $loanDetails->branch_id, $totalLoanAmount, 'Payment transfer for group Loan Sanction', 'DR', 3, 'INR', $jv_unique_id = NULL, $v_no, $ssb_account_id_from = NULL, $ssbAccountDetails->id, $saTranctionId, $ssb_account_tran_id_from = NULL, $cheque_type = NULL, $cheque_id = NULL, $cheque_no = NULL, $transction_no = NULL, 1, Auth::user()->id, $loanDetails->company_id);


                $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef, $loanDetails->branch_id, $bank_id = NULL, $bank_ac_id = NULL, $ssbHead, 5, 54, $loanId, $saTranctionId, $loanDetails->associate_member_id, $loanDetails->member_id, $branch_id_to = NULL, $loanDetails->branch_id, $loan_amount, 'Payment transfer for Group Loan Sanction', 'CR', 3, 'INR', $jv_unique_id = NULL, $v_no, $ssb_account_id_from = NULL, $ssbAccountDetails->id, $saTranctionId, $ssb_account_tran_id_from = NULL, $cheque_type = NULL, $cheque_id = NULL, $cheque_no = NULL, $transction_no = NULL, 1, Auth::user()->id, $loanDetails->company_id);



                /************* Head Implement ****************/
            } elseif ($payment_mode == 1) {
                // $loan_head_id = 66;
                $loan_head_id = $loanDetails['loan']->head_id;
                $headNewId = $loan_head_id;
                if ($request['pay_file_charge'] == 0) {
                    $loan_amount = $loanDetails->amount - $request['file_charge'] - $request['ecs_amount'] - $tragAmount-$tragEcsAmount - $tragFAmount - $insurance_amount;
                    $totalLoanAmount = $loanDetails->amount;
                    $tAmount = $loan_amount;
                } else {
                    $loan_amount = $loanDetails->amount;
                    $totalLoanAmount = $loanDetails->amount;
                    $tAmount = $loan_amount;
                }
                if ($request['bank_transfer_mode'] == 0 && $request['bank_transfer_mode'] != '') {

                    $payment_type = 1;
                    $amount_from_id = $request['company_bank'];
                    $amount_from_name = getSamraddhBank($request['company_bank'])->bank_name;
                    $cheque_id = getSamraddhChequeData($request['cheque_id'])->id;
                    $cheque_no = $request['cheque_id'];
                    $cheque_date = getSamraddhChequeData($request['cheque_id'])->cheque_create_date;
                    $cheque_bank_from = $request['company_bank'];
                    $cheque_bank_ac_from = $request['company_bank_account_number'];
                    $cheque_bank_ifsc_from = getSamraddhBankAccount($request['company_bank_account_number'])->ifsc_code;
                    $cheque_bank_ac_from_id = getSamraddhBankAccount($request['company_bank_account_number'])->id;
                    $cheque_bank_branch_from = NULL;
                    $cheque_bank_to = NULL;
                    $cheque_bank_ac_to = $request['customer_bank_account_number'];
                    $cheque_type = 1;
                    $v_no = NULL;
                    $v_date = NULL;
                    $ssb_account_id_from = NULL;
                    $transction_no = NULL;
                    $transction_bank_from = $request['company_bank'];
                    $transction_bank_ac_from = $request['company_bank_account_number'];
                    $transction_bank_ifsc_from = getSamraddhBankAccount($request['company_bank_account_number'])->ifsc_code;
                    $transction_bank_from_ac_id = getSamraddhBankAccount($request['company_bank_account_number'])->id;
                    $transction_bank_branch_from = NULL;
                    $transction_bank_to = NULL;
                    $transction_bank_ac_to = $request['customer_bank_account_number'];
                    $transction_bank_to_name = $request['customer_bank_name'];
                    $transction_bank_to_ac_no = $request['customer_bank_account_number'];
                    $transction_bank_to_branch = $request['customer_branch_name'];
                    $transction_bank_to_ifsc = $request['customer_ifsc_code'];
                    $transferAccount = $request['customer_bank_account_number'];

                } elseif ($request['bank_transfer_mode'] == 1) {
                    $payment_type = 2;
                    $amount_from_id = $request['company_bank'];
                    $amount_from_name = getSamraddhBank($request['company_bank'])->bank_name;
                    $cheque_type = NULL;
                    $cheque_id = NULL;
                    $cheque_no = NULL;
                    $cheque_date = NULL;
                    $cheque_bank_from = NULL;
                    $cheque_bank_ac_from = NULL;
                    $cheque_bank_ac_from_id = NULL;
                    $cheque_bank_ifsc_from = NULL;
                    $cheque_bank_branch_from = NULL;
                    $cheque_bank_to = NULL;
                    $cheque_bank_ac_to = NULL;
                    $transction_no = $request['utr_transaction_number'];
                    $v_no = NULL;
                    $v_date = NULL;
                    $ssb_account_id_from = NULL;
                    $transction_bank_from = $request['company_bank'];
                    $transction_bank_ac_from = $request['company_bank_account_number'];
                    $transction_bank_ifsc_from = getSamraddhBankAccount($request['company_bank_account_number'])->ifsc_code;
                    $transction_bank_from_ac_id = getSamraddhBankAccount($request['company_bank_account_number'])->id;
                    $transction_bank_branch_from = NULL;
                    $transction_bank_to = NULL;
                    $transction_bank_ac_to = $request['customer_bank_account_number'];
                    $transction_bank_to_name = $request['customer_bank_name'];
                    $transction_bank_to_ac_no = $request['customer_bank_account_number'];
                    $transction_bank_to_branch = $request['customer_branch_name'];
                    $transction_bank_to_ifsc = $request['customer_ifsc_code'];
                    $transferAccount = $request['customer_bank_account_number'];
                }
                // $dayBookRef = CommanController::createBranchDayBookReference($loan_amount);
                //Sanction Entry in  loan Daybook
                $createLoanDayBook = $createDayBook = CommanController::createLoanDayBook($dayBookRef, $dayBookRef, $loanDetails->loan_type, 2, $loanId, $lId = NULL, $loanDetails->account_number, $loanDetails->applicant_id, 0, 0, 0, $loanDetails->amount, 'Loan Sanction', $loanDetails->branch_id, getBranchCode($loanDetails->branch_id)->branch_code, 'CR', 'INR', $payment_type, NULL, NULL, $branch_name = NULL, date("Y-m-d", strtotime(convertDate($request['date']))), NULL, 1, 1, NULL, $loanDetails->account_number, NULL, NULL, NULL, $request['branch'], 0, 0, 0, $loanDetails->company_id, null);




                $branchDayBook = CommanController::branchDayBookNew($dayBookRef, $loanDetails->branch_id, 5, 54, $loanId, $createDayBook, $loanDetails->associate_member_id, $loanDetails->member_id, $branch_id_to = NULL, $loanDetails->branch_id, $totalLoanAmount, 'Payment transfer for Group Loan Sanction', 'To ' . $loanDetails->account_number . ' A/C Cr ' . $totalLoanAmount . '', 'To Bank A/C Dr ' . $loanDetails->account_number . '', 'DR', $payment_type, 'INR', NULL, NULL, NULL, NULL, $entry_date = NULL, $entry_time = NULL, 1, Auth::user()->id, $created_at = NULL, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $jv_unique_id = NULL, 1, $cheque_no, $loanDetails->company_id);


                $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef, $loanDetails->branch_id, $amount_from_id, getSamraddhBankAccount($request['company_bank_account_number'])->id, $loan_head_id, 5, 54, $loanId, $createDayBook, $loanDetails->associate_member_id, $loanDetails->member_id, $branch_id_to = NULL, $loanDetails->branch_id, $totalLoanAmount, 'Payment transfer for Group Loan Sanction', 'DR', $payment_type, 'INR', $jv_unique_id = NULL, NULL, $ssb_account_id_from = NULL, NULL, NULL, $ssb_account_tran_id_from = NULL, 1, $cheque_id, $cheque_no, $transction_no = NULL, 1, Auth::user()->id, $loanDetails->company_id);


                $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef, $loanDetails->branch_id, $amount_from_id, getSamraddhBankAccount($request['company_bank_account_number'])->id, getSamraddhBank($request['company_bank'])->account_head_id, 5, 54, $loanId, $createDayBook, $loanDetails->associate_member_id, $loanDetails->member_id, $branch_id_to = NULL, $loanDetails->branch_id, ($loan_amount + $request['rtgs_neft_charge']), 'Payment transfer for Loan Sanction', 'CR', $payment_type, 'INR', $jv_unique_id = NULL, NULL, $ssb_account_id_from = NULL, NULL, NULL, $ssb_account_tran_id_from = NULL, $cheque_type, $cheque_id, $cheque_no, $transction_no = NULL, 1, Auth::user()->id, $loanDetails->company_id);




                if ($request['bank_transfer_mode'] == 1) {


                    $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef, $loanDetails->branch_id, $bank_id = NULL, $bank_ac_id = NULL, 92, 5, 522, $loanId, $createDayBook, $loanDetails->associate_member_id, $loanDetails->member_id, $branch_id_to = NULL, $loanDetails->branch_id, $request['rtgs_neft_charge'], 'NEFT Charge A/c Dr ' . $request['rtgs_neft_charge'] . '', 'DR', $payment_type, 'INR', $jv_unique_id = NULL, NULL, $ssb_account_id_from = NULL, NULL, NULL, $ssb_account_tran_id_from = NULL, NULL, NULL, NULL, NULL, 1, Auth::user()->id, $loanDetails->company_id);


                    $branchDayBook = CommanController::branchDayBookNew($dayBookRef, $loanDetails->branch_id, 5, 54, $loanId, $createDayBook, $loanDetails->associate_member_id, $loanDetails->member_id, $branch_id_to = NULL, $loanDetails->branch_id, $request['rtgs_neft_charge'], 'NEFT Charge', 'Bank Charge To ' . $loanDetails->account_number . ' A/C Dr ' . $totalLoanAmount . '', 'To Bank A/C Cr ' . $loanDetails->account_number . '', 'DR', $payment_type, 'INR', $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date = NULL, $entry_time = NULL, 1, Auth::user()->id, date("Y-m-d", strtotime(convertDate($request['date']))), $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL, $loanDetails->company_id);

                }

                $samraddhBankDaybook = CommanController::samraddhBankDaybookNew($dayBookRef, $amount_from_id, getSamraddhBankAccount($request['company_bank_account_number'])->id, 5, 54, $loanId, $createDayBook, $loanDetails->associate_member_id, $loanDetails->applicant_id, $loanDetails->branch_id, $loan_amount + $request['rtgs_neft_charge'], $loan_amount + $request['rtgs_neft_charge'], $loan_amount + $request['rtgs_neft_charge'], 'Payment transfer for Loan Sanction', 'Cash A/c Dr ' . ($loan_amount + $request['rtgs_neft_charge']) . '', 'NEFT Charge A/c Dr ' . $request['rtgs_neft_charge'] . '', 'DR', $payment_type, 'INR', $loanDetails->applicant_id, getMemberCustom($loanDetails->customer_id)->first_name . ' ' . getMemberCustom($loanDetails->customer_id)->last_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $amount_from_name, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc, $transction_date = NULL, $entry_date = NULL, $entry_time = NULL, 1, Auth::user()->id, $created_at = NULL, $jv_unique_id = NULL, 1, $cheque_id, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $loanDetails->company_id);

                if ($request['bank_transfer_mode'] == 0 && $request['bank_transfer_mode'] != '') {

                    SamraddhCheque::where('cheque_no', $cheque_no)->update(['status' => 3, 'is_use' => 1]);
                    SamraddhChequeIssue::create([
                        'cheque_id' => getSamraddhChequeData($request['cheque_id'])->id,
                        'type' => 3,
                        'sub_type' => 33,
                        'type_id' => $loanId,
                        'cheque_issue_date' => date("Y-m-d", strtotime(convertDate($request['date']))),
                        'status' => 1,
                    ]);
                }
            } elseif ($payment_mode == 2) {
                $v_no = NULL;

                $loan_head_id = $loanDetails['loan']->head_id;
                $headNewId = $loan_head_id;
                if ($request['pay_file_charge'] == 0) {
                    $loan_amount = $loanDetails->amount - $request['file_charge'] - $request['ecs_amount'] - $insurance_amount - $tragAmount - $tragFAmount - $tragEcsAmount;
                    $totalLoanAmount = $loanDetails->amount;
                } else {
                    $loan_amount = $loanDetails->amount;
                    $totalLoanAmount = $loanDetails->amount;
                }
                // $dayBookRef = CommanController::createBranchDayBookReference($loan_amount);
                //Sanction Entry in  loan Daybook
                $createLoanDayBook = $createDayBook = CommanController::createLoanDayBook($dayBookRef, $dayBookRef, $loanDetails->loan_type, 2, $loanId, $lId = NULL, $loanDetails->account_number, $loanDetails->applicant_id, 0, 0, 0, $loanDetails->amount, 'Loan Sanction', $loanDetails->branch_id, getBranchCode($loanDetails->branch_id)->branch_code, 'CR', 'INR', 0, NULL, NULL, $branch_name = NULL, date("Y-m-d", strtotime(convertDate($request['date']))), NULL, 1, 1, NULL, $loanDetails->account_number, NULL, NULL, NULL, $request['branch'], 0, 0, 0, $loanDetails->company_id, null);


                $branchDayBook = CommanController::branchDayBookNew($dayBookRef, $loanDetails->branch_id, 5, 54, $loanId, $createDayBook, $loanDetails->associate_member_id, $loanDetails->member_id, $loanDetails->branch_id, $loanDetails->branch_id, $totalLoanAmount, 'Payment transfer for Group Loan Sanction', 'To ' . $loanDetails->account_number . ' A/C Dr ' . $totalLoanAmount . '', 'To Bank A/C Cr ' . $loanDetails->account_number . '', 'DR', 0, 'INR', NULL, NULL, NULL, NULL, date("Y-m-d", strtotime(convertDate($request['date']))), NULL, 1, Auth::user()->id, date("Y-m-d", strtotime(convertDate($request['date']))), $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL, $loanDetails->company_id);


                $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef, $loanDetails->branch_id, $bank_id = NULL, $bank_ac_id = NULL, $loan_head_id, 5, 54, $loanId, $createDayBook, $loanDetails->associate_member_id, $loanDetails->member_id, $branch_id_to = NULL, $loanDetails->branch_id, $totalLoanAmount, 'Payment transfer for Group Loan Sanction', 'DR', 0, 'INR', $jv_unique_id = NULL, NULL, $ssb_account_id_from = NULL, NULL, NULL, $ssb_account_tran_id_from = NULL, $cheque_type = NULL, $cheque_id = NULL, $cheque_no = NULL, $transction_no = NULL, 1, Auth::user()->id, $loanDetails->company_id);

                //............................. Optimization code...........................//
                $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef, $loanDetails->branch_id, $bank_id = NULL, $bank_ac_id = NULL, 28, 5, 54, $loanId, $createDayBook, $loanDetails->associate_member_id, $loanDetails->member_id, $branch_id_to = NULL, $loanDetails->branch_id, $loan_amount, 'Payment transfer for Group Loan Sanction', 'CR', 0, 'INR', $jv_unique_id = NULL, NULL, $ssb_account_id_from = NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, Auth::user()->id, $loanDetails->company_id);


            }
            if ($request['pay_file_charge'] == 0) {
                $glResult = Grouploans::find($loanId);
                $glData['deposite_amount'] = $loanDetails->amount - $request['file_charge'] - $insurance_amount - $request['ecs_amount'];
                $glData['due_amount'] = $loanDetails->amount;
                $glData['file_charge_type'] = '1';
                $glData['transfer_amount'] = $loan_amount;
                $glData['approve_date'] = date("Y-m-d", strtotime(convertDate($request['date'])));
                if ($loanDetails->emi_option == 1) {
                    $glData['closing_date'] = date('Y-m-d', strtotime("+" . $loanDetails->emi_period . " months", strtotime($glData['approve_date'])));
                } elseif ($loanDetails->emi_option == 2) {
                    $days = $loanDetails->emi_period * 7;
                    $start_date = $glData['approve_date'];
                    $date = strtotime($start_date);
                    $date = strtotime("+" . $days . " day", $date);
                    $glData['closing_date'] = date('Y-m-d', $date);
                } elseif ($loanDetails->emi_option == 3) {
                    $days = $loanDetails->emi_period;
                    $start_date = $glData['approve_date'];
                    $date = strtotime($start_date);
                    $date = strtotime("+" . $days . " day", $date);
                    $glData['closing_date'] = date('Y-m-d', $date);
                }
                $glData['status'] = 4;
                $glResult->update($glData);
                $mgLoan = Memberloans::where('id', $loanDetails->member_loan_id)
                    ->first();
                $mLoanAmount = $mgLoan->deposite_amount + $loanDetails->amount;
                $mlResult = Memberloans::find($loanDetails->member_loan_id);
                $lData['deposite_amount'] = $mLoanAmount - $request['file_charge'];
                $lData['due_amount'] = $mgLoan->due_amount + $loanDetails->amount;
                $lData['approve_date'] = date("Y-m-d", strtotime(convertDate($request['date'])));
                $lData['status'] = 4;
                $mlResult->update($lData);
                $fileamountArraySsb = array(
                    '1' => $request['file_charge']
                );

                /************* Head Implement ****************/
                $chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
                $v_no = "";
                for ($i = 0; $i < 10; $i++) {
                    $v_no .= $chars[mt_rand(0, strlen($chars) - 1)];
                }
                /*if($request['pay_file_charge'] == 0){
                        $loan_amount=$loanDetails->amount-$request['file_charge'];
                    }else{
                        $loan_amount=$loanDetails->amount;
                    }*/
                $loan_amount = $request['file_charge'];
                // $dayBookRef = CommanController::createBranchDayBookReference($loan_amount + $insurance_amount);
                $branchDayBook = CommanController::branchDayBookNew($dayBookRef, $loanDetails->branch_id, 5, 58, $loanId, $createDayBook, $loanDetails->associate_member_id, $loanDetails->member_id, $branch_id_to = NULL, $loanDetails->branch_id, $loan_amount, 'To ' . $loanDetails->account_number . ' A/C Dr ' . $loan_amount . '', 'To ' . $loanDetails->account_number . ' A/C Dr ' . $loan_amount . '', 'To Bank A/C Cr ' . $loanDetails->account_number . '', 'CR', 5, 'INR', NULL, NULL, NULL, NULL, $entry_date = NULL, $entry_time = NULL, 1, Auth::user()->id, date("Y-m-d", strtotime(convertDate($request['date']))), $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL, $loanDetails->company_id);



                $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef, $loanDetails->branch_id, $bank_id = NULL, $bank_ac_id = NULL, 90, 5, 58, $loanId, $createDayBook, $loanDetails->associate_member_id, $loanDetails->member_id, $branch_id_to = NULL, $loanDetails->branch_id, $loan_amount, 'To ' . $loanDetails->account_number . ' A/C Dr ' . $loan_amount . '', 'CR', 5, 'INR', $jv_unique_id = NULL, $v_no, $ssb_account_id_from = NULL, NULL, NULL, $ssb_account_tran_id_from = NULL, $cheque_type = NULL, $cheque_id = NULL, $cheque_no = NULL, $transction_no = NULL, 1, Auth::user()->id, $loanDetails->company_id);

                if ($insurance_amount > 0) {
                    $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef, $loanDetails->branch_id, $bank_id = NULL, $bank_ac_id = NULL, 294, 5, 526, $loanId, $createDayBook, $loanDetails->associate_member_id, $loanDetails->member_id, $branch_id_to = NULL, $loanDetails->branch_id, $insurance_amount, 'To ' . $loanDetails->account_number . ' A/C Dr ' . $insurance_amount . '', 'CR', 5, 'INR', $jv_unique_id = NULL, $v_no, $ssb_account_id_from = NULL, NULL, NULL, $ssb_account_tran_id_from = NULL, $cheque_type = NULL, $cheque_id = NULL, $cheque_no = NULL, $transction_no = NULL, 1, Auth::user()->id, $loanDetails->company_id);

                    $branchDayBook = CommanController::branchDayBookNew($dayBookRef, $loanDetails->branch_id, 5, 525, $loanId, $createDayBook, $loanDetails->associate_member_id, $loanDetails->member_id, $branch_id_to = NULL, $loanDetails->branch_id, $insurance_amount, 'To ' . $loanDetails->account_number . ' A/C Dr ' . $loan_amount . '', 'To ' . $loanDetails->account_number . ' A/C Dr ' . $insurance_amount . '', 'To Bank A/C Cr ' . $loanDetails->account_number . '', 'CR', 5, 'INR', NULL, NULL, NULL, NULL, $entry_date = NULL, $entry_time = NULL, 1, Auth::user()->id, date("Y-m-d", strtotime(convertDate($request['date']))), $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL, $loanDetails->company_id);

                }

                if ($request['ecs_amount'] > 0) {
                    $branchDayBook = CommanController::branchDayBookNew($dayBookRef, $loanDetails->branch_id, 5, 547, $loanId, $createDayBook, $loanDetails->associate_member_id, $loanDetails->member_id, $branch_id_to = NULL, $loanDetails->branch_id, $request['ecs_amount'], '' . $loanDetails->account_number . 'Group Loan ECS charge', 'To ' . $loanDetails->account_number . ' A/C Dr ' . $loan_amount . '', 'To Bank A/C Cr ' . $loanDetails->account_number . '', 'CR', 5, 'INR', NULL, NULL, NULL, NULL, $entry_date = NULL, $entry_time = NULL, 1, Auth::user()->id, date("Y-m-d", strtotime(convertDate($request['date']))), $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL, $loanDetails->company_id);

                    $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef, $loanDetails->branch_id, $bank_id = NULL, $bank_ac_id = NULL, 434, 5, 547, $loanId, $createDayBook, $loanDetails->associate_member_id, $loanDetails->member_id, $branch_id_to = NULL, $loanDetails->branch_id, $request['ecs_amount'], '' . $loanDetails->account_number . 'Group Loan ECS charge', 'CR', 5, 'INR', $jv_unique_id = NULL, $v_no, $ssb_account_id_from = NULL, NULL, NULL, $ssb_account_tran_id_from = NULL, $cheque_type = NULL, $cheque_id = NULL, $cheque_no = NULL, $transction_no = NULL, 1, Auth::user()->id, $loanDetails->company_id);

                    // $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef, $loanDetails->branch_id, $bank_id = NULL, $bank_ac_id = NULL, 28, 5, 547, $loanId, $createDayBook, $loanDetails->associate_member_id, $loanDetails->member_id, $branch_id_to = NULL, $loanDetails->branch_id, $request['ecs_amount'], '' . $loanDetails->account_number . 'Group Loan ECS charge', 'DR', 0, 'INR', $jv_unique_id = NULL, $v_no, $ssb_account_id_from = NULL, NULL, NULL, $ssb_account_tran_id_from = NULL, $cheque_type = NULL, $cheque_id = NULL, $cheque_no = NULL, $transction_no = NULL, 1, Auth::user()->id, $loanDetails->company_id);
                }
                //Gst Charge Foir Branch DayBook
                if ($gstAmount > 0) {
                    $mlResult = Grouploans::find($loanId);
                    ;
                    if ($IntraState) {




                        $branchDayBook = CommanController::branchDayBookNew($dayBookRef, $loanDetails->branch_id, 5, 536, $loanId, $createDayBook, $loanDetails->associate_member_id, $loanDetails->member_id, $branch_id_to = NULL, $loanDetails->branch_id, $gstAmount, 'GROUP CGST INSURANCE CHARGE ' . $loanDetails->account_number . '', 'GROUP CGST INSURANCE CHARGE ' . $loanDetails->account_number . '', 'GROUP CGST INSURANCE CHARGE ' . $loanDetails->account_number . '', 'CR', 5, 'INR', NULL, NULL, NULL, NULL, $entry_date = NULL, $entry_time = NULL, 1, Auth::user()->id, $created_at = NULL, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL, $loanDetails->company_id);




                        $branchDayBook = CommanController::branchDayBookNew($dayBookRef, $loanDetails->branch_id, 5, 537, $loanId, $createDayBook, $loanDetails->associate_member_id, $loanDetails->member_id, $branch_id_to = NULL, $loanDetails->branch_id, $gstAmount, 'GROUP SGST INSURANCE CHARGE ' . $loanDetails->account_number . '', 'GROUP SGST INSURANCE CHARGE ' . $loanDetails->account_number . '', 'GROUP SGST INSURANCE CHARGE ' . $loanDetails->account_number . '', 'CR', 5, 'INR', NULL, NULL, NULL, NULL, $entry_date = NULL, $entry_time = NULL, 1, Auth::user()->id, $created_at = NULL, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL, $loanDetails->company_id);


                        $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef, $loanDetails->branch_id, $bank_id = NULL, $bank_ac_id = NULL, $cgstHead, 5, 536, $loanId, $createDayBook, $loanDetails->associate_member_id, $loanDetails->member_id, $branch_id_to = NULL, $loanDetails->branch_id, $gstAmount, '' . $loanDetails->account_number . ' GROUP CGST INSURANCE CHARGE', 'CR', 5, 'INR', $jv_unique_id = NULL, $v_no, $ssb_account_id_from = NULL, NULL, NULL, $ssb_account_tran_id_from = NULL, $cheque_type = NULL, $cheque_id = NULL, $cheque_no = NULL, $transction_no = NULL, 1, Auth::user()->id, $loanDetails->company_id);

                        $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef, $loanDetails->branch_id, $bank_id = NULL, $bank_ac_id = NULL, $sgstHead, 5, 537, $loanId, $createDayBook, $loanDetails->associate_member_id, $loanDetails->member_id, $branch_id_to = NULL, $loanDetails->branch_id, $gstAmount, '' . $loanDetails->account_number . ' GROUP SGST INSURANCE CHARGE', 'CR', 5, 'INR', $jv_unique_id = NULL, $v_no, $ssb_account_id_from = NULL, NULL, NULL, $ssb_account_tran_id_from = NULL, $cheque_type = NULL, $cheque_id = NULL, $cheque_no = NULL, $transction_no = NULL, 1, Auth::user()->id, $loanDetails->company_id);



                        //  $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef, $loanDetails->branch_id, $bank_id = NULL, $bank_ac_id = NULL, 28, 5, 536, $loanId, $createDayBook, $loanDetails->associate_member_id, $loanDetails->member_id, $branch_id_to = NULL, $loanDetails->branch_id, $gstAmount,'' . $loanDetails->account_number . ' GROUP CGST INSURANCE CHARGE', 'DR', 0, 'INR', $jv_unique_id = NULL, $v_no, $ssb_account_id_from = NULL, NULL,NULL, $ssb_account_tran_id_from = NULL,$cheque_type = NULL, $cheque_id = NULL, $cheque_no = NULL,$transction_no = NULL,1, Auth::user()->id,$loanDetails->company_id );

                        // $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef, $loanDetails->branch_id, $bank_id = NULL, $bank_ac_id = NULL, 28, 5, 537, $loanId, $createDayBook, $loanDetails->associate_member_id, $loanDetails->member_id, $branch_id_to = NULL, $loanDetails->branch_id,$gstAmount, '' . $loanDetails->account_number . 'GROUP SGST INSURANCE CHARGE', 'DR', 0, 'INR',  $jv_unique_id = NULL, $v_no, $ssb_account_id_from = NULL, NULL,NULL, $ssb_account_tran_id_from = NULL,$cheque_type = NULL, $cheque_id = NULL, $cheque_no = NULL,$transction_no = NULL,1, Auth::user()->id,$loanDetails->company_id );






                    } else {
                        $branchDayBook = CommanController::branchDayBookNew($dayBookRef, $loanDetails->branch_id, 5, 538, $loanId, $createDayBook, $loanDetails->associate_member_id, $loanDetails->member_id, $branch_id_to = NULL, $loanDetails->branch_id, $gstAmount, 'GROUP IGST INSURANCE CHARGE ' . $loanDetails->account_number . '', 'GROUP IGST INSURANCE CHARGE ' . $loanDetails->account_number . '', 'GROUP IGST INSURANCE CHARGE ' . $loanDetails->account_number . '', 'CR', 5, 'INR', NULL, NULL, NULL, NULL, $entry_date = NULL, $entry_time = NULL, 1, Auth::user()->id, $created_at = NULL, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL, $loanDetails->company_id);

                        $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef, $loanDetails->branch_id, $bank_id = NULL, $bank_ac_id = NULL, $cgstHead, 5, 538, $loanId, $createDayBook, $loanDetails->associate_member_id, $loanDetails->member_id, $branch_id_to = NULL, $loanDetails->branch_id, $gstAmount, '' . $loanDetails->account_number . 'GROUP IGST INSURANCE CHARGE', 'CR', 5, 'INR', $jv_unique_id = NULL, $v_no, $ssb_account_id_from = NULL, NULL, NULL, $ssb_account_tran_id_from = NULL, $cheque_type = NULL, $cheque_id = NULL, $cheque_no = NULL, $transction_no = NULL, 1, Auth::user()->id, $loanDetails->company_id);
                    }
                    $createdGstTransaction = CommanController::gstTransaction($dayBookId = $dayBookRef, $getGstSettingno->gst_no, (!isset($loanDetails['loanMember']->gst_no)) ? NULL : $loanDetails['loanMember']->gst_no, $insurance_amount, $getHeadSetting->gst_percentage, ($IntraState == false ? $gstAmount : 0), ($IntraState == true ? $gstAmount : 0), ($IntraState == true ? $gstAmount : 0), ($IntraState == true) ? $insurance_amount + $gstAmount + $gstAmount : $insurance_amount + $gstAmount, 294, $request['date'], 'IC294', $loanDetails['loanMember']->id, $loanDetails->branch_id, $loanDetails->company_id);
                }
                if ($gstAmountFileChrage > 0) {
                    $mlResult = Grouploans::find($loanId);
                    if ($IntraStateFile) {






                        $branchDayBook = CommanController::branchDayBookNew($dayBookRef, $loanDetails->branch_id, 5, 539, $loanId, $createDayBook, $loanDetails->associate_member_id, $loanDetails->member_id, $branch_id_to = NULL, $loanDetails->branch_id, $gstAmountFileChrage, 'Group Loan File Charge CGST ' . $loanDetails->account_number . '', 'Group Loan File Charge CGST  ' . $loanDetails->account_number . '', 'Group Loan  File Charge CGST ' . $loanDetails->account_number . '', 'CR', 5, 'INR', NULL, NULL, NULL, NULL, $entry_date = NULL, $entry_time = NULL, 1, Auth::user()->id, $ssb_account_tran_id_to = NULL, $created_at = NULL, $ssb_account_tran_id_from = NULL, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL, $loanDetails->company_id);

                        $branchDayBook = CommanController::branchDayBookNew($dayBookRef, $loanDetails->branch_id, 5, 540, $loanId, $createDayBook, $loanDetails->associate_member_id, $loanDetails->member_id, $branch_id_to = NULL, $loanDetails->branch_id, $gstAmountFileChrage, 'Group Laon File Charge SGST ' . $loanDetails->account_number . '', 'Group Loan File Charge SGST ' . $loanDetails->account_number . '', 'File Charge SGST' . $loanDetails->account_number . '', 'CR', 5, 'INR', NULL, NULL, NULL, NULL, $entry_date = NULL, $entry_time = NULL, 1, Auth::user()->id, $ssb_account_tran_id_to = NULL, $created_at = NULL, $ssb_account_tran_id_from = NULL, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL, $loanDetails->company_id);

                        $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef, $loanDetails->branch_id, $bank_id = NULL, $bank_ac_id = NULL, $cgstHead, 5, 539, $loanId, $createDayBook, $loanDetails->associate_member_id, $loanDetails->member_id, $branch_id_to = NULL, $loanDetails->branch_id, $gstAmountFileChrage, '' . $loanDetails->account_number . 'Group Loan  File Charge CGST ', 'CR', 5, 'INR', $jv_unique_id = NULL, $v_no, $ssb_account_id_from = NULL, NULL, NULL, $ssb_account_tran_id_from = NULL, $cheque_type = NULL, $cheque_id = NULL, $cheque_no = NULL, $transction_no = NULL, 1, Auth::user()->id, $loanDetails->company_id);

                        $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef, $loanDetails->branch_id, $bank_id = NULL, $bank_ac_id = NULL, $sgstHead, 5, 540, $loanId, $createDayBook, $loanDetails->associate_member_id, $loanDetails->member_id, $branch_id_to = NULL, $loanDetails->branch_id, $gstAmountFileChrage, '' . $loanDetails->account_number . 'Group Loan File Charge SGST ', 'CR', 5, 'INR', $jv_unique_id = NULL, $v_no, $ssb_account_id_from = NULL, NULL, NULL, $ssb_account_tran_id_from = NULL, $cheque_type = NULL, $cheque_id = NULL, $cheque_no = NULL, $transction_no = NULL, 1, Auth::user()->id, $loanDetails->company_id);

                        // $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef, $loanDetails->branch_id, $bank_id = NULL, $bank_ac_id = NULL, 28, 5, 539, $loanId, $createDayBook, $loanDetails->associate_member_id, $loanDetails->member_id, $branch_id_to = NULL, $loanDetails->branch_id, $gstAmountFileChrage, '' . $loanDetails->account_number . 'Group Loan Loan File Charge Cgst  charge', 'DR', 5, 'INR',  $jv_unique_id = NULL, $v_no, $ssb_account_id_from = NULL, NULL,NULL, $ssb_account_tran_id_from = NULL,$cheque_type = NULL, $cheque_id = NULL, $cheque_no = NULL,$transction_no = NULL,1, Auth::user()->id,$loanDetails->company_id );

                        // $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef, $loanDetails->branch_id, $bank_id = NULL, $bank_ac_id = NULL, 28, 5, 540, $loanId, $createDayBook, $loanDetails->associate_member_id, $loanDetails->member_id, $branch_id_to = NULL, $loanDetails->branch_id, $gstAmountFileChrage, '' . $loanDetails->account_number . 'Group Loan  Loan File Charge Sgst  charge', 'DR', 5, 'INR',$jv_unique_id = NULL, $v_no, $ssb_account_id_from = NULL, NULL,NULL, $ssb_account_tran_id_from = NULL,$cheque_type = NULL, $cheque_id = NULL, $cheque_no = NULL,$transction_no = NULL,1, Auth::user()->id,$loanDetails->company_id );
                    } else {

                        $branchDayBook = CommanController::branchDayBookNew($dayBookRef, $loanDetails->branch_id, 5, 541, $loanId, $createDayBook, $loanDetails->associate_member_id, $loanDetails->member_id, $branch_id_to = NULL, $loanDetails->branch_id, $gstAmount, 'GROUP IGST File CHARGE ' . $loanDetails->account_number . '', 'GROUP IGST File CHARGE ' . $loanDetails->account_number . '', 'GROUP IGST File CHARGE ' . $loanDetails->account_number . '', 'CR', 5, 'INR', NULL, NULL, NULL, NULL, $entry_date = NULL, $entry_time = NULL, 1, Auth::user()->id, $created_at = NULL, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL, $loanDetails->company_id);

                        $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef, $loanDetails->branch_id, $bank_id = NULL, $bank_ac_id = NULL, $cgstHead, 5, 541, $loanId, $createDayBook, $loanDetails->associate_member_id, $loanDetails->member_id, $branch_id_to = NULL, $loanDetails->branch_id, $gstAmount, '' . $loanDetails->account_number . 'GROUP IGST File CHARGE', 'CR', 5, 'INR', $jv_unique_id = NULL, $v_no, $ssb_account_id_from = NULL, NULL, NULL, $ssb_account_tran_id_from = NULL, $cheque_type = NULL, $cheque_id = NULL, $cheque_no = NULL, $transction_no = NULL, 1, Auth::user()->id, $loanDetails->company_id);
                    }
                    $createdGstTransaction = CommanController::gstTransaction($dayBookId = $dayBookRef, $getGstSettingno->gst_no, (!isset($loanDetails['loanMember']->gst_no)) ? NULL : $loanDetails['loanMember']->gst_no, $request['file_charge'], $getHeadSettingFileCHrage->gst_percentage, ($IntraStateFile == false ? $gstAmountFileChrage : 0), ($IntraStateFile == true ? $gstAmountFileChrage : 0), ($IntraStateFile == true ? $gstAmountFileChrage : 0), ($IntraStateFile == true ? $gstAmountFileChrage + $request['file_charge'] + $gstAmountFileChrage : $request['file_charge'] + $gstAmountFileChrage), 90, $request['date'], 'FC90', $loanDetails['loanMember']->id, $loanDetails->branch_id, $loanDetails->company_id);
                }
                if (isset($gstAmountEcs) && $gstAmountEcs > 0) {
                    $mlResult = Grouploans::find($loanId);
                    if ($IntraStateFile) {

                        $branchDayBook = CommanController::branchDayBookNew($dayBookRef, $loanDetails->branch_id, 5, 548, $loanId, $createDayBook, $loanDetails->associate_member_id, $loanDetails->member_id, $branch_id_to = NULL, $loanDetails->branch_id, $gstAmountEcs, 'Group Loan ECS Charge CGST ' . $loanDetails->account_number . '', 'Group Loan ECS Charge CGST  ' . $loanDetails->account_number . '', 'Group Loan  ECS Charge CGST ' . $loanDetails->account_number . '', 'CR', 5, 'INR', NULL, NULL, NULL, NULL, $entry_date = NULL, $entry_time = NULL, 1, Auth::user()->id, $ssb_account_tran_id_to = NULL, $created_at = NULL, $ssb_account_tran_id_from = NULL, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL, $loanDetails->company_id);

                        $branchDayBook = CommanController::branchDayBookNew($dayBookRef, $loanDetails->branch_id, 5, 549, $loanId, $createDayBook, $loanDetails->associate_member_id, $loanDetails->member_id, $branch_id_to = NULL, $loanDetails->branch_id, $gstAmountEcs, 'Group Laon ECS Charge SGST ' . $loanDetails->account_number . '', 'Group Loan ECS Charge SGST ' . $loanDetails->account_number . '', 'ECS Charge SGST' . $loanDetails->account_number . '', 'CR', 5, 'INR', NULL, NULL, NULL, NULL, $entry_date = NULL, $entry_time = NULL, 1, Auth::user()->id, $ssb_account_tran_id_to = NULL, $created_at = NULL, $ssb_account_tran_id_from = NULL, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL, $loanDetails->company_id);

                        $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef, $loanDetails->branch_id, $bank_id = NULL, $bank_ac_id = NULL, $cgstHead, 5, 548, $loanId, $createDayBook, $loanDetails->associate_member_id, $loanDetails->member_id, $branch_id_to = NULL, $loanDetails->branch_id, $gstAmountEcs, '' . $loanDetails->account_number . 'Group Loan  ECS Charge CGST ', 'CR', 5, 'INR', $jv_unique_id = NULL, $v_no, $ssb_account_id_from = NULL, NULL, NULL, $ssb_account_tran_id_from = NULL, $cheque_type = NULL, $cheque_id = NULL, $cheque_no = NULL, $transction_no = NULL, 1, Auth::user()->id, $loanDetails->company_id);

                        $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef, $loanDetails->branch_id, $bank_id = NULL, $bank_ac_id = NULL, $sgstHead, 5, 549, $loanId, $createDayBook, $loanDetails->associate_member_id, $loanDetails->member_id, $branch_id_to = NULL, $loanDetails->branch_id, $gstAmountEcs, '' . $loanDetails->account_number . 'Group Loan ECS Charge SGST ', 'CR', 5, 'INR', $jv_unique_id = NULL, $v_no, $ssb_account_id_from = NULL, NULL, NULL, $ssb_account_tran_id_from = NULL, $cheque_type = NULL, $cheque_id = NULL, $cheque_no = NULL, $transction_no = NULL, 1, Auth::user()->id, $loanDetails->company_id);

                        // $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef, $loanDetails->branch_id, $bank_id = NULL, $bank_ac_id = NULL, 28, 5, 539, $loanId, $createDayBook, $loanDetails->associate_member_id, $loanDetails->member_id, $branch_id_to = NULL, $loanDetails->branch_id, $gstAmountEcs, '' . $loanDetails->account_number . 'Group Loan Loan File Charge Cgst  charge', 'DR', 5, 'INR',  $jv_unique_id = NULL, $v_no, $ssb_account_id_from = NULL, NULL,NULL, $ssb_account_tran_id_from = NULL,$cheque_type = NULL, $cheque_id = NULL, $cheque_no = NULL,$transction_no = NULL,1, Auth::user()->id,$loanDetails->company_id );

                        // $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef, $loanDetails->branch_id, $bank_id = NULL, $bank_ac_id = NULL, 28, 5, 540, $loanId, $createDayBook, $loanDetails->associate_member_id, $loanDetails->member_id, $branch_id_to = NULL, $loanDetails->branch_id, $gstAmountEcs, '' . $loanDetails->account_number . 'Group Loan  Loan File Charge Sgst  charge', 'DR', 5, 'INR',$jv_unique_id = NULL, $v_no, $ssb_account_id_from = NULL, NULL,NULL, $ssb_account_tran_id_from = NULL,$cheque_type = NULL, $cheque_id = NULL, $cheque_no = NULL,$transction_no = NULL,1, Auth::user()->id,$loanDetails->company_id );
                    } else {

                        $branchDayBook = CommanController::branchDayBookNew($dayBookRef, $loanDetails->branch_id, 5, 550, $loanId, $createDayBook, $loanDetails->associate_member_id, $loanDetails->member_id, $branch_id_to = NULL, $loanDetails->branch_id, $gstAmount, 'GROUP IGST ECS CHARGE ' . $loanDetails->account_number . '', 'GROUP IGST ECS CHARGE ' . $loanDetails->account_number . '', 'GROUP IGST ECS CHARGE ' . $loanDetails->account_number . '', 'CR', 5, 'INR', NULL, NULL, NULL, NULL, $entry_date = NULL, $entry_time = NULL, 1, Auth::user()->id, $created_at = NULL, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL, $loanDetails->company_id);

                        $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef, $loanDetails->branch_id, $bank_id = NULL, $bank_ac_id = NULL, $cgstHead, 5, 550, $loanId, $createDayBook, $loanDetails->associate_member_id, $loanDetails->member_id, $branch_id_to = NULL, $loanDetails->branch_id, $gstAmount, '' . $loanDetails->account_number . 'GROUP IGST ECS CHARGE', 'CR', 5, 'INR', $jv_unique_id = NULL, $v_no, $ssb_account_id_from = NULL, NULL, NULL, $ssb_account_tran_id_from = NULL, $cheque_type = NULL, $cheque_id = NULL, $cheque_no = NULL, $transction_no = NULL, 1, Auth::user()->id, $loanDetails->company_id);
                    }
                    $createdGstTransaction = CommanController::gstTransaction($dayBookId = $dayBookRef, $getGstSettingno->gst_no, (!isset($loanDetails['loanMember']->gst_no)) ? NULL : $loanDetails['loanMember']->gst_no, $request['ecs_amount'], $getEcsSetting->gst_percentage, ($IntraStateFile == false ? $gstAmountEcs : 0), ($IntraStateFile == true ? $gstAmountEcs : 0), ($IntraStateFile == true ? $gstAmountEcs : 0), ($IntraStateFile == true ? $gstAmountEcs + $request['ecs_amount'] + $gstAmountEcs : $request['ecs_amount'] + $gstAmountEcs), 434, $request['date'], 'EC434', $loanDetails['loanMember']->id, $loanDetails->branch_id, $loanDetails->company_id);
                }

                /************* Head Implement ****************/
            } elseif ($request['pay_file_charge'] == 1) {
                $glResult = Grouploans::find($loanId);
                $glData['deposite_amount'] = $loanDetails->amount;
                $glData['due_amount'] = $loanDetails->amount;
                $glData['file_charge_type'] = '0';
                $glData['transfer_amount'] = $loan_amount;
                $glData['approve_date'] = date("Y-m-d", strtotime(convertDate($request['date'])));
                if ($loanDetails->emi_option == 1) {
                    $glData['closing_date'] = date('Y-m-d', strtotime("+" . $loanDetails->emi_period . " months", strtotime($glData['approve_date'])));
                } elseif ($loanDetails->emi_option == 2) {
                    $days = $loanDetails->emi_period * 7;
                    $start_date = $glData['approve_date'];
                    $date = strtotime($start_date);
                    $date = strtotime("+" . $days . " day", $date);
                    $glData['closing_date'] = date('Y-m-d', $date);
                } elseif ($loanDetails->emi_option == 3) {
                    $days = $loanDetails->emi_period;
                    $start_date = $glData['approve_date'];
                    $date = strtotime($start_date);
                    $date = strtotime("+" . $days . " day", $date);
                    $glData['closing_date'] = date('Y-m-d', $date);
                }
                $glData['status'] = 4;
                $glResult->update($glData);
                $mgLoan = Memberloans::where('id', $loanDetails->member_loan_id)
                    ->first();
                $mLoanAmount = $mgLoan->deposite_amount + $loanDetails->amount;
                $mlResult = Memberloans::find($loanDetails->member_loan_id);
                $lData['deposite_amount'] = $mLoanAmount;
                $lData['due_amount'] = $mgLoan->due_amount + $loanDetails->amount;
                $lData['status'] = 4;
                $lData['approve_date'] = date("Y-m-d", strtotime(convertDate($request['date'])));
                $mlResult->update($lData);
                $fileamountArraySsb = array(
                    '1' => $request['file_charge']
                );

                if ($gstAmount > 0) {
                    if ($IntraState) {

                        $branchDayBook = CommanController::branchDayBookNew($dayBookRef, $loanDetails->branch_id, 5, 536, $loanId, $createDayBook, $loanDetails->associate_member_id, $loanDetails->member_id, $branch_id_to = NULL, $loanDetails->branch_id, $gstAmount, 'GROUP CGST INSURANCE CHARGE ' . $loanDetails->account_number . '', 'GROUP CGST INSURANCE CHARGE ' . $loanDetails->account_number . '', 'GROUP CGST INSURANCE CHARGE ' . $loanDetails->account_number . '', 'CR', 0, 'INR', NULL, NULL, NULL, NULL, $entry_date = NULL, $entry_time = NULL, 1, Auth::user()->id, $created_at = NULL, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL, $loanDetails->company_id);

                        $branchDayBook = CommanController::branchDayBookNew($dayBookRef, $loanDetails->branch_id, 5, 537, $loanId, $createDayBook, $loanDetails->associate_member_id, $loanDetails->member_id, $branch_id_to = NULL, $loanDetails->branch_id, $gstAmount, 'GROUP SGST INSURANCE CHARGE ' . $loanDetails->account_number . '', 'GROUP SGST INSURANCE CHARGE ' . $loanDetails->account_number . '', 'GROUP SGST INSURANCE CHARGE ' . $loanDetails->account_number . '', 'CR', 0, 'INR', NULL, NULL, NULL, NULL, $entry_date = NULL, $entry_time = NULL, 1, Auth::user()->id, $created_at = NULL, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL, $loanDetails->company_id);

                        $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef, $loanDetails->branch_id, $bank_id = NULL, $bank_ac_id = NULL, $cgstHead, 5, 536, $loanId, $createDayBook, $loanDetails->associate_member_id, $loanDetails->member_id, $branch_id_to = NULL, $loanDetails->branch_id, $gstAmount, '' . $loanDetails->account_number . ' GROUP CGST INSURANCE CHARGE', 'CR', 0, 'INR', $jv_unique_id = NULL, $v_no, $ssb_account_id_from = NULL, NULL, NULL, $ssb_account_tran_id_from = NULL, $cheque_type = NULL, $cheque_id = NULL, $cheque_no = NULL, $transction_no = NULL, 1, Auth::user()->id, $loanDetails->company_id);

                        $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef, $loanDetails->branch_id, $bank_id = NULL, $bank_ac_id = NULL, $sgstHead, 5, 537, $loanId, $createDayBook, $loanDetails->associate_member_id, $loanDetails->member_id, $branch_id_to = NULL, $loanDetails->branch_id, $gstAmount, '' . $loanDetails->account_number . ' GROUP SGST INSURANCE CHARGE', 'CR', 0, 'INR', $jv_unique_id = NULL, $v_no, $ssb_account_id_from = NULL, NULL, NULL, $ssb_account_tran_id_from = NULL, $cheque_type = NULL, $cheque_id = NULL, $cheque_no = NULL, $transction_no = NULL, 1, Auth::user()->id, $loanDetails->company_id);

                        $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef, $loanDetails->branch_id, $bank_id = NULL, $bank_ac_id = NULL, 28, 5, 536, $loanId, $createDayBook, $loanDetails->associate_member_id, $loanDetails->member_id, $branch_id_to = NULL, $loanDetails->branch_id, $gstAmount, '' . $loanDetails->account_number . ' GROUP CGST INSURANCE CHARGE', 'DR', 0, 'INR', $jv_unique_id = NULL, $v_no, $ssb_account_id_from = NULL, NULL, NULL, $ssb_account_tran_id_from = NULL, $cheque_type = NULL, $cheque_id = NULL, $cheque_no = NULL, $transction_no = NULL, 1, Auth::user()->id, $loanDetails->company_id);

                        $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef, $loanDetails->branch_id, $bank_id = NULL, $bank_ac_id = NULL, 28, 5, 537, $loanId, $createDayBook, $loanDetails->associate_member_id, $loanDetails->member_id, $branch_id_to = NULL, $loanDetails->branch_id, $gstAmount, '' . $loanDetails->account_number . 'GROUP SGST INSURANCE CHARGE', 'DR', 0, 'INR', $jv_unique_id = NULL, $v_no, $ssb_account_id_from = NULL, NULL, NULL, $ssb_account_tran_id_from = NULL, $cheque_type = NULL, $cheque_id = NULL, $cheque_no = NULL, $transction_no = NULL, 1, Auth::user()->id, $loanDetails->company_id);












                    } else {
                        $branchDayBook = CommanController::branchDayBookNew($dayBookRef, $loanDetails->branch_id, 5, 541, $loanId, $createDayBook, $loanDetails->associate_member_id, $loanDetails->member_id, $branch_id_to = NULL, $loanDetails->branch_id, $gstAmount, 'GROUP IGST File CHARGE ' . $loanDetails->account_number . '', 'GROUP IGST File CHARGE ' . $loanDetails->account_number . '', 'GROUP IGST File CHARGE ' . $loanDetails->account_number . '', 'CR', 0, 'INR', NULL, NULL, NULL, NULL, $entry_date = NULL, $entry_time = NULL, 1, Auth::user()->id, $created_at = NULL, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL, $loanDetails->company_id);

                        $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef, $loanDetails->branch_id, $bank_id = NULL, $bank_ac_id = NULL, $cgstHead, 5, 541, $loanId, $createDayBook, $loanDetails->associate_member_id, $loanDetails->member_id, $branch_id_to = NULL, $loanDetails->branch_id, $gstAmount, '' . $loanDetails->account_number . 'GROUP IGST File CHARGE', 'CR', 0, 'INR', $jv_unique_id = NULL, $v_no, $ssb_account_id_from = NULL, NULL, NULL, $ssb_account_tran_id_from = NULL, $cheque_type = NULL, $cheque_id = NULL, $cheque_no = NULL, $transction_no = NULL, 1, Auth::user()->id, $loanDetails->company_id);
                    }
                    $createdGstTransaction = CommanController::gstTransaction($dayBookId = $dayBookRef, $getGstSettingno->gst_no, (!isset($loanDetails['loanMember']->gst_no)) ? NULL : $loanDetails['loanMember']->gst_no, $insurance_amount, $getHeadSetting->gst_percentage, ($IntraState == false ? $gstAmount : 0), ($IntraState == true ? $gstAmount : 0), ($IntraState == true ? $gstAmount : 0), ($IntraState == true) ? $insurance_amount + $gstAmount + $gstAmount : $insurance_amount + $gstAmount, 294, $request['date'], 'IC294', $loanDetails['loanMember']->id, $loanDetails->branch_id, $loanDetails->company_id);
                }
                //File Charge Gst Charge Transaction
                if ($gstAmountFileChrage > 0) {
                    $mlResult = Grouploans::find($loanId);
                    if ($IntraStateFile) {

                        $branchDayBook = CommanController::branchDayBookNew($dayBookRef, $loanDetails->branch_id, 5, 539, $loanId, $createDayBook, $loanDetails->associate_member_id, $loanDetails->member_id, $branch_id_to = NULL, $loanDetails->branch_id, $gstAmountFileChrage, 'Group Loan File Charge CGST ' . $loanDetails->account_number . '', 'Group Loan File Charge CGST  ' . $loanDetails->account_number . '', 'Group Loan  File Charge CGST ' . $loanDetails->account_number . '', 'CR', 0, 'INR', NULL, NULL, NULL, NULL, $entry_date = NULL, $entry_time = NULL, 1, Auth::user()->id, $ssb_account_tran_id_to = NULL, $created_at = NULL, $ssb_account_tran_id_from = NULL, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL, $loanDetails->company_id);

                        $branchDayBook = CommanController::branchDayBookNew($dayBookRef, $loanDetails->branch_id, 5, 540, $loanId, $createDayBook, $loanDetails->associate_member_id, $loanDetails->member_id, $branch_id_to = NULL, $loanDetails->branch_id, $gstAmountFileChrage, 'File Charge SGST ' . $loanDetails->account_number . '', 'Group Loan File Charge SGST ' . $loanDetails->account_number . '', 'File Charge SGST' . $loanDetails->account_number . '', 'CR', 0, 'INR', NULL, NULL, NULL, NULL, $entry_date = NULL, $entry_time = NULL, 1, Auth::user()->id, $ssb_account_tran_id_to = NULL, $created_at = NULL, $ssb_account_tran_id_from = NULL, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL, $loanDetails->company_id);

                        $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef, $loanDetails->branch_id, $bank_id = NULL, $bank_ac_id = NULL, $cgstHead, 5, 539, $loanId, $createDayBook, $loanDetails->associate_member_id, $loanDetails->member_id, $branch_id_to = NULL, $loanDetails->branch_id, $gstAmountFileChrage, '' . $loanDetails->account_number . 'Group Loan  File Charge CGST ', 'CR', 0, 'INR', $jv_unique_id = NULL, $v_no, $ssb_account_id_from = NULL, NULL, NULL, $ssb_account_tran_id_from = NULL, $cheque_type = NULL, $cheque_id = NULL, $cheque_no = NULL, $transction_no = NULL, 1, Auth::user()->id, $loanDetails->company_id);

                        $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef, $loanDetails->branch_id, $bank_id = NULL, $bank_ac_id = NULL, $sgstHead, 5, 540, $loanId, $createDayBook, $loanDetails->associate_member_id, $loanDetails->member_id, $branch_id_to = NULL, $loanDetails->branch_id, $gstAmountFileChrage, '' . $loanDetails->account_number . 'Group Loan File Charge SGST ', 'CR', 0, 'INR', $jv_unique_id = NULL, $v_no, $ssb_account_id_from = NULL, NULL, NULL, $ssb_account_tran_id_from = NULL, $cheque_type = NULL, $cheque_id = NULL, $cheque_no = NULL, $transction_no = NULL, 1, Auth::user()->id, $loanDetails->company_id);

                        $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef, $loanDetails->branch_id, $bank_id = NULL, $bank_ac_id = NULL, 28, 5, 539, $loanId, $createDayBook, $loanDetails->associate_member_id, $loanDetails->member_id, $branch_id_to = NULL, $loanDetails->branch_id, $gstAmountFileChrage, '' . $loanDetails->account_number . 'Group Loan Loan File Charge Cgst  charge', 'DR', 0, 'INR', $jv_unique_id = NULL, $v_no, $ssb_account_id_from = NULL, NULL, NULL, $ssb_account_tran_id_from = NULL, $cheque_type = NULL, $cheque_id = NULL, $cheque_no = NULL, $transction_no = NULL, 1, Auth::user()->id, $loanDetails->company_id);

                        $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef, $loanDetails->branch_id, $bank_id = NULL, $bank_ac_id = NULL, 28, 5, 540, $loanId, $createDayBook, $loanDetails->associate_member_id, $loanDetails->member_id, $branch_id_to = NULL, $loanDetails->branch_id, $gstAmountFileChrage, '' . $loanDetails->account_number . 'Group Loan File Charge Sgst  charge', 'DR', 0, 'INR', $jv_unique_id = NULL, $v_no, $ssb_account_id_from = NULL, NULL, NULL, $ssb_account_tran_id_from = NULL, $cheque_type = NULL, $cheque_id = NULL, $cheque_no = NULL, $transction_no = NULL, 1, Auth::user()->id, $loanDetails->company_id);
                    } else {
                        $branchDayBook = CommanController::branchDayBookNew($dayBookRef, $loanDetails->branch_id, 5, 541, $loanId, $createDayBook, $loanDetails->associate_member_id, $loanDetails->member_id, $branch_id_to = NULL, $loanDetails->branch_id, $gstAmount, 'GROUP IGST File CHARGE ' . $loanDetails->account_number . '', 'GROUP IGST File CHARGE ' . $loanDetails->account_number . '', 'GROUP IGST File CHARGE ' . $loanDetails->account_number . '', 'CR', 0, 'INR', NULL, NULL, NULL, NULL, $entry_date = NULL, $entry_time = NULL, 1, Auth::user()->id, $created_at = NULL, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL, $loanDetails->company_id);

                        $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef, $loanDetails->branch_id, $bank_id = NULL, $bank_ac_id = NULL, $cgstHead, 5, 541, $loanId, $createDayBook, $loanDetails->associate_member_id, $loanDetails->member_id, $branch_id_to = NULL, $loanDetails->branch_id, $gstAmount, '' . $loanDetails->account_number . 'GROUP IGST File CHARGE', 'CR', 0, 'INR', $jv_unique_id = NULL, $v_no, $ssb_account_id_from = NULL, NULL, NULL, $ssb_account_tran_id_from = NULL, $cheque_type = NULL, $cheque_id = NULL, $cheque_no = NULL, $transction_no = NULL, 1, Auth::user()->id, $loanDetails->company_id);
                    }
                    $createdGstTransaction = CommanController::gstTransaction($dayBookId = $dayBookRef, $getGstSettingno->gst_no, (!isset($loanDetails['loanMember']->gst_no)) ? NULL : $loanDetails['loanMember']->gst_no, $request['file_charge'], $getHeadSettingFileCHrage->gst_percentage, ($IntraStateFile == false ? $gstAmountFileChrage : 0), ($IntraStateFile == true ? $gstAmountFileChrage / 2 : 0), ($IntraStateFile == true ? $gstAmountFileChrage / 2 : 0), ($IntraStateFile == true ? $gstAmountFileChrage + $request['file_charge'] + $gstAmountFileChrage : $request['file_charge'] + $gstAmountFileChrage), 90, $request['date'], 'FC90', $loanDetails['loanMember']->id, $loanDetails->branch_id, $loanDetails->company_id);
                    //DD( $createdGstTransaction);
                }
                if ($gstAmountEcs > 0) {
                    $mlResult = Grouploans::find($loanId);
                    if ($IntraStateFile) {

                        $branchDayBook = CommanController::branchDayBookNew($dayBookRef, $loanDetails->branch_id, 5, 548, $loanId, $createDayBook, $loanDetails->associate_member_id, $loanDetails->member_id, $branch_id_to = NULL, $loanDetails->branch_id, $gstAmountEcs, 'Group Loan ECS Charge CGST ' . $loanDetails->account_number . '', 'Group Loan ECS Charge CGST  ' . $loanDetails->account_number . '', 'Group Loan  ECS Charge CGST ' . $loanDetails->account_number . '', 'CR', 0, 'INR', NULL, NULL, NULL, NULL, $entry_date = NULL, $entry_time = NULL, 1, Auth::user()->id, $ssb_account_tran_id_to = NULL, $created_at = NULL, $ssb_account_tran_id_from = NULL, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL, $loanDetails->company_id);

                        $branchDayBook = CommanController::branchDayBookNew($dayBookRef, $loanDetails->branch_id, 5, 549, $loanId, $createDayBook, $loanDetails->associate_member_id, $loanDetails->member_id, $branch_id_to = NULL, $loanDetails->branch_id, $gstAmountEcs, 'Group Laon ECS Charge SGST ' . $loanDetails->account_number . '', 'Group Loan ECS Charge SGST ' . $loanDetails->account_number . '', 'ECS Charge SGST' . $loanDetails->account_number . '', 'CR', 0, 'INR', NULL, NULL, NULL, NULL, $entry_date = NULL, $entry_time = NULL, 1, Auth::user()->id, $ssb_account_tran_id_to = NULL, $created_at = NULL, $ssb_account_tran_id_from = NULL, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL, $loanDetails->company_id);

                        $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef, $loanDetails->branch_id, $bank_id = NULL, $bank_ac_id = NULL, $cgstHead, 5, 548, $loanId, $createDayBook, $loanDetails->associate_member_id, $loanDetails->member_id, $branch_id_to = NULL, $loanDetails->branch_id, $gstAmountEcs, '' . $loanDetails->account_number . 'Group Loan ECS Charge CGST ', 'CR', 0, 'INR', $jv_unique_id = NULL, $v_no, $ssb_account_id_from = NULL, NULL, NULL, $ssb_account_tran_id_from = NULL, $cheque_type = NULL, $cheque_id = NULL, $cheque_no = NULL, $transction_no = NULL, 1, Auth::user()->id, $loanDetails->company_id);

                        $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef, $loanDetails->branch_id, $bank_id = NULL, $bank_ac_id = NULL, $sgstHead, 5, 549, $loanId, $createDayBook, $loanDetails->associate_member_id, $loanDetails->member_id, $branch_id_to = NULL, $loanDetails->branch_id, $gstAmountEcs, '' . $loanDetails->account_number . 'Group Loan ECS Charge SGST ', 'CR', 0, 'INR', $jv_unique_id = NULL, $v_no, $ssb_account_id_from = NULL, NULL, NULL, $ssb_account_tran_id_from = NULL, $cheque_type = NULL, $cheque_id = NULL, $cheque_no = NULL, $transction_no = NULL, 1, Auth::user()->id, $loanDetails->company_id);

                        $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef, $loanDetails->branch_id, $bank_id = NULL, $bank_ac_id = NULL, 28, 5, 539, $loanId, $createDayBook, $loanDetails->associate_member_id, $loanDetails->member_id, $branch_id_to = NULL, $loanDetails->branch_id, $gstAmountEcs, '' . $loanDetails->account_number . 'Group Loan ECS Charge Cgst  charge', 'DR', 0, 'INR',  $jv_unique_id = NULL, $v_no, $ssb_account_id_from = NULL, NULL,NULL, $ssb_account_tran_id_from = NULL,$cheque_type = NULL, $cheque_id = NULL, $cheque_no = NULL,$transction_no = NULL,1, Auth::user()->id,$loanDetails->company_id );

                        $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef, $loanDetails->branch_id, $bank_id = NULL, $bank_ac_id = NULL, 28, 5, 540, $loanId, $createDayBook, $loanDetails->associate_member_id, $loanDetails->member_id, $branch_id_to = NULL, $loanDetails->branch_id, $gstAmountEcs, '' . $loanDetails->account_number . 'Group Loan File Charge Sgst  charge', 'DR', 0, 'INR',$jv_unique_id = NULL, $v_no, $ssb_account_id_from = NULL, NULL,NULL, $ssb_account_tran_id_from = NULL,$cheque_type = NULL, $cheque_id = NULL, $cheque_no = NULL,$transction_no = NULL,1, Auth::user()->id,$loanDetails->company_id );
                    } else {

                        $branchDayBook = CommanController::branchDayBookNew($dayBookRef, $loanDetails->branch_id, 5, 550, $loanId, $createDayBook, $loanDetails->associate_member_id, $loanDetails->member_id, $branch_id_to = NULL, $loanDetails->branch_id, $gstAmount, 'GROUP IGST ECS CHARGE ' . $loanDetails->account_number . '', 'GROUP IGST ECS CHARGE ' . $loanDetails->account_number . '', 'GROUP IGST ECS CHARGE ' . $loanDetails->account_number . '', 'CR', 5, 'INR', NULL, NULL, NULL, NULL, $entry_date = NULL, $entry_time = NULL, 1, Auth::user()->id, $created_at = NULL, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL, $loanDetails->company_id);

                        $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef, $loanDetails->branch_id, $bank_id = NULL, $bank_ac_id = NULL, $cgstHead, 5, 550, $loanId, $createDayBook, $loanDetails->associate_member_id, $loanDetails->member_id, $branch_id_to = NULL, $loanDetails->branch_id, $gstAmount, '' . $loanDetails->account_number . 'GROUP IGST ECS CHARGE', 'CR', 5, 'INR', $jv_unique_id = NULL, $v_no, $ssb_account_id_from = NULL, NULL, NULL, $ssb_account_tran_id_from = NULL, $cheque_type = NULL, $cheque_id = NULL, $cheque_no = NULL, $transction_no = NULL, 1, Auth::user()->id, $loanDetails->company_id);
                    }
                    $createdGstTransaction = CommanController::gstTransaction($dayBookId = $dayBookRef, $getGstSettingno->gst_no, (!isset($loanDetails['loanMember']->gst_no)) ? NULL : $loanDetails['loanMember']->gst_no, $request['ecs_amount'], $getEcsSetting->gst_percentage, ($IntraStateFile == false ? $gstAmountEcs : 0), ($IntraStateFile == true ? $gstAmountEcs : 0), ($IntraStateFile == true ? $gstAmountEcs : 0), ($IntraStateFile == true ? $gstAmountEcs + $request['ecs_amount'] + $gstAmountEcs : $request['ecs_amount'] + $gstAmountEcs), 434, $request['date'], 'EC434', $loanDetails['loanMember']->id, $loanDetails->branch_id, $loanDetails->company_id);
                }
                /************* Head Implement ****************/
                $chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
                $v_no = "";
                for ($i = 0; $i < 10; $i++) {
                    $v_no .= $chars[mt_rand(0, strlen($chars) - 1)];
                }
                //$loan_amount=$loanDetails->amount;
                $loan_amount = $request['file_charge'];
                // $dayBookRef = CommanController::createBranchDayBookReference($loan_amount - $insurance_amount - $request['file_charge'] );
                $branchDayBook = CommanController::branchDayBookNew($dayBookRef, $loanDetails->branch_id, 5, 58, $loanId, $createDayBook, $loanDetails->associate_member_id, $loanDetails->member_id, $branch_id_to = NULL, $loanDetails->branch_id, $loan_amount, '' . $loanDetails->account_number . ' Group loan File charge', 'To ' . $loanDetails->account_number . ' A/C Dr ' . $loan_amount . '', 'To Bank A/C Cr ' . $loanDetails->account_number . '', 'CR', 0, 'INR', NULL, NULL, NULL, NULL, $entry_date = NULL, $entry_time = NULL, 1, Auth::user()->id, $created_at = NULL, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL, $loanDetails->company_id);

                if ($insurance_amount > 0) {
                    $branchDayBook = CommanController::branchDayBookNew($dayBookRef, $loanDetails->branch_id, 5, 526, $loanId, $createDayBook, $loanDetails->associate_member_id, $loanDetails->member_id, $branch_id_to = NULL, $loanDetails->branch_id, $insurance_amount, '' . $loanDetails->account_number . 'Group Loan insurance charge', 'To ' . $loanDetails->account_number . ' A/C Dr ' . $loan_amount . '', 'To Bank A/C Cr ' . $loanDetails->account_number . '', 'CR', 0, 'INR', NULL, NULL, NULL, NULL, $entry_date = NULL, $entry_time = NULL, 1, Auth::user()->id, date("Y-m-d", strtotime(convertDate($request['date']))), $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL, $loanDetails->company_id);

                    $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef, $loanDetails->branch_id, $bank_id = NULL, $bank_ac_id = NULL, 294, 5, 525, $loanId, $createDayBook, $loanDetails->associate_member_id, $loanDetails->member_id, $branch_id_to = NULL, $loanDetails->branch_id, $insurance_amount, '' . $loanDetails->account_number . 'Group Loan insurance charge', 'CR', 0, 'INR', $jv_unique_id = NULL, $v_no, $ssb_account_id_from = NULL, NULL, NULL, $ssb_account_tran_id_from = NULL, $cheque_type = NULL, $cheque_id = NULL, $cheque_no = NULL, $transction_no = NULL, 1, Auth::user()->id, $loanDetails->company_id);

                    $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef, $loanDetails->branch_id, $bank_id = NULL, $bank_ac_id = NULL, 28, 5, 525, $loanId, $createDayBook, $loanDetails->associate_member_id, $loanDetails->member_id, $branch_id_to = NULL, $loanDetails->branch_id, $insurance_amount, '' . $loanDetails->account_number . 'Group Loan insurance charge', 'DR', 0, 'INR', $jv_unique_id = NULL, $v_no, $ssb_account_id_from = NULL, NULL, NULL, $ssb_account_tran_id_from = NULL, $cheque_type = NULL, $cheque_id = NULL, $cheque_no = NULL, $transction_no = NULL, 1, Auth::user()->id, $loanDetails->company_id);
                }

                // dd('gfh');
                if ($request['ecs_amount'] > 0) {
                    $branchDayBook = CommanController::branchDayBookNew($dayBookRef, $loanDetails->branch_id, 5, 526, $loanId, $createDayBook, $loanDetails->associate_member_id, $loanDetails->member_id, $branch_id_to = NULL, $loanDetails->branch_id, $request['ecs_amount'], '' . $loanDetails->account_number . 'Group Loan ECS charge', 'To ' . $loanDetails->account_number . ' A/C Dr ' . $loan_amount . '', 'To Bank A/C Cr ' . $loanDetails->account_number . '', 'CR', 0, 'INR', NULL, NULL, NULL, NULL, $entry_date = NULL, $entry_time = NULL, 1, Auth::user()->id, date("Y-m-d", strtotime(convertDate($request['date']))), $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL, $loanDetails->company_id);

                    $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef, $loanDetails->branch_id, $bank_id = NULL, $bank_ac_id = NULL, 434, 5, 525, $loanId, $createDayBook, $loanDetails->associate_member_id, $loanDetails->member_id, $branch_id_to = NULL, $loanDetails->branch_id, $request['ecs_amount'], '' . $loanDetails->account_number . 'Group Loan ECS charge', 'CR', 0, 'INR', $jv_unique_id = NULL, $v_no, $ssb_account_id_from = NULL, NULL, NULL, $ssb_account_tran_id_from = NULL, $cheque_type = NULL, $cheque_id = NULL, $cheque_no = NULL, $transction_no = NULL, 1, Auth::user()->id, $loanDetails->company_id);

                    $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef, $loanDetails->branch_id, $bank_id = NULL, $bank_ac_id = NULL, 28, 5, 525, $loanId, $createDayBook, $loanDetails->associate_member_id, $loanDetails->member_id, $branch_id_to = NULL, $loanDetails->branch_id, $request['ecs_amount'], '' . $loanDetails->account_number . 'Group Loan ECS charge', 'DR', 0, 'INR', $jv_unique_id = NULL, $v_no, $ssb_account_id_from = NULL, NULL, NULL, $ssb_account_tran_id_from = NULL, $cheque_type = NULL, $cheque_id = NULL, $cheque_no = NULL, $transction_no = NULL, 1, Auth::user()->id, $loanDetails->company_id);
                }
                $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef, $loanDetails->branch_id, $bank_id = NULL, $bank_ac_id = NULL, 90, 5, 58, $loanId, $createDayBook, $loanDetails->associate_member_id, $loanDetails->member_id, $branch_id_to = NULL, $loanDetails->branch_id, $loan_amount, '' . $loanDetails->account_number . 'Group Loan file charge', 'CR', 0, 'INR', $jv_unique_id = NULL, $v_no, $ssb_account_id_from = NULL, NULL, NULL, $ssb_account_tran_id_from = NULL, $cheque_type = NULL, $cheque_id = NULL, $cheque_no = NULL, $transction_no = NULL, 1, Auth::user()->id, $loanDetails->company_id);
                $allHeadTransaction = CommanController::createAllHeadTransaction($dayBookRef, $loanDetails->branch_id, $bank_id = NULL, $bank_ac_id = NULL, 28, 5, 58, $loanId, $createDayBook, $loanDetails->associate_member_id, $loanDetails->member_id, $branch_id_to = NULL, $loanDetails->branch_id, $loan_amount, '' . $loanDetails->account_number . 'Group Loan file charge', 'DR', 0, 'INR', $jv_unique_id = NULL, $v_no, $ssb_account_id_from = NULL, NULL, NULL, $ssb_account_tran_id_from = NULL, $cheque_type = NULL, $cheque_id = NULL, $cheque_no = NULL, $transction_no = NULL, 1, Auth::user()->id, $loanDetails->company_id);

                /************* Head Implement ****************/
            }
            // event(new UserActivity($glResult,'Transfer Loan Amount',$request));
            if ($request->payment_mode == 1 || $request->payment_mode == 0) {
                // $text = 'Dear Member, Loan of Rs.' . $loanDetails->amount . ' is transferred to your ' . $transferMode . '  on ' . date('d/m/Y', strtotime(convertDate($request['date']))) . ' Loan A/C No ' . $loanDetails->account_number . ' EMI Rs. ' . $loanDetails->emi_amount . ' ' . $Mode . ' Samraddh Bestwin Microfinance';
                // ;
                // $temaplteId = 1207166308925961267;
                // $contactNumber = array();
                // $memberDetail = Member::find($loanDetails->customer_id);
                // $contactNumber[] = $memberDetail->mobile_no;
                // $sendToMember = new Sms();
                // $sendToMember->sendSms( $contactNumber, $text, $temaplteId);
            }
            $transferDate = date("Y-m-d", strtotime(convertDate($request['date'])));
            $currentDate = date("Y-m-d", strtotime(convertDate($globaldate)));
            if ($transferDate != $currentDate) {
                DB::select('call calculate_loan_interest(?,?,?)', [$currentDate, $loanDetails->account_number, 1]);
            }
            $mlResult = Grouploans::find($loanId);
            if ($mlResult['emi_option'] == 3) {
                $date = DateTime::createFromFormat('d/m/Y', $request['date']);
                $date->modify('+1 day');
                $emiDueDate = $date->format('Y-m-d');
            } elseif ($mlResult['emi_option'] == 2) {
                $date = DateTime::createFromFormat('d/m/Y', $request['date']);
                $date->modify('+7 days');
                $emiDueDate = $date->format('Y-m-d');
            } elseif ($mlResult['emi_option'] == 1) {
                $date = DateTime::createFromFormat('d/m/Y', $request['date']);
                $currentDay = $date->format('d');
                $date->modify('+1 month');

                // If the original day exceeds the number of days in the new month,
                // set the day to the last day of the month
                if ($date->format('d') != $currentDay) {
                    $date->modify('last day of last month');
                }

                $emiDueDate = $date->format('Y-m-d');
            }

            // dd($request['date']);
            $lData['emi_due_date'] = $emiDueDate;
            // dd($lData);
            $mlResult->update($lData);


            $t = getLoanData($loanDetails->loan_type)->loan_category;

                $logdata = [
                    'loanId' => $loanId,
                    'loan_type' => $loanDetails->loan_type,
                    'loan_category' => $t,
                    'loan_name' => $loanDetails['loan']->name,
                    'title' => 'Loan Transfer',
                    'status' => 4,
                    'description' => 'A loan amount of ' . $request['hiddenTransferAmount'] . ' has been transferred to the customer through ' .
                    (($request['payment_mode'] == 0) ? 'SSB' : (($request['payment_mode'] == 1) ? 'Bank Transfer' : 'Cash')) . ', incorporating a ' .
                    (($loanDetails['pay_file_charge'] == 1) ? 'cash' : (($loanDetails['pay_file_charge'] == 0) ? 'loan' : '')) . ' file charge of ' .
                    ($request['file_charge'] ?? 0) . ' and an insurance charge of ' . ($request['insurance_amount'] ?? 0) . ' is applicable.',
                    'status_changed_date' => date("Y-m-d", strtotime(convertDate($request['date']))),
                    'created_by' => Auth::user()->id,
                    'user_name' => Auth::user()->username,
                    'created_by_name' => 'Admin',
                ];


            \App\Models\LoanLog::create($logdata);
            DB::commit();
        } catch (\Exception $ex) {

            DB::rollback();
            return back()->with('alert', $ex->getMessage().$ex->getLine());
        }
        return redirect()
            ->route('admin.grouploan.request')
            ->with('success', 'Loan amount has been successfully transferred!');
        /*}else{
            return back()->with('alert', 'SSB Account does not exists!');
        }*/
    }


    /**
     * Update Loan requests status view.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public static function checkCreateBranchCashDR($branch_id, $date, $amount, $type)
    {
        $entryDate = date("Y-m-d", strtotime(convertDate($date)));
        $entryTime = date("H:i:s");
        $currentDateRecord = \App\Models\BranchCash::where('branch_id', $branch_id)->whereDate('entry_date', $entryDate)->first();
        if ($currentDateRecord) {
            $Result = \App\Models\BranchCash::find($currentDateRecord->id);
            $data['balance'] = $currentDateRecord->balance - $amount;
            /*if($type == 0){
                $data['balance']=$currentDateRecord->balance-$amount;
            }elseif($type == 1){
                $data['loan_balance']=$currentDateRecord->loan_balance-$amount;
            }*/
            $data['updated_at'] = $date;
            $Result->update($data);
            $insertid = $currentDateRecord->id;
        } else {
            $oldDateRecord = \App\Models\BranchCash::where('branch_id', $branch_id)->whereDate('entry_date', '<', $entryDate)->orderby('entry_date', 'DESC')
                ->first();
            if ($oldDateRecord) {
                $Result1 = \App\Models\BranchCash::find($oldDateRecord->id);
                $data1['closing_balance'] = $oldDateRecord->balance;
                //$data1['loan_closing_balance']=$oldDateRecord->loan_balance;
                $Result1->update($data1);
                $insertid1 = $oldDateRecord->id;
                $data['balance'] = $oldDateRecord->balance - $amount;
                /*if($type == 0){
                    $data['balance']=$oldDateRecord->balance-$amount;
                }else{
                    $data['balance']=$oldDateRecord->balance;
                }
                if($type == 1){
                    $data['loan_balance']=$oldDateRecord->loan_balance-$amount;
                }
                else{
                    $data['loan_balance']=$oldDateRecord->loan_balance;
                }*/
                $data['opening_balance'] = $oldDateRecord->balance;
                $data['closing_balance'] = 0;
                //$data['loan_opening_balance']=$oldDateRecord->loan_balance;
                //$data['loan_closing_balance']=0;
                $data['branch_id'] = $branch_id;
                $data['entry_date'] = $entryDate;
                $data['entry_time'] = $entryTime;
                $data['type'] = $type;
                $data['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($date)));
                $transcation = \App\Models\BranchCash::create($data);
                $insertid = $transcation->id;
            } else {
                $data['balance'] = 0 - $amount;
                /*if($type == 0){
                    $data['balance']=0-$amount;
                }
                else                {
                    $data['balance']=0;
                }
                if($type == 1){
                    $data['loan_balance']=0-$amount;
                }
                else{
                   $data['loan_balance']=0;
                }*/
                $data['opening_balance'] = 0;
                $data['closing_balance'] = 0;
                //$data['loan_opening_balance']=0;
                //$data['loan_closing_balance']=0;
                $data['branch_id'] = $branch_id;
                $data['entry_date'] = $entryDate;
                $data['entry_time'] = $entryTime;
                $data['type'] = $type;
                $data['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($date)));
                $transcation = \App\Models\BranchCash::create($data);
                $insertid = $transcation->id;
            }
        }
        return true;
    }
    public static function checkCreateBranchClosingDR($branch_id, $date, $amount, $type)
    {
        $entryDate = date("Y-m-d", strtotime(convertDate($date)));
        $entryTime = date("H:i:s");
        $currentDateRecord = \App\Models\BranchClosing::where('branch_id', $branch_id)->whereDate('entry_date', $entryDate)->first();
        if ($currentDateRecord) {
            $Result = \App\Models\BranchClosing::find($currentDateRecord->id);
            $data['balance'] = $currentDateRecord->balance - $amount;
            $data['updated_at'] = $date;
            $Result->update($data);
            $insertid = $currentDateRecord->id;
        } else {
            $oldDateRecord = \App\Models\BranchClosing::where('branch_id', $branch_id)->whereDate('entry_date', '<', $entryDate)->orderby('entry_date', 'DESC')
                ->first();
            if ($oldDateRecord) {
                $Result1 = \App\Models\BranchClosing::find($oldDateRecord->id);
                $data1['closing_balance'] = $oldDateRecord->balance;
                //$data1['loan_closing_balance']=$oldDateRecord->loan_balance;
                $Result1->update($data1);
                $insertid1 = $oldDateRecord->id;
                $data['balance'] = $oldDateRecord->balance - $amount;
                /*if($type == 0){
                    $data['balance']=$oldDateRecord->balance-$amount;
                }else{
                    $data['balance']=$oldDateRecord->balance;
                }
                if($type == 1){
                    $data['loan_balance']=$oldDateRecord->loan_balance-$amount;
                }
                else{
                    $data['loan_balance']=$oldDateRecord->loan_balance;
                }*/
                $data['opening_balance'] = $oldDateRecord->balance;
                $data['closing_balance'] = 0;
                //$data['loan_opening_balance']=$oldDateRecord->loan_balance;
                //$data['loan_closing_balance']=0;
                $data['branch_id'] = $branch_id;
                $data['entry_date'] = $entryDate;
                $data['entry_time'] = $entryTime;
                $data['type'] = $type;
                $data['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($date)));
                $transcation = \App\Models\BranchClosing::create($data);
                $insertid = $transcation->id;
            } else {
                $data['balance'] = 0 - $amount;
                /*if($type == 0){
                    $data['balance']=0-$amount;
                }
                else                {
                    $data['balance']=0;
                }
                if($type == 1){
                    $data['loan_balance']=0-$amount;
                }
                else{
                   $data['loan_balance']=0;
                }*/
                $data['opening_balance'] = 0;
                $data['closing_balance'] = 0;
                //$data['loan_opening_balance']=0;
                //$data['loan_closing_balance']=0;
                $data['branch_id'] = $branch_id;
                $data['entry_date'] = $entryDate;
                $data['entry_time'] = $entryTime;
                $data['type'] = $type;
                $data['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($date)));
                $transcation = \App\Models\BranchClosing::create($data);
                $insertid = $transcation->id;
            }
        }
        return true;
    }
    /**
     * Loan recovery listing.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    /**
     * Display loan details.
     *
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function View($id, $type)
    {
        $data['title'] = "Loan Details";
        $data['loanDetails'] = Memberloans::with(['loan', 'member.memberCompany', 'LoanApplicants', 'LoanCoApplicants', 'LoanGuarantor', 'Loanotherdocs', 'GroupLoanMembers', 'loanInvestmentPlans', 'loanMemberCompany'])->findOrFail($id);
        $data['id'] = $id;
        // pd($data['loanDetails']->toArray());
        return view('templates.admin.loan.view', $data);
    }
    /**
     * Deposite loan EMI.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return JSON
     */
    //Temporary
    public function depositeLoanEmi(Request $request)
    {

        DB::beginTransaction();
        try {
            $entryTime = date("H:i:s");
            $penalty = $request['penalty_amount'] > 0 ? $request['penalty_amount'] : 0;
            $application_date = $request['application_date'];
            $createDayBook = $DayBookref = CommanController::createBranchDayBookReference($penalty);
            $globaldate = date('Y-m-d', strtotime(convertDate($request['application_date'])));

            Session::put('created_at', date("Y-m-d " . $entryTime . "", strtotime(convertDate($request['application_date']))));
            if ($request['loan_emi_payment_mode'] == 0) {
                $ssbAccountDetails = SavingAccount::with('ssbMember')->whereId($request['ssb_id'])->first();
                $checkSSBBalanceDeposit = \App\Models\SavingAccountTransactionView::where('saving_account_id', $request['ssb_id'])->whereDate('opening_date', '<=', $globaldate)->sum('deposit');
                $checkSSBBalanceWithdrawal = \App\Models\SavingAccountTransactionView::where('saving_account_id', $request['ssb_id'])->whereDate('opening_date', '<=', $globaldate)->sum('withdrawal');
                $ssbBalanceAmount = $checkSSBBalanceDeposit - $checkSSBBalanceWithdrawal;
                if ($ssbBalanceAmount < $request['deposite_amount']) {
                    return back()->with('error', 'Insufficient balance in ssb account or Inactive !');
                }
            }
            $deposit = $request['deposite_amount'];
            $loanId = $request['loan_id'];
            $branchId = $request['branch'];
            $mLoan = Memberloans::with(['loanMember', 'loan'])->where('id', $request['loan_id'])->first();
            $companyId = $mLoan->company_id;
            $stateid = getBranchState($mLoan['loanBranch']->name);
            $getHeadSetting = \App\Models\HeadSetting::where('head_id', 33)->first();
            $getGstSetting = \App\Models\GstSetting::where('state_id', $mLoan['loanBranch']->state_id)->where('applicable_date', '<=', $globaldate)->exists();
            $getGstSettingno = \App\Models\GstSetting::select('id', 'gst_no', 'state_id', 'applicable_date')->where('state_id', $mLoan['loanBranch']->state_id)->where('applicable_date', '<=', $globaldate)->first();
            $gstAmount = 0;
            if ($penalty > 0 && $getGstSetting) {
                if ($mLoan['loanBranch']->state_id == $getGstSettingno->state_id) {
                    $gstAmount = (($penalty * $getHeadSetting->gst_percentage) / 100) / 2;
                    $cgstHead = 171;
                    $sgstHead = 172;
                    $IntraState = true;
                } else {
                    $gstAmount = ($penalty * $getHeadSetting->gst_percentage) / 100;
                    $cgstHead = 170;
                    $IntraState = false;
                }
                $penalty = $penalty;
            } else {
                $penalty = 0;
            }
            $gstAmount = ceil($gstAmount);
            $LoanCreatedDate = date('Y-m-d', strtotime($mLoan->approve_date));
            $LoanCreatedYear = date('Y', strtotime($mLoan->approve_date));
            $LoanCreatedMonth = date('m', strtotime($mLoan->approve_date));
            $LoanCreateDate = date('d', strtotime($mLoan->approve_date));
            $currentDate = date('Y-m-d');
            $CurrentDate = date('d');
            $CurrentDateYear = date('Y');
            $CurrentDateMonth = date('m');
            $applicationDate = date('Y-m-d', strtotime(convertDate($request['application_date'])));
            $applicationCurrentDate = date('d', strtotime(convertDate($request['application_date'])));
            $applicationCurrentDateYear = date('Y', strtotime(convertDate($request['application_date'])));
            $applicationCurrentDateMonth = date('m', strtotime(convertDate($request['application_date'])));
            if ($mLoan->emi_option == 1) { //Month
                $daysDiff = (($CurrentDateYear - $LoanCreatedYear) * 12) + ($CurrentDateMonth - $LoanCreatedMonth);
                $daysDiff2 = today()->diffInDays($LoanCreatedDate);
                $nextEmiDates = $this->nextEmiDates($daysDiff, $LoanCreatedDate);
                $nextEmiDates2 = $this->nextEmiDatesDays($daysDiff2, $LoanCreatedDate);
                $nextEmiDates3 = $this->nextEmiDates($mLoan->emi_period, $LoanCreatedDate);
            }
            if ($mLoan->emi_option == 2) { //Week
                $daysDiff = today()->diffInDays($LoanCreatedDate);
                $daysDiff = $daysDiff / 7;
                $nextEmiDates2 = $this->nextEmiDatesDays($daysDiff, $LoanCreatedDate);
                $nextEmiDates = $this->nextEmiDatesWeek($daysDiff, $LoanCreatedDate);
            }
            if ($mLoan->emi_option == 3) {  //Days
                $daysDiff = today()->diffInDays($LoanCreatedDate);
                $nextEmiDates = $this->nextEmiDatesDays($daysDiff, $LoanCreatedDate);
            }
            // $accruedInterest = $this->accruedInterestCalcualte($mLoan->loan_type,$request['deposite_amount'],$mLoan->accrued_interest);
            $roi = 0; //$accruedInterest['accruedInterest'];
            $principal_amount = 0; //$accruedInterest['principal_amount'];
            $totalDayInterest = 0;
            $totalDailyInterest = 0;
            $newApplicationDate = explode('-', $applicationDate);
            $StartnewDAte = $applicationCurrentDateYear . '-' . $applicationCurrentDateMonth . '-01';
            $EndnewDAte = $applicationCurrentDateYear . '-' . $applicationCurrentDateMonth . '-31';
            $dailyoutstandingAmount = 0;
            $lastOutstanding = LoanEmisNew::where('loan_id', $mLoan->id)->where('loan_type', $mLoan->loan_type)->where('is_deleted', '0')->orderBy('id', 'desc')->first();
            $lastOutstandingDate = LoanEmisNew::where('loan_id', $mLoan->id)->where('loan_type', $mLoan->loan_type)->where('is_deleted', '0')->pluck('emi_date')->toArray();
            // $eniDateOutstandingArray = array_values($lastOutstandingDate);
            $newDate = array();
            //$checkDate = array_intersect($nextEmiDates,$lastOutstandingDate);
            $deposit = $request['deposite_amount'];
            if ($lastOutstanding != NULL && isset($lastOutstanding->out_standing_amount)) {
                $checkDateMonth = date('m', strtotime($lastOutstanding->emi_date));
                $checkDateYear = date('Y', strtotime($lastOutstanding->emi_date));
                $newstartDate = $checkDateYear . '-' . $checkDateMonth . '-01';
                $newEndDate = $checkDateYear . '-' . $checkDateMonth . '-31';
                $gapDayes = Carbon::parse($lastOutstanding->emi_date)->diff(Carbon::parse($applicationDate))->format('%a');
                $lastOutstanding2 = LoanEmisNew::where('loan_id', $mLoan->id)->where('loan_type', $mLoan->loan_type)->whereBetween('emi_date', [$newstartDate, $newEndDate])->where('is_deleted', '0')->sum('out_standing_amount');
                if ($mLoan->emi_option == 1 || $mLoan->emi_option == 2) {
                    if (!array_key_exists($applicationCurrentDate . '_' . $applicationCurrentDateMonth . '_' . $applicationCurrentDateYear, $nextEmiDates)) {
                        $outstandingAmount = ($lastOutstanding->out_standing_amount - $deposit);
                    } else {
                        $preDate = current($nextEmiDates);
                        $oldDate = $nextEmiDates[$applicationCurrentDate . '_' . $applicationCurrentDateMonth . '_' . $applicationCurrentDateYear];
                        if ($mLoan->emi_option == 1) {
                            $previousDate = Carbon::parse($oldDate)->subMonth(1);
                        }
                        if ($mLoan->emi_option == 2) {
                            $previousDate = Carbon::parse($oldDate)->subDays(7);
                        }
                        $pDate = date('Y-m-d', strtotime("+1 day", strtotime($previousDate)));
                        if ($preDate == $applicationDate) {
                            $aqmount = LoanEmisNew::where('loan_id', $mLoan->id)->whereBetween('emi_date', [$LoanCreatedDate, $applicationDate])->where('is_deleted', '0')->sum('roi_amount');
                        } else {
                            $aqmount = LoanEmisNew::where('loan_id', $mLoan->id)->whereBetween('emi_date', [$pDate, $applicationDate])->where('is_deleted', '0')->sum('roi_amount');
                        }
                        if ($aqmount > 0) {
                            $outstandingAmount = ($lastOutstanding->out_standing_amount - $deposit + $roi + $aqmount);
                        } else {
                            $outstandingAmount = ($lastOutstanding->out_standing_amount - $deposit + $roi);
                        }
                    }
                    $dailyoutstandingAmount = $outstandingAmount + $roi;
                }
                $deposit = $request['deposite_amount'];
            } else {
                $gapDayes = Carbon::parse($mLoan->approve_date)->diff(Carbon::parse($applicationDate))->format('%a');
                if ($mLoan->emi_option == 1 || $mLoan->emi_option == 2) //Monthly
                {
                    if (!array_key_exists($applicationCurrentDate . '_' . $applicationCurrentDateMonth . '_' . $applicationCurrentDateYear, $nextEmiDates)) {
                        $outstandingAmount = ($mLoan->amount - $deposit);
                    } else {
                        $outstandingAmount = ($mLoan->amount - $deposit + $roi);
                    }
                    $dailyoutstandingAmount = $outstandingAmount + $roi;
                } else {
                    // $principal_amount = $deposit- $roi;
                    $outstandingAmount = ($mLoan->amount - $principal_amount);
                }
                $deposit = $request['deposite_amount'];
                $dailyoutstandingAmount = $mLoan->amount + $roi;
            }
            $amountArraySsb = array(
                '1' => $request['deposite_amount']
            );
            if (isset($ssbAccountDetails['ssbMember'])) {
                $amount_deposit_by_name = $ssbAccountDetails['ssbMember']->first_name . ' ' . $ssbAccountDetails['ssbMember']->last_name;
            } else {
                $amount_deposit_by_name = NULL;
            }
            $dueAmount = $mLoan->due_amount - round($principal_amount);
            $mlResult = Memberloans::find($request['loan_id']);
            $lData['credit_amount'] = $mLoan->credit_amount + $principal_amount;
            $lData['due_amount'] = $dueAmount;
            $lData['accrued_interest'] = $mLoan->accrued_interest - $roi;
            if ($dueAmount == 0) {
                //$lData['status'] = 3;
                //$lData['clear_date'] = date("Y-m-d", strtotime(convertDate($request['application_date'])));
            }
            $lData['received_emi_amount'] = $mLoan->received_emi_amount + $request['deposite_amount'];
            $mlResult->update($lData);
            // add log
            $postData = $_POST;
            $enData = array(
                "post_data" => $postData,
                "lData" => $lData
            );
            $encodeDate = json_encode($enData);
            $arrs = array(
                "load_id" => $loanId,
                "type" => "7",
                "account_head_id" => 0,
                "user_id" => Auth::user()->id,
                "message" => "Loan Recovery   - Loan EMI payment",
                "data" => $encodeDate
            );
            // dd($request['loan_emi_payment_mode']);
            // DB::table('user_log')->insert($arrs);
            // end log
            $desType = 'Loan EMI deposit';
            if ($request['loan_emi_payment_mode'] == 0) {

                $cheque_dd_no = NULL;
                $online_payment_id = NULL;
                $online_payment_by = NULL;
                $bank_name = NULL;
                $cheque_date = NULL;
                $account_number = NULL;
                $paymentMode = 4;
                $ssbpaymentMode = 3;
                $paymentDate = date("Y-m-d " . $entryTime . "", strtotime(convertDate($request['created_date'])));
                // $record1 = SavingAccountTranscation::where('account_no', $ssbAccountDetails->account_no)
                //     ->whereDate('created_at', '<=', date("Y-m-d", strtotime(convertDate($request['application_date']))))->orderby('id', 'desc')
                //     ->first();
                $transDate = date("Y-m-d " . $entryTime . "", strtotime(convertDate($request['application_date'])));
                $record1 = SavingAccountTranscation::where('account_no', $ssbAccountDetails->account_no)
                    ->whereDate('created_at', '<=', $transDate)->whereCompanyId($companyId)->orderby('id', 'desc')
                    ->first();
                $ssb['saving_account_id'] = $ssbAccountDetails->id;
                $ssb['account_no'] = $ssbAccountDetails->account_no;
                $ssb['opening_balance'] = $record1->opening_balance - $request['deposite_amount'];
                $ssb['branch_id'] = $request['branch'];
                $ssb['type'] = 9;
                $ssb['deposit'] = 0;
                $ssb['withdrawal'] = $request['deposite_amount'];
                $ssb['description'] = 'Loan EMI Trf. to ' . $mLoan->account_number;
                $ssb['currency_code'] = 'INR';
                $ssb['payment_type'] = 'DR';
                $ssb['company_id'] = $companyId;
                $ssb['payment_mode'] = $ssbpaymentMode;
                $ssb['daybook_ref_id'] = $DayBookref;
                $ssb['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($request['application_date'])));
                $desType = 'Amount Received From ' . $ssbAccountDetails->account_no;
                $ssbAccountTran = SavingAccountTranscation::create($ssb);
                $ssb_transaction_id = $ssb_account_id_from = $ssbAccountTran->id;
                // update saving account current balance
                $ssbBalance = SavingAccount::find($ssbAccountDetails->id);
                $ssbBalance->balance = $request['ssb_account'] - $request['deposite_amount'];
                $ssbBalance->save();
                $record2 = SavingAccountTranscation::where('account_no', $ssbAccountDetails->account_no)
                    ->whereDate('created_at', '>', date("Y-m-d", strtotime(convertDate($request['application_date']))))->get();
                foreach ($record2 as $key => $value) {
                    $nsResult = SavingAccountTranscation::find($value->id);
                    $nsResult['opening_balance'] = $value->opening_balance - $request['deposite_amount'];
                    $nsResult['company_id'] = $companyId;
                    $nsResult['updated_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($request['application_date'])));
                    $nsResult->save();
                }
                $data['saving_account_transaction_id'] = $ssb_transaction_id;
                $data['loan_id'] = $request['loan_id'];
                $data['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($request['application_date'])));

                // $satRef = TransactionReferences::create($data);

                // $satRefId = $satRef->id;
                $satRefId = null;

                $updateSsbDayBook = $this->updateSsbDayBookAmount($request['deposite_amount'], $request['ssb_account_number'], date("Y-m-d " . $entryTime . "", strtotime(convertDate($request['application_date']))), $companyId);

                // $ssbCreateTran = CommanController::createTransaction

                $ssbCreateTran = null;

                $totalbalance = $request['ssb_account'] - $request['deposite_amount'];

                // $createDayBook = CommanController::createDayBook

            } elseif ($request['loan_emi_payment_mode'] == 1) {
                if ($request['bank_transfer_mode'] == 0 && $request['bank_transfer_mode'] != '') {
                    $cheque_dd_no = $request['customer_cheque'];
                    $paymentMode = 1;
                    $ssbpaymentMode = 5;
                    $online_payment_id = NULL;
                    $online_payment_by = NULL;
                    $satRefId = NULL;
                    $bank_name = $request['customer_bank_name'];
                    $receivedcheque = ReceivedCheque::find($cheque_dd_no);
                    $cheque_date = $receivedcheque->cheque_create_date;
                    $account_number = NULL;
                    $paymentDate = date("Y-m-d", strtotime(convertDate($request['application_date'])));
                } elseif ($request['bank_transfer_mode'] == 1) {
                    $cheque_dd_no = NULL;
                    $paymentMode = 3;
                    $ssbpaymentMode = 5;
                    $online_payment_id = $request['utr_transaction_number'];
                    $online_payment_by = NULL;
                    $satRefId = NULL;
                    $bank_name = NULL;
                    $cheque_date = NULL;
                    $account_number = NULL;
                    $paymentDate = date("Y-m-d", strtotime(convertDate($request['application_date'])));
                }
                $ssb_transaction_id = '';
                $ssb_account_id_from = '';
                $ssbCreateTran = NULL;
                // $createDayBook = CommanController::createDayBook($ssbCreateTran, $satRefId, 5, $request['loan_id'], 0, $mLoan->applicant_id, $dueAmount, $request['deposite_amount'], 0, 'Loan EMI deposit', $mLoan->account_number, $request['branch'], getBranchCode($request['branch'])->branch_code, $amountArraySsb, $paymentMode, $request['loan_associate_name'], $request['associate_member_id'], $mLoan->account_number, $cheque_dd_no, $bank_name, $branch_name = NULL, $paymentDate, $online_payment_id, $online_payment_by, NULL, 'CR', $companyId);
            } elseif ($request['loan_emi_payment_mode'] == 2) {
                $cheque_dd_no = NULL;
                $cheque_date = NULL;
                $account_number = NULL;
                $paymentMode = 0;
                $ssbpaymentMode = 0;
                $online_payment_id = NULL;
                $online_payment_by = NULL;
                $satRefId = NULL;
                $bank_name = NULL;
                $paymentDate = date("Y-m-d", strtotime(convertDate($request['application_date'])));
                $ssb_transaction_id = '';
                $ssb_account_id_from = '';
            } elseif ($request['loan_emi_payment_mode'] == 3) {
                $cheque_dd_no = $request['cheque_number'];
                $cheque_date = $request['cheque_date'];
                $bank_name = $request['bank_name'];
                $account_number = $request['account_number'];
                $paymentMode = 1;
                $ssbpaymentMode = 1;
                $online_payment_id = NULL;
                $online_payment_by = NULL;
                $satRefId = NULL;
                $paymentDate = date("Y-m-d", strtotime(convertDate($request['application_date'])));
                $ssb_transaction_id = '';
                $ssb_account_id_from = '';
            }
            $ssbCreateTran = NULL;

            // $ssbCreateTran = CommanController::createTransaction

            // No Entry in Day Book table as per current Updates Changes Done by Sourab

            // $createDayBook = CommanController::createDayBook
            $transactionPaymentMode = 0;
            if ($request['bank_transfer_mode'] == 0 && $request['bank_transfer_mode'] != '') {
                $checkData['type'] = 4;
                $checkData['branch_id'] = $request['branch'];
                // $checkData['loan_id']=$request['loan_id'];
                $checkData['day_book_id'] = $createDayBook;
                $checkData['cheque_id'] = $cheque_dd_no;
                $checkData['status'] = 1;
                $checkData['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($request['application_date'])));
                $ssbAccountTran = ReceivedChequePayment::create($checkData);
                $dataRC['status'] = 3;
                $receivedcheque = ReceivedCheque::find($cheque_dd_no);
                $receivedcheque->update($dataRC);
            }
            /************* Head Implement ****************/
            if ($request['loan_emi_payment_mode'] == 0) {
                $paymentMode = 4; //saving account transaction
                $transactionPaymentMode = 3;
                $chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
                $v_no = "";
                for ($i = 0; $i < 10; $i++) {
                    $v_no .= $chars[mt_rand(0, strlen($chars) - 1)];
                }
                $principalbranchDayBook = CommanController::branchDayBookNew(
                    $DayBookref,
                    $branchId,
                    5,
                    52,
                    $loanId,
                    $ssbAccountTran->id,
                    $request['associate_member_id'],
                    $mLoan->applicant_id,
                    $branch_id_to = NULL,
                    $branch_id_from = NULL,
                    $deposit,
                    'Amount Received from ' . $ssbAccountDetails->account_no,
                    'SSB A/C Dr ' . ($deposit) . '',
                    'To ' . $mLoan->account_number . ' A/C Cr ' . ($deposit) . '',
                    'CR',
                    3,
                    'INR',
                    $v_no,
                    $ssbAccountDetails->id,
                    $cheque_no = NULL,
                    $transction_no = NULL,
                    $entry_date = $request['application_date'],
                    $entry_time = date("H:i:s"),
                    1,
                    Auth::user()->id,
                    $request['application_date'],
                    $ssb_account_tran_id_to = NULL,
                    $ssb_account_id_from,
                    $jv_unique_id = NULL,
                    $cheque_type = NULL,
                    $cheque_id = NULL,
                    $companyId
                );
                $principalbranchDayBook = CommanController::branchDayBookNew(
                    $DayBookref,
                    $branchId,
                    4,
                    48,
                    $ssbAccountDetails->id,
                    $ssbAccountTran->id,
                    $request['associate_member_id'],
                    $mLoan->applicant_id,
                    $branch_id_to = NULL,
                    $branch_id_from = NULL,
                    $deposit,
                    'Loan Emi Transfer To' . $mLoan->account_number,
                    'SSB A/C Dr ' . ($deposit) . '',
                    'To ' . $mLoan->account_number . ' A/C Cr ' . ($deposit) . '',
                    'DR',
                    3,
                    'INR',
                    $v_no,
                    $ssbAccountDetails->id,
                    $cheque_no = NULL,
                    $transction_no = NULL,
                    $entry_date = $request['application_date'],
                    $entry_time = date("H:i:s"),
                    1,
                    Auth::user()->id,
                    $request['application_date'],
                    $ssb_account_tran_id_to = NULL,
                    $ssb_account_id_from,
                    $jv_unique_id = NULL,
                    $cheque_type = NULL,
                    $cheque_id = NULL,
                    $companyId
                );






            } elseif ($request['loan_emi_payment_mode'] == 2) {
                $paymentMode = 0;


                $loan_head_id = $mLoan['loan']->head_id;


                $principalbranchDayBook = CommanController::branchDayBookNew($DayBookref, $branchId, 5, 52, $loanId, $createDayBook, $request['associate_member_id'], $mLoan->applicant_id, $branch_id_to = NULL, $branch_id_from = NULL, $deposit, '' . $mLoan->account_number . ' EMI collection', 'Cash Dr ' . ($deposit) . '', 'To ' . $mLoan->account_number . ' A/C Cr ' . ($deposit) . '', 'CR', $paymentMode, 'INR', $v_no = NULL, $ssb_account_id_from = NULL, $cheque_no = NULL, $transction_no = NULL, $request['application_date'], date("H:i:s"), 1, Auth::user()->id, $request['application_date'], $ssb_account_tran_id_to = NULL, $ssb_account_id_from, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL, $companyId);


            } elseif ($request['loan_emi_payment_mode'] == 1) {
                $loan_head_id = $mLoan['loan']->head_id;
                if ($request['bank_transfer_mode'] == 0 && $request['bank_transfer_mode'] != '') {
                    $transactionPaymentMode = 1;
                    $payment_type = 1;
                    $amount_from_id = $request['associate_member_id'];
                    $amount_from_name = customGetMemberData($request['associate_member_id'])->first_name . ' ' . customGetMemberData($request['associate_member_id'])->last_name;
                    $cheque_type = 1;
                    $receivedcheque = ReceivedCheque::find($request['customer_cheque']);
                    $cheque_id = $request['customer_cheque'];
                    $cheque_no = $receivedcheque->cheque_no;
                    $cheque_date = $receivedcheque->cheque_create_date;
                    // $chequeDate = date("Y-m-d", strtotime($request['cheque-date']));
                    // $cheque_date = $chequeDate;
                    $cheque_bank_from = $request['customer_bank_name'];
                    $cheque_bank_ac_from = $request['customer_bank_account_number'];
                    $cheque_bank_ifsc_from = $request['customer_ifsc_code'];
                    $cheque_bank_branch_from = NULL;
                    $cheque_bank_to = $request['company_bank'];
                    $cheque_bank_ac_to = $request['company_bank_account_number'];
                    $v_no = NULL;
                    $v_date = NULL;
                    $ssb_account_id_from = NULL;
                    $transction_no = NULL;
                    $transction_bank_from = NULL;
                    $transction_bank_ac_from = NULL;
                    $transction_bank_ifsc_from = NULL;
                    $transction_bank_branch_from = NULL;
                    $transction_bank_to = $request['company_bank'];
                    $transction_bank_ac_to = $request['company_bank_account_number'];
                    $company_name = $request['cheque_company_bank'];
                    $ifsc = NULL;
                    $head_id = getSamraddhBankAccount($request['company_bank_account_number'])->account_head_id;
                    $bankId = getSamraddhBankAccount($request['company_bank_account_number'])->id;
                    $company_bankId = getSamraddhBankAccount($request['company_bank_account_number'])->bank_id;
                } elseif ($request['bank_transfer_mode'] == 1) {
                    $transactionPaymentMode = 2;
                    $cheque_id = NULL;
                    $payment_type = 2;
                    $amount_from_id = $request['associate_member_id'];
                    $amount_from_name = customGetMemberData($request['associate_member_id'])->first_name . ' ' . customGetMemberData($request['associate_member_id'])->last_name;
                    $cheque_type = NULL;
                    $cheque_no = NULL;
                    $cheque_date = NULL;
                    $cheque_bank_from = NULL;
                    $cheque_bank_ac_from = NULL;
                    $cheque_bank_ifsc_from = NULL;
                    $cheque_bank_branch_from = NULL;
                    $cheque_bank_to = NULL;
                    $cheque_bank_ac_to = NULL;
                    $transction_no = $request['utr_transaction_number'];
                    $v_no = NULL;
                    $v_date = NULL;
                    $ssb_account_id_from = NULL;
                    $transction_bank_from = $request['customer_bank_name'];
                    $transction_bank_ac_from = $request['customer_bank_account_number'];
                    $transction_bank_ifsc_from = $request['customer_ifsc_code'];
                    $transction_bank_branch_from = $request['customer_branch_name'];
                    $transction_bank_to = $request['company_bank'];
                    $transction_bank_ac_to = $request['bank_account_number'];
                    $company_name = getSamraddhBank($request['company_bank'])->bank_name;
                    $ifsc = getSamraddhBankAccount($transction_bank_ac_to)->ifsc_code;
                    $bankId = getSamraddhBankAccount($request['bank_account_number'])->id;
                    $head_id = getSamraddhBankAccount($request['bank_account_number'])->account_head_id;
                    $company_bankId = getSamraddhBank($request['company_bank'])->id;
                }


                // $allHeadTransaction = $this->createAllHeadTransaction

                // $allHeadTransaction = $this->createAllHeadTransaction

                // $allHeadTransaction = $this->createAllHeadTransaction

                // $allHeadTransaction = $this->createAllHeadTransaction

                // $principalbranchDayBook = CommanController::branchDayBookNew

                $principalbranchDayBook = CommanController::branchDayBookNew($DayBookref, $branchId, 5, 52, $loanId, $createDayBook, $request['associate_member_id'], $member_id = $mLoan->applicant_id, $branch_id_to = NULL, $branch_id_from = NULL, $deposit, '' . $mLoan->account_number . ' EMI collection', 'Bank A/C Dr ' . ($deposit) . '', 'To ' . $mLoan->account_number . 'Bank A/C Cr ' . ($deposit) . '', 'CR', $payment_type, 'INR', $v_no, $ssb_account_id_from = NULL, $cheque_no, $transction_no = NULL, $request['application_date'], date("H:i:s"), 1, Auth::user()->id, $request['application_date'], $ssb_account_tran_id_to = NULL, $ssb_account_id_from, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id, $companyId);

                $samraddhBankDaybook = CommanController::samraddhBankDaybookNew($DayBookref, $bank_id = $company_bankId, $account_id = $bankId, 5, 52, $loanId, $createDayBook, $request['associate_member_id'], $mLoan->applicant_id, $request['branch'], $deposit, $deposit, $deposit, 'EMI collection', 'Online A/C Cr. ' . ($deposit) . '', 'Online A/C Cr. ' . ($deposit) . '', 'CR', $payment_type, 'INR', $company_bankId, $company_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $company_name, $transction_bank_ac_to, NULL, $ifsc, $request['application_date'], $entry_date = NULL, $entry_time = NULL, 1, Auth::user()->id, $request['application_date'], $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $companyId);
            }
            /************* Head Implement ****************/
            /*---------- commission script  start  ---------*/
            $daybookId = $createDayBook;
            $total_amount = $request['deposite_amount'];
            $percentage = 2;
            $month = NULL;
            $type_id = $request['loan_id'];
            $type = 4;
            $associate_id = $request['associate_member_id'];
            $branch_id = $request['branch'];
            $commission_type = 0;
            $associateDetail = Member::where('id', $associate_id)->first();
            $carder = $associateDetail->current_carder_id;
            $associate_exist = 0;
            $percentInDecimal = $percentage / 100;
            $commission_amount = round($percentInDecimal * $total_amount, 4);
            $loan_associate_code = $request->loan_associate_code;
            $associateCommission['member_id'] = $associate_id;
            $associateCommission['branch_id'] = $branch_id;
            $associateCommission['type'] = $type;
            $associateCommission['type_id'] = $type_id;
            $associateCommission['day_book_id'] = $daybookId;
            $associateCommission['total_amount'] = $total_amount;
            $associateCommission['month'] = $month;
            $associateCommission['commission_amount'] = $commission_amount;
            $associateCommission['percentage'] = $percentage;
            $associateCommission['commission_type'] = $commission_type;
            $date = \App\Models\Daybook::where('id', $daybookId)->first();
            $associateCommission['created_at'] = $date->created_at ?? $request['created_at'];
            $associateCommission['pay_type'] = 4;
            $associateCommission['carder_id'] = $carder;
            $associateCommission['associate_exist'] = $associate_exist;

            /*---------- commission script  end  ---------*/

            $createLoanDayBook = CommanController::createLoanDayBook($DayBookref, $DayBookref, $mLoan->loan_type, 0, $loanId, $lId = NULL, $mLoan->account_number, $mLoan->applicant_id, $roi, $principal_amount, $dueAmount, $request['deposite_amount'], $desType, $request['branch'], getBranchCode($request['branch'])->branch_code, 'CR', 'INR', $paymentMode, $cheque_dd_no, $bank_name, $branch_name = NULL, $paymentDate, $online_payment_id, 1, 1, $cheque_date, $account_number, NULL, $request['loan_associate_name'], $request['associate_member_id'], $request['branch'], $totalDailyInterest, $totalDayInterest, $penalty, $companyId, $request['recovery_module']);


            $this->headTransaction($createLoanDayBook, $transactionPaymentMode, 1);

            $totalDepsoit = LoanDaybooks::where('account_number', $mLoan->account_number)->where('loan_sub_type', 0)->where('is_deleted', 0)->sum('deposit');
            // event(new UserActivity($createLoanDayBook,'Loan Emi',$request));
            $text = 'Dear Member,Received Rs.' . $request['deposite_amount'] . ' as EMI of Loan A/C ' . $mLoan->account_number . ' on ' . date('d/m/Y', strtotime(convertDate($request['application_date']))) . ' Total recovery Rs. ' . $totalDepsoit . ' Thank You. Samraddh Bestwin Microfinance';
            ;
            $temaplteId = 1207166308935249821;
            $contactNumber = array();
            $memberDetail = Member::find($mLoan->customer_id);
            $contactNumber[] = $memberDetail->mobile_no;
            $sendToMember = new Sms();
            $sendToMember->sendSms($contactNumber, $text, $temaplteId);
            DB::commit();
        } catch (\Exception $ex) {
            dd($ex->getLine(),$ex->getMessage(),$ex->getFile());
            DB::rollback();
            return back()->with('alert', $ex->getLine());
        }
        return back()
            ->with('success', 'Loan EMI Successfully submitted!');
    }



    public function depositeGroupLoanEmi(Request $request)
    {
        DB::beginTransaction();
        try {
            $entryTime = date("H:i:s");
            if ($request['penalty_amount'] > 0) {
                $penalty = $request['penalty_amount'];
            } else {
                $penalty = 0;
            }
            Session::put('created_at', date("Y-m-d " . $entryTime . "", strtotime(convertDate($request['application_date']))));
            $mLoan = Grouploans::with(['loanMember', 'loanBranch', 'loan'])->where('id', $request['loan_id'])->first();
            $companyId = $mLoan->company_id;
            $globaldate = date('Y-m-d', strtotime(convertDate($request['application_date'])));

            if ($request['loan_emi_payment_mode'] == 0) {
                $ssbAccountDetails = SavingAccount::with('ssbMember')->where('id', $request['ssb_id'])->where('company_id', $mLoan->company_id)->first();
                $checkSSBBalanceDeposit = \App\Models\SavingAccountTransactionView::where('saving_account_id', $request['ssb_id'])->whereDate('opening_date', '<=', $globaldate)->sum('deposit');
                $checkSSBBalanceWithdrawal = \App\Models\SavingAccountTransactionView::where('saving_account_id', $request['ssb_id'])->whereDate('opening_date', '<=', $globaldate)->sum('withdrawal');
                $ssbBalanceAmount = $checkSSBBalanceDeposit - $checkSSBBalanceWithdrawal;
                if ($ssbBalanceAmount < $request['deposite_amount']) {
                    return back()->with('error', 'Insufficient balance in ssb account or Inactive !');
                }
            }
            $loanId = $request['loan_id'];
            $branchId = $request['branch'];
            $stateid = getBranchState($mLoan['loanBranch']->name);
            $getHeadSetting = \App\Models\HeadSetting::where('head_id', 33)->first();
            $getGstSetting = \App\Models\GstSetting::where('state_id', $mLoan['loanBranch']->state_id)->where('applicable_date', '<=', $globaldate)->exists();
            $getGstSettingno = \App\Models\GstSetting::select('id', 'gst_no', 'state_id')->where('state_id', $mLoan['loanBranch']->state_id)->where('applicable_date', '<=', $globaldate)->first();
            $gstAmount = 0;
            if ($request['penalty_amount'] > 0 && $getGstSetting) {
                if ($mLoan['loanBranch']->state_id == $getGstSettingno->state_id) {
                    $gstAmount = ceil(($request['penalty_amount'] * $getHeadSetting->gst_percentage) / 100) / 2;
                    $cgstHead = 171;
                    $sgstHead = 172;
                    $IntraState = true;
                } else {
                    $gstAmount = ceil($request['penalty_amount'] * $getHeadSetting->gst_percentage) / 100;
                    $cgstHead = 170;
                    $IntraState = false;
                }
                $penalty = $request['penalty_amount'];
            } else {
                $penalty = 0;
            }
            $LoanCreatedDate = date('Y-m-d', strtotime($mLoan->approve_date));
            $LoanCreatedYear = date('Y', strtotime($mLoan->approve_date));
            $LoanCreatedMonth = date('m', strtotime($mLoan->approve_date));
            $LoanCreateDate = date('d', strtotime($mLoan->approve_date));
            $currentDate = date('Y-m-d');
            $CurrentDate = date('d');
            $CurrentDateYear = date('Y');
            $CurrentDateMonth = date('m');
            $applicationDate = date('Y-m-d', strtotime(convertDate($request['application_date'])));
            $applicationCurrentDate = date('d', strtotime(convertDate($request['application_date'])));
            $applicationCurrentDateYear = date('Y', strtotime(convertDate($request['application_date'])));
            $applicationCurrentDateMonth = date('m', strtotime(convertDate($request['application_date'])));
            if ($mLoan->emi_option == 1) { //Month
                $daysDiff = (($CurrentDateYear - $LoanCreatedYear) * 12) + ($CurrentDateMonth - $LoanCreatedMonth);
                $daysDiff2 = today()->diffInDays($LoanCreatedDate);
                $nextEmiDates = $this->nextEmiDates($daysDiff, $LoanCreatedDate);
                $nextEmiDates2 = $this->nextEmiDatesDays($daysDiff2, $LoanCreatedDate);
                $nextEmiDates3 = $this->nextEmiDates($mLoan->emi_period, $LoanCreatedDate);
            }
            if ($mLoan->emi_option == 2) { //Week
                $daysDiff = today()->diffInDays($LoanCreatedDate);
                $daysDiff = $daysDiff / 7;
                $nextEmiDates2 = $this->nextEmiDatesDays($daysDiff, $LoanCreatedDate);
                $nextEmiDates = $this->nextEmiDatesWeek($daysDiff, $LoanCreatedDate);
            }
            if ($mLoan->emi_option == 3) {  //Days
                $daysDiff = today()->diffInDays($LoanCreatedDate);
                $nextEmiDates = $this->nextEmiDatesDays($daysDiff, $LoanCreatedDate);
            }
            // $accruedInterest = $this->accruedInterestCalcualte($mLoan->loan_type,$request['deposite_amount'],$mLoan->accrued_interest);
            $roi = 0;
            $principal_amount = 0;
            // $currentEmiDate = $nextEmiDates[$CurrentDate.'_'.$CurrentDateMonth.'_'.$CurrentDateYear];
            $totalDayInterest = 0;
            $totalDailyInterest = 0;
            $newApplicationDate = explode('-', $applicationDate);
            $StartnewDAte = $applicationCurrentDateYear . '-' . $applicationCurrentDateMonth . '-01';
            $EndnewDAte = $applicationCurrentDateYear . '-' . $applicationCurrentDateMonth . '-31';
            $dailyoutstandingAmount = 0;
            $lastOutstanding = LoanEmisNew::where('loan_id', $mLoan->id)->where('loan_type', $mLoan->loan_type)->where('is_deleted', '0')->orderBy('id', 'desc')->first();
            $lastOutstandingDate = LoanEmisNew::where('loan_id', $mLoan->id)->where('loan_type', $mLoan->loan_type)->where('is_deleted', '0')->pluck('emi_date')->toArray();
            $newDate = array();
            $deposit = $request['deposite_amount'];

            $amountArraySsb = array(
                '1' => $request['deposite_amount']
            );
            $outstandingAmount = 0;
            $deposit = $request['deposite_amount'];
            if (isset($ssbAccountDetails['ssbMember'])) {
                $amount_deposit_by_name = $ssbAccountDetails['ssbMember']->first_name . ' ' . $ssbAccountDetails['ssbMember']->last_name;
            } else {
                $amount_deposit_by_name = NULL;
            }
            $dueAmount = $mLoan->due_amount - round($principal_amount);
            $glResult = Grouploans::find($request['loan_id']);
            $glData['credit_amount'] = $mLoan->credit_amount + $principal_amount;
            $glData['due_amount'] = $dueAmount;
            if ($dueAmount == 0) {
                // $glData['status'] = 3;
            }
            $glResult['received_emi_amount'] = $mLoan->received_emi_amount + $request['deposite_amount'];
            $lData['accrued_interest'] = $mLoan->accrued_interest - $roi;
            $glResult->update($glData);
            $gmLoan = Memberloans::with('loanMember')->where('id', $mLoan->member_loan_id)
                ->first();
            $gmDueAmount = $gmLoan->due_amount - $principal_amount;
            $mlResult = Memberloans::find($mLoan->member_loan_id);
            $lData['credit_amount'] = $mLoan->credit_amount + $principal_amount;
            $lData['due_amount'] = $gmDueAmount;
            $lData['accrued_interest'] = $mLoan->accrued_interest - $roi;
            if ($dueAmount == 0) {
                $lData['status'] = 3;
                $lData['clear_date'] = date("Y-m-d", strtotime(convertDate($request['application_date'])));
            }
            $mlResult->update($lData);
            // add log
            $postData = $_POST;
            $enData = array(
                "post_data" => $postData,
                "lData" => $lData
            );
            $encodeDate = json_encode($enData);
            $arrs = array(
                "load_id" => $loanId,
                "type" => "7",
                "account_head_id" => 0,
                "user_id" => Auth::user()->id,
                "message" => "Group Loan Recovery - Loan EMI payment",
                "data" => $encodeDate
            );
            // DB::table('user_log')->insert($arrs);
            // end log
            $roidayBookRef = CommanController::createBranchDayBookReference($deposit);

            $desType = 'Loan Emi Deposite';
            if ($request['loan_emi_payment_mode'] == 0) {
                $transactionPaymentMode = 3;
                $cheque_dd_no = NULL;
                $online_payment_id = NULL;
                $online_payment_by = NULL;
                $bank_name = NULL;
                $cheque_date = NULL;
                $account_number = NULL;
                $paymentMode = 4;
                $ssbpaymentMode = 3;
                $paymentDate = date("Y-m-d", strtotime(convertDate($request['application_date'])));
                ;
                // $record1 = SavingAccountTranscation::where('account_no', $ssbAccountDetails->account_no)
                //     ->whereDate('created_at', '<', date("Y-m-d", strtotime(convertDate($request['application_date']))))->orderby('id', 'desc')
                //     ->first();
                $transDate = date("Y-m-d " . $entryTime . "", strtotime(convertDate($request['application_date'])));
                $record1 = SavingAccountTranscation::where('account_no', $ssbAccountDetails->account_no)
                    ->whereDate('created_at', '<=', $transDate)->orderby('id', 'desc')
                    ->first();
                $ssb['saving_account_id'] = $ssbAccountDetails->id;
                $ssb['account_no'] = $ssbAccountDetails->account_no;
                $ssb['opening_balance'] = $record1->opening_balance - $request['deposite_amount'];
                $ssb['branch_id'] = $request['branch'];
                $ssb['type'] = 9;
                $ssb['deposit'] = 0;
                $ssb['withdrawal'] = $request['deposite_amount'];
                $ssb['description'] = 'Loan EMI Trf. to ' . $mLoan->account_number;
                $ssb['currency_code'] = 'INR';
                $ssb['payment_type'] = 'DR';
                $ssb['payment_mode'] = $ssbpaymentMode;
                $ssb['company_id'] = $companyId;
                $ssb['daybook_ref_id'] = $roidayBookRef;
                $ssb['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($request['application_date'])));
                $ssbAccountTran = SavingAccountTranscation::create($ssb);
                $ssb_transaction_id = $ssb_account_id_from = $ssbAccountTran->id;
                $desType = 'Amount Received From ' . $ssbAccountDetails->account_no;
                // update saving account current balance
                $ssbBalance = SavingAccount::find($ssbAccountDetails->id);
                $ssbBalance->balance = $request['ssb_account'] - $request['deposite_amount'];
                $ssbBalance->save();
                $record2 = SavingAccountTranscation::where('account_no', $ssbAccountDetails->account_no)
                    ->whereDate('created_at', '>', date("Y-m-d", strtotime(convertDate($request['application_date']))))->get();
                foreach ($record2 as $key => $value) {
                    $nsResult = SavingAccountTranscation::find($value->id);
                    $nsResult['opening_balance'] = $value->opening_balance - $request['deposite_amount'];
                    $nsResult['updated_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($request['application_date'])));
                    $sResult->update($nsResult);
                }
                $data['saving_account_transaction_id'] = $ssb_transaction_id;
                $data['loan_id'] = $request['loan_id'];
                $data['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($request['application_date'])));
                $satRefId = $roidayBookRef;
                // $satRefId = $satRef->id;
                $updateSsbDayBook = $this->updateSsbDayBookAmount($request['deposite_amount'], $request['ssb_account_number'], date("Y-m-d " . $entryTime . "", strtotime(convertDate($request['application_date']))), $companyId);

                // $ssbCreateTran = CommanController::createTransaction($satRefId, 1, $ssbAccountDetails->id, $ssbAccountDetails->member_id, $request['branch'], getBranchCode($request['branch'])->branch_code, $amountArraySsb, $paymentMode, $request['loan_associate_name'], $request['associate_member_id'], $ssbAccountDetails->account_no, $cheque_dd_no, $bank_name, $branch_name = NULL, $paymentDate, $online_payment_id, $online_payment_by, $ssbAccountDetails->id, 'DR');

                $totalbalance = $request['ssb_account'] - $request['deposite_amount'];

                // $createDayBook = CommanController::createDayBook($ssbCreateTran, $satRefId, 1, $ssbAccountDetails->member_investments_id, 0, $ssbAccountDetails->member_id, $totalbalance, 0, $request['deposite_amount'], 'Withdrawal from SSB', $request['account_number'], $request['branch'], getBranchCode($request['branch'])->branch_code, $amountArraySsb, $paymentMode, $request['loan_associate_name'], $request['associate_member_id'], $ssbAccountDetails->account_no, $cheque_dd_no, NULL, NULL, $paymentDate, $online_payment_by, $online_payment_by, $ssbAccountDetails->id, 'DR');
            } elseif ($request['loan_emi_payment_mode'] == 1) {
                if ($request['bank_transfer_mode'] == 0 && $request['bank_transfer_mode'] != '') {
                    $transactionPaymentMode = 1;
                    $cheque_dd_no = $request['customer_cheque'];
                    $paymentMode = 1;
                    $ssbpaymentMode = 5;
                    $online_payment_id = NULL;
                    $online_payment_by = NULL;
                    $satRefId = NULL;
                    $bank_name = NULL;
                    $cheque_date = NULL;
                    $account_number = NULL;
                    $paymentDate = date("Y-m-d " . $entryTime . "", strtotime(convertDate($request['application_date'])));
                } elseif ($request['bank_transfer_mode'] == 1) {
                    $cheque_dd_no = NULL;
                    $paymentMode = 3;
                    $ssbpaymentMode = 5;
                    $online_payment_id = $request['utr_transaction_number'];
                    $online_payment_by = NULL;
                    $satRefId = NULL;
                    $bank_name = NULL;
                    $cheque_date = NULL;
                    $account_number = NULL;
                    $transactionPaymentMode = 2;
                    $paymentDate = date("Y-m-d " . $entryTime . "", strtotime(convertDate($request['application_date'])));
                }
                $ssb_transaction_id = '';
                $ssb_account_id_from = '';

            } elseif ($request['loan_emi_payment_mode'] == 2) {
                $cheque_dd_no = NULL;
                $paymentMode = 0;
                $ssbpaymentMode = 0;
                $online_payment_id = NULL;
                $online_payment_by = NULL;
                $transactionPaymentMode = 0;
                $satRefId = NULL;
                $bank_name = NULL;
                $cheque_date = NULL;
                $account_number = NULL;
                $paymentDate = date("Y-m-d " . $entryTime . "", strtotime(convertDate($request['application_date'])));
                $ssb_transaction_id = '';
                $ssb_account_id_from = '';
            } elseif ($request['loan_emi_payment_mode'] == 3) {
                $cheque_dd_no = $request['cheque_number'];
                $cheque_date = date("Y-m-d", strtotime(convertDate($request['application_date'])));
                $bank_name = $request['bank_name'];
                $account_number = $request['account_number'];
                $paymentMode = 1;
                $ssbpaymentMode = 1;
                $online_payment_id = NULL;
                $online_payment_by = NULL;
                $satRefId = NULL;
                $paymentDate = date("Y-m-d " . $entryTime . "", strtotime(convertDate($request['application_date'])));
                $ssb_transaction_id = '';
                $ssb_account_id_from = '';
            }
            $ssbCreateTran = NULL;
            $createDayBook = $roidayBookRef;
            // $ssbCreateTran = CommanController::createTransaction($satRefId, 9, $request['loan_id'], $mLoan->applicant_id, $request['branch'], getBranchCode($request['branch'])->branch_code, $amountArraySsb, $paymentMode, $request['loan_associate_name'], $request['associate_member_id'], $mLoan->account_number, $cheque_dd_no, $bank_name, $branch_name = NULL, $paymentDate, $online_payment_id, $online_payment_by, NULL, 'CR');
            // $createDayBook = CommanController::createDayBook($ssbCreateTran, $satRefId, 9, $request['loan_id'], 0, $mLoan->applicant_id, $dueAmount, $request['deposite_amount'], 0, 'Loan EMI deposit', $mLoan->account_number, $request['branch'], getBranchCode($request['branch'])->branch_code, $amountArraySsb, $paymentMode, $request['loan_associate_name'], $request['associate_member_id'], $mLoan->account_number, $cheque_dd_no, $bank_name, $branch_name = NULL, $paymentDate, $online_payment_by, $online_payment_by, NULL, 'CR');
            if ($request['loan_emi_payment_mode'] == 3) {
                $checkData['type'] = 5;
                $checkData['branch_id'] = $request['branch'];
                // $checkData['loan_id']=$request['loan_id'];
                $checkData['day_book_id'] = $createDayBook;
                $checkData['cheque_id'] = $cheque_dd_no;
                $checkData['status'] = 1;
                $checkData['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($request['application_date'])));
                $ssbAccountTran = ReceivedChequePayment::create($checkData);
                $dataRC['status'] = 3;
                $receivedcheque = ReceivedCheque::find($cheque_dd_no);
                $receivedcheque->update($dataRC);
            }
            /*************** Head Implement ************/
            if ($request['loan_emi_payment_mode'] == 0) {
                $chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
                $v_no = "";
                for ($i = 0; $i < 10; $i++) {
                    $v_no .= $chars[mt_rand(0, strlen($chars) - 1)];
                }

                $roibranchDayBook = CommanController::branchDayBookNew($roidayBookRef, $branchId, 5, 55, $loanId, $ssb_transaction_id, $request['associate_member_id'], $member_id = $mLoan->member_id, $branchId, $branch_id_from = NULL, $deposit, 'Amount Received from ' . $ssbAccountDetails->account_no, 'SSB A/C Dr ' . ($deposit) . '', 'To ' . $mLoan->account_number . ' A/C Cr ' . ($deposit) . '', 'CR', 3, 'INR', $v_no = NULL, $ssb_account_id_from = NULL, $cheque_no = NULL, $transction_no = NULL, $request['application_date'], $entry_time = NULL, 1, Auth::user()->id, $request['application_date'], $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL, $companyId);



                $roibranchDayBook = CommanController::branchDayBookNew($roidayBookRef, $branchId, 4, 48, $ssbAccountDetails->id, $ssb_transaction_id, $request['associate_member_id'], $member_id = $mLoan->member_id, $branchId, $branch_id_from = NULL, $deposit, 'Loan Emi Transfer To' . $mLoan->account_number, 'SSB A/C Dr ' . ($deposit) . '', 'To ' . $mLoan->account_number . ' A/C Cr ' . ($deposit) . '', 'DR', 3, 'INR', $v_no = NULL, $ssb_account_id_from = NULL, $cheque_no = NULL, $transction_no = NULL, $request['application_date'], $entry_time = NULL, 1, Auth::user()->id, $request['application_date'], $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL, $companyId);



            } elseif ($request['loan_emi_payment_mode'] == 2) {
                $roibranchDayBook = CommanController::branchDayBookNew($roidayBookRef, $branchId, 5, 55, $loanId, $createDayBook, $request['associate_member_id'], $member_id = $mLoan->member_id, $branchId, $branch_id_from = NULL, $deposit, '' . $mLoan->account_number . ' EMI collection', 'Cash A/C Dr ' . ($deposit) . '', 'To ' . $mLoan->account_number . ' A/C Cr ' . ($deposit) . '', 'CR', 0, 'INR', $v_no = NULL, $ssb_account_id_from = NULL, $cheque_no = NULL, $transction_no = NULL, $request['application_date'], $entry_time = NULL, 1, Auth::user()->id, $request['application_date'], $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL, $companyId);

                $loan_head_id = $mLoan['loan']->head_id;




            } elseif ($request['loan_emi_payment_mode'] == 1) {
                $loan_head_id = $mLoan['loan']->head_id;
                if ($request['bank_transfer_mode'] == 0 && $request['bank_transfer_mode'] != '') {
                    $receivedcheque = \App\Models\ReceivedCheque::find($request['customer_cheque']);


                    $payment_type = 1;
                    $cheque_type = 1;
                    $amount_from_id = $request['associate_member_id'];
                    $amount_from_name = getMemberCustom($request['associate_member_id'])->first_name . ' ' . getMemberCustom($request['associate_member_id'])->last_name;
                    $cheque_no = $receivedcheque->cheque_no;
                    $cheque_date = date('Y-m-d', strtotime($receivedcheque->cheque_approved_date));
                    $cheque_bank_from = $request['customer_bank_name'];
                    $cheque_bank_ac_from = $request['customer_bank_account_number'];
                    $cheque_bank_ifsc_from = $request['customer_ifsc_code'];
                    $cheque_bank_branch_from = NULL;
                    $cheque_bank_to = $request['company_bank'];
                    $cheque_bank_ac_to = $request['bank_account_number'];
                    $v_no = NULL;
                    $v_date = NULL;
                    $ssb_account_id_from = NULL;
                    $transction_no = NULL;
                    $transction_bank_from = NULL;
                    $transction_bank_ac_from = NULL;
                    $transction_bank_ifsc_from = NULL;
                    $transction_bank_branch_from = NULL;
                    $company_name = $request->cheque_company_bank;
                    ;
                    $bankId = getSamraddhBankAccount($request['company_bank_account_number'])->id;
                    $head_id = getSamraddhBankAccount($request['company_bank_account_number']);

                    // $cId = \App\Models\SamraddhBank::where('account_head_id', $head_id)->first();
                    //   dd($request->all(),$head_id);
                    $company_bankId = $head_id->bank_id;
                    $transction_bank_ac_to = $bankId;
                    $transction_bank_to = $company_bankId;
                    $ifsc = getSamraddhBankAccount($request['company_bank_account_number'])->ifsc_code;
                } elseif ($request['bank_transfer_mode'] == 1) {
                    $payment_type = 2;
                    $cheque_type = NULL;
                    $amount_from_id = $request['associate_member_id'];
                    $amount_from_name = getMemberCustom($request['associate_member_id'])->first_name . ' ' . getMemberCustom($request['associate_member_id'])->last_name;
                    $cheque_no = NULL;
                    $cheque_date = NULL;
                    $cheque_bank_from = NULL;
                    $cheque_bank_ac_from = NULL;
                    $cheque_bank_ifsc_from = NULL;
                    $cheque_bank_branch_from = NULL;
                    $cheque_bank_to = NULL;
                    $cheque_bank_ac_to = NULL;
                    $transction_no = $request['utr_transaction_number'];
                    $v_no = NULL;
                    $v_date = NULL;
                    $ssb_account_id_from = NULL;
                    $transction_bank_from = $request['customer_bank_name'];
                    $transction_bank_ac_from = $request['customer_bank_account_number'];
                    $transction_bank_ifsc_from = $request['customer_ifsc_code'];
                    $transction_bank_branch_from = $request['customer_branch_name'];
                    $transction_bank_to = $request['company_bank'];
                    $transction_bank_ac_to = $request['bank_account_number'];
                    $company_name = getSamraddhBank($request['company_bank'])->bank_name;
                    $ifsc = getSamraddhBankAccount($transction_bank_ac_to)->ifsc_code;
                    $bankId = getSamraddhBankAccount($request['bank_account_number'])->id;
                    $head_id = getSamraddhBankAccount($request['bank_account_number'])->account_head_id;
                    $company_bankId = getSamraddhBank($request['company_bank'])->id;
                }

                $roibranchDayBook = CommanController::branchDayBookNew($roidayBookRef, $branchId, 5, 55, $loanId, $createDayBook, $request['associate_member_id'], $member_id = $mLoan->member_id, $branchId, $branch_id_from = NULL, $deposit, '' . $mLoan->account_number . ' EMI collection', 'Online A/C Dr ' . ($deposit) . '', 'To ' . $mLoan->account_number . ' A/C Cr ' . ($deposit) . '', 'CR', $payment_type, 'INR', $v_no = NULL, $ssb_account_id_from = NULL, $cheque_no = NULL, $transction_no = NULL, $request['application_date'], $entry_time = NULL, 1, Auth::user()->id, $request['application_date'], $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL, $companyId);

                $samraddhBankDaybook = CommanController::samraddhBankDaybookNew($roidayBookRef, $bank_id = $company_bankId, $account_id = $bankId, 5, 55, $loanId, $createDayBook, $request['associate_member_id'], $mLoan->member_id, $branchId, $deposit, $deposit, $deposit, 'Loan Panelty Charge', 'Online A/C Cr. ' . ($deposit) . '', 'Online A/C Cr. ' . ($deposit) . '', 'CR', $payment_type, 'INR', $company_bankId, $company_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $company_name, $transction_bank_ac_to, NULL, $ifsc, $request['application_date'], $request['application_date'], $entry_time = NULL, 1, Auth::user()->id, $request['application_date'], $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $companyId);

                $dataRC['status'] = 3;
                if ($request['bank_transfer_mode'] == 0) {
                    $receivedcheque = \App\Models\ReceivedCheque::find($request['customer_cheque']);
                    $receivedcheque->update($dataRC);
                }
            }
            /*************** Head Implement ************/
            /*---------- commission script  start  ---------*/
            $daybookId = $createDayBook;
            $total_amount = $request['deposite_amount'];
            $percentage = 2;
            $month = NULL;
            $type_id = $request['loan_id'];
            $type = 7;
            $associate_id = $request['associate_member_id'];
            $branch_id = $request['branch'];
            $commission_type = 0;
            $associateDetail = Member::where('id', $associate_id)->first();
            $carder = $associateDetail->current_carder_id;
            $associate_exist = 0;
            $percentInDecimal = $percentage / 100;
            $commission_amount = round($percentInDecimal * $total_amount, 4);
            $loan_associate_code = $request->loan_associate_code;
            $associateCommission['member_id'] = $associate_id;
            $associateCommission['branch_id'] = $branch_id;
            $associateCommission['type'] = $type;
            $associateCommission['type_id'] = $type_id;
            $associateCommission['day_book_id'] = $daybookId;
            $associateCommission['total_amount'] = $total_amount;
            $associateCommission['month'] = $month;
            $associateCommission['commission_amount'] = $commission_amount;
            $associateCommission['percentage'] = $percentage;
            $associateCommission['commission_type'] = $commission_type;
            $date = \App\Models\Daybook::where('id', $daybookId)->first();
            $associateCommission['created_at'] = $request->created_at;
            $associateCommission['pay_type'] = 4;
            $associateCommission['carder_id'] = $carder;
            $associateCommission['associate_exist'] = $associate_exist;
            if ($loan_associate_code != 9999999) {
                $associateCommissionInsert = \App\Models\CommissionEntryLoan::create($associateCommission);
            }
            // Collection start
            $outstandingAmount = '0';
            $createLoanDayBook = CommanController::createLoanDayBook($roidayBookRef, $daybookId, $mLoan->loan_type, 0, $mLoan->id, $mLoan->id, $mLoan->account_number, $mLoan->member_id, $roi, $principal_amount, $outstandingAmount, $request['deposite_amount'], $desType, $request['branch'], getBranchCode($request['branch'])->branch_code, 'CR', 'INR', $paymentMode, $cheque_dd_no, $bank_name, $branch_name = NULL, $paymentDate, $online_payment_id, 1, 1, $cheque_date, $account_number, NULL, $request['loan_associate_name'], $request['associate_member_id'], $request['branch'], $totalDailyInterest, $totalDayInterest, $penalty, $companyId, $request['recovery_module']);
            $this->headTransaction($createLoanDayBook, $transactionPaymentMode, 3);

            $totalDepsoit = LoanDaybooks::where('account_number', $mLoan->account_number)->where('loan_sub_type', 0)->where('is_deleted', 0)->sum('deposit');
            //  event(new UserActivity($createLoanDayBook,'Group Loan Emi',$request));
            $text = 'Dear Member,Received Rs.' . $request['deposite_amount'] . ' as EMI of Loan A/C ' . $mLoan->account_number . ' on ' . date('d/m/Y', strtotime(convertDate($request['application_date']))) . ' Total recovery Rs. ' . $totalDepsoit . ' Thank You. Samraddh Bestwin Microfinance';
            ;
            $temaplteId = 1207166308935249821;
            $contactNumber = array();
            $memberDetail = Member::find($mLoan->customer_id);
            $contactNumber[] = $memberDetail->mobile_no;
            $sendToMember = new Sms();
            $sendToMember->sendSms($contactNumber, $text, $temaplteId);
            DB::commit();
        } catch (\Exception $ex) {
            dd($ex->getLine(),$ex->getMessage(),$ex->getFile());

            DB::rollback();
            return back()->with('alert', $ex->getMessage().$ex->getLine());
        }
        return back()->with('success', 'Loan EMI Successfully submitted!');
    }
    /**
     * Deposite Group loan EMI.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return JSON
     */

    public function emiTransactionsList(Request $request)
    {
        if ($request->ajax()) {
            if ($request['loanType'] != 3) {
                /*$data = LoanDayBooks::where('loan_type', $request['loanType'])->where('is_deleted', 0)
                    ->where('loan_id', $request['loanId']);*/
                $data = \App\Models\LoanEmisNew::with(['loanEmiDetails', 'loanDetails'])->where('loan_id', $request['loanId'])->where('loan_type', '!=', 3)->where('is_deleted', '0');
            } else {
                $data = \App\Models\LoanEmisNew::with(['loanEmiDetails'])->where('loan_id', $request['loanId'])->where('loan_type', 3)->where('is_deleted', '0');
            }
            // $data1 = $data->where('is_deleted', '0')
            //     ->get();
            $data1 = $data->count();
            $count = $data1;
            $data = $data->offset($_POST['start'])->limit($_POST['length']) /*->orderby('emi_date', 'asc')*/
                ->get();
            $dataCount = $count;
            $totalCount = $dataCount;
            $sno = $_POST['start'];
            if ($_POST['pages'] == 1) {
                $total = 0;
            } else {
                $total = $_POST['total'];
            }
            if ($_POST['pages'] == "1") {
                $length = ($_POST['pages']) * $_POST['length'];
            } else {
                $length = ($_POST['pages'] - 1) * $_POST['length'];
            }
            $rowReturn = array();
            // pd($data);
            foreach ($data as $row) {
                $sno++;
                //     if (isset($row['loanEmiDetails']->loan_sub_type))
                //     {
                //         if ($row['loanEmiDetails']->loan_sub_type == 0)
                //         {
                //             $val['deposite'] = $row->deposit;
                //         }
                //         else
                //         {
                //             $val['deposite'] =0;
                //         }
                // }
                // else{
                $val['deposite'] = $row->deposit;
                // }
                $val['DT_RowIndex'] = $sno;
                // if(isset($row->emi_id))
                // {
                //     if(strlen($row->id) < 8)
                //     {
                //             $val['transaction_id'] =  str_pad($row->id, 8, '0', STR_PAD_LEFT);
                //     }
                //     else{
                //         $val['transaction_id'] = $row->id;
                //     }
                // }
                // else{
                //     $val['transaction_id'] ='N/A';
                // }
                if (isset($row->emi_received_date)) {
                    $val['date'] = date("d/m/Y", strtotime($row->emi_received_date));
                } else if (isset($row->emi_date)) {
                    $val['date'] = date("d/m/Y", strtotime($row->emi_date));
                } else {
                    $val['date'] = date("d/m/Y", strtotime($row->created_at));
                }
                $paymentMode = 'N/A';
                if (isset($row['loanEmiDetails']->payment_mode)) {
                    if ($row['loanEmiDetails']->payment_mode == 0) {
                        $paymentMode = 'Cash';
                    } elseif ($row['loanEmiDetails']->payment_mode == 1) {
                        $paymentMode = 'Cheque';
                    } elseif ($row['loanEmiDetails']->payment_mode == 2) {
                        $paymentMode = 'DD';
                    } elseif ($row['loanEmiDetails']->payment_mode == 3) {
                        $paymentMode = 'Online Transaction';
                    } elseif ($row['loanEmiDetails']->payment_mode == 4) {
                        $paymentMode = 'By Saving Account ';
                    } elseif ($row['loanEmiDetails']->payment_mode == 6) {
                        $paymentMode = 'JV ';
                    }
                } else {
                    if ($row->payment_mode == 0) {
                        $paymentMode = 'Cash';
                    } elseif ($row->payment_mode == 1) {
                        $paymentMode = 'Cheque';
                    } elseif ($row->payment_mode == 2) {
                        $paymentMode = 'DD';
                    } elseif ($row->payment_mode == 3) {
                        $paymentMode = 'Online Transaction';
                    } elseif ($row->payment_mode == 4) {
                        $paymentMode = 'By Saving Account ';
                    } elseif ($row->payment_mode == 6) {
                        $paymentMode = 'JV ';
                    }
                }
                $val['payment_mode'] = $paymentMode;
                if (isset($row['loanEmiDetails']->jv_journal_amount)) {
                    $val['jv_amount'] = number_format((float) $row->jv_journal_amount, 2, '.', '');
                } else {
                    $val['jv_amount'] = 0;
                }
                if (isset($row['loanDetails']->transfer_amount)) {
                    $val['sanction_amount'] = $row['loanDetails']->transfer_amount;
                }
                if (isset($row['loanEmiDetails']->description)) {
                    $val['description'] = $row['loanEmiDetails']->description;
                } else {
                    $val['description'] = "Loan EMI Deposit";
                }
                if (isset($row->penalty)) {
                    $deposite = $row->penalty;
                    $val['penalty'] = $deposite;
                } else {
                    $val['penalty'] = 0;
                }
                if (isset($row['loanEmiDetails']->loan_sub_type)) {
                    if ($row['loanEmiDetails']->loan_sub_type == 0) {
                        $roi_amount = $row->roi_amount;
                        $val['roi_amount'] = $roi_amount + $row->daily_wise_interest;
                    } else {
                        $val['roi_amount'] = '0';
                    }
                } else {
                    $val['roi_amount'] = $row->roi_amount;
                }
                if (isset($row['loanEmiDetails']->loan_sub_type)) {
                    if ($row['loanEmiDetails']->loan_sub_type == 0) {
                        $principal_amount = $row->principal_amount;
                        $val['principal_amount'] = $principal_amount;
                    } else {
                        $val['principal_amount'] = $row->principal_amount;
                    }
                } else {
                    $val['principal_amount'] = $row->principal_amount;
                }
                if ($request['loanType'] != 3) {
                    $val['opening_balance'] = $row->opening_balance;
                }
                if (isset($row['loanEmiDetails']->loan_sub_type)) {
                    // if ($row['loanEmiDetails']->loan_sub_type == 0)
                    // {
                    $opening_balance = $row->out_standing_amount;
                    $val['opening_balance'] = $opening_balance;
                    // }
                    // else
                    // {
                    //     $val['opening_balance'] = '0';
                    // }
                } else {
                    $val['opening_balance'] = $row->out_standing_amount;
                }
                $penalty = 0;
                if (isset($row->penalty)) {
                    $penalty = $row->penalty;
                }
                if (isset($row['loanEmiDetails']->jv_journal_amount)) {
                    $total = $total + $row['loanEmiDetails']->jv_journal_amount;
                } else if (isset($row->deposit)) {
                    $total = $total + $row->deposit + $penalty;
                } else {
                    $total = $total + 0;
                }
                $val['balance'] = number_format((float) $total, 2, '.', '') . ' <i class="fas fa-rupee-sign"></i>';
                $rowReturn[] = $val;
            }
            /*return Datatables::of($data)
            ->addIndexColumn()
            ->addColumn('transaction_id', function($row){
                $transaction_id = $row->id;
                return $transaction_id;
            })
            ->rawColumns(['transaction_id'])
            ->addColumn('date', function($row){
                $date = date("d/m/Y", strtotime($row->payment_date));
                return $date;
            })
            ->rawColumns(['date'])
            ->addColumn('payment_mode', function($row){
                if($row->payment_mode == 0){
                    $paymentMode = 'Cash';
                }elseif($row->payment_mode == 1){
                    $paymentMode = 'Cheque';
                }elseif($row->payment_mode == 2){
                    $paymentMode = 'DD';
                }elseif($row->payment_mode == 3){
                    $paymentMode = 'Online Transaction';
                }elseif($row->payment_mode == 4){
                    $paymentMode = 'By Saving Account ';
                }
                return $paymentMode;
            })
            ->rawColumns(['payment_mode'])
            ->addColumn('description', function($row){
                $description =  $row->description;
                return $description;
            })
            ->rawColumns(['description'])
            ->addColumn('penalty', function($row){
                if($row->loan_sub_type == 1){
                    $deposite =  $row->deposit.' <i class="fas fa-rupee-sign"></i>';
                    return $deposite;
                }else{
                    return 'N/A';
                }
            })
            ->escapeColumns(['description'])
            ->addColumn('deposite', function($row){
                if($row->loan_sub_type == 0){
                    $deposite =  $row->deposit.' <i class="fas fa-rupee-sign"></i>';
                    return $deposite;
                }else{
                    return 'N/A';
                }
            })
            ->escapeColumns(['description'])
            ->addColumn('roi_amount', function($row){
                if($row->loan_sub_type == 0){
                    $roi_amount =  $row->roi_amount.' <i class="fas fa-rupee-sign"></i>';
                    return $roi_amount;
                }else{
                    return 'N/A';
                }
            })
            ->escapeColumns(['description'])
            ->addColumn('principal_amount', function($row){
                if($row->loan_sub_type == 0){
                    $principal_amount =  $row->principal_amount.' <i class="fas fa-rupee-sign"></i>';
                    return $principal_amount;
                }else{
                    return 'N/A';
                }
            })
            ->escapeColumns(['description'])
            ->addColumn('opening_balance', function($row){
                if($row->loan_sub_type == 0){
                    $opening_balance =  $row->opening_balance;
                    return $opening_balance;
                }else{
                    return 'N/A';
                }
            })
            ->rawColumns(['opening_balance'])
            ->make(true);*/
        }
        $output = array(
            "draw" => $_POST['draw'],
            "recordsTotal" => $totalCount,
            "recordsFiltered" => $totalCount,
            "data" => $rowReturn,
            'totalAmount' => $total,
        );
        return json_encode($output);
    }
    /**
     * Display loan details.
     *
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function printView($id, $type)
    {
        $data['title'] = "Download Loan PDF";
        $data['id'] = $id;
        $data['loanDetails'] = ($type != 3) ?
            Memberloans::with('loan', 'loanMember', 'loanMemberBankDetails', 'loanMemberIdProofs', 'LoanApplicants', 'LoanCoApplicants', 'LoanGuarantor', 'Loanotherdocs', 'GroupLoanMembers', 'loanInvestmentPlans')->findOrFail($id)
            :
            Grouploans::with('loan', 'loanMember', 'loanMemberBankDetails', 'LoanApplicants', 'LoanCoApplicants', 'LoanGuarantor')->findOrFail($id)
        ;
        $data['formPrintUrl'] = URL::to("admin/loan/form/print/" . $id . "/" . $type . "");
        $data['formTermConditionUrl'] = URL::to("admin/loan/form/termcondition/" . $id . "/" . $type . "");
        return view('templates.admin.loan.print_view', $data); /*$data['title'] = "Download Loan PDF";
$data['id'] = $id;
$data['data'] =\App\Models\Memberloans::with('loan','loanMember','loanMemberBankDetails','loanMemberIdProofs','LoanApplicants','LoanCoApplicants','LoanGuarantor','Loanotherdocs','GroupLoanMembers','loanInvestmentPlans')->where('id',$id)->orderby('id','DESC')->first();
return view('templates.admin.loan.loan_form_pdf', $data);*/
    }
    /**
     * Print loan form details.
     *
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function printLoanForm($id, $type)
    {
        $data['loanDetails'] = array(
            ''
        );
        $data['loanDetails'] = ($type != 'G') ?
            Memberloans::with([
                'loan',
                'loanMember',
                'loanMemberBankDetails',
                'loanMemberIdProofs',
                'LoanApplicants',
                'LoanCoApplicants',
                'LoanGuarantor',
                'Loanotherdocs',
                'GroupLoanMembers',
                'loanInvestmentPlans',
                'company' => function ($q) {
                    $q->select('id', 'name', 'short_name');
                }
            ])->findOrFail($id)
            :
            Grouploans::with([
                'loan',
                'loanMember',
                'loanMemberBankDetails',
                'LoanApplicants',
                'LoanCoApplicants',
                'LoanGuarantor',
                'company' => function ($q) {
                    $q->select('id', 'name', 'short_name');
                }
            ])->findOrFail($id)
        ;
        $data['EMI'] = [];
        if ($type != 'G') {
            $data['EMI'] = LoanDayBooks::where('loan_type', $type)->where('loan_id', $id)->get()
                ->toArray();
        } else {
            $data['EMI'] = LoanDayBooks::where('loan_type', $type)->where('group_loan_id', $id)->get()
                ->toArray();
        }
        $data['loanDetails'] = $data['loanDetails']->toArray();
        return view('templates.admin.loan.personalAndEmployDetail', $data);
    }
    public function update_pdf_generate_status(Request $request)
    {
        $id = $request->id;
        $status = Memberloans::where('id', $id)->update(['pdf_generate_status' => 1]);
        $return_array = compact('status');
        return json_encode($return_array);
    }
    /**
     * Show loan  commission list.
     * Route: /loan
     * Method: get
     * @return  view
     */
    public function loanCommission($id)
    {
        //die('hi');
        $data['title'] = 'Loan Commission Detail | Listing';
        // $data['plans'] = Plans::where('status',1)->get();
        $data['loan'] = Memberloans::where('id', $id)->first();
        return view('templates.admin.loan.commissionDetailLoan', $data);
    }
    /**
     * Get loan  commission list
     * Route: ajax call from - /admin/loan
     * Method: Post
     * @param  \Illuminate\Http\Request  $request
     * @return JSON array
     */
    public function loanCommissionList(Request $request)
    {
        if ($request->ajax()) {
            $arrFormData = array();
            if (!empty($_POST['searchform'])) {
                foreach ($_POST['searchform'] as $frm_data) {
                    $arrFormData[$frm_data['name']] = $frm_data['value'];
                }
            }
            $data = \App\Models\AssociateCommission::where('type_id', $arrFormData['id'])->whereIn(
                'type',
                array(
                    4,
                    6
                )
            )
                ->where('status', 1)->where('is_deleted', '0');
            if (isset($arrFormData['is_search']) && $arrFormData['is_search'] == 'yes') {
                if ($arrFormData['start_date'] != '') {
                    $startDate = date("Y-m-d", strtotime(convertDate($arrFormData['start_date'])));
                    if ($arrFormData['end_date'] != '') {
                        $endDate = date("Y-m-d ", strtotime(convertDate($arrFormData['end_date'])));
                    } else {
                        $endDate = '';
                    }
                    $data = $data->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate]);
                }
                if (isset($arrFormData['plan_id']) && $arrFormData['plan_id'] != '') {
                    $meid = $arrFormData['plan_id'];
                    $data = $data->whereHas('investment', function ($query) use ($meid) {
                        $query->where('member_investments.plan_id', $meid);
                    });
                }
            }
            $count = $data->orderby('id', 'DESC')
                ->count();
            // $count=count($data1);
            $data = $data->orderby('id', 'DESC')
                ->offset($_POST['start'])->limit($_POST['length'])->get();
            $totalCount = \App\Models\AssociateCommission::where('type_id', $arrFormData['id'])->whereIn(
                'type',
                array(
                    4,
                    6,
                    7,
                    8
                )
            )
                ->where('status', 1)
                ->count();
            $sno = $_POST['start'];
            $rowReturn = array();
            foreach ($data as $val) {
                $sno++;
                $row['DT_RowIndex'] = $sno;
                $row['investment_account'] = getSeniorData($val->member_id, 'first_name') . ' ' . getSeniorData($val->member_id, 'last_name');
                $row['plan_name'] = getSeniorData($val->member_id, 'associate_no');
                $row['total_amount'] = $val->total_amount;
                $row['commission_amount'] = $val->commission_amount;
                $row['percentage'] = $val->percentage;
                $carder_name = getCarderName($val->carder_id);
                $row['carder_name'] = $carder_name;
                $commission_for = '';
                if ($val->type == 4) {
                    $commission_for = 'Loan Commission';
                }
                if ($val->type == 6) {
                    $commission_for = 'Loan Collection';
                }
                if ($val->type == 7) {
                    $commission_for = 'Group Loan Commission';
                }
                if ($val->type == 8) {
                    $commission_for = 'Group Loan Collection';
                }
                $row['commission_type'] = $commission_for;
                $pay_type = '';
                if ($val->pay_type == 4) {
                    $pay_type = 'Loan Emi';
                } elseif ($val->pay_type == 5) {
                    $pay_type = 'Loan Panelty';
                }
                $row['pay_type'] = $pay_type;
                if ($val->is_distribute == 1) {
                    $is_distribute = 'Yes';
                } else {
                    $is_distribute = 'No';
                }
                $row['is_distribute'] = $is_distribute;
                $created_at = date("d/m/Y", strtotime($val->created_at));
                $row['created_at'] = $created_at;
                $rowReturn[] = $row;
            }
            // echo 'hi';
            //  print_r($rowReturn);die;
            $output = array(
                "draw" => $_POST['draw'],
                "recordsTotal" => $totalCount,
                "recordsFiltered" => $totalCount,
                "data" => $rowReturn,
            );
            return json_encode($output);
        }
    }
    /**
     * Show loan  commission list.
     * Route: /loan
     * Method: get
     * @return  view
     */
    public function loanGroupCommission($id)
    {
        $data['title'] = 'Loan Commission Detail | Listing';
        // $data['plans'] = Plans::where('status',1)->get();
        $data['loan'] = Grouploans::where('id', $id)->first();
        return view('templates.admin.loan.commissionDetailLoanGroup', $data);
    }
    /**
     * Get loan  commission list
     * Route: ajax call from - /admin/loan
     * Method: Post
     * @param  \Illuminate\Http\Request  $request
     * @return JSON array
     */
    public function loanGroupCommissionList(Request $request)
    {
        if ($request->ajax()) {
            $arrFormData = array();
            if (!empty($_POST['searchform'])) {
                foreach ($_POST['searchform'] as $frm_data) {
                    $arrFormData[$frm_data['name']] = $frm_data['value'];
                }
            }
            $data = \App\Models\AssociateCommission::where('type_id', $arrFormData['id'])->whereIn(
                'type',
                array(
                    7,
                    8
                )
            )->where('is_deleted', '0')
                ->where('status', 1);
            if (isset($arrFormData['is_search']) && $arrFormData['is_search'] == 'yes') {
                if ($arrFormData['start_date'] != '') {
                    $startDate = date("Y-m-d", strtotime(convertDate($arrFormData['start_date'])));
                    if ($arrFormData['end_date'] != '') {
                        $endDate = date("Y-m-d ", strtotime(convertDate($arrFormData['end_date'])));
                    } else {
                        $endDate = '';
                    }
                    $data = $data->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate]);
                }
                if (isset($arrFormData['plan_id']) && $arrFormData['plan_id'] != '') {
                    $meid = $arrFormData['plan_id'];
                    $data = $data->whereHas('investment', function ($query) use ($meid) {
                        $query->where('member_investments.plan_id', $meid);
                    });
                }
            }
            $count = $data->orderby('id', 'DESC')
                ->count();
            // $count=count($data1);
            $data = $data->orderby('id', 'DESC')
                ->offset($_POST['start'])->limit($_POST['length'])->get();
            $totalCount = \App\Models\AssociateCommission::where('type_id', $arrFormData['id'])->whereIn(
                'type',
                array(
                    7,
                    8
                )
            )
                ->where('status', 1)
                ->count();
            $sno = $_POST['start'];
            $rowReturn = array();
            foreach ($data as $val) {
                $sno++;
                $row['DT_RowIndex'] = $sno;
                $row['investment_account'] = getSeniorData($val->member_id, 'first_name') . ' ' . getSeniorData($val->member_id, 'last_name');
                $row['plan_name'] = getSeniorData($val->member_id, 'associate_no');
                $row['total_amount'] = $val->total_amount;
                $row['commission_amount'] = $val->commission_amount;
                $row['percentage'] = $val->percentage;
                $carder_name = getCarderName($val->carder_id);
                $row['carder_name'] = $carder_name;
                $commission_for = '';
                if ($val->type == 4) {
                    $commission_for = 'Loan Commission';
                }
                if ($val->type == 6) {
                    $commission_for = 'Loan Collection';
                }
                if ($val->type == 7) {
                    $commission_for = 'Group Loan Commission';
                }
                if ($val->type == 8) {
                    $commission_for = 'Group Loan Collection';
                }
                $row['commission_type'] = $commission_for;
                $pay_type = '';
                if ($val->pay_type == 4) {
                    $pay_type = 'Loan Emi';
                } elseif ($val->pay_type == 5) {
                    $pay_type = 'Loan Panelty';
                }
                $row['pay_type'] = $pay_type;
                if ($val->is_distribute == 1) {
                    $is_distribute = 'Yes';
                } else {
                    $is_distribute = 'No';
                }
                $row['is_distribute'] = $is_distribute;
                $created_at = date("d/m/Y", strtotime($val->created_at));
                $row['created_at'] = $created_at;
                $rowReturn[] = $row;
            }
            // echo 'hi';
            //  print_r($rowReturn);die;
            $output = array(
                "draw" => $_POST['draw'],
                "recordsTotal" => $totalCount,
                "recordsFiltered" => $totalCount,
                "data" => $rowReturn,
            );
            return json_encode($output);
        }
    }
    /**
     * Display renew form.
     *
     * @return \Illuminate\Http\Response
     */
    public function getCollectorAssociate(Request $request)
    {
        $code = $request->code;
        $applicationDate = $request->applicationDate;
        $collectorDetails = Member::with('savingAccount')->leftJoin('carders', 'members.current_carder_id', '=', 'carders.id')
            ->where('members.associate_no', $code)
            ->where('members.status', 1)
            ->where('members.is_deleted', 0)
            ->where('members.is_associate', 1)
            ->where('members.is_block', 0)
            ->where('members.associate_status', 1)
            ->select('carders.name as carders_name', 'members.first_name', 'members.last_name', 'members.id')
            ->first();
        if ($collectorDetails) {
            if ($collectorDetails['savingAccount']) {
                $ssbTransaction = SavingAccountTranscation::select('id', 'opening_balance')->where('account_no', $collectorDetails['savingAccount'][0]->account_no)
                    ->whereDate('created_at', date("Y-m-d", strtotime(convertDate($applicationDate))))->orderBy('id', 'desc')
                    ->first();
                if ($ssbTransaction) {
                    $ssbAmount = $ssbTransaction->opening_balance;
                } else {
                    $ssbTransaction = SavingAccountTranscation::select('id', 'opening_balance')->where('account_no', $collectorDetails['savingAccount'][0]->account_no)
                        ->whereDate('created_at', '>', date("Y-m-d", strtotime(convertDate($applicationDate))))->first();
                    if ($ssbTransaction) {
                        $ssbAmount = $ssbTransaction->opening_balance;
                    } else {
                        $ssbTransaction = SavingAccountTranscation::select('id', 'opening_balance')->where('account_no', $collectorDetails['savingAccount'][0]->account_no)
                            ->whereDate('created_at', '<', date("Y-m-d", strtotime(convertDate($applicationDate))))->orderBy('id', 'desc')
                            ->first();
                        if ($ssbTransaction) {
                            $ssbAmount = $ssbTransaction->opening_balance;
                        } else {
                            $ssbAmount = 0;
                        }
                    }
                }
            } else {
                $ssbAmount = 0;
            }
        } else {
            $ssbAmount = 0;
        }
        if ($collectorDetails) {
            return Response::json(['msg_type' => 'success', 'collectorDetails' => $collectorDetails, 'ssbAmount' => $ssbAmount]);
        } else {
            return Response::json(['view' => 'No data found', 'msg_type' => 'error']);
        }
    }
    public function personalAndEmployDetail(Request $request)
    {
        $data['title'] = "PERSONAL AND EMPLOYMENT DETAILS";
        return view('templates.admin.loan.personalAndEmployDetail', $data);
    }
    public function updateBranchCashFromBackDate($amount, $branch_id, $ftdate)
    {
        $globaldate = $ftdate;
        $entryTime = date("H:i:s");
        $entryDate = date("Y-m-d", strtotime(convertDate($globaldate)));
        $getCurrentBranchRecord = \App\Models\BranchCash::where('branch_id', $branch_id)->whereDate('entry_date', $entryDate)->first();
        if ($getCurrentBranchRecord) {
            $bResult = \App\Models\BranchCash::find($getCurrentBranchRecord->id);
            $bData['balance'] = $getCurrentBranchRecord->balance + $amount;
            if ($getCurrentBranchRecord->closing_balance > 0) {
                $bData['closing_balance'] = $getCurrentBranchRecord->closing_balance + $amount;
            }
            $bData['updated_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($globaldate)));
            $bResult->update($bData);
            $getNextBranchRecord = \App\Models\BranchCash::where('branch_id', $branch_id)->whereDate('entry_date', '>', $entryDate)->orderby('entry_date', 'ASC')
                ->get();
            if ($getNextBranchRecord) {
                foreach ($getNextBranchRecord as $key => $value) {
                    $sResult = \App\Models\BranchCash::find($value->id);
                    $sData['opening_balance'] = $value->closing_balance;
                    $sData['balance'] = $value->balance + $amount;
                    if ($value->closing_balance > 0) {
                        $sData['closing_balance'] = $value->closing_balance + $amount;
                    }
                    $sData['updated_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($globaldate)));
                    $sResult->update($sData);
                }
            }
        } else {
            $oldDateRecord = \App\Models\BranchCash::where('branch_id', $branch_id)->whereDate('entry_date', '<', $entryDate)->orderby('entry_date', 'DESC')
                ->first();
            if ($oldDateRecord) {
                $Result1 = \App\Models\BranchCash::find($oldDateRecord->id);
                $data1['closing_balance'] = $oldDateRecord->balance;
                $Result1->update($data1);
                $insertid1 = $oldDateRecord->id;
                $dataRecordExists = \App\Models\BranchCash::where('branch_id', $branch_id)->whereDate('entry_date', '>', $entryDate)->orderby('entry_date', 'ASC')
                    ->get();
                $data['balance'] = $oldDateRecord->balance + $amount;
                $data['opening_balance'] = $oldDateRecord->balance;
                if ($dataRecordExists) {
                    $data['closing_balance'] = $oldDateRecord->balance + $amount;
                    foreach ($dataRecordExists as $key => $value) {
                        $sResult = \App\Models\BranchCash::find($value->id);
                        $sData['opening_balance'] = $value->closing_balance;
                        $sData['balance'] = $value->balance + $amount;
                        if ($value->closing_balance > 0) {
                            $sData['closing_balance'] = $value->closing_balance + $amount;
                        }
                        $sData['updated_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($globaldate)));
                        $sResult->update($sData);
                    }
                } else {
                    $data['closing_balance'] = 0;
                }
                $data['opening_balance'] = $oldDateRecord->balance;
                $data['balance'] = $oldDateRecord->balance;
                $data['closing_balance'] = 0;
                $data['branch_id'] = $branch_id;
                $data['entry_date'] = $entryDate;
                $data['entry_time'] = $entryTime;
                $data['type'] = 1;
                $data['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($entryDate)));
                $transcation = \App\Models\BranchCash::create($data);
                $insertid = $transcation->id;
            } else {
                $data['balance'] = $amount;
                $data['opening_balance'] = 0;
                $data['closing_balance'] = 0;
                $data['branch_id'] = $branch_id;
                $data['entry_date'] = $entryDate;
                $data['entry_time'] = $entryTime;
                $data['type'] = 1;
                $data['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($entryDate)));
                $transcation = \App\Models\BranchCash::create($data);
                $insertid = $transcation->id;
            }
        }
        $getCurrentBranchClosingRecord = \App\Models\BranchClosing::where('branch_id', $branch_id)->whereDate('entry_date', $entryDate)->first();
        if ($getCurrentBranchClosingRecord) {
            $bResult = \App\Models\BranchClosing::find($getCurrentBranchClosingRecord->id);
            $bData['balance'] = $getCurrentBranchClosingRecord->balance + $amount;
            if ($getCurrentBranchClosingRecord->closing_balance > 0) {
                $bData['closing_balance'] = $getCurrentBranchClosingRecord->closing_balance + $amount;
            }
            $bData['updated_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($globaldate)));
            $bResult->update($bData);
            $getNextBranchClosingRecord = \App\Models\BranchClosing::where('branch_id', $branch_id)->whereDate('entry_date', '>', $entryDate)->orderby('entry_date', 'ASC')
                ->get();
            if ($getNextBranchClosingRecord) {
                foreach ($getNextBranchClosingRecord as $key => $value) {
                    $sResult = \App\Models\BranchClosing::find($value->id);
                    $sData['opening_balance'] = $value->closing_balance;
                    $sData['balance'] = $value->balance + $amount;
                    if ($value->closing_balance > 0) {
                        $sData['closing_balance'] = $value->closing_balance + $amount;
                    }
                    $sData['updated_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($globaldate)));
                    $sResult->update($sData);
                }
            }
        } else {
            $oldDateRecord = \App\Models\BranchClosing::where('branch_id', $branch_id)->whereDate('entry_date', '<', $entryDate)->orderby('entry_date', 'DESC')
                ->first();
            if ($oldDateRecord) {
                $Result1 = \App\Models\BranchClosing::find($oldDateRecord->id);
                $data1['closing_balance'] = $oldDateRecord->balance;
                $Result1->update($data1);
                $insertid1 = $oldDateRecord->id;
                $data1RecordExists = \App\Models\BranchClosing::where('branch_id', $branch_id)->whereDate('entry_date', '>', $entryDate)->orderby('entry_date', 'ASC')
                    ->get();
                $data['balance'] = $oldDateRecord->balance + $amount;
                $data['opening_balance'] = $oldDateRecord->balance;
                if ($data1RecordExists) {
                    $data['closing_balance'] = $oldDateRecord->balance + $amount;
                    foreach ($data1RecordExists as $key => $value) {
                        $sResult = \App\Models\BranchClosing::find($value->id);
                        $sData['opening_balance'] = $value->closing_balance;
                        $sData['balance'] = $value->balance + $amount;
                        if ($value->closing_balance > 0) {
                            $sData['closing_balance'] = $value->closing_balance + $amount;
                        }
                        $sData['updated_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($globaldate)));
                        $sResult->update($sData);
                    }
                } else {
                    $data['closing_balance'] = 0;
                }
                $data['opening_balance'] = $oldDateRecord->balance;
                $data['closing_balance'] = 0;
                $data['balance'] = $oldDateRecord->balance;
                $data['branch_id'] = $branch_id;
                $data['entry_date'] = $entryDate;
                $data['entry_time'] = $entryTime;
                $data['type'] = 1;
                $data['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($entryDate)));
                $transcation = \App\Models\BranchClosing::create($data);
                $insertid = $transcation->id;
            } else {
                $data['balance'] = $amount;
                $data['opening_balance'] = 0;
                $data['closing_balance'] = 0;
                $data['branch_id'] = $branch_id;
                $data['entry_date'] = $entryDate;
                $data['entry_time'] = $entryTime;
                $data['type'] = 1;
                $data['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($entryDate)));
                $transcation = \App\Models\BranchClosing::create($data);
                $insertid = $transcation->id;
            }
        }
    }
    public function updateSsbDayBookAmount($amount, $account_number, $date, $companyId)
    {
        $globaldate = $date;
        $entryTime = date("H:i:s");
        $entryDate = date("Y-m-d", strtotime(convertDate($date)));
        $getCurrentBranchRecord = SavingAccountTranscation::where('account_no', $account_number)->whereDate('created_at', $entryDate)->whereCompanyId($companyId)->first();
        if (isset($getCurrentBranchRecord->id)) {
            $bResult = SavingAccountTranscation::find($getCurrentBranchRecord->id);
            $bData['opening_balance'] = $getCurrentBranchRecord->opening_balance - $amount;
            $bData['updated_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($globaldate)));
        }
        $getNextBranchRecord = SavingAccountTranscation::where('account_no', $account_number)->whereDate('created_at', '>', $entryDate)->whereCompanyId($companyId)->orderby('created_at', 'ASC')
            ->get();
        if ($getNextBranchRecord) {
            foreach ($getNextBranchRecord as $key => $value) {
                $sResult = SavingAccountTranscation::find($value->id);
                $sData['opening_balance'] = $value->opening_balance - $amount;
                $sData['company_id'] = $companyId;
                $sData['updated_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($globaldate)));
                $sResult->update($sData);
            }
        }
    }
    public function getBankDayBookAmount(Request $request)
    {
        $fromBankId = $request->fromBankId;
        $date = date("Y-m-d", strtotime(convertDate($request->date)));
        $bankRes = SamraddhBankClosing::where('bank_id', $fromBankId)->whereDate('entry_date', $date) /*->orderBy('entry_date', 'desc')*/->first();
        if ($bankRes) {
            $bankDayBookAmount = (int) $bankRes->balance;
        } else {
            $bankRes = SamraddhBankClosing::where('bank_id', $fromBankId)->whereDate('entry_date', '<', $date)->orderby('entry_date', 'DESC')
                ->first();
            $bankDayBookAmount = (int) $bankRes->balance;
        }
        $return_array = compact('bankDayBookAmount');
        return json_encode($return_array);
    }
    // Edit Branch to ho
    public function getBranchDayBookAmount(Request $request)
    {
        $branch_id = $request->branch_id;
        $date = date("Y-m-d", strtotime(convertDate($request->date)));
        $microLoanRes = BranchCash::select('balance', 'loan_balance')->where('branch_id', $branch_id)->whereDate('entry_date', $date) /*->orderBy('entry_date', 'desc')*/->first();
        if ($microLoanRes) {
            $loanDayBookAmount = (int) $microLoanRes->loan_balance;
            $microDayBookAmount = (int) $microLoanRes->balance;
        } else {
            $microLoan = BranchCash::select('balance', 'loan_balance')->where('branch_id', $branch_id)->whereDate('entry_date', '<', $date)->orderby('entry_date', 'DESC')
                ->first();
            $loanDayBookAmount = (int) $microLoan->loan_balance;
            $microDayBookAmount = (int) $microLoan->balance;
        }
        $return_array = compact('microDayBookAmount', 'loanDayBookAmount');
        return json_encode($return_array);
    }
    public function loanClosing(Request $request)
    {
        $branch = \App\Models\Branch::select(['id','state_id'])->where('id', $request->branch_id)->first();
        $systemDate = headerMonthAvailability(date('d'),date('m'),date('Y'),$branch->state_id);
        $globaldate = date('Y-m-d', strtotime(convertdate($systemDate)));

        $checkSSBBalanceDeposit = \App\Models\SavingAccountTransactionView::where('saving_account_id', $request->ssb_id)->whereDate('opening_date', '<=', $globaldate)->sum('deposit');
        $checkSSBBalanceWithdrawal = \App\Models\SavingAccountTransactionView::where('saving_account_id', $request->ssb_id)->whereDate('opening_date', '<=', $globaldate)->sum('withdrawal');
        $ssbBalanceAmount = $checkSSBBalanceDeposit - $checkSSBBalanceWithdrawal;

        if($ssbBalanceAmount < 0){
            return response()->json([
                'error' => true,
                'message' => 'SSB balance amount is less than 0.'
            ]);
        }
        // dd($globaldate, $checkSSBBalanceDeposit, $checkSSBBalanceWithdrawal, $ssbBalanceAmount);

        $loanId = $request->loan_id;
        $date = $request->created_at;
        $mlResult = Memberloans::find($loanId);
        $lData['status'] = 3;
        $lData['clear_date'] = date("Y-m-d", strtotime(convertDate($date)));
        $mlResult->update($lData);

        $loanData = getLoanDetail($request->loan_id);
        $t = getLoanData($loanData->loan_type);


        $logdata = [
            'loanId' => $loanId,
            'loan_type' => $loanData->loan_type,
            'loan_category' => $t->loan_category,
            'loan_name' => $t->name,
            'status' => 3,
            'title' => 'Loan Closed',
            'description' => 'Loan Closed',
            'status_changed_date' => date("Y-m-d", strtotime(convertDate($date))),
            'created_by' => Auth::user()->id,
            'user_name' => Auth::user()->username,
            'created_by_name' => 'Admin',
        ];

        // dd($logdata);
        \App\Models\LoanLog::create($logdata);

        $return_array = compact('loanId');
        return json_encode($return_array);
    }
    public function groupLoanClosing(Request $request)
    {
        $branch = \App\Models\Branch::select(['id','state_id'])->where('id', $request->branch_id)->first();
        // dd( $request->all());
        $systemDate = headerMonthAvailability(date('d'),date('m'),date('Y'),$branch->state_id);
        $globaldate = date('Y-m-d', strtotime(convertdate($systemDate)));

        $checkSSBBalanceDeposit = \App\Models\SavingAccountTransactionView::where('saving_account_id', $request->ssb_id)->whereDate('opening_date', '<=', $globaldate)->sum('deposit');
        $checkSSBBalanceWithdrawal = \App\Models\SavingAccountTransactionView::where('saving_account_id', $request->ssb_id)->whereDate('opening_date', '<=', $globaldate)->sum('withdrawal');
        $ssbBalanceAmount = $checkSSBBalanceDeposit - $checkSSBBalanceWithdrawal;
        // dd($ssbBalanceAmount);
        if($ssbBalanceAmount < 0){
            return response()->json([
                'error' => true,
                'message' => 'SSB balance amount is less than 0.'
            ]);
        }
        $loanId = $request->loan_id;
        $date = $request->created_at;
        $mlResult = Grouploans::find($loanId);
        $lData['status'] = 3;
        $lData['clear_date'] = date("Y-m-d", strtotime(convertDate($date)));
        $mlResult->update($lData);


        $loanData = getLoanDetail($request->loan_id);
        $t = getLoanData($loanData->loan_type);


        $logdata = [
            'loanId' => $loanId,
            'loan_type' => $loanData->loan_type,
            'loan_category' => $t->loan_category,
            'loan_name' => $t->name,
            'status' => 3,
            'title' => 'Group Loan Closed',
            'description' => 'Loan Closed',
            'status_changed_date' => date("Y-m-d", strtotime(convertDate($date))),
            'created_by' => Auth::user()->id,
            'user_name' => Auth::user()->username,
            'created_by_name' => 'Admin',
        ];

        // dd($logdata);
        \App\Models\LoanLog::create($logdata);
        $return_array = compact('loanId');
        return json_encode($return_array);
    }
    public static function createBranchDayBook($daybook_ref_id, $branch_id, $type, $sub_type, $type_id, $type_transaction_id, $associate_id, $member_id, $branch_id_to, $branch_id_from, $opening_balance, $amount, $closing_balance, $description, $description_dr, $description_cr, $payment_type, $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $is_contra, $contra_id, $created_at, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from)
    {
        $globaldate = Session::get('created_at');
        $t = date("H:i:s");
        $data['daybook_ref_id'] = $daybook_ref_id;
        $data['branch_id'] = $branch_id;
        $data['type'] = $type;
        $data['sub_type'] = $sub_type;
        $data['type_id'] = $type_id;
        $data['type_transaction_id'] = $type_transaction_id;
        $data['associate_id'] = $associate_id;
        $data['member_id'] = $member_id;
        $data['branch_id_to'] = $branch_id_to;
        $data['branch_id_from'] = $branch_id_from;
        $data['opening_balance'] = $opening_balance;
        $data['amount'] = $amount;
        $data['closing_balance'] = $closing_balance;
        $data['description'] = $description;
        $data['description_dr'] = $description_dr;
        $data['description_cr'] = $description_cr;
        $data['payment_type'] = $payment_type;
        $data['payment_mode'] = $payment_mode;
        $data['currency_code'] = $currency_code;
        $data['amount_to_id'] = $amount_to_id;
        $data['amount_to_name'] = $amount_to_name;
        $data['amount_from_id'] = $amount_from_id;
        $data['amount_from_name'] = $amount_from_name;
        $data['v_no'] = $v_no;
        $data['v_date'] = $v_date;
        $data['ssb_account_id_from'] = $ssb_account_id_from;
        $data['cheque_no'] = $cheque_no;
        $data['cheque_date'] = $cheque_date;
        $data['cheque_bank_from'] = $cheque_bank_from;
        $data['cheque_bank_ac_from'] = $cheque_bank_ac_from;
        $data['cheque_bank_ifsc_from'] = $cheque_bank_ifsc_from;
        $data['cheque_bank_branch_from'] = $cheque_bank_branch_from;
        $data['cheque_bank_to'] = $cheque_bank_to;
        $data['cheque_bank_ac_to'] = $cheque_bank_to;
        $data['transction_no'] = $transction_no;
        $data['transction_bank_from'] = $transction_bank_from;
        $data['transction_bank_ac_from'] = $transction_bank_ac_from;
        $data['transction_bank_ifsc_from'] = $transction_bank_ifsc_from;
        $data['transction_bank_branch_from'] = $transction_bank_branch_from;
        $data['transction_bank_to'] = $transction_bank_to;
        $data['transction_bank_ac_to'] = $transction_bank_ac_to;
        $data['transction_date'] = date("Y-m-d", strtotime(convertDate($globaldate)));
        $data['entry_date'] = date("Y-m-d", strtotime(convertDate($globaldate)));
        $data['entry_time'] = date("H:i:s");
        $data['created_by'] = $created_by;
        $data['created_by_id'] = $created_by_id;
        $data['is_contra'] = $is_contra;
        $data['contra_id'] = $contra_id;
        $data['ssb_account_id_to'] = $ssb_account_id_to;
        $data['ssb_account_tran_id_to'] = $ssb_account_tran_id_to;
        $data['ssb_account_tran_id_from'] = $ssb_account_tran_id_from;
        $data['created_at'] = date("Y-m-d " . $t . "", strtotime(convertDate($globaldate)));
        $transcation = \App\Models\BranchDaybook::create($data);
        return true;
    }
    public static function createAllTransaction($daybook_ref_id, $branch_id, $bank_id, $bank_ac_id, $head1, $head2, $head3, $head4, $head5, $type, $sub_type, $type_id, $type_transaction_id, $associate_id, $member_id, $branch_id_to, $branch_id_from, $opening_balance, $amount, $closing_balance, $description, $payment_type, $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from)
    {
        $globaldate = Session::get('created_at');
        $t = date("H:i:s");
        $data['daybook_ref_id'] = $daybook_ref_id;
        $data['branch_id'] = $branch_id;
        $data['bank_id'] = $bank_id;
        $data['bank_ac_id'] = $bank_ac_id;
        $data['head1'] = $head1;
        $data['head2'] = $head2;
        $data['head3'] = $head3;
        $data['head4'] = $head4;
        $data['head5'] = $head5;
        $data['type'] = $type;
        $data['sub_type'] = $sub_type;
        $data['type_id'] = $type_id;
        $data['type_transaction_id'] = $type_transaction_id;
        $data['associate_id'] = $associate_id;
        $data['member_id'] = $member_id;
        $data['branch_id_to'] = $branch_id_to;
        $data['branch_id_from'] = $branch_id_from;
        $data['opening_balance'] = $opening_balance;
        $data['amount'] = $amount;
        $data['closing_balance'] = $closing_balance;
        $data['description'] = $description;
        $data['payment_type'] = $payment_type;
        $data['payment_mode'] = $payment_mode;
        $data['currency_code'] = $currency_code;
        $data['amount_to_id'] = $amount_to_id;
        $data['amount_to_name'] = $amount_to_name;
        $data['amount_from_id'] = $amount_from_id;
        $data['amount_from_name'] = $amount_from_name;
        $data['v_no'] = $v_no;
        $data['v_date'] = $v_date;
        $data['ssb_account_id_from'] = $ssb_account_id_from;
        $data['cheque_no'] = $cheque_no;
        $data['cheque_date'] = $cheque_date;
        $data['cheque_bank_from'] = $cheque_bank_from;
        $data['cheque_bank_ac_from'] = $cheque_bank_ac_from;
        $data['cheque_bank_ifsc_from'] = $cheque_bank_ifsc_from;
        $data['cheque_bank_branch_from'] = $cheque_bank_branch_from;
        $data['cheque_bank_to'] = $cheque_bank_to;
        $data['cheque_bank_ac_to'] = $cheque_bank_ac_to;
        $data['transction_no'] = $transction_no;
        $data['transction_bank_from'] = $transction_bank_from;
        $data['transction_bank_ac_from'] = $transction_bank_ac_from;
        $data['transction_bank_ifsc_from'] = $transction_bank_ifsc_from;
        $data['transction_bank_branch_from'] = $transction_bank_branch_from;
        $data['transction_bank_to'] = $transction_bank_to;
        $data['transction_bank_ac_to'] = $transction_bank_ac_to;
        $data['transction_date'] = $transction_date;
        $data['entry_date'] = date("Y-m-d", strtotime(convertDate($globaldate)));
        $data['entry_time'] = date("H:i:s");
        $data['created_by'] = $created_by;
        $data['created_by_id'] = $created_by_id;
        $data['ssb_account_id_to'] = $ssb_account_id_to;
        $data['ssb_account_tran_id_to'] = $ssb_account_tran_id_to;
        $data['ssb_account_tran_id_from'] = $ssb_account_tran_id_from;
        $data['created_at'] = date("Y-m-d " . $t . "", strtotime(convertDate($globaldate)));
        $transcation = \App\Models\AllTransaction::create($data);
        return true;
    }
    public static function createAllHeadTransaction($daybook_ref_id, $branch_id, $bank_id, $bank_ac_id, $head_id, $type, $sub_type, $type_id, $type_transaction_id, $associate_id, $member_id, $branch_id_to, $branch_id_from, $opening_balance, $amount, $closing_balance, $description, $payment_type, $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $jv_unique_id, $v_no, $v_date, $ssb_account_id_from, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_from_id, $cheque_bank_ac_from_id, $cheque_bank_to, $cheque_bank_ac_to, $cheque_bank_to_name, $cheque_bank_to_branch, $cheque_bank_to_ac_no, $cheque_bank_to_ifsc, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_from_id, $transction_bank_from_ac_id, $transction_bank_to, $transction_bank_ac_to, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc, $transction_date, $created_by, $created_by_id)
    {
        $globaldate = Session::get('created_at');
        $t = date("H:i:s");
        $data['daybook_ref_id'] = $daybook_ref_id;
        $data['branch_id'] = $branch_id;
        $data['bank_id'] = $bank_id;
        $data['bank_ac_id'] = $bank_ac_id;
        $data['head_id'] = $head_id;
        $data['type'] = $type;
        $data['sub_type'] = $sub_type;
        $data['type_id'] = $type_id;
        $data['type_transaction_id'] = $type_transaction_id;
        $data['associate_id'] = $associate_id;
        $data['member_id'] = $member_id;
        $data['branch_id_to'] = $branch_id_to;
        $data['branch_id_from'] = $branch_id_from;
        $data['opening_balance'] = $opening_balance;
        $data['amount'] = $amount;
        $data['closing_balance'] = $closing_balance;
        $data['description'] = $description;
        $data['payment_type'] = $payment_type;
        $data['payment_mode'] = $payment_mode;
        $data['currency_code'] = $currency_code;
        $data['amount_to_id'] = $amount_to_id;
        $data['amount_to_name'] = $amount_to_name;
        $data['amount_from_id'] = $amount_from_id;
        $data['amount_from_name'] = $amount_from_name;
        $data['jv_unique_id'] = $jv_unique_id;
        $data['v_no'] = $v_no;
        $data['v_date'] = $v_date;
        $data['ssb_account_id_from'] = $ssb_account_id_from;
        $data['ssb_account_id_to'] = $ssb_account_id_to;
        $data['ssb_account_tran_id_to'] = $ssb_account_tran_id_to;
        $data['ssb_account_tran_id_from'] = $ssb_account_tran_id_from;
        $data['cheque_type'] = $cheque_type;
        $data['cheque_id'] = $cheque_id;
        $data['cheque_no'] = $cheque_no;
        $data['cheque_date'] = $cheque_date;
        $data['cheque_bank_from'] = $cheque_bank_from;
        $data['cheque_bank_ac_from'] = $cheque_bank_ac_from;
        $data['cheque_bank_ifsc_from'] = $cheque_bank_ifsc_from;
        $data['cheque_bank_branch_from'] = $cheque_bank_branch_from;
        $data['cheque_bank_from_id'] = $cheque_bank_from_id;
        $data['cheque_bank_ac_from_id'] = $cheque_bank_ac_from_id;
        $data['cheque_bank_to'] = $cheque_bank_to;
        $data['cheque_bank_ac_to'] = $cheque_bank_ac_to;
        $data['cheque_bank_to_name'] = $cheque_bank_to_name;
        $data['cheque_bank_to_branch'] = $cheque_bank_to_branch;
        $data['cheque_bank_to_ac_no'] = $cheque_bank_to_ac_no;
        $data['cheque_bank_to_ifsc'] = $cheque_bank_to_ifsc;
        $data['transction_no'] = $transction_no;
        $data['transction_bank_from'] = $transction_bank_from;
        $data['transction_bank_ac_from'] = $transction_bank_ac_from;
        $data['transction_bank_ifsc_from'] = $transction_bank_ifsc_from;
        $data['transction_bank_branch_from'] = $transction_bank_branch_from;
        $data['transction_bank_from_id'] = $transction_bank_from_id;
        $data['transction_bank_from_ac_id'] = $transction_bank_from_ac_id;
        $data['transction_bank_to'] = $transction_bank_to;
        $data['transction_bank_ac_to'] = $transction_bank_ac_to;
        $data['transction_bank_to_name'] = $transction_bank_to_name;
        $data['transction_bank_to_ac_no'] = $transction_bank_to_ac_no;
        $data['transction_bank_to_branch'] = $transction_bank_to_branch;
        $data['transction_bank_to_ifsc'] = $transction_bank_to_ifsc;
        $data['transction_date'] = $transction_date;
        $data['entry_date'] = date("Y-m-d", strtotime(convertDate($globaldate)));
        $data['entry_time'] = date("H:i:s");
        $data['created_by'] = $created_by;
        $data['created_by_id'] = $created_by_id;
        $data['created_at'] = date("Y-m-d " . $t . "", strtotime(convertDate($globaldate)));
        $transcation = \App\Models\AllHeadTransaction::create($data);
        return true;
    }
    public static function createMemberTransaction($daybook_ref_id, $type, $sub_type, $type_id, $type_transaction_id, $associate_id, $member_id, $branch_id, $bank_id, $account_id, $amount, $description, $payment_type, $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from)
    {
        $globaldate = Session::get('created_at');
        $t = date("H:i:s");
        $data['daybook_ref_id'] = $daybook_ref_id;
        $data['type'] = $type;
        $data['sub_type'] = $sub_type;
        $data['type_id'] = $type_id;
        $data['type_transaction_id'] = $type_transaction_id;
        $data['associate_id'] = $associate_id;
        $data['member_id'] = $member_id;
        $data['branch_id'] = $branch_id;
        $data['bank_id'] = $bank_id;
        $data['account_id'] = $account_id;
        $data['amount'] = $amount;
        $data['description'] = $description;
        $data['payment_type'] = $payment_type;
        $data['payment_mode'] = $payment_mode;
        $data['currency_code'] = $currency_code;
        $data['amount_to_id'] = $amount_to_id;
        $data['amount_to_name'] = $amount_to_name;
        $data['amount_from_id'] = $amount_from_id;
        $data['amount_from_name'] = $amount_from_name;
        $data['v_no'] = $v_no;
        $data['v_date'] = $v_date;
        $data['ssb_account_id_from'] = $ssb_account_id_from;
        $data['cheque_no'] = $cheque_no;
        $data['cheque_date'] = $cheque_date;
        $data['cheque_bank_from'] = $cheque_bank_from;
        $data['cheque_bank_ac_from'] = $cheque_bank_ac_from;
        $data['cheque_bank_ifsc_from'] = $cheque_bank_ifsc_from;
        $data['cheque_bank_branch_from'] = $cheque_bank_branch_from;
        $data['cheque_bank_to'] = $cheque_bank_to;
        $data['cheque_bank_ac_to'] = $cheque_bank_ac_to;
        $data['transction_no'] = $transction_no;
        $data['transction_bank_from'] = $transction_bank_from;
        $data['transction_bank_ac_from'] = $transction_bank_ac_from;
        $data['transction_bank_ifsc_from'] = $transction_bank_ifsc_from;
        $data['transction_bank_branch_from'] = $transction_bank_branch_from;
        $data['transction_bank_to'] = $transction_bank_to;
        $data['transction_bank_ac_to'] = $transction_bank_ac_to;
        $data['transction_date'] = date("Y-m-d", strtotime(convertDate($globaldate)));
        $data['entry_date'] = date("Y-m-d", strtotime(convertDate($globaldate)));
        $data['entry_time'] = date("H:i:s");
        $data['created_by'] = $created_by;
        $data['created_by_id'] = $created_by_id;
        $data['ssb_account_id_to'] = $ssb_account_id_to;
        $data['ssb_account_tran_id_to'] = $ssb_account_tran_id_to;
        $data['ssb_account_tran_id_from'] = $ssb_account_tran_id_from;
        $data['created_at'] = date("Y-m-d " . $t . "", strtotime(convertDate($globaldate)));
        $transcation = \App\Models\MemberTransaction::create($data);
        return true;
    }
    public function emideleteForm(Request $request)
    {
        $data['title'] = 'Delete Loan EMI';
        return view('templates.admin.loan.delete_emi_form', $data);
    }
    public function emilist(Request $request)
    {
        if ($request->is_search == 'yes') {
            $id = Memberloans::where('account_number', $request->account_number)
                ->first();
            if ($id) {
                $record = LoanDayBooks::where('loan_type', '!=', 3)->where('loan_sub_type', 0)
                    ->where('loan_id', $id->id) /*->where('payment_mode',0)*/
                    ->orderBy('created_at', 'desc')
                    ->where('is_deleted', 0)
                    ->get();
                if (count($record) > 0) {
                    $status = 1;
                    return \Response::json(['view' => view('templates.admin.loan.emi_list', ['record' => $record])->render(), 'msg_type' => 'success']);
                } else {
                    return response()
                        ->json(['msg_type' => 'error']);
                }
            } else {
                $groupId = Grouploans::where('account_number', $request->account_number)
                    ->first();
                if ($groupId) {
                    $record = LoanDayBooks::where('loan_type', 3)->where('loan_sub_type', 0)
                        ->where('is_deleted', 0) /*->where('payment_mode',0)*/
                        ->where('loan_id', $groupId->id)
                        ->orderBy('id', 'desc')
                        ->get();
                    if (count($record) > 0) {
                        $status = 1;
                        return \Response::json(['view' => view('templates.admin.loan.emi_list', ['record' => $record])->render(), 'msg_type' => 'success']);
                    } else {
                        return response()
                            ->json(['msg_type' => 'error']);
                    }
                } else {
                    return redirect()
                        ->back()
                        ->with('success', 'Loan EMI not generated!');
                }
            }
        }
    }

    public function updateSsbDayBookAmountAfterEmiDelete($amount, $account_number, $date)
    {
        $globaldate = $date;
        $entryTime = date("H:i:s");
        $entryDate = date("Y-m-d", strtotime(convertDate($date)));
        $getCurrentBranchRecord = SavingAccountTranscation::where('account_no', $account_number)->whereDate('created_at', $entryDate)->first();
        //dd($getCurrentBranchRecord);die();
        $bResult = SavingAccountTranscation::find($getCurrentBranchRecord->id);
        $bData['opening_balance'] = $getCurrentBranchRecord->opening_balance + $amount;
        $bData['updated_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($globaldate)));
        $bResult->update($bData);
        $getNextBranchRecord = SavingAccountTranscation::where('account_no', $account_number)->whereDate('created_at', '>', $entryDate)->orderby('created_at', 'ASC')
            ->get();
        if ($getNextBranchRecord) {
            foreach ($getNextBranchRecord as $key => $value) {
                $sResult = SavingAccountTranscation::find($value->id);
                $sData['opening_balance'] = $value->opening_balance + $amount;
                $sData['updated_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($globaldate)));
                $sResult->update($sData);
            }
        }
    }
    public function updateBranchCashFromBackDateAfterDeleteTransaction($amount, $branch_id, $ftdate)
    {
        $globaldate = $ftdate;
        $entryTime = date("H:i:s");
        $entryDate = date("Y-m-d", strtotime(convertDate($globaldate)));
        $getCurrentBranchRecord = \App\Models\BranchCash::where('branch_id', $branch_id)->whereDate('entry_date', $entryDate)->first();
        if ($getCurrentBranchRecord) {
            $bResult = \App\Models\BranchCash::find($getCurrentBranchRecord->id);
            $bData['balance'] = $getCurrentBranchRecord->balance - $amount;
            if ($getCurrentBranchRecord->closing_balance > 0) {
                $bData['closing_balance'] = $getCurrentBranchRecord->closing_balance - $amount;
            }
            //$bData['updated_at']=date("Y-m-d ".$entryTime."", strtotime(convertDate($globaldate)));
            $bResult->update($bData);
            //dd($bData,$amount);
            $getNextBranchRecord = \App\Models\BranchCash::where('branch_id', $branch_id)->whereDate('entry_date', '>', $entryDate)->orderby('entry_date', 'ASC')
                ->get();
            if ($getNextBranchRecord) {
                foreach ($getNextBranchRecord as $key => $value) {
                    $sResult = \App\Models\BranchCash::find($value->id);
                    $sData['opening_balance'] = $value->closing_balance;
                    $sData['balance'] = $value->balance - $amount;
                    if ($value->closing_balance > 0) {
                        $sData['closing_balance'] = $value->closing_balance - $amount;
                    }
                    $sData['updated_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($globaldate)));
                    $sResult->update($sData);
                }
            }
        }
        $getCurrentBranchClosingRecord = \App\Models\BranchClosing::where('branch_id', $branch_id)->whereDate('entry_date', $entryDate)->first();
        if ($getCurrentBranchClosingRecord) {
            $bResult = \App\Models\BranchClosing::find($getCurrentBranchClosingRecord->id);
            $bData['balance'] = $getCurrentBranchClosingRecord->balance - $amount;
            if ($getCurrentBranchClosingRecord->closing_balance > 0) {
                $bData['closing_balance'] = $getCurrentBranchClosingRecord->closing_balance - $amount;
            }
            $bData['updated_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($globaldate)));
            $bResult->update($bData);
            $getNextBranchClosingRecord = \App\Models\BranchClosing::where('branch_id', $branch_id)->whereDate('entry_date', '>', $entryDate)->orderby('entry_date', 'ASC')
                ->get();
            if ($getNextBranchClosingRecord) {
                foreach ($getNextBranchClosingRecord as $key => $value) {
                    $sResult = \App\Models\BranchClosing::find($value->id);
                    $sData['opening_balance'] = $value->closing_balance;
                    $sData['balance'] = $value->balance - $amount;
                    if ($value->closing_balance > 0) {
                        $sData['closing_balance'] = $value->closing_balance - $amount;
                    }
                    $sData['updated_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($globaldate)));
                    $sResult->update($sData);
                }
            }
        }
    }

    public static function updateBackDateloanBankBalanceAftrDeleteTransaction($amount, $bank_id, $account_id, $ltdate, $neft)
    {
        $globaldate = $ltdate;
        $entryTime = date("H:i:s");
        $entryDate = date("Y-m-d", strtotime(convertDate($globaldate)));
        $getCurrentFromBankRecord = \App\Models\SamraddhBankClosing::where('bank_id', $bank_id)->where('account_id', $account_id)->whereDate('entry_date', $entryDate)->first();
        if ($getCurrentFromBankRecord) {
            $Result = \App\Models\SamraddhBankClosing::find($getCurrentFromBankRecord->id);
            $data['balance'] = $getCurrentFromBankRecord->balance + ($amount - $neft);
            if ($getCurrentFromBankRecord->closing_balance > 0) {
                $data['closing_balance'] = $getCurrentFromBankRecord->closing_balance + ($amount - $neft);
            }
            $data['updated_at'] = $entryDate;
            $Result->update($data);
            $insertid = $getCurrentFromBankRecord->id;
            $getNextFromBankRecord = \App\Models\SamraddhBankClosing::where('bank_id', $bank_id)->where('account_id', $account_id)->whereDate('entry_date', '>', $entryDate)->orderby('entry_date', 'ASC')
                ->get();
            if ($getNextFromBankRecord) {
                foreach ($getNextFromBankRecord as $key => $value) {
                    $sResult = \App\Models\SamraddhBankClosing::find($value->id);
                    $sData['opening_balance'] = $value->closing_balance;
                    $sdata['balance'] = $value->balance + ($amount - $neft);
                    if ($value->closing_balance > 0) {
                        $sdata['closing_balance'] = $value->closing_balance + ($amount - $neft);
                    }
                    $sdata['updated_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($globaldate)));
                    $sResult->update($sdata);
                }
            }
        } else {
            $oldCurrentFromDateRecord = \App\Models\SamraddhBankClosing::where('bank_id', $bank_id)->where('account_id', $account_id)->whereDate('entry_date', '<', $entryDate)->orderby('entry_date', 'DESC')
                ->first();
            if ($oldCurrentFromDateRecord) {
                $cResult = \App\Models\SamraddhBankClosing::find($oldCurrentFromDateRecord->id);
                //$cdata['loan_closing_balance']=$oldCurrentFromDateRecord->loan_balance;
                $cdata['closing_balance'] = $oldCurrentFromDateRecord->balance;
                $cdata['updated_at'] = $entryDate;
                $cResult->update($cdata);
                $nextRecordExists = \App\Models\SamraddhBankClosing::where('bank_id', $bank_id)->where('account_id', $account_id)->whereDate('entry_date', '>', $entryDate)->orderby('entry_date', 'ASC')
                    ->get();
                $data1['bank_id'] = $oldCurrentFromDateRecord->bank_id;
                $data1['account_id'] = $oldCurrentFromDateRecord->account_id;
                $data1['opening_balance'] = $oldCurrentFromDateRecord->balance;
                $data1['balance'] = $oldCurrentFromDateRecord->balance + ($amount - $neft);
                if ($nextRecordExists) {
                    $data1['closing_balance'] = $oldCurrentFromDateRecord->balance + ($amount - $neft);
                    foreach ($nextRecordExists as $key => $value) {
                        $sResult = \App\Models\SamraddhBankClosing::find($value->id);
                        $sData['opening_balance'] = $value->closing_balance;
                        $sdata['balance'] = $value->balance + ($amount - $neft);
                        if ($value->closing_balance > 0) {
                            $sdata['closing_balance'] = $value->closing_balance + ($amount - $neft);
                        }
                        $sdata['updated_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($globaldate)));
                        $sResult->update($sdata);
                    }
                } else {
                    $data1['closing_balance'] = 0;
                }
                $data1['entry_date'] = $entryDate;
                $data1['entry_time'] = $entryTime;
                $data1['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($globaldate)));
                $transcation = \App\Models\SamraddhBankClosing::create($data1);
                $insertid = $transcation->id;
            } else {
                $data2['bank_id'] = 0;
                $data1['account_id'] = $account_id;
                $data2['opening_balance'] = 0;
                $data2['balance'] = -($amount + $neft);
                $data2['closing_balance'] = 0;
                $data2['entry_date'] = $entryDate;
                $data2['entry_time'] = $entryTime;
                $data2['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($globaldate)));
                $transcation = \App\Models\SamraddhBankClosing::create($data2);
                $insertid = $transcation->id;
            }
        }
        return true;
    }
    public function getBranchApprovedCheque(Request $request)
    {
        $cheque = ReceivedCheque::whereBranchId($request->branch_id)
            ->whereCompanyId($request->companyId)
            ->where('status', 2)
            ->get();
        return response()
            ->json($cheque);
    }
    public function outstanding_report(Request $request)
    {
        $reportName = $request->segment(3);
        if (check_my_permission(Auth::user()->id, "281") != "1") {
            return redirect()->route('admin.dashboard');
        }
        $data['title'] = 'Loan Outstanding';
        return view('templates.admin.loan.report', $data);
    }


    public function LoanoutstandingDuereport(Request $request)
    {
        if ($request->ajax()) {
            $arrFormData = array();
            if (!empty($_POST['searchform'])) {
                foreach ($_POST['searchform'] as $frm_data) {
                    $arrFormData[$frm_data['name']] = $frm_data['value'];
                }
            }
            $startDate = !empty($arrFormData['from_date']) ? date("Y-m-d", strtotime(convertDate($arrFormData['from_date']))) : '';
            $endDate = !empty($arrFormData['to_date']) ? date("Y-m-d", strtotime(convertDate($arrFormData['to_date']))) : '';
            $memberId = $arrFormData['member_id'] ?? '';
            $customerId = $arrFormData['customer_id'] ?? '';
            $branchId = $arrFormData['branch_id'] != 0 ? $arrFormData['branch_id'] : '';
            $companyId = $arrFormData['company_id'] != 0 ? $arrFormData['company_id'] : '';
            $accountNumber = $arrFormData['loan_account_number'] ?? '';
            $groupCommonId = $arrFormData['group_loan_common_id'] ?? '';
            $emiOption = $arrFormData['emi_option'] ?? '';
            $loant = $arrFormData['loan_type'] ?? '';
            $loanPlan = $arrFormData['loan_plan'] ?? '';
            if ($arrFormData['loan_type'] == 'L') {
                $data = Memberloans::with(['loanMember', 'loanMemberAssociate', 'loanBranch', 'loanTransaction', 'loanTransactionSumLoanDaybook', 'loanMemberCompany'])->with(['loanMemberCustom'])->with(['GroupLoanMembers'])
                    ->with([
                        'loan' => function ($q) {
                            $q->select('id', 'name', 'loan_type');
                        }
                    ])
                    ->with([
                        'loanMember' => function ($q) {
                            $q->select('id', 'member_id', 'first_name', 'last_name');
                        }
                    ])
                    ->with([
                        'loanMemberAssociate' => function ($q) {
                            $q->select('id', 'associate_no', 'first_name', 'last_name');
                        }
                    ])
                    ->with([
                        'loanBranch' => function ($query) {
                            $query->select('id', 'name', 'branch_code', 'sector', 'regan', 'zone', 'state_id');
                        }
                    ])
                    ->with([
                        'getOutstanding' => function ($q) {
                            $q->select('loan_id', 'loan_type', 'out_standing_amount', 'emi_date')->where('is_deleted', '0');
                        },
                        'company' => function ($q) {
                            $q->select('id', 'name');
                        }
                    ])
                    ->whereIn('status', [4])->whereNotNull('approve_date');
                ;
            } else {
                $data = Grouploans::with([
                    'loanMember' => function ($q) {
                        $q->select('id', 'member_id', 'first_name', 'last_name');
                    },
                    'loanMemberAssociate' => function ($q) {
                        $q->select('id', 'member_id', 'first_name', 'last_name');
                    },
                    'loanBranch' => function ($q) {
                        $q->select('id', 'branch_code', 'name', 'state_id', 'state_id');
                    },
                    'loanTransactionSumLoanDaybook'
                ])
                    ->with([
                        'loan' => function ($q) {
                            $q->select('id', 'name', 'loan_type');
                        },
                        'loanMemberCompanyid'
                    ])
                    ->with([
                        'getOutstanding' => function ($q) {
                            $q->select('loan_id', 'loan_type', 'out_standing_amount', 'emi_date')->where('is_deleted', '0');
                        },
                        'company' => function ($q) {
                            $q->select('id', 'name');
                        }
                    ])
                    ->whereNotNull('approve_date')->whereIn('status', [4]);
                ;
            }
            $loanTypeCloser = function ($q) use ($loant) {
                $q->where('loans.loan_type', '=', $loant);
            };
            $companyClosure = function ($q) use ($companyId) {
                $q->where('company_id', $companyId);
            };
            $memberClosure = function ($q) use ($memberId) {
                $q->whereHas('loanMemberCompany', function ($query) use ($memberId) {
                    $query->where('member_companies.member_id', 'LIKE', '%' . $memberId . '%');
                });
            };
            $customerClosure = function ($q) use ($customerId) {
                $q->whereHas('loanMember', function ($query) use ($customerId) {
                    $query->where('members.member_id', 'LIKE', '%' . $customerId . '%');
                });
            };
            $branchClosure = function ($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            };
            $accountNumberClosure = function ($q) use ($accountNumber) {
                $q->where('account_number', $accountNumber);
            };
            $loanPlanClosure = function ($q) use ($loanPlan) {
                $q->where('loan_type', $loanPlan);
            };
            $emiOptionClosure = function ($q) use ($emiOption) {
                $q->where('emi_option', $emiOption);
            };
            $dateClosure = function ($q) use ($startDate, $endDate) {
                $q->when($endDate != '', function ($q) use ($startDate, $endDate) {
                    $q->whereBetween('approve_date', [$startDate, $endDate]);
                });
            };
            $groupCommonIdClosuer = function ($q) use ($groupCommonId) {
                $q->where('group_loan_common_id', $groupCommonId);
            };
            $data = $data->whereHas('loan', $loanTypeCloser);
            $data = $data->when($startDate != '', $dateClosure)
                ->when($companyId != '', $companyClosure)
                ->when($branchId != '', $branchClosure)
                ->when($accountNumber != '', $accountNumberClosure)
                ->when($memberId != '', $memberClosure)
                ->when($customerId != '', $customerClosure)
                ->when($groupCommonId != '', $groupCommonIdClosuer)
                ->when($loanPlan != '', $loanPlanClosure)
                ->when($emiOption != '', $emiOptionClosure);
            $datas = $data->count('id');
            $data = $data->orderby('id', 'desc')->offset($_POST['start'])->limit($_POST['length'])->get();
            $count = $datas;
            $totalCount = $count;
            $sno = $_POST['start'];
            $rowReturn = array();
            foreach ($data as $row) {

                $created_date = date('d/m/Y', strtotime($row->approve_date));
                if($row->emi_option == 3){
                    $created_date = date('d/m/Y', strtotime('+1 days'.$row->approve_date));
                }
                $sno++;
                $val['DT_RowIndex'] = $sno;
                $val['company_name'] = $row['company']->name;
                $val['created_date'] = $created_date;
                $val['branch'] = $row['loanBranch']->name;
                $val['branch_code'] = $row['loanBranch']->branch_code;
                $url = URL::to("admin/loan/emi-transactions/" . $row->id . "/" . $row->loan_type . "");
                $btn = '<a class=" " href="' . $url . '" title="View Statement" target="_blank">' . $row->account_number . '</a>';
                $val['account_number'] = $btn;
                $val['group_loan_common_id'] = $row['loan']->loan_type == "G" ? $row->group_loan_common_id : '';
                $val['member_name'] = $row['loanMember']->first_name . ' ' . $row['loanMember']->last_name;
                $val['customer_id'] = $row['loanMember']->member_id ?? '';
                $val['member_id'] = $row['loan']->loan_type == "G" ? $row['loanMemberCompanyid']->member_id ?? '' : $row['loanMemberCompany']->member_id ?? '';
                switch ($row->emi_option) {
                    case 1:
                        $emitYPE = 'Monthly';
                        $EMI = 'Month';
                        break;
                    case 2:
                        $emitYPE = 'Weekly';
                        $EMI = 'Week';
                        break;
                    case 3:
                        $emitYPE = 'Daily';
                        $EMI = 'Days';
                        break;
                    default:
                        $emitYPE = 'N/A';
                        $EMI = '';
                        break;
                }
                $val['emi_type'] = $emitYPE;
                $val['emi_period'] = $row->emi_period . ' ' . $EMI ?? '';
                $plan_name = 'N/A';
                $plan_name = $row['loan']->name;
                $val['loan_type'] = $plan_name;
                switch ($row->emi_option) {
                    case 1:
                        $tenure = $row->emi_period . ' Months';
                        break;
                    case 2:
                        $tenure = $row->emi_period . ' Weeks';
                        break;
                    case 3:
                        $tenure = $row->emi_period . ' Days';
                        break;
                }
                $val['tenure'] = $tenure;
                // $val['roi'] = $row->ROI . ' %'; // as per discussed with Sachin sir (client) this column is removed by sourab
                $val['loan_amount'] = $row->amount . ' <i class="fas fa-rupee-sign"></i>';
                $val['total_deposit'] = $row['loanTransactionSumLoanDaybook']->sum('deposit') . ' <i class="fas fa-rupee-sign"></i>';
                $totalbalance = $row->emi_period * $row->emi_amount;
                $Finaloutstanding_amount = $totalbalance - $row->received_emi_amount;

                if ($request->title != 'Loan Outstanding') {
                    $val['total_due_amount'] = $Finaloutstanding_amount . ' <i class="fa fa-inr"></i>';
                    $val['no_of_due_emi'] = 0 . ' <i class="fa fa-inr"></i>';
                    $val['total_deposite_emi'] = $row->received_emi_amount . ' <i class="fa fa-inr"></i>';
                    $val['no_of_deposite_emi'] = 0 . ' <i class="fa fa-inr"></i>';
                    $val['outstanding_amount'] = $row->emi_amount . ' <i class="fa fa-inr"></i>';
                } else {
                    $outstandingAmount = isset($row['getOutstanding']->out_standing_amount)
                        ? ($row['getOutstanding']->out_standing_amount > 0 ? $row['getOutstanding']->out_standing_amount : 0)
                        : $row->amount;
                    $lastEmidate = isset($row['getOutstanding']->emi_date) ? date('d/m/Y', strtotime($row['getOutstanding']->emi_date)) : date('d/m/Y', strtotime($row->approve_date));
                    $closerAmount = calculateCloserAmount($outstandingAmount, $lastEmidate, $row->ROI, $row['loanBranch']->state_id);

                    $val['outstanding_amount'] = $closerAmount;
                }
                // }
                $url2 = URL::to("admin/loan/deposit/emi-transactions/" . $row->id . "/" . $row->loan_type . "");
                $eurl = URL::to("admin/loan/repayment_chart/" . $row->member_loan_id . "");
                $btn = '<div class="list-icons"><div class="dropdown"><a href="#" class="list-icons-item" data-toggle="dropdown"><i class="icon-menu9"></i></a><div class="dropdown-menu dropdown-menu-right">';
                $btn .= '<a class="dropdown-item" href="' . $url2 . '"><i class="fas fa-money-bill-wave-alt mr-2" aria-hidden="true"></i>View Deposite Statement</a>';
                $btn .= '<a class="dropdown-item repayment" href="javascript:void(0);" data-toggle="modal" data-target="#exampleModalLong" data-loan-id="' . $row->id . '" data-type = "' . $row->loan_type . '" ><i class="fas fa-percent text-default mr-2"></i>Repayment Chart</a>';
                if ($row->loan_type != 3) {
                    $vurl = URL::to("admin/loan/view/" . $row->id . "/" . $row->loan_type . "");
                } else {
                    $vurl = URL::to("admin/loan/view/" . $row->member_loan_id . "/" . $row->loan_type . "");
                }
                $btn .= '<a class="dropdown-item" href="' . $vurl . '"><i class="fas fa-eye text-default mr-2"></i>View</a>';
                $btn .= '</div></div></div>';
                $val['action'] = $btn;
                $rowReturn[] = $val;
            }
            $output = array(
                "draw" => $_POST['draw'],
                "recordsTotal" => $totalCount,
                "recordsFiltered" => $totalCount,
                "data" => $rowReturn,
            );
            return json_encode($output);
        }
    }

    public function export_Loanoutstandingreport(Request $request)
    {
        if ($request['export'] == 0) {
            $input = $request->all();
            $start = $input["start"];
            $limit = $input["limit"];
            $returnURL = URL::to('/') . "/asset/outstanding.csv";
            $fileName = env('APP_EXPORTURL') . "asset/outstanding.csv";
            global $wpdb;
            $postCols = array(
                'post_title',
                'post_content',
                'post_excerpt',
                'post_name',
            );
            header("Content-type: text/csv");
        }
        $startDate = !empty($request['from_date']) ? date("Y-m-d", strtotime(convertDate($request['from_date']))) : '';
        $endDate = !empty($request['to_date']) ? date("Y-m-d", strtotime(convertDate($request['to_date']))) : '';
        $memberId = $request['member_id'] ?? '';
        $customerId = $request['customer_id'] ?? '';
        $branchId = $request['branch_id'] != '0' ? $request['branch_id'] : '';
        $companyId = $request['company_id'] != '0' ? $request['company_id'] : '';
        $accountNumber = $request['loan_account_number'] ?? '';
        $groupCommonId = $request['group_loan_common_id'] ?? '';
        $emiOption = $request['emi_option'] ?? '';
        $loant = $request['loan_type'] ?? '';
        $loanPlan = $request['loan_plan'] ?? '';
        if ($request['loan_type'] == 'L') {
            $data = Memberloans::with(['loanMember', 'loanMemberAssociate', 'loanBranch', 'loanTransaction', 'loanTransactionSumLoanDaybook', 'loanMemberCompany'])->with(['loanMemberCustom'])->with(['GroupLoanMembers'])
                ->with([
                    'loan' => function ($q) {
                        $q->select('id', 'name', 'loan_type');
                    }
                ])
                ->with([
                    'loanMember' => function ($q) {
                        $q->select('id', 'member_id', 'first_name', 'last_name');
                    }
                ])
                ->with([
                    'loanMemberAssociate' => function ($q) {
                        $q->select('id', 'associate_no', 'first_name', 'last_name');
                    }
                ])
                ->with([
                    'loanBranch' => function ($query) {
                        $query->select('id', 'name', 'branch_code', 'sector', 'regan', 'zone', 'state_id');
                    }
                ])
                ->with([
                    'getOutstanding' => function ($q) {
                        $q->select('loan_id', 'loan_type', 'out_standing_amount', 'emi_date')->where('is_deleted', '0');
                    },
                    'company' => function ($q) {
                        $q->select('id', 'name');
                    }
                ])
                ->whereIn('status', [4])->whereNotNull('approve_date');
            ;
        } else {
            $data = Grouploans::with([
                'loanMember' => function ($q) {
                    $q->select('id', 'member_id', 'first_name', 'last_name');
                },
                'loanMemberAssociate' => function ($q) {
                    $q->select('id', 'member_id', 'first_name', 'last_name');
                },
                'loanBranch' => function ($q) {
                    $q->select('id', 'branch_code', 'name', 'state_id');
                },
                'loanTransactionSumLoanDaybook'
            ])
                ->with([
                    'loan' => function ($q) {
                        $q->select('id', 'name', 'loan_type');
                    },
                    'loanMemberCompanyid'
                ])
                ->with([
                    'getOutstanding' => function ($q) {
                        $q->select('loan_id', 'loan_type', 'out_standing_amount', 'emi_date')->where('is_deleted', '0');
                    },
                    'company' => function ($q) {
                        $q->select('id', 'name');
                    }
                ])
                ->whereNotNull('approve_date')->whereIn('status', [4]);
            ;
        }
        $loanTypeCloser = function ($q) use ($loant) {
            $q->where('loans.loan_type', '=', $loant);
        };
        $companyClosure = function ($q) use ($companyId) {
            $q->where('company_id', $companyId);
        };
        $memberClosure = function ($q) use ($memberId) {
            $q->whereHas('loanMemberCompany', function ($query) use ($memberId) {
                $query->where('member_companies.member_id', 'LIKE', '%' . $memberId . '%');
            });
        };
        $customerClosure = function ($q) use ($customerId) {
            $q->whereHas('loanMember', function ($query) use ($customerId) {
                $query->where('members.member_id', 'LIKE', '%' . $customerId . '%');
            });
        };
        $branchClosure = function ($q) use ($branchId) {
            $q->where('branch_id', $branchId);
        };
        $accountNumberClosure = function ($q) use ($accountNumber) {
            $q->where('account_number', $accountNumber);
        };
        $loanPlanClosure = function ($q) use ($loanPlan) {
            $q->where('loan_type', $loanPlan);
        };
        $emiOptionClosure = function ($q) use ($emiOption) {
            $q->where('emi_option', $emiOption);
        };
        $dateClosure = function ($q) use ($startDate, $endDate) {
            $q->when($endDate != '', function ($q) use ($startDate, $endDate) {
                $q->whereBetween('approve_date', [$startDate, $endDate]);
            });
        };
        $groupCommonIdClosuer = function ($q) use ($groupCommonId) {
            $q->where('group_loan_common_id', $groupCommonId);
        };
        $data = $data->whereHas('loan', $loanTypeCloser);
        $data = $data->when($startDate != '', $dateClosure)
            ->when($companyId != '', $companyClosure)
            ->when($branchId != '', $branchClosure)
            ->when($accountNumber != '', $accountNumberClosure)
            ->when($memberId != '', $memberClosure)
            ->when($customerId != '', $customerClosure)
            ->when($groupCommonId != '', $groupCommonIdClosuer)
            ->when($loanPlan != '', $loanPlanClosure)
            ->when($emiOption != '', $emiOptionClosure);
        if ($request['export'] == 0) {
            $sno = $_POST['start'];
            $totalResults = $data->orderby('id', 'DESC')->count();
            // $results = $data->get()->slice($start,$limit);;
            $results = $data->orderby('id', 'DESC')->offset($start)->limit($limit)->get();

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
                $created_date = date('d/m/Y', strtotime($row->approve_date));
                if($row->emi_option == 3){
                    $created_date = date('d/m/Y', strtotime('+1 days'.$row->approve_date));
                }
                $sno++;
                $sno++;
                $val['Company Name'] = $row['company']->name;
                $val['EMI  DATE'] = $created_date; // date('d/m/Y', strtotime($row->approve_date));
                $val['BRANCH NAME'] = $row['loanBranch']->name;
                $val['BRANCH CODE'] = $row['loanBranch']->branch_code;
                $val['SECTOR'] = $row['loanBranch']->sector;
                $val['REGION'] = $row['loanBranch']->regan;
                $val['ZONE'] = $row['loanBranch']->zone;
                $btn = $row->account_number;
                $val['ACCOUNT NUMBER'] = $btn;
                $val['APPLICANT / GROUP LEADER ID'] = $row['loan']->loan_type == "G" ? $row->group_loan_common_id : '';
                $val['CUSTOMER ID'] = $row['loanMember']->member_id ?? '';
                $val['MEMBER NAME'] = $row['loanMember']->first_name . ' ' . $row['loanMember']->last_name;
                $val['MEMBER ID'] = $row['loan']->loan_type == "G" ? $row['loanMemberCompanyid']->member_id ?? '' : $row['loanMemberCompany']->member_id ?? '';
                $plan_name = 'N/A';
                $plan_name = $row['loan']->name;
                $val['LOAN TYPE'] = $plan_name;
                switch ($row->emi_option) {
                    case 1:
                        $emitYPE = 'Monthly';
                        $EMI = 'Month';
                        break;
                    case 2:
                        $emitYPE = 'Weekly';
                        $EMI = 'Week';
                        break;
                    case 3:
                        $emitYPE = 'Daily';
                        $EMI = 'Days';
                        break;
                    default:
                        $emitYPE = 'N/A';
                        $EMI = '';
                        break;
                }
                $val['EMI TYPE'] = $emitYPE;
                $val['EMI PERIOD'] = $row->emi_period . ' ' . $EMI ?? '';
                switch ($row->emi_option) {
                    case 1:
                        $tenure = $row->emi_period . ' Months';
                        break;
                    case 2:
                        $tenure = $row->emi_period . ' Weeks';
                        break;
                    case 3:
                        $tenure = $row->emi_period . ' Days';
                        break;
                }
                // $val['ROI'] = $row->ROI . ' %';  // as per discussed with Sachin sir (client) this column is removed by
                $val['TOTAL DEPOSIT'] = $row['loanTransactionSumLoanDaybook']->sum('deposit');
                // $val['TENURE'] = $tenure;
                $val['LOAN AMOUNT'] = $row->amount;
                $outstandingAmount = isset($row['getOutstanding']->out_standing_amount)
                    ? ($row['getOutstanding']->out_standing_amount > 0 ? $row['getOutstanding']->out_standing_amount : 0)
                    : $row->amount;
                $lastEmidate = isset($row['getOutstanding']->emi_date) ? date('d/m/Y', strtotime($row['getOutstanding']->emi_date)) : date('d/m/Y', strtotime($row->approve_date));
                $closerAmount = calculateCloserAmount($outstandingAmount, $lastEmidate, $row->ROI, $row['loanBranch']->state_id);
                $val['CLOSURE AMOUNT'] = $closerAmount;
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



    public function repayment_chart(Request $req)
    {
        if ($req->loanType != 3) {
            $laonData = Memberloans::select('id', 'created_at', 'approve_date', 'emi_option', 'emi_period', 'emi_amount', 'amount', 'ROI')->where('id', $req->loanId)->first();
        } else {
            $laonData = Grouploans::select('id', 'created_at', 'approve_date', 'emi_option', 'emi_period', 'emi_amount', 'amount', 'ROI')->where('id', $req->loanId)->first();
        }
        $data = array();
        $dataValue = $this->getOutstandingAmount($laonData->emi_period, $laonData->emi_option, $laonData->approve_date, $laonData->amount, $laonData->ROI, $laonData->emi_amount);
        $totalCount = count($data);
        $sno = 1;
        $rowReturn = array();
        foreach ($dataValue as $i => $value) {
            $val['DT_RowIndex'] = $sno;
            $val['emi_date'] = $value['emi_date'];
            $val['emi_amount'] = number_format((float) $laonData->emi_amount, 2, '.', '');
            $val['roi'] = number_format((float) $value['roi'], 2, '.', '');
            $val['principal_amount'] = number_format((float) $value['principalAmount'], 2, '.', '');
            $val['outstanding'] = number_format((float) $value['outStandingAmount'], 2, '.', '');
            $sno++;
            $rowReturn[] = $val;
        }
        return response()->json($rowReturn);
    }
    public function grp_repayment_chart(Request $req)
    {
        $laonData = Grouploans::select('id', 'created_at', 'approve_date', 'emi_option', 'emi_period', 'emi_amount', 'amount', 'ROI')->where('id', $req->loanId)->first();
        $data = array();
        $dataValue = $this->getOutstandingAmount($laonData->emi_period, $laonData->emi_option, $laonData->approve_date, $laonData->amount, $laonData->ROI, $laonData->emi_amount);
        $totalCount = count($data);
        $sno = 1;
        $rowReturn = array();
        foreach ($dataValue as $i => $value) {
            $val['DT_RowIndex'] = $sno;
            $val['emi_date'] = $value['emi_date'];
            $val['emi_amount'] = number_format((float) $laonData->emi_amount, 2, '.', '');
            $val['roi'] = number_format((float) $value['roi'], 2, '.', '');
            $val['principal_amount'] = number_format((float) $value['principalAmount'], 2, '.', '');
            $val['outstanding'] = number_format((float) $value['outStandingAmount'], 2, '.', '');
            $sno++;
            $rowReturn[] = $val;
        }
        return response()->json($rowReturn);
    }
    public function export_repayment(Request $request)
    {
        $input = $request->all();
        $start = $input["start"];
        $limit = $input["limit"];
        $returnURL = URL::to('/') . "/asset/outstanding.csv";
        $fileName = env('APP_EXPORTURL') . "asset/outstanding.csv";
        global $wpdb;
        $postCols = array(
            'post_title',
            'post_content',
            'post_excerpt',
            'post_name',
        );
        header("Content-type: text/csv");
        if ($request->title != 'Group Loan Outstanding') {
            $laonData = Memberloans::select('id', 'created_at', 'approve_date', 'emi_option', 'emi_period', 'emi_amount', 'amount', 'ROI')->where('id', $request->loanId)->first();
        } else {
            $laonData = Grouploans::select('id', 'created_at', 'approve_date', 'emi_option', 'emi_period', 'emi_amount', 'amount', 'ROI')->where('id', $request->loanId)->first();
        }
        $data = array();
        $dataValue = $this->getOutstandingAmount($laonData->emi_period, $laonData->emi_option, $laonData->approve_date, $laonData->amount, $laonData->ROI, $laonData->emi_amount);
        $totalCount = count($data);
        $sno = 1;
        $rowReturn = array();
        if ($request['export'] == 0) {
            $sno = $_POST['start'];
            $totalResults = count($dataValue);
            $results = $dataValue;
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
            foreach ($dataValue as $i => $value) {
                $val['EMI DATE'] = $value['emi_date'];
                $val['EMI AMOUNT'] = number_format((float) $laonData->emi_amount, 2, '.', '');
                $val['ROI'] = number_format((float) $value['roi'], 2, '.', '');
                $val['PRINCIPAL AMOUNT'] = number_format((float) $value['principalAmount'], 2, '.', '');
                $val['OUTSTANDING AMOUNT'] = number_format((float) $value['outStandingAmount'], 2, '.', '');
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
    }
    public function depositeloanEmiView($id, $type)
    {
        if ($type != 3) {
            // if(!in_array('Loan Transactions', auth()->user()->getPermissionNames()->toArray())){
            //       return redirect()->route('branch.dashboard');
            //       }
            $data['title'] = 'Loan EMI Transactions';
            $data['loanDetails'] = Memberloans::select('loan_type', 'account_number')->where('id', $id)->first();
            //   if($data['loanDetails']->loan_type == 1){
            //       $data['loanTitle'] = 'Personal Loan';
            //   }elseif($data['loanDetails']->loan_type == 2){
            //       $data['loanTitle'] = 'Staff Loan';
            //   }elseif($data['loanDetails']->loan_type == 3){
            //       $data['loanTitle'] = 'Group Loan' ;
            //   }elseif($data['loanDetails']->loan_type == 4){
            //       $data['loanTitle'] = 'Loan Against Investment Plan';
            //   }
            $data['loanTitle'] = $data['loanDetails']->loan->name;
        } else {
            //   if(!in_array('Group Loan Transactions', auth()->user()->getPermissionNames()->toArray())){  //group loan
            //       return redirect()->route('branch.dashboard');
            //       }
            $data['title'] = 'Group Loan EMI Transactions';
            $data['loanDetails'] = Grouploans::select('account_number')->where('id', $id)->first();
            $data['loanTitle'] = 'Group Loan';
        }
        $data['id'] = $id;
        $data['type'] = $type;
        return view('templates.admin.loan.deposite_loan_emi_transaction', $data);
    }
    public function depositLoanTransaction(Request $request)
    {
        if ($request->ajax()) {
            if ($request['loanType'] != 3) {
                $loanRecord = Memberloans::Where('id', $request['loanId'])->first('account_number');
            } else {
                $loanRecord = Grouploans::Where('id', $request['loanId'])->first('account_number');
            }
            $data = LoanDayBooks::where('loan_type', $request['loanType'])->where('loan_id', $request['loanId'])->where('is_deleted', 0);
            $data1 = $data->get();
            $count = count($data1);
            $data = $data->offset($_POST['start'])->limit($_POST['length'])->orderBy('payment_date', 'asc')->get();
            $dataCount = LoanDayBooks::where('loan_id', '=', $request->loanId);
            $totalCount = $dataCount->count();
            $sno = $_POST['start'];
            $rowReturn = array();
            $total = 0;
            foreach ($data as $row) {
                $sno++;
                $val['DT_RowIndex'] = $sno;
                $val['transaction_id'] = $row->id;
                $val['date'] = date("d/m/Y", strtotime($row->payment_date));
                $paymentMode = '';
                if ($row->payment_mode == 0) {
                    $paymentMode = 'Cash';
                } elseif ($row->payment_mode == 1) {
                    $paymentMode = 'Cheque';
                } elseif ($row->payment_mode == 2) {
                    $paymentMode = 'DD';
                } elseif ($row->payment_mode == 3) {
                    $paymentMode = 'Online Transaction';
                } elseif ($row->payment_mode == 4) {
                    $paymentMode = 'By Saving Account ';
                } elseif ($row->payment_mode == 6) {
                    $paymentMode = 'JV ';
                }
                $val['payment_mode'] = $paymentMode;
                $val['description'] = $row->description;
                $val['sanction_amount'] = $row->sanction_amount;
                if ($row->loan_sub_type == 1) {
                    $deposite = $row->deposit;
                    $val['penalty'] = $deposite;
                } else {
                    $val['penalty'] = '0';
                }
                if ($row->loan_sub_type == 0) {
                    $deposite = $row->deposit;
                    ;
                    $val['deposite'] = $deposite;
                } else {
                    $val['deposite'] = '0';
                }
                if ($row->jv_journal_amount) {
                    $jv_journal_amount = $row->jv_journal_amount;
                    ;
                    $val['jv_amount'] = number_format((float) $jv_journal_amount, 2, '.', '');
                } else {
                    $val['jv_amount'] = '0';
                }
                if ($row->igst_charge > 0 && isset($row->igst_charge)) {
                    $val['igst_charge'] = number_format((float) $row->igst_charge, 2, '.', '');
                } else {
                    $val['igst_charge'] = '0';
                }
                if ($row->cgst_charge > 0 && isset($row->cgst_charge)) {
                    $val['cgst_charge'] = number_format((float) $row->cgst_charge, 2, '.', '');
                } else {
                    $val['cgst_charge'] = '0';
                }
                if ($row->sgst_charge > 0 && isset($row->sgst_charge)) {
                    $val['sgst_charge'] = number_format((float) $row->sgst_charge, 2, '.', '');
                } else {
                    $val['sgst_charge'] = '0';
                }
                $total = $total + $row->deposit + $val['igst_charge'] + $val['sgst_charge'] + $val['cgst_charge'];
                $val['balance'] = number_format((float) $total, 2, '.', '');
                $rowReturn[] = $val;
            }
        }
        $output = [
            "draw" => $_POST['draw'],
            "recordsTotal" => $totalCount,
            "recordsFiltered" => $count,
            "data" => $rowReturn
        ];
        return json_encode($output);
    }
    public function loanTransaction(Request $request)
    {
        if (check_my_permission(Auth::user()->id, "265") != "1") {
            return redirect()->route('admin.dashboard');
        }
        $data['title'] = 'Loan Transactions Detail';
        /*        $data=Memberloans::with('loan','loanMember','loanMemberAssociate')->with(['loanBranch' => function($query){ $query->select('id', 'name','branch_code','sector','regan','zone');}])->where('loan_type','!=',3);*/
        return view('templates.admin.loan.loan-transactions', $data);
    }
    public function loanTransactionAjax(Request $request)
    {
        if ($request->ajax()) {
            $arrFormData = array();
            if (!empty($_POST['searchform'])) {
                foreach ($_POST['searchform'] as $frm_data) {
                    $arrFormData[$frm_data['name']] = $frm_data['value'];
                }
            }
            if (isset($arrFormData['is_search']) && $arrFormData['is_search'] == 'yes') {
                $data = LoanDayBooks::with([
                    'loan_member' => function ($query) {
                        $query->select('id', 'member_id', 'first_name', 'last_name', 'associate_code');
                    }
                ])->with([
                            'loanBranch' => function ($query) {
                                $query->select('id', 'name', 'branch_code', 'sector', 'regan', 'zone');
                            }
                        ])->with([
                            'member_loan' => function ($query) {
                                $query->select('id', 'emi_option', 'emi_period', 'applicant_id')->with('loanMemberCompany:id,member_id');
                            }
                        ])->with([
                            'group_member_loan' => function ($query) {
                                $query->select('id', 'emi_option', 'emi_period');
                            }
                        ])->where('is_deleted', 0)
                    ->with(['loanMemberAssociate'])->whereHas('loan_plan', function ($q) use ($arrFormData) {
                        $q->where('loan_type', $arrFormData['transaction_loan_type'])->select('id', 'name', 'loan_type');
                    })
                    // ->with(['loan_plan' => function($q){ $q->select('id','name','loan_type'); }])
                    ->WHERE('status', 1)->where('company_id', $arrFormData['company_id']);
                if (Auth::user()->branch_id > 0) {
                    $id = Auth::user()->branch_id;
                    $data = $data->where('branch_id', '=', $id);
                }
                if ($arrFormData['date_from'] != '') {
                    $startDate = date("Y-m-d", strtotime(convertDate($arrFormData['date_from'])));
                    if ($arrFormData['date_to'] != '') {
                        $endDate = date("Y-m-d ", strtotime(convertDate($arrFormData['date_to'])));
                    } else {
                        $endDate = '';
                    }
                    $data = $data->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate]);
                }
                // if($arrFormData['loan_plan'] !=''){
                //     $planId=$arrFormData['loan_plan'];
                //     $data=$data->where('loan_type','=',$planId);
                // }
                if (isset($arrFormData['branch_id']) && $arrFormData['branch_id'] != '') {
                    $branch_id = $arrFormData['branch_id'];
                    $data = $data->where('branch_id', '=', $branch_id);
                }
                if ($arrFormData['application_number'] != '') {
                    $application_number = $arrFormData['application_number'];
                    $data = $data->where('account_number', '=', $application_number);
                }
                if ($arrFormData['associate_code'] != '') {
                    $associateCode = $arrFormData['associate_code'];
                    $data = $data->whereHas('loanMemberAssociate', function ($query) use ($associateCode) {
                        $query->where('members.associate_no', 'LIKE', '%' . $associateCode . '%');
                    });
                }
                if ($arrFormData['payment_mode'] != '') {
                    $payment_mode = $arrFormData['payment_mode'];
                    $data = $data->where('payment_mode', '=', $payment_mode);
                }
                // $loant=$arrFormData['loan_type'];
                // $data = $data->whereHas('loan_plan', function ($query) use ($loant)
                //     {
                //         $query->where('loans.loan_type', '=',$loant);
                //     });
                $count = $data->count('id');
                $totalCount = $count;
                $data = $data->orderby('id', 'DESC')->offset($_POST['start'])->limit($_POST['length'])->get();
                $sno = $_POST['start'];
                $rowReturn = array();
                foreach ($data as $i => $row) {
                    $sno++;
                    $val['DT_RowIndex'] = $sno;
                    $val['created_at'] = date("d/m/Y", strtotime($row['created_at']));
                    ;

                    $val['branch'] =
                        $val['sector'] = $row['loanBranch']->sector;
                    $val['branch_code'] = $row['loanBranch']->branch_code;
                    $val['region'] = $row['loanBranch']->regan;
                    $val['zone'] = $row['loanBranch']->zone;
                    $url = URL::to("admin/loan/emi-transactions/" . $row->loan_id . "/" . $row->loan_type . "");
                    $btn = '<a class=" " href="' . $url . '" title="Edit Member" target="_blank">' . $row->account_number . '</a>';
                    $val['account_number'] = $btn;
                    if ($row->loan_type == 3) {
                        if (isset($row['loan_member'])) {
                            $val['member_name'] = $row['loan_member']->first_name . ' ' . $row['loan_member']->last_name;
                            $val['customer_id'] = $row['loan_member']->member_id;
                        }
                    } else {
                        if (isset($row['loan_member'])) {
                            $val['customer_id'] = $row['loan_member']->member_id;
                            $val['member_name'] = $row['loan_member']->first_name . ' ' . $row['loan_member']->last_name;
                        }
                    }
                    $val['member_id'] = $row['member_loan']['loanMemberCompany']->member_id;
                    if (isset($row['loanMemberAssociate'])) {
                        $val['associate_code'] = $row['loanMemberAssociate']->associate_no;
                    } else {
                        $val['associate_code'] = 'N/A';
                    }
                    $plan_name = 'N/A';
                    $plan_name = $row['loan_plan']->name;
                    ;
                    $val['plan_name'] = $plan_name;
                    $emi_tenure = 'N/A';
                    if ($row['loan_plan']->loan_type == 'G') {
                        if (isset($row['group_loan']->emi_option) && $row['group_loan']->emi_option == 1) {
                            $emi_tenure = $row['group_loan']->emi_period . " Months";
                        } elseif (isset($row['group_loan']->emi_option) && $row['group_loan']->emi_option == 2) {
                            $emi_tenure = $row['group_loan']->emi_period . " Weeks";
                        } elseif (isset($row['group_loan']->emi_option) && $row['group_loan']->emi_option == 3) {
                            $emi_tenure = $row['group_loan']->emi_period . " Days";
                        }
                    } else {
                        if (isset($row['member_loan']->emi_option) && $row['member_loan']->emi_option == 1) {
                            $emi_tenure = $row['member_loan']->emi_period . " Months";
                        } elseif (isset($row['member_loan']->emi_option) && $row['member_loan']->emi_option == 2) {
                            $emi_tenure = $row['member_loan']->emi_period . " Weeks";
                        } elseif (isset($row['member_loan']->emi_option) && $row['member_loan']->emi_option == 3) {
                            $emi_tenure = $row['member_loan']->emi_period . " Days";
                        }
                    }
                    $val['tenure'] = $emi_tenure;
                    $val['emi_amount'] = $row->deposit;
                    $loan_sub_type = $row->loan_sub_type;
                    if ($loan_sub_type == 0) {
                        $loan_sub_type = 'EMI';
                    } else {
                        $loan_sub_type = 'Late Penalty';
                    }
                    $val['loan_sub_type'] = $loan_sub_type;
                    // $member =Member::where('id',$row->associate_id)->first(['id','first_name','last_name']);
                    if (isset($row['loanMemberAssociate'])) {
                        $val['associate_name'] = $row['loanMemberAssociate']->first_name . ' ' . $row['loanMemberAssociate']->last_name;
                    } else {
                        $val['associate_name'] = 'N/A';
                    }
                    $payment_mode = '';
                    switch ($row['payment_mode']) {
                        case 0:
                            $payment_mode = 'Cash';
                            break;
                        case 1:
                            $payment_mode = 'Cheque';
                            break;
                        case 2:
                            $payment_mode = 'DD';
                            break;
                        case 3:
                            $payment_mode = 'Online Transaction';
                            break;
                        case 4:
                            $payment_mode = 'By Saving Account';
                            break;
                        default:
                            $payment_mode = 'Cash';
                            break;
                    }
                    $val['payment_mode'] = $payment_mode;
                    //           $vurl = URL::to("admin/loan/view/" . $row->id . "/" . $row->loan_type . "");
                    //             $eurl = URL::to("admin/loan/edit/" . $row->id . "");
                    //             $aurl = URL::to("admin/loan/approve/" . $row->id . "");
                    //             $rurl = URL::to("admin/loan/loan-request-reject/" . $row->id . "/" . $row->loan_type . "");
                    //             $taurl = URL::to("admin/loan/transfer/" . $row->id . "");
                    //             $purl = URL::to("admin/loan/print/" . $row->id . "/" . $row->loan_type . "");
                    //             $turl = URL::to("admin/loan/emi-transactions/" . $row->id . "/" . $row->loan_type . "");
                    //              $urlCom = URL::to("admin/loan/commission/" . $row->id . "");
                    //  $pdf = URL::to("admin/loan/download-recovery-clear/" . $row->id . "/" . $row->loan_type . "");
                    //              $print = URL::to("admin/loan/print-recovery-clear/" . $row->id . "/" . $row->loan_type . "");
                    //             $btn = '<div class="list-icons"><div class="dropdown"><a href="#" class="list-icons-item" data-toggle="dropdown"><i class="icon-menu9 mr-2"></i></a><div class="dropdown-menu dropdown-menu-right">';
                    //             if($row->status == 0){
                    //               $btn .= '<a class="dropdown-item" href="' . $aurl . '"><i class="fas fa-thumbs-up mr-2"></i>Approve</a>';
                    //               $btn .= '<a class="dropdown-item reject-loan" href="'.$rurl.'" data-toggle="modal" data-target="#loan-rejected" loan-id="'.$row->id.'"><i class="fas fa-thumbs-down mr-2"></i>Delete</a>';
                    //               $btn .= '<a class="dropdown-item" href="' . $eurl . '"><i class="icon-pencil7 mr-2"></i>Edit</a>';
                    //             }elseif($row->status == 1 && ($row->amount != $row->deposite_amount)){
                    //                 $btn .= '<a class="dropdown-item" href="' . $taurl . '"><i class="fa fa-exchange mr-2"></i>Transfer Amount</a>';
                    //             }
                    //             $btn .= '<a class="dropdown-item" href="' . $vurl . '"><i class="fas fa-eye text-default mr-2"></i>View</a>';
                    //             $btn .= '<a class="dropdown-item" href="' . $purl . '" target="_blank"><i class="fa fa-print mr-2" aria-hidden="true"></i>Print</a>';
                    //             $btn .= '<a class="dropdown-item" href="' . $turl . '"><i class="fas fa-money-bill-wave-alt mr-2" aria-hidden="true"></i>Transactions</a>';
                    //             $btn .= '<a class="dropdown-item" href="' . $urlCom . '"><i class="fas fa-percent text-default mr-2"></i>Loan Commission</a>';
                    // if($row->status == 3){
                    //   $btn .= '<a class="dropdown-item" href="' . $pdf . '"><i class="fas fa-download text-default mr-2"></i>Download No Dues</a>';
                    //   $btn .= '<a class="dropdown-item" href="' . $print . '" target="_blank"><i class="fas fa-print text-default mr-2" ></i>print No Dues</a>';
                    // }
                    //             $btn .= '</div></div></div>';
                    //             $val['action'] = $btn;
                    $rowReturn[] = $val;
                }
                $output = array("draw" => $_POST['draw'], "recordsTotal" => $totalCount, "recordsFiltered" => $count, "data" => $rowReturn);
                return json_encode($output);
            } else {
                $output = array(
                    "branch_id" => Auth::user()->branch_id,
                    "draw" => 0,
                    "recordsTotal" => 0,
                    "recordsFiltered" => 0,
                    "data" => 0,
                );
                return json_encode($output);
            }
        }
    }
    public function gst_amount_penalty(Request $req)
    {
        $loanId = $req->loanId;
        $loanType = $req->loanType;
        $penaltyAmount = $req->penaltyAmount;
        $globaldate = date('Y-m-d', strtotime(convertDate($req->deDate)));
        if ($loanType == 'loan') {
            $loanDetails = Memberloans::select('id', 'branch_id')->with('loanBranch')->where('id', $loanId)->first();
        } else {
            $loanDetails = Grouploans::select('id', 'branch_id')->with('loanBranch')->where('id', $loanId)->first();
        }
        // $stateid = getBranchState($loanDetails['loanBranch']->name);
        // $globaldate = checkMonthAvailability(date('d'),date('m'),date('Y'),$stateid);
        $getHeadSetting = \App\Models\HeadSetting::where('head_id', 33)->first();
        $getGstSetting = \App\Models\GstSetting::where('state_id', $loanDetails['loanBranch']->state_id)->whereDate('applicable_date', '<=', $globaldate)->exists();
        if ($penaltyAmount > 0 && $getGstSetting) {
            if ($loanDetails['loanBranch']->state_id == 33) {
                $gstAmount = (($penaltyAmount * $getHeadSetting->gst_percentage) / 100) / 2;
                $label1 = 'CGST ' . ($getHeadSetting->gst_percentage / 2) . ' %';
                $label2 = 'SGST ' . ($getHeadSetting->gst_percentage / 2) . ' %';
                ;
            } else {
                $gstAmount = ($penaltyAmount * $getHeadSetting->gst_percentage) / 100;
                $label1 = 'IGST ' . ($getHeadSetting->gst_percentage) . ' %';
                $label2 = '';
            }
        } elseif (!empty($penaltyAmount) && $loanDetails->amount) {
            if ($loanDetails['loanBranch']->state_id == 33) {
                $gstAmount = (($loanDetails->amount * $getHeadSetting->gst_percentage) / 100) / 2;
                $label1 = 'CGST ' . ($getHeadSetting->gst_percentage / 2) . ' %';
                $label2 = 'SGST ' . ($getHeadSetting->gst_percentage / 2) . ' %';
                ;
            } else {
                $gstAmount = ($loanDetails->amount * $getHeadSetting->gst_percentage) / 100;
                $label1 = 'IGST ' . ($getHeadSetting->gst_percentage) . ' %';
                $label2 = '';
            }
        } else {
            $gstAmount = 0;
            $label1 = 0;
            $label2 = 0;
        }
        return response()->json(['gstAmount' => $gstAmount, 'label1' => $label1, 'label2' => $label2]);
    }
    /**
     * Get Insurance Charge
     */
    public function getInsuranceCharge(Request $req)
    {
        $loanId = $req->loanId;
        $loanType = $req->loanType;
        $globaldate = date('Y-m-d', strtotime(convertDate($req->deDate)));
        if ($loanType == 'loan') {
            $loanDetails = Memberloans::select('id', 'branch_id', 'amount', 'loan_type')->with('loanBranch')->where('id', $loanId)->first();
        } else {
            $loanDetails = Grouploans::select('id', 'branch_id', 'amount', 'loan_type')->with('loanBranch')->where('id', $loanId)->first();
        }
        $insurance_charge = \App\Models\LoanCharge::where('min_amount', '<=', $loanDetails->amount)->where('max_amount', '>=', $loanDetails->amount)->where('loan_type', $loanDetails->loan_type)->where('type', 2)->where('status', 1)->where('effective_from', '<=', $globaldate)->first();
        return response()->json(['insurance_charge' => $insurance_charge]);
    }
    /*********************  loan Branch Transfer start ******************************/

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
    /*********************  Loan Plan Transfer Starts******************************/
    public function loanPlanTransfer()
    {
        if (check_my_permission(Auth::user()->id, "305") != "1") {
            return redirect()->route('admin.dashboard');
        }
        $data['title'] = 'Loan | Loan Plan Transfer';
        return view('templates.admin.loan.plantransfer.loanplantransfer', $data);
    }
    public function getLoanPlanTansferData(Request $request)
    {
        // $branch =   \App\Models\Branch::where('status',1);
        // if (Auth::user()->branch_id > 0)
        // {
        //     $branch=$branch->where('id', Auth::user()->branch_id);
        // }
        // $branch=$branch->get();
        $loantype = Loans::where('status', 1)->get();
        $groupList = '';
        $data = Memberloans::with([
            'loanMember' => function ($q) {
                $q->select('id', 'member_id', 'first_name', 'last_name');
            },
            'loanMemberAssociate' => function ($q) {
                $q->select('id', 'associate_no', 'first_name', 'last_name');
            },
            'loanBranch' => function ($q) {
                $q->select('id', 'branch_code', 'name');
            }
        ])->with(['loans'])->where('account_number', $request->code)->whereIn('status', [4])
            ->first();
        if (empty($data)) {
            $data = Grouploans::with([
                'loanMember' => function ($q) {
                    $q->select('id', 'member_id', 'first_name', 'last_name');
                },
                'loanMemberAssociate' => function ($q) {
                    $q->select('id', 'associate_no', 'first_name', 'last_name');
                },
                'loanBranch' => function ($q) {
                    $q->select('id', 'branch_code', 'name');
                }
            ])->with(['loans'])->where('account_number', $request->code)->whereIn('status', [4])->first();
            if ($data) {
                $groupList = Grouploans::with([
                    'loanMember' => function ($q) {
                        $q->select('id', 'member_id', 'first_name', 'last_name');
                    }
                ])->where('group_loan_common_id', $data->group_loan_common_id)->where('id', '!=', $data->id)->get();
                $clearCount = Grouploans::where('group_loan_common_id', $data->group_loan_common_id)->where('status', 3)->count();
            }
        }
        $type = $request->type;
        if ($data) {
            if ($data->loan_type == 3) {
                if ($clearCount > 0) {
                    return \Response::json(['view' => 'No data found', 'msg_type' => 'error_cleargoup']);
                } else {
                    $id = $data->id;
                    return \Response::json(['view' => view('templates.admin.loan.plantransfer.loanplantransferdetail', ['loanData' => $data, 'loantype' => $loantype, 'groupList' => $groupList])->render(), 'msg_type' => 'success', 'id' => $id]);
                }
            } else {
                //dd($data);
                if ($data->status == 3) {
                    return \Response::json(['view' => 'No data found', 'msg_type' => 'error_clear']);
                } else {
                    $id = $data->id;
                    return \Response::json(['view' => view('templates.admin.loan.plantransfer.loanplantransferdetail', ['loanData' => $data, 'loantype' => $loantype, 'groupList' => $groupList])->render(), 'msg_type' => 'success', 'id' => $id]);
                }
            }
        } else {
            return \Response::json(['view' => 'No data found', 'msg_type' => 'error']);
        }
    }
    /**
     * Get Loan Account Details
     */
    public function getnewLoanPlanData(Request $request)
    {
        $loanid = $request->loanplan;
        $oldsanctioned_amt = $request->sanctioned_amt;
        $loantype = Loans::where('status', 1)->get();
        $exist = \App\Models\LoanTenure::where('id', $loanid)->where("min_amount", '<=', $oldsanctioned_amt)->where("max_amount", '>=', $oldsanctioned_amt)->exists();
        if ($exist) {
            return \Response::json(['view' => view('templates.admin.loan.plantransfer.new_loanplan_detail', ['loantype' => $loantype])->render(), 'msg_type' => 'success']);
        } else {
            return \Response::json(['view' => view('templates.admin.loan.plantransfer.new_loanplan_detail', ['loantype' => $loantype])->render(), 'msg_type' => 'error']);
        }
    }
    /**
     * Store
     */
    public function loanPlanTransferSave(Request $request)
    {
        $created_by_id = Auth::user()->id;
        $globaldate = $request['created_at'];
        $created_at = date("Y-m-d H:i:s", strtotime(convertDate($globaldate)));
        if ($request->loan_type == 3) {
        } else {
        }
    }
    public function LoanChargesStore($request, $loanId)
    {
        $rules = [
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
        $created_by_id = Auth::user()->id;
        if (isset($request->file_min_amount)) {
            $data['type'] = 1;
            $data['charge_type'] = $request->charge_type;
            $data['charge'] = $request->charge;
            $data['min_amount'] = $request->file_min_amount;
            $data['max_amount'] = $request->file_max_amount;
            $data['effective_from'] = date("Y-m-d", strtotime(convertDate($request->effective_from)));
            $data['effective_to'] = isset($request->file_effective_to) ? date("Y-m-d", strtotime(convertDate($request->file_effective_to))) : NULL;
            $data['created_by'] = 1;
            $data['created_by_id'] = $created_by_id;
            $data['company_id'] = $request->companyId;
            $data['tenure'] = $request->charges_tenure;
            $data['emi_option'] = $request->charges_emi_option;
            $data['loan_id'] = $loanId;
            if ($request['id'] == "") {
                $loanchargecreate = \App\Models\LoanCharge::create($data);
            } else {
                $loanchargecreate = \App\Models\LoanCharge::where("id", $request['id'])->update($data);
            }
        }
        if (isset($request->ins_min_amount)) {
            $data['type'] = 2;
            $data['charge_type'] = $request->ins_charge_type;
            $data['charge'] = $request->ins_charge;
            $data['min_amount'] = $request->ins_min_amount;
            $data['max_amount'] = $request->ins_max_amount;
            $data['effective_from'] = date("Y-m-d", strtotime(convertDate($request->effective_from)));
            $data['effective_to'] = (isset($request->ins_effective_to)) ? date("Y-m-d", strtotime(convertDate($request->ins_effective_to))) : NULL;
            $data['created_by'] = 1;
            $data['created_by_id'] = $created_by_id;
            $data['loan_id'] = $loanId;
            $data['company_id'] = $request->companyId;
            $data['tenure'] = $request->charges_tenure;
            $data['emi_option'] = $request->charges_emi_option;
            if ($request['id'] == "") {
                $loanchargecreate = \App\Models\LoanCharge::create($data);
            } else {
                $loanchargecreate = \App\Models\LoanCharge::where("id", $request['id'])->update($data);
            }
        }
        return $loanchargecreate;
    }
    public function editLoanChargesStore($request, $loanId)
    {
        $rules = [
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
        $created_by_id = Auth::user()->id;
        if (count($request->filecharges_id) > 0) {
            foreach (($request->filecharges_id) as $key => $option) {
                $data['type'] = 1;
                $data['charge_type'] = $_POST['charge_type'][$key];
                $data['charge'] = $_POST['charge'][$key];
                $data['min_amount'] = $_POST['file_min_amount'][$key];
                $data['max_amount'] = $_POST['file_max_amount'][$key];
                $data['effective_from'] = date("Y-m-d", strtotime(convertDate($_POST['file_effective_from'][$key])));
                $data['effective_to'] = date("Y-m-d", strtotime(convertDate($_POST['file_effective_to'][$key])));
                $data['created_by'] = 1;
                $data['created_by_id'] = $created_by_id;
                $data['plan_name'] = $loanId;
                $loanchargecreate = \App\Models\LoanCharge::where("id", $_POST['filecharges_id'][$key])->update($data);
            }
        }
        if (isset($_POST['more_charge_type'])) {
            foreach (($_POST['more_charge_type']) as $key => $option) {
                $dataMore['type'] = 1;
                $dataMore['charge_type'] = $_POST['more_charge_type'][$key];
                $dataMore['charge'] = $_POST['more_charge'][$key];
                $dataMore['min_amount'] = $_POST['more_file_min_amount'][$key];
                $dataMore['max_amount'] = $_POST['more_file_max_amount'][$key];
                $dataMore['effective_from'] = date("Y-m-d", strtotime(convertDate($_POST['more_file_effective_from'][$key])));
                $dataMore['effective_to'] = date("Y-m-d", strtotime(convertDate($_POST['more_file_effective_to'][$key])));
                $dataMore['created_by'] = 1;
                $dataMore['created_by_id'] = $created_by_id;
                $dataMore['plan_name'] = $loanId;
                $res = \App\Models\LoanCharge::create($dataMore);
            }
        }
        if (count($request->insurancecharges_id) > 0) {
            foreach (($request->insurancecharges_id) as $key => $option) {
                $data['type'] = 2;
                $data['charge_type'] = $_POST['ins_charge_type'][$key];
                $data['charge'] = $_POST['ins_charge'][$key];
                $data['min_amount'] = $_POST['ins_min_amount'][$key];
                $data['max_amount'] = $_POST['ins_max_amount'][$key];
                $data['effective_from'] = date("Y-m-d", strtotime(convertDate($_POST['ins_effective_from'][$key])));
                $data['effective_to'] = date("Y-m-d", strtotime(convertDate($_POST['ins_effective_to'][$key])));
                $data['created_by'] = 1;
                $data['created_by_id'] = $created_by_id;
                $data['plan_name'] = $loanId;
                $loanchargecreate = \App\Models\LoanCharge::where("id", $_POST['insurancecharges_id'][$key])->update($data);
            }
        }
        if (isset($_POST['more_ins_charge_type'])) {
            foreach (($_POST['more_ins_charge_type']) as $key => $option) {
                $dataMore['type'] = 2;
                $dataMore['charge_type'] = $_POST['more_ins_charge_type'][$key];
                $dataMore['charge'] = $_POST['more_ins_charge'][$key];
                $dataMore['min_amount'] = $_POST['more_ins_min_amount'][$key];
                $dataMore['max_amount'] = $_POST['more_ins_max_amount_'][$key];
                $dataMore['effective_from'] = date("Y-m-d", strtotime(convertDate($_POST['more_ins_effective_from'][$key])));
                $dataMore['effective_to'] = date("Y-m-d", strtotime(convertDate($_POST['more_ins_effective_to'][$key])));
                $dataMore['created_by'] = 1;
                $dataMore['created_by_id'] = $created_by_id;
                $dataMore['plan_name'] = $loanId;
                $res = \App\Models\LoanCharge::create($dataMore);
            }
        }
        return $loanchargecreate;
    }
    /**
     * Delete Tenure and File And Insurance Charge
     * @params type, Id
     *
     **/
    public function delete_loan_tenure_charge(Request $request)
    {
        $type = $request->type;
        $id = $request->id;
        try {
            switch ($type) {
                case 'tenure':
                    $record = LoanTenure::findorFail($id);
                    $resultMsg = true;
                    $msg = "Tenure Deleted SuccessFully!";
                    break;
                case 'file':
                    $record = \App\Models\LoanCharge::findorFail($id);
                    $resultMsg = true;
                    $msg = "File Charge Deleted SuccessFully!";
                    break;
                case 'insurance':
                    $record = \App\Models\LoanCharge::findorFail($id);
                    $resultMsg = true;
                    $msg = "Insurance Charge Deleted SuccessFully!";
                    break;
            }
        } catch (\Exception $ex) {
            $errormsg = 'No Record To delete' . $ex->getCode();
            return Response::json(['errormsg' => $errormsg]);
        }
        $result = $record->delete();
        // $result = false;
        if ($result) {
            $response['result'] = $resultMsg;
            $response['message'] = $msg;
        } else {
            $response['result'] = false;
            $response['message'] = "Record was not Deleted, Try Again!";
        }
        return json_encode($response, JSON_PRETTY_PRINT);
    }
    /**
     * Update Plan and Tenure and File Charge and Insurance Charge
     * @params  id
     */
    public function editPlan(Request $request)
    {
        $planId = $request->plan_id;
        $slug = str_replace(' ', '-', strtolower($request->name));
        $rules = [
            'name' => 'required|unique:loans,name,' . $planId,
            'code' => 'required|unique:loans,code,' . $planId,
            'loan_type' => 'required',
            'loan_category' => 'required',
            'min_amount' => 'required',
            'max_amount' => 'required',
            'effective_from' => 'required',
            'slug' => 'unique:loans,slug,' . $planId,
        ];
        $customMessages = [
            'required' => 'The :attribute field is required.'
        ];
        $this->validate($request, $rules, $customMessages);
        //echo $request->effective_from;die;
        $globaldate = $request->created_at;
        $created_at = date("Y-m-d H:i:s", strtotime(convertDate($globaldate)));
        $data['name'] = $request->name;
        $data['code'] = $request->code;
        $data['slug'] = $slug;
        $data['loan_type'] = $request->loan_type;
        $data['loan_category'] = $request->loan_category;
        $data['min_amount'] = $request->min_amount;
        $data['max_amount'] = $request->max_amount;
        $data['effective_from'] = date('Y-m-d', strtotime(convertDate($request->effective_from)));
        $data['created_by_id'] = auth()->user()->id;
        $data['created_at'] = $created_at;
        $data['status'] = 1;
        $res = Loans::findorfail($planId);
        AccountHeads::where('head_id', $res->head_id)->update(['sub_head' => $request->name]);
        $res->update($data);
        $updateTenure = LoanTenure::where('loan_id', $planId)->update(['effective_from' => date('Y-m-d', strtotime(convertDate($request->effective_from)))]);
        $updateFileCharge = \App\Models\LoanCharge::where('plan_name', $planId)->update(['effective_from' => date('Y-m-d', strtotime(convertDate($request->effective_from)))]);
        // $this->editTenure($request,$planId);
        // $this->EditLoanChargesStore($request,$planId);
        if ($res) {
            return redirect()->back()->with('success', 'Loan Plan Saved Successfully!');
        } else {
            return redirect()->route('admin.loan.plan_listing')->with('alert', 'Problem With Creating New Plan');
        }
    }
    /**Delete Last Emi
     * @param id,type,id2
     * loanId is loan Id
     * loanType is loan Type
     * recordId is loanDaybook auto id
     */
    public function delete_emi_transaction(Request $request, $loanId, $loanType, $recordId)
    {
        DB::beginTransaction();
        try {
            $memberLoanRecord = (($loanType != 3) ? MemberLoans::findorfail($loanId) : GroupLoans::findorfail($loanId));
            $getRecord = LoanDaybooks::findorfail($recordId);
            $checkPenaltyExist = LoanDaybooks::where('loan_sub_type', 1)->where('created_at', $getRecord->created_at)->where(['loan_type' => $getRecord->loan_type, 'loan_id' => $getRecord->loan_id])->first();
            if ($checkPenaltyExist) {
                $this->records(new AllHeadTransaction(), $checkPenaltyExist->daybook_ref_id);
                $checkPenaltyExist->update(['is_deleted' => 1]);
            }
            /*Delete Record in All Head Transaction */
            $this->records(new AllHeadTransaction(), $getRecord->daybook_ref_id);
            switch ($getRecord->payment_mode) {
                case 4:
                    $getSavingAccount = SavingAccountTranscation::where('daybook_ref_id', $getRecord->daybook_ref_id)->first();
                    $getSavingAccount->update(['is_deleted' => 1]);
                    DB::select('call updateSSbTransactionAmount(?)', [$getSavingAccount->account_no]);
                    $getSavingAccount->savingAc->update(['balance' => $getSavingAccount->savingAc->balance + $getSavingAccount->withdrawal]);
                    break;
                case 1:
                case 3:
                    SamraddhBankDaybook::where('daybook_ref_id', $getRecord->daybook_ref_id)->update(['is_deleted' => 1]);
                    break;
            }
            LoanEmisNew::where('emi_id', $recordId)->update(['is_deleted' => '1']);
            $getRecord->update(['is_deleted' => 1]);
            $this->cron($memberLoanRecord);
            /*Get Branch DaybookRecord */
            DB::commit();
        } catch (\Exception $ex) {
            dd($ex->getLine(),$ex->getMessage(),$ex->getFile());

            DB::rollback();
            return back()->with('alert', $ex->getMessage().$ex->getLine());
        }
        return back()->with('success', "Emi Deleted SuccessFully");
    }
    public function loanRequestRejectHold(Request $request)
    {
        $loanId = $request->demandId;
        $rejectreason = $request->rejectreason;
        $loanType = $request->loanType;
        $loanCategory = $request->loanCategory;
        $status = $request->status;
        $date = date('Y-m-d', strtotime(convertDate($request->create_application_date)));
        $msg = ($status == 5) ? 'Rejected' : (($status == 8) ? 'Cancle' : 'Hold');
        $mLoanDetails = ($loanCategory == '3') ?
            Grouploans::select('branch_id', 'loan_type', 'rejection_description', 'status', 'id')->with([
                'loan' => function ($q) {
                    $q->select('id', 'name');
                }
            ])->where('id', $loanId)->first() : Memberloans::select('branch_id', 'loan_type', 'rejection_description', 'status', 'id')->with([
                'loan' => function ($q) {
                    $q->select('id', 'name');
                }
            ])->where('id', $loanId)->first();
        DB::beginTransaction();
        try {
            $mLoanDetails->update(['status' => $status, 'rejection_description' => $rejectreason]);
            $data = [
                'loanId' => $loanId,
                'loan_type' => $loanType,
                'loan_category' => $loanCategory,
                'loan_name' => $mLoanDetails['loan']->name,
                'status' => $status,
                'title' => $msg,
                'description' => $rejectreason,
                'status_changed_date' => $date,
                'created_by' => Auth::user()->id,
                'user_name' => Auth::user()->username,
                'created_by_name' => 'Admin',
            ];
            \App\Models\LoanLog::create($data);
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('alert', $ex->getMessage().$ex->getLine());
        }
        return back()
            ->with('success', 'Loan request has been ' . $msg . '!');
    }
    /**
     * Summary of loanLogs
     * @param mixed $loanId
     * @param mixed $loanType
     * @return void
     */
    public function loanLogs($loanId, $loanType)
    {
        // dd($loanId,$loanType);
        $data['title'] = 'Loan Logs';
        $data['headTitle'] = 'Loan Log Listing';
        $data['columnName'] = ['S/N', 'Loan Category', 'Loan Name', 'Status', 'Status Change Date', 'Created By', 'UserName', 'Created_at'];
        $data['record'] = \App\Models\LoanLog::logs($loanType, $loanId)->orderBy('created_at', 'desc')->get();
        return view('templates.admin.Logs.index', $data);
    }
    public function getLoanLogs(Request $request)
    {
        $data = \App\Models\LoanLog::where('id', $request['id'])->get();
        // dd( $data);
        return $data;
    }
    /**
     * Summary of loanRequestPending
     * @param mixed $id
     * @return mixed
     */
    public function loanStatusChange($loanId, $loanType, $status, $date)
    {
        $date = decrypt($date);
        $mLoan = ($loanType == '3') ?
            Grouploans::select('branch_id', 'loan_type', 'rejection_description', 'status', 'id', 'account_number')->with([
                'loan' => function ($q) {
                    $q->select('id', 'name', 'loan_category');
                }
            ])->where('id', $loanId)->first() : Memberloans::select('branch_id', 'loan_type', 'rejection_description', 'status', 'id', 'account_number')->with([
                'loan' => function ($q) {
                    $q->select('id', 'name', 'loan_category');
                }
            ])->where('id', $loanId)->first();
        $mLoanData['status'] = $status;
        $mLoanData['rejection_description'] = '';
        $mLoan->update($mLoanData);
        $data = [
            'loanId' => $mLoan->id,
            'loan_type' => $mLoan->loan_type,
            'loan_name' => $mLoan->loan->name,
            'status' => $status,
            'status_changed_date' => $date,
            'created_by' => Auth::user()->id,
            'user_name' => Auth::user()->username,
            'loan_category' => $mLoan->loan->loan_category,
            'created_by_name' => 'Admin',
        ];
        \App\Models\LoanLog::create($data);
        if ($status == 1 && !isset($mLoan->account_number)) {
            ($loanType == '3') ? $this->groupLoanRequestApproval($mLoan->id) : $this->loanRequestApproval($mLoan->id);
        }
        return back()->with('success', 'Loan Status SuccessFully Changed !');
    }

    public function getLoans(Request $request)
    {
        $currglobaldate = Session::get('created_at');
        $loans = \App\Models\LoanTenure::with('loan_tenure_plan')->where('company_id', $request->company_id)->where('status', 1)->get();
        return response()->json(['loans' => $loans]);
    }


    private function headTransaction($loanDaybookId, $paymentMode, $loanType)
    {
        try {
            $allHeadAccruedEntry = array();
            $allHeadPrincipleEntry = array();
            $allHeadpaymentEntry = array();
            $allHeadpaymentEntry2 = array();
            $calculatedDate = '';
            $value = \App\Models\LoanDayBooks::findorfail($loanDaybookId);
            $loansDetail = \App\Models\Loans::where('id', $value->loan_type)->first();
            if ($loanType == 1) {
                $loansRecord = Memberloans::where('account_number', $value->account_number)->first();
                $subType = 545;

            } else {
                $loansRecord = Grouploans::where('account_number', $value->account_number)->first();
                $subType = 546;

            }


            $calculatedDate = date('Y-m-d', strtotime($value->created_at));
            $date = $value;
            $rr = \App\Models\LoanDayBooks::where('account_number', $value->account_number)->where('loan_sub_type', 0)->where('is_deleted', 0)->where('id', '<', $value->id)->orderBY('created_at', 'desc')->first();
            $rangeDate = (isset($date->created_at)) ? date('Y-m-d', strtotime($date->created_at)) : $calculatedDate;
            $stateId = branchName()->state_id;
            $currentDate = checkMonthAvailability(date('d'), date('m'), date('Y'), $stateId);
            $currentDate = date('Y-m-d', strtotime($currentDate));
            $dataTotalCount = DB::select('call calculate_loan_interest_update(?,?,?)', [$currentDate, $value->account_number, 0]);
            if (isset($rr->created_at)) {
                $strattDate = date('Y-m-d', strtotime($rr->created_at));
                ;
                $endDate = date('Y-m-d', strtotime($date->created_at));
                ;
            } else {
                $strattDate = date('Y-m-d', strtotime($loansRecord->approve_date));
                ;
                $endDate = $calculatedDate;
            }
            $accuredSumCR = \App\Models\AllHeadTransaction::where('type', '5')->where('sub_type', $subType)->where('head_id', $loansDetail->ac_head_id)->where('type_transaction_id', $loansRecord->id)->where('payment_type', 'CR')->where('entry_date', '>', $strattDate)->where('entry_date', '<=', $endDate)->sum('amount');
            $accuredSumDR = \App\Models\AllHeadTransaction::where('type', '5')->where('sub_type', $subType)->where('head_id', $loansDetail->ac_head_id)->where('type_transaction_id', $loansRecord->id)->where('payment_type', 'DR')->where('entry_date', '>', $strattDate)->where('entry_date', '<=', $endDate)->sum('amount');
            $emiData = \App\Models\LoanEmisNew::where('emi_date', $rangeDate)->where('loan_type', $value->loan_type)->where('loan_id', $value->loan_id)->where('is_deleted', '0')->first();

            $accuredSum = $accuredSumDR - $accuredSumCR;

            if ($value->deposit <= $accuredSum) {
                $accruedAmount = $value->deposit;
                $principalAmount = 0;
            } else {
                $accruedAmount = $accuredSum;
                $principalAmount = $value->deposit - $accuredSum;
            }
            $paymentHead = '';
            if ($value->payment_mode == 0) {
                $paymentHead = 28;
            }
            if ($value->payment_mode == 4) {
                $ssbHead = \App\Models\Plans::where('company_id', $loansRecord->company_id)->where('plan_category_code', 'S')->first();
                $paymentHead = $ssbHead->deposit_head_id;
            }
            if ($value->payment_mode == 1 || $value->payment_mode == 2 || $value->payment_mode == 3) {
                $getSamraddhData = \App\Models\SamraddhBankDaybook::where('daybook_ref_id', $value->daybook_ref_id)->first();
                $getHead = \App\Models\SamraddhBank::where('id', $getSamraddhData->bank_id)->first();
                $paymentHead = $getHead->account_head_id;
                $bankId = $getSamraddhData->bank_id;
                $bankAcId = $getSamraddhData->account_id;
            }


            $allHeadAccruedEntry = [
                'daybook_ref_id' => $value->daybook_ref_id,
                'branch_id' => $value->branch_id,
                'head_id' => $loansDetail->ac_head_id,
                'type' => 5,
                'bank_id' => $bankId ?? NULL,
                'bank_ac_id' => $bankAcId ?? NULL,
                'sub_type' => $subType,
                'type_id' => $emiData->id ?? null,
                'type_transaction_id' => $value->loan_id,
                'associate_id' => $value->associate_id,
                'member_id' => ($loansDetail->loan_type != 'G') ? $loansRecord->applicant_id : $loansRecord->member_id,

                'branch_id_from' => $value->branch_id,
                'amount' => $accruedAmount,
                'description' => $value->account_number . ' EMI collection',
                'payment_type' => 'CR',
                'payment_mode' => $paymentMode,
                'currency_code' => 'INR',


                'entry_date' => date('Y-m-d', strtotime($value->created_at)),
                'entry_time' => date('H:i:s', strtotime($value->created_at)),
                'created_by' => $value->created_by,
                'created_by_id' => 0,
                'created_at' => $value->created_at,
                'updated_at' => $value->updated_at,
                'company_id' => $value->company_id,
                'cheque_id' => $value->cheque_dd_id ?? NULL,
                'transction_no' => $value->online_payment_id ?? NULL


            ];

            $allHeadPrincipleEntry = [
                'daybook_ref_id' => $value->daybook_ref_id,
                'branch_id' => $value->branch_id,
                'head_id' => $loansDetail->head_id,
                'bank_id' => $bankId ?? NULL,
                'bank_ac_id' => $bankAcId ?? NULL,
                'type' => 5,
                'sub_type' => ($loansDetail->loan_type != 'G') ? 52 : 55,
                'type_id' => $emiData->id ?? null,
                'type_transaction_id' => $value->loan_id,
                'associate_id' => $value->associate_id,
                'member_id' => ($loansDetail->loan_type != 'G') ? $loansRecord->applicant_id : $loansRecord->member_id,

                'branch_id_from' => $value->branch_id,
                'amount' => $principalAmount,
                'description' => $value->account_number . ' EMI collection',
                'payment_type' => 'CR',
                'payment_mode' => $paymentMode,
                'currency_code' => 'INR',

                'entry_date' => date('Y-m-d', strtotime($value->created_at)),
                'entry_time' => date('H:i:s', strtotime($value->created_at)),
                'created_by' => $value->created_by,
                'created_by_id' => 0,
                'created_at' => $value->created_at,
                'updated_at' => $value->updated_at,
                'company_id' => $value->company_id,
                'cheque_id' => $value->cheque_dd_id ?? NULL,
                'transction_no' => $value->online_payment_id ?? NULL


            ];
            $allHeadpaymentEntry = [
                'daybook_ref_id' => $value->daybook_ref_id,
                'branch_id' => $value->branch_id,
                'head_id' => $paymentHead,
                'bank_id' => $bankId ?? NULL,
                'bank_ac_id' => $bankAcId ?? NULL,
                'type' => 5,
                'sub_type' => ($loansDetail->loan_type != 'G') ? 52 : 55,
                'type_id' => $emiData->id ?? null,
                'type_transaction_id' => $value->loan_id,
                'associate_id' => $value->associate_id,
                'member_id' => ($loansDetail->loan_type != 'G') ? $loansRecord->applicant_id : $loansRecord->member_id,

                'branch_id_from' => $value->branch_id,
                'amount' => $value->deposit,
                'description' => $value->account_number . ' EMI collection',
                'payment_type' => 'DR',
                'payment_mode' => $paymentMode,
                'currency_code' => 'INR',

                'entry_date' => date('Y-m-d', strtotime($value->created_at)),
                'entry_time' => date('H:i:s', strtotime($value->created_at)),
                'created_by' => $value->created_by,
                'created_by_id' => 0,
                'created_at' => $value->created_at,
                'updated_at' => $value->updated_at,
                'company_id' => $value->company_id,
                'cheque_id' => $value->cheque_dd_id ?? NULL,
                'transction_no' => $value->online_payment_id ?? NULL


            ];



            $dataInsert1 = \App\Models\AllHeadTransaction::insert($allHeadAccruedEntry);
            $dataInsert2 = \App\Models\AllHeadTransaction::insert($allHeadPrincipleEntry);
            $dataInsert3 = \App\Models\AllHeadTransaction::insert($allHeadpaymentEntry);
            DB::commit();
        } catch (\Exception $ex) {
            dd($ex->getLine(),$ex->getMessage(),$ex->getFile());

            DB::rollback();
            return back()->with('alert', $ex->getMessage().$ex->getLine());

        }


    }
    public function loan_tenure_status(Request $request)
    {
        $loan_tenure_data = LoanTenure::whereId($request->id)->first();
        $loan_data = Loans::where('id', $loan_tenure_data->loan_id)->first();
        if ($loan_tenure_data->status > 0) {
            $date = str_replace('/', '-', $request->gdate);
            $loan_tenure_data->effective_to = date('Y-m-d', strtotime($date));
            $loan_tenure_data->status = 0;
            $loan_tenure_data->save();

            /**Insert the log in the table
             * table name is plan_log_details
             */
            $log_insert = new PlanLogDetails();
            $log_insert->type = 1;
            $log_insert->type_id = $loan_tenure_data->loan_id;
            $log_insert->tenure_id = $loan_tenure_data->id;
            $log_insert->title = 'Loan Plan’s Tenure Status Change';
            if ($loan_tenure_data->emi_option == 1) {
                $log_insert->description = $loan_tenure_data->tenure . ' Months [' . $loan_data->name . ' - ' . $loan_data->code . '] was deactivated by ' . Auth::user()->username;
            } else if ($loan_tenure_data->emi_option == 2) {
                $log_insert->description = $loan_tenure_data->tenure . ' Weeks [' . $loan_data->name . ' - ' . $loan_data->code . '] was deactivated by ' . Auth::user()->username;
            } else if ($loan_tenure_data->emi_option == 3) {
                $log_insert->description = $loan_tenure_data->tenure . ' Days [' . $loan_data->name . ' - ' . $loan_data->code . '] was deactivated by ' . Auth::user()->username;
            }
            $log_insert->old_data = 'Active';
            $log_insert->new_data = 'Inactive';
            $log_insert->created_by = 1;
            $log_insert->created_by_id = Auth::user()->id;
            $log_insert->save();

            return response(['msg' => 1]);
        } else {
            $loan_tenure_data->effective_to = null;
            $loan_tenure_data->status = 1;
            $loan_tenure_data->save();

            /**Insert the log in the table
             * table name is plan_log_details
             */
            $log_insert = new PlanLogDetails();
            $log_insert->type = 1;
            $log_insert->type_id = $loan_tenure_data->loan_id;
            $log_insert->tenure_id = $loan_tenure_data->id;
            $log_insert->title = 'Loan Plan’s Tenure Status Change';
            if ($loan_tenure_data->emi_option == 1) {
                $log_insert->description = $loan_tenure_data->tenure . ' Months [' . $loan_data->name . ' - ' . $loan_data->code . '] was activated by ' . Auth::user()->username;
            } else if ($loan_tenure_data->emi_option == 2) {
                $log_insert->description = $loan_tenure_data->tenure . ' Weeks [' . $loan_data->name . ' - ' . $loan_data->code . '] was activated by ' . Auth::user()->username;
            } else if ($loan_tenure_data->emi_option == 3) {
                $log_insert->description = $loan_tenure_data->tenure . ' Days [' . $loan_data->name . ' - ' . $loan_data->code . '] was activated by ' . Auth::user()->username;
            }
            $log_insert->old_data = 'Inactive';
            $log_insert->new_data = 'Active';
            $log_insert->created_by = 1;
            $log_insert->created_by_id = Auth::user()->id;
            $log_insert->save();

            return response(['msg' => 1]);
        }
    }
}
?>
