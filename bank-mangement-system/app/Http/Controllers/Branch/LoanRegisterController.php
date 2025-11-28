<?php
namespace App\Http\Controllers\Branch;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{Loanscoapplicantdetails, LoanTenure, Member, MemberCompany, Memberloans, Loanapplicantdetails, Loansguarantordetails, SavingAccount, Memberinvestments, Grouploans, LoanAgainstDeposit, MemberIdProof,Loaninvestmentmembers};
use Session;
use Auth;
use Investment;
use DB;
use App\Http\Controllers\Branch\LoanController;
use App\Http\Controllers\Branch\CommanTransactionsController;
use Carbon\Carbon;
use App\Services\ImageUpload;

class LoanRegisterController extends Controller
{
    /**
     * Instantiate a new controller instsance. hello alpana mam
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Loan Registration View
     */

    public function create()
    {
        $branchhh = Auth::user()->id;
        if (!in_array('Register Loan', auth()->user()->getPermissionNames()->toArray())) {
            return redirect()->route('branch.dashboard');
        }
        $data['title'] = "Loan Registrations";
        $currglobaldate = Session::get('created_at');
        $getCompanies = getCompanyBranchWise(Auth::user()->branches->id);
        $data['loans'] = \App\Models\LoanTenure::wherehas('loan_tenure_plan')->where('status', 1)->whereIn('company_id', $getCompanies)->get();


        return view('templates.branch.loan_register.assign', $data);
    }

    public function getCustomerEmployeeDetails(Request $request)
    {
        $getDetails = Member::select('id', 'employee_id', 'is_employee', 'member_id')->with('getEmployeeDetails')->where('member_id', $request->customerId)->first();

        // dd($getDetails->getEmployeeDetails->designation);
        if ($getDetails->is_employee == '1') {
            $data = $getDetails->getEmployeeDetails;
            $dataDesignation = $getDetails->getEmployeeDetails->designation;
            return response()->json(['data' => $data, 'code' => '1', 'dataDesignation' => $dataDesignation]);
        } else {
            return response()->json(['code' => '0']);

        }

    }

    // public function getCustomer(Request $request)
    // {
    //     $applicationDate = date('Y-m-d', strtotime(convertDate($request->applicationDate)));
    //     // $after15Days = Carbon::parse($applicationDate)->addDays(15);
    //     $after15Days = Carbon::createFromFormat('Y-m-d', $applicationDate)->addDays(15);

    //     $companyId = $request->companyId;
    //     $after15Days = date('Y-m-d', strtotime($after15Days));
    //     $member = '';
    //     $checkMember = '';
    //     $newUser = false;
    //     $checkMemberInOtherCompany = false;
    //     $data = '';
    //     $response = [];

    //     $member = Member::select('id', 'member_id', 'first_name', 'last_name', 'associate_no', 'father_husband', 'dob', 'marital_status', 'email', 'mobile_no', 'address', 'occupation_id', 'current_carder_id', 'status', 'is_block', 'is_blacklist_on_loan', 'signature', 'photo')
    //         ->with('occupation:id,name', 'getCarderNameCustom:id,name', 'memberCompany:id,customer_id', 'memberIdProofs:id,member_id,first_id_no,first_id_type_id,second_id_no,second_id_type_id')
    //         ->with([
    //             'customerInvestment' => function ($q) use ($after15Days, $request, $companyId) {
    //                 $q->when($request->loantype == '4', function ($q) use ($after15Days, $companyId) {
    //                     $q->select('id', 'member_id', 'customer_id', 'tenure', 'created_at', 'plan_id', 'account_number', 'current_balance', 'company_id','maturity_date')->where('is_mature', 1)->where('maturity_date', '>', $after15Days)->where('company_id', $companyId)->whereHas('loan_against_deposits', function ($q) {
    //                         $q->select('*')->whereRaw('round(member_investments.tenure * 12) = loan_against_deposits.tenure')->where('status', 1);
    //                     })->whereDoesntHave('demandadvice', function ($q) {
    //                         $q->where('is_reject', '0')->where('is_deleted', '0');
    //                     })
    //                         ->whereDoesntHave('loanAgainstPlan', function ($q) {
    //                             $q->whereHas('memberLoans', function ($q) {
    //                                 $q->whereNotIn('status', [3, 5]);
    //                             });
    //                         });
    //                     ;
    //                 });
    //             },
    //             'checkMemberLoanAgainstExist'
    //         ])
    //         ->where('member_id', $request->customerId)
    //         ->first();

    //     $memberExist = Member::whereMemberId($request->customerId)->with('memberCurrentLoan')->first();
    //     $loanExistdata = Member::where('member_id', $request->customerId)
    //     ->with(['memberCurrentLoan' => function ($query) {
    //         $query->whereNotIn('status', [0, 1, 6, 7])
    //             ->whereHas('loan', function ($q) {
    //             $q->whereIn('loan_category', [4]); 
    //         });
    //     }])
    //     ->first();
    //     // $loanExist = isset($loanExistdata->memberCurrentLoan) ? $loanExistdata->memberCurrentLoan()->count() : 0;
    //     $loanExist = 0;


    //     if ($memberExist->is_blacklist_on_loan == 1) {
    //         // $view = ['msg_type' => 'already', 'status' => '8'];
    //     }
    //     if (!empty($member)) {
    //         if ($member->status == '0' || $member->is_blacklist_on_loan == '1') {
    //             $view = ['msg_type' => 'Customer is Inactive. Please contact administrator!'];
    //         } else if ($member->signature == null || $member->photo == null) {
    //             $view = ['msg_type' => 'Please upload Photo and Signature of Customer and  Register loan again'];
    //         } else {
    //             if ($member->is_block == '1') {
    //                 $view = ['msg_type' => 'Customer is Inactive. Please Upload Signature and Photo.'];
    //             } else {
    //                 $checkMember = MemberCompany::where('customer_id', $member->id)->where('company_id', $request->companyId)->first();
    //                 $checkMemberInOtherCompany = MemberCompany::where('customer_id', $member->id)->exists();
    //                 $newUser = isset($checkMember->id);

    //                 $member_dob = date('Y-m-d', strtotime($member->dob));
    //                 $today_date = date('Y-m-d');
    //                 $diff = abs(strtotime($today_date) - strtotime($member_dob));
    //                 $years = floor($diff / (365 * 60 * 60 * 24));

    //                 $stateId = Auth::user()->branch->state_id;

