<?php

namespace App\Http\Controllers\Branch;

use App\Http\Controllers\Admin\CommanController;
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
use App\Models\CommissionLeaserMonthly;
use App\Models\AssociateMonthlyCommission;
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
use App\Models\TransactionReferences;
use App\Models\SamraddhBank;
use App\Models\CollectorAccount;
use App\Interfaces\RepositoryInterface;
use App\Http\Requests\InvestmentPlanRequest;
use App\Scopes\ActiveScope;
use Illuminate\Support\Facades\Cache;
use Investment;

class InvestmentplanController extends Controller
{
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
    if (!in_array('Investment Plan View', auth()->user()->getPermissionNames()->toArray())) {
      return redirect()->route('branch.dashboard');
    }
    $data['title'] = "Investment Plans";
    return view('templates.branch.investment_management.investment-listing', $data);
  }

  public function getCompanyIdPlans(Request $request)
  {
    $company_id = $request->company_id;
    $excludedCategories = [''];

    $plans = Plans::withoutGlobalScope(ActiveScope::class)->where('company_id', $company_id)->pluck('name', 'id');
    //   $plans = Plans::with(['plan'=>function($q){
    //     $q->withoutGlobalScope(ActiveScope::class);
    //  }])->where('company_id', $company_id)
    //   ->whereNotIn('plan_category_code', $excludedCategories)
    //   ->pluck('name', 'id');
    return json_encode($plans);
  }
  /**
   * Fetch invest listing data.
   *
   * @param  \App\Reservation  $reservation
   * @return \Illuminate\Http\Response
   */
  public function investmentListing(Request $request)
  {

    if ($request->ajax()) {
      $getBranchId = getUserBranchId(Auth::user()->id);
      $company_id = $request->company_id;
      $branch_id = $getBranchId->id;
      $arrFormData = array();

      if (!empty($_POST['searchform'])) {
        foreach ($_POST['searchform'] as $frm_data) {
          $arrFormData[$frm_data['name']] = $frm_data['value'];
        }
      }
      $data = Memberinvestments::select('id', 'form_number', 'company_id', 'plan_id', 'tenure', 'account_number', 'deposite_amount', 'branch_id', 'associate_id', 'member_id', 'created_at', 'current_balance', 'customer_id')->with(['company:id,name', 'member:id,member_id', 'memberCompany.member:id,member_id,first_name,last_name,mobile_no,state_id,district_id,city_id,village,pin_code,address', 'associateMember:id,associate_no,first_name,last_name', 'branch:id,branch_code,name,sector,regan,zone'])->with(['plan' => function ($q) {
        $q->withoutGlobalScope(ActiveScope::class);
      }])
        ->with(['CollectorAccount' => function ($q) {
          $q->with(['member_collector']);
        }])->where('branch_id', $branch_id)->where('is_deleted', 0)->where('company_id', $arrFormData['company_id']);

      /******* fillter query start ****/
      if (isset($arrFormData['is_search']) && $arrFormData['is_search'] == 'yes') {
        $data = $data->when($arrFormData['start_date'] != '', function ($query) use ($arrFormData) {
          $startDate = date("Y-m-d", strtotime(convertDate($arrFormData['start_date'])));
          $endDate = date("Y-m-d", strtotime(convertDate($arrFormData['end_date'])));
          return $query->whereBetween(DB::raw('DATE(created_at)'), [$startDate, $endDate]);
        })->when($arrFormData['plan_id'] != '', function ($query) use ($arrFormData) {
          return $query->where('plan_id', '=', $arrFormData['plan_id']);
        })->when($arrFormData['scheme_account_number'] != '', function ($query) use ($arrFormData) {
          $sAccountNumber = $arrFormData['scheme_account_number'];
          return $query->where('account_number', 'LIKE', '%' . $sAccountNumber . '%');
        })->when($arrFormData['associate_code'] != '', function ($query) use ($arrFormData) {
          $associateCode = $arrFormData['associate_code'];
          return $query->whereHas('associateMember', function ($query) use ($associateCode) {
            $query->where('members.associate_no', 'LIKE', '%' . $associateCode . '%');
          });
        })->when($arrFormData['name'] != '', function ($query) use ($arrFormData) {
          $name = $arrFormData['name'];
          return $query->whereHas('member', function ($query) use ($name) {
            $query->where('members.first_name', 'LIKE', '%' . $name . '%')
              ->orWhere('members.last_name', 'LIKE', '%' . $name . '%')
              ->orWhere(DB::raw('concat(members.first_name," ",members.last_name)'), 'LIKE', "%$name%");
          });
        });

        /******* Company Fillter ****/
        // if ($company_id != '') {
        //   $data = $data->where('company_id', $company_id);
        // }

        if ($arrFormData['customer_id'] != '') {
          $meid = $arrFormData['customer_id'];
          $data = $data->whereHas('memberCompany.member', function ($query) use ($meid) {
            $query->where('member_id', 'LIKE', '%' . $meid . '%');
          });
        }
        if ($arrFormData['member_id'] != '') {
          $meiud = $arrFormData['member_id'];
          $data = $data->whereHas('memberCompany', function ($query) use ($meiud) {
            $query->where('member_companies.member_id', 'LIKE', '%' . $meiud . '%');
          });
        }


      }

      /******* fillter query End ****/
      $data1 = $data->count();

      $count = $data1;
      $data_2 = $data->orderby('id', 'DESC')->get();
      $data = $data->orderby('id', 'DESC')->offset($_POST['start'])->limit($_POST['length'])->get();
      $totalCount = $count;
      $sno = $_POST['start'];
      $rowReturn = array();
      $allAccountNumber = [];
      // foreach ($data as $row) {
      //   $allAccountNumber[] = $row->account_number;
      // }
      // $dayBook = ;

      foreach ($data as $row) {

        $sno++;
        $val['DT_RowIndex'] = $sno;
        $val['plan'] = $row['plan']->name;
        $val['company'] = $row['company']->name ?? 'N/A';
        $val['form_number'] = $row->form_number;
        $val['tenure'] = ($row->plan_id == 1) ? 'N/A' : number_format((float) $row->tenure, 2, '.', '') . ' Year';

        if ($row->plan->plan_category_code == "S") {
          $ssb = getSsbAccountDetail($row->account_number);
          $current_balance = isset($ssb->balance) ? $ssb->balance : 'N/A';
        } else {
          $dayBook = Daybook::where('investment_id', $row->id)->where('account_no', $row->account_number)->orderby('created_at', 'desc')->first(['opening_balance']);
          if ($dayBook) {
            $current_balance = $dayBook->opening_balance;
          } else {
            $current_balance = $row->deposite_amount;
          }
        }


        $val['current_balance'] = $current_balance;
        $val['eli_amount'] = investmentEliAmount($row->id);
        $val['deposite_amount'] = $row->deposite_amount;
        $val['member'] = isset($row['memberCompany']['member']) ? $row['memberCompany']['member']->first_name . ' ' . $row['memberCompany']['member']->last_name : 'N/A';
        $val['customer_id'] = isset($row['memberCompany']['member']['member_id']) ? $row['memberCompany']['member']['member_id'] : 'N/A';
        $val['member_id'] = isset($row['memberCompany']->member_id) ? $row['memberCompany']->member_id : 'N/A';
        $val['mobile_number'] = isset($row['memberCompany']['member']->mobile_no) ? $row['memberCompany']['member']->mobile_no : 'N/A';
        if ($row['associateMember']) {
          $val['associate_code'] = $row['associateMember']['associate_no'];
        } else {
          $val['associate_code'] = "N/A";
        }
        $val['account_number'] = $row['account_number'];
        $val['created_at'] = date("d/m/Y", strtotime($row['created_at']));
        $val['account_number'] = $row->account_number;
        if ($row['associateMember']) {
          $val['associate_name'] = $row['associateMember']['first_name'] . ' ' . $row['associateMember']['last_name'];
        } else {
          $val['associate_name'] = "N/A";
        }
        $val['branch'] = $row['branch']->name;
        $val['branch_code'] = $row['branch']->branch_code;
        $val['sector_name'] = $row['branch']->sector;
        $val['region_name'] = $row['branch']->regan;
        $val['zone_name'] = $row['branch']->zone;
        if (isset($row['memberCompany']['member']->id)) {
          $idProofDetail = \App\Models\MemberIdProof::select('first_id_type_id', 'second_id_type_id', 'first_id_no', 'second_id_no')->where('member_id', $row['memberCompany']['member']->id)->first();

          $val['firstId'] = getIdProofName($idProofDetail->first_id_type_id) . ' - ' . $idProofDetail->first_id_no;
          $val['secondId'] = getIdProofName($idProofDetail->second_id_type_id) . ' - ' . $idProofDetail->second_id_no;
        } else {
          $val['firstId'] = 'N/A';
          $val['secondId'] = 'N/A';
        }


        if (isset($row['CollectorAccount']['member_collector']['associate_no'])) {
          $val['collectorcode'] = $row['CollectorAccount']['member_collector']['associate_no'];
        } else {
          $val['collectorcode'] = "N/A";
        }
        if (isset($row['CollectorAccount']['member_collector']['first_name'])) {
          $val['collectorname'] = $row['CollectorAccount']['member_collector']['first_name'] . ' ' . $row['CollectorAccount']['member_collector']['last_name'];
        } else {
          $val['collectorname'] = "N/A";
        }
        $val['address'] = isset($row['memberCompany']['member']->address) ? preg_replace("/\r|\n/", "", $row['memberCompany']['member']->address) : 'N/A';
        $val['state'] = isset($row['memberCompany']['member']->state_id) ? getStateName($row['memberCompany']['member']->state_id) : 'N/A';
        $val['district'] = isset($row['memberCompany']['member']->district_id) ? getDistrictName($row['memberCompany']['member']->district_id) : 'N/A';
        $val['city'] = isset($row['memberCompany']['member']->city_id) ? getCityName($row['memberCompany']['member']->city_id) : 'N/A';
        $val['village'] = isset($row['memberCompany']['member']->village) ? $row['memberCompany']['member']->village : 'N/A';
        $val['pin_code'] = isset($row['memberCompany']['member']->pin_code) ? $row['memberCompany']['member']->pin_code : 'N/A';
        $url = URL::to("branch/investment/" . $row->id);
        $reciptUrl = URL::to("branch/investment/recipt/" . $row->id . "");
        $commsissionUrl = URL::to("branch/investment/commission/" . $row->id . "");
        $btn = '';
        $Passbook = URL::to("branch/member/passbook/transaction/" . $row->id . "/" . $row['plan']->plan_category_code . "");
        if (in_array('Investment Plan Detail View', auth()->user()->getPermissionNames()->toArray())) {
          $btn .= '<a href="' . $url . '" title="View Detail"><i class="fas fa-eye text-default mr-2"></i></a>';
        }



        // if ( in_array('Print Investment Receipt', auth()->user()->getPermissionNames()->toArray() ) ) {
        if (in_array('Investment Receipt', auth()->user()->getPermissionNames()->toArray())) {
          $btn .= '<a href="' . $reciptUrl . '" title="View Receipt"><i class="fa fa-file mr-2" aria-hidden="true"></i></a>';
        }
        if (in_array('Investment Transaction', auth()->user()->getPermissionNames()->toArray())) {
          $btn .= '<a class=" " href="' . $Passbook . '" title="Passbook View Transcations"><i class="fas fa-print text-default mr-2"></i></a>';
        }
        if (in_array('Investment Commission', auth()->user()->getPermissionNames()->toArray())) {
          $btn .= '<a class=" " href="' . $commsissionUrl . '"  title="View Commission"><i class="fa fa-percent mr-2" aria-hidden="true"></i></a>';
        }
        //$btn .= '<a class=" " href="'.$Passbook.'" title="Passbook"><i class="fa fa-percent mr-2" aria-hidden="true"></i></a>';
        $val['action'] = $btn;
        $rowReturn[] = $val;
      }

      //Set caches
      Cache::put('investment_list', $data_2);
      Cache::put('investment_list_count', $count);
      //End Set caches

      $output = array("draw" => $_POST['draw'], "recordsTotal" => $totalCount, "recordsFiltered" => $count, "data" => $rowReturn);


      return json_encode($output);
    }
  }


  /**
   * Display a form of the register plans.
   *
   * @return \Illuminate\Http\Response
   */
  public function registerPlans()
  {
    if (!in_array('Investment Plan Registration', auth()->user()->getPermissionNames()->toArray())) {
      return redirect()->route('branch.dashboard');
    }

    $getCompanyId = \App\Models\CompanyBranch::wherehas('company', function ($q) {
      $q->whereStatus(1);
    })->where('branch_id', Auth::user()->branches->id)->pluck('company_id');


    $data['title'] = "Investment Plans Registration";
    // $data['plans'] = Plans::whereIn('company_id', $getCompanyId)->whereHas('planTenures', function ($query)  {
    //   $query
    //     ->where('plan_category_code', '!=', 'S');
    // })->where('status', 1)->where('id', '!=', 3)->get();
    $data['plans'] = Plans::whereIn('company_id', $getCompanyId)->where(function ($query) use ($getCompanyId) {
      $query->whereHas('planTenures', function ($subquery) use ($getCompanyId) {
        $subquery->whereIn('company_id', $getCompanyId);
      })
        ->orWhere('plan_category_code', 'S');
    })->where('status', 1)
      ->where('id', '!=', 3)
      ->select(['id', 'slug', 'name', 'plan_category_code', 'company_id', 'multiple_deposit', 'min_deposit', 'max_deposit', 'is_ssb_required', 'plan_sub_category_code'])->orderby('company_id', 'DESC')->orderBy('plan_category_code', 'DESC')
      ->get();

    $data['relations'] = Relations::all();
    $account_setting = \App\Models\SsbAccountSetting::where('plan_type', 1)->where('user_type', 1)->where('status', 1)->first();

    $data['plan_amount'] = $account_setting->amount;
    return view('templates.branch.investment_management.assign', $data);
  }

  public function getBankList()
  {
    $data['samraddhBanks'] = SamraddhBank::with('bankAccount')->where('status', 1)->get();
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
    $view = 'No record found !';
    $countInvestment = 0;
    $resCount = 0;
    $member = Member::with('savingAccount', 'memberNominee', 'customerInvestment')
      ->leftJoin('special_categories', 'members.special_category_id', '=', 'special_categories.id')
      ->leftJoin('member_id_proofs', 'members.id', '=', 'member_id_proofs.member_id')
      ->where('members.member_id', $mId)
      ->select('members.id', 'members.member_id', 'members.first_name', 'members.last_name', 'members.mobile_no', 'members.address', 'special_categories.name as special_category', 'member_id_proofs.first_id_no', 'members.status', 'members.is_block')
      ->get();
    /** check if member details are correct or not */
    if (isset($member[0])) {
      /** check if member status is active or not */
      if ($member[0]->status == '0') {
        $view = 'Customer is Inactive. Please contact administrator!';
      } else {
        /** check if member status is block or not by system*/
        if ($member[0]->is_block == '1') {
          $view = 'Customer is Inactive. Please Upload Signature and Photo.';
        } else {
          $resCount = count($member);
          if ($resCount > 0) {
            $countInvestment = count($member[0]['customerInvestment']);
          } else {
            $countInvestment = 0;
          }
        }
      }
    }

    $return_array = compact('countInvestment', 'member', 'resCount', 'view');
    return json_encode($return_array);
  }


  public function checkMemberExist(Request $request)
  {

    $checkMember = \App\Models\MemberCompany::where('customer_id', $request->memberAutoId)->where('company_id', $request->companyId)->first();
    $checkMemberInOtherCompany = \App\Models\MemberCompany::where('customer_id', $request->memberAutoId)->exists();

    $checkCustomerSSb = \App\Models\SavingAccount::where('customer_id', $request->memberAutoId)->where('company_id', $request->companyId)->first();

    $newUser = isset($checkMember->id);

    return response()->json(['checkMember' => $checkMember, 'newUser' => $newUser, 'checkMemberInOtherCompany' => $checkMemberInOtherCompany, 'checkCustomerSSb' => $checkCustomerSSb]);
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
    $account_open_date = date('Y-m-d', strtotime($request->account_open_date));
    $investmentAmount = \App\Models\PlanDenos::whereHas('planTenure', function ($q) use ($tenure) {
      $q->select('roi', 'plan_id')->where('tenure', $tenure);
    })->with(['planTenure' => function ($q) use ($tenure) {
      $q->select('roi', 'plan_id')->where('tenure', $tenure);
    }])->where('plan_id', $plan_id)->where('tenure', $tenure)->where('effective_from', '<=', $account_open_date)->where(function ($q) use ($account_open_date) {
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
    $planData = Investment::planForm($request, $this->repository);
    $member = $planData['member'];
    $relations = $planData['relations'];
    $plans_tenure = $planData['plans_tenure'];
    $plan = $planData['plan'];
    $plans_tenures = $planData['plans_tenures'];
    $savingAccount = $planData['savingAccount'];



    // $plan = $request->plan;
    // $mId = $request->memberAutoId;
    // $member = MemberNominee::where('member_id', $mId)->get();
    // $relations = Relations::all();
    // $plan_amount = 0;
    // switch ($plan) {
    //   case 'saving-account':
    //     $account_setting = \App\Models\SsbAccountSetting::where('plan_type', 1)->where('user_type', 1)->where('status', 1)->first();
    //     $plan_amount = $account_setting->amount;
    //     break;
    //   case 'saving-account-child':
    //     $account_setting = \App\Models\SsbAccountSetting::where('plan_type', 2)->where('user_type', 1)->where('status', 1)->first();
    //     $plan_amount = $account_setting->amount;
    //     break;
    // }

    // if ($plan) {

    return view('templates.branch.investment_management.' . $plan . '.' . $plan . '', compact('member', 'relations', 'plans_tenure', 'plans_tenures', 'savingAccount'));
    // }
  }
  public function investHeadCreate($amount, $globaldate, $investmentId, $planId, $cheque_no, $cheque_id, $cheque_date, $online_transction_no, $online_transction_date, $online_deposit_bank_id, $online_deposit_bank_ac_id, $branch_id, $associate_id, $memberId, $ssbId, $createDayBook, $payment_mode, $investmentAccountNoRd)
  {
    $amount = $amount;
    $daybookRefRD = CommanTransactionsController::createBranchDayBookReferenceNew($amount, $globaldate);
    $refIdRD = $daybookRefRD;
    $currency_code = 'INR';
    $headPaymentModeRD = 0;
    $payment_type_rd = 'CR';
    $type_id = $investmentId;
    $entry_date = date("Y-m-d", strtotime(convertDate($globaldate)));
    $entry_time = date("H:i:s", strtotime(convertDate($globaldate)));
    $created_by = 2;
    $created_by_id = Auth::user()->id;
    $created_at = date("Y-m-d H:i:s", strtotime(convertDate($globaldate)));
    $updated_at = date("Y-m-d H:i:s", strtotime(convertDate($globaldate)));
    $planDetail = getPlanDetail($planId);
    $type = 3;
    $sub_type = 31;
    $planCode = $planDetail->plan_code;
    if ($planCode == 709) {
      $head1Invest = 1;
      $head2Invest = 8;
      $head3Invest = 20;
      $head4Invest = 59;
      $head5Invest = 80;
      $head_id = 80;
    }
    if ($planCode == 708) {
      $head1Invest = 1;
      $head2Invest = 8;
      $head3Invest = 20;
      $head4Invest = 59;
      $head5Invest = 85;
      $head_id = 85;
    }
    if ($planCode == 705) {
      $head1Invest = 1;
      $head2Invest = 8;
      $head3Invest = 20;
      $head4Invest = 57;
      $head5Invest = 79;
      $head_id = 79;
    }
    if ($planCode == 707) {
      $head1Invest = 1;
      $head2Invest = 8;
      $head3Invest = 20;
      $head4Invest = 59;
      $head5Invest = 81;
      $head_id = 81;
    }
    if ($planCode == 713) {
      $head1Invest = 1;
      $head2Invest = 8;
      $head3Invest = 20;
      $head4Invest = 59;
      $head5Invest = 84;
      $head_id = 84;
    }
    if ($planCode == 710) {
      $head1Invest = 1;
      $head2Invest = 8;
      $head3Invest = 20;
      $head4Invest = 58;
      $head5Invest = NULL;
      $head_id = 58;
    }
    if ($planCode == 712) {
      $head1Invest = 1;
      $head2Invest = 8;
      $head3Invest = 20;
      $head4Invest = 57;
      $head5Invest = 78;
      $head_id = 78;
    }
    if ($planCode == 706) {
      $head1Invest = 1;
      $head2Invest = 8;
      $head3Invest = 20;
      $head4Invest = 57;
      $head5Invest = 77;
      $head_id = 77;
    }
    if ($planCode == 704) {
      $head1Invest = 1;
      $head2Invest = 8;
      $head3Invest = 20;
      $head4Invest = 59;
      $head5Invest = 83;
      $head_id = 83;
    }
    if ($planCode == 718) {
      $head1Invest = 1;
      $head2Invest = 8;
      $head3Invest = 20;
      $head4Invest = 59;
      $head5Invest = 82;
      $head_id = 82;
    }
    if ($planCode == 721) {
      $head1Invest = 1;
      $head2Invest = 8;
      $head3Invest = 207;
      $head4Invest = 59;
      $head5Invest = 82;
      $head_id = 207;
    }
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
    if ($payment_mode == 1) {  // cheque moade 
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
      $allTranRDcheque = CommanTransactionsController::createAllHeadTransaction($refIdRD, $branch_id, $bank_id = NULL, $bank_ac_id = NULL, $head41, $type, $sub_type, $type_id, $createDayBook, $associate_id, $memberId, $branch_id_to = NULL, $branch_id_from = NULL, $amount, $amount, $amount, $rdDes, 'DR', $headPaymentModeRD, $currency_code, $amount_to_id = NULL, $amount_to_name = NULL, $amount_from_id = NULL, $amount_from_name = NULL, $jv_unique_id = NULL, $v_no, $v_date, $ssb_account_id_from, $ssb_account_id_to = NULL, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, 0, $cheque_id, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_from_id = NULL, $cheque_bank_ac_from_id = NULL, $cheque_bank_to, $cheque_bank_ac_to, getSamraddhBank($cheque_bank_to)->bank_name, $cheque_bank_to_branch = NULL, getSamraddhBankAccountId($cheque_bank_ac_to)->account_no, getSamraddhBankAccountId($cheque_bank_ac_to)->ifsc_code, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_from_id = NULL, $transction_bank_from_ac_id = NULL, $transction_bank_to, $transction_bank_ac_to, $transction_bank_to_name = NULL, $transction_bank_to_ac_no = NULL, $transction_bank_to_branch = NULL, $transction_bank_to_ifsc = NULL, $transction_date, $created_by, $created_by_id);
      /*$allTranRDcheque=CommanTransactionsController::createAllTransactionNew($refIdRD,$branch_id,$bank_id=NULL,$bank_ac_id=NULL,$head11,$head21,$head31,$head41,$head51,$type,$sub_type,$type_id,$associate_id,$memberId,$branch_id_to=NULL,$branch_id_from=NULL,$opening_balance=NULL,$amount,$closing_balance=NULL,$rdDes,'CR',$headPaymentModeRD,$currency_code,$amount_to_id=NULL,$amount_to_name=NULL,$amount_from_id=NULL,$amount_from_name=NULL,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at,$updated_at,$createDayBook);*/
      //bank entry
      $bankCheque = CommanTransactionsController::samraddhBankDaybookNew($refIdRD, $cheque_bank_to, $cheque_bank_ac_to, $type, $sub_type, $type_id, $createDayBook, $associate_id, $memberId, $branch_id, $opening_balance = NULL, $amount, $closing_balance = NULL, $rdDes, $rdDesDR, $rdDesCR, 'CR', $headPaymentModeRD, $currency_code, $amount_to_id = NULL, $amount_to_name = NULL, $amount_from_id = NULL, $amount_from_name = NULL, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, getSamraddhBank($cheque_bank_ac_to)->bank_name, getSamraddhBankAccountId($cheque_bank_ac_to)->account_no, $transction_bank_to_branch = NULL, getSamraddhBankAccountId($cheque_bank_ac_to)->ifsc_code, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $jv_unique_id = NULL, 0, $cheque_id, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL);
      /*$bankCheque=CommanTransactionsController::createSamraddhBankDaybookNew($refIdRD,$cheque_bank_to,$cheque_bank_ac_to,$type,$sub_type,$type_id,$associate_id,$memberId,$branch_id,$opening_balance=NULL,$amount,$closing_balance=NULL,$rdDes,$rdDesDR,$rdDesCR,'CR',$headPaymentModeRD,$currency_code,$amount_to_id=NULL,$amount_to_name=NULL,$amount_from_id=NULL,$amount_from_name=NULL,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at,$updated_at,$createDayBook);*/
      //bank balence
      $bankClosing = CommanTransactionsController::checkCreateBankClosing($cheque_bank_to, $cheque_bank_ac_to, $created_at, $amount, 0);
    } elseif ($payment_mode == 2) {  //online transaction
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
      $head111 = 2;
      $head211 = 10;
      $head311 = 27;
      $head411 = $getBHead->account_head_id;
      $head511 = NULL;
      $rdDesDR = 'Bank A/c Dr ' . $amount . '/-';
      $rdDesCR = 'To ' . $planDetail->name . '(' . $investmentAccountNoRd . ')  A/c Cr ' . $amount . '/-';
      $rdDes = 'Amount received for Account opening ' . $planDetail->name . '(' . $investmentAccountNoRd . ') through online transaction(' . $transction_no . ')';
      $rdDesMem = 'Account opening ' . $planDetail->name . '(' . $investmentAccountNoRd . ') through online transaction(' . $transction_no . ')';
      //bank head entry
      $allTranRDonline = CommanTransactionsController::createAllHeadTransaction($refIdRD, $branch_id, $bank_id = NULL, $bank_ac_id = NULL, $head411, $type, $sub_type, $type_id, $createDayBook, $associate_id, $memberId, $branch_id_to = NULL, $branch_id_from = NULL, $amount, $amount, $amount, $rdDes, 'DR', $headPaymentModeRD, $currency_code, $amount_to_id = NULL, $amount_to_name = NULL, $amount_from_id = NULL, $amount_from_name = NULL, $jv_unique_id = NULL, $v_no, $v_date, $ssb_account_id_from, $ssb_account_id_to = NULL, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $cheque_type = NULL, $cheque_id = NULL, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_from_id = NULL, $cheque_bank_ac_from_id = NULL, $cheque_bank_to, $cheque_bank_ac_to, $cheque_bank_to_name = NULL, $cheque_bank_to_branch = NULL, $cheque_bank_to_ac_no = NULL, $cheque_bank_to_ifsc = NULL, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_from_id = NULL, $transction_bank_from_ac_id = NULL, $transction_bank_to, $transction_bank_ac_to, getSamraddhBank($online_deposit_bank_id)->bank_name, getSamraddhBankAccountId($online_deposit_bank_ac_id)->account_no, $transction_bank_to_branch = NULL, getSamraddhBankAccountId($online_deposit_bank_ac_id)->ifsc_code, $transction_date, $created_by, $created_by_id);
      /*$allTranRDonline=CommanTransactionsController::createAllTransactionNew($refIdRD,$branch_id,$bank_id=NULL,$bank_ac_id=NULL,$head111,$head211,$head311,$head411,$head511,$type,$sub_type,$type_id,$associate_id,$memberId,$branch_id_to=NULL,$branch_id_from=NULL,$opening_balance=NULL,$amount,$closing_balance=NULL,$rdDes,'CR',$headPaymentModeRD,$currency_code,$amount_to_id=NULL,$amount_to_name=NULL,$amount_from_id=NULL,$amount_from_name=NULL,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at,$updated_at,$createDayBook);*/
      //bank entry
      $bankonline = CommanTransactionsController::samraddhBankDaybookNew($refIdRD, $cheque_bank_to, $cheque_bank_ac_to, $type, $sub_type, $type_id, $createDayBook, $associate_id, $memberId, $branch_id, $amount, $amount, $amount, $rdDes, $rdDesDR, $rdDesCR, 'CR', $headPaymentModeRD, $currency_code, $amount_to_id = NULL, $amount_to_name = NULL, $amount_from_id = NULL, $amount_from_name = NULL, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, getSamraddhBank($online_deposit_bank_id)->bank_name, getSamraddhBankAccountId($online_deposit_bank_ac_id)->account_no, $transction_bank_to_branch = NULL, getSamraddhBankAccountId($online_deposit_bank_ac_id)->ifsc_code, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $jv_unique_id = NULL, NULL, $cheque_id = NULL, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL);
      /*$bankonline=CommanTransactionsController::createSamraddhBankDaybookNew($refIdRD,$transction_bank_to,$transction_bank_ac_to,$type,$sub_type,$type_id,$associate_id,$memberId,$branch_id,$opening_balance=NULL,$amount,$closing_balance=NULL,$rdDes,$rdDesDR,$rdDesCR,'CR',$headPaymentModeRD,$currency_code,$amount_to_id=NULL,$amount_to_name=NULL,$amount_from_id=NULL,$amount_from_name=NULL,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at,$updated_at,$createDayBook);*/
      //bank balence
      $bankClosing = CommanTransactionsController::checkCreateBankClosing($transction_bank_to, $transction_bank_ac_to, $created_at, $amount, 0);
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
      $allTranRDSSB = CommanTransactionsController::createAllHeadTransaction($refIdRD, $branch_id, $bank_id = NULL, $bank_ac_id = NULL, $head4rdSSB, $type, $sub_type, $type_id, $createDayBook, $associate_id, $memberId, $branch_id_to = NULL, $branch_id_from = NULL, $amount, $amount, $amount, $rdDes, 'DR', $headPaymentModeRD, $currency_code, $amount_to_id = NULL, $amount_to_name = NULL, $amount_from_id = NULL, $amount_from_name = NULL, $jv_unique_id = NULL, $v_no, $v_date, $ssb_account_id_from, $ssb_account_id_to = NULL, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $cheque_type = NULL, $cheque_id = NULL, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_from_id = NULL, $cheque_bank_ac_from_id = NULL, $cheque_bank_to, $cheque_bank_ac_to, $cheque_bank_to_name = NULL, $cheque_bank_to_branch = NULL, $cheque_bank_to_ac_no = NULL, $cheque_bank_to_ifsc = NULL, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_from_id = NULL, $transction_bank_from_ac_id = NULL, $transction_bank_to, $transction_bank_ac_to, $transction_bank_to_name = NULL, $transction_bank_to_ac_no = NULL, $transction_bank_to_branch = NULL, $transction_bank_to_ifsc = NULL, $transction_date, $created_by, $created_by_id);
      /*$allTranRDSSB=CommanTransactionsController::createAllTransactionNew($refIdRD,$branch_id,$bank_id=NULL,$bank_ac_id=NULL,$head1rdSSB,$head2rdSSB,$head3rdSSB,$head4rdSSB,$head5rdSSB,$type,$sub_type,$type_id,$associate_id,$memberId,$branch_id_to=NULL,$branch_id_from=NULL,$opening_balance=NULL,$amount,$closing_balance=NULL,$rdDes,'DR',$headPaymentModeRD,$currency_code,$amount_to_id=NULL,$amount_to_name=NULL,$amount_from_id=NULL,$amount_from_name=NULL,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at,$updated_at,$createDayBook);*/
      $branchClosingSSB = CommanTransactionsController::checkCreateBranchClosingDr($branch_id, $created_at, $amount, 0);
      // Member transaction  +
      $memberTranInvest77 = CommanTransactionsController::memberTransactionNew($refIdRD, '4', '47', $createDayBook, $ssb_account_id_from, $associate_id, $ssbDetals->member_id, $branch_id, $bank_id = NULL, $account_id = NULL, $amount, $SSBDescTran, 'DR', $headPaymentModeRD, $currency_code, $amount_to_id = $type_id, $amount_to_name = $planDetail->name, $amount_from_id = NULL, $amount_from_name = NULL, $v_no, $v_date, $ssb_account_id_from = NULL, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL);
      /*$memberTranInvest77 = CommanTransactionsController::createMemberTransactionNew($refIdRD,'4','47',$ssb_account_id_from,$associate_id,$ssbDetals->member_id,$branch_id,$bank_id=NULL,$account_id=NULL,$amount,$SSBDescTran,'DR',$headPaymentModeRD,$currency_code,$amount_to_id=$type_id,$amount_to_name=$planDetail->name,$amount_from_id=NULL,$amount_from_name=NULL,$v_no,$v_date,$ssb_account_id_from=NULL,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at,$updated_at,$createDayBook);*/
    } else {
      $headPaymentModeRD = 0;
      $head1rdC = 2;
      $head2rdC = 10;
      $head3rdC = 28;
      $head4rdC = 71;
      $head5rdC = NULL;
      $rdDesDR = 'Cash A/c Dr ' . $amount . '/-';
      $rdDesCR = 'To ' . $planDetail->name . '(' . $investmentAccountNoRd . ')  A/c Cr ' . $amount . '/-';
      $rdDes = 'Amount received for Account opening ' . $planDetail->name . '(' . $investmentAccountNoRd . ') through cash(' . getBranchCode($branch_id)->branch_code . ')';
      $rdDesMem = 'Account opening ' . $planDetail->name . '(' . $investmentAccountNoRd . ') through cash(' . getBranchCode($branch_id)->branch_code . ')';
      // branch cash  head entry +
      $allTranRDcash = CommanTransactionsController::createAllHeadTransaction($refIdRD, $branch_id, $bank_id = NULL, $bank_ac_id = NULL, $head3rdC, $type, $sub_type, $type_id, $createDayBook, $associate_id, $memberId, $branch_id_to = NULL, $branch_id_from = NULL, $amount, $amount, $amount, $rdDes, 'DR', $headPaymentModeRD, $currency_code, $amount_to_id = NULL, $amount_to_name = NULL, $amount_from_id = NULL, $amount_from_name = NULL, $jv_unique_id = NULL, $v_no, $v_date, $ssb_account_id_from, $ssb_account_id_to = NULL, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $cheque_type = NULL, $cheque_id = NULL, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_from_id = NULL, $cheque_bank_ac_from_id = NULL, $cheque_bank_to, $cheque_bank_ac_to, $cheque_bank_to_name = NULL, $cheque_bank_to_branch = NULL, $cheque_bank_to_ac_no = NULL, $cheque_bank_to_ifsc = NULL, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_from_id = NULL, $transction_bank_from_ac_id = NULL, $transction_bank_to, $transction_bank_ac_to, $transction_bank_to_name = NULL, $transction_bank_to_ac_no = NULL, $transction_bank_to_branch = NULL, $transction_bank_to_ifsc = NULL, $transction_date, $created_by, $created_by_id);
      /*$allTranRDcash=CommanTransactionsController::createAllTransactionNew($refIdRD,$branch_id,$bank_id=NULL,$bank_ac_id=NULL,$head1rdC,$head2rdC,$head3rdC,$head4rdC,$head5rdC,$type,$sub_type,$type_id,$associate_id,$memberId,$branch_id_to=NULL,$branch_id_from=NULL,$opening_balance=NULL,$amount,$closing_balance=NULL,$rdDes,$payment_type_rd,$headPaymentModeRD,$currency_code,$amount_to_id=NULL,$amount_to_name=NULL,$amount_from_id=NULL,$amount_from_name=NULL,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at,$updated_at,$createDayBook);*/
      //Balance   entry +
      $branchCash = CommanTransactionsController::checkCreateBranchCash($branch_id, $created_at, $amount, 0);
    }
    //branch day book entry +
    $daybookInvest = CommanTransactionsController::branchDayBookNew($refIdRD, $branch_id, $type, $sub_type, $type_id, $createDayBook, $associate_id, $memberId, $branch_id_to = NULL, $branch_id_from = NULL, $opening_balance = NULL, $amount, $closing_balance = NULL, $rdDes, $rdDesDR, $rdDesCR, $payment_type_rd, $headPaymentModeRD, $currency_code, $amount_to_id = NULL, $amount_to_name = NULL, $amount_from_id = NULL, $amount_from_name = NULL, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $is_contra = NULL, $contra_id = NULL, $created_at, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL);
    /*$daybookInvest = CommanTransactionsController::createBranchDayBookNew($refIdRD,$branch_id,$type,$sub_type,$type_id,$associate_id,$memberId,$branch_id_to=NULL,$branch_id_from=NULL,$opening_balance=NULL,$amount,$closing_balance=NULL,$rdDes,$rdDesDR,$rdDesCR,$payment_type_rd,$headPaymentModeRD,$currency_code,$amount_to_id=NULL,$amount_to_name=NULL,$amount_from_id=NULL,$amount_from_name=NULL,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$is_contra=NULL,$contra_id=NULL,$created_at,$updated_at,$createDayBook);*/
    // Investment head entry +
    $allTranInvest = CommanTransactionsController::createAllHeadTransaction($refIdRD, $branch_id, $bank_id = NULL, $bank_ac_id = NULL, $head_id, $type, $sub_type, $type_id, $createDayBook, $associate_id, $memberId, $branch_id_to = NULL, $branch_id_from = NULL, $amount, $amount, $amount, $rdDes, $payment_type_rd, $headPaymentModeRD, $currency_code, $amount_to_id = NULL, $amount_to_name = NULL, $amount_from_id = NULL, $amount_from_name = NULL, $jv_unique_id = NULL, $v_no, $v_date, $ssb_account_id_from, $ssb_account_id_to = NULL, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $cheque_type = NULL, $cheque_id = NULL, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_from_id = NULL, $cheque_bank_ac_from_id = NULL, $cheque_bank_to, $cheque_bank_ac_to, $cheque_bank_to_name = NULL, $cheque_bank_to_branch = NULL, $cheque_bank_to_ac_no = NULL, $cheque_bank_to_ifsc = NULL, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_from_id = NULL, $transction_bank_from_ac_id = NULL, $transction_bank_to, $transction_bank_ac_to, $transction_bank_to_name = NULL, $transction_bank_to_ac_no = NULL, $transction_bank_to_branch = NULL, $transction_bank_to_ifsc = NULL, $transction_date, $created_by, $created_by_id);
    /*$allTranInvest = CommanTransactionsController::createAllTransactionNew($refIdRD,$branch_id,$bank_id=NULL,$bank_ac_id=NULL,$head1Invest,$head2Invest,$head3Invest,$head4Invest,$head5Invest,$type,$sub_type,$type_id,$associate_id,$memberId,$branch_id_to=NULL,$branch_id_from=NULL,$opening_balance=NULL,$amount,$closing_balance=NULL,$rdDes,$payment_type_rd,$headPaymentModeRD,$currency_code,$amount_to_id=NULL,$amount_to_name=NULL,$amount_from_id=NULL,$amount_from_name=NULL,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at,$updated_at,$createDayBook);*/
    // Member transaction  +
    $memberTranInvest = CommanTransactionsController::memberTransactionNew($refIdRD, $type, $sub_type, $type_id, $createDayBook, $associate_id, $memberId, $branch_id, $bank_id = NULL, $account_id = NULL, $amount, $rdDesMem, $payment_type_rd, $headPaymentModeRD, $currency_code, $amount_to_id = NULL, $amount_to_name = NULL, $amount_from_id = NULL, $amount_from_name = NULL, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL);
    /*$memberTranInvest = CommanTransactionsController::createMemberTransactionNew($refIdRD,$type,$sub_type,$type_id,$associate_id,$memberId,$branch_id,$bank_id=NULL,$account_id=NULL,$amount,$rdDesMem,$payment_type_rd,$headPaymentModeRD,$currency_code,$amount_to_id=NULL,$amount_to_name=NULL,$amount_from_id=NULL,$amount_from_name=NULL,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at,$updated_at,$createDayBook);*/
    /******** Balance   entry ***************/
    $branchClosing = CommanTransactionsController::checkCreateBranchClosing($branch_id, $created_at, $amount, 0);
  }

  public function investHeadCreateSSB($amount, $globaldate, $investmentId, $planId, $cheque_no, $cheque_id, $cheque_date, $online_transction_no, $online_transction_date, $online_deposit_bank_id, $online_deposit_bank_ac_id, $branch_id, $associate_id, $memberId, $ssbId, $createDayBook, $payment_mode, $investmentAccountNoRd)
  {
    $amount = $amount;
    $daybookRefRD = CommanTransactionsController::createBranchDayBookReferenceNew($amount, $globaldate);
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
    $allTranRDcash = CommanTransactionsController::createAllHeadTransaction($refIdRD, $branch_id, $bank_id = NULL, $bank_ac_id = NULL, $head3rdC, $type, $sub_type, $type_id, $createDayBook, $associate_id, $memberId, $branch_id_to = NULL, $branch_id_from = NULL, $amount, $amount, $amount, $rdDes, 'DR', $headPaymentModeRD, $currency_code, $amount_to_id = NULL, $amount_to_name = NULL, $amount_from_id = NULL, $amount_from_name = NULL, $jv_unique_id = NULL, $v_no, $v_date, $ssb_account_id_from, $ssb_account_id_to = NULL, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $cheque_type = NULL, $cheque_id = NULL, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_from_id = NULL, $cheque_bank_ac_from_id = NULL, $cheque_bank_to = NULL, $cheque_bank_ac_to = NULL, NULL, $cheque_bank_to_branch = NULL, NULL, NULL, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_from_id = NULL, $transction_bank_from_ac_id = NULL, $transction_bank_to, $transction_bank_ac_to, $transction_bank_to_name = NULL, $transction_bank_to_ac_no = NULL, $transction_bank_to_branch = NULL, $transction_bank_to_ifsc = NULL, $transction_date, $created_by, $created_by_id);
    /*$allTranRDcash=CommanTransactionsController::createAllTransactionNew($refIdRD,$branch_id,$bank_id=NULL,$bank_ac_id=NULL,$head1rdC,$head2rdC,$head3rdC,$head4rdC,$head5rdC,$type,$sub_type,$type_id,$associate_id,$memberId,$branch_id_to=NULL,$branch_id_from=NULL,$opening_balance=NULL,$amount,$closing_balance=NULL,$rdDes,$payment_type_rd,$headPaymentModeRD,$currency_code,$amount_to_id=NULL,$amount_to_name=NULL,$amount_from_id=NULL,$amount_from_name=NULL,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at,$updated_at,$createDayBook);*/
    //Balance   entry +
    $branchCash = CommanTransactionsController::checkCreateBranchCash($branch_id, $created_at, $amount, 0);
    //branch day book entry +
    $daybookInvest = CommanTransactionsController::branchDayBookNew($refIdRD, $branch_id, $type, $sub_type, $type_id, $createDayBook, $associate_id, $memberId, $branch_id_to = NULL, $branch_id_from = NULL, $opening_balance = NULL, $amount, $closing_balance = NULL, $rdDes, $rdDesDR, $rdDesCR, $payment_type_rd, $headPaymentModeRD, $currency_code, $amount_to_id = NULL, $amount_to_name = NULL, $amount_from_id = NULL, $amount_from_name = NULL, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $is_contra = NULL, $contra_id = NULL, $created_at, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL);
    /*$daybookInvest = CommanTransactionsController::createBranchDayBookNew($refIdRD,$branch_id,$type,$sub_type,$type_id,$associate_id,$memberId,$branch_id_to=NULL,$branch_id_from=NULL,$opening_balance=NULL,$amount,$closing_balance=NULL,$rdDes,$rdDesDR,$rdDesCR,$payment_type_rd,$headPaymentModeRD,$currency_code,$amount_to_id=NULL,$amount_to_name=NULL,$amount_from_id=NULL,$amount_from_name=NULL,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$is_contra=NULL,$contra_id=NULL,$created_at,$updated_at,$createDayBook);*/
    // Investment head entry +
    $allTranInvest = CommanTransactionsController::createAllHeadTransaction($refIdRD, $branch_id, $bank_id = NULL, $bank_ac_id = NULL, $head4Invest, $type, $sub_type, $type_id, $createDayBook, $associate_id, $memberId, $branch_id_to = NULL, $branch_id_from = NULL, $amount, $amount, $amount, $rdDes, $payment_type_rd, $headPaymentModeRD, $currency_code, $amount_to_id = NULL, $amount_to_name = NULL, $amount_from_id = NULL, $amount_from_name = NULL, $jv_unique_id = NULL, $v_no, $v_date, $ssb_account_id_from, $ssb_account_id_to = NULL, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $cheque_type = NULL, $cheque_id = NULL, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_from_id = NULL, $cheque_bank_ac_from_id = NULL, $cheque_bank_to = NULL, $cheque_bank_ac_to = NULL, NULL, $cheque_bank_to_branch = NULL, NULL, NULL, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_from_id = NULL, $transction_bank_from_ac_id = NULL, $transction_bank_to, $transction_bank_ac_to, $transction_bank_to_name = NULL, $transction_bank_to_ac_no = NULL, $transction_bank_to_branch = NULL, $transction_bank_to_ifsc = NULL, $transction_date, $created_by, $created_by_id);
    /*$allTranInvest = CommanTransactionsController::createAllTransactionNew($refIdRD,$branch_id,$bank_id=NULL,$bank_ac_id=NULL,$head1Invest,$head2Invest,$head3Invest,$head4Invest,$head5Invest,$type,$sub_type,$type_id,$associate_id,$memberId,$branch_id_to=NULL,$branch_id_from=NULL,$opening_balance=NULL,$amount,$closing_balance=NULL,$rdDes,$payment_type_rd,$headPaymentModeRD,$currency_code,$amount_to_id=NULL,$amount_to_name=NULL,$amount_from_id=NULL,$amount_from_name=NULL,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at,$updated_at,$createDayBook);*/
    // Member transaction  +
    $memberTranInvest = CommanTransactionsController::memberTransactionNew($refIdRD, $type, $sub_type, $type_id, $createDayBook, $associate_id, $memberId, $branch_id, $bank_id = NULL, $account_id = NULL, $amount, $rdDesMem, $payment_type_rd, $headPaymentModeRD, $currency_code, $amount_to_id = NULL, $amount_to_name = NULL, $amount_from_id = NULL, $amount_from_name = NULL, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL);
    /*$memberTranInvest = CommanTransactionsController::createMemberTransactionNew($refIdRD,$type,$sub_type,$type_id,$associate_id,$memberId,$branch_id,$bank_id=NULL,$account_id=NULL,$amount,$rdDesMem,$payment_type_rd,$headPaymentModeRD,$currency_code,$amount_to_id=NULL,$amount_to_name=NULL,$amount_from_id=NULL,$amount_from_name=NULL,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at,$updated_at,$createDayBook);*/
    /******** Balance   entry ***************/
    $branchClosing = CommanTransactionsController::checkCreateBranchClosing($branch_id, $created_at, $amount, 0);
  }
  /**
   * Store a newly created plan in storage.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\Response
   */
  public function Store(InvestmentPlanRequest $request)
  {
    $validated = $request->validated();

    $plantype = $request->input('plan_type');

    $response = Investment::storeInvestment($request, $this->repository);



    $insertedid = $response['insertedid'];

    if ($response['status']) {
      return redirect('branch/investment/recipt/' . $insertedid);
      return back()->with('success', 'Saved Successfully!');
    } else {
      return back()->with('alert', $response['msg']);
    }

    // DB::beginTransaction();
    // try {
    //   if ($plantype == 'saving-account' || $plantype == "saving-account-child") {

    //     $request->request->add(['payment-mode' => 0]);
    //   } else {
    //     if ($request->input('payment-mode') == 1) {
    //       $getChequeDetail = \App\Models\ReceivedCheque::where('id', $request['cheque_id'])->where('status', 3)->first(['id', 'amount', 'status']);
    //       if (!empty($getChequeDetail)) {
    //         return back()->with('alert', 'Cheque already used select another cheque');
    //       } else {
    //         $getamount = \App\Models\ReceivedCheque::where('id', $request['cheque_id'])->first(['id', 'amount']);
    //         //echo $getamount->amount.'=='.number_format((float)$request['amount'], 4, '.', '');die;
    //         if ($getamount->amount != number_format((float)$request['amount'], 4, '.', '')) {
    //           return back()->with('alert', 'Investment  amount is not equal to cheque amount');
    //         }
    //       }
    //     }
    //   }

    //   $stateid = getBranchState(Auth::user()->username);
    //   $globaldate = checkMonthAvailability(date('d'), date('m'), date('Y'), $stateid);
    //   Session::put('created_at', $request['created_at']);
    //   //get login user branch id(branch manager)pass auth id
    //   $getBranchId = getUserBranchId(Auth::user()->id);
    //   $branch_id = $getBranchId->id;
    //   $getBranchCode = getBranchCode($branch_id);
    //   $branchCode = $getBranchCode->branch_code;
    //   //get login user branch id(branch manager)pass auth id
    //   $faCode = getPlanCode($request['investmentplan']);
    //   $planId = $request['investmentplan'];
    //   $investmentMiCode = getInvesmentMiCodeNew($planId, $branch_id);
    //   if (!empty($investmentMiCode)) {
    //     $miCodeAdd = $investmentMiCode->mi_code + 1;
    //   } else {
    //     $miCodeAdd = 1;
    //   }
    //   $miCode = str_pad($miCodeAdd, 5, '0', STR_PAD_LEFT);
    //   $miCodeBig   = str_pad($miCodeAdd, 5, '0', STR_PAD_LEFT);
    //   $passbook =  '720' . $branchCode . $faCode . $miCodeBig;
    //   $certificate = '719' . $branchCode . $faCode . $miCodeBig;
    //   // Invesment Account no 
    //   $investmentAccount = $branchCode . $faCode . $miCode;
    //   $data = $this->getData($request->all(), $type, $miCode, $investmentAccount, $branch_id);
    //   if ($faCode == 705 || $faCode == 706 || $faCode == 712) {
    //     $data['certificate_no'] = $certificate;
    //   } else {
    //     if ($faCode != 703) {
    //       $data['passbook_no'] = $passbook;
    //     }
    //   }
    //   $sAccount = $this->getSavingAccountDetails($request->input('memberAutoId'));

    //   if (count($sAccount) > 0) {
    //     $ssbAccountNumber = $sAccount[0]->account_no;
    //     $ssbId = $sAccount[0]->id;
    //     $ssbBalance = $sAccount[0]->balance;
    //   } else {
    //     $ssbAccountNumber = '';
    //     $ssbId = '';
    //     $ssbBalance = '';
    //   }
    //   $received_cheque_id = $cheque_id = NULL;
    //   $cheque_deposit_bank_id = NULL;
    //   $cheque_deposit_bank_ac_id = NULL;
    //   $cheque_no = NULL;
    //   $cheque_date = $pdate = NULL;

    //   $online_deposit_bank_id = NULL;
    //   $online_deposit_bank_ac_id = NULL;
    //   $online_transction_no = NULL;
    //   $online_transction_date = NULL;

    //   if ($request->input('payment-mode') == 1) {
    //     $chequeDetail = \App\Models\ReceivedCheque::where('id', $request['cheque_id'])->first();
    //     $received_cheque_id = $cheque_id = $request['cheque_id'];
    //     $cheque_deposit_bank_id = $chequeDetail->deposit_bank_id;
    //     $cheque_deposit_bank_ac_id = $chequeDetail->deposit_account_id;
    //     $cheque_no = $request['cheque-number'];
    //     $cheque_date = $pdate = date("Y-m-d", strtotime(str_replace('/', '-', $request['cheque-date'])));
    //   }
    //   if ($request->input('payment-mode') == 2) {
    //     $online_deposit_bank_id = $request['rd_online_bank_id'];
    //     $online_deposit_bank_ac_id = $request['rd_online_bank_ac_id'];
    //     $online_transction_no = $request['transaction-id'];
    //     $online_transction_date = $pdate = date("Y-m-d", strtotime(str_replace('/', '-', $request['date'])));
    //   }

    //   switch ($plantype) {
    //     case "saving-account":
    //       $is_primary = 0;
    //       $description = 'SSB Account opening';
    //       if (SavingAccount::where('member_id', $request['memberAutoId'])->count() > 0) {
    //         return back()->with('alert', 'Your saving account already created!');
    //       }

    //       $res = Memberinvestments::create($data);
    //       $insertedid = $res->id;
    //       $savingAccountId = $res->account_number;
    //       $createAccount = CommanTransactionsController::createSavingAccountDescriptionModify($request['memberAutoId'], $branch_id, $branchCode, $request['amount'], 0, $insertedid, $miCode, $investmentAccount, $is_primary, $faCode, $description, $request['associatemid']);
    //       $mRes = Member::find($request['memberAutoId']);
    //       $mData['ssb_account'] = $investmentAccount;
    //       $mRes->update($mData);
    //       $satRefId = CommanTransactionsController::createTransactionReferences($createAccount['ssb_transaction_id'], $insertedid);

    //       $ssbAccountId = $createAccount['ssb_id'];
    //       $amountArraySsb = array('1' => $request['amount']);

    //       $amount_deposit_by_name = substr($request['member_name'], 0, strpos($request['member_name'], "-"));
    //       $ssbCreateTran = CommanTransactionsController::createTransaction($satRefId, 1, $ssbAccountId, $request['memberAutoId'], $branch_id, $branchCode, $amountArraySsb, 0, $amount_deposit_by_name, $request['memberAutoId'], $investmentAccount, $cheque_dd_no = '0', $bank_name = NULL, $branch_name = NULL, $payment_date = NULL, $online_payment_id = NULL, $online_payment_by = NULL, $saving_account_id = 0, 'CR');

    //       //$createDayBook = CommanTransactionsController::createDayBook($ssbCreateTran,$satRefId,1,$insertedid,$request['associatemid'],$request['memberAutoId'],$request['amount'],$request['amount'],$withdrawal=0,$description,$ssbAccountNumber,$branch_id,$branchCode,$amountArraySsb,0,$amount_deposit_by_name,$request['memberAutoId'],$investmentAccount,$request['cheque-number'],$request['bank-name'],$request['branch-name'],date('Y-m-d'),$request['transaction-id'],$online_payment_by=NULL,$ssbId,'CR');
    //       // ---------------------------  Day book modify --------------------------    
    //       $ppmode = 0;
    //       if ($request->input('payment-mode') == 1) { //cheque
    //         $ppmode = 1;
    //       }
    //       if ($request->input('payment-mode') == 2) { //onine
    //         $ppmode = 3;
    //       }
    //       if ($request->input('payment-mode') == 3) {
    //         //ssb
    //         $ppmode = 4;
    //       }
    //       $createDayBook = CommanTransactionsController::createDayBookNew($ssbCreateTran, $satRefId, 1, $ssbAccountId, $request['associatemid'], $request['memberAutoId'], $request['amount'], $request['amount'], $withdrawal = 0, $description, $ssbAccountNumber, $branch_id, $branchCode, $amountArraySsb, $ppmode, $amount_deposit_by_name, $request['memberAutoId'], $investmentAccount, $request['cheque-number'], $request['bank-name'], $request['branch-name'], $pdate, $online_transction_no, $online_payment_by = NULL, $ssbId, 'CR', $received_cheque_id, $cheque_deposit_bank_id, $cheque_deposit_bank_ac_id, $online_deposit_bank_id, $online_deposit_bank_ac_id);
    //       // ---------------------------  HEAD IMPLEMENT --------------------------
    //       $this->investHeadCreateSSB($request['amount'], $globaldate, $ssbAccountId, $planId, $cheque_no, $cheque_id, $cheque_date, $online_transction_no, $online_transction_date, $online_deposit_bank_id, $online_deposit_bank_ac_id, $branch_id, $request['associatemid'], $request['memberAutoId'], $ssbId, $createAccount['ssb_transaction_id'], $request->input('payment-mode'), $investmentAccount);
    //       //--------------------------------HEAD IMPLEMENT  -------------------------

    //       $transaction = $this->transactionData($satRefId, $request->all(), $insertedid, $type, $ssbCreateTran);
    //       $res = Investmentplantransactions::create($transaction);

    //       $fNominee = $this->getFirstNomineeData($request->all(), $insertedid, $type);
    //       $res = Memberinvestmentsnominees::create($fNominee);

    //       if ($request['second_nominee_add'] == 1) {
    //         $sNominee = $this->getSecondNomineeData($request->all(), $insertedid, $type);
    //         $res = Memberinvestmentsnominees::create($sNominee);
    //       }
    //       if ($request['stationary_charge'] > 0) {
    //         $this->stationaryCharges($insertedid);
    //       }
    //       $savingAccountDetail = SavingAccount::where('account_no', $savingAccountId)->first();
    //       //$text = 'Dear Member, Thank You for Choosing S.B. Micro Finance association Your saving A/C'. $savingAccountDetail->account_no.' is Created on '
    //       //.$savingAccountDetail->created_at->format('d M Y').' With Rs. '. round($request['amount'],2).' Cur Bal: '. round($savingAccountDetail->balance, 2).'. Thanks Have a good day';
    //       $text = 'Dear Member, Thank You for Choosing S.B. Micro Finance association Your saving A/c No.' . $savingAccountDetail->account_no . ' is Credited on '
    //         . $savingAccountDetail->created_at->format('d M Y') . ' With Rs. ' . round($request['amount'], 2) . '. Thanks Have a good day';
    //       $temaplteId = 1207161519023692218;
    //       break;
    //     case "saving-account-child":
    //       $description = 'SSB Account Child';

    //       $res = Memberinvestments::create($data);

    //       $insertedid = $res->id;

    //       $investmentDetail = Memberinvestments::find($res->id);

    //       $fNominee = $this->getFirstNomineeData($request->all(), $insertedid, $type);

    //       $res = Memberinvestmentsnominees::create($fNominee);

    //       if ($request['second_nominee_add'] == 1) {

    //         $sNominee = $this->getSecondNomineeData($request->all(), $insertedid, $type);

    //         $res = Memberinvestmentsnominees::create($sNominee);
    //       }

    //       $paymentData = $this->getPaymentMethodData($request->all(), $insertedid, $type);

    //       $res = Memberinvestmentspayments::create($paymentData);

    //       $amountArray = array('1' => $request->input('amount'));

    //       $amount_deposit_by_name = $request->input('firstname') . ' ' . $request->input('lastname');

    //       if ($request->input('payment-mode') == 3) {

    //         //$savingTransaction = $this->savingAccountTransactionData($request->all(),$insertedid,$type);

    //         $savingTransaction = $this->savingAccountTransactionDataNew($request->all(), $insertedid, $type, 'SRD', $investmentAccount);



    //         $res = SavingAccountTranscation::create($savingTransaction);

    //         $satRefId = CommanTransactionsController::createTransactionReferences($res->id, $insertedid);

    //         $sAccountId = $ssbId;

    //         $sAccountAmount = $ssbBalance - $request->input('amount');



    //         $sResult = SavingAccount::find($sAccountId);

    //         $sData['balance'] = $sAccountAmount;

    //         $sResult->update($sData);

    //         $ssbCreateTran = CommanTransactionsController::createTransaction($satRefId, 1, $sAccountId, $request->input('memberAutoId'), $branch_id, $branchCode, $amountArray, 5, $amount_deposit_by_name, $request->input('memberAutoId'), $ssbAccountNumber, $cheque_dd_no = '0', $bank_name = NULL, $branch_name = NULL, $payment_date = date('Y-m-d'), $online_payment_id = NULL, $online_payment_by = NULL, $saving_account_id = 0, 'DR');

    //         $sAccountNumber = $ssbAccountNumber;
    //       } else {

    //         $sAccountNumber = NULL;

    //         $satRefId = NULL;

    //         $ssbCreateTran = $this->commonTransactionLogData($satRefId, $request->all(), $insertedid, $type);
    //       }



    //       // $createDayBook = CommanTransactionsController::createDayBook($ssbCreateTran,$satRefId,2,$insertedid,$request['associatemid'],$request['memberAutoId'],$request['amount'],$request['amount'],$withdrawal=0,$description,$ssbAccountNumber,$branch_id,$branchCode,$amountArray,$request['payment-mode'],$amount_deposit_by_name,$request['memberAutoId'],$investmentAccount,$request['cheque-number'],$request['bank-name'],$request['branch-name'],date('Y-m-d'),$request['transaction-id'],$online_payment_by=NULL,$ssbId,'CR');



    //       // ---------------------------  Day book modify --------------------------    

    //       $ppmode = 0;

    //       if ($request->input('payment-mode') == 1) { //cheque

    //         $ppmode = 1;
    //       }

    //       if ($request->input('payment-mode') == 2) { //onine

    //         $ppmode = 3;
    //       }

    //       if ($request->input('payment-mode') == 3) {

    //         //ssb

    //         $ppmode = 4;
    //       }

    //       $createDayBook = CommanTransactionsController::createDayBookNew($ssbCreateTran, $satRefId, 2, $insertedid, $request['associatemid'], $request['memberAutoId'], $request['amount'], $request['amount'], $withdrawal = 0, $description, $ssbAccountNumber, $branch_id, $branchCode, $amountArray, $ppmode, $amount_deposit_by_name, $request['memberAutoId'], $investmentAccount, $request['cheque-number'], $request['bank-name'], $request['branch-name'], $pdate, $online_transction_no, $online_payment_by = NULL, $ssbId, 'CR', $received_cheque_id, $cheque_deposit_bank_id, $cheque_deposit_bank_ac_id, $online_deposit_bank_id, $online_deposit_bank_ac_id);

    //       // ---------------------------  HEAD IMPLEMENT --------------------------

    //       $this->investHeadCreate($request['amount'], $globaldate, $insertedid, $planId, $cheque_no, $cheque_id, $cheque_date, $online_transction_no, $online_transction_date, $online_deposit_bank_id, $online_deposit_bank_ac_id, $branch_id, $request['associatemid'], $request['memberAutoId'], $ssbId, $createDayBook, $request->input('payment-mode'), $investmentDetail->account_number);

    //       //--------------------------------HEAD IMPLEMENT  -------------------------



    //       /* ------------------ commission genarate-----------------*/

    //       $commission = CommanTransactionsController::commissionDistributeInvestment($request['associatemid'], $insertedid, 3, $request['amount'], 1, $planId, $branch_id, $request['tenure'], $createDayBook);

    //       $commission_collection = CommanTransactionsController::commissionCollectionInvestment($request['associatemid'], $insertedid, 5, $request['amount'], 1, $planId, $branch_id, $request['tenure'], $createDayBook);

    //       /*----- ------  credit business start ---- ---------------*/

    //       $creditBusiness = CommanTransactionsController::associateCreditBusiness($request['associatemid'], $insertedid, 1, $request['amount'], 1, $planId, $request['tenure'], $createDayBook);



    //       /*----- ------  credit business end ---- ---------------*/

    //       /* ------------------ commission genarate-----------------*/



    //       $transaction = $this->transactionData($satRefId, $request->all(), $insertedid, $type, $ssbCreateTran);

    //       $res = Investmentplantransactions::create($transaction);







    //       //                $paymentData = $this->getPaymentMethodData($request->all(),$insertedid,$type);

    //       //                $res = Memberinvestmentspayments::create($paymentData); 



    //       /*--------------------- received cheque payment -----------------------*/

    //       if ($request->input('payment-mode') == 1) {





    //         $receivedPayment['type'] = 2;

    //         $receivedPayment['branch_id'] = $branch_id;

    //         $receivedPayment['investment_id'] = $insertedid;

    //         $receivedPayment['day_book_id'] = $createDayBook;

    //         $receivedPayment['cheque_id'] = $request['cheque_id'];

    //         $receivedPayment['created_at'] = $globaldate;

    //         $receivedCreate = \App\Models\ReceivedChequePayment::create($receivedPayment);



    //         $dataRC['status'] = 3;

    //         $receivedcheque = \App\Models\ReceivedCheque::find($request['cheque_id']);

    //         $receivedcheque->update($dataRC);
    //       }



    //       if ($request['stationary_charge'] > 0) {

    //         $this->stationaryCharges($insertedid);
    //       }

    //       /*--------------------- received cheque payment -----------------------*/



    //       $text = 'Dear Member, Thank You for Choosing S.B. Micro Finance association Your SSB Child A/c No.' . $investmentDetail->account_number . ' is Credited on '

    //         . $investmentDetail->created_at->format('d M Y') . ' With Rs. ' . round($investmentDetail->deposite_amount, 2) . 'CR. Thanks Have a good day';

    //       $temaplteId = 1207161519023692218;

    //       break;
    //     case "samraddh-kanyadhan-yojana":
    //       $description = 'SK Account opening';
    //       $res = Memberinvestments::create($data);
    //       $insertedid = $res->id;
    //       $investmentDetail = Memberinvestments::find($res->id);
    //       $amountArray = array('1' => $request->input('amount'));
    //       $amount_deposit_by_name = $request->input('firstname') . ' ' . $request->input('lastname');
    //       if ($request->input('payment-mode') == 3) {
    //         //$savingTransaction = $this->savingAccountTransactionData($request->all(),$insertedid,$type);
    //         $savingTransaction = $this->savingAccountTransactionDataNew($request->all(), $insertedid, $type, 'SK', $investmentAccount);
    //         $res = SavingAccountTranscation::create($savingTransaction);
    //         $satRefId = CommanTransactionsController::createTransactionReferences($res->id, $insertedid);
    //         $sAccountId = $ssbId;
    //         $sAccountAmount = $ssbBalance - $request->input('amount');
    //         $sResult = SavingAccount::find($sAccountId);
    //         $sData['balance'] = $sAccountAmount;
    //         $sResult->update($sData);
    //         $ssbCreateTran = CommanTransactionsController::createTransaction($satRefId, 2, $sAccountId, $request->input('memberAutoId'), $branch_id, $branchCode, $amountArray, 5, $amount_deposit_by_name, $request->input('memberAutoId'), $ssbAccountNumber, $cheque_dd_no = '0', $bank_name = NULL, $branch_name = NULL, $payment_date = date('Y-m-d'), $online_payment_id = NULL, $online_payment_by = NULL, $saving_account_id = 0, 'DR');
    //         $sAccountNumber = $ssbAccountNumber;
    //       } else {
    //         $sAccountNumber = NULL;
    //         $satRefId = NULL;
    //         $ssbCreateTran = $this->commonTransactionLogData($satRefId, $request->all(), $insertedid, $type);
    //       }
    //       // $createDayBook = CommanTransactionsController::createDayBook($ssbCreateTran,$satRefId,2,$insertedid,$request['associatemid'],$request['memberAutoId'],$request['amount'],$request['amount'],$withdrawal=0,$description,$ssbAccountNumber,$branch_id,$branchCode,$amountArray,$request['payment-mode'],$amount_deposit_by_name,$request['memberAutoId'],$investmentAccount,$request['cheque-number'],$request['bank-name'],$request['branch-name'],date('Y-m-d'),$request['transaction-id'],$online_payment_by=NULL,$ssbId,'CR');
    //       // ---------------------------  Day book modify --------------------------    
    //       $ppmode = 0;
    //       if ($request->input('payment-mode') == 1) { //cheque
    //         $ppmode = 1;
    //       }
    //       if ($request->input('payment-mode') == 2) { //onine
    //         $ppmode = 3;
    //       }
    //       if ($request->input('payment-mode') == 3) {
    //         //ssb
    //         $ppmode = 4;
    //       }
    //       $createDayBook = CommanTransactionsController::createDayBookNew($ssbCreateTran, $satRefId, 2, $insertedid, $request['associatemid'], $request['memberAutoId'], $request['amount'], $request['amount'], $withdrawal = 0, $description, $ssbAccountNumber, $branch_id, $branchCode, $amountArray, $ppmode, $amount_deposit_by_name, $request['memberAutoId'], $investmentAccount, $request['cheque-number'], $request['bank-name'], $request['branch-name'], $pdate, $online_transction_no, $online_payment_by = NULL, $ssbId, 'CR', $received_cheque_id, $cheque_deposit_bank_id, $cheque_deposit_bank_ac_id, $online_deposit_bank_id, $online_deposit_bank_ac_id);
    //       // ---------------------------  HEAD IMPLEMENT --------------------------
    //       $this->investHeadCreate($request['amount'], $globaldate, $insertedid, $planId, $cheque_no, $cheque_id, $cheque_date, $online_transction_no, $online_transction_date, $online_deposit_bank_id, $online_deposit_bank_ac_id, $branch_id, $request['associatemid'], $request['memberAutoId'], $ssbId, $createDayBook, $request->input('payment-mode'), $investmentDetail->account_number);
    //       //--------------------------------------------------------------------------  
    //       /* ------------------ commission genarate-----------------*/
    //       $commission = CommanTransactionsController::commissionDistributeInvestment($request['associatemid'], $insertedid, 3, $request['amount'], 1, $planId, $branch_id, $request['tenure'], $createDayBook);
    //       $commission_collection = CommanTransactionsController::commissionCollectionInvestment($request['associatemid'], $insertedid, 5, $request['amount'], 1, $planId, $branch_id, $request['tenure'], $createDayBook);
    //       /*----- ------  credit business start ---- ---------------*/
    //       $creditBusiness = CommanTransactionsController::associateCreditBusiness($request['associatemid'], $insertedid, 1, $request['amount'], 1, $planId, $request['tenure'], $createDayBook);
    //       /*----- ------  credit business end ---- ---------------*/
    //       /* ------------------ commission genarate-----------------*/
    //       $transaction = $this->transactionData($satRefId, $request->all(), $insertedid, $type, $ssbCreateTran);
    //       $res = Investmentplantransactions::create($transaction);
    //       $paymentData = $this->getPaymentMethodData($request->all(), $insertedid, $type);
    //       $res = Memberinvestmentspayments::create($paymentData);
    //       /*--------------------- received cheque payment -----------------------*/
    //       if ($request->input('payment-mode') == 1) {
    //         $receivedPayment['type'] = 2;
    //         $receivedPayment['branch_id'] = $branch_id;
    //         $receivedPayment['investment_id'] = $insertedid;
    //         $receivedPayment['day_book_id'] = $createDayBook;
    //         $receivedPayment['cheque_id'] = $request['cheque_id'];
    //         $receivedPayment['created_at'] = $globaldate;
    //         $receivedCreate = \App\Models\ReceivedChequePayment::create($receivedPayment);
    //         $dataRC['status'] = 3;
    //         $receivedcheque = \App\Models\ReceivedCheque::find($request['cheque_id']);
    //         $receivedcheque->update($dataRC);
    //       }
    //       if ($request['stationary_charge'] > 0) {
    //         $this->stationaryCharges($insertedid);
    //       }
    //       /*--------------------- received cheque payment -----------------------*/

    //       $text = 'Dear Member, Thank You for Choosing S.B. Micro Finance association Your SK A/c No.' . $investmentDetail->account_number . ' is Credited on ' . $investmentDetail->created_at->format('d M Y') . ' With Rs. ' . round($investmentDetail->deposite_amount, 2) . '. Thanks Have a good day';
    //       $temaplteId = 1207161519023692218;
    //       break;
    //     case "special-samraddh-money-back":
    //       $description = 'SMB Account opening';
    //       $res = Memberinvestments::create($data);
    //       $monyBackAccount = $res->id;
    //       $investmentDetail = Memberinvestments::find($res->id);
    //       $insertedid = $res->id;
    //       $fNominee = $this->getFirstNomineeData($request->all(), $insertedid, $type);
    //       $res = Memberinvestmentsnominees::create($fNominee);
    //       if ($request['second_nominee_add'] == 1) {
    //         $sNominee = $this->getSecondNomineeData($request->all(), $insertedid, $type);
    //         $res = Memberinvestmentsnominees::create($sNominee);
    //       }
    //       $paymentData = $this->getPaymentMethodData($request->all(), $insertedid, $type);
    //       $res = Memberinvestmentspayments::create($paymentData);
    //       $amountArray = array('1' => $request->input('amount'));
    //       $amount_deposit_by_name = $request->input('firstname') . ' ' . $request->input('lastname');
    //       if ($request->input('payment-mode') == 3) {
    //         // $savingTransaction = $this->savingAccountTransactionData($request->all(),$insertedid,$type);
    //         $savingTransaction = $this->savingAccountTransactionDataNew($request->all(), $insertedid, $type, 'SMB', $investmentAccount);
    //         $res = SavingAccountTranscation::create($savingTransaction);
    //         $satRefId = CommanTransactionsController::createTransactionReferences($res->id, $insertedid);
    //         $sAccountId = $ssbId;
    //         $sAccountAmount = $ssbBalance - $request->input('amount');
    //         $sResult = SavingAccount::find($sAccountId);
    //         $sData['balance'] = $sAccountAmount;
    //         $sResult->update($sData);
    //         $ssbCreateTran = CommanTransactionsController::createTransaction($satRefId, 1, $sAccountId, $request->input('memberAutoId'), $branch_id, $branchCode, $amountArray, 5, $amount_deposit_by_name, $request->input('memberAutoId'), $ssbAccountNumber, $cheque_dd_no = '0', $bank_name = NULL, $branch_name = NULL, $payment_date = date('Y-m-d'), $online_payment_id = NULL, $online_payment_by = NULL, $saving_account_id = 0, 'DR');
    //         $sAccountNumber = $ssbAccountNumber;
    //       } else {
    //         $sAccountNumber = NULL;
    //         $satRefId = NULL;
    //         $ssbCreateTran = $this->commonTransactionLogData($satRefId, $request->all(), $insertedid, $type);
    //       }

    //       //$createDayBook = CommanTransactionsController::createDayBook($ssbCreateTran,$satRefId,2,$insertedid,$request['associatemid'],$request['memberAutoId'],$request['amount'],$request['amount'],$withdrawal=0,$description,$ssbAccountNumber,$branch_id,$branchCode,$amountArray,$request['payment-mode'],$amount_deposit_by_name,$request['memberAutoId'],$investmentAccount,$request['cheque-number'],$request['bank-name'],$request['branch-name'],date('Y-m-d'),$request['transaction-id'],$online_payment_by=NULL,$ssbId,'CR');
    //       // ---------------------------  Day book modify --------------------------    
    //       $ppmode = 0;
    //       if ($request->input('payment-mode') == 1) { //cheque
    //         $ppmode = 1;
    //       }
    //       if ($request->input('payment-mode') == 2) { //onine
    //         $ppmode = 3;
    //       }
    //       if ($request->input('payment-mode') == 3) {
    //         //ssb
    //         $ppmode = 4;
    //       }
    //       $createDayBook = CommanTransactionsController::createDayBookNew($ssbCreateTran, $satRefId, 2, $insertedid, $request['associatemid'], $request['memberAutoId'], $request['amount'], $request['amount'], $withdrawal = 0, $description, $ssbAccountNumber, $branch_id, $branchCode, $amountArray, $ppmode, $amount_deposit_by_name, $request['memberAutoId'], $investmentAccount, $request['cheque-number'], $request['bank-name'], $request['branch-name'], $pdate, $online_transction_no, $online_payment_by = NULL, $ssbId, 'CR', $received_cheque_id, $cheque_deposit_bank_id, $cheque_deposit_bank_ac_id, $online_deposit_bank_id, $online_deposit_bank_ac_id);
    //       // ---------------------------  HEAD IMPLEMENT --------------------------
    //       $this->investHeadCreate($request['amount'], $globaldate, $insertedid, $planId, $cheque_no, $cheque_id, $cheque_date, $online_transction_no, $online_transction_date, $online_deposit_bank_id, $online_deposit_bank_ac_id, $branch_id, $request['associatemid'], $request['memberAutoId'], $ssbId, $createDayBook, $request->input('payment-mode'), $investmentDetail->account_number);
    //       //--------------------------------HEAD IMPLEMENT  -------------------------

    //       /* ------------------ commission genarate-----------------*/
    //       $commission = CommanTransactionsController::commissionDistributeInvestment($request['associatemid'], $insertedid, 3, $request['amount'], 1, $planId, $branch_id, $request['tenure'], $createDayBook);
    //       $commission_collection = CommanTransactionsController::commissionCollectionInvestment($request['associatemid'], $insertedid, 5, $request['amount'], 1, $planId, $branch_id, $request['tenure'], $createDayBook);
    //       /*----- ------  credit business start ---- ---------------*/
    //       $creditBusiness = CommanTransactionsController::associateCreditBusiness($request['associatemid'], $insertedid, 1, $request['amount'], 1, $planId, $request['tenure'], $createDayBook);
    //       /*----- ------  credit business end ---- ---------------*/
    //       /* ------------------ commission genarate-----------------*/
    //       $transaction = $this->transactionData($satRefId, $request->all(), $insertedid, $type, $ssbCreateTran);
    //       $res = Investmentplantransactions::create($transaction);

    //       //                $paymentData = $this->getPaymentMethodData($request->all(),$insertedid,$type);
    //       //                $res = Memberinvestmentspayments::create($paymentData); 
    //       /*--------------------- received cheque payment -----------------------*/
    //       if ($request->input('payment-mode') == 1) {
    //         $receivedPayment['type'] = 2;
    //         $receivedPayment['branch_id'] = $branch_id;
    //         $receivedPayment['investment_id'] = $insertedid;
    //         $receivedPayment['day_book_id'] = $createDayBook;
    //         $receivedPayment['cheque_id'] = $request['cheque_id'];
    //         $receivedPayment['created_at'] = $globaldate;
    //         $receivedCreate = \App\Models\ReceivedChequePayment::create($receivedPayment);
    //         $dataRC['status'] = 3;
    //         $receivedcheque = \App\Models\ReceivedCheque::find($request['cheque_id']);
    //         $receivedcheque->update($dataRC);
    //       }
    //       /*--------------------- received cheque payment -----------------------*/
    //       if ($request['stationary_charge'] > 0) {
    //         $this->stationaryCharges($insertedid);
    //       }
    //       $memberInvestData = Memberinvestments::with('ssb')->find($monyBackAccount);
    //       $text = 'Dear Member, Thank You for Choosing S.B. Micro Finance association Your Saving A/C ' . $memberInvestData->ssb_account_number . ' is Created on ' . $memberInvestData->created_at->format('d M Y') . ' with Rs. 500.00 CR, Money Back A/c No. ' . $memberInvestData->account_number . ' is Created on ' . $memberInvestData->created_at->format('d M Y') . ' with Rs. ' . round($memberInvestData->deposite_amount, 2) . ' CR. Have a good day';
    //       $temaplteId = 1207161519138416891;
    //       break;
    //     case "flexi-fixed-deposit":
    //       $description = 'FFD Account opening';
    //       $res = Memberinvestments::create($data);
    //       $insertedid = $res->id;
    //       $investmentDetail = Memberinvestments::find($res->id);
    //       $fNominee = $this->getFirstNomineeData($request->all(), $insertedid, $type);
    //       $res = Memberinvestmentsnominees::create($fNominee);
    //       if ($request['second_nominee_add'] == 1) {
    //         $sNominee = $this->getSecondNomineeData($request->all(), $insertedid, $type);
    //         $res = Memberinvestmentsnominees::create($sNominee);
    //       }
    //       $paymentData = $this->getPaymentMethodData($request->all(), $insertedid, $type);
    //       $res = Memberinvestmentspayments::create($paymentData);
    //       $amountArray = array('1' => $request->input('amount'));
    //       $amount_deposit_by_name = $request->input('firstname') . ' ' . $request->input('lastname');
    //       if ($request->input('payment-mode') == 3) {
    //         //$savingTransaction = $this->savingAccountTransactionData($request->all(),$insertedid,$type);
    //         $savingTransaction = $this->savingAccountTransactionDataNew($request->all(), $insertedid, $type, 'FFD', $investmentAccount);
    //         $res = SavingAccountTranscation::create($savingTransaction);
    //         $satRefId = CommanTransactionsController::createTransactionReferences($res->id, $insertedid);
    //         $sAccountId = $ssbId;
    //         $sAccountAmount = $ssbBalance - $request->input('amount');
    //         $sResult = SavingAccount::find($sAccountId);
    //         $sData['balance'] = $sAccountAmount;
    //         $sResult->update($sData);
    //         $ssbCreateTran = CommanTransactionsController::createTransaction($satRefId, 1, $sAccountId, $request->input('memberAutoId'), $branch_id, $branchCode, $amountArray, 5, $amount_deposit_by_name, $request->input('memberAutoId'), $ssbAccountNumber, $cheque_dd_no = '0', $bank_name = NULL, $branch_name = NULL, $payment_date = date('Y-m-d'), $online_payment_id = NULL, $online_payment_by = NULL, $saving_account_id = 0, 'DR');
    //         $sAccountNumber = $ssbAccountNumber;
    //       } else {
    //         $sAccountNumber = NULL;
    //         $satRefId = NULL;
    //         $ssbCreateTran = $this->commonTransactionLogData($satRefId, $request->all(), $insertedid, $type);
    //       }

    //       //  $createDayBook = CommanTransactionsController::createDayBook($ssbCreateTran,$satRefId,2,$insertedid,$request['associatemid'],$request['memberAutoId'],$request['amount'],$request['amount'],$withdrawal=0,$description,$ssbAccountNumber,$branch_id,$branchCode,$amountArray,$request['payment-mode'],$amount_deposit_by_name,$request['memberAutoId'],$investmentAccount,$request['cheque-number'],$request['bank-name'],$request['branch-name'],date('Y-m-d'),$request['transaction-id'],$online_payment_by=NULL,$ssbId,'CR');
    //       // ---------------------------  Day book modify --------------------------    
    //       $ppmode = 0;
    //       if ($request->input('payment-mode') == 1) { //cheque
    //         $ppmode = 1;
    //       }
    //       if ($request->input('payment-mode') == 2) { //onine
    //         $ppmode = 3;
    //       }
    //       if ($request->input('payment-mode') == 3) {
    //         //ssb
    //         $ppmode = 4;
    //       }
    //       $createDayBook = CommanTransactionsController::createDayBookNew($ssbCreateTran, $satRefId, 2, $insertedid, $request['associatemid'], $request['memberAutoId'], $request['amount'], $request['amount'], $withdrawal = 0, $description, $ssbAccountNumber, $branch_id, $branchCode, $amountArray, $ppmode, $amount_deposit_by_name, $request['memberAutoId'], $investmentAccount, $request['cheque-number'], $request['bank-name'], $request['branch-name'], $pdate, $online_transction_no, $online_payment_by = NULL, $ssbId, 'CR', $received_cheque_id, $cheque_deposit_bank_id, $cheque_deposit_bank_ac_id, $online_deposit_bank_id, $online_deposit_bank_ac_id);
    //       // ---------------------------  HEAD IMPLEMENT --------------------------
    //       $this->investHeadCreate($request['amount'], $globaldate, $insertedid, $planId, $cheque_no, $cheque_id, $cheque_date, $online_transction_no, $online_transction_date, $online_deposit_bank_id, $online_deposit_bank_ac_id, $branch_id, $request['associatemid'], $request['memberAutoId'], $ssbId, $createDayBook, $request->input('payment-mode'), $investmentDetail->account_number);
    //       //--------------------------------HEAD IMPLEMENT  -------------------------
    //       /* ------------------ commission genarate-----------------*/
    //       $commission = CommanTransactionsController::commissionDistributeInvestment($request['associatemid'], $insertedid, 3, $request['amount'], 1, $planId, $branch_id, $request['tenure'], $createDayBook);
    //       $commission_collection = CommanTransactionsController::commissionCollectionInvestment($request['associatemid'], $insertedid, 5, $request['amount'], 1, $planId, $branch_id, $request['tenure'], $createDayBook);
    //       /*----- ------  credit business start ---- ---------------*/
    //       $creditBusiness = CommanTransactionsController::associateCreditBusiness($request['associatemid'], $insertedid, 1, $request['amount'], 1, $planId, $request['tenure'], $createDayBook);
    //       /*----- ------  credit business end ---- ---------------*/
    //       /* ------------------ commission genarate-----------------*/
    //       $transaction = $this->transactionData($satRefId, $request->all(), $insertedid, $type, $ssbCreateTran);
    //       $res = Investmentplantransactions::create($transaction);

    //       //                $paymentData = $this->getPaymentMethodData($request->all(),$insertedid,$type);
    //       //                $res = Memberinvestmentspayments::create($paymentData); 

    //       /*--------------------- received cheque payment -----------------------*/
    //       if ($request->input('payment-mode') == 1) {
    //         $receivedPayment['type'] = 2;
    //         $receivedPayment['branch_id'] = $branch_id;
    //         $receivedPayment['investment_id'] = $insertedid;
    //         $receivedPayment['day_book_id'] = $createDayBook;
    //         $receivedPayment['cheque_id'] = $request['cheque_id'];
    //         $receivedPayment['created_at'] = $globaldate;
    //         $receivedCreate = \App\Models\ReceivedChequePayment::create($receivedPayment);
    //         $dataRC['status'] = 3;
    //         $receivedcheque = \App\Models\ReceivedCheque::find($request['cheque_id']);
    //         $receivedcheque->update($dataRC);
    //       }
    //       /*--------------------- received cheque payment -----------------------*/
    //       if ($request['stationary_charge'] > 0) {
    //         $this->stationaryCharges($insertedid);
    //       }
    //       $text = 'Dear Member, Thank You for Choosing S.B. Micro Finance association Your FFD A/c No.' . $investmentDetail->account_number . ' is Credited on '
    //         . $investmentDetail->created_at->format('d M Y') . ' With Rs. ' . round($investmentDetail->deposite_amount, 2) . '. Thanks Have a good day';
    //       $temaplteId = 1207161519023692218;
    //       break;
    //     case "fixed-recurring-deposit":
    //       $description = 'FRD Account opening';
    //       $res = Memberinvestments::create($data);
    //       $insertedid = $res->id;
    //       $investmentDetail = Memberinvestments::find($res->id);
    //       $fNominee = $this->getFirstNomineeData($request->all(), $insertedid, $type);
    //       $res = Memberinvestmentsnominees::create($fNominee);
    //       if ($request['second_nominee_add'] == 1) {
    //         $sNominee = $this->getSecondNomineeData($request->all(), $insertedid, $type);
    //         $res = Memberinvestmentsnominees::create($sNominee);
    //       }
    //       $paymentData = $this->getPaymentMethodData($request->all(), $insertedid, $type);
    //       $res = Memberinvestmentspayments::create($paymentData);
    //       $amountArray = array('1' => $request->input('amount'));
    //       $amount_deposit_by_name = $request->input('firstname') . ' ' . $request->input('lastname');
    //       if ($request->input('payment-mode') == 3) {
    //         // $savingTransaction = $this->savingAccountTransactionData($request->all(),$insertedid,$type);
    //         $savingTransaction = $this->savingAccountTransactionDataNew($request->all(), $insertedid, $type, 'FRD', $investmentAccount);
    //         $res = SavingAccountTranscation::create($savingTransaction);
    //         $satRefId = CommanTransactionsController::createTransactionReferences($res->id, $insertedid);
    //         $sAccountId = $ssbId;
    //         $sAccountAmount = $ssbBalance - $request->input('amount');
    //         $sResult = SavingAccount::find($sAccountId);
    //         $sData['balance'] = $sAccountAmount;
    //         $sResult->update($sData);
    //         $ssbCreateTran = CommanTransactionsController::createTransaction($satRefId, 1, $sAccountId, $request->input('memberAutoId'), $branch_id, $branchCode, $amountArray, 5, $amount_deposit_by_name, $request->input('memberAutoId'), $ssbAccountNumber, $cheque_dd_no = '0', $bank_name = NULL, $branch_name = NULL, $payment_date = date('Y-m-d'), $online_payment_id = NULL, $online_payment_by = NULL, $saving_account_id = 0, 'DR');
    //         $sAccountNumber = $ssbAccountNumber;
    //       } else {
    //         $sAccountNumber = NULL;
    //         $satRefId = NULL;
    //         $ssbCreateTran = $this->commonTransactionLogData($satRefId, $request->all(), $insertedid, $type);
    //       }


    //       //$createDayBook = CommanTransactionsController::createDayBook($ssbCreateTran,$satRefId,2,$insertedid,$request['associatemid'],$request['memberAutoId'],$request['amount'],$request['amount'],$withdrawal=0,$description,$ssbAccountNumber,$branch_id,$branchCode,$amountArray,$request['payment-mode'],$amount_deposit_by_name,$request['memberAutoId'],$investmentAccount,$request['cheque-number'],$request['bank-name'],$request['branch-name'],date('Y-m-d'),$request['transaction-id'],$online_payment_by=NULL,$ssbId,'CR');
    //       // ---------------------------  Day book modify --------------------------    
    //       $ppmode = 0;
    //       if ($request->input('payment-mode') == 1) { //cheque
    //         $ppmode = 1;
    //       }
    //       if ($request->input('payment-mode') == 2) { //onine
    //         $ppmode = 3;
    //       }
    //       if ($request->input('payment-mode') == 3) {
    //         //ssb
    //         $ppmode = 4;
    //       }
    //       $createDayBook = CommanTransactionsController::createDayBookNew($ssbCreateTran, $satRefId, 2, $insertedid, $request['associatemid'], $request['memberAutoId'], $request['amount'], $request['amount'], $withdrawal = 0, $description, $ssbAccountNumber, $branch_id, $branchCode, $amountArray, $ppmode, $amount_deposit_by_name, $request['memberAutoId'], $investmentAccount, $request['cheque-number'], $request['bank-name'], $request['branch-name'], $pdate, $online_transction_no, $online_payment_by = NULL, $ssbId, 'CR', $received_cheque_id, $cheque_deposit_bank_id, $cheque_deposit_bank_ac_id, $online_deposit_bank_id, $online_deposit_bank_ac_id);
    //       // ---------------------------  HEAD IMPLEMENT --------------------------
    //       $this->investHeadCreate($request['amount'], $globaldate, $insertedid, $planId, $cheque_no, $cheque_id, $cheque_date, $online_transction_no, $online_transction_date, $online_deposit_bank_id, $online_deposit_bank_ac_id, $branch_id, $request['associatemid'], $request['memberAutoId'], $ssbId, $createDayBook, $request->input('payment-mode'), $investmentDetail->account_number);
    //       //--------------------------------HEAD IMPLEMENT  -----------------------

    //       /* ------------------ commission genarate-----------------*/
    //       $commission = CommanTransactionsController::commissionDistributeInvestment($request['associatemid'], $insertedid, 3, $request['amount'], 1, $planId, $branch_id, $request['tenure'], $createDayBook);
    //       $commission_collection = CommanTransactionsController::commissionCollectionInvestment($request['associatemid'], $insertedid, 5, $request['amount'], 1, $planId, $branch_id, $request['tenure'], $createDayBook);
    //       /*----- ------  credit business start ---- ---------------*/
    //       $creditBusiness = CommanTransactionsController::associateCreditBusiness($request['associatemid'], $insertedid, 1, $request['amount'], 1, $planId, $request['tenure'], $createDayBook);
    //       /*----- ------  credit business end ---- ---------------*/
    //       /* ------------------ commission genarate-----------------*/
    //       $transaction = $this->transactionData($satRefId, $request->all(), $insertedid, $type, $ssbCreateTran);
    //       $res = Investmentplantransactions::create($transaction);

    //       //                $paymentData = $this->getPaymentMethodData($request->all(),$insertedid,$type);
    //       //                $res = Memberinvestmentspayments::create($paymentData); 

    //       /*--------------------- received cheque payment -----------------------*/
    //       if ($request->input('payment-mode') == 1) {
    //         $receivedPayment['type'] = 2;
    //         $receivedPayment['branch_id'] = $branch_id;
    //         $receivedPayment['investment_id'] = $insertedid;
    //         $receivedPayment['day_book_id'] = $createDayBook;
    //         $receivedPayment['cheque_id'] = $request['cheque_id'];
    //         $receivedPayment['created_at'] = $globaldate;
    //         $receivedCreate = \App\Models\ReceivedChequePayment::create($receivedPayment);
    //         $dataRC['status'] = 3;
    //         $receivedcheque = \App\Models\ReceivedCheque::find($request['cheque_id']);
    //         $receivedcheque->update($dataRC);
    //       }
    //       if ($request['stationary_charge'] > 0) {
    //         $this->stationaryCharges($insertedid);
    //       }
    //       /*--------------------- received cheque payment -----------------------*/

    //       $text = 'Dear Member, Thank You for Choosing S.B. Micro Finance association Your FRD A/c No.' . $investmentDetail->account_number . ' is Credited on '
    //         . $investmentDetail->created_at->format('d M Y') . ' With Rs. ' . round($investmentDetail->deposite_amount, 2) . 'CR. Thanks Have a good day';
    //       $temaplteId = 1207161519023692218;
    //       break;
    //     case "samraddh-jeevan":
    //       $description = 'SJ Account opening';
    //       $res = Memberinvestments::create($data);
    //       $insertedid = $res->id;
    //       $monyBackAccount = $res->id;
    //       $investmentDetail = Memberinvestments::find($res->id);
    //       $fNominee = $this->getFirstNomineeData($request->all(), $insertedid, $type);
    //       $res = Memberinvestmentsnominees::create($fNominee);
    //       if ($request['second_nominee_add'] == 1) {
    //         $sNominee = $this->getSecondNomineeData($request->all(), $insertedid, $type);
    //         $res = Memberinvestmentsnominees::create($sNominee);
    //       }
    //       $paymentData = $this->getPaymentMethodData($request->all(), $insertedid, $type);
    //       $res = Memberinvestmentspayments::create($paymentData);
    //       $amountArray = array('1' => $request->input('amount'));
    //       $amount_deposit_by_name = $request->input('firstname') . ' ' . $request->input('lastname');
    //       if ($request->input('payment-mode') == 3) {
    //         //  $savingTransaction = $this->savingAccountTransactionData($request->all(),$insertedid,$type);
    //         $savingTransaction = $this->savingAccountTransactionDataNew($request->all(), $insertedid, $type, 'SJ', $investmentAccount);
    //         $res = SavingAccountTranscation::create($savingTransaction);
    //         $satRefId = CommanTransactionsController::createTransactionReferences($res->id, $insertedid);
    //         $sAccountId = $ssbId;
    //         $sAccountAmount = $ssbBalance - $request->input('amount');
    //         $sResult = SavingAccount::find($sAccountId);
    //         $sData['balance'] = $sAccountAmount;
    //         $sResult->update($sData);
    //         $ssbCreateTran = CommanTransactionsController::createTransaction($satRefId, 1, $sAccountId, $request->input('memberAutoId'), $branch_id, $branchCode, $amountArray, 5, $amount_deposit_by_name, $request->input('memberAutoId'), $ssbAccountNumber, $cheque_dd_no = '0', $bank_name = NULL, $branch_name = NULL, $payment_date = date('Y-m-d'), $online_payment_id = NULL, $online_payment_by = NULL, $saving_account_id = 0, 'DR');
    //         $sAccountNumber = $ssbAccountNumber;
    //       } else {
    //         $sAccountNumber = NULL;
    //         $satRefId = NULL;
    //         $ssbCreateTran = $this->commonTransactionLogData($satRefId, $request->all(), $insertedid, $type);
    //       }

    //       // $createDayBook = CommanTransactionsController::createDayBook($ssbCreateTran,$satRefId,2,$insertedid,$request['associatemid'],$request['memberAutoId'],$request['amount'],$request['amount'],$withdrawal=0,$description,$ssbAccountNumber,$branch_id,$branchCode,$amountArray,$request['payment-mode'],$amount_deposit_by_name,$request['memberAutoId'],$investmentAccount,$request['cheque-number'],$request['bank-name'],$request['branch-name'],date('Y-m-d'),$request['transaction-id'],$online_payment_by=NULL,$ssbId,'CR');
    //       // ---------------------------  Day book modify --------------------------    
    //       $ppmode = 0;
    //       if ($request->input('payment-mode') == 1) { //cheque
    //         $ppmode = 1;
    //       }
    //       if ($request->input('payment-mode') == 2) { //onine
    //         $ppmode = 3;
    //       }
    //       if ($request->input('payment-mode') == 3) {
    //         //ssb
    //         $ppmode = 4;
    //       }
    //       $createDayBook = CommanTransactionsController::createDayBookNew($ssbCreateTran, $satRefId, 2, $insertedid, $request['associatemid'], $request['memberAutoId'], $request['amount'], $request['amount'], $withdrawal = 0, $description, $ssbAccountNumber, $branch_id, $branchCode, $amountArray, $ppmode, $amount_deposit_by_name, $request['memberAutoId'], $investmentAccount, $request['cheque-number'], $request['bank-name'], $request['branch-name'], $pdate, $online_transction_no, $online_payment_by = NULL, $ssbId, 'CR', $received_cheque_id, $cheque_deposit_bank_id, $cheque_deposit_bank_ac_id, $online_deposit_bank_id, $online_deposit_bank_ac_id);
    //       // ---------------------------  HEAD IMPLEMENT --------------------------
    //       $this->investHeadCreate($request['amount'], $globaldate, $insertedid, $planId, $cheque_no, $cheque_id, $cheque_date, $online_transction_no, $online_transction_date, $online_deposit_bank_id, $online_deposit_bank_ac_id, $branch_id, $request['associatemid'], $request['memberAutoId'], $ssbId, $createDayBook, $request->input('payment-mode'), $investmentDetail->account_number);
    //       //--------------------------------HEAD IMPLEMENT  -------------------------

    //       /* ------------------ commission genarate-----------------*/
    //       $commission = CommanTransactionsController::commissionDistributeInvestment($request['associatemid'], $insertedid, 3, $request['amount'], 1, $planId, $branch_id, $request['tenure'], $createDayBook);
    //       $commission_collection = CommanTransactionsController::commissionCollectionInvestment($request['associatemid'], $insertedid, 5, $request['amount'], 1, $planId, $branch_id, $request['tenure'], $createDayBook);
    //       /*----- ------  credit business start ---- ---------------*/
    //       $creditBusiness = CommanTransactionsController::associateCreditBusiness($request['associatemid'], $insertedid, 1, $request['amount'], 1, $planId, $request['tenure'], $createDayBook);
    //       /*----- ------  credit business end ---- ---------------*/
    //       /* ------------------ commission genarate-----------------*/
    //       $transaction = $this->transactionData($satRefId, $request->all(), $insertedid, $type, $ssbCreateTran);
    //       $res = Investmentplantransactions::create($transaction);


    //       //                $paymentData = $this->getPaymentMethodData($request->all(),$insertedid,$type);
    //       //                $res = Memberinvestmentspayments::create($paymentData); 

    //       /*--------------------- received cheque payment -----------------------*/
    //       if ($request->input('payment-mode') == 1) {
    //         $receivedPayment['type'] = 2;
    //         $receivedPayment['branch_id'] = $branch_id;
    //         $receivedPayment['investment_id'] = $insertedid;
    //         $receivedPayment['day_book_id'] = $createDayBook;
    //         $receivedPayment['cheque_id'] = $request['cheque_id'];
    //         $receivedPayment['created_at'] = $globaldate;
    //         $receivedCreate = \App\Models\ReceivedChequePayment::create($receivedPayment);
    //         $dataRC['status'] = 3;
    //         $receivedcheque = \App\Models\ReceivedCheque::find($request['cheque_id']);
    //         $receivedcheque->update($dataRC);
    //       }
    //       /*--------------------- received cheque payment -----------------------*/
    //       if ($request['stationary_charge'] > 0) {
    //         $this->stationaryCharges($insertedid);
    //       }
    //       $memberInvestData = Memberinvestments::with('ssb')->find($monyBackAccount);
    //       $text = 'Dear Member, Thank You for Choosing S.B. Micro Finance association Your S. Jeevan A/c No.' . $memberInvestData->account_number . ' is Credited on ' . $memberInvestData->created_at->format('d M Y') . ' With Rs. ' . round($memberInvestData->deposite_amount, 2) . '. Thanks Have a good day';
    //       /*$text = 'Dear Member, Thank You for Choosing S.B. Micro Finance association Your Saving A/C '. $memberInvestData->ssb_account_number.' is Created on '.$memberInvestData->created_at->format('d M Y') . ' with Rs. 100.00 CR, S. Jeevan A/c No. '.$memberInvestData->account_number.' is Created on '.$memberInvestData->created_at->format('d M Y').' with Rs. '.round($memberInvestData->deposite_amount,2).' CR. Have a good day';*/
    //       $temaplteId = 1207161519023692218;
    //       break;
    //     case "daily-deposit":
    //       $description = 'SDD Account opening';
    //       $res = Memberinvestments::create($data);
    //       $insertedid = $res->id;
    //       $investmentDetail = Memberinvestments::find($res->id);
    //       $fNominee = $this->getFirstNomineeData($request->all(), $insertedid, $type);
    //       $res = Memberinvestmentsnominees::create($fNominee);
    //       if ($request['second_nominee_add'] == 1) {
    //         $sNominee = $this->getSecondNomineeData($request->all(), $insertedid, $type);
    //         $res = Memberinvestmentsnominees::create($sNominee);
    //       }
    //       $paymentData = $this->getPaymentMethodData($request->all(), $insertedid, $type);
    //       $res = Memberinvestmentspayments::create($paymentData);
    //       $amountArray = array('1' => $request->input('amount'));
    //       $amount_deposit_by_name = $request->input('firstname') . ' ' . $request->input('lastname');

    //       if ($request->input('payment-mode') == 3) {
    //         //  $savingTransaction = $this->savingAccountTransactionData($request->all(),$insertedid,$type);
    //         $savingTransaction = $this->savingAccountTransactionDataNew($request->all(), $insertedid, $type, 'SDD', $investmentAccount);
    //         $res = SavingAccountTranscation::create($savingTransaction);
    //         $satRefId = CommanTransactionsController::createTransactionReferences($res->id, $insertedid);
    //         $sAccountId = $ssbId;
    //         $sAccountAmount = $ssbBalance - $request->input('amount');
    //         $sResult = SavingAccount::find($sAccountId);
    //         $sData['balance'] = $sAccountAmount;
    //         $sResult->update($sData);
    //         $ssbCreateTran = CommanTransactionsController::createTransaction($satRefId, 1, $sAccountId, $request->input('memberAutoId'), $branch_id, $branchCode, $amountArray, 5, $amount_deposit_by_name, $request->input('memberAutoId'), $ssbAccountNumber, $cheque_dd_no = '0', $bank_name = NULL, $branch_name = NULL, $payment_date = date('Y-m-d'), $online_payment_id = NULL, $online_payment_by = NULL, $saving_account_id = 0, 'DR');
    //         $sAccountNumber = $ssbAccountNumber;
    //       } else {
    //         $sAccountNumber = NULL;
    //         $satRefId = NULL;
    //         $ssbCreateTran = $this->commonTransactionLogData($satRefId, $request->all(), $insertedid, $type);
    //       }

    //       //                $createDayBook = CommanTransactionsController::createDayBook($ssbCreateTran,$satRefId,2,$insertedid,$request['associatemid'],$request['memberAutoId'],$request['amount'],$request['amount'],$withdrawal=0,$description,$ssbAccountNumber,$branch_id,$branchCode,$amountArray,$request['payment-mode'],$amount_deposit_by_name,$request['memberAutoId'],$investmentAccount,$request['cheque-number'],$request['bank-name'],$request['branch-name'],date('Y-m-d'),$request['transaction-id'],$online_payment_by=NULL,$ssbId,'CR');
    //       // ---------------------------  Day book modify --------------------------    
    //       $ppmode = 0;
    //       if ($request->input('payment-mode') == 1) { //cheque
    //         $ppmode = 1;
    //       }
    //       if ($request->input('payment-mode') == 2) { //onine
    //         $ppmode = 3;
    //       }
    //       if ($request->input('payment-mode') == 3) {
    //         //ssb
    //         $ppmode = 4;
    //       }
    //       $createDayBook = CommanTransactionsController::createDayBookNew($ssbCreateTran, $satRefId, 2, $insertedid, $request['associatemid'], $request['memberAutoId'], $request['amount'], $request['amount'], $withdrawal = 0, $description, $ssbAccountNumber, $branch_id, $branchCode, $amountArray, $ppmode, $amount_deposit_by_name, $request['memberAutoId'], $investmentAccount, $request['cheque-number'], $request['bank-name'], $request['branch-name'], $pdate, $online_transction_no, $online_payment_by = NULL, $ssbId, 'CR', $received_cheque_id, $cheque_deposit_bank_id, $cheque_deposit_bank_ac_id, $online_deposit_bank_id, $online_deposit_bank_ac_id);
    //       // ---------------------------  HEAD IMPLEMENT --------------------------
    //       $this->investHeadCreate($request['amount'], $globaldate, $insertedid, $planId, $cheque_no, $cheque_id, $cheque_date, $online_transction_no, $online_transction_date, $online_deposit_bank_id, $online_deposit_bank_ac_id, $branch_id, $request['associatemid'], $request['memberAutoId'], $ssbId, $createDayBook, $request->input('payment-mode'), $investmentDetail->account_number);
    //       //--------------------------------HEAD IMPLEMENT  -------------------------

    //       /* ------------------ commission genarate-----------------*/
    //       $commission = CommanTransactionsController::commissionDistributeInvestment($request['associatemid'], $insertedid, 3, $request['amount'], 1, $planId, $branch_id, $request['tenure'], $createDayBook);
    //       // print_r('1');die;
    //       $commission_collection = CommanTransactionsController::commissionCollectionInvestment($request['associatemid'], $insertedid, 5, $request['amount'], 1, $planId, $branch_id, $request['tenure'], $createDayBook);
    //       /*----- ------  credit business start ---- ---------------*/
    //       $creditBusiness = CommanTransactionsController::associateCreditBusiness($request['associatemid'], $insertedid, 1, $request['amount'], 1, $planId, $request['tenure'], $createDayBook);
    //       /*----- ------  credit business end ---- ---------------*/
    //       /* ------------------ commission genarate-----------------*/
    //       $transaction = $this->transactionData($satRefId, $request->all(), $insertedid, $type, $ssbCreateTran);
    //       $res = Investmentplantransactions::create($transaction);


    //       //                $paymentData = $this->getPaymentMethodData($request->all(),$insertedid,$type);
    //       //                $res = Memberinvestmentspayments::create($paymentData); 

    //       /*--------------------- received cheque payment -----------------------*/
    //       if ($request->input('payment-mode') == 1) {
    //         $receivedPayment['type'] = 2;
    //         $receivedPayment['branch_id'] = $branch_id;
    //         $receivedPayment['investment_id'] = $insertedid;
    //         $receivedPayment['day_book_id'] = $createDayBook;
    //         $receivedPayment['cheque_id'] = $request['cheque_id'];
    //         $receivedPayment['created_at'] = $globaldate;
    //         $receivedCreate = \App\Models\ReceivedChequePayment::create($receivedPayment);
    //         $dataRC['status'] = 3;
    //         $receivedcheque = \App\Models\ReceivedCheque::find($request['cheque_id']);
    //         $receivedcheque->update($dataRC);
    //       }
    //       if ($request['stationary_charge'] > 0) {
    //         $this->stationaryCharges($insertedid);
    //       }
    //       /*--------------------- received cheque payment -----------------------*/

    //       $text = 'Dear Member, Thank You for Choosing S.B. Micro Finance association Your SDD A/c No.' . $investmentDetail->account_number . ' is Credited on '
    //         . $investmentDetail->created_at->format('d M Y') . ' With Rs. ' . round($investmentDetail->deposite_amount, 2) . 'CR. Thanks Have a good day';
    //       $temaplteId = 1207161519023692218;
    //       break;
    //     case "monthly-income-scheme":
    //       $description = 'MIS Account opening';
    //       $res = Memberinvestments::create($data);
    //       $insertedid = $res->id;
    //       $monyBackAccount = $res->id;
    //       $investmentDetail = Memberinvestments::find($res->id);
    //       $fNominee = $this->getFirstNomineeData($request->all(), $insertedid, $type);
    //       $res = Memberinvestmentsnominees::create($fNominee);
    //       if ($request['second_nominee_add'] == 1) {
    //         $sNominee = $this->getSecondNomineeData($request->all(), $insertedid, $type);
    //         $res = Memberinvestmentsnominees::create($sNominee);
    //       }
    //       $paymentData = $this->getPaymentMethodData($request->all(), $insertedid, $type);
    //       $res = Memberinvestmentspayments::create($paymentData);
    //       $amountArray = array('1' => $request->input('amount'));
    //       $amount_deposit_by_name = $request->input('firstname') . ' ' . $request->input('lastname');
    //       if ($request->input('payment-mode') == 3) {
    //         //  $savingTransaction = $this->savingAccountTransactionData($request->all(),$insertedid,$type);
    //         $savingTransaction = $this->savingAccountTransactionDataNew($request->all(), $insertedid, $type, 'MIS', $investmentAccount);
    //         $res = SavingAccountTranscation::create($savingTransaction);
    //         $satRefId = CommanTransactionsController::createTransactionReferences($res->id, $insertedid);
    //         $sAccountId = $ssbId;
    //         $sAccountAmount = $ssbBalance - $request->input('amount');
    //         $sResult = SavingAccount::find($sAccountId);
    //         $sData['balance'] = $sAccountAmount;
    //         $sResult->update($sData);
    //         $ssbCreateTran = CommanTransactionsController::createTransaction($satRefId, 1, $sAccountId, $request->input('memberAutoId'), $branch_id, $branchCode, $amountArray, 5, $amount_deposit_by_name, $request->input('memberAutoId'), $ssbAccountNumber, $cheque_dd_no = '0', $bank_name = NULL, $branch_name = NULL, $payment_date = date('Y-m-d'), $online_payment_id = NULL, $online_payment_by = NULL, $saving_account_id = 0, 'DR');
    //         $sAccountNumber = $ssbAccountNumber;
    //       } else {
    //         $sAccountNumber = NULL;
    //         $satRefId = NULL;
    //         $ssbCreateTran = $this->commonTransactionLogData($satRefId, $request->all(), $insertedid, $type);
    //       }

    //       //      $createDayBook = CommanTransactionsController::createDayBook($ssbCreateTran,$satRefId,2,$insertedid,$request['associatemid'],$request['memberAutoId'],$request['amount'],$request['amount'],$withdrawal=0,$description,$ssbAccountNumber,$branch_id,$branchCode,$amountArray,$request['payment-mode'],$amount_deposit_by_name,$request['memberAutoId'],$investmentAccount,$request['cheque-number'],$request['bank-name'],$request['branch-name'],date('Y-m-d'),$request['transaction-id'],$online_payment_by=NULL,$ssbId,'CR');
    //       // ---------------------------  Day book modify --------------------------    
    //       $ppmode = 0;
    //       if ($request->input('payment-mode') == 1) { //cheque
    //         $ppmode = 1;
    //       }
    //       if ($request->input('payment-mode') == 2) { //onine
    //         $ppmode = 3;
    //       }
    //       if ($request->input('payment-mode') == 3) {
    //         //ssb
    //         $ppmode = 4;
    //       }
    //       $createDayBook = CommanTransactionsController::createDayBookNew($ssbCreateTran, $satRefId, 2, $insertedid, $request['associatemid'], $request['memberAutoId'], $request['amount'], $request['amount'], $withdrawal = 0, $description, $ssbAccountNumber, $branch_id, $branchCode, $amountArray, $ppmode, $amount_deposit_by_name, $request['memberAutoId'], $investmentAccount, $request['cheque-number'], $request['bank-name'], $request['branch-name'], $pdate, $online_transction_no, $online_payment_by = NULL, $ssbId, 'CR', $received_cheque_id, $cheque_deposit_bank_id, $cheque_deposit_bank_ac_id, $online_deposit_bank_id, $online_deposit_bank_ac_id);
    //       // ---------------------------  HEAD IMPLEMENT --------------------------
    //       $this->investHeadCreate($request['amount'], $globaldate, $insertedid, $planId, $cheque_no, $cheque_id, $cheque_date, $online_transction_no, $online_transction_date, $online_deposit_bank_id, $online_deposit_bank_ac_id, $branch_id, $request['associatemid'], $request['memberAutoId'], $ssbId, $createDayBook, $request->input('payment-mode'), $investmentDetail->account_number);
    //       //--------------------------------HEAD IMPLEMENT  -------------------------

    //       /* ------------------ commission genarate-----------------*/
    //       $commission = CommanTransactionsController::commissionDistributeInvestment($request['associatemid'], $insertedid, 3, $request['amount'], 1, $planId, $branch_id, $request['tenure'], $createDayBook);
    //       $commission_collection = CommanTransactionsController::commissionCollectionInvestment($request['associatemid'], $insertedid, 5, $request['amount'], 1, $planId, $branch_id, $request['tenure'], $createDayBook);
    //       /*----- ------  credit business start ---- ---------------*/
    //       $creditBusiness = CommanTransactionsController::associateCreditBusiness($request['associatemid'], $insertedid, 1, $request['amount'], 1, $planId, $request['tenure'], $createDayBook);
    //       /*----- ------  credit business end ---- ---------------*/
    //       /* ------------------ commission genarate-----------------*/
    //       $transaction = $this->transactionData($satRefId, $request->all(), $insertedid, $type, $ssbCreateTran);
    //       $res = Investmentplantransactions::create($transaction);


    //       //                $paymentData = $this->getPaymentMethodData($request->all(),$insertedid,$type);
    //       //                $res = Memberinvestmentspayments::create($paymentData); 

    //       /*--------------------- received cheque payment -----------------------*/
    //       if ($request->input('payment-mode') == 1) {
    //         $receivedPayment['type'] = 2;
    //         $receivedPayment['branch_id'] = $branch_id;
    //         $receivedPayment['investment_id'] = $insertedid;
    //         $receivedPayment['day_book_id'] = $createDayBook;
    //         $receivedPayment['cheque_id'] = $request['cheque_id'];
    //         $receivedPayment['created_at'] = $globaldate;
    //         $receivedCreate = \App\Models\ReceivedChequePayment::create($receivedPayment);
    //         $dataRC['status'] = 3;
    //         $receivedcheque = \App\Models\ReceivedCheque::find($request['cheque_id']);
    //         $receivedcheque->update($dataRC);
    //       }
    //       if ($request['stationary_charge'] > 0) {
    //         $this->stationaryCharges($insertedid);
    //       }
    //       /*--------------------- received cheque payment -----------------------*/

    //       $memberInvestData = Memberinvestments::with('ssb')->find($monyBackAccount);
    //       $text = 'Dear Member, Thank You for Choosing S.B. Micro Finance association Your MIS A/c No.' . $memberInvestData->account_number . ' is Credited on ' . $memberInvestData->created_at->format('d M Y') . ' With Rs. ' . round($memberInvestData->deposite_amount, 2) . '. Thanks Have a good day';
    //       /*$text = 'Dear Member, Thank You for Choosing S.B. Micro Finance association Your Saving A/C '. $memberInvestData->ssb_account_number.' is Created on '.$memberInvestData->created_at->format('d M Y') . ' with Rs. 100.00 CR, MIS A/c No. '.$memberInvestData->account_number.' is Created on '.$memberInvestData->created_at->format('d M Y').' with Rs. '.round($memberInvestData->deposite_amount,2).' CR. Have a good day';*/
    //       $temaplteId = 1207161519023692218;
    //       break;
    //     case "fixed-deposit":
    //       $description = 'SFD Account opening';
    //       $res = Memberinvestments::create($data);
    //       $insertedid = $res->id;
    //       $investmentDetail = Memberinvestments::find($res->id);
    //       $fNominee = $this->getFirstNomineeData($request->all(), $insertedid, $type);
    //       $res = Memberinvestmentsnominees::create($fNominee);
    //       if ($request['second_nominee_add'] == 1) {
    //         $sNominee = $this->getSecondNomineeData($request->all(), $insertedid, $type);
    //         $res = Memberinvestmentsnominees::create($sNominee);
    //       }
    //       $paymentData = $this->getPaymentMethodData($request->all(), $insertedid, $type);
    //       $res = Memberinvestmentspayments::create($paymentData);
    //       $amountArray = array('1' => $request->input('amount'));
    //       $amount_deposit_by_name = $request->input('firstname') . ' ' . $request->input('lastname');
    //       if ($request->input('payment-mode') == 3) {
    //         //  $savingTransaction = $this->savingAccountTransactionData($request->all(),$insertedid,$type);
    //         $savingTransaction = $this->savingAccountTransactionDataNew($request->all(), $insertedid, $type, 'SFD', $investmentAccount);
    //         $res = SavingAccountTranscation::create($savingTransaction);
    //         $satRefId = CommanTransactionsController::createTransactionReferences($res->id, $insertedid);
    //         $sAccountId = $ssbId;
    //         $sAccountAmount = $ssbBalance - $request->input('amount');

    //         $sResult = SavingAccount::find($sAccountId);
    //         $sData['balance'] = $sAccountAmount;
    //         $sResult->update($sData);
    //         $ssbCreateTran = CommanTransactionsController::createTransaction($satRefId, 1, $sAccountId, $request->input('memberAutoId'), $branch_id, $branchCode, $amountArray, 5, $amount_deposit_by_name, $request->input('memberAutoId'), $ssbAccountNumber, $cheque_dd_no = '0', $bank_name = NULL, $branch_name = NULL, $payment_date = date('Y-m-d'), $online_payment_id = NULL, $online_payment_by = NULL, $saving_account_id = 0, 'DR');
    //         $sAccountNumber = $ssbAccountNumber;
    //       } else {
    //         $sAccountNumber = NULL;
    //         $satRefId = NULL;
    //         $ssbCreateTran = $this->commonTransactionLogData($satRefId, $request->all(), $insertedid, $type);
    //       }

    //       ///$createDayBook = CommanTransactionsController::createDayBook($ssbCreateTran,$satRefId,2,$insertedid,$request['associatemid'],$request['memberAutoId'],$request['amount'],$request['amount'],$withdrawal=0,$description,$ssbAccountNumber,$branch_id,$branchCode,$amountArray,$request['payment-mode'],$amount_deposit_by_name,$request['memberAutoId'],$investmentAccount,$request['cheque-number'],$request['bank-name'],$request['branch-name'],date('Y-m-d'),$request['transaction-id'],$online_payment_by=NULL,$ssbId,'CR');
    //       // ---------------------------  Day book modify --------------------------    
    //       $ppmode = 0;
    //       if ($request->input('payment-mode') == 1) { //cheque
    //         $ppmode = 1;
    //       }
    //       if ($request->input('payment-mode') == 2) { //onine
    //         $ppmode = 3;
    //       }
    //       if ($request->input('payment-mode') == 3) {
    //         //ssb
    //         $ppmode = 4;
    //       }
    //       $createDayBook = CommanTransactionsController::createDayBookNew($ssbCreateTran, $satRefId, 2, $insertedid, $request['associatemid'], $request['memberAutoId'], $request['amount'], $request['amount'], $withdrawal = 0, $description, $ssbAccountNumber, $branch_id, $branchCode, $amountArray, $ppmode, $amount_deposit_by_name, $request['memberAutoId'], $investmentAccount, $request['cheque-number'], $request['bank-name'], $request['branch-name'], $pdate, $online_transction_no, $online_payment_by = NULL, $ssbId, 'CR', $received_cheque_id, $cheque_deposit_bank_id, $cheque_deposit_bank_ac_id, $online_deposit_bank_id, $online_deposit_bank_ac_id);
    //       // ---------------------------  HEAD IMPLEMENT --------------------------
    //       $this->investHeadCreate($request['amount'], $globaldate, $insertedid, $planId, $cheque_no, $cheque_id, $cheque_date, $online_transction_no, $online_transction_date, $online_deposit_bank_id, $online_deposit_bank_ac_id, $branch_id, $request['associatemid'], $request['memberAutoId'], $ssbId, $createDayBook, $request->input('payment-mode'), $investmentDetail->account_number);
    //       //--------------------------------HEAD IMPLEMENT  -------------------------

    //       /* ------------------ commission genarate-----------------*/
    //       $commission = CommanTransactionsController::commissionDistributeInvestment($request['associatemid'], $insertedid, 3, $request['amount'], 1, $planId, $branch_id, $request['tenure'], $createDayBook);
    //       $commission_collection = CommanTransactionsController::commissionCollectionInvestment($request['associatemid'], $insertedid, 5, $request['amount'], 1, $planId, $branch_id, $request['tenure'], $createDayBook);
    //       /*----- ------  credit business start ---- ---------------*/
    //       $creditBusiness = CommanTransactionsController::associateCreditBusiness($request['associatemid'], $insertedid, 1, $request['amount'], 1, $planId, $request['tenure'], $createDayBook);
    //       /*----- ------  credit business end ---- ---------------*/
    //       /* ------------------ commission genarate-----------------*/
    //       $transaction = $this->transactionData($satRefId, $request->all(), $insertedid, $type, $ssbCreateTran);
    //       $res = Investmentplantransactions::create($transaction);


    //       //                $paymentData = $this->getPaymentMethodData($request->all(),$insertedid,$type);
    //       //                $res = Memberinvestmentspayments::create($paymentData); 

    //       /*--------------------- received cheque payment -----------------------*/
    //       if ($request->input('payment-mode') == 1) {
    //         $receivedPayment['type'] = 2;
    //         $receivedPayment['branch_id'] = $branch_id;
    //         $receivedPayment['investment_id'] = $insertedid;
    //         $receivedPayment['day_book_id'] = $createDayBook;
    //         $receivedPayment['cheque_id'] = $request['cheque_id'];
    //         $receivedPayment['created_at'] = $globaldate;
    //         $receivedCreate = \App\Models\ReceivedChequePayment::create($receivedPayment);
    //         $dataRC['status'] = 3;
    //         $receivedcheque = \App\Models\ReceivedCheque::find($request['cheque_id']);
    //         $receivedcheque->update($dataRC);
    //       }
    //       if ($request['stationary_charge'] > 0) {
    //         $this->stationaryCharges($insertedid);
    //       }
    //       /*--------------------- received cheque payment -----------------------*/

    //       $text = 'Dear Member, Thank You for Choosing S.B. Micro Finance association Your SFD A/c No.' . $investmentDetail->account_number . ' is Credited on '
    //         . $investmentDetail->created_at->format('d M Y') . ' With Rs. ' . round($investmentDetail->deposite_amount, 2) . 'CR. Thanks Have a good day';
    //       $temaplteId = 1207161519023692218;
    //       break;
    //     case "recurring-deposit":
    //       $description = 'SRD Account opening';
    //       $res = Memberinvestments::create($data);
    //       $insertedid = $res->id;
    //       $investmentDetail = Memberinvestments::find($res->id);
    //       $fNominee = $this->getFirstNomineeData($request->all(), $insertedid, $type);
    //       $res = Memberinvestmentsnominees::create($fNominee);
    //       if ($request['second_nominee_add'] == 1) {
    //         $sNominee = $this->getSecondNomineeData($request->all(), $insertedid, $type);
    //         $res = Memberinvestmentsnominees::create($sNominee);
    //       }
    //       $paymentData = $this->getPaymentMethodData($request->all(), $insertedid, $type);
    //       $res = Memberinvestmentspayments::create($paymentData);
    //       $amountArray = array('1' => $request->input('amount'));
    //       $amount_deposit_by_name = $request->input('firstname') . ' ' . $request->input('lastname');
    //       if ($request->input('payment-mode') == 3) {
    //         //$savingTransaction = $this->savingAccountTransactionData($request->all(),$insertedid,$type);
    //         $savingTransaction = $this->savingAccountTransactionDataNew($request->all(), $insertedid, $type, 'SRD', $investmentAccount);
    //         $res = SavingAccountTranscation::create($savingTransaction);
    //         $satRefId = CommanTransactionsController::createTransactionReferences($res->id, $insertedid);
    //         $sAccountId = $ssbId;
    //         $sAccountAmount = $ssbBalance - $request->input('amount');

    //         $sResult = SavingAccount::find($sAccountId);
    //         $sData['balance'] = $sAccountAmount;
    //         $sResult->update($sData);
    //         $ssbCreateTran = CommanTransactionsController::createTransaction($satRefId, 1, $sAccountId, $request->input('memberAutoId'), $branch_id, $branchCode, $amountArray, 5, $amount_deposit_by_name, $request->input('memberAutoId'), $ssbAccountNumber, $cheque_dd_no = '0', $bank_name = NULL, $branch_name = NULL, $payment_date = date('Y-m-d'), $online_payment_id = NULL, $online_payment_by = NULL, $saving_account_id = 0, 'DR');
    //         $sAccountNumber = $ssbAccountNumber;
    //       } else {
    //         $sAccountNumber = NULL;
    //         $satRefId = NULL;
    //         $ssbCreateTran = $this->commonTransactionLogData($satRefId, $request->all(), $insertedid, $type);
    //       }

    //       // $createDayBook = CommanTransactionsController::createDayBook($ssbCreateTran,$satRefId,2,$insertedid,$request['associatemid'],$request['memberAutoId'],$request['amount'],$request['amount'],$withdrawal=0,$description,$ssbAccountNumber,$branch_id,$branchCode,$amountArray,$request['payment-mode'],$amount_deposit_by_name,$request['memberAutoId'],$investmentAccount,$request['cheque-number'],$request['bank-name'],$request['branch-name'],date('Y-m-d'),$request['transaction-id'],$online_payment_by=NULL,$ssbId,'CR');
    //       // ---------------------------  Day book modify --------------------------    
    //       $ppmode = 0;
    //       if ($request->input('payment-mode') == 1) { //cheque
    //         $ppmode = 1;
    //       }
    //       if ($request->input('payment-mode') == 2) { //onine
    //         $ppmode = 3;
    //       }
    //       if ($request->input('payment-mode') == 3) {
    //         //ssb
    //         $ppmode = 4;
    //       }
    //       $createDayBook = CommanTransactionsController::createDayBookNew($ssbCreateTran, $satRefId, 2, $insertedid, $request['associatemid'], $request['memberAutoId'], $request['amount'], $request['amount'], $withdrawal = 0, $description, $ssbAccountNumber, $branch_id, $branchCode, $amountArray, $ppmode, $amount_deposit_by_name, $request['memberAutoId'], $investmentAccount, $request['cheque-number'], $request['bank-name'], $request['branch-name'], $pdate, $online_transction_no, $online_payment_by = NULL, $ssbId, 'CR', $received_cheque_id, $cheque_deposit_bank_id, $cheque_deposit_bank_ac_id, $online_deposit_bank_id, $online_deposit_bank_ac_id);
    //       // ---------------------------  HEAD IMPLEMENT --------------------------
    //       $this->investHeadCreate($request['amount'], $globaldate, $insertedid, $planId, $cheque_no, $cheque_id, $cheque_date, $online_transction_no, $online_transction_date, $online_deposit_bank_id, $online_deposit_bank_ac_id, $branch_id, $request['associatemid'], $request['memberAutoId'], $ssbId, $createDayBook, $request->input('payment-mode'), $investmentDetail->account_number);
    //       //--------------------------------HEAD IMPLEMENT  -------------------------

    //       /* ------------------ commission genarate-----------------*/
    //       $commission = CommanTransactionsController::commissionDistributeInvestment($request['associatemid'], $insertedid, 3, $request['amount'], 1, $planId, $branch_id, $request['tenure'], $createDayBook);
    //       $commission_collection = CommanTransactionsController::commissionCollectionInvestment($request['associatemid'], $insertedid, 5, $request['amount'], 1, $planId, $branch_id, $request['tenure'], $createDayBook);
    //       /*----- ------  credit business start ---- ---------------*/
    //       $creditBusiness = CommanTransactionsController::associateCreditBusiness($request['associatemid'], $insertedid, 1, $request['amount'], 1, $planId, $request['tenure'], $createDayBook);
    //       /*----- ------  credit business end ---- ---------------*/
    //       /* ------------------ commission genarate-----------------*/
    //       $transaction = $this->transactionData($satRefId, $request->all(), $insertedid, $type, $ssbCreateTran);
    //       $res = Investmentplantransactions::create($transaction);


    //       //                $paymentData = $this->getPaymentMethodData($request->all(),$insertedid,$type);
    //       //                $res = Memberinvestmentspayments::create($paymentData); 

    //       /*--------------------- received cheque payment -----------------------*/
    //       if ($request->input('payment-mode') == 1) {
    //         $receivedPayment['type'] = 2;
    //         $receivedPayment['branch_id'] = $branch_id;
    //         $receivedPayment['investment_id'] = $insertedid;
    //         $receivedPayment['day_book_id'] = $createDayBook;
    //         $receivedPayment['cheque_id'] = $request['cheque_id'];
    //         $receivedPayment['created_at'] = $globaldate;
    //         $receivedCreate = \App\Models\ReceivedChequePayment::create($receivedPayment);
    //         $dataRC['status'] = 3;
    //         $receivedcheque = \App\Models\ReceivedCheque::find($request['cheque_id']);
    //         $receivedcheque->update($dataRC);
    //       }
    //       if ($request['stationary_charge'] > 0) {
    //         $this->stationaryCharges($insertedid);
    //       }
    //       /*--------------------- received cheque payment -----------------------*/

    //       $text = 'Dear Member, Thank You for Choosing S.B. Micro Finance association Your SRD A/c No.' . $investmentDetail->account_number . ' is Credited on '
    //         . $investmentDetail->created_at->format('d M Y') . ' With Rs. ' . round($investmentDetail->deposite_amount, 2) . 'CR. Thanks Have a good day';
    //       $temaplteId = 1207161519023692218;
    //       break;
    //     case "samraddh-bhavhishya":
    //       $description = 'SB Account opening';
    //       $res = Memberinvestments::create($data);
    //       $insertedid = $res->id;
    //       $investmentDetail = Memberinvestments::find($res->id);
    //       $fNominee = $this->getFirstNomineeData($request->all(), $insertedid, $type);
    //       $res = Memberinvestmentsnominees::create($fNominee);
    //       if ($request['second_nominee_add'] == 1) {
    //         $sNominee = $this->getSecondNomineeData($request->all(), $insertedid, $type);
    //         $res = Memberinvestmentsnominees::create($sNominee);
    //       }
    //       $paymentData = $this->getPaymentMethodData($request->all(), $insertedid, $type);
    //       $res = Memberinvestmentspayments::create($paymentData);
    //       $amountArray = array('1' => $request->input('amount'));
    //       $amount_deposit_by_name = $request->input('firstname') . ' ' . $request->input('lastname');
    //       if ($request->input('payment-mode') == 3) {
    //         //  $savingTransaction = $this->savingAccountTransactionData($request->all(),$insertedid,$type);
    //         $savingTransaction = $this->savingAccountTransactionDataNew($request->all(), $insertedid, $type, 'SB', $investmentAccount);
    //         $res = SavingAccountTranscation::create($savingTransaction);
    //         $satRefId = CommanTransactionsController::createTransactionReferences($res->id, $insertedid);
    //         $sAccountId = $ssbId;
    //         $sAccountAmount = $ssbBalance - $request->input('amount');
    //         $sResult = SavingAccount::find($sAccountId);
    //         $sData['balance'] = $sAccountAmount;
    //         $sResult->update($sData);
    //         $ssbCreateTran = CommanTransactionsController::createTransaction($satRefId, 1, $sAccountId, $request->input('memberAutoId'), $branch_id, $branchCode, $amountArray, 5, $amount_deposit_by_name, $request->input('memberAutoId'), $ssbAccountNumber, $cheque_dd_no = '0', $bank_name = NULL, $branch_name = NULL, $payment_date = date('Y-m-d'), $online_payment_id = NULL, $online_payment_by = NULL, $saving_account_id = 0, 'DR');
    //         $sAccountNumber = $ssbAccountNumber;
    //       } else {
    //         $sAccountNumber = NULL;
    //         $satRefId = NULL;
    //         $ssbCreateTran = $this->commonTransactionLogData($satRefId, $request->all(), $insertedid, $type);
    //       }

    //       //$createDayBook = CommanTransactionsController::createDayBook($ssbCreateTran,$satRefId,2,$insertedid,$request['associatemid'],$request['memberAutoId'],$request['amount'],$request['amount'],$withdrawal=0,$description,$ssbAccountNumber,$branch_id,$branchCode,$amountArray,$request['payment-mode'],$amount_deposit_by_name,$request['memberAutoId'],$investmentAccount,$request['cheque-number'],$request['bank-name'],$request['branch-name'],date('Y-m-d'),$request['transaction-id'],$online_payment_by=NULL,$ssbId,'CR');
    //       // ---------------------------  Day book modify --------------------------    
    //       $ppmode = 0;
    //       if ($request->input('payment-mode') == 1) { //cheque
    //         $ppmode = 1;
    //       }
    //       if ($request->input('payment-mode') == 2) { //onine
    //         $ppmode = 3;
    //       }
    //       if ($request->input('payment-mode') == 3) {
    //         //ssb
    //         $ppmode = 4;
    //       }
    //       $createDayBook = CommanTransactionsController::createDayBookNew($ssbCreateTran, $satRefId, 2, $insertedid, $request['associatemid'], $request['memberAutoId'], $request['amount'], $request['amount'], $withdrawal = 0, $description, $ssbAccountNumber, $branch_id, $branchCode, $amountArray, $ppmode, $amount_deposit_by_name, $request['memberAutoId'], $investmentAccount, $request['cheque-number'], $request['bank-name'], $request['branch-name'], $pdate, $online_transction_no, $online_payment_by = NULL, $ssbId, 'CR', $received_cheque_id, $cheque_deposit_bank_id, $cheque_deposit_bank_ac_id, $online_deposit_bank_id, $online_deposit_bank_ac_id);
    //       // ---------------------------  HEAD IMPLEMENT --------------------------
    //       $this->investHeadCreate($request['amount'], $globaldate, $insertedid, $planId, $cheque_no, $cheque_id, $cheque_date, $online_transction_no, $online_transction_date, $online_deposit_bank_id, $online_deposit_bank_ac_id, $branch_id, $request['associatemid'], $request['memberAutoId'], $ssbId, $createDayBook, $request->input('payment-mode'), $investmentDetail->account_number);
    //       //--------------------------------HEAD IMPLEMENT  -------------------------

    //       /* ------------------ commission genarate-----------------*/
    //       $commission = CommanTransactionsController::commissionDistributeInvestment($request['associatemid'], $insertedid, 3, $request['amount'], 1, $planId, $branch_id, $request['tenure'], $createDayBook);
    //       $commission_collection = CommanTransactionsController::commissionCollectionInvestment($request['associatemid'], $insertedid, 5, $request['amount'], 1, $planId, $branch_id, $request['tenure'], $createDayBook);

    //       /*----- ------  credit business start ---- ---------------*/
    //       $creditBusiness = CommanTransactionsController::associateCreditBusiness($request['associatemid'], $insertedid, 1, $request['amount'], 1, $planId, $request['tenure'], $createDayBook);
    //       /*----- ------  credit business end ---- ---------------*/
    //       /* ------------------ commission genarate-----------------*/
    //       $transaction = $this->transactionData($satRefId, $request->all(), $insertedid, $type, $ssbCreateTran);
    //       $res = Investmentplantransactions::create($transaction);


    //       //                $paymentData = $this->getPaymentMethodData($request->all(),$insertedid,$type);
    //       //                $res = Memberinvestmentspayments::create($paymentData); 

    //       /*--------------------- received cheque payment -----------------------*/
    //       if ($request->input('payment-mode') == 1) {
    //         $receivedPayment['type'] = 2;
    //         $receivedPayment['branch_id'] = $branch_id;
    //         $receivedPayment['investment_id'] = $insertedid;
    //         $receivedPayment['day_book_id'] = $createDayBook;
    //         $receivedPayment['cheque_id'] = $request['cheque_id'];
    //         $receivedPayment['created_at'] = $globaldate;
    //         $receivedCreate = \App\Models\ReceivedChequePayment::create($receivedPayment);
    //         $dataRC['status'] = 3;
    //         $receivedcheque = \App\Models\ReceivedCheque::find($request['cheque_id']);
    //         $receivedcheque->update($dataRC);
    //       }
    //       if ($request['stationary_charge'] > 0) {
    //         $this->stationaryCharges($insertedid);
    //       }
    //       /*--------------------- received cheque payment -----------------------*/

    //       $text = 'Dear Member, Thank You for Choosing S.B. Micro Finance association Your S. Bhavhishya A/c No.' . $investmentDetail->account_number . ' is Credited on '
    //         . $investmentDetail->created_at->format('d M Y') . ' With Rs. ' . round($investmentDetail->deposite_amount, 2) . 'CR. Thanks Have a good day';
    //       $temaplteId = 1207161519023692218;
    //       break;
    //   }
    //   DB::commit();
    // } catch (\Exception $ex) {
    //   DB::rollback();
    //   return back()->with('alert', $ex->getMessage());
    // }
    // $contactNumber = array();
    // $memberDetail = Member::find($request['memberAutoId']);
    // $contactNumber[] = $memberDetail->mobile_no;
    // $sendToMember = new Sms();
    // $sendToMember->sendSms($contactNumber, $text, $temaplteId);

    // if ($res) {
    //   // dd to get member investment last inserted record to register investment collector
    //   $plantypeid = $request['investmentplan'];

    //   if ($plantypeid == 1) {
    //     $collector_type = 'investmentsavingcollector';
    //     $typeid = $savingAccountDetail->id;
    //   } else {
    //     $collector_type = 'investmentcollector';
    //     $typeid = $res->investment_id;
    //   }


    //   $associateid = $request['associatemid'];

    //   CollectorAccountStoreLI($collector_type, $typeid, $associateid, $globaldate);
    //   // Register Collector On Register of new Investment and Make an Entry in New Table Collector Account End


    //   //redirect()->route('investment/recipt/'.$insertedid);
    //   return redirect('branch/investment/recipt/' . $insertedid);
    //   return back()->with('success', 'Saved Successfully!');
    // } else {
    //   return back()->with('alert', 'Problem With Register New Plan');
    // }
  }
  public function reinvestStore(Request $request)
  {
    /*$data['member_id'] = $request->memberAutoId;
      $data['reinvest_plan_data'] = json_encode($request->all());
      $tempReinvest = MemberReinvest::insert($data);
      dd( $request->all(), $tempReinvest );*/
    // print_r($_POST);die;
    $plantype = $request->input('plan_type');
    switch ($plantype) {
      case "saving-account":
        $rules = [
          'amount' => 'required',
          'investmentplan' => 'required',
          'form_number' => 'required',
          'memberid' => 'required',
          'fn_first_name' => 'required',
          //'fn_second_name' => 'required',
          'fn_relationship' => 'required',
          'fn_gender' => 'required',
          'fn_dob' => 'required',
          //'fn_age' => 'required',
          'fn_percentage' => 'required',
          //'fn_mobile_number' => 'required',
          //'sn_first_name' => 'required',
          //'sn_second_name' => 'required',
          //'sn_relationship' => 'required',
          //'sn_gender' => 'required',
          //'sn_dob' => 'required',
          //'sn_age' => 'required',
          //'sn_percentage' => 'required',
          //'sn_mobile_number' => 'required',
        ];
        break;
      case "samraddh-kanyadhan-yojana":
        $rules = [
          'form_number' => 'required',
          //'guardian-ralationship' => 'required',
          //'daughter-name' => 'required',
          //'phone-number' => 'required',
          //'dob' => 'required',
          'amount' => 'required',
          //'tenure' => 'required',
          //'age' => 'required',
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
          //'fn_second_name' => 'required',
          'fn_relationship' => 'required',
          'fn_gender' => 'required',
          'fn_dob' => 'required',
          //'fn_age' => 'required',
          'fn_percentage' => 'required',
          //'fn_mobile_number' => 'required',
          //'sn_first_name' => 'required',
          //'sn_second_name' => 'required',
          //'sn_relationship' => 'required',
          //'sn_gender' => 'required',
          //'sn_dob' => 'required',
          //'sn_age' => 'required',
          //'sn_percentage' => 'required',
          //'sn_mobile_number' => 'required',
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
          //'fn_second_name' => 'required',
          'fn_relationship' => 'required',
          'fn_gender' => 'required',
          'fn_dob' => 'required',
          'fn_age' => 'required',
          'fn_percentage' => 'required',
          //'fn_mobile_number' => 'required',
          //'sn_first_name' => 'required',
          //'sn_second_name' => 'required',
          //'sn_relationship' => 'required',
          //'sn_gender' => 'required',
          //'sn_dob' => 'required',
          //'sn_age' => 'required',
          //'sn_percentage' => 'required',
          //'sn_mobile_number' => 'required',
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
          //'fn_second_name' => 'required',
          'fn_relationship' => 'required',
          'fn_gender' => 'required',
          'fn_dob' => 'required',
          //'fn_age' => 'required',
          'fn_percentage' => 'required',
          //'fn_mobile_number' => 'required',
          //'sn_first_name' => 'required',
          //'sn_second_name' => 'required',
          //'sn_relationship' => 'required',
          //'sn_gender' => 'required',
          //'sn_dob' => 'required',
          //'sn_age' => 'required',
          //'sn_percentage' => 'required',
          //'sn_mobile_number' => 'required',
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
          //'fn_second_name' => 'required',
          'fn_relationship' => 'required',
          'fn_gender' => 'required',
          'fn_dob' => 'required',
          //'fn_age' => 'required',
          'fn_percentage' => 'required',
          //'fn_mobile_number' => 'required',
          //'sn_first_name' => 'required',
          //'sn_second_name' => 'required',
          //'sn_relationship' => 'required',
          //'sn_gender' => 'required',
          //'sn_dob' => 'required',
          //'sn_age' => 'required',
          //'sn_percentage' => 'required',
          //'sn_mobile_number' => 'required',
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
          //'fn_second_name' => 'required',
          'fn_relationship' => 'required',
          'fn_gender' => 'required',
          'fn_dob' => 'required',
          //'fn_age' => 'required',
          'fn_percentage' => 'required',
          //'fn_mobile_number' => 'required',
          //'sn_first_name' => 'required',
          //'sn_second_name' => 'required',
          //'sn_relationship' => 'required',
          //'sn_gender' => 'required',
          //'sn_dob' => 'required',
          //'sn_age' => 'required',
          //'sn_percentage' => 'required',
          //'sn_mobile_number' => 'required',
        ];
        break;
      case "monthly-income-scheme":
        $rules = [
          'form_number' => 'required',
          'amount' => 'required',
          'ssbacount' => 'required',
          //'ssbacount' =>  'required',
          'payment-mode' => 'required',
          'investmentplan' => 'required',
          'memberid' => 'required',
          'fn_first_name' => 'required',
          //'fn_second_name' => 'required',
          'fn_relationship' => 'required',
          'fn_gender' => 'required',
          'fn_dob' => 'required',
          //'fn_age' => 'required',
          'fn_percentage' => 'required',
          //'fn_mobile_number' => 'required',
          //'sn_first_name' => 'required',
          //'sn_second_name' => 'required',
          //'sn_relationship' => 'required',
          //'sn_gender' => 'required',
          //'sn_dob' => 'required',
          //'sn_age' => 'required',
          //'sn_percentage' => 'required',
          //'sn_mobile_number' => 'required',
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
          //'fn_second_name' => 'required',
          'fn_relationship' => 'required',
          'fn_gender' => 'required',
          'fn_dob' => 'required',
          //'fn_age' => 'required',
          'fn_percentage' => 'required',
          //'fn_mobile_number' => 'required',
          //'sn_first_name' => 'required',
          //'sn_second_name' => 'required',
          //'sn_relationship' => 'required',
          //'sn_gender' => 'required',
          //'sn_dob' => 'required',
          //'sn_age' => 'required',
          //'sn_percentage' => 'required',
          //'sn_mobile_number' => 'required',
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
          //'fn_second_name' => 'required',
          'fn_relationship' => 'required',
          'fn_gender' => 'required',
          'fn_dob' => 'required',
          //'fn_age' => 'required',
          'fn_percentage' => 'required',
          //'fn_mobile_number' => 'required',
          //'sn_first_name' => 'required',
          //'sn_second_name' => 'required',
          //'sn_relationship' => 'required',
          //'sn_gender' => 'required',
          //'sn_dob' => 'required',
          //'sn_age' => 'required',
          //'sn_percentage' => 'required',
          //'sn_mobile_number' => 'required',
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
          //'fn_second_name' => 'required',
          'fn_relationship' => 'required',
          'fn_gender' => 'required',
          'fn_dob' => 'required',
          //'fn_age' => 'required',
          'fn_percentage' => 'required',
          //'fn_mobile_number' => 'required',
          //'sn_first_name' => 'required',
          //'sn_second_name' => 'required',
          //'sn_relationship' => 'required',
          //'sn_gender' => 'required',
          //'sn_dob' => 'required',
          //'sn_age' => 'required',
          //'sn_percentage' => 'required',
          //'sn_mobile_number' => 'required',
        ];
        break;
    }
    $customMessages = [
      'fn_first_name.required' => 'The first name field is required.',
      'required' => 'The :attribute field is required.'
    ];
    $this->validate($request, $rules, $customMessages);
    $type = 'create';
    /*DB::beginTransaction();
      try {*/
    if ($request->input('payment-mode') == 1) {
      $getChequeDetail = \App\Models\ReceivedCheque::where('id', $request['cheque_id'])->where('status', 3)->first(['id', 'amount', 'status']);
      if (!empty($getChequeDetail)) {
        return back()->with('alert', 'Cheque already used select another cheque');
      } else {
        $getamount = \App\Models\ReceivedCheque::where('id', $request['cheque_id'])->first(['id', 'amount']);
        if ($getamount->amount != number_format((float) $request['amount'], 4, '.', '')) {
          return back()->with('alert', 'Investment  amount is not equal to cheque amount');
        }
      }
    }
    //get login user branch id(branch manager)pass auth id
    $getBranchId = getUserBranchId(Auth::user()->id);
    $branch_id = $getBranchId->id;
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
    $investmentAccount = $request->account_number_for_reinvest;
    $data = $this->getDataForReinvest($request->all(), $type, $miCode, $investmentAccount, $branch_id);
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
    switch ($plantype) {
      case "samraddh-kanyadhan-yojana":
        $description = 'SK Account opening';
        $res = MemberReinvest::create($data);
        $insertedid = $res->id;
        break;
      case "special-samraddh-money-back":
        $description = 'SMB Account opening';
        $res = Memberinvestments::create($data);
        $monyBackAccount = $res->id;
        $insertedid = $res->id;
        $fNominee = $this->getFirstNomineeData($request->all(), $insertedid, $type);
        $res = Memberinvestmentsnominees::create($fNominee);
        if ($request['second_nominee_add'] == 1) {
          $sNominee = $this->getSecondNomineeData($request->all(), $insertedid, $type);
          $res = Memberinvestmentsnominees::create($sNominee);
        }
        $paymentData = $this->getPaymentMethodData($request->all(), $insertedid, $type);
        $res = Memberinvestmentspayments::create($paymentData);
        $amountArray = array('1' => $request->input('amount'));
        $amount_deposit_by_name = $request->input('firstname') . ' ' . $request->input('lastname');
        if ($request->input('payment-mode') == 3) {
          $savingTransaction = $this->savingAccountTransactionData($request->all(), $insertedid, $type);
          $res = SavingAccountTranscation::create($savingTransaction);
          $satRefId = CommanTransactionsController::createTransactionReferences($res->id, $insertedid);
          $sAccountId = $ssbId;
          $sAccountAmount = $ssbBalance - $request->input('amount');
          $sResult = SavingAccount::find($sAccountId);
          $sData['balance'] = $sAccountAmount;
          $sResult->update($sData);
          $ssbCreateTran = CommanTransactionsController::createTransaction($satRefId, 1, $sAccountId, $request->input('memberAutoId'), $branch_id, $branchCode, $amountArray, 5, $amount_deposit_by_name, $request->input('memberAutoId'), $ssbAccountNumber, $cheque_dd_no = '0', $bank_name = NULL, $branch_name = NULL, $payment_date = date('Y-m-d'), $online_payment_id = NULL, $online_payment_by = NULL, $saving_account_id = 0, 'DR');
          $sAccountNumber = $ssbAccountNumber;
        } else {
          $sAccountNumber = NULL;
          $satRefId = NULL;
          $ssbCreateTran = $this->commonTransactionLogData($satRefId, $request->all(), $insertedid, $type);
        }
        $createDayBook = CommanTransactionsController::createDayBook($ssbCreateTran, $satRefId, 1, $insertedid, $request['associatemid'], $request['memberAutoId'], $request['amount'], $request['amount'], $withdrawal = 0, $description, $ssbAccountNumber, $branch_id, $branchCode, $amountArray, $request['payment-mode'], $amount_deposit_by_name, $request['memberAutoId'], $investmentAccount, $request['cheque-number'], $request['bank-name'], $request['branch-name'], date('Y-m-d'), $request['transaction-id'], $online_payment_by = NULL, $ssbId, 'CR');
        /* ------------------ commission genarate-----------------*/
        $commission = CommanTransactionsController::commissionDistributeInvestment($request['associatemid'], $insertedid, 3, $request['amount'], 1, $planId, $branch_id, $request['tenure'], $createDayBook);
        $commission_collection = CommanTransactionsController::commissionCollectionInvestment($request['associatemid'], $insertedid, 5, $request['amount'], 1, $planId, $branch_id, $request['tenure'], $createDayBook);
        /*----- ------  credit business start ---- ---------------*/
        $creditBusiness = CommanTransactionsController::associateCreditBusiness($request['associatemid'], $insertedid, 1, $request['amount'], 1, $planId, $request['tenure'], $createDayBook);
        /*----- ------  credit business end ---- ---------------*/
        /* ------------------ commission genarate-----------------*/
        $transaction = $this->transactionData($satRefId, $request->all(), $insertedid, $type, $ssbCreateTran);
        $res = Investmentplantransactions::create($transaction);
        $paymentData = $this->getPaymentMethodData($request->all(), $insertedid, $type);
        $res = Memberinvestmentspayments::create($paymentData);
        /*--------------------- received cheque payment -----------------------*/
        if ($request->input('payment-mode') == 1) {
          $receivedPayment['type'] = 2;
          $receivedPayment['branch_id'] = $branch_id;
          $receivedPayment['investment_id'] = $insertedid;
          $receivedPayment['day_book_id'] = $createDayBook;
          $receivedPayment['cheque_id'] = $request['cheque_id'];
          $receivedPayment['created_at'] = $globaldate;
          $receivedCreate = \App\Models\ReceivedChequePayment::create($receivedPayment);
          $dataRC['status'] = 3;
          $receivedcheque = \App\Models\ReceivedCheque::find($request['cheque_id']);
          $receivedcheque->update($dataRC);
        }
        /*--------------------- received cheque payment -----------------------*/
        $memberInvestData = Memberinvestments::with('ssb')->find($monyBackAccount);
        $text = 'Dear Member, Thank You for Choosing S.B. Micro Finance association Your Saving A/C ' . $memberInvestData->ssb_account_number . ' is Created on ' . $memberInvestData->created_at->format('d M Y') . ' with Rs. 500.00 CR, Money Back A/c No. ' . $memberInvestData->account_number . ' is Created on ' . $memberInvestData->created_at->format('d M Y') . ' with Rs. ' . round($memberInvestData->deposite_amount, 2) . ' CR. Have a good day';
        break;
      case "flexi-fixed-deposit":
        $description = 'FFD Account opening';
        $res = MemberReinvest::create($data);
        $insertedid = $res->id;
        $investmentDetail = MemberReinvest::find($res->id);
        // dd($investmentDetail);
        /*$fNominee = $this->getFirstNomineeData($request->all(),$insertedid,$type);
            $res = Memberinvestmentsnominees::create($fNominee);
            if($request['second_nominee_add']==1){
              $sNominee = $this->getSecondNomineeData($request->all(),$insertedid,$type);
              $res = Memberinvestmentsnominees::create($sNominee);
            }
            $paymentData = $this->getPaymentMethodData($request->all(),$insertedid,$type);
            $res = Memberinvestmentspayments::create($paymentData);
            $amountArray=array('1'=>$request->input('amount'));
            $amount_deposit_by_name=$request->input('firstname').' '.$request->input('lastname');
            if($request->input('payment-mode')==3){
              $savingTransaction = $this->savingAccountTransactionData($request->all(),$insertedid,$type);
              $res = SavingAccountTranscation::create($savingTransaction);
              $satRefId = CommanTransactionsController::createTransactionReferences($res->id,$insertedid);
              $sAccountId = $ssbId;
              $sAccountAmount = $ssbBalance-$request->input('amount');
              $sResult = SavingAccount::find($sAccountId);
              $sData['balance'] = $sAccountAmount;
              $sResult->update($sData);
              $ssbCreateTran = CommanTransactionsController::createTransaction($satRefId,1,$sAccountId,$request->input('memberAutoId'),$branch_id,$branchCode,$amountArray,5,$amount_deposit_by_name,$request->input('memberAutoId'),$ssbAccountNumber,$cheque_dd_no='0',$bank_name=NULL,$branch_name=NULL,$payment_date=date('Y-m-d'),$online_payment_id=NULL,$online_payment_by=NULL,$saving_account_id=0,'DR');
              $sAccountNumber = $ssbAccountNumber;
            }else{
              $sAccountNumber = NULL;
              $satRefId = NULL;
              $ssbCreateTran = $this->commonTransactionLogData($satRefId,$request->all(),$insertedid,$type);
            }*/
        /*$createDayBook = CommanTransactionsController::createDayBook($ssbCreateTran,$satRefId,2,$insertedid,$request['associatemid'],$request['memberAutoId'],$request['amount'],$request['amount'],$withdrawal=0,$description,$sAccountNumber,$branch_id,$branchCode,$amountArray,$request['payment-mode'],$amount_deposit_by_name,$request['memberAutoId'],$investmentAccount,$request['cheque-number'],NULL,NULL,date('Y-m-d'),NULL,$online_payment_by=NULL,$ssbId,'CR');*/
        /* ------------------ commission genarate-----------------*/
        /*$commission =CommanTransactionsController:: commissionDistributeInvestment($request['associatemid'],$insertedid,3,$request['amount'],1,$planId,$branch_id,$request['tenure'],$createDayBook);
            $commission_collection =CommanTransactionsController::commissionCollectionInvestment($request['associatemid'],$insertedid,5,$request['amount'],1,$planId,$branch_id,$request['tenure'],$createDayBook);*/
        /*----- ------  credit business start ---- ---------------*/
        /*$creditBusiness =CommanTransactionsController::associateCreditBusiness($request['associatemid'],$insertedid,1,$request['amount'],1,$planId,$request['tenure'],$createDayBook);*/
        /*----- ------  credit business end ---- ---------------*/
        /* ------------------ commission genarate-----------------*/
        /*$transaction = $this->transactionData($satRefId,$request->all(),$insertedid,$type,$ssbCreateTran);
            $res = Investmentplantransactions::create($transaction);
            $paymentData = $this->getPaymentMethodData($request->all(),$insertedid,$type);
            $res = Memberinvestmentspayments::create($paymentData);
            $text = 'Dear Member, Thank You for Choosing S.B. Micro Finance association Your FFD A/c No.'. $investmentDetail->account_number.' is Credited on '
              .$investmentDetail->created_at->format('d M Y').' With Rs. '.round($investmentDetail->deposite_amount,2).'. Thanks Have a good day';*/
        break;
      case "fixed-recurring-deposit":
        $description = 'FRD Account opening';
        $res = MemberReinvest::create($data);
        $insertedid = $res->id;
        $investmentDetail = MemberReinvest::find($res->id);
        $fNominee = $this->getFirstNomineeData($request->all(), $insertedid, $type);
        /*$res = Memberinvestmentsnominees::create($fNominee);
            if($request['second_nominee_add']==1){
              $sNominee = $this->getSecondNomineeData($request->all(),$insertedid,$type);
              $res = Memberinvestmentsnominees::create($sNominee);
            }
            $paymentData = $this->getPaymentMethodData($request->all(),$insertedid,$type);
            $res = Memberinvestmentspayments::create($paymentData);
            $amountArray=array('1'=>$request->input('amount'));
            $amount_deposit_by_name=$request->input('firstname').' '.$request->input('lastname');
            if($request->input('payment-mode')==3){
              $savingTransaction = $this->savingAccountTransactionData($request->all(),$insertedid,$type);
              $res = SavingAccountTranscation::create($savingTransaction);
              $satRefId = CommanTransactionsController::createTransactionReferences($res->id,$insertedid);
              $sAccountId = $ssbId;
              $sAccountAmount = $ssbBalance-$request->input('amount');
              $sResult = SavingAccount::find($sAccountId);
              $sData['balance'] = $sAccountAmount;
              $sResult->update($sData);
              $ssbCreateTran = CommanTransactionsController::createTransaction($satRefId,1,$sAccountId,$request->input('memberAutoId'),$branch_id,$branchCode,$amountArray,5,$amount_deposit_by_name,$request->input('memberAutoId'),$ssbAccountNumber,$cheque_dd_no='0',$bank_name=NULL,$branch_name=NULL,$payment_date=date('Y-m-d'),$online_payment_id=NULL,$online_payment_by=NULL,$saving_account_id=0,'DR');
              $sAccountNumber = $ssbAccountNumber;
            }else{
              $sAccountNumber = NULL;
              $satRefId = NULL;
              $ssbCreateTran = $this->commonTransactionLogData($satRefId,$request->all(),$insertedid,$type);
            }
            $createDayBook = CommanTransactionsController::createDayBook($ssbCreateTran,$satRefId,2,$insertedid,$request['associatemid'],$request['memberAutoId'],$request['amount'],$request['amount'],$withdrawal=0,$description,$sAccountNumber,$branch_id,$branchCode,$amountArray,$request['payment-mode'],$amount_deposit_by_name,$request['memberAutoId'],$investmentAccount,$request['cheque-number'],NULL,NULL,date('Y-m-d'),NULL,$online_payment_by=NULL,$ssbId,'CR');*/
        /* ------------------ commission genarate-----------------*/
        /*$commission =CommanTransactionsController:: commissionDistributeInvestment($request['associatemid'],$insertedid,3,$request['amount'],1,$planId,$branch_id,$request['tenure'],$createDayBook);
            $commission_collection =CommanTransactionsController::commissionCollectionInvestment($request['associatemid'],$insertedid,5,$request['amount'],1,$planId,$branch_id,$request['tenure'],$createDayBook);*/
        /*----- ------  credit business start ---- ---------------*/
        /*$creditBusiness =CommanTransactionsController::associateCreditBusiness($request['associatemid'],$insertedid,1,$request['amount'],1,$planId,$request['tenure']);*/
        /*----- ------  credit business end ---- ---------------*/
        /* ------------------ commission genarate-----------------*/
        /*$transaction = $this->transactionData($satRefId,$request->all(),$insertedid,$type,$ssbCreateTran);
            $res = Investmentplantransactions::create($transaction);
            $paymentData = $this->getPaymentMethodData($request->all(),$insertedid,$type);
            $res = Memberinvestmentspayments::create($paymentData);
            $text = 'Dear Member, Thank You for Choosing S.B. Micro Finance association Your FRD A/c No.'. $investmentDetail->account_number.' is Credited on '
              .$investmentDetail->created_at->format('d M Y').' With Rs. '.round($investmentDetail->deposite_amount,2).'CR. Thanks Have a good day';*/
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
        $res = Memberinvestmentspayments::create($paymentData);
        $amountArray = array('1' => $request->input('amount'));
        $amount_deposit_by_name = $request->input('firstname') . ' ' . $request->input('lastname');
        if ($request->input('payment-mode') == 3) {
          $savingTransaction = $this->savingAccountTransactionData($request->all(), $insertedid, $type);
          $res = SavingAccountTranscation::create($savingTransaction);
          $satRefId = CommanTransactionsController::createTransactionReferences($res->id, $insertedid);
          $sAccountId = $ssbId;
          $sAccountAmount = $ssbBalance - $request->input('amount');
          $sResult = SavingAccount::find($sAccountId);
          $sData['balance'] = $sAccountAmount;
          $sResult->update($sData);
          $ssbCreateTran = CommanTransactionsController::createTransaction($satRefId, 1, $sAccountId, $request->input('memberAutoId'), $branch_id, $branchCode, $amountArray, 5, $amount_deposit_by_name, $request->input('memberAutoId'), $ssbAccountNumber, $cheque_dd_no = '0', $bank_name = NULL, $branch_name = NULL, $payment_date = date('Y-m-d'), $online_payment_id = NULL, $online_payment_by = NULL, $saving_account_id = 0, 'DR');
          $sAccountNumber = $ssbAccountNumber;
        } else {
          $sAccountNumber = NULL;
          $satRefId = NULL;
          $ssbCreateTran = $this->commonTransactionLogData($satRefId, $request->all(), $insertedid, $type);
        }
        $createDayBook = CommanTransactionsController::createDayBook($ssbCreateTran, $satRefId, 1, $insertedid, $request['associatemid'], $request['memberAutoId'], $request['amount'], $request['amount'], $withdrawal = 0, $description, $ssbAccountNumber, $branch_id, $branchCode, $amountArray, $request['payment-mode'], $amount_deposit_by_name, $request['memberAutoId'], $investmentAccount, $request['cheque-number'], $request['bank-name'], $request['branch-name'], date('Y-m-d'), $request['transaction-id'], $online_payment_by = NULL, $ssbId, 'CR');
        /* ------------------ commission genarate-----------------*/
        $commission = CommanTransactionsController::commissionDistributeInvestment($request['associatemid'], $insertedid, 3, $request['amount'], 1, $planId, $branch_id, $request['tenure'], $createDayBook);
        $commission_collection = CommanTransactionsController::commissionCollectionInvestment($request['associatemid'], $insertedid, 5, $request['amount'], 1, $planId, $branch_id, $request['tenure'], $createDayBook);
        /*----- ------  credit business start ---- ---------------*/
        $creditBusiness = CommanTransactionsController::associateCreditBusiness($request['associatemid'], $insertedid, 1, $request['amount'], 1, $planId, $request['tenure'], $createDayBook);
        /*----- ------  credit business end ---- ---------------*/
        /* ------------------ commission genarate-----------------*/
        $transaction = $this->transactionData($satRefId, $request->all(), $insertedid, $type, $ssbCreateTran);
        $res = Investmentplantransactions::create($transaction);
        //		$paymentData = $this->getPaymentMethodData($request->all(),$insertedid,$type);
        //		$res = Memberinvestmentspayments::create($paymentData);
        /*--------------------- received cheque payment -----------------------*/
        if ($request->input('payment-mode') == 1) {
          $receivedPayment['type'] = 2;
          $receivedPayment['branch_id'] = $branch_id;
          $receivedPayment['investment_id'] = $insertedid;
          $receivedPayment['day_book_id'] = $createDayBook;
          $receivedPayment['cheque_id'] = $request['cheque_id'];
          $receivedPayment['created_at'] = $globaldate;
          $receivedCreate = \App\Models\ReceivedChequePayment::create($receivedPayment);
          $dataRC['status'] = 3;
          $receivedcheque = \App\Models\ReceivedCheque::find($request['cheque_id']);
          $receivedcheque->update($dataRC);
        }
        /*--------------------- received cheque payment -----------------------*/
        $memberInvestData = Memberinvestments::with('ssb')->find($monyBackAccount);
        $text = 'Dear Member, Thank You for Choosing S.B. Micro Finance association Your Saving A/C ' . $memberInvestData->ssb_account_number . ' is Created on ' . $memberInvestData->created_at->format('d M Y') . ' with Rs. 500.00 CR, S. Jeevan A/c No. ' . $memberInvestData->account_number . ' is Created on ' . $memberInvestData->created_at->format('d M Y') . ' with Rs. ' . round($memberInvestData->deposite_amount, 2) . ' CR. Have a good day';
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
        $res = Memberinvestmentspayments::create($paymentData);
        $amountArray = array('1' => $request->input('amount'));
        $amount_deposit_by_name = $request->input('firstname') . ' ' . $request->input('lastname');
        if ($request->input('payment-mode') == 3) {
          $savingTransaction = $this->savingAccountTransactionData($request->all(), $insertedid, $type);
          $res = SavingAccountTranscation::create($savingTransaction);
          $satRefId = CommanTransactionsController::createTransactionReferences($res->id, $insertedid);
          $sAccountId = $ssbId;
          $sAccountAmount = $ssbBalance - $request->input('amount');
          $sResult = SavingAccount::find($sAccountId);
          $sData['balance'] = $sAccountAmount;
          $sResult->update($sData);
          $ssbCreateTran = CommanTransactionsController::createTransaction($satRefId, 1, $sAccountId, $request->input('memberAutoId'), $branch_id, $branchCode, $amountArray, 5, $amount_deposit_by_name, $request->input('memberAutoId'), $ssbAccountNumber, $cheque_dd_no = '0', $bank_name = NULL, $branch_name = NULL, $payment_date = date('Y-m-d'), $online_payment_id = NULL, $online_payment_by = NULL, $saving_account_id = 0, 'DR');
          $sAccountNumber = $ssbAccountNumber;
        } else {
          $sAccountNumber = NULL;
          $satRefId = NULL;
          $ssbCreateTran = $this->commonTransactionLogData($satRefId, $request->all(), $insertedid, $type);
        }
        $createDayBook = CommanTransactionsController::createDayBook($ssbCreateTran, $satRefId, 1, $insertedid, $request['associatemid'], $request['memberAutoId'], $request['amount'], $request['amount'], $withdrawal = 0, $description, $ssbAccountNumber, $branch_id, $branchCode, $amountArray, $request['payment-mode'], $amount_deposit_by_name, $request['memberAutoId'], $investmentAccount, $request['cheque-number'], $request['bank-name'], $request['branch-name'], date('Y-m-d'), $request['transaction-id'], $online_payment_by = NULL, $ssbId, 'CR');
        /* ------------------ commission genarate-----------------*/
        $commission = CommanTransactionsController::commissionDistributeInvestment($request['associatemid'], $insertedid, 3, $request['amount'], 1, $planId, $branch_id, $request['tenure'], $createDayBook);
        $commission_collection = CommanTransactionsController::commissionCollectionInvestment($request['associatemid'], $insertedid, 5, $request['amount'], 1, $planId, $branch_id, $request['tenure'], $createDayBook);
        /*----- ------  credit business start ---- ---------------*/
        $creditBusiness = CommanTransactionsController::associateCreditBusiness($request['associatemid'], $insertedid, 1, $request['amount'], 1, $planId, $request['tenure'], $createDayBook);
        /*----- ------  credit business end ---- ---------------*/
        /* ------------------ commission genarate-----------------*/
        $transaction = $this->transactionData($satRefId, $request->all(), $insertedid, $type, $ssbCreateTran);
        $res = Investmentplantransactions::create($transaction);
        //  $paymentData = $this->getPaymentMethodData($request->all(),$insertedid,$type);
        //  $res = Memberinvestmentspayments::create($paymentData);
        /*--------------------- received cheque payment -----------------------*/
        if ($request->input('payment-mode') == 1) {
          $receivedPayment['type'] = 2;
          $receivedPayment['branch_id'] = $branch_id;
          $receivedPayment['investment_id'] = $insertedid;
          $receivedPayment['day_book_id'] = $createDayBook;
          $receivedPayment['cheque_id'] = $request['cheque_id'];
          $receivedPayment['created_at'] = $globaldate;
          $receivedCreate = \App\Models\ReceivedChequePayment::create($receivedPayment);
          $dataRC['status'] = 3;
          $receivedcheque = \App\Models\ReceivedCheque::find($request['cheque_id']);
          $receivedcheque->update($dataRC);
        }
        /*--------------------- received cheque payment -----------------------*/
        $text = 'Dear Member, Thank You for Choosing S.B. Micro Finance association Your SDD A/c No.' . $investmentDetail->account_number . ' is Credited on '
          . $investmentDetail->created_at->format('d M Y') . ' With Rs. ' . round($investmentDetail->deposite_amount, 2) . 'CR. Thanks Have a good day';
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
        $res = Memberinvestmentspayments::create($paymentData);
        $amountArray = array('1' => $request->input('amount'));
        $amount_deposit_by_name = $request->input('firstname') . ' ' . $request->input('lastname');
        if ($request->input('payment-mode') == 3) {
          $savingTransaction = $this->savingAccountTransactionData($request->all(), $insertedid, $type);
          $res = SavingAccountTranscation::create($savingTransaction);
          $satRefId = CommanTransactionsController::createTransactionReferences($res->id, $insertedid);
          $sAccountId = $ssbId;
          $sAccountAmount = $ssbBalance - $request->input('amount');
          $sResult = SavingAccount::find($sAccountId);
          $sData['balance'] = $sAccountAmount;
          $sResult->update($sData);
          $ssbCreateTran = CommanTransactionsController::createTransaction($satRefId, 1, $sAccountId, $request->input('memberAutoId'), $branch_id, $branchCode, $amountArray, 5, $amount_deposit_by_name, $request->input('memberAutoId'), $ssbAccountNumber, $cheque_dd_no = '0', $bank_name = NULL, $branch_name = NULL, $payment_date = date('Y-m-d'), $online_payment_id = NULL, $online_payment_by = NULL, $saving_account_id = 0, 'DR');
          $sAccountNumber = $ssbAccountNumber;
        } else {
          $sAccountNumber = NULL;
          $satRefId = NULL;
          $ssbCreateTran = $this->commonTransactionLogData($satRefId, $request->all(), $insertedid, $type);
        }
        $createDayBook = CommanTransactionsController::createDayBook($ssbCreateTran, $satRefId, 1, $insertedid, $request['associatemid'], $request['memberAutoId'], $request['amount'], $request['amount'], $withdrawal = 0, $description, $ssbAccountNumber, $branch_id, $branchCode, $amountArray, $request['payment-mode'], $amount_deposit_by_name, $request['memberAutoId'], $investmentAccount, $request['cheque-number'], $request['bank-name'], $request['branch-name'], date('Y-m-d'), $request['transaction-id'], $online_payment_by = NULL, $ssbId, 'CR');
        /* ------------------ commission genarate-----------------*/
        $commission = CommanTransactionsController::commissionDistributeInvestment($request['associatemid'], $insertedid, 3, $request['amount'], 1, $planId, $branch_id, $request['tenure'], $createDayBook);
        $commission_collection = CommanTransactionsController::commissionCollectionInvestment($request['associatemid'], $insertedid, 5, $request['amount'], 1, $planId, $branch_id, $request['tenure'], $createDayBook);
        /*----- ------  credit business start ---- ---------------*/
        $creditBusiness = CommanTransactionsController::associateCreditBusiness($request['associatemid'], $insertedid, 1, $request['amount'], 1, $planId, $request['tenure'], $createDayBook);
        /*----- ------  credit business end ---- ---------------*/
        /* ------------------ commission genarate-----------------*/
        $transaction = $this->transactionData($satRefId, $request->all(), $insertedid, $type, $ssbCreateTran);
        $res = Investmentplantransactions::create($transaction);
        //  $paymentData = $this->getPaymentMethodData($request->all(),$insertedid,$type);
        //  $res = Memberinvestmentspayments::create($paymentData);
        /*--------------------- received cheque payment -----------------------*/
        if ($request->input('payment-mode') == 1) {
          $receivedPayment['type'] = 2;
          $receivedPayment['branch_id'] = $branch_id;
          $receivedPayment['investment_id'] = $insertedid;
          $receivedPayment['day_book_id'] = $createDayBook;
          $receivedPayment['cheque_id'] = $request['cheque_id'];
          $receivedPayment['created_at'] = $globaldate;
          $receivedCreate = \App\Models\ReceivedChequePayment::create($receivedPayment);
          $dataRC['status'] = 3;
          $receivedcheque = \App\Models\ReceivedCheque::find($request['cheque_id']);
          $receivedcheque->update($dataRC);
        }
        /*--------------------- received cheque payment -----------------------*/
        $memberInvestData = Memberinvestments::with('ssb')->find($monyBackAccount);
        $text = 'Dear Member, Thank You for Choosing S.B. Micro Finance association Your Saving A/C ' . $memberInvestData->ssb_account_number . ' is Created on ' . $memberInvestData->created_at->format('d M Y') . ' with Rs. 500.00 CR, MIS A/c No. ' . $memberInvestData->account_number . ' is Created on ' . $memberInvestData->created_at->format('d M Y') . ' with Rs. ' . round($memberInvestData->deposite_amount, 2) . ' CR. Have a good day';
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
        $res = Memberinvestmentspayments::create($paymentData);
        $amountArray = array('1' => $request->input('amount'));
        $amount_deposit_by_name = $request->input('firstname') . ' ' . $request->input('lastname');
        if ($request->input('payment-mode') == 3) {
          $savingTransaction = $this->savingAccountTransactionData($request->all(), $insertedid, $type);
          $res = SavingAccountTranscation::create($savingTransaction);
          $satRefId = CommanTransactionsController::createTransactionReferences($res->id, $insertedid);
          $sAccountId = $ssbId;
          $sAccountAmount = $ssbBalance - $request->input('amount');
          $sResult = SavingAccount::find($sAccountId);
          $sData['balance'] = $sAccountAmount;
          $sResult->update($sData);
          $ssbCreateTran = CommanTransactionsController::createTransaction($satRefId, 1, $sAccountId, $request->input('memberAutoId'), $branch_id, $branchCode, $amountArray, 5, $amount_deposit_by_name, $request->input('memberAutoId'), $ssbAccountNumber, $cheque_dd_no = '0', $bank_name = NULL, $branch_name = NULL, $payment_date = date('Y-m-d'), $online_payment_id = NULL, $online_payment_by = NULL, $saving_account_id = 0, 'DR');
          $sAccountNumber = $ssbAccountNumber;
        } else {
          $sAccountNumber = NULL;
          $satRefId = NULL;
          $ssbCreateTran = $this->commonTransactionLogData($satRefId, $request->all(), $insertedid, $type);
        }
        $createDayBook = CommanTransactionsController::createDayBook($ssbCreateTran, $satRefId, 1, $insertedid, $request['associatemid'], $request['memberAutoId'], $request['amount'], $request['amount'], $withdrawal = 0, $description, $ssbAccountNumber, $branch_id, $branchCode, $amountArray, $request['payment-mode'], $amount_deposit_by_name, $request['memberAutoId'], $investmentAccount, $request['cheque-number'], $request['bank-name'], $request['branch-name'], date('Y-m-d'), $request['transaction-id'], $online_payment_by = NULL, $ssbId, 'CR');
        /* ------------------ commission genarate-----------------*/
        $commission = CommanTransactionsController::commissionDistributeInvestment($request['associatemid'], $insertedid, 3, $request['amount'], 1, $planId, $branch_id, $request['tenure'], $createDayBook);
        $commission_collection = CommanTransactionsController::commissionCollectionInvestment($request['associatemid'], $insertedid, 5, $request['amount'], 1, $planId, $branch_id, $request['tenure'], $createDayBook);
        /*----- ------  credit business start ---- ---------------*/
        $creditBusiness = CommanTransactionsController::associateCreditBusiness($request['associatemid'], $insertedid, 1, $request['amount'], 1, $planId, $request['tenure'], $createDayBook);
        /*----- ------  credit business end ---- ---------------*/
        /* ------------------ commission genarate-----------------*/
        $transaction = $this->transactionData($satRefId, $request->all(), $insertedid, $type, $ssbCreateTran);
        $res = Investmentplantransactions::create($transaction);
        //    $paymentData = $this->getPaymentMethodData($request->all(),$insertedid,$type);
        //  $res = Memberinvestmentspayments::create($paymentData);
        /*--------------------- received cheque payment -----------------------*/
        if ($request->input('payment-mode') == 1) {
          $receivedPayment['type'] = 2;
          $receivedPayment['branch_id'] = $branch_id;
          $receivedPayment['investment_id'] = $insertedid;
          $receivedPayment['day_book_id'] = $createDayBook;
          $receivedPayment['cheque_id'] = $request['cheque_id'];
          $receivedPayment['created_at'] = $globaldate;
          $receivedCreate = \App\Models\ReceivedChequePayment::create($receivedPayment);
          $dataRC['status'] = 3;
          $receivedcheque = \App\Models\ReceivedCheque::find($request['cheque_id']);
          $receivedcheque->update($dataRC);
        }
        /*--------------------- received cheque payment -----------------------*/
        $text = 'Dear Member, Thank You for Choosing S.B. Micro Finance association Your SFD A/c No.' . $investmentDetail->account_number . ' is Credited on '
          . $investmentDetail->created_at->format('d M Y') . ' With Rs. ' . round($investmentDetail->deposite_amount, 2) . 'CR. Thanks Have a good day';
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
        $res = Memberinvestmentspayments::create($paymentData);
        $amountArray = array('1' => $request->input('amount'));
        $amount_deposit_by_name = $request->input('firstname') . ' ' . $request->input('lastname');
        if ($request->input('payment-mode') == 3) {
          $savingTransaction = $this->savingAccountTransactionData($request->all(), $insertedid, $type);
          $res = SavingAccountTranscation::create($savingTransaction);
          $satRefId = CommanTransactionsController::createTransactionReferences($res->id, $insertedid);
          $sAccountId = $ssbId;
          $sAccountAmount = $ssbBalance - $request->input('amount');
          $sResult = SavingAccount::find($sAccountId);
          $sData['balance'] = $sAccountAmount;
          $sResult->update($sData);
          $ssbCreateTran = CommanTransactionsController::createTransaction($satRefId, 1, $sAccountId, $request->input('memberAutoId'), $branch_id, $branchCode, $amountArray, 5, $amount_deposit_by_name, $request->input('memberAutoId'), $ssbAccountNumber, $cheque_dd_no = '0', $bank_name = NULL, $branch_name = NULL, $payment_date = date('Y-m-d'), $online_payment_id = NULL, $online_payment_by = NULL, $saving_account_id = 0, 'DR');
          $sAccountNumber = $ssbAccountNumber;
        } else {
          $sAccountNumber = NULL;
          $satRefId = NULL;
          $ssbCreateTran = $this->commonTransactionLogData($satRefId, $request->all(), $insertedid, $type);
        }
        $createDayBook = CommanTransactionsController::createDayBook($ssbCreateTran, $satRefId, 1, $insertedid, $request['associatemid'], $request['memberAutoId'], $request['amount'], $request['amount'], $withdrawal = 0, $description, $ssbAccountNumber, $branch_id, $branchCode, $amountArray, $request['payment-mode'], $amount_deposit_by_name, $request['memberAutoId'], $investmentAccount, $request['cheque-number'], $request['bank-name'], $request['branch-name'], date('Y-m-d'), $request['transaction-id'], $online_payment_by = NULL, $ssbId, 'CR');
        /* ------------------ commission genarate-----------------*/
        $commission = CommanTransactionsController::commissionDistributeInvestment($request['associatemid'], $insertedid, 3, $request['amount'], 1, $planId, $branch_id, $request['tenure'], $createDayBook);
        $commission_collection = CommanTransactionsController::commissionCollectionInvestment($request['associatemid'], $insertedid, 5, $request['amount'], 1, $planId, $branch_id, $request['tenure'], $createDayBook);
        /*----- ------  credit business start ---- ---------------*/
        $creditBusiness = CommanTransactionsController::associateCreditBusiness($request['associatemid'], $insertedid, 1, $request['amount'], 1, $planId, $request['tenure'], $createDayBook);
        /*----- ------  credit business end ---- ---------------*/
        /* ------------------ commission genarate-----------------*/
        $transaction = $this->transactionData($satRefId, $request->all(), $insertedid, $type, $ssbCreateTran);
        $res = Investmentplantransactions::create($transaction);
        //$paymentData = $this->getPaymentMethodData($request->all(),$insertedid,$type);
        //$res = Memberinvestmentspayments::create($paymentData);
        /*--------------------- received cheque payment -----------------------*/
        if ($request->input('payment-mode') == 1) {
          $receivedPayment['type'] = 2;
          $receivedPayment['branch_id'] = $branch_id;
          $receivedPayment['investment_id'] = $insertedid;
          $receivedPayment['day_book_id'] = $createDayBook;
          $receivedPayment['cheque_id'] = $request['cheque_id'];
          $receivedPayment['created_at'] = $globaldate;
          $receivedCreate = \App\Models\ReceivedChequePayment::create($receivedPayment);
          $dataRC['status'] = 3;
          $receivedcheque = \App\Models\ReceivedCheque::find($request['cheque_id']);
          $receivedcheque->update($dataRC);
        }
        /*--------------------- received cheque payment -----------------------*/
        $text = 'Dear Member, Thank You for Choosing S.B. Micro Finance association Your SRD A/c No.' . $investmentDetail->account_number . ' is Credited on '
          . $investmentDetail->created_at->format('d M Y') . ' With Rs. ' . round($investmentDetail->deposite_amount, 2) . 'CR. Thanks Have a good day';
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
        $res = Memberinvestmentspayments::create($paymentData);
        $amountArray = array('1' => $request->input('amount'));
        $amount_deposit_by_name = $request->input('firstname') . ' ' . $request->input('lastname');
        if ($request->input('payment-mode') == 3) {
          $savingTransaction = $this->savingAccountTransactionData($request->all(), $insertedid, $type);
          $res = SavingAccountTranscation::create($savingTransaction);
          $satRefId = CommanTransactionsController::createTransactionReferences($res->id, $insertedid);
          $sAccountId = $ssbId;
          $sAccountAmount = $ssbBalance - $request->input('amount');
          $sResult = SavingAccount::find($sAccountId);
          $sData['balance'] = $sAccountAmount;
          $sResult->update($sData);
          $ssbCreateTran = CommanTransactionsController::createTransaction($satRefId, 1, $sAccountId, $request->input('memberAutoId'), $branch_id, $branchCode, $amountArray, 5, $amount_deposit_by_name, $request->input('memberAutoId'), $ssbAccountNumber, $cheque_dd_no = '0', $bank_name = NULL, $branch_name = NULL, $payment_date = date('Y-m-d'), $online_payment_id = NULL, $online_payment_by = NULL, $saving_account_id = 0, 'DR');
          $sAccountNumber = $ssbAccountNumber;
        } else {
          $sAccountNumber = NULL;
          $satRefId = NULL;
          $ssbCreateTran = $this->commonTransactionLogData($satRefId, $request->all(), $insertedid, $type);
        }
        $createDayBook = CommanTransactionsController::createDayBook($ssbCreateTran, $satRefId, 1, $insertedid, $request['associatemid'], $request['memberAutoId'], $request['amount'], $request['amount'], $withdrawal = 0, $description, $ssbAccountNumber, $branch_id, $branchCode, $amountArray, $request['payment-mode'], $amount_deposit_by_name, $request['memberAutoId'], $investmentAccount, $request['cheque-number'], $request['bank-name'], $request['branch-name'], date('Y-m-d'), $request['transaction-id'], $online_payment_by = NULL, $ssbId, 'CR');
        /* ------------------ commission genarate-----------------*/
        $commission = CommanTransactionsController::commissionDistributeInvestment($request['associatemid'], $insertedid, 3, $request['amount'], 1, $planId, $branch_id, $request['tenure'], $createDayBook);
        $commission_collection = CommanTransactionsController::commissionCollectionInvestment($request['associatemid'], $insertedid, 5, $request['amount'], 1, $planId, $branch_id, $request['tenure'], $createDayBook);
        /*----- ------  credit business start ---- ---------------*/
        $creditBusiness = CommanTransactionsController::associateCreditBusiness($request['associatemid'], $insertedid, 1, $request['amount'], 1, $planId, $request['tenure'], $createDayBook);
        /*----- ------  credit business end ---- ---------------*/
        /* ------------------ commission genarate-----------------*/
        $transaction = $this->transactionData($satRefId, $request->all(), $insertedid, $type, $ssbCreateTran);
        $res = Investmentplantransactions::create($transaction);
        //$paymentData = $this->getPaymentMethodData($request->all(),$insertedid,$type);
        //$res = Memberinvestmentspayments::create($paymentData);
        /*--------------------- received cheque payment -----------------------*/
        if ($request->input('payment-mode') == 1) {
          $receivedPayment['type'] = 2;
          $receivedPayment['branch_id'] = $branch_id;
          $receivedPayment['investment_id'] = $insertedid;
          $receivedPayment['day_book_id'] = $createDayBook;
          $receivedPayment['cheque_id'] = $request['cheque_id'];
          $receivedPayment['created_at'] = $globaldate;
          $receivedCreate = \App\Models\ReceivedChequePayment::create($receivedPayment);
          $dataRC['status'] = 3;
          $receivedcheque = \App\Models\ReceivedCheque::find($request['cheque_id']);
          $receivedcheque->update($dataRC);
        }
        /*--------------------- received cheque payment -----------------------*/
        $text = 'Dear Member, Thank You for Choosing S.B. Micro Finance association Your S. Bhavhishya A/c No.' . $investmentDetail->account_number . ' is Credited on '
          . $investmentDetail->created_at->format('d M Y') . ' With Rs. ' . round($investmentDetail->deposite_amount, 2) . 'CR. Thanks Have a good day';
        break;
    }
    /*DB::commit();
      } catch (\Exception $ex) {
        DB::rollback();*/
    // dd($insertedid);
    /*return back()->with('alert', $ex->getMessage());
      }*/
    $contactNumber = array();
    $memberDetail = Member::find($request['memberAutoId']);
    $contactNumber[] = $memberDetail->mobile_no;
    //$sendToMember = new Sms();
    //$sendToMember->sendSms( $contactNumber, $text );
    // dd($insertedid);
    if ($res) {
      //redirect()->route('investment/recipt/'.$insertedid);
      return redirect('branch/investment/recipt/' . $insertedid);
      return back()->with('success', 'Saved Successfully!');
    } else {
      return back()->with('alert', 'Problem With Register New Plan');
    }
  }
  public function getDataForReinvest($request, $type, $miCode, $investmentAccount, $branch_id)
  {
    $plantype = $request['plan_type'];
    $faCode = getPlanCode($request['investmentplan']);
    switch ($plantype) {
      case "samraddh-kanyadhan-yojana":
        $data['guardians_relation'] = $request['guardian-ralationship'];
        $data['daughter_name'] = $request['daughter-name'];
        $data['phone_number'] = $request['phone-number'];
        $data['dob'] = date("Y-m-d", strtotime(str_replace('/', '-', $request['dob'])));
        $data['age'] = $request['age'];
        $data['deposite_amount'] = $request['amount'];
        $data['current_balance'] = $request['amount'];
        //$data['due_amount'] = $request['amount'];
        $data['tenure'] = $request['tenure'];
        $data['payment_mode'] = $request['payment-mode'];
        $data['maturity_amount'] = $request['maturity-amount'];
        $data['interest_rate'] = $request['interest-rate'];
        break;
      case "special-samraddh-money-back":
        $data['ssb_account_number'] = $request['ssbacount'];
        $data['deposite_amount'] = $request['amount'];
        $data['current_balance'] = $request['amount'];
        //$data['due_amount'] = $request['amount'];
        $data['payment_mode'] = $request['payment-mode'];
        $data['maturity_amount'] = $request['maturity-amount'];
        $data['interest_rate'] = $request['interest-rate'];
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
        $data['current_balance'] = $request['amount'];
        $data['payment_mode'] = $request['payment-mode'];
        $data['maturity_amount'] = $request['maturity-amount'];
        $data['interest_rate'] = $request['interest-rate'];
        break;
      case "fixed-recurring-deposit":
        $data['tenure'] = $request['tenure'] / 12;
        $data['deposite_amount'] = $request['amount'];
        $data['current_balance'] = $request['amount'];
        //$data['due_amount'] = $request['amount'];
        $data['payment_mode'] = $request['payment-mode'];
        $data['maturity_amount'] = $request['maturity-amount'];
        $data['interest_rate'] = $request['interest-rate'];
        break;
      case "samraddh-jeevan":
        $data['tenure'] = $request['tenure'] / 12;
        $data['ssb_account_number'] = $request['ssbacount'];
        $data['deposite_amount'] = $request['amount'];
        $data['current_balance'] = $request['amount'];
        //$data['due_amount'] = $request['amount'];
        $data['payment_mode'] = $request['payment-mode'];
        $data['maturity_amount'] = $request['maturity-amount'];
        $data['interest_rate'] = $request['interest-rate'];
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
        $data['current_balance'] = $request['amount'];
        //$data['due_amount'] = $request['amount'];
        $data['payment_mode'] = $request['payment-mode'];
        $data['interest_rate'] = $request['interest-rate'];
        $data['maturity_amount'] = $request['maturity-amount'];
        break;
      case "monthly-income-scheme":
        $data['ssb_account_number'] = $request['ssbacount'];
        $data['deposite_amount'] = $request['amount'];
        $data['current_balance'] = $request['amount'];
        $data['payment_mode'] = $request['payment-mode'];
        $data['tenure'] = $request['tenure'] / 12;
        $data['maturity_amount'] = $request['maturity-amount'];
        $data['interest_rate'] = $request['interest_rate'];
        break;
      case "fixed-deposit":
        $data['deposite_amount'] = $request['amount'];
        $data['current_balance'] = $request['amount'];
        $data['payment_mode'] = $request['payment-mode'];
        $data['tenure'] = $request['tenure'] / 12;
        $data['maturity_amount'] = $request['maturity-amount'];
        $data['interest_rate'] = $request['interest-rate'];
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
        $data['current_balance'] = $request['amount'];
        //$data['due_amount'] = $request['amount'];
        $data['payment_mode'] = $request['payment-mode'];
        $data['maturity_amount'] = $request['maturity-amount'];
        $data['interest_rate'] = $request['interest-rate'];
        break;
      case "samraddh-bhavhishya":
        $data['deposite_amount'] = $request['amount'];
        $data['current_balance'] = $request['amount'];
        $data['tenure'] = $request['tenure'] / 12;
        $data['maturity_amount'] = $request['maturity-amount'];
        $data['interest_rate'] = $request['interest-rate'];
        break;
    }
    if ($type == 'create') {
      if ($plantype == 'saving-account') {
        $data['ssb_account_number'] = $investmentAccount;
        $data['created_at'] = $request['created_at'];
      }
      $data['mi_code'] = $miCode;
      $data['account_number'] = $investmentAccount;
      $data['plan_id'] = $request['investmentplan'];
      $data['form_number'] = $request['form_number'];
      $data['member_id'] = $request['memberAutoId'];
      $data['associate_id'] = $request['associatemid'];
      $data['branch_id'] = $branch_id;
      $data['old_branch_id'] = $branch_id;
      $data['created_at'] = $request['created_at'];
    }
    return $data;
  }
  /**
   * Store a newly created plan in storage.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\Response
   */
  public function openSavingAccount(Request $request)
  {
    $sId = \App\Models\Branch::where('name', 'like', '%' . Auth::user()->username . '%')->first('state_id');
    $created_at = checkMonthAvailability(date('d'), date('m'), date('Y'), $sId->state_id);
    $request->request->add(['memberAutoId' => $request['saving_account_m_id']]);
    $request->request->add(['investmentplan' => $request['current_plan_id']]);
    $request->request->add(['amount' => $request['ssbamount']]);
    $request->request->add(['plan_type' => $request['account_box_class']]);
    $request->request->add(['payment-mode' => 0]);
    $request->request->add(['created_at' => $created_at]);
    $request->request->add(['associatemid' => $request['saving_account_a_id']]);
    //$created_at = $request['created_at'];
    $getSavingAccount = SavingAccount::where('member_id', $request['memberAutoId'])->first();
    //echo "<pre>"; print_r($getSavingAccount); die;
    if ($getSavingAccount != '') {
      $nomineeForm = $request['nominee_form_class'];
      $accountInput = $request['account_box_class'];
      return Response::json(['msg_type' => 'exists', 'investmentAccount' => $getSavingAccount->account_no, 'nomineeForm' => $nomineeForm, 'accountInput' => $accountInput]);
    } else {
      DB::beginTransaction();
      try {
        Session::put('created_at', $created_at);
        $getBranchId = getUserBranchId(Auth::user()->id);
        $branch_id = $getBranchId->id;
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
        $ssbdata['current_balance'] = $request['ssbamount'];
        $ssbdata['created_at'] = $created_at;
        $res = Memberinvestments::create($ssbdata);
        $investmentId = $res->id;
        $savingAccountId = $res->account_number;
        //$description = 'Cash deposit - opening';
        if ($request['current_plan_id'] == 3) {
          $description = 'SMB Account opening';
        } elseif ($request['current_plan_id'] == 6) {
          $description = 'SJ Account opening';
        } elseif ($request['current_plan_id'] == 8) {
          $description = 'MIS Account opening';
        } else {
          $description = '';
        }
        /*$createAccount = CommanTransactionsController::createSavingAccount($request['saving_account_m_id'],$branch_id,$branchCode,$request['ssbamount'],0,$investmentId,$miCode,$investmentAccount,$is_primary,$faCode);*/
        //$createAccount = CommanTransactionsController::createSavingAccount($request['saving_account_m_id'],$branch_id,$branchCode,$request['ssbamount'],0,$investmentId,$miCode,$investmentAccount,$is_primary,$faCode,$description);
        $createAccount = CommanTransactionsController::createSavingAccountDescriptionModify($request['saving_account_m_id'], $branch_id, $branchCode, $request['ssbamount'], 0, $investmentId, $miCode, $investmentAccount, $is_primary, $faCode, 'SSB Account opening', $request['saving_account_a_id']);
        $mRes = Member::find($request['saving_account_m_id']);
        $mData['ssb_account'] = $investmentAccount;
        $mRes->update($mData);
        $satRefId = CommanTransactionsController::createTransactionReferences($createAccount['ssb_transaction_id'], $investmentId);
        $ssbAccountId = $createAccount['ssb_id'];
        $amountArraySsb = array('1' => $request['ssbamount']);
        $amount_deposit_by_name = $request['saving_account_m_name'];
        $ssbCreateTran = CommanTransactionsController::createTransaction($satRefId, 1, $ssbAccountId, $request['saving_account_m_id'], $branch_id, $branchCode, $amountArraySsb, 0, $amount_deposit_by_name, $request['saving_account_m_id'], $investmentAccount, $cheque_dd_no = '0', $bank_name = NULL, $branch_name = NULL, $payment_date = NULL, $online_payment_id = NULL, $online_payment_by = NULL, $saving_account_id = 0, 'CR');
        $sAccount = $this->getSavingAccountDetails($request->input('saving_account_m_id'));
        if (count($sAccount) > 0) {
          $ssbAccountNumber = $sAccount[0]->account_no;
          $ssbId = $sAccount[0]->id;
        } else {
          $ssbAccountNumber = '';
          $ssbId = '';
        }
        $createDayBook = CommanTransactionsController::createDayBook($ssbCreateTran, $satRefId, 1, $investmentId, $request['associatemid'], $request['saving_account_m_id'], $request['ssbamount'], $request['ssbamount'], $withdrawal = 0, $description, $ssbAccountNumber, $branch_id, $branchCode, $amountArraySsb, 0, $amount_deposit_by_name, $request['saving_account_m_id'], $investmentAccount, 0, NULL, NULL, date('Y-m-d'), NULL, $online_payment_by = NULL, $ssbId, 'CR');
        // ---------------------------  HEAD IMPLEMENT --------------------------
        $this->investHeadCreateSSB($request['ssbamount'], $created_at, $ssbAccountId, $planId, NULL, NULL, NULL, NULL, NULL, NULL, NULL, $branch_id, $request['associatemid'], $request['saving_account_m_id'], $ssbId, $createAccount['ssb_transaction_id'], 0, $investmentAccount);
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
        $ssbfndata['created_at'] = $created_at;
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
          $ssbfndata['created_at'] = $created_at;
          $res = Memberinvestmentsnominees::create($ssbsndata);
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
        //return back()->with('alert', $ex->getMessage());
        return Response::json(['view' => $ex->getMessage(), 'msg_type' => 'error']);
      }
      if ($investmentId) {
        return Response::json(['msg_type' => 'success', 'investmentAccount' => $investmentAccount, 'nomineeForm' => $nomineeForm, 'accountInput' => $accountInput]);
      } else if (count($record) > 0) {
        return Response::json(['view' => 'Account Already Created!', 'msg_type' => 'exists']);
      } else {
        return Response::json(['view' => 'Somthing went wrong!', 'msg_type' => 'error']);
      }
    }
  }
  /**
   * Display created investment by id.
   *
   * @param  $id
   * @return \Illuminate\Http\Response
   */
  public function edit(Request $request, $id)
  {
    //     $data['title'] = "Investment Detail";
//     $data['plans'] = Plans::all();
//     $data['investments'] = Memberinvestments::with('plan', 'member')->findOrFail($id);
//     $data['countRenewals'] = Daybook::where('investment_id', $data['investments']->id)->where('account_no', $data['investments']->account_number)->whereNotIn('transaction_type', [3, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 19])->count();
//     $data['idProof'] = MemberIdProof::where('member_id', $data['investments']->member_id)->pluck('first_id_no')->first();
//     $data['mDetails'] = \App\Models\MemberCompany::leftJoin('special_categories', 'members.special_category_id', '=', 'special_categories.id')
//       ->leftJoin('saving_accounts', 'members.id', '=', 'saving_accounts.member_id')
//       ->where('members.id', $data['investments']['member_id'])
//       ->select('saving_accounts.account_no', 'saving_accounts.balance')
//       ->get();

    // dd( $data['mDetails']);

    //     $data['aDetails'] = Member::leftJoin('carders', 'members.current_carder_id', '=', 'carders.id')
//       ->where('members.id', $data['investments']['associate_id'])
//       ->select('members.id', 'members.first_name', 'members.last_name', 'members.mobile_no', 'carders.name as carder_name', 'members.associate_no')
//       ->get();
//     //echo "<pre>"; print_r($data['investments']); die;
//     $data['formName'] = $data['investments']['plan']->slug;
//     $data['id'] = $id;
//     if ($id && isset($id)) {
//       $data['correctionStatus'] = getCorrectionStatus(2, $id);
//     } else {
//       $data['correctionStatus'] = '';
//     }

    $data['title'] = "Investment Detail";
    $data['plans'] = Plans::all();
    $data['action'] = $request->segment(3);
    $data['investments'] = Memberinvestments::with('member:id,member_id,first_name,last_name', 'memberCompany:id,customer_id,member_id', 'company:id,name')->with(['plan' => function ($q) {
      $q->withoutGlobalScope(ActiveScope::class);
    }])->findOrFail($id);
    $data['countRenewals'] = Daybook::where('investment_id', $data['investments']->id)->where('account_no', $data['investments']->account_number)->whereNotIn('transaction_type', [3, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 19])->count();
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
    $data['formName'] = $data['investments']['plan']['slug'];
    $data['id'] = $id;
    if ($id && isset($id)) {
      $data['correctionStatus'] = getCorrectionStatus(2, $id);
    } else {
      $data['correctionStatus'] = '';
    }
    return view('templates.branch.investment_management.edit', $data);
  }
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
    $data['investments'] = Memberinvestments::with('investmentNomiees', 'investmentPayment')->findOrFail($id);
    $data['relations'] = Relations::all();
    $data['action'] = '';
    //$age = floor((date("Y-m-d")-$data['investments']['re_dob']) / (365.25 * 24 * 60 * 60 * 1000));

    $diff = abs(strtotime(date("Y-m-d")) - strtotime($data['investments']['re_dob']));
    $data['re_age'] = floor($diff / (365 * 60 * 60 * 24));

    return view('templates.branch.investment_management.' . $plan . '.edit-' . $plan . '', $data);
  }
  /**
   * Update the specified plan.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\Response
   */
  public function Update(Request $request)
  {
    $plantype = $request->input('plan_type');
    switch ($plantype) {
      case "saving-account":
        $rules = [
          'form_number' => 'required',
          'amount' => 'required',
          'investmentplan' => 'required',
          'memberid' => 'required',
          'fn_first_name' => 'required',
          //'fn_second_name' => 'required',
          'fn_relationship' => 'required',
          'fn_gender' => 'required',
          'fn_dob' => 'required',
          //'fn_age' => 'required',
          'fn_percentage' => 'required',
          //'fn_mobile_number' => 'required',
          //'sn_first_name' => 'required',
          //'sn_second_name' => 'required',
          //'sn_relationship' => 'required',
          //'sn_gender' => 'required',
          //'sn_dob' => 'required',
          //'sn_age' => 'required',
          //'sn_percentage' => 'required',
          //'sn_mobile_number' => 'required',
        ];
        break;
      case "samraddh-kanyadhan-yojana":
        $rules = [
          'form_number' => 'required',
          //'guardian-ralationship' => 'required',
          //'daughter-name' => 'required',
          //'phone-number' => 'required',
          //'dob' => 'required',
          'amount' => 'required',
          //'tenure' => 'required',
          //'age' => 'required',
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
          //'fn_second_name' => 'required',
          'fn_relationship' => 'required',
          'fn_gender' => 'required',
          'fn_dob' => 'required',
          //'fn_age' => 'required',
          'fn_percentage' => 'required',
          //'fn_mobile_number' => 'required',
          //'sn_first_name' => 'required',
          //'sn_second_name' => 'required',
          //'sn_relationship' => 'required',
          //'sn_gender' => 'required',
          //'sn_dob' => 'required',
          //'sn_age' => 'required',
          //'sn_percentage' => 'required',
          //'sn_mobile_number' => 'required',
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
          //'fn_second_name' => 'required',
          'fn_relationship' => 'required',
          'fn_gender' => 'required',
          'fn_dob' => 'required',
          //'fn_age' => 'required',
          'fn_percentage' => 'required',
          //'fn_mobile_number' => 'required',
          //'sn_first_name' => 'required',
          //'sn_second_name' => 'required',
          //'sn_relationship' => 'required',
          //'sn_gender' => 'required',
          //'sn_dob' => 'required',
          //'sn_age' => 'required',
          //'sn_percentage' => 'required',
          //'sn_mobile_number' => 'required',
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
          //'fn_second_name' => 'required',
          'fn_relationship' => 'required',
          'fn_gender' => 'required',
          'fn_dob' => 'required',
          //'fn_age' => 'required',
          'fn_percentage' => 'required',
          //'fn_mobile_number' => 'required',
          //'sn_first_name' => 'required',
          //'sn_second_name' => 'required',
          //'sn_relationship' => 'required',
          //'sn_gender' => 'required',
          //'sn_dob' => 'required',
          //'sn_age' => 'required',
          //'sn_percentage' => 'required',
          //'sn_mobile_number' => 'required',
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
          //'fn_second_name' => 'required',
          'fn_relationship' => 'required',
          'fn_gender' => 'required',
          'fn_dob' => 'required',
          //'fn_age' => 'required',
          'fn_percentage' => 'required',
          //'fn_mobile_number' => 'required',
          //'sn_first_name' => 'required',
          //'sn_second_name' => 'required',
          //'sn_relationship' => 'required',
          //'sn_gender' => 'required',
          //'sn_dob' => 'required',
          //'sn_age' => 'required',
          //'sn_percentage' => 'required',
          //'sn_mobile_number' => 'required',
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
          //'fn_second_name' => 'required',
          'fn_relationship' => 'required',
          'fn_gender' => 'required',
          'fn_dob' => 'required',
          //'fn_age' => 'required',
          'fn_percentage' => 'required',
          //'fn_mobile_number' => 'required',
          //'sn_first_name' => 'required',
          //'sn_second_name' => 'required',
          //'sn_relationship' => 'required',
          //'sn_gender' => 'required',
          //'sn_dob' => 'required',
          //'sn_age' => 'required',
          //'sn_percentage' => 'required',
          //'sn_mobile_number' => 'required',
        ];
        break;
      case "monthly-income-scheme":
        $rules = [
          'form_number' => 'required',
          'amount' => 'required',
          'ssbacount' => 'required',
          //'ssbacount' =>  'required',
          'payment-mode' => 'required',
          'investmentplan' => 'required',
          'memberid' => 'required',
          'fn_first_name' => 'required',
          //'fn_second_name' => 'required',
          'fn_relationship' => 'required',
          'fn_gender' => 'required',
          'fn_dob' => 'required',
          //'fn_age' => 'required',
          'fn_percentage' => 'required',
          //'fn_mobile_number' => 'required',
          //'sn_first_name' => 'required',
          //'sn_second_name' => 'required',
          //'sn_relationship' => 'required',
          //'sn_gender' => 'required',
          //'sn_dob' => 'required',
          //'sn_age' => 'required',
          //'sn_percentage' => 'required',
          //'sn_mobile_number' => 'required',
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
          //'fn_second_name' => 'required',
          'fn_relationship' => 'required',
          'fn_gender' => 'required',
          'fn_dob' => 'required',
          //'fn_age' => 'required',
          'fn_percentage' => 'required',
          //'fn_mobile_number' => 'required',
          //'sn_first_name' => 'required',
          //'sn_second_name' => 'required',
          //'sn_relationship' => 'required',
          //'sn_gender' => 'required',
          //'sn_dob' => 'required',
          //'sn_age' => 'required',
          //'sn_percentage' => 'required',
          //'sn_mobile_number' => 'required',
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
          //'fn_second_name' => 'required',
          'fn_relationship' => 'required',
          'fn_gender' => 'required',
          'fn_dob' => 'required',
          //'fn_age' => 'required',
          'fn_percentage' => 'required',
          //'fn_mobile_number' => 'required',
          //'sn_first_name' => 'required',
          //'sn_second_name' => 'required',
          //'sn_relationship' => 'required',
          //'sn_gender' => 'required',
          //'sn_dob' => 'required',
          //'sn_age' => 'required',
          //'sn_percentage' => 'required',
          //'sn_mobile_number' => 'required',
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
          //'fn_second_name' => 'required',
          'fn_relationship' => 'required',
          'fn_gender' => 'required',
          'fn_dob' => 'required',
          //'fn_age' => 'required',
          'fn_percentage' => 'required',
          //'fn_mobile_number' => 'required',
          //'sn_first_name' => 'required',
          //'sn_second_name' => 'required',
          //'sn_relationship' => 'required',
          //'sn_gender' => 'required',
          //'sn_dob' => 'required',
          //'sn_age' => 'required',
          //'sn_percentage' => 'required',
          //'sn_mobile_number' => 'required',
        ];
        break;
    }
    $customMessages = [
      'fn_first_name.required' => 'The first name field is required.',
      'required' => 'The :attribute field is required.'
    ];
    $this->validate($request, $rules, $customMessages);
    $type = 'update';
    $investmentId = $request->input('investmentId');
    //get login user branch id(branch manager)pass auth id
    $getBranchId = getUserBranchId(Auth::user()->id);
    $branch_id = $getBranchId->id;
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
    // Invesment Account no 
    $investmentAccount = $branchCode . $faCode . $miCode;
    $data = $this->getData($request->all(), $type, $miCode, $investmentAccount, $branch_id);
    $investment = Memberinvestments::find($investmentId);
    $investment->update($data);
    switch ($plantype) {
      case "saving-account":
        Memberinvestmentsnominees::where('investment_id', $investmentId)->delete();
        $fNominee = $this->getFirstNomineeData($request->all(), $investmentId, $type);
        $res = Memberinvestmentsnominees::create($fNominee);
        if ($request['second_nominee']) {
          $sNominee = $this->getSecondNomineeData($request->all(), $insertedid, $type);
          $res = Memberinvestmentsnominees::create($sNominee);
        }
        break;
      case "samraddh-kanyadhan-yojana":
        Memberinvestmentspayments::where('investment_id', $investmentId)->delete();
        $paymentData = $this->getPaymentMethodData($request->all(), $investmentId, $type);
        $res = Memberinvestmentspayments::create($paymentData);
        break;
      case "special-samraddh-money-back":
        Memberinvestmentsnominees::where('investment_id', $investmentId)->delete();
        $fNominee = $this->getFirstNomineeData($request->all(), $investmentId, $type);
        $fRes = Memberinvestmentsnominees::create($fNominee);
        if ($request['second_nominee']) {
          $sNominee = $this->getSecondNomineeData($request->all(), $investmentId, $type);
          $sRes = Memberinvestmentsnominees::create($sNominee);
        }
        Memberinvestmentspayments::where('investment_id', $investmentId)->delete();
        $paymentData = $this->getPaymentMethodData($request->all(), $investmentId, $type);
        $res = Memberinvestmentspayments::create($paymentData);
        break;
      case "flexi-fixed-deposit":
        Memberinvestmentsnominees::where('investment_id', $investmentId)->delete();
        $fNominee = $this->getFirstNomineeData($request->all(), $investmentId, $type);
        $fRes = Memberinvestmentsnominees::create($fNominee);
        if ($request['second_nominee']) {
          $sNominee = $this->getSecondNomineeData($request->all(), $investmentId, $type);
          $sRes = Memberinvestmentsnominees::create($sNominee);
        }
        Memberinvestmentspayments::where('investment_id', $investmentId)->delete();
        $paymentData = $this->getPaymentMethodData($request->all(), $investmentId, $type);
        $res = Memberinvestmentspayments::create($paymentData);
        break;
      case "fixed-recurring-deposit":
        Memberinvestmentsnominees::where('investment_id', $investmentId)->delete();
        $fNominee = $this->getFirstNomineeData($request->all(), $investmentId, $type);
        $fRes = Memberinvestmentsnominees::create($fNominee);
        if ($request['second_nominee']) {
          $sNominee = $this->getSecondNomineeData($request->all(), $investmentId, $type);
          $sRes = Memberinvestmentsnominees::create($sNominee);
        }
        Memberinvestmentspayments::where('investment_id', $investmentId)->delete();
        $paymentData = $this->getPaymentMethodData($request->all(), $investmentId, $type);
        $res = Memberinvestmentspayments::create($paymentData);
        break;
      case "samraddh-jeevan":
        Memberinvestmentsnominees::where('investment_id', $investmentId)->delete();
        $fNominee = $this->getFirstNomineeData($request->all(), $investmentId, $type);
        $fRes = Memberinvestmentsnominees::create($fNominee);
        if ($request['second_nominee']) {
          $sNominee = $this->getSecondNomineeData($request->all(), $investmentId, $type);
          $sRes = Memberinvestmentsnominees::create($sNominee);
        }
        Memberinvestmentspayments::where('investment_id', $investmentId)->delete();
        $paymentData = $this->getPaymentMethodData($request->all(), $investmentId, $type);
        $res = Memberinvestmentspayments::create($paymentData);
        break;
      case "daily-deposit":
        Memberinvestmentsnominees::where('investment_id', $investmentId)->delete();
        $fNominee = $this->getFirstNomineeData($request->all(), $investmentId, $type);
        $fRes = Memberinvestmentsnominees::create($fNominee);
        if ($request['second_nominee']) {
          $sNominee = $this->getSecondNomineeData($request->all(), $investmentId, $type);
          $sRes = Memberinvestmentsnominees::create($sNominee);
        }
        Memberinvestmentspayments::where('investment_id', $investmentId)->delete();
        $paymentData = $this->getPaymentMethodData($request->all(), $investmentId, $type);
        $res = Memberinvestmentspayments::create($paymentData);
        break;
      case "monthly-income-scheme":
        Memberinvestmentsnominees::where('investment_id', $investmentId)->delete();
        $fNominee = $this->getFirstNomineeData($request->all(), $investmentId, $type);
        $fRes = Memberinvestmentsnominees::create($fNominee);
        if ($request['second_nominee']) {
          $sNominee = $this->getSecondNomineeData($request->all(), $investmentId, $type);
          $sRes = Memberinvestmentsnominees::create($sNominee);
        }
        Memberinvestmentspayments::where('investment_id', $investmentId)->delete();
        $paymentData = $this->getPaymentMethodData($request->all(), $investmentId, $type);
        $res = Memberinvestmentspayments::create($paymentData);
        break;
      case "fixed-deposit":
        Memberinvestmentsnominees::where('investment_id', $investmentId)->delete();
        $fNominee = $this->getFirstNomineeData($request->all(), $investmentId, $type);
        $fRes = Memberinvestmentsnominees::create($fNominee);
        if ($request['second_nominee']) {
          $sNominee = $this->getSecondNomineeData($request->all(), $investmentId, $type);
          $sRes = Memberinvestmentsnominees::create($sNominee);
        }
        Memberinvestmentspayments::where('investment_id', $investmentId)->delete();
        $paymentData = $this->getPaymentMethodData($request->all(), $investmentId, $type);
        $res = Memberinvestmentspayments::create($paymentData);
        break;
      case "recurring-deposit":
        Memberinvestmentsnominees::where('investment_id', $investmentId)->delete();
        $fNominee = $this->getFirstNomineeData($request->all(), $investmentId, $type);
        $fRes = Memberinvestmentsnominees::create($fNominee);
        if ($request['second_nominee']) {
          $sNominee = $this->getSecondNomineeData($request->all(), $investmentId, $type);
          $sRes = Memberinvestmentsnominees::create($sNominee);
        }
        Memberinvestmentspayments::where('investment_id', $investmentId)->delete();
        $paymentData = $this->getPaymentMethodData($request->all(), $investmentId, $type);
        $res = Memberinvestmentspayments::create($paymentData);
        break;
      case "samraddh-bhavhishya":
        Memberinvestmentsnominees::where('investment_id', $investmentId)->delete();
        $fNominee = $this->getFirstNomineeData($request->all(), $investmentId, $type);
        $fRes = Memberinvestmentsnominees::create($fNominee);
        if ($request['second_nominee']) {
          $sNominee = $this->getSecondNomineeData($request->all(), $investmentId, $type);
          $sRes = Memberinvestmentsnominees::create($sNominee);
        }
        Memberinvestmentspayments::where('investment_id', $investmentId)->delete();
        $paymentData = $this->getPaymentMethodData($request->all(), $investmentId, $type);
        $res = Memberinvestmentspayments::create($paymentData);
        break;
    }
    if ($res) {
      return back()->with('success', 'Update was Successful!');
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
  public function getData($request, $type, $miCode, $investmentAccount, $branch_id)
  {
    $plantype = $request['plan_type'];
    $data['old_branch_id'] = $branch_id;
    $faCode = getPlanCode($request['investmentplan']);
    switch ($plantype) {
      // case "saving-account":
      //   $data['deposite_amount'] = $request['amount'];
      //   $data['current_balance'] = $request['amount'];
      //   $data['payment_mode'] = $request['payment-mode'];
      //   /*if($request['hidden_primary_account']){
      //     $data['primary_account'] = $request['primary_account'];
      //   }*/
      //   break;
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
        $data['current_balance'] = $request['amount'];
        //$data['due_amount'] = $request['amount'];
        $data['tenure'] = $request['tenure'];
        $data['payment_mode'] = $request['payment-mode'];
        $data['maturity_amount'] = $request['maturity-amount'];
        $data['maturity_date'] = date("Y-m-d", strtotime('+ ' . ($request['tenure'] * 12) . 'months', strtotime(date("Y/m/d"))));
        $data['interest_rate'] = $request['interest-rate'];
        break;
      case "special-samraddh-money-back":
        $data['ssb_account_number'] = $request['ssbacount'];
        $data['deposite_amount'] = $request['amount'];
        $data['current_balance'] = $request['amount'];
        //$data['due_amount'] = $request['amount'];
        $data['payment_mode'] = $request['payment-mode'];
        $data['maturity_amount'] = $request['maturity-amount'];
        $data['interest_rate'] = $request['interest-rate'];
        $data['tenure'] = $request['tenure'];
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
        $data['current_balance'] = $request['amount'];
        $data['payment_mode'] = $request['payment-mode'];
        $data['maturity_amount'] = $request['maturity-amount'];
        $data['interest_rate'] = $request['interest-rate'];
        $data['maturity_date'] = date("Y-m-d", strtotime('+ ' . ($request['tenure']) . 'months', strtotime(date("Y/m/d"))));
        break;
      case "fixed-recurring-deposit":
        $data['tenure'] = $request['tenure'] / 12;
        $data['deposite_amount'] = $request['amount'];
        $data['current_balance'] = $request['amount'];
        //$data['due_amount'] = $request['amount'];
        $data['payment_mode'] = $request['payment-mode'];
        $data['maturity_amount'] = $request['maturity-amount'];
        $data['interest_rate'] = $request['interest-rate'];
        $data['maturity_date'] = date("Y-m-d", strtotime('+ ' . ($request['tenure']) . 'months', strtotime(date("Y/m/d"))));
        break;
      case "samraddh-jeevan":
        $data['tenure'] = $request['tenure'] / 12;
        $data['ssb_account_number'] = $request['ssbacount'];
        $data['deposite_amount'] = $request['amount'];
        $data['current_balance'] = $request['amount'];
        //$data['due_amount'] = $request['amount'];
        $data['payment_mode'] = $request['payment-mode'];
        $data['maturity_amount'] = $request['maturity-amount'];
        $data['interest_rate'] = $request['interest-rate'];
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
        $data['current_balance'] = $request['amount'];
        //$data['due_amount'] = $request['amount'];
        $data['payment_mode'] = $request['payment-mode'];
        $data['interest_rate'] = $request['interest-rate'];
        $data['maturity_amount'] = $request['maturity-amount'];
        $data['maturity_date'] = date("Y-m-d", strtotime('+ ' . ($request['tenure']) . 'months', strtotime(date("Y/m/d"))));
        break;
      case "monthly-income-scheme":
        $data['ssb_account_number'] = $request['ssbacount'];
        $data['deposite_amount'] = $request['amount'];
        $data['current_balance'] = $request['amount'];
        $data['payment_mode'] = $request['payment-mode'];
        $data['tenure'] = $request['tenure'] / 12;
        $data['maturity_amount'] = $request['maturity-amount'];
        $data['interest_rate'] = $request['interest_rate'];
        $data['maturity_date'] = date("Y-m-d", strtotime('+ ' . ($request['tenure']) . 'months', strtotime(date("Y/m/d"))));
        break;
      case "fixed-deposit":
        $data['deposite_amount'] = $request['amount'];
        $data['current_balance'] = $request['amount'];
        $data['payment_mode'] = $request['payment-mode'];
        $data['tenure'] = $request['tenure'] / 12;
        $data['maturity_amount'] = $request['maturity-amount'];
        $data['interest_rate'] = $request['interest-rate'];
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
        $data['current_balance'] = $request['amount'];
        //$data['due_amount'] = $request['amount'];
        $data['payment_mode'] = $request['payment-mode'];
        $data['maturity_amount'] = $request['maturity-amount'];
        $data['interest_rate'] = $request['interest-rate'];
        $data['maturity_date'] = date("Y-m-d", strtotime('+ ' . ($request['tenure']) . 'months', strtotime(date("Y/m/d"))));
        break;
      case "samraddh-bhavhishya":
        $data['deposite_amount'] = $request['amount'];
        $data['current_balance'] = $request['amount'];
        $data['tenure'] = $request['tenure'] / 12;
        $data['payment_mode'] = $request['payment-mode'];
        $data['maturity_amount'] = $request['maturity-amount'];
        $data['interest_rate'] = $request['interest-rate'];
        $data['maturity_date'] = date("Y-m-d", strtotime('+ ' . ($request['tenure']) . 'months', strtotime(date("Y/m/d"))));
        break;
    }
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
      'created_at' => $request['created_at'],
      //'phone_number' => $request['fn_mobile_number'],
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
      'created_at' => $request['created_at'],
      //'phone_number' => $request['sn_mobile_number'],
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
    $data['created_at'] = $request['created_at'];
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
    $data['investment_id'] = $investmentId;
    $data['transaction_ref_id'] = $satRefId;
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
    switch ($request['payment-mode']) {
      case "0":
        $createTransaction = CommanTransactionsController::createTransaction($satRefId = NULL, 2, $investmentId, $request['memberAutoId'], $branch_id, $branchCode, $amountArraySsb, 0, $amount_deposit_by_name, $request['memberAutoId'], $investmentAccount, $cheque_dd_no = '0', $bank_name = NULL, $branch_name = NULL, $payment_date = date('Y-m-d'), $online_payment_id = NULL, $online_payment_by = NULL, $ssbId, 'CR');
        break;
      case "1":
        $createTransaction = CommanTransactionsController::createTransaction($satRefId = NULL, 2, $investmentId, $request['memberAutoId'], $branch_id, $branchCode, $amountArraySsb, 1, $amount_deposit_by_name, $request['memberAutoId'], $investmentAccount, $request['cheque-number'], $request['bank-name'], $request['branch-name'], $request['cheque-date'], $request['transaction-id'], $online_payment_by = NULL, $ssbId, 'CR');
        break;
      case "2":
        $createTransaction = CommanTransactionsController::createTransaction($satRefId = NULL, 2, $investmentId, $request['memberAutoId'], $branch_id, $branchCode, $amountArraySsb, 3, $amount_deposit_by_name, $request['memberAutoId'], $investmentAccount, 0, NULL, NULL, $request['date'], $request['transaction-id'], $online_payment_by = NULL, $ssbId, 'CR');
        break;
      case "3":
        $createTransaction = CommanTransactionsController::createTransaction(
          $satRefId = NULL,
          2,
          $investmentId,
          $request['memberAutoId'],
          $branch_id,
          $branchCode,
          $amountArraySsb,
          4,
          $amount_deposit_by_name,
          $request['memberAutoId'],
          $investmentAccount,
          0,
          NULL,
          NULL,
          date('Y-m-d'),
          NULL,
          $online_payment_by = NULL,
          $ssbId,
          'CR'
        );
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
    if (count($sAccount) > 0) {
      $ssbAccountNumber = $sAccount[0]->account_no;
      $ssbId = $sAccount[0]->id;
      $ssbBalance = $sAccount[0]->balance;
    } else {
      $ssbAccountNumber = '';
      $ssbId = '';
      $ssbBalance = '';
    }
    $getBranchId = getUserBranchId(Auth::user()->id);
    $branch_id = $getBranchId->id;
    $data['associate_id'] = $request['associatemid'];
    $data['branch_id'] = $branch_id;
    $data['type'] = 6;
    $data['saving_account_id'] = $ssbId;
    $data['account_no'] = $ssbAccountNumber;
    $data['opening_balance'] = $ssbBalance - $request['amount'];
    $data['withdrawal'] = $request['amount'];
    $data['description'] = $ssbAccountNumber . '/Auto debit';
    $data['currency_code'] = 'INR';
    $data['payment_type'] = 'DR';
    $data['payment_mode'] = 3;
    //$data['reference_no'] = '';
    $data['status'] = 1;
    $data['created_at'] = $request['created_at'];
    return $data;
  }
  public function savingAccountTransactionDataNew($request, $investmentId, $type, $plan_name, $account_no)
  {
    $sAccount = $this->getSavingAccountDetails($request['memberAutoId']);
    if (count($sAccount) > 0) {
      $ssbAccountNumber = $sAccount[0]->account_no;
      $ssbId = $sAccount[0]->id;
      $ssbBalance = $sAccount[0]->balance;
    } else {
      $ssbAccountNumber = '';
      $ssbId = '';
      $ssbBalance = '';
    }
    $getBranchId = getUserBranchId(Auth::user()->id);
    $branch_id = $getBranchId->id;
    $data['associate_id'] = $request['associatemid'];
    $data['branch_id'] = $branch_id;
    $data['type'] = 6;
    $data['saving_account_id'] = $ssbId;
    $data['account_no'] = $ssbAccountNumber;
    $data['opening_balance'] = $ssbBalance - $request['amount'];
    $data['withdrawal'] = $request['amount'];
    $data['description'] = 'Payment transfer to ' . $plan_name . '(' . $account_no . ')';
    $data['currency_code'] = 'INR';
    $data['payment_type'] = 'DR';
    $data['payment_mode'] = 3;
    //$data['reference_no'] = '';
    $data['status'] = 1;
    $data['created_at'] = $request['created_at'];
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
    if (count($getId) > 0) {
      return $getId[0]->account_number;
    } else {
      return '';
    }
  }
  /**
   * Show recipt detail after create plan
   * Method: get
   * @param  $id
   * @return  array()  Response
   */
  public function planRecipt($id)
  {

    if (!in_array('Investment Receipt', auth()->user()->getPermissionNames()->toArray())) {
      return redirect()->route('branch.dashboard');
    }
    $data['title'] = 'Investment Plan | Recipt';
    $data['investmentDetails'] = Memberinvestments::with('member', 'investmentNomiees')->with(['plan' => function ($q) {
      $q->withoutGlobalScope(ActiveScope::class);
    }])->where('id', $id)->get();
    // pd($id);
    $data['associateDetails'] = Member::with('savingAccount')->where('id', $data['investmentDetails'][0]->associate_id)->get();

    foreach ($data['investmentDetails'] as $key => $value) {
      $data['associateDetails'] = Member::with('savingAccount')->where('id', $value->associate_id)->get();
      $data['paymentType'] = "";
      if ($value['payment_mode'] == 0) {
        $data['paymentType'] = "Cash";
      } elseif ($value['payment_mode'] == 1) {
        $data['paymentType'] = "Cheque";
      } elseif ($value['payment_mode'] == 2) {
        $data['paymentType'] = "Online Transfer";
      } elseif ($value['payment_mode'] == 3) {
        $data['paymentType'] = "SSB Account";
      }
      $reciptTemplate = $data['investmentDetails'][0]['plan']['CategoryName'][0]->slug;
      //print_r($value->associate_id);die;
    }
    //pd($data);
    return view('templates.branch.investment_management.' . $reciptTemplate . '/' . $reciptTemplate . '-recipt', $data);
  }
  public function deleteTransactions()
  {
    $allInvestments = Memberinvestments::select('id', 'deposite_amount')->whereIn('branch_id', [1, 2, 3, 4])->get();

    foreach ($allInvestments as $key => $investment) {

      Schema::disableForeignKeyConstraints();
      $firstTransactions = DB::table('transactions')->where('transaction_type_id', $investment->id)->orderBy('id', 'ASC')->take(1)->first('id');
      if ($firstTransactions && isset($firstTransactions)) {
        $transactions = DB::table('transactions')->whereNotIn('id', [$firstTransactions->id])->where('transaction_type_id', $investment->id)->delete();
      }
      $firstTransactionsLog = DB::table('transaction_logs')->where('transaction_type_id', $investment->id)->orderBy('id', 'ASC')->take(1)->first('id');
      if ($firstTransactionsLog && isset($firstTransactionsLog)) {
        $transactions_log = DB::table('transaction_logs')->whereNotIn('id', [$firstTransactionsLog->id])->where('transaction_type_id', $investment->id)->delete();
      }
      $firstInvestmentTransactions = DB::table('investment_plan_transactions')->where('investment_id', $investment->id)->orderBy('id', 'ASC')->take(1)->first('id');
      if ($firstInvestmentTransactions && isset($firstInvestmentTransactions)) {
        $investmentTransactions = DB::table('investment_plan_transactions')->whereNotIn('id', [$firstInvestmentTransactions->id])->where('investment_id', $investment->id)->delete();
      }
      $firstDayBook = DB::table('day_books')->where('investment_id', $investment->id)->orderBy('id', 'ASC')->take(1)->first('id');
      if ($firstDayBook && isset($firstDayBook)) {
        $dayBooks = DB::table('day_books')->whereNotIn('id', [$firstDayBook->id])->where('investment_id', $investment->id)->delete();
      }
      $sResult = Memberinvestments::find($investment->id);
      $sData['due_amount'] = 0;
      $sData['current_balance'] = $investment->deposite_amount;
      //$sData['current_balance'] = 0;
      $sResult->update($sData);
      //echo "<pre>"; print_r($investment->id); die;
    }
  }
  public function updateDayBook()
  {
    $transactionsList = Transcation::whereNotIn('transaction_type', [0, 3, 7])->get();
    foreach ($transactionsList as $key => $transactionList) {
      $associatemId = getInvestmentAccount($transactionList->member_id, $transactionList->account_no);
      $data['transaction_type'] = $transactionList->transaction_type;
      $data['transaction_id'] = $transactionList->id;
      $data['investment_id'] = $transactionList->transaction_type_id;
      $data['account_no'] = $transactionList->account_no;
      if ($associatemId && isset($associatemId)) {
        $data['associate_id'] = $associatemId->associate_id;
      } else {
        $data['associate_id'] = NULL;
      }
      $data['member_id'] = $transactionList->member_id;
      $data['opening_balance'] = $transactionList->amount;
      $data['deposit'] = $transactionList->amount;
      $data['withdrawal'] = 0.00;
      if ($transactionList->transaction_type == 1) {
        $data['description'] = 'SSB Account opening';
      } elseif ($transactionList->transaction_type == 2) {
        $planName = transactionPlanName($transactionList->transaction_type_id);
        switch ($planName) {
          case "Saving Account":
            $data['description'] = 'SSB Account opening';
            break;
          case "Samraddh Kanyadhan Yojana":
            $data['description'] = 'SK Account opening';
            break;
          case "Special Samraddh Money Back":
            $data['description'] = 'EMB Account opening';
            break;
          case "Flexi Fixed Deposit":
            $data['description'] = 'FFD Account opening';
            break;
          case "Flexi Recurring Deposit":
            $data['description'] = 'FRD Account opening';
            break;
          case "Samraddh JEEVAN":
            $data['description'] = 'SJ Account opening';
            break;
          case "Daily Deposit":
            $data['description'] = 'SDD Account opening';
            break;
          case "Monthly Income scheme":
            $data['description'] = 'MIS Account opening';
            break;
          case "Fixed Deposit":
            $data['description'] = 'SFD Account opening';
            break;
          case "Recurring Deposit":
            $data['description'] = 'ERD Account opening';
            break;
          case "Samraddh Bhavhishya":
            $data['description'] = 'SB Account opening';
            break;
        }
      } elseif ($transactionList->transaction_type == 4) {
        $planName = transactionPlanName($transactionList->transaction_type_id);
        switch ($planName) {
          case "Saving Account":
            $data['description'] = 'SSB Collection';
            break;
          case "Samraddh Kanyadhan Yojana":
            $data['description'] = 'SK Collection';
            break;
          case "Special Samraddh Money Back":
            $data['description'] = 'EMB Collection';
            break;
          case "Flexi Fixed Deposit":
            $data['description'] = 'FFD Collection';
            break;
          case "Flexi Recurring Deposit":
            $data['description'] = 'FRD Collection';
            break;
          case "Samraddh JEEVAN":
            $data['description'] = 'SJ Collection';
            break;
          case "Daily Deposit":
            $data['description'] = 'SDD Collection';
            break;
          case "Monthly Income scheme":
            $data['description'] = 'MIS Collection';
            break;
          case "Fixed Deposit":
            $data['description'] = 'SFD Collection';
            break;
          case "Recurring Deposit":
            $data['description'] = 'ERD Collection';
            break;
          case "Samraddh Bhavhishya":
            $data['description'] = 'SB Collection';
            break;
        }
      }
      $data['reference_no'] = NULL;
      $data['branch_id'] = $transactionList->branch_id;
      $data['branch_code'] = $transactionList->branch_code;
      $data['amount'] = $transactionList->amount;
      $data['payment_type'] = $transactionList->payment_type;
      $data['currency_code'] = $transactionList->currency_code;
      $data['payment_mode'] = $transactionList->payment_mode;
      $data['saving_account_id'] = $transactionList->saving_account_id;
      $data['cheque_dd_no'] = $transactionList->cheque_dd_no;
      $data['bank_name'] = $transactionList->bank_name;
      $data['branch_name'] = $transactionList->branch_name;
      $data['payment_date'] = $transactionList->created_at;
      $data['online_payment_id'] = $transactionList->online_payment_id;
      $data['online_payment_by'] = $transactionList->online_payment_by;
      $data['amount_deposit_by_name'] = $transactionList->amount_deposit_by_name;
      $data['amount_deposit_by_id'] = $transactionList->amount_deposit_by_id;
      $data['created_by_id'] = $transactionList->created_by_id;
      $data['created_by'] = $transactionList->created_by;
      $data['status'] = $transactionList->status;
      $data['is_deleted'] = $transactionList->is_deleted;
      $data['created_at'] = $transactionList->created_at;
      $data['updated_at'] = $transactionList->updated_at;
      $res = Daybook::create($data);
    }
  }
  public function updateTranactions()
  {
    $dayBooksList = Daybook::select('day_books.*')->leftJoin('transactions', 'day_books.account_no', '=', 'transactions.account_no')->whereNull('transactions.account_no')->get();
    foreach ($dayBooksList as $key => $dayBookList) {
      $data['transaction_type'] = 2;
      $data['transaction_type_id'] = $dayBookList->investment_id;
      $data['account_no'] = $dayBookList->account_no;
      $data['member_id'] = $dayBookList->member_id;
      $data['branch_id'] = $dayBookList->branch_id;
      $data['branch_code'] = $dayBookList->branch_code;
      $data['amount'] = $dayBookList->amount;
      $data['payment_type'] = $dayBookList->payment_type;
      $data['currency_code'] = $dayBookList->currency_code;
      $data['payment_mode'] = $dayBookList->payment_mode;
      $data['saving_account_id'] = $dayBookList->saving_account_id;
      $data['cheque_dd_no'] = $dayBookList->cheque_dd_no;
      $data['bank_name'] = $dayBookList->bank_name;
      $data['branch_name'] = $dayBookList->branch_name;
      $data['payment_date'] = $dayBookList->created_at;
      $data['online_payment_id'] = $dayBookList->online_payment_id;
      $data['online_payment_by'] = $dayBookList->online_payment_by;
      $data['amount_deposit_by_name'] = $dayBookList->amount_deposit_by_name;
      $data['amount_deposit_by_id'] = $dayBookList->amount_deposit_by_id;
      $data['created_by_id'] = $dayBookList->created_by_id;
      $data['created_by'] = $dayBookList->created_by;
      $data['status'] = $dayBookList->status;
      $data['is_deleted'] = $dayBookList->is_deleted;
      $data['created_at'] = $dayBookList->created_at;
      $data['updated_at'] = $dayBookList->updated_at;
      $res = Transcation::create($data);
      $tdata['transaction_id'] = $res->id;
      $tdata['transaction_type'] = 2;
      $tdata['transaction_type_id'] = $dayBookList->investment_id;
      $tdata['account_no'] = $dayBookList->account_no;
      $tdata['member_id'] = $dayBookList->member_id;
      $tdata['branch_id'] = $dayBookList->branch_id;
      $tdata['branch_code'] = $dayBookList->branch_code;
      $tdata['amount'] = $dayBookList->amount;
      $tdata['payment_type'] = $dayBookList->payment_type;
      $tdata['currency_code'] = $dayBookList->currency_code;
      $tdata['payment_mode'] = $dayBookList->payment_mode;
      $tdata['saving_account_id'] = $dayBookList->saving_account_id;
      $tdata['cheque_dd_no'] = $dayBookList->cheque_dd_no;
      $tdata['bank_name'] = $dayBookList->bank_name;
      $tdata['branch_name'] = $dayBookList->branch_name;
      $tdata['payment_date'] = $dayBookList->created_at;
      $tdata['online_payment_id'] = $dayBookList->online_payment_id;
      $tdata['online_payment_by'] = $dayBookList->online_payment_by;
      $tdata['amount_deposit_by_name'] = $dayBookList->amount_deposit_by_name;
      $tdata['amount_deposit_by_id'] = $dayBookList->amount_deposit_by_id;
      $tdata['created_by_id'] = $dayBookList->created_by_id;
      $tdata['created_by'] = $dayBookList->created_by;
      $tdata['status'] = $dayBookList->status;
      $tdata['is_deleted'] = $dayBookList->is_deleted;
      $tdata['created_at'] = $dayBookList->created_at;
      $tdata['updated_at'] = $dayBookList->updated_at;
      $resTranactionLog = TranscationLog::create($tdata);
    }
  }
  public function updateSavingTranactions()
  {
    $saTransactionsList = SavingAccountTranscation::select('saving_account_transctions.*')->leftJoin('transactions', 'saving_account_transctions.account_no', '=', 'transactions.account_no')->whereNull('transactions.account_no')->get();
    foreach ($saTransactionsList as $key => $saTransactionList) {
      $savingAccount = getSavingAccountMemberId($saTransactionList->saving_account_id);
      $mDetails = getMemberDetails($savingAccount->member_id);
      $data['transaction_type'] = 1;
      $data['transaction_type_id'] = $saTransactionList->saving_account_id;
      $data['account_no'] = $saTransactionList->account_no;
      $data['member_id'] = $savingAccount->member_id;
      $data['branch_id'] = $savingAccount->branch_id;
      $data['branch_code'] = $savingAccount->branch_code;
      $data['amount'] = $saTransactionList->deposit;
      $data['payment_type'] = $saTransactionList->payment_type;
      $data['currency_code'] = $saTransactionList->currency_code;
      $data['payment_mode'] = $saTransactionList->payment_mode;
      $data['saving_account_id'] = 0;
      $data['cheque_dd_no'] = 0;
      $data['bank_name'] = NULL;
      $data['branch_name'] = NULL;
      $data['payment_date'] = $saTransactionList->created_at;
      $data['online_payment_id'] = NULL;
      $data['online_payment_by'] = NULL;
      $data['amount_deposit_by_name'] = $mDetails->first_name;
      $data['amount_deposit_by_id'] = $savingAccount->member_id;
      $data['created_by_id'] = $savingAccount->branch_id;
      $data['created_by'] = 2;
      $data['status'] = 1;
      $data['is_deleted'] = 0;
      $data['created_at'] = $saTransactionList->created_at;
      $data['updated_at'] = $saTransactionList->updated_at;
      $res = Transcation::create($data);
      $tdata['transaction_id'] = $res->id;
      $tdata['transaction_type'] = 1;
      $tdata['transaction_type_id'] = $saTransactionList->saving_account_id;
      $tdata['account_no'] = $saTransactionList->account_no;
      $tdata['member_id'] = $savingAccount->member_id;
      $tdata['branch_id'] = $savingAccount->branch_id;
      $tdata['branch_code'] = $savingAccount->branch_code;
      $tdata['amount'] = $saTransactionList->deposit;
      $tdata['payment_type'] = $saTransactionList->payment_type;
      $tdata['currency_code'] = $saTransactionList->currency_code;
      $tdata['payment_mode'] = $saTransactionList->payment_mode;
      $tdata['saving_account_id'] = 0;
      $tdata['cheque_dd_no'] = 0;
      $tdata['bank_name'] = NULL;
      $tdata['branch_name'] = NULL;
      $tdata['payment_date'] = $saTransactionList->created_at;
      $tdata['online_payment_id'] = NULL;
      $tdata['online_payment_by'] = NULL;
      $tdata['amount_deposit_by_name'] = $mDetails->first_name;
      $tdata['amount_deposit_by_id'] = $savingAccount->member_id;
      $tdata['created_by_id'] = $savingAccount->branch_id;
      $tdata['created_by'] = 2;
      $tdata['status'] = 1;
      $tdata['is_deleted'] = 0;
      $tdata['created_at'] = $saTransactionList->created_at;
      $tdata['updated_at'] = $saTransactionList->updated_at;
      $resTranactionLog = TranscationLog::create($tdata);
      $associatemId = getInvestmentAccount($saTransactionList->member_id, $saTransactionList->account_no);
      $dbdata['transaction_id'] = $res->id;
      $dbdata['investment_id'] = $saTransactionList->saving_account_id;
      $dbdata['account_no'] = $saTransactionList->account_no;
      if ($associatemId && isset($associatemId)) {
        $dbdata['associate_id'] = $associatemId->associate_id;
      } else {
        $dbdata['associate_id'] = NULL;
      }
      $dbdata['member_id'] = $savingAccount->member_id;
      $dbdata['opening_balance'] = $saTransactionList->deposit;
      $dbdata['deposit'] = $saTransactionList->deposit;
      $dbdata['withdrawal'] = 0;
      $dbdata['description'] = 'SSB Account opening';
      $dbdata['reference_no'] = NULL;
      $dbdata['branch_id'] = $savingAccount->branch_id;
      $dbdata['branch_code'] = $savingAccount->branch_code;
      $dbdata['amount'] = $saTransactionList->deposit;
      $dbdata['payment_type'] = $saTransactionList->payment_type;
      $dbdata['currency_code'] = $saTransactionList->currency_code;
      $dbdata['payment_mode'] = $saTransactionList->payment_mode;
      $dbdata['saving_account_id'] = 0;
      $dbdata['cheque_dd_no'] = 0;
      $dbdata['bank_name'] = NULL;
      $dbdata['branch_name'] = NULL;
      $dbdata['payment_date'] = $saTransactionList->created_at;
      $dbdata['online_payment_id'] = NULL;
      $dbdata['online_payment_by'] = NULL;
      $dbdata['amount_deposit_by_name'] = $mDetails->first_name;
      $dbdata['amount_deposit_by_id'] = $savingAccount->member_id;
      $dbdata['created_by_id'] = $savingAccount->branch_id;
      $dbdata['created_by'] = 2;
      $dbdata['status'] = 1;
      $dbdata['is_deleted'] = 0;
      $dbdata['created_at'] = $saTransactionList->created_at;
      $dbdata['updated_at'] = $saTransactionList->updated_at;
      $resDaybook = Daybook::create($dbdata);
      //echo "<pre>"; print_r($dbdata); die;
    }
  }
  /**
   * Show commission detail after create plan
   * Method: get
   * @param  $id
   * @return  array()  Response
   */
  public function investmentCommission($id)
  {


    if (!in_array('Investment Commission', auth()->user()->getPermissionNames()->toArray())) {
      return redirect()->route('branch.dashboard');
    }

    $data['title'] = 'Investment Plan | Commission';
    $data['investment'] = getInvestmentDetails($id);
    $data['investmentId'] = $id;
    return view('templates.branch.investment_management.commission', $data);
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
      $investmentId = $request->id;
      $mid = Member::where('associate_no', '9999999')->first('id');
      $data = AssociateCommission::where('member_id', '!=', $mid->id)->where('type', '>', 2)->where('type_id', $investmentId)->orderby('id', 'DESC')->get();
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
  public function updatedateform()
  {
    $data['title'] = "Update investment date";
    return view('templates.branch.investment_management.updatedate', $data);
  }
  public function updatedate(Request $request)
  {
    $acc_n = $request->acc_n;
    $lastdate = $request->lastdate;
    $newdate = $request->newdate;
    $amount = $request->amount;

    $dayBook = Daybook::where('account_no', $acc_n)->whereDate('created_at', '=', $lastdate)->first();

    if ($dayBook) {
      //echo $acc_n.'--'.$lastdate; die;
      $investId = $dayBook->investment_id;
      DB::beginTransaction();
      try {
        $updateDayBook = Daybook::where('account_no', $acc_n)->where('deposit', $amount)->whereDate('created_at', '=', $lastdate)->update(['created_at' => $newdate]);
        $updateT = Transcation::where('transaction_type_id', $investId)->where('amount', $amount)->whereDate('created_at', '=', $lastdate)->update(['created_at' => $newdate]);
        $investIdTL = TranscationLog::where('transaction_type_id', $investId)->where('amount', $amount)->whereDate('created_at', '=', $lastdate)->update(['created_at' => $newdate]);

        $time = strtotime($newdate);
        $month = date("m", $time);
        $updateIPT = Investmentplantransactions::where('investment_id', $investId)->where('deposite_amount', $amount)->whereDate('created_at', '=', $lastdate)->update(['deposite_date' => $newdate, 'created_at' => $newdate, 'deposite_month' => $month]);
        DB::commit();
      } catch (\Exception $ex) {
        DB::rollback();
        return back()->with('alert', $ex->getMessage());
      }
      return back()->with('success', 'Saved Successfully!');
    } else {
      return back()->with('alert', 'Record not found!');
    }
  }
  public function adjustStationaryCharges()
  {
    DB::beginTransaction();
    try {
      $memberArray = array();
      $memberInvestments = Member::with('associateInvestment') /*->offset(800)->limit(1800)*/->where('id', 504)->get();
      $planArray = array(3, 6, 8);
      foreach ($memberInvestments as $key => $memberInvestment) {
        $countInvestment = count($memberInvestment['associateInvestment']);
        if ($countInvestment > 1) {
          $dateTimeArary = array();
          for ($i = 1; $i <= ($countInvestment - 1); $i++) {
            $entryTime = date("H:i:s");
            Session::put('created_at', date("Y-m-d " . $entryTime . "", strtotime(convertDate($memberInvestment['associateInvestment'][$i]->created_at))));

            $vno = NULL;
            $branch_id = $memberInvestment['associateInvestment'][$i]->branch_id;
            $type = 3;
            $sub_type = 35;
            $type_id = $memberInvestment['associateInvestment'][$i]->id;
            $type_transaction_id = $memberInvestment['associateInvestment'][$i]->id;
            $associate_id = NULL;
            $member_id = $memberInvestment['associateInvestment'][$i]->member_id;
            $branch_id_to = NULL;
            $branch_id_from = NULL;
            $opening_balance = 50;
            $amount = 50;
            $closing_balance = 50;
            $description = 'Stationary charges on investment plan registration';
            $description_dr = 'Stationary charges on investment plan registration';
            $description_cr = 'Stationary charges on investment plan registration';
            $payment_type = 'CR';
            $payment_mode = 0;
            $currency_code = 'INR';
            $amount_to_id = NULL;
            $amount_to_name = NULL;
            $amount_from_id = NULL;
            $amount_from_name = NULL;
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
            $transction_date = NULL;
            $entry_date = NULL;
            $entry_time = NULL;
            $created_by = 1;
            $created_by_id = 1;
            $is_contra = NULL;
            $contra_id = NULL;
            $created_at = $memberInvestment['associateInvestment'][$i]->created_at;
            $bank_id = NULL;
            $bank_ac_id = NULL;
            $transction_bank_to_name = NULL;
            $transction_bank_to_ac_no = NULL;
            $transction_bank_to_branch = NULL;
            $transction_bank_to_ifsc = NULL;
            $dayBookRef = CommanController::createBranchDayBookReference($amount);
            if ($memberInvestment['associateInvestment'][$i]->plan_id == 1) {
              $checkRd = Memberinvestments::where('id', '<', $memberInvestment['associateInvestment'][$i]->id)->where('member_id', $memberInvestment->id)->where('plan_id', 10)->first();

              if ($checkRd) {

                if ($checkRd->deposite_amount != 500 && $memberInvestment['associate_senior_code'] != 9999999) {
                  array_push($memberArray, $memberInvestment->member_id);
                  $data['investment_id'] = $memberInvestment['associateInvestment'][$i]->id;
                  $data['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($memberInvestment['associateInvestment'][$i]->created_at)));
                  $satRef = TransactionReferences::create($data);
                  $satRefId = $satRef->id;
                  $amountArraySsb = array('1' => (50));
                  $ssbCreateTran = CommanController::createTransaction($satRefId, 18, $memberInvestment['associateInvestment'][$i]->id, $memberInvestment['associateInvestment'][$i]->member_id, $memberInvestment['associateInvestment'][$i]->branch_id, getBranchCode($memberInvestment['associateInvestment'][$i]->branch_id)->branch_code, $amountArraySsb, 0, NULL, $memberInvestment['associateInvestment'][$i]->member_id, $memberInvestment['associateInvestment'][$i]->account_number, NULL, NULL, NULL, date("Y-m-d", strtotime(convertDate($memberInvestment['associateInvestment'][$i]->created_at))), NULL, NULL, NULL, 'CR');

                  $createDayBook = CommanController::createDayBook($ssbCreateTran, $satRefId, 19, $memberInvestment['associateInvestment'][$i]->id, NULL, $memberInvestment['associateInvestment'][$i]->member_id, 50, 50, $withdrawal = 0, 'Stationary charges on investment plan registration', $memberInvestment['associateInvestment'][$i]->account_number, $memberInvestment['associateInvestment'][$i]->branch_id, getBranchCode($memberInvestment['associateInvestment'][$i]->branch_id)->branch_code, $amountArraySsb, 0, NULL, $memberInvestment['associateInvestment'][$i]->member_id, $memberInvestment['associateInvestment'][$i]->account_number, 50, NULL, NULL, date("Y-m-d", strtotime(convertDate($memberInvestment['associateInvestment'][$i]->created_at))), NULL, NULL, NULL, 'CR');

                  $allTransaction = CommanController::createAllTransaction($dayBookRef, $branch_id, $bank_id, $bank_ac_id, 4, 86, 112, 113, NULL, $type, $sub_type, $type_id, $type_transaction_id, $associate_id, $member_id, $branch_id_to, $branch_id_from, $amount, $amount, $amount, $description, 'CR', $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $memberInvestment['associateInvestment'][$i]->created_at);
                  $allTransaction = CommanController::createAllTransaction($dayBookRef, $branch_id, $bank_id, $bank_ac_id, 2, 10, 28, 71, NULL, $type, $sub_type, $type_id, $type_transaction_id, $associate_id, $member_id, $branch_id_to, $branch_id_from, $amount, $amount, $amount, $description, 'CR', $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $memberInvestment['associateInvestment'][$i]->created_at);
                  $branchDayBook = CommanController::createBranchDayBook($dayBookRef, $branch_id, $type, $sub_type, $type_id, $type_transaction_id, $associate_id, $member_id, $branch_id_to, $branch_id_from, $amount, $amount, $amount, $description, $description_dr, $description_cr, 'CR', $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $is_contra, $contra_id, $created_at, 0);
                  $memberTransaction = CommanController::createMemberTransaction($dayBookRef, $type, $sub_type, $type_id, $type_transaction_id, $associate_id, $member_id, $branch_id, $bank_id, $bank_ac_id, $amount, $description, 'DR', $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at);
                  $this->updateBranchCashCr($memberInvestment['associateInvestment'][$i]->branch_id, $memberInvestment['associateInvestment'][$i]->created_at, 50, 0);
                  $this->updateBranchClosingCashCr($memberInvestment['associateInvestment'][$i]->branch_id, $memberInvestment['associateInvestment'][$i]->created_at, 50, 0);
                }
              } else {
                array_push($memberArray, $memberInvestment->member_id);
                $data['investment_id'] = $memberInvestment['associateInvestment'][$i]->id;
                $data['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($memberInvestment['associateInvestment'][$i]->created_at)));
                $satRef = TransactionReferences::create($data);
                $satRefId = $satRef->id;
                $amountArraySsb = array('1' => (50));
                $ssbCreateTran = CommanController::createTransaction($satRefId, 18, $memberInvestment['associateInvestment'][$i]->id, $memberInvestment['associateInvestment'][$i]->member_id, $memberInvestment['associateInvestment'][$i]->branch_id, getBranchCode($memberInvestment['associateInvestment'][$i]->branch_id)->branch_code, $amountArraySsb, 0, NULL, $memberInvestment['associateInvestment'][$i]->member_id, $memberInvestment['associateInvestment'][$i]->account_number, NULL, NULL, NULL, date("Y-m-d", strtotime(convertDate($memberInvestment['associateInvestment'][$i]->created_at))), NULL, NULL, NULL, 'CR');
                $createDayBook = CommanController::createDayBook($ssbCreateTran, $satRefId, 19, $memberInvestment['associateInvestment'][$i]->id, NULL, $memberInvestment['associateInvestment'][$i]->member_id, 50, 50, $withdrawal = 0, 'Stationary charges on investment plan registration', $memberInvestment['associateInvestment'][$i]->account_number, $memberInvestment['associateInvestment'][$i]->branch_id, getBranchCode($memberInvestment['associateInvestment'][$i]->branch_id)->branch_code, $amountArraySsb, 0, NULL, $memberInvestment['associateInvestment'][$i]->member_id, $memberInvestment['associateInvestment'][$i]->account_number, 50, NULL, NULL, date("Y-m-d", strtotime(convertDate($memberInvestment['associateInvestment'][$i]->created_at))), NULL, NULL, NULL, 'CR');
                $allTransaction = CommanController::createAllTransaction($dayBookRef, $branch_id, $bank_id, $bank_ac_id, 4, 86, 112, 113, NULL, $type, $sub_type, $type_id, $type_transaction_id, $associate_id, $member_id, $branch_id_to, $branch_id_from, $amount, $amount, $amount, $description, 'CR', $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $memberInvestment['associateInvestment'][$i]->created_at);
                $allTransaction = CommanController::createAllTransaction($dayBookRef, $branch_id, $bank_id, $bank_ac_id, 2, 10, 28, 71, NULL, $type, $sub_type, $type_id, $type_transaction_id, $associate_id, $member_id, $branch_id_to, $branch_id_from, $amount, $amount, $amount, $description, 'CR', $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $memberInvestment['associateInvestment'][$i]->created_at);
                $branchDayBook = CommanController::createBranchDayBook($dayBookRef, $branch_id, $type, $sub_type, $type_id, $type_transaction_id, $associate_id, $member_id, $branch_id_to, $branch_id_from, $amount, $amount, $amount, $description, $description_dr, $description_cr, 'CR', $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $is_contra, $contra_id, $created_at, 0);
                $memberTransaction = CommanController::createMemberTransaction($dayBookRef, $type, $sub_type, $type_id, $type_transaction_id, $associate_id, $member_id, $branch_id, $bank_id, $bank_ac_id, $amount, $description, 'DR', $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at);
                $this->updateBranchCashCr($memberInvestment['associateInvestment'][$i]->branch_id, $memberInvestment['associateInvestment'][$i]->created_at, 50, 0);
                $this->updateBranchClosingCashCr($memberInvestment['associateInvestment'][$i]->branch_id, $memberInvestment['associateInvestment'][$i]->created_at, 50, 0);
              }
            } elseif (in_array($memberInvestment['associateInvestment'][$i]->plan_id, $planArray)) {
              $checkEntry = Memberinvestments::where('id', '<', $memberInvestment['associateInvestment'][$i]->id)->where('member_id', $memberInvestment->id)->where('plan_id', 1)->first();

              if ($checkEntry) {
                array_push($memberArray, $memberInvestment->member_id);
                $checkRd = Memberinvestments::where('id', '<', $checkEntry->id)->where('member_id', $memberInvestment->id)->where('plan_id', 10)->first();
                if ($checkRd) {
                  $data['investment_id'] = $memberInvestment['associateInvestment'][$i]->id;
                  $data['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($memberInvestment['associateInvestment'][$i]->created_at)));
                  $satRef = TransactionReferences::create($data);
                  $satRefId = $satRef->id;
                  $amountArraySsb = array('1' => (50));
                  $ssbCreateTran = CommanController::createTransaction($satRefId, 18, $memberInvestment['associateInvestment'][$i]->id, $memberInvestment['associateInvestment'][$i]->member_id, $memberInvestment['associateInvestment'][$i]->branch_id, getBranchCode($memberInvestment['associateInvestment'][$i]->branch_id)->branch_code, $amountArraySsb, 0, NULL, $memberInvestment['associateInvestment'][$i]->member_id, $memberInvestment['associateInvestment'][$i]->account_number, NULL, NULL, NULL, date("Y-m-d", strtotime(convertDate($memberInvestment['associateInvestment'][$i]->created_at))), NULL, NULL, NULL, 'CR');
                  $createDayBook = CommanController::createDayBook($ssbCreateTran, $satRefId, 19, $memberInvestment['associateInvestment'][$i]->id, NULL, $memberInvestment['associateInvestment'][$i]->member_id, 50, 50, $withdrawal = 0, 'Stationary charges on investment plan registration', $memberInvestment['associateInvestment'][$i]->account_number, $memberInvestment['associateInvestment'][$i]->branch_id, getBranchCode($memberInvestment['associateInvestment'][$i]->branch_id)->branch_code, $amountArraySsb, 0, NULL, $memberInvestment['associateInvestment'][$i]->member_id, $memberInvestment['associateInvestment'][$i]->account_number, 50, NULL, NULL, date("Y-m-d", strtotime(convertDate($memberInvestment['associateInvestment'][$i]->created_at))), NULL, NULL, NULL, 'CR');
                  $allTransaction = CommanController::createAllTransaction($dayBookRef, $branch_id, $bank_id, $bank_ac_id, 4, 86, 112, 113, NULL, $type, $sub_type, $type_id, $type_transaction_id, $associate_id, $member_id, $branch_id_to, $branch_id_from, $amount, $amount, $amount, $description, 'CR', $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $memberInvestment['associateInvestment'][$i]->created_at);
                  $allTransaction = CommanController::createAllTransaction($dayBookRef, $branch_id, $bank_id, $bank_ac_id, 2, 10, 28, 71, NULL, $type, $sub_type, $type_id, $type_transaction_id, $associate_id, $member_id, $branch_id_to, $branch_id_from, $amount, $amount, $amount, $description, 'CR', $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $memberInvestment['associateInvestment'][$i]->created_at);
                  $branchDayBook = CommanController::createBranchDayBook($dayBookRef, $branch_id, $type, $sub_type, $type_id, $type_transaction_id, $associate_id, $member_id, $branch_id_to, $branch_id_from, $amount, $amount, $amount, $description, $description_dr, $description_cr, 'CR', $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $is_contra, $contra_id, $created_at, 0);
                  $memberTransaction = CommanController::createMemberTransaction($dayBookRef, $type, $sub_type, $type_id, $type_transaction_id, $associate_id, $member_id, $branch_id, $bank_id, $bank_ac_id, $amount, $description, 'DR', $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at);
                  $this->updateBranchCashCr($memberInvestment['associateInvestment'][$i]->branch_id, $memberInvestment['associateInvestment'][$i]->created_at, 50, 0);
                  $this->updateBranchClosingCashCr($memberInvestment['associateInvestment'][$i]->branch_id, $memberInvestment['associateInvestment'][$i]->created_at, 50, 0);
                }
              } else {
                array_push($memberArray, $memberInvestment->member_id);
                $data['investment_id'] = $memberInvestment['associateInvestment'][$i]->id;
                $data['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($memberInvestment['associateInvestment'][$i]->created_at)));
                $satRef = TransactionReferences::create($data);
                $satRefId = $satRef->id;
                $amountArraySsb = array('1' => (50));
                $ssbCreateTran = CommanController::createTransaction($satRefId, 18, $memberInvestment['associateInvestment'][$i]->id, $memberInvestment['associateInvestment'][$i]->member_id, $memberInvestment['associateInvestment'][$i]->branch_id, getBranchCode($memberInvestment['associateInvestment'][$i]->branch_id)->branch_code, $amountArraySsb, 0, NULL, $memberInvestment['associateInvestment'][$i]->member_id, $memberInvestment['associateInvestment'][$i]->account_number, NULL, NULL, NULL, date("Y-m-d", strtotime(convertDate($memberInvestment['associateInvestment'][$i]->created_at))), NULL, NULL, NULL, 'CR');
                $createDayBook = CommanController::createDayBook($ssbCreateTran, $satRefId, 19, $memberInvestment['associateInvestment'][$i]->id, NULL, $memberInvestment['associateInvestment'][$i]->member_id, 50, 50, $withdrawal = 0, 'Stationary charges on investment plan registration', $memberInvestment['associateInvestment'][$i]->account_number, $memberInvestment['associateInvestment'][$i]->branch_id, getBranchCode($memberInvestment['associateInvestment'][$i]->branch_id)->branch_code, $amountArraySsb, 0, NULL, $memberInvestment['associateInvestment'][$i]->member_id, $memberInvestment['associateInvestment'][$i]->account_number, 50, NULL, NULL, date("Y-m-d", strtotime(convertDate($memberInvestment['associateInvestment'][$i]->created_at))), NULL, NULL, NULL, 'CR');
                $allTransaction = CommanController::createAllTransaction($dayBookRef, $branch_id, $bank_id, $bank_ac_id, 4, 86, 112, 113, NULL, $type, $sub_type, $type_id, $type_transaction_id, $associate_id, $member_id, $branch_id_to, $branch_id_from, $amount, $amount, $amount, $description, 'CR', $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $memberInvestment['associateInvestment'][$i]->created_at);
                $allTransaction = CommanController::createAllTransaction($dayBookRef, $branch_id, $bank_id, $bank_ac_id, 2, 10, 28, 71, NULL, $type, $sub_type, $type_id, $type_transaction_id, $associate_id, $member_id, $branch_id_to, $branch_id_from, $amount, $amount, $amount, $description, 'CR', $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $memberInvestment['associateInvestment'][$i]->created_at);
                $branchDayBook = CommanController::createBranchDayBook($dayBookRef, $branch_id, $type, $sub_type, $type_id, $type_transaction_id, $associate_id, $member_id, $branch_id_to, $branch_id_from, $amount, $amount, $amount, $description, $description_dr, $description_cr, 'CR', $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $is_contra, $contra_id, $created_at, 0);
                $memberTransaction = CommanController::createMemberTransaction($dayBookRef, $type, $sub_type, $type_id, $type_transaction_id, $associate_id, $member_id, $branch_id, $bank_id, $bank_ac_id, $amount, $description, 'DR', $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at);
                $this->updateBranchCashCr($memberInvestment['associateInvestment'][$i]->branch_id, $memberInvestment['associateInvestment'][$i]->created_at, 50, 0);
                $this->updateBranchClosingCashCr($memberInvestment['associateInvestment'][$i]->branch_id, $memberInvestment['associateInvestment'][$i]->created_at, 50, 0);
              }
            } elseif ($memberInvestment['associateInvestment'][$i]->plan_id == 10) {
              if ($memberInvestment['associateInvestment'][$i]->deposite_amount != 500 && $memberInvestment['associate_senior_code'] != 9999999) {
                array_push($memberArray, $memberInvestment->member_id);
                $data['investment_id'] = $memberInvestment['associateInvestment'][$i]->id;
                $data['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($memberInvestment['associateInvestment'][$i]->created_at)));
                $satRef = TransactionReferences::create($data);
                $satRefId = $satRef->id;
                $amountArraySsb = array('1' => (50));
                $ssbCreateTran = CommanController::createTransaction($satRefId, 18, $memberInvestment['associateInvestment'][$i]->id, $memberInvestment['associateInvestment'][$i]->member_id, $memberInvestment['associateInvestment'][$i]->branch_id, getBranchCode($memberInvestment['associateInvestment'][$i]->branch_id)->branch_code, $amountArraySsb, 0, NULL, $memberInvestment['associateInvestment'][$i]->member_id, $memberInvestment['associateInvestment'][$i]->account_number, NULL, NULL, NULL, date("Y-m-d", strtotime(convertDate($memberInvestment['associateInvestment'][$i]->created_at))), NULL, NULL, NULL, 'CR');
                $createDayBook = CommanController::createDayBook($ssbCreateTran, $satRefId, 19, $memberInvestment['associateInvestment'][$i]->id, NULL, $memberInvestment['associateInvestment'][$i]->member_id, 50, 50, $withdrawal = 0, 'Stationery charges on investment plan registration', $memberInvestment['associateInvestment'][$i]->account_number, $memberInvestment['associateInvestment'][$i]->branch_id, getBranchCode($memberInvestment['associateInvestment'][$i]->branch_id)->branch_code, $amountArraySsb, 0, NULL, $memberInvestment['associateInvestment'][$i]->member_id, $memberInvestment['associateInvestment'][$i]->account_number, 50, NULL, NULL, date("Y-m-d", strtotime(convertDate($memberInvestment['associateInvestment'][$i]->created_at))), NULL, NULL, NULL, 'CR');
                $allTransaction = CommanController::createAllTransaction($dayBookRef, $branch_id, $bank_id, $bank_ac_id, 4, 86, 112, 113, NULL, $type, $sub_type, $type_id, $type_transaction_id, $associate_id, $member_id, $branch_id_to, $branch_id_from, $amount, $amount, $amount, $description, 'CR', $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $memberInvestment['associateInvestment'][$i]->created_at);
                $allTransaction = CommanController::createAllTransaction($dayBookRef, $branch_id, $bank_id, $bank_ac_id, 2, 10, 28, 71, NULL, $type, $sub_type, $type_id, $type_transaction_id, $associate_id, $member_id, $branch_id_to, $branch_id_from, $amount, $amount, $amount, $description, 'CR', $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $memberInvestment['associateInvestment'][$i]->created_at);
                $branchDayBook = CommanController::createBranchDayBook($dayBookRef, $branch_id, $type, $sub_type, $type_id, $type_transaction_id, $associate_id, $member_id, $branch_id_to, $branch_id_from, $amount, $amount, $amount, $description, $description_dr, $description_cr, 'CR', $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $is_contra, $contra_id, $created_at, 0);
                $memberTransaction = CommanController::createMemberTransaction($dayBookRef, $type, $sub_type, $type_id, $type_transaction_id, $associate_id, $member_id, $branch_id, $bank_id, $bank_ac_id, $amount, $description, 'DR', $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at);
                $this->updateBranchCashCr($memberInvestment['associateInvestment'][$i]->branch_id, $memberInvestment['associateInvestment'][$i]->created_at, 50, 0);
                $this->updateBranchClosingCashCr($memberInvestment['associateInvestment'][$i]->branch_id, $memberInvestment['associateInvestment'][$i]->created_at, 50, 0);
              }
            } else {
              array_push($memberArray, $memberInvestment->member_id);
              $data['investment_id'] = $memberInvestment['associateInvestment'][$i]->id;
              $data['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($memberInvestment['associateInvestment'][$i]->created_at)));
              $satRef = TransactionReferences::create($data);
              $satRefId = $satRef->id;
              $amountArraySsb = array('1' => (50));
              $ssbCreateTran = CommanController::createTransaction($satRefId, 18, $memberInvestment['associateInvestment'][$i]->id, $memberInvestment['associateInvestment'][$i]->member_id, $memberInvestment['associateInvestment'][$i]->branch_id, getBranchCode($memberInvestment['associateInvestment'][$i]->branch_id)->branch_code, $amountArraySsb, 0, NULL, $memberInvestment['associateInvestment'][$i]->member_id, $memberInvestment['associateInvestment'][$i]->account_number, NULL, NULL, NULL, date("Y-m-d", strtotime(convertDate($memberInvestment['associateInvestment'][$i]->created_at))), NULL, NULL, NULL, 'CR');
              $createDayBook = CommanController::createDayBook($ssbCreateTran, $satRefId, 19, $memberInvestment['associateInvestment'][$i]->id, NULL, $memberInvestment['associateInvestment'][$i]->member_id, 50, 50, $withdrawal = 0, 'Stationery charges on investment plan registration', $memberInvestment['associateInvestment'][$i]->account_number, $memberInvestment['associateInvestment'][$i]->branch_id, getBranchCode($memberInvestment['associateInvestment'][$i]->branch_id)->branch_code, $amountArraySsb, 0, NULL, $memberInvestment['associateInvestment'][$i]->member_id, $memberInvestment['associateInvestment'][$i]->account_number, 50, NULL, NULL, date("Y-m-d", strtotime(convertDate($memberInvestment['associateInvestment'][$i]->created_at))), NULL, NULL, NULL, 'CR');
              $allTransaction = CommanController::createAllTransaction($dayBookRef, $branch_id, $bank_id, $bank_ac_id, 4, 86, 112, 113, NULL, $type, $sub_type, $type_id, $type_transaction_id, $associate_id, $member_id, $branch_id_to, $branch_id_from, $amount, $amount, $amount, $description, 'CR', $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $memberInvestment['associateInvestment'][$i]->created_at);
              $allTransaction = CommanController::createAllTransaction($dayBookRef, $branch_id, $bank_id, $bank_ac_id, 2, 10, 28, 71, NULL, $type, $sub_type, $type_id, $type_transaction_id, $associate_id, $member_id, $branch_id_to, $branch_id_from, $amount, $amount, $amount, $description, 'CR', $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $memberInvestment['associateInvestment'][$i]->created_at);
              $branchDayBook = CommanController::createBranchDayBook($dayBookRef, $branch_id, $type, $sub_type, $type_id, $type_transaction_id, $associate_id, $member_id, $branch_id_to, $branch_id_from, $amount, $amount, $amount, $description, $description_dr, $description_cr, 'CR', $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $is_contra, $contra_id, $created_at, 0);
              $memberTransaction = CommanController::createMemberTransaction($dayBookRef, $type, $sub_type, $type_id, $type_transaction_id, $associate_id, $member_id, $branch_id, $bank_id, $bank_ac_id, $amount, $description, 'DR', $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at);
              $this->updateBranchCashCr($memberInvestment['associateInvestment'][$i]->branch_id, $memberInvestment['associateInvestment'][$i]->created_at, 50, 0);
              $this->updateBranchClosingCashCr($memberInvestment['associateInvestment'][$i]->branch_id, $memberInvestment['associateInvestment'][$i]->created_at, 50, 0);
            }
          }
        }
      }
      $ssbString = implode(",", $memberArray);
      if ($memberArray) {
        echo 'These Member Ids ' . $ssbString . ' distributed 50 rupees on investment.';
      } else {
        echo "No Record Found.";
      }
      DB::commit();
    } catch (\Exception $ex) {
      DB::rollback();
      return back()->with('alert', $ex->getMessage());
    }
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
      $description = 'Stationary charges on investment plan registration';
      $description_dr = 'Stationary charges on investment plan registration';
      $description_cr = 'Stationary charges on investment plan registration';
      $payment_type = 'CR';
      $payment_mode = 0;
      $currency_code = 'INR';
      $amount_to_id = NULL;
      $amount_to_name = NULL;
      $amount_from_id = NULL;
      $amount_from_name = NULL;
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
      $detail = getBranchState($memberInvestments['branch']->name);
      $globaldate = checkMonthAvailability(date('d'), date('m'), date('Y'), $memberInvestments['branch']->state_id);
      $getHeadSetting = \App\Models\HeadSetting::where('head_id', 122)->first();
      $stateid = $memberInvestments['branch']->state_id;

      $getGstSetting = \App\Models\GstSetting::where('state_id', $stateid)->where('applicable_date', '<=', $globaldate)->exists();
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
                        $satRef = TransactionReferences::create($data);
                        $satRefId = $satRef->id;
                        $amountArraySsb = array('1'=>(50));
                        $ssbCreateTran = CommanController::createTransaction($satRefId,18,$memberInvestments->id,$memberInvestments->member_id,$memberInvestments->branch_id,getBranchCode($memberInvestments->branch_id)->branch_code,$amountArraySsb,0,NULL,$memberInvestments->member_id,$memberInvestments->account_number,NULL,NULL,NULL,date("Y-m-d", strtotime(convertDate($memberInvestments->created_at))),NULL,NULL,NULL,'CR');
                        $createDayBook = CommanController::createDayBook($ssbCreateTran,$satRefId,19,$memberInvestments->id,NULL,$memberInvestments->member_id,50,50,$withdrawal=0,'Stationary charges on investment plan registration',$memberInvestments->account_number,$memberInvestments->branch_id,getBranchCode($memberInvestments->branch_id)->branch_code,$amountArraySsb,0,NULL,$memberInvestments->member_id,$memberInvestments->account_number,50,NULL,NULL,date("Y-m-d", strtotime(convertDate($memberInvestments->created_at))),NULL,NULL,NULL,'CR');
                        $allTransaction = CommanController::createAllTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,4,86,121,122,NULL,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$amount,$amount,$amount,$description,'CR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$memberInvestments->created_at);  
                        $allTransaction = CommanController::createAllTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,2,10,28,71,NULL,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$amount,$amount,$amount,$description,'CR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$memberInvestments->created_at);
                        $branchDayBook = CommanController::createBranchDayBook($dayBookRef,$branch_id,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$amount,$amount,$amount,$description,$description_dr,$description_cr,'CR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$is_contra,$contra_id,$created_at,0);
                        $memberTransaction = CommanController::createMemberTransaction($dayBookRef,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id,$bank_id,$bank_ac_id,$amount,$description,'CR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at);    
                        $this->updateBranchCashCr($memberInvestments->branch_id,$memberInvestments->created_at,50,0);
                        $this->updateBranchCashCr($memberInvestments->branch_id,$memberInvestments->created_at,50,0);
                    }      
                }else{
                    $data['investment_id']=$memberInvestments->id;
                    $data['created_at']=date("Y-m-d ".$entryTime."", strtotime(convertDate($memberInvestments->created_at)));
                    $satRef = TransactionReferences::create($data);
                    $satRefId = $satRef->id;
                    $amountArraySsb = array('1'=>(50));
                    $ssbCreateTran = CommanController::createTransaction($satRefId,18,$memberInvestments->id,$memberInvestments->member_id,$memberInvestments->branch_id,getBranchCode($memberInvestments->branch_id)->branch_code,$amountArraySsb,0,NULL,$memberInvestments->member_id,$memberInvestments->account_number,NULL,NULL,NULL,date("Y-m-d", strtotime(convertDate($memberInvestments->created_at))),NULL,NULL,NULL,'CR');
                    $createDayBook = CommanController::createDayBook($ssbCreateTran,$satRefId,19,$memberInvestments->id,NULL,$memberInvestments->member_id,50,50,$withdrawal=0,'Stationary charges on investment plan registration',$memberInvestments->account_number,$memberInvestments->branch_id,getBranchCode($memberInvestments->branch_id)->branch_code,$amountArraySsb,0,NULL,$memberInvestments->member_id,$memberInvestments->account_number,50,NULL,NULL,date("Y-m-d", strtotime(convertDate($memberInvestments->created_at))),NULL,NULL,NULL,'CR');
                    $allTransaction = CommanController::createAllTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,4,86,121,122,NULL,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$amount,$amount,$amount,$description,'CR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$memberInvestments->created_at);
                    $allTransaction = CommanController::createAllTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,2,10,28,71,NULL,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$amount,$amount,$amount,$description,'CR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$memberInvestments->created_at);
                    $branchDayBook = CommanController::createBranchDayBook($dayBookRef,$branch_id,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$amount,$amount,$amount,$description,$description_dr,$description_cr,'CR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$is_contra,$contra_id,$created_at,0);
                    $memberTransaction = CommanController::createMemberTransaction($dayBookRef,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id,$bank_id,$bank_ac_id,$amount,$description,'CR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at);
                    $this->updateBranchCashCr($memberInvestments->branch_id,$memberInvestments->created_at,50,0);
                    $this->updateBranchCashCr($memberInvestments->branch_id,$memberInvestments->created_at,50,0);
                }
            }else*/
      if (in_array($memberInvestments->plan_id, $planArray)) {
        $checkEntry = Memberinvestments::where('id', '<', $memberInvestments->id)->where('plan_id', 1)->first();

        if ($checkEntry) {
          $checkRd = Memberinvestments::where('id', '<', $checkEntry->id)->where('plan_id', 10)->first();
          if ($checkRd) {
            $data['investment_id'] = $memberInvestments->id;
            $data['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($memberInvestments->created_at)));
            $satRef = TransactionReferences::create($data);
            $satRefId = $satRef->id;
            $amountArraySsb = array('1' => (50));
            $ssbCreateTran = CommanController::createTransaction($satRefId, 18, $memberInvestments->id, $memberInvestments->member_id, $memberInvestments->branch_id, getBranchCode($memberInvestments->branch_id)->branch_code, $amountArraySsb, 0, NULL, $memberInvestments->member_id, $memberInvestments->account_number, NULL, NULL, NULL, date("Y-m-d", strtotime(convertDate($memberInvestments->created_at))), NULL, NULL, NULL, 'CR');
            $createDayBook = CommanController::createDayBook($ssbCreateTran, $satRefId, 19, $memberInvestments->id, NULL, $memberInvestments->member_id, 50, 50, $withdrawal = 0, 'Stationary charges on investment plan registration', $memberInvestments->account_number, $memberInvestments->branch_id, getBranchCode($memberInvestments->branch_id)->branch_code, $amountArraySsb, 0, NULL, $memberInvestments->member_id, $memberInvestments->account_number, 50, NULL, NULL, date("Y-m-d", strtotime(convertDate($memberInvestments->created_at))), NULL, NULL, NULL, 'CR');
            $allTransaction = CommanController::createAllHeadTransaction($dayBookRef, $branch_id, $bank_id, $bank_ac_id, 122, $type, $sub_type, $type_id, $createDayBook, $associate_id, $member_id, $branch_id_to, $branch_id_from, $amount, $amount, $amount, $description, 'CR', $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $jv_unique_id = NULL, $v_no, $v_date, $ssb_account_id_from, $ssb_account_id_to = NULL, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $cheque_type = NULL, $cheque_id = NULL, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_from_id = NULL, $cheque_bank_ac_from_id = NULL, $cheque_bank_to, $cheque_bank_ac_to, NULL, $cheque_bank_to_branch = NULL, NULL, NULL, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_from_id = NULL, $transction_bank_from_ac_id = NULL, $transction_bank_to, $transction_bank_ac_to, $transction_bank_to_name = NULL, $transction_bank_to_ac_no = NULL, $transction_bank_to_branch = NULL, $transction_bank_to_ifsc = NULL, $memberInvestments->created_at, $created_by, $created_by_id);
            /*$allTransaction = CommanController::createAllTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,4,86,121,122,NULL,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$amount,$amount,$amount,$description,'CR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$memberInvestments->created_at);*/
            $allTransaction = CommanController::createAllHeadTransaction($dayBookRef, $branch_id, $bank_id, $bank_ac_id, 28, $type, $sub_type, $type_id, $createDayBook, $associate_id, $member_id, $branch_id_to, $branch_id_from, $amount, $amount, $amount, $description, 'DR', $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $jv_unique_id = NULL, $v_no, $v_date, $ssb_account_id_from, $ssb_account_id_to = NULL, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $cheque_type = NULL, $cheque_id = NULL, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_from_id = NULL, $cheque_bank_ac_from_id = NULL, $cheque_bank_to, $cheque_bank_ac_to, NULL, $cheque_bank_to_branch = NULL, NULL, NULL, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_from_id = NULL, $transction_bank_from_ac_id = NULL, $transction_bank_to, $transction_bank_ac_to, $transction_bank_to_name = NULL, $transction_bank_to_ac_no = NULL, $transction_bank_to_branch = NULL, $transction_bank_to_ifsc = NULL, $memberInvestments->created_at, $created_by, $created_by_id);
            /*$allTransaction = CommanController::createAllTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,2,10,28,71,NULL,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$amount,$amount,$amount,$description,'CR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$memberInvestments->created_at);*/
            $branchDayBook = CommanController::branchDayBookNew($dayBookRef, $branch_id, $type, $sub_type, $type_id, $type_transaction_id, $associate_id, $member_id, $branch_id_to, $branch_id_from, $amount, $amount, $amount, $description, $description_dr, $description_cr, 'CR', $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $is_contra, $contra_id, $created_at, 0, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL);
            //$branchDayBook = CommanController::createBranchDayBook($dayBookRef,$branch_id,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$amount,$amount,$amount,$description,$description_dr,$description_cr,'CR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$is_contra,$contra_id,$created_at,0);
            $memberTransaction = CommanController::memberTransactionNew($dayBookRef, $type, $sub_type, $type_id, $type_transaction_id, $associate_id, $member_id, $branch_id, $bank_id, $bank_ac_id, $amount, $description, 'DR', $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL);
            /*$memberTransaction = CommanController::createMemberTransaction($dayBookRef,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id,$bank_id,$bank_ac_id,$amount,$description,'DR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at);*/
            $this->updateBranchCashCr($memberInvestments->branch_id, $memberInvestments->created_at, 50, 0);
            $this->updateBranchClosingCashCr($memberInvestments->branch_id, $memberInvestments->created_at, 50, 0);
          }
        } else {
          $data['investment_id'] = $memberInvestments->id;
          $data['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($memberInvestments->created_at)));
          $satRef = TransactionReferences::create($data);
          $satRefId = $satRef->id;
          $amountArraySsb = array('1' => (50));
          $ssbCreateTran = CommanController::createTransaction($satRefId, 18, $memberInvestments->id, $memberInvestments->member_id, $memberInvestments->branch_id, getBranchCode($memberInvestments->branch_id)->branch_code, $amountArraySsb, 0, NULL, $memberInvestments->member_id, $memberInvestments->account_number, NULL, NULL, NULL, date("Y-m-d", strtotime(convertDate($memberInvestments->created_at))), NULL, NULL, NULL, 'CR');
          $createDayBook = CommanController::createDayBook($ssbCreateTran, $satRefId, 19, $memberInvestments->id, NULL, $memberInvestments->member_id, 50, 50, $withdrawal = 0, 'Stationary charges on investment plan registration', $memberInvestments->account_number, $memberInvestments->branch_id, getBranchCode($memberInvestments->branch_id)->branch_code, $amountArraySsb, 0, NULL, $memberInvestments->member_id, $memberInvestments->account_number, 50, NULL, NULL, date("Y-m-d", strtotime(convertDate($memberInvestments->created_at))), NULL, NULL, NULL, 'CR');
          $allTransaction = CommanController::createAllHeadTransaction($dayBookRef, $branch_id, $bank_id, $bank_ac_id, 122, $type, $sub_type, $type_id, $createDayBook, $associate_id, $member_id, $branch_id_to, $branch_id_from, $amount, $amount, $amount, $description, 'CR', $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $jv_unique_id = NULL, $v_no, $v_date, $ssb_account_id_from, $ssb_account_id_to = NULL, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $cheque_type = NULL, $cheque_id = NULL, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_from_id = NULL, $cheque_bank_ac_from_id = NULL, $cheque_bank_to, $cheque_bank_ac_to, NULL, $cheque_bank_to_branch = NULL, NULL, NULL, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_from_id = NULL, $transction_bank_from_ac_id = NULL, $transction_bank_to, $transction_bank_ac_to, $transction_bank_to_name = NULL, $transction_bank_to_ac_no = NULL, $transction_bank_to_branch = NULL, $transction_bank_to_ifsc = NULL, $memberInvestments->created_at, $created_by, $created_by_id);
          /*$allTransaction = CommanController::createAllTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,4,86,121,122,NULL,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$amount,$amount,$amount,$description,'CR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$memberInvestments->created_at);*/
          $allTransaction = CommanController::createAllHeadTransaction($dayBookRef, $branch_id, $bank_id, $bank_ac_id, 28, $type, $sub_type, $type_id, $createDayBook, $associate_id, $member_id, $branch_id_to, $branch_id_from, $amount, $amount, $amount, $description, 'DR', $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $jv_unique_id = NULL, $v_no, $v_date, $ssb_account_id_from, $ssb_account_id_to = NULL, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $cheque_type = NULL, $cheque_id = NULL, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_from_id = NULL, $cheque_bank_ac_from_id = NULL, $cheque_bank_to, $cheque_bank_ac_to, NULL, $cheque_bank_to_branch = NULL, NULL, NULL, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_from_id = NULL, $transction_bank_from_ac_id = NULL, $transction_bank_to, $transction_bank_ac_to, $transction_bank_to_name = NULL, $transction_bank_to_ac_no = NULL, $transction_bank_to_branch = NULL, $transction_bank_to_ifsc = NULL, $memberInvestments->created_at, $created_by, $created_by_id);
          /*$allTransaction = CommanController::createAllTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,2,10,28,71,NULL,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$amount,$amount,$amount,$description,'CR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$memberInvestments->created_at);*/
          $branchDayBook = CommanController::branchDayBookNew($dayBookRef, $branch_id, $type, $sub_type, $type_id, $type_transaction_id, $associate_id, $member_id, $branch_id_to, $branch_id_from, $amount, $amount, $amount, $description, $description_dr, $description_cr, 'CR', $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $is_contra, $contra_id, $created_at, 0, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL);
          $memberTransaction = CommanController::memberTransactionNew($dayBookRef, $type, $sub_type, $type_id, $type_transaction_id, $associate_id, $member_id, $branch_id, $bank_id, $bank_ac_id, $amount, $description, 'DR', $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL);
          /*$branchDayBook = CommanController::createBranchDayBook($dayBookRef,$branch_id,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$amount,$amount,$amount,$description,$description_dr,$description_cr,'CR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$is_contra,$contra_id,$created_at,0);
                    $memberTransaction = CommanController::createMemberTransaction($dayBookRef,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id,$bank_id,$bank_ac_id,$amount,$description,'DR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at);*/
          $this->updateBranchCashCr($memberInvestments->branch_id, $memberInvestments->created_at, 50, 0);
          $this->updateBranchClosingCashCr($memberInvestments->branch_id, $memberInvestments->created_at, 50, 0);
        }
      } else {
        $data['investment_id'] = $memberInvestments->id;
        $data['created_at'] = date("Y-m-d " . $entryTime . "", strtotime(convertDate($memberInvestments->created_at)));
        $satRef = TransactionReferences::create($data);
        $satRefId = $satRef->id;
        $amountArraySsb = array('1' => (50));
        $ssbCreateTran = CommanController::createTransaction($satRefId, 18, $memberInvestments->id, $memberInvestments->member_id, $memberInvestments->branch_id, getBranchCode($memberInvestments->branch_id)->branch_code, $amountArraySsb, 0, NULL, $memberInvestments->member_id, $memberInvestments->account_number, NULL, NULL, NULL, date("Y-m-d", strtotime(convertDate($memberInvestments->created_at))), NULL, NULL, NULL, 'CR');
        $createDayBook = CommanController::createDayBook($ssbCreateTran, $satRefId, 19, $memberInvestments->id, NULL, $memberInvestments->member_id, 50, 50, $withdrawal = 0, 'Stationary charges on investment plan registration', $memberInvestments->account_number, $memberInvestments->branch_id, getBranchCode($memberInvestments->branch_id)->branch_code, $amountArraySsb, 0, NULL, $memberInvestments->member_id, $memberInvestments->account_number, 50, NULL, NULL, date("Y-m-d", strtotime(convertDate($memberInvestments->created_at))), NULL, NULL, NULL, 'CR');
        $allTransaction = CommanController::createAllHeadTransaction($dayBookRef, $branch_id, $bank_id, $bank_ac_id, 122, $type, $sub_type, $type_id, $createDayBook, $associate_id, $member_id, $branch_id_to, $branch_id_from, $amount, $amount, $amount, $description, 'CR', $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $jv_unique_id = NULL, $v_no, $v_date, $ssb_account_id_from, $ssb_account_id_to = NULL, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $cheque_type = NULL, $cheque_id = NULL, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_from_id = NULL, $cheque_bank_ac_from_id = NULL, $cheque_bank_to, $cheque_bank_ac_to, NULL, $cheque_bank_to_branch = NULL, NULL, NULL, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_from_id = NULL, $transction_bank_from_ac_id = NULL, $transction_bank_to, $transction_bank_ac_to, $transction_bank_to_name = NULL, $transction_bank_to_ac_no = NULL, $transction_bank_to_branch = NULL, $transction_bank_to_ifsc = NULL, $memberInvestments->created_at, $created_by, $created_by_id);
        /*$allTransaction = CommanController::createAllTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,4,86,121,122,NULL,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$amount,$amount,$amount,$description,'CR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$memberInvestments->created_at);*/
        $allTransaction = CommanController::createAllHeadTransaction($dayBookRef, $branch_id, $bank_id, $bank_ac_id, 28, $type, $sub_type, $type_id, $createDayBook, $associate_id, $member_id, $branch_id_to, $branch_id_from, $amount, $amount, $amount, $description, 'DR', $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $jv_unique_id = NULL, $v_no, $v_date, $ssb_account_id_from, $ssb_account_id_to = NULL, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $cheque_type = NULL, $cheque_id = NULL, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_from_id = NULL, $cheque_bank_ac_from_id = NULL, $cheque_bank_to, $cheque_bank_ac_to, NULL, $cheque_bank_to_branch = NULL, NULL, NULL, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_from_id = NULL, $transction_bank_from_ac_id = NULL, $transction_bank_to, $transction_bank_ac_to, $transction_bank_to_name = NULL, $transction_bank_to_ac_no = NULL, $transction_bank_to_branch = NULL, $transction_bank_to_ifsc = NULL, $memberInvestments->created_at, $created_by, $created_by_id);
        /*$allTransaction = CommanController::createAllTransaction($dayBookRef,$branch_id,$bank_id,$bank_ac_id,2,10,28,71,NULL,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$amount,$amount,$amount,$description,'CR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$memberInvestments->created_at);*/
        $branchDayBook = CommanController::branchDayBookNew($dayBookRef, $branch_id, $type, $sub_type, $type_id, $type_transaction_id, $associate_id, $member_id, $branch_id_to, $branch_id_from, $amount, $amount, $amount, $description, $description_dr, $description_cr, 'CR', $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $is_contra, $contra_id, $created_at, 0, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL);
        $memberTransaction = CommanController::memberTransactionNew($dayBookRef, $type, $sub_type, $type_id, $type_transaction_id, $associate_id, $member_id, $branch_id, $bank_id, $bank_ac_id, $amount, $description, 'DR', $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $v_no, $v_date, $ssb_account_id_from, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_to, $cheque_bank_ac_to, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_to, $transction_bank_ac_to, $transction_date, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $jv_unique_id = NULL, $cheque_type = NULL, $cheque_id = NULL);
        /*$branchDayBook = CommanController::createBranchDayBook($dayBookRef,$branch_id,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id_to,$branch_id_from,$amount,$amount,$amount,$description,$description_dr,$description_cr,'CR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$is_contra,$contra_id,$created_at,0);
                $memberTransaction = CommanController::createMemberTransaction($dayBookRef,$type,$sub_type,$type_id,$type_transaction_id,$associate_id,$member_id,$branch_id,$bank_id,$bank_ac_id,$amount,$description,'DR',$payment_mode,$currency_code,$amount_to_id,$amount_to_name,$amount_from_id,$amount_from_name,$v_no,$v_date,$ssb_account_id_from,$cheque_no,$cheque_date,$cheque_bank_from,$cheque_bank_ac_from,$cheque_bank_ifsc_from,$cheque_bank_branch_from,$cheque_bank_to,$cheque_bank_ac_to,$transction_no,$transction_bank_from,$transction_bank_ac_from,$transction_bank_ifsc_from,$transction_bank_branch_from,$transction_bank_to,$transction_bank_ac_to,$transction_date,$entry_date,$entry_time,$created_by,$created_by_id,$created_at);*/
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



          $allTransaction = CommanController::createAllHeadTransaction($dayBookRef, $branch_id, $bank_id, $bank_ac_id, 28, $type, $sub_type_igst, $type_id, $createdGstTransaction, $associate_id, $member_id, $branch_id_to, $branch_id_from, $gstAmount, $gstAmount, $gstAmount, $descriptionB, 'DR', $payment_mode, $currency_code, $amount_to_id, $amount_to_name, $amount_from_id, $amount_from_name, $jv_unique_id = NULL, $v_no, $v_date, $ssb_account_id_from, $ssb_account_id_to = NULL, $ssb_account_tran_id_to = NULL, $ssb_account_tran_id_from = NULL, $cheque_type = NULL, $cheque_id = NULL, $cheque_no, $cheque_date, $cheque_bank_from, $cheque_bank_ac_from, $cheque_bank_ifsc_from, $cheque_bank_branch_from, $cheque_bank_from_id = NULL, $cheque_bank_ac_from_id = NULL, $cheque_bank_to, $cheque_bank_ac_to, NULL, $cheque_bank_to_branch = NULL, NULL, NULL, $transction_no, $transction_bank_from, $transction_bank_ac_from, $transction_bank_ifsc_from, $transction_bank_branch_from, $transction_bank_from_id = NULL, $transction_bank_from_ac_id = NULL, $transction_bank_to, $transction_bank_ac_to, $transction_bank_to_name = NULL, $transction_bank_to_ac_no = NULL, $transction_bank_to_branch = NULL, $transction_bank_to_ifsc = NULL, $transction_date, $created_by, $created_by_id);
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


  public function planform_saving_account(Request $request)
  {
    if ($request->account_no == 'Account Number N/A') {
      $response = ['data' => 2];
    } else {
      $savingAccount = SavingAccount::where('transaction_status', '1')->where('account_no', $request->account_no)->first();
      if ($savingAccount) {
        $response = ['data' => 1];
      } else {
        $response = ['data' => 0];
      }
    }
    return response()->json($response);
  }
}
