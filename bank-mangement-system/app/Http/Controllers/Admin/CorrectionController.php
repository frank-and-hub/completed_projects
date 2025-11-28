<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{Daybook, SamraddhBankDaybook, Member, CorrectionRequests, Memberinvestments, AllHeadTransaction, BranchDaybook, Memberinvestmentsnominees, MemberNominee, PlanTenures, AssociateDependent, SavingAccount, Investmentplanamounts, Memberloans,Grouploans};
use App\Models\SavingAccountTranscation;
use App\Models\IdType;
use App\Models\MemberCompany;
use App\Models\Investmentplantransactions;
use App\Models\CorrectionRequestDetail;
use URL;
use DB;
use Carbon\Carbon;
use Session;
use App\Services\ImageUpload;
use Error;
use Illuminate\Support\Facades\Cache;


/*
    |---------------------------------------------------------------------------
    | Admin Panel -- Member Management CorrectionController
    |--------------------------------------------------------------------------
    |
    | This controller handles members all functionlity.
*/
class CorrectionController extends Controller
{
    /**
     * Create a new controller instance.
     * @return void
     */

    public function __construct()
    {
        // check user login or not
        $this->middleware('auth');
        $this->member = new Member();
    }

    /**
     * Member Correction View.
     * Route: /member/passbook
     * Method: get
     * @return  array()  Response
     */
    public function correctionRequestView(Request $request)
    {
        /*
        if ($request->segment(2) == 'member') {
            if (check_my_permission(Auth::user()->id, "68") != "1") {
                return redirect()->route('admin.dashboard');
            }
            $data['title'] = 'Members';
            $data['type'] = 0;
        } elseif ($request->segment(2) == 'associate') {
            if (check_my_permission(Auth::user()->id, "69") != "1") {
                return redirect()->route('admin.dashboard');
            }
            $data['title'] = 'Associates';
            $data['type'] = 1;
        } elseif ($request->segment(2) == 'memberinvestment') {
            if (check_my_permission(Auth::user()->id, "70") != "1") {
                return redirect()->route('admin.dashboard');
            }
            $data['title'] = 'Investments';
            $data['type'] = 2;
        } elseif ($request->segment(2) == 'renew') {
            if (check_my_permission(Auth::user()->id, "71") != "1") {
                return redirect()->route('admin.dashboard');
            }
            $data['title'] = 'Renewals';
            $data['type'] = 3;
        } elseif ($request->segment(2) == 'printpassbook') {
            if (check_my_permission(Auth::user()->id, "311") != "1") {
                return redirect()->route('admin.dashboard');
            }
            $data['title'] = 'Print Passbook';
            $data['type'] = 5;

        } elseif ($request->segment(2) == 'printcertificate') {
            if (check_my_permission(Auth::user()->id, "312") != "1") {
                return redirect()->route('admin.dashboard');
            }
            $data['title'] = 'Print Certificate';
            $data['type'] = 6;
        }
        */
        $segmentMapping = [
            'member' => ['permission' => '68', 'title' => 'Members', 'type' => 0],
            'associate' => ['permission' => '69', 'title' => 'Associates', 'type' => 1],
            'memberinvestment' => ['permission' => '70', 'title' => 'Investments', 'type' => 2],
            'renew' => ['permission' => '71', 'title' => 'Renewals', 'type' => 3],
            'printpassbook' => ['title' => 'Print Passbook', 'type' => 5],
            'printcertificate' => ['title' => 'Print Certificate', 'type' => 6],
        ];

        $segment = $request->segment(2);

        if (array_key_exists($segment, $segmentMapping)) {
            $mapping = $segmentMapping[$segment];
            $permission = $mapping['permission'] ?? null;
            $title = $mapping['title'];
            $type = $mapping['type'] ?? 0;
        } else {
            // return redirect()->route('admin.dashboard');
        }

        if ($permission !== null && check_my_permission(Auth::user()->id, $permission) != "1") {
            return redirect()->route('admin.dashboard');
        }
        $data['title'] = $title;
        $data['type'] = $type;
        return view('templates.admin.common.correctionrequest', $data);
    }