    //                 $data = checkGstData($request->companyId, $stateId, $applicationDate);
    //                 // if(isset($memberExist->memberCurrentLoan) && $request->loantype != '4')    // new
    //                 // if(isset($member['checkMemberLoanExist']) && $request->loantype != '4')
    //                 if (isset($memberExist->memberCurrentLoan) && $memberExist->memberCurrentLoan->status == '0' && $request->loantype != '4') {
    //                     $view = ['msg_type' => 'already', 'status' => '0'];
    //                 } else if (isset($memberExist->memberCurrentLoan) && $memberExist->memberCurrentLoan->status == '1' && $request->loantype != '4') {
    //                     $view = ['msg_type' => 'already', 'status' => '1', 'ac' => $memberExist->memberCurrentLoan->account_number];
    //                 }
    //                 // else if (isset($memberExist->memberCurrentLoan) && $memberExist->memberCurrentLoan->status == '2' && $request->loantype != '4') {
    //                 //     $view = ['msg_type' => 'already', 'status' => '2', 'ac' => $memberExist->memberCurrentLoan->account_number];
    //                 // }
    //                 // else if (isset($memberExist->memberCurrentLoan) && $memberExist->memberCurrentLoan->status == '4' && $request->loantype != '4') {
    //                 //     $view = ['msg_type' => 'already', 'status' => '4', 'ac' => $memberExist->memberCurrentLoan->account_number];
    //                 // }
    //                 else if (isset($memberExist->memberCurrentLoan) && $memberExist->memberCurrentLoan->status == '6' && $request->loantype != '4') {
    //                     $view = ['msg_type' => 'already', 'status' => '6'];
    //                 } else if (isset($memberExist->memberCurrentLoan) && $memberExist->memberCurrentLoan->status == '7' && $request->loantype != '4') {
    //                     $view = ['msg_type' => 'already', 'status' => '7', 'ac' => $memberExist->memberCurrentLoan->account_number];
    //                 } else {
    //                     $view = ['view' => view('templates.branch.loan_register.partials.member_detail', ['memberData' => $member])->render(), 'msg_type' => 'success', 'checkMember' => $checkMember, 'newUser' => $newUser, 'checkMemberInOtherCompany' => $checkMemberInOtherCompany, 'gstData' => $data['gstData'], 'gstFileChargeData' => $data['gstFileChargeData'], 'gstEcsChargeData' => $data['gstEcsChargeData'], 'member' => $member, 'age' => $years ,'loanExist' => $loanExist];
    //                 }
    //             }
    //         }

    //     } else {
    //         $view = ['msg_type' => 'No Record Found'];
    //     }
    //     return response()->json($view);





    // }

