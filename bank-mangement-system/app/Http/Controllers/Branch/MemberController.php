<?php
namespace App\Http\Controllers\Branch;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use App\Models\Member;
use App\Models\Receipt;
use App\Models\ReceiptAmount;
use App\Models\MemberCompany;
use Session;
use URL;
use DB;
use App\Services\ImageUpload;
use App\Services\Sms;
use App\Http\Traits\IsLoanTrait;

/*
    |---------------------------------------------------------------------------
    | Branch Panel -- Member Management MemberController
    |--------------------------------------------------------------------------
    |
    | This controller handles members all functionlity.
*/
class MemberController extends Controller
{
    use IsLoanTrait;
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
     * Show  particular branch members list.
     * Route: /branch/member 
     * Method: get 
     * @return  array()  Response
     */
    public function index()
    {
        if (!in_array('Member View', auth()->user()->getPermissionNames()->toArray())) {

            return redirect()

                ->route('branch.dashboard');

        }
        $data['title'] = 'Member | Listing';
        return view('templates.branch.member_management.index', $data);
    }


    /**
     * Get members list according to branch.
     * Route: ajax call from - /branch/member
     * Method: Post 
     * @param  \Illuminate\Http\Request  $request
     * @return JSON array
     */
    public function membersListing(Request $request)
    {

        if ($request->ajax()) {
            $arrFormData = array();
            if (!empty($_POST['searchform'])) {
                foreach ($_POST['searchform'] as $frm_data) {
                    $arrFormData[$frm_data['name']] = $frm_data['value'];
                }
            }

            //get login user branch id(branch manager)pass auth id
            $getBranchId = getUserBranchId(Auth::user()->id);
            $branch_id = $getBranchId->id;
            if (isset($arrFormData['is_search']) && $arrFormData['is_search'] == 'yes') {
                $data = MemberCompany::has('company')->with([
                    'branch' => function ($q) {
                        $q->select('id', 'name', 'branch_code', 'sector', 'zone');
                    }
                ])
                    ->with([
                        'memberAssociate' => function ($q) {
                            $q->select('id', 'first_name', 'last_name', 'associate_no');
                        }
                    ])
                    ->with([
                        'member' => function ($q) {
                            $q->select('id', 'first_name', 'last_name', 'dob', 'email', 'mobile_no', 'status', 'signature', 'photo', 'village', 'pin_code', 'state_id', 'district_id', 'city_id', 'branch_id', 'address', 'gender', 'member_id', 'reinvest_old_account_number', 'company_id')
                                ->with([
                                    'states' => function ($q) {
                                        $q->select('id', 'name');
                                    }
                                ])
                                ->with([
                                    'city' => function ($q) {
                                        $q->select('id', 'name');
                                    }
                                ])
                                ->with([
                                    'district' => function ($q) {
                                        $q->select('id', 'name');
                                    }
                                ])
                                ->with([
                                    'memberIdProof' => function ($q) {
                                        $q->select('id', 'first_id_no', 'second_id_no', 'member_id', 'first_id_type_id', 'second_id_type_id')
                                            ->with([
                                                'idTypeFirst' => function ($q) {
                                                    $q->select(['id', 'name']);
                                                }
                                            ])
                                            ->with([
                                                'idTypeSecond' => function ($q) {
                                                    $q->select(['id', 'name']);
                                                }
                                            ]);
                                    }
                                ])
                                ->with([
                                    'memberNomineeDetails' => function ($q) {
                                        $q->select('id', 'name', 'relation', 'age', 'member_id', 'gender')->with([
                                            'nomineeRelationDetails' => function ($q) {
                                                $q->select('id', 'name');
                                            }
                                        ]);
                                    }
                                ])
                                ->with([
                                    'savingAccount_Custom' => function ($q) {
                                        $q->select('id', 'account_no', 'member_id');
                                    }
                                ]);
                        }
                    ])
                    ->where('member_id', '!=', '9999999')->where('branch_id', $branch_id)->where('role_id', 5)->where('is_deleted', 0);


                if ($arrFormData['company_id'] != '') {
                    $company_id = $arrFormData['company_id'];
                    if ($company_id != '0') {
                        $data = $data = $data->where('company_id', '=', $company_id);
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



                $count = $data->orderby('id', 'DESC')->count('id');

                $data = $data->orderby('id', 'DESC')->offset($_POST['start'])->limit($_POST['length'])->get(['id', 're_date', 'member_id', 'company_id', 'associate_id', 'is_block', 'customer_id', 'branch_id', 'created_at', 'status']);


                // $totalCount = MemberCompany::where('member_id','!=','9999999')->where('branch_id',$branch_id)->where('role_id',5)->where('is_deleted',0)->count('id');
                $totalCount = $count;
                $sno = $_POST['start'];
                $rowReturn = array();
                $authDetail = auth()->user()->getPermissionNames()->toArray();

                foreach ($data as $row) {
                    $sno++;
                    $NomineeDetail = $row['member']['memberNomineeDetails'];
                    $val['DT_RowIndex'] = $sno;
                    $val['join_date'] = date("d/m/Y", strtotime($row->re_date));
                    $val['branch'] = $row['branch']->name;
                    // $val['branch_code']=$row['branch']->branch_code;
                    // $val['sector_name']=$row['branch']->sector;
                    // $val['region_name']=$row['branch']->sector;
                    // $val['zone_name']=$row['branch']->zone;
                    $val['customer_id'] = $row['member']->member_id;
                    $val['member_id'] = $row->member_id;
                    $val['name'] = $row['member']->first_name . ' ' . $row['member']->last_name;
                    $val['dob'] = date('d/m/Y', strtotime($row['member']->dob));
                    if ($row['member']->gender == 1) {
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
                    $val['email'] = $row['member']->email;
                    $val['mobile_no'] = $row['member']->mobile_no;
                    $val['associate_code'] = $row['memberAssociate'] ? $row['memberAssociate']->associate_no : 'N/A';
                    $val['associate_name'] = $row['memberAssociate'] ? $row['memberAssociate']->first_name . ' ' . $row['memberAssociate']->last_name ?? '' : 'N/A';
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
                    if ($row['member']->signature == '') {
                        $is_upload = 'No';
                    }
                    if ($row['member']->photo == '') {
                        $is_upload = 'No';
                    }
                    $val['is_upload'] = $is_upload;


                    $val['firstId'] = $row['member']['memberIdProof']['idTypeFirst']['name'] . ' - ' . $row['member']['memberIdProof']['first_id_no'];
                    $val['secondId'] = $row['member']['memberIdProof']['idTypeFirst']['name'] . ' - ' . $row['member']['memberIdProof']['second_id_no'];
                    $val['address'] = preg_replace("/\r|\n/", "", $row['member']->address);
                    $val['state'] = $row['member']['states']->name;
                    $val['district'] = $row['member']['district']->name;
                    $val['city'] = $row['member']['city']->name;
                    $val['village'] = $row['member']->village;
                    $val['pin_code'] = $row['member']->pin_code;
                    $btn = '';
                    $url = URL::to("branch/member/detail/" . $row->customer_id . "?type=0");
                    $url1 = URL::to("branch/member/account/" . $row->id . "");
                    $url2 = URL::to("branch/member/loan/" . $row->id . "");
                    $url3 = URL::to("branch/member/investment/" . $row->id . "");
                    $url4 = URL::to("branch/member/transactions/" . $row->id . "");
                    $url5 = URL::to("branch/form_g/" . $row->id . "");
                    if ($row['member']['memberIdProof']['first_id_type_id'] == 5 || $row['member']['memberIdProof']['second_id_type_id'] == 5) {
                        $btn .= '<a class=" " target="_blank" href="' . $url5 . '" title="Update 15G/15H"><i class="fas fa-book text-default mr-2"></i></a>  ';
                    }
                    if (in_array('Member Profile View', $authDetail)) {
                        $btn .= '<a class=" " href="' . $url . '" title="Member Detail"><i class="fas fa-eye text-default mr-2"></i></a>  ';
                    }
                    if ($row->is_block == 0) {

                        if (in_array('View Saving Account', $authDetail)) {
                            //   $btn .= '<a class=" " href="'.$url1.'" title="Saving Account"><i class="ni ni-spaceship text-default mr-2"></i></a>  ';
                        }

                        if (in_array('View Loan List', $authDetail)) {
                            //  $btn .= '<a class=" " href="'.$url2.'" title="Loans"><i class="ni ni-atom text-default mr-2"></i></a>  ';   
                        }

                        if (in_array('View Member Investment List', $authDetail)) {
                            //  $btn .= '<a class=" " href="'.$url3.'" title="Investment"><i class="ni ni-chart-bar-32 text-default mr-2"></i></a>  ';
                        }

                        if (in_array('View Member Transactions', $authDetail)) {
                            //  $btn .= '<a class=" " href="'.$url4.'" title="Transactions"><i class="fas fa-print text-default mr-2"></i></a>  ';
                        }
                        /*if($correctionStatus == '0'){
                            $btn .= '<a data-id="'.$row->id.'" href="javascript:void(0);" class="m-correction" data-correction-status="'.$correctionStatus.'" title="Correction"><i class="fa fa-check-circle mr-2"></i></a>  ';
                        }else{
                            $btn .= '<a  data-toggle="modal" data-target="#correction-form" data-id="'.$row->id.'" href="javascript:void(0);" class="m-correction" data-correction-status="'.$correctionStatus.'" title="Correction"><i class="fa fa-check-circle mr-2"></i></a>  ';
                        }*/
                    }

                    $val['action'] = $btn;
                    $rowReturn[] = $val;
                }
                $output = array("draw" => $_POST['draw'], "recordsTotal" => $totalCount, "recordsFiltered" => $count, "data" => $rowReturn, );

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
     * Show  member registration.
     * Route: /branch/member/registration 
     * Method: get 
     * @return  array()  Response
     */
    public function register()
    {
        die();
        if (!in_array('Member Create', auth()->user()->getPermissionNames()->toArray())) {

            return redirect()

                ->route('branch.dashboard');

        }
        $data['title'] = 'Member | Registration';
        $data['state'] = stateList();
        $data['occupation'] = occupationList();
        $data['religion'] = religionList();
        $data['specialCategory'] = specialCategoryList();
        $data['idTypes'] = idTypeList();
        $data['relations'] = relationsList();

        return view('templates.branch.member_management.add', $data);

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
        $resCount = checkMemberEmail($request->email, 5);
        $return_array = compact('resCount');
        return json_encode($return_array);
    }
    /**
     * member form number exists or not .
     * Route: ajax call from - /branch/member/registration
     * Method: Post 
     * @param  \Illuminate\Http\Request  $request
     * @return JSON array
     */
    public function memberFormNoCheck(Request $request)
    {
        // pass email form no
        $resCount = checkMemberFormNo($request->form_no, 'form_no');
        $return_array = compact('resCount');
        return json_encode($return_array);
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
            //get login user branch id(branch manager)pass auth id
            $getBranchId = getUserBranchId(Auth::user()->id);
            // pass fa id 1 for member
            $faCode = "CI";
            $branch_id = $getBranchId->id;
            // pass role_id(5 for member),branch_id,fa_code
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
            //dd( $getmemberID);
            $branchMi = $branchCode . $miCode;
            $data = $this->getData($request->all(), 'create');
            $data['role_id'] = 5;
            $data['branch_id'] = $branch_id;
            $data['branch_code'] = $branchCode;
            $data['branch_mi'] = $branchMi;
            $data['member_id'] = $getmemberID;
            $data['mi_code'] = $miCode;
            $data['fa_code'] = $faCode;
            $data['created_at'] = $request['created_at'];
            //  print_r($data);
            $member = Member::create($data);
            $memberId = $member->id;
            $customerid = $member->member_id;
            $signature_filename = '';
            $photo_filename = '';
            if ($request->hasFile('signature')) {
                $signature_image = $request->file('signature');
                $signature_filename = $memberId . '_' . time() . '.' . $signature_image->getClientOriginalExtension();
                $signature_location = 'asset/profile/member_signature/' . $signature_filename;
                $mainFolderSignature = 'profile/member_signature/';
                ImageUpload::upload($signature_image, $mainFolderSignature, $signature_filename);
            }
            if ($request->hasFile('photo')) {
                $photo_image = $request->file('photo');
                $photo_filename = $memberId . '_' . time() . '.' . $photo_image->getClientOriginalExtension();
                $photo_location = 'asset/profile/member_avatar/' . $photo_filename;
                $mainFolderPhoto = '/profile/member_avatar/';
                ImageUpload::upload($photo_image, $mainFolderPhoto, $photo_filename);
            }
            $memberUpdate = Member::find($memberId);
            $memberUpdate->signature = $signature_filename;
            $memberUpdate->photo = $photo_filename;
            $memberUpdate->save();
            // Save bank Information
            if ($request['bank_name'] != '' || $request['bank_branch_name'] != '' || $request['bank_account_no'] != '' || $request['bank_ifsc'] != '' || $request['bank_branch_address'] != '') {
                $dataBank = $this->getBankData($request->all(), 'create', $memberId);
                $bankInfoSave = \App\Models\MemberBankDetail::create($dataBank);
            }
            // Save nominee information
            $dataNominee = $this->getNomineeData($request->all(), 'create', $memberId);
            $nomineeInfoSave = \App\Models\MemberNominee::create($dataNominee);
            // Save Id proofs
            $dataIdProof = $this->getIdProofData($request->all(), 'create', $memberId);
            $idProofInfoSave = \App\Models\MemberIdProof::create($dataIdProof);

            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('alert', $ex->getMessage());
        }
        $contactNumber = array();
        $contactNumber[] = $data['mobile_no'];
        $text = "Dear " . $data['first_name'] . ' ' . $data['last_name'] . ', ';
        $text .= "Welcome to S.B. Micro Finance association Your MI " . $getmemberID . ", Thank you so much for allowing us to help you with your recent account opening. Have a good day. Toll-free No18001216567 https://play.google.com/store/apps/details?id=com.samraddhbestwin.microfinance";
        $numberWithMessage = array();
        $numberWithMessage['contactNumber'] = $contactNumber;
        $numberWithMessage['message'] = $text;
        $templateId = 1207162695930964085;

        /*$adminEmail = new Email();
           $adminEmail->sendEmail( env('ADMIN_EMAIL','micro@mailinator.com'), $member);*/
        $sendToMember = new Sms();
        $sendToMember->sendSms($contactNumber, $text, $templateId);

        if ($memberId) {
            $msg = 'Customer Id :' . $customerid;
            return redirect()->route('branch.member_list')->with('success', 'Member registered successfully! ' . $msg);
        } else {
            return back()->with('alert', 'Member not created.Try again');
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
            $data['form_no'] = $request['form_no'];
            $data['re_date'] = date("Y-m-d", strtotime(str_replace('/', '-', $request['application_date'])));
        }

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
        $data['anniversary_date'] = ($request['marital_status'] == '1') ? date("Y-m-d", strtotime(convertDate($request['anniversary_date']))) : null;
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
        $data['created_at'] = $request['created_at'];
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
        }
        $data['bank_name'] = $request['bank_name'];
        $data['branch_name'] = $request['bank_branch_name'];
        $data['account_no'] = $request['bank_account_no'];
        $data['ifsc_code'] = $request['bank_ifsc'];
        $data['address'] = preg_replace("/\r|\n/", "", $request['bank_branch_address']);
        $data['created_at'] = $request['created_at'];
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
        $data['created_at'] = $request['created_at'];
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
        }
        $data['first_id_type_id'] = $request['first_id_type'];
        $data['first_id_no'] = $request['first_id_proof_no'];
        $data['first_address'] = preg_replace("/\r|\n/", "", $request['first_address_proof']);
        $data['second_id_type_id'] = $request['second_id_type'];
        $data['second_id_no'] = $request['second_id_proof_no'];
        $data['second_address'] = preg_replace("/\r|\n/", "", $request['second_address_proof']);
        $data['created_at'] = $request['created_at'];
        return $data;
    }
    /**
     * Show recipt detail after create member
     * Route: /branch/member/recipt/ 
     * Method: get 
     * @param  $id,$msg
     * @return  array()  Response
     */
    public function memberRecipt($id)
    {
        $data['title'] = 'Member | Recipt';
        $data['type'] = '1';
        $data['recipt'] = Receipt::with([
            'memberReceipt' => function ($query) {
                $query->select('id', 'member_id', 'first_name', 'last_name', 'mobile_no', 'address', 'form_no');
            }
        ])->with([
                    'branchReceipt' => function ($query) {
                        $query->select('id', 'name', 'branch_code');
                    }
                ])->where('id', $id)->first();
        $data['recipt_amount'] = ReceiptAmount::where('receipt_id', $id)->get(['receipt_id', 'amount', 'type_label']);

        return view('templates.branch.member_management.recipt', $data);
    }

    /**
     * Show Member detail 
     * Route: /branch/member/detail/ 
     * Method: get 
     * @param  $id,$msg
     * @return  array()  Response
     */
    public function memberDetail($id)
    {
        $data['title'] = 'Member | Detail';
        $data['membercompany'] = '';
        if ($_GET['type'] == 0) {
            $data['membercompany'] = \App\Models\MemberCompany::with([
                'memberAssociate' => function ($q) {
                    $q->select('id', 'first_name', 'last_name', 'associate_no', 'member_id');
                }
            ])->where('customer_id', $id)->first();
        }
        $data['memberDetail'] = Member::with([
            'children' => function ($q) {
                $q->select(['id', 'first_name', 'last_name', 'associate_no', 'member_id']);
            }
        ])->where('id', $id)->first();
        $data['bankDetail'] = \App\Models\MemberBankDetail::where('member_id', $id)->first();
        $data['nomineeDetail'] = \App\Models\MemberNominee::where('member_id', $id)->first();
        $data['idProofDetail'] = \App\Models\MemberIdProof::where('member_id', $id)->first();

        $recipt = Receipt::where('member_id', $id)->where('receipts_for', 1)->first('id');
        $data['accountDetail'] = \App\Models\SavingAccount::where('customer_id', $id)->first();
        return view('templates.branch.member_management.detail', $data);
    }

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
            $mainFolderPhoto = 'profile/member_avatar/';
            $return = ImageUpload::upload($photo_image, $mainFolderPhoto, $photo_filename);
            // $return = Image::make($photo_image)->resize(300,300)->save($photo_location);
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
        $memberUpdate->save();
        return back()->with($action, $message);
    }

    public function getMemberFromIdProof(Request $request)
    {
        /*
        if ($request->first_id_type == 3 || $request->first_id_type == 5) {
            $isexist = \App\Models\MemberIdProof::join('members', 'member_id_proofs.member_id', '=', 'members.id')
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
            return \App\Models\MemberIdProof::join('members', 'member_id_proofs.member_id', '=', 'members.id')
                ->where('member_id_proofs.first_id_no', $request->id_proof_no)
                ->orWhere('member_id_proofs.second_id_no', $request->id_proof_no)
                ->pluck('members.member_id')
                ->first();
        }
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



    public function customerindex()
    {
        if (!in_array('Member View', auth()->user()->getPermissionNames()->toArray())) {

            return redirect()

                ->route('branch.dashboard');

        }
        $data['title'] = 'Customer | Listing';
        return view('templates.branch.member_management.customer_index', $data);
    }


    public function customerListing(Request $request)
    {

        if ($request->ajax()) {
            $arrFormData = array();
            if (!empty($_POST['searchform'])) {
                foreach ($_POST['searchform'] as $frm_data) {
                    $arrFormData[$frm_data['name']] = $frm_data['value'];
                }
            }

            //get login user branch id(branch manager)pass auth id
            $getBranchId = getUserBranchId(Auth::user()->id);
            $branch_id = $getBranchId->id;
            $data = Member::with([
                'branch' => function ($q) {
                    $q->select(['id', 'name', 'zone', 'sector', 'branch_code', 'regan']);
                }
            ])
                ->with([
                    'children' => function ($q) {
                        $q->select(['id', 'first_name', 'last_name']);
                    }
                ])
                ->with([
                    'states' => function ($query) {
                        $query->select('id', 'name');
                    }
                ])
                ->with([
                    'city' => function ($q) {
                        $q->select(['id', 'name']);
                    }
                ])
                ->with([
                    'district' => function ($q) {
                        $q->select(['id', 'name']);
                    }
                ])
                ->with([
                    'memberIdProof' => function ($q) {
                        $q->with([
                            'idTypeFirst' => function ($q) {
                                $q->select(['id', 'name']);
                            }
                        ])->with([
                                    'idTypeSecond' => function ($q) {
                                        $q->select(['id', 'name']);
                                    }
                                ]);
                    }
                ])
                ->with([
                    'memberNomineeDetails' => function ($q) {
                        $q->with([
                            'nomineeRelationDetails' => function ($q) {
                                $q->select('id', 'name');
                            }
                        ]);
                    }
                ])
                ->where('member_id', '!=', '9999999')->where('branch_id', $branch_id)->where('role_id', 5)->where('is_deleted', 0);

            // dd($data[0]['memberCompany']);   

            if (isset($arrFormData['is_search']) && $arrFormData['is_search'] == 'yes') {



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



                $count = $data->orderby('id', 'DESC')->count('id');
                // $count=count($data1);

                $data = $data->orderby('id', 'DESC')->offset($_POST['start'])->limit($_POST['length'])->get([
                    'id',
                    'dob',
                    're_date',
                    'member_id',
                    'first_name',
                    'last_name',
                    'mobile_no',
                    'email',
                    'associate_code',
                    'associate_id',
                    'status',
                    'signature',
                    'photo',
                    'address',
                    'state_id',
                    'district_id',
                    'city_id',
                    'village',
                    'pin_code',
                    'is_block',
                    'branch_id',
                    'gender'
                ]);


                // $totalCount = Member::where('member_id','!=','9999999')->where('branch_id',$branch_id)->where('role_id',5)->where('is_deleted',0)->count('id');
                $totalCount = $count;
                $sno = $_POST['start'];
                $rowReturn = array();
                $authDetail = auth()->user()->getPermissionNames()->toArray();

                foreach ($data as $row) {


                    $sno++;
                    $NomineeDetail = $row['memberNomineeDetails'];
                    $val['DT_RowIndex'] = $sno;
                    $val['join_date'] = date("d/m/Y", strtotime($row->re_date));
                    $val['branch'] = $row['branch']->name;
                    $val['branch_code'] = $row['branch']->branch_code;
                    $val['sector_name'] = $row['branch']->sector;
                    $val['region_name'] = $row['branch']->sector;
                    $val['zone_name'] = $row['branch']->zone;
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

                    $url = URL::to("branch/member/detail/" . $row->id . "?type=1");
                    $url1 = URL::to("branch/member/saving/" . $row->id . "");
                    $url2 = URL::to("branch/member/loan/" . $row->id . "");
                    $url3 = URL::to("branch/member/investment/" . $row->id . "");
                    if (in_array('Member Profile View', $authDetail)) {
                        $btn = '<a class=" " href="' . $url . '" title="Member Detail"><i class="fas fa-eye text-default mr-2"></i></a>  ';
                    } else {
                        $btn = '';
                    }

                    if ($row->is_block == 0) {
                        if (in_array('View Saving Account', $authDetail)) {
                            $btn .= '<a class=" " href="' . $url1 . '" title="Saving Account"><i class="ni ni-spaceship text-default mr-2"></i></a>  ';
                        }

                        if (in_array('View Loan List', $authDetail)) {
                            $btn .= '<a class=" " href="' . $url2 . '" title="Loans"><i class="ni ni-atom text-default mr-2"></i></a>  ';
                        }

                        if (in_array('View Member Investment List', $authDetail)) {
                            $btn .= '<a class=" " href="' . $url3 . '" title="Investment"><i class="ni ni-chart-bar-32 text-default mr-2"></i></a>  ';
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

}