    /**
     * Fetch member correction listing data.
     *@method post call ajax
     * @param  \App\Reservation  $reservation
     * @return \Illuminate\Http\Response
     */
    public function correctionRequestList(Request $request)
    {
        if ($request->ajax()) {
            $arrFormData = array();
            if (!empty($_POST['searchform'])) {
                foreach ($_POST['searchform'] as $frm_data) {
                    $arrFormData[$frm_data['name']] = $frm_data['value'];
                }
            }
            if (isset($arrFormData['is_search']) && $arrFormData['is_search'] == 'yes') {
                $data = CorrectionRequests::query();
                $data = $data->has('correctionCompay')->with([
                    'correctionMemberInvestmentCustom:id,account_number,plan_id,print_request_type',
                    'correctionMemberInvestmentCustom.plan:id,plan_category_code',
                    'correctionSeniorCustom:id,member_id,associate_no',
                    'correctionDaybookCustom:id,payment_mode,investment_id',
                    'correctionDaybookCustom.investment:id,account_number,plan_id',
                    'branch:id,name,branch_code,sector,regan,zone',
                    'correctionCompay:id,name',
                    'correctionSavingAccount',
                    'correctionSavingAccount.savingAc',
                    // 'correctionSavingAccount.savingAc.getMemberinvestments:id,account_number,plan_id,company_id'
                ])
                ->when($arrFormData['type'] != '', function ($q) use ($arrFormData) {
                    $q->where('correction_type', $arrFormData['type']);
                });

                if (Auth::user()->branch_id > 0) {
                    $id = Auth::user()->branch_id;
                    $data = $data->where('branch_id', '=', $id);
                }
                if (isset($arrFormData['branch_id']) && $arrFormData['branch_id'] > 0) {
                    $branch_id = $arrFormData['branch_id'];
                    $data = $data->where('branch_id', '=', $branch_id);
                }

                if ($arrFormData['correction_date'] != '') {
                    $correction_date = date("Y-m-d", strtotime(convertDate($arrFormData['correction_date'])));
                    $data = $data->whereDate('created_at', $correction_date);
                }
                if (isset($arrFormData['company_id']) && $arrFormData['company_id'] > 0) {
                    $company_id = $arrFormData['company_id'];
                    $data = $data->where('company_id', $company_id);
                }

                if ($arrFormData['status'] != '') {
                    $status = $arrFormData['status'];
                    $data = $data->where('status', $status);
                }

                $data1 = $data->orderBy('id', 'DESC')->count('id');
                $count = $data1;
                $data = $data->orderBy('id', 'DESC')->offset($_POST['start'])->limit($_POST['length']);

                $data = $data->get();

                $totalCount = $data1;
                $sno = $_POST['start'];
                $rowReturn = array();
                $inContext = [
                    0 => 'Member Registration',
                    1 => 'Associate Registration',
                    2 => 'Investment Registration',
                    3 => 'Renewals Transaction',
                    4 => 'Withdrawals',
                    5 => 'Passbook print',
                    6 => 'Certificate print',
                ];
                $status = [
                    0 => 'Pending',
                    1 => 'Corrected',
                    2 => 'Rejected',
                ];
                $printType = [
                    1 => 'Free',
                    2 => 'Paid',
                ];
                foreach ($data as $row) {
                    /** correction request auto id */
                    $id = $row->id;
                    /** correction request plan_category type is correction from saving => 'S' or demand => 'D' */
                    $code = $row->plan_category; // 'S' or  'D';
                    /** last transaction auto id form table saving_account_transaction or day_book */
                    $correctiontypeId = $row->correction_type_id;
                    /** condication for action button start */
                    $btn = '<div class="list-icons"><div class="dropdown"><a href="#" class="list-icons-item" data-toggle="dropdown"><i class="icon-menu9"></i></a><div class="dropdown-menu dropdown-menu-right">';
                    $URL = [
                        0 => "admin/member-edit/" . $correctiontypeId . "?action=change-request&request-id=" . $id . "&type=1",
                        1 => "admin/associate-edit/" . $correctiontypeId . "?action=change-request&request-id=" . $id . "",
                        2 => "admin/investment/edit/" . $correctiontypeId . "?action=change-request&request-id=" . $id . "",
                        3 => "admin/renew/delete/" . $correctiontypeId . "/" . $id . "/" . $code,
                        5 => "admin/investment/updateprintstatus/" . $correctiontypeId . "/" . $id . "",
                        6 => "admin/investment/updateprintstatus/" . $correctiontypeId . "/" . $id . "",
                    ];
                    $url = URL::to($URL[$row->correction_type]);
                    $btn .= '<a class="dropdown-item correction-view-button" href="javascript:void(0)" data-toggle="modal" data-target="#correction-view" data-correction-details="' . $row->correction_description . '" title="View"><i class="icon-eye-blocked2  mr-2"></i>Detail</a>';
                    if ($row->status == 0) {
                        if ($row->correction_type == 3) {
                            if ($row->plan_category == 'S') {
                                $daybook = $row['correctionSavingAccount'];
                                $btn .= $this->daybookbutton($daybook, $url);
                            } else {
                                $daybook = $row['correctionDaybookCustom'];
                                $btn .= $this->daybookbutton($daybook, $url);
                            }
                        } elseif (in_array($row->correction_type, [5, 6])) {
                            $btn .= '<a class="dropdown-item approve" id="approve" href="javascript:void(0)"   data-toggle="modal" data-target="#myModal" data-id="' . $correctiontypeId . '" data-correctionid="' . $id . '"  title="Approve"><i class="icon-pencil5  mr-2"></i>Approve</a>';
                        } else {
                            $btn .= '<a class="dropdown-item" href="' . $url . '" title="Edit"><i class="icon-pencil5  mr-2"></i>Edit</a>';
                        }
                        $btn .= '<a class="dropdown-item correction-reject-button" href="javascript:void(0)" data-toggle="modal" data-target="#correction-rejected" data-correction-id="' . $id . '" title="Reject"><i class="fas fa-thumbs-down mr-2"></i>Reject</a>';
                    }
                    $btn .= '</div></div></div>';
                    /** condication for action button end */
                    $sno++;
                    $account_no = 'N/A'; // Default value
                    switch ($row->correction_type) {
                        case 0:
                            $account_no = $row['correctionSeniorCustom']['member_id'] ?? "N/a";
                            break;
                        case 1:
                            $account_no = $row['correctionSeniorCustom']->associate_no;
                            break;
                        case 2:
                        case 3:
                            if ($row['correctionSavingAccount'] && $row['correctionSavingAccount']['savingAc'] && $row['correctionSavingAccount']['savingAc']['getMemberinvestments']) {
                                $i = $row['correctionSavingAccount']['savingAc']['getMemberinvestments'];
                                $invId = Daybook::where('saving_account_transaction_reference_id',$row->correction_type_id)
                                    ->where('investment_id',$i->id)
                                    ->where('account_no',$i->account_number)
                                    ->first();
                                    if($invId){
                                        $account_no = '<a href="admin/investment/passbook/transaction/' . $i->id . '/' . getPlanCategoryCodeById($i->plan_id) . '">' . $invId->account_no . '</a>';
                                    }else{
                                        $account_no = '';
                                    }
                            }else{
                                $invId = ($row['correctionDaybookCustom']) ? $row['correctionDaybookCustom']['investment'] : '';
                                $i = $row['correctionDaybookCustom'];
                                if($invId){
                                    $account_no = '<a href="admin/investment/passbook/transaction/' . $invId->id . '/' . getPlanCategoryCodeById($invId->plan_id) . '">' . $invId->account_number . '</a>';
                                }else{
                                    $account_no = '';
                                }
                            }
                            break;
                        case 4:
                            if ($row['correctionDaybookCustom'] && $row['correctionDaybookCustom']['investment']) {
                                $invId = $row['correctionDaybookCustom']['investment'];
                                $account_no = '<a href="admin/investment/passbook/transaction/' . $invId->id . '">' . $invId->account_number . '</a>';
                            }
                            break;
                        case 5:
                            if($row['correctionMemberInvestmentCustom']){
                                $invId = $row['correctionMemberInvestmentCustom'];
                                $account_no = '<a href="admin/investment/passbook/transaction/' . $invId->id . '/' . getPlanCategoryCodeById($invId->plan_id) . '">' . $invId->account_number . '</a>';
                            }
                        case 6:
                            if ($row['correctionDaybookCustom'] && $row['correctionDaybookCustom']['investment']) {
                                $invId = $row['correctionMemberInvestmentCustom'];
                                $account_no = '<a href="admin/investment/passbook/transaction/' . $invId->id . '/' . getPlanCategoryCodeById($invId->plan_id) . '">' . $invId->account_number . '</a>';
                            }
                            break;
                    }
                    // dd($row->toArray());
                    $val = [
                        'DT_RowIndex' => $sno,
                        'created_at' => date("d/m/Y", strtotime(convertDate($row->created_at))),
                        'branch' => $row['branch']->name,
                        'company' => $row['correctionCompay'] ? $row['correctionCompay']->name : 'N/A',
                        // 'in_context' => $row->correction_type == 3 ? '' : ($inContext[$row->correction_type] ?? ''),
                        'in_context' => ($inContext[$row->correction_type] ?? ''),
                        'correction' => $row->correction_description,
                        'account_no' => $account_no,
                        'status' => $status[$row->status] ?? '',
                        // 'printType' => $row->correction_type == 3 ? '' : $printType[$row->print_type] ?? 'N/A',
                        'printType' => $printType[$row->print_type] ?? 'N/A',
                        'action' => $btn,
                    ];
                    $rowReturn[] = $val;
                }
                $output = array("draw" => $_POST['draw'], "recordsTotal" => $totalCount, "recordsFiltered" => $count, "data" => $rowReturn);
            } else {
                $output = array(
                    "draw" => 0,
                    "recordsTotal" => 0,
                    "recordsFiltered" => 0,
                    "data" => 0,
                );
            }
            return json_encode($output);
        }
    }