    public function getCustomer(Request $request)
    {
        // dd($request->all());
        $applicationDate = date('Y-m-d', strtotime(convertDate($request->applicationDate)));
        // $after15Days = Carbon::parse($applicationDate)->addDays(15);
        $after15Days = Carbon::createFromFormat('Y-m-d', $applicationDate)->addDays(15);

        $companyId = $request->companyId;
        $after15Days = date('Y-m-d', strtotime($after15Days));
        $member = '';
        $checkMember = '';
        $newUser = false;
        $checkMemberInOtherCompany = false;
        $data = '';
        $response = [];

        $member = Member::select('id', 'member_id', 'first_name', 'last_name', 'associate_no', 'father_husband', 'dob', 'marital_status', 'email', 'mobile_no', 'address', 'occupation_id', 'current_carder_id', 'status', 'is_block', 'is_blacklist_on_loan', 'signature', 'photo')
            ->with('occupation:id,name', 'getCarderNameCustom:id,name', 'memberCompany:id,customer_id', 'memberIdProofs:id,member_id,first_id_no,first_id_type_id,second_id_no,second_id_type_id', 'checkMemberLoanAgainstExistNew')
            ->with([
                'customerInvestment' => function ($q) use ($after15Days, $request, $companyId) {
                    $q->when($request->loantype == '4', function ($q) use ($after15Days, $companyId) {
                        $q->select('id', 'member_id', 'customer_id', 'tenure', 'created_at', 'plan_id', 'account_number', 'current_balance', 'company_id', 'maturity_date')->where('maturity_date', '>', $after15Days)->where('company_id', $companyId)->whereHas('loan_against_deposits', function ($q) {
                            $q->select('*')->whereRaw('round(member_investments.tenure * 12) = loan_against_deposits.tenure')->where('status', 1);
                        })->whereDoesntHave('demandadvice', function ($q) {
                            $q->where('is_reject', '0')->whereIn('status', [0, 1])->where('is_deleted', '0');
                        })
                            ->whereDoesntHave('loanAgainstPlan', function ($q) {
                                $q->whereHas('memberLoans', function ($q) {
                                    $q->whereNotIn('status', [3, 5, 8]);
                                });
                            });
                            // ->whereHas('loanAgainstPlan', function ($query) {
                            //     $query->select('plan_id')
                            //           ->from('loan_investment_plans')
                            //           ->whereIn('member_loan_id', function ($q) {
                            //               $q->select('id')
                            //                 ->from('member_loans')
                            //                 ->whereIn('status', [3, 5, 8]);
                            //           });
                            // });
                    });
                }
            ])
            ->where('member_id', $request->customerId)
            ->first();
            // dd($member);
        if (!isset($member->customerInvestment[0])) {
            $chkk = Member::select('id', 'member_id', 'first_name', 'last_name', 'associate_no', 'father_husband', 'dob', 'marital_status', 'email', 'mobile_no', 'address', 'occupation_id', 'current_carder_id', 'status', 'is_block', 'is_blacklist_on_loan', 'signature', 'photo')
                ->with([
                    'customerInvestment' => function ($q) use ($after15Days, $request, $companyId) {
                        $q->when($request->loantype == '4', function ($q) use ($after15Days, $companyId) {
                            $q->select('id', 'member_id', 'customer_id', 'tenure', 'created_at', 'plan_id', 'account_number', 'current_balance', 'company_id', 'maturity_date')->where('company_id', $companyId)->whereHas('loan_against_deposits', function ($q) {
                                $q->select('*')->whereRaw('round(member_investments.tenure * 12) = loan_against_deposits.tenure')->where('status', 1);
                            })->with(['demandadvice' => function ($q) {
                                $q->where('is_reject', '0')
                                    ->whereIn('status', [0, 1])
                                    ->where('is_deleted', '0');
                            }])
                                ->whereDoesntHave('loanAgainstPlan', function ($q) {
                                    $q->whereHas('memberLoans', function ($q) {
                                        $q->whereIn('status', [3, 5, 8]);
                                    });
                                });
                        });
                    }
                ])
                ->where('member_id', $request->customerId)
                ->first();
                if (isset($chkk->customerInvestment[0])) {
                    foreach ($chkk->customerInvestment as $investment) {
                        if (isset($investment['demandadvice'])) {
                            $pen_app = ($investment['demandadvice']['status'] == 0) ? 'pending' : 'approved';
                            $view = ['msg_type' => 'dem_issue', 'msg' => "Demand exists for this customer with status $pen_app"];
                            return response()->json($view);
                        }
                        if ($investment->maturity_date < $after15Days) {
                            $view = ['msg_type' => 'dem_issue', 'msg' => "Your maturity date has passed or is within the upcoming 15 days, so you can't apply for this loan."];
                            return response()->json($view);
                        }
                    }
                }         
        }
        // loan against investment condition set on 29-03-2024 as per sachine sir instruction
        $loantypes = is_array($request['loantype']) ? $request['loantype'] : [$request['loantype']];

        $memberExist = Member::where('member_id', $request->customerId)
            ->with(['memberCurrentLoan' => function ($query) use ($loantypes) {
                $query->whereNotIn('status', [0, 1, 6, 7])
                    ->whereHas('loan', function ($q) use ($loantypes) {
                        $q->whereIn('loan_category', $loantypes); 
                    });
            }])
            ->first(); 


        // dd( $memberExist);
        $loanExistdata = Member::where('member_id', $request->customerId)
        ->with(['memberCurrentLoan' => function ($query) {
            $query->whereNotIn('status', [0, 1, 6, 7])
                ->whereHas('loan', function ($q) {
                $q->whereIn('loan_category', [4]); 
            });
        }])
        ->first();
        // $loanExist = isset($loanExistdata->memberCurrentLoan) ? $loanExistdata->memberCurrentLoan()->count() : 0;
        $loanExist =  0;

        // dd($loanExist);
        

        if ($memberExist->is_blacklist_on_loan == 1) {
            // $view = ['msg_type' => 'already', 'status' => '8'];
        }
        if (!empty($member)) {
            if ($member->status == '0' || $member->is_blacklist_on_loan == '1') {
                $view = ['msg_type' => 'Customer is Inactive. Please contact administrator!'];
            } else if ($member->signature == null || $member->photo == null) {
                $view = ['msg_type' => 'Please upload Photo and Signature of Customer and  Register loan again'];
            } else {
                if ($member->is_block == '1') {
                    $view = ['msg_type' => 'Customer is Inactive. Please Upload Signature and Photo.'];
                } else {
                    $checkMember = MemberCompany::where('customer_id', $member->id)->where('company_id', $request->companyId)->first();
                    $checkMemberInOtherCompany = MemberCompany::where('customer_id', $member->id)->exists();
                    $newUser = isset($checkMember->id);

                    $member_dob = date('Y-m-d', strtotime($member->dob));
                    $today_date = date('Y-m-d');
                    $diff = abs(strtotime($today_date) - strtotime($member_dob));
                    $years = floor($diff / (365 * 60 * 60 * 24));

                    $stateId = Auth::user()->branch->state_id;

                    $data = checkGstData($request->companyId, $stateId, $applicationDate);
                    // if(isset($memberExist->memberCurrentLoan) && $request->loantype != '4')    // new
                    // if(isset($member['checkMemberLoanExist']) && $request->loantype != '4')
                    if (isset($memberExist->memberCurrentLoan) && $memberExist->memberCurrentLoan->status == '0' && $request->loantype != '4') {
                        $view = ['msg_type' => 'already', 'status' => '0'];
                    } else if (isset($memberExist->memberCurrentLoan) && $memberExist->memberCurrentLoan->status == '1' && $request->loantype != '4') {
                        $view = ['msg_type' => 'already', 'status' => '1', 'ac' => $memberExist->memberCurrentLoan->account_number];
                    } else if (isset($memberExist->memberCurrentLoan) && $memberExist->memberCurrentLoan->status == '2' && $request->loantype != '4') {
                        $view = ['msg_type' => 'already', 'status' => '2', 'ac' => $memberExist->memberCurrentLoan->account_number];
                    }
                    else if (isset($memberExist->memberCurrentLoan) && $memberExist->memberCurrentLoan->status == '4' && $request->loantype != '4' && $request->loantype != '1') {
                        $view = ['msg_type' => 'already', 'status' => '4', 'ac' => $memberExist->memberCurrentLoan->account_number];
                    }
                    else if (isset($memberExist->memberCurrentLoan) && $memberExist->memberCurrentLoan->status == '6' && $request->loantype != '4') {
                        $view = ['msg_type' => 'already', 'status' => '6'];
                    } else if (isset($memberExist->memberCurrentLoan) && $memberExist->memberCurrentLoan->status == '7' && $request->loantype != '4') {
                        $view = ['msg_type' => 'already', 'status' => '7', 'ac' => $memberExist->memberCurrentLoan->account_number];
                    } else {
                        $view = ['view' => view('templates.branch.loan_register.partials.member_detail', ['memberData' => $member])->render(), 'msg_type' => 'success', 'checkMember' => $checkMember, 'newUser' => $newUser, 'checkMemberInOtherCompany' => $checkMemberInOtherCompany, 'gstData' => $data['gstData'], 'gstFileChargeData' => $data['gstFileChargeData'], 'gstEcsChargeData' => $data['gstEcsChargeData'], 'member' => $member, 'age' => $years, 'loanExist' => $loanExist];
                    }
                }
            }
        } else {
            $view = ['msg_type' => 'No Record Found'];
        }
        return response()->json($view);
    }
    public function store(Request $request)
    {
        // dd($request->all());

        $loantype = $request->input('loan_type');
        $loanCategory = \App\Models\Loans::findorfail($request->loanId);
        $loanCategory = $loanCategory->loan_category;
        $type = 'create';
        $BranchId = branchName()->id;
        $branchCode = branchname()->branch_code;

        $stateid = getBranchState(Auth::user()->username);
        $memberDetail = Investment::getMember($request->input('customerId'))->with(['savingAccount:id,account_no,balance,member_id,customer_id'])->first();
        // dd($memberDetail);
        $data = $this->getData($request->all(), $type, $memberDetail->id, $loanCategory);
        $request->request->add(['memberAutoId' => $memberDetail->id, 'created_at' => $request['created_date'], 'associate_id' => $request['acc_member_id']]);
        $faCode = getPlanCode(1);
        $investmentMiCode = getInvesmentMiCodeNew(1, $BranchId);
        $miCodeAdd = (!empty($investmentMiCode))
            ? $investmentMiCode->mi_code + 1 :
            1;
        $miCode = str_pad($miCodeAdd, 5, '0', STR_PAD_LEFT);
        $investmentAccount = $branchCode . $faCode . $miCode;
        $entryTime = date("H:i:s");
        $globaldate = date('Y-m-d', strtotime($request['created_date']));
        Session::put('globaldate', date("Y-m-d " . $entryTime . "", strtotime(convertDate($request['created_date']))));

        $request->request->add(['create_application_date' => $request['created_date']]);
        $memberDetails = MemberCompany::where('customer_id', $request->input('memberAutoId'))->count();
        $customerDatas = Member::where('id', $request->customerId)->first();
        $memberInvestment = Memberinvestments::where('customer_id', $request->input('memberAutoId'))->count();
        $ssb = false;


        DB::beginTransaction();
        try {
            if ($loanCategory != 3) {
                if ($request['newUser'] == "false") {

                    $mData = Investment::registerMember($memberDetail, $request, 'Loan');
                    $data['mi_charge'] = ($memberDetails > 0) ? 0 : 10;
                    $data['stn_charge'] = ($memberDetails > 0) ? 0 : 90;
                    $data['applicant_id'] = $mData['id'];
                    $mId = $mData['id'];
                    $daybookRefRD = CommanTransactionsController::createBranchDayBookReferenceNew(100, $globaldate);
                    if ($memberDetails == 0) {
                        Investment::memberCharges($request['amount'], $request['created_date'], NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, $BranchId, $request['acc_member_id'], $mData, NULL, NULL, 0, NULL, $request->company_id, $daybookRefRD);
                    }
                    
                    $request
                        ->request
                        ->add(['associatemid' => $request['acc_member_id']]);



                    $ssb = Investment::registerSSbRequiredData($request, $mId, 'Loan');
                } else {
                    $mId = MemberCompany::where('customer_id', $memberDetail->id)->where('company_id', $request['company_id'])->first();
                    $checkExist = SavingAccount::where('member_id', $mId->id)->count();
                    if ($checkExist == 0) {

                        $request
                            ->request
                            ->add(['associatemid' => $request['acc_member_id']]);


                        $ssb = Investment::registerSSbRequiredData($request, $mId->id, 'Loan');

                    }
                    $mId = $mId->id;
                    $data['applicant_id'] = $mId;
                }
            }
            switch ($loanCategory) {
                case 1:
                    // if (isset($ssb) && $ssb) {
                    //     if ($ssb['status']) {

                    //         if ($ssb['is_stationary'] && $ssb['status']) {
                    //             $data['stationary_charge'] = 50;

                    //         }
                    //         $data['ssb_id'] = $ssb['data']->id;
                    //     }

                    // } else {
                    //     $ssbGet = SavingAccount::select('id', 'account_no')->where('member_id', $mId)->first();

                    //     $data['ssb_id'] = $ssbGet->id;
                    //     if ($request->ecs_type == 2) {
                    //         $data['ecs_ref_no'] = $ssbGet->account_no;
                    //     }

                    // }

                    if (isset($ssb) && $ssb) {
                        if (($ssb['status'])) {

                            if ($ssb['is_stationary'] && $ssb['status']) {
                                $data['stationary_charge'] = 50;

                            }
                            $data['ssb_id'] = $ssb['data']->id;
                            if ($request->ecs_type == 2) {
                                $data['ecs_ref_no'] = $ssb['data']->account_no;
                            }
                        }

                    } else {
                        $ssbGet = SavingAccount::select('id', 'account_no')->where('member_id', $mId)->first();

                        $data['ssb_id'] = $ssbGet->id;
                        if ($request->ecs_type == 2) {
                            $data['ecs_ref_no'] = $ssbGet->account_no;
                        }

                    }
                    $res = $createdData = Memberloans::create($data);
                    $CollectorAmountData = $this->storeCollectorData($request, $res);
                    $insertedid = $res->id;

                    // for loan log

                    $loanLog = $this->LoanLog($request->all(),$insertedid);


                    $applicantIdFile = $request->file('applicant_id_file');
                    $applicantAddressFile = $request->file('applicant_address_id_file');
                    $applicantIncomeFile = $request->file('applicant_income_file');
                    //echo "<pre>"; print_r($request->all()); die;
                    $applicant = LoanController::applicantDetailData($request->all(), $applicantIdFile, $applicantAddressFile, $applicantIncomeFile, $insertedid, $type);
                    $res = Loanapplicantdetails::create($applicant);
                    $coapplicantIdFile = $request->file('co-applicant_id_file');
                    $coapplicantAddressFile = $request->file('co-applicant_address_id_file');
                    $coapplicantIncomeFile = $request->file('co-applicant_income_file');
                    $coapplicantUnderTakingDoc = $request->file('co-applicant_under_taking_doc');
                    $coApplicant = LoanController::coApplicantDetailData($request->all(), $coapplicantIdFile, $coapplicantAddressFile, $coapplicantIncomeFile, $coapplicantUnderTakingDoc, $insertedid, $type);
                    $res = Loanscoapplicantdetails::create($coApplicant);

                    $guarantorIdFile = $request->file('guarantor_id_file');
                    $guarantorAddressFile = $request->file('guarantor_address_id_file');
                    $guarantorIncomeFile = $request->file('guarantor_income_file');
                    $guarantorOtherFile = $request->file('guarantor_more_upload_file');
                    //$guarantorUnderTakingDoc = $request->file('guarantor_under_taking_doc');
                    $guarantor = LoanController::guarantorDetailData($request->all(), $guarantorIdFile, $guarantorAddressFile, $guarantorIncomeFile, $guarantorOtherFile, $insertedid, $type);
                    $res = Loansguarantordetails::create($guarantor);
                    if ($request['hidden_more_doc'] == 1) {
                        $guarantorMoreDoc = LoanController::uploadGuarantorMoreDoc($request['guarantor_more_doc_title'], $request->file('guarantor_more_upload_file'), $insertedid, 'guarantor', 'moredocument');
                    }
                    break;
                case 3:

                    $request->request->add(['associatemid' => $request['acc_member_id'], 'payment-mode' => 0]);

                    $branchCode = getBranchCode($BranchId);
                    $groupLoanCommonId = groupLoanCommonId($branchCode->branch_code);

                    $groupData['loan_type'] = $request['loanId'];
                    $groupData['branch_id'] = $BranchId;
                    $groupData['group_loan_common_id'] = $groupLoanCommonId;
                    $groupData['associate_member_id'] = $request['group_associate_id'];
                    $groupData['applicant_id'] = $request['group_member_id'];
                    $groupData['amount'] = $request['amount'];
                    $groupData['emi_option'] = $request['emi_option'];
                    $groupData['emi_period'] = $request['emi_period'];
                    $groupData['file_charges'] = $request['file_charge'];
                    $groupData['created_at'] = checkMonthAvailability(date('d'), date('m'), date('Y'), $stateid);
                    $groupData['gsttype'] = ($request['ml_gst_file_status'] == true ? 0 : 1);
                    $groupData['customer_id'] = $memberDetail->id;
                    $groupData['company_id'] = $request['company_id'];
                    if (isset($ssb) && $ssb) {
                        if ($ssb['status']) {

                            if ($ssb['is_stationary'] && $ssb['status']) {
                                $groupData['stationary_charge'] = 50;

                            }
                            $groupData['ssb_id'] = $ssb['data']->id;
                        }

                    }
                    $res = Memberloans::create($groupData);
                    $insertedid = $res->id;
                    $gdata = $createdData = $this->getGroupLoanData($request->all(), $type, $insertedid, $groupLoanCommonId, $ssb);

                    $memberDetail = MemberCompany::where('customer_id', $request['group_leader_m_id'])->first();

                    $resss = Grouploans::where('group_loan_common_id', $groupLoanCommonId)->update(['groupleader_member_id' => $memberDetail->id, 'applicant_id' => $memberDetail->id]);

                    /*$groupLoanMembers = $this->storeGroupLoanMembers($request->all(),$insertedid);*/
                    $applicantIdFile = $request->file('applicant_id_file');
                    $applicantAddressFile = $request->file('applicant_address_id_file');
                    $applicantIncomeFile = $request->file('applicant_income_file');
                    $applicant = LoanController::applicantDetailData($request->all(), $applicantIdFile, $applicantAddressFile, $applicantIncomeFile, $insertedid, $type);
                    $res = Loanapplicantdetails::create($applicant);
                    $coapplicantIdFile = $request->file('co-applicant_id_file');
                    $coapplicantAddressFile = $request->file('co-applicant_address_id_file');
                    $coapplicantIncomeFile = $request->file('co-applicant_income_file');
                    $coapplicantUnderTakingDoc = $request->file('co-applicant_under_taking_doc');
                    $coApplicant = LoanController::coApplicantDetailData($request->all(), $coapplicantIdFile, $coapplicantAddressFile, $coapplicantIncomeFile, $coapplicantUnderTakingDoc, $insertedid, $type);
                    $res = Loanscoapplicantdetails::create($coApplicant);

                    $guarantorIdFile = $request->file('guarantor_id_file');
                    $guarantorAddressFile = $request->file('guarantor_address_id_file');
                    $guarantorIncomeFile = $request->file('guarantor_income_file');
                    $guarantorUnderTakingDoc = $request->file('guarantor_under_taking_doc');
                    //$guarantorOtherFile = $request->file('guarantor_more_upload_file');
                    $guarantor = LoanController::guarantorDetailData($request->all(), $guarantorIdFile, $guarantorAddressFile, $guarantorIncomeFile, $guarantorUnderTakingDoc, $insertedid, $type);
                    $res = Loansguarantordetails::create($guarantor);
                    if ($request['hidden_more_doc'] == 1) {
                        $guarantorMoreDoc = LoanController::uploadGuarantorMoreDoc($request['guarantor_more_doc_title'], $request->file('guarantor_more_upload_file'), $insertedid, 'guarantor', 'moredocument');
                    }
                    break;
                case 2:
                    if (isset($ssb) && $ssb) {
                        if ($ssb['status']) {

                            if ($ssb['is_stationary'] && $ssb['status']) {
                                $data['stationary_charge'] = 50;

                            }
                            $data['ssb_id'] = $ssb['data']->id;
                            if ($request->ecs_type == 2) {
                                $data['ecs_ref_no'] = $ssb['data']->account_no;
                            }
                        }

                    } else {
                        $ssbGet = SavingAccount::select('id', 'account_no')->where('member_id', $mId)->first();

                        $data['ssb_id'] = $ssbGet->id;
                        if ($request->ecs_type == 2) {
                            $data['ecs_ref_no'] = $ssbGet->account_no;
                        }

                    }
                    $res = $createdData = Memberloans::create($data);
                    $CollectorAmountData = $this->storeCollectorData($request, $res);

                    $insertedid = $res->id;

                    $loanLog = $this->LoanLog($request->all(),$insertedid);

                    $applicantIdFile = $request->file('applicant_id_file');
                    $applicantAddressFile = $request->file('applicant_address_id_file');
                    $applicantIncomeFile = $request->file('applicant_income_file');
                    $applicant = LoanController::applicantDetailData($request->all(), $applicantIdFile, $applicantAddressFile, $applicantIncomeFile, $insertedid, $type);
                    $res = Loanapplicantdetails::create($applicant);
                    $guarantorIdFile = $request->file('guarantor_id_file');
                    $guarantorAddressFile = $request->file('guarantor_address_id_file');
                    $guarantorIncomeFile = $request->file('guarantor_income_file');
                    $guarantorUnderTakingDoc = $request->file('guarantor_under_taking_doc');
                    //$guarantorOtherFile = $request->file('guarantor_more_upload_file');
                    $guarantor = LoanController::guarantorDetailData($request->all(), $guarantorIdFile, $guarantorAddressFile, $guarantorIncomeFile, $guarantorUnderTakingDoc, $insertedid, $type);
                    $res = Loansguarantordetails::create($guarantor);
                    if ($request['hidden_more_doc'] == 1) {
                        $guarantorMoreDoc = LoanController::uploadGuarantorMoreDoc($request['guarantor_more_doc_title'], $request->file('guarantor_more_upload_file'), $insertedid, 'guarantor', 'moredocument');
                    }
                    break;
                case 4:
                    if (isset($ssb) && $ssb) {
                        if ($ssb['status']) {

                            if ($ssb['is_stationary'] && $ssb['status']) {
                                $data['stationary_charge'] = 50;

                            }
                            $data['ssb_id'] = $ssb['data']->id;
                            if ($request->ecs_type == 2) {
                                $data['ecs_ref_no'] = $ssb['data']->account_no;
                            }
                        }

                    } else {
                        $ssbGet = SavingAccount::select('id', 'account_no')->where('member_id', $mId)->first();

                        $data['ssb_id'] = $ssbGet->id;
                        if ($request->ecs_type == 2) {
                            $data['ecs_ref_no'] = $ssbGet->account_no;
                        }

                    }
                    $res = $createdData = Memberloans::create($data);
                    $CollectorAmountData = $this->storeCollectorData($request, $res);
                    $insertedid = $res->id;
                    $loanLog = $this->LoanLog($request->all(),$insertedid);
                    $loanInvestmentPlans = LoanController::storeLoanInvestmentPlans($request->all(), $insertedid);
                    $applicantIdFile = $request->file('applicant_id_file');
                    $applicantAddressFile = $request->file('applicant_address_id_file');
                    $applicantIncomeFile = $request->file('applicant_income_file');
                    $applicant = LoanController::applicantDetailData($request->all(), $applicantIdFile, $applicantAddressFile, $applicantIncomeFile, $insertedid, $type);
                    $res = Loanapplicantdetails::create($applicant);
                    break;
            }
            // dd($res);

            
            //event(new UserActivity($createdData, 'Loan Registration', $request));
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            dd($ex->getMessage(), $ex->getLine());
            return back()->with('alert', $ex->getMessage(), $ex->getLine());
        }

        if ($res) {
            // if ($loantype == 'group-loan')
            // {
            //     return redirect()->route('loan.grouploan')
            //         ->with('success', 'Loan details successfully registered!');
            // }
            // else
            // {
            //     return redirect()
            //         ->route('loan.loans')
            //         ->with('success', 'Loan details successfully registered!');
            // }
            return redirect('branch/loan/receipt/' . $insertedid);
            // return redirect()->route('loan.receipt')
        } else {
            return back()
                ->with('alert', 'Problem With Register New Plan');
        }
    }

