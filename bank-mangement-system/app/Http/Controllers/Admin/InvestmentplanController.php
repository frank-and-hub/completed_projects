<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Response;
use App\Models\Member;
use App\Models\Plans;
use App\Models\Memberinvestments;
use App\Models\Memberinvestmentsnominees;
use App\Models\Memberinvestmentspayments;
use App\Models\SavingAccount;
use App\Models\Investmentplantransactions;
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
use App\Models\AccountBranchTransfer;
use DB;
use Session;
use Redirect;
use URL;
use App\Services\Sms;
use App\Models\SamraddhBank;
use Investment;
use CommanTransactionFacade;
use App\Interfaces\RepositoryInterface;
use Illuminate\Support\Facades\Cache;
use App\Scopes\ActiveScope;

class InvestmentplanController extends Controller
{
  private $repository;
  /**
   * Instantiate a new controller instance.
   *
   * @return void
   */
  public function __construct(RepositoryInterface $repository)
  {
    $this->repository = $repository;
    $this->middleware('auth');
  }
  /**
   * Display a listing of the investment plans.
   *
   * @return \Illuminate\Http\Response
   */
  public function investments()
  {
    if (check_my_permission(Auth::user()->id, "19") != "1") {
      return redirect()->route('admin.dashboard');
    }
    $data['title'] = "Investment Plans";
    $data['branch'] = Branch::select('id', 'name')->where('status', 1)->get();
    $data['plans'] = Plans::withoutGlobalScope(ActiveScope::class)->has('company')->whereStatus('1')->pluck('name', 'id');
    return view('templates.admin.investment_management.investment-listing', $data);
  }
  public function getCompanyToPlan(Request $request)
  {
    $company_id = $request->company_id;
    $data['plan'] = Plans::withoutGlobalScope(ActiveScope::class)
      ->has('company')
      ->when($company_id != '0', function ($q) use ($company_id) {
        $q->where('company_id', $company_id);
      })
      ->pluck('name', 'id');
    // $data['plan'] = Plans::where('company_id', $company_id)->pluck('name', 'id');

    return json_encode($data);
  }
  /**
   * Fetch invest listing data.
   *
   * @param  \App\Reservation  $reservation
   * @return \Illuminate\Http\Response
   */
  public function investmentsListing(Request $request)
  {
    if ($request->ajax() && check_my_permission(Auth::user()->id, "19") == "1") {
      $arrFormData['start_date'] = $request->start_date;
      $company_id = $request->company_id;
      $arrFormData['end_date'] = $request->end_date;
      $arrFormData['branch_id'] = $request->branch_id;
      $arrFormData['plan_id'] = $request->plan_id;
      $arrFormData['scheme_account_number'] = $request->scheme_account_number;
      $arrFormData['name'] = $request->name;
      $arrFormData['customer_id'] = $request->customer_id;
      $arrFormData['member_id'] = $request->member_id;
      $arrFormData['associate_code'] = $request->associate_code;
      $arrFormData['is_search'] = $request->is_search;
      $arrFormData['investments_export'] = $request->investments_export;


      if (isset($arrFormData['is_search']) && $arrFormData['is_search'] == 'yes') {
        $data = Memberinvestments::has('company')->select('id', 'form_number', 'company_id', 'plan_id', 'tenure', 'account_number', 'deposite_amount', 'branch_id', 'associate_id', 'member_id', 'created_at', 'current_balance')
          ->with([
            'company:id,name',
            'ssbBalanceView',
            'memberCompany.member:id,member_id,first_name,last_name,mobile_no,state_id,district_id,city_id,village,pin_code,address',
            'memberCompany.member.memberIdProof.idTypeFirst:id,name',
            'memberCompany.member.memberIdProof.idTypeSecond:id,name',
            'CollectorAccount.member_collector:id,associate_no,first_name,last_name',
            'branch:id,branch_code,name,sector,regan,zone',
            'associateMember:id,associate_no,first_name,last_name,associate_no',
            'InvestmentBalance:totalBalance,account_number'
          ])
          ->with([
            'plan' => function ($q) {
              $q->withoutGlobalScope(ActiveScope::class);
            }
          ])
          ->with(['ssb_detailb:id,balance,member_investments_id'])
          ->where('is_deleted', 0);
        /******* fillters query start ****/
        if ($company_id != '0') {
          $data = $data->where('company_id', $company_id);
        }

        if ($arrFormData['start_date'] != '') {
          $startDate = $arrFormData['start_date'];

          $startDate = date("Y-m-d", strtotime(convertDate($startDate)));


          if ($arrFormData['end_date'] != '') {
            $endDate = $arrFormData['end_date'];
            $endDate = date("Y-m-d", strtotime(convertDate($endDate)));
          } else {
            $endDate = '';
          }

          $data = $data->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate]);
        }

        if ($request->branch_id != '') {
          $id = $arrFormData['branch_id'];
          if ($id != '0') {
            $data = $data->where('branch_id', '=', $id);
          }
        }

        if ($arrFormData['plan_id'] != '') {
          $planId = $arrFormData['plan_id'];
          $data = $data->where('plan_id', '=', $planId);
        }

        if ($arrFormData['scheme_account_number'] != '') {
          $sAccountNumber = $arrFormData['scheme_account_number'];
          $data = $data->where('account_number', 'LIKE', '%' . $sAccountNumber . '%');
        }
        if ($arrFormData['member_id'] != '') {
          $meid = $arrFormData['member_id'];
          $data = $data->whereHas('memberCompany', function ($query) use ($meid) {
            $query->where('member_companies.member_id', 'LIKE', '%' . $meid . '%');
          });
        }
        if ($arrFormData['associate_code'] != '') {
          $associateCode = $arrFormData['associate_code'];
          $data = $data->whereHas('associateMember', function ($query) use ($associateCode) {
            $query->where('members.associate_no', 'LIKE', '%' . $associateCode . '%');
          });
        }
        if ($arrFormData['customer_id'] != '') {
          $customer_id = $arrFormData['customer_id'];
          $data = $data->whereHas('memberCompany.member', function ($query) use ($customer_id) {
            $query->where('members.member_id', 'LIKE', '%' . $customer_id . '%');
          });
        }
        if ($arrFormData['name'] != '') {
          $name = $arrFormData['name'];
          $data = $data->whereHas('member', function ($query) use ($name) {
            $query->where('members.first_name', 'LIKE', '%' . $name . '%')
              ->orWhere('members.last_name', 'LIKE', '%' . $name . '%')
              ->orWhere(DB::raw('concat(members.first_name," ",members.last_name)'), 'LIKE', "%$name%");
          });
        }



        // else
        // {
        //     $startDate = date('Y-m-d', strtotime($request->investment_listing_currentdate));
        //     $endDate = date('Y-m-d', strtotime($request->investment_listing_currentdate));
        // }
        /******* fillter query End ****/

        // start Get cache


        // End Get cache

        $count = $data->count('id');
        $token = session()->get('_token');
        $data_2 = $data->orderby('id', 'DESC')->get()->toArray();
        //Set value on caches
        Cache::put('investmentsListing_listAdmin' . $token, $data_2);
        Cache::put('investmentsListing_countAdmin' . $token, $count);
        //End Set value on caches
        $data = $data->orderby('id', 'DESC')->skip($_POST['start'])->take($_POST['length'])->get();
        $totalCount = $count;
        // if (Auth::user()->branch_id > 0) {
        //   $totalCount = Memberinvestments::where('branch_id', '=', Auth::user()->branch_id)->count('id');
        // } else {
        //   $totalCount = Memberinvestments::count('id');
        // }
        $sno = $_POST['start'];
        $rowReturn = array();
        // echo('<pre>'); print_r($data->toArray()); die;
        foreach ($data as $row) {
          //  echo('<pre>'); print_r($row->toArray()); die;
          $sno++;
          $val['DT_RowIndex'] = $sno;
          $val['created_at'] = date("d/m/Y", strtotime($row['created_at']));
          $val['form_number'] = $row->form_number;
          $val['plan'] = $row['plan']->name;
          $val['company'] = $row['company']->name ?? 'N/A';
          $val['branch'] = $row['branch']->name;
          $val['member'] = isset($row['memberCompany']['member']->first_name) ? $row['memberCompany']['member']->first_name . ' ' . $row['memberCompany']['member']->last_name : 'N/A';
          $val['customer_id'] = isset($row['memberCompany']['member']['member_id']) ? $row['memberCompany']['member']['member_id'] : 'N/A';
          $val['member_id'] = isset($row['memberCompany']['member_id']) ? $row['memberCompany']['member_id'] : 'N/A';
          $val['mobile_number'] = isset($row['memberCompany']['member']['mobile_no']) ? $row['memberCompany']['member']['mobile_no'] : 'N/A';

          if (isset($row['associateMember'])) {
            $val['associate_name'] = $row['associateMember']['first_name'] . ' ' . $row['associateMember']['last_name'];
            $val['associate_code'] = $row['associateMember']['associate_no'];
          } else {
            $val['associate_name'] = "N/A";
            $val['associate_code'] = "N/A";
          }

          $val['collectorcode'] = $row['CollectorAccount'] ? isset($row['CollectorAccount']['member_collector']['associate_no']) ? $row['CollectorAccount']['member_collector']['associate_no'] : $row['associateMember']['associate_no'] : 'N/A';
          $val['collectorname'] = $row['CollectorAccount'] ? isset($row['CollectorAccount']['member_collector']['first_name']) ? $row['CollectorAccount']['member_collector']['first_name'] . ' ' . $row['CollectorAccount']['member_collector']['last_name'] : $row['associateMember']['first_name'] . ' ' . $row['associateMember']['last_name'] : 'N/A';


          $val['account_number'] = $row['account_number'];

          if ($row->plan->plan_category_code == "S") {
            $tenure = 'N/A';
          } else {
            $tenure = number_format((float) $row->tenure, 2, '.', '') . ' Year';
          }
          $val['tenure'] = $tenure;
          if ($row->plan->plan_category_code == "S") {
            $amount = $row->ssbBalanceView ? $row->ssbBalanceView->totalBalance ?? 0 : 0;
            $current_balance = number_format((float) $amount, 2, '.', '');
          } else {
            $current_balance = $row['InvestmentBalance'] ? $row['InvestmentBalance']->totalBalance : 0;
          }
          $val['current_balance'] = $current_balance;
          $val['eli_amount'] = investmentEliAmountNew($row->id);
          $val['deposite_amount'] = $row->deposite_amount;

          $val['address'] = isset($row['memberCompany']['member']->address) ? preg_replace("/\r|\n/", "", $row['memberCompany']['member']->address) : 'N/A';
          $val['state'] = isset($row['memberCompany']['member']->state_id) ? getStateName($row['memberCompany']['member']->state_id) : 'N/A'; //getStateName($row['memberCompany']['member']->state_id);
          $val['district'] = isset($row['memberCompany']['member']->district_id) ? getDistrictName($row['memberCompany']['member']->district_id) : 'N/A'; //getDistrictName($row['memberCompany']['member']->district_id);
          $val['city'] = isset($row['memberCompany']['member']->city_id) ? getCityName($row['memberCompany']['member']->city_id) : 'N/A'; //getCityName($row['memberCompany']['member']->city_id);
          $val['village'] = isset($row['memberCompany']['member']->village) ? $row['memberCompany']['member']->village : 'N/A';
          $val['pin_code'] = isset($row['memberCompany']['member']->pin_code) ? $row['memberCompany']['member']->pin_code : 'N/A';

          $val['firstId'] = isset($row['memberCompany']['member']['memberIdProof']) ? $row['memberCompany']['member']['memberIdProof']['idTypeFirst']->name . ' - ' . $row['memberCompany']['member']['memberIdProof']->first_id_no : 'N/A';
          $val['secondId'] = isset($row['memberCompany']['member']['memberIdProof']) ? $row['memberCompany']['member']['memberIdProof']['idTypeSecond']->name . ' - ' . $row['memberCompany']['member']['memberIdProof']->second_id_no : 'N/A';

          // $val['branch_code'] = $row['branch']->branch_code;
          // $val['sector_name'] = $row['branch']->sector;
          // $val['region_name'] = $row['branch']->regan;
          // $val['zone_name'] = $row['branch']->zone;


          $btn = '';
          $url2 = URL::to("admin/investment-log/2/" . $row->id . "");
          $url = URL::to("admin/investment/details/" . $row->id);
          $reciptUrl = URL::to("admin/investment/recipt/" . $row->id . "");
          $passbookUrl = URL::to("admin/investment/passbook/transaction/" . $row->id . "/" . $row['plan']->plan_category_code . "");
          $commsissionUrl = URL::to("admin/investment/commission/" . $row->id . "");
          $editUrl = URL::to("admin/investment/edit/" . $row->id . "/?action=change-request");
          $btn_passbook = '';

          $passbookUrl_new = URL::to("admin/investment/passbook/transaction_new/" . $row->id . "/" . $row['plan']->plan_category_code . "");

          $btn .= '<a href="' . $url . '" title="View Detail"><i class="fas fa-eye text-default mr-2"></i></a>';
        //   $btn .= '<a href="' .$commsissionUrl. '" title="View Detail"><i class="fas fa-percent text-default mr-2"></i></a>';
          if (Auth::user()->id != "13") {
            $btn .= '<a href="' . $reciptUrl . '" title="View Receipt"><i class="fa fa-file mr-2" aria-hidden="true"></i></a>';
          }

          $btn_passbook .= '<a class="fa fa-file " href="' . $passbookUrl . '" title="View Old Transcations"><i class="   mr-2"></i></a>';
          $btn_passbook .= '<a class="far fa-file-alt" href="' . $passbookUrl_new . '" title="View New Transcations"><i class="    mr-2"></i></a>';
          $btn .= '<a class="far fa-file-alt" href="' . $url2 . '" title="Branch Transferred log"><i class="    mr-2"></i></a>';
          $val['action'] = $btn;
          $val['transaction'] = $btn_passbook;
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
  /**
   * Display a form of the register plans.
   *
   * @return \Illuminate\Http\Response
   */

  public function edit(Request $request, $id)
  {
    $data['title'] = "Investment Detail";
    $data['plans'] = Plans::all();
    $data['action'] = $request->segment(3);
    $data['investments'] = Memberinvestments::with('member:id,member_id,first_name,last_name,special_category_id', 'memberCompany:id,customer_id,member_id', 'company:id,name')->with([
      'plan' => function ($q) {
        $q->withoutGlobalScope(ActiveScope::class);
      }
    ])->findOrFail($id);
    $data['idProof'] = MemberIdProof::where('member_id', $data['investments']->member_id)->pluck('first_id_no')->first();
    $data['mDetails'] = Member::leftJoin('special_categories', 'members.special_category_id', '=', 'special_categories.id')
      ->leftJoin('saving_accounts', 'members.id', '=', 'saving_accounts.member_id')
      ->where('members.id', $data['investments']['member_id'])
      ->select('saving_accounts.id', 'saving_accounts.account_no', 'saving_accounts.balance')
      ->get();
    $data['aDetails'] = Member::leftJoin('carders', 'members.current_carder_id', '=', 'carders.id')
      ->where('members.id', $data['investments']['associate_id'])
      ->select('members.id', 'members.first_name', 'members.last_name', 'members.mobile_no', 'carders.name as carder_name', 'members.associate_no')
      ->get();
    $data['formName'] = $data['investments']['plan']->slug;
    $data['id'] = $id;
    $data['branches'] = Branch::all();
    // dd($data);
    return view('templates.admin.investment_management.edit', $data);
  }


  public function registerPlans()
  {
    if (check_my_permission(Auth::user()->id, "18") != "1") {
      return redirect()->route('admin.dashboard');
    }
    $data['title'] = "Investment Plans Registration";
    $data['plans'] = Plans::has('PlanTenures')->where('status', 1)->where('id', '!=', 3)->get(['id', 'slug', 'name']);
    $data['relations'] = Relations::all('id', 'name');
    $data['plans_tenure'] = $this->repository->getAllPlans()->where('status', 1)->has('PlanTenures')->get(['id', 'prematurity', 'prematurity', 'death_help']);
    //$data['branches'] = Branch::all('id','state_id','name');
    if (Auth::user()->branch_id > 0) {
      $id = Auth::user()->branch_id;
      $data['branches'] = Branch::where('status', 1)->where('id', $id)->get('id', 'state_id', 'name');
    } else {
      $data['branches'] = Branch::all('id', 'state_id', 'name');
    }
    return view('templates.admin.investment_management.assign', $data);
  }
  public function getBankList(Request $request)
  {
    $data['samraddhBanks'] = SamraddhBank::with('bankAccount:id,account_no,bank_id')->where('company_id', $request->companyId)->where('status', 1)->get();
    return json_encode($data);
  }
  /**
   * Get branch member details.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return JSON
   */
  public function getmember(Request $request)
  {
    $mId = $request->memberid;
    $companyId = $request->companyId;
    $memberDetail = Investment::getMember($mId)->with(['savingAccount', 'memberNominee', 'associateInvestment', 'memberIdProofs', 'memberCompany'])->whereHas('memberCompany', function ($query) use ($companyId) {
      $query->where('company_id', '=', $companyId);
    })
      ->first();
    if (isset($memberDetail->memberCompany)) {
      $memberDetail = $memberDetail;
      $resCount = 1;
    } else {
      $memberDetails = Investment::registerMember($memberDetail, $request);
      $resCount = 1;
    }
    $member = $memberDetail;
    if ($resCount > 0 && $memberDetail) {
      $countInvestment = count($memberDetail['associateInvestment']);
    } else {
      $countInvestment = 0;
    }
    $plans = $this->repository->getAllPlans()->has('PlanTenures')->whereCompanyId($companyId)->where('status', 1)->where('i', '!=', 3)->select(['id', 'slug', 'name', 'plan_category_code', 'company_id'])->get();
    // $member = Member::with('savingAccount','memberNominee')->leftJoin('special_categories', 'members.special_category_id', '=', 'special_categories.id')->leftJoin('member_id_proofs','members.id', '=','member_id_proofs.member_id')
    // ->where('members.member_id',$mId)
    // ->where('members.status',1)->where('members.is_block',0)
    // ->select('members.id','members.member_id','members.first_name','members.last_name','members.mobile_no','members.address','special_categories.name as special_category','member_id_proofs.first_id_no');
    // if(Auth::user()->branch_id>0){
    //   $member=$member->where('branch_id',Auth::user()->branch_id);
    // }
    // $member=$member->get();
    $return_array = compact('countInvestment', 'member', 'resCount', 'plans');
    return json_encode($return_array);
  }
  /**
   * Search branch members from keyword.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\Response
   */
  public function searchmember(Request $request)
  {
    $keyword = $request->keyword;
    $users = Member::with('savingAccount')->where('first_name', 'like', '%' . $keyword . '%')->where('status', 1)->get();
    $res = '';
    $res .= '<ul id="member-list">';
    foreach ($users as $user) {
      $res .= '<li class="selectmember" data-account="' . $user->member_id . '" data-val="' . $user->first_name . ' ' . $user->last_name . '" value="' . $user->member_id . '">' . $user->first_name . ' ' . $user->last_name . ' - (' . $user->member_id . ')' . '</li>';
      echo $res;
      die;
    }
    $res .= '</ul>';
    //return $res;
  }
  /**
   * Get associate member details.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return JSON
   */
  public function getAccociateMember(Request $request)
  {
    $mId = $request->memberid;
    $member = Member::leftJoin('carders', 'members.current_carder_id', '=', 'carders.id')
      ->where('members.associate_no', $mId)
      ->where('members.status', 1)
      ->where('members.is_deleted', 0)
      ->where('members.is_associate', 1)
      ->where('members.associate_status', 1)
      ->where('members.is_block', 0)
      ->select('members.id', 'members.first_name', 'members.last_name', 'members.mobile_no', 'carders.name as carders_name')
      ->get();
    $resCount = count($member);
    $return_array = compact('member', 'resCount');
    return json_encode($return_array);
  }
  /**
   * Get kanydhan yojna amount.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return JSON
   */
  public function kanyadhanAmount(Request $request)
  {
    $plan_id = $request->plan_id;
    $tenure = $request->tenure;
    $account_open_date = date('Y-m-d', strtotime(convertDate($request->account_open_date)));
    $investmentAmount = \App\Models\PlanDenos::whereHas('planTenure', function ($q) use ($tenure) {
      $q->select('roi', 'plan_id')->where('tenure', $tenure);
    })->with([
          'planTenure' => function ($q) use ($tenure) {
            $q->select('roi', 'plan_id')->where('tenure', $tenure);
          }
        ])->where('plan_id', $plan_id)->where('tenure', $tenure)->where('effective_from', '<=', $account_open_date)->where(function ($q) use ($account_open_date) {
          $q->where('effective_to', '>=', $account_open_date)->orWhere('effective_to', NULL);
        })->select('denomination', 'plan_id')->get();
    $resCount = count($investmentAmount);
    $return_array = compact('investmentAmount', 'resCount');
    return json_encode($return_array);
  }
  /**
   * Get plan form by plan name.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\Response
   */
  public function planForm(Request $request)
  {
    $plan = $request->plan;
    $mId = $request->memberAutoId;
    $member = MemberNominee::where('member_id', $mId)->get();
    $plans_tenure = $this->repository->getAllPlans()->has('PlanTenures')->with('PlanTenures:roi,id,plan_id,tenure')->where('status', '1')->where('death_help', '1')->where('loan_against_deposit', '1')->where('prematurity', '1')->first(['id', 'death_help', 'loan_against_deposit', 'prematurity']);
    $relations = Relations::all();
    $plan_amount = 0;
    // dd($plan);
    switch ($plan) {
      case "saving-account":
      case "monthly-income-scheme":
      case "special-samraddh-money-back":
      case "samraddh-jeevan":
        $account_setting = \App\Models\SsbAccountSetting::where('plan_type', 1)->where('user_type', 1)->where('status', 1)->first();
        $plan_amount = $account_setting->amount;
        break;
      case "saving-account-child":
        $account_setting = \App\Models\SsbAccountSetting::where('plan_type', 2)->where('user_type', 1)->where('status', 1)->first();
        $plan_amount = $account_setting->amount;
        break;
    }
    return view('templates.admin.investment_management.' . $plan . '.' . $plan . '', compact('member', 'relations', 'plans_tenure', 'plan_amount'));
  }
  public function investHeadCreate($amount, $globaldate, $investmentId, $planId, $cheque_no, $cheque_id, $cheque_date, $online_transction_no, $online_transction_date, $online_deposit_bank_id, $online_deposit_bank_ac_id, $branch_id, $associate_id, $memberId, $ssbId, $createDayBook, $payment_mode, $investmentAccountNoRd, $companyId)
  {
    $amount = $amount;
    $daybookRefRD = CommanController::createBranchDayBookReferenceNew($amount, $globaldate);
    $refIdRD = $daybookRefRD;
    $currency_code = 'INR';
    $headPaymentModeRD = 0;
    $payment_type_rd = 'CR';
    $type_id = $investmentId;
    $entry_date = date("Y-m-d", strtotime(convertDate($globaldate)));
    $entry_time = date("H:i:s", strtotime(convertDate($globaldate)));
    $created_by = '1';
    $created_by_id = Auth::user()->id;
    $created_at = date("Y-m-d H:i:s", strtotime(convertDate($globaldate)));
    $updated_at = date("Y-m-d H:i:s", strtotime(convertDate($globaldate)));
    $planDetail = getPlanDetail($planId);
    $type = 3;
    $sub_type = 31;
    $planCode = $planDetail->plan_code;
    $head_id = $planDetail->head_id;
    // if($planCode==709)
    // {
    //     $head1Invest=1;$head2Invest=8;$head3Invest=20;$head4Invest=59;$head5Invest=80;$head_id=80;
    // }
    // if($planCode==708)
    // {
    //   $head1Invest=1;$head2Invest=8;$head3Invest=20;$head4Invest=59;$head5Invest=85;$head_id=85;
    // }
    // if($planCode==705)
    // {
    //   $head1Invest=1;$head2Invest=8;$head3Invest=20;$head4Invest=57;$head5Invest=79;$head_id=79;
    // }
    // if($planCode==707)
    // {
    //   $head1Invest=1;$head2Invest=8;$head3Invest=20;$head4Invest=59;$head5Invest=81;$head_id=81;
    // }
    // if($planCode==713)
    // {
    //   $head1Invest=1;$head2Invest=8;$head3Invest=20;$head4Invest=59;$head5Invest=84;$head_id=84;
    // }
    // if($planCode==710)
    // {
    //   $head1Invest=1;$head2Invest=8;$head3Invest=20;$head4Invest=58;$head5Invest=NULL;$head_id=58;
    // }
    // if($planCode==712)
    // {
    //   $head1Invest=1;$head2Invest=8;$head3Invest=20;$head4Invest=57;$head5Invest=78;$head_id=78;
    // }
    // if($planCode==706)
    // {
    //   $head1Invest=1;$head2Invest=8;$head3Invest=20;$head4Invest=57;$head5Invest=77;$head_id=77;
    // }
    // if($planCode==704)
    // {
    //   $head1Invest=1;$head2Invest=8;$head3Invest=20;$head4Invest=59;$head5Invest=83;$head_id=83;
    // }
    // if($planCode==718)
    // {
    //   $head1Invest=1;$head2Invest=8;$head3Invest=20;$head4Invest=59;$head5Invest=82;$head_id=82;
    // }
    //  if($planCode==721)
    // {
    //   $head1Invest=1;$head2Invest=8;$head3Invest=207;$head4Invest=59;$head5Invest=82;$head_id=207;
    // }
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
    if ($payment_mode == 1) { // cheque moade
      $headPaymentModeRD = 1;
      $chequeDetail = \App\Models\ReceivedCheque::where('id', $cheque_id)->first();
      $cheque_no = $chequeDetail->cheque_no;
      $cheque_date = $cheque_date;
      $cheque_bank_from = $chequeDetail->bank_name;
      $cheque_bank_ac_from = $chequeDetail->cheque_account_no;
      $cheque_bank_ifsc_from = NULL;
      $cheque_bank_branch_from = $chequeDetail->branch_name;
      $cheque_bank_to = $chequeDetail->deposit_bank_id;
      $cheque_bank_ac_to = $chequeDetail->deposit_account_id;
      $getBankHead = \App\Models\SamraddhBank::where('id', $cheque_bank_to)->first();
      $head11 = 2;
      $head21 = 10;
      $head31 = 27;
      $head41 = $getBankHead->account_head_id;
      $head51 = NULL;
      $rdDesDR = 'Bank A/c Dr ' . $amount . '/-';
      $rdDesCR = 'To ' . $planDetail->name . '(' . $investmentAccountNoRd . ')  A/c Cr ' . $amount . '/-';
      $rdDes = 'Amount received for Account opening ' . $planDetail->name . '(' . $investmentAccountNoRd . ') through cheque(' . $cheque_no . ')';
      $rdDesMem = 'Account opening ' . $planDetail->name . '(' . $investmentAccountNoRd . ') through cheque(' . $cheque_no . ')';
      //bank head entry
      // $allTranRDcheque=CommanController::createAllHeadTransaction($refIdRD,$branch_id,$bank_id=NULL,$bank_ac_id=NULL,$head41,$type,$sub_type,$type_id,$createDayBook,$associate_id,$memberId,$branch_id_to=NULL,$branch_id_from=NULL,$amount,$amount,$amount,$rdDes,'DR',$headPaymentModeRD,$currency_code,$amount_to_id=NULL,$amount_to_name=NULL,$amount_from_id=NULL,$amount_from_name=NULL,$jv_unique_id=NULL,$v_no,$v_date,$ssb_account_id_from,$ssb_account_id_to=NULL,$ssb_account_tran_id_to=NULL,$ssb_account_tran_id_from=NULL,0,$cheque_id,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_from_id=NULL,$cheque_bank_ac_from_id=NULL,$cheque_bank_to,$cheque_bank_ac_to,getSamraddhBank($cheque_bank_to)->bank_name,$cheque_bank_to_branch=NULL,getSamraddhBankAccountId($cheque_bank_ac_to)->account_no,getSamraddhBankAccountId($cheque_bank_ac_to)->ifsc_code,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_from_id=NULL,$transction_bank_from_ac_id=NULL,$transction_bank_to,$transction_bank_ac_to,$transction_bank_to_name=NULL,$transction_bank_to_ac_no=NULL,$transction_bank_to_branch=NULL,$transction_bank_to_ifsc=NULL,$transction_date,$created_by,$created_by_id);
      $allTranRDcheque = CommanTransactionFacade::headTransactionCreate($refIdRD, $branch_id, $bank_id = NULL, $bank_ac_id = NULL, $head41, $type, $sub_type, $type_id, $associate_id, $memberId, $branch_id_to = NULL, $branch_id_from = NULL, $amount, $rdDes, 'DR', $headPaymentModeRD, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $createDayBook, NULL, $ssb_account_id_to = NULL, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, 0, $cheque_id, $companyId);
      //bank entry
      $bankCheque = CommanController::samraddhBankDaybookNew(
        $refIdRD,
        $cheque_bank_to,
        $cheque_bank_ac_to,
        $type,
        $sub_type,
        $type_id,
        $createDayBook,
        $associate_id,
        $memberId,
        $branch_id,
        $opening_balance = NULL,
        $amount,
        $closing_balance = NULL,
        $rdDes,
        $rdDesDR,
        $rdDesCR,
        'CR',
        $headPaymentModeRD,
        $currency_code,
        $amount_to_id = NULL,
        $amount_to_name = NULL,
        $amount_from_id = NULL,
        $amount_from_name = NULL,
        $v_no,
        $v_date,
        $ssb_account_id_from,
        $cheque_no,
        $cheque_date,
        $cheque_bank_from,
        $cheque_bank_ac_from,
        $cheque_bank_ifsc_from,
        $cheque_bank_branch_from,
        $cheque_bank_to,
        $cheque_bank_ac_to,
        $transction_no,
        $transction_bank_from,
        $transction_bank_ac_from,
        $transction_bank_ifsc_from,
        $transction_bank_branch_from,
        $transction_bank_to,
        $transction_bank_ac_to,
        getSamraddhBank($cheque_bank_ac_to)->bank_name,
        getSamraddhBankAccountId($cheque_bank_ac_to)->account_no,
        $transction_bank_to_branch = NULL,
        getSamraddhBankAccountId($cheque_bank_ac_to)->ifsc_code,
        $transction_date,
        $entry_date,
        $entry_time,
        $created_by,
        $created_by_id,
        $created_at,
        $jv_unique_id = NULL,
        0,
        $cheque_id,
        $ssb_account_tran_id_to = NULL,
        $ssb_account_tran_id_from = NULL,
        $companyId
      );
      //bank balence
      $bankClosing = CommanController::checkCreateBankClosing($cheque_bank_to, $cheque_bank_ac_to, $created_at, $amount, 0);
    } elseif ($payment_mode == 2) { //online transaction
      $headPaymentModeRD = 2;
      $transction_no = $online_transction_no;
      $transction_bank_from = NULL;
      $transction_bank_ac_from = NULL;
      $transction_bank_ifsc_from = NULL;
      $transction_bank_branch_from = NULL;
      $transction_bank_to = $online_deposit_bank_id;
      $transction_bank_ac_to = $online_deposit_bank_ac_id;
      $transction_date = $online_transction_date;
      $getBHead = \App\Models\SamraddhBank::where('id', $transction_bank_to)->first();
      $head411 = $getBHead->account_head_id;
      $rdDesDR = 'Bank A/c Dr ' . $amount . '/-';
      $rdDesCR = 'To ' . $planDetail->name . '(' . $investmentAccountNoRd . ')  A/c Cr ' . $amount . '/-';
      $rdDes = 'Amount received for Account opening ' . $planDetail->name . '(' . $investmentAccountNoRd . ') through online transaction(' . $transction_no . ')';
      $rdDesMem = 'Account opening ' . $planDetail->name . '(' . $investmentAccountNoRd . ') through online transaction(' . $transction_no . ')';
      //bank head entry
      $allTranRDcheque = CommanTransactionFacade::headTransactionCreate($refIdRD, $branch_id, $bank_id = NULL, $bank_ac_id = NULL, $head411, $type, $sub_type, $type_id, $associate_id, $memberId, $branch_id_to = NULL, $branch_id_from = NULL, $amount, $rdDes, 'DR', $headPaymentModeRD, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $createDayBook, NULL, $ssb_account_id_to = NULL, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, 0, $cheque_id, $companyId);
      // $allTranRDonline=CommanController::createAllHeadTransaction($refIdRD,$branch_id,$bank_id=NULL,$bank_ac_id=NULL,$head411,$type,$sub_type,$type_id,$createDayBook,$associate_id,$memberId,$branch_id_to=NULL,$branch_id_from=NULL,$amount,$amount,$amount,$rdDes,'DR',$headPaymentModeRD,$currency_code,$amount_to_id=NULL,$amount_to_name=NULL,$amount_from_id=NULL,$amount_from_name=NULL,$jv_unique_id=NULL,$v_no,$v_date,$ssb_account_id_from,$ssb_account_id_to=NULL,$ssb_account_tran_id_to=NULL,$ssb_account_tran_id_from=NULL,$cheque_type=NULL,$cheque_id=NULL,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_from_id=NULL,$cheque_bank_ac_from_id=NULL,$cheque_bank_to,$cheque_bank_ac_to,$cheque_bank_to_name=NULL,$cheque_bank_to_branch=NULL,$cheque_bank_to_ac_no=NULL,$cheque_bank_to_ifsc=NULL,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_from_id=NULL,$transction_bank_from_ac_id=NULL,$transction_bank_to,$transction_bank_ac_to,getSamraddhBank($online_deposit_bank_id)->bank_name,getSamraddhBankAccountId($online_deposit_bank_ac_id)->account_no,$transction_bank_to_branch=NULL,getSamraddhBankAccountId($online_deposit_bank_ac_id)->ifsc_code,$transction_date,$created_by,$created_by_id);
      /*$allTranRDonline=CommanController::createAllTransactionNew($refIdRD,$branch_id,$bank_id=NULL,$bank_ac_id=NULL,$head111,$head211,$head311,$head411,$head511,$type,$sub_type,$type_id,$associate_id,$memberId,$branch_id_to=NULL,$branch_id_from=NULL,$opening_balance=NULL,$amount,$closing_balance=NULL,$rdDes,'CR',$headPaymentModeRD,$currency_code,$amount_to_id=NULL,$amount_to_name=NULL,$amount_from_id=NULL,$amount_from_name=NULL,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at,$updated_at,$createDayBook);*/
      //bank entry
      $bankonline = CommanController::samraddhBankDaybookNew($refIdRD, $cheque_bank_to, $cheque_bank_ac_to, $type, $sub_type, $type_id, $createDayBook, $associate_id, $memberId, $branch_id, $opening_balance = NULL, $amount, $closing_balance = NULL, $rdDes, $rdDesDR, $rdDesCR, 'CR', $headPaymentModeRD, $currency_code, $amount_to_id = NULL, $amount_to_name = NULL, $amount_from_id = NULL, $amount_from_name = NULL, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, getSamraddhBank($online_deposit_bank_id)->bank_name, getSamraddhBankAccountId($online_deposit_bank_ac_id)->account_no, $transction_bank_to_branch = NULL, getSamraddhBankAccountId($online_deposit_bank_ac_id)->ifsc_code, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $jv_unique_id = NULL, NULL, $cheque_id = NULL, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL);
      /*$bankonline=CommanController::createSamraddhBankDaybookNew($refIdRD,$transction_bank_to,$transction_bank_ac_to,$type,$sub_type,$type_id,$associate_id,$memberId,$branch_id,$opening_balance=NULL,$amount,$closing_balance=NULL,$rdDes,$rdDesDR,$rdDesCR,'CR',$headPaymentModeRD,$currency_code,$amount_to_id=NULL,$amount_to_name=NULL,$amount_from_id=NULL,$amount_from_name=NULL,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at,$updated_at,$createDayBook);*/
      //bank balence
      $bankClosing = CommanController::checkCreateBankClosing($transction_bank_to, $transction_bank_ac_to, $created_at, $amount, 0);
    } elseif ($payment_mode == 3) { // ssb
      $headPaymentModeRD = 3;
      $v_no = mt_rand(0, 999999999999999);
      $v_date = $entry_date;
      $ssb_account_id_from = $ssbId;
      $SSBDescTran = 'Amount transferred to ' . $planDetail->name . '(' . $investmentAccountNoRd . ') ';
      $head1rdSSB = 1;
      $head2rdSSB = 8;
      $head3rdSSB = 20;
      $head4rdSSB = 56;
      $head5rdSSB = NULL;
      $ssbDetals = SavingAccount::where('id', $ssb_account_id_from)->first();
      $rdDesDR = $planDetail->name . '(' . $investmentAccountNoRd . ') A/c Dr ' . $amount . '/-';
      $rdDesCR = 'To SSB(' . $ssbDetals->account_no . ') A/c Cr ' . $amount . '/-';
      $rdDes = 'Amount received for Account opening ' . $planDetail->name . '(' . $investmentAccountNoRd . ') through SSB(' . $ssbDetals->account_no . ')';
      $rdDesMem = 'Account opening ' . $planDetail->name . '(' . $investmentAccountNoRd . ') online through SSB(' . $ssbDetals->account_no . ')';
      // ssb  head entry -
      $allTranRDcheque = CommanTransactionFacade::headTransactionCreate($refIdRD, $branch_id, $bank_id = NULL, $bank_ac_id = NULL, $head4rdSSB, $type, $sub_type, $type_id, $associate_id, $memberId, $branch_id_to = NULL, $branch_id_from = NULL, $amount, $rdDes, 'DR', $headPaymentModeRD, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $createDayBook, NULL, $ssb_account_id_to = NULL, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, 0, $cheque_id, $companyId);
      // $allTranRDSSB=CommanController::createAllHeadTransaction($refIdRD,$branch_id,$bank_id=NULL,$bank_ac_id=NULL,$head4rdSSB,$type,$sub_type,$type_id,$createDayBook,$associate_id,$memberId,$branch_id_to=NULL,$branch_id_from=NULL,$amount,$amount,$amount,$rdDes,'DR',$headPaymentModeRD,$currency_code,$amount_to_id=NULL,$amount_to_name=NULL,$amount_from_id=NULL,$amount_from_name=NULL,$jv_unique_id=NULL,$v_no,$v_date,$ssb_account_id_from,$ssb_account_id_to=NULL,$ssb_account_tran_id_to=NULL,$ssb_account_tran_id_from=NULL,$cheque_type=NULL,$cheque_id=NULL,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_from_id=NULL,$cheque_bank_ac_from_id=NULL,$cheque_bank_to,$cheque_bank_ac_to,$cheque_bank_to_name=NULL,$cheque_bank_to_branch=NULL,$cheque_bank_to_ac_no=NULL,$cheque_bank_to_ifsc=NULL,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_from_id=NULL,$transction_bank_from_ac_id=NULL,$transction_bank_to,$transction_bank_ac_to,$transction_bank_to_name=NULL,$transction_bank_to_ac_no=NULL,$transction_bank_to_branch=NULL,$transction_bank_to_ifsc=NULL,$transction_date,$created_by,$created_by_id);
      /*$allTranRDSSB=CommanController::createAllTransactionNew($refIdRD,$branch_id,$bank_id=NULL,$bank_ac_id=NULL,$head1rdSSB,$head2rdSSB,$head3rdSSB,$head4rdSSB,$head5rdSSB,$type,$sub_type,$type_id,$associate_id,$memberId,$branch_id_to=NULL,$branch_id_from=NULL,$opening_balance=NULL,$amount,$closing_balance=NULL,$rdDes,'DR',$headPaymentModeRD,$currency_code,$amount_to_id=NULL,$amount_to_name=NULL,$amount_from_id=NULL,$amount_from_name=NULL,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at,$updated_at,$createDayBook);*/
      // $branchClosingSSB=CommanController:: checkCreateBranchClosingDr($branch_id,$created_at,$amount,0);
      // Member transaction  +
      // $memberTranInvest77 = CommanController::memberTransactionNew($refIdRD,'4','47',$createDayBook,$ssb_account_id_from,$associate_id,$ssbDetals->member_id,$branch_id,$bank_id=NULL,$account_id=NULL,$amount,$SSBDescTran,'DR',$headPaymentModeRD,$currency_code,$amount_to_id=$type_id,$amount_to_name=$planDetail->name,$amount_from_id=NULL,$amount_from_name=NULL,$v_no,$v_date,$ssb_account_id_from=NULL,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at,$ssb_account_tran_id_to=NULL,$ssb_account_tran_id_from=NULL,$jv_unique_id=NULL,$cheque_type=NULL,$cheque_id=NULL);
      /*$memberTranInvest77 = CommanController::createMemberTransactionNew($refIdRD,'4','47',$ssb_account_id_from,$associate_id,$ssbDetals->member_id,$branch_id,$bank_id=NULL,$account_id=NULL,$amount,$SSBDescTran,'DR',$headPaymentModeRD,$currency_code,$amount_to_id=$type_id,$amount_to_name=$planDetail->name,$amount_from_id=NULL,$amount_from_name=NULL,$v_no,$v_date,$ssb_account_id_from=NULL,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at,$updated_at,$createDayBook);*/
    } else {
      $headPaymentModeRD = 0;
      $head3rdC = 28;
      $rdDesDR = 'Cash A/c Dr ' . $amount . '/-';
      $rdDesCR = 'To ' . $planDetail->name . '(' . $investmentAccountNoRd . ')  A/c Cr ' . $amount . '/-';
      $rdDes = 'Amount received for Account opening ' . $planDetail->name . '(' . $investmentAccountNoRd . ') through cash(' . getBranchCode($branch_id)->branch_code . ')';
      $rdDesMem = 'Account opening ' . $planDetail->name . '(' . $investmentAccountNoRd . ') through cash(' . getBranchCode($branch_id)->branch_code . ')';
      // branch cash  head entry +
      $allTranRDcheque = CommanTransactionFacade::headTransactionCreate($refIdRD, $branch_id, $bank_id = NULL, $bank_ac_id = NULL, $head3rdC, $type, $sub_type, $type_id, $associate_id, $memberId, $branch_id_to = NULL, $branch_id_from = NULL, $amount, $rdDes, 'DR', $headPaymentModeRD, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $createDayBook, NULL, $ssb_account_id_to = NULL, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, 0, $cheque_id, $companyId);
      // $allTranRDcash=CommanController::createAllHeadTransaction($refIdRD,$branch_id,$bank_id=NULL,$bank_ac_id=NULL,$head3rdC,$type,$sub_type,$type_id,$createDayBook,$associate_id,$memberId,$branch_id_to=NULL,$branch_id_from=NULL,$amount,$amount,$amount,$rdDes,'DR',$headPaymentModeRD,$currency_code,$amount_to_id=NULL,$amount_to_name=NULL,$amount_from_id=NULL,$amount_from_name=NULL,$jv_unique_id=NULL,$v_no,$v_date,$ssb_account_id_from,$ssb_account_id_to=NULL,$ssb_account_tran_id_to=NULL,$ssb_account_tran_id_from=NULL,$cheque_type=NULL,$cheque_id=NULL,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_from_id=NULL,$cheque_bank_ac_from_id=NULL,$cheque_bank_to,$cheque_bank_ac_to,$cheque_bank_to_name=NULL,$cheque_bank_to_branch=NULL,$cheque_bank_to_ac_no=NULL,$cheque_bank_to_ifsc=NULL,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_from_id=NULL,$transction_bank_from_ac_id=NULL,$transction_bank_to,$transction_bank_ac_to,$transction_bank_to_name=NULL,$transction_bank_to_ac_no=NULL,$transction_bank_to_branch=NULL,$transction_bank_to_ifsc=NULL,$transction_date,$created_by,$created_by_id);
      /*$allTranRDcash=CommanController::createAllTransactionNew($refIdRD,$branch_id,$bank_id=NULL,$bank_ac_id=NULL,$head1rdC,$head2rdC,$head3rdC,$head4rdC,$head5rdC,$type,$sub_type,$type_id,$associate_id,$memberId,$branch_id_to=NULL,$branch_id_from=NULL,$opening_balance=NULL,$amount,$closing_balance=NULL,$rdDes,$payment_type_rd,$headPaymentModeRD,$currency_code,$amount_to_id=NULL,$amount_to_name=NULL,$amount_from_id=NULL,$amount_from_name=NULL,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at,$updated_at,$createDayBook);*/
      //Balance   entry +
      // $branchCash=CommanController:: checkCreateBranchCash($branch_id,$created_at,$amount,0);
    }
    //branch day book entry +
    // $daybookInvest = CommanController::branchDayBookNew($refIdRD,$branch_id,$type,$sub_type,$type_id,$createDayBook,$associate_id,$memberId,$branch_id_to=NULL,$branch_id_from=NULL,$opening_balance=NULL,$amount,$closing_balance=NULL,$rdDes,$rdDesDR,$rdDesCR,$payment_type_rd,$headPaymentModeRD,$currency_code,$amount_to_id=NULL,$amount_to_name=NULL,$amount_from_id=NULL,$amount_from_name=NULL,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$is_contra=NULL,$contra_id=NULL,$created_at,$ssb_account_tran_id_to=NULL,$ssb_account_tran_id_from=NULL,$jv_unique_id=NULL,$cheque_type=NULL,$cheque_id=NULL,$companyId);
    $allTranRDcheque = CommanTransactionFacade::createBranchDayBookNew($refIdRD, $branch_id, $type, $sub_type, $type_id, $associate_id, $memberId, $branch_id_to = NULL, $branch_id_from = NULL, $amount, $rdDes, $rdDesDR, $rdDesCR, 'DR', $headPaymentModeRD, $currency_code, $v_no, $ssb_account_id_from, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $createDayBook, $companyId);
    /*$daybookInvest = CommanController::createBranchDayBookNew($refIdRD,$branch_id,$type,$sub_type,$type_id,$associate_id,$memberId,$branch_id_to=NULL,$branch_id_from=NULL,$opening_balance=NULL,$amount,$closing_balance=NULL,$rdDes,$rdDesDR,$rdDesCR,$payment_type_rd,$headPaymentModeRD,$currency_code,$amount_to_id=NULL,$amount_to_name=NULL,$amount_from_id=NULL,$amount_from_name=NULL,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$is_contra=NULL,$contra_id=NULL,$created_at,$updated_at,$createDayBook);*/
    // Investment head entry +
    // $allTranInvest=CommanController::createAllHeadTransaction($refIdRD,$branch_id,$bank_id=NULL,$bank_ac_id=NULL,$head_id,$type,$sub_type,$type_id,$createDayBook,$associate_id,$memberId,$branch_id_to=NULL,$branch_id_from=NULL,$amount,$amount,$amount,$rdDes,$payment_type_rd,$headPaymentModeRD,$currency_code,$amount_to_id=NULL,$amount_to_name=NULL,$amount_from_id=NULL,$amount_from_name=NULL,$jv_unique_id=NULL,$v_no,$v_date,$ssb_account_id_from,$ssb_account_id_to=NULL,$ssb_account_tran_id_to=NULL,$ssb_account_tran_id_from=NULL,$cheque_type=NULL,$cheque_id=NULL,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_from_id=NULL,$cheque_bank_ac_from_id=NULL,$cheque_bank_to,$cheque_bank_ac_to,$cheque_bank_to_name=NULL,$cheque_bank_to_branch=NULL,$cheque_bank_to_ac_no=NULL,$cheque_bank_to_ifsc=NULL,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_from_id=NULL,$transction_bank_from_ac_id=NULL,$transction_bank_to,$transction_bank_ac_to,$transction_bank_to_name=NULL,$transction_bank_to_ac_no=NULL,$transction_bank_to_branch=NULL,$transction_bank_to_ifsc=NULL,$transction_date,$created_by,$created_by_id);
    $allTranRDcheque = CommanTransactionFacade::headTransactionCreate($refIdRD, $branch_id, $bank_id = NULL, $bank_ac_id = NULL, $head_id, $type, $sub_type, $type_id, $associate_id, $memberId, $branch_id_to = NULL, $branch_id_from = NULL, $amount, $rdDes, $payment_type_rd, $headPaymentModeRD, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $createDayBook, NULL, $ssb_account_id_to = NULL, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, 0, $cheque_id, $companyId);
    /*$allTranInvest = CommanController::createAllTransactionNew($refIdRD,$branch_id,$bank_id=NULL,$bank_ac_id=NULL,$head1Invest,$head2Invest,$head3Invest,$head4Invest,$head5Invest,$type,$sub_type,$type_id,$associate_id,$memberId,$branch_id_to=NULL,$branch_id_from=NULL,$opening_balance=NULL,$amount,$closing_balance=NULL,$rdDes,$payment_type_rd,$headPaymentModeRD,$currency_code,$amount_to_id=NULL,$amount_to_name=NULL,$amount_from_id=NULL,$amount_from_name=NULL,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at,$updated_at,$createDayBook);*/
    // Member transaction  +
    // $memberTranInvest = CommanController::memberTransactionNew($refIdRD,$type,$sub_type,$type_id,$createDayBook,$associate_id,$memberId,$branch_id,$bank_id=NULL,$account_id=NULL,$amount,$rdDesMem,$payment_type_rd,$headPaymentModeRD,$currency_code,$amount_to_id=NULL,$amount_to_name=NULL,$amount_from_id=NULL,$amount_from_name=NULL,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at,$ssb_account_tran_id_to=NULL,$ssb_account_tran_id_from=NULL,$jv_unique_id=NULL,$cheque_type=NULL,$cheque_id=NULL);
    /*$memberTranInvest = CommanController::createMemberTransactionNew($refIdRD,$type,$sub_type,$type_id,$associate_id,$memberId,$branch_id,$bank_id=NULL,$account_id=NULL,$amount,$rdDesMem,$payment_type_rd,$headPaymentModeRD,$currency_code,$amount_to_id=NULL,$amount_to_name=NULL,$amount_from_id=NULL,$amount_from_name=NULL,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at,$updated_at,$createDayBook);*/
    /******** Balance   entry ***************/
    // $branchClosing=CommanController:: checkCreateBranchClosing($branch_id,$created_at,$amount,0);
  }
  public function investHeadCreateSSB($amount, $globaldate, $investmentId, $planId, $cheque_no, $cheque_id, $cheque_date, $online_transction_no, $online_transction_date, $online_deposit_bank_id, $online_deposit_bank_ac_id, $branch_id, $associate_id, $memberId, $ssbId, $createDayBook, $payment_mode, $investmentAccountNoRd, $companyId)
  {
    $amount = $amount;
    $daybookRefRD = CommanController::createBranchDayBookReferenceNew($amount, $globaldate);
    $refIdRD = $daybookRefRD;
    $currency_code = 'INR';
    $headPaymentModeRD = 0;
    $payment_type_rd = 'CR';
    $type_id = $investmentId;
    $entry_date = date("Y-m-d", strtotime(convertDate($globaldate)));
    $entry_time = date("H:i:s", strtotime(convertDate($globaldate)));
    $created_by = 1;
    $created_by_id = Auth::user()->id;
    $created_at = date("Y-m-d H:i:s", strtotime(convertDate($globaldate)));
    $updated_at = date("Y-m-d H:i:s", strtotime(convertDate($globaldate)));
    $planDetail = getPlanDetail($planId);
    $type = 4;
    $sub_type = 41;
    $planCode = $planDetail->plan_code;
    $head1Invest = 1;
    $head2Invest = 8;
    $head3Invest = 20;
    $head4Invest = 56;
    $head5Invest = NULL;
    $rdDesDR = 'Cash A/c Dr ' . $amount . '/-';
    $rdDesCR = 'To ' . $planDetail->name . '(' . $investmentAccountNoRd . ')  A/c Cr ' . $amount . '/-';
    $rdDes = 'Amount received for Account opening ' . $planDetail->name . '(' . $investmentAccountNoRd . ') through cash(' . getBranchCode($branch_id)->branch_code . ')';
    $rdDesMem = 'Account opening ' . $planDetail->name . '(' . $investmentAccountNoRd . ') through cash(' . getBranchCode($branch_id)->branch_code . ')';
    $v_no = NULL;
    $v_date = NULL;
    $ssb_account_id_from = NULL;
    $cheque_type = NULL;
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
    $headPaymentModeRD = 0;
    $head1rdC = 2;
    $head2rdC = 10;
    $head3rdC = 28;
    $head4rdC = 71;
    $head5rdC = NULL;
    // branch cash  head entry +
    // $allTranRDcash=CommanController::createAllHeadTransaction($refIdRD,$branch_id,$bank_id=NULL,$bank_ac_id=NULL,$head3rdC,$type,$sub_type,$type_id,$createDayBook,$associate_id,$memberId,$branch_id_to=NULL,$branch_id_from=NULL,$amount,$amount,$amount,$rdDes,'DR',$headPaymentModeRD,$currency_code,$amount_to_id=NULL,$amount_to_name=NULL,$amount_from_id=NULL,$amount_from_name=NULL,$jv_unique_id=NULL,$v_no,$v_date,$ssb_account_id_from,$ssb_account_id_to=NULL,$ssb_account_tran_id_to=NULL,$ssb_account_tran_id_from=NULL,$cheque_type=NULL,$cheque_id=NULL,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_from_id=NULL,$cheque_bank_ac_from_id=NULL,$cheque_bank_to=NULL,$cheque_bank_ac_to=NULL,NULL,$cheque_bank_to_branch=NULL,NULL,NULL,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_from_id=NULL,$transction_bank_from_ac_id=NULL,$transction_bank_to,$transction_bank_ac_to,$transction_bank_to_name=NULL,$transction_bank_to_ac_no=NULL,$transction_bank_to_branch=NULL,$transction_bank_to_ifsc=NULL,$transction_date,$created_by,$created_by_id);
    $allTranRDcheque = CommanTransactionFacade::headTransactionCreate($refIdRD, $branch_id, $bank_id = NULL, $bank_ac_id = NULL, $head3rdC, $type, $sub_type, $type_id, $associate_id, $memberId, $branch_id_to = NULL, $branch_id_from = NULL, $amount, $rdDes, 'DR', $headPaymentModeRD, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $createDayBook, NULL, $ssb_account_id_to = NULL, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, 0, $cheque_id, $companyId);
    /*$allTranRDcash=CommanController::createAllTransactionNew($refIdRD,$branch_id,$bank_id=NULL,$bank_ac_id=NULL,$head1rdC,$head2rdC,$head3rdC,$head4rdC,$head5rdC,$type,$sub_type,$type_id,$associate_id,$memberId,$branch_id_to=NULL,$branch_id_from=NULL,$opening_balance=NULL,$amount,$closing_balance=NULL,$rdDes,$payment_type_rd,$headPaymentModeRD,$currency_code,$amount_to_id=NULL,$amount_to_name=NULL,$amount_from_id=NULL,$amount_from_name=NULL,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at,$updated_at,$createDayBook);*/
    //Balance   entry +
    // $branchCash=CommanController:: checkCreateBranchCash($branch_id,$created_at,$amount,0);
    //branch day book entry +
    // $daybookInvest = CommanController::branchDayBookNew($refIdRD,$branch_id,$type,$sub_type,$type_id,$createDayBook,$associate_id,$memberId,$branch_id_to=NULL,$branch_id_from=NULL,$opening_balance=NULL,$amount,$closing_balance=NULL,$rdDes,$rdDesDR,$rdDesCR,$payment_type_rd,$headPaymentModeRD,$currency_code,$amount_to_id=NULL,$amount_to_name=NULL,$amount_from_id=NULL,$amount_from_name=NULL,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$is_contra=NULL,$contra_id=NULL,$created_at,$ssb_account_tran_id_to=NULL,$ssb_account_tran_id_from=NULL,$jv_unique_id=NULL,$cheque_type=NULL,$cheque_id=NULL);
    $allTranRDcheque = CommanTransactionFacade::createBranchDayBookNew($refIdRD, $branch_id, $type, $sub_type, $type_id, $associate_id, $memberId, $branch_id_to = NULL, $branch_id_from = NULL, $amount, $rdDes, $rdDesDR, $rdDesCR, 'DR', $headPaymentModeRD, $currency_code, $v_no, $ssb_account_id_from, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $createDayBook, $companyId);
    /*$daybookInvest = CommanController::createBranchDayBookNew($refIdRD,$branch_id,$type,$sub_type,$type_id,$associate_id,$memberId,$branch_id_to=NULL,$branch_id_from=NULL,$opening_balance=NULL,$amount,$closing_balance=NULL,$rdDes,$rdDesDR,$rdDesCR,$payment_type_rd,$headPaymentModeRD,$currency_code,$amount_to_id=NULL,$amount_to_name=NULL,$amount_from_id=NULL,$amount_from_name=NULL,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$is_contra=NULL,$contra_id=NULL,$created_at,$updated_at,$createDayBook);*/
    // Investment head entry +
    // $allTranInvest=CommanController::createAllHeadTransaction($refIdRD,$branch_id,$bank_id=NULL,$bank_ac_id=NULL,$head4Invest,$type,$sub_type,$type_id,$createDayBook,$associate_id,$memberId,$branch_id_to=NULL,$branch_id_from=NULL,$amount,$amount,$amount,$rdDes,$payment_type_rd,$headPaymentModeRD,$currency_code,$amount_to_id=NULL,$amount_to_name=NULL,$amount_from_id=NULL,$amount_from_name=NULL,$jv_unique_id=NULL,$v_no,$v_date,$ssb_account_id_from,$ssb_account_id_to=NULL,$ssb_account_tran_id_to=NULL,$ssb_account_tran_id_from=NULL,$cheque_type=NULL,$cheque_id=NULL,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_from_id=NULL,$cheque_bank_ac_from_id=NULL,$cheque_bank_to=NULL,$cheque_bank_ac_to=NULL,NULL,$cheque_bank_to_branch=NULL,NULL,NULL,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_from_id=NULL,$transction_bank_from_ac_id=NULL,$transction_bank_to,$transction_bank_ac_to,$transction_bank_to_name=NULL,$transction_bank_to_ac_no=NULL,$transction_bank_to_branch=NULL,$transction_bank_to_ifsc=NULL,$transction_date,$created_by,$created_by_id);
    $allTranRDcheque = CommanTransactionFacade::headTransactionCreate($refIdRD, $branch_id, $bank_id = NULL, $bank_ac_id = NULL, $head4Invest, $type, $sub_type, $type_id, $associate_id, $memberId, $branch_id_to = NULL, $branch_id_from = NULL, $amount, $rdDes, 'DR', $headPaymentModeRD, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $createDayBook, NULL, $ssb_account_id_to = NULL, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, 0, $cheque_id, $companyId);
    /*$allTranInvest = CommanController::createAllTransactionNew($refIdRD,$branch_id,$bank_id=NULL,$bank_ac_id=NULL,$head1Invest,$head2Invest,$head3Invest,$head4Invest,$head5Invest,$type,$sub_type,$type_id,$associate_id,$memberId,$branch_id_to=NULL,$branch_id_from=NULL,$opening_balance=NULL,$amount,$closing_balance=NULL,$rdDes,$payment_type_rd,$headPaymentModeRD,$currency_code,$amount_to_id=NULL,$amount_to_name=NULL,$amount_from_id=NULL,$amount_from_name=NULL,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at,$updated_at,$createDayBook);*/
    // Member transaction  +
    // $memberTranInvest = CommanController::memberTransactionNew($refIdRD,$type,$sub_type,$type_id,$createDayBook,$associate_id,$memberId,$branch_id,$bank_id=NULL,$account_id=NULL,$amount,$rdDesMem,$payment_type_rd,$headPaymentModeRD,$currency_code,$amount_to_id=NULL,$amount_to_name=NULL,$amount_from_id=NULL,$amount_from_name=NULL,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at,$ssb_account_tran_id_to=NULL,$ssb_account_tran_id_from=NULL,$jv_unique_id=NULL,$cheque_type=NULL,$cheque_id=NULL);
    /*$memberTranInvest = CommanController::createMemberTransactionNew($refIdRD,$type,$sub_type,$type_id,$associate_id,$memberId,$branch_id,$bank_id=NULL,$account_id=NULL,$amount,$rdDesMem,$payment_type_rd,$headPaymentModeRD,$currency_code,$amount_to_id=NULL,$amount_to_name=NULL,$amount_from_id=NULL,$amount_from_name=NULL,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at,$updated_at,$createDayBook);*/
    /******** Balance   entry ***************/
    // $branchClosing=CommanController:: checkCreateBranchClosing($branch_id,$created_at,$amount,0);
  }
  /**
   * Store a newly created plan in storage.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\Response
   */
  public function Store(Request $request)
  {
    $plantype = $request->input('plan_type');
    switch ($plantype) {
      case "saving-account":
        $rules = [
          'amount' => 'required',
          'investmentplan' => 'required',
          'form_number' => 'required',
          'memberid' => 'required',
          'fn_first_name' => 'required',
          'fn_relationship' => 'required',
          'fn_gender' => 'required',
          'fn_dob' => 'required',
          'fn_percentage' => 'required',
        ];
        break;
      case "saving-account-child":
        $rules = [
          'amount' => 'required',
          'investmentplan' => 'required',
          'form_number' => 'required',
          'memberid' => 'required',
          'fn_first_name' => 'required',
          'fn_relationship' => 'required',
          'fn_gender' => 'required',
          'fn_dob' => 'required',
          'fn_percentage' => 'required',
          're_member_dob' => 'required',
          'ex_re_age' => 'required',
          'ex_re_name' => 'required',
          'ex_re_gender' => 'required',
          'ex_re_guardians' => 'required',
        ];
        $request->request->add(['payment-mode' => 0]);
        $request->request->add(['transaction-id' => 0]);
        $request->request->add(['date' => date("Y-m-d")]);
        break;
      case "samraddh-kanyadhan-yojana":
        $rules = [
          'form_number' => 'required',
          'amount' => 'required',
          'payment-mode' => 'required',
        ];
        break;
      case "special-samraddh-money-back":
        $rules = [
          'form_number' => 'required',
          'amount' => 'required',
          'ssbacount' => 'required',
          'payment-mode' => 'required',
          'investmentplan' => 'required',
          'memberid' => 'required',
          'fn_first_name' => 'required',
          'fn_relationship' => 'required',
          'fn_gender' => 'required',
          'fn_dob' => 'required',
          'fn_percentage' => 'required',
        ];
        break;
      case "flexi-fixed-deposit":
        $rules = [
          'form_number' => 'required',
          'amount' => 'required',
          'payment-mode' => 'required',
          'investmentplan' => 'required',
          'memberid' => 'required',
          'fn_first_name' => 'required',
          'fn_relationship' => 'required',
          'fn_gender' => 'required',
          'fn_dob' => 'required',
          'fn_age' => 'required',
          'fn_percentage' => 'required',
        ];
        break;
      case "fixed-recurring-deposit":
        $rules = [
          'form_number' => 'required',
          'amount' => 'required',
          'payment-mode' => 'required',
          'investmentplan' => 'required',
          'memberid' => 'required',
          'fn_first_name' => 'required',
          'fn_relationship' => 'required',
          'fn_gender' => 'required',
          'fn_dob' => 'required',
          'fn_percentage' => 'required',
        ];
        break;
      case "samraddh-jeevan":
        $rules = [
          'form_number' => 'required',
          'amount' => 'required',
          'ssbacount' => 'required',
          'payment-mode' => 'required',
          'investmentplan' => 'required',
          'memberid' => 'required',
          'fn_first_name' => 'required',
          'fn_relationship' => 'required',
          'fn_gender' => 'required',
          'fn_dob' => 'required',
          'fn_percentage' => 'required',
        ];
        break;
      case "daily-deposit":
        $rules = [
          'form_number' => 'required',
          'amount' => 'required',
          'payment-mode' => 'required',
          'investmentplan' => 'required',
          'memberid' => 'required',
          'fn_first_name' => 'required',
          'fn_relationship' => 'required',
          'fn_gender' => 'required',
          'fn_dob' => 'required',
          'fn_percentage' => 'required',
        ];
        break;
      case "monthly-income-scheme":
        $rules = [
          'form_number' => 'required',
          'amount' => 'required',
          'ssbacount' => 'required',
          'payment-mode' => 'required',
          'investmentplan' => 'required',
          'memberid' => 'required',
          'fn_first_name' => 'required',
          'fn_relationship' => 'required',
          'fn_gender' => 'required',
          'fn_dob' => 'required',
          'fn_percentage' => 'required',
        ];
        break;
      case "fixed-deposit":
        $rules = [
          'form_number' => 'required',
          'amount' => 'required',
          'payment-mode' => 'required',
          'tenure' => 'required',
          'investmentplan' => 'required',
          'memberid' => 'required',
          'fn_first_name' => 'required',
          'fn_relationship' => 'required',
          'fn_gender' => 'required',
          'fn_dob' => 'required',
          'fn_percentage' => 'required',
        ];
        break;
      case "recurring-deposit":
        $rules = [
          'form_number' => 'required',
          'amount' => 'required',
          'payment-mode' => 'required',
          'tenure' => 'required',
          'investmentplan' => 'required',
          'memberid' => 'required',
          'fn_first_name' => 'required',
          'fn_relationship' => 'required',
          'fn_gender' => 'required',
          'fn_dob' => 'required',
          'fn_percentage' => 'required',
        ];
        break;
      case "samraddh-bhavhishya":
        $rules = [
          'form_number' => 'required',
          'amount' => 'required',
          'payment-mode' => 'required',
          'investmentplan' => 'required',
          'memberid' => 'required',
          'fn_first_name' => 'required',
          'fn_relationship' => 'required',
          'fn_gender' => 'required',
          'fn_dob' => 'required',
          'fn_percentage' => 'required',
        ];
        break;
    }
    $customMessages = [
      'fn_first_name.required' => 'The first name field is required.',
      'required' => 'The :attribute field is required.'
    ];
    $this->validate($request, $rules, $customMessages);
    DB::beginTransaction();
    try {
      $companyId = $request->company_id;
      if ($plantype != 'saving-account') {
        if ($request->input('payment-mode') == 1) {
          $getChequeDetail = \App\Models\ReceivedCheque::where('id', $request['cheque_id'])->where('status', 3)->first(['id', 'amount', 'status']);
          if (!empty($getChequeDetail)) {
            return back()->with('alert', 'Cheque already used select another cheque');
          } else {
            $getamount = \App\Models\ReceivedCheque::where('id', $request['cheque_id'])->first(['id', 'amount']);
            //echo $getamount->amount.'=='.number_format((float)$request['amount'], 4, '.', '');die;
            if ($getamount->amount != number_format((float) $request['amount'], 4, '.', '')) {
              return back()->with('alert', 'Investment  amount is not equal to cheque amount');
            }
          }
        }
      } else {
        $request->request->add(['payment-mode' => 0]);
      }
      Session::put('created_at', $request['created_at']);
      $globaldate = $request['created_at'];
      $type = 'create';
      //get login user branch id(branch manager)pass auth id
      $branch_id = $request['branchid'];
      $getBranchCode = getBranchCode($branch_id);
      $branchCode = $getBranchCode->branch_code;
      //get login user branch id(branch manager)pass auth id
      $faCode = getPlanCode($request['investmentplan']);
      $planId = $request['investmentplan'];
      $investmentMiCode = getInvesmentMiCodeNew($planId, $branch_id);
      if (!empty($investmentMiCode)) {
        $miCodeAdd = $investmentMiCode->mi_code + 1;
      } else {
        $miCodeAdd = 1;
      }
      $miCode = str_pad($miCodeAdd, 5, '0', STR_PAD_LEFT);
      $miCodeBig = str_pad($miCodeAdd, 5, '0', STR_PAD_LEFT);
      $passbook = '720' . $branchCode . $faCode . $miCodeBig;
      $certificate = '719' . $branchCode . $faCode . $miCodeBig;
      // Invesment Account no
      $investmentAccount = $branchCode . $faCode . $miCode;
      $data = $this->getData($request->all(), $type, $miCode, $investmentAccount, $branch_id);
      if ($faCode == 705 || $faCode == 706 || $faCode == 712) {
        $data['certificate_no'] = $certificate;
      } else {
        if ($faCode != 703) {
          $data['passbook_no'] = $passbook;
        }
      }
      $sAccount = $this->getSavingAccountDetails($request->input('memberAutoId'));
      if (count($sAccount) > 0) {
        $ssbAccountNumber = $sAccount[0]->account_no;
        $ssbId = $sAccount[0]->id;
        $ssbBalance = $sAccount[0]->balance;
      } else {
        $ssbAccountNumber = '';
        $ssbId = '';
        $ssbBalance = '';
      }
      $received_cheque_id = $cheque_id = NULL;
      $cheque_deposit_bank_id = NULL;
      $cheque_deposit_bank_ac_id = NULL;
      $cheque_no = NULL;
      $cheque_date = $pdate = NULL;
      $online_deposit_bank_id = NULL;
      $online_deposit_bank_ac_id = NULL;
      $online_transction_no = NULL;
      $online_transction_date = NULL;
      if ($request->input('payment-mode') == 1) {
        $chequeDetail = \App\Models\ReceivedCheque::where('id', $request['cheque_id'])->first();
        $received_cheque_id = $cheque_id = $request['cheque_id'];
        $cheque_deposit_bank_id = $chequeDetail->deposit_bank_id;
        $cheque_deposit_bank_ac_id = $chequeDetail->deposit_account_id;
        $cheque_no = $request['cheque-number'];
        $cheque_date = $pdate = date("Y-m-d", strtotime(str_replace('/', '-', $request['cheque-date'])));
      }
      if ($request->input('payment-mode') == 2) {
        $online_deposit_bank_id = $request['rd_online_bank_id'];
        $online_deposit_bank_ac_id = $request['rd_online_bank_ac_id'];
        $online_transction_no = $request['transaction-id'];
        $online_transction_date = $pdate = date("Y-m-d", strtotime(str_replace('/', '-', $request['date'])));
      }
      switch ($plantype) {
        case "saving-account":
          $is_primary = 0;
          $description = 'SSB Account opening';
          if (SavingAccount::where('member_id', $request['memberAutoId'])->count() > 0) {
            return back()->with('alert', 'Your saving account already created!');
          }
          $res = Memberinvestments::create($data);
          $insertedid = $res->id;
          $encodeDate = json_encode($data);
          $savingAccountId = $res->account_number;
          if ($plantype == 'saving-account') {
            $type = 0;
            $createAccount = CommanController::createSavingAccountDescriptionModify($request['memberAutoId'], $branch_id, $branchCode, $request['amount'], 0, $insertedid, $miCode, $investmentAccount, $is_primary, $faCode, $description, $request['associatemid'], $type);
          }
          // $createAccount = CommanController::createSavingAccountDescriptionModify($request['memberAutoId'],$branch_id,$branchCode,$request['amount'],0,$insertedid,$miCode,$investmentAccount,$is_primary,$faCode,$description,$request['associatemid']);
          $mRes = Member::find($request['memberAutoId']);
          $mData['ssb_account'] = $investmentAccount;
          $mRes->update($mData);
          // $satRefId = CommanController::createTransactionReferences($createAccount['ssb_transaction_id'],$insertedid);
          $ssbAccountId = $createAccount['ssb_id'];
          $amountArraySsb = array('1' => $request['amount']);
          $amount_deposit_by_name = substr($request['member_name'], 0, strpos($request['member_name'], "-"));
          // $ssbCreateTran = CommanController::createTransaction($satRefId,1,$ssbAccountId,$request['memberAutoId'],$branch_id,$branchCode,$amountArraySsb,0,$amount_deposit_by_name,$request['memberAutoId'],$investmentAccount,$cheque_dd_no='0',$bank_name=NULL,$branch_name=NULL,$payment_date=NULL,$online_payment_id=NULL,$online_payment_by=NULL,$saving_account_id=0,'CR');
          // $createDayBook = CommanController::createDayBook($ssbCreateTran,$satRefId,1,$insertedid,$request['associatemid'],$request['memberAutoId'],$request['amount'],$request['amount'],$withdrawal=0,$description,$ssbAccountNumber,$branch_id,$branchCode,$amountArraySsb,0,$amount_deposit_by_name,$request['memberAutoId'],$investmentAccount,$request['cheque-number'],NULL,NULL,date('Y-m-d'),NULL,$online_payment_by=NULL,$ssbId,'CR');
          // ---------------------------  Day book modify --------------------------
          $ppmode = 0;
          if ($request->input('payment-mode') == 1) { //cheque
            $ppmode = 1;
          }
          if ($request->input('payment-mode') == 2) { //onine
            $ppmode = 3;
          }
          if ($request->input('payment-mode') == 3) {
            //ssb
            $ppmode = 4;
          }
          $createDayBook = CommanController::createDayBookNew(NULL, NULL, 2, $ssbAccountId, $request['associatemid'], $request['memberAutoId'], $request['amount'], $request['amount'], $withdrawal = 0, $description, $ssbAccountNumber, $branch_id, $branchCode, $amountArraySsb, $ppmode, $amount_deposit_by_name, $request['memberAutoId'], $investmentAccount, $request['cheque-number'], $request['bank-name'], $request['branch-name'], $pdate, $online_transction_no, $online_payment_by = NULL, $ssbId, 'CR', $received_cheque_id, $cheque_deposit_bank_id, $cheque_deposit_bank_ac_id, $online_deposit_bank_id, $online_deposit_bank_ac_id);
          // ---------------------------  HEAD IMPLEMENT --------------------------
          $this->investHeadCreateSSB($request['amount'], $globaldate, $ssbAccountId, $planId, $cheque_no, $cheque_id, $cheque_date, $online_transction_no, $online_transction_date, $online_deposit_bank_id, $online_deposit_bank_ac_id, $branch_id, $request['associatemid'], $request['memberAutoId'], $ssbId, $createAccount['ssb_transaction_id'], $request->input('payment-mode'), $investmentAccount, $companyId);
          $satRefId = NULL;
          $ssbCreateTran = NULL;
          //--------------------------------HEAD IMPLEMENT  -------------------------
          $transaction = $this->transactionData($satRefId, $request->all(), $insertedid, $type, $ssbCreateTran);
          $res = Investmentplantransactions::create($transaction);
          $fNominee = $this->getFirstNomineeData($request->all(), $insertedid, $type);
          $res = Memberinvestmentsnominees::create($fNominee);
          if ($request['second_nominee_add'] == 1) {
            $sNominee = $this->getSecondNomineeData($request->all(), $insertedid, $type);
            $res = Memberinvestmentsnominees::create($sNominee);
          }
          $savingAccountDetail = SavingAccount::where('account_no', $savingAccountId)->first();
          if ($request['stationary_charge'] > 0) {
            $this->stationaryCharges($insertedid);
          }
          $text = 'Dear Member, Thank You for Choosing S.B. Micro Finance association Your saving A/c No.' . $savingAccountDetail->account_no . ' is Credited on '
            . $savingAccountDetail->created_at->format('d M Y') . ' With Rs. ' . round($request['amount'], 2) . '. Thanks Have a good day';
          $temaplteId = 1207161519023692218;
          break;
        case "samraddh-kanyadhan-yojana":
          $description = 'SK Account opening';
          $res = Memberinvestments::create($data);
          $insertedid = $res->id;
          $investmentDetail = Memberinvestments::find($res->id);
          $amountArray = array('1' => $request->input('amount'));
          $amount_deposit_by_name = $request->input('firstname') . ' ' . $request->input('lastname');
          if ($request->input('payment-mode') == 3) {
            $savingTransaction = $this->savingAccountTransactionDataNew($request->all(), $insertedid, $type, 'SK', $investmentAccount);
            $res = SavingAccountTranscation::create($savingTransaction);
            // $satRefId = CommanController::createTransactionReferences($res->id,$insertedid);
            $satRefId = NULL;
            $sAccountId = $ssbId;
            $sAccountAmount = $ssbBalance - $request->input('amount');
            $sResult = SavingAccount::find($sAccountId);
            $sData['balance'] = $sAccountAmount;
            $sResult->update($sData);
            // $ssbCreateTran = CommanController::createTransaction($satRefId,1,$sAccountId,$request->input('memberAutoId'),$branch_id,$branchCode,$amountArray,$request['payment-mode'],$amount_deposit_by_name,$request->input('memberAutoId'),$ssbAccountNumber,$request['cheque-number'],$request['bank-name'],$request['branch-name'],$request['cheque-date'],$request['transaction-id'],$online_payment_by=NULL,$saving_account_id=0,'DR');
            $sAccountNumber = $ssbAccountNumber;
          } else {
            $sAccountNumber = NULL;
            $satRefId = NULL;
            // $ssbCreateTran = $this->commonTransactionLogData($satRefId,$request->all(),$insertedid,$type);
          }
          // $createDayBook = CommanController::createDayBook($ssbCreateTran,$satRefId,2,$insertedid,$request['associatemid'],$request['memberAutoId'],$request['amount'],$request['amount'],$withdrawal=0,$description,$sAccountNumber,$branch_id,$branchCode,$amountArray,$request['payment-mode'],$amount_deposit_by_name,$request['memberAutoId'],$investmentAccount,$request['cheque-number'],$request['bank-name'],$request['branch-name'],date("Y-m-d ", strtotime($request['created_at'])),$request['transaction-id'],$online_payment_by=NULL,$ssbId,'CR');
          // ---------------------------  Day book modify --------------------------
          $ppmode = 0;
          if ($request->input('payment-mode') == 1) { //cheque
            $ppmode = 1;
          }
          if ($request->input('payment-mode') == 2) { //onine
            $ppmode = 3;
          }
          if ($request->input('payment-mode') == 3) {
            //ssb
            $ppmode = 4;
          }
          DD($request['amount']);
          $createDayBook = CommanController::createDayBookNew(NULL, NULL, 2, $insertedid, $request['associatemid'], $request['memberAutoId'], $request['amount'], $request['amount'], $withdrawal = 0, $description, $ssbAccountNumber, $branch_id, $branchCode, $amountArray, $ppmode, $amount_deposit_by_name, $request['memberAutoId'], $investmentAccount, $request['cheque-number'], $request['bank-name'], $request['branch-name'], $pdate, $online_transction_no, $online_payment_by = NULL, $ssbId, 'CR', $received_cheque_id, $cheque_deposit_bank_id, $cheque_deposit_bank_ac_id, $online_deposit_bank_id, $online_deposit_bank_ac_id, $request->company_id);
          // ---------------------------  HEAD IMPLEMENT --------------------------
          $this->investHeadCreate($request['amount'], $globaldate, $insertedid, $planId, $cheque_no, $cheque_id, $cheque_date, $online_transction_no, $online_transction_date, $online_deposit_bank_id, $online_deposit_bank_ac_id, $branch_id, $request['associatemid'], $request['memberAutoId'], $ssbId, $createDayBook, $request->input('payment-mode'), $investmentDetail->account_number, $companyId);
          //--------------------------------------------------------------------------
          /* ------------------ commission genarate-----------------*/
          // $commission =CommanController:: commissionDistributeInvestment($request['associatemid'],$insertedid,3,$request['amount'],1,$planId,$branch_id,$request['tenure'],$createDayBook);
          // $commission_collection =CommanController::commissionCollectionInvestment($request['associatemid'],$insertedid,5,$request['amount'],1,$planId,$branch_id,$request['tenure'],$createDayBook);
          /*----- ------  credit business start ---- ---------------*/
          $creditBusiness = CommanController::associateCreditBusiness($request['associatemid'], $insertedid, 1, $request['amount'], 1, $planId, $request['tenure'], $createDayBook);
          /*----- ------  credit business end ---- ---------------*/
          /* ------------------ commission genarate-----------------*/
          $transaction = $this->transactionData($satRefId, $request->all(), $insertedid, $type, NULL);
          $res = Investmentplantransactions::create($transaction);
          $paymentData = $this->getPaymentMethodData($request->all(), $insertedid, $type);
          //$res = Memberinvestmentspayments::create($paymentData);
          /*--------------------- received cheque payment -----------------------*/
          if ($request->input('payment-mode') == 1) {
            $receivedPayment['type'] = 2;
            $receivedPayment['branch_id'] = $branch_id;
            $receivedPayment['investment_id'] = $insertedid;
            $receivedPayment['day_book_id'] = $createDayBook;
            $receivedPayment['cheque_id'] = $request['cheque_id'];
            $receivedPayment['created_at'] = $request['created_at'];
            $receivedCreate = \App\Models\ReceivedChequePayment::create($receivedPayment);
            $dataRC['status'] = 3;
            $receivedcheque = \App\Models\ReceivedCheque::find($request['cheque_id']);
            $receivedcheque->update($dataRC);
          }
          /*--------------------- received cheque payment -----------------------*/
          if ($request['stationary_charge'] > 0) {
            $this->stationaryCharges($insertedid);
          }
          $text = 'Dear Member, Thank You for Choosing S.B. Micro Finance association Your SK A/c No.' . $investmentDetail->account_number . ' is Credited on ' . $investmentDetail->created_at->format('d M Y') . ' With Rs. ' . round($investmentDetail->deposite_amount, 2) . '. Thanks Have a good day';
          $temaplteId = 1207161519023692218;
          break;
        case "special-samraddh-money-back":
          $description = 'SMB Account opening';
          $res = Memberinvestments::create($data);
          $monyBackAccount = $res->id;
          $investmentDetail = Memberinvestments::find($res->id);
          $insertedid = $res->id;
          $fNominee = $this->getFirstNomineeData($request->all(), $insertedid, $type);
          $res = Memberinvestmentsnominees::create($fNominee);
          if ($request['second_nominee_add'] == 1) {
            $sNominee = $this->getSecondNomineeData($request->all(), $insertedid, $type);
            $res = Memberinvestmentsnominees::create($sNominee);
          }
          $paymentData = $this->getPaymentMethodData($request->all(), $insertedid, $type);
          //$res = Memberinvestmentspayments::create($paymentData);
          $amountArray = array('1' => $request->input('amount'));
          $amount_deposit_by_name = $request->input('firstname') . ' ' . $request->input('lastname');
          if ($request->input('payment-mode') == 3) {
            $savingTransaction = $this->savingAccountTransactionDataNew($request->all(), $insertedid, $type, 'SMB', $investmentAccount);
            $res = SavingAccountTranscation::create($savingTransaction);
            $satRefId = NULL;
            $sAccountId = $ssbId;
            $sAccountAmount = $ssbBalance - $request->input('amount');
            $sResult = SavingAccount::find($sAccountId);
            $sData['balance'] = $sAccountAmount;
            $sResult->update($sData);
            // $ssbCreateTran = CommanController::createTransaction($satRefId,1,$sAccountId,$request->input('memberAutoId'),$branch_id,$branchCode,$amountArray,$request['payment-mode'],$amount_deposit_by_name,$request->input('memberAutoId'),$ssbAccountNumber,$request['cheque-number'],$request['bank-name'],$request['branch-name'],$request['cheque-date'],$request['transaction-id'],$online_payment_by=NULL,$saving_account_id=0,'DR');
            $ssbCreateTran = NULL;
            $sAccountNumber = $ssbAccountNumber;
          } else {
            $satRefId = NULL;
            $sAccountNumber = NULL;
            $ssbCreateTran = NULL;
          }
          //$createDayBook = CommanController::createDayBook($ssbCreateTran,$satRefId,2,$insertedid,$request['associatemid'],$request['memberAutoId'],$request['amount'],$request['amount'],$withdrawal=0,$description,$sAccountNumber,$branch_id,$branchCode,$amountArray,$request['payment-mode'],$amount_deposit_by_name,$request['memberAutoId'],$investmentAccount,$request['cheque-number'],$request['bank-name'],$request['branch-name'],date("Y-m-d ", strtotime($request['created_at'])),$request['transaction-id'],$online_payment_by=NULL,$ssbId,'CR');
          // ---------------------------  Day book modify --------------------------
          $ppmode = 0;
          if ($request->input('payment-mode') == 1) { //cheque
            $ppmode = 1;
          }
          if ($request->input('payment-mode') == 2) { //onine
            $ppmode = 3;
          }
          if ($request->input('payment-mode') == 3) {
            //ssb
            $ppmode = 4;
          }
          $createDayBook = CommanController::createDayBookNew(NULL, NULL, 2, $insertedid, $request['associatemid'], $request['memberAutoId'], $request['amount'], $request['amount'], $withdrawal = 0, $description, $ssbAccountNumber, $branch_id, $branchCode, $amountArray, $ppmode, $amount_deposit_by_name, $request['memberAutoId'], $investmentAccount, $request['cheque-number'], $request['bank-name'], $request['branch-name'], $pdate, $online_transction_no, $online_payment_by = NULL, $ssbId, 'CR', $received_cheque_id, $cheque_deposit_bank_id, $cheque_deposit_bank_ac_id, $online_deposit_bank_id, $online_deposit_bank_ac_id, $request->company_id);
          // ---------------------------  HEAD IMPLEMENT --------------------------
          $this->investHeadCreate($request['amount'], $globaldate, $insertedid, $planId, $cheque_no, $cheque_id, $cheque_date, $online_transction_no, $online_transction_date, $online_deposit_bank_id, $online_deposit_bank_ac_id, $branch_id, $request['associatemid'], $request['memberAutoId'], $ssbId, $createDayBook, $request->input('payment-mode'), $investmentDetail->account_number, $companyId);
          //--------------------------------------------------------------------------
          /* ------------------ commission genarate-----------------*/
          // $commission =CommanController:: commissionDistributeInvestment($request['associatemid'],$insertedid,3,$request['amount'],1,$planId,$branch_id,$request['tenure'],$createDayBook);
          // $commission_collection =CommanController::commissionCollectionInvestment($request['associatemid'],$insertedid,5,$request['amount'],1,$planId,$branch_id,$request['tenure'],$createDayBook);
          /*----- ------  credit business start ---- ---------------*/
          $creditBusiness = CommanController::associateCreditBusiness($request['associatemid'], $insertedid, 1, $request['amount'], 1, $planId, $request['tenure'], $createDayBook);
          /*----- ------  credit business end ---- ---------------*/
          /* ------------------ commission genarate-----------------*/
          $transaction = $this->transactionData($satRefId, $request->all(), $insertedid, $type, $ssbCreateTran);
          $res = Investmentplantransactions::create($transaction);
          //   $paymentData = $this->getPaymentMethodData($request->all(),$insertedid,$type);
          //    //$res = Memberinvestmentspayments::create($paymentData);
          /*--------------------- received cheque payment -----------------------*/
          if ($request->input('payment-mode') == 1) {
            $receivedPayment['type'] = 2;
            $receivedPayment['branch_id'] = $branch_id;
            $receivedPayment['investment_id'] = $insertedid;
            $receivedPayment['day_book_id'] = $createDayBook;
            $receivedPayment['cheque_id'] = $request['cheque_id'];
            $receivedPayment['created_at'] = $request['created_at'];
            $receivedCreate = \App\Models\ReceivedChequePayment::create($receivedPayment);
            $dataRC['status'] = 3;
            $receivedcheque = \App\Models\ReceivedCheque::find($request['cheque_id']);
            $receivedcheque->update($dataRC);
          }
          /*--------------------- received cheque payment -----------------------*/
          if ($request['stationary_charge'] > 0) {
            $this->stationaryCharges($insertedid);
          }
          $memberInvestData = Memberinvestments::with('ssb')->find($monyBackAccount);
          $text = 'Dear Member, Thank You for Choosing S.B. Micro Finance association Your Saving A/C ' . $memberInvestData->ssb_account_number . ' is Created on ' . $memberInvestData->created_at->format('d M Y') . ' with Rs.' . $request['ssbamount'] . ' CR, Money Back A/c No. ' . $memberInvestData->account_number . ' is Created on ' . $memberInvestData->created_at->format('d M Y') . ' with Rs. ' . round($memberInvestData->deposite_amount, 2) . ' CR. Have a good day';
          $temaplteId = 1207161519138416891;
          break;
        case "flexi-fixed-deposit":
          $description = 'FFD Account opening';
          $res = Memberinvestments::create($data);
          $insertedid = $res->id;
          $investmentDetail = Memberinvestments::find($res->id);
          $fNominee = $this->getFirstNomineeData($request->all(), $insertedid, $type);
          $res = Memberinvestmentsnominees::create($fNominee);
          if ($request['second_nominee_add'] == 1) {
            $sNominee = $this->getSecondNomineeData($request->all(), $insertedid, $type);
            $res = Memberinvestmentsnominees::create($sNominee);
          }
          $paymentData = $this->getPaymentMethodData($request->all(), $insertedid, $type);
          //$res = Memberinvestmentspayments::create($paymentData);
          $amountArray = array('1' => $request->input('amount'));
          $amount_deposit_by_name = $request->input('firstname') . ' ' . $request->input('lastname');
          if ($request->input('payment-mode') == 3) {
            //$savingTransaction = $this->savingAccountTransactionData($request->all(),$insertedid,$type);
            $savingTransaction = $this->savingAccountTransactionDataNew($request->all(), $insertedid, $type, 'FFD', $investmentAccount);
            $res = SavingAccountTranscation::create($savingTransaction);
            $satRefId = NULL;
            $sAccountId = $ssbId;
            $sAccountAmount = $ssbBalance - $request->input('amount');
            $sResult = SavingAccount::find($sAccountId);
            $sData['balance'] = $sAccountAmount;
            $sResult->update($sData);
            // $ssbCreateTran = CommanController::createTransaction($satRefId,1,$sAccountId,$request->input('memberAutoId'),$branch_id,$branchCode,$amountArray,$request['payment-mode'],$amount_deposit_by_name,$request->input('memberAutoId'),$ssbAccountNumber,$request['cheque-number'],$request['bank-name'],$request['branch-name'],$request['cheque-date'],$request['transaction-id'],$online_payment_by=NULL,$saving_account_id=0,'DR');
            $sAccountNumber = $ssbAccountNumber;
          } else {
            $sAccountNumber = NULL;
            $satRefId = NULL;
            $ssbCreateTran = NULL;
          }
          // $createDayBook = CommanController::createDayBook($ssbCreateTran,$satRefId,2,$insertedid,$request['associatemid'],$request['memberAutoId'],$request['amount'],$request['amount'],$withdrawal=0,$description,$sAccountNumber,$branch_id,$branchCode,$amountArray,$request['payment-mode'],$amount_deposit_by_name,$request['memberAutoId'],$investmentAccount,$request['cheque-number'],$request['bank-name'],$request['branch-name'],date("Y-m-d ", strtotime($request['created_at'])),$request['transaction-id'],$online_payment_by=NULL,$ssbId,'CR');
          // ---------------------------  Day book modify --------------------------
          $ppmode = 0;
          if ($request->input('payment-mode') == 1) { //cheque
            $ppmode = 1;
          }
          if ($request->input('payment-mode') == 2) { //onine
            $ppmode = 3;
          }
          if ($request->input('payment-mode') == 3) {
            //ssb
            $ppmode = 4;
          }
          $createDayBook = CommanController::createDayBookNew(NULL, NULL, 2, $insertedid, $request['associatemid'], $request['memberAutoId'], $request['amount'], $request['amount'], $withdrawal = 0, $description, $ssbAccountNumber, $branch_id, $branchCode, $amountArray, $ppmode, $amount_deposit_by_name, $request['memberAutoId'], $investmentAccount, $request['cheque-number'], $request['bank-name'], $request['branch-name'], $pdate, $online_transction_no, $online_payment_by = NULL, $ssbId, 'CR', $received_cheque_id, $cheque_deposit_bank_id, $cheque_deposit_bank_ac_id, $online_deposit_bank_id, $online_deposit_bank_ac_id, $request->company_id);
          // ---------------------------  HEAD IMPLEMENT --------------------------
          $this->investHeadCreate($request['amount'], $globaldate, $insertedid, $planId, $cheque_no, $cheque_id, $cheque_date, $online_transction_no, $online_transction_date, $online_deposit_bank_id, $online_deposit_bank_ac_id, $branch_id, $request['associatemid'], $request['memberAutoId'], $ssbId, $createDayBook, $request->input('payment-mode'), $investmentDetail->account_number, $companyId);
          //--------------------------------------------------------------------------
          /* ------------------ commission genarate-----------------*/
          // $commission =CommanController:: commissionDistributeInvestment($request['associatemid'],$insertedid,3,$request['amount'],1,$planId,$branch_id,$request['tenure'],$createDayBook);
          //  $commission_collection =CommanController::commissionCollectionInvestment($request['associatemid'],$insertedid,5,$request['amount'],1,$planId,$branch_id,$request['tenure'],$createDayBook);
          /*----- ------  credit business start ---- ---------------*/
          $creditBusiness = CommanController::associateCreditBusiness($request['associatemid'], $insertedid, 1, $request['amount'], 1, $planId, $request['tenure'], $createDayBook);
          /*----- ------  credit business end ---- ---------------*/
          /* ------------------ commission genarate-----------------*/
          $transaction = $this->transactionData($satRefId, $request->all(), $insertedid, $type, $ssbCreateTran);
          $res = Investmentplantransactions::create($transaction);
          //    $paymentData = $this->getPaymentMethodData($request->all(),$insertedid,$type);
          //    //$res = Memberinvestmentspayments::create($paymentData);
          /*--------------------- received cheque payment -----------------------*/
          if ($request->input('payment-mode') == 1) {
            $receivedPayment['type'] = 2;
            $receivedPayment['branch_id'] = $branch_id;
            $receivedPayment['investment_id'] = $insertedid;
            $receivedPayment['day_book_id'] = $createDayBook;
            $receivedPayment['cheque_id'] = $request['cheque_id'];
            $receivedPayment['created_at'] = $request['created_at'];
            $receivedCreate = \App\Models\ReceivedChequePayment::create($receivedPayment);
            $dataRC['status'] = 3;
            $receivedcheque = \App\Models\ReceivedCheque::find($request['cheque_id']);
            $receivedcheque->update($dataRC);
          }
          /*--------------------- received cheque payment -----------------------*/
          if ($request['stationary_charge'] > 0) {
            $this->stationaryCharges($insertedid);
          }
          $text = 'Dear Member, Thank You for Choosing S.B. Micro Finance association Your FFD A/c No.' . $investmentDetail->account_number . ' is Credited on '
            . $investmentDetail->created_at->format('d M Y') . ' With Rs. ' . round($investmentDetail->deposite_amount, 2) . '. Thanks Have a good day';
          $temaplteId = 1207161519023692218;
          break;
        case "fixed-recurring-deposit":
          $description = 'FRD Account opening';
          $res = Memberinvestments::create($data);
          $insertedid = $res->id;
          $investmentDetail = Memberinvestments::find($res->id);
          $fNominee = $this->getFirstNomineeData($request->all(), $insertedid, $type);
          $res = Memberinvestmentsnominees::create($fNominee);
          if ($request['second_nominee_add'] == 1) {
            $sNominee = $this->getSecondNomineeData($request->all(), $insertedid, $type);
            $res = Memberinvestmentsnominees::create($sNominee);
          }
          $paymentData = $this->getPaymentMethodData($request->all(), $insertedid, $type);
          //$res = Memberinvestmentspayments::create($paymentData);
          $amountArray = array('1' => $request->input('amount'));
          $amount_deposit_by_name = $request->input('firstname') . ' ' . $request->input('lastname');
          if ($request->input('payment-mode') == 3) {
            // $savingTransaction = $this->savingAccountTransactionData($request->all(),$insertedid,$type);
            $savingTransaction = $this->savingAccountTransactionDataNew($request->all(), $insertedid, $type, 'FRD', $investmentAccount);
            $res = SavingAccountTranscation::create($savingTransaction);
            $satRefId = NULL;
            $sAccountId = $ssbId;
            $sAccountAmount = $ssbBalance - $request->input('amount');
            $sResult = SavingAccount::find($sAccountId);
            $sData['balance'] = $sAccountAmount;
            $sResult->update($sData);
            // $ssbCreateTran = CommanController::createTransaction($satRefId,1,$sAccountId,$request->input('memberAutoId'),$branch_id,$branchCode,$amountArray,$request['payment-mode'],$amount_deposit_by_name,$request->input('memberAutoId'),$ssbAccountNumber,$request['cheque-number'],$request['bank-name'],$request['branch-name'],$request['cheque-date'],$request['transaction-id'],$online_payment_by=NULL,$saving_account_id=0,'DR');
            $sAccountNumber = $ssbAccountNumber;
          } else {
            $sAccountNumber = NULL;
            $satRefId = NULL;
          }
          $ssbCreateTran = NULL;
          //$createDayBook = CommanController::createDayBook($ssbCreateTran,$satRefId,2,$insertedid,$request['associatemid'],$request['memberAutoId'],$request['amount'],$request['amount'],$withdrawal=0,$description,$sAccountNumber,$branch_id,$branchCode,$amountArray,$request['payment-mode'],$amount_deposit_by_name,$request['memberAutoId'],$investmentAccount,$request['cheque-number'],$request['bank-name'],$request['branch-name'],date("Y-m-d ", strtotime($request['created_at'])),$request['transaction-id'],$online_payment_by=NULL,$ssbId,'CR');
          // ---------------------------  Day book modify --------------------------
          $ppmode = 0;
          if ($request->input('payment-mode') == 1) { //cheque
            $ppmode = 1;
          }
          if ($request->input('payment-mode') == 2) { //onine
            $ppmode = 3;
          }
          if ($request->input('payment-mode') == 3) {
            //ssb
            $ppmode = 4;
          }
          $createDayBook = CommanController::createDayBookNew(NULL, NULL, 2, $insertedid, $request['associatemid'], $request['memberAutoId'], $request['amount'], $request['amount'], $withdrawal = 0, $description, $ssbAccountNumber, $branch_id, $branchCode, $amountArray, $ppmode, $amount_deposit_by_name, $request['memberAutoId'], $investmentAccount, $request['cheque-number'], $request['bank-name'], $request['branch-name'], $pdate, $online_transction_no, $online_payment_by = NULL, $ssbId, 'CR', $received_cheque_id, $cheque_deposit_bank_id, $cheque_deposit_bank_ac_id, $online_deposit_bank_id, $online_deposit_bank_ac_id, $request->company_id);
          // ---------------------------  HEAD IMPLEMENT --------------------------
          $this->investHeadCreate($request['amount'], $globaldate, $insertedid, $planId, $cheque_no, $cheque_id, $cheque_date, $online_transction_no, $online_transction_date, $online_deposit_bank_id, $online_deposit_bank_ac_id, $branch_id, $request['associatemid'], $request['memberAutoId'], $ssbId, $createDayBook, $request->input('payment-mode'), $investmentDetail->account_number, $companyId);
          //--------------------------------------------------------------------------
          /* ------------------ commission genarate-----------------*/
          // $commission =CommanController:: commissionDistributeInvestment($request['associatemid'],$insertedid,3,$request['amount'],1,$planId,$branch_id,$request['tenure'],$createDayBook);
          //  $commission_collection =CommanController::commissionCollectionInvestment($request['associatemid'],$insertedid,5,$request['amount'],1,$planId,$branch_id,$request['tenure'],$createDayBook);
          /*----- ------  credit business start ---- ---------------*/
          $creditBusiness = CommanController::associateCreditBusiness($request['associatemid'], $insertedid, 1, $request['amount'], 1, $planId, $request['tenure'], $createDayBook);
          /*----- ------  credit business end ---- ---------------*/
          /* ------------------ commission genarate-----------------*/
          $transaction = $this->transactionData($satRefId, $request->all(), $insertedid, $type, $ssbCreateTran);
          $res = Investmentplantransactions::create($transaction);
          // $paymentData = $this->getPaymentMethodData($request->all(),$insertedid,$type);
          ////$res = Memberinvestmentspayments::create($paymentData);
          /*--------------------- received cheque payment -----------------------*/
          if ($request->input('payment-mode') == 1) {
            $receivedPayment['type'] = 2;
            $receivedPayment['branch_id'] = $branch_id;
            $receivedPayment['investment_id'] = $insertedid;
            $receivedPayment['day_book_id'] = $createDayBook;
            $receivedPayment['cheque_id'] = $request['cheque_id'];
            $receivedPayment['created_at'] = $request['created_at'];
            $receivedCreate = \App\Models\ReceivedChequePayment::create($receivedPayment);
            $dataRC['status'] = 3;
            $receivedcheque = \App\Models\ReceivedCheque::find($request['cheque_id']);
            $receivedcheque->update($dataRC);
          }
          /*--------------------- received cheque payment -----------------------*/
          if ($request['stationary_charge'] > 0) {
            $this->stationaryCharges($insertedid);
          }
          $text = 'Dear Member, Thank You for Choosing S.B. Micro Finance association Your FRD A/c No.' . $investmentDetail->account_number . ' is Credited on '
            . $investmentDetail->created_at->format('d M Y') . ' With Rs. ' . round($investmentDetail->deposite_amount, 2) . 'CR. Thanks Have a good day';
          $temaplteId = 1207161519023692218;
          break;
        case "samraddh-jeevan":
          $description = 'SJ Account opening';
          $res = Memberinvestments::create($data);
          $insertedid = $res->id;
          $monyBackAccount = $res->id;
          $investmentDetail = Memberinvestments::find($res->id);
          $fNominee = $this->getFirstNomineeData($request->all(), $insertedid, $type);
          $res = Memberinvestmentsnominees::create($fNominee);
          if ($request['second_nominee_add'] == 1) {
            $sNominee = $this->getSecondNomineeData($request->all(), $insertedid, $type);
            $res = Memberinvestmentsnominees::create($sNominee);
          }
          $paymentData = $this->getPaymentMethodData($request->all(), $insertedid, $type);
          ////$res = Memberinvestmentspayments::create($paymentData);
          $amountArray = array('1' => $request->input('amount'));
          $amount_deposit_by_name = $request->input('firstname') . ' ' . $request->input('lastname');
          if ($request->input('payment-mode') == 3) {
            // $savingTransaction = $this->savingAccountTransactionData($request->all(),$insertedid,$type);
            $savingTransaction = $this->savingAccountTransactionDataNew($request->all(), $insertedid, $type, 'SJ', $investmentAccount);
            $res = SavingAccountTranscation::create($savingTransaction);
            $satRefId = NULL;
            $sAccountId = $ssbId;
            $sAccountAmount = $ssbBalance - $request->input('amount');
            $sResult = SavingAccount::find($sAccountId);
            $sData['balance'] = $sAccountAmount;
            $sResult->update($sData);
            $ssbCreateTran = NULL;
            $sAccountNumber = $ssbAccountNumber;
          } else {
            $sAccountNumber = NULL;
            $satRefId = NULL;
            $ssbCreateTran = NULL;
          }
          //$createDayBook = CommanController::createDayBook($ssbCreateTran,$satRefId,2,$insertedid,$request['associatemid'],$request['memberAutoId'],$request['amount'],$request['amount'],$withdrawal=0,$description,$sAccountNumber,$branch_id,$branchCode,$amountArray,$request['payment-mode'],$amount_deposit_by_name,$request['memberAutoId'],$investmentAccount,$request['cheque-number'],$request['bank-name'],$request['branch-name'],date("Y-m-d ", strtotime($request['created_at'])),$request['transaction-id'],$online_payment_by=NULL,$ssbId,'CR');
          // ---------------------------  Day book modify --------------------------
          $ppmode = 0;
          if ($request->input('payment-mode') == 1) { //cheque
            $ppmode = 1;
          }
          if ($request->input('payment-mode') == 2) { //onine
            $ppmode = 3;
          }
          if ($request->input('payment-mode') == 3) {
            //ssb
            $ppmode = 4;
          }
          $createDayBook = CommanController::createDayBookNew(NULL, NULL, 2, $insertedid, $request['associatemid'], $request['memberAutoId'], $request['amount'], $request['amount'], $withdrawal = 0, $description, $ssbAccountNumber, $branch_id, $branchCode, $amountArray, $ppmode, $amount_deposit_by_name, $request['memberAutoId'], $investmentAccount, $request['cheque-number'], $request['bank-name'], $request['branch-name'], $pdate, $online_transction_no, $online_payment_by = NULL, $ssbId, 'CR', $received_cheque_id, $cheque_deposit_bank_id, $cheque_deposit_bank_ac_id, $online_deposit_bank_id, $online_deposit_bank_ac_id, $request->company_id);
          // ---------------------------  HEAD IMPLEMENT --------------------------
          $this->investHeadCreate($request['amount'], $globaldate, $insertedid, $planId, $cheque_no, $cheque_id, $cheque_date, $online_transction_no, $online_transction_date, $online_deposit_bank_id, $online_deposit_bank_ac_id, $branch_id, $request['associatemid'], $request['memberAutoId'], $ssbId, $createDayBook, $request->input('payment-mode'), $investmentDetail->account_number, $companyId);
          //--------------------------------------------------------------------------
          /* ------------------ commission genarate-----------------*/
          // $commission =CommanController:: commissionDistributeInvestment($request['associatemid'],$insertedid,3,$request['amount'],1,$planId,$branch_id,$request['tenure'],$createDayBook);
          //  $commission_collection =CommanController::commissionCollectionInvestment($request['associatemid'],$insertedid,5,$request['amount'],1,$planId,$branch_id,$request['tenure'],$createDayBook);
          //  /*----- ------  credit business start ---- ---------------*/
          // $creditBusiness =CommanController::associateCreditBusiness($request['associatemid'],$insertedid,1,$request['amount'],1,$planId,$request['tenure'],$createDayBook);
          /*----- ------  credit business end ---- ---------------*/
          /* ------------------ commission genarate-----------------*/
          $transaction = $this->transactionData($satRefId, $request->all(), $insertedid, $type, $ssbCreateTran);
          $res = Investmentplantransactions::create($transaction);
          //$paymentData = $this->getPaymentMethodData($request->all(),$insertedid,$type);
          ////$res = Memberinvestmentspayments::create($paymentData);
          /*--------------------- received cheque payment -----------------------*/
          if ($request->input('payment-mode') == 1) {
            $receivedPayment['type'] = 2;
            $receivedPayment['branch_id'] = $branch_id;
            $receivedPayment['investment_id'] = $insertedid;
            $receivedPayment['day_book_id'] = $createDayBook;
            $receivedPayment['cheque_id'] = $request['cheque_id'];
            $receivedPayment['created_at'] = $request['created_at'];
            $receivedCreate = \App\Models\ReceivedChequePayment::create($receivedPayment);
            $dataRC['status'] = 3;
            $receivedcheque = \App\Models\ReceivedCheque::find($request['cheque_id']);
            $receivedcheque->update($dataRC);
          }
          /*--------------------- received cheque payment -----------------------*/
          if ($request['stationary_charge'] > 0) {
            $this->stationaryCharges($insertedid);
          }
          $memberInvestData = Memberinvestments::with('ssb')->find($monyBackAccount);
          $text = 'Dear Member, Thank You for Choosing S.B. Micro Finance association Your S. Jeevan A/c No.' . $memberInvestData->account_number . ' is Credited on ' . $memberInvestData->created_at->format('d M Y') . ' With Rs. ' . round($memberInvestData->deposite_amount, 2) . '. Thanks Have a good day';
          $temaplteId = 1207161519023692218;
          break;
        case "daily-deposit":
          $description = 'SDD Account opening';
          $res = Memberinvestments::create($data);
          $insertedid = $res->id;
          $investmentDetail = Memberinvestments::find($res->id);
          $fNominee = $this->getFirstNomineeData($request->all(), $insertedid, $type);
          $res = Memberinvestmentsnominees::create($fNominee);
          if ($request['second_nominee_add'] == 1) {
            $sNominee = $this->getSecondNomineeData($request->all(), $insertedid, $type);
            $res = Memberinvestmentsnominees::create($sNominee);
          }
          $paymentData = $this->getPaymentMethodData($request->all(), $insertedid, $type);
          //$res = Memberinvestmentspayments::create($paymentData);
          $amountArray = array('1' => $request->input('amount'));
          $amount_deposit_by_name = $request->input('firstname') . ' ' . $request->input('lastname');
          if ($request->input('payment-mode') == 3) {
            // $savingTransaction = $this->savingAccountTransactionData($request->all(),$insertedid,$type);
            $savingTransaction = $this->savingAccountTransactionDataNew($request->all(), $insertedid, $type, 'SDD', $investmentAccount);
            $res = SavingAccountTranscation::create($savingTransaction);
            $satRefId = NULL;
            $sAccountId = $ssbId;
            $sAccountAmount = $ssbBalance - $request->input('amount');
            $sResult = SavingAccount::find($sAccountId);
            $sData['balance'] = $sAccountAmount;
            $sResult->update($sData);
            $ssbCreateTran = NULL;
            $sAccountNumber = $ssbAccountNumber;
          } else {
            $sAccountNumber = NULL;
            $satRefId = NULL;
            $ssbCreateTran = NULL;
          }
          //$createDayBook = CommanController::createDayBook($ssbCreateTran,$satRefId,2,$insertedid,$request['associatemid'],$request['memberAutoId'],$request['amount'],$request['amount'],$withdrawal=0,$description,$sAccountNumber,$branch_id,$branchCode,$amountArray,$request['payment-mode'],$amount_deposit_by_name,$request['memberAutoId'],$investmentAccount,$request['cheque-number'],$request['bank-name'],$request['branch-name'],date("Y-m-d ", strtotime($request['created_at'])),$request['transaction-id'],$online_payment_by=NULL,$ssbId,'CR');
          // ---------------------------  Day book modify --------------------------
          $ppmode = 0;
          if ($request->input('payment-mode') == 1) { //cheque
            $ppmode = 1;
          }
          if ($request->input('payment-mode') == 2) { //onine
            $ppmode = 3;
          }
          if ($request->input('payment-mode') == 3) {
            //ssb
            $ppmode = 4;
          }
          $createDayBook = CommanController::createDayBookNew(NULL, NULL, 2, $insertedid, $request['associatemid'], $request['memberAutoId'], $request['amount'], $request['amount'], $withdrawal = 0, $description, $ssbAccountNumber, $branch_id, $branchCode, $amountArray, $ppmode, $amount_deposit_by_name, $request['memberAutoId'], $investmentAccount, $request['cheque-number'], $request['bank-name'], $request['branch-name'], $pdate, $online_transction_no, $online_payment_by = NULL, $ssbId, 'CR', $received_cheque_id, $cheque_deposit_bank_id, $cheque_deposit_bank_ac_id, $online_deposit_bank_id, $online_deposit_bank_ac_id, $request->company_id);
          // ---------------------------  HEAD IMPLEMENT --------------------------
          $this->investHeadCreate($request['amount'], $globaldate, $insertedid, $planId, $cheque_no, $cheque_id, $cheque_date, $online_transction_no, $online_transction_date, $online_deposit_bank_id, $online_deposit_bank_ac_id, $branch_id, $request['associatemid'], $request['memberAutoId'], $ssbId, $createDayBook, $request->input('payment-mode'), $investmentDetail->account_number, $companyId);
          //--------------------------------------------------------------------------
          /* ------------------ commission genarate-----------------*/
          // $commission =CommanController:: commissionDistributeInvestment($request['associatemid'],$insertedid,3,$request['amount'],1,$planId,$branch_id,$request['tenure'],$createDayBook);
          //  $commission_collection =CommanController::commissionCollectionInvestment($request['associatemid'],$insertedid,5,$request['amount'],1,$planId,$branch_id,$request['tenure'],$createDayBook);
          /*----- ------  credit business start ---- ---------------*/
          $creditBusiness = CommanController::associateCreditBusiness($request['associatemid'], $insertedid, 1, $request['amount'], 1, $planId, $request['tenure'], $createDayBook);
          /*----- ------  credit business end ---- ---------------*/
          /* ------------------ commission genarate-----------------*/
          $transaction = $this->transactionData($satRefId, $request->all(), $insertedid, $type, $ssbCreateTran);
          $res = Investmentplantransactions::create($transaction);
          // $paymentData = $this->getPaymentMethodData($request->all(),$insertedid,$type);
          //  //$res = Memberinvestmentspayments::create($paymentData);
          /*--------------------- received cheque payment -----------------------*/
          if ($request->input('payment-mode') == 1) {
            $receivedPayment['type'] = 2;
            $receivedPayment['branch_id'] = $branch_id;
            $receivedPayment['investment_id'] = $insertedid;
            $receivedPayment['day_book_id'] = $createDayBook;
            $receivedPayment['cheque_id'] = $request['cheque_id'];
            $receivedPayment['created_at'] = $request['created_at'];
            $receivedCreate = \App\Models\ReceivedChequePayment::create($receivedPayment);
            $dataRC['status'] = 3;
            $receivedcheque = \App\Models\ReceivedCheque::find($request['cheque_id']);
            $receivedcheque->update($dataRC);
          }
          /*--------------------- received cheque payment -----------------------*/
          if ($request['stationary_charge'] > 0) {
            $this->stationaryCharges($insertedid);
          }
          $text = 'Dear Member, Thank You for Choosing S.B. Micro Finance association Your SDD A/c No.' . $investmentDetail->account_number . ' is Credited on '
            . $investmentDetail->created_at->format('d M Y') . ' With Rs. ' . round($investmentDetail->deposite_amount, 2) . 'CR. Thanks Have a good day';
          $temaplteId = 1207161519023692218;
          break;
        case "monthly-income-scheme":
          $description = 'MIS Account opening';
          $res = Memberinvestments::create($data);
          $insertedid = $res->id;
          $monyBackAccount = $res->id;
          $investmentDetail = Memberinvestments::find($res->id);
          $fNominee = $this->getFirstNomineeData($request->all(), $insertedid, $type);
          $res = Memberinvestmentsnominees::create($fNominee);
          if ($request['second_nominee_add'] == 1) {
            $sNominee = $this->getSecondNomineeData($request->all(), $insertedid, $type);
            $res = Memberinvestmentsnominees::create($sNominee);
          }
          $paymentData = $this->getPaymentMethodData($request->all(), $insertedid, $type);
          //$res = Memberinvestmentspayments::create($paymentData);
          $amountArray = array('1' => $request->input('amount'));
          $amount_deposit_by_name = $request->input('firstname') . ' ' . $request->input('lastname');
          if ($request->input('payment-mode') == 3) {
            // $savingTransaction = $this->savingAccountTransactionData($request->all(),$insertedid,$type);
            $savingTransaction = $this->savingAccountTransactionDataNew($request->all(), $insertedid, $type, 'MIS', $investmentAccount);
            $res = SavingAccountTranscation::create($savingTransaction);
            $satRefId = NULL;
            $sAccountId = $ssbId;
            $sAccountAmount = $ssbBalance - $request->input('amount');
            $sResult = SavingAccount::find($sAccountId);
            $sData['balance'] = $sAccountAmount;
            $sResult->update($sData);
            $ssbCreateTran = NULL;
          } else {
            $sAccountNumber = NULL;
            $satRefId = NULL;
            $ssbCreateTran = NULL;
          }
          //$createDayBook = CommanController::createDayBook($ssbCreateTran,$satRefId,2,$insertedid,$request['associatemid'],$request['memberAutoId'],$request['amount'],$request['amount'],$withdrawal=0,$description,$sAccountNumber,$branch_id,$branchCode,$amountArray,$request['payment-mode'],$amount_deposit_by_name,$request['memberAutoId'],$investmentAccount,$request['cheque-number'],$request['bank-name'],$request['branch-name'],date("Y-m-d ", strtotime($request['created_at'])),$request['transaction-id'],$online_payment_by=NULL,$ssbId,'CR');
          // ---------------------------  Day book modify --------------------------
          $ppmode = 0;
          if ($request->input('payment-mode') == 1) { //cheque
            $ppmode = 1;
          }
          if ($request->input('payment-mode') == 2) { //onine
            $ppmode = 3;
          }
          if ($request->input('payment-mode') == 3) {
            //ssb
            $ppmode = 4;
          }
          $createDayBook = CommanController::createDayBookNew(NULL, NULL, 2, $insertedid, $request['associatemid'], $request['memberAutoId'], $request['amount'], $request['amount'], $withdrawal = 0, $description, $ssbAccountNumber, $branch_id, $branchCode, $amountArray, $ppmode, $amount_deposit_by_name, $request['memberAutoId'], $investmentAccount, $request['cheque-number'], $request['bank-name'], $request['branch-name'], $pdate, $online_transction_no, $online_payment_by = NULL, $ssbId, 'CR', $received_cheque_id, $cheque_deposit_bank_id, $cheque_deposit_bank_ac_id, $online_deposit_bank_id, $online_deposit_bank_ac_id, $request->company_id);
          // ---------------------------  HEAD IMPLEMENT --------------------------
          $this->investHeadCreate($request['amount'], $globaldate, $insertedid, $planId, $cheque_no, $cheque_id, $cheque_date, $online_transction_no, $online_transction_date, $online_deposit_bank_id, $online_deposit_bank_ac_id, $branch_id, $request['associatemid'], $request['memberAutoId'], $ssbId, $createDayBook, $request->input('payment-mode'), $investmentDetail->account_number, $companyId);
          //--------------------------------------------------------------------------
          /* ------------------ commission genarate-----------------*/
          // $commission =CommanController:: commissionDistributeInvestment($request['associatemid'],$insertedid,3,$request['amount'],1,$planId,$branch_id,$request['tenure'],$createDayBook);
          //  $commission_collection =CommanController::commissionCollectionInvestment($request['associatemid'],$insertedid,5,$request['amount'],1,$planId,$branch_id,$request['tenure'],$createDayBook);
          /*----- ------  credit business start ---- ---------------*/
          $creditBusiness = CommanController::associateCreditBusiness($request['associatemid'], $insertedid, 1, $request['amount'], 1, $planId, $request['tenure'], $createDayBook);
          /*----- ------  credit business end ---- ---------------*/
          /* ------------------ commission genarate-----------------*/
          $transaction = $this->transactionData($satRefId, $request->all(), $insertedid, $type, $ssbCreateTran);
          $res = Investmentplantransactions::create($transaction);
          //$paymentData = $this->getPaymentMethodData($request->all(),$insertedid,$type);
          // //$res = Memberinvestmentspayments::create($paymentData);
          /*--------------------- received cheque payment -----------------------*/
          if ($request->input('payment-mode') == 1) {
            $receivedPayment['type'] = 2;
            $receivedPayment['branch_id'] = $branch_id;
            $receivedPayment['investment_id'] = $insertedid;
            $receivedPayment['day_book_id'] = $createDayBook;
            $receivedPayment['cheque_id'] = $request['cheque_id'];
            $receivedPayment['created_at'] = $request['created_at'];
            $receivedCreate = \App\Models\ReceivedChequePayment::create($receivedPayment);
            $dataRC['status'] = 3;
            $receivedcheque = \App\Models\ReceivedCheque::find($request['cheque_id']);
            $receivedcheque->update($dataRC);
          }
          /*--------------------- received cheque payment -----------------------*/
          if ($request['stationary_charge'] > 0) {
            $this->stationaryCharges($insertedid);
          }
          $memberInvestData = Memberinvestments::with('ssb')->find($monyBackAccount);
          $text = 'Dear Member, Thank You for Choosing S.B. Micro Finance association Your MIS A/c No.' . $memberInvestData->account_number . ' is Credited on ' . $memberInvestData->created_at->format('d M Y') . ' With Rs. ' . round($memberInvestData->deposite_amount, 2) . '. Thanks Have a good day';
          $temaplteId = 1207161519023692218;
          break;
        case "fixed-deposit":
          $description = 'SFD Account opening';
          $res = Memberinvestments::create($data);
          $insertedid = $res->id;
          $investmentDetail = Memberinvestments::find($res->id);
          $fNominee = $this->getFirstNomineeData($request->all(), $insertedid, $type);
          $res = Memberinvestmentsnominees::create($fNominee);
          if ($request['second_nominee_add'] == 1) {
            $sNominee = $this->getSecondNomineeData($request->all(), $insertedid, $type);
            $res = Memberinvestmentsnominees::create($sNominee);
          }
          $paymentData = $this->getPaymentMethodData($request->all(), $insertedid, $type);
          //$res = Memberinvestmentspayments::create($paymentData);
          $amountArray = array('1' => $request->input('amount'));
          $amount_deposit_by_name = $request->input('firstname') . ' ' . $request->input('lastname');
          if ($request->input('payment-mode') == 3) {
            //  $savingTransaction = $this->savingAccountTransactionData($request->all(),$insertedid,$type);
            $savingTransaction = $this->savingAccountTransactionDataNew($request->all(), $insertedid, $type, 'SFD', $investmentAccount);
            $res = SavingAccountTranscation::create($savingTransaction);
            $satRefId = NULL;
            $sAccountId = $ssbId;
            $sAccountAmount = $ssbBalance - $request->input('amount');
            $sResult = SavingAccount::find($sAccountId);
            $sData['balance'] = $sAccountAmount;
            $sResult->update($sData);
            $ssbCreateTran = NULL;
            $sAccountNumber = $ssbAccountNumber;
          } else {
            $sAccountNumber = NULL;
            $satRefId = NULL;
            $ssbCreateTran = NULL;
          }
          //$createDayBook = CommanController::createDayBook($ssbCreateTran,$satRefId,2,$insertedid,$request['associatemid'],$request['memberAutoId'],$request['amount'],$request['amount'],$withdrawal=0,$description,$sAccountNumber,$branch_id,$branchCode,$amountArray,$request['payment-mode'],$amount_deposit_by_name,$request['memberAutoId'],$investmentAccount,$request['cheque-number'],$request['bank-name'],$request['branch-name'],date("Y-m-d ", strtotime($request['created_at'])),$request['transaction-id'],$online_payment_by=NULL,$ssbId,'CR');
          // ---------------------------  Day book modify --------------------------
          $ppmode = 0;
          if ($request->input('payment-mode') == 1) { //cheque
            $ppmode = 1;
          }
          if ($request->input('payment-mode') == 2) { //onine
            $ppmode = 3;
          }
          if ($request->input('payment-mode') == 3) {
            //ssb
            $ppmode = 4;
          }
          $createDayBook = CommanController::createDayBookNew(NULL, NULL, 2, $insertedid, $request['associatemid'], $request['memberAutoId'], $request['amount'], $request['amount'], $withdrawal = 0, $description, $ssbAccountNumber, $branch_id, $branchCode, $amountArray, $ppmode, $amount_deposit_by_name, $request['memberAutoId'], $investmentAccount, $request['cheque-number'], $request['bank-name'], $request['branch-name'], $pdate, $online_transction_no, $online_payment_by = NULL, $ssbId, 'CR', $received_cheque_id, $cheque_deposit_bank_id, $cheque_deposit_bank_ac_id, $online_deposit_bank_id, $online_deposit_bank_ac_id, $request->company_id);
          // ---------------------------  HEAD IMPLEMENT --------------------------
          $this->investHeadCreate($request['amount'], $globaldate, $insertedid, $planId, $cheque_no, $cheque_id, $cheque_date, $online_transction_no, $online_transction_date, $online_deposit_bank_id, $online_deposit_bank_ac_id, $branch_id, $request['associatemid'], $request['memberAutoId'], $ssbId, $createDayBook, $request->input('payment-mode'), $investmentDetail->account_number, $companyId);
          //--------------------------------------------------------------------------
          /* ------------------ commission genarate-----------------*/
          // $commission =CommanController:: commissionDistributeInvestment($request['associatemid'],$insertedid,3,$request['amount'],1,$planId,$branch_id,$request['tenure'],$createDayBook);
          // $commission_collection =CommanController::commissionCollectionInvestment($request['associatemid'],$insertedid,5,$request['amount'],1,$planId,$branch_id,$request['tenure'],$createDayBook);
          /*----- ------  credit business start ---- ---------------*/
          $creditBusiness = CommanController::associateCreditBusiness($request['associatemid'], $insertedid, 1, $request['amount'], 1, $planId, $request['tenure'], $createDayBook);
          /*----- ------  credit business end ---- ---------------*/
          /* ------------------ commission genarate-----------------*/
          $transaction = $this->transactionData($satRefId, $request->all(), $insertedid, $type, $ssbCreateTran);
          $res = Investmentplantransactions::create($transaction);
          //  $paymentData = $this->getPaymentMethodData($request->all(),$insertedid,$type);
          //  //$res = Memberinvestmentspayments::create($paymentData);
          /*--------------------- received cheque payment -----------------------*/
          if ($request->input('payment-mode') == 1) {
            $receivedPayment['type'] = 2;
            $receivedPayment['branch_id'] = $branch_id;
            $receivedPayment['investment_id'] = $insertedid;
            $receivedPayment['day_book_id'] = $createDayBook;
            $receivedPayment['cheque_id'] = $request['cheque_id'];
            $receivedPayment['created_at'] = $request['created_at'];
            $receivedCreate = \App\Models\ReceivedChequePayment::create($receivedPayment);
            $dataRC['status'] = 3;
            $receivedcheque = \App\Models\ReceivedCheque::find($request['cheque_id']);
            $receivedcheque->update($dataRC);
          }
          /*--------------------- received cheque payment -----------------------*/
          if ($request['stationary_charge'] > 0) {
            $this->stationaryCharges($insertedid);
          }
          $text = 'Dear Member, Thank You for Choosing S.B. Micro Finance association Your SFD A/c No.' . $investmentDetail->account_number . ' is Credited on '
            . $investmentDetail->created_at->format('d M Y') . ' With Rs. ' . round($investmentDetail->deposite_amount, 2) . 'CR. Thanks Have a good day';
          $temaplteId = 1207161519023692218;
          break;
        case "recurring-deposit":
          $description = 'SRD Account opening';
          $res = Memberinvestments::create($data);
          $insertedid = $res->id;
          $investmentDetail = Memberinvestments::find($res->id);
          $fNominee = $this->getFirstNomineeData($request->all(), $insertedid, $type);
          $res = Memberinvestmentsnominees::create($fNominee);
          if ($request['second_nominee_add'] == 1) {
            $sNominee = $this->getSecondNomineeData($request->all(), $insertedid, $type);
            $res = Memberinvestmentsnominees::create($sNominee);
          }
          $paymentData = $this->getPaymentMethodData($request->all(), $insertedid, $type);
          ////$res = Memberinvestmentspayments::create($paymentData);
          $amountArray = array('1' => $request->input('amount'));
          $amount_deposit_by_name = $request->input('firstname') . ' ' . $request->input('lastname');
          if ($request->input('payment-mode') == 3) {
            //$savingTransaction = $this->savingAccountTransactionData($request->all(),$insertedid,$type);
            $savingTransaction = $this->savingAccountTransactionDataNew($request->all(), $insertedid, $type, 'SRD', $investmentAccount);
            $res = SavingAccountTranscation::create($savingTransaction);
            $satRefId = NULL;
            $sAccountId = $ssbId;
            $sAccountAmount = $ssbBalance - $request->input('amount');
            $sResult = SavingAccount::find($sAccountId);
            $sData['balance'] = $sAccountAmount;
            $sResult->update($sData);
            $ssbCreateTran = NULL;
            $sAccountNumber = $ssbAccountNumber;
          } else {
            $sAccountNumber = NULL;
            $satRefId = NULL;
            $ssbCreateTran = NULL;
          }
          //$createDayBook = CommanController::createDayBook($ssbCreateTran,$satRefId,2,$insertedid,$request['associatemid'],$request['memberAutoId'],$request['amount'],$request['amount'],$withdrawal=0,$description,$sAccountNumber,$branch_id,$branchCode,$amountArray,$request['payment-mode'],$amount_deposit_by_name,$request['memberAutoId'],$investmentAccount,$request['cheque-number'],$request['bank-name'],$request['branch-name'],date("Y-m-d ", strtotime($request['created_at'])),$request['transaction-id'],$online_payment_by=NULL,$ssbId,'CR');
          // ---------------------------  Day book modify --------------------------
          $ppmode = 0;
          if ($request->input('payment-mode') == 1) { //cheque
            $ppmode = 1;
          }
          if ($request->input('payment-mode') == 2) { //onine
            $ppmode = 3;
          }
          if ($request->input('payment-mode') == 3) {
            //ssb
            $ppmode = 4;
          }
          $createDayBook = CommanController::createDayBookNew(NULL, NULL, 2, $insertedid, $request['associatemid'], $request['memberAutoId'], $request['amount'], $request['amount'], $withdrawal = 0, $description, $ssbAccountNumber, $branch_id, $branchCode, $amountArray, $ppmode, $amount_deposit_by_name, $request['memberAutoId'], $investmentAccount, $request['cheque-number'], $request['bank-name'], $request['branch-name'], $pdate, $online_transction_no, $online_payment_by = NULL, $ssbId, 'CR', $received_cheque_id, $cheque_deposit_bank_id, $cheque_deposit_bank_ac_id, $online_deposit_bank_id, $online_deposit_bank_ac_id, $request->company_id);
          // ---------------------------  HEAD IMPLEMENT --------------------------
          $this->investHeadCreate($request['amount'], $globaldate, $insertedid, $planId, $cheque_no, $cheque_id, $cheque_date, $online_transction_no, $online_transction_date, $online_deposit_bank_id, $online_deposit_bank_ac_id, $branch_id, $request['associatemid'], $request['memberAutoId'], $ssbId, $createDayBook, $request->input('payment-mode'), $investmentDetail->account_number, $companyId);
          //--------------------------------------------------------------------------
          /* ------------------ commission genarate-----------------*/
          //   $commission =CommanController:: commissionDistributeInvestment($request['associatemid'],$insertedid,3,$request['amount'],1,$planId,$branch_id,$request['tenure'],$createDayBook);
          //  $commission_collection =CommanController::commissionCollectionInvestment($request['associatemid'],$insertedid,5,$request['amount'],1,$planId,$branch_id,$request['tenure'],$createDayBook);
          /*----- ------  credit business start ---- ---------------*/
          $creditBusiness = CommanController::associateCreditBusiness($request['associatemid'], $insertedid, 1, $request['amount'], 1, $planId, $request['tenure'], $createDayBook);
          /*----- ------  credit business end ---- ---------------*/
          /* ------------------ commission genarate-----------------*/
          $transaction = $this->transactionData($satRefId, $request->all(), $insertedid, $type, $ssbCreateTran);
          $res = Investmentplantransactions::create($transaction);
          //  $paymentData = $this->getPaymentMethodData($request->all(),$insertedid,$type);
          //  //$res = Memberinvestmentspayments::create($paymentData);
          /*--------------------- received cheque payment -----------------------*/
          if ($request->input('payment-mode') == 1) {
            $receivedPayment['type'] = 2;
            $receivedPayment['branch_id'] = $branch_id;
            $receivedPayment['investment_id'] = $insertedid;
            $receivedPayment['day_book_id'] = $createDayBook;
            $receivedPayment['cheque_id'] = $request['cheque_id'];
            $receivedPayment['created_at'] = $request['created_at'];
            $receivedCreate = \App\Models\ReceivedChequePayment::create($receivedPayment);
            $dataRC['status'] = 3;
            $receivedcheque = \App\Models\ReceivedCheque::find($request['cheque_id']);
            $receivedcheque->update($dataRC);
          }
          /*--------------------- received cheque payment -----------------------*/
          if ($request['stationary_charge'] > 0) {
            $this->stationaryCharges($insertedid);
          }
          $text = 'Dear Member, Thank You for Choosing S.B. Micro Finance association Your SRD A/c No.' . $investmentDetail->account_number . ' is Credited on '
            . $investmentDetail->created_at->format('d M Y') . ' With Rs. ' . round($investmentDetail->deposite_amount, 2) . 'CR. Thanks Have a good day';
          $temaplteId = 1207161519023692218;
          break;
        case "saving-account-child":
          //dd($request->all());
          $description = 'Saving Account Child opening';
          $res = Memberinvestments::create($data);
          $insertedid = $res->id;
          $investmentDetail = Memberinvestments::find($res->id);
          $fNominee = $this->getFirstNomineeData($request->all(), $insertedid, $type);
          $res = Memberinvestmentsnominees::create($fNominee);
          if ($request['second_nominee_add'] == 1) {
            $sNominee = $this->getSecondNomineeData($request->all(), $insertedid, $type);
            $res = Memberinvestmentsnominees::create($sNominee);
          }
          $paymentData = $this->getPaymentMethodData($request->all(), $insertedid, $type);
          ////$res = Memberinvestmentspayments::create($paymentData);
          $amountArray = array('1' => $request->input('amount'));
          $amount_deposit_by_name = $request->input('firstname') . ' ' . $request->input('lastname');
          if ($request->input('payment-mode') == 3) {
            //$savingTransaction = $this->savingAccountTransactionData($request->all(),$insertedid,$type);
            $savingTransaction = $this->savingAccountTransactionDataNew($request->all(), $insertedid, $type, 'SRD', $investmentAccount);
            $res = SavingAccountTranscation::create($savingTransaction);
            $satRefId = NULL;
            $sAccountId = $ssbId;
            $sAccountAmount = $ssbBalance - $request->input('amount');
            $sResult = SavingAccount::find($sAccountId);
            $sData['balance'] = $sAccountAmount;
            $sResult->update($sData);
            $ssbCreateTran = NULL;
            $sAccountNumber = $ssbAccountNumber;
          } else {
            $sAccountNumber = NULL;
            $satRefId = NULL;
            $ssbCreateTran = NULL;
          }
          //$createDayBook = CommanController::createDayBook($ssbCreateTran,$satRefId,2,$insertedid,$request['associatemid'],$request['memberAutoId'],$request['amount'],$request['amount'],$withdrawal=0,$description,$sAccountNumber,$branch_id,$branchCode,$amountArray,$request['payment-mode'],$amount_deposit_by_name,$request['memberAutoId'],$investmentAccount,$request['cheque-number'],$request['bank-name'],$request['branch-name'],date("Y-m-d ", strtotime($request['created_at'])),$request['transaction-id'],$online_payment_by=NULL,$ssbId,'CR');
          // ---------------------------  Day book modify --------------------------
          $ppmode = 0;
          if ($request->input('payment-mode') == 1) { //cheque
            $ppmode = 1;
          }
          if ($request->input('payment-mode') == 2) { //onine
            $ppmode = 3;
          }
          if ($request->input('payment-mode') == 3) {
            //ssb
            $ppmode = 4;
          }
          $createDayBook = CommanController::createDayBookNew(NULL, NULL, 2, $insertedid, $request['associatemid'], $request['memberAutoId'], $request['amount'], $request['amount'], $withdrawal = 0, $description, $ssbAccountNumber, $branch_id, $branchCode, $amountArray, $ppmode, $amount_deposit_by_name, $request['memberAutoId'], $investmentAccount, $request['cheque-number'], $request['bank-name'], $request['branch-name'], $pdate, $online_transction_no, $online_payment_by = NULL, $ssbId, 'CR', $received_cheque_id, $cheque_deposit_bank_id, $cheque_deposit_bank_ac_id, $online_deposit_bank_id, $online_deposit_bank_ac_id, $request->company_id);
          // ---------------------------  HEAD IMPLEMENT --------------------------
          $this->investHeadCreate($request['amount'], $globaldate, $insertedid, $planId, $cheque_no, $cheque_id, $cheque_date, $online_transction_no, $online_transction_date, $online_deposit_bank_id, $online_deposit_bank_ac_id, $branch_id, $request['associatemid'], $request['memberAutoId'], $ssbId, $createDayBook, $request->input('payment-mode'), $investmentDetail->account_number, $companyId);
          //--------------------------------------------------------------------------
          /* ------------------ commission genarate-----------------*/
          //   $commission =CommanController:: commissionDistributeInvestment($request['associatemid'],$insertedid,3,$request['amount'],1,$planId,$branch_id,$request['tenure'],$createDayBook);
          //  $commission_collection =CommanController::commissionCollectionInvestment($request['associatemid'],$insertedid,5,$request['amount'],1,$planId,$branch_id,$request['tenure'],$createDayBook);
          /*----- ------  credit business start ---- ---------------*/
          $creditBusiness = CommanController::associateCreditBusiness($request['associatemid'], $insertedid, 1, $request['amount'], 1, $planId, $request['tenure'], $createDayBook);
          /*----- ------  credit business end ---- ---------------*/
          /* ------------------ commission genarate-----------------*/
          $transaction = $this->transactionData($satRefId, $request->all(), $insertedid, $type, $ssbCreateTran);
          $res = Investmentplantransactions::create($transaction);
          //  $paymentData = $this->getPaymentMethodData($request->all(),$insertedid,$type);
          //  //$res = Memberinvestmentspayments::create($paymentData);
          /*--------------------- received cheque payment -----------------------*/
          if ($request->input('payment-mode') == 1) {
            $receivedPayment['type'] = 2;
            $receivedPayment['branch_id'] = $branch_id;
            $receivedPayment['investment_id'] = $insertedid;
            $receivedPayment['day_book_id'] = $createDayBook;
            $receivedPayment['cheque_id'] = $request['cheque_id'];
            $receivedPayment['created_at'] = $request['created_at'];
            $receivedCreate = \App\Models\ReceivedChequePayment::create($receivedPayment);
            $dataRC['status'] = 3;
            $receivedcheque = \App\Models\ReceivedCheque::find($request['cheque_id']);
            $receivedcheque->update($dataRC);
          }
          /*--------------------- received cheque payment -----------------------*/
          if ($request['stationary_charge'] > 0) {
            $this->stationaryCharges($insertedid);
          }
          $text = 'Dear Member, Thank You for Choosing S.B. Micro Finance association Your SSB Child A/c No.' . $investmentDetail->account_number . ' is Credited on '
            . $investmentDetail->created_at->format('d M Y') . ' With Rs. ' . round($investmentDetail->deposite_amount, 2) . 'CR. Thanks Have a good day';
          $temaplteId = 1207161519023692218;
          break;
        case "samraddh-bhavhishya":
          $description = 'SB Account opening';
          $res = Memberinvestments::create($data);
          $insertedid = $res->id;
          $investmentDetail = Memberinvestments::find($res->id);
          $fNominee = $this->getFirstNomineeData($request->all(), $insertedid, $type);
          $res = Memberinvestmentsnominees::create($fNominee);
          if ($request['second_nominee_add'] == 1) {
            $sNominee = $this->getSecondNomineeData($request->all(), $insertedid, $type);
            $res = Memberinvestmentsnominees::create($sNominee);
          }
          $paymentData = $this->getPaymentMethodData($request->all(), $insertedid, $type);
          //$res = Memberinvestmentspayments::create($paymentData);
          $amountArray = array('1' => $request->input('amount'));
          $amount_deposit_by_name = $request->input('firstname') . ' ' . $request->input('lastname');
          if ($request->input('payment-mode') == 3) {
            // $savingTransaction = $this->savingAccountTransactionData($request->all(),$insertedid,$type);
            $savingTransaction = $this->savingAccountTransactionDataNew($request->all(), $insertedid, $type, 'SB', $investmentAccount);
            $res = SavingAccountTranscation::create($savingTransaction);
            $satRefId = NULL;
            $sAccountId = $ssbId;
            $sAccountAmount = $ssbBalance - $request->input('amount');
            $sResult = SavingAccount::find($sAccountId);
            $sData['balance'] = $sAccountAmount;
            $sResult->update($sData);
            $ssbCreateTran = NULL;
            $sAccountNumber = $ssbAccountNumber;
          } else {
            $sAccountNumber = NULL;
            $satRefId = NULL;
            $ssbCreateTran = NULL;
          }
          // $createDayBook = CommanController::createDayBook($ssbCreateTran,$satRefId,2,$insertedid,$request['associatemid'],$request['memberAutoId'],$request['amount'],$request['amount'],$withdrawal=0,$description,$sAccountNumber,$branch_id,$branchCode,$amountArray,$request['payment-mode'],$amount_deposit_by_name,$request['memberAutoId'],$investmentAccount,$request['cheque-number'],$request['bank-name'],$request['branch-name'],date("Y-m-d ", strtotime($request['created_at'])),$request['transaction-id'],$online_payment_by=NULL,$ssbId,'CR');
          // ---------------------------  Day book modify --------------------------
          $ppmode = 0;
          if ($request->input('payment-mode') == 1) { //cheque
            $ppmode = 1;
          }
          if ($request->input('payment-mode') == 2) { //onine
            $ppmode = 3;
          }
          if ($request->input('payment-mode') == 3) {
            //ssb
            $ppmode = 4;
          }
          $createDayBook = CommanController::createDayBookNew(NULL, NULL, 2, $insertedid, $request['associatemid'], $request['memberAutoId'], $request['amount'], $request['amount'], $withdrawal = 0, $description, $ssbAccountNumber, $branch_id, $branchCode, $amountArray, $ppmode, $amount_deposit_by_name, $request['memberAutoId'], $investmentAccount, $request['cheque-number'], $request['bank-name'], $request['branch-name'], $pdate, $online_transction_no, $online_payment_by = NULL, $ssbId, 'CR', $received_cheque_id, $cheque_deposit_bank_id, $cheque_deposit_bank_ac_id, $online_deposit_bank_id, $online_deposit_bank_ac_id, $request->company_id);
          // ---------------------------  HEAD IMPLEMENT --------------------------
          $this->investHeadCreate($request['amount'], $globaldate, $insertedid, $planId, $cheque_no, $cheque_id, $cheque_date, $online_transction_no, $online_transction_date, $online_deposit_bank_id, $online_deposit_bank_ac_id, $branch_id, $request['associatemid'], $request['memberAutoId'], $ssbId, $createDayBook, $request->input('payment-mode'), $investmentDetail->account_number, $companyId);
          //--------------------------------------------------------------------------
          /*----- ------  credit business start ---- ---------------*/
          $creditBusiness = CommanController::associateCreditBusiness($request['associatemid'], $insertedid, 1, $request['amount'], 1, $planId, $request['tenure'], $createDayBook);
          /*----- ------  credit business end ---- ---------------*/
          /* ------------------ commission genarate-----------------*/
          // $commission =CommanController:: commissionDistributeInvestment($request['associatemid'],$insertedid,3,$request['amount'],1,$planId,$branch_id,$request['tenure'],$createDayBook);
          // $commission_collection =CommanController::commissionCollectionInvestment($request['associatemid'],$insertedid,5,$request['amount'],1,$planId,$branch_id,$request['tenure'],$createDayBook);
          /* ------------------ commission genarate-----------------*/
          $transaction = $this->transactionData($satRefId, $request->all(), $insertedid, $type, $ssbCreateTran);
          $res = Investmentplantransactions::create($transaction);
          // $paymentData = $this->getPaymentMethodData($request->all(),$insertedid,$type);
          //  //$res = Memberinvestmentspayments::create($paymentData);
          /*--------------------- received cheque payment -----------------------*/
          if ($request->input('payment-mode') == 1) {
            $receivedPayment['type'] = 2;
            $receivedPayment['branch_id'] = $branch_id;
            $receivedPayment['investment_id'] = $insertedid;
            $receivedPayment['day_book_id'] = $createDayBook;
            $receivedPayment['cheque_id'] = $request['cheque_id'];
            $receivedPayment['created_at'] = $request['created_at'];
            $receivedCreate = \App\Models\ReceivedChequePayment::create($receivedPayment);
            $dataRC['status'] = 3;
            $receivedcheque = \App\Models\ReceivedCheque::find($request['cheque_id']);
            $receivedcheque->update($dataRC);
          }
          /*--------------------- received cheque payment -----------------------*/
          if ($request['stationary_charge'] > 0) {
            $this->stationaryCharges($insertedid);
          }
          $text = 'Dear Member, Thank You for Choosing S.B. Micro Finance association Your S. Bhavhishya A/c No.' . $investmentDetail->account_number . ' is Credited on '
            . $investmentDetail->created_at->format('d M Y') . ' With Rs. ' . round($investmentDetail->deposite_amount, 2) . 'CR. Thanks Have a good day';
          $temaplteId = 1207161519023692218;
          break;
      }
      DB::commit();
    } catch (\Exception $ex) {
      DB::rollback();
      return back()->with('alert', $ex->getMessage());
    }
    $contactNumber = array();
    $memberDetail = Member::find($request['memberAutoId']);
    $contactNumber[] = $memberDetail->mobile_no;
    $sendToMember = new Sms();
    $sendToMember->sendSms($contactNumber, $text, $temaplteId);
    if ($res) {
      // dd to get member investment last inserted record to register investment collector
      $plantypeid = $request['investmentplan'];
      if ($plantypeid == 1) {
        $collector_type = 'investmentsavingcollector';
        $typeid = $savingAccountDetail->id;
      } else {
        $collector_type = 'investmentcollector';
        $typeid = $res->investment_id;
      }
      $associateid = $request['associatemid'];
      CollectorAccountStoreLI($collector_type, $typeid, $associateid, $globaldate);
      // Register Collector On Register of new Investment and Make an Entry in New Table Collector Account End
      //redirect()->route('investment/recipt/'.$insertedid);
      return redirect('admin/investment/recipt/' . $insertedid);
      return back()->with('success', 'Saved Successfully!');
    } else {
      return back()->with('alert', 'Problem With Register New Plan');
    }
  }
  /**
   * Store a newly created plan in storage.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\Response
   */
  public function openSavingAccount(Request $request)
  {
    DB::beginTransaction();
    try {
      Session::put('created_at', $request['created_at']);
      $request['memberAutoId'] = $request['saving_account_m_id'];
      $request['investmentplan'] = 1;
      $request['amount'] = $request['ssbamount'];
      $request['plan_type'] = $request['account_box_class'];
      $request['payment-mode'] = 0;
      $request['associatemid'] = $request['saving_account_a_id'];
      //echo "<pre>"; print_r($request->all()); die;
      $branch_id = $request['branchid'];
      $getBranchCode = getBranchCode($branch_id);
      $branchCode = $getBranchCode->branch_code;
      $faCode = getPlanCode(1);
      $planId = 1;
      $investmentMiCode = getInvesmentMiCodeNew($planId, $branch_id);
      if (!empty($investmentMiCode)) {
        $miCodeAdd = $investmentMiCode->mi_code + 1;
      } else {
        $miCodeAdd = 1;
      }
      $miCode = str_pad($miCodeAdd, 5, '0', STR_PAD_LEFT);
      // Invesment Account no
      $investmentAccount = $branchCode . $faCode . $miCode;
      /*if($request['hidden_primary_account']==1){
      $is_primary = $request['primary_account'];
      }else{
      $is_primary = 0;
      } */
      $is_primary = 0;
      $ssbdata['mi_code'] = $miCode;
      $ssbdata['account_number'] = $investmentAccount;
      $ssbdata['ssb_account_number'] = $investmentAccount;
      $ssbdata['plan_id'] = 1;
      $ssbdata['form_number'] = $request['f_number'];
      $ssbdata['member_id'] = $request['saving_account_m_id'];
      $ssbdata['associate_id'] = $request['saving_account_a_id'];
      $ssbdata['branch_id'] = $branch_id;
      $ssbdata['old_branch_id'] = $branch_id;
      $ssbdata['deposite_amount'] = $request['ssbamount'];
      $ssbdata['created_at'] = $request['created_at'];
      $res = Memberinvestments::create($ssbdata);
      $investmentId = $res->id;
      $savingAccountId = $res->account_number;
      if ($request['current_plan_id'] == 3) {
        $description = 'SMB Account opening';
      } elseif ($request['current_plan_id'] == 6) {
        $description = 'SJ Account opening';
      } elseif ($request['current_plan_id'] == 8) {
        $description = 'MIS Account opening';
      } else {
        $description = '';
      }
      /*$createAccount = CommanController::createSavingAccount($request['saving_account_m_id'],$branch_id,$branchCode,$request['ssbamount'],0,$investmentId,$miCode,$investmentAccount,$is_primary,$faCode);*/
      $createAccount = CommanController::createSavingAccountDescriptionModify($request['saving_account_m_id'], $branch_id, $branchCode, $request['ssbamount'], 0, $investmentId, $miCode, $investmentAccount, $is_primary, $faCode, 'SSB Account opening', $request['saving_account_a_id']);
      $mRes = Member::find($request['saving_account_m_id']);
      $mData['ssb_account'] = $investmentAccount;
      $mRes->update($mData);
      $satRefId = NULL;
      $ssbAccountId = $createAccount['ssb_id'];
      $amountArraySsb = array('1' => $request['ssbamount']);
      $amount_deposit_by_name = $request['saving_account_m_name'];
      $ssbCreateTran = NULL;
      $sAccount = $this->getSavingAccountDetails($request->input('saving_account_m_id'));
      if (count($sAccount) > 0) {
        $ssbAccountNumber = $sAccount[0]->account_no;
        $ssbId = $sAccount[0]->id;
      } else {
        $ssbAccountNumber = '';
        $ssbId = '';
      }
      $createDayBook = CommanController::createDayBook($ssbCreateTran, $satRefId, 2, $investmentId, $request['associatemid'], $request['saving_account_m_id'], $request['ssbamount'], $request['ssbamount'], $withdrawal = 0, $description, $ssbAccountNumber, $branch_id, $branchCode, $amountArraySsb, 0, $amount_deposit_by_name, $request['saving_account_m_id'], $investmentAccount, 0, NULL, NULL, date('Y-m-d'), NULL, $online_payment_by = NULL, $ssbId, 'CR');
      // ---------------------------  HEAD IMPLEMENT --------------------------
      $this->investHeadCreateSSB($request['ssbamount'], $request['created_at'], $ssbAccountId, $planId, NULL, NULL, NULL, NULL, NULL, NULL, NULL, $branch_id, $request['associatemid'], $request['saving_account_m_id'], $ssbId, $createAccount['ssb_transaction_id'], 0, $investmentAccount, $companyId);
      //--------------------------------HEAD IMPLEMENT  -------------------------
      $type = 'create';
      $transaction = $this->transactionData($satRefId, $request->all(), $investmentId, $type, $ssbCreateTran);
      $res = Investmentplantransactions::create($transaction);
      $ssbfndata['investment_id'] = $investmentId;
      $ssbfndata['nominee_type'] = 0;
      $ssbfndata['name'] = $request['ssb_fn_first_name'];
      //$ssbfndata['second_name'] = $request['ssb_fn_second_name'];
      $ssbfndata['relation'] = $request['ssb_fn_relationship'];
      $ssbfndata['gender'] = $request['ssb_fn_gender'];
      $ssbfndata['dob'] = date("Y-m-d", strtotime(str_replace('/', '-', $request['ssb_fn_dob'])));
      $ssbfndata['age'] = $request['ssb_fn_age'];
      $ssbfndata['percentage'] = $request['ssb_fn_percentage'];
      $res = Memberinvestmentsnominees::create($ssbfndata);
      if ($request['sa_second_nominee_add'] == 1) {
        $ssbsndata['investment_id'] = $investmentId;
        $ssbsndata['nominee_type'] = 1;
        $ssbsndata['name'] = $request['ssb_sn_first_name'];
        //$ssbsndata['second_name'] = $request['ssb_sn_second_name'];
        $ssbsndata['relation'] = $request['ssb_sn_relationship'];
        $ssbsndata['gender'] = $request['ssb_sn_gender'];
        $ssbsndata['dob'] = date("Y-m-d", strtotime(str_replace('/', '-', $request['ssb_sn_dob'])));
        $ssbsndata['age'] = $request['ssb_sn_age'];
        $ssbsndata['percentage'] = $request['ssb_sn_percentage'];
        $msNominee = Memberinvestmentsnominees::create($ssbsndata);
      }
      $nomineeForm = $request['nominee_form_class'];
      $accountInput = $request['account_box_class'];
      $savingAccountDetail = SavingAccount::where('account_no', $savingAccountId)->first();
      //$text = 'Dear Member, Thank You for Choosing S.B. Micro Finance association Your saving A/C'. $savingAccountDetail->account_no.' is Created on '
      //.$savingAccountDetail->created_at->format('d M Y').' With Rs. '. round($request['ssbamount'],2).' Cur Bal: '. round($savingAccountDetail->balance, 2).'. Thanks Have a good day';
      /*$text = 'Dear Member, Thank You for Choosing S.B. Micro Finance association Your saving A/c No.'. $savingAccountDetail->account_no.' is Credited on '
      .$savingAccountDetail->created_at->format('d M Y').' With Rs. '. round($request['ssbamount'],2).'. Thanks Have a good day';
      $temaplteId = 1207161519023692218;
      $contactNumber = array();
      $memberDetail = Member::find($request['memberAutoId']);
      $contactNumber[] = $memberDetail->mobile_no;
      $sendToMember = new Sms();
      $sendToMember->sendSms( $contactNumber, $text, $temaplteId);*/
      DB::commit();
    } catch (\Exception $ex) {
      DB::rollback();
      return back()->with('alert', $ex->getMessage());
    }
    if ($investmentId) {
      return Response::json(['msg_type' => 'success', 'investmentAccount' => $investmentAccount, 'nomineeForm' => $nomineeForm, 'accountInput' => $accountInput]);
    } else if (count($record) > 0) {
      return Response::json(['view' => 'Account Already Created!', 'msg_type' => 'exists']);
    } else {
      return Response::json(['view' => 'Somthing went wrong!', 'msg_type' => 'error']);
    }
  }
  /**
   * Display created investment by id.
   *
   * @param  $id
   * @return \Illuminate\Http\Response
   */

  /**
   * Get plan form in edit by plan name.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\Response
   */
  public function editPlanForm(Request $request)
  {
    $plan = $request->formName;
    $id = $request->investmentId;
    $data['action'] = $request->viewEditAction;
    $data['investments'] = Memberinvestments::with('investmentNomiees')->findOrFail($id);
    $data['relations'] = Relations::all();
    $data['plans_tenure'] = $this->repository->getAllPlans()->has('PlanTenures')->with('PlanTenures:roi,id,plan_id,tenure')->where('status', '1')->where('death_help', '1')->where('loan_against_deposit', '1')->where('prematurity', '1')->first(['id', 'death_help', 'loan_against_deposit', 'prematurity']);
    return view('templates.admin.investment_management.' . $plan . '.edit-' . $plan . '', $data);
  }
  /**
   * Update the specified plan.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\Response
   */
  public function Update(Request $request)
  {
    // dd($request->all());
    $plantype = $request->input('plan_type');
    switch ($plantype) {
      case "saving-account":
        $rules = [
          'amount' => 'required',
          'investmentplan' => 'required',
          'form_number' => 'required',
          //'memberid' => 'required',
          'fn_first_name' => 'required',
          'fn_relationship' => 'required',
          'fn_gender' => 'required',
          'fn_dob' => 'required',
          'fn_percentage' => 'required',
        ];
        break;
      case "saving-account-child":
        $rules = [
          'investmentplan' => 'required',
          'form_number' => 'required',
          //'memberid' => 'required',
          'fn_first_name' => 'required',
          'fn_relationship' => 'required',
          'fn_gender' => 'required',
          'fn_dob' => 'required',
          'fn_percentage' => 'required',
        ];
        // $request->request->add(['amount' => 0]);
        // $request->request->add(['transaction-id' => 0]);
        // $request->request->add(['date' => date("Y-m-d")]);
        break;
      case "samraddh-kanyadhan-yojana":
        $rules = [
          'form_number' => 'required',
          'amount' => 'required',
          'payment-mode' => 'required',
        ];
        break;
      case "special-samraddh-money-back":
        $rules = [
          'form_number' => 'required',
          'amount' => 'required',
          'ssbacount' => 'required',
          'payment-mode' => 'required',
          'investmentplan' => 'required',
          //'memberid' => 'required',
          'fn_first_name' => 'required',
          'fn_relationship' => 'required',
          'fn_gender' => 'required',
          'fn_dob' => 'required',
          'fn_percentage' => 'required',
        ];
        break;
      case "flexi-fixed-deposit":
        $rules = [
          'form_number' => 'required',
          'amount' => 'required',
          'payment-mode' => 'required',
          'investmentplan' => 'required',
          //'memberid' => 'required',
          'fn_first_name' => 'required',
          'fn_relationship' => 'required',
          'fn_gender' => 'required',
          'fn_dob' => 'required',
          'fn_age' => 'required',
          'fn_percentage' => 'required',
        ];
        break;
      case "fixed-recurring-deposit":
        $rules = [
          'form_number' => 'required',
          'amount' => 'required',
          'payment-mode' => 'required',
          'investmentplan' => 'required',
          //'memberid' => 'required',
          'fn_first_name' => 'required',
          'fn_relationship' => 'required',
          'fn_gender' => 'required',
          'fn_dob' => 'required',
          'fn_percentage' => 'required',
        ];
        break;
      case "samraddh-jeevan":
        $rules = [
          'form_number' => 'required',
          'amount' => 'required',
          'ssbacount' => 'required',
          'payment-mode' => 'required',
          'investmentplan' => 'required',
          //'memberid' => 'required',
          'fn_first_name' => 'required',
          'fn_relationship' => 'required',
          'fn_gender' => 'required',
          'fn_dob' => 'required',
          'fn_percentage' => 'required',
        ];
        break;
      case "daily-deposit":
        $rules = [
          'form_number' => 'required',
          'amount' => 'required',
          'payment-mode' => 'required',
          'investmentplan' => 'required',
          //'memberid' => 'required',
          'fn_first_name' => 'required',
          'fn_relationship' => 'required',
          'fn_gender' => 'required',
          'fn_dob' => 'required',
          'fn_percentage' => 'required',
        ];
        break;
      case "monthly-income-scheme":
        $rules = [
          'form_number' => 'required',
          'amount' => 'required',
          'ssbacount' => 'required',
          'payment-mode' => 'required',
          'investmentplan' => 'required',
          //'memberid' => 'required',
          'fn_first_name' => 'required',
          'fn_relationship' => 'required',
          'fn_gender' => 'required',
          'fn_dob' => 'required',
          'fn_percentage' => 'required',
        ];
        break;
      case "fixed-deposit":
        $rules = [
          'form_number' => 'required',
          'amount' => 'required',
          'payment-mode' => 'required',
          'tenure' => 'required',
          'investmentplan' => 'required',
          //'memberid' => 'required',
          'fn_first_name' => 'required',
          'fn_relationship' => 'required',
          'fn_gender' => 'required',
          'fn_dob' => 'required',
          'fn_percentage' => 'required',
        ];
        break;
      case "recurring-deposit":
        $rules = [
          'form_number' => 'required',
          'amount' => 'required',
          'payment-mode' => 'required',
          'tenure' => 'required',
          'investmentplan' => 'required',
          //'memberid' => 'required',
          'fn_first_name' => 'required',
          'fn_relationship' => 'required',
          'fn_gender' => 'required',
          'fn_dob' => 'required',
          'fn_percentage' => 'required',
        ];
        break;
      case "samraddh-bhavhishya":
        $rules = [
          'form_number' => 'required',
          'amount' => 'required',
          'payment-mode' => 'required',
          'investmentplan' => 'required',
          //'memberid' => 'required',
          'fn_first_name' => 'required',
          'fn_relationship' => 'required',
          'fn_gender' => 'required',
          'fn_dob' => 'required',
          'fn_percentage' => 'required',
        ];
        break;
    }
    $customMessages = [
      'fn_first_name.required' => 'The first name field is required.',
      'required' => 'The :attribute field is required.'
    ];
    $this->validate($request, $rules, $customMessages);
    DB::beginTransaction();
    try {
      $investmentId = $request->input('investmentId');
      $investmentPMode = Memberinvestments::select('payment_mode')->where('id', $investmentId)->first();
      $payment_modeget = $investmentPMode->payment_mode;
      if ($request->input('payment-mode') == 1 && $investmentPMode->payment_mode != 1) {
        $getChequeDetail = \App\Models\ReceivedCheque::where('id', $request['cheque_id'])->where('status', 3)->first(['id', 'amount', 'status']);
        if (!empty($getChequeDetail)) {
          return back()->with('alert', 'Cheque already used select another cheque');
        } else {
          $getamount = \App\Models\ReceivedCheque::where('id', $request['cheque_id'])->first(['id', 'amount']);
          //echo $getamount->amount.'=='.number_format((float)$request['amount'], 4, '.', '');die;
          if ($getamount->amount != number_format((float) $request['amount'], 4, '.', '')) {
            return back()->with('alert', 'Investment  amount is not equal to cheque amount');
          }
        }
      }
      if ($plantype != 'saving-account' && $plantype != 'saving-account-child') {
        if (($request['amount'] != $request['hidden_amount']) || ($request['tenure'] != $request['hidden_tenure'])) {
          $getdaybook = Daybook::where('investment_id', $investmentId)->whereIn('transaction_type', [2, 4])->count();
          if ($getdaybook > 1) {
            return back()->with('alert', "There are more than one transaction in this investment. So you cannot update.");
          }
          $dayBookId = Daybook::where('investment_id', $investmentId)->where('transaction_type', 2)->first('id');
          if ($dayBookId) {
            $createDayBook = $dayBookId->id;
            $getcommission = \App\Models\AssociateCommission::where('is_distribute', 1)->where('type_id', $investmentId)->where('day_book_id', $createDayBook)->count();
            if ($getcommission > 0) {
              return back()->with('alert', "You cannot update the amount. Because the commission has already been distributed.");
            }
          }
        }
      }
      // Session::put('created_at', $request['created_at']);
      $type = 'update';
      $ssbId = $request->input('ssb_id');
      // $investmentId = $request->input('investmentId');
      //get login user branch id(branch manager)pass auth id
      $branch_id = $request['branchid'];
      $getBranchCode = getBranchCode($branch_id);
      $branchCode = $getBranchCode->branch_code;
      $planId = $request['investmentplan'];
      // Invesment Account no
      $investmentPMode = Memberinvestments::where('id', $investmentId)->first();
      Session::put('created_at', $investmentPMode->created_at);
      Session::put('created_atUpdate', $investmentPMode->created_at);
      $data = $this->getUpdateData($request->all(), $type, $branch_id);
      $investment = Memberinvestments::find($investmentId);
      $investment->update($data);
      if ($investmentPMode->payment_mode == 3) {
        $getAssociateAmount = SavingAccount::select('balance')->where('id', $ssbId)->first();
        $updatedAmount = $getAssociateAmount->balance + $request['amount'];
        $updateAssociateAmount = SavingAccount::where('id', $ssbId)->update(array('balance' => $updatedAmount));
      }
      $iptId = Investmentplantransactions::select('id')->where('investment_id', $investmentId)->first();
      // $mipId = Memberinvestmentspayments::select('id')->where('investment_id',$investmentId)->first();
      $mifnId = Memberinvestmentsnominees::select('id')->where('nominee_type', 0)->where('investment_id', $investmentId)->first();
      $misnId = Memberinvestmentsnominees::select('id')->where('nominee_type', 1)->where('investment_id', $investmentId)->first();
      $ssb_id = $request->input('ssb_id');
      $amountArraySsb = array('1' => $request['amount']);
      $sAccount = $this->getSavingAccountDetails($request->input('associatemid'));
      $received_cheque_id = $cheque_id = NULL;
      $cheque_deposit_bank_id = NULL;
      $cheque_deposit_bank_ac_id = NULL;
      $cheque_no = NULL;
      $cheque_date = $pdate = NULL;
      $online_deposit_bank_id = NULL;
      $online_deposit_bank_ac_id = NULL;
      $online_transction_no = NULL;
      $online_transction_date = NULL;
      if ($request->input('payment-mode') == 1) {
        $chequeDetail = \App\Models\ReceivedCheque::where('id', $request['cheque_id'])->first();
        $received_cheque_id = $cheque_id = $request['cheque_id'];
        $cheque_deposit_bank_id = $chequeDetail->deposit_bank_id;
        $cheque_deposit_bank_ac_id = $chequeDetail->deposit_account_id;
        $cheque_no = $request['cheque-number'];
        $cheque_date = $pdate = date("Y-m-d", strtotime(str_replace('/', '-', $request['cheque-date'])));
      }
      if ($request->input('payment-mode') == 2) {
        $online_deposit_bank_id = $request['rd_online_bank_id'];
        $online_deposit_bank_ac_id = $request['rd_online_bank_ac_id'];
        $online_transction_no = $request['transaction-id'];
        $online_transction_date = $pdate = date("Y-m-d", strtotime(str_replace('/', '-', $request['date'])));
      }
      if (count($sAccount) > 0) {
        $ssbBalance = $sAccount[0]->balance;
      } else {
        $ssbBalance = '';
      }
      switch ($plantype) {
        case "saving-account":
          $createAccount = CommanController::updateSavingAccountDescription($ssbId, $branch_id, $branchCode, $request['amount']);
          $ssbAccountId = $createAccount;
          $amount_deposit_by_name = substr($request['member_name'], 0, strpos($request['member_name'], "-"));
          $ssbCreateTran = CommanController::updateTransaction($investmentId, $branch_id, $branchCode, $amountArraySsb, 0, $cheque_dd_no = '0', $bank_name = NULL, $branch_name = NULL, $payment_date = NULL, $online_payment_id = NULL, $online_payment_by = NULL, $saving_account_id = 0, 'CR');
          $createDayBook = CommanController::updateDayBook($ssbCreateTran, $request['associatemid'], $request['amount'], $request['amount'], $withdrawal = 0, $branch_id, $branchCode, $amountArraySsb, 0, $request['cheque-number'], NULL, NULL, NULL, $online_payment_by = NULL, $ssbId, 'CR');
          // ---------------------------  HEAD IMPLEMENT --------------------------
          $newDate = date('Y-m-d', strtotime($investmentPMode->created_at));
          $getTransactionData = \App\Models\AllHeadTransaction::where('type', 4)->where('sub_type', 41)->where('type_id', $investmentPMode->id)->where('entry_date', $newDate)->first();
          \App\Models\BranchDaybookReference::where('id', $getTransactionData->daybook_ref_id)->delete();
          \App\Models\AllHeadTransaction::where('type', 4)->where('sub_type', 41)->where('type_id', $investmentPMode->id)->whereDate('entry_date', $newDate)->delete();
          \App\Models\BranchDaybook::where('type', 4)->where('sub_type', 41)->where('type_id', $investmentPMode->id)->where('created_at', $investmentPMode->created_at)->delete();
          \App\Models\MemberTransaction::where('type', 4)->where('sub_type', 41)->where('type_id', $investmentPMode->id)->where('created_at', $investmentPMode->created_at)->delete();
          $currentDateRecord = \App\Models\BranchCash::where('branch_id', $investmentPMode->branch_id)->whereDate('entry_date', $investmentPMode->created_at)->first();
          if ($currentDateRecord) {
            $Result = \App\Models\BranchCash::find($currentDateRecord->id);
            $data['balance'] = $currentDateRecord->balance - $investmentPMode->deposite_amount;
            $Result->update($data);
          }
          $currentDateBranchRecord = \App\Models\BranchClosing::where('branch_id', $investmentPMode->branch_id)->whereDate('entry_date', $investmentPMode->created_at)->first();
          if ($currentDateBranchRecord) {
            $Result = \App\Models\BranchClosing::find($currentDateBranchRecord->id);
            $branchClosingdata['balance'] = $currentDateBranchRecord->balance - $investmentPMode->deposite_amount;
            $Result->update($branchClosingdata);
          }
          $ssbAccountTranId = SavingAccountTranscation::where('saving_account_id', $ssbId)->first();
          $this->investHeadCreateSSB($request['amount'], $investmentPMode->created_at, $ssbId, $investmentPMode->plan_id, $cheque_no, $cheque_id, $cheque_date, $online_transction_no, $online_transction_date, $online_deposit_bank_id, $online_deposit_bank_ac_id, $investmentPMode->branch_id, $request['associatemid'], $request['memberAutoId'], $ssbId, $ssbAccountTranId->id, $request->input('payment-mode'), $investmentPMode->account_number, $companyId);
          //--------------------------------HEAD IMPLEMENT  -------------------------
          if ($iptId && isset($iptId)) {
            $transaction = $this->updateTransactionData($request->all(), $investmentId, $type);
            $investment = Investmentplantransactions::find($iptId['id']);
            $investment->update($transaction);
          }
          if ($mifnId && isset($mifnId)) {
            $fNomineeData = $this->updateFirstNomineeData($request->all(), $investmentId, $type);
            $fNominee = Memberinvestmentsnominees::find($mifnId['id']);
            $fNominee->update($fNomineeData);
          }
          if ($request['second_nominee_add'] == 1) {
            if ($misnId['id'] && isset($misnId['id'])) {
              $sNomineeData = $this->updateSecondNomineeData($request->all(), $investmentId, $type);
              $sNominee = Memberinvestmentsnominees::find($misnId['id']);
              $sNominee->update($sNomineeData);
            } else {
              $sNominee = $this->getSecondNomineeData($request->all(), $investmentId, $type);
              $res = Memberinvestmentsnominees::create($sNominee);
            }
          }
          if ($mipId && isset($mipId)) {
            $paymentData = $this->updatePaymentMethodData($request->all(), $investmentId, $type);
            //  $investmentTransaction = Memberinvestmentspayments::find($mipId['id']);
            $investmentTransaction->update($paymentData);
          }
          break;
        case "samraddh-kanyadhan-yojana":
          if ($request->input('payment-mode') == 3) {
            /*$ssbAccount = SavingAccount::find($ssbId);
            $sData['balance'] = $request['amount'];
            $ssbAccount->save();*/
            $ssbAccountTran = SavingAccountTranscation::where('saving_account_id', $ssb_id)->update(array('opening_balance' => $request['amount'], 'deposit' => $request['amount']));
            $sAccountAmount = $ssbBalance - $request['amount'];
            $updateAssociateAmount = SavingAccount::where('id', $ssbId)->update(array('balance' => $sAccountAmount));
            $commonTransaction = NULL;
          } else {
            $commonTransaction = NULL;
          }
          $createDayBook = CommanController::updateDayBook($commonTransaction, $request['associatemid'], $request['amount'], $request['amount'], $withdrawal = 0, $branch_id, $branchCode, $amountArraySsb, $request->input('payment-mode'), $request['cheque-number'], $request['bank-name'], $request['branch-name'], $request['transaction-id'], $online_payment_by = NULL, $ssbId, 'CR');
          /*--------------------- received cheque payment -----------------------*/
          if ($request->input('payment-mode') == 1 && $payment_modeget != 1) {
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
          if ($iptId && isset($iptId)) {
            $transaction = $this->updateTransactionData($request->all(), $investmentId, $type);
            $investment = Investmentplantransactions::find($iptId['id']);
            $investment->update($transaction);
          }
          if ($mipId && isset($mipId)) {
            $paymentData = $this->updatePaymentMethodData($request->all(), $investmentId, $type);
            //  $investmentTransaction = Memberinvestmentspayments::find($mipId['id']);
            $investmentTransaction->update($paymentData);
          }
          // ---------------------------  HEAD IMPLEMENT --------------------------
          $this->deleteHeadTransactions(3, 31, $investmentPMode->id, $investmentPMode->created_at, $investmentPMode->branch_id, $investmentPMode->payment_mode, $investmentPMode->deposite_amount);
          $this->investHeadCreate($request['amount'], $investmentPMode->created_at, $investmentPMode->id, $investmentPMode->plan_id, $cheque_no, $cheque_id, $cheque_date, $online_transction_no, $online_transction_date, $online_deposit_bank_id, $online_deposit_bank_ac_id, $branch_id, $request['associatemid'], $request['memberAutoId'], $ssbId, $createDayBook, $request->input('payment-mode'), $investmentPMode->account_number, $companyId);
          //--------------------------------------------------------------------------
          break;
        case "special-samraddh-money-back":
          if ($request->input('payment-mode') == 3) {
            /*$ssbAccount = SavingAccount::find($ssbId);
            $sData['balance'] = $request['amount'];
            $ssbAccount->save();*/
            $ssbAccountTran = SavingAccountTranscation::where('saving_account_id', $ssb_id)->update(array('opening_balance' => $request['amount'], 'deposit' => $request['amount']));
            $sAccountAmount = $ssbBalance - $request['amount'];
            $updateAssociateAmount = SavingAccount::where('id', $ssbId)->update(array('balance' => $sAccountAmount));
            $commonTransaction = NULL;
          } else {
            $commonTransaction = NULL;
          }
          $createDayBook = CommanController::updateDayBook($commonTransaction, $request['associatemid'], $request['amount'], $request['amount'], $withdrawal = 0, $branch_id, $branchCode, $amountArraySsb, $request->input('payment-mode'), $request['cheque-number'], $request['bank-name'], $request['branch-name'], $request['transaction-id'], $online_payment_by = NULL, $ssbId, 'CR');
          /*--------------------- received cheque payment -----------------------*/
          if ($request->input('payment-mode') == 1 && $payment_modeget != 1) {
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
          if ($iptId && isset($iptId)) {
            $transaction = $this->updateTransactionData($request->all(), $investmentId, $type);
            $investment = Investmentplantransactions::find($iptId['id']);
            $investment->update($transaction);
          }
          if ($mifnId && isset($mifnId)) {
            $fNomineeData = $this->updateFirstNomineeData($request->all(), $investmentId, $type);
            $fNominee = Memberinvestmentsnominees::find($mifnId['id']);
            $fNominee->update($fNomineeData);
          }
          if ($request['second_nominee_add'] == 1) {
            if ($misnId['id'] && isset($misnId['id'])) {
              $sNomineeData = $this->updateSecondNomineeData($request->all(), $investmentId, $type);
              $sNominee = Memberinvestmentsnominees::find($misnId['id']);
              $sNominee->update($sNomineeData);
            } else {
              $sNominee = $this->getSecondNomineeData($request->all(), $investmentId, $type);
              $res = Memberinvestmentsnominees::create($sNominee);
            }
          }
          if ($mipId && isset($mipId)) {
            $paymentData = $this->updatePaymentMethodData($request->all(), $investmentId, $type);
            //  $investmentTransaction = Memberinvestmentspayments::find($mipId['id']);
            // $investmentTransaction->update($paymentData);
          }
          // ---------------------------  HEAD IMPLEMENT --------------------------
          $this->deleteHeadTransactions(3, 31, $investmentPMode->id, $investmentPMode->created_at, $investmentPMode->branch_id, $investmentPMode->payment_mode, $investmentPMode->deposite_amount);
          $this->investHeadCreate($request['amount'], $investmentPMode->created_at, $investmentPMode->id, $investmentPMode->plan_id, $cheque_no, $cheque_id, $cheque_date, $online_transction_no, $online_transction_date, $online_deposit_bank_id, $online_deposit_bank_ac_id, $branch_id, $request['associatemid'], $request['memberAutoId'], $ssbId, $createDayBook, $request->input('payment-mode'), $investmentPMode->account_number, $companyId);
          //--------------------------------------------------------------------------
          break;
        case "flexi-fixed-deposit":
          if ($request->input('payment-mode') == 3) {
            /*$ssbAccount = SavingAccount::find($ssbId);
            $sData['balance'] = $request['amount'];
            $ssbAccount->save();*/
            $ssbAccountTran = SavingAccountTranscation::where('saving_account_id', $ssb_id)->update(array('opening_balance' => $request['amount'], 'deposit' => $request['amount']));
            $sAccountAmount = $ssbBalance - $request['amount'];
            $updateAssociateAmount = SavingAccount::where('id', $ssbId)->update(array('balance' => $sAccountAmount));
            $commonTransaction = NULL;
          } else {
            $commonTransaction = NULL;
          }
          $createDayBook = CommanController::updateDayBook($commonTransaction, $request['associatemid'], $request['amount'], $request['amount'], $withdrawal = 0, $branch_id, $branchCode, $amountArraySsb, $request->input('payment-mode'), $request['cheque-number'], $request['bank-name'], $request['branch-name'], $request['transaction-id'], $online_payment_by = NULL, $ssbId, 'CR');
          /*--------------------- received cheque payment -----------------------*/
          if ($request->input('payment-mode') == 1 && $payment_modeget != 1) {
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
          if ($iptId && isset($iptId)) {
            $transaction = $this->updateTransactionData($request->all(), $investmentId, $type);
            $investment = Investmentplantransactions::find($iptId['id']);
            $investment->update($transaction);
          }
          if ($mifnId && isset($mifnId)) {
            $fNomineeData = $this->updateFirstNomineeData($request->all(), $investmentId, $type);
            $fNominee = Memberinvestmentsnominees::find($mifnId['id']);
            $fNominee->update($fNomineeData);
          }
          if ($request['second_nominee_add'] == 1) {
            if ($misnId['id'] && isset($misnId['id'])) {
              $sNomineeData = $this->updateSecondNomineeData($request->all(), $investmentId, $type);
              $sNominee = Memberinvestmentsnominees::find($misnId['id']);
              $sNominee->update($sNomineeData);
            } else {
              $sNominee = $this->getSecondNomineeData($request->all(), $investmentId, $type);
              $res = Memberinvestmentsnominees::create($sNominee);
            }
          }
          if ($mipId && isset($mipId)) {
            $paymentData = $this->updatePaymentMethodData($request->all(), $investmentId, $type);
            //  $investmentTransaction = Memberinvestmentspayments::find($mipId['id']);
            // $investmentTransaction->update($paymentData);
          }
          // ---------------------------  HEAD IMPLEMENT --------------------------
          $this->deleteHeadTransactions(3, 31, $investmentPMode->id, $investmentPMode->created_at, $investmentPMode->branch_id, $investmentPMode->payment_mode, $investmentPMode->deposite_amount);
          $this->investHeadCreate($request['amount'], $investmentPMode->created_at, $investmentPMode->id, $investmentPMode->plan_id, $cheque_no, $cheque_id, $cheque_date, $online_transction_no, $online_transction_date, $online_deposit_bank_id, $online_deposit_bank_ac_id, $branch_id, $request['associatemid'], $request['memberAutoId'], $ssbId, $createDayBook, $request->input('payment-mode'), $investmentPMode->account_number, $companyId);
          //--------------------------------------------------------------------------
          break;
        case "fixed-recurring-deposit":
          if ($request->input('payment-mode') == 3) {
            /*$ssbAccount = SavingAccount::find($ssbId);
            $sData['balance'] = $request['amount'];
            $ssbAccount->save();*/
            $ssbAccountTran = SavingAccountTranscation::where('saving_account_id', $ssb_id)->update(array('opening_balance' => $request['amount'], 'deposit' => $request['amount']));
            $sAccountAmount = $ssbBalance - $request['amount'];
            $updateAssociateAmount = SavingAccount::where('id', $ssbId)->update(array('balance' => $sAccountAmount));
            $commonTransaction = NULL;
          } else {
            $commonTransaction = NULL;
          }
          $createDayBook = CommanController::updateDayBook($commonTransaction, $request['associatemid'], $request['amount'], $request['amount'], $withdrawal = 0, $branch_id, $branchCode, $amountArraySsb, $request->input('payment-mode'), $request['cheque-number'], $request['bank-name'], $request['branch-name'], $request['transaction-id'], $online_payment_by = NULL, $ssbId, 'CR');
          /*--------------------- received cheque payment -----------------------*/
          if ($request->input('payment-mode') == 1 && $payment_modeget != 1) {
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
          if ($iptId && isset($iptId)) {
            $transaction = $this->updateTransactionData($request->all(), $investmentId, $type);
            $investment = Investmentplantransactions::find($iptId['id']);
            $investment->update($transaction);
          }
          if ($mifnId && isset($mifnId)) {
            $fNomineeData = $this->updateFirstNomineeData($request->all(), $investmentId, $type);
            $fNominee = Memberinvestmentsnominees::find($mifnId['id']);
            $fNominee->update($fNomineeData);
          }
          if ($request['second_nominee_add'] == 1) {
            if ($misnId['id'] && isset($misnId['id'])) {
              $sNomineeData = $this->updateSecondNomineeData($request->all(), $investmentId, $type);
              $sNominee = Memberinvestmentsnominees::find($misnId['id']);
              $sNominee->update($sNomineeData);
            } else {
              $sNominee = $this->getSecondNomineeData($request->all(), $investmentId, $type);
              $res = Memberinvestmentsnominees::create($sNominee);
            }
          }
          if ($mipId && isset($mipId)) {
            $paymentData = $this->updatePaymentMethodData($request->all(), $investmentId, $type);
            //  $investmentTransaction = Memberinvestmentspayments::find($mipId['id']);
            //$investmentTransaction->update($paymentData);
          }
          // ---------------------------  HEAD IMPLEMENT --------------------------
          $this->deleteHeadTransactions(3, 31, $investmentPMode->id, $investmentPMode->created_at, $investmentPMode->branch_id, $investmentPMode->payment_mode, $investmentPMode->deposite_amount);
          $this->investHeadCreate($request['amount'], $investmentPMode->created_at, $investmentPMode->id, $investmentPMode->plan_id, $cheque_no, $cheque_id, $cheque_date, $online_transction_no, $online_transction_date, $online_deposit_bank_id, $online_deposit_bank_ac_id, $branch_id, $request['associatemid'], $request['memberAutoId'], $ssbId, $createDayBook, $request->input('payment-mode'), $investmentPMode->account_number, $companyId);
          //--------------------------------------------------------------------------
          break;
        case "samraddh-jeevan":
          if ($request->input('payment-mode') == 3) {
            /*$ssbAccount = SavingAccount::find($ssbId);
            $sData['balance'] = $request['amount'];
            $ssbAccount->save();*/
            $ssbAccountTran = SavingAccountTranscation::where('saving_account_id', $ssb_id)->update(array('opening_balance' => $request['amount'], 'deposit' => $request['amount']));
            $sAccountAmount = $ssbBalance - $request['amount'];
            $updateAssociateAmount = SavingAccount::where('id', $ssbId)->update(array('balance' => $sAccountAmount));
            $commonTransaction = NULL;
          } else {
            $commonTransaction = NULL;
          }
          $createDayBook = CommanController::updateDayBook($commonTransaction, $request['associatemid'], $request['amount'], $request['amount'], $withdrawal = 0, $branch_id, $branchCode, $amountArraySsb, $request->input('payment-mode'), $request['cheque-number'], $request['bank-name'], $request['branch-name'], $request['transaction-id'], $online_payment_by = NULL, $ssbId, 'CR');
          /*--------------------- received cheque payment -----------------------*/
          if ($request->input('payment-mode') == 1 && $payment_modeget != 1) {
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
          if ($iptId && isset($iptId)) {
            $transaction = $this->updateTransactionData($request->all(), $investmentId, $type);
            $investment = Investmentplantransactions::find($iptId['id']);
            $investment->update($transaction);
          }
          if ($mifnId && isset($mifnId)) {
            $fNomineeData = $this->updateFirstNomineeData($request->all(), $investmentId, $type);
            $fNominee = Memberinvestmentsnominees::find($mifnId['id']);
            $fNominee->update($fNomineeData);
          }
          if ($request['second_nominee_add'] == 1) {
            if ($misnId['id'] && isset($misnId['id'])) {
              $sNomineeData = $this->updateSecondNomineeData($request->all(), $investmentId, $type);
              $sNominee = Memberinvestmentsnominees::find($misnId['id']);
              $sNominee->update($sNomineeData);
            } else {
              $sNominee = $this->getSecondNomineeData($request->all(), $investmentId, $type);
              $res = Memberinvestmentsnominees::create($sNominee);
            }
          }
          if ($mipId && isset($mipId)) {
            $paymentData = $this->updatePaymentMethodData($request->all(), $investmentId, $type);
            //  $investmentTransaction = Memberinvestmentspayments::find($mipId['id']);
            //   $investmentTransaction->update($paymentData);
          }
          // ---------------------------  HEAD IMPLEMENT --------------------------
          $this->deleteHeadTransactions(3, 31, $investmentPMode->id, $investmentPMode->created_at, $investmentPMode->branch_id, $investmentPMode->payment_mode, $investmentPMode->deposite_amount);
          $this->investHeadCreate($request['amount'], $investmentPMode->created_at, $investmentPMode->id, $investmentPMode->plan_id, $cheque_no, $cheque_id, $cheque_date, $online_transction_no, $online_transction_date, $online_deposit_bank_id, $online_deposit_bank_ac_id, $branch_id, $request['associatemid'], $request['memberAutoId'], $ssbId, $createDayBook, $request->input('payment-mode'), $investmentPMode->account_number, $companyId);
          //--------------------------------------------------------------------------
          break;
        case "daily-deposit":
          if ($request->input('payment-mode') == 3) {
            /*$ssbAccount = SavingAccount::find($ssbId);
            $sData['balance'] = $request['amount'];
            $ssbAccount->save();*/
            $ssbAccountTran = SavingAccountTranscation::where('saving_account_id', $ssb_id)->update(array('opening_balance' => $request['amount'], 'deposit' => $request['amount']));
            $sAccountAmount = $ssbBalance - $request['amount'];
            $updateAssociateAmount = SavingAccount::where('id', $ssbId)->update(array('balance' => $sAccountAmount));
            $commonTransaction = NULL;
          } else {
            $commonTransaction = NULL;
          }
          $createDayBook = CommanController::updateDayBook($commonTransaction, $request['associatemid'], $request['amount'], $request['amount'], $withdrawal = 0, $branch_id, $branchCode, $amountArraySsb, $request->input('payment-mode'), $request['cheque-number'], $request['bank-name'], $request['branch-name'], $request['transaction-id'], $online_payment_by = NULL, $ssbId, 'CR');
          /*--------------------- received cheque payment -----------------------*/
          if ($request->input('payment-mode') == 1 && $payment_modeget != 1) {
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
          if ($iptId && isset($iptId)) {
            $transaction = $this->updateTransactionData($request->all(), $investmentId, $type);
            $investment = Investmentplantransactions::find($iptId['id']);
            $investment->update($transaction);
          }
          if ($mifnId && isset($mifnId)) {
            $fNomineeData = $this->updateFirstNomineeData($request->all(), $investmentId, $type);
            $fNominee = Memberinvestmentsnominees::find($mifnId['id']);
            $fNominee->update($fNomineeData);
          }
          if ($request['second_nominee_add'] == 1) {
            if ($misnId['id'] && isset($misnId['id'])) {
              $sNomineeData = $this->updateSecondNomineeData($request->all(), $investmentId, $type);
              $sNominee = Memberinvestmentsnominees::find($misnId['id']);
              $sNominee->update($sNomineeData);
            } else {
              $sNominee = $this->getSecondNomineeData($request->all(), $investmentId, $type);
              $res = Memberinvestmentsnominees::create($sNominee);
            }
          }
          if ($mipId && isset($mipId)) {
            $paymentData = $this->updatePaymentMethodData($request->all(), $investmentId, $type);
            //  $investmentTransaction = Memberinvestmentspayments::find($mipId['id']);
            // $investmentTransaction->update($paymentData);
          }
          // ---------------------------  HEAD IMPLEMENT --------------------------
          $this->deleteHeadTransactions(3, 31, $investmentPMode->id, $investmentPMode->created_at, $investmentPMode->branch_id, $investmentPMode->payment_mode, $investmentPMode->deposite_amount);
          $this->investHeadCreate($request['amount'], $investmentPMode->created_at, $investmentPMode->id, $investmentPMode->plan_id, $cheque_no, $cheque_id, $cheque_date, $online_transction_no, $online_transction_date, $online_deposit_bank_id, $online_deposit_bank_ac_id, $branch_id, $request['associatemid'], $request['memberAutoId'], $ssbId, $createDayBook, $request->input('payment-mode'), $investmentPMode->account_number, $companyId);
          //--------------------------------------------------------------------------
          break;
        case "monthly-income-scheme":
          if ($request->input('payment-mode') == 3) {
            /*$ssbAccount = SavingAccount::find($ssbId);
            $sData['balance'] = $request['amount'];
            $ssbAccount->save();*/
            $ssbAccountTran = SavingAccountTranscation::where('saving_account_id', $ssb_id)->update(array('opening_balance' => $request['amount'], 'deposit' => $request['amount']));
            $sAccountAmount = $ssbBalance - $request['amount'];
            $updateAssociateAmount = SavingAccount::where('id', $ssbId)->update(array('balance' => $sAccountAmount));
            $commonTransaction = NULL;
          } else {
            $commonTransaction = NULL;
          }
          $createDayBook = CommanController::updateDayBook($commonTransaction, $request['associatemid'], $request['amount'], $request['amount'], $withdrawal = 0, $branch_id, $branchCode, $amountArraySsb, $request->input('payment-mode'), $request['cheque-number'], $request['bank-name'], $request['branch-name'], $request['transaction-id'], $online_payment_by = NULL, $ssbId, 'CR');
          /*--------------------- received cheque payment -----------------------*/
          if ($request->input('payment-mode') == 1 && $payment_modeget != 1) {
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
          if ($iptId && isset($iptId)) {
            $transaction = $this->updateTransactionData($request->all(), $investmentId, $type);
            $investment = Investmentplantransactions::find($iptId['id']);
            $investment->update($transaction);
          }
          if ($mifnId && isset($mifnId)) {
            $fNomineeData = $this->updateFirstNomineeData($request->all(), $investmentId, $type);
            $fNominee = Memberinvestmentsnominees::find($mifnId['id']);
            $fNominee->update($fNomineeData);
          }
          if ($request['second_nominee_add'] == 1) {
            if ($misnId['id'] && isset($misnId['id'])) {
              $sNomineeData = $this->updateSecondNomineeData($request->all(), $investmentId, $type);
              $sNominee = Memberinvestmentsnominees::find($misnId['id']);
              $sNominee->update($sNomineeData);
            } else {
              $sNominee = $this->getSecondNomineeData($request->all(), $investmentId, $type);
              $res = Memberinvestmentsnominees::create($sNominee);
            }
          }
          if ($mipId && isset($mipId)) {
            $paymentData = $this->updatePaymentMethodData($request->all(), $investmentId, $type);
            //  $investmentTransaction = Memberinvestmentspayments::find($mipId['id']);
            //   $investmentTransaction->update($paymentData);
          }
          // ---------------------------  HEAD IMPLEMENT --------------------------
          $this->deleteHeadTransactions(3, 31, $investmentPMode->id, $investmentPMode->created_at, $investmentPMode->branch_id, $investmentPMode->payment_mode, $investmentPMode->deposite_amount);
          $this->investHeadCreate($request['amount'], $investmentPMode->created_at, $investmentPMode->id, $investmentPMode->plan_id, $cheque_no, $cheque_id, $cheque_date, $online_transction_no, $online_transction_date, $online_deposit_bank_id, $online_deposit_bank_ac_id, $branch_id, $request['associatemid'], $request['memberAutoId'], $ssbId, $createDayBook, $request->input('payment-mode'), $investmentPMode->account_number, $companyId);
          //--------------------------------------------------------------------------
          break;
        case "fixed-deposit":
          if ($request->input('payment-mode') == 3) {
            /*$ssbAccount = SavingAccount::find($ssbId);
            $sData['balance'] = $request['amount'];
            $ssbAccount->save(); */
            $ssbAccountTran = SavingAccountTranscation::where('saving_account_id', $ssb_id)->update(array('opening_balance' => $request['amount'], 'deposit' => $request['amount']));
            $sAccountAmount = $ssbBalance - $request['amount'];
            $updateAssociateAmount = SavingAccount::where('id', $ssbId)->update(array('balance' => $sAccountAmount));
            $commonTransaction = NULL;
          } else {
            $commonTransaction = NULL;
          }
          $createDayBook = CommanController::updateDayBook($commonTransaction, $request['associatemid'], $request['amount'], $request['amount'], $withdrawal = 0, $branch_id, $branchCode, $amountArraySsb, $request->input('payment-mode'), $request['cheque-number'], $request['bank-name'], $request['branch-name'], $request['transaction-id'], $online_payment_by = NULL, $ssbId, 'CR');
          /*--------------------- received cheque payment -----------------------*/
          if ($request->input('payment-mode') == 1 && $payment_modeget != 1) {
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
          if ($iptId && isset($iptId)) {
            $transaction = $this->updateTransactionData($request->all(), $investmentId, $type);
            $investment = Investmentplantransactions::find($iptId['id']);
            $investment->update($transaction);
          }
          if ($mifnId && isset($mifnId)) {
            $fNomineeData = $this->updateFirstNomineeData($request->all(), $investmentId, $type);
            $fNominee = Memberinvestmentsnominees::find($mifnId['id']);
            $fNominee->update($fNomineeData);
          }
          if ($request['second_nominee_add'] == 1) {
            if ($misnId['id'] && isset($misnId['id'])) {
              $sNomineeData = $this->updateSecondNomineeData($request->all(), $investmentId, $type);
              $sNominee = Memberinvestmentsnominees::find($misnId['id']);
              $sNominee->update($sNomineeData);
            } else {
              $sNominee = $this->getSecondNomineeData($request->all(), $investmentId, $type);
              $res = Memberinvestmentsnominees::create($sNominee);
            }
          }
          if ($mipId && isset($mipId)) {
            $paymentData = $this->updatePaymentMethodData($request->all(), $investmentId, $type);
            //  $investmentTransaction = Memberinvestmentspayments::find($mipId['id']);
            //   $investmentTransaction->update($paymentData);
          }
          // ---------------------------  HEAD IMPLEMENT --------------------------
          $this->deleteHeadTransactions(3, 31, $investmentPMode->id, $investmentPMode->created_at, $investmentPMode->branch_id, $investmentPMode->payment_mode, $investmentPMode->deposite_amount);
          $this->investHeadCreate($request['amount'], $investmentPMode->created_at, $investmentPMode->id, $investmentPMode->plan_id, $cheque_no, $cheque_id, $cheque_date, $online_transction_no, $online_transction_date, $online_deposit_bank_id, $online_deposit_bank_ac_id, $branch_id, $request['associatemid'], $request['memberAutoId'], $ssbId, $createDayBook, $request->input('payment-mode'), $investmentPMode->account_number, $companyId);
          //--------------------------------------------------------------------------
          break;
        case "recurring-deposit":
          if ($request->input('payment-mode') == 3) {
            /*$ssbAccount = SavingAccount::find($ssbId);
            $sData['balance'] = $request['amount'];
            $ssbAccount->save();*/
            $ssbAccountTran = SavingAccountTranscation::where('saving_account_id', $ssbId)->update(array('opening_balance' => $request['amount'], 'deposit' => $request['amount']));
            $sAccountAmount = $ssbBalance - $request['amount'];
            $updateAssociateAmount = SavingAccount::where('id', $ssbId)->update(array('balance' => $sAccountAmount));
            $commonTransaction = NULL;
          } else {
            $commonTransaction = NULL;
          }
          $createDayBook = CommanController::updateDayBook($commonTransaction, $request['associatemid'], $request['amount'], $request['amount'], $withdrawal = 0, $branch_id, $branchCode, $amountArraySsb, $request->input('payment-mode'), $request['cheque-number'], $request['bank-name'], $request['branch-name'], $request['transaction-id'], $online_payment_by = NULL, $ssbId, 'CR');
          /*--------------------- received cheque payment -----------------------*/
          if ($request->input('payment-mode') == 1 && $payment_modeget != 1) {
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
          if ($iptId && isset($iptId)) {
            $transaction = $this->updateTransactionData($request->all(), $investmentId, $type);
            $investment = Investmentplantransactions::find($iptId['id']);
            $investment->update($transaction);
          }
          if ($mifnId && isset($mifnId)) {
            $fNomineeData = $this->updateFirstNomineeData($request->all(), $investmentId, $type);
            $fNominee = Memberinvestmentsnominees::find($mifnId['id']);
            $fNominee->update($fNomineeData);
          }
          if ($request['second_nominee_add'] == 1) {
            if ($misnId['id'] && isset($misnId['id'])) {
              $sNomineeData = $this->updateSecondNomineeData($request->all(), $investmentId, $type);
              $sNominee = Memberinvestmentsnominees::find($misnId['id']);
              $sNominee->update($sNomineeData);
            } else {
              $sNominee = $this->getSecondNomineeData($request->all(), $investmentId, $type);
              $res = Memberinvestmentsnominees::create($sNominee);
            }
          }
          if ($mipId && isset($mipId)) {
            $paymentData = $this->updatePaymentMethodData($request->all(), $investmentId, $type);
            //  $investmentTransaction = Memberinvestmentspayments::find($mipId['id']);
            //  $investmentTransaction->update($paymentData);
          }
          // ---------------------------  HEAD IMPLEMENT --------------------------
          $this->deleteHeadTransactions(3, 31, $investmentPMode->id, $investmentPMode->created_at, $investmentPMode->branch_id, $investmentPMode->payment_mode, $investmentPMode->deposite_amount);
          $this->investHeadCreate($request['amount'], $investmentPMode->created_at, $investmentPMode->id, $investmentPMode->plan_id, $cheque_no, $cheque_id, $cheque_date, $online_transction_no, $online_transction_date, $online_deposit_bank_id, $online_deposit_bank_ac_id, $branch_id, $request['associatemid'], $request['memberAutoId'], $ssbId, $createDayBook, $request->input('payment-mode'), $investmentPMode->account_number, $companyId);
          //--------------------------------------------------------------------------
          break;
        case "saving-account-child":
          if ($request->input('payment-mode') == 3) {
            /*$ssbAccount = SavingAccount::find($ssbId);
            $sData['balance'] = $request['amount'];
            $ssbAccount->save();*/
            $ssbAccountTran = SavingAccountTranscation::where('saving_account_id', $ssbId)->update(array('opening_balance' => $request['amount'], 'deposit' => $request['amount']));
            $sAccountAmount = $ssbBalance - $request['amount'];
            $updateAssociateAmount = SavingAccount::where('id', $ssbId)->update(array('balance' => $sAccountAmount));
            $commonTransaction = NULL;
          } else {
            $commonTransaction = NULL;
          }
          // $request->request->add(['amount' => 0]);
          // $request->request->add(['payment-mode' => 0]);
          // $request->request->add(['transaction-id' => 0]);
          // $request->request->add(['date' => date("Y-m-d")]);
          $createDayBook = CommanController::updateDayBook($commonTransaction, $request['associatemid'], $request['amount'], $request['amount'], $withdrawal = 0, $branch_id, $branchCode, $amountArraySsb, $request->input('payment-mode'), $request['cheque-number'], $request['bank-name'], $request['branch-name'], $request['transaction-id'], $online_payment_by = NULL, $ssbId, 'CR');
          /*--------------------- received cheque payment -----------------------*/
          if ($request->input('payment-mode') == 1 && $payment_modeget != 1) {
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
          if ($iptId && isset($iptId)) {
            $transaction = $this->updateTransactionData($request->all(), $investmentId, $type);
            $investment = Investmentplantransactions::find($iptId['id']);
            $investment->update($transaction);
          }
          if ($mifnId && isset($mifnId)) {
            $fNomineeData = $this->updateFirstNomineeData($request->all(), $investmentId, $type);
            $fNominee = Memberinvestmentsnominees::find($mifnId['id']);
            $fNominee->update($fNomineeData);
          }
          if ($request['second_nominee_add'] == 1) {
            if ($misnId['id'] && isset($misnId['id'])) {
              $sNomineeData = $this->updateSecondNomineeData($request->all(), $investmentId, $type);
              $sNominee = Memberinvestmentsnominees::find($misnId['id']);
              $sNominee->update($sNomineeData);
            } else {
              $sNominee = $this->getSecondNomineeData($request->all(), $investmentId, $type);
              $res = Memberinvestmentsnominees::create($sNominee);
            }
          }
          // if($mipId && isset($mipId)){
          //     $paymentData = $this->updatePaymentMethodData($request->all(),$investmentId,$type);
          //     $investmentTransaction = Memberinvestmentspayments::find($mipId['id']);
          //     $investmentTransaction->update($paymentData);
          // }
          // ---------------------------  HEAD IMPLEMENT --------------------------
          // $this->deleteHeadTransactions(3,31,$investmentPMode->id,$investmentPMode->created_at,$investmentPMode->branch_id,$investmentPMode->payment_mode,$investmentPMode->deposite_amount);
          // $this->investHeadCreate($request['amount'],$investmentPMode->created_at,$investmentPMode->id,$investmentPMode->plan_id,$cheque_no,$cheque_id,$cheque_date,$online_transction_no,$online_transction_date,$online_deposit_bank_id,$online_deposit_bank_ac_id,$branch_id,$request['associatemid'],$request['memberAutoId'],$ssbId,$createDayBook,$request->input('payment-mode'),$investmentPMode->account_number);
          //--------------------------------------------------------------------------
          $redirectUrls = URL::to("admin/memberinvestment/corrections");
          break;
        case "samraddh-bhavhishya":
          if ($request->input('payment-mode') == 3) {
            /*$ssbAccount = SavingAccount::find($ssbId);
            $sData['balance'] = $request['amount'];
            $ssbAccount->save();*/
            $ssbAccountTran = SavingAccountTranscation::where('saving_account_id', $ssb_id)->update(array('opening_balance' => $request['amount'], 'deposit' => $request['amount']));
            $sAccountAmount = $ssbBalance - $request['amount'];
            $updateAssociateAmount = SavingAccount::where('id', $ssbId)->update(array('balance' => $sAccountAmount));
            $commonTransaction = NULL;
          } else {
            $commonTransaction = NULL;
          }
          $createDayBook = CommanController::updateDayBook($commonTransaction, $request['associatemid'], $request['amount'], $request['amount'], $withdrawal = 0, $branch_id, $branchCode, $amountArraySsb, $request->input('payment-mode'), $request['cheque-number'], $request['bank-name'], $request['branch-name'], $request['transaction-id'], $online_payment_by = NULL, $ssbId, 'CR');
          /*--------------------- received cheque payment -----------------------*/
          if ($request->input('payment-mode') == 1 && $payment_modeget != 1) {
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
          if ($iptId && isset($iptId)) {
            $transaction = $this->updateTransactionData($request->all(), $investmentId, $type);
            $investment = Investmentplantransactions::find($iptId['id']);
            $investment->update($transaction);
          }
          if ($mifnId && isset($mifnId)) {
            $fNomineeData = $this->updateFirstNomineeData($request->all(), $investmentId, $type);
            $fNominee = Memberinvestmentsnominees::find($mifnId['id']);
            $fNominee->update($fNomineeData);
          }
          if ($request['second_nominee_add'] == 1) {
            if ($misnId['id'] && isset($misnId['id'])) {
              $sNomineeData = $this->updateSecondNomineeData($request->all(), $investmentId, $type);
              $sNominee = Memberinvestmentsnominees::find($misnId['id']);
              $sNominee->update($sNomineeData);
            } else {
              $sNominee = $this->getSecondNomineeData($request->all(), $investmentId, $type);
              $res = Memberinvestmentsnominees::create($sNominee);
            }
          }
          if ($mipId && isset($mipId)) {
            $paymentData = $this->updatePaymentMethodData($request->all(), $investmentId, $type);
            //  $investmentTransaction = Memberinvestmentspayments::find($mipId['id']);
            //$investmentTransaction->update($paymentData);
          }
          // ---------------------------  HEAD IMPLEMENT --------------------------
          $this->deleteHeadTransactions(3, 31, $investmentPMode->id, $investmentPMode->created_at, $investmentPMode->branch_id, $investmentPMode->payment_mode, $investmentPMode->deposite_amount);
          $this->investHeadCreate($request['amount'], $investmentPMode->created_at, $investmentPMode->id, $investmentPMode->plan_id, $cheque_no, $cheque_id, $cheque_date, $online_transction_no, $online_transction_date, $online_deposit_bank_id, $online_deposit_bank_ac_id, $branch_id, $request['associatemid'], $request['memberAutoId'], $ssbId, $createDayBook, $request->input('payment-mode'), $investmentPMode->account_number, $companyId);
          //--------------------------------------------------------------------------
          break;
      }
      DB::commit();
    } catch (\Exception $ex) {
      DB::rollback();
      return back()->with('alert', $ex->getMessage());
    }
    if ($investment && $request['requestid']) {
      $correctionRequest = CorrectionRequests::find($request['requestid']);
      $crData['status'] = 1;
      $correctionRequest->update($crData);
      $investmetData['investment_correction_request'] = 0;
      $updateInvestment = Memberinvestments::find($investmentId);
      $updateInvestment->update($investmetData);
      $redirectUrls = URL::to("admin/memberinvestment/corrections");
      //return back()->with('success', 'Update was Successful!');
      return Redirect::to($redirectUrls)->with('success', 'Update was Successful!');
      ;
    } else if ($investment) {
      return back()->with('success', 'Update was Successful!');
      //  return Redirect::to($redirectUrls)->with('success', 'Update was Successful!');;
    } else {
      return back()->with('alert', 'An error occured');
    }
  }
  /**
   * Get investment plans data to store.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\Response
   */
  // public function getData($request,$type,$miCode,$investmentAccount,$branch_id)
  // {
  //     $plantype = $request['plan_type'];
  //     $faCode=getPlanCode($request['investmentplan']);
  //     switch ($plantype) {
  //       case "saving-account":
  //         $data['deposite_amount'] = $request['amount'];
  //         $data['current_balance'] = $request['amount'];
  //         $data['payment_mode'] = $request['payment-mode'];
  //         /*if($request['hidden_primary_account']){
  //           $data['primary_account'] = $request['primary_account'];
  //         }*/
  //         break;
  //       case "samraddh-kanyadhan-yojana":
  //         $data['guardians_relation'] = $request['guardian-ralationship'];
  //         $data['daughter_name'] = $request['daughter-name'];
  //         $data['phone_number'] = $request['phone-number'];
  //         $data['dob'] = date("Y-m-d", strtotime( str_replace('/','-',$request['dob'] ) ) );
  //         $data['age'] = $request['age'];
  //         $data['deposite_amount'] = $request['amount'];
  //         $data['tenure'] = $request['tenure'];
  //         $data['payment_mode'] = $request['payment-mode'];
  //         $data['maturity_amount'] = $request['maturity-amount'];
  //         $data['interest_rate'] = $request['interest-rate'];
  //         $data['current_balance'] = $request['amount'];
  //         $data['maturity_date'] =  date("Y-m-d",strtotime('+ '.($request['tenure']*12).'months', strtotime(date("Y/m/d"))) );
  //         break;
  //       case "special-samraddh-money-back":
  //         $data['ssb_account_number'] = $request['ssbacount'];
  //         $data['deposite_amount'] = $request['amount'];
  //         $data['payment_mode'] = $request['payment-mode'];
  //         $data['maturity_amount'] = $request['maturity-amount'];
  //         $data['interest_rate'] = $request['interest-rate'];
  //         $data['tenure'] = $request['tenure'];
  //         $data['current_balance'] = $request['amount'];
  //         $data['maturity_date'] =  date("Y-m-d",strtotime('+ '.($request['tenure']*12).'months', strtotime(date("Y/m/d"))) );
  //         break;
  //       case "flexi-fixed-deposit":
  //         $tenure = $request['tenure'];
  //         if($tenure == 12){
  //           $tenurefacode = $faCode.'001';
  //         }elseif($tenure == 24){
  //           $tenurefacode = $faCode.'002';
  //         }elseif($tenure == 36){
  //           $tenurefacode = $faCode.'003';
  //         }elseif($tenure == 48){
  //           $tenurefacode = $faCode.'004';
  //         }elseif($tenure == 60){
  //           $tenurefacode = $faCode.'005';
  //         }elseif($tenure == 72){
  //           $tenurefacode = $faCode.'006';
  //         }elseif($tenure == 84){
  //           $tenurefacode = $faCode.'007';
  //         }elseif($tenure == 96){
  //           $tenurefacode = $faCode.'008';
  //         }elseif($tenure == 108){
  //           $tenurefacode = $faCode.'009';
  //         }elseif($tenure == 120){
  //           $tenurefacode = $faCode.'010';
  //         }
  //         $data['tenure'] = $request['tenure']/12;
  //         $data['tenure_fa_code'] = $tenurefacode;
  //         $data['deposite_amount'] = $request['amount'];
  //         $data['payment_mode'] = $request['payment-mode'];
  //         $data['maturity_amount'] = $request['maturity-amount'];
  //         $data['interest_rate'] = $request['interest-rate'];
  //         $data['current_balance'] = $request['amount'];
  //         $data['maturity_date'] =  date("Y-m-d",strtotime('+ '.($request['tenure']).'months', strtotime(date("Y/m/d"))) );
  //         break;
  //       case "fixed-recurring-deposit":
  //         $data['tenure'] = $request['tenure']/12;
  //         $data['deposite_amount'] = $request['amount'];
  //         $data['payment_mode'] = $request['payment-mode'];
  //         $data['maturity_amount'] = $request['maturity-amount'];
  //         $data['interest_rate'] = $request['interest-rate'];
  //         $data['current_balance'] = $request['amount'];
  //         $data['maturity_date'] =  date("Y-m-d",strtotime('+ '.($request['tenure']).'months', strtotime(date("Y/m/d"))) );
  //         break;
  //       case "samraddh-jeevan":
  //         $data['tenure'] = $request['tenure']/12;
  //         $data['ssb_account_number'] = $request['ssbacount'];
  //         $data['deposite_amount'] = $request['amount'];
  //         $data['payment_mode'] = $request['payment-mode'];
  //         $data['maturity_amount'] = $request['maturity-amount'];
  //         $data['interest_rate'] = $request['interest-rate'];
  //         $data['current_balance'] = $request['amount'];
  //         $data['maturity_date'] =  date("Y-m-d",strtotime('+ '.($request['tenure']).'months', strtotime(date("Y/m/d"))) );
  //         break;
  //       case "daily-deposit":
  //         $tenure = $request['tenure'];
  //         if($tenure == 12){
  //           $tenurefacode = $faCode.'001';
  //         }elseif($tenure == 24){
  //           $tenurefacode = $faCode.'002';
  //         }elseif($tenure == 36){
  //           $tenurefacode = $faCode.'003';
  //         }elseif($tenure == 48){
  //           $tenurefacode = $faCode.'004';
  //         }elseif($tenure == 60){
  //           $tenurefacode = $faCode.'005';
  //         }
  //         $data['tenure'] = $request['tenure']/12;
  //         $data['tenure_fa_code'] = $tenurefacode;
  //         $data['deposite_amount'] = $request['amount'];
  //         $data['payment_mode'] = $request['payment-mode'];
  //         $data['interest_rate'] = $request['interest-rate'];
  //         $data['maturity_amount'] = $request['maturity-amount'];
  //         $data['current_balance'] = $request['amount'];
  //         $data['maturity_date'] =  date("Y-m-d",strtotime('+ '.($request['tenure']).'months', strtotime(date("Y/m/d"))) );
  //         break;
  //       case "monthly-income-scheme":
  //         $data['ssb_account_number'] = $request['ssbacount'];
  //         $data['deposite_amount'] = $request['amount'];
  //         $data['payment_mode'] = $request['payment-mode'];
  //         $data['tenure'] = $request['tenure']/12;
  //         $data['maturity_amount'] = $request['maturity-amount'];
  //         $data['interest_rate'] = $request['interest_rate'];
  //         $data['current_balance'] = $request['amount'];
  //         $data['maturity_date'] =  date("Y-m-d",strtotime('+ '.($request['tenure']).'months', strtotime(date("Y/m/d"))) );
  //         break;
  //       case "fixed-deposit":
  //         $data['deposite_amount'] = $request['amount'];
  //         $data['payment_mode'] = $request['payment-mode'];
  //         $data['tenure'] = $request['tenure']/12;
  //         $data['maturity_amount'] = $request['maturity-amount'];
  //         $data['interest_rate'] = $request['interest-rate'];
  //         $data['current_balance'] = $request['amount'];
  //         $data['maturity_date'] =  date("Y-m-d",strtotime('+ '.($request['tenure']).'months', strtotime(date("Y/m/d"))) );
  //         break;
  //       case "recurring-deposit":
  //         $tenure = $request['tenure'];
  //         if($tenure == 36){
  //           $tenurefacode = $faCode.'002';
  //         }elseif($tenure == 60){
  //           $tenurefacode = $faCode.'003';
  //         }elseif($tenure == 84){
  //           $tenurefacode = $faCode.'004';
  //         }else{
  //           $tenurefacode = $faCode.'001';
  //         }
  //         $data['tenure'] = $request['tenure']/12;
  //         $data['tenure_fa_code'] = $tenurefacode;
  //         $data['deposite_amount'] = $request['amount'];
  //         $data['payment_mode'] = $request['payment-mode'];
  //         $data['maturity_amount'] = $request['maturity-amount'];
  //         $data['interest_rate'] = $request['interest-rate'];
  //         $data['current_balance'] = $request['amount'];
  //         $data['maturity_date'] =  date("Y-m-d",strtotime('+ '.($request['tenure']).'months', strtotime(date("Y/m/d"))) );
  //         break;
  //       case "samraddh-bhavhishya":
  //         $data['deposite_amount'] = $request['amount'];
  //         $data['tenure'] = $request['tenure']/12;
  //         $data['maturity_amount'] = $request['maturity-amount'];
  //         $data['interest_rate'] = $request['interest-rate'];
  //         $data['current_balance'] = $request['amount'];
  //         $data['payment_mode'] = $request['payment-mode'];
  //         $data['maturity_date'] =  date("Y-m-d",strtotime('+ '.($request['tenure']).'months', strtotime(date("Y/m/d"))) );
  //         break;
  //     }
  //     if($type=='create'){
  //         if($plantype=='saving-account'){
  //           $data['ssb_account_number'] = $investmentAccount;
  //           $data['created_at'] = $request['created_at'];
  //         }
  //         $data['mi_code'] = $miCode;
  //         $data['account_number'] = $investmentAccount;
  //         $data['plan_id'] = $request['investmentplan'];
  //         $data['form_number'] = $request['form_number'];
  //         $data['member_id'] = $request['memberAutoId'];
  //         $data['associate_id'] = $request['associatemid'];
  //         $data['branch_id'] = $branch_id;
  //         $data['created_at'] = $request['created_at'];
  //     }
  //     return $data;
  // }
  public function getData($request, $type, $miCode, $investmentAccount, $branch_id)
  {
    $plantype = $request['plan_type'];
    $data['old_branch_id'] = $branch_id;
    //dd($plantype);
    $faCode = getPlanCode($request['investmentplan']);
    switch ($plantype) {
      case "saving-account-child":
      case "saving-account":
        $data['deposite_amount'] = $request['amount'];
        $data['current_balance'] = $request['amount'];
        if ($plantype == "saving-account") {
          $data['payment_mode'] = $request['payment-mode'];
        }
        /*if($request['hidden_primary_account']){
        $data['primary_account'] = $request['primary_account'];
        }*/
        break;
      case "samraddh-kanyadhan-yojana":
        $data['guardians_relation'] = $request['guardian-ralationship'];
        $data['daughter_name'] = $request['daughter-name'];
        $data['phone_number'] = $request['phone-number'];
        $data['dob'] = date("Y-m-d", strtotime(str_replace('/', '-', $request['dob'])));
        $data['age'] = $request['age'];
        $data['deposite_amount'] = $request['amount'];
        $data['tenure'] = $request['tenure'];
        $data['payment_mode'] = $request['payment-mode'];
        $data['maturity_amount'] = $request['maturity-amount'];
        $data['interest_rate'] = $request['interest-rate'];
        $data['current_balance'] = $request['amount'];
        $data['maturity_date'] = date("Y-m-d", strtotime('+ ' . ($request['tenure'] * 12) . 'months', strtotime(date("Y/m/d"))));
        break;
      case "special-samraddh-money-back":
        $data['ssb_account_number'] = $request['ssbacount'];
        $data['deposite_amount'] = $request['amount'];
        $data['payment_mode'] = $request['payment-mode'];
        $data['maturity_amount'] = $request['maturity-amount'];
        $data['interest_rate'] = $request['interest-rate'];
        $data['tenure'] = $request['tenure'];
        $data['current_balance'] = $request['amount'];
        $data['maturity_date'] = date("Y-m-d", strtotime('+ ' . ($request['tenure'] * 12) . 'months', strtotime(date("Y/m/d"))));
        break;
      case "flexi-fixed-deposit":
        $tenure = $request['tenure'];
        if ($tenure == 12) {
          $tenurefacode = $faCode . '001';
        } elseif ($tenure == 24) {
          $tenurefacode = $faCode . '002';
        } elseif ($tenure == 36) {
          $tenurefacode = $faCode . '003';
        } elseif ($tenure == 48) {
          $tenurefacode = $faCode . '004';
        } elseif ($tenure == 60) {
          $tenurefacode = $faCode . '005';
        } elseif ($tenure == 72) {
          $tenurefacode = $faCode . '006';
        } elseif ($tenure == 84) {
          $tenurefacode = $faCode . '007';
        } elseif ($tenure == 96) {
          $tenurefacode = $faCode . '008';
        } elseif ($tenure == 108) {
          $tenurefacode = $faCode . '009';
        } elseif ($tenure == 120) {
          $tenurefacode = $faCode . '010';
        }
        $data['tenure'] = $request['tenure'] / 12;
        $data['tenure_fa_code'] = $tenurefacode;
        $data['deposite_amount'] = $request['amount'];
        $data['payment_mode'] = $request['payment-mode'];
        $data['maturity_amount'] = $request['maturity-amount'];
        $data['interest_rate'] = $request['interest-rate'];
        $data['current_balance'] = $request['amount'];
        $data['maturity_date'] = date("Y-m-d", strtotime('+ ' . ($request['tenure']) . 'months', strtotime(date("Y/m/d"))));
        break;
      case "fixed-recurring-deposit":
        $data['tenure'] = $request['tenure'] / 12;
        $data['deposite_amount'] = $request['amount'];
        $data['payment_mode'] = $request['payment-mode'];
        $data['maturity_amount'] = $request['maturity-amount'];
        $data['interest_rate'] = $request['interest-rate'];
        $data['current_balance'] = $request['amount'];
        $data['maturity_date'] = date("Y-m-d", strtotime('+ ' . ($request['tenure']) . 'months', strtotime(date("Y/m/d"))));
        break;
      case "samraddh-jeevan":
        $data['tenure'] = $request['tenure'] / 12;
        $data['ssb_account_number'] = $request['ssbacount'];
        $data['deposite_amount'] = $request['amount'];
        $data['payment_mode'] = $request['payment-mode'];
        $data['maturity_amount'] = $request['maturity-amount'];
        $data['interest_rate'] = $request['interest-rate'];
        $data['current_balance'] = $request['amount'];
        $data['maturity_date'] = date("Y-m-d", strtotime('+ ' . ($request['tenure']) . 'months', strtotime(date("Y/m/d"))));
        break;
      case "daily-deposit":
        $tenure = $request['tenure'];
        if ($tenure == 12) {
          $tenurefacode = $faCode . '001';
        } elseif ($tenure == 24) {
          $tenurefacode = $faCode . '002';
        } elseif ($tenure == 36) {
          $tenurefacode = $faCode . '003';
        } elseif ($tenure == 48) {
          $tenurefacode = $faCode . '004';
        } elseif ($tenure == 60) {
          $tenurefacode = $faCode . '005';
        }
        $data['tenure'] = $request['tenure'] / 12;
        $data['tenure_fa_code'] = $tenurefacode;
        $data['deposite_amount'] = $request['amount'];
        $data['payment_mode'] = $request['payment-mode'];
        $data['interest_rate'] = $request['interest-rate'];
        $data['maturity_amount'] = $request['maturity-amount'];
        $data['current_balance'] = $request['amount'];
        $data['maturity_date'] = date("Y-m-d", strtotime('+ ' . ($request['tenure']) . 'months', strtotime(date("Y/m/d"))));
        break;
      case "monthly-income-scheme":
        $data['ssb_account_number'] = $request['ssbacount'];
        $data['deposite_amount'] = $request['amount'];
        $data['payment_mode'] = $request['payment-mode'];
        $data['tenure'] = $request['tenure'] / 12;
        $data['maturity_amount'] = $request['maturity-amount'];
        $data['interest_rate'] = $request['interest_rate'];
        $data['current_balance'] = $request['amount'];
        $data['maturity_date'] = date("Y-m-d", strtotime('+ ' . ($request['tenure']) . 'months', strtotime(date("Y/m/d"))));
        break;
      case "fixed-deposit":
        $data['deposite_amount'] = $request['amount'];
        $data['payment_mode'] = $request['payment-mode'];
        $data['tenure'] = $request['tenure'] / 12;
        $data['maturity_amount'] = $request['maturity-amount'];
        $data['interest_rate'] = $request['interest-rate'];
        $data['current_balance'] = $request['amount'];
        $data['maturity_date'] = date("Y-m-d", strtotime('+ ' . ($request['tenure']) . 'months', strtotime(date("Y/m/d"))));
        break;
      case "recurring-deposit":
        $tenure = $request['tenure'];
        if ($tenure == 36) {
          $tenurefacode = $faCode . '002';
        } elseif ($tenure == 60) {
          $tenurefacode = $faCode . '003';
        } elseif ($tenure == 84) {
          $tenurefacode = $faCode . '004';
        } else {
          $tenurefacode = $faCode . '001';
        }
        $data['tenure'] = $request['tenure'] / 12;
        $data['tenure_fa_code'] = $tenurefacode;
        $data['deposite_amount'] = $request['amount'];
        $data['payment_mode'] = $request['payment-mode'];
        $data['maturity_amount'] = $request['maturity-amount'];
        $data['interest_rate'] = $request['interest-rate'];
        $data['current_balance'] = $request['amount'];
        $data['maturity_date'] = date("Y-m-d", strtotime('+ ' . ($request['tenure']) . 'months', strtotime(date("Y/m/d"))));
        break;
      case "samraddh-bhavhishya":
        $data['deposite_amount'] = $request['amount'];
        $data['tenure'] = $request['tenure'] / 12;
        $data['maturity_amount'] = $request['maturity-amount'];
        $data['interest_rate'] = $request['interest-rate'];
        $data['current_balance'] = $request['amount'];
        $data['payment_mode'] = $request['payment-mode'];
        $data['maturity_date'] = date("Y-m-d", strtotime('+ ' . ($request['tenure']) . 'months', strtotime(date("Y/m/d"))));
        break;
    }
    // dd($type);
    if ($type == 'create') {
      if ($plantype == 'saving-account') {
        $data['ssb_account_number'] = $investmentAccount;
        $data['created_at'] = $request['created_at'];
      }
      if ($plantype == 'saving-account-child') {
        $data['re_dob'] = date("Y-m-d", strtotime(convertDate($request['re_member_dob'])));
        $data['re_name'] = $request['ex_re_name'];
        $data['re_guardians'] = $request['ex_re_guardians'];
        $data['re_gender'] = $request['ex_re_gender'];
      }
      $data['mi_code'] = $miCode;
      $data['account_number'] = $investmentAccount;
      $data['plan_id'] = $request['investmentplan'];
      $data['form_number'] = $request['form_number'];
      $data['member_id'] = $request['memberAutoId'];
      $data['associate_id'] = $request['associatemid'];
      $data['branch_id'] = $branch_id;
      $data['created_at'] = $request['created_at'];
    }
    // dd($data);
    return $data;
  }
  /**
   * Get investment plan data to update.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\Response
   */
  public function getUpdateData($request, $type, $branch_id)
  {
    $plantype = $request['plan_type'];
    $faCode = getPlanCode($request['investmentplan']);
    switch ($plantype) {
      case "saving-account":
        $data['deposite_amount'] = $request['amount'];
        break;
      case "saving-account-child":
        $data['form_number'] = $request['form_number'];
        break;
      case "samraddh-kanyadhan-yojana":
        $data['guardians_relation'] = $request['guardian-ralationship'];
        $data['daughter_name'] = $request['daughter-name'];
        $data['phone_number'] = $request['phone-number'];
        $data['dob'] = $request['dob'];
        $data['age'] = $request['age'];
        $data['deposite_amount'] = $request['amount'];
        $data['tenure'] = $request['tenure'];
        $data['payment_mode'] = $request['payment-mode'];
        $data['maturity_amount'] = $request['maturity-amount'];
        $data['interest_rate'] = $request['interest_rate'];
        break;
      case "special-samraddh-money-back":
        $data['deposite_amount'] = $request['amount'];
        $data['payment_mode'] = $request['payment-mode'];
        $data['maturity_amount'] = $request['maturity-amount'];
        $data['interest_rate'] = $request['interest_rate'];
        $data['tenure'] = $request['tenure'];
        break;
      case "flexi-fixed-deposit":
        $tenure = $request['tenure'];
        if ($tenure == 12) {
          $tenurefacode = $faCode . '001';
        } elseif ($tenure == 24) {
          $tenurefacode = $faCode . '002';
        } elseif ($tenure == 36) {
          $tenurefacode = $faCode . '003';
        } elseif ($tenure == 48) {
          $tenurefacode = $faCode . '004';
        } elseif ($tenure == 60) {
          $tenurefacode = $faCode . '005';
        } elseif ($tenure == 72) {
          $tenurefacode = $faCode . '006';
        } elseif ($tenure == 84) {
          $tenurefacode = $faCode . '007';
        } elseif ($tenure == 96) {
          $tenurefacode = $faCode . '008';
        } elseif ($tenure == 108) {
          $tenurefacode = $faCode . '009';
        } elseif ($tenure == 120) {
          $tenurefacode = $faCode . '010';
        }
        $data['tenure'] = $request['tenure'] / 12;
        $data['tenure_fa_code'] = $tenurefacode;
        $data['deposite_amount'] = $request['amount'];
        $data['payment_mode'] = $request['payment-mode'];
        $data['maturity_amount'] = $request['maturity-amount'];
        $data['interest_rate'] = $request['interest_rate'];
        break;
      case "fixed-recurring-deposit":
        $data['tenure'] = $request['tenure'] / 12;
        $data['deposite_amount'] = $request['amount'];
        $data['payment_mode'] = $request['payment-mode'];
        $data['maturity_amount'] = $request['maturity-amount'];
        $data['interest_rate'] = $request['interest_rate'];
        break;
      case "samraddh-jeevan":
        $data['tenure'] = $request['tenure'] / 12;
        $data['deposite_amount'] = $request['amount'];
        $data['payment_mode'] = $request['payment-mode'];
        $data['maturity_amount'] = $request['maturity-amount'];
        $data['interest_rate'] = $request['interest_rate'];
        break;
      case "daily-deposit":
        $tenure = $request['tenure'];
        if ($tenure == 12) {
          $tenurefacode = $faCode . '001';
        } elseif ($tenure == 24) {
          $tenurefacode = $faCode . '002';
        } elseif ($tenure == 36) {
          $tenurefacode = $faCode . '003';
        } elseif ($tenure == 48) {
          $tenurefacode = $faCode . '004';
        } elseif ($tenure == 60) {
          $tenurefacode = $faCode . '005';
        }
        $data['tenure'] = $request['tenure'] / 12;
        $data['tenure_fa_code'] = $tenurefacode;
        $data['deposite_amount'] = $request['amount'];
        $data['payment_mode'] = $request['payment-mode'];
        $data['interest_rate'] = $request['interest_rate'];
        $data['maturity_amount'] = $request['maturity-amount'];
        break;
      case "monthly-income-scheme":
        $data['deposite_amount'] = $request['amount'];
        $data['payment_mode'] = $request['payment-mode'];
        $data['tenure'] = $request['tenure'] / 12;
        $data['maturity_amount'] = $request['maturity-amount'];
        $data['interest_rate'] = $request['interest_rate'];
        break;
      case "fixed-deposit":
        $data['deposite_amount'] = $request['amount'];
        $data['payment_mode'] = $request['payment-mode'];
        $data['tenure'] = $request['tenure'] / 12;
        $data['maturity_amount'] = $request['maturity-amount'];
        $data['interest_rate'] = $request['interest_rate'];
        break;
      case "recurring-deposit":
        $tenure = $request['tenure'];
        if ($tenure == 36) {
          $tenurefacode = $faCode . '002';
        } elseif ($tenure == 60) {
          $tenurefacode = $faCode . '003';
        } elseif ($tenure == 84) {
          $tenurefacode = $faCode . '004';
        } else {
          $tenurefacode = $faCode . '001';
        }
        $data['tenure'] = $request['tenure'] / 12;
        $data['tenure_fa_code'] = $tenurefacode;
        $data['deposite_amount'] = $request['amount'];
        $data['payment_mode'] = $request['payment-mode'];
        $data['maturity_amount'] = $request['maturity-amount'];
        $data['interest_rate'] = $request['interest_rate'];
        break;
      case "samraddh-bhavhishya":
        $data['deposite_amount'] = $request['amount'];
        $data['tenure'] = $request['tenure'] / 12;
        $data['maturity_amount'] = $request['maturity-amount'];
        $data['interest_rate'] = $request['interest_rate'];
        break;
    }
    Session::put('created_at', $request['created_at']);
    $insertedid = $request['investmentId'];
    $planId = $request['investmentplan'];
    if ($plantype != 'saving-account' && $plantype != 'saving-account-child') {
      if (($request['amount'] != $request['hidden_amount']) || ($request['tenure'] != $request['hidden_tenure'])) {
        $dayBookId = Daybook::where('investment_id', $insertedid)->where('transaction_type', 2)->first('id');
        if ($dayBookId) {
          $createDayBook = $dayBookId->id;
          /*------------Delete Old  Commission  start ----------*/
          $commission = CommanController::commissionDelete($createDayBook, $insertedid);
          /*------------Delete Old Commission start ----------*/
          //* ------------------ commission genarate-----------------*/
          $commission = CommanController::commissionDistributeInvestment($request['associatemid'], $insertedid, 3, $request['amount'], 1, $planId, $branch_id, $request['tenure'], $createDayBook);
          $commission_collection = CommanController::commissionCollectionInvestment($request['associatemid'], $insertedid, 5, $request['amount'], 1, $planId, $branch_id, $request['tenure'], $createDayBook);
          /*----- ------  credit business start ---- ---------------*/
          $creditBusiness = CommanController::associateCreditBusiness($request['associatemid'], $insertedid, 1, $request['amount'], 1, $planId, $request['tenure'], $createDayBook);
          /*----- ------  credit business end ---- ---------------*/
          /* ------------------ commission genarate-----------------*/
        }
      }
    }
    $data['form_number'] = $request['form_number'];
    $data['associate_id'] = $request['associatemid'];
    $data['branch_id'] = $branch_id;
    $data['old_branch_id'] = $branch_id;
    return $data;
  }
  /**
   * Get investment plans first nominee data to store.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\Response
   */
  public function getFirstNomineeData($request, $investmentId, $type)
  {
    $data = [
      'investment_id' => $investmentId,
      'nominee_type' => 0,
      'name' => $request['fn_first_name'],
      //'second_name' => $request['fn_second_name'],
      'relation' => $request['fn_relationship'],
      'gender' => $request['fn_gender'],
      'dob' => date("Y-m-d", strtotime(str_replace('/', '-', $request['fn_dob']))),
      'age' => $request['fn_age'],
      'percentage' => $request['fn_percentage'],
      //'phone_number' => $request['fn_mobile_number'],
    ];
    return $data;
  }
  /**
   * Get investment plans first nominee data to update.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\Response
   */
  public function updateFirstNomineeData($request, $investmentId, $type)
  {
    $data = [
      'nominee_type' => 0,
      'name' => $request['fn_first_name'],
      'relation' => $request['fn_relationship'],
      'gender' => $request['fn_gender'],
      'dob' => date("Y-m-d", strtotime(str_replace('/', '-', $request['fn_dob']))),
      'age' => $request['fn_age'],
      'percentage' => $request['fn_percentage'],
    ];
    return $data;
  }
  /**
   * Get investment plans second nominee data to store.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\Response
   */
  public function getSecondNomineeData($request, $investmentId, $type)
  {
    $data = [
      'investment_id' => $investmentId,
      'nominee_type' => 1,
      'name' => $request['sn_first_name'],
      //'second_name' => $request['sn_second_name'],
      'relation' => $request['sn_relationship'],
      'gender' => $request['sn_gender'],
      'dob' => date("Y-m-d", strtotime(str_replace('/', '-', $request['sn_dob']))),
      'age' => $request['sn_age'],
      'percentage' => $request['sn_percentage'],
      //'phone_number' => $request['sn_mobile_number'],
    ];
    return $data;
  }
  /**
   * Get investment plans second nominee data to update.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\Response
   */
  public function updatesecondNomineeData($request, $investmentId, $type)
  {
    $data = [
      'nominee_type' => 1,
      'name' => $request['sn_first_name'],
      'relation' => $request['sn_relationship'],
      'gender' => $request['sn_gender'],
      'dob' => date("Y-m-d", strtotime(str_replace('/', '-', $request['sn_dob']))),
      'age' => $request['sn_age'],
      'percentage' => $request['sn_percentage'],
    ];
    return $data;
  }
  /**
   * Get investment plans payment method data to store.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\Response
   */
  public function getPaymentMethodData($request, $investmentId, $type)
  {
    switch ($request['payment-mode']) {
      case "1":
        $data['cheque_number'] = $request['cheque-number'];
        $data['bank_name'] = $request['bank-name'];
        $data['branch_name'] = $request['branch-name'];
        $data['cheque_date'] = date("Y-m-d", strtotime(str_replace('/', '-', $request['cheque-date'])));
        break;
      default:
        $data['transaction_id'] = $request['transaction-id'];
        $data['transaction_date'] = date("Y-m-d", strtotime(str_replace('/', '-', $request['date'])));
    }
    $data['investment_id'] = $investmentId;
    return $data;
  }
  /**
   * Get investment plans payment method data to update.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\Response
   */
  public function updatePaymentMethodData($request, $investmentId, $type)
  {
    switch ($request['payment-mode']) {
      case "1":
        $data['cheque_number'] = $request['cheque-number'];
        $data['bank_name'] = $request['bank-name'];
        $data['branch_name'] = $request['branch-name'];
        $data['cheque_date'] = date("Y-m-d", strtotime(str_replace('/', '-', $request['cheque-date'])));
        break;
      default:
        $data['transaction_id'] = $request['transaction-id'];
        $data['transaction_date'] = date("Y-m-d", strtotime(str_replace('/', '-', $request['date'])));
    }
    $data['investment_id'] = $investmentId;
    return $data;
  }
  /**
   * Get investment plans transaction data to store.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\Response
   */
  public function transactionData($satRefId, $request, $investmentId, $type, $transactionId)
  {
    $getBranchId = getUserBranchId(Auth::user()->id);
    $branch_id = $getBranchId->id;
    $getBranchCode = getBranchCode($branch_id);
    $branchCode = $getBranchCode->branch_code;
    $sAccount = $this->getSavingAccountDetails($request['memberAutoId']);
    $data['transaction_id'] = $transactionId;
    $data['transaction_ref_id'] = $satRefId;
    $data['investment_id'] = $investmentId;
    $data['plan_id'] = $request['investmentplan'];
    $data['member_id'] = $request['memberAutoId'];
    $data['branch_id'] = $branch_id;
    $data['branch_code'] = $branchCode;
    $data['deposite_amount'] = $request['amount'];
    $data['deposite_date'] = date('Y-m-d');
    $data['deposite_month'] = date('m');
    if ($request['plan_type'] == 'saving-account' || $request['plan_type'] == 'saving-account-child') {
      $data['payment_mode'] = 0;
    } else {
      $data['payment_mode'] = $request['payment-mode'];
    }
    if (count($sAccount) > 0) {
      $data['saving_account_id'] = $sAccount[0]->id;
    } else {
      $data['saving_account_id'] = NULL;
    }
    $data['created_at'] = $request['created_at'];
    return $data;
  }
  /**
   * Get investment plans transaction data to store.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\Response
   */
  public function updateTransactionData($request, $investmentId, $type)
  {
    $getBranchId = getUserBranchId(Auth::user()->id);
    $branch_id = $getBranchId->id;
    $getBranchCode = getBranchCode($branch_id);
    $branchCode = $getBranchCode->branch_code;
    $sAccount = $this->getSavingAccountDetails($request['memberAutoId']);
    $data['branch_id'] = $branch_id;
    $data['branch_code'] = $branchCode;
    if ($request['plan_type'] != 'saving-account-child') {
      $data['deposite_amount'] = $request['amount'];
    } else {
      $data['deposite_amount'] = 0;
    }
    $data['deposite_date'] = date('Y-m-d');
    $data['deposite_month'] = date('m');
    if ($request['plan_type'] == 'saving-account' || $request['plan_type'] == 'saving-account-child') {
      $data['payment_mode'] = 0;
    } else {
      $data['payment_mode'] = $request['payment-mode'];
    }
    if (count($sAccount) > 0) {
      $data['saving_account_id'] = $sAccount[0]->id;
    } else {
      $data['saving_account_id'] = NULL;
    }
    return $data;
  }
  /**
   * Get comman transaction log data to store.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\Response
   */
  public function commonTransactionLogData($satRefId, $request, $investmentId, $type)
  {
    $amount_deposit_by_name = substr($request['member_name'], 0, strpos($request['member_name'], "-"));
    $getBranchId = getUserBranchId(Auth::user()->id);
    $branch_id = $getBranchId->id;
    $getBranchCode = getBranchCode($branch_id);
    $branchCode = $getBranchCode->branch_code;
    $sAccount = $this->getSavingAccountDetails($request['memberAutoId']);
    $investmentAccount = $this->getInvestmentAccountNumber($investmentId);
    if (count($sAccount) > 0) {
      $ssbAccountNumber = $sAccount[0]->account_no;
      $ssbId = $sAccount[0]->id;
    } else {
      $ssbAccountNumber = '';
      $ssbId = '';
    }
    $amountArraySsb = array('1' => $request['amount']);
    Session::put('created_at', $request['created_at']);
    switch ($request['payment-mode']) {
      case "0":
        $createTransaction = CommanController::createTransaction($satRefId = NULL, 2, $investmentId, $request['memberAutoId'], $branch_id, $branchCode, $amountArraySsb, 0, $amount_deposit_by_name, $request['memberAutoId'], $ssbAccountNumber, $cheque_dd_no = '0', $bank_name = NULL, $branch_name = NULL, $payment_date = date("Y-m-d ", strtotime($request['created_at'])), $online_payment_id = NULL, $online_payment_by = NULL, $ssbId, 'CR');
        break;
      case "1":
        $createTransaction = CommanController::createTransaction($satRefId = NULL, 2, $investmentId, $request['memberAutoId'], $branch_id, $branchCode, $amountArraySsb, 1, $amount_deposit_by_name, $request['memberAutoId'], $ssbAccountNumber, $request['cheque-number'], $request['bank-name'], $request['branch-name'], $request['cheque-date'], $request['transaction-id'], $online_payment_by = NULL, $ssbId, 'CR');
        break;
      case "2":
        $createTransaction = CommanController::createTransaction($satRefId = NULL, 2, $investmentId, $request['memberAutoId'], $branch_id, $branchCode, $amountArraySsb, 3, $amount_deposit_by_name, $request['memberAutoId'], $ssbAccountNumber, $cheque_dd_no = '0', $bank_name = NULL, $branch_name = NULL, $payment_date = date("Y-m-d ", strtotime($request['created_at'])), $online_payment_id = NULL, $online_payment_by = NULL, $ssbId, 'CR');
        break;
      case "3":
        $createTransaction = CommanController::createTransaction($satRefId = NULL, 2, $investmentId, $request['memberAutoId'], $branch_id, $branchCode, $amountArraySsb, 4, $amount_deposit_by_name, $request['memberAutoId'], $ssbAccountNumber, $cheque_dd_no = '0', $bank_name = NULL, $branch_name = NULL, $payment_date = date("Y-m-d ", strtotime($request['created_at'])), $online_payment_id = NULL, $online_payment_by = NULL, $ssbId, 'CR');
        break;
    }
    return $createTransaction;
  }
  /**
   * Get comman transaction log data to store.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\Response
   */
  public function updateCommonTransactionLogData($request, $investmentId, $type)
  {
    $branch_id = $request['branchid'];
    $getBranchCode = getBranchCode($branch_id);
    $branchCode = $getBranchCode->branch_code;
    if ($request['plan_type'] == 'saving-account-child') {
      $request['payment-mode'] = 0;
      $amountArraySsb = array('1' => 0);
    } else {
      $amountArraySsb = array('1' => $request['amount']);
    }
    // $amountArraySsb=array('1'=>$request['amount']);
    $ssbId = $request['ssb_id'];
    Session::put('created_at', $request['created_at']);
    switch ($request['payment-mode']) {
      case "0":
        $createTransaction = CommanController::updateTransaction($investmentId, $branch_id, $branchCode, $amountArraySsb, 0, $cheque_dd_no = '0', $bank_name = NULL, $branch_name = NULL, $payment_date = NULL, $online_payment_id = NULL, $online_payment_by = NULL, $ssbId = 0, 'CR');
        break;
      case "1":
        $createTransaction = CommanController::updateTransaction($investmentId, $branch_id, $branchCode, $amountArraySsb, 1, $request['cheque-number'], $request['bank-name'], $request['branch-name'], $request['cheque-date'], $request['transaction-id'], $online_payment_by = NULL, $ssbId, 'CR');
        break;
      case "2":
        $createTransaction = CommanController::updateTransaction($investmentId, $branch_id, $branchCode, $amountArraySsb, 3, $cheque_dd_no = '0', $bank_name = NULL, $branch_name = NULL, $payment_date = NULL, $online_payment_id = NULL, $online_payment_by = NULL, $ssbId = 0, 'CR');
        break;
      case "3":
        $createTransaction = CommanController::updateTransaction($investmentId, $branch_id, $branchCode, $amountArraySsb, 4, $cheque_dd_no = '0', $bank_name = NULL, $branch_name = NULL, $payment_date = NULL, $online_payment_id = NULL, $online_payment_by = NULL, $ssbId = 0, 'CR');
        break;
    }
    return $createTransaction;
  }
  /**
   * Get savving account transaction log data to store.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\Response
   */
  public function savingAccountTransactionData($request, $investmentId, $type)
  {
    $sAccount = $this->getSavingAccountDetails($request['memberAutoId']);
    $data['associate_id'] = $request['associatemid'];
    $data['branch_id'] = $request['branchid'];
    $data['type'] = 6;
    $data['saving_account_id'] = $sAccount[0]->id;
    $data['account_no'] = $sAccount[0]->account_no;
    $data['opening_balance'] = $sAccount[0]->balance - $request['amount'];
    $data['withdrawal'] = $request['amount'];
    $data['description'] = $sAccount[0]->account_no . '/Auto debit';
    $data['currency_code'] = 'INR';
    $data['payment_type'] = 'DR';
    $data['payment_mode'] = 3;
    //$data['reference_no'] = '';
    $data['status'] = 1;
    return $data;
  }
  public function savingAccountTransactionDataNew($request, $investmentId, $type, $plan_name, $account_no)
  {
    $sAccount = $this->getSavingAccountDetails($request['memberAutoId']);
    $data['associate_id'] = $request['associatemid'];
    $data['branch_id'] = $request['branchid'];
    $data['type'] = 6;
    $data['saving_account_id'] = $sAccount[0]->id;
    $data['account_no'] = $sAccount[0]->account_no;
    $data['opening_balance'] = $sAccount[0]->balance - $request['amount'];
    $data['withdrawal'] = $request['amount'];
    $data['description'] = 'Payment transferred to ' . $plan_name . '(' . $account_no . ')';
    $data['currency_code'] = 'INR';
    $data['payment_type'] = 'DR';
    $data['payment_mode'] = 3;
    //$data['reference_no'] = '';
    $data['status'] = 1;
    return $data;
  }
  /**
   * Get saving account id.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\Response
   */
  public function getSavingAccountDetails($mId)
  {
    $getDetails = SavingAccount::where('member_id', $mId)->get();
    return $getDetails;
  }
  /**
   * Get investment plan account number.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\Response
   */
  public function getInvestmentAccountNumber($id)
  {
    $getId = Memberinvestments::select('account_number')->where('id', $id)->get();
    return $getId[0]->account_number;
  }
  /**
   * Show recipt detail after create plan
   * Method: get
   * @param  $id
   * @return  array()  Response
   */
  public function planRecipt($id)
  {
    $data['title'] = 'Investment Plan | Receipt';
    $data['investmentDetails'] = Memberinvestments::with('member', 'investmentNomiees')->with([
      'plan' => function ($q) {
        $q->withoutGlobalScope(ActiveScope::class);
      }
    ])->where('id', $id)->get();
    $reciptTemplate = $data['investmentDetails'][0]['plan']->slug;
    $data['associateDetails'] = Member::with('savingAccount')->where('id', $data['investmentDetails'][0]->associate_id)->get();
    if ($data['investmentDetails'][0]['payment_mode'] == 0) {
      $data['paymentType'] = "Cash";
    } elseif ($data['investmentDetails'][0]['payment_mode'] == 1) {
      $data['paymentType'] = "Cheque";
    } elseif ($data['investmentDetails'][0]['payment_mode'] == 2) {
      $data['paymentType'] = "Online Transfer";
    } else {
      $data['paymentType'] = "SSB Account";
    }
    return view('templates.admin.investment_management.' . $reciptTemplate . '/' . $reciptTemplate . '-recipt', $data);
  }
  /**
   * Show commission detail after create plan
   * Method: get
   * @param  $id
   * @return  array()  Response
   */
  public function investmentCommission($id)
  {
    $data['title'] = 'Investment Plan | Commission';
    $data['investment'] = getInvestmentDetails($id);
    $data['investmentId'] = $id;
    return view('templates.admin.investment_management.commission', $data);
  }
  /**
   * Fetch invest listing data.
   *
   * @param  \App\Reservation  $reservation
   * @return \Illuminate\Http\Response
   */
  public function investmentCommissionListing(Request $request)
  {
    if ($request->ajax()) {
      $arrFormData = array();
      if (!empty($_POST['searchform'])) {
        foreach ($_POST['searchform'] as $frm_data) {
          $arrFormData[$frm_data['name']] = $frm_data['value'];
        }
      }
      $investmentId = $request->id;
      $mid = Member::where('associate_no', '9999999')->first('id');
      $data = AssociateCommission::where('member_id', '!=', $mid->id)->where('type', '>', 2)->where('type_id', $investmentId);
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
      }
      $data = $data->orderby('id', 'DESC')->get();
      return Datatables::of($data)
        ->addIndexColumn()
        ->addColumn('transaction_id', function ($row) {
          $transaction_id = $row->day_book_id;
          return $transaction_id;
        })
        ->rawColumns(['transaction_id'])
        ->addColumn('member_id', function ($row) {
          $member = getMemberDetails($row->member_id);
          return $member->associate_no;
        })
        ->rawColumns(['member_id'])
        ->addColumn('member_name', function ($row) {
          $member_name = getMemberDetails($row->member_id);
          return $member_name->first_name . ' ' . $member_name->last_name;
        })
        ->rawColumns(['member_name'])
        ->addColumn('associateCarder', function ($row) {
          $member = getMemberDetails($row->member_id);
          return getCarderName($member->current_carder_id);
        })
        ->rawColumns(['associateCarder'])
        ->addColumn('total_amount', function ($row) {
          return round($row->total_amount, 2);
        })
        ->rawColumns(['total_amount'])
        ->addColumn('commission_amount', function ($row) {
          return round($row->commission_amount, 2);
        })
        ->rawColumns(['commission_amount'])
        ->addColumn('percentage', function ($row) {
          return round($row->percentage, 2);
        })
        ->rawColumns(['percentage'])
        ->addColumn('carder_name', function ($row) {
          if ($row->type == 5) {
            $carder_name = 'Collection Charge';
          } else {
            $carder_name = getCarderName($row->carder_id);
          }
          return $carder_name;
        })
        ->rawColumns(['emi_no'])
        ->addColumn('emi_no', function ($row) {
          $get_plan = getInvestmentDetails($row->type_id);
          if ($get_plan->plan_id == 7) {
            if ($row->month > 1) {
              $emi_no = $row->month . ' Days';
            } else {
              $emi_no = $row->month . ' Day';
            }
          } else {
            if ($row->month > 1) {
              $emi_no = $row->month . ' Months';
            } else {
              $emi_no = $row->month . ' Month';
            }
          }
          return $emi_no;
        })
        ->rawColumns(['emi_no'])
        ->addColumn('commission_type', function ($row) {
          if ($row->commission_type == 0) {
            $commission_type = 'Self';
          } else {
            $commission_type = 'Team Member';
          }
          return $commission_type;
        })
        ->rawColumns(['commission_type'])
        ->addColumn('associate_exist', function ($row) {
          if ($row->associate_exist == 0) {
            $associate_exist = 'Yes';
          } else {
            $associate_exist = 'No';
          }
          return $associate_exist;
        })
        ->rawColumns(['associate_exist'])
        ->addColumn('pay_type', function ($row) {
          if ($row->pay_type == 1) {
            $pay_type = 'OverDue';
          } elseif ($row->pay_type == 2) {
            $pay_type = 'Due Date';
          } else {
            $pay_type = 'Advance';
          }
          return $pay_type;
        })
        ->rawColumns(['pay_type'])
        ->addColumn('is_distribute', function ($row) {
          if ($row->is_distribute == 1) {
            $is_distribute = 'Yes';
          } else {
            $is_distribute = 'No';
          }
          return $is_distribute;
        })
        ->rawColumns(['is_distribute'])
        ->addColumn('created_at', function ($row) {
          $created_at = date("d/m/Y", strtotime($row->created_at));
          return $created_at;
        })
        ->rawColumns(['created_at'])
        ->make(true);
    }
  }
  /**
   * Show Investment associate change.
   * Route: admin/investment-associate
   * Method: get
   * @return  array()  Response
   */
  public function investmentAssociate()
  {
    if (check_my_permission(Auth::user()->id, "20") != "1") {
      return redirect()->route('admin.dashboard');
    }
    $data['title'] = 'Investment Plans | Associate Change';
    return view('templates.admin.investment_management.associate_change', $data);
  }
  /**
   * Get investment detail through account no.
   * Route: admin/investment-associate
   * Method: Post
   * @param  \Illuminate\Http\Request  $request
   * @return JSON array
   */
  public function investmentDataGet(Request $request)
  {
    $data = Memberinvestments::with([
      'plan' => function ($query) {
        $query->select('id', 'name');
      }
    ])->with([
          'associateMember' => function ($query) {
            $query->select('id', 'first_name', 'last_name', 'mobile_no', 'current_carder_id', 'associate_status', 'associate_no', 'member_id');
          },'member'
        ])->where('account_number', $request->code);
    if (Auth::user()->branch_id > 0) {
      $data = $data->where('branch_id', Auth::user()->branch_id);
    }
    $data = $data->first();
    if ($data) {
      if ($data->plan_id != 1) {
        return \Response::json(['view' => view('templates.admin.investment_management.partials.investment_detail', ['investData' => $data])->render(), 'msg_type' => 'success']);
      } else {
        return \Response::json(['view' => 'No data found', 'msg_type' => 'error1']);
      }
    } else {
      return \Response::json(['view' => 'No data found', 'msg_type' => 'error']);
    }
  }
  /**
   * Route: admin/investment-associate
   * Method: Post
   * @param  \Illuminate\Http\Request  $request
   * upgrade associate carder.
   * @return  array()  Response
   */
  public function investmentAssociateSave(Request $request)
  {
    // print_r($_POST);die;
    $rules = [
      'account_no' => ['required'],
      'old_associate_code' => ['required'],
      'new_associate' => ['required'],
      'investment_id' => ['required', 'numeric'],
      'old_associate_id' => ['required', 'numeric'],
      'new_associate_senior_id' => ['required', 'numeric'],
    ];
    $customMessages = [
      'old_associate_code' => 'Please enter associate code',
      'numeric' => ':Attribute - Please enter valid.',
      'unique' => ' :Attribute already exists.'
    ];
    $this->validate($request, $rules, $customMessages);
    DB::beginTransaction();
    try {
      $investment_id = $request['investment_id'];
      $old_associate_id = $request['old_associate_id'];
      $new_associate_senior_id = $request['new_associate_senior_id'];
      $investment['associate_id'] = $new_associate_senior_id;
      $investment['updated_at'] = $request->created_at;
      $investmentDataUpdate = Memberinvestments::find($investment_id);
      $investmentDataUpdate->update($investment);
      $investAssociate['investment_id'] = $investment_id;
      $investAssociate['old_associate_id'] = $old_associate_id;
      $investAssociate['current_associate_id'] = $new_associate_senior_id;
      $investAssociate['status'] = 1;
      $investAssociate['created_at'] = $request->created_at;
      $investAssociate['updated_at'] = $request->created_at;
      $investAssociateCreate = \App\Models\MemberInvestmentAssociate::create($investAssociate);
      $encodeDate = json_encode($investAssociate);
      // $arrs = array("member_investment_associate_id" => $investAssociateCreate->id, "type" => "3", "account_head_id" => 0, "user_id" => Auth::user()->id, "message" => "Investment_Associate_Change", "data" => $encodeDate);
      // DB::table('user_log')->insert($arrs);
      DB::commit();
    } catch (\Exception $ex) {
      DB::rollback();
      return back()->with('alert', $ex->getMessage());
    }
    return back()->with('success', 'Investment Associate Updated Successfully!');
  }
  public function stationaryCharges($investmentId)
  {
    DB::beginTransaction();
    try {
      $memberInvestments = Memberinvestments::with(['branch', 'member'])->where('id', $investmentId)->first();
      $planArray = array(3, 6, 8);
      $dateTimeArary = array();
      $entryTime = date("H:i:s");
      Session::put('created_at', date("Y-m-d " . $entryTime . "", strtotime(convertDate($memberInvestments->created_at))));
      $vno = NULL;
      $branch_id = $memberInvestments->branch_id;
      $type = 3;
      $sub_type = 35;
      $type_id = $memberInvestments->id;
      $type_transaction_id = $memberInvestments->id;
      $associate_id = NULL;
      $member_id = $memberInvestments->member_id;
      $branch_id_to = NULL;
      $branch_id_from = NULL;
      $opening_balance = 50;
      $amount = 50;
      $closing_balance = 50;
      $description = 'Stationary charges on investment plan registration ' . $amount;
      $description_dr = 'Stationary charges on investment plan registration ' . $amount;
      $description_cr = 'Stationary charges on investment plan registration ' . $amount;
      $payment_type = 'CR';
      $payment_mode = 0;
      $currency_code = 'INR';
      $amount_to_id = NULL;
      $amount_to_name = NULL;
      $amount_from_id = NULL;
      $amount_from_name = NULL;
      $sub_type_cgst = 321;
      $sub_type_sgst = 322;
      $sub_type_igst = 323;
      $v_no = $vno;
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
      $transction_date = $memberInvestments->created_at;
      $entry_date = $memberInvestments->created_at;
      $entry_time = date("H:i:s");
      $created_by = 1;
      $created_by_id = 1;
      $is_contra = NULL;
      $contra_id = NULL;
      $created_at = $memberInvestments->created_at;
      $bank_id = NULL;
      $bank_ac_id = NULL;
      $transction_bank_to_name = NULL;
      $transction_bank_to_ac_no = NULL;
      $transction_bank_to_branch = NULL;
      $transction_bank_to_ifsc = NULL;
      $dayBookRef = CommanController::createBranchDayBookReference($amount);
      $detail = getBranchDetail($memberInvestments['branch']->id)->state_id;
      $globaldate = checkMonthAvailability(date('d'), date('m'), date('Y'), $detail);
      $getHeadSetting = \App\Models\HeadSetting::where('head_id', 122)->first();
      $getGstSetting = \App\Models\GstSetting::where('state_id', $memberInvestments['branch']->state_id)->where('applicable_date', '<=', $globaldate)->exists();
      $gstAmount = 0;
      $getGstSettingno = \App\Models\GstSetting::select('id', 'gst_no')->where('state_id', $memberInvestments['branch']->state_id)->where('applicable_date', '<=', $globaldate)->first();
      if (isset($getHeadSetting->gst_percentage) && $getGstSetting) {
        if ($memberInvestments['branch']->state_id == 33) {
          $gstAmount = ceil(($amount * $getHeadSetting->gst_percentage) / 100) / 2;
          $cgstHead = 171;
          $sgstHead = 172;
          $IntraState = true;
        } else {
          $gstAmount = ceil($amount * $getHeadSetting->gst_percentage) / 100;
          $cgstHead = 170;
          $IntraState = false;
        }
        $msg = true;
      } else {
        $IntraState = false;
        $msg = false;
      }
      /*if($memberInvestments->plan_id == 1){
      $checkRd = Memberinvestments::where('id','<',$memberInvestments->id)->where('plan_id',10)->first();
      if($checkRd){
      if($checkRd->deposite_amount != 500 && $memberInvestment['associate_senior_code'] != 9999999){
      $data['investment_id']=$memberInvestments->id;
      $data['created_at']=date("Y-m-d ".$entryTime."", strtotime(convertDate($memberInvestments->created_at)));
      //   $satRef = TransactionReferences::create($data);
      $satRefId = $satRef->id;
      $amountArraySsb = array('1'=>(50));
      $ssbCreateTran = CommanController::createTransaction($satRefId,18,$memberInvestments->id,$memberInvestments->member_id,$memberInvestments->branch_id,getBranchCode($memberInvestments->branch_id)->branch_code,$amountArraySsb,0,NULL,$memberInvestments->member_id,$memberInvestments->account_number,NULL,NULL,NULL,date("Y-m-d", strtotime(convertDate($memberInvestments->created_at))),NULL,NULL,NULL,'CR');
      $createDayBook = CommanController::createDayBook($ssbCreateTran,$satRefId,19,$memberInvestments->id,NULL,$memberInvestments->member_id,50,50,$withdrawal=0,'Stationary charges on investment plan registration',$memberInvestments->account_number,$memberInvestments->branch_id,getBranchCode($memberInvestments->branch_id)->branch_code,$amountArraySsb,0,NULL,$memberInvestments->member_id,$memberInvestments->account_number,50,NULL,NULL,date("Y-m-d", strtotime(convertDate($memberInvestments->created_at))),NULL,NULL,NULL,'CR');
      $allTransaction = CommanController::createAllTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,4,86,121,122,NULL,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$amount,$amount,$amount,$description,'CR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$memberInvestments->created_at);
      $allTransaction = CommanController::createAllTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,2,10,28,71,NULL,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$amount,$amount,$amount,$description,'CR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$memberInvestments->created_at);
      $branchDayBook = CommanController::createBranchDayBook($dayBookRef,$branch_id,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$amount,$amount,$amount,$description,$description_dr,$description_cr,'CR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$is_contra,$contra_id,$created_at,0);
      $memberTransaction = CommanController::createMemberTransaction($dayBookRef,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id,$bank_id,$bank_ac_id,$amount,$description,'CR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at);
      $this->updateBranchCashCr($memberInvestments->branch_id,$memberInvestments->created_at,50,0);
      $this->updateBranchClosingCashCr($memberInvestments->branch_id,$memberInvestments->created_at,50,0);
      }
      }else{
      $data['investment_id']=$memberInvestments->id;
      $data['created_at']=date("Y-m-d ".$entryTime."", strtotime(convertDate($memberInvestments->created_at)));
      //   $satRef = TransactionReferences::create($data);
      $satRefId = $satRef->id;
      $amountArraySsb = array('1'=>(50));
      $ssbCreateTran = CommanController::createTransaction($satRefId,18,$memberInvestments->id,$memberInvestments->member_id,$memberInvestments->branch_id,getBranchCode($memberInvestments->branch_id)->branch_code,$amountArraySsb,0,NULL,$memberInvestments->member_id,$memberInvestments->account_number,NULL,NULL,NULL,date("Y-m-d", strtotime(convertDate($memberInvestments->created_at))),NULL,NULL,NULL,'CR');
      $createDayBook = CommanController::createDayBook($ssbCreateTran,$satRefId,19,$memberInvestments->id,NULL,$memberInvestments->member_id,50,50,$withdrawal=0,'Stationary charges on investment plan registration',$memberInvestments->account_number,$memberInvestments->branch_id,getBranchCode($memberInvestments->branch_id)->branch_code,$amountArraySsb,0,NULL,$memberInvestments->member_id,$memberInvestments->account_number,50,NULL,NULL,date("Y-m-d", strtotime(convertDate($memberInvestments->created_at))),NULL,NULL,NULL,'CR');
      $allTransaction = CommanController::createAllTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,4,86,121,122,NULL,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$amount,$amount,$amount,$description,'CR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$memberInvestments->created_at);
      $allTransaction = CommanController::createAllTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,2,10,28,71,NULL,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$amount,$amount,$amount,$description,'CR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$memberInvestments->created_at);
      $branchDayBook = CommanController::createBranchDayBook($dayBookRef,$branch_id,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$amount,$amount,$amount,$description,$description_dr,$description_cr,'CR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$is_contra,$contra_id,$created_at,0);
      $memberTransaction = CommanController::createMemberTransaction($dayBookRef,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id,$bank_id,$bank_ac_id,$amount,$description,'CR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at);
      $this->updateBranchCashCr($memberInvestments->branch_id,$memberInvestments->created_at,50,0);
      $this->updateBranchClosingCashCr($memberInvestments->branch_id,$memberInvestments->created_at,50,0);
      }
      }else*/
      if (in_array($memberInvestments->plan_id, $planArray)) {
        $checkEntry = Memberinvestments::where('id', '<', $memberInvestments->id)->where('plan_id', 1)->first();
        if ($checkEntry) {
          $checkRd = Memberinvestments::where('id', '<', $checkEntry->id)->where('plan_id', 10)->first();
          if ($checkRd) {
            $data['investment_id'] = $memberInvestments->id;
            $data['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($memberInvestments->created_at)));
            //   $satRef = TransactionReferences::create($data);
            $satRefId = NULL;
            $amountArraySsb = array('1' => (50));
            $ssbCreateTran = CommanController::createTransaction($satRefId, 18, $memberInvestments->id, $memberInvestments->member_id, $memberInvestments->branch_id, getBranchCode($memberInvestments->branch_id)->branch_code, $amountArraySsb, 0, NULL, $memberInvestments->member_id, $memberInvestments->account_number, NULL, NULL, NULL, date("Y-m-d", strtotime(convertDate($memberInvestments->created_at))), NULL, NULL, NULL, 'CR');
            $createDayBook = CommanController::createDayBook($ssbCreateTran, $satRefId, 19, $memberInvestments->id, NULL, $memberInvestments->member_id, 50, 50, $withdrawal = 0, 'Stationary charges on investment plan registration', $memberInvestments->account_number, $memberInvestments->branch_id, getBranchCode($memberInvestments->branch_id)->branch_code, $amountArraySsb, 0, NULL, $memberInvestments->member_id, $memberInvestments->account_number, 50, NULL, NULL, date("Y-m-d", strtotime(convertDate($memberInvestments->created_at))), NULL, NULL, NULL, 'CR');
            $allTransaction = CommanController::createAllHeadTransaction($dayBookRef, $branch_id, $bank_id, $bank_ac_id, 122, $type, $sub_type, $type_id, $createDayBook, $associate_id, $member_id, $branch_id_to, $branch_id_from, $amount, $amount, $amount, $description, 'CR', $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $jv_unique_id = NULL, $v_no, $v_date, $ssb_account_id_from, $ssb_account_id_to = NULL, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $cheque_type = NULL, $cheque_id = NULL, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_from_id = NULL, $cheque_bank_ac_from_id = NULL, $cheque_bank_to, $cheque_bank_ac_to, NULL, $cheque_bank_to_branch = NULL, NULL, NULL, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_from_id = NULL, $transction_bank_from_ac_id = NULL, $transction_bank_to, $transction_bank_ac_to, $transction_bank_to_name = NULL, $transction_bank_to_ac_no = NULL, $transction_bank_to_branch = NULL, $transction_bank_to_ifsc = NULL, $memberInvestments->created_at, $created_by, $created_by_id);
            /*$allTransaction = CommanController::createAllTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,4,86,121,122,NULL,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$amount,$amount,$amount,$description,'CR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$memberInvestments->created_at);*/
            $allTransaction = CommanController::createAllHeadTransaction($dayBookRef, $branch_id, $bank_id, $bank_ac_id, 28, $type, $sub_type, $type_id, $createDayBook, $associate_id, $member_id, $branch_id_to, $branch_id_from, $amount, $amount, $amount, $description, 'DR', $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $jv_unique_id = NULL, $v_no, $v_date, $ssb_account_id_from, $ssb_account_id_to = NULL, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $cheque_type = NULL, $cheque_id = NULL, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_from_id = NULL, $cheque_bank_ac_from_id = NULL, $cheque_bank_to, $cheque_bank_ac_to, NULL, $cheque_bank_to_branch = NULL, NULL, NULL, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_from_id = NULL, $transction_bank_from_ac_id = NULL, $transction_bank_to, $transction_bank_ac_to, $transction_bank_to_name = NULL, $transction_bank_to_ac_no = NULL, $transction_bank_to_branch = NULL, $transction_bank_to_ifsc = NULL, $transction_date, $created_by, $created_by_id);
            /*$allTransaction = CommanController::createAllTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,2,10,28,71,NULL,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$amount,$amount,$amount,$description,'CR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$memberInvestments->created_at);*/
            $branchDayBook = CommanController::branchDayBookNew($dayBookRef, $branch_id, $type, $sub_type, $type_id, $type_transaction_id, $associate_id, $member_id, $branch_id_to, $branch_id_from, $amount, $amount, $amount, $description, $description_dr, $description_cr, 'CR', $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $is_contra, $contra_id, $created_at, 0, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL);
            $memberTransaction = CommanController::memberTransactionNew($dayBookRef, $type, $sub_type, $type_id, $type_transaction_id, $associate_id, $member_id, $branch_id, $bank_id, $bank_ac_id, $amount, $description, 'DR', $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL);
            $this->updateBranchCashCr($memberInvestments->branch_id, $memberInvestments->created_at, 50, 0);
            $this->updateBranchClosingCashCr($memberInvestments->branch_id, $memberInvestments->created_at, 50, 0);
          }
        } else {
          $data['investment_id'] = $memberInvestments->id;
          $data['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($memberInvestments->created_at)));
          //   $satRef = TransactionReferences::create($data);
          $satRefId = NULL;
          $amountArraySsb = array('1' => (50));
          $ssbCreateTran = CommanController::createTransaction($satRefId, 18, $memberInvestments->id, $memberInvestments->member_id, $memberInvestments->branch_id, getBranchCode($memberInvestments->branch_id)->branch_code, $amountArraySsb, 0, NULL, $memberInvestments->member_id, $memberInvestments->account_number, NULL, NULL, NULL, date("Y-m-d", strtotime(convertDate($memberInvestments->created_at))), NULL, NULL, NULL, 'CR');
          $createDayBook = CommanController::createDayBook($ssbCreateTran, $satRefId, 19, $memberInvestments->id, NULL, $memberInvestments->member_id, 50, 50, $withdrawal = 0, 'Stationary charges on investment plan registration', $memberInvestments->account_number, $memberInvestments->branch_id, getBranchCode($memberInvestments->branch_id)->branch_code, $amountArraySsb, 0, NULL, $memberInvestments->member_id, $memberInvestments->account_number, 50, NULL, NULL, date("Y-m-d", strtotime(convertDate($memberInvestments->created_at))), NULL, NULL, NULL, 'CR');
          $allTransaction = CommanController::createAllHeadTransaction($dayBookRef, $branch_id, $bank_id, $bank_ac_id, 122, $type, $sub_type, $type_id, $createDayBook, $associate_id, $member_id, $branch_id_to, $branch_id_from, $amount, $amount, $amount, $description, 'CR', $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $jv_unique_id = NULL, $v_no, $v_date, $ssb_account_id_from, $ssb_account_id_to = NULL, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $cheque_type = NULL, $cheque_id = NULL, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_from_id = NULL, $cheque_bank_ac_from_id = NULL, $cheque_bank_to, $cheque_bank_ac_to, NULL, $cheque_bank_to_branch = NULL, NULL, NULL, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_from_id = NULL, $transction_bank_from_ac_id = NULL, $transction_bank_to, $transction_bank_ac_to, $transction_bank_to_name = NULL, $transction_bank_to_ac_no = NULL, $transction_bank_to_branch = NULL, $transction_bank_to_ifsc = NULL, $transction_date, $created_by, $created_by_id);
          /*$allTransaction = CommanController::createAllTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,4,86,121,122,NULL,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$amount,$amount,$amount,$description,'CR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$memberInvestments->created_at);*/
          $allTransaction = CommanController::createAllHeadTransaction($dayBookRef, $branch_id, $bank_id, $bank_ac_id, 28, $type, $sub_type, $type_id, $createDayBook, $associate_id, $member_id, $branch_id_to, $branch_id_from, $amount, $amount, $amount, $description, 'DR', $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $jv_unique_id = NULL, $v_no, $v_date, $ssb_account_id_from, $ssb_account_id_to = NULL, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $cheque_type = NULL, $cheque_id = NULL, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_from_id = NULL, $cheque_bank_ac_from_id = NULL, $cheque_bank_to, $cheque_bank_ac_to, NULL, $cheque_bank_to_branch = NULL, NULL, NULL, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_from_id = NULL, $transction_bank_from_ac_id = NULL, $transction_bank_to, $transction_bank_ac_to, $transction_bank_to_name = NULL, $transction_bank_to_ac_no = NULL, $transction_bank_to_branch = NULL, $transction_bank_to_ifsc = NULL, $transction_date, $created_by, $created_by_id);
          /*$allTransaction = CommanController::createAllTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,2,10,28,71,NULL,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$amount,$amount,$amount,$description,'CR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$memberInvestments->created_at);*/
          $branchDayBook = CommanController::branchDayBookNew($dayBookRef, $branch_id, $type, $sub_type, $type_id, $type_transaction_id, $associate_id, $member_id, $branch_id_to, $branch_id_from, $amount, $amount, $amount, $description, $description_dr, $description_cr, 'CR', $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $is_contra, $contra_id, $created_at, 0, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL);
          $memberTransaction = CommanController::memberTransactionNew($dayBookRef, $type, $sub_type, $type_id, $type_transaction_id, $associate_id, $member_id, $branch_id, $bank_id, $bank_ac_id, $amount, $description, 'DR', $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL);
          $this->updateBranchCashCr($memberInvestments->branch_id, $memberInvestments->created_at, 50, 0);
          $this->updateBranchClosingCashCr($memberInvestments->branch_id, $memberInvestments->created_at, 50, 0);
        }
      } else {
        $data['investment_id'] = $memberInvestments->id;
        $data['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($memberInvestments->created_at)));
        //   $satRef = TransactionReferences::create($data);
        $satRefId = NULL;
        $amountArraySsb = array('1' => (50));
        $ssbCreateTran = CommanController::createTransaction($satRefId, 18, $memberInvestments->id, $memberInvestments->member_id, $memberInvestments->branch_id, getBranchCode($memberInvestments->branch_id)->branch_code, $amountArraySsb, 0, NULL, $memberInvestments->member_id, $memberInvestments->account_number, NULL, NULL, NULL, date("Y-m-d", strtotime(convertDate($memberInvestments->created_at))), NULL, NULL, NULL, 'CR');
        $createDayBook = CommanController::createDayBook($ssbCreateTran, $satRefId, 19, $memberInvestments->id, NULL, $memberInvestments->member_id, 50, 50, $withdrawal = 0, 'Stationary charges on investment plan registration', $memberInvestments->account_number, $memberInvestments->branch_id, getBranchCode($memberInvestments->branch_id)->branch_code, $amountArraySsb, 0, NULL, $memberInvestments->member_id, $memberInvestments->account_number, 50, NULL, NULL, date("Y-m-d", strtotime(convertDate($memberInvestments->created_at))), NULL, NULL, NULL, 'CR');
        $allTransaction = CommanController::createAllHeadTransaction($dayBookRef, $branch_id, $bank_id, $bank_ac_id, 122, $type, $sub_type, $type_id, $createDayBook, $associate_id, $member_id, $branch_id_to, $branch_id_from, $amount, $amount, $amount, $description, 'CR', $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $jv_unique_id = NULL, $v_no, $v_date, $ssb_account_id_from, $ssb_account_id_to = NULL, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $cheque_type = NULL, $cheque_id = NULL, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_from_id = NULL, $cheque_bank_ac_from_id = NULL, $cheque_bank_to, $cheque_bank_ac_to, NULL, $cheque_bank_to_branch = NULL, NULL, NULL, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_from_id = NULL, $transction_bank_from_ac_id = NULL, $transction_bank_to, $transction_bank_ac_to, $transction_bank_to_name = NULL, $transction_bank_to_ac_no = NULL, $transction_bank_to_branch = NULL, $transction_bank_to_ifsc = NULL, $transction_date, $created_by, $created_by_id);
        /*$allTransaction = CommanController::createAllTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,4,86,121,122,NULL,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$amount,$amount,$amount,$description,'CR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$memberInvestments->created_at);*/
        $allTransaction = CommanController::createAllHeadTransaction($dayBookRef, $branch_id, $bank_id, $bank_ac_id, 28, $type, $sub_type, $type_id, $createDayBook, $associate_id, $member_id, $branch_id_to, $branch_id_from, $amount, $amount, $amount, $description, 'DR', $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $jv_unique_id = NULL, $v_no, $v_date, $ssb_account_id_from, $ssb_account_id_to = NULL, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $cheque_type = NULL, $cheque_id = NULL, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_from_id = NULL, $cheque_bank_ac_from_id = NULL, $cheque_bank_to, $cheque_bank_ac_to, NULL, $cheque_bank_to_branch = NULL, NULL, NULL, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_from_id = NULL, $transction_bank_from_ac_id = NULL, $transction_bank_to, $transction_bank_ac_to, $transction_bank_to_name = NULL, $transction_bank_to_ac_no = NULL, $transction_bank_to_branch = NULL, $transction_bank_to_ifsc = NULL, $transction_date, $created_by, $created_by_id);
        /*$allTransaction = CommanController::createAllTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,2,10,28,71,NULL,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$amount,$amount,$amount,$description,'CR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$memberInvestments->created_at);*/
        $branchDayBook = CommanController::branchDayBookNew($dayBookRef, $branch_id, $type, $sub_type, $type_id, $type_transaction_id, $associate_id, $member_id, $branch_id_to, $branch_id_from, $amount, $amount, $amount, $description, $description_dr, $description_cr, 'CR', $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $is_contra, $contra_id, $created_at, 0, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL);
        $memberTransaction = CommanController::memberTransactionNew($dayBookRef, $type, $sub_type, $type_id, $type_transaction_id, $associate_id, $member_id, $branch_id, $bank_id, $bank_ac_id, $amount, $description, 'DR', $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL);
        $this->updateBranchCashCr($memberInvestments->branch_id, $memberInvestments->created_at, 50, 0);
        $this->updateBranchClosingCashCr($memberInvestments->branch_id, $memberInvestments->created_at, 50, 0);
      }
      if ($gstAmount > 0) {
        $createdGstTransaction = CommanController::gstTransaction($dayBookId = $dayBookRef, $getGstSettingno->gst_no, (!isset($memberInvestments['member']->gst_no)) ? NULL : $memberInvestments['member']->gst_no, $amount, $getHeadSetting->gst_percentage, ($IntraState == false ? $gstAmount : 0), ($IntraState == true ? $gstAmount : 0), ($IntraState == true ? $gstAmount : 0), ($IntraState == true) ? $amount + $gstAmount + $gstAmount : $amount + $gstAmount, 122, $entry_date, 'IPC122', $memberInvestments['member']->id, $branch_id);
        if ($IntraState) {
          $description = 'Stationary  Cgst Charge' . $memberInvestments->account_number;
          $descriptionB = 'Stationary Sgst Charge' . $memberInvestments->account_number;
          $amountArraySsb = array('1' => ($gstAmount));
          $ssbCreateTranCGST = CommanController::createTransaction($satRefId, 18, $memberInvestments->id, $memberInvestments->member_id, $memberInvestments->branch_id, getBranchCode($memberInvestments->branch_id)->branch_code, $amountArraySsb, 0, NULL, $memberInvestments->member_id, $memberInvestments->account_number, NULL, NULL, NULL, date("Y-m-d", strtotime(convertDate($memberInvestments->created_at))), NULL, NULL, NULL, 'CR');
          $ssbCreateTranSGST = CommanController::createTransaction($satRefId, 18, $memberInvestments->id, $memberInvestments->member_id, $memberInvestments->branch_id, getBranchCode($memberInvestments->branch_id)->branch_code, $amountArraySsb, 0, NULL, $memberInvestments->member_id, $memberInvestments->account_number, NULL, NULL, NULL, date("Y-m-d", strtotime(convertDate($memberInvestments->created_at))), NULL, NULL, NULL, 'CR');
          $createDayBook = CommanController::createDayBook($ssbCreateTranCGST, $satRefId, 26, $memberInvestments->id, NULL, $memberInvestments->member_id, $gstAmount, $gstAmount, $withdrawal = 0, ($getHeadSetting->gst_percentage / 2) . 'CGST Charge on Stationary Charge', $memberInvestments->account_number, $memberInvestments->branch_id, getBranchCode($memberInvestments->branch_id)->branch_code, $amountArraySsb, 0, NULL, $memberInvestments->member_id, $memberInvestments->account_number, 50, NULL, NULL, date("Y-m-d", strtotime(convertDate($memberInvestments->created_at))), NULL, NULL, NULL, 'CR');
          $createDayBook = CommanController::createDayBook($ssbCreateTranSGST, $satRefId, 27, $memberInvestments->id, NULL, $memberInvestments->member_id, $gstAmount, $gstAmount, $withdrawal = 0, ($getHeadSetting->gst_percentage / 2) . 'SGST Charge on Stationary Charge', $memberInvestments->account_number, $memberInvestments->branch_id, getBranchCode($memberInvestments->branch_id)->branch_code, $amountArraySsb, 0, NULL, $memberInvestments->member_id, $memberInvestments->account_number, 50, NULL, NULL, date("Y-m-d", strtotime(convertDate($memberInvestments->created_at))), NULL, NULL, NULL, 'CR');
          $allTransaction = CommanController::createAllHeadTransaction($dayBookRef, $branch_id, $bank_id, $bank_ac_id, $cgstHead, $type, $sub_type_cgst, $type_id, $createdGstTransaction, $associate_id, $member_id, $branch_id_to, $branch_id_from, $gstAmount, $gstAmount, $gstAmount, $description, 'CR', $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $jv_unique_id = NULL, $v_no, $v_date, $ssb_account_id_from, $ssb_account_id_to = NULL, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $cheque_type = NULL, $cheque_id = NULL, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_from_id = NULL, $cheque_bank_ac_from_id = NULL, $cheque_bank_to, $cheque_bank_ac_to, NULL, $cheque_bank_to_branch = NULL, NULL, NULL, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_from_id = NULL, $transction_bank_from_ac_id = NULL, $transction_bank_to, $transction_bank_ac_to, $transction_bank_to_name = NULL, $transction_bank_to_ac_no = NULL, $transction_bank_to_branch = NULL, $transction_bank_to_ifsc = NULL, $memberInvestments->created_at, $created_by, $created_by_id);
          $allTransaction = CommanController::createAllHeadTransaction($dayBookRef, $branch_id, $bank_id, $bank_ac_id, 28, $type, $sub_type_cgst, $type_id, $createdGstTransaction, $associate_id, $member_id, $branch_id_to, $branch_id_from, $gstAmount, $gstAmount, $gstAmount, $description, 'DR', $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $jv_unique_id = NULL, $v_no, $v_date, $ssb_account_id_from, $ssb_account_id_to = NULL, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $cheque_type = NULL, $cheque_id = NULL, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_from_id = NULL, $cheque_bank_ac_from_id = NULL, $cheque_bank_to, $cheque_bank_ac_to, NULL, $cheque_bank_to_branch = NULL, NULL, NULL, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_from_id = NULL, $transction_bank_from_ac_id = NULL, $transction_bank_to, $transction_bank_ac_to, $transction_bank_to_name = NULL, $transction_bank_to_ac_no = NULL, $transction_bank_to_branch = NULL, $transction_bank_to_ifsc = NULL, $transction_date, $created_by, $created_by_id);
          $allTransaction = CommanController::createAllHeadTransaction($dayBookRef, $branch_id, $bank_id, $bank_ac_id, $sgstHead, $type, $sub_type_sgst, $type_id, $createdGstTransaction, $associate_id, $member_id, $branch_id_to, $branch_id_from, $gstAmount, $gstAmount, $gstAmount, $descriptionB, 'CR', $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $jv_unique_id = NULL, $v_no, $v_date, $ssb_account_id_from, $ssb_account_id_to = NULL, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $cheque_type = NULL, $cheque_id = NULL, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_from_id = NULL, $cheque_bank_ac_from_id = NULL, $cheque_bank_to, $cheque_bank_ac_to, NULL, $cheque_bank_to_branch = NULL, NULL, NULL, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_from_id = NULL, $transction_bank_from_ac_id = NULL, $transction_bank_to, $transction_bank_ac_to, $transction_bank_to_name = NULL, $transction_bank_to_ac_no = NULL, $transction_bank_to_branch = NULL, $transction_bank_to_ifsc = NULL, $memberInvestments->created_at, $created_by, $created_by_id);
          $allTransaction = CommanController::createAllHeadTransaction($dayBookRef, $branch_id, $bank_id, $bank_ac_id, 28, $type, $sub_type_sgst, $type_id, $createdGstTransaction, $associate_id, $member_id, $branch_id_to, $branch_id_from, $gstAmount, $gstAmount, $gstAmount, $descriptionB, 'DR', $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $jv_unique_id = NULL, $v_no, $v_date, $ssb_account_id_from, $ssb_account_id_to = NULL, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $cheque_type = NULL, $cheque_id = NULL, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_from_id = NULL, $cheque_bank_ac_from_id = NULL, $cheque_bank_to, $cheque_bank_ac_to, NULL, $cheque_bank_to_branch = NULL, NULL, NULL, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_from_id = NULL, $transction_bank_from_ac_id = NULL, $transction_bank_to, $transction_bank_ac_to, $transction_bank_to_name = NULL, $transction_bank_to_ac_no = NULL, $transction_bank_to_branch = NULL, $transction_bank_to_ifsc = NULL, $transction_date, $created_by, $created_by_id);
          /*$allTransaction = CommanController::createAllTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,2,10,28,71,NULL,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$amount,$amount,$amount,$description,'CR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$memberInvestments->created_at);*/
          $branchDayBook = CommanController::branchDayBookNew($dayBookRef, $branch_id, $type, $sub_type_cgst, $type_id, $createdGstTransaction, $associate_id, $member_id, $branch_id_to, $branch_id_from, $gstAmount, $gstAmount, $gstAmount, $description, $description, $description, 'CR', $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $is_contra, $contra_id, $created_at, 0, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL);
          $branchDayBook = CommanController::branchDayBookNew($dayBookRef, $branch_id, $type, $sub_type_sgst, $type_id, $createdGstTransaction, $associate_id, $member_id, $branch_id_to, $branch_id_from, $gstAmount, $gstAmount, $gstAmount, $descriptionB, $descriptionB, $descriptionB, 'CR', $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $is_contra, $contra_id, $created_at, 0, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL);
          $memberTransaction = CommanController::memberTransactionNew($dayBookRef, $type, $sub_type_cgst, $type_id, $createdGstTransaction, $associate_id, $member_id, $branch_id, $bank_id, $bank_ac_id, $gstAmount, $description, 'DR', $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL);
          $memberTransaction = CommanController::memberTransactionNew($dayBookRef, $type, $sub_type_sgst, $type_id, $createdGstTransaction, $associate_id, $member_id, $branch_id, $bank_id, $bank_ac_id, $gstAmount, $descriptionB, 'DR', $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL);
          $rec = [
            'cgst_stationary_chrg' => $gstAmount,
            'sgst_stationary_chrg' => $gstAmount,
            'invoice_id' => $createdGstTransaction,
          ];
        } else {
          $description = 'Stationary  Igst Charge' . $memberInvestments->account_number;
          $amountArraySsb = array('1' => ($gstAmount));
          $ssbCreateTranCGST = CommanController::createTransaction($satRefId, 18, $memberInvestments->id, $memberInvestments->member_id, $memberInvestments->branch_id, getBranchCode($memberInvestments->branch_id)->branch_code, $amountArraySsb, 0, NULL, $memberInvestments->member_id, $memberInvestments->account_number, NULL, NULL, NULL, date("Y-m-d", strtotime(convertDate($memberInvestments->created_at))), NULL, NULL, NULL, 'CR');
          $createDayBook = CommanController::createDayBook($ssbCreateTranCGST, $satRefId, 28, $memberInvestments->id, NULL, $memberInvestments->member_id, $gstAmount, $gstAmount, $withdrawal = 0, ($getHeadSetting->gst_percentage) . 'IGST Charge on Stationary Charge', $memberInvestments->account_number, $memberInvestments->branch_id, getBranchCode($memberInvestments->branch_id)->branch_code, $amountArraySsb, 0, NULL, $memberInvestments->member_id, $memberInvestments->account_number, 50, NULL, NULL, date("Y-m-d", strtotime(convertDate($memberInvestments->created_at))), NULL, NULL, NULL, 'CR');
          $allTransaction = CommanController::createAllHeadTransaction($dayBookRef, $branch_id, $bank_id, $bank_ac_id, $cgstHead, $type, $sub_type_igst, $type_id, $createdGstTransaction, $associate_id, $member_id, $branch_id_to, $branch_id_from, $gstAmount, $gstAmount, $gstAmount, $description, 'CR', $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $jv_unique_id = NULL, $v_no, $v_date, $ssb_account_id_from, $ssb_account_id_to = NULL, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $cheque_type = NULL, $cheque_id = NULL, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_from_id = NULL, $cheque_bank_ac_from_id = NULL, $cheque_bank_to, $cheque_bank_ac_to, NULL, $cheque_bank_to_branch = NULL, NULL, NULL, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_from_id = NULL, $transction_bank_from_ac_id = NULL, $transction_bank_to, $transction_bank_ac_to, $transction_bank_to_name = NULL, $transction_bank_to_ac_no = NULL, $transction_bank_to_branch = NULL, $transction_bank_to_ifsc = NULL, $memberInvestments->created_at, $created_by, $created_by_id);
          $allTransaction = CommanController::createAllHeadTransaction($dayBookRef, $branch_id, $bank_id, $bank_ac_id, 28, $type, $sub_type_igst, $type_id, $createdGstTransaction, $associate_id, $member_id, $branch_id_to, $branch_id_from, $gstAmount, $gstAmount, $gstAmount, $description, 'DR', $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $jv_unique_id = NULL, $v_no, $v_date, $ssb_account_id_from, $ssb_account_id_to = NULL, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $cheque_type = NULL, $cheque_id = NULL, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_from_id = NULL, $cheque_bank_ac_from_id = NULL, $cheque_bank_to, $cheque_bank_ac_to, NULL, $cheque_bank_to_branch = NULL, NULL, NULL, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_from_id = NULL, $transction_bank_from_ac_id = NULL, $transction_bank_to, $transction_bank_ac_to, $transction_bank_to_name = NULL, $transction_bank_to_ac_no = NULL, $transction_bank_to_branch = NULL, $transction_bank_to_ifsc = NULL, $transction_date, $created_by, $created_by_id);
          /*$allTransaction = CommanController::createAllTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,2,10,28,71,NULL,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$amount,$amount,$amount,$description,'CR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$memberInvestments->created_at);*/
          $branchDayBook = CommanController::branchDayBookNew($dayBookRef, $branch_id, $type, $sub_type_igst, $type_id, $createdGstTransaction, $associate_id, $member_id, $branch_id_to, $branch_id_from, $gstAmount, $gstAmount, $gstAmount, $description, $description, $description, 'CR', $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $is_contra, $contra_id, $created_at, 0, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL);
          $memberTransaction = CommanController::memberTransactionNew($dayBookRef, $type, $sub_type_igst, $type_id, $createdGstTransaction, $associate_id, $member_id, $branch_id, $bank_id, $bank_ac_id, $gstAmount, $description, 'DR', $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL);
          $rec = [
            'igst_stationary_chrg' => $gstAmount,
            'invoice_id' => $createdGstTransaction,
          ];
        }
        $memberInvestments->update($rec);
        $this->updateBranchCashCr($memberInvestments->branch_id, $memberInvestments->created_at, $gstAmount, 0);
        $this->updateBranchClosingCashCr($memberInvestments->branch_id, $memberInvestments->created_at, $gstAmount, 0);
      }
      DB::commit();
    } catch (\Exception $ex) {
      DB::rollback();
      return back()->with('alert', $ex->getMessage());
    }
  }
  public static function updateBranchCashCr($branch_id, $date, $amount, $type)
  {
    $entryDate = date("Y-m-d", strtotime(convertDate($date)));
    $entryTime = date("H:i:s", strtotime(convertDate($date)));
    $currentDateRecord = \App\Models\BranchCash::where('branch_id', $branch_id)->whereDate('entry_date', $entryDate)->first();
    if ($currentDateRecord) {
      $Result = \App\Models\BranchCash::find($currentDateRecord->id);
      $data['balance'] = $currentDateRecord->balance + $amount;
      $data['updated_at'] = $date;
      $Result->update($data);
      $getNextBranchRecord = \App\Models\BranchCash::where('branch_id', $branch_id)->whereDate('entry_date', '>', $entryDate)->orderby('entry_date', 'ASC')->get();
      if ($getNextBranchRecord) {
        foreach ($getNextBranchRecord as $key => $value) {
          $sResult = \App\Models\BranchCash::find($value->id);
          $sData['opening_balance'] = $value->closing_balance;
          $sData['balance'] = $value->balance + $amount;
          if ($value->closing_balance > 0) {
            $sData['closing_balance'] = $value->closing_balance + $amount;
          }
          $sData['updated_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($entryDate)));
          $sResult->update($sData);
        }
      }
    } else {
      $oldDateRecord = \App\Models\BranchCash::where('branch_id', $branch_id)->whereDate('entry_date', '<', $entryDate)->orderby('entry_date', 'DESC')->first();
      if ($oldDateRecord) {
        $Result1 = \App\Models\BranchCash::find($oldDateRecord->id);
        $data1['closing_balance'] = $oldDateRecord->balance;
        $Result1->update($data1);
        $insertid1 = $oldDateRecord->id;
        $data1RecordExists = \App\Models\BranchCash::where('branch_id', $branch_id)->whereDate('entry_date', '>', $entryDate)->orderby('entry_date', 'ASC')->get();
        if ($type == 0) {
          $data['balance'] = $oldDateRecord->balance + $amount;
        } else {
          $data['balance'] = $oldDateRecord->balance;
        }
        $data['opening_balance'] = $oldDateRecord->balance;
        if ($data1RecordExists) {
          if ($type == 0) {
            $data['closing_balance'] = $oldDateRecord->balance + $amount;
          } else {
            $data['closing_balance'] = $oldDateRecord->balance;
          }
          foreach ($data1RecordExists as $key => $value) {
            $sResult = \App\Models\BranchCash::find($value->id);
            $sData['opening_balance'] = $value->closing_balance;
            $sData['balance'] = $value->balance + $amount;
            if ($value->closing_balance > 0) {
              $sData['closing_balance'] = $value->closing_balance + $amount;
            }
            $sData['updated_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($entryDate)));
            $sResult->update($sData);
          }
        } else {
          $data['closing_balance'] = 0;
        }
        $data['branch_id'] = $branch_id;
        $data['entry_date'] = $entryDate;
        $data['entry_time'] = $entryTime;
        $data['type'] = $type;
        $data['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($date)));
        $transcation = \App\Models\BranchCash::create($data);
        $insertid = $transcation->id;
      } else {
        if ($type == 0) {
          $data['balance'] = $amount;
        } else {
          $data['balance'] = 0;
        }
        $data['opening_balance'] = 0;
        $data['closing_balance'] = 0;
        $data['branch_id'] = $branch_id;
        $data['entry_date'] = $entryDate;
        $data['entry_time'] = $entryTime;
        $data['type'] = $type;
        $data['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($date)));
        $transcation = \App\Models\BranchCash::create($data);
        $insertid = $transcation->id;
      }
    }
    return true;
  }
  public static function updateBranchClosingCashCr($branch_id, $date, $amount, $type)
  {
    $entryDate = date("Y-m-d", strtotime(convertDate($date)));
    $entryTime = date("H:i:s", strtotime(convertDate($date)));
    $currentDateRecord = \App\Models\BranchClosing::where('branch_id', $branch_id)->whereDate('entry_date', $entryDate)->first();
    if ($currentDateRecord) {
      $Result = \App\Models\BranchClosing::find($currentDateRecord->id);
      if ($type == 0) {
        $data['balance'] = $currentDateRecord->balance + $amount;
      }
      $data['updated_at'] = $date;
      $Result->update($data);
      $getNextBranchClosingRecord = \App\Models\BranchClosing::where('branch_id', $branch_id)->whereDate('entry_date', '>', $entryDate)->orderby('entry_date', 'ASC')->get();
      if ($getNextBranchClosingRecord) {
        foreach ($getNextBranchClosingRecord as $key => $value) {
          $sResult = \App\Models\BranchClosing::find($value->id);
          $sData['opening_balance'] = $value->closing_balance;
          $sData['balance'] = $value->balance + $amount;
          if ($value->closing_balance > 0) {
            $sData['closing_balance'] = $value->closing_balance + $amount;
          }
          $sData['updated_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($entryDate)));
          $sResult->update($sData);
        }
      }
    } else {
      $oldDateRecord = \App\Models\BranchClosing::where('branch_id', $branch_id)->whereDate('entry_date', '<', $entryDate)->orderby('entry_date', 'DESC')->first();
      if ($oldDateRecord) {
        $Result1 = \App\Models\BranchClosing::find($oldDateRecord->id);
        $data1['closing_balance'] = $oldDateRecord->balance;
        $Result1->update($data1);
        $insertid1 = $oldDateRecord->id;
        $data1RecordExists = \App\Models\BranchClosing::where('branch_id', $branch_id)->whereDate('entry_date', '>', $entryDate)->orderby('entry_date', 'ASC')->get();
        if ($type == 0) {
          $data['balance'] = $oldDateRecord->balance + $amount;
        } else {
          $data['balance'] = $oldDateRecord->balance;
        }
        $data['opening_balance'] = $oldDateRecord->balance;
        if ($data1RecordExists) {
          if ($type == 0) {
            $data['closing_balance'] = $oldDateRecord->balance + $amount;
          } else {
            $data['closing_balance'] = $oldDateRecord->balance;
          }
          foreach ($data1RecordExists as $key => $value) {
            $sResult = \App\Models\BranchClosing::find($value->id);
            $sData['opening_balance'] = $value->closing_balance;
            $sData['balance'] = $value->balance + $amount;
            if ($value->closing_balance > 0) {
              $sData['closing_balance'] = $value->closing_balance + $amount;
            }
            $sData['updated_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($entryDate)));
            $sResult->update($sData);
          }
        } else {
          $data['closing_balance'] = 0;
        }
        $data['branch_id'] = $branch_id;
        $data['entry_date'] = $entryDate;
        $data['entry_time'] = $entryTime;
        $data['type'] = $type;
        $data['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($date)));
        $transcation = \App\Models\BranchClosing::create($data);
        $insertid = $transcation->id;
      } else {
        if ($type == 0) {
          $data['balance'] = $amount;
        } else {
          $data['balance'] = 0;
        }
        $data['opening_balance'] = 0;
        $data['closing_balance'] = 0;
        $data['branch_id'] = $branch_id;
        $data['entry_date'] = $entryDate;
        $data['entry_time'] = $entryTime;
        $data['type'] = $type;
        $data['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($date)));
        $transcation = \App\Models\BranchClosing::create($data);
        $insertid = $transcation->id;
      }
    }
    return true;
  }
  public function updatePrintStatus($id, $correctionid)
  {
    $updatePrintStatus = Memberinvestments::where('id', $id)->update(array('is_passbook_print' => 0));
    $correctionRequest = CorrectionRequests::find($correctionid);
    $crData['status'] = 1;
    $correctionRequest->update($crData);
    return back()->with('success', 'Update print status successfully');
  }
  public function updateCertificatePrintStatus($id, $correctionid)
  {
    $updatePrintStatus = Memberinvestments::where('id', $id)->update(array('is_certificate_print' => 0));
    $correctionRequest = CorrectionRequests::find($correctionid);
    $crData['status'] = 1;
    $correctionRequest->update($crData);
    return back()->with('success', 'Update Certificate Print Status successfully');
  }
  public function deleteHeadTransactions($type, $subtype, $investmentid, $createddate, $branchid, $paymentmode, $depositeamount)
  {
    // dd($type,$subtype,$investmentid,$createddate,$branchid,$paymentmode,$depositeamount);
    // die();
    $getTransactionData = \App\Models\AllHeadTransaction::where('type', $type)->where('sub_type', $subtype)->where('type_id', $investmentid) /*->where('created_at',$createddate)*/->first();
    \App\Models\BranchDaybookReference::where('id', $getTransactionData->daybook_ref_id)->delete();
    \App\Models\AllHeadTransaction::where('type', $type)->where('sub_type', $subtype)->where('type_id', $investmentid) /*->where('created_at',$createddate)*/->delete();
    \App\Models\BranchDaybook::where('type', $type)->where('sub_type', $subtype)->where('type_id', $investmentid) /*->where('created_at',$createddate)*/->delete();
    \App\Models\MemberTransaction::where('type', $type)->where('sub_type', $subtype)->where('type_id', $investmentid) /*->where('created_at',$createddate)*/->delete();
    \App\Models\SamraddhBankDaybook::where('type', $type)->where('sub_type', $subtype)->where('type_id', $investmentid) /*->where('created_at',$createddate)*/->delete();
    $currentDateBranchRecord = \App\Models\BranchClosing::where('branch_id', $branchid)->whereDate('entry_date', $createddate)->first();
    if ($currentDateBranchRecord) {
      $Result = \App\Models\BranchClosing::find($currentDateBranchRecord->id);
      $branchClosingdata['balance'] = $currentDateBranchRecord->balance - $depositeamount;
      $Result->update($branchClosingdata);
    }
    if ($paymentmode == 1 || $paymentmode == 2) {
      $currentBankDateRecord = \App\Models\SamraddhBankClosing::where('bank_id', $getTransactionData->transction_bank_to)->where('account_id', $getTransactionData->transction_bank_ac_to)->whereDate('entry_date', $createddate)->first();
      if ($currentBankDateRecord) {
        $Result = \App\Models\SamraddhBankClosing::find($currentBankDateRecord->id);
        $bankClosingdata['balance'] = $currentBankDateRecord->balance - $depositeamount;
        $bankClosingdata['updated_at'] = $createddate;
        $Result->update($bankClosingdata);
      }
    }
    if ($paymentmode == 3) {
      $currentBranchClosingDateRecord = \App\Models\BranchClosing::where('branch_id', $branchid)->whereDate('entry_date', $createddate)->first();
      if ($currentBranchClosingDateRecord) {
        $Result = \App\Models\BranchClosing::find($currentBranchClosingDateRecord->id);
        $bdata['balance'] = $currentBranchClosingDateRecord->balance + $depositeamount;
        $bdata['updated_at'] = $createddate;
        $Result->update($bdata);
      }
    }
    return true;
  }
  /*--------------------- investment branch transfer start -----------------------*/
  public function investmentbranchtransfer()
  {
    if (check_my_permission(Auth::user()->id, "278") != "1") {
      return redirect()->route('admin.dashboard');
    }
    $data['title'] = 'Investment Plans | Branch Transfer';
    return view('templates.admin.investment_management.investmentbranchtrasnfer_change', $data);
  }
  public function investmentBrtransferDataGet(Request $request)
  {
    $branch = Branch::select('id', 'name', 'branch_code')->where('status', 1);
    $data = Memberinvestments::with([
      'plan' => function ($query) {
        $query->select('id', 'name');
      }
    ])->with([
          'associateMember' => function ($query) {
            $query->select('id', 'first_name', 'last_name', 'mobile_no', 'current_carder_id', 'associate_status', 'associate_no');
          }
        ])->with([
          'branch' => function ($query) {
            $query->select('id', 'branch_code', 'name');
          }
        ])->with([
          'member' => function ($query) {
            $query->select('id', 'member_id', 'first_name', 'last_name', 'mobile_no');
          }
        ])->where('account_number', $request->code);
    if (Auth::user()->branch_id > 0) {
      $branch = $branch->where('id', Auth::user()->branch_id);
    }
    $branch = $branch->get();
    $data = $data->first();
    if ($data) {
      if ($data->is_mature == 1) {
        return \Response::json(['view' => view('templates.admin.investment_management.partials.investmentbranchtransfer_detail', ['investData' => $data, 'branch' => $branch])->render(), 'msg_type' => 'success']);
      } else if ($data->is_mature == 0) {
        return \Response::json(['view' => 'No data found', 'msg_type' => 'error_mature']);
      } else {
        return \Response::json(['view' => 'No data found', 'msg_type' => 'error1']);
      }
    } else {
      return \Response::json(['view' => 'No data found', 'msg_type' => 'error']);
    }
  }
  public function invsbranchtransfersave(Request $request)
  {
    $id = $request->investment_id;
    $created_by_id = Auth::user()->id;
    $globaldate = $request['created_at'];
    $created_at = date("Y-m-d H:i:s", strtotime(convertDate($globaldate)));
    if (empty($request->branch_id)) {
      return back()->with('errors', 'Investment Id Not Found');
    }
    $planData = Plans::whereId($request->plan_id)->first();
    if (($planData->plan_category_code == 'M' && $planData->plan_sub_category_code == 'B') || ($planData->plan_category_code == 'F' && $planData->plan_sub_category_code == 'I')) {
      $ssbDetail = DB::select('call getSbbDetailByInvestmentId(?)', [$id]);
      //print_r($ssbDetail[0]);die;
      if ($request->branch_id != $ssbDetail[0]->branch_id) {
        $getBranchCode = getBranchCode($request->branch_id);
        $branchCode = $getBranchCode->branch_code;
        $Membernew = Memberinvestments::where('id', $ssbDetail[0]->member_investments_id)->update(['branch_id' => $request->branch_id]);
        $Membernew = SavingAccount::where('id', $ssbDetail[0]->id)->update(['branch_id' => $request->branch_id, 'branch_code' => $branchCode]);
        $InvestmentBranchTransferSSB = new AccountBranchTransfer;
        $InvestmentBranchTransferSSB->type = 4;
        $InvestmentBranchTransferSSB->new_branch_id = $request->branch_id;
        $InvestmentBranchTransferSSB->type_id = $ssbDetail[0]->id;
        $InvestmentBranchTransferSSB->old_branch_id = $ssbDetail[0]->branch_id;
        $InvestmentBranchTransferSSB->created_by = 1;
        $InvestmentBranchTransferSSB->created_by_id = $created_by_id;
        $InvestmentBranchTransferSSB->created_at = $created_at;
        $InvestmentBranchTransferSSB->updated_at = $created_at;
        $InvestmentBranchTransferSSB->save();
      }
    }
    $InvestmentBranchTransfer = new AccountBranchTransfer;
    if ($planData->plan_category_code == 'S') {
      $getBranchCode = getBranchCode($request->branch_id);
      $branchCode = $getBranchCode->branch_code;
      $InvestmentBranchTransfer->type = 4;
      $Membernew = SavingAccount::where('member_investments_id', $id)->update(['branch_id' => $request->branch_id, 'branch_code' => $branchCode]);
    } else {
      $InvestmentBranchTransfer->type = 2;
    }
    $Membernew = \App\Models\Memberinvestments::where('id', $id)->update(['branch_id' => $request->branch_id]);
    $created_by_id = Auth::user()->id;
    $InvestmentBranchTransfer->new_branch_id = $request->branch_id;
    $InvestmentBranchTransfer->type_id = $id;
    $InvestmentBranchTransfer->old_branch_id = $request->old_branch_id;
    $InvestmentBranchTransfer->created_by = 1;
    $InvestmentBranchTransfer->created_by_id = $created_by_id;
    $InvestmentBranchTransfer->created_at = $created_at;
    $InvestmentBranchTransfer->updated_at = $created_at;
    $InvestmentBranchTransfer->save();
    return back()->with('success', 'investment branch updated successfully');
  }
  /*--------------------- investment branch transfer end -----------------------*/
}