    /** use for showing renewal listing separately */
    public function correctionRequestList_renewal(Request $request)
    {
        if ($request->ajax()) {
            $arrFormData = array();
            if (!empty($_POST['searchform'])) {
                foreach ($_POST['searchform'] as $frm_data) {
                    $arrFormData[$frm_data['name']] = $frm_data['value'];
                }
            }
            if (isset($arrFormData['is_search']) && $arrFormData['is_search'] == 'yes') {
                $data = CorrectionRequests::query();
                if (isset($arrFormData['code_type']) && $arrFormData['code_type'] == 'new') {
                    $data = $data->with([
                        'correctionSavingAccount:id,saving_account_id,account_no,type,payment_type,payment_mode,company_id,status',
                        'correctionSavingAccount.savingAc:id,member_investments_id,account_no,member_id,customer_id,balance,company_id,status,is_deleted'
                    ]);
                }
                $data = $data->has('correctionCompay')->with([
                    'correctionMemberInvestmentCustom:id,account_number,plan_id,print_request_type',
                    'correctionSeniorCustom:id,member_id,associate_no',
                    'correctionDaybookCustom:id,payment_mode,investment_id',
                    'correctionDaybookCustom.investment:id,account_number,plan_id',
                    'branch:id,name,branch_code,sector,regan,zone',
                    'correctionCompay:id,name'
                ])
                    ->when($arrFormData['type'] != '', function ($q) use ($arrFormData) {
                        $q->where('correction_type', $arrFormData['type']);
                    });
                if (isset($arrFormData['branch_id']) && $arrFormData['branch_id'] > 0) {
                    $branch_id = $arrFormData['branch_id'];
                    $data = $data->where('branch_id', '=', $branch_id);
                }
                if ($arrFormData['correction_date'] != '') {
                    $correction_date = date("Y-m-d", strtotime(convertDate($arrFormData['correction_date'])));
                    // condication : transaction filter is new starting from 15th may 2023
                    $data = $data->whereDate('created_at', $correction_date);
                }
                $date = "2023-05-15";
                if (isset($arrFormData['code_type']) && $arrFormData['code_type'] == 'new') {
                    $data = $data->whereDate('created_at', '>', $date);
                } else {
                    $data = $data->whereDate('created_at', '<', $date);
                }
                if ($arrFormData['company_id'] > 0) {
                    $company_id = $arrFormData['company_id'];
                    $data = $data->where('company_id', $company_id);
                }
                if ($arrFormData['status'] != '') {
                    $status = $arrFormData['status'];
                    $data = $data->where('status', $status);
                }
                $data1 = $data->orderBy('id', 'DESC')->count('id');
                $count = $data1;
                $data = $data->orderBy('id', 'DESC')->offset($_POST['start'])->limit($_POST['length']);
                $data = $data->get();
                $totalCount = $data1;
                $sno = $_POST['start'];
                $rowReturn = array();
                $inContext = [
                    0 => 'Member Registration',
                    1 => 'Associate Registration',
                    2 => 'Investment Registration',
                    3 => 'Renewals Transaction',
                    4 => 'Withdrawals',
                    5 => 'Passbook print',
                    6 => 'Certificate print',
                ];
                $status = [
                    0 => 'Pending',
                    1 => 'Corrected',
                    2 => 'Rejected',
                ];
                $printType = [
                    1 => 'Free',
                    2 => 'Paid',
                ];
                foreach ($data as $row) {
                    /** correction request auto id */
                    $id = $row->id;
                    /** correction request plan_category type is correction from saving => 'S' or demand => 'D' */
                    $code = $row->plan_category; // 'S' or  'D';
                    /** last transaction auto id form table saving_account_transaction or day_book */
                    $correctiontypeId = $row->correction_type_id;
                    /** condication for action button start */
                    $btn = '<div class="list-icons"><div class="dropdown"><a href="#" class="list-icons-item" data-toggle="dropdown"><i class="icon-menu9"></i></a><div class="dropdown-menu dropdown-menu-right">';
                    $URL = [
                        0 => "admin/member-edit/" . $correctiontypeId . "?action=change-request&request-id=" . $id . "&type=1",
                        1 => "admin/associate-edit/" . $correctiontypeId . "?action=change-request&request-id=" . $id . "",
                        2 => "admin/investment/edit/" . $correctiontypeId . "?action=change-request&request-id=" . $id . "",
                        3 => "admin/renew/delete/" . $correctiontypeId . "/" . $id . "/" . $code,
                        5 => "admin/investment/updateprintstatus/" . $correctiontypeId . "/" . $id . "",
                        6 => "admin/investment/updateprintstatus/" . $correctiontypeId . "/" . $id . "",
                    ];
                    $url = URL::to($URL[$row->correction_type]);
                    $btn .= '<a class="dropdown-item correction-view-button" href="javascript:void(0)" data-toggle="modal" data-target="#correction-view" data-correction-details="' . $row->correction_description . '" title="View"><i class="icon-eye-blocked2  mr-2"></i>Detail</a>';
                    if ($row->status == 0) {
                        if ($row->correction_type == 3) {
                            if ($row->plan_category == 'S') {
                                $daybook = $row['correctionSavingAccount'];
                                $btn .= $this->daybookbutton($daybook, $url);
                            } else {
                                $daybook = $row['correctionDaybookCustom'];
                                $btn .= $this->daybookbutton($daybook, $url);
                            }
                        } elseif (in_array($row->correction_type, [5, 6])) {
                            $btn .= '<a class="dropdown-item approve" id="approve" href="javascript:void(0)"   data-toggle="modal" data-target="#myModal" data-id="' . $correctiontypeId . '" data-correctionid="' . $id . '"  title="Approve"><i class="icon-pencil5  mr-2"></i>Approve</a>';
                        } else {
                            $btn .= '<a class="dropdown-item" href="' . $url . '" title="Edit"><i class="icon-pencil5  mr-2"></i>Edit</a>';
                        }
                        $btn .= '<a class="dropdown-item correction-reject-button" href="javascript:void(0)" data-toggle="modal" data-target="#correction-rejected" data-correction-id="' . $id . '" title="Reject"><i class="fas fa-thumbs-down mr-2"></i>Reject</a>';
                    }
                    $btn .= '</div></div></div>';
                    /** condication for action button end */
                    $sno++;
                    $account_no = 'N/A'; // Default value
                    switch ($row->correction_type) {
                        case 0:
                            $account_no = $row['correctionSeniorCustom']['member_id'] ?? "N/a";
                            break;
                        case 1:
                            $account_no = $row['correctionSeniorCustom']->associate_no;
                            break;
                        case 2:
                        case 3:
                        case 4:
                            if ($row['correctionDaybookCustom'] && $row['correctionDaybookCustom']['investment']) {
                                $account_no = $row['correctionDaybookCustom']['investment']->account_number;
                            }
                            break;
                        case 5:
                        case 6:
                            if ($row['correctionDaybookCustom'] && $row['correctionDaybookCustom']['investment']) {
                                $invId = $row['correctionMemberInvestmentCustom'];
                                $account_no = '<a href="admin/investment/passbook/transaction/' . $invId->id . '/' . $invId->plan_id . '">' . $invId->account_number . '</a>';
                            }
                            break;
                    }
                    $val = [
                        'DT_RowIndex' => $sno,
                        'created_at' => date("d/m/Y", strtotime(str_replace('-', '/', $row->created_at))),
                        'branch' => $row['branch']->name,
                        'company' => $row['correctionCompay'] ? $row['correctionCompay']->name : 'N/A',
                        'in_context' => $inContext[$row->correction_type] ?? '',
                        'correction' => $row->correction_description,
                        'account_no' => $account_no,
                        'status' => $status[$row->status] ?? '',
                        'printType' => $printType[$row->print_type] ?? 'N/A',
                        'action' => $btn,
                    ];
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
     * Reject Corrections.
     * Route: rejectcorrectionrequest
     * Method: get
     * @param  $id,$msg
     * @return  array()  Response
     */
    public function rejectCoreectionRequest(Request $request)
    {
        $correctionDetails = CorrectionRequests::where('id', $request->correction_id)->first(['id', 'correction_type', 'correction_type_id', 'plan_category']);
        $correction = CorrectionRequests::find($request->correction_id);
        $correctionData['rejected_correction_description'] = $request->rejection;
        $correctionData['status'] = 2;
        $update = ['investment_correction_request' => '0', 'renewal_correction_request' => '0'];
        $correction->update($correctionData);

        if ($correctionDetails->correction_type == 2) {
            Memberinvestments::find($correctionDetails->correction_type_id)->update(['investment_correction_request' => '0']);
        }

        if ($correctionDetails->correction_type == 3) {
            if ($correctionDetails->plan_category == 'S') {
                $data = SavingAccountTranscation::whereId($correctionDetails->correction_type_id)->first(['account_no', 'daybook_ref_id']);
                Memberinvestments::where('account_number', $data->account_no)->update($update);
            } else {
                $data = Daybook::whereId($correctionDetails->correction_type_id)->first(['investment_id', 'daybook_ref_id']);
                Memberinvestments::find($data->investment_id)->update($update);
            }
        }

        // $cheque = SamraddhBankDaybook::whereDaybookRefId($data->daybook_ref_id)->value('cheque_no');
        // if ($cheque) {
        //     ReceivedCheque::whereChequeNo($cheque)->update(['status' => '2']);
        // }

        return back()->with('success', 'Correction request has been rejected!');
    }

    public function updateprintstatus(Request $request)
    {

        $updateprintstatus = Memberinvestments::where('id', $request->userid)->first();
        $updatecorrectionstatus = CorrectionRequests::where('id', $request->corr_id)->first();
        $updatecorrectionstatus->status = 1;
        if ($request->printstatus == 1) {
            $updatecorrectionstatus->print_type = $request->printstatus;

            if ($updatecorrectionstatus->correction_type == 6) {
                $updatecorrectionstatus->print_type = $request->printstatus;
                $updateprintstatus->is_certificate_print = 0;
            } else {
                $updateprintstatus->is_passbook_print = 0;
            }
        } else {
            $updatecorrectionstatus->print_type = $request->printstatus;
            if ($updatecorrectionstatus->correction_type == 6) {
                $updatecorrectionstatus->print_type = $request->printstatus;
                $updateprintstatus->is_certificate_print = 0;
            } else {
                $updateprintstatus->is_passbook_print = 0;
            }

        }

        $updateprintstatus->update();
        $updatecorrectionstatus->update();

        if ($updatecorrectionstatus->correction_type == 6) {
            return redirect()->route('admin.printcertificate.correctionrequest');
        } else {
            return redirect()->route('admin.printpassbook.correctionrequest');
        }
    }
    public function correctionRequestlists(Request $request)
    {
        if ($request->ajax()) {


            $arrFormData = array();
            if (!empty($_POST['searchform'])) {
                foreach ($_POST['searchform'] as $frm_data) {
                    $arrFormData[$frm_data['name']] = $frm_data['value'];
                }
            }
            if (isset($arrFormData['is_search']) && $arrFormData['is_search'] == 'yes') {
                $data = CorrectionRequestDetail::has('company')->with(['correction_type', 'branch:id,name', 'customer:id,first_name,member_id,associate_no', 'company:id,name', 'user:id,username'])->where('is_deleted', '!=', 1);


                if (isset($arrFormData['branch_id']) && $arrFormData['branch_id'] != '0') {
                    $id = $arrFormData['branch_id'];
                    $data = $data->where('branch_id', '=', $id);
                }

                if ($arrFormData['company_id'] != '' && $arrFormData['company_id'] != '0') {
                    $company_id = $arrFormData['company_id'];
                    $data = $data->where('company_id', $company_id);
                }
                if ($arrFormData['associate_code'] != '') {
                    $name = $arrFormData['associate_code'];
                    $data = $data->whereHas('customer', function ($query) use ($name) {
                        $query->where('members.associate_no', 'LIKE', '%' . $name . '%')->orWhere(DB::raw('concat(members.associate_no)'), 'LIKE', "%$name%");
                    });
                }
                if ($arrFormData['customer_id'] != '') {
                    $name = $arrFormData['customer_id'];
                    $data = $data->whereHas('customer', function ($query) use ($name) {
                        $query->where('members.member_id', 'LIKE', '%' . $name . '%')->orWhere(DB::raw('concat(members.member_id)'), 'LIKE', "%$name%");
                    });
                }

                if ($arrFormData['status'] != '') {
                    $status = $arrFormData['status'];
                    $data = $data->where('status', $status);
                }

                $data1 = $data->orderBy('id', 'DESC')->count('id');
                $count = $data1;
                $datac = $data->orderBy('id', 'DESC')->get();
                $data = $data->orderBy('id', 'DESC')->offset($_POST['start'])->limit($_POST['length']);

                $data = $data->get();

                $totalCount = $data1;
                $sno = $_POST['start'];
                $rowReturn = array();
                $token = session()->get('_token');

                $Cache = Cache::put('correction_list' . $token, $datac);
                Cache::put('correction_list_COUNT' . $token, $count);
                foreach ($data as $row) {

                    $sno++;
                    $val['DT_RowIndex'] = $sno;
                    $val['created_at'] = date("d/m/Y", strtotime(str_replace('-', '/', $row->created_at)));
                    $name = '';
                    $name .= $row['customer']->first_name ?? '';
                    $name .= $row['customer']->last_name ?? '';
                    $val['customer_name'] = $name;
                    $val['correction_type_Id'] = $row['correction_type']->module_name;
                    $val['field_name'] = $row['correction_type']->field_name;
                    $val['old_value'] = $row->old_value;
                    $act = '';
                    if ($row->correction_type_Id == 4 || $row->correction_type_Id == 38) {
                        $act = '(' . $row->actual_value . ')';
                    }
                    $val['new_value'] = $row->new_value . $act;
                    if ($row->correction_type_Id == 63 || $row->correction_type_Id == 64) {
                        $folderName = $row->correction_type_Id == 63 ? 'profile/member_avatar/' . $row->actual_value : 'profile/member_signature/' . $row->actual_value;
                        $folderName2 = $row->correction_type_Id == 63 ? 'profile/member_avatar/' . $val['old_value'] : 'profile/member_signature/' . $val['old_value'];
                        $url = ImageUpload::generatePreSignedUrl($folderName);
                        $url2 = ImageUpload::generatePreSignedUrl($folderName2);
                        $image = '<a href="' . $url . '" target="_blank">' . $val['new_value'] . ' </a>';
                        $val['old_value'] = '<a href="' . $url2 . '" target="_blank">' . $val['old_value'] . ' </a>';
                        $val['new_value'] = $image;
                    }
                    $val['description'] = $row->description;
                    $status = 'N/A';
                    if ($row->status == 0 || $row->status == '0') {
                        $status = 'PENDING';
                    } elseif ($row->status == 1 || $row->status == '1') {
                        $status = 'APPROVED';
                    } elseif ($row->status == 2 || $row->status == '2') {
                        $status = 'REJECTED';
                    }
                    $val['branch'] = $row['branch']->name;
                    $val['company'] = $row['company']->name;
                    $user = 'N/A';
                    if ($row->created_by == 3 || $row->created_by == '3') {
                        $user = 'ASSOCIATE';
                    } elseif ($row->created_by == 1 || $row->created_by == '1') {
                        $user = 'ADMIN';
                    } elseif ($row->created_by == 2 || $row->created_by == '2') {
                        $user = 'BRANCH';
                    } elseif ($row->created_by == 4 || $row->created_by == '4') {
                        $user = 'E-PASSBOOK';
                    }
                    $val['status'] = $status;
                    $val['created_by'] = $user;
                    $val['user'] = $row['user']->username;
                    ;
                    $val['status_date'] = $row->status_date ? date("d/m/Y", strtotime(str_replace('-', '/', $row->status_date))) : 'N/A';
                    $val['status_remark'] = $row->status_remark ?? 'N/A';
                    $btn = 'N/A';
                    if ($row->status == 0) {
                        $btn = '';
                        $btn .= '<div class="list-icons"><div class="dropdown"><a href="#" class="list-icons-item" data-toggle="dropdown"><i class="icon-menu9"></i></a><div class="dropdown-menu dropdown-menu-right">';
                        $btn .= '<a class="dropdown-item approve" id="approve" href="javascript:void(0)"   data-toggle="modal" data-target="#myModal" data-id="' . $row->correction_type_id . '" data-correctionid="' . $row->id . '"  title="Approve"><i class="icon-pencil5  mr-2"></i>Approve</a>';
                        $btn .= '<a class="dropdown-item correction-reject-button" href="javascript:void(0)" data-toggle="modal" data-target="#correction-rejected" data-correction-id="' . $row->id . '" title="Reject"><i class="fas fa-thumbs-down"></i>Reject</a>';
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

    public function rejectCorrectionRequest(Request $request)
    {
        $date = (date("Y/m/d H:i:s", strtotime(str_replace('/', '-', $request->created_at))));
        $correctionDetails = CorrectionRequestDetail::find($request->correction_id);
        $correctionDetails['status'] = 2;
        $correctionDetails['status_remark'] = $request->rejection;
        $correctionDetails['status_date'] = $date;
        $url = true;
        if ($correctionDetails['correction_type_Id'] == 63 || $correctionDetails['correction_type_Id'] == 64) {
            $folderName = $correctionDetails['correction_type_Id'] == 63 ? 'profile/member_avatar/' . $correctionDetails['actual_value'] : 'profile/member_signature/' . $correctionDetails['actual_value'];
            $url = ImageUpload::deleteImage($folderName);
        }
        $return = $correctionDetails->update();
        if ($return && $url) {
            return back()->with('success', 'Correction request has been rejected!');
        } else {
            return back()->with('warning', 'Some technical error!');
        }
    }
    public function approveCorrectionRequest(Request $request)
    {
        DB::beginTransaction();
        try {
            $date = (date("Y/m/d H:i:s", strtotime(str_replace('/', '-', $request->created_at))));
            $correctionDetails = CorrectionRequestDetail::whereId($request->correction_id)->with('correction_type')->first();
            $updatevalue = $correctionDetails['actual_value'] != NULL ? $correctionDetails['actual_value'] : $correctionDetails['new_value'];
            if ($correctionDetails['correction_type']['main_table'] == 'member' && $correctionDetails['correction_type_Id'] != "38" && $correctionDetails['correction_type_Id'] != "4") {
                $update = Member::whereId($correctionDetails->type_id)->update([$correctionDetails['correction_type']['field_slug'] => $updatevalue]);
            } elseif ($correctionDetails['correction_type']['module_name'] == 'Investment Details') {
                if ($correctionDetails['correction_type_Id'] == 53 || $correctionDetails['correction_type_Id'] == 51) {
                    $member_data = Memberinvestments::whereId($correctionDetails->type_id)->with('plan')->first();
                    $tenure_ = ($correctionDetails['correction_type_Id'] == 51) ? $updatevalue : $member_data->tenure*12;
                    $tenure_data = PlanTenures::where('plan_id',$member_data['plan']->id)->where('tenure',$tenure_)->first();
                    $deno_amount = ($correctionDetails['correction_type_Id'] == 53) ? $updatevalue : $member_data->deposite_amount;
                    $ci = $tenure_data->compounding?? "";
                    $code = $member_data['plan']->plan_category_code;
                    $for_maturity = [
                        'interest' => $tenure_data->roi??$member_data->interest_rate,
                        'tenure' => $tenure_,
                        'ci' => $ci,
                        'amount' => $deno_amount,
                        'code' =>$code
                    ];
                    $maturity_amount = $this->maturitycalculate($for_maturity);
                    $member_data->update(['maturity_amount'=>$maturity_amount,'interest_rate'=>$tenure_data->roi??$member_data->interest_rate]);
                }
                if ($correctionDetails['correction_type_Id'] == 53 || $correctionDetails['correction_type_Id'] == 54) {
                    $member_data = Memberinvestments::whereId($correctionDetails->type_id)->first();
                    $account_no = $member_data['account_number'];
                    $day_book = Daybook::Where('account_no', $account_no)->where('transaction_type', 2)->first();
                    if ($day_book['is_deleted'] == 1) {
                        return ['deleted'];
                    }
                    $refID = $day_book->daybook_ref_id ?? $day_book->transaction_id;
                    $all_head_trxn = AllHeadTransaction::where('daybook_ref_id', $refID)
                        ->whereIn('type', [3, 4])
                        ->whereIn('sub_type', [31, 41, 43, 42])
                        ->where('amount', $day_book['deposit'])
                        ->where('is_deleted', 0)
                        ->get();
                    if (isset($all_head_trxn[2])) {
                        pd("Error adding");
                        return ['error'];
                    }
                    $BranchDaybook = BranchDaybook::where('daybook_ref_id', $refID)
                        ->whereIn('type', [3, 4])
                        ->whereIn('sub_type', [31, 41, 43, 42])
                        ->where('amount', $day_book['deposit'])
                        ->where('is_deleted', 0)
                        ->get();
                    if (isset($all_head_trxn) && !empty($all_head_trxn)) {
                        if ($correctionDetails['correction_type_Id'] == 53) {
                            foreach ($all_head_trxn as $transaction) {
                                // Update the record as needed
                                $transaction->update([
                                    'amount' => $updatevalue
                                ]);
                            }
                            $day_book->update([
                                'opening_balance' => $updatevalue,
                                'amount' => $updatevalue,
                                'deposit' => $updatevalue
                            ]);
                        } elseif ($correctionDetails['correction_type_Id'] == 54) {
                            foreach ($all_head_trxn as $transaction) {
                                $transaction->update([
                                    'created_at' => $updatevalue
                                ]);
                            }
                            $day_book->update([
                                'created_at' => $updatevalue
                            ]);
                        }
                    } else {
                        pd("Error updating");
                        return ['error'];
                    }
                    if (isset($BranchDaybook)) {
                        if ($correctionDetails['correction_type_Id'] == 53) {
                            foreach ($BranchDaybook as $trxn) {
                                $lastCrPosition = strrpos($trxn['description_dr'], 'Dr');
                                if ($lastCrPosition !== false) {
                                    $resultString = substr($trxn['description_dr'], 0, $lastCrPosition);
                                    $resultString = trim($resultString);
                                    $description_dr = "$resultString $updatevalue/-";
                                } else {
                                    $description_dr = $trxn['description_dr'];
                                }
                                $lastCrPosition = strrpos($trxn['description_cr'], 'Cr');
                                if ($lastCrPosition !== false) {
                                    $resultString = substr($trxn['description_cr'], 0, $lastCrPosition);
                                    $resultString = trim($resultString);
                                    $description_cr = "$resultString $updatevalue/-";
                                } else {
                                    $description_cr = $trxn['description_cr'];
                                }
                                $trxn->update([
                                    'amount' => $updatevalue,
                                    'description_cr' => $description_cr,
                                    'description_dr' => $description_dr,
                                ]);
                            }
                        } elseif ($correctionDetails['correction_type_Id'] == 54) {
                            foreach ($BranchDaybook as $trxn) {
                                $trxn->update([
                                    'created_at' => $updatevalue
                                ]);
                            }
                        }
                    }
                }
                $modelClass = '\App\Models\\' . $correctionDetails['correction_type']['main_table'];
                $modelInstance = new $modelClass;

                if ($correctionDetails['correction_type']['main_table'] == 'Memberinvestments') {
                    $updatevalue = $correctionDetails['correction_type_Id'] == 51 ? $updatevalue/12 : $updatevalue;
                    $update = $modelInstance->whereId($correctionDetails->type_id)->update([$correctionDetails['correction_type']['field_slug'] => $updatevalue]);
                    if ($correctionDetails['correction_type_Id'] == 53) {
                        $modelInstance->whereId($correctionDetails->type_id)->update(['current_balance' => $updatevalue]);
                    }
                } else {
                    $update = $modelInstance->whereInvestment_id($correctionDetails->type_id)->update([$correctionDetails['correction_type']['field_slug'] => $updatevalue]);
                }
            } else {
                $modelClass = '\App\Models\\' . $correctionDetails['correction_type']['main_table'];
                $modelInstance = new $modelClass;
                // 38 and 4 are for id proof condition in this we have to update2 fields so its slightely diff
                if ($correctionDetails['correction_type_Id'] == "38" || $correctionDetails['correction_type_Id'] == "4") {
                    if ($correctionDetails['correction_type_Id'] == "38") {
                        $id_no = 'first_id_no';
                    } else {
                        $id_no = 'second_id_no';
                    }
                    $idval = IdType::whereName($correctionDetails['new_value'])->value('id');
                    $update = $modelInstance->whereMember_id($correctionDetails->type_id)->update([$correctionDetails['correction_type']['field_slug'] => $idval, $id_no => $updatevalue]);
                } else {
                    $update = $modelInstance->whereMember_id($correctionDetails->type_id)->update([$correctionDetails['correction_type']['field_slug'] => $updatevalue]);
                }
            }
            if ($update) {
                $correctionDetails->update(['status' => 1, 'status_remark' => 'Approved by ' . Auth::user()->username . '', 'status_date' => $date]);
                 DB::commit();
                $msg = 'success';
            } else {
                $msg = 'error';
                DB::rollback();
            }
            return response()->json($msg, 200);
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('alert', $ex->getMessage());
        }
    }
    public function approveCorrectionRequest_old(Request $request)
    {
        $date = (date("Y/m/d H:i:s", strtotime(str_replace('/', '-', $request->created_at))));
        $correctionDetails = CorrectionRequestDetail::whereId($request->correction_id)->with('correction_type')->first();
        $updatevalue = $correctionDetails['actual_value'] != NULL ? $correctionDetails['actual_value'] : $correctionDetails['new_value'];
        if ($correctionDetails['correction_type']['main_table'] == 'member' && $correctionDetails['correction_type_Id'] != "38" && $correctionDetails['correction_type_Id'] != "4") {
            $update = Member::whereId($correctionDetails->type_id)->update([$correctionDetails['correction_type']['field_slug'] => $updatevalue]);
        } else {
            $modelClass = '\App\Models\\' . $correctionDetails['correction_type']['main_table'];
            $modelInstance = new $modelClass;
            // 38 and 4 are for id proof condition in this we have to update2 fields so its slightely diff
            if ($correctionDetails['correction_type_Id'] == "38" || $correctionDetails['correction_type_Id'] == "4") {
                if ($correctionDetails['correction_type_Id'] == "38") {
                    $id_no = 'first_id_no';
                } else {
                    $id_no = 'second_id_no';
                }
                $idval = IdType::whereName($correctionDetails['new_value'])->value('id');
                $update = $modelInstance->whereMember_id($correctionDetails->type_id)->update([$correctionDetails['correction_type']['field_slug'] => $idval, $id_no => $updatevalue]);
            } else {
                $update = $modelInstance->whereMember_id($correctionDetails->type_id)->update([$correctionDetails['correction_type']['field_slug'] => $updatevalue]);
            }
        }
        if ($update) {
            $correctionDetails->update(['status' => 1, 'status_remark' => 'Approved by ' . Auth::user()->username . '', 'status_date' => $date]);
            return response()->json('success', 200);
        } else {
            return response()->json('error', 200);
        }
    }
    public function exportcorrection(Request $request)
    {
        $token = session()->get('_token');
        $file = Session::get('_fileName');
        $data = Cache::get('correction_list' . $token);
        $count = Cache::get('correction_list_COUNT' . $token);
        $input = $request->all();
        $start = $input["start"];
        $limit = $input["limit"];
        $returnURL = URL::to('/') . "/asset/CorrectionList" . $file . ".csv";
        $fileName = env('APP_EXPORTURL') . "/asset/CorrectionList" . $file . ".csv";
        global $wpdb;
        $postCols = array(
            'post_title',
            'post_content',
            'post_excerpt',
            'post_name',
        );
        header("Content-type: text/csv");
        $totalResults = $count;
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
        $rowReturn = [];
        $data = $data->toArray();
        $record = array_slice($data, $start, $limit);
        $totalCount = count($record);
        foreach ($record as $row) {
            $sno++;
            $val['S No.'] = $sno;
            $val['CREATED AT'] = date("d/m/Y", strtotime(str_replace('-', '/', $row['created_at'])));
            $name = '';
            $name .= $row['customer']['first_name'] ?? '';
            $name .= $row['customer']['last_name'] ?? '';
            $val['CHANGES FOR'] = $row['correction_type']['module_name'];
            $val['NAME'] = $name;
            $val['FIELD TO UPDATE'] = $row['correction_type']['field_name'];
            $val['OLD VALUE'] = $row['old_value'];
            $val['NEW VALUE'] = $row['new_value'];
            $val['DESCRIPTION'] = $row['description'];
            $status = 'N/A';
            if ($row['status'] == 0 || $row['status'] == '0') {
                $status = 'PENDING';
            } elseif ($row['status'] == 1 || $row['status'] == '1') {
                $status = 'APPROVED';
            } elseif ($row['status'] == 2 || $row['status'] == '2') {
                $status = 'REJECTED';
            }
            $val['BRANCH NAME'] = $row['branch']['name'];
            $val['COMPANY NAME'] = $row['company']['name'];
            $user = 'N/A';
            if ($row['created_by'] == 3 || $row['created_by'] == '3') {
                $user = 'ASSOCIATE';
            } elseif ($row['created_by'] == 1 || $row['created_by'] == '1') {
                $user = 'ADMIN';
            } elseif ($row['created_by'] == 2 || $row['created_by'] == '2') {
                $user = 'BRANCH';
            } elseif ($row['created_by'] == 4 || $row['created_by'] == '4') {
                $user = 'E-PASSBOOK';
            }
            $val['STATUS'] = $status;
            $val['CREATED BY'] = $user;
            $val['USER'] = $row['user']['username'];
            ;
            $val['STATUS DATE'] = !empty($row['status_date']) ? date("d/m/Y", strtotime(str_replace('-', '/', $row['status_date']))) : 'N/A';
            $val['STATUS REMARK'] = $row['status_remark'] ?? 'N/A';
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
    public function correctionRequestviewnew()
    {
        $data['title'] = 'Correction Request List';
        return view('templates.admin.common.correctionrequestnew', $data);
    }
    public function maturitycalculate($request)
    {
        $plancode = $request['code'];
        switch ($plancode) {
            case "S":
                $maturity = "";
                break;
            case "D":
                $rate = $request['interest'];
                $time = $request['tenure'];
                $ci = $request['ci']?? 3;
                $freq = 12;
                $irate = $rate / $ci;
                $maturity = 0;
                $monthlyPrincipal = $request['amount'] * 30;
                for ($i = 1; $i <= $time; $i++) {
                    $maturity += $monthlyPrincipal * pow((1 + ($rate / 100) / $freq), $freq * (($time - $i + 1) / 12));
                }
                break;
            case "F":
                $initialInvestment = $request['amount'];
                $annualInterestRate = $request['interest'];
                $timeInYears = $request['tenure'];
                $rate = $annualInterestRate / 100;
                $maturity = $initialInvestment * pow((1 + $rate), $timeInYears);
            case "M":
                $principal = $request['amount'];
                $rate = $request['interest'];
                $time = $request['tenure'];
                $ci = $request['ci']?? 12;
                $freq = 4;
                $maturity = 0;
                for ($i = 1; $i <= $time; $i++) {
                    $maturity += $principal * pow((1 + ($rate / 100) / $freq), $freq * (($time - $i + 1) / 12));
                }
                break;
        }
        return round($maturity);
    }
    public function daybookbutton($daybook, $url)
    {
        if (!empty($daybook)) {
            $btn = '';
            // if ($daybook->payment_mode == 1) {
            //     $btn .= '<a class="dropdown-item correction-delete-button" href="javascript:void(0)" data-toggle="modal" data-target="#correction-delete"  title="Approve"><i class="fas fa-thumbs-up  mr-2"></i>Approve</a>';
            // } else {
            $btn .= '<a class="dropdown-item" href="' . $url . '" title="Approve"><i class="fas fa-thumbs-up  mr-2"></i>Approve</a>';
            // }
        } else {
            $btn .= '<a class="dropdown-item" href="' . $url . '" title="Approve"><i class="fas fa-thumbs-up  mr-2"></i>Approve</a>';
        }
        return $btn;
    }

    // public function registerSSbRequiredData(Request $request)
    // {
    //     // dd("SSB Required");
    //     $ddaa = array();
    //     $results = Memberloans::select('applicant_id', 'ssb_id', 'member_loans.branch_id', 'member_loans.company_id', 'member_loans.customer_id')
    //         ->leftJoin('saving_accounts', 'applicant_id', '=', 'member_id')
    //         ->whereNull('member_id')
    //         ->where('member_loans.status', '=', 4)
    //         // ->where('member_loans.company_id', '=', 2)
    //         ->whereNotIn('member_loans.loan_type',[3,6])
    //         ->get();
    //     foreach ($results as $updateingdata) {
    //         $chk_s = SavingAccount::where('member_id', $updateingdata->applicant_id)->where('is_deleted', 0)->where('status', 1)->exists();
    //         $memberId = $updateingdata->applicant_id;
    //         $Memberloans = Memberloans::where('applicant_id', $updateingdata->applicant_id)->where('status', 4)->first();
    //         if ($Memberloans == null) {
    //             $responseq = [
    //                 'status' => 'Error',
    //                 'message' => "Account not found $updateingdata->applicant_id"
    //             ];
    //             return $responseq;
    //         }
    //         if ($chk_s) {
    //             $responseq = [
    //                 'status' => 'Error',
    //                 'message' => "saving account exists of $updateingdata->applicant_id"
    //             ];
    //             return $responseq;
    //         }
    //         $nominee_data = MemberNominee::where('member_id', $updateingdata->customer_id)->first();
    //         $dataarray = [
    //             'memberAutoId' => $updateingdata->customer_id,
    //             'company_id' => $updateingdata->company_id,
    //             'branchid' => $updateingdata->branch_id,
    //             'create_application_date' => $request->create_application_date,
    //             'fnumber' => $request->form_number,
    //             'fn_percentage' => 100,
    //             'fn_age' => $nominee_data->age,
    //             'fn_dob' => $nominee_data->dob,
    //             'fn_gender' => $nominee_data->gender,
    //             'fn_relationship' => $nominee_data->relation,
    //             'fn_first_name' => $nominee_data->name,
    //             'associatemid' => 1,
    //             'payment-mode' => 0,
    //             'amount' => 0,
    //         ];
    //         DB::beginTransaction();
    //         try {
    //             $ssbAmount = 0;
    //             $checkSSbExistinAllCompany = SavingAccount::where('customer_id', $dataarray['memberAutoId'])->count();
    //             $branch_id = $dataarray['branchid'];
    //             $getBranchCode = getBranchCode($branch_id);
    //             $branchCode = $getBranchCode->branch_code;
    //             $planDetails = \App\Models\Plans::select('id', 'plan_category_code', 'short_name')->where('plan_category_code', 'S')->whereCompanyId($dataarray['company_id'])->whereStatus(1)->first();
    //             $faCode = getPlanCode($planDetails->id);
    //             $planId = $planDetails->id;
    //             $investmentMiCode = getInvesmentMiCodeNew($planId, $branch_id);
    //             $codes = generateCode($dataarray, 'create', config('constants.PASSBOOK'), 5, $dataarray['company_id']);
    //             if (!empty($investmentMiCode)) {
    //                 $miCodeAdd = $investmentMiCode->mi_code + 1;
    //             } else {
    //                 $miCodeAdd = 1;
    //             }
    //             $miCode = str_pad($miCodeAdd, 5, '0', STR_PAD_LEFT);
    //             $passbook = $codes['passbookCode'] . $branchCode . $faCode . $miCode;
    //             // Invesment Account no
    //             $investmentAccount = $branchCode . $faCode . $miCode;
    //             $globaldate = $pdate = isset($dataarray['create_application_date']) ? date("Y-m-d H:i:s", strtotime(str_replace('/', '-', $dataarray['create_application_date']))) : date("Y-m-d H:i:s", strtotime(str_replace('/', '-', $dataarray['created_at'])));
    //             $globaldate = date("Y-m-d H:i:s");
    //             Session::put('created_at', $globaldate);
    //             $ssbAmountTrans = 0;
    //             $is_primary = 0;
    //             $ssbdata['mi_code'] = $miCode;
    //             $ssbdata['account_number'] = $investmentAccount;
    //             $ssbdata['ssb_account_number'] = $investmentAccount;
    //             $ssbdata['plan_id'] = $planId;
    //             $ssbdata['form_number'] = $dataarray['fnumber'] ?? 0;
    //             $ssbdata['member_id'] = $memberId;
    //             $ssbdata['customer_id'] = $dataarray['memberAutoId'];
    //             $ssbdata['associate_id'] = $dataarray['associatemid'];
    //             $ssbdata['branch_id'] = $branch_id;
    //             $ssbdata['old_branch_id'] = $branch_id;
    //             $ssbdata['deposite_amount'] = $ssbAmountTrans;
    //             $ssbdata['current_balance'] = $ssbAmountTrans;
    //             $ssbdata['created_at'] = $globaldate;
    //             $ssbdata['company_id'] = $dataarray['company_id'];
    //             $createSavingAccountDescriptionModify = 1;
    //             $is_app = null;
    //             $daybookRefRDrr = CommanController::createBranchDayBookReferenceNew($ssbAmountTrans, $globaldate);
    //             $res = Memberinvestments::create($ssbdata);
    //             $investmentId = $res->id;
    //             $savingAccountId = $res->account_number;
    //             $description = $planDetails->short_name . ' Account Opening';
    //             $companyId = $dataarray['company_id'];
    //             $createAccount = CommanController::createSavingAccountDescriptionModify($memberId, $branch_id, $branchCode, $ssbAmountTrans, 0, $res->id, $miCode, $investmentAccount, $is_primary, $faCode, $description, $dataarray['associatemid'], 0, $daybookRefRDrr, $dataarray['company_id'], $dataarray['memberAutoId'], $passbook, $createSavingAccountDescriptionModify);
    //             $mRes = MemberCompany::find($memberId);
    //             $mData['ssb_account'] = $investmentAccount;
    //             $mRes->update($mData);
    //             $satRefId = NULL;
    //             $ssbAccountId = $createAccount['ssb_id'];
    //             $ssbAmountNew = $ssbAmountTrans;
    //             $amountArraySsb = array('1' => $ssbAmountNew);
    //             $amount_deposit_by_name = NULL;
    //             $ssbCreateTran = NULL;
    //             $sAccount = $createAccount;
    //             if (count($sAccount) > 0) {
    //                 $ssbAccountNumber = $investmentAccount;
    //                 $ssbId = $sAccount['ssb_id'];
    //             } else {
    //                 $ssbAccountNumber = '';
    //                 $ssbId = '';
    //             }
    //             $is_app = null;
    //             $createDayBookNew_payment_mode = 0;
    //             $createDayBook = CommanController::createDayBookNew($daybookRefRDrr, $daybookRefRDrr, 2, $ssbId, $dataarray['associatemid'], $memberId, $ssbAmountTrans, $ssbAmountTrans, $withdrawal = 0, $description, $ssbAccountNumber, $branch_id, $branchCode, $amountArraySsb, $createDayBookNew_payment_mode, $amount_deposit_by_name, $memberId, $investmentAccount, NULL, NULL, NULL, $globaldate, NULL, NULL, $ssbId, 'CR', NULL, NULL, NULL, NULL, NULL, $companyId, $is_app);
    //             $type = 'create';
    //             if (isset($dataarray['fn_first_name'])) {
    //                 $transaction = $this->transactionData($satRefId, $dataarray, $investmentId, $type, $ssbCreateTran);
    //                 $res = Investmentplantransactions::create($transaction);
    //                 $ssbfndata['investment_id'] = $investmentId;
    //                 $ssbfndata['nominee_type'] = 0;
    //                 $ssbfndata['name'] = $dataarray['fn_first_name'];
    //                 //$ssbfndata['second_name'] = $dataarray['ssb_fn_second_name'];
    //                 $ssbfndata['relation'] = $dataarray['fn_relationship'];
    //                 $ssbfndata['gender'] = $dataarray['fn_gender'];
    //                 $ssbfndata['dob'] = date("Y-m-d", strtotime(str_replace('/', '-', $dataarray['fn_dob'])));
    //                 $ssbfndata['age'] = $dataarray['fn_age'];
    //                 $ssbfndata['percentage'] = $dataarray['fn_percentage'];
    //                 $res = Memberinvestmentsnominees::create($ssbfndata);
    //             }
    //             $savingAccountDetail = SavingAccount::where('account_no', $savingAccountId)->first();
    //             array_push($ddaa,$savingAccountDetail->id);
    //             $Memberloans->update(['ssb_id' => $savingAccountDetail->id]);
    //             $response = [
    //                 'status' => true,
    //                 'data' => $ddaa,
    //             ];
    //             DB::commit();
    //         } catch (\Exception $ex) {
    //             DB::rollback();
    //             $response = [
    //                 'status' => false,
    //                 'msg' => $ex->getMessage(),
    //                 'line' => $ex->getLine(),
    //             ];
    //         }
    //     }

    //     return $response;
    // }
    public function registerSSbRequiredData(Request $request)
    {
        dd("SSB Required");
        $ddaa = array();
        $results = Grouploans::select('ml.member_id as member_id', 'ml.ssb_id', 'ml.customer_id','ml.branch_id','ml.company_id')
        ->from('group_loans as ml')
        ->leftJoin('saving_accounts as s', 's.member_id', '=', 'ml.member_id')
        ->whereNull('s.member_id')
        ->where('ml.status', '=', 4)
        ->get();
        foreach ($results as $updateingdata) {
            $chk_s = SavingAccount::where('member_id', $updateingdata->member_id)->where('is_deleted', 0)->where('status', 1)->exists();
            $memberId = $updateingdata->member_id;
            $Memberloans = Grouploans::where('member_id', $updateingdata->member_id)->where('status', 4)->first();
            if ($Memberloans == null) {
                $responseq = [
                    'status' => 'Error',
                    'message' => "Account not found $updateingdata->member_id"
                ];
                return $responseq;
            }
            if ($chk_s) {
                $responseq = [
                    'status' => 'Error',
                    'message' => "saving account exists of $updateingdata->member_id"
                ];
                return $responseq;
            }
            $nominee_data = MemberNominee::where('member_id', $updateingdata->customer_id)->first();
            $dataarray = [
                'memberAutoId' => $updateingdata->customer_id,
                'company_id' => $updateingdata->company_id,
                'branchid' => $updateingdata->branch_id,
                'create_application_date' => $request->create_application_date,
                'fnumber' => $request->form_number,
                'fn_percentage' => 100,
                'fn_age' => $nominee_data->age,
                'fn_dob' => $nominee_data->dob,
                'fn_gender' => $nominee_data->gender,
                'fn_relationship' => $nominee_data->relation,
                'fn_first_name' => $nominee_data->name,
                'associatemid' => 1,
                'payment-mode' => 0,
                'amount' => 0,
            ];
            DB::beginTransaction();
            try {
                $ssbAmount = 0;
                $checkSSbExistinAllCompany = SavingAccount::where('customer_id', $dataarray['memberAutoId'])->count();
                $branch_id = $dataarray['branchid'];
                $getBranchCode = getBranchCode($branch_id);
                $branchCode = $getBranchCode->branch_code;
                $planDetails = \App\Models\Plans::select('id', 'plan_category_code', 'short_name')->where('plan_category_code', 'S')->whereCompanyId($dataarray['company_id'])->whereStatus(1)->first();
                $faCode = getPlanCode($planDetails->id);
                $planId = $planDetails->id;
                $investmentMiCode = getInvesmentMiCodeNew($planId, $branch_id);
                $codes = generateCode($dataarray, 'create', config('constants.PASSBOOK'), 5, $dataarray['company_id']);
                if (!empty($investmentMiCode)) {
                    $miCodeAdd = $investmentMiCode->mi_code + 1;
                } else {
                    $miCodeAdd = 1;
                }
                $miCode = str_pad($miCodeAdd, 5, '0', STR_PAD_LEFT);
                $passbook = $codes['passbookCode'] . $branchCode . $faCode . $miCode;
                // Invesment Account no
                $investmentAccount = $branchCode . $faCode . $miCode;
                $globaldate = $pdate = isset($dataarray['create_application_date']) ? date("Y-m-d H:i:s", strtotime(str_replace('/', '-', $dataarray['create_application_date']))) : date("Y-m-d H:i:s", strtotime(str_replace('/', '-', $dataarray['created_at'])));
                $globaldate = date("Y-m-d H:i:s");
                Session::put('created_at', $globaldate);
                $ssbAmountTrans = 0;
                $is_primary = 0;
                $ssbdata['mi_code'] = $miCode;
                $ssbdata['account_number'] = $investmentAccount;
                $ssbdata['ssb_account_number'] = $investmentAccount;
                $ssbdata['plan_id'] = $planId;
                $ssbdata['form_number'] = $dataarray['fnumber'] ?? 0;
                $ssbdata['member_id'] = $memberId;
                $ssbdata['customer_id'] = $dataarray['memberAutoId'];
                $ssbdata['associate_id'] = $dataarray['associatemid'];
                $ssbdata['branch_id'] = $branch_id;
                $ssbdata['old_branch_id'] = $branch_id;
                $ssbdata['deposite_amount'] = $ssbAmountTrans;
                $ssbdata['current_balance'] = $ssbAmountTrans;
                $ssbdata['created_at'] = $globaldate;
                $ssbdata['company_id'] = $dataarray['company_id'];
                $createSavingAccountDescriptionModify = 1;
                $is_app = null;
                $daybookRefRDrr = CommanController::createBranchDayBookReferenceNew($ssbAmountTrans, $globaldate);
                $res = Memberinvestments::create($ssbdata);
                $investmentId = $res->id;
                $savingAccountId = $res->account_number;
                $description = $planDetails->short_name . ' Account Opening';
                $companyId = $dataarray['company_id'];
                $createAccount = CommanController::createSavingAccountDescriptionModify($memberId, $branch_id, $branchCode, $ssbAmountTrans, 0, $res->id, $miCode, $investmentAccount, $is_primary, $faCode, $description, $dataarray['associatemid'], 0, $daybookRefRDrr, $dataarray['company_id'], $dataarray['memberAutoId'], $passbook, $createSavingAccountDescriptionModify);
                $mRes = MemberCompany::find($memberId);
                $mData['ssb_account'] = $investmentAccount;
                $mRes->update($mData);
                $satRefId = NULL;
                $ssbAccountId = $createAccount['ssb_id'];
                $ssbAmountNew = $ssbAmountTrans;
                $amountArraySsb = array('1' => $ssbAmountNew);
                $amount_deposit_by_name = NULL;
                $ssbCreateTran = NULL;
                $sAccount = $createAccount;
                if (count($sAccount) > 0) {
                    $ssbAccountNumber = $investmentAccount;
                    $ssbId = $sAccount['ssb_id'];
                } else {
                    $ssbAccountNumber = '';
                    $ssbId = '';
                }
                $is_app = null;
                $createDayBookNew_payment_mode = 0;
                $createDayBook = CommanController::createDayBookNew($daybookRefRDrr, $daybookRefRDrr, 2, $ssbId, $dataarray['associatemid'], $memberId, $ssbAmountTrans, $ssbAmountTrans, $withdrawal = 0, $description, $ssbAccountNumber, $branch_id, $branchCode, $amountArraySsb, $createDayBookNew_payment_mode, $amount_deposit_by_name, $memberId, $investmentAccount, NULL, NULL, NULL, $globaldate, NULL, NULL, $ssbId, 'CR', NULL, NULL, NULL, NULL, NULL, $companyId, $is_app);
                $type = 'create';
                if (isset($dataarray['fn_first_name'])) {
                    $transaction = $this->transactionData($satRefId, $dataarray, $investmentId, $type, $ssbCreateTran);
                    $res = Investmentplantransactions::create($transaction);
                    $ssbfndata['investment_id'] = $investmentId;
                    $ssbfndata['nominee_type'] = 0;
                    $ssbfndata['name'] = $dataarray['fn_first_name'];
                    //$ssbfndata['second_name'] = $dataarray['ssb_fn_second_name'];
                    $ssbfndata['relation'] = $dataarray['fn_relationship'];
                    $ssbfndata['gender'] = $dataarray['fn_gender'];
                    $ssbfndata['dob'] = date("Y-m-d", strtotime(str_replace('/', '-', $dataarray['fn_dob'])));
                    $ssbfndata['age'] = $dataarray['fn_age'];
                    $ssbfndata['percentage'] = $dataarray['fn_percentage'];
                    $res = Memberinvestmentsnominees::create($ssbfndata);
                }
                $savingAccountDetail = SavingAccount::where('account_no', $savingAccountId)->first();
                array_push($ddaa,$savingAccountDetail->id);
                $Memberloans->update(['ssb_id' => $savingAccountDetail->id]);
                $response = [
                    'status' => true,
                    'data' => $ddaa,
                ];
                DB::commit();
            } catch (\Exception $ex) {
                DB::rollback();
                $response = [
                    'status' => false,
                    'msg' => $ex->getMessage(),
                    'line' => $ex->getLine(),
                ];
            }
        }

        return $response;
    }
    public function transactionData($satRefId, $dataarray, $investmentId, $type, $transactionId)
    {
        $branch_id = $dataarray['branchid'];
        $getBranchCode = getBranchCode($branch_id);
        $branchCode = $getBranchCode->branch_code;
        $creatAt = Session::get('created_at');
        $sAccount = $this->getMemberId($dataarray['memberAutoId'])->with(['savingAccount:id,account_no,balance,member_id'])->first();
        $data['transaction_id'] = $transactionId;
        $data['transaction_ref_id'] = $satRefId;
        $data['investment_id'] = $investmentId;
        $data['plan_id'] = isset($dataarray['investmentplan']) ? $dataarray['investmentplan'] : 1;
        $data['member_id'] = $dataarray['memberAutoId'];
        $data['branch_id'] = $branch_id;
        $data['branch_code'] = $branchCode;
        $data['deposite_amount'] = $dataarray['amount'];
        $data['deposite_date'] = date('Y-m-d');
        $data['deposite_month'] = date('m');
        $data['payment_mode'] = 0;
        if (isset($sAccount['savingAccount'][0])) {
            $data['saving_account_id'] = $sAccount->id;
        } else {
            $data['saving_account_id'] = NULL;
        }
        $data['created_at'] = $creatAt;
        return $data;
    }
    public function getMemberId($customerId)
    {
        $columns = ['id', 'member_id'];
        $getMember = $this->member->whereId($customerId)->whereStatus(1)->where('is_block', 0);
        return $getMember;
    }
}
