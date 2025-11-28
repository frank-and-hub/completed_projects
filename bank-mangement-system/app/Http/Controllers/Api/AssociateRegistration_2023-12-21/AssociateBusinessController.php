<?php
namespace App\Http\Controllers\Api\AssociateRegistration;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use App\Http\Resources\AssociateLoanResource;
use App\Models\Companies;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Validator;
use DB;
use Illuminate\Support\Facades\Cache;



class AssociateBusinessController extends controller
{

  public function report(Request $request)
  {
    
      $formData = $request->all();
    
      $startDate = date('Y-m-d',strtotime($formData['start_date']));
      $endDate = date('Y-m-d',strtotime($formData['end_date']));
      $formData['associate'] = empty($formData['associate']) ? 0 : $formData['associate'];
      $company =$formData['company_id'];
      $branch = $formData['branch_id'];
      $associate = $formData['associate'];
      // Fetch data from the database
      // $data = $this->associateReport($startDate, $endDate,$associate, $company, $branch );
     
      $data = DB::select('call associate_business_report(?,?,?,?,?,?,?)', [$startDate, $endDate, $associate,$company, $branch, 1,99999]);
      
           
      // $formattedData = $this->formatDataForResponse($data);

      return new AssociateLoanResource($data);
  }

  // LISTING FUNTION
  private function formatDataForResponse($data)
  {
      $sno =0;
      $val = [];
      foreach ($data as $row)
      {
        $sno++;
        $val['sno'] = $sno;
        // $val['Company'] = $row->company_id;
        $val['AssociateCode'] = $row->associate_no;
        $val['AssociateName'] = $row->name;
        $val['AssociateBranch'] = $row->regan;
        $val['DailyNCC'] = $row->dnccamt;
        $val['DailyRenewal'] = $row->drenamt;
        $val['MonthlyNCC'] = $row->mnccamt;
        $val['MonthlyRenewal'] = $row->mrenamt;
        $val['FDNCC'] = $row->fnccamt;
        $val['NCC'] = $row->ncc_m;
        $val['TCC'] = $row->tcc_m;
        $val['SSBNCC'] = $row->snccamt;
        $val['T_NCC'] = $row->ncc_ssb;
        $val['T_TCC'] = $row->tcc_ssb;
        $val['SSBRenewal'] = $row->ssbren;
        $val['NewMembers'] = $row->new_m;
        $val['NewAssociates'] = $row->new_a;
        $val['NewLoansOTH'] = $row->loan_ac_no;
        $val['LoanAmount'] = $row->loan_amt;
        $val['LoanRecovery'] = $row->loan_recv_amt;
        $val['NewLoanLAD'] = $row->lad_transfer_ac_no;
        $val['LADAmount'] = $row->lad_transfer_amount;
        $val['LADRecovery'] = $row->lad_rec_amount;
        $val['MaturityPayment'] = $row->dem_amt;
        $val['Commission'] = 0.00;
        $rowReturn[] = $val;
      }

      return $rowReturn;
  }

