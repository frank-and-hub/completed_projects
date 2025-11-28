<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Response;
use App\Models\User;
use App\Models\Member;
use App\Models\Plans;
use App\Models\Memberinvestments;
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
use App\Models\Branch;
use App\Models\AssociateCommission;
use App\Models\Daybook;
use App\Models\CorrectionRequests;
use App\Http\Controllers\Admin\CommanController;
use DB;
use Illuminate\Support\Facades\Cache;
use Validator;
use Session;
use Redirect;
use App\Scopes\ActiveScope;
use URL;
use App\Services\Sms;
use App\Models\SamraddhBank;

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
        if (check_my_permission(Auth::user()->id, "22") != "1") {
            return redirect()->route('admin.dashboard');
        }
        $data['title'] = "Renewal List";
        $data['branch'] = Branch::where('status', 1)->get(['id', 'name']);
        /*$data['plans'] = Plans::where('status',1)->where('id','!=',1)->get();	*/
        $data['plans'] = Plans::withoutGlobalScope(ActiveScope::class)->whereNotIn('id', array(1, 4, 8, 9))->get(['id', 'name']);
        return view('templates.admin.investment_management.renewaldetails.renewaldetails-listing', $data);
    }

    public function getCompanyIdPlans(Request $request)
    {

        $company_id = $request->company_id;
        $excludedCategories = ['S'];
        $data['plan'] = Plans::where('company_id', $company_id)
        ->whereNotIn('plan_category_code', $excludedCategories)->withoutGlobalScope(ActiveScope::class)
        ->pluck('name', 'id');
       
        return json_encode($data);
    }
    /**
     * Fetch invest listing data.
     *
     * @param  \App\Reservation  $reservation
     * @return \Illuminate\Http\Response
     */
    public function renewaldetailsListing(Request $request)
    {
        if ($request->ajax() && check_my_permission(Auth::user()->id, "22") == "1") {
            $company_id = $request->company_id;
            // $arrFormData = [];
            // if (!empty($_POST['searchform'])) {
            //     foreach ($_POST['searchform'] as $frm_data) {
            //         $arrFormData[$frm_data['name']] = $frm_data['value'];
            //     }
            // }
            $arrFormData['start_date'] = $start_date =  $request->start_date;
            $arrFormData['end_date'] = $end_date =  $request->end_date;
            
            $startDate = date("Y-m-d", strtotime(convertDate($start_date)));
            $endDate = date("Y-m-d", strtotime(convertDate($end_date)));

            $arrFormData['branch_id'] = $branch_id =  $request->branch_id;
            $arrFormData['plan_id'] = $plan_id =  $request->plan_id;
            $arrFormData['scheme_account_number'] = $scheme_account_number =  $request->scheme_account_number;
            $arrFormData['transaction_by'] = $transaction_by =  $request->transaction_by;
            $arrFormData['name'] = $name =  $request->name;
            $arrFormData['member_id'] = $member_id =  $request->member_id;
            $arrFormData['associate_code'] = $associate_code =  $request->associate_code;
            $arrFormData['is_search'] = $is_search =  $request->is_search;
            $arrFormData['investments_export'] = $investments_export =  $request->investments_export;
            $arrFormData['account_no'] = $account_no =  $request->account_no;
            $pid = 1;
          
            if (isset($is_search) && $is_search == 'yes') {
                //only cloumns needed
                $get = ['id', 'payment_mode', 'branch_id', 'member_id', 'associate_id', 'investment_id', 'account_no', 'created_at', 'amount', 'is_app', 'company_id'];
                $data = \App\Models\Daybook::has('company')->
                    select($get)
                    ->with([
                        'dbranch:id,name,branch_code,sector,zone',
                        'MemberCompany:id,member_id,customer_id',
                        'MemberCompany.member:id,member_id,first_name,last_name',
                        'company:id,name',
                        'member:id,member_id,first_name,last_name',
                        'associateMember:id,associate_no,first_name,last_name',
                        'investment:id,plan_id,account_number,tenure,created_at,deposite_amount,branch_id',
                        'investment.plan:id,name',
                        'investment.branch:id,name'
                        ])
                    ->whereHas('investment', function ($query) use ($pid) {
                        $query->where('member_investments.plan_id', '!=', $pid);
                    })
                    ->where('transaction_type', 4)
                    ->where('is_deleted', 0)
                    ->when((Auth::user()->branch_id > 0),function($query){
                        $query->where('branch_id', '=', Auth::user()->branch_id);
                    })
                    ->when(($start_date!=''),function($query)use($startDate,$endDate){
                        $query->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate]);
                    })
                    ->when(($branch_id!=''),function($query)use($branch_id){
                        $query->when(($branch_id!='0'),function($query)use($branch_id){
                            $query->where('branch_id', '=', $branch_id);
                        });
                    })
                    ->when(($account_no!=''),function($query)use($account_no){
                        $query->when(($account_no!='0'),function($query)use($account_no){
                            $query->where('account_no', '=', $account_no);
                        });
                    })
                    ->when(($plan_id!=''),function($query)use($plan_id){
                        $query->whereHas('investment', function ($query) use ($plan_id) {
                            $query->where('member_investments.plan_id', '=', $plan_id);
                        });
                    })
                    ->when(($scheme_account_number!=''),function($query)use($scheme_account_number){
                        $query->where('account_number', 'LIKE', '%' . $scheme_account_number . '%');
                    })
                    ->when(($transaction_by!=''),function($query)use($transaction_by){
                        $query->where('is_app', '=', $transaction_by);
                    })
                    ->when(($member_id!=''),function($query)use($member_id){
                        $query->whereHas('member', function ($query) use ($member_id) {
                            $query->where('members.member_id', 'LIKE', '%' . $member_id . '%');
                        });
                    })
                    ->when(($associate_code!=''),function($query)use($associate_code){
                        $query->whereHas('associateMember', function ($query) use ($associate_code) {
                            $query->where('members.associate_no', 'LIKE', '%' . $associate_code . '%');
                        });
                    })
                    ->when(($name!=''),function($query)use($name){
                        $query->whereHas('member', function ($query) use ($name) {
                            $query->where('members.first_name', 'LIKE', '%' . $name . '%')
                                ->orWhere('members.last_name', 'LIKE', '%' . $name . '%')
                                ->orWhere(DB::raw('concat(members.first_name," ",members.last_name)'), 'LIKE', "%$name%");
                        });
                    })
                    ->when(($company_id!=''),function($query)use($company_id){
                        $query->when(($company_id!='0'),function($query)use($company_id){
                            $query->where('company_id', '=', $company_id);
                        });
                    })
                    ;
                $count = $data->orderby('id', 'DESC')->count('id');
                // this is for cache
                $data_2=$data->orderby('id', 'DESC')->get();                
                //this is for cache
                $token = session()->get('_token');
                //Set caches
                Cache::put('renewalexport_list'.$token, $data_2->toArray());
                Cache::put('renewalexport_count'.$token, $count);
                //End Set caches
                $data = $data->orderby('id', 'DESC')->offset($_POST['start'])->limit($_POST['length'])->get();
              
                $totalCount = $count; 
                $sno = $_POST['start'];
                $rowReturn = array();
                $payment_mode = [
                    0 => "Cash",
                    1 => "Cheque",
                    2 => "DD",
                    3 => "Online",
                    4 => "By Saving Account",
                    5 => "From Loan Amount"
                ];
                foreach ($data as $row) {
                    
                    $planId = isset($row['investment']['plan_id']) ? $row['investment']['plan_id'] : 0;
                    $planName = isset($row['investment']['plan']) ? $row['investment']['plan']->name : '';
                    $plan = $planId > 0 ? $planName : 'N/A';
                    $tenure = 'N/A';
                    if ($planId != 1) {
                        $tenure = isset($row['investment']['tenure']) ? $row['investment']['tenure'] . ' Year' : 'N/A';
                    }
                    $sno++;
                    $val = [
                        'DT_RowIndex' => $sno,
                        'created_at' => date("d/m/Y", strtotime($row['created_at'])),
                        'tran_by' => (($row['is_app'] == 1) ? 'Associate' : ($row['is_app'] == 2 ? 'E-Passbook' : 'Software')),
                        'branch' => $row['dbranch']['name'] ?? 'N/A',
                        'branch_code' => $row['dbranch']['branch_code'] ?? 'N/A',
                        'sector_name' => $row['dbranch']['sector'] ?? 'N/A',
                        'region_name' => $row['dbranch']['branch_code'] ?? 'N/A',
                        'zone_name' => $row['dbranch']['zone'] ?? 'N/A',
                        'customer_id' => isset($row['MemberCompany']['member']) ? ($row['MemberCompany']['member']->member_id ?? 'N/A') : 'N/A',
                        'member_id' => $row['MemberCompany']->member_id??'N/A',
                        'account_number' => $row['account_no']??'N/A',
                        'company' => $row['company']['name'] ?? 'N/A',
                        'member' => isset($row['MemberCompany']['member']) ? $row['MemberCompany']['member']->first_name . ' ' . $row['MemberCompany']['member']->last_name : 'N/A',
                        'plan' => $plan,
                        'tenure' => $tenure,
                        'amount' => number_format((float)($row['amount']??0), 2, '.', ''),
                        'associate_code' => isset($row['associateMember']) ? $row['associateMember']['associate_no'] : 'N/A',
                        'associate_name' => isset($row['associateMember']) ? $row['associateMember']['first_name'] . ' ' . ($row['associateMember']['last_name']??'') : 'N/A',
                        'payment_mode' => $payment_mode[$row->payment_mode],
                        'account_opening_date' => isset($row['investment']->created_at) ? date('d/m/Y', strtotime($row['investment']->created_at)) : 'N/A',
                        'demo_amount' => $row['investment']->deposite_amount ?? 'N/A',
                        'mother_branch' => $row['investment']['branch']->name ?? 'N/A',
                    ];                    
                    
                    $rowReturn[] = $val;
                }
                
                $output = [
                    "draw" => $_POST['draw'], 
                    "recordsTotal" => $totalCount, 
                    "recordsFiltered" => $count, 
                    "data" => $rowReturn
                ];
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
     * Display a form of the register plans.
     *
     * @return \Illuminate\Http\Response
     */
}