    // Create loan log update by shahid on 16-03-2024
    public function LoanLog($data,$loan_type,$status=null){
       
        try {
        $stateid = Auth::user()->username;
        $loginid = Auth::user()->id;
        
        $data1['loanId'] = $loan_type ?? null;
        $data1['loan_type'] =$data['loanId'] ?? null;
        $data1['loan_category'] =getLoanData($data['loanId'])->loan_category;
        $data1['loan_name'] = getLoanData($data['loanId'])->slug ?? null;
        $data1['title'] = 'Create';
        $data1['status'] =0;
        $data1['status_changed_date'] = date('Y-m-d') ?? null;
        $data1['created_by'] = $loginid ?? null;
        $data1['user_name'] =$stateid ?? null;
        $data1['created_by_name'] ='Branch';
        $data1['description'] ="Loan application Created by".' '. $stateid  ?? null;
        $data1['is_correction'] = 0;
         
        $logs = \App\Models\LoanLog::create($data1);
        DB::commit();
        return $logs;
        
        } catch (\Exception $ex) {
            DB::rollback();
            dd($ex->getMessage(),$ex->getLine());
            return back()->with('alert', $ex->getMessage());
        }

    }

    public function storeCollectorData($request, $res)
    {
        $collectorData = [
            'type' => 2,
            'type_id' => $res->id,
            'associate_id' => $request['associate_id'] ?? null,
            'status' => 1,
            'created_id' => Auth::user()->id,
            'created_by' => 2,
            'created_at' => date("Y-m-d", strtotime(convertDate($request['created_date']))),
        ];

        \App\Models\CollectorAccount::create($collectorData);

        return $collectorData;
    }

