<?php
namespace App\Http\Controllers\Admin;

use App\Models\MemberIdProof;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use App\Models\{Member, Branch, Receipt, ReceiptAmount, Memberloans, Grouploans, Memberinvestments, Memberinvestmentspayments, CorrectionRequests, MemberTransaction, MemberCompany, Plans, EliMoneybackInvestments};
use Illuminate\Support\Facades\Cache;
use Yajra\DataTables\DataTables;
use Session;
use Redirect;
use URL;
use DB;
use App\Services\Sms;
use App\Services\ImageUpload;

/*
    |---------------------------------------------------------------------------
    | Admin Panel -- Member Management MemberController
    |--------------------------------------------------------------------------
    |
    | This controller handles members all functionlity.
*/
class MemberController extends Controller
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
     * Show    members list.
     * Route: /admin/member 
     * Method: get 
     * @return  array()  Response
     */
    public function index()
    {
        if (check_my_permission(Auth::user()->id, "3") != "1") {
            return redirect()->route('admin.dashboard');
        }
        $data['title'] = 'Member | Listing';
        // $data['branch'] = Branch::where('status',1)->get(['id','name']);
        return view('templates.admin.member.index', $data);
    }
    /**
     * Get members list
     * Route: ajax call from - /admin/member
     * Method: Post 
     * @param  \Illuminate\Http\Request  $request
     * @return JSON array
     */
    public function membersListing(Request $request)
    {
        if ($request->ajax() && check_my_permission(Auth::user()->id, "3") == "1") {
            // fillter array 
            $arrFormData = array();
            if (!empty($_POST['searchform'])) {
                foreach ($_POST['searchform'] as $frm_data) {
                    $arrFormData[$frm_data['name']] = $frm_data['value'];
                }
            }
            $companyId = $arrFormData['company_id'];
            // print_r($companyId);die;
            if (isset($arrFormData['is_search']) && $arrFormData['is_search'] == 'yes') {
                $data = MemberCompany::has('company')->select('id', 're_date', 'member_id', 'company_id', 'associate_code', 'associate_id', 'is_block', 'customer_id', 'branch_id')
                    ->with([
                        'member:id,first_name,last_name,dob,email,mobile_no,status,signature,photo,village,pin_code,state_id,district_id,city_id,associate_id,branch_id,address,gender,company_id,member_id',
                        'member.branch:id,name,branch_code,sector,zone',
                        'member.states:id,name',
                        'member.city:id,name',
                        'member.district:id,name',
                        'member.memberIdProof:id,first_id_no,second_id_no,member_id,first_id_type_id,second_id_type_id',
                        'member.memberIdProof.idTypeFirst:id,name',
                        'member.memberIdProof.idTypeSecond:id,name',
                        'member.children:id,first_name,last_name',
                        'member.memberNomineeDetails:id,name,relation,age,member_id,gender',
                        'member.memberNomineeDetails.nomineeRelationDetails:id,name',
                        'savingAccountNew:id,account_no,member_id,company_id'
                    ])->where('member_id', '!=', '9999999');
                if (Auth::user()->branch_id > 0) {
                    $id = Auth::user()->branch_id;
                    $data = $data->where('branch_id', '=', $id);
                }
                /******* fillter query start ****/
                if ($arrFormData['company_id'] != '') {
                    $company_id = $arrFormData['company_id'];
                    if ($company_id != '0') {
                        $data = $data->where('company_id', '=', $company_id);
                    }
                }
                if ($arrFormData['customer_id'] != '') {
                    $customer_id = $arrFormData['customer_id'];
                    $data = $data->whereHas('member', function ($query) use ($customer_id) {
                        $query->where('members.member_id', $customer_id);
                    });
                }
                if ($arrFormData['associate_code'] != '') {
                    $associate_code = $arrFormData['associate_code'];
                    $data = $data->whereHas('memberAssociate', function ($query) use ($associate_code) {
                        $query->where('members.associate_no', $associate_code);
                    });
                }
                if ($arrFormData['member_id'] != '') {
                    $meid = $arrFormData['member_id'];
                    $data = $data->where('member_id', '=', $meid);
                }
                if (isset($arrFormData['branch_id']) && $arrFormData['branch_id'] != '') {
                    $id = $arrFormData['branch_id'];
                    if ($id != '0') {
                        $data = $data->where('branch_id', '=', $id);
                    }
                }
                if ($arrFormData['status'] != '') {
                    $statusId = $arrFormData['status'];
                    $data = $data->where('is_block', '=', $statusId);
                }
                if ($arrFormData['name'] != '') {
                    $name = $arrFormData['name'];
                    $data = $data->whereHas(
                        'member',
                        function ($qm) use ($name) {
                            $qm->where('first_name', 'LIKE', '%' . $name . '%')
                                ->orWhere('last_name', 'LIKE', '%' . $name . '%')
                                ->orWhere(DB::raw('concat(first_name," ",last_name)'), 'LIKE', "%$name%");
                        }
                    );
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
                $data = $data->orderby('id', 'DESC')->offset($_POST['start'])->limit($_POST['length'])->get(['id', 're_date', 'member_id', 'company_id', 'associate_id', 'is_block', 'customer_id', 'branch_id', 'created_at', 'status']);

                $totalCount = $count;
                $sno = $_POST['start'];
                $rowReturn = array();
                foreach ($data as $row) {
                    $sno++;
                    $memberid = $row->customer_id ?? '';
                    $url = URL::to("admin/member-detail/" . $memberid . "?type=0");
                    $url1 = URL::to("admin/member-account/" . $memberid . "");
                    $url2 = URL::to("admin/member-loan/" . $memberid . "");
                    $url3 = URL::to("admin/member-investment/" . $memberid . "");
                    $url4 = URL::to("admin/member-edit/" . $memberid . "?type=0");
                    $url5 = URL::to("admin/member-transactions/" . $memberid . "");
                    $url6 = URL::to("admin/form_g/" . $row->id . "");
                    $url8 = URL::to("admin/member-edit/" . $memberid . "/?type=0");
                    $NomineeDetail = $row['member']['memberNomineeDetails'] ?? 'N/A';
                    $val['DT_RowIndex'] = $sno;
                    $val['company'] = $row['company'] ? $row['company']['name'] : 'N/A';
                    $val['join_date'] = date("d/m/Y", strtotime($row->re_date)) ?? 'N/A';
                    $val['branch'] = $row['branch']->name ?? 'N/A';
                    $btnS = '';                    
                    $btnS .= '<a class=" " href="' . $url8 . '" title="Edit Member">' . $row->member_id . '</a>';
                    $val['member_id'] = $btnS;
                    $val['customer_id'] = $row['member']->member_id ?? '';
                    $firstname = $row['member']->first_name ?? '';
                    $last_name = $row['member']->last_name ?? '';
                    $val['name'] = $firstname . ' ' . $last_name ?? '';
                    $dob = $row['member']->dob ?? 'N/A';
                    $val['dob'] = date('d/m/Y', strtotime($dob)) ?? 'N/A';
                    $gender = $row['member']->gender ?? 'N/A';
                    if ($gender == 1) {
                        $val['gender'] = 'Male';
                    } else {
                        $val['gender'] = 'Female';
                    }
                    $val['nominee_name'] = $NomineeDetail->name ?? 'N/A';
                    if ($row->id) {
                        $relation_id = $NomineeDetail->relation ?? 'N/A';
                        if ($relation_id) {
                            $relationname = $NomineeDetail['nomineeRelationDetails'] ?? '';
                            if ($relationname) {
                                $val['relation'] = $relationname->name;
                            }
                        }
                    }
                    $val['nominee_age'] = $NomineeDetail->age ?? 'N/A';
                    $NomineeDetailgender = $NomineeDetail->gender ?? 'N/A';
                    if ($NomineeDetailgender == 1) {
                        $val['nominee_gender'] = 'Male';
                    } else {
                        $val['nominee_gender'] = 'Female';
                    }
                    $accountNo = '';
                    if ($row->savingAccountNew) {
                        $accountNo = $row->savingAccountNew->account_no ?? 'N/A'; 
                    }
                    $val['ssb_account'] = $accountNo ?? 'N/A';
                    $val['email'] = $row['member']->email ?? 'N/A';
                    $val['mobile_no'] = $row['member']->mobile_no ?? 'N/A';
                    $val['associate_code'] = $row['memberAssociate']->associate_no ?? 'N/A';
                    $memberchildfname = $row['memberAssociate']->first_name ?? '';
                    $memberchildlname = $row['memberAssociate']->last_name ?? '';
                    $val['associate_name'] = "$memberchildfname $memberchildlname";
                    $is_block = $row['member']->is_block ?? '';
                    if ($is_block == 1) {
                        $status = 'Blocked';
                    } else {
                        $status = $row['member']->status ?? '';
                        if ($status == 1) {
                            $status = 'Active';
                        } else {
                            $status = 'Inactive';
                        }
                    }
                    $val['status'] = $status;
                    $is_upload = 'Yes';
                    $signature = $row['member']->signature ?? '';
                    if ($signature == '') {
                        $is_upload = 'No';
                    }
                    $photo = $row['member']->photo ?? 'N/A';
                    if ($photo == '') {
                        $is_upload = 'No';
                    }
                    $val['is_upload'] = $is_upload;
                    $idfirsttypename = $row['member']['memberIdProof']['idTypeFirst']->name ?? 'N/A';
                    $idsecondtypename = $row['member']['memberIdProof']->first_id_no ?? 'N/A';
                    $val['firstId'] = "$idfirsttypename - $idsecondtypename";
                    $idTypeSecond = $row['member']['memberIdProof']['idTypeSecond']->name ?? 'N/A';
                    $second_id_no = $row['member']['memberIdProof']->second_id_no ?? 'N/A';
                    $val['secondId'] = "$idTypeSecond - $second_id_no";
                    $val['address'] = $row['member']->address ? preg_replace("/\r|\n/", "", $row['member']->address) : 'N/A';
                    $val['state'] = $row['member']['states']->name ?? 'N/A';
                    $val['district'] = $row['member']['district']->name ?? 'N/A';
                    $val['city'] = $row['member']['city']->name ?? 'N/A';
                    $val['village'] = $row['member']->village ?? 'N/A';
                    $val['pin_code'] = $row['member']->pin_code ?? 'N/A';
                    $btn = '<div class="list-icons"><div class="dropdown"><a href="#" class="list-icons-item" data-toggle="dropdown"><i class="icon-menu9"></i></a><div class="dropdown-menu dropdown-menu-right">';
                    $memberid = $row['member']->id ?? '';
                    
                    $btn .= '<a class="dropdown-item" href="' . $url . '" title="Member Detail"><i class="icon-eye-blocked2  mr-2"></i>Detail</a>  ';
                    if ($row['member']['memberIdProof']->first_id_type_id == '5' || $row['member']['memberIdProof']->second_id_type_id == '5') {
                        if (check_my_permission(Auth::user()->id, "238") == "1" && ($row->is_block != 1)) {
                            $btn .= '<a class="dropdown-item" target="_blank" href="' . $url6 . '" title="Update 15G/15H"><i class="icon-book  mr-2"></i>Update 15G/15H</a>  ';
                        }
                    }
                    if ($is_block == 0) {
                        if (check_my_permission(Auth::user()->id, "237") == "1" && ($row->is_block != 1)) {
                            $btn .= '<a class="dropdown-item" href="' . $url4 . '" title="Member Edit"><i class="icon-pencil5  mr-2"></i>Edit</a>  ';
                        }
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
     * Show    create member.
     * Route: /admin/member-register 
     * Method: get 
     * @return  array()  Response
     */
    public function register()
    {
        die();
        if (check_my_permission(Auth::user()->id, "2") != "1") {
            return redirect()->route('admin.dashboard');
        }
        if (Auth::user()->branch_id > 0) {
            $id = Auth::user()->branch_id;
            $data['branch'] = Branch::where('status', 1)->where('id', '=', $id)->get(['id', 'state_id', 'name']);
        } else {
            $data['branch'] = Branch::where('status', 1)->get(['id', 'state_id', 'name']);
        }
        $data['title'] = 'Member | Registration';
        $data['state'] = stateList();
        $data['occupation'] = occupationList();
        $data['religion'] = religionList();
        $data['specialCategory'] = specialCategoryList();
        $data['idTypes'] = idTypeList();
        $data['relations'] = relationsList();
        return view('templates.admin.member.add', $data);
    }
    /**
     * Show  member Member Detail.
     * Route: /admin/member-detail 
     * Method: get 
     * @return  array()  Response
     */
    public function detail($id)
    {
        if (check_my_permission(Auth::user()->id, "3") != "1") {
            return redirect()->route('admin.dashboard');
        }
        $data['title'] = 'Member | Detail';
        $data['membercompany'] = '';
        if ($_GET['type'] == 0) {
            $data['membercompany'] = MemberCompany::has('company')->with([
                'memberAssociate:id,first_name,last_name,associate_no,member_id,company_id'
            ])
                ->where('customer_id', $id)->first();
        }
        $data['memberDetail'] = Member::with([
            'children' => function ($q) {
                $q->select(['id', 'first_name', 'last_name', 'associate_no', 'member_id', 'is_block']);
            }
        ])->where('id', $id)->first();
        $data['bankDetail'] = \App\Models\MemberBankDetail::where('member_id', $id)->first();
        $data['nomineeDetail'] = \App\Models\MemberNominee::where('member_id', $id)->first();
        $data['idProofDetail'] = \App\Models\MemberIdProof::where('member_id', $id)->first();
        $recipt = Receipt::where('member_id', $id)->where('receipts_for', 1)->first('id');
        $data['recipt'] = $recipt ? $recipt->id : '';
        return view('templates.admin.member.detail', $data);
    }
    /**
     * Show  member receipt
     * Route: /admin/member-receipt 
     * Method: get 
     * @param  $id
     * @return  array()  Response
     */
    public function receipt($id)
    {
        if (check_my_permission(Auth::user()->id, "3") != "1") {
            return redirect()->route('admin.dashboard');
        }
        $data['title'] = 'Member | Receipt';
        $data['type'] = '1';
        $data['receipt'] = Receipt::with([
            'memberReceipt' => function ($query) {
                $query->select('id', 'member_id', 'first_name', 'last_name', 'mobile_no', 'address', 'form_no');
            }
        ])->with([
                    'branchReceipt' => function ($query) {
                        $query->select('id', 'name', 'branch_code');
                    }
                ])->where('id', $id)->first();
        $data['receipt_amount'] = ReceiptAmount::where('receipt_id', $id)->get(['receipt_id', 'amount', 'type_label']);
        return view('templates.admin.member.receipt', $data);
    }
    /**
     * Get city list according to district.
     * Route: ajax call from - /branch/member/registration
     * Method: Post 
     * @param  \Illuminate\Http\Request  $request
     * @return JSON array
     */
    public function getCity(Request $request)
    {
        $city = cityListState($request->district_id);
        $return_array = compact('city');
        return json_encode($return_array);
    }
    /**
     * Get district list according to state.
     * Route: ajax call from - /branch/member/registration
     * Method: Post 
     * @param  \Illuminate\Http\Request  $request
     * @return JSON array
     */
    public function getDistrict(Request $request)
    {
        $district = districtList($request->state_id);
        $return_array = compact('district');
        return json_encode($return_array);
    }
    /**
     * Get associate detail through associate code.
     * Route: ajax call from - /branch/member/registration
     * Method: Post 
     * @param  \Illuminate\Http\Request  $request
     * @return JSON array
     */
    public function getAssociateMember(Request $request)
    {
        $a = array("id", "first_name", "last_name", "current_carder_id", 'member_id', 'associate_status', 'is_block');
        $resCount = 0;
        $data = '';
        $carder = "";
        $carder_id = "";
        $msg = '0';
        if ($request->code != '') {
            $data = memberFieldDataStatus($a, $request->code, 'associate_no');
            $resCount = count($data);
            if ($resCount > 0) {
                if ($data[0]->is_block == 1) {
                    $msg = 'block';
                } else {
                    $carder = getCarderName($data[0]->current_carder_id);
                    $carder_id = $data[0]->current_carder_id;
                    if ($data[0]->associate_status == 0) {
                        $msg = 'InactiveAssociate';
                    }
                }
            }
        }
        $return_array = compact('data', 'resCount', 'carder', 'carder_id', 'msg');
        return json_encode($return_array);
    }
    /**
     * member Email id exists or not .
     * Route: ajax call from - /branch/member/registration
     * Method: Post 
     * @param  \Illuminate\Http\Request  $request
     * @return JSON array
     */
    public function memberEmailCheck(Request $request)
    {
        // pass email id or role id for check email exists or not
        $id = $request->id;
        if ($id > 0) {
            $data = Member::where([['email', '=', $request->email], ['id', '!=', $id]])->count();
        } else {
            $data = Member::where([['email', '=', $request->email],])->count();
        }
        $resCount = $data;
        $return_array = compact('resCount');
        return json_encode($return_array);
    }
    /**
     * Member update.
     * Route: /admin/member-detail 
     * Method: get 
     * @return  array()  Response
     */
    public function edit($id)
    {
        if (check_my_permission(Auth::user()->id, "236") != "1" || check_my_permission(Auth::user()->id, "237") != "1") {
            return redirect()->route('admin.dashboard');
        }
        $data['title'] = 'Member | Edit';
        $data['branch'] = Branch::where('status', 1)->get();
        $data['state'] = stateList();
        $data['occupation'] = occupationList();
        $data['religion'] = religionList();
        $data['specialCategory'] = specialCategoryList();
        $data['idTypes'] = idTypeList();
        $data['relations'] = relationsList();
        $data['membercompany'] = '';
        if ($_GET['type'] == 0) {
            $data['membercompany'] = \App\Models\MemberCompany::with([
                'memberAssociate' => function ($q) {
                    $q->select('id', 'first_name', 'last_name', 'associate_no', 'member_id', 'is_employee', 'employee_id');
                }
            ])->where('customer_id', $id)->first();
        }
        $data['memberDetail'] = Member::where('id', $id)->first();
        $data['bankDetail'] = \App\Models\MemberBankDetail::where('member_id', $id)->first();
        $data['nomineeDetail'] = \App\Models\MemberNominee::where('member_id', $id)->first();
        $data['idProofDetail'] = \App\Models\MemberIdProof::where('member_id', $id)->first();
        return view('templates.admin.member.edit', $data);
    }
    /**
     * Save Member data.
     * Route: /branch/member/registration 
     * Method: Post 
     * @param  \Illuminate\Http\Request  $request
     * @return  array()  Response
     */
    public function save(Request $request)
    {
        $rules = [
            'form_no' => ['required', 'numeric'],
            'first_name' => ['required'],
        ];
        $customMessages = [
            'required' => 'Please enter :attribute.',
            'unique' => ' :Attribute already exists.'
        ];
        $this->validate($request, $rules, $customMessages);
        DB::beginTransaction();
        try {
            Session::put('created_at', $request['created_at']);
            $globaldate = $request['created_at'];
            if ($request['id'] == 0) {
                $faCode = "CI";
                $branch_id = $request['branch_id'];
                $getMiCode = getLastMiCodeCustomer(5, $branch_id);
                if (!empty($getMiCode)) {
                    if ($getMiCode->mi_code == 9999998) {
                        $miCodeAdd = $getMiCode->mi_code + 2;
                    } else {
                        $miCodeAdd = $getMiCode->mi_code + 1;
                    }
                } else {
                    $miCodeAdd = 1;
                }
                $miCode = str_pad($miCodeAdd, 6, '0', STR_PAD_LEFT);
                $getBranchCode = getBranchCode($branch_id);
                $branchCode = $getBranchCode->branch_code;
                // genarate Member id 
                $getmemberID = $branchCode . $faCode . $miCode;
                // save member details
                $branchMi = $branchCode . $miCode;
                $data = $this->getData($request->all(), 'create');
            } else {
                $data = $this->getData($request->all(), 'update');
            }
            if ($request['id'] == 0) {
                $data['role_id'] = 5;
                $data['member_id'] = $getmemberID;
                $data['mi_code'] = $miCode;
                $data['fa_code'] = $faCode;
                $data['branch_id'] = $branch_id;
                $data['branch_code'] = $branchCode;
                $data['branch_mi'] = $branchMi;
                $data['created_at'] = $request['created_at'];
                $member = Member::create($data);
                $memberId = $member->id;
                $customerid = $member->member_id;
                /****associate target entry****/
            } else {
                $memberId = $request['id'];
                $member = Member::find($memberId);
                $member->update($data);
            }
            if ($request['id'] == 0) {
                if ($request->hasFile('file')) {
                    // $mainFolder = 'update_15g';
                    // $file = $request->file;
                    // $uploadFile = $file->getClientOriginalName();
                    // $filename = pathinfo($uploadFile, PATHINFO_FILENAME);
                    // $fname = $filename . '_' . time() . '.' . $file->getClientOriginalExtension();
                    // ImageUpload::upload($file, $mainFolder, $fname);
                    // $fData = [
                    //     'file_name' => $fname,
                    //     'file_path' => $mainFolder,
                    //     'file_extension' => $file->getClientOriginalExtension(),
                    // ];
                    // $form15GUpdate = Form15G::find($id);             
                    // $form15GUpdate->file=$fname; 
                    // $form15GUpdate->save();
                    // $formData['year'] = $request->year;
                    // $formData['member_id'] = $memberId;
                    // $formData['member_id'] = $fname;
                    // $form = Form15G::create($formData);
                    // $id=$form->id;
                }
            }
            // upload signature or profile picture  
            $imgChk = 0;
            $signature_filename = '';
            $photo_filename = '';
            if ($request->hasFile('signature')) {
                $signature_image = $request->file('signature');
                $signature_filename = $memberId . '_' . time() . '.' . $signature_image->getClientOriginalExtension();
                $signature_location = 'asset/profile/member_signature/' . $signature_filename;
                $mainFolderSignature = '/profile/member_signature/';
                ImageUpload::upload($signature_image, $mainFolderSignature, $signature_filename);
                $imgChk++;
            }
            if ($request->hasFile('photo')) {
                $photo_image = $request->file('photo');
                $photo_filename = $memberId . '_' . time() . '.' . $photo_image->getClientOriginalExtension();
                $photo_location = 'asset/profile/member_avatar/' . $photo_filename;
                $mainFolderPhoto = '/profile/member_avatar/';
                ImageUpload::upload($photo_image, $mainFolderPhoto, $photo_filename);
                $imgChk++;
            }
            if ($imgChk > 0) {
                $memberUpdate = Member::find($memberId);
                if ($signature_filename != '') {
                    $memberUpdate->signature = $signature_filename;
                }
                if ($photo_filename != '') {
                    $memberUpdate->photo = $photo_filename;
                }
                $memberUpdate->created_at = $request['created_at'];
                $memberUpdate->save();
            }
            // Save bank Information    
            if ($request['bank_name'] != '' || $request['bank_branch_name'] != '' || $request['bank_account_no'] != '' || $request['bank_ifsc'] != '' || $request['bank_branch_address'] != '') {
                if ($request['id'] == 0 || $request['bank_id'] == 0) {
                    $dataBank = $this->getBankData($request->all(), 'create', $memberId);
                } else {
                    $dataBank = $this->getBankData($request->all(), 'update', $memberId);
                }
                if ($request['bank_id'] == 0) {
                    $bankInfoSave = \App\Models\MemberBankDetail::create($dataBank);
                } else {
                    $bankInfoSave = \App\Models\MemberBankDetail::find($request['bank_id']);
                    $bankInfoSave->update($dataBank);
                }
            }
            // Save nominee information
            if ($request['id'] == 0) {
                $dataNominee = $this->getNomineeData($request->all(), 'create', $memberId);
            } else {
                $dataNominee = $this->getNomineeData($request->all(), 'update', $memberId);
            }
            if ($request['nominee_id'] == 0) {
                $nomineeInfoSave = \App\Models\MemberNominee::create($dataNominee);
            } else {
                $nomineeInfoSave = \App\Models\MemberNominee::find($request['nominee_id']);
                $nomineeInfoSave->update($dataNominee);
            }
            // Save Id proofs
            if ($request['id'] == 0) {
                $dataIdProof = $this->getIdProofData($request->all(), 'create', $memberId);
            } else {
                $dataIdProof = $this->getIdProofData($request->all(), 'update', $memberId);
            }
            if ($request['IdProof_id'] == 0) {
                $idProofInfoSave = \App\Models\MemberIdProof::create($dataIdProof);
            } else {
                $idProofInfoSave = \App\Models\MemberIdProof::find($request['IdProof_id']);
                $idProofInfoSave->update($dataIdProof);
            }
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('alert', $ex->getMessage());
        }
        if ($request['id'] == 0) {
            $contactNumber = array();
            $contactNumber[] = $data['mobile_no'];
            $text = "Dear " . $data['first_name'] . ' ' . $data['last_name'] . ', ';
            $text .= "Welcome to S.B. Micro Finance association Your MI " . $getmemberID . ", Thank you so much for allowing us to help you with your recent account opening. Have a good day. Toll-free No18001216567 https://play.google.com/store/apps/details?id=com.samraddhbestwin.microfinance";
            $numberWithMessage = array();
            $numberWithMessage['contactNumber'] = $contactNumber;
            $numberWithMessage['message'] = $text;
            $templateId = 1207162695930964085;
            /*$adminEmail = new Email();
               $adminEmail->sendEmail( env('ADMIN_EMAIL', 'micro@mailinator.com'), $member);*/
            $sendToMember = new Sms();
            $sendToMember->sendSms($contactNumber, $text, $templateId);
        }
        if ($request['id'] == 0) {
            if ($memberId) {
                $msg = 'Customer Id :' . $customerid;
                return redirect()->route('admin.member')->with('success', 'Member registered successfully! ' . $msg);
            } else {
                return back()->with('alert', 'Member not created.Try again');
            }
        } else {
            if (isset($request['action']) && $request['action'] == 'change-request') {
                $correctionRequest = CorrectionRequests::find($request['requestid']);
                $crData['status'] = 1;
                $correctionRequest->update($crData);
                return Redirect::to('admin/member-edit/' . $request['id'] . '?action=change-request&request-id=' . $request['requestid'] . '')->with('success', 'Member updated successfully!');
            } else {
                return Redirect::to('admin/member-edit/' . $request['id'] . '?type=' . $request['getType'] . '')->with('success', 'Member updated successfully!');
            }
        }
    }
    /**
     * Get member data to save.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getData($request, $type)
    {
        if ($type == 'create') {
            $data['re_date'] = date("Y-m-d", strtotime(str_replace('/', '-', $request['application_date'])));
            $data['created_at'] = $request['created_at'];
        }
        $data['form_no'] = $request['form_no'];
        $data['first_name'] = $request['first_name'];
        $data['associate_id'] = $request['associate_id'];
        $data['associate_code'] = $request['associate_code'];
        $data['last_name'] = $request['last_name'];
        $data['mobile_no'] = $request['mobile_no'];
        $data['email'] = $request['email'];
        $data['dob'] = date("Y-m-d", strtotime(str_replace('/', '-', $request['dob'])));
        $data['age'] = $request['age'];
        if (isset($request['gender'])) {
            $data['gender'] = $request['gender'];
        }
        $data['annual_income'] = $request['annual_income'];
        $data['occupation_id'] = $request['occupation'];
        if (isset($request['marital_status'])) {
            $data['marital_status'] = $request['marital_status'];
        }
        if (isset($request['anniversary_date']) && $request['marital_status'] == '1') {
            $data['anniversary_date'] = date("Y-m-d", strtotime(convertDate($request['anniversary_date'])));
        } else {
            $data['anniversary_date'] = null;
        }
        $data['father_husband'] = $request['f_h_name'];
        $data['address'] = preg_replace("/\r|\n/", "", $request['address']);
        $data['state_id'] = $request['state_id'];
        $data['district_id'] = $request['district_id'];
        $data['city_id'] = $request['city_id'];
        $data['village'] = $request['village_name'];
        $data['pin_code'] = $request['pincode'];
        $data['religion_id'] = $request['religion'];
        $data['mother_name'] = $request['mother_name'];
        $data['special_category_id'] = $request['special_category'];
        $data['status'] = 1;
        return $data;
    }
    /**
     * Get member bank information for save.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getBankData($request, $type, $memberId)
    {
        if ($type == 'create') {
            $data['member_id'] = $memberId;
            $data['created_at'] = $request['created_at'];
        }
        $data['bank_name'] = $request['bank_name'];
        $data['branch_name'] = $request['bank_branch_name'];
        $data['account_no'] = $request['bank_account_no'];
        $data['ifsc_code'] = $request['bank_ifsc'];
        $data['address'] = preg_replace("/\r|\n/", "", $request['bank_branch_address']);
        return $data;
    }
    /**
     * Get member nominee detail for save.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getNomineeData($request, $type, $memberId)
    {
        if ($type == 'create') {
            $data['member_id'] = $memberId;
            $data['created_at'] = $request['created_at'];
        }
        $data['name'] = $request['nominee_first_name'];
        $data['relation'] = $request['nominee_relation'];
        if (isset($request['nominee_gender'])) {
            $data['gender'] = $request['nominee_gender'];
        }
        if (isset($request['nominee_dob']) && $request['nominee_dob'] != '') {
            $data['dob'] = date("Y-m-d", strtotime(str_replace('/', '-', $request['nominee_dob'])));
        }
        $data['age'] = $request['nominee_age'];
        $data['mobile_no'] = $request['nominee_mobile_no'];
        if (isset($request['is_minor']) && !empty($request['is_minor'])) {
            $data['is_minor'] = $request['is_minor'];
            $data['parent_name'] = $request['parent_nominee_name'];
            $data['parent_no'] = $request['parent_nominee_mobile_no'];
        } else {
            $data['is_minor'] = 0;
        }
        return $data;
    }
    /**
     * Get member Id proof for save.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getIdProofData($request, $type, $memberId)
    {
        if ($type == 'create') {
            $data['member_id'] = $memberId;
            $data['created_at'] = $request['created_at'];
        }
        $data['first_id_type_id'] = $request['first_id_type'];
        $data['first_id_no'] = $request['first_id_proof_no'];
        $data['first_address'] = preg_replace("/\r|\n/", "", $request['first_address_proof']);
        $data['second_id_type_id'] = $request['second_id_type'];
        $data['second_id_no'] = $request['second_id_proof_no'];
        $data['second_address'] = preg_replace("/\r|\n/", "", $request['second_address_proof']);
        return $data;
    }
    /**
     * Update Member Image.
     * Route: /admin/member-detail
     * Method: Post 
     * @param  \Illuminate\Http\Request  $request
     * @return  array()  Response
     */
    public function imageUpload(Request $request)
    {
        $memberId = $request->input('member-id');
        $signature_filename = '';
        $photo_filename = '';
        if ($request->hasFile('signature')) {
            $validator = Validator::make($request->all(), [
                'signature' => 'mimes:jpeg,jpg,png,gif|required',
            ]);
            if ($validator->fails()) {
                return back()->with('alert', 'Please upload only Jpeg,jpg,png file!');
            }
            $signature_image = $request->file('signature');
            $signature_filename = $memberId . '_' . time() . '.' . $signature_image->getClientOriginalExtension();
            $signature_location = 'asset/profile/member_signature/' . $signature_filename;
            $mainFolderSignature = 'profile/member_signature/';
            $return = ImageUpload::upload($signature_image, $mainFolderSignature, $signature_filename);
            //    $return = Image::make($signature_image)->resize(300,300)->save($signature_location);
            if ($return) {
                $action = 'success';
                $message = 'Signature uploaded Successfully!';
            } else {
                $action = 'alert';
                $message = 'Signature not uploaded Successfully!';
            }
        }
        if ($request->hasFile('photo')) {
            $validator = Validator::make($request->all(), [
                'photo' => 'mimes:jpeg,jpg,png,gif|required',
            ]);
            if ($validator->fails()) {
                return back()->with('alert', 'Please upload only Jpeg,jpg,png file!');
            }
            $photo_image = $request->file('photo');
            $photo_filename = $memberId . '_' . time() . '.' . $photo_image->getClientOriginalExtension();
            $photo_location = 'asset/profile/member_avatar/' . $photo_filename;
            $mainFolderPhoto = 'profile/member_avatar/' . $photo_filename;
            $return = ImageUpload::upload($photo_image, $mainFolderPhoto, $photo_filename);
            if ($return) {
                $action = 'success';
                $message = 'Photo uploaded Successfully!';
            } else {
                $action = 'alert';
                $message = 'Photo not uploaded Successfully!';
            }
        }
        $memberUpdate = Member::find($memberId);
        if ($signature_filename && $signature_filename != '') {
            $memberUpdate->signature = $signature_filename;
        }
        if ($photo_filename && $photo_filename != '') {
            $memberUpdate->photo = $photo_filename;
        }
        if ($memberUpdate->signature != '' && $memberUpdate->photo != '') {
            $memberUpdate->is_block = 0;
        }
        $memberUpdate->created_at = $request['created_at'];
        $memberUpdate->save();
        return back()->with($action, $message);
    }
    /**
     * Show    members Loan List.
     * Route: /admin/member-loan 
     * Method: get 
     * @return  array()  Response
     */
    public function loan($id)
    {
        if (check_my_permission(Auth::user()->id, "240") != "1") {
            return redirect()->route('admin.dashboard');
        }
        $data['title'] = 'Member | Loan';
        $data['memberDetail'] = Member::where('id', $id)->first(['id', 'member_id', 'first_name', 'last_name']);
        return view('templates.admin.member.member_loan', $data);
    }
    /**
     * Fetch loan listing data by member id.
     *@method post call ajax
     * @param  \App\Reservation  $reservation
     * @return \Illuminate\Http\Response
     */
    public function membersLoanListing(Request $request)
    {
        if ($request->ajax()) {
            $memberId = $request['member_id'];
            $data = Memberloans::with('loan', 'loanMemberAssociate', 'company')->whereHas('loan', function ($query) {
                $query->where('loan_type', '!=', 'G');
            })->where('customer_id', $memberId)->orderBy('id', 'DESC')->get();
            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('date', function ($row) {
                    $date = date("d/m/Y", strtotime($row->created_at));
                    return $date;
                })
                ->rawColumns(['date'])
                ->addColumn('loan_name', function ($row) {
                    $loan_name = $row['loan']->name;
                    return $loan_name;
                })
                ->rawColumns(['loan_name'])
                ->addColumn('transfer_amount', function ($row) {
                    $transfer_amount = $row->transfer_amount;
                    return $transfer_amount;
                })
                ->rawColumns(['transfer_amount'])
                ->addColumn('amount', function ($row) {
                    $amount = $row->deposite_amount;
                    return $amount;
                })
                ->rawColumns(['amount'])
                ->addColumn('loan_amount', function ($row) {
                    $amount = $row->amount . ' <i class="fas fa-rupee-sign"></i>';
                    return number_format((float) $amount, 2, '.', '');
                })
                ->rawColumns(['account_number'])
                ->addColumn('account_number', function ($row) {
                    $account_number = $row->account_number;
                    return $account_number;
                })
                ->rawColumns(['account_number'])
                ->addColumn('file_charge', function ($row) {
                    $amount = $row->file_charges . ' <i class="fas fa-rupee-sign"></i>';
                    return number_format((float) $amount, 2, '.', '');
                })
                ->rawColumns(['file_charge'])
                ->addColumn('insurence_charge', function ($row) {
                    $amount = $row->insurance_charge . ' <i class="fas fa-rupee-sign"></i>';
                    return number_format((float) $amount, 2, '.', '');
                })
                ->rawColumns(['insurence_charge'])
                ->addColumn('file_charge_type', function ($row) {
                    $type = ($row->file_charge_type) ? 'Loan' : 'Cash';
                    return $type;
                })
                ->rawColumns(['file_charge_type'])
                ->addColumn('branch', function ($row) {
                    $branch = Branch::where('id', $row->branch_id)->first()->name;
                    return $branch;
                })
                ->rawColumns(['branch'])
                ->addColumn('associate_code', function ($row) {
                    $associate_code = $row['loanMemberAssociate']->associate_no;
                    return $associate_code;
                })
                ->rawColumns(['associate_code'])
                ->addColumn('associate_name', function ($row) {
                    $associate_name = $row['loanMemberAssociate']->first_name . ' ' . $row['loanMemberAssociate']->last_name;
                    return $associate_name;
                })
                ->rawColumns(['associate_name'])
                ->addColumn('approve_date', function ($row) {
                    if ($row->approve_date) {
                        return date("d/m/Y", strtotime($row->approve_date));
                    } else {
                        return 'N/A';
                    }
                })
                ->rawColumns(['approve_date'])
                ->addColumn('status', function ($row) {
                    if ($row->status == 0) {
                        $status = 'Pending';
                    } else if ($row->status == 1) {
                        $status = 'Approved';
                    } else if ($row->status == 2) {
                        $status = 'Rejected';
                    } else if ($row->status == 3) {
                        $status = 'Clear';
                    } else if ($row->status == 4) {
                        $status = 'Due';
                    }
                    return $status;
                })
                ->rawColumns(['status'])
                ->addColumn('action', function ($row) {
                    $url = URL::to("admin/loan/deposit/emi-transactions/" . $row->id . "/" . $row->loan_type);
                    $btn = '<a class="dropdown-item" href="' . $url . '" style="padding-left: 3px;"><i class="fas fa-eye mr-2"></i> View</a>';
                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
    }
    /**
     * Fetch loan listing data by member id.
     *@method post call ajax
     * @param  \App\Reservation  $reservation
     * @return \Illuminate\Http\Response
     */
    public function membersGroupLoanListing(Request $request)
    {
        if ($request->ajax()) {
            $memberId = $request['member_id'];
            $data = Grouploans::with('Loan', 'loanBranch', 'loanMemberAssociate', 'groupleaderMemberIDCustom')->whereHas('loan', function ($query) {
                $query->where('loan_type', '=', 'G');
            })->where('customer_id', $memberId)->orderBy('id', 'DESC')->get();
            if (isset($data['0']['group_loan_common_id'])) {
                $data1 = $data['0']['group_loan_common_id'];
            }
            $count_number = '0';
            if (isset($data1) && !empty($data1)) {
                $count_number = Grouploans::where('group_loan_common_id', $data1)->count();
            }
            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('date', function ($row) {
                    $date = date("d/m/Y", strtotime($row->created_at)) ?? 'N/A';
                    return $date;
                })
                ->rawColumns(['date'])
                ->addColumn('loan_name', function ($row) {
                    $loan_name = $row['loan']->name;
                    return $loan_name;
                })
                ->rawColumns(['loan_name'])
                ->rawColumns(['account_number'])
                ->addColumn('account_number', function ($row) {
                    $account_number = $row->account_number;
                    return $account_number;
                })
                ->rawColumns(['account_number'])
                ->rawColumns(['leader'])
                ->addColumn('leader', function ($row) {
                    $leader = $row['groupleaderMemberIDCustom']->first_name . ' ' . $row['groupleaderMemberIDCustom']->last_name;
                    return $leader;
                })
                ->rawColumns(['leader'])
                ->addColumn('amount', function ($row) {
                    $amount = $row['deposite_amount'] ?? '0';
                    return $amount;
                })
                ->rawColumns(['amount'])
                ->addColumn('insurence_charge', function ($row) {
                    $insurenceAmount = $row->file_charges . ' <i class="fas fa-rupee-sign"></i>';
                    return number_format((float) $insurenceAmount, 2, '.', '');
                })
                ->rawColumns(['insurence_charge'])
                ->addColumn('file_charge', function ($row) {
                    $amount = $row->insurance_charge . ' <i class="fas fa-rupee-sign"></i>';
                    return number_format((float) $amount, 2, '.', '');
                })
                ->rawColumns(['file_charge'])
                ->addColumn('file_charge_type', function ($row) {
                    $type = ($row->file_charge_type) ? 'Loan' : 'Cash';
                    return $type;
                })
                ->rawColumns(['file_charge_type'])
                ->addColumn('loan_amount', function ($row) {
                    $amount = $row->amount . ' <i class="fas fa-rupee-sign"></i>';
                    return number_format((float) $amount, 2, '.', '');
                })
                ->rawColumns(['loan_amount'])
                ->addColumn('total_amount', function ($row) {
                    $total_amount = $row['loanTransaction']->sum('deposit') ?? ' 0';
                    return $total_amount;
                })
                ->rawColumns(['total_amount'])
                ->addColumn('branch', function ($row) {
                    $branch = $row['loanBranch']->name;
                    return $branch;
                })
                ->rawColumns(['branch'])
                ->addColumn('associate_code', function ($row) {
                    $associate_code = $row['loanMemberAssociate']->associate_no;
                    return $associate_code;
                })
                ->rawColumns(['associate_code'])
                ->addColumn('associate_name', function ($row) {
                    $associate_name = $row['loanMemberAssociate']->first_name . ' ' . $row['loanMemberAssociate']->last_name;
                    return $associate_name;
                })
                ->rawColumns(['associate_name'])
                ->addColumn('approve_date', function ($row) {
                    if ($row->approve_date) {
                        return date("d/m/Y", strtotime($row->approve_date));
                    } else {
                        return 'N/A';
                    }
                })
                ->rawColumns(['approve_date'])
                ->addColumn('memberNo', function ($row) use ($count_number) {
                    $memberNo = $count_number ?? '0 ';
                    return $memberNo;
                })
                ->rawColumns(['memberNo'])
                ->addColumn('status', function ($row) {
                    if ($row->status == 0) {
                        $status = 'Pending';
                    } else if ($row->status == 1) {
                        $status = 'Approved';
                    } else if ($row->status == 2) {
                        $status = 'Rejected';
                    } else if ($row->status == 3) {
                        $status = 'Clear';
                    } else if ($row->status == 4) {
                        $status = 'Due';
                    }
                    return $status;
                })
                ->rawColumns(['status'])
                ->addColumn('action', function ($row) {
                    $url = URL::to("admin/loan/deposit/emi-transactions/" . $row->id . '/' . $row->loan_type . '');
                    $btn = '<a class="dropdown-item pl-0" href="' . $url . '"><i class="fas fa-eye mr-2s"></i>View</a>';
                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
    }
    /**
     * Show    members investment.
     * Route: /admin/member-investment 
     * Method: get 
     * @return  array()  Response
     */
    public function investment($id)
    {
        if (check_my_permission(Auth::user()->id, "239") != "1") {
            return redirect()->route('admin.dashboard');
        }
        $data['title'] = 'Member | Investment';
        $data['memberDetail'] = Member::where('id', $id)->first(['id', 'member_id', 'first_name', 'last_name']);
        return view('templates.admin.member.member_investment', $data);
    }
    /**
     * Fetch invest listing data by member id.
     *@method post call ajax
     * @param  \App\Reservation  $reservation
     * @return \Illuminate\Http\Response
     */
    public function membersInvestment(Request $request)
    {
        if ($request->ajax()) {
            $memberId = $request['member_id'];
            $data = \App\Models\Memberinvestments::has('company')->with('plan', 'branch', 'associateMember', 'company', 'memberCompany', 'member')->where('customer_id', $memberId)->orderBy('id', 'DESC')->get();
            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('company_name', function ($row) {
                    $company_name = $row['company']->name;
                    return $company_name;
                })
                ->rawColumns(['company_name'])
                ->addColumn('branch', function ($row) {
                    $branch = $row['branch']->name;
                    return $branch;
                })
                ->rawColumns(['branch'])
                ->addColumn('date', function ($row) {
                    $date = date("d/m/Y", strtotime($row->created_at));
                    return $date;
                })
                ->rawColumns(['date'])
                ->addColumn('branch_code', function ($row) {
                    $branch_code = $row['branch']->branch_code;
                    return $branch_code;
                })
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
                ->addColumn('plan', function ($row) {
                    $plan = $row['plan']->name;
                    return $plan;
                })
                ->rawColumns(['plan'])
                ->addColumn('member_id', function ($row) {
                    $member_id = $row['memberCompany']->member_id;
                    return $member_id;
                })
                ->rawColumns(['member_id'])
                ->addColumn('member_name', function ($row) {
                    $member_name = $row['member']->first_name . ' ' . $row['member']->last_name;
                    return $member_name;
                })
                ->rawColumns(['member_name'])
                ->addColumn('account_no', function ($row) {
                    $account_no = $row->account_number;
                    return $account_no;
                })
                ->rawColumns(['account_no'])
                ->addColumn('amount', function ($row) {
                    $amount = $row->deposite_amount;
                    return $amount;
                })
                ->rawColumns(['amount'])
                ->addColumn('rate', function ($row) {
                    $rate = $row->interest_rate ?? '0';
                    return $rate;
                })
                ->rawColumns(['rate'])
                ->addColumn('fd_amount', function ($row) {
                    $fdAmount = EliMoneybackInvestments::where('investment_id', $row->id)->first();
                    if ($fdAmount) {
                        $fdAmount = number_format($fdAmount->mb_fd_amount, 2);
                    } else {
                        $fdAmount = 'N/A';
                    }
                    return $fdAmount;
                })
                ->rawColumns(['fd_amount'])
                ->addColumn('carry_forward_amount', function ($row) {
                    $carry_forward_amount = number_format($row->carry_forward_amount, 2);
                    return $carry_forward_amount;
                })
                ->rawColumns(['carry_forward_amount'])
                ->addColumn('mamount', function ($row) {
                    $mamount = $row->maturity_amount;
                    return $mamount;
                })
                ->rawColumns(['mamount'])
                ->addColumn('tenure', function ($row) {
                    if ($row->tenure) {
                        $tenure = $row->tenure . ' years';
                    } else {
                        $tenure = '';
                    }
                    return $tenure;
                })
                ->rawColumns(['tenure'])
                ->addColumn('associate_code', function ($row) {
                    $associate_code = $row['associateMember']->associate_no ?? ' N/A';
                    return $associate_code;
                })
                ->rawColumns(['associate_code'])
                ->addColumn('associate_name', function ($row) {
                    $associate_name = (!empty ($row['associateMember']->first_name) && !empty ($row['associateMember']->last_name)) ? $row['associateMember']->first_name . ' ' . $row['associateMember']->last_name : '';
                    return $associate_name;
                })
                ->rawColumns(['associate_name'])
                ->addColumn('action', function ($row) {
                    $url = '';
                    $passbookUrl = URL::to("admin/investment/passbook/transaction/" . $row->id . "/" . $row['plan']->plan_category_code . "");
                    $btn = '<a class="dropdown-item" href="' . $passbookUrl . '" title="Member Investment"><i class="icon-eye-blocked2  mr-2" ></i></a>';
                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
    }
    /**
     * Show    members account.
     * Route: /admin/member-account 
     * Method: get 
     * @return  array()  Response
     */
    public function account($id)
    {
        if (check_my_permission(Auth::user()->id, "3") != "1") {
            return redirect()->route('admin.dashboard');
        }
        $data['title'] = 'Member | Account';
        $data['accountDetail'] = \App\Models\SavingAccount::with([
            'savingBranch' => function ($query) {
                $query->select('id', 'name', 'branch_code', 'city_id', 'pin_code', 'address');
            }
        ])->where('id', $id)->first();
        $data['memberDetail'] = Member::where('id', $data['accountDetail']->customer_id)->first();
        if (!empty($data['accountDetail'])) {
            $data['accountTranscation'] = \App\Models\SavingAccountTransactionView::where('saving_account_id', $data['accountDetail']->id)->orderby('opening_date', 'DESC')->limit(10)->get();
        }
        $data['memberId'] = $data['accountDetail']->customer_id;
        return view('templates.admin.member.member_account', $data);
    }
    /**
     * Show    members account.
     * Route: /admin/member-account-statement 
     * Method: get 
     * @return  array()  Response
     */
    public function statement($id, $memberId)
    {
        $data['title'] = 'Member | Account';
        $data['memberDetail'] = Member::where('id', $memberId)->first();
        $data['accountDetail'] = \App\Models\SavingAccount::with([
            'savingBranch' => function ($query) {
                $query->select('id', 'name', 'branch_code', 'city_id', 'pin_code', 'address');
            }
        ])->where('id', $id)->first();
        return view('templates.admin.member.account_statemant', $data);
    }
    /**
     * filer account statement .
     * Route: /admin/member-account-statement 
     * Method: Post 
     * @param  \Illuminate\Http\Request  $request
     * @return  array()  Response
     */
    public function statement_filter(Request $request)
    {
        //  print_r($_POST);die;
        $data['title'] = 'Member | Account Statement';
        $data['accountDetail'] = \App\Models\SavingAccount::with([
            'savingBranch' => function ($query) {
                $query->select('id', 'name', 'branch_code', 'city_id', 'pin_code', 'address');
            }
        ])->where('id', $request['id'])->first();
        $memberId = $data['accountDetail']->customer_id;
        $data['memberDetail'] = Member::where('id', $memberId)->first();
        $startDate = date("Y-m-d", strtotime(convertDate($request['start_date'])));
        $endDate = date("Y-m-d", strtotime(convertDate($request['end_date'])));
        $data['accountTranscation'] = \App\Models\SavingAccountTransactionView::where('saving_account_id', $request['id'])->whereBetween(\DB::raw('DATE(opening_date)'), [$startDate, $endDate])->orderby('opening_date', 'DESC')->get();
        $data['startDate'] = $request['start_date'];
        $data['endDate'] = $request['end_date'];
        $data['is_fillter'] = 1;
        return view('templates.admin.member.account_statemant', $data);
    }
    /**
     * All transaction Listing.
     * Route: /member/passbook
     * Method: get 
     * @return  array()  Response
     */
    public function transactions($id)
    {
        if (check_my_permission(Auth::user()->id, "242") != "1") {
            return redirect()->route('admin.dashboard');
        }
        $data['title'] = 'Member | Transaction Listing';
        $data['memberDetail'] = Member::where('id', $id)->first(['id', 'member_id', 'first_name', 'last_name']);
        return view('templates.admin.member.member_transaction', $data);
    }
    /**
     * Fetch transaction listing data by member id.
     *@method post call ajax
     * @param  \App\Reservation  $reservation
     * @return \Illuminate\Http\Response
     */
    public function transactionsListing(Request $request)
    {
        if ($request->ajax()) {
            $arrFormData = array();
            //   print_r($_POST);die;
            if (!empty($_POST['searchform'])) {
                foreach ($_POST['searchform'] as $frm_data) {
                    $arrFormData[$frm_data['name']] = $frm_data['value'];
                }
            }
            $id = $arrFormData['member_id'];
            // echo $arrFormData['is_search'];die;
            $data = MemberTransaction::with(['memberTransaction', 'memberTransactionBranch'])->where('member_id', $id)->where('is_deleted', 0);
            if (isset($arrFormData['is_search']) && $arrFormData['is_search'] == 'yes') {
                if ($arrFormData['start_date'] != '') {
                    $startDate = date("Y-m-d", strtotime(convertDate($arrFormData['start_date'])));
                    if ($arrFormData['end_date'] != '') {
                        $endDate = date("Y-m-d", strtotime(convertDate($arrFormData['end_date'])));
                    } else {
                        $endDate = '';
                    }
                    //  echo '9';die;
                    $data = $data->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate]);
                }
            }
            // print_r($arrFormData);die;
            $data = $data->orderBy('created_at', 'DESC')->get();
            //dd($data);
            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('date', function ($row) {
                    $date = date("d/m/Y", strtotime($row->entry_date));
                    return $date;
                })
                ->rawColumns(['date'])
                ->addColumn('member', function ($row) {
                    if ($row->memberTransaction) {
                        $member = $row->memberTransaction->first_name . ' ' . $row->memberTransaction->last_name;
                        return $member;
                    }
                })
                ->rawColumns(['member'])
                ->addColumn('member_id', function ($row) {
                    if ($row->memberTransaction) {
                        $member_id = $row->memberTransaction->member_id;
                        return $member_id;
                    }
                })
                ->rawColumns(['member_id'])
                ->addColumn('branch_name', function ($row) {
                    $branch_name = '';
                    if ($row->memberTransactionBranch) {
                        $branch_name = $row->memberTransactionBranch->name;
                    }
                    return $branch_name;
                })
                ->rawColumns(['branch_name'])
                ->addColumn('branch_code', function ($row) {
                    $branch_code = '';
                    if ($row->memberTransactionBranch) {
                        $branch_code = $row->memberTransactionBranch->branch_code;
                    }
                    return $branch_code;
                })
                ->rawColumns(['branch_code'])
                ->addColumn('sector', function ($row) {
                    if ($row->memberTransactionBranch) {
                        $sector = $row->memberTransactionBranch->sector;
                        return $sector;
                    } else {
                        return 'N/A';
                    }
                })
                ->rawColumns(['sector'])
                ->addColumn('regan', function ($row) {
                    if ($row->memberTransactionBranch) {
                        $regan = $row->memberTransactionBranch->regan;
                        return $regan;
                    } else {
                        return 'N/A';
                    }
                })
                ->rawColumns(['regan'])
                ->addColumn('zone', function ($row) {
                    if ($row->memberTransactionBranch) {
                        $zone = $row->memberTransactionBranch->zone;
                        return $zone;
                    } else {
                        return 'N/A';
                    }
                })
                ->rawColumns(['zone'])
                ->addColumn('tran_type', function ($row) {
                    $tran_type = 'N/A';
                    if ($row->type == 1) {
                        if ($row->sub_type == 11) {
                            $tran_type = "Member Register(MI Charge)";
                        }
                        if ($row->sub_type == 12) {
                            $tran_type = "Member Register(STN Charge)";
                        }
                        if ($row->sub_type == 13) {
                            $tran_type = "Member  JV Entry ";
                        }
                        if ($row->sub_type == 14) {
                            $tran_type = "Member TDS on Interest";
                        }
                    }
                    if ($row->type == 2) {
                        if ($row->sub_type == 21) {
                            $tran_type = "Associate Commission";
                        }
                        if ($row->sub_type == 22) {
                            $tran_type = "Associate  JV Commission ";
                        }
                        if ($row->sub_type == 23) {
                            $tran_type = "Associate  JV Fuel Charge ";
                        }
                    }
                    if ($row->type == 3) {
                        if ($row->sub_type == 30) {
                            $tran_type = "R-Investment Register";
                        }
                        if ($row->sub_type == 31) {
                            $tran_type = "Account opening";
                        }
                        if ($row->sub_type == 32) {
                            $tran_type = "Renew";
                        }
                        if ($row->sub_type == 33) {
                            $tran_type = "Passbook Print";
                        }
                        if ($row->sub_type == 38) {
                            $tran_type = 'JV Entry';
                        }
                        if ($row->sub_type == 39) {
                            $tran_type = 'JV Stationary Charge';
                        }
                        if ($row->sub_type == 311) {
                            $tran_type = 'JV Passbook Print';
                        }
                        if ($row->sub_type == 312) {
                            $tran_type = 'JV Certificate Print';
                        }
                        $tran_type = "Investment";
                    }
                    if ($row->type == 4) {
                        if ($row->sub_type == 41) {
                            $tran_type = "Account opening";
                        }
                        if ($row->sub_type == 42) {
                            $tran_type = "Deposit";
                        }
                        if ($row->sub_type == 43) {
                            $tran_type = "Withdraw";
                        }
                        if ($row->sub_type == 44) {
                            $tran_type = "Passbook Print";
                        }
                        if ($row->sub_type == 45) {
                            $tran_type = "Commission";
                        }
                        if ($row->sub_type == 46) {
                            $tran_type = "Fuel Charge";
                        }
                        if ($row->sub_type == 412) {
                            $tran_type = 'SSB  JV Entry';
                        }
                        if ($row->sub_type == 413) {
                            $tran_type = 'SSB JV Passbook Print';
                        }
                        if ($row->sub_type == 414) {
                            $tran_type = 'SSB JV Certificate Print';
                        }
                        $tran_type = "Saving Account";
                    }
                    if ($row->type == 5) {
                        if ($row->sub_type == 51 || $row->sub_type == 52 || $row->sub_type == 53 || $row->sub_type == 57) {
                            $tran_type = "Loan";
                        }
                        if ($row->sub_type == 54 || $row->sub_type == 55 || $row->sub_type == 56 || $row->sub_type == 58) {
                            $tran_type = "Group Loan";
                        }
                        if ($row->sub_type == 511) {
                            $tran_type = 'Loan JV Loan';
                        }
                        if ($row->sub_type == 512) {
                            $tran_type = 'Loan JV  Group Loan';
                        }
                        if ($row->sub_type == 513) {
                            $tran_type = 'Loan JV Loan Panelty';
                        }
                        if ($row->sub_type == 514) {
                            $tran_type = 'Loan JV Group Loan Panelty';
                        }
                        if ($row->sub_type == 515) {
                            $tran_type = 'Loan JV Loan Emi';
                        }
                        if ($row->sub_type == 516) {
                            $tran_type = 'Loan JV Group Loan Emi';
                        }
                    }
                    if ($row->type == 6) {
                        if ($row->sub_type == 61) {
                            $tran_type = "Employee Salary";
                        }
                        if ($row->sub_type == 62) {
                            $tran_type = "Employee JV Salary";
                        }
                    }
                    if ($row->type == 7) {
                        if ($row->sub_type == 70) {
                            $tran_type = "Branch Cash";
                        }
                        if ($row->sub_type == 71) {
                            $tran_type = "Branch Cheque";
                        }
                        if ($row->sub_type == 72) {
                            $tran_type = "Branch Online";
                        }
                        if ($row->sub_type == 73) {
                            $tran_type = "Branch SSB";
                        }
                    }
                    if ($row->type == 8) {
                        if ($row->sub_type == 80) {
                            $tran_type = "Bank Cash";
                        }
                        if ($row->sub_type == 81) {
                            $tran_type = "Bank Cheque";
                        }
                        if ($row->sub_type == 82) {
                            $tran_type = "Bank Online";
                        }
                        if ($row->sub_type == 83) {
                            $tran_type = "Bank SSB";
                        }
                    }
                    if ($row->type == 9) {
                        if ($row->sub_type == 90) {
                            $tran_type = "Commission TDS";
                        }
                    }
                    if ($row->type == 10) {
                        if ($row->sub_type == 101) {
                            $tran_type = "Rent - Ledger";
                        } elseif ($row->sub_type == 102) {
                            $tran_type = 'Rent - Payment';
                        } elseif ($row->sub_type == 103) {
                            $tran_type = 'Rent - Security';
                        } elseif ($row->sub_type == 104) {
                            $tran_type = 'Rent - Advance';
                        } elseif ($row->sub_type == 105) {
                            $tran_type = 'Rent - JV Ledger';
                        } elseif ($row->sub_type == 106) {
                            $tran_type = 'Rent - JV Security';
                        }
                    }
                    if ($row->type == 11) {
                        $tran_type = "Demand";
                    }
                    if ($row->type == 12) {
                        if ($row->sub_type == 121) {
                            $tran_type = "Salary - Ledger";
                        } elseif ($row->sub_type == 122) {
                            $tran_type = 'Salary - Transfer';
                        } elseif ($row->sub_type == 123) {
                            $tran_type = 'Salary - Advance';
                        }
                    }
                    if ($row->type == 13) {
                        if ($row->sub_type == 131) {
                            $tran_type = "Demand Advice - Fresh Expense";
                        } elseif ($row->sub_type == 132) {
                            $tran_type = 'Demand Advice - Ta Advance';
                        } elseif ($row->sub_type == 133) {
                            $tran_type = 'Demand Advice - Maturity';
                        } elseif ($row->sub_type == 134) {
                            $tran_type = 'Demand Advice - Prematurity';
                        } elseif ($row->sub_type == 135) {
                            $tran_type = 'Demand Advice - Death Help';
                        } elseif ($row->sub_type == 136) {
                            $tran_type = 'Demand Advice - Death Claim';
                        } elseif ($row->sub_type == 137) {
                            $tran_type = 'Demand Advice - EM';
                        } elseif ($row->sub_type == 138) {
                            $tran_type = 'Demand Advice - JV Ta Advance';
                        }
                    }
                    if ($row->type == 14) {
                        if ($row->sub_type == 141) {
                            $tran_type = "Voucher - Director ";
                        } elseif ($row->sub_type == 142) {
                            $tran_type = 'Voucher  - ShareHolder';
                        } elseif ($row->sub_type == 143) {
                            $tran_type = 'Voucher  - Penal Interest';
                        } elseif ($row->sub_type == 144) {
                            $tran_type = 'Voucher  - Bank';
                        } elseif ($row->sub_type == 145) {
                            $tran_type = 'Voucher  - Eli Loan';
                        }
                    }
                    if ($row->type == 15) {
                        if ($row->sub_type == 151) {
                            $tran_type = 'Director - Deposit';
                        } elseif ($row->sub_type == 152) {
                            $tran_type = 'Director - Withdraw';
                        } elseif ($row->sub_type == 153) {
                            $tran_type = 'Director - JV Deposit';
                        }
                    }
                    if ($row->type == 16) {
                        if ($row->sub_type == 161) {
                            $tran_type = 'ShareHolder - Deposit';
                        } elseif ($row->sub_type == 162) {
                            $tran_type = 'ShareHolder - Transfer';
                        } elseif ($row->sub_type == 163) {
                            $tran_type = 'ShareHolder - JV Deposit';
                        }
                    }
                    if ($row->type == 17) {
                        if ($row->sub_type == 171) {
                            $tran_type = 'Loan From Bank  - Create Loan';
                        } elseif ($row->sub_type == 172) {
                            $tran_type = 'Loan From Bank  - Emi Payment';
                        } elseif ($row->sub_type == 173) {
                            $tran_type = 'Loan From Bank  - JV Entry';
                        }
                    }
                    if ($row->type == 18) {
                        if ($row->sub_type == 181) {
                            $tran_type = 'Bank Charge  - Create';
                        }
                    }
                    if ($row->type == 19) {
                        if ($row->sub_type == 191) {
                            $tran_type = 'Assets  - Assets';
                        } elseif ($row->sub_type == 192) {
                            $tran_type = 'Assets  - Depreciation';
                        }
                    }
                    if ($row->type == 20) {
                        if ($row->sub_type == 201) {
                            $tran_type = 'Expense Booking  - Create Expense';
                        }
                    }
                    if ($row->type == 21) {
                        $tran_type = 'Stationery Charge';
                    }
                    if ($row->type == 22) {
                        if ($row->sub_type == 222) {
                            $tran_type = 'JV To Bank';
                        }
                    }
                    if ($row->type == 23) {
                        if ($row->sub_type == 232) {
                            $tran_type = 'JV To Branch';
                        }
                    }
                    return $tran_type;
                })
                ->rawColumns(['tran_type'])
                ->addColumn('tran_account', function ($row) {
                    $accountNo = 'N/A';
                    if ($row->type == 3) {
                        $accounts = getInvestmentDetails($row->type_id);
                        if (isset ($accounts)) {
                            $accountNo = $accounts->account_number;
                        } else {
                            $accountNo = '';
                        }
                    }
                    if ($row->type == 4) {
                        $account = getSsbAccountNumber($row->type_id);
                        if (isset ($account->account_no)) {
                            $accountNo = $account->account_no;
                        } else {
                            $accountNo = '';
                        }
                    }
                    if ($row->type == 5) {
                        if ($row->sub_type == 54 || $row->sub_type == 55 || $row->sub_type == 56 || $row->sub_type == 58) {
                            $account = getGroupLoanDetail($row->type_id);
                            if (isset ($account->account_no)) {
                                $accountNo = $account->account_no;
                            } else {
                                $accountNo = '';
                            }
                        } elseif ($row->sub_type == 51 || $row->sub_type == 52 || $row->sub_type == 53 || $row->sub_type == 57) {
                            $account = getLoanDetail($row->type_id);
                            if (isset ($account->account_no)) {
                                $accountNo = $account->account_no;
                            }
                        }
                    }
                    return $accountNo;
                })
                ->rawColumns(['tran_account'])
                ->addColumn('amount', function ($row) {
                    $amount = number_format($row->amount, 2);
                    return $amount;
                })
                ->rawColumns(['amount'])
                ->addColumn('payment_type', function ($row) {
                    $payment_type = 'N/A';
                    if ($row->payment_type == 'DR') {
                        $payment_type = 'Debit';
                    }
                    if ($row->payment_type == 'CR') {
                        $payment_type = 'Credit';
                    }
                    return $payment_type;
                })
                ->rawColumns(['payment_type'])
                ->addColumn('payment_mode', function ($row) {
                    $payment_type = 'N/A';
                    $payment_mode = 'N/A';
                    if ($row->payment_mode == 0) {
                        $payment_mode = 'Cash';
                    }
                    if ($row->payment_mode == 1) {
                        $payment_mode = 'Cheque';
                    }
                    if ($row->payment_mode == 2) {
                        $payment_mode = 'Online Transfer';
                    }
                    if ($row->payment_mode == 3) {
                        $payment_mode = 'SSB Transfer Through JV';
                    }
                    if ($row->payment_mode == 4) {
                        if ($row->payment_type == 'CR') {
                            $payment_mode = "Auto Credit";
                        } else {
                            $payment_mode = "Auto Debit";
                        }
                    }
                    if ($row->payment_mode == 5) {
                        $payment_mode = 'BY LOAN AMOUNT';
                    }
                    if ($row->payment_mode == 6) {
                        $payment_mode = 'JV';
                    }
                    if ($row->payment_mode == 7) {
                        $payment_mode = 'CREDIT CARD';
                    }
                    return $payment_mode;
                })
                ->rawColumns(['payment_mode'])
                ->addColumn('detail', function ($row) {
                    $detail = $row->description;
                    return $detail;
                })
                ->rawColumns(['detail'])
                /* ->addColumn('action', function($row){
                     $url = URL::to("branch/member/passbook/cover/".$row->id);                
                     $url2 = URL::to("branch/member/passbook/transaction/".$row->id.'/'.$row->plan_code);
                     $url3 = URL::to("branch/member/passbook/certificate/".$row->id.'/'.$row->plan_code);
                     if($row->plan_code==705 || $row->plan_code==706 || $row->plan_code==712)
                     {
                         $btn = '<a class="" href="'.$url3.'" title="Certificate"><i class="fas fa-certificate text-default mr-2"></i></a>';
                     }
                     else
                     {
                        $btn = '<a class="" href="'.$url.'" title="Passbook Cover"><i class="fas fa-book text-default mr-2"></i></a>';  
                     }
                     if($row->plan_code==703)
                     {
                        $btn .= '<a class="" href="'.$url2.'" title="Passbook Transaction"><i class="fas fa-print text-default mr-2"></i></a>'; 
                     } 
                     return $btn;
                 })
                 ->rawColumns(['action'])
                 */
                ->make(true);
        }
    }
    public function getMemberFromIdProof(Request $request)
    {
        /*
        if ($request->first_id_type == 3 || $request->first_id_type == 5) {
            $isexist = MemberIdProof::join('members', 'member_id_proofs.member_id', '=', 'members.id')
                // ->where('member_id_proofs.first_id_no', $request->id_proof_no)
                // ->orWhere('member_id_proofs.second_id_no', $request->second_id_proof_no)
                ->where(function($query) use($request){
                    $query->where('member_id_proofs.first_id_no', $request->id_proof_no)
                    ->orWhere('member_id_proofs.second_id_no', $request->second_id_proof_no);
                })
                ->get();
            $counts = $isexist->count();
            if ($counts > 0) {
                $first_id_type = $request->first_id_type;
                $second_id_type = $request->second_id_type;
                if (!empty($first_id_type && empty($second_id_type))) {
                    $first_id_type = 'firstidtypechecked';
                    $second_id_type = '';
                } elseif (!empty($first_id_type) && !empty($second_id_type)) {
                    $first_id_type = 'firstidtypechecked';
                    $second_id_type = 'secondidtypechecked';
                } elseif (!empty($second_id_type) && empty($first_id_type)) {
                    $second_id_type = 'secondidtypechecked';
                    $first_id_type = '';
                } else {
                    $first_id_type = '';
                    $second_id_type = '';
                }
                return response()->json(['first_id_type' => $first_id_type, 'second_id_type' => $second_id_type, 'msg' => 'exists', 'status' => 200]);
            }
        } else {
            return MemberIdProof::join('members', 'member_id_proofs.member_id', '=', 'members.id')->where('member_id_proofs.first_id_no', $request->id_proof_no)->orWhere('member_id_proofs.second_id_no', $request->id_proof_no)->pluck('members.member_id')->first();
        }
        // } 
        // else {
        //     if($request->first_id_type==3 || $request->first_id_type==5){
        //         $isexist = MemberIdProof::join('members', 'member_id_proofs.member_id', '=', 'members.id')->where('member_id_proofs.first_id_no', $request->id_proof_no)->orWhere('member_id_proofs.second_id_no', $request->id_proof_no)->get();
        //         $counts = $isexist->count();
        //         if($counts>0) {
        //             $first_id_type = $request->first_id_type;
        //             $second_id_type = $request->second_id_type;
        //             if(!empty($first_id_type && empty($second_id_type))){
        //                 $first_id_type = 'firstidtypechecked';
        //                 $second_id_type = '';
        //             }
        //             elseif(!empty($first_id_type) && !empty($second_id_type)){
        //                 $first_id_type = 'firstidtypechecked';
        //                 $second_id_type = 'secondidtypechecked';
        //             }
        //             elseif(!empty($second_id_type) && empty($first_id_type)){
        //                 $second_id_type = 'secondidtypechecked';
        //                 $first_id_type = '';
        //             }
        //             else{
        //                 $first_id_type = '';
        //                 $second_id_type = '';
        //             }
        //             return response()->json(['msg' =>'exists','status'=>200]);
        //         } 
        //     }
        //     else 
        //     {
        //         return MemberIdProof::join('members', 'member_id_proofs.member_id', '=', 'members.id')->where('member_id_proofs.first_id_no', $request->id_proof_no)->orWhere('member_id_proofs.second_id_no', $request->id_proof_no)->pluck('members.member_id')->where('is_deleted','0')->first();
        //     }
        // }
        */
        /** this code last modify by sourab on 29-12-2023 for correcting condication of error on id profe case */
        if ($request->first_id_type == 3 || $request->first_id_type == 5 || $request->second_id_type == 3) {
            $isexist = \App\Models\MemberIdProof::join('members', 'member_id_proofs.member_id', '=', 'members.id')
                ->where(function ($query) use ($request) {
                    $query->whereIn('member_id_proofs.first_id_no', [$request->id_proof_no, $request->second_id_proof_no])
                        ->orWhereIn('member_id_proofs.second_id_no', [$request->id_proof_no, $request->second_id_proof_no]);
                })
                ->whereIsDeleted('0')
                ->get(['second_id_no', 'first_id_no']);
            $counts = $isexist->count('id');
            $f = '';
            $s = '';
            if ($counts > 0) {
                $first_id_type = $request->id_proof_no;
                $second_id_type = $request->second_id_proof_no;
                // dd($isexist[0]->first_id_no , $first_id_type , $isexist[0]->second_id_no , $second_id_type);
                if (($isexist[0]->first_id_no == $first_id_type) && ($isexist[0]->second_id_no != $second_id_type)) {
                    $f = 'firstidtypechecked';
                    $s = '';
                } else if (($isexist[0]->first_id_no != $first_id_type) && ($isexist[0]->second_id_no == $second_id_type)) {
                    $f = '';
                    $s = 'secondidtypechecked';
                } else if (($isexist[0]->first_id_no == $first_id_type) && ($isexist[0]->second_id_no == $second_id_type)) {
                    $f = 'firstidtypechecked';
                    $s = 'secondidtypechecked';
                }
            }
            return response()->json(['first_id_type' => $f, 'second_id_type' => $s, 'msg' => 'exists', 'status' => 200]);
        } else {
            return \App\Models\MemberIdProof::join('members', 'member_id_proofs.member_id', '=', 'members.id')
                // ->where('member_id_proofs.first_id_no', $request->id_proof_no)
                // ->orWhere('member_id_proofs.second_id_no', $request->id_proof_no)
                ->where(function ($query) use ($request) {
                    $query->whereIn('member_id_proofs.first_id_no', [$request->id_proof_no, $request->second_id_proof_no])
                        ->orWhereIn('member_id_proofs.second_id_no', [$request->id_proof_no, $request->second_id_proof_no]);
                })
                ->whereIsDeleted('0')
                ->pluck('members.member_id')->first();
        }
    }
    /**
     * Investment payment cheque Listing.
     * Route: /member/passbook
     * Method: get 
     * @return  array()  Response
     */
    public function memberChequePaymentView()
    {
        /*$allInvestments = Memberinvestments::select('id')->where('payment_mode',1)->get();
        foreach ($allInvestments as $key => $investment) {
          Schema::disableForeignKeyConstraints();
          $firstDayBook =  DB::table('member_investments_payments')->where('investment_id',$investment->id)->orderBy('id','ASC')->take(1)->first('id');
          if($firstDayBook && isset($firstDayBook)){
            $dayBooks = DB::table('member_investments_payments')->whereNotIn('id', [$firstDayBook->id])->where('investment_id',$investment->id)->delete();
          }
        }*/
        $data['title'] = 'Member | Payments';
        return view('templates.admin.member.memberchequepayments', $data);
    }
    /**
     * Fetch transaction listing data by member id.
     *@method post call ajax
     * @param  \App\Reservation  $reservation
     * @return \Illuminate\Http\Response
     */
    public function memberChequePaymentListing(Request $request)
    {
        if ($request->ajax()) {
            $arrFormData = array();
            if (!empty($_POST['searchform'])) {
                foreach ($_POST['searchform'] as $frm_data) {
                    $arrFormData[$frm_data['name']] = $frm_data['value'];
                }
            }
            $data = Memberinvestments::with('plan', 'investmentPayment', 'branch')->where('payment_mode', 1);
            /******* fillter query start ****/
            if (isset($arrFormData['is_search']) && $arrFormData['is_search'] == 'yes') {
                if ($arrFormData['branch_id'] != '') {
                    $id = $arrFormData['branch_id'];
                    $data = $data->where('branch_id', '=', $id);
                }
                if ($arrFormData['start_date'] != '') {
                    $startDate = date("Y-m-d", strtotime(convertDate($arrFormData['start_date'])));
                    $data = $data->whereHas('investmentPayment', function ($query) use ($startDate) {
                        $query->where('member_investments_payments.created_at', $startDate);
                    });
                }
                if ($arrFormData['status'] != '') {
                    $status = $arrFormData['status'];
                    $data = $data->whereHas('investmentPayment', function ($query) use ($status) {
                        $query->where('member_investments_payments.status', $status);
                    });
                }
            }
            $data = $data->orderby('id', 'DESC')->get();
            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('s-branch', function ($row) {
                    $branch = $row['branch']->name;
                    return $branch;
                })
                ->rawColumns(['s-branch'])
                ->addColumn('branch_code', function ($row) {
                    $branch_code = $row['branch']->branch_code;
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
                ->addColumn('amount', function ($row) {
                    $amount = $row->deposite_amount;
                    return $amount;
                })
                ->rawColumns(['amount'])
                ->addColumn('transaction_date', function ($row) {
                    foreach ($row['investmentPayment'] as $key => $value) {
                        $transaction_date = date("d/m/Y", strtotime(convertDate($value->created_at)));
                    }
                    return $transaction_date;
                })
                ->rawColumns(['transaction_date'])
                ->addColumn('cheque_date', function ($row) {
                    foreach ($row['investmentPayment'] as $key => $value) {
                        if ($value->cheque_date && isset ($value->cheque_date)) {
                            $cheque_date = date("d/m/Y", strtotime(convertDate($value->cheque_date)));
                        } else {
                            $cheque_date = '';
                        }
                    }
                    return $cheque_date;
                })
                ->rawColumns(['cheque_date'])
                ->addColumn('cheque_number', function ($row) {
                    foreach ($row['investmentPayment'] as $key => $value) {
                        $cheque_number = $value->cheque_number;
                    }
                    return $cheque_number;
                })
                ->rawColumns(['cheque_number'])
                ->addColumn('bank_name', function ($row) {
                    foreach ($row['investmentPayment'] as $key => $value) {
                        $bank_name = $value->branch_name;
                    }
                    return $bank_name;
                })
                ->rawColumns(['bank_name'])
                ->addColumn('branch_name', function ($row) {
                    foreach ($row['investmentPayment'] as $key => $value) {
                        $branch_name = $value->bank_name;
                    }
                    return $branch_name;
                })
                ->rawColumns(['branch_name'])
                ->addColumn('status', function ($row) {
                    foreach ($row['investmentPayment'] as $key => $value) {
                        if ($value->status == 0) {
                            $status = 'Approve';
                        } elseif ($value->status == 1) {
                            $status = 'Unapprove';
                        }
                    }
                    return $status;
                })
                ->rawColumns(['status'])
                ->addColumn('action', function ($row) {
                    foreach ($row['investmentPayment'] as $key => $value) {
                        $url = URL::to("admin/member-payment/cheque/" . $value->id . "");
                        $btn = '<a class="dropdown-item" href="' . $url . '" title="Member Detail"> <i class="far fa-thumbs-up mr-2"></i></a>  ';
                    }
                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
    }
    /**
     * Investment payment cheque Listing.
     * Route: /member/passbook
     * Method: get 
     * @return  array()  Response
     */
    public function memberChequeStatus($id)
    {
        $getStatus = Memberinvestmentspayments::where('id', $id)->first('status');
        $miPayment = Memberinvestmentspayments::find($id);
        if ($getStatus->status == 0) {
            $data['status'] = 1;
        } else {
            $data['status'] = 0;
        }
        $miPayment->update($data);
        return redirect()->route('admin.member.investmentchequepayment')->with('success', 'Status updated successfully!');
    }
    public function memberInterestTds($id, Request $request)
    {
        if (check_my_permission(Auth::user()->id, "241") != "1") {
            return redirect()->route('admin.dashboard');
        }
        $data['branch'] = Branch::where('status', 1)->get();
        $data['plans'] = Plans::all();
        $data['title'] = 'Interest and TDS Deduction';
        $data['id'] = $id;
        if ($request['is_search'] != '') {
            if ($request['is_search'] == 'yes') {
                if ($request['start_date'] != '') {
                    $data['startDate'] = date("Y-m-d", strtotime(convertDate($request['start_date'])));
                    if ($request['end_date'] != '') {
                        $data['endDate'] = date("Y-m-d ", strtotime(convertDate($request['end_date'])));
                    } else {
                        $data['endDate'] = '';
                    }
                } else {
                    $data['startDate'] = '';
                    $data['endDate'] = '';
                }
                if ($request['plan_id'] != '') {
                    $data['planId'] = $request['plan_id'];
                } else {
                    $data['planId'] = '';
                }
                if ($request['branch_id'] != '') {
                    $data['branch_id'] = $request['branch_id'];
                } else {
                    $data['branch_id'] = '';
                }
                $data['is_search'] = 'yes';
            } else {
                $data['startDate'] = '';
                $data['endDate'] = '';
                $data['planId'] = '';
                $data['branch_id'] = '';
                $data['is_search'] = 'no';
            }
        } else {
            $data['startDate'] = '';
            $data['endDate'] = '';
            $data['planId'] = '';
            $data['branch_id'] = '';
            $data['is_search'] = 'no';
        }
        return view('templates.admin.member.member_investment_interest_tds', $data);
    }
    public function memberInterestTdsListing(Request $request)
    {
        if ($request->ajax()) {
            $arrFormData = array();
            if (!empty($_POST['searchform'])) {
                foreach ($_POST['searchform'] as $frm_data) {
                    $arrFormData[$frm_data['name']] = $frm_data['value'];
                }
            }
            $mId = $arrFormData['m_id'];
            $data = MemberInvestmentInterestTds::where('member_id', $mId);
            /******* fillter query start ****/
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
                if ($arrFormData['plan_id'] != '') {
                    $planId = $arrFormData['plan_id'];
                    $data = $data->where('plan_type', '=', $planId);
                }
                if ($arrFormData['branch_id'] != '') {
                    $branch_id = $arrFormData['branch_id'];
                    $data = $data->where('branch_id', '=', $branch_id);
                }
            }
            /******* fillter query End ****/
            $data = $data->orderby('id', 'DESC')->get();
            $count = count($data);
            // $totalCount = MemberInvestmentInterestTds::where('member_id',$mId)->count();
            $totalCount -= $count;
            $sno = $_POST['start'];
            $rowReturn = array();
            foreach ($data as $row) {
                $sno++;
                $val['DT_RowIndex'] = $sno;
                $val['date'] = date("d/m/Y", strtotime(convertDate($row->date_to)));
                $val['plan_name'] = getPlanDetail($row->plan_type)->name;
                $val['account_number'] = getInvestmentDetails($row->investment_id)->account_number;
                $val['interest_amount'] = $row->interest_amount;
                $val['tds_deduction'] = $row->tdsamount_on_interest;
                $val['cr_amount'] = 1;
                $val['dr_amount'] = $row->interest_amount - $row->tdsamount_on_interest;
                $val['balance'] = 1;
                $rowReturn[] = $val;
            }
            $output = array("draw" => $_POST['draw'], "recordsTotal" => $totalCount, "recordsFiltered" => $count, "data" => $rowReturn, );
            return json_encode($output);
        }
    }
    public function memberTdsCertificate($id)
    {
        $data['title'] = 'Member TDS | Certificate';
        $data['id'] = $id;
        return view('templates.admin.member.member_tds_certificate', $data);
    }
    public function printInvestmentTds(Request $request)
    {
        $data['plans'] = Plans::all();
        $data['id'] = $request['m_id'];
        $data['startDate'] = $request['start_date'];
        $data['endDate'] = $request['end_date'];
        $data['planId'] = $request['plan_id'];
        $data['branch_id'] = $request['branch_id'];
        $data['records'] = Member::with('associateInvestment')->where('id', $request['m_id'])->get();
        return view('templates.admin.member.member_tds_print', $data);
    }
    /**
     * Get Customer list
     * Route: ajax call from - /admin/customer
     * Method: Post 
     * @param  \Illuminate\Http\Request  $request
     * @return JSON array
     */
    public function customerindex()
    {
        if (check_my_permission(Auth::user()->id, "200") != "1") {
            return redirect()->route('admin.dashboard');
        }
        $data['title'] = 'Customer | Listing';
        $data['branch'] = Branch::where('status', 1)->get(['id', 'name']);
        return view('templates.admin.member.customerindex', $data);
    }
    /**
     * Get Customer list
     * Route: ajax call from - /admin/Customer
     * Method: Post 
     * @param  \Illuminate\Http\Request  $request
     * @return JSON array
     */
    public function customerListing(Request $request)
    {
        if ($request->ajax() && check_my_permission(Auth::user()->id, "3") == "1") {
            // fillter array 
            $arrFormData = array();
            if (!empty($_POST['searchform'])) {
                foreach ($_POST['searchform'] as $frm_data) {
                    $arrFormData[$frm_data['name']] = $frm_data['value'];
                }
            }
            if (isset($arrFormData['is_search']) && $arrFormData['is_search'] == 'yes') {
                /*
                $data =Member::with(['branch'=>function($q){
                    $q->select(['id','name','zone','sector','branch_code','regan']);
                }])
                ->with(['children'=>function($q){
                    $q->select(['id','first_name','last_name']);
                }])
                ->with(['states' =>function($query) {
                    $query->select('id','name');
                }])
                ->with(['city' => function($q){ $q->select(['id','name']);
                }])
                ->with(['district' => function($q){$q->select(['id','name']);
                }])
                ->with(['memberIdProof'=>function($q){
                    $q->with(['idTypeFirst'=>function($q){
                        $q->select(['id','name']);
                    }])->with(['idTypeSecond'=>function($q){
                        $q->select(['id','name']);
                    }]);
                    }])
                ->with(['memberNomineeDetails' => function($q){
                        $q->with(['nomineeRelationDetails'=>function($q){
                        $q->select('id','name');
                    }]);
                }]) 
                ->where('member_id','!=','9999999')->where('is_deleted',0);
                if(Auth::user()->branch_id>0){
                    $id=Auth::user()->branch_id;
                    $data=$data->where('branch_id','=',$id);
                }
                */
                $data = Member::where('member_id', '!=', '0CI09999999')
                    ->with([
                        'branch:id,name,zone,sector,branch_code,regan',
                        'children:id,first_name,last_name',
                        'states:id,name',
                        'city:id,name',
                        'district:id,name',
                        'memberIdProof',
                        'memberIdProof.idTypeFirst:id,name',
                        'memberIdProof.idTypeSecond:id,name',
                        'memberNomineeDetails',
                        'memberNomineeDetails.nomineeRelationDetails:id,name',
                    ])
                    ->where('is_deleted', 0);
                /******* fillter query start ****/
                if ($arrFormData['customer_id'] != '') {
                    $customer_id = $arrFormData['customer_id'];
                    $data = $data->where('member_id', 'LIKE', '%' . $customer_id . '%');
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
                $count = $data->orderby('id', 'DESC')->count('id');
                $data_export = $data->orderby('id', 'DESC')->get();
                $data = $data->orderby('id', 'DESC')->offset($_POST['start'])->limit($_POST['length'])->get();
                // $dataCount = Member::where('member_id','!=','9999999');
                // if(Auth::user()->branch_id>0){
                // $dataCount=$dataCount->where('branch_id','=',Auth::user()->branch_id);
                // }
                // $totalCount =$dataCount->count('id');
                $totalCount = $count;
                $sno = $_POST['start'];
                $rowReturn = array();
                foreach ($data as $row) {
                    $sno++;
                    $NomineeDetail = $row['memberNomineeDetails'];
                    $val['DT_RowIndex'] = $sno;
                    $val['join_date'] = date("d/m/Y", strtotime($row->re_date));
                    $val['branch'] = $row['branch']->name;
                    // $val['branch_code']=$row['branch']->branch_code;
                    // $val['sector_name']=$row['branch']->sector;
                    // $val['region_name']=$row['branch']->sector;
                    // $val['zone_name']=$row['branch']->zone;
                    $val['customer_id'] = isset($row->member_id) ? $row->member_id : '';
                    // $val['member_id']=$row->member_id;
                    $val['name'] = $row->first_name . ' ' . $row->last_name;
                    $val['dob'] = date('d/m/Y', strtotime($row->dob));
                    if ($row->gender == 1) {
                        $val['gender'] = "Male";
                    } else {
                        $val['gender'] = "Female";
                    }
                    $val['nominee_name'] = $NomineeDetail->name;
                    if ($row->id) {
                        $relation_id = $NomineeDetail->relation;
                        if ($relation_id) {
                            $val['relation'] = $NomineeDetail['nomineeRelationDetails']->name;
                        }
                    }
                    $val['nominee_age'] = $NomineeDetail->age;
                    if ($NomineeDetail->gender == 1) {
                        $val['nominee_gender'] = 'Male';
                    } else {
                        $val['nominee_gender'] = 'Female';
                    }
                    $val['email'] = $row->email;
                    $val['mobile_no'] = $row->mobile_no;
                    $val['associate_code'] = $row->associate_code;
                    $val['associate_name'] = $row['children']->first_name . ' ' . $row['children']->last_name;
                    if ($row->is_block == 1) {
                        $status = 'Blocked';
                    } else {
                        if ($row->status == 1) {
                            $status = 'Active';
                        } else {
                            $status = 'Inactive';
                        }
                    }
                    $val['status'] = $status;
                    $is_upload = 'Yes';
                    if ($row->signature == '') {
                        $is_upload = 'No';
                    }
                    if ($row->photo == '') {
                        $is_upload = 'No';
                    }
                    $val['is_upload'] = $is_upload;
                    $val['firstId'] = $row['memberIdProof']['idTypeFirst']->name . ' - ' . $row['memberIdProof']->first_id_no;
                    $val['secondId'] = $row['memberIdProof']['idTypeSecond']->name . ' - ' . $row['memberIdProof']->second_id_no;
                    $val['address'] = preg_replace("/\r|\n/", "", $row->address);
                    $val['state'] = $row['states'] ? $row['states']->name : 'N/A';
                    $val['district'] = $row['district'] ? $row['district']->name : 'N/A';
                    $val['city'] = $row['city'] ? $row['city']->name : 'N/A';
                    $val['village'] = $row->village;
                    $val['pin_code'] = $row->pin_code;
                    $btn = '<div class="list-icons"><div class="dropdown"><a href="#" class="list-icons-item" data-toggle="dropdown"><i class="icon-menu9"></i></a><div class="dropdown-menu dropdown-menu-right">';
                    $url = URL::to("admin/member-detail/" . $row->id . "?type=1");
                    $urledit = URL::to("admin/member-edit/" . $row->id . "?type=1");
                    $urlform15 = URL::to("admin/form_g/" . $row->id);
                    $url1 = URL::to("admin/member-saving/" . $row->id . "");
                    $url2 = URL::to("admin/member-loan/" . $row->id . "");
                    $url3 = URL::to("admin/member-investment/" . $row->id . "");
                    $url5 = URL::to("admin/member-transactions/" . $row->id . "");
                    $btn .= '<a class="dropdown-item" href="' . $url . '" title="Member Detail"><i class="icon-eye-blocked2  mr-2"></i>Detail</a>  ';
                    $btn .= '<a class="dropdown-item" href="' . $urledit . '" title="Member Edit"><i class="icon-pencil5  mr-2"></i>Edit</a>  ';
                    // $btn .= '<a class="dropdown-item" target="_blank"  href="'.$urlform15.'" title="Form15G Edit"><i class="icon-book  mr-2"></i>Form 15G</a>  ';
                    $btn .= '<a class="dropdown-item" href="' . $url1 . '" title="Saving Account"><i class="icon-box  mr-2"></i>SSB Account</a>  ';
                    if (check_my_permission(Auth::user()->id, "240") == "1") {
                        $btn .= '<a class="dropdown-item" href="' . $url2 . '" title="Loans"><i class="icon-snowflake mr-2"></i>Loans</a>  ';
                    }
                    if (check_my_permission(Auth::user()->id, "239") == "1") {
                        $btn .= '<a class="dropdown-item" href="' . $url3 . '" title="Investment"><i class="icon-pulse2 mr-2"></i>Investment</a>  ';
                    }
                    if (check_my_permission(Auth::user()->id, "242") == "1") {
                        // $btn .= '<a class="dropdown-item" href="'.$url5.'" title="Transaction"><i class="fas fa-print mr-2"></i>Transaction</a>  '; 
                    }
                    $btn .= '</div></div></div>';
                    $val['action'] = $btn;
                    $rowReturn[] = $val;
                }
                Cache::put('member_exportlist', $data_export);
                Cache::put('member_export_count', $count);
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
    public function saving($id)
    {
        if (check_my_permission(Auth::user()->id, "239") != "1") {
            return redirect()->route('admin.dashboard');
        }
        $data['title'] = 'Customer | Saving Account';
        $data['memberDetail'] = Member::where('id', $id)->first(['id', 'member_id', 'first_name', 'last_name']);
        return view('templates.admin.member.member_saving', $data);
    }
    public function membersSaving(Request $request)
    {
        if ($request->ajax()) {
            $memberId = $request['member_id'];
            $data = \App\Models\SavingAccount::has('company')->with(['getMemberinvestments:plan_id,id,form_number', 'savingBranch:id,name,branch_code', 'getMemberinvestments.plan:id,name', 'customerSSB:member_id,id,first_name,last_name'])->where('customer_id', $memberId)->orderBy('id', 'DESC')->get();
            // pd($data);
            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('company_name', function ($row) {
                    $company_name = $row['company']->name;
                    return $company_name;
                })
                ->rawColumns(['company_name'])
                ->addColumn('customer_id', function ($row) {
                    $customer_id = $row['customerSSB']->member_id;
                    return $customer_id;
                })
                ->rawColumns(['customer_id'])
                ->addColumn('member_id', function ($row) {
                    $member_id = $row['ssbMemberCustomer']->member_id;
                    return $member_id;
                })
                ->rawColumns(['member_id'])
                ->addColumn('account_no', function ($row) {
                    return $row->account_no;
                })
                ->rawColumns(['account_no'])
                ->addColumn('member_name', function ($row) {
                    $member_name = $row['customerSSB']['first_name'] . ' ' . $row['customerSSB']['last_name'];
                    return $member_name;
                })
                ->rawColumns(['member_name'])
                ->addColumn('balance', function ($row) {
                    $balance = $row->getSSBAccountBalance->totalBalance ?? '0';
                    return $balance;
                })
                ->rawColumns(['balance'])
                ->addColumn('action', function ($row) {
                    $url1 = URL::to("admin/member-account/" . $row->id . "");
                    $btn = '<a class="dropdown-item" href="' . $url1 . '" title="Detail"><i class="icon-eye  mr-2"></i></a>';
                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
    }
}