  private function associateReport($startDate, $endDate, $associate, $company, $branch)
  {
    $page_number = 1;
    $page_size = 9999;

    $start_row = ($page_number - 1) * $page_size;

    $result = DB::table('members as b')
        ->join('branch as br', 'br.id', '=', 'b.associate_branch_id')
        ->select(
            'br.regan',
            'br.sector',
            'br.zone',
            'br.name as branch_name',
            'b.associate_no',
            DB::raw("TRIM(CONCAT(b.first_name, ' ', IFNULL(b.last_name, '')) AS name"),
            DB::raw("IFNULL(dnccac, 0) AS dnccac"),
            DB::raw("IFNULL(dnccamt, 0) AS dnccamt"),
            DB::raw("IFNULL(drenac, 0) AS drenac"),
            DB::raw("IFNULL(drenamt, 0) AS drenamt"),
            DB::raw("IFNULL(mnccac, 0) AS mnccac"),
            DB::raw("IFNULL(mnccamt, 0) AS mnccamt"),
            DB::raw("IFNULL(mrenac, 0) AS mrenac"),
            DB::raw("IFNULL(mrenamt, 0) AS mrenamt"),
            DB::raw("IFNULL(fnccac, 0) AS fnccac"),
            DB::raw("IFNULL(fnccamt, 0) AS fnccamt"),
            DB::raw("IFNULL(ncc_m, 0) AS ncc_m"),
            DB::raw("IFNULL(tcc_m, 0) AS tcc_m"),
            DB::raw("IFNULL(snccamt, 0) AS snccamt"),
            DB::raw("IFNULL(sni, 0) AS sni"),
            DB::raw("IFNULL(ssbren_ac, 0) AS ssbren_ac"),
            DB::raw("IFNULL(ssbren, 0) AS ssbren"),
            DB::raw("(IFNULL(dnccamt, 0) + IFNULL(mnccamt, 0) + IFNULL(fnccamt, 0) + IFNULL(snccamt, 0)) AS ncc_ssb"),
            DB::raw("(IFNULL(dnccamt, 0) + IFNULL(mnccamt, 0) + IFNULL(fnccamt, 0) + IFNULL(drenamt, 0) + IFNULL(mrenamt, 0) + IFNULL(ssbren, 0) + IFNULL(snccamt, 0)) AS tcc_ssb"),
            DB::raw("IFNULL(new_a, 0) AS new_a"),
            DB::raw("IFNULL(new_m, 0) AS new_m"),
            DB::raw("IFNULL(dem_amt, 0) AS dem_amt"),
            DB::raw("IFNULL(loan_amt, 0) AS loan_amt"),
            DB::raw("IFNULL(loan_ac_no, 0) AS loan_ac_no"),
            DB::raw("IFNULL(loan_recv_ac_no, 0) AS loan_recv_ac_no"),
            DB::raw("IFNULL(loan_recv_amt, 0) AS loan_recv_amt"),
            DB::raw("IFNULL(lad_transfer_ac_no, 0) AS lad_transfer_ac_no"),
            DB::raw("IFNULL(lad_transfer_amount, 0) AS lad_transfer_amount"),
            DB::raw("IFNULL(lad_rec_amount, 0) AS lad_rec_amount"),
            DB::raw("IFNULL(lad_rec_ac_no, 0) AS lad_rec_ac_no")
        )
        ->leftJoin(DB::raw('(SELECT @from_date := from_date, @to_date := to_date, @company := company_id, @branch_id := branch_id, @associate_code := associate_code) dt'))
        ->leftJoin(DB::raw('(SELECT associate_id, SUM(IF(p.plan_category_code = "D" AND d.transaction_type = 2, 1, 0)) AS dnccac, SUM(IF(p.plan_category_code = "D" AND d.transaction_type = 2, d.deposit, 0)) AS dnccamt, SUM(IF(p.plan_category_code = "D" AND d.transaction_type = 4, 1, 0)) AS drenac, SUM(IF(p.plan_category_code = "D" AND d.transaction_type = 4, d.deposit, 0)) AS drenamt, SUM(IF(p.plan_category_code = "M" AND d.transaction_type = 2, 1, 0)) AS mnccac, SUM(IF(p.plan_category_code = "M" AND d.transaction_type = 2, d.deposit, 0)) AS mnccamt, SUM(IF(p.plan_category_code = "M" AND d.transaction_type = 4, 1, 0)) AS mrenac, SUM(IF(p.plan_category_code = "M" AND d.transaction_type = 4, d.deposit, 0)) AS mrenamt, SUM(IF(p.plan_category_code = "F" AND d.transaction_type = 2, 1, 0)) AS fnccac, SUM(IF(p.plan_category_code = "F" AND d.transaction_type = 2, d.deposit, 0)) AS fnccamt, SUM(IF(d.transaction_type = 2, d.deposit, 0)) AS ncc_m, SUM(d.deposit) AS tcc_m FROM day_books d JOIN member_investments a ON a.account_number = d.account_no JOIN plans p ON p.id = a.plan_id JOIN plan_categories c ON c.code = p.plan_category_code WHERE d.transaction_type IN (2, 4) AND d.is_deleted = 0 AND p.plan_category_code IN ("D", "M", "F") AND DATE(d.created_at) BETWEEN @from_date AND @to_date AND (CASE WHEN @company <> 0 THEN d.company_id = @company ELSE 1 END) AND (CASE WHEN @branch_id <> 0 THEN d.branch_id = @branch_id ELSE 1 END) GROUP BY d.associate_id) t'), 't.associate_id', '=', 'b.id')
        ->leftJoin(DB::raw('(SELECT associate_id, SUM(deposit) AS loan_recv_amt, COUNT(id) AS loan_recv_ac_no FROM (SELECT d.associate_id, d.deposit, d.id FROM loan_day_books d JOIN member_loans a ON a.account_number = d.account_number JOIN loans l ON a.loan_type = l.id WHERE d.loan_sub_type IN (0, 1) AND l.loan_category NOT IN (3, 4) AND d.is_deleted = 0 AND DATE(d.payment_date) BETWEEN @from_date AND @to_date AND (CASE WHEN @company <> 0 THEN a.company_id = @company ELSE 1 END) AND (CASE WHEN @branch_id <> 0 THEN a.branch_id = @branch_id ELSE 1 END) UNION ALL SELECT d.associate_id, d.deposit, d.id FROM loan_day_books d JOIN group_loans a ON a.account_number = d.account_number JOIN loans l ON a.loan_type = l.id WHERE d.loan_sub_type IN (0, 1) AND l.loan_category = 3 AND d.is_deleted = 0 AND DATE(d.payment_date) BETWEEN @from_date AND @to_date AND (CASE WHEN @company <> 0 THEN a.company_id = @company ELSE 1 END) AND (CASE WHEN @branch_id <> 0 THEN a.branch_id = @branch_id ELSE 1 END)) z GROUP BY associate_id) t1'), 't1.associate_id', '=', 'b.id')
        ->leftJoin(DB::raw('(SELECT associate_member_id, SUM(amount) AS loan_amt, COUNT(id) AS loan_ac_no FROM (SELECT a.associate_member_id, a.amount, a.id FROM member_loans a INNER JOIN loans l ON a.loan_type = l.id WHERE l.loan_category NOT IN (3, 4) AND DATE(a.approve_date) BETWEEN @from_date AND @to_date AND (CASE WHEN @company <> 0 THEN a.company_id = @company ELSE 1 END) AND (CASE WHEN @branch_id <> 0 THEN a.branch_id = @branch_id ELSE 1 END) UNION ALL SELECT a.associate_member_id, a.amount, a.id FROM group_loans a INNER JOIN loans l ON a.loan_type = l.id WHERE l.loan_category = 3 AND DATE(a.approve_date) BETWEEN @from_date AND @to_date AND (CASE WHEN @company <> 0 THEN a.company_id = @company ELSE 1 END) AND (CASE WHEN @branch_id <> 0 THEN a.branch_id = @branch_id ELSE 1 END)) z1 GROUP BY associate_member_id) t2'), 't2.associate_member_id', '=', 'b.id')
        ->leftJoin(DB::raw('(SELECT a.associate_member_id, COUNT(a.id) AS lad_transfer_ac_no, SUM(a.amount) AS lad_transfer_amount FROM member_loans a INNER JOIN loans l ON a.loan_type = l.id WHERE l.loan_category = 4 AND a.status = 4 AND DATE(a.approve_date) BETWEEN @from_date AND @to_date AND (CASE WHEN @company <> 0 THEN a.company_id = @company ELSE 1 END) AND (CASE WHEN @branch_id <> 0 THEN a.branch_id = @branch_id ELSE 1 END) GROUP BY a.associate_member_id) mll'), 'mll.associate_member_id', '=', 'b.id')
        ->leftJoin(DB::raw('(SELECT d.associate_id, SUM(d.deposit) AS lad_rec_amount, COUNT(d.id) AS lad_rec_ac_no FROM loan_day_books d JOIN member_loans a ON a.account_number = d.account_number JOIN loans l ON a.loan_type = l.id WHERE d.loan_sub_type IN (0, 1) AND l.loan_category = 4 AND d.is_deleted = 0 AND DATE(d.payment_date) BETWEEN @from_date AND @to_date AND (CASE WHEN @company <> 0 THEN a.company_id = @company ELSE 1 END) AND (CASE WHEN @branch_id <> 0 THEN a.branch_id = @branch_id ELSE 1 END) GROUP BY d.associate_id) mlr'), 'mlr.associate_id', '=', 'b.id')
        ->leftJoin(DB::raw('(SELECT sa.associate_id AS associate_sa, SUM(deposit) AS snccamt, COUNT(id) AS sni FROM saving_account_transctions s INNER JOIN saving_accounts sa ON sa.id = s.saving_account_id WHERE s.is_deleted = 0 AND s.type = 1 AND DATE(s.created_at) BETWEEN @from_date AND @to_date AND (CASE WHEN @company <> 0 THEN s.company_id = @company ELSE 1 END) AND (CASE WHEN @branch_id <> 0 THEN s.branch_id = @branch_id ELSE 1 END) GROUP BY sa.associate_id) ssb'), 'ssb.associate_sa', '=', 'b.id')
        ->leftJoin(DB::raw('(SELECT associate_id, SUM(deposit) AS ssbren, COUNT(id) AS ssbren_ac FROM saving_account_transctions sr WHERE sr.is_deleted = 0 AND sr.type = 2 AND DATE(sr.created_at) BETWEEN @from_date AND @to_date AND (CASE WHEN @company <> 0 THEN sr.company_id = @company ELSE 1 END) AND (CASE WHEN @branch_id <> 0 THEN sr.branch_id = @branch_id ELSE 1 END) GROUP BY associate_id) ssbr'), 'ssbr.associate_id', '=', 'b.id')
        ->leftJoin(DB::raw('(SELECT mc.associate_id, COUNT(mc.id) AS new_m FROM member_companies mc WHERE mc.is_deleted = 0 AND (CASE WHEN @company <> 0 THEN mc.company_id = @company ELSE 1 END) AND mc.created_at BETWEEN @from_date AND @to_date GROUP BY mc.associate_id) memm'), 'memm.associate_id', '=', 'b.id')
        ->leftJoin(DB::raw('(SELECT m.associate_senior_id, COUNT(m.id) AS new_a FROM members m WHERE m.is_deleted = 0 AND DATE(m.associate_join_date) BETWEEN @from_date AND @to_date AND (CASE WHEN @company <> 0 THEN m.company_id = @company ELSE 1 END) AND (CASE WHEN @branch_id <> 0 THEN m.branch_id = @branch_id ELSE 1 END) GROUP BY m.associate_senior_id) mema'), 'mema.associate_senior_id', '=', 'b.id')
        ->leftJoin(DB::raw('(SELECT mi.associate_id AS senior, SUM(dem.final_amount) AS dem_amt FROM demand_advices dem INNER JOIN member_investments mi ON mi.id = dem.investment_id WHERE dem.payment_type != "0" AND dem.is_deleted = 0 AND dem.is_reject = "0" AND dem.date BETWEEN @from_date AND @to_date AND (CASE WHEN @company <> 0 THEN dem.company_id = @company ELSE 1 END) AND (CASE WHEN @branch_id <> 0 THEN dem.branch_id = @branch_id ELSE 1 END) GROUP BY mi.associate_id) demand'), 'demand.senior', '=', 'b.id')
        ->where('b.is_associate', 1)
        ->when($associate, function ($query) use ($associate) {
            return $query->where('b.associate_no', $associate);
        })
        ->when($branch, function ($query) use ($branch) {
            return $query->where('br.id', $branch);
        })
        ->orderBy('b.id')
        ->skip($start_row)
        ->take($page_size)
        ->get();
    
    return $result;
  }



}