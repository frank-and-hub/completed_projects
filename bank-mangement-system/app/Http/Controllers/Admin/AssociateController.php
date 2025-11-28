<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Request as Request1;
use App\Models\{ReceiptAmount, Carder, Member, Branch, CommissionLeaserMonthly, AccountBranchTransfer, Receipt, Plans, Memberinvestments, AssociateCommission, BusinessTarget, CorrectionRequests, SamraddhBank, ReceivedCheque, Memberinvestmentsnominees, AssociateDependent, AssociateTree, AssociateGuarantor, FaCode, MemberIdProof, MemberNominee};
use App\Http\Controllers\Admin\CommanController;
use App\Interfaces\RepositoryInterface;
use Carbon\Carbon;
use App\Models\SavingAccount;
use App\Models\AssociateMonthlyCommission;
use Illuminate\Support\Facades\{Hash, Auth, DB, Response, Session, Image, Redirect, URL, Validator};
use App\Http\Requests;
use App\Http\Controllers\Branch\CommanTransactionsController;
use Yajra\DataTables\DataTables;
use App\Services\Sms;
use Investment;
use DateTime;
use App\Models\Loans;

/*
|---------------------------------------------------------------------------
| Admin Panel -- Associate Management AssociateController
|--------------------------------------------------------------------------
|
| This controller handles associate all functionlity.
*/
class AssociateController extends Controller
{
    private $repository;
    public function __construct(RepositoryInterface $repository)
    {
        // check user login or not
        $this->middleware('auth');
        $this->repository = $repository;
    }
    public function index()
    {
        if (check_my_permission(Auth::user()->id, "6") != "1") {
            return redirect()->route('admin.dashboard');
        }
        $data['title'] = 'Associate | Listing';
        $data['branch'] = Branch::where('status', 1)->pluck('name', 'id');
        return view('templates.admin.associate.index', $data);
    }
    public function CommissionQueryManualScript()
    {
        /* ------------------ Script for commission ---------------*/
        $get = \App\Models\Daybook::where('is_eli', '!=', 1)->where('id', '>', 0)->where('id', '<=', 50000)->where('transaction_type', 2)->whereNotIn('id', function ($q) {
            $q->select('day_book_id')
                ->from('associate_commissions');
        })
            ->orderby('id', 'ASC')->orderby('opening_balance', 'ASC')->get();
        //print_r(count($get));die;
        foreach ($get as $val) {
            $invDetail = Memberinvestments::where('id', $val->investment_id)->first('id', 'plan_id', 'branch_id', 'tenure', 'associate_id');
            if ($invDetail->plan_id > 1) {
                // print_r($invDetail->id.','); 
                $depositAmount = $val->deposit;
                $currentBalance = $val->opening_balance;
                $preBalance = $currentBalance - $depositAmount;
                $payDate = date("Y-m-d ", strtotime($val->created_at));
                $invAssociateId = $invDetail->associate_id;
                $collector_id = $val->associate_id;
                if ($val->transaction_type == 2) {
                    if ($invDetail->plan_id == 3 || $invDetail->plan_id == 2) {
                        $tenureMonth = $invDetail->tenure;
                    } else {
                        $tenureMonth = $invDetail->tenure * 12;
                    }
                    /* ------------------ commission genarate-----------------*/
                    $commission = CommanController::commissionDistributeInvestment($invAssociateId, $val->investment_id, 3, $depositAmount, 1, $invDetail->plan_id, $invDetail->branch_id, $tenureMonth, $val->id);
                    $commission_collection = CommanController::commissionCollectionInvestment($collector_id, $val->investment_id, 5, $depositAmount, 1, $invDetail->plan_id, $invDetail->branch_id, $tenureMonth, $val->id);
                    /*----- ------  credit business start ---- ---------------*/
                    $creditBusiness = CommanController::associateCreditBusiness($invAssociateId, $val->investment_id, 1, $depositAmount, 1, $invDetail->plan_id, $tenureMonth, $val->id);
                    /*----- ------  credit business end ---- ---------------*/
                    /* ------------------ commission genarate-----------------*/
                }
                if ($val->transaction_type == 4) {
                    /*-------------------------------  Commission  Section Start ------------------------------------*/
                    if ($invDetail->plan_id == 3 || $invDetail->plan_id == 2) {
                        $tenureMonth = $invDetail->tenure * 12;
                    } else {
                        $tenureMonth = $invDetail->tenure * 12;
                    }
                    $Commission = getMonthlyWiseRenewal1New($val->investment_id, $depositAmount, $payDate, $preBalance, $val->id);
                    foreach ($Commission as $val1) {
                        $commission = CommanController::commissionDistributeInvestmentRenew($invAssociateId, $val->investment_id, 3, $val1['amount'], $val1['month'], $invDetail->plan_id, $invDetail->branch_id, $tenureMonth, $val->id, $val1['type']);
                        $commission_collection = CommanController::commissionCollectionInvestmentRenew($invAssociateId, $val->investment_id, 5, $val1['amount'], $val1['month'], $invDetail->plan_id, $invDetail->branch_id, $tenureMonth, $val->id, $val1['type']);
                        /*----- ------  credit business start ---- ---------------*/
                        $creditBusiness = CommanController::associateCreditBusiness($invAssociateId, $val->investment_id, 1, $val1['amount'], $val1['month'], $invDetail->plan_id, $tenureMonth, $val->id);
                        /*----- ------  credit business end ---- ---------------*/
                    }
                    /*-----------------------------  Commission  Section End -------------------------------------*/
                }
            }
        }
        //die;
        /* ------------------ Script for commission ---------------*/
    }
    public function associateListing(Request $request)
    {
        $arrFormData = [];
        foreach ($request->searchform as $key => $val) {
            $arrFormData[$val['name']] = $val['value'];
        }
        if ($request->ajax() && check_my_permission(Auth::user()->id, "6") == "1") {
            // fillter array 
            if (isset($arrFormData['is_search']) && $arrFormData['is_search'] == 'yes') {
                $data = Member::select(['id', 'associate_branch_id', 'member_id', 'associate_no', 'first_name', 'last_name', 'dob', 'associate_join_date', 'mobile_no', 'email', 'associate_senior_id', 'associate_senior_code', 'associate_status', 'photo', 'signature', 'address', 'state_id', 'district_id', 'city_id', 'village', 'pin_code', 'branch_id'])
                    ->with(['associate_branch:id,name,branch_code,sector,regan,zone'])
                    // ->where('member_id', '!=', '9999999')
                    ->where('member_id', '!=', '0CI09999999')
                    ->with(['seniorData:id,first_name,last_name', 'states:id,name', 'city:id,name', 'district:id,name'])
                    ->with([
                        'memberIdProof' => function ($q) {
                            $q->select('id', 'first_id_no', 'second_id_no', 'member_id', 'first_id_type_id', 'second_id_type_id')
                                ->with(['idTypeFirst:id,name', 'idTypeSecond:id,name']);
                        }
                    ])
                    ->with([
                        'memberNomineeDetails' => function ($q) {
                            $q->select('id', 'name', 'age', 'relation', 'member_id')
                                ->with(['nomineeRelationDetails:id,name']);
                        }
                    ])
                    ->where('is_associate', 1);
                if (Auth::user()->branch_id > 0) {
                    $id = Auth::user()->branch_id;
                    $data = $data->where('associate_branch_id', '=', $id);
                }
                /******* fillter query start ****/
                if ($arrFormData['sassociate_code'] != '') {
                    $associate_code = $arrFormData['sassociate_code'];
                    $data = $data->where('associate_senior_code', '=', $associate_code);
                }
                if (isset($arrFormData['branch_id']) && $arrFormData['branch_id'] != '') {
                    $branch_id = $arrFormData['branch_id'];
                    if ($branch_id != '0') {
                        $data = $data->where('associate_branch_id', $branch_id);
                    }
                }
                $data->when(isset($arrFormData['customer_id']), function ($q) use ($arrFormData) {
                    $q->where('member_id', 'like', '%' . $arrFormData['customer_id'] . '%');
                });
                if ($arrFormData['associate_code'] != '') {
                    $meid = $arrFormData['associate_code'];
                    $data = $data->where('associate_no', 'LIKE', "%$meid%");
                }
                if ($arrFormData['name'] != '') {
                    $name = $arrFormData['name'];
                    $data = $data->where(function ($query) use ($name) {
                        $query->where('first_name', 'LIKE', '%' . $name . '%')
                            ->orWhere('last_name', 'LIKE', '%' . $name . '%')
                            ->orWhere(DB::raw('concat(first_name," ",last_name)'), 'LIKE', "%$name%");
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
                /******* fillter query End ****/
                $data1 = $data->orderby('associate_join_date', 'DESC')->count('id');
                $count = $data1; //count($data1);
                $data = $data->orderby('associate_join_date', 'DESC')->offset($_POST['start'])->limit($_POST['length'])->get();
                // $dataCount = Member::where('member_id', '!=', '9999999')->where('is_associate', 1);
                // if (Auth::user()->branch_id > 0) {
                //     $dataCount = $dataCount->where('associate_branch_id', '=', Auth::user()->branch_id);
                // }
                // $totalCount = $dataCount->count('id');
                $totalCount = $count;
                $sno = $_POST['start'];
                $rowReturn = array();
                foreach ($data as $row) {
                    $relationId = '';
                    $sno++;
                    $val['DT_RowIndex'] = $sno;
                    $NomineeDetail = $row['memberNomineeDetails'];
                    $val['join_date'] = date("d/m/Y", strtotime($row->associate_join_date));
                    $val['branch'] = $row['associate_branch']->name;
                    $val['branch_code'] = $row['associate_branch']->branch_code;
                    $val['sector_name'] = $row['associate_branch']->sector;
                    $val['region_name'] = $row['associate_branch']->regan;
                    $val['zone_name'] = $row['associate_branch']->zone;
                    $val['dob'] = date('d/m/Y', strtotime($row->dob));
                    $val['m_id'] = $row->member_id;
                    $val['member_id'] = $row->associate_no;
                    $val['name'] = $row->first_name . ' ' . $row->last_name;
                    $val['email'] = $row->email;
                    $val['mobile_no'] = $row->mobile_no;
                    $val['associate_code'] = $row->associate_senior_code;
                    $val['associate_name'] = $row['seniorData']->first_name . ' ' . $row['seniorData']->last_name;
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
                    $btn = '<div class="list-icons"><div class="dropdown"><a href="#" class="list-icons-item" data-toggle="dropdown"><i class="icon-menu9"></i></a><div class="dropdown-menu dropdown-menu-right">';
                    $url = URL::to("admin/associate-detail/" . $row->id . "");
                    $url6 = URL::to("admin/associate-idcard/" . $row->id . "");
                    $url7 = URL::to("admin/associate-log/1/" . $row->id . "");
                    if (Auth::user()->id != "13") {
                        $btn .= '<a class="dropdown-item" href="' . $url . '" title="Member Detail"><i class="icon-eye-blocked2  mr-2"></i>Detail</a>  ';
                    }
                    if ($row->is_block == 0) {
                        if (Auth::user()->id != "13") {
                            // $btn .= '<a class="dropdown-item" href="'.$url4.'" title="Member Edit"><i class="icon-pencil5  mr-2"></i>Edit</a>  ';
                        }
                        $btn .= '<a class="dropdown-item" href="' . $url6 . '" title="ID Card"><i class="fas fa-id-card mr-2"></i>ID Card</a>';
                        $btn .= '<a class="dropdown-item" href="' . $url7 . '" title="Branch Transferred log"><i class="fas fa-id-card mr-2"></i>Branch Transferred Log</a>';
                    }
                    $btn .= '</div></div></div>';
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
    public function associateRegister()
    {
        if (check_my_permission(Auth::user()->id, "5") != "1") {
            return redirect()->route('admin.dashboard');
        }
        $data['title'] = 'Associate | Registration';
        $data['relations'] = relationsList();
        $data['samraddhBanks'] = SamraddhBank::select('id', 'bank_name')->with([
            'bankAccount' => function ($q) {
                $q->select('id', 'account_no');
            }
        ])->get();
        // return view('templates.admin.associate.add', $data);
        $data['carder'] = Carder::where('status', 1)->where('is_deleted', 0)->limit(3)->get(['id', 'name', 'short_name']);
        return view('templates.admin.associate.addCompany', $data);
    }
    public function detail($id)
    {
        $data['title'] = 'Associate | Details';
        $data['memberDetail'] = Member::where('id', $id)->first();
        $data['idProofDetail'] = \App\Models\MemberIdProof::where('member_id', $id)->first();
        $data['guarantorDetail'] = \App\Models\AssociateGuarantor::where('member_id', $id)->first();
        $data['dependentDetail'] = \App\Models\AssociateDependent::where('member_id', $id)->get();
        $recipt = Receipt::where('member_id', $id)->where('receipts_for', 4)->first();
        $data['recipt'] = ($recipt) ? $recipt->id : 0;
        return view('templates.admin.associate.detail', $data);
    }
    public function receipt($id)
    {
        $data['title'] = 'Associate | Receipt';
        $data['type'] = '1';
        $receipt = Receipt::where('id', $id)->first('member_id');
        $Receipt = Receipt::with([
            'memberReceipt:id,member_id,first_name,last_name,mobile_no,address,ssb_account,rd_account,associate_no',
            'branchReceipt:id,name,branch_code'
        ])
            ->where('member_id', $receipt->member_id)
        ;
        $receipts = $Receipt->get();
        $value = array();
        // $data['receipt'] = $Receipt->first();
        foreach ($receipts as $val) {
            $value[] = [
                'receipt' => Receipt::with([
                    'memberReceipt:id,member_id,first_name,last_name,mobile_no,address,ssb_account,rd_account,associate_no',
                    'branchReceipt:id,name,branch_code'
                ])
                    ->where('member_id', $val->member_id)
                    ->first()
                ,
                'receipt_amount' => ReceiptAmount::where('receipt_id', $val->id)
                    ->with('receiptAmount')
                    ->get(['receipt_id', 'amount', 'type_label'])
                ,
                'total_amount' => ReceiptAmount::where('receipt_id', $val->id)
                    ->sum('amount'),
                'ssb_receipt_amount' => ReceiptAmount::where('receipt_id', $val->id)
                    ->with('receiptAmount')
                    ->where('type_label', 1)
                    ->get(['receipt_id', 'amount'])
                ,
            ];
        }
        $data['value'] = $value;
        return (!$receipt->member_id) ? back() : view('templates.admin.associate.receipt ', $data);
    }
    public function getMemberData(Request $request)
    {
        $data = Member::where('member_id', $request->code)->where('status', 1)->where('is_deleted', 0);
        if (Auth::user()->branch_id > 0) {
            $data = $data->where('branch_id', Auth::user()->branch_id);
        }
        $data = $data->first();
        if ($data) {
            if ($data->is_block == 1) {
                return \Response::json(['view' => 'No data found', 'msg_type' => 'error2']);
            } else {
                if ($data->is_associate == 0) {
                    $id = $data->id;
                    $idProofDetail = \App\Models\MemberIdProof::where('member_id', $id)->first();
                    $nomineeDetail = \App\Models\MemberNominee::where('member_id', $id)->first();
                    $nomineeDOB = date("d/m/Y", strtotime($nomineeDetail->dob));
                    return \Response::json(['view' => view('templates.admin.associate.partials.member_detail', ['memberData' => $data, 'idProofDetail' => $idProofDetail])->render(), 'msg_type' => 'success', 'id' => $id, 'nomineeDetail' => $nomineeDetail, 'nomineeDOB' => $nomineeDOB]);
                } else {
                    return \Response::json(['view' => 'No data found', 'msg_type' => 'error1']);
                }
            }
        } else {
            return \Response::json(['view' => 'No data found', 'msg_type' => 'error']);
        }
    }
    /*
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
    */
    /*
    public function getCarderAssociate(Request $request)
    {
    //print_r($request->id);die;
    if ($request->id > 1) {
    $carde = \App\Models\Carder::where('id', '<', $request->id)->where('status', 1)->where('is_deleted', 0)->limit(15)->get(['id', 'name', 'short_name']);
    } else {
    $carde = \App\Models\Carder::where('id', '<=', $request->id)->where('status', 1)->where('is_deleted', 0)->limit(15)->get(['id', 'name', 'short_name']);
    }
    $return_array = compact('carde');
    return json_encode($return_array);
    }
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
    /*
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
        $data = Memberinvestments::where(['plan_id' => 10, 'member_id' => $request->member_id])->where('deposite_amount', '>=', 500)->where('tenure', '>=', 5)->pluck('account_number', 'id');
        return json_encode($data);
    }
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
        //$branch_id=$getBranchId->id;
        //$getBranchId=getUserBranchId(Auth::user()->id);
        $memberId = $request['id'];
        $dataMemberDetail = Member::where('id', $memberId)->first();
        $branch_id = $dataMemberDetail->branch_id;
        $getBranchCode = getBranchCode($branch_id);
        $branchCode = $getBranchCode->branch_code;
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
        if (check_my_permission(Auth::user()->id, "137") == "1" && Auth::user()->role_id == "2") {
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
                    $invPaymentMode['cheque_date'] = date("Y-m-d", strtotime(str_replace('/', '-', $request['rd_cheque_date'])));
                    $payment_mode = 1;
                    $rdPayDate = date("Y-m-d", strtotime(str_replace('/', '-', $request['rd_cheque_date'])));
                }
                if ($request['payment_mode'] == 2) {
                    $invPaymentMode['transaction_id'] = $request['rd_online_id'];
                    $invPaymentMode['transaction_date'] = date("Y-m-d", strtotime(str_replace('/', '-', $request['rd_online_date'])));
                    $payment_mode = 3;
                    $online_deposit_bank_id = $request['rd_online_bank_id'];
                    $online_deposit_bank_ac_id = $request['rd_online_bank_ac_id'];
                    $rdPayDate = date("Y-m-d", strtotime(str_replace('/', '-', $request['rd_online_date'])));
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
                            $ssbTranCalculation = CommanController::ssbTransactionModify($ssbAccountDetail->id, $ssbAccountDetail->account_no, $ssbAccountDetail->balance, $request['rd_amount'], $detail, 'INR', 'DR', 3, $branch_id, 1, 6);
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
                            // add log data
                            $encodeDate = json_encode($dataInvestrd);
                            $arrs = array("investmentId" => $investmentId, "type" => "2", "account_head_id" => 0, "user_id" => Auth::user()->id, "message" => "Member investments Create", "data" => $encodeDate);
                            DB::table('user_log')->insert($arrs);
                            // End Log data
                            $satRefId = CommanController::createTransactionReferences($ssbTranCalculation, $investmentId);
                            $rdCreateTran = CommanController::createTransaction($satRefId, 1, $ssbAccountDetail->id, $memberId, $branch_id, $branchCode, $amountArrayRD, 5, $deposit_by_name, $deposit_by_id, $ssbAccountDetail->account_no, $cheque_dd_no = '0', $bank_name = 'null', $branch_name = 'null', $payment_date = 'null', $online_payment_id = 'null', $online_payment_by = 'null', $saving_account_id = 0, 'DR');
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
                    'dob' => date("Y-m-d", strtotime(str_replace('/', '-', $request['rd_first_dob']))),
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
                        'dob' => date("Y-m-d", strtotime(str_replace('/', '-', $request['rd_second_dob']))),
                        'age' => $request['rd_second_age'],
                        'percentage' => $request['rd_second_percentage'],
                        'phone_number' => $request['rd_second_mobile_no'],
                        'created_at' => $request['created_at'],
                    );
                    $resinvDatard2 = \App\Models\Memberinvestmentsnominees::create($invDatard2);
                }
                $invPaymentMode['investment_id'] = $investmentId;
                $res = \App\Models\Memberinvestmentspayments::create($invPaymentMode);
                // add log data
                $encodeDate = json_encode($invPaymentMode);
                $arrs = array("MemberinvestmentspaymentsID" => $res->id, "type" => "2", "account_head_id" => 0, "user_id" => Auth::user()->id, "message" => "Member investments paymentsID", "data" => $encodeDate);
                DB::table('user_log')->insert($arrs);
                // End Log data
                $amountArray = array('1' => $request['rd_amount']);
                $createTransaction = CommanController::createTransaction($satRefId = NULL, 2, $investmentId, $memberId, $branch_id, $branchCode, $amountArray, $payment_mode, $deposit_by_name, $deposit_by_id, $investmentAccountNoRd, $request['rd_cheque_no'], $request['rd_bank_name'], $request['rd_branch_name'], $rdPayDate, $request['rd_online_id'], $online_payment_by = 'null', $rdDebitaccountId, 'CR');
                $sAccountNumber = '';
                if ($rdDebitaccountId != 0) {
                    $sAccountNumber = $rdDebitaccountId;
                }
                $description = 'SRD Account Opening';
                $createDayBook = CommanController::createDayBookNew($createTransaction, $satRefId, 2, $investmentId, $request['senior_id'], $memberId, $request['rd_amount'], $request['rd_amount'], $withdrawal = 0, $description, $sAccountNumber, $branch_id, $branchCode, $amountArray, $payment_mode, $deposit_by_name, $deposit_by_id, $investmentAccountNoRd, $request['rd_cheque_no'], $request['rd_bank_name'], $request['rd_branch_name'], $rdPayDate, $request['rd_online_id'], $online_payment_by = Null, $sAccountNumber, 'CR', $received_cheque_id, $cheque_deposit_bank_id, $cheque_deposit_bank_ac_id, $online_deposit_bank_id, $online_deposit_bank_ac_id);
                /*--------------------- received cheque payment -----------------------*/
                if ($payment_mode == 1) {
                    $receivedPayment['type'] = 2;
                    $receivedPayment['branch_id'] = $branch_id;
                    $receivedPayment['investment_id'] = $investmentId;
                    $receivedPayment['day_book_id'] = $createDayBook;
                    $receivedPayment['cheque_id'] = $request['cheque_id'];
                    $receivedPayment['created_at'] = $request['created_at'];
                    $receivedCreate = \App\Models\ReceivedChequePayment::create($receivedPayment);
                    $dataRC['status'] = 3;
                    $receivedcheque = \App\Models\ReceivedCheque::find($request['cheque_id']);
                    $receivedcheque->update($dataRC);
                }
                /*--------------------- received cheque payment -----------------------*/
                /* ------------------ commission genarate-----------------*/
                $commission = CommanController::commissionDistributeInvestment($request['senior_id'], $investmentId, 3, $request['rd_amount'], 1, $planId, $branch_id, $request['tenure'], $createDayBook);
                $commission_collection = CommanController::commissionCollectionInvestment($request['senior_id'], $investmentId, 5, $request['rd_amount'], 1, $planId, $branch_id, $request['tenure'], $createDayBook);
                /*----- ------  credit business start ---- ---------------*/
                $creditBusiness = CommanController::associateCreditBusiness($request['senior_id'], $investmentId, 1, $request['rd_amount'], 1, $planId, $request['tenure'], $createDayBook);
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
                $daybookRefRD = CommanController::createBranchDayBookReferenceNew($rdAmount, $globaldate);
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
                    $getBankHead = \App\Models\SamraddhBank::where('id', $cheque_bank_to)->first('id', 'account_head_id');
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
                    $allTranRDcheque = CommanController::headTransactionCreate($refIdRD, $branch_id, $bank_id, $bank_ac_id, $head41, $typeHeadRd, $sub_typeHeadRd, $type_idRD, $associate_id_admin, $memberId, $branch_id_to = NULL, $branch_id_from = NULL, $opening_balance = NULL, $rdAmount, $closing_balance = NULL, $rdDes, 'DR', $headPaymentModeRD, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $createDayBook, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $cheque_bank_to_name, $cheque_bank_to_branch, $cheque_bank_to_ac_no, $cheque_bank_to_ifsc, $transction_bank_from_id, $transction_bank_from_ac_id, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc, $cheque_bank_from_id, $cheque_bank_ac_from_id);
                    //bank entry
                    $bankCheque = CommanController::NewFieldAddSamraddhBankDaybookCreate($refIdRD, $cheque_bank_to, $cheque_bank_ac_to, $typeHeadRd, $sub_typeHeadRd, $type_idRD, $associate_id_admin, $memberId, $branch_id, $opening_balance = NULL, $rdAmount, $closing_balance = NULL, $rdDes, $rdDesDR, $rdDesCR, 'CR', $headPaymentModeRD, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $createDayBook, $ssb_account_id_to, $cheque_bank_from_id, $cheque_bank_ac_from_id, $cheque_bank_to_name, $cheque_bank_to_branch, $cheque_bank_to_ac_no, $cheque_bank_to_ifsc, $transction_bank_from_id, $transction_bank_from_ac_id, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $jv_unique_id, $cheque_type, $cheque_id);
                    //bank balence
                    $bankClosing = CommanController::checkCreateBankClosing($cheque_bank_to, $cheque_bank_ac_to, $created_at, $rdAmount, 0);
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
                    $getBHead = \App\Models\SamraddhBank::where('id', $transction_bank_to)->first('id', 'account_head_id');
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
                    $allTranRDonline = CommanController::headTransactionCreate($refIdRD, $branch_id, $bank_id, $bank_ac_id, $head411, $typeHeadRd, $sub_typeHeadRd, $type_idRD, $associate_id_admin, $memberId, $branch_id_to = NULL, $branch_id_from = NULL, $opening_balance = NULL, $rdAmount, $closing_balance = NULL, $rdDes, 'DR', $headPaymentModeRD, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $createDayBook, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $cheque_bank_to_name, $cheque_bank_to_branch, $cheque_bank_to_ac_no, $cheque_bank_to_ifsc, $transction_bank_from_id, $transction_bank_from_ac_id, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc, $cheque_bank_from_id, $cheque_bank_ac_from_id);
                    //bank entry
                    $bankonline = CommanController::NewFieldAddSamraddhBankDaybookCreate($refIdRD, $transction_bank_to, $transction_bank_ac_to, $typeHeadRd, $sub_typeHeadRd, $type_idRD, $associate_id_admin, $memberId, $branch_id, $opening_balance = NULL, $rdAmount, $closing_balance = NULL, $rdDes, $rdDesDR, $rdDesCR, 'CR', $headPaymentModeRD, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $createDayBook, $ssb_account_id_to, $cheque_bank_from_id, $cheque_bank_ac_from_id, $cheque_bank_to_name, $cheque_bank_to_branch, $cheque_bank_to_ac_no, $cheque_bank_to_ifsc, $transction_bank_from_id, $transction_bank_from_ac_id, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $jv_unique_id, $cheque_type, $cheque_id);
                    //bank balence
                    $bankClosing = CommanController::checkCreateBankClosing($transction_bank_to, $transction_bank_ac_to, $created_at, $rdAmount, 0);
                } elseif ($request['payment_mode'] == 3) { // ssb
                    $headPaymentModeRD = 3;
                    $v_no = mt_rand(0, 999999999999999);
                    $v_date = $entry_date;
                    $ssb_account_id_from = $sAccountNumber;
                    $SSBDescTran = 'Amount transferred to ' . $planDetail->name . '(' . $investmentAccountNoRd . ') ';
                    $head4rdSSB = getPlanDetailByCompany($companyId);
                    $ssbDetals = SavingAccount::where('id', $ssb_account_id_from)->first();
                    $rdDesDR = $planDetail->name . '(' . $investmentAccountNoRd . ') A/c Dr ' . $rdAmount . '/-';
                    $rdDesCR = 'To SSB(' . $ssbDetals->account_no . ') A/c Cr ' . $rdAmount . '/-';
                    $rdDes = 'Amount received from Account opening ' . $planDetail->name . '(' . $investmentAccountNoRd . ') through SSB(' . $ssbDetals->account_no . ')';
                    $rdDesMem = 'Account opening ' . $planDetail->name . '(' . $investmentAccountNoRd . ') through SSB(' . $ssbDetals->account_no . ')';
                    // ssb  head entry -
                    $allTranRDSSB = CommanController::headTransactionCreate($refIdRD, $branch_id, $bank_id, $bank_ac_id, $head4rdSSB, $typeHeadRd, $sub_typeHeadRd, $type_idRD, $associate_id_admin, $memberId, $branch_id_to = NULL, $branch_id_from = NULL, $opening_balance = NULL, $rdAmount, $closing_balance = NULL, $SSBDescTran, 'DR', $headPaymentModeRD, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $createDayBook, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $cheque_bank_to_name, $cheque_bank_to_branch, $cheque_bank_to_ac_no, $cheque_bank_to_ifsc, $transction_bank_from_id, $transction_bank_from_ac_id, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc, $cheque_bank_from_id, $cheque_bank_ac_from_id);
                    $branchClosing = CommanController::checkCreateBranchClosingDr($branch_id, $created_at, $rdAmount, 0);
                    $memberTranInvest77 = CommanController::NewFieldAddMemberTransactionCreate($refIdRD, '4', '47', $ssb_account_id_from, $associate_id_admin, $ssbDetals->member_id, $branch_id, $bank_id, $bank_ac_id, $rdAmount, $SSBDescTran, 'DR', $headPaymentModeRD, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $createDayBook, $ssb_account_id_to, $cheque_bank_from_id, $cheque_bank_ac_from_id, $cheque_bank_to_name, $cheque_bank_to_branch, $cheque_bank_to_ac_no, $cheque_bank_to_ifsc, $transction_bank_from_id, $transction_bank_from_ac_id, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $jv_unique_id, $cheque_type, $cheque_id);
                } else {
                    $headPaymentModeRD = 0;
                    $head1rdC = 2;
                    $head2rdC = 10;
                    $head3rdC = 28;
                    $head4rdC = 71;
                    $head5rdC = NULL;
                    $rdDesDR = 'Cash A/c Dr ' . $rdAmount . '/-';
                    $rdDesCR = 'To ' . $planDetail->name . '(' . $investmentAccountNoRd . ')  A/c Cr ' . $rdAmount . '/-';
                    $rdDes = 'Amount received for Account opening ' . $planDetail->name . '(' . $investmentAccountNoRd . ') through cash(' . getBranchCode($branch_id)->branch_code . ')';
                    $rdDesMem = 'Account opening ' . $planDetail->name . '(' . $investmentAccountNoRd . ') through cash(' . getBranchCode($branch_id)->branch_code . ')';
                    // branch cash  head entry +
                    $allTranRDcash = CommanController::headTransactionCreate($refIdRD, $branch_id, $bank_id, $bank_ac_id, $head3rdC, $typeHeadRd, $sub_typeHeadRd, $type_idRD, $associate_id_admin, $memberId, $branch_id_to = NULL, $branch_id_from = NULL, $opening_balance = NULL, $rdAmount, $closing_balance = NULL, $rdDes, 'DR', $headPaymentModeRD, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $createDayBook, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $cheque_bank_to_name, $cheque_bank_to_branch, $cheque_bank_to_ac_no, $cheque_bank_to_ifsc, $transction_bank_from_id, $transction_bank_from_ac_id, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc, $cheque_bank_from_id, $cheque_bank_ac_from_id);
                    //Balance   entry +
                    $branchCash = CommanController::checkCreateBranchCash($branch_id, $created_at, $rdAmount, 0);
                }
                $head1rd = 1;
                $head2rd = 8;
                $head3rd = 20;
                $head4rd = 59;
                $head5rd = 83;
                //branch day book entry +
                $daybookRd = CommanController::NewFieldBranchDaybookCreate($refIdRD, $branch_id, $typeHeadRd, $sub_typeHeadRd, $type_idRD, 1, $memberId, $branch_id_to = NULL, $branch_id_from = NULL, $opening_balance = NULL, $rdAmount, $closing_balance = NULL, $rdDes, $rdDesDR, $rdDesCR, $payment_type_rd, $headPaymentModeRD, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $is_contra = NULL, $contra_id = NULL, $created_at, $updated_at, $createDayBook, $ssb_account_id_to, $cheque_bank_from_id, $cheque_bank_ac_from_id, $cheque_bank_to_name, $cheque_bank_to_branch, $cheque_bank_to_ac_no, $cheque_bank_to_ifsc, $transction_bank_from_id, $transction_bank_from_ac_id, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc, 0, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $jv_unique_id, $cheque_type, $cheque_id);
                // Investment head entry +
                $allTranRD = CommanController::headTransactionCreate($refIdRD, $branch_id, $bank_id, $bank_ac_id, $head5rd, $typeHeadRd, $sub_typeHeadRd, $type_idRD, $associate_id_admin, $memberId, $branch_id_to = NULL, $branch_id_from = NULL, $opening_balance = NULL, $rdAmount, $closing_balance = NULL, $rdDes, $payment_type_rd, $headPaymentModeRD, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $createDayBook, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $cheque_bank_to_name, $cheque_bank_to_branch, $cheque_bank_to_ac_no, $cheque_bank_to_ifsc, $transction_bank_from_id, $transction_bank_from_ac_id, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc, $cheque_bank_from_id, $cheque_bank_ac_from_id);
                // Member transaction  +
                $memberTranRD = CommanController::NewFieldAddMemberTransactionCreate($refIdRD, $typeHeadRd, $sub_typeHeadRd, $type_idRD, $associate_id_admin, $memberId, $branch_id, $bank_id, $bank_ac_id, $rdAmount, $rdDesMem, $payment_type_rd, $headPaymentModeRD, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $createDayBook, $ssb_account_id_to, $cheque_bank_from_id, $cheque_bank_ac_from_id, $cheque_bank_to_name, $cheque_bank_to_branch, $cheque_bank_to_ac_no, $cheque_bank_to_ifsc, $transction_bank_from_id, $transction_bank_from_ac_id, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $jv_unique_id, $cheque_type, $cheque_id);
                /******** Balance   entry ***************/
                $branchClosing = CommanController::checkCreateBranchClosing($branch_id, $created_at, $rdAmount, 0);
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
                $dataInvest['account_number'] = $investmentAccountNoSsb;
                $dataInvest['mi_code'] = $miCode;
                $dataInvest['associate_id'] = 1;
                $dataInvest['current_balance'] = $request['ssb_amount'];
                $dataInvest['created_at'] = $request['created_at'];
                $res = \App\Models\Memberinvestments::create($dataInvest);
                $investmentId = $res->id;
                //create savings account
                $description = 'SSB Account Opening';
                $amount = $request['ssb_amount'];
                $createAccount = CommanController::createSavingAccountDescriptionModify($memberId, $branch_id, $branchCode, $amount, $payment_mode, $investmentId, $miCode, $investmentAccountNoSsb, $is_primary, $faCode, $description, $associate_id = 1);
                $ssbAccountId = $createAccount['ssb_id']; //sb account id
                $satRefId = CommanController::createTransactionReferences($createAccount['ssb_transaction_id'], $investmentId);
                $amountArraySsb = array('1' => $amount);
                $ssbCreateTran = CommanController::createTransaction($satRefId, 1, $ssbAccountId, $memberId, $branch_id, $branchCode, $amountArraySsb, 0, $deposit_by_name, $deposit_by_id, $investmentAccountNoSsb, $cheque_dd_no = '0', $bank_name = 'null', $branch_name = 'null', $payment_date = 'null', $online_payment_id = 'null', $online_payment_by = 'null', $saving_account_id = 0, 'CR');
                $description = 'SSB Account Opening';
                $sAccountNumber = '';
                $createDayBook = CommanController::createDayBookNew($ssbCreateTran, $satRefId, 1, $ssbAccountId, $request['senior_id'], $memberId, $request['ssb_amount'], $request['ssb_amount'], $withdrawal = 0, $description, $sAccountNumber, $branch_id, $branchCode, $amountArraySsb, $payment_mode, $deposit_by_name, $deposit_by_id, $investmentAccountNoSsb, 0, Null, Null, date('Y-m-d'), Null, $online_payment_by = Null, $sAccountNumber, 'CR', $received_cheque_id = NULL, $cheque_deposit_bank_id = NULL, $cheque_deposit_bank_ac_id = NULL, $online_deposit_bank_id = NULL, $online_deposit_bank_ac_id = NULL);
                $invData1ssb = array(
                    'investment_id' => $investmentId,
                    'nominee_type' => 0,
                    'name' => $request['ssb_first_first_name'],
                    //  'second_name' => $request['ssb_first_last_name'],
                    'relation' => $request['ssb_first_relation'],
                    'gender' => $request['ssb_first_gender'],
                    'dob' => date("Y-m-d", strtotime(str_replace('/', '-', $request['ssb_first_dob']))),
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
                        'dob' => date("Y-m-d", strtotime(str_replace('/', '-', $request['ssb_second_dob']))),
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
                $bank_id = NULL;
                $bank_ac_id = NULL;
                $associate_id_admin = 1;
                $ssbAmount = $request['ssb_amount'];
                $daybookRefssb = CommanController::createBranchDayBookReferenceNew($ssbAmount, $globaldate);
                $refIdssb = $daybookRefssb;
                $currency_code = 'INR';
                $headPaymentModessb = 0;
                $payment_type_ssb = 'CR';
                $type_idssb = $ssbAccountId;
                $entry_date = date("Y-m-d", strtotime(convertDate($globaldate)));
                $entry_time = date("H:i:s", strtotime(convertDate($globaldate)));
                $created_by = 1;
                $created_by_id = Auth::user()->id;
                $created_at = date("Y-m-d H:i:s", strtotime(convertDate($globaldate)));
                $updated_at = date("Y-m-d H:i:s", strtotime(convertDate($globaldate)));
                $typeHeadssb = 4;
                $sub_typeHeadssb = 41;
                $ssbDesDR = 'Cash A/c Dr ' . $ssbAmount . '/-';
                $ssbDesCR = 'To SSB (' . $investmentAccountNoSsb . ')  A/c Cr ' . $ssbAmount . '/-';
                $ssbDes = 'Amount received for Account opening SSB (' . $investmentAccountNoSsb . ') through cash(' . getBranchCode($branch_id)->branch_code . ')';
                $ssbDesMem = 'Account opening SSB (' . $investmentAccountNoSsb . ') through cash(' . getBranchCode($branch_id)->branch_code . ')';
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
                $daybookssb = CommanController::NewFieldBranchDaybookCreate($refIdssb, $branch_id, $typeHeadssb, $sub_typeHeadssb, $type_idssb, 1, $memberId, $branch_id_to = NULL, $branch_id_from = NULL, $opening_balance = NULL, $ssbAmount, $closing_balance = NULL, $ssbDes, $ssbDesDR, $ssbDes, $payment_type_ssb, $headPaymentModessb, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $is_contra = NULL, $contra_id = NULL, $created_at, $updated_at, $createAccount['ssb_transaction_id'], $ssb_account_id_to, $cheque_bank_from_id, $cheque_bank_ac_from_id, $cheque_bank_to_name, $cheque_bank_to_branch, $cheque_bank_to_ac_no, $cheque_bank_to_ifsc, $transction_bank_from_id, $transction_bank_from_ac_id, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc, 0, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $jv_unique_id, $cheque_type, $cheque_id);
                // Investment head entry +
                $allTranssb = CommanController::headTransactionCreate($refIdssb, $branch_id, $bank_id, $bank_ac_id, $head4ssb, $typeHeadssb, $sub_typeHeadssb, $type_idssb, $associate_id_admin, $memberId, $branch_id_to = NULL, $branch_id_from = NULL, $opening_balance = NULL, $ssbAmount, $closing_balance = NULL, $ssbDes, $payment_type_ssb, $headPaymentModessb, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $createAccount['ssb_transaction_id'], $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $cheque_bank_to_name, $cheque_bank_to_branch, $cheque_bank_to_ac_no, $cheque_bank_to_ifsc, $transction_bank_from_id, $transction_bank_from_ac_id, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc, $cheque_bank_from_id, $cheque_bank_ac_from_id);
                // Member transaction  +
                $memberTranssb = CommanController::NewFieldAddMemberTransactionCreate($refIdssb, $typeHeadssb, $sub_typeHeadssb, $type_idssb, $associate_id_admin, $memberId, $branch_id, $bank_id, $bank_ac_id, $ssbAmount, $ssbDesMem, $payment_type_ssb, $headPaymentModessb, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $createAccount['ssb_transaction_id'], $ssb_account_id_to, $cheque_bank_from_id, $cheque_bank_ac_from_id, $cheque_bank_to_name, $cheque_bank_to_branch, $cheque_bank_to_ac_no, $cheque_bank_to_ifsc, $transction_bank_from_id, $transction_bank_from_ac_id, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $jv_unique_id, $cheque_type, $cheque_id);
                // branch cash  head entry +
                $head1ssbC = 2;
                $head2ssbC = 10;
                $head3ssbC = 28;
                $head4ssbC = 71;
                $head5ssbC = NULL;
                $allTranssbcash = CommanController::headTransactionCreate($refIdssb, $branch_id, $bank_id, $bank_ac_id, $head3ssbC, $typeHeadssb, $sub_typeHeadssb, $type_idssb, $associate_id_admin, $memberId, $branch_id_to = NULL, $branch_id_from = NULL, $opening_balance = NULL, $ssbAmount, $closing_balance = NULL, $ssbDes, 'DR', $headPaymentModessb, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $createAccount['ssb_transaction_id'], $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $cheque_bank_to_name, $cheque_bank_to_branch, $cheque_bank_to_ac_no, $cheque_bank_to_ifsc, $transction_bank_from_id, $transction_bank_from_ac_id, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc, $cheque_bank_from_id, $cheque_bank_ac_from_id);
                //Balance   entry +
                $branchCash = CommanController::checkCreateBranchCash($branch_id, $created_at, $ssbAmount, 0);
                $branchClosing = CommanController::checkCreateBranchClosing($branch_id, $created_at, $ssbAmount, 0);
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
                $dataAssociate['associate_join_date'] = date("Y-m-d", strtotime(str_replace('/', '-', $request['application_date'])));
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
                /****associate target entry****/
                $com_type = 2;
                $member_carder = $request['current_carder'];
                $commistionMember = CommanController::commissionDistributeMember($request['senior_id'], $memberId, $com_type, $branch_id, $member_carder);
                /****associate target entry****/
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
                if (isset($request['rd_account'])) {
                    $rdset = 1;
                } else {
                    $rdset = 0;
                }
                if ($request['rd_account'] == 0 && $request['ssb_account'] == 0 && $rdset == 1) {
                    $amountArray1 = array('1' => $request['ssb_amount'], '2' => $request['rd_amount']);
                    $typeArray = array('1' => 1, '2' => 2);
                    $receipts_for = 4;
                    $createRecipt = CommanController::createPaymentRecipt(0, 0, $memberId, $branch_id, $branchCode, $amountArray1, $typeArray, $receipts_for, $account_no = '0');
                    $recipt_id = $createRecipt;
                    $isReceipt = 'yes';
                } elseif ($request['rd_account'] == 0 && $request['ssb_account'] == 1 && $rdset == 1) {
                    $amountArray1 = array('1' => $request['rd_amount']);
                    $typeArray = array('1' => 2);
                    $receipts_for = 4;
                    $createRecipt = CommanController::createPaymentRecipt(0, 0, $memberId, $branch_id, $branchCode, $amountArray1, $typeArray, $receipts_for, $account_no = '0');
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
                    $createRecipt = CommanController::createPaymentRecipt(0, 0, $memberId, $branch_id, $branchCode, $amountArray1, $typeArray, $receipts_for, $account_no = '0');
                    $recipt_id = $createRecipt;
                    $isReceipt = 'yes';
                }
                $dataMsg['msg_type'] = 'success';
                $dataMsg['msg'] = 'Associate created Successfully';
                $dataMsg['reciept_generate '] = $isReceipt;
                $dataMsg['reciept_id'] = $recipt_id;
            }
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
    public function edit($id)
    {
        $data['title'] = 'Associate | Edit';
        $data['relations'] = relationsList();
        $data['memberData'] = Member::with('associate_branch')->where('id', $id)->first();
        $data['idProofDetail'] = \App\Models\MemberIdProof::where('member_id', $id)->first();
        $data['nomineeDetail'] = \App\Models\MemberNominee::where('member_id', $id)->first();
        $data['guarantorDetail'] = \App\Models\AssociateGuarantor::where('member_id', $id)->first();
        $data['dependentDetail'] = \App\Models\AssociateDependent::where('member_id', $id)->get();
        return view('templates.admin.associate.edit', $data);
    }
    public function update(Request $request)
    {
        Session::put('created_at', $request['created_at']);
        $globaldate = $request['created_at'];
        $rules = [
            'form_no' => ['required', 'numeric'],
            'application_date' => ['required'],
        ];
        $customMessages = [
            'required' => 'Please enter :attribute.',
            'unique' => ' :Attribute already exists.',
            'numeric' => ' :Attribute valid number.'
        ];
        $this->validate($request, $rules, $customMessages);
        DB::beginTransaction();
        try {
            $memberId = $request['id'];
            $dataAssociate['associate_form_no'] = $request['form_no'];
            $dataAssociate['associate_join_date'] = date("Y-m-d", strtotime(str_replace('/', '-', $request['application_date'])));
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
            if ($_POST['old_dep_count'] > 0) {
                foreach (($_POST['old_dep_id']) as $key => $option) {
                    if (isset($_POST['old_dep_first_name'][$key]) && $_POST['old_dep_first_name'][$key] != '') {
                        $associateDep['name'] = $_POST['old_dep_first_name'][$key];
                        if ($_POST['old_dep_age'][$key] != '') {
                            $associateDep['age'] = $_POST['old_dep_age'][$key];
                        }
                        if ($_POST['old_dep_relation'][$key] != '') {
                            $associateDep['relation'] = $_POST['old_dep_relation'][$key];
                        }
                        if ($_POST['old_dep_income'][$key] != '') {
                            $associateDep['monthly_income'] = $_POST['old_dep_income'][$key];
                        }
                        $associateDep['gender'] = $_POST['old_dep_gender'][$key];
                        $associateDep['marital_status'] = $_POST['old_dep_marital_status'][$key];
                        $associateDep['living_with_associate'] = $_POST['old_dep_living'][$key];
                        $associateDep['dependent_type'] = $_POST['old_dep_type'][$key];
                        $associateDepUpdate = \App\Models\AssociateDependent::find($_POST['old_dep_id'][$key]);
                        $associateDepUpdate->update($associateDep);
                    }
                }
            }
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
            $associateGuarantor['first_name'] = $request['first_g_first_name'];
            $associateGuarantor['first_mobile_no'] = $request['first_g_Mobile_no'];
            $associateGuarantor['first_address'] = $request['first_g_address'];
            $associateGuarantor['second_name'] = $request['second_g_first_name'];
            $associateGuarantor['second_mobile_no'] = $request['second_g_Mobile_no'];
            $associateGuarantor['second_address'] = $request['second_g_address'];
            $associateUpdate = \App\Models\AssociateGuarantor::find($request['guarantor_id']);
            $associateUpdate->update($associateGuarantor);
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('alert', $ex->getMessage());
        }
        if (isset($request['action']) && $request['action'] == 'change-request') {
            $correctionRequest = CorrectionRequests::find($request['requestid']);
            $crData['status'] = 1;
            $correctionRequest->update($crData);
            return redirect('admin/associate-edit/' . $request['id'] . '?action=change-request&request-id=' . $request['requestid'] . '')->with('success', 'Associate updated successfully!');
        } else {
            return redirect()->route('admin.associate-edit', ['id' => $request['id']])->with('success', 'Associate updated successfully!');
        }
    }
    public function deleteDependent(Request $request)
    {
        $resCount = 0;
        $res = \App\Models\AssociateDependent::where('id', $request['id'])->delete();
        if ($res) {
            $resCount = 1;
        }
        $return_array = compact('resCount');
        return json_encode($return_array);
    }
    public function idCard($id)
    {
        $data['title'] = 'Associate | Id Card';
        $data['memberData'] = Member::with('branch')->where('id', $id)->first();
        $data['logo'] = \App\Models\Logo::first();
        $data['setting'] = \App\Models\Settings::first();
        return view('templates.admin.associate.idcard', $data);
    }
    public function tree_hierarchy(Request $request)
    {
        if (check_my_permission(Auth::user()->id, "7") != "1") {
            return redirect()->route('admin.dashboard');
        }
        $data['title'] = 'Associate | Tree';
        $data['code'] = '';
        if (Request1::isMethod('post')) {
            $code = $request['associate_code'];
            $data['code'] = $code;
            $dataS = Member::where('associate_no', $code);
            if (Auth::user()->branch_id > 0) {
                $dataS = $dataS->where('branch_id', Auth::user()->branch_id);
            }
            $data['associate'] = $dataS->first();
        }
        return view('templates.admin.associate.tree', $data);
    }
    public function get($data)
    {
        $data = \App\Models\AssociateTree::where('member_id', 16)->get();
        foreach ($data as $value) {
            echo $value->id . '=' . $value->member_id . '=' . $value->senior_id . '<br>';
            $this->get($value->subcategory1);
        }
        die;
        foreach ($data as $value) {
            echo $value->id . '=' . $value->member_id . '=' . $value->senior_id . '<br>';
            $this->get($value->subcategory1);
        }
    }
    public function upgrade()
    {
        if (check_my_permission(Auth::user()->id, "9") != "1") {
            return redirect()->route('admin.dashboard');
        }
        $data['title'] = 'Associate | Upgrade';
        //$data['branch']=Branch::where('status',1)->get();
        return view('templates.admin.associate.upgrade', $data);
    }
    public function getAssociateData(Request $request)
    {
        $data = Member::where('associate_no', $request->code)->where('is_deleted', 0);
        if (Auth::user()->branch_id > 0) {
            $data = $data->where('branch_id', Auth::user()->branch_id);
        }
        $data = $data->first();
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
                    // $businessTarget = \App\Models\BusinessTarget::where('carder_id',$carder)->first(); 
                    // $memberCount = \App\Models\AssociateCommission::where('member_id',$id)->where('type',1)->where('status',1)->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate])->count();  
                    //  $commissionSelf = \App\Models\AssociateKotaBusiness::where('member_id',$id)->where('type',1)->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate])->sum('business_amount');
                    // $commissionCredit=getKotaBusinessTeam($id,$startDate,$endDate);
                    //print_r($commissionCredit);die;
                    $commissionCredit = $commissionSelf = $memberCount = $businessTarget = 0;
                    return \Response::json(['view' => view('templates.admin.associate.partials.associate_detail', ['memberData' => $data, 'businessTarget' => $businessTarget, 'memberCount' => $memberCount, 'commissionSelf' => $commissionSelf, 'commissionCredit' => $commissionCredit, 'carder' => $carder, 'type' => $type])->render(), 'msg_type' => 'success', 'id' => $id, 'carder' => $carder]);
                } else {
                    return \Response::json(['view' => 'No data found', 'msg_type' => 'error1']);
                }
            }
        } else {
            return \Response::json(['view' => 'No data found', 'msg_type' => 'error']);
        }
    }
    public function associateCommission()
    {
        if (check_my_permission(Auth::user()->id, "12") != "1") {
            return redirect()->route('admin.dashboard');
        }
        $data['title'] = 'Associate Commission | Listing';
        $data['branch'] = Branch::where('status', 1)->get(['id', 'name']);
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

        return view('templates.admin.associate.commission', $data);
    }
    public function associateCommissionList(Request $request)
    {
        if ($request->ajax() && check_my_permission(Auth::user()->id, "12") == "1") {
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
                // $data = AssociateCommission::with('member','investment')->where('type','3');
                $data = Member::select(['id', 'first_name', 'last_name', 'associate_no', 'associate_senior_code', 'associate_branch_id', 'member_id', 'created_at', 'associate_senior_id', 'current_carder_id', 'company_id'])
                    ->with([
                        'associate_branch' => function ($query) {
                            $query->select('id', 'name', 'branch_code', 'sector', 'regan', 'zone');
                        }
                    ])
                    ->with([
                        'getCarderNameCustom' => function ($q) {
                            $q->select('id', 'name', 'short_name');
                        }
                    ])
                    ->with('company:id,name')
                    ->where('member_id', '!=', '0CI09999999')->where('is_associate', 1);
                if (Auth::user()->branch_id > 0) {
                    $id = Auth::user()->branch_id;
                    $data = $data->where('associate_branch_id', '=', $id);
                }
                if ($arrFormData['associate_code'] != '') {
                    $associate_code = $arrFormData['associate_code'];
                    $data = $data->where('associate_no', 'LIKE', '%' . $associate_code . '%');
                }
                if (isset($arrFormData['branch_id']) && $arrFormData['branch_id'] != '' && $arrFormData['branch_id'] != 0) {
                    $id = $arrFormData['branch_id'];
                    $data = $data->where('associate_branch_id', '=', $id);
                }
                if ($arrFormData['associate_name'] != '') {
                    $name = $arrFormData['associate_name'];
                    $data = $data->where(function ($query) use ($name) {
                        $query->where('first_name', 'LIKE', '%' . $name . '%')->orWhere('last_name', 'LIKE', '%' . $name . '%')->orWhere(DB::raw('concat(first_name," ",last_name)'), 'LIKE', "%$name%");
                    });
                }
                $count = $data->orderby('id', 'DESC')->count('id');
                $data = $data->orderby('id', 'DESC')->offset($_POST['start'])->limit($_POST['length'])->get();
                $dataCount = Member::where('member_id', '!=', '0CI09999999')->where('is_associate', 1);
                if (Auth::user()->branch_id > 0) {
                    $dataCount = $dataCount->where('associate_branch_id', '=', Auth::user()
                        ->branch_id);
                }
                $totalCount = $dataCount->count('id');
                $sno = $_POST['start'];
                $rowReturn = array();
                foreach ($data as $row) {
                    $sno++;
                    $val['DT_RowIndex'] = $sno;
                    $val['branch_name'] = "N/A";
                    if ($row['associate_branch']) {
                        $val['branch_name'] = $row['associate_branch']->name . '(' . $row['associate_branch']->branch_code . ')';
                    }

                    $val['associate_name'] = $row->first_name . ' ' . $row->last_name;
                    $val['associate_code'] = $row->associate_no;
                    $val['associate_carder'] = $row['getCarderNameCustom']->name . '(' . $row['getCarderNameCustom']->short_name . ')';
                    // $val['senior_code'] = $row->associate_senior_code;
                    // $val['senior_name'] = $row['seniorData']->first_name . ' ' . $row['seniorData']->last_name;
                    // $val['senior_carder'] = $row['seniorData']['getCarderNameCustom']->name . '(' . $row['seniorData']['getCarderNameCustom']->short_name . ')';

                    $val['total_amount'] = 0; //getAssociateTotalCommission($row->id,$startDate,$endDate,'total_amount');
                    // investment commission with collection % and loan recovery %

                    if (($year == 2022 && $month <= 11) || ($year < 2022)) {
                        $val['commission_amount'] = getAssociateTotalCommissionAdmin($row->id, $startDate, $endDate, 'commission_amount');
                        $val['collection_amount'] = getTotalCollection($row->id, $startDate, $endDate); //plan no 4,9 not include in this and loan recovery also not added
                        $val['collection_amount_all'] = getTotalCollection_all($row->id, $startDate, $endDate); //Investment total renewal all type add and loan recovery also not added
                        $btn = 'N/A';
                    } else {
                        $val['commission_amount'] = getAssociateTotalCommissionAdminNew($row->id, $year, $month, $company_id);
                        $total_amount_query = \App\Models\CommissionFuleCollection::select('qualifying_amount', 'total_amount')->where('associate_id', $row->id)
                            ->where('month', $month)
                            ->where('year', $year);
                        if ($company_id > 0) {
                            $total_amount_query = $total_amount_query->where('company_id', $company_id);
                        }
                        $qualifying_amount_sum = $total_amount_query->sum('qualifying_amount');
                        $total_amount_sum = $total_amount_query->sum('total_amount');
                        $val['collection_amount'] = number_format($qualifying_amount_sum, 2, '.', '');
                        $val['collection_amount_all'] = number_format($total_amount_sum, 2, '.', '');
                        $btn = '<div class="list-icons"><div class="dropdown"><a href="#" class="list-icons-item" data-toggle="dropdown"><i class="icon-menu9"></i></a><div class="dropdown-menu dropdown-menu-right">';
                        // $url = URL::to("admin/associate-commission-detail/" . $row->id . "/" . $year . "/" . $month . "");
                        $url = URL::to("admin/associate-commission-detail/" . $row->id . "?&year=$year&month=$month");
                        $url1 = URL::to("admin/associate/loan-commission-detail/" . $row->id . "?&year=$year&month=$month");
                        // $url1 = route('admin.associate.commission.detail_loan',[$row->id,$year,$month]);
                        $btn .= '<a class="dropdown-item" href="' . $url . '" title="Investment Commission Detail"><i class="icon-eye-blocked2  mr-2"></i>Investment Commission Detail</a>  ';
                        $btn .= '<a class="dropdown-item" href="' . $url1 . '" title="Loan Commission Detail"><i class="icon-eye-blocked2  mr-2"></i>Loan Commission Detail</a>  ';
                        $btn .= '</div></div></div>';
                    }
                    $val['action'] = $btn;
                    $rowReturn[] = $val;
                }
                $output = array(
                    "draw" => $_POST['draw'],
                    "recordsTotal" => $totalCount,
                    "recordsFiltered" => $count,
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
    public function kotaBusinessReportListing(Request $request)
    {
        if ($request->ajax() && check_my_permission(Auth::user()->id, "13") == "1") {
            $arrFormData = array();
            if (!empty($_POST['searchform'])) {
                foreach ($_POST['searchform'] as $frm_data) {
                    $arrFormData[$frm_data['name']] = $frm_data['value'];
                }
            }
            $startDate = date('Y-m-d', strtotime(convertDate($arrFormData['start_date'])));
            $endDate = date('Y-m-d', strtotime(convertDate($arrFormData['end_date'])));
            if (isset($arrFormData['is_search']) && $arrFormData['is_search'] == 'yes') {
                $data = Member::select('id', 'mobile_no', 'is_block', 'associate_status', 'associate_join_date', 'current_carder_id', 'first_name', 'last_name', 'associate_no', 'associate_branch_id', 'associate_senior_id')->with([
                    'associate_branch' => function ($q) {
                        $q->select('id', 'branch_code', 'name', 'sector', 'regan', 'zone');
                    }
                ])
                    ->with([
                        'seniorData' => function ($q) {
                            $q->select(['id', 'first_name', 'last_name', 'associate_no', 'associate_senior_id', 'current_carder_id'])
                                ->with([
                                    'getCarderNameCustom' => function ($q) {
                                        $q->select('id', 'name', 'short_name');
                                    }
                                ]);
                        }
                    ])
                    ->with([
                        'getCarderNameCustom' => function ($q) {
                            $q->select('id', 'name', 'short_name');
                        }
                    ])
                    ->with([
                        'getBusinessTargetAmt' => function ($q) {
                            $q->select('id', 'self', 'credit');
                        }
                    ])
                    ->where('member_id', '!=', '9999999')->where('is_associate', 1);
                if (Auth::user()->branch_id > 0) {
                    $id = Auth::user()->branch_id;
                    $data = $data->where('associate_branch_id', '=', $id);
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
                if ($arrFormData['associate_code'] != '') {
                    $associate_code = $arrFormData['associate_code'];
                    $data = $data->where('associate_no', 'LIKE', '%' . $associate_code . '%');
                }
                if (isset($arrFormData['branch_id']) && $arrFormData['branch_id'] != '') {
                    $id = $arrFormData['branch_id'];
                    $data = $data->where('associate_branch_id', '=', $id);
                }
                if ($arrFormData['associate_name'] != '') {
                    $name = $arrFormData['associate_name'];
                    $data = $data->where(function ($query) use ($name) {
                        $query->where('first_name', 'LIKE', '%' . $name . '%')->orWhere('last_name', 'LIKE', '%' . $name . '%')->orWhere(DB::raw('concat(first_name," ",last_name)'), 'LIKE', "%$name%");
                    });
                }
                if ($arrFormData['cader_id'] != '') {
                    $cader_id = $arrFormData['cader_id'];
                    $data = $data->where('current_carder_id', '=', $cader_id);
                }
                /*if($arrFormData['select_plan'] !=''){
                $select_plan =$arrFormData['select_plan'];                     
                }*/
                $count = $data->orderby('id', 'DESC')->count('id');
                $data = $data->orderby('id', 'DESC')->offset($_POST['start'])->limit($_POST['length'])->get();
                $dataCount = Member::where('member_id', '!=', '9999999')->where('is_associate', 1);
                if (Auth::user()->branch_id > 0) {
                    $dataCount = $dataCount->where('associate_branch_id', '=', Auth::user()->branch_id);
                }
                $totalCount = $dataCount->count('id');
                $sno = $_POST['start'];
                $rowReturn = array();
                foreach ($data as $row) {
                    $sno++;
                    $val['DT_RowIndex'] = $sno;
                    $val['branch_code'] = $row['associate_branch']->branch_code;
                    $val['branch_name'] = $row['associate_branch']->name;
                    $val['sector'] = $row['associate_branch']->sector;
                    $val['regan'] = $row['associate_branch']->regan;
                    $val['zone'] = $row['associate_branch']->zone;
                    $val['senior_code'] = $row['seniorData']->associate_no;
                    $val['senior_name'] = $row['seniorData']->first_name . ' ' . $row['seniorData']->last_name;
                    $val['quota_business_target_self_amt'] = $row['getBusinessTargetAmt']->self; //getBusinessTargetAmt($row->current_carder_id)->self;
                    $val['achieved_target_self_amt'] = round(\App\Models\AssociateKotaBusiness::where('member_id', $row->id)->where('type', 1)->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate])->sum('business_amount'), 2);
                    $targetSelf = $row['getBusinessTargetAmt']->self; //getBusinessTargetAmt($row->current_carder_id)->self;
                    $achievedSelf = \App\Models\AssociateKotaBusiness::where('member_id', $row->id)->where('type', 1)->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate])->sum('business_amount');
                    if ($achievedSelf > 0) {
                        $targetSelfPer = 100 - ($achievedSelf / $targetSelf) * 100;
                    } else {
                        $targetSelfPer = 100;
                    }
                    $val['quota_business_target_self_percentage'] = round($targetSelfPer, 3);
                    $targetSelf = $row['getBusinessTargetAmt']->self; //getBusinessTargetAmt($row->current_carder_id)->self;
                    $achievedSelf = \App\Models\AssociateKotaBusiness::where('member_id', $row->id)->where('type', 1)->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate])->sum('business_amount');
                    if ($achievedSelf > 0) {
                        $achievedSelfPer = ($achievedSelf / $targetSelf) * 100;
                    } else {
                        $achievedSelfPer = 0;
                    }
                    $val['achieved_target_self_percentage'] = round($achievedSelfPer, 3);
                    $val['associate_code'] = $row->associate_no;
                    $val['associate_name'] = $row->first_name . ' ' . $row->last_name;
                    if ($row->current_carder_id > 1) {
                        //getBusinessTargetAmt($row->current_carder_id)->credit
                        $targetTeam = round($row['getBusinessTargetAmt']->credit, 2);
                    } else {
                        $targetTeam = 'N/A';
                    }
                    $val['quota_business_target_team_amt'] = $targetTeam;
                    if ($row->current_carder_id > 1) {
                        $achievedTarget = round(getKotaBusinessTeam($row->id, $startDate, $endDate), 2);
                    } else {
                        $achievedTarget = 'N/A';
                    }
                    $val['achieved_target_team_amt'] = $achievedTarget;
                    if ($row->current_carder_id > 1) {
                        $targetTeam = $row['getBusinessTargetAmt']->credit; //getBusinessTargetAmt( $row->current_carder_id )->credit;
                        $targetteamAchivede = getKotaBusinessTeam($row->id, $startDate, $endDate);
                        $achievedTeamfPer = round(100.000 - ($targetteamAchivede / $targetTeam) * 100, 2);
                    } else {
                        $achievedTeamfPer = 'N/A';
                    }
                    $val['quota_business_target_team_percentage'] = $achievedTeamfPer;
                    if ($row->current_carder_id > 1) {
                        $targetTeam = $row['getBusinessTargetAmt']->credit; //getBusinessTargetAmt ( $row->current_carder_id )->credit;
                        $achievedTarget = getKotaBusinessTeam($row->id, $startDate, $endDate);
                        $achievedTeamfPer = round(($achievedTarget / $targetTeam) * 100, 2);
                    } else {
                        $achievedTeamfPer = 'N/A';
                    }
                    $val['achieved_target_team_percentage'] = $achievedTeamfPer;
                    $val['joining_date'] = date("d/m/Y", strtotime($row->associate_join_date));
                    $val['mobile_number'] = $row->mobile_no;
                    if ($row->is_block == 0) {
                        if ($row->associate_status == 1) {
                            $status = 'Active';
                        } else {
                            $status = 'Inactive';
                        }
                    } else {
                        $status = 'Blocked';
                    }
                    $val['status'] = $status;
                    //getCarderNameFull($row->current_carder_id);
                    $val['associate_carder'] = $row['getCarderNameCustom']->name; //
                    //getSeniorData($row->associate_senior_id, 'current_carder_id')
                    $val['senior_carder'] = $row['seniorData']['getCarderNameCustom']->name;
                    //getCarderNameFull($row['seniorData']->current_carder_id);
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
    public function getAssociateCarder(Request $request)
    {
        $associateCode = $request->associate_code;
        $carder = Member::where('associate_no', $associateCode)->first('current_carder_id');
        //$resCount = count($carder);
        if ($carder) {
            $return_array = compact('carder');
        } else {
            $return_array = 0;
        }
        return json_encode($return_array);
    }
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
    public function upgrade_save(Request $request)
    {
        Session::put('created_at', $request['created_at']);
        $globaldate = $request['created_at'];
        // print_r($_POST);die;
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
            $associate_carder_id = DB::getPdo()->lastInsertId();
            //$associate_carder_id = $associateCarderInsert->id;
            $encodeDate = json_encode($carderDetail);
            // $arrs = array("associate_carder_id" => $associate_carder_id, "type" => "2", "account_head_id" => 0, "user_id" => Auth::user()->id, "message" => "Associate Carder Change – Upgrade", "data" => $encodeDate);
            // DB::table('user_log')->insert($arrs);
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
                // $associateTree['parent_id'] = $getParentID->id;
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
    public function active_deactivate()
    {
        if (check_my_permission(Auth::user()->id, "11") != "1") {
            return redirect()->route('admin.dashboard');
        }
        $data['title'] = 'Associate | Status';
        //$data['branch']=Branch::where('status',1)->get();
        return view('templates.admin.associate.status', $data);
    }
    public function getAssociateDataAll(Request $request)
    {
        $data = Member::where('associate_no', $request->code)->where('is_deleted', 0);
        if (Auth::user()->branch_id > 0) {
            $data = $data->where('branch_id', Auth::user()->branch_id);
        }
        $data = $data->first();
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
                $memberCount = \App\Models\AssociateCommission::where('member_id', $id)->where('type', 1)->where('status', 1)->count();
                $commissionSelf = 0; // \App\Models\AssociateKotaBusiness::where('member_id',$id)->where('type',1)->sum('business_amount'); 
                $commissionCredit = 0; //getKotaBusinessTeam($id,$startDate,$endDate);
                //print_r($commissionCredit);die;
                return \Response::json(['view' => view('templates.admin.associate.partials.associate_detail', ['memberData' => $data, 'businessTarget' => $businessTarget, 'memberCount' => $memberCount, 'commissionSelf' => $commissionSelf, 'commissionCredit' => $commissionCredit, 'carder' => $carder, 'type' => $type])->render(), 'msg_type' => 'success', 'id' => $id, 'carder' => $carder]);
            }
        } else {
            return \Response::json(['view' => 'No data found', 'msg_type' => 'error']);
        }
    }
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
                $encodeDate = json_encode($status);
                // $arrs = array("AssociateStatusID" => $associateStatusInsert->id, "type" => "2", "account_head_id" => 0, "user_id" => Auth::user()->id, "message" => "Associate Deactivate or Activate", "data" => $encodeDate);
                // DB::table('user_log')->insert($arrs);
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
    public function downgrade()
    {
        if (check_my_permission(Auth::user()->id, "10") != "1") {
            return redirect()->route('admin.dashboard');
        }
        $data['title'] = 'Associate | Downgrade ';
        //$data['branch']=Branch::where('status',1)->get();
        return view('templates.admin.associate.downgrade', $data);
    }
    public function downgrade_save(Request $request)
    {
        // print_r($_POST);die;
        Session::put('created_at', $request['created_at']);
        $globaldate = $request['created_at'];
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
                        if ($getMemberIdChild) {
                            $memberTreeChild = \App\Models\AssociateTree::find($getMemberIdChild->id);
                            $memberTreeChild->update($associateTreeChild);
                        }
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
            $associate_carder_id = DB::getPdo()->lastInsertId();
            //$associate_carder_id = $associateCarderInsert->id;
            $encodeDate = json_encode($carderDetail);
            // $arrs = array("associate_carder_id" => $associate_carder_id, "type" => "2", "account_head_id" => 0, "user_id" => Auth::user()->id, "message" => "Associate Carder Change – Downgrade", "data" => $encodeDate);
            // DB::table('user_log')->insert($arrs);
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
    public function associateCommissionDetail(Request $request, $id)
    {
        $data['title'] = 'Associate Commission Detail | Listing';
        $data['plans'] = Plans::where('plan_category_code', '!=', 'S')->get();
        $data['member'] = Member::where('id', $id)->first();
        $data['years'] = CommissionLeaserMonthly::where('is_deleted', 0)
            ->where('year', '>', 2022)
            ->distinct()
            ->get('year');
        $data['months'] = CommissionLeaserMonthly::where('is_deleted', 0)
            ->where('year', '>', 2022)
            ->distinct()
            ->get(['month', 'year']);

        return view('templates.admin.associate.commissionDetail', $data);
    }
    public function associateCommissionDetailList(Request $request)
    {
        if ($request->ajax()) {
            $arrFormData = array();
            $arrFormData['year'] = $request->year;
            $arrFormData['month'] = $request->month;
            $arrFormData['plan_id'] = $request->plan_id;
            $arrFormData['is_search'] = $request->is_search;
            $arrFormData['commission_export'] = $request->commission_export;
            $arrFormData['id'] = $request->id;
            if (isset($arrFormData['is_search']) && $arrFormData['is_search'] == 'yes') {
                $data = AssociateMonthlyCommission::where('assocaite_id', $arrFormData['id'])->with([
                    'investment' => function ($q) {
                        $q->select('id', 'plan_id', 'account_number');
                    }
                ])->with('investment.plan:id,name')
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
            }
            $count = $data->orderby('id', 'DESC')->count('id');
            $data = $data->orderby('id', 'DESC')->offset($_POST['start'])->limit($_POST['length'])->get(['id', 'type_id', 'total_amount', 'commission_amount', 'qualifying_amount', 'percentage', 'type', 'cadre_from', 'cadre_to', 'month', 'created_at', 'assocaite_id', 'commission_for_year', 'commission_for_month']);
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
                $val['plan_name'] = $row['investment']->plan->name;
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
            $output = array("draw" => $_POST['draw'], "recordsTotal" => $totalCount, "recordsFiltered" => $count, "data" => $rowReturn, );
            return json_encode($output);
        }
    }
    public function tree_view($id)
    {
        $data['title'] = 'Associate | Tree View';
        $data['associate'] = Member::where('id', $id)->first();
        return view('templates.admin.associate.tree_view', $data);
    }
    public function commissionTransfer(Request $request)
    {
        if (check_my_permission(Auth::user()->id, "14") != "1") {
            return redirect()->route('admin.dashboard');
        }
        $data['title'] = 'Commission Transfer | Ledger Create';
        $data['start_date'] = '';
        $data['end_date'] = '';
        if (Request1::isMethod('post')) {
            $start_date = $request['start_date'];
            $end_date = $request['end_date'];
            $startDateDb = date("Y-m-d", strtotime(convertDate($start_date)));
            $endDateDb = date("Y-m-d", strtotime(convertDate($end_date)));
            $data['start_date'] = $request['start_date'];
            $data['end_date'] = $request['end_date'];
            $data['start_date_time'] = $startDateDb;
            $data['end_date_time'] = $endDateDb;
            $data['code'] = 1;
            $mid = Member::where('associate_no', '9999999')->first('id');
            $midId = Member::where('associate_no', '!=', '9999999')->where('id', '>', 10000)->where('id', '<=', 15000)->get(['id']);
            //$data['total_commission']=AssociateCommission::select(DB::raw('sum(commission_amount) as total'),DB::raw('member_id as member_id') )->where('type','>',2)->where('status',1)->where('member_id','!=',$mid->id)->where('is_distribute',0)->whereBetween(\DB::raw('DATE(created_at)'), [$startDateDb, $endDateDb])->groupBy(DB::raw('member_id'))->get(); 
            $data['total_commission'] = AssociateCommission::select(DB::raw('sum(commission_amount) as total'), DB::raw('member_id as member_id'))->where('type', '>', 2)->where('status', 1) /*->whereIn('member_id',$midId)*/ ->where('is_distribute', 0)->whereBetween(\DB::raw('DATE(created_at)'), [$startDateDb, $endDateDb])->groupBy(DB::raw('member_id'))->get();
            //$data['total_commission']=AssociateCommission::select(DB::raw('sum(commission_amount) as total'),DB::raw('member_id as member_id') )->where('type','>',2)->where('status',1)->where('member_id','!=',$mid->id)->where('is_distribute',0)->whereDate('created_at','<=', $endDateDb)->groupBy(DB::raw('member_id'))->get();
        }
        return view('templates.admin.associate.commission_transfer', $data);
    }
    public function laserCheck(Request $request)
    {
        //echo $request->start_date;die;
        $data = \App\Models\CommissionLeaser::where('status', 1)->where([['start_date', '>', $request->start_date], ['end_date', '<=', $request->end_date]])->whereBetween('start_date', array($request->start_date, $request->end_date))
            ->WhereBetween('end_date', array($request->start_date, $request->end_date))->get();
        //print_r($data);die;
        $count = count($data);
        $return_array = compact('count');
        return json_encode($return_array);
    }
    public function commissionTransferSave(Request $request)
    {
        Session::put('created_at', $request['created_at']);
        $globaldate = $request['created_at'];
        DB::beginTransaction();
        try {
            $data = \App\Models\CommissionLeaser::where('status', 1)->where([['start_date', '>', $request->start_date_time], ['end_date', '<=', $request->end_date_time]])->whereBetween('start_date', array($request->start_date_time, $request->end_date_time))
                ->WhereBetween('end_date', array($request->start_date_time, $request->end_date_time))->get();
            $count = count($data);
            if ($count > 0) {
                return back()->with('error', 'Selected date range already exits');
            }
            $leaser['start_date'] = $request->start_date_time;
            $leaser['end_date'] = $request->end_date_time;
            $startDateDb = date("Y-m-d", strtotime(convertDate($request->start_date_time)));
            $endDateDb = date("Y-m-d", strtotime(convertDate($request->end_date_time)));
            $start_date = date("My", strtotime($request->start_date_time));
            $end_date = date("My", strtotime($request->end_date_time));
            $sms_date = date("MY", strtotime($request->start_date_time));
            $leaser['total_amount'] = $request->total;
            $leaser['ledger_amount'] = $request->totalFinalAmount;
            $leaser['total_fuel'] = $request->totalFuleAmount;
            $leaser['total_collection'] = $request->totalCollection;
            $leaser['created_at'] = $globaldate;
            $leaserCreate = \App\Models\CommissionLeaser::create($leaser);
            $leaserId = $leaserCreate->id;
            $encodeDate = json_encode($leaser);
            $arrs = array("leaser_id" => $leaserCreate->id, "type" => "2", "account_head_id" => 0, "user_id" => Auth::user()->id, "message" => "Associate ledger Create", "data" => $encodeDate);
            DB::table('user_log')->insert($arrs);
            if (count($_POST['member_id']) > 0) {
                foreach ($_POST['member_id'] as $k => $val) {
                    $commission = AssociateCommission::where('type', '>', 2)->where('status', 1)->where('member_id', $_POST['member_id'][$k])->where('is_distribute', 0)->whereBetween(DB::raw('DATE(created_at)'), [$request->start_date_time, $request->end_date_time])->pluck('id')->toArray();
                    $a = implode(',', $commission);
                    $comDataUpdate = AssociateCommission::where('type', '>', 2)->where('status', 1)->where('member_id', $_POST['member_id'][$k])->where('is_distribute', 0)->whereBetween(DB::raw('DATE(created_at)'), [$request->start_date_time, $request->end_date_time])->update(['is_distribute' => 1]);
                    // $comDataUpdate = AssociateCommission::where('type','>',2)->where('status',1)->where('member_id',$_POST['member_id'][$k])->where('is_distribute',0)->whereDate('created_at','<=',$endDateDb)->update([ 'is_distribute' => 1 ]);
                    $leaser1['commission_leaser_id'] = $leaserId;
                    $leaser1['member_id'] = $_POST['member_id'][$k];
                    $leaser1['amount_tds'] = ($_POST['amount'][$k]);
                    $leaser1['amount'] = ($_POST['amount'][$k] - $_POST['tds'][$k]);
                    $leaser1['total_row'] = count($commission);
                    $leaser1['commission_id'] = $a;
                    $leaser1['total_tds'] = $_POST['tds'][$k];
                    $leaser1['fuel'] = $_POST['fule'][$k];
                    $leaser1['collection'] = $_POST['collection'][$k];
                    $leaser1['created_at'] = $globaldate;
                    $leaserCreate = \App\Models\CommissionLeaserDetail::create($leaser1);
                    $ssbAccountDetail = getMemberSsbAccountDetail($_POST['member_id'][$k]);
                    $detail = 'Comm ' . $start_date;
                    // $ssbTranCalculation = CommanController::ssbTransaction($ssbAccountDetail->id,$ssbAccountDetail->account_no,$ssbAccountDetail->balance,$_POST['amount'][$k],$detail,'INR','CR',3);
                    /*********************  ssb transaction ******************************/
                    $amounTra = ($_POST['amount'][$k] - $_POST['tds'][$k]);
                    $transactionBydate = \App\Models\SavingAccountTranscation::select('opening_balance')->where('saving_account_id', $ssbAccountDetail->id)->whereDate('created_at', '<=', $globaldate)->orderby('id', 'desc')->first();
                    $balanceTra = $transactionBydate->opening_balance;
                    $dataSsb['deposit'] = $amounTra;
                    $ssbBalance = $balanceTra + $amounTra;
                    $dataSsb['saving_account_id'] = $ssbAccountDetail->id;
                    $dataSsb['account_no'] = $ssbAccountDetail->account_no;
                    $dataSsb['opening_balance'] = $ssbBalance;
                    $dataSsb['type'] = 3;
                    $dataSsb['amount'] = $balanceTra;
                    $dataSsb['description'] = $detail;
                    $dataSsb['currency_code'] = 'INR';
                    $dataSsb['payment_type'] = 'CR';
                    $dataSsb['payment_mode'] = 3;
                    $dataSsb['created_at'] = $globaldate;
                    $resSsb = \App\Models\SavingAccountTranscation::create($dataSsb);
                    $CommTrnId = $resSsb->id;
                    // $ssbBalance = $balance-$amount;
                    $sResult = \App\Models\SavingAccount::find($ssbAccountDetail->id);
                    $sData['balance'] = $ssbBalance;
                    $sResult->update($sData);
                    /*********************  ssb transaction ******************************/
                    $amountArray = array('1' => $_POST['amount'][$k]);
                    $member = $memDetail = Member::where('id', $_POST['member_id'][$k])->first();
                    $branch_id = $member->associate_branch_id;
                    $branchCode = $member->associate_branch_code;
                    $deposit_by_name = $member->first_name . ' ' . $member->last_name;
                    $deposit_by_id = $_POST['member_id'][$k];
                    //$satRefId = CommanController::createTransactionReferences($ssbAccountDetail->id,$ssbAccountDetail->member_investments_id);
                    $rdCreateTran = CommanController::createTransaction(NULL, 1, $ssbAccountDetail->id, $_POST['member_id'][$k], $branch_id, $branchCode, $amountArray, 5, $deposit_by_name, $deposit_by_id, $ssbAccountDetail->account_no, $cheque_dd_no = 0, $bank_name = null, $branch_name = null, $payment_date = null, $online_payment_id = null, $online_payment_by = null, $saving_account_id = 0, 'CR');
                    $sms_text_fule = '';
                    if ($_POST['fule'][$k] > 0) {
                        $detail = 'Fuel ' . $start_date;
                        $ssbTranCalculation = CommanController::ssbTransactionModify($ssbAccountDetail->id, $ssbAccountDetail->account_no, $ssbBalance, $_POST['fule'][$k], $detail, 'INR', 'CR', 3, $branch_id = NULL, $associate_id = NULL, $type = 4);
                        $fuleTranId = $ssbTranCalculation;
                        $amountArray = array('1' => $_POST['fule'][$k]);
                        $member = Member::where('id', $_POST['member_id'][$k])->first();
                        $branch_id = $member->associate_branch_id;
                        $branchCode = $member->associate_branch_code;
                        $deposit_by_name = $member->first_name . ' ' . $member->last_name;
                        $deposit_by_id = $_POST['member_id'][$k];
                        $rdCreateTran = CommanController::createTransaction(NULL, 1, $ssbAccountDetail->id, $_POST['member_id'][$k], $branch_id, $branchCode, $amountArray, 5, $deposit_by_name, $deposit_by_id, $ssbAccountDetail->account_no, $cheque_dd_no = 0, $bank_name = null, $branch_name = null, $payment_date = null, $online_payment_id = null, $online_payment_by = null, $saving_account_id = 0, 'CR');
                        $sms_text_fule = 'and Monthly Fuel amount ' . $sms_date . ' has been credited with Rs.' . $_POST['fule'][$k];
                        $sms_text = 'Your Monthly Commission ' . $sms_date . ' has been credited with Rs.' . $amounTra . ' and Monthly Fuel amount ' . $sms_date . ' has been credited with Rs.' . $_POST['fule'][$k] . ' in Saving A/c ' . $ssbAccountDetail->account_no . ' on ' . date("d-M-Y") . ' Thanks. http://www.samraddhbestwin.com';
                        $templateId = 1207161648340908349;
                    }
                    /*--------------------------sms start -------------------------*/
                    $sms_text = 'Your Monthly Commission ' . $sms_date . ' has been credited with Rs.' . $amounTra . ' in Saving A/c ' . $ssbAccountDetail->account_no . ' on ' . date("d-M-Y") . ' Thanks. http://www.samraddhbestwin.com';
                    $templateId = 1207161648370549369;
                    $contactNumber = array();
                    $contactNumber[] = $member->mobile_no;
                    $sendToMember = new Sms();
                    $sendToMember->sendSms($contactNumber, $sms_text, $templateId);
                    /*--------------------------sms end -------------------------*/
                    /*****************************Head impliment start ******************************/
                    $payment_mode = 3;
                    $payment_type = 'CR';
                    $currency_code = 'INR';
                    $tdsAmount = $_POST['tds'][$k];
                    $fuleAmount = $_POST['fule'][$k];
                    $commAmount = ($_POST['amount'][$k] - $_POST['tds'][$k]);
                    $member_id = $_POST['member_id'][$k];
                    $amount = $commAmount + $fuleAmount + $tdsAmount;
                    $ssbAmountComm = $commAmount + $fuleAmount;
                    $daybookRef = CommanController::createBranchDayBookReferenceNew($amount, $globaldate);
                    $refId = $daybookRef;
                    $type_id = $ssbAccountDetail->id;
                    $associate_id = $ssbAccountDetail->associate_id;
                    $entry_date = date("Y-m-d", strtotime(convertDate($globaldate)));
                    $entry_time = date("H:i:s", strtotime(convertDate($globaldate)));
                    $created_by = 1;
                    $created_by_id = Auth::user()->id;
                    $created_at = date("Y-m-d H:i:s", strtotime(convertDate($globaldate)));
                    $updated_at = date("Y-m-d H:i:s", strtotime(convertDate($globaldate)));
                    $randNumber = mt_rand(0, 999999999999999);
                    $v_no = $randNumber;
                    $v_date = $entry_date;
                    $ssb_account_id_to = $type_id;
                    $ssb_account_tran_id_to = $CommTrnId;
                    $jv_unique_id = $ssb_account_tran_id_from = $cheque_type = $cheque_id = $cheque_bank_to_name = $cheque_bank_to_branch = $cheque_bank_to_ac_no = $cheque_bank_to_ifsc = $transction_bank_from_id = $transction_bank_from_ac_id = $transction_bank_to_name = $transction_bank_to_ac_no = $transction_bank_to_branch = $transction_bank_to_ifsc = $cheque_bank_from_id = $cheque_bank_ac_from_id = NULL;
                    $amount_to_name = NULL;
                    $amount_from_id = NULL;
                    $amount_from_name = NULL;
                    $amount_to_id = NULL;
                    $ssb_account_id_from = $cheque_no = $cheque_date = $cheque_bank_from = $cheque_bank_ac_from = $cheque_bank_ifsc_from = $cheque_bank_branch_from = $cheque_bank_to = $cheque_bank_ac_to = $transction_no = $transction_bank_from = $transction_bank_ac_from = $transction_bank_ifsc_from = $transction_bank_branch_from = $transction_bank_to = $transction_bank_ac_to = $transction_date = NULL;
                    $bank_id = NULL;
                    $bank_ac_id = NULL;
                    // commission entry----------------------------
                    $des = $memDetail->first_name . ' ' . $memDetail->last_name . '(' . $memDetail->associate_no . ')-commission paid for ' . date("F Y", strtotime($request->start_date_time));
                    $type = 4;
                    $sub_type = 45;
                    //commission ssb head
                    $head1ComSsb = 1;
                    $head2ComSsb = 8;
                    $head3ComSsb = 20;
                    $head4ComSsb = 56;
                    $head5ComSsb = NULL;
                    $allTranCommSSB = CommanController::headTransactionCreate($refId, $branch_id, $bank_id, $bank_ac_id, $head4ComSsb, $type, $sub_type, $type_id, $associate_id, $member_id, $branch_id_to = NULL, $branch_id_from = NULL, $opening_balance = NULL, $commAmount, $closing_balance = NULL, $des, 'CR', $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $CommTrnId, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $cheque_bank_to_name, $cheque_bank_to_branch, $cheque_bank_to_ac_no, $cheque_bank_to_ifsc, $transction_bank_from_id, $transction_bank_from_ac_id, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc, $cheque_bank_from_id, $cheque_bank_ac_from_id);
                    //commission head
                    $head1Com = 4;
                    $head2Com = 86;
                    $head3Com = 87;
                    $head4Com = NULL;
                    $head5Com = NULL;
                    $allTranComm = CommanController::headTransactionCreate($refId, $branch_id, $bank_id, $bank_ac_id, $head3Com, $type, $sub_type, $type_id, $associate_id, $member_id, $branch_id_to = NULL, $branch_id_from = NULL, $opening_balance = NULL, $commAmount, $closing_balance = NULL, $des, 'DR', $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $CommTrnId, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $cheque_bank_to_name, $cheque_bank_to_branch, $cheque_bank_to_ac_no, $cheque_bank_to_ifsc, $transction_bank_from_id, $transction_bank_from_ac_id, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc, $cheque_bank_from_id, $cheque_bank_ac_from_id);
                    //commission branch daybook 
                    $comDR = $memDetail->first_name . ' ' . $memDetail->last_name . '(' . $memDetail->associate_no . ') A/c Dr' . $commAmount . '/-';
                    $comCR = 'To SSB(' . $ssbAccountDetail->account_no . ') A/c Cr ' . $commAmount . '/-';
                    $daybookComm = CommanController::NewFieldBranchDaybookCreate($refId, $branch_id, $type, $sub_type, $type_id, $associate_id, $member_id, $branch_id_to = NULL, $branch_id_from = NULL, $opening_balance = NULL, $commAmount, $closing_balance = NULL, $des, $comDR, $comCR, $payment_type, $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $is_contra = NULL, $contra_id = NULL, $created_at, $updated_at, $CommTrnId, $ssb_account_id_to, $cheque_bank_from_id, $cheque_bank_ac_from_id, $cheque_bank_to_name, $cheque_bank_to_branch, $cheque_bank_to_ac_no, $cheque_bank_to_ifsc, $transction_bank_from_id, $transction_bank_from_ac_id, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc, 0, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $jv_unique_id, $cheque_type, $cheque_id);
                    //commission member transaction
                    $memComDes = 'Commission transfer for ' . date("F Y", strtotime($request->start_date_time));
                    $memberTranComm = CommanController::NewFieldAddMemberTransactionCreate($refId, $type, $sub_type, $type_id, $associate_id, $member_id, $branch_id, $bank_id = NULL, $account_id = NULL, $commAmount, $memComDes, $payment_type, $payment_mode, $currency_code, $amount_to_id = NULL, $amount_to_name = NULL, $amount_from_id = NULL, $amount_from_name = NULL, $v_no, $v_date, $ssb_account_id_from = NULL, $cheque_no = NULL, $cheque_date = NULL, $cheque_bank_from = NULL, $cheque_bank_ac_from = NULL, $cheque_bank_ifsc_from = NULL, $cheque_bank_branch_from = NULL, $cheque_bank_to = NULL, $cheque_bank_ac_to = NULL, $transction_no = NULL, $transction_bank_from = NULL, $transction_bank_ac_from = NULL, $transction_bank_ifsc_from = NULL, $transction_bank_branch_from = NULL, $transction_bank_to = NULL, $transction_bank_ac_to = NULL, $transction_date = NULL, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $CommTrnId, $ssb_account_id_to, $cheque_bank_from_id, $cheque_bank_ac_from_id, $cheque_bank_to_name, $cheque_bank_to_branch, $cheque_bank_to_ac_no, $cheque_bank_to_ifsc, $transction_bank_from_id, $transction_bank_from_ac_id, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $jv_unique_id, $cheque_type, $cheque_id);
                    //fule entry--------------------------------
                    if ($fuleAmount > 0) {
                        $ssb_account_id_to = $type_id;
                        $ssb_account_tran_id_to = $fuleTranId;
                        $desFule = $memDetail->first_name . ' ' . $memDetail->last_name . '(' . $memDetail->associate_no . ')-Fuel charge paid for ' . date("F Y", strtotime($request->start_date_time));
                        $type1 = 4;
                        $sub_type1 = 46;
                        //fule ssb head 
                        $head1FuleSsb = 1;
                        $head2FuleSsb = 8;
                        $head3FuleSsb = 20;
                        $head4FuleSsb = 56;
                        $head5FuleSsb = NULL;
                        $allTranFuleSSb = CommanController::headTransactionCreate($refId, $branch_id, $bank_id, $bank_ac_id, $head4FuleSsb, $type1, $sub_type1, $type_id, $associate_id, $member_id, $branch_id_to = NULL, $branch_id_from = NULL, $opening_balance = NULL, $fuleAmount, $closing_balance = NULL, $desFule, 'CR', $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $fuleTranId, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $cheque_bank_to_name, $cheque_bank_to_branch, $cheque_bank_to_ac_no, $cheque_bank_to_ifsc, $transction_bank_from_id, $transction_bank_from_ac_id, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc, $cheque_bank_from_id, $cheque_bank_ac_from_id);
                        //fule head
                        $head1Fule = 4;
                        $head2Fule = 86;
                        $head3Fule = 88;
                        $head4Fule = NULL;
                        $head5Fule = NULL;
                        $allTranFule = CommanController::headTransactionCreate($refId, $branch_id, $bank_id, $bank_ac_id, $head3Fule, $type1, $sub_type1, $type_id, $associate_id, $member_id, $branch_id_to = NULL, $branch_id_from = NULL, $opening_balance = NULL, $fuleAmount, $closing_balance = NULL, $desFule, 'DR', $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $fuleTranId, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $cheque_bank_to_name, $cheque_bank_to_branch, $cheque_bank_to_ac_no, $cheque_bank_to_ifsc, $transction_bank_from_id, $transction_bank_from_ac_id, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc, $cheque_bank_from_id, $cheque_bank_ac_from_id);
                        //fule branch daybook  
                        $fuleDR = $memDetail->first_name . ' ' . $memDetail->last_name . '(' . $memDetail->associate_no . ') A/c Dr' . $fuleAmount . '/-';
                        $fuleCR = 'To SSB(' . $ssbAccountDetail->account_no . ') A/c Cr ' . $fuleAmount . '/-';
                        $daybookFule = CommanController::NewFieldBranchDaybookCreate($refId, $branch_id, $type1, $sub_type1, $type_id, $associate_id, $member_id, $branch_id_to = NULL, $branch_id_from = NULL, $opening_balance = NULL, $fuleAmount, $closing_balance = NULL, $desFule, $fuleDR, $fuleCR, $payment_type, $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $is_contra = NULL, $contra_id = NULL, $created_at, $updated_at, $fuleTranId, $ssb_account_id_to, $cheque_bank_from_id, $cheque_bank_ac_from_id, $cheque_bank_to_name, $cheque_bank_to_branch, $cheque_bank_to_ac_no, $cheque_bank_to_ifsc, $transction_bank_from_id, $transction_bank_from_ac_id, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc, 0, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $jv_unique_id, $cheque_type, $cheque_id);
                        //fule member transaction
                        $memFuleDes = 'Fule transfer for ' . date("F Y", strtotime($request->start_date_time));
                        $memberTranFule = CommanController::NewFieldAddMemberTransactionCreate($refId, $type1, $sub_type1, $type_id, $associate_id, $member_id, $branch_id, $bank_id = NULL, $account_id = NULL, $fuleAmount, $memFuleDes, $payment_type, $payment_mode, $currency_code, $amount_to_id = NULL, $amount_to_name = NULL, $amount_from_id = NULL, $amount_from_name = NULL, $v_no, $v_date, $ssb_account_id_from = NULL, $cheque_no = NULL, $cheque_date = NULL, $cheque_bank_from = NULL, $cheque_bank_ac_from = NULL, $cheque_bank_ifsc_from = NULL, $cheque_bank_branch_from = NULL, $cheque_bank_to = NULL, $cheque_bank_ac_to = NULL, $transction_no = NULL, $transction_bank_from = NULL, $transction_bank_ac_from = NULL, $transction_bank_ifsc_from = NULL, $transction_bank_branch_from = NULL, $transction_bank_to = NULL, $transction_bank_ac_to = NULL, $transction_date = NULL, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $fuleTranId, $ssb_account_id_to, $cheque_bank_from_id, $cheque_bank_ac_from_id, $cheque_bank_to_name, $cheque_bank_to_branch, $cheque_bank_to_ac_no, $cheque_bank_to_ifsc, $transction_bank_from_id, $transction_bank_from_ac_id, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $jv_unique_id, $cheque_type, $cheque_id);
                    }
                    //tds entry  --------------------------------------------
                    if ($tdsAmount > 0) {
                        $ssb_account_id_to = NULL;
                        $ssb_account_tran_id_to = NULL;
                        $destds = $memDetail->first_name . ' ' . $memDetail->last_name . '(' . $memDetail->associate_no . ')-TDS deduction for ' . date("F Y", strtotime($request->start_date_time));
                        $type2 = 9;
                        $sub_type2 = 90;
                        //tds head 
                        $head1Tds = 1;
                        $head2Tds = 8;
                        $head3Tds = 22;
                        $head4Tds = 63;
                        $head5Tds = NULL;
                        $allTranTds = CommanController::headTransactionCreate($refId, $branch_id, $bank_id, $bank_ac_id, $head4Tds, $type2, $sub_type2, $type_id, $associate_id, $member_id, $branch_id_to = NULL, $branch_id_from = NULL, $opening_balance = NULL, $tdsAmount, $closing_balance = NULL, $destds, 'CR', $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $type_transaction_id = NULL, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $cheque_bank_to_name, $cheque_bank_to_branch, $cheque_bank_to_ac_no, $cheque_bank_to_ifsc, $transction_bank_from_id, $transction_bank_from_ac_id, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc, $cheque_bank_from_id, $cheque_bank_ac_from_id);
                        //tds member transaction
                        $memTdsDes = 'TDS deduction for ' . date("F Y", strtotime($request->start_date_time));
                        $memberTranComm = CommanController::NewFieldAddMemberTransactionCreate($refId, $type2, $sub_type2, $type_id, $associate_id, $member_id, $branch_id, $bank_id = NULL, $account_id = NULL, $tdsAmount, $memTdsDes, $payment_type, $payment_mode, $currency_code, $amount_to_id = NULL, $amount_to_name = NULL, $amount_from_id = NULL, $amount_from_name = NULL, $v_no, $v_date, $ssb_account_id_from = NULL, $cheque_no = NULL, $cheque_date = NULL, $cheque_bank_from = NULL, $cheque_bank_ac_from = NULL, $cheque_bank_ifsc_from = NULL, $cheque_bank_branch_from = NULL, $cheque_bank_to = NULL, $cheque_bank_ac_to = NULL, $transction_no = NULL, $transction_bank_from = NULL, $transction_bank_ac_from = NULL, $transction_bank_ifsc_from = NULL, $transction_bank_branch_from = NULL, $transction_bank_to = NULL, $transction_bank_ac_to = NULL, $transction_date = NULL, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $type_transaction_id = NULL, $ssb_account_id_to, $cheque_bank_from_id, $cheque_bank_ac_from_id, $cheque_bank_to_name, $cheque_bank_to_branch, $cheque_bank_to_ac_no, $cheque_bank_to_ifsc, $transction_bank_from_id, $transction_bank_from_ac_id, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $jv_unique_id, $cheque_type, $cheque_id);
                    }
                    $branchClosing = CommanController::checkCreateBranchClosing($branch_id, $created_at, $ssbAmountComm, 0);
                    /*****************************Head impliment End ******************************/
                }
            }
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('alert', $ex->getMessage());
        }
        return back()->with('success', 'Commission Transfer to SSb Acount Successfully');
    }
    public function commissionTransferList(Request $request)
    {
        if (check_my_permission(Auth::user()->id, "15") != "1") {
            return redirect()->route('admin.dashboard');
        }
        $data['title'] = 'Commission Transfer | Ledger List';
        return view('templates.admin.associate.commission_transfer_list', $data);
    }
    public function leaserList(Request $request)
    {
        if ($request->ajax()) {
            $data = \App\Models\CommissionLeaser::orderby('id', 'DESC')->get();
            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('start', function ($row) {
                    $start = date("d/m/Y H:i:s a", strtotime($row->start_date));
                    return $start;
                })
                ->rawColumns(['start'])
                ->addColumn('end', function ($row) {
                    $end = date("d/m/Y H:i:s a", strtotime($row->end_date));
                    return $end;
                })
                ->rawColumns(['end'])
                ->addColumn('total', function ($row) {
                    $total = $row->total_amount;
                    return number_format((float) $total, 2, '.', '');
                    ;
                    ;
                })
                ->rawColumns(['total'])
                ->addColumn('credit', function ($row) {
                    $credit = $row->credit_amount;
                    return number_format((float) $credit, 2, '.', '');
                    ;
                    ;
                })
                ->rawColumns(['total'])
                ->addColumn('status', function ($row) {
                    if ($row->status == 1) {
                        $status = 'Transferred';
                    } else if ($row->status == 2) {
                        $status = 'Partial Transfer';
                    } else if ($row->status == 3) {
                        $status = 'Pending';
                    } else {
                        $status = 'Deleted';
                    }
                    return $status;
                })
                ->rawColumns(['status'])
                ->addColumn('created', function ($row) {
                    $created = date("d/m/Y H:i:s a", strtotime($row->created_at));
                    return $created;
                })
                ->rawColumns(['created'])
                ->addColumn('ledgerAmount', function ($row) {
                    $ledgerAmount = $row->ledger_amount;
                    return number_format((float) $ledgerAmount, 2, '.', '');
                    ;
                    ;
                })
                ->rawColumns(['ledgerAmount'])
                ->addColumn('total_fuel', function ($row) {
                    $total_fuel = $row->total_fuel;
                    return number_format((float) $total_fuel, 2, '.', '');
                    ;
                    ;
                })
                ->rawColumns(['total_fuel'])
                ->addColumn('credit_fuel', function ($row) {
                    $credit_fuel = $row->credit_fuel;
                    return number_format((float) $credit_fuel, 2, '.', '');
                    ;
                    ;
                })
                ->rawColumns(['credit_fuel'])
                ->addColumn('action', function ($row) {
                    $btn = '<div class="list-icons"><div class="dropdown"><a href="#" class="list-icons-item" data-toggle="dropdown"><i class="icon-menu9"></i></a><div class="dropdown-menu dropdown-menu-right">';
                    $url = URL::to("admin/associate-commission-transfer-detail/" . $row->id . "");
                    $url1 = URL::to("admin/ledger-delete/" . $row->id . "");
                    $btn .= '<a  class="dropdown-item" href="' . $url . '" title="Ledger  Detail"><i class="icon-eye-blocked2  mr-2"></i>Ledger  Detail</a>  ';
                    if ($row->status == 1) {
                        if (Auth::user()->id != "13") {
                            // $btn .= '<button class="dropdown-item" href="#" title="Ledger  Detail" onclick="leaserDelete('.$row->id.')"><i class="fa fa-trash  mr-2"></i>Delete</button>  ';
                        }
                    }
                    $btn .= '</div></div></div>';
                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
    }
    public function commissionTransferDetail($id)
    {
        $data['title'] = 'Commission Transfer | Ledger Detail';
        $data['detail'] = \App\Models\CommissionLeaser::where('id', $id)->first();
        return view('templates.admin.associate.transfer_list_detail', $data);
    }
    public function leaserDetailList(Request $request)
    {
        if ($request->ajax()) {
            $data = \App\Models\CommissionLeaserDetail::where('commission_leaser_id', $request->id)->orderby('id', 'DESC')->get();
            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('code', function ($row) {
                    $code = getSeniorData($row->member_id, 'associate_no');
                    return $code;
                })
                ->rawColumns(['code'])
                ->addColumn('carder', function ($row) {
                    $carder = getCarderName(getSeniorData($row->member_id, 'current_carder_id'));
                    return $carder;
                })
                ->rawColumns(['code'])
                ->addColumn('name', function ($row) {
                    $name = getSeniorData($row->member_id, 'first_name') . ' ' . getSeniorData($row->member_id, 'last_name');
                    return $name;
                })
                ->rawColumns(['name'])
                ->addColumn('total', function ($row) {
                    $total = number_format((float) $row->amount, 2, '.', '');
                    return $total;
                })
                ->rawColumns(['total'])
                ->addColumn('account', function ($row) {
                    $ssbAccountDetail = getMemberSsbAccountDetail($row->member_id);
                    $account = $ssbAccountDetail->account_no;
                    return $account;
                })
                ->rawColumns(['account'])
                ->addColumn('status', function ($row) {
                    if ($row->status == 1) {
                        $status = 'Transferred';
                    } else if ($row->status == 2) {
                        $status = 'Partial Transfer';
                    } else if ($row->status == 3) {
                        $status = 'Pending';
                    } else {
                        $status = 'Deleted';
                    }
                    return $status;
                })
                ->rawColumns(['status'])
                ->addColumn('created', function ($row) {
                    $created = date("d/m/Y H:i:s a", strtotime($row->created_at));
                    return $created;
                })
                ->rawColumns(['created'])
                ->addColumn('pan', function ($row) {
                    $pan = get_member_id_proof($row->member_id, 5);
                    return $pan;
                })
                ->rawColumns(['pan'])
                ->addColumn('tds', function ($row) {
                    $tds = number_format((float) $row->total_tds, 2, '.', '');
                    ;
                    return $tds;
                })
                ->rawColumns(['tds'])
                ->addColumn('fuel', function ($row) {
                    $fuel = number_format((float) $row->fuel, 2, '.', '');
                    return $fuel;
                })
                ->rawColumns(['fuel'])
                ->addColumn('collection', function ($row) {
                    $collection = number_format((float) $row->collection, 2, '.', '');
                    return $collection;
                })
                ->rawColumns(['collection'])
                ->addColumn('amount_tds', function ($row) {
                    $amount_tds = number_format((float) $row->amount_tds, 2, '.', '');
                    return $amount_tds;
                })
                ->rawColumns(['amount_tds'])
                ->addColumn('action', function ($row) {
                    $btn = '<div class="list-icons"><div class="dropdown"><a href="#" class="list-icons-item" data-toggle="dropdown"><i class="icon-menu9"></i></a><div class="dropdown-menu dropdown-menu-right">';
                    $url = URL::to("admin/associate-commission-detail/" . $row->member_id . "");
                    $url1 = URL::to("admin/associate/loan-commission-detail/" . $row->member_id . "");
                    $btn .= '<a class="dropdown-item" href="' . $url . '" title="Investment Commission Detail"><i class="icon-eye-blocked2  mr-2"></i>Investment Commission Detail</a>  ';
                    $btn .= '<a class="dropdown-item" href="' . $url1 . '" title="Loan Commission Detail"><i class="icon-eye-blocked2  mr-2"></i>Loan Commission Detail</a>  ';
                    $btn .= '</div></div></div>';
                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
    }
    public function laserDeleteOLd(Request $request)
    {
        $sumAmount = 0;
        $sumAmountFuel = 0;
        Session::put('created_at', $request['created_at']);
        $globaldate = $request['created_at'];
        DB::beginTransaction();
        try {
            $dataget = \App\Models\CommissionLeaser::where('id', $request->id)->first();
            $start_date = date("d/m/Y", strtotime($dataget->start_date));
            $end_date = date("d/m/Y", strtotime($dataget->end_date));
            $data = \App\Models\CommissionLeaserDetail::where('commission_leaser_id', $request->id)->get();
            foreach ($data as $val) {
                $sumAmount = $sumAmount + $val->amount;
                $leaserUpdate['status'] = 0;
                $leaserUpdateDetail = \App\Models\CommissionLeaserDetail::find($val->id);
                $leaserUpdateDetail->update($leaserUpdate);
                $a = explode(",", $val->commission_id);
                foreach ($a as $k => $b) {
                    if ($b) {
                        $commissionUpdate['is_distribute'] = 0;
                        $comDataUpdate = AssociateCommission::find($b);
                        $comDataUpdate->update($commissionUpdate);
                    }
                }
                $ssbAccountDetail = getMemberSsbAccountDetail($val->member_id);
                $detail = 'Commission Refunded';
                //$ssbTranCalculation = CommanController::ssbTransaction($ssbAccountDetail->id,$ssbAccountDetail->account_no,$ssbAccountDetail->balance,$val->amount,$detail,'INR','DR',3);
                /*********************  ssb transaction ******************************/
                $amounTra = $val->amount;
                $balanceTra = $ssbAccountDetail->balance;
                $dataSsb['withdrawal'] = $amounTra;
                $ssbBalance = $balanceTra - $amounTra;
                $dataSsb['saving_account_id'] = $ssbAccountDetail->id;
                $dataSsb['account_no'] = $ssbAccountDetail->account_no;
                $dataSsb['opening_balance'] = $ssbBalance;
                $dataSsb['amount'] = $balanceTra;
                $dataSsb['description'] = $detail;
                $dataSsb['currency_code'] = 'INR';
                $dataSsb['payment_type'] = 'DR';
                $dataSsb['payment_mode'] = 3;
                $dataSsb['created_at'] = $globaldate;
                $resSsb = \App\Models\SavingAccountTranscation::create($dataSsb);
                // $ssbBalance = $balance-$amount;
                $sResult = \App\Models\SavingAccount::find($ssbAccountDetail->id);
                $sData['balance'] = $ssbBalance;
                $sResult->update($sData);
                /*********************  ssb transaction ******************************/
                $amountArray = array('1' => $val->amount);
                $member = Member::where('id', $val->member_id)->first();
                $branch_id = $member->associate_branch_id;
                $branchCode = $member->associate_branch_code;
                $deposit_by_name = $member->first_name . ' ' . $member->last_name;
                $deposit_by_id = $val->member_id;
                $rdCreateTran = CommanController::createTransaction(NULL, 1, $ssbAccountDetail->id, $val->member_id, $branch_id, $branchCode, $amountArray, 5, $deposit_by_name, $deposit_by_id, $ssbAccountDetail->account_no, $cheque_dd_no = 0, $bank_name = null, $branch_name = null, $payment_date = null, $online_payment_id = null, $online_payment_by = null, $saving_account_id = 0, 'DR');
                if ($val->fuel > 0) {
                    $sumAmountFuel = $sumAmountFuel + $val->fuel;
                    $detail = 'Fule Refunded';
                    $ssbTranCalculation = CommanController::ssbTransaction($ssbAccountDetail->id, $ssbAccountDetail->account_no, $ssbBalance, $val->fuel, $detail, 'INR', 'DR', 3);
                    $amountArray = array('1' => $val->fuel);
                    $member = Member::where('id', $val->member_id)->first();
                    $branch_id = $member->associate_branch_id;
                    $branchCode = $member->associate_branch_code;
                    $deposit_by_name = $member->first_name . ' ' . $member->last_name;
                    $deposit_by_id = $val->member_id;
                    $rdCreateTran = CommanController::createTransaction(NULL, 1, $ssbAccountDetail->id, $val->member_id, $branch_id, $branchCode, $amountArray, 5, $deposit_by_name, $deposit_by_id, $ssbAccountDetail->account_no, $cheque_dd_no = 0, $bank_name = null, $branch_name = null, $payment_date = null, $online_payment_id = null, $online_payment_by = null, $saving_account_id = 0, 'DR');
                }
            }
            $leaser['credit_amount'] = $sumAmount;
            $leaser['credit_fuel'] = $sumAmountFuel;
            $leaser['status'] = 0;
            $leaserUpdate = \App\Models\CommissionLeaser::find($request->id);
            $leaserUpdate->update($leaser);
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            return 1;
        }
        return 0;
    }
    public function laserDelete(Request $request)
    {
        $sumAmount = 0;
        $sumAmountFuel = 0;
        Session::put('created_at', $request['created_at']);
        $globaldate = $request['created_at'];
        DB::beginTransaction();
        try {
            $dataget = \App\Models\CommissionLeaser::where('id', $request->id)->first();
            $start_date = date("d/m/Y", strtotime($dataget->start_date));
            $end_date = date("d/m/Y", strtotime($dataget->end_date));
            $data = \App\Models\CommissionLeaserDetail::where('commission_leaser_id', $request->id)->get();
            foreach ($data as $val) {
                $sumAmount = $sumAmount + $val->amount;
                $leaserUpdate['status'] = 0;
                $leaserUpdateDetail = \App\Models\CommissionLeaserDetail::find($val->id);
                $leaserUpdateDetail->update($leaserUpdate);
                $ssbAccountDetail = getMemberSsbAccountDetail($val->member_id);
                $detail = 'Commission Refunded';
                $memDetail = $member = Member::where('id', $val->member_id)->first();
                $branch_id = $ssbAccountDetail->branch_id;
                $branchCode = $ssbAccountDetail->branch_code;
                $deposit_by_name = $member->first_name . ' ' . $member->last_name;
                $deposit_by_id = $val->member_id;
                $associate_id = $ssbAccountDetail->associate_id;
                $a = explode(",", $val->commission_id);
                foreach ($a as $k => $b) {
                    if ($b) {
                        $commissionUpdate['is_distribute'] = 0;
                        $comDataUpdate = AssociateCommission::find($b);
                        $comDataUpdate->update($commissionUpdate);
                    }
                }
                /*********************  ssb transaction ******************************/
                $amounTra = $val->amount;
                $balanceTra = $ssbAccountDetail->balance;
                $dataSsb['withdrawal'] = $amounTra;
                $ssbBalance = $balanceTra - $amounTra;
                $dataSsb['saving_account_id'] = $ssbAccountDetail->id;
                $dataSsb['account_no'] = $ssbAccountDetail->account_no;
                $dataSsb['opening_balance'] = $ssbBalance;
                $dataSsb['type'] = 3;
                $dataSsb['branch_id'] = $branch_id;
                $dataSsb['associate_id'] = $associate_id;
                $dataSsb['amount'] = $balanceTra;
                $dataSsb['description'] = $detail;
                $dataSsb['currency_code'] = 'INR';
                $dataSsb['payment_type'] = 'DR';
                $dataSsb['payment_mode'] = 3;
                $dataSsb['created_at'] = $globaldate;
                $resSsb = \App\Models\SavingAccountTranscation::create($dataSsb);
                $CommTrnId = $resSsb->id;
                // $ssbBalance = $balance-$amount;
                $sResult = \App\Models\SavingAccount::find($ssbAccountDetail->id);
                $sData['balance'] = $ssbBalance;
                $sResult->update($sData);
                /*********************  ssb transaction ******************************/
                $amountArray = array('1' => $val->amount);
                $rdCreateTran = CommanController::createTransaction(NULL, 1, $ssbAccountDetail->id, $val->member_id, $branch_id, $branchCode, $amountArray, 5, $deposit_by_name, $deposit_by_id, $ssbAccountDetail->account_no, $cheque_dd_no = 0, $bank_name = null, $branch_name = null, $payment_date = null, $online_payment_id = null, $online_payment_by = null, $saving_account_id = 0, 'DR');
                if ($val->fuel > 0) {
                    $sumAmountFuel = $sumAmountFuel + $val->fuel;
                    $detail = 'Fule Refunded';
                    $ssbTranCalculation = CommanController::ssbTransactionModify($ssbAccountDetail->id, $ssbAccountDetail->account_no, $ssbBalance, $val->fuel, $detail, 'INR', 'DR', 3, $branch_id, $associate_id, 4);
                    $fuleTranId = $ssbTranCalculation;
                    $amountArray = array('1' => $val->fuel);
                    $rdCreateTran = CommanController::createTransaction(NULL, 1, $ssbAccountDetail->id, $val->member_id, $branch_id, $branchCode, $amountArray, 5, $deposit_by_name, $deposit_by_id, $ssbAccountDetail->account_no, $cheque_dd_no = 0, $bank_name = null, $branch_name = null, $payment_date = null, $online_payment_id = null, $online_payment_by = null, $saving_account_id = 0, 'DR');
                }
                /*****************************Head impliment start ******************************/
                $payment_mode = 3;
                $payment_type = 'DR';
                $currency_code = 'INR';
                $tdsAmount = $val->total_tds;
                $fuleAmount = $val->fuel;
                $commAmount = $val->amount;
                $member_id = $val->member_id;
                $amount = $commAmount + $fuleAmount + $tdsAmount;
                $ssbAmountComm = $commAmount + $fuleAmount;
                $daybookRef = CommanController::createBranchDayBookReferenceNew($amount, $globaldate);
                $refId = $daybookRef;
                $type_id = $ssbAccountDetail->id;
                $entry_date = date("Y-m-d", strtotime(convertDate($globaldate)));
                $entry_time = date("H:i:s", strtotime(convertDate($globaldate)));
                $created_by = 1;
                $created_by_id = Auth::user()->id;
                $created_at = date("Y-m-d H:i:s", strtotime(convertDate($globaldate)));
                $updated_at = date("Y-m-d H:i:s", strtotime(convertDate($globaldate)));
                $randNumber = mt_rand(0, 999999999999999);
                $v_no = $randNumber;
                $v_date = $entry_date;
                $ssb_account_id_to = $type_id;
                $ssb_account_tran_id_to = $CommTrnId;
                $jv_unique_id = $ssb_account_tran_id_from = $cheque_type = $cheque_id = $cheque_bank_to_name = $cheque_bank_to_branch = $cheque_bank_to_ac_no = $cheque_bank_to_ifsc = $transction_bank_from_id = $transction_bank_from_ac_id = $transction_bank_to_name = $transction_bank_to_ac_no = $transction_bank_to_branch = $transction_bank_to_ifsc = $cheque_bank_from_id = $cheque_bank_ac_from_id = NULL;
                $amount_to_name = NULL;
                $amount_from_id = NULL;
                $amount_from_name = NULL;
                $amount_to_id = NULL;
                $ssb_account_id_from = $cheque_no = $cheque_date = $cheque_bank_from = $cheque_bank_ac_from = $cheque_bank_ifsc_from = $cheque_bank_branch_from = $cheque_bank_to = $cheque_bank_ac_to = $transction_no = $transction_bank_from = $transction_bank_ac_from = $transction_bank_ifsc_from = $transction_bank_branch_from = $transction_bank_to = $transction_bank_ac_to = $transction_date = NULL;
                $bank_id = NULL;
                $bank_ac_id = NULL;
                // commission entry----------------------------
                $des = $memDetail->first_name . ' ' . $memDetail->last_name . '(' . $memDetail->associate_no . ')-Commission of ' . date("F Y", strtotime($request->start_date_time)) . ' was returned';
                $type = 4;
                $sub_type = 45;
                //commission ssb head
                $head1ComSsb = 1;
                $head2ComSsb = 8;
                $head3ComSsb = 20;
                $head4ComSsb = 56;
                $head5ComSsb = NULL;
                $allTranCommSSB = CommanController::headTransactionCreate($refId, $branch_id, $bank_id, $bank_ac_id, $head4ComSsb, $type, $sub_type, $type_id, $associate_id, $member_id, $branch_id_to = NULL, $branch_id_from = NULL, $opening_balance = NULL, $commAmount, $closing_balance = NULL, $des, 'CR', $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $CommTrnId, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $cheque_bank_to_name, $cheque_bank_to_branch, $cheque_bank_to_ac_no, $cheque_bank_to_ifsc, $transction_bank_from_id, $transction_bank_from_ac_id, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc, $cheque_bank_from_id, $cheque_bank_ac_from_id);
                //commission head
                $head1Com = 4;
                $head2Com = 86;
                $head3Com = 87;
                $head4Com = NULL;
                $head5Com = NULL;
                $allTranComm = CommanController::headTransactionCreate($refId, $branch_id, $bank_id, $bank_ac_id, $head3Com, $type, $sub_type, $type_id, $associate_id, $member_id, $branch_id_to = NULL, $branch_id_from = NULL, $opening_balance = NULL, $commAmount, $closing_balance = NULL, $des, 'DR', $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $CommTrnId, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $cheque_bank_to_name, $cheque_bank_to_branch, $cheque_bank_to_ac_no, $cheque_bank_to_ifsc, $transction_bank_from_id, $transction_bank_from_ac_id, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc, $cheque_bank_from_id, $cheque_bank_ac_from_id);
                //commission branch daybook 
                $comDR = 'SSB(' . $ssbAccountDetail->account_no . ') A/c Dr ' . $commAmount . '/-';
                $comCR = 'To ' . $memDetail->first_name . ' ' . $memDetail->last_name . '(' . $memDetail->associate_no . ') A/c Cr' . $commAmount . '/-';
                $daybookComm = CommanController::NewFieldBranchDaybookCreate($refId, $branch_id, $type, $sub_type, $type_id, $associate_id, $member_id, $branch_id_to = NULL, $branch_id_from = NULL, $opening_balance = NULL, $commAmount, $closing_balance = NULL, $des, $comDR, $comCR, $payment_type, $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $is_contra = NULL, $contra_id = NULL, $created_at, $updated_at, $CommTrnId, $ssb_account_id_to, $cheque_bank_from_id, $cheque_bank_ac_from_id, $cheque_bank_to_name, $cheque_bank_to_branch, $cheque_bank_to_ac_no, $cheque_bank_to_ifsc, $transction_bank_from_id, $transction_bank_from_ac_id, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc, 0, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $jv_unique_id, $cheque_type, $cheque_id);
                //commission member transaction
                $memComDes = 'Commission of ' . date("F Y", strtotime($request->start_date_time)) . ' was returned';
                $memberTranComm = CommanController::NewFieldAddMemberTransactionCreate($refId, $type, $sub_type, $type_id, $associate_id, $member_id, $branch_id, $bank_id = NULL, $account_id = NULL, $commAmount, $memComDes, $payment_type, $payment_mode, $currency_code, $amount_to_id = NULL, $amount_to_name = NULL, $amount_from_id = NULL, $amount_from_name = NULL, $v_no, $v_date, $ssb_account_id_from = NULL, $cheque_no = NULL, $cheque_date = NULL, $cheque_bank_from = NULL, $cheque_bank_ac_from = NULL, $cheque_bank_ifsc_from = NULL, $cheque_bank_branch_from = NULL, $cheque_bank_to = NULL, $cheque_bank_ac_to = NULL, $transction_no = NULL, $transction_bank_from = NULL, $transction_bank_ac_from = NULL, $transction_bank_ifsc_from = NULL, $transction_bank_branch_from = NULL, $transction_bank_to = NULL, $transction_bank_ac_to = NULL, $transction_date = NULL, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $CommTrnId, $ssb_account_id_to, $cheque_bank_from_id, $cheque_bank_ac_from_id, $cheque_bank_to_name, $cheque_bank_to_branch, $cheque_bank_to_ac_no, $cheque_bank_to_ifsc, $transction_bank_from_id, $transction_bank_from_ac_id, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $jv_unique_id, $cheque_type, $cheque_id);
                //fule entry--------------------------------
                if ($fuleAmount > 0) {
                    $desFule = $memDetail->first_name . ' ' . $memDetail->last_name . '(' . $memDetail->associate_no . ')-Fuel charge of ' . date("F Y", strtotime($request->start_date_time)) . ' was returned';
                    $type1 = 4;
                    $sub_type1 = 46;
                    //fule ssb head 
                    $head1FuleSsb = 1;
                    $head2FuleSsb = 8;
                    $head3FuleSsb = 20;
                    $head4FuleSsb = 56;
                    $head5FuleSsb = NULL;
                    $allTranFuleSSb = CommanController::headTransactionCreate($refId, $branch_id, $bank_id, $bank_ac_id, $head4FuleSsb, $type1, $sub_type1, $type_id, $associate_id, $member_id, $branch_id_to = NULL, $branch_id_from = NULL, $opening_balance = NULL, $fuleAmount, $closing_balance = NULL, $desFule, 'CR', $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $fuleTranId, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $cheque_bank_to_name, $cheque_bank_to_branch, $cheque_bank_to_ac_no, $cheque_bank_to_ifsc, $transction_bank_from_id, $transction_bank_from_ac_id, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc, $cheque_bank_from_id, $cheque_bank_ac_from_id);
                    //fule head
                    $head1Fule = 4;
                    $head2Fule = 86;
                    $head3Fule = 88;
                    $head4Fule = NULL;
                    $head5Fule = NULL;
                    $allTranFule = CommanController::headTransactionCreate($refId, $branch_id, $bank_id, $bank_ac_id, $head3Fule, $type1, $sub_type1, $type_id, $associate_id, $member_id, $branch_id_to = NULL, $branch_id_from = NULL, $opening_balance = NULL, $fuleAmount, $closing_balance = NULL, $desFule, 'DR', $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $fuleTranId, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $cheque_bank_to_name, $cheque_bank_to_branch, $cheque_bank_to_ac_no, $cheque_bank_to_ifsc, $transction_bank_from_id, $transction_bank_from_ac_id, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc, $cheque_bank_from_id, $cheque_bank_ac_from_id);
                    //fule branch daybook  
                    $fuleDR = 'SSB(' . $ssbAccountDetail->account_no . ') A/c Dr ' . $fuleAmount . '/-';
                    $fuleCR = 'To ' . $memDetail->first_name . ' ' . $memDetail->last_name . '(' . $memDetail->associate_no . ') A/c Cr' . $fuleAmount . '/-';
                    $daybookFule = CommanController::NewFieldBranchDaybookCreate($refId, $branch_id, $type1, $sub_type1, $type_id, $associate_id, $member_id, $branch_id_to = NULL, $branch_id_from = NULL, $opening_balance = NULL, $fuleAmount, $closing_balance = NULL, $desFule, $fuleDR, $fuleCR, $payment_type, $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $is_contra = NULL, $contra_id = NULL, $created_at, $updated_at, $fuleTranId, $ssb_account_id_to, $cheque_bank_from_id, $cheque_bank_ac_from_id, $cheque_bank_to_name, $cheque_bank_to_branch, $cheque_bank_to_ac_no, $cheque_bank_to_ifsc, $transction_bank_from_id, $transction_bank_from_ac_id, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc, 0, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $jv_unique_id, $cheque_type, $cheque_id);
                    //fule member transaction
                    $memFuleDes = 'Fule of ' . date("F Y", strtotime($request->start_date_time)) . ' was returned';
                    $memberTranFule = CommanController::NewFieldAddMemberTransactionCreate($refId, $type1, $sub_type1, $type_id, $associate_id, $member_id, $branch_id, $bank_id = NULL, $account_id = NULL, $fuleAmount, $memFuleDes, $payment_type, $payment_mode, $currency_code, $amount_to_id = NULL, $amount_to_name = NULL, $amount_from_id = NULL, $amount_from_name = NULL, $v_no, $v_date, $ssb_account_id_from = NULL, $cheque_no = NULL, $cheque_date = NULL, $cheque_bank_from = NULL, $cheque_bank_ac_from = NULL, $cheque_bank_ifsc_from = NULL, $cheque_bank_branch_from = NULL, $cheque_bank_to = NULL, $cheque_bank_ac_to = NULL, $transction_no = NULL, $transction_bank_from = NULL, $transction_bank_ac_from = NULL, $transction_bank_ifsc_from = NULL, $transction_bank_branch_from = NULL, $transction_bank_to = NULL, $transction_bank_ac_to = NULL, $transction_date = NULL, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $fuleTranId, $ssb_account_id_to, $cheque_bank_from_id, $cheque_bank_ac_from_id, $cheque_bank_to_name, $cheque_bank_to_branch, $cheque_bank_to_ac_no, $cheque_bank_to_ifsc, $transction_bank_from_id, $transction_bank_from_ac_id, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $jv_unique_id, $cheque_type, $cheque_id);
                }
                //tds entry  --------------------------------------------
                if ($tdsAmount > 0) {
                    $ssb_account_id_to = NULL;
                    $ssb_account_tran_id_to = NULL;
                    $destds = $memDetail->first_name . ' ' . $memDetail->last_name . '(' . $memDetail->associate_no . ')-TDS of ' . date("F Y", strtotime($request->start_date_time)) . ' was returned';
                    $type2 = 9;
                    $sub_type2 = 90;
                    //tds head 
                    $head1Tds = 1;
                    $head2Tds = 8;
                    $head3Tds = 22;
                    $head4Tds = 63;
                    $head5Tds = NULL;
                    $allTranTds = CommanController::headTransactionCreate($refId, $branch_id, $bank_id, $bank_ac_id, $head4Tds, $type2, $sub_type2, $type_id, $associate_id, $member_id, $branch_id_to = NULL, $branch_id_from = NULL, $opening_balance = NULL, $tdsAmount, $closing_balance = NULL, $destds, 'CR', $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $type_transaction_id = NULL, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $cheque_bank_to_name, $cheque_bank_to_branch, $cheque_bank_to_ac_no, $cheque_bank_to_ifsc, $transction_bank_from_id, $transction_bank_from_ac_id, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc, $cheque_bank_from_id, $cheque_bank_ac_from_id);
                    //tds member transaction
                    $memTdsDes = 'TDS of ' . date("F Y", strtotime($request->start_date_time)) . ' was returned';
                    $memberTranComm = CommanController::NewFieldAddMemberTransactionCreate($refId, $type2, $sub_type2, $type_id, $associate_id, $member_id, $branch_id, $bank_id = NULL, $account_id = NULL, $tdsAmount, $memTdsDes, $payment_type, $payment_mode, $currency_code, $amount_to_id = NULL, $amount_to_name = NULL, $amount_from_id = NULL, $amount_from_name = NULL, $v_no, $v_date, $ssb_account_id_from = NULL, $cheque_no = NULL, $cheque_date = NULL, $cheque_bank_from = NULL, $cheque_bank_ac_from = NULL, $cheque_bank_ifsc_from = NULL, $cheque_bank_branch_from = NULL, $cheque_bank_to = NULL, $cheque_bank_ac_to = NULL, $transction_no = NULL, $transction_bank_from = NULL, $transction_bank_ac_from = NULL, $transction_bank_ifsc_from = NULL, $transction_bank_branch_from = NULL, $transction_bank_to = NULL, $transction_bank_ac_to = NULL, $transction_date = NULL, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $type_transaction_id = NULL, $ssb_account_id_to, $cheque_bank_from_id, $cheque_bank_ac_from_id, $cheque_bank_to_name, $cheque_bank_to_branch, $cheque_bank_to_ac_no, $cheque_bank_to_ifsc, $transction_bank_from_id, $transction_bank_from_ac_id, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $jv_unique_id, $cheque_type, $cheque_id);
                }
                $branchClosing = CommanController::checkCreateBranchClosingDr($branch_id, $created_at, $ssbAmountComm, 0);
                /*****************************Head impliment End ******************************/
            }
            $leaser['credit_amount'] = $sumAmount;
            $leaser['credit_fuel'] = $sumAmountFuel;
            $leaser['status'] = 0;
            $leaserUpdate = \App\Models\CommissionLeaser::find($request->id);
            $leaserUpdate->update($leaser);
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            return 1;
        }
        return 0;
    }
    public function associateCommissionCollection()
    {
        $data['title'] = 'Associate Collection | Listing';
        $data['branch'] = Branch::where('status', 1)->get();
        return view('templates.admin.associate.collection', $data);
    }
    public function associateCollectionList(Request $request)
    {
        if ($request->ajax()) {
            $arrFormData = array();
            if (!empty($_POST['searchform'])) {
                foreach ($_POST['searchform'] as $frm_data) {
                    $arrFormData[$frm_data['name']] = $frm_data['value'];
                }
            }
            $startDate = '';
            $endDate = '';
            // $data = AssociateCommission::with('member','investment')->where('type','3');
            $data = Member::with('associate_branch')->where('member_id', '!=', '9999999')->where('is_associate', 1);
            if (isset($arrFormData['is_search']) && $arrFormData['is_search'] == 'yes') {
                if ($arrFormData['associate_code'] != '') {
                    $associate_code = $arrFormData['associate_code'];
                    $data = $data->where('associate_no', 'LIKE', '%' . $associate_code . '%');
                }
                if ($arrFormData['branch_id'] != '') {
                    $id = $arrFormData['branch_id'];
                    $data = $data->where('associate_branch_id', '=', $id);
                }
                if ($arrFormData['associate_name'] != '') {
                    $name = $arrFormData['associate_name'];
                    $data = $data->where(function ($query) use ($name) {
                        $query->where('first_name', 'LIKE', '%' . $name . '%')->orWhere('last_name', 'LIKE', '%' . $name . '%')->orWhere(DB::raw('concat(first_name," ",last_name)'), 'LIKE', "%$name%");
                    });
                }
                if ($arrFormData['start_date'] != '') {
                    $startDate = date("Y-m-d", strtotime(convertDate($arrFormData['start_date'])));
                    if ($arrFormData['end_date'] != '') {
                        $endDate = date("Y-m-d", strtotime(convertDate($arrFormData['end_date'])));
                    } else {
                        $endDate = '';
                    }
                }
            }
            $data = $data->orderby('id', 'DESC')->get();
            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('branch_name', function ($row) {
                    $branch_name = $row['associate_branch']->name;
                    return $branch_name;
                })
                ->rawColumns(['branch_name'])
                ->addColumn('associate_name', function ($row) {
                    $name = $row->first_name . ' ' . $row->last_name;
                    return $name;
                })
                ->rawColumns(['associate_name'])
                ->addColumn('associate_code', function ($row) use ($startDate) {
                    $associate_code = $row->associate_no;
                    return $associate_code;
                })
                ->rawColumns(['associate_code'])
                ->addColumn('associate_carder', function ($row) {
                    $associate_carder = getCarderName($row->current_carder_id);
                    return $associate_carder;
                })
                ->rawColumns(['associate_carder'])
                ->addColumn('senior_code', function ($row) {
                    $name = $row->associate_senior_code;
                    return $name;
                })
                ->rawColumns(['senior_code'])
                ->addColumn('senior_name', function ($row) {
                    $senior_name = getSeniorData($row->associate_senior_id, 'first_name') . ' ' . getSeniorData($row->associate_senior_id, 'last_name');
                    return $senior_name;
                })
                ->rawColumns(['senior_name'])
                ->addColumn('senior_carder', function ($row) {
                    $senior_carder = getCarderName(getSeniorData($row->associate_senior_id, 'current_carder_id'));
                    return $senior_carder;
                })
                ->rawColumns(['investment_account'])
                ->addColumn('total_amount', function ($row) use ($startDate, $endDate) {
                    $total_amount = getAssociateTotalCommissionCollection($row->id, $startDate, $endDate, 'total_amount');
                    return $total_amount;
                })
                ->rawColumns(['total_amount'])
                ->addColumn('commission_amount', function ($row) use ($startDate, $endDate) {
                    $commission_amount = getAssociateTotalCommissionCollection($row->id, $startDate, $endDate, 'commission_amount');
                    return $commission_amount;
                })
                ->rawColumns(['commission_amount'])
                ->make(true);
        }
    }
    public function senior()
    {
        if (check_my_permission(Auth::user()->id, "8") != "1") {
            return redirect()->route('admin.dashboard');
        }
        $data['title'] = 'Associate | Senior Change';
        return view('templates.admin.associate.senior', $data);
    }
    public function associterSeniorDataGet(Request $request)
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
                    if ($carder > $request->carder) {
                        return \Response::json(['view' => view('templates.admin.associate.partials.associate_detail_senior', ['memberData' => $data])->render(), 'msg_type' => 'success', 'id' => $id, 'carder' => $carder]);
                    } else {
                        return \Response::json(['view' => $request->carder . '==' . $carder, 'msg_type' => 'error3']);
                    }
                } else {
                    return \Response::json(['view' => 'No data found', 'msg_type' => 'error1']);
                }
            }
        } else {
            return \Response::json(['view' => 'No data found', 'msg_type' => 'error']);
        }
    }
    public function senior_save(Request $request)
    {
        // print_r($_POST);die;
        $rules = [
            'associate_code' => ['required', 'numeric'],
            'new_associate_senior' => ['required', 'numeric'],
            'old_senior_code' => ['required', 'numeric'],
        ];
        $customMessages = [
            'associate_code' => 'Please enter associate code',
            'numeric' => ':Attribute - Please enter valid.',
            'unique' => ' :Attribute already exists.'
        ];
        $this->validate($request, $rules, $customMessages);
        DB::beginTransaction();
        try {
            $associate_id = $request['member_id'];
            $associate_carder = $request['associate_carder'];
            $new_associate_senior_id = $request['new_associate_senior_id'];
            $new_associate_senior_code = $request['new_associate_senior'];
            $new_associate_senior_carder = $request['new_associate_senior_carder'];
            $old_senior_id = $request['old_senior_id'];
            if ($associate_carder >= $new_associate_senior_carder) {
                return back()->with('alert', "Senior associate's carder must be greater than associate's carder");
            }
            $member['associate_senior_id'] = $new_associate_senior_id;
            $member['associate_senior_code'] = $new_associate_senior_code;
            $memberDataUpdate = Member::find($associate_id);
            $memberDataUpdate->update($member);
            $getMemberId = \App\Models\AssociateTree::Where('member_id', $associate_id)->first();
            $getParentID = \App\Models\AssociateTree::Where('member_id', $new_associate_senior_id)->first();
            $associateTree['parent_id'] = $getParentID->id;
            $associateTree['senior_id'] = $new_associate_senior_id;
            $memberTreeUpdate = \App\Models\AssociateTree::find($getMemberId->id);
            $memberTreeUpdate->update($associateTree);
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('alert', $ex->getMessage());
        }
        return back()->with('success', 'Associate Senior Code Updated Successfully!');
    }
    public function inactiveAssociateListing(Request $request)
    {
        if ($request->ajax() && check_my_permission(Auth::user()->id, "11") == "1") {
            $data = Member::select('id', 'associate_join_date', 'associate_no', 'first_name', 'last_name', 'email', 'mobile_no', 'associate_senior_code', 'is_block', 'associate_status', 'associate_branch_id', 'associate_senior_id', 'member_id')->with([
                'seniorData' => function ($q) {
                    $q->select('id', 'first_name', 'last_name');
                }
            ])->with([
                        'associate_branch' => function ($q) {
                            $q->select('id', 'name', 'branch_code', 'sector', 'regan', 'zone');
                        }
                    ])->where('member_id', '!=', '9999999')->where('is_associate', 1)->where('associate_status', 0);
            if (Auth::user()->branch_id > 0) {
                $id = Auth::user()->branch_id;
                $data = $data->where('associate_branch_id', '=', $id);
            }
            $data1 = $data->orderby('associate_join_date', 'DESC')->count('id');
            $count = $data1; //count($data1);
            $data = $data->orderby('associate_join_date', 'DESC')->offset($_POST['start'])->limit($_POST['length'])->get();
            $dataCount = Member::where('id', '!=', 1)->where('is_associate', 1)->where('associate_status', 0);
            if (Auth::user()->branch_id > 0) {
                $dataCount = $dataCount->where('associate_branch_id', '=', Auth::user()->branch_id);
            }
            $totalCount = $dataCount->count('id');
            $sno = $_POST['start'];
            $rowReturn = array();
            foreach ($data as $row) {
                $sno++;
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
                $val['email'] = $row->email;
                $val['mobile_no'] = $row->mobile_no;
                $val['associate_code'] = $row->associate_senior_code;
                $val['associate_name'] = $row['seniorData']->first_name . ' ' . $row['seniorData']->last_name; //getSeniorData($row->associate_senior_id,'first_name').' '.getSeniorData($row->associate_senior_id,'last_name');
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
                $rowReturn[] = $val;
            }
            $output = array("draw" => $_POST['draw'], "recordsTotal" => $totalCount, "recordsFiltered" => $count, "data" => $rowReturn, );
            return json_encode($output);
        }
    }
    public function associateCommissionDetailLoan($id)
    {
        $data['title'] = 'Associate Loan Commission Detail | Listing';
        $data['plans'] = Loans::get();
        $data['member'] = Member::select('id', 'associate_no', 'first_name', 'last_name', 'current_carder_id')->where('id', $id)->first();
        $data['years'] = CommissionLeaserMonthly::where('is_deleted', 0)
            ->where('year', '>', 2022)
            ->distinct()
            ->get('year');
        $data['months'] = CommissionLeaserMonthly::where('is_deleted', 0)
            ->where('year', '>', 2022)
            ->distinct()
            ->get(['month', 'year']);
        return view('templates.admin.associate.commissionDetailLoan', $data);
    }
    public function associateCommissionDetailListLoan(Request $request)
    {
        if ($request->ajax()) {
            $arrFormData['year'] = $request->year;
            $arrFormData['month'] = $request->month;
            $arrFormData['plan_id'] = $request->plan_id;
            $arrFormData['is_search'] = $request->is_search;
            $arrFormData['commission_export'] = $request->commission_export;
            $arrFormData['id'] = $request->id;
            $arrFormData = array();
            if (!empty($_POST['searchform'])) {
                foreach ($_POST['searchform'] as $frm_data) {
                    $arrFormData[$frm_data['name']] = $frm_data['value'];
                }
            }
            // if ($arrFormData['year'] <= 2022 && $arrFormData['month'] < 12) {
            //     $data = AssociateCommission::select(['id', 'type_id', 'total_amount', 'commission_amount', 'percentage', 'type', 'carder_id', 'month', 'commission_type', 'associate_exist', 'pay_type', 'is_distribute', 'created_at'])->where('member_id', $arrFormData['id'])->whereIn('type', array(4, 6, 7, 8))->where('status', 1);
            //     if (isset($arrFormData['is_search']) && $arrFormData['is_search'] == 'yes') {
            //         if ($arrFormData['year'] != '') {
            //             $year = $arrFormData['year'];
            //             $data = $data->where(\DB::raw('YEAR(created_at)'), $year);
            //         }
            //         if ($arrFormData['month'] != '') {
            //             $month = $arrFormData['month'];
            //             $data = $data->where(\DB::raw('MONTH(created_at)'), $month);
            //         }
            //     }
            //     $count = $data->orderby('id', 'DESC')->count('id');
            //     $data = $data->orderby('id', 'DESC')->offset($_POST['start'])->limit($_POST['length'])->get();
            //     $totalCount = AssociateCommission::where('member_id', $arrFormData['id'])->whereIn('type', array(4, 6, 7, 8))->where('status', 1)->count('id');
            // } else {
            $data = \App\Models\AssociateMonthlyCommission::
                with('loan.loan:id,name')
                ->with('loan:id,account_number,loan_type')
                ->with('group_loan.loan:id,name')
                ->with('group_loan:id,account_number,loan_type')
                ->where('assocaite_id', $arrFormData['id'])->whereIn('type', array(2, 3))->where('is_deleted', '0');
            if (isset($arrFormData['is_search']) && $arrFormData['is_search'] == 'yes') {
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
            }
            $count = $data->orderby('id', 'DESC')->count('id');
            // $count=count($data1);
            $data = $data->orderby('id', 'DESC')->offset($_POST['start'])->limit($_POST['length'])->get(['id', 'type_id', 'total_amount', 'commission_amount', 'percentage', 'type', 'cadre_from', 'cadre_to', 'month', 'is_distribute', 'created_at', 'assocaite_id', 'commission_for_year', 'qualifying_amount', 'commission_for_month']);
            $totalCount = \App\Models\AssociateMonthlyCommission::where('assocaite_id', $arrFormData['id'])->where('type', 2)->where('is_deleted', '0') /*->where('is_distribute',0)*/ ->count('id');
            // }
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
                if ($row->type == 2) {
                    $val['account_number'] = $row['loan']->account_number;
                    $val['plan_name'] = $row['loan']['loan']->name;
                } else {
                    $val['account_number'] = $row['group_loan']->account_number;
                    $val['plan_name'] = $row['group_loan']['loan']->name;
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
            $output = array("draw" => $_POST['draw'], "recordsTotal" => $totalCount, "recordsFiltered" => $count, "data" => $rowReturn, );
            return json_encode($output);
        }
    }
    /* Associate Collection Report */
    // public function AssociateCollectionReport(Request $request)
    // {
    //     if (check_my_permission(Auth::user()->id, "279") != "1") {
    //         return redirect()->route('admin.dashboard');
    //     }
    //     $data['title'] = 'Associate Collection Report';
    //     $data['branch'] = Branch::select('id', 'name')->where('status', 1)->get();
    //     return view('templates.admin.associate.associate_collection_report', $data);
    // }
    // public function AssociateCollectionReportList(Request $request)
    // {
    //     $fillter = 1;
    //     if ($request->ajax()) {
    //         $arrFormData = array();
    //         if (!empty($_POST['searchform'])) {
    //             foreach ($_POST['searchform'] as $frm_data) {
    //                 $arrFormData[$frm_data['name']] = $frm_data['value'];
    //             }
    //         }
    //         if (isset($arrFormData['is_search']) && $arrFormData['is_search'] == 'yes') {
    //             $fillter = 0;
    //             $startDate = date('Y-m-d', strtotime($request->adm_report_currentdate));
    //             $endDate = date('Y-m-d', strtotime($request->adm_report_currentdate));
    //             if ($arrFormData['start_date'] != '') {
    //                 $startDate = date("Y-m-d", strtotime(convertDate($arrFormData['start_date'])));
    //                 if ($arrFormData['end_date'] != '') {
    //                     $endDate = date("Y-m-d ", strtotime(convertDate($arrFormData['end_date'])));
    //                 } else {
    //                     $endDate = date('Y-m-d', strtotime($request->adm_report_currentdate));
    //                 }
    //             }
    //             $branch_id = 0;
    //             if (isset($arrFormData['branch_id']) && $arrFormData['branch_id'] != '') {
    //                 $branch_id = $arrFormData['branch_id'];
    //             }
    //             $associate_code = '';
    //             if (isset($arrFormData['associate_code']) && $arrFormData['associate_code'] != '') {
    //                 $associate_code = $arrFormData['associate_code'];
    //             }
    //             //$dataNew=$data[0]->id;
    //             $branchId = $branch_id;
    //             $associteCode = $associate_code;
    //             $pageNo = 0;
    //             $perPageRecord = '';
    //             if ($_POST['length']) {
    //                 $perPageRecord = $_POST['length'];
    //             }
    //             if ($_POST['start'] == 0) {
    //                 $pageNo = 1;
    //             } else {
    //                 $pageGet = $_POST['start'] / $_POST['length'];
    //                 $pageNo = $pageGet + 1;
    //             }
    //             $toDay = date("d", strtotime($startDate));
    //             $toMonth = date("m", strtotime($startDate));
    //             $toYear = date("Y", strtotime($startDate));
    //             $fromDay = date("d", strtotime($endDate));
    //             $fromMonth = date("m", strtotime($endDate));
    //             $fromYear = date("Y", strtotime($endDate));
    //             $dataTotalCount = DB::select('call associteCollectionList(?,?,?,?,?,?,?,?,?,?)', [0, '', $toDay, $toMonth, $toYear, $fromDay, $fromMonth, $fromYear, 0, $perPageRecord]);
    //             $totalCount = count($dataTotalCount);
    //             $data = DB::select('call associteCollectionList(?,?,?,?,?,?,?,?,?,?)', [$branchId, $associteCode, $toDay, $toMonth, $toYear, $fromDay, $fromMonth, $fromYear, $pageNo, $perPageRecord]);
    //             $count = $totalCount;
    //             if ($branchId != '' || $associteCode != '') {
    //                 $dataTotalCountF = DB::select('call associteCollectionList(?,?,?,?,?,?,?,?,?,?)', [$branchId, $associteCode, $toDay, $toMonth, $toYear, $fromDay, $fromMonth, $fromYear, 0, $perPageRecord]);
    //                 $count = count($dataTotalCountF);
    //             }
    //             $sno = $_POST['start'];
    //             $rowReturn = array();
    //             foreach ($data as $row) {
    //                 $sno++;
    //                 $val['DT_RowIndex'] = $sno;
    //                 if (isset($row->branch_code)) {
    //                     $val['branch_code'] = $row->branch_code;
    //                 } else {
    //                     $val['branch_code'] = 'N/A';
    //                 }
    //                 if (isset($row->branch_code)) {
    //                     $val['branch_name'] = $row->name;
    //                 } else {
    //                     $val['branch_name'] = 'N/A';
    //                 }
    //                 if (isset($row->associate_no)) {
    //                     $val['associate_code'] = $row->associate_no;
    //                 } else {
    //                     $val['associate_code'] = 'N/A';
    //                 }
    //                 if (isset($row->first_name)) {
    //                     $val['associate_name'] = $row->first_name . '' . $row->last_name;
    //                 } else {
    //                     $val['associate_name'] = 'N/A';
    //                 }
    //                 $val['total_collection'] = $row->totalsum;
    //                 $rowReturn[] = $val;
    //             }
    //             $output = array("draw" => $_POST['draw'], "recordsTotal" => $totalCount, "recordsFiltered" => $count, "data" => $rowReturn);
    //             return json_encode($output);
    //         } else {
    //             $output = array(
    //                 "draw" => 0,
    //                 "recordsTotal" => 0,
    //                 "recordsFiltered" => 0,
    //                 "data" => 0,
    //             );
    //             return json_encode($output);
    //         }
    //     }
    // }
    public function AssociateCollectionReport(Request $request)
    {
        if (check_my_permission(Auth::user()->id, "279") != "1") {
            return redirect()->route('admin.dashboard');
        }
        $data['title'] = 'Associate Collection Report';
        $data['branch'] = Branch::select('id', 'name')->where('status', 1)->get();
        return view('templates.admin.associate.associate_collection_report', $data);
    }
    public function AssociateCollectionReportList(Request $request)
    {
        $fillter = 1;
        if ($request->ajax()) {
            $arrFormData = array();
            if (!empty($_POST['searchform'])) {
                foreach ($_POST['searchform'] as $frm_data) {
                    $arrFormData[$frm_data['name']] = $frm_data['value'];
                }
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
                $branch_id = 0;
                if (isset($arrFormData['branch_id']) && $arrFormData['branch_id'] != '') {
                    $branch_id = $arrFormData['branch_id'];
                }
                $associate_code = '';
                if (isset($arrFormData['associate_code']) && $arrFormData['associate_code'] != '') {
                    $associate_code = $arrFormData['associate_code'];
                }
                $companyId = 0;
                if (isset($arrFormData['company_id']) && $arrFormData['company_id'] != '') {
                    $companyId = $arrFormData['company_id'];
                }
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
                $dataTotalCount = DB::select('call associteCollectionList(?,?,?,?,?,?,?,?,?,?,?)', [0, '', $toDay, $toMonth, $toYear, $fromDay, $fromMonth, $fromYear, 0, $perPageRecord, 0]);
                $totalCount = count($dataTotalCount);
                $data = DB::select('call associteCollectionList(?,?,?,?,?,?,?,?,?,?,?)', [$branchId, $associteCode, $toDay, $toMonth, $toYear, $fromDay, $fromMonth, $fromYear, $pageNo, $perPageRecord, $companyId]);
                $count = $totalCount;
                if ($branchId != '' || $associteCode != '' || $companyId != '') {
                    $dataTotalCountF = DB::select('call associteCollectionList(?,?,?,?,?,?,?,?,?,?,?)', [$branchId, $associteCode, $toDay, $toMonth, $toYear, $fromDay, $fromMonth, $fromYear, 0, $perPageRecord, $companyId]);
                    $count = count($dataTotalCountF);
                }
                $sno = $_POST['start'];
                $rowReturn = array();
                foreach ($data as $row) {
                    $sno++;
                    $val['DT_RowIndex'] = $sno;
                    if (isset($row->com_name)) {
                        $val['company_id'] = $row->com_name;
                    } else {
                        $val['company_id'] = 'N/A';
                    }
                    if (isset($row->branch_code)) {
                        $val['branch_name'] = $row->name . " (" . $row->branch_code . ")";
                    } else {
                        $val['branch_name'] = 'N/A';
                    }
                    if (isset($row->associate_no)) {
                        $val['associate_code'] = $row->associate_no;
                    } else {
                        $val['associate_code'] = 'N/A';
                    }
                    if (isset($row->first_name)) {
                        $val['associate_name'] = $row->first_name . '  ' . $row->last_name;
                    } else {
                        $val['associate_name'] = 'N/A';
                    }
                    $val['total_collection'] = $row->totalsum;
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
    /*********************  associate Branch Transfer start ******************************/
    public function assbranchtransfer()
    {
        if (check_my_permission(Auth::user()->id, "277") != "1") {
            return redirect()
                ->route('admin.dashboard');
        }
        $data['title'] = 'Associate | Branch Transfer';
        return view('templates.admin.associate.branchtransfer', $data);
    }
    public function getAssociatebrtansferData(Request $request)
    {
        $branch = Branch::where('status', 1);
        $data = Member::where('associate_no', $request->code)
            ->where('is_deleted', 0);
        if (Auth::user()->branch_id > 0) {
            $branch = $branch->where('id', Auth::user()->branch_id);
        }
        $branch = $branch->get();
        $data = $data->first();
        $type = $request->type;
        if ($data) {
            if ($data->is_block == 1) {
                return \Response::json(['view' => 'No data found', 'msg_type' => 'error2']);
            } else {
                if ($data->associate_status == 1) {
                    $id = $data->id;
                    $carder = $data->current_carder_id;
                    return \Response::json(['view' => view('templates.admin.associate.partials.associatebranchtransfer_detail', ['memberData' => $data, 'branch' => $branch])->render(), 'msg_type' => 'success', 'id' => $id, 'carder' => $carder]);
                } else {
                    return \Response::json(['view' => 'No data found', 'msg_type' => 'error1']);
                }
            }
        } else {
            return \Response::json(['view' => 'No data found', 'msg_type' => 'error']);
        }
    }
    public function assbranchtransfersave(Request $request)
    {
        $id = $request->associate_id;
        $globaldate = $request['created_at'];
        $created_at = date("Y-m-d H:i:s", strtotime(convertDate($globaldate)));
        if (empty($request->branch_id) || empty($request->associate_id)) {
            //return \Response::json(['view' => 'No data found', 'msg_type' => 'error2']);
            return back()->with('errors', 'Associate Id Not Found');
        }
        $getBranchCode = getBranchCode($request->branch_id);
        $branchCode = $getBranchCode->branch_code;
        $Membernew = \App\Models\Member::where('id', $id)->update(['associate_branch_id' => $request->branch_id, 'associate_branch_code' => $branchCode]);
        $created_by_id = Auth::user()->id;
        $AssociateBranchTransfer = new AccountBranchTransfer;
        $AssociateBranchTransfer->type = 1;
        $AssociateBranchTransfer->new_branch_id = $request['branch_id'];
        $AssociateBranchTransfer->type_id = $request['associate_id'];
        $AssociateBranchTransfer->old_branch_id = $request['old_branch_id'];
        $AssociateBranchTransfer->created_by = 1;
        $AssociateBranchTransfer->created_by_id = $created_by_id;
        $AssociateBranchTransfer->created_at = $created_at;
        $AssociateBranchTransfer->updated_at = $created_at;
        $AssociateBranchTransfer->save();
        return back()->with('success', 'Associate branch updated successfully');
    }
    /*********************  associate Branch Transfer end ******************************/
    // Sourab ka banaya huaa Code hai contect to Sourab
    public function getCustomerData(Request $request)
    {
        $customerId = $request->code;
        $plan = $this->repository->getAllCompanies()->whereStatus('1')->whereDoesntHave('plans', function ($query) {
            $query->wherePlanCategoryCode('S')->whereStatus('1')->select(['id', 'plan_category_code']);
        })->count('id');
        if ($plan > 0) {
            return Response::json(['view' => 'Plan Not Exists', 'msg_type' => 'error3']);
        }
        $customer = $this->repository->getAllMember()->whereMemberId($customerId)->whereIsDeleted(0)->first();
        if (!$customer) {
            return Response::json(['view' => 'No record found !', 'msg_type' => 'error']);
        }
        if ($customer->status == '0') {
            return Response::json(['view' => 'Customer is Inactive. Please contact administrator!', 'msg_type' => 'error']);
        }
        if ($customer->is_block == '1') {
            return Response::json(['view' => 'Customer is Inactive. Please Upload Signature and Photo.', 'msg_type' => 'error']);
        }
        $branchId = ($customer->branch_id);
        $getBranchCode = getBranchCode($branchId);
        $branchCode = $getBranchCode->branch_code;
        $associateSettings = $this->repository->getAllCompanies()->whereStatus('1') /*->with('companyAssociate')*/ ->whereHas('companyAssociate', function ($query) {
            $query->whereStatus('1')->select(['id', 'status', 'company_id']);
        })->first(['id', 'status']);
        $companyId = $associateSettings->id;
        $savingAccount = $this->repository->getAllSavingAccount()->whereCustomerId($customer->id)->count('id');
        $customerCompany = $this->repository->getAllMemberCompany()->whereCustomerId($customerId)->whereStatus(1)->whereIsDeleted(0)->whereCompanyId($companyId)->count();
        // $tenureData = $this->repository->getAllPlanTenures()->with([
        //     'plans' => function ($q) use ($companyId) {
        //         $q->whereCompanyId($companyId)->wherePlanCategoryCode('M')->whereStatus('1')->wherePlanSubCategoryCode(NULL)->whereHybridType(NULL);
        //     }
        // ])->whereTenure('60')->first()->toArray();
        $tenureData = $this->repository->getAllPlanTenures()->whereTenure('60')->whereHas('plans', function ($q) use ($companyId) {
            $q->whereCompanyId($companyId)->wherePlanCategoryCode('M')->whereStatus('1')->wherePlanSubCategoryCode(NULL)->whereHybridType(NULL);
        })->first()->toArray();
        $tenure = $tenureData['tenure'] ?? '0';
        $defaultMemberInvesment = Memberinvestments::wherePlanId($tenureData['plan_id'])->whereCompanyId($companyId)->whereCustomerId($customer->id)->get(['id', 'plan_id', 'company_id', 'customer_id']);
        $associate = (isset($customer->associate_form_no) && isset($customer->associate_join_date) && isset($customer->associate_no) && ($customer->is_associate > 0) && ($customer->associate_micode > 0) && ($customer->associate_facode > 0) && isset($customer->associate_branch_id) && isset($customer->associate_branch_code) && isset($customer->associate_branch_id_old) && isset($customer->associate_branch_code_old) && isset($customer->associate_senior_code) && ($customer->associate_senior_id > 0) && ($customer->current_carder_id > 0)) ? '1' : '0';
        $rdPlanId = $tenureData['plan_id'] ?? '0';
        $recipt = Receipt::whereMemberId($customer->id)->whereReceiptsFor(1)->whereStatus(1)->first('id');
        // $rdPlanCode = $tenureData['plans']['plan_code'] ?? '0';
        $rdPlanCode = $tenureData['plan_code'] ?? '0';
        if ($customer->is_block == 1) {
            return Response::json(['view' => 'Customer Is Bloked', 'msg_type' => 'error2']);
        } else {
            // if ($associate == 0) {
            return ($customer->is_associate == 0) ? Response::json([
                'view' => view('templates.branch.associate_registration_management.partials.member_detail', [
                    'memberData' => $customer,
                    'idProofDetail' => MemberIdProof::where('member_id', $customer->id)->first(),
                    'nomineeDetail' => $nomineeDetail = MemberNominee::where('member_id', $customer->id)->first(),
                    'nomineeDOB' => $nomineeDetail->dob ? date("d/m/Y", strtotime($nomineeDetail->dob)) : ''
                ])->render(),
                'msg_type' => 'success',
                'id' => $customer->id,
                'haveSsbAccount' => $savingAccount > 0 ? '1' : '0',
                'Rd_Account_Investment' => ($tenure > 0 && $defaultMemberInvesment->count() == 0) ? '0' : '1',
                'nomineeDetail' => $nomineeDetail,
                'tenure' => $tenure,
                'rd_account_number' => $defaultMemberInvesment->first()->id ?? '0',
                'rdPlanCode' => $rdPlanCode,
                'rdPlanId' => $rdPlanId,
                'associate' => $associate,
                'details' => (object) [
                    'form_no' => $customer->associate_form_no,
                    'senior_code' => $customer->associate_senior_code,
                    'senior_id' => $customer->associate_senior_id,
                    'current_carder' => $customer->current_carder_id,
                    'senior_name' => $customer->first_name . ' ' . $customer->last_name,
                    'address' => $customer->address,
                    'mobile_no' => $customer->mobile_no,
                    'roi' => $tenureData['roi'] ?? '0',
                    'recipt_id' => $recipt ? $recipt->id : '0',
                ],
                'form' => ($savingAccount > 0 && $tenure != 0) ? '2' : '1',
                'nomineeDOB' => $nomineeDetail->dob ? date("d/m/Y", strtotime($nomineeDetail->dob)) : ''
            ]) : Response::json(['view' => 'Associate Already Exists', 'msg_type' => 'error1']);
        }
    }
    public function associateSsbAccountGet(Request $request)
    {
        $resCount = 0;
        $account_no = '';
        $balance = '';
        $name = '';
        $customerId = $request->customerId;
        $company = $this->repository->getAllCompanies()->whereStatus('1');
        $customer = $this->repository->getAllMember()->whereId($customerId)->whereStatus(1)->whereIsDeleted(0)->first(['first_name', 'id', 'last_name', 'member_id']);
        $memberCompany = $this->repository->getAllMemberCompany()->whereCustomerId($customerId)->whereStatus(1)->whereIsDeleted(0)->get();
        $savingAccount = $this->repository->getAllSavingAccount()->whereCustomerId($customerId);
        if ($savingAccount->count() > 0) {
            $account_no = $savingAccount->first()->account_no;
            $balance = $savingAccount->first()->balance;
            $resCount = 1;
        }
        if (!empty($customer)) {
            $name = $customer->first_name . ' ' . $customer->last_name;
        }
        $return_array = compact('account_no', 'balance', 'resCount', 'name');
        return Response::json($return_array);
    }
    public function getSeniorDetail(Request $request)
    {
        $array = ["id", "first_name", "last_name", 'mobile_no', 'address', 'current_carder_id', 'associate_status', 'is_block'];
        $data = memberFieldDataStatus($array, $request->code, 'associate_no');
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
    public function getCarderAssociate(Request $request)
    {
        //print_r($request->id);die;
        if ($request->id > 1) {
            $carde = \App\Models\Carder::where('id', '<', $request->id)->where('status', 1)->where('is_deleted', 0)->limit(15)->get(['id', 'name', 'short_name']);
        } else {
            $carde = \App\Models\Carder::where('id', '<=', $request->id)->where('status', 1)->where('is_deleted', 0)->limit(15)->get(['id', 'name', 'short_name']);
        }
        $return_array = compact('carde');
        return json_encode($return_array);
    }
    public function validator($request)
    {
        $rules = [
            'created_at' => ['required'],
            'form_no' => ['required'],
            'id' => ['required'],
            'senior_code' => ['required', 'numeric'],
            'current_carder' => ['required'],
            'payment_mode' => ['required_if:rd_account,0', 'nullable'],
            'rd_form_no' => ['required_if:rd_account,0', 'nullable', 'integer'],
            'tenure' => ['required_if:rd_account,0', 'numeric', 'nullable'],
            'rd_first_first_name' => ['required_if:rd_account,0', 'nullable'],
            'rd_second_first_name' => ['required_if:rd_account,0', 'nullable'],
            'rd_first_gender' => ['required_if:rd_account,0', 'nullable'],
            'rd_second_gender' => ['required_if:rd_account,0', 'nullable'],
            'rd_first_relation' => ['required_if:rd_account,0', 'nullable'],
            'rd_second_relation' => ['required_if:rd_account,0', 'nullable'],
            'rd_first_age' => ['required_if:rd_account,0', 'nullable'],
            'rd_second_age' => ['required_if:rd_account,0', 'nullable'],
            'rd_first_dob' => ['required_if:rd_account,0', 'date_format:d/m', 'nullable'],
            'rd_second_dob' => ['required_if:rd_account,0', 'date_format:d/m', 'nullable'],
            'rd_first_mobile_no' => ['required_if:rd_account,0', 'numeric', 'nullable'],
            'rd_second_mobile_no' => ['required_if:rd_account,0', 'numeric', 'nullable'],
            'rd_first_percentage' => ['required_if:rd_account,0', 'nullable'],
            'rd_second_percentage' => ['required_if:rd_account,0', 'nullable'],
            'ssb_amount' => ['required_if:ssb_account,0', 'nullable'],
            'ssb_form_no' => ['required_if:ssb_account,0', 'nullable'],
            'ssb_first_first_name' => ['required_if:ssb_account,0', 'nullable'],
            'ssb_second_first_name' => ['required_if:ssb_account,0', 'nullable'],
            'ssb_first_gender' => ['required_if:ssb_account,0', 'nullable'],
            'ssb_second_gender' => ['required_if:ssb_account,0', 'nullable'],
            'ssb_first_relation' => ['required_if:ssb_account,0', 'nullable'],
            'ssb_second_relation' => ['required_if:ssb_account,0', 'nullable'],
            'ssb_first_age' => ['required_if:ssb_account,0', 'nullable'],
            'ssb_second_age' => ['required_if:ssb_account,0', 'nullable'],
            'ssb_first_dob' => ['required_if:ssb_account,0', 'date_format:d/m', 'nullable'],
            'ssb_second_dob' => ['required_if:ssb_account,0', 'date_format:d/m', 'nullable'],
            'ssb_first_mobile_no' => ['required_if:ssb_account,0', 'numeric', 'nullable'],
            'ssb_second_mobile_no' => ['required_if:ssb_account,0', 'numeric', 'nullable'],
            'ssb_first_percentage' => ['required_if:ssb_account,0', 'nullable'],
            'ssb_second_percentage' => ['required_if:ssb_account,0', 'nullable'],
            'rd_online_bank_id' => ['required_if:payment_mode,2', 'required_if:rd_account,0', 'nullable'],
            'rd_online_id' => ['required_if:payment_mode,2', 'required_if:rd_account,0', 'nullable'],
            'rd_online_date' => ['required_if:payment_mode,2', 'required_if:rd_account,0', 'date_format:d/m', 'nullable'],
            'rd_online_bank_ac_id' => ['required_if:payment_mode,2', 'required_if:rd_account,0', 'nullable'],
            'cheque_id' => ['required_if:payment_mode,1', 'required_if:rd_account,0', 'nullable'],
            'rd_ssb_account_number' => ['required_if:payment_mode,3', 'required_if:rd_account,0', 'nullable'],
            'rd_ssb_account_amount' => ['required_if:payment_mode,3', 'required_if:rd_account,0', 'nullable']
        ];
        $validator = Validator::make($request, $rules);
        if ($validator->fails()) {
            return $validator->errors()->toArray();
        }
    }
    public function store(Request $request)
    {
        Session::put('created_at', $request->created_at);
        $errorCount = 0;
        $form = 0;
        $recipt_id = 0;
        $isReceipt = 'no';
        $is_primary = 0;
        $investmentId = 0;
        $rdAmount = $request->rd_amount;
        $dataMsg['errormsg'] = '';
        $investmentAccountNoSsb = '';
        $globaldate = $request->created_at;
        $created_at = date("Y-m-d H:i:s", strtotime(convertDate($globaldate)));
        $updated_at = date("Y-m-d H:i:s", strtotime(convertDate($globaldate)));
        $customerId = $request->id;
        $ssb_account = $request->ssb_account;
        $rd_account = $request->rd_account;
        $ssb_account_number = $request->ssb_account_number;
        $customer = $this->repository->getAllMember()->whereId($customerId)->whereStatus(1)->whereIsDeleted(0)->first();
        if (!empty($customer)) {
            $customerName = $customer->first_name . ' ' . $customer->last_name;
        }
        $branchId = $customer->branch_id;
        $getBranchCode = getBranchCode($branchId);
        $branchCode = $getBranchCode->branch_code;
        $ssb_amount = $request->ssb_amount;
        $associateSettings = $this->repository->getAllCompanies()->whereStatus('1')->whereHas('companyAssociate', function ($query) {
            $query->whereStatus('1')->select(['id', 'status', 'company_id']);
        })->first(['id', 'status']);
        $companyId = $associateSettings->id;
        $memberCompanyCount = $this->repository->getAllMemberCompany()->whereCustomerId($customerId)/*->whereCompanyId($companyId)*/ ->whereStatus(1)->whereIsDeleted(0)->count();
        $membercompany = $this->repository->getAllMemberCompany()->whereCustomerId($customerId)->whereCompanyId($companyId)->whereStatus(1)->whereIsDeleted(0)->first();
        $memberId = $membercompany ? $membercompany->id : NULL;
        $planAssociateSettings = $this->repository->getAllPlans()->wherePlanCategoryCode('S')->whereStatus('1')->whereCompanyId($companyId)->first(['id', 'company_id', 'status', 'plan_category_code', 'plan_code', 'deposit_head_id']);
        $FaCode = FaCode::whereCompanyId($companyId)->whereStatus('1')->orderBy('code', 'asc')->get(['id', 'name', 'code', 'status', 'company_id', 'slug']);
        // check if member not have any investment or not
        $notHaveMemberInvestment = $this->repository->getAllMemberinvestments()->whereCustomerId($customerId)->whereIsDeleted('0')->exists();
        // check if member not have any investment or not
        $investmentCheck = Memberinvestments::wherecustomerId($customerId)->get();
        if ($investmentCheck->count('id') >= 1) {
            $existingInvestmentId = $investmentCheck->first()->id;
            $existingInvestmentCompanyId = $investmentCheck->first()->company_id;
        }
        if ($request->id == '') {
            $dataMsg['errormsg'] .= 'Please select Customer.<br>';
            $form++;
            $errorCount++;
        }
        if ($request->form_no == '') {
            $dataMsg['errormsg'] .= 'Please enter form no.<br>';
            $form++;
            $errorCount++;
        }
        if ($request->application_date == '') {
            $dataMsg['errormsg'] .= 'Please enter application date.<br>';
            $form++;
            $errorCount++;
        }
        if ($request->ssb_account == '') {
            $dataMsg['errormsg'] .= 'Please select SSB account option.<br>';
            $form++;
            $errorCount++;
        }
        if ($request->ssb_account == 1) {
            if ($request->ssb_account_number == '') {
                $dataMsg['errormsg'] .= 'Please enter SSB account no.<br>';
                $form++;
                $errorCount++;
            }
        }
        if ($request->ssb_account == 0) {
            if ($request->ssb_amount == '') {
                $dataMsg['errormsg'] .= 'Please enter SSB amount.<br>';
                $form++;
                $errorCount++;
            }
            if ($request->ssb_first_first_name == '') {
                $dataMsg['errormsg'] .= 'Please enter SSB first nominee  name.<br>';
                $form++;
                $errorCount++;
            }
            if ($request->ssb_first_relation == '') {
                $dataMsg['errormsg'] .= 'Please enter SSB first nominee relation.<br>';
                $form++;
                $errorCount++;
            }
            if ($request->ssb_first_dob == '') {
                $dataMsg['errormsg'] .= 'Please enter SSB first nominee date of birth.<br>';
                $form++;
                $errorCount++;
            }
            if ($request->ssb_first_percentage == '') {
                $dataMsg['errormsg'] .= 'Please enter SSB first nominee percentage.<br>';
                $form++;
                $errorCount++;
            }
            if (!isset($request->ssb_first_gender)) {
                $dataMsg['errormsg'] .= 'Please select SSB first nominee gender.<br>';
                $form++;
                $errorCount++;
            }
            if ($request->ssb_first_age == '') {
                $dataMsg['errormsg'] .= 'Please enter SSB first nominee age.<br>';
                $form++;
                $errorCount++;
            }
            if ($request->ssb_first_mobile_no == '') {
                $dataMsg['errormsg'] .= 'Please enter SSB first nominee mobile No.<br>';
                $form++;
                $errorCount++;
            }
            if ($request->ssb_second_validate == 1) {
                if ($request->ssb_second_first_name == '') {
                    $dataMsg['errormsg'] .= 'Please enter SSB second nominee  name.<br>';
                    $form++;
                    $errorCount++;
                }
                if ($request->ssb_second_relation == '') {
                    $dataMsg['errormsg'] .= 'Please enter SSB second nominee relation.<br>';
                    $form++;
                    $errorCount++;
                }
                if ($request->ssb_second_dob == '') {
                    $dataMsg['errormsg'] .= 'Please enter SSB second nominee date of birth.<br>';
                    $form++;
                    $errorCount++;
                }
                if ($request->ssb_second_percentage == '') {
                    $dataMsg['errormsg'] .= 'Please enter SSB second nominee percentage.<br>';
                    $form++;
                    $errorCount++;
                }
                if (!isset($request->ssb_second_gender)) {
                    $dataMsg['errormsg'] .= 'Please select SSB second nominee gender.<br>';
                    $form++;
                    $errorCount++;
                }
                if ($request->ssb_second_age == '') {
                    $dataMsg['errormsg'] .= 'Please enter SSB second nominee age.<br>';
                    $form++;
                    $errorCount++;
                }
                if ($request->ssb_second_mobile_no == '') {
                    $dataMsg['errormsg'] .= 'Please enter SSB second nominee mobile No.<br>';
                    $form++;
                    $errorCount++;
                }
            }
        }
        if (check_my_permission(Auth::user()->id, "137") == "1" && Auth::user()->role_id == "2") {
            if ($request->rd_account == '') {
                $dataMsg['errormsg'] .= 'Please select RD account option.<br>';
                $form++;
                $errorCount++;
            }
            if ($request->rd_account == 1) {
                if ($request->rd_account_number == '') {
                    $dataMsg['errormsg'] .= 'Please enter RD account no.<br>';
                    $form++;
                    $errorCount++;
                }
            }
            if ($request->rd_account == 0) {
                if ($request->rd_amount == '') {
                    $dataMsg['errormsg'] .= 'Please enter RD amount.<br>';
                    $form++;
                    $errorCount++;
                }
                if ($request->rd_first_first_name == '') {
                    $dataMsg['errormsg'] .= 'Please enter RD first nominee  name.<br>';
                    $form++;
                    $errorCount++;
                }
                if ($request->rd_first_relation == '') {
                    $dataMsg['errormsg'] .= 'Please enter RD first nominee relation.<br>';
                    $form++;
                    $errorCount++;
                }
                if ($request->rd_first_dob == '') {
                    $dataMsg['errormsg'] .= 'Please enter RD first nominee date of birth.<br>';
                    $form++;
                    $errorCount++;
                }
                if ($request->rd_first_percentage == '') {
                    $dataMsg['errormsg'] .= 'Please enter RD first nominee percentage.<br>';
                    $form++;
                    $errorCount++;
                }
                if (!isset($request->rd_first_gender)) {
                    $dataMsg['errormsg'] .= 'Please select RD first nominee gender.<br>';
                    $form++;
                    $errorCount++;
                }
                if ($request->rd_first_age == '') {
                    $dataMsg['errormsg'] .= 'Please enter RD first nominee age.<br>';
                    $form++;
                    $errorCount++;
                }
                if ($request->rd_first_mobile_no == '') {
                    $dataMsg['errormsg'] .= 'Please enter RD first nominee mobile No.<br>';
                    $form++;
                    $errorCount++;
                }
                if ($request->rd_second_validate == 1) {
                    if ($request->rd_second_first_name == '') {
                        $dataMsg['errormsg'] .= 'Please enter RD second nominee  name.<br>';
                        $form++;
                        $errorCount++;
                    }
                    if ($request->rd_second_relation == '') {
                        $dataMsg['errormsg'] .= 'Please enter RD second nominee relation.<br>';
                        $form++;
                        $errorCount++;
                    }
                    if ($request->rd_second_dob == '') {
                        $dataMsg['errormsg'] .= 'Please enter RD second nominee date of birth.<br>';
                        $form++;
                        $errorCount++;
                    }
                    if ($request->rd_second_percentage == '') {
                        $dataMsg['errormsg'] .= 'Please enter RD second nominee percentage.<br>';
                        $form++;
                        $errorCount++;
                    }
                    if (!isset($request->rd_second_gender)) {
                        $dataMsg['errormsg'] .= 'Please select RD second nominee gender.<br>';
                        $form++;
                        $errorCount++;
                    }
                    if ($request->rd_second_age == '') {
                        $dataMsg['errormsg'] .= 'Please enter RD second nominee age.<br>';
                        $form++;
                        $errorCount++;
                    }
                    if ($request->rd_second_mobile_no == '') {
                        $dataMsg['errormsg'] .= 'Please enter RD second nominee mobile No.<br>';
                        $form++;
                        $errorCount++;
                    }
                }
            }
        }
        if ($request->ssb_account == 0) {
            // ssb account no exits or not(pass member id and ssb account no)
            $ssbAccountDetail = getMemberCompanySsbAccountDetail($customerId, $companyId);
            if (!empty($ssbAccountDetail)) {
                $dataMsg['msg_type'] = 'error';
                $dataMsg['msg'] = 'SSB account already exists!.';
                $dataMsg['reciept_generate '] = 'no';
                $dataMsg['reciept_id'] = 0;
                $dataMsg['errormsg'] .= 'SSB account already exists!.<br>';
                $form++;
                $errorCount++;
            }
        }
        if ($request->payment_mode == 1) {
            $getChequeDetail = ReceivedCheque::where('id', $request->cheque_id)->where('status', 3)->first(['id', 'amount', 'status']);
            if (!empty($getChequeDetail)) {
                $dataMsg['msg_type'] = 'cheque_error';
                $dataMsg['msg'] = 'Cheque already used select another cheque.';
                $dataMsg['reciept_generate '] = 'no';
                $dataMsg['reciept_id'] = 0;
                $dataMsg['errormsg'] .= 'Cheque already used select another cheque.<br>';
                $form++;
                $errorCount++;
            } else {
                $getamount = ReceivedCheque::where('id', $request->cheque_id)->first(['id', 'amount']);
                if ($getamount->amount != number_format((float) $request->rd_amount, 4, '.', '')) {
                    $dataMsg['msg_type'] = 'cheque_error';
                    $dataMsg['msg'] = 'RD amount is not equal to cheque amount.';
                    $dataMsg['reciept_generate '] = 'no';
                    $dataMsg['reciept_id'] = 0;
                    $dataMsg['errormsg'] .= 'RD amount is not equal to cheque amount.<br>';
                    $form++;
                    $errorCount++;
                }
            }
        }
        if ($errorCount > 0) {
            $dataMsg['form'] = $form;
            return json_encode($dataMsg);
        }
        DB::beginTransaction();
        try {
            /*
            if ($existingInvestmentId > 0) {
                $stationaryCharges = Investment::stationaryCharges($existingInvestmentId, $existingInvestmentCompanyId);
                if ($stationaryCharges) {
                    $amountArray = array('1' => 50);
                    $typeArray = array('1' => 1);
                    $receipts_for = 1;
                    $createRecipt = CommanTransactionsController::createPaymentRecipt(0, 0, $customerId, $branchId, $branchCode, $amountArray, $typeArray, $receipts_for, $account_no = '0');
                }
            }
            */
            if (!$memberId) {
                $customerDetail = (object) [
                    'id' => $customerId,
                    'associate_code' => $customer->associate_code,
                    'associate_id' => $customer->associate_id,
                    'ssb_account' => $ssb_account_number ?? 0,
                    'rd_account' => 0,
                    'branch_mi' => $customer->branch_mi,
                    'reinvest_old_account_number' => NULL,
                    'old_c_id' => 0,
                    'otp' => NULL,
                    'varifiy_time' => NULL,
                    'is_varified' => NULL,
                    'upi' => NULL,
                    'token' => csrf_token(),
                ];
                $customerDetailsRequest = [
                    'company_id' => $companyId,
                    'create_application_date' => $globaldate,
                    'branchid' => $branchId,
                ];
                $membercompany = Investment::registerMember($customerDetail, $customerDetailsRequest);
                $memberId = $membercompany->id;
                // create recipt
                if ($memberCompanyCount == 0) {
                    if (!$ssb_account_number) {
                        $amountMi = 10;
                        $amountStn = 90;
                        $amountArray = array('1' => $amountMi, '2' => $amountStn);
                        $typeArray = array('1' => 1, '2' => 2);
                        $receipts_for = 1;
                        $createRecipt = CommanTransactionsController::createPaymentRecipt(0, 0, $customerId, $branchId, $branchCode, $amountArray, $typeArray, $receipts_for, $account_no = '0');
                        $recipt_id = $createRecipt;
                        /************************* Account head impelment memberId********************/
                        $totalAmount = $amountMi + $amountStn;
                        $daybookRefMember = CommanTransactionsController::createBranchDayBookReferenceNew($totalAmount, $globaldate);
                        $bank_id = NULL;
                        $bank_ac_id = NULL;
                        $amount_to_id = NULL;
                        $amount_to_name = NULL;
                        $amount_from_id = NULL;
                        $amount_from_name = NULL;
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
                        $type_transaction_id = NULL;
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
                        $refId = $daybookRefMember;
                        $type_id = $customerId;
                        $entry_date = date("Y-m-d", strtotime(convertDate($globaldate)));
                        $entry_time = date("H:i:s", strtotime(convertDate($globaldate)));
                        $created_by = 1;
                        $created_by_id = Auth::user()->id;
                        $payment_type = 'CR';
                        $payment_mode = 0; // Cash Payment Mod
                        $currency_code = 'INR';
                        $typeMI = 1;
                        $sub_typeMI = 11;
                        $head_idM1 = 55; // MEMBERSHIP FEES-10/- Head id in Account Head
                        $head_idM2 = 28; // CASH IN HAND Head id in Account Head
                        $desMI = 'Cash received from member ' . $customer->first_name . ' ' . $customer->last_name . '(' . $customer->member_id . ') through MI charge';
                        $desMIDR = 'Cash A/c Dr ' . $amountMi . '/-';
                        $desMICR = 'To ' . $customer->first_name . ' ' . $customer->last_name . '(' . $customer->member_id . ') A/c Cr ' . $amountMi . '/-';
                        $daybookMI = CommanTransactionsController::createBranchDayBookNew($refId, $branchId, $typeMI, $sub_typeMI, $type_id, $customer->associate_id, $memberId, $branchId_to = NULL, $branchId_from = NULL, $amountMi, $desMI, $desMIDR, $desMICR, $payment_type, $payment_mode, $currency_code, $v_no = NULL, $ssb_account_id_from = NULL, $cheque_no = NULL, $transction_no = NULL, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $type_transaction_id = NULL, $companyId);
                        $allTranMI = CommanTransactionsController::headTransactionCreate($refId, $branchId, $bank_id, $bank_ac_id, $head_idM1, $typeMI, $sub_typeMI, $type_id, $customerId, $memberId, $branchId_to = NULL, $branchId_from = NULL, $amountMi, $desMI, 'CR', $payment_mode, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $type_transaction_id, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $companyId);
                        $allTranMI2 = CommanTransactionsController::headTransactionCreate($refId, $branchId, $bank_id, $bank_ac_id, $head_idM2, $typeMI, $sub_typeMI, $type_id, $customerId, $memberId, $branchId_to = NULL, $branchId_from = NULL, $amountMi, $desMI, 'DR', $payment_mode, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $type_transaction_id, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $companyId);
                        $typeSTN = 1;
                        $sub_typeSTN = 12;
                        $head3STN = 34;
                        $head3STN2 = 28;
                        $desSTN = 'Cash received from member ' . $customer->first_name . ' ' . $customer->last_name . '(' . $customer->member_id . ') through STN charge';
                        $desSTNDR = 'Cash A/c Dr ' . $amountStn . '/-';
                        $desSTNCR = 'To ' . $customer->first_name . ' ' . $customer->last_name . '(' . $customer->member_id . ') A/c Cr ' . $amountStn . '/-';
                        $daybookMI = CommanTransactionsController::createBranchDayBookNew($refId, $branchId, $typeSTN, $sub_typeSTN, $type_id, $customer->associate_id, $memberId, $branchId_to = NULL, $branchId_from = NULL, $amountStn, $desSTN, $desSTNDR, $desSTNCR, $payment_type, $payment_mode, $currency_code, $v_no = NULL, $ssb_account_id_from = NULL, $cheque_no = NULL, $transction_no = NULL, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $type_transaction_id = NULL, $companyId);
                        $allTranSTN = CommanTransactionsController::headTransactionCreate($refId, $branchId, $bank_id, $bank_ac_id, $head3STN, $typeSTN, $sub_typeSTN, $type_id, $customerId, $memberId, $branchId_to = NULL, $branchId_from = NULL, $amountStn, $desSTN, 'CR', $payment_mode, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $type_transaction_id, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $companyId);
                        $allTranSTN2 = CommanTransactionsController::headTransactionCreate($refId, $branchId, $bank_id, $bank_ac_id, $head3STN2, $typeSTN, $sub_typeSTN, $type_id, $customerId, $memberId, $branchId_to = NULL, $branchId_from = NULL, $amountStn, $desSTN, 'DR', $payment_mode, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $type_transaction_id, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $companyId);
                    }
                }
                /******** Balance   entry ***************/
                /************************* Account head impelment ********************/
            }
            if (!$ssb_account_number) {
                // saving accout create deafault company
                $payment_mode = 0;
                // getInvesment Plan id by plan code
                $planAssociateSettings = $this->repository->getAllPlans()->wherePlanSubCategoryCode(Null)->wherePlanCategoryCode('S')->whereStatus('1')->whereCompanyId($companyId)->first();
                $ssbFaCode = $planAssociateSettings->plan_code;
                // $ssbFaCode = $FaCode[3]->code;
                $ssbPlanIdGet = getPlanID($ssbFaCode);
                $ssbPlanId = $ssbPlanIdGet->id;
                $investmentMiCodeSsb = getInvesmentMiCodeNew($ssbPlanId, $branchId);
                if (!empty($investmentMiCodeSsb)) {
                    $ssbmiCodeAdd = $investmentMiCodeSsb->mi_code + 1;
                } else {
                    $ssbmiCodeAdd = 1;
                }
                $miCodeSsb = str_pad($ssbmiCodeAdd, 5, '0', STR_PAD_LEFT);
                // Invesment Account no
                $investmentAccountNoSsb = $branchCode . $ssbFaCode . $miCodeSsb;
                $dataInvest['deposite_amount'] = $ssb_amount;
                $dataInvest['plan_id'] = $ssbPlanId;
                $dataInvest['form_number'] = $request->ssb_form_no;
                $dataInvest['member_id'] = $memberId;
                $dataInvest['customer_id'] = $customerId;
                $dataInvest['branch_id'] = $branchId;
                $dataInvest['old_branch_id'] = $branchId;
                $dataInvest['account_number'] = $investmentAccountNoSsb;
                $dataInvest['mi_code'] = $miCodeSsb;
                $dataInvest['associate_id'] = 1;
                $dataInvest['current_balance'] = $ssb_amount;
                $dataInvest['created_at'] = $globaldate;
                $dataInvest['interest_rate'] = 0.00;
                $dataInvest['company_id'] = $companyId;
                $res = $this->repository->CreateMemberinvestments($dataInvest);
                $investmentId = $res->id;
                //create savings account
                $des = 'SSB Account Opening';
                $amount = $ssb_amount;
                $daybookRefssbId = CommanTransactionsController::createBranchDayBookReferenceNew($ssb_amount, $globaldate);
                $createAccount = CommanTransactionsController::createSavingAccountDescriptionModify($memberId, $branchId, $branchCode, $amount, $payment_mode, $investmentId, $miCodeSsb, $investmentAccountNoSsb, $is_primary, $ssbFaCode, $des, $associate_id = 1, $companyId, $globaldate, $customerId, $daybookRefssbId);
                // p('saving Account');
                $ssbAccountId = $createAccount['ssb_id'];
                $amountArraySsb = array('1' => $amount);
                $description = 'SSB Account Opening';
                $sAccountNumber = '';
                $satRefId = NULL;
                $invData1ssb = [
                    'investment_id' => $investmentId,
                    'nominee_type' => 0,
                    'name' => $request->ssb_first_first_name,
                    'relation' => $request->ssb_first_relation,
                    'gender' => $request->ssb_first_gender,
                    'dob' => date("Y-m-d", strtotime(convertDate($request->ssb_first_dob))),
                    'age' => $request->ssb_first_age,
                    'percentage' => $request->ssb_first_percentage,
                    'phone_number' => $request->ssb_first_mobile_no,
                    'created_at' => $globaldate,
                ];
                $resinvData1 = $this->repository->CreateMemberinvestmentsnominees($invData1ssb);
                if ($request->ssb_second_validate == 1) {
                    $invData2ssb = [
                        'investment_id' => $investmentId,
                        'nominee_type' => 1,
                        'name' => $request->ssb_second_first_name,
                        'relation' => $request->ssb_second_relation,
                        'gender' => $request->ssb_second_gender,
                        'dob' => date("Y-m-d", strtotime(convertDate($request->ssb_second_dob))),
                        'age' => $request->ssb_second_age,
                        'percentage' => $request->ssb_second_percentage,
                        'phone_number' => $request->ssb_second_mobile_no,
                        'created_at' => $globaldate,
                    ];
                    $resinvData2 = $this->repository->CreateMemberinvestmentsnominees($invData2ssb);
                }
                //==================  Head Implement start ==============/
                $jv_unique_id = $ssb_account_tran_id_to = $ssb_account_tran_id_from = $cheque_type = $cheque_id = $cheque_bank_to_name = $cheque_bank_to_branch = $cheque_bank_to_ac_no = $cheque_bank_to_ifsc = $ssb_account_id_to = $transction_bank_from_id = $transction_bank_from_ac_id = $transction_bank_to_name = $transction_bank_to_ac_no = $transction_bank_to_branch = $transction_bank_to_ifsc = $cheque_bank_from_id = $cheque_bank_ac_from_id = NULL;
                $amount_to_name = NULL;
                $amount_from_id = NULL;
                $amount_from_name = NULL;
                $amount_to_id = NULL;
                $amount_to_id = NULL;
                $bank_id = NULL;
                $bank_ac_id = NULL;
                $associate_id_admin = 1;
                $ssbAmount = $ssb_amount;
                $refIdssb = $daybookRefssbId;
                $currency_code = 'INR';
                $headPaymentModessb = 0;
                $payment_type_ssb = 'CR';
                $type_idssb = $ssbAccountId;
                $entry_date = date("Y-m-d", strtotime(convertDate($globaldate)));
                $entry_time = date("H:i:s", strtotime(convertDate($globaldate)));
                $created_by = 1;
                $created_by_id = Auth::user()->id;
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
                // $head4ssb = getPlanDetail($ssbPlanId)->deposit_head_id;
                $head4ssb = getPlanDetailByCompany($companyId);
                $head5ssb = NULL;
                $daybookssb = CommanTransactionsController::NewFieldBranchDaybookCreate($refIdssb, $branchId, $typeHeadssb, $sub_typeHeadssb, $type_idssb, 1, $memberId, $branchId_to = NULL, $branchId_from = NULL, $ssbAmount, $ssbDes, $ssbDesDR, $ssbDes, $payment_type_ssb, $headPaymentModessb, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $jv_unique_id, $cheque_type, $cheque_id, $companyId);
                // Investment head entry +
                $allTranssb = CommanTransactionsController::headTransactionCreate($refIdssb, $branchId, $bank_id, $bank_ac_id, $head4ssb, $typeHeadssb, $sub_typeHeadssb, $type_idssb, $associate_id_admin, $memberId, $branchId_to = NULL, $branchId_from = NULL, $ssbAmount, $ssbDes, $payment_type_ssb, $headPaymentModessb, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $createAccount['ssb_transaction_id'], $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $companyId);
                // branch cash  head entry +
                $head3ssbC = 28;
                $allTranssbcash = CommanTransactionsController::headTransactionCreate($refIdssb, $branchId, $bank_id, $bank_ac_id, $head3ssbC, $typeHeadssb, $sub_typeHeadssb, $type_idssb, $associate_id_admin, $memberId, $branchId_to = NULL, $branchId_from = NULL, $ssbAmount, $ssbDes, 'DR', $headPaymentModessb, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $createAccount['ssb_transaction_id'], $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $companyId);
                $ssb_account_number = $investmentAccountNoSsb;
            }
            if (!$rd_account && !empty($request->rd_first_percentage)) {
                $faCodeRd = $request->rdPlanCode;
                $dataInvestrd['deposite_amount'] = $request->rd_amount;
                $dataInvestrd['payment_mode'] = $request->payment_mode;
                $dataInvestrd['tenure'] = $request->tenure / 12;
                $dataInvestrd['company_id'] = $companyId;
                $dataInvestrd['current_balance'] = $request->rd_amount;
                $dataInvestrd['tenure_fa_code'] = NULL;
                $formNumber = $request->rd_form_no;
                // getInvesment Plan id by plan code
                $planIdRd = $request->rdPlanId;
                $investmentMiCodeRD = getInvesmentMiCode($planIdRd, $branchId);
                if (!empty($investmentMiCodeRD)) {
                    $miCodeAddRD = $investmentMiCodeRD->mi_code + 1;
                    if ($investmentMiCodeRD->mi_code == 9999998) {
                        $miCodeAddRD = $investmentMiCodeRD->mi_code + 2;
                    }
                } else {
                    $miCodeAddRD = 1;
                }
                $miCodeRd = str_pad($miCodeAddRD, 5, '0', STR_PAD_LEFT);
                // Invesment Account no
                $investmentAccountNoRd = $branchCode . $faCodeRd . $miCodeRd;
                $miCodeBig = str_pad($miCodeAddRD, 5, '0', STR_PAD_LEFT);
                $passbook = $FaCode[0]->code . $branchCode . $faCodeRd . $miCodeBig;
                $rate = $request->roi;
                $time = $request->tenure;
                $principal = $request->rd_amount;
                $ci = 1;
                $irate = $rate / $ci;
                $year = $time / 12;
                $freq = 4;
                $mAmountResult = 0;
                for ($i = 1; $i <= $time; $i++) {
                    $mAmountResult += $principal * pow((1 + (($rate / 100) / $freq)), $freq * (($time - $i + 1) / 12));
                }
                $maturityAmountVal = (round($mAmountResult) > 0 && $time <= 84) ? round($mAmountResult) : '';
                // $maturityAmountVal  = number_format($maturityAmountVal , 2);
                $dataInvestrd['passbook_no'] = $passbook;
                $dataInvestrd['maturity_amount'] = $maturityAmountVal;
                $dataInvestrd['old_branch_id'] = $branchId;
                $payment_mode = 0;
                $rdDebitaccountId = 0;
                $rdPayDate = null;
                $received_cheque_id = NULL;
                $cheque_deposit_bank_id = NULL;
                $cheque_deposit_bank_ac_id = NULL;
                $online_deposit_bank_id = NULL;
                $online_deposit_bank_ac_id = NULL;
                $maturityDate = new DateTime($globaldate);
                $maturityDate->modify('+60 months');
                if ($request->payment_mode == 1) {
                    $received_cheque_id = $request->cheque_id;
                    $chequeDetail = ReceivedCheque::where('id', $request->cheque_id)->first();
                    $cheque_deposit_bank_id = $chequeDetail->deposit_bank_id;
                    $cheque_deposit_bank_ac_id = $chequeDetail->deposit_account_id;
                    $invPaymentMode['cheque_date'] = date("Y-m-d", strtotime(convertDate($request->rd_cheque_date)));
                    $payment_mode = 1;
                    $rdPayDate = date("Y-m-d", strtotime(convertDate($request->rd_cheque_date)));
                } elseif ($request->payment_mode == 2) {
                    $payment_mode = 3;
                    $online_deposit_bank_id = $request->rd_online_bank_id;
                    $online_deposit_bank_ac_id = $request->rd_online_bank_ac_id;
                    $rdPayDate = date("Y-m-d", strtotime(convertDate($request->rd_online_date)));
                } elseif ($request->payment_mode == 3) {
                    $rdPayDate = date("Y-m-d");
                    $ssbAccountDetail = getMemberCompanySsbAccountDetail($customerId, $companyId);
                    $payment_mode = 4;
                    $rdDebitaccountId = $ssbAccountDetail->id;
                    if (!empty($ssbAccountDetail)) {
                        if ($ssbAccountDetail->balance > $request->rd_amount) {
                            $detail = 'RD/' . $investmentAccountNoRd . '/Auto Debit';
                            // data save in saviong account transaction table 
                            $ssbTranCalculation = CommanTransactionsController::ssbTransactionModify($ssbAccountDetail->id, $ssbAccountDetail->account_no, $ssbAccountDetail->balance, $request->rd_amount, $detail, 'INR', 'DR', 3, $branchId, 1, 6);
                            $amountArrayRD = array('1' => $request->rd_amount);
                            $dataInvestrd['plan_id'] = $planIdRd;
                            $dataInvestrd['form_number'] = $formNumber;
                            $dataInvestrd['member_id'] = $memberId;
                            $dataInvestrd['branch_id'] = $branchId;
                            $dataInvestrd['old_branch_id'] = $branchId;
                            $dataInvestrd['maturity_date'] = $maturityDate->format('Y-m-d');
                            $dataInvestrd['account_number'] = $investmentAccountNoRd;
                            $dataInvestrd['mi_code'] = $miCodeRd;
                            $dataInvestrd['associate_id'] = 1;
                            $dataInvestrd['interest_rate'] = $rate;
                            $dataInvestrd['current_balance'] = $request->rd_amount;
                            $dataInvestrd['created_at'] = $request->created_at;
                            $res = $this->repository->CreateMemberinvestments($dataInvestrd);
                            $investmentId = $res->id;
                        } else {
                            $dataMsg['msg_type'] = 'error';
                            $dataMsg['msg'] = 'Your SSB account does not have a sufficient balance.';
                            $dataMsg['reciept_generate '] = $isReceipt;
                            $dataMsg['reciept_id'] = $recipt_id;
                            $dataMsg['errormsg'] .= 'Your SSB account does not have a sufficient balance.<br>';
                        }
                    } else {
                        $dataMsg['msg_type'] = 'error';
                        $dataMsg['msg'] = 'You does not have SSB account';
                        $dataMsg['reciept_generate '] = $isReceipt;
                        $dataMsg['reciept_id'] = $recipt_id;
                        $dataMsg['errormsg'] .= 'You does not have SSB account.<br>';
                    }
                } else {
                    $dataInvestrd['plan_id'] = $planIdRd;
                    $dataInvestrd['form_number'] = $formNumber;
                    $dataInvestrd['member_id'] = $memberId;
                    $dataInvestrd['customer_id'] = $customerId;
                    $dataInvestrd['branch_id'] = $branchId;
                    $dataInvestrd['account_number'] = $investmentAccountNoRd;
                    $dataInvestrd['mi_code'] = $miCodeRd;
                    $dataInvestrd['maturity_date'] = $maturityDate->format('Y-m-d');
                    $dataInvestrd['associate_id'] = 1;
                    $dataInvestrd['interest_rate'] = $rate;
                    $dataInvestrd['current_balance'] = $request->rd_amount;
                    $dataInvestrd['created_at'] = $request->created_at;
                    $dataInvestrd['company_id'] = $companyId;
                    $res = $this->repository->CreateMemberinvestments($dataInvestrd);
                    $investmentId = $res->id;
                    $satRefId = NULL;
                }
                $invDatard1 = [
                    'investment_id' => $investmentId,
                    'nominee_type' => 0,
                    'name' => $request->rd_first_first_name,
                    'relation' => $request->rd_first_relation,
                    'gender' => $request->rd_first_gender,
                    'dob' => date("Y-m-d", strtotime(convertDate($request->rd_first_dob))),
                    'age' => $request->rd_first_age,
                    'percentage' => $request->rd_first_percentage,
                    'phone_number' => $request->rd_first_mobile_no,
                    'created_at' => $request->created_at,
                ];
                $resinvDatard1 = $this->repository->CreateMemberinvestmentsnominees($invDatard1);
                if ($request->rd_second_validate == 1) {
                    $invDatard2 = [
                        'investment_id' => $investmentId,
                        'nominee_type' => 1,
                        'name' => $request->rd_second_first_name,
                        'relation' => $request->rd_second_relation,
                        'gender' => $request->rd_second_gender,
                        'dob' => date("Y-m-d", strtotime(convertDate($request->rd_second_dob))),
                        'age' => $request->rd_second_age,
                        'percentage' => $request->rd_second_percentage,
                        'phone_number' => $request->rd_second_mobile_no,
                        'created_at' => $request->created_at,
                    ];
                    $resinvDatard2 = $this->repository->CreateMemberinvestmentsnominees($invDatard2);
                }
                $amountArray = array('1' => $request->rd_amount);
                $sAccountNumber = ($rdDebitaccountId != 0) ? $rdDebitaccountId : '';
                $description = 'SRD Account Opening';
                $daybookRefRD = CommanTransactionsController::createBranchDayBookReferenceNew($rdAmount, $globaldate);
                $createDayBook = CommanTransactionsController::createDayBookNew(NULL, $daybookRefRD, 2, $investmentId, $request->senior_id, $memberId, $request->rd_amount, $request->rd_amount, $withdrawal = 0, $description, $sAccountNumber, $branchId, $branchCode, $amountArray, $payment_mode, $customerName, $customerId, $investmentAccountNoRd, $request->rd_cheque_no, $request->rd_bank_name, $request->rd_branch_name, $rdPayDate, $request->rd_online_id, $online_payment_by = Null, $sAccountNumber, 'CR', $received_cheque_id, $cheque_deposit_bank_id, $cheque_deposit_bank_ac_id, $online_deposit_bank_id, $online_deposit_bank_ac_id, $companyId);
                //--------------------- received cheque payment -----------------------//
                if ($payment_mode == 1) { // payment type Cheque
                    $receivedPayment['type'] = 2;
                    $receivedPayment['branch_id'] = $branchId;
                    $receivedPayment['investment_id'] = $investmentId;
                    $receivedPayment['cheque_id'] = $request->cheque_id;
                    $receivedPayment['day_book_id'] = $createDayBook;
                    $receivedPayment['created_at'] = $globaldate;
                    $receivedCreate = ReceivedChequePayment::create($receivedPayment);
                    $dataRC['status'] = 3;
                    $receivedcheque = ReceivedCheque::find($request->cheque_id);
                    $receivedcheque->update($dataRC);
                }
                //--------------------- received cheque payment -----------------------//
                //-------***************** RD head Implement start **********---//
                $jv_unique_id = $ssb_account_tran_id_to = $ssb_account_tran_id_from = $cheque_type = $cheque_id = $cheque_bank_to_name = $cheque_bank_to_branch = $cheque_bank_to_ac_no = $cheque_bank_to_ifsc = $ssb_account_id_to = $transction_bank_from_id = $transction_bank_from_ac_id = $transction_bank_to_name = $transction_bank_to_ac_no = $transction_bank_to_branch = $transction_bank_to_ifsc = $cheque_bank_from_id = $cheque_bank_ac_from_id = NULL;
                $amount_to_name = NULL;
                $amount_from_id = NULL;
                $amount_from_name = NULL;
                $amount_to_id = NULL;
                $bank_id = NULL;
                $bank_ac_id = NULL;
                $associate_id_admin = 1;
                $rdAmount = $request->rd_amount;
                $refIdRD = $daybookRefRD;
                $currency_code = 'INR';
                $headPaymentModeRD = 0;
                $payment_type_rd = 'CR';
                $type_idRD = $investmentId;
                $entry_date = date("Y-m-d", strtotime(convertDate($globaldate)));
                $entry_time = date("H:i:s", strtotime(convertDate($globaldate)));
                $created_by = 1;
                $created_by_id = Auth::user()->id;
                $planDetail = getPlanDetail($planIdRd, $companyId);
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
                if ($request->payment_mode == 1) { // cheque moade 
                    $headPaymentModeRD = 1;
                    $chequeDetail = ReceivedCheque::where('id', $request->cheque_id)->first();
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
                    $cheque_id = $request->cheque_id;
                    $cheque_bank_from_id = $cheque_bank_ac_from_id = NULL;
                    $getBankHead = SamraddhBank::where('id', $cheque_bank_to)->first();
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
                    $allTranRDcheque = CommanTransactionsController::headTransactionCreate($refIdRD, $branchId, $bank_id, $bank_ac_id, $head41, $typeHeadRd, $sub_typeHeadRd, $type_idRD, $associate_id_admin, $memberId, $branchId_to = NULL, $branchId_from = NULL, $rdAmount, $rdDes, 'DR', $headPaymentModeRD, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $createDayBook, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $companyId);
                    //bank entry
                    $bankCheque = CommanTransactionsController::NewFieldAddSamraddhBankDaybookCreate($refIdRD, $cheque_bank_to, $cheque_bank_ac_to, $typeHeadRd, $sub_typeHeadRd, $type_idRD, $associate_id_admin, $memberId, $branchId, $opening_balance = NULL, $rdAmount, $closing_balance = NULL, $rdDes, $rdDesDR, $rdDesCR, 'CR', $headPaymentModeRD, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $createDayBook, $ssb_account_id_to, $cheque_bank_from_id, $cheque_bank_ac_from_id, $cheque_bank_to_name, $cheque_bank_to_branch, $cheque_bank_to_ac_no, $cheque_bank_to_ifsc, $transction_bank_from_id, $transction_bank_from_ac_id, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $jv_unique_id, $cheque_type, $cheque_id, $companyId);
                }
                if ($request->payment_mode == 2) { //online transaction
                    $headPaymentModeRD = 2;
                    $transction_no = $request->rd_online_id;
                    $transction_bank_from = NULL;
                    $transction_bank_ac_from = NULL;
                    $transction_bank_ifsc_from = NULL;
                    $transction_bank_branch_from = NULL;
                    $transction_bank_to = $online_deposit_bank_id;
                    $transction_bank_ac_to = $online_deposit_bank_ac_id;
                    $transction_date = $rdPayDate;
                    $getBHead = SamraddhBank::where('id', $transction_bank_to)->first();
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
                    $allTranRDonline = CommanTransactionsController::headTransactionCreate($refIdRD, $branchId, $bank_id, $bank_ac_id, $head411, $typeHeadRd, $sub_typeHeadRd, $type_idRD, $associate_id_admin, $memberId, $branchId_to = NULL, $branchId_from = NULL, $rdAmount, $rdDes, 'DR', $headPaymentModeRD, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $createDayBook, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $companyId);
                    //bank entry
                    $bankonline = CommanTransactionsController::NewFieldAddSamraddhBankDaybookCreate($refIdRD, $transction_bank_to, $transction_bank_ac_to, $typeHeadRd, $sub_typeHeadRd, $type_idRD, $associate_id_admin, $memberId, $branchId, $opening_balance = NULL, $rdAmount, $closing_balance = NULL, $rdDes, $rdDesDR, $rdDesCR, 'CR', $headPaymentModeRD, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $createDayBook, $ssb_account_id_to, $cheque_bank_from_id, $cheque_bank_ac_from_id, $cheque_bank_to_name, $cheque_bank_to_branch, $cheque_bank_to_ac_no, $cheque_bank_to_ifsc, $transction_bank_from_id, $transction_bank_from_ac_id, $transction_bank_to_name, $transction_bank_to_ac_no, $transction_bank_to_branch, $transction_bank_to_ifsc, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $jv_unique_id, $cheque_type, $cheque_id, $companyId);
                }
                if ($request->payment_mode == 3) { // ssb
                    $ssb_account_tran_id_from = $ssbTranCalculation;
                    $headPaymentModeRD = 3;
                    $v_no = mt_rand(0, 999999999999999);
                    $v_date = $entry_date;
                    $ssb_account_id_from = $sAccountNumber;
                    $SSBDescTran = 'Amount transfer to ' . $planDetail->name . '(' . $investmentAccountNoRd . ') ';
                    $head4rdSSB = getPlanDetailByCompany($companyId);
                    $ssbDetals = SavingAccount::where('id', $ssb_account_id_from)->first();
                    $rdDesDR = $planDetail->name . '(' . $investmentAccountNoRd . ') A/c Dr ' . $rdAmount . '/-';
                    $rdDesCR = 'To SSB(' . $ssbDetals->account_no . ') A/c Cr ' . $rdAmount . '/-';
                    $rdDes = 'Amount received for Account opening ' . $planDetail->name . '(' . $investmentAccountNoRd . ') through SSB(' . $ssbDetals->account_no . ')';
                    $rdDesMem = 'Account opening ' . $planDetail->name . '(' . $investmentAccountNoRd . ') through SSB(' . $ssbDetals->account_no . ')';
                    // ssb  head entry -
                    $allTranRDSSB = CommanTransactionsController::headTransactionCreate($refIdRD, $branchId, $bank_id, $bank_ac_id, $head4rdSSB, $typeHeadRd, $sub_typeHeadRd, $type_idRD, $associate_id_admin, $memberId, $branchId_to = NULL, $branchId_from = NULL, $rdAmount, $SSBDescTran, 'DR', $headPaymentModeRD, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $createDayBook, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $companyId);
                }
                if ($request->payment_mode == 0) {
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
                    $allTranRDcash = CommanTransactionsController::headTransactionCreate($refIdRD, $branchId, $bank_id, $bank_ac_id, $head3rdC, $typeHeadRd, $sub_typeHeadRd, $type_idRD, $associate_id_admin, $memberId, $branchId_to = NULL, $branchId_from = NULL, $rdAmount, $rdDes, 'DR', $headPaymentModeRD, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $createDayBook, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $companyId);
                }
                $head1rd = 1;
                $head2rd = 8;
                $head3rd = 20;
                $head4rd = $planAssociateSettings->deposit_head_id;
                $head5rd = 83;
                //branch day book entry +
                $daybookRd = CommanTransactionsController::NewFieldBranchDaybookCreate($refIdRD, $branchId, $typeHeadRd, $sub_typeHeadRd, $type_idRD, 1, $memberId, $branchId_to = NULL, $branchId_from = NULL, $rdAmount, $rdDes, $rdDesDR, $rdDesCR, $payment_type_rd, $headPaymentModeRD, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $jv_unique_id, $cheque_type, $cheque_id, $companyId);
                // Investment head entry +
                $allTranRD = CommanTransactionsController::headTransactionCreate($refIdRD, $branchId, $bank_id, $bank_ac_id, $head5rd, $typeHeadRd, $sub_typeHeadRd, $type_idRD, $associate_id_admin, $memberId, $branchId_to = NULL, $branchId_from = NULL, $rdAmount, $rdDes, $payment_type_rd, $headPaymentModeRD, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $createDayBook, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $companyId);
            }
            if ($memberId) {
                $dataAssociate['associate_form_no'] = $request->form_no;
                $dataAssociate['associate_status'] = 0;
                $dataAssociate['associate_branch_id'] = $branchId;
                $dataAssociate['associate_branch_code'] = $branchCode;
                //---------------- Add branch field -----------
                $dataAssociate['associate_branch_id_old'] = $branchId;
                $dataAssociate['associate_branch_code_old'] = $branchCode;
                $dataAssociate['associate_senior_code'] = $request->senior_code;
                $dataAssociate['associate_senior_id'] = $request->senior_id;
                $dataAssociate['current_carder_id'] = $request->current_carder;
                $dataAssociate['ssb_account'] = ($ssb_account == 0 || $ssb_account == NULL) ? $ssb_account_number : NULL;
                $dataAssociate['rd_account'] = !empty($request->rd_first_percentage) ? $investmentAccountNoRd : NULL;
                // $dataAssociate['company_id'] = $companyId;
                $dataAssociate['token'] = csrf_token();
                $memberDataUpdate = Member::find($customerId);
                $memberDataUpdate->update($dataAssociate);
            }
            if ($memberId && $ssb_account_number) {
                if ($rd_account) {
                    $rdset = 1;
                } else {
                    $rdset = 0;
                }
                if ($request->rd_account == 0 && $request->ssb_account == 0 && $rdset == 1) {
                    $amountArray1 = array('1' => $request->ssb_amount, '2' => $request->rd_amount);
                    $typeArray = array('1' => 1, '2' => 2);
                    $receipts_for = 4;
                    $createRecipt = CommanTransactionsController::createPaymentRecipt(0, 0, $customerId, $branchId, $branchCode, $amountArray1, $typeArray, $receipts_for, $account_no = '0');
                    $recipt_id = $createRecipt;
                    $isReceipt = 'yes';
                } elseif ($request->rd_account == 0 && $request->ssb_account == 1 && $rdset == 1) {
                    $amountArray1 = array('1' => $request->rd_amount);
                    $typeArray = array('1' => 2);
                    $receipts_for = 4;
                    $createRecipt = CommanTransactionsController::createPaymentRecipt(0, 0, $customerId, $branchId, $branchCode, $amountArray1, $typeArray, $receipts_for, $account_no = '0');
                    $recipt_id = $createRecipt;
                    $isReceipt = 'yes';
                } elseif ($request->rd_account == 1 && $request->ssb_account == 0 && $rdset == 1) {
                    $amountArray1 = array('1' => $request->ssb_amount);
                    $typeArray = array('1' => 1);
                    $receipts_for = 4;
                    $createRecipt = CommanTransactionsController::createPaymentRecipt(0, 0, $customerId, $branchId, $branchCode, $amountArray1, $typeArray, $receipts_for, $account_no = '0');
                    $recipt_id = $createRecipt;
                    $isReceipt = 'yes';
                } elseif ($request->ssb_account == 0) {
                    $amountArray1 = array('1' => $request->ssb_amount);
                    $typeArray = array('1' => 1);
                    $receipts_for = 4;
                    $createRecipt = CommanTransactionsController::createPaymentRecipt(0, 0, $customerId, $branchId, $branchCode, $amountArray1, $typeArray, $receipts_for, $account_no = '0');
                    $recipt_id = $createRecipt;
                    $isReceipt = 'yes';
                }
                $dataMsg['msg_type'] = 'success';
                $dataMsg['msg'] = 'Associate Details Updated Successfully';
                $dataMsg['reciept_generate '] = $isReceipt;
                $dataMsg['recipt_id'] = $recipt_id;
                $dataMsg['form_no'] = $request->form_no;
                $dataMsg['senior_code'] = $request->senior_code;
                $dataMsg['senior_id'] = $request->senior_id;
                $dataMsg['current_carder'] = $request->current_carder;
                $dataMsg['ssb_form_no'] = $request->ssb_form_no;
            }
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            $dataMsg['msg_type'] = 'error';
            $dataMsg['msg'] = $ex->getMessage();
            $dataMsg['line'] = $ex->getLine();
            $dataMsg['reciept_generate '] = 0;
            $dataMsg['reciept_id'] = 0;
        }
        return response()->json(compact('dataMsg'));
    }
    public function create(Request $request)
    {
        Session::put('created_at', $request->created_at);
        $globaldate = $request->created_at;
        $created_at = date("Y-m-d H:i:s", strtotime(convertDate($globaldate)));
        $updated_at = date("Y-m-d H:i:s", strtotime(convertDate($globaldate)));
        $is_primary = 0;
        $recipt_id = $request->receipt_id;
        $isReceipt = 'No';
        $investmentAccountNoRd = 0;
        $customerId = $request->customerRegisterId;
        $customer = $this->repository->getAllMember()->whereId($customerId)->whereStatus(1)->whereIsDeleted(0)->first();
        if (!empty($customer)) {
            $customerName = $customer->first_name . ' ' . $customer->last_name;
        }
        $branchId = $customer->branch_id;
        $getBranchCode = getBranchCode($branchId);
        $branchCode = $getBranchCode->branch_code;
        $associateSettings = $this->repository->getAllCompanies()->whereStatus('1')->whereHas('companyAssociate', function ($query) {
            $query->whereStatus('1')->select(['id', 'status', 'company_id']);
        })->first(['id', 'status']);
        $associatecompanyId = $associateSettings->id;
        $membercompany = $this->repository->getAllMemberCompany()->whereCustomerId($customerId)->whereCompanyId($associatecompanyId)->whereStatus(1)->whereIsDeleted(0)->first();
        $memberId = $membercompany ? $membercompany->id : NULL;
        // $planAssociateSettings = $this->repository->getAllPlans()->wherePlanCategoryCode('S')->whereStatus('1')->whereCompanyId($associatecompanyId)->first(['id', 'company_id', 'status', 'plan_category_code', 'plan_code', 'deposit_head_id']);
        // $FaCode = FaCode::whereCompanyId($associatecompanyId)->whereStatus('1')->orderBy('code', 'asc')->get(['id', 'name', 'code', 'status', 'company_id', 'slug']);
        $notInCompanyCustomer = $this->repository->getAllCompanies()->whereStatus('1')->whereDoesntHave('memberCompany', function ($query) use ($customerId) {
            $query->whereCustomerId($customerId)->select(['id', 'company_id', 'customer_id']);
        })->get();
        // $memberInvestmet = Memberinvestments::whereHas('member', function ($q) use ($customerId) {
        //     $q->whereId($customerId);
        // })->whereCompanyId($associatecompanyId)->first('id');
        // $memberInvestmetId = $memberInvestmet->id;
        // $memberInvestmentNominee = Memberinvestmentsnominees::whereHas('memberinvestments', function ($q) use ($memberInvestmetId) {
        //     $q->where('id', $memberInvestmetId);
        // })->get();
        DB::beginTransaction();
        try {
            foreach ($notInCompanyCustomer as $company) {
                $companyId = $company->id;
                $ssb_amount = ($associatecompanyId == $companyId) ? 100 : 0;
                $customerDetail = (object) [
                    'id' => $customerId,
                    'associate_code' => $customer->associate_code,
                    'associate_id' => $customer->associate_id,
                    'ssb_account' => 0,
                    'rd_account' => 0,
                    'branch_mi' => $customer->branch_mi,
                    'reinvest_old_account_number' => NULL,
                    'old_c_id' => 0,
                    'otp' => NULL,
                    'varifiy_time' => NULL,
                    'is_varified' => NULL,
                    'upi' => NULL,
                    'token' => csrf_token(),
                ];
                $customerDetailsRequest = [
                    'company_id' => $companyId,
                    'create_application_date' => $globaldate,
                    'branchid' => $branchId,
                ];
                $membercompany = Investment::registerMember($customerDetail, $customerDetailsRequest);
                $memberId = $membercompany->id;
                $ssb_account_number = $this->repository->getAllSavingAccount()->whereCustomerId($customerId)->whereCompanyId($companyId);
                if ($ssb_account_number->count() === 0) {
                    // saving accout create deafault company
                    $payment_mode = 0;
                    // getInvesment Plan id by plan code
                    $planAssociateSettings = $this->repository->getAllPlans()->wherePlanSubCategoryCode(Null)->wherePlanCategoryCode('S')->whereStatus('1')->whereCompanyId($companyId)->first();
                    $ssbFaCode = $planAssociateSettings->plan_code;
                    // $ssbFaCode = $FaCode[3]->code;
                    $ssbPlanIdGet = getPlanID($ssbFaCode);
                    $ssbPlanId = $ssbPlanIdGet->id;
                    $investmentMiCodeSsb = getInvesmentMiCodeNew($ssbPlanId, $branchId);
                    $miCodeAdd = $investmentMiCodeSsb ? ($investmentMiCodeSsb->mi_code + 1) : 1;
                    $miCode = str_pad($miCodeAdd, 5, '0', STR_PAD_LEFT);
                    // Invesment Account no
                    $investmentAccountNoSsb = $branchCode . $ssbFaCode . $miCode;
                    $dataInvest['deposite_amount'] = $ssb_amount ?? 0;
                    $dataInvest['plan_id'] = $ssbPlanId;
                    $dataInvest['form_number'] = $request->ssb_form_no_form;
                    $dataInvest['member_id'] = $memberId;
                    $dataInvest['customer_id'] = $customerId;
                    $dataInvest['branch_id'] = $branchId;
                    $dataInvest['old_branch_id'] = $branchId;
                    $dataInvest['account_number'] = $investmentAccountNoSsb;
                    $dataInvest['mi_code'] = $miCode;
                    $dataInvest['associate_id'] = 1;
                    $dataInvest['deposite_amount'] = $ssb_amount ?? 0;
                    $dataInvest['current_balance'] = $ssb_amount ?? 0;
                    $dataInvest['created_at'] = $globaldate;
                    $dataInvest['company_id'] = $companyId;
                    $res = $this->repository->CreateMemberinvestments($dataInvest);
                    $investmentId = $res->id;
                    //create savings account
                    $des = 'SSB Account Opening';
                    $amount = $ssb_amount;
                    $daybookRefssbId = CommanTransactionsController::createBranchDayBookReferenceNew($ssb_amount, $globaldate);
                    $createAccount = CommanTransactionsController::createSavingAccountDescriptionModify($memberId, $branchId, $branchCode, $amount, $payment_mode, $investmentId, $miCode, $investmentAccountNoSsb, $is_primary, $ssbFaCode, $des, $associate_id = 1, $companyId, $globaldate, $customerId, $daybookRefssbId);
                    $ssbAccountId = $createAccount['ssb_id'];
                    $ssb_account = $investmentAccountNoSsb;
                    if (isset($ssb_account) && !empty($ssb_account)) {
                        $this->repository->getAllMemberCompany()->whereId($memberId)->update(['ssb_account' => $investmentAccountNoSsb]);
                    }
                }
            }
            $allCompanyCount = $this->repository->getAllCompanies()->whereStatus('1')->count('id');
            $totalMemberInCompanyCount = $this->repository->getAllMemberCompany()->whereCustomerId($customerId)->count('id');
            if ($allCompanyCount == $totalMemberInCompanyCount) {
                $FaCode = FaCode::whereCompanyId($associatecompanyId)->whereStatus('1')->where('slug', 'associate_code')->first();
                $faCodeAssociate = $FaCode->code;
                $getMiCodeAssociate = getAssociateMiCodeNew($memberId, $branchId);
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
                // Update Associate Details
                $getmemberID = $branchCode . $faCodeAssociate . $miCodeAssociate;
                $dataAssociate['associate_form_no'] = $request->form_no;
                $dataAssociate['associate_join_date'] = date("Y-m-d", strtotime(convertDate($request->application_date)));
                $dataAssociate['associate_no'] = $getmemberID;
                $dataAssociate['is_associate'] = 1;
                $dataAssociate['associate_status'] = 1;
                $dataAssociate['associate_micode'] = $miCodeAssociate;
                $dataAssociate['associate_facode'] = $faCodeAssociate;
                $dataAssociate['associate_branch_id'] = $branchId;
                $dataAssociate['associate_branch_code'] = $branchCode;
                //---------------- Add branch field -----------
                $dataAssociate['associate_branch_id_old'] = $branchId;
                $dataAssociate['associate_branch_code_old'] = $branchCode;
                $dataAssociate['associate_senior_code'] = $request->senior_code;
                $dataAssociate['associate_senior_id'] = $request->senior_id;
                $dataAssociate['current_carder_id'] = $request->current_carder;
                // if ($request->ssb_account == 0) {
                //     $dataAssociate['ssb_account'] = $investmentAccountNoSsb;
                // }
                // if ($request->rd_account == 0) {
                //     $dataAssociate['rd_account'] = $investmentAccountNoRd;
                // }
                $dataAssociate['role_id'] = 5;
                $memberDataUpdate = Member::find($customerId);
                $memberDataUpdate->update($dataAssociate);
                // Details of Associate's dependents details Form //
                if (isset($_POST['dep_first_name']) && $_POST['dep_first_name'] != '') {
                    $associateDependent1['member_id'] = $customerId;
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
                    $associateDependent1['created_at'] = $globaldate;
                    $associateInsert1 = AssociateDependent::create($associateDependent1);
                }
                if (isset($_POST['dep_first_name1'])) {
                    if (!empty($_POST['dep_first_name1'])) {
                        foreach (($_POST['dep_first_name1']) as $key => $option) {
                            if (isset($_POST['dep_first_name1'][$key]) && $_POST['dep_first_name1'][$key] != '') {
                                $associateDependent['member_id'] = $customerId;
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
                                $associateDependent['created_at'] = $globaldate;
                                $associateInsert = AssociateDependent::create($associateDependent);
                            }
                        }
                    }
                }
                // Details of Associate's dependents From End //
                // Guarantor Details Form Start
                $associateGuarantor['member_id'] = $customerId;
                $associateGuarantor['first_name'] = $request->first_g_first_name;
                $associateGuarantor['first_mobile_no'] = $request->first_g_Mobile_no;
                $associateGuarantor['first_address'] = $request->first_g_address;
                $associateGuarantor['second_name'] = $request->second_g_first_name;
                $associateGuarantor['second_mobile_no'] = $request->second_g_Mobile_no;
                $associateGuarantor['second_address'] = $request->second_g_address;
                $associateGuarantor['created_at'] = $globaldate;
                $associateInsert = AssociateGuarantor::create($associateGuarantor);
                //=========================   associate tree start =========/
                $getParentID = AssociateTree::Where('member_id', $request->senior_id)->first();
                $associateTree['member_id'] = $customerId;
                $associateTree['parent_id'] = $getParentID->id;
                $associateTree['senior_id'] = $request->senior_id;
                $associateTree['carder'] = $request->current_carder;
                $associateTree['created_at'] = $request->created_at;
                $associateTreeInsert = AssociateTree::create($associateTree);
                // Guarantor Details Form End
                // ===========================   associate tree end ==================/
                $dataMsg['msg_type'] = 'success';
                $dataMsg['msg'] = 'Associate Registered Successfully Associate Code : ' . $getmemberID; // 'Associate created Successfully';
                $dataMsg['reciept_generate '] = $isReceipt;
                $dataMsg['reciept_id'] = $recipt_id;
            } else {
                $dataMsg['msg_type'] = 'error';
                $dataMsg['msg'] = 'Associate Not Registered With Guarantor Details !'; // 'Associate created Successfully';
                $dataMsg['reciept_generate '] = $isReceipt;
                $dataMsg['reciept_id'] = $recipt_id;
            }
            $contactNumber = array();
            $contactNumber[] = $customer->mobile_no;
            $ssbGetDetailCheck = $this->repository->getAllSavingAccount()->whereCustomerId($customer->id)->count();
            $s = $this->repository->getAllSavingAccount()->whereCustomerId($customer->id)->orderBy('company_id')->pluck('company_id')->toArray();
            $c = $this->repository->getAllCompanies()->whereStatus('1')->orderBy('id')->pluck('id')->toArray();
            // dd($c,$s);
            $nhscoc = array_diff($c, $s); // not Have Saving Account On Company
            $nhscoc = reset($nhscoc);
            if ($ssbGetDetailCheck < count($c)) {
                $MemberId = getMemberAllData($customer->id, $nhscoc)->id;
                $payment_mode = 0;
                $planAssociateSettings = $this->repository->getAllPlans()->wherePlanSubCategoryCode(Null)->wherePlanCategoryCode('S')->whereStatus('1')->whereCompanyId($nhscoc)->first();
                $ssbFaCode = $planAssociateSettings->plan_code;
                // $ssbFaCode = $FaCode[3]->code;
                $ssbPlanIdGet = getPlanID($ssbFaCode);
                $ssbPlanId = $ssbPlanIdGet->id;
                $investmentMiCodeSsb = getInvesmentMiCodeNew($ssbPlanId, $branchId);
                $miCodeAdd = $investmentMiCodeSsb ? ($investmentMiCodeSsb->mi_code + 1) : 1;
                $miCodeDefault = str_pad($miCodeAdd, 5, '0', STR_PAD_LEFT);
                $investmentAccountNoSsb = $branchCode . $ssbFaCode . $miCodeDefault;
                $dataInvest = [
                    'deposite_amount' => $ssb_amount ?? 0,
                    'plan_id' => $ssbPlanId,
                    'form_number' => $request->ssb_form_no_form,
                    'member_id' => $MemberId,
                    'customer_id' => $customer->id,
                    'branch_id' => $branchId,
                    'old_branch_id' => $branchId,
                    'account_number' => $investmentAccountNoSsb,
                    'mi_code' => $miCodeDefault,
                    'associate_id' => 1,
                    'current_balance' => $ssb_amount ?? 0,
                    'created_at' => $globaldate,
                    'company_id' => $nhscoc,
                ];
                $res = $this->repository->CreateMemberinvestments($dataInvest);
                $investmentIddefault = $res->id;
                //create savings account
                $des = 'SSB Account Opening';
                $amount = $ssb_amount ?? 0;
                $daybookRefssbIdssbdefault = CommanTransactionsController::createBranchDayBookReferenceNew($ssb_amount ?? 0, $globaldate);
                $createAccount = CommanTransactionsController::createSavingAccountDescriptionModify($MemberId, $branchId, $branchCode, $amount, $payment_mode, $investmentIddefault, $miCodeDefault, $investmentAccountNoSsb, $is_primary, $ssbFaCode, $des, $associate_id = 1, $nhscoc, $globaldate, $customerId, $daybookRefssbIdssbdefault);
            }
            // $ssbGetDetail = $this->repository->getAllSavingAccount()->whereCustomerId($customer->id)->whereCompanyId($associatecompanyId)->first();
            $ssbGetDetail = $this->repository->getAllSavingAccount()->whereCustomerId($customer->id)->whereCompanyId('1')->first();
            $invGetDetail = Memberinvestments::whereAccountNumber($ssbGetDetail->account_no)->first();

            if (isset($customer->ssb_account)) {
                // if ($ssbGetDetail && $invGetDetail) {
                if (isset($customer->ssb_account) && isset($customer->rd_accoun)) {
                    $text = 'Dear Member, Thank You for Choosing S.B. Micro Finance association. Your Associate Code ' . $dataAssociate['associate_no'] . ', Saving A/C ' . $ssbGetDetail->account_no . ' is Created on ' . $ssbGetDetail->created_at->format('d M Y') . ' with Rs. ' . round($ssb_amount, 2) . ' CR, Recurring A/c No. ' . $customer->ssb_account . ' is Created on ' . $invGetDetail->created_at->format('d M Y') . ' with Rs. ' . round($invGetDetail->deposite_amount, 2) . ' CR. Have a good day';
                } elseif ($ssbGetDetail) {
                    $link = "https://play.google.com/store/apps/details?id=com.associate.sbmfa";
                    $associateNo = $dataAssociate['associate_no'];
                    $sAccount = $ssbGetDetail->account_no;
                    $sAccountAmount = round($invGetDetail->deposite_amount, 2);
                    $cDate = $ssbGetDetail->created_at->format('d M Y');
                    $text = "Dear Member, Thank You for Choosing S.B. Micro Finance association. Your Associate Code $associateNo, Saving A/C $sAccount is Created on $cDate with Rs. $sAccountAmount Cr. Have a good day $link";
                    // $text = 'Dear Member, Thank You for Choosing S.B. Micro Finance association. Your Associate Code ' . $dataAssociate['associate_no'] . ', Saving A/C ' . $ssbGetDetail->account_no . ' is Created on ' . $ssbGetDetail->created_at->format('d M Y') . '. Have a good day';
                }
                $templateId = 1201160311561236445;
                $sendToMember = new Sms();
                $sendToMember->sendSms($contactNumber, $text, $templateId);
            }
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            $dataMsg['msg_type'] = 'error';
            $dataMsg['msg'] = $ex->getMessage();
            $dataMsg['line'] = $ex->getLine();
            $dataMsg['file'] = $ex->getFile();
        }
        return json_encode($dataMsg);
    }
    // those function have made for one time runnig perpose to create member in member_company table with ssb account 
    // if after runnig this code route will be removed by Sourab Biswas
    // if not fount Route in Web.php file please add before use bellow route code tot use item and remove die();
    /*
    Route::get('associate/registration/customer/allCustomer', 'Admin\AssociateController@allCustomer')->name('admin.associate.dependents.allCustomer');
    Route::get('associate/registration/customer/allssb', 'Admin\AssociateController@allssb')->name('admin.associate.dependents.allssb');
    */
    public function allCustomer()
    {
        die();
        DB::beginTransaction();
        try {
            $branchId = $customer->branch_id;
            $getBranch = getBranchDetail($branchId);
            $branchCode = $getBranch->branch_code;
            $state_id = $getBranch->state_id;
            $currentDate = checkMonthAvailability(date('d'), date('m'), date('Y'), $state_id);
            $globaldate = date('Y-m-d', strtotime($currentDate));
            $is_primary = 0;
            $associateSettings = $this->repository->getAllCompanies()
                ->whereStatus('1')
                ->whereHas('companyAssociate', function ($query) {
                    $query->whereStatus('1')
                        ->select(['id', 'status', 'company_id']);
                })
                ->first(['id', 'status']);
            $associatecompanyId = $associateSettings->id;
            $allCustomer = $this->repository->getAllMember()
                ->where('branch_id', '!=', 0)
                ->whereIsAssociate(1)
                ->get(['id', 'branch_id', 'associate_code', 'associate_id', 'branch_mi', 'branch_code'])
            ;
            $start = 0;
            $limit = 3700;
            $getAllcustomercount = $allCustomer->count('id');
            $sno = 0;
            // $allCustomer->chunk($getAllcustomercount)->each(function ($customer) use ($associatecompanyId,$globaldate,$is_primary) {
            foreach ($allCustomer->slice($start, $limit) as $customer) {
                $sno++;
                $customerId = $customer->id;

                $membercompany = $this->repository->getAllMemberCompany()
                    ->whereCustomerId($customerId)
                    ->whereCompanyId($associatecompanyId)
                    ->whereStatus(1)
                    ->whereIsDeleted(0)
                    ->first();
                $memberId = $membercompany ? $membercompany->id : null;
                p('member id - ' . ' ' . $memberId);
                p('customer id - ' . ' ' . $customerId);
                p('sno  - ' . ' ' . $sno);
                $notInCompanyCustomer = $this->repository->getAllCompanies()
                    ->whereStatus('1')
                    ->whereDoesntHave('memberCompany', function ($query) use ($customerId) {
                        $query->whereCustomerId($customerId)
                            ->select(['id', 'customer_id', 'company_id', 'member_id',]);
                    })
                    ->get(['id', 'status']);
                if (!$notInCompanyCustomer->isEmpty()) {
                    foreach ($notInCompanyCustomer as $company) {
                        $companyId = $company->id;
                        // p('company id', $companyId);
                        $ssb_amount = ($associatecompanyId == $companyId) ? 100 : 0;
                        $customerDetail = (object) [
                            'id' => $customerId,
                            'associate_code' => $customer->associate_code,
                            'associate_id' => $customer->associate_id,
                            'ssb_account' => 0,
                            'rd_account' => 0,
                            'branch_mi' => $customer->branch_mi,
                            'reinvest_old_account_number' => null,
                            'old_c_id' => 0,
                            'otp' => null,
                            'varifiy_time' => null,
                            'is_varified' => null,
                            'upi' => null,
                        ];
                        $customerDetailsRequest = [
                            'company_id' => $companyId,
                            'create_application_date' => $globaldate,
                            'branchid' => $branchId,
                        ];
                        $membercompany = Investment::registerMember($customerDetail, $customerDetailsRequest);
                        $memberId = $membercompany->id;
                        p($memberId . ' member Company Id');
                        $ssb_account_number = $this->repository->getAllSavingAccount()
                            ->whereCustomerId($customerId)
                            ->whereCompanyId($companyId);
                        if ($ssb_account_number->count() == 0) {
                            // Saving account create default company
                            $payment_mode = 0;
                            $planAssociateSettings = $this->repository->getAllPlans()
                                ->wherePlanSubCategoryCode(null)
                                ->wherePlanCategoryCode('S')
                                ->whereStatus('1')
                                ->whereCompanyId($companyId)
                                ->first(['id', 'company_id', 'status', 'plan_category_code', 'plan_code', 'deposit_head_id', 'plan_sub_category_code']);
                            $ssbFaCode = $planAssociateSettings->plan_code;
                            $ssbPlanIdGet = getPlanID($ssbFaCode);
                            $ssbPlanId = $ssbPlanIdGet->id;
                            $investmentMiCodeSsb = getInvesmentMiCodeNew($ssbPlanId, $branchId);
                            $miCodeAdd = $investmentMiCodeSsb ? ($investmentMiCodeSsb->mi_code + 1) : 1;
                            $miCode = str_pad($miCodeAdd, 5, '0', STR_PAD_LEFT);
                            $investmentAccountNoSsb = $branchCode . $ssbFaCode . $miCode;
                            $dataInvest['deposite_amount'] = $ssb_amount ?? 0;
                            $dataInvest['plan_id'] = $ssbPlanId;
                            $dataInvest['form_number'] = null;
                            $dataInvest['member_id'] = $memberId;
                            $dataInvest['customer_id'] = $customerId;
                            $dataInvest['branch_id'] = $branchId;
                            $dataInvest['old_branch_id'] = $branchId;
                            $dataInvest['account_number'] = $investmentAccountNoSsb;
                            $dataInvest['mi_code'] = $miCode;
                            $dataInvest['associate_id'] = 1;
                            $dataInvest['deposite_amount'] = $ssb_amount ?? 0;
                            $dataInvest['current_balance'] = $ssb_amount ?? 0;
                            $dataInvest['created_at'] = $globaldate;
                            $dataInvest['company_id'] = $companyId;
                            $res = $this->repository->CreateMemberinvestments($dataInvest);
                            $investmentId = $res->id;
                            // Create savings account
                            $des = 'SSB Account Opening';
                            $amount = $ssb_amount;
                            $daybookRefssbId = CommanTransactionsController::createBranchDayBookReferenceNew($ssb_amount, $globaldate);
                            $createAccount = CommanTransactionsController::createSavingAccountDescriptionModify($memberId, $branchId, $branchCode, $amount, $payment_mode, $investmentId, $miCode, $investmentAccountNoSsb, $is_primary, $ssbFaCode, $des, $associate_id = 1, $companyId, $globaldate, $customerId, $daybookRefssbId);
                            $ssbAccountId = $createAccount['ssb_id'];
                            $ssb_account = $investmentAccountNoSsb;
                            p('ssb id - ' . $ssbAccountId);
                            if (isset($ssb_account) && !empty($ssb_account)) {
                                $this->repository->getAllMemberCompany()
                                    ->whereId($memberId)
                                    ->update(['ssb_account' => $investmentAccountNoSsb]);
                            }
                        }
                    }
                } else {
                    p('memmber Already in member company' . ' ' . $customerId);
                }
            }
            ;
            DB::commit();
        } catch (\Exception $ex) {
            p($ex->getMessage());
            DB::rollback();
        }
    }
    public function allssb()
    {
        die();
        $is_primary = 0;
        $allCustomer = $this->repository->getAllMember()
            ->where('branch_id', '!=', 0)
            ->whereIsAssociate(1)
            ->get(['id', 'branch_id', 'associate_code', 'associate_id', 'branch_mi', 'branch_code'])
        ;
        $getAllcustomercount = $allCustomer->count('id');
        // $allCustomer->chunk($getAllcustomercount)->each(function ($customer) use ($globaldate,$is_primary) {
        foreach ($allCustomer as $customer) {
            $customerId = $customer->id;
            $branchId = $customer->branch_id;
            $getBranch = getBranchDetail($branchId);
            $branchCode = $getBranch->branch_code;
            $state_id = $getBranch->state_id;
            $currentDate = checkMonthAvailability(date('d'), date('m'), date('Y'), $state_id);
            $globaldate = date('Y-m-d', strtotime($currentDate));
            $membercompany = $this->repository->getAllMemberCompany()
                ->whereCustomerId($customerId)
                ->whereStatus(1)
                // ->whereHas('ssb_detail')
                ->whereDoesntHave('ssb_detail')
                ->whereIsDeleted(0)
                ->get();
            $memberCount = $membercompany->count('id');
            // $membercompany->chunk($memberCount)->each(function($member)use($customerId,$branchCode,$branchId){
            foreach ($membercompany as $membercom) {
                $membercomId = $membercom->id;
                $companyId = $membercom->company_id;
                p('member company id' . $membercomId);
                $payment_mode = 0;
                $planAssociateSettings = $this->repository->getAllPlans()
                    ->wherePlanSubCategoryCode(null)
                    ->wherePlanCategoryCode('S')
                    ->whereStatus('1')
                    ->whereCompanyId($companyId)
                    ->first(['id', 'company_id', 'status', 'plan_category_code', 'plan_code', 'deposit_head_id', 'plan_sub_category_code']);
                $ssbFaCode = $planAssociateSettings->plan_code;
                $ssbPlanIdGet = getPlanID($ssbFaCode);
                $ssbPlanId = $ssbPlanIdGet->id;
                $investmentMiCodeSsb = getInvesmentMiCodeNew($ssbPlanId, $branchId);
                $miCodeAdd = $investmentMiCodeSsb ? ($investmentMiCodeSsb->mi_code + 1) : 1;
                $miCode = str_pad($miCodeAdd, 5, '0', STR_PAD_LEFT);
                $investmentAccountNoSsb = $branchCode . $ssbFaCode . $miCode;
                $dataInvest['deposite_amount'] = 0;
                $dataInvest['plan_id'] = $ssbPlanId;
                $dataInvest['form_number'] = null;
                $dataInvest['member_id'] = $membercomId;
                $dataInvest['customer_id'] = $customerId;
                $dataInvest['branch_id'] = $branchId;
                $dataInvest['old_branch_id'] = $branchId;
                $dataInvest['account_number'] = $investmentAccountNoSsb;
                $dataInvest['mi_code'] = $miCode;
                $dataInvest['associate_id'] = 1;
                $dataInvest['deposite_amount'] = 0;
                $dataInvest['current_balance'] = 0;
                $dataInvest['created_at'] = $globaldate;
                $dataInvest['company_id'] = $companyId;
                $res = $this->repository->CreateMemberinvestments($dataInvest);
                $investmentId = $res->id;
                // Create savings account
                $des = 'SSB Account Opening';
                $amount = 0;
                $daybookRefssbId = CommanTransactionsController::createBranchDayBookReferenceNew(0, $globaldate);
                $createAccount = CommanTransactionsController::createSavingAccountDescriptionModify($membercomId, $branchId, $branchCode, $amount, $payment_mode, $investmentId, $miCode, $investmentAccountNoSsb, $is_primary, $ssbFaCode, $des, $associate_id = 1, $companyId, $globaldate, $customerId, $daybookRefssbId);
                $ssbAccountId = $createAccount['ssb_id'];
                $ssb_account = $investmentAccountNoSsb;
                p('ssb id - ' . $ssbAccountId);
                if (isset($ssb_account) && !empty($ssb_account)) {
                    $this->repository->getAllMemberCompany()
                        ->whereId($membercomId)
                        ->update(['ssb_account' => $investmentAccountNoSsb]);
                }
            }
            ;
        }
        ;
    }
    public function newAssociateCommissionList()
    {
        if (check_my_permission(Auth::user()->id, "319") != "1") {
            return redirect()
                ->route('admin.dashboard')
                ->with('alert', "you do not  have permission");
        }

        $data['title'] = 'Commission Ledger Detail Company Wise';
        $data['companies'] = \App\Models\Companies::where('status', 1)->where('delete', '0')->get(['id', 'name']);
        $data['detailMonths'] = \App\Models\CommissionLeaserMonthly::select('id', 'month', 'year', 'is_deleted', 'company_id')->where('is_deleted', 0)->distinct('month')->get();
        return view('templates.admin.associate.associate_commision_company', $data);
    }
    public function newAssociateCommissionDetailList(Request $request)
    {
        if ($request->ajax()) {
            $arrFormData = array();
            if (!empty($_POST['searchform'])) {
                foreach ($_POST['searchform'] as $frm_data) {
                    $arrFormData[$frm_data['name']] = $frm_data['value'];
                }
            }
            $data = \App\Models\CommissionLeaserDetailMonthly::select('id', 'member_id', 'amount', 'status', 'created_at', 'total_tds', 'fuel', 'collection', 'amount_tds', 'commission_leaser_id', 'company_id')
                ->with([
                    'member' => function ($q) {
                        $q->select('id', 'member_id', 'first_name', 'last_name', 'current_carder_id', 'associate_no')->with([
                            'getCarderNameCustom' => function ($q) {
                                $q->select('id', 'name');
                            },
                            'memberIdProof' => function ($q) {
                                $q->select('id', 'first_id_no', 'member_id');
                            }
                        ]);
                    },
                    'SavingAcount' => function ($q) {
                        $q->select('id', 'member_id', 'account_no');
                    }
                ])
                ->with([
                    'commissionLeaser' => function ($q) {
                        $q->select('id', 'month', 'year');
                    }
                ])
                ->with('company:id,name');
            if (isset($arrFormData['associate_code']) && $arrFormData['associate_code'] != '') {
                $meid = $arrFormData['associate_code'];
                $data = $data->whereHas('member', function ($query) use ($meid) {
                    $query->where('members.associate_no', $meid);
                });
            }
            if (isset($arrFormData['company_id']) && $arrFormData['company_id'] > 0) {
                $companyId = $arrFormData['company_id'];
                $data->where('company_id', $companyId);
            }
            if (isset($arrFormData['company_id']) && $arrFormData['company_id'] == 0) {
                $month = $arrFormData['month'];
                $year = $arrFormData['year'];
                $data = $data->whereHas('commissionLeaser', function ($query) use ($month, $year) {
                    $query->where('commission_leaser_monthly.month', $month);
                    $query->where('commission_leaser_monthly.year', $year);
                });
            } else {
                if (isset($arrFormData['ledger_month']) && $arrFormData['ledger_month'] > 0) {
                    $ledger_month = $arrFormData['ledger_month'];
                    $data->where('commission_leaser_id', $ledger_month);
                }
            }
            $count = $data->count('id');
            $data = $data->orderby('id', 'DESC')->offset($_POST['start'])->limit($_POST['length'])->get();
            $totalCount = $count;
            $sno = $_POST['start'];
            $rowReturn = array();
            foreach ($data as $row) {
                $sno++;
                $val['DT_RowIndex'] = $sno;
                if (isset($row['commissionLeaser']->id)) {
                    $val['ledgerMonth'] = getMonthName($row['commissionLeaser']->month) . " " . $row['commissionLeaser']->year;
                } else {
                    $val['ledgerMonth'] = "N/A";
                }
                if (isset($row['member']->associate_no)) {
                    $val['code'] = $row['member']->associate_no;
                } else {
                    $val['code'] = 'N/A';
                }
                if (isset($row['company'])) {
                    $val['companyName'] = $row['company']->name;
                } else {
                    $val['companyName'] = 'N/A';
                }
                if (isset($row['member']['getCarderNameCustom'])) {
                    // $val['carder'] = $row['member']['getCarderNameCustom']->name;
                    $val['carder'] = getCarderName(getSeniorData($row->member_id, 'current_carder_id'));
                } else {
                    $val['carder'] = 'N/A';
                }
                if (isset($row['member'])) {
                    $val['name'] = $row['member']->first_name . ' ' . $row['member']->last_name;
                } else {
                    $val['name'] = 'N/A';
                }
                if (isset($row->amount)) {
                    $val['total'] = number_format((float) $row->amount, 2, '.', '');
                } else {
                    $val['total'] = 'N/A';
                }
                if (isset($row->member_id)) {
                    $val['account'] = isset($row['SavingAcount']->account_no) ? $row['SavingAcount']->account_no : "N/A";
                } else {
                    $val['account'] = 'N/A';
                }
                if (isset($row->status)) {
                    if ($row->status == 1) {
                        $status = 'Transferred';
                    } else if ($row->status == 2) {
                        $status = 'Partial Transfer';
                    } else if ($row->status == 3) {
                        $status = 'Pending';
                    } else {
                        $status = 'Deleted';
                    }
                } else {
                    $status = 'Deleted';
                }
                $val['status'] = $status;
                if (isset($row->created_at)) {
                    $val['created'] = date("d/m/Y H:i:s a", strtotime($row->created_at));
                } else {
                    $val['created'] = 'N/A';
                }
                if (isset($row->member_id)) {
                    $val['pan'] = $row['member']['memberIdProof']->first_id_no;
                } else {
                    $val['pan'] = 'N/A';
                }
                $val['tds'] = number_format((float) $row->total_tds, 2, '.', '');
                $val['fuel'] = number_format((float) $row->fuel, 2, '.', '');
                $val['collection'] = number_format((float) $row->collection, 2, '.', '');
                $val['amount_tds'] = number_format((float) $row->amount_tds, 2, '.', '');
                $btn = '<div class="list-icons"><div class="dropdown"><a href="#" class="list-icons-item" data-toggle="dropdown"><i class="icon-menu9"></i></a><div class="dropdown-menu dropdown-menu-right">';
                // $url = URL::to("admin/associate-commission-detail/" . $row->member_id . "?year=" . $row['commissionLeaser']->year . "&month=" . $row['commissionLeaser']->month . "");
                // $url1 = URL::to("admin/associate/loan-commission-detail/" . $row->member_id . "?year=" . $row['commissionLeaser']->year . "&month=" . $row['commissionLeaser']->month . "");
                // $btn .= '<a class="dropdown-item" href="' . $url . '" title="Investment Commission Detail"><i class="icon-eye-blocked2  mr-2"></i>Investment Commission Detail </a>  ';
                // $btn .= '<a class="dropdown-item" href="' . $url1 . '" title="Loan Commission Detail"><i class="icon-eye-blocked2  mr-2"></i>Loan Commission Detail</a>  ';
                $btn .= '</div></div></div>';
                $val['action'] = $btn;
                $rowReturn[] = $val;
            }
        }
        $output = array("draw" => $_POST['draw'], "recordsTotal" => $totalCount, "recordsFiltered" => $count, "data" => $rowReturn);
        return json_encode($output);
    }
    public function companyComissionDetailExport(Request $request)
    {
        if ($request['companyComission_export'] == 0) {
            $input = $request->all();
            $start = $input["start"];
            $limit = $input["limit"];
            $returnURL = URL::to('/') . "/asset/commission_ledger_detail_company_wise.csv";
            $fileName = env('APP_EXPORTURL') . "asset/commission_ledger_detail_company_wise.csv";
            global $wpdb;
            $postCols = array(
                'post_title',
                'post_content',
                'post_excerpt',
                'post_name',
            );
            header("Content-type: text/csv");
        }
        $data = \App\Models\CommissionLeaserDetailMonthly::select('id', 'member_id', 'amount', 'status', 'created_at', 'total_tds', 'fuel', 'collection', 'amount_tds', 'commission_leaser_id', 'company_id')
            ->with([
                'member' => function ($q) {
                    $q->select('id', 'member_id', 'first_name', 'last_name', 'current_carder_id', 'associate_no')->with([
                        'getCarderNameCustom' => function ($q) {
                            $q->select('id', 'name');
                        },
                        'memberIdProof' => function ($q) {
                            $q->select('id', 'first_id_no', 'member_id');
                        }
                    ]);
                },
                'SavingAcount' => function ($q) {
                    $q->select('id', 'member_id', 'account_no');
                }
            ])
            ->with([
                'commissionLeaser' => function ($q) {
                    $q->select('id', 'month', 'year');
                }
            ])
            ->with('company:id,name');
        if (isset($request['associate_code']) && $request['associate_code'] != '') {
            $meid = $request['associate_code'];
            $data = $data->whereHas('member', function ($query) use ($meid) {
                $query->where('members.associate_no', $meid);
            });
        }
        if (isset($request['company_id']) && $request['company_id'] > 0) {
            $company_id = $request['company_id'];
            $data = $data->where('company_id', $company_id);
        }
        if (isset($request['company_id']) && $request['company_id'] == 0) {
            $month = $request['month'];
            $year = $request['year'];
            $data = $data->whereHas('commissionLeaser', function ($query) use ($month, $year) {
                $query->where('commission_leaser_monthly.month', $month);
                $query->where('commission_leaser_monthly.year', $year);
            });
        } else {
            if (isset($request['ledger_month']) && $request['ledger_month'] > 0) {
                $ledger_month = $request['ledger_month'];
                $data->where('commission_leaser_id', $ledger_month);
            }
        }
        if ($request['companyComission_export'] == 0) {
            $totalResults = $data->orderby('id', 'DESC')->count();
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
                $sno++;
                $val['S/N'] = $sno;
                $val['Company Name'] = $row['company']->name;
                if (isset($row['commissionLeaser']->id)) {
                    $val['Ledger Month'] = getMonthName($row['commissionLeaser']->month) . " " . $row['commissionLeaser']->year;
                } else {
                    $val['Ledger Month'] = "N/A";
                }
                $val['ASSOCIATE CODE'] = getSeniorData($row->member_id, 'associate_no');
                $val['ASSOCIATE NAME'] = getSeniorData($row->member_id, 'first_name') . ' ' . getSeniorData($row->member_id, 'last_name');
                $val['ASSOCIATE CARDER'] = getCarderName(getSeniorData($row->member_id, 'current_carder_id'));
                $val['PAN NO'] = get_member_id_proof($row->member_id, 5);
                $val['TOTAL AMOUNT'] = number_format((float) $row->amount_tds, 2, '.', '');
                $val['TDS AMOUNT'] = number_format((float) $row->total_tds, 2, '.', '');
                $val['FINAL PAYABLE AMOUNT'] = number_format((float) $row->amount, 2, '.', '');
                $val['TOTAL COLLECTION'] = number_format((float) $row->collection, 2, '.', '');
                $val['FUEL AMOUNT'] = number_format((float) $row->fuel, 2, '.', '');
                $ssbAccountDetail = getMemberSsbAccountDetail($row->member_id);
                $val['SSB ACCOUNT NO'] = isset($ssbAccountDetail->account_no) ? $ssbAccountDetail->account_no : "N/A";
                $status = '';
                if ($row->status == 1) {
                    $status = 'Transferred';
                } else if ($row->status == 0) {
                    $status = 'Deleted';
                } else {
                    $status = 'Pending';
                }
                $val['STATUS'] = $status;
                $val['CREATED'] = date("d/m/Y H:i:s a", strtotime($row->created_at));
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
    public function defact_code(Request $request, $customerId)
    {
        DB::beginTransaction();
        try {
            $this->repository->getAllCompanies()->whereStatus('1')->each(function ($value) use ($customerId) {
                $companyId = $value->id;
                $is_primary = 0;
                $payment_mode = 0;
                $associate_id = 1;
                $customer = $this->repository->getAllMember()->whereId($customerId)->whereStatus(1)->whereIsDeleted(0)->first();
                $branchId = $customer->branch_id;
                $getBranchCode = getBranchCode($branchId);
                $branchCode = $getBranchCode->branch_code;
                $stateId = (Branch::whereId($branchId)->value('state_id'));
                $globalDate = headerMonthAvailability(date('d'), date('m'), date('Y'), $stateId);
                $globalDate = date('Y-m-d', strtotime(convertDate(($globalDate))));
                Session::put('created_at', $globalDate);
                $branchCode = $customer->branch_code;
                $s = $this->repository->getAllSavingAccount()->whereCustomerId($customerId)->whereIsDeleted(0)->whereStatus('1')->where('company_id', '!=', $companyId)->pluck('company_id')->toArray();
                $c = $this->repository->getAllCompanies()->whereStatus('1')->orderBy('id')->pluck('id')->toArray();
                $nhscoc = array_diff($c, $s); // not Have Saving Account On Company
                $nhscoc = reset($nhscoc);  // Get the first value from the array
                $ssbGetDetails = $this->repository->getAllSavingAccount()->whereCustomerId($customerId)->whereCompanyId($companyId)->whereStatus('1')->whereIsDeleted('0')->first();
                $ssbGetDetailCheck = $this->repository->getAllSavingAccount()->whereCustomerId($customerId)->whereCompanyId($nhscoc)->whereStatus('1')->whereIsDeleted('0')->count();
                if ($ssbGetDetailCheck == 0) {
                    // if($nhscoc === 1){
                    //     $amount = 500;
                    //     $dayBookRefId = CommanTransactionsController::createBranchDayBookReferenceNew($amount, $globalDate);
                    // }else{
                    $amount = 0;
                    $dayBookRefId = NULL;
                    // }
                    $MemberId = getMemberAllData($customerId, $nhscoc)->id;
                    $planAssociateSettings = $this->repository->getAllPlans()->wherePlanSubCategoryCode(Null)->wherePlanCategoryCode('S')->whereStatus('1')->whereCompanyId($nhscoc)->first();
                    $ssbPlanId = $planAssociateSettings->id;
                    $investmentGetDetailCheck = $this->repository->getAllMemberinvestments()->wherePlanId($ssbPlanId)->whereCustomerId($customerId)->whereCompanyId($nhscoc)->whereMemberId($MemberId)->get()->toArray();
                    $ssbFaCode = $planAssociateSettings->plan_code;
                    $investmentMiCodeSsb = getInvesmentMiCodeNew($ssbPlanId, $branchId);
                    $miCodeAdd = $investmentMiCodeSsb ? ($investmentMiCodeSsb->mi_code + 1) : 1;
                    $miCodeDefault = str_pad($miCodeAdd, 5, '0', STR_PAD_LEFT);
                    if (empty ($investmentGetDetailCheck)) {
                        $investmentAccountNoSsb = $branchCode . $ssbFaCode . $miCodeDefault;
                    } else {
                        $investmentAccountNoSsb = $investmentGetDetailCheck[0]['account_number'];
                    }
                    $dataInvest = [
                        'deposite_amount' => 0,
                        'plan_id' => $ssbPlanId,
                        'form_number' => $ssbGetDetails->form_number ?? NULL,
                        'member_id' => $MemberId,
                        'customer_id' => $customerId,
                        'branch_id' => $branchId,
                        'old_branch_id' => $branchId,
                        'account_number' => $investmentAccountNoSsb,
                        'mi_code' => $miCodeDefault,
                        'associate_id' => $associate_id,
                        'current_balance' => $amount,
                        'created_at' => $globalDate,
                        'company_id' => $nhscoc,
                    ];
                    if (empty ($investmentGetDetailCheck)) {
                        $res = $this->repository->CreateMemberinvestments($dataInvest);
                        $investmentIddefault = $res->id;
                    } else {
                        $investmentIddefault = $investmentGetDetailCheck[0]['id'];
                    }
                    $des = 'SSB Account Opening';
                    $createAccount = CommanTransactionsController::createSavingAccountDescriptionModify($MemberId, $branchId, $branchCode, $amount, $payment_mode, $investmentIddefault, $miCodeDefault, $investmentAccountNoSsb, $is_primary, $ssbFaCode, $des, $associate_id, $nhscoc, $globalDate, $customerId, $dayBookRefId);
                } else {
                    p('no need to call for company');
                }
            });
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            p($ex->getMessage());
            p($ex->getLine());
        }
        dd('Done');
    }
     // restricted url
    //  Route::get('createAssociatSSB/{associate_no}/{associate_join_date}', 'Admin\AssociateController@createAssociatSSB');

    public function createAssociatSSB($associate_no, $associate_join_date)
    {
        try {
            DB::beginTransaction();
            // $globaldate =  date('Y-m-d'." " . date('H:i:s') . "",strtotime(convertdate($associate_join_date)));
            $globaldate =  '2024-04-10 00:00:00';
            $is_primary = 0;
            $allCustomer = $this->repository->getAllMember()
                ->where('branch_id', '!=', 0)
                ->whereIsAssociate(1)
                ->where('associate_no', $associate_no)
                ->get(['id', 'branch_id', 'associate_code', 'associate_id', 'branch_mi', 'branch_code']);
            foreach ($allCustomer as $customer) {
                $customerId = $customer->id;
                $branchId = $customer->branch_id;
                $getBranchCode = getBranchCode($branchId);
                $branchCode = $getBranchCode->branch_code;
                $com = $this->getCompanyDetailsByMemberId($customerId);
                if (!empty($com)) {
                    foreach ($com as $k => $v) {
                        $companyId = $v;
                        $membercompany = $this->repository->getAllMemberCompany()
                            ->whereCustomerId($customerId)
                            ->whereStatus(1)
                            ->whereIsDeleted(0)
                            ->whereCompanyId($companyId)
                            ->exists();
                        if (!$membercompany) {
                            $customerDetail = (object) [
                                'id' => $customerId,
                                'associate_code' => $customer->associate_code,
                                'associate_id' => $customer->associate_id,
                                'ssb_account' => 0,
                                'rd_account' => 0,
                                'branch_mi' => $customer->branch_mi,
                                'reinvest_old_account_number' => NULL,
                                'old_c_id' => 0,
                                'otp' => NULL,
                                'varifiy_time' => NULL,
                                'is_varified' => NULL,
                                'upi' => NULL,
                                'token' => csrf_token(),
                            ];
                            $customerDetailsRequest = [
                                'company_id' => $companyId,
                                'create_application_date' => $globaldate,
                                'branchid' => $branchId,
                            ];
                            $membercompany = Investment::registerMember($customerDetail, $customerDetailsRequest);
                        }
                    }
                }
                $memberCompany = $this->repository->getAllMemberCompany()
                    ->whereCustomerId($customerId)
                    ->whereStatus(1)
                    ->whereDoesntHave('ssb_detail')
                    ->whereIsDeleted(0)
                    ->get();                
                if ($memberCompany->count('id') == 3) {
                    dd('all companies no data found');
                }
                // dd($memberCompany->toArray());
                foreach ($memberCompany as $membercom) {
                    $membercomId = $membercom->id;
                    $cyId = $membercom->company_id;
                    p("member company id - $membercomId , company - id  - $cyId");
                    $payment_mode = 0;
                    $planAssociateSettings = $this->repository->getAllPlans()
                        ->wherePlanSubCategoryCode(null)
                        ->wherePlanCategoryCode('S')
                        ->whereStatus('1')
                        ->whereCompanyId($cyId)
                        ->first(['id', 'company_id', 'status', 'plan_category_code', 'plan_code', 'deposit_head_id', 'plan_sub_category_code']);

                    $ssbFaCode = $planAssociateSettings->plan_code;
                    $ssbPlanIdGet = getPlanID($ssbFaCode);
                    $ssbPlanId = $ssbPlanIdGet->id;
                    $investmentMiCodeSsb = getInvesmentMiCodeNew($ssbPlanId, $branchId);
                    $miCodeAdd = $investmentMiCodeSsb ? ($investmentMiCodeSsb->mi_code + 1) : 1;
                    $miCode = str_pad($miCodeAdd, 5, '0', STR_PAD_LEFT);
                    $investmentAccountNoSsb = $branchCode . $ssbFaCode . $miCode;
                    $dataInvest['deposite_amount'] = 0;
                    $dataInvest['plan_id'] = $ssbPlanId;
                    $dataInvest['form_number'] = null;
                    $dataInvest['member_id'] = $membercomId;
                    $dataInvest['customer_id'] = $customerId;
                    $dataInvest['branch_id'] = $branchId;
                    $dataInvest['old_branch_id'] = $branchId;
                    $dataInvest['account_number'] = $investmentAccountNoSsb;
                    $dataInvest['mi_code'] = $miCode;
                    $dataInvest['associate_id'] = 1;
                    $dataInvest['deposite_amount'] = 0;
                    $dataInvest['current_balance'] = 0;
                    $dataInvest['created_at'] = $globaldate;
                    $dataInvest['company_id'] = $cyId;

                    $getInvestmentRow = $this->repository->getAllMemberinvestments()->wherePlanId($ssbPlanId)->whereCompanyId($cyId)->whereMemberId($membercomId)->whereCustomerId($customerId)->orderBy('created_at');
                    if (!$getInvestmentRow->exists()) {
                        $res = $this->repository->CreateMemberinvestments($dataInvest);
                        $investmentId = $res->id;
                    } else {
                        $investmentId = $getInvestmentRow->value('id');
                    }

                    $getSavingAccountExists = $this->repository->getAllSavingAccount()->whereMemberInvestmentsId($investmentId)->whereCustomerId($customerId)->whereCompanyId($cyId);

                    $des = 'SSB Account Opening';
                    $amount = 0;

                    if (!$getSavingAccountExists->exists()) {
                        $daybookRefssbId = CommanTransactionsController::createBranchDayBookReferenceNew(0, $globaldate);
                        $createAccount = CommanTransactionsController::createSavingAccountDescriptionModify($membercomId, $branchId, $branchCode, $amount, $payment_mode, $investmentId, $miCode, $investmentAccountNoSsb, $is_primary, $ssbFaCode, $des, $associate_id = 1, $cyId, $globaldate, $customerId, $daybookRefssbId);
                    } else {
                        $createAccount['ssb_id'] = $getSavingAccountExists->value('id');
                    }
                    $ssbAccountId = $createAccount['ssb_id'];
                    $ssb_account = $investmentAccountNoSsb;
                    p('ssb id - ' . $ssbAccountId);

                    if (isset($ssb_account) && !empty($ssb_account)) {
                        $this->repository->getAllMemberCompany()->whereId($membercomId)->update(['ssb_account' => $investmentAccountNoSsb]);
                    }
                }
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
        }
    }
    public function getCompanyDetailsByMemberId($customerId)
    {
        $com = [];
        foreach ($this->repository->getAllCompanies()->get() as $k => $c) {
            $cid = $c->id;
            $membercompany = $this->repository->getAllMemberCompany()
                ->whereCustomerId($customerId)
                ->whereStatus(1)
                ->whereIsDeleted(0)
                ->whereCompanyId($cid)
                ->exists();
            if (!$membercompany) {
                $com[$k] = $cid;
            }
        }
        return $com;
    }
}