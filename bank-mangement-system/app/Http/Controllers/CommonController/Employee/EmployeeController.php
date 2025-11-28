<?php

namespace App\Http\Controllers\CommonController\Employee;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Validator;
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
use Carbon\Carbon;
use DB;
use URL;
use Image;
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
        // $this->middleware('auth');
    }

    public function add()
    {
        if (auth()->user()->role_id == 3) {
            if (!in_array('Register Employee', auth()->user()->getPermissionNames()->toArray())) {
                return redirect()->route('branch.dashboard');
            }
        } else {
            if (check_my_permission(Auth::user()->id, "109") != "1") {
                return redirect()->route('admin.dashboard');
            }
        }
        $data['title'] = 'Employee Management | Employee Registration';
        if (Auth::user()->branch_id > 0) {
            $data['branch'] = Branch::select('id', 'branch_code', 'name')->where('status', 1)->where('id', Auth::user()->branch_id)->get();
        } else {
            $data['branch'] = Branch::select('id', 'name', 'branch_code')->where('status', 1)->get();
        }
        $data['designation'] = Designation::select('id', 'designation_name')->where('status', 1)->get();
        return view('templates.CommonViews.Employee.index', $data);
    }
    /**
     * save  Employee.
     * Route: admin/hr/employee/add
     * Method: get 
     * @return  array()Response
     */
    public function employeeSave(Request $request)
    {
        $rules = [
            'category' => ['required'],
            'company_id' => ['required'],
            'branch_id' => ['required'],
            'designation' => ['required'],
            'recommendation_employee_name' => ['required'],
            'applicant_name' => ['required'],
            'dob' => ['required'],
            'gender' => ['required']
        ];
        // Add the 'salary' rule conditionally
        $rules['salary'] = (isset(auth()->user()->role_id) && auth()->user()->role_id == 3) ? ['regex:/^\d*(\.\d{1,4})?$/'] : ['required', 'regex:/^\d*(\.\d{1,4})?$/'];

        if ($request->esi_account_no != '') {
            $rules = ['esi_account_no' => ['unique:employees,esi_account_no']];
        }
        if ($request->pf_account_no != '') {
            $rules = ['pf_account_no' => ['unique:employees,pf_account_no']];
        }
        $customMessages = [
            'required' => ':Attribute  is required.',
            'unique' => ':Attribute already exists.'
        ];
        $this->validate($request, $rules, $customMessages);
        //   print_r($_POST);die;
        DB::beginTransaction();
        try {
            $Emp_customerID = $request->empID;
            $empDetail = Member::where('member_id', $Emp_customerID)->first();
            $member_Customer_id = NULL;
            $ID_Customer = null;
            if ($empDetail) {
                $ID_Customer = $empDetail->id ?? '';
                $member_Customer_id = $empDetail->member_id ?? '';
            }
            $globaldate = $request->created_at;
            $current_date = date("Y-m-d", strtotime(convertDate($globaldate)));
            $entry_date = date("Y-m-d", strtotime(convertDate($globaldate)));
            $entry_time = date("H:i:s", strtotime(convertDate($globaldate)));
            $date_create = $entry_date . ' ' . $entry_time;
            $created_at = date("Y-m-d H:i:s", strtotime(convertDate($date_create)));
            $updated_at = date("Y-m-d H:i:s", strtotime(convertDate($date_create)));
            $data['designation_id'] = $request->designation;
            $data['company_id'] = $request->company_id;
            $data['salary'] = $request->salary ?? 0;
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
            $data['esi_account_no'] = $request->esi_account_no;
            $data['pf_account_no'] = $request->pf_account_no;
            $data['customer_code'] = $member_Customer_id;
            $data['customer_id'] = $ID_Customer;
            $data['is_customer'] = $ID_Customer ? 1 : 0;
            $create = Employee::create($data);
            $employeeId = $create->id;
            $empUpdate = Employee::find($employeeId);
            if ($request->hasFile('photo')) {
                $photo_image = $request->file('photo');
                $photo_filename = $employeeId . '_' . time() . '.' . $photo_image->getClientOriginalExtension();
                $photo_location = 'asset/employee/' . $photo_filename;
                $mainFolder = 'employee/';
                ImageUpload::upload($photo_image, $mainFolder, $photo_filename);
                $empUpdate->photo = $photo_filename;
            } else {
                if ($request->hidden_photo != null && $request->hidden_photo != 'noimage') {
                    ImageUpload::ImageCopy($request->hidden_photo, 'asset/profile/member_avatar/', 'asset/employee/');
                    $empUpdate->photo = $request->hidden_image;
                }
            }
            $empUpdate->save();
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
        if (Auth::user()->role_id == 3) {
            return redirect('branch/hr/employee/application_print/' . $createApp->id."?type=1")->with('success', 'Employee Application Created  Successfully');
        } else {
            return redirect('admin/hr/employee/application_print/' . $createApp->id."?type=1")->with('success', 'Employee Application Created  Successfully');
        }
    }
    public function companydate(Request $request)
    {
        $data['companyDate'] = \App\Models\Companies::where('id', $request['company_id'])->first('created_at')->created_at;
        $data['companyDate'] = date('d/m/Y', strtotime(convertDate($data['companyDate'])));
        return json_encode($data['companyDate']);
    }
    public function employeeDetail(Request $request)
    {
        $msg = 0;
        $data = [];
        $msg_veryCustomerId = '';
        $customerID = $request->code;
        $member = Member::with([
            'memberNomineeDetails:id,member_id,relation,name,mobile_no',
            'memberBankDetails:id,member_id,bank_name,address,account_no,ifsc_code',
            'memberIdProof:id,member_id,first_address,second_address,first_id_type_id,first_id_no,first_id_no,second_id_type_id,second_id_no',
            'savingAccount'
        ])->where('member_id', $customerID)
            ->whereStatus('1')
            ->whereIsDeleted('0')
            ->first();
        if (!empty($member)) {
            $folderName = 'profile/member_avatar/' . $member->photo;
            if ($member->photo != null) {
                if (ImageUpload::fileExists($folderName)) {
                    // Generate pre-signed URL
                    $url = ImageUpload::generatePreSignedUrl($folderName);
                } else {
                    // Set a default URL or message when the image does not exist
                    $url = 'noimage';
                }
            } else {
                $url = 'noimage';
            }
            $data['member'] = $member;
            $data['image'] = $url;
            $msg = 1;
            $veryCustomerId = Employee::where('customer_id', $member->id)->exists() ? 1 : 0;

        } else {
            $veryCustomerId = Member::where('member_id', $customerID)->exists() ? 1 : 0;
            $memberCI = 1;
            $msg_veryCustomerId = (Member::where('member_id', $customerID)->whereStatus('0')->exists() ? 'Please check provided customer is Inactive' : (Member::where('member_id', $customerID)->whereIsDeleted('1')->exists() ? 'Please check provided customer is Deleted' : 'Something Went Wrong !'));
            return response()->json(['value' => $memberCI, 'msg_veryCustomerId' => $msg_veryCustomerId]);
        }
        $savingAccount = SavingAccount::whereCustomerId($member->id)->whereStatus('1')->whereIsDeleted('0')->get();
        return response()->json(['data' => $data, 'msg' => $msg, 'veryCustomer' => $veryCustomerId, 'savingAccount' => $savingAccount]);
    }
    /**
     * designation data  get by category.
     * Route: /hr 
     * Method: get 
     * @return  view
     */
    public function designationByCategory(Request $request)
    {
        $data = Designation::where('category', $request->category)->where('status', '1')->get();
        $return_array = compact('data');
        return json_encode($return_array);
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
            $account_no = SavingAccount::where('account_no', $ssbAccount)->when($companyId, function ($query) use ($companyId) {
                $query->where('company_id', $companyId);
            })->first(['member_id', 'account_no', 'id', 'created_at', 'company_id', 'customer_id']);
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
    public function ssbDataGet(Request $request)
    {
        $companyId = $request->company_id;
        $customerID = $request->customer_id;
        $data['account_no'] = '';
        $data['register_date'] = '';
        $data['id'] = '';
        if ($customerID) {
            $member = Member::where('member_id', $customerID)->first(['id', 'member_id']);
            if (!$member) {
                return response()->json(['error' => 'Member not found'], 404);
            }
            $memberCustomer = $member->id;
            $account = SavingAccount::where('customer_id', $memberCustomer)
                ->where('company_id', $companyId)
                ->first(['id', 'account_no', 'created_at']);
            if (!$account) {
                return response()->json(['error' => 'SSB Account not found'], 404);
            }
            $data['id'] = $account->id;
            $data['account_no'] = $account->account_no;
            $data['register_date'] = $account->created_at;
        }
        return response()->json($data);
    }
}