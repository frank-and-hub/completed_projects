<?php
namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Auth;
use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\SamraddhBankDaybook;
use Carbon\Carbon;
use App\Interfaces\RepositoryInterface;
use App\Models\SamraddhBank;
use App\Models\AccountHeads;
use DB;
use App\Http\Traits\BalanceSheetTrait;

class BankLedgerController extends Controller
{
    use BalanceSheetTrait;
    private $repository;
    public function __construct(RepositoryInterface $repository)
    {
        $this->middleware('auth');
        $this->repository = $repository;
    }
    /**
     * function created by sourab which is is used
     * for getting pervious date total balance from
     * bankbalance table witch is used by repository pattern
     * rest code in repositery files and after getting data
     * from banakbalablance it's add on current balance
     */
    public function getBalance($startDate, $arrFormData, $endDate)
    {
        $opningbankBalance = $this->repository->getBankBalance($startDate, $arrFormData['company_id'], $arrFormData['bank_name'], $arrFormData['bank_account'], $endDate)
            ->orderByDESC('entry_date')
            ->sum('totalAmount');
        return $opningbankBalance;
    }
    // bank ledger report
    public function bankLedgerreport()
    {
        if (check_my_permission(Auth::user()->id, "33") != "1") {
            return redirect()->route('admin.dashboard');
        }
        $data['title'] = "Bank Ledger Report";
        $data['branches'] = Branch::get();
        $data['banks'] = SamraddhBank::get();
        return view('templates.admin.bank_ledger.report', $data);
    }
    public function bankLedgerListing(Request $request)
    {
        $pageStart = $_POST['start'];
        $start = $_POST['start'];
        $totalBalance = 0;
        $startAt = $_POST['draw'];
        $length = $_POST['length'];
        $search = $_POST['search']['value'];
        $from_start = $start;
        $bank_id = '';
        $startDate = '';
        $endDate = '';
        $bankAccount_id = '';
        if ($request->ajax()) {
            $arrFormData = array();
            if (!empty($_POST['searchform'])) {
                foreach ($_POST['searchform'] as $frm_data) {
                    $arrFormData[$frm_data['name']] = $frm_data['value'];
                }
            }
            if (isset($arrFormData['is_search']) && $arrFormData['is_search'] == 'yes') {
                // Now Count row start................................................................../
                $data1 = SamraddhBankDaybook::with([
                    'memberInvestment.memberCompany.member',
                    'companyName:id,name',
                    'Branch:id,name,branch_code,sector,regan,zone',
                    'memberCompany:id,customer_id,member_id',
                    'memberCompany.member:id,first_name,last_name'
                ])
                    // as per discussed with sachin sir on call with alpana ma'am from now 04-03-24 all active or deleted bank details will show on ledger listing
                    ->where('is_deleted', 0)
                ;
                if (isset($arrFormData['is_search']) && $arrFormData['is_search'] == 'yes') {
                    if ($arrFormData['bank_name'] != '' && $arrFormData['bank_account'] != '') {
                        $bank_id = $arrFormData['bank_name'];
                        $data1 = $data1->where('bank_id', $bank_id);
                    }
                    if ($arrFormData['bank_account'] != '') {
                        $bankAccount_id = $arrFormData['bank_account'];
                        $data1 = $data1->whereAccountId($bankAccount_id);
                    }
                    if ($arrFormData['company_id'] != '') {
                        $company_id = $arrFormData['company_id'];
                        $data1 = $data1->where('company_id', $company_id);
                    }
                    if ($arrFormData['start_date'] != '') {
                        $startDate = date("Y-m-d", strtotime(convertDate($arrFormData['start_date'])));
                        $created_at = date("Y-m-d", strtotime(convertDate($arrFormData['created_at'])));
                        if ($arrFormData['end_date'] != '') {
                            $endDate = date("Y-m-d ", strtotime(convertDate($arrFormData['end_date'])));
                        } else {
                            $endDate = '';
                        }
                        $data1 = $data1->whereBetween(\DB::raw('entry_date'), ["" . $startDate . "", "" . $endDate . ""]);
                    }
                }
                // $whereCond = '((type = "3" && bank_id = "'.$bank_id.'") ||(type = "4" && amount_from_id = "'.$bank_id.'") ||(type = "10" && bank_id = "'.$bank_id.'") ||(type = "22" && amount_from_id = "'.$bank_id.'") || (type = "7" && amount_to_id = "'.$bank_id.'") || (type="5" && amount_to_id = "'.$bank_id.'") || (type = "8" && (amount_from_id = "'.$bank_id.'" ||(type = "12" && bank_id = "'.$bank_id.'")||(type = "14" && amount_to_id = "'.$bank_id.'") ||(type = "15" && amount_to_id = "'.$bank_id.'") || (type = "16" && amount_to_id = "'.$bank_id.'") || (type = "17" && amount_to_id = "'.$bank_id.'")|| (type = "18" && amount_from_id = "'.$bank_id.'")||amount_from_id = "'.$bank_id.'")) || (account_id = "'.$bank_id.'")  || (type = "13" && sub_type="132" && bank_id = "'.$bank_id.'"))';
                // 		$data1=$data1->whereRaw($whereCond);
                $totalCount = $data1->orderBy('entry_date', 'asc')->count();
                // Now count row end .................................................................../
                $date = Carbon::today()->toDateString();
                $data = SamraddhBankDaybook::with([
                    'memberInvestment.memberCompany.member',
                    'companyName:id,name',
                    'Branch:id,name,branch_code,sector,regan,zone',
                    'memberCompany:id,customer_id,member_id',
                    'memberCompany.member:id,first_name,last_name'
                ])
                    // as per discussed with sachin sir on call with alpana ma'am from now 04-03-24 all active or deleted bank details will show on ledger listing
                    ->where('is_deleted', 0)
                    ->offset($start)
                    ->limit($totalCount)
                ;
                if ($arrFormData['bank_name'] != '' && $arrFormData['bank_account'] != '') {
                    $bank_id = $arrFormData['bank_name'];
                    $bank_name = $arrFormData['name'];
                    $bankAccount_id = $arrFormData['bank_account'];
                    $data = $data->where('bank_id', $bank_id);
                }
                if ($arrFormData['bank_account'] != '') {
                    $bank_account = $arrFormData['bank_account'];
                    $data = $data->whereAccountId($bank_account);
                }
                if ($arrFormData['company_id'] != '') {
                    $company_id = $arrFormData['company_id'];
                    $data = $data->where('company_id', $company_id);
                }
                if ($arrFormData['start_date'] != '') {
                    $startDate = date("Y-m-d", strtotime(convertDate($arrFormData['start_date'])));
                    $created_at = date("Y-m-d", strtotime(convertDate($arrFormData['created_at'])));
                    if ($arrFormData['end_date'] != '') {
                        $endDate = date("Y-m-d ", strtotime(convertDate($arrFormData['end_date'])));
                    } else {
                        $endDate = '';
                    }
                    $data = $data->whereBetween(\DB::raw('entry_date'), ["" . $startDate . "", "" . $endDate . ""]);
                }
                $data = $data->orderBy('entry_date', 'asc')->get();
                $rowReturn = array();
                // Start code opening balance...................................../
                if ($bank_id != "" && $bankAccount_id != "" && count($data) > 0 && $pageStart == 0) {
                    $val = array();
                    $val["Sr_no"] = "";
                    $val['DT_RowIndex'] = " ";
                    $val['id'] = "";
                    $val['daybook_ref_id'] = "";
                    $val['bank_id'] = "";
                    $val['company'] = '';
                    $val['account_id'] = "";
                    $val['type'] = "";
                    $val['sub_type'] = "";
                    $val['type_id'] = "";
                    $val['type_transaction_id'] = "";
                    $val['associate_id'] = "";
                    $val['member_id'] = "";
                    $val['branch_id'] = "";
                    $val['opening_balance'] = "";
                    $val['closing_balance'] = "";
                    $val['description'] = "";
                    $val['description_dr'] = "";
                    $val['description_cr'] = "";
                    $val['payment_type'] = "";
                    $val['payment_mode'] = "";
                    $val['currency_code'] = "";
                    $val['amount_to_id'] = "";
                    $val['amount_to_name'] = "";
                    $val['amount_from_id'] = "";
                    $val['amount_from_name'] = "";
                    $val['v_no'] = "";
                    $val['v_date'] = "";
                    $val['ssb_account_id_from'] = "";
                    $val['ssb_account_id_to'] = "";
                    $val['cheque_no'] = "";
                    $val['cheque_date'] = "";
                    $val['cheque_bank_from'] = "";
                    $val['cheque_bank_ac_from'] = "";
                    $val['cheque_bank_ifsc_from'] = "";
                    $val['cheque_bank_branch_from'] = "";
                    $val['cheque_bank_from_id'] = "";
                    $val['cheque_bank_ac_from_id'] = "";
                    $val['cheque_bank_to'] = "";
                    $val['cheque_bank_ac_to'] = "";
                    $val['cheque_bank_to_name'] = "";
                    $val['cheque_bank_to_branch'] = "";
                    $val['cheque_bank_to_ac_no'] = "";
                    $val['cheque_bank_to_ifsc'] = "";
                    $val['transction_no'] = "";
                    $val['transction_bank_from'] = "";
                    $val['transction_bank_ac_from'] = "";
                    $val['transction_bank_ifsc_from'] = "";
                    $val['transction_bank_branch_from'] = "";
                    $val['transction_bank_from_id'] = "";
                    $val['transction_bank_from_ac_id'] = "";
                    $val['transction_bank_to'] = "";
                    $val['transction_bank_ac_to'] = "";
                    $val['transction_bank_to_name'] = "";
                    $val['transction_bank_to_ac_no'] = "";
                    $val['transction_bank_to_branch'] = "";
                    $val['transction_bank_to_ifsc'] = "";
                    $val['transction_date'] = "";
                    $val['entry_date'] = "";
                    $val['entry_time'] = "";
                    $val['created_by'] = "";
                    $val['created_by_id'] = "";
                    $val['created_at'] = "";
                    $val['member_name'] = "";
                    $val['m_account'] = "";
                    $val['updated_at'] = "";
                    $val['branch_code'] = "";
                    $val['branch_name'] = "";
                    $val['date'] = "";
                    $val['account_number'] = "";
                    $val['particular'] = "Opening Balance";
                    $val['account_head_no'] = "";
                    $val['account_head_name'] = "";
                    $val['cheque_no'] = "";
                    $val['credit'] = " ";
                    $val['debit'] = "";
                    $demobalance = $this->getbalance($startDate, $arrFormData, $endDate);
                    $val['balance'] = number_format((float) $demobalance, 2, '.', '');
                    $rowReturn[] = $val;
                }
                $totalbalance = (count($rowReturn) > 0) ? $rowReturn[0]['balance'] : 'N/A';
                foreach ($data as $value) {
                    // dd($value);
                    $data = $this->getCompleteDetail($value);
                    $memberAccount = $data['memberAccount'];
                    $type = $data['type'];
                    $plan_name = $data['plan_name'];
                    $rentType = $data['rent_type'];
                    $memberName = $data['memberName'];
                    $memberId = $data['memberId'];
                    $val = array();
                    $start++;
                    $val["Sr_no"] = $start;
                    $val['DT_RowIndex'] = $start;
                    $val['id'] = $value->id;
                    $val['daybook_ref_id'] = $value->daybook_ref_id;
                    $val['bank_id'] = $value->bank_id;
                    $val['company'] = $value->companyName->name;
                    $val['account_id'] = $value->account_id;
                    $val['type'] = $value->type;
                    $val['sub_type'] = $value->sub_type;
                    $val['type_id'] = $value->type_id;
                    $val['type_transaction_id'] = $value->type_transaction_id;
                    $val['associate_id'] = $value->associate_id;
                    $val['member_id'] = $value->member_id;
                    $val['branch_id'] = $value->branch_id;
                    $val['opening_balance'] = $value->opening_balance;
                    $val['closing_balance'] = $value->closing_balance;
                    $val['description'] = $value->description;
                    $val['description_dr'] = $value->description_dr;
                    $val['description_cr'] = $value->description_cr;
                    $val['payment_type'] = $value->payment_type;
                    $val['payment_mode'] = $value->payment_mode;
                    $val['currency_code'] = $value->currency_code;
                    $val['amount_to_id'] = $value->amount_to_id;
                    $val['amount_to_name'] = $value->amount_to_name;
                    $val['amount_from_id'] = $value->amount_from_id;
                    $val['amount_from_name'] = $value->amount_from_name;
                    $val['v_no'] = $value->v_no;
                    $val['v_date'] = $value->v_date;
                    $val['ssb_account_id_from'] = $value->ssb_account_id_from;
                    $val['ssb_account_id_to'] = $value->ssb_account_id_to;
                    $val['cheque_no'] = $value->cheque_no;
                    $val['cheque_date'] = $value->cheque_date;
                    $val['cheque_bank_from'] = $value->cheque_bank_from;
                    $val['cheque_bank_ac_from'] = $value->cheque_bank_ac_from;
                    $val['cheque_bank_ifsc_from'] = $value->cheque_bank_ifsc_from;
                    $val['cheque_bank_branch_from'] = $value->cheque_bank_branch_from;
                    $val['cheque_bank_from_id'] = $value->cheque_bank_from_id;
                    $val['cheque_bank_ac_from_id'] = $value->cheque_bank_ac_from_id;
                    $val['cheque_bank_to'] = $value->cheque_bank_to;
                    $val['cheque_bank_ac_to'] = $value->cheque_bank_ac_to;
                    $val['cheque_bank_to_name'] = $value->cheque_bank_to_name;
                    $val['cheque_bank_to_branch'] = $value->cheque_bank_to_branch;
                    $val['cheque_bank_to_ac_no'] = $value->cheque_bank_to_ac_no;
                    $val['cheque_bank_to_ifsc'] = $value->cheque_bank_to_ifsc;
                    $val['transction_no'] = $value->transction_no;
                    $val['transction_bank_from'] = $value->transction_bank_from;
                    $val['transction_bank_ac_from'] = $value->transction_bank_ac_from;
                    $val['transction_bank_ifsc_from'] = $value->transction_bank_ifsc_from;
                    $val['transction_bank_branch_from'] = $value->transction_bank_branch_from;
                    $val['transction_bank_from_id'] = $value->transction_bank_from_id;
                    $val['transction_bank_from_ac_id'] = $value->transction_bank_from_ac_id;
                    $val['transction_bank_to'] = $value->transction_bank_to;
                    $val['transction_bank_ac_to'] = $value->transction_bank_ac_to;
                    $val['transction_bank_to_name'] = $value->transction_bank_to_name;
                    $val['transction_bank_to_ac_no'] = $value->transction_bank_to_ac_no;
                    $val['transction_bank_to_branch'] = $value->transction_bank_to_branch;
                    $val['transction_bank_to_ifsc'] = $value->transction_bank_to_ifsc;
                    $val['transction_date'] = $value->transction_date;
                    $val['entry_date'] = $value->entry_date;
                    $val['entry_time'] = $value->entry_time;
                    $val['created_by'] = $value->created_by;
                    $val['created_by_id'] = $value->created_by_id;
                    $val['created_at'] = $value->created_at;
                    $val['updated_at'] = $value->updated_at;
                    //branch_code
                    $val['branch_code'] = $value->Branch ? $value->Branch->branch_code : 'N/A';
                    // branch name
                    if ($value->Branch) {
                        $val['branch_name'] = $value->Branch->name;
                    } else if ($value->type == 8) {
                        $val['branch_name'] = 'Bank To Bank';
                    } else {
                        $val['branch_name'] = "N/A";
                    }
                    //Account Number
                    $transaction_date = date("d/m/Y", strtotime(convertDate($value->entry_date)));
                    $val['date'] = $transaction_date;
                    $val['account_number'] = $memberAccount;
                    //description
                    $val['particular'] = $type . '-' . $val['daybook_ref_id'];
                    $val['member_name'] = $memberName;
                    $val['m_account'] = $memberId;
                    // account_head
                    $account_head_id = SamraddhBank::where('id', $value->bank_id)->first(['account_head_id', 'bank_name', 'company_id']);
                    if ($account_head_id) {
                        $account_head_no = AccountHeads::where('head_id', $account_head_id->account_head_id)->first(['id']);
                        $val['account_head_no'] = $account_head_no->id;
                        $val['account_head_name'] = $account_head_id->bank_name;
                    } else {
                        $val['account_head_no'] = "N/A";
                        $val['account_head_name'] = "N/A";
                    }
                    //Cheque Number
                    if ($value->cheque_no) {
                        $val['cheque_no'] = $value->cheque_no;
                    } else if ($value->transction_no) {
                        $val['cheque_no'] = $value->transction_no;
                        // $val['cheque_no'] = \App\Models\BranchDaybook::where('daybook_ref_id',$value->daybook_ref_id)->value('transction_no');
                    } else {
                        $val['cheque_no'] = \App\Models\BranchDaybook::where('daybook_ref_id', $value->daybook_ref_id)->orderBy('id')->value('transction_no') ?? 'N/A';
                    }
                    // $amount = '';
                    if ($value->payment_type == "CR") {
                        $val['credit'] = "&#x20B9;" . number_format((float) $value->amount, 2, '.', '');
                    } else {
                        $val['credit'] = "N/A";
                    }
                    if ($value->payment_type == "DR") {
                        $val['debit'] = "&#x20B9;" . number_format((float) $value->amount, 2, '.', '');
                    } else {
                        $val['debit'] = "N/A";
                    }
                    //Balance
                    if ($value->payment_type == "CR") {
                        $t = number_format((float) $totalbalance + $value->amount, 2, '.', '');
                        $totalbalance = $t;
                    } elseif ($value->payment_type == "DR") {
                        $t = number_format((float) $totalbalance - $value->amount, 2, '.', '');
                        $totalbalance = $t;
                    }
                    // p($val);
                    $val['balance'] = number_format((float) $totalbalance, 2, '.', '');
                    $rowReturn[] = $val;
                }
                // closing balance start ............................/
                if ($bank_id != "" && $bankAccount_id != "" && count($data) > 0) {
                    $start++;
                    $val = array();
                    $val["Sr_no"] = '';
                    $val['DT_RowIndex'] = " ";
                    $val['id'] = "";
                    $val['daybook_ref_id'] = "";
                    $val['bank_id'] = "";
                    $val['company'] = "";
                    $val['account_id'] = "";
                    $val['type'] = "";
                    $val['sub_type'] = "";
                    $val['type_id'] = "";
                    $val['type_transaction_id'] = "";
                    $val['associate_id'] = "";
                    $val['member_id'] = "";
                    $val['branch_id'] = "";
                    $val['opening_balance'] = "";
                    $val['closing_balance'] = "";
                    $val['description'] = "";
                    $val['description_dr'] = "";
                    $val['description_cr'] = "";
                    $val['payment_type'] = "";
                    $val['payment_mode'] = "";
                    $val['currency_code'] = "";
                    $val['amount_to_id'] = "";
                    $val['amount_to_name'] = "";
                    $val['amount_from_id'] = "";
                    $val['amount_from_name'] = "";
                    $val['v_no'] = "";
                    $val['v_date'] = "";
                    $val['ssb_account_id_from'] = "";
                    $val['ssb_account_id_to'] = "";
                    $val['cheque_no'] = "";
                    $val['cheque_date'] = "";
                    $val['cheque_bank_from'] = "";
                    $val['cheque_bank_ac_from'] = "";
                    $val['cheque_bank_ifsc_from'] = "";
                    $val['cheque_bank_branch_from'] = "";
                    $val['cheque_bank_from_id'] = "";
                    $val['cheque_bank_ac_from_id'] = "";
                    $val['cheque_bank_to'] = "";
                    $val['cheque_bank_ac_to'] = "";
                    $val['cheque_bank_to_name'] = "";
                    $val['cheque_bank_to_branch'] = "";
                    $val['cheque_bank_to_ac_no'] = "";
                    $val['cheque_bank_to_ifsc'] = "";
                    $val['transction_no'] = "";
                    $val['transction_bank_from'] = "";
                    $val['transction_bank_ac_from'] = "";
                    $val['transction_bank_ifsc_from'] = "";
                    $val['transction_bank_branch_from'] = "";
                    $val['transction_bank_from_id'] = "";
                    $val['transction_bank_from_ac_id'] = "";
                    $val['transction_bank_to'] = "";
                    $val['transction_bank_ac_to'] = "";
                    $val['transction_bank_to_name'] = "";
                    $val['transction_bank_to_ac_no'] = "";
                    $val['transction_bank_to_branch'] = "";
                    $val['transction_bank_to_ifsc'] = "";
                    $val['transction_date'] = "";
                    $val['entry_date'] = "";
                    $val['entry_time'] = "";
                    $val['created_by'] = "";
                    $val['created_by_id'] = "";
                    $val['created_at'] = "";
                    $val['updated_at'] = "";
                    $val['branch_code'] = "";
                    $val['branch_name'] = "";
                    $val['date'] = "";
                    $val['account_number'] = "";
                    $val['particular'] = "Closing Balance";
                    $val['account_head_no'] = "";
                    $val['account_head_name'] = "";
                    $val['member_name'] = "";
                    $val['m_account'] = "";
                    $val['cheque_no'] = "";
                    $val['credit'] = "";
                    $val['debit'] = "";
                    $val['balance'] = $totalbalance ?? '0.00';
                    $rowReturn[] = $val;
                }
                //}
                // closing balance end ................................./
                $output = array("draw" => $_POST['draw'], "recordsTotal" => $totalCount, 'balance' => $totalbalance, "recordsFiltered" => $totalCount, "data" => $rowReturn, "page" => $pageStart, 'start' => $start);
                return json_encode($output);
            } else {
                $output = array(
                    "branch_id" => Auth::user()->branch_id,
                    "draw" => 0,
                    "recordsTotal" => 0,
                    "recordsFiltered" => 0,
                    "data" => 0,
                );
                return json_encode($output);
            }
        }
    }
}
