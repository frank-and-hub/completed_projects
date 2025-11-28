<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{Plans, Relations, SsbAccountSetting, Branch, MemberNominee, PlanCategory, SavingAccount, Memberinvestments, Investmentplantransactions, Memberinvestmentsnominees, SavingAccountTranscation, Member};
use Investment;
use Auth;
use App\Interfaces\RepositoryInterface;
use App\Http\Requests\InvestmentPlanRequest;
use Session;
use App\Http\Controllers\Admin\CommanController;
use App\Services\Sms;
use CommanTransactionFacade;
use DB;
use App\Scopes\ActiveScope;
class InvestmentControllerV2 extends Controller
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
   * Display a listing of the resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function index()
  {
    if (check_my_permission(Auth::user()->id, "19") != "1") {
      return redirect()->route('admin.dashboard');
    }
    $data['title'] = "Investment Plans";
    return view('templates.admin.investment_management_v2.investment-listing', $data);
  }
  /**
   * Show the form for creating a new resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function create()
  {
    if (check_my_permission(Auth::user()->id, "18") != "1") {
      return redirect()->route('admin.dashboard');
    }
    $data['title'] = "Investment Plans Registration";
    $data['plans'] = $this->repository->getAllPlans()->has('PlanTenures')->has('company')->where('status', 1)->where('id', '!=', 3)->get(['id', 'slug', 'name', 'plan_category_code']);
    $data['relations'] = Relations::all();
    $account_setting = \App\Models\SsbAccountSetting::where('plan_type', 1)->where('user_type', 1)->where('status', 1)->first();
    $data['branches'] = Branch::all('id', 'state_id', 'name');
    $data['plan_amount'] = $account_setting->amount;
    return view('templates.admin.investment_management_v2.assign', $data);
  }
  /**
   * Store a newly created resource in storage.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\Response
   */
  public function store(InvestmentPlanRequest $request)
  {
    $validated = $request->validated();
    $response = Investment::storeInvestment($request,$this->repository);
   
    $insertedid = $response['insertedid'];

    if($response['status']){
        return redirect('admin/investment/recipt/' . $insertedid);
        return back()->with('success', 'Saved Successfully!');
    }
    else{
        return back()->with('alert',$response['msg']);
    }
 
  }
  
  /**
   * Get Member Detail using customerId
   * @param int $memberId
   * @return \Illuminate\Http\Response
   */
  public function getMember(Request $request)
  {
    $mId = $request->memberid;
    $companyId = $request->companyId;
    /*
    $memberDetail = Investment::getMember($mId)
      ->with(['memberNominee', 'customerInvestment', 'memberIdProofs'])->when(method_exists(Member::class, 'memberCompany'), function ($q) use ($companyId) {
        $q->with(['memberCompany' => function ($q) use ($companyId) {
          $q->where('company_id', $companyId);
        }]);
      })->with(['savingAccount' => function ($q) use ($companyId) {
        $q->where('company_id', $companyId);
      }])
      ->first();
    $newUser  = isset($memberDetail->memberCompany) ;     
    $resCount = isset($memberDetail->id) ? 1 : 0;
    $member = $memberDetail;
    $countInvestment = ($resCount > 0 && $memberDetail) ? count($memberDetail['customerInvestment']) : 0;
    $plans = $this->repository->getAllPlans()
      ->getCompanyRecords('companyId', $companyId) ->where(function ($query) use ($companyId) {
        $query->whereHas('planTenures', function ($subquery) use ($companyId) {
            $subquery->where('company_id', $companyId);
        })
        ->orWhere('plan_category_code', 'S');
    })->where('status', 1)
      ->where('id', '!=', 3)
      ->select(['id', 'slug', 'name', 'plan_category_code', 'company_id','multiple_deposit','min_deposit','max_deposit','is_ssb_required','plan_sub_category_code'])->orderby('company_id','ASC')->orderBy('plan_category_code','ASC')
      ->get();
 */
  // $memberDetail = Investment::getMember($mId)
    // code commented by sourab on 07-11-2023
    $memberDetail = $this->repository->getAllMember()->whereMemberId($mId)
      ->with(['memberNominee', 'customerInvestment', 'memberIdProofs'])
      ->when(method_exists(Member::class, 'memberCompany'), function ($q) use ($companyId) {
          $q
          ->with(['memberCompany' => function ($q) use ($companyId) {
            $q->where('company_id', $companyId);
        }]);
      })
      ->with(['savingAccount' => function ($q) use ($companyId) {
        $q->where('company_id', $companyId);
      }])
      ->first();   
    // default values are null or empty 
    $view = 'No record found !';
    $countInvestment = 0;
    $resCount = 0;
    $plans = '';
    $member = '';
    $newUser = '';
    $showMi = '';
    /** check if member details are correct or not */
    if ($memberDetail) {
        /** check if member status is active or not */
      if ($memberDetail->status == '0') {
        $view = 'Customer is Inactive. Please contact administrator!';
      }else{
         /** check if member status is block or not by system*/
        if ($memberDetail->is_block == '1') {
            $view = 'Customer is Inactive. Please Upload Signature and Photo.';
        }else{
          $checkMemberInOtherCompany = \App\Models\MemberCompany::where('customer_id', $memberDetail->id)->exists();
          $newUser  = isset($memberDetail->memberCompany);
          $showMi =  $checkMemberInOtherCompany ? true : false;
          $resCount = isset($memberDetail->id) ? 1 : 0;
          $member = $memberDetail;
          $countInvestment = ($resCount > 0 && $memberDetail) ? count($memberDetail['customerInvestment']) : 0;
          $plans = $this->repository->getAllPlans()
            ->getCompanyRecords('companyId', $companyId)->where(function ($query) use ($companyId) {
              $query->whereHas('planTenures', function ($subquery) use ($companyId) {
                $subquery->where('company_id', $companyId);
              })
                ->orWhere('plan_category_code', 'S');
            })->where('status', 1)
            ->where('id', '!=', 3)
            ->select(['id', 'slug', 'name', 'plan_category_code', 'company_id', 'multiple_deposit', 'min_deposit', 'max_deposit', 'is_ssb_required', 'plan_sub_category_code'])->orderby('company_id', 'ASC')->orderBy('plan_category_code', 'ASC')
            ->get();
            $view = '';
        }
      }
    }   

    $return_array = compact('countInvestment', 'member', 'resCount', 'plans','newUser','view');
    return json_encode($return_array);
  }
  /**
   * Get Plan Form based on name 
   * @param string $request->plan
   * @param int request->memberAutoId
   */
  public function planForm(Request $request)
  {

    $planData = Investment::planForm($request,$this->repository);
    $member = $planData['member'];
    $relations = $planData['relations'];
    $plans_tenure = $planData['plans_tenure'];
    $plan = $planData['plan'];
    $plans_tenures = $planData['plans_tenures'];
    $savingAccount = $planData['savingAccount'];
    return view('templates.admin.investment_management_v2.' . $plan . '.' . $plan . '', compact('member', 'relations', 'plans_tenure', 'plans_tenures', 'savingAccount'));
  }

  public function planRecipt($id)
  {

    $data['title'] = 'Investment Plan | Receipt';
    $data['investmentDetails'] = Memberinvestments::with('member:id,member_id,first_name,last_name', 'investmentNomiees','memberCompany:id,customer_id,member_id','getBankDetails')->with(['plan'=>function($q){
      $q->withoutGlobalScope(ActiveScope::class);
   }])->where('id', $id)->get();
    $reciptTemplate = $data['investmentDetails'][0]['plan']['CategoryName'][0]->slug;
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
    return view('templates.admin.investment_management_v2.' . $reciptTemplate . '/' . $reciptTemplate . '-recipt', $data);
  }
}
