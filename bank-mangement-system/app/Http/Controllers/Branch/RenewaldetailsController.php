<?php
namespace App\Http\Controllers\Branch;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Response;
use Validator;
use Session;
use Redirect;
use URL;
use DB;
use App\Models\User;
use App\Models\Member;
use App\Models\Plans;
use App\Models\Memberinvestments;
use App\Models\MemberReinvest;
use App\Models\Memberinvestmentsnominees;
use App\Models\Memberinvestmentspayments;
use App\Models\SavingAccount;
use App\Models\Investmentplantransactions;
use App\Models\Transcation;
use App\Models\SavingAccountTranscation;
use App\Models\MemberNominee;
use App\Models\Relations;
use App\Models\Investmentplanamounts;
use App\Models\MemberIdProof;
use App\Models\Daybook;
use App\Models\TranscationLog;
use App\Http\Controllers\Branch\CommanTransactionsController;
use Illuminate\Support\Facades\Schema;
use App\Services\Sms;
use App\Models\AssociateCommission;
use App\Models\CorrectionRequests;
use App\Models\SamraddhBank;
use Illuminate\Support\Facades\Cache;

use App\Scopes\ActiveScope;
class RenewaldetailsController extends Controller
{
    /**
     * Instantiate a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }
    /**
     * Display a listing of the investment plans.
     *
     * @return \Illuminate\Http\Response
     */
    public function renewaldetails()
    {
        if (!in_array('Renewal List', auth()->user()->getPermissionNames()->toArray())) {
            return redirect()->route('branch.dashboard');
        }
       
        $title = "Renewal List";
        $pid = 1;
        $plans = Plans::whereNotIn('id',array(1,4,8,9))->pluck('name', 'id');

        /*echo '<pre>';
        print_r($getDaybook);
        exit;*/
        return view('templates.branch.investment_management.renewaldetails.renewaldetails-listing', compact(['title','plans']));
    }

    public function getCompanyIdPlans(Request $request){
        $company_id = $request->company_id;
        $excludedCategories = ['S'];
        $plans = Plans::where('company_id', $company_id)
        ->whereNotIn('plan_category_code', $excludedCategories)->withoutGlobalScope(ActiveScope::class)
        ->pluck('name', 'id');
        return json_encode($plans);
    }
    /**
     * Fetch invest listing data.
     *
     * @param  \App\Reservation  $reservation
     * @return \Illuminate\Http\Response
     */
    public function renewaldetailsListing(Request $request)
    {
        if ($request->ajax()) {
            $getBranchId = getUserBranchId(Auth::user()->id);
            $branch_id = $getBranchId->id;
            $arrFormData['start_date'] = $request->start_date;
            $company_id = $request->company_id;
            $arrFormData['end_date'] = $request->end_date;
            $arrFormData['branch_id'] = $request->branch_id;
            $arrFormData['plan_id'] = $request->plan_id;
            $arrFormData['transaction_by'] = $request->transaction_by;
            $arrFormData['scheme_account_number'] = $request->scheme_account_number;
            $arrFormData['name'] = $request->name;
            $arrFormData['member_id'] = $request->member_id;
            $arrFormData['associate_code'] = $request->associate_code;
            $arrFormData['is_search'] = $request->is_search;
            $arrFormData['investments_export'] = $request->investments_export;
            $arrFormData['account_no'] = $request->account_no;
            $pid = 1;

            
            // $data = Memberinvestments::with('plan','member','associateMember','branch');
            /******* fillter query start ****/
            if (isset($arrFormData['is_search']) && $arrFormData['is_search'] == 'yes') {
                    $data = \App\Models\Daybook::with([
                        'dbranch',
                        'company:id,name',
                        'member:id,member_id,first_name,last_name',
                        'MemberCompany',
                        'associateMember:id,associate_no,first_name,last_name',
                        'investment' => function ($query) {
                            $query->select('id', 'plan_id', 'account_number', 'tenure','member_id','customer_id');
                        }
                    ])
                        ->whereHas('investment', function ($query) use ($pid) {
                            $query->where('member_investments.plan_id', '!=', $pid);
                        })->where('transaction_type', 4)->where('is_deleted', 0)
                        ->where('branch_id', $branch_id);
                if ($arrFormData['start_date'] != '') {
                    $startDate = $arrFormData['start_date'];
                    $endDate = $arrFormData['end_date'];
                    $startDate = date("Y-m-d", strtotime(convertDate($startDate)));
                    $endDate = date("Y-m-d", strtotime(convertDate($endDate)));
                    $data = $data->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate]);
                }
                if ($arrFormData['branch_id'] != '') {
                    $id = $arrFormData['branch_id'];
                    $data = $data->where('branch_id', '=', $id);
                }
                if ($arrFormData['account_no'] != '') {
                    $account_no = $arrFormData['account_no'];
                    $data = $data->where('account_no', '=', $account_no);
                }
                if ($arrFormData['plan_id'] != '') {
                    $planId = $arrFormData['plan_id'];
                    /* $data=$data->where('plan_id','=',$planId);*/
                    $data = $data->whereHas('investment', function ($query) use ($planId) {
                        $query->where('member_investments.plan_id', '=', $planId);
                    });
                }
                if ($arrFormData['scheme_account_number'] != '') {
                    $sAccountNumber = $arrFormData['scheme_account_number'];
                    $data = $data->where('account_number', 'LIKE', '%' . $sAccountNumber . '%');
                }
				if (isset($arrFormData['transaction_by']) && $arrFormData['transaction_by'] != '') {
					$transaction_by   = $arrFormData['transaction_by'];
					$data = $data->where('is_app', '=', $transaction_by);
				}
                if ($arrFormData['member_id'] != '') {
                    $meid = $arrFormData['member_id'];
                    $data = $data->whereHas('member', function ($query) use ($meid) {
                        $query->where('members.member_id', 'LIKE', '%' . $meid . '%');
                    });
                }
                if ($arrFormData['associate_code'] != '') {
                    $associateCode = $arrFormData['associate_code'];
                    $data = $data->whereHas('associateMember', function ($query) use ($associateCode) {
                        $query->where('members.associate_no', 'LIKE', '%' . $associateCode . '%');
                    });
                }
                if ($arrFormData['name'] != '') {
                    $name = $arrFormData['name'];
                    $data = $data->whereHas('member', function ($query) use ($name) {
                        $query->where('members.first_name', 'LIKE', '%' . $name . '%')->orWhere('members.last_name', 'LIKE', '%' . $name . '%')->orWhere(DB::raw('concat(members.first_name," ",members.last_name)'), 'LIKE', "%$name%");
                    });
                }
             
                if($company_id != ''){
            
                    $data = $data->where('company_id',$company_id);
                   
                }              
            }else{
                return json_encode([]);
            }