    public function receipt($loanId)
    {
        $data['data'] = Memberloans::findorfail($loanId);
        $data['title'] = 'Loan Receipt';
        return view('templates.branch.loan_register.receipt', $data);
    }


    public function getData($request, $type, $customerId, $loanCategory)
    {

        $BranchId = branchName();
        $stateid = getBranchState(Auth::user()->username);
        //$created_at = checkMonthAvailability(date('d'),date('m'),date('Y'),$stateid);
        $created_at = date("Y-m-d", strtotime(convertDate($request['created_date'])));
        $loantype = $loanCategory;
        $applicationNumber = $this->generateApplicationNumber($request['loanId'], $request['loan_type']);
        switch ($loantype) {
            case "1":
                break;
            case "2":
                $data['emp_code'] = $request['emp_code'];

                break;
            case "3":
                $data['group_activity'] = $request['group_activity'];
                $data['groupleader_member_id'] = $request['group_leader_m_id'];
                $data['group_associate_id'] = $request['group_associate_id'];
                $data['number_of_member'] = $request['number_of_member'];
                $data['group_member_id'] = $request['group_member_id'];
                break;
        }
        if ($type == 'create') {
            $data['loan_type'] = $request['loanId'];
            $data['branch_id'] = $BranchId->id;
            $data['old_branch_id'] = $BranchId->id;
            $data['associate_member_id'] = $request['acc_member_id'];
            $data['application_no'] = $applicationNumber;
            $data['amount'] = $request['loan_amount'];
            //$data['emi_mode'] = $request['emi_mode'];
            $data['ecs_type'] = $request['ecs_type'] ?? 0;
            $data['ecs_charges'] = $request['ecsCharge'] ?? null;
            $data['ecs_charge_igst'] = (isset($request['ecsStatus']) && $request['ecsStatus'] == "false" && is_numeric($request['ecsFileamount']) ? $request['ecsFileamount'] : 0) ?? 0;
            $data['ecs_charge_cgst'] = (isset($request['ecsStatus']) && $request['ecsStatus'] == "true" && isset($request['ecsFileamount']) && is_numeric($request['ecsFileamount'])) ? $request['ecsFileamount'] : 0;
            $data['ecs_charge_sgst'] = (isset($request['ecsStatus']) && $request['ecsStatus'] == "true" && isset($request['ecsFileamount']) && is_numeric($request['ecsFileamount'])) ? $request['ecsFileamount'] : 0;
            $data['ROI'] = $request['interest_rate'];
            $data['emi_option'] = $request['emi_option'];
            $data['emi_period'] = $request['emi_period'];
            $data['emi_amount'] = $request['loan_emi'];
            $data['file_charges'] = $request['file_charge'];
            $data['insurance_charge'] = $request['insurance_charge'];

            $data['insurance_charge_igst'] = (isset($request['gstStatus']) && $request['gstStatus'] == "false" ?
                (is_numeric($request['gstAmount']) ? $request['gstAmount'] : 0.00) : 0.00);


            $data['insurance_cgst'] = ($request['gstStatus'] == "true" ? $request['gstAmount'] : 0);
            $data['insurance_sgst'] = ($request['gstStatus'] == "true" ? $request['gstAmount'] : 0);
            $data['filecharge_igst'] = ($request['gstFileStatus'] == 'false' ? $request['gstFileAmount'] : '0');
            $data['filecharge_sgst'] = ($request['gstFileStatus'] == 'true' ? $request['gstFileAmount'] : '0');
            $data['filecharge_cgst'] = ($request['gstFileStatus'] == 'true' ? $request['gstFileAmount'] : '0');
            $data['gsttype'] = ($request['gstStatus'] == true ? 0 : 1);
            $data['loan_purpose'] = $request['loan_purpose'];
            //  $data['bank_account'] = $request['bank_account'];
            //  $data['ifsc_code'] = $request['ifsc_code'];
            // $data['bank_name'] = $request['bank_name'];
            if ($request['emi_option'] == 1) {
                $data['closing_date'] = date('Y-m-d', strtotime("+" . $request['emi_period'] . " months", strtotime($created_at)));
            } elseif ($request['emi_option'] == 2) {
                $days = $request['emi_period'] * 7;
                $start_date = $created_at;
                $date = strtotime($start_date);
                $date = strtotime("+" . $days . " day", $date);
                $data['closing_date'] = date('Y-m-d', $date);
            } elseif ($request['emi_option'] == 3) {
                $days = $request['emi_period'];
                $start_date = $created_at;
                $date = strtotime($start_date);
                $date = strtotime("+" . $days . " day", $date);
                $data['closing_date'] = date('Y-m-d', $date);
            }
            //$data['created_at'] = $created_at;

            $data['customer_id'] = $customerId;
            $data['company_id'] = $request['company_id'];
            $data['created_at'] = date("Y-m-d", strtotime(convertDate($request['created_date'])));
            return $data;
        } elseif ($type == 'update') {
        }
    }


