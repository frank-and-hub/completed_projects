<?php

namespace App\Http\Controllers\Admin\HrManagement;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Request as Request1;
use Illuminate\Support\Facades\Response;
use Validator;
use Carbon\Carbon;
use DB;
use URL;
use Session;
use Image;
use Yajra\DataTables\DataTables;
use App\Models\EmployeeLedger;
use App\Models\EmployeeSalary;
use App\Models\Employee;
use App\Models\Branch;
use App\Models\AdvancedTransaction;
use App\Models\Designation;
use App\Models\EmployeeSalaryLeaser;
use App\Models\EmployeeSalaryTransfer;
use Illuminate\Support\Facades\Cache;
use App\Http\Controllers\Admin\CommanController;
use App\Models\VendorBillPayment;
use App\Models\SalaryRentLog;
/*
|---------------------------------------------------------------------------
| Admin Panel -- Salary Management SalaryController
|--------------------------------------------------------------------------
|
| This controller handles Salary all functionlity.
*/

class SalaryController extends Controller
{
    /**
     * Create a new controller instance.
     * @return void
     */
    public function __construct()
    {
        // check user login or not
        $this->middleware('auth');
    }
    /**
     * Show Salary.
     * Route: admin/hr/salary
     * Method: get 
     * @return  array()  Response
     */
    public function index()
    {
        if (check_my_permission(Auth::user()->id, "118") != "1") {
            return redirect()->route('admin.dashboard');
        }
        $data['title'] = 'Salary Management | Salary Ledger List';
        //$data['branch']=Branch::where('status',1)->get();
        return view('templates.admin.hr_management.salary.index', $data);
    }
    /**
     * Get cheque list
     * Route: ajax call from - /admin/received/cheque
     * Method: Post 
     * @param  \Illuminate\Http\Request  $request
     * @return JSON array
     */
    public function salary_leaser_listing(Request $request)
    {
        if ($request->ajax()) {
            $arrFormData = [
                'company_id' => $request->company_id,
                'month' => $request->month,
                'year' => $request->year,
                'is_search' => $request->is_search,
                'status' => $request->status,
            ];

            if (isset($arrFormData['is_search']) && $arrFormData['is_search'] == 'yes') {
                $query = EmployeeSalaryLeaser::with(['company:id,name'])->where('id', '>', 0)->where('is_deleted', 0);

                if ($arrFormData['company_id'] != ' ') {
                    $query->where('company_id', $arrFormData['company_id']);
                }
                if ($arrFormData['month'] != '') {
                    $query->where('month', $arrFormData['month']);
                }
                if ($arrFormData['year'] != '') {
                    $query->where('year', $arrFormData['year']);
                }
                if ($arrFormData['status'] != '') {
                    $query->where('status', $arrFormData['status']);
                }

                $data = $query->orderBy('created_at', 'DESC')->get();
                $totalCount = $data->count();
                $count = $data->count();

                $sno = $_POST['start'];
                $rowReturn = [];

                foreach ($data as $row) {
                    $sno++;
                    $val['DT_RowIndex'] = $sno;
                    $val['company_name'] = $row->company->name ?? 'N/A';
                    $val['month'] = $row->month_name;
                    $val['year'] = $row->year;
                    $val['total_amount'] = number_format((float)$row->total_amount, 2, '.', '');
                    $val['transferred_amount'] = number_format((float)$row->transfer_amount, 2, '.', '');
                    $pending = $row->total_amount - $row->transfer_amount;
                    $transfer_charge = number_format((float)$pending, 2, '.', '');
                    $val['transfer_charge'] = $transfer_charge;
                    $pending = EmployeeSalary::where('leaser_id', $row->id)->where('is_deleted', 0)->sum('neft_charge');
                    $neft = number_format((float)$row->total_neft, 2, '.', '');
                    $val['neft'] = $neft;
                    $val['created_at'] = date("d/m/Y", strtotime(convertDate($row->created_at)));

                    $status = 'Pending ';
                    if ($row->status == 1) {
                        $status = 'Transferred ';
                    } elseif ($row->status == 2) {
                        $status = 'Partial Transfer ';
                    }
                    $val['status'] = $status;

                    $btn = '<div class="list-icons"><div class="dropdown"><a href="#" class="list-icons-item" data-toggle="dropdown"><i class="icon-menu9"></i></a><div class="dropdown-menu dropdown-menu-right">';
                    $url2 = URL::to("admin/hr/salary/transfer/" . $row->id . "");
                    $url3 = URL::to("admin/hr/salary/list/" . $row->id . "");
                    $url4 = URL::to("admin/hr/salary/transfer_detail/" . $row->id . "");

                    if ($row->status != 1) {
                        $btn .= '<a class="dropdown-item" href="' . $url2 . '" title="Transfer Salary" target="_blank"><i class="icon-list  mr-2"></i>Transfer Salary </a>  ';
                    }
                    $btn .= '<a class="dropdown-item" href="' . $url3 . '" title="Salary Ledger Detail/Transferred Salary" target="_blank"><i class="icon-list  mr-2"></i>Salary Ledger Detail/Transferred Salary</a>  ';
                    /*$btn .= '<a class="dropdown-item" href="'.$url4.'" title="Salary Transfer Detail"><i class="icon-list  mr-2"></i>Salary Transfer Detail</a>  ';*/
                    if ($row->status == 0) {
                        $btn .= '<a class="dropdown-item" href="javascript:void(0)" title="Ledger Delete" onclick=deleteLedger("' . $row->id . '");><i class="icon-trash-alt  mr-2"></i>Ledger Delete</a>';
                    }
                    $re_month1 = $row->month + 1;
                    if ($re_month1 == 13) {
                        $re_month1 = 1;
                    }
                    $dateChk2 = $row->year . '-' . $re_month1 . '-01';
                    $data_employee = Employee::where('is_employee', 1)->where('company_id', $row->company_id)->where('status', 1)->whereDate('employee_date', '<', $dateChk2)->pluck('id');
                    $leaser_employee = EmployeeSalary::where('leaser_id', $row->id)->orderBy('employee_id')->pluck('employee_id')->toArray();
                    $regenerate = [];
                    foreach ($data_employee as $data_emp) {
                        if (!in_array($data_emp, $leaser_employee)) {
                            array_push($regenerate, $data_emp);
                        }
                    }
                    $re = URL::to("admin/hr/salary/regenerate/" . base64_encode($row->id) . "");
                    if (count($regenerate) > 0 && check_my_permission(Auth::user()->id, "358") == "1") {
                        $re .= '?regenerate=' . urlencode(json_encode($regenerate));
                        $btn .= '<a class="dropdown-item" href="' . $re . '" title="Ledger Regenerate"><i class="fas fa-sync-alt  mr-2"></i>Regenerate Ledger</a>';
                    }
                    $btn .= '</div></div></div>';
                    $val['action'] = $btn;
                    $rowReturn[] = $val;
                }

                $output = [
                    "branch_id" => Auth::user()->branch_id,
                    "draw" => $_POST['draw'],
                    "recordsTotal" => $totalCount,
                    "recordsFiltered" => $count,
                    "data" => $rowReturn
                ];

                return json_encode($output);
            } else {
                $output = [
                    "branch_id" => Auth::user()->branch_id,
                    "draw" => 0,
                    "recordsTotal" => 0,
                    "recordsFiltered" => 0,
                    "data" => 0,
                ];

                return json_encode($output);
            }
        }
    }
    /**
     * Show Salary.
     * Route: admin/hr/salary/payable
     * Method: get 
     * @return  array()  Response
     */
    public function payable(Request $request)
    {
        if (check_my_permission(Auth::user()->id, "117") != "1") {
            return redirect()->route('admin.dashboard');
        }
        $data['title'] = 'Salary Management | Salary Payable';
        //$data['branch']=Branch::where('status',1)->get();
        $data['pre_month_days'] = '';
        if (Request1::isMethod('post')) {
            // print_r($_POST);die;
            $data['code'] = 1;
            $data['company_id'] = $request->company_id;
            $data['re_month'] = $request->month;
            $data['re_year'] = $request->year;
            $check_data = EmployeeSalaryLeaser::where('month', $request->month)->where('year', $request->year)->where('is_deleted', 0)->where('company_id', $request->company_id);
            $check_data = $check_data->count();
            if ($check_data > 0) {
                return back()->with('alert', 'The salary of the employee has already been generated from this detail. Please change the details.')->withInput($request->all());
            }
            $getCurentdate = date("d", strtotime(convertDate($request->created_at1)));
            $getCurentMont = date("m", strtotime(convertDate($request->created_at1)));
            $getCurentYear = date("Y", strtotime(convertDate($request->created_at1)));
            $lastDayOfMonth = date('Y-m-t', strtotime($request->created_at1));
            $ledgerMontget = $request->month;
            $ledgerYearget = $request->year;
            if ($ledgerMontget == $getCurentMont && $ledgerYearget == $getCurentYear && date('Y-m-d', strtotime($request->created_at1)) != $lastDayOfMonth) {
                return back()->with('alert', "The salary ledger for the current month can be created on the last day of the month!")->withInput($request->all());
            }
            if ($ledgerYearget == $getCurentYear && $ledgerMontget > $getCurentMont) {
                return back()->with('alert', "You can not create ledger in future date!")->withInput($request->all());
            }
            $current_year = $request->year;
            if ($request->month == 2) {
                $pre_month = 02;
            } else {
                $pre_month = date("m", mktime(null, null, null, $request->month));
            }
            $pre_month_days = cal_days_in_month(CAL_GREGORIAN, $pre_month, $current_year);
            if ($request->month == 2) {
                $pre_month_name = 'February';
            } else {
                $pre_month_name = date("F", mktime(null, null, null, $pre_month));
            }
            $data['pre_month'] = $pre_month;
            $data['pre_month_days'] = $pre_month_days;
            $data['pre_month_name'] = $pre_month_name;
            $data['current_year'] = $current_year;
            // $dateChk=$current_year.'/'.$pre_month.'/01';
            $re_month1 = $pre_month + 1;
            $dateChk = $current_year . '/' . $re_month1 . '/01';
            if ($re_month1 == 13) {
                $current_year =  $current_year + 1;
                $re_month1 = 1;
            }
            $dateChk2 = $current_year . '-' . $re_month1 . '-01';
            // echo $dateChk;die;
            $data_employee = Employee::with('company:id,name')->with(['branch' => function ($query) {
                $query->select('id', 'name', 'branch_code','regan');
            }])
                ->with(['designation' => function ($query) {
                    $query->select('id', 'designation_name');
                }])
                ->where('is_employee', 1)->where('company_id', $request->company_id)->where('status', 1)->whereDate('employee_date', '<', $dateChk2);
            /*->where(function($q) use ($dateChk2){
                    $q->where('is_terminate', 0)->orWhereDate('terminate_date','>=',$dateChk2);
                });*/

            $data_employee = $data_employee->orderBy('branch_id')->orderBy('employee_name') /*->offset(0)->limit(50)*/->get(['id', 'category', 'employee_name', 'employee_code', 'created_at', 'designation_id', 'branch_id', 'esi_account_no', 'pf_account_no', 'salary', 'pen_card', 'bank_name', 'bank_account_no', 'bank_ifsc_code', 'ssb_account', 'company_id']);
            $data['employee'] = $data_employee;
            $dateChk_ledger = $current_year . '/' . $pre_month . '/01';
            if ($pre_month == 12) {
                $current_year = $current_year - 1;
                $dateChk_ledger = $current_year . '/' . $pre_month . '/01';
            }
            $lastDayofMonth = Carbon::parse($dateChk_ledger)->endOfMonth()->toDateString();
            $data['ledgerDate'] = date("d/m/Y", strtotime(convertDate($lastDayofMonth)));
            $Cache = Cache::put('employeesalarypayable_list', $data_employee->toArray());
            Cache::put('employeesalarypayable_count', count($data_employee->toArray()));
        }
        return view('templates.admin.hr_management.salary.payable', $data);
    }

    /**
     * designation data  get by category.
     * Route: /hr 
     * Method: get 
     * @return  view
     */
    public function designationByCategorySalary(Request $request)
    {
        //echo $request->start_date;die;
        if ($request->category == 'all') {
            $data = Designation::where('status', '1')->get();
        } else {
            $data = Designation::where('category', $request->category)->where('status', '1')->get();
        }
        //print_r($data);die; 
        $return_array = compact('data');
        return json_encode($return_array);
    }
    /**
     * designation data  get by category.
     * Route: /hr 
     * Method: get 
     * @return  view
     */
    public function salary_generate(Request $request)
    {

        $company_id = $request->company;
        $monthName = date('F', mktime(0, 0, 0, $request->ledger_month, 10));
        $rules = [
            'salary_month' => ['required'],
            'salary_year' => ['required'],
            'company' => ['required'],


        ];
        $customMessages = [
            'required' => ':Attribute  is required.',
        ];


        $this->validate($request, $rules, $customMessages);
        DB::beginTransaction();
        try {

            $company_id = $request->company;
            $globaldate = $request->created_at;
            $select_date = $request->select_date;
            $current_date = date("Y-m-d", strtotime(convertDate($globaldate)));
            $entry_date = date("Y-m-d", strtotime(convertDate($select_date)));
            $entry_time = date("H:i:s", strtotime(convertDate($globaldate)));
            $date_create = $entry_date . ' ' . $entry_time;
            $created_at = date("Y-m-d H:i:s", strtotime(convertDate($date_create)));
            $updated_at = date("Y-m-d H:i:s", strtotime(convertDate($date_create)));
            Session::put('created_at', $created_at);

            $check_data = EmployeeSalaryLeaser::where('month', $request->salary_month)->where('is_deleted', 0)->where('company_id', $company_id)->where('year', $request->salary_year);
            $check_data = $check_data->count();
            if ($check_data > 0) {
                return back()->with('alert', 'The salary of the employee has already been generated from this detail. Please change the details.')->withInput($request->all());
            }

            $getCurentMont = date("m", strtotime(convertDate($request->created_at)));
            $getCurentYear = date("Y", strtotime(convertDate($request->created_at)));
            $ledgerMontget = $request->salary_month;
            $ledgerYearget = $request->salary_year;
            //echo $getCurentMont.'=='.$getCurentYear.'=='.$ledgerMontget.'=='.$ledgerYearget;die;
            if ($ledgerYearget == $getCurentYear && $ledgerMontget > $getCurentMont) {
                return back()->with('alert', "You can not create ledger in future date!")->withInput($request->all());
            }
            $leaserdata['company_id'] =   $company_id;
            $leaserdata['month'] = $request->salary_month;
            $leaserdata['month_name'] = $monthName = $request->salary_month_name;
            $leaserdata['year'] = $request->salary_year;
            $leaserdata['day'] = $request->salary_day;
            $leaserdata['total_paybale_amount'] = $request->salary_to_sum;
            $leaserdata['total_esi_amount'] = $request->esi_to_sum;
            $leaserdata['total_pf_amount'] = $request->pf_to_sum;
            $leaserdata['total_tds_amount'] = $request->tds_to_sum;
            $leaserdata['total_transfer_amount'] = $request->transfer_to_sum;
            $leaserdata['total_amount'] = $total_amount = $request->transfer_to_sum;
            $leaserdata['status'] = 0;
            $leaserdata['created_at'] = $created_at;
            $leaserdata['updated_at'] = $created_at;
            $create1 = EmployeeSalaryLeaser::create($leaserdata);
            $leaser = $create1->id;
            $payment_mode = 3;
            $payment_type = 'CR';
            $currency_code = 'INR';
            $created_by = 1;
            $created_by_id = \Auth::user()->id;
            $randNumber = mt_rand(0, 999999999999999);
            $v_no = $randNumber;
            $v_date = $entry_date;

            // head  entry----------------------------
            $des = 'Salary Ledger of ' . $monthName . ' ' . $request->salary_year;
            $type = 12;
            $sub_type = 121;
            $type_id = $leaser;
            if (isset($_POST['salary'])) {
                $daybookRef = CommanController::createBranchDayBookReferenceNew($total_amount, $globaldate);
                $refId = $daybookRef;

                foreach (($_POST['salary']) as $key => $option) {
                    $empID = $_POST['employee_id'][$key];
                    $empDetail = Employee::where('id', $empID)->first();
                    $data['leaser_id'] = $create1->id;
                    $data['employee_id'] = $empID;
                    $branch_id = $empDetail->branch_id;
                    if ($empDetail->ssb_id) {
                        $data['employee_ssb'] = getSsbAccountNumber($empDetail->ssb_id)->account_no;
                        $data['employee_ssb_id'] = $empDetail->ssb_id;
                    } else {
                        $data['employee_ssb'] = NULL;
                        $data['employee_ssb_id'] = NULL;
                    }
                    $data['company_id'] = $company_id;
                    $data['employee_bank'] = $empDetail->bank_name;
                    $data['employee_bank_ac'] = $empDetail->bank_account_no;
                    $data['employee_bank_ifsc'] = $empDetail->bank_ifsc_code;
                    $data['month'] = $request->salary_month;
                    $data['month_name'] = $request->salary_month_name;
                    $data['year'] = $request->salary_year;
                    $data['branch_id'] = $empDetail->branch_id;
                    $data['designation_id'] = $empDetail->designation_id;
                    $data['category'] = $empDetail->category;
                    $data['day'] = $request->salary_day;
                    $data['fix_salary'] = $_POST['salary'][$key];
                    $data['leave'] = $_POST['leave'][$key];
                    $data['total_salary'] = $_POST['total_salary'][$key];
                    $data['deduction'] = $_POST['deduction'][$key];
                    $data['incentive_bonus'] = $_POST['incentive_bonus'][$key];
                    $data['paybale_amount'] = $_POST['transfer_salary'][$key];
                    $data['esi_amount'] = $_POST['esi_amount'][$key];
                    $data['pf_amount'] = $_POST['pf_amount'][$key];
                    $data['tds_amount'] = $_POST['tds_amount'][$key];
                    $data['final_paybale_amount'] = $_POST['final_payable_amount'][$key];
                    $data['transfer_salary'] = $_POST['final_payable_amount'][$key];
                    $data['actual_transfer_amount'] = $_POST['final_payable_amount'][$key];
                    $data['ledger_create_ref_id'] = $refId;
                    $data['created_at'] = date("Y-m-d H:i:s", strtotime(convertDate($created_at)));
                    $data['updated_at'] = date("Y-m-d H:i:s", strtotime(convertDate($updated_at)));
                    $create = EmployeeSalary::create($data);
                    $TranId = $tranId = $create->id;

                    // Employee Ledger CR KI ENTRY JAYEGI
                    //---    paybale amount -----------
                    $data['company_id'] = $company_id;
                    $val['employee_id'] = $empID;
                    $val['branch_id'] = $empDetail->branch_id;
                    $val['type'] = 6;
                    $val['type_id'] = $TranId;
                    $val['deposit'] = $_POST['transfer_salary'][$key];
                    $val['description'] = $des;
                    $val['currency_code'] = 'INR';
                    $val['payment_type'] = 'CR';
                    $val['payment_mode'] = $payment_mode;
                    $val['status'] = 1;
                    $val['v_no'] = $v_no;
                    $val['v_date'] = $v_date;
                    $val['created_at'] = date("Y-m-d H:i:s", strtotime(convertDate($created_at)));
                    $val['updated_at'] = date("Y-m-d H:i:s", strtotime(convertDate($updated_at)));
                    $val['daybook_ref_id'] = $refId;

                    $createEmployeeledger = EmployeeLedger::create($val);
                    $branch_id = $empDetail->branch_id;
                    $bank_id = NULL;
                    $bank_ac_id = NULL;
                    $associate_id = NULL;
                    $member_id = NULL;
                    $branch_id_to = NULL;
                    $branch_id_from = NULL;
                    $ssb_account_id_from = NULL;
                    $cheque_no = NULL;
                    $transction_no = NULL;
                    $jv_unique_id = NULL;
                    $ssb_account_id_to = NULL;
                    $ssb_account_tran_id_to = NULL;
                    $ssb_account_tran_id_from = NULL;
                    $cheque_type = NULL;
                    $cheque_id = NULL;


                    //expence
                    $head12 = 4;
                    $head22 = 86;
                    $head32 = 37;
                    $head42 = NULL;
                    $head52 = NULL;
                    $allTran2 = CommanController::newheadTransactionCreate($refId, $branch_id, $bank_id, $bank_ac_id, $head32, $type, $sub_type, $type_id, $associate_id, $member_id, $branch_id_to, $branch_id_from,  $_POST['transfer_salary'][$key],  $des, 'DR', $payment_mode, $currency_code,  $v_no,  $ssb_account_id_from, $cheque_no,   $transction_no,  $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $tranId, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $company_id);

                    ///libility
                    $head11 = 1;
                    $head21 = 8;
                    $head31 = 21;
                    $head41 = 61;
                    $head51 = NULL;
                    $allTran1 = CommanController::newheadTransactionCreate($refId, $branch_id, $bank_id, $bank_ac_id, $head41, $type, $sub_type, $type_id, $associate_id = NULL, $member_id, $branch_id_to = NULL, $branch_id_from = NULL,  $_POST['transfer_salary'][$key], $des, 'CR', $payment_mode, $currency_code,  $v_no, $ssb_account_id_from, $cheque_no, $transction_no,  $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $tranId, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $company_id);

                    // ---- esi  amount --------------------------
                    if ($_POST['esi_amount'][$key] > 0) {
                        $des_esi = 'ESI  Deduction of ' . $monthName . ' ' . $request->salary_year;

                        $esi['employee_id'] = $empID;
                        $esi['branch_id'] = $empDetail->branch_id;
                        $esi['type'] = 8;
                        $esi['type_id'] = $TranId;
                        $esi['withdrawal'] = $_POST['esi_amount'][$key];
                        $esi['description'] = $des_esi;
                        $esi['currency_code'] = 'INR';
                        $esi['payment_type'] = 'DR';
                        $esi['payment_mode'] = $payment_mode;
                        $esi['status'] = 1;
                        $esi['v_no'] = $v_no;
                        $esi['created_at'] = date("Y-m-d H:i:s", strtotime(convertDate($created_at)));
                        $esi['updated_at'] = date("Y-m-d H:i:s", strtotime(convertDate($updated_at)));
                        $esi['daybook_ref_id'] = $refId;
                        $createEmployeeledgerEsi = EmployeeLedger::create($esi);
                        $allTran1 = CommanController::newheadTransactionCreate($refId, $branch_id, $bank_id, $bank_ac_id, 325, 12, 124, $type_id, $associate_id = NULL, $member_id, $branch_id_to = NULL, $branch_id_from = NULL,  $_POST['esi_amount'][$key],  $des_esi, 'CR', $payment_mode, $currency_code,  $v_no, $ssb_account_id_from, $cheque_no, $transction_no,  $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $tranId, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $company_id);

                        // salary creditor -- 141
                        $allTran1 = CommanController::newheadTransactionCreate($refId, $branch_id, $bank_id, $bank_ac_id, 61, 12, 124, $type_id, $associate_id = NULL, $member_id, $branch_id_to = NULL, $branch_id_from = NULL,  $_POST['esi_amount'][$key],  $des_esi, 'DR', $payment_mode, $currency_code,  $v_no, $ssb_account_id_from, $cheque_no,  $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $tranId, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $company_id);
                    }

                    // ---- PF  amount --------------------------
                    if ($_POST['pf_amount'][$key] > 0) {

                        $des_pf = 'PF  Deduction of ' . $monthName . ' ' . $request->salary_year;
                        $pf['employee_id'] = $empID;
                        $pf['branch_id'] = $empDetail->branch_id;
                        $pf['type'] = 9;
                        $pf['type_id'] = $TranId;
                        $pf['withdrawal'] = $_POST['pf_amount'][$key];
                        $pf['description'] = $des_pf;
                        $pf['currency_code'] = 'INR';
                        $pf['payment_type'] = 'DR';
                        $pf['payment_mode'] = $payment_mode;
                        $pf['status'] = 1;
                        $pf['v_no'] = $v_no;
                        $pf['created_at'] = date("Y-m-d H:i:s", strtotime(convertDate($created_at)));
                        $pf['updated_at'] = date("Y-m-d H:i:s", strtotime(convertDate($updated_at)));
                        $pf['daybook_ref_id'] = $refId;
                        $createEmployeeledgerPf = EmployeeLedger::create($pf);
                        $allTran1 = CommanController::newheadTransactionCreate($refId, $branch_id, $bank_id, $bank_ac_id, 331, 12, 125, $type_id, $associate_id = NULL, $member_id, $branch_id_to = NULL, $branch_id_from = NULL,  $_POST['pf_amount'][$key],  $des_pf, 'CR', $payment_mode, $currency_code,  $v_no,  $ssb_account_id_from, $cheque_no,  $transction_no,  $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $tranId, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $company_id);
                        // salary creditor -- 141
                        $allTran1 = CommanController::newheadTransactionCreate($refId, $branch_id, $bank_id, $bank_ac_id, 61, 12, 125, $type_id, $associate_id = NULL, $member_id, $branch_id_to = NULL, $branch_id_from = NULL,  $_POST['pf_amount'][$key],  $des_pf, 'DR', $payment_mode, $currency_code,  $v_no, $ssb_account_id_from, $cheque_no,  $transction_no,  $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $tranId, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $company_id);
                    }

                    // ---- TDS  amount --------------------------
                    if ($_POST['tds_amount'][$key] > 0) {
                        $des_tds = 'TDS Deduction of ' . $monthName . ' ' . $request->salary_year;
                        $tds['employee_id'] = $empID;
                        $tds['branch_id'] = $empDetail->branch_id;
                        $tds['type'] = 10;
                        $tds['type_id'] = $TranId;
                        $tds['withdrawal'] = $_POST['tds_amount'][$key];
                        $tds['description'] = $des_tds;
                        $tds['currency_code'] = 'INR';
                        $tds['payment_type'] = 'DR';
                        $tds['payment_mode'] = $payment_mode;
                        $tds['status'] = 1;
                        $tds['v_no'] = $v_no;
                        $tds['created_at'] = date("Y-m-d H:i:s", strtotime(convertDate($created_at)));
                        $tds['updated_at'] = date("Y-m-d H:i:s", strtotime(convertDate($updated_at)));
                        $tds['daybook_ref_id'] = $refId;
                        $createEmployeeledgertds = EmployeeLedger::create($tds);

                        $allTran1 = CommanController::newheadTransactionCreate($refId, $branch_id, $bank_id, $bank_ac_id, 327, 12, 126, $type_id, $associate_id = NULL, $member_id, $branch_id_to = NULL, $branch_id_from = NULL,  $_POST['tds_amount'][$key],  $des_tds, 'CR', $payment_mode, $currency_code,  $v_no,  $ssb_account_id_from, $cheque_no,  $transction_no,  $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $tranId, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $company_id);
                        // salary creditor -- 141
                        $allTran1 = CommanController::newheadTransactionCreate($refId, $branch_id, $bank_id, $bank_ac_id, 61, 12, 126, $type_id, $associate_id = NULL, $member_id, $branch_id_to = NULL, $branch_id_from = NULL,  $_POST['tds_amount'][$key],  $des_tds, 'DR', $payment_mode, $currency_code,  $v_no,  $ssb_account_id_from, $cheque_no,  $transction_no,  $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $tranId, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $company_id);
                    }
                }
            }

            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('alert', $ex->getMessage());
        }

        return redirect('admin/hr/salary/transfer/' . $leaser)->with('success', 'Employee Salary Generated  Successfully');
    }
    /**
     * Show Salary.
     * Route: admin/hr/salary
     * Method: get 
     * @return  array()  Response
     */
    public function transfer($id)
    {
        $data['title'] = 'Salary Management | Employee Salary List';
        $data['leaser_id'] = $id;

        $data['leaserData'] = EmployeeSalaryLeaser::with('company:id,name')->where('id', $id)->first();
        $leaserDate = date("Y/m/d", strtotime($data['leaserData']->created_at));
        $data['salary_list'] = EmployeeSalary::with('salary_employee')->with(['advance' => function ($q) use ($leaserDate) {
            $q->where('status_date', '<=', $leaserDate)->where('settle_amount', '>', 0)->where('type', 4)->where('sub_type', 42)->where('status', 1)->select('id', 'type_id', 'settle_amount', 'status_date');
        }])->with(['salary_branch' => function ($query) {
            $query->select('id', 'name', 'branch_code', 'sector', 'regan', 'zone');
        }])->where('leaser_id', $id)->whereIn('is_transferred', ['2', '0'])->where('is_deleted', 0)->where('transfer_salary', '>', 0)->orderBy('branch_id')->get();
        // pd($data['salary_list']->toArray());
        $companyId = $data['leaserData']['company']->id;
        $data['branch'] = \App\Models\CompanyBranch::with('branch:id,name')->where('company_id', $companyId)->get();


        return view('templates.admin.hr_management.salary.transfer', $data);
    }
    public function transfer_next(Request $request)
    {

        $data['title'] = 'Salary Management | Salary Transfer List';
        $data['branch'] = \App\Models\Branch::where('status', 1)->get();
        $data['leaser_id'] = $request->leaser_id;
        $data['amount_mode'] = $request->amount_mode;
        $select_id_get = rtrim($request->select_id, ',');
        $select_id = explode(",", $select_id_get);
        //print_r($_POST);die;
        $data['salary_list'] = EmployeeSalary::with(['salary_employee' => function ($query) {
            $query->select('id', 'category', 'employee_name', 'employee_code', 'created_at', 'designation_id', 'branch_id', 'esi_account_no', 'pf_account_no', 'salary', 'pen_card', 'bank_name', 'bank_account_no', 'bank_ifsc_code', 'ssb_account');
        }])->with(['salary_branch' => function ($query) {
            $query->select('id', 'name', 'branch_code', 'sector', 'regan', 'zone');
        }])->whereIn('id', $select_id)->orderBy('branch_id')->get();
        $data['selectedSalary'] = $select_id_get;
        $check_data = \App\Models\EmployeeSalaryLeaser::where('id', $request->leaser_id)->first();
        $data['leaserData'] = \App\Models\EmployeeSalaryLeaser::with('company:id,name')->where('id', $request->leaser_id)->first();
        $companyId = $data['leaserData']['company']->id;
        $data['bank'] = \App\Models\SamraddhBank::where('status', 1)->where('company_id', $companyId)->get();
        $data['ledger_date'] = date("d/m/Y", strtotime($check_data->created_at));

        return view('templates.admin.hr_management.salary.transfer_save', $data);
    }

