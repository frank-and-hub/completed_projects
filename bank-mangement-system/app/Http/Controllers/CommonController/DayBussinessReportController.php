<?php

namespace App\Http\Controllers\CommonController;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Companies;
use App\Models\CompanyBranch;
use App\Models\Plans;
use App\Models\PlanTenures;
use App\Models\Daybook;
use App\Models\Memberinvestments;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\BranchBusinessReportExport1;
use Carbon;
use URL;
use Auth;
use DB;
use Illuminate\Http\Request;

class DayBussinessReportController extends Controller
{
    public function index()
    {
        if (Auth::user()->role_id != 3) { // admin permissiso
            if (check_my_permission(Auth::user()->id, "348") != "1") {
                return redirect()->route('admin.dashboard');
            }
        } else { // branch permission
            if (!in_array('Daily Business Report', auth()->user()->getPermissionNames()->toArray())) {
                return redirect()->route('branch.dashboard');
            }
        }
        $data['title'] = 'Report | Day Business Report';
        return view('templates/CommonViews/DayBusinessReport.index', $data);
    }
    public function reportDetail(Request $request)
    {

        $daybookreport = new \App\Http\Controllers\Admin\Report\DayBookDublicateController;
        $daybookreportglobal = $daybookreport->day_book_filter_book_report_global($request->all());
        $dateForm = date("Y-m-d", strtotime(convertDate($request->start_date)));
        $dateTo = date("Y-m-d", strtotime(convertDate($request->end_date)));
        $branch_id = $request->branch;
        $companyId = $request->company_id;
        /////-----------NEW FW JOINING datil-------------
        $new_FW = DB::table('members')
            ->where('is_associate', 1)
            ->where('associate_branch_id', $branch_id)
            ->whereBetween(DB::raw('DATE(associate_join_date)'), [$dateForm, $dateTo])
            ->count();
        //--------------Renewal Account no.-------------
        $ac_no = DB::table('member_investments')
            ->join('members', 'members.id', '=', 'member_investments.associate_id')
            ->where('members.is_associate', 1)
            ->where('members.associate_branch_id', $branch_id)
            ->whereBetween(DB::raw('DATE(members.associate_join_date)'), [$dateForm, $dateTo])
            ->where(function ($query) use ($companyId) {
                if ($companyId != 0) {
                    $query->where('member_investments.company_id', $companyId);
                }
            })
            ->count();
        //--------------Renewal NCC-------------
        $ncc = DB::table('member_investments')
            ->join('members', 'members.id', '=', 'member_investments.associate_id')
            ->where('members.is_associate', 1)
            ->where('members.associate_branch_id', $branch_id)
            ->whereBetween(DB::raw('DATE(members.associate_join_date)'), [$dateForm, $dateTo])
            ->where(function ($query) use ($companyId) {
                if ($companyId != 0) {
                    $query->where('member_investments.company_id', $companyId);
                }
            })
            ->sum('member_investments.deposite_amount');
        //--------------Renewal Account-------------
        $renewal_ac_no = DB::table('day_books')
            ->join('members', 'members.id', '=', 'day_books.associate_id')
            ->where('members.is_associate', 1)
            ->where('members.associate_branch_id', $branch_id)
            ->whereBetween(DB::raw('DATE(members.associate_join_date)'), [$dateForm, $dateTo])
            ->where('day_books.transaction_type', 4)
            ->where('day_books.is_deleted', 0)
            ->where(function ($query) use ($companyId) {
                if ($companyId != 0) {
                    $query->where('day_books.company_id', $companyId);
                }
            })
            ->distinct()
            ->count('day_books.account_no');
        //--------------Renewal Amount-------------
        $renewal_amt = DB::table('day_books')
            ->join('members', 'members.id', '=', 'day_books.associate_id')
            ->where('members.is_associate', 1)
            ->where('members.associate_branch_id', $branch_id)
            ->whereBetween(DB::raw('DATE(members.associate_join_date)'),  [$dateForm, $dateTo])
            ->where('day_books.transaction_type', 4)
            ->where('day_books.is_deleted', 0)
            ->where(function ($query) use ($companyId) {
                if ($companyId != 0) {
                    $query->where('day_books.company_id', $companyId);
                }
            })
            ->sum('day_books.deposit');
        /////-----------NEW FW JOINING datil-------------
        //---------------Fund transfer Query start------------------
        $dataFundTransfer = DB::table('samraddh_bank_accounts as a')
            ->select('a.id', 'a.account_no', 'b.bank_name')
            ->selectSub(function ($query) use ($dateForm, $dateTo, $companyId, $branch_id) {
                $query->select(DB::raw('IFNULL(SUM(ft.amount), 0)'))
                    ->from('funds_transfer as ft')
                    ->where('ft.transfer_type', 0)
                    ->where('ft.is_deleted', 0)
                    ->where('ft.status', 1)                    
                    ->where(function ($query) use ($companyId) {
                        if ($companyId != 0) {
                            $query->where('ft.company_id', $companyId);
                        }
                    })
                    ->where('ft.branch_id', $branch_id)
                    ->whereBetween(DB::raw('DATE(ft.created_at)'), [$dateForm, $dateTo])
                    ->whereColumn('ft.head_office_bank_account_number', 'a.account_no');
            }, 'funds_transfer_amt')
            ->join('samraddh_banks as b', 'b.id', '=', 'a.bank_id')
            ->where(function ($query) use ($companyId) {
                if ($companyId != 0) {
                    $query->where('a.company_id', $companyId);
                }
            })
            ->get();
        //---------------Fund transfer Query end------------------
        //group & personal loan query start--------------
        $dataLoanDetail = DB::table('loans as l')
            ->select('l.id', 'l.loan_type', 'l.name')
            ->selectRaw('IFNULL(m.new_loan_amt, 0) as new_loan_amt')
            ->selectRaw('IFNULL(m.new_loan_ac_no, 0) as new_loan_ac_no')
            ->selectRaw('IFNULL(ml.loan_rec_amount, 0) as loan_rec_amount')
            ->selectRaw('IFNULL(ml.loan_rec_ac_no, 0) as loan_rec_ac_no')
            ->leftJoin(DB::raw('(SELECT loan_type, SUM(amount) as new_loan_amt, COUNT(id) as new_loan_ac_no
                FROM (
                    SELECT a.loan_type, a.amount, a.id
                    FROM member_loans a
                    JOIN loans l1 ON l1.loan_type = "L" AND l1.id = a.loan_type
                    WHERE a.status IN (3, 4) 
                    AND DATE(a.approve_date) BETWEEN "' . $dateForm . '" AND "' . $dateTo . '"
                    AND a.branch_id = ' . $branch_id . '
                    UNION ALL
                    SELECT g.loan_type, g.amount, g.id
                    FROM group_loans g
                    JOIN loans l1 ON l1.loan_type = "G" AND l1.id = g.loan_type
                    WHERE g.status IN (3, 4) 
                    AND DATE(g.approve_date) BETWEEN "' . $dateForm . '" AND "' . $dateTo . '"
                   AND g.branch_id = ' . $branch_id . '
                ) z1
                GROUP BY loan_type
            ) m'), 'm.loan_type', '=', 'l.id')
            ->leftJoin(DB::raw('(SELECT loan_type, SUM(deposit) as loan_rec_amount, COUNT(id) as loan_rec_ac_no
                FROM loan_day_books
                WHERE loan_sub_type IN (0, 1) AND is_deleted = 0 
                AND DATE(payment_date) BETWEEN "' . $dateForm . '" AND "' . $dateTo . '"
                AND branch_id = ' . $branch_id . '
                GROUP BY loan_type
            ) ml'), 'ml.loan_type', '=', 'l.id')
            ->where(function ($query) use ($companyId) {
                if ($companyId != 0) {
                    $query->where('l.company_id', $companyId);
                }
            })
            ->get();
        //group & personal loan query end--------------
        // investmentPlan Details query start---------------   
        $dataInvestmentDetail = DB::table('plan_tenures AS pt')
            ->join('plans AS p', 'p.id', '=', 'pt.plan_id')
            ->leftJoin(DB::raw('(
            SELECT
                mi.plan_id,
                ROUND(mi.tenure * 12) AS tenure,
                pt1.effective_from,
                SUM(mi.deposite_amount) AS ncc,
                SUM(IF(mi.deposite_amount > 0, 1, 0)) AS nccAC
            FROM
                member_investments AS mi
                JOIN plan_tenures pt1 ON pt1.plan_id = mi.plan_id AND pt1.month_to = ROUND(mi.tenure * 12, 0) AND DATE(mi.created_at) BETWEEN pt1.effective_from AND IFNULL(pt1.effective_to, NOW())
            WHERE
                mi.is_deleted = 0
                AND DATE(mi.created_at) BETWEEN "' . $dateForm . '" AND "' . $dateTo . '"
                AND mi.branch_id = ' . $branch_id . '
            GROUP BY
                mi.plan_id, mi.tenure, pt1.effective_from
        ) AS t'), function ($join) {
                $join->on('t.plan_id', '=', 'pt.plan_id')
                    ->on('t.tenure', '=', 'pt.tenure')
                    ->on('t.effective_from', '=', 'pt.effective_from');
            })
            ->leftJoin(DB::raw('(
            SELECT
                m.plan_id,
                ROUND(m.tenure * 12) AS tenure,
                pt1.effective_from,
                SUM(IF(d.transaction_type = 4, d.deposit, 0)) AS ren,
                SUM(IF(d.transaction_type = 4, 1, 0)) AS renAC
            FROM
                day_books AS d
                JOIN member_investments m ON d.account_no = m.account_number
                JOIN plan_tenures pt1 ON pt1.plan_id = m.plan_id AND pt1.month_to = ROUND(m.tenure * 12, 0) AND DATE(m.created_at) BETWEEN pt1.effective_from AND IFNULL(pt1.effective_to, NOW())
            WHERE
                d.is_deleted = 0
                AND DATE(d.created_at) BETWEEN "' . $dateForm . '" AND "' . $dateTo . '"
                AND d.branch_id = ' . $branch_id . '
                AND d.transaction_type = 4
            GROUP BY
                m.plan_id, m.tenure, pt1.effective_from
        ) AS t1'), function ($join) {
                $join->on('t1.plan_id', '=', 'pt.plan_id')
                    ->on('t1.tenure', '=', 'pt.tenure')
                    ->on('t1.effective_from', '=', 'pt.effective_from');
            })
            ->leftJoin(DB::raw('(
            SELECT
                m.plan_id,
                ROUND(m.tenure * 12) AS tenure,
                pt1.effective_from,
                SUM(da.final_amount) AS maturityPayment
            FROM
                demand_advices AS da
                JOIN member_investments m ON da.account_number = m.account_number
                JOIN plan_tenures pt1 ON pt1.plan_id = m.plan_id AND pt1.month_to = ROUND(m.tenure * 12, 0) AND DATE(m.created_at) BETWEEN pt1.effective_from AND IFNULL(pt1.effective_to, NOW())
            WHERE
                da.is_deleted = 0
                AND DATE(da.payment_date) BETWEEN "' . $dateForm . '" AND "' . $dateTo . '"
                AND da.payment_type != 0
                AND da.is_mature = 0
                AND da.branch_id = ' . $branch_id . '
            GROUP BY
                m.plan_id, m.tenure, pt1.effective_from
        ) AS t2'), function ($join) {
                $join->on('t2.plan_id', '=', 'pt.plan_id')
                    ->on('t2.tenure', '=', 'pt.tenure')
                    ->on('t2.effective_from', '=', 'pt.effective_from');
            })
            ->where('p.plan_category_code', '!=', 'S')
            ->where('pt.month_to', '=', DB::raw('pt.tenure'))
            ->where(function ($query) use ($companyId) {
                if ($companyId != 0) {
                    $query->where('p.company_id', $companyId);
                }
            })
            ->groupBy(DB::raw('IF(p.plan_sub_category_code = "K", "K", p.plan_category_code)'), DB::raw('IF(p.plan_sub_category_code = "K", 216, pt.tenure)'))
            ->select(
                DB::raw('IF(p.plan_sub_category_code = "K", "K", p.plan_category_code) AS plancategory'),
                DB::raw('IF(p.plan_sub_category_code = "K", 216, pt.tenure) AS plantenure'),
                DB::raw('SUM(IFNULL(t.ncc, 0)) AS ncc'),
                DB::raw('SUM(IFNULL(t.nccAC, 0)) AS nccAC'),
                DB::raw('SUM(IFNULL(t1.ren, 0)) AS ren'),
                DB::raw('SUM(IFNULL(t1.renAC, 0)) AS renAC'),
                DB::raw('SUM(IFNULL(t1.ren, 0) + IFNULL(t.ncc, 0)) AS totalAmount'),
                DB::raw('SUM(IFNULL(t2.maturityPayment, 0)) AS maturityPayment')
            )->orderBy('plancategory', 'ASC')->get();
        // investmentPlan Details query end---------------
        $getBranchId = getUserBranchId(Auth::user()->id);
        $branch_id = $getBranchId->id;
        if (Auth::user()->role_id != 3) {
            $branch_id = $request->branch;
        }
        if ($branch_id) {
            $data = Branch::where('id', $branch_id)->first();
            $companyId = Companies::find($request->company_id);
            $companyName = $companyId ? $companyId->name : 'All Company';
            return \Response::json([
                'view' => view('templates.CommonViews.DayBusinessReport.content', [
                    'name' => $data->name,
                    'branch_code' => $data->branch_code,
                    'regan' => $data->regan,
                    'sector' => $data->sector,
                    'zone' => $data->zone,
                    'start_date' => $request->start_date,
                    'end_date' => $request->end_date,
                    'companyName' => $companyName,
                    'investmentDetails' => $dataInvestmentDetail,
                    'loanDetails' => $dataLoanDetail,
                    'daybookreportglobal' => $daybookreportglobal,
                    'fundTransferDetails' => $dataFundTransfer,
                    'newFW' => $new_FW,
                    'Newaccount' => $ac_no,
                    'ncc_amount' => $ncc,
                    'renewal_ac_no' => $renewal_ac_no,
                    'renewal_amt' => $renewal_amt,
                ])->render(),
                'msg_type' => 'success',
            ]);
        } else {
            return \Response::json(['msg_type' => 'error']);
        }
    }
    public function getBranch(Request $request)
    {
        if ($request->company_id == 0) {
            $branchIds = CompanyBranch::where('status', 1)->get(['company_id', 'branch_id', 'status']);
        } else {
            $branchIds = CompanyBranch::where('status', 1)->where('company_id', $request->company_id)
                ->get(['company_id', 'branch_id', 'status']);
        }
        $branchdetails = array();
        foreach ($branchIds as $row) {
            if ($row->branch_id) {
                $branch = Branch::where('id', $row->branch_id)
                    ->first(['id', 'name', 'branch_code']);
                array_push($branchdetails, $branch);
            }
        }
        return $branchdetails;
    }
}
