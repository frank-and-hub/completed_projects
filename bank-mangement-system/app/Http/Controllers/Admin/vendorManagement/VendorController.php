<?php

namespace App\Http\Controllers\Admin\vendorManagement;
use App\Http\Controllers\Admin\CommanController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Validator;
use App\Models\VendorCategory;
use App\Models\Vendor;
use App\Models\Employee;
use App\Models\RentLiability;
use App\Models\VendorBill;
use App\Models\VendorBillPayment;
use App\Models\VendorTransaction;
use App\Models\VendorBillItem;
use App\Models\VendorLog;
use App\Models\AccountHeads;
use App\Models\VendorCreditNode;
use App\Models\Member;
use App\Models\AdvancedTransaction;
use App\Models\AssociateTransaction;
use App\Models\CustomerTransaction;
use App\Models\RentPayment;
use App\Models\Companies;
use Carbon\Carbon;
use App\Models\EmployeeSalary;
use App\Models\CommissionLeaserDetail;
use DB;
use URL;
use Image;
use Yajra\DataTables\DataTables;
/*
    |---------------------------------------------------------------------------
    | Admin Panel -- Vendor Management VendorController
    |--------------------------------------------------------------------------
    |
    | This controller handles Vendor   all functionlity.
*/

class VendorController extends Controller
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
     * Show Vendor  .
     * Route: admin/vendor
     * Method: get 
     * @return  array()  Response
     */
    public function index()
    {
        if (check_my_permission(Auth::user()->id, "165") != "1") {
            return redirect()->route('admin.dashboard');
        }
        $data['category'] = VendorCategory::select('id', 'name')->where('status', 1)->orderby('id', 'DESC')->get();
        $data['title'] = 'Vendor Management | Vendors   List';
        $data['accountHeadLibilities'] = AccountHeads::select('id', 'head_id', 'sub_head')->where('parent_id', 53)->get();

        return view('templates.admin.vendor_management.vendor.index', $data);
    }
    /**
     * Get cheque list
     * Route: ajax call from - admin/vendor
     * Method: Post 
     * @param  \Illuminate\Http\Request  $request
     * @return JSON array
     */
    public function list(Request $request)
    {
        if ($request->ajax()) {

            $arrFormData = array();
            if (!empty($_POST['searchform'])) {
                foreach ($_POST['searchform'] as $frm_data) {
                    $arrFormData[$frm_data['name']] = $frm_data['value'];
                }
            }
            $data = Vendor::has('company')->select('id', 'name', 'company_name', 'email_id', 'mobile_no', 'pan_number', 'gst_type', 'gst_no', 'vendor_category', 'status', 'created_at', 'company_id')
                ->with('company:id,short_name')
                ->where('type', 0);
            /******* fillter query start ****/
            if (isset($arrFormData['is_search']) && $arrFormData['is_search'] == 'yes') {
                if ($arrFormData['status'] != '') {
                    $status = $arrFormData['status'];
                    $data = $data->where('status', $status);
                }
                if ($arrFormData['category'] != '') {
                    $category = $arrFormData['category'];
                    $data = $data->where('vendor_category', 'LIKE', '%' . $category . '%');
                }
                if ($arrFormData['company_id'] != null) {
                    $company_id = $arrFormData['company_id'];
                    if ($company_id != '0') {
                        $data = $data->where('company_id', $company_id);
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

            $data = $data->orderby('id', 'DESC')->get();

            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('name', function ($row) {
                    $name = $row->name;
                    return $name;
                })
                ->rawColumns(['name'])



                ->addColumn('cname', function ($row) {
                    $cname = $row->company_name;
                    return $cname;
                })
                ->rawColumns(['cname'])

                ->addColumn('companyname', function ($row) {
                    $companyname = $row->company->short_name;
                    return $companyname;
                })
                ->rawColumns(['companyname'])

                ->addColumn('email', function ($row) {
                    $email = $row->email_id;
                    if($email==''){

                        return $email='N/A';
                    }
                    else{
                        return $email;
                    }
                })
                ->rawColumns(['email'])

                ->addColumn('mobile', function ($row) {
                    $mobile = $row->mobile_no;
                    return $mobile;
                })
                ->rawColumns(['mobile'])

                ->addColumn('pan', function ($row) {
                    $pan = $row->pan_number;
                    return $pan;
                })
                ->rawColumns(['pan'])
                ->addColumn('gst_type', function ($row) {
                    $gst_type = '';
                    if ($row->gst_type == 1) {
                        $gst_type = 'Registered regular Business';
                    }
                    if ($row->gst_type == 2) {
                        $gst_type = 'Registered Compositor';
                    }
                    if ($row->gst_type == 3) {
                        $gst_type = 'Unregistered Business';
                    }
                    if ($row->gst_type == 4) {
                        $gst_type = 'Overseas';
                    }
                    return $gst_type;
                })
                ->rawColumns(['gst_type'])
                ->addColumn('gst_no', function ($row) {
                    $gst_no = $row->gst_no;
                    return $gst_no;
                })
                ->rawColumns(['gst_no'])

                ->addColumn('category', function ($row) {
                    $category = explode(',', $row->vendor_category);
                    $getName = VendorCategory::select('id', 'name')->whereIn('id', $category)->get();
              
                    $gt = '';
                    foreach ($getName  as $ind => $val) {
                        if (count($getName) != ($ind + 1)) {
                            $gt .= $val->name . ' , ';
                        } else {
                            $gt .= $val->name;
                        }
                    }
                    return $gt;
                })
                ->rawColumns(['category'])

                ->addColumn('status', function ($row) {
                    $status = 'Active';
                    if ($row->status == 1) {
                        $status = 'Active';
                    }
                    if ($row->status == 0) {
                        $status = 'Inactive';
                    }
                    return $status;
                })
                ->rawColumns(['status'])
                ->addColumn('created_at', function ($row) {
                    $created_at = date("d/m/Y", strtotime($row->created_at));
                    return $created_at;
                })
                ->rawColumns(['upcreated_atdated_at'])
                ->addColumn('action', function ($row) {

                    $btn = "";

                    if (check_my_permission(Auth::user()->id, "197") == "1" || check_my_permission(Auth::user()->id, "198") == "1") {
                        $btn .= '<div class="list-icons"><div class="dropdown"><a href="#" class="list-icons-item" data-toggle="dropdown"><i class="icon-menu9"></i></a><div class="dropdown-menu dropdown-menu-right">';
                    }
                    $url2 = URL::to("admin/vendor/edit/" . $row->id . "");
                    $url3 = URL::to("admin/vendor/detail/" . $row->id . "");

                    if (check_my_permission(Auth::user()->id, "197") == "1") {
                        $btn .= '<a class="dropdown-item" href="' . $url2 . '" title="Vendor  Edit"><i class="icon-pencil7  mr-2"></i>Vendor  Edit</a>  ';
                    }
                    if (check_my_permission(Auth::user()->id, "198") == "1") {
                        $btn .= '<a class="dropdown-item" href="' . $url3 . '" title="Vendor  Detail"><i class="icon-eye8  mr-2"></i>Vendor  Detail</a>  ';
                    }


                    $btn .= '</div></div></div>';
                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
    }

    public function listsCustomer(Request $request)
    {
        if ($request->ajax()) {

            $arrFormData = array();
            if (!empty($_POST['searchform'])) {
                foreach ($_POST['searchform'] as $frm_data) {
                    $arrFormData[$frm_data['name']] = $frm_data['value'];
                }
            }
            $data = Vendor::has('company')->where('type', 1);

            /******* fillter query start ****/
            if (isset($arrFormData['is_search']) && $arrFormData['is_search'] == 'yes') {
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
                if($arrFormData['company_id'] != ''){
                    $company_id = $arrFormData['company_id'];
                    if($company_id > 0){
                        $data = $data->whereCompanyId($company_id);
                    }
                }
            }

            $data = $data->orderby('created_at', 'DESC')->get();
            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('name', function ($row) {
                    $name = $row->name;
                    return $name;
                })
                ->rawColumns(['name'])
                ->addColumn('cname', function ($row) {
                    $cname = $row->company_name;
                    return $cname;
                })
                ->rawColumns(['cname'])
                ->addColumn('email', function ($row) {
                    $email = $row->email_id;
                    return $email;
                })
                ->rawColumns(['email'])

                ->addColumn('mobile', function ($row) {
                    $mobile = $row->mobile_no;
                    return $mobile;
                })
                ->rawColumns(['mobile'])

                ->addColumn('pan', function ($row) {
                    $pan = $row->pan_number;
                    return $pan;
                })
                ->rawColumns(['pan'])
                ->addColumn('gst_type', function ($row) {
                    $gst_type = '';
                    if ($row->gst_type == 1) {
                        $gst_type = 'Registered regular Business';
                    }
                    if ($row->gst_type == 2) {
                        $gst_type = 'Registered Compositor';
                    }
                    if ($row->gst_type == 3) {
                        $gst_type = 'Unregistered Business';
                    }
                    if ($row->gst_type == 4) {
                        $gst_type = 'Overseas';
                    }
                    return $gst_type;
                })
                ->rawColumns(['gst_type'])
                ->addColumn('gst_no', function ($row) {
                    $gst_no = $row->gst_no;
                    if($gst_no=''){

                        return $gst_no='N/A';
                    }else{

                        return $gst_no;
                    }
                })
                ->rawColumns(['gst_no'])
                ->addColumn('status', function ($row) {
                    $status = 'Active';
                    if ($row->status == 1) {
                        $status = 'Active';
                    }
                    if ($row->status == 0) {
                        $status = 'Inactive';
                    }
                    return $status;
                })
                ->rawColumns(['status'])
                ->addColumn('created_at', function ($row) {
                    $created_at = date("d/m/Y", strtotime($row->created_at));
                    return $created_at;
                })
                ->rawColumns(['upcreated_atdated_at'])
                ->addColumn('action', function ($row) {

                    $btn = "";

                    if (check_my_permission(Auth::user()->id, "199") == "1" || check_my_permission(Auth::user()->id, "200") == "1") {
                        $btn .= '<div class="list-icons"><div class="dropdown"><a href="#" class="list-icons-item" data-toggle="dropdown"><i class="icon-menu9"></i></a><div class="dropdown-menu dropdown-menu-right">';
                    }
                    $url2 = URL::to("admin/vendor/edit/" . $row->id . "");
                    $url3 = URL::to("admin/vendor/detail/" . $row->id . "");
                    if (check_my_permission(Auth::user()->id, "199") == "1") {
                        $btn .= '<a class="dropdown-item" href="' . $url2 . '" title="Vendor  Edit"><i class="icon-pencil7  mr-2"></i>Vendor  Edit</a>  ';
                    }
                    if (check_my_permission(Auth::user()->id, "200") == "1") {
                        $btn .= '<a class="dropdown-item" href="' . $url3 . '" title="Vendor  Detail"><i class="icon-eye8  mr-2"></i>Vendor  Detail</a>  ';
                    }

                    $btn .= '</div></div></div>';
                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
    }
    public function rentListing(Request $request)

    {

        if ($request->ajax() && check_my_permission(Auth::user()->id, "79") == "1") {



            $arrFormData = array();

            if (!empty($_POST['searchform'])) {

                foreach ($_POST['searchform'] as $frm_data) {

                    $arrFormData[$frm_data['name']] = $frm_data['value'];
                }
            }


            $data = RentLiability::with('liabilityBranch:id,name,branch_code,sector,regan,zone')
                ->with('SsbAccountNumberCustom:id,account_no')
                ->with(['employee_rent' => function ($query) {
                    $query->select('id', 'designation_id', 'employee_code', 'employee_name', 'mobile_no')
                        ->with('designation:id,designation_name');
                }]);

            if (isset($arrFormData['is_search']) && $arrFormData['is_search'] == 'yes') {

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
                if ($arrFormData['rent_type'] != '') {

                    $rent_type = $arrFormData['rent_type'];

                    $data = $data->where('rent_type', '=', $rent_type);
                }
            }



            $data = $data->orderby('created_at', 'DESC')->get();



            return Datatables::of($data)

                ->addIndexColumn()

                ->addColumn('branch', function ($row) {

                    $branch = $row['liabilityBranch']->name;

                    return $branch;
                })

                ->rawColumns(['branch'])



                ->addColumn('branch_code', function ($row) {

                    $branch_code = $row['liabilityBranch']->branch_code;

                    return $branch_code;
                })

                ->rawColumns(['branch_code'])

                ->addColumn('sector', function ($row) {

                    $sector = $row['liabilityBranch']->sector;

                    return $sector;
                })

                ->rawColumns(['sector'])

                ->addColumn('regan', function ($row) {

                    $regan = $row['liabilityBranch']->regan;

                    return $regan;
                })

                ->rawColumns(['regan'])

                ->addColumn('zone', function ($row) {

                    $zone = $row['liabilityBranch']->zone;

                    return $zone;
                })

                ->rawColumns(['zone'])





                ->addColumn('rent_type', function ($row) {

                    $rent_type =  $row->rent_type;
                    if ($rent_type == 93) {
                        return $rent_type = 'Guest House';
                    }
                    if ($rent_type == 94) {
                        return $rent_type = 'Office Rent';
                    } else {
                        return $rent_type = 'Lobby';
                    }
                })

                ->rawColumns(['rent_type'])

                ->addColumn('period_from', function ($row) {

                    $period_from = date("d/m/Y", strtotime(convertDate($row->agreement_from)));

                    return $period_from;
                })

                ->rawColumns(['period_from'])

                ->addColumn('period_to', function ($row) {

                    $period_to = date("d/m/Y", strtotime(convertDate($row->agreement_to)));

                    return $period_to;
                })

                ->rawColumns(['period_to'])

                ->addColumn('address', function ($row) {

                    $address = $row->place;

                    return $address;
                })

                ->rawColumns(['address'])



                ->addColumn('owner_name', function ($row) {

                    $owner_name = $row->owner_name;

                    return $owner_name;
                })

                ->rawColumns(['owner_name'])

                ->addColumn('owner_mobile_number', function ($row) {

                    $owner_mobile_number = $row->owner_mobile_number;

                    return $owner_mobile_number;
                })

                ->rawColumns(['owner_mobile_number'])

                ->addColumn('owner_pen_card', function ($row) {

                    $owner_pen_card = $row->owner_pen_number;

                    return $owner_pen_card;
                })

                ->rawColumns(['owner_pen_card'])

                ->addColumn('owner_aadhar_card', function ($row) {

                    $owner_aadhar_card = $row->owner_aadhar_number;

                    return $owner_aadhar_card;
                })

                ->rawColumns(['owner_aadhar_card'])

                ->addColumn('owner_ssb_account', function ($row) {
                    $owner_ssb_account = 'N/A';
                    if ($row->owner_ssb_id != '') {
                        $owner_ssb_account = $row['SsbAccountNumberCustom']->account_no;
                    }

                    return $owner_ssb_account;
                })

                ->rawColumns(['owner_ssb_account'])

                ->addColumn('bank_name', function ($row) {

                    $bank_name = $row->owner_bank_name;

                    return $bank_name;
                })

                ->rawColumns(['bank_name'])

                ->addColumn('bank_account_number', function ($row) {

                    $bank_account_number = $row->owner_bank_account_number;

                    return $bank_account_number;
                })

                ->rawColumns(['bank_account_number'])

                ->addColumn('ifsc_code', function ($row) {

                    $ifsc_code = $row->owner_bank_ifsc_code;

                    return $ifsc_code;
                })

                ->rawColumns(['ifsc_code'])

                ->addColumn('security_amount', function ($row) {

                    $security_amount = number_format((float)$row->security_amount, 2, '.', '');

                    return $security_amount;
                })

                ->rawColumns(['security_amount'])

                ->addColumn('rent', function ($row) {
                    $rent = number_format((float)$row->rent, 2, '.', '');
                    return $rent;
                })
                ->rawColumns(['rent'])
                ->addColumn('yearly_increment', function ($row) {
                    $yearly_increment = number_format((float)$row->yearly_increment, 2, '.', '') . '%';
                    return $yearly_increment;
                })
                ->rawColumns(['yearly_increment'])
                ->addColumn('office_area', function ($row) {
                    $office_area = $row->office_area;
                    return $office_area;
                })
                ->rawColumns(['office_area'])
                ->addColumn('advance_payment', function ($row) {
                    $advance_payment = $row->advance_payment;
                    return $advance_payment;
                })
                ->rawColumns(['advance_payment'])
                ->addColumn('current_balance', function ($row) {
                    $current_balance = $row->current_balance;
                    return $current_balance;
                })
                ->rawColumns(['current_balance'])
                ->addColumn('employee_code', function ($row) {
                    $employee_code = $row['employee_rent']->employee_code;
                    return $employee_code;
                })
                ->rawColumns(['employee_code'])
                ->addColumn('employee_name', function ($row) {
                    $employee_name = $row['employee_rent']->employee_name;
                    return $employee_name;
                })
                ->rawColumns(['employee_name'])
                ->addColumn('employee_designation', function ($row) {
                    $employee_designation = $row['employee_rent']->designation->designation_name;
                    return $employee_designation;
                })
                ->rawColumns(['employee_designation'])
                ->addColumn('mobile_number', function ($row) {
                    $mobile_number = $row['employee_rent']->mobile_no;
                    return $mobile_number;
                })

                ->rawColumns(['mobile_number'])
                ->addColumn('rent_agreement', function ($row) {
                    $rent_agreement = getFileData($row->rent_agreement_file_id);
                    foreach ($rent_agreement as $key => $value) {
                        return '<a href="samraddhbestwin/core/storage/images/rent-liabilities/' . $value->file_name . '" target="blank">' . $value->file_name . '</a>';
                    }
                    /*if(!empty($rent_agreement)){

                    $file = '<a href="samraddhbestwin/core/storage/images/rent-liabilities/'.$rent_agreement[0]->file_name.'" target="blank">'.$rent_agreement[0]->file_name.'<a>';

                }else{

                    $file = '';

                }*/
                })
                ->escapeColumns(['mobile_number'])
                ->addColumn('agreement_status', function ($row) {

                    if ($row->status == 0) {
                        $agreement_status = 'Active';
                    } else {
                        $agreement_status = 'Deactive';
                    }

                    return $agreement_status;
                })

                ->rawColumns(['agreement_status'])
                ->addColumn('created_at', function ($row) {

                    $created_at = date("d/m/Y", strtotime($row->created_at));

                    return $created_at;
                })

                ->rawColumns(['created_at'])

                ->addColumn('action', function ($row) {
                    $url = URL::to("admin/rent/edit-rent-liability/" . $row->id . "");

                    $url1 = URL::to("admin/vendor/detail_rent/" . $row->id);


                    $btn = '<div class="list-icons"><div class="dropdown"><a href="#" class="list-icons-item" data-toggle="dropdown"><i class="icon-menu9"></i></a><div class="dropdown-menu dropdown-menu-right">';

                    $btn .= '<a class="dropdown-item" href="' . $url1 . '" title="Rent Owner Detail"><i class="icon-eye8  mr-2"></i>Rent Owner Detail</a>';

                    $btn .= '<a class="dropdown-item" href="' . $url . '" title="Rent Owner Edit"><i class="icon-pencil7 mr-2"></i>Rent Owner Edit</a>';
                    $btn .= '</div></div></div>';
                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
    }


    public function employeeListing(Request $request)
    {
        if ($request->ajax()) {
            $arrFormData = array();
            if (!empty($_POST['searchform'])) {
                foreach ($_POST['searchform'] as $frm_data) {
                    $arrFormData[$frm_data['name']] = $frm_data['value'];
                }
            }
            $data = Employee::with(['branch' => function ($query) {
                $query->select('id', 'name', 'branch_code', 'sector', 'regan', 'zone');
            }])->where('is_employee', 1);
            /******* fillter query start ****/

            if (isset($arrFormData['is_search']) && $arrFormData['is_search'] == 'yes') {
                if ($arrFormData['status'] != '') {
                    $status = $arrFormData['status'];

                    if ($status == 1) {
                        $data = $data->where('status', 1);
                    }
                    if ($status == 0) {
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
            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('designation', function ($row) {
                    $designation = getDesignationData('designation_name', $row->designation_id)->designation_name;
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
                    $branch = $row['branch']->name ?? "N/A";
                    return $branch;
                })
                ->rawColumns(['branch'])
                ->addColumn('branch_code', function ($row) {
                    $branch_code = $row['branch']->branch_code ?? "N/A";
                    return $branch_code;
                })
                ->rawColumns(['branch_code'])
                ->addColumn('sector', function ($row) {
                    $sector = $row['branch']->sector;
                    return $sector;
                })
                ->rawColumns(['sector'])
                ->addColumn('regan', function ($row) {
                    $regan = $row['branch']->regan;
                    return $regan;
                })
                ->rawColumns(['regan'])
                ->addColumn('zone', function ($row) {
                    $zone = $row['branch']->zone;
                    return $zone;
                })
                ->rawColumns(['zone'])
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



                    $btn = '<div class="list-icons"><div class="dropdown"><a href="#" class="list-icons-item" data-toggle="dropdown"><i class="icon-menu9"></i></a><div class="dropdown-menu dropdown-menu-right">';


                    $url2 = URL::to("admin/hr/employee/edit/" . $row->id);
                    $url4 = URL::to("admin/hr/employee/detail/" . $row->id);
                    $url = URL::to("admin/vendor/detail_employee/" . $row->id);

                    $btn .= '<a class="dropdown-item" href="' . $url . '" title="Employee Detail"><i class="icon-eye8  mr-2"></i>Employee Detail</a>';
                    $btn .= '<a class="dropdown-item" href="' . $url2 . '" title="Employee Edit"><i class="icon-pencil7  mr-2"></i>Employee Edit</a>';

                    $btn .= '</div></div></div>';
                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
    }

    /**
     * Vendor   delete.
     * Route: admin/vendor/delete
     * Method: get 
     * @return  array()  Response
     */
    public function vendorDelete($id)
    {
        DB::beginTransaction();
        try {
            $chk = 0;
            if ($chk == 0) {
                $deleteupdate = Vendor::whereId($id)->delete();
            }

            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('alert', $ex->getMessage());
        }
        return redirect()->route('admin.vendor')->with('success', 'Vendor Deleted Successfully');
    }

    /**
     * Add  Vendor  .
     * Route: admin/vendor/add
     * Method: get 
     * @return  array()  Response
     */
    public function add()
    {
        if (check_my_permission(Auth::user()->id, "195") != "1" && check_my_permission(Auth::user()->id, "196") != "1") {
            return redirect()->route('admin.dashboard');
        }
        $data['title'] = 'Vendor Management | Vendor Registration';
        $data['category'] = VendorCategory::select('id', 'name')->where('status', 1)->orderby('id', 'DESC')->get();
        $data['state'] = stateList();
        $data['companies'] = Companies::select('id','name','created_at')->get();
        // dd($data['companies']);
        return view('templates.admin.vendor_management.vendor.add', $data);
    }

    public function companydate(Request $request)
    {
        $company_id =  $request['company_id'];
        if($company_id != '0'){
            $companyDate = \App\Models\Companies::when($company_id != '0',function($q)use ($company_id){
                $q->where('id',$company_id);
            })->first('created_at')->created_at;
            $data['companyDate'] = date('d/m/Y', strtotime(convertDate($companyDate)));            
            return json_encode($data['companyDate']);
        }else{
            return json_encode('05/08/2021');
        }
    }
    /**
     * 
     * save  Vendor  .
     * Route: admin/vendor/add
     * Method: get 
     * @return  array()  Response
     */
    public function save(Request $request)
    {
        DB::beginTransaction();
        try {
            $category = '';
            if ($request->type == 0) {
                if (!isset($_POST['category'])) {
                    return back()->with('alert', 'Please Select Category');
                }
                if (count($request->category) > 0) {
                    $category = implode(",", $request->category);
                } else {
                    $category = '';
                }
            }
            // print_r($category);die;

            $globaldate = $request->created_at;
            $select_date = $request->select_date;
            $current_date = date("Y-m-d", strtotime(convertDate($globaldate)));
            $entry_date = date("Y-m-d", strtotime(convertDate($select_date)));
            $entry_time = date("H:i:s", strtotime(convertDate($globaldate)));
            $date_create = $entry_date . ' ' . $entry_time;
            $created_at = date("Y-m-d H:i:s", strtotime(convertDate($date_create)));
            $updated_at = date("Y-m-d H:i:s", strtotime(convertDate($date_create)));

            $data['company_id']= $request->searchCompanyId;
            $data['name'] = $request->name;
            $data['company_name'] = $request->company_name;
            $data['email_id'] = $request->email;
            $data['mobile_no'] = $request->mobile;
            $data['gst_type'] = $request->gst_treatment;
            $data['gst_no'] = $request->gst_no;
            $data['pan_number'] = $request->pan_card;
            $data['vendor_category'] = $category;
            $data['address'] = trimData($request->address);
            $data['city'] = $request->city;
            $data['state'] = $request->state;
            $data['zip_code'] = $request->zip_code;
            $data['bank_name'] = $request->bank_name;
            $data['bank_ac_no'] = $request->account_no;
            $data['ifsc'] = $request->ifsc_code;
            $data['ssb_account_id'] = $request->ssb_account_id;
            $data['ssb_account'] = $request->ssb_account;
            $data['status'] = 1;
            $data['created_at'] = $created_at;
            $data['updated_at'] = $created_at;
            $data['type'] = $request->type;

            $create = Vendor::create($data);

            $created_by = 1;
            $created_by_id = \Auth::user()->id;
            $created_by_name = \Auth::user()->username;

            $data1['vendor_id'] = $create->id;
            $data1['title'] = 'Vendor Contact Added';
            $data1['description'] = NULL;
            $data1['created_by'] = $created_by;
            $data1['created_by_id'] = $created_by_id;
            $data1['created_by_name'] = $created_by_name;
            $data1['created_at'] = $created_at;
            $data1['updated_at'] = $created_at;

            $create1 = VendorLog::create($data1);


            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('alert', $ex->getMessage());
        }
        return redirect('admin/vendor')->with('success', 'Vendor Created Successfully');
    }

    /**
     * Edit  Vendor  .
     * Route: admin/vendor/edit
     * Method: get 
     * @return  array()  Response
     */
    public function edit($id)
    {
        if (check_my_permission(Auth::user()->id, "197") != "1" && check_my_permission(Auth::user()->id, "199") != "1") {
            return redirect()->route('admin.dashboard');
        }

        // $data['vendor']=Vendor::where('id',$id)->first();
        $data['vendor'] = Vendor::where('id', $id)
            ->with(['vendortransaction' => function ($query) use ($id) {
                $query->where('vendor_id', $id)->orderBy('id');
            }])->first();
        $data['vendor']->vendortransaction->pluck('vendor_id')->count();
        // dd($vendorIdCount);
        if ($data['vendor']->type == 0) {

            $data['title'] = 'Vendor Management | Vendor Edit';
        } else {
            $data['title'] = 'Vendor Management | Customer Edit';
        }
        $data['get_cat'] = '';
        if ($data['vendor']->vendor_category != '') {
            $data['get_cat'] = $data['vendor']->vendor_category;
        }
        // print_r($data['get_cat']);die;

        $data['category'] = VendorCategory::where('status', 1)->orderby('id', 'DESC')->get();
        $data['state'] = stateList();
        return view('templates.admin.vendor_management.vendor.edit', $data);
    }
    /**
     * Update  Vendor  .
     * Route: admin/vendor/edit
     * Method: get 
     * @return  array()  Response
     */
    public function update(Request $request)
    {
        $created_by = 1;
        $created_by_id = \Auth::user()->id;
        $created_by_name = \Auth::user()->username;
        DB::beginTransaction();
        try {
            $category = '';
            if ($request->type == 0) {
                if (!isset($_POST['category'])) {
                    return back()->with('alert', 'Please Select Category');
                }
                if (count($request->category) > 0) {
                    $category = implode(",", $request->category);
                } else {
                    $category = '';
                }
            }


            $data['name'] = $request->name;
            $data['company_id']= $request->company;
            $data['company_name'] = $request->company_name;
            $data['email_id'] = $request->email;
            $data['mobile_no'] = $request->mobile;
            $data['gst_type'] = $request->gst_treatment;
            $data['gst_no'] = $request->gst_no;
            $data['pan_number'] = $request->pan_card;
            $data['vendor_category'] = $category;
            $data['address'] = trimData($request->address);
            $data['city'] = $request->city;
            $data['state'] = $request->state;
            $data['zip_code'] = $request->zip_code;
            $data['bank_name'] = $request->bank_name;
            $data['bank_ac_no'] = $request->account_no;
            $data['ifsc'] = $request->ifsc_code;
            $data['ssb_account_id'] = $request->ssb_account_id;
            $data['ssb_account'] = $request->ssb_account;
            $data['type'] = $request->type;
            $updatedata = Vendor::find($request->id);
            $updatedata->update($data);

            $data1['vendor_id'] = $request->id;
            $data1['title'] = 'Vendor Contact Updated';
            $data1['description'] = NULL;
            $data1['created_by'] = $created_by;
            $data1['created_by_id'] = $created_by_id;
            $data1['created_by_name'] = $created_by_name;
            // $data1['created_at'] = $created_at; 
            // $data1['updated_at'] = $created_at; 

            $create1 = VendorLog::create($data1);



            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('alert', $ex->getMessage());
        }
        return redirect('admin/vendor')->with('success', 'Vendor Updated Successfully');
    }

    /**
     * Edit  Vendor  .
     * Route: admin/vendor/edit
     * Method: get 
     * @return  array()  Response
     */
    public function detail($id)
    {
        if (check_my_permission(Auth::user()->id, "198") != "1" && check_my_permission(Auth::user()->id, "200") != "1") {
            return redirect()->route('admin.dashboard');
        }

        $data['vendor'] = Vendor::where('id', $id)->first();

        if ($data['vendor']->type == 0) {
            $data['outstanding'] = VendorBill::where('vendor_id', $id)->where('is_deleted', 0)->sum('balance');
            //$data['unused_credit']=AdvancedTransaction::where('vendor_id',$id)->sum('balance');   
            $data['credit_node'] = VendorCreditNode::where('vendor_id', $id)->where('is_deleted', 0)->sum('total_amount');
            $data['title'] = 'Vendor Management | Vendor Detail';
            $advance_amt_dr =  AdvancedTransaction::where('is_deleted', 0)->where('type', 1)->where('type_id', $id)->where('payment_type', "DR")->sum('amount');
            
            $advance_amt_cr =  AdvancedTransaction::where('is_deleted', 0)->where('type', 1)->where('type_id', $id)->where('payment_type', "CR")->sum('amount');
            
            $total_advanced_amt = $advance_amt_dr - $advance_amt_cr;
            $advance_amt = $total_advanced_amt;
            $data['advance_amt'] = number_format((float)$advance_amt, 2, '.', '');
            $data['vendor_payments']=VendorBill::where('vendor_id',$id)->whereIN('bill_type',['4','0'])->where('due_amount','>',0)->where('is_deleted',0)->get();
            $data['advance_transactions'] = VendorTransaction::select('id', 'branch_id', 'amount', 'entry_date','vendor_id','type_transaction_id')
            ->where('type', 5)
            ->where('sub_type', 51)
            ->where('vendor_id', $id)
            ->whereHas('advance_transactions',function($q){
                $q->select('used_amount','id','type_id','type_transaction_id','amount')->where('amount','!=','0');
           
            })
            ->with(['branches'=> function ($q) {
                $q->select('id','name')->get();
            }])->orderby('entry_date', 'ASC')
            ->get();

            foreach ($data['advance_transactions'] as $detail) {
                $detail->entry_date = date('d-m-Y', strtotime($detail->entry_date));
            }
            
        } else {
            $data['outstanding'] = CustomerTransaction::where('customer_id', $id)->where('is_deleted', 0)->sum('amount');
            //$data['unused_credit']=AdvancedTransaction::where('vendor_id',$id)->sum('balance');   
            $data['credit_node'] = VendorCreditNode::where('vendor_id', $id)->where('is_deleted', 0)->sum('total_amount');
            $data['title'] = 'Vendor Management | Customer Detail';
            $advance_amt_dr =  AdvancedTransaction::where('is_deleted', 0)->where('type', 2)->where('type_id', $id)->where('payment_type', "DR")->sum('amount');
            $advance_amt_cr =  AdvancedTransaction::where('is_deleted', 0)->where('type', 2)->where('type_id', $id)->where('payment_type', "CR")->sum('amount');
            $total_advanced_amt =     $advance_amt_dr - $advance_amt_cr;
            $advance_amt = $total_advanced_amt;
            $data['advance_amt'] = number_format((float)$advance_amt, 2, '.', '');
        }
        return view('templates.admin.vendor_management.vendor.detail', $data);
    }

    public function advancepayment(Request $request)
    {
       
       
        
        DB::beginTransaction();
        try {

            $globaldate = $request->created_at;
            $select_date = $request->payment_date;
            $current_date = date("Y-m-d", strtotime(convertDate($globaldate)));
            $entry_date = date("Y-m-d", strtotime(convertDate($select_date)));
            $entry_time = date("H:i:s", strtotime(convertDate($globaldate)));
            $date_create = $entry_date . ' ' . $entry_time;
            $created_at = date("Y-m-d H:i:s", strtotime(convertDate($date_create)));
            $updated_at = date("Y-m-d H:i:s", strtotime(convertDate($date_create)));
            $vendorDetail = Vendor::where('id', $request->vid)->first();
            $advance_id= $request->advance_id;
            $currency_code = 'INR';
            $created_by = 1;
            $created_by_id = \Auth::user()->id;
            $created_by_name = \Auth::user()->username;
            $v_no = NULL;
            $v_date = NULL;

            $typeBill = 27;
            $sub_typeBill = 272;
            $typeAdv = 27;
            $sub_typeAdv = 275;


            $cheque_id = NULL;
            $cheque_number = $cheque_no = NULL;
            $cheque_date = NULL;
            $utr_no = NULL;
            $transaction_date = NULL;
            $neft_charge  = NULL;

            $bank_id = NULL;
            $bank_ac_id = NULL;
            $associate_id = NULL;
            $member_id = NULL;
            $branch_id_to = NULL;
            $branch_id_from = NULL;
            $opening_balance = NULL;
            $closing_balance = NULL;
            $amount_to_id = NULL;
            $amount_to_name = NULL;
            $amount_from_id = NULL;
            $amount_from_name = NULL;
            $ssb_account_id_from = NULL;
            $cheque_no = NULL;
            $cheque_date = NULL;
            $cheque_bank_from = NULL;
            $cheque_bank_ac_from = NULL;
            $cheque_bank_ifsc_from = NULL;
            $cheque_bank_branch_from = NULL;
            $cheque_bank_to = NULL;
            $cheque_bank_ac_to = NULL;
            $transction_no = NULL;
            $transction_bank_from = NULL;
            $transction_bank_ac_from = NULL;
            $transction_bank_ifsc_from = NULL;
            $transction_bank_branch_from = NULL;
            $transction_bank_to = NULL;
            $transction_bank_ac_to = NULL;
            $transction_date = NULL;
            $jv_unique_id = NULL;
            $ssb_account_id_to = NULL;
            $ssb_account_tran_id_to = NULL;
            $ssb_account_tran_id_from = NULL;
            $cheque_type = NULL;
            $cheque_id = NULL;
            $cheque_bank_to_name = NULL;
            $cheque_bank_to_branch = NULL;
            $cheque_bank_to_ac_no = NULL;
            $cheque_bank_to_ifsc = NULL;
            $transction_bank_from_id = NULL;
            $transction_bank_from_ac_id = NULL;
            $transction_bank_to_name = NULL;
            $transction_bank_to_ac_no = NULL;
            $transction_bank_to_branch = NULL;
            $transction_bank_to_ifsc = NULL;
            $cheque_bank_from_id = NULL;
            $cheque_bank_ac_from_id = NULL;
            $companyId=$request->company_id;
            $paymentMode = 3;
            $total_amount=$request->total_amount;
            $totalPaybaleAmount = $request->total_amount;
            $daybookRef = CommanController::createBranchDayBookReferenceNew($totalPaybaleAmount, $globaldate);
            $refId = $daybookRef;

            $type_id = $vendorId = $request->vid;
            $bank_id_from_c = $request->bank_id;
            $bank_ac_id_from_c = $bank_id_ac = $request->bank_ac;
            $bank_id_from = $bank_id_from_c;

            $bank_ac_id_from = $bank_ac_id_from_c;
            $bank_name_to = $vendorDetail->bank_name;
            $bank_ac_to = $vendorDetail->bank_ac_no;
            $bank_ifsc_to = $vendorDetail->ifsc;
           $sum =0;
            
            $vendorBillID = '';
            if (isset($_POST['bill_id'])) {
                foreach (($_POST['bill_id']) as $key => $option) {
                    if ($_POST['pay_amount'][$key] > 0) {
                        $bill_id = $_POST['bill_id'][$key];
                        $billDetaill = $billDetail = VendorBill::where('id', $_POST['bill_id'][$key])->first();
                        
                        $branch_id_bill = $billDetaill->branch_id;
                        $vendorBillID = $billDetaill->id;
                        $status = 1;
                        $trAmount = $billDetaill->transferd_amount + $_POST['pay_amount'][$key];
                        $due = $billDetaill->payble_amount - $trAmount;
                        if ($due == 0) {
                            $status = 2;
                        }
                        $vendorBill['transferd_amount'] = $trAmount;
                        $vendorBill['due_amount'] = $due;
                        $vendorBill['balance'] = $due;
                        $vendorBill['status'] = $status;
                        $vendorBill['payment_date'] = $entry_date;
                        $vendorBill['partial_daybook_ref_id'] =$billDetaill->partial_daybook_ref_id . ',' . $refId;
                        $vendorBillUpdate = VendorBill::find($bill_id);
                        $vendorBillUpdate->update($vendorBill);
                        $desLibe1 = 'Bill(' . $billDetaill->bill_number . ') payment  for Rs.' . number_format((float)$_POST['pay_amount'][$key], 2, '.', '') . 'From Advance Payment';
                        $vendorBillPayment['branch_id'] = $branch_id_bill;
                        $vendorBillPayment['vendor_id'] = $vendorId;
                        $vendorBillPayment['vendor_bill_id'] = $billDetail->id;
                        $vendorBillPayment['bill_type'] = 0;
                        $vendorBillPayment['withdrawal'] = $_POST['pay_amount'][$key];
                        $vendorBillPayment['description'] = $desLibe1;
                        $vendorBillPayment['currency_code'] = $currency_code;
                        $vendorBillPayment['payment_type'] = 'DR';
                        $vendorBillPayment['payment_mode'] = $paymentMode;
                        $vendorBillPayment['payment_date'] = $entry_date;
                        $vendorBillPayment['created_by'] = $created_by;
                        $vendorBillPayment['created_by_id'] = $created_by_id;
                        $vendorBillPayment['created_at'] = $created_at;
                        $vendorBillPayment['updated_at'] = $updated_at;
                        $vendorBillPayment['daybook_ref_id'] = $refId;
                        $vendorBillPayment['company_id'] = $companyId;
                        $vendorBillPaymentCreate = VendorBillPayment::create($vendorBillPayment);
                        $billPaymentId = $vendorBillPaymentCreate->id;
                        
                        // ----------- Eli Amount --------
                        if ($paymentMode == 3) {

                            // ------------ Cash  head --------                                                          
                            $desLibe = $desLibe1;
                            $allTran1 = CommanController::newHeadTransactionCreate ($refId, $branch_id_bill, $bank_id, $bank_ac_id, 185, $typeBill, $sub_typeBill, $type_id, $associate_id= NULL, $member_id, $branch_id_to= NULL, $branch_id_from= NULL,  $_POST['pay_amount'][$key],  $desLibe, 'CR', 3, $currency_code,  $v_no,  $ssb_account_id_from, $cheque_no,  $transction_no,  $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $billPaymentId, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $companyId);

                           

                            $description_dr_b1 = $vendorDetail->name . 'A/c Dr ' . number_format((float)$_POST['pay_amount'][$key], 2, '.', '') . '/-';
                            $description_cr_b1 = 'To Branch Cash A/c Cr' . number_format((float)$_POST['pay_amount'][$key], 2, '.', '') . '/-';

                            $brDaybook1 = CommanController::branchDaybookCreateModified($refId, $branch_id_bill, $typeBill, $sub_typeBill, $vendorId, $associate_id = NULL, $member_id = NULL, $branch_id_to = NULL, $branch_id_from = NULL, $_POST['pay_amount'][$key], $desLibe, $description_dr_b1, $description_cr_b1, 'DR', $paymentMode, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id,  $created_at, $updated_at, $billPaymentId, $ssb_account_id_to, $companyId);
                        }


                        //---------   vendor transaction -----------

                        $desExp = $desLibe1;

                        $desTran = $desExp;
                        $vendorTran1['type'] = 2;
                        $vendorTran1['sub_type'] = 22;
                        $vendorTran1['type_id'] = $vendorBillID;
                        $vendorTran1['vendor_id'] = $vendorId;
                        $vendorTran1['type_transaction_id'] = $billPaymentId;
                        $vendorTran1['branch_id'] = $branch_id_bill;
                        $vendorTran1['amount'] = $_POST['pay_amount'][$key];
                        $vendorTran1['description'] = $desLibe1;
                        $vendorTran1['payment_type'] = 'DR';
                        $vendorTran1['payment_mode'] = $paymentMode;
                        $vendorTran1['currency_code'] = 'INR';
                        $vendorTran1['v_no'] = $v_no;
                        $vendorTran1['bank_id'] = $bank_id;
                        $vendorTran1['account_id'] = $bank_ac_id;

                        $vendorTran1['entry_date'] = $entry_date;
                        $vendorTran1['entry_time'] = $entry_time;
                        $vendorTran1['created_by'] = 1;
                        $vendorTran1['created_by_id'] = $created_by_id;
                        $vendorTran1['created_at'] = $created_at;
                        $vendorTran1['updated_at'] = $updated_at;
                        $vendorTran1['daybook_ref_id'] = $refId;
                        $vendorTran1['company_id'] = $companyId;

                        $vendorTranCreate1 = VendorTransaction::create($vendorTran1);
                        $vendorTranID1 = $vendorTranCreate1->id;
                        /// -----  vendor libility head ------
                        $LibHead = 140;
                        $desLibe = $desLibe1;

                        $allTran1 = CommanController::newHeadTransactionCreate(
                            $daybook_ref_id=$refId,
                            $branch_id=$branch_id_bill, 
                            $bank_id, 
                            $bank_ac_id, $LibHead, $typeBill, $sub_typeBill, $type_id, $associate_id= NULL, $member_id, $branch_id_to= NULL, $branch_id_from= NULL,  $_POST['pay_amount'][$key],  $desLibe, 'DR', 3, $currency_code,  $v_no,  $ssb_account_id_from, $cheque_no,  $transction_no,  $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $billPaymentId, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $companyId);

                         

                        $vendorLog['vendor_id'] = $vendorId;
                        $vendorLog['vendor_bill_id'] = $vendorBillID;
                        $vendorLog['title'] = 'Bill Payment';
                        $vendorLog['bill_no'] = $billDetaill->bill_number;
                        $vendorLog['description'] = 'Bill Payment from Advance Payment for Rs.' . number_format((float)$_POST['pay_amount'][$key], 2, '.', '') . ' by ' . $created_by_name;
                        $vendorLog['amount'] = $_POST['pay_amount'][$key];
                        $vendorLog['item_detail'] = 'Bill(' . $billDetaill->bill_number . ')';
                        $vendorLog['item_id'] = NULL;
                        $vendorLog['created_by'] = 1;
                        $vendorLog['created_by_id'] = $created_by_id;
                        $vendorLog['created_by_name'] = $created_by_name;
                        $vendorLog['created_at'] = $created_at;
                        $vendorLog['updated_at'] = $updated_at;
                        $vendorLog['daybook_ref_id'] = $refId;
                        $vendorLogCreate = VendorLog::create($vendorLog);
                        $vendorLogID = $vendorLogCreate->id;



                        
                      
                    }
                }
                $AdvancedTransactionUpdate1 = AdvancedTransaction::find($advance_id);
                
                $vendorTran1a['type'] = 1;
                $vendorTran1a['type_id'] = $vendorId;
                // $vendorTran1a['type_transaction_id'] = $billPaymentId;
                $vendorTran1a['branch_id'] = $request->branch_id;
                $vendorTran1a['used_amount'] = $AdvancedTransactionUpdate1->used_amount + $request->total_amount;
                $vendorTran1a['amount'] = $AdvancedTransactionUpdate1->amount - $request->total_amount;
                $vendorTran1a['description'] = $desAdv = $vendorDetail->name . 'Bill Payment Through advanced Payment of Rs. ' . number_format((float)$sum, 2, '.', '') . '/-';
                $vendorTran1a['payment_mode'] = $paymentMode;
                $vendorTran1a['sub_type'] = 11;
                $vendorTran1a['currency_code'] = 'INR';
                $vendorTran1a['v_no'] = $v_no;
                $vendorTran1a['entry_date'] = $entry_date;
                $vendorTran1a['entry_time'] = $entry_time;
                $vendorTran1a['created_by'] = 1;
                $vendorTran1a['created_by_id'] = $created_by_id;
                $vendorTran1a['created_at'] = $created_at;
                $vendorTran1a['updated_at'] = $updated_at;
                $vendorTran1a['partial_daybook_ref_id'] =$AdvancedTransactionUpdate1->partial_daybook_ref_id . ',' . $refId;
                $vendorTran1a['company_id'] = $companyId;
                $AdvancedTransactionUpdate = AdvancedTransaction::find($advance_id);
                $AdvancedTransactionUpdate->update($vendorTran1a);
            }
            
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('alert', $ex->getMessage());
        }

        return back()->with('success', 'Advance Settlement Successfully');
    }

    public function advancepaymentduelist(Request $request)
    {
         
        $data['details'] = VendorBill::select('id', 'branch_id', 'bill_number', 'payble_amount','due_amount','bill_date','vendor_id')
            ->where('bill_date', '>=', $request->entryDate)
            ->where('branch_id', $request->branchId)
            ->where('vendor_id',$request->vendor_id)
            ->where('balance', '!=',0)
            ->orderBy('bill_date', 'asc')
            ->with(['vendorBranchDetail' => function ($q) {
                $q->select('id', 'name');
            }])->get();
            foreach ($data['details'] as $detail) {
                $detail->bill_date = date('d-m-Y', strtotime($detail->bill_date));
            }
            $data['lower_date'] = $data['details']->isEmpty() ? null : $data['details']->first()->bill_date;
            $formattedLowerDate = $data['lower_date'] ? date('d-m-Y', strtotime($data['lower_date'])) : null;
            $data['formatted_lower_date'] = $formattedLowerDate;
            return response()->json($data);
    }

    /**
     * Edit  Vendor  .
     * Route: admin/vendor/edit
     * Method: get 
     * @return  array()  Response
     */
    public function print()
    {
        $data['title'] = 'Vendor Management | Vendor Print';
        // $data['vendor'] = Vendor::where('id', $id)->first();
        return view('templates.admin.vendor_management.vendor.print', $data);
    }


    /**
     * save  Vendor Category .
     * Route: admin/vendor/category/add
     * Method: get 
     * @return  array()  Response
     */
    public function categorySave(Request $request)
    {
        $id = 0;
        $msg_type = '';
        $error = '';
        DB::beginTransaction();
        try {

            $data['name'] = $request->name;
            $data['status'] = 1;
            $data['created_at'] = $request->created_at;
            $create = VendorCategory::create($data);
            $id = $create->id;

            $msg_type = 'success';
            $error = '';
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            /// return back()->with('alert', $ex->getMessage());
            $msg_type = 'error';
            $error = $ex->getMessage();
        }
        $data_cat = VendorCategory::where('status', 1)->orderby('id', 'DESC')->get();

        $return_array = compact('data_cat', 'msg_type', 'error', 'id');

        return json_encode($return_array);
    }


    /**
     * status change Customer &  Vendor  .
     * Route: admin/admin/vendor/status/
     * Method: get 
     * @return  array()  Response
     */
    public function changeStatus($id, $status)
    {

        $created_by = 1;
        $created_by_id = \Auth::user()->id;
        $created_by_name = \Auth::user()->username;
        DB::beginTransaction();
        try {

            if ($status == 1) {
                $data['status'] = 0;
                $msg = 'Mark as inactive';
            } else {
                $data['status'] = 1;
                $msg = 'Mark as active';
            }
            $updatedata = Vendor::find($id);
            $updatedata->update($data);

            $data1['vendor_id'] = $id;
            $data1['title'] = 'Vendor Status Changed';
            $data1['description'] = $msg;
            $data1['created_by'] = $created_by;
            $data1['created_by_id'] = $created_by_id;
            $data1['created_by_name'] = $created_by_name;
            // $data1['created_at'] = $created_at; 
            // $data1['updated_at'] = $created_at; 

            $create1 = VendorLog::create($data1);

            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('alert', $ex->getMessage());
        }
        if ($updatedata->type == 0) {
            return back()->with('success', 'Vendor Status Changed Successfully');
        } else {
            return back()->with('success', 'Customer Status Changed Successfully');
        }
    }

    /**
     * deleteCustomer &  Vendor  .
     * Route: admin/admin/vendor/status/
     * Method: get 
     * @return  array()  Response
     */
    public function delete($id)
    {

        DB::beginTransaction();
        try {
            $vendor = Vendor::where('id', $id)->first();
            $chk = VendorBill::where('vendor_id', $id)->count();
            if ($chk == 0) {
                $deleteupdate = Vendor::whereId($id)->delete();
            } else {
                if ($vendor->type == 0) {
                    return back()->with('success', 'The vendor cannot be deleted  moved because the bill has already been created.');
                } else {
                    return back()->with('success', 'The customer cannot be deleted because the bill has already been created.');
                }
            }

            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('alert', $ex->getMessage());
        }

        if ($vendor->type == 0) {
            return redirect('admin/vendor')->with('success', 'Vendor Deleted Successfully');
        } else {
            return redirect('admin/vendor')->with('success', 'Customer Deleted Successfully');
        }
    }


    public function detail_rent($id)
    {
        $data['title'] = 'Vendor Management | Vendor Rent Detail';
        $data['vendor'] = Vendor::where('id', $id)->first();
        $data['rentLiability'] = RentLiability::with('liabilityBranch', 'liabilityFile')->with(['employee_rent' => function ($query) {
            $query->select('id', 'designation_id', 'employee_code', 'employee_name', 'mobile_no', 'employee_date');
        }])->where('id', $id)->first();

        $actual_transfer_amnt = RentPayment::where('rent_liability_id', $id)->where('is_deleted', 0)->sum('actual_transfer_amount');
        $transfer_amnt = RentPayment::where('rent_liability_id', $id)->where('is_deleted', 0)->sum('transferred_amount');
        $data['outstanding'] = number_format((float)$actual_transfer_amnt - $transfer_amnt, 2, '.', '');
        $advance_amt_dr =  AdvancedTransaction::where('is_deleted', 0)->where('type', 3)->where('type_id', $id)->where('payment_type', "DR")->sum('amount');
        $advance_amt_cr =  AdvancedTransaction::where('is_deleted', 0)->where('type', 3)->where('type_id', $id)->where('payment_type', "CR")->sum('amount');
        $total_advanced_amt = $advance_amt_dr - $advance_amt_cr;
        $advance_amt = $total_advanced_amt;
        $data['advance_amt'] = number_format((float)$advance_amt, 2, '.', '');
        return view('templates.admin.vendor_management.vendor.rent_detail', $data);
    }

    public function detail_salary($id)
    {
        $data['title'] = 'Vendor Management | Vendor Employee Detail';
        $empID = $id;

        $data['employee'] = Employee::where('id', $empID)->with('company')->first();

        if ($data['employee']->is_resigned == 1) {

            $data['app'] = \App\Models\EmployeeApplication::where('employee_id', $empID)->where('application_type', 2)->orderby('id', 'DESC')->get();
        }

        if ($data['employee']->is_terminate == 1) {

            $data['terminate'] = \App\Models\EmployeeTerminate::where('employee_id', $empID)->get();
        }

        if ($data['employee']->is_transfer == 1) {

            $data['transfer'] = \App\Models\EmployeeTransfer::with(['transferBranch' => function ($query) {
                $query->select('id', 'name');
            }])->with(['transferBranchOld' => function ($query) {
                $query->select('id', 'name');
            }])->with(['transferEmployee' => function ($query) {
                $query->select('*');
            }])->where('employee_id', $empID)->orderby('id', 'DESC')->get();
        }
        $actual_transfer_amnt = EmployeeSalary::where('employee_id', $id)->where('is_deleted', 0)->sum('actual_transfer_amount');
        $transfer_amnt = EmployeeSalary::where('employee_id', $id)->where('is_deleted', 0)->sum('transferred_salary');
        $data['outstanding'] = number_format((float)$actual_transfer_amnt - $transfer_amnt, 2, '.', '');

        $advance_amt_dr =  AdvancedTransaction::where('is_deleted', 0)->where('type', 4)->where('type_id', $id)->where('payment_type', "DR")->sum('amount');
        $advance_amt_cr =  AdvancedTransaction::where('is_deleted', 0)->where('type', 4)->where('type_id', $id)->where('payment_type', "CR")->sum('amount');
        $total_advanced_amt = $advance_amt_dr - $advance_amt_cr;
        $advance_amt = $total_advanced_amt;
        $data['advance_amt'] = number_format((float)$advance_amt, 2, '.', '');

        return view('templates.admin.vendor_management.vendor.salary_detail', $data);
    }

    public function vendor_transaction(Request $request)
    {
         
        if ($request->vendor_type == 0) {
            $data = VendorTransaction::with('branch_detail:id,name,branch_code', 'bill_detail')->where('vendor_id', $request->vendor_id)->where('is_deleted', '!=', 1)->where('type', '!=' ,5)->where('sub_type', '!=' , 51);
        } elseif ($request->vendor_type == 1) {
            $data = \App\Models\CustomerTransaction::with('branch_detail', 'bill_detail')->where('customer_id', $request->vendor_id)->where('is_deleted', 0);
        } elseif ($request->vendor_type == 3) {
            $data = \App\Models\EmployeeLedger::with('branch_detail')->where('employee_id', $request->vendor_id)->where('is_deleted', 0);
        } elseif ($request->vendor_type == 2) {
            $data = \App\Models\RentLiabilityLedger::where('rent_liability_id', $request->vendor_id)->where('is_deleted', 0);
        } elseif ($request->vendor_type == 4) {
            $data = AssociateTransaction::where('associate_id', $request->vendor_id)->where('is_deleted', 0);
        }
        $data1 = $data->orderby('created_at', 'ASC')->get();
        $count = count($data1);
        $data = $data->orderby('created_at', 'ASC')->offset($_POST['start'])->limit($_POST['length'])->get();
        $totalCount = $count;
        $sno = $_POST['start'];
        $rowReturn = array();
        if ($_POST['pages'] == 1) {
            $totalAmountssssss  = 0;
        } else {
            $totalAmountssssss  = $_POST['total'];
        }

        if ($request->vendor_type == 0) {
            $dataCR = VendorTransaction::with('branch_detail:id,name,branch_code', 'bill_detail')->where('vendor_id', $request->vendor_id)->where('is_deleted', 0)->where('type', '!=' ,5)->where('sub_type', '!=' , 51);
            $accountheadId = 140;
        } elseif ($request->vendor_type == 1) {
            $dataCR = \App\Models\CustomerTransaction::where('customer_id', $request->vendor_id)->where('is_deleted', 0);
            $accountheadId = 142;
        } elseif ($request->vendor_type == 3) {
            $dataCR = \App\Models\EmployeeLedger::where('type', '!=', 2)->where('employee_id', $request->vendor_id)->where('is_deleted', 0);
            $accountheadId = 61;
        } elseif ($request->vendor_type == 2) {
            $dataCR = \App\Models\RentLiabilityLedger::where('rent_liability_id', $request->vendor_id)->where('is_deleted', 0);
            $accountheadId = 60;
        } elseif ($request->vendor_type == 4) {
            $dataCR = AssociateTransaction::where('type_id', $request->vendor_id)->where('is_deleted', 0);
            $accountheadId = 141;
        }
        $accounthead = AccountHeads::where('head_id', $accountheadId)->first();
        if ($_POST['pages'] == "1") {
            $length = ($_POST['pages']) * $_POST['length'];
        } else {
            $length = ($_POST['pages'] - 1) * $_POST['length'];
        }

        $dataCR = $dataCR->offset(0)->limit($length)->get();

        // if($request->vendor_type ==2 || $request->vendor_type ==3)
        //  {
        //      $totalCR = $dataCR->where('payment_type','CR')->sum('deposit');
        //      $totalDR = $dataCR->where('payment_type','DR')->sum('withdrawal');
        //      $totalAmountssssss = $totalCR - $totalDR;
        //  }
        //  else{
        //      $totalDR = $dataCR->where('payment_type','DR')->sum('amount');
        //      $totalCR = $dataCR->where('payment_type','CR')->sum('amount');
        //      $totalAmountssssss = $totalCR - $totalDR;
        //  }



        if ($_POST['pages'] == "1") {
            $totalAmountssssss = 0;
        }

        foreach ($data as $key => $row) {
            $val['DT_RowIndex'] =  $sno + 1;
            $val['date'] =  date("d/m/Y", strtotime($row->created_at));
            if (isset($row['bill_detail']->bill_number)) {
                $bill =  $row['bill_detail']->bill_number;
            } else {
                $bill = 'N/A';
            }
            $val['bill_number'] = $bill;
            if (isset($row['branch_detail']->name)) {
                $branch = $row['branch_detail']->name ." (".$row['branch_detail']->branch_code . ")";
            } else {
                $branch = 'N/A';
            }
            $val['branch_name'] = $branch;
            if (isset($row['branch_detail']->branch_code)) {
                $branch_code = $row['branch_detail']->branch_code;
            } else {
                $branch_code = 'N/A';
            }
            $val['branch_code'] = $branch_code;
            if (isset($row->description)) {
                $description = $row->description;
            } else {
                $description = 'N/A';
            }
            $val['particular'] = $description;

            if ($row->payment_type == 'DR') {
                if ($request->vendor_type == 2 ||  $request->vendor_type == 3) {
                    $dr = number_format((float)$row->withdrawal, 2, '.', '');
                } else {
                    $dr = number_format((float)$row->amount, 2, '.', '');
                }
            } else {
                $dr = '0';
            }

            $val['dr'] = $dr;
            if ($row->payment_type == 'CR') {
                if ($request->vendor_type == 2 ||  $request->vendor_type == 3) {
                    $cr = number_format((float)$row->deposit, 2, '.', '');
                } else {
                    $cr = number_format((float) $row->amount, 2, '.', '');
                }
            } else {
                $cr = '0';
            }
            $val['cr'] = $cr;
            if ($request->vendor_type == 0 && $row->type != 5) {
                if ($accounthead->cr_nature == 1) {
                    $total = (float)$cr  - (float)$dr;
                } else {
                    $total = (float)$dr - (float)$cr;
                }
                $totalAmountssssss = $totalAmountssssss + $total;
            }
            if ($request->vendor_type == 0 && $row->type != 5) {
                $val['balance'] = number_format((float)$totalAmountssssss, 2, '.', '');
            } else {
                $val['balance'] = '';
            }
            if ($row->payment_type == 'CR') {
                $payment = 'Credit';
            } else {
                $payment = 'Debit';
            }
            $val['payment_type'] = $payment;
            $payment_mode = '';
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

                if ($row->payment_type == 'CR') {
                    $payment_mode = 'JV';
                } else {
                    $payment_mode = 'JV/SSB';
                }
            }


            $val['payment_mode'] = $payment_mode;
            $bank_detail = getSamraddhBank($row->bank_id);
            $bank_detail1 = 'N/A';
            if ($bank_detail) {
                $bank_detail1 = $bank_detail->bank_name;
            }
            $val['bank_detail'] = $bank_detail1;


            $rowReturn[] = $val;
        }
        $output = array("draw" => $_POST['draw'], "recordsTotal" => $totalCount, "recordsFiltered" => $totalCount, "data" => $rowReturn, 'total' => $totalAmountssssss);

        return json_encode($output);
    }

    // Associate Lsiting

    public function associateListing(Request $request)
    {
        if ($request->ajax()) {
            // fillter array 
            $arrFormData = array();
            if (!empty($_POST['searchform'])) {
                foreach ($_POST['searchform'] as $frm_data) {
                    $arrFormData[$frm_data['name']] = $frm_data['value'];
                }
            }
            $data = Member::with('associate_branch')->where('member_id', '!=', '9999999')->where('is_associate', 1);

            /******* fillter query start ****/

            if (isset($arrFormData['is_search']) && $arrFormData['is_search'] == 'yes') {
                if ($arrFormData['status'] != '') {
                    $status = $arrFormData['status'];

                    if ($status == 1) {
                        $data = $data->where('status', 1);
                    }
                    if ($status == 0) {
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

            $data1 = $data->orderby('associate_join_date', 'DESC')->get();
            $totalCount = count($data1);
            $data = $data->orderby('associate_join_date', 'DESC')->offset($_POST['start'])->limit($_POST['length'])->get();
            $count = count($data);
            $sno = $_POST['start'];
            $rowReturn = array();

            foreach ($data as $key => $row) {
                $sno++;

                $val['DT_RowIndex'] = $sno;

                $val['join_date'] = date("d/m/Y", strtotime($row->associate_join_date));

                $val['branch'] = $row['associate_branch']->name;



                $val['branch_code'] = $row['associate_branch']->branch_code;

                $val['sector'] = $row['associate_branch']->sector;

                $val['region'] = $row['associate_branch']->regan;

                $val['zone'] = $row['associate_branch']->zone;
                $val['dob'] = date('d/m/Y', strtotime($row->dob));


                $val['m_id'] = $row->member_id;

                $val['member_id'] = $row->associate_no;

                $val['name'] = $row->first_name . ' ' . $row->last_name;

                $val['dob'] = date('d/m/Y', strtotime($row->dob));


                $val['mobile_no'] = $row->mobile_no;

                $val['associate_code'] = $row->associate_senior_code;

                $val['associate_name'] = getSeniorData($row->associate_senior_id, 'first_name') . ' ' . getSeniorData($row->associate_senior_id, 'last_name');

                if ($row->is_block == 1) {

                    $status = 'Blocked';
                } else {

                    if ($row->associate_status == 1) {

                        $status = 'Active';
                    } else {

                        $status = 'Inactive';
                    }
                }

                $val['status'] = $status;
                $btn = '<div class="list-icons"><div class="dropdown"><a href="#" class="list-icons-item" data-toggle="dropdown"><i class="icon-menu9"></i></a><div class="dropdown-menu dropdown-menu-right">';


                $url2 = URL::to("admin/associate-edit/" . $row->id);
                $url4 = URL::to("admin/hr/associate/detail/" . $row->id);
                $url = URL::to("admin/vendor/associate_detail/" . $row->id);

                $btn .= '<a class="dropdown-item" href="' . $url . '" title="Employee Detail"><i class="icon-eye8  mr-2"></i>Associate Detail</a>';
                $btn .= '<a class="dropdown-item" href="' . $url2 . '" title="Employee Edit"><i class="icon-pencil7  mr-2"></i>Associate Edit</a>';

                $btn .= '</div></div></div>';
                $val['action'] = $btn;
                $rowReturn[] = $val;
            }
            $output = array("draw" => $_POST['draw'], "recordsTotal" => $totalCount, "recordsFiltered" => $count, "data" => $rowReturn,);



            return json_encode($output);
        }
    }

    public function associate_detail($id)
    {
        $data['memberDetail'] = Member::where('id', $id)->first();
        $data['title'] = 'Associate Detail';
        $amnt = CommissionLeaserDetail::where('member_id', $id)->where('is_deleted', 0)->sum('amount');
        $fuelamnt = CommissionLeaserDetail::where('member_id', $id)->where('is_deleted', 0)->sum('fuel');
        $trans_fuelamnt = CommissionLeaserDetail::where('member_id', $id)->where('is_deleted', 0)->sum('transferred_fuel_amount');
        $trans_amnt = CommissionLeaserDetail::where('member_id', $id)->where('is_deleted', 0)->sum('transferred_amount');
        $realAmnt = $amnt + $fuelamnt;
        $transferAmnt = $trans_fuelamnt + $trans_amnt;
        $outstanding = $realAmnt - $transferAmnt;
        $data['outstanding'] = number_format((float)$outstanding, 2, '.', '');

        $advance_amt_dr =  AdvancedTransaction::where('is_deleted', 0)->where('type', 5)->where('type_id', $id)->where('payment_type', "DR")->sum('amount');
        $advance_amt_cr =  AdvancedTransaction::where('is_deleted', 0)->where('type', 5)->where('type_id', $id)->where('payment_type', "CR")->sum('amount');
        $total_advanced_amt = $advance_amt_dr - $advance_amt_cr;
        $advance_amt = $total_advanced_amt;
        $data['advance_amt'] = number_format((float)$advance_amt, 2, '.', '');

        return view('templates.admin.vendor_management.vendor.associate_detail', $data);
    }


    public function credit_node_transaction()
    {
        if (check_my_permission(Auth::user()->id, "229") != "1") {
            return redirect()->route('admin.dashboard');
        }

        $id = '';
        if ($_GET['id']) {
            $id = $_GET['id'];
        }
        $data['id'] = $id;
        $data['title'] = 'Vendor Management | Credit Note Transaction';

        return view("templates.admin.vendor_management.vendor.credit_node_transaction", $data);
    }

    public function credit_node_transaction_list(Request $request)
    {
        if ($request->ajax()) {
            $id = $request->id;
            if ($id) {
                $data  = VendorCreditNode::where('vendor_id', $id)->where('is_deleted', 0);
            }
            // else{
            //      $data  = VendorCreditNode::where('vendor_id',$id);
            // }


            $data = $data->orderby('created_at', 'desc')->get();

            $count = count($data);
            $totalCount = $data->count();
            $sno = $_POST['start'];
            $rowReturn = array();

            foreach ($data as $key => $value) {
                $sno++;

                $val['DT_RowIndex'] = $sno;
                $val['date'] = date('d/m/Y', strtotime($value->credit_node_date));
                $val['credit_node'] = $value->credit_node;
                $val['order_no'] = $value->order_no;
                $val['balance'] = number_format((float)$value->total_amount - $value->used_amount, 2, '.', '');
                $val['amount'] = number_format((float)$value->total_amount, 2, '.', '');
                if ($value->status == '0') {
                    $status = 'Open';
                } elseif ($value->status == '1') {
                    $status = 'Closed';
                } else {
                    $status = 'N/A';
                }
                $val['status'] = $status;

                $rowReturn[] = $val;
            }
            $output = array("draw" => $_POST['draw'], "recordsTotal" => $totalCount, "recordsFiltered" => $count, "data" => $rowReturn,);


            return json_encode($output);
        }
    }

    public function advance_transaction()
    {
        if (check_my_permission(Auth::user()->id, "230") != "1") {
            return redirect()->route('admin.dashboard');
        }

        $id = '';
        $type = '';
        if ($_GET['id']) {
            $id = $_GET['id'];
        }

        if ($_GET['type']) {
            $type = $_GET['type'];
        }
        if ($type == 0) {
            $type = 3;
        } elseif ($type == 1) {
            $type = 4;
        } elseif ($type == 3) {
            $type = 0;
        } elseif ($type == 4) {
            $type = 1;
        }

        $data['id'] = $id;
        $data['title'] = 'Vendor Management | Advance Transaction';
        $data['type'] = $type;
        return view("templates.admin.vendor_management.vendor.advance_transaction", $data);
    }

    public function advance_transaction_list(Request $request)
    {
        
        if ($request->ajax()) {
            $id = $request->id;
            

            if ($id) {


                if ($request->type == 4) {

                    $data  = \App\Models\BankingLedger::whereIN('vendor_type', [$request->type, 5])->where('vendor_type_id', $id);
                } else {

                    // $data  = \App\Models\BankingLedger::where('vendor_type', $request->type)->where('vendor_type_id', $id)->where('advanced_amount', '>', 0);
                    $data =  AdvancedTransaction::where('is_deleted', 0)->where('type', 1)->where('type_id', $id)->where('total_amount', '>', 0);
                }
            }

            $data = $data->orderby('created_at', 'desc')->get();
            $count = count($data);
            $totalCount = $data->count();
            $sno = $_POST['start'];
            $rowReturn = array();

            foreach ($data as $key => $value) {
                $sno++;

                $val['DT_RowIndex'] = $sno;
                $val['date'] = date('d/m/Y', strtotime($value->created_at));
                $val['bill_no'] = $value->bill_id;
                if ($value->payment_type == 'CR') {
                    $mode = 'Credit';
                } else {
                    $mode = 'Debit';
                }
                $val['mode'] = $mode;
                $val['t_amnt'] = number_format((float)$value->total_amount, 2, '.', '');
                if ($value->type == 4 || $value->type == 5) {
                    $val['used_amnt'] = number_format((float)$value->used_amount  , 2, '.', '');
                } else {
                    $val['used_amnt'] = number_format((float)$value->used_amount , 2, '.', '');
                }
                if ($value->type == 4) {
                    $val['balance'] = number_format((float)$value->amount  , 2, '.', '');
                } else {
                    $val['balance'] = number_format((float)$value->amount  , 2, '.', '');
                }


                $rowReturn[] = $val;
            }
            $output = array("draw" => $_POST['draw'], "recordsTotal" => $totalCount, "recordsFiltered" => $count, "data" => $rowReturn,);


            return json_encode($output);
        }
    }



    public function jv_transaction()
    {
        if (check_my_permission(Auth::user()->id, "231") != "1") {
            return redirect()->route('admin.dashboard');
        }

        $id = '';
        if ($_GET['id']) {
            $id = $_GET['id'];
        }
        $data['id'] = $id;
        $data['title'] = 'Advance Transaction';

        return view("templates.admin.vendor_management.vendor.advance_transaction", $data);
    }

    public function jv_transaction_list(Request $request)
    {
        if ($request->ajax()) {
            $id = $request->id;
            if ($id) {
                $data  = AdvancedTransaction::where('type_id', $id)->where('is_deleted', 0);
            }
            // else{
            //      $data  = VendorCreditNode::where('vendor_id',$id);
            // }


            $data = $data->orderby('created_at', 'desc')->get();

            $count = count($data);
            $totalCount = $data->count();
            $sno = $_POST['start'];
            $rowReturn = array();

            foreach ($data as $key => $value) {
                $sno++;

                $val['DT_RowIndex'] = $sno;
                $val['date'] = date('d/m/Y', strtotime($value->entry_date));
                $val['bill_no'] = $value->bill_id;
                if ($value->payment_type == 'CR') {
                    $mode = 'Credit';
                } else {
                    $mode = 'Debit';
                }
                $val['mode'] = $mode;
                $val['t_amnt'] = number_format((float)$value->total_amount, 2, '.', '');
                $val['used_amnt'] = number_format((float)$value->used_amount, 2, '.', '');

                $val['balance'] = number_format((float)$value->total_amount - $value->used_amount, 2, '.', '');

                $rowReturn[] = $val;
            }
            $output = array("draw" => $_POST['draw'], "recordsTotal" => $totalCount, "recordsFiltered" => $count, "data" => $rowReturn,);


            return json_encode($output);
        }
    }

    /**
     * Show Vendor transaction log   .
     * Route: admin/vendor/log 
     * Method: get 
     * @return  array()  Response
     */
    public function transactionLog($id)
    {
        if (check_my_permission(Auth::user()->id, "232") != "1") {
            return redirect()->route('admin.dashboard');
        }

        $data['log'] = VendorLog::where('vendor_id', $id)->where('is_deleted', '!=', 1)->orderby('created_at', 'DESC')->get();
        $data['title'] = 'Vendor Management | Vendors Transaction Logs';

        return view('templates.admin.vendor_management.vendor.log', $data);
    }
}