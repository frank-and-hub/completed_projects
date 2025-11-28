<?php

namespace App\Http\Controllers\Branch;

use App\Models\SavingAccount;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use App\Http\Requests;
use Illuminate\Support\Facades\Response;
use Validator;
use App\Models\Member;
use App\Models\CommissionLeaserMonthly;
use App\Models\AssociateMonthlyCommission;
use App\Models\Receipt;
use App\Models\Memberinvestments;
use App\Models\ReceiptAmount;
use App\Models\Plans;
use App\Models\Loans;
use App\Http\Controllers\Branch\CommanTransactionsController;
use Carbon\Carbon;
use Yajra\DataTables\DataTables;
use Illuminate\Http\Request;
use Session;
use Image;
use Redirect;
use URL;
use DB;
use App\Services\Sms;
use App\Models\Branch;
use App\Models\AssociateCommission;
use App\Models\BusinessTarget;
use App\Models\SamraddhBank;

/*
|---------------------------------------------------------------------------
| Branch Panel -- Associate Management MemberAssociateController
|--------------------------------------------------------------------------
|
| This controller handles associate all functionlity.
*/

class MemberAssociateController extends Controller
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
     * Show  particular branch members list.
     * Route: /branch/associate 
     * Method: get 
     * @return  array()  Response
     */
    public function index()
    {

        if (!in_array('Associate Details', auth()->user()->getPermissionNames()->toArray())) {
            return redirect()->route('branch.dashboard');
        }

        $data['title'] = 'Associate Management | Listing';


        return view('templates.branch.associate_management.index', $data);
    }
    /**
     * Get Accociate list according to branch.
     * Route: ajax call from - /branch/associate
     * Method: Post 
     * @param  \Illuminate\Http\Request  $request
     * @return JSON array
     */
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

            $getBranchId = getUserBranchId(Auth::user()->id);
            $auth = auth()->user()->getPermissionNames()->toArray();
            $branch_id = $getBranchId->id;
            $data = Member::select(['id', 'associate_branch_id', 'member_id', 'associate_no', 'first_name', 'last_name', 'dob', 'associate_join_date', 'mobile_no', 'email', 'associate_senior_id', 'associate_senior_code', 'associate_status', 'photo', 'signature', 'address', 'state_id', 'district_id', 'city_id', 'village', 'pin_code'])->with(['associate_branch'])
                ->with([
                    'seniorData' => function ($q) {
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
                        $q->with([
                            'nomineeRelationDetails' => function ($q) {
                                $q->select('id', 'name');
                            }
                        ]);
                    }
                ])
                // ->where('member_id', '!=', '9999999')
                ->where('member_id', '!=', '0CI09999999')
                ->where('associate_branch_id', $branch_id)
                ->where('is_associate', 1)
                ->where('is_deleted', 0);

            /******* fillter query start ****/
            if (isset($arrFormData['is_search']) && $arrFormData['is_search'] == 'yes') {
                if ($arrFormData['sassociate_code'] != '') {
                    $associate_code = $arrFormData['sassociate_code'];
                    $data = $data->where('associate_senior_code', '=', $associate_code);
                }

                if ($arrFormData['associate_code'] != '') {
                    $meid = $arrFormData['associate_code'];
                    $data = $data->where('associate_no', '=', $meid);
                }
                if ($arrFormData['customer_id'] != '') {
                    $customer_id = $arrFormData['customer_id'];
                    $data = $data->where('member_id', 'like', '%' . $customer_id . '%');
                }
                if ($arrFormData['name'] != '') {
                    $name = $arrFormData['name'];
                    $data = $data->where(function ($query) use ($name) {
                        $query->where('first_name', 'LIKE', '%' . $name . '%')->orWhere('last_name', 'LIKE', '%' . $name . '%')->orWhere(DB::raw('concat(first_name," ",last_name)'), 'LIKE', "%$name%");
                    });
                }
                if ($arrFormData['start_date'] != '') {
                    $startDate = date("Y-m-d", strtotime(convertDate($arrFormData['start_date'])));

                    if ($arrFormData['end_date'] != '') {
                        $endDate = date("Y-m-d ", strtotime(convertDate($arrFormData['end_date'])));
                    } else {
                        $endDate = '';
                    }
                    $data = $data->whereBetween(\DB::raw('DATE(associate_join_date)'), [$startDate, $endDate]);
                }
            }

            /******* fillter query End ****/

            $count = $data->orderby('associate_join_date', 'DESC')->count();
            // $count=count($data1);

            $data = $data->orderby('associate_join_date', 'DESC')->offset($_POST['start'])->limit($_POST['length'])->get();

            $totalCount = Member::where('member_id', '!=', '9999999')->where('associate_branch_id', $branch_id)->where('is_associate', 1)->where('is_deleted', 0)->count();

            $sno = $_POST['start'];
            $rowReturn = array();
            $relationId = '';
            foreach ($data as $row) {

                $sno++;
                $NomineeDetail = $row['memberNomineeDetails'];
                $val['DT_RowIndex'] = $sno;
                $val['join_date'] = date("d/m/Y", strtotime($row->associate_join_date));
                $val['branch'] = $row['associate_branch']->name;

                $val['branch_code'] = $row['associate_branch']->branch_code;
                $val['sector_name'] = $row['associate_branch']->sector;
                $val['region_name'] = $row['associate_branch']->regan;
                $val['zone_name'] = $row['associate_branch']->zone;
                $val['member_id'] = $row->member_id;
                $val['associate_no'] = $row->associate_no;

                $val['name'] = $row->first_name . ' ' . $row->last_name;
                $val['dob'] = date('d/m/Y', strtotime($row->dob));
                $val['nominee_name'] = $NomineeDetail->name;

                if ($row->id) {
                    $relation_id = $NomineeDetail->relation;

                    if ($relation_id) {
                        $val['relation'] = $NomineeDetail['nomineeRelationDetails']->name;
                    } else {
                        $val['relation'] = 'N/A';
                    }
                }


                $val['nominee_age'] = $NomineeDetail->age;
                $val['email'] = $row->email;
                $val['mobile_no'] = $row->mobile_no;
                if ($row->associate_senior_id == 0) {
                    $senior_code = $row->associate_senior_id . ' (Super Admin)';
                } else {
                    $senior_code = $row->associate_senior_code;
                }
                $val['senior_code'] = $senior_code;
                $val['associate_name'] = $row['seniorData']->first_name . ' ' . $row['seniorData']->last_name;
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

                $val['address'] = $row->address;
                $val['state'] = $row['states']->name;
                $val['district'] = $row['district']->name;
                $val['city'] = $row['city']->name;
                $val['village'] = $row->village;
                $val['pin_code'] = $row->pin_code;

                //$correctionStatus = getCorrectionStatus(1,$row->id);
                $url = URL::to("branch/associate/detail/" . $row->id . "");
                $btn = "";
                if (in_array('Associate Profile View', $auth)) {
                    $btn = '<a class=" " href="' . $url . '" title="Associate Detail"><i class="fas fa-eye text-default"></i></a>  ';
                }


                $val['action'] = $btn;
                $rowReturn[] = $val;
            }
            $output = array("draw" => $_POST['draw'], "recordsTotal" => $totalCount, "recordsFiltered" => $count, "data" => $rowReturn,);

            return json_encode($output);
        }
    }
    /**
     * Show  associate registration.
     * Route: /branch/associate/registration 
     * Method: get 
     * @return  array()  Response
     */
    public function register()
    {


        $data['title'] = 'Associate | Registration';
        $data['carder'] = \App\Models\Carder::where('status', 1)->where('is_deleted', 0)->limit(3)->get(['id', 'name', 'short_name']);
        $data['relations'] = relationsList();
        $data['samraddhBanks'] = SamraddhBank::with('bankAccount')->get();
        return view('templates.branch.associate_management.add', $data);
    }

    /**
     * associate form number exists or not .
     * Route: ajax call from - /branch/associate/registration
     * Method: Post 
     * @param  \Illuminate\Http\Request  $request
     * @return JSON array
     */
    public function associateFormNoCheck(Request $request)
    {
        $resCount = checkMemberFormNo($request->form_no, 'associate_form_no');
        $return_array = compact('resCount');
        return json_encode($return_array);
    }

    /**
     * Save associate data.
     * Route: /branch/associate/registration 
     * Method: Post 
     * @param  \Illuminate\Http\Request  $request
     * @return  array()  Response
     */
    public function save(Request $request)
    {
        Session::put('created_at', $request['created_at']);
        $globaldate = $request['created_at'];

        $errorCount = 0;
        $form1 = 0;
        $form2 = 0;
        $dataMsg['errormsg1'] = '';
        $dataMsg['errormsg2'] = '';
        $investmentAccountNoRd = '';
        $investmentAccountNoSsb = '';
        $isReceipt = 'no';
        $recipt_id = 0;
        $is_primary = 0;

        //get login user branch id(branch manager)pass auth id
        $getBranchId = getUserBranchId(Auth::user()->id);
        $branch_id = $getBranchId->id;
        $memberId = $request['id'];
        $getBranchCode = getBranchCode($branch_id);
        $branchCode = $getBranchCode->branch_code;
        $dataMemberDetail = Member::where('id', $memberId)->first();
        if (!empty($dataMemberDetail)) {
            $deposit_by_name = $dataMemberDetail->first_name . ' ' . $dataMemberDetail->last_name;
        }
        $deposit_by_id = $memberId;


        if ($request['id'] == '' || $request['member_id'] == '') {
            $dataMsg['errormsg1'] .= 'Please select member.<br>';
            $form1++;
            $errorCount++;
        }
        if ($request['form_no'] == '') {
            $dataMsg['errormsg1'] .= 'Please enter form no.<br>';
            $form1++;
            $errorCount++;
        }
        if ($request['application_date'] == '') {
            $dataMsg['errormsg1'] .= 'Please enter application date.<br>';
            $form1++;
            $errorCount++;
        }
        if ($request['current_carder'] == '') {
            $dataMsg['errormsg1'] .= 'Please assign carder.<br>';
            $form1++;
            $errorCount++;
        }
        if ($request['senior_id'] == '') {
            $dataMsg['errormsg1'] .= 'Please enter senior code.<br>';
            $form1++;
            $errorCount++;
        }
        if ($request['first_g_first_name'] == '') {
            $dataMsg['errormsg1'] .= 'Please enter guarantor  name.<br>';
            $form1++;
            $errorCount++;
        }
        if ($request['first_g_Mobile_no'] == '') {
            $dataMsg['errormsg1'] .= 'Please enter guarantor mobile number.<br>';
            $form1++;
            $errorCount++;
        }
        if ($request['first_g_address'] == '') {
            $dataMsg['errormsg1'] .= 'Please enter guarantor address.<br>';
            $form1++;
            $errorCount++;
        }
        if ($request['ssb_account'] == '') {
            $dataMsg['errormsg2'] .= 'Please select SSB account option.<br>';
            $form2++;
            $errorCount++;
        }
        if ($request['ssb_account'] == 1) {
            if ($request['ssb_account_number'] == '') {
                $dataMsg['errormsg2'] .= 'Please enter SSB account no.<br>';
                $form2++;
                $errorCount++;
            }
        }
        if ($request['ssb_account'] == 0) {
            if ($request['ssb_amount'] == '') {
                $dataMsg['errormsg2'] .= 'Please enter SSB amount.<br>';
                $form2++;
                $errorCount++;
            }
            if ($request['ssb_first_first_name'] == '') {
                $dataMsg['errormsg2'] .= 'Please enter SSB first nominee  name.<br>';
                $form2++;
                $errorCount++;
            }
            if ($request['ssb_first_relation'] == '') {
                $dataMsg['errormsg2'] .= 'Please enter SSB first nominee relation.<br>';
                $form2++;
                $errorCount++;
            }
            if ($request['ssb_first_dob'] == '') {
                $dataMsg['errormsg2'] .= 'Please enter SSB first nominee date of birth.<br>';
                $form2++;
                $errorCount++;
            }
            if ($request['ssb_first_percentage'] == '') {
                $dataMsg['errormsg2'] .= 'Please enter SSB first nominee percentage.<br>';
                $form2++;
                $errorCount++;
            }
            if (!isset($request['ssb_first_gender'])) {
                $dataMsg['errormsg2'] .= 'Please select SSB first nominee gender.<br>';
                $form2++;
                $errorCount++;
            }
            if ($request['ssb_first_age'] == '') {
                $dataMsg['errormsg2'] .= 'Please enter SSB first nominee age.<br>';
                $form2++;
                $errorCount++;
            }
            if ($request['ssb_first_mobile_no'] == '') {
                $dataMsg['errormsg2'] .= 'Please enter SSB first nominee mobile No.<br>';
                $form2++;
                $errorCount++;
            }
            if ($request['ssb_second_validate'] == 1) {
                if ($request['ssb_second_first_name'] == '') {
                    $dataMsg['errormsg2'] .= 'Please enter SSB second nominee  name.<br>';
                    $form2++;
                    $errorCount++;
                }
                if ($request['ssb_second_relation'] == '') {
                    $dataMsg['errormsg2'] .= 'Please enter SSB second nominee relation.<br>';
                    $form2++;
                    $errorCount++;
                }
                if ($request['ssb_second_dob'] == '') {
                    $dataMsg['errormsg2'] .= 'Please enter SSB second nominee date of birth.<br>';
                    $form2++;
                    $errorCount++;
                }
                if ($request['ssb_second_percentage'] == '') {
                    $dataMsg['errormsg2'] .= 'Please enter SSB second nominee percentage.<br>';
                    $form2++;
                    $errorCount++;
                }
                if (!isset($request['ssb_second_gender'])) {
                    $dataMsg['errormsg2'] .= 'Please select SSB second nominee gender.<br>';
                    $form2++;
                    $errorCount++;
                }
                if ($request['ssb_second_age'] == '') {
                    $dataMsg['errormsg2'] .= 'Please enter SSB second nominee age.<br>';
                    $form2++;
                    $errorCount++;
                }
                if ($request['ssb_second_mobile_no'] == '') {
                    $dataMsg['errormsg2'] .= 'Please enter SSB second nominee mobile No.<br>';
                    $form2++;
                    $errorCount++;
                }
            }
        }
        if (in_array('Associate RD Account Investment required', auth()->user()->getPermissionNames()->toArray())) {

            if ($request['rd_account'] == '') {
                $dataMsg['errormsg2'] .= 'Please select RD account option.<br>';
                $form2++;
                $errorCount++;
            }
            if ($request['rd_account'] == 1) {
                if ($request['rd_account_number'] == '') {
                    $dataMsg['errormsg2'] .= 'Please enter RD account no.<br>';
                    $form2++;
                    $errorCount++;
                }
            }
            if ($request['rd_account'] == 0) {
                if ($request['rd_amount'] == '') {
                    $dataMsg['errormsg2'] .= 'Please enter RD amount.<br>';
                    $form2++;
                    $errorCount++;
                }
                if ($request['rd_first_first_name'] == '') {
                    $dataMsg['errormsg2'] .= 'Please enter RD first nominee  name.<br>';
                    $form2++;
                    $errorCount++;
                }
                if ($request['rd_first_relation'] == '') {
                    $dataMsg['errormsg2'] .= 'Please enter RD first nominee relation.<br>';
                    $form2++;
                    $errorCount++;
                }
                if ($request['rd_first_dob'] == '') {
                    $dataMsg['errormsg2'] .= 'Please enter RD first nominee date of birth.<br>';
                    $form2++;
                    $errorCount++;
                }
                if ($request['rd_first_percentage'] == '') {
                    $dataMsg['errormsg2'] .= 'Please enter RD first nominee percentage.<br>';
                    $form2++;
                    $errorCount++;
                }
                if (!isset($request['rd_first_gender'])) {
                    $dataMsg['errormsg2'] .= 'Please select RD first nominee gender.<br>';
                    $form2++;
                    $errorCount++;
                }
                if ($request['rd_first_age'] == '') {
                    $dataMsg['errormsg2'] .= 'Please enter RD first nominee age.<br>';
                    $form2++;
                    $errorCount++;
                }
                if ($request['rd_first_mobile_no'] == '') {
                    $dataMsg['errormsg2'] .= 'Please enter RD first nominee mobile No.<br>';
                    $form2++;
                    $errorCount++;
                }
                if ($request['rd_second_validate'] == 1) {
                    if ($request['rd_second_first_name'] == '') {
                        $dataMsg['errormsg2'] .= 'Please enter RD second nominee  name.<br>';
                        $form2++;
                        $errorCount++;
                    }
                    if ($request['rd_second_relation'] == '') {
                        $dataMsg['errormsg2'] .= 'Please enter RD second nominee relation.<br>';
                        $form2++;
                        $errorCount++;
                    }
                    if ($request['rd_second_dob'] == '') {
                        $dataMsg['errormsg2'] .= 'Please enter RD second nominee date of birth.<br>';
                        $form2++;
                        $errorCount++;
                    }
                    if ($request['rd_second_percentage'] == '') {
                        $dataMsg['errormsg2'] .= 'Please enter RD second nominee percentage.<br>';
                        $form2++;
                        $errorCount++;
                    }
                    if (!isset($request['rd_second_gender'])) {
                        $dataMsg['errormsg2'] .= 'Please select RD second nominee gender.<br>';
                        $form2++;
                        $errorCount++;
                    }
                    if ($request['rd_second_age'] == '') {
                        $dataMsg['errormsg2'] .= 'Please enter RD second nominee age.<br>';
                        $form2++;
                        $errorCount++;
                    }
                    if ($request['rd_second_mobile_no'] == '') {
                        $dataMsg['errormsg2'] .= 'Please enter RD second nominee mobile No.<br>';
                        $form2++;
                        $errorCount++;
                    }
                }
            }
        }
        $dataMsg['msg'] = 'Associate not created.Check your fields';


        if ($request['rd_account'] == 1) {
            // ssb account no exits or not(pass member id and ssb account no)
            $rdAccountDetail = getInvestmentAccount($memberId, $request['rd_account_number']);

            if (!empty($rdAccountDetail)) {
                $investmentAccountNoRd = $request['rd_account_number'];
            } else {
                $dataMsg['msg_type'] = 'error';
                $dataMsg['msg'] = 'RD account not created!';
                $dataMsg['reciept_generate '] = 'no';
                $dataMsg['reciept_id'] = 0;
                $dataMsg['errormsg2'] .= 'RD account number wrong.<br>';
                $form2++;
                $errorCount++;
            }
        }
        if ($request['rd_account'] == 0) {
            if ($request['payment_mode'] == 3) {
                // pass member id 
                $ssbPrimary = getMemberSsbAccountDetail($memberId);
                if (!empty($ssbPrimary)) {
                    if ($ssbPrimary->balance < $request['rd_amount']) {
                        $dataMsg['msg_type'] = 'error';
                        $dataMsg['msg'] = 'SSB account does not have a sufficient balance.Change your payment mode';
                        $dataMsg['reciept_generate '] = 'no';
                        $dataMsg['reciept_id'] = 0;
                        $dataMsg['errormsg2'] .= 'Your SSB account does not have a sufficient balance.<br>';
                        $form2++;
                        $errorCount++;
                    }
                } else {
                    $dataMsg['msg_type'] = 'error';
                    $dataMsg['msg'] = 'You does not have SSB account';
                    $dataMsg['reciept_generate '] = 'no';
                    $dataMsg['reciept_id'] = 0;
                    $dataMsg['errormsg2'] .= 'You does not have SSB account.<br>';
                    $form2++;
                    $errorCount++;
                }
            }
        }

        if ($request['ssb_account'] == 1) {
            // ssb account no exits or not(pass member id and ssb account no)
            $ssbAccountDetail = getInvestmentAccount($memberId, $request['ssb_account_number']);

            if (!empty($ssbAccountDetail)) {
                $investmentAccountNoSsb = $request['ssb_account_number'];
                $ssbAccountDetail = getMemberSsbAccountDetail($memberId);
                $ssbAccountId = $ssbAccountDetail->id;
            } else {
                $dataMsg['msg_type'] = 'error';
                $dataMsg['msg'] = 'SSB account number wrong.';
                $dataMsg['reciept_generate '] = 'no';
                $dataMsg['reciept_id'] = 0;
                $dataMsg['errormsg2'] .= 'SSB account number wrong.<br>';
                $form2++;
                $errorCount++;
            }
        }
        if ($request['ssb_account'] == 0) {
            // ssb account no exits or not(pass member id and ssb account no)
            $ssbAccountDetail = getMemberSsbAccountDetail($memberId);

            if (!empty($ssbAccountDetail)) {
                $dataMsg['msg_type'] = 'error';
                $dataMsg['msg'] = 'SSB account already exists!.';
                $dataMsg['reciept_generate '] = 'no';
                $dataMsg['reciept_id'] = 0;
                $dataMsg['errormsg2'] .= 'SSB account already exists!.<br>';
                $form2++;
                $errorCount++;
            }
        }
        if ($request['payment_mode'] == 1) {
            $getChequeDetail = \App\Models\ReceivedCheque::where('id', $request['cheque_id'])->where('status', 3)->first(['id', 'amount', 'status']);

            if (!empty($getChequeDetail)) {
                $dataMsg['msg_type'] = 'cheque_error';
                $dataMsg['msg'] = 'Cheque already used select another cheque.';
                $dataMsg['reciept_generate '] = 'no';
                $dataMsg['reciept_id'] = 0;
                $dataMsg['errormsg2'] .= 'Cheque already used select another cheque.<br>';
                $form2++;
                $errorCount++;
            } else {
                $getamount = \App\Models\ReceivedCheque::where('id', $request['cheque_id'])->first(['id', 'amount']);

                if ($getamount->amount != number_format((float) $request['rd_amount'], 4, '.', '')) {
                    $dataMsg['msg_type'] = 'cheque_error';
                    $dataMsg['msg'] = 'RD amount is not equal to cheque amount.';
                    $dataMsg['reciept_generate '] = 'no';
                    $dataMsg['reciept_id'] = 0;
                    $dataMsg['errormsg2'] .= 'RD amount is not equal to cheque amount.<br>';
                    $form2++;
                    $errorCount++;
                }
            }
        }
        if ($errorCount > 0) {

            $dataMsg['form1'] = $form1;
            $dataMsg['form2'] = $form2;
            // print_r($dataMsg);die;
            return json_encode($dataMsg);
        }
        DB::beginTransaction();
        try {
            if ($request['rd_account'] == '0') {
                $faCode = 704;
                $dataInvestrd['deposite_amount'] = $request['rd_amount'];
                $dataInvestrd['payment_mode'] = $request['payment_mode'];
                $dataInvestrd['tenure'] = $request['tenure'] / 12;
                $tenure = $request['tenure'] / 12;

                $dataInvestrd['deposite_amount'] = $request['rd_amount'];
                $dataInvestrd['current_balance'] = $request['rd_amount'];

                if ($tenure == 36) {
                    $tenurefacode = $faCode . '002';
                } elseif ($tenure == 60) {
                    $tenurefacode = $faCode . '003';
                } elseif ($tenure == 84) {
                    $tenurefacode = $faCode . '004';
                } else {
                    $tenurefacode = $faCode . '001';
                }
                $dataInvestrd['tenure_fa_code'] = $tenurefacode;
                //$formNumber = rand(10,1000);
                $formNumber = $request['rd_form_no'];
                // getInvesment Plan id by plan code

                $planIdGet = getPlanID($faCode);
                $planId = $planIdGet->id;

                $investmentMiCode = getInvesmentMiCodeNew($planId, $branch_id);
                if (!empty($investmentMiCode)) {
                    $miCodeAdd = $investmentMiCode->mi_code + 1;
                    if ($investmentMiCode->mi_code == 9999998) {
                        $miCodeAdd = $investmentMiCode->mi_code + 2;
                    }
                } else {
                    $miCodeAdd = 1;
                }

                $miCode = str_pad($miCodeAdd, 5, '0', STR_PAD_LEFT);
                // Invesment Account no
                $investmentAccountNoRd = $branchCode . $faCode . $miCode;
                $miCodeBig = str_pad($miCodeAdd, 5, '0', STR_PAD_LEFT);

                $passbook = '720' . $branchCode . $faCode . $miCodeBig;
                if ($faCode == 704) {
                    $dataInvestrd['passbook_no'] = $passbook;
                    $dataInvestrd['maturity_amount'] = $request['rd_amount_maturity'];
                    $dataInvestrd['interest_rate'] = $request['rd_rate'];
                }

                $payment_mode = 0;
                $rdDebitaccountId = 0;
                $rdPayDate = null;

                $received_cheque_id = NULL;
                $cheque_deposit_bank_id = NULL;
                $cheque_deposit_bank_ac_id = NULL;
                $online_deposit_bank_id = NULL;
                $online_deposit_bank_ac_id = NULL;
                if ($request['payment_mode'] == 1) {
                    $invPaymentMode['cheque_number'] = $request['rd_cheque_no'];
                    $invPaymentMode['bank_name'] = $request['rd_branch_name'];
                    $invPaymentMode['branch_name'] = $request['rd_bank_name'];
                    $received_cheque_id = $request['cheque_id'];
                    $chequeDetail = \App\Models\ReceivedCheque::where('id', $request['cheque_id'])->first();
                    $cheque_deposit_bank_id = $chequeDetail->deposit_bank_id;
                    $cheque_deposit_bank_ac_id = $chequeDetail->deposit_account_id;
                    $invPaymentMode['cheque_date'] = date("Y-m-d", strtotime(convertDate($request['rd_cheque_date'])));
                    $payment_mode = 1;
                    $rdPayDate = date("Y-m-d", strtotime(convertDate($request['rd_cheque_date'])));
                }
                if ($request['payment_mode'] == 2) {
                    $invPaymentMode['transaction_id'] = $request['rd_online_id'];
                    $invPaymentMode['transaction_date'] = date("Y-m-d", strtotime(convertDate($request['rd_online_date'])));
                    $payment_mode = 3;

                    $online_deposit_bank_id = $request['rd_online_bank_id'];
                    $online_deposit_bank_ac_id = $request['rd_online_bank_ac_id'];
                    $rdPayDate = date("Y-m-d", strtotime(convertDate($request['rd_online_date'])));
                }
                if ($request['payment_mode'] == 3) {
                    // pass member id
                    $rdPayDate = date("Y-m-d");
                    $ssbAccountDetail = getMemberSsbAccountDetail($memberId);
                    $invPaymentMode['ssb_account_id'] = $ssbAccountDetail->id;
                    $invPaymentMode['ssb_account_no'] = $ssbAccountDetail->account_no;
                    $payment_mode = 4;
                    $rdDebitaccountId = $ssbAccountDetail->id;
                    if (!empty($ssbAccountDetail)) {
                        if ($ssbAccountDetail->balance > $request['rd_amount']) {
                            $detail = 'RD/' . $investmentAccountNoRd . '/Auto Debit';
                            $ssbTranCalculation = CommanTransactionsController::ssbTransactionModify($ssbAccountDetail->id, $ssbAccountDetail->account_no, $ssbAccountDetail->balance, $request['rd_amount'], $detail, 'INR', 'DR', 3, $branch_id, 1, 6);

                            $amountArrayRD = array('1' => $request['rd_amount']);
                            $dataInvestrd['plan_id'] = $planId;
                            $dataInvestrd['form_number'] = $formNumber;
                            $dataInvestrd['member_id'] = $memberId;
                            $dataInvestrd['branch_id'] = $branch_id;
                            $dataInvestrd['account_number'] = $investmentAccountNoRd;
                            $dataInvestrd['mi_code'] = $miCode;
                            $dataInvestrd['associate_id'] = 1;
                            $dataInvestrd['current_balance'] = $request['rd_amount'];
                            $dataInvestrd['created_at'] = $request['created_at'];
                            $res = \App\Models\Memberinvestments::create($dataInvestrd);
                            $investmentId = $res->id;

                            $satRefId = CommanTransactionsController::createTransactionReferences($ssbTranCalculation, $investmentId);

                            $rdCreateTran = CommanTransactionsController::createTransaction($satRefId, 1, $ssbAccountDetail->id, $memberId, $branch_id, $branchCode, $amountArrayRD, 5, $deposit_by_name, $deposit_by_id, $ssbAccountDetail->account_no, $cheque_dd_no = '0', $bank_name = 'null', $branch_name = 'null', $payment_date = 'null', $online_payment_id = 'null', $online_payment_by = 'null', $saving_account_id = 0, 'DR');
                        } else {
                            $dataMsg['msg_type'] = 'error';
                            $dataMsg['msg'] = 'Your SSB account does not have a sufficient balance.';
                            $dataMsg['reciept_generate '] = 'no';
                            $dataMsg['reciept_id'] = 0;
                            $dataMsg['errormsg2'] .= 'Your SSB account does not have a sufficient balance.<br>';
                            $form2++;
                            $errorCount++;
                            return json_encode($dataMsg);
                        }
                    } else {
                        $dataMsg['msg_type'] = 'error';
                        $dataMsg['msg'] = 'You does not have SSB account';
                        $dataMsg['reciept_generate '] = 'no';
                        $dataMsg['reciept_id'] = 0;
                        $dataMsg['errormsg2'] .= 'You does not have SSB account.<br>';
                        $form2++;
                        $errorCount++;
                        return json_encode($dataMsg);
                    }
                } else {
                    $dataInvestrd['plan_id'] = $planId;
                    $dataInvestrd['form_number'] = $formNumber;
                    $dataInvestrd['member_id'] = $memberId;
                    $dataInvestrd['branch_id'] = $branch_id;
                    $dataInvestrd['account_number'] = $investmentAccountNoRd;
                    $dataInvestrd['mi_code'] = $miCode;
                    $dataInvestrd['associate_id'] = 1;
                    $dataInvestrd['current_balance'] = $request['rd_amount'];
                    $dataInvestrd['created_at'] = $request['created_at'];
                    $res = \App\Models\Memberinvestments::create($dataInvestrd);
                    $investmentId = $res->id;
                    $satRefId = NULL;
                }



                $invDatard1 = array(
                    'investment_id' => $investmentId,
                    'nominee_type' => 0,
                    'name' => $request['rd_first_first_name'],
                    //  'second_name' => $request['rd_first_last_name'],
                    'relation' => $request['rd_first_relation'],
                    'gender' => $request['rd_first_gender'],
                    'dob' => date("Y-m-d", strtotime(convertDate($request['rd_first_dob']))),
                    'age' => $request['rd_first_age'],
                    'percentage' => $request['rd_first_percentage'],
                    'phone_number' => $request['rd_first_mobile_no'],
                    'created_at' => $request['created_at'],
                );
                $resinvDatard1 = \App\Models\Memberinvestmentsnominees::create($invDatard1);
                if ($request['rd_second_validate'] == 1) {
                    $invDatard2 = array(
                        'investment_id' => $investmentId,
                        'nominee_type' => 1,
                        'name' => $request['rd_second_first_name'],
                        // 'second_name' => $request['rd_second_last_name'],
                        'relation' => $request['rd_second_relation'],
                        'gender' => $request['rd_second_gender'],
                        'dob' => date("Y-m-d", strtotime(convertDate($request['rd_second_dob']))),
                        'age' => $request['rd_second_age'],
                        'percentage' => $request['rd_second_percentage'],
                        'phone_number' => $request['rd_second_mobile_no'],
                        'created_at' => $request['created_at'],
                    );
                    $resinvDatard2 = \App\Models\Memberinvestmentsnominees::create($invDatard2);
                }

                $invPaymentMode['investment_id'] = $investmentId;
                $res = \App\Models\Memberinvestmentspayments::create($invPaymentMode);

                $amountArray = array('1' => $request['rd_amount']);

                $createTransaction = CommanTransactionsController::createTransaction($satRefId = NULL, 2, $investmentId, $memberId, $branch_id, $branchCode, $amountArray, $payment_mode, $deposit_by_name, $deposit_by_id, $investmentAccountNoRd, $request['rd_cheque_no'], $request['rd_bank_name'], $request['rd_branch_name'], $rdPayDate, $request['rd_online_id'], $online_payment_by = 'null', $rdDebitaccountId, 'CR');
                $sAccountNumber = '';
                if ($rdDebitaccountId != 0) {
                    $sAccountNumber = $rdDebitaccountId;
                }
                $description = 'SRD Account Opening';
                $createDayBook = CommanTransactionsController::createDayBookNew($createTransaction, $satRefId, 2, $investmentId, $request['senior_id'], $memberId, $request['rd_amount'], $request['rd_amount'], $withdrawal = 0, $description, $sAccountNumber, $branch_id, $branchCode, $amountArray, $payment_mode, $deposit_by_name, $deposit_by_id, $investmentAccountNoRd, $request['rd_cheque_no'], $request['rd_bank_name'], $request['rd_branch_name'], $rdPayDate, $request['rd_online_id'], $online_payment_by = Null, $sAccountNumber, 'CR', $received_cheque_id, $cheque_deposit_bank_id, $cheque_deposit_bank_ac_id, $online_deposit_bank_id, $online_deposit_bank_ac_id);


                /*--------------------- received cheque payment -----------------------*/
                if ($payment_mode == 1) {
                    $receivedPayment['type'] = 2;
                    $receivedPayment['branch_id'] = $branch_id;
                    $receivedPayment['investment_id'] = $investmentId;
                    $receivedPayment['cheque_id'] = $request['cheque_id'];
                    $receivedPayment['day_book_id'] = $createDayBook;
                    $receivedPayment['created_at'] = $globaldate;
                    $receivedCreate = \App\Models\ReceivedChequePayment::create($receivedPayment);
                    $dataRC['status'] = 3;
                    $receivedcheque = \App\Models\ReceivedCheque::find($request['cheque_id']);
                    $receivedcheque->update($dataRC);
                }
                /*--------------------- received cheque payment -----------------------*/

                /* ------------------ commission genarate-----------------*/
                $commission = CommanTransactionsController::commissionDistributeInvestment($request['senior_id'], $investmentId, 3, $request['rd_amount'], 1, $planId, $branch_id, $request['tenure'], $createDayBook);
                $commission_collection = CommanTransactionsController::commissionCollectionInvestment($request['senior_id'], $investmentId, 5, $request['rd_amount'], 1, $planId, $branch_id, $request['tenure'], $createDayBook);

                /*----- ------  credit business start ---- ---------------*/
                $creditBusiness = CommanTransactionsController::associateCreditBusiness($request['senior_id'], $investmentId, 1, $request['rd_amount'], 1, $planId, $request['tenure'], $createDayBook);

                /*----- ------  credit business end ---- ---------------*/

                /* ------------------ commission genarate-----------------*/
                /************************ RD head Implement start ****************/

                $jv_unique_id = $ssb_account_tran_id_to = $ssb_account_tran_id_from = $cheque_type = $cheque_id = $cheque_bank_to_name = $cheque_bank_to_branch = $cheque_bank_to_ac_no = $cheque_bank_to_ifsc = $ssb_account_id_to = $transction_bank_from_id = $transction_bank_from_ac_id = $transction_bank_to_name = $transction_bank_to_ac_no = $transction_bank_to_branch = $transction_bank_to_ifsc = $cheque_bank_from_id = $cheque_bank_ac_from_id = NULL;
                $amount_to_name = NULL;
                $amount_from_id = NULL;
                $amount_from_name = NULL;
                $amount_to_id = NULL;

                $bank_id = NULL;
                $bank_ac_id = NULL;
                $associate_id_admin = 1;

                $rdAmount = $request['rd_amount'];
                $daybookRefRD = CommanTransactionsController::createBranchDayBookReferenceNew($rdAmount, $globaldate);
                $refIdRD = $daybookRefRD;
                $currency_code = 'INR';
                $headPaymentModeRD = 0;
                $payment_type_rd = 'CR';

                $type_idRD = $investmentId;


                $entry_date = date("Y-m-d", strtotime(convertDate($globaldate)));
                $entry_time = date("H:i:s", strtotime(convertDate($globaldate)));
                $created_by = 1;
                $created_by_id = Auth::user()->id;
                $created_at = date("Y-m-d H:i:s", strtotime(convertDate($globaldate)));
                $updated_at = date("Y-m-d H:i:s", strtotime(convertDate($globaldate)));


                $planDetail = getPlanDetail($planId);
                $typeHeadRd = 3;
                $sub_typeHeadRd = 31;



                $v_no = NULL;
                $v_date = NULL;
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



                if ($request['payment_mode'] == 1) { // cheque moade 
                    $headPaymentModeRD = 1;
                    $chequeDetail = \App\Models\ReceivedCheque::where('id', $request['cheque_id'])->first();

                    $cheque_no = $chequeDetail->cheque_no;
                    $cheque_date = $rdPayDate;
                    $cheque_bank_from = $chequeDetail->bank_name;
                    $cheque_bank_ac_from = $chequeDetail->cheque_account_no;
                    $cheque_bank_ifsc_from = NULL;
                    $cheque_bank_branch_from = $chequeDetail->branch_name;
                    $cheque_bank_to = $chequeDetail->deposit_bank_id;
                    $cheque_bank_ac_to = $chequeDetail->deposit_account_id;


                    $cheque_bank_to_name = getSamraddhBank($cheque_bank_to)->bank_name;

                    $bank_ac_detail_get = getSamraddhBankAccountId($cheque_bank_ac_to);
                    $cheque_bank_to_branch = $bank_ac_detail_get->branch_name;
                    $cheque_bank_to_ac_no = $bank_ac_detail_get->account_no;
                    $cheque_bank_to_ifsc = $bank_ac_detail_get->ifsc_code;

                    $cheque_type = 0;
                    $cheque_id = $request['cheque_id'];
                    $cheque_bank_from_id = $cheque_bank_ac_from_id = NULL;



                    $getBankHead = \App\Models\SamraddhBank::where('id', $cheque_bank_to)->first();

                    $head11 = 2;
                    $head21 = 10;
                    $head31 = 27;
                    $head41 = $getBankHead->account_head_id;
                    $head51 = NULL;

                    $rdDesDR = 'Bank A/c Dr ' . $rdAmount . '/-';
                    $rdDesCR = 'To ' . $planDetail->name . '(' . $investmentAccountNoRd . ')  A/c Cr ' . $rdAmount . '/-';
                    $rdDes = 'Amount received for Account opening ' . $planDetail->name . '(' . $investmentAccountNoRd . ') through cheque(' . $cheque_no . ')';
                    $rdDesMem = 'Account opening ' . $planDetail->name . '(' . $investmentAccountNoRd . ') through cheque(' . $cheque_no . ')';


                    //bank head entry
                    $allTranRDcheque = CommanTransactionsController::headTransactionCreate($refIdRD, $branch_id, $bank_id, $bank_ac_id, $head41, $typeHeadRd, $sub_typeHeadRd, $type_idRD, $associate_id_admin, $memberId, $branch_id_to = NULL, $branch_id_from = NULL, $opening_balance = NULL, $rdAmount, $closing_balance = NULL, $rdDes, 'DR', $headPaymentModeRD, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $createDayBook, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $cheque_bank_to_name, $cheque_bank_to_branch, $cheque_bank_to_ac_no, $cheque_bank_to_ifsc, $transction_bank_from_id, $transction_bank_from_ac_id, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc, $cheque_bank_from_id, $cheque_bank_ac_from_id);


                    //bank entry
                    $bankCheque = CommanTransactionsController::NewFieldAddSamraddhBankDaybookCreate($refIdRD, $cheque_bank_to, $cheque_bank_ac_to, $typeHeadRd, $sub_typeHeadRd, $type_idRD, $associate_id_admin, $memberId, $branch_id, $opening_balance = NULL, $rdAmount, $closing_balance = NULL, $rdDes, $rdDesDR, $rdDesCR, 'CR', $headPaymentModeRD, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $createDayBook, $ssb_account_id_to, $cheque_bank_from_id, $cheque_bank_ac_from_id, $cheque_bank_to_name, $cheque_bank_to_branch, $cheque_bank_to_ac_no, $cheque_bank_to_ifsc, $transction_bank_from_id, $transction_bank_from_ac_id, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $jv_unique_id, $cheque_type, $cheque_id);

                    //bank balence
                    $bankClosing = CommanTransactionsController::checkCreateBankClosing($cheque_bank_to, $cheque_bank_ac_to, $created_at, $rdAmount, 0);
                } elseif ($request['payment_mode'] == 2) { //online transaction
                    $headPaymentModeRD = 2;
                    $transction_no = $request['rd_online_id'];
                    $transction_bank_from = NULL;
                    $transction_bank_ac_from = NULL;
                    $transction_bank_ifsc_from = NULL;
                    $transction_bank_branch_from = NULL;

                    $transction_bank_to = $online_deposit_bank_id;
                    $transction_bank_ac_to = $online_deposit_bank_ac_id;
                    $transction_date = $rdPayDate;
                    $getBHead = \App\Models\SamraddhBank::where('id', $transction_bank_to)->first();

                    $head111 = 2;
                    $head211 = 10;
                    $head311 = 27;
                    $head411 = $getBHead->account_head_id;
                    $head511 = NULL;

                    $rdDesDR = 'Bank A/c Dr ' . $rdAmount . '/-';
                    $rdDesCR = 'To ' . $planDetail->name . '(' . $investmentAccountNoRd . ')  A/c Cr ' . $rdAmount . '/-';
                    $rdDes = 'Amount received for Account opening ' . $planDetail->name . '(' . $investmentAccountNoRd . ') through online transaction(' . $transction_no . ')';
                    $rdDesMem = 'Account opening ' . $planDetail->name . '(' . $investmentAccountNoRd . ') through online transaction(' . $transction_no . ')';


                    //bank head entry
                    $allTranRDonline = CommanTransactionsController::headTransactionCreate($refIdRD, $branch_id, $bank_id, $bank_ac_id, $head411, $typeHeadRd, $sub_typeHeadRd, $type_idRD, $associate_id_admin, $memberId, $branch_id_to = NULL, $branch_id_from = NULL, $opening_balance = NULL, $rdAmount, $closing_balance = NULL, $rdDes, 'DR', $headPaymentModeRD, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $createDayBook, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $cheque_bank_to_name, $cheque_bank_to_branch, $cheque_bank_to_ac_no, $cheque_bank_to_ifsc, $transction_bank_from_id, $transction_bank_from_ac_id, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc, $cheque_bank_from_id, $cheque_bank_ac_from_id);


                    //bank entry
                    $bankonline = CommanTransactionsController::NewFieldAddSamraddhBankDaybookCreate($refIdRD, $transction_bank_to, $transction_bank_ac_to, $typeHeadRd, $sub_typeHeadRd, $type_idRD, $associate_id_admin, $memberId, $branch_id, $opening_balance = NULL, $rdAmount, $closing_balance = NULL, $rdDes, $rdDesDR, $rdDesCR, 'CR', $headPaymentModeRD, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $createDayBook, $ssb_account_id_to, $cheque_bank_from_id, $cheque_bank_ac_from_id, $cheque_bank_to_name, $cheque_bank_to_branch, $cheque_bank_to_ac_no, $cheque_bank_to_ifsc, $transction_bank_from_id, $transction_bank_from_ac_id, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $jv_unique_id, $cheque_type, $cheque_id);

                    //bank balence
                    $bankClosing = CommanTransactionsController::checkCreateBankClosing($transction_bank_to, $transction_bank_ac_to, $created_at, $rdAmount, 0);
                } elseif ($request['payment_mode'] == 3) { // ssb

                    $ssb_account_tran_id_from = $ssbTranCalculation;

                    $headPaymentModeRD = 3;

                    $v_no = mt_rand(0, 999999999999999);
                    $v_date = $entry_date;
                    $ssb_account_id_from = $sAccountNumber;
                    $SSBDescTran = 'Amount transfer to ' . $planDetail->name . '(' . $investmentAccountNoRd . ') ';
                    $head1rdSSB = 1;
                    $head2rdSSB = 8;
                    $head3rdSSB = 20;
                    $head4rdSSB = 56;
                    $head5rdSSB = NULL;

                    $ssbDetals = SavingAccount::where('id', $ssb_account_id_from)->first();
                    $rdDesDR = $planDetail->name . '(' . $investmentAccountNoRd . ') A/c Dr ' . $rdAmount . '/-';
                    $rdDesCR = 'To SSB(' . $ssbDetals->account_no . ') A/c Cr ' . $rdAmount . '/-';
                    $rdDes = 'Amount received for Account opening ' . $planDetail->name . '(' . $investmentAccountNoRd . ') through SSB(' . $ssbDetals->account_no . ')';
                    $rdDesMem = 'Account opening ' . $planDetail->name . '(' . $investmentAccountNoRd . ') through SSB(' . $ssbDetals->account_no . ')';


                    // ssb  head entry -
                    $allTranRDSSB = CommanTransactionsController::headTransactionCreate($refIdRD, $branch_id, $bank_id, $bank_ac_id, $head4rdSSB, $typeHeadRd, $sub_typeHeadRd, $type_idRD, $associate_id_admin, $memberId, $branch_id_to = NULL, $branch_id_from = NULL, $opening_balance = NULL, $rdAmount, $closing_balance = NULL, $SSBDescTran, 'DR', $headPaymentModeRD, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $createDayBook, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $cheque_bank_to_name, $cheque_bank_to_branch, $cheque_bank_to_ac_no, $cheque_bank_to_ifsc, $transction_bank_from_id, $transction_bank_from_ac_id, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc, $cheque_bank_from_id, $cheque_bank_ac_from_id);


                    $branchClosing = CommanTransactionsController::checkCreateBranchClosingDr($branch_id, $created_at, $rdAmount, 0);

                    $memberTranInvest77 = CommanTransactionsController::NewFieldAddMemberTransactionCreate($refIdRD, '4', '47', $ssb_account_id_from, $associate_id_admin, $ssbDetals->member_id, $branch_id, $bank_id, $bank_ac_id, $rdAmount, $SSBDescTran, 'DR', $headPaymentModeRD, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $createDayBook, $ssb_account_id_to, $cheque_bank_from_id, $cheque_bank_ac_from_id, $cheque_bank_to_name, $cheque_bank_to_branch, $cheque_bank_to_ac_no, $cheque_bank_to_ifsc, $transction_bank_from_id, $transction_bank_from_ac_id, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $jv_unique_id, $cheque_type, $cheque_id);
                } else {
                    $headPaymentModeRD = 0;
                    $head1rdC = 2;
                    $head2rdC = 10;
                    $head3rdC = 28;
                    $head4rdC = 71;
                    $head5rdC = NULL;

                    $rdDesDR = 'Cash A/c Dr ' . $rdAmount . '/-';
                    $rdDesCR = 'To ' . $planDetail->name . '(' . $investmentAccountNoRd . ')  A/c Cr ' . $rdAmount . '/-';
                    $rdDes = 'Amount received for Account opening ' . $planDetail->name . '(' . $investmentAccountNoRd . ') through cash(' . $branchCode . ')';
                    $rdDesMem = 'Account opening ' . $planDetail->name . '(' . $investmentAccountNoRd . ') through cash(' . $branchCode . ')';


                    // branch cash  head entry +
                    $allTranRDcash = CommanTransactionsController::headTransactionCreate($refIdRD, $branch_id, $bank_id, $bank_ac_id, $head3rdC, $typeHeadRd, $sub_typeHeadRd, $type_idRD, $associate_id_admin, $memberId, $branch_id_to = NULL, $branch_id_from = NULL, $opening_balance = NULL, $rdAmount, $closing_balance = NULL, $rdDes, 'DR', $headPaymentModeRD, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $createDayBook, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $cheque_bank_to_name, $cheque_bank_to_branch, $cheque_bank_to_ac_no, $cheque_bank_to_ifsc, $transction_bank_from_id, $transction_bank_from_ac_id, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc, $cheque_bank_from_id, $cheque_bank_ac_from_id);


                    //Balance   entry +
                    $branchCash = CommanTransactionsController::checkCreateBranchCash($branch_id, $created_at, $rdAmount, 0);
                }

                $head1rd = 1;
                $head2rd = 8;
                $head3rd = 20;
                $head4rd = 59;
                $head5rd = 83;
                //branch day book entry +
                $daybookRd = CommanTransactionsController::NewFieldBranchDaybookCreate($refIdRD, $branch_id, $typeHeadRd, $sub_typeHeadRd, $type_idRD, 1, $memberId, $branch_id_to = NULL, $branch_id_from = NULL, $opening_balance = NULL, $rdAmount, $closing_balance = NULL, $rdDes, $rdDesDR, $rdDesCR, $payment_type_rd, $headPaymentModeRD, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $is_contra = NULL, $contra_id = NULL, $created_at, $updated_at, $createDayBook, $ssb_account_id_to, $cheque_bank_from_id, $cheque_bank_ac_from_id, $cheque_bank_to_name, $cheque_bank_to_branch, $cheque_bank_to_ac_no, $cheque_bank_to_ifsc, $transction_bank_from_id, $transction_bank_from_ac_id, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc, 0, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $jv_unique_id, $cheque_type, $cheque_id);


                // Investment head entry +
                $allTranRD = CommanTransactionsController::headTransactionCreate($refIdRD, $branch_id, $bank_id, $bank_ac_id, $head5rd, $typeHeadRd, $sub_typeHeadRd, $type_idRD, $associate_id_admin, $memberId, $branch_id_to = NULL, $branch_id_from = NULL, $opening_balance = NULL, $rdAmount, $closing_balance = NULL, $rdDes, $payment_type_rd, $headPaymentModeRD, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $createDayBook, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $cheque_bank_to_name, $cheque_bank_to_branch, $cheque_bank_to_ac_no, $cheque_bank_to_ifsc, $transction_bank_from_id, $transction_bank_from_ac_id, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc, $cheque_bank_from_id, $cheque_bank_ac_from_id);

                // Member transaction  +
                $memberTranRD = CommanTransactionsController::NewFieldAddMemberTransactionCreate($refIdRD, $typeHeadRd, $sub_typeHeadRd, $type_idRD, $associate_id_admin, $memberId, $branch_id, $bank_id, $bank_ac_id, $rdAmount, $rdDesMem, $payment_type_rd, $headPaymentModeRD, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $createDayBook, $ssb_account_id_to, $cheque_bank_from_id, $cheque_bank_ac_from_id, $cheque_bank_to_name, $cheque_bank_to_branch, $cheque_bank_to_ac_no, $cheque_bank_to_ifsc, $transction_bank_from_id, $transction_bank_from_ac_id, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $jv_unique_id, $cheque_type, $cheque_id);

                /******** Balance   entry ***************/
                $branchClosing = CommanTransactionsController::checkCreateBranchClosing($branch_id, $created_at, $rdAmount, 0);



                /************************ RD head Implement end ****************/
            }

            if ($request['ssb_account'] == 0) {
                $payment_mode = 0;
                $formNumber = rand(10, 1000);
                // getInvesment Plan id by plan code
                $faCode = 703;
                $planIdGet = getPlanID($faCode);
                $planId = $planIdGet->id;
                $investmentMiCode = getInvesmentMiCodeNew($planId, $branch_id);
                if (!empty($investmentMiCode)) {
                    $miCodeAdd = $investmentMiCode->mi_code + 1;
                } else {
                    $miCodeAdd = 1;
                }
                $miCode = str_pad($miCodeAdd, 5, '0', STR_PAD_LEFT);
                // Invesment Account no
                $investmentAccountNoSsb = $branchCode . $faCode . $miCode;

                $dataInvest['deposite_amount'] = $request['ssb_amount'];
                $dataInvest['plan_id'] = $planId;
                $dataInvest['form_number'] = $request['ssb_form_no'];
                $dataInvest['member_id'] = $memberId;
                $dataInvest['branch_id'] = $branch_id;
                $dataInvest['old_branch_id'] = $branch_id;
                $dataInvest['account_number'] = $investmentAccountNoSsb;
                $dataInvest['mi_code'] = $miCode;
                $dataInvest['associate_id'] = 1;
                $dataInvest['deposite_amount'] = $request['ssb_amount'];
                $dataInvest['current_balance'] = $request['ssb_amount'];
                $dataInvest['created_at'] = $request['created_at'];
                $res = \App\Models\Memberinvestments::create($dataInvest);
                $investmentId = $res->id;

                //create savings account
                $des = 'SSB Account Opening';
                $amount = $request['ssb_amount'];
                $createAccount = CommanTransactionsController::createSavingAccountDescriptionModify($memberId, $branch_id, $branchCode, $amount, $payment_mode, $investmentId, $miCode, $investmentAccountNoSsb, $is_primary, $faCode, $des, $associate_id = 1);


                $ssbAccountId = $createAccount['ssb_id']; //sb account id
                $satRefId = CommanTransactionsController::createTransactionReferences($createAccount['ssb_transaction_id'], $investmentId);


                $amountArraySsb = array('1' => $amount);

                $ssbCreateTran = CommanTransactionsController::createTransaction($satRefId, 1, $ssbAccountId, $memberId, $branch_id, $branchCode, $amountArraySsb, 0, $deposit_by_name, $deposit_by_id, $investmentAccountNoSsb, $cheque_dd_no = '0', $bank_name = 'null', $branch_name = 'null', $payment_date = 'null', $online_payment_id = 'null', $online_payment_by = 'null', $saving_account_id = 0, 'CR');

                $description = 'SSB Account Opening';
                $sAccountNumber = '';

                $createDayBook = CommanTransactionsController::createDayBookNew($ssbCreateTran, $satRefId, 1, $ssbAccountId, $request['senior_id'], $memberId, $request['ssb_amount'], $request['ssb_amount'], $withdrawal = 0, $description, $sAccountNumber, $branch_id, $branchCode, $amountArraySsb, $payment_mode, $deposit_by_name, $deposit_by_id, $investmentAccountNoSsb, 0, Null, Null, date('Y-m-d'), Null, $online_payment_by = Null, $sAccountNumber, 'CR', $received_cheque_id = NULL, $cheque_deposit_bank_id = NULL, $cheque_deposit_bank_ac_id = NULL, $online_deposit_bank_id = NULL, $online_deposit_bank_ac_id = NULL);

                $invData1ssb = array(
                    'investment_id' => $investmentId,
                    'nominee_type' => 0,
                    'name' => $request['ssb_first_first_name'],
                    //  'second_name' => $request['ssb_first_last_name'],
                    'relation' => $request['ssb_first_relation'],
                    'gender' => $request['ssb_first_gender'],
                    'dob' => date("Y-m-d", strtotime(convertDate($request['ssb_first_dob']))),
                    'age' => $request['ssb_first_age'],
                    'percentage' => $request['ssb_first_percentage'],
                    'phone_number' => $request['ssb_first_mobile_no'],
                    'created_at' => $request['created_at'],
                );
                $resinvData1 = \App\Models\Memberinvestmentsnominees::create($invData1ssb);
                if ($request['ssb_second_validate'] == 1) {
                    $invData2ssb = array(
                        'investment_id' => $investmentId,
                        'nominee_type' => 1,
                        'name' => $request['ssb_second_first_name'],
                        // 'second_name' => $request['ssb_second_last_name'],
                        'relation' => $request['ssb_second_relation'],
                        'gender' => $request['ssb_second_gender'],
                        'dob' => date("Y-m-d", strtotime(convertDate($request['ssb_second_dob']))),
                        'age' => $request['ssb_second_age'],
                        'percentage' => $request['ssb_second_percentage'],
                        'phone_number' => $request['ssb_second_mobile_no'],
                        'created_at' => $request['created_at'],
                    );
                    $resinvData2 = \App\Models\Memberinvestmentsnominees::create($invData2ssb);
                }

                /*******************  Head Implement start ************************/


                $jv_unique_id = $ssb_account_tran_id_to = $ssb_account_tran_id_from = $cheque_type = $cheque_id = $cheque_bank_to_name = $cheque_bank_to_branch = $cheque_bank_to_ac_no = $cheque_bank_to_ifsc = $ssb_account_id_to = $transction_bank_from_id = $transction_bank_from_ac_id = $transction_bank_to_name = $transction_bank_to_ac_no = $transction_bank_to_branch = $transction_bank_to_ifsc = $cheque_bank_from_id = $cheque_bank_ac_from_id = NULL;
                $amount_to_name = NULL;
                $amount_from_id = NULL;
                $amount_from_name = NULL;
                $amount_to_id = NULL;
                $amount_to_id = NULL;

                $bank_id = NULL;
                $bank_ac_id = NULL;
                $associate_id_admin = 1;


                $ssbAmount = $request['ssb_amount'];
                $daybookRefssb = CommanTransactionsController::createBranchDayBookReferenceNew($ssbAmount, $globaldate);
                $refIdssb = $daybookRefssb;
                $currency_code = 'INR';
                $headPaymentModessb = 0;
                $payment_type_ssb = 'CR';
                $type_idssb = $ssbAccountId;
                $entry_date = date("Y-m-d", strtotime(convertDate($globaldate)));
                $entry_time = date("H:i:s", strtotime(convertDate($globaldate)));
                $created_by = 2;
                $created_by_id = Auth::user()->id;
                $created_at = date("Y-m-d H:i:s", strtotime(convertDate($globaldate)));
                $updated_at = date("Y-m-d H:i:s", strtotime(convertDate($globaldate)));
                $typeHeadssb = 4;
                $sub_typeHeadssb = 41;
                $ssbDesDR = 'Cash A/c Dr ' . $ssbAmount . '/-';
                $ssbDesCR = 'To SSB (' . $investmentAccountNoSsb . ')  A/c Cr ' . $ssbAmount . '/-';
                $ssbDes = 'Amount received for Account opening SSB(' . $investmentAccountNoSsb . ') through cash(' . $branchCode . ')';
                $ssbDesMem = 'Account opening SSB(' . $investmentAccountNoSsb . ') through cash(' . $branchCode . ')';

                $v_no = NULL;
                $v_date = NULL;
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

                $head1ssb = 1;
                $head2ssb = 8;
                $head3ssb = 20;
                $head4ssb = 56;
                $head5ssb = NULL;
                //branch day book entry +
                $daybookssb = CommanTransactionsController::NewFieldBranchDaybookCreate($refIdssb, $branch_id, $typeHeadssb, $sub_typeHeadssb, $type_idssb, 1, $memberId, $branch_id_to = NULL, $branch_id_from = NULL, $opening_balance = NULL, $ssbAmount, $closing_balance = NULL, $ssbDes, $ssbDesDR, $ssbDes, $payment_type_ssb, $headPaymentModessb, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $is_contra = NULL, $contra_id = NULL, $created_at, $updated_at, $createAccount['ssb_transaction_id'], $ssb_account_id_to, $cheque_bank_from_id, $cheque_bank_ac_from_id, $cheque_bank_to_name, $cheque_bank_to_branch, $cheque_bank_to_ac_no, $cheque_bank_to_ifsc, $transction_bank_from_id, $transction_bank_from_ac_id, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc, 0, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $jv_unique_id, $cheque_type, $cheque_id);


                // Investment head entry +
                $allTranssb = CommanTransactionsController::headTransactionCreate($refIdssb, $branch_id, $bank_id, $bank_ac_id, $head4ssb, $typeHeadssb, $sub_typeHeadssb, $type_idssb, $associate_id_admin, $memberId, $branch_id_to = NULL, $branch_id_from = NULL, $opening_balance = NULL, $ssbAmount, $closing_balance = NULL, $ssbDes, $payment_type_ssb, $headPaymentModessb, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $createAccount['ssb_transaction_id'], $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $cheque_bank_to_name, $cheque_bank_to_branch, $cheque_bank_to_ac_no, $cheque_bank_to_ifsc, $transction_bank_from_id, $transction_bank_from_ac_id, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc, $cheque_bank_from_id, $cheque_bank_ac_from_id);

                // Member transaction  +
                $memberTranssb = CommanTransactionsController::NewFieldAddMemberTransactionCreate($refIdssb, $typeHeadssb, $sub_typeHeadssb, $type_idssb, $associate_id_admin, $memberId, $branch_id, $bank_id, $bank_ac_id, $ssbAmount, $ssbDesMem, $payment_type_ssb, $headPaymentModessb, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $createAccount['ssb_transaction_id'], $ssb_account_id_to, $cheque_bank_from_id, $cheque_bank_ac_from_id, $cheque_bank_to_name, $cheque_bank_to_branch, $cheque_bank_to_ac_no, $cheque_bank_to_ifsc, $transction_bank_from_id, $transction_bank_from_ac_id, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $jv_unique_id, $cheque_type, $cheque_id);


                // branch cash  head entry +
                $head1ssbC = 2;
                $head2ssbC = 10;
                $head3ssbC = 28;
                $head4ssbC = 71;
                $head5ssbC = NULL;
                $allTranssbcash = CommanTransactionsController::headTransactionCreate($refIdssb, $branch_id, $bank_id, $bank_ac_id, $head3ssbC, $typeHeadssb, $sub_typeHeadssb, $type_idssb, $associate_id_admin, $memberId, $branch_id_to = NULL, $branch_id_from = NULL, $opening_balance = NULL, $ssbAmount, $closing_balance = NULL, $ssbDes, 'DR', $headPaymentModessb, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $createAccount['ssb_transaction_id'], $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $cheque_bank_to_name, $cheque_bank_to_branch, $cheque_bank_to_ac_no, $cheque_bank_to_ifsc, $transction_bank_from_id, $transction_bank_from_ac_id, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc, $cheque_bank_from_id, $cheque_bank_ac_from_id);


                //Balance   entry +
                $branchCash = CommanTransactionsController::checkCreateBranchCash($branch_id, $created_at, $ssbAmount, 0);
                $branchClosing = CommanTransactionsController::checkCreateBranchClosing($branch_id, $created_at, $ssbAmount, 0);
                /*******************  Head Implement start ************************/
            }

            // if($investmentAccountNoSsb=='' || $investmentAccountNoRd=='')
            // {
            //             $dataMsg['msg_type']='error';
            //             $dataMsg['msg']='Associate not created.Try again';
            //             $dataMsg['reciept_generate ']='no';
            //             $dataMsg['reciept_id']=0;
            // }
            if ($investmentAccountNoSsb == '') {
                $dataMsg['msg_type'] = 'error';
                $dataMsg['msg'] = 'Associate not created.Try again';
                $dataMsg['reciept_generate '] = 'no';
                $dataMsg['reciept_id'] = 0;
            } else {
                // pass fa id 2 for Associate
                $getfaCode = getFaCode(2);
                $faCodeAssociate = $getfaCode->code;
                $getMiCodeAssociate = getAssociateMiCodeNew($memberId, $branch_id);
                if (!empty($getMiCodeAssociate)) {
                    if ($getMiCodeAssociate->associate_micode == 9999998) {
                        $miCodeAddAssociate = $getMiCodeAssociate->associate_micode + 2;
                    } else {
                        $miCodeAddAssociate = $getMiCodeAssociate->associate_micode + 1;
                    }
                } else {
                    $miCodeAddAssociate = 1;
                }
                $miCodeAssociate = str_pad($miCodeAddAssociate, 5, '0', STR_PAD_LEFT);
                // genarate Member id
                $getmemberID = $branchCode . $faCodeAssociate . $miCodeAssociate;

                $dataAssociate['associate_form_no'] = $request['form_no'];
                $dataAssociate['associate_join_date'] = date("Y-m-d", strtotime(convertDate($request['application_date'])));
                $dataAssociate['associate_no'] = $getmemberID;
                $dataAssociate['is_associate'] = 1;
                $dataAssociate['associate_status'] = 1;
                $dataAssociate['associate_micode'] = $miCodeAssociate;
                $dataAssociate['associate_facode'] = $faCodeAssociate;
                $dataAssociate['associate_branch_id'] = $branch_id;
                $dataAssociate['associate_branch_code'] = $branchCode;
                //---------------- Add branch field -----------

                $dataAssociate['associate_branch_id_old'] = $branch_id;
                $dataAssociate['associate_branch_code_old'] = $branchCode;

                $dataAssociate['associate_senior_code'] = $request['senior_code'];
                $dataAssociate['associate_senior_id'] = $request['senior_id'];
                $dataAssociate['current_carder_id'] = $request['current_carder'];
                if ($request['ssb_account'] == 0) {
                    $dataAssociate['ssb_account'] = $investmentAccountNoSsb;
                }
                if ($request['rd_account'] == 0) {
                    $dataAssociate['rd_account'] = $investmentAccountNoRd;
                }
                $memberDataUpdate = Member::find($memberId);
                $memberDataUpdate->update($dataAssociate);


                if (isset($_POST['dep_first_name']) && $_POST['dep_first_name'] != '') {
                    $associateDependent1['member_id'] = $memberId;
                    $associateDependent1['name'] = $_POST['dep_first_name'];

                    if ($_POST['dep_age'] != '') {
                        $associateDependent1['age'] = $_POST['dep_age'];
                    }
                    if ($_POST['dep_relation'] != '') {
                        $associateDependent1['relation'] = $_POST['dep_relation'];
                    }
                    if ($_POST['dep_income'] != '') {
                        $associateDependent1['monthly_income'] = $_POST['dep_income'];
                    }

                    $associateDependent1['gender'] = $_POST['dep_gender'];
                    $associateDependent1['marital_status'] = $_POST['dep_marital_status'];
                    $associateDependent1['living_with_associate'] = $_POST['dep_living'];
                    $associateDependent1['dependent_type'] = $_POST['dep_type'];
                    $associateDependent1['created_at'] = $request['created_at'];
                    $associateInsert1 = \App\Models\AssociateDependent::create($associateDependent1);
                }

                //print_r($_POST);die;

                if (isset($_POST['dep_first_name1'])) {
                    if (!empty($_POST['dep_first_name1'])) {
                        foreach (($_POST['dep_first_name1']) as $key => $option) {
                            if (isset($_POST['dep_first_name1'][$key]) && $_POST['dep_first_name1'][$key] != '') {
                                $associateDependent['member_id'] = $memberId;
                                $associateDependent['name'] = $_POST['dep_first_name1'][$key];
                                if ($_POST['dep_age1'][$key] != '') {
                                    $associateDependent['age'] = $_POST['dep_age1'][$key];
                                }
                                if ($_POST['dep_relation1'][$key] != '') {
                                    $associateDependent['relation'] = $_POST['dep_relation1'][$key];
                                }
                                if ($_POST['dep_income1'][$key] != '') {
                                    $associateDependent['monthly_income'] = $_POST['dep_income1'][$key];
                                }
                                $associateDependent['gender'] = $_POST['dep_gender1'][$key];
                                $associateDependent['marital_status'] = $_POST['dep_marital_status1'][$key];
                                $associateDependent['living_with_associate'] = $_POST['dep_living1'][$key];
                                $associateDependent['dependent_type'] = $_POST['dep_type1'][$key];
                                $associateDependent['created_at'] = $request['created_at'];
                                $associateInsert = \App\Models\AssociateDependent::create($associateDependent);
                            }
                        }
                    }
                }
                $associateGuarantor['member_id'] = $memberId;
                $associateGuarantor['first_name'] = $request['first_g_first_name'];
                $associateGuarantor['first_mobile_no'] = $request['first_g_Mobile_no'];
                $associateGuarantor['first_address'] = $request['first_g_address'];
                $associateGuarantor['second_name'] = $request['second_g_first_name'];
                $associateGuarantor['second_mobile_no'] = $request['second_g_Mobile_no'];
                $associateGuarantor['second_address'] = $request['second_g_address'];
                $associateGuarantor['created_at'] = $request['created_at'];
                $associateInsert = \App\Models\AssociateGuarantor::create($associateGuarantor);

                /* ***************   associate tree start ****************** */
                $getParentID = \App\Models\AssociateTree::Where('member_id', $request['senior_id'])->first();

                $associateTree['member_id'] = $memberId;
                $associateTree['parent_id'] = $getParentID->id;
                $associateTree['senior_id'] = $request['senior_id'];
                $associateTree['carder'] = $request['current_carder'];
                $associateTree['created_at'] = $request['created_at'];
                $associateTreeInsert = \App\Models\AssociateTree::create($associateTree);

                /* ***************   associate tree end ****************** */
                /****associate target entry****/
                $com_type = 2;
                $member_carder = $request['current_carder'];
                $commistionMember = CommanTransactionsController::commissionDistributeMember($request['senior_id'], $memberId, $com_type, $branch_id, $member_carder);
                /****associate target entry****/
                if (isset($request['rd_account'])) {
                    $rdset = 1;
                } else {
                    $rdset = 0;
                }

                if ($request['rd_account'] == 0 && $request['ssb_account'] == 0 && $rdset == 1) {

                    $amountArray1 = array('1' => $request['ssb_amount'], '2' => $request['rd_amount']);
                    $typeArray = array('1' => 1, '2' => 2);
                    $receipts_for = 4;
                    $createRecipt = CommanTransactionsController::createPaymentRecipt(0, 0, $memberId, $branch_id, $branchCode, $amountArray1, $typeArray, $receipts_for, $account_no = '0');
                    $recipt_id = $createRecipt;
                    $isReceipt = 'yes';
                } elseif ($request['rd_account'] == 0 && $request['ssb_account'] == 1 && $rdset == 1) {
                    $amountArray1 = array('1' => $request['rd_amount']);
                    $typeArray = array('1' => 2);
                    $receipts_for = 4;
                    $createRecipt = CommanTransactionsController::createPaymentRecipt(0, 0, $memberId, $branch_id, $branchCode, $amountArray1, $typeArray, $receipts_for, $account_no = '0');
                    $recipt_id = $createRecipt;
                    $isReceipt = 'yes';
                } elseif ($request['rd_account'] == 1 && $request['ssb_account'] == 0 && $rdset == 1) {
                    $amountArray1 = array('1' => $request['ssb_amount']);
                    $typeArray = array('1' => 1);
                    $receipts_for = 4;
                    $createRecipt = CommanTransactionsController::createPaymentRecipt(0, 0, $memberId, $branch_id, $branchCode, $amountArray1, $typeArray, $receipts_for, $account_no = '0');
                    $recipt_id = $createRecipt;
                    $isReceipt = 'yes';
                } elseif ($request['ssb_account'] == 0) {
                    $amountArray1 = array('1' => $request['ssb_amount']);
                    $typeArray = array('1' => 1);
                    $receipts_for = 4;
                    $createRecipt = CommanTransactionsController::createPaymentRecipt(0, 0, $memberId, $branch_id, $branchCode, $amountArray1, $typeArray, $receipts_for, $account_no = '0');
                    $recipt_id = $createRecipt;
                    $isReceipt = 'yes';
                }

                $dataMsg['msg_type'] = 'success';
                $dataMsg['msg'] = 'Associate created Successfully';
                $dataMsg['reciept_generate '] = $isReceipt;
                $dataMsg['reciept_id'] = $recipt_id;
            }
            $contactNumber = array();
            $contactNumber[] = $memberDataUpdate->mobile_no;

            $ssbGetDetail = SavingAccount::where('id', $ssbAccountId)->first();
            // print_r($ssbAccountId);die;
            $invGetDetail = \App\Models\Memberinvestments::where('account_number', $investmentAccountNoRd)->first();
            // print_r($invGetDetail->created_at);die;

            // 	$text = 'Dear Member, Thank You for Choosing S.B. Micro Finance association Your Associate A/c No. '. $dataAssociate['associate_no'].', Saving A/C '.
            // 		SavingAccount::find($ssbAccountId)->account_no .' is Created on '. $ssbGetDetail->created_at->format('d M Y') . ' with Rs. '. round($request['ssb_amount'],2).' CR, Recurring A/c No. '
            //     .$investmentAccountNoRd.' is Created on '. $invGetDetail->created_at->format('d M Y').' with Rs. '.round($invGetDetail->deposite_amount,2).'. Have a good day';

            if ($ssbGetDetail && $invGetDetail) {
                $text = 'Dear Member, Thank You for Choosing S.B. Micro Finance association. Your Associate Code ' . $dataAssociate['associate_no'] . ', Saving A/C ' .
                    SavingAccount::find($ssbAccountId)->account_no . ' is Created on ' . $ssbGetDetail->created_at->format('d M Y') . ' with Rs. ' . round($request['ssb_amount'], 2) . ' CR, Recurring A/c No. '
                    . $investmentAccountNoRd . ' is Created on ' . $invGetDetail->created_at->format('d M Y') . ' with Rs. ' . round($invGetDetail->deposite_amount, 2) . ' CR. Have a good day';
            } elseif ($ssbGetDetail) {
                $text = 'Dear Member, Thank You for Choosing S.B. Micro Finance association. Your Associate Code ' . $dataAssociate['associate_no'] . ', Saving A/C ' .
                    SavingAccount::find($ssbAccountId)->account_no . ' is Created on ' . $ssbGetDetail->created_at->format('d M Y') . ' with Rs. ' . round($request['ssb_amount'], 2) . ' CR. Have a good day';
            }

            $templateId = 1201160311561236445;

            $sendToMember = new Sms();
            $sendToMember->sendSms($contactNumber, $text, $templateId);
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            $dataMsg['msg_type'] = 'error';
            $dataMsg['msg'] = $ex->getMessage();
            $dataMsg['reciept_generate '] = 0;
            $dataMsg['reciept_id'] = 0;
            return json_encode($dataMsg);
        }
        // create transaction

        return json_encode($dataMsg);
    }
    public function associateDetail($id)
    {
        $data['title'] = 'Associate | Detail';
        $data['memberDetail'] = Member::where('id', $id)->first();


        $data['idProofDetail'] = \App\Models\MemberIdProof::where('member_id', $id)->first();
        $data['guarantorDetail'] = \App\Models\AssociateGuarantor::where('member_id', $id)->first();
        $data['dependentDetail'] = \App\Models\AssociateDependent::where('member_id', $id)->get();
        $recipt = Receipt::where('member_id', $id)->where('receipts_for', 4)->first();
        $data['recipt'] = ($recipt) ? $recipt->id : 0;


        return view('templates.branch.associate_management.detail', $data);
    }

    /**
     * Get Member detail through member code.
     * Route: ajax call from - /branch/associate/registration
     * Method: Post
     * @param  \Illuminate\Http\Request  $request
     * @return JSON array
     */
    public function getMemberData(Request $request)
    {

        $data = Member::where('member_id', $request->code)->where('status', 1)->where('is_deleted', 0)->first();
        if ($data) {
            if ($data->is_block == 1) {
                return \Response::json(['view' => 'No data found', 'msg_type' => 'error2']);
            } else {
                if ($data->is_associate == 0) {
                    $id = $data->id;
                    $idProofDetail = \App\Models\MemberIdProof::where('member_id', $id)->first();
                    $nomineeDetail = \App\Models\MemberNominee::where('member_id', $id)->first();
                    $nomineeDOB = '';
                    if ($nomineeDetail->dob) {
                        $nomineeDOB = date("d/m/Y", strtotime($nomineeDetail->dob));
                    }

                    return Response::json(['view' => view('templates.branch.associate_management.partials.member_detail', ['memberData' => $data, 'idProofDetail' => $idProofDetail])->render(), 'msg_type' => 'success', 'id' => $id, 'nomineeDetail' => $nomineeDetail, 'nomineeDOB' => $nomineeDOB]);
                } else {
                    return Response::json(['view' => 'No data found', 'msg_type' => 'error1']);
                }
            }
        } else {
            return Response::json(['view' => 'No data found', 'msg_type' => 'error']);
        }
    }

    /**
     * Get Senior detail through senior code.
     * Route: ajax call from - /branch/member/registration
     * Method: Post
     * @param  \Illuminate\Http\Request  $request
     * @return JSON array
     */
    public function getSeniorDetail(Request $request)
    {
        $a = array("id", "first_name", "last_name", 'mobile_no', 'address', 'current_carder_id', 'associate_status', 'is_block');
        $data = memberFieldDataStatus($a, $request->code, 'associate_no');
        $resCount = count($data);
        $carder = "";
        $carder_id = "";
        $msg = '0';
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

        $return_array = compact('data', 'resCount', 'carder', 'carder_id', 'msg');
        return json_encode($return_array);
    }

    /**
     * Get carder below Senior carder.
     * Route: ajax call from - /branch/member/registration
     * Method: Post
     * @param  \Illuminate\Http\Request  $request
     * @return JSON array
     */
    public function getCarderAssociate(Request $request)
    {
        //print_r($request->id);die;

        if ($request->id > 1) {
            $carde = \App\Models\Carder::where('id', '<', $request->id)->where('status', 1)->where('is_deleted', 0)->limit(1)->get(['id', 'name', 'short_name']);
        } else {
            $carde = \App\Models\Carder::where('id', '<=', $request->id)->where('status', 1)->where('is_deleted', 0)->limit(1)->get(['id', 'name', 'short_name']);
        }
        $return_array = compact('carde');
        return json_encode($return_array);
    }

    /**
     * Member's ssb account  exists or not .
     * Route: ajax call from - /branch/associate/registration
     * Method: Post
     * @param  \Illuminate\Http\Request  $request
     * @return JSON array
     */
    public function checkSsbAcount(Request $request)
    {
        $resCount = 0;
        $data = getInvestmentAccount($request->member_id, $request->account_no);
        if (!empty($data)) {
            $resCount = 1;
        }
        $return_array = compact('resCount');
        return json_encode($return_array);
    }
    /**
     * Member's ssb account  Etail .
     * Route: ajax call from - /branch/associate/registration
     * Method: Post
     * @param  \Illuminate\Http\Request  $request
     * @return JSON array
     */
    public function associateSsbAccountGet(Request $request)
    {
        $resCount = 0;
        $account_no = '';
        $balance = '';
        $name = '';
        $data = getMemberSsbAccountDetail($request->member_id);
        $member = Member::where('id', $request->member_id)->first();
        if (!empty($data)) {
            $account_no = $data->account_no;
            $balance = $data->balance;
            $resCount = 1;
        }
        if (!empty($member)) {
            $name = $member->first_name . ' ' . $member->last_name;
        }
        $return_array = compact('account_no', 'balance', 'resCount', 'name');
        return json_encode($return_array);
    }

    /**
     * Member's ssb account Balance  exists or not .
     * Route: ajax call from - /branch/associate/registration
     * Method: Post
     * @param  \Illuminate\Http\Request  $request
     * @return JSON array
     */
    public function checkSsbAcountBalance(Request $request)
    {
        //print_r($request->member_id);die;
        $resCount = 0;
        $data = getMemberSsbAccountDetail($request->member_id);
        //print_r($data);die;
        if (!empty($data)) {
            if ($data->balance >= $request->rd_amount) {
                $resCount = 1;
            }
        } else {
            $resCount = 2;
        }
        $return_array = compact('resCount');
        return json_encode($return_array);
    }

    /**
     * Member's rd account Balance  exists or not .
     * Route: ajax call from - /branch/associate/registration
     * Method: Post
     * @param  \Illuminate\Http\Request  $request
     * @return JSON array
     */
    public function associateRdbAccountGet(Request $request)
    {
        $data = Memberinvestments::where('id', $request->rdAccountId)->select('account_number', 'deposite_amount', 'member_id')->first();
        $return_array = [];
        if ($data) {
            $return_array['account_id'] = $data->account_number;
            $return_array['amount'] = $data->deposite_amount;
            $return_array['name'] = Member::find($data->member_id)->first_name;
        } else {
            $return_array['account_id'] = '';
            $return_array['amount'] = '';
            $return_array['name'] = '';
        }
        return json_encode($return_array);
    }

    public function associateRdbAccounts(Request $request)
    {
        $associateSettings = \App\Models\companies::whereStatus('1')->whereHas('companyAssociate', function ($query) {
            $query->whereStatus('1')->select(['id', 'status', 'company_id']);
        })->first(['id', 'status']);
        $data = Memberinvestments::where(['plan_id' => $request->planId, 'customer_id' => $request->customerId, 'company_id' => $associateSettings->id])->where('deposite_amount', '>=', 500)->where('tenure', '>=', 5)->pluck('account_number', 'id');
        return json_encode($data);
    }
    /**
     * Show recipt detail after create Associate
     * Route: branch/associate/receipt/ 
     * Method: get 
     * @param  $id,$msg
     * @return  array()  Response
     */
    public function reciept($id)
    {
        $data['title'] = 'Associate | Recipt';
        $data['type'] = '1';
        $data['recipt'] = Receipt::with(['memberReceipt:id,member_id,first_name,last_name,mobile_no,address,ssb_account,rd_account,associate_no', 'branchReceipt:id,name,branch_code'])->where('id', $id)->first();
        $data['recipt_amount'] = ReceiptAmount::where('receipt_id', $id)->get(['receipt_id', 'amount', 'type_label']);
        $data['total_amount'] = ReceiptAmount::where('receipt_id', $id)->sum('amount');
        $data['ssb_recipt_amount'] = ReceiptAmount::where('receipt_id', $id)->where('type_label', 1)->first(['receipt_id', 'amount']);
        return view('templates.branch.associate_management.recipt', $data);
    }


    /**
     * Show associate commission list.
     * Route: /associate/commission 
     * Method: get 
     * @return  view
     */
    public function associateCommission()
    {
        if (!in_array('Associate Commission', auth()->user()->getPermissionNames()->toArray())) {
            return redirect()->route('branch.dashboard');
        }

        $data['title'] = 'Associate Commission | Listing';
        $data['branch'] = Branch::where('status', 1)->get();
        $data['years'] = CommissionLeaserMonthly::select('year')
            ->where('is_deleted', 0)
            ->where('year', '!=', 2022)
            ->distinct()
            ->get('year');
        $data['dates'] = CommissionLeaserMonthly::select('month', 'year')
            ->where('is_deleted', 0)
            ->where('year', '!=', 2022)
            ->distinct()
            ->get();
        return view('templates.branch.associate_management.commission', $data);
    }

    /**
     * Get associate list
     * Route: ajax call from - /admin/associate
     * Method: Post 
     * @param  \Illuminate\Http\Request  $request
     * @return JSON array
     */
    public function associateCommissionList(Request $request)
    {
        if ($request->ajax()) {

            $arrFormData = array();
            if (!empty($_POST['searchform'])) {
                foreach ($_POST['searchform'] as $frm_data) {
                    $arrFormData[$frm_data['name']] = $frm_data['value'];
                }
            }
            if (isset($arrFormData['is_search']) && $arrFormData['is_search'] == 'yes') {
                $year = $arrFormData['year'];
                $month = $arrFormData['month'];
                $sday = 1;
                $company_id = $arrFormData['company_id'];
                $startDate = Carbon::create($year, $month, $sday)->format('Y-m-d');
                $endDate = Carbon::create($year, $month)->endOfMonth()->toDateString();
                $b_id = getUserBranchId(Auth::user()->id)->id;
                // $data = AssociateCommission::with('member','investment')->where('type','3');
                //$data = Member::with('associate_branch')->where('member_id','!=','9999999')->where('is_associate',1);
                $data = Member::with(['associate_branch' => function ($query) {
                    $query->select('id', 'name', 'branch_code', 'sector', 'regan', 'zone');
                }])
                    ->with([
                        'seniorData' => function ($q) {
                            $q->select(['id', 'first_name', 'last_name', 'current_carder_id'])
                                ->with([
                                    'getCarderNameCustom' => function ($q) {
                                        $q->select('id', 'name', 'short_name');
                                    }
                                ]);
                        }
                    ])
                    ->with(['getCarderNameCustom' => function ($q) {
                        $q->select('id', 'name', 'short_name');
                    }])
                    ->withCount(['AssociateTotalCommission' => function ($q) {
                        $q->select(DB::raw('sum(commission_amount)'));
                    }])
                    ->where('member_id', '!=', '9999999')->where('is_associate', 1);


                $data = $data->where('associate_branch_id', '=', $b_id);

                if (isset($arrFormData['is_search']) && $arrFormData['is_search'] == 'yes') {
                    if ($arrFormData['associate_code'] != '') {
                        $associate_code = $arrFormData['associate_code'];
                        $data = $data->where('associate_no', 'LIKE', '%' . $associate_code . '%');
                    }
                    if ($arrFormData['associate_name'] != '') {
                        $name = $arrFormData['associate_name'];
                        $data = $data->where(function ($query) use ($name) {
                            $query->where('first_name', 'LIKE', '%' . $name . '%')->orWhere('last_name', 'LIKE', '%' . $name . '%')->orWhere(DB::raw('concat(first_name," ",last_name)'), 'LIKE', "%$name%");
                        });
                    }

                    $data1 = $data->orderby('id', 'DESC')->get();
                    $count = count($data1);

                    $data = $data->orderby('id', 'DESC')->offset($_POST['start'])->limit($_POST['length'])->get();

                    $totalCount = Member::where('member_id', '!=', '9999999')->where('is_associate', 1)->where('associate_branch_id', '=', $b_id)->count();

                    $sno = $_POST['start'];
                    $rowReturn = array();


                    foreach ($data as $row) {
                        $sno++;
                        $val['DT_RowIndex'] = $sno;
                        $val['branch_name'] = $row['associate_branch']->name . '(' . $row['associate_branch']->branch_code . ')';
                        $val['row_id'] = $row->id;
                        // $val['branch_code'] = $row['associate_branch']->branch_code;
                        $val['sector_name'] = $row['associate_branch']->sector;
                        $val['region_name'] = $row['associate_branch']->regan;
                        $val['zone_name'] = $row['associate_branch']->zone;
                        $val['associate_name'] = $row->first_name . ' ' . $row->last_name;
                        $val['associate_code'] = $row->associate_no;
                        $val['associate_carder'] = $row['getCarderNameCustom']->name . '(' . $row['getCarderNameCustom']->short_name . ')';

                        $val['senior_code'] = $row->associate_senior_code;
                        $val['senior_name'] = $row['seniorData']->first_name . ' ' . $row['seniorData']->last_name;
                        $val['senior_carder'] = $row['seniorData']['getCarderNameCustom']->name . '(' . $row['seniorData']['getCarderNameCustom']->short_name . ')';
                        //$val['total_amount'] = getAssociateTotalCommissionDistribute($row->id,$startDate,$endDate,'total_amount');
                        //$row['AssociateTotalCommission']->aggregate;//
                        // if ($row->associate_total_commission_count) {
                        //     $val['commission_amount'] = number_format($row->associate_total_commission_count, 2, '.', '');
                        // } else {
                        //     $val['commission_amount'] = 0;
                        // }
                        //getAssociateTotalCommissionDistribute($row->id,$startDate,$endDate,'commission_amount');
                        //$val['collection_amount']= getTotalCollection($row->id,$startDate,$endDate);



                        if (($year == 2022 && $month <= 11) || ($year < 2022)) {
                            $val['commission_amount'] = getAssociateTotalCommissionAdmin($row->id, $startDate, $endDate, 'commission_amount') . '&#8377';
                            $val['collection_amount'] = getTotalCollection($row->id, $startDate, $endDate) . '&#8377'; //plan no 4,9 not include in this and loan recovery also not added
                            $val['collection_amount_all'] = getTotalCollection_all($row->id, $startDate, $endDate) . '&#8377'; //Investment total renewal all type add and loan recovery also not added
                            $btn = 'N/A';
                        } else {
                            $val['commission_amount'] = getAssociateTotalCommissionAdminNew($row->id, $year, $month, $company_id) . '&#8377';

                            $val['collection_amount'] = getTotalCollectionNew2($row->id, $year, $month, $company_id) . '&#8377'; //plan no 4,9 not include in this and loan recovery also not added
                            $val['collection_amount_all'] = getTotalCollection_allNew($row->id, $year, $month, $company_id) . '&#8377'; //Investment total renewal all type add and loan recovery also not added
                            $btn = '';
                            $url = URL::to("branch/associate/commission-detail/" . $row->id . "?&year=$year&month=$month");
                            // $url1 = URL::to("branch/associate/loan-commission-detail/" . $row->id . "");
                             $url1 = URL::to("branch/associate/loan-commission-detail/" . $row->id . "?&year=$year&month=$month");
                        $btn .= '<a class=" " href="' . $url . '" title="Associate Investment commission Detail"><i class="fas fa-percent text-default"></i> </a>  ';
                        $btn .= ' <a class=" " href="' . $url1 . '" title="Associate Loan commission Detail"> <i class="fas fa-percent  text-primary"></i></a>  ';
                        
                        }
                        
                        $val['action'] = $btn;
                        // $val['action'] = "N/a";

                        $rowReturn[] = $val;
                    }
                    $output = array("draw" => $_POST['draw'], "recordsTotal" => $totalCount, "recordsFiltered" => $count, "data" => $rowReturn,);

                    return json_encode($output);
                }
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
     * Show associate commission list.
     * Route: /associatecommission 
     * Method: get 
     * @return  view
     */
    public function associateCommissionDetail($id)
    {

        $data['title'] = 'Associate Commission Detail | Listing';
        $data['member'] = Member::where('id', $id)->first();

        $data['years'] = CommissionLeaserMonthly::where('is_deleted', 0)
            ->where('year', '>', 2022)
            ->distinct()
            ->get('year');
        $data['months'] = CommissionLeaserMonthly::where('is_deleted', 0)
            ->where('year', '>', 2022)
            ->distinct()
            ->get(['month', 'year']);
        $data['plan'] = Plans::where('plan_category_code','!=','S')->get(['id', 'name']);

        return view('templates.branch.associate_management.commissionDetail', $data);
    }
    /**
     * Get associate list
     * Route: ajax call from - /admin/associate
     * Method: Post 
     * @param  \Illuminate\Http\Request  $request
     * @return JSON array
     */
    public function associateCommissionDetailList(Request $request)
    {

        if ($request->ajax()) {

            $arrFormData['year'] = $request->year;
            $arrFormData['month'] = $request->month;
            $arrFormData['plan_id'] = $request->plan_id;
            $arrFormData['is_search'] = $request->is_search;
            $arrFormData['id'] = $request->id;

            $arrFormData = array();
            if (!empty($_POST['searchform'])) {
                foreach ($_POST['searchform'] as $frm_data) {
                    $arrFormData[$frm_data['name']] = $frm_data['value'];
                }
            }
            if (isset($arrFormData['is_search']) && $arrFormData['is_search'] == 'yes') {
                $data = AssociateMonthlyCommission::where('assocaite_id', $arrFormData['id'])->with(['investment' => function ($q) {
                    $q->select('id', 'plan_id', 'account_number');
                }])->with('investment.plan:id,name')
                    ->where('type', 1)->where('is_deleted', '0');
                if ($arrFormData['year'] != '') {
                    $year = $arrFormData['year'];
                    $data = $data->where('commission_for_year', $year);
                }
                if ($arrFormData['month'] != '') {
                    $month = $arrFormData['month'];
                    $data = $data->where('commission_for_month', $month);
                }
                if (isset($arrFormData['plan_id']) && $arrFormData['plan_id'] != '') {
                    $meid = $arrFormData['plan_id'];
                    $data = $data->whereHas('investment', function ($query) use ($meid) {
                        $query->where('member_investments.plan_id', $meid);
                    });
                }
            } else {
                if (isset($arrFormData['is_search']) && $arrFormData['is_search'] == 'no') {
                    $output = array(
                        "draw" => 0,
                        "recordsTotal" => 0,
                        "recordsFiltered" => 0,
                        "data" => 0,
                    );
                    return json_encode($output);
                }
            }
            $count = $data->orderby('id', 'DESC')->count('id');
            $data = $data->orderby('id', 'DESC')->offset($_POST['start'])->limit($_POST['length'])->get(['id', 'type_id', 'total_amount', 'commission_amount', 'percentage', 'type', 'cadre_from', 'cadre_to', 'month',  'created_at', 'assocaite_id', 'commission_for_year', 'commission_for_month','qualifying_amount']);
            $totalCount = AssociateMonthlyCommission::where('assocaite_id', $arrFormData['id'])->where('type', 1)->where('is_deleted', '0')->count('id');
            $sno = $_POST['start'];
            $rowReturn = array();
            foreach ($data as $row) {
                $sno++;
                $val['DT_RowIndex'] = $sno;
                $monthAbbreviations = [
                    1 => 'Jan',
                    2 => 'Feb',
                    3 => 'Mar',
                    4 => 'Apr',
                    5 => 'May',
                    6 => 'Jun',
                    7 => 'Jul',
                    8 => 'Aug',
                    9 => 'Sep',
                    10 => 'Oct',
                    11 => 'Nov',
                    12 => 'Dec'
                ];
                $val['month'] = $monthAbbreviations[$row->commission_for_month] . '-' . $row->commission_for_year;
                $val['account_number'] = $row['investment']->account_number;
                $val['plan_name'] =  $row['investment']->plan->name;
                $val['total_amount'] = number_format((float) $row->total_amount, 2, '.', '');
                $val['qualifying_amount'] = number_format((float) $row->qualifying_amount, 2, '.', '');
                $val['commission_amount'] = number_format((float) $row->commission_amount, 2, '.', '');
                $val['percentage'] = number_format((float) $row->percentage, 2, '.', '');
                $val['carder_from'] = $row->cadre_from;
                $val['carder_to'] = $row->cadre_to;
                if ($row->cadre_from === 1) {
                    $val['commission_type'] = 'Self';
                } else {
                    $val['commission_type'] = 'Team';
                }
                $rowReturn[] = $val;
            }
            // echo 'hi';
            //  print_r($rowReturn);die;
            $output = array("draw" => $_POST['draw'], "recordsTotal" => $totalCount, "recordsFiltered" => $count, "data" => $rowReturn,);
            return json_encode($output);
        }
    }

    /**
     * Show associate upgrade.
     * Route: /branch/associate-upgrade
     * Method: get 
     * @return  array()  Response
     */
    public function upgrade()
    {

        if (!in_array('Associate Upgrade', auth()->user()->getPermissionNames()->toArray())) {
            return redirect()->route('branch.dashboard');
        }

        $data['title'] = 'Associate | Upgrade';
        $data['branch'] = Branch::where('status', 1)->get();


        return view('templates.branch.associate_management.upgrade', $data);
    }


    /**
     * Get Member detail through member code.
     * Route: ajax call from -admin/associate-register
     * Method: Post
     * @param  \Illuminate\Http\Request  $request
     * @return JSON array
     */
    public function getAssociateData(Request $request)
    {

        $data = Member::where('associate_no', $request->code)->where('is_deleted', 0)->first();
        $type = $request->type;

        if ($data) {
            if ($data->is_block == 1) {
                return \Response::json(['view' => 'No data found', 'msg_type' => 'error2']);
            } else {
                if ($data->associate_status == 1) {


                    $id = $data->id;
                    $carder = $data->current_carder_id;


                    $carder = $data->current_carder_id;
                    $finacialYear = getFinacialYear();
                    $startDate = $finacialYear['dateStart'];
                    $endDate = $finacialYear['dateEnd'];
                    $businessTarget = \App\Models\BusinessTarget::where('carder_id', $carder)->first();
                    $memberCount = \App\Models\AssociateCommission::where('member_id', $id)->where('type', 1)->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate])->count();

                    $commissionSelf = \App\Models\AssociateKotaBusiness::where('member_id', $id)->where('type', 1)->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate])->sum('business_amount');

                    $commissionCredit = getKotaBusinessTeam($id, $startDate, $endDate);
                    //print_r($commissionCredit);die;

                    return \Response::json(['view' => view('templates.branch.associate_management.partials.associate_detail', ['memberData' => $data, 'businessTarget' => $businessTarget, 'memberCount' => $memberCount, 'commissionSelf' => $commissionSelf, 'commissionCredit' => $commissionCredit, 'carder' => $carder, 'type' => $type])->render(), 'msg_type' => 'success', 'id' => $id, 'carder' => $carder]);
                } else {
                    return \Response::json(['view' => 'No data found', 'msg_type' => 'error1']);
                }
            }
        } else {
            return \Response::json(['view' => 'No data found', 'msg_type' => 'error']);
        }
    }

    /**
     * Get carder above Senior carder.
     * Route: ajax call from - admin/associate-register
     * Method: Post
     * @param  \Illuminate\Http\Request  $request
     * @return JSON array
     */
    public function getCarderUpgrade(Request $request)
    {
        //print_r($request->id);die;
        $type = $request->type;
        if ($type == 'upgrade') {
            $carde = \App\Models\Carder::where('id', '!=', 16)->where('id', '>', $request->id)->where('status', 1)->where('is_deleted', 0)->get(['id', 'name', 'short_name']);
        } else {
            $carde = \App\Models\Carder::where('id', '!=', 16)->where('id', '<', $request->id)->where('status', 1)->where('is_deleted', 0)->get(['id', 'name', 'short_name']);
        }
        $return_array = compact('carde');
        return json_encode($return_array);
    }

    /**
     * Route: /admin/associate/upgrade 
     * Method: Post 
     * @param  \Illuminate\Http\Request  $request
     * upgrade associate carder.
     * @return  array()  Response
     */
    public function upgrade_save(Request $request)
    {
        // print_r($_POST);die;

        Session::put('created_at', $request['created_at']);
        $globaldate = $request['created_at'];

        $rules = [
            'associate_code' => ['required', 'numeric'],
            'current_carder_id' => ['required'],
            'upgrade_carder' => ['required'],
        ];

        $customMessages = [
            'required' => 'Please enter :attribute.',
            'numeric' => ':Attribute - Please enter valid.',
            'unique' => ' :Attribute already exists.'

        ];
        $this->validate($request, $rules, $customMessages);

        DB::beginTransaction();

        try {
            $id = $request['member_id'];
            $associate_carder = $request['current_carder_id'];
            $associate_senior_id = $request['associate_senior_id'];
            $senior_current_carder = $request['senior_current_carder_id'];
            $upgrade_carder = $request['upgrade_carder'];
            $associate_code = $request['associate_code'];
            $getCompanyId = Member::where('associate_no', '9999999')->first();

            $companyId = $getCompanyId->id;
            if ($senior_current_carder > $upgrade_carder) {
                $senior_id = $associate_senior_id;
                $getSenior = Member::where('id', $associate_senior_id)->first();

                $senior_code = $getSenior->associate_no;
            } else {
                $senior_id = $companyId;
                $senior_code = $getCompanyId->associate_no;
            }

            $member['current_carder_id'] = $upgrade_carder;
            $member['associate_senior_id'] = $senior_id;
            $member['associate_senior_code'] = $senior_code;
            $memberDataUpdate = Member::find($id);
            $memberDataUpdate->update($member);

            $carderDetail['member_id'] = $id;
            $carderDetail['branch_id'] = $request['branch_id'];
            $carderDetail['carder_id'] = $upgrade_carder;
            $carderDetail['old_carder_id'] = $associate_carder;
            $carderDetail['created_at'] = $globaldate;
            $associateCarderInsert = \App\Models\AssociateCarder::create($carderDetail);
            if ($senior_id != $associate_senior_id) {
                $getMemberId = \App\Models\AssociateTree::Where('member_id', $id)->first();

                $getParentID = \App\Models\AssociateTree::Where('member_id', $senior_id)->first();

                $associateTree['parent_id'] = $getParentID->id;
                $associateTree['senior_id'] = $senior_id;
                $associateTree['carder'] = $upgrade_carder;
                $memberTreeUpdate = \App\Models\AssociateTree::find($getMemberId->id);
                $memberTreeUpdate->update($associateTree);
            } else {
                $getMemberId = \App\Models\AssociateTree::Where('member_id', $id)->first();

                $getParentID = \App\Models\AssociateTree::Where('member_id', $senior_id)->first();

                //$associateTree['parent_id'] = $getParentID->id;
                // $associateTree['senior_id'] = $senior_id;
                $associateTree['carder'] = $upgrade_carder;
                $memberTreeUpdate = \App\Models\AssociateTree::find($getMemberId->id);
                $memberTreeUpdate->update($associateTree);
            }





            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('alert', $ex->getMessage());
        }

        return back()->with('success', 'Associate Carder Upgraded/Promoted Successfully!');
    }

    /**
     * Associate Change status.
     * Route: /admin/associate-upgrade
     * Method: get 
     * @return  array()  Response
     */
    public function active_deactivate()
    {
        if (!in_array('Associate Deactivate or Activate', auth()->user()->getPermissionNames()->toArray())) {
            return redirect()->route('branch.dashboard');
        }
        $data['title'] = 'Associate | Status';
        //$data['branch']=Branch::where('status',1)->get();
        return view('templates.branch.associate_management.status', $data);
    }
    /**
     * Get Member detail through member code.
     * Route: ajax call from -admin/associate-register
     * Method: Post
     * @param  \Illuminate\Http\Request  $request
     * @return JSON array
     */
    public function getAssociateDataAll(Request $request)
    {

        $data = Member::where('associate_no', $request->code)->where('is_deleted', 0)->first();
        $type = $request->type;

        if ($data) {
            if ($data->is_block == 1) {
                return \Response::json(['view' => 'No data found', 'msg_type' => 'error2']);
            } else {
                $id = $data->id;
                $carder = $data->current_carder_id;

                $finacialYear = getFinacialYear();
                $startDate = $finacialYear['dateStart'];
                $endDate = $finacialYear['dateEnd'];

                $carder = $data->current_carder_id;
                $businessTarget = \App\Models\BusinessTarget::where('carder_id', $carder)->first();
                $memberCount = \App\Models\AssociateCommission::where('member_id', $id)->where('type', 1)->count();
                $commissionSelf = \App\Models\AssociateKotaBusiness::where('member_id', $id)->where('type', 1)->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate])->sum('business_amount');
                $commissionCredit = getKotaBusinessTeam($id, $startDate, $endDate);
                //print_r($commissionCredit);die;

                return \Response::json(['view' => view('templates.branch.associate_management.partials.associate_detail', ['memberData' => $data, 'businessTarget' => $businessTarget, 'memberCount' => $memberCount, 'commissionSelf' => $commissionSelf, 'commissionCredit' => $commissionCredit, 'carder' => $carder, 'type' => $type])->render(), 'msg_type' => 'success', 'id' => $id, 'carder' => $carder]);
            }
        } else {
            return \Response::json(['view' => 'No data found', 'msg_type' => 'error']);
        }
    }


    /**
     * Route: /admin/associate/status 
     * Method: Post 
     * @param  \Illuminate\Http\Request  $request
     * upgrade associate carder.
     * @return  array()  Response
     */
    public function status_save(Request $request)
    {

        Session::put('created_at', $request['created_at']);
        $globaldate = $request['created_at'];

        //print_r($_POST);die;
        $rules = [
            'associate_code' => ['required', 'numeric'],
            'current_status' => ['required'],
            'old_status' => ['required'],
        ];

        $customMessages = [
            'required' => 'Please enter :attribute.',
            'numeric' => ':Attribute - Please enter valid.',
            'unique' => ' :Attribute already exists.'

        ];
        $this->validate($request, $rules, $customMessages);

        DB::beginTransaction();

        try {
            $id = $request['member_id'];
            $current_status = $request['current_status'];
            $old_status = $request['old_status'];

            if ($current_status != $old_status) {



                $status['member_id'] = $id;
                $status['branch_id'] = $request['branch_id'];
                $status['status'] = $current_status;
                $status['old_status'] = $old_status;
                $status['created_at'] = $globaldate;
                $associateStatusInsert = \App\Models\AssociateStatus::create($status);

                $member['associate_status'] = $current_status;
                $memberDataUpdate = Member::find($id);
                $memberDataUpdate->update($member);
            }

            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('alert', $ex->getMessage());
        }

        return back()->with('success', 'Associate Status Changed Successfully!');
    }

    /**
     * Associate downgrade carder.
     * Route: /admin/associate-downgrade
     * Method: get 
     * @return  array()  Response
     */
    public function downgrade()
    {

        if (!in_array('Associate Downgrade', auth()->user()->getPermissionNames()->toArray())) {
            return redirect()->route('branch.dashboard');
        }

        $data['title'] = 'Associate | Downgrade ';
        //$data['branch']=Branch::where('status',1)->get();        
        return view('templates.branch.associate_management.downgrade', $data);
    }

    /**
     * Route: /admin/associate/upgrade 
     * Method: Post 
     * @param  \Illuminate\Http\Request  $request
     * upgrade associate carder.
     * @return  array()  Response
     */
    public function downgrade_save(Request $request)
    {

        Session::put('created_at', $request['created_at']);
        $globaldate = $request['created_at'];

        // print_r($_POST);die;
        $rules = [
            'associate_code' => ['required', 'numeric'],
            'current_carder_id' => ['required'],
            'downgrade_carder' => ['required'],
        ];

        $customMessages = [
            'required' => 'Please enter :attribute.',
            'numeric' => ':Attribute - Please enter valid.',
            'unique' => ' :Attribute already exists.'

        ];
        $this->validate($request, $rules, $customMessages);

        DB::beginTransaction();

        try {
            $id = $request['member_id'];
            $associate_carder = $request['current_carder_id'];
            $associate_senior_id = $request['associate_senior_id'];
            $senior_current_carder = $request['senior_current_carder_id'];
            $downgrade_carder = $request['downgrade_carder'];
            $associate_code = $request['associate_code'];
            $getCompanyId = Member::where('associate_no', '9999999')->first();

            $companyId = $getCompanyId->id;
            if ($associate_carder > $downgrade_carder) {
                $senior_id = $associate_senior_id;
                $getSenior = Member::where('id', $associate_senior_id)->first();
                $senior_code = $getSenior->associate_no;

                $getMember = Member::Where('current_carder_id', '>=', $downgrade_carder)->Where('associate_senior_id', $id)->get();

                $getParentID = \App\Models\AssociateTree::Where('member_id', $senior_id)->first();

                if (count($getMember) > 0) {
                    foreach ($getMember as $val) {

                        $getMemberIdChild = \App\Models\AssociateTree::Where('member_id', $val->id)->first();
                        $members['associate_senior_id'] = $senior_id;
                        $members['associate_senior_code'] = $senior_code;
                        $membersDataUpdate = Member::find($val->id);
                        $membersDataUpdate->update($members);

                        $associateTreeChild['parent_id'] = $getParentID->id;
                        $associateTreeChild['senior_id'] = $senior_id;

                        $memberTreeChild = \App\Models\AssociateTree::find($getMemberIdChild->id);
                        $memberTreeChild->update($associateTreeChild);
                    }
                }
            }


            $member['current_carder_id'] = $downgrade_carder;
            $memberDataUpdate = Member::find($id);
            $memberDataUpdate->update($member);

            $carderDetail['member_id'] = $id;
            $carderDetail['branch_id'] = $request['branch_id'];
            $carderDetail['carder_id'] = $downgrade_carder;
            $carderDetail['old_carder_id'] = $associate_carder;
            $carderDetail['created_at'] = $globaldate;
            $associateCarderInsert = \App\Models\AssociateCarder::create($carderDetail);
            $getMemberId = \App\Models\AssociateTree::Where('member_id', $id)->first();

            $associateTree['carder'] = $downgrade_carder;
            $memberTreeUpdate = \App\Models\AssociateTree::find($getMemberId->id);
            $memberTreeUpdate->update($associateTree);

            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('alert', $ex->getMessage());
        }

        return back()->with('success', 'Associate Carder Downgrade Successfully!');
    }


    /**
     * Show associate commission list.
     * Route: /associatecommission 
     * Method: get 
     * @return  view
     */
    public function associateCommissionDetailLoan($id)
    {
        $data['title'] = 'Associate Loan Commission Detail | Listing';
        $data['loan'] = Loans::get();
        $data['member'] = Member::where('id', $id)->first();
        $data['years'] = CommissionLeaserMonthly::where('is_deleted', 0)
        ->where('year', '>', 2022)
        ->distinct()
        ->get('year');
        $data['months'] = CommissionLeaserMonthly::where('is_deleted', 0)
        ->where('year', '>', 2022)
        ->distinct()
        ->get(['month', 'year']);
        return view('templates.branch.associate_management.commissionDetailLoan', $data);
    }

    /**
     * Get associate list
     * Route: ajax call from - /branch/associate
     * Method: Post 
     * @param  \Illuminate\Http\Request  $request
     * @return JSON array
     */
    public function associateCommissionDetailListLoan(Request $request)
    { 
       
        if ($request->ajax()) {
           
            $arrFormData['year'] = $request->year;
            $arrFormData['month'] = $request->month;
            $arrFormData['plan_id'] = $request->plan_id;
            $arrFormData['is_search'] = $request->is_search;
            $arrFormData['id'] = $request->id;
           
            $arrFormData = array();
            if (!empty($_POST['searchform'])) {
                foreach ($_POST['searchform'] as $frm_data) {
                    $arrFormData[$frm_data['name']] = $frm_data['value'];
                }
               
            }
            if (isset($arrFormData['is_search']) && $arrFormData['is_search'] == 'yes') {                
                $data = AssociateMonthlyCommission::with('loan.loan:id,name')
                ->with('loan:id,account_number,loan_type')
                ->with('group_loan.loan:id,name')
                ->with('group_loan:id,account_number,loan_type')
                ->where('assocaite_id', $arrFormData['id'])
                ->whereIn('type', array(2, 3))
                    ->where('is_deleted', '0');
                if ($arrFormData['year'] != '') {
                    $year = $arrFormData['year'];
                    $data = $data->where('commission_for_year', $year);
                }
                if ($arrFormData['month'] != '') {
                    $month = $arrFormData['month'];
                    $data = $data->where('commission_for_month', $month);
                }
                if (isset($arrFormData['plan_id']) && $arrFormData['plan_id'] != '') {
                    $meid = $arrFormData['plan_id'];
                    $data = $data->whereHas('investment', function ($query) use ($meid) {
                        $query->where('member_investments.plan_id', $meid);
                    });
                }
            }else {
                if (isset($arrFormData['is_search']) && $arrFormData['is_search'] == 'no') {
                    $output = array(
                        "draw" => 0,
                        "recordsTotal" => 0,
                        "recordsFiltered" => 0,
                        "data" => 0,
                    );
                    return json_encode($output);
                }

            }
            $count = $data->orderby('id', 'DESC')->count('id');
            $data = $data->orderby('id', 'DESC')->offset($_POST['start'])->limit($_POST['length'])->get(['id', 'type_id', 'total_amount', 'commission_amount', 'percentage', 'type', 'cadre_from', 'cadre_to', 'month',  'created_at', 'assocaite_id', 'commission_for_year', 'commission_for_month','qualifying_amount']);
            $totalCount = AssociateMonthlyCommission::where('assocaite_id', $arrFormData['id'])->whereIn('type', array(2, 3))->where('is_deleted', '0')->count('id');
            $sno = $_POST['start'];
            $rowReturn = array();
            foreach ($data as $row) {
                $sno++;
                $val['DT_RowIndex'] = $sno;
                $monthAbbreviations = [
                    1 => 'Jan',
                    2 => 'Feb',
                    3 => 'Mar',
                    4 => 'Apr',
                    5 => 'May',
                    6 => 'Jun',
                    7 => 'Jul',
                    8 => 'Aug',
                    9 => 'Sep',
                    10 => 'Oct',
                    11 => 'Nov',
                    12 => 'Dec'
                ];
                $val['month'] = $monthAbbreviations[$row->commission_for_month] . '-' . $row->commission_for_year;
                $val['account_number'] = $row['investment']->account_number;
                $val['plan_name'] =  $row['investment']->plan->name;

                if($row->type==2){
                    $val['account_number'] = $row['loan']->account_number;
                    $val['plan_name'] =  $row['loan']['loan']->name;
                }
                else
                {
                    $val['account_number'] = $row['group_loan']->account_number;
                    $val['plan_name'] =  $row['group_loan']['loan']->name;
                }

                
                $val['total_amount'] = number_format((float) $row->total_amount, 2, '.', '');
                $val['qualifying_amount'] = number_format((float) $row->qualifying_amount, 2, '.', '');
                $val['commission_amount'] = number_format((float) $row->commission_amount, 2, '.', '');
                $val['percentage'] = number_format((float) $row->percentage, 2, '.', '');
                $val['carder_from'] = $row->cadre_from;
                $val['carder_to'] = $row->cadre_to;
                if ($row->cadre_from === 1) {
                    $val['commission_type'] = 'Self';
                } else {
                    $val['commission_type'] = 'Team';
                }
                $rowReturn[] = $val;
            }
            // echo 'hi';
            //  print_r($rowReturn);die;
            $output = array("draw" => $_POST['draw'], "recordsTotal" => $totalCount, "recordsFiltered" => $count, "data" => $rowReturn,);
            return json_encode($output);
        }
    }
    /**
     * Show associate commission Report list.
     * Route: /associate/commission 
     * Method: get 
     * @return  view
     */
    public function AssociateCollectionReport()
    {
        //  if(!in_array('Associate Collection Report', auth()->user()->getPermissionNames()->toArray())){
        //      return redirect()->route('branch.dashboard');
        //  }


        if (!in_array('Associate Collection Report', auth()->user()->getPermissionNames()->toArray())) {
            return redirect()->route('branch.dashboard');
        }


        $data['title'] = 'Associate Collection Report| Listing';
        $data['branch'] = Branch::where('status', 1)->get();
        return view('templates.branch.associate_management.associatecollectionreport', $data);
    }


    public function AssociateCollectionReportList(Request $request)
    {
        $fillter = 1;
        if ($request->ajax()) {
            $arrFormData = array(); {
                if (!empty($_POST['searchform'])) {
                    foreach ($_POST['searchform'] as $frm_data) {
                        $arrFormData[$frm_data['name']] = $frm_data['value'];
                    }
                }
                if (isset($arrFormData['is_search']) && $arrFormData['is_search'] == 'no') {
                    $output = array(
                        "draw" => 0,
                        "recordsTotal" => 0,
                        "recordsFiltered" => 0,
                        "data" => 0,
                    );
                    return json_encode($output);
                }

                if (isset($arrFormData['is_search']) && $arrFormData['is_search'] == 'yes') {
                    $fillter = 0;
                    $startDate = date('Y-m-d', strtotime($request->adm_report_currentdate));
                    $endDate = date('Y-m-d', strtotime($request->adm_report_currentdate));
                    if ($arrFormData['start_date'] != '') {
                        $startDate = date("Y-m-d", strtotime(convertDate($arrFormData['start_date'])));

                        if ($arrFormData['end_date'] != '') {
                            $endDate = date("Y-m-d ", strtotime(convertDate($arrFormData['end_date'])));
                        } else {
                            $endDate = date('Y-m-d', strtotime($request->adm_report_currentdate));
                        }
                    }
                    $branch_id = getUserBranchId(Auth::user()->id)->id;
                    if (isset($arrFormData['branch_id']) && $arrFormData['branch_id'] != '') {
                        $branch_id = $arrFormData['branch_id'];
                    }
                    $associate_code = '';
                    if (isset($arrFormData['associate_code']) && $arrFormData['associate_code'] != '') {
                        $associate_code = $arrFormData['associate_code'];
                    }
                }

                $branch_id = getUserBranchId(Auth::user()->id)->id;
                //$dataNew=$data[0]->id;
                $branchId = $branch_id;
                $associteCode = $associate_code;
                $pageNo = 0;
                $perPageRecord = '';
                if ($_POST['length']) {
                    $perPageRecord = $_POST['length'];
                }

                if ($_POST['start'] == 0) {
                    $pageNo = 1;
                } else {
                    $pageGet = $_POST['start'] / $_POST['length'];

                    $pageNo = $pageGet + 1;
                }
                $toDay = date("d", strtotime($startDate));
                $toMonth = date("m", strtotime($startDate));
                $toYear = date("Y", strtotime($startDate));

                $fromDay = date("d", strtotime($endDate));
                $fromMonth = date("m", strtotime($endDate));
                $fromYear = date("Y", strtotime($endDate));

                $company_id = $arrFormData['company_id'];

                $dataTotalCount = DB::select('call associteCollectionList(?,?,?,?,?,?,?,?,?,?,?)', [$branchId, '', $toDay, $toMonth, $toYear, $fromDay, $fromMonth, $fromYear, 0, $perPageRecord, $company_id]);

                $totalCount = count($dataTotalCount);


                $data = DB::select('call associteCollectionList(?,?,?,?,?,?,?,?,?,?,?)', [$branchId, $associteCode, $toDay, $toMonth, $toYear, $fromDay, $fromMonth, $fromYear, $pageNo, $perPageRecord, $company_id]);
                $count = $totalCount;
                if ($branchId != '' || $associteCode != '') {
                    $dataTotalCountF = DB::select('call associteCollectionList(?,?,?,?,?,?,?,?,?,?,?)', [$branchId, $associteCode, $toDay, $toMonth, $toYear, $fromDay, $fromMonth, $fromYear, 0, $perPageRecord, $company_id]);

                    $count = count($dataTotalCountF);
                }

                //dd($data);

                $sno = $_POST['start'];
                $rowReturn = array();
                foreach ($data as $row) {

                    $sno++;
                    $val['DT_RowIndex'] = $sno;

                    if (isset($row->branch_code)) {
                        $val['branch_code'] = $row->branch_code;
                    } else {
                        $val['branch_code'] = 'N/A';
                    }


                    if (isset($row->branch_code)) {
                        $val['branch_name'] = $row->name;
                    } else {
                        $val['branch_name'] = 'N/A';
                    }

                    if (isset($row->associate_no)) {
                        $val['associate_code'] = $row->associate_no;
                    } else {
                        $val['associate_code'] = 'N/A';
                    }

                    if (isset($row->first_name)) {

                        $val['associate_name'] = $row->first_name . ' ' . $row->last_name;
                    } else {
                        $val['associate_name'] = 'N/A';
                    }

                    $val['total_collection'] = $row->totalsum;

                    $rowReturn[] = $val;
                }
            }

            $output = array("draw" => $_POST['draw'], "recordsTotal" => $totalCount, "recordsFiltered" => $count, "data" => $rowReturn);



            return json_encode($output);
        }
    }
}