    public function transfer_save(Request $request)
    {

        $company_id = $request->company_id;
        $rules = [
            'company_id' => ['required'],
            'leaser_id' => ['required'],
            'total_transfer' => ['required'],
            'amount_mode' => ['required'],
        ];
        $customMessages = [
            'required' => ':Attribute  is required.',
        ];
        $this->validate($request, $rules, $customMessages);

        DB::beginTransaction();
        try {
            // print_r($_POST);die;
            $company_id = $request->company_id;
            $globaldate = $request->created_at;
            $neft_charge = 0;
            $leaser_id = $ledger_id = $type_id = $request->leaser_id;
            $total_transfer_amount = 0;
            $currency_code = 'INR';
            $select_date = $request->select_date;
            $current_date = date("Y-m-d", strtotime(convertDate($globaldate)));
            $entry_date = date("Y-m-d", strtotime(convertDate($select_date)));
            $entry_time = date("H:i:s", strtotime(convertDate($globaldate)));
            $date_create = $entry_date . ' ' . $entry_time;
            $created_at = date("Y-m-d H:i:s", strtotime(convertDate($date_create)));
            $updated_at = date("Y-m-d H:i:s", strtotime(convertDate($date_create)));
            Session::put('created_at', $created_at);
            $created_by = 1;
            $created_by_id = \Auth::user()->id;
            $created_by_name = \Auth::user()->username;
            $randNumber = mt_rand(0, 999999999999999);
            $v_no = $randNumber;
            $v_date = $entry_date;
            $type = 12;
            $sub_type = 122;

            $daybookRef = CommanController::createBranchDayBookReferenceNew($request->total_transfer_amount, $created_at);

            if ($request->amount_mode == 2) { /// bank 
                $bank_id_from_c = $request->bank_id;
                $bank_ac_id_from_c = $request->account_id;

                if (isset($_POST['salary_id'])) {
                    foreach (($_POST['salary_id']) as $key => $option) {
                        $salaryId = $_POST['salary_id'][$key];
                        $salaryDetail = EmployeeSalary::with('salary_employee')->where('id', $salaryId)->first();
                        //=-----------------------------
                        $salaryPaymentBalance = $salaryDetail->balance;
                        $employeeBalance = $salaryDetail['salary_employee']->bill_current_balance;
                        $empID = $salaryDetail['salary_employee']->id;
                        $bank_name_to = $salaryDetail->employee_bank;
                        $bank_ac_to = $salaryDetail->employee_bank_ac;
                        $bank_ifsc_to = $salaryDetail->employee_bank_ifsc;
                        $v_no = NULL;
                        $v_date = NULL;
                        $ssb_account_id_from = NULL;
                        $ssb_account_id_to = NULL;
                        $cheque_no = NULL;
                        $transction_no = NULL;
                        $jv_unique_id = NULL;
                        $ssb_account_tran_id_to = NULL;
                        $ssb_account_tran_id_from = NULL;
                        $cheque_type = NULL;
                        $cheque_id = NULL;
                        $member_id = NULL;
                        $amount_to_id = NULL;
                        $amount_to_name = NULL;
                        $amount_from_id = NULL;
                        $amount_from_name = NULL;
                        $bank_id_from = $request->bank_id;
                        $bank_ac_id_from = $request->account_id;
                        $bank_id = $bank_id_from;
                        $bank_id_ac = $bank_ac_id = $bank_ac_id_from;
                        $bankfrmDetail = \App\Models\SamraddhBank::where('id', $bank_id_from)->first();
                        $bankacfrmDetail = \App\Models\SamraddhBankAccount::where('id', $bank_ac_id_from)->first();
                        $des = $salaryDetail['salary_employee']->employee_name . "'s " . $salaryDetail->month_name . '' . $salaryDetail->year . ' salary payment has been transferred to the ' . $bank_name_to . ' A/c' . $bank_ac_to;
                        $branch_id = $salaryDetail->branch_id;
                        $branchCode = getBranchCode($branch_id)->branch_code;
                        $empCurrentBalance = $salaryDetail['salary_employee']->current_balance;
                        // $salaryAmount=$salaryDetail->actual_transfer_amount-$salaryDetail->transferred_salary;
                        $salaryAmount = $actualSalaryAmount = $salaryDetail->actual_transfer_amount - $salaryDetail->transferred_salary;
                        $total_transfer_amount = $total_transfer_amount + $actualSalaryAmount;
                        $payment_type = 'CR'; //------------
                        $refId = $daybookRef;
                        $salary['company_bank'] = $bank_id_from;
                        $salary['company_bank_ac'] = $bank_ac_id_from;
                        $salary['transferred_salary'] = $salaryAmount + $salaryDetail->transferred_salary;
                        $salary['transferred_in'] = $request->amount_mode;
                        $salary['is_transferred'] = 1;
                        $salary['transferred_date'] = $entry_date;
                        $salary['employee_bank'] = $bank_name_to;
                        $salary['employee_bank_ac'] = $bank_ac_to;
                        $salary['employee_bank_ifsc'] = $bank_ifsc_to;
                        $salary['payment_mode'] = $paymentMode = $request->payment_mode;
                        $salary['salary_daybook_ref_id'] = $refId;
                        $salary['balance'] = $salaryPaymentBalance - $salaryAmount;
                        $empLedger['to_bank_name'] = $bank_name_to;
                        $empLedger['to_bank_ac_no'] = $bank_ac_to;
                        $empLedger['to_bank_ifsc'] = $bank_ifsc_to;
                        $empLedger['from_bank_name'] = $bankfrmDetail->bank_name;
                        $empLedger['from_bank_ac_no'] = $bankacfrmDetail->account_no;
                        $empLedger['from_bank_ifsc'] = $bankacfrmDetail->ifsc_code;
                        $empLedger['from_bank_id'] = $bank_id_from;
                        $empLedger['from_bank_ac_id'] = $bank_ac_id_from;
                        $bill['to_bank_name'] = $bank_name_to;
                        $bill['to_bank_branch'] = NULL;
                        $bill['to_bank_ac_no'] = $bank_ac_to;
                        $bill['to_bank_ifsc'] = $bank_ifsc_to;
                        $bill['to_bank_id'] = NULL;
                        $bill['to_bank_account_id'] = NULL;
                        $bill['from_bank_name'] = $bankfrmDetail->bank_name;
                        $bill['from_bank_branch'] = NULL;
                        $bill['from_bank_ac_no'] = $bankacfrmDetail->account_no;
                        $bill['from_bank_ifsc'] = $bankacfrmDetail->ifsc_code;
                        $bill['from_bank_id'] = $bank_id_from;
                        $bill['from_bank_ac_id'] = $bank_ac_id_from;
                        if ($request->payment_mode == 1) {
                            $salary['company_cheque_id'] = $cheque_id = $request->cheque_id;
                            $salary['company_cheque_no'] = $cheque_no = $request->cheque_number;
                            $empLedger['cheque_id'] = $cheque_id;
                            $empLedger['cheque_no'] = $cheque_no;
                            $empLedger['cheque_date'] = $entry_date;
                            $empLedger['payment_mode'] = 1;
                            $bill['payment_mode'] = 1;
                            $bill['cheque_id_company'] = $cheque_id;
                            $bill['cheque_no_company'] = $cheque_no;
                            $bill['cheque_date'] = $entry_date;
                            $cheque_no = $cheque_no;
                            $cheque_date = $entry_date;
                            $cheque_bank_from = $bankfrmDetail->bank_name;
                            $cheque_bank_ac_from = $bankacfrmDetail->account_no;
                            $cheque_bank_ifsc_from = $bankacfrmDetail->ifsc_code;
                            $cheque_bank_branch_from = $bankacfrmDetail->branch_name;
                            $cheque_bank_from_id = $bank_id_from;
                            $cheque_bank_ac_from_id = $bank_ac_id_from;
                            $cheque_bank_to = NULL;
                            $cheque_bank_ac_to = NULL;
                            $cheque_bank_to_name = $bank_name_to;
                            $cheque_bank_to_branch = NULL;
                            $cheque_bank_to_ac_no = $bank_ac_to;
                            $cheque_bank_to_ifsc = $bank_ifsc_to;
                            //-----------------------
                            $chequeIssue['cheque_id'] = $cheque_id;
                            $chequeIssue['type'] = 5;
                            $chequeIssue['sub_type'] = 51;
                            $chequeIssue['type_id'] = $type_id;
                            $chequeIssue['cheque_issue_date'] = $entry_date;
                            $chequeIssue['created_at'] = $created_at;
                            $chequeIssue['updated_at'] = $updated_at;
                            $chequeIssueCreate = \App\Models\SamraddhChequeIssue::create($chequeIssue);
                            //------------------ 
                            $cheque_type = 1;
                            $cheque_id = $cheque_id;
                            $chequeUpdate['is_use'] = 1;
                            $chequeUpdate['status'] = 3;
                            $chequeUpdate['updated_at'] = $updated_at;
                            $chequeDataUpdate = \App\Models\SamraddhCheque::find($cheque_id);
                            $chequeDataUpdate->update($chequeUpdate);
                        } else {
                            $salary['online_transaction_no'] = $transction_no = $request->utr_tran;
                            $salary['neft_charge'] = $request->neft_charge;
                            $neft_charge = $request->neft_charge;
                            $empLedger['transaction_no'] = $transction_no;
                            $empLedger['transaction_date'] = $transaction_date = $transction_date = $entry_date;
                            //$empLedger['transaction_charge']=$neft_charge;
                            $empLedger['payment_mode'] = 2;
                            $bill['payment_mode'] = 2;
                            $bill['transaction_no'] = $transction_no;
                            $bill['transaction_date'] = $transaction_date;
                            $bill['transaction_charge'] = $neft_charge;
                            $transction_bank_from = $bankfrmDetail->bank_name;
                            $transction_bank_ac_from = $bankacfrmDetail->account_no;
                            $transction_bank_ifsc_from = $bankacfrmDetail->ifsc_code;
                            $transction_bank_branch_from = $bankacfrmDetail->branch_name;
                            $transction_bank_from_id = $bank_id_from;
                            $transction_bank_from_ac_id = $bank_ac_id_from;
                            $transction_bank_to = NULL;
                            $transction_bank_ac_to = NULL;
                            $transction_bank_to_name = $bank_name_to;
                            $transction_bank_to_branch = NULL;
                            $transction_bank_to_ac_no = $bank_ac_to;
                            $transction_bank_to_ifsc = $bank_ifsc_to;
                            // bank charge head entry +
                        }
                        //------------------------libility -(mines)  ------------
                        $head11 = 1;
                        $head21 = 8;
                        $head31 = 21;
                        $head51 = NULL;
                        $head41 = 61;
                        $allTran1 = CommanController::newHeadTransactionCreate($refId, $branch_id, $bank_id, $bank_ac_id, $head41, $type, $sub_type, $type_id, $associate_id = NULL, $member_id, $branch_id_to = NULL, $branch_id_from = NULL,  $actualSalaryAmount,  $des, 'DR', $paymentMode, $currency_code,  $v_no,  $ssb_account_id_from, $cheque_no,  $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $salaryId, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $company_id);
                        $description_dr = $salaryDetail['salary_employee']->employee_name . 'A/c Dr ' . $salaryAmount . '/-';
                        $description_cr = 'To ' . $bankfrmDetail->bank_name . '(' . $bankacfrmDetail->account_no . ') A/c Cr ' . $salaryAmount . '/-';
                        // ---------------- branch daybook entry -----------------
                        $brDaybook = CommanController::branchDaybookCreateModified($refId, $branch_id, $type, $sub_type, $type_id, $associate_id = NULL, $member_id = NULL, $branch_id_to = NULL, $branch_id_from = NULL,  $salaryAmount,  $des, $description_dr, $description_cr, 'DR', $paymentMode, $currency_code,  $v_no,  $ssb_account_id_from, $cheque_no,  $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $type_transaction_id = NULL, $ssb_account_id_to, $company_id);


                        // ------------------ samraddh bank entry -(mines) ---------------
                        $bankAmountRent = $neft_charge + $salaryAmount;
                        $description_dr_b = $salaryDetail['salary_employee']->employee_name . 'A/c Dr ' . $salaryAmount . '/-';
                        $description_cr_b = 'To ' . $bankfrmDetail->bank_name . '(' . $bankacfrmDetail->account_no . ') A/c Cr ' . $salaryAmount . '/-';
                        $allTran2 = CommanController::newHeadTransactionCreate($refId, $branch_id, $bank_id, $bank_ac_id, $bankfrmDetail->account_head_id, $type, $sub_type, $type_id, $associate_id = NULL, $member_id, $branch_id_to = NULL, $branch_id_from = NULL,  $salaryAmount,  $des, 'CR', $paymentMode, $currency_code,  $v_no,  $ssb_account_id_from, $cheque_no,  $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $salaryId, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $company_id);

                        $smbdc = CommanController::NewFieldAddSamraddhBankDaybookCreateModify($refId, $bank_id, $bank_id_ac, $type, $sub_type, $type_id, $associate_id = NULL, $member_id = NULL, $branch_id, $opening_balance = NULL, $salaryAmount, $closing_balance = NULL, $des, $description_dr_b, $description_cr_b, 'DR', $paymentMode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date = NULL, $ssb_account_id_from, $cheque_no, $cheque_date = NULL, $cheque_bank_from = NULL, $cheque_bank_ac_from = NULL, $cheque_bank_ifsc_from = NULL, $cheque_bank_branch_from = NULL, $cheque_bank_to = NULL, $cheque_bank_ac_to = NULL, $transction_no, $transction_bank_from = NULL, $transction_bank_ac_from = NULL, $transction_bank_ifsc_from = NULL, $transction_bank_branch_from = NULL, $transction_bank_to = NULL, $transction_bank_ac_to = NULL, $transction_date = NULL, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $salaryId, $ssb_account_id_to, $cheque_bank_from_id = NULL, $cheque_bank_ac_from_id = NULL, $cheque_bank_to_name = NULL, $cheque_bank_to_branch = NULL, $cheque_bank_to_ac_no = NULL, $cheque_bank_to_ifsc = NULL, $transction_bank_from_id = NULL, $transction_bank_from_ac_id = NULL, $transction_bank_to_name = NULL, $transction_bank_to_ac_no = NULL, $transction_bank_to_branch = NULL, $transction_bank_to_ifsc = NULL, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $jv_unique_id, $cheque_type, $cheque_id, $company_id);
                        //  neft entry ------------
                        if ($neft_charge > 0) {
                            $des_neft = "NEFT Charges for the salary payment of " . $salaryDetail->month_name . '' . $salaryDetail->year;
                            $description_dr_b_neft = $bankfrmDetail->bank_name . '(' . $bankacfrmDetail->account_no . ') A/c Dr ' . $neft_charge . '/-';
                            $description_cr_b_neft = 'To NFFT A/c Cr ' . $neft_charge . '/-';
                            $allTranSSB = CommanController::newHeadTransactionCreate($refId, 29, $bank_id, $bank_ac_id, 92, $type, $sub_type, $type_id, $associate_id = NULL, $member_id, $branch_id_to = NULL, $branch_id_from = NULL,  $neft_charge,  $des_neft, 'DR', $paymentMode, $currency_code,  $v_no, $ssb_account_id_from, $cheque_no,  $transction_no,  $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $salaryId, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $company_id);

                            $allTran2 = CommanController::newHeadTransactionCreate($refId, 29, $bank_id, $bank_ac_id, $bankfrmDetail->account_head_id, $type, $sub_type, $type_id, $associate_id = NULL, $member_id, $branch_id_to = NULL, $branch_id_from = NULL, $neft_charge,  $des_neft, 'CR', $paymentMode, $currency_code,  $v_no,  $ssb_account_id_from, $cheque_no,   $transction_no,  $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $salaryId, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $company_id);

                            $smbdc = CommanController::NewFieldAddSamraddhBankDaybookCreateModify($refId, $bank_id, $bank_id_ac, $type, $sub_type, $type_id, $associate_id = NULL, $member_id = NULL, 29, $opening_balance = NULL, $neft_charge, $closing_balance = NULL, $des_neft, $description_dr_b_neft, $description_cr_b_neft, 'DR', $paymentMode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $salaryId, $ssb_account_id_to, $cheque_bank_from_id, $cheque_bank_ac_from_id, $cheque_bank_to_name, $cheque_bank_to_branch, $cheque_bank_to_ac_no, $cheque_bank_to_ifsc, $transction_bank_from_id, $transction_bank_from_ac_id, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $jv_unique_id, $cheque_type, $cheque_id, $company_id);

                            // ---------------- branch daybook entry -----------------
                            $brDaybook = CommanController::branchDaybookCreateModified($refId, 29, $type, $sub_type, $type_id, $associate_id = NULL, $member_id = NULL, $branch_id_to = NULL, $branch_id_from = NULL, $salaryAmount,  $des_neft, $description_dr_b_neft, $description_cr_b_neft, 'DR', $paymentMode, $currency_code,  $v_no,  $ssb_account_id_from, $cheque_no, $transction_no,  $entry_date, $entry_time, $created_by, $created_by_id,  $created_at, $updated_at, $type_transaction_id = NULL, $ssb_account_id_to, $company_id);
                        }

                        $employeeupdate = EmployeeSalary::find($salaryId);
                        $employeeupdate->update($salary);
                        $detail = 'Salary Payment ' . $salaryDetail->month_name . ' ' . $salaryDetail->year;
                        $empLedger['employee_id'] = $empID;
                        $empLedger['branch_id'] = $branch_id;
                        $empLedger['type'] = 1;
                        $empLedger['type_id'] = $salaryId;
                        $empLedger['withdrawal'] = $salaryAmount;
                        $empLedger['description'] = $detail;
                        $empLedger['currency_code'] = $currency_code;
                        $empLedger['payment_type'] = 'DR';
                        $empLedger['daybook_ref_id'] = $refId;
                        $empLedger['created_at'] = $created_at;
                        $empLedger['updated_at'] = $updated_at;
                        $empL = \App\Models\EmployeeLedger::create($empLedger);



                        $sumTransferredAmount = \App\Models\EmployeeSalary::where('employee_id', $empID)->sum('transferred_salary');
                        $sumActualTransferAmount = \App\Models\EmployeeSalary::where('employee_id', $empID)->sum('actual_transfer_amount');

                        $libilityBalance = $sumActualTransferAmount - $sumTransferredAmount;

                        $lib['current_balance'] = $libilityBalance;
                        $lib['bill_current_balance'] = $libilityBalance;
                        $libUpdate = Employee::find($empID);
                        $libUpdate->update($lib);
                    }
                }
                $payment_count = \App\Models\EmployeeSalary::where('leaser_id', $ledger_id)->where('is_transferred', 1)->get();
                $total_payment_count = \App\Models\EmployeeSalary::where('leaser_id', $ledger_id)->get();
                $l = \App\Models\EmployeeSalaryLeaser::where('id', $ledger_id)->first();
                if ($l->transfer_amount > 0) {
                    $ledgertransfer_amount = $total_transfer_amount + $l->transfer_amount;
                } else {
                    $ledgertransfer_amount = $total_transfer_amount;
                }
                $empdataL['transfer_amount'] = $ledgertransfer_amount;
                if ($request->neft_charge > 0) {
                    $empdataL['total_neft'] = $l->total_neft + $request->neft_charge;
                }
                if (count($payment_count) == count($total_payment_count)) {
                    $empdataL['status'] = 1;
                } else {
                    $empdataL['status'] = 2;
                }
                $empdataL['updated_at'] = $updated_at;
                $empdataUpdateL = \App\Models\EmployeeSalaryLeaser::find($leaser_id);
                $empdataUpdateL->update($empdataL);
            } else if ($request->amount_mode == 1) {
                //  ssb -----
                if (isset($_POST['salary_id'])) {
                    foreach (($_POST['salary_id']) as $key => $option) {
                        $salaryId = $_POST['salary_id'][$key];
                        $salaryDetail = EmployeeSalary::with('salary_employee')->where('id', $salaryId)->first();
                        // +------------------------------------------------
                        $salaryPaymentBalance = $salaryDetail->balance;
                        $employeeBalance = $salaryDetail['salary_employee']->bill_current_balance;
                        $empID = $salaryDetail['salary_employee']->id;
                        $empSSBId = $salaryDetail['salary_employee']->ssb_id;
                        $ssbAccountDetail = getSavingAccountMemberId($empSSBId);
                        $ssbBalance = $ssbAccountDetail->balance;
                        $member_id = $ssbAccountDetail->member_id;
                        $empSSBAccount = $ssbAccountDetail->account_no;
                        $salaryAmount = $salaryDetail->actual_transfer_amount - $salaryDetail->transferred_salary;
                        $refId = $daybookRef;
                        $branch_id = $salaryDetail->branch_id;
                        $branchCode = getBranchCode($branch_id)->branch_code;
                        $empCurrentBalance = $salaryDetail['salary_employee']->current_balance;
                        $actualSalaryAmount = $salaryDetail->actual_transfer_amount - $salaryDetail->transferred_salary;
                        $total_transfer_amount = $total_transfer_amount + $actualSalaryAmount;
                        $salary['transferred_salary'] = $salaryAmount + $salaryDetail->transferred_salary;
                        $salary['transferred_in'] = $request->amount_mode;
                        $salary['is_transferred'] = 1;
                        $salary['employee_ssb_id'] = $empSSBId;
                        $salary['employee_ssb'] = $empSSBAccount;
                        $salary['transferred_date'] = $entry_date;
                        $salary['salary_daybook_ref_id'] = $refId;
                        $salary['balance'] = $salaryPaymentBalance - $salaryAmount;
                        $salary['neft_charge'] = $request->neft_charge;
                        $employeeupdate = EmployeeSalary::find($salaryId);
                        $employeeupdate->update($salary);
                        //------------ ssb tran head entry start  --------------------------
                        $detail = 'Salary Payment ' . $salaryDetail->month_name . ' ' . $salaryDetail->year;
                        $ssbTranCalculation = CommanController::SSBDateCR($empSSBId, $empSSBAccount, $ssbBalance, $salaryAmount, $detail, 'INR', 'CR', 3, $branch_id, $associate_id = NULL, 8, $created_at, $refId, $company_id);
                        $ssbBack = CommanController::SSBBackDateCR($empSSBId, $created_at, $salaryAmount);
                        $ssbRentTranID = $ssbTranCalculation;
                        $amountArray = array('1' => $salaryAmount);
                        $deposit_by_name = $created_by_name;
                        $deposit_by_id = $created_by_id;

                        //-------------------- ssb head entry start -----------------
                        $payment_mode = $paymentMode = 3;
                        $payment_type = 'CR';
                        $des = $salaryDetail['salary_employee']->employee_name . "'s " . $salaryDetail->month_name . '' . $salaryDetail->year . ' salary payment has been transferred to the SSB A/c' . $empSSBAccount;
                        $ssb_account_id_from = NULL;
                        $cheque_no = NULL;
                        $type_transaction_id = NULL;
                        $transction_no = NULL;
                        $ssb_account_id_to = $empSSBId;
                        $jv_unique_id = NULL;
                        $ssb_account_tran_id_to = $ssbRentTranID;
                        $ssb_account_tran_id_from = NULL;
                        $cheque_type = NULL;
                        $cheque_id = NULL;
                        $bank_id = NULL;
                        $bank_ac_id = NULL;
                        $head1SSB = 1;
                        $head2SSB = 8;
                        $head3SSB = 20;
                        $head4SSB = 56;
                        $head5SSB = NULL;
                        // ssb head entry +
                        $allTranSSB = CommanController::newheadTransactionCreate($refId, $branch_id, $bank_id, $bank_ac_id, $head4SSB, 4, 410, $empSSBId, $associate_id = NULL, $member_id, $branch_id_to = NULL, $branch_id_from = NULL,  $salaryAmount,  $des, 'CR', $payment_mode, $currency_code, $v_no,  $ssb_account_id_from, $cheque_no,  $transction_no,  $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $ssbRentTranID, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $company_id);
                        // ssb Member transaction  +
                        //------------ ssb tran head entry end --------------------------
                        ///libility
                        $head11 = 1;
                        $head21 = 8;
                        $head31 = 21;
                        $head41 = 61;
                        $head51 = NULL;
                        $allTran1 = CommanController::newHeadTransactionCreate($refId, $branch_id, $bank_id, $bank_ac_id, $head41, $type, $sub_type, $type_id, $associate_id = NULL, $member_id = NULL, $branch_id_to = NULL, $branch_id_from = NULL,  $actualSalaryAmount,  $des, 'DR', $payment_mode, $currency_code,  $v_no,  $ssb_account_id_from, $cheque_no,  $transction_no,  $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $ssbRentTranID, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $company_id);
                        $description_dr = $salaryDetail['salary_employee']->employee_name . 'A/c Dr ' . $salaryAmount . '/-';
                        $description_cr = 'To SSB(' . $empSSBAccount . ') A/c Cr ' . $salaryAmount . '/-';

                        $brDaybook = CommanController::branchDaybookCreateModified($refId, $branch_id, $type, $sub_type, $type_id, $associate_id = NULL, $member_id = NULL, $branch_id_to = NULL, $branch_id_from = NULL,  $salaryAmount,  $des, $description_dr, $description_cr, 'DR', $payment_mode, $currency_code,  $v_no,  $ssb_account_id_from, $cheque_no,  $transction_no,  $entry_date, $entry_time, $created_by, $created_by_id,  $created_at, $updated_at, $type_transaction_id, $ssb_account_id_to, $company_id);


                        $empLedger['employee_id'] = $empID;
                        $empLedger['branch_id'] = $branch_id;
                        $empLedger['type'] = 1;
                        $empLedger['type_id'] = $salaryId;
                        $empLedger['withdrawal'] = $salaryAmount;
                        $empLedger['description'] = $detail;
                        $empLedger['currency_code'] = $currency_code;
                        $empLedger['payment_type'] = 'DR';
                        $empLedger['payment_mode'] = 3;
                        $empLedger['v_no'] = $v_no;
                        $empLedger['v_date'] = $v_date;
                        $empLedger['ssb_account_id_to'] = $ssb_account_id_to;
                        $empLedger['created_at'] = $created_at;
                        $empLedger['updated_at'] = $updated_at;
                        $empLedger['daybook_ref_id'] = $refId;
                        $empL = \App\Models\EmployeeLedger::create($empLedger);



                        $sumTransferredAmount = \App\Models\EmployeeSalary::where('employee_id', $empID)->sum('transferred_salary');
                        $sumActualTransferAmount = \App\Models\EmployeeSalary::where('employee_id', $empID)->sum('actual_transfer_amount');

                        $libilityBalance = $sumActualTransferAmount - $sumTransferredAmount;

                        $lib['current_balance'] = $libilityBalance;
                        $lib['bill_current_balance'] = $libilityBalance;
                        $libUpdate = Employee::find($empID);
                        $libUpdate->update($lib);
                    }
                }
                $payment_count = \App\Models\EmployeeSalary::where('leaser_id', $ledger_id)->where('is_transferred', 1)->get();
                $total_payment_count = \App\Models\EmployeeSalary::where('leaser_id', $ledger_id)->get();
                $l = \App\Models\EmployeeSalaryLeaser::where('id', $ledger_id)->first();
                if ($l->transfer_amount > 0) {
                    $ledgertransfer_amount = $total_transfer_amount + $l->transfer_amount;
                } else {
                    $ledgertransfer_amount = $total_transfer_amount;
                }
                $empdataL['transfer_amount'] = $ledgertransfer_amount;
                if (count($payment_count) == count($total_payment_count)) {
                    $empdataL['status'] = 1;
                } else {
                    $empdataL['status'] = 2;
                }
                $empdataL['updated_at'] = $updated_at;
                $empdataUpdateL = \App\Models\EmployeeSalaryLeaser::find($leaser_id);
                $empdataUpdateL->update($empdataL);
            } else {
                $payBranch = $_POST['payment_branch'];
                $v_no = $v_date = NULL;
                //  cash -----
                if (isset($_POST['salary_id'])) {
                    foreach (($_POST['salary_id']) as $key => $option) {
                        $salaryId = $_POST['salary_id'][$key];
                        $salaryDetail = EmployeeSalary::with('salary_employee')->where('id', $salaryId)->first();
                        $salaryAmount = $salaryDetail->actual_transfer_amount - $salaryDetail->transferred_salary;
                        //$daybookRef=CommanController::createBranchDayBookReferenceNew($salaryAmount,$globaldate);
                        $refId = $daybookRef;
                        // +------------------------------------------------
                        $salaryPaymentBalance = $salaryDetail->balance;
                        $employeeBalance = $salaryDetail['salary_employee']->bill_current_balance;
                        $empID = $salaryDetail['salary_employee']->id;
                        $empSSBId = NULL;
                        $member_id = NULL;
                        $branch_id = $salaryDetail->branch_id;
                        $branchCode = getBranchCode($branch_id)->branch_code;
                        $empCurrentBalance = $salaryDetail['salary_employee']->current_balance;
                        $actualSalaryAmount = $salaryDetail->actual_transfer_amount - $salaryDetail->transferred_salary;
                        $total_transfer_amount = $total_transfer_amount + $actualSalaryAmount;
                        $salary['transferred_salary'] = $salaryAmount + $salaryDetail->transferred_salary;
                        $salary['transferred_in'] = 0;
                        $salary['is_transferred'] = 1;
                        $salary['transferred_date'] = $entry_date;
                        $salary['balance'] = $salaryPaymentBalance - $salaryAmount;
                        $salary['salary_daybook_ref_id'] = $refId;
                        $employeeupdate = EmployeeSalary::find($salaryId);
                        $employeeupdate->update($salary);
                        $detail = 'Salary Payment ' . $salaryDetail->month_name . ' ' . $salaryDetail->year;
                        $payment_mode = $paymentMode = 0;
                        $payment_type = 'CR';
                        $des = $salaryDetail['salary_employee']->employee_name . "'s " . $salaryDetail->month_name . '' . $salaryDetail->year . ' salary payment by cash';
                        $ssb_account_id_from = NULL;
                        $cheque_no = NULL;
                        $transction_no = NULL;
                        $ssb_account_id_to = NULL;
                        $jv_unique_id = NULL;
                        $ssb_account_tran_id_to = NULL;
                        $ssb_account_tran_id_from = NULL;
                        $cheque_type = NULL;
                        $cheque_id = NULL;
                        $bank_id = NULL;
                        $bank_ac_id = NULL;
                        $head1Cash = 2;
                        $head2Cash = 10;
                        $head3Cash = 28;
                        $head4Cash = NULL;
                        $head5Cash = NULL;
                        // cash  head entry +
                        $allTranSSB = CommanController::newHeadTransactionCreate($refId, $payBranch, $bank_id, $bank_ac_id, $head3Cash, $type, $sub_type, $type_id, $associate_id = NULL, $member_id, $branch_id_to = NULL, $branch_id_from = NULL,  $salaryAmount,  $des, 'CR', $payment_mode, $currency_code,  $v_no,  $ssb_account_id_from, $cheque_no,  $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $salaryId, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $company_id);
                        //------------ ssb tran head entry end --------------------------
                        ///libility
                        $head11 = 1;
                        $head21 = 8;
                        $head31 = 21;
                        $head41 = 61;
                        $head51 = NULL;
                        $allTran1 = CommanController::newHeadTransactionCreate($refId, $branch_id, $bank_id, $bank_ac_id, $head41, $type, $sub_type, $type_id, $associate_id = NULL, $member_id = NULL, $branch_id_to = NULL, $branch_id_from = NULL, $actualSalaryAmount,  $des, 'DR', $payment_mode, $currency_code,  $v_no, $ssb_account_id_from, $cheque_no, $transction_no,  $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $salaryId, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $company_id);
                        $description_dr = $salaryDetail['salary_employee']->employee_name . 'A/c Dr ' . $salaryAmount . '/-';
                        $description_cr = 'To Branch Cash A/c Cr ' . $salaryAmount . '/-';


                        $brDaybook = CommanController::branchDaybookCreateModified($refId, $payBranch, $type, $sub_type, $type_id, $associate_id = NULL, $member_id = NULL, $branch_id_to = NULL, $branch_id_from = NULL,  $salaryAmount,  $des, $description_dr, $description_cr, 'DR', $payment_mode, $currency_code,  $v_no,  $ssb_account_id_from, $cheque_no, $transction_no,  $entry_date, $entry_time, $created_by, $created_by_id,  $created_at, $updated_at, $type_transaction_id = NULL, $ssb_account_id_to, $company_id);

                        $empLedger['employee_id'] = $empID;
                        $empLedger['branch_id'] = $branch_id;
                        $empLedger['type'] = 1;
                        $empLedger['type_id'] = $salaryId;
                        $empLedger['withdrawal'] = $salaryAmount;
                        $empLedger['description'] = $detail;
                        $empLedger['currency_code'] = $currency_code;
                        $empLedger['payment_type'] = 'DR';
                        $empLedger['payment_mode'] = 0;
                        $empLedger['created_at'] = $created_at;
                        $empLedger['updated_at'] = $updated_at;
                        $empLedger['daybook_ref_id'] = $refId;
                        $empL = \App\Models\EmployeeLedger::create($empLedger);


                        $sumTransferredAmount = \App\Models\EmployeeSalary::where('employee_id', $empID)->sum('transferred_salary');
                        $sumActualTransferAmount = \App\Models\EmployeeSalary::where('employee_id', $empID)->sum('actual_transfer_amount');

                        $libilityBalance = $sumActualTransferAmount - $sumTransferredAmount;

                        $lib['current_balance'] = $libilityBalance;
                        $lib['bill_current_balance'] = $libilityBalance;
                        $libUpdate = Employee::find($empID);
                        $libUpdate->update($lib);
                    }
                }
                $payment_count = \App\Models\EmployeeSalary::where('leaser_id', $ledger_id)->where('is_transferred', 1)->get();
                $total_payment_count = \App\Models\EmployeeSalary::where('leaser_id', $ledger_id)->get();
                $l = \App\Models\EmployeeSalaryLeaser::where('id', $ledger_id)->first();
                if ($l->transfer_amount > 0) {
                    $ledgertransfer_amount = $total_transfer_amount + $l->transfer_amount;
                } else {
                    $ledgertransfer_amount = $total_transfer_amount;
                }
                $empdataL['transfer_amount'] = $ledgertransfer_amount;
                if (count($payment_count) == count($total_payment_count)) {
                    $empdataL['status'] = 1;
                } else {
                    $empdataL['status'] = 2;
                }
                $empdataL['updated_at'] = $updated_at;
                $empdataUpdateL = \App\Models\EmployeeSalaryLeaser::find($leaser_id);
                $empdataUpdateL->update($empdataL);
            }


            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();

            // return back()->with('alert', $ex->getMessage());
        }
        return redirect('admin/hr/salary/transfer/' . $leaser_id)->with('success', 'Employee Salary Transferred  Successfully');
    }
    public function transferred($id)
    {
        $data['title'] = "Salary Management | Employee's Transferred Salary List";
        $data['branch'] = Branch::where('status', 1)->get();
        $data['leaser_id'] = $id;
        $data['leaserData'] = EmployeeSalaryLeaser::with('company:id,name')->where('id', $id)->first();
        return view('templates.admin.hr_management.salary.list', $data);
    }
    /**
     * Get Salary list
     * Route: ajax call from - /admin/hr/salary
     * Method: Post 
     * @param  \Illuminate\Http\Request  $request
     * @return JSON array
     */
    public function salary_listing(Request $request)
    {
        if ($request->ajax()) {
            // fillter array 
            $arrFormData = array();
            //   
            $arrFormData['category'] = $request->category;
            $arrFormData['designation'] = $request->designation;
            $arrFormData['employee_name'] = $request->employee_name;
            $arrFormData['employee_code'] = $request->employee_code;
            $arrFormData['is_search'] = $request->is_search;
            $arrFormData['leaser_id'] = $leaser_id = $request->leaser_id;
            $arrFormData['status'] = $status = $request->status;
            //print_r($arrFormData);die;
            $data = EmployeeSalary::with('salary_employee')->with(['salary_branch' => function ($query) {
                $query->select('id', 'name', 'branch_code', 'sector', 'regan', 'zone');
            }])->where('leaser_id', $leaser_id);
            /******* fillter query start ****/
            if (isset($arrFormData['is_search']) && $arrFormData['is_search'] == 'yes') {
                if ($arrFormData['status'] != '') {
                    $status = $arrFormData['status'];
                    if ($status == 1) {
                        $data = $data->whereIn('is_transferred', ['1', '2']);
                    } else {
                        $data = $data->where('is_transferred', $status);
                    }
                }
                if ($arrFormData['category'] != '') {
                    $category = $arrFormData['category'];
                    if ($category > 0) {
                        $data = $data->where('category', $category);
                    }
                }
                if ($arrFormData['designation'] != '') {
                    $designation = $arrFormData['designation'];
                    if ($designation > 0) {
                        $data = $data->where('designation_id', $designation);
                    }
                }
                if ($arrFormData['employee_name'] != '') {
                    $employee_name = $arrFormData['employee_name'];
                    $data = $data->whereHas('salary_employee', function ($query) use ($employee_name) {
                        $query->where('employees.employee_name', 'LIKE', '%' . $employee_name . '%');
                    });
                }
                if ($arrFormData['employee_code'] != '') {
                    $employee_code = $arrFormData['employee_code'];
                    $data = $data->whereHas('salary_employee', function ($query) use ($employee_code) {
                        $query->where('employees.employee_code', 'LIKE', '%' . $employee_code . '%');
                    });
                }
            }
            $data1 = $data->orderby('created_at', 'DESC')->get();
            $count = count($data1);
            $data = $data->orderby('created_at', 'DESC')->offset($_POST['start'])->limit($_POST['length'])->get();
            $totalCount = EmployeeSalary::count();
            $sno = $_POST['start'];
            $rowReturn = array();
            foreach ($data as $row) {
                $sno++;
                $val['DT_RowIndex'] = $sno;
                $category = 'All';
                $val['branch'] = $row['salary_branch']->name . '(' . $row['salary_branch']->branch_code . ')';

                if ($row->category == 1) {
                    $category = 'On-rolled';
                }
                if ($row->category == 2) {
                    $category = 'Contract';
                }
                $val['category_name'] = $category;
                $val['employee_name'] = $row['salary_employee']->employee_name;
                $val['employee_code'] = $row['salary_employee']->employee_code;
                $val['advance_payment'] = "N/A";
                $val['settle_amount'] = "N/A";
                if ($row->settle_amount > 0) {
                    $val['advance_payment'] = number_format((float) $row->advance_payment, 2, '.', '') . "&#x20B9; ";
                    $val['settle_amount'] = number_format((float) $row->settle_amount, 2, '.', '') . "&#x20B9; ";
                }
                if ($row->designation_id) {
                    $val['designation_name'] = getDesignationData('designation_name', $row->designation_id)->designation_name;
                } else {
                    $val['designation_name'] = 'All';
                }
                $val['fix_salary'] = number_format((float) $row->fix_salary, 2, '.', '') . "&#x20B9; ";
                $val['leave'] = number_format((float) $row->leave, 1, '.', '');
                $val['total_salary'] = number_format((float) $row->total_salary, 2, '.', '') . "&#x20B9; ";
                $val['deduction'] = number_format((float) $row->deduction, 2, '.', '') . "&#x20B9; ";
                $val['incentive_bonus'] = number_format((float) $row->incentive_bonus, 2, '.', '') . "&#x20B9; ";
                $val['paybale_amount'] = number_format((float) $row->paybale_amount, 2, '.', '') . "&#x20B9; ";
                $val['esi_amount'] = number_format((float) $row->esi_amount, 2, '.', '') . "&#x20B9; ";
                $val['pf_amount'] = number_format((float) $row->pf_amount, 2, '.', '') . "&#x20B9; ";
                $val['tds_amount'] = number_format((float) $row->tds_amount, 2, '.', '') . "&#x20B9; ";
                $val['total_payable_salary'] = number_format((float) $row->actual_transfer_amount, 2, '.', '') . "&#x20B9; ";
                $val['transferred_salary'] = number_format((float) $row->transferred_salary, 2, '.', '') . "&#x20B9; ";
                $val['transferred_in'] = 'N/A';
                if ($row->transferred_in == 1) {
                    $val['transferred_in'] = 'SSB';
                }
                if ($row->transferred_in == 2) {
                    $val['transferred_in'] = 'Bank';
                }
                if ($row->transferred_in == 0 && $row->transferred_in != NULL) {
                    $val['transferred_in'] = 'Cash';
                }
                if ($row->transferred_date) {
                    $val['transferred_date'] = date("d/m/Y", strtotime($row->transferred_date));
                } else {
                    $val['transferred_date'] = 'N/A';
                }
                $val['employee_ssb'] = $row->employee_ssb;
                $val['employee_bank'] = $row->employee_bank;
                $val['employee_bank_ac'] = $row->employee_bank_ac;
                $val['employee_bank_ifsc'] = $row->employee_bank_ifsc;
                /** $val['company_ssb']=$row->company_ssb;*/
                $val['company_bank'] = 'N/A';
                $val['company_bank_ac'] = 'N/A';
                if ($row->transferred_in == 2) {
                    $bankfrmDetail = \App\Models\SamraddhBank::where('id', $row->company_bank)->first();
                    $bankacfrmDetail = \App\Models\SamraddhBankAccount::where('id', $row->company_bank_ac)->first();
                    $val['company_bank'] = $bankfrmDetail->bank_name;
                    $val['company_bank_ac'] = $bankacfrmDetail->account_no;
                }
                $val['payment_mode'] = 'N/A';
                if ($row->transferred_in == 2) {
                    if ($row->payment_mode == 1) {
                        $val['payment_mode'] = 'Cheque';
                    }
                    if ($row->payment_mode == 2) {
                        $val['payment_mode'] = 'Online';
                    }
                }
                $val['company_cheque_id'] = 'N/A';
                if ($row->payment_mode == 1 && $row->transferred_in == 2) {
                    $c = \App\Models\SamraddhCheque::where('id', $row->company_cheque_id)->first();
                    $val['company_cheque_id'] = $c->cheque_no;
                }
                $val['online_transaction_no'] = 'N/A';
                $val['online'] = 'N/A';
                $val['neft_charge'] = 'N/A';
                if ($row->payment_mode == 2 && $row->transferred_in == 2) {
                    $val['online_transaction_no'] = $row->online_transaction_no;
                    $val['online'] = $row->online_transaction_no;
                    $val['neft_charge'] = number_format((float) $row->neft_charge, 2, '.', '') . "&#x20B9; ";
                }
                if ($row->is_transferred == 0) {
                    $val['transfer_status'] = 'No';
                } elseif ($row->is_transferred == 1) {
                    $val['transfer_status'] = 'Fully Transferred';
                } else {
                    $val['transfer_status'] =  'Partial Transfer';
                }
                $btn = '<div class="list-icons"><div class="dropdown"><a href="#" class="list-icons-item" data-toggle="dropdown"><i class="icon-menu9"></i></a><div class="dropdown-menu dropdown-menu-right">';
                $url2 = URL::to("admin/hr/salary/advice/" . $row->id . "");
                $ur_edit = URL::to("admin/hr/salary/salary_edit/" . base64_encode($row->id) . "");
                if ($row->is_transferred == 1) {
                    $btn .= '<a class="dropdown-item" href="' . $url2 . '" title="Advice Print"><i class="icon-printer  mr-2"></i>Advice Print</a>  ';
                    // && (Auth::user()->id == 1 || Auth::user()->id == 16 || Auth::user()->id == 14)
                    if ($row->year >= 2023 && check_my_permission(Auth::user()->id, "360") == "1") {
                        if (isset($row->salary_daybook_ref_id) && EmployeeSalary::where('salary_daybook_ref_id', $row->salary_daybook_ref_id)->count() > 1) {
                            // Fetch employees with their salary details
                            $emp_names = EmployeeSalary::where('salary_daybook_ref_id', $row->salary_daybook_ref_id)
                                ->with('salary_employee:id,employee_name')
                                ->select('employee_id')
                                ->get();
                            $employeeNames = $emp_names->pluck('salary_employee.employee_name')->implode(',');
                            // $employeeNames now contains an array of employee names
                            $btn .= '<span class="dropdown-item" onclick="DeleteSalarym(this)" data-sal="' . base64_encode($row->salary_daybook_ref_id) . '" data-emp="' . $employeeNames . '" title="Delete salary payment"><i class="fas fa-trash-alt mr-2"></i>Delete multiple payment</span>';
                        } else {
                            $btn .= '<span class="dropdown-item" onclick="DeleteSalary(this)" data-sal="' . base64_encode($row->id) . '" title="Delete salary payment"><i class="fas fa-trash-alt mr-2"></i>Delete payment</span>';
                        }
                    }
                } elseif ($row->is_transferred == 0 && check_my_permission(Auth::user()->id, "359") == "1") {
                    if ($row->year >= 2023) {
                        $btn .= '<a class="dropdown-item" href="' . $ur_edit . '" title="Edit salary"><i class="far fa-edit  mr-2"></i>Edit</a>';
                    }
                } 
                // else {
                //     if (($row->year >= 2023 && check_my_permission(Auth::user()->id, "360") == "1")) {
                //         $btn .= '<span class="dropdown-item" onclick="DeleteSalary(this)" data-sal="' . base64_encode($row->id) . '" title="Delete salary payment"><i class="fas fa-trash-alt mr-2"></i>Delete payment</span>';
                //     }
                // }
                $btn .= '</div></div></div>';
                $val['action'] = $btn;
                $rowReturn[] = $val;
            }
            $output = array("draw" => $_POST['draw'], "recordsTotal" => $totalCount, "recordsFiltered" => $count, "data" => $rowReturn,);
            return json_encode($output);
        }
    }
    public function advice($id)
    {

        $data['title'] = "Salary Management | Salary Advice";
        $data['row'] = EmployeeSalary::with('company:id,name')->with('salary_employee')->with(['salary_branch' => function ($query) {
            $query->select('id', 'name', 'branch_code', 'sector', 'regan', 'zone');
        }])->with(['salaryBank' => function ($query) {
            $query->select('id', 'bank_name');
        }])->with(['salaryBankAccount' => function ($query) {
            $query->select('id', 'account_no');
        }])->with(['salarySSB' => function ($query) {
            $query->select('id', 'account_no');
        }])->with(['salaryCheque' => function ($query) {
            $query->select('id', 'cheque_no');
        }])->where('id', $id)->first();
        // pd($data['row']->toArray());
        return view('templates.admin.hr_management.salary.print_advice', $data);
    }
    public function advancePayble($salaryId, $ledgerId)
    {
        $data['title'] = 'Salary Management | Salary Transfer List';
        $data['leaser_id'] = $ledgerId;
        //print_r($_POST);die;
        $data['salary_list'] = EmployeeSalary::with('company:id,name')->with('salary_employee')->where('id', $salaryId)->first();
        $company_id = $data['salary_list']->company->id;
        $check_data = \App\Models\EmployeeSalaryLeaser::where('id', $ledgerId)->first();
        $data['ledger_date'] = date("d/m/Y", strtotime($check_data->created_at));

        $data['bank'] = \App\Models\SamraddhBank::where('status', 1)->where('company_id', $company_id)->get();
        $data['branch'] = \App\Models\Branch::where('status', 1)->get();

        $compdate = date("Y/m/d", strtotime($check_data->created_at));
        $advanceBalance = AdvancedTransaction::where('type', 4)->where('sub_type', 42)->where('status', 1)->where('type_id', $data['salary_list']['employee_id'])->where('status_date', '<=', $compdate)->where('settle_amount', '>', 0)->select('settle_amount')->get();
        $data['advanceAmount'] = 0;
        foreach ($advanceBalance as $advanceAmount) {
            $data['advanceAmount'] += $advanceAmount['settle_amount'];
        }

        if ($data['salary_list']->actual_transfer_amount == $data['salary_list']->transferred_salary) {
            return redirect('admin/hr/salary/transfer/' . $ledgerId)->with('alert', 'Payment has already done');
        }
        return view('templates.admin.hr_management.salary.transfer_advance_save', $data);
    }
    /******
     * Transfer Salary with advance Amount adjustment last modification date 26-09-2023 by mahesh
     */
    public function salaryTransferAdvanceSave(Request $request)
    {
        $company_id = $request->company_id;
        if ($request->transfer_amount == 0) {
            $rules = [
                // 'amount_mode' => ['required'],
                'total_transfer_amount' => ['required'],
                'leaser_id' => ['required'],
                // 'bank_id' => ['required'], 
                // 'account_id' => ['required'], 
                'advance_payment' => ['required'],
                'actual_transfer' => ['required'],
                'advance_settel' => ['required'],
                'transfer_amount' => ['required'],
            ];
        } else {
            $rules = [
                'amount_mode' => ['required'],
                'total_transfer_amount' => ['required'],
                'leaser_id' => ['required'],
                // 'bank_id' => ['required'], 
                'company_id' => ['required'],
                'advance_payment' => ['required'],
                'actual_transfer' => ['required'],
                'advance_settel' => ['required'],
                'transfer_amount' => ['required'],
            ];
        }
        $customMessages = [
            'required' => ':Attribute  is required.',
        ];
        $this->validate($request, $rules, $customMessages);
        DB::beginTransaction();
        try {
            $globaldate = $request->created_at;
            $neft_charge = 0;
            $leaser_id = $ledger_id = $type_id = $request->leaser_id;
            $total_transfer_amount = 0;
            $select_date = $request->select_date;
            $current_date = date("Y-m-d", strtotime(convertDate($globaldate)));
            $entry_date = date("Y-m-d", strtotime(convertDate($select_date)));
            $entry_time = date("H:i:s", strtotime(convertDate($globaldate)));
            $date_create = $entry_date . ' ' . $entry_time;
            $created_at = date("Y-m-d H:i:s", strtotime(convertDate($date_create)));
            $updated_at = date("Y-m-d H:i:s", strtotime(convertDate($date_create)));
            Session::put('created_at', $created_at);
            $currency_code = 'INR';
            $created_by = 1;
            $created_by_id = \Auth::user()->id;
            $created_by_name = \Auth::user()->username;
            $randNumber = mt_rand(0, 999999999999999);
            $v_no = $randNumber;
            $v_date = $entry_date;
            $type = 12;
            $sub_type = 122;

            $salaryId = $request->salaryID;
            $salaryDetail = EmployeeSalary::with('salary_employee')->where('id', $salaryId)->first();
            $empID = $salaryDetail['salary_employee']->id;
            if ($request->transfer_amount == 0) {
                $sub_type = 127;

                /// ------------------------------Advance settelment  --------------

                //=-----------------------------
                $salaryPaymentBalance = $salaryDetail->balance;
                $employeeBalance = $salaryDetail['salary_employee']->bill_current_balance;

                $empSSBId = NULL;
                $member_id = Null;
                $branch_id = $payBranch = $salaryDetail->branch_id;
                $branchCode = getBranchCode($branch_id)->branch_code;
                $empCurrentBalance = $salaryDetail['salary_employee']->current_balance;
                $salaryAmount = $request->total_transfer_amount;
                $actualSalaryAmount = $request->advance_settel;
                $total_transfer_amount = $total_transfer_amount + $actualSalaryAmount;

                $salary['transferred_salary'] = $request->advance_settel;
                $salary['transferred_in'] = 3;
                $salary['is_transferred'] = 1;
                $salary['transferred_date'] = $entry_date;
                $salary['balance'] = $salaryDetail->actual_transfer_amount - $request->advance_settel;
                $salary['advance_payment'] = $salaryDetail['salary_employee']->advance_payment;
                $salary['current_advance_payment'] = $salaryDetail['salary_employee']->advance_payment - $request->advance_settel;
                $salary['settle_amount'] = $request->advance_settel;
                $salary['neft_charge'] = $request->neft_charge;

                $daybookRef = CommanController::createBranchDayBookReferenceNew($actualSalaryAmount, $globaldate);
                $refId = $daybookRef;
                $salary['salary_daybook_ref_id'] = $refId;
                $employeeupdate = EmployeeSalary::find($salaryId);
                $employeeupdate->update($salary);
                $detail = $salaryDetail['salary_employee']->employee_name . "'s " . $salaryDetail->month_name . '' . $salaryDetail->year . ' salary payment has been settled  through Advance Payment';
                $payment_mode = $paymentMode = 3;
                $payment_type = 'CR';
                $des = $salaryDetail['salary_employee']->employee_name . "'s " . $salaryDetail->month_name . '' . $salaryDetail->year . ' salary payment has been settled  through Advance Payment';
                $ssb_account_id_from = NULL;
                $cheque_no = NULL;
                $transction_no = NULL;
                $jv_unique_id = NULL;
                $ssb_account_tran_id_to = NULL;
                $ssb_account_tran_id_from = NULL;
                $cheque_type = NULL;
                $cheque_id = NULL;
                $bank_id = NULL;
                $bank_ac_id = NULL;

                ///libility
                $head11 = 1;
                $head21 = 8;
                $head31 = 21;
                $head41 = 61;
                $head51 = NULL;
                $ssb_account_id_to = NULL;
                $allTran1 = CommanController::newheadTransactionCreate($refId, $branch_id, $bank_id, $bank_ac_id, $head41, $type, $sub_type, $type_id, $associate_id = NULL, $member_id = NULL, $branch_id_to = NULL, $branch_id_from = NULL,  $actualSalaryAmount,  $des, 'DR', $payment_mode, $currency_code,  $v_no,  $ssb_account_id_from, $cheque_no,  $transction_no,  $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $salaryId, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $company_id);
                // ------ advance rent - (mines)----------------

                $head11A = 2;
                $head21A = 10;
                $head31A = 29;
                $head41A = 73;
                $head51A = NULL;
                $desA = $des;

                $allTran14 = CommanController::newheadTransactionCreate($refId, $branch_id, $bank_id, $bank_ac_id, $head41A, $type, $sub_type, $type_id, $associate_id = NULL, $member_id = NULL, $branch_id_to = NULL, $branch_id_from = NULL,  $request->advance_settel,  $des, 'CR', $payment_mode, $currency_code, $v_no,  $ssb_account_id_from, $cheque_no, $transction_no,  $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $salaryId, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $company_id);
                $empLedgerA['employee_id'] = $empID;
                $empLedgerA['branch_id'] = $branch_id;
                $empLedgerA['type'] = 3;
                $empLedgerA['type_id'] = $salaryId;
                $empLedgerA['deposit'] = $request->advance_settel;
                $empLedgerA['description'] = $desA;
                $empLedgerA['currency_code'] = $currency_code;
                $empLedgerA['payment_type'] = 'CR';
                $empLedgerA['payment_mode'] = 3;
                $empLedgerA['created_at'] = $created_at;
                $empLedgerA['daybook_ref_id'] = $refId;
                $empLedgerA['updated_at'] = $updated_at;
                $empLA = \App\Models\EmployeeLedger::create($empLedgerA);

                $description_dr = $salaryDetail['salary_employee']->employee_name . 'A/c Dr ' . $salaryAmount . '/-';
                $description_cr = 'To Branch Cash A/c Cr ' . $salaryAmount . '/-';
                $brDaybook = CommanController::branchDaybookCreateModified($refId, $payBranch, $type, $sub_type, $type_id, $associate_id = NULL, $member_id = NULL, $branch_id_to = NULL, $branch_id_from = NULL, $salaryAmount,  $des, $description_dr, $description_cr, 'DR', $payment_mode, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no,  $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $salaryId, $ssb_account_id_to, $company_id);
            } else {

                if ($request->amount_mode == 2) {
                    //--------- bank ---------
                    $bank_id_from_c = $request->bank_id;
                    $bank_ac_id_from_c = $request->account_id;
                    // $bankBla = \App\Models\SamraddhBankClosing::where('bank_id', $bank_id_from_c)->where('account_id', $bank_ac_id_from_c)->whereDate('entry_date', '<=', $entry_date)->orderby('entry_date', 'desc')->first();
                    // if ($bankBla) {
                    //     if ($request->total_transfer_amount > $bankBla->balance) {
                    //         return redirect('admin/hr/salary/transfer/' . $ledger_id)->with('alert', 'Sufficient amount not available in bank account!');
                    //     }
                    // } else {
                    //     return redirect('admin/hr/salary/transfer/' . $ledger_id)->with('alert', 'Sufficient amount not available in bank account!');
                    // }
                    $salaryId = $request->salaryID;

                    $salaryPaymentBalance = $salaryDetail->balance;
                    $employeeBalance = $salaryDetail['salary_employee']->bill_current_balance;
                    $bank_name_to = $salaryDetail->employee_bank;
                    $bank_ac_to = $salaryDetail->employee_bank_ac;
                    $bank_ifsc_to = $salaryDetail->employee_bank_ifsc;
                    $jv_unique_id = NULL;
                    $ssb_account_tran_id_to = NULL;
                    $ssb_account_tran_id_from = NULL;
                    $cheque_type = NULL;
                    $cheque_id = NULL;
                    $member_id = NULL;
                    $amount_to_id = NULL;
                    $amount_to_name = NULL;
                    $amount_from_id = NULL;
                    $amount_from_name = NULL;
                    $v_no = NULL;
                    $v_date = NULL;
                    $ssb_account_id_from = NULL;
                    $ssb_account_id_to = NULL;
                    $cheque_no = NULL;
                    $cheque_date = NULL;
                    $cheque_bank_from = NULL;
                    $cheque_bank_ac_from = NULL;
                    $cheque_bank_ifsc_from = NULL;
                    $cheque_bank_branch_from = NULL;
                    $cheque_bank_to = NULL;
                    $cheque_bank_ac_to = NULL;
                    $cheque_bank_from_id = NULL;
                    $cheque_bank_ac_from_id = NULL;
                    $cheque_bank_to_name = NULL;
                    $cheque_bank_to_branch = NULL;
                    $cheque_bank_to_ac_no = NULL;
                    $cheque_bank_to_ifsc = NULL;
                    $transction_no = NULL;
                    $transction_bank_from = NULL;
                    $transction_bank_ac_from = NULL;
                    $transction_bank_ifsc_from = NULL;
                    $transction_bank_branch_from = NULL;
                    $transction_bank_to = NULL;
                    $transction_bank_ac_to = NULL;
                    $transction_date = NULL;
                    $transction_bank_from_id = NULL;
                    $transction_bank_from_ac_id = NULL;
                    $transction_bank_to_name = NULL;
                    $transction_bank_to_ac_no = NULL;
                    $transction_bank_to_branch = NULL;
                    $transction_bank_to_ifsc = NULL;
                    $bank_id_from = $request->bank_id;
                    $bank_ac_id_from = $request->account_id;
                    $bank_id = $bank_id_from;
                    $bank_id_ac = $bank_ac_id = $bank_ac_id_from;
                    $bankfrmDetail = \App\Models\SamraddhBank::where('id', $bank_id_from)->first();
                    $bankacfrmDetail = \App\Models\SamraddhBankAccount::where('id', $bank_ac_id_from)->first();


                    $des = $salaryDetail['salary_employee']->employee_name . "'s " . $salaryDetail->month_name . '' . $salaryDetail->year . ' salary payment has been transferred to the ' . $bank_name_to . ' A/c' . $bank_ac_to;
                    $branch_id = $salaryDetail->branch_id;
                    $branchCode = getBranchCode($branch_id)->branch_code;
                    $empCurrentBalance = $salaryDetail['salary_employee']->current_balance;
                    $salaryAmount = $request->total_transfer_amount;
                    $actualSalaryAmount = $salaryDetail->actual_transfer_amount - $salaryDetail->transferred_salary;
                    $transferAmount = $request->transfer_amount;
                    $total_transfer_amount = $total_transfer_amount + $actualSalaryAmount;
                    $payment_type = 'CR';
                    //------------


                    $daybookRef = CommanController::createBranchDayBookReferenceNew($salaryAmount, $globaldate);


                    $refId = $daybookRef;

                    $salary['company_bank'] = $bank_id_from;
                    $salary['company_bank_ac'] = $bank_ac_id_from;
                    $salary['transferred_salary'] = $actualSalaryAmount + $salaryDetail->transferred_salary;
                    $salary['transferred_in'] = $request->amount_mode;
                    $salary['is_transferred'] = 1;
                    $salary['transferred_date'] = $entry_date;
                    $salary['employee_bank'] = $bank_name_to;
                    $salary['employee_bank_ac'] = $bank_ac_to;
                    $salary['employee_bank_ifsc'] = $bank_ifsc_to;
                    $salary['payment_mode'] = $paymentMode = $payment_mode = $request->payment_mode;
                    $salary['balance'] = $salaryPaymentBalance - $actualSalaryAmount;
                    $salary['advance_payment'] = $salaryDetail['salary_employee']->advance_payment;
                    $salary['current_advance_payment'] = $advance_payment = $salaryDetail['salary_employee']->advance_payment - $request->advance_settel;
                    $salary['settle_amount'] = $request->advance_settel;
                    $empLedger['to_bank_name'] = $bank_name_to;
                    $empLedger['to_bank_ac_no'] = $bank_ac_to;
                    $empLedger['to_bank_ifsc'] = $bank_ifsc_to;
                    $empLedger['from_bank_name'] = $bankfrmDetail->bank_name;
                    $empLedger['from_bank_ac_no'] = $bankacfrmDetail->account_no;
                    $empLedger['from_bank_ifsc'] = $bankacfrmDetail->ifsc_code;
                    $empLedger['from_bank_id'] = $bank_id_from;
                    $empLedger['from_bank_ac_id'] = $bank_ac_id_from;


                    if ($request->payment_mode == 1) {


                        $salary['company_cheque_id'] = $cheque_id = $request->cheque_id;
                        $salary['company_cheque_no'] = $cheque_no = $request->cheque_number;
                        $empLedger['cheque_id'] = $cheque_id;
                        $empLedger['cheque_no'] = $cheque_no;
                        $empLedger['cheque_date'] = $entry_date;
                        $cheque_no = $cheque_no;
                        $cheque_date = $entry_date;
                        $cheque_type = 1;
                        $cheque_id = $cheque_id;
                        $cheque_bank_from = $bankfrmDetail->bank_name;
                        $cheque_bank_ac_from = $bankacfrmDetail->account_no;
                        $cheque_bank_ifsc_from = $bankacfrmDetail->ifsc_code;
                        $cheque_bank_branch_from = $bankacfrmDetail->branch_name;
                        $cheque_bank_from_id = $bank_id_from;
                        $cheque_bank_ac_from_id = $bank_ac_id_from;
                        $cheque_bank_to = NULL;
                        $cheque_bank_ac_to = NULL;
                        $cheque_bank_to_name = $bank_name_to;
                        $cheque_bank_to_branch = NULL;
                        $cheque_bank_to_ac_no = $bank_ac_to;
                        $cheque_bank_to_ifsc = $bank_ifsc_to;
                        //-----------------------
                        $chequeIssue['cheque_id'] = $cheque_id;
                        $chequeIssue['type'] = 5;
                        $chequeIssue['sub_type'] = 51;
                        $chequeIssue['type_id'] = $type_id;
                        $chequeIssue['cheque_issue_date'] = $entry_date;
                        $chequeIssue['created_at'] = $created_at;
                        $chequeIssue['updated_at'] = $updated_at;
                        $chequeIssueCreate = \App\Models\SamraddhChequeIssue::create($chequeIssue);
                        //------------------ 
                        $chequeUpdate['is_use'] = 1;
                        $chequeUpdate['status'] = 3;
                        $chequeUpdate['updated_at'] = $updated_at;
                        $chequeDataUpdate = \App\Models\SamraddhCheque::find($cheque_id);
                        $chequeDataUpdate->update($chequeUpdate);
                    } else {
                        $salary['online_transaction_no'] = $transction_no = $request->utr_tran;
                        $salary['neft_charge'] = NULL;
                        $neft_charge = $request->neft_charge;
                        $empLedger['transaction_no'] = $transction_no;
                        $empLedger['transaction_date'] = $transaction_date = $transction_date = $entry_date;
                        //  $empLedger['transaction_charge']=$neft_charge;
                        $transction_bank_from = $bankfrmDetail->bank_name;
                        $transction_bank_ac_from = $bankacfrmDetail->account_no;
                        $transction_bank_ifsc_from = $bankacfrmDetail->ifsc_code;
                        $transction_bank_branch_from = $bankacfrmDetail->branch_name;
                        $transction_bank_from_id = $bank_id_from;
                        $transction_bank_from_ac_id = $bank_ac_id_from;
                        $transction_bank_to = NULL;
                        $transction_bank_ac_to = NULL;
                        $transction_bank_to_name = $bank_name_to;
                        $transction_bank_to_branch = NULL;
                        $transction_bank_to_ac_no = $bank_ac_to;
                        $transction_bank_to_ifsc = $bank_ifsc_to;
                        // bank charge head entry +
                        if ($neft_charge > 0) {

                            $des_neft = "NEFT Charges for the salary payment of " . $salaryDetail->month_name . '' . $salaryDetail->year;
                            $description_dr_b_neft = $bankfrmDetail->bank_name . '(' . $bankacfrmDetail->account_no . ') A/c Dr ' . $neft_charge . '/-';
                            $description_cr_b_neft = 'To NFFT A/c Cr ' . $neft_charge . '/-';
                            $allTranneft = CommanController::newheadTransactionCreate($refId, 29, $bank_id, $bank_ac_id, 92, $type, $sub_type, $type_id, $associate_id = NULL, $member_id, $branch_id_to = NULL, $branch_id_from = NULL,  $neft_charge,  $des_neft, 'DR', $paymentMode, $currency_code, $v_no,  $ssb_account_id_from, $cheque_no,  $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $salaryId, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $company_id);
                            $allTranneft = CommanController::newheadTransactionCreate($refId, 29, $bank_id, $bank_ac_id, $bankfrmDetail->account_head_id, $type, $sub_type, $type_id, $associate_id = NULL, $member_id, $branch_id_to = NULL, $branch_id_from = NULL, $neft_charge, $des_neft, 'CR', $paymentMode, $currency_code, $v_no, $ssb_account_id_from, $cheque_no,  $transction_no,  $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $salaryId, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $company_id);
                            $smbdcneft = CommanController::NewFieldAddSamraddhBankDaybookCreateModify($refId, $bank_id, $bank_id_ac, $type, $sub_type, $type_id, $associate_id = NULL, $member_id = NULL, 29, $opening_balance = NULL, $neft_charge, $closing_balance = NULL, $des_neft, $description_dr_b_neft, $description_cr_b_neft, 'DR', $paymentMode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $salaryId, $ssb_account_id_to, $cheque_bank_from_id, $cheque_bank_ac_from_id, $cheque_bank_to_name, $cheque_bank_to_branch, $cheque_bank_to_ac_no, $cheque_bank_to_ifsc, $transction_bank_from_id, $transction_bank_from_ac_id, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $jv_unique_id, $cheque_type, $cheque_id, $company_id);
                            $brDaybookneft = CommanController::branchDaybookCreateModified($refId, 29, $type, $sub_type, $type_id, $associate_id = NULL, $member_id = NULL, $branch_id_to = NULL, $branch_id_from = NULL, $neft_charge,  $des_neft, $description_dr_b_neft, $description_cr_b_neft, 'DR', $paymentMode, $currency_code,  $v_no,  $ssb_account_id_from, $cheque_no,   $transction_no,  $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $salaryId, $ssb_account_id_to, $company_id);
                        }
                    }


                    //------------------------libility -(mines)  ------------
                    $head11 = 1;
                    $head21 = 8;
                    $head31 = 21;
                    $head41 = 61;
                    $head51 = NULL;
                    $allTran1 = CommanController::newheadTransactionCreate($refId, $branch_id, $bank_id, $bank_ac_id, $head41, $type, $sub_type, $type_id, $associate_id = NULL, $member_id, $branch_id_to = NULL, $branch_id_from = NULL,  $actualSalaryAmount,  $des, 'DR', $payment_mode, $currency_code, $v_no,  $ssb_account_id_from, $cheque_no,  $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $salaryId, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $company_id);
                    $description_dr = $salaryDetail['salary_employee']->employee_name . 'A/c Dr ' . $transferAmount . '/-';
                    $description_cr = 'To ' . $bankfrmDetail->bank_name . '(' . $bankacfrmDetail->account_no . ') A/c Cr ' . $transferAmount . '/-';
                    if ($request->advance_settel > 0) {
                        $head11A = 2;
                        $head21A = 10;
                        $head31A = 29;
                        $head41A = 73;
                        $head51A = NULL;
                        $sub_type = 127;


                        $desA = $desAA = $salaryDetail['salary_employee']->employee_name . "'s " . $salaryDetail->month_name . '' . $salaryDetail->year . ' salary payment has been settled  through Advance Payment';

                        $allTran14 = CommanController::newheadTransactionCreate($refId, $branch_id, $bank_id = NULL, $bank_ac_id = NULL, $head41A, $type, $sub_type, $type_id, $associate_id = NULL, $member_id, $branch_id_to = NULL, $branch_id_from = NULL, $request->advance_settel,  $desA, 'CR', $payment_mode, $currency_code,  $v_no,  $ssb_account_id_from, $cheque_no,  $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $salaryId, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $company_id);
                        $empLedgerA['employee_id'] = $empID;
                        $empLedgerA['branch_id'] = $branch_id;
                        $empLedgerA['type'] = 3;
                        $empLedgerA['type_id'] = $salaryId;
                        $empLedgerA['deposit'] = $request->advance_settel;
                        $empLedgerA['description'] = $desAA;
                        $empLedgerA['currency_code'] = $currency_code;
                        $empLedgerA['payment_type'] = 'CR';
                        $empLedgerA['payment_mode'] = 3;
                        $empLedgerA['created_at'] = $created_at;
                        $empLedgerA['daybook_ref_id'] = $refId;
                        $empLedgerA['updated_at'] = $updated_at;
                        $empLA = \App\Models\EmployeeLedger::create($empLedgerA);
                    }

                    // ---------------- branch daybook entry -----------------
                    $brDaybook = CommanController::createBranchDayBookModify($refId, $branch_id, $type, $sub_type, $type_id, $type_transaction_id = NULL, $associate_id = NULL, $member_id = NULL, $branch_id_to = NULL, $branch_id_from = NULL, $transferAmount, $des, $description_dr, $description_cr, 'DR', $paymentMode, $currency_code,  $v_no,  $ssb_account_id_from, $cheque_no,  $transction_no,  $entry_date, $entry_time, $created_by, $created_by_id,  $created_at, $company_id);

                    // ------------------ samraddh bank entry -(mines) ---------------
                    $bankAmountRent = $neft_charge + $transferAmount;
                    $description_dr_b = $salaryDetail['salary_employee']->employee_name . 'A/c Dr ' . $transferAmount . '/-';
                    $description_cr_b = 'To ' . $bankfrmDetail->bank_name . '(' . $bankacfrmDetail->account_no . ') A/c Cr ' . $transferAmount . '/-';
                    $allTran2 = CommanController::newheadTransactionCreate($refId, $branch_id, $bank_id, $bank_ac_id, $bankfrmDetail->account_head_id, $type, $sub_type, $type_id, $associate_id = NULL, $member_id, $branch_id_to = NULL, $branch_id_from = NULL,  $transferAmount, $des, 'CR', $paymentMode, $currency_code,  $v_no, $ssb_account_id_from, $cheque_no, $transction_no,  $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $salaryId, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $company_id);
                    $smbdc = CommanController::NewFieldAddSamraddhBankDaybookCreateModify($refId, $bank_id, $bank_id_ac, $type, $sub_type, $type_id, $associate_id = NULL, $member_id = NULL, $branch_id, $opening_balance = NULL, $transferAmount, $closing_balance = NULL, $des, $description_dr_b, $description_cr_b, 'DR', $paymentMode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $salaryId, $ssb_account_id_to, $cheque_bank_from_id, $cheque_bank_ac_from_id, $cheque_bank_to_name, $cheque_bank_to_branch, $cheque_bank_to_ac_no, $cheque_bank_to_ifsc, $transction_bank_from_id, $transction_bank_from_ac_id, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $jv_unique_id, $cheque_type, $cheque_id, $company_id);

                    $salary['salary_daybook_ref_id'] = $refId;
                    $employeeupdate = EmployeeSalary::find($salaryId);
                    $employeeupdate->update($salary);
                    $detail = 'Salary Payment ' . $salaryDetail->month_name . ' ' . $salaryDetail->year;
                    $empLedger['employee_id'] = $empID;
                    $empLedger['branch_id'] = $branch_id;
                    $empLedger['type'] = 1;
                    $empLedger['type_id'] = $salaryId;
                    $empLedger['withdrawal'] = $transferAmount;
                    $empLedger['description'] = $detail;
                    $empLedger['currency_code'] = $currency_code;
                    $empLedger['payment_type'] = 'DR';
                    $empLedger['payment_mode'] = 2;
                    $empLedger['created_at'] = $created_at;
                    $empLedger['daybook_ref_id'] = $refId;
                    $empLedger['updated_at'] = $updated_at;
                    $empL = \App\Models\EmployeeLedger::create($empLedger);
                } else if ($request->amount_mode == 1) {
                    /// ------------------------------ssb --------------
                    $salaryId = $request->salaryID;
                    $salaryDetail = EmployeeSalary::with('salary_employee')->where('id', $salaryId)->first();
                    //=-----------------------------
                    $salaryPaymentBalance = $salaryDetail->balance;
                    $employeeBalance = $salaryDetail['salary_employee']->bill_current_balance;
                    $empID = $salaryDetail['salary_employee']->id;
                    $empSSBId = $salaryDetail['salary_employee']->ssb_id;
                    $ssbAccountDetail = getSavingAccountMemberId($empSSBId);
                    $ssbBalance = $ssbAccountDetail->balance;
                    $member_id = $ssbAccountDetail->member_id;
                    $empSSBAccount = $ssbAccountDetail->account_no;
                    $branch_id = $salaryDetail->branch_id;
                    $branchCode = getBranchCode($branch_id)->branch_code;
                    $empCurrentBalance = $salaryDetail['salary_employee']->current_balance;
                    $salaryAmount = $request->total_transfer_amount;
                    $actualSalaryAmount = $salaryDetail->actual_transfer_amount - $salaryDetail->transferred_salary;
                    $total_transfer_amount = $total_transfer_amount + $actualSalaryAmount;
                    $daybookRef = CommanController::createBranchDayBookReferenceNew($actualSalaryAmount, $globaldate);
                    $refId = $daybookRef;
                    $salary['transferred_salary'] = $actualSalaryAmount + $salaryDetail->transferred_salary;
                    $salary['transferred_in'] = $request->amount_mode;
                    $salary['is_transferred'] = 1;
                    $salary['employee_ssb_id'] = $empSSBId;
                    $salary['employee_ssb'] = $empSSBAccount;
                    $salary['transferred_date'] = $entry_date;
                    $salary['balance'] = $salaryPaymentBalance - $actualSalaryAmount;
                    $salary['advance_payment'] = $salaryDetail['salary_employee']->advance_payment;
                    $salary['current_advance_payment'] = $salaryDetail['salary_employee']->advance_payment - $request->advance_settel;
                    $salary['settle_amount'] = $request->advance_settel;
                    $salary['salary_daybook_ref_id'] = $refId;
                    $employeeupdate = EmployeeSalary::find($salaryId);
                    $employeeupdate->update($salary);
                    //------------ ssb tran head entry start  --------------------------
                    $detail = 'Salary Payment ' . $salaryDetail->month_name . ' ' . $salaryDetail->year;
                    //$ssbTranCalculation = CommanController::ssbTransactionModify($empSSBId,$empSSBAccount,$ssbBalance,$salaryAmount,$detail,'INR','CR',3,$branch_id,$associate_id=NULL,8);
                    $ssbTranCalculation = CommanController::SSBDateCR($empSSBId, $empSSBAccount, $ssbBalance, $salaryAmount, $detail, 'INR', 'CR', 3, $branch_id, $associate_id = NULL, 8, $created_at, $refId, $company_id);
                    $ssbBack = CommanController::SSBBackDateCR($empSSBId, $created_at, $salaryAmount);
                    $ssbRentTranID = $ssbTranCalculation;
                    $amountArray = array('1' => $salaryAmount);
                    $deposit_by_name = $created_by_name;
                    $deposit_by_id = $created_by_id;

                    //-------------------- ssb head entry start -----------------
                    $payment_mode = $paymentMode = 3;
                    $payment_type = 'CR';
                    $des = $salaryDetail['salary_employee']->employee_name . "'s " . $salaryDetail->month_name . '' . $salaryDetail->year . ' salary payment has been transferred to the SSB A/c' . $empSSBAccount;
                    $ssb_account_id_from = NULL;
                    $cheque_no = NULL;
                    $transction_no = NULL;
                    $ssb_account_id_to = $empSSBId;
                    $jv_unique_id = NULL;
                    $ssb_account_tran_id_to = $ssbRentTranID;
                    $ssb_account_tran_id_from = NULL;
                    $cheque_type = NULL;
                    $cheque_id = NULL;
                    $bank_id = NULL;
                    $bank_ac_id = NULL;
                    $head1SSB = 1;
                    $head2SSB = 8;
                    $head3SSB = 20;
                    $head4SSB = 56;
                    $head5SSB = NULL;
                    // ssb head entry +
                    $allTranSSB = CommanController::newheadTransactionCreate($refId, $branch_id, $bank_id, $bank_ac_id, $head4SSB, 4, 410, $empSSBId, $associate_id = NULL, $member_id, $branch_id_to = NULL, $branch_id_from = NULL, $salaryAmount,  $des, 'CR', $payment_mode, $currency_code,  $v_no,  $ssb_account_id_from, $cheque_no,  $transction_no,  $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $ssbRentTranID, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $company_id);

                    ///libility
                    $head11 = 1;
                    $head21 = 8;
                    $head31 = 21;
                    $head41 = 61;
                    $head51 = NULL;
                    $allTran1 = CommanController::newheadTransactionCreate($refId, $branch_id, $bank_id, $bank_ac_id, $head41, $type, $sub_type, $type_id, $associate_id = NULL, $member_id = NULL, $branch_id_to = NULL, $branch_id_from = NULL,  $actualSalaryAmount,  $des, 'DR', $payment_mode, $currency_code,  $v_no, $ssb_account_id_from, $cheque_no,  $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $salaryId, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $company_id);
                    // ------ advance rent - (mines)----------------
                    if ($request->advance_settel > 0) {
                        $head11A = 2;
                        $head21A = 10;
                        $head31A = 29;
                        $head41A = 73;
                        $head51A = NULL;
                        $sub_type = 127;


                        $desA = $desAA = $salaryDetail['salary_employee']->employee_name . "'s " . $salaryDetail->month_name . '' . $salaryDetail->year . ' salary payment has been settled  through Advance Payment';

                        $allTran14 = CommanController::newheadTransactionCreate($refId, $branch_id, $bank_id, $bank_ac_id, $head41A, $type, $sub_type, $type_id, $associate_id = NULL, $member_id = NULL, $branch_id_to = NULL, $branch_id_from = NULL,  $request->advance_settel,  $des, 'CR', $payment_mode, $currency_code, $v_no,  $ssb_account_id_from, $cheque_no,  $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $salaryId, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $company_id);
                        $empLedgerA['employee_id'] = $empID;
                        $empLedgerA['branch_id'] = $branch_id;
                        $empLedgerA['type'] = 3;
                        $empLedgerA['type_id'] = $salaryId;
                        $empLedgerA['deposit'] = $request->advance_settel;
                        $empLedgerA['description'] = $desAA;
                        $empLedgerA['currency_code'] = $currency_code;
                        $empLedgerA['payment_type'] = 'CR';
                        $empLedgerA['payment_mode'] = 3;
                        $empLedgerA['created_at'] = $created_at;
                        $empLedgerA['daybook_ref_id'] = $refId;
                        $empLedgerA['updated_at'] = $updated_at;
                        $empLA = \App\Models\EmployeeLedger::create($empLedgerA);
                    }
                    $description_dr = $salaryDetail['salary_employee']->employee_name . 'A/c Dr ' . $salaryAmount . '/-';
                    $description_cr = 'To SSB(' . $empSSBAccount . ') A/c Cr ' . $salaryAmount . '/-';
                    $brDaybook = CommanController::branchDaybookCreateModified($refId, $branch_id, $type, $sub_type, $type_id, $associate_id = NULL, $member_id = NULL, $branch_id_to = NULL, $branch_id_from = NULL, $salaryAmount,  $des, $description_dr, $description_cr, 'DR', $payment_mode, $currency_code, $v_no,  $ssb_account_id_from, $cheque_no, $transction_no,  $entry_date, $entry_time, $created_by, $created_by_id,  $created_at, $updated_at, $salaryId, $ssb_account_id_to, $company_id);

                    $empLedger['employee_id'] = $empID;
                    $empLedger['branch_id'] = $branch_id;
                    $empLedger['type'] = 1;
                    $empLedger['type_id'] = $salaryId;
                    $empLedger['withdrawal'] = $salaryAmount;
                    $empLedger['description'] = $detail;
                    $empLedger['currency_code'] = $currency_code;
                    $empLedger['payment_type'] = 'DR';
                    $empLedger['payment_mode'] = 3;
                    $empLedger['v_no'] = $v_no;
                    $empLedger['v_date'] = $v_date;
                    $empLedger['ssb_account_id_to'] = $ssb_account_id_to;
                    $empLedger['created_at'] = $created_at;
                    $empLedger['updated_at'] = $updated_at;
                    $empLedger['daybook_ref_id'] = $refId;
                    $empL = \App\Models\EmployeeLedger::create($empLedger);
                } else {
                    $payBranch = $_POST['payment_branch'];
                    $v_no = $v_date = NULL;
                    /// ------------------------------Cash --------------
                    $salaryId = $request->salaryID;
                    $salaryDetail = EmployeeSalary::with('salary_employee')->where('id', $salaryId)->first();
                    //=-----------------------------
                    $salaryPaymentBalance = $salaryDetail->balance;
                    $employeeBalance = $salaryDetail['salary_employee']->bill_current_balance;
                    $empID = $salaryDetail['salary_employee']->id;
                    $empSSBId = NULL;
                    $member_id = Null;
                    $branch_id = $salaryDetail->branch_id;
                    $branchCode = getBranchCode($branch_id)->branch_code;
                    $empCurrentBalance = $salaryDetail['salary_employee']->current_balance;
                    $salaryAmount = $request->total_transfer_amount;
                    $actualSalaryAmount = $salaryDetail->actual_transfer_amount - $salaryDetail->transferred_salary;
                    $total_transfer_amount = $total_transfer_amount + $actualSalaryAmount;
                    $salary['transferred_salary'] = $actualSalaryAmount + $salaryDetail->transferred_salary;
                    $salary['transferred_in'] = 0;
                    $salary['is_transferred'] = 1;
                    $salary['transferred_date'] = $entry_date;
                    $salary['balance'] = $salaryPaymentBalance - $actualSalaryAmount;
                    $salary['advance_payment'] = $salaryDetail['salary_employee']->advance_payment;
                    $salary['current_advance_payment'] = $salaryDetail['salary_employee']->advance_payment - $request->advance_settel;
                    $salary['settle_amount'] = $request->advance_settel;
                    $daybookRef = CommanController::createBranchDayBookReferenceNew($actualSalaryAmount, $globaldate);
                    $refId = $daybookRef;
                    $salary['salary_daybook_ref_id'] = $refId;
                    $employeeupdate = EmployeeSalary::find($salaryId);
                    $employeeupdate->update($salary);
                    $detail = 'Salary Payment ' . $salaryDetail->month_name . ' ' . $salaryDetail->year;
                    $payment_mode = $paymentMode = 0;
                    $payment_type = 'CR';
                    $des = $salaryDetail['salary_employee']->employee_name . "'s " . $salaryDetail->month_name . '' . $salaryDetail->year . ' salary payment payment by cash';
                    $ssb_account_id_from = NULL;
                    $cheque_no = NULL;
                    $ssb_account_id_to = NULL;
                    $jv_unique_id = NULL;
                    $ssb_account_tran_id_to = NULL;
                    $ssb_account_tran_id_from = NULL;
                    $cheque_type = NULL;
                    $transction_no = NULL;
                    $cheque_id = NULL;
                    $bank_id = NULL;
                    $bank_ac_id = NULL;
                    $head1Cash = 2;
                    $head2Cash = 10;
                    $head3Cash = 28;
                    $head4Cash = NULL;
                    $head5Cash = NULL;
                    // cash  head entry +
                    $allTranSSB = CommanController::newheadTransactionCreate($refId, $payBranch, $bank_id, $bank_ac_id, $head3Cash, $type, $sub_type, $type_id, $associate_id = NULL, $member_id, $branch_id_to = NULL, $branch_id_from = NULL,  $salaryAmount,  $des, 'CR', $payment_mode, $currency_code,  $v_no,  $ssb_account_id_from, $cheque_no,   $transction_no,  $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $salaryId, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $company_id);
                    //------------ cash tran head entry end --------------------------
                    ///libility
                    $head11 = 1;
                    $head21 = 8;
                    $head31 = 21;
                    $head41 = 61;
                    $head51 = NULL;
                    $allTran1 = CommanController::newheadTransactionCreate($refId, $branch_id, $bank_id, $bank_ac_id, $head41, $type, $sub_type, $type_id, $associate_id = NULL, $member_id = NULL, $branch_id_to = NULL, $branch_id_from = NULL,  $actualSalaryAmount,  $des, 'DR', $payment_mode, $currency_code,  $v_no,  $ssb_account_id_from, $cheque_no,  $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $salaryId, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $company_id);
                    // ------ advance rent - (mines)----------------
                    if ($request->advance_settel > 0) {
                        $head11A = 2;
                        $head21A = 10;
                        $head31A = 29;
                        $head41A = 73;
                        $head51A = NULL;
                        $sub_type = 127;


                        $desA = $desAA = $salaryDetail['salary_employee']->employee_name . "'s " . $salaryDetail->month_name . '' . $salaryDetail->year . ' salary payment has been settled  through Advance Payment';


                        $allTran14 = CommanController::newheadTransactionCreate($refId, $branch_id, $bank_id, $bank_ac_id, $head41A, $type, $sub_type, $type_id, $associate_id = NULL, $member_id = NULL, $branch_id_to = NULL, $branch_id_from = NULL, $request->advance_settel,  $des, 'CR', $payment_mode, $currency_code,  $v_no,  $ssb_account_id_from, $cheque_no, $transction_no,  $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $salaryId, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $company_id);
                        $empLedgerA['employee_id'] = $empID;
                        $empLedgerA['branch_id'] = $branch_id;
                        $empLedgerA['type'] = 3;
                        $empLedgerA['type_id'] = $salaryId;
                        $empLedgerA['deposit'] = $request->advance_settel;
                        $empLedgerA['description'] = $desAA;
                        $empLedgerA['currency_code'] = $currency_code;
                        $empLedgerA['payment_type'] = 'CR';
                        $empLedgerA['payment_mode'] = 3;
                        $empLedgerA['created_at'] = $created_at;
                        $empLedgerA['daybook_ref_id'] = $refId;
                        $empLedgerA['updated_at'] = $updated_at;
                        $empLA = \App\Models\EmployeeLedger::create($empLedgerA);
                    }
                    $description_dr = $salaryDetail['salary_employee']->employee_name . 'A/c Dr ' . $salaryAmount . '/-';
                    $description_cr = 'To Branch Cash A/c Cr ' . $salaryAmount . '/-';
                    $brDaybook = CommanController::branchDaybookCreateModified($refId, $payBranch, $type, $sub_type, $type_id, $associate_id = NULL, $member_id = NULL, $branch_id_to = NULL, $branch_id_from = NULL,  $salaryAmount,  $des, $description_dr, $description_cr, 'DR', $payment_mode, $currency_code,  $v_no, $ssb_account_id_from, $cheque_no,  $transction_no,  $entry_date, $entry_time, $created_by, $created_by_id,  $created_at, $updated_at, $ssb_account_id_to,  $type_transaction_id = Null, $company_id);

                    $empLedger['employee_id'] = $empID;
                    $empLedger['branch_id'] = $branch_id;
                    $empLedger['type'] = 1;
                    $empLedger['type_id'] = $salaryId;
                    $empLedger['withdrawal'] = $salaryAmount;
                    $empLedger['description'] = $detail;
                    $empLedger['currency_code'] = $currency_code;
                    $empLedger['payment_type'] = 'DR';
                    $empLedger['payment_mode'] = 0;
                    $empLedger['created_at'] = $created_at;
                    $empLedger['updated_at'] = $updated_at;
                    $empLedger['daybook_ref_id'] = $refId;
                    $empL = \App\Models\EmployeeLedger::create($empLedger);
                }
            }
            $advance_amt = $request->advance_settel;
            /****************************************
             * 
             * Adjustment OF ADVANCE SALARY BELOW ****************BY MAHESH 25-09-2023 
             */
            if ($advance_amt > 0) {
                $advanceBalance = AdvancedTransaction::where('type', 4)->where('sub_type', 42)->where('status', 1)->where('type_id', $empID)->where('settle_amount', '>', 0)->select('settle_amount', 'id', 'partial_daybook_ref_id')->get();
                foreach ($advanceBalance as $key) {
                    $advance_amt = $key['settle_amount'] - $advance_amt;
                    $advance_refId = $refId;
                    if ($key['partial_daybook_ref_id'] != NULL) {
                        $advance_refId = $key['partial_daybook_ref_id'];
                        $advance_refId = $advance_refId . ',' . $refId;
                    }
                    if ($advance_amt == 0) {
                        $advance_amtt = AdvancedTransaction::where('id', $key['id'])->update([
                            'settle_amount' => 0,
                            'partial_daybook_ref_id' => $advance_refId
                        ]);
                        break;
                    } elseif ($advance_amt < 0) {
                        $advance_amtt = AdvancedTransaction::where('id', $key['id'])->update([
                            'settle_amount' => 0,
                            'partial_daybook_ref_id' => $advance_refId
                        ]);
                        $advance_amt = $advance_amt * -1;
                    } elseif ($advance_amt > 0) {
                        $advance_amtt = AdvancedTransaction::where('id', $key['id'])->update([
                            'settle_amount' => $advance_amt,
                            'partial_daybook_ref_id' => $advance_refId
                        ]);
                        break;
                    }
                }
            }
            $payment_count = \App\Models\EmployeeSalary::where('leaser_id', $ledger_id)->where('is_transferred', 1)->get();
            $total_payment_count = \App\Models\EmployeeSalary::where('leaser_id', $ledger_id)->get();
            $l = \App\Models\EmployeeSalaryLeaser::where('id', $ledger_id)->first();
            if ($l->transfer_amount > 0) {
                $ledgertransfer_amount = $total_transfer_amount + $l->transfer_amount;
            } else {
                $ledgertransfer_amount = $total_transfer_amount;
            }
            $empdataL['transfer_amount'] = $ledgertransfer_amount;
            if ($request->neft_charge > 0) {
                $empdataL['total_neft'] = $l->total_neft + $request->neft_charge;
            }

            if (count($payment_count) == count($total_payment_count)) {
                $empdataL['status'] = 1;
            } else {
                $empdataL['status'] = 2;
            }
            $empdataL['updated_at'] = $updated_at;
            $empdataUpdateL = \App\Models\EmployeeSalaryLeaser::find($leaser_id);
            $empdataUpdateL->update($empdataL);

            $sumTransferredAmount = \App\Models\EmployeeSalary::where('employee_id', $empID)->sum('transferred_salary');
            $sumActualTransferAmount = \App\Models\EmployeeSalary::where('employee_id', $empID)->sum('actual_transfer_amount');
            $sumsettelAdvance = \App\Models\EmployeeSalary::where('employee_id', $empID)->sum('settle_amount');
            $sumAdvance = \App\Models\EmployeeLedger::where('employee_id', $empID)->where('type', 2)->sum('deposit');

            $libilityBalance = $sumActualTransferAmount - $sumTransferredAmount;

            $libUpdate = Employee::find($empID);
            $lib['current_balance'] = $libilityBalance;
            $lib['advance_payment'] = $libUpdate->advance_payment - $sumAdvance - $sumsettelAdvance;
            $lib['bill_current_balance'] = $libilityBalance;
            $libUpdate->update($lib);
            // dd($refId);
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            dd($ex->getMessage(), $ex->getLine(), $ex->getTrace(), $ex->getTraceAsString(), $ex->getFile(), $ex->getCode());
            return back()->with('alert', $ex->getMessage());
        }
        return redirect('admin/hr/salary/transfer/' . $leaser_id)->with('success', 'Employee Salary Transferred  Successfully');
    }
    /**
     * Route: /admin/cheque/delete
     * Method: Post 
     * @param  \Illuminate\Http\Request  $request
     * add cheque.
     * @return  array()  Response
     */
    public function ledgerDelete(Request $request)
    {
        // die('eeee');
        $id = $request->id;
        $ledger = \App\Models\EmployeeSalaryLeaser::where('id', $id)->first();
        if ($ledger->status == 0) {
            $get_rId = \App\Models\AllHeadTransaction::where('type_id', $id)->first();
            if ($get_rId) {
                $idref = $get_rId->daybook_ref_id;
                $allTransaction = \App\Models\AllHeadTransaction::where('type_id', $id)->where('type', 12)->where('sub_type', 121)->update(['is_deleted' => 1]);
                // $branchDaybookReference = \App\Models\BranchDaybookReference::where('id', $idref)->delete();
            }
            $payment_count = \App\Models\EmployeeSalary::where('leaser_id', $id)->update(['is_deleted' => 1]);
            //$billDelete=VendorBillPayment::where('salary_ledger_id',$id)->delete(); 
            $deleteApplicaton = \App\Models\EmployeeSalaryLeaser::where('id', $id)->update(['is_deleted' => 1]);
            $msg = "Ledger Deleted  Successfully";
            $data = 1;
        } else {
            $msg = "Ledger can't deleted because payment already transferred ";
            $data = 2;
        }
        $return_array = compact('data', 'msg', 'id');
        return json_encode($return_array);
    }
    public function employ_salary_leaser()
    {
        if (check_my_permission(Auth::user()->id, "119") != "1") {
            return redirect()->route('admin.dashboard');
        }
        $data['title'] = "Salary Management | Employee's Salary List";
        //$data['branch']=Branch::where('status',1)->get();
        return view('templates.admin.hr_management.salary.employ_salary_list', $data);
    }
    public function employ_salary_listing(Request $request)
    {
        if ($request->ajax()) {
            // fillter array 
            $arrFormData = array();
            //   
            $arrFormData['category'] = $request->category;
            $arrFormData['company_id'] = $request->company_id;
            $arrFormData['designation'] = $request->designation;
            $arrFormData['employee_name'] = $request->employee_name;
            $arrFormData['employee_code'] = $request->employee_code;
            $arrFormData['is_search'] = $request->is_search;
            $arrFormData['status'] = $status = $request->status;
            $arrFormData['month'] = $month = $request->month;
            $arrFormData['year'] = $year = $request->year;
            //print_r($arrFormData);die;
            if (isset($arrFormData['is_search']) && $arrFormData['is_search'] == 'yes') {
                $data = EmployeeSalary::select('id', 'category', 'month_name', 'year', 'settle_amount', 'advance_payment', 'designation_id', 'fix_salary', 'leave', 'total_salary', 'deduction', 'incentive_bonus', 'transferred_salary', 'transferred_in', 'transfer_salary', 'transferred_date', 'employee_ssb', 'employee_bank', 'employee_bank_ac', 'employee_bank_ifsc', 'payment_mode', 'esi_amount', 'tds_amount', 'pf_amount', 'paybale_amount', 'online_transaction_no', 'neft_charge', 'is_transferred', 'employee_id', 'final_paybale_amount', 'company_cheque_id', 'company_bank', 'company_bank_ac', 'branch_id', 'company_id')->where('is_deleted', 0)
                    ->has('company')->with('company:id,name')
                    ->with(['salary_employee' => function ($query) {
                        $query->select('id', 'employee_name', 'employee_code');
                    }])
                    ->with(['salaryCheque' => function ($query) {
                        $query->select('id', 'cheque_no');
                    }])
                    ->with(['salaryDesignationCustom' => function ($query) {
                        $query->select('id', 'designation_name');
                    }])
                    ->with(['salaryBank' => function ($query) {
                        $query->select('id', 'bank_name');
                    }])
                    ->with(['salaryBankAccount' => function ($query) {
                        $query->select('id', 'account_no');
                    }])
                    ->with(['salary_branch' => function ($query) {
                        $query->select('id', 'name', 'branch_code', 'sector', 'regan', 'zone');
                    }]);

                //pd($data);
                /******* fillter query start ****/
                if (Auth::user()->branch_id > 0) {
                    $data = $data->where('branch_id', '=', Auth::user()->branch_id);
                }
                if ($arrFormData['company_id'] > 0) {
                    $company_id = $arrFormData['company_id'];
                    $data = $data->where('company_id', $company_id);
                }

                if ($arrFormData['status'] != '') {
                    $status = $arrFormData['status'];
                    $data = $data->where('is_transferred', $status);
                }
                if ($arrFormData['category'] != '') {
                    $category = $arrFormData['category'];
                    if ($category > 0) {
                        $data = $data->where('category', $category);
                    }
                }
                if ($arrFormData['month'] != '') {
                    $month = $arrFormData['month'];
                    $data = $data->where('month', $month);
                }
                if ($arrFormData['year'] != '') {
                    $year = $arrFormData['year'];
                    $data = $data->where('year', $year);
                }
                if ($arrFormData['designation'] != '') {
                    $designation = $arrFormData['designation'];
                    if ($designation > 0) {
                        $data = $data->where('designation_id', $designation);
                    }
                }
                if ($arrFormData['employee_name'] != '') {
                    $employee_name = $arrFormData['employee_name'];
                    $data = $data->whereHas('salary_employee', function ($query) use ($employee_name) {
                        $query->where('employees.employee_name', 'LIKE', '%' . $employee_name . '%');
                    });
                }
                if ($arrFormData['employee_code'] != '') {
                    $employee_code = $arrFormData['employee_code'];
                    $data = $data->whereHas('salary_employee', function ($query) use ($employee_code) {
                        $query->where('employees.employee_code', 'LIKE', '%' . $employee_code . '%');
                    });
                }
                $count = $data->count('id');
                $token = session()->get('_token');
                $Cache = Cache::put('employeesalarypayable_list' . $token, $data->get()->toArray());
                Cache::put('employeesalarypayable_count' . $token, count($data->get()->toArray()));
                $data = $data->orderby('created_at', 'DESC')->offset($_POST['start'])->limit($_POST['length'])->get();
                if (Auth::user()->branch_id > 0) {
                    $totalCount = EmployeeSalary::where('branch_id', '=', Auth::user()->branch_id)->count('id');
                } else {
                    $totalCount = EmployeeSalary::count('id');
                }
                $sno = $_POST['start'];
                $rowReturn = array();

                foreach ($data as $row) {
                    $sno++;
                    $val['DT_RowIndex'] = $sno;
                    $val['company_name'] = $row->company->name ?? 'N/A';
                    $category = 'All';
                    $val['branch'] = $row['salary_branch']->name . '(' . $row['salary_branch']->branch_code . ')';
                    if ($row->category == 1) {
                        $category = 'On-rolled';
                    }
                    if ($row->category == 2) {
                        $category = 'Contract';
                    }
                    $val['category_name'] = $category;
                    $val['employee_name'] = $row['salary_employee']->employee_name;
                    $val['month'] = $row->month_name;
                    $val['year'] = $row->year;
                    $val['employee_code'] = $row['salary_employee']->employee_code;
                    $val['advance_payment'] = "N/A";
                    $val['settle_amount'] = "N/A";
                    if ($row->settle_amount > 0) {
                        $val['advance_payment'] = number_format((float) $row->advance_payment, 2, '.', '') . "&#x20B9; ";
                        $val['settle_amount'] = number_format((float) $row->settle_amount, 2, '.', '') . "&#x20B9; ";
                    }
                    if ($row->designation_id) {
                        $val['designation_name'] = $row['salaryDesignationCustom']->designation_name; //getDesignationData('designation_name',$row->designation_id)->designation_name;
                    } else {
                        $val['designation_name'] = 'All';
                    }
                    $val['fix_salary'] = number_format((float) $row->fix_salary, 2, '.', '') . "&#x20B9; ";
                    $val['leave'] = number_format((float) $row->leave, 1, '.', '');
                    $val['total_salary'] = number_format((float) $row->total_salary, 2, '.', '') . "&#x20B9; ";
                    $val['deduction'] = number_format((float) $row->deduction, 2, '.', '') . "&#x20B9; ";
                    $val['incentive_bonus'] = number_format((float) $row->incentive_bonus, 2, '.', '') . "&#x20B9; ";
                    $val['paybale_amount'] = number_format((float) $row->paybale_amount, 2, '.', '') . "&#x20B9; ";
                    $val['esi_amount'] = number_format((float) $row->esi_amount, 2, '.', '') . "&#x20B9; ";
                    $val['pf_amount'] = number_format((float) $row->pf_amount, 2, '.', '') . "&#x20B9; ";
                    $val['tds_amount'] = number_format((float) $row->tds_amount, 2, '.', '') . "&#x20B9; ";
                    $val['total_payable_salary'] = number_format((float) $row->final_paybale_amount, 2, '.', '') . "&#x20B9; ";
                    $val['transferred_salary'] = number_format((float) $row->transferred_salary, 2, '.', '') . "&#x20B9; ";
                    $val['transferred_in'] = 'N/A';
                    if ($row->transferred_in == 1) {
                        $val['transferred_in'] = 'SSB';
                    }
                    if ($row->transferred_in == 2) {
                        $val['transferred_in'] = 'Bank';
                    }
                    if ($row->transferred_in == 0 && $row->transferred_in != NULL) {
                        $val['transferred_in'] = 'Cash';
                    }
                    if ($row->transferred_date) {
                        $val['transferred_date'] = date("d/m/Y", strtotime($row->transferred_date));
                    } else {
                        $val['transferred_date'] = 'N/A';
                    }
                    $val['employee_ssb'] = $row->employee_ssb;
                    $val['employee_bank'] = $row->employee_bank;
                    $val['employee_bank_ac'] = $row->employee_bank_ac;
                    $val['employee_bank_ifsc'] = $row->employee_bank_ifsc;
                    /** $val['company_ssb']=$row->company_ssb;*/
                    $val['company_bank'] = 'N/A';
                    $val['company_bank_ac'] = 'N/A';
                    if ($row->transferred_in == 2) {
                        $bankfrmDetail = $row['salaryBank']; //\App\Models\SamraddhBank::where('id',$row->company_bank)->first();
                        $bankacfrmDetail = $row['salaryBankAccount']; //\App\Models\SamraddhBankAccount::where('id',$row->company_bank_ac)->first();
                        $val['company_bank'] = $bankfrmDetail->bank_name;
                        $val['company_bank_ac'] = $bankacfrmDetail->account_no;
                    }
                    $val['payment_mode'] = 'N/A';
                    if ($row->transferred_in == 2) {
                        if ($row->payment_mode == 1) {
                            $val['payment_mode'] = 'Cheque';
                        }
                        if ($row->payment_mode == 2) {
                            $val['payment_mode'] = 'Online';
                        }
                    }
                    $val['company_cheque_id'] = 'N/A';
                    if ($row->payment_mode == 1 && $row->transferred_in == 2) {
                        $c = $row['salaryCheque']; //\App\Models\SamraddhCheque::where('id',$row->company_cheque_id)->first();
                        $val['company_cheque_id'] = $c->cheque_no;
                    }
                    $val['online_transaction_no'] = 'N/A';
                    $val['online'] = 'N/A';
                    $val['neft_charge'] = 'N/A';
                    if ($row->payment_mode == 2 && $row->transferred_in == 2) {
                        $val['online_transaction_no'] = $row->online_transaction_no;
                        $val['online'] = $row->online_transaction_no;
                        $val['neft_charge'] = number_format((float) $row->neft_charge, 2, '.', '') . "&#x20B9; ";
                    }
                    if ($row->is_transferred == 0) {
                        $val['transfer_status'] = 'No';
                    } else {
                        $val['transfer_status'] = 'Yes';
                    }
                    $btn = '<div class="list-icons"><div class="dropdown"><a href="#" class="list-icons-item" data-toggle="dropdown"><i class="icon-menu9"></i></a><div class="dropdown-menu dropdown-menu-right">';
                    $url2 = URL::to("admin/hr/salary/advice/" . $row->id . "");
                    if ($row->is_transferred == 1) {
                        $btn .= '<a class="dropdown-item" href="' . $url2 . '" title="Advice Print"><i class="icon-printer  mr-2"></i>Advice Print</a>  ';
                    } else {
                        $btn .= 'N/A';
                    }
                    $btn .= '</div></div></div>';
                    $val['action'] = $btn;
                    $rowReturn[] = $val;
                }
                $output = array("draw" => $_POST['draw'], "recordsTotal" => $totalCount, "recordsFiltered" => $count, "data" => $rowReturn,);
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
    public function export(Request $request)
    {
        $data = Cache::get('employeesalarypayable_list');
        $count = Cache::get('employeesalarypayable_count');
        $input = $request->all();
        $start = $input["start"];
        $limit = $input["limit"];
        $returnURL = URL::to('/') . "/asset/employeesalarypayable.csv";
        $fileName = env('APP_EXPORTURL') . "/asset/employeesalarypayable.csv";
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
        $count = count($data);
        $rowReturn = [];
        $record = array_slice($data, $start, $limit);
        $totalCount = count($record);
        foreach ($record as $row) {
            $sno++;
            $val['S.No'] = $sno;
            $val['Company Name'] = $row['company']['name'];
            $val['BR Name'] = $row['branch']['name'];
            $val['Employee Name '] = $row['employee_name'];
            $val['Employee Code '] = ($row['employee_code'] != "")?'"' . $row['employee_code'] . '"': "";
            $val['Designation'] = $row['designation']['designation_name'];
            $val['Gross Salary'] = number_format((float) $row['salary'], 2, '.', '');
            $val['Leave'] = 0.00;
            $val['Total Salary'] = number_format((float) $row['salary'], 2, '.', '');
            $val['Deduction'] = 0.00;
            $val['Incentive / Bonus '] = 0.00;
            $val['Payable Amount '] = number_format((float) $row['salary'], 2, '.', '');
            $val['ESI Amount '] = $row['esi_account_no'] == '' ? $row['esi_account_no'] : 0.00;
            $val['PF Amount '] = $row['pf_account_no'] == '' ? $row['pf_account_no'] : 0.00;
            $val['TDS Amount'] = $row['pen_card'] == '' ? $row['pen_card'] : 0.00;
            $val['Transferred salary'] = number_format((float) $row['salary'], 2, '.', '');
            $val['Bank Name'] = $row['bank_name'];
            $val['Bank A/c No.'] = ($row['bank_account_no'] != "")?'"' . $row['bank_account_no'] . '"': "";
            $val['IFSC code '] = $row['bank_ifsc_code'];
            $val['SSB A/c No.'] = $row['ssb_account'];
            $val['RO Name'] = $row['branch']['regan'];
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


    public function partPayment($salaryId, $ledgerId)
    {
        $data['title'] = 'Salary Management | Part Payment';
        $data['branch'] = \App\Models\Branch::where('status', 1)->get();

        $data['leaser_id'] = $ledgerId;
        //print_r($_POST);die;
        $data['salary_list'] = EmployeeSalary::with('salary_employee')->where('id', $salaryId)->first();
        $check_data = \App\Models\EmployeeSalaryLeaser::where('id', $ledgerId)->first();
        $data['ledger_date'] = date("d/m/Y", strtotime($check_data->created_at));
        $companyId =  $data['salary_list']->company_id;
        $data['bank'] = \App\Models\SamraddhBank::where('status', 1)->where('company_id', $companyId)->get();

        if ($data['salary_list']->actual_transfer_amount == $data['salary_list']->transferred_salary) {
            return redirect('admin/hr/salary/transfer/' . $ledgerId)->with('alert', 'Payment has already done');
        }

        return view('templates.admin.hr_management.salary.part_payment', $data);
    }
    public function partPaymentSave(Request $request)
    {

        $company_id = $request->company_id;
        $rules = [
            'company_id' => ['required'],
            'amount_mode' => ['required'],
            'total_transfer_amount' => ['required'],
            'leaser_id' => ['required'],
            'actual_transfer' => ['required'],
            'transfer_amount' => ['required'],
        ];

        $customMessages = [
            'required' => ':Attribute  is required.',
        ];
        $this->validate($request, $rules, $customMessages);
        DB::beginTransaction();
        try {

            $globaldate = $request->created_at;
            $neft_charge = 0;
            $leaser_id = $ledger_id = $type_id = $request->leaser_id;
            $total_transfer_amount = 0;
            $select_date = $request->select_date;
            $current_date = date("Y-m-d", strtotime(convertDate($globaldate)));
            $entry_date = date("Y-m-d", strtotime(convertDate($select_date)));
            $entry_time = date("H:i:s", strtotime(convertDate($globaldate)));
            $date_create = $entry_date . ' ' . $entry_time;
            $created_at = date("Y-m-d H:i:s", strtotime(convertDate($date_create)));
            $updated_at = date("Y-m-d H:i:s", strtotime(convertDate($date_create)));
            Session::put('created_at', $created_at);
            $currency_code = 'INR';
            $created_by = 1;
            $created_by_id = \Auth::user()->id;
            $created_by_name = \Auth::user()->username;
            $randNumber = mt_rand(0, 999999999999999);
            $v_no = $randNumber;
            $v_date = $entry_date;
            $type = 12;
            $sub_type = 128;
            $salaryId = $request->salaryID;
            $salaryDetail = EmployeeSalary::with('salary_employee')->where('id', $salaryId)->first();
            $empID = $salaryDetail['salary_employee']->id;

            if ($request->amount_mode == 2) {

                //--------- bank ---------
                $bank_id_from_c = $request->bank_id;
                $bank_ac_id_from_c = $request->account_id;
                // $bankBla = \App\Models\SamraddhBankClosing::where('bank_id', $bank_id_from_c)->where('account_id', $bank_ac_id_from_c)->whereDate('entry_date', '<=', $entry_date)->orderby('entry_date', 'desc')->first();
                // if ($bankBla) {
                //     if ($request->total_transfer_amount > $bankBla->balance) {
                //         return redirect('admin/hr/salary/transfer/' . $ledger_id)->with('alert', 'Sufficient amount not available in bank account!');
                //     }
                // } else {
                //     return redirect('admin/hr/salary/transfer/' . $ledger_id)->with('alert', 'Sufficient amount not available in bank account!');
                // }
                $salaryId = $request->salaryID;

                $salaryPaymentBalance = $salaryDetail->balance;
                $employeeBalance = $salaryDetail['salary_employee']->bill_current_balance;
                $bank_name_to = $salaryDetail->employee_bank;
                $bank_ac_to = $salaryDetail->employee_bank_ac;
                $bank_ifsc_to = $salaryDetail->employee_bank_ifsc;
                $jv_unique_id = NULL;
                $ssb_account_tran_id_to = NULL;
                $ssb_account_tran_id_from = NULL;
                $cheque_type = NULL;
                $cheque_id = NULL;
                $member_id = NULL;
                $amount_to_id = NULL;
                $amount_to_name = NULL;
                $amount_from_id = NULL;
                $amount_from_name = NULL;
                $v_no = NULL;
                $v_date = NULL;
                $ssb_account_id_from = NULL;
                $ssb_account_id_to = NULL;
                $cheque_no = NULL;
                $cheque_date = NULL;
                $cheque_bank_from = NULL;
                $cheque_bank_ac_from = NULL;
                $cheque_bank_ifsc_from = NULL;
                $cheque_bank_branch_from = NULL;
                $cheque_bank_to = NULL;
                $cheque_bank_ac_to = NULL;
                $cheque_bank_from_id = NULL;
                $cheque_bank_ac_from_id = NULL;
                $cheque_bank_to_name = NULL;
                $cheque_bank_to_branch = NULL;
                $cheque_bank_to_ac_no = NULL;
                $cheque_bank_to_ifsc = NULL;
                $transction_no = NULL;
                $transction_bank_from = NULL;
                $transction_bank_ac_from = NULL;
                $transction_bank_ifsc_from = NULL;
                $transction_bank_branch_from = NULL;
                $transction_bank_to = NULL;
                $transction_bank_ac_to = NULL;
                $transction_date = NULL;
                $transction_bank_from_id = NULL;
                $transction_bank_from_ac_id = NULL;
                $transction_bank_to_name = NULL;
                $transction_bank_to_ac_no = NULL;
                $transction_bank_to_branch = NULL;
                $transction_bank_to_ifsc = NULL;
                $bank_id_from = $request->bank_id;
                $bank_ac_id_from = $request->account_id;
                $bank_id = $bank_id_from;
                $bank_id_ac = $bank_ac_id = $bank_ac_id_from;
                $bankfrmDetail = \App\Models\SamraddhBank::where('id', $bank_id_from)->first();
                $bankacfrmDetail = \App\Models\SamraddhBankAccount::where('id', $bank_ac_id_from)->first();
                $des = $salaryDetail['salary_employee']->employee_name . "'s " . $salaryDetail->month_name . '' . $salaryDetail->year . ' salary payment has been transferred to the ' . $bank_name_to . ' A/c' . $bank_ac_to;
                $branch_id = $salaryDetail->branch_id;
                $branchCode = getBranchCode($branch_id)->branch_code;
                $empCurrentBalance = $salaryDetail['salary_employee']->current_balance;

                $salaryAmount = $request->total_transfer_amount;

                $actualSalaryAmount = $request->transfer_amount;
                $transferAmount = $request->transfer_amount;
                $total_transfer_amount = $total_transfer_amount + $request->transfer_amount;;
                $payment_type = 'CR';
                //------------

                $daybookRef = CommanController::createBranchDayBookReferenceNew($salaryAmount, $globaldate);
                $refId = $daybookRef;

                $salary['company_bank'] = $bank_id_from;
                $salary['company_bank_ac'] = $bank_ac_id_from;
                $salary['transferred_salary'] = $actualSalaryAmount + $salaryDetail->transferred_salary;
                $salary['transferred_in'] = $request->amount_mode;

                $getTotalSalary = $salaryDetail->actual_transfer_amount;
                $getTotalTransfer = $actualSalaryAmount + $salaryDetail->transferred_salary;

                if ($getTotalTransfer == $getTotalSalary) {
                    $salary['is_transferred'] = 1;
                } else {
                    $salary['is_transferred'] = 2;
                }
                $salary['transferred_date'] = $entry_date;
                $salary['employee_bank'] = $bank_name_to;
                $salary['employee_bank_ac'] = $bank_ac_to;
                $salary['employee_bank_ifsc'] = $bank_ifsc_to;
                $salary['payment_mode'] = $paymentMode = $payment_mode = $request->payment_mode;
                $salary['balance'] = $salaryPaymentBalance - $actualSalaryAmount;
                $salary['settle_amount'] = $request->advance_settel;
                $empLedger['to_bank_name'] = $bank_name_to;
                $empLedger['to_bank_ac_no'] = $bank_ac_to;
                $empLedger['to_bank_ifsc'] = $bank_ifsc_to;
                $empLedger['from_bank_name'] = $bankfrmDetail->bank_name;
                $empLedger['from_bank_ac_no'] = $bankacfrmDetail->account_no;
                $empLedger['from_bank_ifsc'] = $bankacfrmDetail->ifsc_code;
                $empLedger['from_bank_id'] = $bank_id_from;
                $empLedger['from_bank_ac_id'] = $bank_ac_id_from;


                if ($request->payment_mode == 1) {

                    $salary['company_cheque_id'] = $cheque_id = $request->cheque_id;
                    $salary['company_cheque_no'] = $cheque_no = $request->cheque_number;
                    $empLedger['cheque_id'] = $cheque_id;
                    $empLedger['cheque_no'] = $cheque_no;
                    $empLedger['cheque_date'] = $entry_date;
                    $cheque_no = $cheque_no;
                    $cheque_date = $entry_date;
                    $cheque_type = 1;
                    $cheque_id = $cheque_id;
                    $cheque_bank_from = $bankfrmDetail->bank_name;
                    $cheque_bank_ac_from = $bankacfrmDetail->account_no;
                    $cheque_bank_ifsc_from = $bankacfrmDetail->ifsc_code;
                    $cheque_bank_branch_from = $bankacfrmDetail->branch_name;
                    $cheque_bank_from_id = $bank_id_from;
                    $cheque_bank_ac_from_id = $bank_ac_id_from;
                    $cheque_bank_to = NULL;
                    $cheque_bank_ac_to = NULL;
                    $cheque_bank_to_name = $bank_name_to;
                    $cheque_bank_to_branch = NULL;
                    $cheque_bank_to_ac_no = $bank_ac_to;
                    $cheque_bank_to_ifsc = $bank_ifsc_to;
                    //-----------------------

                    $chequeIssue['cheque_id'] = $cheque_id;
                    $chequeIssue['type'] = 5;
                    $chequeIssue['sub_type'] = 51;
                    $chequeIssue['type_id'] = $type_id;
                    $chequeIssue['cheque_issue_date'] = $entry_date;
                    $chequeIssue['created_at'] = $created_at;
                    $chequeIssue['updated_at'] = $updated_at;
                    $chequeIssueCreate = \App\Models\SamraddhChequeIssue::create($chequeIssue);
                    //------------------   

                    $chequeUpdate['is_use'] = 1;
                    $chequeUpdate['status'] = 3;
                    $chequeUpdate['updated_at'] = $updated_at;
                    $chequeDataUpdate = \App\Models\SamraddhCheque::find($cheque_id);
                    $chequeDataUpdate->update($chequeUpdate);
                } else {

                    $salary['online_transaction_no'] = $transction_no = $request->utr_tran;
                    $salary['neft_charge'] = NULL;
                    $neft_charge = $request->neft_charge;
                    $empLedger['transaction_no'] = $transction_no;
                    $empLedger['transaction_date'] = $transaction_date = $transction_date = $entry_date;
                    //  $empLedger['transaction_charge']=$neft_charge;
                    $transction_bank_from = $bankfrmDetail->bank_name;
                    $transction_bank_ac_from = $bankacfrmDetail->account_no;
                    $transction_bank_ifsc_from = $bankacfrmDetail->ifsc_code;
                    $transction_bank_branch_from = $bankacfrmDetail->branch_name;
                    $transction_bank_from_id = $bank_id_from;
                    $transction_bank_from_ac_id = $bank_ac_id_from;
                    $transction_bank_to = NULL;
                    $transction_bank_ac_to = NULL;
                    $transction_bank_to_name = $bank_name_to;
                    $transction_bank_to_branch = NULL;
                    $transction_bank_to_ac_no = $bank_ac_to;
                    $transction_bank_to_ifsc = $bank_ifsc_to;
                    // bank charge head entry +

                    if ($neft_charge > 0) {

                        $des_neft = "NEFT Charges for the salary payment of " . $salaryDetail->month_name . '' . $salaryDetail->year;
                        $description_dr_b_neft = $bankfrmDetail->bank_name . '(' . $bankacfrmDetail->account_no . ') A/c Dr ' . $neft_charge . '/-';
                        $description_cr_b_neft = 'To NFFT A/c Cr ' . $neft_charge . '/-';

                        $allTranneft = CommanController::newheadTransactionCreate($refId, 29, $bank_id, $bank_ac_id, 92, $type, $sub_type, $type_id, $associate_id = NULL, $member_id, $branch_id_to = NULL, $branch_id_from = NULL,  $neft_charge,  $des_neft, 'DR', $paymentMode, $currency_code,  $v_no,  $ssb_account_id_from, $cheque_no,  $transction_no,  $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $salaryId, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $company_id);

                        $allTranneft = CommanController::newheadTransactionCreate($refId, 29, $bank_id, $bank_ac_id, $bankfrmDetail->account_head_id, $type, $sub_type, $type_id, $associate_id = NULL, $member_id, $branch_id_to = NULL, $branch_id_from = NULL,  $neft_charge,  $des_neft, 'CR', $paymentMode, $currency_code,  $v_no,  $ssb_account_id_from, $cheque_no,  $transction_no,  $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $salaryId, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $company_id);

                        $smbdcneft = CommanController::NewFieldAddSamraddhBankDaybookCreateModify($refId, $bank_id, $bank_id_ac, $type, $sub_type, $type_id, $associate_id = NULL, $member_id = NULL, 29, $opening_balance = NULL, $neft_charge, $closing_balance = NULL, $des_neft, $description_dr_b_neft, $description_cr_b_neft, 'DR', $paymentMode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $salaryId, $ssb_account_id_to, $cheque_bank_from_id, $cheque_bank_ac_from_id, $cheque_bank_to_name, $cheque_bank_to_branch, $cheque_bank_to_ac_no, $cheque_bank_to_ifsc, $transction_bank_from_id, $transction_bank_from_ac_id, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $jv_unique_id, $cheque_type, $cheque_id, $company_id);

                        $brDaybookneft = CommanController::branchDaybookCreateModified($refId, 29, $type, $sub_type, $type_id, $associate_id = NULL, $member_id = NULL, $branch_id_to = NULL, $branch_id_from = NULL,  $neft_charge,  $des_neft, $description_dr_b_neft, $description_cr_b_neft, 'DR', $paymentMode, $currency_code,  $v_no,  $ssb_account_id_from, $cheque_no, $transction_no,  $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $salaryId, $ssb_account_id_to, $company_id);
                    }
                }

                //------------------------libility -(mines)  ------------
                $head11 = 1;
                $head21 = 8;
                $head31 = 21;
                $head41 = 61;
                $head51 = NULL;
                $allTran1 = CommanController::newheadTransactionCreate($refId, $branch_id, $bank_id, $bank_ac_id, $head41, $type, $sub_type, $type_id, $associate_id = NULL, $member_id, $branch_id_to = NULL, $branch_id_from = NULL,  $actualSalaryAmount,  $des, 'DR', $payment_mode, $currency_code,  $v_no,  $ssb_account_id_from, $cheque_no,  $transction_no,  $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $salaryId, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $company_id);
                $description_dr = $salaryDetail['salary_employee']->employee_name . 'A/c Dr ' . $transferAmount . '/-';
                $description_cr = 'To ' . $bankfrmDetail->bank_name . '(' . $bankacfrmDetail->account_no . ') A/c Cr ' . $transferAmount . '/-';

                // ---------------- branch daybook entry -----------------
                $brDaybook = CommanController::branchDaybookCreateModified($refId, $branch_id, $type, $sub_type, $type_id, $associate_id = NULL, $member_id = NULL, $branch_id_to = NULL, $branch_id_from = NULL, $transferAmount,  $des, $description_dr, $description_cr, 'DR', $paymentMode, $currency_code,  $v_no,  $ssb_account_id_from, $cheque_no, $transction_no,  $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $ssb_account_id_to, $type_transaction_id = NULL, $company_id);

                // ------------------ samraddh bank entry -(mines) ---------------
                $bankAmountRent = $neft_charge + $transferAmount;
                $description_dr_b = $salaryDetail['salary_employee']->employee_name . 'A/c Dr ' . $transferAmount . '/-';
                $description_cr_b = 'To ' . $bankfrmDetail->bank_name . '(' . $bankacfrmDetail->account_no . ') A/c Cr ' . $transferAmount . '/-';
                $allTran2 = CommanController::newheadTransactionCreate($refId, $branch_id, $bank_id, $bank_ac_id, $bankfrmDetail->account_head_id, $type, $sub_type, $type_id, $associate_id = NULL, $member_id, $branch_id_to = NULL, $branch_id_from = NULL,  $transferAmount,  $des, 'CR', $paymentMode, $currency_code,  $v_no,  $ssb_account_id_from, $cheque_no,  $transction_no,  $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $salaryId, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $company_id);

                $smbdc = CommanController::NewFieldAddSamraddhBankDaybookCreateModify($refId, $bank_id, $bank_id_ac, $type, $sub_type, $type_id, $associate_id = NULL, $member_id = NULL, $branch_id, $opening_balance = NULL, $transferAmount, $closing_balance = NULL, $des, $description_dr_b, $description_cr_b, 'DR', $paymentMode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $salaryId, $ssb_account_id_to, $cheque_bank_from_id, $cheque_bank_ac_from_id, $cheque_bank_to_name, $cheque_bank_to_branch, $cheque_bank_to_ac_no, $cheque_bank_to_ifsc, $transction_bank_from_id, $transction_bank_from_ac_id, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $jv_unique_id, $cheque_type, $cheque_id, $company_id);


                $pat_payment_ref_id = $salaryDetail->pat_payment_ref_id;
                $partPaymentRefId = $pat_payment_ref_id . ',' . $refId;

                $salary['pat_payment_ref_id'] = $partPaymentRefId;
                $salary['neft_charge'] = $request->neft_charge;;

                $employeeupdate = EmployeeSalary::find($salaryId);
                $employeeupdate->update($salary);
                $detail = 'Salary Payment ' . $salaryDetail->month_name . ' ' . $salaryDetail->year;
                $empLedger['employee_id'] = $empID;
                $empLedger['branch_id'] = $branch_id;
                $empLedger['type'] = 1;
                $empLedger['type_id'] = $salaryId;
                $empLedger['withdrawal'] = $transferAmount;
                $empLedger['description'] = $detail;
                $empLedger['currency_code'] = $currency_code;
                $empLedger['payment_type'] = 'DR';
                $empLedger['payment_mode'] = 2;
                $empLedger['created_at'] = $created_at;
                $empLedger['daybook_ref_id'] = $refId;
                $empLedger['updated_at'] = $updated_at;
                $empL = \App\Models\EmployeeLedger::create($empLedger);
            } else if ($request->amount_mode == 1) {
                /// ------------------------------ssb --------------
                $salaryId = $request->salaryID;
                //=-----------------------------

                $salaryPaymentBalance = $salaryDetail->balance;
                $employeeBalance = $salaryDetail['salary_employee']->bill_current_balance;
                $empID = $salaryDetail['salary_employee']->id;
                $empSSBId = $salaryDetail['salary_employee']->ssb_id;
                $ssbAccountDetail = getSavingAccountMemberId($empSSBId);
                $ssbBalance = $ssbAccountDetail->balance;
                $member_id = $ssbAccountDetail->member_id;
                $empSSBAccount = $ssbAccountDetail->account_no;
                $branch_id = $salaryDetail->branch_id;
                $branchCode = getBranchCode($branch_id)->branch_code;
                $empCurrentBalance = $salaryDetail['salary_employee']->current_balance;
                $salaryAmount = $request->total_transfer_amount;
                $actualSalaryAmount = $request->total_transfer_amount;
                $total_transfer_amount = $total_transfer_amount + $actualSalaryAmount;

                $daybookRef = CommanController::createBranchDayBookReferenceNew($actualSalaryAmount, $globaldate);
                $refId = $daybookRef;
                $salary['transferred_salary'] = $actualSalaryAmount + $salaryDetail->transferred_salary;

                $salary['transferred_in'] = $request->amount_mode;

                $getTotalSalary = $salaryDetail->actual_transfer_amount;
                $getTotalTransfer = $actualSalaryAmount + $salaryDetail->transferred_salary;

                if ($getTotalTransfer == $getTotalSalary) {
                    $salary['is_transferred'] = 1;
                } else {
                    $salary['is_transferred'] = 2;
                }
                $salary['employee_ssb_id'] = $empSSBId;
                $salary['employee_ssb'] = $empSSBAccount;
                $salary['transferred_date'] = $entry_date;
                $salary['balance'] = $salaryPaymentBalance - $actualSalaryAmount;

                // $salary['salary_daybook_ref_id'] = $refId;


                $pat_payment_ref_id = $salaryDetail->pat_payment_ref_id;
                $partPaymentRefId = $pat_payment_ref_id . ',' . $refId;

                $salary['pat_payment_ref_id'] = $partPaymentRefId;

                $employeeupdate = EmployeeSalary::find($salaryId);
                $employeeupdate->update($salary);
                //------------ ssb tran head entry start  --------------------------
                $detail = 'Salary Payment ' . $salaryDetail->month_name . ' ' . $salaryDetail->year;

                $ssbTranCalculation = CommanController::SSBDateCR($empSSBId, $empSSBAccount, $ssbBalance, $salaryAmount, $detail, 'INR', 'CR', 3, $branch_id, $associate_id = NULL, 8, $created_at, $refId, $company_id);
                $ssbBack = CommanController::SSBBackDateCR($empSSBId, $created_at, $salaryAmount);
                $ssbRentTranID = $ssbTranCalculation;
                $amountArray = array('1' => $salaryAmount);
                $deposit_by_name = $created_by_name;
                $deposit_by_id = $created_by_id;

                //-------------------- ssb head entry start -----------------
                $payment_mode = $paymentMode = 3;
                $payment_type = 'CR';
                $des = $salaryDetail['salary_employee']->employee_name . "'s " . $salaryDetail->month_name . '' . $salaryDetail->year . ' salary payment has been transferred to the SSB A/c' . $empSSBAccount;
                $ssb_account_id_from = NULL;
                $cheque_no = NULL;
                $ssb_account_id_to = $empSSBId;
                $jv_unique_id = NULL;
                $ssb_account_tran_id_to = $ssbRentTranID;
                $ssb_account_tran_id_from = NULL;
                $cheque_type = NULL;
                $cheque_id = NULL;
                $bank_id = NULL;
                $transction_no = NULL;
                $type_transaction_id = NULL;
                $bank_ac_id = NULL;
                $head1SSB = 1;
                $head2SSB = 8;
                $head3SSB = 20;
                $head4SSB = 56;
                $head5SSB = NULL;
                // ssb head entry +
                $allTranSSB = CommanController::newheadTransactionCreate($refId, $branch_id, $bank_id, $bank_ac_id, $head4SSB, 4, 410, $empSSBId, $associate_id = NULL, $member_id, $branch_id_to = NULL, $branch_id_from = NULL, $salaryAmount,  $des, 'CR', $payment_mode, $currency_code,  $v_no,  $ssb_account_id_from, $cheque_no, $transction_no,  $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $ssbRentTranID, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $company_id);
                //------------ ssb tran head entry end --------------------------
                ///libility
                $head11 = 1;
                $head21 = 8;
                $head31 = 21;
                $head41 = 61;
                $head51 = NULL;
                $allTran1 = CommanController::newheadTransactionCreate($refId, $branch_id, $bank_id, $bank_ac_id, $head41, $type, $sub_type, $type_id, $associate_id = NULL, $member_id = NULL, $branch_id_to = NULL, $branch_id_from = NULL,  $actualSalaryAmount,  $des, 'DR', $payment_mode, $currency_code,  $v_no,  $ssb_account_id_from, $cheque_no,  $transction_no,  $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $salaryId, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $company_id);

                $description_dr = $salaryDetail['salary_employee']->employee_name . 'A/c Dr ' . $salaryAmount . '/-';
                $description_cr = 'To SSB(' . $empSSBAccount . ') A/c Cr ' . $salaryAmount . '/-';
                $brDaybook = CommanController::branchDaybookCreateModified($refId, $branch_id, $type, $sub_type, $type_id, $associate_id = NULL, $member_id = NULL, $branch_id_to = NULL, $branch_id_from = NULL,  $salaryAmount,  $des, $description_dr, $description_cr, 'DR', $payment_mode, $currency_code,  $v_no,  $ssb_account_id_from, $cheque_no,  $transction_no,  $entry_date, $entry_time, $created_by, $created_by_id,  $created_at, $updated_at, $type_transaction_id,  $ssb_account_id_to, $company_id);

                $empLedger['employee_id'] = $empID;
                $empLedger['branch_id'] = $branch_id;
                $empLedger['type'] = 1;
                $empLedger['type_id'] = $salaryId;
                $empLedger['withdrawal'] = $salaryAmount;
                $empLedger['description'] = $detail;
                $empLedger['currency_code'] = $currency_code;
                $empLedger['payment_type'] = 'DR';
                $empLedger['payment_mode'] = 3;
                $empLedger['v_no'] = $v_no;
                $empLedger['v_date'] = $v_date;
                $empLedger['ssb_account_id_to'] = $ssb_account_id_to;
                $empLedger['created_at'] = $created_at;
                $empLedger['updated_at'] = $updated_at;
                $empLedger['daybook_ref_id'] = $refId;
                $empL = \App\Models\EmployeeLedger::create($empLedger);
            } else {

                $payBranch = $_POST['payment_branch'];
                $v_no = $v_date = NULL;
                /// ------------------------------Cash --------------
                $salaryId = $request->salaryID;
                $salaryPaymentBalance = $salaryDetail->balance;
                $employeeBalance = $salaryDetail['salary_employee']->bill_current_balance;
                $empID = $salaryDetail['salary_employee']->id;
                $empSSBId = NULL;
                $member_id = Null;
                $branch_id = $salaryDetail->branch_id;
                $branchCode = getBranchCode($branch_id)->branch_code;
                $empCurrentBalance = $salaryDetail['salary_employee']->current_balance;
                $salaryAmount = $request->total_transfer_amount;
                $actualSalaryAmount = $request->total_transfer_amount;

                $total_transfer_amount = $total_transfer_amount + $actualSalaryAmount;

                $salary['transferred_salary'] = $actualSalaryAmount + $salaryDetail->transferred_salary;
                $salary['transferred_in'] = 0;
                $getTotalSalary = $salaryDetail->actual_transfer_amount;
                $getTotalTransfer = $actualSalaryAmount + $salaryDetail->transferred_salary;

                if ($getTotalTransfer == $getTotalSalary) {
                    $salary['is_transferred'] = 1;
                } else {
                    $salary['is_transferred'] = 2;
                }

                $salary['transferred_date'] = $entry_date;
                $salary['balance'] = $salaryPaymentBalance - $actualSalaryAmount;
                $salary['advance_payment'] = $salaryDetail['salary_employee']->advance_payment;
                $salary['current_advance_payment'] = $salaryDetail['salary_employee']->advance_payment - $request->advance_settel;
                $salary['settle_amount'] = $request->advance_settel;
                $salary['neft_charge'] = $request->neft_charge;

                $daybookRef = CommanController::createBranchDayBookReferenceNew($actualSalaryAmount, $globaldate);
                $refId = $daybookRef;

                $pat_payment_ref_id = $salaryDetail->pat_payment_ref_id;
                $partPaymentRefId = $pat_payment_ref_id . ',' . $refId;

                $salary['pat_payment_ref_id'] = $partPaymentRefId;

                $employeeupdate = EmployeeSalary::find($salaryId);
                $employeeupdate->update($salary);
                $detail = 'Salary Part Payment ' . $salaryDetail->month_name . ' ' . $salaryDetail->year;
                $payment_mode = $paymentMode = 0;
                $payment_type = 'CR';
                $des = $salaryDetail['salary_employee']->employee_name . "'s " . $salaryDetail->month_name . '' . $salaryDetail->year . ' salary payment payment by cash';
                $ssb_account_id_from = NULL;
                $transction_no = NULL;
                $cheque_no = NULL;
                $jv_unique_id = NULL;
                $ssb_account_tran_id_to = NULL;
                $ssb_account_tran_id_from = NULL;
                $cheque_type = NULL;
                $cheque_id = NULL;
                $bank_id = NULL;
                $ssb_account_id_to = $empSSBId;
                $bank_ac_id = NULL;
                $head1Cash = 2;
                $head2Cash = 10;
                $head3Cash = 28;
                $head4Cash = NULL;
                $head5Cash = NULL;
                // cash  head entry +

                $allTranSSB = CommanController::newheadTransactionCreate($refId, $payBranch, $bank_id, $bank_ac_id, $head3Cash, $type, $sub_type, $type_id, $associate_id = NULL, $member_id, $branch_id_to = NULL, $branch_id_from = NULL,  $salaryAmount,  $des, 'CR', $payment_mode, $currency_code,  $v_no,  $ssb_account_id_from, $cheque_no,   $transction_no,  $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $salaryId, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $company_id);

                //------------ cash tran head entry end --------------------------
                ///libility
                $head11 = 1;
                $head21 = 8;
                $head31 = 21;
                $head41 = 61;
                $head51 = NULL;

                $allTran1 = CommanController::headTransactionCreate($refId, $branch_id, $bank_id, $bank_ac_id, $head41, $type, $sub_type, $type_id, $associate_id = NULL, $member_id = NULL, $branch_id_to = NULL, $branch_id_from = NULL,  $actualSalaryAmount,  $des, 'DR', $payment_mode, $currency_code,  $v_no,  $ssb_account_id_from, $cheque_no,   $transction_no,  $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $salaryId, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $company_id);

                $description_dr = $salaryDetail['salary_employee']->employee_name . 'A/c Dr ' . $salaryAmount . '/-';
                $description_cr = 'To Branch Cash A/c Cr ' . $salaryAmount . '/-';
                $brDaybook = CommanController::branchDaybookCreateModified($refId, $payBranch, $type, $sub_type, $type_id, $associate_id = NULL, $member_id = NULL, $branch_id_to = NULL, $branch_id_from = NULL, $salaryAmount,  $des, $description_dr, $description_cr, 'DR', $payment_mode, $currency_code,  $v_no,  $ssb_account_id_from, $cheque_no,  $transction_no,  $entry_date, $entry_time, $created_by, $created_by_id,  $created_at, $updated_at, $salaryId, $ssb_account_id_to, $company_id);

                $empLedger['employee_id'] = $empID;
                $empLedger['branch_id'] = $branch_id;
                $empLedger['type'] = 1;
                $empLedger['type_id'] = $salaryId;
                $empLedger['withdrawal'] = $salaryAmount;
                $empLedger['description'] = $detail;
                $empLedger['currency_code'] = $currency_code;
                $empLedger['payment_type'] = 'DR';
                $empLedger['payment_mode'] = 0;
                $empLedger['created_at'] = $created_at;
                $empLedger['updated_at'] = $updated_at;
                $empLedger['daybook_ref_id'] = $refId;
                $empL = \App\Models\EmployeeLedger::create($empLedger);
            }

            $payment_count = \App\Models\EmployeeSalary::where('leaser_id', $ledger_id)->where('is_transferred', 1)->get();
            $total_payment_count = \App\Models\EmployeeSalary::where('leaser_id', $ledger_id)->get();
            $l = \App\Models\EmployeeSalaryLeaser::where('id', $ledger_id)->first();

            if ($l->transfer_amount > 0) {
                $ledgertransfer_amount = $total_transfer_amount + $l->transfer_amount;
            } else {
                $ledgertransfer_amount = $total_transfer_amount;
            }
            $empdataL['transfer_amount'] = $ledgertransfer_amount;
            if ($request->neft_charge > 0) {
                $empdataL['total_neft'] = $l->total_neft + $request->neft_charge;
            }

            if (count($payment_count) == count($total_payment_count)) {
                $empdataL['status'] = 1;
            } else {
                $empdataL['status'] = 2;
            }
            $empdataL['updated_at'] = $updated_at;
            $empdataUpdateL = \App\Models\EmployeeSalaryLeaser::find($leaser_id);
            $empdataUpdateL->update($empdataL);

            $sumTransferredAmount = \App\Models\EmployeeSalary::where('employee_id', $empID)->sum('transferred_salary');
            $sumActualTransferAmount = \App\Models\EmployeeSalary::where('employee_id', $empID)->sum('actual_transfer_amount');


            $libilityBalance = $sumActualTransferAmount - $sumTransferredAmount;

            $lib['current_balance'] = $libilityBalance;
            $lib['bill_current_balance'] = $libilityBalance;
            $libUpdate = Employee::find($empID);
            $libUpdate->update($lib);
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            // dd( $ex->getMessage(), $ex->getLine());
            return back()->with('alert', $ex->getMessage());
        }
        return redirect('admin/hr/salary/transfer/' . $leaser_id)->with('success', 'Employee Salary Transferred  Successfully');
    }

    public static function getbranchbankbalanceamount(Request $request)
    {

        $globaldate = $request->entrydate;
        $entry_date = date("Y-m-d", strtotime(convertDate($globaldate)));
        $branch_id = $request->branch_id;
        $company_id = $request->company_id;
        $getBranchAmount = \App\Models\Branch::whereId($branch_id)->first();
        $Amount = $company_id == 1 ? $getBranchAmount->total_amount : 0;
        $startDate = ($company_id == 1) ? date("Y-m-d", strtotime(convertDate('2021-08-05'))) : '';
        $balance = \App\Models\BranchCurrentBalance::where('branch_id', $branch_id)->where('company_id', $company_id)->when($startDate != '', function ($q) use ($startDate) {
            $q->whereDate('entry_date', '>=', $startDate);
        })->where('entry_date', '<=', $entry_date);

        if ($company_id != '') {
            $balance = $balance->where('company_id', $company_id);
        }
        $balance = $balance->sum('totalAmount');
        $return_array = ['balance' => $balance + $Amount];
        return json_encode($return_array);
    }
    public function salary_delete(Request $request)
    {
        DB::beginTransaction();
        try {
            $salary_detail = [];
            $id = base64_decode($request->id);
            if ($request->multiple == 'no') {
                $EmployeeSalaries = EmployeeSalary::whereId($id)->select('ledger_create_ref_id', 'salary_daybook_ref_id', 'pat_payment_ref_id', 'leaser_id', 'employee_id', 'id', 'transferred_salary', 'tds_amount', 'settle_amount', 'created_at', 'balance', 'is_transferred')->get();
            } else if ($request->multiple == 'yes') {
                $EmployeeSalaries = EmployeeSalary::where('salary_daybook_ref_id', $id)->select('ledger_create_ref_id', 'salary_daybook_ref_id', 'pat_payment_ref_id', 'leaser_id', 'employee_id', 'id', 'transferred_salary', 'tds_amount', 'settle_amount', 'created_at', 'balance', 'is_transferred')->get();
            } else {
                return back()->with('alert', "something went wrong");
            }
            $main_payment = 0;
            $day_book_ids = [];
            foreach ($EmployeeSalaries as $EmployeeSalary) {
                if ($EmployeeSalary->is_transferred == 0) {
                    return back()->with('alert', "something went suspecious");
                }
                $old_value = [
                    'leave' => $EmployeeSalary->leave, 'deduction' => $EmployeeSalary->deduction, 'incentive_bonus' => $EmployeeSalary->incentive_bonus, 'paybale_amount' => $EmployeeSalary->paybale_amount, 'final_paybale_amount' => $EmployeeSalary->final_paybale_amount, 'advance_payment' => $EmployeeSalary->advance_payment, 'current_advance_payment' => $EmployeeSalary->current_advance_payment, 'settle_amount' => $EmployeeSalary->settle_amount, 'total_salary' => $EmployeeSalary->total_salary, 'tds_amount' => $EmployeeSalary->tds_amount, 'pf_amount' => $EmployeeSalary->pf_amount, 'esi_amount' => $EmployeeSalary->esi_amount, 'actual_transfer_amount' => $EmployeeSalary->actual_transfer_amount, 'transferred_salary' => $EmployeeSalary->transferred_salary, 'transfer_salary' => $EmployeeSalary->transfer_salary
                ];
                $EmployeeLedger = EmployeeLedger::where('employee_id', $EmployeeSalary->employee_id)
                    ->where('type_id', $EmployeeSalary->id)->whereIn('type', [1, 2, 3])
                    ->update(['is_deleted' => 1]);
                if (isset($EmployeeSalary->pat_payment_ref_id)) {
                    $first = substr($EmployeeSalary->pat_payment_ref_id, 0, 1);
                    if ($first == ',') {
                        $part_ids = substr($EmployeeSalary->pat_payment_ref_id, 1);
                        $part_ids_array = explode(",", $part_ids);
                        foreach ($part_ids_array as $key) {
                            array_push($day_book_ids, $key);
                        }
                    } else {
                        $part_ids_array = explode(",", $part_ids);
                        foreach ($part_ids_array as $key) {
                            array_push($day_book_ids, $key);
                        }
                    }
                }
                if (isset($EmployeeSalary->salary_daybook_ref_id) && $main_payment == 0) {
                    array_push($day_book_ids, $EmployeeSalary->salary_daybook_ref_id);
                    $main_payment++;
                }
                $chk_amt = 0;
                $Emp_data = Employee::find($EmployeeSalary->employee_id);
                if (isset($EmployeeSalary->settle_amount)) {
                    $salary_detail['current_advance_payment'] = $EmployeeSalary->current_advance_payment + $EmployeeSalary->settle_amount;
                    $salary_detail['settle_amount'] = null;
                    $advance_trxn_update = AdvancedTransaction::where('type', 4)->where('sub_type', 42)->where('type_id', $EmployeeSalary->employee_id)->where('status', 1)->where('status_date', '<=', $EmployeeSalary->created_at)->where('is_deleted', 0)->get(['amount', 'settle_amount', 'id']);
                    foreach ($advance_trxn_update as $advance_trxn) {
                        if ($advance_trxn->amount - $advance_trxn->settle_amount >= $EmployeeSalary->settle_amount) {
                            $advance_update_amount['settle_amount'] = $advance_trxn->settle_amount + $EmployeeSalary->settle_amount;
                            $chk_amt = $chk_amt + $EmployeeSalary->settle_amount;
                        } else {
                            $advance_update_amount['settle_amount'] = $advance_trxn->settle_amount + $advance_trxn->amount - $advance_trxn->settle_amount;
                            $chk_amt = $chk_amt + $advance_trxn->amount - $advance_trxn->settle_amount;
                            dd("else");
                        }
                        if ($chk_amt == $EmployeeSalary->settle_amount) {
                            AdvancedTransaction::whereId($advance_trxn->id)->update($advance_update_amount);
                            break;
                        } else if ($chk_amt > $EmployeeSalary->settle_amount) {
                            return back()->with('alert', "Advance amount settlement have some error");
                        } else {
                            AdvancedTransaction::whereId($advance_trxn->id)->update($advance_update_amount);
                        }
                    }
                    $empdata['advance_payment'] = $Emp_data['advance_payment'] + $chk_amt;
                }
                $salary_detail['transferred_in'] = null;
                $salary_detail['is_transferred'] = 0;
                $salary_detail['balance'] = 0;
                $salary_detail['transferred_date'] = null;
                $salary_detail['transferred_salary'] = null;
                $salary_detail['pat_payment_ref_id'] = null;
                $salary_detail['current_advance_payment'] = null;
                $salary_detail['salary_daybook_ref_id'] = null;
                $salary_detail['balance'] = $EmployeeSalary->balance + $EmployeeSalary->transferred_salary;
                $EmployeeSalaryLeaser = \App\Models\EmployeeSalaryLeaser::find($EmployeeSalary->leaser_id);
                $leaser = [];
                $leaser['total_tds_amount'] = $EmployeeSalaryLeaser->total_tds_amount - $EmployeeSalary->tds_amount;
                $leaser['total_pf_amount'] = $EmployeeSalaryLeaser->total_pf_amount - $EmployeeSalary->pf_amount;
                $leaser['total_esi_amount'] = $EmployeeSalaryLeaser->total_esi_amount - $EmployeeSalary->esi_amount;
                $leaser['transfer_amount'] = $EmployeeSalaryLeaser->transfer_amount - $EmployeeSalary->transferred_salary;
                $leaser['total_neft'] = $EmployeeSalaryLeaser->total_neft - $EmployeeSalary->neft_charge;
                $leaser['status'] = 2;
                $EmployeeSalaryLeaser->update($leaser);
                if ($EmployeeSalary->is_transferred == 0) {
                    return back()->with('alert', "something went wrong");
                }
                $EmployeeSalary->update($salary_detail);
                $new_value = [
                    'leave' => $EmployeeSalary->leave, 'deduction' => $EmployeeSalary->deduction, 'incentive_bonus' => $EmployeeSalary->incentive_bonus, 'paybale_amount' => $EmployeeSalary->paybale_amount, 'final_paybale_amount' => $EmployeeSalary->final_paybale_amount, 'advance_payment' => $EmployeeSalary->advance_payment, 'current_advance_payment' => $EmployeeSalary->current_advance_payment, 'settle_amount' => $EmployeeSalary->settle_amount, 'total_salary' => $EmployeeSalary->total_salary, 'tds_amount' => $EmployeeSalary->tds_amount, 'pf_amount' => $EmployeeSalary->pf_amount, 'esi_amount' => $EmployeeSalary->esi_amount, 'actual_transfer_amount' => $EmployeeSalary->actual_transfer_amount, 'transferred_salary' => $EmployeeSalary->transferred_salary, 'transfer_salary' => $EmployeeSalary->transfer_salary
                ];
                $log = [];
                $log['type'] = 1;
                $log['emp_owner_id'] = $EmployeeSalary->employee_id;
                $log['ledger_id'] = $EmployeeSalary->leaser_id;
                $log['ledger_detail_id'] = $EmployeeSalary->id;
                $log['daybook_ref_id'] = $EmployeeSalary->ledger_create_ref_id;
                $log['description'] = $request->reason;
                $log['old_value'] = json_encode($old_value);
                $log['new_value'] = json_encode($new_value);
                $log['created_by'] = 1;
                $log['created_by_id'] = Auth::user()->id;
                $log['created_at'] = $request->created_at;
                $log['title'] = "Salary Delete";
                $log['status'] = 1;
                SalaryRentLog::insert($log);
                $sumTransferredAmount = \App\Models\EmployeeSalary::where('employee_id', $EmployeeSalary->employee_id)->sum('transferred_salary');
                $sumActualTransferAmount = \App\Models\EmployeeSalary::where('employee_id', $EmployeeSalary->employee_id)->sum('actual_transfer_amount');

                $libilityBalance = $sumActualTransferAmount - $sumTransferredAmount;
                $empdata['current_balance'] = $libilityBalance;
                $empdata['bill_current_balance'] = $libilityBalance;
                $Emp_data->update($empdata);
            }
            if (!empty($day_book_ids) || count($day_book_ids) > 0) {
                AllHeadTransaction_delete($day_book_ids);
                BranchDaybook_delete($day_book_ids);
                SamraddhBankDaybook_delete($day_book_ids);
                SavingAccountTranscation_delete($day_book_ids);
            }
            DB::commit();
            return response()->json(['message' => 'Employee salary deleted successfully']);
        } catch (\Exception $ex) {
            DB::rollback();
            // dd($ex->getMessage(), $ex->getLine());
            return back()->with('alert', $ex->getMessage());
        }
    }
    public function salary_edit($id)
    {
        if (check_my_permission(Auth::user()->id, "359") != "1") {
            return redirect()->route('admin.dashboard');
        }
        $id = base64_decode($id);
        // ->select('employee_id', 'month', 'day', 'year', 'employee_id', 'id', 'total_salary', 'tds_amount')
        $data['title'] = 'Salary Management | Salary Edit';
        $EmployeeSalary = $data['EmployeeSalary'] = EmployeeSalary::whereId($id)->first();
        $data_employee = Employee::with('company:id,name')->with(['branch' => function ($query) {
            $query->select('id', 'name', 'branch_code');
        }])
            ->with(['designation' => function ($query) {
                $query->select('id', 'designation_name');
            }])
            ->where('is_employee', 1)->where('id', $EmployeeSalary->employee_id)->where('status', 1);
        $data['employee_salary_id'] = base64_encode($id);
        $data_employee = $data_employee->orderBy('branch_id')->orderBy('employee_name')->get(['id', 'category', 'employee_name', 'employee_code', 'created_at', 'designation_id', 'branch_id', 'esi_account_no', 'pf_account_no', 'salary', 'pen_card', 'bank_name', 'bank_account_no', 'bank_ifsc_code', 'ssb_account', 'company_id']);
        $data['employee'] = $data_employee;
        $data['company_id'] = $EmployeeSalary->company_id;
        $month = ($EmployeeSalary->month < 10) ? "0$EmployeeSalary->month" : $EmployeeSalary->month;
        $data['ledgerDate'] = "$EmployeeSalary->day/$month/$EmployeeSalary->year";
        return view('templates.admin.hr_management.salary.salary_edit', $data);
    }
    public function salary_edit_save(Request $request)
    {
        $id = base64_decode($request->employee_salary_id);
        $monthName = date('F', mktime(0, 0, 0, $request->ledger_month, 10));
        DB::beginTransaction();
        try {
            $company_id = $request->company;
            $globaldate = $request->created_at;
            $select_date = $request->select_date;
            $current_date = date("Y-m-d", strtotime(convertDate($globaldate)));
            $entry_date = date("Y-m-d", strtotime(convertDate($select_date)));
            $entry_time = date("H:i:s", strtotime(convertDate($globaldate)));
            $date_create = $entry_date . ' ' . $entry_time;
            $created_at = date("Y-m-d H:i:s", strtotime(convertDate($date_create)));
            $updated_at = date("Y-m-d H:i:s", strtotime(convertDate($date_create)));
            Session::put('created_at', $created_at);
            // $check_data = EmployeeSalaryLeaser::where('id', $id)->first();
            $EmployeeSalary = EmployeeSalary::where('id', $id)->first();
            EmployeeLedger::where('type_id', $id)->update(['is_deleted' => 1]);
            $employee_data = Employee::where('id', $EmployeeSalary->employee_id)->first();
            $old_value = [
                'leave' => $EmployeeSalary->leave,
                'deduction' => $EmployeeSalary->deduction,
                'incentive_bonus' => $EmployeeSalary->incentive_bonus,
                'paybale_amount' => $EmployeeSalary->paybale_amount,
                'final_paybale_amount' => $EmployeeSalary->final_paybale_amount,
                'advance_payment' => $EmployeeSalary->advance_payment,
                'current_advance_payment' => $EmployeeSalary->current_advance_payment,
                'settle_amount' => $EmployeeSalary->settle_amount,
                'total_salary' => $EmployeeSalary->total_salary,
                'tds_amount' => $EmployeeSalary->tds_amount,
                'pf_amount' => $EmployeeSalary->pf_amount,
                'esi_amount' => $EmployeeSalary->esi_amount,
                'actual_transfer_amount' => $EmployeeSalary->actual_transfer_amount,
                'transferred_salary' => $EmployeeSalary->transferred_salary,
                'transfer_salary' => $EmployeeSalary->transfer_salary,
            ];
            $create1 = $EmployeeSalary->leaser_id;
            $EmployeeSalaryLeaser = \App\Models\EmployeeSalaryLeaser::find($EmployeeSalary->leaser_id);
            $leaser = [];
            $leaser['total_paybale_amount'] = $EmployeeSalaryLeaser->total_paybale_amount - $EmployeeSalary->total_salary;
            $leaser['total_tds_amount'] = $EmployeeSalaryLeaser->total_tds_amount - $EmployeeSalary->tds_amount;
            $leaser['total_pf_amount'] = $EmployeeSalaryLeaser->total_pf_amount - $EmployeeSalary->pf_amount;
            $leaser['total_esi_amount'] = $EmployeeSalaryLeaser->total_esi_amount - $EmployeeSalary->esi_amount;
            $leaser['transfer_amount'] = $EmployeeSalaryLeaser->transfer_amount - $EmployeeSalary->transferred_salary;
            $leaser['total_transfer_amount'] = $EmployeeSalaryLeaser->total_transfer_amount - $EmployeeSalary->transfer_salary;
            $leaser['total_amount'] = $EmployeeSalaryLeaser->total_amount - $EmployeeSalary->transfer_salary;
            $EmployeeSalaryLeaser->update($leaser);
            $leaserdata['total_paybale_amount'] = $EmployeeSalaryLeaser->total_paybale_amount + $request->salary_to_sum;
            $leaserdata['total_esi_amount'] = $EmployeeSalaryLeaser->total_esi_amount + $request->esi_to_sum;
            $leaserdata['total_pf_amount'] = $EmployeeSalaryLeaser->total_pf_amount + $request->pf_to_sum;
            $leaserdata['total_tds_amount'] = $EmployeeSalaryLeaser->total_tds_amount + $request->tds_to_sum;
            $leaserdata['total_transfer_amount'] = $EmployeeSalaryLeaser->total_transfer_amount + $request->transfer_to_sum;
            $leaserdata['total_amount'] = $total_amount = $EmployeeSalaryLeaser->total_amount + $request->transfer_to_sum;
            // $leaserdata['status'] = 0;
            // $leaserdata['created_at'] = $created_at;
            // $leaserdata['updated_at'] = $created_at;
            $EmployeeSalaryLeaser->update($leaserdata);
            $leaser = $EmployeeSalary->leaser_id;
            $payment_mode = 3;
            $payment_type = 'CR';
            $currency_code = 'INR';
            $created_by = 1;
            $created_by_id = \Auth::user()->id;
            $randNumber = mt_rand(0, 999999999999999);
            $v_no = $randNumber;
            $v_date = $entry_date;
            // head  entry----------------------------
            $monthName = $EmployeeSalary->month_name;
            $des = 'Salary Ledger of ' . $EmployeeSalary->month_name . ' ' . $EmployeeSalary->year;
            $type = 12;
            $sub_type = 121;
            $type_id = $leaser;
            $all_head_leaser_del = AllHeadTransaction_delete(explode("sa", $EmployeeSalary->ledger_create_ref_id), $EmployeeSalary->leaser_id, $EmployeeSalary->id);
            if (isset($_POST['salary'])) {
                $daybookRef = $EmployeeSalary->ledger_create_ref_id;
                $refId = $daybookRef;
                foreach (($_POST['salary']) as $key => $option) {
                    $empID = $_POST['employee_id'][$key];
                    $empDetail = Employee::where('id', $empID)->first();
                    $company_id = $empDetail->company_id;
                    $data['leaser_id'] = $create1;
                    $data['employee_id'] = $empID;
                    $branch_id = $empDetail->branch_id;
                    if ($empDetail->ssb_id) {
                        $data['employee_ssb'] = getSsbAccountNumber($empDetail->ssb_id)->account_no;
                        $data['employee_ssb_id'] = $empDetail->ssb_id;
                    } else {
                        $data['employee_ssb'] = NULL;
                        $data['employee_ssb_id'] = NULL;
                    }
                    $data['company_id'] = $company_id;
                    // $data['employee_bank'] = $empDetail->bank_name;
                    // $data['employee_bank_ac'] = $empDetail->bank_account_no;
                    // $data['employee_bank_ifsc'] = $empDetail->bank_ifsc_code;
                    // $data['month'] = $request->salary_month;
                    // $data['month_name'] = $request->salary_month_name;
                    // $data['year'] = $request->salary_year;
                    // $data['branch_id'] = $empDetail->branch_id;
                    // $data['designation_id'] = $empDetail->designation_id;
                    // $data['category'] = $empDetail->category;
                    // $data['day'] = $request->salary_day;
                    $data['fix_salary'] = $_POST['salary'][$key];
                    $data['leave'] = $_POST['leave'][$key];
                    $data['total_salary'] = $_POST['total_salary'][$key];
                    $data['deduction'] = $_POST['deduction'][$key];
                    $data['incentive_bonus'] = $_POST['incentive_bonus'][$key];
                    $data['paybale_amount'] = $_POST['transfer_salary'][$key];
                    $data['esi_amount'] = $_POST['esi_amount'][$key];
                    $data['pf_amount'] = $_POST['pf_amount'][$key];
                    $data['tds_amount'] = $_POST['tds_amount'][$key];
                    $data['final_paybale_amount'] = $_POST['final_payable_amount'][$key];
                    $data['transfer_salary'] = $_POST['final_payable_amount'][$key];
                    $data['actual_transfer_amount'] = $_POST['final_payable_amount'][$key];
                    // $data['ledger_create_ref_id'] = $refId;
                    $data['created_at'] = date("Y-m-d H:i:s", strtotime(convertDate($created_at)));
                    // $create = EmployeeSalary::create($data);
                    $create = $EmployeeSalary->update($data);
                    $TranId = $tranId = $EmployeeSalary->id;
                    // Employee Ledger CR KI ENTRY JAYEGI
                    //---    paybale amount -----------
                    $val['company_id'] = $company_id;
                    $val['employee_id'] = $empID;
                    $val['branch_id'] = $empDetail->branch_id;
                    $val['type'] = 6;
                    $val['type_id'] = $TranId;
                    $val['deposit'] = $_POST['transfer_salary'][$key];
                    $val['description'] = $des;
                    $val['currency_code'] = 'INR';
                    $val['payment_type'] = 'CR';
                    $val['payment_mode'] = $payment_mode;
                    $val['status'] = 1;
                    $val['v_no'] = $v_no;
                    $val['v_date'] = $v_date;
                    $val['created_at'] = date("Y-m-d H:i:s", strtotime(convertDate($created_at)));
                    $val['updated_at'] = date("Y-m-d H:i:s", strtotime(convertDate($updated_at)));
                    $val['daybook_ref_id'] = $refId;

                    $createEmployeeledger = EmployeeLedger::create($val);
                    $branch_id = $empDetail->branch_id;
                    $bank_id = NULL;
                    $bank_ac_id = NULL;
                    $associate_id = NULL;
                    $member_id = NULL;
                    $branch_id_to = NULL;
                    $branch_id_from = NULL;
                    $ssb_account_id_from = NULL;
                    $cheque_no = NULL;
                    $transction_no = NULL;
                    $jv_unique_id = NULL;
                    $ssb_account_id_to = NULL;
                    $ssb_account_tran_id_to = NULL;
                    $ssb_account_tran_id_from = NULL;
                    $cheque_type = NULL;
                    $cheque_id = NULL;

                    //expence
                    $head12 = 4;
                    $head22 = 86;
                    $head32 = 37;
                    $head42 = NULL;
                    $head52 = NULL;
                    $allTran2 = CommanController::newheadTransactionCreate($refId, $branch_id, $bank_id, $bank_ac_id, $head32, $type, $sub_type, $type_id, $associate_id, $member_id, $branch_id_to, $branch_id_from,  $_POST['transfer_salary'][$key],  $des, 'DR', $payment_mode, $currency_code,  $v_no,  $ssb_account_id_from, $cheque_no,   $transction_no,  $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $tranId, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $company_id);

                    ///libility
                    $head11 = 1;
                    $head21 = 8;
                    $head31 = 21;
                    $head41 = 61;
                    $head51 = NULL;
                    $allTran1 = CommanController::newheadTransactionCreate($refId, $branch_id, $bank_id, $bank_ac_id, $head41, $type, $sub_type, $type_id, $associate_id = NULL, $member_id, $branch_id_to = NULL, $branch_id_from = NULL,  $_POST['transfer_salary'][$key], $des, 'CR', $payment_mode, $currency_code,  $v_no, $ssb_account_id_from, $cheque_no, $transction_no,  $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $tranId, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $company_id);

                    // ---- esi  amount --------------------------
                    if ($_POST['esi_amount'][$key] > 0) {
                        $des_esi = 'ESI  Deduction of ' . $monthName . ' ' . $EmployeeSalary->year;

                        $esi['employee_id'] = $empID;
                        $esi['branch_id'] = $empDetail->branch_id;
                        $esi['type'] = 8;
                        $esi['type_id'] = $TranId;
                        $esi['withdrawal'] = $_POST['esi_amount'][$key];
                        $esi['description'] = $des_esi;
                        $esi['currency_code'] = 'INR';
                        $esi['payment_type'] = 'DR';
                        $esi['payment_mode'] = $payment_mode;
                        $esi['status'] = 1;
                        $esi['v_no'] = $v_no;
                        $esi['v_date'] = $v_date;
                        $esi['company_id'] = $company_id;
                        $esi['created_at'] = date("Y-m-d H:i:s", strtotime(convertDate($created_at)));
                        $esi['updated_at'] = date("Y-m-d H:i:s", strtotime(convertDate($updated_at)));
                        $esi['daybook_ref_id'] = $refId;
                        $createEmployeeledgerEsi = EmployeeLedger::create($esi);
                        $allTran1 = CommanController::newheadTransactionCreate($refId, $branch_id, $bank_id, $bank_ac_id, 325, 12, 124, $type_id, $associate_id = NULL, $member_id, $branch_id_to = NULL, $branch_id_from = NULL,  $_POST['esi_amount'][$key],  $des_esi, 'CR', $payment_mode, $currency_code,  $v_no, $ssb_account_id_from, $cheque_no, $transction_no,  $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $tranId, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $company_id);

                        // salary creditor -- 141
                        $allTran1 = CommanController::newheadTransactionCreate($refId, $branch_id, $bank_id, $bank_ac_id, 61, 12, 124, $type_id, $associate_id = NULL, $member_id, $branch_id_to = NULL, $branch_id_from = NULL,  $_POST['esi_amount'][$key],  $des_esi, 'DR', $payment_mode, $currency_code,  $v_no, $ssb_account_id_from, $cheque_no,  $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $tranId, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $company_id);
                    }

                    // ---- PF  amount --------------------------
                    if ($_POST['pf_amount'][$key] > 0) {

                        $des_pf = 'PF  Deduction of ' . $monthName . ' ' . $EmployeeSalary->year;
                        $pf['employee_id'] = $empID;
                        $pf['branch_id'] = $empDetail->branch_id;
                        $pf['type'] = 9;
                        $pf['type_id'] = $TranId;
                        $pf['withdrawal'] = $_POST['pf_amount'][$key];
                        $pf['description'] = $des_pf;
                        $pf['currency_code'] = 'INR';
                        $pf['company_id'] = $company_id;
                        $pf['payment_type'] = 'DR';
                        $pf['payment_mode'] = $payment_mode;
                        $pf['status'] = 1;
                        $pf['v_no'] = $v_no;
                        $pf['v_date'] = $v_date;
                        $pf['created_at'] = date("Y-m-d H:i:s", strtotime(convertDate($created_at)));
                        $pf['updated_at'] = date("Y-m-d H:i:s", strtotime(convertDate($updated_at)));
                        $pf['daybook_ref_id'] = $refId;
                        $createEmployeeledgerPf = EmployeeLedger::create($pf);
                        $allTran1 = CommanController::newheadTransactionCreate($refId, $branch_id, $bank_id, $bank_ac_id, 331, 12, 125, $type_id, $associate_id = NULL, $member_id, $branch_id_to = NULL, $branch_id_from = NULL,  $_POST['pf_amount'][$key],  $des_pf, 'CR', $payment_mode, $currency_code,  $v_no,  $ssb_account_id_from, $cheque_no,  $transction_no,  $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $tranId, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $company_id);
                        // salary creditor -- 141
                        $allTran1 = CommanController::newheadTransactionCreate($refId, $branch_id, $bank_id, $bank_ac_id, 61, 12, 125, $type_id, $associate_id = NULL, $member_id, $branch_id_to = NULL, $branch_id_from = NULL,  $_POST['pf_amount'][$key],  $des_pf, 'DR', $payment_mode, $currency_code,  $v_no, $ssb_account_id_from, $cheque_no,  $transction_no,  $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $tranId, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $company_id);
                    }

                    // ---- TDS  amount --------------------------
                    if ($_POST['tds_amount'][$key] > 0) {
                        $des_tds = 'TDS Deduction of ' . $monthName . ' ' . $EmployeeSalary->year;
                        $tds['employee_id'] = $empID;
                        $tds['branch_id'] = $empDetail->branch_id;
                        $tds['type'] = 10;
                        $tds['type_id'] = $TranId;
                        $tds['withdrawal'] = $_POST['tds_amount'][$key];
                        $tds['description'] = $des_tds;
                        $tds['currency_code'] = 'INR';
                        $tds['payment_type'] = 'DR';
                        $tds['payment_mode'] = $payment_mode;
                        $tds['company_id'] = $company_id;
                        $tds['status'] = 1;
                        $tds['v_no'] = $v_no;
                        $tds['v_date'] = $v_date;
                        $tds['created_at'] = date("Y-m-d H:i:s", strtotime(convertDate($created_at)));
                        $tds['updated_at'] = date("Y-m-d H:i:s", strtotime(convertDate($updated_at)));
                        $tds['daybook_ref_id'] = $refId;
                        $createEmployeeledgertds = EmployeeLedger::create($tds);

                        $allTran1 = CommanController::newheadTransactionCreate($refId, $branch_id, $bank_id, $bank_ac_id, 327, 12, 126, $type_id, $associate_id = NULL, $member_id, $branch_id_to = NULL, $branch_id_from = NULL,  $_POST['tds_amount'][$key],  $des_tds, 'CR', $payment_mode, $currency_code,  $v_no,  $ssb_account_id_from, $cheque_no,  $transction_no,  $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $tranId, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $company_id);
                        // salary creditor -- 141
                        $allTran1 = CommanController::newheadTransactionCreate($refId, $branch_id, $bank_id, $bank_ac_id, 61, 12, 126, $type_id, $associate_id = NULL, $member_id, $branch_id_to = NULL, $branch_id_from = NULL,  $_POST['tds_amount'][$key],  $des_tds, 'DR', $payment_mode, $currency_code,  $v_no,  $ssb_account_id_from, $cheque_no,  $transction_no,  $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $tranId, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $company_id);
                    }
                }
            }
            // $EmployeeSalary->update(['is_deleted' =>1]); 
            $new_value = [
                'leave' => $EmployeeSalary->leave,
                'deduction' => $EmployeeSalary->deduction,
                'incentive_bonus' => $EmployeeSalary->incentive_bonus,
                'paybale_amount' => $EmployeeSalary->paybale_amount,
                'final_paybale_amount' => $EmployeeSalary->final_paybale_amount,
                'advance_payment' => $EmployeeSalary->advance_payment,
                'current_advance_payment' => $EmployeeSalary->current_advance_payment,
                'settle_amount' => $EmployeeSalary->settle_amount,
                'total_salary' => $EmployeeSalary->total_salary,
                'tds_amount' => $EmployeeSalary->tds_amount,
                'pf_amount' => $EmployeeSalary->pf_amount,
                'esi_amount' => $EmployeeSalary->esi_amount,
                'actual_transfer_amount' => $EmployeeSalary->actual_transfer_amount,
                'transferred_salary' => $EmployeeSalary->transferred_salary,
                'transfer_salary' => $EmployeeSalary->transfer_salary,
            ];
            $log = [];
            $log['type'] = 1;
            $log['emp_owner_id'] = $EmployeeSalary->employee_id;
            $log['ledger_id'] = $EmployeeSalary->leaser_id;
            $log['ledger_detail_id'] = $EmployeeSalary->id;
            $log['daybook_ref_id'] = $EmployeeSalary->ledger_create_ref_id;
            $log['description'] = "Employe $employee_data->name ";
            $log['old_value'] = json_encode($old_value);
            $log['new_value'] = json_encode($new_value);
            $log['created_by'] = 1;
            $log['created_by_id'] = Auth::user()->id;
            $log['created_at'] = $request->created_at;
            $log['title'] = "Salary Edit";
            $log['status'] = 1;
            SalaryRentLog::insert($log);
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            // dd($ex->getMessage(), $ex->getLine());
            return back()->with('alert', $ex->getMessage());
        }
        return redirect('admin/hr/salary/transfer/' . $leaser)->with('success', 'Employee Salary Updated  Successfully');
    }
    public function regenerate_salary($id)
    {
        if (check_my_permission(Auth::user()->id, "358") != "1") {
            return redirect()->route('admin.dashboard');
        }
        if (isset($_GET['regenerate'])) {
            $regenerate_param = $_GET['regenerate'];
            $regenerate_array = json_decode(urldecode($regenerate_param), true);
        }
        $data['title'] = "Regenerate | Salary management";
        $salary_id = EmployeeSalaryLeaser::whereId(base64_decode($id))->first();
        $data_employee = Employee::with('company:id,name')->with(['branch' => function ($query) {
            $query->select('id', 'name', 'branch_code');
        }])
            ->with(['designation' => function ($query) {
                $query->select('id', 'designation_name');
            }])
            ->where('is_employee', 1)->where('status', 1)->whereIn('id', $regenerate_array);
        $data_employee = $data_employee->orderBy('branch_id')->orderBy('employee_name')->get(['id', 'category', 'employee_name', 'employee_code', 'created_at', 'designation_id', 'branch_id', 'esi_account_no', 'pf_account_no', 'salary', 'pen_card', 'bank_name', 'bank_account_no', 'bank_ifsc_code', 'ssb_account', 'company_id']);
        $data['employee'] = $data_employee;
        $dateChk_ledger = $salary_id->year . '/' . $salary_id->month . '/01';
        $data['company_id'] = $salary_id->company_id;
        $lastDayofMonth = Carbon::parse($dateChk_ledger)->endOfMonth()->toDateString();
        $data['ledgerDate'] = date("d/m/Y", strtotime(convertDate($lastDayofMonth)));
        $data['employee_salary_id'] = $id;
        $Cache = Cache::put('employeesalarypayable_list', $data_employee->toArray());
        Cache::put('employeesalarypayable_count', count($data_employee->toArray()));
        return view('templates.admin.hr_management.salary.regenerate', $data);
    }
    public function salary_regenerate(Request $request)
    {
        if (check_my_permission(Auth::user()->id, "358") != "1") {
            return redirect()->route('admin.dashboard');
        }
        $id = base64_decode($request->employee_salary_id);
        $monthName = date('F', mktime(0, 0, 0, $request->ledger_month, 10));
        DB::beginTransaction();
        try {
            $company_id = $request->company;
            $globaldate = $request->created_at;
            $select_date = $request->select_date;
            $current_date = date("Y-m-d", strtotime(convertDate($globaldate)));
            $entry_date = date("Y-m-d", strtotime(convertDate($select_date)));
            $entry_time = date("H:i:s", strtotime(convertDate($globaldate)));
            $date_create = $entry_date . ' ' . $entry_time;
            $created_at = date("Y-m-d H:i:s", strtotime(convertDate($date_create)));
            $updated_at = date("Y-m-d H:i:s", strtotime(convertDate($date_create)));
            Session::put('created_at', $created_at);
            // $check_data = EmployeeSalaryLeaser::where('id', $id)->first();
            $EmployeeSalaryLeaser = \App\Models\EmployeeSalaryLeaser::find($id);
            $EmployeeSalary = EmployeeSalary::where('leaser_id', $id)->first();
            $create1 = $EmployeeSalary->leaser_id;
            $old_value = [
                'total_paybale_amount' => $EmployeeSalaryLeaser->total_paybale_amount,
                'total_esi_amount' => $EmployeeSalaryLeaser->total_esi_amount,
                'total_pf_amount' => $EmployeeSalaryLeaser->total_pf_amount,
                'total_tds_amount' => $EmployeeSalaryLeaser->total_tds_amount,
                'total_transfer_amount' => $EmployeeSalaryLeaser->total_transfer_amount,
                'total_amount' => $EmployeeSalaryLeaser->total_amount,
                'transfer_amount' => $EmployeeSalaryLeaser->transfer_amount
            ];
            $leaser = [];
            $leaserdata['total_paybale_amount'] = $EmployeeSalaryLeaser->total_paybale_amount + $request->salary_to_sum;
            $leaserdata['total_esi_amount'] = $EmployeeSalaryLeaser->total_esi_amount + $request->esi_to_sum;
            $leaserdata['total_pf_amount'] = $EmployeeSalaryLeaser->total_pf_amount + $request->pf_to_sum;
            $leaserdata['total_tds_amount'] = $EmployeeSalaryLeaser->total_tds_amount + $request->tds_to_sum;
            $leaserdata['total_transfer_amount'] = $EmployeeSalaryLeaser->total_transfer_amount + $request->transfer_to_sum;
            $leaserdata['total_amount'] = $EmployeeSalaryLeaser->total_amount + $request->transfer_to_sum;
            if ($EmployeeSalaryLeaser->status == 1) {
                $leaserdata['status'] = 2;
            }
            $EmployeeSalaryLeaser->update($leaserdata);
            $leaser = $EmployeeSalary->leaser_id;
            $payment_mode = 3;
            $payment_type = 'CR';
            $currency_code = 'INR';
            $created_by = 1;
            $created_by_id = \Auth::user()->id;
            $randNumber = mt_rand(0, 999999999999999);
            $v_no = $randNumber;
            $v_date = $entry_date;
            // head  entry----------------------------
            $monthName = $EmployeeSalaryLeaser->month_name;
            $des = 'Salary Ledger of ' . $EmployeeSalaryLeaser->month_name . ' ' . $EmployeeSalaryLeaser->year;
            $type = 12;
            $sub_type = 121;
            $type_id = $leaser;
            if (isset($_POST['salary'])) {
                $daybookRef = $EmployeeSalary->ledger_create_ref_id;
                $refId = $daybookRef;
                foreach (($_POST['salary']) as $key => $option) {
                    $empID = $_POST['employee_id'][$key];
                    $empDetail = Employee::where('id', $empID)->first();
                    $company_id = $empDetail->company_id;
                    $data['leaser_id'] = $create1;
                    $data['employee_id'] = $empID;
                    $branch_id = $empDetail->branch_id;
                    if ($empDetail->ssb_id) {
                        $data['employee_ssb'] = getSsbAccountNumber($empDetail->ssb_id)->account_no;
                        $data['employee_ssb_id'] = $empDetail->ssb_id;
                    } else {
                        $data['employee_ssb'] = NULL;
                        $data['employee_ssb_id'] = NULL;
                    }
                    $data['company_id'] = $company_id;
                    $data['employee_bank'] = $empDetail->bank_name;
                    $data['employee_bank_ac'] = $empDetail->bank_account_no;
                    $data['employee_bank_ifsc'] = $empDetail->bank_ifsc_code;
                    $data['month'] = $EmployeeSalary->month;
                    $data['month_name'] = $EmployeeSalary->month_name;
                    $data['year'] = $EmployeeSalary->year;
                    $data['branch_id'] = $empDetail->branch_id;
                    $data['designation_id'] = $empDetail->designation_id;
                    $data['category'] = $empDetail->category;
                    $data['day'] = $EmployeeSalary->day;
                    $data['fix_salary'] = $_POST['salary'][$key];
                    $data['leave'] = $_POST['leave'][$key];
                    $data['total_salary'] = $_POST['total_salary'][$key];
                    $data['deduction'] = $_POST['deduction'][$key];
                    $data['incentive_bonus'] = $_POST['incentive_bonus'][$key];
                    $data['paybale_amount'] = $_POST['transfer_salary'][$key];
                    $data['esi_amount'] = $_POST['esi_amount'][$key];
                    $data['pf_amount'] = $_POST['pf_amount'][$key];
                    $data['tds_amount'] = $_POST['tds_amount'][$key];
                    $data['final_paybale_amount'] = $_POST['final_payable_amount'][$key];
                    $data['transfer_salary'] = $_POST['final_payable_amount'][$key];
                    $data['actual_transfer_amount'] = $_POST['final_payable_amount'][$key];
                    $data['ledger_create_ref_id'] = $refId;
                    $data['created_at'] = date("Y-m-d H:i:s", strtotime(convertDate($created_at)));
                    $create = EmployeeSalary::create($data);
                    // $create = $EmployeeSalary->update($data);
                    $TranId = $tranId = $EmployeeSalary->id;
                    // Employee Ledger CR KI ENTRY JAYEGI
                    //---    paybale amount -----------
                    $val['company_id'] = $company_id;
                    $val['employee_id'] = $empID;
                    $val['branch_id'] = $empDetail->branch_id;
                    $val['type'] = 6;
                    $val['type_id'] = $TranId;
                    $val['deposit'] = $_POST['transfer_salary'][$key];
                    $val['description'] = $des;
                    $val['currency_code'] = 'INR';
                    $val['payment_type'] = 'CR';
                    $val['payment_mode'] = $payment_mode;
                    $val['status'] = 1;
                    $val['v_no'] = $v_no;
                    $val['v_date'] = $v_date;
                    $val['created_at'] = date("Y-m-d H:i:s", strtotime(convertDate($created_at)));
                    $val['updated_at'] = date("Y-m-d H:i:s", strtotime(convertDate($updated_at)));
                    $val['daybook_ref_id'] = $refId;
                    $createEmployeeledger = EmployeeLedger::create($val);
                    $branch_id = $empDetail->branch_id;
                    $bank_id = NULL;
                    $bank_ac_id = NULL;
                    $associate_id = NULL;
                    $member_id = NULL;
                    $branch_id_to = NULL;
                    $branch_id_from = NULL;
                    $ssb_account_id_from = NULL;
                    $cheque_no = NULL;
                    $transction_no = NULL;
                    $jv_unique_id = NULL;
                    $ssb_account_id_to = NULL;
                    $ssb_account_tran_id_to = NULL;
                    $ssb_account_tran_id_from = NULL;
                    $cheque_type = NULL;
                    $cheque_id = NULL;
                    //expence
                    $head12 = 4;
                    $head22 = 86;
                    $head32 = 37;
                    $head42 = NULL;
                    $head52 = NULL;
                    $allTran2 = CommanController::newheadTransactionCreate($refId, $branch_id, $bank_id, $bank_ac_id, $head32, $type, $sub_type, $type_id, $associate_id, $member_id, $branch_id_to, $branch_id_from,  $_POST['transfer_salary'][$key],  $des, 'DR', $payment_mode, $currency_code,  $v_no,  $ssb_account_id_from, $cheque_no,   $transction_no,  $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $tranId, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $company_id);
                    ///libility
                    $head11 = 1;
                    $head21 = 8;
                    $head31 = 21;
                    $head41 = 61;
                    $head51 = NULL;
                    $allTran1 = CommanController::newheadTransactionCreate($refId, $branch_id, $bank_id, $bank_ac_id, $head41, $type, $sub_type, $type_id, $associate_id = NULL, $member_id, $branch_id_to = NULL, $branch_id_from = NULL,  $_POST['transfer_salary'][$key], $des, 'CR', $payment_mode, $currency_code,  $v_no, $ssb_account_id_from, $cheque_no, $transction_no,  $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $tranId, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $company_id);
                    // ---- esi  amount --------------------------
                    if ($_POST['esi_amount'][$key] > 0) {
                        $des_esi = 'ESI  Deduction of ' . $monthName . ' ' . $EmployeeSalary->year;
                        $esi['employee_id'] = $empID;
                        $esi['branch_id'] = $empDetail->branch_id;
                        $esi['type'] = 8;
                        $esi['type_id'] = $TranId;
                        $esi['withdrawal'] = $_POST['esi_amount'][$key];
                        $esi['description'] = $des_esi;
                        $esi['currency_code'] = 'INR';
                        $esi['payment_type'] = 'DR';
                        $esi['payment_mode'] = $payment_mode;
                        $esi['status'] = 1;
                        $esi['v_no'] = $v_no;
                        $esi['v_date'] = $v_date;
                        $esi['company_id'] = $company_id;
                        $esi['created_at'] = date("Y-m-d H:i:s", strtotime(convertDate($created_at)));
                        $esi['updated_at'] = date("Y-m-d H:i:s", strtotime(convertDate($updated_at)));
                        $esi['daybook_ref_id'] = $refId;
                        $createEmployeeledgerEsi = EmployeeLedger::create($esi);
                        $allTran1 = CommanController::newheadTransactionCreate($refId, $branch_id, $bank_id, $bank_ac_id, 325, 12, 124, $type_id, $associate_id = NULL, $member_id, $branch_id_to = NULL, $branch_id_from = NULL,  $_POST['esi_amount'][$key],  $des_esi, 'CR', $payment_mode, $currency_code,  $v_no, $ssb_account_id_from, $cheque_no, $transction_no,  $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $tranId, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $company_id);

                        // salary creditor -- 141
                        $allTran1 = CommanController::newheadTransactionCreate($refId, $branch_id, $bank_id, $bank_ac_id, 61, 12, 124, $type_id, $associate_id = NULL, $member_id, $branch_id_to = NULL, $branch_id_from = NULL,  $_POST['esi_amount'][$key],  $des_esi, 'DR', $payment_mode, $currency_code,  $v_no, $ssb_account_id_from, $cheque_no,  $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $tranId, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $company_id);
                    }

                    // ---- PF  amount --------------------------
                    if ($_POST['pf_amount'][$key] > 0) {
                        $des_pf = 'PF  Deduction of ' . $monthName . ' ' . $EmployeeSalary->year;
                        $pf['employee_id'] = $empID;
                        $pf['branch_id'] = $empDetail->branch_id;
                        $pf['type'] = 9;
                        $pf['type_id'] = $TranId;
                        $pf['withdrawal'] = $_POST['pf_amount'][$key];
                        $pf['description'] = $des_pf;
                        $pf['currency_code'] = 'INR';
                        $pf['company_id'] = $company_id;
                        $pf['payment_type'] = 'DR';
                        $pf['payment_mode'] = $payment_mode;
                        $pf['status'] = 1;
                        $pf['v_no'] = $v_no;
                        $pf['v_date'] = $v_date;
                        $pf['created_at'] = date("Y-m-d H:i:s", strtotime(convertDate($created_at)));
                        $pf['updated_at'] = date("Y-m-d H:i:s", strtotime(convertDate($updated_at)));
                        $pf['daybook_ref_id'] = $refId;
                        $createEmployeeledgerPf = EmployeeLedger::create($pf);
                        $allTran1 = CommanController::newheadTransactionCreate($refId, $branch_id, $bank_id, $bank_ac_id, 331, 12, 125, $type_id, $associate_id = NULL, $member_id, $branch_id_to = NULL, $branch_id_from = NULL,  $_POST['pf_amount'][$key],  $des_pf, 'CR', $payment_mode, $currency_code,  $v_no,  $ssb_account_id_from, $cheque_no,  $transction_no,  $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $tranId, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $company_id);
                        // salary creditor -- 141
                        $allTran1 = CommanController::newheadTransactionCreate($refId, $branch_id, $bank_id, $bank_ac_id, 61, 12, 125, $type_id, $associate_id = NULL, $member_id, $branch_id_to = NULL, $branch_id_from = NULL,  $_POST['pf_amount'][$key],  $des_pf, 'DR', $payment_mode, $currency_code,  $v_no, $ssb_account_id_from, $cheque_no,  $transction_no,  $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $tranId, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $company_id);
                    }

                    // ---- TDS  amount --------------------------
                    if ($_POST['tds_amount'][$key] > 0) {
                        $des_tds = 'TDS Deduction of ' . $monthName . ' ' . $EmployeeSalary->year;
                        $tds['employee_id'] = $empID;
                        $tds['branch_id'] = $empDetail->branch_id;
                        $tds['type'] = 10;
                        $tds['type_id'] = $TranId;
                        $tds['withdrawal'] = $_POST['tds_amount'][$key];
                        $tds['description'] = $des_tds;
                        $tds['currency_code'] = 'INR';
                        $tds['payment_type'] = 'DR';
                        $tds['payment_mode'] = $payment_mode;
                        $tds['company_id'] = $company_id;
                        $tds['status'] = 1;
                        $tds['v_no'] = $v_no;
                        $tds['v_date'] = $v_date;
                        $tds['created_at'] = date("Y-m-d H:i:s", strtotime(convertDate($created_at)));
                        $tds['updated_at'] = date("Y-m-d H:i:s", strtotime(convertDate($updated_at)));
                        $tds['daybook_ref_id'] = $refId;
                        $createEmployeeledgertds = EmployeeLedger::create($tds);

                        $allTran1 = CommanController::newheadTransactionCreate($refId, $branch_id, $bank_id, $bank_ac_id, 327, 12, 126, $type_id, $associate_id = NULL, $member_id, $branch_id_to = NULL, $branch_id_from = NULL,  $_POST['tds_amount'][$key],  $des_tds, 'CR', $payment_mode, $currency_code,  $v_no,  $ssb_account_id_from, $cheque_no,  $transction_no,  $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $tranId, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $company_id);
                        // salary creditor -- 141
                        $allTran1 = CommanController::newheadTransactionCreate($refId, $branch_id, $bank_id, $bank_ac_id, 61, 12, 126, $type_id, $associate_id = NULL, $member_id, $branch_id_to = NULL, $branch_id_from = NULL,  $_POST['tds_amount'][$key],  $des_tds, 'DR', $payment_mode, $currency_code,  $v_no,  $ssb_account_id_from, $cheque_no,  $transction_no,  $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $tranId, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $company_id);
                    }
                    $log = [];
                    $log['type'] = 1;
                    $log['emp_owner_id'] = $empDetail->id;
                    $log['ledger_id'] = $EmployeeSalary->leaser_id;
                    $log['ledger_detail_id'] = $EmployeeSalary->id;
                    $log['daybook_ref_id'] = $daybookRef;
                    $log['description'] = "Employe $empDetail->name ";
                    $log['old_value'] = 'N/a';
                    // $log['old_value'] = json_encode($old_value);
                    // $log['new_value'] = json_encode($new_value);
                    $log['new_value'] = 'N/a';
                    $log['created_by'] = 1;
                    $log['created_by_id'] = Auth::user()->id;
                    $log['created_at'] = $request->created_at;
                    $log['title'] = "Salary regenerated";
                    $log['status'] = 1;
                    SalaryRentLog::insert($log);
                }
            }
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('alert', $ex->getMessage());
        }
        return redirect('admin/hr/salary/transfer/' . $leaser)->with('success', 'Employee Salary Updated  Successfully');
    }
}
