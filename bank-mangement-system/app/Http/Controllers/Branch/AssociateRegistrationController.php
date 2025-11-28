<?php
namespace App\Http\Controllers\Branch;

use Illuminate\Http\Request;
use App\Interfaces\RepositoryInterface;
use App\Http\Controllers\Controller;
use App\Models\SavingAccount;
use Illuminate\Support\Facades\{Hash, Auth, DB, Response, Session, Image, Redirect, URL, Validator};
use App\Http\Requests;
use App\Models\{ReceivedCheque, Memberinvestmentsnominees, AssociateDependent, AssociateTree, AssociateGuarantor, FaCode, Member, Receipt, Memberinvestments, ReceiptAmount, SamraddhBank, Carder, MemberIdProof, MemberNominee};
use App\Http\Controllers\Branch\CommanTransactionsController;
use Yajra\DataTables\DataTables;
use App\Services\Sms;
use Investment;
use DateTime;

class AssociateRegistrationController extends Controller
{
    private $repository;
    public function __construct(RepositoryInterface $repository)
    {
        $this->middleware('auth');
        $this->repository = $repository;
    }
    public function index()
    {
        if (!in_array('Associate Create', auth()->user()->getPermissionNames()->toArray())) {

            return redirect()

                ->route('branch.dashboard');

        }
        $data['title'] = 'Associate | Registration';
        $data['carder'] = Carder::whereStatus(1)->where('is_deleted', 0)->limit(3)->get(['id', 'name', 'short_name']);
        $data['relations'] = relationsList();
        $data['samraddhBanks'] = SamraddhBank::with('bankAccount')->get();
        return view('templates.branch.associate_registration_management.index', $data);
    }
    public function create(Request $request)
    {
        Session::put('created_at', $request->created_at);
        $globaldate = $request->created_at;
        $created_at = date("Y-m-d H:i:s", strtotime(convertDate($globaldate)));
        $updated_at = date("Y-m-d H:i:s", strtotime(convertDate($globaldate)));
        $is_primary = 0;
        $isReceipt = 'No';
        $receipt_id = $request->receipt_id??0;
        $investmentAccountNoRd = 0;
        $customerId = $request->customerRegisterId;
        $getBranchId = getUserBranchId(Auth::user()->id);
        $branchId = $getBranchId->id;
        $getBranchCode = getBranchCode($branchId);
        $branchCode = $getBranchCode->branch_code;
        $customer = $this->repository->getAllMember()->whereId($customerId)->whereStatus(1)->whereIsDeleted(0)->first();
        if (!empty($customer)) {
            $customerName = $customer->first_name . ' ' . $customer->last_name;
        }
        $associateSettings = $this->repository->getAllCompanies()->whereStatus('1')->whereHas('companyAssociate', function ($query) {
            $query->whereStatus('1')->select(['id', 'status', 'company_id']);
        })->first(['id', 'status']);
        $associatecompanyId = $associateSettings->id;
        $membercompany = $this->repository->getAllMemberCompany()->whereCustomerId($customerId)->whereCompanyId($associatecompanyId)->whereStatus(1)->whereIsDeleted(0)->first();
        $memberId = $membercompany ? $membercompany->id : NULL;
        // $planAssociateSettings = $this->repository->getAllPlans()->wherePlanCategoryCode('S')->whereStatus('1')->whereCompanyId($associatecompanyId)->first(['id', 'company_id', 'status', 'plan_category_code', 'plan_code', 'deposit_head_id']);
        
        $notInCompanyCustomer = $this->repository->getAllCompanies()->whereStatus('1')->whereDoesntHave('memberCompany', function ($query) use ($customerId) {
            $query->whereCustomerId($customerId)->select(['id', 'company_id', 'customer_id']);
        })->get();
        $memberInvestmet = Memberinvestments::whereHas('member', function ($q) use ($customerId) {
            $q->whereId($customerId);
        })->first();
        $memberInvestmetId = $memberInvestmet->id;
        $memberInvestmentNominee = Memberinvestmentsnominees::whereHas('memberinvestments', function ($q) use ($memberInvestmetId) {
            $q->where('id', $memberInvestmetId);
        })->get();
        
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
                $ssb_account_number = $this->repository->getAllSavingAccount()->whereCustomerId($customerId)->whereCompanyId($companyId)->whereMemberId($memberId);
                if ($ssb_account_number->count('id') == 0) {
                    // saving accout create deafault company
                    $payment_mode = 0;
                    // getInvesment Plan id by plan code                    
                    $planAssociateSettings = $this->repository->getAllPlans()->wherePlanCategoryCode('S')->whereStatus('1')->whereCompanyId($companyId)->first();
                    $ssbFaCode = $planAssociateSettings->plan_code;
                    // $ssbFaCode = $FaCode[3]->code;
                    $ssbPlanIdGet = getPlanID($ssbFaCode);
                    $ssbPlanId = $ssbPlanIdGet->id;
                    $investmentMiCodeSsb = getInvesmentMiCodeNew($ssbPlanId, $branchId);
                    $miCodeAdd = $investmentMiCodeSsb ? $investmentMiCodeSsb->mi_code + 1 : 1;
                    $miCode = str_pad($miCodeAdd, 5, '0', STR_PAD_LEFT);
                    // Invesment Account no
                    $investmentAccountNoSsb = $branchCode . $ssbFaCode . $miCode;
                    $dataInvestR['deposite_amount'] = $ssb_amount;
                    $dataInvestR['plan_id'] = $ssbPlanId;
                    $dataInvestR['form_number'] = $request->ssb_form_no_form;
                    $dataInvestR['member_id'] = $memberId;
                    $dataInvestR['customer_id'] = $customerId;
                    $dataInvestR['branch_id'] = $branchId;
                    $dataInvestR['old_branch_id'] = $branchId;
                    $dataInvestR['account_number'] = $investmentAccountNoSsb;
                    $dataInvestR['mi_code'] = $miCode;
                    $dataInvestR['associate_id'] = 1;
                    $dataInvestR['current_balance'] = $ssb_amount;
                    $dataInvestR['created_at'] = $globaldate;
                    $dataInvestR['company_id'] = $companyId;
                    $res = $this->repository->CreateMemberinvestments($dataInvestR);
                    $investmentId = $res->id;
                    //create savings account
                    $des = 'SSB Account Opening';
                    $amount = $ssb_amount;
                    $daybookRefssbId = CommanTransactionsController::createBranchDayBookReferenceNew($ssb_amount, $globaldate);
                    $createAccount = CommanTransactionsController::createSavingAccountDescriptionModify($memberId, $branchId, $branchCode, $amount, $payment_mode, $investmentId, $miCode, $investmentAccountNoSsb, $is_primary, $ssbFaCode, $des, $associate_id = 1, $companyId, $globaldate, $customerId, $daybookRefssbId);
                    $ssbAccountId = $createAccount['ssb_id'];
                    $ssb_account = $investmentAccountNoSsb;
                    if(isset($ssb_account) && !empty($ssb_account)){
                        $this->repository->getAllMemberCompany()->whereId($memberId)->update(['ssb_account'=>$investmentAccountNoSsb]);
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
                $dataMsg['msg'] = 'Associate Registered Successfully Associate Code : ' . $getmemberID ; // 'Associate created Successfully';
                $dataMsg['reciept_generate '] = $isReceipt;
                $dataMsg['receipt_id'] = $receipt_id;
            } else {
                $dataMsg['msg_type'] = 'error';
                $dataMsg['msg'] = 'Associate Not Registered With Guarantor Details !';
                $dataMsg['reciept_generate '] = $isReceipt;
                $dataMsg['receipt_id'] = $receipt_id;
            }
            $contactNumber = array();
            $contactNumber[] = $customer->mobile_no;
            $ssbGetDetailCheck = $this->repository->getAllSavingAccount()->whereCustomerId($customer->id)->count();
            $s = $this->repository->getAllSavingAccount()->whereCustomerId($customer->id)->orderBy('company_id')->pluck('company_id')->toArray();
            $c = $this->repository->getAllCompanies()->whereStatus('1')->orderBy('id')->pluck('id')->toArray();
            // dd($c,$s);
            $nhscoc = array_diff($c,$s); // not Have Saving Account On Company
            $nhscoc = reset($nhscoc);  // Get the first value from the array
            if ($ssbGetDetailCheck < count($c)) {
                $MemberId = getMemberAllData($customerId,$nhscoc)->id;
                $payment_mode = 0;
                $planAssociateSettings = $this->repository->getAllPlans()->wherePlanSubCategoryCode(Null)->wherePlanCategoryCode('S')->whereStatus('1')->whereCompanyId($nhscoc)->first();
                $ssbFaCode = $planAssociateSettings->plan_code;
                // $ssbFaCode = $FaCode[3]->code;
                $ssbPlanIdGet = getPlanID($ssbFaCode);
                $ssbPlanId = $ssbPlanIdGet->id;
                $investmentMiCodeSsb = getInvesmentMiCodeNew($ssbPlanId, $branchId);
                $miCodeAdd = $investmentMiCodeSsb ? $investmentMiCodeSsb->mi_code + 1 : 1;
                $miCodeDefault = str_pad($miCodeAdd, 5, '0', STR_PAD_LEFT);
                $investmentAccountNoSsb = $branchCode . $ssbFaCode . $miCodeDefault;
                $dataInvest = [
                    'deposite_amount' => $ssb_amount??0,
                    'plan_id' => $ssbPlanId,
                    'form_number' => $request->ssb_form_no_form,
                    'member_id' => $MemberId,
                    'customer_id' => $customerId,
                    'branch_id' => $branchId,
                    'old_branch_id' => $branchId,
                    'account_number' => $investmentAccountNoSsb,
                    'mi_code' => $miCodeDefault,
                    'associate_id' => 1,
                    'current_balance' => $ssb_amount??0,
                    'created_at' => $globaldate,
                    'company_id' => $nhscoc,
                ];
                $res = $this->repository->CreateMemberinvestments($dataInvest);
                $investmentIddefault = $res->id;
                //create savings account
                $des = 'SSB Account Opening';
                $amount = $ssb_amount??0;
                $daybookRefssbIdssbdefault = CommanTransactionsController::createBranchDayBookReferenceNew($ssb_amount??0, $globaldate);
                $createAccount = CommanTransactionsController::createSavingAccountDescriptionModify($MemberId, $branchId, $branchCode, $amount, $payment_mode, $investmentIddefault, $miCodeDefault, $investmentAccountNoSsb, $is_primary, $ssbFaCode, $des, $associate_id = 1, $nhscoc, $globaldate, $customerId, $daybookRefssbIdssbdefault);
            }
			$ssbGetDetail = $this->repository->getAllSavingAccount()->whereCustomerId($customer->id)->whereCompanyId('1')->first();
            // $ssbGetDetail = $this->repository->getAllSavingAccount()->whereCustomerId($customer->id)->first();
            $invGetDetail = Memberinvestments::whereAccountNumber($ssbGetDetail->account_no)->first();
			
            if(isset($customer->ssb_account)){
            // if ($request->ssb_account == 0) {
                if (isset($customer->ssb_account) && isset($customer->rd_accoun)) {           
                    $text = 'Dear Member, Thank You for Choosing S.B. Micro Finance association. Your Associate Code ' . $dataAssociate['associate_no'] . ', Saving A/C ' . $ssbGetDetail->account_no . ' is Created on ' . $ssbGetDetail->created_at->format('d M Y') . ' with Rs. ' . round($ssb_amount, 2) . ' CR, Recurring A/c No. ' . $customer->rd_accoun . ' is Created on ' . $invGetDetail->created_at->format('d M Y') . ' with Rs. ' . round($invGetDetail->deposite_amount, 2) . ' CR. Have a good day';
                } elseif (isset($customer->ssb_account)) {
                    $link = "https://play.google.com/store/apps/details?id=com.associate.sbmfa";
                    $associateNo = $dataAssociate['associate_no'];
                    $sAccount = $ssbGetDetail->account_no;
                    $sAccountAmount = round($invGetDetail->deposite_amount, 2);
                    $cDate = $ssbGetDetail->created_at->format('d M Y');
					// $text = 'Dear Member, Thank You for Choosing S.B. Micro Finance association. Your Associate Code ' . $dataAssociate['associate_no'] . ', Saving A/C ' . $ssbGetDetail->account_no . ' is Created on ' . $ssbGetDetail->created_at->format('d M Y') .'. Have a good day';
					$text = "Dear Member, Thank You for Choosing S.B. Micro Finance association. Your Associate Code $associateNo, Saving A/C $sAccount is Created on $cDate with Rs. $sAccountAmount Cr. Have a good day $link";
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
    public function store(Request $request)
    {
        Session::put('created_at', $request->created_at);
        $errorCount = 0;
        $form = 0;
        $receipt_id = 0;
        $investmentId = 0;
        $isReceipt = 'no';
        $is_primary = 0;
        $dataMsg['errormsg'] = '';
        $investmentAccountNoSsb = '';
        $globaldate = $request->created_at;
        $created_at = date("Y-m-d H:i:s", strtotime(convertDate($globaldate)));
        $updated_at = date("Y-m-d H:i:s", strtotime(convertDate($globaldate)));
        $customerId = $request->id;
        $ssb_account = $request->ssb_account;
        $rd_account = $request->rd_account;
        $ssb_account_number = $request->ssb_account_number;
        $getBranchId = getUserBranchId(Auth::user()->id);
        $branchId = $getBranchId->id;
        $getBranchCode = getBranchCode($branchId);
        $branchCode = $getBranchCode->branch_code;
        $customer = $this->repository->getAllMember()->whereId($customerId)->whereStatus(1)->whereIsDeleted(0)->first();
        if (!empty($customer)) {
            $customerName = $customer->first_name . ' ' . $customer->last_name;
        }
        $ssb_amount = $request->ssb_amount;
        $associateSettings = $this->repository->getAllCompanies()->whereStatus('1')->whereHas('companyAssociate', function ($query) {
            $query->whereStatus('1')->select(['id', 'status', 'company_id']);
        })->first(['id', 'status']);
        $companyId = $associateSettings->id;
        $memberCompanyCount = $this->repository->getAllMemberCompany()->whereCustomerId($customerId)/*->whereCompanyId($companyId)*/->whereStatus(1)->whereIsDeleted(0)->count();
        $membercompany = $this->repository->getAllMemberCompany()->whereCustomerId($customerId)->whereCompanyId($companyId)->whereStatus(1)->whereIsDeleted(0)->first();
        $memberId = $membercompany ? $membercompany->id : NULL;
        // check if member not have any investment or not
        $notHaveMemberInvestment = $this->repository->getAllMemberinvestments()->whereCustomerId($customerId)->whereIsDeleted('0')->exists();
        // check if member not have any investment or not
        $planAssociateSettings = $this->repository->getAllPlans()->wherePlanCategoryCode('S')->whereStatus('1')->whereCompanyId($companyId)->first(['id', 'company_id', 'status', 'plan_category_code', 'plan_code', 'deposit_head_id']);
        // $FaCode = FaCode::whereCompanyId($companyId)->whereStatus('1')->orderBy('code', 'asc')->first(['id', 'name', 'code', 'status', 'company_id', 'slug']);
        $FaCode = FaCode::whereCompanyId($companyId)->whereStatus('1')->orderBy('code', 'asc')->get(['id', 'name', 'code', 'status', 'company_id', 'slug']);
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
        if (in_array('Associate RD Account Investment required', auth()->user()->getPermissionNames()->toArray())) {
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
            if($request->payment_mode != null){
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
        }
        /*
        $dataMsg['msg'] = 'Associate not created.Check your fields';
        if ($request -> ssb_account == 1) {
            // ssb account no exits or not(pass member id and ssb account no)
            $ssbAccountDetail = getInvestmentAccount($memberId, $request -> ssb_account_number);
            if (!empty($ssbAccountDetail)) {
                $investmentAccountNoSsb = $request -> ssb_account_number;
                $ssbAccountDetail = getMemberSsbAccountDetail($customerId);
                $ssbAccountId = $ssbAccountDetail -> id;
            } else {
                $dataMsg['msg_type'] = 'error';
                $dataMsg['msg'] = 'SSB account number wrong.';
                $dataMsg['reciept_generate '] = 'no';
                $dataMsg['receipt_id'] = 0;
                $dataMsg['errormsg'].= 'SSB account number wrong.<br>';
                $form++;
                $errorCount++;
            }
        }
        */
        if ($request->ssb_account == 0) {
            // ssb account no exits or not(pass member id and ssb account no)
            $ssbAccountDetail = getMemberCompanySsbAccountDetail($customerId, $companyId);
            if (!empty($ssbAccountDetail)) {
                $dataMsg['msg_type'] = 'error';
                $dataMsg['msg'] = 'SSB account already exists!.';
                $dataMsg['reciept_generate '] = 'no';
                $dataMsg['receipt_id'] = 0;
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
                $dataMsg['receipt_id'] = 0;
                $dataMsg['errormsg'] .= 'Cheque already used select another cheque.<br>';
                $form++;
                $errorCount++;
            } else {
                $getamount = ReceivedCheque::where('id', $request->cheque_id)->first(['id', 'amount']);
                if ($getamount->amount != number_format((float) $request->rd_amount, 4, '.', '')) {
                    $dataMsg['msg_type'] = 'cheque_error';
                    $dataMsg['msg'] = 'RD amount is not equal to cheque amount.';
                    $dataMsg['reciept_generate '] = 'no';
                    $dataMsg['receipt_id'] = 0;
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
                    'ssb_account' => $ssb_account_number ?? 0, // $investmentAccount
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
                if($memberCompanyCount == 0){
                    // if((!$ssb_account_number) && (!$notHaveMemberInvestment)){
                    if(!$ssb_account_number){
                        $amountMi = 10;
                        $amountStn = 90;
                        $amountArray = array('1' => $amountMi, '2' => $amountStn);
                        $typeArray = array('1' => 1, '2' => 2);
                        $receipts_for = 1;
                        $createRecipt = CommanTransactionsController::createPaymentRecipt(0, 0, $customerId, $branchId, $branchCode, $amountArray, $typeArray, $receipts_for, $account_no = '0');
                        $receipt_id = $createRecipt;
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
                        $created_by = 2;
                        $created_by_id = Auth::user()->id;
                        $payment_type = 'CR';
                        $payment_mode = 0; // Cash Payment Mod
                        $currency_code = 'INR';
                        $typeMI = 1;
                        $sub_typeMI = 11;
                        $head_idM1 = 55; // MEMBERSHIP FEES-10/- Head id in Account Head
                        $head_idM2 = 28; // CASH IN HAND Head id in Account Head
                        $desMI = 'Cash received from member ' . $customer->first_name . ' ' . $customer->last_name . '(' . $customer->member_id . ') through MI charge';
                        $desMIDR = 'Cash A/c Dr 10/-';
                        $desMICR = 'To ' . $customer->first_name . ' ' . $customer->last_name . '(' . $customer->member_id . ') A/c Cr 10/-';
                        $daybookMI = CommanTransactionsController::createBranchDayBookNew($refId, $branchId, $typeMI, $sub_typeMI, $type_id, $customer->associate_id, $memberId, $branchId_to = NULL, $branchId_from = NULL, $amountMi, $desMI, $desMIDR, $desMICR, $payment_type, $payment_mode, $currency_code, $v_no = NULL, $ssb_account_id_from = NULL, $cheque_no = NULL, $transction_no = NULL, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $type_transaction_id = NULL, $companyId);

                        $allTranMI = CommanTransactionsController::headTransactionCreate($refId, $branchId, $bank_id, $bank_ac_id, $head_idM1, $typeMI, $sub_typeMI, $type_id, 1, $memberId, $branchId_to = NULL, $branchId_from = NULL, $amountMi, $desMI, 'CR', $payment_mode, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $type_transaction_id, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $companyId);

                        $allTranMI2 = CommanTransactionsController::headTransactionCreate($refId, $branchId, $bank_id, $bank_ac_id, $head_idM2, $typeMI, $sub_typeMI, $type_id, 1, $memberId, $branchId_to = NULL, $branchId_from = NULL, $amountMi, $desMI, 'DR', $payment_mode, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $type_transaction_id, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $companyId);
                        $typeSTN = 1;
                        $sub_typeSTN = 12;
                        $head3STN = 34;
                        $head3STN2 = 28;
                        $desSTN = 'Cash received from member ' . $customer->first_name . ' ' . $customer->last_name . '(' . $customer->member_id . ') through STN charge';
                        $desSTNDR = 'Cash A/c Dr 90/-';
                        $desSTNCR = 'To ' . $customer->first_name . ' ' . $customer->last_name . '(' . $customer->member_id . ') A/c Cr 90/-';
                        $daybookMI = CommanTransactionsController::createBranchDayBookNew($refId, $branchId, $typeSTN, $sub_typeSTN, $type_id, $customer->associate_id, $memberId, $branchId_to = NULL, $branchId_from = NULL, $amountStn, $desSTN, $desSTNDR, $desSTNCR, $payment_type, $payment_mode, $currency_code, $v_no = NULL, $ssb_account_id_from = NULL, $cheque_no = NULL, $transction_no = NULL, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $type_transaction_id = NULL, $companyId);

                        $allTranSTN = CommanTransactionsController::headTransactionCreate($refId, $branchId, $bank_id, $bank_ac_id, $head3STN, $typeSTN, $sub_typeSTN, $type_id, 1, $memberId, $branchId_to = NULL, $branchId_from = NULL, $amountStn, $desSTN, 'CR', $payment_mode, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $type_transaction_id, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $companyId);

                        $allTranSTN2 = CommanTransactionsController::headTransactionCreate($refId, $branchId, $bank_id, $bank_ac_id, $head3STN2, $typeSTN, $sub_typeSTN, $type_id, 1, $memberId, $branchId_to = NULL, $branchId_from = NULL, $amountStn, $desSTN, 'DR', $payment_mode, $currency_code, $v_no, $ssb_account_id_from, $cheque_no, $transction_no, $entry_date, $entry_time, $created_by, $created_by_id, $created_at, $updated_at, $type_transaction_id, $jv_unique_id, $ssb_account_id_to, $ssb_account_tran_id_to, $ssb_account_tran_id_from, $cheque_type, $cheque_id, $companyId);
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
                $dataInvest['payment_mode'] = 0;
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
                $created_by = 2;
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
            if(isset($ssb_account_number) && !empty($ssb_account_number)){
                $membercompanyssb_account_numberupdate = $this->repository->getAllMemberCompany()->whereId($memberId)->update(['ssb_account'=>$ssb_account_number]);
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
                // $maturityAmountVal  = number_format($maturityAmountVal,2);
                // dd($maturityAmountVal);
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
                if($request->payment_mode == 1){
                    $received_cheque_id = $request->cheque_id;
                    $chequeDetail = ReceivedCheque::where('id', $request->cheque_id)->first();
                    $cheque_deposit_bank_id = $chequeDetail->deposit_bank_id;
                    $cheque_deposit_bank_ac_id = $chequeDetail->deposit_account_id;
                    $invPaymentMode['cheque_date'] = date("Y-m-d", strtotime(convertDate($request->rd_cheque_date)));
                    $payment_mode = 1;
                    $rdPayDate = date("Y-m-d", strtotime(convertDate($request->rd_cheque_date)));
                }elseif($request->payment_mode == 2){
                    $payment_mode = 3;
                    $online_deposit_bank_id = $request->rd_online_bank_id;
                    $online_deposit_bank_ac_id = $request->rd_online_bank_ac_id;
                    $rdPayDate = date("Y-m-d", strtotime(convertDate($request->rd_online_date)));
                }elseif($request->payment_mode == 3){
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
                            $dataInvestrd['payment_mode'] = $payment_mode;
                            $dataInvestrd['interest_rate'] = $rate;
                            $dataInvestrd['current_balance'] = $request->rd_amount;
                            $dataInvestrd['created_at'] = $request->created_at;
                            $res = $this->repository->CreateMemberinvestments($dataInvestrd);
                            $investmentId = $res->id;
                        } else {
                            $dataMsg['msg_type'] = 'error';
                            $dataMsg['msg'] = 'Your SSB account does not have a sufficient balance.';
                            $dataMsg['reciept_generate '] = $isReceipt;
                            $dataMsg['receipt_id'] = $receipt_id;
                            $dataMsg['errormsg'] .= 'Your SSB account does not have a sufficient balance.<br>';
                        }
                    } else {
                        $dataMsg['msg_type'] = 'error';
                        $dataMsg['msg'] = 'You does not have SSB account';
                        $dataMsg['reciept_generate '] = $isReceipt;
                        $dataMsg['receipt_id'] = $receipt_id;
                        $dataMsg['errormsg'] .= 'You does not have SSB account.<br>';
                    }
                }else{
                    $dataInvestrd['plan_id'] = $planIdRd;
                    $dataInvestrd['form_number'] = $formNumber;
                    $dataInvestrd['member_id'] = $memberId;
                    $dataInvestrd['customer_id'] = $customerId;
                    $dataInvestrd['branch_id'] = $branchId;
                    $dataInvestrd['account_number'] = $investmentAccountNoRd;
                    $dataInvestrd['mi_code'] = $miCodeRd;
                    $dataInvestrd['maturity_date'] = $maturityDate->format('Y-m-d');
                    $dataInvestrd['associate_id'] = 1;
                    $dataInvestrd['payment_mode'] = $payment_mode;
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
                $daybookRefRD = CommanTransactionsController::createBranchDayBookReferenceNew($request->rd_amount, $globaldate);
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
                $created_by = 2;
                $created_by_id = Auth::user()->id;
                $planDetail = getPlanDetail($planIdRd,$companyId);
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
                    $head411 = $getBHead->account_head_id;
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
            if(isset($rd_account) && !empty($rd_account)){
                $membercompanyrd_accountupdate = $this->repository->getAllMemberCompany()->whereId($memberId)->update(['rd_account'=>$investmentId]);
            }
            if ($memberId) {
                $dataAssociate['associate_form_no'] = $request->form_no;
                $dataAssociate['associate_status'] = 0;
                $dataAssociate['associate_branch_id'] = $branchId;
                $dataAssociate['associate_branch_code'] = $branchCode;
                //---------------- Add branch field -----------
                $dataAssociate['associate_branch_id_old'] = $branchId;
                $dataAssociate['role_id'] = 5;
                $dataAssociate['associate_branch_code_old'] = $branchCode;
                $dataAssociate['associate_senior_code'] = $request->senior_code;
                $dataAssociate['associate_senior_id'] = $request->senior_id;
                $dataAssociate['current_carder_id'] = $request->current_carder;
                $dataAssociate['ssb_account'] = ($ssb_account == 0 || $ssb_account == NULL ) ? $ssb_account_number : NULL;
                $dataAssociate['rd_account'] = !empty($request->rd_first_percentage) ? $investmentAccountNoRd : NULL;
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
                    $receipt_id = $createRecipt;
                    $isReceipt = 'yes';
                } elseif ($request->rd_account == 0 && $request->ssb_account == 1 && $rdset == 1) {
                    $amountArray1 = array('1' => $request->rd_amount);
                    $typeArray = array('1' => 2);
                    $receipts_for = 4;
                    $createRecipt = CommanTransactionsController::createPaymentRecipt(0, 0, $customerId, $branchId, $branchCode, $amountArray1, $typeArray, $receipts_for, $account_no = '0');
                    $receipt_id = $createRecipt;
                    $isReceipt = 'yes';
                } elseif ($request->rd_account == 1 && $request->ssb_account == 0 && $rdset == 1) {
                    $amountArray1 = array('1' => $request->ssb_amount);
                    $typeArray = array('1' => 1);
                    $receipts_for = 4;
                    $createRecipt = CommanTransactionsController::createPaymentRecipt(0, 0, $customerId, $branchId, $branchCode, $amountArray1, $typeArray, $receipts_for, $account_no = '0');
                    $receipt_id = $createRecipt;
                    $isReceipt = 'yes';
                } elseif ($request->ssb_account == 0) {
                    $amountArray1 = array('1' => $request->ssb_amount);
                    $typeArray = array('1' => 1);
                    $receipts_for = 4;
                    $createRecipt = CommanTransactionsController::createPaymentRecipt(0, 0, $customerId, $branchId, $branchCode, $amountArray1, $typeArray, $receipts_for, $account_no = '0');
                    $receipt_id = $createRecipt;
                    $isReceipt = 'yes';
                }
                $dataMsg['msg_type'] = 'success';
                $dataMsg['msg'] = 'Associate Details Updated Successfully';
                $dataMsg['reciept_generate '] = $isReceipt;
                $dataMsg['receipt_id'] = $receipt_id;
                $dataMsg['form_no'] = $request->form_no;
                $dataMsg['senior_code'] = $request->senior_code;
                $dataMsg['senior_id'] = $request->senior_id;
                $dataMsg['current_carder'] = $request->current_carder;
                $dataMsg['ssb_form_no'] = $request->ssb_form_no;
            }
            DB::commit();
            // DB::rollback();
        } catch (\Exception $ex) {
            DB::rollback();
            $dataMsg['msg_type'] = 'error';
            $dataMsg['msg'] = $ex->getMessage();
            $dataMsg['line'] = $ex->getLine();
            $dataMsg['reciept_generate '] = 0;
            $dataMsg['receipt_id'] = 0;
        }
        return response()->json(compact('dataMsg'));
    }
    public function show($id)
    {
        //
    }
    public function edit($id)
    {
        //
    }
    public function update(Request $request, $id)
    {
        //
    }
    public function destroy($id)
    {
        //
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
    public function getCustomerData(Request $request)
    {
        $getBranchId = getUserBranchId(Auth::user()->id);
        $branchId = $getBranchId->id;
        $branchCode = $getBranchId->branch_code;
        $customerId = $request->code;
        $plan = $this->repository->getAllCompanies()->whereStatus('1')->whereDoesntHave('plans', function ($query) {
            $query->wherePlanCategoryCode('S')->whereStatus('1')->select(['id', 'plan_category_code']);
        })->count('id');
        if ($plan > 0) {
            return Response:: json(['view' => 'Plan Not Exists', 'msg_type' => 'error3']);
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
        $associateSettings = $this->repository->getAllCompanies()->whereStatus('1') /*->with('companyAssociate')*/->whereHas('companyAssociate', function ($query) {
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
        $tenureData = $this->repository->getAllPlanTenures()->whereTenure('60')->whereHas('plans',function ($q) use ($companyId) {
            $q->whereCompanyId($companyId)->wherePlanCategoryCode('M')->whereStatus('1')->wherePlanSubCategoryCode(NULL)->whereHybridType(NULL);
        })->first()->toArray();
        $tenure = $tenureData['tenure'] ?? '0';
        $defaultMemberInvesment = Memberinvestments::wherePlanId($tenureData['plan_id'])->whereCompanyId($companyId)->whereCustomerId($customer->id)->get(['id','plan_id','company_id','customer_id']);
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
                'rd_account_number' =>  $defaultMemberInvesment->first()->id??'0',
                'nomineeDetail' => $nomineeDetail,
                'tenure' => $tenure,
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
                    'receipt_id' => $recipt ? $recipt->id : '0',
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
        $array = ['id', 'first_name', 'last_name', 'mobile_no', 'address', 'current_carder_id', 'associate_status', 'is_block', 'associate_no'];
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
        return Response::json($return_array);
    }
    public function getCarderAssociate(Request $request)
    {
        if ($request->id > 1) {
            $carde = Carder::where('id', '<', $request->id)->where('status', 1)->where('is_deleted', 0)->limit(1)->get(['id', 'name', 'short_name']);
        } else {
            $carde = Carder::where('id', '<=', $request->id)->where('status', 1)->where('is_deleted', 0)->limit(1)->get(['id', 'name', 'short_name']);
        }
        return Response::json(compact('carde'));
    }

}