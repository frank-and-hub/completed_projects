<?php

namespace App\Http\Controllers\Branch\HrManagement;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Validator;
use Illuminate\Support\Facades\Auth;
use App\Models\EmployeeDiploma;
use App\Models\EmployeeExperience;
use App\Models\EmployeeQualification;
use App\Models\EmployeeTerminate;
use App\Models\EmployeeTransfer;
use App\Models\EmployeeApplication;
use App\Services\ImageUpload;
use App\Models\Employee;
use App\Models\Branch;
use App\Models\Member;
use App\Models\SavingAccount;
use App\Models\Designation;
use Carbon\Carbon;
use DB;
use URL;
use Image;
use Yajra\DataTables\DataTables;

/*
    |---------------------------------------------------------------------------
    | Branch Panel -- Employee Management EmployeeController
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
     * Route: branch/hr/employee
     * Method: get 
     * @return  array()  Response
     */
    public function index()
    {

        if (!in_array('Employee List', auth()->user()->getPermissionNames()->toArray())) {
            return redirect()->route('branch.dashboard');
        }

        $data['title'] = 'Employee Management | Employee List';
        $data['designation'] = Designation::where('status', 1)->get();
        $data['branch'] = Branch::where('status', 1)->get();
        return view('templates.branch.hr_management.employee.index', $data);
    }
    /**
     * Get  Employee  list
     * Route: ajax call from - branch/hr/employee
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
            //print_r($arrFormData);die;
            $getBranchId = getUserBranchId(Auth::user()->id);
            $branch_id = $getBranchId->id;

            $data = \App\Models\Employee::has('company')
                ->with([
                        'branch:id,name,branch_code,sector,regan,zone',
                        'empApp:id,employee_id,application_type,status',
                        'company:id,name'
                    ])
                ->where('is_employee', 1)
                ->where('branch_id', $branch_id);
            /******* fillter query start ****/
            if (isset($arrFormData['is_search']) && $arrFormData['is_search'] == 'yes') {
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
                /*if($arrFormData['branch'] !=''){
                    $branch=$arrFormData['branch'];
                    $data=$data->where('branch_id',$branch);
                }*/
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

            }

            /******* fillter query End ****/
            $data = $data->orderby('created_at', 'DESC')->get();
            //print_r($data);die;
            return Datatables::of($data)
                ->addIndexColumn()
                ->rawColumns(['company_name'])
                ->addColumn('company_name', function ($row) {
                    $company_name = $row['company']->name ?? 'N/A';
                    return $company_name;
                })
                ->rawColumns(['company'])



                ->addColumn('designation', function ($row) {

                    if (isset($row->designation_id)) {
                        $designation = getDesignationData('designation_name', $row->designation_id)->designation_name;
                    } else {
                        $designation = "N/A";
                    }


                    return $designation;
                })
                ->rawColumns(['designation'])
                ->addColumn('category', function ($row) {
                    $category = '';
                    if ($row->category == 1) {
                        $category = 'On-rolled';
                    }
                    if ($row->category == 2) {
                        $category = 'Contract';
                    }
                    return $category;
                })
                ->rawColumns(['category'])
                ->addColumn('branch', function ($row) {
                    $branch = $row['branch']->name . '(' . $branch_code = $row['branch']->branch_code . ')';

                    return $branch;
                })
                ->rawColumns(['branch'])
                // ->addColumn('branch_code', function($row){
                //     $branch_code = $row['branch']->branch_code;
                //     return $branch_code;
                // })
                // ->rawColumns(['branch_code'])
                // ->addColumn('sector', function($row){
                //     $sector = $row['branch']->sector;
                //     return $sector;
                // })
                // ->rawColumns(['sector'])
                // ->addColumn('regan', function($row){
                //     $regan = $row['branch']->regan;
                //     return $regan;
                // })
                // ->rawColumns(['regan'])
                // ->addColumn('zone', function($row){
                //     $zone = $row['branch']->zone;
                //     return $zone;
                // })
                // ->rawColumns(['zone'])
                ->addColumn('rec_employee_name', function ($row) {
                    $rec_employee_name = $row->recommendation_employee_name;
                    return $rec_employee_name;
                })
                ->rawColumns(['rec_employee_name'])
                ->addColumn('employee_name', function ($row) {
                    $employee_name = $row->employee_name;
                    return $employee_name;
                })
                ->rawColumns(['employee_name'])
                ->addColumn('employee_code', function ($row) {
                    $employee_code = $row->employee_code;
                    return $employee_code;
                })
                ->rawColumns(['employee_code'])
                ->addColumn('dob', function ($row) {
                    $dob = date("d/m/Y", strtotime($row->dob));
                    return $dob;
                })
                ->rawColumns(['dob'])
                ->addColumn('gender', function ($row) {
                    $gender = 'Other';
                    if ($row->gender == 1) {
                        $gender = 'Male';
                    }
                    if ($row->gender == 2) {
                        $gender = 'Female';
                    }
                    return $gender;
                })
                ->rawColumns(['gender'])
                ->addColumn('mobile_no', function ($row) {
                    $mobile_no = $row->mobile_no;
                    return $mobile_no;
                })
                ->rawColumns(['mobile_no'])
                ->addColumn('email', function ($row) {
                    $email = $row->email;
                    return $email;
                })
                ->rawColumns(['email'])
                ->addColumn('guardian_name', function ($row) {
                    $guardian_name = $row->father_guardian_name;
                    return $guardian_name;
                })
                ->rawColumns(['guardian_name'])
                ->addColumn('guardian_number', function ($row) {
                    $guardian_number = $row->father_guardian_number;
                    return $guardian_number;
                })
                ->rawColumns(['guardian_number'])
                ->addColumn('mother_name', function ($row) {
                    $mother_name = $row->mother_name;
                    return $mother_name;
                })
                ->rawColumns(['mother_name'])
                ->addColumn('pen_card', function ($row) {
                    $pen_card = $row->pen_card;
                    return $pen_card;
                })
                ->rawColumns(['pen_card'])
                ->addColumn('aadhar_card', function ($row) {
                    $aadhar_card = $row->aadhar_card;
                    return $aadhar_card;
                })
                ->rawColumns(['aadhar_card'])
                ->addColumn('voter_id', function ($row) {
                    $voter_id = $row->voter_id;
                    return $voter_id;
                })
                ->rawColumns(['voter_id'])
                ->addColumn('esi', function ($row) {
                    $esi = $row->esi_account_no;
                    return $esi;
                })
                ->rawColumns(['esi'])
                ->addColumn('pf', function ($row) {
                    $pf = $row->pf_account_no;
                    return $pf;
                })
                ->rawColumns(['pf'])
                ->addColumn('status', function ($row) {
                    if ($row->is_employee == 0) {
                        $status = 'Pending';
                    } else {
                        $status = 'Inactive';

                        if ($row->status == 1) {
                            $status = 'Active';
                        }
                    }
                    return $status;
                })
                ->rawColumns(['status'])
                ->addColumn('resign', function ($row) {
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

                    return $resign;
                })
                ->rawColumns(['resign'])
                ->addColumn('terminate', function ($row) {
                    $terminate = 'No';

                    if ($row->is_terminate == 1) {
                        $terminate = 'Yes';
                    }
                    return $terminate;
                })
                ->rawColumns(['terminate'])
                ->addColumn('transfer', function ($row) {
                    $transfer = 'No';

                    if ($row->is_transfer == 1) {
                        $transfer = 'Yes';
                    }
                    return $transfer;
                })
                ->rawColumns(['transfer'])
                ->addColumn('created_at', function ($row) {
                    $created_at = date("d/m/Y", strtotime($row->created_at));
                    return $created_at;
                })
                ->rawColumns(['created_at'])
                ->addColumn('action', function ($row) {

                    $btn = '';
                    $url2 = URL::to("branch/hr/employee/edit/" . $row->id);
                    $url4 = URL::to("branch/hr/employee/detail/" . $row->id);
                    $url7 = URL::to("branch/hr/employee/transfer_letter/" . $row->id);

                    $url8 = URL::to("branch/hr/employee/resign_request?employee=" . $row->employee_code);

                    $url11 = URL::to("branch/hr/employee/application_print/" . $row->id);
                    $empLedger = URL::to("branch/hr/employ/ledger/" . $row->id . "");


                    if (in_array('Employee View', auth()->user()->getPermissionNames()->toArray())) {
                        $btn .= '<a class="" href="' . $url4 . '" title="Employee View"><i class="fas fa-eye text-default mr-2 "></i></a>';
                    }

                    if (in_array('Employee Transactions', auth()->user()->getPermissionNames()->toArray())) {
                        $btn .= '<a href="' . $empLedger . '" title="transactions"><i class="ni ni-chart-bar-32 text-default mr-2" aria-hidden="true"></i></a>';
                    }


                    //$btn .= '<a class="" href="'.$url2.'" title="Employee Edit"><i class="fas fa-edit text-default mr-2"></i></a>';
                    if (in_array('Employee Download Application', auth()->user()->getPermissionNames()->toArray()) || in_array('Employee Print Application', auth()->user()->getPermissionNames()->toArray())) {
                        $btn .= '<a class="" href="' . $url11 . '" title="Download PDF & Print"><i class="fas fa-print text-default mr-2 "></i></a>';
                    }


                    if (in_array('Resign Request', auth()->user()->getPermissionNames()->toArray())) {

                        if ($row->is_resigned == 0 && $row->is_terminate != 1) {
                            $btn .= '<a class="" href="' . $url8 . '" title="Resign Request"><i class="fas fa-plus-square text-default mr-2 "></i></a>';
                        }

                    }


                    if ($row->is_transfer == 1) {
                        $btn .= '<a class="" href="' . $url7 . '" title="Transfer Letter"><i class="fas fa-envelope text-default mr-2"></i></a>';
                    }
                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
    }


    /**
     * Employee Application List.
     * Route: branch/hr/employee/application
     * Method: get 
     * @return  array()  Response
     */
    public function applicationList()
    {

        if (!in_array('Employee Application', auth()->user()->getPermissionNames()->toArray())) {
            return redirect()->route('branch.dashboard');
        }

        $data['title'] = 'Employee Management | Employee Application List';
        $data['designation'] = Designation::where('status', 1)->get();
        $data['branch'] = Branch::where('status', 1)->get();
        return view('templates.branch.hr_management.employee.application_list', $data);
    }

    /**
     * Get  Employee Application list
     * Route: ajax call from - branch/hr/employee/Application
     * Method: Post 
     * @param  \Illuminate\Http\Request  $request
     * @return JSON array
     */
    public function employeeApplicationListing(Request $request)
    {
        if ($request->ajax()) {

            $arrFormData = array();
            if (!empty($_POST['searchform'])) {
                foreach ($_POST['searchform'] as $frm_data) {
                    $arrFormData[$frm_data['name']] = $frm_data['value'];
                }
            }
            if (isset($arrFormData['is_search']) && $arrFormData['is_search'] == 'yes') {
                $data = EmployeeApplication::has('company')->with(['branch' => function ($query) {
                    $query->select('id', 'name', 'branch_code', 'sector', 'regan', 'zone'); }])
                    ->with([
                        'company' => function ($q) {
                            $q->select(['id', 'name']);
                        }
                    ])
                    ->with(['employeeget' => function ($query) {
                        $query->select('id', 'category', 'recommendation_employee_name', 'employee_name', 'dob', 'gender', 'mobile_no', 'email', 'father_guardian_name', 'father_guardian_number', 'mother_name', 'pen_card', 'aadhar_card', 'voter_id', 'designation_id', 'esi_account_no', 'pf_account_no'); }])

                    ->where(function ($query) {
                        $query->where('application_type', 1)
                            ->WhereHas('employeeget', function ($query) {
                                $query->where('is_employee', 0);
                            });
                    });

                $getBranchId = getUserBranchId(Auth::user()->id);
                $branch_id = $getBranchId->id;

                $data = $data->where('branch_id', $branch_id);

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

                /* if($arrFormData['branch'] !=''){
                    $branch=$arrFormData['branch'];
                    $data=$data->where('branch_id',$branch);
                }*/
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

                $data = $data->orderby('created_at', 'DESC')->get();
                $count = count($data);
                $total = EmployeeApplication::with(['branch' => function ($query) {
                    $query->select('id', 'name', 'branch_code', 'sector', 'regan', 'zone'); }])
                    ->with([
                        'company' => function ($q) {
                            $q->select(['id', 'name']);
                        }
                    ])
                    ->with(['employeeget' => function ($query) {
                        $query->select('*'); }])->get();
                $totalCount = count($data);
                $sno = $_POST['start'];
                $rowReturn = array();

                foreach ($data as $row) {
                    $sno++;
                    $val['DT_RowIndex'] = $sno;
                    $val['company_name'] = 'N/A';
                    if ($row['company']) {
                        $val['company_name'] = $row['company']->name;
                    }
                    if (isset($row['employeeget']->designation_id)) {
                        $val['designation'] = getDesignationData('designation_name', $row['employeeget']->designation_id)->designation_name;
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
                    $val['branch'] = $row['branch']->name . '(' . $val['branch_code'] = $row['branch']->branch_code . ')';
                    // $val['branch_code']= $row['branch']->branch_code;
                    // $val['sector']= $row['branch']->sector;
                    // $val['regan']= $row['branch']->regan;
                    // $val['zone']= $row['branch']->zone;
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
                    if ($row->status == 2) {
                        $status = 'Rejected';
                    }

                    $val['status'] = $status;
                    $val['created_at'] = date("d/m/Y", strtotime($row->created_at));
                    $btn = '';
                    $url11 = URL::to("branch/hr/employee/application_print/" . $row->id . "?type=" . $row->application_type);
                    $url4 = URL::to("branch/hr/employee/detail/" . $row->id . "?type=" . $row->application_type);
                    $url5 = URL::to("branch/hr/employee/resign_letter/" . $row['employeeget']->id);

                    if (in_array('Application View', auth()->user()->getPermissionNames()->toArray())) {
                        $btn .= '<a class="" href="' . $url4 . '" title="Employee View"><i class="fas fa-eye text-default mr-2 "></i></a>';
                    }

                    if ($row->application_type == 1) {

                        if (in_array('Print Application', auth()->user()->getPermissionNames()->toArray()) || in_array('Download Application', auth()->user()->getPermissionNames()->toArray())) {
                            $btn .= '<a class="" href="' . $url11 . '" title="Download PDF & Print"><i class="fas fa-print text-default mr-2 "></i></a>';

                        }

                    }
                    $val['action'] = $btn;
                    $rowReturn[] = $val;

                }
                $output = array("draw" => $_POST['draw'], "recordsTotal" => $totalCount, "recordsFiltered" => $count, "data" => $rowReturn, );
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
     * Show Employee Detail.
     * Route: branch/hr/employee/detail
     * Method: get 
     * @return  array()  Response
     */
    public function detail($id)
    {
        $data['title'] = 'Employee Management | Employee Detail';
        if (isset($_GET['type'])) {
            if (!in_array('Application View', auth()->user()->getPermissionNames()->toArray())) {
                return redirect()->route('branch.dashboard');
            }
            $data['application'] = EmployeeApplication::where('id', $id)->where('application_type', $_GET['type'])->first();
            $empID = $data['application']->employee_id;
            $data['type'] = $_GET['type'];
            $data['title'] = 'Employee Management | Employee Application Detail';
            $data['employee'] = Employee::where('id', $empID)->first();
            $customerID = $data['employee']->customer_id;            
            $data['member'] = Member::where('id',$customerID)->select('id','member_id')->first();
            $data['app'] = EmployeeApplication::where('employee_id', $empID)->where('application_type', 2)->orderby('id', 'DESC')->get();
        } else {
            if (!in_array('Employee View', auth()->user()->getPermissionNames()->toArray())) {
                return redirect()->route('branch.dashboard');
            }
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

        return view('templates.branch.hr_management.employee.detail', $data);
    }
    /**
     * Add  Employee.
     * Route: branch/hr/employee/add
     * Method: get 
     * @return  array()  Response
     */
    public function add()
    {
        die();
        if (!in_array('Register Employee', auth()->user()->getPermissionNames()->toArray())) {
            return redirect()->route('branch.dashboard');
        }

        $data['title'] = 'Employee Management | Employee Registration';
        $data['branch'] = \App\Models\Branch::where('status', 1)->get();
        $data['designation'] = \App\Models\Designation::where('status', 1)->get();
        return view('templates.branch.hr_management.employee.register', $data);
    }
    /**
     * save  Employee.
     * Route: branch/hr/employee/add
     * Method: get 
     * @return  array()  Response
     */
    public function employeeSave(Request $request)
    {
        // $rules = [
        //     'category' => ['required'],
        //     'branch' => ['required'],
        //     'designation' => ['required'],
        //     'salary' => ['required','regex:/^\d*(\.\d{1,4})?$/'],
        //     'recommendation_employee_name' => ['required'],
        //     'applicant_name' => ['required'],
        //     'dob' => ['required'],
        //     'gender' => ['required'],            
        //     'esi_account_no' => ['unique:employees,esi_account_no'],
        //     'pf_account_no' => ['unique:employees,pf_account_no'],
        // ];
        $rules = [
            'category' => ['required'],
            'branch' => ['required'],
            'designation' => ['required'],
            'salary' => ['required', 'regex:/^\d*(\.\d{1,4})?$/'],
            'recommendation_employee_name' => ['required'],
            'applicant_name' => ['required'],
            'dob' => ['required'],
            'gender' => ['required'],
            'company_id' => ['required']
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
        //print_r($_POST);die;
        DB::beginTransaction();
        try {
            $stateid = getBranchState(Auth::user()->username);
            $globaldate = checkMonthAvailability(date('d'), date('m'), date('Y'), $stateid);
            $data['company_id'] = $request->company_id;
            $data['designation_id'] = $request->designation;
            $data['salary'] = $request->salary;
            $data['branch_id'] = $request->branch;
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
            $data['esi_account_no'] = $request->esi_account_no;
            $data['pf_account_no'] = $request->pf_account_no;
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


            $data['ssb_id'] = $request->ssb_account_id;
            $data['ssb_id'] = $request->ssb_account_id;

            $data['status'] = 0;
            $data['created_at'] = $globaldate;
            $data['updated_at'] = $globaldate;

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
            $dataApp['branch_id'] = $request->branch;
            $dataApp['application_type'] = 1;
            $dataApp['application_date'] = date("Y-m-d", strtotime($globaldate));
            $dataApp['status'] = 0;
            $dataApp['created_at'] = $globaldate;
            $dataApp['updated_at'] = $globaldate;
            $dataApp['company_id'] = $request->company_id;

            $createApp = EmployeeApplication::create($dataApp);

            $dataExam['employee_id'] = $employeeId;
            $dataExam['exam_name'] = $_POST['examination'];
            $dataExam['exam_from'] = $_POST['examination_passed'];
            $dataExam['board_university'] = $_POST['university_name'];
            $dataExam['subject'] = $_POST['subject'];
            $dataExam['per_division'] = $_POST['division'];
            $dataExam['passing_year'] = $_POST['passing_year'];
            $dataExam['created_at'] = $globaldate;
            $dataExam['updated_at'] = $globaldate;

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
                    $dataExamMore['created_at'] = $globaldate;
                    $dataExamMore['updated_at'] = $globaldate;
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
                $dataDiploma['created_at'] = $globaldate;
                $dataDiploma['updated_at'] = $globaldate;
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
                    $dataDiplomaMore['created_at'] = $globaldate;
                    $dataDiplomaMore['updated_at'] = $globaldate;
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
                $dataWork['created_at'] = $globaldate;
                $dataWork['updated_at'] = $globaldate;
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
                    $dataWorkMore['created_at'] = $globaldate;
                    $dataWorkMore['updated_at'] = $globaldate;

                    $createWorkMore = EmployeeExperience::create($dataWorkMore);

                }
            }

            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('alert', $ex->getMessage());
        }
        return redirect('branch/hr/employee/application_print/' . $createApp->id . '?type=1')->with('success', 'Employee Application Created Successfully');

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
     *  Employee Resign request.
     * Route: branch/hr/employee/detail
     * Method: get 
     * @return  array()  Response
     */
    public function resignRequest()
    {
        if (!in_array('Resign Request', auth()->user()->getPermissionNames()->toArray())) {
            return redirect()->route('branch.dashboard');
        }
        $data['detail'] = '';
        if (isset($_GET['employee'])) {
            $employee = $_GET['employee'];
            $data['detail'] = Employee::with(['company' => function ($query) {
                $query->select('id', 'name'); }])->where('employee_code', $employee)->first();
            // $data['detail'] = Employee::join('companies', 'employees.company_id', '=', 'companies.id')
            // > ->where('employee_code', $employee)
            //     ->select('employees.*', 'companies.id', 'companies.name')
            //     -first();

        }

        $data['title'] = 'Employee Management | Employee Resign Request';
        return view('templates.branch.hr_management.employee.resign_request', $data);
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
        $getBranchId = getUserBranchId(Auth::user()->id);
        $branch_id = $getBranchId->id;
        $data = Employee::with(['branch' => function ($query) {
            $query->select('id', 'name'); }, 'company' => function ($query) {
                $query->select('id', 'name'); }, 'loan:emp_code'])->where('employee_code', $request->code)->first();
        //     print_r($data);die; 
        if (!empty($data)) {
            $data = $data;
            $msg = 1;
            $designation = getDesignationData('designation_name', $data->designation_id)->designation_name;
        } else {
            $data = $data;
            $msg = 0;
            $designation = '';
        }
        $return_array = compact('data', 'msg', 'designation', 'branch_id');
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
            'employee_code' => ['required'],
            'company_id' => ['required'],
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

        DB::beginTransaction();
        try {
            $stateid = getBranchState(Auth::user()->username);
            $globaldate = checkMonthAvailability(date('d'), date('m'), date('Y'), $stateid);
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
            $employeeResign['updated_at'] = $globaldate;
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
            $dataApp['application_date'] = date("Y-m-d", strtotime($globaldate));
            $dataApp['status'] = 0;
            $dataApp['resign_file'] = $application_file_filename;
            $dataApp['resign_remark'] = $request->remark;
            $dataApp['created_at'] = $globaldate;
            $dataApp['updated_at'] = $globaldate;
            $createApp = EmployeeApplication::create($dataApp);


            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('alert', $ex->getMessage());
        }
        return redirect('branch/hr/employee/application_print/' . $createApp->id . '?type=2')->with('success', 'Resignation Submitted Successfully');
    }

    /**
     * Show Employee transfer letter.
     * Route: branch/hr/employee
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

        return view('templates.branch.hr_management.employee.transfer_letter', $data);
    }

    /**
     * transfer  Employee List.
     * Route: branch/hr/transfer
     * Method: get 
     * @return  array()  Response
     */
    public function transferList()
    {
        if (!in_array('Employee Transfer List', auth()->user()->getPermissionNames()->toArray())) {
            return redirect()->route('branch.dashboard');
        }

        $data['title'] = 'Employee Management | Employee Transfer List';
        $data['designation'] = Designation::where('status', 1)->get();
        $data['branch'] = Branch::where('status', 1)->get();
        return view('templates.branch.hr_management.employee.transfer_list', $data);
    }

    /**
     * Get  Employee Transfer list
     * Route: ajax call from - branch/hr/employee/tranfer
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
            //print_r($arrFormData);die;
            if (isset($arrFormData['is_search']) && $arrFormData['is_search'] == 'yes') {

                /** this code is last modify by Sourab on 05-10-2023 for adding a new column for showing 
                 * new barnch on listing and export.
                 */
                $data = EmployeeTransfer::has('company')->with([
                    'transferBranch:id,name,branch_code,sector,regan,zone',
                    'transferBranchOld:id,name,branch_code,sector,regan,zone',
                    'transferEmployee'
                ]);

                $getBranchId = getUserBranchId(Auth::user()->id);
                $branch_id = $getBranchId->id;

                $data = $data->where('old_branch_id', $branch_id);

                /******* fillter query start ****/

                if ($arrFormData['employee_code'] != '') {
                    $employee_code = $arrFormData['employee_code'];

                    $data = $data->whereHas('transferEmployee', function ($query) use ($employee_code) {
                        $query->where('employee_code', $employee_code);
                    });
                }

                if ($arrFormData['reco_employee_name'] != '') {
                    $reco_employee_name = $arrFormData['reco_employee_name'];
                    $employee_name = NULL;
                    ////$data=$data->where('recommendation_employee_name',$reco_employee_name);
                    $data = $data->whereHas('transferEmployee', function ($query) use ($employee_name, $reco_employee_name) {
                        $query->where('recommendation_employee_name', 'LIKE', '%' . $reco_employee_name . '%');
                    });
                }
                /* if($arrFormData['branch'] !=''){
                        $branch=$arrFormData['branch'];
                        $data=$data->where('branch_id',$branch);
                    }*/
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
                    $startDate = date("Y-m-d", strtotime(convertDate($arrFormData['start_date'])));

                    if ($arrFormData['end_date'] != '') {
                        $endDate = date("Y-m-d ", strtotime(convertDate($arrFormData['end_date'])));
                    } else {
                        $endDate = '';
                    }
                    $data = $data->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate]);
                }



                /******* fillter query End ****/
                $data = $data->orderby('created_at', 'DESC')->get();
                $count = count($data);
                $total = EmployeeTransfer::with([
                    'transferBranch:id,name,branch_code,sector,regan,zone',
                    'transferBranchOld:id,name,branch_code,sector,regan,zone',
                    'transferEmployee:employee_code,employee_name',
                    'transferEmployee.company:id,name'
                ])->get();

                $totalCount = count($total);
                $sno = $_POST['start'];
                $rowReturn = array();

                foreach ($data as $row) {
                    $file = "<a href='" . ImageUpload::generatePreSignedUrl('employee/transfer/' . $row->file) . "'   target='_blank'  >" . $row->file . " </a>";
                    $sno++;
                    $val['DT_RowIndex'] = $sno;
                    $val['company_name'] = $row['transferEmployee']['company']->name ?? "N/A";
                    $val['branch'] = $row['transferBranchOld']->name . '(' . $row['transferBranchOld']->branch_code . ')';
                    $val['new_branch'] = $row['transferBranch']->name . '(' . $row['transferBranch']->branch_code . ')';
                    $val['apply_date'] = date("d/m/Y", strtotime($row->apply_date));
                    $val['employee_code'] = $row['transferEmployee']->employee_code;
                    $val['employee_name'] = $row['transferEmployee']->employee_name;
                    $val['old_designation'] = getDesignationData('designation_name', $row->old_designation_id)->designation_name;
                    $old_category = '';

                    if ($row->old_category == 1) {
                        $old_category = 'On-rolled';
                    }
                    if ($row->old_category == 2) {
                        $old_category = 'Contract';
                    }

                    $val['old_category'] = $old_category;
                    $val['old_branch'] = $row['transferBranchOld']->name . '(' . $row['transferBranchOld']->branch_code . ')';
                    $val['old_branch_code'] = $row['transferBranchOld']->branch_code;
                    $val['old_sector'] = $row['transferBranchOld']->sector;

                    $val['old_regan'] = $row['transferBranchOld']->regan;
                    $val['old_zone'] = $row['transferBranchOld']->zone;
                    $val['rec_employee_name_old'] = $row->old_recommendation_name;
                    $val['transfer_date'] = date("d/m/Y", strtotime($row->transfer_date));

                    $val['branch_code'] = $row['transferBranch']->branch_code;
                    $val['sector'] = $row['transferBranch']->sector;
                    $val['regan'] = $row['transferBranch']->regan;
                    $val['zone'] = $row['transferBranch']->zone;
                    $val['designation'] = getDesignationData('designation_name', $row->designation_id)->designation_name;

                    $category = '';
                    if ($row->category == 1) {
                        $category = 'On-rolled';
                    }
                    if ($row->category == 2) {
                        $category = 'Contract';
                    }

                    $val['category'] = $category;
                    $val['rec_employee_name'] = $row->recommendation_name;
                    $val['file'] = $row->file ? $file : '';
                    $val['created_at'] = date("d/m/Y", strtotime($row->created_at));

                    $btn = '';
                    $url = URL::to("branch/hr/employee/transfer/detail/" . $row->id . "");
                    $btn .= '<a class="" href="' . $url . '" title="Employee Transfer Detail"><i class="fas fa-eye text-default mr-2 "></i></a>';
                    $val['action'] = $btn;
                    $rowReturn[] = $val;

                }
                $output = array("draw" => $_POST['draw'], "recordsTotal" => $totalCount, "recordsFiltered" => $count, "data" => $rowReturn);
                return json_encode($output);
            } else {
                $output = array("draw" => 0, "recordsTotal" => 0, "recordsFiltered" => 0, "data" => 0);
                return json_encode($output);
            }
        }
    }



    /**
     * transfer  Employee Detail.
     * Route: branch/hr/transfer/detail
     * Method: get 
     * @return  array()  Response
     */
    public function transferDetail($id)
    {

        $data['title'] = 'Employee Management | Employee Transfer Detail';
        // $data['employee']=EmployeeTransfer::with(['transferBranch' => function($query){ $query->select('id','name');}])->with(['transferBranchOld' => function($query){ $query->select('id','name');}])->with(['transferEmployee' => function($query){ $query->select('*');}])->where('id',$id)->first(); 

        $data['employee'] = EmployeeTransfer::with([
            'transferBranch:id,name',
            'transferBranchOld:id,name',
            'transferEmployee:id,branch_id,designation_id,company_id,salary,employee_code,category,employee_name,status',
            'transferEmployee.branch:id,name,branch_code',
            'transferEmployee.company:id,name',
            'transferEmployee.designation:id,designation_name'
        ])->find($id);
        return view('templates.branch.hr_management.employee.transfer_detail', $data);
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
    public function application_print($id)
    {


        $data['type'] = '';

        if (isset($_GET['type'])) {

            $data['type'] = $_GET['type'];
            if (!in_array('Print Application', auth()->user()->getPermissionNames()->toArray()) && !in_array('Download Application', auth()->user()->getPermissionNames()->toArray())) {
                return redirect()->route('branch.dashboard');
            }
        } else {

            if (!in_array('Employee Download Application', auth()->user()->getPermissionNames()->toArray()) && !in_array('Employee Print Application', auth()->user()->getPermissionNames()->toArray())) {
                return redirect()->route('branch.dashboard');
            }
        }





        $data['title'] = 'Employee Management | Employee Application Print';
        $data['branch'] = \App\Models\Branch::where('status', 1)->get();

        $data['application'] = EmployeeApplication::where('id', $id)->first();
        if (isset($_GET['type'])) {
            $empID = $data['application']->employee_id;
        } else {
            $empID = $id;
        }
        $data['employee'] = Employee::with('branch:id,name,branch_code')->where('id', $empID)->first();
        $customerID = $data['employee']->customer_id??null;
        $data['member'] = Member::where('id',$customerID)->select('id','member_id')->first();
        $data['qualification'] = EmployeeQualification::where('employee_id', $empID)->get();
        $data['diploma'] = EmployeeDiploma::where('employee_id', $empID)->get();
        $data['work'] = EmployeeExperience::where('employee_id', $empID)->get();

        //  print_r($data['employee']);die;
        return view('templates.branch.hr_management.employee.print_application', $data);


    }

    public function checkSsbAccount(Request $request)
    {
        $ssbAccount = $request->ssb_account;
        $resCount = 0;
        $account_no = '';
        $name = '';

        if ($ssbAccount) {
            $account_no = SavingAccount::where('account_no', $ssbAccount)->first(['member_id', 'account_no', 'id', 'company_id', 'customer_id']);
            if ($account_no) {
                $member = Member::where('id', $account_no->customer_id)->first(['first_name', 'last_name']);
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
                $resCount = 1;
            }
        }
        $return_array = compact('account_no', 'resCount', 'name');
        return json_encode($return_array);
    }

    public function ledgerEmploy($id)
    {



        if (!in_array('Employee Transactions', auth()->user()->getPermissionNames()->toArray())) {
            return redirect()->route('branch.dashboard');
        }

        $data['title'] = 'Employee Management | Employee List';
        $data['emp'] = $id;
        $data['detail'] = \App\Models\Employee::where('id', $id)->first();
        return view('templates.branch.hr_management.employee.emp_ledger', $data);
    }


    public function ledgerEmployListing(Request $request)
    {
        if ($request->ajax()) {


            $lib = $request->liability;


            $data = \App\Models\EmployeeLedger::where('employee_id', $lib)->where('is_deleted',0)->orderby('opening_balance', 'DESC')->orderby('created_at', 'DESC')->get()
            ;


            return Datatables::of($data)
                ->addIndexColumn()
                ->rawColumns(['company_name'])
                ->addColumn('company_name', function ($row) {
                    $company_name = $row['company']->name;
                    return $company_name;
                })
                ->rawColumns(['company_name'])


                // ->addColumn('owner_name', function($row){
                //     $owner_name = $row['rentLibL']->owner_name;
                //     return $owner_name;
                // })
                // ->rawColumns(['owner_name'])
                // ->addColumn('owner_mobile_number', function($row){
                //     $owner_mobile_number = $row['rentLibL']->owner_mobile_number;
                //     return $owner_mobile_number;
                // })
                // ->rawColumns(['owner_mobile_number'])
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
                        $payment_mode = 'SSB through Jv';
                    }


                    return $payment_mode;
                })
                ->rawColumns(['payment_mode'])
                ->addColumn('withdrawal', function ($row) {
                    if ($row->withdrawal > 0) {
                        $withdrawal = number_format((float) $row->withdrawal, 2, '.', '');
                    } else {
                        $withdrawal = '';
                    }
                    return $withdrawal;
                })
                ->rawColumns(['withdrawal'])
                ->addColumn('deposit', function ($row) {
                    if ($row->deposit > 0) {
                        $deposit = number_format((float) $row->deposit, 2, '.', '');
                    } else {
                        $deposit = '';
                    }
                    return $deposit;
                })
                ->rawColumns(['deposit'])
                ->addColumn('opening_balance', function ($row) {

                    $opening_balance = number_format((float) $row->opening_balance, 2, '.', '');
                    return $opening_balance;
                })
                ->rawColumns(['opening_balance'])

                ->make(true);
        }
    }

}
