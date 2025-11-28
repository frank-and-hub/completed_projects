<?php
namespace App\Http\Controllers\Admin\HrManagement;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Member;
use App\Models\EmployeeDiploma;
use App\Models\EmployeeExperience;
use App\Models\EmployeeQualification;
use App\Models\EmployeeTerminate;
use App\Models\EmployeeTransfer;
use App\Models\EmployeeApplication;
use App\Models\Employee;
use App\Models\Branch;
use App\Models\SavingAccount;
use App\Models\Designation;
use DB;
use URL;
use App\Services\ImageUpload;
use Yajra\DataTables\DataTables;

/*
    |---------------------------------------------------------------------------
    | Admin Panel -- Employee Management EmployeeController
    |--------------------------------------------------------------------------
    |
    | This controller handles Employee all functionlity.
*/
class EmployeeController extends Controller
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
     * Active  Employee List.
     * Route: admin/hr/employee
     * Method: get 
     * @return  array()  Response
     */
    public function index()
    {
        if (check_my_permission(Auth::user()->id, "112") != "1") {
            return redirect()->route('admin.dashboard');
        }
        $data['title'] = 'Employee Management | Employee List';
        $data['designation'] = Designation::select('id', 'designation_name')->where('status', 1)->get();
        $data['branch'] = Branch::select('id', 'name', 'branch_code')->where('status', 1)->get();
        return view('templates.admin.hr_management.employee.index', $data);
    }
    /**
     * Get  Employee  list
     * Route: ajax call from - admin/hr/employee
     * Method: Post 
     * @param  \Illuminate\Http\Request  $request
     * @return JSON array
     */
    public function employeeListing(Request $request)
    {
        if ($request->ajax()) {
            // fillter array 
            $arrFormData = array();
            if (!empty($_POST['searchform'])) {
                foreach ($_POST['searchform'] as $frm_data) {
                    $arrFormData[$frm_data['name']] = $frm_data['value'];
                }
            }
            if (isset($arrFormData['is_search']) && $arrFormData['is_search'] == 'yes') {
                //print_r($arrFormData);die;
                $data = Employee::with(['branch' => function ($query) {
                    $query->select('id', 'name', 'branch_code', 'sector', 'regan', 'zone'); }])->with([
                            'company' => function ($q) {
                                $q->select(['id', 'name']);
                            }
                        ])
                    ->with(['designation' => function ($query) {
                        $query->select('id', 'designation_name'); }])
                    ->with(['empApp:id,employee_id,application_type,status'])
                    ->where('is_employee', 1);

                $totalCount = $data->count('id');
                if (!is_null(Auth::user()->branch_ids)) {
                    $branch_ids = Auth::user()->branch_ids;
                    $data = $data->whereIn('branch_id', explode(",", $branch_ids));
                }
                /******* fillter query start ****/

                if ($arrFormData['employee_name'] != '') {
                    $employee_name = $arrFormData['employee_name'];
                    $data = $data->where('employee_name', 'LIKE', '%' . $employee_name . '%');
                }
                if ($arrFormData['employee_code'] != '') {
                    $employee_code = $arrFormData['employee_code'];
                    $data = $data->where('employee_code', 'LIKE', '%' . $employee_code . '%');
                }
                if ($arrFormData['reco_employee_name'] != '') {
                    $reco_employee_name = $arrFormData['reco_employee_name'];
                    //$data=$data->where('recommendation_employee_name',$reco_employee_name);
                    $data = $data->where('recommendation_employee_name', 'LIKE', '%' . $reco_employee_name . '%');
                }
                if (isset($arrFormData['branch_id']) && $arrFormData['branch_id'] != '') {
                    $branch = $arrFormData['branch_id'];
                    if ($branch != '0') {
                        $data = $data->where('branch_id', $branch);
                    }
                }
                if (isset($arrFormData['company_id']) && $arrFormData['company_id'] != '') {
                    $companyId = $arrFormData['company_id'];
                    if ($companyId != '0') {
                        $data = $data->where('company_id', $companyId);
                    }
                }
                if ($arrFormData['category'] != '') {
                    $categoryid = $arrFormData['category'];
                    $data = $data->where('category', $categoryid);
                }
                if ($arrFormData['designation'] != '') {
                    $designation = $arrFormData['designation'];
                    $data = $data->where('designation_id', $designation);
                }
                if ($arrFormData['status'] != '') {
                    $status = $arrFormData['status'];
                    if ($status == 'resigned') {
                        $data = $data->where('is_resigned', '>', 0);
                    }
                    if ($status == 'terminated') {
                        $data = $data->where('is_terminate', 1);
                    }
                    if ($status == 'tranfered') {
                        $data = $data->where('is_transfer', 1);
                    }
                    if ($status == 'active') {
                        $data = $data->where('status', 1);
                    }
                    if ($status == 'inactive') {
                        $data = $data->where('status', 0);
                    }


                }
                if ($arrFormData['start_date'] != '') {
                    $startDate = date("Y-m-d", strtotime(convertDate($arrFormData['start_date'])));
                    if ($arrFormData['end_date'] != '') {
                        $endDate = date("Y-m-d ", strtotime(convertDate($arrFormData['end_date'])));
                    } else {
                        $endDate = '';
                    }
                    $data = $data->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate]);
                }



                /******* fillter query End ****/
                $count = $data->count('id');

                $data = $data->offset($_POST['start'])->limit($_POST['length']);
                $data = $data->orderby('created_at', 'DESC')->get(['id', 'category', 'recommendation_employee_name', 'employee_name', 'employee_code', 'dob', 'gender', 'mobile_no', 'email', 'father_guardian_name', 'father_guardian_number', 'mother_name', 'pen_card', 'aadhar_card', 'voter_id', 'is_employee', 'status', 'is_resigned', 'is_terminate', 'is_transfer', 'created_at', 'designation_id', 'branch_id', 'esi_account_no', 'pf_account_no', 'company_id']);
                $sno = $_POST['start'];
                $rowReturn = array();
                // pd($data);
                foreach ($data as $row) {
                    $sno++;
                    $val['DT_RowIndex'] = $sno;
                    $val['company_name'] = 'N/A';
                    if ($row['company']) {
                        $val['company_name'] = $row['company']->name;
                    }
                    if (isset($row['designation']->designation_name) && !empty($row['designation']->designation_name)) {
                        $val['designation'] = $row['designation']->designation_name;  //getDesignationData('designation_name',$row->designation_id)->designation_name;
                    } else {
                        $val['designation'] = "N/A";
                    }

                    $category = '';
                    if ($row->category == 1) {
                        $category = 'On-rolled';
                    }
                    if ($row->category == 2) {
                        $category = 'Contract';
                    }
                    $val['category'] = $category;
                    $val['branch'] = ($row['branch']['name']) . '(' . $row['branch']->branch_code . ')' ?? 'N/A';


                    $val['rec_employee_name'] = $row->recommendation_employee_name;
                    $val['employee_name'] = $row->employee_name;
                    $val['employee_code'] = $row->employee_code;
                    $val['dob'] = date("d/m/Y", strtotime($row->dob));
                    $gender = 'Other';
                    if ($row->gender == 1) {
                        $gender = 'Male';
                    }
                    if ($row->gender == 2) {
                        $gender = 'Female';
                    }
                    $val['gender'] = $gender;
                    $val['mobile_no'] = $row->mobile_no;
                    $val['email'] = $row->email;
                    $val['guardian_name'] = $row->father_guardian_name;
                    $val['guardian_number'] = $row->father_guardian_number;
                    $val['mother_name'] = $row->mother_name;
                    $val['pen_card'] = $row->pen_card;
                    $val['aadhar_card'] = $row->aadhar_card;
                    $val['voter_id'] = $row->voter_id;
                    $val['esi'] = $row->esi_account_no;
                    $val['pf'] = $row->pf_account_no;
                    if ($row->is_employee == 0) {
                        $status = 'Pending';
                    } else {
                        $status = 'Inactive';
                        if ($row->status == 1) {
                            $status = 'Active';
                        }
                    }
                    $val['status'] = $status;
                    $resign = 'No';
                    if ($row->is_resigned == 1 || $row->is_resigned == 2) {

                        $resign = 'Yes';
                    }
                    if ($row->empApp && $row->empApp->application_type == 2) {
                        if ($row->empApp->status == 0) {
                            $resign = 'Pending';
                        }
                        if ($row->empApp->status == 1) {
                            $resign = 'Approved';
                        }
                        if ($row->empApp->status == 3) {
                            $resign = 'Rejected';
                        }
                        if ($row->empApp->status == 9) {
                            $resign = 'Deleted';
                        }
                    }

                    $val['resign'] = $resign;
                    $terminate = 'No';
                    if ($row->is_terminate == 1) {
                        $terminate = 'Yes';
                    }
                    $val['terminate'] = $terminate;
                    $transfer = 'No';
                    if ($row->is_transfer == 1) {
                        $transfer = 'Yes';
                    }
                    $val['transfer'] = $transfer;
                    $val['created_at'] = date("d/m/Y", strtotime($row->created_at));
                    $btn = '<div class="list-icons"><div class="dropdown"><a href="#" class="list-icons-item" data-toggle="dropdown"><i class="icon-menu9"></i></a><div class="dropdown-menu dropdown-menu-right">';
                    $url = URL::to("admin/hr/employee/transfer-request?employee=" . $row->employee_code);
                    $url1 = URL::to("admin/hr/employee/terminate?employee=" . $row->employee_code);
                    $url2 = URL::to("admin/hr/employee/edit/" . $row->id);
                    $url4 = URL::to("admin/hr/employee/detail/" . $row->id);
                    $url5 = URL::to("admin/hr/employee/resign_letter/" . $row->id);
                    $url6 = URL::to("admin/hr/employee/termination_letter/" . $row->id);
                    $url7 = URL::to("admin/hr/employee/transfer_letter/" . $row->id);
                    $url8 = URL::to("admin/hr/employee/resign_request?employee=" . $row->employee_code);
                    $empLedger = URL::to("admin/hr/employ/ledger/" . $row->id . "");
                    $url222 = URL::to("admin/hr/employee/application_print/" . $row->id . "?type=1");
                    $btn .= '<a class="dropdown-item" href="' . $url4 . '" title="Employee View"><i class="icon-eye8  mr-2"></i>Employee View</a>';
                    $btn .= '<a class="dropdown-item" href="' . $url2 . '" title="Employee Edit"><i class="icon-pencil7  mr-2"></i>Employee Edit</a>';
                    $btn .= '<a class="dropdown-item" href="' . $url222 . '" title="Download PDF & Print"><i class="icon-printer  mr-2"></i>Download PDF & Print </a>';
                    $btn .= '<a class="dropdown-item" href="' . $empLedger . '" target="blank"><i class="icon-list mr-2"></i>Transactions</a>';
                    if ($row->is_resigned == 0 && $row->is_terminate == 0 && $row->status == 1) {
                        $btn .= '<a class="dropdown-item" href="' . $url8 . '" title="Resign Request"><i class="icon-add  mr-2"></i>Resign Request</a>';
                    }
                    if ($row->is_resigned == 2) {
                        //$btn .= '<a class="dropdown-item" href="'.$url5.'" title="Resign Letter"><i class="icon-envelope  mr-2"></i>Resign Letter</a>'; 
                    }
                    if ($row->is_terminate == 1) {
                        // $btn .= '<a class="dropdown-item" href="'.$url6.'" title="Termination Letter"><i class="icon-envelope  mr-2"></i>Termination Letter</a>'; 
                    } else {
                        if ($row->is_terminate == 0 && $row->is_resigned < 2 && $row->status == 1) {
                            $btn .= '<a class="dropdown-item" href="' . $url1 . '" title="Terminate "><i class="icon-user-cancel mr-2"></i>Terminate</a>';
                        }
                    }
                    if ($row->is_transfer == 1) {
                        $btn .= '<a class="dropdown-item" href="' . $url7 . '" title="Transfer Letter"><i class="icon-envelope  mr-2"></i>Transfer Letter</a>';
                    }
                    if ($row->is_terminate == 0 && $row->is_resigned < 2 && $row->status == 1) {
                        $btn .= '<a class="dropdown-item" href="' . $url . '" title="Transfer "><i class="fa fa-share mr-2"></i>Transfer</a>';
                    }
                    $btn .= '</div></div></div>';
                    $val['action'] = $btn;

                    $rowReturn[] = $val;
                }
                $output = array("branch_id" => Auth::user()->branch_id, "draw" => $_POST['draw'], "recordsTotal" => $totalCount, "recordsFiltered" => $count, "data" => $rowReturn, );
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
    /**
     * Employee Application List.
     * Route: admin/hr/employee/application
     * Method: get 
     * @return  array()  Response
     */
    public function applicationList()
    {
        if (check_my_permission(Auth::user()->id, "111") != "1") {
            return redirect()->route('admin.dashboard');
        }

        $data['title'] = 'Employee Management | Employee Application List';
        $data['designation'] = Designation::select('id', 'designation_name')->where('status', 1)->get();
        $data['branch'] = Branch::select('id', 'name', 'branch_code')->where('status', 1)->get();
        return view('templates.admin.hr_management.employee.application_list', $data);
    }
    /**
     * Get  Employee Application list
     * Route: ajax call from - admin/hr/employee/Application
     * Method: Post 
     * @param  \Illuminate\Http\Request  $request
     * @return JSON array
     */
    public function employeeApplicationListing(Request $request)
    {


        if ($request->ajax()) {

            // fillter array 
            $arrFormData = array();
            if (!empty($_POST['searchform'])) {
                foreach ($_POST['searchform'] as $frm_data) {
                    $arrFormData[$frm_data['name']] = $frm_data['value'];
                }
            }
            $companyId = $arrFormData['company_id'];
            if (isset($arrFormData['is_search']) && $arrFormData['is_search'] == 'yes') {

                $data = EmployeeApplication::with('branch:id,name,branch_code,sector,regan,zone')
                    ->with('employeeget:id,category,recommendation_employee_name,employee_name,dob,gender,mobile_no,email,father_guardian_name,father_guardian_number,mother_name,pen_card,aadhar_card,voter_id,designation_id,esi_account_no,pf_account_no,is_employee')
                    ->with('company:id,name')
                    ->with('employeeget.designation:id,designation_name') /*->whereHas('employeeget', function ($query) {
$query->where('is_employee', 0);
})*/->whereNotIn('status', [9]);
                    // ->where(function ($query) {
                    //     $query->where('application_type', 1)
                    //         ->WhereHas('employeeget', function ($query) {
                    //             $query->where('is_employee', 0);
                    //         });
                    // });


                if (!is_null(Auth::user()->branch_ids)) {
                    $branch_ids = Auth::user()->branch_ids;
                    $data = $data->whereIn('branch_id', explode(",", $branch_ids));
                }
                /******* fillter query start ****/


                if (isset($arrFormData['app_type'])) {
                    $app_type = $arrFormData['app_type'];
                    $data = $data->where('application_type', $app_type);
                }

                if ($arrFormData['employee_name'] != '') {
                    $employee_name = $arrFormData['employee_name'];
                    $data = $data->whereHas('employeeget', function ($query) use ($employee_name) {
                        $query->where('employee_name', 'LIKE', '%' . $employee_name . '%');
                    });
                }
                if ($arrFormData['reco_employee_name'] != '') {
                    $reco_employee_name = $arrFormData['reco_employee_name'];
                    //$data=$data->where('recommendation_employee_name',$reco_employee_name);
                    $data = $data->whereHas('employeeget', function ($query) use ($reco_employee_name) {
                        $query->where('recommendation_employee_name', 'LIKE', '%' . $reco_employee_name . '%');
                    });
                }
                if (isset($arrFormData['branch_id']) && $arrFormData['branch_id'] != '') {
                    $branch = $arrFormData['branch_id'];
                    if ($branch != '0') {
                        $data = $data->where('branch_id', $branch);
                    }
                }
                if (isset($companyId) && $companyId != '') {
                    if ($companyId != '0') {
                        $data = $data->where('company_id', $companyId);
                    }
                }

                if ($arrFormData['category'] != '') {
                    $categoryid = $arrFormData['category'];
                    $data = $data->whereHas('employeeget', function ($query) use ($categoryid) {
                        $query->where('category', $categoryid);
                    });
                }
                if ($arrFormData['designation'] != '') {
                    $designation = $arrFormData['designation'];
                    $data = $data->whereHas('employeeget', function ($query) use ($designation) {
                        $query->where('designation_id', $designation);
                    });
                }
                if ($arrFormData['status'] != '') {
                    $status = $arrFormData['status'];
                    $data = $data->where('status', $status);

                }
                if ($arrFormData['start_date'] != '') {
                    $startDate = date("Y-m-d", strtotime(convertDate($arrFormData['start_date'])));
                    if ($arrFormData['end_date'] != '') {
                        $endDate = date("Y-m-d ", strtotime(convertDate($arrFormData['end_date'])));
                    } else {
                        $endDate = '';
                    }
                    $data = $data->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate]);
                }



                /******* fillter query End ****/
                $totalCount = $count = $data->count('id');

                $data = $data->orderby('created_at', 'DESC')->offset($_POST['start'])->limit($_POST['length'])->get(['id', 'application_type', 'status', 'created_at', 'branch_id', 'employee_id', 'company_id']);
                $sno = $_POST['start'];
                $rowReturn = array();
                foreach ($data as $row) {
                    $sno++;
                    $val['DT_RowIndex'] = $sno;
                    $val['company_name'] = 'N/A';
                    if ($row['company']) {
                        $val['company_name'] = $row['company']->name;
                    }
                    if (isset($row['employeeget']['designation']->designation_name) && !empty($row['employeeget']['designation']->designation_name)) {
                        $val['designation'] = $row['employeeget']['designation']->designation_name;
                    } else {
                        $val['designation'] = "N/A";
                    }


                    $category = '';
                    if ($row['employeeget']->category == 1) {
                        $category = 'On-rolled';
                    }
                    if ($row['employeeget']->category == 2) {
                        $category = 'Contract';
                    }
                    $val['category'] = $category;
                    if (isset($row['branch'])) {
                        $val['branch'] = $row['branch']->name . ' (' . $row['branch']->branch_code . ')';
                    } else {
                        $val['branch'] = 'N/A';
                    }
                    $val['rec_employee_name'] = $row['employeeget']->recommendation_employee_name;
                    $val['employee_name'] = $row['employeeget']->employee_name;
                    $val['dob'] = date("d/m/Y", strtotime($row['employeeget']->dob));
                    $gender = 'Other';
                    if ($row['employeeget']->gender == 1) {
                        $gender = 'Male';
                    }
                    if ($row['employeeget']->gender == 2) {
                        $gender = 'Female';
                    }
                    $val['gender'] = $gender;
                    $val['mobile_no'] = $row['employeeget']->mobile_no;
                    $val['email'] = $row['employeeget']->email;
                    $val['guardian_name'] = $row['employeeget']->father_guardian_name;
                    $val['guardian_number'] = $row['employeeget']->father_guardian_number;
                    $val['mother_name'] = $row['employeeget']->mother_name;
                    $val['pen_card'] = $row['employeeget']->pen_card;
                    $val['aadhar_card'] = $row['employeeget']->aadhar_card;
                    $val['voter_id'] = $row['employeeget']->voter_id;
                    $val['esi'] = $row['employeeget']->esi_account_no;
                    $val['pf'] = $row['employeeget']->pf_account_no;
                    $application_type = '';
                    if ($row->application_type == 1) {
                        $application_type = 'Register';
                    } else {
                        $application_type = 'Resign';
                    }
                    $val['application_type'] = $application_type;
                    $status = 'Pending';
                    if ($row->status == 1) {
                        $status = 'Approved';
                    }
                    if ($row->status == 3) {
                        $status = 'Rejected';
                    }
                    $val['status'] = $status;
                    $val['created_at'] = date("d/m/Y", strtotime($row->created_at));
                    $btn = '<div class="list-icons"><div class="dropdown"><a href="#" class="list-icons-item" data-toggle="dropdown"><i class="icon-menu9"></i></a><div class="dropdown-menu dropdown-menu-right">';
                    //$url = URL::to("admin/hr/employee/delete/".$row->id."");  
                    $url2 = URL::to("admin/hr/employee/application_edit/" . $row->id . "?type=" . $row->application_type);
                    $url222 = URL::to("admin/hr/employee/application_print/" . $row->id);
                    $url4 = URL::to("admin/hr/employee/detail/" . $row->id . "?type=" . $row->application_type);
                    $url5 = URL::to("admin/hr/employee/resign_letter/" . $row['employeeget']->id);
                    $url9 = URL::to("admin/hr/employee/application_approve/" . $row->id . "/" . $row->application_type);
                    $btn .= '<a class="dropdown-item" href="' . $url4 . '" title="Application View"><i class="icon-eye8  mr-2"></i>Application View</a>';
                    if ($row->application_type == 1) {
                        $btn .= '<a class="dropdown-item" href="' . $url222 . '" title="Download & Print"><i class="icon-printer  mr-2"></i>Download & Print </a>';
                    }
                    if ($row->status == 0) {

                        if ($row->application_type == 1) {
                            $btn .= '<a class="dropdown-item" href="' . $url2 . '" title="Application Edit"><i class="icon-pencil7  mr-2"></i>Application Edit</a>';
                            $btn .= '<a class="dropdown-item" href="' . $url9 . '" title="Application Approved" ><i class="icon-checkmark4  mr-2"></i>Application Approved</a>';
                            $btn .= '<a class="dropdown-item" href="javascript:void(0)" title="Application Delete" onclick=deleteApplication("' . $row->id . '","' . $row->application_type . '");><i class="icon-trash-alt  mr-2"></i>Application Delete</a>';
                        } else {
                            $btn .= '<a class="dropdown-item" href="javascript:void(0)" title="Application Approved" onclick=applicationApproved("' . $row->id . '","' . $row->application_type . '");><i class="icon-checkmark4  mr-2"></i>Resign Approved</a>';
                            $btn .= '<a class="dropdown-item" href="javascript:void(0)" title="Application Delete" onclick=deleteApplication("' . $row->id . '","' . $row->application_type . '");><i class="icon-trash-alt  mr-2"></i>Resign Delete</a>';
                        }

                        if ($row->application_type == 2) {
                            $btn .= '<a class="dropdown-item" href="javascript:void(0)" title="Resgin Reject" onclick=rejectApplication("' . $row->id . '");><i class="icon-cross2  mr-2"></i>Resign Reject</a>';
                        }
                    }
                    if ($row->status == 1 && $row->application_type == 2) {
                        // $btn .= '<a class="dropdown-item" href="'.$url5.'" title="Resign Letter"><i class="icon-envelope  mr-2"></i>Resign Letter</a>'; 
                    }
                    $btn .= '</div></div></div>';
                    $val['action'] = $btn;
                    $rowReturn[] = $val;
                }
                $output = array("branch_id" => Auth::user()->branch_id, "draw" => $_POST['draw'], "recordsTotal" => $totalCount, "recordsFiltered" => $count, "data" => $rowReturn, );
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
    /**
     * Application delete .
     * Route: admin/hr/employee/application
     * Method: get 
     * @return  array()  Response
     */
    public function employeeApplicationDelete(Request $request)
    {
        //  print_r($_POST);die;
        DB::beginTransaction();
        try {
            if ($request->type == 2) {
                $getEmployeeId = EmployeeApplication::where('id', $request->id)->first('employee_id');
                $employeeId = $getEmployeeId->employee_id;
                $employeeResign['is_resigned'] = 0;
                $employeeResign['status'] = 1;
                $employeeResign['updated_at'] = $request->datetime;
                $employeeResignUpdate = Employee::find($employeeId);
                $employeeResignUpdate->update($employeeResign);
                $deleteApplicaton = EmployeeApplication::where('id', $request->id)->update(['status' => 9]);
            } else {
                $getEmployeeId = EmployeeApplication::where('id', $request->id)->first('employee_id');
                $employeeId = $getEmployeeId->employee_id;
                $deleteEmployeeDiploma = EmployeeDiploma::where('employee_id', $employeeId)->update(['status' => 9]);
                $deleteEmployeeQualification = EmployeeQualification::where('employee_id', $employeeId)->update(['status' => 9]);
                $deleteEmployeeExperience = EmployeeExperience::where('employee_id', $employeeId)->update(['status' => 9]);
                $deleteApplicaton = EmployeeApplication::where('id', $request->id)->update(['status' => 9]);
                $deleteEmployee = Employee::where('id', $employeeId)->update([
                    'status' => 9,
                    'is_customer'=>'0',
                    'customer_id'=>NULL,
                    'customer_code'=>NULL
                ]);
            }

            DB::commit();
            $msg = 'success';
            $data = 1;
            $return_array = compact('data', 'msg');
            return json_encode($return_array);

        } catch (\Exception $ex) {
            DB::rollback();
            $msg = $ex->getMessage();
            $data = 0;
            $return_array = compact('data', 'msg');
            return json_encode($return_array);
        }
    }
    /**
     * Show Employee Detail.
     * Route: admin/hr/employee/detail
     * Method: get 
     * @return  array()  Response
     */
    public function detail($id)
    {
        $data['title'] = 'Employee Management | Employee Detail';
        if (isset($_GET['type'])) {
            $data['application'] = EmployeeApplication::with('branch:id,name,branch_code')->with('company:id,name')->where('id', $id)->where('application_type', $_GET['type'])->first();
            $empID = $data['application']->employee_id;
            $data['type'] = $_GET['type'];
            $data['title'] = 'Employee Management | Employee Application Detail';
            $data['employee'] = Employee::where('id', $empID)->first();
            $customerID = $data['employee']->customer_id;            
            $data['member'] = Member::where('id',$customerID)->select('id','member_id')->first();
            $data['app'] = EmployeeApplication::where('employee_id', $empID)->where('application_type', 2)->orderby('id', 'DESC')->get();
        } else {
            $empID = $id;
            $data['employee'] = Employee::where('id', $empID)->first();
            $customerID = $data['employee']->customer_id;            
            $data['member'] = Member::where('id',$customerID)->select('id','member_id')->first();
            if ($data['employee']->is_resigned == 1) {
                $data['app'] = EmployeeApplication::where('employee_id', $empID)->where('application_type', 2)->orderby('id', 'DESC')->get();
            }
            if ($data['employee']->is_terminate == 1) {
                $data['terminate'] = EmployeeTerminate::where('employee_id', $empID)->get();
            }
            if ($data['employee']->is_transfer == 1) {
                $data['transfer'] = EmployeeTransfer::with(['transferBranch' => function ($query) {
                    $query->select('id', 'name'); }])->with(['transferBranchOld' => function ($query) {
                        $query->select('id', 'name'); }])->with(['transferEmployee' => function ($query) {
                            $query->select('*'); }])->where('employee_id', $empID)->orderby('id', 'DESC')->get();
            }
        }
        $data['qualification'] = EmployeeQualification::where('employee_id', $empID)->get();
        $data['diploma'] = EmployeeDiploma::where('employee_id', $empID)->get();
        $data['work'] = EmployeeExperience::where('employee_id', $empID)->get();
        //pd($data);
        return view('templates.admin.hr_management.employee.detail', $data);
    }
    /**
     * Add  Employee.
     * Route: admin/hr/employee/add
     * Method: get 
     * @return  array()  Response
     */
    public function add()
    {
        die();
        if (check_my_permission(Auth::user()->id, "109") != "1") {
            return redirect()->route('admin.dashboard');
        }
        $data['title'] = 'Employee Management | Employee Registration';
        if (Auth::user()->branch_id > 0) {
            $data['branch'] = \App\Models\Branch::select('id', 'branch_code', 'name')->where('status', 1)->where('id', Auth::user()->branch_id)->get();
        } else {
            $data['branch'] = \App\Models\Branch::select('id', 'branch_code', 'name')->where('status', 1)->get();
        }


        $data['designation'] = \App\Models\Designation::select('id', 'designation_name')->where('status', 1)->get();
        return view('templates.admin.hr_management.employee.register', $data);
    }
    /**
     * save  Employee.
     * Route: admin/hr/employee/add
     * Method: get 
     * @return  array()  Response
     */
    public function employeeSave(Request $request)
    {

        $rules = [
            'category' => ['required'],
            'company_id' => ['required'],
            'branch_id' => ['required'],
            'designation' => ['required'],
            'salary' => ['required', 'regex:/^\d*(\.\d{1,4})?$/'],
            'recommendation_employee_name' => ['required'],
            'applicant_name' => ['required'],
            'dob' => ['required'],
            'gender' => ['required']
        ];
        if ($request->esi_account_no != '') {
            $rules = ['esi_account_no' => ['unique:employees,esi_account_no']];
        }
        if ($request->pf_account_no != '') {
            $rules = ['pf_account_no' => ['unique:employees,pf_account_no']];
        }


        $customMessages = [
            'required' => ':Attribute  is required.',
            'unique' => ' :Attribute already exists.'
        ];
        $this->validate($request, $rules, $customMessages);
        DB::beginTransaction();
        try {
            $globaldate = $request->created_at;
            $select_date = $request->select_date;
            $current_date = date("Y-m-d", strtotime(convertDate($globaldate)));
            $entry_date = date("Y-m-d", strtotime(convertDate($select_date)));
            $entry_time = date("H:i:s", strtotime(convertDate($globaldate)));
            $date_create = $entry_date . ' ' . $entry_time;
            $created_at = date("Y-m-d H:i:s", strtotime(convertDate($date_create)));
            $updated_at = date("Y-m-d H:i:s", strtotime(convertDate($date_create)));
            $data['designation_id'] = $request->designation;
            $data['company_id'] = $request->company_id;
            $data['salary'] = $request->salary;
            $data['branch_id'] = $request->branch_id;
            $data['category'] = $request->category;
            $data['recommendation_employee_name'] = $request->recommendation_employee_name;
            $data['recom_employee_designation'] = $request->recommendation_employee_designation;
            $data['employee_name'] = $request->applicant_name;
            $data['dob'] = date("Y-m-d", strtotime(str_replace('/', '-', $request->dob)));
            $data['gender'] = $request->gender;
            $data['mobile_no'] = $request->mobile_no;
            $data['father_guardian_name'] = $request->guardian_name;
            $data['father_guardian_number'] = $request->guardian_number;
            $data['mother_name'] = $request->mother_name;
            $data['email'] = $request->email;
            $data['marital_status'] = $request->marital_status;
            $data['permanent_address'] = preg_replace("/\r|\n/", "", trimData($request->permanent_address));
            $data['current_address'] = preg_replace("/\r|\n/", "", trimData($request->current_address));
            $data['pen_card'] = $request->pen_number;
            $data['aadhar_card'] = $request->aadhar_number;
            $data['voter_id'] = $request->voter_id;
            $data['bank_name'] = $request->bank_name;
            $data['bank_branch_name'] = NULL;
            $data['bank_account_no'] = $request->account_no;
            $data['bank_ifsc_code'] = $request->ifsc_code;
            $data['bank_address'] = preg_replace("/\r|\n/", "", $request->bank_address);
            $data['language1'] = $request->language_known_1;
            $data['language2'] = $request->language_known_2;
            $data['language3'] = $request->language_known_3;
            $data['ssb_account'] = $request->ssb_account;
            $data['ssb_id'] = $request->ssb_account_id;
            $data['status'] = 0;
            $data['created_at'] = $created_at;
            $data['updated_at'] = $created_at;
            $data['esi_account_no'] = $request->esi_account_no;
            $data['pf_account_no'] = $request->pf_account_no;
            $create = Employee::create($data);
            $employeeId = $create->id;
            if ($request->hasFile('photo')) {
                $photo_image = $request->file('photo');
                $photo_filename = $employeeId . '_' . time() . '.' . $photo_image->getClientOriginalExtension();
                $photo_location = 'asset/employee/' . $photo_filename;
                $photo_location = 'employee/';
                ImageUpload::upload($photo_image, $photo_location, $photo_filename);
                // Image::make($photo_image)->resize(300,300)->save($photo_location);
                $empUpdate = Employee::find($employeeId);
                $empUpdate->photo = $photo_filename;
                $empUpdate->save();
            }
            $dataApp['employee_id'] = $employeeId;
            $dataApp['branch_id'] = $request->branch_id;
            $dataApp['application_type'] = 1;
            $dataApp['application_date'] = date("Y-m-d", strtotime(str_replace('/', '-', $request->select_date)));
            $dataApp['status'] = 0;
            $dataApp['created_at'] = $created_at;
            $dataApp['updated_at'] = $created_at;
            $dataApp['company_id'] = $request->company_id;
            $createApp = EmployeeApplication::create($dataApp);
            $dataExam['employee_id'] = $employeeId;
            $dataExam['exam_name'] = $_POST['examination'];
            $dataExam['exam_from'] = $_POST['examination_passed'];
            $dataExam['board_university'] = $_POST['university_name'];
            $dataExam['subject'] = $_POST['subject'];
            $dataExam['per_division'] = $_POST['division'];
            $dataExam['passing_year'] = $_POST['passing_year'];
            $dataExam['created_at'] = $created_at;
            $dataExam['status'] = 1;
            $dataExam['updated_at'] = $created_at;
            $createExam = EmployeeQualification::create($dataExam);
            if (isset($_POST['examination_more'])) {
                foreach (($_POST['examination_more']) as $key => $option) {
                    $dataExamMore['employee_id'] = $employeeId;
                    $dataExamMore['exam_name'] = $_POST['examination_more'][$key];
                    $dataExamMore['exam_from'] = $_POST['examination_passed_more'][$key];
                    $dataExamMore['board_university'] = $_POST['university_name_more'][$key];
                    $dataExamMore['subject'] = $_POST['subject_more'][$key];
                    $dataExamMore['per_division'] = $_POST['division_more'][$key];
                    $dataExamMore['passing_year'] = $_POST['passing_year_more'][$key];
                    $dataExamMore['created_at'] = $created_at;
                    $dataExamMore['status'] = 1;
                    $dataExamMore['updated_at'] = $created_at;
                    $createExamMore = EmployeeQualification::create($dataExamMore);
                }
            }
            if (isset($_POST['diploma_course']) && ($_POST['diploma_course'] != '')) {
                $dataDiploma['employee_id'] = $employeeId;
                $dataDiploma['course'] = $_POST['diploma_course'];
                $dataDiploma['academy'] = $_POST['academy'];
                $dataDiploma['board_university'] = $_POST['diploma_university_name'];
                $dataDiploma['subject'] = $_POST['diploma_subject'];
                $dataDiploma['per_division'] = $_POST['diploma_division'];
                $dataDiploma['passing_year'] = $_POST['diploma_passing_year'];
                $dataDiploma['created_at'] = $created_at;
                $dataDiploma['status'] = 1;
                $dataDiploma['updated_at'] = $created_at;
                $createDiploma = EmployeeDiploma::create($dataDiploma);
            }
            if (isset($_POST['diploma_course_more'])) {
                foreach (($_POST['diploma_course_more']) as $key => $option) {
                    $dataDiplomaMore['employee_id'] = $employeeId;
                    $dataDiplomaMore['course'] = $_POST['diploma_course_more'][$key];
                    $dataDiplomaMore['academy'] = $_POST['academy_more'][$key];
                    $dataDiplomaMore['board_university'] = $_POST['diploma_university_name_more'][$key];
                    $dataDiplomaMore['subject'] = $_POST['diploma_subject_more'][$key];
                    $dataDiplomaMore['per_division'] = $_POST['diploma_division_more'][$key];
                    $dataDiplomaMore['passing_year'] = $_POST['diploma_passing_year_more'][$key];
                    $dataDiplomaMore['created_at'] = $created_at;
                    $dataDiplomaMore['updated_at'] = $created_at;
                    $dataDiplomaMore['status'] = 1;
                    $createDiplomaMore = EmployeeDiploma::create($dataDiplomaMore);

                }
            }
            if (isset($_POST['company_name']) && ($_POST['company_name'] != '')) {
                $total = date("Y", strtotime(str_replace('/', '-', $_POST['work_end']))) - date("Y", strtotime(str_replace('/', '-', $_POST['work_start'])));
                $dataWork['employee_id'] = $employeeId;
                $dataWork['company_name'] = $_POST['company_name'];
                $dataWork['total_year'] = $total;
                $dataWork['from_date'] = date("Y-m-d", strtotime(str_replace('/', '-', $_POST['work_start'])));
                $dataWork['to_date'] = date("Y-m-d", strtotime(str_replace('/', '-', $_POST['work_end'])));
                $dataWork['work_nature'] = $_POST['nature_work'];
                $dataWork['salary'] = $_POST['work_salary'];
                $dataWork['supervisor_name'] = $_POST['reference_name'];
                $dataWork['supervisor_number'] = $_POST['reference_no'];
                $dataWork['created_at'] = $created_at;
                $dataWork['status'] = 1;
                $dataWork['updated_at'] = $created_at;
                $createWork = EmployeeExperience::create($dataWork);
            }
            if (isset($_POST['company_name_more'])) {
                foreach (($_POST['company_name_more']) as $key => $option) {
                    $totalmore = date("Y", strtotime(str_replace('/', '-', $_POST['work_end_more'][$key]))) - date("Y", strtotime(str_replace('/', '-', $_POST['work_start_more'][$key])));
                    $dataWorkMore['employee_id'] = $employeeId;
                    $dataWorkMore['company_name'] = $_POST['company_name_more'][$key];
                    $dataWorkMore['total_year'] = $totalmore;
                    $dataWorkMore['from_date'] = date("Y-m-d", strtotime(str_replace('/', '-', $_POST['work_start_more'][$key])));
                    $dataWorkMore['to_date'] = date("Y-m-d", strtotime(str_replace('/', '-', $_POST['work_end_more'][$key])));
                    $dataWorkMore['work_nature'] = $_POST['nature_work_more'][$key];
                    $dataWorkMore['salary'] = $_POST['work_salary_more'][$key];
                    $dataWorkMore['supervisor_name'] = $_POST['reference_name_more'][$key];
                    $dataWorkMore['supervisor_number'] = $_POST['reference_no_more'][$key];
                    $dataWorkMore['created_at'] = $created_at;
                    $dataWorkMore['updated_at'] = $created_at;
                    $dataWorkMore['status'] = 1;
                    $createWorkMore = EmployeeExperience::create($dataWorkMore);

                }
            }

            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('alert', $ex->getMessage());
        }
        // return redirect('admin/hr/employee/application_print/' . $createApp->id)->with('success', 'Employee Application Created  Successfully');
        return redirect('admin/hr/employee/detail/' . $createApp->id)->with('success', 'Employee Application Created  Successfully');
    }
    /**
     * Edit  Employee.
     * Route: admin/hr/employee/edit
     * Method: get 
     * @return  array()  Response
     */
    public function edit($id)
    {
        $data['title'] = 'Employee Management | Employee Edit';
        $data['branch'] = \App\Models\Branch::where('status', 1)->get();

        $empID = $id;

        $data['employee'] = Employee::with('company:id,name')->where('id', $empID)->first();
        $customerID = $data['employee']->customer_id;             
        $data['member'] = Member::where('id',$customerID)->select('id','member_id')->first();
        $data['qualification'] = EmployeeQualification::where('employee_id', $empID)->get();
        $data['diploma'] = EmployeeDiploma::where('employee_id', $empID)->get();
        $data['work'] = EmployeeExperience::where('employee_id', $empID)->get();
        return view('templates.admin.hr_management.employee.edit', $data);
    }
    /**
     * Update  Employee.
     * Route: admin/hr/employee/edit
     * Method: get 
     * @return  array()  Response
     */
    public function employeeUpdate(Request $request)
    {
        $employeeId = $request->employee_id;
        $companyId = $request->company_id;

        $rules = [
            'category' => ['required'],
            // 'branch_id' => ['required'],
            'designation' => ['required'],
            'salary' => ['required', 'regex:/^\d*(\.\d{1,4})?$/'],
            'recommendation_employee_name' => ['required'],
            'applicant_name' => ['required'],
            'dob' => ['required'],
            // 'company_id' => ['required'],
            'gender' => ['required']
        ];
        if ($request->esi_account_no != '') {
            $rules = ['esi_account_no' => ['unique:employees,esi_account_no,' . $employeeId]];
        }
        if ($request->pf_account_no != '') {
            $rules = ['pf_account_no' => ['unique:employees,pf_account_no,' . $employeeId]];
        }

        $customMessages = [
            'required' => ':Attribute  is required.',
            'unique' => ' :Attribute already exists.'
        ];
        $this->validate($request, $rules, $customMessages);
        DB::beginTransaction();
        try {
            $globaldate = $request->created_at;
            $select_date = $request->select_date;
            $current_date = date("Y-m-d", strtotime(convertDate($globaldate)));
            $entry_date = date("Y-m-d", strtotime(convertDate($select_date)));
            $entry_time = date("H:i:s", strtotime(convertDate($globaldate)));
            $date_create = $entry_date . ' ' . $entry_time;
            $created_at = date("Y-m-d H:i:s", strtotime(convertDate($date_create)));
            $updated_at = date("Y-m-d H:i:s", strtotime(convertDate($date_create)));
            $data['designation_id'] = $request->designation;
            $data['salary'] = $request->salary;
            // $data['company_id'] = $request->company_id;
            // $data['branch_id'] = $request->branch_id;   
            $data['category'] = $request->category;
            $data['recommendation_employee_name'] = $request->recommendation_employee_name;
            $data['recom_employee_designation'] = $request->recom_employee_designation;
            $data['employee_name'] = $request->applicant_name;
            $data['dob'] = date("Y-m-d", strtotime(str_replace('/', '-', $request->dob)));
            $data['gender'] = $request->gender;
            $data['mobile_no'] = $request->mobile_no;
            $data['father_guardian_name'] = $request->guardian_name;
            $data['father_guardian_number'] = $request->guardian_number;
            $data['mother_name'] = $request->mother_name;
            $data['email'] = $request->email;
            $data['marital_status'] = $request->marital_status;
            $data['permanent_address'] = preg_replace("/\r|\n/", "", trimData($request->permanent_address));
            $data['current_address'] = preg_replace("/\r|\n/", "", trimData($request->current_address));
            $data['pen_card'] = $request->pen_number;
            $data['aadhar_card'] = $request->aadhar_number;
            $data['voter_id'] = $request->voter_id;
            $data['bank_name'] = $request->bank_name;
            $data['bank_branch_name'] = NULL;
            $data['bank_account_no'] = $request->account_no;
            $data['bank_ifsc_code'] = $request->ifsc_code;
            $data['bank_address'] = preg_replace("/\r|\n/", "", $request->bank_address);
            $data['language1'] = $request->language_known_1;
            $data['language2'] = $request->language_known_2;
            $data['language3'] = $request->language_known_3;
            $data['ssb_account'] = $request->ssb_account;
            $data['ssb_id'] = $request->ssb_account_id;
            $data['created_at'] = $created_at;
            $data['updated_at'] = $created_at;
            $data['esi_account_no'] = $request->esi_account_no;
            $data['pf_account_no'] = $request->pf_account_no;
            $employeeupdate = Employee::find($employeeId);
            $employeeupdate->update($data);
            if ($request->hasFile('photo')) {
                $photo_image = $request->file('photo');
                $photo_filename = $employeeId . '_' . time() . '.' . $photo_image->getClientOriginalExtension();
                // $photo_location = 'asset/employee/' . $photo_filename;
                $photo_location = 'employee/';
                ImageUpload::upload($photo_image, $photo_location, $photo_filename);
                // Image::make($photo_image)->resize(300,300)->save($photo_location);
                $empUpdate = Employee::find($employeeId);
                $empUpdate->photo = $photo_filename;
                $empUpdate->save();
            }
            if (isset($_POST['app_id'])) {
                $dataApp['created_at'] = $created_at;
                $dataApp['branch_id'] = $request->branch_id;
                $dataApp['company_id'] = $companyId;
                $Appupdate = EmployeeApplication::find($_POST['app_id']);
                $Appupdate->update($dataApp);
            }

            if (isset($_POST['examination']) && ($_POST['examination'] != '')) {
                $dataExam['employee_id'] = $employeeId;
                $dataExam['exam_name'] = $_POST['examination'];
                $dataExam['exam_from'] = $_POST['examination_passed'];
                $dataExam['board_university'] = $_POST['university_name'];
                $dataExam['subject'] = $_POST['subject'];
                $dataExam['per_division'] = $_POST['division'];
                $dataExam['passing_year'] = $_POST['passing_year'];
                $dataExam['created_at'] = $created_at;
                $dataExam['updated_at'] = $created_at;
                $createExam = EmployeeQualification::create($dataExam);
            }
            if (isset($_POST['examination_more'])) {
                foreach (($_POST['examination_more']) as $key => $option) {
                    $dataExamMore['employee_id'] = $employeeId;
                    $dataExamMore['exam_name'] = $_POST['examination_more'][$key];
                    $dataExamMore['exam_from'] = $_POST['examination_passed_more'][$key];
                    $dataExamMore['board_university'] = $_POST['university_name_more'][$key];
                    $dataExamMore['subject'] = $_POST['subject_more'][$key];
                    $dataExamMore['per_division'] = $_POST['division_more'][$key];
                    $dataExamMore['passing_year'] = $_POST['passing_year_more'][$key];
                    $dataExamMore['created_at'] = $created_at;
                    $dataExamMore['updated_at'] = $created_at;
                    $createExamMore = EmployeeQualification::create($dataExamMore);

                }
            }
            if (isset($_POST['qualification_id'])) {
                foreach (($_POST['qualification_id']) as $key => $option) {
                    //$updateExamold['employee_id'] = $employeeId; 
                    $updateExamold['exam_name'] = $_POST['examination_old'][$key];
                    $updateExamold['exam_from'] = $_POST['examination_passed_old'][$key];
                    $updateExamold['board_university'] = $_POST['university_name_old'][$key];
                    $updateExamold['subject'] = $_POST['subject_old'][$key];
                    $updateExamold['per_division'] = $_POST['division_old'][$key];
                    $updateExamold['passing_year'] = $_POST['passing_year_old'][$key];
                    $updateExamolddata = EmployeeQualification::find($_POST['qualification_id'][$key]);
                    $updateExamolddata->update($updateExamold);

                }
            }
            if (isset($_POST['diploma_course']) && ($_POST['diploma_course'] != '')) {
                $dataDiploma['employee_id'] = $employeeId;
                $dataDiploma['course'] = $_POST['diploma_course'];
                $dataDiploma['academy'] = $_POST['academy'];
                $dataDiploma['board_university'] = $_POST['diploma_university_name'];
                $dataDiploma['subject'] = $_POST['diploma_subject'];
                $dataDiploma['per_division'] = $_POST['diploma_division'];
                $dataDiploma['passing_year'] = $_POST['diploma_passing_year'];
                $dataDiploma['created_at'] = $created_at;
                $dataDiploma['updated_at'] = $created_at;
                $createDiploma = EmployeeDiploma::create($dataDiploma);
            }
            if (isset($_POST['diploma_id_old'])) {
                foreach (($_POST['diploma_id_old']) as $key => $option) {
                    // $dataDiplomaMore['employee_id'] = $employeeId; 
                    $dataDiploma_old['course'] = $_POST['diploma_course_old'][$key];
                    $dataDiploma_old['academy'] = $_POST['academy_old'][$key];
                    $dataDiploma_old['board_university'] = $_POST['diploma_university_name_old'][$key];
                    $dataDiploma_old['subject'] = $_POST['diploma_subject_old'][$key];
                    $dataDiploma_old['per_division'] = $_POST['diploma_division_old'][$key];
                    $dataDiploma_old['passing_year'] = $_POST['diploma_passing_year_old'][$key];
                    $updateDiplomaold = EmployeeDiploma::find($_POST['diploma_id_old'][$key]);
                    $updateDiplomaold->update($dataDiploma_old);

                }
            }
            if (isset($_POST['diploma_course_more'])) {
                foreach (($_POST['diploma_course_more']) as $key => $option) {
                    $dataDiplomaMore['employee_id'] = $employeeId;
                    $dataDiplomaMore['course'] = $_POST['diploma_course_more'][$key];
                    $dataDiplomaMore['academy'] = $_POST['academy_more'][$key];
                    $dataDiplomaMore['board_university'] = $_POST['diploma_university_name_more'][$key];
                    $dataDiplomaMore['subject'] = $_POST['diploma_subject_more'][$key];
                    $dataDiplomaMore['per_division'] = $_POST['diploma_division_more'][$key];
                    $dataDiplomaMore['passing_year'] = $_POST['diploma_passing_year_more'][$key];
                    $dataDiplomaMore['created_at'] = $created_at;
                    $dataDiplomaMore['updated_at'] = $created_at;
                    $createDiplomaMore = EmployeeDiploma::create($dataDiplomaMore);

                }
            }
            if (isset($_POST['company_name']) && ($_POST['company_name'] != '')) {
                $total = date("Y", strtotime(str_replace('/', '-', $_POST['work_end']))) - date("Y", strtotime(str_replace('/', '-', $_POST['work_start'])));
                $dataWork['employee_id'] = $employeeId;
                $dataWork['company_name'] = $_POST['company_name'];
                $dataWork['total_year'] = $total;
                $dataWork['from_date'] = date("Y-m-d", strtotime(str_replace('/', '-', $_POST['work_start'])));
                $dataWork['to_date'] = date("Y-m-d", strtotime(str_replace('/', '-', $_POST['work_end'])));
                $dataWork['work_nature'] = $_POST['nature_work'];
                $dataWork['salary'] = $_POST['work_salary'];
                $dataWork['supervisor_name'] = $_POST['reference_name'];
                $dataWork['supervisor_number'] = $_POST['reference_no'];
                $dataWork['created_at'] = $created_at;
                $dataWork['updated_at'] = $created_at;
                $createWork = EmployeeExperience::create($dataWork);
            }
            if (isset($_POST['company_name_more'])) {
                foreach (($_POST['company_name_more']) as $key => $option) {
                    $totalmore = date("Y", strtotime(str_replace('/', '-', $_POST['work_end_more'][$key]))) - date("Y", strtotime(str_replace('/', '-', $_POST['work_start_more'][$key])));
                    $dataWorkMore['employee_id'] = $employeeId;
                    $dataWorkMore['company_name'] = $_POST['company_name_more'][$key];
                    $dataWorkMore['total_year'] = $totalmore;
                    $dataWorkMore['from_date'] = date("Y-m-d", strtotime(str_replace('/', '-', $_POST['work_start_more'][$key])));
                    $dataWorkMore['to_date'] = date("Y-m-d", strtotime(str_replace('/', '-', $_POST['work_end_more'][$key])));
                    $dataWorkMore['work_nature'] = $_POST['nature_work_more'][$key];
                    $dataWorkMore['salary'] = $_POST['work_salary_more'][$key];
                    $dataWorkMore['supervisor_name'] = $_POST['reference_name_more'][$key];
                    $dataWorkMore['supervisor_number'] = $_POST['reference_no_more'][$key];
                    $createWorkMore = EmployeeExperience::create($dataWorkMore);

                }
            }
            if (isset($_POST['company_name_old'])) {
                foreach (($_POST['company_name_old']) as $key => $option) {
                    $totalmore = date("Y", strtotime(str_replace('/', '-', $_POST['work_end_old'][$key]))) - date("Y", strtotime(str_replace('/', '-', $_POST['work_start_old'][$key])));
                    // $dataWorkMore['employee_id'] = $employeeId; 
                    $dataWorkMore['company_name'] = $_POST['company_name_old'][$key];
                    $dataWorkMore['total_year'] = $totalmore;
                    $dataWorkMore['from_date'] = date("Y-m-d", strtotime(str_replace('/', '-', $_POST['work_start_old'][$key])));
                    $dataWorkMore['to_date'] = date("Y-m-d", strtotime(str_replace('/', '-', $_POST['work_end_old'][$key])));
                    $dataWorkMore['work_nature'] = $_POST['nature_work_old'][$key];
                    $dataWorkMore['salary'] = $_POST['work_salary_old'][$key];
                    $dataWorkMore['supervisor_name'] = $_POST['reference_name_old'][$key];
                    $dataWorkMore['supervisor_number'] = $_POST['reference_no_old'][$key];
                    $updateworkold = EmployeeExperience::find($_POST['work_id'][$key]);
                    $updateworkold->update($dataWorkMore);

                }
            }

            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('alert', $ex->getMessage());
        }
        if (isset($_POST['app_id'])) {
            return redirect('admin/hr/employee/application')->with('success', 'Employee Application Updated Successfully');
        } else {
            return redirect('admin/hr/employee')->with('success', 'Employee Updated Successfully');
        }
    }
    /**
     * designation data  get by id.
     * Route: /hr 
     * Method: get 
     * @return  view
     */
    public function designationDataGet(Request $request)
    {
        //echo $request->start_date;die;
        $data = \App\Models\Designation::where('id', $request->designation)->first();
        //print_r($data);die; 
        if ($data) {
            $salary = number_format((float) (($data->basic_salary + $data->daily_allowances + $data->hra + $data->hra_metro_city + $data->uma + $data->convenience_charges + $data->maintenance_allowance + $data->communication_allowance + $data->prd + $data->ia + $data->ca + $data->fa) - ($data->pf + $data->tds)), 2, '.', '');
            $msg = 1;

        } else {
            $data = '';
            $salary = '';
            $msg = 0;

        }
        $return_array = compact('data', 'salary', 'msg');
        return json_encode($return_array);
    }
    /**
     * Application approved .
     * Route: admin/hr/employee/application
     * Method: get 
     * @return  array()  Response
     */
    public function employeeApplicationApprove(Request $request)
    {
        //  print_r($_POST);die;

        //   $rules = [
        //     'employee_code' => ['required','unique:employees,employee_code'],
        // ];
        // $customMessages = [
        //     'required' => ':Attribute  is required.',
        //     'unique' => ' :Attribute already exists.'
        // ];
        //  $this->validate($request, $rules, $customMessages);


        DB::beginTransaction();
        try {
            if ($request->type == 2) {
                $getEmployeeId = EmployeeApplication::where('id', $request->id)->first('employee_id');
                $employeeId = $getEmployeeId->employee_id;
                $employeeResign['is_resigned'] = 1;
                $employeeResign['status'] = 0;
                $employeeResign['updated_at'] = $request->datetime;
                $employeeResignUpdate = Employee::find($employeeId);
                $employeeResignUpdate->update($employeeResign);
                $empAppResign['status'] = 1;
                $empAppResign['status_date'] = date("Y-m-d", strtotime(str_replace('/', '-', $request->date)));
                $empAppResign['updated_at'] = $request->datetime;
                $empAppResignUpdate = EmployeeApplication::find($request->id);
                $empAppResignUpdate->update($empAppResign);
            } else {
                $getEmployeeId = EmployeeApplication::where('id', $request->id)->first('employee_id');
                $employeeId = $getEmployeeId->employee_id;
                $getEmployeeData = Employee::where('id', $employeeId)->first();
                $getMiCode = getEmployeeMiCode($getEmployeeData->category);
                // print_r(count($getMiCode));
                if (!empty($getMiCode)) {
                    if ($getMiCode->mi_code > 0) {
                        $miCode = $getMiCode->mi_code + 1;
                    } else {
                        $miCode = 110;
                    }

                } else {
                    $miCode = 110;
                }
                $miCodeEmp = str_pad($miCode, 4, '0', STR_PAD_LEFT);
                if ($getEmployeeData->category == 1) {
                    $employee_code = $miCodeEmp;
                } else {
                    $employee_code = $miCodeEmp . 'c';
                }
                //echo $employee_code;die;
                $employee['employee_code'] = $employee_code;
                $employee['mi_code'] = $miCode;
                $employee['mi_category'] = $request->category;
                $employee['category'] = $request->category;
                $employee['designation_id'] = $request->designation;
                $employee['salary'] = $request->salary;
                $employee['is_employee'] = 1;
                $employee['employee_date'] = date("Y-m-d", strtotime(str_replace('/', '-', $request->date)));
                $employee['status'] = 1;
                $employee['updated_at'] = $request->datetime;
                $employeeUpdate = Employee::find($employeeId);
                $employeeUpdate->update($employee);
                $empApp['status'] = 1;
                $empApp['status_date'] = date("Y-m-d", strtotime(str_replace('/', '-', $request->date)));
                $empApp['updated_at'] = $request->datetime;
                $empAppUpdate = EmployeeApplication::find($request->id);
                $empAppUpdate->update($empApp);
            }

            DB::commit();
            $msg = 'success';
            $data = 1;
            $return_array = compact('data', 'msg');
            return json_encode($return_array);

        } catch (\Exception $ex) {
            DB::rollback();
            $msg = $ex->getMessage();
            $data = 0;
            $return_array = compact('data', 'msg');
            return json_encode($return_array);
        }
    }
    /**
     * Application reject .
     * Route: admin/hr/employee/application
     * Method: get 
     * @return  array()  Response
     */
    public function employeeApplicationReject(Request $request)
    {
        //  print_r($_POST);die;
        DB::beginTransaction();
        try {

            $getEmployeeId = EmployeeApplication::where('id', $request->id)->first('employee_id');
            $employeeId = $getEmployeeId->employee_id;
            $employeeResign['is_resigned'] = 0;
            $employeeResign['status'] = 1;
            $employeeResign['updated_at'] = $request->datetime;
            $employeeResignUpdate = Employee::find($employeeId);
            $employeeResignUpdate->update($employeeResign);
            $empAppResign['status'] = 3;
            $empAppResign['status_date'] = date("Y-m-d", strtotime(str_replace('/', '-', $request->date)));
            $empAppResign['updated_at'] = $request->datetime;
            $empAppResignUpdate = EmployeeApplication::find($request->id);
            $empAppResignUpdate->update($empAppResign);


            DB::commit();
            $msg = 'success';
            $data = 1;
            $return_array = compact('data', 'msg');
            return json_encode($return_array);

        } catch (\Exception $ex) {
            DB::rollback();
            $msg = $ex->getMessage();
            $data = 0;
            $return_array = compact('data', 'msg');
            return json_encode($return_array);
        }
    }
    /**
     * Show Employee Detail.
     * Route: admin/hr/employee/detail
     * Method: get 
     * @return  array()  Response
     */
    public function resignLetter($id)
    {
        $data['title'] = 'Employee Management | Employee Resign Letter';
        $data['employee'] = Employee::where('id', $id)->first();

        return view('templates.admin.hr_management.employee.resign_letter', $data);
    }
    /**
     *  Employee Resign request.
     * Route: admin/hr/employee/detail
     * Method: get 
     * @return  array()  Response
     */
    public function resignRequest()
    {

        if (check_my_permission(Auth::user()->id, "110") != "1") {
            return redirect()->route('admin.dashboard');
        }
        $data['title'] = 'Employee Management | Employee Resign Request';


        return view('templates.admin.hr_management.employee.resign_request', $data);
    }
    /**
     * employee data  get by code.
     * Route: /hr 
     * Method: get 
     * @return  view
     */
    public function employeeDataGet(Request $request)
    {
        //echo $request->start_date;die;
        $data = Employee::with(['branch' => function ($query) {
            $query->select('id', 'name'); }, 'company' => function ($query) {
                $query->select('id', 'name'); }, 'loan:emp_code'])->where('employee_code', $request->code)->first();
        ;


        if (!is_null(Auth::user()->branch_ids)) {
            $branch_ids = Auth::user()->branch_ids;
            $data = $data->whereIn('branch_id', explode(",", $branch_ids));
        }
        if (!empty($data)) {
            $data = $data;
            $msg = 1;
            $designation = getDesignationData('designation_name', $data->designation_id)->designation_name;
        } else {
            $data = $data;
            $msg = 0;
            $designation = '';
        }
        $return_array = compact('data', 'msg', 'designation');
        return json_encode($return_array);
    }
    /**
     * employee data  get by code.
     * Route: /hr 
     * Method: get 
     * @return  view
     */
    public function resignRequestSave(Request $request)
    {
        $rules = [
            'company_id' => ['required'],
            'employee_code' => ['required'],
            'employee_name' => ['required'],
            'branch' => ['required'],
            'application_file' => ['required'],
            'remark' => ['required'],
        ];
        $customMessages = [
            'required' => ':Attribute  is required.',
            'unique' => ' :Attribute already exists.'
        ];
        $this->validate($request, $rules, $customMessages);
        //  print_r($_POST);die;
        DB::beginTransaction();
        try {
            $companyId = $request->company_id;
            $employeeId = $request->employee_id;
            $data = Employee::where('id', $employeeId)->first(['is_terminate', 'is_resigned']);
            //  print_r($data->is_resigned);die;
            if ($data->is_resigned == 1) {
                return back()->with('alert', 'You cannot apply for resignation. Because the application already submitted!');
            }
            if ($data->is_resigned == 2) {
                return back()->with('alert', 'You cannot apply for resignation. Because the application already approved!');
            }
            if ($data->is_terminate == 1) {
                return back()->with('alert', 'You cannot apply for resignation. Because the employee has already been terminated by the administration!');
            }
            $employeeResign['is_resigned'] = 1;
            //$employeeResign['updated_at'] = $request->datetime;          
            $employeeResignUpdate = Employee::find($employeeId);
            $employeeResignUpdate->update($employeeResign);
            if ($request->hasFile('application_file')) {
                $application_file = $request->file('application_file');
                $application_file_filename = $employeeId . '_' . time() . '.' . $application_file->getClientOriginalExtension();
                $photo_location = 'asset/employee/resign/' . $application_file_filename;
                $application_location = 'employee/resign/';
                ImageUpload::upload($application_file, $application_location, $application_file_filename);
                // $request->application_file->move('asset/employee/resign/', $application_file_filename); 
            } else {
                $application_file_filename = '';
            }
            $dataApp['company_id'] = $companyId;
            $dataApp['employee_id'] = $employeeId;
            $dataApp['branch_id'] = $request->branch_id;
            $dataApp['application_type'] = 2;
            $dataApp['application_date'] = date("Y-m-d", strtotime(str_replace('/', '-', $request->create_application_date)));
            $dataApp['status'] = 0;
            $dataApp['resign_file'] = $application_file_filename;
            $dataApp['resign_remark'] = $request->remark;
            $dataApp['created_at'] = $request->created_at;
            $dataApp['updated_at'] = $request->created_at;
            $createApp = EmployeeApplication::create($dataApp);

            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('alert', $ex->getMessage());
        }
        return redirect('admin/hr/employee/application')->with('success', 'Resignation Submitted Successfully');
    }
    /**
     * Show Employee transfer letter.
     * Route: admin/hr/employee
     * Method: get 
     * @return  array()  Response
     */
    public function transferLetter($id)
    {
        $data['title'] = 'Employee Management | Employee Transfer Letter';
        if (isset($_GET['type'])) {
            $letter_id = $_GET['type'];
            // echo $letter_id;die;
            $data['transfer'] = EmployeeTransfer::with(['transferBranch' => function ($query) {
                $query->select('*'); }])->with(['transferBranchOld' => function ($query) {
                    $query->select('*'); }])->where('id', $letter_id)->orderby('id', 'DESC')->first();
        } else {
            $letter_id = $id;
            $data['transfer'] = EmployeeTransfer::with(['transferBranch' => function ($query) {
                $query->select('*'); }])->with(['transferBranchOld' => function ($query) {
                    $query->select('*'); }])->where('employee_id', $letter_id)->orderby('id', 'DESC')->first();
        }
        $data['employee'] = Employee::with('company:id,name')->where('id', $id)->first();

        $data['setting'] = \App\Models\Settings::first();

        return view('templates.admin.hr_management.employee.transfer_letter', $data);
    }
    /**
     * Show Employee termination letter.
     * Route: admin/hr/employee
     * Method: get 
     * @return  array()  Response
     */
    public function terminationLetter($id)
    {
        $data['title'] = 'Employee Management | Employee Termination Letter';
        $data['employee'] = Employee::where('id', $id)->first();

        return view('templates.admin.hr_management.employee.termination_letter', $data);
    }
    /**
     *  Employee Terminate.
     * Route: admin/hr/employee/terminate
     * Method: get 
     * @return  array()  Response
     */
    public function terminateRequest()
    {
        if (check_my_permission(Auth::user()->id, "113") != "1") {
            return redirect()->route('admin.dashboard');
        }
        $data['title'] = 'Employee Management | Employee Terminate';
        return view('templates.admin.hr_management.employee.termination_request', $data);
    }
    /**
     * employee terminate save.
     * Route: admin/hr/employee/terminate
     * Method: get 
     * @return  view
     */
    public function terminateRequestSave(Request $request)
    {
        $rules = [
            'employee_code' => ['required'],
            'employee_name' => ['required'],
            'branch' => ['required'],
            'remark' => ['required'],

        ];
        $customMessages = [
            'required' => ':Attribute  is required.',
            'unique' => ' :Attribute already exists.'
        ];
        $this->validate($request, $rules, $customMessages);
        //  print_r($_POST);die;
        DB::beginTransaction();
        try {
            $employeeId = $request->employee_id;
            $data = Employee::where('id', $employeeId)->first(['is_terminate', 'status']);
            if ($data->status == 0) {
                return back()->with('alert', 'You cannot terminate. Because the employee inactive!');
            }
            if ($data->is_terminate == 1) {
                return back()->with('alert', 'You cannot terminate. Because the employee already terminated!');
            }

            $employeeTerminate['is_terminate'] = 1;
            $employeeTerminate['status'] = 0;
            $employeeTerminate['terminate_date'] = date("Y-m-d", strtotime(str_replace('/', '-', $request->create_application_date)));
            // $employeeTerminate['updated_at'] = $request->datetime;          
            $employeeTerminateUpdate = Employee::find($employeeId);
            $employeeTerminateUpdate->update($employeeTerminate);

            $dataEmployeeTerminate['employee_id'] = $employeeId;
            $dataEmployeeTerminate['terminate_date'] = date("Y-m-d", strtotime(str_replace('/', '-', $request->create_application_date)));
            $dataEmployeeTerminate['status'] = 1;
            $dataEmployeeTerminate['remark'] = $request->remark;
            $dataEmployeeTerminate['created_at'] = $request->created_at;
            //$dataEmployeeTerminate['updated_at'] = $request->created_at;          
            $createEmployeeTerminate = EmployeeTerminate::create($dataEmployeeTerminate);

            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('alert', $ex->getMessage());
        }
        return redirect('admin/hr/employee')->with('success', 'Employee Terminated Successfully');
    }
    /**
     *  Employee Transfer.
     * Route: admin/hr/employee/transfer
     * Method: get 
     * @return  view
     */
    public function transferRequest()
    {
        if (check_my_permission(Auth::user()->id, "115") != "1") {
            return redirect()->route('admin.dashboard');
        }

        $data['title'] = 'Employee Management | Employee Transfer';
        $data['designation'] = Designation::where('status', 1)->get();

        if (Auth::user()->branch_id > 0) {
            $id = Auth::user()->branch_id;
            $data['branch'] = Branch::where('status', 1)->where('id', '=', $id)->get();
        } else {
            $data['branch'] = Branch::where('status', 1)->get();
        }

        return view('templates.admin.hr_management.employee.transfer_request', $data);
    }
    /**
     *  Employee Transfer.
     * Route: admin/hr/employee/transfer
     * Method: get 
     * @return  view
     */
    public function transferRequestSave(Request $request)
    {

        $rules = [
            'employee_code' => ['required'],
            'employee_name' => ['required'],
            'branch' => ['required'],
            'designation' => ['required'],
            'category' => ['required'],
            'salary' => ['required'],
            'rec_employee_name' => ['required'],
            'transfer_date' => ['required'],
            'transfer_branch' => ['required'],
            'transfer_designation' => ['required'],
            'transfer_rec_employee_name' => ['required'],
            'transfer_salary' => ['required'],
            'transfer_category' => ['required'],
            'file' => ['required'],
        ];
        $customMessages = [
            'required' => ':Attribute  is required.',
            'unique' => ' :Attribute already exists.'
        ];
        $this->validate($request, $rules, $customMessages);
        //print_r($_POST);die;
        DB::beginTransaction();
        try {
            $employeeId = $request->employee_id;
            $data = Employee::where('id', $employeeId)->first(['is_terminate', 'status']);
            if ($data->status == 0) {
                return back()->with('alert', 'You cannot Transfer. Because the employee inactive!');
            }
            if ($data->is_terminate == 1) {
                return back()->with('alert', 'You cannot Transfer. Because the employee terminated!');
            }


            $employeeTransfer['designation_id'] = $request->transfer_designation;
            $employeeTransfer['salary'] = $request->transfer_salary;
            $employeeTransfer['branch_id'] = $request->transfer_branch;
            $employeeTransfer['category'] = $request->transfer_category;
            //$employeeTransfer['recommendation_employee_name'] = $request->transfer_rec_employee_name;
            $employeeTransfer['is_transfer'] = 1;
            $employeeTransfer['updated_at'] = date("Y-m-d", strtotime(str_replace('/', '-', $request->transfer_date)));
            $employeeTransferUpdate = Employee::find($employeeId);
            $employeeTransferUpdate->update($employeeTransfer);

            if ($request->hasFile('file')) {
                $application_file = $request->file('file');
                $application_file_filename = $employeeId . '_' . time() . '.' . $application_file->getClientOriginalExtension();
                $photo_location = 'asset/employee/transfer/' . $application_file_filename;
                $application_location = 'employee/transfer/';
                ImageUpload::upload($application_file, $application_location, $application_file_filename);
                //  $request->file->move('asset/employee/transfer/', $application_file_filename);                  
                //Image::make($application_file)->save($photo_location); 
            } else {
                $application_file_filename = '';
            }
            $dataEmployeeTransfer['employee_id'] = $employeeId;
            $dataEmployeeTransfer['old_designation_id'] = $request->designation_id;
            $dataEmployeeTransfer['old_salary'] = $request->salary;
            $dataEmployeeTransfer['old_branch_id'] = $request->branch_id;
            $dataEmployeeTransfer['old_category'] = $request->category_id;
            $dataEmployeeTransfer['old_recommendation_name'] = $request->rec_employee_name;
            $dataEmployeeTransfer['old_recom_designation'] = $request->rec_employee_designation;
            $dataEmployeeTransfer['designation_id'] = $request->transfer_designation;
            $dataEmployeeTransfer['salary'] = $request->transfer_salary;
            $dataEmployeeTransfer['branch_id'] = $request->transfer_branch;
            $dataEmployeeTransfer['category'] = $request->transfer_category;
            $dataEmployeeTransfer['recommendation_name'] = $request->transfer_rec_employee_name;
            $dataEmployeeTransfer['recom_designation'] = $request->transfer_rec_employee_designation;
            $dataEmployeeTransfer['file'] = $application_file_filename;
            $dataEmployeeTransfer['transfer_date'] = date("Y-m-d", strtotime(str_replace('/', '-', $request->transfer_date)));
            $dataEmployeeTransfer['apply_date'] = date("Y-m-d", strtotime(str_replace('/', '-', $request->create_application_date)));
            $dataEmployeeTransfer['status'] = 1;
            $dataEmployeeTransfer['created_at'] = date("Y-m-d", strtotime(str_replace('/', '-', $request->created_at)));
            $dataEmployeeTransfer['updated_at'] = date("Y-m-d", strtotime(str_replace('/', '-', $request->transfer_date)));

            $createEmployeeTerminate = EmployeeTransfer::create($dataEmployeeTransfer);

            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('alert', $ex->getMessage());
        }
        return redirect('admin/hr/employee/transfer')->with('success', 'Employee Transfer Successfully');
    }
    /**
     * transfer  Employee List.
     * Route: admin/hr/transfer
     * Method: get 
     * @return  array()  Response
     */
    public function transferList()
    {
        if (check_my_permission(Auth::user()->id, "114") != "1") {
            return redirect()->route('admin.dashboard');
        }

        $data['title'] = 'Employee Management | Employee Transfer List';
        $data['designation'] = Designation::select('id', 'designation_name')->where('status', 1)->get();
        $data['branch'] = Branch::select('id', 'name', 'branch_code')->where('status', 1)->get();
        return view('templates.admin.hr_management.employee.transfer_list', $data);
    }

    /**
     * Get  Employee Transfer list
     * Route: ajax call from - admin/hr/employee/tranfer
     * Method: Post 
     * @param  \Illuminate\Http\Request  $request
     * @return JSON array
     */
    public function employeeTransferListing(Request $request)
    {

        if ($request->ajax()) {
            // fillter array 
            $arrFormData = array();
            if (!empty($_POST['searchform'])) {
                foreach ($_POST['searchform'] as $frm_data) {
                    $arrFormData[$frm_data['name']] = $frm_data['value'];
                }
            }
            if (isset($arrFormData['is_search']) && $arrFormData['is_search'] == 'yes') {

                $data = EmployeeTransfer::select(
                    'id',
                    'apply_date',
                    'old_category',
                    'old_recommendation_name',
                    'category',
                    'recommendation_name',
                    'created_at',
                    'file',
                    'designation_id',
                    'old_designation_id',
                    'employee_id',
                    'branch_id',
                    'old_branch_id'
                )
                    ->with([
                        'oldDesignation:id,designation_name',
                        'designation:id,designation_name',
                        'transferBranch:id,name,branch_code,sector,regan,zone',
                        'transferBranchOld:id,name,branch_code,sector,regan,zone',
                        'transferEmployee:id,employee_code,employee_name,company_id',
                        'transferEmployee.company:id,name'
                    ]);
                $totalCount = $data->count('id');


                if (!is_null(Auth::user()->branch_ids)) {
                    $branch_ids = Auth::user()->branch_ids;
                    $data = $data->whereIn('old_branch_id', explode(",", $branch_ids));
                }
                /******* fillter query start ****/
                if (isset($arrFormData['branch']) && $arrFormData['branch'] != '') {
                    $branch = $arrFormData['branch'];
                    $data = $data->where('old_branch_id', $branch);
                }
                if ($arrFormData['employee_code'] != '') {
                    $employee_code = $arrFormData['employee_code'];

                    $data = $data->whereHas('transferEmployee', function ($query) use ($employee_code) {
                        $query->where('employee_code', $employee_code);
                    });
                }
                if ($arrFormData['reco_employee_name'] != '') {
                    $reco_employee_name = $arrFormData['reco_employee_name'];
                    ////$data=$data->where('recommendation_employee_name',$reco_employee_name);
                    $data = $data->whereHas('transferEmployee', function ($query) use ($reco_employee_name) {
                        $query->where('recommendation_employee_name', 'LIKE', '%' . $reco_employee_name . '%');
                    });
                }
                if ($arrFormData['branch'] != '') {
                    $branch = $arrFormData['branch'];
                    $data = $data->where('branch_id', $branch);
                }
                if ($arrFormData['category'] != '') {
                    $categoryid = $arrFormData['category'];
                    $data = $data->whereHas('transferEmployee', function ($query) use ($categoryid) {
                        $query->where('category', $categoryid);
                    });
                }
                if ($arrFormData['designation'] != '') {
                    $designation = $arrFormData['designation'];
                    $data = $data->whereHas('transferEmployee', function ($query) use ($designation) {
                        $query->where('designation_id', $designation);
                    });
                }

                if ($arrFormData['start_date'] != '') {
                    $startDate = convertDate($arrFormData['start_date']);
                    $endDate = ($arrFormData['end_date'] != '') ? convertDate($arrFormData['end_date']) : '';
                    $data = $data->whereDate('created_at', '>=', $startDate);
                    if ($endDate) {
                        $data = $data->whereDate('created_at', '<=', $endDate);
                    }
                }

                /******* fillter query End ****/
                $count = $data->count('id');
                $data = $data->orderby('created_at', 'DESC')->offset($_POST['start'])->limit($_POST['length'])->get();
                $sno = $_POST['start'];
                $rowReturn = array();
                foreach ($data as $row) {
                    $file = "<a href='" . ImageUpload::generatePreSignedUrl('employee/transfer/' . $row->file) . "'   target='_blank'  >" . $row->file . " </a>";
                    $sno++;
                    $val['DT_RowIndex'] = $sno;
                    $val['company_name'] = $row->transferEmployee->company ? $row->transferEmployee->company->name : 'N/A';
                    $val['apply_date'] = date("d/m/Y", strtotime($row->apply_date));
                    $val['employee_code'] = $row->transferEmployee->employee_code;
                    $val['employee_name'] = $row->transferEmployee->employee_name;
                    $val['old_designation'] = $row->oldDesignation->designation_name;
                    $old_category = ($row->old_category == 1) ? 'On-rolled' : 'Contract';
                    $val['old_category'] = $old_category;
                    $val['old_branch'] = $row->transferBranchOld->name . '(' . $row->transferBranchOld->branch_code . ')';
                    $val['new_branch'] = $row->transferBranch->name . '(' . $row->transferBranch->branch_code . ')';
                    $val['rec_employee_name_old'] = $row->old_recommendation_name;
                    $val['transfer_date'] = date("d/m/Y", strtotime($row->apply_date));
                    $val['branch'] = $row->transferBranch->name;
                    $val['designation'] = $row->designation->designation_name;
                    $category = ($row->category == 1) ? 'On-rolled' : 'Contract';
                    $val['category'] = $category;
                    $val['rec_employee_name'] = $row->recommendation_name;
                    $val['file'] = $row->file ? $file : '';
                    $val['created_at'] = date("d/m/Y", strtotime($row->created_at));

                    $btn = '<a class="" href="' . URL::to("admin/hr/employee/transfer/detail/" . $row->id) . '" title="Employee Transfer Detail"><i class="fas fa-eye text-default mr-2 "></i></a>';
                    $val['action'] = $btn;
                    $rowReturn[] = $val;

                }
                $output = array("branch_id" => Auth::user()->branch_id, "draw" => $_POST['draw'], "recordsTotal" => $totalCount, "recordsFiltered" => $count, "data" => $rowReturn);
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

    /**
     * transfer  Employee Detail.
     * Route: admin/hr/transfer/detail
     * Method: get 
     * @return  array()  Response
     */
    public function transferDetail($id)
    {
        $data['title'] = 'Employee Management | Employee Transfer Detail';
        $data['employee'] = EmployeeTransfer::with([
            'transferBranch:id,name',
            'transferBranchOld:id,name',
            'transferEmployee:id,branch_id,designation_id,company_id,salary,employee_code,category,employee_name,status',
            'transferEmployee.branch:id,name,branch_code',
            'transferEmployee.company:id,name',
            'transferEmployee.designation:id,designation_name'

        ])->find($id);

        // pd($data);
        return view('templates.admin.hr_management.employee.transfer_detail', $data);
    }
    /**
     * designation data  get by category.
     * Route: /hr 
     * Method: get 
     * @return  view
     */
    public function designationByCategory(Request $request)
    {
        //echo $request->start_date;die;
        $data = Designation::where('category', $request->category)->where('status', '1')->get();
        //print_r($data);die; 
        $return_array = compact('data');

        return json_encode($return_array);
    }
    /**
     * Transfer count by employee code.
     * Route: /hr 
     * Method: get 
     * @return  view
     */
    public function trasnsferCount(Request $request)
    {
        //echo $request->start_date;die;
        $data = Employee::where('employee_code', $request->code)->first();
        //print_r($data);die; 
        if ($data) {
            $employee_id = $data->id;
            $count1 = EmployeeTransfer::where('employee_id', $employee_id)->get();
            $count = count($count1);
            $msg = 1;
        } else {
            $msg = 0;
            $count = 0;
        }
        $return_array = compact('count', 'msg');

        return json_encode($return_array);
    }
    /**
     * application   Employee.
     * Route: admin/hr/employee/application edit
     * Method: get 
     * @return  array()  Response
     */
    public function application_edit($id)
    {
        $data['title'] = 'Employee Management | Employee Application Edit';
        $data['branch'] = \App\Models\Branch::where('status', 1)->get();
        if (isset($_GET['type']) && $_GET['type'] == 1) {
            $data['application'] = EmployeeApplication::where('id', $id)->first();
            $empID = $data['application']->employee_id;
            $data['employee'] = Employee::with('company:id,name')->where('id', $empID)->first();
            $customerID = $data['employee']->customer_id;             
            $data['member'] = Member::where('id',$customerID)->select('id','member_id')->first();
            $data['app_id'] = $id;
            $data['qualification'] = EmployeeQualification::where('employee_id', $empID)->get();
            $data['diploma'] = EmployeeDiploma::where('employee_id', $empID)->get();
            $data['work'] = EmployeeExperience::where('employee_id', $empID)->get();
            return view('templates.admin.hr_management.employee.edit', $data);
        }

    }
    /**
     * Delete Employee qualification.
     * Route: /hr 
     * Method: Post 
     * @return  view
     */
    public function delete_qualification(Request $request)
    {
        //echo $request->start_date;die;
        $deleteEmployeeQualification = EmployeeQualification::where('id', $request->id)->delete();

        if ($deleteEmployeeQualification) {
            $msg = 1;
        } else {
            $msg = 0;
        }
        $return_array = compact('msg');

        return json_encode($return_array);
    }
    /**
     * Delete Employee diploma.
     * Route: /hr 
     * Method: Post 
     * @return  view
     */
    public function delete_diploma(Request $request)
    {
        //echo $request->start_date;die;
        $deleteEmployeeDiploma = EmployeeDiploma::where('id', $request->id)->delete();

        if ($deleteEmployeeDiploma) {
            $msg = 1;
        } else {
            $msg = 0;
        }
        $return_array = compact('msg');

        return json_encode($return_array);
    }
    /**
     * Delete Employee Experience.
     * Route: /hr 
     * Method: Post 
     * @return  view
     */
    public function delete_experience(Request $request)
    {
        //echo $request->start_date;die;
        $deleteEmployeeDiploma = EmployeeExperience::where('id', $request->id)->delete();

        if ($deleteEmployeeDiploma) {
            $msg = 1;
        } else {
            $msg = 0;
        }
        $return_array = compact('msg');

        return json_encode($return_array);
    }
    /**
     * application   Employee.
     * Route: admin/hr/employee/application edit
     * Method: get 
     * @return  array()  Response
     */
    public function application_print($id)
    {
        $data['title'] = 'Employee Management | Employee Application Print';
        $data['branch'] = \App\Models\Branch::where('status', 1)->get();
        $data['application'] = EmployeeApplication::where('id', $id)->first();
        $empID = $data['application']->employee_id;
        if (isset($_GET['type'])) {
            $empID = $id;
        }
        $data['employee'] = Employee::where('id', $empID)->first();
        $customerID = $data['employee']->customer_id??0;
        $data['member'] = Member::where('id',$customerID)->select('id','member_id')->first();
        $data['qualification'] = EmployeeQualification::where('employee_id', $empID)->get();
        $data['diploma'] = EmployeeDiploma::where('employee_id', $empID)->get();
        $data['work'] = EmployeeExperience::where('employee_id', $empID)->get();
        return view('templates.admin.hr_management.employee.print_application', $data);
    }
    /**
     * Approve Application  .
     * Route: admin/hr/employee/edit
     * Method: get 
     * @return  array()  Response
     */
    public function application_approve($id, $type)
    {
        $data['title'] = 'Employee Management | Application Approve';
        $data['branch'] = \App\Models\Branch::where('status', 1)->get();
        $data['application'] = EmployeeApplication::where('id', $id)->first();
        $empID = $data['application']->employee_id;
        $data['employee'] = Employee::where('id', $empID)->first();
        return view('templates.admin.hr_management.employee.approve', $data);
    }
    /**
     * Application approved .
     * Route: admin/hr/employee/application
     * Method: get 
     * @return  array()  Response
     */
    public function employee_approve(Request $request)
    {

        //  print_r($_POST);die;
        DB::beginTransaction();
        try {
            $globaldate = $request->created_at;
            $select_date = $request->select_date;
            $current_date = date("Y-m-d", strtotime(convertDate($globaldate)));
            $entry_date = date("Y-m-d", strtotime(convertDate($select_date)));
            $entry_time = date("H:i:s", strtotime(convertDate($globaldate)));
            $date_create = $entry_date . ' ' . $entry_time;
            $created_at = date("Y-m-d H:i:s", strtotime(convertDate($date_create)));
            $updated_at = date("Y-m-d H:i:s", strtotime(convertDate($date_create)));

            $employeeId = $request->employee_id;
            $getEmployeeData = Employee::where('id', $employeeId)->first();
            $getMiCode = getEmployeeMiCode($getEmployeeData->category);
            // print_r(count($getMiCode));
            if (!empty($getMiCode)) {
                if ($getMiCode->mi_code > 0) {
                    $miCode = $getMiCode->mi_code + 1;
                } else {
                    $miCode = 110;
                }

            } else {
                $miCode = 110;
            }
            $miCodeEmp = str_pad($miCode, 4, '0', STR_PAD_LEFT);
            if ($getEmployeeData->category == 1) {
                $employee_code = $miCodeEmp;
            } else {
                $employee_code = $miCodeEmp . 'c';
            }
            $employee['employee_code'] = $employee_code;
            $employee['designation_id'] = $request->designation;
            $employee['salary'] = $request->salary;
            $employee['mi_code'] = $miCode;
            $employee['mi_category'] = $request->category;
            $employee['category'] = $request->category;
            $employee['designation_id'] = $request->designation;
            $employee['salary'] = $request->salary;
            $employee['is_employee'] = 1;
            $employee['employee_date'] = date("Y-m-d", strtotime(str_replace('/', '-', $created_at)));
            $employee['status'] = 1;
            $employee['updated_at'] = $request->created_at;
            $employeeUpdate = Employee::find($employeeId);
            $employeeUpdate->update($employee);
            $empApp['status'] = 1;
            $empApp['status_date'] = date("Y-m-d", strtotime(str_replace('/', '-', $created_at)));
            $empApp['updated_at'] = $request->created_at;
            $empAppUpdate = EmployeeApplication::find($request->id);
            $empAppUpdate->update($empApp);
            $empMem = [
                'is_employee' => 1,
                'employee_id' => $getEmployeeData->id,
                'employee_code' => $employee_code,
            ];
            $empMemUpdate = Member::find($getEmployeeData->customer_id);
            if ($empMemUpdate) {
                $empMemUpdate->update($empMem);            
            }
            DB::commit();

        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('alert', $ex->getMessage());
        }
        return redirect('admin/hr/employee')->with('success', 'Employee Application Approved Successfully');
    }

    public function checkSsbAccount(Request $request)
    {
        $ssbAccount = $request->ssb_account;
        $companyId = $request->company_id;
        $resCount = 0;
        $account_no = '';
        $name = '';
        $ssbDate = '';
        if ($ssbAccount) {
            $account_no = SavingAccount::where('account_no', $ssbAccount)
            ->when($companyId, function ($query) use ($companyId) {
                $query->where('company_id', $companyId);
            })
            ->first(['member_id', 'account_no', 'id', 'created_at', 'company_id', 'customer_id']);
            if ($account_no) {
                $ssbDate = date("d/m/Y", strtotime(convertDate($account_no->created_at)));
                $member = Member::where('id', $account_no->customer_id)->first(['first_name', 'last_name', 'company_id']);
                if ($member) {
                    $first_name = $member->first_name;
                    $last_name = $member->last_name;
                    $company_id = $member->company_id;
                } else {
                    $first_name = '';
                    $last_name = '';
                    $company_id = '';
                }
                $name = $member->first_name . ' ' . $member->last_name;
                $companyId = $member->company_id;
                $resCount = 1;
            }
        }
        $return_array = compact('account_no', 'resCount', 'name', 'ssbDate', 'companyId');
        return json_encode($return_array);
    }
    public function ledgerEmploy($id)
    {
        $data['title'] = 'Employee Management | Employee List';
        $data['emp'] = $id;
        $data['detail'] = \App\Models\Employee::where('id', $id)->first();

        return view('templates.admin.hr_management.employee.emp_ledger', $data);
    }
    public function ledgerEmployListing(Request $request)
    {
        if ($request->ajax()) {

            $lib = $request->liability;

            $data = \App\Models\EmployeeLedger::where('employee_id', $lib)->where('is_deleted',0)->orderby('id', 'DESC')->get();

            return Datatables::of($data)
                ->addIndexColumn()
                ->rawColumns(['company_name'])
                ->addColumn('company_name', function ($row) {
                    $company_name = $row['company']->name;
                    return $company_name;
                })
                ->rawColumns(['company_name'])

                ->addColumn('date', function ($row) {
                    $date = date("d/m/Y", strtotime($row->created_at));
                    return $date;
                })
                ->rawColumns(['date'])
                ->addColumn('description', function ($row) {
                    $description = $row->description;
                    return $description;
                })
                ->rawColumns(['description'])
                ->addColumn('reference_no', function ($row) {
                    $reference_no = 'N/A';
                    if ($row->payment_mode == 0) {
                        $reference_no = 'N/A';
                    }
                    if ($row->payment_mode == 1) {
                        $reference_no = $row->cheque_no;
                    }
                    if ($row->payment_mode == 2) {
                        $reference_no = $row->transaction_no;
                    }
                    if ($row->payment_mode == 3) {
                        $reference_no = $row->v_no;
                    }
                    if ($row->payment_mode == 4) {
                        $reference_no = $row->jv_unique_id;
                    }


                    return $reference_no;
                })
                ->rawColumns(['reference_no'])
                ->addColumn('payment_mode', function ($row) {
                    $payment_mode = 'N/A';
                    if ($row->payment_mode == 0) {
                        $payment_mode = 'Cash';
                    }
                    if ($row->payment_mode == 1) {
                        $payment_mode = 'Cheque';
                    }
                    if ($row->payment_mode == 2) {
                        $payment_mode = 'Online';
                    }
                    if ($row->payment_mode == 3) {
                        $payment_mode = 'SSB ';
                    }
                    if ($row->payment_mode == 4) {
                        $payment_mode = 'Jv';
                    }



                    return $payment_mode;
                })
                ->rawColumns(['payment_mode'])
                ->addColumn('amount', function ($row) {
                    if ($row->payment_type == "CR") {
                        $amount = $row->deposit;
                    } elseif ($row->payment_type == "DR") {
                        $amount = $row->withdrawal;
                    }
                    return number_format((float) $amount, 2, '.', '');
                })
                ->rawColumns(['amount'])
                ->addColumn('payment_type', function ($row) {
                    if ($row->payment_type == "CR") {
                        $type = 'Credit';
                    } elseif ($row->payment_type == "DR") {
                        $type = 'Debit';
                    }
                    return $type;
                })
                ->rawColumns(['payment_type'])

                ->make(true);
        }
    }
    public function companydate(Request $request)
    {
        $data['companyDate'] = \App\Models\Companies::where('id', $request['company_id'])->first('created_at')->created_at;
        $data['companyDate'] = date('d/m/Y', strtotime(convertDate($data['companyDate'])));
        return json_encode($data['companyDate']);
    }
    public function all_img()
    {
        $data['title'] = 'All Images';
        $data['data'] = Employee::where('is_employee', 1)->select('employee_name', 'mobile_no', 'photo', 'status')->get();
        return view('templates.admin.hr_management.employee.all_emp', $data);
    }
}