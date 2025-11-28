<?php

namespace App\Http\Controllers\Admin\Report;

use App\Models\MemberIdProof;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use App\Models\SavingAccount;
use App\Models\SavingAccountTranscation;
use App\Models\EmployeeSalary;
use App\Models\Daybook;
use App\Models\BranchCurrentBalance;
use App\Models\ReceivedVoucher;
use App\Models\Branch;
use App\Models\BranchCash;
use App\Models\Transcation;
use App\Models\Memberinvestments;
use App\Models\Plans;
use App\Models\AccountHeads;
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
use DB;
use Cache;
use App\Services\Email;
use App\Services\Sms;
use Illuminate\Support\Facades\Schema;
use App\Http\Traits\BalanceSheetTrait;

/*
    |---------------------------------------------------------------------------
    | Admin Panel -- Member Management MemberController
    |--------------------------------------------------------------------------
    |
    | This controller handles members all functionlity.
*/

class DayBookDublicateController extends Controller
{
    use BalanceSheetTrait;

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
        $data['title'] = 'Report | DayBook  Report';
        if (check_my_permission(Auth::user()->id, "141") != "1") {
            return redirect()->route('admin.dashboard');
        }
        if (Auth::user()->branch_id > 0) {
            $id = Auth::user()->branch_id;
            $data['branch'] = Branch::where('status', 1)->where('id', '=', $id)->get();
        } else {
            $data['branch'] = Branch::where('status', 1)->get();
        }
        return view('templates.admin.report.dublicate.day_book', $data);
    }
    public function print_day_bookReport()
    {
        $token = session()->get('_token');
        $alltransaction = Cache::get('daybook_transaction'.$token);
        $data['title'] = 'Report | Duplicate DayBook  Report';
        $startDate = '';
        $endDate = '';
        $branch_id = '';
        if (isset($_GET['from_date'])) {
            $startDate = $_GET['from_date'];
        }
        if (isset($_GET['to_date'])) {
            $endDate = $_GET['to_date'];
        }
        if (isset($_GET['branch'])) {
            $branch_id = hex2bin($_GET['branch']);
        }
        if (isset($_GET['company_id'])) {
            $company_id = hex2bin($_GET['company_id']);
        }
        $data['company_id'] =  $company_id;
        $planDaily = getPlanID('710')->id;
        $dailyId = array($planDaily);
        $planSSB = getPlanID('703')->id;
        $planKanyadhan = getPlanID('709')->id;
        $planMB = !empty(getPlanID('708')->id) ? getPlanID('708')->id :  null;
        $planFRD = getPlanID('707')->id;
        $planJeevan = getPlanID('713')->id;
        $planRD = getPlanID('704')->id;
        $planBhavhishya = getPlanID('718')->id;
        $monthlyId = array($planKanyadhan, $planMB, $planFRD, $planJeevan, $planRD, $planBhavhishya);
        $planMI = getPlanID('712')->id;
        $planFFD = getPlanID('705')->id;
        $planFD = getPlanID('706')->id;
        $fdId = array($planMI, $planFFD, $planFD);
        $tenure = array(1, 3, 5, 7, 10);
        $existsopening = BranchCurrentBalance::where('branch_id', $branch_id)->where('company_id', $company_id)->where('entry_date', $startDate)->exists();
        $cashInhandOpening =   BranchCurrentBalance::first();
       
        if ($existsopening) {
            $cashInhandOpening =   BranchCurrentBalance::where('branch_id', $branch_id)->where('company_id', $company_id)->where('entry_date', $startDate)->orderBy('entry_date', 'DESC')->first();
            $data['cashInhandOpening'] = number_format((float)$cashInhandOpening->opening_balance + $cashInhandOpening->loan_opening_balance, 2, '.', '');
        } else {
            $cashInhandOpening =   BranchCurrentBalance::where('branch_id', $branch_id)->where('company_id', $company_id)->where('entry_date', '<', $startDate)->orderBy('entry_date', 'DESC')->first();
            if($cashInhandOpening) {
                $data['cashInhandOpening'] = number_format((float)  $cashInhandOpening->totalAmount, 2, '.', '');
            }
        }
        $cashInhandclosing = BranchCurrentBalance::where('branch_id', $branch_id)->where('company_id', $company_id)->where('entry_date', '<=', $endDate)->orderBy('entry_date', 'DESC')->first();
        if ($cashInhandclosing) {
            $data['cashInhandclosing'] = number_format((float)$cashInhandclosing->totalAmount +  $cashInhandclosing->totalAmount, 2, '.', '');
        } else {
            $data['cashInhandclosing'] = 0;
        }
        // $data['samraddhData'] = DB::table('branch_daybook')->where('branch_id', $branch_id)->where('company_id', $company_id)->whereBetween('entry_date', [$startDate, $endDate])->orderBy('entry_date', 'ASC')->where('is_deleted', 0)->get();
        $data['cash_in_hand_cr'] = BranchDaybook::where('description_dr', 'not like', '%Eli Amount%')->where('company_id', $company_id)->where('payment_mode', 0)->where('branch_id', $branch_id)->where('payment_type', 'CR')->whereBetween(\DB::raw('DATE(entry_date)'), [$startDate, $endDate])->where('is_deleted', 0)->sum('amount');
        $data['cash_in_hand_dr'] = BranchDaybook::where('payment_mode', 0)->where('branch_id', $branch_id)->where('company_id', $company_id)->where('payment_type', 'DR')->whereBetween(\DB::raw('DATE(entry_date)'), [$startDate, $endDate])->where('is_deleted', 0)->sum('amount');
        $data['cheque_cr'] = BranchDaybook::whereIn('payment_mode', [1])->where('payment_type', 'CR')->where('company_id', $company_id)->where('branch_id', $branch_id)->whereBetween(\DB::raw('DATE(entry_date)'), [$startDate, $endDate])->sum('amount');
        $data['cheque_dr'] = BranchDaybook::whereIn('payment_mode', [1])->where('payment_type', 'DR')->where('company_id', $company_id)->where('branch_id', $branch_id)->whereBetween(\DB::raw('DATE(entry_date)'), [$startDate, $endDate])->sum('amount');
        $data['bank_cr'] = BranchDaybook::whereIn('payment_mode', [1, 2])->where('payment_type', 'CR')->where('company_id', $company_id)->where('branch_id', $branch_id)->whereBetween(\DB::raw('DATE(entry_date)'), [$startDate, $endDate])->sum('amount');
        $data['bank_dr'] = BranchDaybook::whereIn('payment_mode', [1, 2])->where('payment_type', 'DR')->where('company_id', $company_id)->where('branch_id', $branch_id)->whereBetween(\DB::raw('DATE(entry_date)'), [$startDate, $endDate])->sum('amount');
        $data['file_chrg_total'] = Daybook::whereIn('transaction_type', ['6,10'])->where('branch_id', $branch_id)->where('company_id', $company_id)->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate])->count();
        $data['file_chrg_amount_total'] = Daybook::whereIn('transaction_type', ['6,10'])->where('branch_id', $branch_id)->where('company_id', $company_id)->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate])->sum('amount');;
        $data['fund_transfer_total'] = \App\Models\FundTransfer::where('transfer_type', 0)->where('branch_id', $branch_id)->where('company_id', $company_id)->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate])->count();
        $data['fund_transfer_amount_total'] = \App\Models\FundTransfer::where('transfer_type', 0)->where('branch_id', $branch_id)->where('company_id', $company_id)->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate])->sum('amount');
        $data['mi_total'] = 0;
        $data['mi_amount_total'] = 0;
        $data['stn_total'] =0;
        $data['stn_amount_total'] = 0;
        $data['other_total__income_account'] = getExpenseHeadaccountCount(3, 1, $startDate, $endDate, $branch_id);
        $data['other_total__expense_account'] = getExpenseHeadaccountCount(4, 1, $startDate, $endDate, $branch_id);
        $data['other_total__income_amount'] = headTotalNew(3, $startDate, $endDate, $branch_id,$company_id);
        $data['other_total__expense_amount'] = headTotalNew(4, $startDate, $endDate, $branch_id,$company_id);
        $data['investment_stationary_chrg_account'] = getInvestmentStationarychrgAccount($startDate, $endDate, $branch_id,$company_id);
        $data['investment_stationary_chrg_amount'] = getInvestmentStationarychrgAmount($startDate, $endDate, $branch_id,$company_id);
        $data['investment_stationary_chrg_account'] = getInvestmentStationarychrgAccount($startDate, $endDate, $branch_id,$company_id);
        $data['investment_stationary_chrg_amount'] = getInvestmentStationarychrgAmount($startDate, $endDate, $branch_id,$company_id);
        $data['loan_total_account'] = LoanDayBooks::where('loan_sub_type', 0)->where('branch_id', $branch_id)->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate])->where('is_deleted', 0)->count();
        // dd($data['loan_total_account']);
        $data['loan_total_amount'] = LoanDayBooks::where('loan_sub_type', 0)->where('branch_id', $branch_id)->where('company_id', $company_id)->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate])->where('is_deleted', 0)->sum('deposit');
        $data['received_voucher_account'] = ReceivedVoucher::where('branch_id', $branch_id)->where('company_id', $company_id)->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate])->count();;
        $data['received_voucher_amount'] = ReceivedVoucher::where('branch_id', $branch_id)->where('company_id', $company_id)->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate])->sum('amount');
        $renew_emi_recovery_12_Id = getmemberinvestementPlanwise($startDate, $endDate, $branch_id, $planDaily, '12',$company_id);
        $renew_emi_recovery_24_Id = getmemberinvestementPlanwise($startDate, $endDate, $branch_id, $planDaily, '24',$company_id);
        $renew_emi_recovery_36_Id  = getmemberinvestementPlanwise($startDate, $endDate, $branch_id, $planDaily, '36',$company_id);
        $renew_emi_recovery_60_Id  = getmemberinvestementPlanwise($startDate, $endDate, $branch_id, $planDaily, '60',$company_id);
       
        $data['ssbNiAc'] = totalSSbAccountByType($startDate, $endDate, $branch_id, 1,$company_id);
        $data['ssbNiAmount'] = number_format((float)totalSSbAmtByType($startDate, $endDate, $branch_id, 1,$company_id), 2, '.', '');
        $data['ssbRenewAc'] = totalSSbAccountByType($startDate, $endDate, $branch_id, 2,$company_id);
        $data['ssbRenewAmount'] = number_format((float)totalSSbAmtByType($startDate, $endDate, $branch_id, 2,$company_id), 2, '.', '');
        $data['ssbWAc'] = totalSSbAccountByType($startDate, $endDate, $branch_id, 5,$company_id);
        $data['ssbWAmount'] = number_format((float)totalSSbAmtByType($startDate, $endDate, $branch_id, 5,$company_id), 2, '.', '');
        $data['branch_id'] = $branch_id;
         $data['bank'] = SamraddhBank::with('bankAccount')->where('company_id',$company_id)->get();
        // print_r($data['startDate']);die;
        // sourab code
        $aa = BranchDaybookAmount($startDate, $endDate, $branch_id,$company_id);
        $cash_in_hand['DR'] = 0;
        $cash_in_hand['CR'] = 0;
        if (array_key_exists('0_CR', $aa)) {
            $cash_in_hand['CR'] = $aa['0_CR'];
        }
        if (array_key_exists('0_DR', $aa)) {
            $cash_in_hand['DR'] = $aa['0_DR'];
        }
        $getBranchOpening_cash = getBranchOpeningDetail($branch_id);
        $balance_cash = 0;
        $C_balance_cash = 0;
        $currentdate = date('Y-m-d');
        if ($getBranchOpening_cash->date == $startDate) {
            $balance_cash = $getBranchOpening_cash->total_amount;
            if ($endDate == '') {
                $endDate = $currentdate;
            }
        }
        if ($getBranchOpening_cash->date < $startDate) {
            if ($getBranchOpening_cash->date != '') {
                $getBranchTotalBalance_cash = getBranchTotalBalanceAllTran($startDate, $getBranchOpening_cash->date, $getBranchOpening_cash->total_amount, $branch_id,$company_id);
            } else {
                $getBranchTotalBalance_cash = getBranchTotalBalanceAllTran($startDate, $currentdate, $getBranchOpening_cash->total_amount, $branch_id,$company_id);
            }

            $balance_cash = $getBranchTotalBalance_cash;
            $data['balance_cash'] = $balance_cash;
            if ($endDate == '') {
                $endDate = $currentdate;
            }
        }
        $getTotal_DR = getBranchTotalBalanceAllTranDR($startDate, $endDate, $branch_id,$company_id);
        $getTotal_CR = getBranchTotalBalanceAllTranCR($startDate, $endDate, $branch_id,$company_id);
        $totalBalance = $getTotal_CR - $getTotal_DR;
        $data['C_balance_cash'] = $balance_cash + $totalBalance;

        //Data Get All Records
        $alldata =  BranchDaybook::/*select('id','type','sub_type','type_id','type_transaction_id','entry_date','amount','opening_balance','closing_balance','transction_bank_to','description_cr','description_dr;)*/
        with(['memberCompanybyMemberId.member','associateMember','member_investment' => function ($q) {
            $q->select('id', 'account_number', 'plan_id', 'member_id', 'associate_id', 'customer_id')
                ->with(['member' => function ($q) {
                    $q->select('id', 'member_id', 'first_name', 'last_name');
                }])
            ->with('ssb','plan','associateMember','memberCompany')
            ;
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
                    $q->select('id', 'account_number', 'plan_id', 'member_id', 'account_number')->with('plan')
                        ->with('member');
                }])->with(['expenses' => function ($qa) {
                    $qa->select('id')->with('advices');
                }]);
            }])
            ->with(['member' => function ($q) {
                $q->select('id', 'member_id', 'first_name', 'last_name');
            }])
            ->with('receivedvoucherbytype_id')
            ->with('receivedvoucherbytype_transaction_id')
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
            })
            ->when('type' == 1, function ($q) {
                return $q->with('memberMemberId');
            })
            ->when('type' == 2, function ($q) {
                return $q->with('memberMemberId');
            })
            ->with('accountHead')->with(['loan_from_bank' => function ($q) {
                $q->with('loan_emi');
            }])
            ->with('company_bound')->with(['bill_expense' => function ($q) {
                $q->with('head')
                    ->with('subb_head')
                    ->with('subb_head2');
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
            }])
            ->with('associateMember')
            ->with('SavingAccountTranscationtype_trans_id')
            ->where('branch_id', $branch_id)
            ->where('company_id', $company_id)
            ->where('amount', '>', 0)
            ->whereBetween('entry_date', [$startDate, $endDate])
            ->where('is_deleted', 0)
            ->orderBy('entry_date', 'ASC')
            ->get();

        $data['alltransaction'] = $alldata;
        
        // $clBalance=$cash_in_hand['CR']-$cash_in_hand['DR']+$cashInhandOpening;
        //sourab code
        return view('templates.admin.report.dublicate.print_day_book', $data);
    }
    public function day_filterbookReport(Request $request)
    {
        /*
        if ($request->ajax()) {
            if ($request['start_date'] != '') {
                $startDate = date("Y-m-d", strtotime(convertDate($request['start_date'])));
            } else {
                $startDate = '';
            }
            if ($request['end_date'] != '') {
                $endDate = date("Y-m-d ", strtotime(convertDate($request['end_date'])));
            } else {
                $endDate = '';
            }
            if ($request['branch_id'] != '') {
                $branch_id = $request['branch_id'];
            } else {
                $branch_id = '';
            }
            if ($request['company_id'] != '') {
                $company_id = $request['company_id'];
            } else {
                $company_id = '';
            }
            if (isset($request['is_search']) && $request['is_search'] == 'yes') {
                $cash_in_hand['CR'] = BranchDaybook::where('description_dr', 'not like', '%Eli Amount%')->where('payment_mode', 0)->where('branch_id', $branch_id)->where('company_id', $company_id)->where('payment_type', 'CR')->whereBetween(\DB::raw('DATE(entry_date)'), [$startDate, $endDate])->where('is_deleted', 0)->sum('amount');
                $existsopening = BranchCurrentBalance::where('branch_id', $branch_id)->where('company_id', $company_id)->where('entry_date', $startDate)->exists();
                if ($existsopening) {
                    $cashInhandOpening =   BranchCurrentBalance::where('branch_id', $branch_id)->where('company_id', $company_id)->where('entry_date', $startDate)->orderBy('entry_date', 'DESC')->first();
                    $cashInhandOpening = $cashInhandOpening->totalAmount;
                } else {
                    $cashInhandOpening =   BranchCurrentBalance::where('branch_id', $branch_id)->where('company_id', $company_id)->where('entry_date', '<=', $startDate)->orderBy('entry_date', 'DESC')->first();
                    if ($cashInhandOpening) {
                        $cashInhandOpening = number_format((float)  $cashInhandOpening->totalAmount, 2, '.', '');
                    } else {
                        $cashInhandOpening = 0;
                    }
                }
                $cashInhandclosing = BranchCurrentBalance::where('branch_id', $branch_id)->where('company_id', $company_id)->where('entry_date', '<=', $endDate)->orderBy('entry_date', 'DESC')->first();
                if (isset($cashInhandclosing->totalAmount)) {
                    $cashInhandclosing = $cashInhandclosing->totalAmount;
                } else {
                    $cashInhandclosing =  0;
                }
                $cash_in_hand['DR'] = BranchDaybook::where('payment_mode', 0)->where('branch_id', $branch_id)->where('company_id', $company_id)->where('payment_type', 'DR')->whereBetween(\DB::raw('DATE(entry_date)'), [$startDate, $endDate])->where('is_deleted', 0)->sum('amount');
                $cheque['CR'] = BranchDaybook::whereIn('payment_mode', [1])->where('payment_type', 'CR')->where('company_id', $company_id)->where('branch_id', $branch_id)->where('is_deleted', 0)->whereBetween(\DB::raw('DATE(entry_date)'), [$startDate, $endDate])->where('is_deleted', 0)->sum('amount');
                $cheque['DR'] = BranchDaybook::whereIn('payment_mode', [1])->where('payment_type', 'DR')->where('company_id', $company_id)->where('branch_id', $branch_id)->where('is_deleted', 0)->whereBetween(\DB::raw('DATE(entry_date)'), [$startDate, $endDate])->sum('amount');
                $bank['CR'] = BranchDaybook::whereIn('payment_mode', [1, 2])->where('payment_type', 'CR')->where('company_id', $company_id)->where('branch_id', $branch_id)->where('is_deleted', 0)->whereBetween('entry_date', [$startDate, $endDate])->sum('amount');
                $bank['DR'] = BranchDaybook::whereIn('payment_mode', [1, 2])->where('payment_type', 'DR')->where('company_id', $company_id)->where('branch_id', $branch_id)->where('is_deleted', 0)->whereBetween('entry_date', [$startDate, $endDate])->sum('amount');
                $data = DB::table('branch_daybook')->where('branch_id', $branch_id)->where('company_id', $company_id)->whereBetween('entry_date', [$startDate, $endDate])->orderBy('entry_date', 'ASC')->where('is_deleted', 0)->get();
                $bank = SamraddhBank::with('bankAccount')->where('company_id', $company_id)->get();

                $aa = BranchDaybookAmount($startDate, $endDate, $branch_id, $company_id);
                $cheque['CR'] = 0;
                $cheque['DR'] = 0;
                $cash_in_hand['DR'] = 0;
                $cash_in_hand['CR'] = 0;
                if (array_key_exists('0_CR', $aa)) {
                    $cash_in_hand['CR'] = $aa['0_CR'];
                }
                if (array_key_exists('0_DR', $aa)) {
                    $cash_in_hand['DR'] = $aa['0_DR'];
                }
                if (array_key_exists('1_CR', $aa)) {
                    $cheque['CR'] = $aa['1_CR'];
                }
                if (array_key_exists('1_DR', $aa)) {
                    $cheque['DR'] = $aa['1_DR'];
                }
                $bank['CR'] = 0;
                $bank['DR'] = 0;
                if (array_key_exists('2_CR', $aa) && array_key_exists('1_CR', $aa)) {
                    $bank['CR'] = $aa['1_CR'] + $aa['2_CR'];
                }
                if (array_key_exists('2_DR', $aa) && array_key_exists('1_DR', $aa)) {
                    $bank['DR'] = $aa['1_DR'] + $aa['2_DR'];
                }
                $bank = SamraddhBank::with('bankAccount')->where('company_id', $company_id)->get();
            }
            return \Response::json(['view' => view('templates.admin.report.dublicate.filtered_sheet_day_book', [
                'cashInhand' => $cash_in_hand, 
                'cashInhandOpening' => $cashInhandOpening, 
                'cashInhandclosing' => $cashInhandclosing, 
                'cheque' => $cheque, 
                'bank' => $bank, 
                'data' => $data, 
                'end_date' => $endDate, 
                'branch_id' => $branch_id, 
                'bank' => $bank, 
                'start_date' => $startDate, 
                'company_id' => $company_id
                ])->render(), 'msg_type' => 'success']);
        }
        */
        
        // uncomment this code for making rest code correct for global filter
        if ($request->ajax()) {
            $result = $this->day_book_filter_book_report_global($request->all());
            $cash_in_hand = $result['cash_in_hand'];
            $cashInhandOpening = $result['cashInhandOpening'];
            $cashInhandclosing = $result['cashInhandclosing'];
            $cheque = $result['cheque'];
            $bank = $result['bank'];
            $data = $result['data'];
            $endDate = $result['endDate'];
            $branch_id = $result['branch_id'];
            $startDate = $result['startDate'];
            $company_id = $result['company_id'];
            return \Response::json(['view' => view('templates.admin.report.dublicate.filtered_sheet_day_book', [
                'cashInhand' => $cash_in_hand, 
                'cashInhandOpening' => $cashInhandOpening, 
                'cashInhandclosing' => $cashInhandclosing, 
                'cheque' => $cheque, 
                'bank' => $bank, 
                'data' => $data, 
                'end_date' => $endDate, 
                'branch_id' => $branch_id, 
                'start_date' => $startDate, 
                'company_id' => $company_id
                ])->render(), 'msg_type' => 'success']);
        }
        
    }
    public function transaction_list(Request $request)
    {
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
        if ($request['branch_id'] != '') {
            $branch_id = $request->branch_id;
        } else {
            $branch_id = '';
        }
        if ($request['company_id'] != '') {
            $company_id = $request->company_id;
        } else {
            $company_id = '';
        }
        $balance_closing = array();
        $rowReturn = array();
        $offset = $request->limit * $request->page;
        $limit = $request->limit;
        $data =  BranchDaybook::with(['memberCompanybyMemberId.member','associateMember','member_investment' => function ($q) {
            $q->select('id', 'account_number', 'plan_id', 'member_id', 'associate_id', 'customer_id')
                ->with(['member' => function ($q) {
                    $q->select('id', 'member_id', 'first_name', 'last_name');
                }])
            ->with('ssb','plan','associateMember','memberCompany')
            ;
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
                    $q->select('id', 'account_number', 'plan_id', 'member_id', 'account_number')->with('plan')
                        ->with('member');
                }])->with(['expenses' => function ($qa) {
                    $qa->select('id')->with('advices');
                }]);
            }])
            ->with(['member' => function ($q) {
                $q->select('id', 'member_id', 'first_name', 'last_name');
            }])
            ->with('receivedvoucherbytype_id')
            ->with('receivedvoucherbytype_transaction_id')
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
            })
            ->when('type' == 1, function ($q) {
                return $q->with('memberMemberId');
            })
            ->when('type' == 2, function ($q) {
                return $q->with('memberMemberId');
            })
            ->with('accountHead')->with(['loan_from_bank' => function ($q) {
                $q->with('loan_emi');
            }])
            ->with('company_bound')->with(['bill_expense' => function ($q) {
                $q->with('head')
                    ->with('subb_head')
                    ->with('subb_head2');
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
            }])
            ->with('associateMember')
            ->with('SavingAccountTranscationtype_trans_id')
            ->where('branch_id', $branch_id)
            ->where('company_id', $company_id)
            ->where('amount', '>', 0)
            ->whereBetween('entry_date', [$startDate, $endDate])
            ->where('is_deleted', 0)
            ->orderBy('entry_date', 'ASC')
            ->offset($offset)
            ->limit($limit)
            ->get();
        // dd($data->toArray());
        $balance = '';
        $getBranchOpening = getBranchOpeningDetail($branch_id);
        

        $totalcashInhandclosing = BranchCurrentBalance::where('branch_id', $branch_id)->where('company_id', $company_id)->where('entry_date', '<=', $endDate)->orderBy('entry_date', 'DESC')->first();
                if (isset($totalcashInhandclosing->totalAmount)) {
                    $totalcashInhandclosing = $totalcashInhandclosing->totalAmount;
                } else {
                    $totalcashInhandclosing =  0;
                }

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
        $payment_mode[8] = 'SSb Debit Cron';
        $types = getTransactionTypeCustom();
        $transactionCreatedBy = array();
        $transactionCreatedBy[0] = 'Software';
        $transactionCreatedBy[1] = 'Associate App';
        $transactionCreatedBy[2] = 'E-Passbook App';

        $token = session()->get('_token');
        Cache::put('daybook_transactionAdmin'.$token, $data);
        foreach ($data as $index => $value) {
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
                $is_eli = Daybook::where('id', $value->type_transaction_id)->where('company_id',$company_id)->first();
                if (isset($is_eli->is_eli)) {
                    $is_eli = $is_eli->is_eli;
                }
            }
            $memberName = 'N/A';
            $memberAccount = 'N/A';
            $plan_name = 'N/A';

            if ($value->payment_mode == 6) {
                $rentPaymentDetail = $value['RentLiabilityLedger'];
                $salaryDetail = $value['EmployeeSalary'];
            } else {
                $rentPaymentDetail = $value['RentPayment'];
                $salaryDetail = $value['EmployeeSalaryBytype_id'];
            }

            $memberName = 'N/A';
            $memberAccount = 'N/A';
            $plan_name = 'N/A';
            $data = getCompleteDetail($value);
            
            $memberAccount = $data['memberAccount'];
            $type = $data['type'];
            $plan_name = $data['plan_name'];
            $memberId = $data['memberId'];
            $memberName = $data['memberName'];
            $a_name = $data['associate_name']??'N/A';

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
        return response()->json(['data' => $rowReturn, 'c_balance' => $balance_closing, 'sno' => $sNo, 'msg' => 'success']);
    }
    /** day_book_filter_book_report_global function created by sourab on 31-10-2023 */
    public function day_book_filter_book_report_global($request){
        $startDate = $request['start_date'] != '' ? date("Y-m-d", strtotime(convertDate($request['start_date']))): '';
        $endDate = $request['end_date'] != '' ? date("Y-m-d", strtotime(convertDate($request['end_date']))): '';
        $branch_id = isset($request['branch_id']) ? $request['branch_id'] : ($request['branch']??'0');
        $company_id = $request['company_id'] ?? '0';

        // if (isset($request['is_search']) && $request['is_search'] == 'yes') {
            $cash_in_hand['CR'] = BranchDaybook::where('description_dr', 'not like', '%Eli Amount%')
                ->where('payment_mode', 0)
                ->where('branch_id', $branch_id)
                ->when($company_id != '0',function($q) use($company_id){
                    $q->where('company_id', $company_id);
                })
                ->where('payment_type', 'CR')
                ->whereBetween(\DB::raw('DATE(entry_date)'), [$startDate, $endDate])
                ->where('is_deleted', 0)
                ->sum('amount');
            $existsopening = BranchCurrentBalance::where('branch_id', $branch_id)
                ->when($company_id != '0',function($q) use($company_id){
                    $q->where('company_id', $company_id);
                })
                ->where('entry_date', $startDate)
                ->exists();
            if ($existsopening) {
                $cashInhandOpening =  BranchCurrentBalance::where('branch_id', $branch_id)
                    ->when($company_id != '0',function($q) use($company_id){
                        $q->where('company_id', $company_id);
                    })
                    ->where('entry_date', $startDate)
                    ->orderBy('entry_date', 'DESC')
                    ->first();
                $cashInhandOpening = $cashInhandOpening->totalAmount;
            } else {
                $cashInhandOpening =   BranchCurrentBalance::where('branch_id', $branch_id)
                    ->when($company_id != '0',function($q) use($company_id){
                        $q->where('company_id', $company_id);
                    })
                    ->where('entry_date', '<=', $startDate)
                    ->orderBy('entry_date', 'DESC')
                    ->first();
                if ($cashInhandOpening) {
                    $cashInhandOpening = number_format((float)  $cashInhandOpening->totalAmount, 2, '.', '');
                } else {
                    $cashInhandOpening = 0;
                }
            }
            $cashInhandclosing = BranchCurrentBalance::where('branch_id', $branch_id)
                ->when($company_id != '0',function($q) use($company_id){
                    $q->where('company_id', $company_id);
                })
                ->where('entry_date', '<=', $endDate)
                ->orderBy('entry_date', 'DESC')
                ->first();
            if (isset($cashInhandclosing->totalAmount)) {
                $cashInhandclosing = $cashInhandclosing->totalAmount;
            } else {
                $cashInhandclosing =  0;
            }
            // We won't be incorporating all the company filters here.
            
            // $cash_in_hand['DR'] = BranchDaybook::where('payment_mode',[0])->where('payment_type', 'DR')->where('company_id', $company_id)->where('branch_id', $branch_id)->where('is_deleted', 0)->whereBetween(\DB::raw('DATE(entry_date)'), [$startDate, $endDate])->sum('amount');
            $cash_in_hand['DR'] = $this->amountBranchDaybook([0],'DR',$company_id,$branch_id,$startDate,$endDate);
            // $cheque['CR'] = BranchDaybook::whereIn('payment_mode', [1])->where('payment_type', 'CR')->where('company_id', $company_id)->where('branch_id', $branch_id)->where('is_deleted', 0)->whereBetween(\DB::raw('DATE(entry_date)'), [$startDate, $endDate])->sum('amount');
            $cheque['CR'] = $this->amountBranchDaybook([1],'CR',$company_id,$branch_id,$startDate,$endDate);
            // $cheque['DR'] = BranchDaybook::whereIn('payment_mode', [1])->where('payment_type', 'DR')->where('company_id', $company_id)->where('branch_id', $branch_id)->where('is_deleted', 0)->whereBetween(\DB::raw('DATE(entry_date)'), [$startDate, $endDate])->sum('amount');
            $cheque['DR'] = $this->amountBranchDaybook([1],'DR',$company_id,$branch_id,$startDate,$endDate);
            // $bank['CR'] = BranchDaybook::whereIn('payment_mode', [1, 2])->where('payment_type', 'CR')->where('company_id', $company_id)->where('branch_id', $branch_id)->where('is_deleted', 0)->whereBetween('entry_date', [$startDate, $endDate])->sum('amount');
            $bank['CR'] = $this->amountBranchDaybook([1,2],'CR',$company_id,$branch_id,$startDate,$endDate);
            // $bank['DR'] = BranchDaybook::whereIn('payment_mode', [1, 2])->where('payment_type', 'DR')->where('company_id', $company_id)->where('branch_id', $branch_id)->where('is_deleted', 0)->whereBetween('entry_date', [$startDate, $endDate])->sum('amount');
            $bank['DR'] = $this->amountBranchDaybook([1,2],'DR',$company_id,$branch_id,$startDate,$endDate);           
           
            $data = DB::table('branch_daybook')->where('branch_id', $branch_id)->where('company_id', $company_id)->whereBetween('entry_date', [$startDate, $endDate])->orderBy('entry_date', 'ASC')->where('is_deleted', 0)->get();
            $bank = SamraddhBank::with('bankAccount')->where('company_id', $company_id)->get();
            $data = BranchDaybook::query()
                ->where('branch_id', $branch_id)
                ->when($company_id != '0',function($q) use($company_id){
                    $q->where('company_id', $company_id);
                })
                ->whereBetween('entry_date', [$startDate, $endDate])
                ->orderBy('entry_date', 'ASC')
                ->where('is_deleted', 0)
                ->get();
            
            /** this BranchDaybookAmount function is from helper not created by sourab. only modify by sourab on 31-10-2023. */
            $aa = BranchDaybookAmount($startDate, $endDate, $branch_id, $company_id);
            $cheque['CR'] = 0;
            $cheque['DR'] = 0;
            $cash_in_hand['DR'] = 0;
            $cash_in_hand['CR'] = 0;
            if (array_key_exists('0_CR', $aa)) {
                $cash_in_hand['CR'] = $aa['0_CR'];
            }
            if (array_key_exists('0_DR', $aa)) {
                $cash_in_hand['DR'] = $aa['0_DR'];
            }
            if (array_key_exists('1_CR', $aa)) {
                $cheque['CR'] = $aa['1_CR'];
            }
            if (array_key_exists('1_DR', $aa)) {
                $cheque['DR'] = $aa['1_DR'];
            }
            $bank['CR'] = 0;
            $bank['DR'] = 0;
            if (array_key_exists('2_CR', $aa) && array_key_exists('1_CR', $aa)) {
                $bank['CR'] = $aa['1_CR'] + $aa['2_CR'];
            }
            if (array_key_exists('2_DR', $aa) && array_key_exists('1_DR', $aa)) {
                $bank['DR'] = $aa['1_DR'] + $aa['2_DR'];
            }
            /** get samraddh_bank table details as per company_id */
            $bank = SamraddhBank::with('bankAccount')->when($company_id != '0',function($q)use($company_id){
                    $q->where('company_id', $company_id);
            })->get();
        // }
        $result['cash_in_hand'] = $cash_in_hand;
        $result['cashInhandOpening'] = $cashInhandOpening;
        $result['cashInhandclosing'] = $cashInhandclosing;
        $result['cheque'] = $cheque;
        $result['bank'] = $bank;
        $result['data'] = $data;
        $result['endDate'] = $endDate;
        $result['branch_id'] = $branch_id;
        $result['startDate'] = $startDate;
        $result['company_id'] = $company_id;
        return $result;
    }
    /** amountBranchDaybook created for getting sum of amount from branch_daybooks table
     * created by Sourab on 31-10-2023
     * @param  array $payment_type
     * @param mixed $payment_type
     * @param integer $company_id
     * @param integer $branch_id
     * @param string $startDate
     * @param string $endDate
     * @return 
     */
    public function amountBranchDaybook($payment_modem,$payment_type,$company_id,$branch_id,$startDate,$endDate){
        $data = BranchDaybook::whereIn('payment_mode', $payment_modem)
            ->where('payment_type', $payment_type)
            ->when($company_id != '0',function($q) use($company_id){
                $q->where('company_id', $company_id);
            })
            ->where('branch_id', $branch_id)
            ->where('is_deleted', 0)
            ->whereBetween('entry_date', [$startDate, $endDate])
            ->sum('amount');
        return $data;
    }
}
