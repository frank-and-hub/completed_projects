<?php

namespace App\Http\Controllers\Branch\Report;

use App\Models\MemberIdProof;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use App\Models\Daybook;
use App\Models\ReceivedVoucher;
use App\Models\BranchCurrentBalance;
use App\Models\Branch;
use App\Models\Transcation;
use App\Models\Memberinvestments;
use App\Models\Plans;
use App\Models\AccountHeads;
use App\Models\BranchCash;
use App\Models\EmployeeSalary;
use App\Models\SavingAccount;
use App\Models\SavingAccountTranscation;
use App\Models\AllTransaction;
use App\Models\SamraddhBankDaybook;
use App\Models\BranchDaybook;
use App\Models\SamraddhBank;
use App\Models\LoanDayBooks;
use App\Models\MemberTransaction;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Admin\CommanController;
use App\Models\Member;
use Carbon\Carbon;
use Session;
use Image;
use Redirect;
use URL;
use Cache;
use DB;
use App\Services\Email;
use App\Services\Sms;
use Illuminate\Support\Facades\Schema;
/*
    |---------------------------------------------------------------------------
    | Admin Panel -- Member Management MemberController
    |--------------------------------------------------------------------------
    |
    | This controller handles members all functionlity.
*/

class DayBookDublicateController extends Controller
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
     * Show Daybook report.
     * Route: /admin/report/daybook 
     * Method: get 
     * @return  array()  Response
     */
    //Branch Business Report (AMAN !! 15-05)
    public function day_bookReport()
    {
        if (!in_array('Day Book Report', auth()->user()->getPermissionNames()->toArray())) {
            return redirect()->route('branch.dashboard');    
        }
        $data['title'] = 'Report | DayBook  Report';
        $data['branch'] = Branch::where('status', 1)->get();
        return view('templates.branch.report.dublicate.day_book', $data);
    }
    public function print_day_bookReport()
    {

        $alltransaction = Cache::get('daybook_transaction');

        $data['title'] = 'Report | Duplicate DayBook  Report';
        $startDate = '';
        $endDate = '';
        $getBranchId = getUserBranchId(Auth::user()->id);
        $branch_id = $getBranchId->id;
        $company_id = '';

        if (isset($_GET['from_date'])) {
            $startDate = date("Y-m-d", strtotime(convertDate($_GET['from_date'])));
        }
        if (isset($_GET['to_date'])) {
            $endDate = $_GET['to_date'];
            $endDate = date("Y-m-d", strtotime(convertDate($_GET['to_date'])));
        }
        if (isset($_GET['company_id'])) {
            $company_id = hex2bin($_GET['company_id']);
        }


        //dd($company_id);
        // $dailyId =getPlanCodeByCategory('D');
        // $planDaily = getPlanID('D')->id;
        // $dailyId = array($planDaily);

        $planDaily = getPlanID('710')->id;
        $dailyId = array($planDaily);
        $planSSB = getPlanID('703')->id;
        $planKanyadhan = getPlanID('709')->id;
        $planMB = getPlanID('708');

        $planFRD = getPlanID('707')->id;
        $planJeevan = getPlanID('713')->id;
        $planRD = getPlanID('704')->id;
        $planBhavhishya = getPlanID('718')->id;
        $planMI = getPlanID('712')->id;
        $planFFD = getPlanID('705')->id;
        $planFD = getPlanID('706')->id;
        //  $fdId = getPlanCodeByCategory('F');
        $fdId = array($planMI, $planFFD, $planFD);
        // $dailyId = getPlanCodeByCategory('D');
        $dailyId = array($planDaily);
        // $monthlyId = getPlanCodeByCategory('M');
        $monthlyId = array($planKanyadhan, $planMB, $planFRD, $planJeevan, $planRD, $planBhavhishya);
        //$planSSB = getPlanCodeByCategory('S');


        $tenure = array(1, 3, 5, 7, 10);
        $data['cash_in_hand_cr'] = BranchDaybook::where(function ($q) {
            $q->where('sub_type', '!=', 30)->orwhere('sub_type', '=', NULL);
        })->where('payment_mode', 0)->where('branch_id', $branch_id)->where('payment_type', 'CR')->whereBetween(\DB::raw('DATE(entry_date)'), [$startDate, $endDate])->where('company_id', $company_id)->where('is_deleted', 0)->sum('amount');
        $data['cash_in_hand_dr'] = BranchDaybook::where('payment_mode', 0)->where('branch_id', $branch_id)->where('payment_type', 'DR')->whereBetween(\DB::raw('DATE(entry_date)'), [$startDate, $endDate])->where('company_id', $company_id)->where('is_deleted', 0)->sum('amount');
        $data['cheque_cr'] = BranchDaybook::whereIn('payment_mode', [1])->where('payment_type', 'CR')->where('branch_id', $branch_id)->whereBetween(\DB::raw('DATE(entry_date)'), [$startDate, $endDate])->where('company_id', $company_id)->where('is_deleted', 0)->sum('amount');
        $data['cheque_dr'] = BranchDaybook::whereIn('payment_mode', [1])->where('payment_type', 'DR')->where('branch_id', $branch_id)->whereBetween(\DB::raw('DATE(entry_date)'), [$startDate, $endDate])->where('company_id', $company_id)->where('is_deleted', 0)->sum('amount');
        $data['bank_cr'] = SamraddhBankDaybook::whereIn('payment_mode', [1, 2])->where('payment_type', 'CR')->where('company_id', $company_id)->where('branch_id', $branch_id)->whereBetween(\DB::raw('DATE(entry_date)'), [$startDate, $endDate])->sum('amount');
        $data['bank_dr'] = SamraddhBankDaybook::whereIn('payment_mode', [1, 2])->where('payment_type', 'DR')->where('company_id', $company_id)->where('branch_id', $branch_id)->whereBetween(\DB::raw('DATE(entry_date)'), [$startDate, $endDate])->sum('amount');
        $existsopening = BranchCurrentBalance::where('branch_id', $branch_id)->where('entry_date', $startDate)->exists();
        if ($existsopening) {
            $cashInhandOpening =   BranchCurrentBalance::where('branch_id', $branch_id)->where('company_id', $company_id)->where('entry_date', $startDate)->orderBy('entry_date', 'DESC')->first();
            $data['cashInhandOpening'] = !empty($cashInhandOpening->totalAmount) ? $cashInhandOpening->totalAmount : 0;
        } else {
            $cashInhandOpening =   BranchCurrentBalance::where('branch_id', $branch_id)->where('company_id', $company_id)->where('company_id', $company_id)->where('entry_date', '<', $startDate)->orderBy('entry_date', 'DESC')->first();
            $data['cashInhandOpening'] = !empty($cashInhandOpening->totalAmount) ? $cashInhandOpening->totalAmount : 0;
        }
        $cashInhand_closing = BranchCurrentBalance::where('branch_id', $branch_id)->where('entry_date', '<=', $endDate)->orderBy('entry_date', 'DESC')->first();
        $data['cashInhandclosing'] = !empty($cashInhandOpening->totalAmount) ? $cashInhandOpening->totalAmount : 0;
        $data['company_id'] = $company_id;
        $data['samraddhData'] =  DB::table('branch_daybook')->select('branch_daybook.*', 'branch_daybook.created_at as record_created_date', 'branch_daybook.payment_mode as branch_payment_mode', 'branch_daybook.payment_type as branch_payment_type', 'branch_daybook.member_id as branch_member_id', 'branch_daybook.associate_id as branch_associate_id', 'member_investments.id', 'branch_daybook.id as btid', 'branch_daybook.company_id')->leftjoin('member_investments', 'member_investments.id', 'branch_daybook.type_id')->where('branch_daybook.company_id', $company_id)->where('branch_daybook.branch_id', $branch_id)->whereBetween('branch_daybook.entry_date', [$startDate, $endDate])->orderBy('branch_daybook.entry_date', 'ASC')->where('branch_daybook.is_deleted', 0)->get();

        $data['file_chrg_total'] = Daybook::whereIn('transaction_type', ['6,10'])->where('branch_id', $branch_id)->where('company_id', $company_id)->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate])->count();
        $data['file_chrg_amount_total'] = Daybook::whereIn('transaction_type', ['6,10'])->where('branch_id', $branch_id)->where('company_id', $company_id)->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate])->sum('amount');
        $data['mi_total'] = 0;
        $data['mi_amount_total'] = 0;
        $data['stn_total'] = 0;
        $data['stn_amount_total'] = 0;

        $data['other_total__income_account'] = getExpenseHeadaccountCount(3, 1, $startDate, $endDate, $branch_id);
        $data['other_total__expense_account'] = getExpenseHeadaccountCount(4, 1, $startDate, $endDate, $branch_id);
        $data['other_total__income_amount'] = headTotalNew(3, $startDate, $endDate, $branch_id,$company_id);
        $data['other_total__expense_amount'] = headTotalNew(4, $startDate, $endDate, $branch_id,$company_id);
        $data['investment_stationary_chrg_account'] = getInvestmentStationarychrgAccount($startDate, $endDate, $branch_id, $company_id);
        $data['investment_stationary_chrg_amount'] = getInvestmentStationarychrgAmount($startDate, $endDate, $branch_id, $company_id);
        $data['loan_total_account'] = LoanDayBooks::where('loan_sub_type', 0)->where('branch_id', $branch_id)->where('company_id', $company_id)->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate])->count();
        $data['loan_total_amount'] = LoanDayBooks::where('loan_sub_type', 0)->where('branch_id', $branch_id)->where('company_id', $company_id)->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate])->sum('deposit');
        $data['received_voucher_account'] = ReceivedVoucher::where('branch_id', $branch_id)->where('company_id', $company_id)->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate])->count();;
        $data['received_voucher_amount'] = ReceivedVoucher::where('branch_id', $branch_id)->where('company_id', $company_id)->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate])->sum('amount');
        $renew_emi_recovery_12_Id = getmemberinvestementPlanwise($startDate, $endDate, $branch_id, $planDaily, '12', $company_id);
        $renew_emi_recovery_24_Id = getmemberinvestementPlanwise($startDate, $endDate, $branch_id, $planDaily, '24', $company_id);
        $renew_emi_recovery_36_Id  = getmemberinvestementPlanwise($startDate, $endDate, $branch_id, $planDaily, '36', $company_id);
        $renew_emi_recovery_60_Id  = getmemberinvestementPlanwise($startDate, $endDate, $branch_id, $planDaily, '60', $company_id);

        $data['ssbNiAc'] = totalSSbAccountByType($startDate, $endDate, $branch_id, 1, $company_id);
        $data['ssbNiAmount'] = number_format((float)totalSSbAmtByType($startDate, $endDate, $branch_id, 1, $company_id), 2, '.', '');
        $data['ssbRenewAc'] = totalSSbAccountByType($startDate, $endDate, $branch_id, 2, $company_id);
        $data['ssbRenewAmount'] = number_format((float)totalSSbAmtByType($startDate, $endDate, $branch_id, 2, $company_id), 2, '.', '');
        $data['ssbWAc'] = totalSSbAccountByType($startDate, $endDate, $branch_id, 5, $company_id);
        $data['ssbWAmount'] = number_format((float)totalSSbAmtByType($startDate, $endDate, $branch_id, 5, $company_id), 2, '.', '');
        $data['bank'] = SamraddhBank::with('bankAccount')->where('company_id', $company_id)->get();


        //Get All Data By Records 

     
        $alldata =  BranchDaybook::/*select('id','type','sub_type','type_id','type_transaction_id','entry_date','amount','opening_balance','closing_balance','transction_bank_to','description_cr','description_dr;)*/with(['member_investment' => function ($q) {
            $q->select('id', 'account_number', 'plan_id', 'member_id', 'associate_id', 'customer_id')->with(['member' => function ($q) {
                $q->select('id', 'member_id', 'first_name', 'last_name');
            }])->with('ssb')->with('plan')->with('associateMember');
        }])
            ->when('type' == 5, function ($q) {
                return $q->with(['member_loan' => function ($q) {
                    $q->select('id', 'applicant_id', 'loan_type')->with('loanMember');
                }]);
            })->when('type' == 5, function ($q) {
                $q->with(['group_member_loan' => function ($q) {
                    $q->select('id', 'applicant_id', 'loan_type', 'member_loan_id', 'member_id')->with('loanMember');
                }]);
            })
            ->with(['demand_advice' => function ($q) {
                $q->select('id', 'investment_id', 'employee_name')->with(['investment' => function ($q) {
                    $q->select('id', 'account_number', 'plan_id', 'member_id', 'account_number', 'customer_id')->with('plan')
                        ->with('member');
                }])->with(['expenses' => function ($qa) {
                    $qa->select('id')->with('advices');
                }]);
            }])
            ->with(['member' => function ($q) {
                $q->select('id', 'member_id', 'first_name', 'last_name');
            }])->with('receivedvoucherbytype_id')->with('receivedvoucherbytype_transaction_id')
            ->with(['SavingAccountTranscation' => function ($q) {
                $q->with(['savingAc' => function ($q) {
                    $q->select('id', 'account_no')->with('ssbMember')->with('associate');
                }]);
            }])
            ->when('type' == 7, function ($q) {
                return $q->with(['SamraddhBank' => function ($q) {
                    $q->with('bankAccount');
                }]);
            })
            ->when('type' == 15, function ($q) {
                return $q->with(['VoucherSamraddhBank' => function ($q) {
                    $q->with('bankAccount');
                }]);
            })
            ->when('type' == 15, function ($q) {
                return $q->with(['VoucherSamraddhBankbank_ac_id' => function ($q) {
                    $q->with('bankAccount');
                }]);
            })->when('type' == 1, function ($q) {
                return $q->with('memberMemberId');
            })->when('type' == 2, function ($q) {
                return $q->with('memberMemberId');
            })->with('accountHead')->with(['loan_from_bank' => function ($q) {
                $q->with('loan_emi');
            }])->with('company_bound')->with(['bill_expense' => function ($q) {
                $q->with('head')->with('subb_head')->with('subb_head2');
            }])
            ->with('BillExpense')
            ->with(['EmployeeSalaryBytype_id' => function ($q) {
                $q->with('salary_employee');
            }])
            ->with(['RentPayment' => function ($q) {
                $q->with('rentLib');
            }])
            ->with(['RentLiabilityLedger' => function ($q) {
                $q->with('rentLib');
            }])
            ->with(['EmployeeSalary' => function ($q) {
                $q->with('salary_employee');
            }])->with('associateMember')->with('SavingAccountTranscationtype_trans_id')
            ->where('company_id', $company_id)
            ->where('branch_id', $branch_id)->whereBetween('entry_date', [$startDate, $endDate])->where('is_deleted', 0)->orderBy('entry_date', 'ASC')->get();
          
        $data['alltransaction'] = $alldata;


        $data['fund_transfer_total'] = \App\Models\FundTransfer::where('transfer_type', 0)->where('branch_id', $branch_id)->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate])->count();
        $data['fund_transfer_amount_total'] = \App\Models\FundTransfer::where('transfer_type', 0)->where('branch_id', $branch_id)->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate])->sum('amount');
        //print_r($data['samraddhData'][0]);die;
        return view('templates.branch.report.dublicate.print_day_book', $data);
    }
    public function day_filterbookReport(Request $request)
    {
        $data['title'] = 'Report | Duplicate DayBook  Report';
        if ($request->ajax()) {
            if ($request['start_date'] != '') {
                $startDate = date("Y-m-d", strtotime(convertDate($request['start_date'])));
            } else {
                $startDate = '';
            }
            if ($request['end_date'] != '') {
                $endDate = date("Y-m-d", strtotime(convertDate($request['end_date'])));
            } else {
                $endDate = '';
            }
            if ($request['branch'] != '') {
                $branch_id = $request['branch'];
            } else {
                $branch_id = '';
            }
            if ($request['company'] != '') {
                $company_id = $request['company'];
            } else {
                $company_id = '';
            }
            if (isset($request['is_search']) && $request['is_search'] == 'yes') {
                $cash_in_hand['CR'] = BranchDaybook::where('payment_mode', 0)->where('branch_id', $branch_id)->where('payment_type', 'CR')->where('company_id', $company_id)->whereBetween(\DB::raw('DATE(entry_date)'), [$startDate, $endDate])->where('is_deleted', 0)->sum('amount');
                $record = BranchDaybook::where('payment_mode', 0)->where('branch_id', $branch_id)->where('company_id', $company_id)->where('payment_type', 'CR')->whereBetween(\DB::raw('DATE(entry_date)'), [$startDate, $endDate])->where('is_deleted', 0)->get();
                foreach ($record as $key => $value) {
                    $rec = 0;
                    if ($value->type == 3 && $value->sub_type == 30) {
                        $rec  = Daybook::where('id', $value->type_transaction_id)->first();
                        if (isset($rec->is_eli)) {
                            $rec = $rec->is_eli;
                            if ($rec == 1) {
                                $cash_in_hand['CR'] = $cash_in_hand['CR'] - $value->amount;
                            }
                        }
                    }
                }
                $cash_in_hand['DR'] = BranchDaybook::where('payment_mode', 0)->where('branch_id', $branch_id)->where('company_id', $company_id)->where('payment_type', 'DR')->whereBetween(\DB::raw('DATE(entry_date)'), [$startDate, $endDate])->where('is_deleted', 0)->sum('amount');
                $cheque['CR'] = BranchDaybook::whereIn('payment_mode', [1])->where('payment_type', 'CR')->where('company_id', $company_id)->where('branch_id', $branch_id)->whereBetween(\DB::raw('DATE(entry_date)'), [$startDate, $endDate])->where('is_deleted', 0)->sum('amount');
                $cheque['DR'] = BranchDaybook::whereIn('payment_mode', [1])->where('payment_type', 'DR')->where('company_id', $company_id)->where('branch_id', $branch_id)->whereBetween(\DB::raw('DATE(entry_date)'), [$startDate, $endDate])->where('is_deleted', 0)->sum('amount');
                $bank['CR'] = SamraddhBankDaybook::whereIn('payment_mode', [1, 2])->where('payment_type', 'CR')->where('company_id', $company_id)->where('branch_id', $branch_id)->whereBetween(\DB::raw('DATE(entry_date)'), [$startDate, $endDate])->sum('amount');
                $bank['DR'] = SamraddhBankDaybook::whereIn('payment_mode', [1, 2])->where('payment_type', 'DR')->where('company_id', $company_id)->where('branch_id', $branch_id)->whereBetween(\DB::raw('DATE(entry_date)'), [$startDate, $endDate])->sum('amount');

                $existsopening = BranchCurrentBalance::where('branch_id', $branch_id)->where('company_id', $company_id)->where('entry_date', $startDate)->exists();
                $existsopening = BranchCurrentBalance::where('branch_id', $branch_id)->where('company_id', $company_id)->where('entry_date', $startDate)->exists();
                if ($existsopening) {
                    $cashInhandOpening =   BranchCurrentBalance::where('branch_id', $branch_id)->where('company_id', $company_id)->where('entry_date', $startDate)->orderBy('entry_date', 'DESC')->first();
                    //$cashInhandOpening = $cashInhandOpening->opening_balance + $cashInhandOpening->loan_opening_balance;
                    $cashInhandOpening = $cashInhandOpening->totalAmount;
                } else {
                    $cashInhandOpening =   BranchCurrentBalance::where('branch_id', $branch_id)->where('company_id', $company_id)->where('entry_date', '<', $startDate)->orderBy('entry_date', 'DESC')->first();
                    if (isset($cashInhandOpening->totalAmount)) {
                        $cashInhandOpening = !empty($cashInhandOpening->totalAmount) ? $cashInhandOpening->totalAmount : 0;
                    } else {
                        $cashInhandOpening = 0;
                    }
                }
                $cashInhandclosing = BranchCurrentBalance::where('branch_id', $branch_id)->where('company_id', $company_id)->where('entry_date', '<=', $endDate)->orderBy('entry_date', 'DESC')->first();
                $cashInhandclosing = !empty($cashInhandclosing->totalAmount) ? $cashInhandclosing->totalAmount : 0;
                //   $data = BranchDaybook::where('branch_id',$branch_id)->whereBetween(\DB::raw('DATE(entry_date)'), [$startDate, $endDate])->orderBy('created_at','ASC')->get();
                // $data = DB::table('branch_daybook')->select('branch_daybook.*','branch_daybook.created_at as record_created_date','branch_daybook.payment_mode as branch_payment_mode','branch_daybook.payment_type as branch_payment_type','branch_daybook.member_id as branch_member_id','branch_daybook.associate_id as branch_associate_id','member_investments.id','branch_daybook.id as btid')->leftjoin('member_investments','member_investments.id','branch_daybook.type_id')->where('branch_daybook.branch_id',$branch_id)->whereBetween('branch_daybook.entry_date',[$startDate, $endDate])->orderBy('branch_daybook.entry_date','ASC')->get();
            }
            return \Response::json(['view' => view('templates.branch.report.dublicate.filtered_sheet_day_book', ['cashInhand' => $cash_in_hand, 'cashInhandOpening' => $cashInhandOpening, 'cashInhandclosing' => $cashInhandclosing, 'cheque' => $cheque, 'bank' => $bank, 'end_date' => $endDate, 'branch_id' => $branch_id, 'bank' => $bank, 'start_date' => $startDate, 'company_id' => $company_id])->render(), 'msg_type' => 'success']);
        }
    }
    // public function transaction_list(Request $request)
    // {
    //     // $branch_id = $request->branch;
    //     // $startDate = $request->start_date;
    //     // $endDate = $request->end_date;
    //     $records = '';
    //     if($request->page == 0)
    //     {
    //       $i = 1;  
    //     }
    //     else{
    //          $i = $request->index;
    //     }
    //     if($request['start_date'] !=''){
    //         $startDate=date("Y-m-d", strtotime(convertDate($request->start_date)));
    //     }
    //     else{
    //         $startDate='';
    //     }
    //     if($request['end_date'] !=''){
    //          $endDate=date("Y-m-d ", strtotime(convertDate($request->end_date)));
    //     }
    //     else {
    //         $endDate='';
    //     }
    //     if($request['branch']!='') {
    //         $branch_id=$request->branch;
    //     }
    //     else {
    //         $branch_id='';
    //     }
    //     $rowReturn = array();
    //     $offset = $request->limit * $request->page;
    //     $limit = $request->limit;
    //     // $offset = $offset - 1;
    //     $data =  DB::table('branch_daybook')->select('branch_daybook.*','branch_daybook.created_at as record_created_date','branch_daybook.payment_mode as branch_payment_mode','branch_daybook.payment_type as branch_payment_type','branch_daybook.member_id as branch_member_id','branch_daybook.associate_id as branch_associate_id','member_investments.id','branch_daybook.id as btid')->leftjoin('member_investments','member_investments.id','branch_daybook.type_id')->where('branch_daybook.branch_id',$branch_id)->whereBetween('branch_daybook.entry_date',[$startDate, $endDate])->where('branch_daybook.is_deleted',0)->orderBy('branch_daybook.entry_date','ASC')->offset($offset)->limit($limit)->get();
    //     foreach ($data as $index => $value) {
    //         $f1=0;
    //         $f2=0;    
    //         $getBranchOpening =getBranchOpeningDetail($branch_id);
    //         $balance=0;
    //         $currentdate = date('Y-m-d');
    //         if($getBranchOpening->date==$startDate)
    //         {
    //           $balance=$getBranchOpening->total_amount;
    //         }
    //         if($getBranchOpening->date<$startDate)
    //         {
    //           if($getBranchOpening->date != '')
    //             {
    //                  $getBranchTotalBalance=getBranchTotalBalanceAllTran($startDate,$getBranchOpening->date,$getBranchOpening->total_amount,$branch_id);
    //             }
    //             else{
    //                 $getBranchTotalBalance=getBranchTotalBalanceAllTran($startDate, $currentdate,$getBranchOpening->total_amount,$branch_id);
    //             }
    //           $balance=$getBranchTotalBalance;
    //               if($endDate == '')
    //               {
    //                 $endDate=$currentdate;
    //               }
    //         }
    //         $type = '';
    //         $getTransType = \App\Models\TransactionType::where('type',$value->type)->where('sub_type',$value->sub_type)->first();
    //             $type = '';
    //             if($value->type == $getTransType->type)
    //             {
    //                 if($value->sub_type == $getTransType->sub_type)
    //                 {
    //                     $type = $getTransType->title;
    //                 }
    //             }
    //             else{
    //                 $type='N/A';
    //             }
    //             if($value->type == 21)
    //             {
    //                  $record = ReceivedVoucher::where('id',$value->type_id)->first();
    //                  if($record )
    //                  {
    //                      $type= $record->particular;
    //                  }
    //                 else{
    //                     $type="N/A";
    //                 }
    //             }
    //             // Member Name, Member Account and Member Id
    //             $memberData = getMemberInvestment($value->type_id);
    //             $loanData = getLoanDetail($value->type_id);
    //             $groupLoanData = getGroupLoanDetail($value->type_id);
    //             $DemandAdviceData = \App\Models\DemandAdvice::where('id',$value->type_id)->first();
    //             $freshExpenseData = \App\Models\DemandAdviceExpense::where('id',$value->type_id)->first();
    //             $memberName = 'N/A';
    //             $memberAccount = 'N/A';
    //             $is_eli = 0;
    //             $plan_name ='';
    //             if($value->payment_mode == 6)
    //             {
    //                 $rentPaymentDetail=\App\Models\RentLiabilityLedger::with('rentLib')->where('id',$value->type_transaction_id)->first(); 
    //                 $salaryDetail=EmployeeSalary::with('salary_employee')->where('id',$value->type_transaction_id)->first();
    //             }
    //             else{
    //                 $rentPaymentDetail=\App\Models\RentPayment::with('rentLib')->where('id',$value->type_transaction_id)->first(); 
    //                 $salaryDetail=EmployeeSalary::with('salary_employee')->where('id',$value->type_transaction_id)->first();
    //             } 
    //             if($value->type==14)
    //             {
    //               if($value->sub_type == 144)
    //               {
    //                  $voucherDetail=ReceivedVoucher::where('id',$value->type_transaction_id)->first();
    //               }
    //               else{
    //                  $voucherDetail=ReceivedVoucher::where('id',$value->type_id)->first();
    //               }
    //             }
    //             if($value->type == 1)
    //             {
    //                 if($value->type_id){
    //                   $memberName =getMemberData($value->type_id)->first_name. ' '.getMemberData($value->type_id)->last_name ;
    //                   $memberId =getMemberData($value->type_id)->member_id;
    //                   $memberAccount = 'N/A' ;
    //                 }
    //             }
    //             elseif($value->type == 2)
    //             {
    //                  if($value->type_id){
    //                     $memberName = getMemberData($value->type_id)->first_name. ' '.getMemberData($value->type_id)->last_name ;
    //                     $memberId =getMemberData($value->type_id)->member_id;
    //                     $memberAccount = 'N/A' ;
    //                 }
    //             }
    //              elseif($value->type ==3)
    //         {
    //             if($value->sub_type ==38)
    //             {
    //                 $record = Daybook::where('id',$value->type_transaction_id)->first();
    //                 $memberAccount = $record->account_no;
    //                 $planDetail = getInvestmentAccount($record->member_id,$record->account_no);
    //                 $plan_name =getPlanDetail($planDetail->plan_id)->name;
    //                 $memberName =getMemberData($record->member_id)->first_name. ' '.getMemberData($record->member_id)->last_name;
    //                 $memberId =getMemberData($record->member_id)->member_id;
    //             }else{
    //                     if($value->member_id){
    //                     $memberName =getMemberData($value->member_id)->first_name. ' '.getMemberData($value->member_id)->last_name;
    //                     $memberId =getMemberData($value->member_id)->member_id;
    //                 }
    //                 if( $memberData)
    //                 {
    //                     $plan_name =getPlanDetail($memberData->plan_id)->name;
    //                     $memberAccount = $memberData->account_number;
    //                 }
    //             }
    //         }
    //         elseif($value->type ==4)
    //         {
    //             if($value->sub_type == 412)
    //             {
    //                 $record = SavingAccountTranscation::with('savingAc')->where('id',$value->type_transaction_id)->first();
    //                 $memberAccount = $record->account_no;
    //                 $planDetail = getInvestmentAccount($record['savingAc']->member_id,$record->account_no);
    //                 $plan_name =getPlanDetail($planDetail->plan_id)->name;
    //                 $memberName =getMemberData($record['savingAc']->member_id)->first_name. ' '.getMemberData($record['savingAc']->member_id)->last_name;
    //                 $memberId =getMemberData($record['savingAc']->member_id)->member_id;
    //             }
    //             else{
    //                      if($value->member_id){
    //                     $memberName = getMemberData($value->member_id)->first_name. ' '.getMemberData($value->member_id)->last_name ;
    //                     $memberId =getMemberData($value->member_id)->member_id;
    //                 }
    //                 if($value->sub_type == 42)
    //                 {
    //                     $memberAccount =SavingAccountTranscation::where('id',$value->type_transaction_id)->first();
    //                      if(isset($memberAccount->account_no)){
    //                         $memberAccount = $memberAccount->account_no;
    //                      }
    //                 }
    //                 else{
    //                     $memberAccount = getSsbAccountNumber($value->type_id);
    //                   if($memberAccount)
    //                   {
    //                   $memberAccount = $memberAccount->account_no;
    //                   }
    //                 }
    //             }
    //         }
    //             elseif($value->type ==5)
    //             {
    //                 if($value->sub_type == 51 || $value->sub_type == 52 || $value->sub_type == 53 || $value->sub_type == 57  || $value->sub_type == 511  || $value->sub_type == 513  || $value->sub_type == 515)
    //                  {
    //                      if($loanData)
    //                      {
    //                         $memberName = getMemberData($loanData->applicant_id)->first_name. ' '.getMemberData($loanData->applicant_id)->last_name;
    //                         if($loanData->loan_type==1)
    //                          {
    //                             $plan_name ='Personal Loan(PL)';
    //                          }
    //                          if($loanData->loan_type==2)
    //                          {
    //                             $plan_name ='Staff Loan(SL)';
    //                          }
    //                          if($loanData->loan_type==4)
    //                          {
    //                             $plan_name ='Loan against Investment plan(DL)';
    //                          }
    //                      }
    //                  }
    //                 elseif($value->sub_type == 54 || $value->sub_type == 55 || $value->sub_type == 56 || $value->sub_type == 58  || $value->sub_type == 512  || $value->sub_type == 514  || $value->sub_type == 516 || $value->sub_type==518 ){
    //                      if($groupLoanData)
    //                      {
    //                         $memberName = getMemberData($groupLoanData->applicant_id)->first_name. ' '.getMemberData($groupLoanData->applicant_id)->last_name;
    //                      }
    //                 }
    //              }
    //             elseif($value->type ==6)
    //             {
    //                 if(isset($salaryDetail['ledger_employee']->employee_name))
    //                 {
    //                     $memberName = $salaryDetail['ledger_employee']->employee_name;
    //                     $memberAccount = $salaryDetail['ledger_employee']->employee_code;
    //                 }
    //                 elseif(isset($salaryDetail['salary_employee']->employee_name))
    //                 {
    //                     $memberName = $salaryDetail['salary_employee']->employee_name;
    //                     $memberAccount = $salaryDetail['salary_employee']->employee_code;
    //                 }
    //             }
    //             elseif($value->type ==7)
    //             {
    //               $memberName = SamraddhBank::where('id',$value->transction_bank_to)->first();
    //               $memberName =  $memberName->bank_name;
    //               $memberAccount = getSamraddhBankAccountId($value->transction_bank_to);
    //               $memberAccount = $memberAccount->account_no;
    //             }
    //             elseif($value->type ==9)
    //             {
    //               $memberName ==getMemberData($value->type_id)->first_name. ' '.getMemberData($value->type_id)->last_name ; 
    //               $memberAccount =getMemberData($value->type_id)->member_id ;
    //             }
    //             elseif($value->type ==10)
    //             {
    //               if($rentPaymentDetail['rentLib'])
    //               {
    //                 if($rentPaymentDetail)
    //                 {
    //                   $memberName = $rentPaymentDetail['rentLib']->owner_name;
    //                 }
    //               }
    //                 $memberAccount = 'N/A';
    //             }
    //              elseif($value->type ==11)
    //             {
    //               if($DemandAdviceData['employee_name'])
    //               {
    //                 $memberName = $DemandAdviceData->party_name;
    //               }
    //                 $memberAccount = 'N/A';
    //             }
    //             elseif($value->type ==12)
    //             {
    //               if($salaryDetail['salary_employee'])
    //               {
    //                  $memberName = $salaryDetail['salary_employee']->employee_name;
    //                   $memberAccount = $salaryDetail['salary_employee']->employee_name;
    //               }
    //             }
    //             elseif($value->type ==13)
    //             {
    //                 if($value->sub_type == 131 )
    //                 {
    //                   if($freshExpenseData)
    //                   {
    //                     $memberAccount = $freshExpenseData['advices']->voucher_number;
    //                     $memberId = $freshExpenseData->bill_number;
    //                   }
    //                 }
    //                 if($value->sub_type == 132 )
    //                 {
    //                   if($freshExpenseData)
    //                   {
    //                     $memberAccount = $freshExpenseData['advices']->voucher_number;
    //                     $memberId = $freshExpenseData->bill_number;
    //                   }
    //                 }
    //                 if($value->sub_type == 133 )
    //                 {
    //                   $memberAccount = getMemberInvestment($DemandAdviceData->investment_id)->account_number;
    //                   $plan_id = getMemberInvestment($DemandAdviceData->investment_id);
    //                   $plan_name = getPlanDetail($plan_id->plan_id)->name;
    //                 }
    //                 if($value->sub_type == 134 )
    //                 {
    //                   $memberAccount = getMemberInvestment($DemandAdviceData->investment_id)->account_number;
    //                   $plan_id = getMemberInvestment($DemandAdviceData->investment_id);
    //                   $plan_name = getPlanDetail($plan_id->plan_id)->name;
    //                 }
    //                 if($value->sub_type == 135 )
    //                 {
    //                   $memberAccount = getMemberInvestment($DemandAdviceData->investment_id)->account_number;
    //                   $plan_id = getMemberInvestment($DemandAdviceData->investment_id);
    //                   $plan_name = getPlanDetail($plan_id->plan_id)->name;
    //                 }
    //                 if($value->sub_type == 136 )
    //                 {
    //                   $memberAccount = getMemberInvestment($DemandAdviceData->investment_id)->account_number;
    //                   $plan_id = getMemberInvestment($DemandAdviceData->investment_id);
    //                   $plan_name = getPlanDetail($plan_id->plan_id)->name;
    //                 }
    //                 if($value->sub_type == 137 )
    //                 {
    //                   $memberAccount = getMemberInvestment($DemandAdviceData->investment_id)->account_number;
    //                   $plan_id = getMemberInvestment($DemandAdviceData->investment_id);
    //                   $plan_name = getPlanDetail($plan_id->plan_id)->name;
    //                 }  
    //                 if($value->sub_type == 142 )
    //                 {
    //                   if($freshExpenseData)
    //                   {
    //                     $memberName = $freshExpenseData->party_name;
    //                     $memberAccount = $freshExpenseData['advices']->voucher_number;
    //                     $memberId = $freshExpenseData->bill_number;
    //                   }
    //                 }  
    //             }
    //              elseif($value->type ==14)
    //             {
    //               if($voucherDetail != '')
    //               {
    //                 if($voucherDetail->type == 1)
    //                 {
    //                     if($voucherDetail->received_mode == 1 || $voucherDetail->received_mode == 2)
    //                   {
    //                       $memberAccount = getSamraddhBankAccountId($voucherDetail->receive_bank_ac_id)->account_no;
    //                       $bankAccount = getSamraddhBank($voucherDetail->receive_bank_id);
    //                         if(isset($bankAccount))
    //                         {
    //                             $memberAccount = $memberAccount.'('.$bankAccount->bank_name.')';
    //                         }
    //                   }
    //                 }
    //                 if($voucherDetail->type == 2 )
    //                 {
    //                     if($voucherDetail->received_mode == 1 || $voucherDetail->received_mode == 2)
    //                   {
    //                       $memberAccount = getSamraddhBankAccountId($voucherDetail->receive_bank_ac_id)->account_no;
    //                       $bankAccount = getSamraddhBank($voucherDetail->receive_bank_id);
    //                         if(isset($bankAccount))
    //                         {
    //                             $memberAccount = $memberAccount.'('.$bankAccount->bank_name.')';
    //                         }
    //                   }
    //                 }
    //                 if($voucherDetail->type == 3 )
    //                 {
    //                   $memberId =  getEmployeeData($voucherDetail->employee_id)->employee_code;
    //                   if($voucherDetail->received_mode == 1 || $voucherDetail->received_mode == 2)
    //                   {
    //                   $memberAccount = getSamraddhBankAccountId($voucherDetail->receive_bank_ac_id)->account_no;
    //                   $bankAccount = getSamraddhBank($voucherDetail->receive_bank_id);
    //                     if(isset($bankAccount))
    //                     {
    //                         $memberAccount = $memberAccount.'('.$bankAccount->bank_name.')';
    //                     }
    //                   }
    //                 }
    //                 if($voucherDetail->type == 4 )
    //                 {
    //                   $memberAccount = getSamraddhBankAccountId($voucherDetail->bank_ac_id)->account_no;
    //                 }
    //                 if($voucherDetail->type ==5 )
    //                 {
    //                     if(isset($voucherDetail))
    //                     {
    //                         $memberAccount = getSamraddhBankAccountId($voucherDetail->receive_bank_ac_id)->account_no;
    //                         $bankAccount = getSamraddhBank($voucherDetail->receive_bank_id);
    //                         if(isset($bankAccount))
    //                         {
    //                             $memberAccount = $memberAccount.'('.$bankAccount->bank_name.')';
    //                         }
    //                     }
    //                 }
    //               }
    //             }
    //              elseif($value->type ==15)
    //             {
    //               $memberName =getAcountHeadNameHeadId($value->type_id);
    //               $memberAccount ="N/A";
    //             }
    //              elseif($value->type ==16)
    //             {
    //               $memberName =getAcountHeadNameHeadId($value->type_id);
    //               $memberAccount ="N/A";
    //             }
    //             elseif($value->type ==17)
    //         {
    //             if($value->sub_type == 171)
    //             {
    //                 $detail =\App\Models\LoanFromBank::where('daybook_ref_id',$value->daybook_ref_id   )->first();
    //                 if($detail)
    //                 {
    //                   $memberAccount = $detail->loan_account_number;
    //                   $memberName =$detail->bank_name;
    //                 }
    //             }
    //             else if($value->sub_type==172)
    //             {
    //                 $detail =\App\Models\LoanEmi::where('id',$value->type_transaction_id   )->first();
    //                 if($detail)
    //                 {
    //                   $memberAccount = \App\Models\LoanFromBank::where('id',$detail->loan_bank_account   )->first(); 
    //                   $memberAccount =$memberAccount->loan_account_number;
    //                   $memberName =$detail->loan_bank_name;
    //                 }
    //             }
    //         }
    //          elseif($value->type ==30)
    //         {
    //             if($value->sub_type == 301)
    //             {
    //                 $detail =\App\Models\CompanyBound::where('daybook_ref_id',$value->daybook_ref_id   )->first();
    //                 if($detail)
    //                 {
    //                   $memberAccount = $detail->fd_no;
    //                   $memberName =$detail->bank_name;
    //                 }
    //             }
    //             else if($value->sub_type==302)
    //             {
    //                 $detail =\App\Models\CompanyBoundTransaction::where('daybook_ref_id',$value->daybook_ref_id   )->first();
    //                 if($detail)
    //                 {
    //                   $record = \App\Models\CompanyBound::where('id',$detail->bound_id   )->first(); 
    //                   $memberAccount =$record->fd_no;
    //                   $memberName =$record->bank_name;
    //                 }
    //             }
    //         }
    //             elseif($value->type ==21)
    //             {
    //               $memberAccount = getMemberData($value->member_id)->member_id;
    //             }
    //             $type = '';
    //             $getTransType = \App\Models\TransactionType::where('type',$value->type)->where('sub_type',$value->sub_type)->first();
    //                 $type = '';
    //             if($value->type == $getTransType->type)
    //             {
    //                 if($value->sub_type == $getTransType->sub_type)
    //                 {
    //                     $type = $getTransType->title;
    //                 }
    //             }
    //             if($value->type == 21)
    //             {
    //                  $record = ReceivedVoucher::where('id',$value->type_id)->first();
    //                  if($record )
    //                  {
    //                      $type= $record->particular;
    //                  }
    //                 else{
    //                     $type="N/A";
    //                 }
    //             }
    //              if($value->type == 22)
    //             {
    //                  if($value->sub_type == 222)
    //                 {
    //                     $type = $value->description;
    //                 }
    //             }
    //             if($value->type == 23)
    //             {
    //                  if($value->sub_type == 232)
    //                 {
    //                     $type = $value->description;
    //                 }
    //             }
    //             if($value->sub_type==43 || $value->sub_type==41)
    //                     {
    //                         $associate_code = SavingAccount::where('id',$value->type_id)->first();
    //                         $associate_name = Member::where('id',$associate_code->associate_id)->first();
    //                     }
    //                     if($value->type==13  || $value->sub_type ==35 || $value->sub_type ==37 || $value->sub_type ==33 || $value->sub_type==34 || $value->type == 21)
    //                     {
    //                       $associate_code = getAssociateId($value->member_id);
    //                       $associate_name = Member::where('associate_no',$associate_code)->first();
    //                     }
    //                     if($value->type == 20)
    //                     {
    //                         if(isset($value['bill_expense']->bill_no))
    //                         {
    //                             $memberAccount='Bill No.'.$value['bill_expense']->bill_no;
    //                             $memberName = $value['bill_expense']->party_name;
    //                             $mainHead =  $value['bill_expense']['head']->sub_head;
    //                         }  
    //                         $name = \App\Models\BillExpense::where('daybook_refid',$value->daybook_ref_id)->first();
    //                         $record = \App\Models\Expense::where('bill_no',$value->type_id)->first();
    //                         if(isset($record->account_head_id) && isset($record->sub_head1) && isset($record->sub_head2))
    //                         {
    //                           $subHead =  $value['bill_expense']['subb_head']->sub_head;
    //                           $subHead2 =  $value['bill_expense']['subb_head2']->sub_head;
    //                           $plan_name = 'INDIRECT EXPENSE /'.$mainHead.'/'.$subHead.'/'.$subHead2;
    //                         }
    //                         elseif(isset($record->account_head_id) && isset($record->sub_head1) )
    //                         {
    //                           $subHead =  $value['bill_expense']['subb_head']->sub_head;
    //                           $plan_name = 'INDIRECT EXPENSE /'.$mainHead.'/'.$subHead;
    //                         }
    //                         elseif(isset($record->account_head_id))
    //                         {
    //                             $plan_name = 'INDIRECT EXPENSE /'.$mainHead;
    //                         }
    //                     }
    //             // Associate
    //             $a_name ='N/A';
    //             if($value->sub_type==43 || $value->sub_type==41 || $value->type == 13 || $value->sub_type ==35  || $value->sub_type ==37 || $value->sub_type ==33 || $value->sub_type==34 || $value->type == 21)
    //             {
    //                 if($associate_name)
    //                 {
    //                     $a_name = $associate_name->first_name.' '.$associate_name->last_name.'('.$associate_name->associate_no.')';
    //                 }
    //             }
    //             else{
    //                     if($value->branch_associate_id)
    //                     {
    //                       $a_name = getMemberData($value->branch_associate_id)->first_name.' '.getMemberData($value->branch_associate_id)->last_name.'('.getMemberData($value->branch_associate_id)->associate_no.')';
    //                     }
    //                 }
    //                 dd($value);
    //             // Payment Type
    //             $cr_amount = 0;
    //             $dr_amnt = 0;
    //             if($value->payment_type == 'CR')
    //             {
    //                 $cr_amount = number_format((float)$value->amount, 2, '.', '');
    //             }
    //             if($value->payment_type == 'DR'){
    //               $dr_amnt =  number_format((float)$value->amount, 2, '.', '');
    //             }
    //             // Balance
    //             if($value->branch_payment_mode == 0 && $value->sub_type != 30 ) 
    //             {
    //                 $balance = number_format((float)$balance, 2, '.', '');
    //             }    
    //             // Ref Number
    //             if($value->branch_payment_mode == 0 )
    //             {
    //                 $ref_no = 'N/A';
    //             }
    //             elseif($value->branch_payment_mode == 1)
    //             {
    //                  $ref_no = $value->cheque_no;
    //             }
    //             elseif($value->branch_payment_mode == 2)
    //             {
    //                  $ref_no = $value->transction_no;
    //             }
    //             elseif($value->branch_payment_mode == 3)
    //              { 
    //                 $ref_no = $value->v_no;
    //              }
    //             elseif($value->branch_payment_mode == 6)
    //              { 
    //                 $ref_no = $value->jv_unique_id; 
    //             } 
    //             else{
    //                 $ref_no = 'N/A';
    //             }
    //             // Payment Mode
    //             if($value->sub_type == 30)
    //             {
    //                 $pay_mode = 'ELI';
    //             }
    //             else
    //             if($value->branch_payment_mode == 0 )
    //             {
    //                 $pay_mode = 'CASH';
    //             }
    //             elseif($value->branch_payment_mode == 1)
    //             {
    //                 $pay_mode = 'CHEQUE';
    //             }
    //             elseif($value->branch_payment_mode == 2)
    //             {
    //                 $pay_mode = 'ONLINE TRANSFER';
    //             }
    //             elseif($value->branch_payment_mode == 3)
    //             {
    //                 $pay_mode = 'SSB';
    //             }
    //             elseif($value->branch_payment_mode ==4)
    //             {
    //                 $pay_mode = 'AUTO TRANSFER';
    //             }
    //             elseif($value->branch_payment_mode ==6)
    //             {
    //                 $pay_mode = 'JV';
    //             }
    //             if($value->entry_date){
    //                 $date =date("d/m/Y", strtotime(convertDate($value->entry_date)));
    //             }
    //             else 
    //             {
    //                 $date = 'N/A';
    //             }
    //             if($value->branch_payment_type == 'CR')
    //             {
    //               if($value->branch_payment_mode == 0 && $is_eli == 0)
    //               {
    //                   $balance=$balance+$value->amount;
    //               }
    //             }
    //             if($value->branch_payment_type == 'DR')
    //             {
    //               if($value->branch_payment_mode == 0 && $is_eli== 0)
    //               {
    //                   $balance=$balance-$value->amount;
    //               }
    //             }
    //             if($value->branch_payment_mode == 0 && $is_eli == 0 ) 
    //             {
    //               $balance =   number_format((float)$balance, 2, '.', '');
    //             }
    //             else{
    //                 $balance = '';
    //             }
    //             // tag
    //             $tag='';
    //             if($value->type = 3)
    //             {
    //                 if ($value->sub_type == 31) {
    //                     $tag='N';
    //                 }
    //                 if ($value->sub_type == 32) {
    //                       $tag='R';
    //                 }
    //             }
    //             if($value->type == 4)
    //             {
    //                 if($value->sub_type == 41)
    //                 {
    //                   $tag='N';
    //                 }
    //                 if ($value->sub_type == 42) {
    //                       $tag='R';
    //                 }
    //                 if ($value->sub_type == 43) {
    //                         $tag='W';
    //                 }
    //             }
    //             if($value->type == 5)
    //             {
    //                     if($value->sub_type == 51)
    //                 {
    //                     $tag='LD';
    //                 }
    //                 if ($value->sub_type == 52) {
    //                     $tag='L';
    //                 }
    //                 if ($value->sub_type == 54) {
    //                     $tag='LD';
    //                 }
    //                 if ($value->sub_type == 55) {
    //                     $tag='L';
    //                 }
    //             }
    //             if($value->type == 7)
    //             {
    //                 $tag='B';  
    //             }
    //             if($value->type ==13)
    //             {
    //                 if($value->sub_type == 131)
    //                 {
    //                     $tag='E';
    //                 }
    //                 if ($value->sub_type == 133) {
    //                     $tag='M';
    //                 }
    //                 if ($value->sub_type == 134) {
    //                     $tag='M';
    //                 }
    //                 if ($value->sub_type == 135) {
    //                     $tag='M';
    //                 }
    //                 if ($value->sub_type == 136) {
    //                     $tag='M';
    //                 }
    //               if ($value->sub_type == 137) {
    //                     $tag='M';
    //                 }
    //             }
    //         $records = "
    //                     <tr>
    //                         <td>".$i."</td>
    //                         <td>".$date."</td>
    //                         <td>".$value->btid."</td>
    //                         <td>".$memberAccount."</td>
    //                         <td>".$plan_name."</td>
    //                         <td>".$memberName."</td>
    //                         <td>".$a_name."</td>
    //                         <td>".$type."</td>
    //                         <td>".$value->description_cr."</td>
    //                         <td>".$value->description_dr."</td>
    //                         <td>". $cr_amount."</td>
    //                         <td>".$dr_amnt."</td>
    //                         <td>".$balance."</td>
    //                         <td>".$ref_no."</td>
    //                         <td>".$pay_mode."</td>
    //                         <td>".$tag."</td>
    //                     </tr>";
    //                 $rowReturn[] = $records;
    //               $i =  $i+1;
    //     }
    //     $sNo =array($i) ;
    //       return response()->json(['data'=>$rowReturn,'sno'=>$sNo,'msg'=>'success']); 
    // }
    public function transaction_list(Request $request)
    {
        // $branch_id = $request->branch;
        // $startDate = $request->start_date;
        // $endDate = $request->end_date;
        $records = '';
        if ($request->page == 0) {
            $i = 1;
        } else {
            $i = $request->index;
        }
        if ($request['start_date'] != '') {
            $startDate = date("Y-m-d", strtotime(convertDate($request->start_date)));
        } else {
            $startDate = '';
        }
        if ($request['end_date'] != '') {
            $endDate = date("Y-m-d ", strtotime(convertDate($request->end_date)));
        } else {
            $endDate = '';
        }
        if ($request['branch'] != '') {
            $branch_id = $request->branch;
        } else {
            $branch_id = '';
        }
        if ($request['company'] != '') {
            $company_id = $request->company;
        } else {
            $company_id = '';
        }

        $balance_closing = array();
        $rowReturn = array();
        $offset = $request->limit * $request->page;
        $limit = $request->limit;
        // $offset = $offset - 1;
        $data =  BranchDaybook::/*select('id','type','sub_type','type_id','type_transaction_id','entry_date','amount','opening_balance','closing_balance','transction_bank_to','description_cr','description_dr;)*/with(['member_investment' => function ($q) {
            $q->select('id', 'account_number', 'plan_id', 'member_id', 'associate_id', 'customer_id')->with(['member' => function ($q) {
                $q->select('id', 'member_id', 'first_name', 'last_name');
            }])->with('ssb')->with('plan')->with('associateMember');
        }])
            ->when('type' == 5, function ($q) {
                return $q->with(['member_loan' => function ($q) {
                    $q->select('id', 'applicant_id', 'loan_type')->with('loanMember');
                }]);
            })->when('type' == 5, function ($q) {
                $q->with(['group_member_loan' => function ($q) {
                    $q->select('id', 'applicant_id', 'loan_type', 'member_loan_id', 'member_id')->with('loanMember');
                }]);
            })
            ->with(['demand_advice' => function ($q) {
                $q->select('id', 'investment_id', 'employee_name')->with(['investment' => function ($q) {
                    $q->select('id', 'account_number', 'plan_id', 'member_id', 'account_number', 'customer_id')->with('plan')
                        ->with('member');
                }])->with(['expenses' => function ($qa) {
                    $qa->select('id')->with('advices');
                }]);
            }])
            ->with(['member' => function ($q) {
                $q->select('id', 'member_id', 'first_name', 'last_name');
            }])->with('receivedvoucherbytype_id')->with('receivedvoucherbytype_transaction_id')
            ->with(['SavingAccountTranscation' => function ($q) {
                $q->with(['savingAc' => function ($q) {
                    $q->select('id', 'account_no')->with('ssbMember')->with('associate');
                }]);
            }])
            ->when('type' == 7, function ($q) {
                return $q->with(['SamraddhBank' => function ($q) {
                    $q->with('bankAccount');
                }]);
            })
            ->when('type' == 15, function ($q) {
                return $q->with(['VoucherSamraddhBank' => function ($q) {
                    $q->with('bankAccount');
                }]);
            })
            ->when('type' == 15, function ($q) {
                return $q->with(['VoucherSamraddhBankbank_ac_id' => function ($q) {
                    $q->with('bankAccount');
                }]);
            })->when('type' == 1, function ($q) {
                return $q->with('memberMemberId');
            })->when('type' == 2, function ($q) {
                return $q->with('memberMemberId');
            })->with('accountHead')->with(['loan_from_bank' => function ($q) {
                $q->with('loan_emi');
            }])->with('company_bound')->with(['bill_expense' => function ($q) {
                $q->with('head')->with('subb_head')->with('subb_head2');
            }])
            ->with('BillExpense')
            ->with(['EmployeeSalaryBytype_id' => function ($q) {
                $q->with('salary_employee');
            }])
            ->with(['RentPayment' => function ($q) {
                $q->with('rentLib');
            }])
            ->with(['RentLiabilityLedger' => function ($q) {
                $q->with('rentLib');
            }])
            ->with(['EmployeeSalary' => function ($q) {
                $q->with('salary_employee');
            }])->with('associateMember')->with('SavingAccountTranscationtype_trans_id')
            ->where('company_id', $company_id)
            ->where('branch_id', $branch_id)->whereBetween('entry_date', [$startDate, $endDate])->where('is_deleted', 0)->orderBy('entry_date', 'ASC')->offset($offset)->limit($limit)->get();
        $balance = '';
        $getBranchOpening = getBranchOpeningDetail($branch_id);
        $currentdate = date('Y-m-d');
        if ($getBranchOpening->date == $startDate) {
            $balance = $getBranchOpening->total_amount;
        }
        if ($getBranchOpening->date < $startDate) {
            if ($getBranchOpening->date != '') {
                $getBranchTotalBalance = getBranchTotalBalanceAllTran($startDate, $getBranchOpening->date, $getBranchOpening->total_amount, $branch_id, $company_id);
            } else {
                $getBranchTotalBalance = getBranchTotalBalanceAllTran($startDate, $currentdate, $getBranchOpening->total_amount, $branch_id, $company_id);
            }
            $balance = $getBranchTotalBalance;
        }
        $payment_mode[0] = 'CASH';
        $payment_mode[1] = 'CHEQUE';
        $payment_mode[2] = 'ONLINE TRANSFER';
        $payment_mode[3] = 'SSB';
        $payment_mode[4] = 'AUTO TRANSFER';
        $payment_mode[5] = 'Loan';
        $payment_mode[6] = 'JV';
        $payment_mode[8] = 'Debit Card Transfer';
        $types = getTransactionTypeCustom();
        $transactionCreatedBy = array();
        $transactionCreatedBy[0] = 'Software';
        $transactionCreatedBy[1] = 'Associate App';
        $transactionCreatedBy[2] = 'E-Passbook App';
        // Remove the last cached item
        Cache::forget('daybook_transaction');
        Cache::put('daybook_transaction', $data);
        foreach ($data as $index => $value) {
            // print_r($company_id);
            // echo"<pre>";
            // dd($value);
            $f1 = 0;
            $f2 = 0;
            if ($value->type != 21) {
                if (array_key_exists($value->type . '_' . $value->sub_type, $types)) {
                    $type = $types[$value->type . '_' . $value->sub_type];
                }
            }

            if (!empty($value->company_id)) {
                $companyname = \App\Models\Companies::where('id', $value->company_id)->value('name');
            } else {
                $companyname = 'N/A';
            }

            if ($value->type == 21 && $value->sub_type == '') {
                $record = ReceivedVoucher::where('id', $value->type_id)->first();
                if ($record) {
                    $type = $record->particular;
                } else {
                    $type = "N/A";
                }
            }
            if ($value->type == 22 || $value->type == 23) {
                if ($value->sub_type == 222) {
                    $type = $value->description;
                }
            }
            // Member Name, Member Account and Member Id
            $is_eli = 0;
            $plan_name = '';
            if ($value->sub_type == 30) {
                $is_eli = Daybook::where('id', $value->type_transaction_id)->first();
                if (isset($is_eli->is_eli)) {
                    $is_eli = $is_eli->is_eli;
                }
            }

            $memberName = 'N/A';
            $memberAccount = 'N/A';
            $plan_name = 'N/A';
            $a_name = 'N/A';
            $data = getCompleteDetail($value);
            $memberAccount = $data['memberAccount'];
            $type = $data['type'];
            $plan_name = $data['plan_name'];
            $memberId = $data['memberId'];
            $memberName = $data['memberName'];
            $a_name = $data['associate_name'];

            if ($value->payment_mode == 6) {
                $rentPaymentDetail = $value['RentLiabilityLedger'];
                $salaryDetail = $value['EmployeeSalary'];
            } else {
                $rentPaymentDetail = $value['RentPayment'];
                $salaryDetail = $value['EmployeeSalaryBytype_id'];
            }

            // Payment Type
            $cr_amount = 0;
            $dr_amnt = 0;
            if ($value->payment_type == 'CR') {
                $cr_amount = number_format((float)$value->amount, 2, '.', '');
            }
            if ($value->payment_type == 'DR') {
                $dr_amnt =  number_format((float)$value->amount, 2, '.', '');
            }
            // Balance
            if ($value->payment_mode == 0 && $is_eli == 0) {
                $balance = number_format((float)$balance, 2, '.', '');
            }
            // Ref Number
            if ($value->payment_mode == 0) {
                $ref_no = 'N/A';
            } elseif ($value->payment_mode == 1) {
                $ref_no = $value->cheque_no;
            } elseif ($value->payment_mode == 2) {
                $ref_no = $value->transction_no;
            } elseif ($value->payment_mode == 3) {
                $ref_no = $value->v_no;
            } elseif ($value->payment_mode == 6) {
                $ref_no = $value->jv_unique_id;
            } else {
                $ref_no = 'N/A';
            }
            // Payment Mode
            if ($value->sub_type == 30) {
                $pay_mode = 'ELI';
            } else
                if ($value->payment_mode == 0) {
                $pay_mode = 'CASH';
            } elseif ($value->payment_mode == 1) {
                $pay_mode = 'CHEQUE';
            } elseif ($value->payment_mode == 2) {
                $pay_mode = 'ONLINE TRANSFER';
            } elseif ($value->payment_mode == 3) {
                $pay_mode = 'SSB';
            } elseif ($value->payment_mode == 4) {
                $pay_mode = 'AUTO TRANSFER';
            } elseif ($value->payment_mode == 5) {
                $pay_mode = 'From loan';
            } elseif ($value->payment_mode == 8) {
                $pay_mode = 'SSB Debit Cron';
            } elseif ($value->payment_mode == 6) {
                $pay_mode = 'JV';
            }
            if ($value->entry_date) {
                $date = date("d/m/Y", strtotime(convertDate($value->entry_date)));
            } else {
                $date = 'N/A';
            }
            // if($value->payment_mode == 0 && $is_eli == 0 ) 
            // {
            //   $balance =   number_format((float)$balance, 2, '.', '');
            // }
            // else{
            //     $balance = '';
            // }
            // tag
            $tag = '';
            if ($value->type = 3) {
                if ($value->sub_type == 31) {
                    $tag = 'N';
                }
                if ($value->sub_type == 32) {
                    $tag = 'R';
                }
            }
            if ($value->type == 4) {
                if ($value->sub_type == 41) {
                    $tag = 'N';
                }
                if ($value->sub_type == 42) {
                    $tag = 'R';
                }
                if ($value->sub_type == 43) {
                    $tag = 'W';
                }
            }
            if ($value->type == 5) {
                if ($value->sub_type == 51) {
                    $tag = 'LD';
                }
                if ($value->sub_type == 52) {
                    $tag = 'L';
                }
                if ($value->sub_type == 54) {
                    $tag = 'LD';
                }
                if ($value->sub_type == 55) {
                    $tag = 'L';
                }
            }
            if ($value->type == 7) {
                $tag = 'B';
            }
            if ($value->type == 13) {
                if ($value->sub_type == 131) {
                    $tag = 'E';
                }
                if ($value->sub_type == 133) {
                    $tag = 'M';
                }
                if ($value->sub_type == 134) {
                    $tag = 'M';
                }
                if ($value->sub_type == 135) {
                    $tag = 'M';
                }
                if ($value->sub_type == 136) {
                    $tag = 'M';
                }
                if ($value->sub_type == 137) {
                    $tag = 'M';
                }
            }
            if ($value->payment_type == 'CR') {
                if ($value->payment_mode == 0 && $is_eli == 0) {
                    $balance = $balance + $value->amount;
                }
            }
            if ($value->payment_type == 'DR') {
                if ($value->payment_mode == 0 && $is_eli == 0) {
                    $balance = $balance - $value->amount;
                }
            }
            // if($value->payment_mode == 0 && $is_eli == 0 ) 
            // {
            //   $balance =   number_format((float)$balance, 2, '.', '');
            // }
            // else{
            //     $balance = '';
            // }
            $records = "
                        <tr>
                            <td>" . $i . "</td>
                            <td>" . $date . "</td>
                            <td>" . $value->id . "</td>
                            <td>" . $transactionCreatedBy[($value->is_app) ? $value->is_app : 0] . "</td>
                            <td>" . $memberAccount . "</td>
                            <td>" . $companyname . "</td>
                            <td>" . $plan_name . "</td>
                            <td>" . $memberName . "</td>
                            <td>" . $a_name . "</td>
                            <td>" . $type . "</td>
                            <td>" . $value->description_cr . "</td>
                            <td>" . $value->description_dr . "</td>
                            <td>" . $cr_amount . "</td>
                            <td>" . $dr_amnt . "</td>
                            <td>" . $balance . "</td>
                            <td>" . $ref_no . "</td>
                            <td>" . $pay_mode . "</td>
                            <td>" . $tag . "</td>
                        </tr>";
            $rowReturn[] = $records;
            $balance_closing[]  = $balance;
            $i =  $i + 1;
        }
        $sNo = array($i);
        return response()->json(['data' => $rowReturn, 'c_balance' => $balance, 'sno' => $sNo, 'msg' => 'success']);
    }
}