    public function investmentgetdata($request, $type, $miCode, $investmentAccount, $branch_id, $faCode, $mId)
    {
        $ssbAmount = \App\Models\SsbAccountSetting::where('user_type', 1)->where('plan_type', 1)->whereStatus(1)->first();
        $checkSSbExistinAllCompany = SavingAccount::where('customer_id', $request['memberAutoId'])->exists();
        $data['old_branch_id'] = $branch_id;
        $created_at = date("Y-m-d", strtotime(convertDate($request['created_date'])));
        $data['deposite_amount'] = (!$checkSSbExistinAllCompany) ? $ssbAmount->amount : 0;
        $data['current_balance'] = (!$checkSSbExistinAllCompany) ? $ssbAmount->amount : 0;
        $data['payment_mode'] = 0;
        $data['ssb_account_number'] = $investmentAccount;
        $data['mi_code'] = $miCode;
        $data['account_number'] = $investmentAccount;
        $data['member_id'] = $mId->id;
        $data['plan_id'] = 1;
        $data['form_number'] = NULL;
        $data['customer_id'] = $request['memberAutoId'];
        $data['associate_id'] = $request['acc_member_id'];
        $data['branch_id'] = $branch_id;
        $data['created_at'] = $created_at;
        $data['company_id'] = $request['company_id'];

        return $data;
    }


