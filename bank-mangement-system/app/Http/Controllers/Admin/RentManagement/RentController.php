<?php

namespace App\Http\Controllers\Admin\RentManagement;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Request as Request1;
use App\Models\Files;
use App\Models\RentLiability;
use App\Models\Branch;
use App\Models\AccountHeads;
use App\Models\SubAccountHeads;
use App\Models\SavingAccount;
use App\Models\AdvancedTransaction;
use App\Models\Companies;
use App\Models\SalaryRentLog;
use App\Models\SavingAccountTranscation;
use App\Models\TransactionReferences;
use Carbon\Carbon;
use Session;
use DB;
use URL;
use Illuminate\Support\Facades\Cache;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Admin\CommanController;
use App\Models\VendorBillPayment;
use App\Services\ImageUpload;



class RentController extends Controller
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
     * Display a listing of the account heads.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (check_my_permission(Auth::user()->id, "79") != "1") {
            return redirect()->route('admin.dashboard');
        }

        $data['title'] = 'Rent Management | Rent Liabilities Listing';
        $data['branches'] = Branch::select('id', 'name', 'branch_code', 'state_id')->where('status', 1)->get();
        $data['accountHeadLibilities'] = AccountHeads::select('id', 'head_id', 'sub_head')->where('parent_id', 53)->get();
        return view('templates.admin.rent-management.rent-liability-listing', $data);
    }
    /**
     * Display the specified resource.
     *
     * @param  \App\Reservation  $reservation
     * @return \Illuminate\Http\Response
     */
    public function rentLiabilitiesListing(Request $request)
    {
        if ($request->ajax() && check_my_permission(Auth::user()->id, "79") == "1") {
            $arrFormData = array();
            if (!empty($_POST['searchform'])) {
                foreach ($_POST['searchform'] as $frm_data) {
                    $arrFormData[$frm_data['name']] = $frm_data['value'];
                }
            }

            if (isset($arrFormData['is_search']) && $arrFormData['is_search'] == 'yes') {

                $data = RentLiability::select('id', 'branch_id', 'rent_agreement_file_id', 'employee_id', 'rent_type', 'owner_ssb_id', 'place', 'owner_name', 'owner_mobile_number', 'owner_pen_number', 'owner_aadhar_number', 'owner_bank_name', 'owner_bank_account_number', 'owner_bank_ifsc_code', 'security_amount', 'rent', 'yearly_increment', 'office_area', 'advance_payment', 'current_balance', 'created_at', 'status', 'agreement_from', 'agreement_to', 'company_id')
                ->with(['AcountHeadCustom' => function ($q) {
                    $q->select('id', 'head_id', 'parent_id', 'sub_head');
                }, 'SsbAccountNumberCustom' => function ($q) {
                    $q->select('id', 'account_no', 'member_id');
                }, 'rentFileCustom' => function ($q) {
                    $q->select('id', 'file_name');
                }, 'liabilityBranch' => function ($query) {
                    $query->select('id', 'name', 'branch_code', 'sector', 'regan', 'zone');
                }])->with(['employee_rent' => function ($query) {
                    $query->select('id', 'designation_id', 'employee_code', 'employee_name', 'mobile_no')->with(['designation' => function ($q) {
                        $q->select('id', 'designation_name');
                    }]);
                }])->has('company')->with('company:id,name');

                if (Auth::user()->branch_id > 0) {
                    $id = Auth::user()->branch_id;
                    $data = $data->where('branch_id', '=', $id);
                }

                if (isset($arrFormData['branch_id']) && $arrFormData['branch_id'] > 0) {
                    $branchId = $arrFormData['branch_id'];
                    $data = $data->where('branch_id', '=', $branchId);
                }
                if (isset($arrFormData['company_id']) && $arrFormData['company_id'] > 0) {
                    $company_id = $arrFormData['company_id'];
                    $data = $data->where('company_id', '=', $company_id);
                }
                if ($arrFormData['rent_type'] != '') {
                    $rent_type = $arrFormData['rent_type'];
                    $data = $data->where('rent_type', '=', $rent_type);
                }

                $data1 = $data->count('id');
                $count = $data1;
                $data = $data->orderby('created_at', 'DESC')->offset($_POST['start'])->limit($_POST['length'])->get();

                $totalCount =  $count;
                $sno = $_POST['start'];
                $rowReturn = array();

                foreach ($data as $row) {
                    $sno++;
                    $val['DT_RowIndex'] = $sno;
                    $val['branch'] = $row['liabilityBranch']->name . " (" . $row['liabilityBranch']->branch_code . ")";
                    $val['company'] = $row['company']->name;
                    if ($row['AcountHeadCustom']) {
                        $val['rent_type'] = $row['AcountHeadCustom']->sub_head; //getAcountHead($row->rent_type);
                    } else {
                        $val['rent_type'] = 'N/A';
                    }

                    $val['period_from'] = date("d/m/Y", strtotime(convertDate($row->agreement_from)));
                    $val['period_to'] = date("d/m/Y", strtotime(convertDate($row->agreement_to)));
                    $val['address'] = $row->place;
                    $val['owner_name'] = $row->owner_name;
                    $val['owner_mobile_number'] = $row->owner_mobile_number;
                    $val['owner_pen_card'] = $row->owner_pen_number;
                    $val['owner_aadhar_card'] = $row->owner_aadhar_number;
                    $owner_ssb_account = 'N/A';
                    if ($row->owner_ssb_id != '') {
                        $owner_ssb_account = $row['SsbAccountNumberCustom']->account_no; //getSsbAccountNumber($row->owner_ssb_id)->account_no;
                    }
                    $val['owner_ssb_account'] = $owner_ssb_account;
                    $val['bank_name'] = $row->owner_bank_name;
                    $val['bank_account_number'] = $row->owner_bank_account_number;
                    $val['ifsc_code'] = $row->owner_bank_ifsc_code;
                    $val['security_amount'] = number_format((float)$row->security_amount, 2, '.', '');
                    $val['rent'] = number_format((float)$row->rent, 2, '.', '');
                    $val['yearly_increment'] = number_format((float)$row->yearly_increment, 2, '.', '') . '%';

                    $val['office_area'] = $row->office_area;
                    $val['advance_payment'] = $row->advance_payment;
                    $val['current_balance'] = $row->current_balance;
                    $val['employee_code'] = $row['employee_rent']->employee_code;
                    $val['employee_name'] = $row['employee_rent']->employee_name;
                    $val['employee_designation'] = $row['employee_rent']['designation']->designation_name; //getDesignationData('designation_name',$row['employee_rent']->designation_id)->designation_name;
                    $val['mobile_number'] = $row['employee_rent']->mobile_no;
                    $rent_agreement = $row['rentFileCustom']; //getFileData($row->rent_agreement_file_id);
                    $aggerment = "";
                    if ($rent_agreement) {
                        //foreach ($rent_agreement as $key => $value) {
                            $folderName = 'rent-liabilities/'.$rent_agreement->file_name;
                            $url  = ImageUpload::generatePreSignedUrl($folderName);
                        $aggerment = '<a href="'.$url.'" target="blank">' . $rent_agreement->file_name . '</a>';
                        //} 
                    } else {
                        $aggerment = 'N/A';
                    }

                    $val['rent_agreement'] = $aggerment;
                    if ($row->status == 0) {
                        $agreement_status = 'Active';
                    } else {
                        $agreement_status = 'Deactive';
                    }
                    $val['agreement_status'] = $agreement_status;
                    $val['created_at'] = date("d/m/Y", strtotime($row->created_at));
                    $url = URL::to("admin/rent/edit-rent-liability/" . $row->id . "");
                    $update15G = URL::to("admin/rent/form_g/" . $row->id . "");
                    $statusUrl = URL::to("admin/rent/updatestatus/" . $row->id . "");
                    $libLedger = URL::to("admin/rent/ledger-liability/" . $row->id . "");
                    $btn = '<div class="list-icons"><div class="dropdown"><a href="#" class="list-icons-item" data-toggle="dropdown"><i class="icon-menu9"></i></a><div class="dropdown-menu dropdown-menu-right">';
                    $btn .= '<a class="dropdown-item" href="' . $url . '"><i class="icon-pencil7 mr-2"></i>Edit</a>';
                    // $btn .= '<a class="dropdown-item" href="' . $update15G . '"><i class="fa fa-plus-square  mr-2"></i>Update 15G</a>';
                    if ($row->status == 0) {
                        $btn .= '<a class="dropdown-item" href="' . $statusUrl . '"><i class="icon-cross3 mr-2"></i>Deactive</a>';
                    } else {
                        $btn .= '<a class="dropdown-item" href="' . $statusUrl . '"><i class="icon-checkmark4 mr-2"></i>Active</a>';
                    }
                    $btn .= '<a class="dropdown-item" href="' . $libLedger . '" target="blank"><i class="icon-list mr-2"></i>Transactions</a>';
                    $btn .= '</div></div></div>';
                    $val['action'] = $btn;
                    $rowReturn[] = $val;
                }
                $output = array("draw" => $_POST['draw'], "recordsTotal" => $totalCount, "recordsFiltered" => $count, "data" => $rowReturn);
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
     * Add Rent Liability View.
     * Route: /member/passbook
     * Method: get 
     * @return  array()  Response
     */
    public function addLiability()
    {
        if (check_my_permission(Auth::user()->id, "78") != "1") {
            return redirect()->route('admin.dashboard');
        }

        $data['title'] = 'Rent Management | Add Rent Liability';
        if (Auth::user()->branch_id > 0) {
            $id = Auth::user()->branch_id;
            $data['branches'] = Branch::select('id', 'name', 'branch_code', 'state_id')->where('status', 1)->where('id', $id)->get();
        } else {
            $data['branches'] = Branch::select('id', 'name', 'branch_code', 'state_id')->where('status', 1)->get();
        }
        $data['accountHeadLibilities'] = AccountHeads::select('id', 'head_id', 'sub_head', 'parent_id')->where('parent_id', 53)->get();
        //$data['accountHeadLibilities'] = array('');
        return view('templates.admin.rent-management.addliability', $data);
    }
    /**
     * Get rent libility type.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return JSON
     */
    public function rentLiabilitiyType(Request $request)
    {
        $libilityId = $request->libilityId;
        $libilityTypes = SubAccountHeads::where('account_head_id', $libilityId)->get();
        $resCount = count($libilityTypes);
        $return_array = compact('libilityTypes', 'resCount');
        return json_encode($return_array);
    }
    /**
     * Save Rent Liability.
     * Route: /save-account-head
     * Method: get 
     * @return  array()  Response
     */
    public function saveLiability(Request $request)
    {
        // dd($request->All());
        $rules = [
            'branch' => 'required',
            'rentType' => 'required',
            'agreement_from' => 'required',
            'agreement_to' => 'required',
            //'date' => 'required',
        ];
        $customMessages = [
            'required' => 'The :attribute field is required.'
        ];
        $this->validate($request, $rules, $customMessages);
        $globaldate = $request->created_at;
        $select_date = $request->select_date;
        $current_date = date("Y-m-d", strtotime(convertDate($globaldate)));
        $entry_date = date("Y-m-d", strtotime(convertDate($select_date)));
        $entry_time = date("H:i:s", strtotime(convertDate($globaldate)));
        $date_create = $entry_date . ' ' . $entry_time;
        $created_at = date("Y-m-d H:i:s", strtotime(convertDate($date_create)));
        $updated_at = date("Y-m-d H:i:s", strtotime(convertDate($date_create)));
        // $mainFolder = storage_path() . '/images/rent-liabilities';
         $mainFolder = 'rent-liabilities/';
        if ($request->file('rent_agreement')) {

            $file = $request->file('rent_agreement');
            $uploadFile = $file->getClientOriginalName();
            $filename = pathinfo($uploadFile, PATHINFO_FILENAME);
            $fname = $filename . '_' . time() . '.' . $file->getClientOriginalExtension();
            // $file->move($mainFolder, $fname);
            ImageUpload::upload($file, $mainFolder,$fname);
            $fData = [
                'file_name' => $fname,
                'file_path' => $mainFolder,
                'file_extension' => $file->getClientOriginalExtension(),
            ];
            $res = Files::create($fData);
            $file_id = $res->id;
        } else {
            $file_id = '';
        }

        $rentLiability = new RentLiability();
        $rentLiability->branch_id = $request->branch;
        $rentLiability->company_id = $request->company_id;
        $rentLiability->rent_type = $request->rentType;
        //$rentLiability->date = date("Y-m-d", strtotime( $request->date));
        $rentLiability->agreement_from = date("Y-m-d", strtotime(convertDate($request->agreement_from)));
        $rentLiability->agreement_to = date("Y-m-d", strtotime(convertDate($request->agreement_to)));
        $rentLiability->place = $request->place;
        $rentLiability->owner_name = $request->owner_name;
        $rentLiability->owner_mobile_number = $request->owner_mobile_number;
        $rentLiability->owner_pen_number = $request->owner_pen_card;
        $rentLiability->owner_aadhar_number = $request->owner_aadhar_card;
        $rentLiability->owner_ssb_number = $request->owner_ssb_account;
        $rentLiability->owner_bank_name = $request->bank_name;
        $rentLiability->owner_bank_account_number = $request->bank_account_number;
        $rentLiability->owner_bank_ifsc_code = $request->ifsc_code;
        $rentLiability->security_amount = $request->security_amount;
        $rentLiability->rent = $request->rent;
        $rentLiability->yearly_increment = $request->yearly_increment;
        $rentLiability->office_area = $request->office_area;
        $rentLiability->employee_code = $request->employee_code;
        $rentLiability->authorized_employee_name = $request->employee_name;
        $rentLiability->authorized_employee_designation = $request->employee_designation;
        $rentLiability->mobile_number = $request->mobile_number;
        $rentLiability->rent_agreement_file_id = $file_id;
        $rentLiability->created_at = $created_at;
        $rentLiability->owner_ssb_id = $request->owner_ssb_id;
        $rentLiability->dob = date("Y-m-d", strtotime(convertDate($request->dob)));
        $rentLiability->age = $request->age;
        $rentLiability->employee_id = $request->employee_id;
        $data = $rentLiability;
        $rentLiabilitys = $rentLiability->save();

        $rentLiabilitys_id = DB::getPdo()->lastInsertId();
        $encodeDate = json_encode($data);

        if ($rentLiabilitys) {
            return redirect()->route('admin.rent.liabilities')->with('success', 'Rent Liability Added Successfully!');
        } else {
            return back()->with('alert', 'Problem With Creating New User');
        }
    }
    /**
     * Edit user View.
     * Route: /member/passbook
     * Method: get sss
     * @return  array()  Response
     */
    public function editLiability($id)
    {
        $data['title'] = 'Rent Management | Edit Rent Liability';
        // $data['branches'] = Branch::where('status', 1)->get();
        $data['rentLiability'] = RentLiability::with('liabilityBranch', 'liabilityFile')->with(['employee_rent' => function ($query) {
            $query->select('id', 'designation_id', 'employee_code', 'employee_name', 'mobile_no', 'employee_date', 'created_at');
        }])->where('id', $id)->first();
        $data['companyDate'] = Companies::where('id',$data['rentLiability']['company_id'])->first('created_at')->created_at;
        $data['companyDate'] = date('d/m/Y', strtotime(convertDate($data['companyDate'])));
        $data['libilityTypes'] = AccountHeads::where('parent_id', 53)->get();
        return view('templates.admin.rent-management.editliability', $data);
    }
    /**
     * User View.
     * Route: /member/passbook
     * Method: get sss
     * @return  array()  Response
     */
    public function viewUser($id)
    {
        $data['title'] = 'User Details';
        $data['user'] = User::with('userEmployee')->where('id', $id)->where('role_id', 5)->first();
        $data['employee'] = $data['user']['userEmployee'];
        $data['id'] = $id;
        $arr = Permission::select('id', 'name', 'parent_id')->get()->toArray();
        $new = array();
        foreach ($arr as $a) {
            $new[$a['parent_id']][] = $a;
        }
        $data['permissions'] = $this->createTree($new, $new[0]);
        $data['userPermissions'] = User::find($id)->getPermissionNames()->toArray();
        return view('templates.admin.user.user-details', $data);
    }
    /**
     * Update the specified accounthead.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function updateLiability(Request $request)
    {
        $rules = [
            'branch' => 'required',
            'rentType' => 'required',
            'agreement_from' => 'required',
            'agreement_to' => 'required',
            //'date' => 'required',
        ];
        $customMessages = [
            'required' => 'The :attribute field is required.'
        ];
        $this->validate($request, $rules, $customMessages);
        $globaldate = $request->created_at;
        $select_date = $request->select_date;
        $current_date = date("Y-m-d", strtotime(convertDate($globaldate)));
        $entry_date = date("Y-m-d", strtotime(convertDate($select_date)));
        $entry_time = date("H:i:s", strtotime(convertDate($globaldate)));
        $date_create = $entry_date . ' ' . $entry_time;
        $created_at = date("Y-m-d H:i:s", strtotime(convertDate($date_create)));
        $updated_at = date("Y-m-d H:i:s", strtotime(convertDate($date_create)));
        $idFile = $request->file('rent_agreement');
        $rentId = $request['rent_id'];

        // $mainFolder = storage_path() . '/images/rent-liabilities';
        $mainFolder =  '/rent-liabilities';

        if ($idFile && $request['hidden_file_id'] == '') {
            $file = $request->file('rent_agreement');
            $uploadFile = $file->getClientOriginalName();
            $filename = pathinfo($uploadFile, PATHINFO_FILENAME);
            $fname = $filename . '_' . time() . '.' . $file->getClientOriginalExtension();
            ImageUpload::upload($file, $mainFolder,$fname);
            // $file->move($mainFolder, $fname);
            $fData = [
                'file_name' => $fname,
                'file_path' => $mainFolder,
                'file_extension' => $file->getClientOriginalExtension(),
            ];
            $res = Files::create($fData);
            $file_id = $res->id;
        } elseif ($idFile && $request['hidden_file_id'] != '') {
            $hiddenFileId = $request['hidden_file_id'];
            $file = $request->file('rent_agreement');
            $uploadFile = $file->getClientOriginalName();
            $filename = pathinfo($uploadFile, PATHINFO_FILENAME);
            $fname = $filename . '_' . time() . '.' . $file->getClientOriginalExtension();
            ImageUpload::upload($file, $mainFolder,$fname);
            // $file->move($mainFolder, $fname);
            $data = [
                'file_name' => $fname,
                'file_path' => $mainFolder,
                'file_extension' => $file->getClientOriginalExtension(),
            ];
            $fileRes = Files::find($hiddenFileId);
            $fileRes->update($data);
            $file_id = $hiddenFileId;
        } elseif ($idFile == '') {
            $file_id = $request['hidden_file_id'];
        }
        $rentData = [
            'branch_id' => $request['branch'],
            'company_id' => $request['company_id'],
            'rent_type' => $request['rentType'],
            //'date' => date("Y-m-d", strtotime( convertDate($request->date))),
            'agreement_from' => date("Y-m-d", strtotime(convertDate($request->agreement_from))),
            'agreement_to' => date("Y-m-d", strtotime(convertDate($request->agreement_to))),
            'place' => $request['place'],
            'owner_name' => $request['owner_name'],
            'owner_mobile_number' => $request['owner_mobile_number'],
            'owner_pen_number' => $request['owner_pen_card'],
            'owner_aadhar_number' => $request['owner_aadhar_card'],
            'owner_bank_name' => $request['bank_name'],
            'owner_bank_account_number' => $request['bank_account_number'],
            'owner_bank_ifsc_code' => $request['ifsc_code'],
            'security_amount' => $request['security_amount'],
            'rent' => $request['rent'],
            'yearly_increment' => $request['yearly_increment'],
            'office_area' => $request['office_area'],
            'employee_code' => $request['employee_code'],
            'authorized_employee_name' => $request['employee_name'],
            'authorized_employee_designation' => $request['employee_designation'],
            'mobile_number' => $request['mobile_number'],
            'rent_agreement_file_id' => $file_id,
            'employee_id' => $request['employee_id'],
            'created_at' => $created_at,
            'dob' => date("Y-m-d", strtotime(convertDate($request->dob))),
            'age' => $request['age'],
        ];
        if ($request['owner_ssb_account'] != '') {
            $rentData['owner_ssb_number'] = $request['owner_ssb_account'];
            $rentData['owner_ssb_id'] = $request['owner_ssb_id'];
        } else {
            $rentData['owner_ssb_number'] = NULL;
            $rentData['owner_ssb_id'] = NULL;
        }
        //echo "<pre>"; print_r($rentData); die;
        $rentLiability = RentLiability::find($rentId);
        $rentLiability->update($rentData);


        $encodeDate = json_encode($rentData);

        if ($rentLiability) {
            return redirect('admin/rentliabilities')->with('success', 'Rent agreement updated successfully!');
        } else {
            return back()->with('alert', 'An error occured');
        }
    }
    /**
     * Update status.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function updateStatus($id)
    {
        $rentStatus = RentLiability::select('status')->where('id', $id)->first();
        $rdata = RentLiability::findOrFail($id);
        if ($rentStatus->status == 0) {
            $rdata->status = 1;
        } else {
            $rdata->status = 0;
        }
        $rdata = $rdata->save();

        $encodeDate = "status changed_from_" . $rentStatus->status . "other";

        if ($rdata) {
            return redirect()->route('admin.rent.liabilities')->with('success', 'Rent agreement status updated successfully!');
        } else {
            return back()->with('alert', 'Problem with update rent agreement');
        }
    }
    /**
     * Rent Payable View.
     *
     * @return \Illuminate\Http\Response
     */
    public function rentPayableView()
    {
        $data['title'] = 'Rent Management | Rent Payable';
        return view('templates.admin.rent-management.rent_payable', $data);
    }
    /**
     * Display rent payable listing.
     *
     * @param  \App\Reservation  $reservation
     * @return \Illuminate\Http\Response
     */
    public function rentPayableListing(Request $request)
    {
        if ($request->ajax()) {
            $arrFormData = array();
            if (!empty($_POST['searchform'])) {
                foreach ($_POST['searchform'] as $frm_data) {
                    $arrFormData[$frm_data['name']] = $frm_data['value'];
                }
            }
            $data = RentLiability::with('liabilityBranch')->where('status', 0);

            if (isset($arrFormData['is_search']) && $arrFormData['is_search'] == 'yes') {
                if ($arrFormData['rent_month'] != '') {
                    $month = $arrFormData['rent_month'];
                    $data = $data->whereMonth('created_at', $month);
                }
                if ($arrFormData['rent_year'] != '') {
                    $year = $arrFormData['rent_year'];
                    $data = $data->whereYear('created_at', $year);
                }
                if ($arrFormData['rent_type'] != '') {
                    $rent_type = $arrFormData['rent_type'];
                    $data = $data->where('rent_type', '=', $rent_type);
                }
            } else {
                $data = $data->where('branch_id', '=', 0);
            }
            $data = $data->orderby('created_at', 'DESC')->get();
            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('checkbox', function ($row) {
                    $branch = '<input type="checkbox" name="rent_payable_record" value="' . $row->id . '" id="rent_payable_record">';
                    return $branch;
                })
                ->escapeColumns(['branch'])
                ->addColumn('branch', function ($row) {
                    $branch = $row['liabilityBranch']->name;
                    return $branch;
                })
                ->rawColumns(['branch'])
                ->addColumn('rent_type', function ($row) {
                    $rent_type = getSubAcountHead($row->rent_type);
                    return $rent_type;
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
                    $owner_ssb_account = $row->owner_ssb_number;
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
                    $security_amount = $row->security_amount;
                    return $security_amount;
                })
                ->rawColumns(['security_amount'])
                ->addColumn('rent', function ($row) {
                    $rent = $row->rent;
                    return $rent;
                })
                ->rawColumns(['rent'])
                ->addColumn('yearly_increment', function ($row) {
                    $yearly_increment = $row->yearly_increment . '%';
                    return $yearly_increment;
                })
                ->rawColumns(['yearly_increment'])
                ->addColumn('office_area', function ($row) {
                    $office_area = $row->office_area;
                    return $office_area;
                })
                ->rawColumns(['office_area'])
                ->addColumn('employee_code', function ($row) {
                    $employee_code = $row->employee_code;
                    return $employee_code;
                })
                ->rawColumns(['employee_code'])
                ->addColumn('employee_name', function ($row) {
                    $employee_name = $row->authorized_employee_name;
                    return $employee_name;
                })
                ->rawColumns(['employee_name'])
                ->addColumn('employee_designation', function ($row) {
                    $employee_designation = $row->authorized_employee_designation;
                    return $employee_designation;
                })
                ->rawColumns(['employee_designation'])
                ->addColumn('mobile_number', function ($row) {
                    $mobile_number = $row->mobile_number;
                    return $mobile_number;
                })
                ->rawColumns(['mobile_number'])
                ->addColumn('rent_agreement', function ($row) {
                    $rent_agreement = getFileData($row->rent_agreement_file_id);
                    foreach ($rent_agreement as $key => $value) {
                        $folderName = 'rent-liabilities/'.$value->file_name;
                        $url  = ImageUpload::generatePreSignedUrl($folderName);

                        return '<a href="'.$url.'" target="blank">' . $value->file_name . '<a>';
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
                ->addColumn('action', function ($row) {
                    $url = URL::to("admin/rent/edit-rent-liability/" . $row->id . "");
                    $statusUrl = URL::to("admin/rent/updatestatus/" . $row->id . "");

                    $btn = '<div class="list-icons"><div class="dropdown"><a href="#" class="list-icons-item" data-toggle="dropdown"><i class="icon-menu9"></i></a><div class="dropdown-menu dropdown-menu-right">';
                    $btn .= '<a class="dropdown-item" href="' . $url . '"><i class="icon-pencil7 mr-2"></i>Edit</a>';
                    if ($row->status == 0) {
                        $btn .= '<a class="dropdown-item" href="' . $statusUrl . '"><i class="icon-pencil7 mr-2"></i>Deactive</a>';
                    } else {
                        $btn .= '<a class="dropdown-item" href="' . $statusUrl . '"><i class="icon-pencil7 mr-2"></i>Active</a>';
                    }
                    $btn .= '</div></div></div>';
                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
    }
    /**
     * Display rent payable listing.
     *
     * @param  \App\Reservation  $reservation
     * @return \Illuminate\Http\Response
     */
    public function transferRentPayableView(Request $request)
    {
        $data['title'] = 'Rent Management | Transfer Rent Payable';
        if ($request['selected_records'] && isset($request['selected_records'])) {
            $sRecord = explode(',', $request['selected_records']);
            $data['rentPayable'] = RentLiability::with('liabilityBranch')->whereIn('id', $sRecord)->get();
            $totalAmount = 0;
            foreach ($data['rentPayable'] as $key => $value) {
                $totalAmount = $totalAmount + $value->rent;
            }
            $data['totalAmount'] = $totalAmount;
            $data['selectedRecords'] = $request['selected_records'];
        } else {
            $data['rentPayable'] = array();
            $data['totalAmount'] = 0;
            $data['selectedRecords'] = 0;
        }
        if ($request['pending_records'] && isset($request['pending_records'])) {
            $usRecord = explode(',', $request['pending_records']);
            $update = DB::table('rent_liabilities')->whereIn('id', $usRecord)->update(['status' => 3]);
        }
        /*$data['acountheads'] = AccountHeads::where('account_type',2)->get();*/
        $data['acountheads'] = array('');
        return view('templates.admin.rent-management.transfer_rent_payable', $data);
    }
    /**
     * Transfer rent payable amount.
     *
     * @param  \App\Reservation  $reservation
     * @return \Illuminate\Http\Response
     */
    public function transferRentPayableAmount(Request $request)
    {
        $rentIds = explode(',', $request['rentIds']);

        DB::beginTransaction();
        try {
            foreach ($rentIds as $key => $value) {
                $rData = RentLiability::with('liabilityBranch')->where('id', $value)->first();
                $ssbAccountDetails = SavingAccount::select('id', 'balance', 'branch_id', 'branch_code', 'member_id', 'account_no')->where('account_no', $rData->owner_ssb_number)->first();
                $amountArraySsb = array('1' => $rData->rent);
                $amount_deposit_by_name = $ssbAccountDetails['ssbMember']->first_name . ' ' . $ssbAccountDetails['ssbMember']->last_name;
                $rlResult = RentLiability::find($rData->id);
                $rlResult->status = 2;
                $rlResult->save();
                if ($request['amount_mode'] == 0) {
                    $cheque_dd_no = NULL;
                    $online_payment_id = NULL;
                    $online_payment_by = NULL;
                    $bank_name = NULL;
                    $cheque_date = NULL;
                    $account_number = NULL;
                    $paymentMode = 4;
                    $ssbpaymentMode = 3;
                    $paymentDate = $request['created_at'];
                    $ssb['saving_account_id'] = $ssbAccountDetails->id;
                    $ssb['account_no'] = $ssbAccountDetails->account_no;
                    $ssb['opening_balance'] = $ssbAccountDetails->balance + $rData->rent;
                    $ssb['deposit'] = $rData->rent;
                    $ssb['withdrawal'] = 0;
                    $ssb['description'] = 'Transfer Rent Payable';
                    $ssb['currency_code'] = 'INR';
                    $ssb['payment_type'] = 'DR';
                    $ssb['payment_mode'] = $ssbpaymentMode;
                    $ssb['created_at'] = $request['created_at'];
                    $ssbAccountTran = SavingAccountTranscation::create($ssb);
                    $ssb_transaction_id = $ssbAccountTran->id;
                    // update saving account current balance 
                    $ssbBalance = SavingAccount::find($ssbAccountDetails->id);
                    $ssbBalance->balance = $ssbAccountDetails->balance + $rData->rent;
                    $ssbBalance->save();
                    $data['saving_account_transaction_id'] = $ssb_transaction_id;
                    $data['rent_id'] = $rData->id;
                    $data['created_at'] = $request['created_at'];
                    $satRef = TransactionReferences::create($data);
                    $satRefId = $satRef->id;
                    // $ssbCreateTran = CommanController::createTransaction($satRefId, 1, $ssbAccountDetails->id, $ssbAccountDetails->member_id, $rData->branch_id, getBranchCode($rData->branch_id)->branch_code, $amountArraySsb, $paymentMode, $amount_deposit_by_name, $ssbAccountDetails->id, $ssbAccountDetails->account_no, $cheque_dd_no, $bank_name, $branch_name = NULL, $paymentDate, $online_payment_id, $online_payment_by, $ssbAccountDetails->id, 'DR');
                    $totalbalance = $ssbAccountDetails->balance + $rData->rent;

                    $createDayBook = CommanController::createDayBook($ssbCreateTran, $satRefId, 1, $rData->id, 0, $ssbAccountDetails->member_id, $totalbalance, $rData->rent, 0, 'Transfer Rent Payable', $rData->owner_ssb_number, $rData->branch_id, getBranchCode($rData->branch_id)->branch_code, $amountArraySsb, $paymentMode, $amount_deposit_by_name, $rData->id, $ssbAccountDetails->account_no, $cheque_dd_no, NULL, NULL, $paymentDate, $online_payment_by, $online_payment_by, $ssbAccountDetails->id, 'DR');
                } elseif ($request['amount_mode'] == 1) {
                    $cheque_dd_no = $request['cheque_number'];
                    $cheque_date = $request['cheque_date'];
                    $bank_name = $request['bank_name'];
                    $account_number = $request['bank_account_number'];
                    $paymentMode = $request['mode'];
                    if ($paymentMode == 0) {
                        $cheque_dd_no = $request['cheque_number'];
                        $cheque_date = $request['cheque_date'];
                        $online_payment_id = NULL;
                        $online_payment_by = NULL;
                    } elseif ($paymentMode == 1) {
                        $online_payment_id = $request['transaction_id'];
                        $online_payment_by = NULL;
                        $cheque_dd_no = NULL;
                        $cheque_date = NULL;
                    }
                    $satRefId = NULL;
                    $paymentDate = $request['cheque_date'];
                }
                // $ssbCreateTran = CommanController::createTransaction($satRefId, 13, $rData->id, $ssbAccountDetails->member_id, $rData->branch_id, getBranchCode($rData->branch_id)->branch_code, $amountArraySsb, $paymentMode, $amount_deposit_by_name, $ssbAccountDetails->member_id, $ssbAccountDetails->account_no, $cheque_dd_no, $bank_name, $branch_name = NULL, $paymentDate, $online_payment_id, $online_payment_by, $ssbAccountDetails->id, 'CR');

                $createDayBook = CommanController::createDayBook($ssbCreateTran, $satRefId, 13, $rData->id, 0, $ssbAccountDetails->member_id, $rData->rent, $rData->rent, 0, 'Transfer Rent Payable', $rData->owner_ssb_number, $rData->branch_id, getBranchCode($rData->branch_id)->branch_code, $amountArraySsb, $paymentMode, $amount_deposit_by_name, $ssbAccountDetails->member_id, $ssbAccountDetails->account_no, $cheque_dd_no, $bank_name, $branch_name = NULL, $paymentDate, $online_payment_by, $online_payment_by, $ssbAccountDetails->id, 'CR');
            }
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('alert', $ex->getMessage());
        }
        return redirect()->route('admin.rent.payable')->with('success', 'Rent payable amount successfully transfer!');
    }
    /**
     * Rent report listing view.
     *
     * @param  \App\Reservation  $reservation
     * @return \Illuminate\Http\Response
     */
    public function rentReportView(Request $request)
    {
        $report = array();
        $data['title'] = 'Rent Management | Rent Report';
        $result = RentLiability::select('id')->where('status', 3)->get();
        foreach ($result as $key => $value) {
            array_push($report, $value->id);
        }
        $data['report'] = implode(',', $report);
        /*$data['acountheads'] = AccountHeads::where('account_type',2)->get();*/
        $data['acountheads'] = array('');
        return view('templates.admin.rent-management.rent_report', $data);
    }
    /**
     * Display rent report listing.
     *
     * @param  \App\Reservation  $reservation
     * @return \Illuminate\Http\Response
     */
    public function rentReportListing(Request $request)
    {
        if ($request->ajax()) {
            $arrFormData = array();
            if (!empty($_POST['searchform'])) {
                foreach ($_POST['searchform'] as $frm_data) {
                    $arrFormData[$frm_data['name']] = $frm_data['value'];
                }
            }
            $data = RentLiability::with('liabilityBranch')->where('status', 3);

            if (isset($arrFormData['is_search']) && $arrFormData['is_search'] == 'yes') {
                if ($arrFormData['rent_branch'] != '') {
                    $branch = $arrFormData['rent_branch'];
                    $data = $data->where('branch_id', $branch);
                }
                if ($arrFormData['rent_month'] != '') {
                    $month = $arrFormData['rent_month'];
                    $data = $data->whereMonth('created_at', $month);
                }
                if ($arrFormData['rent_year'] != '') {
                    $year = $arrFormData['rent_year'];
                    $data = $data->whereYear('created_at', $year);
                }
                if ($arrFormData['rent_type'] != '') {
                    $rent_type = $arrFormData['rent_type'];
                    $data = $data->where('rent_type', '=', $rent_type);
                }
            }
            $data = $data->orderby('created_at', 'DESC')->get();
            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('checkbox', function ($row) {
                    $branch = '<input type="checkbox" name="rent_payable_record" value="' . $row->id . '" id="rent_payable_record">';
                    return $branch;
                })
                ->escapeColumns(['branch'])
                ->addColumn('branch', function ($row) {
                    $branch = $row['liabilityBranch']->name;
                    return $branch;
                })
                ->rawColumns(['branch'])
                ->addColumn('rent_type', function ($row) {
                    $rent_type = getSubAcountHead($row->rent_type);
                    return $rent_type;
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
                    $owner_ssb_account = $row->owner_ssb_number;
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
                    $security_amount = $row->security_amount;
                    return $security_amount;
                })
                ->rawColumns(['security_amount'])
                ->addColumn('rent', function ($row) {
                    $rent = $row->rent;
                    return $rent;
                })
                ->rawColumns(['rent'])
                ->addColumn('yearly_increment', function ($row) {
                    $yearly_increment = $row->yearly_increment . '%';
                    return $yearly_increment;
                })
                ->rawColumns(['yearly_increment'])
                ->addColumn('office_area', function ($row) {
                    $office_area = $row->office_area;
                    return $office_area;
                })
                ->rawColumns(['office_area'])
                ->addColumn('employee_code', function ($row) {
                    $employee_code = $row->employee_code;
                    return $employee_code;
                })
                ->rawColumns(['employee_code'])
                ->addColumn('employee_name', function ($row) {
                    $employee_name = $row->authorized_employee_name;
                    return $employee_name;
                })
                ->rawColumns(['employee_name'])
                ->addColumn('employee_designation', function ($row) {
                    $employee_designation = $row->authorized_employee_designation;
                    return $employee_designation;
                })
                ->rawColumns(['employee_designation'])
                ->addColumn('mobile_number', function ($row) {
                    $mobile_number = $row->mobile_number;
                    return $mobile_number;
                })
                ->rawColumns(['mobile_number'])
                ->addColumn('rent_agreement', function ($row) {
                    $rent_agreement = getFileData($row->rent_agreement_file_id);
                    foreach ($rent_agreement as $key => $value) {
                        $folderName = 'rent-liabilities/'.$value->file_name;
                        $url  = ImageUpload::generatePreSignedUrl($folderName);
                        return '<a href="'.$url.'" target="blank">' . $value->file_name . '<a>';
                    }
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
                /*->addColumn('action', function($row){
                $url = URL::to("admin/rent/edit-rent-liability/".$row->id."");
                $statusUrl = URL::to("admin/rent/updatestatus/".$row->id."");
                
                $btn = '<div class="list-icons"><div class="dropdown"><a href="#" class="list-icons-item" data-toggle="dropdown"><i class="icon-menu9"></i></a><div class="dropdown-menu dropdown-menu-right">';
                $btn .= '<a class="dropdown-item" href="'.$url.'"><i class="icon-pencil7 mr-2"></i>Edit</a>';
                if($row->status == 0){
                    $btn .= '<a class="dropdown-item" href="'.$statusUrl.'"><i class="icon-pencil7 mr-2"></i>Deactive</a>';
                }else{
                    $btn .= '<a class="dropdown-item" href="'.$statusUrl.'"><i class="icon-pencil7 mr-2"></i>Active</a>';   
                }
                $btn .= '</div></div></div>';          
                return $btn;
            })
            ->rawColumns(['action'])*/
                ->make(true);
        }
    }
    /**
     * Get rent report ids.
     *
     * @param  \App\Reservation  $reservation
     * @return \Illuminate\Http\Response
     */
    public function rentReportIds(Request $request)
    {
        $data = RentLiability::with('liabilityBranch')->where('status', 3);
        if (isset($request['isSearch']) && $request['isSearch'] == 'yes') {
            if ($request['rentBranch'] != '') {
                $branch = $request['rentBranch'];
                $data = $data->where('branch_id', $branch);
            }
            if ($request['rentMonth'] != '') {
                $month = $request['rentMonth'];
                $data = $data->whereMonth('created_at', $month);
            }
            if ($request['rentYear'] != '') {
                $year = $request['rentYear'];
                $data = $data->whereYear('created_at', $year);
            }
            if ($request['rentType'] != '') {
                $rent_type = $request['rentType'];
                $data = $data->where('rent_type', '=', $rent_type);
            }
        }
        $data = $data->get();
        $report = array();
        foreach ($data as $key => $value) {
            array_push($report, $value->id);
        }
        $resCount = implode(',', $report);
        $return_array = compact('resCount');
        return json_encode($return_array);
    }
    public function rentSsbCheck(Request $request)
    {
        $ssbAccount = $request->ssb_account;
        $resCount = 0;
        $account_no = '';
        $name = '';
        $ssbDate = '';

        if ($ssbAccount) {
            $account_no = SavingAccount::where('account_no', $ssbAccount)->first(['customer_id', 'account_no', 'id', 'created_at', 'company_id']);
            if ($account_no) {
                $ssbDate = date("d/m/Y", strtotime(convertDate($account_no->created_at)));
                $member = \App\Models\Member::where('id', $account_no->customer_id)->first(['first_name', 'last_name']);
                $name = $member->first_name . ' ' . $member->last_name;
                $resCount = 1;
                $companyId = $account_no->company_id;
            }
        }
        $return_array = compact('account_no', 'resCount', 'name', 'ssbDate');
        return json_encode($return_array);
    }
    public function rentEmployeeCheck(Request $request)
    {
        $employee_code = $request->employee_code;
        $resCount = 0;
        $emp = '';
        $designation_name = '';
        $register_date = '';

        if ($employee_code) {
            $emp = \App\Models\Employee::where('employee_code', $employee_code)->first(['id', 'designation_id', 'employee_code', 'employee_name', 'mobile_no', 'status', 'created_at', 'employee_date', 'company_id', 'branch_id']);
            if ($emp) {
                if ($emp->status == 0) {
                    $resCount = 2;
                } else {
                    $resCount = 1;
                    $designation_name = getDesignationData('designation_name', $emp->designation_id)->designation_name;
                    $register_date = date("d/m/Y", strtotime($emp->employee_date));
                }
            }
        }
        $return_array = compact('emp', 'resCount', 'designation_name', 'register_date');
        return json_encode($return_array);
    }
    /**
     * Display a listing of the account heads.
     *
     * @return \Illuminate\Http\Response
     */
    public function ledger_list()
    {
        
        if (check_my_permission(Auth::user()->id, "81") != "1") {
            return redirect()->route('admin.dashboard');
        }
        $data['title'] = 'Rent Management | Rent Ledger Listing';
        $data['company_id'] = 0;
        return view('templates.admin.rent-management.rent_ledger_list', $data);
    }
    /**
     * Display the specified resource.
     *
     * @param  \App\Reservation  $reservation
     * @return \Illuminate\Http\Response
     */
    public function rentLedgerListing(Request $request)
    {
        if ($request->ajax()) {
            $arrFormData = array();
            $arrFormData['rent_month'] = $request->rent_month;
            $arrFormData['rent_year'] = $request->rent_year;
            $arrFormData['is_search'] = $request->is_search;
            $arrFormData['status'] = $request->status;
            $arrFormData['company_id'] = $request->company_id;
            if (isset($arrFormData['is_search']) && $arrFormData['is_search'] == 'yes') {
                $data = \App\Models\RentLedger::select('month_name', 'year', 'total_amount', 'transfer_amount', 'id', 'created_at', 'status', 'tds_amount', 'payable_amount', 'company_id')->has('company')->with('company:id,name')->with(['RentPayments' => function ($q) {
                    $q->select('id', 'ledger_id', 'neft_charge');
                }])->where('id', '>', 0)->where('is_deleted', 0);

                if ($arrFormData['rent_month'] != '') {
                    $rent_month = $arrFormData['rent_month'];
                    $data = $data->where('month', $rent_month);
                }
                if ($arrFormData['rent_year'] != '') {
                    $rent_year = $arrFormData['rent_year'];
                    $data = $data->where('year', $rent_year);
                }
                if ($arrFormData['company_id'] != 0) {
                    $company_id = $arrFormData['company_id'];
                    if ($company_id > 0) {
                        $data = $data->where('company_id', $company_id);
                    }
                }
                if ($arrFormData['status'] != '') {
                    $status = $arrFormData['status'];
                    $data = $data->where('status', $status);
                }



                $data1 = $data->orderby('created_at', 'DESC')->count('id');
                $count = ($data1);
                $data = $data->offset($_POST['start'])->limit($_POST['length'])->orderby('created_at', 'DESC')->get();

                $totalCount =  $count;
                $sno = $_POST['start'];
                $rowReturn = array();
                foreach ($data as $row) {
                    $sno++;
                    $val['DT_RowIndex'] = $sno;
                    $val['company'] = ($row['company']['name'])??"N/A";
                    $val['month'] = $row->month_name;
                    $val['year'] = $row->year;
                    $val['total_amount'] = number_format((float)$row->total_amount, 2, '.', '');
                    $val['tds_amount'] = number_format((float)$row->tds_amount, 2, '.', '');
                    $val['transferred_amount'] = number_format((float)$row->transfer_amount, 2, '.', '');
                    $pending = $row->total_amount - $row->transfer_amount - $row->tds_amount;
                    $val['transfer_charge'] = number_format((float)$pending, 2, '.', '');
                    $val['payable_amount'] = number_format((float)$row->payable_amount, 2, '.', '');
                    $neft = $row['RentPayments']->sum('neft_charge');
                    $val['neft'] = number_format((float)$neft, 2, '.', '');
                    $val['created_at'] = date("d/m/Y", strtotime(convertDate($row->created_at)));
                    $status = 'Pending ';
                    if ($row->status == 1) {
                        $status = 'Transferred';
                    }
                    if ($row->status == 2) {
                        $status = 'Partial Transfer';
                    }
                    $val['status'] = $status;
                    $url = URL::to("admin/rent/ledger-delete/" . $row->id . "");
                    $url1 = URL::to("admin/rent/ledger-report/" . $row->id . "");
                    $url2 = URL::to("admin/rent/ledger-payable/" . $row->id . "");
                    $btn = '<div class="list-icons"><div class="dropdown"><a href="#" class="list-icons-item" data-toggle="dropdown"><i class="icon-menu9"></i></a><div class="dropdown-menu dropdown-menu-right">';
                    if ($row->status == 0) {
                        $btn .= '<a class="dropdown-item" href="javascript:void(0)" title="Ledger Delete" onclick=deleteLedger("' . $row->id . '");><i class="icon-trash-alt  mr-2"></i>Ledger Delete</a>';
                    }
                    $btn .= '<a class="dropdown-item" href="' . $url1 . '"><i class="icon-list3 mr-2"></i>Ledger Details</a>';
                    if ($row->status != 1) {
                        $btn .= '<a class="dropdown-item" href="' . $url2 . '" target="blank"><i class="icon-share2 mr-2"></i>Ledger Payable</a>';
                    }
                    $btn .= '</div></div></div>';
                    $val['action'] = $btn;

                    $rowReturn[] = $val;
                }
                $output = array("draw" => $_POST['draw'], "recordsTotal" => $totalCount, "recordsFiltered" => $count, "data" => $rowReturn);
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
     * Display a listing of the account heads.
     *
     * @return \Illuminate\Http\Response
     */
    public function ledgerCreate(Request $request)
    {
        $period = now()->subMonths(12)->monthsUntil(now());

        $data = [];
        foreach ($period as $date) {

            $data['month'][$date->format('F')] = $date->format('F');
            $data['year'][$date->year] = $date->year;
        }

        // dd($data);    

        if (check_my_permission(Auth::user()->id, "80") != "1") {
            return redirect()->route('admin.dashboard');
        }

        $data['title'] = 'Rent Management | Rent Ledger Create';
        $finacialYear = getFinacialYear();
        $startMonth = date('F', strtotime($finacialYear['dateStart']));
        $endMonth = date('F', strtotime($finacialYear['dateEnd']));
        $startYear = date('Y', strtotime($finacialYear['dateStart']));
        $endYear = date('Y', strtotime($finacialYear['dateEnd']));

        $existMonth = \App\Models\RentLedger::whereBetween('month_name', [$startMonth, $endMonth])->whereBetween('year', [$startYear, $endYear])->pluck('year', 'month')->toArray();


        if (Request1::isMethod('post')) {

            $date = $request->rent_year . '-' . $request->rent_month . '-01';

            // print_r($_POST);die;
            $lastDate = Carbon::parse($date)->startOfMonth();
            $data['lastDate'] = date('d/m/Y', strtotime($lastDate));
            $data['code'] = 1;
            $data['re_month'] = $request->rent_month;
            $data['re_year'] = $request->rent_year;
            $data['re_month_name'] = $request->rent_month_name;
            $data['re_company'] = $request->company_id;
            $check_data = \App\Models\RentLedger::where('month', $request->rent_month)->where('year', $request->rent_year)->where('company_id', $request->company_id)->where('is_deleted', 0);
            $check_data = $check_data->count('id');
            if ($check_data > 0) {
                return back()->with('alert', 'The rent ledger of this month has already been generated. Please change the details.')->withInput($request->all());
            }
            $getCurentMont = date("m", strtotime(convertDate($request->created_at1)));
            $getCurentYear = date("Y", strtotime(convertDate($request->created_at1)));
            $ledgerMontget = $request->rent_month;
            $ledgerYearget = $request->rent_year;
            // echo $getCurentMont.'=='.$getCurentYear.'=='.$ledgerMontget.'=='.$ledgerYearget;die;
            if ($ledgerYearget == $getCurentYear && $ledgerMontget > $getCurentMont) {
                return back()->with('alert', "You can not create ledger in future date!")->withInput($request->all());
            }
            $re_month1 = $data['re_month'];
            $dateChk = $data['re_year'] . '/' . $re_month1 . '/01';
            $data_rent = RentLiability::select('id', 'branch_id', 'rent_agreement_file_id', 'employee_id', 'rent_type', 'owner_ssb_id', 'place', 'owner_name', 'owner_mobile_number', 'owner_pen_number', 'owner_aadhar_number', 'owner_bank_name', 'owner_bank_account_number', 'owner_bank_ifsc_code', 'security_amount', 'rent', 'yearly_increment', 'office_area', 'advance_payment', 'current_balance', 'created_at', 'status', 'agreement_from', 'company_id')->with(['AcountHeadCustom' => function ($q) {
                $q->select('id', 'head_id', 'parent_id', 'sub_head');
            }, 'SsbAccountNumberCustom' => function ($q) {
                $q->select('id', 'account_no', 'member_id');
            }, 'rentFileCustom' => function ($q) {
                $q->select('id', 'file_name');
            }, 'liabilityBranch' => function ($query) {
                $query->select('id', 'name', 'branch_code', 'sector', 'regan', 'zone');
            }])->with(['employee_rent' => function ($query) {
                $query->select('id', 'designation_id', 'employee_code', 'employee_name', 'mobile_no')->with(['designation' => function ($q) {
                    $q->select('id', 'designation_name');
                }]);
            }])->where('status', 0)->whereDate('agreement_from', '<=', $dateChk)->where('company_id',$request->company_id);
            $data['companies'] = Companies::where('id', $request->company_id)->get(['name', 'id'])->toArray();
            $data['c_name'] = $data['companies'][0]['name'];
            $data['c_id'] = $data['companies'][0]['id'];
            // $data_rent=RentLiability::select('id','branch_id','rent_agreement_file_id','employee_id','rent_type','owner_ssb_id','place','owner_name','owner_mobile_number','owner_pen_number','owner_aadhar_number','owner_bank_name','owner_bank_account_number','owner_bank_ifsc_code','security_amount','rent','yearly_increment', 'office_area','advance_payment','current_balance','created_at','status')->with(['liabilityBranch' => function($query){ $query->select('id', 'name','branch_code','sector','regan','zone');}])->with(['employee_rent' => function($query){ $query->select('id', 'designation_id','employee_code','employee_name','mobile_no');}])
            $data_rent = $data_rent->orderby('created_at', 'DESC')->get();
            $token = session()->get('_token');

            $Cache = Cache::put('rent_ledger_create_list'.$token, $data_rent->toArray());
            Cache::put('rent_ledger_create_count'.$token, count($data_rent->toArray()));
            $data['rent'] = $data_rent;
        }

        return view('templates.admin.rent-management.ledger-create', $data);
    }
    public function ledgerSave(Request $request)
    {
        $totalTransferAmount = collect($request->get('amount'))->sum();
        $totalTdsAmount = collect($request->get('tds_amount'))->sum();
        $totalPayable = collect($request->get('transfer_amount'))->sum();
        $monthName = date('F', mktime(0, 0, 0, $request->ledger_month, 10));

        // print_r($_POST);die;
        $rules = [
            'ledger_month' => ['required'],
            'ledger_year' => ['required'],
        ];
        $customMessages = [
            'required' => ':Attribute  is required.',
        ];
        $this->validate($request, $rules, $customMessages);
        DB::beginTransaction();
        try {
            $total_amount = 0;
            $transfer_amount = 0;
            $settel_amount = 0;
            $company_id = $request->company_id;
            $globaldate = $request->created_at;
            Session::put('created_at', $request->created_at);
            $check_data = \App\Models\RentLedger::where('month', $request->ledger_month)->where('year', $request->ledger_year)->where('company_id', $request->company_id)->where('is_deleted', 0);

            $check_data = $check_data->count();
            if ($check_data > 0) {
                return back()->with('alert', 'The rent ledger of this month has already been generated. Please change the details.')->withInput($request->all());
            }
            $getCurentMont = date("m", strtotime(convertDate($request->created_at)));
            $getCurentYear = date("Y", strtotime(convertDate($request->created_at)));
            $ledgerMontget = $request->ledger_month;
            $ledgerYearget = $request->ledger_year;
            //echo $getCurentMont.'=='.$getCurentYear.'=='.$ledgerMontget.'=='.$ledgerYearget;die;
            if ($ledgerYearget == $getCurentYear && $ledgerMontget > $getCurentMont) {
                return back()->with('alert', "You can not create ledger in future date!")->withInput($request->all());
            }
            $select_date = $request->select_date;
            $current_date = date("Y-m-d", strtotime(convertDate($globaldate)));
            $entry_date = date("Y-m-d", strtotime(convertDate($select_date)));
            $entry_time = date("H:i:s", strtotime(convertDate($globaldate)));
            $date_create = $entry_date . ' ' . $entry_time;
            $created_at = date("Y-m-d H:i:s", strtotime(convertDate($date_create)));
            $updated_at = date("Y-m-d H:i:s", strtotime(convertDate($date_create)));
            $leaserdata['month'] = $request->ledger_month;
            $leaserdata['month_name'] = $monthName;
            $leaserdata['year'] = $request->ledger_year;
            $leaserdata['status'] = 0;
            $leaserdata['created_at'] = $created_at;
            $leaserdata['updated_at'] = $created_at;
            $leaserdata['total_amount'] = $totalTransferAmount;
            $leaserdata['tds_amount'] = $totalTdsAmount;
            $leaserdata['payable_amount'] = $totalPayable;
            $leaserdata['company_id'] = $request->company_id;
            $create = \App\Models\RentLedger::create($leaserdata);
            $leaser = $create->id;
            $encodeDate = json_encode($leaserdata);
            // $arrs = array("leaser_id" => $leaser, "type" => "11", "account_head_id" => 0, "user_id" => Auth::user()->id, "message" => "ledger Create", "data" => $encodeDate);
            // DB::table('user_log')->insert($arrs);
            $payment_mode = 3;
            $payment_type = 'CR';
            $currency_code = 'INR';
            $created_by = 1;
            $created_by_id = \Auth::user()->id;

            $randNumber = mt_rand(0, 999999999999999);
            $v_no = $randNumber;
            $v_date = $entry_date;
            // head  entry----------------------------
            $des = 'Rent Ledger of ' . $monthName . ' ' . $request->ledger_year;
            $tdsdes = 'Tds Amount of Rent Ledger of ' . $monthName . ' ' . $request->ledger_year;
            $type = 10;
            $sub_type = 101;
            $type_id = $leaser;
            $daybookRef = CommanController::createBranchDayBookReferenceNew($total_amount, $globaldate);
            $refId = $daybookRef;
            if (isset($_POST['rent_lib_id'])) {
                foreach (($_POST['rent_lib_id']) as $key => $option) {
                    $rentLiabilityId = $_POST['rent_lib_id'][$key];
                    $libDetail = RentLiability::where('id', $rentLiabilityId)->first();
                    $data['ledger_id']              = $leaser;
                    $data['rent_liability_id']      = $rentLiabilityId;
                    $data['branch_id']              = $libDetail->branch_id;
                    $data['month']                  = $request->ledger_month;
                    $data['month_name']             = $monthName;
                    $data['year']                   = $request->ledger_year;
                    $data['security_amount']        = $libDetail->security_amount;
                    $data['rent_amount']            = $libDetail->rent;
                    $data['actual_transfer_amount'] = $_POST['transfer_amount'][$key];

                    $data['transfer_amount']        = $_POST['transfer_amount'][$key];
                    $data['yearly_increment']       = $libDetail->yearly_increment;
                    $data['office_area']            = $libDetail->office_area;
                    $data['employee_id']            = $libDetail->employee_id;
                    $data['balance']                = $_POST['transfer_amount'][$key];
                    $data['tds_amount']             = $_POST['tds_amount'][$key];
                    $data['actual_tds']             = $_POST['tds_amount_actual'][$key];


                    if ($libDetail->owner_ssb_id) {
                        $data['owner_ssb_id']           = $libDetail->owner_ssb_id;
                        $data['owner_ssb_account']      = getSsbAccountNumber($libDetail->owner_ssb_id)->account_no;
                    } else {
                        $data['owner_ssb_id']           = NULL;
                        $data['owner_ssb_account']      = NULL;
                    }

                    $data['owner_bank_name']        = $libDetail->owner_bank_name;
                    $data['owner_bank_account_number'] = $libDetail->owner_bank_account_number;
                    $data['owner_bank_ifsc_code']    = $libDetail->owner_bank_ifsc_code;
                    $data['status']                  = 0;
                    $data['created_at'] = $created_at;
                    $data['updated_at'] = $created_at;
                    $data['company_id'] = $company_id;
                    $data['ledger_create_daybook_ref_id'] = $refId;
                    // $data['payment_ref_id'] = $refId;


                    $create = \App\Models\RentPayment::create($data);
                    $TranId = $tranId = $create->id;

                    $branch_id = $libDetail->branch_id;
                    // Rent Liablility Leaser
                    $val['rent_liability_id'] = $rentLiabilityId;
                    $val['type'] = 4;
                    $val['type_id'] = $TranId;
                    $val['deposit'] = $_POST['amount'][$key];
                    $val['description'] =  $des;
                    $val['currency_code'] = 'INR';
                    $val['payment_type'] = 'CR';
                    $val['payment_mode'] = 3;
                    $val['status'] = 1;
                    $val['v_no'] = $v_no;
                    $val['v_date'] = $v_date;
                    $val['created_at'] = $created_at;
                    $val['updated_at'] = $updated_at;
                    $val['daybook_ref_id'] = $refId;
                    $val['company_id'] = $company_id;
                   
                    $createRentLeaser = \App\Models\RentLiabilityLedger::create($val);
                    $val2['rent_liability_id'] = $rentLiabilityId;
                    $val2['type'] = 5;
                    $val2['type_id'] = $TranId;
                    $val2['withdrawal'] = $_POST['tds_amount'][$key];
                    $val2['description'] = $tdsdes;
                    $val2['currency_code'] = 'INR';
                    $val2['payment_type'] = 'DR';
                    $val2['payment_mode'] = 3;
                    $val2['status'] = 1;
                    $val2['v_no'] = $v_no;
                    $val2['v_date'] = $v_date;
                    $val2['created_at'] = $created_at;
                    $val2['updated_at'] = $updated_at;
                    $val2['daybook_ref_id'] = $refId;
                    $val2['company_id'] = $company_id;
                    if ($_POST['tds_amount'][$key] > 0) {
                        $createRentLeaser = \App\Models\RentLiabilityLedger::create($val2);
                    }
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
                    $total_amount = $total_amount + $_POST['transfer_amount'][$key];
                    //expence
                    $head12 = 4;
                    $head22 = 86;
                    $head32 = 53;
                    $head42 = $libDetail->rent_type;
                    $head52 = NULL;
                    $allTran2 = CommanController::newHeadTransactionCreate($refId, $branch_id, $bank_id, $bank_ac_id, $head42, $type, $sub_type, $type_id, $associate_id = NULL, $member_id, $branch_id_to = NULL, $branch_id_from = NULL, $_POST['amount'][$key], $des, 'DR', $payment_mode, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $tranId, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $company_id);
                    ///libility
                    $head11 = 1;
                    $head21 = 8;
                    $head31 = 21;
                    $head41 = 60;
                    $head51 = NULL;
                    $allTran1 = CommanController::newHeadTransactionCreate($refId, $branch_id, $bank_id, $bank_ac_id, $head41, $type, $sub_type, $type_id, $associate_id = NULL, $member_id, $branch_id_to = NULL, $branch_id_from = NULL, $_POST['amount'][$key], $des, 'CR', $payment_mode, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $tranId, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $company_id);

                    if ($_POST['tds_amount'][$key] > 0) {
                        $allTran1 = CommanController::newHeadTransactionCreate($refId, $branch_id, $bank_id, $bank_ac_id, $head41, $type, $sub_type, $type_id, $associate_id = NULL, $member_id, $branch_id_to = NULL, $branch_id_from = NULL, $_POST['tds_amount'][$key], $tdsdes, 'DR', $payment_mode, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $tranId, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $company_id);

                        /**Duties and taxies > Tds on Rent Head */

                        $allTran1 = CommanController::newHeadTransactionCreate($refId, $branch_id, $bank_id, $bank_ac_id, 265, $type, $sub_type, $type_id, $associate_id = NULL, $member_id, $branch_id_to = NULL, $branch_id_from = NULL, $_POST['tds_amount'][$key], $tdsdes, 'CR', $payment_mode, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $tranId, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $company_id);

                        $sumTransferredAmount = \App\Models\RentPayment::where('rent_liability_id', $rentLiabilityId)->sum('transferred_amount');

                        $sumActualTransferAmount = \App\Models\RentPayment::where('rent_liability_id', $rentLiabilityId)->sum('actual_transfer_amount');
                        $libilityBalance = $sumActualTransferAmount - $sumTransferredAmount;
                        $currentLib = $_POST['amount'][$key] - $_POST['tds_amount'][$key];

                        $lib['current_balance'] = $libilityBalance + $currentLib;
                        $libUpdate = RentLiability::find($libDetail->id);
                        $libUpdate->update($lib);
                    }
                }
            }
            $updateLedger['total_amount'] = $total_amount;
            // $leaserdataUpdate = \App\Models\RentLedger::find($leaser);
            // $leaserdataUpdate->update($updateLedger);
            $reference['amount'] = $total_amount;
            $referenceUpdate = \App\Models\BranchDaybookReference::find($refId);
            $referenceUpdate->update($reference);
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('alert', $ex->getMessage());
        }
        return redirect('admin/rent/ledger-payable/' . $leaser)->with('success', 'Rent Ledger Created Successfully');
    }
    /**
     * Display a listing of the account heads.
     *
     * @return \Illuminate\Http\Response
     */
    public function ledger_payable($id)
    {
        $data['title'] = 'Rent Management | Rent Payable Listing';
        $data['leaserData'] = \App\Models\RentLedger::where('id', $id)->first();
        $leaserDate = date("Y/m/d", strtotime($data['leaserData']->created_at));
        $data['leaser_id'] = $id;
        $data['rent_list'] = \App\Models\RentPayment::select('id', 'rent_liability_id', 'branch_id', 'company_bank_id', 'company_bank_ac_id', 'owner_ssb_id', 'employee_id', 'rent_liability_id', 'owner_bank_name', 'owner_bank_account_number', 'owner_bank_ifsc_code', 'security_amount', 'rent_amount', 'yearly_increment', 'status', 'current_advance_payment', 'actual_transfer_amount', 'transfer_amount', 'advance_payment', 'settle_amount', 'tds_amount', 'transferred_date', 'v_date', 'v_no', 'transfer_mode', 'payment_mode', 'company_cheque_id', 'company_cheque_no', 'online_transaction_no', 'neft_charge', 'transferred_amount', 'company_id')->with('rentCompany:id,name')
            ->with(['rentLib' => function ($q) use($leaserDate){
            $q->select('id', 'branch_id', 'rent_agreement_file_id', 'employee_id', 'rent_type', 'owner_ssb_id', 'place', 'owner_name', 'owner_mobile_number', 'owner_pen_number', 'owner_aadhar_number', 'owner_bank_name', 'owner_bank_account_number', 'owner_bank_ifsc_code', 'security_amount', 'rent', 'yearly_increment', 'office_area', 'advance_payment', 'current_balance', 'created_at', 'status', 'agreement_from', 'agreement_to')->with(['AcountHeadCustom' => function ($q) {
                $q->select('id', 'head_id', 'sub_head');
            }])->with(['advance' => function ($q) use($leaserDate){
                $q->where('status_date','<=',$leaserDate)->where('settle_amount','>',0)->select('id','type_id','settle_amount','status_date');
            }]);
        }])->with(['rentSSB' => function ($q) {
            $q->select('id', 'account_no');
        }])->with(['rentBank' => function ($q) {
            $q->select('id', 'bank_name');
        }])->with(['rentBankAccount' => function ($q) {
            $q->select('id', 'account_no');
        }])->with(['rentBranch' => function ($query) {
            $query->select('id', 'name', 'branch_code', 'sector', 'regan', 'zone');
        }])->with(['rentEmp' => function ($query) {
            $query->select('id', 'designation_id', 'employee_code', 'employee_name', 'mobile_no')->with(['designation' => function ($q) {
                $q->select('id', 'designation_name');
            }]);
        }])->where('ledger_id', $id)->where('status', '!=', 1)->where('transfer_amount', '>', 0)->get();
        // dd($data['rent_list']);
        // dd(empty($data['rent_list'][0]['rentLib']['advance'][0]),$data['rent_list'][0]['rentLib']['advance'],empty($data['rent_list'][3]['rentLib']['advance'][0]),$data['rent_list'][3]['rentLib']['advance']);
        $data['company_name'] = ($data['rent_list'][0]['rentCompany']['name'])??"N/A";
        // $data['rent_list']=\App\Models\RentPayment::with('rentLib')->with('rentSSB')->with(['rentBranch' => function($query){ $query->select('id', 'name','branch_code','sector','regan','zone');}])->with(['rentEmp' => function($query){ $query->select('id', 'designation_id','employee_code','employee_name','mobile_no');}])
        return view('templates.admin.rent-management.transfer', $data);
    }
    public function rentTransferNext(Request $request)
    {
        //  $bankClosing=CommanController::SSBBackDateCR(1375,'2021-04-22 20:52:22',5000);
        //  die;
        
        if($request->select_id == null){
            return redirect()->back()->with('alert', 'Please select fields with is advance no');
        }
        $data['title'] = 'Rent Management | Rent Transfer List';
        $data['leaser_id'] = $request->leaser_id;
        $data['amount_mode'] = $request->amount_mode;
        $select_id_get = rtrim($request->select_id, ',');
        $select_id = explode(",", $select_id_get);
        //print_r($_POST);die;
        $data['rent_list'] = \App\Models\RentPayment::with('rentLib')->with('rentSSB')->with(['rentBranch' => function ($query) {
            $query->select('id', 'name', 'branch_code', 'sector', 'regan', 'zone');
        }])->with(['rentEmp' => function ($query) {
            $query->select('id', 'designation_id', 'employee_code', 'employee_name', 'mobile_no');
        }])->with('rentCompany:id,name')->whereIn('id', $select_id)->get();
        // pd($data);
        $data['c_id'] = ($data['rent_list']['rentCompany']['id']) ?? $data['rent_list'][0]['company_id'];
        $data['bank'] = \App\Models\SamraddhBank::where('status', 1)->where('company_id', $data['c_id'])->get();
        $data['selectedRent'] = $select_id_get;
        $check_data = \App\Models\RentLedger::where('id', $request->leaser_id)->first();
        $data['ledger_date'] = date("d/m/Y", strtotime($check_data->created_at));
        return view('templates.admin.rent-management.transfer_save', $data);
    }
    public function rentTransferSave(Request $request)
    {

        // print_r($_POST);die;
        $rules = [
            'amount_mode' => ['required'],
            'leaser_id' => ['required'],
        ];
        $customMessages = [
            'required' => ':Attribute  is required.',
        ];
        $this->validate($request, $rules, $customMessages);
        DB::beginTransaction();
        try {
            $ledger_id = $leaser = $request->leaser_id;
            $globaldate = $request->created_at;
            $total_transfer_amount = 0;
            $neft_charge = 0;
            $company_id = $request->company_id;
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
            // head  entry----------------------------                     
            $type = 10;
            $sub_type = 102;
            $type_id = $leaser;
            //
            if ($request->amount_mode == 1) {
                //ssb
                if (isset($_POST['rent_id'])) {
                    foreach (($_POST['rent_id']) as $key => $option) {
                        $rentPaymentId = $_POST['rent_id'][$key];
                        $rentPaymentDetail = \App\Models\RentPayment::with('rentLib')->where('id', $rentPaymentId)->first();

                        $total_transfer_amount = $total_transfer_amount + ($rentPaymentDetail->actual_transfer_amount - $rentPaymentDetail->transferred_amount);
                        $branch_id = $rentPaymentDetail->branch_id;
                        $branchCode = getBranchCode($branch_id)->branch_code;
                        $rentAmount = $rentPaymentDetail->actual_transfer_amount - $rentPaymentDetail->transferred_amoun;
                        $ssbAccountDetail = getSavingAccountMemberId($rentPaymentDetail->owner_ssb_id);
                        $totalPaybaleAmount = $rentPaymentDetail->transferred_amount + $rentAmount;


                        $daybookRef = CommanController::createBranchDayBookReferenceNew($totalPaybaleAmount, $globaldate);
                        $refId = $daybookRef;

                        $rentPaymentBalance = $rentPaymentDetail->balance;
                        $rentLiabilityBalance = $rentPaymentDetail['rentLib']->bill_current_balance;
                        $data['transferred_date']   = $entry_date;
                        $data['transfer_mode']      = $request->amount_mode;
                        $data['v_no']               = $v_no;
                        $data['v_date']             = $entry_date;
                        $data['status']             = 1;
                        $data['updated_at'] = $request->created_at;
                        $data['transfer_amount'] = $totalPaybaleAmount;
                        $data['transferred_amount'] = $totalPaybaleAmount;
                        $data['balance'] = $rentPaymentBalance - ($totalPaybaleAmount);
                        $data['payment_ref_id']             = $refId;
                        $rentPaymentUpdate = \App\Models\RentPayment::find($rentPaymentId);
                        $rentPaymentUpdate->update($data);
                        //print_r($rentPaymentDetail->owner_ssb_id);
                        //  print_r($ssbAccountDetail);die;
                        $ssbBalance = $ssbAccountDetail->balance;
                        $member_id = $ssbAccountDetail->member_id;
                        $ssbId = $ssbAccountDetail->id;
                        $ssbAccount = $ssbAccountDetail->account_no;
                        //------------ ssb tran head entry start  --------------------------
                        $detail = 'Rent Payment ' . $rentPaymentDetail->month_name . ' ' . $rentPaymentDetail->year;
                        $ssbTranCalculation = CommanController::SSBDateCR($ssbId, $ssbAccount, $ssbBalance, $rentAmount, $detail, 'INR', 'CR', 3, $branch_id, $associate_id = NULL, 7, $created_at,$refId,$company_id);
                        $bankClosing = CommanController::SSBBackDateCR($ssbId, $created_at, $rentAmount);
                        $ssbRentTranID = $ssbTranCalculation;
                        $amountArray = array('1' => $rentAmount);
                        $deposit_by_name = $created_by_name;
                        $deposit_by_id = $created_by_id;
                        // $rdCreateTran = CommanController::createTransaction(NULL, 1, $ssbAccountDetail->id, $member_id, $branch_id, $branchCode, $amountArray, 77, $deposit_by_name, $deposit_by_id, $ssbAccountDetail->account_no, $cheque_dd_no = 0, $bank_name = null, $branch_name = null, $payment_date = null, $online_payment_id = null, $online_payment_by = null, $saving_account_id = 0, 'CR');
                        //-------------------- ssb head entry start -----------------
                        $payment_mode = $paymentMode = 3;
                        $payment_type = 'CR';

                        $rentLiabilityId = $rentPaymentDetail['rentLib']->id;
                        $lib_current_balance = $rentPaymentDetail['rentLib']->current_balance;
                        $des = $rentPaymentDetail['rentLib']->owner_name . "'s " . $rentPaymentDetail->month_name . '' . $rentPaymentDetail->year . ' rent payment has been transferred to the SSB A/c' . $ssbAccount;
                        $head1SSB = 1;
                        $head2SSB = 8;
                        $head3SSB = 20;
                        $head4SSB = 56;
                        $head5SSB = NULL;
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
                        $ssb_account_id_to = $ssbId;
                        $cheque_bank_from_id = NULL;
                        $cheque_bank_ac_from_id = NULL;
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
                        $jv_unique_id = NULL;
                        $ssb_account_tran_id_to = $ssbRentTranID;
                        $ssb_account_tran_id_from = NULL;
                        $cheque_type = NULL;
                        $cheque_id = NULL;
                        $amount_to_id = NULL;
                        $amount_to_name = NULL;
                        $amount_from_id = NULL;
                        $amount_from_name = NULL;
                        $bank_id = NULL;
                        $bank_ac_id = NULL;
                        // ssb head entry +
                        $allTranSSB = CommanController::newHeadTransactionCreate($refId, $branch_id, $bank_id, $bank_ac_id, $head4SSB, 4, 49, $ssbId, $associate_id = NULL, $member_id, $branch_id_to = NULL, $branch_id_from = NULL, $rentAmount, $des, 'CR', $payment_mode, $currency_code, $v_no,  $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $ssbRentTranID, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $company_id);
                        // ssb Member transaction  +
                        // $memberTranSSB = CommanController::NewFieldAddMemberTransactionCreate($refId, 4, 49, $ssbId, $associate_id = NULL, $member_id, $branch_id, $bank_id = NULL, $bank_ac_id = NULL, $rentAmount, $des, $payment_type, $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $ssbRentTranID, $ssb_account_id_to, $cheque_bank_from_id, $cheque_bank_ac_from_id, $cheque_bank_to_name, $cheque_bank_to_branch, $cheque_bank_to_ac_no, $cheque_bank_to_ifsc, $transction_bank_from_id, $transction_bank_from_ac_id, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $jv_unique_id, $cheque_type, $cheque_id);
                        //------------ ssb tran head entry end --------------------------
                        $ssb_account_id_to = $ssbAccountDetail->id;
                        $rentLibLedger['rent_liability_id'] = $rentLiabilityId;
                        $rentLibLedger['type'] = 1;
                        $rentLibLedger['type_id'] = $rentPaymentId;
                        $rentLibLedger['withdrawal'] = $rentAmount;
                        $rentLibLedger['description'] = $detail;
                        $rentLibLedger['currency_code'] = $currency_code;
                        $rentLibLedger['payment_type'] = 'DR';
                        $rentLibLedger['payment_mode'] = 3;
                        $rentLibLedger['v_no'] = $v_no;
                        $rentLibLedger['v_date'] = $v_date;
                        $rentLibLedger['ssb_account_id_to'] = $ssb_account_id_to;
                        $rentLibLedger['created_at'] = $created_at;
                        $rentLibLedger['updated_at'] = $updated_at;
                        $rentLibLedger['daybook_ref_id'] = $refId;
                        $rentLibLedger['company_id'] = $company_id;

                        $rlL = \App\Models\RentLiabilityLedger::create($rentLibLedger);

                        ///------------------libility------------------
                        $head11 = 1;
                        $head21 = 8;
                        $head31 = 21;
                        $head41 = 60;
                        $head51 = NULL;
                        $allTran1 = CommanController::newHeadTransactionCreate($refId, $branch_id, $bank_id, $bank_ac_id, $head41, $type, $sub_type, $type_id, $associate_id = NULL, $member_id = NULL, $branch_id_to = NULL, $branch_id_from = NULL, $rentAmount, $des, 'DR', $payment_mode, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $ssbRentTranID, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $company_id);

                        $description_dr = $rentPaymentDetail['rentLib']->owner_name . 'A/c Dr ' . $rentAmount . '/-';
                        $description_cr = 'To SSB(' . $ssbAccount . ') A/c Cr ' . $rentAmount . '/-';

                        $brDaybook = CommanController::branchDaybookCreateModified($refId, $branch_id, $type, $sub_type, $type_id, $associate_id = NULL, $member_id = NULL, $branch_id_to = NULL, $branch_id_from = NULL, $rentAmount, $des, $description_dr, $description_cr, 'DR', $payment_mode, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $rentPaymentId, $ssb_account_id_to, $company_id);
                    }
                }
            } else {
                /// bank
                $bank_id_from_c = $request->bank_id;
                $bank_ac_id_from_c = $request->account_id;
                $bankBla = \App\Models\BankBalance::where('bank_id', $bank_id_from_c)->where('account_id', $bank_ac_id_from_c)->whereDate('entry_date', '<=', $entry_date)->where('company_id', $company_id)->orderby('entry_date', 'desc')->sum('totalAmount');
                if ($bankBla) {
                    if ($request->total_transfer_amount > $bankBla) {
                        return redirect('admin/rent/ledger-payable/' . $ledger_id)->with('alert', 'Sufficient amount not available in bank account!');
                    }
                } else {
                    return redirect('admin/rent/ledger-payable/' . $ledger_id)->with('alert', 'Sufficient amount not available in bank account!');
                }

                //echo $bankBla->balance;die;      
                if (isset($_POST['rent_id'])) {

                    foreach (($_POST['rent_id']) as $key => $option) {
                        $rentPaymentId = $_POST['rent_id'][$key];
                        $rentPaymentDetail = \App\Models\RentPayment::with('rentLib')->where('id', $rentPaymentId)->first();
                        $rentPaymentBalance = $rentPaymentDetail->balance;
                        $rentLiabilityBalance = $rentPaymentDetail['rentLib']->bill_current_balance;
                        $branch_id = $rentPaymentDetail->branch_id;
                        $branchCode = getBranchCode($branch_id)->branch_code;
                        $rentAmount = $rentPaymentDetail->actual_transfer_amount - $rentPaymentDetail->transferred_amount;

                        $totalPaybaleAmount = $rentPaymentDetail->transferred_amount + $rentAmount;

                        $detail = 'Rent Payment ' . $rentPaymentDetail->month_name . ' ' . $rentPaymentDetail->year;
                        $bank_name_to = $rentPaymentDetail->owner_bank_name;
                        $bank_ac_to = $rentPaymentDetail->owner_bank_account_number;
                        $bank_ifsc_to = $rentPaymentDetail->owner_bank_ifsc_code;
                        //-------------------
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
                        $bank_id_ac =  $bank_ac_id = $bank_ac_id_from;
                        $payment_type = 'CR';
                        //------------
                        $daybookRef = CommanController::createBranchDayBookReferenceNew($rentAmount, $globaldate);
                        $refId = $daybookRef;
                        //------------
                        $rentLiabilityId = $rentPaymentDetail['rentLib']->id;
                        $lib_current_balance = $rentPaymentDetail['rentLib']->current_balance;
                        $des = $rentPaymentDetail['rentLib']->owner_name . "'s " . $rentPaymentDetail->month_name . '' . $rentPaymentDetail->year . ' rent payment has been transferred to the ' . $bank_name_to . ' A/c' . $bank_ac_to;
                        $data['transferred_date']   = $entry_date;
                        $data['transfer_mode']      = $request->amount_mode;
                        $data['company_bank_id']    = $bank_id_from = $request->bank_id;
                        $data['company_bank_ac_id'] = $bank_ac_id_from = $request->account_id;
                        $data['transfer_amount'] = $totalPaybaleAmount;
                        $data['transferred_amount'] = $totalPaybaleAmount;
                        $data['payment_ref_id'] = $refId;
                        $data['balance'] = $rentPaymentBalance - $totalPaybaleAmount;
                        $paymentMode = $request->payment_mode;
                        $bankfrmDetail = \App\Models\SamraddhBank::where('id', $bank_id_from)->first();
                        $bankacfrmDetail = \App\Models\SamraddhBankAccount::where('bank_id', $bank_id_from)->first();
                        if ($request->payment_mode == 1) {
                            $data['company_cheque_id'] = $cheque_id = $request->cheque_id;
                            $data['company_cheque_no'] = $cheque_no = $request->cheque_number;
                            $cheque_date = $entry_date;
                            $rentLibLedger['cheque_id'] = $cheque_id;
                            $rentLibLedger['cheque_no'] = $cheque_no;
                            $rentLibLedger['cheque_date'] = $cheque_date;
                            $cheque_type = 1;
                            $cheque_id = $cheque_id;
                            $cheque_no = $cheque_no;
                            $cheque_date = $cheque_date;
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
                            //---------- Bill Payment --------
                            $bill['payment_mode'] = 1;
                            $bill['cheque_id_company'] = $cheque_id;
                            $bill['cheque_no_company'] = $cheque_no;
                            $bill['cheque_date'] = $cheque_date;

                            $bill['to_bank_name'] = $cheque_bank_to_name;
                            $bill['to_bank_branch'] = $cheque_bank_to_branch;
                            $bill['to_bank_ac_no'] = $cheque_bank_to_ac_no;
                            $bill['to_bank_ifsc'] = $cheque_bank_to_ifsc;
                            $bill['to_bank_id'] = $cheque_bank_to;
                            $bill['to_bank_account_id'] = $cheque_bank_ac_to;
                            $bill['from_bank_name'] = $cheque_bank_from;
                            $bill['from_bank_branch'] = $cheque_bank_branch_from;
                            $bill['from_bank_ac_no'] = $cheque_bank_ac_from;
                            $bill['from_bank_ifsc'] = $cheque_bank_ifsc_from;
                            $bill['from_bank_id'] = $cheque_bank_from_id;
                            $bill['from_bank_ac_id'] = $cheque_bank_ac_from_id;
                            //-----------------------
                            $chequeIssue['cheque_id'] = $cheque_id;
                            $chequeIssue['type'] = 5;
                            $chequeIssue['sub_type'] = 51;
                            $chequeIssue['type_id'] = $type_id;
                            $chequeIssue['cheque_issue_date'] = $entry_date;
                            $chequeIssue['created_at'] = $created_at;
                            $chequeIssue['updated_at'] = $updated_at;
                            $chequeIssue['updated_at'] = $updated_at;
                            $chequeIssueCreate = \App\Models\SamraddhChequeIssue::create($chequeIssue);
                            //------------------ 
                            $chequeUpdate['is_use'] = 1;
                            $chequeUpdate['status'] = 3;
                            $chequeUpdate['updated_at'] = $updated_at;
                            $chequeDataUpdate = \App\Models\SamraddhCheque::find($cheque_id);
                            $chequeDataUpdate->update($chequeUpdate);
                            $data['payment_mode']       = 1;
                        } else {
                            $data['online_transaction_no'] = $transction_no = $request->utr_tran;
                            $data['neft_charge'] = $neft_charge = $request->neft_charge;
                            $transaction_date = $entry_date;
                            $rentLibLedger['transaction_no'] = $transction_no;
                            $rentLibLedger['transaction_date'] = $transaction_date;
                            $rentLibLedger['transaction_charge'] = $neft_charge;

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
                            //---------- Bill Payment --------
                            $bill['payment_mode'] = 2;
                            $bill['transaction_no'] = $transction_no;
                            $bill['transaction_date'] = $transaction_date;
                            $bill['transaction_charge'] = $neft_charge;
                            $bill['to_bank_name'] = $transction_bank_to_name;
                            $bill['to_bank_branch'] = $transction_bank_to_branch;
                            $bill['to_bank_ac_no'] = $transction_bank_to_ac_no;
                            $bill['to_bank_ifsc'] = $transction_bank_to_ifsc;
                            $bill['to_bank_id'] = $transction_bank_to;
                            $bill['to_bank_account_id'] = $transction_bank_ac_to;
                            $bill['from_bank_name'] = $transction_bank_from;
                            $bill['from_bank_branch'] = $transction_bank_branch_from;
                            $bill['from_bank_ac_no'] = $transction_bank_ac_from;
                            $bill['from_bank_ifsc'] = $transction_bank_ifsc_from;
                            $bill['from_bank_id'] = $transction_bank_from_id;
                            $bill['from_bank_ac_id'] = $transction_bank_from_ac_id;

                            $data['payment_mode']       = 0;
                        }
                        $data['status']             = 1;
                        $data['updated_at'] = $request->created_at;
                        $rentPaymentUpdate = \App\Models\RentPayment::find($rentPaymentId);
                        $rentPaymentUpdate->update($data);
                        $total_transfer_amount = $total_transfer_amount + ($rentPaymentDetail->actual_transfer_amount - $rentPaymentDetail->transferred_amount);
                        //----------------------------------- 
                        // $rentGet=\App\Models\RentLiabilityLedger::where('rent_liability_id',$rentLiabilityId)->whereDate('created_at','<=',$entry_date)->orderby('created_at','DESC')->first(); 
                        $rentLibLedger['rent_liability_id'] = $rentLiabilityId;
                        $rentLibLedger['type'] = 1;
                        $rentLibLedger['type_id'] = $rentPaymentId;

                        $rentLibLedger['withdrawal'] = $rentAmount;
                        $rentLibLedger['description'] = $detail;
                        $rentLibLedger['currency_code'] = $currency_code;
                        $rentLibLedger['payment_type'] = 'DR';
                        $rentLibLedger['payment_mode'] = $paymentMode;
                        $rentLibLedger['created_at'] = $created_at;
                        $rentLibLedger['updated_at'] = $updated_at;
                        $rentLibLedger['daybook_ref_id'] = $refId;
                        $rentLibLedger['company_id'] = $company_id;
                        $rlL = \App\Models\RentLiabilityLedger::create($rentLibLedger);
                        //------------------------libility -(mines)  ------------
                        $head11 = 1;
                        $head21 = 8;
                        $head31 = 21;
                        $head41 = 60;
                        $head51 = NULL;
                        $allTran1 = CommanController::newHeadTransactionCreate($refId, $branch_id, $bank_id, $bank_ac_id, $head41, $type, $sub_type, $type_id, $associate_id = NULL, $member_id = NULL, $branch_id_to = NULL, $branch_id_from = NULL,  $rentAmount, $des, 'DR', $paymentMode, $currency_code,  $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $rentPaymentId, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $company_id);

                        $description_dr = $rentPaymentDetail['rentLib']->owner_name . 'A/c Dr ' . $rentAmount . '/-';
                        $description_cr = 'To ' . $bankfrmDetail->bank_name . '(' . $bankacfrmDetail->account_no . ') A/c Cr ' . $rentAmount . '/-';
                        // ---------------- branch daybook entry -----------------
                        $brDaybook = CommanController::branchDaybookCreateModified($refId, $branch_id, $type, $sub_type, $type_id, $associate_id = NULL, $member_id = NULL, $branch_id_to = NULL, $branch_id_from = NULL, $rentAmount, $des, $description_dr, $description_cr, 'DR', $paymentMode, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $rentPaymentId, $ssb_account_id_to, $company_id);
                        // ------------------ samraddh bank entry -(mines) ---------------
                        $bankAmountRent =  $rentAmount;
                        $neftAmount = $neft_charge;
                        $description_dr_b = $rentPaymentDetail['rentLib']->owner_name . 'A/c Dr ' . $bankAmountRent . '/-';
                        $description_cr_b = 'To ' . $bankfrmDetail->bank_name . '(' . $bankacfrmDetail->account_no . ') A/c Cr ' . $bankAmountRent . '/-';


                        $allTran2 = CommanController::newHeadTransactionCreate($refId, $branch_id, $bank_id, $bank_ac_id, $bankfrmDetail->account_head_id, $type, $sub_type, $type_id, $associate_id = NULL, $member_id = NULL, $branch_id_to = NULL, $branch_id_from = NULL, $bankAmountRent, $des, 'CR', $paymentMode, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $rentPaymentId, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $company_id);


                        $smbdc = CommanController::NewFieldAddSamraddhBankDaybookCreateModify($refId, $bank_id_from, $bank_ac_id_from, $type, $sub_type, $type_id, $associate_id = NULL, $member_id = NULL, $branch_id, $opening_balance = NULL, $bankAmountRent, $closing_balance = NULL, $des, $description_dr_b, $description_cr_b, 'DR', $paymentMode, $currency_code, $amount_to_id = NULL, $amount_to_name = NULL, $amount_from_id = NULL, $amount_from_name = NULL, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $rentPaymentId, $ssb_account_id_to, $cheque_bank_from_id, $cheque_bank_ac_from_id, $cheque_bank_to_name, $cheque_bank_to_branch, $cheque_bank_to_ac_no, $cheque_bank_to_ifsc, $transction_bank_from_id, $transction_bank_from_ac_id, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $jv_unique_id, $cheque_type, $cheque_id, $company_id);
                        //-----------   bank balence  ---------------------------

                        // if ($current_date == $entry_date) {
                        //     $bankClosing = CommanController::checkCreateBankClosingDR($bank_id_from, $bank_ac_id_from, $created_at, $rentAmount, 0);
                        //     // //-----------   barnch clossing balence  ---------------------------                    
                        //     //     $branchClosing=CommanController:: checkCreateBranchClosingDr($branch_id,$created_at,$rentAmount,0);
                        // } else {
                        //     $bankClosing = CommanController::checkCreateBankClosingDRBackDate($bank_id_from, $bank_ac_id_from, $created_at, $rentAmount, 0);
                        //     // //-----------   barnch clossing balence  ---------------------------   
                        //     //     $branchClosing=CommanController:: checkCreateBranchClosingBackdateDR($branch_id,$created_at,$rentAmount,0);
                        // }
                    }
                }
            }
            // bank charge head entry +
            if ($request->amount_mode == 2) {
                if ($request->neft_charge > 0) {
                    $allTranSSB = CommanController::newHeadTransactionCreate($refId, '29', $bank_id, $bank_ac_id, 92, $type, $sub_type, $type_id, $associate_id = NULL, $member_id = NULL, $branch_id_to = NULL, $branch_id_from = NULL, $request->neft_charge, $des, 'DR', $paymentMode, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $rentPaymentId, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $company_id);

                    $allTran2 = CommanController::newHeadTransactionCreate($refId, '29', $bank_id, $bank_ac_id, $bankfrmDetail->account_head_id, $type, $sub_type, $type_id, $associate_id = NULL, $member_id = NULL, $branch_id_to = NULL, $branch_id_from = NULL, $request->neft_charge, $des, 'CR', $paymentMode, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $rentPaymentId, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $company_id);


                    $smbdc = CommanController::NewFieldAddSamraddhBankDaybookCreateModify($refId, $bank_id_from, $bank_ac_id_from, $type, $sub_type, $type_id, $associate_id = NULL, $member_id = NULL, '29', $opening_balance = NULL, $request->neft_charge, $closing_balance = NULL, $des, $description_dr_b, $description_cr_b, 'DR', $paymentMode, $currency_code, $amount_to_id = NULL, $amount_to_name = NULL, $amount_from_id = NULL, $amount_from_name = NULL, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $rentPaymentId, $ssb_account_id_to, $cheque_bank_from_id, $cheque_bank_ac_from_id, $cheque_bank_to_name, $cheque_bank_to_branch, $cheque_bank_to_ac_no, $cheque_bank_to_ifsc, $transction_bank_from_id, $transction_bank_from_ac_id, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $jv_unique_id, $cheque_type, $cheque_id, $company_id);
                }
            }

            $payment_count = \App\Models\RentPayment::where('ledger_id', $ledger_id)->where('status', 1)->get();
            $total_payment_count = \App\Models\RentPayment::where('ledger_id', $ledger_id)->get();
            $l = \App\Models\RentLedger::where('id', $ledger_id)->first();
            if ($l->transfer_amount > 0) {
                $ledgertransfer_amount = $total_transfer_amount + $l->transfer_amount;
            } else {
                $ledgertransfer_amount = $total_transfer_amount;
            }

            $updateLedger['transfer_amount'] = $ledgertransfer_amount;

            if (count($payment_count) == count($total_payment_count)) {
                $updateLedger['status'] = 1;
            } else {
                $updateLedger['status'] = 2;
            }

            $leaserdataUpdate = \App\Models\RentLedger::find($ledger_id);
            $leaserdataUpdate->update($updateLedger);

            $sumTransferredAmount = \App\Models\RentPayment::where('rent_liability_id', $rentPaymentDetail['rentLib']->id)->sum('transferred_amount');
            $sumActualTransferAmount = \App\Models\RentPayment::where('rent_liability_id', $rentPaymentDetail['rentLib']->id)->sum('actual_transfer_amount');
            $libilityBalance = $sumActualTransferAmount - $sumTransferredAmount;


            $lib['current_balance'] = $libilityBalance;
            $libUpdate = RentLiability::find($rentPaymentDetail['rentLib']->id);
            $libUpdate->update($lib);



            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            dd($ex->getLine());
            return redirect('admin/rent/rent-ledger')->with('alert', $ex->getMessage());
        }

        return redirect('admin/rent/rent-ledger')->with('success', 'Rent Payment Transferred Successfully');
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
        $ledger = \App\Models\RentLedger::where('id', $id)->first();
        if ($ledger->status == 0) {
            $get_rId = \App\Models\AllHeadTransaction::where('type_id', $id)->first();
            if ($get_rId) {
                $idref = $get_rId->daybook_ref_id;
                $allTransaction = \App\Models\AllHeadTransaction::where('type_id', $id)->where('type', 10)->where('sub_type', 101)->update(['is_deleted' => 1]);
                // $branchDaybookReference = \App\Models\BranchDaybookReference::where('id', $idref)->delete();
            }
            $payment_count = \App\Models\RentPayment::where('ledger_id', $id)->update(['is_deleted' => 1]);
            $billDelete = VendorBillPayment::where('rent_ledger_id', $id)->update(['is_deleted' => 1]);
            $deleteApplicaton = \App\Models\RentLedger::where('id', $id)->update(['is_deleted' => 1]);
            $msg = "Ledger Deleted  Successfully";
            $data = 1;
        } else {
            $msg = "Ledger can't deleted because payment already transferred ";
            $data = 2;
        }
        $return_array = compact('data', 'msg', 'id');
        return json_encode($return_array);
    }
    /**
     * Display a listing of the account heads.
     *
     * @return \Illuminate\Http\Response
     */
    public function ledgerReport($id)
    {
        $data['title'] = 'Rent Management | Rent Ledger Report';
        // $data['branch'] = Branch::where('status', 1)->get();
        $data['leaserData'] = \App\Models\RentLedger::where('id', $id)->with('company:id,name')->first();
        $data['leaser_id'] = $id;
        // dd($data);
        $data['company_id'] = $data['leaserData']['company_id'];
        $data['company_name'] = $data['leaserData']['company']['name'];
        return view('templates.admin.rent-management.rent_ledger_report', $data);
    }
    /**
     * Display the specified resource.
     *
     * @param  \App\Reservation  $reservation
     * @return \Illuminate\Http\Response
     */
    public function rentLedgerReportListing(Request $request)
    {
        if ($request->ajax()) {
            $is_search = $request->is_search;
            $branch_id = $request->branch_id;
            $company_id = $request->company_id;
            $status = $request->status;
            $ledger_id = $request->ledger_id;
            $data = \App\Models\RentPayment::select('id', 'year', 'rent_liability_id', 'branch_id', 'company_bank_id', 'company_bank_ac_id', 'owner_ssb_id', 'employee_id', 'rent_liability_id', 'owner_bank_name', 'owner_bank_account_number', 'owner_bank_ifsc_code', 'security_amount', 'rent_amount', 'yearly_increment', 'status', 'current_advance_payment', 'actual_transfer_amount', 'transfer_amount', 'advance_payment', 'settle_amount', 'transferred_date', 'v_date', 'v_no', 'transfer_mode', 'payment_mode', 'company_cheque_id', 'company_cheque_no', 'online_transaction_no', 'neft_charge', 'tds_amount', 'company_id', 'payment_ref_id', 'part_payment_ref_id')->with(['rentLib' => function ($q) {
                $q->with(['AcountHeadCustom' => function ($q) {
                    $q->select('id', 'head_id', 'sub_head');
                }]);
            }])->with('rentCompany:id,name')->with(['rentSSB' => function ($q) {
                $q->select('id', 'account_no');
            }])->with(['rentBank' => function ($q) {
                $q->select('id', 'bank_name');
            }])->with(['rentBankAccount' => function ($q) {
                $q->select('id', 'account_no');
            }])->with(['rentBranch' => function ($query) {
                $query->select('id', 'name', 'branch_code', 'sector', 'regan', 'zone');
            }])->with(['rentEmp' => function ($query) {
                $query->select('id', 'designation_id', 'employee_code', 'employee_name', 'mobile_no')->with(['designation' => function ($q) {
                    $q->select('id', 'designation_name');
                }]);
            }])->where('ledger_id', $ledger_id);
            if ($is_search == 'yes') {
                if ($branch_id > 0) {
                    $data = $data->where('branch_id', $branch_id);
                }
                if ($company_id > 0) {
                    $data = $data->where('company_id', $company_id);
                }
                if ($status != '') {
                    $data = $data->where('status', $status);
                }
            }

            $data1 = $data->orderby('created_at', 'DESC')->count('id');
            $count = ($data1);
            $data = $data->orderby('created_at', 'DESC')->offset($_POST['start'])->limit($_POST['length'])->get();

            $count = ($data1);
            $totalCount = $count;
            $sno = $_POST['start'];
            $rowReturn = array();
            foreach ($data as $row) {

                $sno++;
                $val['DT_RowIndex'] = $sno;
                $val['company'] = isset($row['rentCompany']->name) ? $row['rentCompany']->name : "N/A";
                $val['branch'] = $row['rentBranch']->name . " (" . $row['rentBranch']->branch_code . ")";
                if ($row['rentLib']['AcountHeadCustom']) {
                    $val['rent_type'] = $row['rentLib']['AcountHeadCustom']->sub_head; //getAcountHead($row['rentLib']->rent_type);
                } else {
                    $val['rent_type'] = 'N/A';
                }

                $val['period_from'] = date("d/m/Y", strtotime(convertDate($row['rentLib']->agreement_from)));
                $val['period_to'] = date("d/m/Y", strtotime(convertDate($row['rentLib']->agreement_to)));
                $val['address'] = $row['rentLib']->place;
                $val['owner_name'] = $row['rentLib']->owner_name;
                $val['owner_mobile_number'] = $row['rentLib']->owner_mobile_number;
                $val['owner_pen_card'] = $row['rentLib']->owner_pen_number;
                $val['owner_aadhar_card'] = $row['rentLib']->owner_aadhar_number;
                $owner_ssb_account = '';
                if ($row['rentSSB']) {
                    $owner_ssb_account = isset($row['rentSSB']->account_no) ? $row['rentSSB']->account_no : "N/A";
                }
                $val['owner_ssb_account'] = $owner_ssb_account;
                $val['bank_name'] = $row->owner_bank_name;
                $val['bank_account_number'] = $row->owner_bank_account_number;
                $val['ifsc_code'] = $row->owner_bank_ifsc_code;
                $val['security_amount'] = number_format((float)$row->security_amount, 2, '.', '');
                $val['rent'] = number_format((float)$row->rent_amount, 2, '.', '');
                $val['yearly_increment'] = number_format((float)$row->yearly_increment, 2, '.', '') . '%';
                $val['office_area'] = $row['rentLib']->office_area;
                $val['employee_code'] = $row['rentEmp']->employee_code;
                $val['employee_name'] = $row['rentEmp']->employee_name;
                if ($row['rentEmp']['designation']) {
                    $val['employee_designation'] = $row['rentEmp']['designation']->designation_name; //getDesignationData('designation_name',$row['rentEmp']->designation_id)->designation_name;
                } else {
                    $val['employee_designation'] = 'N/A';
                }

                $val['mobile_number'] = $row['rentEmp']->mobile_no;
                $status = 'Pending';
                if ($row->status == 1) {
                    $status = 'Transferred ';
                }
                $val['status'] = $status;
                $current_advance_payment = 'N/A';
                if ($row->current_advance_payment) {
                    $current_advance_payment = number_format((float)$row->current_advance_payment, 2, '.', '');
                }
                $val['current_advance_payment'] = $current_advance_payment;
                $actual = 'N/A';
                if ($row->actual_transfer_amount) {
                    $actual = number_format((float)$row->actual_transfer_amount + $row->tds_amount, 2, '.', '');
                }
                $val['actual'] = $actual;
                $val['transfer'] = number_format((float)$row->transfer_amount, 2, '.', '');
                $val['tds_amount'] = number_format((float)$row->tds_amount, 2, '.', '');
                $advance = 'N/A';
                if ($row->advance_payment) {
                    $advance = number_format((float)$row->advance_payment, 2, '.', '');
                }
                $val['advance'] = $advance;
                $settle = 'N/A';
                if ($row->settle_amount) {
                    $settle = number_format((float)$row->settle_amount, 2, '.', '');
                }
                $val['settle'] = $settle;
                $transfer_date = 'N/A';
                if ($row->transferred_date) {
                    $transfer_date = date("d/m/Y", strtotime(convertDate($row->transferred_date)));
                }
                $val['transfer_date'] = $transfer_date;
                $v_date = 'N/A';
                if ($row->v_date) {
                    $v_date = date("d/m/Y", strtotime(convertDate($row->v_date)));
                }
                $val['v_date'] = $v_date;
                $v_no = 'N/A';
                if ($row->v_no) {
                    $v_no = $row->v_no;
                }
                $val['v_no'] = $v_no;
                $mode = 'N/A';

                if ($row->transfer_mode == 1) {
                    $mode = 'SSB/JV';
                }
                if ($row->transfer_mode == 2) {
                    $mode = 'Bank';
                }
                $val['mode'] = $mode;
                $bank = 'N/A';
                if ($row->company_bank_id) {
                    $bank = $row['rentBank']->bank_name;
                }
                $val['bank'] = $bank;
                $bank_ac = 'N/A';
                if ($row->company_bank_ac_id) {
                    $bank_ac = $row['rentBankAccount']->account_no;
                }
                $val['bank_ac'] = $bank_ac;
                $payment_mode = 'N/A';
                if ($row->payment_mode == 1) {
                    $payment_mode = 'Cheque';
                }
                if ($row->payment_mode == '0') {
                    $payment_mode = 'Online';
                }
                $val['payment_mode'] = $payment_mode;
                $cheques = 'N/A';

                if ($row->company_cheque_id) {
                    $cheques = $row->company_cheque_no;
                }
                $val['cheque'] = $cheques;
                $online_no = 'N/A';
                if ($row->online_transaction_no) {
                    $online_no = $row->online_transaction_no;
                }
                $val['online_no'] = $online_no;
                $neft = 'N/A';
                if ($row->neft_charge) {
                    $neft = number_format((float)$row->neft_charge, 2, '.', '');
                }
                $val['neft'] = $neft;
                $btn = '<div class="list-icons"><div class="dropdown"><a href="#" class="list-icons-item" data-toggle="dropdown"><i class="icon-menu9"></i></a><div class="dropdown-menu dropdown-menu-right">';
                $ur_edit = URL::to("admin/rent/ledger-edit/" . base64_encode($row->id) . "");
                if ($row->status == 1 && check_my_permission(Auth::user()->id, "362") == "1") {
                    if ($row->year >= 2023) {
                        if (isset($row->payment_ref_id) && $row->part_payment_ref_id != 0 && RentPayment::where('payment_ref_id', $row->payment_ref_id)->count() > 1) {

                            // Fetch employees with their salary details
                            $emp_names = RentPayment::where('payment_ref_id', $row->payment_ref_id)
                                ->with('rentLib:id,owner_name')
                                // ->select('employee_id')
                                ->get();
                            $employeeNames = $emp_names->pluck('rentLib.owner_name')->implode(',');
                            // $employeeNames now contains an array of employee names
                            $btn .= '<span class="dropdown-item" onclick="DeleteRentm(this)" data-sal="' . base64_encode($row->payment_ref_id) . '" data-emp="' . $employeeNames . '" title="Delete rent payment"><i class="fas fa-trash-alt mr-2"></i>Delete multiple payment</span>';
                        } else {
                            $btn .= '<span class="dropdown-item" onclick="DeleteRent(this)" data-sal="' . base64_encode($row->id) . '" title="Delete rent payment"><i class="fas fa-trash-alt mr-2"></i>Delete payment</span>';
                        }
                    }
                } elseif ($row->status == 0 && check_my_permission(Auth::user()->id, "361") == "1") {
                    $btn .= '<a class="dropdown-item" href="' . $ur_edit . '" title="Edit rent"><i class="far fa-edit  mr-2"></i>Edit</a>  ';
                } 
                // else {
                //     if ($row->year >= 2023 && check_my_permission(Auth::user()->id, "362") == "1") {
                //         $btn .= '<span class="dropdown-item" onclick="DeleteRent(this)" data-sal="' . base64_encode($row->id) . '" title="Delete rent payment"><i class="fas fa-trash-alt mr-2"></i>Delete payment</span>';
                //     }
                // }
                $btn .= '</div></div></div>';
                if(Auth::user()->id == 1 || Auth::user()->id == 16 || Auth::user()->id == 14){
                    $val['action'] = $btn;
                } else {
                    $val['action'] = 'n/a';
                }
                $rowReturn[] = $val;
            }
            $output = array("draw" => $_POST['draw'], "recordsTotal" => $totalCount, "recordsFiltered" => $count, "data" => $rowReturn);
            return json_encode($output);
        }
    }
    public function advancePayble($id, $leaser_id)
    {

        $data['title'] = 'Rent Management | Rent Advance Adjustment';
        $data['leaser_id'] = $leaser_id;
        //print_r($_POST);die;
        $data['rent_list'] = \App\Models\RentPayment::with('rentLib')->with('rentSSB')->with(['rentBranch' => function ($query) {
            $query->select('id', 'name', 'branch_code', 'sector', 'regan', 'zone');
        }])->with(['rentEmp' => function ($query) {
            $query->select('id', 'designation_id', 'employee_code', 'employee_name', 'mobile_no');
        }])->with('rentCompany:id,name')->where('id', $id)->first();
        $data['c_id'] = ($data['rent_list']['rentCompany']['id']) ?? $data['rent_list'][0]['company_id'];

        $data['bank'] = \App\Models\SamraddhBank::where('status', 1)->where('company_id', $data['c_id'])->get();
        $check_data = \App\Models\RentLedger::where('id', $leaser_id)->first();
        $data['ledger_date'] = date("d/m/Y", strtotime($check_data->created_at));
        
        if ($data['rent_list']->actual_transfer_amount == $data['rent_list']->transferred_amount) {
            return redirect('admin/rent/ledger-payable/' . $leaser_id)->with('alert', 'Payment has already done');
        }
        $compdate = date("Y/m/d", strtotime($check_data->created_at));
        $advanceBalance = AdvancedTransaction::where('type',3)->where('sub_type',31)->where('status',1)->where('type_id',$data['rent_list']['rentLib']['id'])->where('status_date','<=',$compdate)->where('settle_amount','>',0)->select('settle_amount')->get();
        $data['advanceAmount'] = 0;
        foreach($advanceBalance as $advanceAmount){
            $data['advanceAmount'] += $advanceAmount['settle_amount'];
        }
        return view('templates.admin.rent-management.transfer_advance_save', $data);
    }
    // public function rentTransferAdvanceSave(Request $request)
    // {
    //     // print_r($_POST);die;
    //     $rules = [
    //         'amount_mode' => ['required'],
    //         'total_transfer_amount' => ['required'],
    //         'leaser_id' => ['required'],
    //         // 'bank_id' => ['required'], 
    //         //   'account_id' => ['required'], 
    //         'advance_payment' => ['required'],
    //         'actual_transfer' => ['required'],
    //         'security_amount' => ['required'],
    //         'advance_settel' => ['required'],
    //         'security_settel' => ['required'],
    //         'transfer_amount' => ['required'],
    //     ];
    //     $customMessages = [
    //         'required' => ':Attribute  is required.',
    //     ];
    //     $this->validate($request, $rules, $customMessages);
    //     DB::beginTransaction();
    //     try {
    //         $ledger_id = $leaser = $request->leaser_id;
    //         $globaldate = $request->created_at;
    //         $total_transfer_amount = 0;
    //         $select_date = $request->select_date;
    //         $current_date = date("Y-m-d", strtotime(convertDate($globaldate)));
    //         $entry_date = date("Y-m-d", strtotime(convertDate($select_date)));
    //         $entry_time = date("H:i:s", strtotime(convertDate($globaldate)));
    //         $date_create = $entry_date . ' ' . $entry_time;
    //         $created_at = date("Y-m-d H:i:s", strtotime(convertDate($date_create)));
    //         $updated_at = date("Y-m-d H:i:s", strtotime(convertDate($date_create)));
    //         Session::put('created_at', $created_at);
    //         $currency_code = 'INR';
    //         $created_by = 1;
    //         $created_by_id = \Auth::user()->id;
    //         $created_by_name = \Auth::user()->username;
    //         $randNumber = mt_rand(0, 999999999999999);
    //         $v_no = $randNumber;
    //         $v_date = $entry_date;
    //         // head  entry----------------------------                    
    //         $type = 10;
    //         $sub_type = 102;
    //         $type_id = $leaser;
    //         if($request->transfer_amount==0)
    //         {
    //             //ksjdfnsdkjfsdkjfbdskjfbsdkjfbkj
    //         }
    //         else
    //         {

    //             if ($request->amount_mode == 1) {                    //ssb  
    //                 $rentPaymentId = $request->rentPaymentId;
    //                 $rentPaymentDetail = \App\Models\RentPayment::with('rentLib')->where('id', $rentPaymentId)->first();
    //                 $rentPaymentBalance = $rentPaymentDetail->balance;
    //                 $rentLiabilityBalance = $rentPaymentDetail['rentLib']->bill_current_balance;
    //                 $rentAmount = $rentPaymentDetail->actual_transfer_amount - $rentPaymentDetail->transferred_amount;
    //                 $rentAmount1 = $request->transfer_amount;

    //                 $daybookRef = CommanController::createBranchDayBookReferenceNew($rentAmount, $globaldate);
    //                 $refId = $daybookRef;

    //                 $data['transferred_date']   = $entry_date;
    //                 $data['security_amount']   = $request->security_amount;
    //                 $data['settle_amount']      = $request->advance_settel + $request->security_settel;
    //                 $data['advance_payment']   = $request->advance_payment;
    //                 $data['current_advance_payment'] = $rlldata['advance_payment'] = $request->advance_payment - $request->advance_settel;
    //                 $data['transfer_amount']   = $request->transfer_amount;
    //                 $data['transfer_mode']      = $request->amount_mode;
    //                 $data['v_no']               = $v_no;
    //                 $data['v_date']             = $entry_date;
    //                 $data['status']             = 1;
    //                 $data['transferred_amount'] = $rentPaymentDetail->actual_transfer_amount - $rentPaymentDetail->transferred_amount;
    //                 $data['balance'] = $rentPaymentBalance - ($rentPaymentDetail->actual_transfer_amount - $rentPaymentDetail->transferred_amount);
    //                 $data['payment_ref_id'] = $refId;
    //                 $data['updated_at'] = $request->created_at;
    //                 $rentPaymentUpdate = \App\Models\RentPayment::find($rentPaymentId);
    //                 $rentPaymentUpdate->update($data);
    //                 $total_transfer_amount = $rentPaymentDetail->actual_transfer_amount;
    //                 $branch_id = $rentPaymentDetail->branch_id;
    //                 $branchCode = getBranchCode($branch_id)->branch_code;

    //                 $ssbAccountDetail = getSavingAccountMemberId($rentPaymentDetail->owner_ssb_id);
    //                 $ssbBalance = $ssbAccountDetail->balance;
    //                 $member_id = $ssbAccountDetail->member_id;
    //                 $ssbId = $ssbAccountDetail->id;
    //                 $ssbAccount = $ssbAccountDetail->account_no;
    //                 //------------ ssb tran head entry start  --------------------------
    //                 $detail = 'Rent Payment ' . $rentPaymentDetail->month_name . ' ' . $rentPaymentDetail->year;
    //                 /// $ssbTranCalculation = CommanController::ssbTransactionModify($ssbId,$ssbAccount,$ssbBalance,$rentAmount1,$detail,'INR','CR',3,$branch_id,$associate_id=NULL,7);
    //                 $ssbTranCalculation = CommanController::SSBDateCR($ssbId, $ssbAccount, $ssbBalance, $rentAmount1, $detail, 'INR', 'CR', 3, $branch_id, $associate_id = NULL, 7, $created_at);
    //                 $SSbbck = CommanController::SSBBackDateCR($ssbId, $created_at, $rentAmount);
    //                 $ssbRentTranID = $ssbTranCalculation;
    //                 $amountArray = array('1' => $rentAmount1);
    //                 $deposit_by_name = $created_by_name;
    //                 $deposit_by_id = $created_by_id;
    //                 $rdCreateTran = CommanController::createTransaction(NULL, 1, $ssbAccountDetail->id, $member_id, $branch_id, $branchCode, $amountArray, 77, $deposit_by_name, $deposit_by_id, $ssbAccountDetail->account_no, $cheque_dd_no = 0, $bank_name = null, $branch_name = null, $payment_date = null, $online_payment_id = null, $online_payment_by = null, $saving_account_id = 0, 'CR');
    //                 //-------------------- ssb head entry start -----------------
    //                 $payment_mode = 3;
    //                 $payment_type = 'CR';

    //                 $rentLiabilityId = $rentPaymentDetail['rentLib']->id;
    //                 $lib_current_balance = $rentPaymentDetail['rentLib']->current_balance;
    //                 $des = $rentPaymentDetail['rentLib']->owner_name . "'s " . $rentPaymentDetail->month_name . '' . $rentPaymentDetail->year . ' rent payment has been transferred to the SSB A/c' . $ssbAccount;
    //                 $head1SSB = 1;
    //                 $head2SSB = 8;
    //                 $head3SSB = 20;
    //                 $head4SSB = 56;
    //                 $head5SSB = NULL;
    //                 $ssb_account_id_from = NULL;
    //                 $cheque_no = NULL;
    //                 $cheque_date = NULL;
    //                 $cheque_bank_from = NULL;
    //                 $cheque_bank_ac_from = NULL;
    //                 $cheque_bank_ifsc_from = NULL;
    //                 $cheque_bank_branch_from = NULL;
    //                 $cheque_bank_to = NULL;
    //                 $cheque_bank_ac_to = NULL;
    //                 $transction_no = NULL;
    //                 $transction_bank_from = NULL;
    //                 $transction_bank_ac_from = NULL;
    //                 $transction_bank_ifsc_from = NULL;
    //                 $transction_bank_branch_from = NULL;
    //                 $transction_bank_to = NULL;
    //                 $transction_bank_ac_to = NULL;
    //                 $transction_date = NULL;
    //                 $ssb_account_id_to = $ssbId;
    //                 $cheque_bank_from_id = NULL;
    //                 $cheque_bank_ac_from_id = NULL;
    //                 $cheque_bank_to_name = NULL;
    //                 $cheque_bank_to_branch = NULL;
    //                 $cheque_bank_to_ac_no = NULL;
    //                 $cheque_bank_to_ifsc = NULL;
    //                 $transction_bank_from_id = NULL;
    //                 $transction_bank_from_ac_id = NULL;
    //                 $transction_bank_to_name = NULL;
    //                 $transction_bank_to_ac_no = NULL;
    //                 $transction_bank_to_branch = NULL;
    //                 $transction_bank_to_ifsc = NULL;
    //                 $jv_unique_id = NULL;
    //                 $ssb_account_tran_id_to = $ssbRentTranID;
    //                 $ssb_account_tran_id_from = NULL;
    //                 $cheque_type = NULL;
    //                 $cheque_id = NULL;
    //                 $amount_to_id = NULL;
    //                 $amount_to_name = NULL;
    //                 $amount_from_id = NULL;
    //                 $amount_from_name = NULL;
    //                 $bank_id = NULL;
    //                 $bank_ac_id = NULL;
    //                 // ssb head entry +
    //                 $allTranSSB = CommanController::headTransactionCreate($refId, $branch_id, $bank_id, $bank_ac_id, $head4SSB, 4, 49, $ssbId, $associate_id = NULL, $member_id, $branch_id_to = NULL, $branch_id_from = NULL, $opening_balance = NULL, $rentAmount1, $closing_balance = NULL, $des, 'CR', $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $ssbRentTranID, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $cheque_bank_to_name, $cheque_bank_to_branch, $cheque_bank_to_ac_no, $cheque_bank_to_ifsc, $transction_bank_from_id, $transction_bank_from_ac_id, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc, $cheque_bank_from_id, $cheque_bank_ac_from_id);
    //                 // ssb Member transaction  +
    //                 $memberTranSSB = CommanController::NewFieldAddMemberTransactionCreate($refId, 4, 49, $ssbId, $associate_id = NULL, $member_id, $branch_id, $bank_id = NULL, $account_id = NULL, $rentAmount1, $des, $payment_type, $payment_mode, $currency_code, $amount_to_id = NULL, $amount_to_name = NULL, $amount_from_id = NULL, $amount_from_name = NULL, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $ssbRentTranID, $ssb_account_id_to, $cheque_bank_from_id, $cheque_bank_ac_from_id, $cheque_bank_to_name, $cheque_bank_to_branch, $cheque_bank_to_ac_no, $cheque_bank_to_ifsc, $transction_bank_from_id, $transction_bank_from_ac_id, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $jv_unique_id, $cheque_type, $cheque_id);
    //                 //------------ ssb tran head entry end --------------------------
    //                 $ssb_account_id_to = $ssbAccountDetail->id;
    //                 // $rentGet=\App\Models\RentLiabilityLedger::where('rent_liability_id',$rentLiabilityId)->whereDate('created_at','<=',$entry_date)->orderby('created_at','DESC')->first(); 
    //                 $rentLibLedger['rent_liability_id'] = $rentLiabilityId;
    //                 $rentLibLedger['type'] = 1;
    //                 $rentLibLedger['type_id'] = $rentPaymentId;
    //                 $rentLibLedger['withdrawal'] = $rentAmount1;
    //                 $rentLibLedger['description'] = $detail;
    //                 $rentLibLedger['currency_code'] = $currency_code;
    //                 $rentLibLedger['payment_type'] = 'DR';
    //                 $rentLibLedger['payment_mode'] = 3;
    //                 $rentLibLedger['v_no'] = $v_no;
    //                 $rentLibLedger['v_date'] = $v_date;
    //                 $rentLibLedger['ssb_account_id_to'] = $ssb_account_id_to;
    //                 $rentLibLedger['created_at'] = $created_at;
    //                 $rentLibLedger['updated_at'] = $updated_at;
    //                 $rentLibLedger['daybook_ref_id'] = $refId;
    //                 $rlL = \App\Models\RentLiabilityLedger::create($rentLibLedger);
    //                 // -------bill Payment -----------
    //                 //$billGet=VendorBillPayment::where('rent_id',$rentPaymentId)->whereDate('payment_date','<=',$entry_date)->orderby('payment_date','DESC')->first();   
    //                 // $bill['bill_type']=1; 
    //                 // $bill['rent_ledger_id']=$ledger_id;
    //                 // $bill['rent_owner_id']=$rentLiabilityId;
    //                 // $bill['rent_id']=$rentPaymentId;

    //                 // $bill['withdrawal']=$rentAmount;
    //                 // $bill['description']=$detail;
    //                 // $bill['currency_code']=$currency_code;
    //                 // $bill['payment_type']='DR';
    //                 // $bill['payment_mode']=$payment_mode;
    //                 // $bill['payment_date']=$v_date;
    //                 // $bill['ssb_account_id_to']=$ssb_account_id_to;
    //                 // $bill['v_no']=$v_no;
    //                 // $bill['v_date']=$v_date;
    //                 // $bill['created_at']=$created_at;
    //                 // $bill['updated_at']=$updated_at;
    //                 // $bill['daybook_ref_id']=$refId;
    //                 // $createBill = VendorBillPayment::create($bill);
    //                 // $rlldata['security_amount']= $rentPaymentDetail['rentLib']->security_amount-$request->security_settel;
    //                 // $rlldata['current_balance']= $lib_current_balance-$rentAmount1; 
    //                 // $rlldata['bill_current_balance']=$rentLiabilityBalance-$rentAmount; 
    //                 // $rlldata['updated_at']=$updated_at; 
    //                 // $rllUpdate = \App\Models\RentLiability::find($rentLiabilityId);
    //                 // $rllUpdate->update($rlldata); 
    //                 // $rentLedgerBackdate22=CommanController:: rentLedgerBackDateCR($rentLiabilityId,$created_at,$rentAmount);
    //                 ///---------------libility - (mines) ----------------
    //                 $head11 = 1;
    //                 $head21 = 8;
    //                 $head31 = 21;
    //                 $head41 = 60;
    //                 $head51 = NULL;
    //                 $allTran1 = CommanController::headTransactionCreate($refId, $branch_id, $bank_id, $bank_ac_id, $head41, $type, $sub_type, $type_id, $associate_id = NULL, $member_id = NULL, $branch_id_to = NULL, $branch_id_from = NULL, $opening_balance = NULL, $rentAmount, $closing_balance = NULL, $des, 'DR', $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $rentPaymentId, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $cheque_bank_to_name, $cheque_bank_to_branch, $cheque_bank_to_ac_no, $cheque_bank_to_ifsc, $transction_bank_from_id, $transction_bank_from_ac_id, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc, $cheque_bank_from_id, $cheque_bank_ac_from_id);
    //                 // ------ advance rent - (mines)----------------
    //                 if ($request->advance_settel > 0) {
    //                     $head11A = 2;
    //                     $head21A = 10;
    //                     $head31A = 29;
    //                     $head41A = 74;
    //                     $head51A = NULL;
    //                     $allTran14 = CommanController::headTransactionCreate($refId, $branch_id, $bank_id, $bank_ac_id, $head41A, $type, $sub_type, $type_id, $associate_id = NULL, $member_id = NULL, $branch_id_to = NULL, $branch_id_from = NULL, $opening_balance = NULL, $request->advance_settel, $closing_balance = NULL, $des, 'CR', $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $rentPaymentId, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $cheque_bank_to_name, $cheque_bank_to_branch, $cheque_bank_to_ac_no, $cheque_bank_to_ifsc, $transction_bank_from_id, $transction_bank_from_ac_id, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc, $cheque_bank_from_id, $cheque_bank_ac_from_id);

    //                     $rentLibLedgerAdvance['rent_liability_id'] = $rentLiabilityId;
    //                     $rentLibLedgerAdvance['type'] = 5;
    //                     $rentLibLedgerAdvance['type_id'] = $rentPaymentId;
    //                     $rentLibLedgerAdvance['deposit'] = $request->advance_settel;
    //                     $rentLibLedgerAdvance['description'] = $detail;
    //                     $rentLibLedgerAdvance['currency_code'] = $currency_code;
    //                     $rentLibLedgerAdvance['payment_type'] = 'CR';
    //                     $rentLibLedgerAdvance['payment_mode'] = 3;
    //                     $rentLibLedgerAdvance['v_no'] = $v_no;
    //                     $rentLibLedgerAdvance['v_date'] = $v_date;
    //                     $rentLibLedgerAdvance['ssb_account_id_to'] = $ssb_account_id_to;
    //                     $rentLibLedgerAdvance['created_at'] = $created_at;
    //                     $rentLibLedgerAdvance['updated_at'] = $updated_at;
    //                     $rentLibLedgerAdvance['daybook_ref_id'] = $refId;
    //                     $rlLa = \App\Models\RentLiabilityLedger::create($rentLibLedgerAdvance);
    //                 }
    //                 // ------ security  rent - (mines)----------------
    //                 if ($request->security_settel > 0) {
    //                     $head11S = 2;
    //                     $head21S = 10;
    //                     $head31S = 29;
    //                     $head41S = 75;
    //                     $head51S = NULL;
    //                     $allTran123 = CommanController::headTransactionCreate($refId, $branch_id, $bank_id, $bank_ac_id, $head41S, $type, $sub_type, $type_id, $associate_id = NULL, $member_id = NULL, $branch_id_to = NULL, $branch_id_from = NULL, $opening_balance = NULL, $request->security_settel, $closing_balance = NULL, $des, 'CR', $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $rentPaymentId, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $cheque_bank_to_name, $cheque_bank_to_branch, $cheque_bank_to_ac_no, $cheque_bank_to_ifsc, $transction_bank_from_id, $transction_bank_from_ac_id, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc, $cheque_bank_from_id, $cheque_bank_ac_from_id);

    //                     $rentLibLedgerSecurity['rent_liability_id'] = $rentLiabilityId;
    //                     $rentLibLedgerSecurity['type'] = 6;
    //                     $rentLibLedgerSecurity['type_id'] = $rentPaymentId;
    //                     $rentLibLedgerSecurity['deposit'] = $request->security_settel;
    //                     $rentLibLedgerSecurity['description'] = $detail;
    //                     $rentLibLedgerSecurity['currency_code'] = $currency_code;
    //                     $rentLibLedgerSecurity['payment_type'] = 'CR';
    //                     $rentLibLedgerSecurity['payment_mode'] = 3;
    //                     $rentLibLedgerSecurity['v_no'] = $v_no;
    //                     $rentLibLedgerSecurity['v_date'] = $v_date;
    //                     $rentLibLedgerSecurity['ssb_account_id_to'] = $ssb_account_id_to;
    //                     $rentLibLedgerSecurity['created_at'] = $created_at;
    //                     $rentLibLedgerSecurity['updated_at'] = $updated_at;
    //                     $rentLibLedgerSecurity['daybook_ref_id'] = $refId;
    //                     $rlLs = \App\Models\RentLiabilityLedger::create($rentLibLedgerSecurity);
    //                 }
    //                 $description_dr = $rentPaymentDetail['rentLib']->owner_name . 'A/c Dr ' . $rentAmount1 . '/-';
    //                 $description_cr = 'To SSB(' . $ssbAccount . ') A/c Cr ' . $rentAmount1 . '/-';
    //                 $brDaybook = CommanController::NewFieldBranchDaybookCreate($refId, $branch_id, $type, $sub_type, $type_id, $associate_id = NULL, $member_id = NULL, $branch_id_to = NULL, $branch_id_from = NULL, $opening_balance = NULL, $rentAmount1, $closing_balance = NULL, $des, $description_dr, $description_cr, 'CR', $payment_mode, $currency_code, $amount_to_id = NULL, $amount_to_name = NULL, $amount_from_id = NULL, $amount_from_name = NULL, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $is_contra = NULL, $contra_id = NULL, $created_at, $updated_at, $rentPaymentId, $ssb_account_id_to, $cheque_bank_from_id, $cheque_bank_ac_from_id, $cheque_bank_to_name, $cheque_bank_to_branch, $cheque_bank_to_ac_no, $cheque_bank_to_ifsc, $transction_bank_from_id, $transction_bank_from_ac_id, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc, 0, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $jv_unique_id, $cheque_type, $cheque_id);
    //                 // $branchClosing=CommanController:: checkCreateBranchClosing($branch_id,$created_at,$rentAmount1,0);
    //                 // if($current_date==$entry_date)
    //                 //     {
    //                 //         $branchClosing=CommanController:: checkCreateBranchClosingDr($branch_id,$created_at,$rentAmount1,0);
    //                 //     }
    //                 //     else
    //                 //     { 
    //                 //         $branchClosing=CommanController:: checkCreateBranchClosingBackdateDR($branch_id,$created_at,$rentAmount1,0);
    //                 //     }                     
    //             } else {
    //                 /// bank
    //                 $neft_charge = 0;
    //                 $bank_id_from_c = $request->bank_id;
    //                 $bank_ac_id_from_c = $request->account_id;
    //                 $bankBla = \App\Models\SamraddhBankClosing::where('bank_id', $bank_id_from_c)->where('account_id', $bank_ac_id_from_c)->whereDate('entry_date', '<=', $entry_date)->orderby('entry_date', 'desc')->first();
    //                 if ($bankBla) {
    //                     if ($request->total_transfer_amount > $bankBla->balance) {
    //                         return redirect('admin/rent/ledger-payable/' . $ledger_id)->with('alert', 'Sufficient amount not available in bank account!');
    //                     }
    //                 } else {
    //                     return redirect('admin/rent/ledger-payable/' . $ledger_id)->with('alert', 'Sufficient amount not available in bank account!');
    //                 }
    //                 $rentPaymentId = $request->rentPaymentId;
    //                 $rentPaymentDetail = \App\Models\RentPayment::with('rentLib')->where('id', $rentPaymentId)->first();
    //                 $rentPaymentBalance = $rentPaymentDetail->balance;
    //                 $rentLiabilityBalance = $rentPaymentDetail['rentLib']->bill_current_balance;
    //                 $branch_id = $rentPaymentDetail->branch_id;
    //                 $branchCode = getBranchCode($branch_id)->branch_code;
    //                 $rentAmount = $rentPaymentDetail->actual_transfer_amount;
    //                 $rentAmount1 = $request->transfer_amount;
    //                 $detail = 'Rent Payment ' . $rentPaymentDetail->month_name . ' ' . $rentPaymentDetail->year;
    //                 $bank_name_to = $rentPaymentDetail->owner_bank_name;
    //                 $bank_ac_to = $rentPaymentDetail->owner_bank_account_number;
    //                 $bank_ifsc_to = $rentPaymentDetail->owner_bank_ifsc_code;
    //                 //-------------------
    //                 $v_no = NULL;
    //                 $v_date = NULL;
    //                 $ssb_account_id_from = NULL;
    //                 $ssb_account_id_to = NULL;
    //                 $cheque_no = NULL;
    //                 $cheque_date = NULL;
    //                 $cheque_bank_from = NULL;
    //                 $cheque_bank_ac_from = NULL;
    //                 $cheque_bank_ifsc_from = NULL;
    //                 $cheque_bank_branch_from = NULL;
    //                 $cheque_bank_to = NULL;
    //                 $cheque_bank_ac_to = NULL;
    //                 $cheque_bank_from_id = NULL;
    //                 $cheque_bank_ac_from_id = NULL;
    //                 $cheque_bank_to_name = NULL;
    //                 $cheque_bank_to_branch = NULL;
    //                 $cheque_bank_to_ac_no = NULL;
    //                 $cheque_bank_to_ifsc = NULL;
    //                 $transction_no = NULL;
    //                 $transction_bank_from = NULL;
    //                 $transction_bank_ac_from = NULL;
    //                 $transction_bank_ifsc_from = NULL;
    //                 $transction_bank_branch_from = NULL;
    //                 $transction_bank_to = NULL;
    //                 $transction_bank_ac_to = NULL;
    //                 $transction_date = NULL;
    //                 $transction_bank_from_id = NULL;
    //                 $transction_bank_from_ac_id = NULL;
    //                 $transction_bank_to_name = NULL;
    //                 $transction_bank_to_ac_no = NULL;
    //                 $transction_bank_to_branch = NULL;
    //                 $transction_bank_to_ifsc = NULL;
    //                 $jv_unique_id = NULL;
    //                 $ssb_account_tran_id_to = NULL;
    //                 $ssb_account_tran_id_from = NULL;
    //                 $cheque_type = NULL;
    //                 $cheque_id = NULL;
    //                 $member_id = NULL;
    //                 $amount_to_id = NULL;
    //                 $amount_to_name = NULL;
    //                 $amount_from_id = NULL;
    //                 $amount_from_name = NULL;
    //                 $bank_id_from = $request->bank_id;
    //                 $bank_ac_id_from = $request->account_id;
    //                 $bank_id = $bank_id_from;
    //                 $bank_id_ac =  $bank_ac_id = $bank_ac_id_from;
    //                 $payment_type = 'CR';
    //                 //------------
    //                 $daybookRef = CommanController::createBranchDayBookReferenceNew($rentAmount, $globaldate);
    //                 $refId = $daybookRef;
    //                 //------------
    //                 $rentLiabilityId = $rentPaymentDetail['rentLib']->id;
    //                 $lib_current_balance = $rentPaymentDetail['rentLib']->current_balance;
    //                 $des = $rentPaymentDetail['rentLib']->owner_name . "'s " . $rentPaymentDetail->month_name . '' . $rentPaymentDetail->year . ' rent payment has been transferred to the ' . $bank_name_to . ' A/c' . $bank_ac_to;
    //                 $data['transferred_amount'] = $rentAmount;
    //                 $data['balance'] = $rentPaymentBalance - $rentAmount;
    //                 $data['transferred_date']   = $entry_date;
    //                 $data['security_amount']   = $request->security_amount;
    //                 $data['settle_amount']      = $request->advance_settel + $request->security_settel;
    //                 $data['advance_payment']   = $request->advance_payment;
    //                 $data['current_advance_payment'] = $rlldata['advance_payment'] = $request->advance_payment - $request->advance_settel;
    //                 $data['transfer_amount']   = $request->transfer_amount;
    //                 $data['transfer_mode']      = $request->amount_mode;
    //                 $data['company_bank_id']    = $bank_id_from = $request->bank_id;
    //                 $data['company_bank_ac_id'] = $bank_ac_id_from = $request->account_id;
    //                 $paymentMode = $request->payment_mode;
    //                 $bankfrmDetail = \App\Models\SamraddhBank::where('id', $bank_id_from)->first();
    //                 $bankacfrmDetail = \App\Models\SamraddhBankAccount::where('id', $bank_id_from)->first();
    //                 if ($request->payment_mode == 1) {
    //                     $data['company_cheque_id'] = $cheque_id = $request->cheque_id;
    //                     $data['company_cheque_no'] = $cheque_no = $request->cheque_number;
    //                     $cheque_date = $entry_date;
    //                     $rentLibLedger['cheque_id'] = $cheque_id;
    //                     $rentLibLedger['cheque_no'] = $cheque_no;
    //                     $rentLibLedger['cheque_date'] = $cheque_date;
    //                     $cheque_no = $cheque_no;
    //                     $cheque_date = $cheque_date;
    //                     $cheque_bank_from = $bankfrmDetail->bank_name;
    //                     $cheque_bank_ac_from = $bankacfrmDetail->account_no;
    //                     $cheque_bank_ifsc_from = $bankacfrmDetail->ifsc_code;
    //                     $cheque_bank_branch_from = $bankacfrmDetail->branch_name;
    //                     $cheque_bank_from_id = $bank_id_from;
    //                     $cheque_bank_ac_from_id = $bank_ac_id_from;
    //                     $cheque_bank_to = NULL;
    //                     $cheque_bank_ac_to = NULL;
    //                     $cheque_bank_to_name = $bank_name_to;
    //                     $cheque_bank_to_branch = NULL;
    //                     $cheque_bank_to_ac_no = $bank_ac_to;
    //                     $cheque_bank_to_ifsc = $bank_ifsc_to;
    //                     $cheque_type = 1;
    //                     $cheque_id = $cheque_id;
    //                     //---------- Bill Payment --------
    //                     $bill['payment_mode'] = 1;
    //                     $bill['cheque_id_company'] = $cheque_id;
    //                     $bill['cheque_no_company'] = $cheque_no;
    //                     $bill['cheque_date'] = $cheque_date;

    //                     $bill['to_bank_name'] = $cheque_bank_to_name;
    //                     $bill['to_bank_branch'] = $cheque_bank_to_branch;
    //                     $bill['to_bank_ac_no'] = $cheque_bank_to_ac_no;
    //                     $bill['to_bank_ifsc'] = $cheque_bank_to_ifsc;
    //                     $bill['to_bank_id'] = $cheque_bank_to;
    //                     $bill['to_bank_account_id'] = $cheque_bank_ac_to;
    //                     $bill['from_bank_name'] = $cheque_bank_from;
    //                     $bill['from_bank_branch'] = $cheque_bank_branch_from;
    //                     $bill['from_bank_ac_no'] = $cheque_bank_ac_from;
    //                     $bill['from_bank_ifsc'] = $cheque_bank_ifsc_from;
    //                     $bill['from_bank_id'] = $cheque_bank_from_id;
    //                     $bill['from_bank_ac_id'] = $cheque_bank_ac_from_id;
    //                     //-----------------------
    //                     $chequeIssue['cheque_id'] = $cheque_id;
    //                     $chequeIssue['type'] = 5;
    //                     $chequeIssue['sub_type'] = 51;
    //                     $chequeIssue['type_id'] = $type_id;
    //                     $chequeIssue['cheque_issue_date'] = $entry_date;
    //                     $chequeIssue['created_at'] = $created_at;
    //                     $chequeIssue['updated_at'] = $updated_at;
    //                     $chequeIssueCreate = \App\Models\SamraddhChequeIssue::create($chequeIssue);
    //                     //------------------ 
    //                     $chequeUpdate['is_use'] = 1;
    //                     $chequeUpdate['status'] = 3;
    //                     $chequeUpdate['updated_at'] = $updated_at;
    //                     $chequeDataUpdate = \App\Models\SamraddhCheque::find($cheque_id);
    //                     $chequeDataUpdate->update($chequeUpdate);
    //                     $data['payment_mode']       = 1;
    //                 } else {
    //                     $data['online_transaction_no'] = $transction_no = $request->utr_tran;
    //                     $data['neft_charge'] = $neft_charge = $request->neft_charge;
    //                     $transaction_date = $entry_date;
    //                     $rentLibLedger['transaction_no'] = $transction_no;
    //                     $rentLibLedger['transaction_date'] = $transaction_date;
    //                     $rentLibLedger['transaction_charge'] = $neft_charge;
    //                     $transction_bank_from = $bankfrmDetail->bank_name;
    //                     $transction_bank_ac_from = $bankacfrmDetail->account_no;
    //                     $transction_bank_ifsc_from = $bankacfrmDetail->ifsc_code;
    //                     $transction_bank_branch_from = $bankacfrmDetail->branch_name;
    //                     $transction_bank_from_id = $bank_id_from;
    //                     $transction_bank_from_ac_id = $bank_ac_id_from;
    //                     $transction_bank_to = NULL;
    //                     $transction_bank_ac_to = NULL;
    //                     $transction_bank_to_name = $bank_name_to;
    //                     $transction_bank_to_branch = NULL;
    //                     $transction_bank_to_ac_no = $bank_ac_to;
    //                     $transction_bank_to_ifsc = $bank_ifsc_to;
    //                     //---------- Bill Payment --------
    //                     $bill['payment_mode'] = 2;
    //                     $bill['transaction_no'] = $transction_no;
    //                     $bill['transaction_date'] = $transaction_date;
    //                     $bill['transaction_charge'] = $neft_charge;
    //                     $bill['to_bank_name'] = $transction_bank_to_name;
    //                     $bill['to_bank_branch'] = $transction_bank_to_branch;
    //                     $bill['to_bank_ac_no'] = $transction_bank_to_ac_no;
    //                     $bill['to_bank_ifsc'] = $transction_bank_to_ifsc;
    //                     $bill['to_bank_id'] = $transction_bank_to;
    //                     $bill['to_bank_account_id'] = $transction_bank_ac_to;
    //                     $bill['from_bank_name'] = $transction_bank_from;
    //                     $bill['from_bank_branch'] = $transction_bank_branch_from;
    //                     $bill['from_bank_ac_no'] = $transction_bank_ac_from;
    //                     $bill['from_bank_ifsc'] = $transction_bank_ifsc_from;
    //                     $bill['from_bank_id'] = $transction_bank_from_id;
    //                     $bill['from_bank_ac_id'] = $transction_bank_from_ac_id;
    //                     // bank charge head entry +


    //                     $data['payment_mode']       = 0;
    //                 }
    //                 $data['status']             = 1;
    //                 $data['updated_at'] = $request->created_at;
    //                 $data['payment_ref_id'] = $refId;
    //                 $rentPaymentUpdate = \App\Models\RentPayment::find($rentPaymentId);
    //                 $rentPaymentUpdate->update($data);
    //                 $total_transfer_amount = $rentPaymentDetail->actual_transfer_amount;
    //                 //----------------------------------- 
    //                 // $rentGet=\App\Models\RentLiabilityLedger::where('rent_liability_id',$rentLiabilityId)->whereDate('created_at','<=',$entry_date)->orderby('created_at','DESC')->first(); 
    //                 $rentLibLedger['rent_liability_id'] = $rentLiabilityId;
    //                 $rentLibLedger['type'] = 1;
    //                 $rentLibLedger['type_id'] = $rentPaymentId;
    //                 $rentLibLedger['withdrawal'] = $rentAmount1;
    //                 $rentLibLedger['description'] = $detail;
    //                 $rentLibLedger['currency_code'] = $currency_code;
    //                 $rentLibLedger['payment_type'] = 'DR';
    //                 $rentLibLedger['payment_mode'] = $paymentMode;
    //                 $rentLibLedger['created_at'] = $created_at;
    //                 $rentLibLedger['updated_at'] = $updated_at;
    //                 $rentLibLedger['daybook_ref_id'] = $refId;
    //                 $rlL = \App\Models\RentLiabilityLedger::create($rentLibLedger);
    //                 /// ------------- Bill Payment ----------
    //                 // $billGet=VendorBillPayment::where('rent_id',$rentPaymentId)->whereDate('payment_date','<=',$entry_date)->orderby('payment_date','DESC')->first();
    //                 // $bill['bill_type']=1; 
    //                 // $bill['rent_ledger_id']=$ledger_id;
    //                 // $bill['rent_owner_id']=$rentLiabilityId;
    //                 // $bill['rent_id']=$rentPaymentId; 
    //                 // $bill['withdrawal']=$rentAmount;
    //                 // $bill['description']=$detail;
    //                 // $bill['currency_code']=$currency_code;
    //                 // $bill['payment_type']='DR'; 
    //                 // $bill['payment_date']=$entry_date; 
    //                 // $bill['created_at']=$created_at;
    //                 // $bill['updated_at']=$updated_at;
    //                 // $bill['daybook_ref_id']=$refId;
    //                 // $createBill = VendorBillPayment::create($bill); 
    //                 // //------------------------------------------------                           
    //                 // $rlldata['security_amount']= $rentPaymentDetail['rentLib']->security_amount-$request->security_settel;
    //                 // $rlldata['current_balance']= $lib_current_balance-$rentAmount1; 
    //                 // $rlldata['bill_current_balance']=$rentLiabilityBalance-$rentAmount;
    //                 // $rlldata['updated_at']=$updated_at; 
    //                 // $rllUpdate = \App\Models\RentLiability::find($rentLiabilityId);
    //                 // $rllUpdate->update($rlldata); 
    //                 // $rentBackdate34=CommanController:: rentLedgerBackDateCR($rentLiabilityId,$created_at,$rentAmount1);
    //                 //------------------------libility -(mines)  ------------
    //                 $head11 = 1;
    //                 $head21 = 8;
    //                 $head31 = 21;
    //                 $head41 = 60;
    //                 $head51 = NULL;
    //                 $allTran1 = CommanController::headTransactionCreate($refId, $branch_id, $bank_id, $bank_ac_id, $head41, $type, $sub_type, $type_id, $associate_id = NULL, $member_id = NULL, $branch_id_to = NULL, $branch_id_from = NULL, $opening_balance = NULL, $rentAmount, $closing_balance = NULL, $des, 'DR', $paymentMode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $rentPaymentId, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $cheque_bank_to_name, $cheque_bank_to_branch, $cheque_bank_to_ac_no, $cheque_bank_to_ifsc, $transction_bank_from_id, $transction_bank_from_ac_id, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc, $cheque_bank_from_id, $cheque_bank_ac_from_id);
    //                 // ------ advance rent - (mines)----------------
    //                 if ($request->advance_settel > 0) {
    //                     $head11A = 2;
    //                     $head21A = 10;
    //                     $head31A = 29;
    //                     $head41A = 74;
    //                     $head51A = NULL;
    //                     $allTran14 = CommanController::headTransactionCreate($refId, $branch_id, $bank_id, $bank_ac_id, $head41A, $type, $sub_type, $type_id, $associate_id = NULL, $member_id = NULL, $branch_id_to = NULL, $branch_id_from = NULL, $opening_balance = NULL, $request->advance_settel, $closing_balance = NULL, $des, 'CR', $paymentMode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $rentPaymentId, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $cheque_bank_to_name, $cheque_bank_to_branch, $cheque_bank_to_ac_no, $cheque_bank_to_ifsc, $transction_bank_from_id, $transction_bank_from_ac_id, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc, $cheque_bank_from_id, $cheque_bank_ac_from_id);

    //                     $rentLibLedgerAdvance['rent_liability_id'] = $rentLiabilityId;
    //                     $rentLibLedgerAdvance['type'] = 5;
    //                     $rentLibLedgerAdvance['type_id'] = $rentPaymentId;
    //                     $rentLibLedgerAdvance['deposit'] = $request->advance_settel;
    //                     $rentLibLedgerAdvance['description'] = 'Advance Payment Adjustment';
    //                     $rentLibLedgerAdvance['currency_code'] = $currency_code;
    //                     $rentLibLedgerAdvance['payment_type'] = 'CR';
    //                     $rentLibLedgerAdvance['payment_mode'] = $paymentMode;
    //                     $rentLibLedgerAdvance['created_at'] = $created_at;
    //                     $rentLibLedgerAdvance['updated_at'] = $updated_at;
    //                     $rentLibLedgerAdvance['daybook_ref_id'] = $refId;
    //                     $rlLa = \App\Models\RentLiabilityLedger::create($rentLibLedgerAdvance);
    //                 }
    //                 // ------ security  rent - (mines)----------------
    //                 if ($request->security_settel > 0) {
    //                     $head11S = 2;
    //                     $head21S = 10;
    //                     $head31S = 29;
    //                     $head41S = 75;
    //                     $head51S = NULL;
    //                     $allTran123 = CommanController::headTransactionCreate($refId, $branch_id, $bank_id, $bank_ac_id, $head41S, $type, $sub_type, $type_id, $associate_id = NULL, $member_id = NULL, $branch_id_to = NULL, $branch_id_from = NULL, $opening_balance = NULL, $request->security_settel, $closing_balance = NULL, $des, 'CR', $paymentMode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $rentPaymentId, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $cheque_bank_to_name, $cheque_bank_to_branch, $cheque_bank_to_ac_no, $cheque_bank_to_ifsc, $transction_bank_from_id, $transction_bank_from_ac_id, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc, $cheque_bank_from_id, $cheque_bank_ac_from_id);

    //                     $rentLibLedgerSecurity['rent_liability_id'] = $rentLiabilityId;
    //                     $rentLibLedgerSecurity['type'] = 6;
    //                     $rentLibLedgerSecurity['type_id'] = $rentPaymentId;
    //                     $rentLibLedgerSecurity['deposit'] = $request->security_settel;
    //                     $rentLibLedgerSecurity['description'] = 'Security Amount Adjustment';
    //                     $rentLibLedgerSecurity['currency_code'] = $currency_code;
    //                     $rentLibLedgerSecurity['payment_type'] = 'CR';
    //                     $rentLibLedgerSecurity['payment_mode'] = $paymentMode;
    //                     $rentLibLedgerSecurity['created_at'] = $created_at;
    //                     $rentLibLedgerSecurity['updated_at'] = $updated_at;
    //                     $rentLibLedgerSecurity['daybook_ref_id'] = $refId;
    //                     $rlLs = \App\Models\RentLiabilityLedger::create($rentLibLedgerSecurity);
    //                 }

    //                 $bankAmountRent = $rentAmount;
    //                 $description_dr = $rentPaymentDetail['rentLib']->owner_name . 'A/c Dr ' . $rentAmount1 . '/-';
    //                 $description_cr = 'To ' . $bankfrmDetail->bank_name . '(' . $bankacfrmDetail->account_no . ') A/c Cr ' . $rentAmount1 . '/-';
    //                 // ---------------- branch daybook entry -----------------
    //                 $brDaybook = CommanController::NewFieldBranchDaybookCreate($refId, $branch_id, $type, $sub_type, $type_id, $associate_id = NULL, $member_id = NULL, $branch_id_to = NULL, $branch_id_from = NULL, $opening_balance = NULL, $rentAmount1, $closing_balance = NULL, $des, $description_dr, $description_cr, 'DR', $paymentMode, $currency_code, $amount_to_id = NULL, $amount_to_name = NULL, $amount_from_id = NULL, $amount_from_name = NULL, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $is_contra = NULL, $contra_id = NULL, $created_at, $updated_at, $rentPaymentId, $ssb_account_id_to, $cheque_bank_from_id, $cheque_bank_ac_from_id, $cheque_bank_to_name, $cheque_bank_to_branch, $cheque_bank_to_ac_no, $cheque_bank_to_ifsc, $transction_bank_from_id, $transction_bank_from_ac_id, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc, 0, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $jv_unique_id, $cheque_type, $cheque_id);
    //                 // ------------------ samraddh bank entry -(mines) ---------------
    //                 $bankAmountRent =  $rentAmount1;
    //                 $description_dr_b = $rentPaymentDetail['rentLib']->owner_name . 'A/c Dr ' . $bankAmountRent . '/-';
    //                 $description_cr_b = 'To ' . $bankfrmDetail->bank_name . '(' . $bankacfrmDetail->account_no . ') A/c Cr ' . $bankAmountRent . '/-';
    //                 $allTran2 = CommanController::headTransactionCreate($refId, $branch_id, $bank_id, $bank_ac_id, $bankfrmDetail->account_head_id, $type, $sub_type, $type_id, $associate_id = NULL, $member_id = NULL, $branch_id_to = NULL, $branch_id_from = NULL, $opening_balance = NULL, $bankAmountRent, $closing_balance = NULL, $des, 'CR', $paymentMode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $rentPaymentId, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $cheque_bank_to_name, $cheque_bank_to_branch, $cheque_bank_to_ac_no, $cheque_bank_to_ifsc, $transction_bank_from_id, $transction_bank_from_ac_id, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc, $cheque_bank_from_id, $cheque_bank_ac_from_id);
    //                 $bncktrn = $bankAmountRent;
    //                 $smbdc = CommanController::NewFieldAddSamraddhBankDaybookCreateModify($refId, $bank_id_from, $bank_ac_id_from, $type, $sub_type, $type_id, $associate_id = NULL, $member_id = NULL, $branch_id, $opening_balance = NULL, $bncktrn, $closing_balance = NULL, $des, $description_dr_b, $description_cr_b, 'DR', $paymentMode, $currency_code, $amount_to_id = NULL, $amount_to_name = NULL, $amount_from_id = NULL, $amount_from_name = NULL, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $rentPaymentId, $ssb_account_id_to, $cheque_bank_from_id, $cheque_bank_ac_from_id, $cheque_bank_to_name, $cheque_bank_to_branch, $cheque_bank_to_ac_no, $cheque_bank_to_ifsc, $transction_bank_from_id, $transction_bank_from_ac_id, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $jv_unique_id, $cheque_type, $cheque_id);

    //                 if ($current_date == $entry_date) {
    //                     $bankClosing = CommanController::checkCreateBankClosingDR($bank_id_from, $bank_ac_id_from, $created_at, $bncktrn, 0);
    //                     // //-----------   barnch clossing balence  --------------------------- 
    //                     // $branchClosing=CommanController:: checkCreateBranchClosingDr($branch_id,$created_at,$rentAmount1,0);
    //                 } else {
    //                     $bankClosing = CommanController::checkCreateBankClosingDRBackDate($bank_id_from, $bank_ac_id_from, $created_at, $bncktrn, 0);
    //                     // //-----------   barnch clossing balence  --------------------------- 
    //                     // $branchClosing=CommanController:: checkCreateBranchClosingBackdateDR($branch_id,$created_at,$rentAmount1,0);
    //                 }
    //             }
    //         }
    //         if($request->amount_mode == 2)
    //         {
    //             if ($request->neft_charge > 0) {
    //                 $allTranSSB = CommanController::headTransactionCreate($refId, 29, $bank_id, $bank_ac_id, 92, $type, $sub_type, $type_id, $associate_id = NULL, $member_id = NULL, $branch_id_to = NULL, $branch_id_from = NULL, $opening_balance = NULL,$request->neft_charge, $closing_balance = NULL, $des, 'DR', $paymentMode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $rentPaymentId, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $cheque_bank_to_name, $cheque_bank_to_branch, $cheque_bank_to_ac_no, $cheque_bank_to_ifsc, $transction_bank_from_id, $transction_bank_from_ac_id, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc, $cheque_bank_from_id, $cheque_bank_ac_from_id);

    //                 $allTran2 = CommanController::headTransactionCreate($refId, 29, $bank_id, $bank_ac_id, $bankfrmDetail->account_head_id, $type, $sub_type, $type_id, $associate_id = NULL, $member_id = NULL, $branch_id_to = NULL, $branch_id_from = NULL, $opening_balance = NULL, $request->neft_charge, $closing_balance = NULL, $des, 'CR', $paymentMode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $rentPaymentId, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $cheque_bank_to_name, $cheque_bank_to_branch, $cheque_bank_to_ac_no, $cheque_bank_to_ifsc, $transction_bank_from_id, $transction_bank_from_ac_id, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc, $cheque_bank_from_id, $cheque_bank_ac_from_id);


    //                 CommanController::NewFieldAddSamraddhBankDaybookCreateModify($refId, $bank_id_from, $bank_ac_id_from, $type, $sub_type, $type_id, $associate_id = NULL, $member_id = NULL, 29, $opening_balance = NULL,$request->neft_charge, $closing_balance = NULL, $des, $description_dr_b, $description_cr_b, 'DR', $paymentMode, $currency_code, $amount_to_id = NULL, $amount_to_name = NULL, $amount_from_id = NULL, $amount_from_name = NULL, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $rentPaymentId, $ssb_account_id_to, $cheque_bank_from_id, $cheque_bank_ac_from_id, $cheque_bank_to_name, $cheque_bank_to_branch, $cheque_bank_to_ac_no, $cheque_bank_to_ifsc, $transction_bank_from_id, $transction_bank_from_ac_id, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $jv_unique_id, $cheque_type, $cheque_id);
    //             }
    //         }

    //         $payment_count = \App\Models\RentPayment::where('ledger_id', $ledger_id)->where('status', 1)->get();
    //         $total_payment_count = \App\Models\RentPayment::where('ledger_id', $ledger_id)->get();
    //         $l = \App\Models\RentLedger::where('id', $ledger_id)->first();
    //         if ($l->transfer_amount > 0) {
    //             $ledgertransfer_amount = $total_transfer_amount + $l->transfer_amount;
    //         } else {
    //             $ledgertransfer_amount = $total_transfer_amount;
    //         }
    //         $updateLedger['transfer_amount'] = $ledgertransfer_amount;
    //         if (count($payment_count) == count($total_payment_count)) {
    //             $updateLedger['status'] = 1;
    //         } else {
    //             $updateLedger['status'] = 2;
    //         }
    //         $leaserdataUpdate = \App\Models\RentLedger::find($ledger_id);
    //         $leaserdataUpdate->update($updateLedger);
    //         DB::commit();
    //     } catch (\Exception $ex) {
    //         DB::rollback();
    //         return back()->with('alert', $ex->getMessage());
    //     }
    //     return redirect('admin/rent/rent-ledger')->with('success', 'Rent Payment Transferred Successfully');
    // }


    public function rentTransferAdvanceSave(Request $request)
    {
        // print_r($_POST);die;
        if ($request->transfer_amount == 0) {
            $rules = [
                //'amount_mode' => ['required'],
                'total_transfer_amount' => ['required'],
                'leaser_id' => ['required'],
                // 'bank_id' => ['required'], 
                //   'account_id' => ['required'], 
                'advance_payment' => ['required'],
                'actual_transfer' => ['required'],
                'security_amount' => ['required'],
                'advance_settel' => ['required'],
                'security_settel' => ['required'],
                'transfer_amount' => ['required'],
            ];
        } else {
            $rules = [
                'amount_mode' => ['required'],
                'total_transfer_amount' => ['required'],
                'leaser_id' => ['required'],
                // 'bank_id' => ['required'], 
                //   'account_id' => ['required'], 
                'advance_payment' => ['required'],
                'actual_transfer' => ['required'],
                'security_amount' => ['required'],
                'advance_settel' => ['required'],
                'security_settel' => ['required'],
                'transfer_amount' => ['required'],
            ];
        }
        $customMessages = [
            'required' => ':Attribute  is required.',
        ];
        $this->validate($request, $rules, $customMessages);
        DB::beginTransaction();
        try {
            $company_id = $request->company_id;
            $ledger_id = $leaser = $request->leaser_id;
            $globaldate = $request->created_at;
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
            // head  entry----------------------------                    
            $type = 10;
            $sub_type = 102;
            $type_id = $leaser;
            $advance_amt = $request->advance_settel;
            if ($request->transfer_amount == 0) {
                //total transfer amount was zero 
                $sub_type = 107;
                $rentPaymentId = $request->rentPaymentId;
                $rentPaymentDetail = \App\Models\RentPayment::with('rentLib')->where('id', $rentPaymentId)->first();
                $rentPaymentBalance = $rentPaymentDetail->balance;
                $rentLiabilityBalance = $rentPaymentDetail['rentLib']->bill_current_balance;
                $rentAmount = $rentPaymentDetail->actual_transfer_amount - $rentPaymentDetail->transferred_amount;
                $rentAmount1 = $request->transfer_amount;

                $daybookRef = CommanController::createBranchDayBookReferenceNew($rentAmount, $globaldate);
                $refId = $daybookRef;

                $totalPaybaleAmount = $rentPaymentDetail->transferred_amount + $rentAmount;
                $rentLiabilityId = $rentPaymentDetail['rentLib']->id;
                if ($advance_amt > 0) {
                    $advanceBalance = AdvancedTransaction::where('type',3)->where('sub_type',31)->where('status',1)->where('type_id',$rentLiabilityId)->where('settle_amount','>',0)->select('settle_amount','id','partial_daybook_ref_id')->get();

                    foreach ($advanceBalance as $key) {  
                        $advance_amt = $key['settle_amount'] - $advance_amt;
                        $advance_refId = $refId;
                        if($key['partial_daybook_ref_id'] != NULL){
                            $advance_refId = $key['partial_daybook_ref_id'];
                            $advance_refId = $advance_refId.','.$refId;
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
                            $advance_amt = $advance_amt*-1;
                        } elseif ($advance_amt > 0) {
                            $advance_amtt = AdvancedTransaction::where('id', $key['id'])->update([
                                'settle_amount' => $advance_amt,
                                'partial_daybook_ref_id' => $advance_refId
                            ]);
                            break;
                        }
                    }
                }
                $data['transferred_date']   = $entry_date;
                $data['security_amount']   = $request->security_amount;
                $data['settle_amount']      = $request->advance_settel + $request->security_settel;
                $data['advance_payment']   = $request->advance_payment;
                $data['current_advance_payment'] = $rlldata['advance_payment'] = $request->advance_payment - $request->advance_settel;
                $data['transfer_amount']   = $totalPaybaleAmount;
                $data['transfer_mode']      = $request->amount_mode;
                $data['v_no']               = $v_no;
                $data['v_date']             = $entry_date;
                $data['status']             = 1;
                $data['transferred_amount'] = $totalPaybaleAmount;
                $data['balance'] = $rentPaymentBalance - ($totalPaybaleAmount);
                $data['payment_ref_id'] = $refId;
                $data['updated_at'] = $request->created_at;
                $rentPaymentUpdate = \App\Models\RentPayment::find($rentPaymentId);
                $rentPaymentUpdate->update($data);
                $total_transfer_amount = $rentPaymentDetail->actual_transfer_amount;
                $branch_id = $rentPaymentDetail->branch_id;
                $branchCode = getBranchCode($branch_id)->branch_code;
                $payment_mode = 3;
                $payment_type = 'CR';

                $rentLiabilityId = $rentPaymentDetail['rentLib']->id;
                $lib_current_balance = $rentPaymentDetail['rentLib']->current_balance;
                $des = $rentPaymentDetail['rentLib']->owner_name . "'s " . $rentPaymentDetail->month_name . '' . $rentPaymentDetail->year . ' rent payment has been settled  through Advance Payment';
                $head1SSB = 1;
                $head2SSB = 8;
                $head3SSB = 20;
                $head4SSB = 56;
                $head5SSB = NULL;
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
                $ssb_account_id_to = $ssbId = NULL;
                $cheque_bank_from_id = NULL;
                $cheque_bank_ac_from_id = NULL;
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
                $jv_unique_id = NULL;
                $ssb_account_tran_id_to = $ssbRentTranID = NULL;
                $ssb_account_tran_id_from = NULL;
                $cheque_type = NULL;
                $cheque_id = NULL;
                $amount_to_id = NULL;
                $amount_to_name = NULL;
                $amount_from_id = NULL;
                $amount_from_name = NULL;
                $bank_id = NULL;
                $bank_ac_id = NULL;
                $ssb_account_id_to = NULL;

                ///---------------libility - (mines) ----------------
                $head11 = 1;
                $head21 = 8;
                $head31 = 21;
                $head41 = 60;
                $head51 = NULL;
                $allTran1 = CommanController::newHeadTransactionCreate($refId, $branch_id, $bank_id, $bank_ac_id, $head41, $type, $sub_type, $type_id, $associate_id = NULL, $member_id = NULL, $branch_id_to = NULL, $branch_id_from = NULL, $rentAmount, $des, 'DR', $payment_mode, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $rentPaymentId, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $company_id);
                // ------ advance rent - (mines)----------------

                $head11A = 2;
                $head21A = 10;
                $head31A = 29;
                $head41A = 74;
                $head51A = NULL;
                $allTran14 = CommanController::newHeadTransactionCreate($refId, $branch_id, $bank_id, $bank_ac_id, $head41A, $type, $sub_type, $type_id, $associate_id = NULL, $member_id = NULL, $branch_id_to = NULL, $branch_id_from = NULL, $request->advance_settel, $des, 'CR', $payment_mode, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $rentPaymentId, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $company_id);

                $rentLibLedgerAdvance['rent_liability_id'] = $rentLiabilityId;
                $rentLibLedgerAdvance['type'] = 7;
                $rentLibLedgerAdvance['type_id'] = $rentPaymentId;
                $rentLibLedgerAdvance['deposit'] = $request->advance_settel;
                $rentLibLedgerAdvance['description'] = $des;
                $rentLibLedgerAdvance['currency_code'] = $currency_code;
                $rentLibLedgerAdvance['payment_type'] = 'CR';
                $rentLibLedgerAdvance['payment_mode'] = 3;
                $rentLibLedgerAdvance['v_no'] = $v_no;
                $rentLibLedgerAdvance['v_date'] = $v_date;
                $rentLibLedgerAdvance['ssb_account_id_to'] = $ssb_account_id_to;
                $rentLibLedgerAdvance['created_at'] = $created_at;
                $rentLibLedgerAdvance['updated_at'] = $updated_at;
                $rentLibLedgerAdvance['daybook_ref_id'] = $refId;
                $rentLibLedgerAdvance['company_id'] = $company_id;
                $rlLa = \App\Models\RentLiabilityLedger::create($rentLibLedgerAdvance);

                $description_dr = $rentPaymentDetail['rentLib']->owner_name . 'A/c Dr ' . $rentAmount . '/-';
                $description_cr = 'To Advance Rent A/c Cr ' . $rentAmount . '/-';

                $brDaybook = CommanController::branchDaybookCreateModified($refId, $branch_id, $type, $sub_type, $type_id, $associate_id = NULL, $member_id = NULL, $branch_id_to = NULL, $branch_id_from = NULL, $rentAmount1, $des, $description_dr, $description_cr, 'DR', 3, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $rentPaymentId, $ssb_account_id_to, $company_id);
            } else {

                if ($request->amount_mode == 1) {
                    //ssb  
                    $rentPaymentId = $request->rentPaymentId;
                    $rentPaymentDetail = \App\Models\RentPayment::with('rentLib')->where('id', $rentPaymentId)->first();
                    $rentPaymentBalance = $rentPaymentDetail->balance;
                    $rentLiabilityBalance = $rentPaymentDetail['rentLib']->bill_current_balance;
                    $rentAmount = $rentPaymentDetail->actual_transfer_amount - $rentPaymentDetail->transferred_amount;
                    $rentAmount1 = $request->transfer_amount;



                    $daybookRef = CommanController::createBranchDayBookReferenceNew($rentAmount, $globaldate);
                    $refId = $daybookRef;
                    $totalPaybaleAmount = $rentPaymentDetail->transferred_amount + $rentAmount;

                    $data['transferred_date']   = $entry_date;
                    $data['security_amount']   = $request->security_amount;
                    $data['settle_amount']      = $request->advance_settel + $request->security_settel;
                    $data['advance_payment']   = $request->advance_payment;
                    $data['current_advance_payment'] = $rlldata['advance_payment'] = $request->advance_payment - $request->advance_settel;

                    $data['transfer_mode']      = $request->amount_mode;
                    $data['v_no']               = $v_no;
                    $data['v_date']             = $entry_date;


                    $data['balance'] = $rentPaymentBalance - ($totalPaybaleAmount);
                    $data['payment_ref_id'] = $refId;
                    $data['updated_at'] = $request->created_at;

                    $currentTransferAmount = $rentAmount1 + $request->advance_settel + $request->security_settel;


                    $data['transfer_amount']   = $totalPaybaleAmount;
                    if ($currentTransferAmount == $rentAmount) {
                        $data['status']             = 1;
                        $data['transferred_amount'] = $totalPaybaleAmount;
                        $total_transfer_amount = $rentAmount;
                    } else {
                        $data['status']             = 2;
                        $data['transferred_amount'] = $totalPaybaleAmount;
                        $total_transfer_amount = $currentTransferAmount;
                    }


                    $rentPaymentUpdate = \App\Models\RentPayment::find($rentPaymentId);
                    $rentPaymentUpdate->update($data);

                    $branch_id = $rentPaymentDetail->branch_id;
                    $branchCode = getBranchCode($branch_id)->branch_code;

                    $ssbAccountDetail = getSavingAccountMemberId($rentPaymentDetail->owner_ssb_id);
                    $ssbBalance = $ssbAccountDetail->balance;
                    $member_id = $ssbAccountDetail->member_id;
                    $ssbId = $ssbAccountDetail->id;
                    $ssbAccount = $ssbAccountDetail->account_no;
                    //------------ ssb tran head entry start  --------------------------
                    $detail = 'Rent Payment of ' . $rentPaymentDetail->month_name . ' ' . $rentPaymentDetail->year;
                    $ssbTranCalculation = CommanController::SSBDateCR($ssbId, $ssbAccount, $ssbBalance, $rentAmount1, $detail, 'INR', 'CR', 3, $branch_id, $associate_id = NULL, 7, $created_at ,$refId, $company_id);
                    $SSbbck = CommanController::SSBBackDateCR($ssbId, $created_at, $rentAmount ,$company_id);
                    $ssbRentTranID = $ssbTranCalculation;
                    $amountArray = array('1' => $rentAmount1);
                    $deposit_by_name = $created_by_name;
                    $deposit_by_id = $created_by_id;
                    // $rdCreateTran = CommanController::createTransaction(NULL, 1, $ssbAccountDetail->id, $member_id, $branch_id, $branchCode, $amountArray, 77, $deposit_by_name, $deposit_by_id, $ssbAccountDetail->account_no, $cheque_dd_no = 0, $bank_name = null, $branch_name = null, $payment_date = null, $online_payment_id = null, $online_payment_by = null, $saving_account_id = 0, 'CR');
                    //-------------------- ssb head entry start -----------------
                    $payment_mode = 3;
                    $payment_type = 'CR';

                    $rentLiabilityId = $rentPaymentDetail['rentLib']->id;
                    $lib_current_balance = $rentPaymentDetail['rentLib']->current_balance;
                    $des = $rentPaymentDetail['rentLib']->owner_name . "'s " . $rentPaymentDetail->month_name . '' . $rentPaymentDetail->year . ' rent payment has been transferred to the SSB A/c' . $ssbAccount;
                    $head1SSB = 1;
                    $head2SSB = 8;
                    $head3SSB = 20;
                    $head4SSB = 56;
                    $head5SSB = NULL;
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
                    $ssb_account_id_to = $ssbId;
                    $cheque_bank_from_id = NULL;
                    $cheque_bank_ac_from_id = NULL;
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
                    $jv_unique_id = NULL;
                    $ssb_account_tran_id_to = $ssbRentTranID;
                    $ssb_account_tran_id_from = NULL;
                    $cheque_type = NULL;
                    $cheque_id = NULL;
                    $amount_to_id = NULL;
                    $amount_to_name = NULL;
                    $amount_from_id = NULL;
                    $amount_from_name = NULL;
                    $bank_id = NULL;
                    $bank_ac_id = NULL;
                    // ssb head entry +
                    $allTranSSB = CommanController::newHeadTransactionCreate($refId, $branch_id, $bank_id, $bank_ac_id, $head4SSB, 4, 49, $ssbId, $associate_id = NULL, $member_id, $branch_id_to = NULL, $branch_id_from = NULL, $rentAmount1, $des, 'CR', $payment_mode, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $ssbRentTranID, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $company_id);
                    // ssb Member transaction  +
                    // $memberTranSSB = CommanController::NewFieldAddMemberTransactionCreate($refId, 4, 49, $ssbId, $associate_id = NULL, $member_id, $branch_id, $bank_id = NULL, $account_id = NULL, $rentAmount1, $des, $payment_type, $payment_mode, $currency_code, $amount_to_id = NULL, $amount_to_name = NULL, $amount_from_id = NULL, $amount_from_name = NULL, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $ssbRentTranID, $ssb_account_id_to, $cheque_bank_from_id, $cheque_bank_ac_from_id, $cheque_bank_to_name, $cheque_bank_to_branch, $cheque_bank_to_ac_no, $cheque_bank_to_ifsc, $transction_bank_from_id, $transction_bank_from_ac_id, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $jv_unique_id, $cheque_type, $cheque_id);
                    //------------ ssb tran head entry end --------------------------
                    $ssb_account_id_to = $ssbAccountDetail->id;
                    // $rentGet=\App\Models\RentLiabilityLedger::where('rent_liability_id',$rentLiabilityId)->whereDate('created_at','<=',$entry_date)->orderby('created_at','DESC')->first(); 
                    $rentLibLedger['rent_liability_id'] = $rentLiabilityId;
                    $rentLibLedger['type'] = 1;
                    $rentLibLedger['type_id'] = $rentPaymentId;
                    $rentLibLedger['withdrawal'] = $rentAmount1;
                    $rentLibLedger['description'] = $detail;
                    $rentLibLedger['currency_code'] = $currency_code;
                    $rentLibLedger['payment_type'] = 'DR';
                    $rentLibLedger['payment_mode'] = 3;
                    $rentLibLedger['v_no'] = $v_no;
                    $rentLibLedger['v_date'] = $v_date;
                    $rentLibLedger['ssb_account_id_to'] = $ssb_account_id_to;
                    $rentLibLedger['created_at'] = $created_at;
                    $rentLibLedger['updated_at'] = $updated_at;
                    $rentLibLedger['daybook_ref_id'] = $refId;
                    $rentLibLedger['company_id'] = $company_id;
                    $rlL = \App\Models\RentLiabilityLedger::create($rentLibLedger);

                    ///---------------libility - (mines) ----------------
                    $head11 = 1;
                    $head21 = 8;
                    $head31 = 21;
                    $head41 = 60;
                    $head51 = NULL;
                    $allTran1 = CommanController::newHeadTransactionCreate($refId, $branch_id, $bank_id, $bank_ac_id, $head41, $type, $sub_type, $type_id, $associate_id = NULL, $member_id = NULL, $branch_id_to = NULL, $branch_id_from = NULL, $rentAmount1, $des, 'DR', $payment_mode, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $rentPaymentId, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $company_id);
                    $advance_amt = $request->advance_settel;
                    if ($advance_amt > 0) {
                        $advanceBalance = AdvancedTransaction::where('type',3)->where('sub_type',31)->where('status',1)->where('type_id',$rentLiabilityId)->where('settle_amount','>',0)->select('settle_amount','id','partial_daybook_ref_id')->get();

                        foreach ($advanceBalance as $key) {  
                            $advance_amt = $key['settle_amount'] - $advance_amt;
                            $advance_refId = $refId;
                            if($key['partial_daybook_ref_id'] != NULL){
                                $advance_refId = $key['partial_daybook_ref_id'];
                                $advance_refId = $advance_refId.','.$refId;
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
                                $advance_amt = $advance_amt*-1;
                            } elseif ($advance_amt > 0) {
                                $advance_amtt = AdvancedTransaction::where('id', $key['id'])->update([
                                    'settle_amount' => $advance_amt,
                                    'partial_daybook_ref_id' => $advance_refId
                                ]);
                                break;
                            }
                        }
                    }
                    
                    // ------ advance rent - (mines)----------------
                    if ($request->advance_settel > 0) {

                        $sub_type = 107;
                        $head11A = 2;
                        $head21A = 10;
                        $head31A = 29;
                        $head41A = 74;
                        $head51A = NULL;
                        $desA = $rentPaymentDetail['rentLib']->owner_name . "'s " . $rentPaymentDetail->month_name . '' . $rentPaymentDetail->year . ' rent payment has been settled  through Advance Payment';

                        $allTran1 = CommanController::newHeadTransactionCreate($refId, $branch_id, $bank_id, $bank_ac_id, $head41, $type, $sub_type, $type_id, $associate_id = NULL, $member_id = NULL, $branch_id_to = NULL, $branch_id_from = NULL,  $request->advance_settel, $desA, 'DR', 3, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $rentPaymentId, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $company_id);


                        $allTran14 = CommanController::newHeadTransactionCreate($refId, $branch_id, $bank_id, $bank_ac_id, $head41A, $type, $sub_type, $type_id, $associate_id = NULL, $member_id = NULL, $branch_id_to = NULL, $branch_id_from = NULL, $request->advance_settel, $desA, 'CR', 3, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $rentPaymentId, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $company_id);

                        $rentLibLedgerAdvance['rent_liability_id'] = $rentLiabilityId;
                        $rentLibLedgerAdvance['type'] = 7;
                        $rentLibLedgerAdvance['type_id'] = $rentPaymentId;
                        $rentLibLedgerAdvance['deposit'] = $request->advance_settel;
                        $rentLibLedgerAdvance['description'] = $desA;
                        $rentLibLedgerAdvance['currency_code'] = $currency_code;
                        $rentLibLedgerAdvance['payment_type'] = 'CR';
                        $rentLibLedgerAdvance['payment_mode'] = 3;
                        $rentLibLedgerAdvance['v_no'] = $v_no;
                        $rentLibLedgerAdvance['v_date'] = $v_date;
                        $rentLibLedgerAdvance['ssb_account_id_to'] = $ssb_account_id_to;
                        $rentLibLedgerAdvance['created_at'] = $created_at;
                        $rentLibLedgerAdvance['updated_at'] = $updated_at;
                        $rentLibLedgerAdvance['daybook_ref_id'] = $refId;
                        $rentLibLedgerAdvance['company_id'] = $company_id;
                        $rlLa = \App\Models\RentLiabilityLedger::create($rentLibLedgerAdvance);

                        $description_drA = $rentPaymentDetail['rentLib']->owner_name . 'A/c Dr ' . $rentAmount . '/-';
                        $description_crA = 'To Advance Rent A/c Cr ' . $rentAmount . '/-';

                        $brDaybook = CommanController::branchDaybookCreateModified($refId, $branch_id, $type, $sub_type, $type_id, $associate_id = NULL, $member_id = NULL, $branch_id_to = NULL, $branch_id_from = NULL, $request->advance_settel, $desA, $description_drA, $description_crA, 'DR', 3, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $rentPaymentId, $ssb_account_id_to, $company_id);
                    }
                    // ------ security  rent - (mines)----------------
                    // if ($request->security_settel > 0) {
                    //     $head11S = 2;
                    //     $head21S = 10;
                    //     $head31S = 29;
                    //     $head41S = 75;
                    //     $head51S = NULL;
                    //     $allTran123 = CommanController::headTransactionCreate($refId, $branch_id, $bank_id, $bank_ac_id, $head41S, $type, $sub_type, $type_id, $associate_id = NULL, $member_id = NULL, $branch_id_to = NULL, $branch_id_from = NULL, $opening_balance = NULL, $request->security_settel, $closing_balance = NULL, $des, 'CR', $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $rentPaymentId, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $cheque_bank_to_name, $cheque_bank_to_branch, $cheque_bank_to_ac_no, $cheque_bank_to_ifsc, $transction_bank_from_id, $transction_bank_from_ac_id, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc, $cheque_bank_from_id, $cheque_bank_ac_from_id);

                    //     $rentLibLedgerSecurity['rent_liability_id'] = $rentLiabilityId;
                    //     $rentLibLedgerSecurity['type'] = 6;
                    //     $rentLibLedgerSecurity['type_id'] = $rentPaymentId;
                    //     $rentLibLedgerSecurity['deposit'] = $request->security_settel;
                    //     $rentLibLedgerSecurity['description'] = $detail;
                    //     $rentLibLedgerSecurity['currency_code'] = $currency_code;
                    //     $rentLibLedgerSecurity['payment_type'] = 'CR';
                    //     $rentLibLedgerSecurity['payment_mode'] = 3;
                    //     $rentLibLedgerSecurity['v_no'] = $v_no;
                    //     $rentLibLedgerSecurity['v_date'] = $v_date;
                    //     $rentLibLedgerSecurity['ssb_account_id_to'] = $ssb_account_id_to;
                    //     $rentLibLedgerSecurity['created_at'] = $created_at;
                    //     $rentLibLedgerSecurity['updated_at'] = $updated_at;
                    //     $rentLibLedgerSecurity['daybook_ref_id'] = $refId;
                    //     $rlLs = \App\Models\RentLiabilityLedger::create($rentLibLedgerSecurity);
                    // }
                    $description_dr = $rentPaymentDetail['rentLib']->owner_name . 'A/c Dr ' . $rentAmount1 . '/-';
                    $description_cr = 'To SSB(' . $ssbAccount . ') A/c Cr ' . $rentAmount1 . '/-';
                    $brDaybook = CommanController::branchDaybookCreateModified($refId, $branch_id, $type, $sub_type, $type_id, $associate_id = NULL, $member_id = NULL, $branch_id_to = NULL, $branch_id_from = NULL, $rentAmount1, $des, $description_dr, $description_cr, 'CR', $payment_mode, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $rentPaymentId, $ssb_account_id_to, $company_id);
                } else {
                    /// bank
                    $neft_charge = 0;
                    $bank_id_from_c = $request->bank_id;
                    $bank_ac_id_from_c = $request->account_id;
                    $bankBla = \App\Models\BankBalance::where('bank_id', $bank_id_from_c)->where('account_id', $bank_ac_id_from_c)->whereDate('entry_date', '<=', $entry_date)->where('company_id', $company_id)->orderby('entry_date', 'desc')->sum('totalAmount');
                    if ($bankBla) {
                        if ($request->total_transfer_amount > $bankBla) {
                            return redirect('admin/rent/ledger-payable/' . $ledger_id)->with('alert', 'Sufficient amount not available in bank account!');
                        }
                    } else {
                        return redirect('admin/rent/ledger-payable/' . $ledger_id)->with('alert', 'Sufficient amount not available in bank account!');
                    }
                    $rentPaymentId = $request->rentPaymentId;
                    $rentPaymentDetail = \App\Models\RentPayment::with('rentLib')->where('id', $rentPaymentId)->first();
                    $rentPaymentBalance = $rentPaymentDetail->balance;
                    $rentLiabilityBalance = $rentPaymentDetail['rentLib']->bill_current_balance;
                    $branch_id = $rentPaymentDetail->branch_id;
                    $branchCode = getBranchCode($branch_id)->branch_code;
                    $rentAmount = $rentPaymentDetail->actual_transfer_amount - $rentPaymentDetail->transferred_amount;
                    $rentAmount1 = $request->transfer_amount;
                    $detail = 'Rent Payment ' . $rentPaymentDetail->month_name . ' ' . $rentPaymentDetail->year;
                    $bank_name_to = $rentPaymentDetail->owner_bank_name;
                    $bank_ac_to = $rentPaymentDetail->owner_bank_account_number;
                    $bank_ifsc_to = $rentPaymentDetail->owner_bank_ifsc_code;
                    //-------------------
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
                    $bank_id_ac =  $bank_ac_id = $bank_ac_id_from;
                    $payment_type = 'CR';
                    //------------
                    $daybookRef = CommanController::createBranchDayBookReferenceNew($rentAmount1, $globaldate);
                    $refId = $daybookRef;
                    //------------
                    $rentLiabilityId = $rentPaymentDetail['rentLib']->id;
                    $advance_amt = $request->advance_settel;
                    if ($advance_amt > 0) {
                        $advanceBalance = AdvancedTransaction::where('type',3)->where('sub_type',31)->where('status',1)->where('type_id',$rentLiabilityId)->where('settle_amount','>',0)->select('settle_amount','id','partial_daybook_ref_id')->get();

                        foreach ($advanceBalance as $key) {  
                            $advance_amt = $key['settle_amount'] - $advance_amt;
                            $advance_refId = $refId;
                            if($key['partial_daybook_ref_id'] != NULL){
                                $advance_refId = $key['partial_daybook_ref_id'];
                                $advance_refId = $advance_refId.','.$refId;
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
                                $advance_amt = $advance_amt*-1;
                            } elseif ($advance_amt > 0) {
                                $advance_amtt = AdvancedTransaction::where('id', $key['id'])->update([
                                    'settle_amount' => $advance_amt,
                                    'partial_daybook_ref_id' => $advance_refId
                                ]);
                                break;
                            }
                        }
                    }
                    
                    $lib_current_balance = $rentPaymentDetail['rentLib']->current_balance;
                    $des = $rentPaymentDetail['rentLib']->owner_name . "'s " . $rentPaymentDetail->month_name . '' . $rentPaymentDetail->year . ' rent payment has been transferred to the ' . $bank_name_to . ' A/c' . $bank_ac_to;
                    //$data['transferred_amount'] = $rentAmount;

                    $totalPaybaleAmount = $rentPaymentDetail->transferred_amount + $rentAmount;
                    $data['balance'] = $rentPaymentBalance - $rentAmount1;
                    $data['transferred_date']   = $entry_date;
                    $data['security_amount']   = $request->security_amount;
                    $data['settle_amount']      = $request->advance_settel + $request->security_settel;
                    $data['advance_payment']   = $request->advance_payment;
                    $data['current_advance_payment'] = $rlldata['advance_payment'] = $request->advance_payment - $request->advance_settel;
                    $data['transfer_amount']   = $totalPaybaleAmount;
                    $data['transfer_mode']      = $request->amount_mode;
                    $data['company_bank_id']    = $bank_id_from = $request->bank_id;
                    $data['company_bank_ac_id'] = $bank_ac_id_from = $request->account_id;
                    $paymentMode = $request->payment_mode;
                    $bankfrmDetail = \App\Models\SamraddhBank::where('id', $bank_id_from)->first();
                    $bankacfrmDetail = \App\Models\SamraddhBankAccount::where('bank_id', $bank_id_from)->first();
                    if ($request->payment_mode == 1) {
                        $data['company_cheque_id'] = $cheque_id = $request->cheque_id;
                        $data['company_cheque_no'] = $cheque_no = $request->cheque_number;
                        $cheque_date = $entry_date;
                        $rentLibLedger['cheque_id'] = $cheque_id;
                        $rentLibLedger['cheque_no'] = $cheque_no;
                        $rentLibLedger['cheque_date'] = $cheque_date;
                        $cheque_no = $cheque_no;
                        $cheque_date = $cheque_date;
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
                        $cheque_type = 1;
                        $cheque_id = $cheque_id;
                        //---------- Bill Payment --------
                        
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
                        $data['payment_mode']       = 1;
                    } else {
                        $data['online_transaction_no'] = $transction_no = $request->utr_tran;
                        $data['neft_charge'] = $neft_charge = $request->neft_charge;
                        $transaction_date = $entry_date;
                        $rentLibLedger['transaction_no'] = $transction_no;
                        $rentLibLedger['transaction_date'] = $transaction_date;
                        $rentLibLedger['transaction_charge'] = $neft_charge;
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
                        //---------- Bill Payment --------

                        // bank charge head entry +


                        $data['payment_mode']       = 0;
                    }
                    //  $data['status']             = 1;
                    $data['updated_at'] = $request->created_at;
                    $data['payment_ref_id'] = $refId;

                    $currentTransferAmount = $rentAmount1 + $request->advance_settel + $request->security_settel;

                    if ($currentTransferAmount == $rentAmount) {
                        $data['status']             = 1;
                        $data['transferred_amount'] = $totalPaybaleAmount;
                        $total_transfer_amount = $rentAmount;
                    } else {
                        $data['status']             = 2;
                        $data['transferred_amount'] = $totalPaybaleAmount;
                        $total_transfer_amount = $currentTransferAmount;
                    }


                    $rentPaymentUpdate = \App\Models\RentPayment::find($rentPaymentId);
                    $rentPaymentUpdate->update($data);
                    $total_transfer_amount = $rentPaymentDetail->actual_transfer_amount;
                    //----------------------------------- 

                    $rentLibLedger['rent_liability_id'] = $rentLiabilityId;
                    $rentLibLedger['type'] = 1;
                    $rentLibLedger['type_id'] = $rentPaymentId;
                    $rentLibLedger['withdrawal'] = $rentAmount1;
                    $rentLibLedger['description'] = $detail;
                    $rentLibLedger['currency_code'] = $currency_code;
                    $rentLibLedger['payment_type'] = 'DR';
                    $rentLibLedger['payment_mode'] = $paymentMode;
                    $rentLibLedger['created_at'] = $created_at;
                    $rentLibLedger['updated_at'] = $updated_at;
                    $rentLibLedger['daybook_ref_id'] = $refId;
                    $rentLibLedger['company_id'] = $company_id;
                    $rlL = \App\Models\RentLiabilityLedger::create($rentLibLedger);

                    //------------------------libility -(mines)  ------------
                    $head11 = 1;
                    $head21 = 8;
                    $head31 = 21;
                    $head41 = 60;
                    $head51 = NULL;
                    $allTran1 = CommanController::newHeadTransactionCreate($refId, $branch_id, $bank_id, $bank_ac_id, $head41, $type, $sub_type, $type_id, $associate_id = NULL, $member_id = NULL, $branch_id_to = NULL, $branch_id_from = NULL, $rentAmount1, $des, 'DR', $paymentMode, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $rentPaymentId, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $company_id);
                    // ------ advance rent - (mines)----------------
                    if ($request->advance_settel > 0) {
                        $sub_type = 107;
                        $head11A = 2;
                        $head21A = 10;
                        $head31A = 29;
                        $head41A = 74;
                        $head51A = NULL;
                        $desA = $rentPaymentDetail['rentLib']->owner_name . "'s " . $rentPaymentDetail->month_name . '' . $rentPaymentDetail->year . ' rent payment has been settled  through Advance Payment';
                        
                        $allTran1 = CommanController::newHeadTransactionCreate($refId, $branch_id, $bank_id, $bank_ac_id, $head41, $type, $sub_type, $type_id, $associate_id = NULL, $member_id = NULL, $branch_id_to = NULL, $branch_id_from = NULL, $request->advance_settel, $desA, 'DR', $paymentMode, $currency_code,  $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $rentPaymentId, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $company_id);
                        
                        $allTran14 = CommanController::newHeadTransactionCreate($refId, $branch_id, $bank_id, $bank_ac_id, $head41A, $type, $sub_type, $type_id, $associate_id = NULL, $member_id = NULL, $branch_id_to = NULL, $branch_id_from = NULL, $request->advance_settel, $desA, 'CR', $paymentMode, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $rentPaymentId, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $company_id);

                        $rentLibLedgerAdvance['rent_liability_id'] = $rentLiabilityId;
                        $rentLibLedgerAdvance['type'] = 7;
                        $rentLibLedgerAdvance['type_id'] = $rentPaymentId;
                        $rentLibLedgerAdvance['deposit'] = $request->advance_settel;
                        $rentLibLedgerAdvance['description'] = $desA;
                        $rentLibLedgerAdvance['currency_code'] = $currency_code;
                        $rentLibLedgerAdvance['payment_type'] = 'CR';
                        $rentLibLedgerAdvance['payment_mode'] = 3;
                        $rentLibLedgerAdvance['created_at'] = $created_at;
                        $rentLibLedgerAdvance['updated_at'] = $updated_at;
                        $rentLibLedgerAdvance['daybook_ref_id'] = $refId;
                        $rentLibLedgerAdvance['company_id'] = $company_id;
                        $rlLa = \App\Models\RentLiabilityLedger::create($rentLibLedgerAdvance);

                        $description_drA = $rentPaymentDetail['rentLib']->owner_name . 'A/c Dr ' . $rentAmount . '/-';
                        $description_crA = 'To Advance Rent A/c Cr ' . $rentAmount . '/-';

                        $brDaybook = CommanController::branchDaybookCreateModified($refId, $branch_id, $type, $sub_type, $type_id, $associate_id = NULL, $member_id = NULL, $branch_id_to = NULL, $branch_id_from = NULL, $request->advance_settel, $desA, $description_drA, $description_crA, 'DR', 3, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $rentPaymentId, $ssb_account_id_to, $company_id);
                    }
                    // ------ security  rent - (mines)----------------
                    // if ($request->security_settel > 0) {
                    //     $head11S = 2;
                    //     $head21S = 10;
                    //     $head31S = 29;
                    //     $head41S = 75;
                    //     $head51S = NULL;
                    //     $allTran123 = CommanController::headTransactionCreate($refId, $branch_id, $bank_id, $bank_ac_id, $head41S, $type, $sub_type, $type_id, $associate_id = NULL, $member_id = NULL, $branch_id_to = NULL, $branch_id_from = NULL, $opening_balance = NULL, $request->security_settel, $closing_balance = NULL, $des, 'CR', $paymentMode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $rentPaymentId, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $cheque_bank_to_name, $cheque_bank_to_branch, $cheque_bank_to_ac_no, $cheque_bank_to_ifsc, $transction_bank_from_id, $transction_bank_from_ac_id, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc, $cheque_bank_from_id, $cheque_bank_ac_from_id);

                    //     $rentLibLedgerSecurity['rent_liability_id'] = $rentLiabilityId;
                    //     $rentLibLedgerSecurity['type'] = 6;
                    //     $rentLibLedgerSecurity['type_id'] = $rentPaymentId;
                    //     $rentLibLedgerSecurity['deposit'] = $request->security_settel;
                    //     $rentLibLedgerSecurity['description'] = 'Security Amount Adjustment';
                    //     $rentLibLedgerSecurity['currency_code'] = $currency_code;
                    //     $rentLibLedgerSecurity['payment_type'] = 'CR';
                    //     $rentLibLedgerSecurity['payment_mode'] = $paymentMode;
                    //     $rentLibLedgerSecurity['created_at'] = $created_at;
                    //     $rentLibLedgerSecurity['updated_at'] = $updated_at;
                    //     $rentLibLedgerSecurity['daybook_ref_id'] = $refId;
                    //     $rlLs = \App\Models\RentLiabilityLedger::create($rentLibLedgerSecurity);
                    // }

                    $bankAmountRent = $rentAmount;
                    $description_dr = $rentPaymentDetail['rentLib']->owner_name . 'A/c Dr ' . $rentAmount1 . '/-';
                    $description_cr = 'To ' . $bankfrmDetail->bank_name . '(' . $bankacfrmDetail->account_no . ') A/c Cr ' . $rentAmount1 . '/-';
                    // ---------------- branch daybook entry -----------------
                    $brDaybook = CommanController::branchDaybookCreateModified($refId, $branch_id, $type, $sub_type, $type_id, $associate_id = NULL, $member_id = NULL, $branch_id_to = NULL, $branch_id_from = NULL, $rentAmount1, $des, $description_dr, $description_cr, 'DR', $paymentMode, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $rentPaymentId, $ssb_account_id_to, $company_id);
                    // ------------------ samraddh bank entry -(mines) ---------------
                    $bankAmountRent =  $rentAmount1;
                    $description_dr_b = $rentPaymentDetail['rentLib']->owner_name . 'A/c Dr ' . $bankAmountRent . '/-';
                    $description_cr_b = 'To ' . $bankfrmDetail->bank_name . '(' . $bankacfrmDetail->account_no . ') A/c Cr ' . $bankAmountRent . '/-';
                    $allTran2 = CommanController::newHeadTransactionCreate($refId, $branch_id, $bank_id, $bank_ac_id, $bankfrmDetail->account_head_id, $type, $sub_type, $type_id, $associate_id = NULL, $member_id = NULL, $branch_id_to = NULL, $branch_id_from = NULL, $bankAmountRent, $des, 'CR', $paymentMode, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $rentPaymentId, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $company_id);

                    $bncktrn = $bankAmountRent;
                    $smbdc = CommanController::NewFieldAddSamraddhBankDaybookCreateModify($refId, $bank_id_from, $bank_ac_id_from, $type, $sub_type, $type_id, $associate_id = NULL, $member_id = NULL, $branch_id, $opening_balance = NULL, $bncktrn, $closing_balance = NULL, $des, $description_dr_b, $description_cr_b, 'DR', $paymentMode, $currency_code, $amount_to_id = NULL, $amount_to_name = NULL, $amount_from_id = NULL, $amount_from_name = NULL, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $rentPaymentId, $ssb_account_id_to, $cheque_bank_from_id, $cheque_bank_ac_from_id, $cheque_bank_to_name, $cheque_bank_to_branch, $cheque_bank_to_ac_no, $cheque_bank_to_ifsc, $transction_bank_from_id, $transction_bank_from_ac_id, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $jv_unique_id, $cheque_type, $cheque_id, $company_id);
                }
            }
            if ($request->amount_mode == 2) {
                if ($request->neft_charge > 0) {
                    $allTranSSB = CommanController::newHeadTransactionCreate($refId, 29, $bank_id, $bank_ac_id, 92, $type, $sub_type, $type_id, $associate_id = NULL, $member_id = NULL, $branch_id_to = NULL, $branch_id_from = NULL, $request->neft_charge, $des, 'DR', $paymentMode, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $rentPaymentId, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $company_id);

                    $allTran2 = CommanController::newHeadTransactionCreate($refId, 29, $bank_id, $bank_ac_id, $bankfrmDetail->account_head_id, $type, $sub_type, $type_id, $associate_id = NULL, $member_id = NULL, $branch_id_to = NULL, $branch_id_from = NULL, $request->neft_charge, $des, 'CR', $paymentMode, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $rentPaymentId, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $company_id);


                    CommanController::NewFieldAddSamraddhBankDaybookCreateModify($refId, $bank_id_from, $bank_ac_id_from, $type, $sub_type, $type_id, $associate_id = NULL, $member_id = NULL, 29, $opening_balance = NULL, $request->neft_charge, $closing_balance = NULL, $des, $description_dr_b, $description_cr_b, 'DR', $paymentMode, $currency_code, $amount_to_id = NULL, $amount_to_name = NULL, $amount_from_id = NULL, $amount_from_name = NULL, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $rentPaymentId, $ssb_account_id_to, $cheque_bank_from_id, $cheque_bank_ac_from_id, $cheque_bank_to_name, $cheque_bank_to_branch, $cheque_bank_to_ac_no, $cheque_bank_to_ifsc, $transction_bank_from_id, $transction_bank_from_ac_id, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $jv_unique_id, $cheque_type, $cheque_id, $company_id);
                }
            }
            $payment_count = \App\Models\RentPayment::where('ledger_id', $ledger_id)->where('status', 1)->get();
            $total_payment_count = \App\Models\RentPayment::where('ledger_id', $ledger_id)->get();

            $l = \App\Models\RentLedger::where('id', $ledger_id)->first();
            if ($l->transfer_amount > 0) {
                $ledgertransfer_amount = $total_transfer_amount + $l->transfer_amount;
            } else {
                $ledgertransfer_amount = $total_transfer_amount;
            }
            $updateLedger['transfer_amount'] = $ledgertransfer_amount;
            if (count($payment_count) == count($total_payment_count)) {
                $updateLedger['status'] = 1;
            } else {
                $updateLedger['status'] = 2;
            }
            $leaserdataUpdate = \App\Models\RentLedger::find($ledger_id);
            $leaserdataUpdate->update($updateLedger);


            $sumTransferredAmount = \App\Models\RentPayment::where('rent_liability_id', $rentPaymentDetail['rentLib']->id)->sum('transferred_amount');
            $sumActualTransferAmount = \App\Models\RentPayment::where('rent_liability_id', $rentPaymentDetail['rentLib']->id)->sum('actual_transfer_amount');
            $libilityBalance = $sumActualTransferAmount - $sumTransferredAmount;
            $advanceBalance = $rentPaymentDetail['rentLib']->advance_payment - $request->advance_settel;

            $lib['current_balance'] = $libilityBalance;
            $lib['advance_payment'] = $advanceBalance;
            $libUpdate = RentLiability::find($rentPaymentDetail['rentLib']->id);
            $libUpdate->update($lib);
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            dd($ex->getMessage(),$ex->getLine());
            return back()->with('alert', $ex->getMessage());
        }
        return redirect('admin/rent/rent-ledger')->with('success', 'Rent Payment Transferred Successfully');
    }
    /**
     * Display a listing of the account heads.     
     *
     * @return \Illuminate\Http\Response
     */
    public function ledgerLiability($id)
    {
        $data['title'] = 'Rent Management | Rent Liability Ledger Report';
        $data['liability'] = $id;
        $data['detail'] = \App\Models\RentLiability::where('id', $id)->first();
        return view('templates.admin.rent-management.rent_ledger_lib_report', $data);
    }
    /**
     * Display the specified resource.
     *
     * @param  \App\Reservation  $reservation
     * @return \Illuminate\Http\Response
     */
    public function rentLiabilityLedgerListing(Request $request)
    {
        if ($request->ajax()) {
            $lib = $request->liability;
            $data = \App\Models\RentLiabilityLedger::where('rent_liability_id', $lib)->with('company:id,name')->where('is_deleted', 0)->orderby('created_at', 'DESC')->orderby('id', 'DESC')->get();
            // print_r($data);die;
            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('owner_name', function ($row) {
                    $owner_name = $row['rentLibL']->owner_name;
                    return $owner_name;
                })
                ->rawColumns(['owner_name'])
                ->addColumn('owner_mobile_number', function ($row) {
                    $owner_mobile_number = $row['rentLibL']->owner_mobile_number;
                    return $owner_mobile_number;
                })
                ->addColumn('company', function ($row) {
                    $company = $row['company']->name;
                    return $company;
                })
                ->rawColumns(['owner_mobile_number'])
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
                        $payment_mode = 'SSB/JV';
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
                    if(empty($amount)) {
                        $amount = 0.00;
                    }
                    return number_format((float)$amount, 2, '.', '');
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
                // ->addColumn('opening_balance', function($row){                
                //     $opening_balance = $row->opening_balance;                  
                //     return $opening_balance;
                // })
                // ->rawColumns(['opening_balance'])           
                ->make(true);
        }
    }
    public function rentpaymentledgerReport()
    {
        if (check_my_permission(Auth::user()->id, "82") != "1") {
            return redirect()->route('admin.dashboard');
        }
        $data['title'] = 'Rent Management | Rent Payment  Report';
        $data['branch'] = Branch::select('id', 'name', 'branch_code')->where('status', 1)->get();
        $data['leaserData'] = \App\Models\RentLedger::select('month_name', 'year', 'total_amount', 'transfer_amount', 'id', 'created_at', 'status')->get();
        $data['accountHeadLibilities'] = AccountHeads::select('id', 'sub_head',)->where('parent_id', 53)->get();
        return view('templates.admin.rent-management.rent_payment_ledger_report', $data);
    }
    public function rentPaymentLedgerReportListing(Request $request)
    {
        if ($request->ajax() && check_my_permission(Auth::user()->id, "82") == "1") {
            $is_search = $request->is_search;
            $branch_id = $request->branch_id;
            $company_id = $request->company_id;
            $status = $request->status;
            $month = $request->month;
            $year = $request->year;
            $rent_type = $request->rent_type;
            $arrFormData = array();
            if (!empty($_POST['searchform'])) {
                foreach ($_POST['searchform'] as $frm_data) {
                    $arrFormData[$frm_data['name']] = $frm_data['value'];
                }
            }
            $branch_id = $arrFormData['branch_id'];
            $data = \App\Models\RentPayment::select('id', 'rent_liability_id', 'branch_id', 'company_bank_id', 'company_bank_ac_id', 'owner_ssb_id', 'employee_id', 'rent_liability_id', 'owner_bank_name', 'owner_bank_account_number', 'owner_bank_ifsc_code', 'security_amount', 'rent_amount', 'tds_amount', 'yearly_increment', 'status', 'current_advance_payment', 'actual_transfer_amount', 'transfer_amount', 'advance_payment', 'settle_amount', 'transferred_date', 'v_date', 'v_no', 'transfer_mode', 'payment_mode', 'company_cheque_id', 'company_cheque_no', 'online_transaction_no', 'neft_charge', 'company_id', 'month_name', 'year')
                ->with(['rentLib' => function ($q) {
                    $q->select('id', 'branch_id', 'rent_agreement_file_id', 'employee_id', 'rent_type', 'owner_ssb_id', 'place', 'owner_name', 'owner_mobile_number', 'owner_pen_number', 'owner_aadhar_number', 'owner_bank_name', 'owner_bank_account_number', 'owner_bank_ifsc_code', 'security_amount', 'rent', 'yearly_increment', 'office_area', 'advance_payment', 'current_balance', 'created_at', 'status', 'agreement_from', 'agreement_to')
                        ->with(['AcountHeadCustom' => function ($q) {
                            $q->select('id', 'head_id', 'sub_head');
                        }]);
                }])->with(['rentSSB' => function ($q) {
                    $q->select('id', 'account_no');
                }])->with(['rentBank' => function ($q) {
                    $q->select('id', 'bank_name');
                }])->with(['rentBankAccount' => function ($q) {
                    $q->select('id', 'account_no');
                }])->with(['rentBranch' => function ($query) {
                    $query->select('id', 'name', 'branch_code');
                }])->with(['rentEmp' => function ($query) {
                    $query->select('id', 'designation_id', 'employee_code', 'employee_name', 'mobile_no')->with(['designation' => function ($q) {
                        $q->select('id', 'designation_name');
                    }]);
                }])->has('rentCompany')->with('rentCompany:id,name')->where('is_deleted',0);


            if (Auth::user()->branch_id > 0) {
                $id = Auth::user()->branch_id;
                $data = $data->where('branch_id', '=', $id);
            }
            if ($is_search == 'yes') {
                if ($branch_id > 0) {
                    $data = $data->where('branch_id', $branch_id);
                }
                if ($status != '') {
                    $status = $status;
                    $data = $data->where('status', $status);
                }
                if ($company_id > 0) {
                    $data = $data->where('company_id', $company_id);
                }
                if ($month != '') {
                    $month = $month;
                    $data = $data->where('month', $month);
                }
                if ($year != '') {
                    $year = $year;
                    $data = $data->where('year', $year);
                }
                if ($rent_type != '') {
                    $rent_type = $rent_type;
                    $data = $data->whereHas('rentLib', function ($query) use ($rent_type) {
                        $query->where('rent_liabilities.rent_type', $rent_type);
                    });
                }
            }

            $data = $data->orderby('created_at', 'DESC')->get();
            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('rentCompany', function ($row) {
                    $company = $row['rentCompany']->name;
                    return $company;
                })
                ->rawColumns(['rentCompany'])
                ->addColumn('branch', function ($row) {
                    $branch = $row['rentBranch']->name . " (" . $row['rentBranch']->branch_code . ")";
                    return $branch;
                })
                ->rawColumns(['branch'])
                ->addColumn('rent_type', function ($row) {
                    $rent_type = $row['rentLib']['AcountHeadCustom']->sub_head; //getAcountHead($row['rentLib']->rent_type);
                    return $rent_type;
                })
                ->rawColumns(['rent_type'])
                ->addColumn('month', function ($row) {
                    $month = $row->month_name;
                    return $month;
                })
                ->rawColumns(['month'])
                ->addColumn('year', function ($row) {
                    $year = $row->year;
                    return $year;
                })
                ->rawColumns(['year'])
                ->addColumn('period_from', function ($row) {
                    $period_from = date("d/m/Y", strtotime(convertDate($row['rentLib']->agreement_from)));
                    return $period_from;
                })
                ->rawColumns(['period_from'])
                ->addColumn('period_to', function ($row) {
                    $period_to = date("d/m/Y", strtotime(convertDate($row['rentLib']->agreement_to)));
                    return $period_to;
                })
                ->rawColumns(['period_to'])
                ->addColumn('address', function ($row) {
                    $address = $row['rentLib']->place;
                    return $address;
                })
                ->rawColumns(['address'])
                ->addColumn('owner_name', function ($row) {
                    $owner_name = $row['rentLib']->owner_name;
                    return $owner_name;
                })
                ->rawColumns(['owner_name'])
                ->addColumn('owner_mobile_number', function ($row) {
                    $owner_mobile_number = $row['rentLib']->owner_mobile_number;
                    return $owner_mobile_number;
                })
                ->rawColumns(['owner_mobile_number'])
                ->addColumn('owner_pen_card', function ($row) {
                    $owner_pen_card = $row['rentLib']->owner_pen_number;
                    return $owner_pen_card;
                })
                ->rawColumns(['owner_pen_card'])
                ->addColumn('owner_aadhar_card', function ($row) {
                    $owner_aadhar_card = $row['rentLib']->owner_aadhar_number;
                    return $owner_aadhar_card;
                })
                ->rawColumns(['owner_aadhar_card'])
                ->addColumn('owner_ssb_account', function ($row) {
                    $owner_ssb_account = '';
                    if ($row['rentSSB']) {
                        $owner_ssb_account = isset($row['rentSSB']->account_no) ? $row['rentSSB']->account_no : "N/A";
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
                    $rent = number_format((float)$row->rent_amount, 2, '.', '');
                    return $rent;
                })
                ->rawColumns(['rent'])
                ->addColumn('yearly_increment', function ($row) {
                    $yearly_increment = number_format((float)$row->yearly_increment, 2, '.', '') . '%';
                    return $yearly_increment;
                })
                ->rawColumns(['yearly_increment'])
                ->addColumn('office_area', function ($row) {
                    $office_area = $row['rentLib']->office_area;
                    return $office_area;
                })
                ->rawColumns(['office_area'])
                ->addColumn('employee_code', function ($row) {
                    $employee_code = $row['rentEmp']->employee_code;
                    return $employee_code;
                })
                ->rawColumns(['employee_code'])
                ->addColumn('employee_name', function ($row) {
                    $employee_name = $row['rentEmp']->employee_name;
                    return $employee_name;
                })
                ->rawColumns(['employee_name'])
                ->addColumn('employee_designation', function ($row) {
                    $employee_designation = $row['rentEmp']['designation']->designation_name; //getDesignationData('designation_name',$row['rentEmp']->designation_id)->designation_name;
                    return $employee_designation;
                })
                ->rawColumns(['employee_designation'])
                ->addColumn('mobile_number', function ($row) {
                    $mobile_number = $row['rentEmp']->mobile_no;
                    return $mobile_number;
                })
                ->rawColumns(['mobile_number'])
                ->addColumn('status', function ($row) {
                    $status = 'Pending';
                    if ($row->status == 1) {
                        $status = 'Transferred ';
                    }

                    return $status;
                })
                ->rawColumns(['status'])
                ->addColumn('current_advance_payment', function ($row) {
                    $current_advance_payment = 'N/A';
                    if ($row->current_advance_payment) {
                        $current_advance_payment = number_format((float)$row->current_advance_payment, 2, '.', '');
                    }
                    return $current_advance_payment;
                })
                ->rawColumns(['current_advance_payment'])

                ->addColumn('actual', function ($row) {
                    $actual = 'N/A';
                    if ($row->actual_transfer_amount) {
                        $actual = number_format((float)$row->actual_transfer_amount + $row->tds_amount, 2, '.', '');
                    }
                    return $actual;
                })
                ->rawColumns(['actual'])
                ->addColumn('tds_amount', function ($row) {
                    $actual = 'N/A';
                    if ($row->tds_amount) {
                        $tds_amount = number_format((float)$row->tds_amount, 2, '.', '');
                    }
                    return $tds_amount;
                })
                ->rawColumns(['tds_amount'])
                ->addColumn('transfer', function ($row) {
                    $transfer = number_format((float)$row->transfer_amount, 2, '.', '');
                    return $transfer;
                })
                ->rawColumns(['transfer'])
                ->addColumn('advance', function ($row) {
                    $advance = 'N/A';
                    if ($row->advance_payment) {
                        $advance = number_format((float)$row->advance_payment, 2, '.', '');
                    }
                    return $advance;
                })
                ->rawColumns(['advance'])
                ->addColumn('settle', function ($row) {
                    $settle = 'N/A';
                    if ($row->settle_amount) {
                        $settle = number_format((float)$row->settle_amount, 2, '.', '');
                    }
                    return $settle;
                })
                ->rawColumns(['settle'])
                ->addColumn('transfer_date', function ($row) {
                    $transfer_date = 'N/A';
                    if ($row->transferred_date) {
                        $transfer_date = date("d/m/Y", strtotime(convertDate($row->transferred_date)));
                    }
                    return $transfer_date;
                })
                ->rawColumns(['transfer_date'])
                ->addColumn('v_date', function ($row) {
                    $v_date = 'N/A';
                    if ($row->v_date) {
                        $v_date = date("d/m/Y", strtotime(convertDate($row->v_date)));
                    }
                    return $v_date;
                })
                ->rawColumns(['v_date'])
                ->addColumn('v_no', function ($row) {
                    $v_no = 'N/A';
                    if ($row->v_no) {
                        $v_no = $row->v_no;
                    }
                    return $v_no;
                })
                ->rawColumns(['v_no'])
                ->addColumn('mode', function ($row) {
                    $mode = 'N/A';
                    if ($row->transfer_mode == 1) {
                        $mode = 'SSB/JV';
                    }
                    if ($row->transfer_mode == 2) {
                        $mode = 'Bank';
                    }

                    return $mode;
                })
                ->rawColumns(['mode'])
                ->addColumn('bank', function ($row) {
                    $bank = 'N/A';
                    if ($row->company_bank_id) {
                        $bank = $row['rentBank']->bank_name;
                    }
                    return $bank;
                })
                ->rawColumns(['bank'])
                ->addColumn('bank_ac', function ($row) {
                    $bank_ac = 'N/A';
                    if ($row->company_bank_ac_id) {
                        $bank_ac = $row['rentBankAccount']->account_no;
                    }
                    return $bank_ac;
                })
                ->rawColumns(['bank_ac'])
                ->addColumn('payment_mode', function ($row) {


                    $payment_mode = 'NA';

                    if ($row->payment_mode == '1') {
                        $payment_mode = 'Cheque';
                    }
                    if ($row->payment_mode == '0') {
                        $payment_mode = 'Online';
                    }

                    return $payment_mode;
                })
                ->rawColumns(['payment_mode'])
                ->addColumn('cheque', function ($row) {
                    $cheque = 'N/A';
                    if ($row->company_cheque_id) {
                        $cheque = $row->company_cheque_no;
                    }
                    return $cheque;
                })
                ->rawColumns(['cheque'])
                ->addColumn('online_no', function ($row) {
                    $online_no = 'N/A';
                    if ($row->online_transaction_no) {
                        $online_no = $row->online_transaction_no;
                    }
                    return $online_no;
                })
                ->rawColumns(['online_no'])
                ->addColumn('neft', function ($row) {
                    $neft = 'N/A';
                    if ($row->neft_charge) {
                        $neft = $row->neft_charge;
                    }
                    return $neft;
                })
                ->rawColumns(['neft'])
                ->make(true);
        }
    }
    public function export(Request $request)
    {
        $token = session()->get('_token');

        $data = Cache::get('rent_ledger_create_list'.$token);
        $count = Cache::get('rent_ledger_create_count'.$token);
        $input = $request->all();
        // dd($input);
        $start = $input["start"];
        $limit = $input["limit"];
        $company_id = $input["company_id"];
        $daata['companies'] = Companies::where('id', $company_id)->get(['name', 'id'])->toArray();
        $daata['c_name'] = $daata['companies'][0]['name'];
        $daata['c_id'] = $daata['companies'][0]['id'];
        $returnURL = URL::to('/') . "/report/employeesalarypayable.csv";
        $fileName = env('APP_EXPORTURL') .'report/employeesalarypayable.csv';
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
            $val['Company Name'] = $daata['c_name'];
            $val['BR Name'] = $row['liability_branch']['name'];
            $val['Rent Type'] = $row['acount_head_custom']['sub_head'];
            $val['Owner Name'] = $row['owner_name'];
            $val['Security Amount'] = number_format((float)$row['security_amount'], 2, '.', '');
            $val['Rent'] = number_format((float)$row['rent'], 2, '.', '');
            $val['Amount'] = number_format((float)$row['rent'], 2, '.', '');
            $val['Tds Amount'] = number_format((float)0, 2, '.', '');
            $val['Transfer Amount'] = number_format((float)$row['rent'], 2, '.', '');
            $val['Employee Name'] = $row['employee_rent']['employee_name'];
            $val['Employee Designation'] = $row['employee_rent']['designation']['designation_name'];
            $val['Employee Mobile'] = $row['employee_rent']['mobile_no'];
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
            $percentage = number_format((float)$percentage, 1, '.', '');
        }
        // Output some stuff for jquery to use
        $response = array(
            'result'        => $result,
            'start'         => $start,
            'limit'         => $limit,
            'totalResults'  => $totalResults,
            'fileName' => $returnURL,
            'percentage' => $percentage
        );
        echo json_encode($response);
    }


    public function partPayment($id, $leaser_id)
    {
        
        $data['title'] = 'Rent Management | Rent Part Payment';
        $data['leaser_id'] = $leaser_id;
        //print_r($_POST);die;
        $data['rent_list'] = \App\Models\RentPayment::with('rentLib')->with('rentSSB')->with(['rentBranch' => function ($query) {
            $query->select('id', 'name', 'branch_code', 'sector', 'regan', 'zone');
        }])->with(['rentEmp' => function ($query) {
            $query->select('id', 'designation_id', 'employee_code', 'employee_name', 'mobile_no');
        }])->with('rentCompany:id,name')->where('id', $id)->first();
        $check_data = \App\Models\RentLedger::where('id', $leaser_id)->first();
        $data['ledger_date'] = date("d/m/Y", strtotime($check_data->created_at));
        $data['c_id'] = $data['rent_list']['rentCompany']['id'];
        // dd($data['c_id']);
        $data['bank'] = \App\Models\SamraddhBank::where('status', 1)->where('company_id', $data['c_id'])->get();
        if ($data['rent_list']->actual_transfer_amount == $data['rent_list']->transferred_amount) {
            return redirect('admin/rent/ledger-payable/' . $leaser_id)->with('alert', 'Payment has already done');
        }
        // pd( $data['rent_list']->toArray());

        return view('templates.admin.rent-management.part_payment', $data);
    }


    public function partPaymentSave(Request $request)
    {
        // print_r($_POST);die;

        $rules = [
            'amount_mode' => ['required'],
            'total_transfer_amount' => ['required'],
            'leaser_id' => ['required'],
            // 'bank_id' => ['required'], 
            //   'account_id' => ['required'], 
            //  'advance_payment' => ['required'],
            'actual_transfer' => ['required'],
            //  'security_amount' => ['required'],
            ///  'advance_settel' => ['required'],
            // 'security_settel' => ['required'],
            'transfer_amount' => ['required'],
        ];

        $customMessages = [
            'required' => ':Attribute  is required.',
        ];
        $this->validate($request, $rules, $customMessages);
        DB::beginTransaction();
        try {
            $company_id = $request->company_id;
            // dd($request->all());
            $ledger_id = $leaser = $request->leaser_id;
            $globaldate = $request->created_at;
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
            // head  entry----------------------------                    
            $type = 10;
            $sub_type = 108;
            $type_id = $leaser;


            if ($request->amount_mode == 1) {



                //ssb  
                $rentPaymentId = $request->rentPaymentId;
                $rentPaymentDetail = \App\Models\RentPayment::with('rentLib')->where('id', $rentPaymentId)->first();
                $rentPaymentBalance = $rentPaymentDetail->balance;
                $rentLiabilityBalance = $rentPaymentDetail['rentLib']->bill_current_balance;


                $rentAmount = $rentAmount1 = $request->transfer_amount;
                $totalPaybaleAmount = $rentPaymentDetail->transferred_amount + $rentAmount;


                $daybookRef = CommanController::createBranchDayBookReferenceNew($rentAmount, $globaldate);
                $refId = $daybookRef;
                $data['transferred_date']   = $entry_date;
                $data['transfer_amount']    = $totalPaybaleAmount;
                $data['transfer_mode']      = $request->amount_mode;
                $data['v_no']               = $v_no;
                $data['v_date']             = $entry_date;

                $data['balance'] = $rentPaymentBalance - $rentAmount;

                $part_payment_ref_id = $rentPaymentDetail->part_payment_ref_id;
                $partPaymentRefId = $part_payment_ref_id . ',' . $refId;

                $data['part_payment_ref_id'] = $partPaymentRefId;
                $data['updated_at'] = $request->created_at;
                $totalRentPaybale = $rentPaymentDetail->actual_transfer_amount;
                if ($totalPaybaleAmount == $totalRentPaybale) {
                    $data['status']             = 1;
                    $data['transferred_amount'] = $totalPaybaleAmount;
                    $total_transfer_amount = $rentAmount;
                } else {
                    $data['status']             = 2;
                    $data['transferred_amount'] = $totalPaybaleAmount;
                    $total_transfer_amount = $rentAmount;
                }


                $rentPaymentUpdate = \App\Models\RentPayment::find($rentPaymentId);
                $rentPaymentUpdate->update($data);

                $branch_id = $rentPaymentDetail->branch_id;
                $branchCode = getBranchCode($branch_id)->branch_code;

                $ssbAccountDetail = getSavingAccountMemberId($rentPaymentDetail->owner_ssb_id);
                $ssbBalance = $ssbAccountDetail->balance;
                $member_id = $ssbAccountDetail->member_id;
                $ssbId = $ssbAccountDetail->id;
                $ssbAccount = $ssbAccountDetail->account_no;
                //------------ ssb tran head entry start  --------------------------
                $detail = 'Rent Part Payment of ' . $rentPaymentDetail->month_name . ' ' . $rentPaymentDetail->year;
                $ssbTranCalculation = CommanController::SSBDateCR($ssbId, $ssbAccount, $ssbBalance, $rentAmount, $detail, 'INR', 'CR', 3, $branch_id, $associate_id = NULL, 7, $created_at, $refId,$company_id);
                $SSbbck = CommanController::SSBBackDateCR($ssbId, $created_at, $rentAmount ,$company_id);
                $ssbRentTranID = $ssbTranCalculation;
                $amountArray = array('1' => $rentAmount);
                $deposit_by_name = $created_by_name;
                $deposit_by_id = $created_by_id;
                // $rdCreateTran = CommanController::createTransaction(NULL, 1, $ssbAccountDetail->id, $member_id, $branch_id, $branchCode, $amountArray, 77, $deposit_by_name, $deposit_by_id, $ssbAccountDetail->account_no, $cheque_dd_no = 0, $bank_name = null, $branch_name = null, $payment_date = null, $online_payment_id = null, $online_payment_by = null, $saving_account_id = 0, 'CR');
                //-------------------- ssb head entry start -----------------
                $payment_mode = 3;
                $payment_type = 'CR';

                $rentLiabilityId = $rentPaymentDetail['rentLib']->id;
                $lib_current_balance = $rentPaymentDetail['rentLib']->current_balance;
                $des = $rentPaymentDetail['rentLib']->owner_name . "'s " . $rentPaymentDetail->month_name . '' . $rentPaymentDetail->year . ' rent payment has been transferred to the SSB A/c' . $ssbAccount;
                $head1SSB = 1;
                $head2SSB = 8;
                $head3SSB = 20;
                $head4SSB = 56;
                $head5SSB = NULL;
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
                $ssb_account_id_to = $ssbId;
                $cheque_bank_from_id = NULL;
                $cheque_bank_ac_from_id = NULL;
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
                $jv_unique_id = NULL;
                $ssb_account_tran_id_to = $ssbRentTranID;
                $ssb_account_tran_id_from = NULL;
                $cheque_type = NULL;
                $cheque_id = NULL;
                $amount_to_id = NULL;
                $amount_to_name = NULL;
                $amount_from_id = NULL;
                $amount_from_name = NULL;
                $bank_id = NULL;
                $bank_ac_id = NULL;
                // ssb head entry +
                $allTranSSB = CommanController::newHeadTransactionCreate($refId, $branch_id, $bank_id, $bank_ac_id, $head4SSB, 4, 49, $ssbId, $associate_id = NULL, $member_id, $branch_id_to = NULL, $branch_id_from = NULL, $rentAmount, $des, 'CR', $payment_mode, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $ssbRentTranID, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $company_id);
                // ssb Member transaction  +
                // $memberTranSSB = CommanController::NewFieldAddMemberTransactionCreate($refId, 4, 49, $ssbId, $associate_id = NULL, $member_id, $branch_id, $bank_id = NULL, $account_id = NULL, $rentAmount1, $des, $payment_type, $payment_mode, $currency_code, $amount_to_id = NULL, $amount_to_name = NULL, $amount_from_id = NULL, $amount_from_name = NULL, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $ssbRentTranID, $ssb_account_id_to, $cheque_bank_from_id, $cheque_bank_ac_from_id, $cheque_bank_to_name, $cheque_bank_to_branch, $cheque_bank_to_ac_no, $cheque_bank_to_ifsc, $transction_bank_from_id, $transction_bank_from_ac_id, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $jv_unique_id, $cheque_type, $cheque_id);
                //------------ ssb tran head entry end --------------------------
                
                
                $ssb_account_id_to = $ssbAccountDetail->id;
                // $rentGet=\App\Models\RentLiabilityLedger::where('rent_liability_id',$rentLiabilityId)->whereDate('created_at','<=',$entry_date)->orderby('created_at','DESC')->first(); 
                $rentLibLedger['rent_liability_id'] = $rentLiabilityId;
                $rentLibLedger['type'] = 8;
                $rentLibLedger['type_id'] = $rentPaymentId;
                $rentLibLedger['withdrawal'] = $rentAmount1;
                $rentLibLedger['description'] = $detail;
                $rentLibLedger['currency_code'] = $currency_code;
                $rentLibLedger['payment_type'] = 'DR';
                $rentLibLedger['payment_mode'] = 3;
                $rentLibLedger['v_no'] = $v_no;
                $rentLibLedger['v_date'] = $v_date;
                $rentLibLedger['ssb_account_id_to'] = $ssb_account_id_to;
                $rentLibLedger['created_at'] = $created_at;
                $rentLibLedger['updated_at'] = $updated_at;
                $rentLibLedger['daybook_ref_id'] = $refId;
                $rentLibLedger['company_id'] = $company_id;
                $rlL = \App\Models\RentLiabilityLedger::create($rentLibLedger);

                ///---------------libility - (mines) ----------------
                $head11 = 1;
                $head21 = 8;
                $head31 = 21;
                $head41 = 60;
                $head51 = NULL;
                $allTran1 = CommanController::newHeadTransactionCreate($refId, $branch_id, $bank_id, $bank_ac_id, $head41, $type, $sub_type, $type_id, $associate_id = NULL, $member_id = NULL, $branch_id_to = NULL, $branch_id_from = NULL, $rentAmount1,  $des, 'DR', $payment_mode, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $rentPaymentId, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $company_id);

                $description_dr = $rentPaymentDetail['rentLib']->owner_name . 'A/c Dr ' . $rentAmount1 . '/-';
                $description_cr = 'To SSB(' . $ssbAccount . ') A/c Cr ' . $rentAmount1 . '/-';
                $brDaybook = CommanController::branchDaybookCreateModified($refId, $branch_id, $type, $sub_type, $ssbId, $associate_id = NULL, $member_id = NULL, $branch_id_to = NULL, $branch_id_from = NULL, $rentAmount1, $des, $description_dr, $description_cr, 'CR', $payment_mode, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $ssbRentTranID, $ssb_account_id_to,$company_id);
                $brDaybook = CommanController::branchDaybookCreateModified($refId, $branch_id, $type, $sub_type, $type_id, $associate_id = NULL, $member_id = NULL, $branch_id_to = NULL, $branch_id_from = NULL, $rentAmount1, $des, $description_dr, $description_cr, 'DR', $payment_mode, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $rentPaymentId, $ssb_account_id_to,$company_id);
            } else {
                /// bank
                $neft_charge = 0;
                $bank_id_from_c = $request->bank_id;
                $bank_ac_id_from_c = $request->account_id;
                $bankBla = \App\Models\BankBalance::where('bank_id', $bank_id_from_c)->where('account_id', $bank_ac_id_from_c)->whereDate('entry_date', '<=', $entry_date)->where('company_id', $company_id)->orderby('entry_date', 'desc')->sum('totalAmount');
                if ($bankBla) {
                    if ($request->total_transfer_amount > $bankBla) {
                        return redirect('admin/rent/ledger-payable/' . $ledger_id)->with('alert', 'Sufficient amount not available in bank account!');
                    }
                } else {
                    return redirect('admin/rent/ledger-payable/' . $ledger_id)->with('alert', 'Sufficient amount not available in bank account!');
                }
                $rentPaymentId = $request->rentPaymentId;
                $rentPaymentDetail = \App\Models\RentPayment::with('rentLib')->where('id', $rentPaymentId)->first();
                $rentPaymentBalance = $rentPaymentDetail->balance;
                $rentLiabilityBalance = $rentPaymentDetail['rentLib']->bill_current_balance;
                $branch_id = $rentPaymentDetail->branch_id;
                $branchCode = getBranchCode($branch_id)->branch_code;



                $rentAmount = $rentAmount1 = $request->transfer_amount;
                $totalPaybaleAmount = $rentPaymentDetail->transferred_amount + $rentAmount;


                $detail = 'Rent Payment ' . $rentPaymentDetail->month_name . ' ' . $rentPaymentDetail->year;
                $bank_name_to = $rentPaymentDetail->owner_bank_name;
                $bank_ac_to = $rentPaymentDetail->owner_bank_account_number;
                $bank_ifsc_to = $rentPaymentDetail->owner_bank_ifsc_code;
                //-------------------
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
                $bank_id_ac =  $bank_ac_id = $bank_ac_id_from;
                $payment_type = 'CR';
                //------------
                $daybookRef = CommanController::createBranchDayBookReferenceNew($rentAmount1, $globaldate);
                $refId = $daybookRef;
                //------------
                $rentLiabilityId = $rentPaymentDetail['rentLib']->id;
                $lib_current_balance = $rentPaymentDetail['rentLib']->current_balance;
                $des = $rentPaymentDetail['rentLib']->owner_name . "'s " . $rentPaymentDetail->month_name . '' . $rentPaymentDetail->year . ' rent payment has been transferred to the ' . $bank_name_to . ' A/c' . $bank_ac_to;

                $data['transferred_date']   = $entry_date;
                $data['transfer_amount']   = $totalPaybaleAmount;
                $data['transfer_mode']      = $request->amount_mode;
                $data['company_bank_id']    = $bank_id_from = $request->bank_id;
                $data['company_bank_ac_id'] = $bank_ac_id_from = $request->account_id;
                $paymentMode = $request->payment_mode;
                $bankfrmDetail = \App\Models\SamraddhBank::where('id', $bank_id_from)->first();
                $bankacfrmDetail = \App\Models\SamraddhBankAccount::where('id', $bank_ac_id_from)->first();
                if ($request->payment_mode == 1) {
                    $data['company_cheque_id'] = $cheque_id = $request->cheque_id;
                    $data['company_cheque_no'] = $cheque_no = $request->cheque_number;
                    $cheque_date = $entry_date;
                    $rentLibLedger['cheque_id'] = $cheque_id;
                    $rentLibLedger['cheque_no'] = $cheque_no;
                    $rentLibLedger['cheque_date'] = $cheque_date;
                    $cheque_no = $cheque_no;
                    $cheque_date = $cheque_date;
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
                    $cheque_type = 1;
                    $cheque_id = $cheque_id;
                    //---------- Bill Payment --------

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
                    $data['payment_mode']       = 1;
                } else {
                    $data['online_transaction_no'] = $transction_no = $request->utr_tran;
                    $data['neft_charge'] = $neft_charge = $request->neft_charge;
                    $transaction_date = $entry_date;
                    $rentLibLedger['transaction_no'] = $transction_no;
                    $rentLibLedger['transaction_date'] = $transaction_date;
                    $rentLibLedger['transaction_charge'] = $neft_charge;
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
                    //---------- Bill Payment --------

                    // bank charge head entry +


                    $data['payment_mode']       = 0;
                }
                //  $data['status']             = 1;
                $data['updated_at'] = $request->created_at;


                $data['balance'] = $rentPaymentBalance - $rentAmount;

                $data['updated_at'] = $request->created_at;

                $totalRentPaybale = $rentPaymentDetail->actual_transfer_amount;

                if ($totalPaybaleAmount == $totalRentPaybale) {
                    $data['status']             = 1;
                    $data['transferred_amount'] = $totalPaybaleAmount;
                    $total_transfer_amount = $rentAmount;
                } else {
                    $data['status']             = 2;
                    $data['transferred_amount'] = $totalPaybaleAmount;
                    $total_transfer_amount = $rentAmount;
                }

                $part_payment_ref_id = $rentPaymentDetail->part_payment_ref_id;
                $partPaymentRefId = $part_payment_ref_id . ',' . $refId;
                $data['part_payment_ref_id'] = $partPaymentRefId;

                $rentPaymentUpdate = \App\Models\RentPayment::find($rentPaymentId);
                $rentPaymentUpdate->update($data);
                //----------------------------------- 

                $rentLibLedger['rent_liability_id'] = $rentLiabilityId;
                $rentLibLedger['type'] = 8;
                $rentLibLedger['type_id'] = $rentPaymentId;
                $rentLibLedger['withdrawal'] = $rentAmount1;
                $rentLibLedger['description'] = $detail;
                $rentLibLedger['currency_code'] = $currency_code;
                $rentLibLedger['payment_type'] = 'DR';
                $rentLibLedger['payment_mode'] = $paymentMode;
                $rentLibLedger['created_at'] = $created_at;
                $rentLibLedger['updated_at'] = $updated_at;
                $rentLibLedger['daybook_ref_id'] = $refId;
                $rentLibLedger['company_id'] = $company_id;
                $rlL = \App\Models\RentLiabilityLedger::create($rentLibLedger);

                //------------------------libility -(mines)  ------------
                $head11 = 1;
                $head21 = 8;
                $head31 = 21;
                $head41 = 60;
                $head51 = NULL;
                $allTran1 = CommanController::newHeadTransactionCreate($refId, $branch_id, $bank_id, $bank_ac_id, $head41, $type, $sub_type, $type_id, $associate_id = NULL, $member_id = NULL, $branch_id_to = NULL, $branch_id_from = NULL, $rentAmount1, $des, 'DR', $paymentMode, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $rentPaymentId, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $company_id);

                $bankAmountRent = $rentAmount;
                $description_dr = $rentPaymentDetail['rentLib']->owner_name . 'A/c Dr ' . $rentAmount1 . '/-';
                $description_cr = 'To ' . $bankfrmDetail->bank_name . '(' . $bankacfrmDetail->account_no . ') A/c Cr ' . $rentAmount1 . '/-';
                // ---------------- branch daybook entry -----------------
                $brDaybook = CommanController::branchDaybookCreateModified($refId, $branch_id, $type, $sub_type, $type_id, $associate_id = NULL, $member_id = NULL, $branch_id_to = NULL, $branch_id_from = NULL, $rentAmount1, $des, $description_dr, $description_cr, 'DR', $paymentMode, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $rentPaymentId, $ssb_account_id_to, $company_id);
                // ------------------ samraddh bank entry -(mines) ---------------
                $bankAmountRent =  $rentAmount1;
                $description_dr_b = $rentPaymentDetail['rentLib']->owner_name . 'A/c Dr ' . $bankAmountRent . '/-';
                $description_cr_b = 'To ' . $bankfrmDetail->bank_name . '(' . $bankacfrmDetail->account_no . ') A/c Cr ' . $bankAmountRent . '/-';
                $allTran2 = CommanController::newHeadTransactionCreate($refId, $branch_id, $bank_id, $bank_ac_id, $bankfrmDetail->account_head_id, $type, $sub_type, $type_id, $associate_id = NULL, $member_id = NULL, $branch_id_to = NULL, $branch_id_from = NULL, $bankAmountRent, $des, 'CR', $paymentMode, $currency_code,  $v_no, $ssb_account_id_from, $cheque_no, $transction_no,  $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $rentPaymentId, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $company_id);
                $bncktrn = $bankAmountRent;
                $smbdc = CommanController::NewFieldAddSamraddhBankDaybookCreateModify($refId, $bank_id_from, $bank_ac_id_from, $type, $sub_type, $type_id, $associate_id = NULL, $member_id = NULL, $branch_id, $opening_balance = NULL, $bncktrn, $closing_balance = NULL, $des, $description_dr_b, $description_cr_b, 'DR', $paymentMode, $currency_code, $amount_to_id = NULL, $amount_to_name = NULL, $amount_from_id = NULL, $amount_from_name = NULL, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $rentPaymentId, $ssb_account_id_to, $cheque_bank_from_id, $cheque_bank_ac_from_id, $cheque_bank_to_name, $cheque_bank_to_branch, $cheque_bank_to_ac_no, $cheque_bank_to_ifsc, $transction_bank_from_id, $transction_bank_from_ac_id, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $jv_unique_id, $cheque_type, $cheque_id, $company_id);
            }

            if ($request->amount_mode == 2) {
                if ($request->neft_charge > 0) {
                    $allTranSSB = CommanController::newHeadTransactionCreate($refId, 29, $bank_id, $bank_ac_id, 92, $type, $sub_type, $type_id, $associate_id = NULL, $member_id = NULL, $branch_id_to = NULL, $branch_id_from = NULL, $request->neft_charge, $des, 'DR', $paymentMode, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $rentPaymentId, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $company_id);

                    $allTran2 = CommanController::newHeadTransactionCreate($refId, 29, $bank_id, $bank_ac_id, $bankfrmDetail->account_head_id, $type, $sub_type, $type_id, $associate_id = NULL, $member_id = NULL, $branch_id_to = NULL, $branch_id_from = NULL, $request->neft_charge, $des, 'CR', $paymentMode, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $rentPaymentId, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $company_id);


                    CommanController::NewFieldAddSamraddhBankDaybookCreateModify($refId, $bank_id_from, $bank_ac_id_from, $type, $sub_type, $type_id, $associate_id = NULL, $member_id = NULL, 29, $opening_balance = NULL, $request->neft_charge, $closing_balance = NULL, $des, $description_dr_b, $description_cr_b, 'DR', $paymentMode, $currency_code, $amount_to_id = NULL, $amount_to_name = NULL, $amount_from_id = NULL, $amount_from_name = NULL, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $rentPaymentId, $ssb_account_id_to, $cheque_bank_from_id, $cheque_bank_ac_from_id, $cheque_bank_to_name, $cheque_bank_to_branch, $cheque_bank_to_ac_no, $cheque_bank_to_ifsc, $transction_bank_from_id, $transction_bank_from_ac_id, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $jv_unique_id, $cheque_type, $cheque_id, $company_id);
                }
            }

            $payment_count = \App\Models\RentPayment::where('ledger_id', $ledger_id)->where('status', 1)->get();
            $total_payment_count = \App\Models\RentPayment::where('ledger_id', $ledger_id)->get();

            $l = \App\Models\RentLedger::where('id', $ledger_id)->first();
            if ($l->transfer_amount > 0) {
                $ledgertransfer_amount = $total_transfer_amount + $l->transfer_amount;
            } else {
                $ledgertransfer_amount = $total_transfer_amount;
            }
            $updateLedger['transfer_amount'] = $ledgertransfer_amount;
            if (count($payment_count) == count($total_payment_count)) {
                $updateLedger['status'] = 1;
            } else {
                $updateLedger['status'] = 2;
            }
            $leaserdataUpdate = \App\Models\RentLedger::find($ledger_id);
            $leaserdataUpdate->update($updateLedger);


            $sumTransferredAmount = \App\Models\RentPayment::where('rent_liability_id', $rentPaymentDetail['rentLib']->id)->sum('transferred_amount');
            $sumActualTransferAmount = \App\Models\RentPayment::where('rent_liability_id', $rentPaymentDetail['rentLib']->id)->sum('actual_transfer_amount');
            $libilityBalance = $sumActualTransferAmount - $sumTransferredAmount;

            $lib['current_balance'] = $libilityBalance;
            $libUpdate = RentLiability::find($rentPaymentDetail['rentLib']->id);
            $libUpdate->update($lib);
            
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('alert', $ex->getMessage());
        }
        return redirect('admin/rent/ledger-payable/' . $ledger_id)->with('success', 'Rent Payment Transferred Successfully');
    }
    public function rent_edit($id)
    {
        if (check_my_permission(Auth::user()->id, "361") != "1") {
            return redirect()->route('admin.dashboard');
        }
        $data['rent_ID'] = $id;
        $id = base64_decode($id);
        $data['title'] = 'Rent Management | Rent Edit';
        $rent_record = $data['rent_record'] = \App\Models\RentPayment::whereId($id)->with('rentCompany:id,name')->first();
        $data_rent = RentLiability::select('id', 'branch_id', 'rent_agreement_file_id', 'employee_id', 'rent_type', 'owner_ssb_id', 'place', 'owner_name', 'owner_mobile_number', 'owner_pen_number', 'owner_aadhar_number', 'owner_bank_name', 'owner_bank_account_number', 'owner_bank_ifsc_code', 'security_amount', 'rent', 'yearly_increment', 'office_area', 'advance_payment', 'current_balance', 'created_at', 'status', 'agreement_from', 'company_id')->with(['AcountHeadCustom' => function ($q) {
            $q->select('id', 'head_id', 'parent_id', 'sub_head');
        }, 'SsbAccountNumberCustom' => function ($q) {
            $q->select('id', 'account_no', 'member_id');
        }, 'rentFileCustom' => function ($q) {
            $q->select('id', 'file_name');
        }, 'liabilityBranch' => function ($query) {
            $query->select('id', 'name', 'branch_code', 'sector', 'regan', 'zone');
        }])->with(['employee_rent' => function ($query) {
            $query->select('id', 'designation_id', 'employee_code', 'employee_name', 'mobile_no')->with(['designation' => function ($q) {
                $q->select('id', 'designation_name');
            }]);
        }])->where('status', 0)->whereId($rent_record->rent_liability_id);
        $data_rent = $data_rent->orderby('created_at', 'DESC')->get();
        $data['rent'] = $data_rent;
        $month = ($rent_record->month < 10) ? "0$rent_record->month" : $rent_record->month;
        $data['lastDate'] = "01/$month/$rent_record->year";
        // $data['lastDate'] = "01/01/2024";
        return view('templates.admin.rent-management.ledger_edit', $data);
    }
    public function rent_edit_save(Request $request)
    {
        $totalTransferAmount = collect($request->get('amount'))->sum();
        $totalTdsAmount = collect($request->get('tds_amount'))->sum();
        $totalPayable = collect($request->get('transfer_amount'))->sum();
        DB::beginTransaction();
        try {
            $total_amount = 0;
            $transfer_amount = 0;
            $settel_amount = 0;
            $globaldate = $request->created_at;
            // dd($request->all());
            $id = base64_decode($request->rent_ID);
            Session::put('created_at', $request->created_at);
            $RentPayment = RentPayment::where('id', $id)->first();
            $company_id = $RentPayment->company_id;
            $monthName = $RentPayment->month_name;
            $data_rent = RentLiability::select('id', 'branch_id', 'rent_agreement_file_id', 'employee_id', 'rent_type', 'owner_ssb_id', 'place', 'owner_name', 'owner_mobile_number', 'owner_pen_number', 'owner_aadhar_number', 'owner_bank_name', 'owner_bank_account_number', 'owner_bank_ifsc_code', 'security_amount', 'rent', 'yearly_increment', 'office_area', 'advance_payment', 'current_balance', 'created_at', 'status', 'agreement_from', 'company_id')->with(['AcountHeadCustom' => function ($q) {
                $q->select('id', 'head_id', 'parent_id', 'sub_head');
            }, 'SsbAccountNumberCustom' => function ($q) {
                $q->select('id', 'account_no', 'member_id');
            }, 'rentFileCustom' => function ($q) {
                $q->select('id', 'file_name');
            }, 'liabilityBranch' => function ($query) {
                $query->select('id', 'name', 'branch_code', 'sector', 'regan', 'zone');
            }])->with(['employee_rent' => function ($query) {
                $query->select('id', 'designation_id', 'employee_code', 'employee_name', 'mobile_no')->with(['designation' => function ($q) {
                    $q->select('id', 'designation_name');
                }]);
            }])->whereId($RentPayment->rent_liability_id);
            $data_rent = $data_rent->orderby('created_at', 'DESC')->get();

            $old_value = [
                'part_payment_ref_id' => $RentPayment->part_payment_ref_id, 'payment_ref_id' => $RentPayment->payment_ref_id, 'transferred_amount' => $RentPayment->transferred_amount, 'tds_amount' => $RentPayment->tds_amount,  'settle_amount' => $RentPayment->settle_amount, 'advance_payment' => $RentPayment->advance_payment, 'current_advance_payment' => $RentPayment->current_advance_payment, 'transferred_date' => $RentPayment->transferred_date, 'actual_transfer_amount' => $RentPayment->actual_transfer_amount, 'neft_charge' => $RentPayment->neft_charge
            ];
            $select_date = $request->select_date;
            $current_date = date("Y-m-d", strtotime(convertDate($globaldate)));
            $entry_date = date("Y-m-d", strtotime(convertDate($select_date)));
            $entry_time = date("H:i:s", strtotime(convertDate($globaldate)));
            $date_create = $entry_date . ' ' . $entry_time;
            $created_at = date("Y-m-d H:i:s", strtotime(convertDate($date_create)));

            $updated_at = date("Y-m-d H:i:s", strtotime(convertDate($date_create)));
            $rent_main_ledger = \App\Models\RentLedger::whereId($RentPayment->ledger_id)->first();
            $leaserdata['total_amount'] = $rent_main_ledger->total_amount - $RentPayment->transfer_amount - $RentPayment->tds_amount;
            $leaserdata['tds_amount'] = $rent_main_ledger->tds_amount - $RentPayment->tds_amount;
            $leaserdata['payable_amount'] = $rent_main_ledger->payable_amount - $RentPayment->transfer_amount;
            $create = $rent_main_ledger->update($leaserdata);
            $leaserdata['total_amount'] = $rent_main_ledger->total_amount + $totalTransferAmount;
            $leaserdata['tds_amount'] = $rent_main_ledger->tds_amount + $totalTdsAmount;
            $leaserdata['payable_amount'] = $rent_main_ledger->payable_amount + $totalPayable;
            $create = $rent_main_ledger->update($leaserdata);
            $leaser = $RentPayment->ledger_id;
            $payment_mode = 3;
            $payment_type = 'CR';
            $currency_code = 'INR';
            $created_by = 1;
            $created_by_id = \Auth::user()->id;
            $randNumber = mt_rand(0, 999999999999999);
            $v_no = $randNumber;
            $v_date = $entry_date;
            // head  entry----------------------------
            $des = 'Rent Ledger of ' . $monthName . ' ' . $RentPayment->year;
            $tdsdes = 'Tds Amount of Rent Ledger of ' . $monthName . ' ' . $RentPayment->year;
            $type = 10;
            $sub_type = 101;
            $type_id = $leaser;
            $all_head_leaser_del = AllHeadTransaction_delete(explode("sa", $RentPayment->ledger_create_daybook_ref_id), $RentPayment->leaser_id, $RentPayment->id);
            $daybookRef = $RentPayment->ledger_create_daybook_ref_id;
            $refId = $daybookRef;
            \App\Models\RentLiabilityLedger::where('type_id', $id)->update(['is_deleted' => 1]);
            if (isset($_POST['rent_lib_id'])) {
                foreach (($_POST['rent_lib_id']) as $key => $option) {
                    $rentLiabilityId = $_POST['rent_lib_id'][$key];
                    $libDetail = RentLiability::where('id', $rentLiabilityId)->first();
                    $data['ledger_id']              = $leaser;
                    $data['rent_liability_id']      = $rentLiabilityId;
                    $data['branch_id']              = $libDetail->branch_id;
                    $data['month']                  = $RentPayment->month;
                    $data['month_name']             = $monthName;
                    $data['year']                   = $RentPayment->year;
                    $data['security_amount']        = $libDetail->security_amount;
                    $data['rent_amount']            = $libDetail->rent;
                    $data['actual_transfer_amount'] = $updating_data['actual_transfer_amount'] =  $_POST['transfer_amount'][$key];
                    $data['transfer_amount']        = $updating_data['transfer_amount'] =  $_POST['transfer_amount'][$key];
                    $data['yearly_increment']       = $libDetail->yearly_increment;
                    $data['office_area']            = $libDetail->office_area;
                    $data['employee_id']            = $libDetail->employee_id;
                    $data['balance']                = $updating_data['balance'] =  $_POST['transfer_amount'][$key];
                    $data['tds_amount']             = $updating_data['tds_amount'] =  $_POST['tds_amount'][$key];
                    $data['actual_tds']             = 0;
                    if ($libDetail->owner_ssb_id) {
                        $data['owner_ssb_id']           = $libDetail->owner_ssb_id;
                        $data['owner_ssb_account']      = getSsbAccountNumber($libDetail->owner_ssb_id)->account_no;
                    } else {
                        $data['owner_ssb_id']           = NULL;
                        $data['owner_ssb_account']      = NULL;
                    }
                    $data['owner_bank_name']        = $libDetail->owner_bank_name;
                    $data['owner_bank_account_number'] = $libDetail->owner_bank_account_number;
                    $data['owner_bank_ifsc_code']    = $libDetail->owner_bank_ifsc_code;
                    $data['status']                  = 0;
                    $data['created_at'] = $created_at;
                    $data['updated_at'] = $created_at;
                    $data['company_id'] = $company_id;
                    $data['ledger_create_daybook_ref_id'] = $refId;
                    // $data['payment_ref_id'] = $refId;
                    // $create = \App\Models\RentPayment::create($data);
                    $RentPayment->update($updating_data);
                    $TranId = $tranId = $id;
                    $branch_id = $libDetail->branch_id;
                    // Rent Liablility Leaser
                    $val['rent_liability_id'] = $rentLiabilityId;
                    $val['type'] = 4;
                    $val['type_id'] = $TranId;
                    $val['deposit'] = $_POST['amount'][$key];
                    $val['description'] =  $des;
                    $val['currency_code'] = 'INR';
                    $val['payment_type'] = 'CR';
                    $val['payment_mode'] = 3;
                    $val['status'] = 1;
                    $val['v_no'] = $v_no;
                    $val['v_date'] = $v_date;
                    $val['created_at'] = $created_at;
                    $val['updated_at'] = $updated_at;
                    $val['daybook_ref_id'] = $refId;
                    $val['company_id'] = $company_id;
                    $createRentLeaser = \App\Models\RentLiabilityLedger::create($val);
                    $val2['rent_liability_id'] = $rentLiabilityId;
                    $val2['type'] = 5;
                    $val2['type_id'] = $TranId;
                    $val2['withdrawal'] = $_POST['tds_amount'][$key];
                    $val2['description'] = $tdsdes;
                    $val2['currency_code'] = 'INR';
                    $val2['payment_type'] = 'DR';
                    $val2['payment_mode'] = 3;
                    $val2['status'] = 1;
                    $val2['v_no'] = $v_no;
                    $val2['v_date'] = $v_date;
                    $val2['created_at'] = $created_at;
                    $val2['updated_at'] = $updated_at;
                    $val2['daybook_ref_id'] = $refId;
                    $val2['company_id'] = $company_id;
                    if ($_POST['tds_amount'][$key] > 0) {
                        $createRentLeaser = \App\Models\RentLiabilityLedger::create($val2);
                    }
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
                    $total_amount = $total_amount + $_POST['transfer_amount'][$key];
                    //expence
                    $head12 = 4;
                    $head22 = 86;
                    $head32 = 53;
                    $head42 = $libDetail->rent_type;
                    $head52 = NULL;
                    $allTran2 = CommanController::newHeadTransactionCreate($refId, $branch_id, $bank_id, $bank_ac_id, $head42, $type, $sub_type, $type_id, $associate_id = NULL, $member_id, $branch_id_to = NULL, $branch_id_from = NULL, $_POST['amount'][$key], $des, 'DR', $payment_mode, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $tranId, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $company_id);
                    ///libility
                    $head11 = 1;
                    $head21 = 8;
                    $head31 = 21;
                    $head41 = 60;
                    $head51 = NULL;
                    $allTran1 = CommanController::newHeadTransactionCreate($refId, $branch_id, $bank_id, $bank_ac_id, $head41, $type, $sub_type, $type_id, $associate_id = NULL, $member_id, $branch_id_to = NULL, $branch_id_from = NULL, $_POST['amount'][$key], $des, 'CR', $payment_mode, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $tranId, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $company_id);
                    if ($_POST['tds_amount'][$key] > 0) {
                        $allTran1 = CommanController::newHeadTransactionCreate($refId, $branch_id, $bank_id, $bank_ac_id, $head41, $type, $sub_type, $type_id, $associate_id = NULL, $member_id, $branch_id_to = NULL, $branch_id_from = NULL, $_POST['tds_amount'][$key], $tdsdes, 'DR', $payment_mode, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $tranId, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $company_id);
                        /**Duties and taxies > Tds on Rent Head */
                        $allTran1 = CommanController::newHeadTransactionCreate($refId, $branch_id, $bank_id, $bank_ac_id, 265, $type, $sub_type, $type_id, $associate_id = NULL, $member_id, $branch_id_to = NULL, $branch_id_from = NULL, $_POST['tds_amount'][$key], $tdsdes, 'CR', $payment_mode, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $tranId, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $company_id);
                        $sumTransferredAmount = \App\Models\RentPayment::where('rent_liability_id', $rentLiabilityId)->sum('transferred_amount');
                        $sumActualTransferAmount = \App\Models\RentPayment::where('rent_liability_id', $rentLiabilityId)->sum('actual_transfer_amount');
                        $libilityBalance = $sumActualTransferAmount - $sumTransferredAmount;
                        $currentLib = $_POST['amount'][$key] - $_POST['tds_amount'][$key];
                        $lib['current_balance'] = $libilityBalance + $currentLib;
                        $libUpdate = RentLiability::find($libDetail->id);
                        $libUpdate->update($lib);
                    }
                }
            }
            $new_value = [
                'part_payment_ref_id' => $RentPayment->part_payment_ref_id, 'payment_ref_id' => $RentPayment->payment_ref_id, 'transferred_amount' => $RentPayment->transferred_amount, 'tds_amount' => $RentPayment->tds_amount,  'settle_amount' => $RentPayment->settle_amount, 'advance_payment' => $RentPayment->advance_payment, 'current_advance_payment' => $RentPayment->current_advance_payment, 'transferred_date' => $RentPayment->transferred_date, 'actual_transfer_amount' => $RentPayment->actual_transfer_amount, 'neft_charge' => $RentPayment->neft_charge
            ];
            $log = [];
            $log['type'] = 2;
            $log['emp_owner_id'] = $RentPayment->rent_liability_id;
            $log['ledger_id'] = $RentPayment->ledger_id;
            $log['ledger_detail_id'] = $RentPayment->id;
            $log['daybook_ref_id'] = $RentPayment->ledger_create_daybook_ref_id;
            $log['description'] = "Rent edit";
            $log['old_value'] = json_encode($old_value);
            $log['new_value'] = json_encode($new_value);
            $log['created_by'] = 1;
            $log['created_by_id'] = Auth::user()->id;
            $log['created_at'] = $request->created_at;
            $log['title'] = "Rent Edit";
            $log['status'] = 1;
            SalaryRentLog::insert($log);
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            dd($ex->getMessage(), $ex->getLine());
            return back()->with('alert', $ex->getMessage());
        }
        return redirect('admin/rent/ledger-payable/' . $leaser)->with('success', 'Rent Ledger Updated Successfully');
    }
    public function payment_delete(Request $request)
    {
        DB::beginTransaction();
        try {
            $rent_detail = [];
            $id = base64_decode($request->id);
            if ($request->multiple == 'no') {
                $RentPayments = RentPayment::whereId($id)->get();
            } else if ($request->multiple == 'yes') {
                $RentPayments = RentPayment::where('payment_ref_id', $id)->get();
            } else {
                return back()->with('alert', "something went wrong");
            }
            $main_payment = 0;
            $day_book_ids = [];
            foreach ($RentPayments as $RentPayment) {
                if ($RentPayment->status == 0) {
                    dd("something went suspecious");
                    return back()->with('alert', "something went suspecious");
                }
                $old_value = [
                    'part_payment_ref_id' => $RentPayment->part_payment_ref_id, 'payment_ref_id' => $RentPayment->payment_ref_id, 'transferred_amount' => $RentPayment->transferred_amount, 'tds_amount' => $RentPayment->tds_amount,  'settle_amount' => $RentPayment->settle_amount, 'advance_payment' => $RentPayment->advance_payment, 'current_advance_payment' => $RentPayment->current_advance_payment, 'transferred_date' => $RentPayment->transferred_date, 'actual_transfer_amount' => $RentPayment->actual_transfer_amount, 'neft_charge' => $RentPayment->neft_charge
                ];

                if (isset($RentPayment->part_payment_ref_id)) {
                    $first = substr($RentPayment->part_payment_ref_id, 0, 1);
                    if ($first == ',') {
                        $part_ids = substr($RentPayment->part_payment_ref_id, 1);
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
                if (isset($RentPayment->payment_ref_id) && $main_payment == 0) {
                    array_push($day_book_ids, $RentPayment->payment_ref_id);
                    $main_payment++;
                }
                $chk_amt = 0;
                $Emp_data = RentLiability::find($RentPayment->rent_liability_id);
                if (isset($RentPayment->settle_amount)) {
                    $rent_detail['current_advance_payment'] = $RentPayment->current_advance_payment + $RentPayment->settle_amount;
                    $rent_detail['settle_amount'] = null;
                    $advance_trxn_update = AdvancedTransaction::where('type', 3)->where('sub_type', 31)->where('type_id', $RentPayment->rent_liability_id)->where('status', 1)->where('status_date', '<=', $RentPayment->created_at)->where('is_deleted', 0)->get(['amount', 'settle_amount', 'id']);
                    foreach ($advance_trxn_update as $advance_trxn) {
                        if ($advance_trxn->amount - $advance_trxn->settle_amount >= $RentPayment->settle_amount) {
                            $advance_update_amount['settle_amount'] = $advance_trxn->settle_amount + $RentPayment->settle_amount;
                            $chk_amt = $chk_amt + $RentPayment->settle_amount;
                        } else {
                            $advance_update_amount['settle_amount'] = $advance_trxn->settle_amount + $advance_trxn->amount - $advance_trxn->settle_amount;
                            $chk_amt = $chk_amt + $advance_trxn->amount - $advance_trxn->settle_amount;
                            dd("else");
                        }
                        if ($chk_amt == $RentPayment->settle_amount) {
                            AdvancedTransaction::whereId($advance_trxn->id)->update($advance_update_amount);
                            break;
                        } else if ($chk_amt > $RentPayment->settle_amount) {
                            dd("Advance amount settlement have some error");
                            return back()->with('alert', "Advance amount settlement have some error");
                        } else {
                            AdvancedTransaction::whereId($advance_trxn->id)->update($advance_update_amount);
                        }
                    }
                    $empdata['advance_payment'] = $Emp_data['advance_payment'] + $chk_amt;
                } else {
                    $empdata['advance_payment'] = $Emp_data['advance_payment'];
                }
                $rent_detail['transfer_mode'] = null;
                $rent_detail['status'] = 0;
                $rent_detail['balance'] = 0;
                $rent_detail['transferred_date'] = null;
                $rent_detail['transferred_amount'] = null;
                $rent_detail['company_bank_id'] = null;
                $rent_detail['company_bank_ac_id'] = null;
                $rent_detail['payment_mode'] = null;
                $rent_detail['company_cheque_id'] = null;
                $rent_detail['company_cheque_no'] = null;
                $rent_detail['online_transaction_no'] = null;
                $rent_detail['neft_charge'] = null;
                $rent_detail['online_transaction_no'] = null;
                $rent_detail['part_payment_ref_id'] = null;
                $rent_detail['current_advance_payment'] = null;
                $rent_detail['payment_ref_id'] = null;
                $RentPaymentLeaser = \App\Models\RentLedger::find($RentPayment->ledger_id);
                $leaser = [];
                $leaser['transfer_amount'] = $RentPaymentLeaser->transfer_amount - $RentPayment->transfer_amount;
                $RentPaymentLeaser->update($leaser);
                if ($RentPayment->status == 0) {
                    dd("something went wrong");
                    return back()->with('alert', "something went wrong");
                }
                $RentPayment->update($rent_detail);
                $new_value = [
                    'part_payment_ref_id' => $RentPayment->part_payment_ref_id, 'payment_ref_id' => $RentPayment->payment_ref_id, 'transferred_amount' => $RentPayment->transferred_amount, 'tds_amount' => $RentPayment->tds_amount,  'settle_amount' => $RentPayment->settle_amount, 'advance_payment' => $RentPayment->advance_payment, 'current_advance_payment' => $RentPayment->current_advance_payment, 'transferred_date' => $RentPayment->transferred_date, 'actual_transfer_amount' => $RentPayment->actual_transfer_amount, 'neft_charge' => $RentPayment->neft_charge
                ];
                $log = [];
                $log['type'] = 2;
                $log['emp_owner_id'] = $RentPayment->rent_liability_id;
                $log['ledger_id'] = $RentPayment->ledger_id;
                $log['ledger_detail_id'] = $RentPayment->id;
                $log['daybook_ref_id'] = $RentPayment->ledger_create_daybook_ref_id;
                $log['description'] = $request->reason;
                $log['old_value'] = json_encode($old_value);
                $log['new_value'] = json_encode($new_value);
                $log['created_by'] = 1;
                $log['created_by_id'] = Auth::user()->id;
                $log['created_at'] = $request->created_at;
                $log['title'] = "Rent Delete";
                $log['status'] = 1;
                $Emp_data->update($empdata);
                SalaryRentLog::insert($log);
            }
            $RentLiabilityLedger = RentLiabilityLedger::whereIn('daybook_ref_id', $day_book_ids)->update(['is_deleted' => 1]);
            if (!empty($day_book_ids) || count($day_book_ids) > 0) {
                AllHeadTransaction_delete($day_book_ids);
                BranchDaybook_delete($day_book_ids);
                SamraddhBankDaybook_delete($day_book_ids);
                SavingAccountTranscation_delete($day_book_ids);
            }
            DB::commit();
            return response()->json(['message' => 'Rent payment deleted successfully']);
        } catch (\Exception $ex) {
            DB::rollback();
            // dd($ex->getMessage(), $ex->getLine());
            // ed($ex);
            return back()->with('alert', $ex->getMessage());
        }
    }
}