            /******* fillter query End ****/
            $count = $data->orderby('id', 'DESC')->count();
            $data_2=$data->orderby('id', 'DESC')->get();
            //$count=count($data1);
            $data = $data->orderby('id', 'DESC')->offset($_POST['start'])->limit($_POST['length'])->get();
            // $totalCount = \App\Models\Daybook::with([
            //     'member',
            //     'associateMember',
            //     'investment' => function ($query) {
            //         $query->select('id', 'plan_id', 'account_number', 'tenure');
            //     }
            // ])
            //     ->whereHas('investment', function ($query) use ($pid) {
            //         $query->where('member_investments.plan_id', '!=', $pid);
            //     })->where('transaction_type', 4)->where('is_deleted', 0)
            //     ->where('branch_id', $branch_id)->count();
            $totalCount = $count;
            $sno = $_POST['start'];
            $rowReturn = array();
            $paymentMode = [
                0 => "Cash",
                1 => "Cheque",
                2 => "DD",
                3 => "Online",
                4 => "By Saving Account",
                5 => "From Loan Amount"
            ];
            foreach ($data as $row) {
                $planName = '';
                $planId = $row['investment']['plan_id']??'N/A';    
                if ($planId > 0) {
                    $PlanDetail = getPlanDetail($planId);
                    if (!empty($PlanDetail)) {
                        $planName = $PlanDetail->toArray()['name'];
                    }
                }
                $tenure = '';
                if ($planId == 1) {
                    $tenure = 'N/A';
                } else {
                    $tenure = $row['investment']['tenure'] . ' Year';
                }
                $sno++;
                $val = [
                    'DT_RowIndex' => $sno,
                    'created_at' => date("d/m/Y", strtotime($row['created_at'])),
                    'tran_by' => (($row['is_app'] == 1) ? 'Associate' : (($row['is_app'] == 2) ? 'E-Passbook' : 'Software')),
                    'branch' => $row['dbranch']['name']??'N/A',
                    'branch_code' => $row['dbranch']['branch_code']??'N/A',
                    'sector_name' => $row['dbranch']['sector']??'N/A',
                    'region_name' => $row['dbranch']['sector']??'N/A',
                    'zone_name' => $row['dbranch']['zone']??'N/A',
                    'customer_id' => $row['MemberCompany']['member']->member_id??'N/A',
                    'member_id' => $row['MemberCompany']->member_id??'N/A',
                    'account_number' => $row['account_no']??'N/A',
                    'company' => $row['company']['name']??'N/A',
                    'member' => isset($row['MemberCompany']['member']) ? $row['MemberCompany']['member']->first_name . ' ' . $row['MemberCompany']['member']->last_name : 'N/A',
                    'plan' => $planName,
                    'tenure' => $tenure,
                    'amount' => $row['amount'],
                    'associate_code' => $row['associateMember'] ? $row['associateMember']['associate_no']??'N/A' : 'N/A',
                    'associate_name' => $row['associateMember'] ? $row['associateMember']['first_name'] . ' ' . $row['associateMember']['last_name']??'' : 'N/A',
                    'payment_mode' => $paymentMode[$row->payment_mode],
                ];
                $rowReturn[] = $val;
            }
            $token = session()->get('_token');
             //Set caches
             Cache::put('renewalexport_list_branch'.$token, $data_2->toArray());
             Cache::put('renewalexport_count_branch'.$token, $count);
             //End Set caches 

             
            $output = array("draw" => $_POST['draw'], "recordsTotal" => $totalCount, "recordsFiltered" => $count, "data" => $rowReturn, );
            return json_encode($output);
        }
    }
/**
 * Display a form of the register plans.
 *
 * @return \Illuminate\Http\Response
 */
}