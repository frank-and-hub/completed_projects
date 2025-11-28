<?php

namespace App\Http\Controllers\Admin\Report;

use App\Models\MemberIdProof;

use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\Hash;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use Validator;

use App\Models\Daybook;

use App\Models\LoanDayBooks;

use App\Models\Branch;

use App\Models\Transcation;

use App\Models\Memberinvestments;

use App\Models\Plans;

use App\Models\AccountHeads;

use App\Http\Controllers\Admin\CommanController;

use App\Models\Member;

use Yajra\DataTables\DataTables;

use Carbon\Carbon;

use Session;

use Image;

use Redirect;

use URL;

use DB;

use App\Services\Email;

use App\Services\Sms;

use Illuminate\Support\Facades\Schema;

/*

    |---------------------------------------------------------------------------

    | Admin Panel -- Report Management BranchBusinessController

    |--------------------------------------------------------------------------

    |

    | This controller handles branch_business report all functionlity.

*/

class BranchBusinessController extends Controller
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

     * Show Branch Business report.

     * Route: /admin/report/branch_business 

     * Method: get 

     * @return  array()  Response

     */

    //Branch Business Report (AMAN !! 15-05)

    public function branch_business()
    {
        if (check_my_permission(Auth::user()->id, "127") != "1") {
            return redirect()->route('admin.dashboard');
        }

        $data['title'] = 'Report | Daily Business  Report';

        if (Auth::user()->branch_id > 0) {
            $data['branch'] = Branch::where('status', 1)
                ->where('id', Auth::user()->branch_id)
                ->get();
        } else {
            $data['branch'] = Branch::where('status', 1)->get();
        }

        return view('templates.admin.report.branch_business', $data);
    }

    public function branch_business_listing(Request $request)
    {
        if ($request->ajax()) {
            if ($request['start_date'] != '') {
                $startDate = date("Y-m-d", strtotime(convertDate($request['start_date'])));
            } else {
                $startDate = date('Y-m-d', strtotime($request->associate_report_currentdate));
            }

            if ($request['end_date'] != '') {
                $endDate = date("Y-m-d ", strtotime(convertDate($request['end_date'])));
            } else {
                $endDate = date('Y-m-d', strtotime($request->associate_report_currentdate));
            }

            if ($request['branch'] != '') {
                $branch_id = $request['branch'];
            } else {
                $branch_id = '';
            }

            if ($request['company_id'] != '') {
                $company_id = $request['company_id'];
            } else {
                $company_id = '';
            }

            if (isset($request['is_search']) && $request['is_search'] == 'yes') {
                $account_head = AccountHeads::where(function ($q) {
                    $q
                        ->orwhere('parent_id', 14)

                        ->orwhere('parent_id', 86);
                })
                    ->where('status', 0)
                    ->get();

                $planDaily = getPlanID('710')->id;

                $dailyId = [$planDaily];

                $planSSB = getPlanID('703')->id;

                $planKanyadhan = getPlanID('709')->id;

                $planMB = getPlanID('708')->id;

                $planFRD = getPlanID('707')->id;

                $planJeevan = getPlanID('713')->id;

                $planRD = getPlanID('704')->id;

                $planBhavhishya = getPlanID('718')->id;

                $monthlyId = [$planKanyadhan, $planMB, $planFRD, $planJeevan, $planRD, $planBhavhishya];

                $planMI = getPlanID('712')->id;

                $planFFD = getPlanID('705')->id;

                $planFD = getPlanID('706')->id;

                $fdId = [$planMI, $planFFD, $planFD];

                $tenure = [1, 3, 5, 7, 10];
                dd($request->all());
                $val['current_daily_new_ac'] = branchBusinessInvestNewAcCount($startDate, $endDate, $branch_id, $planDaily);
                $val['total_case_current_daily_new_ac'] = branchBusinessTotalCaseCollectionCount($startDate, $endDate, $branch_id, [$planDaily]);

                $val['current_daily_new_deno_ac'] = branchBusinessInvestNewDenoSum($startDate, $endDate, $branch_id, $planDaily);
                $val['total_case_current_daily_new_deno_ac'] = branchBusinessTotalCaseCollectionAmountSum($startDate, $endDate, $branch_id, [$planDaily]);

                $val['current_daily_renew_ac'] = branchBusinessInvestRenewAc($startDate, $endDate, $dailyId, $branch_id);

                $val['current_daily_renew_amount_sum'] = branchBusinessInvestRenewAmountSum($startDate, $endDate, $dailyId, $branch_id);

                $val['current_monthly_new_ac'] = branchBusinessInvestNewAcCountType($startDate, $endDate, $monthlyId, $branch_id);
                $val['total_case_current_monthly_new_ac'] = branchBusinessTotalCaseCollectionCount($startDate, $endDate, $branch_id, [$monthlyId]);

                $val['current_monthly_deno_sum'] = branchBusinessInvestNewDenoSumType($startDate, $endDate, $monthlyId, $branch_id);
                $val['total_case_current_monthly_deno_sum'] = branchBusinessTotalCaseCollectionAmountSum($startDate, $endDate, $branch_id, [$monthlyId]);

                $val['current_monthly_renew_ac'] = branchBusinessInvestRenewAc($startDate, $endDate, $monthlyId, $branch_id);

                $val['current_monthly_renew_amount_sum'] = branchBusinessInvestRenewAmountSum($startDate, $endDate, $monthlyId, $branch_id);

                $val['current_fd_new_ac'] = branchBusinessInvestNewAcCountType($startDate, $endDate, $fdId, $branch_id);
                $val['total_case_current_fd_new_ac'] = branchBusinessTotalCaseCollectionCount($startDate, $endDate, $branch_id, [$fdId]);

                $val['current_fd_deno_sum'] = branchBusinessInvestNewDenoSumType($startDate, $endDate, $fdId, $branch_id);
                $val['total_case_current_fd_deno_sum'] = branchBusinessTotalCaseCollectionAmountSum($startDate, $endDate, $branch_id, [$fdId]);

                $val['current_fd_renew_ac'] = branchBusinessInvestRenewAc($startDate, $endDate, $fdId, $branch_id);

                $val['current_fd_renew'] = branchBusinessInvestRenewAmountSum($startDate, $endDate, $fdId, $branch_id);

                $val['current_daily_new_ac_tenure12'] = branchBusinessTenureInvestNewAcCount($startDate, $endDate, $branch_id, $planDaily, '12');

                $val['current_daily_new_ac_tenure24'] = branchBusinessTenureInvestNewAcCount($startDate, $endDate, $branch_id, $planDaily, '24');

                $val['current_daily_new_ac_tenure36'] = branchBusinessTenureInvestNewAcCount($startDate, $endDate, $branch_id, $planDaily, '36');

                $val['current_daily_new_ac_tenure60'] = branchBusinessTenureInvestNewAcCount($startDate, $endDate, $branch_id, $planDaily, '60');

                $val['monthly_new_ac_tenure12'] = branchBusinessInvestTenureNewAcCountType($startDate, $endDate, $branch_id, $monthlyId, '12');

                $val['monthly_new_ac_tenure36'] = branchBusinessInvestTenureNewAcCountType($startDate, $endDate, $branch_id, $monthlyId, '36');

                $val['monthly_new_ac_tenure60'] = branchBusinessInvestTenureNewAcCountType($startDate, $endDate, $branch_id, $monthlyId, '60');

                $val['monthly_new_ac_tenure84'] = branchBusinessInvestTenureNewAcCountType($startDate, $endDate, $branch_id, $monthlyId, '84');

                $val['monthly_new_ac_tenure120'] = branchBusinessInvestTenureNewAcCountType($startDate, $endDate, $branch_id, $monthlyId, '120');

                $val['monthly_new_ac_tenurekanyadan'] = branchBusinessInvestTenureKanyadhan($startDate, $endDate, $branch_id, $monthlyId, $tenure);

                $val['monthly_new_ac_amt_sum_tenure12'] = branchBusinessInvestTenureNewDenoSumType($startDate, $endDate, $branch_id, $monthlyId, '12');

                $val['monthly_new_ac_amt_sum_tenure36'] = branchBusinessInvestTenureNewDenoSumType($startDate, $endDate, $branch_id, $monthlyId, '36');

                $val['monthly_new_ac_amt_sum_tenure60'] = branchBusinessInvestTenureNewDenoSumType($startDate, $endDate, $branch_id, $monthlyId, '60');

                $val['monthly_new_ac_amt_sum_tenure84'] = branchBusinessInvestTenureNewDenoSumType($startDate, $endDate, $branch_id, $monthlyId, '84');

                $val['monthly_new_ac_amt_sum_tenure120'] = branchBusinessInvestTenureNewDenoSumType($startDate, $endDate, $branch_id, $monthlyId, '120');

                $val['monthly_new_ac_amt_sum_tenurekanyadan'] = branchBusinessInvestKanyadhanTenureNewDenoSumType($startDate, $endDate, $branch_id, $monthlyId, $tenure);

                $val['current_daily_new_ac_amt_sum_tenure12'] = branchBusinessTenureInvestNewDenoSum($startDate, $endDate, $branch_id, $planDaily, '12');

                $val['current_daily_new_ac_amt_sum_tenure24'] = branchBusinessTenureInvestNewDenoSum($startDate, $endDate, $branch_id, $planDaily, '24');

                $val['current_daily_new_ac_amt_sum_tenure36'] = branchBusinessTenureInvestNewDenoSum($startDate, $endDate, $branch_id, $planDaily, '36');

                $val['current_daily_new_ac_amt_sum_tenure60'] = branchBusinessTenureInvestNewDenoSum($startDate, $endDate, $branch_id, $planDaily, '60');

                $val['total_cash_account'] = gettotalcashaccount($startDate, $endDate, $branch_id);
                $val['total_cash_amount'] = gettotalcashamount($startDate, $endDate, $branch_id);

                // Fd N.I

                $val['monthly_new_fd_ac_tenure12'] = branchBusinessInvestTenureNewAcCountType($startDate, $endDate, $branch_id, $fdId, '12');
                $val['monthly_new_fd_ac_tenure18'] = branchBusinessInvestTenureNewAcCountType($startDate, $endDate, $branch_id, $fdId, '18');
                $val['monthly_new_fd_ac_tenure48'] = branchBusinessInvestTenureNewAcCountType($startDate, $endDate, $branch_id, $fdId, '48');
                $val['monthly_new_fd_ac_tenure60'] = branchBusinessInvestTenureNewAcCountType($startDate, $endDate, $branch_id, $fdId, '60');
                $val['monthly_new_fd_ac_tenure72'] = branchBusinessInvestTenureNewAcCountType($startDate, $endDate, $branch_id, $fdId, '72');
                $val['monthly_new_fd_ac_tenure96'] = branchBusinessInvestTenureNewAcCountType($startDate, $endDate, $branch_id, $fdId, '96');
                $val['monthly_new_fd_ac_tenure120'] = branchBusinessInvestTenureNewAcCountType($startDate, $endDate, $branch_id, $fdId, '120');

                $val['monthly_new_fd_sum_ac_tenure12'] = branchBusinessInvestTenureNewDenoSumType($startDate, $endDate, $branch_id, $fdId, '12');
                $val['monthly_new_fd_sum_ac_tenure18'] = branchBusinessInvestTenureNewDenoSumType($startDate, $endDate, $branch_id, $fdId, '18');
                $val['monthly_new_fd_sum_ac_tenure48'] = branchBusinessInvestTenureNewDenoSumType($startDate, $endDate, $branch_id, $fdId, '48');
                $val['monthly_new_fd_sum_ac_tenure60'] = branchBusinessInvestTenureNewDenoSumType($startDate, $endDate, $branch_id, $fdId, '60');
                $val['monthly_new_fd_sum_ac_tenure72'] = branchBusinessInvestTenureNewDenoSumType($startDate, $endDate, $branch_id, $fdId, '72');
                $val['monthly_new_fd_sum_ac_tenure96'] = branchBusinessInvestTenureNewDenoSumType($startDate, $endDate, $branch_id, $fdId, '96');
                $val['monthly_new_fd_sum_ac_tenure120'] = branchBusinessInvestTenureNewDenoSumType($startDate, $endDate, $branch_id, $fdId, '120');

                // Loan Plan Wise
                $val['personal_loan_total_ac'] = gettotalloanAccountPlanwise($startDate, $endDate, $branch_id, '1');
                $val['grp_loan_total_ac'] = gettotalloanAccountPlanwise($startDate, $endDate, $branch_id, '3');
                $val['loan_against_investment_total_ac'] = gettotalloanAccountPlanwise($startDate, $endDate, $branch_id, '4');
                $val['staff_loan_total_ac'] = gettotalloanAccountPlanwise($startDate, $endDate, $branch_id, '2');

                $val['personal_loan_total_amt'] = gettotalloanAmountPlanwise($startDate, $endDate, $branch_id, '1');
                $val['grp_loan_total_amt'] = gettotalloanAmountPlanwise($startDate, $endDate, $branch_id, '3');
                $val['loan_against_investment_total_amt'] = gettotalloanAmountPlanwise($startDate, $endDate, $branch_id, '4');
                $val['staff_loan_total_amt'] = gettotalloanAmountPlanwise($startDate, $endDate, $branch_id, '2');

                //$val['staff_loan_total_ac'] = gettotalloanAmountPlanwise($startDate,$endDate,$branch_id,'2');

                // Fund Transfer Loan and Micro Detail

                /*
                $loans  = DB::table('funds_transfer')
                        ->join('samraddh_banks', 'funds_transfer.head_office_bank_id', '=', 'samraddh_banks.id')
                        ->select('funds_transfer.head_office_bank_id','samraddh_banks.bank_name',DB::raw('DATE(transfer_date_time)'), DB::raw('sum(amount) as total'))
                        ->where('funds_transfer.transfer_type',0)->where('funds_transfer.transfer_mode','1')
                        ->whereBetween(\DB::raw('DATE(transfer_date_time)'), [$startDate, $endDate])->groupBy('head_office_bank_id', 'DB::raw("DATE(transfer_date_time)")')->orderBy('transfer_date_time', 'DESC')
                        ->get();
                */
                /*
                $loans  = DB::table('funds_transfer')
                        ->join('samraddh_banks', 'funds_transfer.head_office_bank_id', '=', 'samraddh_banks.id')
                        ->select('funds_transfer.head_office_bank_id','samraddh_banks.bank_name',DB::raw('DATE(transfer_date_time)'), DB::raw('sum(amount) as total'))
                        ->where('funds_transfer.transfer_type',0)->where('funds_transfer.transfer_mode','1')
                        ->whereBetween(\DB::raw('DATE(transfer_date_time)'), [$startDate, $endDate])->groupBy('DB::raw("DATE(transfer_date_time)")')->orderBy('transfer_date_time', 'DESC')
                        ->get();
                */

                //$loans =getfundsendloanandmicro($startDate,$endDate,$branch_id,'0') ;
                //$micros =getfundsendloanandmicro($startDate,$endDate,$branch_id,'1') ;

                // ...........................Fund Transfer Loan and Micro Detail...................//
                $loans = DB::table('funds_transfer as w')
                    ->join('samraddh_banks', 'w.head_office_bank_id', '=', 'samraddh_banks.id')
                    ->select([DB::Raw('sum(w.amount) as amount'), DB::Raw('DATE(w.transfer_date_time) day'), 'w.head_office_bank_id', 'samraddh_banks.bank_name'])
                    ->where('w.transfer_type', 0)
                    ->where('w.transfer_mode', '0')
                    ->where('w.branch_id', $branch_id)
                    ->whereBetween(\DB::raw('DATE(transfer_date_time)'), [$startDate, $endDate])
                    ->groupBy('day', 'head_office_bank_id')
                    ->orderBy('day', 'desc')
                    ->get();

                $micros = DB::table('funds_transfer as w')
                    ->join('samraddh_banks', 'w.head_office_bank_id', '=', 'samraddh_banks.id')
                    ->select([DB::Raw('sum(w.amount) as amount'), DB::Raw('DATE(w.transfer_date_time) day'), 'w.head_office_bank_id', 'samraddh_banks.bank_name'])
                    ->where('w.transfer_type', 0)
                    ->where('w.transfer_mode', '1')
                    ->where('w.branch_id', $branch_id)
                    ->whereBetween(\DB::raw('DATE(transfer_date_time)'), [$startDate, $endDate])
                    ->groupBy('day', 'head_office_bank_id')
                    ->orderBy('day', 'desc')
                    ->get();

                $totalMicro = DB::table('funds_transfer as w')
                    ->select([DB::Raw('sum(w.amount) as amount')])
                    ->where('w.transfer_type', 0)
                    ->where('w.transfer_mode', '1')
                    ->where('w.branch_id', $branch_id)
                    ->whereBetween(\DB::raw('DATE(transfer_date_time)'), [$startDate, $endDate])
                    ->get();
                if (count($totalMicro) > 0) {
                    $totalMicro = $totalMicro[0]->amount;
                } else {
                    $totalMicro = 0;
                }

                $totalLoan = DB::table('funds_transfer as w')
                    ->select([DB::Raw('sum(w.amount) as amount')])
                    ->where('w.transfer_type', 0)
                    ->where('w.transfer_mode', '0')
                    ->where('w.branch_id', $branch_id)
                    ->whereBetween(\DB::raw('DATE(transfer_date_time)'), [$startDate, $endDate])
                    ->get();
                if (count($totalLoan) > 0) {
                    $totalLoan = $totalLoan[0]->amount;
                } else {
                    $totalLoan = 0;
                }
                $totalAmounts = (float) $totalLoan + (float) $totalMicro;
                // ...........................End Fund Transfer Loan and Micro Detail...................//

                // ........................... RECEIVED CHEQUES Detail...................//
                $receivedChequeMicro = DB::table('branch_daybook as w')
                    ->join('samraddh_banks', 'w.cheque_bank_to', '=', 'samraddh_banks.id', 'left')
                    ->select([DB::Raw('sum(w.amount) as amount'), DB::Raw('DATE(w.entry_date) day'), 'w.cheque_bank_to', 'samraddh_banks.bank_name'])
                    ->where('w.payment_mode', 1)
                    ->where('w.payment_type', 'CR')
                    ->where('w.branch_id', $branch_id)
                    ->where('w.type', '!=', '5')
                    ->where('w.type', '!=', '10')
                    ->where('w.type', '!=', '12')
                    ->whereBetween(\DB::raw('DATE(entry_date)'), [$startDate, $endDate])
                    ->groupBy('day', 'cheque_bank_to')
                    ->orderBy('day', 'desc')
                    ->get();

                $receivedChequeLoan = DB::table('samraddh_bank_daybook as w')
                    ->join('samraddh_banks', 'w.amount_from_id', '=', 'samraddh_banks.id', 'left')
                    ->select([DB::Raw('sum(w.amount) as amount'), DB::Raw('DATE(w.entry_date) day'), 'w.amount_from_id', 'samraddh_banks.bank_name'])
                    ->where('w.payment_mode', 1)
                    ->where('w.payment_type', 'CR')
                    ->where('w.branch_id', $branch_id)
                    ->where('w.type', '5')
                    ->whereIn('w.sub_type', ["52,53,55,56,57,58"])
                    ->whereBetween(\DB::raw('DATE(entry_date)'), [$startDate, $endDate])
                    ->groupBy('day', 'amount_from_id')
                    ->orderBy('day', 'desc')
                    ->get();

                $receivedChequeMicoTotal = DB::table('branch_daybook as w')
                    ->select([DB::Raw('sum(w.amount) as amount')])
                    ->where('w.payment_mode', 1)
                    ->where('w.payment_type', 'CR')
                    ->where('w.branch_id', $branch_id)
                    ->where('w.type', '!=', '5')
                    ->where('w.type', '!=', '10')
                    ->where('w.type', '!=', '12')
                    ->whereBetween(\DB::raw('DATE(entry_date)'), [$startDate, $endDate])
                    ->get();

                if (count($receivedChequeMicoTotal) > 0) {
                    $receivedChequeMicoTotal = $receivedChequeMicoTotal[0]->amount;
                } else {
                    $receivedChequeMicoTotal = 0;
                }

                $receivedChequeLoanTotal = DB::table('samraddh_bank_daybook as w')
                    ->select([DB::Raw('sum(w.amount) as amount')])
                    ->where('w.payment_mode', 1)
                    ->where('w.payment_type', 'CR')
                    ->where('w.branch_id', $branch_id)
                    ->where('w.type', '5')
                    ->whereIn('w.sub_type', ["52,53,55,56,57,58"])
                    ->whereBetween(\DB::raw('DATE(entry_date)'), [$startDate, $endDate])
                    ->get();

                if (count($receivedChequeLoanTotal) > 0) {
                    $receivedChequeLoanTotal = $receivedChequeLoanTotal[0]->amount;
                } else {
                    $receivedChequeLoanTotal = 0;
                }

                $total_received_cheque_amount = (float) $receivedChequeLoanTotal + (float) $receivedChequeMicoTotal;

                // ...........................End RECEIVED CHEQUES Detail...................//

                $val['fw_fd'] = branchBusinessInvestRenewAmountSumFW($startDate, $endDate, $fdId, $branch_id);
                $val['fw_month'] = branchBusinessInvestRenewAmountSumFW($startDate, $endDate, $monthlyId, $branch_id);
                $val['fw_daily'] = branchBusinessInvestRenewAmountSumFW($startDate, $endDate, $dailyId, $branch_id);

                $val['fw_fd_count'] = branchBusinessInvestRenewAmountcountFW($startDate, $endDate, $fdId, $branch_id);
                $val['fw_month_count'] = branchBusinessInvestRenewAmountcountFW($startDate, $endDate, $monthlyId, $branch_id);
                $val['fw_daily_count'] = branchBusinessInvestRenewAmountcountFW($startDate, $endDate, $dailyId, $branch_id);

                $val['fw_loan_recovery_count'] = fw_loan_recovery_count($startDate, $endDate, $branch_id);
                $val['fw_loan_recovery'] = fw_loan_recovery($startDate, $endDate, $branch_id);

                $val['fw_filecharge'] = fw_filecharge($startDate, $endDate, $branch_id);
                $val['fw_filecharge_sum'] = fw_filecharge_sum($startDate, $endDate, $branch_id);

                $val['fw_other'] = totalOtherMemberFW($startDate, $endDate, $branch_id);
                $val['fw_other_sum'] = totalOtherMemberFWSum($startDate, $endDate, $branch_id);

                $val['fw_total'] = $val['fw_other'] + $val['fw_filecharge'] + $val['fw_loan_recovery_count'] + $val['fw_fd_count'] + $val['fw_month_count'] + $val['fw_daily_count'];

                $val['fw_total_sum'] = $val['fw_other_sum'] + $val['fw_filecharge_sum'] + $val['fw_loan_recovery'] + $val['fw_daily'] + $val['fw_month'] + $val['fw_fd'];

                $val['total_loan_recovery_ac'] = LoanDayBooks::where('branch_id', $branch_id)
                    ->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate])
                    ->where('is_deleted', 0)
                    ->count();
                $val['total_loan_recovery_amt'] = LoanDayBooks::where('branch_id', $branch_id)
                    ->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate])
                    ->where('is_deleted', 0)
                    ->sum('deposit');
                $val['total_loan_recovery_ac_cashmode'] = loanRecoverAccountCashMode($startDate, $endDate, $branch_id);
                $val['total_loan_recovery_amt_cashmode'] = number_format((float) loanRecoverAmtCashMode($startDate, $endDate, $branch_id), 2, '.', '');

                $val['file_chrg_total_ac'] = Daybook::whereIn('transaction_type', ['6,10'])
                    ->where('branch_id', $branch_id)
                    ->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate])
                    ->count();
                $val['file_chrg_amount_total'] = Daybook::whereIn('transaction_type', ['6,10'])
                    ->where('branch_id', $branch_id)
                    ->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate])
                    ->sum('amount');
                $val['file_chrg_total_ac_case_mode'] = Daybook::whereIn('transaction_type', ['6,10'])
                    ->where('branch_id', $branch_id)
                    ->where('payment_mode', '0')
                    ->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate])
                    ->count();
                $val['file_chrg_amount_total_cash_mode'] = Daybook::whereIn('transaction_type', ['6,10'])
                    ->where('branch_id', $branch_id)
                    ->where('payment_mode', '0')
                    ->whereBetween(\DB::raw('DATE(created_at)'), [$startDate, $endDate])
                    ->sum('amount');

                $val['totalFWbranchwise'] = getTotalFWbranchWise($startDate, $endDate, $branch_id);
                $val['totalFW'] = getTotalFW();

                // On Other Case
                $val['Other_ncc_total'] = totalOtherMiByTypeTotalCount($startDate, $endDate, $branch_id, 1, 12);
                +totalOtherMiByTypeTotalCount($startDate, $endDate, $branch_id, 1, 11);
                $val['Other_ncc_sum'] = totalOtherMiByType($startDate, $endDate, $branch_id, 1, 12);
                +totalOtherMiByType($startDate, $endDate, $branch_id, 1, 11);

                $val['total_final_payments'] = getTotalFinalPaymentBranchDaybook($startDate, $endDate, $branch_id);

                $closing_cash_in_hand_samraddh_micro = closing_cash_in_hand_samraddh_micro($endDate, $branch_id);
                $closing_cash_in_hand_samraddh_loan = closing_cash_in_hand_samraddh_loan($endDate, $branch_id);

                $branchToHoTotal = getbranchtoHototalAmount($startDate, $endDate, $branch_id);
            }

            return \Response::json([
                'view' => view('templates.admin.report.filtered_branch_business_report', [
                    'data' => $val,
                    'account_head' => $account_head,
                    'branchToHoTotal' => $branchToHoTotal,
                    'end_date' => $endDate,
                    'branch_id' => $branch_id,
                    'loans' => $loans,
                    'micros' => $micros,
                    'total_amounts' => $totalAmounts,
                    'receivedChequeMicro' => $receivedChequeMicro,
                    'receivedChequeLoan' => $receivedChequeLoan,
                    'total_received_cheque_amount' => $total_received_cheque_amount,
                    'closing_cash_in_hand_samraddh_micro' => $closing_cash_in_hand_samraddh_micro,
                    'closing_cash_in_hand_samraddh_loan' => $closing_cash_in_hand_samraddh_loan,
                    'start_date' => $startDate,
                ])->render(),
                'msg_type' => 'success',
                'startDate' => $startDate,
                'endDate' => $endDate,
            ]);
        }
    }
}
