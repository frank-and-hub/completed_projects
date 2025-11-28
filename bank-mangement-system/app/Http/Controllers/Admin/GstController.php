<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Requests\GstRequest;
use App\Http\Requests\GstEditRequest;

use App\Http\Controllers\Controller;
use App\Http\Requests\HeadSettingRequest;
use App\Interfaces\RepositoryInterface;
use App\Models\{GstSetting, HeadSetting, Branch, AccountHeads, SamraddhBank, SamraddhBankAccount, GstPayable, GstTransfer, AllHeadTransaction, Files, States, GstSetOff, Member};
use DB;
use URL;
use Auth;
use Session;

class GstController extends Controller
{
    private $repository;
    public function __construct(RepositoryInterface $repository)
    {
        $this->repository = $repository;
    }
    public function gst_setting_form(Request $req)
    {
        if (check_my_permission(Auth::user()->id, "273") != "1") {

            return redirect()->route('admin.dashboard');

        }
        $data['title'] = 'GST Setting';
        $data['states'] = \App\Models\States::select('id', 'name', 'gst_code')->get();

        return view('templates.admin.gst.gst_setting_form', $data);
    }

    public function gst_setting_save(GstRequest $req)
    {
        //Store Data in DB
        DB::beginTransaction();
        try {
            $data = [
                'gst_no' => $req->gst_no,
                'state_id' => $req->state_id,
                'applicable_date' => date('Y-m-d', strtotime(convertDate($req->applicable_date))),
                'end_date' => date('Y-m-d', strtotime(convertDate($req->end_date))),
                'category' => $req->category,
            ];

            $createData = GstSetting::create($data);
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('alert', $ex->getMessage());
        }
        return redirect()->route('admin.gst.gst_setting_listing')->with('success', 'Gst Setting Generated Successfully');

    }
    public function gst_paypal_setting()
    {
        $data['title'] = "GST Listing";
        $data['branch'] = Branch::where('status', 1)->get();
        $data['Heads'] = AccountHeads::where('parent_id', 298)->pluck('sub_head', 'head_id');
        // $data['Heads'] = AccountHeads::whereIn('head_id', [169,170,171,172,308])->pluck('sub_head','head_id');
        $data['SamraddhBanks'] = SamraddhBank::where('status', 1)->get();
        $data['SamraddhBankAccounts'] = SamraddhBankAccount::where('status', 1)->get();
        $data['view'] = 0;
        return view('templates.admin.gst.gst_payable', $data);
    }
    public function index(Request $req)
    {
        if (check_my_permission(Auth::user()->id, "267") != "1") {

            return redirect()->route('admin.dashboard');

        }
        $data['title'] = "GST Setting Listing";
        return view('templates.admin.gst.gst_lisiting', $data);
    }



    public function setting_listing(Request $request)
    {

        if ($request->ajax()) {

            $data = GstSetting::has('company')->orderBy('created_at', 'desc');
            $count = $data->count();
            $data = $data->offset($_POST['start'])->limit($_POST['length'])->get();
            $rowReturn = array();
            $totalCount = $count;

            foreach ($data as $sno => $value) {

                $sno++;
                $val['DT_RowIndex'] = $sno;
                $val['gst_number'] = $value->gst_no;
                $val['state'] = $value->state->name;
                $val['application_date'] = date('d/m/Y', strtotime($value->applicable_date));
                $cateGory = 'N/A';
                if ($value->category == 0) {
                    $cateGory = '<span class="badge badge-primary">Main</span>';
                } else {
                    $cateGory = '<span class="badge badge-secondary">ISD</span>';
                    ;
                }
                $val['category'] = $cateGory;
                $btn = '';
                $url = URL::to("admin/edit/gst_setting/" . $value->id . "");
                $btn .= '<a href="' . $url . '" title="Edit Gst Setting"><i class="fa fa-edit mr-2" aria-hidden="true"></i></a>';
                $val['action'] = $btn;
                $rowReturn[] = $val;
            }

            $output = array("draw" => $_POST['draw'], "recordsTotal" => $totalCount, "recordsFiltered" => $totalCount, "data" => $rowReturn, );

            return json_encode($output);


        }
    }


    //Set Gst Head Setting

    public function HeadSettingform()
    {
        if (check_my_permission(Auth::user()->id, "274") != "1") {

            return redirect()->route('admin.dashboard');

        }
        $data['title'] = 'GST Head Setting';
        $data['heads'] = \App\Models\AccountHeads::whereIn('head_id', ['33', '90', '294', '35', '139', '203', '122'])->get();
        return view('templates.admin.gst.head_setting', $data);
    }

    public function headSettingSave(HeadSettingRequest $req)
    {
        DB::beginTransaction();

        try {
            $data = [
                'head_id' => $req->head_id,
                'gst_percentage' => $req->gst_percentage,
            ];
            $createData = HeadSetting::create($data);
            DB::commit();

        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('alert', $ex->getMessage());
        }
        return redirect()->route('admin.gst.head.setting_list')->with('success', 'Gst Head  Setting Generated Successfully');

    }

    public function headSetting()
    {
        if (check_my_permission(Auth::user()->id, "268") != "1") {

            return redirect()->route('admin.dashboard');

        }
        $data['title'] = 'Head Setting List';
        return view('templates.admin.gst.head_setting_list', $data);
    }

    public function head_setting_listing(Request $request)
    {
        if ($request->ajax()) {

            $data = HeadSetting::orderBy('created_at', 'desc');
            $count = $data->count();
            $data = $data->offset($_POST['start'])->limit($_POST['length'])->get();
            $rowReturn = array();
            $totalCount = $count;

            foreach ($data as $sno => $value) {

                $sno++;
                $val['DT_RowIndex'] = $sno;
                $val['head_name'] = $value->HeadDetail->sub_head;
                $val['gst_percentage'] = $value->gst_percentage . '%';
                $btn = '';
                $url = URL::to("admin/edit/head_setting/" . $value->id . "");
                $btn .= '<a href="' . $url . '" title="Edit Gst Setting"><i class="fa fa-edit mr-2" aria-hidden="true"></i></a>';
                $val['action'] = $btn;
                $rowReturn[] = $val;
            }

            $output = array("draw" => $_POST['draw'], "recordsTotal" => $totalCount, "recordsFiltered" => $totalCount, "data" => $rowReturn, );

            return json_encode($output);


        }
    }

    /**
     * Edit Gst Setting
     * @param Id
     */

    public function edit_gst_setting_form($id)
    {
        $data['record'] = GstSetting::find($id);
        $data['title'] = "Edit Gst";
        $data['states'] = \App\Models\States::get();
        return view('templates.admin.gst.gst_setting_form', $data);
    }

    /**
     * Updat eRecord
     */

    public function gst_setting_update(GstEditRequest $req)
    {
        $updateData = GstSetting::find($req->edit_id);

        $updateRecord = [
            'gst_no' => $req->gst_no,
            'state_id' => $req->state_id,
            'applicable_date' => date('Y-m-d', strtotime(convertDate($req->applicable_date))),
            'end_date' => date('Y-m-d', strtotime(convertDate($req->end_date))),
            'category' => $req->category,
        ];
        $updateData->update($updateRecord);
        return redirect()->route('admin.gst.gst_setting_listing')->with('success', 'Update Record Successfully!', 'Success');
    }

    /**
     * Head Setting Edit Form call
     * @param id 
     */

    public function edit_head_setting_form($id)
    {
        $data['title'] = 'GST Head Setting';
        $data['heads'] = \App\Models\AccountHeads::whereIn('head_id', ['33', '90', '294', '35', '139', '203', '122'])->get();
        $data['record'] = HeadSetting::find($id);
        return view('templates.admin.gst.head_setting', $data);

    }

    /**
     * Update Head Setting Record 
     */

    public function head_setting_update(Request $request)
    {
        $updateData = HeadSetting::find($request->edit_id);
        $updateRecord = [

            'gst_percentage' => $request->gst_percentage,
        ];
        $updateData->update($updateRecord);
        return redirect()->route('admin.gst.head.setting_list')->with('success', 'Update Record Successfully!', 'Success');
    }

    /**
     * check charge on a state
     */

