<?php
namespace App\Http\Controllers\Branch;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CorrectionRequests;
use App\Models\Memberinvestments;
use App\Models\SavingAccount;
use App\Models\Companies;
use Illuminate\Support\Facades\Cache;
use Session;
use DB;
use Yajra\DataTables\DataTables;
use URL;

/*
    |---------------------------------------------------------------------------
    | Branch Panel -- Member Management MemberController
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
    }

    /**
     * Member Correction View.
     * Route: /member/passbook
     * Method: get
     * @return  array()  Response
     */
    public function correctionRequestView(Request $request)
    {

        if ($request->segment(2) == 'member') {

            if (!in_array('View Member Correction Request List', auth()->user()->getPermissionNames()->toArray())) {
                return redirect()->route('branch.dashboard');
            }
            $data['title'] = 'Members';
            //$fileName = 'investment_management';
            $data['type'] = 0;
        } elseif ($request->segment(2) == 'associate') {

            if (!in_array('View Associate Correction Request List', auth()->user()->getPermissionNames()->toArray())) {
                return redirect()->route('branch.dashboard');
            }
            //$fileName = $request->segment(2);
            $data['title'] = 'Associates';
            $data['type'] = 1;
        } elseif ($request->segment(2) == 'investment') {

            if (!in_array('View Investment Correction Request List', auth()->user()->getPermissionNames()->toArray())) {
                return redirect()->route('branch.dashboard');
            }
            $data['title'] = 'Investments';
            $data['type'] = 2;
        } elseif ($request->segment(2) == 'renewal') {
            if (!in_array('View Renewal Correction Request List', auth()->user()->getPermissionNames()->toArray())) {
                return redirect()->route('branch.dashboard');
            }
            $data['title'] = 'Renewals';
            $data['type'] = 3;
        } elseif ($request->segment(2) == 'printcertificate') {

            $data['title'] = 'Print Certificate';

            $data['type'] = 6;
        } elseif ($request->segment(2) == 'printpassbook') {

            $data['title'] = 'Print Passbook';

            $data['type'] = 5;
        }
        return view('templates.branch.common.correctionrequest', $data);
    }

    /**
     * Fetch member correction listing data.
     *@method post call ajax
     * @param  \App\Reservation  $reservation
     * @return \Illuminate\Http\Response
     */
    public function correctionRequestList(Request $request)
    {
        $branchId = Auth::id();
        if ($request->ajax()) {
            $data = CorrectionRequests::with(['branch' => function ($query) {
                $query->select('id', 'name', 'branch_code', 'sector', 'regan', 'zone');
            }])->where('correction_type', $request->type)->where('branch_id', $branchId) /*->where('status',0)*/;
            $data = $data->orderBy('id', 'DESC')->get();
            return Datatables::of($data)
                ->addIndexColumn()

                ->addColumn('created_at', function ($row) {
                    $created_at = date("d/m/Y", strtotime(str_replace('-', '/', $row->created_at)));
                    return $created_at;
                })
                ->rawColumns(['created_at'])

                ->addColumn('branch', function ($row) {
                    $branch = $row['branch']->name;
                    return $branch;
                })
                ->rawColumns(['branch'])


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


                ->addColumn('in_context', function ($row) {
                    if ($row->correction_type == 0) {
                        $in_context = 'Member Registration';
                    } elseif ($row->correction_type == 1) {
                        $in_context = 'Associate Registration';
                    } elseif ($row->correction_type == 2) {
                        $in_context = 'Investment Registration';
                    } elseif ($row->correction_type == 3) {
                        $in_context = 'Renewals Transaction';
                    } elseif ($row->correction_type == 4) {
                        $in_context = 'Withdrawals';
                    } elseif ($row->correction_type == 5) {
                        $in_context = 'Passbook print';
                    } elseif ($row->correction_type == 6) {
                        $in_context = 'Certificate print';
                    }
                    return $in_context;
                })
                ->rawColumns(['in_context'])

                ->addColumn('correction', function ($row) {
                    $correction = $row->correction_description;
                    return $correction;
                })
                ->rawColumns(['correction'])

                ->addColumn('status', function ($row) {
                    if ($row->status == 0) {
                        $status = 'Pending';
                    } elseif ($row->status == 1) {
                        $status = 'Corrected';
                    } elseif ($row->status == 2) {
                        $status = '<a class="correction-view-button" href="javascript:void(0)" data-toggle="modal" data-target="#rejection-view" data-correction-details="' . $row->rejected_correction_description . '" title="View"><i class="icon-eye-blocked2  mr-2"></i>Rejected</a>';
                    }
                    return $status;
                })
                ->rawColumns(['status'])

                ->make(true);
        }
    }

    /**
     * Save Corrections.
     * Route: /branch/member/detail/
     * Method: get
     * @param  $id,$msg
     * @return  array()  Response
     */
    public function saveCoreectionRequest(Request $request)
    {
        DB::beginTransaction();
        try{
            $getBranch = branchName();
            $branch_id = $getBranch->id;
            $stateid = $getBranch->state_id;
            $branchCode = $getBranch->branch_code;
            $companyId = 0;
            if (isset($request->companyid)) {
                $companyId = $request->companyid;
            }
            if ($request->correction_type == 2) {
                $investmetData['investment_correction_request'] = 1;
                $updateInvestment = Memberinvestments::find($request->correction_type_id);
                $companyId = $updateInvestment->company_id;
                $updateInvestment->update($investmetData);
            }
            if ($request->correction_type == 3) {
                $data['created_at'] = date('Y-m-d H:i:s', strtotime(convertDate($request->request_date)));
                $data['plan_category'] = $request->plan_category_code;
                $data['account_id'] = $request->account_id;
                if ($request->plan_category_code != 'S') {
                    $investmet_id = $request->account_id;
                } else {
                    $ssbData = SavingAccount::where('id', $request->account_id)->first(['id', 'member_investments_id']);
                    $investmet_id = $ssbData->member_investments_id;
                }
                $investmetData['renewal_correction_request'] = 1;
                $updateInvestment = Memberinvestments::find($investmet_id);
                $companyId = $updateInvestment->company_id;
                $updateInvestment->update($investmetData);
            }
            $data['correction_type'] = $request->correction_type;
            $data['correction_type_id'] = $request->correction_type_id;
            $data['correction_description'] = $request->corrections;
            $data['branch_id'] = $branch_id;
            $data['branch_code'] = $branchCode;
            $data['company_id'] = $companyId;
            $data['created_at'] = date('Y-m-d H:i:s', strtotime(convertDate(checkMonthAvailability(date('d'), date('m'), date('Y'), $stateid))));
            CorrectionRequests::create($data);
            DB::commit();
        }catch(\Exception $e){
            DB::rollback();
            dd($e->getMessage() . ' - ' . $e->getLine() . ' - ' . $e->getFile());
            return back()->with('alert', $e->getMessage() . ' - ' . $e->getLine() . ' - ' . $e->getFile());
        }
        return back()->with('success', 'Correction request submitted successfully!');
    }
    public function renewal(){
        if (!in_array('Renewal Correction Request', auth()->user()->getPermissionNames()->toArray())) {
            return redirect()->route('branch.dashboard');
        }
        $data['title'] = 'Renewal Correction Request';
        $data['company'] = Companies::where('status',1)->pluck('name','id');
        return view('templates/branch/CorrectionManagement/renewal',$data);
    }

    public function renewalList(Request $request)
    {
        if ($request->ajax()) {
            $arrFormData = array();
            if (!empty($_POST['searchform'])) {
                foreach ($_POST['searchform'] as $frm_data) {
                    $arrFormData[$frm_data['name']] = $frm_data['value'];
                }
            }
            if (isset($arrFormData['is_search']) && $arrFormData['is_search'] == 'yes') {
                $data = CorrectionRequests::with([
                    'correctionCompay:id,name,short_name',
                    'branch:id,name',
                    'MemberInvestment:id,account_number,member_id,customer_id,company_id,plan_id',
                    'MemberInvestment.member:id,member_id,first_name,last_name',
                    'MemberInvestment.memberCompany:id,member_id,customer_id,company_id',
                    'MemberInvestment.plan:id,name',
                    'correctionSavingAccount',
                    'correctionDaybookCustom'
                ])->has('correctionCompay')
                ->where('correction_type',3);
                $getBranchId = getUserBranchId(Auth::user()->id);
                $arrFormData['branch_id'] = $getBranchId->id;
                if (isset($arrFormData['branch_id']) && $arrFormData['branch_id'] != '0') {
                    $id = $arrFormData['branch_id'];
                    $data = $data->where('branch_id', '=', $id);
                }
                if ($arrFormData['company_id'] != '' && $arrFormData['company_id'] != '0') {
                    $company_id = $arrFormData['company_id'];
                    $data = $data->where('company_id', $company_id);
                }
                if ($arrFormData['account_no'] != '') {
                    $account_no = $arrFormData['account_no'];
                    $data = $data->whereHas('MemberInvestment', function ($query) use ($account_no) {
                        $query->where('member_investments.account_number', $account_no );
                    });
                }
                if ($arrFormData['status'] != '') {
                    $status = $arrFormData['status'];
                    $data = $data->where('status', $status);
                }
                if ($arrFormData['member_name'] != '') {
                    $member_name = $arrFormData['member_name'];
                    $data = $data->whereHas('MemberInvestment.member', function ($query) use ($member_name) {
                        $query->where('first_name', 'LIKE', '%' . $member_name . '%')
                            ->orWhere('last_name', 'LIKE', '%' . $member_name . '%')
                            ->orWhere(DB::raw('concat(first_name," ",last_name)'), 'LIKE', "%$member_name%");
                    });
                }
                if (isset($arrFormData['correction_date']) && $arrFormData['correction_date'] != '') {
                    $startDate = date('Y-m-d',strtotime(convertDate($arrFormData['correction_date'])));
                    $data = $data->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $startDate]);
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
                $Cache = Cache::put('correctionlist_renewal' . $token, $datac);
                Cache::put('correctionlist_renewal_count' . $token, $count);
                $status = [
                    0=>'PENDING',
                    1=>'CORRECTED',
                    2=>'REJECTED',
                ];
                foreach ($data as $row) {
                    $sno++;
                    $amount = 0;
                    if($row->plan_category != 'S'){
                        $amount = $row['correctionDaybookCustom'] ? $row['correctionDaybookCustom']['amount'] : 'N/A';
                    }else{
                        $amount = $row['correctionSavingAccount'] ? $row['correctionSavingAccount']['deposit'] : 'N/A';
                    }
                    $val['DT_RowIndex'] = $sno;
                    $val['company'] = $row['correctionCompay'] ? $row['correctionCompay']->short_name : 'N/A';
                    $val['created_at']  = date("d/m/Y", strtotime(str_replace('-', '/', $row->created_at)));
                    $val['account_no'] = $row['MemberInvestment'] ? $row['MemberInvestment']['account_number'] : 'N/A';
                    $val['name'] = $row['MemberInvestment'] ? $row['MemberInvestment']['member']['first_name'] . ' ' . $row['MemberInvestment']['member']['last_name']??'' : '';
                    $val['customer_id'] = $row['MemberInvestment'] ? $row['MemberInvestment']['member']['member_id'] : 'N/A';
                    $val['member_id'] = $row['MemberInvestment'] ? $row['MemberInvestment']['memberCompany']['member_id'] : 'N/A';
                    $val['amount'] = $amount . ' &#8377;' ;
                    $val['plan'] = $row['MemberInvestment'] ? $row['MemberInvestment']['plan'] ? $row['MemberInvestment']['plan']['name'] : 'N/A' : 'N/A';
                    $val['correction_description'] = $row['correction_description'];
                    $val['rejected_correction_description'] = $row['rejected_correction_description']??'N/A';
                    $val['status'] = $status[$row->status];
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
    public function renewalListExport(Request $request){
        $token = session()->get('_token');
        $file = Session::get('_fileName');
        $data  = Cache::get('correctionlist_renewal' . $token);
        $count = Cache::get('correctionlist_renewal_count' . $token);
        $input = $request->all();
        $start = $input["start"];
        $limit = $input["limit"];
        $returnURL = URL::to('/') . "/asset/Correction_List_Renewal" . $file . ".csv";
        $fileName = env('APP_EXPORTURL') . "/asset/Correction_List_Renewal" . $file . ".csv";
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
        $status = [
            0=>'PENDING',
            1=>'CORRECTED',
            2=>'REJECTED',
        ];
        $sno = $_POST['start'];
        $rowReturn = [];
        $data = $data->toArray();
        $record = array_slice($data, $start, $limit);
        $totalCount = count($record);
        foreach ($record as $row) {
            $sno++;
            $amount = 0;
            // pd($row);
            if($row['plan_category'] != 'S'){
                $amount = $row['correction_daybook_custom']['amount'];
            }else{
                $amount = $row['correction_saving_account']['deposit'];
            }
            $val['S No.'] = $sno;
            $val['COMPANY'] = $row['correction_compay'] ? $row['correction_compay']['name'] : 'N/A';
            $val['CREATED AT']  = date("d/m/Y", strtotime(str_replace('-', '/', $row['created_at'])));
            $val['ACCOUNT NUMBER'] = $row['member_investment'] ? $row['member_investment']['account_number'] : 'N/A';
            $val['NAME'] = $row['member_investment'] ? $row['member_investment']['member']['first_name'] . ' ' . $row['member_investment']['member']['last_name']??'' : 'N/A';
            $val['CUSTOMER ID'] = $row['member_investment'] ? $row['member_investment']['member']['member_id'] : 'N/A';
            $val['MEMBER ID'] = $row['member_investment'] ? $row['member_investment']['member_company']['member_id'] : 'N/A';
            $val['AMOUNT'] = $amount;
            $val['PLAN'] = $row['member_investment'] ? $row['member_investment']['plan'] ? $row['member_investment']['plan']['name'] : 'N/A' : 'N/A';
            $val['CORRECTION DESCRIPTION'] = $row['correction_description'];
            $val['REJECTED CORRECTION DESCRIPTION'] = $row['rejected_correction_description']??'N/A';
            $val['status'] = $status[$row['status']];
            if (!$headerDisplayed) {
                fputcsv($handle, array_keys($val));
                $headerDisplayed = true;
            }
            fputcsv($handle, $val);
        }
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