    public function generateApplicationNumber($loanId, $loanType)
    {
        $branchId = Auth::user()->branches->id;
        $branchCode = Auth::user()->branches->branch_code;
        $newLoanId = (strlen($loanId) < 2) ? str_pad($loanId, 2, '0', STR_PAD_LEFT) : $loanId;
        $getApplicationNumber = ($loanType == 'L')
            ?
            Memberloans::where('branch_id', $branchId)->whereloanType($loanId)->orderByDesc('id')->first()
            :
            Grouploans::where('branch_id', $branchId)->whereloanType($loanId)->orderByDesc('id')->first()
        ;
        $addNumber = 1;
        $addNumber = str_pad($addNumber, 6, '0', STR_PAD_LEFT);
        $applicationNumber = (isset($getApplicationNumber->application_no)) ?
            $getApplicationNumber->application_no + 1 :
            $branchCode . $newLoanId . $addNumber;
        return $applicationNumber;
    }
    public function checkMemberForGroupLoan($customerId, $companyId, $createdAt, $request)
    {


        $ssb = '';
        $mId = '';
        $memberId = '';

        $request['memberAutoId'] = $customerId;
        $request['memberAutoId'] = $customerId;

        $memberDetail = MemberCompany::where('customer_id', $customerId)->count();
        $customerData = Member::where('id', $customerId)->first();

        $checkMemberExistInotherCompany = MemberCompany::where('company_id', $companyId)->where('customer_id', $customerId)->first();

        $globaldate = date('Y-m-d', strtotime(convertdate($createdAt)));
        $BranchId = branchName()->id;

        if (empty($checkMemberExistInotherCompany)) {
            $mData = Investment::registerMember($customerData, $request, 'Loan');
            $data['mi_charge'] = ($memberDetail > 0) ? 0 : 10;
            $data['stn_charge'] = ($memberDetail > 0) ? 0 : 90;
            $data['applicant_id'] = $mData['id'];
            $mId = $mData['id'];
            $daybookRefRD = CommanTransactionsController::createBranchDayBookReferenceNew(100, $globaldate);
            if ($memberDetail == 0) {
                Investment::memberCharges($request['amount'], $globaldate, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, $BranchId, $request['acc_member_id'], $mData, NULL, NULL, 0, NULL, $request['company_id'], $daybookRefRD);
            }
            $memberId = $mId;
            $ssb = Investment::registerSSbRequiredData($request, $mId, 'Loan');
        } else {
            $mId = $checkMemberExistInotherCompany->id;
            $checkExist = SavingAccount::where('member_id', $mId)->first();
            if (empty($checkExist)) {

                $ssb = Investment::registerSSbRequiredData($request, $mId, 'Loan');
                // $ssb = Investment::registerSSbRequiredData($request, $request['is_m_id'], 'Loan');

            } else {
                $ssb = $checkExist->id;
            }
            $ssb = $ssb;

            $memberId = $checkMemberExistInotherCompany->id;
        }


        $returnArray = ['mId' => $mId, 'ssb' => $ssb, 'memberId' => $memberId];
        return $returnArray;

    }


