<?php

namespace App\Http\Controllers\Branch;

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
    return view('templates.branch.investment_management_v2.investment-listing', $data);
  }
  /**
   * Show the form for creating a new resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function create()
  {

    // if (check_my_permission(Auth::user()->id, "18") != "1") {
    //   return redirect()->route('admin.dashboard');
    // }
    $data['title'] = "Investment Plans Registration";
    $data['plans'] = $this->repository->getAllPlans()->has('PlanTenures')->has('company')->where('status', 1)->where('id', '!=', 3)->orderby('company_id','DESC')->orderBy('plan_category_code','DESC')->get(['id', 'slug', 'name', 'plan_category_code']);
    $data['relations'] = Relations::all();
    $account_setting = \App\Models\SsbAccountSetting::where('plan_type', 1)->where('user_type', 1)->where('status', 1)->first();
    $data['branches'] = Branch::all('id', 'state_id', 'name');
    $data['plan_amount'] = $account_setting->amount;
    return view('templates.branch.investment_management_v2.assign', $data);
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
    $response = Investment::storeInvestment($request, $this->repository);

    $insertedid = $response['insertedid'];

    if ($response['status']) {
      return redirect('branch/investment/recipt/' . $insertedid);
      return back()->with('success', 'Saved Successfully!');
    } else {
      return back()->with('alert', $response['msg']);
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
    $memberDetail = Investment::getMember($mId)
      ->with([ 'memberNominee', 'customerInvestment', 'memberIdProofs'])->when(method_exists(Member::class, 'memberCompany'), function ($q) use ($companyId) {
        $q->with(['memberCompany' => function ($q) use ($companyId) {

          $q->where('company_id', $companyId);
        }]);
      })->with(['savingAccount' => function ($q) use ($companyId){
        $q->where('company_id', $companyId);
      }])
      ->first();




    $newUser  = isset($memberDetail->memberCompany);

    $resCount = isset($memberDetail->id) ? 1 : 0;
    $member = $memberDetail;
    $countInvestment = ($resCount > 0 && $memberDetail) ? count($memberDetail['customerInvestment']) : 0;
    $plans = $this->repository->getAllPlans()
      ->getCompanyRecords('companyId', $companyId)->whereHas('planTenures', function ($query) use ($companyId) {
        $query->where('company_id', $companyId)
          ->where('plan_category_code', '!=', 'S');
      })
      ->where('status', 1)
      ->where('id', '!=', 3)
      ->select(['id', 'slug', 'name', 'plan_category_code', 'company_id', 'multiple_deposit', 'min_deposit', 'max_deposit', 'is_ssb_required'])
      ->get();

    $return_array = compact('countInvestment', 'member', 'resCount', 'plans', 'newUser');
    return json_encode($return_array);
  }
  /**
   * Get Plan Form based on name 
   * @param string $request->plan
   * @param int request->memberAutoId
   */
  public function planForm(Request $request)
  {

    $planData = Investment::planForm($request, $this->repository);
    $member = $planData['member'];
    $relations = $planData['relations'];
    $plans_tenure = $planData['plans_tenure'];
    $plan = $planData['plan'];

    return view('templates.branch.investment_management_v2.' . $plan . '.' . $plan . '', compact('member', 'relations', 'plans_tenure'));
  }

  public function planRecipt($id)
  {

    $data['title'] = 'Investment Plan | Receipt';
    $data['investmentDetails'] = Memberinvestments::with('plan:id,name,plan_category_code', 'member:id,member_id,first_name,last_name', 'investmentNomiees', 'memberCompany:id,customer_id,member_id', 'getBankDetails')->where('id', $id)->get();

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
    return view('templates.branch.investment_management_v2.' . $reciptTemplate . '/' . $reciptTemplate . '-recipt', $data);
  }
}