    public function checkgstCharge(Request $request)
    {
        if ($request->branchId != '0') {
            $detail = getBranchDetail($request->branchId)->state_id;
            if ($request->memberid) {
                $member = Member::where('member_id', $request->memberid)->has('memberCompany')->with([
                    'memberCompany:id,customer_id,member_id,branch_id',
                    'memberCompany.branch:id,state_id,name'
                ])->first();
                $amount = 50;
                $getHeadSetting = HeadSetting::where('head_id', 122)->first();
                $detailss = $member['memberCompany']->branch ? $member['memberCompany']->branch->state_id : $detail;
                if (isset($request->type) && ($request->type != 1)) {
                    $globaldate = checkMonthAvailability(date('d'), date('m'), date('Y'), $detail);
                } else {
                    $globaldate = date('Y-m-d', strtotime(convertDate($request->date)));
                }
                $getGstSetting = GstSetting::where('state_id', $detailss)->where('applicable_date', '<=', $globaldate)->exists();
                if (isset($getHeadSetting->gst_percentage) && $getGstSetting) {
                    if ($detail == $detailss) {
                        $gstAmount = (($amount * $getHeadSetting->gst_percentage) / 100) / 2;
                        $IntraState = true;
                    } else {
                        $gstAmount = ($amount * $getHeadSetting->gst_percentage) / 100;
                        $IntraState = false;
                    }
                    $msg = true;
                } else {
                    $IntraState = '';
                    $msg = false;
                    $gstAmount = 0;
                }
            } else {
                $IntraState = '';
                $msg = false;
                $gstAmount = 0;
            }
        } else {
            $IntraState = '';
            $msg = false;
            $gstAmount = 0;
        }
        return response()->json(['IntraState' => $IntraState, 'gstAmount' => $gstAmount, 'msg' => $msg]);


    }
    public function gst_payable_listing(Request $request)
    {
        if ($request->ajax()) {
            $arrFormData = array();
            if (!empty($_POST['searchform'])) {
                foreach ($_POST['searchform'] as $frm_data) {
                    $arrFormData[$frm_data['name']] = $frm_data['value'];
                }
            }
            $company_id = $arrFormData['company_id'];
            $data = AllHeadTransaction::has('company')->whereIn('head_id', [170, 171, 172])
                ->select('id', 'created_at', 'head_id', 'member_id', 'branch_id', 'daybook_ref_id', 'amount', 'payment_type', 'company_id')
                ->with([
                    'member:id,member_id,first_name,last_name',
                    'member.memberIdProof:id,first_id_no,second_id_no,member_id,first_id_type_id,second_id_type_id',
                    'AccountHeads:id,head_id,sub_head,cr_nature',
                    'branch:id,name',
                    'company:id,name,short_name'
                ])
                ->where('payment_type', 'CR')
                ->where('is_deleted', 0);
            /******* fillter query start ****/
            if (isset($arrFormData['is_search']) && $arrFormData['is_search'] == 'yes') {
                if (isset($arrFormData['branch_id']) && $arrFormData['branch_id'] != '') {
                    $id = $arrFormData['branch_id'];
                    if ($id != '0') {
                        $data = $data->where('branch_id', $id);
                    }
                }
                if (isset($arrFormData['company_id']) && $arrFormData['company_id'] != '') {
                    $company_id = $arrFormData['company_id'];
                    if ($company_id != '0') {
                        $data = $data->where('company_id', $company_id);
                    }
                }
                if ($arrFormData['start_date'] != '') {
                    $startDate = date("Y-m-d", strtotime(convertDate($arrFormData['start_date'])));
                    if ($arrFormData['end_date'] != '') {
                        $endDate = date("Y-m-d", strtotime(convertDate($arrFormData['end_date'])));
                    } else {
                        $endDate = '';
                    }
                    $data = $data->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate]);
                }
            } else {
                $data = $data->where('id', 0);
            }
            /******* fillter query End ****/
            $count = $data->count('id');
            $totalAmount = 0;
            $totalAmountData = $data->limit($_POST['start'])->orderby('id', 'DESC')->get();
            foreach ($totalAmountData as $item) {
                $totalAmount = ($totalAmount + (float) $item->amount) - (float) getTdsDrAmount($item->daybook_ref_id, $item->head_id);
            }
            $data = $data->orderby('id', 'DESC')->offset($_POST['start'])->limit($_POST['length'])->get();
            $totalCount = $count; //AllTransaction::where('payment_type','CR')->count('id');
            $sno = $_POST['start'];
            $rowReturn = array();
            foreach ($data as $row) {
                $sno++;
                $getTdsDrAmount = getTdsDrAmount($row->daybook_ref_id, $row->head_id);
                $totalAmount = ($totalAmount + (float) $row->amount) - (float) $getTdsDrAmount;
                $val = [
                    'DT_RowIndex' => $sno,
                    'created_date' => date("d/m/Y", strtotime(convertDate($row->created_at))),
                    'company' => $row->company ? $row->company->short_name : 'N/A',
                    'branch' => $row->branch ? $row->branch->name : 'N/A',
                    'head' => $row['AccountHeads'] ? $row['AccountHeads']->sub_head : 'N/A',
                    'name' => $row['member'] ? $row['member']->first_name . ' ' . $row['member']->last_name ?? '' : 'N/A',
                    'customer_id' => $row['member'] ? $row['member']->member_id : 'N/A',
                    'dr_entry' => number_format($getTdsDrAmount, 2),
                    'cr_entry' => number_format($row->amount, 2),
                    'balance' => number_format($totalAmount, 2),
                ];
                $rowReturn[] = $val;
            }
            $output = array("draw" => $_POST['draw'], "recordsTotal" => $totalCount, "recordsFiltered" => $count, "data" => $rowReturn);
            return json_encode($output);
        }
    }
    public function add_gst_payable(Request $request)
    {
        $data['title'] = 'GST Transfer Request';
        $data['branch'] = Branch::where('status', 1)->get();
        $data['gstHeads'] = AccountHeads::where('parent_id', 298)->pluck('sub_head', 'head_id');
        $data['allState'] = GstSetting::with(['State:id,name'])->get(['state_id'])->groupBy('state_id')->toArray();
        $data['view'] = 0;
        return view('templates.admin.gst.add_gst_payable', $data);
    }
    // to get all gst ammount on selected date
    public function getgstPayableAmount(Request $request)
    {
        $startDate = date("Y-m-d", strtotime(convertDate($request->startDate)));
        $start = date("Y-m-d H:i:s", strtotime(convertDate($request->startDate)));
        $endDate = date("Y-m-d", strtotime(convertDate($request->endDate)));
        $end = date("Y-m-d H:i:s", strtotime(convertDate($request->endDate)));
        $companyId = $request->companyId;
        $stateId = $request->stateId;
        $branches = Branch::with([
            'companybranchs' => function ($q) use ($companyId) {
                $q->whereCompanyId($companyId)->where('status', '1')->get();
            }
        ])->where('state_id', $stateId)->pluck('id');
        $checkStartDate = GstPayable::whereDate('from_date', '<=', $startDate)
            ->where('state_id', $stateId)
            ->whereDate('to_date', '>=', $startDate)
            ->where('state_id', $stateId)
            ->where('company_id', $companyId)
            ->whereIn('gst_head_id', [169])
            ->where('is_deleted', 0)
            ->count();
        $checkEndDate = GstPayable::whereDate('from_date', '<=', $endDate)
            ->where('state_id', $stateId)
            ->whereDate('to_date', '>=', $endDate)
            ->where('company_id', $companyId)
            ->whereIn('gst_head_id', [169])
            ->where('is_deleted', 0)
            ->count();
        $checkGstStartDate = GstTransfer::where(function ($query) use ($start, $stateId, $companyId) {
            $query->whereDate('start_date', '<=', $start)->whereDate('end_date', '>=', $start)
                ->where('state_id', $stateId)
                ->whereIn('head_id', [169])
                ->where('company_id', $companyId)
                ->where('deleted_at', NULL);
        })->orWhereBetween(\DB::raw('DATE(start_date)'), [$start, $end])
            ->where('state_id', $stateId)
            ->whereIn('head_id', [169])
            ->where('company_id', $companyId)
            ->where('deleted_at', NULL);
        $checkGstEndDate = GstTransfer::where(function ($query) use ($end, $start, $stateId, $companyId) {
            $query->whereDate('start_date', '>=', $end)->whereDate('end_date', '<=', $end)
                ->where('state_id', $stateId)
                ->whereIn('head_id', [169])
                ->where('company_id', $companyId)
                ->where('deleted_at', NULL);
        })->orWhereBetween(\DB::raw('DATE(end_date)'), [$start, $end])
            ->where('state_id', $stateId)
            ->whereIn('head_id', [169])
            ->where('company_id', $companyId)
            ->where('deleted_at', NULL)
            ->count();
        $cr = AllHeadTransaction::whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate])
            ->whereNotIn('type', [9])
            ->whereNotIn('sub_type', [94, 95])
            ->whereIn('branch_id', $branches)
            ->where('is_deleted', 0)
            ->where(['payment_type' => 'CR', 'company_id' => $companyId, 'is_deleted' => 0])
            ->selectRaw(
                'SUM(CASE WHEN head_id IN (170, 171, 172) THEN amount ELSE 0 END) AS total,
                SUM(CASE WHEN head_id = 171 THEN amount ELSE 0 END) AS amtCgstCr,
                SUM(CASE WHEN head_id = 170 THEN amount ELSE 0 END) AS amtIgstCr,
                SUM(CASE WHEN head_id = 172 THEN amount ELSE 0 END) AS amtSgstCr'
            )
            ->first();
        $dr = AllHeadTransaction::whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate])
            ->whereNotIn('type', [9])
            ->whereNotIn('sub_type', [94, 95])
            ->whereIn('branch_id', $branches)
            ->where('is_deleted', 0)
            ->where(['payment_type' => 'DR', 'company_id' => $companyId, 'is_deleted' => 0])
            ->selectRaw(
                'SUM(CASE WHEN head_id IN (170, 171, 172) THEN amount ELSE 0 END) AS total,
                SUM(CASE WHEN head_id = 171 THEN amount ELSE 0 END) AS amtCgstDr,
                SUM(CASE WHEN head_id = 170 THEN amount ELSE 0 END) AS amtIgstDr,
                SUM(CASE WHEN head_id = 172 THEN amount ELSE 0 END) AS amtSgstDr'
            )
            ->first();
        $gsttransferrequest = GstTransfer::where('deleted_at', NULL)->where('company_id', $companyId)
            ->whereIn('head_id', [169])
            ->whereDate('start_date', '<=', $endDate)
            ->where('deleted_at', NULL)
            ->where('state_id', $stateId)
            ->count();
        $totalDr = number_format((float) $cr->total, 2, '.', '');
        $totalCr = number_format((float) $dr->total, 2, '.', '');
        $amtIgstCr = number_format((float) $cr->amtIgstCr, 2, '.', '');
        $amtCgstCr = number_format((float) $cr->amtCgstCr, 2, '.', '');
        $amtSgstCr = number_format((float) $cr->amtSgstCr, 2, '.', '');
        $amtIgstDr = number_format((float) $dr->amtIgstDr, 2, '.', '');
        $amtCgstDr = number_format((float) $dr->amtCgstDr, 2, '.', '');
        $amtSgstDr = number_format((float) $dr->amtSgstDr, 2, '.', '');
        $return_array = compact('checkStartDate', 'checkEndDate', 'checkGstEndDate', 'checkGstStartDate', 'amtSgstCr', 'amtSgstDr', 'amtCgstCr', 'amtCgstDr', 'amtIgstCr', 'amtIgstDr', 'totalDr', 'totalCr');
        // dd($checkStartDate,$checkEndDate);
        return json_encode($return_array);
    }
    public function gst_transfer_listing(Request $request)
    {
        if ($request->ajax()) {
            $arrFormData = [];
            if (!empty($_POST['searchform'])) {
                foreach ($_POST['searchform'] as $frm_data) {
                    $arrFormData[$frm_data['name']] = $frm_data['value'];
                }
            }
            $data = GstTransfer::has('company')->whereNull('deleted_at')
                ->with([
                    'company:id,name,short_name',
                    'AllHeadTransaction:id,head_id,daybook_ref_id,branch_id,amount,description,payment_type,payment_mode,entry_date,entry_time,is_deleted,company_id,created_by_id',
                    'AllHeadTransaction.AccountHeads:id:head_id,sub_head',
                    'payable',
                    'payable.challan',
                    'state:id,name'
                ]);
            if (isset($arrFormData['is_search']) && $arrFormData['is_search'] == 'yes') {
                /******* fillter query start ****/
                if (isset($arrFormData['company_id']) && $arrFormData['company_id'] != '') {
                    $company_id = $arrFormData['company_id'];
                    if ($company_id != '0') {
                        $data = $data->whereCompanyId($company_id);
                    }
                }
                if ($arrFormData['state'] != '') {
                    $state = $arrFormData['state'];
                    $data = $data->whereHas('state', function ($query) use ($state) {
                        $query->whereId($state);
                    });
                }
                if ($arrFormData['is_paid'] != '') {
                    $is_paid = $arrFormData['is_paid'];
                    $data = $data->whereIsPaid($is_paid);
                }
                if ($arrFormData['start_date'] != '') {
                    $start_date = $arrFormData['start_date'];
                    $end_date = $enddate = $arrFormData['end_date'];
                    if ($enddate) {
                        $end_date = date("Y-m-d H:i:s", strtotime(convertDate($end_date)));
                    }
                    $start_date = date("Y-m-d H:i:s", strtotime(convertDate($start_date)));
                    $data = $data->where(function ($query) use ($start_date, $end_date) {
                        $query->whereDate('start_date', '>=', $start_date)
                            ->when($end_date, function ($query) use ($end_date) {
                                $query->whereDate('end_date', '<=', $end_date);
                            });
                    });
                }
                // if (($arrFormData['start_date'] != '') && ($arrFormData['end_date'] != '')) {
                //     $start_date = date("Y-m-d H:i:s", strtotime(convertDate($arrFormData['start_date'])));
                //     $end_date = date("Y-m-d H:i:s", strtotime(convertDate($arrFormData['end_date'])));
                //     $data = $data->where(function($query)use($end_date,$start_date){
                //         $query->WhereBetween('start_date',[$start_date,$end_date])
                //             ->orWhereBetween('end_date',[$start_date,$end_date])
                //             ;
                //     });
                // }
            }
            // $data->dd();
            $count = $data->count('id');
            $data = $data->orderby('id', 'DESC')->offset($_POST['start'])->limit($_POST['length'])->get();
            $totalCount = $count;
            $sno = $_POST['start'];
            $rowReturn = array();
            foreach ($data as $row) {
                $sum = ($row->igst_amt ?? 0) + ($row->cgst_amt ?? 0) + ($row->sgst_amt ?? 0);
                $sno++;
                $btn = '';
                $urlImage = $row->payable ? ImageUpload::generatePreSignedUrl('gst-payable/challan/' . ($row->payable->challan->file_name)) : '';
                $pay = '<a title="Pay Gst" class="text-dark pay_data btn btn-white w-100 legitRipple text-left dropdown-item" ' . ($sum == 0 ? "onclick=zero_gst_amount()" : "href=" . route('admin.gst_transfer_pay', [$row->company_id, $row->id]) . ' ') . ' ><i class="fa fa-credit-card  mr-2"></i>Pay</a>';
                $view = '<a title="View Detail" class="text-dark view_details btn btn-white w-100 legitRipple text-left dropdown-item" href="' . route('admin.gst_transfer_pay.view', [$row->company_id, $row->id]) . '" target=_blank ><i class="fa fa-eye  mr-2"></i>View Detail</a>';
                $viewfile = $row->payable ? '<a href="' . $urlImage . '" title="Vew File" target="_blank" class="">' . ($row->payable->challan->file_name) . '</a>' : 'N/A';
                $download = '<div class="download_data btn btn-white w-100 legitRipple text-left dropdown-item" data-name="' . (string) ($row->payable ? $row->payable->challan->file_name : '') . '" data-path="' . $urlImage . '"><i class="fa fa-download  mr-2"></i>Download File</div>';
                $btn .= '<div class="list-icons"><div class="dropdown"><a href="#" class="list-icons-item" data-toggle="dropdown"><i class="icon-menu9"></i></a><div class="dropdown-menu dropdown-menu-right">';
                $btn .= empty($row->payable) ? $pay : '';
                // $btn .= !empty($row->payable) ? $view . $download : '';
                $btn .= !empty($row->payable) ? $view : '';
                $btn .= '</div></div></div>';
                $val = [
                    'DT_RowIndex' => $sno,
                    'transfer_date' => date('d/m/Y', strtotime($row->transfer_date)),
                    'date_range' => date("d/m/Y", strtotime(convertDate($row->start_date))) . ' - ' . date("d/m/Y", strtotime(convertDate($row->end_date))),
                    'state' => $row->state->name,
                    'igst_amt' => '&#8377 ' . number_format((float) $row->igst_amt ?? 0, 2, '.', ''),
                    'cgst_amt' => '&#8377 ' . number_format((float) $row->cgst_amt ?? 0, 2, '.', ''),
                    'sgst_amt' => '&#8377 ' . number_format((float) $row->sgst_amt ?? 0, 2, '.', ''),
                    'set_off_Igst' => '&#8377 ' . number_format((float) $row->set_off_Igst ?? 0, 2, '.', ''),
                    'set_off_cgst' => '&#8377 ' . number_format((float) $row->set_off_cgst ?? 0, 2, '.', ''),
                    'set_off_sgst' => '&#8377 ' . number_format((float) $row->set_off_sgst ?? 0, 2, '.', ''),
                    'final_igst' => '&#8377 ' . number_format((float) $row->final_igst ?? 0, 2, '.', ''),
                    'final_cgst' => '&#8377 ' . number_format((float) $row->final_cgst ?? 0, 2, '.', ''),
                    'final_sgst' => '&#8377 ' . number_format((float) $row->final_sgst ?? 0, 2, '.', ''),
                    'transfer_amount' => '&#8377 ' . number_format((float) ($row->igst_amt + $row->cgst_amt + $row->sgst_amt), 2, '.', ''),
                    'penalty_amount' => '&#8377 ' . number_format((float) (!empty($row->payable) ? (($row->payable->paid_amount) - ($row->payable->late_panelty)) : 0), 2, '.', ''),
                    'neft_charge' => '&#8377 ' . number_format((float) (!empty($row->payable) ? ($row->payable->neft_charge) : 0), 2, '.', ''),
                    'late_panelty' => '&#8377 ' . number_format((float) (!empty($row->payable) ? ($row->payable->late_panelty) : 0), 2, '.', ''),
                    'total_payable_amount' => '&#8377 ' . number_format((float) (!empty($row->payable) ? ($row->payable->paid_amount + $row->payable->neft_charge) : 0), 2, '.', ''),
                    'payment_date' => !empty($row->payable) ? date('d/m/Y', strtotime($row->payable->payment_date)) : 'N/A',
                    'to_paid' => $row->is_paid == 0 ? 'No' : 'Yes',
                    'company' => $row->company ? $row->company->short_name : 'N/A',
                    'file' => $row->payable ? $viewfile : 'N/A',
                    'action' => $btn,
                ];
                $rowReturn[] = $val;
            }
            $output = array("draw" => $_POST['draw'], "recordsTotal" => $totalCount, "recordsFiltered" => $count, "data" => $rowReturn);
            return json_encode($output);
        }
    }
    public function gst_transfer_pay(Request $request, $companyId, $id)
    {
        $data['title'] = 'Gst Payable';
        $data['branch'] = Branch::where('status', 1)->get();
        $data['gstHeads'] = AccountHeads::where('parent_id', 22)->pluck('sub_head', 'head_id');
        ;
        $data['SamraddhBanks'] = SamraddhBank::where('status', 1)->whereCompanyId($companyId)->pluck('bank_name', 'id');
        $data['companyId'] = $companyId;
        $details = GstTransfer::whereId($id)->with('payable')->whereCompanyId($companyId)->where('deleted_at', NULL)->first();
        if ((!$details) || ($details->is_paid == '1')) {
            return back();
        }
        if (!empty($details)) {
            $i = $details->igst_amt ?? 0;
            $c = $details->cgst_amt ?? 0;
            $s = $details->sgst_amt ?? 0;
            if (($i + $c + $s) == 0) {
                return back();
            }
        }
        $data['allState'] = GstSetting::with(['State:id,name'])->get(['state_id'])->groupBy('state_id')->toArray();
        $data['SamraddhBankAccounts'] = SamraddhBankAccount::where('status', 1)->get(['id', 'bank_id', 'account_no', 'status']);
        $data['startDate'] = $details->start_date;
        $data['endDate'] = $details->end_date;
        $data['payable_igst_amount'] = empty($details) ? 0 : $details->igst_amt;
        $data['payable_cgst_amount'] = empty($details) ? 0 : $details->cgst_amt;
        $data['payable_sgst_amount'] = empty($details) ? 0 : $details->sgst_amt;
        $data['daybook_diff'] = $details->daybook_ref_id ?? 0;
        $data['state_id'] = $details->state_id;
        $data['id'] = $id;
        $data['view'] = 0;
        return view('templates.admin.gst.add_gst_payable_new', $data);
    }
    public function gst_transfer_view(Request $request, $companyId, $id)
    {
        $data['title'] = 'Gst Payable Details';
        $data['branch'] = Branch::where('status', 1)->get();
        $data['gstHeads'] = AccountHeads::where('parent_id', 22)->pluck('sub_head', 'head_id');
        ;
        $data['SamraddhBanks'] = SamraddhBank::where('status', 1)->whereCompanyId($companyId)->pluck('bank_name', 'id');
        $data['companyId'] = $companyId;
        $details = GstTransfer::whereId($id)->with(['payable', 'payable.challan', 'state:id,name'])->whereCompanyId($companyId)->first();
        if (!$details) {
            return back();
        }
        if (!empty($details)) {
            $i = $details->igst_amt ?? 0;
            $c = $details->cgst_amt ?? 0;
            $s = $details->sgst_amt ?? 0;
            if (($i + $c + $s) == 0) {
                return back();
            }
        }
        $payable = $details->payable;
        if (!$payable) {
            return back();
        }
        $challan = $details->payable->challan;
        $data['allState'] = GstSetting::with(['State:id,name'])->get(['state_id'])->groupBy('state_id')->toArray();
        $data['SamraddhBankAccounts'] = SamraddhBankAccount::where('status', 1)->get(['id', 'bank_id', 'account_no', 'status']);
        $SamraddhBankAccount = SamraddhBankAccount::pluck('account_no', 'id');
        $data['startDate'] = $details->start_date;
        $data['endDate'] = $details->end_date;
        // $data['head_id'] = $details->head_id;
        $data['daybook_diff'] = $details->daybook_ref_id;
        $data['id'] = $id;
        $data['view'] = 1;
        $data['state_id'] = $details->state->id;
        $data['payment_date'] = date('d/m/Y', strtotime($payable->payment_date));
        $data['late_panelty'] = $payable ? ($payable->late_panelty) : 0;
        $data['total_paid'] = number_format((float) ($payable->paid_amount ?? 0), 2, '.', '');
        $data['bank_id'] = $payable->bank_id;
        $data['remark'] = $payable->remark ?? 'N/A';
        $data['payable_igst_amount'] = $payable->igst_amount ?? 0;
        $data['payable_cgst_amount'] = $payable->cgst_amount ?? 0;
        $data['payable_sgst_amount'] = $payable->sgst_amount ?? 0;
        $data['neft_charge'] = number_format((float) $payable->neft_charge, 2, '.', '');
        $data['transaction_number'] = $payable->transaction_number ?? 'N/A';
        $data['account_no'] = $SamraddhBankAccount[$payable->bank_id];
        $data['bank_available_balance'] = checkBankBalance((object) [
            'account_id' => $payable->account_id,
            'bank_id' => $payable->bank_id,
            'company_id' => $payable->company_id,
            'entry_date' => $payable->created_at,
        ]);
        $data['ChalanFile'] = $challan->file_name;
        $data['ChalanSrc'] = ImageUpload::generatePreSignedUrl('gst-payable/challan/' . $challan->file_name);
        return view('templates.admin.gst.add_gst_payable_new', $data);
    }
    public function export_gst_payable(Request $request)
    {
        if ($request->gst_payable_export == 0) {
            $input = $request->all();
            $start = $input["start"];
            $limit = $input["limit"];
            $balance = 0;
            $_fileName = Session::get('_fileName');
            $returnURL = URL::to('/') . "/asset/gst_payable_list" . $_fileName . ".csv";
            $fileName = env('APP_EXPORTURL') . "asset/gst_payable_list" . $_fileName . ".csv";
            global $wpdb;
            $postCols = array(
                'post_title',
                'post_content',
                'post_excerpt',
                'post_name',
            );
            header("Content-type: text/csv");
        }
        $token = Session::get('_token');
        $data = AllHeadTransaction::has('company')->whereIn('head_id', [170, 171, 172])
            ->select('id', 'created_at', 'head_id', 'member_id', 'branch_id', 'daybook_ref_id', 'amount', 'payment_type', 'company_id')
            ->with([
                'member:id,member_id,first_name,last_name',
                'member.memberIdProof:id,first_id_no,second_id_no,member_id,first_id_type_id,second_id_type_id',
                'AccountHeads:id,head_id,sub_head,cr_nature',
                'branch:id,name',
                'company:id,name,short_name'
            ])
            ->where('payment_type', 'CR')
            ->where('is_deleted', 0);
        /******* fillter query start ****/
        if (isset($request->is_search) && $request->is_search == 'yes') {
            if (isset($request->branch_id) && $request->branch_id != '') {
                $id = $request->branch_id;
                $data = $data->where('branch_id', $id);
            }
            if (isset($request->company_id) && $request->company_id != '') {
                $company_id = $request->company_id;
                if ($company_id != '0') {
                    $data = $data->where('company_id', $company_id);
                }

            }
            if ($request->start_date != '') {
                $startDate = date("Y-m-d", strtotime(convertDate($request->start_date)));
                if ($request->end_date != '') {
                    $endDate = date("Y-m-d", strtotime(convertDate($request->end_date)));
                } else {
                    $endDate = '';
                }
                $data = $data->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate]);
            }
        } else {
            $data = $data->where('id', 0);
        }
        $count = $data->count('id');
        $totalResults = $count;
        $totalAmountData = $data->limit($start)->orderby('id', 'DESC')->get();
        foreach ($totalAmountData as $item) {
            $balance = ($balance + (float) $item->amount) - (float) getTdsDrAmount($item->daybook_ref_id, $item->head_id);
        }
        $results = $data->offset($start)->limit($limit)->orderby('id', 'DESC')->get()->toArray();
        // $results = array_slice($data, $start, $count);
        // $bal = array_slice($data, 0, $start);
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
        // foreach ($bal as $row) {
        //     $balance = ($balance + (float) $row['amount']) - (float) getTdsDrAmount($row['daybook_ref_id'], $row['head_id']);
        // }
        foreach ($results as $row) {
            $sno++;
            $getTdsDrAmount = getTdsDrAmount($row['daybook_ref_id'], $row['head_id']);
            $balance = ($balance + (float) $row['amount']) - (float) $getTdsDrAmount;
            $val = [
                'S/N' => $sno,
                'CREATED DATE' => date("d/m/Y", strtotime(convertDate($row['created_at']))),
                'COMPANY' => $row['company']['name'] ?? 'N/A',
                'BRANCH' => $row['branch']['name'] ?? 'N/A',
                'HEAD NAME' => $row['account_heads'] ? $row['account_heads']['sub_head'] : 'N/A',
                'CUSTOMER NAME' => $row['member'] ? $row['member']['first_name'] . ' ' . $row['member']['last_name'] ?? '' : 'N/A',
                'CUSTOMER ID' => isset($row['member']) ? ($row['member']['member_id']) : 'N/A',
                'DR' => number_format($getTdsDrAmount, 2),
                'CR' => number_format($row['amount'], 2),
                'BALANCE' => number_format($balance, 2),
            ];
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
            'limit' => $count,
            'totalResults' => $totalResults,
            'fileName' => $returnURL,
            'percentage' => $percentage
        );
        echo json_encode($response);
    }
    public function export_gst_transafer(Request $request)
    {
        if ($request->ajax()) {
            $input = $request->all();
            $start = $input["start"];
            $limit = $input["limit"];
            $_fileName = Session::get('_fileName');
            $returnURL = URL::to('/') . "/asset/gst_transfer_payable_" . $_fileName . ".csv";
            $fileName = env('APP_EXPORTURL') . "asset/gst_transfer_payable_" . $_fileName . ".csv";
            global $wpdb;
            $postCols = array(
                'post_title',
                'post_content',
                'post_excerpt',
                'post_name',
            );
            header("Content-type: text/csv");
        }
        $data = GstTransfer::has('company')->whereNull('deleted_at')
            ->with([
                'company:id,name,short_name',
                'AllHeadTransaction:id,head_id,daybook_ref_id,branch_id,amount,description,payment_type,payment_mode,entry_date,entry_time,is_deleted,company_id,created_by_id',
                'AllHeadTransaction.AccountHeads:id:head_id,sub_head',
                'payable',
                'payable.challan',
                'state:id,name'
            ]);
        if (isset($input['is_search']) && $input['is_search'] == 'yes') {
            /******* fillter query start ****/
            if (isset($input['company_id']) && $input['company_id'] != '') {
                $company_id = $input['company_id'];
                if ($company_id != '0') {
                    $data->whereCompanyId($company_id);
                }
            }
            if ($input['state'] != '') {
                $state = $input['state'];
                $data = $data->whereHas('state', function ($query) use ($state) {
                    $query->whereId($state);
                });
            }
            if ($input['is_paid'] != '') {
                $is_paid = $input['is_paid'];
                $data->whereIsPaid($is_paid);
            }
            if ($input['start_date'] != '') {
                $start_date = $input['start_date'];
                $end_date = $input['end_date'];
                $start_date = date("Y-m-d H:i:s", strtotime(convertDate($start_date)));
                $end_date = date("Y-m-d H:i:s", strtotime(convertDate($end_date)));
                $data = $data->where(function ($query) use ($start_date, $end_date) {
                    $query->whereDate('start_date', '<=', $start_date)
                        ->whereDate('end_date', '>=', $end_date)
                        ->orWhereBetween('start_date', [$start_date, $end_date])
                        ->orWhereBetween('end_date', [$start_date, $end_date]);
                });
            }
        }
        // $headName = AccountHeads::pluck('sub_head', 'head_id');
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
            $val = [
                'S/No' => $sno,
                'Transfer Date' => date('d/m/Y', strtotime($row->transfer_date)),
                'Date Range' => date("d/m/Y", strtotime(convertDate($row->start_date))) . ' - ' . date("d/m/Y", strtotime(convertDate($row->end_date))),
                'State' => $row->state->name,
                'IGST Amount' => number_format((float) $row->igst_amt, 2, '.', ''),
                'CGST Amount' => number_format((float) $row->cgst_amt, 2, '.', ''),
                'SGST Amount' => number_format((float) $row->sgst_amt, 2, '.', ''),
                'Set Off IGST Amount' => number_format((float) $row->set_off_Igst ?? 0, 2, '.', ''),
                'Set Off CGST Amount' => number_format((float) $row->set_off_cgst ?? 0, 2, '.', ''),
                'Set Off SGST Amount' => number_format((float) $row->set_off_sgst ?? 0, 2, '.', ''),
                'Final IGST Amount' => number_format((float) $row->final_igst ?? 0, 2, '.', ''),
                'Final CGST Amount' => number_format((float) $row->final_cgst ?? 0, 2, '.', ''),
                'Final SGST Amount' => number_format((float) $row->final_sgst ?? 0, 2, '.', ''),
                'Transfer Request Amount' => number_format((float) ($row->igst_amt + $row->cgst_amt + $row->sgst_amt), 2, '.', ''),
                'GST Payable Amount' => number_format((float) ($row->payable ? ($row->payable->paid_amount) - ($row->payable->late_panelty) : 0), 2, '.', ''),
                'NEFT Charges' => number_format((float) ($row->payable ? ($row->payable->neft_charge) : 0), 2, '.', ''),
                'Late Penalty Amount' => number_format((float) ($row->payable ? ($row->payable->neft_charge) : 0), 2, '.', ''),
                'Total Payable Amount' => number_format((float) ($row->payable ? ($row->payable->neft_charge + $row->payable->paid_amount) : 0), 2, '.', ''),
                'Payment Date' => $row->payable ? date('d/m/Y', strtotime($row->payable->payment_date)) : 'N/A',
                'Is Paid' => $row->is_paid == 0 ? 'No' : 'Yes',
                'Company Name' => $row->company->short_name,
                'Challan Slip' => $row->payable ? $row->payable->challan->file_name : 'N/A',
            ];
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
    public function compay_to_state(Request $request)
    {
        $data = States::with([
            'branch' => function ($q) use ($request) {
                $q->with([
                    'companybranchs' => function ($q) use ($request) {
                        $q->where('company_id', $request->companyId)->select('id', 'company_id', 'branch_id');
                    }
                ])->select('id', 'state_id', 'branch_code');
            }
        ])
            ->get()
            ->groupBy('branch.company_branchs.company.id')
            ->toArray();
        // pd($data);
        return $data;
    }
    // gst transfer controller 
    public function paygstTransferAmount(Request $request)
    {
        $rules = [
            'payable_start_date' => 'required',
            'payable_end_date' => 'required',
            'created_at' => 'required',
            'payable_igst_amount_cr' => 'required|numeric',
            'payable_cgst_amount_cr' => 'required|numeric',
            'payable_sgst_amount_cr' => 'required|numeric',
            'payable_igst_amount_dr' => 'required|numeric',
            'payable_cgst_amount_dr' => 'required|numeric',
            'payable_sgst_amount_dr' => 'required|numeric',
            'company_id' => 'required',
            'state' => 'required',
        ];
        $messages = [
            'required' => 'The :attribute field is required.',
            'numeric' => 'The :attribute field must be a number.',
            'gt' => 'The :attribute field must be greater than zero.',
        ];
        $this->validate($request, $rules, $messages);
        $companyId = $request->company_id;
        $stateId = $request->state;
        $branches = Branch::with([
            'companybranchs' => function ($q) use ($companyId) {
                $q->whereCompanyId($companyId)->where('status', '1')->get();
            }
        ])->where('state_id', $stateId)->pluck('id');
        $created_by_id = Auth::user()->id;
        $created_by = 1;
        $allGstHeads = [170, 171, 172];
        $lth_headId = 458; // head_id for LIABILITY TRANSFER for GST    
        DB::beginTransaction();
        try {
            $t = date("H:i:s");
            $fromDate = date("Y-m-d", strtotime(str_replace('/', '-', $request->payable_start_date)));
            $toDate = date("Y-m-d", strtotime(str_replace('/', '-', $request->payable_end_date)));
            $payable_start_date = date("Y-m-d " . $t . "", strtotime(str_replace('/', '-', $request->payable_start_date)));
            $payable_end_date = date("Y-m-d " . $t . "", strtotime(str_replace('/', '-', $request->payable_end_date)));
            // $AddtoDate = date("Y-m-d", strtotime(str_replace('/', '-', $request->payable_end_date . ' +1 day')));
            $start = date("Y-m-d", strtotime(str_replace('/', '-', $request->payable_start_date . ' -1 month')));
            $end = date("Y-m-d", strtotime(str_replace('/', '-', $request->payable_end_date . ' -1 month ')));
            Session::put('created_at', $toDate);
            Session::put('created_atUpdate', $toDate);
            $referenceId = CommanController::createBranchDayBookReference($request->payable_total_cr_amount - $request->payable_total_dr_amount);
            $old_daybook_id = [];
            $sumAmountCr = [];
            $sumAmountDr = [];
            $insertDate = [];
            $daDataGstSetOff = [];
            foreach ($allGstHeads as $headValue) {
                $data = AllHeadTransaction::where('head_id', $headValue)
                    ->whereNotIn('sub_type', [94, 95])
                    ->whereBetween(\DB::raw('DATE(created_at)'), [$fromDate, $toDate])
                    ->when(!empty($branches), function ($q) use ($branches) {
                        $q->whereIn('branch_id', $branches);
                    })
                    ->where('is_deleted', 0)
                    ->where('company_id', (int) $companyId);
                $transacionsC = clone $data; // Clone the query instance for 'CR' transactions
                $transacionsD = clone $data; // Clone the query instance for 'DR' transactions
                $transacionsCR = $transacionsC->where('payment_type', 'CR')->get();
                $transacionsDR = $transacionsD->where('payment_type', 'DR')->get();
                $sumAmountBranchCr = $transacionsC->where('payment_type', 'CR')->get()->groupBy('branch_id')->map(function ($group) {
                    return $group->sum('amount');
                })->toArray();
                $sumAmountBranchDr = $transacionsD->where('payment_type', 'DR')->get()->groupBy('branch_id')->map(function ($group) {
                    return $group->sum('amount');
                })->toArray();
                $sumDr = 0;
                $sumCr = 0;
                $sumAmount = [];
                foreach ($transacionsCR as $value) {
                    if ($value->amount > 0) {
                        $insertCR = [
                            'daybook_ref_id' => $referenceId,
                            'branch_id' => $value->branch_id,
                            'bank_id' => NULL,
                            'bank_ac_id' => NULL,
                            'head_id' => $value->head_id,
                            'type' => 9,    //    $value->type, 
                            'sub_type' => 94,//     $value->sub_type, 
                            'type_id' => $value->type_id,
                            'type_transaction_id' => $value->type_transaction_id,
                            'associate_id' => $value->associate_id,
                            'member_id' => $value->member_id,
                            'branch_id_to' => NULL,
                            'branch_id_from' => NULL,
                            'amount' => $value->amount,
                            'description' => 'GST Transfer amount DR' . $value->amount . '',
                            'payment_type' => 'DR',
                            'payment_mode' => 3,
                            'currency_code' => 'INR',
                            'jv_unique_id' => NULL,
                            'v_no' => NULL,
                            'ssb_account_id_from' => NULL,
                            'ssb_account_id_to' => NULL,
                            'ssb_account_tran_id_to' => NULL,
                            'ssb_account_tran_id_from' => NULL,
                            'cheque_type' => NULL,
                            'cheque_id' => NULL,
                            'cheque_no' => NULL,
                            'transction_no' => NULL,
                            'entry_date' => date("Y-m-d", strtotime(convertDate($toDate))),
                            'entry_time' => $t,
                            'created_by' => $created_by,
                            'created_by_id' => $created_by_id,
                            'company_id' => $companyId,
                            'created_at' => date("Y-m-d " . $t . "", strtotime(convertDate($toDate))),
                            'is_app' => 0,
                            'is_query' => 0,
                            'is_cron' => 0,
                        ];
                        $sumCr += $value->amount;
                        array_push($old_daybook_id, $value->daybook_ref_id);
                    }
                    $sumAmountCr[$headValue] = $sumCr;
                }
                foreach ($transacionsDR as $value) {
                    if ($value->amount > 0) {
                        $insertDR = [
                            'daybook_ref_id' => $referenceId,
                            'branch_id' => $value->branch_id,
                            'bank_id' => NULL,
                            'bank_ac_id' => NULL,
                            'head_id' => $value->head_id,
                            'type' => 9, //     $value->type, 
                            'sub_type' => 94, //    $value->sub_type, 
                            'type_id' => $value->type_id,
                            'type_transaction_id' => $value->type_transaction_id,
                            'associate_id' => $value->associate_id,
                            'member_id' => $value->member_id,
                            'branch_id_to' => NULL,
                            'branch_id_from' => NULL,
                            'amount' => $value->amount,
                            'description' => 'GST Transfer amount CR' . $value->amount . '',
                            'payment_type' => 'CR',
                            'payment_mode' => 3,
                            'currency_code' => 'INR',
                            'jv_unique_id' => NULL,
                            'v_no' => NULL,
                            'ssb_account_id_from' => NULL,
                            'ssb_account_id_to' => NULL,
                            'ssb_account_tran_id_to' => NULL,
                            'ssb_account_tran_id_from' => NULL,
                            'cheque_type' => NULL,
                            'cheque_id' => NULL,
                            'cheque_no' => NULL,
                            'transction_no' => NULL,
                            'entry_date' => date("Y-m-d", strtotime(convertDate($toDate))),
                            'entry_time' => $t,
                            'created_by' => $created_by,
                            'created_by_id' => $created_by_id,
                            'company_id' => $companyId,
                            'created_at' => date("Y-m-d " . $t . "", strtotime(convertDate($toDate))),
                            'is_app' => 0,
                            'is_query' => 0,
                            'is_cron' => 0,
                        ];
                        $sumDr += $value->amount;
                        array_push($old_daybook_id, $value->daybook_ref_id);
                    }
                    $sumAmountDr[$headValue] = $sumDr;
                }
                $a = $headValue == 170 ? (array_key_exists(170, $sumAmountDr) ? ($sumAmountDr[170]) : 0) : 0;
                $b = $headValue == 171 ? (array_key_exists(171, $sumAmountDr) ? ($sumAmountDr[171]) : 0) : 0;
                $c = $headValue == 172 ? (array_key_exists(172, $sumAmountDr) ? ($sumAmountDr[172]) : 0) : 0;
                $total_tax = ($sumAmountCr[$headValue] ?? 0);
                $daDataGstSetOff = [
                    'head_id' => $headValue,
                    'total_tax' => $total_tax,
                    'daybook_ref_id' => $referenceId,
                    'adj_igst' => $a,
                    'adj_cgst' => $b,
                    'adj_sgst' => $c,
                    'balance' => 0,
                    'tax_payable' => 0,
                    'start_date' => $payable_start_date,
                    'end_date' => $payable_end_date,
                    'created_by' => $created_by,
                    'created_by_id' => $created_by_id,
                    'created_at' => $request->created_at,
                    'company_id' => $companyId,
                    'state_id' => $stateId
                ];
                $GstSetOffId = GstSetOff::insertGetId($daDataGstSetOff);

                foreach ($sumAmountBranchCr as $key => $value) {
                    // LTH CR In loop
                    if ($value > 0) {
                        $insertBranchCR = [
                            'daybook_ref_id' => $referenceId,
                            'branch_id' => $key,
                            'bank_id' => NULL,
                            'bank_ac_id' => NULL,
                            'head_id' => $lth_headId,
                            'type' => 9,
                            'sub_type' => 94,
                            'type_id' => $GstSetOffId,
                            'type_transaction_id' => $GstSetOffId,
                            'associate_id' => NULL,
                            'member_id' => NULL,
                            'branch_id_to' => NULL,
                            'branch_id_from' => NULL,
                            'amount' => $value,
                            'description' => 'Gst Transfer Amount CR On ' . getBranchDetail($key)->name . ' Branch ' . $value . '',
                            'payment_type' => 'CR',
                            'payment_mode' => 3,
                            'currency_code' => 'INR',
                            'jv_unique_id' => NULL,
                            'v_no' => NULL,
                            'ssb_account_id_from' => NULL,
                            'ssb_account_id_to' => NULL,
                            'ssb_account_tran_id_to' => NULL,
                            'ssb_account_tran_id_from' => NULL,
                            'cheque_type' => NULL,
                            'cheque_id' => NULL,
                            'cheque_no' => NULL,
                            'transction_no' => NULL,
                            'entry_date' => date("Y-m-d", strtotime(convertDate($toDate))),
                            'entry_time' => $t,
                            'created_by' => $created_by,
                            'created_by_id' => $created_by_id,
                            'company_id' => $companyId,
                            'created_at' => date("Y-m-d " . $t . "", strtotime(convertDate($toDate))),
                            'is_app' => 0,
                            'is_query' => 0,
                            'is_cron' => 0,
                        ];
                    }
                }
                foreach ($sumAmountBranchDr as $key => $value) {
                    // LTH DR In loop
                    if ($value > 0) {
                        $insertBranchDR = [
                            'daybook_ref_id' => $referenceId,
                            'branch_id' => $key,
                            'bank_id' => NULL,
                            'bank_ac_id' => NULL,
                            'head_id' => $lth_headId,
                            'type' => 9,
                            'sub_type' => 94,
                            'type_id' => $GstSetOffId,
                            'type_transaction_id' => $GstSetOffId,
                            'associate_id' => NULL,
                            'member_id' => NULL,
                            'branch_id_to' => NULL,
                            'branch_id_from' => NULL,
                            'amount' => $value,
                            'description' => 'Gst Transfer Amount DR On ' . getBranchDetail($key)->name . ' Branch ' . $value . '',
                            'payment_type' => 'DR',
                            'payment_mode' => 3,
                            'currency_code' => 'INR',
                            'jv_unique_id' => NULL,
                            'v_no' => NULL,
                            'ssb_account_id_from' => NULL,
                            'ssb_account_id_to' => NULL,
                            'ssb_account_tran_id_to' => NULL,
                            'ssb_account_tran_id_from' => NULL,
                            'cheque_type' => NULL,
                            'cheque_id' => NULL,
                            'cheque_no' => NULL,
                            'transction_no' => NULL,
                            'entry_date' => date("Y-m-d", strtotime(convertDate($toDate))),
                            'entry_time' => $t,
                            'created_by' => $created_by,
                            'created_by_id' => $created_by_id,
                            'company_id' => $companyId,
                            'created_at' => date("Y-m-d " . $t . "", strtotime(convertDate($toDate))),
                            'is_app' => 0,
                            'is_query' => 0,
                            'is_cron' => 0,
                        ];
                    }
                }
                if (array_sum($sumAmountBranchCr) > 0) {
                    // head id will be  in (170,171,172) credit value 
                    $allHeadTransaction_igst_DR = CommanController::createAllHeadTransaction($referenceId, 29, NULL, NULL, $headValue, 9, 94, $GstSetOffId, $GstSetOffId, NULL, NULL, NULL, NULL, array_sum($sumAmountBranchCr), 'GST Transfer Amount ' . getAcountHeadData($headValue) . ' on ' . array_sum($sumAmountBranchCr) . '', 'CR', 3, 'INR', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, $created_by, $created_by_id, $companyId);
                }
                if (array_sum($sumAmountBranchDr) > 0) {
                    // head id will be  in (170,171,172) debit value 
                    $allHeadTransaction_igst_CR = CommanController::createAllHeadTransaction($referenceId, 29, NULL, NULL, $headValue, 9, 94, $GstSetOffId, $GstSetOffId, NULL, NULL, NULL, NULL, array_sum($sumAmountBranchDr), 'GST Transfer Amount ' . getAcountHeadData($headValue) . ' on ' . array_sum($sumAmountBranchDr) . '', 'DR', 3, 'INR', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, $created_by, $created_by_id, $companyId);
                }
                if (array_sum($sumAmountBranchCr) > 0) {
                    // head id will be  in 458  libility transfer Crditet value 
                    $allHeadTransaction_LTH_CR = CommanController::createAllHeadTransaction($referenceId, 29, NULL, NULL, $lth_headId, 9, 94, $GstSetOffId, $GstSetOffId, NULL, NULL, NULL, NULL, array_sum($sumAmountBranchCr), 'GST Transfer Amount DR LIABILITY TRANSFER Head ' . array_sum($sumAmountBranchCr) . '', 'DR', 3, 'INR', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, $created_by, $created_by_id, $companyId);
                }
                if (array_sum($sumAmountBranchDr) > 0) {
                    // head id will be  in 458  libility transfer Debit value 
                    $allHeadTransaction_LTH_DR = CommanController::createAllHeadTransaction($referenceId, 29, NULL, NULL, $lth_headId, 9, 94, $GstSetOffId, $GstSetOffId, NULL, NULL, NULL, NULL, array_sum($sumAmountBranchDr), 'GST Transfer Amount CR LIABILITY TRANSFER Head ' . array_sum($sumAmountBranchDr) . '', 'CR', 3, 'INR', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, $created_by, $created_by_id, $companyId);
                }
            }
            if (!empty($insertDate)) {
                $insertDate_CR_LTH_allheadtransaction = $this->repository->insertAllHeadTransaction($insertDate);
            }

            if (!empty($insertCR)) {
                $insertCR_CR_LTH_allheadtransaction = $this->repository->insertAllHeadTransaction($insertCR);
            }

            if (!empty($insertDR)) {
                $insertDR_CR_LTH_allheadtransaction = $this->repository->insertAllHeadTransaction($insertDR);
            }

            if (!empty($insertBranchCR)) {
                $insertBranchCR_CR_LTH_allheadtransaction = $this->repository->insertAllHeadTransaction($insertBranchCR);
            }

            if (!empty($insertBranchDR)) {
                $insertBranchDR_CR_LTH_allheadtransaction = $this->repository->insertAllHeadTransaction($insertBranchDR);
            }

            // check last month gst set - off table 
            /*
            $checkGstTransfer = GstTransfer::where(function ($query) use ($end) {
                    $query->whereDate('start_date', '<=', $end)->whereDate('end_date', '>=', $end)
                    ->where('state_id', $stateId)
                    ->whereIn('head_id', [169])
                    ->where('company_id', $companyId)
                    ->where('deleted_at', NULL)
                    ;
                })
                ->orWhereBetween(\DB::raw('DATE(end_date)'), [$start, $end])
                ->where('state_id', $stateId)
                ->whereIn('head_id', [169])
                ->where('company_id', $companyId)
                ->where('deleted_at', NULL)
                ->first(['set_off_Igst','set_off_cgst','set_off_sgst']);
            */
            $igst = (array_key_exists(170, $sumAmountCr) ? ($sumAmountCr[170]) : 0) - (array_key_exists(170, $sumAmountDr) ? ($sumAmountDr[170]) : 0);
            $cgst = (array_key_exists(171, $sumAmountCr) ? ($sumAmountCr[171]) : 0) - (array_key_exists(171, $sumAmountDr) ? ($sumAmountDr[171]) : 0);
            $sgst = (array_key_exists(172, $sumAmountCr) ? ($sumAmountCr[172]) : 0) - (array_key_exists(172, $sumAmountDr) ? ($sumAmountDr[172]) : 0);
            // if(isset($checkGstTransfer) && count($checkGstTransfer->toArray()) > 0 ){
            //     $igst = $igst - ($checkGstTransfer['set_off_Igst'] ?? 0);
            //     $cgst = $cgst - ($checkGstTransfer['set_off_cgst'] ?? 0);
            //     $sgst = $sgst - ($checkGstTransfer['set_off_sgst'] ?? 0);
            // }
            $daData = [
                'daybook_ref_id' => $referenceId,
                'head_id' => 169,
                'transfer_date' => $toDate,
                'is_paid' => 0,
                'igst_amt' => (array_key_exists(170, $sumAmountCr) ? ($sumAmountCr[170]) : 0) < (array_key_exists(170, $sumAmountDr) ? ($sumAmountDr[170]) : 0) ? 0 : ($igst),
                'cgst_amt' => (array_key_exists(171, $sumAmountCr) ? ($sumAmountCr[171]) : 0) < (array_key_exists(171, $sumAmountDr) ? ($sumAmountDr[171]) : 0) ? 0 : ($cgst),
                'sgst_amt' => (array_key_exists(172, $sumAmountCr) ? ($sumAmountCr[172]) : 0) < (array_key_exists(172, $sumAmountDr) ? ($sumAmountDr[172]) : 0) ? 0 : ($sgst),
                'set_off_Igst' => (array_key_exists(170, $sumAmountCr) ? ($sumAmountCr[170]) : 0) > (array_key_exists(170, $sumAmountDr) ? ($sumAmountDr[170]) : 0) ? 0 : abs(array_key_exists(170, $sumAmountCr) ? ($sumAmountCr[170]) : 0) - (array_key_exists(170, $sumAmountDr) ? ($sumAmountDr[170]) : 0),
                'set_off_cgst' => (array_key_exists(171, $sumAmountCr) ? ($sumAmountCr[171]) : 0) > (array_key_exists(171, $sumAmountDr) ? ($sumAmountDr[171]) : 0) ? 0 : abs(array_key_exists(171, $sumAmountCr) ? ($sumAmountCr[171]) : 0) - (array_key_exists(171, $sumAmountDr) ? ($sumAmountDr[171]) : 0),
                'set_off_sgst' => (array_key_exists(172, $sumAmountCr) ? ($sumAmountCr[172]) : 0) > (array_key_exists(172, $sumAmountDr) ? ($sumAmountDr[172]) : 0) ? 0 : abs(array_key_exists(172, $sumAmountCr) ? ($sumAmountCr[172]) : 0) - (array_key_exists(172, $sumAmountDr) ? ($sumAmountDr[172]) : 0),
                'final_igst' => 0.00,
                'final_cgst' => 0.00,
                'final_sgst' => 0.00,
                'state_id' => $stateId,
                'start_date' => $payable_start_date,
                'end_date' => $payable_end_date,
                'deleted_at' => NULL,
                'old_daybook_id' => json_encode($old_daybook_id),
                'transfer_daybook_ref_id' => $referenceId,
                'payment_ref_id' => NULL,
                'company_id' => $companyId
            ];
            // pd($daData);
            $GstTransfer = GstTransfer::create($daData);
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            // dd($ex->getLine());
            return back()->with('alert', $ex->getMessage());
        }
        return back()->with('success', 'Gst Transfer Request Created Successfully!');
    }
    // gst payable controller 
    public function payGstPayableAmount(Request $request)
    {
        $rules = [
            'payable_start_date' => 'required',
            'payable_end_date' => 'required',
            'payable_payment_date' => 'required',
            'bank_id' => 'required',
            'account_id' => 'required',
            'remark' => 'required',
            'daybook_diff' => 'required',
            'state' => 'required',
            'id' => 'required',
            'company_id' => 'required',
            'transaction_number' => 'required|unique:gst_payables,transaction_number',
            // 'total_paid_amount' => 'required|gt:0',
        ];
        $messages = [
            'required' => 'The :attribute field is required.',
            'gt' => 'The :attribute field must be greater than zero.',
        ];
        $this->validate($request, $rules, $messages);
        $companyId = $request->company_id;
        $stateId = $request->state;

        DB::beginTransaction();
        try {
            // upload chalan image file start
            if ($request->has('upload_challan')) {
                try {
                    $mainFolder = 'gst-payable/challan';
                    $file = $request->file('upload_challan');
                    $uploadFile = $file->getClientOriginalName();
                    $fname = time() . '.' . $file->getClientOriginalExtension();
                    $files = ImageUpload::upload($file, $mainFolder, $fname);
                    $fData = [
                        'file_name' => $fname,
                        'file_path' => $mainFolder,
                        'file_extension' => $file->getClientOriginalExtension(),
                    ];
                    $file_id = Files::insertGetId($fData);
                } catch (\Exception $e) {
                    // $file_id = 0;
                    $file_id = $e->getMessage();
                }
            } else {
                $file_id = NULL;
            }
            // upload chalan image file end
            $t = date("H:i:s");
            $entry_time = date("H:i:s");
            $fromDate = date("Y-m-d", strtotime(str_replace('/', '-', $request->payable_start_date)));
            $toDate = date("Y-m-d", strtotime(str_replace('/', '-', $request->payable_end_date)));
            $AddtoDate = date("Y-m-d", strtotime(str_replace('/', '-', $request->payable_payment_date)));
            Session::put('created_at', $AddtoDate);
            Session::put('created_atUpdate', $AddtoDate);
            $paymentDate = date("Y-m-d " . $entry_time . "", strtotime(str_replace('/', '-', $request->payable_payment_date)));
            $referenceId = $request->daybook_diff;
            $gstTrasfer = $request->id;
            /*
            $branches = Branch::with(['companybranchs' => function ($q) use ($companyId) {
                $q->whereCompanyId($companyId)->where('status', '1')->get();
            }])->where('state_id', $stateId)->pluck('id');
            */
            $nft = $request->neft_charge ?? 0;
            $created_by_id = Auth::user()->id;
            $late_panelty = $request->payable_late_panelty ?? 0;
            $insertallGstHeads = [];
            $insertbank = [];
            $insertnft = [];
            $insertlate_panelty = [];
            $daData = [
                'gst_head_id' => 169,
                'daybook_ref_id' => $referenceId,
                'igst_amount' => $request->payable_igst_amount,
                'cgst_amount' => $request->payable_cgst_amount,
                'sgst_amount' => $request->payable_sgst_amount,
                'state_id' => $stateId,
                'paid_amount' => $request->total_paid_amount,
                'from_date' => $fromDate,
                'to_date' => $toDate,
                'payment_date' => $paymentDate,
                'bank_id' => $request->bank_id,
                'account_id' => $request->account_id,
                'transaction_number' => $request->transaction_number,
                'neft_charge' => $nft,
                'late_panelty' => $late_panelty,
                'challan_id' => $file_id,
                'remark' => $request->remark,
                'created_at' => $request->created_at,
                'company_id' => $companyId,
                'is_deleted' => 0
            ];
            $gstPayable = GstPayable::insertGetId($daData);
            // alll gst head
            // 170 for Integrated head
            // 171 for Central head
            // 172 for State/UT head

            $allGstHeads = [170 => 'payable_igst_amount', 171 => 'payable_cgst_amount', 172 => 'payable_sgst_amount'];
            foreach ($allGstHeads as $key => $val) {
                // bank payable entry for every gst head in (170,171,172)
                $amt = $request[$val] ?? 0;
                if ($amt > 0) {
                    $insertallGstHeads = [
                        'daybook_ref_id' => $referenceId,
                        'branch_id' => 29,
                        'bank_id' => $request->bank_id,
                        'bank_ac_id' => $request->account_id,
                        'head_id' => $key,
                        'type' => 9,
                        'sub_type' => 95,
                        'type_id' => $gstPayable,
                        'type_transaction_id' => $gstTrasfer,
                        'associate_id' => NULL,
                        'member_id' => NULL,
                        'branch_id_to' => NULL,
                        'branch_id_from' => NULL,
                        'amount' => ($amt),
                        'description' => 'GST Payable ' . ucwords(getAcountHeadData($key)) . ' on Gst Bank Payable A/C ' . ($amt) . '',
                        'payment_type' => 'DR',
                        'payment_mode' => 2,
                        'currency_code' => 'INR',
                        'jv_unique_id' => NULL,
                        'v_no' => NULL,
                        'ssb_account_id_from' => NULL,
                        'ssb_account_id_to' => NULL,
                        'ssb_account_tran_id_to' => NULL,
                        'ssb_account_tran_id_from' => NULL,
                        'cheque_type' => NULL,
                        'cheque_id' => NULL,
                        'cheque_no' => NULL,
                        'transction_no' => $request->transaction_number,
                        'entry_date' => date("Y-m-d", strtotime(convertDate($paymentDate))),
                        'entry_time' => date("H:i:s"),
                        'created_by' => 1,
                        'created_by_id' => $created_by_id,
                        'company_id' => $companyId,
                        'created_at' => date("Y-m-d " . $t . "", strtotime(convertDate($paymentDate))),
                        'is_app' => 0,
                        'is_query' => 0,
                        'is_cron' => 0,
                    ];
                }
            }
            // LATE PENALTY entry
            if ($late_panelty > 0) {
                $insertlate_panelty = [
                    'daybook_ref_id' => $referenceId,
                    'branch_id' => 29,
                    'bank_id' => $request->bank_id,
                    'bank_ac_id' => $request->account_id,
                    'head_id' => 33,   // LATE PENALTY account head
                    'type' => 9,
                    'sub_type' => 95,
                    'type_id' => $gstPayable,
                    'type_transaction_id' => $gstTrasfer,
                    'associate_id' => NULL,
                    'member_id' => NULL,
                    'branch_id_to' => NULL,
                    'branch_id_from' => NULL,
                    'amount' => $late_panelty,
                    'description' => 'GST Payable ' . ucwords(getAcountHeadData(33)) . ' on on Gst Payable A/c Dr ' . $late_panelty . '',
                    'payment_type' => 'DR',
                    'payment_mode' => 2,
                    'currency_code' => 'INR',
                    'jv_unique_id' => NULL,
                    'v_no' => NULL,
                    'ssb_account_id_from' => NULL,
                    'ssb_account_id_to' => NULL,
                    'ssb_account_tran_id_to' => NULL,
                    'ssb_account_tran_id_from' => NULL,
                    'cheque_type' => NULL,
                    'cheque_id' => NULL,
                    'cheque_no' => NULL,
                    'transction_no' => $request->transaction_number,
                    'entry_date' => date("Y-m-d", strtotime(convertDate($paymentDate))),
                    'entry_time' => date("H:i:s"),
                    'created_by' => 1,
                    'created_by_id' => $created_by_id,
                    'company_id' => $companyId,
                    'created_at' => date("Y-m-d " . $t . "", strtotime(convertDate($paymentDate))),
                    'is_app' => 0,
                    'is_query' => 0,
                    'is_cron' => 0,
                ];
            }
            //nft charges if any
            if ($nft > 0) {
                $insertnft = [
                    'daybook_ref_id' => $referenceId,
                    'branch_id' => 29,
                    'bank_id' => $request->bank_id,
                    'bank_ac_id' => $request->account_id,
                    'head_id' => 92,  // nft charges account head
                    'type' => 9,
                    'sub_type' => 95,
                    'type_id' => $gstPayable,
                    'type_transaction_id' => $gstTrasfer,
                    'associate_id' => NULL,
                    'member_id' => NULL,
                    'branch_id_to' => NULL,
                    'branch_id_from' => NULL,
                    'amount' => $nft,
                    'description' => 'GST Payable ' . ucwords(getAcountHeadData(92)) . ' on Gst Payable A/c Dr ' . $nft,
                    'payment_type' => 'DR',
                    'payment_mode' => 2,
                    'currency_code' => 'INR',
                    'jv_unique_id' => NULL,
                    'v_no' => NULL,
                    'ssb_account_id_from' => NULL,
                    'ssb_account_id_to' => NULL,
                    'ssb_account_tran_id_to' => NULL,
                    'ssb_account_tran_id_from' => NULL,
                    'cheque_type' => NULL,
                    'cheque_id' => NULL,
                    'cheque_no' => NULL,
                    'transction_no' => $request->transaction_number,
                    'entry_date' => date("Y-m-d", strtotime(convertDate($paymentDate))),
                    'entry_time' => date("H:i:s"),
                    'created_by' => 1,
                    'created_by_id' => $created_by_id,
                    'company_id' => $companyId,
                    'created_at' => date("Y-m-d " . $t . "", strtotime(convertDate($paymentDate))),
                    'is_app' => 0,
                    'is_query' => 0,
                    'is_cron' => 0,
                ];
            }
            //total amount late penalty + gst_amount + nft if any
            $totalamount = $request->total_paid_amount + ($nft);
            //credit ampount on bank crarges entry 
            $insertbank = [
                'daybook_ref_id' => $referenceId,
                'branch_id' => 29,
                'bank_id' => $request->bank_id,
                'bank_ac_id' => $request->account_id,
                'head_id' => getSamraddhBank($request->bank_id)->account_head_id,
                'type' => 9,
                'sub_type' => 95,
                'type_id' => $gstPayable,
                'type_transaction_id' => $gstTrasfer,
                'associate_id' => NULL,
                'member_id' => NULL,
                'branch_id_to' => NULL,
                'branch_id_from' => NULL,
                'amount' => $totalamount,
                'description' => 'Bank Transaction on Gst Payable A/c Cr ' . $totalamount . '',
                'payment_type' => 'CR',
                'payment_mode' => 2,
                'currency_code' => 'INR',
                'jv_unique_id' => NULL,
                'v_no' => NULL,
                'ssb_account_id_from' => NULL,
                'ssb_account_id_to' => NULL,
                'ssb_account_tran_id_to' => NULL,
                'ssb_account_tran_id_from' => NULL,
                'cheque_type' => NULL,
                'cheque_id' => NULL,
                'cheque_no' => NULL,
                'transction_no' => $request->transaction_number,
                'entry_date' => date("Y-m-d", strtotime(convertDate($paymentDate))),
                'entry_time' => date("H:i:s"),
                'created_by' => 1,
                'created_by_id' => $created_by_id,
                'company_id' => $companyId,
                'created_at' => date("Y-m-d " . $t . "", strtotime(convertDate($paymentDate))),
                'is_app' => 0,
                'is_query' => 0,
                'is_cron' => 0,
            ];
            // one entry for SamraddhBankDaybook table
            $samraddhBankDaybook = CommanController::samraddhBankDaybookNew($referenceId, $request->bank_id, $request->account_id, 9, 95, $gstPayable, $gstTrasfer, NULL, NULL, 29, $totalamount, $totalamount, $totalamount, 'Gst Payable amount ' . $totalamount . '', 'Gst Payable Dr ' . $totalamount . '', 'Gst Payable Cr ' . $totalamount . '', 'DR', 2, 'INR', NULL, NULL, $request->bank_id, getSamraddhBank($request->bank_id)->bank_name, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, $request->transaction_number, $request->bank_id, $request->account_id, getSamraddhBankAccountId($request->account_id)->ifsc_code, NULL, $request->bank_id, $request->account_id, NULL, NULL, NULL, NULL, $paymentDate, $paymentDate, $entry_time, 1, Auth::user()->id, $request->created_at, NULL, NULL, NULL, NULL, NULL, $companyId);
            // one entry for branch_dayBook table
            $createBranchDayBookModify = CommanController::createBranchDayBookModify($referenceId, 29, 9, 95, $gstPayable, $gstTrasfer, NULL, NULL, null, null, $totalamount, 'Gst Payable Amount ' . $totalamount . '', 'Gst Payable Dr ' . $totalamount . '', 'Gst Payable Cr ' . $totalamount . '', 'DR', 2, 'INR', NULL, NULL, NULL, NULL, $request->created_at, $entry_time, 1, $created_by_id, $paymentDate, $companyId);
            // one entry for all head transaction table

            if (!empty($insertallGstHeads)) {
                $allHeadTransaction = $this->repository->insertAllHeadTransaction($insertallGstHeads);
            }
            if (!empty($insertbank)) {
                $allHeadTransaction = $this->repository->insertAllHeadTransaction($insertbank);
            }
            if (!empty($insertnft)) {
                $allHeadTransaction = $this->repository->insertAllHeadTransaction($insertnft);
            }
            if (!empty($insertlate_panelty)) {
                $allHeadTransaction = $this->repository->insertAllHeadTransaction($insertlate_panelty);
            }

            $gst_transfer_data = [
                'final_igst' => $request->payable_igst_amount,
                'final_cgst' => $request->payable_cgst_amount,
                'final_sgst' => $request->payable_sgst_amount,
                'is_paid' => 1,
                'payment_ref_id' => $referenceId
            ];
            $gst_transfer_update = GstTransfer::whereId($request->id)->update($gst_transfer_data);
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            return back()->with('alert', $ex->getMessage());
            // dd($ex->getLine());
        }
        return redirect()->route('admin.add-gst-payable')->with('success', 'Payment Completed Successfully!');
    }
    public function transactionNumberCheck(Request $request)
    {
        $data = GstPayable::pluck('id', 'transaction_number');
        return (!empty($data) ? $data[$request->number] ?? 0 : 0);
    }
    public function gst_transferlisting(Request $request)
    {
        $data['title'] = 'GST Payable';
        $data['branch'] = Branch::where('status', 1)->get();
        $data['gstHeads'] = AccountHeads::where('parent_id', 298)->pluck('sub_head', 'head_id');
        $data['allState'] = GstSetting::with(['State:id,name'])->get(['state_id'])->groupBy('state_id')->toArray();
        $data['view'] = 0;
        return view('templates.admin.gst.gst_transfer_listing', $data);
    }
    public function chalandownload(Request $request)
    {
        $fileName = $request->input('name');
        $file = $request->input('path');
        p($fileName);
        p($file);
        return response()->download($file);
    }
    public function gst_setoff(Request $request)
    {
        $data['title'] = 'GST Set-Off';
        $data['branch'] = Branch::where('status', 1)->get();
        $data['gstHeads'] = AccountHeads::where('parent_id', 298)->pluck('sub_head', 'head_id');
        $data['allState'] = GstSetting::with(['State:id,name'])->get(['state_id'])->groupBy('state_id')->toArray();
        $data['view'] = 0;
        return view('templates.admin.gst.gst_setoff', $data);
    }
}