    public function getGroupLoanData($request, $type, $memberLoanId, $groupLoanCommonId, $ssb = NULL)
    {
        DB::beginTransaction();
        try {
            $BranchId = branchName()->id;
            $stateid = getBranchState(Auth::user()->username);
            //$created_at = checkMonthAvailability(date('d'),date('m'),date('Y'),$stateid);
            $created_at = date("Y-m-d", strtotime(convertDate($request['created_date'])));
            $applicationNumber = $this->generateApplicationNumber($request['loanId'], $request['loan_type']);
            foreach ($request['m_id'] as $key => $value) {


                if ($request['m_amount'][$key]) {

                    if (!isset($request['is_m_id'][$key])) {

                        $memberDetailIds = $this->checkMemberForGroupLoan($request['m_id'][$key], $request['company_id'], $request['created_date'], $request);


                        $memberDetailId = $memberDetailIds['memberId'];

                    } else {
                        $memberDetailId = $request['is_m_id'][$key];

                    }

                    // if (isset($ssb) && $ssb) {
                    //     if ($ssb['is_stationary'] && $ssb['status']) {
                    //         $data['stationary_charge'] = 50;
                    //         $data['ssb_id'] = $ssb['data']->id;
                    //     }

                    // } else {
                    //     $ssbGet = SavingAccount::select('id', 'account_no')->where('member_id', $memberDetailId)->first();

                    //     $ssbId = $ssbGet->id ?? null;
                    //     if ($request['ecs_type'] == 2) {
                    //         $ecs_ref_no = $ssbGet->account_no ?? null;
                    //     }

                    // }


                    $ssbGet = SavingAccount::select('id', 'account_no')->where('member_id', $memberDetailId)->first();

                    if (isset($memberDetailIds['ssb']) && $memberDetailIds['ssb']) {
                        if (isset($memberDetailIds['ssb']['is_stationary']) && $memberDetailIds['ssb']['status']) {
                            $data['stationary_charge'] = 50;

                            $data['ssb_id'] = $ssbGet->id ?? null;
                        } else {
                            $data['ssb_id'] = $ssbGet->id ?? null;
                        }
                        if ($request['ecs_type'] == 2) {
                            $data['ecs_ref_no'] = $ssbGet->account_no ?? null;
                        }
                    } else {


                        $ssbId = $ssbGet->id ?? null;
                        if ($request['ecs_type'] == 2) {

                            $data['ecs_ref_no'] = $ssbGet->account_no ?? null;
                        }

                    }



                    $data['loan_type'] = $request['loanId'];
                    $data['member_loan_id'] = $memberLoanId;
                    $data['group_loan_common_id'] = $groupLoanCommonId;
                    $data['group_activity'] = $request['group_activity'];
                    $data['group_associate_id'] = $request['group_associate_id'];
                    $data['number_of_member'] = $request['number_of_member'];
                    $data['group_member_id'] = $request['group_member_id'];
                    $data['branch_id'] = $BranchId;
                    $data['ecs_type'] = $request['ecs_type'];
                    $data['ecs_charges'] = $request['ecsFileamount'][$key];


                    $data['ecs_charge_cgst'] = (isset($request['ecsStatus'][$key]) && $request['ecsStatus'][$key] == 'true' && is_numeric($request['ecs_charges'][$key]) ? $request['ecs_charges'][$key] : 0) ?? 0;


                    $data['ecs_charge_sgst'] = (isset($request['ecsStatus'][$key]) && $request['ecsStatus'][$key] == 'true' && is_numeric($request['ecs_charges'][$key]) ? $request['ecs_charges'][$key] : 0) ?? 0;


                    $data['ecs_charge_igst'] = (isset($request['ecsStatus'][$key]) && $request['ecsStatus'][$key] == 'false' && is_numeric($request['ecs_charges'][$key]) ? $request['ecs_charges'][$key] : 0) ?? 0;
                    
                    $data['old_branch_id'] = $BranchId;
                    $data['associate_member_id'] = $request['group_associate_id'];

                    $data['customer_id'] = $request['m_id'][$key];
                    ;
                    $data['member_id'] = $memberDetailId;
                    $data['amount'] = $request['m_amount'][$key];
                    $data['ROI'] = $request['ml_interest_rate'][$key];
                    $data['emi_option'] = $request['emi_option'];
                    $data['emi_period'] = $request['emi_period'];
                    $data['emi_amount'] = $request['ml_emi'][$key];
                    $data['file_charges'] = $request['ml_file_charge'][$key];
                    $data['insurance_charge'] = (isset($request['ml_insurance_charge'][$key]) ? ($request['ml_insurance_charge'][$key]) : 0);
                    $data['insurance_cgst'] = ($request['ml_gst_status'][$key] == 'true' ? ($request['ml_gst_charge'][$key] / 2) : 0);
                    $data['insurance_sgst'] = ($request['ml_gst_status'][$key] == 'true' ? ($request['ml_gst_charge'][$key] / 2) : 0);
                    $data['insurance_charge_igst'] = ($request['ml_gst_status'][$key] == 'false' ? ($request['ml_gst_charge'][$key]) : 0);
                    $data['filecharge_cgst'] = ($request['ml_gst_file_status'][$key] == 'true' ? ($request['ml_gst_file_charge'][$key]) : 0);
                    $data['filecharge_sgst'] = ($request['ml_gst_file_status'][$key] == 'true' ? ($request['ml_gst_file_charge'][$key]) : 0);
                    $data['filecharge_igst'] = ($request['ml_gst_file_status'][$key] == 'false' ? ($request['ml_gst_file_charge'][$key]) : 0);
                    $data['gsttype'] = ($request['ml_gst_file_status'] == true ? 0 : 1);
                    $data['gst_status'] = ($request['ml_gst_status'][$key] == 'false' ? 1 : 0);
                    if ($request['emi_option'] == 1) {
                        $data['closing_date'] = date('Y-m-d', strtotime("+" . $request['emi_period'] . " months", strtotime($created_at)));
                    } elseif ($request['emi_option'] == 2) {
                        $days = $request['emi_period'] * 7;
                        $start_date = $created_at;
                        $date = strtotime($start_date);
                        $date = strtotime("+" . $days . " day", $date);
                        $data['closing_date'] = date('Y-m-d', $date);
                    } elseif ($request['emi_option'] == 3) {
                        $days = $request['emi_period'];
                        $start_date = $created_at;
                        $date = strtotime($start_date);
                        $date = strtotime("+" . $days . " day", $date);
                        $data['closing_date'] = date('Y-m-d', $date);
                    }
                    $data['created_at'] = $created_at;
                    $data['company_id'] = $request['company_id'];
                    $data['application_no'] = $applicationNumber;

                    $res = Grouploans::create($data);

                    $collectorData = [
                        'type' => $request['loanId'],
                        'type_id' => $res->id,
                        'associate_id' => $request['group_associate_id'],
                        'status' => 1,
                        'created_id' => Auth::user()->id,
                        'created_by' => 2,
                        'created_at' => $created_at,
                    ];
                    \App\Models\CollectorAccount::create($collectorData);

                }
            }

            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            dd('dfgdfg',$ex->getMessage(),$ex->getLine());
            return back()->with('alert', $ex->getMessage());
        }

        return $res;
    }

    public function checkLoanAgainstInvestmentPercentage(Request $request)
    {
        $tenure = $request['tenure'];
        $plan = $request['plan'];
        $month = $request['month'];

        $getData = LoanAgainstDeposit::whereTenure($tenure)->wherePlanId($plan)->where('month_from', '<=', $month)->where('month_to', '>=', $month)->whereStatus(1)->orderByDesc('created_at')->first('loan_per');
        return response()->json($getData->loan_per);
    }



    public function getMemberIdProof(Request $request)
    {
        $idType = $request->id_type;
        $customerId = $request->customer_id;
        $getIdDetail = MemberIdProof::select('id', 'member_id', 'first_id_no', 'second_id_type_id', 'first_id_type_id', 'second_id_no')->whereMemberId($customerId)->where('first_id_type_id', $idType)->first();
        return response()->json($getIdDetail);
    }

    public function getLoanInvestmentRecord(Request $request){
        $data = Loaninvestmentmembers::where('plan_id',$request['investmentId'])->whereHas('memberLoans', function ($q) {
            $q->whereNotIn('status', [3, 5, 8]);
        })->exists();
        return response()->json($data);
    }


}
